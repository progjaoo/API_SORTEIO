<?php
require_once "../config/conexao.php";
require_once "../config/cors.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['token'])) {
    http_response_code(400);
    echo json_encode(['erro' => 'Token não enviado']);
    exit;
}

$stmt = $pdo->prepare("
  INSERT INTO dispositivos_push (expo_push_token, plataforma, ativo)
  VALUES (:token, :plataforma, 1)
  ON DUPLICATE KEY UPDATE ativo = 1
");

$stmt->execute([
  ':token' => $data['token'],
  ':plataforma' => $data['plataforma'] ?? null
]);

echo json_encode(['success' => true]);
