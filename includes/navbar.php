<?php
// Pastikan user data tersedia
if (!isset($user)) {
    // Jika variabel tidak tersedia, ambil dari database
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $query = "SELECT * FROM users WHERE id = '$user_id'";
        $result = mysqli_query($koneksi, $query);
        $user = mysqli_fetch_assoc($result);
    }
}

// Gunakan foto profil dari database atau default jika tidak ada
$foto_profil = $user['foto_profil'] ?? 'assets/img/user-default.png';
?>

<!-- Navbar -->
<nav class="navbar navbar-expand navbar-light mb-4">
    <div class="container-fluid">
        <!-- Hapus class d-md-none agar tombol toggle muncul di semua ukuran layar -->
        <button class="btn btn-link" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        
        <ul class="navbar-nav ms-auto">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="user-info">
                        <img src="<?php echo $base_url ?? ''; ?><?php echo $foto_profil; ?>" alt="User">
                        <span><?php echo $user['nama']; ?></span>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                    <li><a class="dropdown-item" href="<?php echo $base_url ?? ''; ?>profil.php"><i class="fas fa-user-circle fa-sm fa-fw me-2 text-gray-400"></i> Profil</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="<?php echo $base_url ?? ''; ?>logout.php" onclick="return confirm('Apakah Anda yakin ingin keluar?')"><i class="fas fa-sign-out-alt fa-sm fa-fw me-2 text-gray-400"></i> Logout</a></li>
                </ul>
            </li>
        </ul>
    </div>
</nav>
