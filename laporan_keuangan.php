<?php
// laporan_keuangan.php
session_start();
if (!isset($_SESSION['user_id']))
    header('Location:index.php');
require 'db.php';

/* TOTAL Pendapatan (sales) */
$stmt = $mysqli->prepare("SELECT IFNULL(SUM(total),0) AS t FROM sales");
$stmt->execute();
$pendapatan = $stmt->get_result()->fetch_assoc()['t'];
$stmt->close();

/* TOTAL Pengeluaran (expenses / pengeluaran) */
$stmt = $mysqli->prepare("SELECT IFNULL(SUM(amount),0) AS t FROM expenses");
$stmt->execute();
$pengeluaran = $stmt->get_result()->fetch_assoc()['t'];
$stmt->close();

$saldo = $pendapatan - $pengeluaran;
// =======================
// FILTER BULAN & TAHUN
// =======================
$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');


/* Ambil riwayat gabungan (sales + expenses) tanpa periode */
$sql = "
    SELECT id, created_at AS tanggal, 'Penjualan' AS tipe, CONCAT('Penjualan #', id) AS keterangan, total AS nominal
    FROM sales
    WHERE MONTH(created_at) = '$bulan' AND YEAR(created_at) = '$tahun'
    
    UNION ALL
    
    SELECT id, created_at AS tanggal, 'Pengeluaran' AS tipe, note AS keterangan, amount AS nominal
    FROM expenses
    WHERE MONTH(created_at) = '$bulan' AND YEAR(created_at) = '$tahun'  
    ORDER BY tanggal DESC
