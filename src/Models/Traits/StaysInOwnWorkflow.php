<?php

namespace Hubleto\App\External\WaiBlue\HlthcrCore\Models\Traits;

use Hubleto\App\Community\Workflow\Models\Workflow;
use Hubleto\App\Community\Workflow\Models\WorkflowHistory;
use Hubleto\App\Community\Workflow\Models\WorkflowStep;
use Hubleto\Framework\Exceptions\RecordSaveException;

/**
 * For models with a workflow of their own group (columns id_workflow and id_workflow_step).
 */
trait StaysInOwnWorkflow
{
  // Hubleto's form offers every workflow (Deals, Leads, ...), but the record must stay in a workflow of its group.
  protected function refuseWorkflowOfOtherGroup(array $record, string $workflowGroup): void
  {
    $isWorkflowSent = array_key_exists('id_workflow', $record) || array_key_exists('id_workflow_step', $record);
    if (!$isWorkflowSent) return;

    $storedRecord = $this->record->where('id', (int) ($record['id'] ?? 0))->first();
    $storedIdWorkflow = $storedRecord ? (int) $storedRecord->id_workflow : 0;
    $storedIdWorkflowStep = $storedRecord ? (int) $storedRecord->id_workflow_step : 0;

    $idWorkflow = (int) ($record['id_workflow'] ?? $storedIdWorkflow);
    $idWorkflowStep = (int) ($record['id_workflow_step'] ?? $storedIdWorkflowStep);

    $isUnchanged = $storedRecord && $idWorkflow == $storedIdWorkflow && $idWorkflowStep == $storedIdWorkflowStep;
    if ($isUnchanged) return;

    $isWorkflowOfGroup = $this->getModel(Workflow::class)->record
      ->where('id', $idWorkflow)
      ->where('group', $workflowGroup)
      ->exists()
    ;
    $isStepOfWorkflow = $this->getModel(WorkflowStep::class)->record
      ->where('id', $idWorkflowStep)
      ->where('id_workflow', $idWorkflow)
      ->exists()
    ;

    if (!$isWorkflowOfGroup || !$isStepOfWorkflow) {
      throw new RecordSaveException($this->translate('Choose a state of this record\'s own workflow.'));
    }
  }

  // A new record always starts in the first step of its group's workflow (set in onAfterCreate), whatever a save sends.
  protected function dropSentWorkflow(array $record): array
  {
    unset($record['id_workflow']);
    unset($record['id_workflow_step']);
    return $record;
  }

  // For a record that gets its first step without a model update: Hubleto logs the history only on updates.
  protected function logFirstWorkflowStep(array $savedRecord): void
  {
    $idWorkflowStep = (int) ($savedRecord['id_workflow_step'] ?? 0);
    if ($idWorkflowStep <= 0) return;

    // No user on the command line (demo data).
    $idUser = (int) $this->authProvider()->getUserId();

    $this->getModel(WorkflowHistory::class)->record->recordCreate([
      'model' => get_class($this),
      'record_id' => (int) $savedRecord['id'],
      'datetime_change' => date('Y-m-d H:i:s'),
      'id_user' => $idUser > 0 ? $idUser : null,
      'id_workflow' => (int) ($savedRecord['id_workflow'] ?? 0),
      'id_workflow_step' => $idWorkflowStep,
    ]);
  }
}
