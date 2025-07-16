<?php
session_start();
require_once '../config/koneksi.php';
require_once '../includes/config.php';


// Tampilkan error untuk debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Ambil data user yang login
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Jika bukan admin, redirect ke dashboard
if ($role != 'admin') {
    header("Location: ../dashboard.php");
    exit();
}

// Ambil data user untuk navbar
$query_user = "SELECT * FROM users WHERE id = '$user_id'";
$result_user = mysqli_query($koneksi, $query_user);
$user = mysqli_fetch_assoc($result_user);

// Filter data
$kelas_id = isset($_GET['kelas_id']) ? $_GET['kelas_id'] : '';
$tanggal_awal = isset($_GET['tanggal_awal']) ? $_GET['tanggal_awal'] : date('Y-m-01');
$tanggal_akhir = isset($_GET['tanggal_akhir']) ? $_GET['tanggal_akhir'] : date('Y-m-t');
$mapel_id = isset($_GET['mapel_id']) ? $_GET['mapel_id'] : ''; // Tambahkan filter mata pelajaran

// Ambil data kelas untuk filter
$query_kelas = "SELECT * FROM kelas ORDER BY nama_kelas";
$result_kelas = mysqli_query($koneksi, $query_kelas);

// Ambil data mata pelajaran untuk filter
$query_mapel = "SELECT * FROM mapel ORDER BY nama_mapel";
$result_mapel = mysqli_query($koneksi, $query_mapel);

// Query untuk laporan absensi - PERBAIKAN: menggunakan 'jadwal' yang benar
$query_absensi = "SELECT a.*, ad.status, s.id as siswa_id, s.nama_siswa, s.nis, k.nama_kelas, m.id as mapel_id, m.nama_mapel, g.nama 
                FROM absensi a 
                JOIN absensi_detail ad ON a.id = ad.id_absensi
                JOIN siswa s ON ad.id_siswa = s.id 
                JOIN kelas k ON s.id_kelas = k.id 
                JOIN jadwal j ON a.id_jadwal = j.id
                JOIN mapel m ON j.mapel_id = m.id 
                JOIN guru g ON j.guru_id = g.id 
                WHERE a.tanggal BETWEEN '$tanggal_awal' AND '$tanggal_akhir'";

if (!empty($kelas_id)) {
    $query_absensi .= " AND s.id_kelas = '$kelas_id'";
}

// Tambahkan filter mata pelajaran pada query
if (!empty($mapel_id)) {
    $query_absensi .= " AND m.id = '$mapel_id'";
}

// Tambahkan filter semester pada query
$semester = isset($_GET['semester']) ? $_GET['semester'] : '';

// Tambahkan ke query
if (!empty($semester)) {
    $query_absensi .= " AND k.semester = '$semester'";
}

$query_absensi .= " ORDER BY a.tanggal DESC, k.nama_kelas, s.nama_siswa";
$result_absensi = mysqli_query($koneksi, $query_absensi);

