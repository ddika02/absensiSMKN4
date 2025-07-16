<?php
session_start();
require_once 'config/koneksi.php';
require_once 'includes/config.php';


// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Ambil data user yang login
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$query = "SELECT * FROM users WHERE id = '$user_id'";
$result = mysqli_query($koneksi, $query);
$user = mysqli_fetch_assoc($result);

// Inisialisasi variabel hari
$hari_ini = date('l');
$hari_indo = [
    'Sunday' => 'Minggu',
    'Monday' => 'Senin',
    'Tuesday' => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday' => 'Kamis',
    'Friday' => 'Jumat',
    'Saturday' => 'Sabtu'
];
$hari = $hari_indo[$hari_ini];

// Inisialisasi variabel untuk semua role
$total_jadwal = 0;
$total_kelas_ajar = 0;
$total_mapel_ajar = 0;
$result_jadwal_hari_ini = null;
$guru_id = 0;
$total_siswa = 0;
$total_guru = 0;
$total_kelas = 0;
$total_mapel = 0;

// Inisialisasi variabel rekap absensi
$total_hadir = 0;
$total_izin = 0;
$total_sakit = 0;
$total_alpha = 0;

// Hitung jumlah data untuk dashboard admin
if ($role == 'admin') {
    $query_siswa = "SELECT COUNT(*) as total FROM siswa";
    $result_siswa = mysqli_query($koneksi, $query_siswa);
    $total_siswa = mysqli_fetch_assoc($result_siswa)['total'];
    
    $query_guru = "SELECT COUNT(*) as total FROM guru";
    $result_guru = mysqli_query($koneksi, $query_guru);
    $total_guru = mysqli_fetch_assoc($result_guru)['total'];
    
    $query_kelas = "SELECT COUNT(*) as total FROM kelas";
    $result_kelas = mysqli_query($koneksi, $query_kelas);
    $total_kelas = mysqli_fetch_assoc($result_kelas)['total'];
    
    $query_mapel = "SELECT COUNT(*) as total FROM mapel";
    $result_mapel = mysqli_query($koneksi, $query_mapel);
    $total_mapel = mysqli_fetch_assoc($result_mapel)['total'];
}
// Hitung jumlah data untuk dashboard guru
else if ($role == 'guru') {
    $query_guru = "SELECT id FROM guru WHERE user_id = '$user_id'";
    $result_guru = mysqli_query($koneksi, $query_guru);
    
    if ($result_guru && mysqli_num_rows($result_guru) > 0) {
        $guru_data = mysqli_fetch_assoc($result_guru);
        $guru_id = $guru_data['id'];

        // Dapatkan jadwal hari ini untuk guru tersebut
        $query_jadwal_hari_ini = "
        SELECT jadwal.*, mapel.nama_mapel, kelas.nama_kelas 
        FROM jadwal 
        JOIN mapel ON jadwal.mapel_id = mapel.id 
        JOIN kelas ON jadwal.kelas_id = kelas.id 
        WHERE jadwal.hari = '$hari' AND jadwal.guru_id = '$guru_id'
        ORDER BY jam_mulai ASC";
        $result_jadwal_hari_ini = mysqli_query($koneksi, $query_jadwal_hari_ini);
        
        // Gunakan guru_id untuk query jadwal
        $query_jadwal = "SELECT COUNT(*) as total FROM jadwal WHERE guru_id = '$guru_id'";
        $result_jadwal = mysqli_query($koneksi, $query_jadwal);
        $total_jadwal = mysqli_fetch_assoc($result_jadwal)['total'];
        
        $query_kelas_ajar = "SELECT COUNT(DISTINCT kelas_id) as total FROM jadwal WHERE guru_id = '$guru_id'";
        $result_kelas_ajar = mysqli_query($koneksi, $query_kelas_ajar);
        $total_kelas_ajar = mysqli_fetch_assoc($result_kelas_ajar)['total'];
        
        $query_mapel_ajar = "SELECT COUNT(DISTINCT mapel_id) as total FROM jadwal WHERE guru_id = '$guru_id'";
        $result_mapel_ajar = mysqli_query($koneksi, $query_mapel_ajar);
        $total_mapel_ajar = mysqli_fetch_assoc($result_mapel_ajar)['total'];
        
        // Ambil jadwal mengajar hari ini
        $query_jadwal_hari_ini = "SELECT j.*, mp.nama_mapel, k.nama_kelas 
                                  FROM jadwal j
                                  JOIN mapel mp ON j.mapel_id = mp.id
                                  JOIN kelas k ON j.kelas_id = k.id
                                  WHERE j.guru_id = '$guru_id' AND j.hari = '$hari'
                                  ORDER BY j.jam_mulai ASC";
        $result_jadwal_hari_ini = mysqli_query($koneksi, $query_jadwal_hari_ini);
    } else {
        // Handle jika guru tidak ditemukan
        $total_jadwal = 0;
        $total_kelas_ajar = 0;
        $total_mapel_ajar = 0;
        $result_jadwal_hari_ini = null;
        
        // Tambahkan debugging jika diperlukan
        // echo "<div class='alert alert-warning'>Data guru dengan NIP $nip tidak ditemukan.</div>";
    }
}
// Dapatkan awal dan akhir bulan ini
$tanggal_awal = date('Y-m-01');
$tanggal_akhir = date('Y-m-t');

