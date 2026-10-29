<?php

namespace Hubleto\App\External\WaiBlue\HlthcrCore\Services;

/**
 * Whose records the audits, documents and exams calendars show (legacy calendars): administrators and managers
 * see all authors, others only themselves. "My events", or a user Hubleto sends (its reminders), narrows it.
 */
class CalendarAuthor extends \Hubleto\Erp\Core
{
  // 0 means every author.
  public function getAuthorIdToShow(array $calendarFilter): int
  {
    $showsOnlyMyEvents = (int) ($calendarFilter['fOwnership'] ?? 0) == 1;
    $canSeeAllAuthors = $this->getService(Roles::class)->isActiveUserAdministratorOrManager();
    if ($showsOnlyMyEvents || !$canSeeAllAuthors) return (int) $this->authProvider()->getUserId();

    return (int) ($calendarFilter['idUser'] ?? 0);
  }
}
