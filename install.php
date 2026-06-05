<?php
/**
 * Kurulum Sihirbazı
 * -----------------
 * Tek seferlik çalıştırılır. Veritabanı bilgilerini alır, tabloları kurar,
 * örnek içeriği ekler, yönetici hesabını oluşturur ve config.php dosyasını yazar.
 * Kurulum bittikten sonra bu dosyayı (install.php) sunucudan SİLİN.
 */
declare(strict_types=1);

$ROOT       = __DIR__;
$configFile = $ROOT . '/config.php';
$lockFile   = $ROOT . '/install.lock';
$schemaFile = $ROOT . '/database/schema.sql';

function esc($v): string { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); }

$errors  = [];
$done    = false;

// PHP sürüm kontrolü
if (PHP_VERSION_ID < 80000) {
    $errors[] = 'PHP 8.0 veya üzeri gerekiyor. Şu an: ' . PHP_VERSION . '. cPanel > MultiPHP Manager bölümünden PHP sürümünü yükseltin.';
}

// Zaten kurulu mu?
$alreadyInstalled = is_file($lockFile) || is_file($configFile);

/** SQL dosyasını çalıştırır (yorumları atlar, ifadeleri ';' ile ayırır). */
function run_sql_file(PDO $pdo, string $file): void
{
    $raw = file_get_contents($file);
    if ($raw === false) {
        throw new RuntimeException('schema.sql okunamadı.');
    }
    $lines = preg_split('/\r\n|\n|\r/', $raw);
    $keep  = [];
    foreach ($lines as $line) {
        if (strpos(ltrim($line), '--') === 0) {
            continue; // yorum satırı
        }
        $keep[] = $line;
    }
    $sql = implode("\n", $keep);
    foreach (explode(';', $sql) as $statement) {
        $statement = trim($statement);
        if ($statement !== '') {
            $pdo->exec($statement);
        }
    }
}

