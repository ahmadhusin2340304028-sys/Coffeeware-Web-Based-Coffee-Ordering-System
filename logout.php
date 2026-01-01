<?php
session_start(); // Mulai session agar bisa dihapus

// ---- Hapus semua data session ----
$_SESSION = array();

// ---- Jika session menggunakan cookie, hapus juga cookienya ----
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000, 
        $params["path"], 
        $params["domain"], 
        $params["secure"], 
        $params["httponly"]
    );
}

// ---- Hapus cookies lain yang mungkin digunakan ----
foreach ($_COOKIE as $key => $value) {
    setcookie($key, '', time() - 3600, '/');
}

setcookie("id", "", time()-3600);
setcookie("key", "", time()-3600);

// ---- Akhiri session ----
session_destroy();

// ---- Redirect ke login.php ----
header("Location: login.php");
exit;
?>
