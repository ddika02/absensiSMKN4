<?php
session_start();
require_once '../config/koneksi.php';
require_once '../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SESSION['role'] != 'guru' && $_SESSION['role'] != 'admin') {
    header("Location: ../dashboard.php");
    exit();
}

$role = $_SESSION['role'];
$nip = $_SESSION['username'];

// Pastikan parameter ID siswa ada
if (!isset($_GET['id'])) {
    header("Location: admin.php");
    exit();
}

$siswa_id = $_GET['id'];

// Query untuk mendapatkan informasi siswa
$query_siswa = "SELECT s.*, k.nama_kelas, k.tahun_ajaran, k.semester 
               FROM siswa s 
               JOIN kelas k ON s.id_kelas = k.id 
               WHERE s.id = '$siswa_id'";
$result_siswa = mysqli_query($koneksi, $query_siswa);

if (mysqli_num_rows($result_siswa) == 0) {
    header("Location: admin.php");
    exit();
}

$siswa = mysqli_fetch_assoc($result_siswa);

// Query rekap absensi untuk siswa tertentu
$query_detail = "
    SELECT 
        s.id AS siswa_id,
        s.nis,
        s.nama_siswa,
        COUNT(ad.id) AS total_pertemuan,
        SUM(ad.status = 'Hadir') AS hadir,
        SUM(ad.status = 'Izin') AS izin,
        SUM(ad.status = 'Sakit') AS sakit,
        SUM(ad.status = 'Alpha') AS alpha
    FROM absensi_detail ad
    JOIN siswa s ON ad.id_siswa = s.id
    JOIN absensi a ON ad.id_absensi = a.id
    JOIN jadwal j ON a.id_jadwal = j.id
    WHERE s.id = '$siswa_id'
";

if ($role == 'guru') {
    $query_detail .= " AND j.guru_id = (SELECT id FROM guru WHERE nip = '$nip')";
}

$query_detail .= " GROUP BY s.id";

$result_detail = mysqli_query($koneksi, $query_detail);

// Query untuk detail absensi per pertemuan
$query_pertemuan = "
    SELECT 
        a.tanggal,
        a.pertemuan,
        ad.status,
        ad.keterangan,
        m.nama_mapel,
        g.nama as nama_guru
    FROM absensi_detail ad
    JOIN absensi a ON ad.id_absensi = a.id
    JOIN jadwal j ON a.id_jadwal = j.id
    JOIN mapel m ON j.mapel_id = m.id
    JOIN guru g ON j.guru_id = g.id
    WHERE ad.id_siswa = '$siswa_id'
    ORDER BY a.tanggal DESC
";

$result_pertemuan = mysqli_query($koneksi, $query_pertemuan);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Absensi Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <?php include '../includes/styles.php'; ?>
</head>
<body>
    <!-- Sidebar -->
    <?php include '../includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content" id="content">
        <!-- Navbar -->
        <?php include '../includes/navbar.php'; ?>

        <div class="container-fluid mt-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">Detail Absensi Siswa</h4>
                <a href="admin.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
            </div>

            <!-- Informasi Siswa -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <strong>Informasi Siswa</strong>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="150">NIS</th>
                                    <td>: <?php echo $siswa['nis']; ?></td>
                                </tr>
                                <tr>
                                    <th>Nama Siswa</th>
                                    <td>: <?php echo $siswa['nama_siswa']; ?></td>
                                </tr>
                                <tr>
                                    <th>Kelas</th>
                                    <td>: <?php echo $siswa['nama_kelas']; ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="150">Tahun Ajaran</th>
                                    <td>: <?php echo $siswa['tahun_ajaran']; ?></td>
                                </tr>
                                <tr>
                                    <th>Semester</th>
                                    <td>: <?php echo $siswa['semester']; ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rekap Absensi -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <strong>Rekap Absensi</strong>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-primary">
                            <tr>
                                <th>NIS</th>
                                <th>Nama Siswa</th>
                                <th>Pertemuan</th>
                                <th>Hadir</th>
                                <th>Izin</th>
                                <th>Sakit</th>
                                <th>Alpha</th>
                                <th>Persentase Kehadiran</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (mysqli_num_rows($result_detail) > 0) {
                                $row = mysqli_fetch_assoc($result_detail);
                                $total = $row['total_pertemuan'];
                                $persentase = $total > 0 ? round(($row['hadir'] / $total) * 100, 2) : 0;
                                
                                echo "<tr>";
                                echo "<td>" . $row['nis'] . "</td>";
                                echo "<td>" . $row['nama_siswa'] . "</td>";
                                echo "<td>" . $row['total_pertemuan'] . "</td>";
                                echo "<td>" . $row['hadir'] . "</td>";
                                echo "<td>" . $row['izin'] . "</td>";
                                echo "<td>" . $row['sakit'] . "</td>";
                                echo "<td>" . $row['alpha'] . "</td>";
                                echo "<td>" . $persentase . "%</td>";
                                echo "</tr>";
                            } else {
                                echo "<tr><td colspan='8' class='text-center'>Tidak ada data absensi siswa</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Detail Absensi Per Pertemuan -->
            <div class="card shadow-sm">
                <div class="card-header">
                    <strong>Detail Absensi Per Pertemuan</strong>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-primary">
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Pertemuan</th>
                                <th>Mata Pelajaran</th>
                                <th>Guru</th>
                                <th>Status</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            if (mysqli_num_rows($result_pertemuan) > 0) {
                                while ($row = mysqli_fetch_assoc($result_pertemuan)) {
                                    echo "<tr>";
                                    echo "<td>" . $no++ . "</td>";
                                    echo "<td>" . date('d-m-Y', strtotime($row['tanggal'])) . "</td>";
                                    echo "<td>" . $row['pertemuan'] . "</td>";
                                    echo "<td>" . $row['nama_mapel'] . "</td>";
                                    echo "<td>" . $row['nama_guru'] . "</td>";
                                    echo "<td>" . $row['status'] . "</td>";
                                    echo "<td>" . $row['keterangan'] . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='7' class='text-center'>Tidak ada detail absensi</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <?php include '../includes/scripts.php'; ?>
</body>
</html>
