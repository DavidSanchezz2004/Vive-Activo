<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\PatientController;
use App\Http\Controllers\Admin\ConsultationController;
use App\Http\Controllers\Admin\SessionController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\DocumentController;
use App\Http\Controllers\Admin\NutritionPlanController;
use App\Http\Controllers\Admin\NutritionPlanTemplateController;
use App\Http\Controllers\Admin\RoutineTemplateController as AdminRoutineTemplateController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\PatientPlanController;
use App\Http\Controllers\PatientDashboardController;
use App\Http\Controllers\Patient\SessionReviewController as PatientSessionReviewController;
use App\Http\Controllers\StudentDashboardController;
use App\Http\Controllers\Supervisor\PatientController as SupervisorPatientController;
use App\Http\Controllers\Supervisor\NutritionPlanController as SupervisorNutritionPlanController;
use App\Http\Controllers\Supervisor\NutritionPlanTemplateController as SupervisorNutritionPlanTemplateController;
use App\Http\Controllers\Supervisor\RoutineController as SupervisorRoutineController;
use App\Http\Controllers\Supervisor\RoutineTemplateController as SupervisorRoutineTemplateController;
use App\Http\Controllers\Supervisor\StudentController as SupervisorStudentController;
use App\Http\Controllers\Supervisor\DashboardController as SupervisorDashboardController;

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
    Route::get('/', DashboardController::class)->name('dashboard');

    // CRUD de usuarios
    Route::resource('users', UserController::class);

    // Catálogo de planes comerciales
    Route::resource('planes', PlanController::class)
      ->except(['show'])
      ->parameters(['planes' => 'plan']);

    // placeholders (para que el sidebar no reviente)
    Route::resource('pacientes', PatientController::class)->only(['index','show'])->parameters(['pacientes'=>'patient']);
    Route::post('pacientes/{patient}/assign',   [PatientController::class, 'assign'])->name('pacientes.assign');
    Route::post('pacientes/{patient}/unassign', [PatientController::class, 'unassign'])->name('pacientes.unassign');
    Route::patch('pacientes/{patient}/toggle',  [PatientController::class, 'toggle'])->name('pacientes.toggle');
    Route::resource('alumnos', StudentController::class)->only(['index', 'edit', 'update'])->parameters(['alumnos' => 'student']);
    Route::patch('alumnos/{student}/toggle', [StudentController::class, 'toggle'])->name('alumnos.toggle');
    Route::resource('consultas', ConsultationController::class)->parameters(['consultas' => 'consultum']);
    Route::patch('consultas/{consultum}/status', [ConsultationController::class, 'updateStatus'])->name('consultas.status');
    Route::resource('sesiones', SessionController::class)->except(['destroy'])->parameters(['sesiones' => 'sesione']);
    Route::patch('sesiones/{sesione}/status', [SessionController::class, 'updateStatus'])->name('sesiones.status');
    // Pagos y documentos de paciente (nested)
    Route::post('pacientes/{patient}/pagos',           [PaymentController::class, 'store'])->name('pacientes.pagos.store');
    Route::put('pacientes/{patient}/pagos/{payment}',  [PaymentController::class, 'update'])->name('pacientes.pagos.update');
    Route::delete('pacientes/{patient}/pagos/{payment}',[PaymentController::class,'destroy'])->name('pacientes.pagos.destroy');
    Route::post('pacientes/{patient}/documentos',               [DocumentController::class, 'store'])->name('pacientes.documentos.store');
    Route::delete('pacientes/{patient}/documentos/{document}',  [DocumentController::class, 'destroy'])->name('pacientes.documentos.destroy');

    // Plan comercial del paciente (instancia)
    Route::post('pacientes/{patient}/planes', [PatientPlanController::class, 'store'])->name('pacientes.planes.store');
    Route::patch('pacientes/{patient}/planes/{patientPlan}/cancel', [PatientPlanController::class, 'cancel'])->name('pacientes.planes.cancel');
    // Planes nutricionales (anidados bajo pacientes)
    Route::get('pacientes/{patient}/nutrition-plans/create',        [NutritionPlanController::class, 'create'])->name('pacientes.nutrition_plans.create');
    Route::post('pacientes/{patient}/nutrition-plans',              [NutritionPlanController::class, 'store'])->name('pacientes.nutrition_plans.store');
    Route::get('pacientes/{patient}/nutrition-plans/{plan}',        [NutritionPlanController::class, 'show'])->name('pacientes.nutrition_plans.show');
    Route::patch('pacientes/{patient}/nutrition-plans/{plan}/off',  [NutritionPlanController::class, 'deactivate'])->name('pacientes.nutrition_plans.deactivate');
    Route::delete('pacientes/{patient}/nutrition-plans/{plan}',     [NutritionPlanController::class, 'destroy'])->name('pacientes.nutrition_plans.destroy');

    // Plantillas de planes nutricionales
    Route::resource('nutrition-templates', NutritionPlanTemplateController::class)
      ->except(['show'])
      ->names('nutrition_templates');

    // Plantillas de rutinas
    Route::resource('routine-templates', AdminRoutineTemplateController::class)
      ->except(['show'])
      ->names('routine_templates');
    Route::get('/reportes', fn () => view('admin.reportes'))->name('reportes');
    Route::get('/config', fn () => view('admin.config'))->name('config');
  });

  // SUPERVISOR
  Route::prefix('supervisor')->name('supervisor.')->group(function () {
    Route::get('/', [SupervisorDashboardController::class, 'dashboard'])->name('dashboard');
    Route::resource('consultas', ConsultationController::class)->parameters(['consultas' => 'consultum']);
    Route::patch('consultas/{consultum}/status', [ConsultationController::class, 'updateStatus'])->name('consultas.status');
    Route::resource('sesiones', SessionController::class)->except(['destroy'])->parameters(['sesiones' => 'sesione']);
    Route::patch('sesiones/{sesione}/status', [SessionController::class, 'updateStatus'])->name('sesiones.status');

    Route::get('/pacientes', [SupervisorPatientController::class, 'index'])->name('pacientes');
    Route::get('/pacientes/{patient}', [SupervisorPatientController::class, 'show'])->name('pacientes.show');

    // Planes nutricionales (anidados bajo pacientes)
    Route::get('pacientes/{patient}/nutrition-plans/create',        [SupervisorNutritionPlanController::class, 'create'])->name('pacientes.nutrition_plans.create');
    Route::post('pacientes/{patient}/nutrition-plans',              [SupervisorNutritionPlanController::class, 'store'])->name('pacientes.nutrition_plans.store');
    Route::get('pacientes/{patient}/nutrition-plans/{plan}',        [SupervisorNutritionPlanController::class, 'show'])->name('pacientes.nutrition_plans.show');
    Route::patch('pacientes/{patient}/nutrition-plans/{plan}/off',  [SupervisorNutritionPlanController::class, 'deactivate'])->name('pacientes.nutrition_plans.deactivate');
    Route::delete('pacientes/{patient}/nutrition-plans/{plan}',     [SupervisorNutritionPlanController::class, 'destroy'])->name('pacientes.nutrition_plans.destroy');

    // Plantillas de planes nutricionales
    Route::resource('nutrition-templates', SupervisorNutritionPlanTemplateController::class)
      ->except(['show'])
      ->names('nutrition_templates');

    // Plantillas de rutinas
    Route::resource('routine-templates', SupervisorRoutineTemplateController::class)
      ->except(['show'])
      ->names('routine_templates');

    // Rutinas (anidadas bajo pacientes)
    Route::get('pacientes/{patient}/routines/create',        [SupervisorRoutineController::class, 'create'])->name('pacientes.routines.create');
    Route::post('pacientes/{patient}/routines',              [SupervisorRoutineController::class, 'store'])->name('pacientes.routines.store');
    Route::get('pacientes/{patient}/routines/{routine}',     [SupervisorRoutineController::class, 'show'])->name('pacientes.routines.show');
    Route::patch('pacientes/{patient}/routines/{routine}/off',[SupervisorRoutineController::class, 'deactivate'])->name('pacientes.routines.deactivate');
    Route::delete('pacientes/{patient}/routines/{routine}',  [SupervisorRoutineController::class, 'destroy'])->name('pacientes.routines.destroy');

    Route::get('/alumnos', [SupervisorStudentController::class, 'index'])->name('alumnos');
    Route::get('/alumnos/{student}', [SupervisorStudentController::class, 'show'])->name('alumnos.show');
    Route::get('/reportes', [SupervisorDashboardController::class, 'reportes'])->name('reportes');
  });

  // ESTUDIANTE
  Route::prefix('estudiante')->name('estudiante.')->group(function () {
    Route::get('/',          [StudentDashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/pacientes', [StudentDashboardController::class, 'pacientes'])->name('pacientes');
    Route::get('/sesiones',  [StudentDashboardController::class, 'sesiones'])->name('sesiones');
    Route::patch('/sesiones/{sesione}/atencion', [StudentDashboardController::class, 'registrarAtencion'])->name('sesiones.atencion');
  });

  // PACIENTE
  Route::prefix('paciente')->name('paciente.')->group(function () {
    Route::get('/',          [PatientDashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/plan',      [PatientDashboardController::class, 'plan'])->name('plan');
    Route::get('/rutina',    [PatientDashboardController::class, 'rutina'])->name('rutina');
    Route::get('/consultas', [PatientDashboardController::class, 'consultas'])->name('consultas');
    Route::get('/sesiones',  [PatientDashboardController::class, 'sesiones'])->name('sesiones');

    Route::post('/sesiones/{patientSession}/review', [PatientSessionReviewController::class, 'store'])
      ->name('sesiones.review');
  });


  //PERFIL PARA TODOS
  Route::get('/perfil', [ProfileController::class, 'show'])->name('profile.show');
  Route::post('/perfil/personal', [ProfileController::class, 'updatePersonal'])->name('profile.personal');
  Route::post('/perfil/direccion', [ProfileController::class, 'updateAddress'])->name('profile.address');
  Route::post('/perfil/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');

});