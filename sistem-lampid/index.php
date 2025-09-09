<?php
require_once 'includes/db_connect.php';

// Cek jika user sudah login, langsung arahkan ke dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: " . ($_SESSION['role'] == 'admin' ? 'admin/dashboard.php' : 'desa/dashboard.php'));
    exit();
}

// Ambil semua info web
$stmt_info = $db->query("SELECT * FROM web_info WHERE id_info = 1");
$info = $stmt_info->fetch(PDO::FETCH_ASSOC);

// Hitung jumlah desa
$stmt_desa = $db->query("SELECT COUNT(*) as total_desa FROM desa");
$jumlah_desa = $stmt_desa->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SISTEM LAMPID KECAMATAN CIBUNGBULANG</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <header class="navbar">
        <div class="navbar-container">
            <div class="navbar-brand">
                <img src="assets/img/logo_cibungbulang.png" alt="Logo">
                <span>SISTEM LAMPID CIBUNGBULANG</span>
            </div>
            <nav class="navbar-nav">
                <a href="login.php" class="btn btn-login">Login</a>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <section class="hero-section">
            <div class="hero-content">
                <h1><?php echo htmlspecialchars($info['judul']); ?></h1>
                <p><?php echo htmlspecialchars($info['deskripsi']); ?></p>
            </div>
            
            <div class="stats-container">
                <h2>Statistik Wilayah</h2>
                <div class="info-stats-grid">
                    <div class="stat-box">
                        <i class="fa-solid fa-map-marked-alt"></i>
                        <div class="stat-text">
                            <span><?php echo htmlspecialchars($info['luas_wilayah']); ?></span>
                            <small>Luas Wilayah</small>
                        </div>
                    </div>
                    <div class="stat-box">
                        <i class="fa-solid fa-users"></i>
                        <div class="stat-text">
                            <span><?php echo number_format($info['jumlah_penduduk'], 0, ',', '.'); ?></span>
                            <small>Jiwa</small>
                        </div>
                    </div>
                    <div class="stat-box">
                        <i class="fa-solid fa-house-flag"></i>
                        <div class="stat-text">
                            <span><?php echo htmlspecialchars($jumlah_desa); ?></span>
                            <small>Desa</small>
                        </div>
                    </div>
                    <div class="stat-box">
                        <i class="fa-solid fa-sitemap"></i>
                        <div class="stat-text">
                            <span><?php echo htmlspecialchars($info['jumlah_rw']); ?></span>
                            <small>RW</small>
                        </div>
                    </div>
                    <div class="stat-box">
                        <i class="fa-solid fa-house-user"></i>
                        <div class="stat-text">
                            <span><?php echo htmlspecialchars($info['jumlah_rt']); ?></span>
                            <small>RT</small>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
    
    <footer class="app-footer">
        <p>&copy; <?php echo date('Y'); ?> Kecamatan Cibungbulang. All Rights Reserved.</p>
    </footer>

</body>
</html>