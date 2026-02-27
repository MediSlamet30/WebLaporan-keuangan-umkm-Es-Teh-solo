<?php
date_default_timezone_set('Asia/Jakarta');

session_start();
if (!isset($_SESSION['user_id']))
    header('Location: index.php');

require 'db.php';

// FILTER RANGE
$start = $_GET['start'] ?? date('Y-m-d', strtotime('-6 days'));
$end = $_GET['end'] ?? date('Y-m-d');

$periodStart = $start . " 00:00:00";
$periodEnd = $end . " 23:59:59";

// TOTAL PENDAPATAN GLOBAL
$qTotal = $mysqli->query("SELECT IFNULL(SUM(total),0) AS total FROM sales");
$totalPendapatan = (int) $qTotal->fetch_assoc()['total'];

// PENDAPATAN PERIODE
$stmt = $mysqli->prepare("
    SELECT IFNULL(SUM(total),0) AS total_period
    FROM sales
    WHERE created_at BETWEEN ? AND ?
");
$stmt->bind_param('ss', $periodStart, $periodEnd);
$stmt->execute();
$res = $stmt->get_result();
$total_period = (int) $res->fetch_assoc()['total_period'];

// TOTAL TRANSAKSI
$total_tx = (int) $mysqli->query("
    SELECT COUNT(*) AS c 
    FROM sales 
    WHERE created_at BETWEEN '$periodStart' AND '$periodEnd'
")->fetch_assoc()['c'];

// GRAFIK DATA
$labels = [];
$data = [];
$data_expense = [];

$period = new DatePeriod(
    new DateTime($start),
    new DateInterval('P1D'),
    (new DateTime($end))->modify('+1 day')
);

foreach ($period as $day) {
    $d = $day->format("Y-m-d");
    $labels[] = $d;

    // Pendapatan per hari
    $stmt = $mysqli->prepare("
        SELECT IFNULL(SUM(total),0) AS t 
        FROM sales 
        WHERE DATE(created_at)=?
    ");
    $stmt->bind_param('s', $d);
    $stmt->execute();
    $r = $stmt->get_result()->fetch_assoc()['t'];
    $data[] = (int) $r;

    // Pengeluaran per hari
    $stmt2 = $mysqli->prepare("
        SELECT IFNULL(SUM(amount),0) AS e 
        FROM expenses 
        WHERE DATE(created_at)=?
    ");
    $stmt2->bind_param('s', $d);
    $stmt2->execute();
    $e = $stmt2->get_result()->fetch_assoc()['e'];
    $data_expense[] = (int) $e;
}

// Produk terlaris
$q1 = "
SELECT p.name, SUM(si.qty) AS total
FROM sale_items si
JOIN products p ON p.id = si.product_id
JOIN sales s ON s.id = si.sale_id
WHERE s.created_at BETWEEN '$periodStart' AND '$periodEnd'
GROUP BY si.product_id
ORDER BY total DESC
LIMIT 5
";
$res = $mysqli->query($q1);

$top_names = [];
$top_totals = [];
while ($row = $res->fetch_assoc()) {
    $top_names[] = $row['name'];
    $top_totals[] = (int) $row['total'];
}
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body,
        h1,
        h2,
        h3,
        p,
        a,
        div,
        span {
            font-family: 'Plus Jakarta Sans', sans-serif !important;
        }

        body {
            background: #f2f4f7;
            margin: 0;
            padding: 0;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }

        .card {
            background: white;
            padding: 16px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .stat-box {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }

        .stat {
            background: linear-gradient(to bottom right, #ffffff, #f0f0f0);
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .stat h3 {
            margin: 0;
            font-size: .95rem;
            color: #555;
        }

        .stat .big {
            margin-top: 8px;
            font-size: 1.8rem;
            font-weight: bold;
        }

        .filter-container {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 18px 20px;
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .filter-container button {
            padding: 8px 16px;
            border: none;
            background: #1d8937;
            color: white;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.2s;
            margin-top: 20px;
        }
    </style>
</head>

<body>

    <?php include 'nav.php'; ?>
    <main class="container">

        <h2>Dashboard</h2>

        <form method="GET" class="filter-container">
            <label>Dari</label>
            <input type="date" name="start" value="<?= $start ?>" required>

            <label>Sampai</label>
            <input type="date" name="end" value="<?= $end ?>" required>

            <button type="submit">Filter</button>
            <a href="dashboard.php" class="reset-btn">Reset</a>
        </form>

        <div class="stat-box">
            <div class="stat">
                <h3>Pendapatan Periode</h3>
                <p class="big">Rp <?= number_format($total_period, 0, ',', '.') ?></p>
            </div>
            <div class="stat">
                <h3>Total Transaksi</h3>
                <p class="big"><?= number_format($total_tx) ?></p>
            </div>
            <div class="stat">
                <h3>Total Penjualan</h3>
                <p class="big">Rp <?= number_format($totalPendapatan, 0, ',', '.') ?></p>
            </div>
        </div>

        <div class="dashboard-grid">
            <div>
                <div class="card">
                    <h3>Grafik Pendapatan & Pengeluaran</h3>
                    <canvas id="combinedChart" height="140"></canvas>
                </div>

                <div class="card" style="margin-top:20px;">
                    <h3>Top 5 Produk Terlaris</h3>
                    <canvas id="topChart" height="130"></canvas>
                </div>
            </div>

            <div>
                <div class="card">
                    <h3>Menu Cepat</h3>
                    <p><a href="input_penjualan.php">Input Penjualan</a></p>
                    <p><a href="menu.php">Produk</a></p>
                    <p><a href="laporan_keuangan.php">Laporan Keuangan</a></p>
                </div>

                <div class="card" style="margin-top:20px;">
                    <h3>Tanggal</h3>
                    <p style="font-size:1.2rem; font-weight:bold;"><?= date('l, d F Y') ?></p>
                </div>
            </div>
        </div>

    </main>

    <script>
        const labels = <?= json_encode($labels) ?>;
        const revenue = <?= json_encode($data) ?>;
        const expense = <?= json_encode($data_expense) ?>;

        // ======================
        //  GRAFIK GABUNGAN
        // ======================
        new Chart(document.getElementById('combinedChart'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: "Pendapatan (Rp)",
                        data: revenue,
                        borderColor: "#1d8937",
                        borderWidth: 3,
                        tension: 0.35
                    },
                    {
                        label: "Pengeluaran (Rp)",
                        data: expense,
                        borderColor: "#d62828",
                        borderWidth: 3,
                        tension: 0.35
                    }
                ]
            }
        });

        // TOP PRODUK
        new Chart(document.getElementById('topChart'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($top_names) ?>,
                datasets: [{ label: "Porsi", data: <?= json_encode($top_totals) ?>, borderWidth: 1 }]
            },
            options: { plugins: { legend: { display: false } } }
        });
    </script>

</body>

</html>
