<?php 
    require '../db_connect.php';
    require 'fungsi.php';
    session_start();

    if (!isset($_SESSION["login"])){
        header("Location:../login.php");
        exit;
    }

    $id = $_GET['id'];
    
    if ( hapusUser($id) >0 ){
        echo "<script>
            alert('data berhasil dihapus');
            window.location.href = 'users.php';
        </script>";
    } else {
        echo "<script>
            alert('data gagal dhapus: " . mysqli_error($conn) . "');
        </script>";
    }
?>