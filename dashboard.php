
<?php
session_start();

// Proteksi dashboard
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Logout
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();

    header("Location: login.php");
    exit;
}

// Output API
if (isset($_GET['menu']) && $_GET['menu'] === 'api') {
    $outputApi = [
        "status" => "success",
        "message" => "API Inventaris aktif",
        "user" => [
            "username" => $_SESSION['username'],
            "role" => $_SESSION['role']
        ],
        "data" => [
            "barang" => $_SESSION['stok'] ?? [],
            "jumlah" => count($_SESSION['stok'] ?? [])
        ]
    ];

    // Tampilkan output dalam halaman agar tersedia tombol kembali.
    header('Content-Type: text/html; charset=utf-8');
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Output API - Inventaris</title>
        <style>
            * {
                box-sizing: border-box;
            }

            body {
                font-family: 'Segoe UI', Arial, sans-serif;
                background: #f3f6fb;
                margin: 0;
                padding: 30px;
                color: #1e293b;
            }

            .api-container {
                max-width: 1000px;
                margin: 0 auto;
            }

            .btn-kembali {
                display: inline-block;
                padding: 11px 18px;
                margin-bottom: 22px;
                background: #1e3a8a;
                color: white;
                text-decoration: none;
                border-radius: 8px;
                font-size: 14px;
                font-weight: 600;
                transition: .2s;
            }

            .btn-kembali:hover {
                background: #2563eb;
                transform: translateY(-1px);
            }

            .panel {
                background: white;
                border: 1px solid #e8edf5;
                border-radius: 14px;
                padding: 25px;
                box-shadow: 0 4px 15px #0f172a09;
            }

            h1 {
                margin: 0 0 8px;
                color: #172554;
                font-size: 26px;
            }

            .description {
                margin: 0 0 20px;
                color: #64748b;
                font-size: 14px;
            }

            pre {
                margin: 0;
                padding: 20px;
                background: #0f172a;
                color: #e2e8f0;
                border-radius: 10px;
                overflow-x: auto;
                font-size: 13px;
                line-height: 1.7;
                white-space: pre-wrap;
                overflow-wrap: anywhere;
            }

            @media (max-width: 600px) {
                body {
                    padding: 16px;
                }

                .panel {
                    padding: 18px;
                }
            }
        </style>
    </head>
    <body>
        <main class="api-container">
            <a href="dashboard.php" class="btn-kembali">← Kembali ke Dashboard</a>

            <section class="panel">
                <h1>Output API</h1>
                <p class="description">Data inventaris dalam format JSON.</p>
                <pre><?php
                    echo htmlspecialchars(
                        json_encode($outputApi, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                        ENT_QUOTES,
                        'UTF-8'
                    );
                ?></pre>
            </section>
        </main>
    </body>
    </html>
    <?php
    exit;
}

// Update stok
$pesan = "";
$jenisPesan = "success";

if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['update_stok'])) {

    $barang = trim($_POST['barang'] ?? "");
    $stok = filter_var(
        $_POST['stok'] ?? null,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 0]]
    );

    if ($barang === "" || strlen($barang) > 100 ||
        $stok === false || $stok === null) {
        $pesan = "Nama barang atau jumlah stok tidak valid.";
        $jenisPesan = "error";
    } else {
        if (!isset($_SESSION['stok'])) {
            $_SESSION['stok'] = [];
        }

        $_SESSION['stok'][$barang] = $stok;
        $pesan = "Stok berhasil diperbarui.";
    }
}

