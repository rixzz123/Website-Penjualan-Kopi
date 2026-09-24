<?php
$conn = new mysqli("localhost", "root", "", "db_kopi_nusantara", 3307);

if ($conn->connect_error) {
    die($conn->connect_error);
}

echo "Koneksi berhasil!";