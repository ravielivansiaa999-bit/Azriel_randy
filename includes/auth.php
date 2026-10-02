<?php
if (session_status() === PHP_SESSION_NONE) session_start();
define('BASE', '/perpus_db');
function wajibLogin() {
if (!isset($_SESSION['user'])) {
header("Location: " . BASE . "/auth/login.php");
exit;
}
}
function wajibRole(array $roleBoleh) {
wajibLogin();
if (!in_array($_SESSION['user']['role'], $roleBoleh, true)) {
http_response_code(403);
die("Akses ditolak (403). Role Anda tidak diizinkan membuka halaman ini.");
}
}
// Escape output supaya aman dari XSS
function e($teks) {
return htmlspecialchars((string)$teks, ENT_QUOTES, 'UTF-8');
}