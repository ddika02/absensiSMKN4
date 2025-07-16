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
    header("Location: index.php?status=error&message=ID+guru+tidak+ditemukan");
    exit();
}

$id = $_GET['id'];

// Ambil data guru berdasarkan id
$query_guru = "SELECT * FROM guru WHERE id = '$id'";
$result_guru = mysqli_query($koneksi, $query_guru);

if (mysqli_num_rows($result_guru) == 0) {
    header("Location: index.php?status=error&message=Data+guru+tidak+ditemukan");
    exit();
}

$guru = mysqli_fetch_assoc($result_guru);

// Ambil jumlah jadwal mengajar
$query_jadwal = "SELECT COUNT(*) as total_jadwal FROM jadwal WHERE guru_id = '$id'";
$result_jadwal = mysqli_query($koneksi, $query_jadwal);
$row_jadwal = mysqli_fetch_assoc($result_jadwal);
$total_jadwal = $row_jadwal['total_jadwal'];

// Ambil jumlah kelas yang diajar
$query_kelas = "SELECT COUNT(DISTINCT kelas_id) as total_kelas FROM jadwal WHERE guru_id = '$id'";
$result_kelas = mysqli_query($koneksi, $query_kelas);
$row_kelas = mysqli_fetch_assoc($result_kelas);
$total_kelas = $row_kelas['total_kelas'];

// Ambil jumlah mata pelajaran yang diajar
$query_mapel = "SELECT COUNT(DISTINCT mapel_id) as total_mapel FROM jadwal WHERE guru_id = '$id'";
$result_mapel = mysqli_query($koneksi, $query_mapel);
$row_mapel = mysqli_fetch_assoc($result_mapel);
$total_mapel = $row_mapel['total_mapel'];

// Ambil data jadwal mengajar
$query_jadwal_detail = "SELECT jm.*, mp.nama_mapel, k.nama_kelas 
                        FROM jadwal jm 
                        JOIN mapel mp ON jm.mapel_id = mp.id 
                        JOIN kelas k ON jm.kelas_id = k.id 
                        WHERE jm.guru_id = '$id' 
                        ORDER BY jm.hari, jm.jam_mulai";
$result_jadwal_detail = mysqli_query($koneksi, $query_jadwal_detail);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Guru - Sistem Absensi SMKN 4 Tasikmalaya</title>
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
                <h1 class="h3 mb-0 text-gray-800">Detail Guru</h1>
                <a href="index.php" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <!-- Content Row -->
            <div class="row">
                <div class="col-lg-8">
                    <div class="card shadow mb-4">
                        <div class="profile-header">
                            <h2><?php echo $guru['nama']; ?></h2>
                            <p class="mb-0">NIP: <?php echo $guru['nip']; ?></p>
                            <?php if ($guru['nama']): ?>
                                <span class="badge bg-success mt-2">Memiliki Akun User</span>
                            <?php endif; ?>
                        </div>
                        <div class="profile-info">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="info-label">Jenis Kelamin</div>
                                        <div class="info-value"><?php echo $guru['jenis_kelamin']; ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="info-label">Tempat, Tanggal Lahir</div>
                                        <div class="info-value">
                                            <?php echo $guru['tempat_lahir']; ?>, 
                                            <?php echo date('d F Y', strtotime($guru['tanggal_lahir'])); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="info-label">Email</div>
                                        <div class="info-value"><?php echo $guru['email']; ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="info-label">Nomor Telepon</div>
                                        <div class="info-value">
                                            <?php echo !empty($guru['no_telp']) ? $guru['no_telp'] : '-'; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="info-item">
                                        <div class="info-label">Alamat</div>
                                        <div class="info-value"><?php echo $guru['alamat']; ?></div>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if ($_SESSION['role'] == 'admin'): ?>
                            <div class="d-flex justify-content-end mt-4">
                                <a href="edit.php?id=<?php echo $guru['id']; ?>" class="btn btn-primary me-2">
                                    <i class="fas fa-edit me-1"></i> Edit Data
                                </a>
                                <a href="index.php?action=delete&id=<?php echo $guru['id']; ?>" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus data guru ini?')">
                                    <i class="fas fa-trash-alt me-1"></i> Hapus Data
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="stats-card">
                                <div class="stats-icon">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div class="stats-value"><?php echo $total_jadwal; ?></div>
                                <div class="stats-label">Total Jadwal Mengajar</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="stats-card">
                                <div class="stats-icon">
                                    <i class="fas fa-school"></i>
                                </div>
                                <div class="stats-value"><?php echo $total_kelas; ?></div>
                                <div class="stats-label">Kelas Diajar</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="stats-card">
                                <div class="stats-icon">
                                    <i class="fas fa-book"></i>
                                </div>
                                <div class="stats-value"><?php echo $total_mapel; ?></div>
                                <div class="stats-label">Mata Pelajaran</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Jadwal Mengajar -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Jadwal Mengajar</h6>
                        </div>
                        <div class="card-body">
                            <?php if (mysqli_num_rows($result_jadwal_detail) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="5%">No</th>
                                                <th width="15%">Hari</th>
                                                <th width="15%">Jam</th>
                                                <th width="30%">Mata Pelajaran</th>
                                                <th width="35%">Kelas</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php 
    $no = 1;
    while ($jadwal = mysqli_fetch_assoc($result_jadwal_detail)): 
        // Validasi nilai hari
        $hari = !empty($jadwal['hari']) ? $jadwal['hari'] : 'Hari tidak valid';
        // Pastikan hari adalah salah satu dari nilai yang valid
        $hari_valid = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        if (!in_array($hari, $hari_valid)) {
            $hari = 'Hari tidak valid';
        }
?>

                                            <tr>
                                                <td><?php echo $no++; ?></td>
                                                <td><?php echo $hari; ?></td>
                                                <td><?php echo substr($jadwal['jam_mulai'], 0, 5) . ' - ' . substr($jadwal['jam_selesai'], 0, 5); ?></td>
                                                <td><?php echo $jadwal['nama_mapel']; ?></td>
                                                <td><?php echo $jadwal['nama_kelas']; ?></td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i> Guru ini belum memiliki jadwal mengajar.
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