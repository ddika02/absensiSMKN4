<?php
session_start();
require_once '../config/koneksi.php';
require_once '../includes/config.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Cek role user, hanya admin yang boleh akses halaman ini
if ($_SESSION['role'] != 'admin') {
    header("Location: ../dashboard.php");
    exit();
}

// Ambil data kelas untuk dropdown
$query_kelas = "SELECT * FROM kelas ORDER BY nama_kelas ASC";
$result_kelas = mysqli_query($koneksi, $query_kelas);

// Proses form tambah siswa
if (isset($_POST['submit'])) {
    $nis = mysqli_real_escape_string($koneksi, $_POST['nis']);
    $nama_siswa = mysqli_real_escape_string($koneksi, $_POST['nama_siswa']);
    $jenis_kelamin = mysqli_real_escape_string($koneksi, $_POST['jenis_kelamin']);
    $tempat_lahir = mysqli_real_escape_string($koneksi, $_POST['tempat_lahir']);
    $tanggal_lahir = mysqli_real_escape_string($koneksi, $_POST['tanggal_lahir']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $no_telp = mysqli_real_escape_string($koneksi, $_POST['no_telp']);
    $id_kelas = mysqli_real_escape_string($koneksi, $_POST['id_kelas']);
    
    // Cek apakah NIS sudah ada
    $query_check = "SELECT * FROM siswa WHERE nis = '$nis'";
    $result_check = mysqli_query($koneksi, $query_check);
    
    if (mysqli_num_rows($result_check) > 0) {
        $error = "NIS sudah terdaftar. Silakan gunakan NIS lain.";
    } else {
        // Insert data siswa
        $query_insert = "INSERT INTO siswa (nis, nama_siswa, jenis_kelamin, tempat_lahir, tanggal_lahir, alamat, no_telp, id_kelas) 
                        VALUES ('$nis', '$nama_siswa', '$jenis_kelamin', '$tempat_lahir', '$tanggal_lahir', '$alamat', '$no_telp', '$id_kelas')";
        $result_insert = mysqli_query($koneksi, $query_insert);
        
        if ($result_insert) {
            header("Location: index.php?status=success&message=Data+siswa+berhasil+ditambahkan");
            exit();
        } else {
            $error = "Gagal menambahkan data siswa: " . mysqli_error($koneksi);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Siswa - Sistem Absensi SMKN 4 Tasikmalaya</title>
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
    <!-- Sidebar -->
    <?php include '../includes/sidebar.php'; ?>
    <!-- Main Content -->
    <div class="main-content">
        <!-- Navbar -->
        <?php include '../includes/navbar.php'; ?>

        <!-- Page Content -->
        <div class="container-fluid">
            <!-- Page Heading -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Tambah Siswa</h1>
                <a href="index.php" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <!-- Alert Error -->
            <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Content Row -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Form Tambah Siswa</h6>
                        </div>
                        <div class="card-body">
                            <form action="" method="POST">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="nis" class="form-label">NIS <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="nis" name="nis" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="nama_siswa" class="form-label">Nama Siswa <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="nama_siswa" name="nama_siswa" required>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="jenis_kelamin" class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                                        <select class="form-select" id="jenis_kelamin" name="jenis_kelamin" required>
                                            <option value="">Pilih Jenis Kelamin</option>
                                            <option value="L">Laki-laki</option>
                                            <option value="P">Perempuan</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="id_kelas" class="form-label">Kelas <span class="text-danger">*</span></label>
                                        <select class="form-select" id="id_kelas" name="id_kelas" required>
                                            <option value="">Pilih Kelas</option>
                                            <?php while ($kelas = mysqli_fetch_assoc($result_kelas)): ?>
                                                <option value="<?php echo $kelas['id']; ?>"><?php echo $kelas['nama_kelas']; ?></option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="tempat_lahir" class="form-label">Tempat Lahir <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="tempat_lahir" name="tempat_lahir" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="tanggal_lahir" class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" required>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="no_telp" class="form-label">Nomor Telepon</label>
                                        <input type="text" class="form-control" id="no_telp" name="no_telp">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="alamat" class="form-label">Alamat <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="alamat" name="alamat" rows="3" required></textarea>
                                    </div>
                                </div>
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <button type="reset" class="btn btn-secondary me-md-2">
                                        <i class="fas fa-undo me-1"></i> Reset
                                    </button>
                                    <button type="submit" name="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> Simpan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <?php include '../includes/scripts.php'; ?>
</body>
</html>