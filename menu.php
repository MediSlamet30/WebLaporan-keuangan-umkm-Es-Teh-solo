<?php
session_start();
require 'db.php'; // ✅ HARUS DI ATAS SEBELUM QUERY APAPUN

// ==========================
// UPDATE STOK OLEH ADMIN
// ==========================
if (isset($_POST['update_stock']) && $_SESSION['role'] === 'admin') {
  $pid = (int)$_POST['product_id'];
  $qty = (int)$_POST['qty'];

  $mysqli->query("
    UPDATE products 
    SET stock = stock + $qty 
    WHERE id = $pid
  ");

  header("Location: menu.php");
  exit;
}

// PROTEKSI LOGIN
if (!isset($_SESSION['user_id'])) {
  header('Location: index.php');
  exit;
}

// AMBIL PRODUK
$res = $mysqli->query("SELECT * FROM products ORDER BY id ASC");
$products = $res->fetch_all(MYSQLI_ASSOC);
?>

<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <title>Daftar Produk - Es Teh</title>
  <link rel="stylesheet" href="style.css">
</head>

<body>

  <?php include 'nav.php'; ?>

  <main class="container">
    <h2 style="text-align:center; color:black; margin-bottom:24px;">Daftar Produk</h2>


    <div class="product-grid">

      <?php foreach ($products as $p):
        $pid = $p['id'];
        $stok = $p['stock'];
        ?>


        <div class="product-card">

          <span class="stock <?= ($stok == 0 ? 'habis' : 'ada') ?>">
            Stok: <?= $stok ?>
          </span>

          <img src="img/products/<?= htmlspecialchars($p['image']) ?>" class="product-img">

          <div class="product-info">
            <h4><?= htmlspecialchars($p['name']) ?></h4>
            <p class="price">Rp <?= number_format($p['price'], 0, ',', '.') ?></p>

            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
              <form method="POST" style="margin-top:10px; display:flex; gap:6px;">
                <input type="hidden" name="product_id" value="<?= $pid ?>">
                <input type="number" name="qty" value="1" min="1" style="width:60px;">
                <button type="submit" name="update_stock"
                  style="background:#16a34a;color:#fff;border:none;padding:6px 10px;border-radius:6px;">
                  + Stok
                </button>
              </form>
            <?php endif; ?>
          </div>

        </div>
      <?php endforeach; ?>
    </div>


  </main>
  <div class="main-content"></div>
</body>

</html>