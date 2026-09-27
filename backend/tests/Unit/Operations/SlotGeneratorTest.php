<?php

namespace Tests\Unit\Operations;

use App\Domain\Operations\Support\SlotGenerator;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SlotGeneratorTest extends TestCase
{
    #[Test]
    public function it_slices_a_shift_into_slots_sized_by_duration_plus_buffer(): void
    {
        $date = Carbon::parse('2026-10-03'); // a Saturday, arbitrary fixed date

        $slots = SlotGenerator::generate(
            $date,
            [['start_time' => '09:00:00', 'end_time' => '11:00:00', 'daily_cap' => null]],
            [],
            serviceDurationMinutes: 30,
            serviceBufferMinutes: 5,
            alreadyBookedForService: 0,
        );

        // step = 35 minutes: 09:00-09:30, 09:35-10:05, 10:10-10:40 (10:45-11:15 would overrun 11:00)
        $this->assertCount(3, $slots);
        $this->assertSame('09:00', $slots[0]['starts_at']->format('H:i'));
        $this->assertSame('09:35', $slots[1]['starts_at']->format('H:i'));
        $this->assertSame('10:10', $slots[2]['starts_at']->format('H:i'));
    }

    #[Test]
    public function it_excludes_slots_overlapping_a_busy_interval(): void
    {
        $date = Carbon::parse('2026-10-03');

        $slots = SlotGenerator::generate(
            $date,
            [['start_time' => '09:00:00', 'end_time' => '10:00:00', 'daily_cap' => null]],
            [['starts_at' => Carbon::parse('2026-10-03 09:00'), 'ends_at' => Carbon::parse('2026-10-03 09:30')]],
            serviceDurationMinutes: 30,
            serviceBufferMinutes: 0,
            alreadyBookedForService: 0,
        );

        // step = 30: 09:00 slot conflicts with the busy interval and is dropped, 09:30 remains
        $this->assertCount(1, $slots);
        $this->assertSame('09:30', $slots[0]['starts_at']->format('H:i'));
    }

    #[Test]
    public function it_drops_a_shift_entirely_once_its_daily_cap_is_reached(): void
    {
        $date = Carbon::parse('2026-10-03');

        $slots = SlotGenerator::generate(
            $date,
            [['start_time' => '09:00:00', 'end_time' => '12:00:00', 'daily_cap' => 2]],
            [],
            serviceDurationMinutes: 30,
            serviceBufferMinutes: 0,
            alreadyBookedForService: 2,
        );

        $this->assertSame([], $slots);
    }

    #[Test]
    public function it_hides_slots_before_not_before_for_same_day_bookings(): void
    {
        $date = Carbon::parse('2026-10-03');

        $slots = SlotGenerator::generate(
            $date,
            [['start_time' => '09:00:00', 'end_time' => '11:00:00', 'daily_cap' => null]],
            [],
            serviceDurationMinutes: 30,
            serviceBufferMinutes: 0,
            alreadyBookedForService: 0,
            notBefore: Carbon::parse('2026-10-03 09:45'),
        );

        $this->assertTrue(collect($slots)->every(fn (array $slot) => $slot['starts_at']->gte(Carbon::parse('2026-10-03 09:45'))));
        $this->assertSame('10:00', $slots[0]['starts_at']->format('H:i'));
    }
}
