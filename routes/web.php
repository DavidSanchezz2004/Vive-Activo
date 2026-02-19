<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/', fn () => redirect()->route('login'));

Route::middleware('guest')->group(function () {
  Route::view('/login', 'auth.login')->name('login');

  Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:login')
    ->name('login.post');

});

Route::middleware(['auth'])->group(function () {
    Route::get('/admin', fn () => view('admin.index'))->name('admin.index');
    Route::get('/supervisor', fn () => view('supervisor.index'))->name('supervisor.index');
    Route::get('/alumno', fn () => view('estudiante.index'))->name('alumno.index');
    Route::get('/dashboard', fn () => view('paciente.index'))->name('dashboard');
});

Route::post('/logout', [AuthController::class, 'logout'])
  ->middleware('auth')
  ->name('logout');

