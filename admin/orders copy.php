<?php 
session_start();
require '../db_connect.php';
require 'fungsi.php';

if (isset($_COOKIE["id"]) && isset($_COOKIE["key"])) {
    $id = $_COOKIE["id"];
    $key = $_COOKIE["key"];

    $hasil = mysqli_query($conn, "SELECT * FROM users WHERE user_id = '$id'");
    $row = mysqli_fetch_assoc($hasil);

    // Pastikan data user ditemukan
    if ($row) {
        // Cek apakah key cookie cocok dengan hash dari nama user
        if ($key === hash("sha256", $row['name'])) {

            // Set session login
            $_SESSION["login"] = true;
            $_SESSION['name']  = $row['name'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['nomor'] = $row['nomor'];
            $_SESSION['role']  = $row['role'];
        }
    }
}

if (!isset($_SESSION["login"])) {
  echo "<script>
          alert('You need to login first');
          window.location.href = '../login.php';
        </script>";
  exit;
}

if (isset($_SESSION['role']) && $_SESSION['role'] === 'customer') {
    // Optional: redirect admin ke halaman admin
    header("Location: ../index.php");
    exit;
}



//tampil
$query = "SELECT 
    o.order_id,
    u.name AS customer,
    o.total_price,
    o.payment_method,
    o.payment_status,
    o.status,
    DATE_FORMAT(o.created_at, '%Y-%m-%d %H:%i') AS order_date
FROM orders AS o
LEFT JOIN users AS u ON o.user_id = u.user_id
ORDER BY o.created_at DESC;
";
$ordersData = query($query);



// edit user
if ( isset($_POST['submit']) ){
        if ( editUser($_POST) > 0 )  {
    echo "<script>
            alert('data berhasil didiubah');
            window.location.href = 'users.php';
        </script>";
    } else {
        echo "<script>
            alert('data gagal diubah: " . mysqli_error($conn) . "');
        </script>";
    }
    }

if (isset($_POST['submitEditOrder'])) {

    $order_id        = $_POST['order_id'];
    $payment_method  = $_POST['payment_method'];
    $payment_status  = $_POST['payment_status'];
    $order_status    = $_POST['status'];   // kolom bernama "status"

    // Query untuk update orders
    $query = "UPDATE orders SET
                payment_method = '$payment_method',
                payment_status = '$payment_status',
                status = '$order_status'
              WHERE order_id = '$order_id'";

    if (mysqli_query($conn, $query)) {

        echo "<script>
                alert('Order berhasil diperbarui!');
                window.location.href = 'orders.php';
              </script>";
    } else {
        echo "<script>
                alert('Gagal memperbarui order!');
                window.location.href = 'orders.php';
              </script>";
    }
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Admin - Orders | KopiWare</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://unpkg.com/bootstrap-table@1.21.0/dist/bootstrap-table.min.css">
</head>
<style>
    body {
      background-color: #f8f9fa;
    }
    .sidebar {
      width: 250px;
      height: 100vh;
      position: fixed;
      background-color: #2c2f33;
      color: white;
      padding-top: 20px;
    }
    .sidebar a {
      color: #ddd;
      text-decoration: none;
      display: block;
      padding: 10px 20px;
      border-radius: 8px;
      margin: 4px 8px;
    }
    .sidebar a:hover, .sidebar a.active {
      background-color: #343a40;
      color: #fff;
    }
    .main-content {
      margin-left: 250px;
      padding: 20px;
    }
    .navbar {
      background-color: #fff;
      border-bottom: 1px solid #dee2e6;
    }      
    .account-dropdown {
      position: relative;
      display: inline-block;
    }

    .account-dropdown .dropdown-content {
      display: none;
      position: absolute;
      right: 0;
      background-color: white;
      min-width: 200px;
      box-shadow: 0px 8px 16px rgba(0,0,0,0.2);
      padding: 10px;
      border-radius: 10px;
      z-index: 10;
    }

    .account-dropdown .dropdown-content p {
      margin: 8px 0;
      padding: 5px 10px;
    }

    .account-dropdown .dropdown-content a {
      color: black;
      text-decoration: none;
    }

    .account-dropdown .dropdown-content a:hover {
      color: #007bff;
    }

    /* Saat ikon 👤 di-hover, tampilkan dropdown */
    .account-dropdown:hover .dropdown-content {
      display: block;
    }

    /* Styling tambahan opsional */
    .account-btn {
      background: none;
      border: none;
      font-size: 1.5rem;
    }

    .account-btn:hover {
      cursor: pointer;
    }
</style>
<body>
 
  <!-- Sidebar -->
  <?php include "includes/sidebar.php"; ?>

  <!-- Main Content -->
  <div class="main-content">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light d-flex" >
      <div class="container-fluid">
        <h5 class="mb-0">Orders</h5>
        <span class="date"><i class="bi bi-clock"></i> <?= date("D, d M y, h.i A") ?></span>
        <div class="d-flex align-items-center">
          <i class="bi bi-bell me-3 fs-5"></i>

          <!-- Tombol akun dan dropdown -->
          <div class="account-dropdown position-relative">
            <button class="btn account-btn d-flex align-items-center">
              <i class="bi bi-person-circle fs-4 me-2"></i>
              <h6 class="mb-0">
                <?= isset($_SESSION['name']) ? "Hello, " . htmlspecialchars($_SESSION['name']): ""; ?>
              </h6>
            </button>

            <!-- Dropdown -->
            <div class="dropdown-content">
              <?php if (!isset($_SESSION['name'])): ?>
                <p><a class="login-logout" href="login.php">Login</a></p>
              <?php else: ?>
                <p><strong><?= htmlspecialchars($_SESSION['name']); ?></strong></p>
                <p><?= htmlspecialchars($_SESSION['email']); ?></p>
                <p><?= htmlspecialchars($_SESSION['nomor']); ?></p>
                <p><a class="login-logout" href="../logout.php">Logout</a></p>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </nav>

    <!-- Table Preview -->
    <div class="container mt-4">          
          <div id="toolbar">
            <button id="btn-add" class="btn btn-primary"
              data-bs-toggle="modal"
              data-bs-target="#addOrderModal">
              <i class="bi bi-plus-lg"></i> Add New Order
            </button>
          </div>

          <table class="table table-hover table-responsive small"
          data-toggle="table" data-search="true" data-pagination="true" data-toolbar="#toolbar">
            <thead>
              <tr>
                <th>#</th>
                <th>ID</th>
                <th>Customer</th>
                <th>Total Price</th>
                <th>Payment Method</th>
                <th>Payment Status</th>
                <th>Order Status</th>
                <th>Order Time</th>
                <th>Action</th>
                
              </tr>
            </thead>
            <tbody>
              <?php $no = 1; foreach($ordersData as $n) : ?>
              <tr>
                <td><?=  $no; ?></td>
                <td><?= $n['order_id']; ?></td>
                <td><?= $n['customer'];  ?></td>
                <td><?= $n['total_price'];  ?></td>
                <td><?= $n['payment_method'];  ?></td>
                <td><?= $n['payment_status'];  ?></td>
                <td><?= $n['status'];  ?></td>
                <td><?= $n['order_date'];  ?></td>
                
                <td>
                <a href="orders.php?order_id=<?= $n['order_id'] ?>&detail=true" 
                  class="btn btn-light btn-sm rounded-5 border">
                  <i class="bi bi-eye"></i>
                </a> 

                 <!-- Tombol Edit -->
                <a href="orders.php?order_id=<?= $n['order_id'] ?>&edit=true" 
                  class="btn btn-warning btn-sm rounded-5">
                  <i class="bi bi-pencil"></i>
                </a> 

                <a href="hapusOrder.php?id=<?= $n['order_id']; ?>" class="btn btn-danger btn-sm rounded-5" onclick="return confirm('Yakin hapus order ini?')"><i class="bi bi-trash"></i></a>
                </td>
                <?php $no++; ?>               
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          
        </div>
      </div>
    </div>
  </div>


  <!-- Modal -->
  <div class="modal fade <?= isset($_GET['order_id'] ) && isset($_GET['edit']) ? 'show d-block' : '' ?>" id="editOrderModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5>Edit Order</h5>
          <a type="button" class="btn-close" href="orders.php"></a>
        </div>
        <div class="modal-body">
          <?php
          // Ambil data user kalau ada user_id di GET
          if (isset($_GET['order_id'])) {
              $id = $_GET['order_id'];
              $result = mysqli_query($conn, "SELECT * FROM orders WHERE order_id = $id");
              $userEdit = mysqli_fetch_assoc($result);

          }
          ?>
          <form action="orders.php" method="post">
            <input type="hidden" name="order_id" value="<?= $userEdit['order_id'] ?? '' ?>">
            <label class="form-label">Metode Pembayaran</label>
              <select class="form-select" name="payment_method" required>
                <option value="<?= $userEdit['payment_method']; ?>" selected><?= $userEdit['payment_method']; ?></option>
                <option value="cash">Cash</option>
                <option value="qris/transfer">Qris/Transfer</option>
              </select>
            <label class="form-label">Status Pembayaran</label>
              <select class="form-select" name="payment_status" required>
                <option value="<?= $userEdit['payment_status']; ?>" selected><?= $userEdit['payment_status']; ?></option>
                <option value="pending">pending</option>
                <option value="paid">paid</option>
                <option value="refunded">rufunded</option>
              </select>
            <label class="form-label">Status Pesanan</label>
              <select class="form-select" name="status" required>
                <option value="<?= $userEdit['status']; ?>" selected><?= $userEdit['status']; ?></option>
                <option value="processing">processing</option>
                <option value="completed">completed</option>
                <option value="cancelled">cancelled</option>
              </select>    
            <div class="modal-footer">
              <a type="button" class="btn btn-secondary" style="text-decoration: none;" href="orders.php">batal</a>
              <button type="submit" class="btn btn-primary" name="submitEditOrder">Simpan</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

                <!-- Modal Detail Order -->
<div class="modal fade <?= isset($_GET['order_id'] ) && isset($_GET['detail']) ? 'show d-block' : '' ?>" id="orderDetailModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Detail Order #<span id="orderIdTitle"></span></h5>
        <a href="orders.php" ype="button" class="btn-close"></a>
      </div>
      <div class="modal-body">
        <div id="orderInfo">
        <?php
        // Ambil data user kalau ada user_id di GET
        if (isset($_GET['order_id'])) {
            $id = $_GET['order_id'];
            $order = query("SELECT 
          o.order_id,
          u.name AS customer_name,
          m.name AS menu_name,
          m.category,
          m.price AS menu_price,
          oi.quantity,
          (m.price * oi.quantity) AS subtotal,
          o.total_price,
          o.payment_method,
          o.payment_status,
          o.order_source,
          o.status,
          o.created_at
      FROM orders o
      JOIN users u ON o.user_id = u.user_id
      JOIN order_items oi ON o.order_id = oi.order_id
      JOIN menu m ON oi.menu_id = m.menu_id
      WHERE o.order_id = '$id'");

        }
        ?>
          <p><strong>Customer:</strong> <span id="customerName"><?= $order[0]['customer_name']; ?></span></p>
          <p><strong>Metode Pembayaran:</strong> <span id="paymentMethod"><?= $order[0]['payment_method']; ?></span></p>
          <p><strong>Status Pembayaran:</strong> <span id="paymentMethod"><?= $order[0]['payment_status']; ?></span></p>
          <p><strong>Status Pesanan:</strong> <span id="orderStatus"><?= $order[0]['status']; ?></span></p>
          <p><strong>Waktu pemesanan:</strong> <span id="orderDate"><?= $order[0]['created_at']; ?></span></p>
        </div>
        <hr>
        <table class="table table-striped">
          <thead>
            <tr>
              <th>#</th>
              <th>Menu</th>
              <th>Kategori</th>
              <th>Qty</th>
              <th>Harga</th>
              <th>Subtotal</th>
            </tr>
          </thead>
          <tbody id="orderItemsTable">
            <?php $no = 1; foreach($order as $n) : ?>
              <tr>
                <td><?=  $no; ?></td>
                <td><?= $n['menu_name']; ?></td>
                <td><?= $n['category'];  ?></td>
                <td><?= $n['quantity'];  ?></td>
                <td><?= $n['menu_price'];  ?></td>
                <td><?= $n['subtotal'];  ?></td>               
                <?php $no++; ?>               
              </tr>
              <?php endforeach; ?>
          </tbody>
        </table>
        <div class="text-end">
          <strong>Total: Rp <span id="orderTotal"><?= $order[0]['total_price']; ?></span></strong>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Tambah Order Offline -->
<div class="modal fade" id="addOrderModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Tambah Order</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <form id="offlineOrderForm" action="process_offline_order.php" method="POST">
        <div class="modal-body">
          <!-- Bagian 1: Data Pelanggan -->
          <h6 class="mb-2">Data Pelanggan</h6>
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Id Pelanggan</label>
              <input type="text" name="user_id" class="form-control"
                value="1" placeholder="Offline Customer ID">
            </div>            
          </div>
           <!-- ID Offline Customer -->
          <input type="hidden" name="order_source" value="offline">

          <!-- Bagian 2: Informasi Transaksi -->
          <h6 class="mb-2">Informasi Pembayaran</h6>
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Metode Pembayaran</label>
              <select class="form-select" name="payment_method" required>
                <option value="cash" selected>Cash</option>
                <option value="transfer">Transfer</option>
                <option value="qris">QRIS</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Status Pembayaran</label>
              <select class="form-select" name="payment_status" required>
                <option value="pending">Pending</option>
                <option value="paid">Paid</option>
              </select>
            </div>
          </div>

          <!-- Bagian 3: Detail Menu Pesanan -->
          <h6 class="mb-2">Menu Pesanan</h6>
          <div id="menuItems">
            <div class="menu-item row mb-2">
              <div class="col-md-5">
                <select class="form-select menu-select" name="menu_id[]" required onchange="updatePrice(this)">
                  <option value="">Pilih Menu</option>
                  <option value="4" data-price="18000">Espresso - 18000</option>
                  <option value="5" data-price="20000">Americano - 20000</option>
                  <option value="6" data-price="25000">Cappuccino - 25000</option>
                  <option value="7" data-price="27000">Matcha Latte - 27000</option>
                  <option value="8" data-price="25000">Chocolate Latte - 25000</option>
                  <option value="9" data-price="26000">Red Velvet Latte - 26000</option>
                  <option value="10" data-price="18000">French Fries - 18000</option>
                  <option value="11" data-price="20000">Onion Rings - 20000</option>
                  <option value="12" data-price="25000">Chicken Wings - 25000</option>
                  <option value="13" data-price="32000">Chicken Rice Bowl - 32000</option>
                  <option value="14" data-price="35000">Beef Blackpepper Rice - 35000</option>
                  <option value="15" data-price="33000">Spaghetti Bolognese - 33000</option>
                  <option value="16" data-price="28000">Mocha - 28000</option>
                  <option value="17" data-price="18000">Lemon Tea - 18000</option>
                </select>
              </div>
              <div class="col-md-2">
                <input type="number" class="form-control quantity" name="quantity[]" min="1" value="1" onchange="calcSubtotal(this)">
              </div>
              <div class="col-md-3">
                <input type="text" class="form-control price" name="price[]" placeholder="Harga" readonly>
              </div>
              <div class="col-md-2 text-end">
                <button type="button" class="btn btn-danger btn-sm" onclick="removeMenu(this)">Hapus</button>
              </div>
            </div>
          </div>

          <div class="text-end mb-3">
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="addMenu()">+ Tambah Menu</button>
          </div>

          <!-- Total Harga -->
          <div class="row">
            <div class="col-md-8 text-end fw-bold">
              Total Harga:
            </div>
            <div class="col-md-4">
              <input type="text" id="total_price" name="total_price" class="form-control" readonly value="0">
            </div>
          </div>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan Order</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Script Dinamis Menu dan Kalkulasi -->
<script>
function updatePrice(select) {
  const price = select.options[select.selectedIndex].getAttribute('data-price');
  const row = select.closest('.menu-item');
  const priceInput = row.querySelector('.price');
  priceInput.value = price || 0;
  calcTotal();
}

function calcSubtotal(input) {
  const row = input.closest('.menu-item');
  const qty = parseInt(input.value) || 1;
  const price = parseInt(row.querySelector('.price').value) || 0;
  row.querySelector('.price').value = price * qty;
  calcTotal();
}

function calcTotal() {
  let total = 0;
  document.querySelectorAll('.price').forEach(p => {
    total += parseInt(p.value) || 0;
  });
  document.getElementById('total_price').value = total;
}

function addMenu() {
  const container = document.getElementById('menuItems');
  const clone = container.querySelector('.menu-item').cloneNode(true);
  clone.querySelectorAll('input, select').forEach(el => el.value = '');
  container.appendChild(clone);
}

function removeMenu(button) {
  const container = document.getElementById('menuItems');
  if (container.querySelectorAll('.menu-item').length > 1) {
    button.closest('.menu-item').remove();
    calcTotal();
  }
}
</script>


  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://unpkg.com/bootstrap-table@1.21.0/dist/bootstrap-table.min.js"></script>
   <script src="https://cdn.jsdelivr.net/npm/jquery/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
    <script src="https://unpkg.com/bootstrap-table@1.21.0/dist/bootstrap-table.min.js"></script>
  
  
</body>
</html>
