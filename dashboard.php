
<?php
require 'config/database.php';
require 'includes/auth.php';

wajibLogin();

function hitung($conn, $sql, $types = '', ...$params) {
    $stmt = $conn->prepare($sql);

    if ($types) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    return $stmt->get_result()->fetch_row()[0];
}

require 'includes/header.php';

$role = $_SESSION['user']['role'];
$username = e($_SESSION['user']['username']);

// Data dashboard
if ($role === 'siswa') {
    $aktif = hitung(
        $conn,
        "SELECT COUNT(*) FROM peminjaman
         WHERE anggota_id = ?
         AND status IN ('diajukan','dipinjam')",
        "i",
        $_SESSION['user']['anggota_id']
    );
} else {
    $buku = hitung(
        $conn,
        "SELECT COUNT(*) FROM buku WHERE deleted_at IS NULL"
    );

    $agt = hitung($conn, "SELECT COUNT(*) FROM anggota");

    $tunggu = hitung(
        $conn,
        "SELECT COUNT(*) FROM peminjaman WHERE status = 'diajukan'"
    );

    $pinjam = hitung(
        $conn,
        "SELECT COUNT(*) FROM peminjaman WHERE status = 'dipinjam'"
    );
}
?>

<style>
/* DASHBOARD */
.dashboard {
    font-family: 'Segoe UI', Arial, sans-serif;
    color: #1e293b;
    padding: 10px 0 30px;
}

.dashboard * {
    box-sizing: border-box;
}

.welcome-card {
    background: linear-gradient(120deg, #4338ca, #6366f1, #818cf8);
    color: white;
    padding: 32px;
    border-radius: 20px;
    margin-bottom: 28px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(79, 70, 229, .18);
}

.welcome-card::after {
    content: "📚";
    position: absolute;
    right: 35px;
    top: 15px;
    font-size: 105px;
    opacity: .15;
    transform: rotate(-15deg);
}

.welcome-card h2 {
    margin: 0 0 12px;
    font-size: 28px;
    font-weight: 700;
    color: white;
}

.welcome-card p {
    margin: 0;
    color: #e0e7ff;
    font-size: 15px;
}

.welcome-label {
    display: inline-block;
    background: rgba(255,255,255,.18);
    border: 1px solid rgba(255,255,255,.25);
    padding: 7px 13px;
    border-radius: 30px;
    font-size: 12px;
    margin-bottom: 17px;
}

/* STATISTIK */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 18px;
    margin-bottom: 28px;
}

.stat-card {
    background: white;
    padding: 23px;
    border-radius: 17px;
    border: 1px solid #edf0f7;
    box-shadow: 0 5px 20px rgba(15, 23, 42, .045);
    transition: transform .2s, box-shadow .2s;
    position: relative;
    overflow: hidden;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 25px rgba(15, 23, 42, .10);
}

.stat-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
}

.stat-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 14px;
    font-size: 23px;
}

.stat-card h3 {
    margin: 0 0 8px;
    color: #64748b;
    font-size: 13px;
    font-weight: 600;
}

.stat-number {
    font-size: 32px;
    line-height: 1.2;
    font-weight: 750;
    color: #172554;
    margin-bottom: 8px;
}

.stat-description {
    font-size: 12px;
    color: #94a3b8;
}

/* WARNA KARTU */
.blue .stat-icon {
    background: #e8efff;
    color: #3b82f6;
}

.green .stat-icon {
    background: #dcfce7;
    color: #16a34a;
}

.orange .stat-icon {
    background: #ffedd5;
    color: #ea580c;
}

.purple .stat-icon {
    background: #f3e8ff;
    color: #9333ea;
}

