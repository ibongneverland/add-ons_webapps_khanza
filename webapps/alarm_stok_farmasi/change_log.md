## [v1.1.0] — 18 April 2026, 11:51 WIB
### 🔒 Keamanan / ⚡ Optimasi
- **[AUTH]** Mengimplementasikan sistem validasi login berbasis AES Decrypt sesuai standar SIMKES Khanza.
- **[SECURITY]** Menambahkan `auth.php` sebagai proteksi Zero-Trust pada seluruh halaman UI dan endpoint API.
- **[PDO]** Migrasi total dari ekstensi `mysqli` menjadi *PHP Data Objects* (PDO) untuk seluruh query database.
- **[CONFIG]** Merombak arsitektur `koneksi.php` agar Plug-and-Play mendeteksi otomatis letak `conf.php` dari parent direcories maupun server document root.
- **[UI]** Menambahkan tombol Logout beserta konfirmasi modal bergaya Bootstrap 5.
