# Deploy ke Shared Hosting

Panduan deploy aplikasi Laravel ke shared hosting.

## Persiapan

### 1. lokal - Build Assets

```bash
npm run build
```

### 2. lokal - Export Database

```bash
# Export database terbaru
mysqldump -u username -p database_name > database_name.sql
```

---

## Langkah Deploy

### 1. Upload File

Upload semua file ke shared hosting (melalui FTP/cPanel File Manager), **kecuali**:
- `.env` (jangan upload, akan dibuat manual di hosting)
- `node_modules/`
- `vendor/` (bisa di-generate via composer, atau upload semua)

**Rekomendasi:** Upload semua file termasuk `vendor/` untuk mempercepat deploy.

### 2. Setup .env

Buat file `.env` di root folder dengan konfigurasi yang sesuai:

```env
APP_NAME="MediumERP"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-anda.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=nama_database
DB_USERNAME=username_database
DB_PASSWORD=password_database

LOG_CHANNEL=daily
LOG_LEVEL=debug
```

### 3. Setup Folder Storage

```bash
# Buat symbolic link untuk storage
ln -s /home/username/storage/app/public /home/username/public_html/storage

# Atau via cPanel File Manager:
# Buat folder: storage/app/public
# Buat folder: storage/framework/cache/data
# Buat folder: storage/framework/sessions
# Buat folder: storage/framework/views
# Copy .gitignore dari storage ke masing-masing folder
```

### 4. Install/Update Dependencies

```bash
# Install composer dependencies
composer install --optimize-autoloader --no-dev

# Generate application key
php artisan key:generate
```

### 5. Konfigurasi Database

**Opsi A: Import Database Manual**
```bash
# Upload dan import file SQL
mysql -u username -p database_name < database_name.sql
```

**Opsi B: Manual Migration SQL** (untuk shared hosting)

File SQL tersedia di folder `database/migrations_sql/`:

1. Login ke cPanel -> PHPMyAdmin
2. Import file-file SQL berikut secara berurutan:
   - `database/migrations_sql/all_migrations.sql` (semua migration)
   - Atau import satu per satu sesuai kebutuhan

### 6. Konfigurasi Path (Jika Perlu)

Jika shared hosting menggunakan `public_html` sebagai folder public:

**Opsi A: Ubah index.php**

Edit `public/index.php` (atau `index.php` di root jika menggunakan public_html):

```php
// Tambahkan sebelum require autoload
require __DIR__.'/../vendor/autoload.php';

// Ubah path bootstrap
$app = require_once __DIR__.'/../bootstrap/app.php';
```

**Opsi B: Setup .htaccess**

Tambahkan `.htaccess` di root folder:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

### 7. Set Permissions

```bash
# Folder yang perlu writable
chmod 755 storage -R
chmod 755 bootstrap/cache -R
```

### 8. Clear Cache

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### 9. Optimasi (Production)

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

---

## Troubleshooting

### Error 500 / White Page

1. Check `storage/logs/laravel.log`
2. Pastikan `.env` sudah benar
3. Pastikan permissions sudah benar (`755` untuk folder, `644` untuk file)

### Assets Tidak Tampil

1. Pastikan `public/build` sudah diupload
2. Cek `.env` dengan `ASSET_URL` jika menggunakan CDN:
   ```env
   ASSET_URL=https://cdn.domain-anda.com
   ```

### Database Connection Error

1. Cek kredensial DB di `.env`
2. Pastikan user DB sudah dibuat dan有权限
3. Pastikan `DB_HOST` sesuai (biasanya `localhost`)

### Migration Error

Jika ada error saat migration, jalankan SQL manual via PHPMyAdmin dari folder `database/migrations_sql/`

---

## Struktur Folder di Shared Hosting

```
/home/username/
├── public_html/          (atau public/)
│   ├── index.php
│   ├── build/
│   └── .htaccess
├── storage/
│   ├── app/public/
│   └── framework/
├── vendor/
└── .env
```

---

## Update Deployment

Untuk update deployment selanjutnya:

```bash
# 1. Upload file perubahan
# 2. Clear cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 3. Jika ada migration baru
php artisan migrate

# 4. Rebuild cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