";
$riwayat = $mysqli->query($sql);
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Laporan Keuangan</title>
    <link rel="stylesheet" href="style.css">

    <style>
        :root {
            --primary: #0ea5e9;
            --card-bg: #ffffff;
            --page-bg: #f3f6fb;
            --muted: #64748b;
            --shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
            --radius: 12px;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
            background: var(--page-bg);
            margin: 0;
        }

        .wrap {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
        }

        /* Kartu ringkasan */
        .cards {
            display: flex;
            gap: 20px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }

        .card {
            flex: 1;
            min-width: 200px;
            background: #ffffff;
            /* semua kartu warna sama */
            border-radius: var(--radius);
            padding: 22px;
            box-shadow: var(--shadow);
            text-align: center;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.1);
        }

        .card .title {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .card .value {
            font-size: 24px;
            font-weight: 800;
            margin-top: 8px;
            color: #111827;
        }

        /* Toolbar */
        .toolbar {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-bottom: 20px;
        }

        .btn {
            padding: 12px 20px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 700;
            font-size: 14px;
            transition: all 0.2s;
        }

        .btn-primary {
            background: var(--primary);
            color: #fff;
            line-height: 1.2;
            padding: 4px 12px;
            margin-top: 28px;
        }

        .btn-primary:hover {
            background: #0c87d2;
        }

        /* Tabel riwayat */
        .box {
            background: var(--card-bg);
            padding: 18px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            min-width: 600px;
        }

        th,
        td {
            padding: 14px 12px;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
        }

        thead th {
            background: #f1f5f9;
            color: #111827;
            font-weight: 700;
            text-transform: uppercase;
        }

        tbody tr:hover {
            background: #f0f9ff;
        }

        .tag-sale {
            display: inline-block;
            background: #d1fae5;
            color: #065f46;
            padding: 6px 12px;
            border-radius: 999px;
            font-weight: 700;
            font-size: 13px;
        }

        .tag-exp {
            display: inline-block;
            background: #fee2e2;
            color: #b91c1c;
            padding: 6px 12px;
            border-radius: 999px;
            font-weight: 700;
            font-size: 13px;
        }

        /* Responsive */
        @media(max-width:900px) {
            .cards {
                flex-direction: column;
            }

            .toolbar {
                justify-content: center;
            }
        }

        /* Print only: tampilkan wrap + kartu ringkasan + tabel, sembunyikan nav & toolbar */
        @media print {
            body * {
                visibility: hidden;
            }

            .wrap,
            .wrap * {
                visibility: visible;
            }

            .wrap {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                padding: 0;
            }

            .toolbar {
                display: none;
            }

            nav {
                display: none;
            }

            .card,
            .box {
                box-shadow: none;
                background: #ffffff;
            }

            table {
                font-size: 12pt;
            }
        }
    </style>

</head>

<body>

    <?php include 'nav.php'; ?>

    <div class="wrap">

        <!-- Kartu ringkasan -->
        <div class="cards">
            <div class="card">
                <div class="title">PEMASUKAN</div>
                <div class="value">Rp <?= number_format($pendapatan, 0, ',', '.') ?></div>
            </div>
            <div class="card">
                <div class="title">PENGELUARAN</div>
                <div class="value">Rp <?= number_format($pengeluaran, 0, ',', '.') ?></div>
            </div>
            <div class="card">
                <div class="title">LABA</div>
                <div class="value">Rp <?= number_format($saldo, 0, ',', '.') ?></div>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="toolbar">
            <button class="btn btn-primary" onclick="window.print()">🖨 Cetak / Simpan PDF</button>
        </div>

        <!-- Filter -->
        <form method="GET" style="margin-bottom:15px; display:flex; gap:10px; align-items:center;">
            <select name="bulan" style="padding:8px;border-radius:6px;">
                <?php
                $namaBulan = [
                    1 => 'Januari',
                    2 => 'Februari',
                    3 => 'Maret',
                    4 => 'April',
                    5 => 'Mei',
                    6 => 'Juni',
                    7 => 'Juli',
                    8 => 'Agustus',
                    9 => 'September',
                    10 => 'Oktober',
                    11 => 'November',
                    12 => 'Desember'
                ];
                foreach ($namaBulan as $no => $nama):
                    ?>
                    <option value="<?= $no ?>" <?= ($bulan == $no ? 'selected' : '') ?>>
                        <?= $nama ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="tahun" style="padding:8px;border-radius:6px;">
                <?php for ($t = date('Y'); $t >= 2020; $t--): ?>
                    <option value="<?= $t ?>" <?= ($tahun == $t ? 'selected' : '') ?>>
                        <?= $t ?>
                    </option>
                <?php endfor; ?>
            </select>

            <button type="submit" class="btn btn-primary">🔍 Filter</button>
        </form>

        <!-- Riwayat transaksi -->


        <div class="box">
            <h3 style="margin-top:0;">📚 Riwayat Transaksi</h3>

            <table>
                <thead>
                    <tr>
                        <th style="width:170px">Tanggal</th>
                        <th style="width:120px">Tipe</th>
                        <th>Keterangan</th>
                        <th style="text-align:right; width:160px">Nominal</th>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <th style="width:120px">Aksi</th>
                        <?php endif; ?>

                    </tr>
                </thead>
                <tbody>
                    <?php if ($riwayat->num_rows === 0): ?>
                        <tr>
                            <td colspan="<?= ($_SESSION['role'] === 'admin' ? 5 : 4) ?>" style="text-align:center; padding:18px; color:#64748b;">Belum ada transaksi.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php while ($row = $riwayat->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['tanggal']) ?></td>
                                <td>
                                    <?php if ($row['tipe'] === 'Penjualan'): ?>
                                        <span class="tag-sale">Penjualan</span>
                                    <?php else: ?>
                                        <span class="tag-exp">Pengeluaran</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($row['keterangan']) ?></td>
                                <td style="text-align:right;">Rp <?= number_format($row['nominal'], 0, ',', '.') ?></td>
                                <?php if ($_SESSION['role'] === 'admin'): ?>
                                    <td>
                                        <form method="POST" action="delete_transaction.php"
                                            onsubmit="return confirm('Hapus transaksi ini?');" style="margin:0;">
                                            <input type="hidden" name="type"
                                                value="<?= ($row['tipe'] === 'Penjualan' ? 'sales' : 'expenses') ?>">
                                            <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                                            <button type="submit"
                                                style="background:#ef4444;color:#fff;border:none;padding:6px 10px;border-radius:6px;cursor:pointer;font-weight:700;">
                                                Hapus
                                            </button>
                                        </form>
                                    </td>
                                <?php endif; ?>


                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</body>

</html>