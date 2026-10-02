
<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$file = __DIR__ . "/users.json";
$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm = $_POST["confirm_password"] ?? "";
    $role = $_POST["role"] ?? "";

    if ($username === "" || strlen($username) < 3) {
        $error = "Username minimal 3 karakter.";
    } elseif (strlen($username) > 30 ||
              !preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $error = "Username hanya boleh berisi huruf, angka, dan _ (maksimal 30 karakter).";
    } elseif (strlen($password) < 8) {
        $error = "Password minimal 8 karakter.";
    } elseif ($password !== $confirm) {
        $error = "Konfirmasi password tidak cocok.";
    } elseif (!in_array($role, ["admin", "teknisi"], true)) {
        $error = "Role tidak valid.";
    } else {
        if (!file_exists($file)) {
            file_put_contents($file, "[]", LOCK_EX);
        }

        $users = json_decode(file_get_contents($file), true);
        if (!is_array($users)) {
            $users = [];
        }

        $exists = false;
        foreach ($users as $user) {
            if (strcasecmp($user["username"], $username) === 0) {
                $exists = true;
                break;
            }
        }

        if ($exists) {
            $error = "Username sudah digunakan.";
        } else {
            $users[] = [
                "id" => bin2hex(random_bytes(8)),
                "username" => $username,
                "password" => password_hash(
                    $password,
                    PASSWORD_DEFAULT
                ),
                "role" => $role
            ];

            $json = json_encode(
                $users,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
            );

            if (file_put_contents($file, $json, LOCK_EX) !== false) {
                $success = "Registrasi berhasil! Silakan login.";
            } else {
                $error = "Gagal menyimpan akun. Periksa izin folder.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registrasi Inventaris</title>
    <style>
        * { box-sizing: border-box; }

        body {
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .register-box {
            background: white;
            padding: 30px;
            border-radius: 12px;
            width: 100%;
            max-width: 380px;
            box-shadow: 0 4px 15px #0002;
        }

        h2 {
            text-align: center;
            color: #2563eb;
        }

        label {
            display: block;
            margin-top: 14px;
        }

        input, select {
            width: 100%;
            padding: 11px;
            margin-top: 6px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
        }

        button {
            width: 100%;
            padding: 12px;
            margin-top: 20px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        button:hover { background: #1d4ed8; }

        .error { color: #dc2626; }
        .success { color: #15803d; }

        .link {
            text-align: center;
            margin-top: 18px;
        }

        a { color: #2563eb; }
    </style>
</head>
<body>
<div class="register-box">
    <h2>Registrasi Akun</h2>

    <?php if ($error !== ""): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if ($success !== ""): ?>
        <p class="success"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <form method="POST" action="register.php">
        <label>Username</label>
        <input type="text" name="username"
               minlength="3" maxlength="30"
               pattern="[a-zA-Z0-9_]+"
               placeholder="Buat username" required>

        <label>Password</label>
        <input type="password" name="password"
               minlength="8"
               placeholder="Minimal 8 karakter" required>

        <label>Konfirmasi Password</label>
        <input type="password" name="confirm_password"
               minlength="8"
               placeholder="Ulangi password" required>

        <label>Pilih Role</label>
        <select name="role" required>
            <option value="">-- Pilih Role --</option>
            <option value="admin">Admin</option>
            <option value="teknisi">Teknisi</option>
        </select>

        <button type="submit">Daftar</button>
    </form>

    <div class="link">
        Sudah punya akun?
        <a href="login.php">Login di sini</a>
    </div>
</div>
</body>
</html>