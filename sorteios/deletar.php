<?php
require_once "../config/conexao.php";
require_once "../config/cors.php";

$id = $_GET['id'] ?? null;

$stmt = $pdo->prepare("DELETE FROM sorteios WHERE id = ?");
$stmt->execute([$id]);

echo json_encode(["success" => true]);
