<?php
session_start();
require_once '../config/koneksi.php';
require_once '../includes/config.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Cek apakah user memiliki akses admin
if ($_SESSION['role'] != 'admin') {
    header("Location: ../dashboard.php?status=error&message=Anda+tidak+memiliki+akses+ke+halaman+ini");
    exit();
}

// Cek apakah ada parameter id
if (!isset($_GET['id'])) {
    header("Location: index.php?status=error&message=ID+kelas+tidak+ditemukan");
    exit();
}

$id = $_GET['id'];

// Ambil data kelas berdasarkan id
$query_kelas = "SELECT * FROM kelas WHERE id = '$id'";
$result_kelas = mysqli_query($koneksi, $query_kelas);

if (mysqli_num_rows($result_kelas) == 0) {
    header("Location: index.php?status=error&message=Data+kelas+tidak+ditemukan");
    exit();
}

$kelas = mysqli_fetch_assoc($result_kelas);

// Proses form edit kelas
if (isset($_POST['submit'])) {
    $nama_kelas = mysqli_real_escape_string($koneksi, $_POST['nama_kelas']);
    $tingkat = mysqli_real_escape_string($koneksi, $_POST['tingkat']);
    $jurusan = mysqli_real_escape_string($koneksi, $_POST['jurusan']);
    $wali_kelas = mysqli_real_escape_string($koneksi, $_POST['wali_kelas']);
    $tahun_ajaran = mysqli_real_escape_string($koneksi, $_POST['tahun_ajaran']);
    $semester = mysqli_real_escape_string($koneksi, $_POST['semester']);
    
    // Validasi data
    $errors = [];
    
    // Cek apakah nama kelas sudah ada (kecuali kelas yang sedang diedit)
    $query_cek = "SELECT * FROM kelas WHERE nama_kelas = '$nama_kelas' AND id != '$id'";
    $result_cek = mysqli_query($koneksi, $query_cek);
    if (mysqli_num_rows($result_cek) > 0) {
        $errors[] = "Nama kelas sudah ada dalam database";
    }
    
    if (empty($errors)) {
        $query = "UPDATE kelas SET 
                 nama_kelas = '$nama_kelas', 
                 tingkat = '$tingkat', 
                 jurusan = '$jurusan', 
                 wali_kelas = '$wali_kelas', 
                 tahun_ajaran = '$tahun_ajaran',
                 semester = '$semester' 
                 WHERE id = '$id'";
        
        if (mysqli_query($koneksi, $query)) {
            header("Location: index.php?status=success&message=Data+kelas+berhasil+diperbarui");
            exit();
        } else {
            $errors[] = "Gagal memperbarui data: " . mysqli_error($koneksi);
        }
    }
}

