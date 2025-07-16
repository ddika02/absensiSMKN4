<?php
session_start();
require_once '../config/koneksi.php';
require_once '../includes/config.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Cek role (hanya admin yang boleh mengakses halaman ini)
if ($_SESSION['role'] != 'admin') {
    header("Location: ../dashboard.php");
    exit();
}

// Ambil ID jadwal dari parameter URL
if (!isset($_GET['id'])) {
    header("Location: index.php?status=error&message=" . urlencode("ID jadwal tidak ditemukan"));
    exit();
}

$id_jadwal = mysqli_real_escape_string($koneksi, $_GET['id']);

// Ambil data jadwal berdasarkan ID
$query_jadwal = "SELECT * FROM jadwal WHERE id = '$id_jadwal'";
$result_jadwal = mysqli_query($koneksi, $query_jadwal);

if (mysqli_num_rows($result_jadwal) == 0) {
    header("Location: index.php?status=error&message=" . urlencode("Jadwal tidak ditemukan"));
    exit();
}

$jadwal = mysqli_fetch_assoc($result_jadwal);

// Proses form edit jadwal
if (isset($_POST['edit'])) {
    $kelas_id = mysqli_real_escape_string($koneksi, $_POST['kelas_id']);
    $mapel_id = mysqli_real_escape_string($koneksi, $_POST['mapel_id']);
    $guru_id = mysqli_real_escape_string($koneksi, $_POST['guru_id']);
    $hari = mysqli_real_escape_string($koneksi, $_POST['hari']);
    $jam_mulai = mysqli_real_escape_string($koneksi, $_POST['jam_mulai']);
    $jam_selesai = mysqli_real_escape_string($koneksi, $_POST['jam_selesai']);
    
    $query = "UPDATE jadwal SET 
              kelas_id = '$kelas_id', 
              mapel_id = '$mapel_id', 
              guru_id = '$guru_id', 
              hari = '$hari', 
              jam_mulai = '$jam_mulai', 
              jam_selesai = '$jam_selesai' 
              WHERE id = '$id_jadwal'";
    
    $result = mysqli_query($koneksi, $query);
    
    if ($result) {
        header("Location: index.php?status=success&message=" . urlencode("Jadwal berhasil diperbarui"));
        exit();
    } else {
        $error = "Gagal memperbarui jadwal: " . mysqli_error($koneksi);
    }
}

// Ambil data untuk dropdown
$query_kelas = "SELECT id, nama_kelas FROM kelas ORDER BY nama_kelas ASC";
$result_kelas = mysqli_query($koneksi, $query_kelas);

$query_mapel = "SELECT id, nama_mapel FROM mapel ORDER BY nama_mapel ASC";
$result_mapel = mysqli_query($koneksi, $query_mapel);

$query_guru = "SELECT id, nama FROM guru ORDER BY nama ASC";
$result_guru = mysqli_query($koneksi, $query_guru);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Jadwal - Sistem Absensi SMKN 4 Tasikmalaya</title>
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
    <div class="main-content" id="content">
        <!-- Include Navbar -->
        <?php include '../includes/navbar.php'; ?>
        
        <!-- Page Content -->
        <div class="container-fluid">
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Edit Jadwal</h1>
                <a href="index.php" class="btn btn-sm btn-secondary shadow-sm">
                    <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
                </a>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold">Form Edit Jadwal</h6>
                </div>
                <div class="card-body">
                    <form action="" method="POST">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="kelas_id" class="form-label">Kelas</label>
                                <select class="form-select" id="kelas_id" name="kelas_id" required>
                                    <option value="" disabled>Pilih Kelas</option>
                                    <?php while ($kelas = mysqli_fetch_assoc($result_kelas)): ?>
                                    <option value="<?php echo $kelas['id']; ?>" <?php echo ($jadwal['kelas_id'] == $kelas['id']) ? 'selected' : ''; ?>>
                                        <?php echo $kelas['nama_kelas']; ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="mapel_id" class="form-label">Mata Pelajaran</label>
                                <select class="form-select" id="mapel_id" name="mapel_id" required>
                                    <option value="" disabled>Pilih Mata Pelajaran</option>
                                    <?php while ($mapel = mysqli_fetch_assoc($result_mapel)): ?>
                                    <option value="<?php echo $mapel['id']; ?>" <?php echo ($jadwal['mapel_id'] == $mapel['id']) ? 'selected' : ''; ?>>
                                        <?php echo $mapel['nama_mapel']; ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="guru_id" class="form-label">Guru</label>
                                <select class="form-select" id="guru_id" name="guru_id" required>
                                    <option value="" disabled>Pilih Guru</option>
                                    <?php while ($guru = mysqli_fetch_assoc($result_guru)): ?>
                                    <option value="<?php echo $guru['id']; ?>" <?php echo ($jadwal['guru_id'] == $guru['id']) ? 'selected' : ''; ?>>
                                        <?php echo $guru['nama']; ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="hari" class="form-label">Hari</label>
                                <select class="form-select" id="hari" name="hari" required>
                                    <option value="" disabled>Pilih Hari</option>
                                    <option value="Senin" <?php echo ($jadwal['hari'] == 'Senin') ? 'selected' : ''; ?>>Senin</option>
                                    <option value="Selasa" <?php echo ($jadwal['hari'] == 'Selasa') ? 'selected' : ''; ?>>Selasa</option>
                                    <option value="Rabu" <?php echo ($jadwal['hari'] == 'Rabu') ? 'selected' : ''; ?>>Rabu</option>
                                    <option value="Kamis" <?php echo ($jadwal['hari'] == 'Kamis') ? 'selected' : ''; ?>>Kamis</option>
                                    <option value="Jumat" <?php echo ($jadwal['hari'] == 'Jumat') ? 'selected' : ''; ?>>Jumat</option>
                                    <option value="Sabtu" <?php echo ($jadwal['hari'] == 'Sabtu') ? 'selected' : ''; ?>>Sabtu</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="jam_mulai" class="form-label">Jam Mulai</label>
                                <input type="time" class="form-control" id="jam_mulai" name="jam_mulai" value="<?php echo $jadwal['jam_mulai']; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="jam_selesai" class="form-label">Jam Selesai</label>
                                <input type="time" class="form-control" id="jam_selesai" name="jam_selesai" value="<?php echo $jadwal['jam_selesai']; ?>" required>
                            </div>
                        </div>
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="index.php" class="btn btn-secondary me-md-2">Batal</a>
                            <button type="submit" name="edit" class="btn btn-primary">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Include Scripts -->
    <?php include '../includes/scripts.php'; ?>
</body>
</html>