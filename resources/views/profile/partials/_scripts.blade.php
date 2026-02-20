<script>
  function openModal(id){
    const el = document.getElementById(id);
    if(!el) return;
    el.classList.add('open');
    el.setAttribute('aria-hidden','false');
    document.body.style.overflow = 'hidden';
    if (window.lucide) lucide.createIcons();
  }

  function closeModal(id){
    const el = document.getElementById(id);
    if(!el) return;
    el.classList.remove('open');
    el.setAttribute('aria-hidden','true');
    document.body.style.overflow = '';
  }

  document.addEventListener('click', (e) => {
    const open = e.target.closest('[data-open]');
    if(open){ openModal(open.getAttribute('data-open')); return; }

    const close = e.target.closest('[data-close]');
    if(close){ closeModal(close.getAttribute('data-close')); return; }
  });

  document.addEventListener('keydown', (e) => {
    if(e.key === 'Escape'){
      closeModal('modalPersonal');
      closeModal('modalAddress');
    }
  });

  // Si hay errores, abre modal correcto
  @if($errors->any())
    @if($errors->hasAny(['first_name','last_name','phone','document_type','document_number']))
    openModal('modalPersonal');
  @endif

  @if($errors->hasAny(['country','region','district','address_line']))
    openModal('modalAddress');
@endif
  @endif

  document.querySelectorAll('.profile-input').forEach(input => {
  input.addEventListener('input', function(){
    const error = this.parentElement.querySelector('.profile-error');
    if(error) error.remove();
  });
});
</script>