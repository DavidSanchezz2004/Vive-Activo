<?php

use App\Enums\UserRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;

RedirectIfAuthenticated::redirectUsing(function (Request $request): string {
    $user = Auth::user();

    if (! $user) {
        return route('login');
    }

    return match ($user->role) {
        UserRole::Admin => route('admin.dashboard'),
        UserRole::Supervisor => route('supervisor.dashboard'),
        UserRole::Student => route('estudiante.dashboard'),
        default => route('paciente.dashboard'),
    };
});

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