$menu = $_GET['menu'] ?? '';
$stokBarang = $_SESSION['stok'] ?? [];
$totalBarang = count($stokBarang);
$totalStok = array_sum($stokBarang);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Inventaris</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f3f6fb;
            margin: 0;
            color: #1e293b;
        }

        /* NAVBAR */
        .navbar {
            background: #172554;
            color: white;
            padding: 0 35px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 70px;
            flex-wrap: wrap;
            box-shadow: 0 3px 15px #0f172a25;
        }

        .brand {
            font-size: 21px;
            font-weight: 700;
            color: white;
            padding: 18px 0;
            letter-spacing: .3px;
        }

        .brand span {
            color: #60a5fa;
        }

        .nav-menu {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }

        .nav-menu a {
            text-decoration: none;
            color: #cbd5e1;
            padding: 11px 15px;
            border-radius: 8px;
            font-size: 14px;
            transition: .2s;
        }

        .nav-menu a:hover {
            background: #334155;
            color: white;
        }

        .nav-menu a.active {
            background: #2563eb;
            color: white;
        }

        .nav-menu a.logout {
            background: #dc2626;
            color: white;
        }

        .nav-menu a.logout:hover {
            background: #b91c1c;
        }

        /* KONTEN UTAMA */
        .container {
            max-width: 1200px;
            margin: 35px auto;
            padding: 0 25px 40px;
        }

        /* JUDUL HALAMAN */
        .page-heading {
            margin-bottom: 24px;
        }

        .page-heading h1 {
            margin: 0 0 7px;
            font-size: 28px;
            color: #172554;
        }

        .page-heading p {
            color: #64748b;
            margin: 0;
            font-size: 14px;
        }

        /* HERO */
        .hero {
            position: relative;
            overflow: hidden;
            background: linear-gradient(120deg, #1e40af, #2563eb, #3b82f6);
            border-radius: 18px;
            padding: 30px 32px;
            color: white;
            margin-bottom: 25px;
            box-shadow: 0 10px 25px #2563eb30;
        }

        .hero::before {
            content: "";
            position: absolute;
            width: 220px;
            height: 220px;
            border-radius: 50%;
            background: #ffffff12;
            right: 8%;
            top: -110px;
        }

        .hero::after {
            content: "";
            position: absolute;
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: #ffffff12;
            right: -25px;
            bottom: -90px;
        }

        .hero-content {
            position: relative;
            z-index: 1;
        }

        .hero-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #bfdbfe;
            margin-bottom: 10px;
        }

        .hero h2 {
            font-size: 26px;
            margin: 0 0 10px;
        }

        .hero p {
            color: #dbeafe;
            font-size: 14px;
            margin: 0;
            line-height: 1.7;
        }

        .hero-role {
            display: inline-block;
            margin-top: 18px;
            padding: 7px 13px;
            background: #ffffff20;
            border: 1px solid #ffffff45;
            border-radius: 20px;
            font-size: 12px;
            color: white;
        }

        /* KARTU STATISTIK */
        .stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 20px;
            margin-bottom: 28px;
        }

        .stat-card {
            background: white;
            padding: 22px;
            border-radius: 14px;
            box-shadow: 0 4px 15px #0f172a09;
            border: 1px solid #e8edf5;
            display: flex;
            align-items: center;
            gap: 16px;
            transition: transform .2s, box-shadow .2s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 22px #0f172a12;
        }

        .stat-icon {
            width: 52px;
            height: 52px;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 13px;
            font-size: 24px;
            flex-shrink: 0;
        }

        .icon-blue {
            background: #dbeafe;
            color: #2563eb;
        }

        .icon-green {
            background: #dcfce7;
            color: #16a34a;
        }

        .icon-purple {
            background: #f3e8ff;
            color: #9333ea;
        }

        .stat-info p {
            color: #64748b;
            font-size: 13px;
            margin: 0 0 7px;
        }

        .stat-info h3 {
            font-size: 25px;
            margin: 0;
            color: #172554;
        }

        /* PANEL */
        .panel {
            background: white;
            border-radius: 14px;
            padding: 25px;
            border: 1px solid #e8edf5;
            box-shadow: 0 4px 15px #0f172a09;
            margin-bottom: 25px;
        }

        .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .panel-header h2 {
            font-size: 19px;
            color: #172554;
            margin: 0;
        }

        .panel-description {
            color: #64748b;
            font-size: 13px;
            margin: 6px 0 0;
        }

        /* FORM UPDATE STOK */
        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            font-size: 13px;
            color: #334155;
            margin-bottom: 8px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            outline: none;
            font-size: 14px;
            background: #f8fafc;
            transition: .2s;
        }

        .form-group input:focus {
            border-color: #3b82f6;
            background: white;
            box-shadow: 0 0 0 3px #3b82f620;
        }

        .btn-primary {
            background: #2563eb;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: .2s;
        }

        .btn-primary:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
        }

        /* NOTIFIKASI */
        .pesan {
            padding: 13px 16px;
            border-radius: 9px;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .success {
            color: #166534;
            background: #dcfce7;
            border: 1px solid #bbf7d0;
        }

        .error {
            color: #991b1b;
            background: #fee2e2;
            border: 1px solid #fecaca;
        }

        /* TABEL */
        .table-wrapper {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            min-width: 300px;
        }

        th {
            background: #f1f5f9;
            color: #475569;
            font-weight: 600;
            padding: 14px 16px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        td {
            padding: 14px 16px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
        }

        tbody tr:hover {
            background: #f8fafc;
        }

        .stock-badge {
            display: inline-block;
            background: #dcfce7;
            color: #15803d;
            padding: 5px 11px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 12px;
        }

        .empty {
            text-align: center;
            padding: 35px 15px;
            color: #94a3b8;
            font-size: 14px;
        }

        /* FOOTER */
        .footer {
            text-align: center;
            color: #94a3b8;
            font-size: 12px;
            padding: 15px 0 0;
        }

        /* RESPONSIVE */
        @media (max-width: 750px) {
            .navbar {
                padding: 0 18px 12px;
                flex-direction: column;
                align-items: flex-start;
            }

            .brand {
                padding: 16px 0 8px;
            }

            .nav-menu {
                width: 100%;
                gap: 4px;
            }

            .nav-menu a {
                padding: 10px;
                font-size: 12px;
            }

            .stats {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .container {
                margin: 25px auto;
                padding: 0 15px 30px;
            }

            .hero {
                padding: 24px;
            }

            .hero h2 {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
    <div class="brand">
        <span>◆</span> Inventaris
    </div>

    <div class="nav-menu">
        <a href="dashboard.php"
           class="<?= $menu === '' ? 'active' : '' ?>">
            Dashboard
        </a>

        <a href="dashboard.php?menu=update"
           class="<?= $menu === 'update' ? 'active' : '' ?>">
            Update Stok
        </a>

        <a href="dashboard.php?menu=api">
           
            Output API
        </a>

        <a href="dashboard.php?logout=1" class="logout">
            Logout
        </a>
    </div>
</nav>

<!-- KONTEN UTAMA -->
<div class="container">

    <!-- JUDUL -->
    <div class="page-heading">
        <h1>
            <?= $menu === 'update' ? 'Update Stok' : 'Dashboard' ?>
        </h1>
        <p>Kelola dan pantau data inventaris Anda.</p>
    </div>

    <!-- HERO -->
    <div class="hero">
        <div class="hero-content">
            <div class="hero-label">INVENTARIS MANAGEMENT SYSTEM</div>
            <h2>
                Selamat Datang,
                <?= htmlspecialchars($_SESSION['username']) ?>!
            </h2>
            <p>
                Selamat bekerja. Pantau dan kelola inventaris
                melalui dashboard ini.
            </p>
            <span class="hero-role">
                ● <?= htmlspecialchars(ucfirst($_SESSION['role'])) ?>
            </span>
        </div>
    </div>

    <!-- STATISTIK -->
    <div class="stats">
        <div class="stat-card">
            <div class="stat-icon icon-blue">▣</div>
            <div class="stat-info">
                <p>Jenis Barang</p>
                <h3><?= $totalBarang ?></h3>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon icon-green">▤</div>
            <div class="stat-info">
                <p>Total Stok</p>
                <h3><?= $totalStok ?></h3>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon icon-purple">◉</div>
            <div class="stat-info">
                <p>Status Akun</p>
                <h3 style="font-size:20px;">Aktif</h3>
            </div>
        </div>
    </div>

    <!-- NOTIFIKASI -->
    <?php if ($pesan !== ""): ?>
        <p class="pesan <?= $jenisPesan ?>">
            <?= htmlspecialchars($pesan) ?>
        </p>
    <?php endif; ?>

    <!-- FORM UPDATE STOK -->
    <?php if ($menu === 'update'): ?>
        <div class="panel">
            <div class="panel-header">
                <div>
                    <h2>Perbarui Stok Barang</h2>
                    <p class="panel-description">
                        Masukkan nama barang dan jumlah stok terbaru.
                    </p>
                </div>
            </div>

            <form method="POST" action="dashboard.php?menu=update">
                <div class="form-group">
                    <label>Nama Barang</label>
                    <input type="text" name="barang"
                           maxlength="100"
                           placeholder="Contoh: Keyboard"
                           required>
                </div>

                <div class="form-group">
                    <label>Jumlah Stok</label>
                    <input type="number" name="stok"
                           min="0"
                           placeholder="Masukkan jumlah stok"
                           required>
                </div>

                <button type="submit" name="update_stok"
                        class="btn-primary">
                    Simpan Stok
                </button>
            </form>
        </div>
    <?php endif; ?>

    <!-- DAFTAR STOK -->
    <div class="panel">
        <div class="panel-header">
            <div>
                <h2>Daftar Inventaris</h2>
                <p class="panel-description">
                    Ringkasan stok barang yang tercatat.
                </p>
            </div>
        </div>

        <?php if (!empty($stokBarang)): ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Nama Barang</th>
                            <th>Jumlah Stok</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stokBarang as $nama => $jumlah): ?>
                            <tr>
                                <td><?= htmlspecialchars($nama) ?></td>
                                <td>
                                    <span class="stock-badge">
                                        <?= (int)$jumlah ?> unit
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty">
                <div style="font-size:36px; margin-bottom:10px;">▤</div>
                <strong>Belum ada data inventaris</strong>
                <p>Tambahkan barang melalui menu Update Stok.</p>
            </div>
        <?php endif; ?>
    </div>

    <div class="footer">
        Inventaris Management System &copy; 2026
    </div>
</div>

</body>
</html>