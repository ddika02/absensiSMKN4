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

// Proses form tambah guru
if (isset($_POST['submit'])) {
    $nip = mysqli_real_escape_string($koneksi, $_POST['nip']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $jenis_kelamin = mysqli_real_escape_string($koneksi, $_POST['jenis_kelamin']);
    $tempat_lahir = mysqli_real_escape_string($koneksi, $_POST['tempat_lahir']);
    $tanggal_lahir = mysqli_real_escape_string($koneksi, $_POST['tanggal_lahir']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $no_telp = mysqli_real_escape_string($koneksi, $_POST['no_telp']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $create_account = isset($_POST['create_account']) ? 1 : 0;
    
    // Cek apakah NIP sudah ada
    $query_check = "SELECT * FROM guru WHERE nip = '$nip'";
    $result_check = mysqli_query($koneksi, $query_check);
    
    if (mysqli_num_rows($result_check) > 0) {
        $error = "NIP sudah terdaftar. Silakan gunakan NIP lain.";
    } else {
        // Jika create_account dicentang, buat akun user
        if ($create_account) {
            // Cek apakah email sudah digunakan
            $query_check_email = "SELECT * FROM users WHERE username = '$email'";
            $result_check_email = mysqli_query($koneksi, $query_check_email);
            
            if (mysqli_num_rows($result_check_email) > 0) {
                $error = "Email sudah digunakan untuk akun lain. Silakan gunakan email lain.";
            } else {
                // Generate password default (NIP)
                $password = password_hash($nip, PASSWORD_DEFAULT);
                
                // Insert ke tabel users
                $query_user = "INSERT INTO users (username, password, nama, role) VALUES ('$email', '$password', '$nama', 'guru')";
                $result_user = mysqli_query($koneksi, $query_user);
                
                if ($result_user) {
                    $user_id = mysqli_insert_id($koneksi);
                    
                    // Insert ke tabel guru dengan user_id
                    $query_guru = "INSERT INTO guru (nip, nama, jenis_kelamin, tempat_lahir, tanggal_lahir, alamat, no_telp, email, user_id) 
                                  VALUES ('$nip', '$nama', '$jenis_kelamin', '$tempat_lahir', '$tanggal_lahir', '$alamat', '$no_telp', '$email', '$user_id')";
                    $result_guru = mysqli_query($koneksi, $query_guru);
                    
                    if ($result_guru) {
                        header("Location: index.php?status=success&message=Data+guru+berhasil+ditambahkan+dengan+akun+user");
                        exit();
                    } else {
                        // Jika gagal insert ke guru, hapus user yang sudah dibuat
                        $query_delete_user = "DELETE FROM users WHERE id = '$user_id'";
                        mysqli_query($koneksi, $query_delete_user);
                        $error = "Gagal menambahkan data guru: " . mysqli_error($koneksi);
                    }
                } else {
                    $error = "Gagal membuat akun user: " . mysqli_error($koneksi);
                }
            }
        } else {
            // Insert ke tabel guru tanpa user_id
            $query_guru = "INSERT INTO guru (nip, nama, jenis_kelamin, tempat_lahir, tanggal_lahir, alamat, no_telp, email) 
                          VALUES ('$nip', '$nama', '$jenis_kelamin', '$tempat_lahir', '$tanggal_lahir', '$alamat', '$no_telp', '$email')";
            $result_guru = mysqli_query($koneksi, $query_guru);
            
            if ($result_guru) {
                header("Location: index.php?status=success&message=Data+guru+berhasil+ditambahkan");
                exit();
            } else {
                $error = "Gagal menambahkan data guru: " . mysqli_error($koneksi);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Guru - Sistem Absensi SMKN 4 Tasikmalaya</title>
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
                <h1 class="h3 mb-0 text-gray-800">Tambah Guru</h1>
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
                            <h6 class="m-0 font-weight-bold text-primary">Form Tambah Guru</h6>
                        </div>
                        <div class="card-body">
                            <form action="" method="POST">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="nip" class="form-label">NIP <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="nip" name="nip" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="nama" class="form-label">Nama Guru <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="nama" name="nama" required>
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
                                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="email" name="email" required>
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
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="create_account" name="create_account" checked>
                                            <label class="form-check-label" for="create_account">
                                                Buat akun user untuk guru ini (Password default: NIP)
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <a href="index.php" class="btn btn-secondary me-md-2">
                                        <i class="fas fa-times me-1"></i> Batal
                                    </a>
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
    
    <?php include '../includes/scripts.php'; ?>
</body>
</html>