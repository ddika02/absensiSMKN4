<?php
session_start();
require_once '../config/koneksi.php';
require_once '../includes/config.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Cek apakah ada parameter id
if (!isset($_GET['id'])) {
    header("Location: index.php?status=error&message=ID+siswa+tidak+ditemukan");
    exit();
}

$id = $_GET['id'];

// Ambil data siswa berdasarkan id
$query_siswa = "SELECT s.*, k.nama_kelas 
               FROM siswa s 
               JOIN kelas k ON s.id_kelas = k.id 
               WHERE s.id = '$id'";
$result_siswa = mysqli_query($koneksi, $query_siswa);

if (mysqli_num_rows($result_siswa) == 0) {
    header("Location: index.php?status=error&message=Data+siswa+tidak+ditemukan");
    exit();
}

$siswa = mysqli_fetch_assoc($result_siswa);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Siswa - Sistem Absensi SMKN 4 Tasikmalaya</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <?php include '../includes/styles.php'; ?>
</head>
<body>
    <!-- Sidebar -->
    <?php include '../includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Navbar -->
        <?php include '../includes/navbar.php'; ?>

        <!-- Page Content -->
        <div class="container-fluid">
            <!-- Page Heading -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Detail Siswa</h1>
                <a href="index.php" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <!-- Content Row -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow mb-4">
                        <div class="profile-header">
                            <h2><?php echo $siswa['nama_siswa']; ?></h2>
                            <p class="mb-0">NIS: <?php echo $siswa['nis']; ?></p>
                            <p>Kelas: <?php echo $siswa['nama_kelas']; ?></p>
                        </div>
                        <div class="profile-info">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="info-label">Jenis Kelamin</div>
                                        <div class="info-value"><?php echo $siswa['jenis_kelamin']; ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="info-label">Tempat, Tanggal Lahir</div>
                                        <div class="info-value">
                                            <?php echo $siswa['tempat_lahir']; ?>, 
                                            <?php echo !empty($siswa['tanggal_lahir']) ? date('d F Y', strtotime($siswa['tanggal_lahir'])) : '-'; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="info-label">Nomor Telepon</div>
                                        <div class="info-value">
                                            <?php echo !empty($siswa['no_telp']) ? $siswa['no_telp'] : '-'; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="info-label">Alamat</div>
                                        <div class="info-value"><?php echo $siswa['alamat']; ?></div>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if ($_SESSION['role'] == 'admin'): ?>
                            <div class="d-flex justify-content-end mt-4">
                                <a href="edit.php?id=<?php echo $siswa['id']; ?>" class="btn btn-primary me-2">
                                    <i class="fas fa-edit me-1"></i> Edit Data
                                </a>
                                <a href="index.php?action=delete&id=<?php echo $siswa['id']; ?>" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus data siswa ini?')">
                                    <i class="fas fa-trash-alt me-1"></i> Hapus Data
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <?php include '../includes/scripts.php'; ?>
</body>
</html>