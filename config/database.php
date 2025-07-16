<?php
// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'db_absensi_smkn4');

// Membuat koneksi database
function connectDB() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Cek koneksi
    if ($conn->connect_error) {
        die("Koneksi database gagal: " . $conn->connect_error);
    }
    
    // Set charset ke utf8
    $conn->set_charset("utf8");
    
    return $conn;
}

// Fungsi untuk mengamankan input
function sanitize($conn, $data) {
    return mysqli_real_escape_string($conn, htmlspecialchars(trim($data)));
}

// Fungsi untuk eksekusi query
function query($sql) {
    $conn = connectDB();
    $result = $conn->query($sql);
    $conn->close();
    return $result;
}

// Fungsi untuk mengambil satu baris data
function fetch_assoc($sql) {
    $conn = connectDB();
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $conn->close();
    return $row;
}

// Fungsi untuk mengambil semua baris data
function fetch_all($sql) {
    $conn = connectDB();
    $result = $conn->query($sql);
    $rows = [];
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
    }
    
    $conn->close();
    return $rows;
}
?>