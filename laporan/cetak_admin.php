<?php
session_start();
require_once '../config/koneksi.php';

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

// Filter data
$kelas_id = isset($_GET['kelas_id']) ? $_GET['kelas_id'] : '';
$tanggal_awal = isset($_GET['tanggal_awal']) ? $_GET['tanggal_awal'] : date('Y-m-01');
$tanggal_akhir = isset($_GET['tanggal_akhir']) ? $_GET['tanggal_akhir'] : date('Y-m-t');

// Ambil data kelas jika ada filter
$nama_kelas = "Semua Kelas";
if (!empty($kelas_id)) {
    $query_kelas_info = "SELECT nama_kelas FROM kelas WHERE id = '$kelas_id'";
    $result_kelas_info = mysqli_query($koneksi, $query_kelas_info);
    $kelas_info = mysqli_fetch_assoc($result_kelas_info);
    $nama_kelas = $kelas_info['nama_kelas'];
}

// Query untuk laporan absensi per siswa
// Modifikasi query untuk menambahkan kolom semester
// Query untuk laporan absensi per siswa dengan mata pelajaran dan guru terpisah
$query = "SELECT s.id as siswa_id, s.nis, s.nama_siswa, k.nama_kelas, k.tahun_ajaran, k.semester, 
          a.id as absensi_id, a.tanggal, a.pertemuan,
          mp.nama_mapel, g.nama as nama_guru,
          ad.status,
          COUNT(DISTINCT a.id) as total_pertemuan,
          SUM(CASE WHEN ad.status = 'Hadir' THEN 1 ELSE 0 END) as jml_hadir,
          SUM(CASE WHEN ad.status = 'Izin' THEN 1 ELSE 0 END) as jml_izin,
          SUM(CASE WHEN ad.status = 'Sakit' THEN 1 ELSE 0 END) as jml_sakit,
          SUM(CASE WHEN ad.status = 'Alpha' THEN 1 ELSE 0 END) as jml_alpha
          FROM siswa s
          JOIN kelas k ON s.id_kelas = k.id
          LEFT JOIN absensi_detail ad ON s.id = ad.id_siswa
          LEFT JOIN absensi a ON ad.id_absensi = a.id
          LEFT JOIN jadwal j ON a.id_jadwal = j.id
          LEFT JOIN mapel mp ON j.mapel_id = mp.id
          LEFT JOIN guru g ON j.guru_id = g.id";

// Tambahkan filter jika ada
if (!empty($tanggal_awal) && !empty($tanggal_akhir)) {
    $query .= " WHERE a.tanggal BETWEEN '$tanggal_awal' AND '$tanggal_akhir'";
} else {
    $query .= " WHERE 1=1";
}

if (!empty($kelas_id)) {
    $query .= " AND k.id = '$kelas_id'";
}

// Ubah GROUP BY menjadi hanya berdasarkan siswa dan mata pelajaran
$query .= " GROUP BY s.id, mp.id ORDER BY k.nama_kelas, s.nama_siswa, mp.nama_mapel ASC";
$result = mysqli_query($koneksi, $query);

// Mengelompokkan data laporan per siswa
$laporan_per_siswa = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $laporan_per_siswa[$row['nama_kelas']][$row['nama_mapel']][] = $row;
    }
}

// Query untuk statistik kehadiran per kelas
$query_statistik = "SELECT k.nama_kelas, mp.nama_mapel, k.tahun_ajaran, k.semester, 
                   COUNT(DISTINCT s.id) as total_siswa,
                   COUNT(DISTINCT a.id) as total_pertemuan,
                   SUM(CASE WHEN ad.status = 'Hadir' THEN 1 ELSE 0 END) as jml_hadir,
                   SUM(CASE WHEN ad.status = 'Izin' THEN 1 ELSE 0 END) as jml_izin,
                   SUM(CASE WHEN ad.status = 'Sakit' THEN 1 ELSE 0 END) as jml_sakit,
                   SUM(CASE WHEN ad.status = 'Alpha' THEN 1 ELSE 0 END) as jml_alpha
                   FROM kelas k
                   LEFT JOIN siswa s ON k.id = s.id_kelas
                   LEFT JOIN absensi_detail ad ON s.id = ad.id_siswa
                   LEFT JOIN absensi a ON ad.id_absensi = a.id
                   LEFT JOIN jadwal j ON a.id_jadwal = j.id
                   LEFT JOIN mapel mp ON j.mapel_id = mp.id";

if (!empty($tanggal_awal) && !empty($tanggal_akhir)) {
    $query_statistik .= " WHERE a.tanggal BETWEEN '$tanggal_awal' AND '$tanggal_akhir'";
} else {
    $query_statistik .= " WHERE 1=1";
}

if (!empty($kelas_id)) {
    $query_statistik .= " AND k.id = '$kelas_id'";
}

