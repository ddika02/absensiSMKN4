<?php
session_start();
require_once '../config/koneksi.php';
require_once '../includes/config.php';

// Debug
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Validasi role
if ($_SESSION['role'] != 'guru' && $_SESSION['role'] != 'admin') {
    header("Location: ../dashboard.php");
    exit();
}

$id_absensi = $_GET['id'] ?? null;
if (!$id_absensi) {
    die("ID absensi tidak ditemukan.");
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Cek guru berdasarkan user_id
$query_guru = "SELECT * FROM guru WHERE user_id = '$user_id'";
$result_guru = mysqli_query($koneksi, $query_guru);

if (!$result_guru || mysqli_num_rows($result_guru) === 0) {
    die("Data guru tidak ditemukan. Silakan hubungi administrator.");
}

$guru = mysqli_fetch_assoc($result_guru);
$guru_id = $guru['id'];

// Validasi kepemilikan absensi
$query_cek = "SELECT a.*, j.guru_id FROM absensi a
              JOIN jadwal j ON a.id_jadwal = j.id
              WHERE a.id = '$id_absensi'";
$result_cek = mysqli_query($koneksi, $query_cek);

if (!$result_cek || mysqli_num_rows($result_cek) == 0) {
    die("Absensi tidak ditemukan.");
}

$absensi_row = mysqli_fetch_assoc($result_cek);
if ($role == 'guru' && $absensi_row['guru_id'] != $guru_id) {
    die("<div class='alert alert-danger mt-5 text-center'>Akses ditolak. Anda tidak memiliki hak untuk melihat absensi ini.</div>");
}

// Ambil data absensi lengkap
$query_absensi = "SELECT a.*, mp.nama_mapel, k.nama_kelas, g.nama as nama_guru 
                 FROM absensi a
                 JOIN jadwal j ON a.id_jadwal = j.id
                 JOIN guru g ON j.guru_id = g.id
                 JOIN mapel mp ON j.mapel_id = mp.id
                 JOIN kelas k ON j.kelas_id = k.id
                 WHERE a.id = '$id_absensi'";
$result_absensi = mysqli_query($koneksi, $query_absensi);
$absensi = mysqli_fetch_assoc($result_absensi);

// Detail absensi siswa
$query_detail = "SELECT ad.*, s.nis, s.nama_siswa 
                FROM absensi_detail ad
                JOIN siswa s ON ad.id_siswa = s.id
                WHERE ad.id_absensi = '$id_absensi'
                ORDER BY s.nama_siswa ASC";
$result_detail = mysqli_query($koneksi, $query_detail);

// Rekap jumlah status
$query_count = "SELECT 
               SUM(CASE WHEN status = 'Hadir' THEN 1 ELSE 0 END) as jml_hadir,
               SUM(CASE WHEN status = 'Izin' THEN 1 ELSE 0 END) as jml_izin,
               SUM(CASE WHEN status = 'Sakit' THEN 1 ELSE 0 END) as jml_sakit,
               SUM(CASE WHEN status = 'Alpha' THEN 1 ELSE 0 END) as jml_alpha
               FROM absensi_detail WHERE id_absensi = '$id_absensi'";
$result_count = mysqli_query($koneksi, $query_count);
$count = mysqli_fetch_assoc($result_count);

$success = isset($_GET['success']) ? "<div class='alert alert-success'>Data berhasil disimpan</div>" : "";
$update = isset($_GET['update']) ? "<div class='alert alert-success'>Data berhasil diperbarui</div>" : "";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Absensi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php include '../includes/styles.php'; ?>
</head>
<body>
<?php include '../includes/sidebar.php'; ?>
<div class="main-content" id="content">
    <?php include '../includes/navbar.php'; ?>
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3">Detail Absensi</h1>
            <div>
                <a href="index.php" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
                <a href="cetak.php?id=<?= $id_absensi ?>" class="btn btn-primary btn-sm" target="_blank"><i class="fas fa-print"></i> Cetak</a>
            </div>
        </div>

        <?= $success ?>
        <?= $update ?>

        <div class="card mb-4">
            <div class="card-header"><strong>Informasi Absensi</strong></div>
            <div class="card-body row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr><th>Tanggal</th><td>: <?= date('d-m-Y', strtotime($absensi['tanggal'])) ?></td></tr>
                        <tr><th>Mapel</th><td>: <?= $absensi['nama_mapel'] ?></td></tr>
                        <tr><th>Kelas</th><td>: <?= $absensi['nama_kelas'] ?></td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr><th>Pertemuan</th><td>: <?= $absensi['pertemuan'] ?></td></tr>
                        <tr><th>Guru</th><td>: <?= $absensi['nama_guru'] ?></td></tr>
                        <tr><th>Materi</th><td>: <?= $absensi['materi'] ?: '-' ?></td></tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Card Rekap Absensi Model Dashboard -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card shadow-sm border-start border-4 border-success">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-success fw-bold mb-1">HADIR</h6>
                            <h2 class="fw-bold"><?= $count['jml_hadir'] ?></h2>
                        </div>
                        <div class="display-5 text-success">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card shadow-sm border-start border-4 border-warning">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-warning fw-bold mb-1">IZIN</h6>
                            <h2 class="fw-bold"><?= $count['jml_izin'] ?></h2>
                        </div>
                        <div class="display-5 text-warning">
                            <i class="fas fa-envelope-open-text"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card shadow-sm border-start border-4 border-info">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-info fw-bold mb-1">SAKIT</h6>
                            <h2 class="fw-bold"><?= $count['jml_sakit'] ?></h2>
                        </div>
                        <div class="display-5 text-info">
                            <i class="fas fa-notes-medical"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card shadow-sm border-start border-4 border-danger">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-danger fw-bold mb-1">ALPHA</h6>
                            <h2 class="fw-bold"><?= $count['jml_alpha'] ?></h2>
                        </div>
                        <div class="display-5 text-danger">
                            <i class="fas fa-times-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><strong>Detail Absensi Siswa</strong></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead><tr><th>No</th><th>NIS</th><th>Nama</th><th>Status</th><th>Keterangan</th></tr></thead>
                        <tbody>
                        <?php $no=1; while ($row = mysqli_fetch_assoc($result_detail)): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= $row['nis'] ?></td>
                                <td><?= $row['nama_siswa'] ?></td>
                                <td><?= $row['status'] ?></td>
                                <td><?= $row['keterangan'] ?: '-' ?></td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<?php include '../includes/scripts.php'; ?>
</body>
</html>
