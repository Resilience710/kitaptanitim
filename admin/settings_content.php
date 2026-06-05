<?php
/** İçerik (metinler, linkler, logo/favicon) düzenleme. */
require __DIR__ . '/../includes/init.php';
require_admin();

/** Düzenlenebilir metin alanları: anahtar => [etiket, tip, ipucu] */
$fields = [
    'site_title'         => ['Site Başlığı', 'text', 'Tarayıcı sekmesinde ve sosyal paylaşımlarda görünür.'],
    'logo_text'          => ['Logo Metni', 'text', 'Logo görseli yüklemezseniz üst menüde bu yazı görünür.'],
    'hero_eyebrow'       => ['Üst Etiket (küçük yazı)', 'text', 'Başlığın üstündeki küçük yazı. Örn: YENİ KİTAP'],
    'hero_heading'       => ['Ana Başlık', 'text', 'Ana sayfadaki büyük başlık.'],
    'hero_subheading'    => ['Ana Açıklama', 'textarea', 'Başlığın altındaki tanıtım cümlesi.'],
    'btn_buy_label'      => ['Satın Al Butonu Yazısı', 'text', 'Etsy mağazasına yönlendiren buton.'],
    'btn_detail_label'   => ['İncele Butonu Yazısı', 'text', 'Detay sayfasına yönlendiren buton.'],
    'about_title'        => ['Hakkında Başlığı', 'text', ''],
    'about_text'         => ['Hakkında Metni', 'textarea', 'Kitap hakkında daha uzun açıklama.'],
    'collection_title'   => ['Koleksiyon Başlığı', 'text', 'Diğer kitaplar bölümünün başlığı.'],
    'collection_subtitle'=> ['Koleksiyon Alt Metni', 'text', ''],
    'footer_text'        => ['Alt Bilgi Metni', 'text', 'Sayfa altındaki telif yazısı.'],
    'social_instagram'   => ['Instagram Adresi', 'url', 'Tam bağlantı (https://...). Boşsa gösterilmez.'],
    'social_etsy'        => ['Etsy Mağaza Adresi', 'url', 'Tam bağlantı (https://...). Kitabın kendi Etsy linki yoksa bu kullanılır.'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    foreach ($fields as $key => $_def) {
        set_setting($key, trim($_POST[$key] ?? ''));
    }

    // Logo görseli
    if (!empty($_POST['remove_logo_image'])) {
        set_setting('logo_image', '');
    } elseif (!empty($_FILES['logo_image']['name'])) {
        $path = store_uploaded_image($_FILES['logo_image']);
        if ($path) {
            set_setting('logo_image', $path);
        } else {
            flash('Logo görseli yüklenemedi (geçerli bir resim seçtiğinizden ve uploads klasörünün yazılabilir olduğundan emin olun).', 'error');
        }
    }

    // Favicon
    if (!empty($_FILES['favicon']['name'])) {
        $path = store_uploaded_image($_FILES['favicon']);
        if ($path) {
            set_setting('favicon', $path);
        } else {
            flash('Favicon yüklenemedi. PNG veya JPG deneyin.', 'error');
        }
    }

    flash('İçerik kaydedildi.');
    redirect(url('admin/settings_content.php'));
}

$admin_title  = 'İçerik';
$admin_active = 'content';
include __DIR__ . '/partials/admin_head.php';
?>
<div class="page-head">
  <div><h1>İçerik</h1><p class="adm-sub">Sitenizdeki tüm metinleri ve bağlantıları buradan düzenleyin.</p></div>
</div>

<form method="post" enctype="multipart/form-data">
  <?= csrf_field() ?>

  <div class="card">
    <h2>Metinler</h2>
    <div class="grid2">
      <?php foreach ($fields as $key => $def): ?>
        <?php [$label, $type, $hint] = $def; ?>
        <div class="field <?= $type === 'textarea' ? 'field--full' : '' ?>">
          <label><?= e($label) ?></label>
          <?php if ($type === 'textarea'): ?>
            <textarea name="<?= e($key) ?>"><?= e(get_setting($key, '')) ?></textarea>
          <?php else: ?>
            <input type="<?= $type === 'url' ? 'url' : 'text' ?>" name="<?= e($key) ?>" value="<?= e(get_setting($key, '')) ?>">
          <?php endif; ?>
          <?php if ($hint): ?><div class="hint"><?= e($hint) ?></div><?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="card">
    <h2>Logo ve Favicon</h2>
    <div class="grid2">
      <div class="field">
        <label>Logo Görseli</label>
        <?php if ($logo = get_setting('logo_image', '')): ?>
          <p><img src="<?= e(asset_url($logo)) ?>" alt="logo" style="max-height:46px;background:#1b1b1a;padding:6px;border-radius:6px"></p>
          <label style="font-weight:400"><input type="checkbox" name="remove_logo_image" value="1"> Logoyu kaldır (yazı kullan)</label>
        <?php endif; ?>
        <input type="file" name="logo_image" accept="image/*">
        <div class="hint">PNG (şeffaf) önerilir. Yüklemezseniz logo metni gösterilir.</div>
      </div>
      <div class="field">
        <label>Favicon (sekme simgesi)</label>
        <?php if ($fav = get_setting('favicon', '')): ?>
          <p><img src="<?= e(asset_url($fav)) ?>" alt="favicon" style="width:32px;height:32px"></p>
        <?php endif; ?>
        <input type="file" name="favicon" accept="image/png,image/jpeg">
        <div class="hint">Kare PNG önerilir (örn. 64x64).</div>
      </div>
    </div>
  </div>

  <div class="actions"><button class="btn" type="submit">Kaydet</button></div>
</form>

<?php include __DIR__ . '/partials/admin_foot.php'; ?>
