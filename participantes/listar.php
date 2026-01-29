<?php
require_once "../config/conexao.php";
require_once "../config/cors.php";

$stmt = $pdo->query("
    SELECT id, nome_completo, email, telefone, cpf
    FROM participantes
");

echo json_encode($stmt->fetchAll());
