<?php
session_start();
require_once '../config/koneksi.php';
require_once '../includes/config.php'; // Tambahkan ini untuk base_url dan fungsi isActive

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

// Proses hapus data
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Cek apakah kelas memiliki siswa
    $query_cek_siswa = "SELECT COUNT(*) as total FROM siswa WHERE id_kelas = '$id'";
    $result_cek_siswa = mysqli_query($koneksi, $query_cek_siswa);
    $row_cek_siswa = mysqli_fetch_assoc($result_cek_siswa);
    
    // Cek apakah kelas memiliki jadwal mengajar
    $query_cek_jadwal = "SELECT COUNT(*) as total FROM jadwal WHERE kelas_id = '$id'";
    $result_cek_jadwal = mysqli_query($koneksi, $query_cek_jadwal);
    $row_cek_jadwal = mysqli_fetch_assoc($result_cek_jadwal);
    
    if ($row_cek_siswa['total'] > 0) {
        header("Location: index.php?status=error&message=Kelas+tidak+dapat+dihapus+karena+masih+memiliki+siswa");
        exit();
    } else if ($row_cek_jadwal['total'] > 0) {
        header("Location: index.php?status=error&message=Kelas+tidak+dapat+dihapus+karena+masih+memiliki+jadwal+mengajar");
        exit();
    } else {
        $query_delete = "DELETE FROM kelas WHERE id = '$id'";
        if (mysqli_query($koneksi, $query_delete)) {
            header("Location: index.php?status=success&message=Data+kelas+berhasil+dihapus");
            exit();
        } else {
            header("Location: index.php?status=error&message=Gagal+menghapus+data+kelas");
            exit();
        }
    }
}

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page > 1) ? ($page * $limit) - $limit : 0;

// Pencarian
$search = isset($_GET['search']) ? $_GET['search'] : '';
$where = "";
if (!empty($search)) {
    $where = "WHERE nama_kelas LIKE '%$search%' OR tingkat LIKE '%$search%' OR jurusan LIKE '%$search%'";
}

// Query untuk mendapatkan total data
$query_count = "SELECT COUNT(*) as total FROM kelas $where";
$result_count = mysqli_query($koneksi, $query_count);
$row_count = mysqli_fetch_assoc($result_count);
$total_data = $row_count['total'];
$total_pages = ceil($total_data / $limit);

// Query untuk mendapatkan data kelas dengan pagination
$query_kelas = "SELECT * FROM kelas $where ORDER BY tingkat ASC, jurusan ASC, nama_kelas ASC LIMIT $start, $limit";
$result_kelas = mysqli_query($koneksi, $query_kelas);

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
    <title>Data Kelas - Sistem Absensi SMKN 4 Tasikmalaya</title>
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
                <h1 class="h3 mb-0 text-gray-800">Data Kelas</h1>
                <a href="tambah.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                    <i class="fas fa-plus fa-sm text-white-50"></i> Tambah Kelas
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
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Daftar Kelas</h6>
                    <form action="" method="GET" class="search-form">
                        <div class="input-group">
                            <input type="text" class="form-control" name="search" placeholder="Cari kelas..." value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="20%">Nama Kelas</th>
                                    <th width="15%">Tingkat</th>
                                    <th width="25%">Jurusan</th>
                                    <th width="15%">Jumlah Siswa</th>
                                    <th width="20%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                if (mysqli_num_rows($result_kelas) > 0):
                                    $no = $start + 1;
                                    while ($kelas = mysqli_fetch_assoc($result_kelas)):
                                        // Hitung jumlah siswa per kelas
                                        $id_kelas = $kelas['id'];
                                        $query_siswa = "SELECT COUNT(*) as total FROM siswa WHERE id_kelas = '$id_kelas'";
                                        $result_siswa = mysqli_query($koneksi, $query_siswa);
                                        $row_siswa = mysqli_fetch_assoc($result_siswa);
                                        $jumlah_siswa = $row_siswa['total'];
                                ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo $kelas['nama_kelas']; ?></td>
                                    <td><?php echo $kelas['tingkat']; ?></td>
                                    <td><?php echo $kelas['jurusan']; ?></td>
                                    <td><?php echo $jumlah_siswa; ?> siswa</td>
                                    <td>
    <div class="btn-group btn-group-sm" role="group">
        <a href="detail.php?id=<?= $kelas['id']; ?>" 
           class="btn btn-info">
            <i class="fas fa-eye me-1"></i> Detail
        </a>
        <a href="edit.php?id=<?= $kelas['id']; ?>" 
           class="btn btn-warning text-white">
            <i class="fas fa-edit me-1"></i> Edit
        </a>
        <a href="index.php?action=delete&id=<?= $kelas['id']; ?>" 
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
                                    <td colspan="6" class="text-center py-4">
                                        <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">Tidak ada data kelas yang ditemukan</p>
                                        <?php if (!empty($search)): ?>
                                            <a href="index.php" class="btn btn-sm btn-primary mt-2">Tampilkan Semua</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div>
                            <p class="text-muted mb-0">Menampilkan <?php echo $start + 1; ?> - <?php echo min($start + $limit, $total_data); ?> dari <?php echo $total_data; ?> data</p>
                        </div>
                        <nav aria-label="Page navigation">
                            <ul class="pagination">
                                <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . $search : ''; ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . $search : ''; ?>"><?php echo $i; ?></a>
                                </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . $search : ''; ?>" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Include Scripts -->
    <?php include '../includes/scripts.php'; ?>
</body>
</html>