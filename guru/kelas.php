<?php
session_start();
require_once '../config/koneksi.php';
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'guru') {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$query_guru = "SELECT id FROM guru WHERE user_id = '$user_id'";
$result_guru = mysqli_query($koneksi, $query_guru);
$guru = mysqli_fetch_assoc($result_guru);
$guru_id = $guru['id'];

$query_kelas = "SELECT DISTINCT k.* FROM kelas k JOIN jadwal j ON k.id = j.kelas_id WHERE j.guru_id = '$guru_id' ORDER BY k.nama_kelas ASC";
$result_kelas = mysqli_query($koneksi, $query_kelas);

$query_user = "SELECT * FROM users WHERE id = '$user_id'";
$result_user = mysqli_query($koneksi, $query_user);
$user = mysqli_fetch_assoc($result_user);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelas yang Diajar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php include '../includes/styles.php'; ?>
</head>
<body>

<?php include '../includes/sidebar.php'; ?>
<div class="main-content" id="content">
<?php include '../includes/navbar.php'; ?>

<div class="container-fluid mt-4">
    <h3 class="mb-4">Kelas yang Diajar</h3>
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Nama Kelas</th>
                    <th>Tingkat</th>
                    <th>Jurusan</th>
                </tr>
            </thead>
            <tbody>
            <?php 
            $no = 1;
            if(mysqli_num_rows($result_kelas) > 0) {
                while ($row = mysqli_fetch_assoc($result_kelas)): ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><?= htmlspecialchars($row['nama_kelas']); ?></td>
                        <td><?= htmlspecialchars($row['tingkat']); ?></td>
                        <td><?= htmlspecialchars($row['jurusan']); ?></td>
                    </tr>
                <?php endwhile;
            } else {
                echo '<tr><td colspan="4" class="text-center">Tidak ada data kelas yang diajar.</td></tr>';
            }
            ?>
            </tbody>
        </table>
    </div>
</div>
</div>

<?php include '../includes/scripts.php'; ?>
</body>
</html>