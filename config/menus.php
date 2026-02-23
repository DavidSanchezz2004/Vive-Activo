<?php

return [
  'admin' => [
    ['label' => 'Inicio', 'route' => 'admin.dashboard', 'icon' => 'layout-dashboard'],
    ['label' => 'Pacientes', 'route' => 'admin.pacientes.index', 'icon' => 'users'],
    ['label' => 'Planes', 'route' => 'admin.planes.index', 'icon' => 'package'],
    ['label' => 'Plantillas Nutricionales', 'route' => 'admin.nutrition_templates.index', 'icon' => 'book-open'],
    ['label' => 'Plantillas de Rutinas', 'route' => 'admin.routine_templates.index', 'icon' => 'dumbbell'],
    ['label' => 'Usuarios Plataforma', 'route' => 'admin.users.index', 'icon' => 'user'],
    ['label' => 'Alumnos', 'route' => 'admin.alumnos.index', 'icon' => 'graduation-cap'],
    ['label' => 'Consultas',           'route' => 'admin.consultas.index', 'icon' => 'calendar'],
    ['label' => 'Sesiones',             'route' => 'admin.sesiones.index',  'icon' => 'clock'],
    ['label' => 'Reportes',             'route' => 'admin.reportes',        'icon' => 'file-text'],
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
    ['label' => 'Mi rutina', 'route' => 'paciente.rutina', 'icon' => 'dumbbell'],
    ['label' => 'Consultas', 'route' => 'paciente.consultas', 'icon' => 'dumbbell'],
    ['label' => 'Sesiones', 'route' => 'paciente.sesiones', 'icon' => 'calendar-check'],
  ],

  'supervisor' => [
    ['label' => 'Inicio', 'route' => 'supervisor.dashboard', 'icon' => 'layout-dashboard'],
    ['label' => 'Consultas', 'route' => 'supervisor.consultas.index', 'icon' => 'calendar'],
    ['label' => 'Sesiones',  'route' => 'supervisor.sesiones.index',  'icon' => 'clock'],
    ['label' => 'Pacientes', 'route' => 'supervisor.pacientes', 'icon' => 'users'],
    ['label' => 'Plantillas Nutricionales', 'route' => 'supervisor.nutrition_templates.index', 'icon' => 'book-open'],
    ['label' => 'Plantillas de Rutinas', 'route' => 'supervisor.routine_templates.index', 'icon' => 'dumbbell'],
    ['label' => 'Alumnos', 'route' => 'supervisor.alumnos', 'icon' => 'graduation-cap'],
    ['label' => 'Reportes', 'route' => 'supervisor.reportes', 'icon' => 'file-text'],
  ],
];