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


       

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CoffeeWare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* --- Style Dropdown Akun --- */
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

.account-dropdown .dropdown-content a:active {
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

.account-btn:active {
  cursor: pointer;
}
body{
    font-family: sans-serif;
}

/* navbar */

.logo{
    font-weight: bold; 
    font-style: italic; 
    font-family: sans-serif;
}   

.navbar{
    border-bottom: 2px solid rgb(255, 255, 255);
}

/* header */

.hero {
    min-height: 100vh;
    position: relative;
    overflow: hidden; /* penting agar video tidak keluar area */
}

/* VIDEO SEBAGAI BACKGROUND */
.hero-video {
    position: absolute;
    top: 20px;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover; /* sama seperti background-size: cover */
    object-position: center; /* sama seperti background-position: center */
    z-index: 0; /* berada di belakang konten */
    filter: brightness(70%); /* opsional: biar teks lebih jelas */
}

/* Konten hero berada di atas video */
.hero .container {
    position: relative;
    z-index: 2;
}


.hero .container h1{
    color: aliceblue;
    font-size: 4em;
    text-shadow: 2px 2px 5px rgba(1,1,3,0.5) ;
    line-height: 4rem;
}

.hero .container{
    padding: 1.4rem 7%;
    max-width: 68rem
    ;
}

.hero .container p{
    color: rgba(255, 255, 255, 0.865);
    font-size: 1.6rem;
    text-shadow: 2px 2px 5px rgba(1,1,3,0.5) ;
    margin-top: 5px;
    line-height: 1.6rem;
}

body{
    height: 2000px;
}

.hero::after{
    content: "";
    display: block;
    position: absolute;
    width: 100%;
    height: 20%;
    bottom: 0;
    background: linear-gradient(0deg, rgba(255,255,255,1) 15%, rgba(1,1,3,0) 50%);
}

.button-hero{
    box-shadow: 3px 3px 6px rgba(1,1,3,0.7);
    margin-top: 1px;
    z-index: 3;
}

.button-hero {
  background-color: #8e612e;
  color: #fff;
  border: none;
  transition: 0.3s;
}

.button-hero:hover {
  background-color: #da9c56ff;
}

.btn{
  background-color: #6f4e37;
  border: #6f4e37;
}

.btn:hover {
  background-color: #da9c56ff;
}


/* about */

#about{
    padding: 8rem 7%;
    align-items: center;
    justify-content: center;    
    
}

.gambar{
    align-items: center;
    justify-content: center;
}

#menu{
    padding: 8rem 7% 1.4rem;
    background-color: #eed4b8;;
}

.luar {
    color: white;
    text-shadow: 2px 2px 5px rgba(1,1,3,0.5) ;
    margin-top: 5px;
    line-height: 1.6rem;
}

.menu {
  background-color: #eed4b8;
  padding: 100px 0;
}

.menu .card {
  border: none;
  background-color: #fff;
  color: #4a3b2d;
  transition: 0.3s;
}

.menu .card:hover {
  transform: translateY(-5px);
  box-shadow: 0 6px 15px rgba(142, 97, 46, 0.3);
}

.menu h2,
.menu h6 {
  color: #fff;
}

/* --- Style Dropdown Akun --- */
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

/* --- FOOTER BASE STYLE --- */
.logo-footer {
    color:#8e612e; /* contoh warna kuning */
    font-size: 30px;
}


.site-footer {
    background: #ffffffff;
    color:#8e612e;
    padding: 60px 40px 40px;
    font-family: Arial, sans-serif;
}

.site-footer ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.site-footer li {
    margin-bottom: 8px;
    font-size: 14px;
    letter-spacing: 1px;
}

/* --- TOP SECTION (LOGO + MENU) --- */
.footer-top {
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 40px;
}

.footer-brand h2 {
    font-size: 28px;
    letter-spacing: 2px;
    margin: 0 0 5px;
}

.footer-brand p {
    margin: 0;
    font-size: 12px;
    letter-spacing: 1.5px;
}

.footer-menu {
    display: flex;
    gap: 50px;
    flex-wrap: wrap;
}

.footer-menu ul li {
    cursor: pointer;
}

.footer-menu ul li:hover {
    color:#8e612e;
}

/* --- LINE --- */
.footer-line {
    border: 0;
    border-top: 1px solid #333;
    margin: 40px 0;
}

/* --- BOTTOM SECTION (ICONS + COPYRIGHT) --- */
.footer-bottom {
    text-align: center;
}

.social-icons a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 42px;
    height: 42px;
    border: 1px solid #fff;
    border-radius: 50%;
    margin: 0 6px;
    font-size: 18px;
    transition: 0.3s;
}

.social-icons a:hover {
    background: #fff;
    color: #111;
}

