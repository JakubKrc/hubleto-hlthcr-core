<?php

namespace Hubleto\App\External\WaiBlue\HlthcrCore\Services;

/**
 * Adding months to a date the way people expect it: 31.08. + 6 months is 28.02., not 03.03.
 * (PHP's "+N months" overflows into the next month; the legacy app had the same problem.)
 */
class MonthDates
{
  public static function addMonths(string $date, int $months): string
  {
    $startDate = new \DateTimeImmutable($date);
    $targetMonth = $startDate->modify('first day of this month')->modify('+' . $months . ' months');

    $lastDayOfTargetMonth = (int) $targetMonth->format('t');
    $day = min((int) $startDate->format('j'), $lastDayOfTargetMonth);

    return $targetMonth->setDate((int) $targetMonth->format('Y'), (int) $targetMonth->format('n'), $day)->format('Y-m-d');
  }
}
