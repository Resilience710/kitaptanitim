<?php
/**
 * Oturum, admin kimlik doğrulama ve CSRF koruması.
 */

/** Güvenli ayarlarla oturumu başlatır. */
function start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? '') == 443);
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => $secure,
    ]);
    session_name('KTSESID');
    session_start();
}

function current_admin(): ?array
{
    return $_SESSION['admin'] ?? null;
}

function is_logged_in(): bool
{
    return !empty($_SESSION['admin']);
}

/** Admin değilse giriş sayfasına yönlendirir. */
function require_admin(): void
{
    if (!is_logged_in()) {
        redirect(url('admin/login.php'));
    }
}

/** Kullanıcı adı + şifre ile giriş dener. */
function login_admin(string $username, string $password): bool
{
    $stmt = db()->prepare('SELECT * FROM admin_users WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    if (!$user || !password_verify($password, $user['password_hash'])) {
        return false;
    }
    session_regenerate_id(true);
    $_SESSION['admin'] = [
        'id'           => (int) $user['id'],
        'username'     => $user['username'],
        'display_name' => $user['display_name'],
    ];
    $upd = db()->prepare('UPDATE admin_users SET last_login_at = NOW() WHERE id = ?');
    $upd->execute([(int) $user['id']]);
    return true;
}

function logout_admin(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/* --------------------------------- CSRF ---------------------------------- */

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

/** POST isteklerinde CSRF token doğrular; geçersizse betiği durdurur. */
function csrf_check(): void
{
    $token = $_POST['csrf'] ?? '';
    if (!is_string($token) || empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $token)) {
        http_response_code(400);
        die('Oturum doğrulaması başarısız (CSRF). Lütfen sayfayı yenileyip tekrar deneyin.');
    }
}
