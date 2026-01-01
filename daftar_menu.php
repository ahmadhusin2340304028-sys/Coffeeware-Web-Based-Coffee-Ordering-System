<?php 
require 'db_connect.php';
session_start();

error_reporting(0);
ini_set('display_errors', 0);

$key = $_GET['key'] ?? '';

// Query pencarian
if ($key) {
  $stmt = $conn->prepare("SELECT * FROM menu WHERE name LIKE ?");
  $search = "%$key%";
  $stmt->bind_param("s", $search);
  $stmt->execute();
  $menus = $stmt->get_result();
} 
?>

<div class="row g-3">
  <?php if ($menus->num_rows > 0): ?>
    <?php while ($menu = $menus->fetch_assoc()): ?>
      <div class="col-md-4 col-sm-6">
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
                          <a href="?action=minus&id=<?= $menu['menu_id'] ?>&category=<?= urlencode($menu['category']) ?>" class="btn-minus me-3 fs-5 text-center" style="width:37px; ">−</a>
                          <a href="?action=add&id=<?= $menu['menu_id'] ?>&category=<?= urlencode($menu['category']) ?>" class="btn-minus me-3 fs-5 text-center" style="width:37px; ">+</a>
          
                      </div>
                  </div>
              </div>


      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <div class="col-12 text-center">
      <div class="alert alert-warning">Menu tidak ditemukan.</div>
    </div>
  <?php endif; ?>
</div>
