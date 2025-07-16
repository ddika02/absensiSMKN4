<?php
session_start();
require_once 'config/koneksi.php';

// Cek apakah user sudah login
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Inisialisasi variabel
$error = "";
$username = "";

// Proses login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];
    
    // Validasi input
    if (empty($username) || empty($password)) {
        $error = "Username dan password harus diisi!";
    } else {
        // Cek user di database
        $query = "SELECT * FROM users WHERE username = '$username'";
        $result = mysqli_query($koneksi, $query);
    
        if (mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);
            
            // Verifikasi password
            if (password_verify($password, $user['password'])) {
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nama'] = $user['nama'];
                $_SESSION['role'] = $user['role'];
    
                // Redirect ke dashboard
                header("Location: dashboard.php");
                exit();
            } else {
                // Tambahkan debugging untuk password
                error_log("Password verification failed for user: $username");
                $error = "Password salah!";
            }
        } else {
            $error = "Username tidak ditemukan!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Absensi SMKN 4 Tasikmalaya</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #1cc88a;
            --dark-color: #5a5c69;
            --light-color: #f8f9fc;
        }
        
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8f9fc;
            margin: 0;
            padding: 0;
            height: 100vh;
            width: 100vw;
            overflow-x: hidden;
        }
        
        .container {
            max-width: 100%;
            padding: 0;
            margin: 0;
            height: 100vh;
        }
        
        .row {
            margin: 0;
            height: 100%;
        }
        
        .col-xl-10, .col-lg-12, .col-md-9 {
            padding: 0;
            max-width: 100%;
            flex: 0 0 100%;
        }
        
        .card {
            border: none;
            border-radius: 0;
            box-shadow: none;
            margin: 0 !important;
            height: 100%;
        }
        
        .card-body {
            padding: 0;
            height: 100%;
        }
        
        .row > div {
            padding: 0;
        }
        
        .col-lg-6 {
            height: 100vh;
        }
        
        .bg-login-image img {
            width: 100%;
            height: 100vh;
            object-fit: cover;
        }
        
        .p-5 {
            display: flex;
            flex-direction: column;
            justify-content: center;
            height: 100%;
            padding: 2rem !important;
        }
        
        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid #e3e6f0;
            text-align: center;
            padding: 2rem 1rem;
        }
        
        .card-header img {
            max-width: 80px;
            margin-bottom: 1rem;
        }
        
        .card-header h4 {
            color: var(--dark-color);
            font-weight: 700;
            margin-bottom: 0;
        }
        
        .card-body {
            padding: 2rem;
        }
        
        .form-control {
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            border: 1px solid #d1d3e2;
            font-size: 0.9rem;
        }
        
        .form-control:focus {
            border-color: #bac8f3;
            box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.25);
        }
        
        .input-group-text {
            background-color: var(--light-color);
            border: 1px solid #d1d3e2;
            border-radius: 0.5rem 0 0 0.5rem;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 0.75rem;
            font-weight: 600;
            border-radius: 0.5rem;
        }
        
        .btn-primary:hover {
            background-color: #2e59d9;
            border-color: #2653d4;
        }
        
        .alert {
            border-radius: 0.5rem;
            font-size: 0.9rem;
        }
        
        .footer {
            text-align: center;
            color: white;
            margin-top: 2rem;
            font-size: 0.9rem;
        }
        
        .footer a {
            color: white;
            text-decoration: none;
            font-weight: 600;
        }
        
        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-10 col-lg-12 col-md-9">
                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <div class="row">
                            <div class="col-lg-6 d-none d-lg-block bg-login-image">
                                <img src="assets/img/login-bg.jpeg" alt="Login Background" class="img-fluid h-100" style="object-fit: cover;">
                            </div>
                            <div class="col-lg-6">
                                <div class="p-5">
                                    <div class="text-center mb-4">
                                        <img src="assets/img/logo-smkn4.jpg" alt="Logo SMKN 4 Tasikmalaya" width="80">
                                        <h1 class="h4 text-gray-900 mt-3">Sistem Absensi</h1>
                                        <h2 class="h5 text-primary font-weight-bold">SMKN 4 TASIKMALAYA</h2>
                                    </div>
                                    
                                    <?php if (!empty($error)): ?>
                                    <div class="alert alert-danger" role="alert">
                                        <i class="fas fa-exclamation-triangle me-2"></i> <?php echo $error; ?>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <form class="user" method="POST" action="">
                                        <div class="mb-3">
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                                <input type="text" class="form-control" id="username" name="username" placeholder="Username" value="<?php echo $username; ?>" required>
                                            </div>
                                        </div>
                                        <div class="mb-4">
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                                <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="fas fa-sign-in-alt me-2"></i> Login
                                        </button>
                                    </form>
                                    <hr>
                                    <div class="text-center mt-3">
                                        <small class="text-muted">
                                            &copy; <?php echo date('Y'); ?> SMKN 4 Tasikmalaya<br>
                                            Sistem Absensi Siswa
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>