<?php
require_once 'config/koneksi.php';

// Username admin yang ingin direset passwordnya
$admin_username = 'dika@gmail.com';
// Password baru (dalam plaintext)
$new_password = 'dika123';

// Hash password
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

// Update password di database
$query = "UPDATE users SET password = '$hashed_password' WHERE username = '$admin_username'";
$result = mysqli_query($koneksi, $query);

if ($result) {
    echo "Password untuk user $admin_username berhasil direset!";
} else {
    echo "Gagal mereset password: " . mysqli_error($koneksi);
}
?>