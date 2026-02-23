@php
  $user = auth()->user();
  $p = $user?->profile;

  $avatarUrl = ($p && $p->avatar_path)
    ? asset('storage/'.$p->avatar_path)
    : null;

  $initials = strtoupper(substr($user->name ?? 'VA', 0, 2));
@endphp

<header class="d-header">
  <button
    class="d-btn d-btn-outline d-menu-toggle"
    id="sidebar-toggle"
    type="button"
    aria-label="Abrir menú"
  >
    <i data-lucide="menu"></i>
  </button>

  <h2 class="d-page-title">@yield('page_title', 'Panel')</h2>

  <div class="header-actions">
    <div class="header-user-welcome">
      <span class="header-user-greeting">Bienvenido</span>
      <strong class="header-user-name">{{ $user->name ?? 'Vive Activo' }}</strong>
    </div>

    <div class="user-dropdown" id="userDropdown">
      <button
        class="user-dropdown-toggle"
        id="userMenuToggle"
        type="button"
        aria-haspopup="true"
        aria-expanded="false"
      >
        @if($avatarUrl)
          <img
            src="{{ $avatarUrl }}"
            alt="Foto de perfil"
            class="header-avatar-img"
          />
        @else
          <div class="d-avatar admin-avatar header-avatar">{{ $initials }}</div>
        @endif
      </button>

      <div
        class="user-dropdown-menu"
        id="userDropdownMenu"
        role="menu"
        aria-label="Menú de usuario"
      >
        <a href="{{ route('profile.show') }}" class="user-dropdown-item">
          <i data-lucide="user-circle-2"></i><span>Mi perfil</span>
        </a>

        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button class="user-logout-btn" type="submit">Cerrar Sesión</button>
        </form>
      </div>
    </div>
  </div>
</header>