/* --- COPYRIGHT --- */
.footer-bottom p {
    margin-top: 25px;
    font-size: 13px;
    letter-spacing: 1px;
}

/* --- RESPONSIVE --- */
@media (max-width: 768px) {
    .footer-top {
        flex-direction: column;
        align-items: flex-start;
    }

    .footer-menu {
        flex-direction: column;
        gap: 20px;
    }

    .footer-menu ul li {
        font-size: 15px;
    }
}

@media (max-width: 480px) {
    .social-icons a {
        width: 36px;
        height: 36px;
        font-size: 16px;
    }

    .footer-brand h2 {
        font-size: 24px;
    }
}

    </style>
    
   
</head>
<body>
    <!-- // navbar -->
    <nav class="main-nav navbar bg-light navbar-expand-sm p-1 fixed-top d-flex border-bottom border-2 border-warning" data-bs-theme="light" id="navbar">
            <div class="container">                
                <a  href="#" class="logo fs-3 navbar-brand">
                    Coffee<span style="color: #8e612e;">ware</span>.
                </a>
                <button
                    type="button"
                    class="navbar-toggler"
                    data-bs-toggle="collapse"
                    data-bs-target="#navmenu"                
                    >
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navmenu">
                    <ul class="navbar-nav ms-auto d-flex align-items-center" >
                        <li class="nav-item"><a href="#" class="nav-link">Home</a></li>
                        <li class="nav-item"><a href="#about" class="nav-link">About</a></li>
                        <li class="nav-item"><a href="#contact" class="nav-link">contact</a></li>
                        <div class="account-dropdown position-relative ms-auto">
                            <button class="btn account-btn text-decoration-none text-reset"><i class="bi bi-person-circle"></i></button>
                            <div class="dropdown-content">
                                <?php if (!isset($_SESSION['name'])): ?>
                                    <!-- Tampilkan hanya tombol Login -->
                                    <p><a class="login-logout" href="login.php">Login</a></p>

                                <?php else: ?>
                                    <!-- Tampilkan profil user biasa -->
                                    <p><strong><?= htmlspecialchars($_SESSION['name']); ?></strong></p>
                                    <p><?= htmlspecialchars($_SESSION['email']); ?></p>
                                    <p><?= htmlspecialchars($_SESSION['nomor']); ?></p>
                                    <p><a class="login-logout" href="logout.php">Logout</a></p>
                                <?php endif; ?>
                            </div>
                        </div>

                    </ul>
                </div>
                
            </div>
            

    </nav>  

    <!-- // header section -->
        <section class="hero d-flex align-items-center mt-4">
            <video class="hero-video" autoplay muted loop>
                <source src="img/back-video.mp4" type="video/mp4">
            </video>
            <div class="container">
                <h1 class="fw-bolder ">Brewed with <span style="color: #8e612e;">Passion</span> <br>Served with Love.</h1>
                <p class="fw-light">
Discover the perfect cup that warms your soul</p>
                <a href="menu.php?category=Coffee" class="button-hero btn btn-lg mt-3">Check it Now!</a>
            </div>
        </section>

    <!-- // about section -->
     <section id="about">
        <div class="container">
            <div class="row align-items-center">
            
                <!-- Kolom Gambar -->
                <div class="col-md-6 d-flex     justify-content-center mb-4 mb-md-0">
                    
                    <img src="img/aboutus.jpg" class="img-fluid rounded shadow" alt="About Juice" style="max-height: 350px;">
                </div>

                <!-- Kolom Teks -->
                <div class="col-md-6">    
                    <h2 class="fw-bold mb-3 text-align: justify;">About Us</h2>                
                    <p class="fs-5 text-muted" style="text-align: justify;">
                    Di CoffeeWare, kami percaya setiap cangkir kopi memiliki cerita.
