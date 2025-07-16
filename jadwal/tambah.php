<?php
session_start();
require_once '../config/koneksi.php';
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Ambil data guru, mapel, kelas
$guru = mysqli_query($koneksi, "SELECT id, nama FROM guru ORDER BY nama ASC");
$mapel = mysqli_query($koneksi, "SELECT id, nama_mapel FROM mapel ORDER BY nama_mapel ASC");
$kelas = mysqli_query($koneksi, "SELECT id, nama_kelas FROM kelas ORDER BY nama_kelas ASC");

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hari = $_POST['hari'];
    $jam_mulai = $_POST['jam_mulai'];
    $jam_selesai = $_POST['jam_selesai'];
    $guru_id = $_POST['guru_id'];
    $mapel_id = $_POST['mapel_id'];
    $kelas_id = $_POST['kelas_id'];

    $sql = "INSERT INTO jadwal (hari, jam_mulai, jam_selesai, guru_id, mapel_id, kelas_id) 
            VALUES ('$hari', '$jam_mulai', '$jam_selesai', '$guru_id', '$mapel_id', '$kelas_id')";

    if (mysqli_query($koneksi, $sql)) {
        header("Location: index.php?status=success&message=" . urlencode("Jadwal berhasil ditambahkan"));
    } else {
        header("Location: index.php?status=error&message=" . urlencode("Gagal menambahkan jadwal"));
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Jadwal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php include '../includes/styles.php'; ?>
</head>
<body>
<?php include '../includes/sidebar.php'; ?>
<div class="main-content" id="content">
    <?php include '../includes/navbar.php'; ?>

    <div class="container-fluid mt-4">
        <h3 class="mb-4">Tambah Jadwal Pelajaran</h3>

        <div class="card shadow">
            <div class="card-body">
                <form method="POST">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Hari</label>
                            <select name="hari" class="form-select" required>
                                <option value="">Pilih Hari</option>
                                <?php
                                $hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                                foreach ($hari as $h) {
                                    echo "<option value='$h'>$h</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Jam Mulai</label>
                            <input type="time" name="jam_mulai" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label>Jam Selesai</label>
                            <input type="time" name="jam_selesai" class="form-control" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label>Guru</label>
                            <select name="guru_id" class="form-select" required>
                                <option value="">Pilih Guru</option>
                                <?php while ($g = mysqli_fetch_assoc($guru)): ?>
                                    <option value="<?= $g['id'] ?>"><?= $g['nama'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Mata Pelajaran</label>
                            <select name="mapel_id" class="form-select" required>
                                <option value="">Pilih Mapel</option>
                                <?php while ($m = mysqli_fetch_assoc($mapel)): ?>
                                    <option value="<?= $m['id'] ?>"><?= $m['nama_mapel'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Kelas</label>
                            <select name="kelas_id" class="form-select" required>
                                <option value="">Pilih Kelas</option>
                                <?php while ($k = mysqli_fetch_assoc($kelas)): ?>
                                    <option value="<?= $k['id'] ?>"><?= $k['nama_kelas'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>

                    <div class="text-end">
                        <a href="index.php" class="btn btn-secondary">Kembali</a>
                        <button type="submit" class="btn btn-primary">Simpan Jadwal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/scripts.php'; ?>
</body>
</html>
