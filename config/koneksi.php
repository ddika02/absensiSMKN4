<?php
// Konfigurasi database
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'db_absensi_smkn4';

// Membuat koneksi ke database
$koneksi = mysqli_connect($host, $username, $password, $database);

// Cek koneksi
if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Set karakter encoding
mysqli_set_charset($koneksi, "utf8");
?>