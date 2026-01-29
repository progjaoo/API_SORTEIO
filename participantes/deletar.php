<?php
require_once "../config/conexao.php";
require_once "../config/cors.php";

$id = $_GET['id'] ?? null;

if (!$id) {
    http_response_code(400);
    echo json_encode(["erro" => "ID obrigatório"]);
    exit;
}

$stmt = $pdo->prepare("DELETE FROM participantes WHERE id = ?");
$stmt->execute([$id]);

echo json_encode(["success" => true]);
