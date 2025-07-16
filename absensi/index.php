<?php
session_start();
require_once '../config/koneksi.php';
require_once '../includes/config.php'; // Tambahkan ini untuk base_url dan fungsi isActive

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Cek role (hanya guru yang boleh mengakses halaman ini)
if ($_SESSION['role'] != 'guru') {
    header("Location: ../dashboard.php");
    exit();
}

// Ambil data user yang login
$user_id = $_SESSION['user_id'];
$query_user = "SELECT * FROM users WHERE id = '$user_id'";
$result_user = mysqli_query($koneksi, $query_user);
$user = mysqli_fetch_assoc($result_user);

// Ambil data guru berdasarkan nama (bukan username/NIP)
$nama_guru = $user['nama'];
$query_guru = "SELECT * FROM guru WHERE nama = '$nama_guru'";
$result_guru = mysqli_query($koneksi, $query_guru);

// Jika tidak ditemukan dengan nama, coba dengan username sebagai NIP
$nip = $_SESSION['username'];
if (!$result_guru || mysqli_num_rows($result_guru) == 0) {
    $query_guru = "SELECT * FROM guru WHERE nip = '$nip'";
    $result_guru = mysqli_query($koneksi, $query_guru);
}

// Periksa apakah data guru ditemukan
if (!$result_guru || mysqli_num_rows($result_guru) == 0) {
    die("Data guru tidak ditemukan. Silakan hubungi administrator. Username: $nip");
}

$guru = mysqli_fetch_assoc($result_guru);
$id_guru = $guru['id'];

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page > 1) ? ($page * $limit) - $limit : 0;

// Filter
$filter_bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$filter_tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');
$filter_kelas = isset($_GET['kelas']) ? $_GET['kelas'] : '';

// Query untuk menghitung total data
$query_count = "SELECT COUNT(*) as total FROM absensi a 
               JOIN jadwal j ON a.id_jadwal = j.id 
               WHERE j.guru_id = '$id_guru'";

// Tambahkan filter jika ada
if (!empty($filter_bulan) && !empty($filter_tahun)) {
    $query_count .= " AND MONTH(a.tanggal) = '$filter_bulan' AND YEAR(a.tanggal) = '$filter_tahun'";
}

if (!empty($filter_kelas)) {
    $query_count .= " AND j.kelas_id = '$filter_kelas'";
}

$result_count = mysqli_query($koneksi, $query_count);
$row_count = mysqli_fetch_assoc($result_count);
$total_data = $row_count['total'];
$total_page = ceil($total_data / $limit);

// Query untuk mengambil data absensi
$query = "SELECT a.*, mp.nama_mapel, k.nama_kelas, 
          COUNT(CASE WHEN ad.status = 'Hadir' THEN 1 END) as jml_hadir,
          COUNT(CASE WHEN ad.status = 'Izin' THEN 1 END) as jml_izin,
          COUNT(CASE WHEN ad.status = 'Sakit' THEN 1 END) as jml_sakit,
          COUNT(CASE WHEN ad.status = 'Alpha' THEN 1 END) as jml_alpha
          FROM absensi a
          JOIN absensi_detail ad ON a.id = ad.id_absensi
          JOIN jadwal j ON a.id_jadwal = j.id
          JOIN mapel mp ON j.mapel_id = mp.id
          JOIN kelas k ON j.kelas_id = k.id
          WHERE j.guru_id = '$id_guru'";

// Tambahkan filter jika ada
if (!empty($filter_bulan) && !empty($filter_tahun)) {
    $query .= " AND MONTH(a.tanggal) = '$filter_bulan' AND YEAR(a.tanggal) = '$filter_tahun'";
}

if (!empty($filter_kelas)) {
    $query .= " AND j.kelas_id = '$filter_kelas'";
}

$query .= " GROUP BY a.id ORDER BY a.tanggal DESC, a.created_at DESC LIMIT $start, $limit";
$result = mysqli_query($koneksi, $query);

// Ambil daftar kelas yang diajar oleh guru
$query_kelas = "SELECT DISTINCT k.id, k.nama_kelas 
               FROM jadwal j 
               JOIN kelas k ON j.kelas_id = k.id 
               WHERE j.guru_id = '$id_guru' 
               ORDER BY k.nama_kelas ASC";
