<?php 
require 'db_connect.php';
session_start();

if (isset($_POST["register"])) {
    $user = $_POST["username"];
    $email = $_POST["email"];
    $no = $_POST["nomor"];
    $pass = $_POST["password"];
    $pass2 = $_POST["password2"];
    $hashpass = md5($pass);

    $hasil = mysqli_query($conn, "SELECT name FROM users where name = '$user'");

    if ( mysqli_fetch_assoc($hasil)){
          echo "<script>
            alert('user sudah terdaftar');            
        </script>";
       
    }elseif ($pass !== $pass2){
          echo "<script>
            alert('konfirmasi pasword tidak sesuai');            
        </script>"; 
    }else{
         mysqli_query($conn, "INSERT INTO users VALUES ('', '$user', '$email', '$no', '$hashpass', 'customer');");
         if( mysqli_affected_rows($conn) > 0 ){
          echo "<script>
            alert('user telah terdaftar');            
        </script>"; 
        }else{
              echo "<script>
                alert('user gagal terdaftar');            
            </script>";
        }

    }
  }


?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CoffeeWare Register</title>
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
      flex-direction: row;
      width: 90%;
      height: 85%;
      background-color: rgba(25, 20, 18, 0.95);
      border-radius: 25px;
      overflow: hidden;
      box-shadow: 0 0 40px rgba(0, 0, 0, 0.4);
    }

    /* ======= LEFT FORM ======= */
    .login-left {
      flex: 1.1;
      padding: 75px;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .login-left h2 {
      font-weight: 600;
      color: #ffffff;
      margin-bottom: 5px;
    }

    .login-left h2 span {
      color: #c9a196;
    }

    .login-left p {
      color: #c4b6ae;
      margin-bottom: 30px;
    }

    /* ======= RIGHT IMAGE ======= */
    .login-right {
      flex: 0.9;
      background: url('img/side-img.jpg') no-repeat center center/cover;
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
      font-weight: 750;
      border-radius: 10px;
      transition: 0.3s;
    }

    .btn-login:hover {
      background-color: #b89084;
    }

    /* ======= TEXT LINKS ======= */
    .footer-text {
      text-align: center;
      margin-top: 15px;
      font-size: 14px;
    }

    .footer-text a {
      color: #c9a196;
      text-decoration: none;
      font-weight: 750;
    }

    .footer-text a:hover {
      text-decoration: underline;
    }

    /* ======= RESPONSIVE ======= */
    @media (max-width: 1075px) and (max-height: 1400px) {
      body {
        height: 100vh;
        padding: 40px 0;
      }

      .login-container {
        flex-direction: column;
        width: 95%;
        height: auto;
        background-color: transparent;
        box-shadow: none;
      }

      .login-left {
        order: 1;
        background-color: rgba(0, 0, 0, 0.5);
        border-radius: 15px;
        margin-bottom: 20px;
        padding: 40px 20px;
      }

      .login-right {
        order: 2;
        height: 300px;
        border-radius: 15px;
      }

      .form-control,
      .btn-login,
      .footer-text {
        width: 70%;
      }
    }
  </style>
</head>
<body>

  <div class="login-container">
    
     
    <div class="login-right"></div>
    <div class="login-left">
      <div class="text-center mb-2">
        <!-- Coffee Cup Icon (SVG) -->
        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="#fff" viewBox="0 0 16 16">
          <path d="M6 1a1 1 0 0 1 1 1v1h6.5a1.5 1.5 0 0 1 0 3H13v1a5 5 0 0 1-10 0V3a2 2 0 0 1 2-2h1Zm7 3H7v2h6a.5.5 0 0 0 0-1H13a.5.5 0 0 0 0 1V4Z"/>
        </svg>
        <h2 class="mt-3">Coffee<span>Ware</span></h2>
        <p>Welcome, Create Your Account</p>
      </div>

      <!-- Form Register -->
      <form method="post" action="">
        <div class="mb-3">
          <input type="text" class="form-control form-control-sm w-75 mx-auto" name="username" placeholder="Username" required>
        </div>
        <div class="mb-3">
          <input type="text" class="form-control form-control-sm w-75 mx-auto" name="email" placeholder="Email" required>
        </div>
        <div class="mb-3">
          <input type="text" class="form-control form-control-sm w-75 mx-auto" name="nomor" placeholder="No +62123456789"  required>
        </div>
        <div class="mb-3">
          <input type="password" class="form-control form-control-sm w-75 mx-auto" name="password" placeholder="Password" required>
        </div>
        <div class="mb-3">
          <input type="password" class="form-control form-control-sm w-75 mx-auto" name="password2" placeholder="Confirm Password" required>
        </div>

        <button class="btn-login form-control-sm w-75 d-flex mx-auto justify-content-center" name="register">Sign Up</button>
      </form>

      <div class="footer-text">
        Already have an account? <a href="login.php">Sign In</a>
      </div>
    </div>

    
    
  </div>

</body>
</html>