// Ambil data guru untuk dropdown wali kelas
$query_guru = "SELECT id, nama FROM guru ORDER BY nama ASC";
$result_guru = mysqli_query($koneksi, $query_guru);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Kelas - Sistem Absensi SMKN 4 Tasikmalaya</title>
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
    <div class="main-content">
       <!-- Include Navbar -->
       <?php include '../includes/navbar.php'; ?>

        <!-- Page Content -->
        <div class="container-fluid">
            <!-- Page Heading -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Edit Kelas</h1>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <!-- Alert Messages -->
            <?php if (isset($errors) && !empty($errors)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong>Error!</strong>
                    <ul class="mb-0 mt-2">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Content Row -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Form Edit Kelas</h6>
                </div>
                <div class="card-body">
                    <form action="" method="POST">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="nama_kelas" class="form-label">Nama Kelas <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nama_kelas" name="nama_kelas" value="<?php echo isset($_POST['nama_kelas']) ? $_POST['nama_kelas'] : $kelas['nama_kelas']; ?>" required>
                                <div class="form-text">Contoh: X PPLG 1, XI TJKT 2, XII TOI 3</div>
                            </div>
                            <div class="col-md-6">
                                <label for="tingkat" class="form-label">Tingkat <span class="text-danger">*</span></label>
                                <select class="form-select" id="tingkat" name="tingkat" required>
                                    <option value="" disabled>Pilih Tingkat</option>
                                    <option value="X" <?php echo (isset($_POST['tingkat']) && $_POST['tingkat'] == 'X') || (!isset($_POST['tingkat']) && $kelas['tingkat'] == 'X') ? 'selected' : ''; ?>>X (Sepuluh)</option>
                                    <option value="XI" <?php echo (isset($_POST['tingkat']) && $_POST['tingkat'] == 'XI') || (!isset($_POST['tingkat']) && $kelas['tingkat'] == 'XI') ? 'selected' : ''; ?>>XI (Sebelas)</option>
                                    <option value="XII" <?php echo (isset($_POST['tingkat']) && $_POST['tingkat'] == 'XII') || (!isset($_POST['tingkat']) && $kelas['tingkat'] == 'XII') ? 'selected' : ''; ?>>XII (Dua Belas)</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="jurusan" class="form-label">Jurusan <span class="text-danger">*</span></label>
                                <select class="form-select" id="jurusan" name="jurusan" required>
                                    <option value="" disabled selected>Pilih Jurusan</option>
                                    <option value="PPLG" <?php echo (isset($_POST['jurusan']) && $_POST['jurusan'] == 'PPLG') ? 'selected' : ''; ?>>Pengembangan Perangkat Lunak dan Gim(PPLG)</option>
                                    <option value="TJKT" <?php echo (isset($_POST['jurusan']) && $_POST['jurusan'] == 'TJKT') ? 'selected' : ''; ?>>Teknik Jaringan Komputer dan Telekomunikasi(TJKT)</option>
                                    <option value="TOI" <?php echo (isset($_POST['jurusan']) && $_POST['jurusan'] == 'TOI') ? 'selected' : ''; ?>>Teknik Otomasi Industri(TOI)</option>
                                    <option value="TSM" <?php echo (isset($_POST['jurusan']) && $_POST['jurusan'] == 'TSM') ? 'selected' : ''; ?>>Teknik Sepeda Motor(TSM)</option>
                                    <option value="DKV" <?php echo (isset($_POST['jurusan']) && $_POST['jurusan'] == 'DKV') ? 'selected' : ''; ?>>Desain Komunikasi Visual(DKV)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="wali_kelas" class="form-label">Wali Kelas <span class="text-danger">*</span></label>
                                <select class="form-select" id="wali_kelas" name="wali_kelas" required>
                                    <option value="" disabled>Pilih Wali Kelas</option>
                                    <?php 
                                    // Reset pointer mysqli result
                                    mysqli_data_seek($result_guru, 0);
                                    while ($guru = mysqli_fetch_assoc($result_guru)): 
                                    ?>
                                        <option value="<?php echo $guru['id']; ?>" <?php echo (isset($_POST['wali_kelas']) && $_POST['wali_kelas'] == $guru['id']) || (!isset($_POST['wali_kelas']) && $kelas['wali_kelas'] == $guru['id']) ? 'selected' : ''; ?>>
                                            <?php echo $guru['nama']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="tahun_ajaran" class="form-label">Tahun Ajaran <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="tahun_ajaran" name="tahun_ajaran" value="<?php echo isset($_POST['tahun_ajaran']) ? $_POST['tahun_ajaran'] : $kelas['tahun_ajaran']; ?>" required>
                                <div class="form-text">Contoh: 2023/2024</div>
                            </div>
                            <div class="col-md-6">
                                <label for="semester" class="form-label">Semester <span class="text-danger">*</span></label>
                                <select class="form-select" id="semester" name="semester" required>
                                    <option value="Ganjil" <?php echo (isset($_POST['semester']) && $_POST['semester'] == 'Ganjil') || (!isset($_POST['semester']) && $kelas['semester'] == 'Ganjil') ? 'selected' : ''; ?>>Ganjil</option>
                                    <option value="Genap" <?php echo (isset($_POST['semester']) && $_POST['semester'] == 'Genap') || (!isset($_POST['semester']) && $kelas['semester'] == 'Genap') ? 'selected' : ''; ?>>Genap</option>
                                </select>
                            </div>
                        </div>
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <a href="index.php" class="btn btn-secondary me-md-2">
                                <i class="fas fa-times me-1"></i> Batal
                            </a>
                            <button type="submit" name="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Include Scripts -->
    <?php include '../includes/scripts.php'; ?>
</body>
</html>