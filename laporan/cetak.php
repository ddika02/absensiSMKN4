<?php
session_start();
require_once '../config/koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Cek role (hanya guru yang boleh mengakses halaman ini)
if ($_SESSION['role'] != 'guru' && $_SESSION['role'] != 'admin') {
    header("Location: ../dashboard.php");
    exit();
}

$role = $_SESSION['role'];
$nip = $_SESSION['username'];

// Jika ada parameter id, berarti cetak detail absensi
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id_absensi = $_GET['id'];
    
    // Query untuk mendapatkan data absensi
    $query_absensi = "SELECT a.*, g.nip, mp.nama_mapel, k.nama_kelas, g.nama as nama_guru 
                     FROM absensi a 
                     JOIN jadwal j ON a.id_jadwal = j.id 
                     JOIN mapel mp ON j.mapel_id = mp.id 
                     JOIN kelas k ON j.kelas_id = k.id 
                     JOIN guru g ON j.guru_id = g.id 
                     WHERE a.id = '$id_absensi'";
    
    // Jika role guru, tambahkan filter nip
    if ($role == 'guru') {
        $query_absensi .= " AND g.nip = '$nip'";
    }
    
    $result_absensi = mysqli_query($koneksi, $query_absensi);
    
    if (mysqli_num_rows($result_absensi) == 0) {
        header("Location: index.php");
        exit();
    }
    
    $absensi = mysqli_fetch_assoc($result_absensi);
    
    // Ambil detail absensi siswa
    $query_detail = "SELECT ad.*, s.nis, s.nama_siswa 
                    FROM absensi_detail ad 
                    JOIN siswa s ON ad.id_siswa = s.id 
                    WHERE ad.id_absensi = '$id_absensi' 
                    ORDER BY s.nama_siswa ASC";
    $result_detail = mysqli_query($koneksi, $query_detail);
    
    // Cetak detail absensi
    $title = "Detail Absensi Kelas {$absensi['nama_kelas']} - {$absensi['nama_mapel']}";
    $mode = "detail";
} else {
    // Cetak laporan absensi
    $filter_bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
    $filter_tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');
    $filter_kelas = isset($_GET['kelas']) ? $_GET['kelas'] : '';
    $filter_mapel = isset($_GET['mapel']) ? $_GET['mapel'] : '';
    
    // Ambil data guru berdasarkan username (NIP) atau nama
    $nip = $_SESSION['username'];
    $nama = $_SESSION['nama_lengkap'] ?? $_SESSION['nama'] ?? '';
    
    $query_guru = "SELECT id FROM guru WHERE nip = '$nip' OR nama = '$nama'";
    $result_guru = mysqli_query($koneksi, $query_guru);
    
    if ($role == 'guru' && (!$result_guru || mysqli_num_rows($result_guru) == 0)) {
        die("Data guru tidak ditemukan. Silakan hubungi administrator.");
    }
    
    if ($role == 'guru') {
        $guru_data = mysqli_fetch_assoc($result_guru);
        $guru_id = $guru_data['id'];
    }
    
    // Hapus baris berikut yang menyebabkan error
    // mysqli_query($koneksi, "SET SESSION group_concat_max_length = 1000");
    
    // Query untuk laporan absensi per siswa
    $query = "SELECT s.id as siswa_id, s.nis, s.nama_siswa, k.nama_kelas, 
              COUNT(DISTINCT a.id) as total_pertemuan,
              GROUP_CONCAT(DISTINCT mp.nama_mapel ORDER BY mp.nama_mapel SEPARATOR ', ') as mata_pelajaran,
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
    
    if ($role == 'guru') {
        $query .= " WHERE j.guru_id = '$guru_id'";
    } else {
        $query .= " WHERE 1=1";
    }
    
    // Tambahkan filter jika ada
    if (!empty($filter_bulan) && !empty($filter_tahun)) {
        $query .= " AND MONTH(a.tanggal) = '$filter_bulan' AND YEAR(a.tanggal) = '$filter_tahun'";
    }
    
    if (!empty($filter_kelas)) {
        $query .= " AND k.id = '$filter_kelas'";
    }
    
    if (!empty($filter_mapel)) {
        $query .= " AND mp.id = '$filter_mapel'";
    }
    
    // Tambahkan LIMIT pada query utama (misalnya batasi 100 siswa per halaman)
    $query .= " GROUP BY s.id, s.nis, s.nama_siswa, k.nama_kelas ORDER BY k.nama_kelas, s.nama_siswa ASC LIMIT 100";
    $result = mysqli_query($koneksi, $query);
    
    // Set judul laporan
    $bulan_nama = array(
        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
        '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
        '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
    );
    
    $title = "Laporan Absensi Siswa Bulan {$bulan_nama[$filter_bulan]} $filter_tahun";
    $mode = "laporan";
}

