<?php
// auth.php
// Konfigurasi Keamanan Session Zero-Trust

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    // Atur expiration ke 1 tahun (tidak ada auto-logout spesifik)
    ini_set('session.gc_maxlifetime', 31536000); 
    session_set_cookie_params(31536000);
    session_start();
}

$current_script = basename($_SERVER['SCRIPT_NAME']);

// CSRF Token protection (Aturan .antigravityrules #0)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($current_script !== 'login.php' && $current_script !== 'logout.php') {
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        $is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
        
        if ($is_ajax) {
            header('Content-Type: application/json');
            header('HTTP/1.1 401 Unauthorized');
            echo json_encode(['error' => 'Akses ditolak. Silakan login.']);
            exit;
        } else {
            header("Location: login.php");
            exit;
        }
    }
}
?>
