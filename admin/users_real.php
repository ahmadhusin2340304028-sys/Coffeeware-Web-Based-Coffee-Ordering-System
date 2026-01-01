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
    u.user_id, 
    u.name, 
    u.email, 
    u.nomor, 
    COUNT(o.order_id) AS total_orders
FROM users u
LEFT JOIN orders o ON u.user_id = o.user_id
WHERE u.role = 'customer' OR u.role = ''
GROUP BY 
    u.user_id, 
    u.name, 
    u.email, 
    u.nomor
ORDER BY u.user_id DESC;";
$usersData = query($query);



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

//cari user
 if (isset($_POST['cari'])){
        $usersData = cariUser($_POST['key']);
    }



?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Admin - Users | KopiWare</title>
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
        <h5 class="mb-0">Users</h5>
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
     
        
          <table class="table table-hover small" data-toggle="table" data-search="true" data-pagination="true">
            <thead>
              <tr>
                <th>#</th>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>No</th>
                <th>Total Orders</th>
                <th>Action</th>
                
              </tr>
            </thead>
            <tbody>
              <?php $no = 1; foreach($usersData as $n) : ?>
              <tr>
                <td><?=  $no; ?></td>
                <td><?= $n['user_id']; ?></td>
                <td><?= $n['name'];  ?></td>
                <td><?= $n['email'];  ?></td>
                <td><?= $n['nomor'];  ?></td>
                <td><?= $n['total_orders'];  ?></td>
                
                <td>
                 <!-- Tombol Edit -->
                <a href="users.php?user_id=<?= $n['user_id'] ?>" 
                  class="btn btn-warning btn-sm rounded-5">
                  <i class="bi bi-pencil"></i>
                </a>

                <!-- Modal -->
                <div class="modal fade <?= isset($_GET['user_id']) ? 'show d-block' : '' ?>" id="editUserModal" tabindex="-1">
                  <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5>Edit User</h5>
                        <a type="button" class="btn-close" href="users.php"></a>
                      </div>
                      <div class="modal-body">
                        <?php
                        // Ambil data user kalau ada user_id di GET
                        if (isset($_GET['user_id'])) {
                            $id = $_GET['user_id'];
                            $result = mysqli_query($conn, "SELECT * FROM users WHERE user_id = $id");
                            $userEdit = mysqli_fetch_assoc($result);
                        }
                        ?>
                        <form action="users.php" method="post">
                          <input type="hidden" name="user_id" value="<?= $userEdit['user_id'] ?? '' ?>">
                          <input class="form-control mb-2" type="text" name="name" placeholder="Username" value="<?= $userEdit['name'] ?? '' ?>">
                          <input class="form-control mb-2" type="email" name="email" placeholder="Email" value="<?= $userEdit['email'] ?? '' ?>">
                          <input class="form-control mb-2" type="text" name="nomor" placeholder="+62***********" value="<?= $userEdit['nomor'] ?? '' ?>">
                          <div class="modal-footer">
                            <a type="button" class="btn btn-secondary" style="text-decoration: none;" href="users.php">batal</a>
                            <button type="submit" class="btn btn-primary" name="submit">Simpan</button>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>



  

                <a href="hapusUser.php?id=<?= $n['user_id']; ?>" class="btn btn-danger btn-sm rounded-5" onclick="return confirm('Yakin hapus user ini?')"><i class="bi bi-trash"></i></a>
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

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/jquery/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
    <script src="https://unpkg.com/bootstrap-table@1.21.0/dist/bootstrap-table.min.js"></script>
  
  
</body>
</html>