// Workaround for missing cURL extension
if (!function_exists('curl_init')) {
    if (!defined('CURLOPT_CONNECTTIMEOUT')) define('CURLOPT_CONNECTTIMEOUT', 78);
    if (!defined('CURLOPT_MAXREDIRS')) define('CURLOPT_MAXREDIRS', 68);
    if (!defined('CURLPROTO_HTTP')) define('CURLPROTO_HTTP', 1);
    if (!defined('CURLPROTO_HTTPS')) define('CURLPROTO_HTTPS', 2);
    if (!defined('CURLPROTO_FTP')) define('CURLPROTO_FTP', 4);
    if (!defined('CURLPROTO_FTPS')) define('CURLPROTO_FTPS', 8);
    if (!defined('CURLOPT_PROTOCOLS')) define('CURLOPT_PROTOCOLS', 181);
    if (!defined('CURLOPT_SSL_VERIFYHOST')) define('CURLOPT_SSL_VERIFYHOST', 81);
    if (!defined('CURLOPT_SSL_VERIFYPEER')) define('CURLOPT_SSL_VERIFYPEER', 64);
    if (!defined('CURLOPT_TIMEOUT')) define('CURLOPT_TIMEOUT', 13);
    if (!defined('CURLOPT_USERAGENT')) define('CURLOPT_USERAGENT', 10018);
    if (!defined('CURLOPT_FAILONERROR')) define('CURLOPT_FAILONERROR', 45); // Added this line
    if (!defined('CURLOPT_FOLLOWLOCATION')) define('CURLOPT_FOLLOWLOCATION', 52); // Added this line
    if (!defined('CURLOPT_RETURNTRANSFER')) define('CURLOPT_RETURNTRANSFER', 19913); // Added this line
}

// Tambahkan library TCPDF
require_once('../vendor/tecnickcom/tcpdf/tcpdf.php');

// Buat objek PDF
// Setelah membuat objek PDF, tambahkan pengaturan berikut
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

// Nonaktifkan cache gambar untuk mengurangi penggunaan memori
$pdf->setImageScale(1.53);
$pdf->setJPEGQuality(90);

// Nonaktifkan beberapa fitur yang tidak diperlukan
$pdf->SetAutoPageBreak(true, 15);
$pdf->SetCompression(true);

// Set informasi dokumen
$pdf->SetCreator('Sistem Absensi SMKN 4 Tasikmalaya');
$pdf->SetAuthor('SMKN 4 Tasikmalaya');
$pdf->SetTitle($title);

// Hapus header dan footer default
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Set margin
$pdf->SetMargins(15, 15, 15);

// Tambahkan halaman
$pdf->AddPage();

// Buat header laporan
$pdf->Image('../assets/img/logo-smkn4.jpg', 15, 10, 20, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'SISTEM INFORMASI ABSENSI', 0, false, 'C', 0, '', 0, false, 'M', 'M');
$pdf->Ln(7);
$pdf->Cell(0, 10, 'SMK NEGERI 4 TASIKMALAYA', 0, false, 'C', 0, '', 0, false, 'M', 'M');
$pdf->Ln(7);
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 10, 'Jl. Depok, Sukamenak, Kec. Purbaratu, Kab. Tasikmalaya, Jawa Barat 46196', 0, false, 'C', 0, '', 0, false, 'M', 'M');
$pdf->Ln(10);
$pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
$pdf->Ln(5);

