<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); 
define('DB_NAME', '');

try {
    $koneksi = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $koneksi->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    die("<div style='color:#721c24; background-color:#f8d7da; border:1px solid #f5c6cb; padding:20px; margin:20px; font-family:sans-serif; border-radius:5px;'>
            <h3>❌ Password Database Masih Ditolak Hosting, Bre!</h3>
            <p><b>Pesan dari Server:</b> " . $e->getMessage() . "</p>
            <hr>
            <p><b>Solusi Langkah Akhir:</b></p>
            <p>Di InfinityFree, password MySQL itu <b>BUKAN</b> password yang lu pake buat login ke website InfinityFree. Tapi password khusus akun hosting lu.</p>
            <p>Coba buka tab baru, masuk ke <b>Client Area InfinityFree</b>, klik akun lu (<code>if0_42155093</code>), lalu cari bagian <b>Account Details -> Password</b>. Nah, copas password bawaan dari sana, terus ganti bagian <code>DB_PASS</code> di file ini pake password itu.</p>
         </div>");
}
?>