$query_statistik .= " GROUP BY k.id, mp.id ORDER BY k.nama_kelas, mp.nama_mapel ASC";
$result_statistik = mysqli_query($koneksi, $query_statistik);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan Absensi - Sistem Absensi SMKN 4 Tasikmalaya</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h2, .header h3 {
            margin: 5px 0;
        }
        .info {
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .footer {
            margin-top: 50px;
            text-align: right;
        }
        @media print {
            @page {
                size: landscape;
            }
            body {
                padding: 0;
                margin: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN ABSENSI SISWA</h2>
        <h3>SMK NEGERI 4 TASIKMALAYA</h3>
        <p>Periode: <?php echo date('d-m-Y', strtotime($tanggal_awal)); ?> s/d <?php echo date('d-m-Y', strtotime($tanggal_akhir)); ?></p>
    </div>

    <div class="no-print" style="margin-bottom: 15px; text-align: right;">
        <button onclick="window.print();" class="btn btn-primary">Cetak Laporan</button>
    </div>
    
    <div class="info">
        <p><strong>Kelas:</strong> <?php echo $nama_kelas; ?></p>
    </div>
    
    <!-- Statistik Kehadiran per Kelas -->
    <h4>Statistik Kehadiran per Kelas</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>Kelas</th>
                <th>Mata Pelajaran</th>
                <th>Tahun Ajaran</th>
                <th>Semester</th>
                <th>Jumlah Siswa</th>
                <th>Total Pertemuan</th>
                <th>Hadir</th>
                <th>Izin</th>
                <th>Sakit</th>
                <th>Alpha</th>
                <th>Persentase Kehadiran</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            if (mysqli_num_rows($result_statistik) > 0) {
                while ($row = mysqli_fetch_assoc($result_statistik)) {
                    $total_kehadiran = $row['jml_hadir'] + $row['jml_izin'] + $row['jml_sakit'] + $row['jml_alpha'];
                    // Perbaikan perhitungan persentase kehadiran
                    // Persentase = (jumlah hadir / total kehadiran) * 100
                    $persentase = ($total_kehadiran > 0) ? round(($row['jml_hadir'] / $total_kehadiran) * 100, 2) : 0;
                    
                    echo "<tr>";
                    echo "<td>" . $no++ . "</td>";
                    echo "<td>" . $row['nama_kelas'] . "</td>";
                    echo "<td>" . $row['nama_mapel'] . "</td>";
                    echo "<td>" . $row['tahun_ajaran'] . "</td>"; // Menampilkan tahun ajaran
                    echo "<td>" . $row['semester'] . "</td>";
                    echo "<td>" . $row['total_siswa'] . "</td>";
                    echo "<td>" . $row['total_pertemuan'] . "</td>";
                    echo "<td>" . $row['jml_hadir'] . "</td>";
                    echo "<td>" . $row['jml_izin'] . "</td>";
                    echo "<td>" . $row['jml_sakit'] . "</td>";
                    echo "<td>" . $row['jml_alpha'] . "</td>";
                    echo "<td>" . $persentase . "%</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='11' class='text-center'>Tidak ada data statistik</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <!-- Laporan Absensi per Siswa -->
    <h4>Laporan Absensi per Siswa</h4>
    <?php if (!empty($laporan_per_siswa)): ?>
        <?php foreach ($laporan_per_siswa as $kelas => $mapel_data): ?>
            <h5>Kelas: <?php echo $kelas; ?></h5>
            <?php foreach ($mapel_data as $mapel => $siswa_data): ?>
                <h6>Mata Pelajaran: <?php echo $mapel ?: 'Tidak Terjadwal'; ?></h6>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                        <th>No</th>
                        <th>NIS</th>
                        <th>Nama Siswa</th>
                        <th>Guru Pengajar</th>
                        <th>Total Pertemuan</th>
                        <th>Hadir</th>
                        <th>Izin</th>
                        <th>Sakit</th>
                        <th>Alpha</th>
                        <th>Persentase Kehadiran</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no_siswa = 1;
                    foreach ($siswa_data as $row) {
                        $total_kehadiran_siswa = $row['jml_hadir'] + $row['jml_izin'] + $row['jml_sakit'] + $row['jml_alpha'];
                        // Perbaikan perhitungan persentase kehadiran siswa
                        // Persentase = (jumlah hadir / total kehadiran) * 100
                        $persentase_siswa = ($total_kehadiran_siswa > 0) ? round(($row['jml_hadir'] / $total_kehadiran_siswa) * 100, 2) : 0;

                        echo "<tr>";
                        echo "<td>" . $no_siswa++ . "</td>";
                        echo "<td>" . $row['nis'] . "</td>";
                        echo "<td>" . $row['nama_siswa'] . "</td>";
                        echo "<td>" . $row['nama_guru'] . "</td>";
                        echo "<td>" . $row['total_pertemuan'] . "</td>";
                        echo "<td>" . $row['jml_hadir'] . "</td>";
                        echo "<td>" . $row['jml_izin'] . "</td>";
                        echo "<td>" . $row['jml_sakit'] . "</td>";
                        echo "<td>" . $row['jml_alpha'] . "</td>";
                        echo "<td>" . $persentase_siswa . "%</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        <?php endforeach; ?>
    <?php endforeach; ?>
    <?php else: ?>
        <p>Tidak ada data absensi untuk ditampilkan.</p>
    <?php endif; ?>

    <div class="footer">
        <p>Dicetak pada: <?php echo date('d-m-Y H:i:s'); ?></p>
    </div>
</body>
</html>