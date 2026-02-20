<section class="d-card profile-summary">
  <div class="profile-user">
    <img src="{{ $avatar }}" alt="Foto de perfil" class="profile-user-photo" />

    <div>
      <h2 class="profile-user-name">{{ $user->name }}</h2>
      <p class="profile-user-meta">
        Cuenta personal <span>|</span> {{ $p->country ?? '—' }}
      </p>

      <form method="POST" action="{{ route('profile.avatar') }}" enctype="multipart/form-data" class="profile-upload-form">
        @csrf

        <div class="profile-upload-row">
          <label for="avatar_input" class="profile-file-label">
            <i data-lucide="camera"></i>
            <span>Seleccionar</span>
          </label>

          <input
            id="avatar_input"
            type="file"
            name="avatar"
            accept="image/*"
            class="profile-file-input"
            onchange="
              const file = this.files[0];
              document.getElementById('file_name').textContent = file ? file.name : 'Sin archivo...';
              document.getElementById('profile_submit_btn').style.display = file ? 'inline-flex' : 'none';
            "
          >

          <span id="file_name" class="profile-file-name">Sin archivo...</span>

          <button id="profile_submit_btn" class="d-btn d-btn-outline profile-upload-btn" type="submit" style="display:none;">
            <i data-lucide="upload"></i>
            Subir
          </button>
        </div>
      </form>

      @error('avatar')
        <p class="text-muted" style="margin-top:8px;">{{ $message }}</p>
      @enderror
    </div>
  </div>
</section>