<?php

namespace Hubleto\App\External\WaiBlue\HlthcrCore\HubletoOverrides;

use Hubleto\App\Community\Notifications\EventListeners\NotifyUpdatedRecord;
use Hubleto\Framework\Interfaces\ModelInterface;

/**
 * Replaces Hubleto's "<record> updated" notifications to owners and managers (English, with a JSON diff).
 * The legacy app had no such notifications, so nothing is sent.
 */
class NoRecordUpdateNotifications extends NotifyUpdatedRecord
{
  // Must run before the Notifications app's init(), which creates the listener.
  public static function register(): void
  {
    \Hubleto\Framework\DependencyInjection::setServiceProvider(NotifyUpdatedRecord::class, self::class);
  }

  public function onModelAfterUpdate(ModelInterface $model, array $originalRecord, array $savedRecord): void
  {
  }
}
