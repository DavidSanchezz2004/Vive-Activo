<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>@yield('title', 'Vive Activo')</title>

  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <script src="https://unpkg.com/lucide@latest"></script>

  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
  @stack('styles')
</head>

<body>
  <div class="d-layout">
    @include('partials.sidebar')

    <main class="d-main">
      @include('partials.header')

      <section class="d-content">
        <div class="d-container">
          @yield('content')
        </div>
      </section>
    </main>
  </div>

  <div class="d-overlay" id="d-overlay"></div>

  <script>
    const sidebarThemeToggle = document.getElementById("sidebarThemeToggle");
    const savedSidebarTheme = localStorage.getItem("sidebar-theme");

    function applySidebarTheme(theme) {
      document.body.classList.toggle("sidebar-dark", theme === "dark");
      if (sidebarThemeToggle) sidebarThemeToggle.checked = theme === "dark";
      if (window.lucide) lucide.createIcons();
    }

    applySidebarTheme(savedSidebarTheme === "dark" ? "dark" : "light");

    if (sidebarThemeToggle) {
      sidebarThemeToggle.addEventListener("change", function () {
        const theme = this.checked ? "dark" : "light";
        localStorage.setItem("sidebar-theme", theme);
        applySidebarTheme(theme);
      });
    }

    const userMenuToggle = document.getElementById("userMenuToggle");
    const userDropdownMenu = document.getElementById("userDropdownMenu");

    if (userMenuToggle && userDropdownMenu) {
      userMenuToggle.addEventListener("click", function (event) {
        event.stopPropagation();
        const isOpen = userDropdownMenu.classList.toggle("open");
        userMenuToggle.setAttribute("aria-expanded", isOpen ? "true" : "false");
      });

      document.addEventListener("click", function (event) {
        if (!event.target.closest("#userDropdown")) {
          userDropdownMenu.classList.remove("open");
          userMenuToggle.setAttribute("aria-expanded", "false");
        }
      });

      document.addEventListener("keydown", function (event) {
        if (event.key === "Escape") {
          userDropdownMenu.classList.remove("open");
          userMenuToggle.setAttribute("aria-expanded", "false");
        }
      });
    }

    if (window.lucide) lucide.createIcons();
  </script>

  {{-- Aquí se inyectan scripts desde vistas (modales, etc.) --}}
  @stack('scripts')
</body>
</html>