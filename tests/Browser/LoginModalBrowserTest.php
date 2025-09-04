<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class LoginModalBrowserTest extends DuskTestCase
{
    use RefreshDatabase;

    public function test_modal_login_behavior(): void
    {
        // Create a user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->click('@login-button') // Adjust selector as needed
                    ->whenAvailable('.modal', function ($modal) {
                        $modal->type('input[name="email"]', 'test@example.com')
                              ->type('input[name="password"]', 'password')
                              ->click('button[type="submit"]');
                    })
                    ->waitForLocation('/')
                    ->assertAuthenticated();
        });
    }

    public function test_modal_closes_after_failed_login(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->click('@login-button')
                    ->whenAvailable('.modal', function ($modal) {
                        $modal->type('input[name="email"]', 'wrong@example.com')
                              ->type('input[name="password"]', 'wrongpassword')
                              ->click('button[type="submit"]');
                    })
                    ->waitUntilMissing('.modal')
                    ->assertGuest();
        });
    }
}
