<?php
session_start();
require_once '../config/koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Cek role (hanya admin yang boleh mengakses halaman ini)
if ($_SESSION['role'] != 'admin') {
    header("Location: ../dashboard.php");
    exit();
}

// Ambil ID jadwal dari parameter URL
if (!isset($_GET['id'])) {
    header("Location: index.php?status=error&message=" . urlencode("ID jadwal tidak ditemukan"));
    exit();
}

$id_jadwal = mysqli_real_escape_string($koneksi, $_GET['id']);

// Hapus jadwal
$query = "DELETE FROM jadwal WHERE id = '$id_jadwal'";
$result = mysqli_query($koneksi, $query);

if ($result) {
    header("Location: index.php?status=success&message=" . urlencode("Jadwal berhasil dihapus"));
} else {
    header("Location: index.php?status=error&message=" . urlencode("Gagal menghapus jadwal: " . mysqli_error($koneksi)));
}
exit();