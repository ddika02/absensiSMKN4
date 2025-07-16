<?php
require_once '../config/database.php';

// Fungsi untuk mendapatkan data user berdasarkan ID
function get_user_by_id($user_id) {
    $conn = connectDB();
    $user_id = sanitize($conn, $user_id);
    
    $sql = "SELECT id, username, nama_lengkap, email, role, foto FROM users WHERE id = '$user_id'";
    $result = $conn->query($sql);
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        $conn->close();
        return $user;
    }
    
    $conn->close();
    return false;
}

// Fungsi untuk mendapatkan semua data guru
function get_all_guru() {
    $sql = "SELECT id, username, nama_lengkap, email, foto FROM users WHERE role = 'guru' ORDER BY nama_lengkap ASC";
    return fetch_all($sql);
}

// Fungsi untuk mendapatkan semua data kelas
function get_all_kelas() {
    $sql = "SELECT k.*, u.nama_lengkap as wali_kelas FROM kelas k 
            LEFT JOIN users u ON k.wali_kelas_id = u.id 
            ORDER BY k.nama_kelas ASC";
    return fetch_all($sql);
}

// Fungsi untuk mendapatkan data kelas berdasarkan ID
function get_kelas_by_id($kelas_id) {
    $conn = connectDB();
    $kelas_id = sanitize($conn, $kelas_id);
    
    $sql = "SELECT k.*, u.nama_lengkap as wali_kelas FROM kelas k 
            LEFT JOIN users u ON k.wali_kelas_id = u.id 
            WHERE k.id = '$kelas_id'";
    
    $result = $conn->query($sql);
    
    if ($result->num_rows == 1) {
        $kelas = $result->fetch_assoc();
        $conn->close();
        return $kelas;
    }
    
    $conn->close();
    return false;
}

// Fungsi untuk mendapatkan semua data siswa
function get_all_siswa() {
    $sql = "SELECT s.*, k.nama_kelas FROM siswa s 
            JOIN kelas k ON s.kelas_id = k.id 
            ORDER BY s.nama_lengkap ASC";
    return fetch_all($sql);
}

// Fungsi untuk mendapatkan data siswa berdasarkan kelas
function get_siswa_by_kelas($kelas_id) {
    $conn = connectDB();
    $kelas_id = sanitize($conn, $kelas_id);
    
    $sql = "SELECT * FROM siswa WHERE kelas_id = '$kelas_id' ORDER BY nama_lengkap ASC";
    $result = $conn->query($sql);
    
    $siswa = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $siswa[] = $row;
        }
    }
    
    $conn->close();
    return $siswa;
}

// Fungsi untuk mendapatkan semua data mata pelajaran
function get_all_mapel() {
    $sql = "SELECT * FROM mata_pelajaran ORDER BY nama_mapel ASC";
    return fetch_all($sql);
}

// Fungsi untuk mendapatkan jadwal mengajar guru
function get_jadwal_guru($guru_id) {
    $conn = connectDB();
    $guru_id = sanitize($conn, $guru_id);
    
    $sql = "SELECT j.*, m.nama_mapel, k.nama_kelas 
            FROM jadwal_mengajar j 
            JOIN mata_pelajaran m ON j.mapel_id = m.id 
            JOIN kelas k ON j.kelas_id = k.id 
            WHERE j.guru_id = '$guru_id' 
            ORDER BY FIELD(j.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'), j.jam_mulai ASC";
    
    return fetch_all($sql);
}

// Fungsi untuk mendapatkan pertemuan berdasarkan jadwal
function get_pertemuan_by_jadwal($jadwal_id) {
    $conn = connectDB();
    $jadwal_id = sanitize($conn, $jadwal_id);
    
    $sql = "SELECT * FROM pertemuan WHERE jadwal_id = '$jadwal_id' ORDER BY pertemuan_ke ASC";
    
    return fetch_all($sql);
}

// Fungsi untuk mendapatkan absensi berdasarkan pertemuan
function get_absensi_by_pertemuan($pertemuan_id) {
    $conn = connectDB();
    $pertemuan_id = sanitize($conn, $pertemuan_id);
    
    $sql = "SELECT a.*, s.nis, s.nama_lengkap 
            FROM absensi a 
            JOIN siswa s ON a.siswa_id = s.id 
            WHERE a.pertemuan_id = '$pertemuan_id' 
            ORDER BY s.nama_lengkap ASC";
    
    return fetch_all($sql);
}

// Fungsi untuk mendapatkan rekap absensi per siswa
function get_rekap_absensi_siswa($jadwal_id, $siswa_id) {
    $conn = connectDB();
    $jadwal_id = sanitize($conn, $jadwal_id);
    $siswa_id = sanitize($conn, $siswa_id);
    
    $sql = "SELECT p.pertemuan_ke, p.tanggal, a.status 
            FROM pertemuan p 
            LEFT JOIN absensi a ON p.id = a.pertemuan_id AND a.siswa_id = '$siswa_id' 
            WHERE p.jadwal_id = '$jadwal_id' 
            ORDER BY p.pertemuan_ke ASC";
    
    return fetch_all($sql);
}

// Fungsi untuk format tanggal Indonesia
function format_tanggal($tanggal) {
    $bulan = array (
        1 => 'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember'
    );
    $split = explode('-', $tanggal);
    return $split[2] . ' ' . $bulan[(int)$split[1]] . ' ' . $split[0];
}

// Fungsi untuk upload file
function upload_file($file, $target_dir, $allowed_types = ['jpg', 'jpeg', 'png']) {
    // Cek apakah direktori ada, jika tidak buat direktori
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_name = $file['name'];
    $file_tmp = $file['tmp_name'];
    $file_size = $file['size'];
    $file_error = $file['error'];
    
    // Cek apakah ada error
    if ($file_error !== 0) {
        return ['status' => false, 'message' => 'Terjadi kesalahan saat upload file.'];
    }
    
    // Cek ukuran file (max 2MB)
    if ($file_size > 2097152) {
        return ['status' => false, 'message' => 'Ukuran file terlalu besar (max 2MB).'];
    }
    
    // Cek ekstensi file
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    if (!in_array($file_ext, $allowed_types)) {
        return ['status' => false, 'message' => 'Tipe file tidak diizinkan.'];
    }
    
    // Generate nama file baru untuk menghindari duplikasi
    $new_file_name = uniqid() . '.' . $file_ext;
    $target_file = $target_dir . $new_file_name;
    
    // Upload file
    if (move_uploaded_file($file_tmp, $target_file)) {
        return ['status' => true, 'file_name' => $new_file_name];
    } else {
        return ['status' => false, 'message' => 'Gagal mengupload file.'];
    }
}
?>