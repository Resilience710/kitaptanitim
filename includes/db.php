<?php
/**
 * Veritabanı (PDO) bağlantısı.
 */

/**
 * Verilen bilgilerle yeni bir PDO bağlantısı kurar.
 * install.php hem de uygulama bu fonksiyonu kullanır.
 */
function db_connect(string $host, string $name, string $user, string $pass, string $charset = 'utf8mb4'): PDO
{
    $dsn = "mysql:host={$host};dbname={$name};charset={$charset}";
    return new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
}

/**
 * Uygulama genelinde kullanılan tekil (singleton) PDO bağlantısı.
 * config.php içindeki bilgileri kullanır.
 */
function db(): PDO
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }
    $c = $GLOBALS['KT_CONFIG']['db'] ?? null;
    if (!$c) {
        http_response_code(500);
        die('Yapılandırma bulunamadı. Lütfen kurulumu (install.php) tamamlayın.');
    }
    try {
        $pdo = db_connect($c['host'], $c['name'], $c['user'], $c['pass'], $c['charset'] ?? 'utf8mb4');
    } catch (PDOException $e) {
        http_response_code(500);
        $dev = (($GLOBALS['KT_CONFIG']['app']['env'] ?? 'production') === 'development');
        $msg = $dev ? $e->getMessage() : 'Veritabanına bağlanılamadı. Lütfen config.php bilgilerini kontrol edin.';
        die('<h1>Bağlantı Hatası</h1><p>' . htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') . '</p>');
    }
    return $pdo;
}
