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

// Cek parameter id_jadwal
if (!isset($_GET['id_jadwal']) || empty($_GET['id_jadwal'])) {
    header("Location: index.php");
    exit();
}

$id_jadwal = $_GET['id_jadwal'];

// Query jadwal tanpa filter NIP guru
$query_jadwal = "SELECT j.*, mp.nama_mapel, k.nama_kelas 
                FROM jadwal j 
                JOIN mapel mp ON j.mapel_id = mp.id 
                JOIN kelas k ON j.kelas_id = k.id 
                WHERE j.id = '$id_jadwal'";
$result_jadwal = mysqli_query($koneksi, $query_jadwal); // Eksekusi query
if (mysqli_num_rows($result_jadwal) == 0) {
    header("Location: index.php");
    exit();
}

$jadwal = mysqli_fetch_assoc($result_jadwal);
$kelas_id = $jadwal['kelas_id']; 

// Ambil data siswa di kelas tersebut
$query_siswa = "SELECT * FROM siswa WHERE id_kelas = '$kelas_id' ORDER BY nama_siswa ASC";
$result_siswa = mysqli_query($koneksi, $query_siswa);

// Hitung pertemuan terakhir
$query_pertemuan = "SELECT MAX(pertemuan) as last_pertemuan FROM absensi WHERE id_jadwal = '$id_jadwal'";
$result_pertemuan = mysqli_query($koneksi, $query_pertemuan);
$row_pertemuan = mysqli_fetch_assoc($result_pertemuan);
$pertemuan = isset($row_pertemuan['last_pertemuan']) ? $row_pertemuan['last_pertemuan'] + 1 : 1;

// Cek apakah sudah ada absensi hari ini
$tanggal = date('Y-m-d');
$query_cek = "SELECT * FROM absensi WHERE id_jadwal = '$id_jadwal' AND tanggal = '$tanggal'";
$result_cek = mysqli_query($koneksi, $query_cek);
$absensi_exists = mysqli_num_rows($result_cek) > 0;
$pesan = "";

