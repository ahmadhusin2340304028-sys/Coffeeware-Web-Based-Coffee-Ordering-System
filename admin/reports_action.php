<?php  
include '../db_connect.php';

// Ambil filter dari form
$reportType   = $_GET['reportType'] ?? 'Penjualan Harian';
$product      = $_GET['product']    ?? 'Semua';

$startDate    = $_GET['dateRange1'] ?? '';
$endDate      = $_GET['dateRange2'] ?? '';
?>


<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title><?= $reportType; ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://unpkg.com/bootstrap-table@1.21.0/dist/bootstrap-table.min.css">
</head>
<body>
<style>
    body{
        width: 100%;
        height: 100vh;
        display: flex;
        justify-content: center;
        padding:30px;
    }
    @page {
    size: auto;
    margin: 0;
}


</style>

<?php

// ==========================
//   QUERY BERDASARKAN JENIS
// ==========================

$output = "";


////////////
// custom
///////////////
if ($reportType == "Penjualan(custom)") {

        $filterProduct = "";
    if ($product != "Semua") {
        $filterProduct = "
            AND order_id IN (
                SELECT order_id 
                FROM order_items oi 
                JOIN menu m ON oi.menu_id = m.menu_id
                WHERE m.name = '$product'
            )
        ";
    }

        // Query range
    $q1 = $conn->query("
        SELECT 
            COUNT(*) AS total_transaksi
        FROM orders
        WHERE created_at BETWEEN '$startDate' AND '$endDate'
        $filterProduct;

    ");

    $order = ($q1->fetch_object())->total_transaksi ?? 0;

    // Query range
    $q2 = $conn->query("
        SELECT 
            SUM(total_price) AS total_pendapatan
        FROM orders
        WHERE created_at BETWEEN '$startDate' AND '$endDate'
        $filterProduct;

    ");

    $pendapatan = ($q2->fetch_object())->total_pendapatan ?? 0;

    $query = "
        SELECT 
            DATE(created_at) AS tanggal,
            COUNT(*) AS total_transaksi,
            SUM(total_price) AS pendapatan
        FROM orders
        WHERE created_at BETWEEN '$startDate' AND '$endDate'
        $filterProduct
        GROUP BY DATE(created_at)
        ORDER BY tanggal ASC;
    ";

    $result = $conn->query($query);

    $output .= "
        <div class'container' style='width:100%;margin: 30px;'><h4>Laporan Penjualan $startDate - $endDate</h4>
        <ul>
            <li>Total Order               : $order</li>
            <li>Total Pendapatan          : Rp. " . number_format($pendapatan,0,',','.') . "</li>
        </ul>
        <table class='table table-striped table-hover'>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tanggal</th>
                    <th>Total Transaksi</th>
                    <th>Pendapatan</th>
                </tr>
            </thead>
            <tbody>
    ";
    $n=1;
    while($row = $result->fetch_assoc()) {
        $output .= "
            <tr>
                <td>{$n}</td>
                <td>{$row['tanggal']}</td>
                <td>{$row['total_transaksi']}</td>
                <td>Rp " . number_format($row['pendapatan']) . "</td>
            </tr>
        ";
        $n++;
    }

    $output .= "</tbody></table></div>";
}


////////////////////
// harian
////////////////////
if ($reportType == "Penjualan Harian") {

    // Query hari ini
    $q1 = $conn->query("
        SELECT 
            COUNT(*) AS total_transaksi_hari_ini
        FROM orders
        WHERE DATE(created_at) = CURDATE();

    ");

    $hari_ini = ($q1->fetch_object())->total_transaksi_hari_ini ?? 0;


    // Query hari lalu
    $q2 = $conn->query("
        SELECT 
            COUNT(*) AS total_transaksi_kemarin
        FROM orders
        WHERE DATE(created_at) = CURDATE() - INTERVAL 1 DAY;

    ");

    $hari_lalu = ($q2->fetch_object())->total_transaksi_kemarin ?? 0;


    // total pendapatan
        // hari ini
    $q3 = $conn->query("
        SELECT 
            SUM(total_price) AS pendapatan_hari_ini
        FROM orders
        WHERE DATE(created_at) = CURDATE();

    ");

    $Ohari_ini = ($q3->fetch_object())->pendapatan_hari_ini ?? 0;


    // hari lalu
    $q4 = $conn->query("
        SELECT 
            SUM(total_price) AS pendapatan_kemarin
        FROM orders
        WHERE DATE(created_at) = CURDATE() - INTERVAL 1 DAY;

    ");

    $Ohari_lalu = ($q4->fetch_object())->pendapatan_kemarin ?? 0;

    // Hitung selisih
    $selisih_harian = $Ohari_ini - $Ohari_lalu;

    // Persentase kenaikan / penurunan
    $persen = ($Ohari_lalu > 0) 
        ? (($Ohari_ini - $Ohari_lalu) / $Ohari_lalu) * 100
        : 100; 
    $persen = round($persen);

        $filterProduct = "";
    if ($product != "Semua") {
        $filterProduct = "
            AND order_id IN (
                SELECT order_id 
                FROM order_items oi 
                JOIN menu m ON oi.menu_id = m.menu_id
                WHERE m.name = '$product'
            )
        ";
    }

    $query = "
        SELECT 
            HOUR(created_at) AS jam,
            COUNT(*) AS total_transaksi,
            SUM(total_price) AS pendapatan
        FROM orders 
        WHERE created_at BETWEEN CURDATE()
            AND DATE_ADD(CURDATE(), INTERVAL 1 DAY) - INTERVAL 1 SECOND
            $filterProduct
        GROUP BY HOUR(created_at)
        ORDER BY jam ASC;

    ";

    $result = $conn->query($query);

    $output .= "
        <div class'container' style='width:100%;margin: 30px;'><h4>Laporan Penjualan Harian</h4>
        <ul>
                <li>Total Order hari ini                : $hari_ini</li>
                <li>Total Order hari lalu               : $hari_lalu</li>
                <li>Total Pendapatan hari ini           : Rp. " . number_format($Ohari_ini,0,',','.') . "</li>
                <li>Total Pendapatan kemarin            : Rp. " . number_format($Ohari_lalu,0,',','.') . "</li>
                <li>Perbandingan dengan hari sebelumnya : Rp. " . number_format($selisih_harian,0,',','.') . "</li>
                <li>Perubahan                             : $persen%</li>
            </ul>
        <table class='table table-striped table-hover'>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Jam</th>
                    <th>Total Transaksi</th>
                    <th>Pendapatan</th>
                </tr>
            </thead>
            <tbody>
    ";
    $n=1;
    while($row = $result->fetch_assoc()) {
        $output .= "
            <tr>
                <td>{$n}:00</td>
                <td>{$row['jam']}:00</td>
                <td>{$row['total_transaksi']}</td>
                <td>Rp " . number_format($row['pendapatan']) . "</td>
            </tr>
        ";
        $n++;
    }

    $output .= "</tbody></table></div>";
}

//////////////////
//MINGGUAN
/////////////////
if ($reportType == "Penjualan Mingguan") {

    // Query minggu ini
    $q1 = $conn->query("
        SELECT 
            SUM(total_price) AS pendapatan_minggu_ini
        FROM orders
        WHERE YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1);

    ");

    $minggu_ini = ($q1->fetch_object())->pendapatan_minggu_ini ?? 0;


    // Query minggu lalu
    $q2 = $conn->query("
        SELECT 
            SUM(total_price) AS pendapatan_minggu_lalu
        FROM orders
        WHERE YEARWEEK(created_at, 1) = YEARWEEK(CURDATE() - INTERVAL 1 WEEK, 1);

    ");

    $minggu_lalu = ($q2->fetch_object())->pendapatan_minggu_lalu ?? 0;


    // Hitung selisih
    $selisih_mingguan = $minggu_ini - $minggu_lalu;

    // Persentase kenaikan / penurunan
    $persen = ($minggu_lalu > 0) 
        ? (($minggu_ini - $minggu_lalu) / $minggu_lalu) * 100
        : 100; 
    $persen = round($persen); 

    // Query totalorder minggu ini
    $q3 = $conn->query("
        SELECT 
            COUNT(*) AS total_transaksi_minggu_ini
        FROM orders
        WHERE YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1);


    ");

    $Ominggu_ini = ($q3->fetch_object())->total_transaksi_minggu_ini ?? 0;


    // Query totalorder minggu lalu
    $q4 = $conn->query("
        SELECT 
            COUNT(*) AS total_transaksi_minggu_lalu
        FROM orders
        WHERE YEARWEEK(created_at, 1) = YEARWEEK(CURDATE() - INTERVAL 1 WEEK, 1);

    ");

    $Ominggu_lalu = ($q4->fetch_object())->total_transaksi_minggu_lalu ?? 0;

        $filterProduct = "";
    if ($product != "Semua") {
        $filterProduct = "
            AND order_id IN (
                SELECT order_id 
                FROM order_items oi 
                JOIN menu m ON oi.menu_id = m.menu_id
                WHERE m.name = '$product'
            )
        ";
    }

    $query = "
        SELECT 
            DATE(created_at) AS tanggal,
            COUNT(*) AS total_transaksi,
            SUM(total_price) AS pendapatan
        FROM orders
        WHERE created_at >= CURDATE() - INTERVAL 7 DAY
        AND created_at < CURDATE() + INTERVAL 1 DAY
        $filterProduct
        GROUP BY DATE(created_at)
        ORDER BY tanggal ASC;
    ";

    $result = $conn->query($query);



    $output .= "
        <div class'container' style='width:100%;margin: 30px;'>
            <h4>Laporan Penjualan Mingguan</h4>
            <ul>
                <li>Total Order minggu ini                : $Ominggu_ini</li>
                <li>Total Order minggu lalu               : $Ominggu_lalu</li>
                <li>Total Pendapatan minggu ini           : Rp. " . number_format($minggu_ini,0,',','.') . "</li>
                <li>Total Pendapatan minggu lalu          : Rp. " . number_format($minggu_lalu,0,',','.') . "</li>
                <li>Perbandingan dengan minggu sebelumnya : Rp. " . number_format($selisih_mingguan,0,',','.') . "</li>
                <li>Perubahan                             : $persen%</li>
            </ul>
            "; 
            if($product != "Semua"){ $output.="<p>filter menu : $product</p>";};
            $output .="<div class='d-flex justify-content-center'>
                <table class='table table-striped table-hover mx-auto border rounded' style='width:100%;'>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Tanggal</th>
                            <th>Total Transaksi</th>
                            <th>Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody>
    ";
    $n = 1;
    while($row = $result->fetch_assoc()) {
        $output .= "
            <tr>
                <td>{$n}</td>
                <td>{$row['tanggal']}</td>
                <td>{$row['total_transaksi']}</td>
                <td>Rp " . number_format($row['pendapatan'],0,',','.') . "</td>
            </tr>
        ";
        $n++;
    }

    $output .= "
                    </tbody>
                </table>
            </div>
        </div>
    ";

}

//////////////////
// BULANAN
///////////////
if ($reportType == "Penjualan Bulanan") {


    // Query bulan ini
    $q1 = $conn->query("
        SELECT SUM(total_price) AS pendapatan_bulan_ini
        FROM orders
        WHERE YEAR(created_at) = YEAR(CURRENT_DATE)
        AND MONTH(created_at) = MONTH(CURRENT_DATE)
    ");

    $bulan_ini = ($q1->fetch_object())->pendapatan_bulan_ini ?? 0;


    // Query bulan lalu
    $q2 = $conn->query("
        SELECT SUM(total_price) AS pendapatan_bulan_lalu
        FROM orders
        WHERE YEAR(created_at) = YEAR(CURRENT_DATE - INTERVAL 1 MONTH)
        AND MONTH(created_at) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH)
    ");

    $bulan_lalu = ($q2->fetch_object())->pendapatan_bulan_lalu ?? 0;


    // Hitung selisih
    $selisih = $bulan_ini - $bulan_lalu;

    // Persentase kenaikan / penurunan
    $persen = ($bulan_lalu > 0) 
        ? (($bulan_ini - $bulan_lalu) / $bulan_lalu) * 100
        : 100;

    $persen = round($persen);


    $query = "
        SELECT 
            DATE(created_at) AS tanggal,
            COUNT(*) AS total_transaksi,
            SUM(total_price) AS pendapatan
        FROM orders
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
        GROUP BY DATE(created_at)
        ORDER BY tanggal ASC;
    ";

    $result = $conn->query($query);
    
    $q3 = $conn->query("
        SELECT 
            COUNT(*) AS total_transaksi_bulan_ini
        FROM orders
        WHERE YEAR(created_at) = YEAR(CURRENT_DATE)
        AND MONTH(created_at) = MONTH(CURRENT_DATE);

    ");

    $tOBI = ($q3->fetch_object())->total_transaksi_bulan_ini ?? 0;

    $q4 = $conn->query("
        SELECT 
            COUNT(*) AS total_transaksi_bulan_lalu
        FROM orders
        WHERE YEAR(created_at) = YEAR(CURRENT_DATE - INTERVAL 1 MONTH)
        AND MONTH(created_at) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH);


    ");

    $tOBL = ($q4->fetch_object())->total_transaksi_bulan_lalu ?? 0;

    $output .= "
        <div class'container' style='width:100%;margin: 30px;'><h4>Laporan Penjualan Bulanan</h4><br>
        <ul>
            <li>Total Order Bulan Ini                : $tOBI</li>
            <li>Total Order Bulan Lalu               : $tOBL</li>
            <li>Total Pendapatan Bulan Ini           : Rp. " . number_format($bulan_ini,0,',','.') . "</li>
            <li>Total Pendapatan Bulan lalu          : Rp. " . number_format($bulan_lalu,0,',','.') . "</li>
            <li>Perbandingan dengan Bulan Sebelumnya : Rp. " . number_format($selisih,0,',','.') . "</li>
            <li>Perubahan                            : $persen%</li>
        </ul>
        <table class='table table-striped table-hover'>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tanggal</th>
                    <th>Total Transaksi</th>
                    <th>Pendapatan</th>
                </tr>
            </thead>
            <tbody>
    ";
    $n=1;
    while($row = $result->fetch_assoc()) {
        $output .= "
            <tr>
                <td>{$n}</td>
                <td>{$row['tanggal']}</td>
                <td>{$row['total_transaksi']}</td>
                <td>Rp " . number_format($row['pendapatan']) . "</td>
            </tr>
        ";
        $n++;
    }

    $output .= "</tbody></table></div>";
}


// ==========================
//   PENJUALAN PER PRODUK
// ==========================

if ($reportType == "Penjualan Per Produk") {

    $filterProduct = ($product != "Semua") ? "AND m.name = '$product'" : "";

    $query = "
        SELECT 
            m.name,
            SUM(oi.quantity) AS terjual,
            m.price,
            SUM(oi.quantity * m.price) AS pendapatan
        FROM order_items oi
        JOIN menu m ON oi.menu_id = m.menu_id
        JOIN orders o ON oi.order_id = o.order_id
        WHERE o.created_at BETWEEN '$startDate' AND '$endDate'
        $filterProduct
        GROUP BY m.name, m.price
    ";


    $result = $conn->query($query);

    $output .= "
        <div class'container' style='width:100%;margin: 30px;'><h4>Laporan Penjualan Per Produk</h4>
        <table class='table table-striped table-hover'>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Produk</th>
                    <th>Terjual</th>
                    <th>Harga</th>
                    <th>Pendapatan</th>
                </tr>
            </thead>
            <tbody>
    ";
    $n=1;
    while($row = $result->fetch_assoc()) {
        $output .= "
            <tr>
                <td>{$n}</td>
                <td>{$row['name']}</td>
                <td>{$row['terjual']}</td>
                <td>Rp " . number_format($row['price']) . "</td>
                <td>Rp " . number_format($row['pendapatan']) . "</td>
            </tr>
        ";
        $n++;
    }

    $output .= "</tbody></table></div>";
}


// ==========================
//       RIWAYAT ORDER
// ==========================

if ($reportType == "Riwayat Order") {

    $filterProduct = "";
    if ($product != "Semua") {
        $filterProduct = "
            AND o.order_id IN (
                SELECT order_id 
                FROM order_items oi 
                JOIN menu m ON oi.menu_id = m.menu_id
                WHERE m.name = '$product'
            )
        ";
    }

    $query = "
        SELECT 
            o.order_id,
            o.created_at,
            o.total_price,
            o.payment_method
        FROM orders o
        WHERE DATE(o.created_at) BETWEEN '$startDate' AND '$endDate'
        $filterProduct
        ORDER BY o.order_id DESC
    ";

    $result = $conn->query($query);

    $output .= "
        <div class'container' style='width:100%;margin: 30px;'><h4>Riwayat Order</h4>
        <table class='table table-striped table-hover'>
            <thead>
                <tr>
                    <th>#</th>
                    <th>ID Order</th>
                    <th>Tanggal</th>
                    <th>Total</th>
                    <th>Metode Pembayaran</th>
                </tr>
            </thead>
            <tbody>
    ";
    $n=1;
    while($row = $result->fetch_assoc()) {
        $output .= "
            <tr>
                <td>{$n}</td>
                <td>{$row['order_id']}</td>
                <td>{$row['created_at']}</td>
                <td>Rp " . number_format($row['total_price']) . "</td>
                <td>{$row['payment_method']}</td>
            </tr>
        ";
        $n++;
    }

    $output .= "</tbody></table></div>";
}


// Output hasil
echo $output;


?>
  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://unpkg.com/bootstrap-table@1.21.0/dist/bootstrap-table.min.js"></script>
   <script src="https://cdn.jsdelivr.net/npm/jquery/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
    <script src="https://unpkg.com/bootstrap-table@1.21.0/dist/bootstrap-table.min.js"></script>
  
</body>
</html>