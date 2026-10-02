<?php
// api_alat.php
session_start();

if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    header("Content-Type: application/json; charset=utf-8");
    echo json_encode([
        "status" => "error",
        "message" => "Silakan login terlebih dahulu."
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

require_once __DIR__ . "/koneksi.php";

header("Content-Type: application/json; charset=utf-8");

try {
    $stmt = $pdo->query("SELECT id, nama_alat, stok FROM alat ORDER BY id ASC");
    $alat = $stmt->fetchAll();

    echo json_encode([
        "status" => "success",
        "message" => "Data inventaris berhasil diambil.",
        "jumlah" => count($alat),
        "data" => $alat
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Gagal mengambil data inventaris."
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
