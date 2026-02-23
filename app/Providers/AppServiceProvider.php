<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PatientSession;
use App\Observers\UserObserver;
use App\Observers\PatientSessionObserver;
use Illuminate\Support\Facades\URL;


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
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        User::observe(UserObserver::class);
        PatientSession::observe(PatientSessionObserver::class);

        RateLimiter::for('login', function (Request $request) {
            $email = strtolower((string) $request->input('email'));

            return [
                Limit::perMinute(5)->by($request->ip()),
                Limit::perMinute(5)->by($email.'|'.$request->ip()),
            ];
        });
    }
}
