<?php
require_once __DIR__ . '/_layout.php';
admin_header('Preferensi Tampilan','preferences');
?>
<section class="admin-page-intro"><div><h2>Atur ruang kerja sesuai kebutuhanmu.</h2><p>Perubahan hanya berlaku di browser ini dan tidak mengubah tampilan pencari kerja atau recruiter.</p></div></section>
<section class="prefs-page">
  <div class="prefs-grid">
    <section class="prefs-card">
      <div class="prefs-card-heading"><span class="prefs-icon">◐</span><div><h3>Tampilan</h3><p>Pilih suasana visual dashboard.</p></div></div>
      <div class="prefs-control"><div><b>Mode warna</b><small>Gunakan tampilan terang atau gelap.</small></div><div class="prefs-choice" role="group" aria-label="Mode warna"><button type="button" data-theme-choice="light" class="selected">Terang</button><button type="button" data-theme-choice="dark">Gelap</button></div></div>
      <div class="prefs-control"><div><b>Kepadatan konten</b><small>Atur jarak antar elemen dan baris tabel.</small></div><select data-pref="density"><option value="comfortable">Nyaman</option><option value="compact">Ringkas</option></select></div>
    </section>
    <section class="prefs-card">
      <div class="prefs-card-heading"><span class="prefs-icon">Aa</span><div><h3>Tipografi & bahasa</h3><p>Sesuaikan keterbacaan antarmuka.</p></div></div>
      <div class="prefs-control"><div><b>Ukuran teks</b><small>Atur skala teks untuk seluruh workspace.</small></div><select data-pref="fontSize"><option value="normal">Normal</option><option value="large">Besar</option><option value="small">Kecil</option></select></div>
      <div class="prefs-control"><div><b>Bahasa antarmuka</b><small>Bahasa yang tersedia saat ini.</small></div><select data-pref="language"><option value="id">Bahasa Indonesia</option><option value="en" disabled>English — segera hadir</option></select></div>
    </section>
    <section class="prefs-card">
      <div class="prefs-card-heading"><span class="prefs-icon">◉</span><div><h3>Aksesibilitas</h3><p>Kurangi gangguan dan tingkatkan kenyamanan.</p></div></div>
      <label class="prefs-toggle-row"><span><b>Kurangi animasi</b><small>Minimalkan gerakan dan transisi.</small></span><input type="checkbox" data-pref="reducedMotion"><i></i></label>
      <label class="prefs-toggle-row"><span><b>Kontras lebih tinggi</b><small>Pertegas teks, garis, dan elemen kontrol.</small></span><input type="checkbox" data-pref="highContrast"><i></i></label>
    </section>
    <section class="prefs-card prefs-reset-card">
      <div class="prefs-card-heading"><span class="prefs-icon">↺</span><div><h3>Preferensi browser</h3><p>Pengaturan ini hanya berlaku pada perangkat dan browser yang sedang digunakan.</p></div></div>
      <button type="button" class="prefs-reset" data-pref-reset>Reset ke pengaturan awal</button>
      <span class="prefs-saved" role="status" aria-live="polite">Perubahan tersimpan otomatis.</span>
    </section>
  </div>
</section>
<?php admin_footer(); ?>
