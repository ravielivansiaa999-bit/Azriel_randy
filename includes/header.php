<?php $role = $_SESSION['user']['role']; ?>
<!DOCTYPE html>
<html lang="id"><head><meta charset="UTF-8"><title>Perpustakaan</title>
<style>
body{font-family:sans-serif;margin:0}
nav{background:#1e3a5f;padding:12px 20px}
nav a{color:#fff;margin-right:16px;text-decoration:none}
main{padding:20px}
table{border-collapse:collapse;width:100%}
th,td{border:1px solid #ccc;padding:8px;text-align:left}
th{background:#eee}
.ok{background:#d4edda;padding:10px} .err{background:#f8d7da;padding:10px}
.telat{background:#f8d7da}
</style></head><body>
<nav>
<a href="<?= BASE ?>/dashboard.php">Dashboard</a>
<a href="<?= BASE ?>/buku/index.php">Katalog Buku</a>
<?php if (in_array($role, ['admin','petugas'])): ?>
<a href="<?= BASE ?>/peminjaman/index.php">Peminjaman</a>
<?php endif; ?>
<?php if ($role === 'siswa'): ?>
<a href="<?= BASE ?>/peminjaman/riwayat.php">Riwayat Saya</a>
<?php endif; ?>
<?php if ($role === 'admin'): ?>
<a href="<?= BASE ?>/users/index.php">Kelola User</a>
<?php endif; ?>
<a href="<?= BASE ?>/auth/logout.php" style="float:right">
Logout (<?= e($_SESSION['user']['username']) ?> - <?= e($role) ?>)</a>
</nav>
<main>
<?php
foreach (['pesan' => 'ok', 'error' => 'err'] as $kunci => $kelas) {
if (!empty($_SESSION[$kunci])) {
echo "<p class='$kelas'>" . e($_SESSION[$kunci]) . "</p>";
unset($_SESSION[$kunci]);
}
}