<?php
include "db_connect.php";

$order_id = $_GET['order_id'];
$Om = $_GET['Om'];
$adr = $_GET['adr'];
$adrs = $_GET['adrs'];
$pesan = ""; 
$pesan2 = "";


$query = "
    SELECT 
        o.order_id,
        u.name AS customer_name,
        u.nomor,
        o.total_price,
        o.created_at
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    WHERE o.order_id = '".$order_id."'
";

$sql = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>COD Berhasil</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: url('img/coffee-back.jpg') center/cover no-repeat fixed;
            font-family: "Poppins", sans-serif;
            color: #4b2e2b;
        }

        .center-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .card {
            background: rgba(255, 248, 240, 0.92);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .coffee-decor {
            font-size: 40px;
            text-align: center;
            margin-bottom: 10px;
        }

        .btn-back {
            background-color: #b08968;
            border: none;
        }

        .btn-back:hover {
            background-color: #9b775c;
        }
    </style>
</head>

<body>
<?php

if($Om == 'delivery') : 
    $pesan = "Pesanan sedang diproses untuk pengantaran. Estimasi waktu: 30–60 menit sesuai jarak dari cafe.";
    $pesan2 = " <div class='d-flex'>
                    <p class='me-3'>Address:</p>
                    <ul class='list-unstyled'>
                        <li>$adr</li>
                        <li><small class='text-muted'>$adrs</small></li>
                    </ul>
                </div>";
else:     
    $pesan = "<p>Pesanan sedang diproses. Pesanan akan selesai paling lama 30 menit. Silahkan lakukan pick-up di cafe ya!.</p>";
endif; 


?>
<div class="center-wrapper">
    <div class="card">
        <div class="text-center">
            <img src="img/order_done.svg" alt="" width="30%" class="mb-2">
            <h4>Hore!! Pesanan Berhasil!</h4>
            <p class="px-4"><?= $pesan; ?></p>
        </div>
        <p><strong>Order ID : <?= $order_id; ?></strong> </p>
        <p>Order Method: <?= $Om; ?></p>   
        <?= $pesan2; ?>     
        <p>Total: <span style="color: blue;">Rp <?= number_format($data['total_price'], 0, ',', '.') ?></span></p>
        <a href="menu.php" class="btn btn-back w-100 mt-3">Kembali ke Menu</a>
    </div>
</div>

</body>
</html>
