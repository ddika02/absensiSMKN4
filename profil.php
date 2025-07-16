<?php
session_start();
require_once 'config/koneksi.php';
require_once 'includes/config.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Ambil data user yang login
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$query = "SELECT * FROM users WHERE id = '$user_id'";
$result = mysqli_query($koneksi, $query);
$user = mysqli_fetch_assoc($result);

// Proses update profil
$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password_lama = $_POST['password_lama'];
    $password_baru = $_POST['password_baru'];
    $konfirmasi_password = $_POST['konfirmasi_password'];
    
    // Validasi username (jika diubah)
    if ($username != $user['username']) {
        $query_cek = "SELECT * FROM users WHERE username = '$username' AND id != '$user_id'";
        $result_cek = mysqli_query($koneksi, $query_cek);
        
        if (mysqli_num_rows($result_cek) > 0) {
            $error = "Username sudah digunakan!";
        }
    }
    
    // Proses upload foto profil jika ada
    $foto_profil = $user['foto_profil'] ?? 'assets/img/user-default.png';
    
    if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['foto_profil']['name'];
        $filesize = $_FILES['foto_profil']['size'];
        $filetype = $_FILES['foto_profil']['type'];
        
        // Validasi ekstensi file
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if (!in_array(strtolower($ext), $allowed)) {
            $error = "Format file tidak didukung. Gunakan format JPG, JPEG, PNG, atau GIF.";
        }
        
        // Validasi ukuran file (maksimal 2MB)
        if ($filesize > 2097152) {
            $error = "Ukuran file terlalu besar. Maksimal 2MB.";
        }
        
        // Jika tidak ada error, proses upload
        if (empty($error)) {
            // Buat nama file unik
            $new_filename = 'user_' . $user_id . '_' . time() . '.' . $ext;
            $upload_dir = 'assets/img/profile/';
            
            // Buat direktori jika belum ada
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $destination = $upload_dir . $new_filename;
            
            // Pindahkan file
            if (move_uploaded_file($_FILES['foto_profil']['tmp_name'], $destination)) {
                $foto_profil = $destination;
                
                // Hapus foto lama jika bukan default
                if ($user['foto_profil'] != 'assets/img/user-default.png' && file_exists($user['foto_profil'])) {
                    unlink($user['foto_profil']);
                }
            } else {
                $error = "Gagal mengupload file.";
            }
        }
    }
    
    // Jika tidak ada error, lanjutkan update
    if (empty($error)) {
        // Jika password diubah
        if (!empty($password_baru)) {
            // Verifikasi password lama
            if (password_verify($password_lama, $user['password'])) {
                // Validasi password baru dan konfirmasi
                if ($password_baru === $konfirmasi_password) {
                    $hashed_password = password_hash($password_baru, PASSWORD_DEFAULT);
                    $query_update = "UPDATE users SET nama = '$nama', username = '$username', password = '$hashed_password', foto_profil = '$foto_profil' WHERE id = '$user_id'";
                } else {
                    $error = "Password baru dan konfirmasi password tidak sama!";
                }
            } else {
                $error = "Password lama salah!";
            }
        } else {
            // Update tanpa mengubah password
            $query_update = "UPDATE users SET nama = '$nama', username = '$username', foto_profil = '$foto_profil' WHERE id = '$user_id'";
        }
        
        // Eksekusi query update jika tidak ada error
        if (empty($error)) {
            $result_update = mysqli_query($koneksi, $query_update);
            
            if ($result_update) {
                $_SESSION['nama'] = $nama;
                $_SESSION['username'] = $username;
                $success = "Profil berhasil diperbarui!";
                
                // Refresh data user
                $query = "SELECT * FROM users WHERE id = '$user_id'";
                $result = mysqli_query($koneksi, $query);
                $user = mysqli_fetch_assoc($result);
            } else {
                $error = "Gagal memperbarui profil: " . mysqli_error($koneksi);
            }
        }
    }
}

// Gunakan foto profil dari database atau default jika tidak ada
$foto_profil = $user['foto_profil'] ?? 'assets/img/user-default.png';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - Sistem Absensi SMKN 4 Tasikmalaya</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <?php include 'includes/styles.php'; ?>
</head>
<body>
    <!-- Sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content" id="content">
        <!-- Navbar -->
        <?php include 'includes/navbar.php'; ?>

        <!-- Page Content -->
        <div class="container-fluid">
            <!-- Page Heading -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Edit Profil</h1>
            </div>
            
            <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <?php echo $success; ?>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-lg-8">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Edit Profil</h6>
                        </div>
                        <div class="card-body">
                            <form action="" method="POST" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="foto_profil" class="form-label">Foto Profil</label>
                                    <input type="file" class="form-control" id="foto_profil" name="foto_profil" accept="image/*">
                                    <small class="text-muted">Format: JPG, JPEG, PNG, GIF. Ukuran maksimal: 2MB</small>
                                    <div class="mt-2">
                                        <img id="preview" src="#" alt="Preview" class="img-preview">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="nama" class="form-label">Nama Lengkap</label>
                                    <input type="text" class="form-control" id="nama" name="nama" value="<?php echo $user['nama']; ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="username" name="username" value="<?php echo $user['username']; ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="role" class="form-label">Role</label>
                                    <input type="text" class="form-control" id="role" value="<?php echo ucfirst($user['role']); ?>" readonly>
                                </div>
                                
                                <hr>
                                
                                <h5>Ubah Password</h5>
                                <p class="text-muted small">Kosongkan jika tidak ingin mengubah password</p>
                                
                                <div class="mb-3">
                                    <label for="password_lama" class="form-label">Password Lama</label>
                                    <input type="password" class="form-control" id="password_lama" name="password_lama">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="password_baru" class="form-label">Password Baru</label>
                                    <input type="password" class="form-control" id="password_baru" name="password_baru">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="konfirmasi_password" class="form-label">Konfirmasi Password Baru</label>
                                    <input type="password" class="form-control" id="konfirmasi_password" name="konfirmasi_password">
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Informasi Pengguna</h6>
                        </div>
                        <div class="card-body text-center">
                            <img src="<?php echo $foto_profil; ?>" alt="User" class="img-profile">
                            <h5 class="mb-1"><?php echo $user['nama']; ?></h5>
                            <p class="text-muted"><?php echo ucfirst($user['role']); ?></p>
                            <hr>
                            <div class="text-start">
                                <p><strong>Username:</strong> <?php echo $user['username']; ?></p>
                                <p><strong>Terakhir Login:</strong> <?php echo date('d M Y H:i:s'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <?php include 'includes/scripts.php'; ?>
    <script>
        // Preview foto profil sebelum upload
        document.getElementById('foto_profil').addEventListener('change', function() {
            const preview = document.getElementById('preview');
            const file = this.files[0];
            
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
            }
        });
    </script>
</body>
</html>
