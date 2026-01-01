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
$stmt = $conn->prepare("SELECT * FROM menu");
$stmt->execute();
$menus = $stmt->get_result();
$jumlahA = $conn->query("SELECT COUNT(*) FROM menu")->fetch_row()[0];

if (isset($_GET['category'])){
  $cat = $_GET['category'];
$categories = [];
$result = $conn->query("SELECT DISTINCT category FROM menu ORDER BY category");
$jumlah = $conn->query("SELECT COUNT(*) FROM menu WHERE category='$cat'")->fetch_row()[0];

while ($row = $result->fetch_assoc()) {
  $categories[] = $row['category'];
}


// Ambil kategori aktif (default: Coffee)
$activeCategory = $_GET['category'] ?? 'Coffee';

// Ambil menu berdasarkan kategori
$stmt = $conn->prepare("SELECT * FROM menu WHERE category = ?");
$stmt->bind_param("s", $activeCategory);
$stmt->execute();
$menus = $stmt->get_result();

}

if ( isset($_POST['tambah_menu']) ){
        if ( tambahMenu($_POST) > 0 )  {
          
    echo "<script>
            alert('data berhasil ditambahkan');
            window.location.href = 'menus.php';
        </script>";
    } else {
        echo "<script>
            alert('data gagal ditambahkan: " . mysqli_error($conn) . "');
        </script>";
    }
    }

  if ( isset($_POST['edit_menu']) ){
        if ( editMenu($_POST) > 0 )  {
    echo "<script>
            alert('data berhasil didiubah');
            window.location.href = 'menus.php';
        </script>";
    } else {
        echo "<script>
            alert('data gagal diubah: " . mysqli_error($conn) . "');
        </script>";
    }
    }



// // edit user
// if ( isset($_POST['submit']) ){
//         if ( editUser($_POST) > 0 )  {
//     echo "<script>
//             alert('data berhasil didiubah');
//             window.location.href = 'users.php';
//         </script>";
//     } else {
//         echo "<script>
//             alert('data gagal diubah: " . mysqli_error($conn) . "');
//         </script>";
//     }
//     }

// //cari user
//  if (isset($_POST['cari'])){
//         $usersData = cariUser($_POST['key']);
//     }



