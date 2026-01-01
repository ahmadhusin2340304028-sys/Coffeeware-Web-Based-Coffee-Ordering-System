<?php
session_start();
require 'db_connect.php';
if (!isset($_SESSION['login'])) {
    header('Location: login.php');
    exit;
}
$order_id = intval($_GET['order_id'] ?? 0);
if (!$order_id) {
    echo "Order ID invalid";
    exit;
}

// ambil order
$stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ?");
$stmt->bind_param("i",$order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$order) {
    echo "Order not found";
    exit;
}

// ambil items
$stmt2 = $conn->prepare("SELECT oi.*, m.name FROM order_items oi JOIN menu m ON oi.menu_id = m.menu_id WHERE oi.order_id = ?");
$stmt2->bind_param("i",$order_id);
$stmt2->execute();
$items_res = $stmt2->get_result();
$items = $items_res->fetch_all(MYSQLI_ASSOC);
$stmt2->close();

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Order Success</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
  <div class="card p-3">
    <h4>Order #<?= htmlspecialchars($order['order_id']) ?> berhasil!</h4>
    <p>Status: <strong><?= htmlspecialchars($order['status']) ?></strong></p>

    <h6>Items</h6>
    <ul class="list-group mb-3">
      <?php foreach ($items as $it): ?>
        <li class="list-group-item d-flex justify-content-between">
          <div>
            <?= htmlspecialchars($it['name']) ?> <small class="text-muted">x<?= (int)$it['qty'] ?></small>
          </div>
          <div>Rp <?= number_format($it['subtotal'],0,',','.') ?></div>
        </li>
      <?php endforeach; ?>
    </ul>

    <div class="row">
      <div class="col-md-6">
        <p><strong>Payment Method:</strong> <?= htmlspecialchars(strtoupper($order['payment_method'])) ?></p>
        <?php if ($order['payment_method'] === 'qris'): ?>
          <?php
            $qfile = __DIR__ . '/qrcodes/' . $order['payment_ref'];
            $qurl = file_exists($qfile) ? 'qrcodes/' . $order['payment_ref'] : $order['payment_ref'];
          ?>
          <div class="mb-2">
            <img src="<?= htmlspecialchars($qurl) ?>" alt="QRIS" style="max-width:300px">
          </div>
          <a href="<?= htmlspecialchars($qurl) ?>" class="btn btn-primary" download="qris_order_<?= $order['order_id'] ?>.png">Download QR Code</a>
        <?php elseif ($order['payment_method'] === 'bank'): ?>
          <p>Virtual Account: <strong id="vaCode"><?= htmlspecialchars($order['payment_ref']) ?></strong></p>
          <button class="btn btn-outline-secondary" id="copyVa">Copy VA</button>
        <?php else: ?>
          <p>Pembayaran saat diterima (COD).</p>
        <?php endif; ?>
      </div>

      <div class="col-md-6">
        <p><strong>Order Method:</strong> <?= htmlspecialchars(ucfirst($order['method'])) ?></p>
        <?php if ($order['method'] === 'delivery'): ?>
          <div class="alert alert-info">
            Pesanan sedang diproses untuk pengantaran. Estimasi waktu: 30–60 menit.<br>
            Alamat: <?= nl2br(htmlspecialchars($order['address'])) ?>
          </div>
        <?php else: ?>
          <div class="alert alert-success">
            Pilih pick-up. Pesanan paling lama selesai <strong>15 menit</strong>.
          </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="mt-3">
      <a href="index.php" class="btn btn-secondary">Kembali ke Home</a>
    </div>
  </div>
</div>

<script>
document.getElementById('copyVa')?.addEventListener('click', function(){
  const va = document.getElementById('vaCode').innerText;
  navigator.clipboard.writeText(va).then(()=> {
    alert('VA disalin: ' + va);
  }).catch(()=> alert('Gagal menyalin VA'));
});
</script>
</body>
</html>