$result_kelas = mysqli_query($koneksi, $query_kelas);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Absensi - Sistem Absensi SMKN 4 Tasikmalaya</title>
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
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">Data Absensi Siswa</h1>
                <div>
                    <a href="../jadwal/index.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-calendar-alt"></i> Jadwal Mengajar
                    </a>
                </div>
            </div>

            <!-- Filter -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Filter Data</h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="" class="row g-3">
                        <div class="col-md-4">
                            <label for="bulan" class="form-label">Bulan</label>
                            <select class="form-select" id="bulan" name="bulan">
                                <?php
                                $bulan_array = [
                                    '01' => 'Januari',
                                    '02' => 'Februari',
                                    '03' => 'Maret',
                                    '04' => 'April',
                                    '05' => 'Mei',
                                    '06' => 'Juni',
                                    '07' => 'Juli',
                                    '08' => 'Agustus',
                                    '09' => 'September',
                                    '10' => 'Oktober',
                                    '11' => 'November',
                                    '12' => 'Desember'
                                ];
                                foreach ($bulan_array as $key => $value) {
                                    $selected = ($key == $filter_bulan) ? 'selected' : '';
                                    echo "<option value='$key' $selected>$value</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="tahun" class="form-label">Tahun</label>
                            <select class="form-select" id="tahun" name="tahun">
                                <?php
                                $tahun_sekarang = date('Y');
                                for ($i = $tahun_sekarang - 2; $i <= $tahun_sekarang + 1; $i++) {
                                    $selected = ($i == $filter_tahun) ? 'selected' : '';
                                    echo "<option value='$i' $selected>$i</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="kelas" class="form-label">Kelas</label>
                            <select class="form-select" id="kelas" name="kelas">
                                <option value="">Semua Kelas</option>
                                <?php
                                while ($kelas = mysqli_fetch_assoc($result_kelas)) {
                                    $selected = ($kelas['id'] == $filter_kelas) ? 'selected' : '';
                                    echo "<option value='{$kelas['id']}' $selected>{$kelas['nama_kelas']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-12 mt-3">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
                            <a href="index.php" class="btn btn-secondary"><i class="fas fa-sync-alt"></i> Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Data Absensi -->
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Data Absensi</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>No</th>
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
                                $no = $start + 1;
                                if (mysqli_num_rows($result) > 0) {
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        $tanggal = date('d-m-Y', strtotime($row['tanggal']));
                                        echo "<tr>";
                                        echo "<td>$no</td>";
                                        echo "<td>$tanggal</td>";
                                        echo "<td>{$row['nama_mapel']}</td>";
                                        echo "<td>{$row['nama_kelas']}</td>";
                                        echo "<td>{$row['pertemuan']}</td>";
                                        echo "<td>{$row['jml_hadir']}</td>";
                                        echo "<td>{$row['jml_izin']}</td>";
                                        echo "<td>{$row['jml_sakit']}</td>";
                                        echo "<td>{$row['jml_alpha']}</td>";
                                        echo "<td>";
                                        echo "<a href='detail.php?id={$row['id']}' class='btn btn-info btn-sm btn-action' title='Detail'><i class='fas fa-eye'></i></a>";
                                        echo "<a href='cetak.php?id={$row['id']}' class='btn btn-primary btn-sm btn-action' title='Cetak' target='_blank'><i class='fas fa-print'></i></a>";
                                        echo "</td>";
                                        echo "</tr>";
                                        $no++;
                                    }
                                } else {
                                    echo "<tr><td colspan='10' class='text-center'>Tidak ada data absensi</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_data > 0): ?>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            <p class="mb-0">Menampilkan <?= $start + 1 ?> sampai <?= min($start + mysqli_num_rows($result), $total_data) ?> dari <?= $total_data ?> data</p>
                        </div>
                        <nav aria-label="Page navigation">
                            <ul class="pagination">
                                <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=1&bulan=<?= $filter_bulan ?>&tahun=<?= $filter_tahun ?>&kelas=<?= $filter_kelas ?>" aria-label="First">
                                        <span aria-hidden="true">&laquo;&laquo;</span>
                                    </a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $page - 1 ?>&bulan=<?= $filter_bulan ?>&tahun=<?= $filter_tahun ?>&kelas=<?= $filter_kelas ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                                <?php endif; ?>
                                
                                <?php
                                $start_page = max(1, $page - 2);
                                $end_page = min($total_page, $page + 2);
                                
                                for ($i = $start_page; $i <= $end_page; $i++) {
                                    $active = ($i == $page) ? 'active' : '';
                                    echo "<li class='page-item $active'><a class='page-link' href='?page=$i&bulan=$filter_bulan&tahun=$filter_tahun&kelas=$filter_kelas'>$i</a></li>";
                                }
                                ?>
                                
                                <?php if ($page < $total_page): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $page + 1 ?>&bulan=<?= $filter_bulan ?>&tahun=<?= $filter_tahun ?>&kelas=<?= $filter_kelas ?>" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $total_page ?>&bulan=<?= $filter_bulan ?>&tahun=<?= $filter_tahun ?>&kelas=<?= $filter_kelas ?>" aria-label="Last">
                                        <span aria-hidden="true">&raquo;&raquo;</span>
                                    </a>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Custom JS -->
    <script>
        $(document).ready(function() {
            // Toggle sidebar on mobile
            $('#sidebarToggle').on('click', function() {
                $('.sidebar').toggleClass('active');
                $('.main-content').toggleClass('active');
            });
        });
    </script>
</body>
</html>
