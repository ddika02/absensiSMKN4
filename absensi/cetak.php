<?php
session_start();
require_once '../config/koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Cek role (hanya guru dan admin yang boleh mengakses halaman ini)
if ($_SESSION['role'] != 'guru' && $_SESSION['role'] != 'admin') {
    header("Location: ../dashboard.php");
    exit();
}

$role = $_SESSION['role'];
$nip = $_SESSION['username'];

// Filter
$filter_bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$filter_tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');
$filter_kelas = isset($_GET['kelas']) ? $_GET['kelas'] : '';

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

$query .= " GROUP BY a.id ORDER BY a.tanggal DESC, a.created_at DESC";
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
    <title>Cetak Data Absensi - Sistem Absensi SMKN 4 Tasikmalaya</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #fff;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h2 {
            margin-bottom: 5px;
        }
        .header p {
            margin: 0;
            font-size: 14px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #000;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .no-print {
            margin-top: 20px;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                padding: 0;
                margin: 15mm 15mm 15mm 15mm;
            }
            @page {
                size: A4 landscape;
                margin: 10mm;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>DATA ABSENSI SISWA</h2>
            <p>SMKN 4 TASIKMALAYA</p>
            <p>Tahun Ajaran <?php echo date('Y'); ?>/<?php echo date('Y')+1; ?></p>
        </div>
        
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
                    $no = 1;
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
                            echo "<a href='detail.php?id={$row['id']}' class='btn btn-info btn-sm btn-action' title='Detail'><i class='fas fa-eye'></i></a> ";
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
        
        <div class="no-print text-center">
            <button class="btn btn-primary" onclick="window.print()"><i class="fas fa-print"></i> Cetak</button>
            <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
        </div>
    </div>
    
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</body>
</html>