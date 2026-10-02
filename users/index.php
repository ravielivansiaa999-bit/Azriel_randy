
<?php
require '../config/database.php';
require '../includes/auth.php';

wajibRole(['admin']);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $roleBaru = $_POST['role'] ?? '';

    $anggota = ($_POST['anggota_id'] ?? '') !== ''
        ? (int)$_POST['anggota_id']
        : null;

    if ($username === '' || strlen($password) < 6) {
        $error = "Username wajib diisi dan password minimal 6 karakter.";

    } elseif (!in_array($roleBaru, ['admin', 'petugas', 'siswa'], true)) {
        $error = "Role tidak valid.";

    } elseif ($roleBaru === 'siswa' && $anggota === null) {
        $error = "User siswa harus dihubungkan ke data anggota.";

    } else {

        if ($roleBaru !== 'siswa') {
            $anggota = null;
        }

        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("
                INSERT INTO users (username, password, role, anggota_id)
                VALUES (?, ?, ?, ?)
            ");

            $stmt->bind_param("sssi", $username, $hash, $roleBaru, $anggota);
            $stmt->execute();

            $_SESSION['pesan'] = "User berhasil ditambahkan.";

            header("Location: index.php");
            exit;

        } catch (mysqli_sql_exception $ex) {

            $error = ($ex->getCode() == 1062)
                ? "Username atau anggota tersebut sudah punya akun."
                : "Gagal menyimpan user.";
        }
    }
}

