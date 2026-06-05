<?php
/** Ana sayfa (tek sayfa landing). */
require __DIR__ . '/includes/init.php';

$featured   = get_featured_product();
$collection = get_collection_products($featured['id'] ?? 0);

include __DIR__ . '/includes/header.php';
?>

<!-- ============================ HERO ============================ -->
<section class="hero" id="kitap">
  <div class="container hero__inner">
    <div class="hero__text reveal">
      <?php if ($eyebrow = get_setting('hero_eyebrow', '')): ?>
        <p class="hero__eyebrow"><?= e($eyebrow) ?></p>
      <?php endif; ?>
      <h1 class="hero__title"><?= e(get_setting('hero_heading', 'Kitabınız')) ?></h1>
      <p class="hero__sub"><?= e(get_setting('hero_subheading', '')) ?></p>
      <div class="hero__actions">
        <?php
          $etsyUrl = $featured['etsy_url'] ?? '';
          $etsyUrl = ($etsyUrl !== '') ? $etsyUrl : get_setting('social_etsy', '#');
        ?>
        <a class="btn btn--primary" href="<?= e($etsyUrl ?: '#') ?>" target="_blank" rel="noopener">
          <?= e(get_setting('btn_buy_label', 'Etsy\'de Satın Al')) ?>
        </a>
        <?php if ($featured): ?>
          <a class="btn btn--ghost" href="<?= e(product_url($featured['slug'])) ?>">
            <?= e(get_setting('btn_detail_label', 'İncele')) ?>
          </a>
        <?php endif; ?>
      </div>
    </div>

    <div class="hero__media reveal">
      <div class="book3d">
        <div class="book3d__cover">
          <img src="<?= e(image_src(get_primary_image($featured['id'] ?? 0))) ?>"
               alt="<?= e($featured['title'] ?? 'Kitap kapağı') ?>">
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============================ HAKKINDA ============================ -->
<section class="about section" id="hakkinda">
  <div class="container about__inner">
    <div class="reveal">
      <h2 class="section__title"><?= e(get_setting('about_title', 'Kitap Hakkında')) ?></h2>
      <div class="about__text"><?= nl2br(e(get_setting('about_text', ''))) ?></div>
      <?php if ($featured): ?>
        <a class="btn btn--ghost" href="<?= e(product_url($featured['slug'])) ?>">
          <?= e(get_setting('btn_detail_label', 'İncele')) ?> →
        </a>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- ============================ KOLEKSİYON ============================ -->
<?php if (!empty($collection)): ?>
<section class="collection section" id="koleksiyon">
  <div class="container">
    <div class="section__head reveal">
      <h2 class="section__title"><?= e(get_setting('collection_title', 'Diğer Kitaplar')) ?></h2>
      <p class="section__sub"><?= e(get_setting('collection_subtitle', '')) ?></p>
    </div>
    <div class="cards">
      <?php foreach ($collection as $book): ?>
        <a class="card reveal" href="<?= e(product_url($book['slug'])) ?>">
          <div class="card__media">
            <img src="<?= e(image_src(get_primary_image((int) $book['id']))) ?>" alt="<?= e($book['title']) ?>">
          </div>
          <div class="card__body">
            <h3 class="card__title"><?= e($book['title']) ?></h3>
            <?php if (!empty($book['subtitle'])): ?>
              <p class="card__sub"><?= e($book['subtitle']) ?></p>
            <?php endif; ?>
            <?php if ($book['price'] !== null && (float) $book['price'] > 0): ?>
              <span class="card__price"><?= e(format_price($book['price'], $book['currency'])) ?></span>
            <?php endif; ?>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
