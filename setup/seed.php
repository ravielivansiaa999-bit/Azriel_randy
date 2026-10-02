<?php
require '../config/database.php';
$akun = [
['admin', 'admin123', 'admin', null],
['petugas', 'petugas123', 'petugas', null],
['ani', 'siswa123', 'siswa', 1], // terhubung ke anggota id 1
];
$stmt = $conn->prepare("INSERT INTO users (username, password, role, anggota_id) VALUES (?, ?, ?, ?)");
foreach ($akun as [$u, $p, $r, $a]) {
$hash = password_hash($p, PASSWORD_DEFAULT);
$stmt->bind_param("sssi", $u, $hash, $r, $a);
$stmt->execute();
}
echo "Akun berhasil dibuat. HAPUS file ini sekarang!";