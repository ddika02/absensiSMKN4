<?php
session_start();
require_once '../config/koneksi.php';
require_once '../includes/config.php';

// Cek login dan role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$query = mysqli_query($koneksi, "SELECT * FROM users WHERE id = '$user_id'");
$user = mysqli_fetch_assoc($query);

// Tambah user
if (isset($_POST['tambah'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = mysqli_real_escape_string($koneksi, $_POST['role']);

    $cek = mysqli_query($koneksi, "SELECT * FROM users WHERE username = '$username'");
    if (mysqli_num_rows($cek) > 0) {
        $error = "Username sudah digunakan!";
    } else {
        $result = mysqli_query($koneksi, "INSERT INTO users (nama, username, password, role) VALUES ('$nama', '$username', '$password', '$role')");
        $success = $result ? "User berhasil ditambahkan!" : "Gagal menambahkan user.";
    }
}

// Edit user
if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $role = mysqli_real_escape_string($koneksi, $_POST['role']);

    $cek = mysqli_query($koneksi, "SELECT * FROM users WHERE username = '$username' AND id != '$id'");
    if (mysqli_num_rows($cek) > 0) {
        $error = "Username sudah digunakan oleh user lain!";
    } else {
        $result = mysqli_query($koneksi, "UPDATE users SET nama='$nama', username='$username', role='$role' WHERE id='$id'");
        $success = $result ? "User berhasil diperbarui!" : "Gagal memperbarui user.";
    }
}

// Hapus user
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    if ($id == $user_id) {
        $error = "Tidak dapat menghapus user yang sedang aktif!";
    } else {
        $result = mysqli_query($koneksi, "DELETE FROM users WHERE id='$id'");
        $success = $result ? "User berhasil dihapus!" : "Gagal menghapus user.";
    }
}

// Reset password
if (isset($_GET['reset'])) {
    $id = $_GET['reset'];
    $default_password = password_hash("password123", PASSWORD_DEFAULT);
    $result = mysqli_query($koneksi, "UPDATE users SET password='$default_password' WHERE id='$id'");
    $success = $result ? "Password berhasil direset menjadi 'password123'!" : "Gagal mereset password.";
}

$result_users = mysqli_query($koneksi, "SELECT * FROM users ORDER BY role, nama");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <?php include '../includes/styles.php'; ?>
</head>
<body>
<?php include '../includes/sidebar.php'; ?>
<div class="main-content" id="content">
    <?php include '../includes/navbar.php'; ?>
    <div class="container-fluid">
        <h3 class="mb-4">Manajemen User</h3>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#tambahUserModal">Tambah User</button>

        <table class="table table-bordered">
            <thead>
                <tr><th>No</th><th>Nama</th><th>Username</th><th>Role</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                <?php $no = 1; while ($row = mysqli_fetch_assoc($result_users)): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= $row['nama'] ?></td>
                    <td><?= $row['username'] ?></td>
                    <td><span class="badge <?= $row['role'] == 'admin' ? 'bg-danger' : 'bg-primary' ?>"><?= ucfirst($row['role']) ?></span></td>
                    <td>
    <div class="btn-group btn-group-sm" role="group">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editUserModal<?= $row['id'] ?>">
            <i class="fas fa-edit me-1"></i> Edit
        </button>
        <a href="?reset=<?= $row['id'] ?>" class="btn btn-warning" onclick="return confirm('Reset password user ini?')">
            <i class="fas fa-redo me-1"></i> Reset
        </a>
        <?php if ($row['id'] != $user_id): ?>
            <a href="?hapus=<?= $row['id'] ?>" class="btn btn-danger" onclick="return confirm('Hapus user ini?')">
                <i class="fas fa-trash me-1"></i> Hapus
            </a>
        <?php endif; ?>
    </div>
</td>

                </tr>

                <!-- Modal Edit -->
                <div class="modal fade" id="editUserModal<?= $row['id'] ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="POST">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit User</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <div class="mb-3">
                                        <label>Nama</label>
                                        <input type="text" name="nama" class="form-control" value="<?= $row['nama'] ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label>Username</label>
                                        <input type="text" name="username" class="form-control" value="<?= $row['username'] ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label>Role</label>
                                        <select name="role" class="form-select" required>
                                            <option value="admin" <?= $row['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                                            <option value="guru" <?= $row['role'] == 'guru' ? 'selected' : '' ?>>Guru</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" name="edit" class="btn btn-primary">Simpan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Modal Tambah -->
        <div class="modal fade" id="tambahUserModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST">
                        <div class="modal-header">
                            <h5 class="modal-title">Tambah User</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label>Nama</label>
                                <input type="text" name="nama" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Username</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Role</label>
                                <select name="role" class="form-select" required>
                                    <option disabled selected value="">Pilih Role</option>
                                    <option value="admin">Admin</option>
                                    <option value="guru">Guru</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" name="tambah" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('sidebarToggle')?.addEventListener('click', () => {
        document.querySelector('.sidebar')?.classList.toggle('active');
        document.querySelector('.main-content')?.classList.toggle('active');
    });
</script>
</body>
</html>
