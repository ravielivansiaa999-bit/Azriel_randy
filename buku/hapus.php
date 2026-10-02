<?php
require '../config/database.php';
require '../includes/auth.php';
wajibRole(['admin']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
$id = (int)($_POST['id'] ?? 0);
// Tolak jika masih ada yang meminjam/mengajukan buku ini
$stmt = $conn->prepare("SELECT COUNT(*) FROM peminjaman
WHERE buku_id = ? AND status IN ('diajukan','dipinjam')");
$stmt->bind_param("i", $id);
$stmt->execute();
if ($stmt->get_result()->fetch_row()[0] > 0) {
$_SESSION['error'] = "Buku tidak bisa dihapus karena masih ada transaksi aktif.";
} else {
$stmt = $conn->prepare("UPDATE buku SET deleted_at = NOW() WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$_SESSION['pesan'] = "Buku berhasil dihapus.";
}
}
header("Location: index.php");