<?php
require '../config/database.php';
require '../includes/auth.php';
wajibRole(['siswa']);
$stmt = $conn->prepare(
"SELECT p.*, b.judul FROM peminjaman p
JOIN buku b ON b.id = p.buku_id
WHERE p.anggota_id = ? ORDER BY p.id DESC");
$stmt->bind_param("i", $_SESSION['user']['anggota_id']);
$stmt->execute();
$data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
require '../includes/header.php';
?>
<h2>Riwayat Peminjaman Saya</h2>
<table>
<tr><th>Buku</th><th>Status</th><th>Tgl Pinjam</th><th>Jatuh Tempo</th><th>Kembali</th><th>Denda</th></tr>
<?php foreach ($data as $d): ?>
<tr>
<td><?= e($d['judul']) ?></td>
<td><?= e(ucfirst($d['status'])) ?></td>
<td><?= e($d['tgl_pinjam'] ?? '-') ?></td>
<td><?= e($d['tgl_jatuh_tempo'] ?? '-') ?></td>
<td><?= e($d['tgl_kembali'] ?? '-') ?></td>
<td>Rp<?= number_format($d['denda'], 0, ',', '.') ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php require '../includes/footer.php';