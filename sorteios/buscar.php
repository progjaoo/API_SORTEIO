<?php
require_once "../config/conexao.php";
require_once "../config/cors.php";

$id = $_GET['id'] ?? null;

$stmt = $pdo->prepare("SELECT * FROM sorteios WHERE id = ?");
$stmt->execute([$id]);

echo json_encode($stmt->fetch());