?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Admin - Users | KopiWare</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
</style>
<body>
 
  <!-- Sidebar -->
  <?php include "includes/sidebar.php"; ?>

  <!-- Main Content -->
  <div class="main-content">
    <!-- Navbar fixed -->
    <nav class="navbar navbar-expand-lg navbar-light px-4 d-flex align-items-center justify-content-between">
      <h5 class="mb-0">Menu</h5>
      <span class="date d-flex justify-content-center"><i class="bi bi-clock"></i> <?= date("D, d M y, h.i A") ?></span>
      <div class="d-flex align-items-center">
        
        <i class="bi bi-bell me-3 fs-5"></i>
        <div class="account-dropdown position-relative">
          <button class="btn account-btn d-flex align-items-center">
            <i class="bi bi-person-circle fs-4 me-2"></i>
            <h6 class="mb-0">
              <?= isset($_SESSION['name']) ? "Hello, " . htmlspecialchars($_SESSION['name']) : ""; ?>
            </h6>
          </button>
          <div class="dropdown-content">
            <?php if (!isset($_SESSION['name'])): ?>
              <p><a href="login.php">Login</a></p>
            <?php else: ?>
              <p><strong><?= htmlspecialchars($_SESSION['name']); ?></strong></p>
              <p><?= htmlspecialchars($_SESSION['email']); ?></p>
              <p><?= htmlspecialchars($_SESSION['nomor']); ?></p>
              <p><a href="../logout.php">Logout</a></p>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </nav>






    <!-- Table Preview -->
    <div class="container mt-2">        
        <div class="mb-1 shadow-sm bg-white d-flex justify-content-between align-items-center rounded p-2" >
            <div class="d-flex">
                <form action="" method="post" class="d-flex" role="search">
                    <input name="key" class="form-control me-2 w-100" type="search" placeholder="Search" aria-label="Search"/>
                    <button class="btn btn-outline-primary" name="cari" type="submit">Search</button>
                </form>                
                <?php if (isset($_GET['category'])): ?>
                  <a href="menus.php?category=<?= $_GET['category'] ?>" class="btn btn-primary ms-2"><i class="bi bi-arrow-clockwise "></i></a>
                <?php else: ?>
                  <a href="menus.php" class="btn btn-primary ms-2"><i class="bi bi-arrow-clockwise "></i></a>
                <?php endif;  ?>
                
            </div>
            <!-- Tombol Tambah Menu -->
            <div class="d-flex justify-content-end ms-auto">
              <a 
              class="btn btn-primary btn-sm rounded-5"
              data-bs-toggle="modal"
              data-bs-target="#addMenuModal">
              <i class="bi bi-plus-circle"></i>  tambah menu
              </a>             
            </div>         
          
            
        </div>
      <div class="container mt-2">
        <?php if (isset($_GET['category'])): ?>
          <h4 class="mb-2">Daftar <?= $_GET['category'] ?> (<?= $jumlah ?>)</h4>
        <?php else: ?>
          <h4 class="mb-2">Daftar Menu (<?= $jumlahA ?>) </h4>
        <?php endif;  ?>

        <div class="row">
          <?php while ($menu = $menus->fetch_assoc()) : ?>
            <div class="col-md-4 mb-4">
              <!-- card  -->
              <div class="card shadow-sm border-0">
                <img src="../img/<?= htmlspecialchars($menu['image']); ?>" class="card-img-top" alt="<?= htmlspecialchars($menu['name']); ?>" style="height: 300px; object-fit: cover;">
                <div class="card-body">
                  <h5 class="card-title"><?= htmlspecialchars($menu['name']); ?></h5>
                  <p class="card-text text-muted mb-1"><?= htmlspecialchars($menu['description']); ?></p>
                  <p class="card-text fw-bold text-success">Rp<?= $menu['price']; ?></p>
                  <div class="d-flex justify-content-end">
                    <?php if (isset($_GET['category'])): ?>
                      <a href="menus.php?menu_id=<?= $menu['menu_id'] ?>&category=<?= $_GET['category'] ?>" class="btn btn-warning btn-sm rounded-5"><i class="bi bi-pencil"></i></a>
                    <?php else: ?>
                      <a href="menus.php?menu_id=<?= $menu['menu_id'] ?>" class="btn btn-warning btn-sm rounded-5"><i class="bi bi-pencil"></i></a>
                    <?php endif;  ?>                                                                                
                    <a href="hapusMenu.php?menu_id=<?= $menu['menu_id']; ?>" class="btn btn-danger btn-sm ms-2 rounded-5" onclick="return confirm('Yakin ingin menghapus menu ini?')"><i class="bi bi-trash"></i></a>
                  </div>                             


                </div>
              </div>
              <!-- cardend -->
            </div>
          <?php endwhile; ?>
        </div>
        
      </div>
    </div>

    <!-- Modal Tambah Menu -->
    <div class="modal fade" id="addMenuModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5>Tambah Menu</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <form action="menus.php" method="post" enctype="multipart/form-data">
              <input class="form-control mb-2" type="text" name="name" placeholder="Nama Menu" required>
              <textarea class="form-control mb-2" name="description" placeholder="Deskripsi Menu" rows="3" required></textarea>
              <input class="form-control mb-2" type="number" name="price" placeholder="Harga" required>
              <select class="form-control mb-2" name="category" required>
                <option value="">Pilih Kategori</option>
                <option value="coffee">coffee</option>
                <option value="non-coffee">non-coffee</option>
                <option value="main-course">main course</option>
                <option value="snack">snack</option>
              </select>
              <input class="form-control mb-2" type="file" name="gambar">

              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary" name="tambah_menu">Simpan</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    
    <div class="modal  <?= isset($_GET['menu_id']) ? 'show d-block fade' : '' ?>" id="editMenuModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5>Edit Menu</h5>
            <?php if (isset($_GET['category'])): ?>
                  <a type="button" class="btn-close" href="menus.php?category=<?= $_GET['category'] ?>"></a>
                <?php else: ?>
                  <a type="button" class="btn-close" href="menus.php"></a>
                <?php endif;  ?>            
          </div>
          <div class="modal-body">  
            <?php  
            if (isset($_GET['menu_id'])){
              $id = $_GET['menu_id'];
              $result = mysqli_query($conn, "SELECT * FROM menu WHERE menu_id = $id");
              $menuEdit = mysqli_fetch_assoc($result);
            }

            ?>
            <form action="menus.php" method="post" enctype="multipart/form-data">
              <input type="hidden" name="menu_id" id="edit-menu-id" value="<?= $menuEdit['menu_id']; ?>">
              <input type="hidden" name="gambarLama" value="<?= $menuEdit['image']; ?>">
              <input class="form-control mb-2" type="text" name="name" id="edit-menu-name" value="<?= $menuEdit['name']; ?>" required>
              <textarea class="form-control mb-2" name="description" id="edit-menu-description" rows="3" required><?= $menuEdit['description']; ?></textarea>
              <input class="form-control mb-2" type="number" name="price" id="edit-menu-price" value="<?= $menuEdit['price']; ?>" required>
              <select class="form-control mb-2" name="category" id="edit-menu-category">
                <option value="<?= $menuEdit['category']; ?>"><?= $menuEdit['category']; ?></option>
                <option value="coffee">Coffee</option>
                <option value="non-coffee">Non Coffee</option>
                <option value="main-course">Main Course</option>
                <option value="snack">Snack</option>
              </select>
              <img class="d-flex justify-content-start rounded m-3" src="../img/<?= $menuEdit['image']; ?>" width="50px">
              <input class="form-control mb-2" type="file" name="gambar">
              <div class="modal-footer">
                <?php if (isset($_GET['category'])): ?>
                  <a type="button" class="btn btn-secondary" href="menus.php?category=<?= $_GET['category'] ?>">Batal</a>
                <?php else: ?>
                  <a type="button" class="btn btn-secondary" href="menus.php">Batal</a>
                <?php endif; ?> 
                <button type="submit" class="btn btn-primary" name="edit_menu">Simpan</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>



  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script></script>
  
  
</body>
</html>
