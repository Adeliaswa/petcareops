<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profil')
            ->put('/password', [
                'current_password' => 'password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profil');

        $this->assertTrue(
            Hash::check('new-password', $user->refresh()->password)
        );
    }

    public function test_wrong_current_password_is_rejected(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profil')
            ->put('/password', [
                'current_password' => 'wrong-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('updatePassword', 'current_password')
            ->assertRedirect('/profil');
    }

    public function test_new_password_must_be_at_least_eight_characters(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profil')
            ->put('/password', [
                'current_password' => 'password',
                'password' => '1234567',
                'password_confirmation' => '1234567',
            ]);

        $response->assertSessionHasErrorsIn('updatePassword', 'password');

        $this->assertTrue(
            Hash::check('password', $user->refresh()->password)
        );
    }

    public function test_password_confirmation_must_match(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profil')
            ->put('/password', [
                'current_password' => 'password',
                'password' => 'new-password',
                'password_confirmation' => 'different-password',
            ]);

        $response->assertSessionHasErrorsIn('updatePassword', 'password');

        $this->assertTrue(
            Hash::check('password', $user->refresh()->password)
        );
    }
}