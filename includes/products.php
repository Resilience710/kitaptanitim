<?php
/**
 * Ürün (kitap) veri katmanı.
 */

/** Ana sayfa hero'sunda gösterilecek öne çıkan kitabı döndürür. */
function get_featured_product(): ?array
{
    $sql = "SELECT * FROM products
            WHERE status = 'published'
            ORDER BY is_featured DESC, sort_order ASC, created_at DESC
            LIMIT 1";
    $row = db()->query($sql)->fetch();
    return $row ?: null;
}

/** Koleksiyon bölümünde gösterilecek diğer yayınlanmış kitaplar. */
function get_collection_products(int $excludeId = 0): array
{
    $stmt = db()->prepare(
        "SELECT * FROM products
         WHERE status = 'published' AND id <> ?
         ORDER BY sort_order ASC, created_at DESC"
    );
    $stmt->execute([$excludeId]);
    return $stmt->fetchAll();
}

/** Admin listesi için tüm kitaplar (taslaklar dahil). */
function get_all_products(): array
{
    return db()->query(
        "SELECT * FROM products
         ORDER BY is_featured DESC, sort_order ASC, created_at DESC"
    )->fetchAll();
}

function get_product_by_id(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM products WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

function get_product_by_slug(string $slug): ?array
{
    $stmt = db()->prepare('SELECT * FROM products WHERE slug = ? LIMIT 1');
    $stmt->execute([$slug]);
    return $stmt->fetch() ?: null;
}

/** Bir kitabın görsellerini (önce birincil) döndürür. */
function get_product_images(int $productId): array
{
    $stmt = db()->prepare(
        'SELECT * FROM product_images
         WHERE product_id = ?
         ORDER BY is_primary DESC, sort_order ASC, id ASC'
    );
    $stmt->execute([$productId]);
    return $stmt->fetchAll();
}

/** Bir kitabın birincil (kapak) görselini döndürür. */
function get_primary_image(int $productId): ?array
{
    $images = get_product_images($productId);
    return $images[0] ?? null;
}

/** Bir görsel satırından gösterilecek kaynağı (varsa) çözer. */
function image_src(?array $image, string $fallback = 'assets/img/placeholder-cover.svg'): string
{
    $path = $image['file_path'] ?? '';
    if ($path === '') {
        $path = $fallback;
    }
    return asset_url($path);
}

/**
 * Bir slug'ın benzersiz olmasını sağlar (gerekirse sonuna sayı ekler).
 * $ignoreId: güncellenen kaydın kendi id'si (kendisiyle çakışmayı yok sayar).
 */
function unique_slug(string $base, int $ignoreId = 0): string
{
    $base = slugify($base);
    $slug = $base;
    $i = 2;
    while (true) {
        $stmt = db()->prepare('SELECT id FROM products WHERE slug = ? AND id <> ? LIMIT 1');
        $stmt->execute([$slug, $ignoreId]);
        if (!$stmt->fetch()) {
            return $slug;
        }
        $slug = $base . '-' . $i;
        $i++;
    }
}
