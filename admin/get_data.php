<?php
require '../db_connect.php';

if (isset($_POST['user_id'])) {
    $id = $_POST['user_id'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $dataUser = $result->fetch_assoc();

    echo json_encode($dataUser);
}

if (isset($_POST['menu_id'])) {
    $id = $_POST['menu_id'];

    $stmt = $conn->prepare("SELECT * FROM menu WHERE menu_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $dataMenu = $result->fetch_assoc();

    echo json_encode($dataMenu);
}

if (isset($_POST['edit_id'])) {
    $id = $_POST['edit_id'];

    $stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $dataOrder = $result->fetch_assoc();

    echo json_encode($dataOrder);
}


if (isset($_POST['detail_id'])){
    $oID = $_POST['detail_id'];
    $stmt = $conn->prepare("SELECT 
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
      WHERE o.order_id = ?");
    $stmt->bind_param("i", $oID);
    $stmt->execute();
    
    $result = $stmt->get_result();
    
    $data = [];

    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    echo json_encode($data);
}

