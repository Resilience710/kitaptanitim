<?php
/** Bir kitabın görsellerini yönetme: yükleme, birincil yapma, sıralama, silme. */
require __DIR__ . '/../includes/init.php';
require_admin();

$id      = (int) ($_GET['id'] ?? 0);
$product = $id ? get_product_by_id($id) : null;
if (!$product) {
    flash('Kitap bulunamadı.', 'error');
    redirect(url('admin/products_list.php'));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'upload' && !empty($_FILES['images']['name'][0])) {
        $files   = $_FILES['images'];
        $count   = count($files['name']);
        $existing = get_product_images($id);
        $nextSort = 0;
        foreach ($existing as $ex) { $nextSort = max($nextSort, (int) $ex['sort_order'] + 1); }
        $hasPrimary = false;
        foreach ($existing as $ex) { if ($ex['is_primary']) { $hasPrimary = true; break; } }

        $ok = 0; $fail = 0;
        for ($i = 0; $i < $count; $i++) {
            if (($files['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            $single = [
                'name'     => $files['name'][$i],
                'type'     => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error'    => $files['error'][$i],
                'size'     => $files['size'][$i],
            ];
            $path = store_uploaded_image($single);
            if ($path) {
                $primary = (!$hasPrimary && $ok === 0) ? 1 : 0;
                if ($primary) { $hasPrimary = true; }
                $stmt = db()->prepare(
                    'INSERT INTO product_images (product_id, file_path, alt_text, is_primary, sort_order)
                     VALUES (?, ?, ?, ?, ?)'
                );
                $stmt->execute([$id, $path, $product['title'], $primary, $nextSort++]);
                $ok++;
            } else {
                $fail++;
            }
        }
        flash($ok . ' görsel yüklendi.' . ($fail ? " ($fail tanesi başarısız — geçerli resim / boyut / izinleri kontrol edin.)" : ''), $fail ? 'error' : 'success');

    } elseif ($action === 'primary') {
        $imgId = (int) ($_POST['image_id'] ?? 0);
        db()->prepare('UPDATE product_images SET is_primary = 0 WHERE product_id = ?')->execute([$id]);
        db()->prepare('UPDATE product_images SET is_primary = 1 WHERE id = ? AND product_id = ?')->execute([$imgId, $id]);
        flash('Kapak görseli güncellendi.');

    } elseif ($action === 'delete') {
        $imgId = (int) ($_POST['image_id'] ?? 0);
        $stmt = db()->prepare('SELECT * FROM product_images WHERE id = ? AND product_id = ?');
        $stmt->execute([$imgId, $id]);
        $img = $stmt->fetch();
        if ($img) {
            if (strpos($img['file_path'], 'uploads/') === 0) {
                @unlink(KT_ROOT . '/' . $img['file_path']);
            }
            db()->prepare('DELETE FROM product_images WHERE id = ?')->execute([$imgId]);
            // Birincil silindiyse ilk kalanı birincil yap
            if ($img['is_primary']) {
                $rest = get_product_images($id);
                if (!empty($rest[0])) {
                    db()->prepare('UPDATE product_images SET is_primary = 1 WHERE id = ?')->execute([(int) $rest[0]['id']]);
                }
            }
            flash('Görsel silindi.');
        }

    } elseif ($action === 'move') {
        $imgId = (int) ($_POST['image_id'] ?? 0);
        $dir   = $_POST['dir'] === 'up' ? 'up' : 'down';
        $images = get_product_images($id);
        $idx = null;
        foreach ($images as $k => $im) { if ((int) $im['id'] === $imgId) { $idx = $k; break; } }
        if ($idx !== null) {
            $swap = $dir === 'up' ? $idx - 1 : $idx + 1;
            if (isset($images[$swap])) {
                $a = $images[$idx]; $b = $images[$swap];
                db()->prepare('UPDATE product_images SET sort_order = ? WHERE id = ?')->execute([(int) $b['sort_order'], (int) $a['id']]);
                db()->prepare('UPDATE product_images SET sort_order = ? WHERE id = ?')->execute([(int) $a['sort_order'], (int) $b['id']]);
            }
        }
    }
    redirect(url('admin/product_images.php?id=' . $id));
}

$images = get_product_images($id);

$admin_title  = 'Görseller';
$admin_active = 'products';
include __DIR__ . '/partials/admin_head.php';
?>
<div class="page-head">
  <div>
    <h1>Görseller — <?= e($product['title']) ?></h1>
    <p class="adm-sub">İlk görsel (kapak) ana sayfada ve listede kullanılır. Birden çok görsel detay sayfasında galeri oluşturur.</p>
  </div>
  <a class="btn btn--ghost btn--sm" href="<?= e(url('admin/product_edit.php?id=' . $id)) ?>">← Kitap bilgileri</a>
</div>

<div class="card">
  <h2>Yeni Görsel Yükle</h2>
  <form method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="upload">
    <div class="field">
      <input type="file" name="images[]" accept="image/jpeg,image/png,image/webp,image/gif" multiple required>
      <div class="hint">Birden fazla seçebilirsiniz. JPG, PNG, WEBP veya GIF. En fazla 4 MB.</div>
    </div>
    <button class="btn" type="submit">Yükle</button>
  </form>
</div>

<div class="card">
  <h2>Mevcut Görseller</h2>
  <?php if (!$images): ?>
    <p class="hint">Henüz görsel yok. Yukarıdan ekleyin.</p>
  <?php else: ?>
    <div class="img-grid">
      <?php foreach ($images as $i => $img): ?>
        <div class="img-card">
          <div class="img-card__media"><img src="<?= e(image_src($img)) ?>" alt=""></div>
          <div class="img-card__body">
            <?php if ($img['is_primary']): ?>
              <span class="badge badge--featured" style="align-self:flex-start">Kapak</span>
            <?php endif; ?>
            <div class="img-card__row">
              <?php if (!$img['is_primary']): ?>
                <form method="post"><?= csrf_field() ?>
                  <input type="hidden" name="action" value="primary">
                  <input type="hidden" name="image_id" value="<?= (int) $img['id'] ?>">
                  <button class="btn btn--ghost btn--sm" type="submit">Kapak yap</button>
                </form>
              <?php endif; ?>
            </div>
            <div class="img-card__row">
              <form method="post"><?= csrf_field() ?>
                <input type="hidden" name="action" value="move"><input type="hidden" name="dir" value="up">
                <input type="hidden" name="image_id" value="<?= (int) $img['id'] ?>">
                <button class="btn btn--ghost btn--sm" type="submit" <?= $i === 0 ? 'disabled' : '' ?>>↑</button>
              </form>
              <form method="post"><?= csrf_field() ?>
                <input type="hidden" name="action" value="move"><input type="hidden" name="dir" value="down">
                <input type="hidden" name="image_id" value="<?= (int) $img['id'] ?>">
                <button class="btn btn--ghost btn--sm" type="submit" <?= $i === count($images) - 1 ? 'disabled' : '' ?>>↓</button>
              </form>
              <form method="post" data-confirm="Bu görseli silmek istediğinize emin misiniz?"><?= csrf_field() ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="image_id" value="<?= (int) $img['id'] ?>">
                <button class="btn btn--danger btn--sm" type="submit">Sil</button>
              </form>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/partials/admin_foot.php'; ?>
