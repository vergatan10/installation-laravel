# Laravel Web Installer

Installer web ala script CodeCanyon untuk **project Laravel apa saja** (Laravel 10 / 11 / 12 / 13).
Upload script → buka URL → installer muncul otomatis → selesai.

## Alur installer

1. **Welcome** (+ **Installer Key** jika diaktifkan, lihat [Installer Key](#installer-key-opsional))
2. **Requirements**: cek versi PHP & extension
3. **Permissions**: cek folder `storage`, `bootstrap/cache` dan `.env` bisa ditulis
4. **Database**: isi nama/URL aplikasi + data database → **Test Connection** (tombol lanjut baru aktif setelah koneksi berhasil)
5. **Pengaturan** *(opsional)*: field `.env` tambahan seperti email/SMTP, payment, API key (lihat [Pengaturan .env Tambahan](#pengaturan-env-tambahan))
6. **Admin / Super Admin** *(opsional)*: form akun admin, field-nya bisa diatur sendiri (lihat [Akun Super Admin](#akun-super-admin))
7. **Install**: otomatis menjalankan:
   - menulis `.env` (APP_ENV, APP_DEBUG)
   - `APP_KEY` (dibuat otomatis saat pertama kali dibuka)
   - `migrate --force`
   - `db:seed --force`
   - `storage:link`
   - membuat akun admin
   - perintah artisan tambahan (opsional)
   - `optimize:clear`
   - membuat file lock `storage/installed`
8. **Selesai**: installer terkunci. Semua URL `/install` otomatis di-redirect ke halaman utama, dan website berjalan normal.

Tampilan tersedia dalam **English** dan **Bahasa Indonesia** (lihat [Bahasa](#bahasa)). Tidak perlu license key.

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
php artisan vendor:publish --tag=installer-views    # resources/views/vendor/installer (ubah tampilan)
php artisan vendor:publish --tag=installer-lang     # lang/vendor/installer (ubah / tambah terjemahan)
```

Opsi di `config/installer.php`:

| Opsi | Fungsi |
|---|---|
| `route_prefix` | URL installer (default `install`) |
| `locale`, `locales` | Bahasa default & pilihan bahasa, lihat [Bahasa](#bahasa) |
| `access_key` | Kode akses installer, lihat [Installer Key](#installer-key-opsional) |
| `requirements.php` / `requirements.extensions` | Versi PHP minimum & extension wajib |
| `permissions` | Folder/file yang wajib writable |
| `database_drivers` | Pilihan database di form |
| `migrate`, `seed`, `seeder` | Jalankan migrate/seed, dan seeder khusus |
| `storage_link` | Jalankan `storage:link` |
| `commands` | Perintah artisan tambahan, contoh `['permission:cache-reset', 'app:setup' => ['--force' => true]]` |
| `settings.*` | Halaman pengaturan `.env` tambahan, lihat [Pengaturan .env Tambahan](#pengaturan-env-tambahan) |
| `admin.*` | Halaman akun admin, lihat [Akun Super Admin](#akun-super-admin) |
| `redirect_after_install`, `login_url` | Tujuan tombol di halaman selesai |
| `except` | Path yang tidak di-redirect ke installer sebelum terinstall (misal `api/*`) |

---

## Installer Key (opsional)

Tanpa key, **siapa pun** yang membuka domain Anda sebelum diinstal bisa menjalankan installer (lalu memasang database & akun admin miliknya).
Dengan installer key, halaman Welcome meminta kode dulu sebelum langkah lain bisa dibuka.

Cara paling mudah, tambahkan ke `.env.example` project Anda:

```dotenv
INSTALLER_KEY=kode-rahasia-anda
```

Atau langsung di config:

```php
'access_key' => env('INSTALLER_KEY', 'kode-rahasia-anda'),
```

- Kosong / tidak diset = installer **tanpa** kode (perilaku default).
- Key dibaca langsung dari file `.env` meskipun `.env` baru dibuat dari `.env.example` pada kunjungan pertama.
- Salah memasukkan kode 5 kali → harus menunggu 60 detik.
- Berikan kode ini ke orang yang akan menginstal (misal pembeli), jangan dicantumkan di repo publik.

---

## Pengaturan .env Tambahan

Halaman **Database** selalu menanyakan `APP_*` dan `DB_*`. Kebutuhan `.env` lainnya (email, payment gateway, API key, fitur on/off, dll.)
didefinisikan di `settings.groups`. Setiap grup tampil sebagai satu kotak di halaman **Pengaturan**, dan nilainya langsung ditulis ke `.env`.

Jika `groups` kosong (default), halaman Pengaturan **tidak muncul**.

| Properti grup | Keterangan |
|---|---|
| `title` | Judul grup |
| `description` | Keterangan di bawah judul (opsional) |
| `fields` | Daftar field. **Key = nama variabel `.env`** |

| Properti field | Keterangan |
|---|---|
| `label` | Label field |
| `type` | `text`, `email`, `password`, `number`, `url`, `tel`, `select`, atau `boolean` (checkbox → `true`/`false`) |
| `rules` | Aturan validasi Laravel (default `nullable`) |
| `options` | Untuk `select`: `['value' => 'Label']` |
| `default` | Nilai awal jika `.env` belum punya nilai |
| `hint` | Teks bantuan kecil |

Nilai awal diambil dari `.env` yang ada (biasanya dari `.env.example`), lalu `default`.

### Contoh: Email (SMTP) + payment + fitur

```php
'settings' => [
    'title' => null,          // null = "Pengaturan" / "Settings"
    'description' => null,
    'step_label' => null,

    'groups' => [
        'mail' => [
            'title' => 'Email (SMTP)',
            'description' => 'Dipakai untuk kirim email reset password dan notifikasi.',
            'fields' => [
                'MAIL_MAILER' => ['label' => 'Mailer', 'type' => 'select', 'options' => ['smtp' => 'SMTP', 'log' => 'Log (tidak dikirim)'], 'default' => 'smtp', 'rules' => 'required'],
                'MAIL_HOST' => ['label' => 'SMTP Host', 'rules' => 'nullable|string'],
                'MAIL_PORT' => ['label' => 'SMTP Port', 'type' => 'number', 'default' => 587, 'rules' => 'nullable|integer'],
                'MAIL_USERNAME' => ['label' => 'SMTP Username', 'rules' => 'nullable|string'],
                'MAIL_PASSWORD' => ['label' => 'SMTP Password', 'type' => 'password', 'rules' => 'nullable|string'],
                'MAIL_FROM_ADDRESS' => ['label' => 'Email Pengirim', 'type' => 'email', 'rules' => 'nullable|email'],
            ],
        ],

        'payment' => [
            'title' => 'Midtrans',
            'fields' => [
                'MIDTRANS_SERVER_KEY' => ['label' => 'Server Key', 'type' => 'password', 'rules' => 'nullable|string'],
                'MIDTRANS_CLIENT_KEY' => ['label' => 'Client Key', 'rules' => 'nullable|string'],
                'MIDTRANS_PRODUCTION' => ['label' => 'Mode Production', 'type' => 'boolean', 'default' => false],
            ],
        ],

        'features' => [
            'title' => 'Fitur',
            'fields' => [
                'FEATURE_REGISTRATION' => ['label' => 'Izinkan registrasi publik', 'type' => 'boolean', 'default' => true],
            ],
        ],
    ],
],
```

> Pengaturan ini untuk **`.env`**. Data yang berhubungan dengan akun admin (username, no. HP, dll.) diatur terpisah di `admin.fields`.

---

## Akun Super Admin

Installer menampilkan halaman pembuatan akun admin setelah langkah Database (dan Pengaturan, jika ada).
Data yang diisi disimpan dulu di session, lalu akunnya dibuat pada tahap **Install** (setelah migrate & seed),
jadi tabel `users` sudah pasti ada. Setelah instalasi selesai, halaman ini ikut terkunci bersama seluruh installer.

| Opsi | Fungsi |
|---|---|
| `enabled` | `false` = langkah admin dilewati |
| `title`, `description`, `step_label` | Judul halaman, keterangan, dan label di stepper (`null` = teks bawaan sesuai bahasa) |
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
| `type` | `text`, `email`, `password`, `tel`, `number`, `url`, atau `select` |
| `rules` | Aturan validasi Laravel. `confirmed` otomatis menampilkan input "Konfirmasi ..." |
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

Set `'enabled' => false`. Langkah admin dilewati, dan Anda bisa membuat admin lewat seeder sendiri.

---

## Bahasa

Installer sudah tersedia dalam **English (`en`)** dan **Bahasa Indonesia (`id`)**.

```php
'locale' => 'id',               // bahasa default
'locales' => [                  // pilihan bahasa di pojok atas installer
    'en' => 'English',
    'id' => 'Bahasa Indonesia',
],
```

- Hanya satu bahasa di `locales` = pilihan bahasa disembunyikan.
- Pesan validasi Bahasa Indonesia sudah disertakan. Jika project Anda punya `lang/id/validation.php` sendiri, file itu yang dipakai.
- Label di config (`title`, `label`, `hint`, `description`) boleh teks biasa **atau** key terjemahan, misal `'label' => 'installer.admin_phone'` (diambil dari `lang/id/installer.php` project Anda).

### Mengubah teks atau menambah bahasa

```bash
php artisan vendor:publish --tag=installer-lang
```

File akan disalin ke `lang/vendor/installer/{en,id}/`. Ubah teksnya di sana, atau tambah bahasa baru dengan menyalin folder `en` menjadi, misalnya, `ms`, lalu daftarkan di `locales`.

---

## Perintah artisan

```bash
php artisan installer:lock     # tandai sudah terinstall (matikan installer)
php artisan installer:unlock   # buka lagi installer (untuk install ulang)
```

## Upgrade dari versi sebelumnya

Jika Anda pernah publish `config/installer.php`, opsi baru tidak otomatis masuk ke file tersebut. Tambahkan manual
(lihat config bawaan di `vendor/vergatan10/installation-laravel/config/installer.php`), atau publish ulang (menimpa file lama):

```bash
php artisan vendor:publish --tag=installer-config --force
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
