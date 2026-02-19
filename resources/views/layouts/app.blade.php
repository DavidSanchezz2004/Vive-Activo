<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>@yield('title', 'Vive Activo')</title>

  {{-- Fuentes globales --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

  @stack('styles')
</head>
<body>

  @yield('content')

  {{-- Lib global (para icons) --}}
  <script src="https://unpkg.com/lucide@latest"></script>

  {{-- JS extra por vista --}}
  @stack('scripts')

  <script>
    if (window.lucide) lucide.createIcons();
  </script>
</body>
</html>
