@php
  /** @var \App\Models\User|null $user */
  $mode = $mode ?? 'create';

  $name  = old('name',  $user->name  ?? '');
  $email = old('email', $user->email ?? '');
  $role  = old('role',  $user->role  ?? '');

  // si tienes is_active en DB:
  $isActive = old('is_active', isset($user) ? (int)($user->is_active ?? 1) : 1);
@endphp

<div class="d-form-group">
  <label class="d-label" for="name">Nombre</label>
  <input id="name" name="name" type="text" class="d-input" maxlength="120" value="{{ $name }}" required>
  @error('name') <div class="d-error">{{ $message }}</div> @enderror
</div>

<div class="d-form-group">
  <label class="d-label" for="email">Correo</label>
  <input id="email" name="email" type="email" class="d-input" maxlength="190" value="{{ $email }}" required>
  @error('email') <div class="d-error">{{ $message }}</div> @enderror
</div>

<div class="d-form-group">
  <label class="d-label" for="role">Rol</label>
  <select id="role" name="role" class="d-select" required>
    <option value="">Selecciona</option>
    <option value="admin"      @selected($role==='admin')>Administrador</option>
    <option value="supervisor" @selected($role==='supervisor')>Supervisor</option>
    <option value="student"    @selected($role==='student')>Estudiante</option>
    <option value="patient"    @selected($role==='patient')>Paciente</option>
  </select>
  @error('role') <div class="d-error">{{ $message }}</div> @enderror
</div>

{{-- Si tienes estado --}}
<div class="d-form-group">
  <label class="d-label" for="is_active">Estado</label>
  <select id="is_active" name="is_active" class="d-select">
    <option value="1" @selected((int)$isActive===1)>Activo</option>
    <option value="0" @selected((int)$isActive===0)>Inactivo</option>
  </select>
  @error('is_active') <div class="d-error">{{ $message }}</div> @enderror
</div>

<hr style="opacity:.15; margin:18px 0;">

@if($mode === 'create')
  <div class="d-form-group">
    <label class="d-label" for="password">Contraseña</label>
    <input id="password" name="password" type="password" class="d-input" minlength="8" required>
    @error('password') <div class="d-error">{{ $message }}</div> @enderror
  </div>

  <div class="d-form-group">
    <label class="d-label" for="password_confirmation">Confirmar contraseña</label>
    <input id="password_confirmation" name="password_confirmation" type="password" class="d-input" minlength="8" required>
  </div>
@else
  <div class="d-form-group">
    <label class="d-label" for="password">Nueva contraseña (opcional)</label>
    <input id="password" name="password" type="password" class="d-input" minlength="8">
    @error('password') <div class="d-error">{{ $message }}</div> @enderror
  </div>

  <div class="d-form-group">
    <label class="d-label" for="password_confirmation">Confirmar nueva contraseña</label>
    <input id="password_confirmation" name="password_confirmation" type="password" class="d-input" minlength="8">
  </div>
@endif

<button type="submit" class="{{ $submitClass ?? 'd-btn d-btn-primary full-btn' }}">
  {{ $submitText ?? 'Guardar' }}
</button>