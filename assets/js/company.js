// Rangkul — interaksi kecil area Company (tanpa dependensi).
document.addEventListener('DOMContentLoaded', function () {
  // Konfirmasi sebelum aksi destruktif: <form data-confirm="Pesan">
  document.querySelectorAll('form[data-confirm]').forEach(function (f) {
    f.addEventListener('submit', function (e) {
      if (!window.confirm(f.getAttribute('data-confirm'))) e.preventDefault();
    });
  });
  // Label tautan/lokasi wawancara menyesuaikan mode online/offline.
  var mode = document.getElementById('mode');
  var label = document.getElementById('loc-label');
  var input = document.getElementById('location_or_link');
  if (mode && label && input) {
    var sync = function () {
      var online = mode.value === 'online';
      label.firstChild.nodeValue = online ? 'Tautan wawancara (Zoom / Google Meet) ' : 'Lokasi wawancara ';
      input.placeholder = online ? 'https://meet.google.com/...' : 'Alamat lengkap, lantai, nama ruangan';
    };
    mode.addEventListener('change', sync); sync();
  }
});
