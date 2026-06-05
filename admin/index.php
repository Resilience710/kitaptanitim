<?php
/** Yönetim paneli ana sayfası (kontrol paneli). */
require __DIR__ . '/../includes/init.php';

$admin_title  = 'Panel';
$admin_active = 'dashboard';

$products = get_all_products();
$total    = count($products);
$featured = get_featured_product();
$installExists = is_file(KT_ROOT . '/install.php');

include __DIR__ . '/partials/admin_head.php';
?>
<div class="page-head">
  <div>
    <h1>Hoş geldiniz 👋</h1>
    <p class="adm-sub">Buradan sitenizin içeriğini, görünümünü ve kitaplarınızı yönetebilirsiniz.</p>
  </div>
  <a class="btn" href="<?= e(url('admin/product_edit.php')) ?>">+ Yeni Kitap</a>
</div>

<?php if ($installExists): ?>
  <div class="adm-flash adm-flash--error">
    <strong>Güvenlik uyarısı:</strong> <code>install.php</code> dosyası hâlâ sunucuda.
    Lütfen FTP veya cPanel Dosya Yöneticisi ile silin.
  </div>
<?php endif; ?>

<div class="grid2">
  <div class="card">
    <h2>Kitaplar</h2>
    <p style="font-size:2rem;font-weight:700;margin:0"><?= (int) $total ?></p>
    <p class="hint">Öne çıkan: <?= $featured ? e($featured['title']) : '—' ?></p>
    <div class="actions">
      <a class="btn btn--ghost btn--sm" href="<?= e(url('admin/products_list.php')) ?>">Kitapları Yönet</a>
    </div>
  </div>

  <div class="card">
    <h2>Hızlı İşlemler</h2>
    <div class="actions">
      <a class="btn btn--ghost btn--sm" href="<?= e(url('admin/settings_content.php')) ?>">Metinleri Düzenle</a>
      <a class="btn btn--ghost btn--sm" href="<?= e(url('admin/settings_theme.php')) ?>">Görünümü Değiştir</a>
      <a class="btn btn--ghost btn--sm" href="<?= e(url('')) ?>" target="_blank">Siteyi Aç ↗</a>
    </div>
  </div>
</div>

<div class="card">
  <h2>Son Kitaplar</h2>
  <?php if (!$products): ?>
    <p class="hint">Henüz kitap yok. <a href="<?= e(url('admin/product_edit.php')) ?>">İlk kitabınızı ekleyin.</a></p>
  <?php else: ?>
    <table class="table">
      <thead><tr><th></th><th>Başlık</th><th>Durum</th><th></th></tr></thead>
      <tbody>
      <?php foreach (array_slice($products, 0, 5) as $p): ?>
        <tr>
          <td><img class="thumb" src="<?= e(image_src(get_primary_image((int) $p['id']))) ?>" alt=""></td>
          <td>
            <?= e($p['title']) ?>
            <?php if ($p['is_featured']): ?><span class="badge badge--featured">Öne çıkan</span><?php endif; ?>
          </td>
          <td>
            <?php if ($p['status'] === 'published'): ?>
              <span class="badge badge--pub">Yayında</span>
            <?php else: ?>
              <span class="badge badge--draft">Taslak</span>
            <?php endif; ?>
          </td>
          <td><a class="btn btn--ghost btn--sm" href="<?= e(url('admin/product_edit.php?id=' . (int) $p['id'])) ?>">Düzenle</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/partials/admin_foot.php'; ?>
