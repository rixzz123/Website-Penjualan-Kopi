<?php
require_once 'includes/auth.php';
require_once 'includes/koneksi.php';
session_destroy();
header("Location: " . BASE_URL . "/login.php");
exit;
