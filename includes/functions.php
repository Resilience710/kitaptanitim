<?php
/**
 * Genel yardımcı fonksiyonlar (yapılandırmadan bağımsız temel araçlar).
 */

/** HTML çıktısını güvenli hale getirir (XSS koruması). */
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/** config.php içinden nokta gösterimiyle değer okur: cfg('app.base'). */
function cfg(string $path, $default = null)
{
    $cur = $GLOBALS['KT_CONFIG'] ?? [];
    foreach (explode('.', $path) as $part) {
        if (is_array($cur) && array_key_exists($part, $cur)) {
            $cur = $cur[$part];
        } else {
            return $default;
        }
    }
    return $cur;
}

/** Uygulamanın taban yolu (alt klasör kurulumları için). */
function base_path(): string
{
    return rtrim((string) cfg('app.base', ''), '/');
}

/** Site içi bir URL üretir. url('assets/css/style.css') => /assets/css/style.css */
function url(string $path = ''): string
{
    $path = ltrim($path, '/');
    $u = base_path() . '/' . $path;
    return $u === '' ? '/' : $u;
}

/** Statik dosya URL'si (görsel, css, js). */
function asset_url(string $path): string
{
    return url($path);
}

/** Bir kitabın detay sayfası adresi. */
function product_url(string $slug): string
{
    if (cfg('app.pretty_urls', true)) {
        return url('kitap/' . rawurlencode($slug));
    }
    return url('product.php?slug=' . rawurlencode($slug));
}

/** Yönlendirme yapıp betiği sonlandırır. */
function redirect(string $to): void
{
    header('Location: ' . $to);
    exit;
}

/** Türkçe karakterleri ASCII'ye çevirerek URL dostu "slug" üretir. */
function slugify(string $text): string
{
    $tr = [
        'ş' => 's', 'Ş' => 's', 'ı' => 'i', 'İ' => 'i', 'ğ' => 'g', 'Ğ' => 'g',
        'ç' => 'c', 'Ç' => 'c', 'ö' => 'o', 'Ö' => 'o', 'ü' => 'u', 'Ü' => 'u',
        'â' => 'a', 'î' => 'i', 'û' => 'u',
    ];
    $text = strtr($text, $tr);
    $text = mb_strtolower($text, 'UTF-8');
    $text = preg_replace('/[^a-z0-9]+/u', '-', $text);
    $text = trim((string) $text, '-');
    return $text !== '' ? $text : 'kitap';
}

/** Fiyatı Türk biçiminde gösterir: 149.90 => "149,90 TL". */
function format_price($price, string $currency = 'TL'): string
{
    if ($price === null || $price === '' ) {
        return '';
    }
    return number_format((float) $price, 2, ',', '.') . ' ' . $currency;
}

/* ----------------------------- Flash mesajları ---------------------------- */

function flash(string $message, string $type = 'success'): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return;
    }
    $_SESSION['flash'][] = ['msg' => $message, 'type' => $type];
}

function get_flashes(): array
{
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

/* --------------------------- Görsel yükleme kontrolü ---------------------- */

/**
 * Yüklenen bir görseli doğrular.
 * Dönüş: ['ok'=>bool, 'error'=>string, 'ext'=>string, 'mime'=>string]
 */
function validate_image_upload(array $file, int $maxBytes = 4194304): array
{
    if (!isset($file['error']) || is_array($file['error'])) {
        return ['ok' => false, 'error' => 'Geçersiz yükleme.'];
    }
    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return ['ok' => false, 'error' => 'Dosya sunucu sınırından büyük.'];
        case UPLOAD_ERR_NO_FILE:
            return ['ok' => false, 'error' => 'Dosya seçilmedi.'];
        default:
            return ['ok' => false, 'error' => 'Yükleme sırasında hata oluştu.'];
    }
    if (($file['size'] ?? 0) > $maxBytes) {
        return ['ok' => false, 'error' => 'Dosya boyutu 4 MB sınırını aşıyor.'];
    }
    $info = @getimagesize($file['tmp_name']);
    if ($info === false) {
        return ['ok' => false, 'error' => 'Dosya geçerli bir görsel değil.'];
    }
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];
    $mime = $info['mime'] ?? '';
    if (!isset($allowed[$mime])) {
        return ['ok' => false, 'error' => 'Sadece JPG, PNG, WEBP veya GIF yükleyebilirsiniz.'];
    }
    return ['ok' => true, 'error' => '', 'ext' => $allowed[$mime], 'mime' => $mime];
}

/**
 * Doğrulanmış bir görseli uploads/ klasörüne taşır, göreli yolu döndürür.
 * Hata olursa null döner.
 */
function store_uploaded_image(array $file): ?string
{
    $check = validate_image_upload($file);
    if (!$check['ok']) {
        return null;
    }
    $uploadsDir = defined('KT_ROOT') ? KT_ROOT . '/uploads' : __DIR__ . '/../uploads';
    if (!is_dir($uploadsDir) || !is_writable($uploadsDir)) {
        return null;
    }
    $name = bin2hex(random_bytes(8)) . '.' . $check['ext'];
    $dest = $uploadsDir . '/' . $name;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return null;
    }
    return 'uploads/' . $name;
}
