# Kitap Tanıtım Sitesi

Etsy'de satılan kitap(lar) için minimal, tek sayfalık tanıtım sitesi ve tam özellikli
yönetim paneli. **PHP 8 + MySQL** ile yazılmıştır; **derleme adımı / Node.js gerektirmez**,
GüzelHosting gibi paylaşımlı cPanel hosting paketlerinde doğrudan çalışır.

## Özellikler

- **Tek sayfa landing**: ince/şeffaf üst menü, sağda 3B kitap görseli, solda başlık + 2 buton
  (Etsy'de Satın Al / İncele), "Hakkında" ve "Koleksiyon" bölümleri, kaydırma animasyonları.
- **Kitap detay sayfası**: çoklu görsel galerisi, açıklama, fiyat, Etsy butonu (`/kitap/{slug}`).
- **Çok kitap desteği**: dilediğiniz kadar kitap ekleyin; biri "öne çıkan" olarak ana sayfada,
  diğerleri koleksiyon bölümünde görünür.
- **Yönetim paneli (`/admin`)**: her şey özelleştirilebilir.
  - **Görünüm**: renkler, Google Fonts yazı tipleri, yazı boyutları, üst menü kalınlığı, köşe yuvarlaklığı, sayfa genişliği.
  - **İçerik**: tüm başlıklar, metinler, buton yazıları, logo, favicon, sosyal/Etsy bağlantıları.
  - **Kitaplar**: ekle/düzenle/sil, öne çıkanı seç, çoklu görsel yükle/sırala/kapak seç.
- **Güvenlik**: oturum girişi, şifre hashleme, CSRF koruması, PDO hazır ifadeler, güvenli görsel yükleme.

## Teknoloji

- PHP 8.0+ (PDO/MySQL), saf PHP — framework veya Composer bağımlılığı yok.
- MySQL / MariaDB (utf8mb4).
- Saf CSS (CSS değişkenleriyle tema) + bağımlılıksız vanilla JavaScript.

## Kurulum (cPanel / GüzelHosting)

1. **Veritabanı oluşturun**: cPanel → *MySQL Veritabanları* → bir veritabanı ve bir kullanıcı
   oluşturun, kullanıcıyı veritabanına **TÜM YETKİLER** ile ekleyin. (Ad, kullanıcı, şifreyi not alın.)
2. **PHP sürümü**: cPanel → *MultiPHP Manager* → alan adınız için **PHP 8.x** seçin.
3. **Dosyaları yükleyin**: Bu projenin tüm dosyalarını `public_html` içine yükleyin
   (cPanel *Dosya Yöneticisi* ile zip yükleyip "Extract" yapabilir veya FTP kullanabilirsiniz).
   > `.htaccess` gibi gizli dosyaların da yüklendiğinden emin olun (Dosya Yöneticisi'nde
   > *Settings → Show Hidden Files* açık olsun).
4. **İzinler**: `uploads/` klasörünün izinlerini **755** yapın (görsel yükleme için).
5. **Sihirbazı çalıştırın**: tarayıcıda `https://alanadiniz.com/install.php` adresini açın,
   veritabanı bilgilerinizi ve yönetici kullanıcı adı/şifrenizi girin, "Kurulumu Tamamla" deyin.
6. **install.php dosyasını SİLİN** (kurulum bitince güvenlik için). 
7. Siteyi `https://alanadiniz.com/` adresinden, yönetimi `https://alanadiniz.com/admin` adresinden açın.

### Alternatif: elle veritabanı kurulumu

Sihirbaz yerine cPanel → *phpMyAdmin* → veritabanını seçin → *Içe Aktar (Import)* →
`database/schema.sql` dosyasını yükleyin. Ardından `config.sample.php` dosyasını
`config.php` olarak kopyalayıp veritabanı bilgilerinizi girin.
Bu yöntemde varsayılan giriş: kullanıcı **admin**, şifre **admin123** — *giriş yapınca hemen değiştirin.*

## Sık Karşılaşılan Durumlar

- **`/kitap/...` adresleri açılmıyor (404)**: Sunucuda `mod_rewrite` kapalı olabilir ya da
  site bir alt klasörde olabilir.
  - Alt klasör ise `.htaccess` içindeki `# RewriteBase /` satırını açıp klasör adını yazın
    (örn. `RewriteBase /site/`) ve `config.php` içindeki `'base'` değerini `'/site'` yapın.
  - Yine de çalışmazsa `config.php` içinde `'pretty_urls' => false` yapın; site
    `product.php?slug=...` biçiminde sorunsuz çalışmaya devam eder.
- **Görsel yüklenemiyor**: `uploads/` klasörü izinlerini 755 yapın; sunucunun
  `upload_max_filesize` / `post_max_size` değerlerinin en az 4 MB olduğundan emin olun.
- **Veritabanına bağlanılamadı**: `config.php` içindeki bilgileri kontrol edin; cPanel'de
  sunucu adı genellikle `localhost`'tur.

## Yerel Geliştirme

```bash
# MySQL'i kurup bir veritabanı oluşturun, sonra:
php -S localhost:8000
# Tarayıcıda http://localhost:8000/install.php açın.
```
> Yerleşik `php -S` sunucusu `.htaccess`'i işlemez; bu yüzden yerelde güzel adresler yerine
> `product.php?slug=...` ile test edin. Gerçek sunucuda (Apache) güzel adresler çalışır.

## Dizin Yapısı

```
index.php / product.php   Ön yüz sayfaları
install.php               Kurulum sihirbazı (kurulumdan sonra silinir)
config.php                Kuruluma özel ayarlar (otomatik üretilir, git'e girmez)
.htaccess                 Yönlendirme + güvenlik
includes/                 Çekirdek PHP (db, ayarlar, tema, auth, ürünler, şablonlar)
admin/                    Yönetim paneli
assets/                   CSS, JS, görseller
uploads/                  Yüklenen görseller
database/schema.sql       Veritabanı şeması + örnek içerik
```

## Şifre Değiştirme

Şu an panel üzerinde şifre değiştirme ekranı yoktur. Şifreyi değiştirmek için phpMyAdmin'de
`admin_users` tablosundaki `password_hash` alanını güncelleyin (PHP `password_hash()` ile
üretilmiş bir bcrypt değeri olmalı). İleride bu ekran panele eklenebilir.