// Ambil daftar user
$users = $conn->query("
    SELECT u.id, u.username, u.role, a.nama
    FROM users u
    LEFT JOIN anggota a ON a.id = u.anggota_id
    ORDER BY u.role, u.username
")->fetch_all(MYSQLI_ASSOC);

// Ambil daftar anggota
$anggotaList = $conn->query("
    SELECT id, nis, nama
    FROM anggota
    ORDER BY nama
")->fetch_all(MYSQLI_ASSOC);

require '../includes/header.php';
?>

<style>
/* HALAMAN KELOLA USER */
.kelola-user {
    font-family: 'Segoe UI', Arial, sans-serif;
    color: #1e293b;
    padding: 10px 0 30px;
}

.kelola-user * {
    box-sizing: border-box;
}

/* HEADER */
.user-header {
    background: linear-gradient(120deg, #4338ca, #6366f1, #818cf8);
    color: white;
    padding: 30px;
    border-radius: 20px;
    margin-bottom: 25px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 10px 25px rgba(79, 70, 229, .15);
}

.user-header::after {
    content: "👥";
    position: absolute;
    right: 35px;
    top: 10px;
    font-size: 95px;
    opacity: .15;
    transform: rotate(-12deg);
}

.user-header h2 {
    color: white;
    font-size: 27px;
    margin: 0 0 10px;
}

.user-header p {
    color: #e0e7ff;
    margin: 0;
    font-size: 14px;
}

/* LAYOUT */
.user-layout {
    display: grid;
    grid-template-columns: minmax(280px, 350px) minmax(0, 1fr);
    gap: 20px;
    align-items: start;
}

/* CARD */
.user-card {
    background: white;
    border: 1px solid #edf0f7;
    border-radius: 16px;
    box-shadow: 0 5px 20px rgba(15, 23, 42, .045);
    overflow: hidden;
}

.user-card-header {
    padding: 20px;
    border-bottom: 1px solid #edf0f7;
}

.user-card-header h3 {
    margin: 0 0 6px;
    color: #1e293b;
    font-size: 17px;
}

.user-card-header p {
    margin: 0;
    color: #94a3b8;
    font-size: 12px;
}

.user-card-body {
    padding: 22px;
}

/* FORM */
.form-group {
    margin-bottom: 18px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #334155;
    font-size: 13px;
    font-weight: 650;
}

.form-control {
    width: 100%;
    padding: 12px 14px;
    border: 1px solid #dbe2ef;
    border-radius: 10px;
    background: #f8fafc;
    color: #1e293b;
    font-family: inherit;
    font-size: 13px;
    outline: none;
    transition: .2s;
}

.form-control:focus {
    border-color: #6366f1;
    background: white;
    box-shadow: 0 0 0 3px rgba(99,102,241,.12);
}

.form-control::placeholder {
    color: #94a3b8;
}

.form-hint {
    color: #94a3b8;
    font-size: 11px;
    margin-top: 7px;
    line-height: 1.5;
}

/* ROLE SELECT */
.role-select {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 7px;
}

.role-option {
    position: relative;
}

.role-option input {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}

.role-option label {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 7px;
    padding: 12px 5px;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    background: #f8fafc;
    color: #64748b;
    font-size: 12px;
    cursor: pointer;
    transition: .2s;
}

.role-option label span {
    font-size: 20px;
}

.role-option input:checked + label {
    background: #eef2ff;
    color: #4f46e5;
    border-color: #6366f1;
    box-shadow: 0 0 0 2px rgba(99,102,241,.08);
}

/* BUTTON */
.btn-user {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 13px 16px;
    border: none;
    border-radius: 10px;
    background: #4f46e5;
    color: white;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    transition: .2s;
}

.btn-user:hover {
    background: #4338ca;
    transform: translateY(-2px);
    box-shadow: 0 5px 12px rgba(79,70,229,.2);
}

/* ALERT */
.alert-user {
    padding: 13px 15px;
    border-radius: 10px;
    font-size: 13px;
    margin-bottom: 18px;
    line-height: 1.5;
}

.alert-error {
    background: #fef2f2;
    color: #b91c1c;
    border: 1px solid #fecaca;
}

.alert-success {
    background: #f0fdf4;
    color: #15803d;
    border: 1px solid #bbf7d0;
}

/* USER LIST */
.user-list-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
}

.user-count {
    background: #eef2ff;
    color: #4f46e5;
    padding: 7px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
}

/* TABLE */
.user-table-wrap {
    width: 100%;
    overflow-x: auto;
}

.kelola-user table {
    width: 100%;
    min-width: 520px;
    border-collapse: collapse;
}

.kelola-user thead {
    background: #f8fafc;
}

.kelola-user th {
    padding: 14px 16px;
    text-align: left;
    color: #64748b;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: .4px;
    border-bottom: 1px solid #e2e8f0;
}

.kelola-user td {
    padding: 15px 16px;
    font-size: 13px;
    color: #475569;
    border-bottom: 1px solid #f1f5f9;
}

.kelola-user tbody tr:nth-child(even) {
    background: #fafbff;
}

.kelola-user tbody tr:hover {
    background: #f1f5ff;
}

.kelola-user tbody tr:last-child td {
    border-bottom: none;
}

/* USER IDENTITY */
.user-identity {
    display: flex;
    align-items: center;
    gap: 11px;
}

.user-avatar {
    width: 38px;
    height: 38px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #e0e7ff;
    color: #4f46e5;
    border-radius: 11px;
    font-weight: 750;
    font-size: 15px;
}

.user-name {
    color: #1e293b;
    font-weight: 650;
}

/* ROLE BADGES */
.role-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
}

.role-admin {
    background: #f3e8ff;
    color: #7e22ce;
}

.role-petugas {
    background: #dbeafe;
    color: #1d4ed8;
}

.role-siswa {
    background: #dcfce7;
    color: #15803d;
}

.anggota-name {
    color: #475569;
}

.no-anggota {
    color: #94a3b8;
    font-style: italic;
}

/* EMPTY */
.empty-user {
    padding: 45px 20px;
    text-align: center;
    color: #94a3b8;
}

.empty-user div {
    font-size: 40px;
    margin-bottom: 10px;
}

.empty-user h3 {
    color: #334155;
    font-size: 16px;
    margin: 0 0 7px;
}

/* RESPONSIVE */
@media (max-width: 950px) {
    .user-layout {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 600px) {
    .user-header {
        padding: 23px;
        border-radius: 15px;
    }

    .user-header h2 {
        font-size: 22px;
    }

    .user-header::after {
        font-size: 60px;
        right: 15px;
        top: 20px;
    }

    .user-card-body {
        padding: 17px;
    }

    .user-card-header {
        padding: 17px;
    }
}
</style>

<div class="kelola-user">

    <!-- HEADER -->
    <div class="user-header">
        <h2>👥 Kelola User</h2>
        <p>
            Tambahkan akun admin, petugas, dan siswa
            untuk mengakses sistem perpustakaan.
        </p>
    </div>

    <div class="user-layout">

        <!-- FORM TAMBAH USER -->
        <div class="user-card">

            <div class="user-card-header">
                <h3>＋ Tambah Akun Baru</h3>
                <p>Lengkapi informasi akun pengguna.</p>
            </div>

            <div class="user-card-body">

                <?php if ($error): ?>
                    <div class="alert-user alert-error">
                        ⚠ <?= e($error) ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($_SESSION['pesan'])): ?>
                    <div class="alert-user alert-success">
                        ✓ <?= e($_SESSION['pesan']) ?>
                    </div>
                    <?php unset($_SESSION['pesan']); ?>
                <?php endif; ?>

                <form method="post">

                    <div class="form-group">
                        <label for="username">Username</label>

                        <input
                            class="form-control"
                            id="username"
                            name="username"
                            placeholder="Masukkan username"
                            value="<?= e($_POST['username'] ?? '') ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>

                        <input
                            class="form-control"
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Minimal 6 karakter"
                            minlength="6"
                            required
                        >

                        <div class="form-hint">
                            Password akan disimpan menggunakan password_hash().
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Pilih Role Pengguna</label>

                        <div class="role-select">

                            <div class="role-option">
                                <input
                                    type="radio"
                                    id="role-admin"
                                    name="role"
                                    value="admin"
                                    <?= ($_POST['role'] ?? 'admin') === 'admin' ? 'checked' : '' ?>
                                >
                                <label for="role-admin">
                                    <span>👑</span>
                                    Admin
                                </label>
                            </div>

                            <div class="role-option">
                                <input
                                    type="radio"
                                    id="role-petugas"
                                    name="role"
                                    value="petugas"
                                    <?= ($_POST['role'] ?? '') === 'petugas' ? 'checked' : '' ?>
                                >
                                <label for="role-petugas">
                                    <span>🧑‍💼</span>
                                    Petugas
                                </label>
                            </div>

                            <div class="role-option">
                                <input
                                    type="radio"
                                    id="role-siswa"
                                    name="role"
                                    value="siswa"
                                    <?= ($_POST['role'] ?? '') === 'siswa' ? 'checked' : '' ?>
                                >
                                <label for="role-siswa">
                                    <span>🎓</span>
                                    Siswa
                                </label>
                            </div>

                        </div>
                    </div>

                    <div class="form-group">
                        <label for="anggota_id">
                            Hubungkan Anggota
                        </label>

                        <select
                            class="form-control"
                            id="anggota_id"
                            name="anggota_id"
                        >
                            <option value="">
                                -- Pilih anggota khusus siswa --
                            </option>

                            <?php foreach ($anggotaList as $a): ?>
                                <option
                                    value="<?= (int)$a['id'] ?>"
                                    <?= (($_POST['anggota_id'] ?? '') == $a['id'] && ($_POST['anggota_id'] ?? '') !== '') ? 'selected' : '' ?>
                                >
                                    <?= e($a['nis']) ?> - <?= e($a['nama']) ?>
                                </option>
                            <?php endforeach; ?>

                        </select>

                        <div class="form-hint">
                            Wajib dipilih jika role adalah siswa.
                        </div>
                    </div>

                    <button type="submit" class="btn-user">
                        ＋ Tambahkan User
                    </button>

                </form>

            </div>
        </div>

        <!-- DAFTAR USER -->
        <div class="user-card">

            <div class="user-card-header user-list-header">
                <div>
                    <h3>Daftar Pengguna</h3>
                    <p>Seluruh akun yang terdaftar dalam sistem.</p>
                </div>

                <span class="user-count">
                    <?= count($users) ?> Akun
                </span>
            </div>

            <?php if (count($users) > 0): ?>

                <div class="user-table-wrap">

                    <table>

                        <thead>
                            <tr>
                                <th>Pengguna</th>
                                <th>Role</th>
                                <th>Anggota</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php foreach ($users as $u): ?>

                                <?php
                                $roleClass = [
                                    'admin' => 'role-admin',
                                    'petugas' => 'role-petugas',
                                    'siswa' => 'role-siswa'
                                ][$u['role']] ?? '';
                                ?>

                                <tr>

                                    <td>
                                        <div class="user-identity">

                                            <div class="user-avatar">
                                                <?= e(strtoupper(substr($u['username'], 0, 1))) ?>
                                            </div>

                                            <div class="user-name">
                                                <?= e($u['username']) ?>
                                            </div>

                                        </div>
                                    </td>

                                    <td>
                                        <span class="role-badge <?= $roleClass ?>">
                                            <?php
                                            echo match ($u['role']) {
                                                'admin' => '👑 Admin',
                                                'petugas' => '🧑‍💼 Petugas',
                                                'siswa' => '🎓 Siswa',
                                                default => e($u['role'])
                                            };
                                            ?>
                                        </span>
                                    </td>

                                    <td>
                                        <?php if (!empty($u['nama'])): ?>
                                            <span class="anggota-name">
                                                <?= e($u['nama']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="no-anggota">
                                                Tidak terhubung
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php else: ?>

                <div class="empty-user">
                    <div>👥</div>
                    <h3>Belum Ada Pengguna</h3>
                    <p>Tambahkan akun pertama melalui formulir.</p>
                </div>

            <?php endif; ?>

        </div>

    </div>

</div>

<?php require '../includes/footer.php'; ?>