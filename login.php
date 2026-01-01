<?php

require 'db_connect.php';
session_start();

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



if (isset($_SESSION["login"])){
    header("Location:index.php");
        exit;
}

if (isset($_POST['login'])){  
    $nama = $_POST['username'];
    $pass = $_POST['password'];

    $hasil = mysqli_query($conn, "SELECT * FROM users where name = '$nama'");

    if (mysqli_num_rows($hasil) === 1){
        $row = mysqli_fetch_assoc($hasil);

        if (md5($pass) === $row['password']){  
            
            if (isset($_POST["remember"])){     
                setcookie('id', $row['user_id'], time()+604800);
                setcookie("key", hash("sha256", $row["password"]), time()+604800);
            }

            $_SESSION["login"] = true;
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['name'] = $row['name'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['nomor'] = $row['nomor'];
            $_SESSION['role']  = $row['role'];
              

            if ($row['role'] == 'admin') {
                echo "<script>
                    alert('Login berhasil sebagai admin.');
                    window.location.href = 'admin/dashboard_admin.php';
                </script>";
                exit;
            } else {
                echo "<script>
                    alert('Login berhasil.');
                    window.location.href = 'index.php';
                </script>";
                exit;
            }
        }
    $error = true;    
        
    }
    
}    
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CoffeeWare Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    /* ======= GLOBAL ======= */
    body {
      background: url('img/coffee-back.jpg') no-repeat center center/cover;
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      font-family: 'Poppins', sans-serif;
      color: #fff;
    }

    /* ======= CARD CONTAINER ======= */
    .login-container {
      display: flex;
      width: 90%;
      height: 85%;
      background-color: rgba(25, 20, 18, 0.95);
      border-radius: 25px;
      overflow: hidden;
      box-shadow: 0 0 40px rgba(0, 0, 0, 0.4);
    }

    /* ======= LEFT IMAGE ======= */
    .login-left {
      flex: 0.9;
      background: url('img/side-img.jpg') no-repeat center center/cover;
    }

    /* ======= RIGHT FORM ======= */
    .login-right {
      flex: 1.1;
      padding: 50px;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .login-right h2 {
      font-weight: 600;
      color: #ffffff;
      margin-bottom: 5px;
    }

    .login-right h2 span {
      color: #c9a196;
    }

    .login-right p {
      color: #c4b6ae;
      margin-bottom: 30px;
    }

    /* ======= INPUT FORM ======= */
    .form-control {
      background-color: #3a2e29;
      border: none;
      color: #fff;
      padding: 12px 15px;
      border-radius: 10px;
    }

    .form-control:focus {
      box-shadow: none;
      border: 1px solid #c9a196;
    }

    .form-control::placeholder {
      color: #b8a89e;
    }

    /* ======= BUTTON ======= */
    .btn-login {
      background-color: #c9a196;
      border: none;
      width: 100%;
      max-width: 1000px;
      padding: 10px;
      color: #fff;
      font-weight: 500;
      border-radius: 10px;
      transition: 0.3s;
    }

    .btn-login:hover {
      background-color: #b89084;
    }

    /* ======= TEXT LINKS ======= */
    .options {
      font-size: 14px;
      margin-bottom: 25px;
    }

    .options a {
      color: #c9a196;
      text-decoration: none;
    }

    .options a:hover {
      text-decoration: underline;
    }

    .footer-text {
      text-align: center;
      margin-top: 15px;
      font-size: 14px;
    }

    .footer-text a {
      color: #c9a196;
      text-decoration: none;
      font-weight: 500;
    }

    .footer-text a:hover {
      text-decoration: underline;
    }

    /* ======= RESPONSIVE ======= */
    @media (max-width: 1050px) and (max-height: 1400px) {
      body {
        height: 100vh;
        padding: 40px 0;
      }

      .login-container {
        flex-direction: column;
        width: 95%;
        height: auto;
        background-color: transparent; /* Hapus background gelap */
        box-shadow: none;
      }

      .login-left {
        order: 2;
        height: 300px;
        border-radius: 15px;
      }

      .login-right {
        order: 1;
        background-color: rgba(0, 0, 0, 0.5);
        border-radius: 15px;
        margin-bottom: 20px;
        padding: 40px 20px;
      }

      .form-control,
      .btn-login,
      .options {
        width: 70%;
      }
    }
  </style>
 
</head>
<body>

  <div class="login-container">
    <!-- Left Image -->
    <div class="login-left"></div>

    <!-- Right Form -->
    <div class="login-right">
      <div class="text-center mb-4">
        <!-- Coffee Cup Icon (SVG) -->
        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="#fff" viewBox="0 0 16 16">
          <path d="M6 1a1 1 0 0 1 1 1v1h6.5a1.5 1.5 0 0 1 0 3H13v1a5 5 0 0 1-10 0V3a2 2 0 0 1 2-2h1Zm7 3H7v2h6a.5.5 0 0 0 0-1H13a.5.5 0 0 0 0 1V4Z"/>
        </svg>
        <h2 class="mt-3">Coffee<span>Ware</span></h2>
        <p>Welcome, Please login to your account</p>
      </div>

      <form method="post" action="">
        <div class="mb-3">
          <input type="text" class="form-control w-75 mx-auto" name="username" placeholder="Username" required>
        </div>
        <div class="mb-3">
          <input type="password" class="form-control w-75 mx-auto" name="password" placeholder="Password" required>
        </div>

        <div class="d-flex justify-content-between align-items-center w-75 mx-auto options">
          <div>
            <input type="checkbox" name="remember" id="remember">
            <label for="remember"> Remember Me</label>
          </div>
          
        </div>

        <button class="btn-login w-75 d-flex mx-auto justify-content-center" name="login">Sign In</button>
      </form>

      <div class="footer-text">
        Don't have an account? <a href="register.php">Sign Up</a>
      </div>
    </div>
  </div>

</body>
</html>
