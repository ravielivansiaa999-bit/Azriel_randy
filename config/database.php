<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
$conn = new mysqli("localhost", "root", "", "perpus_db");
$conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
die("Koneksi database gagal.");
}