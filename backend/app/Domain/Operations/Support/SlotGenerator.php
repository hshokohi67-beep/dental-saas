<?php

namespace App\Domain\Operations\Support;

use Carbon\Carbon;

/**
 * Pure slot-slicing logic — takes plain arrays (no Eloquent/DB access), so
 * it's unit-testable the same way ToothNumber is. Callers (controllers/
 * actions) are responsible for querying shifts/leaves/appointments and
 * shaping them into the arrays this expects.
 */
class SlotGenerator
{
    /**
     * @param  list<array{start_time: string, end_time: string, daily_cap: ?int}>  $shifts  Shifts already filtered to: this staff, this day-of-week, active, in effective range, and allowing the requested service.
     * @param  list<array{starts_at: Carbon, ends_at: Carbon}>  $busyIntervals  Existing active appointments + staff leave windows for this staff, this date.
     * @return list<array{starts_at: Carbon, ends_at: Carbon}>
     */
    public static function generate(
        \DateTimeInterface $date,
        array $shifts,
        array $busyIntervals,
        int $serviceDurationMinutes,
        int $serviceBufferMinutes,
        int $alreadyBookedForService,
        ?Carbon $notBefore = null,
    ): array {
        $slots = [];
        $step = $serviceDurationMinutes + $serviceBufferMinutes;
        $day = Carbon::instance($date)->startOfDay();

        foreach ($shifts as $shift) {
            if ($shift['daily_cap'] !== null && $alreadyBookedForService >= $shift['daily_cap']) {
                continue;
            }

            $shiftStart = $day->clone()->setTimeFromTimeString($shift['start_time']);
            $shiftEnd = $day->clone()->setTimeFromTimeString($shift['end_time']);

            $cursor = $shiftStart->clone();
            while ($cursor->clone()->addMinutes($serviceDurationMinutes)->lte($shiftEnd)) {
                $slotStart = $cursor->clone();
                $slotEnd = $slotStart->clone()->addMinutes($serviceDurationMinutes);
                $cursor = $cursor->clone()->addMinutes($step);

                if ($notBefore !== null && $slotStart->lt($notBefore)) {
                    continue;
                }

                if (self::overlapsAny($slotStart, $slotEnd, $busyIntervals)) {
                    continue;
                }

                $slots[] = ['starts_at' => $slotStart, 'ends_at' => $slotEnd];
            }
        }

        usort($slots, fn (array $a, array $b) => $a['starts_at'] <=> $b['starts_at']);

        return $slots;
    }

    /**
     * @param  list<array{starts_at: Carbon, ends_at: Carbon}>  $busyIntervals
     */
    private static function overlapsAny(Carbon $start, Carbon $end, array $busyIntervals): bool
    {
        foreach ($busyIntervals as $busy) {
            if ($start->lt($busy['ends_at']) && $end->gt($busy['starts_at'])) {
                return true;
            }
        }

        return false;
    }
}
