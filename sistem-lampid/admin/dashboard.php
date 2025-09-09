<?php
require_once '../includes/db_connect.php';

// Keamanan: Cek sesi login admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

// Data untuk Insight (Keseluruhan)
$insights = [];
$metrics = ['lahir', 'mati', 'pindah', 'datang'];
foreach ($metrics as $metric) {
    $max_stmt = $db->query("SELECT d.nama_desa, p.$metric, p.bulan, p.tahun FROM data_penduduk p JOIN desa d ON p.id_desa = d.id_desa WHERE p.$metric = (SELECT MAX($metric) FROM data_penduduk) LIMIT 1");
    $insights[$metric]['max'] = $max_stmt->fetch(PDO::FETCH_ASSOC);

    $min_stmt = $db->query("SELECT d.nama_desa, p.$metric, p.bulan, p.tahun FROM data_penduduk p JOIN desa d ON p.id_desa = d.id_desa WHERE p.$metric = (SELECT MIN($metric) FROM data_penduduk WHERE $metric > 0) LIMIT 1");
    $insights[$metric]['min'] = $min_stmt->fetch(PDO::FETCH_ASSOC);
}

// Data untuk Grafik (Akumulasi Keseluruhan)
$chart_data_stmt = $db->query("
    SELECT 
        SUM(penduduk_bulan_lalu) as total_penduduk_lalu, SUM(lahir) as total_lahir, SUM(mati) as total_mati,
        SUM(pindah) as total_pindah, SUM(datang) as total_datang, SUM(penduduk_bulan_ini) as total_penduduk_ini,
        SUM(kartu_keluarga) as total_kk, SUM(ktp) as total_ktp
    FROM data_penduduk
");
$chart_data = $chart_data_stmt->fetch(PDO::FETCH_ASSOC);

// Menyiapkan data untuk Chart.js
$labels = ["Penduduk Lalu", "Lahir", "Mati", "Pindah", "Datang", "Penduduk Kini", "KK", "KTP"];
$data_values = $chart_data ? array_values($chart_data) : array_fill(0, count($labels), 0);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - SISTEM LAMPID</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="main-wrapper">
    <?php include '../includes/header.php'; ?>

    <div class="content">
        <div class="content-header">
            <h1>Dashboard Admin</h1>
            <p>Selamat datang, <?php echo htmlspecialchars($_SESSION['username']); ?>. Berikut adalah ringkasan data kependudukan.</p>
        </div>

        <div class="card">
            <h2>Insight Kependudukan (Keseluruhan)</h2>
            <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                <?php foreach ($metrics as $metric): ?>
                <div style="flex: 1; min-width: 250px; background-color: var(--bg-cool-gray); padding: 15px; border-radius: var(--border-radius-md); border: 1px solid var(--border-color);">
                    <h4 style="margin-top: 0; color: var(--text-dark-slate);"><?php echo ucfirst($metric); ?> Tertinggi</h4>
                    <?php if ($insights[$metric]['max']): ?>
                        <p><strong><?php echo number_format($insights[$metric]['max'][$metric]); ?></strong> di <?php echo $insights[$metric]['max']['nama_desa']; ?><br><small>(<?php echo strftime('%B', mktime(0,0,0,$insights[$metric]['max']['bulan'],10)) . " " . $insights[$metric]['max']['tahun']; ?>)</small></p>
                    <?php else: ?><p>Data tidak ditemukan.</p><?php endif; ?>
                    
                    <h4 style="margin-top: 10px; color: var(--text-dark-slate);"><?php echo ucfirst($metric); ?> Terendah</h4>
                     <?php if ($insights[$metric]['min']): ?>
                        <p><strong><?php echo number_format($insights[$metric]['min'][$metric]); ?></strong> di <?php echo $insights[$metric]['min']['nama_desa']; ?><br><small>(<?php echo strftime('%B', mktime(0,0,0,$insights[$metric]['min']['bulan'],10)) . " " . $insights[$metric]['min']['tahun']; ?>)</small></p>
                    <?php else: ?><p>Data tidak ditemukan.</p><?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="card">
            <h2>Grafik Akumulasi Data Kependudukan (Keseluruhan)</h2>
            <canvas id="populationChart"></canvas>
        </div>
    </div>
</div>

<script>
const ctx = document.getElementById('populationChart').getContext('2d');
const populationChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($labels); ?>,
        datasets: [{
            label: 'Total Akumulasi Data',
            data: <?php echo json_encode($data_values); ?>,
            // WARNA BARU SESUAI TEMA DINGIN
            backgroundColor: 'rgba(99, 102, 241, 0.7)', // Warna --accent-indigo dengan transparansi
            borderColor: 'rgba(99, 102, 241, 1)',   // Warna --accent-indigo solid
            borderWidth: 1,
            borderRadius: 5
        }]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true,
                // WARNA BARU SESUAI TEMA DINGIN
                ticks: { color: '#64748b' }, // Warna --text-secondary-slate
                grid: { color: '#e2e8f0' }   // Warna --border-color
            },
            x: {
                 // WARNA BARU SESUAI TEMA DINGIN
                ticks: { color: '#64748b' }, // Warna --text-secondary-slate
                grid: { display: false }
            }
        },
        plugins: {
            legend: {
                labels: {
                    // WARNA BARU SESUAI TEMA DINGIN
                    color: '#1e293b' // Warna --text-dark-slate
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let label = context.dataset.label || '';
                        if (label) { label += ': '; }
                        if (context.parsed.y !== null) {
                            label += new Intl.NumberFormat('id-ID').format(context.parsed.y);
                        }
                        return label;
                    }
                }
            }
        }
    }
});
</script>

</body>
</html>