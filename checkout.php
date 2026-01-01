<?php
session_start();
require 'db_connect.php';
if (!isset($_SESSION["login"])) {
    echo "<script>
            alert('You need to login first');
            window.location.href = 'login.php';
          </script>";
    exit;
}

// ambil cart dari session
$cart = $_SESSION['cart'] ?? []; // format: [menu_id => qty, ...]

// Ambil data menu untuk yang ada di cart
$cartItems = [];
$subtotal = 0.0;
if (!empty($cart)) {
    $ids = implode(',', array_keys($cart));
    // safety: jika tidak ada id, hindari query
    $result = $conn->query("SELECT * FROM menu WHERE menu_id IN ($ids)");
    while ($row = $result->fetch_assoc()) {
        $qty = $cart[$row['menu_id']];
        $row['qty'] = $qty;
        $row['subtotal'] = $row['price'] * $qty;
        $subtotal += $row['subtotal'];
        $cartItems[] = $row;
    }
}

if (isset($_POST['checkout'])) {
      $user_id = $_SESSION['user_id'];        // ID pengguna yang sedang login
    $payment_method = $_POST['payment'];    // Misal: 'cash'
    $total_price = $_POST['subtotal'];   // Total dari cart
    $status = 'pending';
    $order_source = 'online';
    // data non database      
    $adr = $_POST['address']; 
    $Om = $_POST['order_method'];      
    $adrs = $_POST['addressSub'];

    // 1️⃣ Insert ke tabel orders
    $sql = "INSERT INTO orders (order_id, user_id, total_price, payment_method, payment_status, order_source, status, created_at)
            VALUES ('', '$user_id', '$total_price', '$payment_method', 'pending', '$order_source', '$status', NOW())";
    mysqli_query($conn, $sql);

    // Ambil ID order yang baru dimasukkan
    $order_id = mysqli_insert_id($conn);

    // 2️⃣ Insert ke tabel order_items untuk tiap menu di cart
    foreach ($_SESSION['cart'] as $menu_id => $qty) {
        $sql_item = "INSERT INTO order_items (order_item_id, order_id, menu_id, quantity)
                     VALUES ('', '$order_id', '$menu_id', '$qty')";
        mysqli_query($conn, $sql_item);
    }

    // Hapus cart setelah checkout
    unset($_SESSION['cart']);

    if ($payment_method == "cash" ){
      header("location:cod_success.php?order_id=$order_id&Om=$Om&adr=$adr&adrs=$adrs");
    }else{
      header("location:./midtrans/examples/snap/checkout-process-simple-version.php?order_id=$order_id");
    }

    echo "<script>alert('Order berhasil dibuat!'); </script>";
}



