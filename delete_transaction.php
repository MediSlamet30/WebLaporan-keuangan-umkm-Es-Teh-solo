<?php
// delete_transaction.php
if ($_SESSION['role'] !== 'admin') {
    die('Akses ditolak!');
}

session_start();
if (!isset($_SESSION['user_id'])) header('Location:index.php');

require 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: laporan_keuangan.php');
    exit;
}

$type = $_POST['type'] ?? '';
$id   = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0) {
    $_SESSION['flash_error'] = 'ID tidak valid.';
    header('Location: laporan_keuangan.php');
    exit;
}

$mysqli->begin_transaction();

try {
    if ($type === 'sales') {
        // hapus sale_items dulu
        $stm = $mysqli->prepare("DELETE FROM sale_items WHERE sale_id = ?");
        $stm->bind_param('i', $id);
        $stm->execute();
        $stm->close();

        // hapus sales
        $stm = $mysqli->prepare("DELETE FROM sales WHERE id = ?");
        $stm->bind_param('i', $id);
        $stm->execute();
        $deleted = $stm->affected_rows;
        $stm->close();

    } elseif ($type === 'pengeluaran') {
        $stm = $mysqli->prepare("DELETE FROM pengeluaran WHERE id = ?");
        $stm->bind_param('i', $id);
        $stm->execute();
        $deleted = $stm->affected_rows;
        $stm->close();

    } else {
        throw new Exception('Tipe transaksi tidak dikenali.');
    }

    $mysqli->commit();

    $_SESSION['flash_success'] = ($deleted > 0) ? 'Data berhasil dihapus.' : 'Data tidak ditemukan / sudah dihapus.';
    header('Location: laporan_keuangan.php');
    exit;

} catch (Exception $e) {
    $mysqli->rollback();
    $_SESSION['flash_error'] = 'Terjadi kesalahan: ' . $e->getMessage();
    header('Location: laporan_keuangan.php');
    exit;
}
