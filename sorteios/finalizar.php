<?php
require_once "../config/conexao.php";
require_once "../config/cors.php";

$data = json_decode(file_get_contents("php://input"), true);

$stmt = $pdo->prepare("
  UPDATE sorteios
  SET estado = 'FINALIZADO'
  WHERE id = :id
");

$stmt->execute([":id" => $data['id']]);

echo json_encode(["success" => true]);