// Judul laporan
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, $title, 0, false, 'C', 0, '', 0, false, 'M', 'M');
$pdf->Ln(15);

if ($mode == "detail") {
    // Informasi absensi
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(30, 7, 'Tanggal', 0, 0, 'L');
    $pdf->Cell(5, 7, ':', 0, 0, 'C');
    $pdf->Cell(60, 7, date('d-m-Y', strtotime($absensi['tanggal'])), 0, 0, 'L');
    $pdf->Cell(30, 7, 'Pertemuan', 0, 0, 'L');
    $pdf->Cell(5, 7, ':', 0, 0, 'C');
    $pdf->Cell(50, 7, $absensi['pertemuan'], 0, 1, 'L');
    
    $pdf->Cell(30, 7, 'Kelas', 0, 0, 'L');
    $pdf->Cell(5, 7, ':', 0, 0, 'C');
    $pdf->Cell(60, 7, $absensi['nama_kelas'], 0, 0, 'L');
    $pdf->Cell(30, 7, 'Guru', 0, 0, 'L');
    $pdf->Cell(5, 7, ':', 0, 0, 'C');
    $pdf->Cell(50, 7, $absensi['nama_guru'], 0, 1, 'L');
    
    $pdf->Cell(30, 7, 'Mata Pelajaran', 0, 0, 'L');
    $pdf->Cell(5, 7, ':', 0, 0, 'C');
    $pdf->Cell(60, 7, $absensi['nama_mapel'], 0, 0, 'L');
    $pdf->Cell(30, 7, 'Materi', 0, 0, 'L');
    $pdf->Cell(5, 7, ':', 0, 0, 'C');
    $pdf->Cell(50, 7, $absensi['materi'], 0, 1, 'L');
    
    if (!empty($absensi['catatan'])) {
        $pdf->Ln(5);
        $pdf->Cell(30, 7, 'Catatan', 0, 0, 'L');
        $pdf->Cell(5, 7, ':', 0, 0, 'C');
        $pdf->MultiCell(0, 7, $absensi['catatan'], 0, 'L', 0, 1, '', '', true);
    }
    
    $pdf->Ln(5);
    
    // Tabel detail absensi
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(10, 7, 'No', 1, 0, 'C');
    $pdf->Cell(30, 7, 'NIS', 1, 0, 'C');
    $pdf->Cell(70, 7, 'Nama Siswa', 1, 0, 'C');
    $pdf->Cell(30, 7, 'Status', 1, 0, 'C');
    $pdf->Cell(40, 7, 'Keterangan', 1, 1, 'C');
    
    $pdf->SetFont('helvetica', '', 10);
    $no = 1;
    if (mysqli_num_rows($result_detail) > 0) {
        while ($row = mysqli_fetch_assoc($result_detail)) {
            $pdf->Cell(10, 7, $no++, 1, 0, 'C');
            $pdf->Cell(30, 7, $row['nis'], 1, 0, 'C');
            $pdf->Cell(70, 7, $row['nama_siswa'], 1, 0, 'L');
            $pdf->Cell(30, 7, $row['status'], 1, 0, 'C');
            $pdf->Cell(40, 7, $row['keterangan'], 1, 1, 'L');
        }
    } else {
        $pdf->Cell(180, 7, 'Tidak ada data detail absensi', 1, 1, 'C');
    }
} else {
    // Tabel laporan absensi per siswa
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->Cell(10, 7, 'No', 1, 0, 'C');
    $pdf->Cell(25, 7, 'NIS', 1, 0, 'C');
    $pdf->Cell(50, 7, 'Nama Siswa', 1, 0, 'C');
    $pdf->Cell(25, 7, 'Kelas', 1, 0, 'C');
    $pdf->Cell(15, 7, 'Pertemuan', 1, 0, 'C');
    $pdf->Cell(15, 7, 'Hadir', 1, 0, 'C');
    $pdf->Cell(15, 7, 'Izin', 1, 0, 'C');
    $pdf->Cell(15, 7, 'Sakit', 1, 0, 'C');
    $pdf->Cell(15, 7, 'Alpha', 1, 1, 'C');
    
    $pdf->SetFont('helvetica', '', 9);
    $no = 1;
    $current_kelas = '';
    
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            // Jika kelas berubah, tampilkan mata pelajaran
            if ($current_kelas != $row['nama_kelas']) {
                $current_kelas = $row['nama_kelas'];
                $pdf->SetFont('helvetica', 'B', 9);
                $pdf->Cell(185, 7, 'Mata Pelajaran: ' . ($row['mata_pelajaran'] ?? '-'), 1, 1, 'L');
                $pdf->SetFont('helvetica', '', 9);
            }
            
            $pdf->Cell(10, 7, $no++, 1, 0, 'C');
            $pdf->Cell(25, 7, $row['nis'], 1, 0, 'C');
            $pdf->Cell(50, 7, $row['nama_siswa'], 1, 0, 'L');
            $pdf->Cell(25, 7, $row['nama_kelas'], 1, 0, 'C');
            $pdf->Cell(15, 7, $row['total_pertemuan'], 1, 0, 'C');
            $pdf->Cell(15, 7, $row['jml_hadir'], 1, 0, 'C');
            $pdf->Cell(15, 7, $row['jml_izin'], 1, 0, 'C');
            $pdf->Cell(15, 7, $row['jml_sakit'], 1, 0, 'C');
            $pdf->Cell(15, 7, $row['jml_alpha'], 1, 1, 'C');
        }
    } else {
        $pdf->Cell(185, 7, 'Tidak ada data absensi', 1, 1, 'C');
    }
}

