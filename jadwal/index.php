<?php
session_start();
require_once '../config/koneksi.php';
require_once '../includes/config.php'; // Tambahkan ini untuk base_url dan fungsi isActive

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Ambil data user yang login
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$query = "SELECT * FROM users WHERE id = '$user_id'";
$result = mysqli_query($koneksi, $query);
$user = mysqli_fetch_assoc($result);

// Filter
$filter_hari = isset($_GET['hari']) ? $_GET['hari'] : '';
$filter_kelas = isset($_GET['kelas']) ? $_GET['kelas'] : '';

// Query untuk jadwal
if ($role == 'admin') {
    // Untuk admin
    $query_jadwal = "SELECT j.*, g.nama as nama_guru, mp.nama_mapel, k.nama_kelas, k.tahun_ajaran, k.semester 
                    FROM jadwal j
                    JOIN guru g ON j.guru_id = g.id
                    JOIN mapel mp ON j.mapel_id = mp.id
                    JOIN kelas k ON j.kelas_id = k.id";
    
    // Tambahkan filter jika ada
    if (!empty($filter_hari)) {
        $query_jadwal .= " WHERE j.hari = '$filter_hari'";
    }
    
    if (!empty($filter_kelas)) {
        if (strpos($query_jadwal, 'WHERE') !== false) {
            $query_jadwal .= " AND j.kelas_id = '$filter_kelas'";
        } else {
            $query_jadwal .= " WHERE j.kelas_id = '$filter_kelas'";
        }
    }
    
    $query_jadwal .= " ORDER BY FIELD(j.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'), j.jam_mulai ASC";
} else { // Guru
    // Ambil data guru berdasarkan username (NIP) atau nama
    $nip = $user['username'];
    $nama_guru = $user['nama'];
    
    $query_guru = "SELECT id FROM guru WHERE nip = '$nip' OR nama = '$nama_guru'";
    $result_guru = mysqli_query($koneksi, $query_guru);
    
    if ($result_guru && mysqli_num_rows($result_guru) > 0) {
        $guru_data = mysqli_fetch_assoc($result_guru);
        $guru_id = $guru_data['id'];
        
        $query_jadwal = "SELECT j.*, mp.nama_mapel, k.nama_kelas, k.tahun_ajaran, k.semester 
                        FROM jadwal j
                        JOIN mapel mp ON j.mapel_id = mp.id
                        JOIN kelas k ON j.kelas_id = k.id
                        WHERE j.guru_id = '$guru_id'";
        
        // Tambahkan filter jika ada
        if (!empty($filter_hari)) {
            $query_jadwal .= " AND j.hari = '$filter_hari'";
        }
        
        if (!empty($filter_kelas)) {
            $query_jadwal .= " AND j.kelas_id = '$filter_kelas'";
        }
        
        $query_jadwal .= " ORDER BY FIELD(j.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'), j.jam_mulai ASC";
    } else {
        // Handle jika guru tidak ditemukan
        $query_jadwal = "SELECT 1 WHERE 0"; // Query kosong
    }
}

$result_jadwal = mysqli_query($koneksi, $query_jadwal);

