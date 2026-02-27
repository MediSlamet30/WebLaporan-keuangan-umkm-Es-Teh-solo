<?php
require 'vendor/autoload.php';
require 'db.php';

use Dompdf\Dompdf;

$start = $_GET['start'];
$end   = $_GET['end'];

$periodStart = $start . " 00:00:00";
$periodEnd   = $end . " 23:59:59";

/* Total Pendapatan */
$q = $mysqli->prepare("SELECT IFNULL(SUM(total),0) AS t FROM sales WHERE created_at BETWEEN ? AND ?");
$q->bind_param('ss', $periodStart, $periodEnd);
$q->execute();
$pendapatan = $q->get_result()->fetch_assoc()['t'];

/* Total Pengeluaran */
$q = $mysqli->prepare("SELECT IFNULL(SUM(jumlah),0) AS t FROM pengeluaran WHERE tanggal BETWEEN ? AND ?");
$q->bind_param('ss', $periodStart, $periodEnd);
$q->execute();
$pengeluaran = $q->get_result()->fetch_assoc()['t'];

$laba = $pendapatan - $pengeluaran;

/* Riwayat transaksi */
$sql = "
SELECT created_at AS tanggal, 'Penjualan' AS jenis, CONCAT('Penjualan #', id) AS ket, total AS jumlah
FROM sales
WHERE created_at BETWEEN ? AND ?
UNION ALL
SELECT tanggal, 'Pengeluaran' AS jenis, keterangan AS ket, jumlah
FROM pengeluaran
WHERE tanggal BETWEEN ? AND ?
ORDER BY tanggal DESC
";

$q = $mysqli->prepare($sql);
$q->bind_param('ssss', $periodStart, $periodEnd, $periodStart, $periodEnd);
$q->execute();
$data = $q->get_result();

/* HTML PDF */
$html = "
<h2>Laporan Keuangan</h2>
<p>Periode: $start — $end</p>

<h3>Total:</h3>
<p>Pendapatan: <b>Rp ".number_format($pendapatan)."</b></p>
<p>Pengeluaran: <b>Rp ".number_format($pengeluaran)."</b></p>
<p>Laba / Rugi: <b>Rp ".number_format($laba)."</b></p>

<h3>Rincian Transaksi</h3>
<table border='1' cellspacing='0' cellpadding='6' width='100%'>
<tr>
    <th>Tanggal</th>
    <th>Jenis</th>
    <th>Keterangan</th>
    <th>Jumlah</th>
</tr>
";

while($r = $data->fetch_assoc()){
    $html .= "
    <tr>
        <td>{$r['tanggal']}</td>
        <td>{$r['jenis']}</td>
        <td>{$r['ket']}</td>
        <td>Rp ".number_format($r['jumlah'])."</td>
    </tr>";
}

$html .= "</table>";

/* Output ke PDF */
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4');
$dompdf->render();

header("Content-Disposition: attachment; filename=Laporan_Keuangan_$start-$end.pdf");

$dompdf->stream("", ["Attachment" => true]);
