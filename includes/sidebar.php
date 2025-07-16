<?php
// Pastikan session sudah dimulai di file utama
if (!isset($user_id) || !isset($role)) {
    // Jika variabel tidak tersedia, ambil dari session
    $user_id = $_SESSION['user_id'] ?? null;
    $role = $_SESSION['role'] ?? null;
}

// Tentukan halaman aktif
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

// Fungsi isActive() sudah dideklarasikan di config.php
// Tidak perlu dideklarasikan lagi di sini
?>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <i class="fas fa-school me-2"></i>
        <span>ABSENSI SMKN 4</span>
    </div>
    <div class="sidebar-menu">
        <a href="<?php echo $base_url ?? ''; ?>dashboard.php" class="<?php echo isActive('dashboard.php'); ?>">
            <i class="fas fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
        
        <?php if ($role == 'admin'): ?>
        <a href="<?php echo $base_url ?? ''; ?>siswa/index.php" class="<?php echo isActive('index.php', 'siswa'); ?>">
            <i class="fas fa-user-graduate"></i>
            <span>Data Siswa</span>
        </a>
        <a href="<?php echo $base_url ?? ''; ?>guru/index.php" class="<?php echo isActive('index.php', 'guru'); ?>">
            <i class="fas fa-chalkboard-teacher"></i>
            <span>Data Guru</span>
        </a>
        <a href="<?php echo $base_url ?? ''; ?>kelas/index.php" class="<?php echo isActive('index.php', 'kelas'); ?>">
            <i class="fas fa-school"></i>
            <span>Data Kelas</span>
        </a>
        <a href="<?php echo $base_url ?? ''; ?>mapel/index.php" class="<?php echo isActive('index.php', 'mapel'); ?>">
            <i class="fas fa-book"></i>
            <span>Mata Pelajaran</span>
        </a>
        <a href="<?php echo $base_url ?? ''; ?>jadwal/index.php" class="<?php echo isActive('index.php', 'jadwal'); ?>">
            <i class="fas fa-calendar-alt"></i>
            <span>Jadwal Pelajaran</span>
        </a>
        <a href="<?php echo $base_url ?? ''; ?>users/index.php" class="<?php echo isActive('index.php', 'users'); ?>">
            <i class="fas fa-users-cog"></i>
            <span>Manajemen User</span>
        </a>
        <a href="<?php echo $base_url ?? ''; ?>laporan/admin.php" class="<?php echo isActive('admin.php', 'laporan'); ?>">
            <i class="fas fa-file-alt"></i>
            <span>Laporan Absensi</span>
        </a>
        <?php endif; ?>
        
        <?php if ($role == 'guru'): ?>
        <a href="<?php echo $base_url ?? ''; ?>jadwal/index.php" class="<?php echo isActive('index.php', 'jadwal'); ?>">
            <i class="fas fa-calendar-alt"></i>
            <span>Jadwal Mengajar</span>
        </a>
        <a href="<?php echo $base_url ?? ''; ?>absensi/index.php" class="<?php echo isActive('index.php', 'absensi'); ?>">
            <i class="fas fa-clipboard-list"></i>
            <span>Absensi Siswa</span>
        </a>
        <a href="<?php echo $base_url ?? ''; ?>laporan/index.php" class="<?php echo isActive('index.php', 'laporan'); ?>">
            <i class="fas fa-file-alt"></i>
            <span>Laporan Absensi</span>
        </a>
        <?php endif; ?>
        
        <a href="<?php echo $base_url ?? ''; ?>profil.php" class="<?php echo isActive('profil.php'); ?>">
            <i class="fas fa-user-circle"></i>
            <span>Profil</span>
        </a>
        <a href="<?php echo $base_url ?? ''; ?>logout.php" onclick="return confirm('Apakah Anda yakin ingin keluar?')">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</div>