// Cek error query
if (!$result_absensi) {
    $error = mysqli_error($koneksi);
    $error_message = "<div class='alert alert-danger'>Error query: $error</div>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Absensi - Sistem Absensi SMKN 4 Tasikmalaya</title>
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
    <!-- Include Sidebar -->
    <?php include '../includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content" id="content">
        <!-- Include Navbar -->
        <?php include '../includes/navbar.php'; ?>
        
        <div class="container-fluid">
            <div class="page-header">
                <h1 class="h3 mb-0 text-gray-800">Laporan Absensi</h1>
            </div>

            <?php if (isset($error_message)) echo $error_message; ?>

            <!-- Filter -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Filter Laporan</h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="kelas_id">Kelas</label>
                                <select name="kelas_id" id="kelas_id" class="form-select">
                                    <option value="">Semua Kelas</option>
                                    <?php while ($kelas = mysqli_fetch_assoc($result_kelas)): ?>
                                    <option value="<?php echo $kelas['id']; ?>" <?php echo ($kelas_id == $kelas['id']) ? 'selected' : ''; ?>>
                                        <?php echo $kelas['nama_kelas']; ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="tanggal_awal">Tanggal Awal</label>
                                <input type="date" name="tanggal_awal" id="tanggal_awal" class="form-control" value="<?php echo $tanggal_awal; ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="tanggal_akhir">Tanggal Akhir</label>
                                <input type="date" name="tanggal_akhir" id="tanggal_akhir" class="form-control" value="<?php echo $tanggal_akhir; ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="semester">Semester</label>
                                <select name="semester" id="semester" class="form-select">
                                    <option value="">Semua Semester</option>
                                    <option value="Ganjil" <?php echo (isset($_GET['semester']) && $_GET['semester'] == 'Ganjil') ? 'selected' : ''; ?>>Ganjil</option>
                                    <option value="Genap" <?php echo (isset($_GET['semester']) && $_GET['semester'] == 'Genap') ? 'selected' : ''; ?>>Genap</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="mapel_id">Mata Pelajaran</label>
                                <select name="mapel_id" id="mapel_id" class="form-select">
                                    <option value="">Semua Mata Pelajaran</option>
                                    <?php mysqli_data_seek($result_mapel, 0); while ($mapel = mysqli_fetch_assoc($result_mapel)): ?>
                                    <option value="<?php echo $mapel['id']; ?>" <?php echo (isset($_GET['mapel_id']) && $_GET['mapel_id'] == $mapel['id']) ? 'selected' : ''; ?>>
                                        <?php echo $mapel['nama_mapel']; ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-12 mb-3">
                                <button type="submit" class="btn btn-primary">Filter</button>
                                <a href="cetak_admin.php?kelas_id=<?php echo $kelas_id; ?>&tanggal_awal=<?php echo $tanggal_awal; ?>&tanggal_akhir=<?php echo $tanggal_akhir; ?>&semester=<?php echo $semester; ?>&mapel_id=<?php echo $mapel_id; ?>" class="btn btn-success" target="_blank">
                                    <i class="fas fa-print"></i> Cetak Laporan
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Laporan Absensi -->
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Laporan Absensi Siswa</h6>
                </div>
                <div class="card-body">
                    <?php
                    // Mengelompokkan data berdasarkan mata pelajaran
                    $grouped_data = [];
                    if ($result_absensi && mysqli_num_rows($result_absensi) > 0) {
                        mysqli_data_seek($result_absensi, 0);
                        while ($row = mysqli_fetch_assoc($result_absensi)) {
                            $grouped_data[$row['nama_mapel']][] = $row;
                        }
                    }
                    
                    // Tampilkan data yang dikelompokkan
                    if (!empty($grouped_data)) {
                        foreach ($grouped_data as $mapel => $rows) {
                            echo "<h5 class='mt-4 mb-3'>Mata Pelajaran: $mapel</h5>";
                            echo "<div class='table-responsive'>";
                            echo "<table class='table table-bordered' width='100%' cellspacing='0'>";
                            echo "<thead>";
                            echo "<tr>";
                            echo "<th>No</th>";
                            echo "<th>Tanggal</th>";
                            echo "<th>NIS</th>";
                            echo "<th>Nama Siswa</th>";
                            echo "<th>Status</th>";
                            echo "<th>Aksi</th>";
                            echo "</tr>";
                            echo "</thead>";
                            echo "<tbody>";
                            
                            $no = 1;
                            foreach ($rows as $row) {
                                echo "<tr>";
                                echo "<td>" . $no++ . "</td>";
                                echo "<td>" . htmlspecialchars($row['tanggal']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['nis']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['nama_siswa']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                                echo "<td><a href='detail_siswa.php?siswa_id={$row['siswa_id']}&mapel_id={$row['mapel_id']}' class='btn btn-info btn-sm'>Detail</a></td>";
                                echo "</tr>";
                            }
                            
                            echo "</tbody>";
                            echo "</table>";
                            echo "</div>";
                        }
                    } else {
                        echo "<div class='alert alert-info'>Tidak ada data absensi untuk ditampilkan pada rentang tanggal yang dipilih.</div>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Include Scripts -->
    <?php include '../includes/scripts.php'; ?>
</body>
</html>