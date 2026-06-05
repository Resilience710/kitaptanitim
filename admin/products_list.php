<?php
/** Kitap listesi + işlemler (öne çıkar, sil). */
require __DIR__ . '/../includes/init.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    $id     = (int) ($_POST['id'] ?? 0);

    if ($action === 'feature' && $id) {
        db()->exec('UPDATE products SET is_featured = 0');
        $stmt = db()->prepare('UPDATE products SET is_featured = 1 WHERE id = ?');
        $stmt->execute([$id]);
        flash('Öne çıkan kitap güncellendi.');
    } elseif ($action === 'delete' && $id) {
        // Önce görsel dosyalarını sil (yalnızca uploads/ içindekiler)
        foreach (get_product_images($id) as $img) {
            $path = $img['file_path'] ?? '';
            if (strpos($path, 'uploads/') === 0) {
                @unlink(KT_ROOT . '/' . $path);
            }
        }
        $stmt = db()->prepare('DELETE FROM products WHERE id = ?'); // images CASCADE ile silinir
        $stmt->execute([$id]);
        flash('Kitap silindi.');
    }
    redirect(url('admin/products_list.php'));
}

$products = get_all_products();

$admin_title  = 'Kitaplar';
$admin_active = 'products';
include __DIR__ . '/partials/admin_head.php';
?>
<div class="page-head">
  <div><h1>Kitaplar</h1><p class="adm-sub">Kitaplarınızı ekleyin, düzenleyin ve öne çıkanı seçin.</p></div>
  <a class="btn" href="<?= e(url('admin/product_edit.php')) ?>">+ Yeni Kitap</a>
</div>

<?php if (!$products): ?>
  <div class="card"><p class="hint">Henüz kitap yok. <a href="<?= e(url('admin/product_edit.php')) ?>">İlk kitabınızı ekleyin.</a></p></div>
<?php else: ?>
  <table class="table">
    <thead><tr><th></th><th>Başlık</th><th>Fiyat</th><th>Durum</th><th style="text-align:right">İşlemler</th></tr></thead>
    <tbody>
    <?php foreach ($products as $p): ?>
      <tr>
        <td><img class="thumb" src="<?= e(image_src(get_primary_image((int) $p['id']))) ?>" alt=""></td>
        <td>
          <strong><?= e($p['title']) ?></strong>
          <?php if ($p['is_featured']): ?><span class="badge badge--featured">Öne çıkan</span><?php endif; ?>
          <?php if (!empty($p['subtitle'])): ?><div class="hint"><?= e($p['subtitle']) ?></div><?php endif; ?>
        </td>
        <td><?= $p['price'] !== null && (float) $p['price'] > 0 ? e(format_price($p['price'], $p['currency'])) : '—' ?></td>
        <td>
          <?php if ($p['status'] === 'published'): ?>
            <span class="badge badge--pub">Yayında</span>
          <?php else: ?>
            <span class="badge badge--draft">Taslak</span>
          <?php endif; ?>
        </td>
        <td style="text-align:right">
          <div class="actions" style="justify-content:flex-end;margin:0">
            <?php if (!$p['is_featured']): ?>
              <form method="post" style="display:inline">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="feature">
                <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                <button class="btn btn--ghost btn--sm" type="submit">Öne çıkar</button>
              </form>
            <?php endif; ?>
            <a class="btn btn--ghost btn--sm" href="<?= e(url('admin/product_images.php?id=' . (int) $p['id'])) ?>">Görseller</a>
            <a class="btn btn--ghost btn--sm" href="<?= e(url('admin/product_edit.php?id=' . (int) $p['id'])) ?>">Düzenle</a>
            <form method="post" style="display:inline" data-confirm="Bu kitabı ve tüm görsellerini silmek istediğinize emin misiniz?">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
              <button class="btn btn--danger btn--sm" type="submit">Sil</button>
            </form>
          </div>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>

<?php include __DIR__ . '/partials/admin_foot.php'; ?>
