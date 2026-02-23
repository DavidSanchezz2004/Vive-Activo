<div class="profile-modal" id="modalPersonal" aria-hidden="true">
  <div class="profile-modal__overlay" data-close="modalPersonal"></div>

  <div class="profile-modal__card" role="dialog" aria-modal="true">
    <div class="profile-modal__header">
      <h3>Editar información personal</h3>
      <button type="button" class="profile-modal__close" data-close="modalPersonal">
        <i data-lucide="x"></i>
      </button>
    </div>

    <form method="POST" action="{{ route('profile.personal') }}" class="profile-form">
      @csrf

      <div class="profile-form-grid">
        <div>
          <p class="profile-label">Nombres</p>
          <input class="profile-input" name="first_name" value="{{ old('first_name', $p->first_name) }}">
          @error('first_name') <p class="profile-error">{{ $message }}</p> @enderror
        </div>

        <div>
          <p class="profile-label">Apellidos</p>
          <input class="profile-input" name="last_name" value="{{ old('last_name', $p->last_name) }}">
          @error('last_name') <p class="profile-error">{{ $message }}</p> @enderror
        </div>

        <div>
          <p class="profile-label">Teléfono</p>
          <input class="profile-input" name="phone" value="{{ old('phone', $p->phone) }}">
          @error('phone') <p class="profile-error">{{ $message }}</p> @enderror
        </div>

        <div>
          <p class="profile-label">Tipo de documento</p>
          @php $dt = old('document_type', $p->document_type); @endphp
          <select class="profile-input" name="document_type">
            <option value="" {{ $dt ? '' : 'selected' }}>—</option>
            <option value="DNI" {{ $dt === 'DNI' ? 'selected' : '' }}>DNI</option>
            <option value="CE"  {{ $dt === 'CE' ? 'selected' : '' }}>CE</option>
            <option value="PAS" {{ $dt === 'PAS' ? 'selected' : '' }}>PAS</option>
          </select>
          @error('document_type') <p class="profile-error">{{ $message }}</p> @enderror
        </div>

        <div>
          <p class="profile-label">N° documento</p>
          <input class="profile-input" name="document_number" value="{{ old('document_number', $p->document_number) }}">
          @error('document_number') <p class="profile-error">{{ $message }}</p> @enderror
        </div>
      </div>

      <div class="profile-modal__actions">
        <button type="button" class="d-btn d-btn-outline" data-close="modalPersonal">Cancelar</button>
        <button type="submit" class="d-btn">Guardar</button>
      </div>
    </form>
  </div>
</div>