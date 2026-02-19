@extends('layouts.app')

@section('title', 'Iniciar Sesión - Vive Activo')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <style>
    /* Login Specific Overrides */
    body { background: white; }

    .login-split { display: flex; min-height: 100vh; width: 100%; }

    /* Left Side */
    .login-image {
      flex: 1.2;
      background:
        linear-gradient(rgba(5, 150, 105, 0.9), rgba(6, 78, 59, 0.9)),
        url("{{ asset('images/Banner.webp') }}") center/cover no-repeat;
      display: flex; flex-direction: column; justify-content: center; align-items: center;
      color: white; padding: 60px; text-align: center;
      position: relative;
    }
    .login-image h1 { font-size: 48px; font-weight: 800; margin-bottom: 24px; line-height: 1.1; }
    .login-image p { font-size: 18px; max-width: 500px; opacity: 0.9; line-height: 1.6; }

    /* Right Side */
    .login-form-container {
      flex: 1;
      display: flex; flex-direction: column; justify-content: center; align-items: center;
      padding: 40px; background: white;
    }
    .login-card-inner { width: 100%; max-width: 420px; }

    .logo-area { margin-bottom: 40px; text-align: center; }
    .logo-area img { height: 64px; }

    .input-group-icon { position: relative; }
    .input-group-icon svg { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--d-muted); transition: .3s; }
    .input-group-icon input { padding-left: 48px; }
    .input-group-icon input:focus + svg { color: var(--d-brand); }

    @media (max-width: 900px) {
      .login-split { flex-direction: column; }
      .login-image { padding: 40px 24px; min-height: 300px; flex: none; }
      .login-image h1 { font-size: 32px; }
      .login-form-container { flex: 1; padding: 40px 24px; }
    }
  </style>
@endpush

@section('content')

  <div class="login-split">
    <!-- Visual Side -->
    <div class="login-image">
      <div>
        <h1>Tu salud, <br>nuestra prioridad.</h1>
        <p>Accede a la plataforma integral de Vive Activo para gestionar tus planes, consultas y métricas de salud en un solo lugar.</p>

        <div style="margin-top: 40px; display: flex; gap: 24px; justify-content: center; opacity: 0.8;">
          <div style="text-align: center;">
            <i data-lucide="shield-check" size="32"></i>
            <div style="font-size: 12px; font-weight: 600; text-transform: uppercase; margin-top: 8px;">100% Seguro</div>
          </div>
          <div style="text-align: center;">
            <i data-lucide="users" size="32"></i>
            <div style="font-size: 12px; font-weight: 600; text-transform: uppercase; margin-top: 8px;">Comunidad</div>
          </div>
          <div style="text-align: center;">
            <i data-lucide="heart-pulse" size="32"></i>
            <div style="font-size: 12px; font-weight: 600; text-transform: uppercase; margin-top: 8px;">Salud Real</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Form Side -->
    <div class="login-form-container">
      <div class="login-card-inner">
        <div class="logo-area">
          <img src="{{ asset('images/Logo-vive-activo.webp') }}" alt="Logo Vive Activo">
          <h2 class="mt-4" style="margin-bottom: 8px; font-size: 24px;">¡Bienvenido de nuevo!</h2>
          <p class="text-muted">Ingresa tus credenciales para continuar</p>
        </div>
          <form id="loginForm" method="POST" action="{{ route('login.post') }}">
              @csrf
              <div class="d-form-group">
                <label class="d-label">Correo Electrónico</label>
                <div class="input-group-icon">
                  <input
                    type="email"
                    name="email"
                    class="d-input"
                    placeholder="ejemplo@correo.com"
                    value="{{ old('email') }}"
                    required
                    autocomplete="email"
                  >
                  <i data-lucide="mail" size="20"></i>
                </div>

               
              </div>

              <div class="d-form-group">
                <div class="flex-between">
                  <label class="d-label">Contraseña</label>
                </div>

                <div class="input-group-icon">
                  <input
                    type="password"
                    name="password"
                    class="d-input"
                    placeholder="••••••••"
                    required
                    autocomplete="current-password"
                  >
                  <i data-lucide="lock" size="20"></i>
                </div>

                <div style="text-align: right; margin-top: 8px;">
                  <a href="#" style="font-size: 12px; color: var(--d-brand); font-weight: 600; text-decoration: none;">
                    ¿Olvidaste tu contraseña?
                  </a>
                </div>
              </div>

              <button type="submit" class="d-btn d-btn-primary" style="width: 100%; padding: 14px; font-size: 16px;">
                Ingresar <i data-lucide="arrow-right" size="18"></i>
              </button>

              @if ($errors->any())
                <p style="color: var(--d-danger); text-align: center; font-size: 14px; margin-top: 16px;">
                  <i data-lucide="alert-circle" size="14" style="vertical-align: middle;"></i>
                  Credenciales incorrectas, intenta nuevamente.
                </p>
              @endif
          </form>
        <div style="text-align: center; margin-top: 32px; font-size: 14px; color: var(--d-muted);">
          ¿Aún no tienes cuenta? <a href="#" style="color: var(--d-brand); font-weight: 600; text-decoration: none;">Contáctanos</a>
        </div>
      </div>
    </div>
  </div>

@endsection

