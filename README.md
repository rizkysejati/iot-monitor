# IoT Monitor - Dashboard Monitoring Sensor IoT

Proyek UAS Pemrograman Web (Kelompok 4, Kelas B, Kasus 4).
PHP native + PDO + MySQL/MariaDB, tanpa framework.

## Anggota
- Steven Spielberg (H1051241090)
- Marsha Erliani (H1051251008)
- Rizky Dwi Karunia Sejati (H1051251054)
- Eurin Jaya Limbun (H1051251094)
- Redo Anthus (H1051251096)

## Cara pasang (XAMPP)
1. Salin folder proyek ke `C:\xampp\htdocs\iot-monitor`.
2. Nyalakan Apache dan MySQL di XAMPP.
3. Buka phpMyAdmin, tab Import, pilih `database/iot_monitor.sql`.
4. Jika perlu, sesuaikan user/password di `config/database.php`.
5. Buka http://localhost/iot-monitor/ lalu login.

Akun uji: `admin` / `admin123`

## Struktur folder
- `config/` koneksi PDO (`database.php`) dan pengaturan (`app.php`)
- `auth/` login dan logout
- `includes/` header, footer, fungsi bantu, pemeriksa login, CSRF
- `perangkat/`, `sensor/` CRUD (daftar, tambah, ubah, hapus)
- `dashboard.php` ringkasan, filter, statistik, riwayat
- `api/terima_data.php` endpoint penerima data alat
- `assets/` CSS dan JS
- `tools/` simulator ESP32 dan peragaan rollback
- `database/` berkas `.sql`

## Fitur
- Login admin (`password_hash` / `password_verify`, session)
- CRUD perangkat dan sensor, dengan pencarian dan pagination
- Dashboard: data terbaru per sensor, statistik (AVG/MAX/MIN), riwayat, filter perangkat dan tanggal, penanda "Bahaya", segarkan otomatis 10 detik
- API penerima data dengan API key dan kode status HTTP

## Keamanan
- Prepared statements PDO (anti SQL injection)
- `htmlspecialchars` lewat `e()` (anti XSS)
- Token CSRF untuk semua form POST
- Cookie sesi httponly + SameSite, `session_regenerate_id` saat login
- Validasi input di sisi server

## Transaction
`api/terima_data.php` menjalankan INSERT pembacaan dan UPDATE `terakhir_aktif` dalam satu transaction. Gagal salah satu, semuanya dibatalkan (`rollBack`).

## Uji API
```
http://localhost/iot-monitor/api/terima_data.php?api_key=<API_KEY>&id_sensor=<ID_ANGKA>&nilai=40
```
Balasan JSON: 201 sukses, 401 key salah, 403 perangkat nonaktif, 404 sensor tidak cocok, 405 metode salah, 422 data tidak valid, 500 galat server.

## Simulator ESP32 (untuk demo)
Jalankan di terminal dari folder proyek:
```
php tools/simulator_esp32.php            # kirim tiap 5 detik
php tools/simulator_esp32.php 3          # tiap 3 detik
```
Buka dashboard dengan "Segarkan otomatis" aktif, angka akan berubah sendiri. Hentikan dengan Ctrl+C.

## Peragaan rollback
```
php tools/demo_rollback.php
```
Menampilkan jumlah pembacaan sebelum dan sesudah transaction yang sengaja digagalkan; jumlahnya sama.
