<?php
session_start();
if (!isset($_SESSION['user_id'])) header('Location:index.php');

require 'db.php';

// Jika form disubmit → masukkan ke tabel expenses
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $note   = $_POST['keterangan'];
    $amount = $_POST['jumlah'];

    $stmt = $mysqli->prepare("INSERT INTO expenses (note, amount, created_at) VALUES (?, ?, NOW())");
    $stmt->bind_param('si', $note, $amount);
    $stmt->execute();

    echo "<script>alert('Pengeluaran berhasil dicatat!');location.href='input_pengeluaran.php';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Input Pengeluaran</title>
    <link rel="stylesheet" href="style.css">

<style>
/* MAIN CONTENT */
.main-content {
    margin-left: 260px;
    padding: 40px;
}

/* CARD */
.card-box {
    max-width: 600px;
    margin: 0 auto;
    background: #ffffff;
    padding: 28px;
    border-radius: 18px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.08);
    border: 1px solid #e5e7eb;
    margin-left: -160px;
}

/* TITLE */
.title-page {
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 25px;
    color: #222;
    display: flex;
    align-items: center;
    gap: 8px;
}

/* INPUT */
.input-modern {
    width: 100%;
    padding: 12px 14px;
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    font-size: 16px;
    margin-top: 6px;
    margin-bottom: 18px;
    outline: none;
    transition: 0.2s ease;
}
.input-modern:focus {
    border-color: #258132;
    box-shadow: 0 0 5px rgba(37, 129, 50, 0.25);
}

/* LABEL */
label {
    font-size: 16px;
    font-weight: 600;
    color: #333;
    margin-bottom: 4px;
    display: block;
}

/* BUTTON */
.btn-submit {
    background: #258132;
    padding: 13px 20px;
    color: white;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 600;
    border: none;
    cursor: pointer;
    width: 100%;
    transition: 0.2s ease;
}
.btn-submit:hover {
    background: #1f6a2a;
    transform: translateY(-2px);
}

/* RESPONSIVE */
@media(max-width: 768px){
    .main-content {
        margin-left: 0;
        padding: 20px;
    }

    .card-box {
        padding: 20px;
    }
}
</style>

</head>

<body>

<?php include 'nav.php'; ?>

<div class="main-content">

<div class="card-box">

    <div class="title-page">💸 Input Pengeluaran</div>

    <form method="POST">

        <label>Keterangan Pengeluaran</label>
        <input type="text" name="keterangan" required class="input-modern">

        <label>Jumlah (Rp)</label>
        <input type="number" name="jumlah" required class="input-modern">

        <button type="submit" class="btn-submit">Catat Pengeluaran</button>

    </form>

</div>

</div>

</body>
</html>
