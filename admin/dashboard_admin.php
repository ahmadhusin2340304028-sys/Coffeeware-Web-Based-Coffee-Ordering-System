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



$queryTabelRecentOrders = "SELECT  
    u.name AS customer, 
    m.name AS menu, 
    oi.quantity AS Qty, 
    o.total_price AS price, 
    o.status, 
    o.created_at
FROM orders o
JOIN users u ON o.user_id = u.user_id
JOIN order_items oi ON o.order_id = oi.order_id
JOIN menu m ON oi.menu_id = m.menu_id
ORDER BY o.created_at DESC
LIMIT 10;
;
";

$sql_today = "SELECT COUNT(*) as total_orders_today
FROM orders
WHERE created_at BETWEEN CONCAT(CURDATE(), ' 00:00:00') 
                    AND CONCAT(CURDATE(), ' 23:59:59');
";

$result_today = $conn->query($sql_today);

if ($result_today) {
    $data = $result_today->fetch_assoc();
    $total_orders_today = $data['total_orders_today'];
} else {
    $total_orders_today = 0;
}

$newestOrders=query($queryTabelRecentOrders);


// --- Hitung Total Users ---
$query_users_total = "SELECT COUNT(*) AS total_users FROM users";
$result_users_total = mysqli_query($conn, $query_users_total);
$row_users_total = mysqli_fetch_assoc($result_users_total);
$total_users = $row_users_total['total_users'];

// --- Hitung Total Menu Items ---
$query_menu_total = "SELECT COUNT(*) AS total_menu FROM menu";
$result_menu_total = mysqli_query($conn, $query_menu_total);
$row_menu_total = mysqli_fetch_assoc($result_menu_total);
$total_menu = $row_menu_total['total_menu'];

// --- Hitung Total income ---
$query_income_total = "SELECT SUM(total_price) AS total_pendapatan FROM orders
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
  AND payment_status = 'paid';";
$result_income_total = mysqli_query($conn, $query_income_total);
$row_income_total = mysqli_fetch_assoc($result_income_total);
$total_income = $row_income_total['total_pendapatan'];

