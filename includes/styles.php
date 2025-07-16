<style>
    :root {
        --primary-color: #4e73df;
        --secondary-color: #1cc88a;
        --danger-color: #e74a3b;
        --warning-color: #f6c23e;
        --info-color: #36b9cc;
    }
    
    body {
        font-family: 'Nunito', sans-serif;
        background-color: #f8f9fc;
    }
    
    .sidebar {
        min-height: 100vh;
        background: linear-gradient(180deg, var(--primary-color) 0%, #224abe 100%);
        color: white;
        position: fixed;
        width: 250px;
        transition: all 0.3s;
        z-index: 1000;
    }
    
    /* Tambahkan class untuk sidebar yang disembunyikan */
    .sidebar.active {
        margin-left: -250px;
    }
    
    .sidebar-brand {
        height: 70px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        font-weight: 800;
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .sidebar-menu {
        padding: 1rem 0;
    }
    
    .sidebar-menu a {
        display: block;
        padding: 0.8rem 1.5rem;
        color: rgba(255, 255, 255, 0.8);
        text-decoration: none;
        transition: all 0.3s;
    }
    
    .sidebar-menu a:hover, .sidebar-menu a.active {
        color: white;
        background-color: rgba(255, 255, 255, 0.1);
        border-left: 4px solid white;
    }
    
    .sidebar-menu i {
        margin-right: 10px;
        width: 20px;
        text-align: center;
    }
    
    .main-content {
        margin-left: 250px;
        padding: 20px;
        transition: all 0.3s;
    }
    
    /* Tambahkan class untuk main content saat sidebar disembunyikan */
    .main-content.active {
        margin-left: 0;
    }
    
    .navbar {
        background-color: white;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    }
    
    .card {
        border: none;
        border-radius: 0.5rem;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
        margin-bottom: 1.5rem;
    }
    
    .card-header {
        background-color: #f8f9fc;
        border-bottom: 1px solid #e3e6f0;
        font-weight: bold;
    }
    
    .card-stats {
        border-left: 4px solid var(--primary-color);
    }
    
    .card-stats.card-siswa {
        border-left-color: var(--primary-color);
    }
    
    .card-stats.card-guru {
        border-left-color: var(--info-color);
    }
    
    .card-stats.card-kelas {
        border-left-color: var(--warning-color);
    }
    
    .card-stats.card-mapel {
        border-left-color: var(--secondary-color);
    }
    
    .card-stats .icon {
        font-size: 2rem;
        opacity: 0.3;
    }
    
    .table-responsive {
        overflow-x: auto;
    }
    
    .btn-circle {
        border-radius: 100%;
        height: 2.5rem;
        width: 2.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .user-info {
        display: flex;
        align-items: center;
    }
    
    .user-info img {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        margin-right: 10px;
        object-fit: cover;
    }
    
    .img-profile {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        margin: 0 auto 1rem;
        object-fit: cover;
    }
    
    .img-preview {
        max-width: 200px;
        max-height: 200px;
        display: none;
        margin-top: 10px;
        object-fit: cover;
    }
    
    /* Modifikasi media query untuk mobile */
    @media (max-width: 768px) {
        .sidebar {
            margin-left: -250px;
        }
        
        .sidebar.active {
            margin-left: 0;
        }
        
        .main-content {
            margin-left: 0;
        }
        
        .main-content.active {
            margin-left: 250px;
        }
    }
</style>