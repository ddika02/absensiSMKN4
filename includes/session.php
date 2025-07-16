<?php
// Mulai session
session_start();

// Fungsi untuk mengatur session timeout (30 menit)
function check_session_timeout() {
    $timeout_duration = 1800; // 30 menit dalam detik
    
    // Jika session timeout belum diatur, atur sekarang
    if (!isset($_SESSION['last_activity'])) {
        $_SESSION['last_activity'] = time();
    }
    
    // Cek apakah session sudah timeout
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout_duration)) {
        // Session timeout, logout user
        session_unset();
        session_destroy();
        
        // Redirect ke halaman login dengan pesan
        header("Location: ../index.php?msg=timeout");
        exit();
    }
    
    // Update waktu aktivitas terakhir
    $_SESSION['last_activity'] = time();
}

// Fungsi untuk mengecek akses halaman admin
function require_admin() {
    require_once 'auth.php';
    
    // Cek session timeout
    check_session_timeout();
    
    // Jika user belum login atau bukan admin, redirect ke halaman login
    if (!is_logged_in() || !is_admin()) {
        header("Location: ../index.php?msg=unauthorized");
        exit();
    }
}

// Fungsi untuk mengecek akses halaman guru
function require_guru() {
    require_once 'auth.php';
    
    // Cek session timeout
    check_session_timeout();
    
    // Jika user belum login atau bukan guru, redirect ke halaman login
    if (!is_logged_in() || !is_guru()) {
        header("Location: ../index.php?msg=unauthorized");
        exit();
    }
}

// Fungsi untuk mengecek akses halaman yang memerlukan login
function require_login() {
    require_once 'auth.php';
    
    // Cek session timeout
    check_session_timeout();
    
    // Jika user belum login, redirect ke halaman login
    if (!is_logged_in()) {
        header("Location: ../index.php?msg=login_required");
        exit();
    }
}
?>