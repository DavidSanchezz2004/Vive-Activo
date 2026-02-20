<div class="profile-modal" id="modalAddress" aria-hidden="true">
  <div class="profile-modal__overlay" data-close="modalAddress"></div>

  <div class="profile-modal__card" role="dialog" aria-modal="true">
    <div class="profile-modal__header">
      <h3>Editar dirección</h3>
      <button type="button" class="profile-modal__close" data-close="modalAddress">
        <i data-lucide="x"></i>
      </button>
    </div>

    <form method="POST" action="{{ route('profile.address') }}" class="profile-form">
      @csrf

      <div class="profile-form-grid">
        <div>
          <p class="profile-label">País</p>
          <input class="profile-input" name="country" value="{{ old('country', $p->country) }}">
          @error('country') <p class="profile-error">{{ $message }}</p> @enderror
        </div>

        <div>
          <p class="profile-label">Provincia</p>
          <input class="profile-input" name="region" value="{{ old('region', $p->region) }}">
          @error('region') <p class="profile-error">{{ $message }}</p> @enderror
        </div>

        <div>
          <p class="profile-label">Distrito</p>
          <input class="profile-input" name="district" value="{{ old('district', $p->district) }}">
          @error('district') <p class="profile-error">{{ $message }}</p> @enderror
        </div>

        <div>
          <p class="profile-label">Dirección (opcional)</p>
          <input class="profile-input" name="address_line" value="{{ old('address_line', $p->address_line) }}">
          @error('address_line') <p class="profile-error">{{ $message }}</p> @enderror
        </div>
      </div>

      <div class="profile-modal__actions">
        <button type="button" class="d-btn d-btn-outline" data-close="modalAddress">Cancelar</button>
        <button type="submit" class="d-btn">Guardar</button>
      </div>
    </form>
  </div>
</div>