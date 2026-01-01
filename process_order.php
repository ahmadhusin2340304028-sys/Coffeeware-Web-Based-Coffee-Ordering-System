<?php
session_start();
require 'db_connect.php';

if (isset($_POST['checkout'])) {
    var_dump($_SESSION);
    $user_id = $_SESSION['user_id'];        // ID pengguna yang sedang login
    $payment_method = $_POST['payment'];    // Misal: 'cash'
    $total_price = $_POST['total_price'];   // Total dari cart
    $status = 'pending';
    $order_source = 'online';

    // 1️⃣ Insert ke tabel orders
    $sql = "INSERT INTO orders (order_id, user_id, total_price, payment_method, payment_status, order_source, status)
            VALUES ('', '$user_id', '$total_price', '$payment_method', 'pending', '$order_source', '$status')";
    mysqli_query($conn, $sql);

    // Ambil ID order yang baru dimasukkan
    $order_id = mysqli_insert_id($conn);

    // 2️⃣ Insert ke tabel order_items untuk tiap menu di cart
    foreach ($_SESSION['cart'] as $menu_id => $qty) {
        $sql_item = "INSERT INTO order_items (order_item_id, order_id, menu_id, quantity)
                     VALUES ('', '$order_id', '$menu_id', '$qty')";
        mysqli_query($conn, $sql_item);
    }

    // Hapus cart setelah checkout
    unset($_SESSION['cart']);

    echo "<script>alert('Order berhasil dibuat!'); window.location='orders.php';</script>";
}
