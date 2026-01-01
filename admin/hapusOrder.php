<?php 
    require '../db_connect.php';
    require 'fungsi.php';
    session_start();

    if (!isset($_SESSION["login"])){
        header("Location:../login.php");
        exit;
    }

    $id = $_GET['id'];
    
    if ( hapusOrder($id) >0 ){
        echo "<script>
            alert('data berhasil dihapus');
            window.location.href = 'orders.php';
        </script>";
    } else {
        echo "<script>
            alert('data gagal dhapus: " . mysqli_error($conn) . "');
        </script>";
    }
?>