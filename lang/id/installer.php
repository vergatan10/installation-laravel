<?php

return [

    'wizard' => 'Wizard Instalasi',
    'language' => 'Bahasa',

    'steps' => [
        'welcome' => 'Selamat Datang',
        'requirements' => 'Persyaratan',
        'permissions' => 'Izin Folder',
        'database' => 'Database',
        'settings' => 'Pengaturan',
        'admin' => 'Admin',
        'install' => 'Instal',
    ],

    'common' => [
        'back' => 'Kembali',
        'next' => 'Lanjut',
        'check_again' => 'Cek Ulang',
        'ok' => 'OK',
        'failed' => 'GAGAL',
        'enabled' => 'Aktif',
        'missing' => 'Tidak ada',
        'not_found' => 'tidak ditemukan',
        'previous_steps' => 'Silakan selesaikan langkah sebelumnya terlebih dahulu.',
        'env_not_writable' => 'File .env tidak bisa ditulis.',
        'form_errors' => 'Silakan periksa kembali isian formulir.',
    ],

    'welcome' => [
        'title' => 'Selamat Datang',
        'intro' => 'Wizard ini akan menginstal :app di server Anda dalam beberapa langkah.',
        'prepare' => 'Sebelum mulai, siapkan:',
        'prepare_db' => 'Database kosong (MySQL / MariaDB / PostgreSQL / SQL Server) atau gunakan SQLite',
        'prepare_db_credentials' => 'Host, port, nama database, username dan password database',
        'prepare_settings' => 'Pengaturan tambahan: :groups',
        'prepare_admin' => 'Data untuk :title (:fields)',
        'info' => 'Installer akan menulis file .env, menjalankan migrasi dan seeder database, membuat storage link, lalu mengunci dirinya sendiri setelah selesai.',
        'start' => 'Cek Persyaratan',
    ],

    'access' => [
        'title' => 'Kode Installer',
        'intro' => 'Installer ini dilindungi. Masukkan kode installer yang Anda terima untuk melanjutkan.',
        'label' => 'Kode Installer',
        'submit' => 'Buka',
        'invalid' => 'Kode installer salah.',
        'throttled' => 'Terlalu banyak percobaan. Coba lagi dalam :seconds detik.',
        'unlocked' => 'Installer terbuka.',
    ],

    'requirements' => [
        'title' => 'Persyaratan Server',
        'intro' => 'Server Anda harus memenuhi persyaratan berikut.',
        'php' => 'PHP >= :version',
        'extension' => 'Extension :name',
    ],

    'permissions' => [
        'title' => 'Izin Folder',
        'intro' => 'File dan folder berikut harus bisa ditulis oleh web server (biasanya 775 untuk folder, 664 untuk file).',
    ],

    'check' => [
        'failed' => 'Ada pengecekan yang gagal. Perbaiki di server Anda (atau hubungi penyedia hosting), lalu klik Cek Ulang.',
    ],

    'database' => [
        'title' => 'Aplikasi & Database',
        'intro' => 'Buat database kosong di panel hosting (cPanel, Plesk, dll.), lalu isi detailnya di bawah dan klik Tes Koneksi.',
        'application' => 'Aplikasi',
        'app_name' => 'Nama Aplikasi',
        'app_url' => 'URL Aplikasi',
        'environment' => 'Environment',
        'env_production' => 'Production',
        'env_staging' => 'Staging',
        'env_local' => 'Local / Development',
        'debug' => 'Aktifkan mode debug',
        'debug_hint' => 'Matikan untuk website yang sudah live.',
        'database' => 'Database',
        'type' => 'Jenis Database',
        'sqlite_path' => 'Lokasi File SQLite',
        'sqlite_hint' => 'File akan dibuat otomatis jika belum ada.',
        'host' => 'Host',
        'port' => 'Port',
        'name' => 'Nama Database',
        'username' => 'Username',
        'password' => 'Password',
        'test' => 'Tes Koneksi',
        'testing' => 'Mengetes...',
        'save' => 'Simpan & Lanjut',
        'connected' => 'Koneksi berhasil!',
        'connect_failed' => 'Tidak bisa terhubung ke database: :error',
        'request_failed' => 'Request gagal: :error',
    ],

    'settings' => [
        'title' => 'Pengaturan',
        'intro' => 'Atur pengaturan aplikasi lainnya. Anda bisa mengubahnya nanti di file .env.',
    ],

    'admin' => [
        'title' => 'Akun Administrator',
        'description' => 'Akun ini akan dibuat setelah database terinstal. Gunakan untuk login.',
        'name' => 'Nama Lengkap',
        'email' => 'Email',
        'password' => 'Password',
        'confirm' => 'Konfirmasi :field',
        'missing' => 'Data administrator tidak ditemukan, silakan kembali ke langkah akun.',
        'identifier_missing' => 'Kolom identifier admin [:column] tidak ada di daftar field admin.',
        'ready' => ':title :id siap digunakan.',
    ],

    'run' => [
        'title' => 'Instal',
        'intro' => 'Semua sudah siap. Klik Instal Sekarang dan jangan tutup halaman ini sampai selesai.',
        'install_now' => 'Instal Sekarang',
        'installing' => 'Menginstal...',
        'retry' => 'Coba Lagi',
        'pending' => 'Menunggu',
        'running' => 'Berjalan...',
        'done' => 'Selesai',
        'warning' => 'Peringatan',
        'failed' => 'Gagal',
        'server_error' => 'Server error (HTTP :status). Cek storage/logs/laravel.log.',
        'request_failed' => 'Request gagal: :error',
        'complete_title' => 'Instalasi Selesai!',
        'complete_intro' => ':app berhasil diinstal. Installer sekarang sudah terkunci.',
        'admin_login' => 'Login Admin',
        'go_website' => 'Buka Website',
    ],

    'tasks' => [
        'environment' => 'Menulis konfigurasi environment',
        'key' => 'Membuat application key',
        'migrate' => 'Menjalankan migrasi database',
        'seed' => 'Mengisi data awal (seeder)',
        'storage' => 'Membuat storage link',
        'admin' => 'Membuat :title',
        'commands' => 'Menjalankan perintah tambahan',
        'optimize' => 'Membersihkan cache',
        'finish' => 'Menyelesaikan instalasi',
    ],

    'messages' => [
        'env_set' => 'APP_ENV=:env, APP_DEBUG=:debug',
        'key_generated' => 'Application key baru dibuat.',
        'key_set' => 'Application key sudah ada.',
        'migrated' => 'Migrasi selesai.',
        'seeded' => 'Seeder selesai.',
        'storage_exists' => 'public/storage sudah ada.',
        'symlink_disabled' => 'Fungsi symlink() dinonaktifkan di server ini. Buat link public/storage secara manual.',
        'storage_linked' => 'Storage link dibuat.',
        'storage_failed' => 'Gagal membuat storage link: :error',
        'commands_done' => 'Perintah tambahan selesai.',
        'caches_cleared' => 'Cache dibersihkan.',
        'caches_failed' => 'Tidak semua cache bisa dibersihkan: :error',
        'lock_failed' => 'Gagal menulis file lock :path',
        'completed' => 'Instalasi selesai.',
    ],

];
