<?php
session_start();
require_once '../config/koneksi.php';
require_once '../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SESSION['role'] != 'guru') {
    header("Location: ../dashboard.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$query_user = "SELECT * FROM users WHERE id = '$user_id'";
$result_user = mysqli_query($koneksi, $query_user);
$user = mysqli_fetch_assoc($result_user);

$nama_guru = $user['nama'];
$nip = $user['username'];

$query_guru = "SELECT id FROM guru WHERE nama = '$nama_guru' OR nip = '$nip'";
$result_guru = mysqli_query($koneksi, $query_guru);

if (!$result_guru || mysqli_num_rows($result_guru) == 0) {
    die("Data guru tidak ditemukan. Silakan hubungi administrator.");
}

$guru_data = mysqli_fetch_assoc($result_guru);
$guru_id = $guru_data['id'];

$filter_bulan = $_GET['bulan'] ?? date('m');
$filter_tahun = $_GET['tahun'] ?? date('Y');
$filter_kelas = $_GET['kelas'] ?? '';
$filter_mapel = $_GET['mapel'] ?? '';
$filter_semester = $_GET['semester'] ?? '';

$query_kelas = "SELECT DISTINCT k.id, k.nama_kelas 
               FROM jadwal j 
               JOIN kelas k ON j.kelas_id = k.id 
               WHERE j.guru_id = '$guru_id' 
               ORDER BY k.nama_kelas ASC";
$result_kelas = mysqli_query($koneksi, $query_kelas);

$query_mapel = "SELECT DISTINCT mp.id, mp.nama_mapel 
               FROM jadwal j 
               JOIN mapel mp ON j.mapel_id = mp.id 
               WHERE j.guru_id = '$guru_id' 
               ORDER BY mp.nama_mapel ASC";
$result_mapel = mysqli_query($koneksi, $query_mapel);

// Query laporan
$query = "SELECT s.id as siswa_id, s.nis, s.nama_siswa, k.nama_kelas, mp.id as mapel_id, mp.nama_mapel, a.tanggal, a.pertemuan, ad.status
          FROM siswa s
          JOIN kelas k ON s.id_kelas = k.id
          LEFT JOIN absensi_detail ad ON s.id = ad.id_siswa
          LEFT JOIN absensi a ON ad.id_absensi = a.id
          LEFT JOIN jadwal j ON a.id_jadwal = j.id
          LEFT JOIN mapel mp ON j.mapel_id = mp.id
          WHERE j.guru_id = '$guru_id'";

if (!empty($filter_bulan) && !empty($filter_tahun)) {
    $query .= " AND MONTH(a.tanggal) = '$filter_bulan' AND YEAR(a.tanggal) = '$filter_tahun'";
}

if (!empty($filter_kelas)) {
    $query .= " AND k.id = '$filter_kelas'";
}

if (!empty($filter_mapel)) {
    $query .= " AND mp.id = '$filter_mapel'";
}

$query .= " ORDER BY k.nama_kelas, s.nama_siswa, a.tanggal, a.pertemuan ASC";
$result = mysqli_query($koneksi, $query);

$laporan_per_kelas = [];
while ($row = mysqli_fetch_assoc($result)) {
    $laporan_per_kelas[$row['nama_kelas']][] = $row;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Absensi - Sistem Absensi SMKN 4 Tasikmalaya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700&display=swap" rel="stylesheet">
    <?php include '../includes/styles.php'; ?>
</head>
<body>
<?php include '../includes/sidebar.php'; ?>

<div class="main-content" id="content">
    <?php include '../includes/navbar.php'; ?>

    <div class="container-fluid">
        <div class="page-header">
            <h1 class="h3 mb-0 text-gray-800">Laporan Absensi</h1>
        </div>

        <!-- Filter Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Filter Laporan</h6>
            </div>
            <div class="card-body">
                <form method="GET">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="bulan">Bulan</label>
                            <select name="bulan" id="bulan" class="form-select">
                                <?php
                                $bulan_arr = [
                                    '01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni',
                                    '07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'
                                ];
                                foreach ($bulan_arr as $key => $val) {
                                    $selected = ($filter_bulan == $key) ? 'selected' : '';
                                    echo "<option value='$key' $selected>$val</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="tahun">Tahun</label>
                            <select name="tahun" id="tahun" class="form-select">
                                <?php
                                $tahun_sekarang = date('Y');
                                for ($i = $tahun_sekarang - 5; $i <= $tahun_sekarang + 5; $i++) {
                                    $selected = ($filter_tahun == $i) ? 'selected' : '';
                                    echo "<option value='$i' $selected>$i</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="kelas">Kelas</label>
                            <select name="kelas" id="kelas" class="form-select">
                                <option value="">Semua Kelas</option>
                                <?php while ($kelas = mysqli_fetch_assoc($result_kelas)): ?>
                                    <option value="<?= $kelas['id']; ?>" <?= ($filter_kelas == $kelas['id']) ? 'selected' : ''; ?>>
                                        <?= $kelas['nama_kelas']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="mapel">Mata Pelajaran</label>
                            <select name="mapel" id="mapel" class="form-select">
                                <option value="">Semua Mapel</option>
                                <?php mysqli_data_seek($result_mapel, 0); while ($mapel = mysqli_fetch_assoc($result_mapel)): ?>
                                    <option value="<?= $mapel['id']; ?>" <?= ($filter_mapel == $mapel['id']) ? 'selected' : ''; ?>>
                                        <?= $mapel['nama_mapel']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="cetak.php?bulan=<?= $filter_bulan; ?>&tahun=<?= $filter_tahun; ?>&kelas=<?= $filter_kelas; ?>&mapel=<?= $filter_mapel; ?>" class="btn btn-success" target="_blank">
                                <i class="fas fa-print"></i> Cetak Laporan
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        

        <!-- Tabel Laporan -->
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Laporan Absensi Siswa</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                        <tr>
                            <th>No</th>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th>Mata Pelajaran</th>
                            <th>Tanggal</th>
                            <th>Pertemuan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($laporan_per_kelas)): ?>
                            <tr>
                                <td colspan="8" class="text-center">Tidak ada data untuk ditampilkan.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($laporan_per_kelas as $nama_kelas => $laporan_siswa): ?>
                                <tr class="table-primary">
                                    <td colspan="8"><strong>Kelas: <?= htmlspecialchars($nama_kelas); ?></strong></td>
                                </tr>
                                <?php 
                                $no = 1;
                                foreach ($laporan_siswa as $row): ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($row['nis']); ?></td>
                                        <td><?= htmlspecialchars($row['nama_siswa']); ?></td>
                                        <td><?= htmlspecialchars($row['nama_mapel']); ?></td>
                                        <td><?= htmlspecialchars(date('d-m-Y', strtotime($row['tanggal']))); ?></td>
                                        <td><?= htmlspecialchars($row['pertemuan']); ?></td>
                                        <td><?= htmlspecialchars($row['status']); ?></td>
                                        <td>
                                            <a href="detail_siswa.php?siswa_id=<?= $row['siswa_id']; ?>&mapel_id=<?= $row['mapel_id']; ?>" class="btn btn-info btn-sm">Detail</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/scripts.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        $('#sidebarToggle').on('click', function () {
            $('.sidebar').toggleClass('active');
            $('.content').toggleClass('active');
        });
    });
</script>
</body>
</html>