$qJual = $conn->query("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') AS bulan,
        SUM(total_price) AS pendapatan
    FROM orders
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY bulan ASC
");

$label_jual = [];
$data_jual   = [];

while ($row = $qJual->fetch_assoc()) {
    $label_jual[] = $row['bulan'];              // contoh: 2025-01
    $data_jual[]   = (int)$row['pendapatan'];    // contoh: 450000
}

// grafik produk
$q = $conn->query("
    SELECT 
    m.name AS nama_menu,
    SUM(oi.quantity) AS total_dipesan
FROM order_items AS oi
JOIN menu AS m 
    ON oi.menu_id = m.menu_id
JOIN orders AS o
    ON oi.order_id = o.order_id
WHERE 
    YEAR(o.created_at) = YEAR(CURRENT_DATE)
    AND MONTH(o.created_at) = MONTH(CURRENT_DATE)
GROUP BY 
    oi.menu_id, m.name
ORDER BY 
    total_dipesan DESC
LIMIT 10;

");

$labels = [];
$data   = [];

while ($row = $q->fetch_assoc()) {
    $labels[] = $row['nama_menu'];
    $data[]   = (int)$row['total_dipesan'];
}


?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>CoffeeWare Admin | Dashboard</title>
  <!-- Bootstrap 5 CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

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
</head>
<body>

  <!-- Sidebar -->
  <?php include "includes/sidebar.php"; ?>

  <!-- Main Content -->
  <div class="main-content">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light d-flex">
      <div class="container-fluid">
        <h5 class="mb-0">Dashboard</h5>
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
              <?php if (!isset($_SESSION['name']) || (isset($_SESSION['role']) && $_SESSION['role'] != 'admin')): ?>
                <p><a class="login-logout" href="../login.php">Login</a></p>
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


    <!-- Dashboard Cards -->
    <div class="container mt-2">
      <div class="row g-3">
        <div class="col-md-4">
          <div class="card shadow-sm border-0">
            <div class="card-body">
              <h6><i class="bi bi-cash-stack fs-4 mx-2"></i>  Total income in a month</h6>
              <h4 style="color: #0d6efd;" class="d-flex justify-content-end">Rp. <?= number_format($total_income,0,',','.') ?></h4>
              <small class="text-muted">-</small>
              
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card shadow-sm border-0">
            <div class="card-body">
              <h6><i class="bi bi-people fs-4 mx-2"></i>  Total Users</h6>
              <h3 class="d-flex justify-content-end"><?= $total_users ?></h3>
              <small class="text-muted">-</small>
            </div>
          </div>
        </div>        
        <div class="col-md-4">
          <div class="card shadow-sm border-0">
            <div class="card-body">
              <h6><i class="bi bi-cart4 fs-4 mx-2"></i>  Orders Today</h6>
              <h3 class="d-flex justify-content-end"><?=  "$total_orders_today"; ?></h3>
              <small class="text-muted">+<?= $total_orders_today ?> today</small>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Dashboard grafik -->
    <div class="container mt-2">
      <div class="row g-3">
        <div class="col-md-6" >
          <div class="card shadow-sm" style="background-color: #343a40;">
            <div class="card-header text-light">
              <h5 class="mb-0">Produk Terlaris Bulan ini</h5>
            </div>
            <div class="card-body">
                <div class="text-center p-2 border rounded"><canvas id="chartProduk"></canvas></div>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card shadow-sm" style="background-color: #343a40;">
            <div class="card-header text-white">
              <h5 class="mb-0">Tren Penjualan Bulanan</h5>
            </div>
            <div class="card-body">
                <div class="text-center p-2 border rounded"><canvas id="chartPenjualan"></canvas></div>
            </div>
          </div>
        </div>        
      </div>
    </div>

    <!-- Table Preview -->
    <div class="container mt-2">
      <div class="card shadow-sm border-0">
        <div class="card-header bg-white">
          <h6 class="mb-0">Recent Orders</h6>
        </div>
        <div class="card-body">
          <table class="table table-hover table-borderless">
            <thead>
              <tr>
                <th>#</th>
                <th>Customer</th>
                <th>Menu</th>
                <th>Total</th>
                <th>created_at</th>
                <th>Order Status</th>
                
              </tr>
            </thead>
            <tbody>
              <?php $no = 1; foreach($newestOrders as $n) : ?>
              <tr>
                <td><?=  $no; ?></td>
                <td><?= $n['customer']; ?></td>
                <td><?= $n['menu'];  ?></td>
                <td><?= $n['price'];  ?></td>
                <td><?= $n['created_at'];  ?></td>
                <td><?= $n['status'];  ?></td> 
                <?php $no++; ?>               
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <script>
      const label_jual = <?= json_encode($label_jual); ?>;
      const dataValues = <?= json_encode($data_jual); ?>;
      const productLabels = <?= json_encode($labels); ?>;
      const productData   = <?= json_encode($data); ?>;
  </script>
  <script>
  const ctx = document.getElementById('chartPenjualan').getContext('2d');

  new Chart(ctx, {
      type: 'line',
      data: {
          labels: label_jual,
          datasets: [{
              label: 'Pendapatan',
              data: dataValues,
              borderWidth: 2,
              tension: 0.4
          }]
      },
      options: {
          responsive: true,
          scales: {
              y: {
                  beginAtZero: true,
                  grid: {
                      display: false   // ❌ matikan grid Y
                  }
              },
              x: {
                  grid: {
                      display: false   // ❌ matikan grid X
                  }
              }
          }
      }
  });


  const ctx2 = document.getElementById('chartProduk').getContext('2d');

new Chart(ctx2, {
    type: 'bar',
    data: {
        labels: productLabels,
        datasets: [{
            label: 'Jumlah Dipesan',
            data: productData,
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                grid: { display: false }
            },
            x: {
                grid: { display: false }
            }
        }
    }
});
  </script>
</body>
</html>
