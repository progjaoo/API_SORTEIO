<?php
require_once "../config/conexao.php";
require_once "../config/cors.php";

$stmt = $pdo->query("
    SELECT id, nome, email, perfil, ativo
    FROM usuarios
");

echo json_encode($stmt->fetchAll());
