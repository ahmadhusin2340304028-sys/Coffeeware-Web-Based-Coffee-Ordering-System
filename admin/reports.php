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

// --- Hitung produk terlaris ---
$query_produk_laris = "SELECT 
    m.menu_id,
    m.name,
    SUM(oi.quantity) AS total_dipesan
FROM order_items oi
JOIN orders o ON oi.order_id = o.order_id
JOIN menu m ON oi.menu_id = m.menu_id
WHERE o.created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
GROUP BY m.menu_id, m.name
ORDER BY total_dipesan DESC
LIMIT 1;
";
$result_produk_laris = mysqli_query($conn, $query_produk_laris);
$row_produk_laris = mysqli_fetch_assoc($result_produk_laris);
$produk_laris = $row_produk_laris['name'];

// Hitung user yang daftar hari ini
// $query_users_today = "
//     SELECT COUNT(*) AS users_today 
//     FROM users 
//     WHERE DATE(created_at) = CURDATE()
// ";
// $result_users_today = mysqli_query($conn, $query_users_today);
// $row_users_today = mysqli_fetch_assoc($result_users_today);
// $users_today = $row_users_today['users_today'];

// --- Hitung Total Menu Items ---
$query_menu_total = "SELECT COUNT(*) AS total_menu FROM menu";
$result_menu_total = mysqli_query($conn, $query_menu_total);
$row_menu_total = mysqli_fetch_assoc($result_menu_total);
$total_menu = $row_menu_total['total_menu'];

// --- Hitung Total income ---
$query_income_total = "SELECT SUM(total_price) AS total_pendapatan FROM orders
WHERE created_at BETWEEN CURDATE()
            AND DATE_ADD(CURDATE(), INTERVAL 1 DAY) - INTERVAL 1 SECOND
  AND payment_status = 'paid';";
$result_income_total = mysqli_query($conn, $query_income_total);
$row_income_total = mysqli_fetch_assoc($result_income_total);
$total_income = $row_income_total['total_pendapatan'];

// --- Hitung Total income month---
$query_income_totalM = "SELECT SUM(total_price) AS total_pendapatan
FROM orders
WHERE created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01') 
  AND payment_status = 'paid';";
$result_income_totalM = mysqli_query($conn, $query_income_totalM);
$row_income_totalM = mysqli_fetch_assoc($result_income_totalM);
$total_incomeM = $row_income_totalM['total_pendapatan'];


