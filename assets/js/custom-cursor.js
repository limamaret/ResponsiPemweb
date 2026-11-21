// Rangkul pointer polish: keep the familiar native pointer, add a restrained glow,
// a short dotted trail, and a soft click ripple on desktop.
(() => {
  const finePointer = window.matchMedia('(hover: hover) and (pointer: fine)');
  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
  if (!finePointer.matches || reducedMotion.matches) return;

  const body = document.body;

  const glow = document.createElement('div');
  glow.className = 'rangkul-pointer-glow';
  glow.setAttribute('aria-hidden', 'true');
  body.appendChild(glow);

  let lastTrail = 0;
  let lastX = -100;
  let lastY = -100;

  const interactiveSelector = 'a, button, [role="button"], .highlight-card, .feature-card, .faq-item, label';

  document.addEventListener('pointermove', (event) => {
    const x = event.clientX;
    const y = event.clientY;
    glow.style.transform = `translate3d(${x}px, ${y}px, 0) translate(-50%, -50%)`;
    glow.classList.add('is-visible');

    const now = performance.now();
    const distance = Math.hypot(x - lastX, y - lastY);
    if (distance > 18 && now - lastTrail > 34) {
      const dot = document.createElement('span');
      dot.className = 'rangkul-pointer-trail';
      dot.setAttribute('aria-hidden', 'true');
      dot.style.left = `${x}px`;
      dot.style.top = `${y}px`;
      body.appendChild(dot);
      dot.addEventListener('animationend', () => dot.remove(), { once: true });
      lastTrail = now;
      lastX = x;
      lastY = y;
    }
  }, { passive: true });

  document.addEventListener('pointerover', (event) => {
    if (event.target.closest(interactiveSelector)) glow.classList.add('is-hover');
  });

  document.addEventListener('pointerout', (event) => {
    const leaving = event.target.closest(interactiveSelector);
    const entering = event.relatedTarget && event.relatedTarget.closest
      ? event.relatedTarget.closest(interactiveSelector)
      : null;
    if (leaving && !entering) glow.classList.remove('is-hover');
  });

  document.addEventListener('pointerleave', () => glow.classList.remove('is-visible', 'is-hover'));

  document.addEventListener('click', (event) => {
    const ripple = document.createElement('span');
    ripple.className = 'rangkul-click-ripple';
    ripple.setAttribute('aria-hidden', 'true');
    ripple.style.left = `${event.clientX}px`;
    ripple.style.top = `${event.clientY}px`;
    body.appendChild(ripple);
    ripple.addEventListener('animationend', () => ripple.remove(), { once: true });
  });
})();
