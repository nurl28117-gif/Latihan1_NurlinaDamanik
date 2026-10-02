<?php
// update_stock.php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . "/koneksi.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: dashboard.php");
    exit;
}

$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT, [
    "options" => ["min_range" => 1]
]);
$stok = filter_input(INPUT_POST, "stok", FILTER_VALIDATE_INT, [
    "options" => ["min_range" => 0]
]);

if ($id === false || $id === null || $stok === false || $stok === null) {
    header("Location: dashboard.php?status=invalid");
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE alat SET stok = :stok WHERE id = :id");
    $stmt->execute([
        ":stok" => $stok,
        ":id" => $id
    ]);

    if ($stmt->rowCount() > 0) {
        header("Location: dashboard.php?status=updated");
    } else {
        // Bisa berarti stok tidak berubah atau ID tidak ditemukan.
        $check = $pdo->prepare("SELECT id FROM alat WHERE id = :id");
        $check->execute([":id" => $id]);
        header("Location: dashboard.php?status=" . ($check->fetch() ? "unchanged" : "notfound"));
    }
    exit;
} catch (PDOException $e) {
    header("Location: dashboard.php?status=error");
    exit;
}
