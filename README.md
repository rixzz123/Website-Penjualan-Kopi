
  KOPI NUSANTARA – Website Marketplace

-----------------------------------------
  DESKRIPSI PROYEK
-----------------------------------------
Website Marketplace Agen Kopi Nusantara
berbasis PHP Native dengan koneksi database
MySQL/MariaDB. Fitur: Autentikasi (Login/Register),
Katalog Produk, Detail Produk, Keranjang Belanja,
Checkout, Panel Admin/Agen (CRUD Produk + Transaksi).

-----------------------------------------
  KEBUTUHAN SISTEM
-----------------------------------------
- PHP 7.4 atau lebih baru
- MySQL / MariaDB
- Web Server: Apache (XAMPP/WAMPP)
- Browser modern

-----------------------------------------
  STRUKTUR FOLDER
-----------------------------------------
kopinusantara/
├── index.php             (Beranda)
├── katalog.php           (Katalog Produk)
├── detail.php            (Detail Produk)
├── keranjang.php         (Keranjang & Checkout)
├── sukses.php            (Halaman sukses order)
├── login.php             (Halaman Login)
├── register.php          (Halaman Register)
├── logout.php            (Logout)
├── includes/
│   ├── koneksi.php       (Koneksi database)
│   ├── auth.php          (Fungsi autentikasi & helper)
│   ├── header.php        (Header/Navbar)
│   └── footer.php        (Footer)
├── admin/
│   ├── dashboard.php     (Dashboard Agen)
│   ├── produk.php        (CRUD Produk)
│   ├── transaksi.php     (Data Transaksi)
│   └── sidebar.php       (Sidebar Admin)
├── assets/
│   ├── css/style.css     (Stylesheet utama)
│   └── js/main.js        (JavaScript)
├── uploads/
│   └── produk/           (Folder gambar produk)
└── readme.txt            (Dokumentasi ini)

-----------------------------------------
  FITUR APLIKASI
-----------------------------------------
HALAMAN PUBLIK:
  [✓] Beranda dengan banner & produk unggulan
  [✓] Katalog produk dengan filter & search
  [✓] Detail produk (deskripsi, stok, agen)
  [✓] Tambah ke keranjang
  [✓] Keranjang & checkout ke tabel transaksi
  [✓] Register akun baru
  [✓] Login dengan deteksi role

PANEL AGEN:
   Dashboard dengan statistik
   CRUD Produk (tambah, lihat, edit, hapus)
   Upload gambar produk
   Data transaksi + ubah status
