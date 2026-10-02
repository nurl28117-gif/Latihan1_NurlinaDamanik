<?php
$hash = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $password = $_POST["password"] ?? "";

    if ($password === "") {
        $error = "Password tidak boleh kosong.";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Generate Password Hash</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0; padding: 24px; min-height: 100vh;
            display: grid; place-items: center;
            font-family: Arial, sans-serif; background: #f1f5f9; color: #1e293b;
        }
        .card {
            width: 100%; max-width: 480px; background: white; padding: 28px;
            border-radius: 14px; box-shadow: 0 8px 24px #0f172a14;
        }
        h1 { margin: 0 0 8px; color: #172554; font-size: 23px; }
        p { color: #64748b; font-size: 14px; line-height: 1.5; }
        label { display: block; margin: 18px 0 7px; font-weight: 600; font-size: 14px; }
        input, textarea {
            width: 100%; padding: 11px 12px; border: 1px solid #cbd5e1;
            border-radius: 8px; font: inherit;
        }
        button {
            margin-top: 15px; border: 0; border-radius: 8px; padding: 11px 16px;
            background: #2563eb; color: white; font-weight: 600; cursor: pointer;
        }
        .error { color: #b91c1c; }
        textarea { margin-top: 8px; min-height: 90px; }
        .note { font-size: 12px; }
    </style>
</head>
<body>
<main class="card">
    <h1>Generate Password Hash</h1>
    <p>Gunakan halaman ini untuk membuat hash password dengan <code>password_hash()</code>.</p>

    <?php if ($error !== ""): ?>
        <p class="error"><?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?></p>
    <?php endif; ?>

    <form method="POST">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
        <button type="submit">Buat Hash</button>
    </form>

    <?php if ($hash !== ""): ?>
        <label for="hash">Hasil hash</label>
        <textarea id="hash" readonly><?= htmlspecialchars($hash, ENT_QUOTES, "UTF-8") ?></textarea>
        <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('hash').value)">Salin Hash</button>
    <?php endif; ?>

    <p class="note">Untuk keamanan, hapus atau nonaktifkan file ini setelah digunakan.</p>
</main>
</body>
</html>
