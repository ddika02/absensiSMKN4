<?php
session_start();
require_once '../config/koneksi.php';
require_once '../includes/config.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SESSION['role'] != 'admin') {
    header("Location: ../dashboard.php");
    exit();
}

// Tambah
if (isset($_POST['tambah'])) {
    $kode = mysqli_real_escape_string($koneksi, $_POST['kode']);
    $nama_mapel = mysqli_real_escape_string($koneksi, $_POST['nama_mapel']);

    $check_query = "SELECT * FROM mapel WHERE kode = '$kode'";
    $check_result = mysqli_query($koneksi, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        $error = "Kode mata pelajaran sudah digunakan!";
    } else {
        $query = "INSERT INTO mapel (kode, nama_mapel) VALUES ('$kode', '$nama_mapel')";
        $result = mysqli_query($koneksi, $query);

        if ($result) {
            $success = "Mata pelajaran berhasil ditambahkan!";
        } else {
            $error = "Gagal menambahkan mata pelajaran: " . mysqli_error($koneksi);
        }
    }
}

// Edit
if (isset($_POST['edit'])) {
    $id = mysqli_real_escape_string($koneksi, $_POST['id']);
    $kode = mysqli_real_escape_string($koneksi, $_POST['kode']);
    $nama_mapel = mysqli_real_escape_string($koneksi, $_POST['nama_mapel']);

    $query = "UPDATE mapel SET kode = '$kode', nama_mapel = '$nama_mapel' WHERE id = '$id'";
    $result = mysqli_query($koneksi, $query);

    if ($result) {
        $success = "Mata pelajaran berhasil diperbarui!";
    } else {
        $error = "Gagal memperbarui mata pelajaran: " . mysqli_error($koneksi);
    }
}

// Hapus
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $query = "DELETE FROM mapel WHERE id = '$id'";
    $result = mysqli_query($koneksi, $query);

    if ($result) {
        $success = "Mata pelajaran berhasil dihapus!";
    } else {
        $error = "Gagal menghapus mata pelajaran: " . mysqli_error($koneksi);
    }
}

$query = "SELECT * FROM mapel ORDER BY nama_mapel ASC";
$result = mysqli_query($koneksi, $query);

$user_id = $_SESSION['user_id'];
$query_user = "SELECT * FROM users WHERE id = '$user_id'";
$result_user = mysqli_query($koneksi, $query_user);
$user = mysqli_fetch_assoc($result_user);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Mata Pelajaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php include '../includes/styles.php'; ?>
</head>
<body>

<?php include '../includes/sidebar.php'; ?>
<div class="main-content" id="content">
<?php include '../includes/navbar.php'; ?>

<div class="container-fluid mt-4">
    <h3 class="mb-4">Mata Pelajaran</h3>
    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#tambahMapelModal">
        <i class="fas fa-plus"></i> Tambah Mata Pelajaran
    </button>

    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Kode</th>
                    <th>Nama Mata Pelajaran</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php 
            $no = 1;
            mysqli_data_seek($result, 0);
            while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td><?= $row['kode']; ?></td>
                    <td><?= $row['nama_mapel']; ?></td>
                    <td>
    <div class="btn-group btn-group-sm" role="group">
        <button class="btn btn-warning text-white" data-bs-toggle="modal" data-bs-target="#editMapelModal<?= $row['id']; ?>">
            <i class="fas fa-edit me-1"></i> Edit
        </button>
        <a href="?hapus=<?= $row['id']; ?>" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus?')">
            <i class="fas fa-trash-alt me-1"></i> Hapus
        </a>
    </div>
</td>

                </tr>

                <!-- Modal Edit -->
                <div class="modal fade" id="editMapelModal<?= $row['id']; ?>" tabindex="-1" aria-labelledby="editMapelModalLabel<?= $row['id']; ?>" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="POST">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Mata Pelajaran</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="id" value="<?= $row['id']; ?>">
                                    <div class="mb-3">
                                        <label>Kode Mata Pelajaran</label>
                                        <input type="text" name="kode" class="form-control" value="<?= $row['kode']; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label>Nama Mata Pelajaran</label>
                                        <input type="text" name="nama_mapel" class="form-control" value="<?= $row['nama_mapel']; ?>" required>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" name="edit" class="btn btn-primary">Simpan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="tambahMapelModal" tabindex="-1" aria-labelledby="tambahMapelModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h5 class="modal-title">Tambah Mata Pelajaran</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label>Kode Mata Pelajaran</label>
            <input type="text" name="kode" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Nama Mata Pelajaran</label>
            <input type="text" name="nama_mapel" class="form-control" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="tambah" class="btn btn-success">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="../assets/js/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php include '../includes/scripts.php'; ?>
</body>
</html>
