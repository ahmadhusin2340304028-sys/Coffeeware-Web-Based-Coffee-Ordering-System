<?php
require '../db_connect.php'; // pastikan koneksi mysqli ke DB kopiware

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1️⃣ Ambil data dari form
    $user_id         = $_POST['user_id']; // default 1 = Offline Customer
    $payment_method  = $_POST['payment_method'];
    $payment_status  = $_POST['payment_status'];
    $order_source    = $_POST['order_source']; // offline
    $status          = 'pending';

    // Array menu & quantity
    $menu_ids = $_POST['menu_id'];
    $quantities = $_POST['quantity'];

    // 2️⃣ Hitung total harga (berdasarkan tabel menus)
    $total_price = 0;
    foreach ($menu_ids as $index => $menu_id) {
        $q = "SELECT price FROM menu WHERE menu_id = $menu_id";
        $res = mysqli_query($conn, $q);
        $menu = mysqli_fetch_assoc($res);
        $subtotal = $menu['price'] * $quantities[$index];
        $total_price += $subtotal;
    }

    // 3️⃣ Simpan ke tabel orders
    $queryOrder = "INSERT INTO orders (order_id,user_id, total_price, payment_method, payment_status, order_source, status)
                   VALUES ('', '$user_id', '$total_price', '$payment_method', '$payment_status', '$order_source', '$status')";

    if (mysqli_query($conn, $queryOrder)) {
        $order_id = mysqli_insert_id($conn); // Ambil ID order yang baru dibuat

        // 4️⃣ Simpan ke tabel order_items
        foreach ($menu_ids as $index => $menu_id) {
            $qty = $quantities[$index];
            $queryItem = "INSERT INTO order_items (order_item_id, order_id, menu_id, quantity)
                          VALUES ('', '$order_id', '$menu_id', '$qty')";
            mysqli_query($conn, $queryItem);
        }

        // 5️⃣ Redirect ke halaman orders.php dengan pesan sukses
        echo "<script>
                alert('Order offline berhasil disimpan!');
                window.location.href='orders.php';
              </script>";
    } else {
        echo "<script>
                alert('Gagal menyimpan order!');
                window.location.href='orders.php';
              </script>";
    }
}
?>
