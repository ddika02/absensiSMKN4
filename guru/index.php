<?php
session_start();
require_once '../config/koneksi.php';
require_once '../includes/config.php'; // Tambahkan ini untuk base_url dan fungsi isActive

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

// Proses hapus data guru
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Cek apakah guru memiliki jadwal mengajar
    $query_check_jadwal = "SELECT COUNT(*) as total FROM jadwal WHERE guru_id = '$id'";
    $result_check_jadwal = mysqli_query($koneksi, $query_check_jadwal);
    $row_check_jadwal = mysqli_fetch_assoc($result_check_jadwal);
    
    if ($row_check_jadwal['total'] > 0) {
        header("Location: index.php?status=error&message=Guru+tidak+dapat+dihapus+karena+memiliki+jadwal+mengajar");
        exit();
    }
    
    // Cek apakah guru memiliki user account
    $query_check_user = "SELECT user_id FROM guru WHERE id = '$id'";
    $result_check_user = mysqli_query($koneksi, $query_check_user);
    $row_check_user = mysqli_fetch_assoc($result_check_user);
    
    if ($row_check_user && $row_check_user['user_id']) {
        $user_id = $row_check_user['user_id'];
        
        // Hapus data guru
        $query_delete_guru = "DELETE FROM guru WHERE id = '$id'";
        $result_delete_guru = mysqli_query($koneksi, $query_delete_guru);
        
        // Hapus user account
        $query_delete_user = "DELETE FROM users WHERE id = '$user_id'";
        $result_delete_user = mysqli_query($koneksi, $query_delete_user);
        
        if ($result_delete_guru && $result_delete_user) {
            header("Location: index.php?status=success&message=Data+guru+berhasil+dihapus");
            exit();
        } else {
            header("Location: index.php?status=error&message=Gagal+menghapus+data+guru");
            exit();
        }
    } else {
        // Hapus data guru saja
        $query_delete = "DELETE FROM guru WHERE id = '$id'";
        $result_delete = mysqli_query($koneksi, $query_delete);
        
        if ($result_delete) {
            header("Location: index.php?status=success&message=Data+guru+berhasil+dihapus");
            exit();
        } else {
            header("Location: index.php?status=error&message=Gagal+menghapus+data+guru");
            exit();
        }
    }
}

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page > 1) ? ($page * $limit) - $limit : 0;

// Search functionality
$search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
$where = "";
if (!empty($search)) {
    $where = "WHERE nip LIKE '%$search%' OR nama LIKE '%$search%'";
}

// Get total records
$query_total = "SELECT COUNT(*) as total FROM guru $where";
$result_total = mysqli_query($koneksi, $query_total);
$row_total = mysqli_fetch_assoc($result_total);
$total = $row_total['total'];

// Calculate total pages
$pages = ceil($total / $limit);

// Get guru data with pagination
$query_guru = "SELECT * FROM guru $where ORDER BY nama ASC LIMIT $start, $limit";
$result_guru = mysqli_query($koneksi, $query_guru);

// Ambil data user untuk navbar
$user_id = $_SESSION['user_id'];
$query_user = "SELECT * FROM users WHERE id = '$user_id'";
$result_user = mysqli_query($koneksi, $query_user);
$user = mysqli_fetch_assoc($result_user);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Guru - Sistem Absensi SMKN 4 Tasikmalaya</title>
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
                <h1 class="h3 mb-0 text-gray-800">Data Guru</h1>
                <a href="tambah.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                    <i class="fas fa-plus fa-sm text-white-50"></i> Tambah Guru
                </a>
            </div>
            
            <!-- Tampilkan pesan sukses/error -->
            <?php if (isset($_GET['status']) && isset($_GET['message'])): ?>
                <div class="alert alert-<?php echo $_GET['status'] == 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                    <?php echo urldecode($_GET['message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <!-- Sisanya tetap sama seperti kode asli -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-wrap justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">Daftar Guru</h6>
                            <div class="search-form">
                                <form action="" method="GET" class="d-flex">
                                    <input type="text" class="form-control" name="search" placeholder="Cari NIP/Nama Guru" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="5%">No</th>
                                            <th width="15%">NIP</th>
                                            <th width="25%">Nama Guru</th>
                                            <th width="15%">Jenis Kelamin</th>
                                            <th width="20%">No. Telepon</th>
                                            <th width="20%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        if (mysqli_num_rows($result_guru) > 0):
                                            $no = $start + 1;
                                            while ($guru = mysqli_fetch_assoc($result_guru)): 
                                        ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td><?php echo $guru['nip']; ?></td>
                                            <td><?php echo $guru['nama']; ?></td>
                                            <td><?php echo $guru['jenis_kelamin']; ?></td>
                                            <td><?php echo !empty($guru['no_telp']) ? $guru['no_telp'] : '-'; ?></td>
                                            <td>
    <div class="btn-group btn-group-sm" role="group">
        <a href="detail.php?id=<?= $guru['id']; ?>" 
           class="btn btn-info">
            <i class="fas fa-eye me-1"></i> Detail
        </a>
        <a href="edit.php?id=<?= $guru['id']; ?>" 
           class="btn btn-warning text-white">
            <i class="fas fa-edit me-1"></i> Edit
        </a>
        <a href="index.php?action=delete&id=<?= $guru['id']; ?>" 
           class="btn btn-danger"
           onclick="return confirm('Yakin ingin menghapus data ini?')">
            <i class="fas fa-trash-alt me-1"></i> Hapus
        </a>
    </div>
</td>

                                        </tr>
                                        <?php 
                                            endwhile; 
                                        else: 
                                        ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-3">Tidak ada data guru</td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination -->
                            <?php if ($pages > 1): ?>
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center mt-3">
                                    <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page-1; ?><?php echo !empty($search) ? '&search='.$search : ''; ?>" aria-label="Previous">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = 1; $i <= $pages; $i++): ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search='.$search : ''; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page+1; ?><?php echo !empty($search) ? '&search='.$search : ''; ?>" aria-label="Next">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                            <?php endif; ?>
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