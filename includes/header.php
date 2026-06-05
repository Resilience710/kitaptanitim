<?php
/**
 * Genel site başlığı (head + üst menü). init.php önceden dahil edilmiş olmalıdır.
 * Sayfalar isterse $page_title değişkenini ayarlayarak başlığı özelleştirebilir.
 */
$siteTitle = get_setting('site_title', 'Kitap');
$pageTitle = isset($page_title) && $page_title !== '' ? $page_title . ' — ' . $siteTitle : $siteTitle;
$logoImage = get_setting('logo_image', '');
$logoText  = get_setting('logo_text', $siteTitle);
$gFonts    = google_fonts_link();
$favicon   = get_setting('favicon', 'assets/img/favicon.svg');
$etsyTop   = get_setting('social_etsy', '');
?>
<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?></title>
<meta name="description" content="<?= e(get_setting('hero_subheading', '')) ?>">
<?php if ($favicon): ?>
<link rel="icon" href="<?= e(asset_url($favicon)) ?>">
<?php endif; ?>
<?php if ($gFonts): ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="<?= e($gFonts) ?>" rel="stylesheet">
<?php endif; ?>
<link rel="stylesheet" href="<?= e(asset_url('assets/css/style.css')) ?>">
<style><?= render_theme_vars() ?></style>
</head>
<body>
<header class="site-header" id="top">
  <div class="container site-header__inner">
    <a class="brand" href="<?= e(url('')) ?>">
      <?php if ($logoImage): ?>
        <img src="<?= e(asset_url($logoImage)) ?>" alt="<?= e($logoText) ?>" class="brand__img">
      <?php else: ?>
        <span class="brand__text"><?= e($logoText) ?></span>
      <?php endif; ?>
    </a>
    <button class="nav-toggle" aria-label="Menü" aria-expanded="false">
      <span></span><span></span><span></span>
    </button>
    <nav class="site-nav">
      <a href="<?= e(url('') . '#kitap') ?>">Kitap</a>
      <a href="<?= e(url('') . '#hakkinda') ?>">Hakkında</a>
      <a href="<?= e(url('') . '#koleksiyon') ?>">Koleksiyon</a>
      <?php if ($etsyTop): ?>
        <a class="site-nav__cta" href="<?= e($etsyTop) ?>" target="_blank" rel="noopener">Etsy</a>
      <?php endif; ?>
    </nav>
  </div>
</header>
<main id="main">