if (!$alreadyInstalled && !$errors && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $dbHost    = trim($_POST['db_host'] ?? 'localhost');
    $dbName    = trim($_POST['db_name'] ?? '');
    $dbUser    = trim($_POST['db_user'] ?? '');
    $dbPass    = (string) ($_POST['db_pass'] ?? '');
    $adminUser = trim($_POST['admin_user'] ?? '');
    $adminPass = (string) ($_POST['admin_pass'] ?? '');
    $siteTitle = trim($_POST['site_title'] ?? '');

    if ($dbName === '' || $dbUser === '') {
        $errors[] = 'Veritabanı adı ve kullanıcısı zorunludur.';
    }
    if ($adminUser === '' || strlen($adminPass) < 6) {
        $errors[] = 'Yönetici kullanıcı adı girin ve en az 6 karakterlik bir şifre belirleyin.';
    }
    if (!is_file($schemaFile)) {
        $errors[] = 'database/schema.sql dosyası bulunamadı. Tüm dosyaları yüklediğinizden emin olun.';
    }

    if (!$errors) {
        try {
            $dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";
            $pdo = new PDO($dsn, $dbUser, $dbPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);

            // Tablolar + örnek içerik
            run_sql_file($pdo, $schemaFile);

            // Yönetici hesabını kullanıcının bilgileriyle güncelle
            $hash = password_hash($adminPass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('UPDATE admin_users SET username = ?, password_hash = ?, display_name = ? WHERE id = 1');
            $stmt->execute([$adminUser, $hash, 'Yönetici']);

            // Site başlığı verildiyse uygula
            if ($siteTitle !== '') {
                foreach (['site_title', 'logo_text'] as $key) {
                    $s = $pdo->prepare('UPDATE settings SET setting_value = ? WHERE setting_key = ?');
                    $s->execute([$siteTitle, $key]);
                }
            }

            // config.php yaz
            $config = [
                'db'  => [
                    'host'    => $dbHost,
                    'name'    => $dbName,
                    'user'    => $dbUser,
                    'pass'    => $dbPass,
                    'charset' => 'utf8mb4',
                ],
                'app' => [
                    'base'        => '',
                    'pretty_urls' => true,
                    'env'         => 'production',
                    'key'         => bin2hex(random_bytes(16)),
                ],
            ];
            $php = "<?php\n// Otomatik oluşturuldu (install.php). Elle düzenleyebilirsiniz.\nreturn "
                 . var_export($config, true) . ";\n";

            if (@file_put_contents($configFile, $php) === false) {
                throw new RuntimeException('config.php yazılamadı. public_html yazma izinlerini kontrol edin ya da config.sample.php dosyasını elle düzenleyip config.php olarak kaydedin.');
            }
            @file_put_contents($lockFile, date('c'));

            $done = true;
        } catch (Throwable $ex) {
            $errors[] = 'Kurulum hatası: ' . $ex->getMessage();
        }
    }
}

// uploads klasörü yazılabilir mi? (uyarı amaçlı)
$uploadsWritable = is_dir($ROOT . '/uploads') && is_writable($ROOT . '/uploads');
?>
<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Kurulum — Kitap Tanıtım Sitesi</title>
<style>
  :root{--ac:#1b1b1a;--bd:#e3ded6;--mut:#7a756e;--bg:#fbfaf8}
  *{box-sizing:border-box}
  body{margin:0;background:var(--bg);color:#1b1b1a;font:16px/1.6 system-ui,-apple-system,Segoe UI,Roboto,sans-serif}
  .wrap{max-width:560px;margin:40px auto;padding:0 20px}
  .card{background:#fff;border:1px solid var(--bd);border-radius:14px;padding:28px}
  h1{font-size:24px;margin:0 0 4px}
  p.sub{color:var(--mut);margin:0 0 22px}
  label{display:block;font-weight:600;font-size:14px;margin:14px 0 5px}
  input{width:100%;padding:11px 12px;border:1px solid var(--bd);border-radius:9px;font-size:15px}
  input:focus{outline:none;border-color:var(--ac)}
  .row{display:flex;gap:12px}.row>div{flex:1}
  button{margin-top:22px;width:100%;background:var(--ac);color:#fff;border:0;border-radius:100px;padding:13px;font-size:16px;font-weight:600;cursor:pointer}
  .msg{padding:12px 14px;border-radius:10px;margin-bottom:14px;font-size:14px}
  .err{background:#fdecea;border:1px solid #f4c7c0;color:#a02617}
  .ok{background:#eaf7ee;border:1px solid #bfe6cb;color:#1c6b35}
  .hint{font-size:13px;color:var(--mut);margin-top:6px}
  code{background:#f3efe9;padding:2px 6px;border-radius:5px}
  a.btn{display:inline-block;margin-top:8px;margin-right:8px;padding:10px 18px;background:var(--ac);color:#fff;text-decoration:none;border-radius:100px;font-weight:600}
</style>
</head>
<body>
<div class="wrap">
  <div class="card">
    <h1>Kitap Tanıtım Sitesi — Kurulum</h1>
    <p class="sub">cPanel'de oluşturduğunuz MySQL veritabanı bilgilerini girin.</p>

    <?php foreach ($errors as $err): ?>
      <div class="msg err"><?= esc($err) ?></div>
    <?php endforeach; ?>

    <?php if ($alreadyInstalled): ?>
      <div class="msg ok">Kurulum zaten tamamlanmış görünüyor.</div>
      <p>Güvenlik için <code>install.php</code> dosyasını sunucudan silin.</p>
      <a class="btn" href="<?= esc('index.php') ?>">Siteyi Aç</a>
      <a class="btn" href="<?= esc('admin/login.php') ?>">Yönetime Giriş</a>

    <?php elseif ($done): ?>
      <div class="msg ok">Kurulum başarıyla tamamlandı! 🎉</div>
      <p><strong>Önemli:</strong> Şimdi güvenlik için <code>install.php</code> dosyasını silin
         (cPanel Dosya Yöneticisi veya FTP ile).</p>
      <a class="btn" href="<?= esc('index.php') ?>">Siteyi Aç</a>
      <a class="btn" href="<?= esc('admin/login.php') ?>">Yönetime Giriş</a>

    <?php else: ?>
      <?php if (!$uploadsWritable): ?>
        <div class="msg err">Uyarı: <code>uploads/</code> klasörü yazılabilir değil. Görsel yükleyebilmek için
          bu klasörün izinlerini <code>755</code> yapın.</div>
      <?php endif; ?>
      <form method="post" autocomplete="off">
        <div class="row">
          <div>
            <label>Veritabanı Sunucusu</label>
            <input name="db_host" value="<?= esc($_POST['db_host'] ?? 'localhost') ?>">
          </div>
          <div>
            <label>Veritabanı Adı</label>
            <input name="db_name" value="<?= esc($_POST['db_name'] ?? '') ?>" required>
          </div>
        </div>
        <label>Veritabanı Kullanıcısı</label>
        <input name="db_user" value="<?= esc($_POST['db_user'] ?? '') ?>" required>
        <label>Veritabanı Şifresi</label>
        <input name="db_pass" type="password" value="">

        <hr style="margin:24px 0;border:0;border-top:1px solid var(--bd)">

        <label>Site Başlığı</label>
        <input name="site_title" value="<?= esc($_POST['site_title'] ?? '') ?>" placeholder="Kitabınızın / sitenizin adı">
        <label>Yönetici Kullanıcı Adı</label>
        <input name="admin_user" value="<?= esc($_POST['admin_user'] ?? 'admin') ?>" required>
        <label>Yönetici Şifresi</label>
        <input name="admin_pass" type="password" required>
        <div class="hint">En az 6 karakter. Bu bilgilerle <code>/admin</code> paneline gireceksiniz.</div>

        <button type="submit">Kurulumu Tamamla</button>
      </form>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
