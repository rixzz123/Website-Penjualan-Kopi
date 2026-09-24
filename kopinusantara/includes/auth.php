<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['id_user']);
}

function getRole() {
    return $_SESSION['role'] ?? null;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: " . BASE_URL . "/login.php");
        exit;
    }
}

function requireAgen() {
    requireLogin();
    if (getRole() !== 'agen') {
        header("Location: " . BASE_URL . "/index.php");
        exit;
    }
}

function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}
