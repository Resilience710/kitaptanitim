<?php
/** Yönetim paneli üst şablonu. Tüm admin sayfaları (login hariç) bunu dahil eder. */
require_admin();
$admin_title  = $admin_title ?? 'Yönetim';
$admin_active = $admin_active ?? '';
$nav = [
    'dashboard' => ['index.php',            'Panel'],
    'content'   => ['settings_content.php', 'İçerik'],
    'theme'     => ['settings_theme.php',   'Görünüm'],
    'products'  => ['products_list.php',    'Kitaplar'],
];
?>
<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($admin_title) ?> — Yönetim</title>
<link rel="icon" href="<?= e(asset_url(get_setting('favicon', 'assets/img/favicon.svg'))) ?>">
<link rel="stylesheet" href="<?= e(asset_url('admin/assets/admin.css')) ?>">
</head>
<body>
<header class="adm-top">
  <div class="adm-top__inner">
    <a class="adm-brand" href="<?= e(url('admin/index.php')) ?>">📖 Yönetim</a>
    <nav class="adm-nav">
      <?php foreach ($nav as $key => $item): ?>
        <a class="<?= $key === $admin_active ? 'is-active' : '' ?>" href="<?= e(url('admin/' . $item[0])) ?>"><?= e($item[1]) ?></a>
      <?php endforeach; ?>
      <a href="<?= e(url('')) ?>" target="_blank">Siteyi Gör ↗</a>
      <a class="adm-logout" href="<?= e(url('admin/logout.php')) ?>">Çıkış</a>
    </nav>
  </div>
</header>
<main class="adm-main">
  <div class="adm-container">
    <?php foreach (get_flashes() as $f): ?>
      <div class="adm-flash adm-flash--<?= e($f['type']) ?>"><?= e($f['msg']) ?></div>
    <?php endforeach; ?>