// Tanda tangan
$pdf->Ln(15);
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(120, 7, '', 0, 0, 'L');
$pdf->Cell(60, 7, 'Tasikmalaya, ' . date('d F Y'), 0, 1, 'C');
$pdf->Cell(120, 7, '', 0, 0, 'L');
$pdf->Cell(60, 7, 'Guru Mata Pelajaran', 0, 1, 'C');
$pdf->Ln(15);
$pdf->Cell(120, 7, '', 0, 0, 'L');
$pdf->Cell(60, 7, ($mode == "detail") ? $absensi['nama_guru'] : $_SESSION['nama'], 0, 1, 'C');

// Output PDF
// Ganti baris terakhir dari
$pdf->Output('laporan_absensi.pdf', 'I');

// Menjadi (simpan ke file terlebih dahulu, lalu tampilkan)
$temp_file = tempnam(sys_get_temp_dir(), 'pdf_');
$pdf->Output($temp_file, 'F');

// Kemudian kirim file ke browser
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="laporan_absensi.pdf"');
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');
readfile($temp_file);
unlink($temp_file); // Hapus file sementara
exit();
?>

// Tambahkan di awal file setelah session_start()
set_time_limit(300); // Tambah batas waktu eksekusi menjadi 5 menit
ini_set('memory_limit', '256M'); // Tambah batas memori

// Tambahkan informasi semester pada header laporan
$pdf->Cell(30, 7, 'Tahun Ajaran', 0, 0, 'L');
$pdf->Cell(5, 7, ':', 0, 0, 'C');
$pdf->Cell(60, 7, $absensi['tahun_ajaran'], 0, 0, 'L');
$pdf->Cell(30, 7, 'Semester', 0, 0, 'L');
$pdf->Cell(5, 7, ':', 0, 0, 'C');
$pdf->Cell(50, 7, $absensi['semester'], 0, 1, 'L');