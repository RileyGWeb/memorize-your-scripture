<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Verified;
use App\Listeners\UpdateLoginStreak;
use App\Listeners\MarkUserAsVerified;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(Login::class, UpdateLoginStreak::class);
        Event::listen(Verified::class, MarkUserAsVerified::class);
        
        // Prohibit destructive commands on production
        DB::prohibitDestructiveCommands(
            // $this->app->isProduction()
            // true
            false
        );
    }
}
