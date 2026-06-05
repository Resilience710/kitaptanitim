<?php
/**
 * Tema motoru: ayarları CSS değişkenlerine ve dinamik Google Fonts bağlantısına çevirir.
 * style.css yalnızca var(--...) kullanır; böylece admin'den yapılan her değişiklik
 * kod düzenlemeden anında siteye yansır.
 */

/** Tema ayarlarının varsayılan değerleri. */
function theme_defaults(): array
{
    return [
        'theme_bg_color'          => '#fbfaf8',
        'theme_text_color'        => '#1b1b1a',
        'theme_accent_color'      => '#1b1b1a',
        'theme_muted_color'       => '#7a756e',
        'theme_border_color'      => '#e7e2da',
        'theme_card_bg'           => '#ffffff',
        'theme_btn_text_color'    => '#ffffff',
        'theme_font_heading_name' => 'Playfair Display',
        'theme_font_body_name'    => 'Inter',
        'theme_font_size_base'    => '17px',
        'theme_font_size_h1'      => '3.4rem',
        'theme_header_height'     => '64px',
        'theme_radius'            => '10px',
        'theme_btn_radius'        => '100px',
        'theme_max_width'         => '1180px',
        'theme_section_gap'       => '110px',
    ];
}

/** Bir tema değerini (ayar yoksa varsayılanı) döndürür. */
function theme_value(string $key): string
{
    $defaults = theme_defaults();
    $v = get_setting($key, null);
    if ($v === null || $v === '') {
        return (string) ($defaults[$key] ?? '');
    }
    return (string) $v;
}

/** CSS enjeksiyonunu engellemek için değerleri temizler. */
function css_safe(string $value): string
{
    return trim(preg_replace('/[<>{};"\\\\]/', '', $value));
}

/** Font adını CSS font-family değerine çevirir. */
function font_family_css(string $name, string $fallback): string
{
    $name = trim(str_replace(["'", '"'], '', $name));
    return $name !== '' ? "'{$name}', {$fallback}" : $fallback;
}

/** <style> içine yazılacak :root{} CSS değişken bloğunu üretir. */
function render_theme_vars(): string
{
    $vars = [
        '--bg'            => theme_value('theme_bg_color'),
        '--text'          => theme_value('theme_text_color'),
        '--accent'        => theme_value('theme_accent_color'),
        '--muted'         => theme_value('theme_muted_color'),
        '--border'        => theme_value('theme_border_color'),
        '--card-bg'       => theme_value('theme_card_bg'),
        '--btn-text'      => theme_value('theme_btn_text_color'),
        '--font-heading'  => font_family_css(theme_value('theme_font_heading_name'), 'Georgia, serif'),
        '--font-body'     => font_family_css(theme_value('theme_font_body_name'), 'system-ui, -apple-system, sans-serif'),
        '--fs-base'       => theme_value('theme_font_size_base'),
        '--fs-h1'         => theme_value('theme_font_size_h1'),
        '--header-height' => theme_value('theme_header_height'),
        '--radius'        => theme_value('theme_radius'),
        '--btn-radius'    => theme_value('theme_btn_radius'),
        '--max-width'     => theme_value('theme_max_width'),
        '--section-gap'   => theme_value('theme_section_gap'),
    ];
    $out = ':root{';
    foreach ($vars as $k => $v) {
        $out .= $k . ':' . css_safe($v) . ';';
    }
    return $out . '}';
}

/** Google Fonts <link> adresini ayarlardaki font adlarından üretir. */
function google_fonts_link(): ?string
{
    $names = array_unique(array_filter([
        theme_value('theme_font_heading_name'),
        theme_value('theme_font_body_name'),
    ]));
    if (!$names) {
        return null;
    }
    $params = [];
    foreach ($names as $name) {
        $encoded = str_replace('%20', '+', rawurlencode(trim($name)));
        $params[] = 'family=' . $encoded . ':wght@400;500;600;700';
    }
    return 'https://fonts.googleapis.com/css2?' . implode('&', $params) . '&display=swap';
}