/// grafik
// Query pendapatan per bulan untuk 12 bulan terakhir
$q = $conn->query("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') AS bulan,
        SUM(total_price) AS pendapatan
    FROM orders
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY bulan ASC
");

$labels = [];
$data   = [];

while ($row = $q->fetch_assoc()) {
    $labels[] = $row['bulan'];              // contoh: 2025-01
    $data[]   = (int)$row['pendapatan'];    // contoh: 450000
}

//list menu
$stmt = $conn->prepare("SELECT name FROM menu");
$stmt->execute();
$menus = $stmt->get_result();

?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>CoffeeWare Admin | Reports</title>
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
        <h5 class="mb-0">Reports</h5>
        <span class="date"><i class="bi bi-clock"></i> <?= date("D, d M y, h.i A") ?></span>
        <div class="d-flex align-items-center">
          <i class="bi bi-bell me-3 fs-5"></i>

          <!-- Akun -->
          <div class="account-dropdown position-relative">
            <button class="btn account-btn d-flex align-items-center">
              <i class="bi bi-person-circle fs-4 me-2"></i>
              <h6 class="mb-0">
                <?= isset($_SESSION['name']) ? "Hello, " . htmlspecialchars($_SESSION['name']) : ""; ?>
              </h6>
            </button>

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


    <div class="row">

      <!-- Filter Laporan -->
      <div class="col-md-12 mb-4">
        <div class="card shadow-sm">
          <div class="card-body">
            <h5 class="card-title text-primary"><i class="bi bi-funnel"></i> Filter Laporan CoffeeWare</h5>
            <hr>

            <form action="reports_action.php" method="get">
              <div class="row g-3">

                <div class="col-md-3">
                  <label for="reportType" class="form-label"><strong>Jenis Laporan</strong></label>
                  <div class="my-auto">
                    <select id="reportType" class="form-select" name="reportType">
                      <option selected>Penjualan Harian</option>
                      <option>Penjualan Mingguan</option>
                      <option>Penjualan Bulanan</option>
                      <option>Penjualan(custom)</option>
                      <option>Penjualan Per Produk</option>
                      <option>Riwayat Order</option>
                    </select>
                  </div>
                  <small class="text-muted" style="font-size: 10px;">*tanggal periode hanya berlaku untuk jenis:
                    <ul>
                    <li>Penjualan(custom)</li>
                    <li>Penjualan Per Produk</li>
                    <li>Riwayat Order</li>
                  </ul></small>
                  
                </div>

                <div class="col-md-3">
                  <label for="dateRange1"><strong>Periode</strong></label><br>
                  <label for="dateRange1" class="form-label" style="display: inline;">start</label>
                  <input type="date" id="dateRange" class="form-control mb-2" placeholder="start date" name="dateRange1" style="display: inline;">
                  <label for="dateRange2" class="form-label">end</label> 
                  <input type="date" id="dateRange" class="form-control" placeholder="end date" name="dateRange2">
                </div>

                <div class="col-md-3">
                  <label for="productFilter" class="form-label"><strong>Filter Kategori Menu</strong></label>
                  <select id="productFilter" class="form-select" name="product">
                    <option selected>Semua</option>
                    <?php  foreach($menus as $m):  ?> 
                      <option><?= $m['name']; ?></option>                                     
                    <?php  endforeach;  ?>                   
                  </select>
                </div>

                <div class="col-md-3">
                  <button type="submit" class="btn btn-primary d-block w-100 mb-2 rounded-5">
                      <i class="bi bi-eye"></i> Tampilkan
                  </button>

                  <button type="submit" formaction="export_csv.php" class="btn btn-success d-block w-100 rounded-5">
                      <i class="bi bi-download"></i> Unduh CSV
                  </button>
              </div>


              </div>
            </form>

          </div>
        </div>
      </div>


      <!-- Ringkasan -->
      <div class="col-lg-3 col-md-6 mb-4">
        <div class="card bg-primary text-white shadow-sm">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <div class="text-uppercase small">Today's Income</div>
                <h4 class="mb-0">Rp. <?= $total_income; ?></h4>
              </div>
              <i class="bi bi-cash fs-1"></i>
            </div>
          </div>
        </div>
      </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card bg-success text-white shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-uppercase small">Month's Income</div>
                        <h4 class="mb-0">Rp. <?= $total_incomeM; ?></h4>
                    </div>
                    <i class="bi bi-cash-stack fs-1"></i>
                    </div>
                </div>
            </div>
        </div>

      <div class="col-lg-3 col-md-6 mb-4">
        <div class="card bg-warning text-dark shadow-sm">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <div class="text-uppercase small">Total Order Today</div>
                <h4 class="mb-0"><?= $total_orders_today; ?></h4>
              </div>
              <i class="bi bi-receipt fs-1"></i>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-md-6 mb-4">
        <div class="card bg-info text-white shadow-sm">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <div class="text-uppercase small">Best-selling</div>
                <h4 class="mb-0"><?= $produk_laris; ?></h4>
              </div>
              <i class="bi bi-cup-hot fs-1"></i>
            </div>
          </div>
        </div>
      </div>

      


      <!-- Grafik Penjualan -->
      <div class="col-md-12 mb-4">
        <div class="card shadow-sm">
          <div class="card-header bg-white">
            <h5 class="mb-0">Tren Penjualan Bulanan</h5>
          </div>
          <div class="card-body">
            <div class="text-center p-5 border rounded" style="min-height: 300px;">
                <canvas id="chartPenjualan"></canvas>
            </div>
          </div>
        </div>
      </div>


      <!-- Tabel Laporan -->
      <div class="col-md-12" style="display: none;">
        <div class="card shadow-sm">
          <div class="card-header bg-white">
            <h5 class="mb-0">Detail Transaksi</h5>
          </div>
          <div class="card-body">
            <div class="table-responsive">

              <table class="table table-striped table-hover">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Tanggal</th>
                    <th>Pelanggan</th>
                    <th>Menu</th>
                    <th>Total</th>
                    <th>Metode</th>
                    <th>Status</th>
                    <th>Aksi</th>
                  </tr>
                </thead>

                <tbody>
                  <tr>
                    <td>INV-001</td>
                    <td>2025-11-17</td>
                    <td>Budi</td>
                    <td>Cappuccino</td>
                    <td>Rp 28.000</td>
                    <td>Cash</td>
                    <td><span class="badge bg-success">Selesai</span></td>
                    <td><button class="btn btn-sm btn-info text-white"><i class="bi bi-eye"></i></button></td>
                  </tr>

                  <tr>
                    <td>INV-002</td>
                    <td>2025-11-17</td>
                    <td>Rina</td>
                    <td>Americano, Croissant</td>
                    <td>Rp 45.000</td>
                    <td>QRIS</td>
                    <td><span class="badge bg-success">Selesai</span></td>
                    <td><button class="btn btn-sm btn-info text-white"><i class="bi bi-eye"></i></button></td>
                  </tr>

                  <tr>
                    <td>INV-003</td>
                    <td>2025-11-16</td>
                    <td>Lutfi</td>
                    <td>Latte</td>
                    <td>Rp 25.000</td>
                    <td>Cash</td>
                    <td><span class="badge bg-warning text-dark">Pending</span></td>
                    <td><button class="btn btn-sm btn-info text-white"><i class="bi bi-eye"></i></button></td>
                  </tr>
                </tbody>

              </table>

            </div>
          </div>
        </div>
      </div>

    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <script>
      const labels = <?= json_encode($labels); ?>;
      const dataValues = <?= json_encode($data); ?>;
  </script>
  <script>
  const ctx = document.getElementById('chartPenjualan').getContext('2d');

  new Chart(ctx, {
      type: 'line',
      data: {
          labels: labels,
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
  </script>




</body>
</html>
