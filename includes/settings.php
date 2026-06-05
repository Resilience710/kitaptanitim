<?php
/**
 * Ayarlar (settings) anahtar-değer deposu erişimi.
 * Tema ve içerik metinleri burada saklanır; "her şey özelleştirilebilir" mantığının temeli.
 */

/** Tüm ayarları tek seferde okur ve önbelleğe alır. */
function get_all_settings(bool $fresh = false): array
{
    if (!$fresh && isset($GLOBALS['KT_SETTINGS_CACHE'])) {
        return $GLOBALS['KT_SETTINGS_CACHE'];
    }
    $cache = [];
    foreach (db()->query('SELECT setting_key, setting_value FROM settings')->fetchAll() as $row) {
        $cache[$row['setting_key']] = $row['setting_value'];
    }
    $GLOBALS['KT_SETTINGS_CACHE'] = $cache;
    return $cache;
}

/** Tek bir ayarı okur. */
function get_setting(string $key, $default = null)
{
    $all = get_all_settings();
    return array_key_exists($key, $all) ? $all[$key] : $default;
}

/** Tek bir ayarı yazar/günceller (UPSERT). */
function set_setting(string $key, $value): void
{
    $stmt = db()->prepare(
        'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
    );
    $stmt->execute([$key, (string) $value]);
    if (isset($GLOBALS['KT_SETTINGS_CACHE'])) {
        $GLOBALS['KT_SETTINGS_CACHE'][$key] = (string) $value;
    }
}

/** Birden çok ayarı topluca yazar. */
function set_settings(array $pairs): void
{
    foreach ($pairs as $key => $value) {
        set_setting((string) $key, $value);
    }
}
