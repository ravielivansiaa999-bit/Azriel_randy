<?php
require '../config/database.php';
require '../includes/auth.php';
wajibRole(['admin', 'petugas']);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
header("Location: index.php");
exit;
}
$id = (int)($_POST['id'] ?? 0);
$aksi = $_POST['aksi'] ?? '';
$uid = (int)$_SESSION['user']['id'];
try {
$conn->begin_transaction();
// Kunci baris peminjaman, sekaligus hitung hari terlambat
$stmt = $conn->prepare(
"SELECT buku_id, status,
GREATEST(COALESCE(DATEDIFF(CURDATE(), tgl_jatuh_tempo), 0), 0) AS telat
FROM peminjaman WHERE id = ? FOR UPDATE");
$stmt->bind_param("i", $id);
$stmt->execute();
$p = $stmt->get_result()->fetch_assoc();
if (!$p) throw new Exception("Data peminjaman tidak ditemukan.");
$buku = (int)$p['buku_id'];
if ($aksi === 'setujui') {
if ($p['status'] !== 'diajukan') throw new Exception("Pengajuan sudah diproses.");
$stmt = $conn->prepare("SELECT stok FROM buku WHERE id = ? FOR UPDATE");
$stmt->bind_param("i", $buku);
$stmt->execute();
if ($stmt->get_result()->fetch_row()[0] < 1) throw new Exception("Stok buku habis.");
$stmt = $conn->prepare(
"UPDATE peminjaman SET status = 'dipinjam', tgl_pinjam = CURDATE(),
tgl_jatuh_tempo = DATE_ADD(CURDATE(), INTERVAL 7 DAY), diproses_oleh = ?
WHERE id = ?");
$stmt->bind_param("ii", $uid, $id);
$stmt->execute();
$stmt = $conn->prepare("UPDATE buku SET stok = stok - 1 WHERE id = ?");
$stmt->bind_param("i", $buku);
$stmt->execute();
$pesan = "Pengajuan disetujui. Jatuh tempo 7 hari lagi.";
} elseif ($aksi === 'tolak') {
if ($p['status'] !== 'diajukan') throw new Exception("Pengajuan sudah diproses.");
$stmt = $conn->prepare("UPDATE peminjaman SET status = 'ditolak', diproses_oleh = ? WHERE id = ?");
$stmt->bind_param("ii", $uid, $id);
$stmt->execute();
$pesan = "Pengajuan ditolak.";
} elseif ($aksi === 'kembali') {
if ($p['status'] !== 'dipinjam') throw new Exception("Buku ini tidak sedang dipinjam.");
$denda = (int)$p['telat'] * 1000; // Rp1.000 per hari
$stmt = $conn->prepare(
"UPDATE peminjaman SET status = 'dikembalikan', tgl_kembali = CURDATE(), denda = ?
WHERE id = ?");
$stmt->bind_param("ii", $denda, $id);
$stmt->execute();
$stmt = $conn->prepare("UPDATE buku SET stok = stok + 1 WHERE id = ?");
$stmt->bind_param("i", $buku);
$stmt->execute();
$pesan = "Buku dikembalikan. Denda: Rp" . number_format($denda, 0, ',', '.');
} else {
throw new Exception("Aksi tidak dikenal.");
}
$conn->commit();
$_SESSION['pesan'] = $pesan;
} catch (Exception $ex) {
$conn->rollback();
$_SESSION['error'] = ($ex instanceof mysqli_sql_exception)
? "Terjadi kesalahan database."
: $ex->getMessage();
}
header("Location: index.php?status=" . ($aksi === 'kembali' ? 'dipinjam' : 'diajukan'));