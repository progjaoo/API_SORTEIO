<?php
require_once "../config/conexao.php";
require_once "../config/cors.php";

$id = $_GET['id'] ?? null;

if (!$id) {
    http_response_code(400);
    echo json_encode(["erro" => "ID do participante não informado"]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT 
        id,
        nome_completo,
        email,
        telefone,
        cpf,
        endereco
    FROM participantes
    WHERE id = :id
");

$stmt->execute([":id" => $id]);
$participante = $stmt->fetch();

if (!$participante) {
    http_response_code(404);
    echo json_encode(["erro" => "Participante não encontrado"]);
    exit;
}

echo json_encode($participante);
