document.addEventListener('DOMContentLoaded', function () {
  const body = document.body;
  const toggle = document.querySelector('.admin-menu-toggle');
  const sidebar = document.getElementById('admin-sidebar');
  const backdrop = document.querySelector('.admin-sidebar-backdrop');
  function closeMenu() {
    if (!toggle || !sidebar || !backdrop) return;
    body.classList.remove('nav-open');
    toggle.setAttribute('aria-expanded', 'false');
    toggle.setAttribute('aria-label', 'Buka navigasi');
  }
  if (toggle && sidebar && backdrop) {
    toggle.addEventListener('click', function () {
      const open = body.classList.toggle('nav-open');
      toggle.setAttribute('aria-expanded', String(open));
      toggle.setAttribute('aria-label', open ? 'Tutup navigasi' : 'Buka navigasi');
    });
    backdrop.addEventListener('click', closeMenu);
    sidebar.querySelectorAll('a').forEach(function (link) { link.addEventListener('click', closeMenu); });
    document.addEventListener('keydown', function (event) { if (event.key === 'Escape') { closeMenu(); closeProfile(); } });
    window.addEventListener('resize', function () { if (window.innerWidth > 760) closeMenu(); });
  }

  // Profile actions live at the bottom of the rail, separate from workspace preferences.
  const profileButton = document.querySelector('.admin-rail-profile');
  const profileMenu = document.getElementById('admin-profile-menu');
  function closeProfile() {
    if (!profileButton || !profileMenu) return;
    profileMenu.hidden = true;
    profileButton.setAttribute('aria-expanded', 'false');
  }
  if (profileButton && profileMenu) {
    profileButton.addEventListener('click', function (event) {
      event.stopPropagation();
      const open = profileMenu.hidden;
      profileMenu.hidden = !open;
      profileButton.setAttribute('aria-expanded', String(open));
    });
    document.addEventListener('click', function (event) {
      if (!profileMenu.contains(event.target) && !profileButton.contains(event.target)) closeProfile();
    });
  }

  // Dashboard tabs.
  const tabs = Array.from(document.querySelectorAll('[data-repo-tab]'));
  const panels = Array.from(document.querySelectorAll('[data-repo-panel]'));
  if (tabs.length && panels.length) {
    tabs.forEach(function (tab, index) {
      tab.addEventListener('click', function () {
        const target = tab.getAttribute('data-repo-tab');
        tabs.forEach(function (item) {
          const active = item === tab;
          item.classList.toggle('active', active);
          item.setAttribute('aria-selected', String(active));
          item.tabIndex = active ? 0 : -1;
        });
        panels.forEach(function (panel) {
          const active = panel.getAttribute('data-repo-panel') === target;
          panel.classList.toggle('active', active);
          panel.hidden = !active;
        });
      });
      tab.addEventListener('keydown', function (event) {
        if (event.key === 'ArrowRight' || event.key === 'ArrowLeft') {
          event.preventDefault();
          const next = (index + (event.key === 'ArrowRight' ? 1 : tabs.length - 1)) % tabs.length;
          tabs[next].focus(); tabs[next].click();
        }
      });
    });
  }

  // Local, reversible workspace preferences.
  const storageKey = 'rangkul-admin-preferences-v1';
  const defaults = {theme:'light',density:'comfortable',fontSize:'normal',language:'id',reducedMotion:false,highContrast:false};
  let prefs = {...defaults};
  try { prefs = {...defaults, ...JSON.parse(localStorage.getItem(storageKey) || '{}')}; } catch (_) {}
  function applyPrefs() {
    body.dataset.theme = prefs.theme;
    body.dataset.density = prefs.density;
    body.dataset.fontSize = prefs.fontSize;
    body.classList.toggle('pref-reduced-motion', !!prefs.reducedMotion);
    body.classList.toggle('pref-high-contrast', !!prefs.highContrast);
    document.querySelectorAll('[data-theme-choice]').forEach(function (button) {
      button.classList.toggle('selected', button.dataset.themeChoice === prefs.theme);
      button.setAttribute('aria-pressed', String(button.dataset.themeChoice === prefs.theme));
    });
    document.querySelectorAll('[data-pref]').forEach(function (control) {
      const key = control.dataset.pref;
      if (control.type === 'checkbox') control.checked = !!prefs[key];
      else control.value = prefs[key] || defaults[key];
    });
  }
  function savePrefs() {
    try { localStorage.setItem(storageKey, JSON.stringify(prefs)); } catch (_) {}
    applyPrefs();
    const status = document.querySelector('.prefs-saved');
    if (status) {
      status.textContent = 'Preferensi berhasil disimpan.';
      status.classList.add('visible');
      window.clearTimeout(status._hideTimer);
      status._hideTimer = window.setTimeout(function () {
        status.classList.remove('visible');
        status.textContent = 'Perubahan tersimpan otomatis.';
      }, 2600);
    }
  }
  applyPrefs();
  document.querySelectorAll('[data-theme-choice]').forEach(function (button) {
    button.addEventListener('click', function () { prefs.theme = button.dataset.themeChoice; savePrefs(); });
  });
  document.querySelectorAll('[data-pref]').forEach(function (control) {
    control.addEventListener('change', function () {
      prefs[control.dataset.pref] = control.type === 'checkbox' ? control.checked : control.value;
      savePrefs();
    });
  });
  const reset = document.querySelector('[data-pref-reset]');
  if (reset) reset.addEventListener('click', function () { prefs = {...defaults}; savePrefs(); });

  // Rangkul-styled confirmation dialog (never use the browser's native confirm()).
  let pendingConfirmation = null;
  const confirmLayer = document.createElement('div');
  confirmLayer.className = 'rangkul-confirm-layer';
  confirmLayer.hidden = true;
  confirmLayer.innerHTML = `
    <section class="rangkul-confirm-dialog" role="dialog" aria-modal="true" aria-labelledby="rangkul-confirm-title" aria-describedby="rangkul-confirm-message" tabindex="-1">
      <div class="rangkul-confirm-mark" aria-hidden="true"><span>!</span></div>
      <div class="rangkul-confirm-copy">
        <h2 id="rangkul-confirm-title">Konfirmasi tindakan</h2>
        <p id="rangkul-confirm-message"></p>
      </div>
      <div class="rangkul-confirm-actions">
        <button type="button" class="rangkul-confirm-cancel">Batal</button>
        <button type="button" class="rangkul-confirm-accept">Lanjutkan</button>
      </div>
    </section>`;
  document.body.appendChild(confirmLayer);
  const confirmDialog = confirmLayer.querySelector('.rangkul-confirm-dialog');
  const confirmCancel = confirmLayer.querySelector('.rangkul-confirm-cancel');
  const confirmAccept = confirmLayer.querySelector('.rangkul-confirm-accept');
  function closeConfirmation() {
    confirmLayer.hidden = true;
    document.body.classList.remove('rangkul-confirm-open');
    const previous = pendingConfirmation && pendingConfirmation.button;
    pendingConfirmation = null;
    if (previous && previous.isConnected) previous.focus();
  }
  function showConfirmation(button) {
    const form = button.form;
    if (!form) return;
    pendingConfirmation = {button, form};
    confirmLayer.querySelector('#rangkul-confirm-title').textContent = button.dataset.confirmTitle || 'Konfirmasi tindakan';
    confirmLayer.querySelector('#rangkul-confirm-message').textContent = button.dataset.confirm || 'Yakin ingin melanjutkan tindakan ini?';
    confirmAccept.textContent = button.dataset.confirmLabel || 'Ya, lanjutkan';
    confirmAccept.classList.toggle('is-danger', button.dataset.confirmTone === 'danger');
    confirmLayer.hidden = false;
    document.body.classList.add('rangkul-confirm-open');
    confirmDialog.focus();
    confirmCancel.focus();
  }
  document.addEventListener('click', function (event) {
    const button = event.target.closest('button[data-confirm]');
    if (!button) return;
    event.preventDefault();
    showConfirmation(button);
  }, true);
  confirmCancel.addEventListener('click', closeConfirmation);
  confirmLayer.addEventListener('click', function (event) {
    if (event.target === confirmLayer) closeConfirmation();
  });
  confirmAccept.addEventListener('click', function () {
    if (!pendingConfirmation) return;
    const current = pendingConfirmation;
    confirmLayer.hidden = true;
    document.body.classList.remove('rangkul-confirm-open');
    pendingConfirmation = null;
    if (current.button.isConnected && current.form.isConnected) {
      if (typeof current.form.requestSubmit === 'function') current.form.requestSubmit(current.button);
      else current.button.click();
    }
  });
  document.addEventListener('keydown', function (event) {
    if (confirmLayer.hidden) return;
    if (event.key === 'Escape') { event.preventDefault(); closeConfirmation(); }
    if (event.key === 'Tab') {
      event.preventDefault();
      (document.activeElement === confirmCancel ? confirmAccept : confirmCancel).focus();
    }
  });

});
// Profile photo cropper: choose -> position/zoom -> confirm -> upload.
(function () {
  const source = document.getElementById('profile-photo-source');
  const cropped = document.getElementById('profile-photo-cropped');
  const form = document.getElementById('account-photo-form');
  const modal = document.getElementById('photo-crop-modal');
  const stage = document.getElementById('photo-crop-stage');
  const image = document.getElementById('photo-crop-image');
  const zoomInput = document.getElementById('photo-crop-zoom');
  const apply = document.getElementById('photo-crop-apply');
  const cancel = document.getElementById('photo-crop-cancel');
  const close = document.getElementById('photo-crop-close');
  const submit = document.getElementById('profile-photo-submit');
  const selected = document.getElementById('account-photo-selected');
  if (!source || !cropped || !form || !modal || !stage || !image) return;

  let objectUrl = null;
  let scale = 1;
  let baseScale = 1;
  let x = 0;
  let y = 0;
  let dragging = false;
  let startX = 0;
  let startY = 0;
  let originX = 0;
  let originY = 0;
  let naturalW = 0;
  let naturalH = 0;
  const stageSize = () => stage.clientWidth;

  function draw() {
    const w = naturalW * scale;
    const h = naturalH * scale;
    image.style.width = w + 'px';
    image.style.height = h + 'px';
    image.style.left = x + 'px';
    image.style.top = y + 'px';
  }
  function openCrop(file) {
    if (objectUrl) URL.revokeObjectURL(objectUrl);
    objectUrl = URL.createObjectURL(file);
    image.onload = function () {
      naturalW = image.naturalWidth;
      naturalH = image.naturalHeight;
      // The stage has zero layout width while its parent modal is hidden.
      // Reveal first, then measure on the next frame before sizing the image.
      modal.hidden = false;
      document.body.classList.add('photo-crop-open');
      requestAnimationFrame(function () {
        const size = stageSize();
        if (!size || !naturalW || !naturalH) {
          selected.textContent = 'Pratinjau foto gagal dimuat. Coba pilih foto lain.';
          closeCrop();
          return;
        }
        baseScale = size / Math.min(naturalW, naturalH);
        zoomInput.value = '1';
        scale = baseScale;
        x = (size - naturalW * scale) / 2;
        y = (size - naturalH * scale) / 2;
        draw();
      });
    };
    image.src = objectUrl;
  }
  function closeCrop() {
    modal.hidden = true;
    document.body.classList.remove('photo-crop-open');
  }
  // Two photo sources: local upload or live camera. Both feed the same cropper.
  const uploadButton = document.getElementById('profile-photo-upload');
  const cameraButton = document.getElementById('profile-photo-camera');
  const cameraModal = document.getElementById('photo-camera-modal');
  const cameraVideo = document.getElementById('photo-camera-video');
  const cameraMessage = document.getElementById('photo-camera-message');
  const captureButton = document.getElementById('photo-camera-capture');
  let cameraStream = null;
  let cameraRequest = 0;
  if (uploadButton) uploadButton.addEventListener('click', function () { source.click(); });
  function stopCamera() {
    cameraRequest++;
    if (cameraStream) { cameraStream.getTracks().forEach(function (track) { track.stop(); }); cameraStream = null; }
    if (cameraVideo) { cameraVideo.pause(); cameraVideo.srcObject = null; }
    if (cameraModal) cameraModal.hidden = true;
    if (captureButton) captureButton.disabled = true;
    document.body.classList.remove('photo-crop-open');
  }
  if (cameraButton && cameraModal && cameraVideo && captureButton) {
    cameraButton.addEventListener('click', async function () {
      cameraModal.hidden = false;
      cameraMessage.textContent = 'Mengaktifkan kamera…';
      captureButton.disabled = true;
      document.body.classList.add('photo-crop-open');
      const request = ++cameraRequest;
      if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        cameraMessage.textContent = 'Kamera tidak tersedia. Gunakan localhost atau HTTPS dan pastikan perangkat memiliki kamera.';
        return;
      }
      try {
        const stream = await navigator.mediaDevices.getUserMedia({video:{facingMode:'user'},audio:false});
        if (request !== cameraRequest || cameraModal.hidden) { stream.getTracks().forEach(function (track) {track.stop();}); return; }
        cameraStream = stream;
        cameraVideo.srcObject = stream;
        await cameraVideo.play();
        if (request !== cameraRequest) return;
        cameraMessage.textContent = 'Kamera siap. Klik Ambil Foto untuk melanjutkan.';
        captureButton.disabled = false;
      } catch (error) {
        if (request !== cameraRequest) return;
        cameraMessage.textContent = 'Kamera tidak dapat diakses. Periksa izin kamera di browser atau gunakan Unggah Gambar.';
      }
    });
    captureButton.addEventListener('click', function () {
      if (!cameraStream || !cameraVideo.videoWidth || !cameraVideo.videoHeight) return;
      const canvas = document.createElement('canvas');
      canvas.width = cameraVideo.videoWidth;
      canvas.height = cameraVideo.videoHeight;
      const context = canvas.getContext('2d');
      context.drawImage(cameraVideo, 0, 0, canvas.width, canvas.height);
      canvas.toBlob(function (blob) {
        if (!blob) { cameraMessage.textContent = 'Foto gagal diambil. Coba lagi.'; return; }
        stopCamera();
        openCrop(new File([blob], 'foto-kamera.jpg', {type:'image/jpeg'}));
      }, 'image/jpeg', 0.92);
    });
    document.getElementById('photo-camera-close').addEventListener('click', stopCamera);
    document.getElementById('photo-camera-cancel').addEventListener('click', stopCamera);
    cameraModal.addEventListener('click', function (event) { if (event.target === cameraModal) stopCamera(); });
    document.addEventListener('keydown', function (event) { if (event.key === 'Escape' && !cameraModal.hidden) stopCamera(); });
    window.addEventListener('pagehide', stopCamera);
  }
  source.addEventListener('change', function () {
    const file = source.files && source.files[0];
    if (!file) return;
    if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type) || file.size > 2 * 1024 * 1024) {
      selected.textContent = 'Format JPG, PNG, WebP dengan ukuran maksimal 2 MB.';
      source.value = '';
      return;
    }
    openCrop(file);
  });
  zoomInput.addEventListener('input', function () {
    const nextScale = baseScale * Number(zoomInput.value);
    const center = stageSize() / 2;
    const ratio = nextScale / scale;
    x = center - (center - x) * ratio;
    y = center - (center - y) * ratio;
    scale = nextScale;
    draw();
  });
  stage.addEventListener('pointerdown', function (event) {
    dragging = true;
    startX = event.clientX;
    startY = event.clientY;
    originX = x;
    originY = y;
    stage.setPointerCapture(event.pointerId);
  });
  stage.addEventListener('pointermove', function (event) {
    if (!dragging) return;
    x = originX + event.clientX - startX;
    y = originY + event.clientY - startY;
    draw();
  });
  ['pointerup', 'pointercancel', 'lostpointercapture'].forEach(function (name) {
    stage.addEventListener(name, function () { dragging = false; });
  });
  function dismiss() { closeCrop(); source.value = ''; }
  cancel.addEventListener('click', dismiss);
  close.addEventListener('click', dismiss);
  modal.addEventListener('click', function (event) { if (event.target === modal) dismiss(); });
  document.addEventListener('keydown', function (event) { if (event.key === 'Escape' && !modal.hidden) dismiss(); });
  apply.addEventListener('click', function () {
    const size = stageSize();
    const sx = Math.max(0, Math.min(naturalW - size / scale, -x / scale));
    const sy = Math.max(0, Math.min(naturalH - size / scale, -y / scale));
    const cropSize = Math.min(size / scale, naturalW - sx, naturalH - sy);
    const canvas = document.createElement('canvas');
    canvas.width = 512;
    canvas.height = 512;
    const ctx = canvas.getContext('2d');
    ctx.imageSmoothingEnabled = true;
    ctx.imageSmoothingQuality = 'high';
    ctx.drawImage(image, sx, sy, cropSize, cropSize, 0, 0, 512, 512);
    canvas.toBlob(function (blob) {
      if (!blob) { selected.textContent = 'Foto gagal diproses. Coba pilih foto lain.'; return; }
      const file = new File([blob], 'rangkul-profile.jpg', { type: 'image/jpeg' });
      const transfer = new DataTransfer();
      transfer.items.add(file);
      cropped.files = transfer.files;
      selected.textContent = 'Foto siap diunggah · hasil crop 1:1';
      submit.disabled = false;
      closeCrop();
    }, 'image/jpeg', 0.92);
  });
  form.addEventListener('submit', function (event) {
    if (!cropped.files || !cropped.files.length) {
      event.preventDefault();
      selected.textContent = 'Pilih dan sesuaikan foto terlebih dahulu.';
    }
  });
})();


