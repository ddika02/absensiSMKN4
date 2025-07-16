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
    header("Location: index.php?status=error&message=ID+kelas+tidak+ditemukan");
    exit();
}

$id = $_GET['id'];

// Ambil data kelas berdasarkan id
$query_kelas = "SELECT k.*, g.nama 
               FROM kelas k 
               LEFT JOIN guru g ON k.wali_kelas = g.id 
               WHERE k.id = '$id'";
$result_kelas = mysqli_query($koneksi, $query_kelas);

if (mysqli_num_rows($result_kelas) == 0) {
    header("Location: index.php?status=error&message=Data+kelas+tidak+ditemukan");
    exit();
}

$kelas = mysqli_fetch_assoc($result_kelas);

// Hitung jumlah siswa
$query_jumlah_siswa = "SELECT COUNT(*) as total FROM siswa WHERE id_kelas = '$id'";
$result_jumlah_siswa = mysqli_query($koneksi, $query_jumlah_siswa);
$row_jumlah_siswa = mysqli_fetch_assoc($result_jumlah_siswa);
$jumlah_siswa = $row_jumlah_siswa['total'];

// Ambil data siswa di kelas ini
$query_siswa = "SELECT * FROM siswa WHERE id_kelas = '$id' ORDER BY nama_siswa ASC";
$result_siswa = mysqli_query($koneksi, $query_siswa);

// Ambil data jadwal pelajaran untuk kelas ini
$query_jadwal = "SELECT jm.*, mp.nama_mapel, g.nama 
                FROM jadwal jm 
                JOIN mapel mp ON jm.mapel_id = mp.id 
                JOIN guru g ON jm.guru_id = g.id 
                WHERE jm.kelas_id = '$id' 
                ORDER BY jm.hari, jm.jam_mulai";
$result_jadwal = mysqli_query($koneksi, $query_jadwal);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Kelas - Sistem Absensi SMKN 4 Tasikmalaya</title>
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
                <h1 class="h3 mb-0 text-gray-800">Detail Kelas</h1>
                <a href="index.php" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <!-- Content Row -->
            <div class="row">
                <div class="col-lg-8">
                    <div class="card shadow mb-4">
                        <div class="profile-header">
                            <h2><?php echo $kelas['nama_kelas']; ?></h2>
                            <p class="mb-0">Tingkat: <?php echo $kelas['tingkat']; ?></p>
                            <p>Jurusan: <?php echo $kelas['jurusan']; ?></p>
                        </div>
                        <div class="profile-info">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="info-label">Wali Kelas</div>
                                        <div class="info-value"><?php echo $kelas['nama']; ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="info-label">Tahun Ajaran</div>
                                        <div class="info-value"><?php echo $kelas['tahun_ajaran']; ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="info-label">Semester</div>
                                        <div class="info-value"><?php echo $kelas['semester']; ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="info-label">Jumlah Siswa</div>
                                        <div class="info-value"><?php echo $jumlah_siswa; ?> siswa</div>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if ($_SESSION['role'] == 'admin'): ?>
                            <div class="d-flex justify-content-end mt-4">
                                <a href="edit.php?id=<?php echo $kelas['id']; ?>" class="btn btn-primary me-2">
                                    <i class="fas fa-edit me-1"></i> Edit Data
                                </a>
                                <a href="index.php?action=delete&id=<?php echo $kelas['id']; ?>" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus data kelas ini?')">
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
                                    <i class="fas fa-user-graduate"></i>
                                </div>
                                <div class="stats-value"><?php echo $jumlah_siswa; ?></div>
                                <div class="stats-label">Total Siswa</div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="stats-card">
                                <div class="stats-icon">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div class="stats-value"><?php echo mysqli_num_rows($result_jadwal); ?></div>
                                <div class="stats-label">Jadwal Pelajaran</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Daftar Siswa -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Daftar Siswa</h6>
                        </div>
                        <div class="card-body">
                            <?php if (mysqli_num_rows($result_siswa) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="5%">No</th>
                                                <th width="15%">NIS</th>
                                                <th width="30%">Nama Siswa</th>
                                                <th width="15%">Jenis Kelamin</th>
                                                <th width="20%">No. Telepon</th>
                                                <th width="15%">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $no = 1;
                                            while ($siswa = mysqli_fetch_assoc($result_siswa)): 
                                            ?>
                                            <tr>
                                                <td><?php echo $no++; ?></td>
                                                <td><?php echo $siswa['nis']; ?></td>
                                                <td><?php echo $siswa['nama_siswa']; ?></td>
                                                <td><?php echo $siswa['jenis_kelamin']; ?></td>
                                                <td><?php echo !empty($siswa['no_telp']) ? $siswa['no_telp'] : '-'; ?></td>
                                                <td>
                                                    <a href="../siswa/detail.php?id=<?php echo $siswa['id']; ?>" class="btn btn-info btn-sm">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i> Belum ada siswa di kelas ini.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Jadwal Pelajaran -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Jadwal Pelajaran</h6>
                        </div>
                        <div class="card-body">
                            <?php if (mysqli_num_rows($result_jadwal) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="5%">No</th>
                                                <th width="15%">Hari</th>
                                                <th width="15%">Jam</th>
                                                <th width="30%">Mata Pelajaran</th>
                                                <th width="35%">Guru Pengajar</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $no = 1;
                                            while ($jadwal = mysqli_fetch_assoc($result_jadwal)): 
                                                // Konversi hari dari angka ke nama hari
                                                $hari = $jadwal['hari'];

                                            ?>
                                            <tr>
                                                <td><?php echo $no++; ?></td>
                                                <td><?php echo $hari; ?></td>
                                                <td><?php echo substr($jadwal['jam_mulai'], 0, 5) . ' - ' . substr($jadwal['jam_selesai'], 0, 5); ?></td>
                                                <td><?php echo $jadwal['nama_mapel']; ?></td>
                                                <td><?php echo $jadwal['nama']; ?></td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i> Belum ada jadwal pelajaran untuk kelas ini.
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