Dari aroma pertama hingga tegukan terakhir, kami menuangkan cinta dan dedikasi dalam setiap racikan.
Biji kopi pilihan kami disangrai dengan hati-hati untuk menghadirkan rasa hangat, nyaman, dan penuh makna di setiap sajian.
                    </p>
                    <a href="menu.php" class="btn btn-success mt-3">Explore Menu</a>
                </div>
            
            </div>
        </div>
    </section>


    <!-- contact section -->
    <section id="contact" class="py-5 mt-5" style="background-color: #eed4b8;">
        <div class="container">
            <div class="row g-5">            
            <!-- Lokasi -->
            <div class="col-md-6">
                <h2 class="fw-bold mb-3">Lokasi Kami</h2>
                <p class="mb-4">
                Kamu bisa menemukan kami di Prikanan, Daerah tepi laut. Nikmati secangkir kopi ditemani semilir angin laut dan pemandangan ombak yang menenangkan.
                </p>
                <!-- Embed Google Maps -->
                <div class="ratio ratio-16x9">
                <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3983.177447490545!2d117.56933957465613!3d3.306236452167589!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x32138abde68f8e17%3A0x1c468d87d0d4a879!2sTarakan%2C%20Karang%20Anyar%20Pantai%2C%20Kec.%20Tarakan%20Bar.%2C%20Kota%20Tarakan%2C%20Kalimantan%20Utara!5e0!3m2!1sid!2sid!4v1761190992719!5m2!1sid!2sid" 
                    width="100%" 
                    height="250" 
                    style="border:0;" 
                    allowfullscreen="" 
                    loading="lazy"></iframe>
                </div>
            </div>

            <!-- Form -->
            <div class="col-md-6">
                <h2 class="fw-bold mb-3">Hubungi Kami</h2>
                <form onsubmit="sendToWhatsApp(event)">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" placeholder="Masukkan username" value="<?= $_SESSION['name'] ?? "Masukkan username"?>">
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" placeholder="Masukkan email" value="<?= $_SESSION['email'] ?? "Masukkan email"?>">
                </div>
                <div class="mb-3">
                    <label for="phone" class="form-label">No HP</label>
                    <input type="tel" class="form-control" id="phone" placeholder="Masukkan nomor HP" value="<?= $_SESSION['nomor'] ?? "Masukkan nomor HP"?>">
                </div>
                <button type="submit" class="btn btn-success w-100">Kirim</button>
                </form>
            </div>

            </div>
        </div>
    </section>
    
    <!-- footer -->

    <footer class="site-footer">
    <div class="footer-top">
        <div class="footer-brand">
            <h2>COFFEEWARE</h2>
            <p>Discover the perfect cup that warms your soul</p>
        </div>

        <div class="footer-menu">
            <ul>
                <li style="border-bottom: 1px solid #8e612e;">TOP MENU</li>
                <li>ESPRESSO</li>
                <li>AMERICANO</li>
                <li>FRENCH FRIES</li>
                <li>CHICKEN RICE BOWL</li>
            </ul>
            <ul>
                <li style="border-bottom: 1px solid #8e612e;">CATEGORY</li>
                <li><a class="text-decoration-none text-reset" href="menu.php">COFFEE</a></li>
                <li><a class="text-decoration-none text-reset" href="menu.php?category=Non-Coffee">NON-COFFEE</a></li>
                <li><a class="text-decoration-none text-reset" href="menu.php?category=Main%20Course">MAIN COURSE</a></li>
                <li><a class="text-decoration-none text-reset" href="menu.php?category=Snack">SNACK</a></li>
            </ul>
            <ul>
                <li style="border-bottom: 1px solid #8e612e;">QUICK LINKS</li>
                <li><a class="text-decoration-none text-reset" href="#">HOME</a></li>
                <li><a class="text-decoration-none text-reset" href="#about">ABOUT US</a></li>
                <li><a class="text-decoration-none text-reset" href="#contact">CONTACT US</a></li>
                <li><a class="text-decoration-none text-reset" href="menu.php">MENU</a></li>
            </ul>
        </div>
    </div>

    <hr class="footer-line">

    <div class="footer-bottom">
        <div class="social-icons">
            <a href="#"><i class="logo-footer bi bi-facebook"></i></a>
            <a href="#"><i class="logo-footer bi bi-twitter"></i></a>
            <a href="#"><i class="logo-footer bi bi-instagram"></i></a>
            <a href="#"><i class="logo-footer bi bi-whatsapp"></i></a>
            <a href="#"><i class="logo-footer bi bi-envelope"></i></a>
        </div>

        <p class="copyright">©2025 coffeeware. All rights reserved.</p>
    </div>
</footer>





    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js" integrity="sha384-7qAoOXltbVP82dhxHAUje59V5r2YsVfBafyUDxEdApLPmcdhBPg1DKg1ERo0BZlK" crossorigin="anonymous"></script>
    <script>
        function sendToWhatsApp(event) {
            event.preventDefault();
            let username = document.getElementById("username").value;
            let email = document.getElementById("email").value;
            let phone = document.getElementById("phone").value;

            if (username === "" || email === "" || phone === "") {
                alert("Harap isi semua field sebelum mengirim!");
                return; // hentikan proses
            }

            let message = `Halo, saya mau daftar:\n\nUsername: ${username}\nEmail: ${email}\nNo HP: ${phone}`;
            let waURL = "https://wa.me/6285256875779?text=" + encodeURIComponent(message);

            window.open(waURL, "_blank");
        }
    </script>

<script src="https://unpkg.com/feather-icons"></script>
<script>
  feather.replace();
</script>


</body>
</html>