.blue { border-bottom: 4px solid #3b82f6; }
.green { border-bottom: 4px solid #22c55e; }
.orange { border-bottom: 4px solid #f97316; }
.purple { border-bottom: 4px solid #a855f7; }

/* INFORMASI */
.info-card {
    background: white;
    padding: 25px;
    border-radius: 17px;
    border: 1px solid #edf0f7;
    box-shadow: 0 5px 20px rgba(15, 23, 42, .045);
}

.info-card h3 {
    margin: 0 0 10px;
    font-size: 18px;
    color: #1e293b;
}

.info-card p {
    color: #64748b;
    line-height: 1.7;
    margin: 0;
}

.info-icon {
    display: inline-flex;
    width: 42px;
    height: 42px;
    background: #eef2ff;
    color: #4f46e5;
    border-radius: 12px;
    align-items: center;
    justify-content: center;
    font-size: 21px;
    margin-bottom: 15px;
}

/* SISWA */
.student-card {
    background: linear-gradient(135deg, #0f766e, #14b8a6);
    color: white;
    padding: 30px;
    border-radius: 20px;
    box-shadow: 0 10px 25px rgba(20,184,166,.18);
    margin-bottom: 25px;
}

.student-card h3 {
    color: white;
    margin: 0 0 12px;
    font-size: 18px;
}

.student-number {
    font-size: 45px;
    font-weight: 750;
    margin: 10px 0;
}

.student-card p {
    color: #ccfbf1;
    margin: 0;
}

.student-progress {
    height: 9px;
    background: rgba(255,255,255,.25);
    border-radius: 20px;
    overflow: hidden;
    margin-top: 20px;
}

.student-progress span {
    display: block;
    height: 100%;
    background: white;
    border-radius: 20px;
}

/* RESPONSIVE */
@media (max-width: 1000px) {
    .stats-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 600px) {
    .dashboard {
        padding-top: 5px;
    }

    .welcome-card {
        padding: 24px;
        border-radius: 16px;
    }

    .welcome-card h2 {
        font-size: 22px;
    }

    .welcome-card::after {
        font-size: 65px;
        right: 15px;
        top: 20px;
    }

    .stats-grid {
        grid-template-columns: 1fr;
        gap: 13px;
    }

    .stat-card {
        padding: 20px;
    }

    .stat-number {
        font-size: 28px;
    }
}
</style>

<div class="dashboard">

    <!-- HEADER SAMBUTAN -->
    <div class="welcome-card">
        <div class="welcome-label">
            ✨ SISTEM INFORMASI PERPUSTAKAAN
        </div>

        <h2>Selamat Datang, <?= $username ?>! 👋</h2>

        <p>
            Selamat beraktivitas dan mengelola perpustakaan hari ini.
        </p>
    </div>

    <?php if ($role === 'siswa'): ?>

        <!-- DASHBOARD SISWA -->
        <div class="student-card">
            <h3>📖 Aktivitas Peminjaman Saya</h3>

            <div class="student-number">
                <?= $aktif ?> / 3
            </div>

            <p>
                Buku dalam pengajuan atau sedang dipinjam
            </p>

            <div class="student-progress">
                <span style="width: <?= min(100, ($aktif / 3) * 100) ?>%"></span>
            </div>
        </div>

        <div class="info-card">
            <div class="info-icon">💡</div>
            <h3>Informasi Peminjaman</h3>
            <p>
                Kamu dapat memiliki maksimal 3 buku dalam status
                pengajuan atau sedang dipinjam. Jangan lupa
                mengembalikan buku tepat waktu!
            </p>
        </div>

    <?php else: ?>

        <!-- DASHBOARD ADMIN / PETUGAS -->
        <div class="stats-grid">

            <div class="stat-card blue">
                <div class="stat-top">
                    <div class="stat-icon">📚</div>
                    <span>📈</span>
                </div>

                <h3>Total Buku</h3>
                <div class="stat-number"><?= $buku ?></div>
                <div class="stat-description">
                    Koleksi buku tersedia dalam sistem
                </div>
            </div>

            <div class="stat-card green">
                <div class="stat-top">
                    <div class="stat-icon">👥</div>
                    <span>👤</span>
                </div>

                <h3>Total Anggota</h3>
                <div class="stat-number"><?= $agt ?></div>
                <div class="stat-description">
                    Anggota perpustakaan terdaftar
                </div>
            </div>

            <div class="stat-card orange">
                <div class="stat-top">
                    <div class="stat-icon">⏳</div>
                    <span>🔔</span>
                </div>

                <h3>Menunggu Persetujuan</h3>
                <div class="stat-number"><?= $tunggu ?></div>
                <div class="stat-description">
                    Pengajuan peminjaman baru
                </div>
            </div>

            <div class="stat-card purple">
                <div class="stat-top">
                    <div class="stat-icon">📖</div>
                    <span>🔄</span>
                </div>

                <h3>Sedang Dipinjam</h3>
                <div class="stat-number"><?= $pinjam ?></div>
                <div class="stat-description">
                    Transaksi peminjaman aktif
                </div>
            </div>

        </div>

        <!-- INFORMASI -->
        <div class="info-card">
            <div class="info-icon">✨</div>

            <h3>Ringkasan Perpustakaan</h3>

            <p>
                Selamat datang di dashboard pengelolaan perpustakaan.
                Pantau koleksi buku, data anggota, pengajuan
                peminjaman, dan transaksi yang sedang berlangsung
                melalui halaman ini.
            </p>
        </div>

    <?php endif; ?>

</div>

<?php
require 'includes/footer.php';
?>