<?php
if(session_status() === PHP_SESSION_NONE) session_start();
?>

<div class="sidebar">
    <div class="sidebar-header">
    <img src="img/logo.png" class="logo">
        <h2></h2>
    </div>

    <ul class="menu">
        <li><a href="dashboard.php" class="<?= basename($_SERVER['PHP_SELF'])=='dashboard.php'?'active':'' ?>"><b>DASHBOARD</b></a></li>
        <li><a href="menu.php" class="<?= basename($_SERVER['PHP_SELF'])=='menu.php'?'active':'' ?>"><b>DAFTAR PRODUK</b></a></li>
        <li><a href="input_penjualan.php" class="<?= basename($_SERVER['PHP_SELF'])=='input_penjualan.php'?'active':'' ?>"><b>INPUT PENJUALAN</b></a></li>
        <li><a href="input_pengeluaran.php" class="<?= basename($_SERVER['PHP_SELF'])=='input_pengeluaran.php'?'active':'' ?>"><b>INPUT PENGELUARAN</b></a></li>
        <li><a href="laporan_keuangan.php" class="<?= basename($_SERVER['PHP_SELF'])=='laporan_keuangan.php'?'active':'' ?>"><b>LAPORAN KEUANGAN</b></a></li>
        <li><a href="logout.php" class="logout"><b>LOGOUT</b></a></li>
    </ul>

    <div class="user-box">
        Halo, <b><?= htmlspecialchars($_SESSION['user_name'] ?? '') ?></b>
    </div>
</div>

<!-- Wrapper Area Untuk Konten -->
<div class="main-content">
