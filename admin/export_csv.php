<?php
include '../db_connect.php';

// Ambil filter dari GET
$reportType = $_GET['reportType'] ?? 'Penjualan Harian';
$product    = $_GET['product'] ?? 'Semua';

$startDate    = $_GET['dateRange1'] ?? '';
$endDate      = $_GET['dateRange2'] ?? '';
$filterRangeTime = "DATE(created_at) BETWEEN '$startDate' AND '$endDate'";
$currentDate  = date("Y-m-d");
// Tentukan nama file
$filename = "laporan_" . str_replace(" ", "_", strtolower($reportType)) . $currentDate .".csv";

// Header supaya browser mendownload CSV
header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=\"$filename\"");

$output = fopen("php://output", "w");

// ==========================
//  PENJUALAN HARIAN
// ==========================

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

   fputs($output, "\xEF\xBB\xBF");

    // judul
    fputcsv($output, ["Laporan Penjualan Harian"], ";");
    fputcsv($output, [""], ";");
    fputcsv($output, ["Total Order Hari ini", "$hari_ini"], ";");//total order hari ini
    fputcsv($output, ["Total Order Kemarin", "$hari_lalu"], ";");//total order hari lalu
    fputcsv($output, ["Total Pendapatan Hari Ini", "Rp. " . number_format($Ohari_ini,0,',','.') . ""], ";");//total order hari lalu
    fputcsv($output, ["Total Pendapatan Hari Lalu", "Rp. " . number_format($Ohari_lalu,0,',','.') . ""], ";");//total order hari lalu
    fputcsv($output, ["Perbandingan", "Rp. " . number_format($selisih_harian,0,',','.') . ""], ";");//selisih 
    fputcsv($output, ["Persentase", "$persen%"], ";");//persen
    fputcsv($output, [""], ";");

    // Header kolom CSV
    fputcsv($output, ["#","Jam", "Total Transaksi", "Pendapatan"], ";");
    $n=1;
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $n,
            $row['jam'] . ':00',
            $row['total_transaksi'],
            number_format($row['pendapatan'],0,',','.')
        ], ";"); // <-- delimiter wajib pakai semicolon
        $n++;
    }
}


// ==========================
//  PENJUALAN MINGGUAN
// ==========================

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

    fputs($output, "\xEF\xBB\xBF");

    // judul
    fputcsv($output, ["Laporan Penjualan Mingguan"], ";");
    fputcsv($output, [""], ";");
    fputcsv($output, ["Total Order Minggu ini", "$Ominggu_ini"], ";");//total order hari ini
    fputcsv($output, ["Total Order Minggu Lalu", "$Ominggu_lalu"], ";");//total order hari lalu
    fputcsv($output, ["Total Pendapatan Minggu Ini", "Rp. " . number_format($minggu_ini,0,',','.') . ""], ";");//total order hari lalu
    fputcsv($output, ["Total Pendapatan Minggu Lalu", "Rp. " . number_format($minggu_lalu,0,',','.') . ""], ";");//total order hari lalu
    fputcsv($output, ["Perbandingan", "Rp. " . number_format($selisih_mingguan,0,',','.') . ""], ";");//selisih 
    fputcsv($output, ["Persentase", "$persen%"], ";");//persen
    fputcsv($output, [""], ";");

    // Header kolom CSV
    fputcsv($output, ["#","Tanggal", "Total Transaksi", "Pendapatan"], ";");
    $n=1;
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $n,
            $row['tanggal'],
            $row['total_transaksi'],
            number_format($row['pendapatan'],0,',','.')
        ], ";"); // <-- delimiter wajib pakai semicolon
    $n++;
    }


}


// ==========================
//  PENJUALAN BULANAN
// ==========================

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

    fputs($output, "\xEF\xBB\xBF");

    // judul
    fputcsv($output, ["Laporan Penjualan Bulanan"], ";");
    fputcsv($output, [""], ";");
    fputcsv($output, ["Total Order Bulan ini", "$tOBI"], ";");//total order hari ini
    fputcsv($output, ["Total Order Bulan Lalu", "$tOBL"], ";");//total order hari lalu
    fputcsv($output, ["Total Pendapatan Bulan Ini", "Rp. " . number_format($bulan_ini,0,',','.') . ""], ";");//total order hari lalu
    fputcsv($output, ["Total Pendapatan Bulan Lalu", "Rp. " . number_format($bulan_lalu,0,',','.') . ""], ";");//total order hari lalu
    fputcsv($output, ["Perbandingan", "Rp. " . number_format($selisih,0,',','.') . ""], ";");//selisih 
    fputcsv($output, ["Persentase", "$persen%"], ";");//persen
    fputcsv($output, [""], ";");

    // Header kolom CSV
    fputcsv($output, ["#","Tanggal", "Total Transaksi", "Pendapatan"], ";");
    $n=1;
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $n,
            $row['tanggal'],
            $row['total_transaksi'],
            number_format($row['pendapatan'],0,',','.')
        ], ";"); // <-- delimiter wajib pakai semicolon
    $n++;
    }
}

