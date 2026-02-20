<?php

return [
  'admin' => [
    ['label' => 'Inicio', 'route' => 'admin.dashboard', 'icon' => 'layout-dashboard'],
    ['label' => 'Pacientes', 'route' => 'admin.pacientes.index', 'icon' => 'users'],
    ['label' => 'Usuarios Plataforma', 'route' => 'admin.users.index', 'icon' => 'user'],
    ['label' => 'Alumnos', 'route' => 'admin.estudiante.index', 'icon' => 'graduation-cap'],
    ['label' => 'Reportes', 'route' => 'admin.reportes', 'icon' => 'file-text'],
    ['label' => 'Configuración', 'route' => 'admin.config', 'icon' => 'settings'],
  ],

  'student' => [
    ['label' => 'Inicio', 'route' => 'estudiante.dashboard', 'icon' => 'layout-dashboard'],
    ['label' => 'Pacientes', 'route' => 'estudiante.pacientes', 'icon' => 'users'],
    ['label' => 'Sesiones', 'route' => 'estudiante.sesiones', 'icon' => 'calendar'],
  ],

  'patient' => [
    ['label' => 'Inicio', 'route' => 'paciente.dashboard', 'icon' => 'layout-dashboard'],
    ['label' => 'Mi plan', 'route' => 'paciente.plan', 'icon' => 'file-text'],
    ['label' => 'Rutinas', 'route' => 'paciente.rutinas', 'icon' => 'dumbbell'],
    ['label' => 'Citas', 'route' => 'paciente.citas', 'icon' => 'calendar-check'],
  ],

  'supervisor' => [
    ['label' => 'Inicio', 'route' => 'supervisor.dashboard', 'icon' => 'layout-dashboard'],
    ['label' => 'Pacientes', 'route' => 'supervisor.pacientes', 'icon' => 'users'],
    ['label' => 'Alumnos', 'route' => 'supervisor.alumnos', 'icon' => 'graduation-cap'],
    ['label' => 'Reportes', 'route' => 'supervisor.reportes', 'icon' => 'file-text'],
  ],
];