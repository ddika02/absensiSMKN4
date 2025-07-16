<?php
session_start();
require_once '../config/koneksi.php';
require_once '../includes/config.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$role     = $_SESSION['role'];
$username = $_SESSION['username'];

if ($role !== 'guru' && $role !== 'admin') {
    header("Location: ../dashboard.php");
    exit();
}

// Ambil parameter
$siswa_id = $_GET['siswa_id'] ?? null;
$mapel_id = $_GET['mapel_id'] ?? '';
$bulan    = $_GET['bulan'] ?? date('m');
$tahun    = $_GET['tahun'] ?? date('Y');
$semester = $_GET['semester'] ?? '';
$semester = in_array($semester, ['Ganjil', 'Genap']) ? $semester : '';

if (!$siswa_id) {
    header("Location: " . ($role === 'admin' ? 'admin.php' : 'index.php'));
    exit();
}

// Ambil data siswa
$q_siswa = mysqli_query($koneksi, "
    SELECT s.*, k.nama_kelas, k.tahun_ajaran, k.semester 
    FROM siswa s 
    JOIN kelas k ON s.id_kelas = k.id 
    WHERE s.id = '$siswa_id'
");
if (mysqli_num_rows($q_siswa) === 0) {
    header("Location: " . ($role === 'admin' ? 'admin.php' : 'index.php'));
    exit();
}
$siswa = mysqli_fetch_assoc($q_siswa);

// === Jika login sebagai guru ===
$guru_id = null;
$blokir_data = false;
if ($role === 'guru') {
    $q_guru = mysqli_query($koneksi, "SELECT id FROM guru WHERE nip = '$username'");
    if ($row = mysqli_fetch_assoc($q_guru)) {
        $guru_id = $row['id'];

        // Cek apakah guru ini mengajar mapel yang dipilih
        if (empty($mapel_id)) {
            $blokir_data = true; // mapel belum dipilih
        } else {
            $cek_mapel = mysqli_query($koneksi, "
                SELECT 1 FROM jadwal 
                WHERE guru_id = '$guru_id' AND mapel_id = '$mapel_id' LIMIT 1
            ");
            if (mysqli_num_rows($cek_mapel) === 0) {
                die('<div class="alert alert-danger text-center mt-5">Akses ditolak. Anda tidak mengajar mata pelajaran ini.</div>');
            }
        }
    }
}

// Ambil nama mapel (jika ada)
$mapel_nama = '';
if (!empty($mapel_id)) {
    $q_mapel = mysqli_query($koneksi, "SELECT nama_mapel FROM mapel WHERE id = '$mapel_id'");
    if ($r = mysqli_fetch_assoc($q_mapel)) {
        $mapel_nama = $r['nama_mapel'];
    }
}

// === REKAP ABSENSI ===
$query_rekap = "
    SELECT 
        s.nis,
        s.nama_siswa,
        COUNT(ad.id) AS total_pertemuan,
        SUM(ad.status = 'Hadir') AS hadir,
        SUM(ad.status = 'Izin') AS izin,
        SUM(ad.status = 'Sakit') AS sakit,
        SUM(ad.status = 'Alpha') AS alpha
    FROM absensi_detail ad
    JOIN absensi a ON ad.id_absensi = a.id
    JOIN jadwal j ON a.id_jadwal = j.id
    JOIN siswa s ON ad.id_siswa = s.id
    JOIN kelas k ON s.id_kelas = k.id
    WHERE ad.id_siswa = '$siswa_id'
";

if (!empty($bulan) && !empty($tahun)) {
    $query_rekap .= " AND MONTH(a.tanggal) = '$bulan' AND YEAR(a.tanggal) = '$tahun'";
}
if (!empty($semester)) {
    $query_rekap .= " AND k.semester = '$semester'";
}
if (!empty($mapel_id)) {
    $query_rekap .= " AND j.mapel_id = '$mapel_id'";
}
if ($role === 'guru' && $guru_id) {
    $query_rekap .= " AND j.guru_id = '$guru_id'";
}
$query_rekap .= " GROUP BY s.id";
$result_detail = !$blokir_data ? mysqli_query($koneksi, $query_rekap) : null;

// === DETAIL PER PERTEMUAN ===
$query_pertemuan = "
    SELECT 
        a.tanggal,
        a.pertemuan,
        a.materi,
        ad.status,
        ad.keterangan,
        m.nama_mapel,
        g.nama AS nama_guru
    FROM absensi_detail ad
    JOIN absensi a ON ad.id_absensi = a.id
    JOIN jadwal j ON a.id_jadwal = j.id
    JOIN mapel m ON j.mapel_id = m.id
    JOIN guru g ON j.guru_id = g.id
    JOIN siswa s ON ad.id_siswa = s.id
    JOIN kelas k ON s.id_kelas = k.id
    WHERE ad.id_siswa = '$siswa_id'
";

if (!empty($bulan) && !empty($tahun)) {
    $query_pertemuan .= " AND MONTH(a.tanggal) = '$bulan' AND YEAR(a.tanggal) = '$tahun'";
}
if (!empty($semester)) {
    $query_pertemuan .= " AND k.semester = '$semester'";
}
if (!empty($mapel_id)) {
    $query_pertemuan .= " AND j.mapel_id = '$mapel_id'";
}
if ($role === 'guru' && $guru_id) {
    $query_pertemuan .= " AND j.guru_id = '$guru_id'";
}
$query_pertemuan .= " ORDER BY a.tanggal DESC";
$result_pertemuan = !$blokir_data ? mysqli_query($koneksi, $query_pertemuan) : null;
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
<?php include '../includes/sidebar.php'; ?>
<div class="main-content" id="content">
    <?php include '../includes/navbar.php'; ?>

    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-0">Detail Absensi Siswa</h4>

                <?php if ($role === 'guru' && $guru_id): ?>
                    <form method="GET" class="mt-2">
                        <input type="hidden" name="siswa_id" value="<?= $siswa_id ?>">
                        <input type="hidden" name="bulan" value="<?= $bulan ?>">
                        <input type="hidden" name="tahun" value="<?= $tahun ?>">
                        <input type="hidden" name="semester" value="<?= $semester ?>">

                        <select name="mapel_id" class="form-select mt-2" style="width: 250px;" onchange="this.form.submit()">
                            <option value="">-- Pilih Mata Pelajaran --</option>
                            <?php
                            $q_mapel_guru = mysqli_query($koneksi, "
                                SELECT DISTINCT m.id, m.nama_mapel
                                FROM jadwal j
                                JOIN mapel m ON j.mapel_id = m.id
                                WHERE j.guru_id = '$guru_id'
                            ");
                            while ($m = mysqli_fetch_assoc($q_mapel_guru)) {
                                $selected = ($mapel_id == $m['id']) ? 'selected' : '';
                                echo "<option value='{$m['id']}' $selected>{$m['nama_mapel']}</option>";
                            }
                            ?>
                        </select>
                    </form>
                <?php endif; ?>
            </div>

            <a href="<?= $role === 'admin' ? 'admin.php' : 'index.php'; ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>

        <?php if ($mapel_nama): ?>
            <div class="alert alert-info">Mata Pelajaran: <strong><?= $mapel_nama ?></strong></div>
        <?php endif; ?>

        <?php if ($role === 'guru' && $guru_id): ?>
            <div class="alert alert-info">Anda login sebagai guru: <strong><?= $username ?></strong></div>
        <?php endif; ?>

        <!-- Informasi Siswa -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white"><strong>Informasi Siswa</strong></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr><th>NIS</th><td>: <?= $siswa['nis']; ?></td></tr>
                            <tr><th>Nama Siswa</th><td>: <?= $siswa['nama_siswa']; ?></td></tr>
                            <tr><th>Kelas</th><td>: <?= $siswa['nama_kelas']; ?></td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr><th>Tahun Ajaran</th><td>: <?= $siswa['tahun_ajaran']; ?></td></tr>
                            <tr><th>Semester</th><td>: <?= $siswa['semester']; ?></td></tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rekap Absensi -->
        <div class="card shadow-sm mb-4">
            <div class="card-header"><strong>Rekap Absensi</strong></div>
            <div class="card-body table-responsive">
                <?php if ($blokir_data): ?>
                    <div class="alert alert-warning text-center">Silakan pilih mata pelajaran terlebih dahulu.</div>
                <?php endif; ?>
                <table class="table table-bordered table-striped">
                    <thead class="table-primary">
                        <tr>
                            <th>NIS</th>
                            <th>Nama</th>
                            <th>Pertemuan</th>
                            <th>Hadir</th>
                            <th>Izin</th>
                            <th>Sakit</th>
                            <th>Alpha</th>
                            <th>Persentase</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$blokir_data && $result_detail && mysqli_num_rows($result_detail) > 0):
                            $row = mysqli_fetch_assoc($result_detail);
                            $total = $row['total_pertemuan'];
                            $persen = $total > 0 ? round(($row['hadir'] / $total) * 100, 2) : 0;
                        ?>
                            <tr>
                                <td><?= $row['nis']; ?></td>
                                <td><?= $row['nama_siswa']; ?></td>
                                <td><?= $total; ?></td>
                                <td><?= $row['hadir']; ?></td>
                                <td><?= $row['izin']; ?></td>
                                <td><?= $row['sakit']; ?></td>
                                <td><?= $row['alpha']; ?></td>
                                <td><?= $persen; ?>%</td>
                            </tr>
                        <?php else: ?>
                            <tr><td colspan="8" class="text-center">Tidak ada data</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Detail Absensi Per Pertemuan -->
        <div class="card shadow-sm">
            <div class="card-header"><strong>Detail Absensi Per Pertemuan</strong></div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-primary">
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Pertemuan</th>
                            <th>Materi</th>
                            <th>Mapel</th>
                            <th>Guru</th>
                            <th>Status</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1;
                        if (!$blokir_data && $result_pertemuan && mysqli_num_rows($result_pertemuan) > 0):
                            while ($row = mysqli_fetch_assoc($result_pertemuan)): ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= date('d-m-Y', strtotime($row['tanggal'])); ?></td>
                                    <td><?= $row['pertemuan']; ?></td>
                                    <td><?= $row['materi'] ?: '-'; ?></td>
                                    <td><?= $row['nama_mapel']; ?></td>
                                    <td><?= $row['nama_guru']; ?></td>
                                    <td><?= $row['status']; ?></td>
                                    <td><?= $row['keterangan']; ?></td>
                                </tr>
                            <?php endwhile; else: ?>
                            <tr><td colspan="8" class="text-center">Tidak ada detail absensi</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
<?php include '../includes/scripts.php'; ?>
</body>
</html>
