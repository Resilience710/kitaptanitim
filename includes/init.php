<?php
/**
 * Önyükleme (bootstrap). Tüm genel sayfaların en üstünde çağrılır.
 * Yapılandırmayı yükler, çekirdek dosyaları dahil eder ve oturumu başlatır.
 */
declare(strict_types=1);

define('KT_ROOT', dirname(__DIR__));

$configFile = KT_ROOT . '/config.php';

// Kurulum tamamlanmadıysa install.php'ye yönlendir.
if (!is_file($configFile)) {
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    if (basename($script) !== 'install.php') {
        $toInstall = (strpos($script, '/admin/') !== false) ? '../install.php' : 'install.php';
        header('Location: ' . $toInstall);
        exit;
    }
    return;
}

$GLOBALS['KT_CONFIG'] = require $configFile;

// Hata gösterimi ortama göre.
if ((($GLOBALS['KT_CONFIG']['app']['env'] ?? 'production') === 'development')) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    ini_set('display_errors', '0');
}

require __DIR__ . '/functions.php';
require __DIR__ . '/db.php';
require __DIR__ . '/settings.php';
require __DIR__ . '/theme.php';
require __DIR__ . '/products.php';
require __DIR__ . '/auth.php';

start_session();
