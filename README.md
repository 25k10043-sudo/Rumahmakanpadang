# RM Padang Digital

Website pemesanan menu Padang dan panel operasional staf berbasis Laravel 13, PHP 8.3+, dan SQLite.

## Fitur saat ini

- Katalog menu responsif, kategori lauk, keranjang, serta pilihan makan di tempat/bungkus/antar.
- Pesanan tersimpan di database, nomor pesanan dan halaman pelacakan digital.
- Panel login admin/staf, antrean pesanan, pergantian status, pencatatan pembayaran, dan toggle ketersediaan lauk.
- Ringkasan omzet hanya terlihat oleh admin. Pembayaran QRIS/kartu dicatat oleh staf; payment gateway belum diintegrasikan.

## Menjalankan secara lokal

Prasyarat: PHP 8.3+ dengan ekstensi SQLite, Composer, dan Node.js/npm.

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
New-Item -ItemType File -Path database\database.sqlite -Force
php artisan migrate --seed
npm.cmd install
npm.cmd run build
php artisan serve
```

Buka `http://127.0.0.1:8000`. Database lokal berada di `database/database.sqlite`.

### Akun demo lokal

- Admin: `admin@padang.local` / `PadangAdmin123!`
- Staf: `staf@padang.local` / `StafPadang123!`

Akun demo hanya dibuat saat seeding di luar environment production. Untuk membuat admin pertama pada production, isi `ADMIN_EMAIL` dan `ADMIN_PASSWORD` di environment hosting sebelum menjalankan seeder. Jangan menggunakan kata sandi demo di server publik.

## Tes

```powershell
composer test
```

## Deployment

Konfigurasi [Railway](https://railway.com) tersedia di `Dockerfile` dan `railway.json`. Buat layanan PostgreSQL dan hubungkan repository ke Railway. Set environment variable berikut pada layanan web:

| Variable | Value |
|---|---|
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | `https://${{RAILWAY_PUBLIC_DOMAIN}}` |
| `APP_TIMEZONE` | `Asia/Jakarta` |
| `APP_KEY` | Hasil `php artisan key:generate --show` |
| `DB_CONNECTION` | `pgsql` |
| `DB_URL` | `${{Postgres.DATABASE_URL}}` |
| `SESSION_SECURE_COOKIE` | `true` |
| `ADMIN_EMAIL` / `ADMIN_PASSWORD` | Email admin dan kata sandi kuat yang Anda buat |

Container menjalankan migrasi otomatis saat mulai. Setelah deploy pertama, jalankan `php artisan db:seed --force` sekali dari Railway shell untuk membuat data menu dan akun admin. Seeder production tidak membuat akun demo. Railway menyediakan domain HTTPS; middleware aplikasi mengarahkan HTTP ke HTTPS dan mempercayai forwarded protocol dari proxy hosting.

Gunakan PostgreSQL terkelola untuk data production; SQLite pada filesystem instance sementara dapat kehilangan data setelah redeploy. Link publik baru tersedia setelah repository GitHub terhubung ke akun Railway Anda dan deployment berhasil.
