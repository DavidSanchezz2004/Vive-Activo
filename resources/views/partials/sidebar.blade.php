@php
  $user = auth()->user();

  // role es Enum (App\Enums\UserRole) => sacamos el string value
  $role = $user?->role?->value ?? 'patient';

  // menu por rol (config/menus.php debe tener keys: admin, supervisor, student, patient)
  $menu = config("menus.$role", []);

  $roleLabel = match($role) {
    'admin' => 'Panel Admin',
    'student' => 'Panel Estudiante',
    'supervisor' => 'Panel Supervisor',
    default => 'Panel Paciente',
  };
@endphp

<aside class="d-sidebar" id="sidebar">
  <div class="d-brand">
    <div class="d-brand-top">
      <img
        src="{{ asset('images/Logo-vive-activo.webp') }}"
        alt="Vive Activo"
        class="d-brand-logo"
      />
      <strong class="d-brand-name">Vive Activo</strong>

      <div class="d-brand-welcome">
        <span class="d-brand-greeting">Bienvenido</span>
        <strong class="d-brand-role">{{ $roleLabel }}</strong>
      </div>
    </div>
  </div>

  <nav class="d-nav">
    <span class="d-nav-title">NAVEGACIÓN</span>

    @foreach($menu as $item)
      <a
        href="{{ route($item['route']) }}"
        class="d-nav-item {{ request()->routeIs($item['route']) ? 'active' : '' }}"
      >
        <i data-lucide="{{ $item['icon'] }}"></i>
        <span>{{ $item['label'] }}</span>
      </a>
    @endforeach

    <div class="sidebar-theme-card">
      <div class="sidebar-theme-left">
        <i data-lucide="moon-star" class="sidebar-theme-icon"></i>
        <span>Modo noche</span>
      </div>
      <label class="sidebar-theme-toggle" for="sidebarThemeToggle">
        <input type="checkbox" id="sidebarThemeToggle" />
        <span class="sidebar-theme-slider"></span>
      </label>
    </div>
  </nav>
</aside>