// Ambil daftar kelas untuk filter
$query_kelas = "SELECT id, nama_kelas FROM kelas ORDER BY nama_kelas ASC";
$result_kelas = mysqli_query($koneksi, $query_kelas);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Pelajaran - Sistem Absensi SMKN 4 Tasikmalaya</title>
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
                <h1 class="h3 mb-0 text-gray-800">
                    <?php echo $role == 'admin' ? 'Jadwal Pelajaran' : 'Jadwal Mengajar'; ?>
                </h1>
                <?php if ($role == 'admin'): ?>
                <a href="tambah.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                    <i class="fas fa-plus fa-sm text-white-50"></i> Tambah Jadwal
                </a>
                <?php endif; ?>
            </div>
            
            <!-- Tampilkan pesan sukses/error -->
            <?php if (isset($_GET['status']) && isset($_GET['message'])): ?>
                <div class="alert alert-<?php echo $_GET['status'] == 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                    <?php echo urldecode($_GET['message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <!-- Sisanya tetap sama seperti kode asli -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Filter Jadwal</h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="">
                        <div class="row">
                            <div class="col-md-5 mb-3">
                                <label for="hari">Hari</label>
                                <select name="hari" id="hari" class="form-select">
                                    <option value="">Semua Hari</option>
                                    <option value="Senin" <?php echo ($filter_hari == 'Senin') ? 'selected' : ''; ?>>Senin</option>
                                    <option value="Selasa" <?php echo ($filter_hari == 'Selasa') ? 'selected' : ''; ?>>Selasa</option>
                                    <option value="Rabu" <?php echo ($filter_hari == 'Rabu') ? 'selected' : ''; ?>>Rabu</option>
                                    <option value="Kamis" <?php echo ($filter_hari == 'Kamis') ? 'selected' : ''; ?>>Kamis</option>
                                    <option value="Jumat" <?php echo ($filter_hari == 'Jumat') ? 'selected' : ''; ?>>Jumat</option>
                                    <option value="Sabtu" <?php echo ($filter_hari == 'Sabtu') ? 'selected' : ''; ?>>Sabtu</option>
                                </select>
                            </div>
                            <div class="col-md-5 mb-3">
                                <label for="kelas">Kelas</label>
                                <select name="kelas" id="kelas" class="form-select">
                                    <option value="">Semua Kelas</option>
                                    <?php while ($kelas = mysqli_fetch_assoc($result_kelas)): ?>
                                    <option value="<?php echo $kelas['id']; ?>" <?php echo ($filter_kelas == $kelas['id']) ? 'selected' : ''; ?>>
                                        <?php echo $kelas['nama_kelas']; ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-2 mb-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">Filter</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Jadwal Table -->
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Daftar Jadwal Mengajar</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Hari</th>
                                    <th>Jam</th>
                                    <?php if ($role == 'admin'): ?>
                                    <th>Guru</th>
                                    <?php endif; ?>
                                    <th>Mata Pelajaran</th>
                                    <th>Kelas</th>
                                    <th>Tahun Ajaran</th>
                                    <th>Semester</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                if (mysqli_num_rows($result_jadwal) > 0) {
                                    while ($jadwal = mysqli_fetch_assoc($result_jadwal)) {
                                        echo "<tr>";
                                        echo "<td>" . $no++ . "</td>";
                                        echo "<td>" . $jadwal['hari'] . "</td>";
                                        echo "<td>" . $jadwal['jam_mulai'] . " - " . $jadwal['jam_selesai'] . "</td>";
                                        if ($role == 'admin') {
                                            echo "<td>" . $jadwal['nama_guru'] . "</td>";
                                        }
                                        echo "<td>" . $jadwal['nama_mapel'] . "</td>";
                                        echo "<td>" . $jadwal['nama_kelas'] . "</td>";
                                        echo "<td>" . $jadwal['tahun_ajaran'] . "</td>";
                                        echo "<td>" . $jadwal['semester'] . "</td>";
                                        echo "<td>";
                                        if ($role == 'guru') {
                                            echo "<a href='../absensi/form.php?id_jadwal=" . $jadwal['id'] . "' class='btn btn-sm btn-primary mb-1'><i class='fas fa-clipboard-list'></i> Absensi</a>";
                                        }
                                        if ($role == 'admin') {
                                            echo "<a href='edit.php?id=" . $jadwal['id'] . "' class='btn btn-sm btn-warning mb-1'><i class='fas fa-edit'></i> Edit</a> ";
                                            echo "<a href='hapus.php?id=" . $jadwal['id'] . "' class='btn btn-sm btn-danger mb-1' onclick=\"return confirm('Apakah Anda yakin ingin menghapus jadwal ini?')\"><i class='fas fa-trash'></i> Hapus</a>";
                                        }
                                        echo "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='" . ($role == 'admin' ? '7' : '6') . "' class='text-center'>Tidak ada data jadwal</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Include Scripts -->
    <?php include '../includes/scripts.php'; ?>
</body>
</html>