$query_absensi_bulan_ini = "
SELECT status, COUNT(*) as jumlah 
FROM absensi_detail 
JOIN absensi ON absensi_detail.id_absensi = absensi.id 
WHERE absensi.tanggal BETWEEN '$tanggal_awal' AND '$tanggal_akhir'
GROUP BY status
";

$result_absensi = mysqli_query($koneksi, $query_absensi_bulan_ini);

// Set default 0
$total_hadir = $total_izin = $total_sakit = $total_alpha = 0;

while ($row = mysqli_fetch_assoc($result_absensi)) {
    switch ($row['status']) {
        case 'Hadir':
            $total_hadir = $row['jumlah'];
            break;
        case 'Izin':
            $total_izin = $row['jumlah'];
            break;
        case 'Sakit':
            $total_sakit = $row['jumlah'];
            break;
        case 'Alpha':
            $total_alpha = $row['jumlah'];
            break;
    }
}


// Statistik Kehadiran Bulanan
$tanggal_awal = date('Y-m-01');
$tanggal_akhir = date('Y-m-t');

if ($role == 'admin') {
    $query_absensi_bulan_ini = "
    SELECT status, COUNT(*) as jumlah 
    FROM absensi_detail 
    JOIN absensi ON absensi_detail.id_absensi = absensi.id 
    WHERE absensi.tanggal BETWEEN '$tanggal_awal' AND '$tanggal_akhir'
    GROUP BY status";
} else if ($role == 'guru') {
    $query_absensi_bulan_ini = "
    SELECT status, COUNT(*) as jumlah 
    FROM absensi_detail 
    JOIN absensi ON absensi_detail.id_absensi = absensi.id 
    JOIN jadwal ON absensi.id_jadwal = jadwal.id
    WHERE absensi.tanggal BETWEEN '$tanggal_awal' AND '$tanggal_akhir'
    AND jadwal.guru_id = '$guru_id'
    GROUP BY status";
}

