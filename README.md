# Laravel Web Installer

Installer web ala script CodeCanyon untuk **project Laravel apa saja** (Laravel 10 / 11 / 12 / 13).
Upload script → buka URL → installer muncul otomatis → selesai.

## Alur installer

1. **Welcome**
2. **Requirements**: cek versi PHP & extension
3. **Permissions**: cek folder `storage`, `bootstrap/cache` dan `.env` bisa ditulis
4. **Database**: isi nama/URL aplikasi + data database → **Test Connection** (tombol lanjut baru aktif setelah koneksi berhasil)
5. **Admin / Super Admin**: form akun admin, field-nya bisa diatur sendiri (lihat [Akun Super Admin](#akun-super-admin)), atau dimatikan
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
| `admin.*` | Halaman akun admin, lihat [Akun Super Admin](#akun-super-admin) |
| `redirect_after_install`, `login_url` | Tujuan tombol di halaman selesai |
| `except` | Path yang tidak di-redirect ke installer sebelum terinstall (misal `api/*`) |

## Akun Super Admin

Setelah **Test Connection** berhasil dan data database disimpan, installer menampilkan halaman pembuatan akun admin.
Data yang diisi disimpan dulu di session, lalu akunnya dibuat pada tahap **Install** (setelah migrate & seed),
jadi tabel `users` sudah pasti ada. Setelah instalasi selesai, halaman ini ikut terkunci bersama seluruh installer.

Semua diatur di bagian `admin` pada `config/installer.php` (publish dulu dengan `--tag=installer-config`).

| Opsi | Fungsi |
|---|---|
| `enabled` | `false` = langkah admin dilewati (langsung ke Install) |
| `title`, `description`, `step_label` | Judul halaman, keterangan, dan label di stepper |
| `model` | Model user (default `App\Models\User`) |
| `identifier` | Kolom untuk mencari user yang sudah ada (default `email`). Kalau installer dijalankan ulang, user ini di-update, bukan dibuat dobel |
| `fields` | Daftar field di form (lihat di bawah) |
| `attributes` | Kolom tetap yang dipaksa ke user, contoh `['is_admin' => true, 'status' => 'active']` |
| `role` | Nama role untuk spatie/laravel-permission (`$user->assignRole()`) |
| `callback` | Class invokable yang dipanggil setelah user disimpan, untuk logika custom |

### Format `fields`

Key = nama input sekaligus nama kolom di tabel (kecuali diatur lain lewat `column`).

| Properti | Keterangan |
|---|---|
| `label` | Label field |
| `type` | `text`, `email`, `password`, `tel`, `number`, atau `select` |
| `rules` | Aturan validasi Laravel. `confirmed` otomatis menampilkan input "Confirm ..." |
| `options` | Untuk `select`: `['value' => 'Label']` |
| `default` | Nilai awal |
| `hint` | Teks bantuan kecil di bawah field |
| `column` | Nama kolom tujuan jika berbeda dari key, atau `false` = tidak disimpan ke tabel (hanya dikirim ke `callback`) |

Field bertipe `password` otomatis di-hash (bcrypt).

### Contoh: Super Admin dengan username

```php
'admin' => [
    'enabled' => true,
    'title' => 'Super Admin',
    'description' => 'Akun ini memiliki akses penuh ke aplikasi.',
    'step_label' => 'Super Admin',
    'model' => App\Models\User::class,
    'identifier' => 'username',

    'fields' => [
        'name'     => ['label' => 'Nama Lengkap', 'rules' => 'required|string|max:255'],
        'username' => ['label' => 'Username', 'rules' => 'required|alpha_dash|max:50', 'hint' => 'Dipakai untuk login'],
        'email'    => ['label' => 'Email', 'type' => 'email', 'rules' => 'required|email|max:255'],
        'phone'    => ['label' => 'No. HP', 'type' => 'tel', 'rules' => 'nullable|string|max:20'],
        'password' => ['label' => 'Password', 'type' => 'password', 'rules' => 'required|string|min:8|confirmed'],
    ],

    'attributes' => ['is_superadmin' => true],
    'role' => 'super-admin',
    'callback' => null,
],
```

> Kolom seperti `username`, `phone`, `is_superadmin` harus sudah ada di tabel `users` (buat lewat migration project Anda).

### Contoh: callback untuk logika custom

Cocok untuk kebutuhan seperti membuat data profil, menyimpan ke tabel lain, atau mengisi setting awal:

```php
// config/installer.php
'callback' => App\Installer\SetupSuperAdmin::class,

'fields' => [
    // ...
    'company' => ['label' => 'Nama Perusahaan', 'rules' => 'required|string', 'column' => false],
],
```

```php
// app/Installer/SetupSuperAdmin.php
namespace App\Installer;

class SetupSuperAdmin
{
    public function __invoke($user, array $data): void
    {
        // $data berisi semua input form (password sudah di-hash)
        \App\Models\Setting::updateOrCreate(['key' => 'company_name'], ['value' => $data['company']]);
        $user->profile()->create(['position' => 'Owner']);
    }
}
```

### Tanpa akun admin

Set `'enabled' => false`. Alurnya jadi Database → Install, dan Anda bisa membuat admin lewat seeder sendiri.

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
