<?php
require_once '../config/database.php';

// Fungsi untuk login
function login($username, $password) {
    $conn = connectDB();
    $username = sanitize($conn, $username);
    
    $sql = "SELECT * FROM users WHERE username = '$username'";
    $result = $conn->query($sql);
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        // Verifikasi password
        if (password_verify($password, $user['password'])) {
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['logged_in'] = true;
            
            $conn->close();
            return true;
        }
    }
    
    $conn->close();
    return false;
}

// Fungsi untuk logout
function logout() {
    // Hapus semua data session
    session_unset();
    session_destroy();
    
    // Redirect ke halaman login
    header("Location: ../index.php");
    exit();
}

// Fungsi untuk cek apakah user sudah login
function is_logged_in() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

// Fungsi untuk cek apakah user adalah admin
function is_admin() {
    return is_logged_in() && $_SESSION['role'] === 'admin';
}

// Fungsi untuk cek apakah user adalah guru
function is_guru() {
    return is_logged_in() && $_SESSION['role'] === 'guru';
}

// Fungsi untuk mengubah password
function change_password($user_id, $old_password, $new_password) {
    $conn = connectDB();
    $user_id = sanitize($conn, $user_id);
    
    // Ambil data user
    $sql = "SELECT password FROM users WHERE id = '$user_id'";
    $result = $conn->query($sql);
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        // Verifikasi password lama
        if (password_verify($old_password, $user['password'])) {
            // Hash password baru
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update password
            $sql = "UPDATE users SET password = '$hashed_password' WHERE id = '$user_id'";
            if ($conn->query($sql) === TRUE) {
                $conn->close();
                return true;
            }
        }
    }
    
    $conn->close();
    return false;
}
?>