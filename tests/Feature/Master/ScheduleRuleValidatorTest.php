<?php

namespace Tests\Feature\Master;

use App\Models\Groomer;
use App\Models\GroomerShift;
use App\Models\User;
use App\Services\ScheduleRuleValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ScheduleRuleValidatorTest extends TestCase
{
    use RefreshDatabase;

    private function createGroomer(): Groomer
    {
        $user = User::factory()->create();

        return Groomer::create([
            'user_id' => $user->id,
            'level_skill' => 'dasar',
            'bisa_kucing' => false,
            'bisa_agresif' => false,
        ]);
    }

    public function test_rejects_overlapping_shifts(): void
    {
        $groomer = $this->createGroomer();

        GroomerShift::create([
            'groomer_id' => $groomer->id,
            'hari' => 1,
            'jam_mulai' => '09:00',
            'jam_selesai' => '12:00',
        ]);

        $this->expectException(ValidationException::class);

        app(ScheduleRuleValidator::class)->validateShift(
            $groomer->id,
            1,
            '11:00',
            '14:00'
        );
    }

    public function test_allows_adjacent_shifts(): void
    {
        $groomer = $this->createGroomer();

        GroomerShift::create([
            'groomer_id' => $groomer->id,
            'hari' => 1,
            'jam_mulai' => '09:00',
            'jam_selesai' => '12:00',
        ]);

        app(ScheduleRuleValidator::class)->validateShift(
            $groomer->id,
            1,
            '12:00',
            '15:00'
        );

        $this->assertTrue(true);
    }

    public function test_allows_overlapping_hours_for_different_groomers(): void
    {
        $groomer1 = $this->createGroomer();
        $groomer2 = $this->createGroomer();

        GroomerShift::create([
            'groomer_id' => $groomer1->id,
            'hari' => 1,
            'jam_mulai' => '09:00',
            'jam_selesai' => '12:00',
        ]);

        app(ScheduleRuleValidator::class)->validateShift(
            $groomer2->id,
            1,
            '10:00',
            '13:00'
        );

        $this->assertTrue(true);
    }

    public function test_ignores_current_shift_when_updating(): void
    {
        $groomer = $this->createGroomer();

        $shift = GroomerShift::create([
            'groomer_id' => $groomer->id,
            'hari' => 1,
            'jam_mulai' => '09:00',
            'jam_selesai' => '12:00',
        ]);

        app(ScheduleRuleValidator::class)->validateShift(
            $groomer->id,
            1,
            '10:00',
            '13:00',
            $shift->id
        );

        $this->assertTrue(true);
    }
}