// Rangkul feedback notifications: enter, auto-dismiss, and exit.
(function () {
  const notices = document.querySelectorAll('.notice');
  notices.forEach(function (notice, index) {
    notice.classList.add('notice-animated');
    notice.setAttribute('aria-live', notice.classList.contains('error') ? 'assertive' : 'polite');
    notice.setAttribute('aria-atomic', 'true');

    const close = document.createElement('button');
    close.type = 'button';
    close.className = 'notice-close';
    close.setAttribute('aria-label', 'Tutup notifikasi');
    close.innerHTML = '<svg viewBox="0 0 20 20" aria-hidden="true"><path d="m5 5 10 10M15 5 5 15"/></svg>';
    close.addEventListener('click', function () { dismiss(notice); });
    notice.appendChild(close);

    requestAnimationFrame(function () {
      notice.classList.add('notice-visible');
    });

    const delay = notice.classList.contains('error') ? 6000 : 4200;
    const timer = window.setTimeout(function () { dismiss(notice); }, delay);
    notice.addEventListener('mouseenter', function () { window.clearTimeout(timer); });
    notice.addEventListener('mouseleave', function () {
      window.setTimeout(function () { dismiss(notice); }, 1400);
    });
  });

  function dismiss(notice) {
    if (!notice || notice.classList.contains('notice-leaving')) return;
    notice.classList.remove('notice-visible');
    notice.classList.add('notice-leaving');
    window.setTimeout(function () {
      if (notice.parentNode) notice.parentNode.removeChild(notice);
    }, 320);
  }
})();
