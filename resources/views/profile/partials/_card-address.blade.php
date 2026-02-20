<article class="d-card profile-info-card">
  <h3 class="profile-card-title">Detalles de Dirección</h3>

  <div class="profile-info-grid">
    <div>
      <p class="profile-label">Ubicación</p>
      <p class="profile-value">{{ $p->country ?? '—' }}</p>
    </div>
    <div>
      <p class="profile-label">Provincia </p>
      <p class="profile-value">{{ $p->region ?? '—' }}</p>
    </div>
    <div>
      <p class="profile-label">Distrito</p>
      <p class="profile-value">{{ $p->district ?? '—' }}</p>
    </div>
    <div>
      <p class="profile-label">DNI / DOCUMENTO</p>
      <p class="profile-value">{{ $p->document_number ?? '—' }}</p>
    </div>
  </div>

  <div class="profile-edit-wrap">
    <button class="d-btn d-btn-outline" type="button" data-open="modalAddress">
      <i data-lucide="pencil"></i> Editar
    </button>
  </div>
</article>