
<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";
    $file = __DIR__ . "/users.json";

    $users = file_exists($file)
        ? json_decode(file_get_contents($file), true)
        : [];

    if (!is_array($users)) {
        $users = [];
    }

    $account = null;

    foreach ($users as $user) {
        if (strcasecmp($user["username"], $username) === 0) {
            $account = $user;
            break;
        }
    }

    if ($account !== null &&
        password_verify($password, $account["password"])) {

        session_regenerate_id(true);

        $_SESSION["user_id"] = $account["id"];
        $_SESSION["username"] = $account["username"];
        $_SESSION["role"] = $account["role"];

        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Username atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Inventaris</title>
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

        .login-box {
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
            margin-top: 15px;
        }

        input {
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

        .link {
            text-align: center;
            margin-top: 18px;
        }

        a { color: #2563eb; }
    </style>
</head>
<body>
<div class="login-box">
    <h2>Login Inventaris</h2>

    <?php if ($error !== ""): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <label>Username</label>
        <input type="text" name="username"
               placeholder="Masukkan username" required>

        <label>Password</label>
        <input type="password" name="password"
               placeholder="Masukkan password" required>

        <button type="submit">Login</button>
    </form>

    <div class="link">
        Belum punya akun?
        <a href="register.php">Daftar sekarang</a>
    </div>
</div>
</body>
</html>