<?php
/** Kitap ekleme / düzenleme. */
require __DIR__ . '/../includes/init.php';
require_admin();

$id      = (int) ($_GET['id'] ?? 0);
$product = $id ? get_product_by_id($id) : null;
$isNew   = !$product;

if ($id && !$product) {
    flash('Kitap bulunamadı.', 'error');
    redirect(url('admin/products_list.php'));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $title      = trim($_POST['title'] ?? '');
    $subtitle   = trim($_POST['subtitle'] ?? '');
    $slugInput  = trim($_POST['slug'] ?? '');
    $desc       = trim($_POST['description'] ?? '');
    $priceRaw   = trim(str_replace(',', '.', $_POST['price'] ?? ''));
    $price      = $priceRaw === '' ? null : (float) $priceRaw;
    $currency   = trim($_POST['currency'] ?? 'TL') ?: 'TL';
    $etsy       = trim($_POST['etsy_url'] ?? '');
    $status     = ($_POST['status'] ?? 'published') === 'draft' ? 'draft' : 'published';
    $featured   = !empty($_POST['is_featured']) ? 1 : 0;

    $errors = [];
    if ($title === '') {
        $errors[] = 'Başlık zorunludur.';
    }

    if (!$errors) {
        $slug = unique_slug($slugInput !== '' ? $slugInput : $title, $id);

        if ($isNew) {
            $stmt = db()->prepare(
                'INSERT INTO products (slug, title, subtitle, description, price, currency, etsy_url, is_featured, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([$slug, $title, $subtitle, $desc, $price, $currency, $etsy, $featured, $status]);
            $id = (int) db()->lastInsertId();
        } else {
            $stmt = db()->prepare(
                'UPDATE products SET slug=?, title=?, subtitle=?, description=?, price=?, currency=?, etsy_url=?, is_featured=?, status=?
                 WHERE id=?'
            );
            $stmt->execute([$slug, $title, $subtitle, $desc, $price, $currency, $etsy, $featured, $status, $id]);
        }

        // Tek öne çıkan kuralı
        if ($featured) {
            $clr = db()->prepare('UPDATE products SET is_featured = 0 WHERE id <> ?');
            $clr->execute([$id]);
        }

        if ($isNew) {
            flash('Kitap eklendi. Şimdi görselleri ekleyebilirsiniz.');
            redirect(url('admin/product_images.php?id=' . $id));
        }
        flash('Kitap güncellendi.');
        redirect(url('admin/product_edit.php?id=' . $id));
    }

    // Hata: girilen değerleri koru
    $product = [
        'title' => $title, 'subtitle' => $subtitle, 'slug' => $slugInput, 'description' => $desc,
        'price' => $price, 'currency' => $currency, 'etsy_url' => $etsy, 'status' => $status, 'is_featured' => $featured,
    ];
    foreach ($errors as $err) { flash($err, 'error'); }
}

$v = function (string $key, $default = '') use ($product) {
    return $product[$key] ?? $default;
};

$admin_title  = $isNew ? 'Yeni Kitap' : 'Kitabı Düzenle';
$admin_active = 'products';
include __DIR__ . '/partials/admin_head.php';
?>
<div class="page-head">
  <div><h1><?= $isNew ? 'Yeni Kitap' : 'Kitabı Düzenle' ?></h1></div>
  <a class="btn btn--ghost btn--sm" href="<?= e(url('admin/products_list.php')) ?>">← Listeye dön</a>
</div>

<form method="post">
  <?= csrf_field() ?>
  <div class="card">
    <div class="grid2">
      <div class="field field--full">
        <label>Başlık *</label>
        <input type="text" name="title" data-slug-source value="<?= e($v('title')) ?>" required>
      </div>
      <div class="field">
        <label>Alt Başlık</label>
        <input type="text" name="subtitle" value="<?= e($v('subtitle')) ?>">
      </div>
      <div class="field">
        <label>URL Adı (slug)</label>
        <input type="text" name="slug" data-slug-target value="<?= e($v('slug')) ?>" placeholder="otomatik oluşturulur">
        <div class="hint">Boş bırakırsanız başlıktan otomatik üretilir.</div>
      </div>
      <div class="field">
        <label>Fiyat</label>
        <input type="text" name="price" value="<?= $v('price') !== null ? e($v('price')) : '' ?>" placeholder="örn: 149,90">
        <div class="hint">Boş bırakırsanız fiyat gösterilmez.</div>
      </div>
      <div class="field">
        <label>Para Birimi</label>
        <select name="currency">
          <?php foreach (['TL', 'USD', 'EUR', 'GBP'] as $c): ?>
            <option value="<?= e($c) ?>" <?= $v('currency', 'TL') === $c ? 'selected' : '' ?>><?= e($c) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field field--full">
        <label>Etsy Bağlantısı</label>
        <input type="url" name="etsy_url" value="<?= e($v('etsy_url')) ?>" placeholder="https://www.etsy.com/...">
        <div class="hint">Boşsa, İçerik bölümündeki genel Etsy mağaza adresi kullanılır.</div>
      </div>
      <div class="field field--full">
        <label>Açıklama</label>
        <textarea name="description" style="min-height:200px"><?= e($v('description')) ?></textarea>
      </div>
      <div class="field">
        <label>Durum</label>
        <select name="status">
          <option value="published" <?= $v('status', 'published') === 'published' ? 'selected' : '' ?>>Yayında</option>
          <option value="draft" <?= $v('status') === 'draft' ? 'selected' : '' ?>>Taslak (gizli)</option>
        </select>
      </div>
      <div class="field">
        <label>Öne Çıkan</label>
        <label style="font-weight:400">
          <input type="checkbox" name="is_featured" value="1" <?= $v('is_featured') ? 'checked' : '' ?>>
          Ana sayfada büyük olarak göster
        </label>
      </div>
    </div>
  </div>

  <div class="actions">
    <button class="btn" type="submit"><?= $isNew ? 'Ekle ve Görsellere Geç' : 'Kaydet' ?></button>
    <?php if (!$isNew): ?>
      <a class="btn btn--ghost" href="<?= e(url('admin/product_images.php?id=' . $id)) ?>">Görselleri Yönet</a>
    <?php endif; ?>
  </div>
</form>

<?php include __DIR__ . '/partials/admin_foot.php'; ?>
