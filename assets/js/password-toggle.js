document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('input[type="password"]').forEach((input) => {
    const field = document.createElement('span');
    field.className = 'password-field';
    input.parentNode.insertBefore(field, input);
    field.appendChild(input);
    const toggle = document.createElement('button');
    toggle.type = 'button';
    toggle.className = 'password-toggle';
    toggle.setAttribute('aria-label', 'Tampilkan kata sandi');
    toggle.setAttribute('aria-pressed', 'false');
    toggle.innerHTML = '<span class="toggle-icon">◉</span><span class="toggle-text">Tampilkan</span>';
    toggle.addEventListener('click', () => {
      const show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      toggle.setAttribute('aria-pressed', String(show));
      toggle.setAttribute('aria-label', show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
      toggle.querySelector('.toggle-text').textContent = show ? 'Sembunyikan' : 'Tampilkan';
    });
    field.appendChild(toggle);
  });
});