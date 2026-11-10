<?php

namespace Tests\Feature;

use Illuminate\Console\Scheduling\Schedule;
use Tests\TestCase;

class ReminderScheduleTest extends TestCase
{
    public function test_reminder_command_is_scheduled_daily_at_seven_wib(): void
    {
        $schedule = app(Schedule::class);

        $event = collect($schedule->events())->first(
            fn ($event) => str_contains(
                $event->command,
                'pengingat:jalankan'
            )
        );

        $this->assertNotNull($event);
        $this->assertSame('0 7 * * *', $event->expression);
        $this->assertSame('Asia/Jakarta', $event->timezone);
    }
}