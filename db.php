<?php
// db.php — file koneksi database

$host = "localhost";     // biasanya: localhost
$user = "root";          // default XAMPP
$pass = "";              // default XAMPP kosong
$db   = "topsis";     // ganti sesuai nama database kamu

$conn = mysqli_connect($host, $user, $pass, $db);

// Cek koneksi
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Optional: set charset biar tidak error karakter
mysqli_set_charset($conn, "utf8mb4");
?>
