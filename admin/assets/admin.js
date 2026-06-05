/* Yönetim paneli yardımcı betikleri. */
(function () {
  'use strict';

  /* Renk seçici (color input) ile metin kutusunu eşitle */
  document.querySelectorAll('.color-row').forEach(function (row) {
    var picker = row.querySelector('input[type=color]');
    var text = row.querySelector('input[type=text]');
    if (!picker || !text) return;
    picker.addEventListener('input', function () { text.value = picker.value; });
    text.addEventListener('input', function () {
      if (/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/.test(text.value)) picker.value = text.value;
    });
  });

  /* Silme onayı */
  document.querySelectorAll('form[data-confirm]').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      if (!window.confirm(form.getAttribute('data-confirm'))) e.preventDefault();
    });
  });

  /* Başlıktan otomatik slug önerisi (yalnızca slug boşsa) */
  var titleInput = document.querySelector('[data-slug-source]');
  var slugInput = document.querySelector('[data-slug-target]');
  if (titleInput && slugInput) {
    titleInput.addEventListener('input', function () {
      if (slugInput.dataset.touched === '1') return;
      slugInput.value = titleInput.value
        .toLowerCase()
        .replace(/ş/g,'s').replace(/ı/g,'i').replace(/ğ/g,'g').replace(/ç/g,'c').replace(/ö/g,'o').replace(/ü/g,'u')
        .replace(/[^a-z0-9]+/g,'-').replace(/^-+|-+$/g,'');
    });
    slugInput.addEventListener('input', function () { slugInput.dataset.touched = '1'; });
  }
})();
