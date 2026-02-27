<?php
session_start();
if (!isset($_SESSION['user_id'])) header('Location: index.php');

require 'db.php';

// Ambil daftar produk
$products = $mysqli->query("SELECT * FROM products ORDER BY name ASC");

// ================================
// PROSES SUBMIT + POTONG STOK
// ================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $items = $_POST['items'];
    $total = 0;

    // ✅ CEK STOK DULU
    foreach ($items as $it) {
        if ($it['qty'] > 0) {

            $cek = $mysqli->query("SELECT stock FROM products WHERE id=".(int)$it['product']);
            $stok = $cek->fetch_assoc()['stock'];

            if ($it['qty'] > $stok) {
                echo "<script>
                    alert('Stok tidak cukup untuk salah satu produk!');
                    history.back();
                </script>";
                exit;
            }

            $total += $it['qty'] * $it['price'];
        }
    }

    // ✅ SIMPAN KE SALES
    $stmt = $mysqli->prepare("INSERT INTO sales (total) VALUES (?)");
    $stmt->bind_param('i', $total);
    $stmt->execute();
    $sale_id = $stmt->insert_id;

    // ✅ SIMPAN ITEM & POTONG STOK
    foreach ($items as $it) {
        if ($it['qty'] > 0) {

            $q = $mysqli->prepare("
                INSERT INTO sale_items (sale_id, product_id, qty, price)
                VALUES (?,?,?,?)
            ");
            $q->bind_param('iiis', $sale_id, $it['product'], $it['qty'], $it['price']);
            $q->execute();

            // ✅ POTONG STOK
            $mysqli->query("
                UPDATE products 
                SET stock = stock - ".(int)$it['qty']." 
                WHERE id = ".(int)$it['product']
            );
        }
    }

    echo "<script>
        alert('Penjualan berhasil & stok otomatis berkurang!');
        location.href='input_penjualan.php';
    </script>";
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Input Penjualan</title>
    <link rel="stylesheet" href="style.css">

<style>

.main-content {
    margin-left: 220px;
    padding: 40px;
}

.card-box {
    max-width: 850px;
    margin: 0 auto;
    margin-left: -160px;
    background: #ffffff;
    padding: 25px;
    border-radius: 18px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.08);
    border: 1px solid #e5e7eb;
}

.title-page {
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 18px;
    color: #222;
}

.table-wrapper {
    overflow-x: auto;
    border-radius: 10px;
}

.table-modern {
    width: 100%;
    border-collapse: collapse;
    min-width: 600px;
}

.table-modern thead {
    background: #f3f4f6;
}

.table-modern th {
    padding: 12px;
    font-size: 15px;
    font-weight: 600;
    border-bottom: 2px solid #ddd;
    text-align: left;
}

.table-modern td {
    padding: 12px;
    border-bottom: 1px solid #eee;
    font-size: 15px;
}

.qty-input {
    width: 80px;
    padding: 8px 10px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 15px;
}

.btn-submit {
    background: #258132;
    padding: 13px 22px;
    color: white;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 600;
    border: none;
    cursor: pointer;
}
</style>

</head>

<body>

<?php include 'nav.php'; ?>

<div class="main-content">
<div class="card-box">

    <div class="title-page">🧾 Input Penjualan</div>

    <form method="POST">

        <div class="table-wrapper">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Qty</th>
                    </tr>
                </thead>

                <tbody>
                <?php while($p = $products->fetch_assoc()): ?>
                <tr>
                    <td><?= $p['name'] ?></td>
                    <td><b>Rp <?= number_format($p['price']) ?></b></td>
                    <td><b><?= $p['stock'] ?></b></td>

                    <td>
                        <input type="hidden" name="items[<?= $p['id'] ?>][product]" value="<?= $p['id'] ?>">
                        <input type="hidden" name="items[<?= $p['id'] ?>][price]" value="<?= $p['price'] ?>">

                        <input type="number"
                            class="qty-input"
                            name="items[<?= $p['id'] ?>][qty]"
                            min="0"
                            max="<?= $p['stock'] ?>"
                            value="0">
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>

            </table>
        </div>

        <br>
        <button class="btn-submit" type="submit">💾 Simpan Penjualan</button>

    </form>

</div>
</div>

</body>
</html>
