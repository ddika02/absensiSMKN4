<?php
// Base URL untuk aplikasi
$base_url = '/absensiSMKN4/';

// Fungsi untuk menentukan class active pada menu
function isActive($page, $dir = '') {
    $current_page = basename($_SERVER['PHP_SELF']);
    $current_dir = basename(dirname($_SERVER['PHP_SELF']));
    
    if ($dir !== '' && $current_dir === $dir) {
        return 'active';
    }
    return ($current_page === $page) ? 'active' : '';
}
?>