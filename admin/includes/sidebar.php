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
  .submenu a {
    padding-left: 40px;
    font-size: 14px;
  }
  .submenu a:hover {
    background-color: #3a3f44;
  }
</style>

<div class="sidebar">
  <h4 class="text-center mb-4">☕ CoffeeWare</h4>

  <a href="dashboard_admin.php" class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard_admin.php' ? 'active fw-bold text-primary' : '' ?>">
    <i class="bi bi-speedometer2"></i> Dashboard
  </a>

  <a href="users.php" class="<?= basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active fw-bold text-primary' : '' ?>">
    <i class="bi bi-people"></i> Users
  </a>

  <!-- Dropdown Menu -->
  <a class="d-flex justify-content-between align-items-center" 
     data-bs-toggle="collapse" 
     href="#menuDropdown" 
     role="button" 
     aria-expanded="false" 
     aria-controls="menuDropdown">
    <span><i class="bi bi-cup-hot"></i> Menu</span>
    <i class="bi bi-caret-down-fill small"></i>
  </a>

  <div class="collapse submenu" id="menuDropdown">
    <a href="menus.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'menus.php' && ($_GET['category'] ?? '') == '') ? 'active fw-bold text-primary' : '' ?>">
      All Menu
    </a>
    <a href="menus.php?category=coffee" class="<?= (basename($_SERVER['PHP_SELF']) == 'menus.php' && ($_GET['category'] ?? '') == 'coffee') ? 'active fw-bold text-primary' : '' ?>">
      Coffee
    </a>
    <a href="menus.php?category=non-coffee" class="<?= (basename($_SERVER['PHP_SELF']) == 'menus.php' && ($_GET['category'] ?? '') == 'non-coffee') ? 'active fw-bold text-primary' : '' ?>">
      Non-Coffee
    </a>
    <a href="menus.php?category=main course" class="<?= (basename($_SERVER['PHP_SELF']) == 'menus.php' && ($_GET['category'] ?? '') == 'main course') ? 'active fw-bold text-primary' : '' ?>">
      Main Course
    </a>
    <a href="menus.php?category=snack" class="<?= (basename($_SERVER['PHP_SELF']) == 'menus.php' && ($_GET['category'] ?? '') == 'snack') ? 'active fw-bold text-primary' : '' ?>">
      Snacks
    </a>
  </div>

  <a href="orders.php" class="<?= basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active fw-bold text-primary' : '' ?>">
    <i class="bi bi-cart4"></i> Orders
  </a>

  <a href="reports.php" class="<?= basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active fw-bold text-primary' : '' ?>">
    <i class="bi-file-earmark-text"></i> Reports
  </a>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