// contoh shipping: jika delivery pilih, nanti di server hitung jarak. Untuk demo pakai fixed shipping
$default_shipping = 8000;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Checkout - CoffeeWare</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { font-family: 'Poppins', sans-serif; background-color: #f7f7f7; }
    .section-header { background-color: #e3c4a8; font-weight: 600; padding: 8px 12px; border-radius: 8px 8px 0 0; }
    .card-section { background-color: #fff; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); margin-bottom: 15px; }
    .order-item { background-color: #e3c4a8; border-radius: 10px; padding: 12px; margin-bottom: 10px; }
    .bottom-summary { background-color: #e3c4a8; padding: 10px 15px; border-radius: 0 0 8px 8px; display: flex; justify-content: space-between; align-items: center; }
    .btn-order { background-color: red; color: #fff; font-weight: 600; border-radius: 8px; padding: 10px 25px; border: none; }
    .btn-order:hover { background-color: #b30000; }
    .left { min-height: 70vh; }
  </style>
</head>
<body>

<nav class="navbar px-4 py-2 d-flex justify-content-between align-items-center">
  <a href="menu.php" class="btn btn-light border-0 text-light fw-bold bg-danger rounded-3">← back to menu</a>
  <h5 class="m-0">Checkout</h5>
  <div class="text-muted small">Sun, <?= date('d M y, H.i A') ?></div>
</nav>

<div class="container mt-4">
  <div class="row g-3">
    <!-- LEFT: Orders -->
    <div class="col-lg-6 left">
      <div class="card-section">
        <div class="section-header">Orders Menu</div>
        <div class="p-3">
          <?php if (empty($cartItems)): ?>
            <div class="alert alert-warning">Keranjang kosong. <a href="menu.php">Kembali belanja</a></div>
          <?php else: ?>
            <?php foreach ($cartItems as $it): ?>
              <div class="order-item d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="mb-1"><?= htmlspecialchars($it['name']) ?></h6>
                  <div class="small text-muted"><?= htmlspecialchars($it['description']) ?></div>
                </div>
                <div class="text-end">
                  <div>Rp <?= number_format($it['price'],0,',','.') ?></div>
                  <div class="small">x<?= (int)$it['qty'] ?></div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <div class="bottom-summary">
          <span><?= array_sum($cart) ?> Items</span>
          <span>Rp <?= number_format($subtotal,0,',','.') ?></span>
        </div>
      </div>
    </div>

    <!-- RIGHT: Form Order -->
    <div class="col-lg-6">
      <form id="orderForm" method="post" action="">
        <input type="hidden" name="subtotal" value="<?= htmlspecialchars($subtotal) ?>">
        <div class="card-section mb-3">
          <div class="section-header">Order Method</div>
          <div class="p-3">
            <div class="form-check">
              <input class="form-check-input" type="radio" name="order_method" id="radio-pickup" value="pickup" checked>
              <label class="form-check-label" for="pickup">Pick-up</label>
            </div>
            <div class="form-check mt-2">
              <input class="form-check-input" type="radio" name="order_method" id="radio-delivery" value="delivery">
              <label class="form-check-label" for="delivery">Delivery (around the city)</label>
            </div>
          </div>
        </div>

        <div class="card-section mb-3" id="address-container" style="display:none;">
          <div class="section-header">Address</div>
          <div class="p-3">
            <input type="text" class="form-control mb-2" name="address" placeholder="Alamat lengkap (jalan, kota)">
            <input type="text" class="form-control mb-2" name="addressSub" placeholder="Koordinat (lat,lng) - optional">
          </div>
        </div>

        <div class="card-section mb-3">
          <div class="section-header">Payment Method</div>
          <div class="p-3">
            <div class="form-check">
              <input class="form-check-input" type="radio" name="payment" id="cod" value="cash" checked>
              <label class="form-check-label" for="cod">Cash/COD</label>
            </div>
            <div class="form-check mt-2">
              <input class="form-check-input" type="radio" name="payment" id="qris" value="qris/transfer">
              <label class="form-check-label" for="qris">Qris/Transfer</label>
            </div>        
          </div>
        </div>

        <div class="card-section mb-3">
          <div class="section-header">Payment Details</div>
          <div class="p-3">
            <div class="d-flex justify-content-between"><span>Order Subtotal</span><span id="displaySubtotal">Rp <?= number_format($subtotal,0,',','.') ?></span></div>
            <div class="d-flex justify-content-between"><span>Shipping Subtotal</span><span id="displayShipping"></span></div>
            <hr>
            <div class="d-flex justify-content-between fw-bold"><span>Total</span><span id="displayTotal">Rp <?php  echo number_format($subtotal,0,',','.') ?></span></div>
          </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3 p-2 rounded" style="background-color: #e3c4a8;">
          <span class="fw-bold" id="summaryTotal">Rp <?= number_format($subtotal,0,',','.') ?> total</span>
          <div>
            <a href="cart.php" class="btn btn-light border me-2">manage order</a>
            <button type="submit" id="btnOrder" class="btn btn-order" name= "checkout" >Order</button>
          </div>
        </div>
      </form>
    </div>

  </div>
</div>

<!-- Modal hasil (tetap gunakan order_success.php juga) -->
<div class="modal fade" id="orderModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Order Result</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="orderResult"></div>
      <div class="modal-footer">
        <a href="index.php" class="btn btn-secondary">Home</a>
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const radioDelivery = document.getElementById("radio-delivery");
    const radioPickup = document.getElementById("radio-pickup");
    const addressContainer = document.getElementById("address-container");

    function checkDelivery() {
        if (radioDelivery.checked) {
            addressContainer.style.display = "block";
        } else {
            addressContainer.style.display = "none";
        }
    }

    radioDelivery.addEventListener("change", checkDelivery);
    radioPickup.addEventListener("change", checkDelivery);
</script>

</body>
</html>