// Proses form absensi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tanggal = $_POST['tanggal'];
    $pertemuan = $_POST['pertemuan'];
    $materi = mysqli_real_escape_string($koneksi, $_POST['materi']);
    $catatan = mysqli_real_escape_string($koneksi, $_POST['catatan']);
    
    // Cek lagi apakah sudah ada absensi di tanggal tersebut
    $query_cek = "SELECT * FROM absensi WHERE id_jadwal = '$id_jadwal' AND tanggal = '$tanggal'";
    $result_cek = mysqli_query($koneksi, $query_cek);
    
    if (mysqli_num_rows($result_cek) > 0) {
        $pesan = "<div class='alert alert-danger'>Absensi untuk tanggal ini sudah ada!</div>";
    } else {
        // Simpan data absensi
        $query_insert = "INSERT INTO absensi (id_jadwal, tanggal, pertemuan, materi, catatan)
        VALUES ('$id_jadwal', '$tanggal', '$pertemuan', '$materi', '$catatan')";
        $result_insert = mysqli_query($koneksi, $query_insert);
        
        if ($result_insert) {
            $id_absensi = mysqli_insert_id($koneksi);
            $success = true;
            
            // Simpan detail absensi untuk setiap siswa
            foreach ($_POST['status'] as $id_siswa => $status) {
                $keterangan = isset($_POST['keterangan'][$id_siswa]) ? mysqli_real_escape_string($koneksi, $_POST['keterangan'][$id_siswa]) : '';
                
                $query_detail = "INSERT INTO absensi_detail (id_absensi, id_siswa, status, keterangan)
                VALUES ('$id_absensi', '$id_siswa', '$status', '$keterangan')";
                $result_detail = mysqli_query($koneksi, $query_detail);
                
                if (!$result_detail) {
                    $success = false;
                }
            }
            
            if ($success) {
                // Catat aktivitas
                $guru_id = $_SESSION['user_id']; // Gunakan user_id yang login
                $keterangan = "Melakukan absensi kelas {$jadwal['nama_kelas']} mata pelajaran {$jadwal['nama_mapel']} pertemuan ke-$pertemuan";
$user_id = $_SESSION['user_id'];
$query_guru = mysqli_query($koneksi, "SELECT id FROM guru WHERE user_id = '$user_id'");
$data_guru = mysqli_fetch_assoc($query_guru);
$id_guru = $data_guru['id'];
if (!$id_guru) {
    die("ID Guru tidak ditemukan. Pastikan guru telah dikaitkan dengan user.");
}

                $query_aktivitas = "INSERT INTO aktivitas_absensi (tanggal, id_guru, id_kelas, keterangan) VALUES ('$tanggal', '$id_guru', '$kelas_id', '$keterangan')";
                mysqli_query($koneksi, $query_aktivitas);
                
                header("Location: detail.php?id=$id_absensi&success=1");
                exit();
            } else {
                $pesan = "<div class='alert alert-danger'>Gagal menyimpan detail absensi!</div>";
            }
        } else {
            $pesan = "<div class='alert alert-danger'>Gagal menyimpan data absensi!</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Absensi Siswa - SMKN 4</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <?php include '../includes/styles.php'; ?>
    
    <style>
        /* Style untuk status kehadiran modern */
        .status-container {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 5px;
        }
        
        .status-card {
            position: relative;
            width: 70px;
            height: 70px;
            border-radius: 8px;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .status-card input[type="radio"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .status-card-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            padding: 10px;
            text-align: center;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .status-card i {
            font-size: 20px;
            margin-bottom: 5px;
        }
        
        .status-card-hadir .status-card-content {
            background-color: rgba(28, 200, 138, 0.1);
            color: var(--success-color);
        }
        
        .status-card-izin .status-card-content {
            background-color: rgba(54, 185, 204, 0.1);
            color: var(--info-color);
        }
        
        .status-card-sakit .status-card-content {
            background-color: rgba(246, 194, 62, 0.1);
            color: var(--warning-color);
        }
        
        .status-card-alpha .status-card-content {
            background-color: rgba(231, 74, 59, 0.1);
            color: var(--danger-color);
        }
        
        /* Hover state */
        .status-card:hover .status-card-content {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        /* Checked state */
        .status-card input[type="radio"]:checked + .status-card-content {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .status-card-hadir input[type="radio"]:checked + .status-card-content {
            background-color: var(--success-color);
            color: white;
            border-color: var(--success-color);
        }
        
        .status-card-izin input[type="radio"]:checked + .status-card-content {
            background-color: var(--info-color);
            color: white;
            border-color: var(--info-color);
        }
        
        .status-card-sakit input[type="radio"]:checked + .status-card-content {
            background-color: var(--warning-color);
            color: white;
            border-color: var(--warning-color);
        }
        
        .status-card-alpha input[type="radio"]:checked + .status-card-content {
            background-color: var(--danger-color);
            color: white;
            border-color: var(--danger-color);
        }
        
        /* Responsive untuk status cards */
        @media (max-width: 768px) {
            .status-container {
                justify-content: center;
            }
            
            .status-card {
                width: 60px;
                height: 60px;
            }
            
            .status-card i {
                font-size: 16px;
                margin-bottom: 3px;
            }
            
            .status-card-content span {
                font-size: 12px;
            }
        }
    </style>
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
                <h1 class="h3 mb-0 text-gray-800">Form Absensi Siswa</h1>
                <a href="index.php" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>

            <div class="card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Jadwal</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="150">Mata Pelajaran</th>
                                    <td>: <?php echo $jadwal['nama_mapel']; ?></td>
                                </tr>
                                <tr>
                                    <th>Kelas</th>
                                    <td>: <?php echo $jadwal['nama_kelas']; ?></td>
                                </tr>
                                <tr>
                                    <th>Hari</th>
                                    <td>: <?php echo $jadwal['hari']; ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="150">Jam</th>
                                    <td>: <?php echo $jadwal['jam_mulai'] . ' - ' . $jadwal['jam_selesai']; ?></td>
                                </tr>
                                <tr>
                                    <th>Pertemuan Ke</th>
                                    <td>: <?php echo $pertemuan; ?></td>
                                </tr>
                                <tr>
                                    <th>Tanggal</th>
                                    <td>: <?php echo date('d-m-Y'); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($absensi_exists): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> Absensi untuk jadwal ini pada tanggal <?php echo date('d-m-Y'); ?> sudah ada!
            </div>
            <?php else: ?>
            <?php echo $pesan; ?>
            <form method="POST" action="">
                <div class="card">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">Data Absensi</h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="tanggal" class="form-label">Tanggal</label>
                                <input type="date" class="form-control" id="tanggal" name="tanggal" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="pertemuan" class="form-label">Pertemuan Ke</label>
                                <input type="number" class="form-control" id="pertemuan" name="pertemuan" value="<?php echo $pertemuan; ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="materi" class="form-label">Materi</label>
                            <textarea class="form-control" id="materi" name="materi" rows="2" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="catatan" class="form-label">Catatan</label>
                            <textarea class="form-control" id="catatan" name="catatan" rows="2"></textarea>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">Daftar Siswa</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th width="5%">No</th>
                                        <th width="15%">NIS</th>
                                        <th width="30%">Nama Siswa</th>
                                        <th width="30%">Status</th>
                                        <th width="20%">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $no = 1;
                                    if (mysqli_num_rows($result_siswa) > 0) {
                                        while ($siswa = mysqli_fetch_assoc($result_siswa)) {
                                            echo "<tr>";
                                            echo "<td>" . $no++ . "</td>";
                                            echo "<td>" . $siswa['nis'] . "</td>";
                                            echo "<td>" . $siswa['nama_siswa'] . "</td>";
                                            echo "<td>";
                                            echo "<div class='status-container'>";
                                            echo "<label class='status-card status-card-hadir'>";
                                            echo "<input type='radio' name='status[" . $siswa['id'] . "]' value='Hadir' checked>";
                                            echo "<div class='status-card-content'>";
                                            echo "<i class='fas fa-check-circle'></i>";
                                            echo "<span>Hadir</span>";
                                            echo "</div>";
                                            echo "</label>";
                                            
                                            echo "<label class='status-card status-card-izin'>";
                                            echo "<input type='radio' name='status[" . $siswa['id'] . "]' value='Izin'>";
                                            echo "<div class='status-card-content'>";
                                            echo "<i class='fas fa-clipboard-check'></i>";
                                            echo "<span>Izin</span>";
                                            echo "</div>";
                                            echo "</label>";
                                            
                                            echo "<label class='status-card status-card-sakit'>";
                                            echo "<input type='radio' name='status[" . $siswa['id'] . "]' value='Sakit'>";
                                            echo "<div class='status-card-content'>";
                                            echo "<i class='fas fa-procedures'></i>";
                                            echo "<span>Sakit</span>";
                                            echo "</div>";
                                            echo "</label>";
                                            
                                            echo "<label class='status-card status-card-alpha'>";
                                            echo "<input type='radio' name='status[" . $siswa['id'] . "]' value='Alpha'>";
                                            echo "<div class='status-card-content'>";
                                            echo "<i class='fas fa-times-circle'></i>";
                                            echo "<span>Alpha</span>";
                                            echo "</div>";
                                            echo "</label>";
                                            echo "</div>";
                                            echo "</td>";
                                            echo "<td><input type='text' class='form-control' name='keterangan[" . $siswa['id'] . "]' placeholder='Keterangan'></td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='5' class='text-center'>Tidak ada data siswa di kelas ini</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Absensi</button>
                            <a href="index.php" class="btn btn-secondary"><i class="fas fa-times"></i> Batal</a>
                        </div>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Include Scripts -->
    <?php include '../includes/scripts.php'; ?>
    
    <!-- Custom JS untuk form absensi -->
    <script>
        $(document).ready(function() {
            // Kode JavaScript khusus untuk form absensi jika diperlukan
        });
    </script>
</body>
</html>

<!-- Hapus kode statis berikut -->
<!-- Ganti bagian status kehadiran dengan kode berikut -->
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
            $('.content').toggleClass('active');
        });
    });
</script>
</body>
</html>

<style>
    :root {
        --primary-color: #4e73df;
        --secondary-color: #1cc88a;
        --success-color: #1cc88a;
        --info-color: #36b9cc;
        --warning-color: #f6c23e;
        --danger-color: #e74a3b;
        --light-color: #f8f9fc;
        --dark-color: #5a5c69;
    }
    
    /* Sidebar Styles */
    .sidebar {
        min-height: 100vh;
        background: linear-gradient(180deg, var(--primary-color) 0%, #224abe 100%);
        color: white;
        width: 250px;
        position: fixed;
        top: 0;
        left: 0;
        z-index: 100;
        transition: all 0.3s;
    }
    
    .sidebar-brand {
        height: 70px;
        padding: 15px 20px;
        display: flex;
        align-items: center;
        font-size: 20px;
        font-weight: 700;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .sidebar-menu {
        padding: 20px 0;
    }
    
    .sidebar-menu a {
        display: block;
        padding: 12px 20px;
        color: rgba(255, 255, 255, 0.8);
        text-decoration: none;
        transition: all 0.3s;
    }
    
    .sidebar-menu a:hover, .sidebar-menu a.active {
        background-color: rgba(255, 255, 255, 0.1);
        color: white;
        border-left: 4px solid white;
    }
    
    .sidebar-menu i {
        margin-right: 10px;
        width: 20px;
        text-align: center;
    }
    
    /* Content Styles */
    .content {
        margin-left: 250px;
        padding: 20px;
    }
    
    .card {
        border: none;
        border-radius: 5px;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        margin-bottom: 20px;
    }
    
    .card-header {
        background-color: white;
        border-bottom: 1px solid #e3e6f0;
        padding: 15px 20px;
    }
    
    .card-body {
        padding: 20px;
    }
    
    .btn-primary {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }
    
    .btn-success {
        background-color: var(--success-color);
        border-color: var(--success-color);
    }
    
    .btn-info {
        background-color: var(--info-color);
        border-color: var(--info-color);
    }
    
    .btn-warning {
        background-color: var(--warning-color);
        border-color: var(--warning-color);
    }
    
    .btn-danger {
        background-color: var(--danger-color);
        border-color: var(--danger-color);
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .sidebar {
            margin-left: -250px;
        }
        .content {
            margin-left: 0;
        }
        .sidebar.active {
            margin-left: 0;
        }
        .content.active {
            margin-left: 250px;
        }
    }
</style>