<?php
/** Yönetim girişi. */
require __DIR__ . '/../includes/init.php';

if (is_logged_in()) {
    redirect(url('admin/index.php'));
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $username = trim($_POST['username'] ?? '');
    $password = (string) ($_POST['password'] ?? '');
    if (login_admin($username, $password)) {
        redirect(url('admin/index.php'));
    }
    $error = 'Kullanıcı adı veya şifre hatalı.';
}
?>
<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Giriş — Yönetim</title>
<link rel="icon" href="<?= e(asset_url(get_setting('favicon', 'assets/img/favicon.svg'))) ?>">
<link rel="stylesheet" href="<?= e(asset_url('admin/assets/admin.css')) ?>">
<style>
  body{display:flex;align-items:center;justify-content:center;min-height:100vh}
  .login{width:100%;max-width:380px;padding:0 20px}
  .login .card{padding:30px}
</style>
</head>
<body>
<div class="login">
  <div class="card">
    <h1 style="text-align:center">📖 Yönetim Girişi</h1>
    <p class="adm-sub" style="text-align:center"><?= e(get_setting('site_title', '')) ?></p>
    <?php if ($error): ?><div class="adm-flash adm-flash--error"><?= e($error) ?></div><?php endif; ?>
    <form method="post">
      <?= csrf_field() ?>
      <div class="field">
        <label>Kullanıcı Adı</label>
        <input type="text" name="username" autofocus required>
      </div>
      <div class="field">
        <label>Şifre</label>
        <input type="password" name="password" required>
      </div>
      <button class="btn" type="submit" style="width:100%;justify-content:center">Giriş Yap</button>
    </form>
  </div>
</div>
</body>
</html>