$result_absensi = mysqli_query($koneksi, $query_absensi_bulan_ini);
$total_hadir = $total_izin = $total_sakit = $total_alpha = 0;
while ($row = mysqli_fetch_assoc($result_absensi)) {
    switch ($row['status']) {
        case 'Hadir': $total_hadir = $row['jumlah']; break;
        case 'Izin': $total_izin = $row['jumlah']; break;
        case 'Sakit': $total_sakit = $row['jumlah']; break;
        case 'Alpha': $total_alpha = $row['jumlah']; break;
    }
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistem Absensi SMKN 4 Tasikmalaya</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Custom CSS -->
    <?php include 'includes/styles.php'; ?>
</head>
<body>
    <!-- Sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content" id="content">
        <!-- Navbar -->
        <?php include 'includes/navbar.php'; ?>

        <!-- Page Content -->
        <div class="container-fluid">
            <!-- Page Heading -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
                <div>
                    <span class="text-muted me-2"><?php echo date('d F Y'); ?></span>
                </div>
            </div>

            <!-- Content Row -->
            <?php if ($role == 'admin'): ?>
            <div class="row">
                <!-- Siswa Card -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card card-stats card-siswa h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total Siswa</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_siswa; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-user-graduate fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-0">
                            <a href="<?php echo $base_url; ?>siswa/index.php" class="text-primary small stretched-link">Lihat Detail</a>
                        </div>
                    </div>
                </div>

                <!-- Guru Card -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card card-stats card-guru h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Total Guru</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_guru; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-chalkboard-teacher fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-0">
                            <a href="<?php echo $base_url; ?>guru/index.php" class="text-info small stretched-link">Lihat Detail</a>
                        </div>
                    </div>
                </div>

                <!-- Kelas Card -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card card-stats card-kelas h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Total Kelas</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_kelas; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-school fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-0">
                            <a href="<?php echo $base_url; ?>kelas/index.php" class="text-warning small stretched-link">Lihat Detail</a>
                        </div>
                    </div>
                </div>

                <!-- Mapel Card -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card card-stats card-mapel h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Mata Pelajaran</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_mapel; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-book fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-0">
                            <a href="<?php echo $base_url; ?>mapel/index.php" class="text-success small stretched-link">Lihat Detail</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Row -->
            <div class="row">
                <!-- aktivitas_absensi Terbaru -->
                <div class="col-lg-6 mb-4">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">aktivitas_absensi Terbaru</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Guru</th>
                                            <th>Kelas</th>
                                            <th>aktivitas_absensi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $query_aktivitas_absensi = "SELECT a.tanggal, g.nama, k.nama_kelas, a.keterangan 
                                                           FROM aktivitas_absensi a
                                                           JOIN guru g ON a.id_guru = g.id
                                                           JOIN kelas k ON a.id_kelas = k.id
                                                           ORDER BY a.tanggal DESC LIMIT 5";
                                        $result_aktivitas_absensi = mysqli_query($koneksi, $query_aktivitas_absensi);
                                        
                                        if (mysqli_num_rows($result_aktivitas_absensi) > 0) {
                                            while ($row = mysqli_fetch_assoc($result_aktivitas_absensi)) {
                                                echo "<tr>";
                                                echo "<td>" . date('d/m/Y', strtotime($row['tanggal'])) . "</td>";
                                                echo "<td>" . $row['nama'] . "</td>";
                                                echo "<td>" . $row['nama_kelas'] . "</td>";
                                                echo "<td>" . $row['keterangan'] . "</td>";
                                                echo "</tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='4' class='text-center'>Belum ada aktivitas_absensi</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistik Kehadiran -->
                <div class="col-lg-6 mb-4">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Statistik Kehadiran Bulan Ini</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-area">
                            <canvas id="kehadiranChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($role == 'guru'): ?>
            <div class="row">
                <!-- Jadwal Mengajar Card -->
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card card-stats card-siswa h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total Jadwal Mengajar</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_jadwal; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-0">
                            <a href="<?php echo $base_url; ?>jadwal/index.php" class="text-primary small stretched-link">Lihat Detail</a>
                        </div>
                    </div>
                </div>

                <!-- Kelas Ajar Card -->
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card card-stats card-guru h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Kelas yang Diajar</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_kelas_ajar; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-school fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-0">
                            <a href="<?php echo $base_url; ?>guru/kelas.php" class="text-info small stretched-link">Lihat Detail</a>
                        </div>
                    </div>
                </div>

                <!-- Mapel Ajar Card -->
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card card-stats card-kelas h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Mata Pelajaran yang Diajar</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_mapel_ajar; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-book fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-0">
                            <a href="<?php echo $base_url; ?>guru/mapel.php" class="text-warning small stretched-link">Lihat Detail</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Row -->
            <div class="row">
                <!-- Jadwal Hari Ini -->
                <div class="col-lg-6 mb-4">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Jadwal Mengajar Hari Ini (<?php echo $hari; ?>)</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Jam</th>
                                            <th>Mata Pelajaran</th>
                                            <th>Kelas</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if ($result_jadwal_hari_ini && mysqli_num_rows($result_jadwal_hari_ini) > 0) {
                                            while ($row = mysqli_fetch_assoc($result_jadwal_hari_ini)) {
                                                echo "<tr>";
                                                echo "<td>" . $row['jam_mulai'] . " - " . $row['jam_selesai'] . "</td>";
                                                echo "<td>" . $row['nama_mapel'] . "</td>";
                                                echo "<td>" . $row['nama_kelas'] . "</td>";
                                                echo "<td>
                                                        <a href='absensi/form.php?id_jadwal=" . $row['id'] . "' class='btn btn-sm btn-primary'>
                                                            <i class='fas fa-clipboard-list'></i> Absensi
                                                        </a>
                                                      </td>";
                                                echo "</tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='4' class='text-center'>Tidak ada jadwal mengajar hari ini</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rekap Absensi -->
                <div class="col-lg-6 mb-4">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Rekap Absensi Bulan Ini</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-area">
                            <canvas id="rekapAbsensiChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Laporan Absensi Terbaru -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Absensi Terbaru</h6>
                            <a href="laporan/index.php" class="btn btn-sm btn-primary">
                                <i class="fas fa-print"></i> Cetak Laporan
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Mata Pelajaran</th>
                                            <th>Kelas</th>
                                            <th>Pertemuan</th>
                                            <th>Hadir</th>
                                            <th>Izin</th>
                                            <th>Sakit</th>
                                            <th>Alpha</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Pastikan $guru_id sudah terdefinisi dan valid
                                        if ($role == 'guru' && isset($guru_id) && $guru_id > 0) {
                                            $query_absensi_terbaru = "SELECT a.*, mp.nama_mapel, k.nama_kelas,
                                                                COUNT(CASE WHEN ad.status = 'Hadir' THEN 1 END) as jml_hadir,
                                                                COUNT(CASE WHEN ad.status = 'Izin' THEN 1 END) as jml_izin,
                                                                COUNT(CASE WHEN ad.status = 'Sakit' THEN 1 END) as jml_sakit,
                                                                COUNT(CASE WHEN ad.status = 'Alpha' THEN 1 END) as jml_alpha
                                                                FROM absensi a
                                                                JOIN absensi_detail ad ON a.id = ad.id_absensi
                                                                JOIN jadwal j ON a.id_jadwal = j.id
                                                                JOIN mapel mp ON j.mapel_id = mp.id
                                                                JOIN kelas k ON j.kelas_id = k.id
                                                                JOIN guru g ON j.guru_id = g.id
                                                                WHERE j.guru_id = '$guru_id'
                                                                GROUP BY a.id
                                                                ORDER BY a.tanggal DESC, a.created_at DESC
                                                                LIMIT 5";
                                            $result_absensi_terbaru = mysqli_query($koneksi, $query_absensi_terbaru);
                                            
                                            // Tampilkan data absensi
                                            if ($result_absensi_terbaru && mysqli_num_rows($result_absensi_terbaru) > 0) {
                                                while ($row = mysqli_fetch_assoc($result_absensi_terbaru)) {
                                                    echo "<tr>";
                                                    echo "<td>" . date('d/m/Y', strtotime($row['tanggal'])) . "</td>";
                                                    echo "<td>" . $row['nama_mapel'] . "</td>";
                                                    echo "<td>" . $row['nama_kelas'] . "</td>";
                                                    echo "<td>" . $row['pertemuan'] . "</td>";
                                                    echo "<td>" . $row['jml_hadir'] . "</td>";
                                                    echo "<td>" . $row['jml_izin'] . "</td>";
                                                    echo "<td>" . $row['jml_sakit'] . "</td>";
                                                    echo "<td>" . $row['jml_alpha'] . "</td>";
                                                    echo "<td>
                                                            <a href='absensi/detail.php?id=" . $row['id'] . "' class='btn btn-sm btn-info'>
                                                                <i class='fas fa-eye'></i>
                                                            </a>
                                                            <a href='absensi/cetak.php?id=" . $row['id'] . "' class='btn btn-sm btn-primary'>
                                                                <i class='fas fa-print'></i>
                                                            </a>
                                                          </td>";
                                                    echo "</tr>";
                                                }
                                            } else {
                                                echo "<tr><td colspan='9' class='text-center'>Belum ada data absensi</td></tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='9' class='text-center'>Data guru tidak ditemukan atau belum ada data absensi</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Scripts -->
    <?php include 'includes/scripts.php'; ?>
    <script>
        <?php if ($role == 'admin'): ?>
        // Ambil data statistik absensi bulan ini untuk admin
        <?php
        $bulan_ini = date('m');
        $tahun_ini = date('Y');
        $query_statistik = "SELECT 
            SUM(CASE WHEN ad.status = 'Hadir' THEN 1 ELSE 0 END) as total_hadir,
            SUM(CASE WHEN ad.status = 'Izin' THEN 1 ELSE 0 END) as total_izin,
            SUM(CASE WHEN ad.status = 'Sakit' THEN 1 ELSE 0 END) as total_sakit,
            SUM(CASE WHEN ad.status = 'Alpha' THEN 1 ELSE 0 END) as total_alpha
            FROM absensi a
            JOIN absensi_detail ad ON a.id = ad.id_absensi
            WHERE MONTH(a.tanggal) = '$bulan_ini' AND YEAR(a.tanggal) = '$tahun_ini'"; 
        $result_statistik = mysqli_query($koneksi, $query_statistik);
        $statistik = mysqli_fetch_assoc($result_statistik);
        
        // Pastikan nilai tidak null
        $total_hadir_admin = $statistik['total_hadir'] ?? 0;
        $total_izin_admin = $statistik['total_izin'] ?? 0;
        $total_sakit_admin = $statistik['total_sakit'] ?? 0;
        $total_alpha_admin = $statistik['total_alpha'] ?? 0;
        ?>
        
        // Kehadiran Chart
        var ctx = document.getElementById('kehadiranChart').getContext('2d');
        var kehadiranChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Hadir', 'Izin', 'Sakit', 'Alpha'],
                datasets: [{
                    data: [<?php echo $total_hadir_admin; ?>, <?php echo $total_izin_admin; ?>, <?php echo $total_sakit_admin; ?>, <?php echo $total_alpha_admin; ?>],
                    backgroundColor: ['#4e73df', '#1cc88a', '#f6c23e', '#e74a3b'],
                    hoverBackgroundColor: ['#2e59d9', '#17a673', '#f4b619', '#d52a1a'],
                    hoverBorderColor: "rgba(234, 236, 244, 1)",
                }],
            },
            options: {
                maintainAspectRatio: false,
                tooltips: {
                    backgroundColor: "rgb(255,255,255)",
                    bodyFontColor: "#858796",
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    xPadding: 15,
                    yPadding: 15,
                    displayColors: false,
                    caretPadding: 10,
                },
                legend: {
                    display: false
                },
                cutoutPercentage: 80,
            },
        });
        <?php endif; ?>
        
        <?php if ($role == 'guru' && isset($guru_id) && $guru_id > 0): ?>
        // Ambil data rekap absensi untuk chart
        <?php
        $query_rekap = "SELECT 
            SUM(CASE WHEN ad.status = 'Hadir' THEN 1 ELSE 0 END) as total_hadir,
            SUM(CASE WHEN ad.status = 'Izin' THEN 1 ELSE 0 END) as total_izin,
            SUM(CASE WHEN ad.status = 'Sakit' THEN 1 ELSE 0 END) as total_sakit,
            SUM(CASE WHEN ad.status = 'Alpha' THEN 1 ELSE 0 END) as total_alpha
            FROM absensi a
            JOIN absensi_detail ad ON a.id = ad.id_absensi
            JOIN jadwal j ON a.id_jadwal = j.id
            WHERE j.guru_id = '$guru_id' 
            AND MONTH(a.tanggal) = MONTH(CURRENT_DATE()) 
            AND YEAR(a.tanggal) = YEAR(CURRENT_DATE())";
        $result_rekap = mysqli_query($koneksi, $query_rekap);
        $rekap_data = mysqli_fetch_assoc($result_rekap);
        
        // Pastikan nilai tidak null
        $total_hadir = $rekap_data['total_hadir'] ?? 0;
        $total_izin = $rekap_data['total_izin'] ?? 0;
        $total_sakit = $rekap_data['total_sakit'] ?? 0;
        $total_alpha = $rekap_data['total_alpha'] ?? 0;
        ?>
        
        // Rekap Absensi Chart
        var ctx = document.getElementById('rekapAbsensiChart').getContext('2d');
        var rekapAbsensiChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Hadir', 'Izin', 'Sakit', 'Alpha'],
                datasets: [{
                    data: [<?php echo $total_hadir; ?>, <?php echo $total_izin; ?>, <?php echo $total_sakit; ?>, <?php echo $total_alpha; ?>],
                    backgroundColor: ['#4e73df', '#1cc88a', '#f6c23e', '#e74a3b'],
                    hoverBackgroundColor: ['#2e59d9', '#17a673', '#f4b619', '#d52a1a'],
                    hoverBorderColor: "rgba(234, 236, 244, 1)",
                }],
            },
            options: {
                maintainAspectRatio: false,
                tooltips: {
                    backgroundColor: "rgb(255,255,255)",
                    bodyFontColor: "#858796",
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    xPadding: 15,
                    yPadding: 15,
                    displayColors: false,
                    caretPadding: 10,
                },
                legend: {
                    display: false
                },
                cutoutPercentage: 80,
            },
        });
        <?php else: ?>
        // Default chart jika data guru tidak ditemukan
        var ctx = document.getElementById('rekapAbsensiChart').getContext('2d');
        var rekapAbsensiChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Hadir', 'Izin', 'Sakit', 'Alpha'],
                datasets: [{
                    data: [0, 0, 0, 0],
                    backgroundColor: ['#4e73df', '#1cc88a', '#f6c23e', '#e74a3b'],
                    hoverBackgroundColor: ['#2e59d9', '#17a673', '#f4b619', '#d52a1a'],
                    hoverBorderColor: "rgba(234, 236, 244, 1)",
                }],
            },
            options: {
                maintainAspectRatio: false,
                tooltips: {
                    backgroundColor: "rgb(255,255,255)",
                    bodyFontColor: "#858796",
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    xPadding: 15,
                    yPadding: 15,
                    displayColors: false,
                    caretPadding: 10,
                },
                legend: {
                    display: false
                },
                cutoutPercentage: 80,
            },
        });
        <?php endif; ?>
    </script>
</body>
</html>


