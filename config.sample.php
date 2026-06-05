<?php
/**
 * ÖRNEK YAPILANDIRMA DOSYASI
 *
 * Bu dosyayı doğrudan düzenlemeyin. Kurulum sihirbazı (install.php) çalıştığında
 * sizin girdiğiniz bilgilerle "config.php" dosyası otomatik oluşturulur.
 *
 * Dilerseniz bu dosyayı "config.php" olarak kopyalayıp elle de düzenleyebilirsiniz.
 */
return [
    // Veritabanı bağlantı bilgileri (cPanel > MySQL Databases bölümünden alınır)
    'db' => [
        'host'    => 'localhost',   // GüzelHosting/cPanel'de neredeyse her zaman 'localhost'
        'name'    => 'veritabani_adi',
        'user'    => 'veritabani_kullanicisi',
        'pass'    => 'veritabani_sifresi',
        'charset' => 'utf8mb4',
    ],

    'app' => [
        // Site bir alt klasörde kuruluysa (örn. public_html/site) buraya '/site' yazın.
        // Kök dizinde (public_html) kuruluysa boş bırakın.
        'base'        => '',

        // .htaccess / mod_rewrite çalışıyorsa true bırakın (güzel adresler: /kitap/slug).
        // Adresler çalışmazsa false yapın (yedek: product.php?slug=...).
        'pretty_urls' => true,

        // 'production' veya 'development'. development hata detaylarını gösterir.
        'env'         => 'production',

        // Rastgele güvenlik anahtarı (kurulum sırasında otomatik üretilir).
        'key'         => 'change-me',
    ],
];
