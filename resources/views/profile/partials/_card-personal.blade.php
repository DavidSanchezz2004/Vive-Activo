<article class="d-card profile-info-card">
  <h3 class="profile-card-title">Información Personal</h3>

  <div class="profile-info-grid">
    <div>
      <p class="profile-label">Nombres</p>
      <p class="profile-value">{{ $p->first_name ?? '—' }}</p>
    </div>
    <div>
      <p class="profile-label">Apellidos</p>
      <p class="profile-value">{{ $p->last_name ?? '—' }}</p>
    </div>
    <div>
      <p class="profile-label">Correo</p>
      <p class="profile-value">{{ $user->email }}</p>
    </div>
    <div>
      <p class="profile-label">Teléfono</p>
      <p class="profile-value">{{ $p->phone ?? '—' }}</p>
    </div>
    <div>
      <p class="profile-label">Cargo</p>
      <p class="profile-value">{{ $roleLabel }}</p>
    </div>
  </div>

  <div class="profile-edit-wrap">
    <button class="d-btn d-btn-outline" type="button" data-open="modalPersonal">
      <i data-lucide="pencil"></i> Editar
    </button>
  </div>
</article>