<?php
session_start();
require 'db_connect.php';



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


error_reporting(0);
ini_set('display_errors', 0);

// Ambil kategori unik
$categories = [];
$result = $conn->query("SELECT DISTINCT category FROM menu ORDER BY category");
while ($row = $result->fetch_assoc()) {
  $categories[] = $row['category'];
}

// Ambil kategori aktif (default: Coffee)
$activeCategory = $_GET['category'] ?? 'Coffee';

if (isset($_GET['category'])){
  $cat = $_GET['category'];
$jumlah = $conn->query("SELECT COUNT(*) FROM menu WHERE category='$cat'")->fetch_row()[0];
}

// Ambil menu berdasarkan kategori
$stmt = $conn->prepare("SELECT * FROM menu WHERE category = ?");
$stmt->bind_param("s", $activeCategory);
$stmt->execute();
$menus = $stmt->get_result();

// Hitung total item di keranjang
if (!isset($_SESSION['cart'])) 
  $_SESSION['cart'] = [];
  $totalItems = array_sum($_SESSION['cart']);

// Proses tambah/kurang item
if (isset($_GET['action']) && isset($_GET['id'])) {

  $id = $_GET['id'];
  if ($_GET['action'] == 'add') {
    $_SESSION['cart'][$id] = ($_SESSION['cart'][$id] ?? 0) + 1;
  } elseif ($_GET['action'] == 'minus' && isset($_SESSION['cart'][$id])) {
    $_SESSION['cart'][$id]--;
    if ($_SESSION['cart'][$id] <= 0) unset($_SESSION['cart'][$id]);
  }
  header("Location: menu.php?category=" . $activeCategory);
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CoffeeWare Menu</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
  font-family: 'Poppins', sans-serif;
  background-color: #f5f5f5;
  overflow-x: hidden;
}
  a{
    text-decoration: none;
    color: inherit;
    
  }

/* ===== SIDEBAR ===== */
.sidebar {
  position: fixed;           /* ✅ tetap di tempat */
  top: 0;
  left: 0;
  width: 230px;
  height: 100vh;
  background-color: #d9b89b;
  padding-top: 20px;
  transition: all 0.3s ease;
  z-index: 1050;
}

.sidebar .nav-link {
  color: #000;
  font-weight: 500;
  margin-bottom: 10px;
}

.sidebar .nav-link.active {
  background-color: #3e2f25;
  color: #fff;
}

/* ===== MAIN CONTENT AREA ===== */
.main-content {
  margin-left: 230px; /* ✅ kasih ruang biar nggak ketimpa sidebar */
  transition: margin-left 0.3s ease;
}

/* ===== NAVBAR ===== */
.navbar {
  background-color: #fff;
  box-shadow: 0 2px 5px rgba(0,0,0,0.1);
  position: sticky;
  top: 0;
  z-index: 100;
}

.date { color: #333; }

/* ===== CARD MENU ===== */
.card {
  position: relative;
  border: 1px solid #ccc;
  border-radius: 10px;
}
.card-img-top {
  width: 100%;
  height: 180px;
  object-fit: contain; /* menyesuaikan gambar agar proporsional */
  background-color: #f8f9fa; /* latar belakang abu-abu muda jika gambar kecil */
  padding: 10px;
  border-bottom: 1px solid #eee;
}
.card {
  transition: transform 0.2s ease;
}
.card:hover {
  transform: scale(1.03);
  border: 1px solid black;
}
.badge-circle {
  position: absolute;
  top: -10px;
  left: -10px;
  background-color: #d9534f;
  color: #fff;
  border-radius: 50%;
  font-size: 14px;
  padding: 5px 9px;
}

.btn-add, .btn-minus {
  border: none;
  background-color: #e9d5c5;
  padding: 4px 8px;
  border-radius: 5px;
  font-weight: bold;
}
.btn-add:hover, .btn-minus:hover {
  background-color: #cba88f;
}

.out-stock {
  background-color: #b8b8b8;
  color: #fff;
  text-align: center;
  padding: 3px 0;
  font-weight: 500;
}
#checkout a{
  text-decoration: none;
  color: white;
}
.checkout-btn {
  background-color: red;
  color: #fff;
  font-weight: 600;
}
.checkout-btn:hover {
  background-color: darkred;
}




/* ====== RESPONSIVE ====== */
@media (max-width: 991px) {
  .date {
    display: none;
  }

  .sidebar {
    left: -250px; /* ✅ sidebar disembunyikan dulu */
    width: 250px;
  }

  .sidebar.active {
    left: 0;
  }

  .main-content {
    margin-left: 0; /* ✅ biar isi geser pas sidebar sembunyi */
  }

  .toggle-sidebar {
    display: inline-block;
    cursor: pointer;
    font-size: 22px;
    margin-right: 15px;
  }

  .content-overlay {
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.5);
    display: none;
    z-index: 1049;
  }

  .content-overlay.active {
    display: block;
  }
}

/* --- Style Dropdown Akun --- */
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

@media (max-width: 576px) {
  .search {
    max-width: 130px;
  }

  .navbar .form-control {
    width: 100%;
    margin-bottom: 10px;
  }

  .navbar .d-flex {
    align-items: flex-start;
  }
}
@media(max-width: 472px){
  .search{
    display: none;
  }
}

  </style>
</head>
<body>

