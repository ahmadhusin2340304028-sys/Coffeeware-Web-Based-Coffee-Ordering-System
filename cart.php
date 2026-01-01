<?php
session_start();
include 'db_connect.php';

// Pastikan cart sudah ada
if (!isset($_SESSION['cart'])) {
  $_SESSION['cart'] = [];
}

// Proses aksi hapus item
if (isset($_GET['action']) && isset($_GET['id'])) {
  $id = $_GET['id'];

  if ($_GET['action'] == 'remove') {
    unset($_SESSION['cart'][$id]);
  }
  header("Location: cart.php");
  exit;
}

// Ambil data produk berdasarkan item di cart
$cartItems = [];
$totalHarga = 0;

if (!empty($_SESSION['cart'])) {
  $ids = implode(',', array_keys($_SESSION['cart']));
  $result = $conn->query("SELECT * FROM menu WHERE menu_id IN ($ids)");

  while ($row = $result->fetch_assoc()) {
    $qty = $_SESSION['cart'][$row['menu_id']];
    $subtotal = $row['price'] * $qty;
    $totalHarga += $subtotal;
    $row['qty'] = $qty;
    $row['subtotal'] = $subtotal;
    $cartItems[] = $row;
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CoffeeWare - Cart</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f8f5f2;
    }
    a{
      text-decoration: none;
      color: inherit;
    }
    .navbar {
      background-color: #fff;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .table img {
      width: 60px;
      border-radius: 8px;
    }
    .btn-remove {
      background-color: #d9534f;
      color: #fff;
      border: none;
      padding: 5px 10px;
      border-radius: 5px;
    }
    .btn-remove:hover {
      background-color: darkred;
    }
    .btn-back {
      background-color: #bfa288;
      color: #fff;
      border: none;
      padding: 8px 16px;
      border-radius: 5px;
    }
    .btn-back:hover {
      background-color: #a8856c;
    }
    .checkout-btn {
      background-color: #198754;
      color: #fff;
      border: none;
      padding: 10px 20px;
      border-radius: 5px;
      font-weight: bold;
    }
    .checkout-btn:hover {
      background-color: #146c43;
    }

    /* RESPONSIVE */
    @media (max-width: 768px) {
      .table thead {
        display: none;
      }
      .table, .table tbody, .table tr, .table td {
        display: block;
        width: 100%;
      }
      .table tr {
        margin-bottom: 1rem;
        border: 1px solid #ddd;
        border-radius: 10px;
        padding: 10px;
        background-color: #fff;
      }
      .table td {
        text-align: right;
        padding-left: 50%;
        position: relative;
      }
      .table td::before {
        content: attr(data-label);
        position: absolute;
        left: 10px;
        width: 45%;
        text-align: left;
        font-weight: bold;
        color: #555;
      }
      .checkout-btn {
        width: 100%;
      }
    }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar px-3 py-2">
  <div class="container-fluid d-flex justify-content-between align-items-center">
    <a href="menu.php" class="btn btn-light border-0 text-light fw-bold bg-danger rounded-3">← Kembali ke Menu</a>
    <h5 class="m-0">🛒 Keranjang Kamu</h5>
  </div>
</nav>

<div class="container mt-4">
  <?php if (empty($cartItems)): ?>
    <div class="alert alert-warning text-center shadow-sm p-4">
      Keranjang kamu kosong 😅 <br>
      <a href="menu.php" class="btn btn-primary mt-3">Lihat Menu</a>
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table align-middle">
        <thead class="table-light text-center">
          <tr>
            <th>No</th>
            <th>Gambar</th>
            <th>Nama Menu</th>
            <th>Kategori</th>
            <th>Harga</th>
            <th>Qty</th>
            <th>Subtotal</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($cartItems as $i => $item): ?>
          <tr>
            <td data-label="No" class="text-center"><?= $i+1 ?></td>
            <td data-label="Gambar" class="text-center">
              <img src="img/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
            </td>
            <td data-label="Nama Menu"><?= htmlspecialchars($item['name']) ?></td>
            <td data-label="Kategori"><?= htmlspecialchars($item['category']) ?></td>
            <td data-label="Harga" class="text-end">Rp <?= number_format($item['price'], 0, ',', '.') ?></td>
            <td data-label="Qty" class="text-center"><?= $item['qty'] ?></td>
            <td data-label="Subtotal" class="text-end fw-bold">Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></td>
            <td data-label="Aksi" class="text-center">
              <a href="?action=remove&id=<?= $item['menu_id'] ?>" class="btn-remove">Hapus</a>
            </td>
          </tr>
          <?php endforeach; ?>
          <tr>
            <td colspan="6" class="text-end fw-bold">Total Harga:</td>
            <td class="text-end fw-bold fs-5">Rp <?= number_format($totalHarga, 0, ',', '.') ?></td>
            <td></td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="text-end mt-4">
      <a class="checkout-btn w-100 w-md-auto" href="checkout.php">🧾 Proses Checkout</a>
    </div>
  <?php endif; ?>
</div>

</body>
</html>
