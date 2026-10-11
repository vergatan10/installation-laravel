# Laravel Web Installer

Installer web ala script CodeCanyon untuk **project Laravel apa saja** (Laravel 10 / 11 / 12 / 13).
Upload script → buka URL → installer muncul otomatis → selesai.

## Alur installer

1. **Welcome**
2. **Requirements**: cek versi PHP & extension
3. **Permissions**: cek folder `storage`, `bootstrap/cache` dan `.env` bisa ditulis
4. **Database**: isi nama/URL aplikasi + data database → **Test Connection** (tombol lanjut baru aktif setelah koneksi berhasil)
5. **Admin**: buat akun administrator (bisa dimatikan)
6. **Install**: otomatis menjalankan:
   - menulis `.env` (APP_NAME, APP_URL, APP_ENV, APP_DEBUG, DB_*)
   - `APP_KEY` (dibuat otomatis saat pertama kali dibuka)
   - `migrate --force`
   - `db:seed --force`
   - `storage:link`
   - membuat akun admin
   - perintah artisan tambahan (opsional)
   - `optimize:clear`
   - membuat file lock `storage/installed`
7. **Selesai**: installer terkunci. Semua URL `/install` otomatis di-redirect ke halaman utama, dan website berjalan normal.

> Tidak perlu license key.

---

## 1. Upload package ini ke GitHub

```bash
cd installation-laravel
git init
git add .
git commit -m "Laravel web installer"
git branch -M main
git remote add origin https://github.com/vergatan10/installation-laravel.git
git push -u origin main

# beri versi (disarankan)
git tag v1.0.0
git push --tags
```

> Kalau mau ganti nama package, ubah `"name"` di `composer.json` (misal `namaanda/nama-package`).

## 2. Pasang di project Laravel Anda

Tambahkan repository GitHub tadi ke `composer.json` **project Laravel Anda**:

```json
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/vergatan10/installation-laravel"
    }
]
```

Lalu:

```bash
composer require vergatan10/installation-laravel:^1.0
# atau tanpa tag:  composer require vergatan10/installation-laravel:dev-main
```

Selesai. Service provider terdaftar otomatis (package auto-discovery).

> Repo private? Jalankan `composer config --global github-oauth.github.com TOKEN_GITHUB_ANDA` dulu.

## 3. Distribusi ke server / pembeli

- Pastikan `.env` **tidak** ikut di-upload (installer akan membuatnya dari `.env.example`).
- Pastikan `storage/installed` **tidak** ikut di-upload (secara default sudah di-ignore oleh `storage/.gitignore`).
- Upload project (beserta folder `vendor` jika server tidak punya akses composer), arahkan domain ke folder `public`.
- Buka `https://domain-anda.com` → otomatis diarahkan ke `/install`.

### PENTING: project yang SUDAH berjalan

Jika package ini ditambahkan ke project yang sudah live, jalankan sekali di server:

```bash
php artisan installer:lock
```

Kalau tidak, website akan diarahkan ke installer karena file lock belum ada.

---

## Konfigurasi (opsional)

```bash
php artisan vendor:publish --tag=installer-config   # config/installer.php
php artisan vendor:publish --tag=installer-views    # resources/views/vendor/installer (ubah tampilan/bahasa)
```

Opsi penting di `config/installer.php`:

| Opsi | Fungsi |
|---|---|
| `route_prefix` | URL installer (default `install`) |
| `requirements.php` / `requirements.extensions` | Versi PHP minimum & extension wajib |
| `permissions` | Folder/file yang wajib writable |
| `database_drivers` | Pilihan database di form |
| `migrate`, `seed`, `seeder` | Jalankan migrate/seed, dan seeder khusus |
| `storage_link` | Jalankan `storage:link` |
| `commands` | Perintah artisan tambahan, contoh `['permission:cache-reset', 'app:setup' => ['--force' => true]]` |
| `admin.enabled` | Tampilkan langkah buat admin |
| `admin.model` | Model user (default `App\Models\User`) |
| `admin.attributes` | Kolom tambahan, contoh `['is_admin' => true, 'role' => 'admin']` |
| `admin.role` | Nama role untuk spatie/laravel-permission (`assignRole`) |
| `redirect_after_install`, `login_url` | Tujuan tombol di halaman selesai |
| `except` | Path yang tidak di-redirect ke installer sebelum terinstall (misal `api/*`) |

## Perintah artisan

```bash
php artisan installer:lock     # tandai sudah terinstall (matikan installer)
php artisan installer:unlock   # buka lagi installer (untuk install ulang)
```

## Catatan

- **Seeder & `--no-dev`**: `DatabaseSeeder` bawaan Laravel memakai `User::factory()` (butuh Faker, yang termasuk dev dependency). Kalau server di-install dengan `composer install --no-dev`, buat seeder khusus produksi dan set `'seeder' => 'Database\\Seeders\\ProductionSeeder'`, atau `'seed' => false`.
- **Kode yang mengakses database saat boot** (misal `AppServiceProvider` yang mengambil setting dari tabel) akan error sebelum database dipasang. Bungkus dengan pengecekan:
  ```php
  if (\Vergatan10\Installer\Support\Installer::isInstalled()) { /* ... */ }
  ```
- Selama belum terinstall, session/cache dipaksa memakai driver `file` dan queue memakai `sync`, sehingga installer tetap jalan walaupun `.env.example` memakai driver `database`.
- Kenapa tidak menghapus file installer? Package ada di `vendor/`, yang akan dibuat ulang setiap `composer install`. Sebagai gantinya installer **dikunci** dengan `storage/installed`: semua route `/install` langsung redirect ke home, dan middleware redirect tidak didaftarkan sama sekali, jadi tidak ada beban tambahan.
- `storage:link` gagal di hosting yang menonaktifkan `symlink()`; installer hanya memberi peringatan dan tetap melanjutkan.

## Lisensi

MIT
