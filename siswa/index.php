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

// Proses hapus data
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $query_delete = "DELETE FROM siswa WHERE id = '$id'";
    $result_delete = mysqli_query($koneksi, $query_delete);
    
    if ($result_delete) {
        header("Location: index.php?status=success&message=Data+siswa+berhasil+dihapus");
    } else {
        header("Location: index.php?status=error&message=Gagal+menghapus+data+siswa");
    }
    exit();
}

// Ambil data kelas untuk filter
$query_kelas = "SELECT * FROM kelas ORDER BY nama_kelas ASC";
$result_kelas = mysqli_query($koneksi, $query_kelas);

// Filter data berdasarkan kelas
$filter_kelas = isset($_GET['kelas']) ? $_GET['kelas'] : '';
$where_kelas = $filter_kelas != '' ? "WHERE s.id_kelas = '$filter_kelas'" : "";

// Pencarian data
$search = isset($_GET['search']) ? $_GET['search'] : '';
if ($search != '') {
    $where_kelas = $where_kelas == '' ? "WHERE " : $where_kelas . " AND ";
    $where_kelas .= "(s.nis LIKE '%$search%' OR s.nama_siswa LIKE '%$search%')";
}

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Query untuk menghitung total data
$query_count = "SELECT COUNT(*) as total FROM siswa s $where_kelas";
$result_count = mysqli_query($koneksi, $query_count);
$row_count = mysqli_fetch_assoc($result_count);
$total_data = $row_count['total'];
$total_pages = ceil($total_data / $limit);

// Query untuk mengambil data siswa dengan join kelas
$query_siswa = "SELECT s.*, k.nama_kelas 
                FROM siswa s 
                LEFT JOIN kelas k ON s.id_kelas = k.id 
                $where_kelas 
                ORDER BY s.nama_siswa ASC 
                LIMIT $start, $limit";
$result_siswa = mysqli_query($koneksi, $query_siswa);

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
    <title>Data Siswa - Sistem Absensi SMKN 4 Tasikmalaya</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
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
                <h1 class="h3 mb-0 text-gray-800">Data Siswa</h1>
                <a href="tambah.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                    <i class="fas fa-plus fa-sm text-white-50"></i> Tambah Siswa
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
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Daftar Siswa</h6>
                        </div>
                        <div class="card-body">
                            <!-- Filter dan Pencarian -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <form action="" method="GET" class="form-inline">
                                        <div class="input-group">
                                            <select name="kelas" class="form-select" onchange="this.form.submit()">
                                                <option value="">Semua Kelas</option>
                                                <?php while ($kelas = mysqli_fetch_assoc($result_kelas)): ?>
                                                    <option value="<?php echo $kelas['id']; ?>" <?php echo $filter_kelas == $kelas['id'] ? 'selected' : ''; ?>>
                                                        <?php echo $kelas['nama_kelas']; ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                    </form>
                                </div>
                                <div class="col-md-6">
                                    <form action="" method="GET" class="form-inline">
                                        <?php if ($filter_kelas): ?>
                                            <input type="hidden" name="kelas" value="<?php echo $filter_kelas; ?>">
                                        <?php endif; ?>
                                        <div class="input-group">
                                            <input type="text" name="search" class="form-control" placeholder="Cari NIS atau Nama..." value="<?php echo $search; ?>">
                                            <button class="btn btn-primary" type="submit">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Tabel Data Siswa -->
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th width="5%">No</th>
                                            <th width="15%">NIS</th>
                                            <th width="30%">Nama Siswa</th>
                                            <th width="10%">Jenis Kelamin</th>
                                            <th width="20%">Kelas</th>
                                            <th width="20%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        if (mysqli_num_rows($result_siswa) > 0):
                                            $no = $start + 1;
                                            while ($siswa = mysqli_fetch_assoc($result_siswa)): 
                                        ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td><?php echo $siswa['nis']; ?></td>
                                            <td><?php echo $siswa['nama_siswa']; ?></td>
                                            <td><?php echo $siswa['jenis_kelamin']; ?></td>
                                            <td><?php echo $siswa['nama_kelas']; ?></td>
                                            <td>
    <div class="btn-group btn-group-sm" role="group">
        <a href="detail.php?id=<?= $siswa['id']; ?>" 
           class="btn btn-info">
            <i class="fas fa-eye me-1"></i> Detail
        </a>
        <a href="edit.php?id=<?= $siswa['id']; ?>" 
           class="btn btn-warning text-white">
            <i class="fas fa-edit me-1"></i> Edit
        </a>
        <a href="index.php?action=delete&id=<?= $siswa['id']; ?>" 
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
                                            <td colspan="6" class="text-center">Tidak ada data siswa</td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <?php if ($total_pages > 1): ?>
                            <div class="d-flex justify-content-end mt-3">
                                <nav aria-label="Page navigation">
                                    <ul class="pagination">
                                        <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page-1; ?><?php echo $filter_kelas ? '&kelas='.$filter_kelas : ''; ?><?php echo $search ? '&search='.$search : ''; ?>" aria-label="Previous">
                                                <span aria-hidden="true">&laquo;</span>
                                            </a>
                                        </li>
                                        <?php endif; ?>
                                        
                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo $filter_kelas ? '&kelas='.$filter_kelas : ''; ?><?php echo $search ? '&search='.$search : ''; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                        <?php endfor; ?>
                                        
                                        <?php if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page+1; ?><?php echo $filter_kelas ? '&kelas='.$filter_kelas : ''; ?><?php echo $search ? '&search='.$search : ''; ?>" aria-label="Next">
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
        </div>
    </div>
    
    <!-- Include Scripts -->
    <?php include '../includes/scripts.php'; ?>
    

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
</body>
</html>