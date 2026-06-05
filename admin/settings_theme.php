<?php
/** Görünüm (tema): renkler, fontlar, boyutlar. */
require __DIR__ . '/../includes/init.php';
require_admin();

$colorFields = [
    'theme_bg_color'       => 'Arka Plan Rengi',
    'theme_text_color'     => 'Yazı Rengi',
    'theme_accent_color'   => 'Vurgu Rengi (butonlar)',
    'theme_btn_text_color' => 'Buton Yazı Rengi',
    'theme_muted_color'    => 'Soluk Yazı Rengi',
    'theme_border_color'   => 'Çerçeve/Çizgi Rengi',
    'theme_card_bg'        => 'Kart Arka Planı',
];

$sizeFields = [
    'theme_font_size_base' => ['Temel Yazı Boyutu', 'örn: 17px'],
    'theme_font_size_h1'   => ['Ana Başlık Boyutu', 'örn: 3.4rem'],
    'theme_header_height'  => ['Üst Menü Yüksekliği (kalınlık)', 'örn: 64px'],
    'theme_radius'         => ['Köşe Yuvarlaklığı', 'örn: 10px'],
    'theme_btn_radius'     => ['Buton Köşe Yuvarlaklığı', 'örn: 100px'],
    'theme_max_width'      => ['Sayfa Genişliği', 'örn: 1180px'],
    'theme_section_gap'    => ['Bölüm Aralığı', 'örn: 110px'],
];

$fontKeys = ['theme_font_heading_name', 'theme_font_body_name'];

$googleFonts = [
    'Playfair Display', 'Cormorant Garamond', 'EB Garamond', 'Libre Baskerville',
    'Lora', 'Merriweather', 'PT Serif', 'Crimson Text', 'DM Serif Display',
    'Inter', 'Montserrat', 'Poppins', 'Roboto', 'Open Sans', 'Raleway',
    'Nunito', 'Work Sans', 'DM Sans', 'Source Sans 3', 'Josefin Sans',
];

$allThemeKeys = array_merge(array_keys($colorFields), array_keys($sizeFields), $fontKeys);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (!empty($_POST['reset_defaults'])) {
        foreach (theme_defaults() as $k => $v) {
            set_setting($k, $v);
        }
        flash('Tema varsayılanlara döndürüldü.');
    } else {
        foreach ($allThemeKeys as $key) {
            if (isset($_POST[$key])) {
                set_setting($key, trim((string) $_POST[$key]));
            }
        }
        flash('Görünüm kaydedildi.');
    }
    redirect(url('admin/settings_theme.php'));
}

$admin_title  = 'Görünüm';
$admin_active = 'theme';
include __DIR__ . '/partials/admin_head.php';
?>
<div class="page-head">
  <div><h1>Görünüm</h1><p class="adm-sub">Renkleri, yazı tiplerini ve boyutları değiştirin. Değişiklikler kaydettiğinizde siteye anında yansır.</p></div>
  <a class="btn btn--ghost btn--sm" href="<?= e(url('')) ?>" target="_blank">Siteyi Önizle ↗</a>
</div>

<form method="post">
  <?= csrf_field() ?>

  <div class="card">
    <h2>Renkler</h2>
    <div class="grid2">
      <?php foreach ($colorFields as $key => $label): ?>
        <?php $val = theme_value($key); ?>
        <div class="field">
          <label><?= e($label) ?></label>
          <div class="color-row">
            <input type="color" value="<?= e($val) ?>">
            <input type="text" name="<?= e($key) ?>" value="<?= e($val) ?>">
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="card">
    <h2>Yazı Tipleri (Google Fonts)</h2>
    <div class="grid2">
      <?php
      $fontLabels = ['theme_font_heading_name' => 'Başlık Fontu', 'theme_font_body_name' => 'Metin Fontu'];
      foreach ($fontKeys as $key):
          $current = theme_value($key);
          $options = $googleFonts;
          if (!in_array($current, $options, true)) { array_unshift($options, $current); }
      ?>
        <div class="field">
          <label><?= e($fontLabels[$key]) ?></label>
          <select name="<?= e($key) ?>">
            <?php foreach ($options as $font): ?>
              <option value="<?= e($font) ?>" <?= $font === $current ? 'selected' : '' ?>><?= e($font) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="hint">Listede olmayan bir Google Fonts yazı tipini kullanmak isterseniz, fonts.google.com'daki tam adını yazmanız yeterlidir (gelişmiş).</div>
  </div>

  <div class="card">
    <h2>Boyutlar ve Aralıklar</h2>
    <div class="grid2">
      <?php foreach ($sizeFields as $key => $def): ?>
        <?php [$label, $hint] = $def; ?>
        <div class="field">
          <label><?= e($label) ?></label>
          <input type="text" name="<?= e($key) ?>" value="<?= e(theme_value($key)) ?>">
          <div class="hint"><?= e($hint) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="actions">
    <button class="btn" type="submit">Kaydet</button>
    <button class="btn btn--ghost" type="submit" name="reset_defaults" value="1"
            onclick="return confirm('Tüm tema ayarları varsayılana dönecek. Emin misiniz?')">Varsayılanlara Dön</button>
  </div>
</form>

<?php include __DIR__ . '/partials/admin_foot.php'; ?>