<div class="container-fluid">
  <div class="row">
    <!-- Sidebar -->
    <div class="col-md-2 sidebar" id="sidebar">
      <h5><a href="index.php"><i class=" bi bi-arrow-left mx-2"></i></a> <strong> Menu</strong></h5>
      <nav class="nav flex-column mt-3">
        <?php foreach ($categories as $cat): ?>
          <a class="nav-link <?= $cat == $activeCategory ? 'active' : '' ?>" href="?category=<?= $cat ?>">
            <?= htmlspecialchars($cat) ?>
          </a>
        <?php endforeach; ?>
      </nav>
    </div>

    <!-- Main Content -->
     <div class="content-overlay d-lg-none" id="overlay"></div>

    <div class="col-md-10 main-content">
      <!-- Top Navbar -->
      <nav class="navbar px-4 d-flex justify-content-between">
        <div class="d-flex align-items-center">
          <span class="toggle-sidebar d-lg-none" id="toggleSidebar">☰</span>
          <form action="" method="get">
            <input type="search" class="form-control me-3 search" id="search" name="key" placeholder="Search...">
          </form>
          <span class="date"><i class="bi bi-clock"></i> <?= date("D, d M y, h.i A") ?></span>
        </div>

        <div class="d-flex align-items-center" id="check">
          <button class="btn position-relative me-3">
            <a href="cart.php" style="text-decoration: none;">🛒</a>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
              <?= $totalItems ?>
            </span>
          </button>

          <a class="btn checkout-btn me-2" href="checkout.php">Checkout</a>
         

          <!-- Tombol akun dan dropdown -->
          <div class="account-dropdown position-relative">
            <button class="btn account-btn"><i class="bi bi-person-circle"></i></button>
            <div class="dropdown-content">
              <?php if (!isset($_SESSION['name'])): ?>
                <p><a class="login-logout" href="login.php">Login</a></p>
              <?php else: ?>
                <p><strong><?= $_SESSION['name']; ?></strong></p>
                <p><?= $_SESSION['email']; ?></p>
                <p><?= $_SESSION['nomor']; ?></p>
                <p><a class="login-logout" href="logout.php">Logout</a></p>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </nav>

      <!-- Menu Grid -->
      <div class="p-4" style="overflow: auto;" id="daftarmenu">
        <h4 class="mb-4 fw-3"><?= htmlspecialchars($activeCategory) ?>(<?= $jumlah; ?>)</h4>
        <div class="row g-3">
          <?php while ($menu = $menus->fetch_assoc()): ?>
           <div class="col-md-4 col-sm-6 mb-4">
              <div class="card shadow-sm border-0" style="background-color: #d9b89b;">

                  <?php if (isset($_SESSION['cart'][$menu['menu_id']])): ?>
                      <span class="badge-circle"><?= $_SESSION['cart'][$menu['menu_id']] ?></span>
                  <?php endif; ?>

                  <?php if ($menu['image']): ?>
                    <div class="rounded overflow-hidden text-center p-2">
                      <img src="img/<?= htmlspecialchars($menu['image']); ?>" 
                          alt="<?= htmlspecialchars($menu['name']); ?>"
                          style="height: 300px; object-fit: cover; width:100%; border-radius: 20px;">
                    </div>
                  <?php endif; ?>

                  <div class="card-body">
                      <h5 class="card-title"><?= htmlspecialchars($menu['name']) ?></h5>

                      <p class="card-text text-muted mb-1">
                          <?= htmlspecialchars($menu['description']) ?>
                      </p>

                      <p class="card-text fw-bold">
                          Rp <?= number_format($menu['price'], 0, ',', '.') ?>
                      </p>

                      <div class="d-flex justify-content-end">
                          <a href="?action=minus&id=<?= $menu['menu_id'] ?>&category=<?= urlencode($activeCategory) ?>"
                            class="btn-minus me-3 fs-5 text-center" style="width:37px; " >−</a>

                          <a href="?action=add&id=<?= $menu['menu_id'] ?>&category=<?= urlencode($activeCategory) ?>"
                            class="btn-add fs-5 text-center" style="width:37px; ">+</a>
                      </div>
                  </div>
              </div>
          </div>
          <?php endwhile; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.getElementById('toggleSidebar').addEventListener('click', function() {
  document.getElementById('sidebar').classList.toggle('active');
  document.getElementById('overlay').classList.toggle('active');
});

document.getElementById('overlay').addEventListener('click', function() {
  document.getElementById('sidebar').classList.remove('active');
  this.classList.remove('active');
});

let cari = document.getElementById('search');
let container = document.getElementById('daftarmenu');

cari.addEventListener('keyup', function() {

    let xhr = new XMLHttpRequest();

    xhr.onreadystatechange = function(){
        if ( xhr.readyState == 4 && xhr.status == 200){
            container.innerHTML = xhr.responseText;
            }
    }
    xhr.open('GET', 'daftar_menu.php?key='+ cari.value, true);
    xhr.send();

});


    // Simpan posisi scroll sebelum halaman direload
    window.addEventListener("beforeunload", function () {
        localStorage.setItem("scrollPos", window.scrollY);
    });

    window.addEventListener("load", function () {
        let scrollPos = localStorage.getItem("scrollPos");
        if (scrollPos !== null) {
            window.scrollTo(0, parseInt(scrollPos));
        }
    });


</script>

</body>
</html>