// ==========================
//  PENJUALAN(CUSTOM)
// ==========================

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

    fputs($output, "\xEF\xBB\xBF");

    // judul
    fputcsv($output, ["Laporan Penjualan Bulanan"], ";");
    fputcsv($output, [""], ";");
    fputcsv($output, ["Total Order", "$order"], ";");//total order hari ini
    fputcsv($output, ["Total Pendapatan", "Rp. " . number_format($pendapatan,0,',','.') . ""], ";");//total order hari lalu
    fputcsv($output, [""], ";");

    // Header kolom CSV
    fputcsv($output, ["#","Tanggal", "Total Transaksi", "Pendapatan"], ";");
    $n=1;
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $n,
            $row['tanggal'],
            $row['total_transaksi'],
            number_format($row['pendapatan'],0,',','.')
        ], ";"); // <-- delimiter wajib pakai semicolon
    $n++;
    }
    
}




// ==========================
//  PENJUALAN PER PRODUK
// ==========================

if ($reportType == "Penjualan Per Produk") {


    $query_produk_laris = "SELECT 
        m.name,
        m.price,
        SUM(oi.quantity) AS total_dipesan,
        SUM(oi.quantity * m.price) AS pendapatan
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.order_id
    JOIN menu m ON oi.menu_id = m.menu_id
    WHERE o.created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
    GROUP BY m.menu_id, m.name
    ORDER BY total_dipesan DESC;
    ";

    $resultPL = $conn->query($query_produk_laris);

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

    fputs($output, "\xEF\xBB\xBF");

    

    // Header kolom CSV
    fputcsv($output, ["Laporan Penjualan Per Produk (Bulanan)"], ";");
    fputcsv($output, [""], ";");
    fputcsv($output, ["Urutan Produk Terlaris"], ";");
    fputcsv($output, [""], ";");
    fputcsv($output, ["#","Nama_menu", "Terjual", "Harga", "Pendapatan"], ";");
    $n=1;
    while ($rowP = $resultPL->fetch_assoc()) {
        fputcsv($output, [
            $n,
            $rowP['name'],
            $rowP['total_dipesan'],
            number_format($rowP['price'],0,',','.'),
            number_format($rowP['pendapatan'],0,',','.')
        ], ";"); // <-- delimiter wajib pakai semicolon
    $n++;
    }

    // Header kolom CSV
    fputcsv($output, [""], ";");
    fputcsv($output, [""], ";");
    fputcsv($output, ["Tabel Perjualan/Produk"], ";");
    fputcsv($output, [""], ";");
    fputcsv($output, ["#","Tanggal", "Total Transaksi", "Pendapatan"], ";");
    $n=1;
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $n,
            $row['name'],
            $row['terjual'],
            number_format($row['price'],0,',','.'),
            number_format($row['pendapatan'],0,',','.')
        ], ";"); // <-- delimiter wajib pakai semicolon
    $n++;
    }
    
}


// ==========================
//  RIWAYAT ORDER
// ==========================

if ($reportType == "Riwayat Order") {

    fputcsv($output, ["ID Order", "Tanggal", "Total", "Metode Pembayaran"]);

    $filterProduct = "";
    if ($product != "Semua") {
        $filterProduct = "
            AND o.id IN (
                SELECT order_id 
                FROM order_detail od
                JOIN products p ON od.product_id = p.id
                WHERE p.product_name = '$product'
            )
        ";
    }

    $query = "
        SELECT 
            o.id,
            o.created_at,
            o.total,
            o.payment_method
        FROM orders o
        WHERE DATE(o.created_at) BETWEEN '$startDate' AND '$endDate'
        $filterProduct
        ORDER BY o.created_at DESC
    ";

    $result = $conn->query($query);

    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['id'],
            $row['created_at'],
            $row['total'],
            $row['payment_method']
        ]);
    }
}

fclose($output);
exit;
?>

