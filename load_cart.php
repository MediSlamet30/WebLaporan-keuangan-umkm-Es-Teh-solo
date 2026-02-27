<?php
require "db.php";
$sale_id = $_GET['sale_id'];
$result = $mysqli->query("
    SELECT si.id, p.name, si.qty, si.price, (si.qty*si.price) AS subtotal
    FROM sale_items si
    JOIN products p ON p.id = si.product_id
    WHERE si.sale_id=$sale_id
    ORDER BY si.id DESC
");

echo json_encode($result->fetch_all(MYSQLI_ASSOC));
