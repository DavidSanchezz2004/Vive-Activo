<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\UserController;

Route::get('/', fn () => redirect()->route('login'));

// ============ AUTH ============
Route::middleware('guest')->group(function () {
  Route::view('/login', 'auth.login')->name('login');

  Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:login')
    ->name('login.post');
});

Route::post('/logout', [AuthController::class, 'logout'])
  ->middleware('auth')
  ->name('logout');

// ============ DASHBOARDS POR ROL ============
Route::middleware(['auth'])->group(function () {

  // ADMIN
  Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', fn () => view('admin.dashboard'))->name('dashboard');

    // CRUD de usuarios
    Route::resource('users', UserController::class);

    // placeholders (para que el sidebar no reviente)
    Route::get('/pacientes', fn () => view('admin.pacientes.index'))->name('pacientes.index');
    Route::get('/estudiante', fn () => view('admin.estudiante.index'))->name('estudiante.index');
    Route::get('/reportes', fn () => view('admin.reportes'))->name('reportes');
    Route::get('/config', fn () => view('admin.config'))->name('config');
  });

  // SUPERVISOR
  Route::prefix('supervisor')->name('supervisor.')->group(function () {
    Route::get('/', fn () => view('supervisor.dashboard'))->name('dashboard');

    Route::get('/pacientes', fn () => view('supervisor.pacientes'))->name('pacientes');
    Route::get('/alumnos', fn () => view('supervisor.alumnos'))->name('alumnos');
    Route::get('/reportes', fn () => view('supervisor.reportes'))->name('reportes');
  });

  // ESTUDIANTE
  Route::prefix('estudiante')->name('estudiante.')->group(function () {
    Route::get('/', fn () => view('estudiante.dashboard'))->name('dashboard');

    Route::get('/pacientes', fn () => view('estudiante.pacientes'))->name('pacientes');
    Route::get('/sesiones', fn () => view('estudiante.sesiones'))->name('sesiones');
  });

  // PACIENTE
  Route::prefix('paciente')->name('paciente.')->group(function () {
    Route::get('/', fn () => view('paciente.dashboard'))->name('dashboard');

    Route::get('/plan', fn () => view('paciente.plan'))->name('plan');
    Route::get('/rutinas', fn () => view('paciente.rutinas'))->name('rutinas');
    Route::get('/citas', fn () => view('paciente.citas'))->name('citas');
  });


  //PERFIL PARA TODOS
  Route::get('/perfil', [ProfileController::class, 'show'])->name('profile.show');
  Route::post('/perfil/personal', [ProfileController::class, 'updatePersonal'])->name('profile.personal');
  Route::post('/perfil/direccion', [ProfileController::class, 'updateAddress'])->name('profile.address');
  Route::post('/perfil/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');

});