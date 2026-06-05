<?php
/** Kitap detay sayfası. /kitap/{slug} (rewrite) veya product.php?slug=... */
require __DIR__ . '/includes/init.php';

$slug    = $_GET['slug'] ?? '';
$product = $slug !== '' ? get_product_by_slug($slug) : null;

if (!$product || $product['status'] !== 'published') {
    http_response_code(404);
    $page_title = 'Sayfa bulunamadı';
    include __DIR__ . '/includes/header.php';
    echo '<section class="section"><div class="container" style="text-align:center;padding:60px 0">';
    echo '<h1 class="section__title">Kitap bulunamadı</h1>';
    echo '<p><a class="btn btn--primary" href="' . e(url('')) . '">Ana sayfaya dön</a></p>';
    echo '</div></section>';
    include __DIR__ . '/includes/footer.php';
    exit;
}

$images   = get_product_images((int) $product['id']);
$main     = $images[0] ?? null;
$etsyUrl  = $product['etsy_url'] !== '' ? $product['etsy_url'] : get_setting('social_etsy', '#');
$page_title = $product['title'];

include __DIR__ . '/includes/header.php';
?>

<section class="product section">
  <div class="container product__inner">

    <!-- Galeri -->
    <div class="product__gallery reveal">
      <div class="gallery">
        <div class="gallery__main">
          <img id="galleryMain" src="<?= e(image_src($main)) ?>" alt="<?= e($product['title']) ?>">
        </div>
        <?php if (count($images) > 1): ?>
          <div class="gallery__thumbs">
            <?php foreach ($images as $i => $img): ?>
              <button class="gallery__thumb<?= $i === 0 ? ' is-active' : '' ?>"
                      type="button"
                      data-full="<?= e(image_src($img)) ?>">
                <img src="<?= e(image_src($img)) ?>" alt="<?= e($img['alt_text'] ?? $product['title']) ?>">
              </button>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Bilgi -->
    <div class="product__info reveal">
      <a class="product__back" href="<?= e(url('')) ?>">← Tüm kitaplar</a>
      <h1 class="product__title"><?= e($product['title']) ?></h1>
      <?php if (!empty($product['subtitle'])): ?>
        <p class="product__subtitle"><?= e($product['subtitle']) ?></p>
      <?php endif; ?>
      <?php if ($product['price'] !== null && (float) $product['price'] > 0): ?>
        <p class="product__price"><?= e(format_price($product['price'], $product['currency'])) ?></p>
      <?php endif; ?>

      <div class="product__desc"><?= nl2br(e($product['description'] ?? '')) ?></div>

      <div class="product__actions">
        <a class="btn btn--primary" href="<?= e($etsyUrl ?: '#') ?>" target="_blank" rel="noopener">
          <?= e(get_setting('btn_buy_label', 'Etsy\'de Satın Al')) ?>
        </a>
      </div>
    </div>

  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
