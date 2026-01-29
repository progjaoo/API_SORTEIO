<?php
require_once "../config/conexao.php";
require_once "../config/cors.php";

$participante_id = $_GET['id'] ?? null;

if (!$participante_id) {
    http_response_code(400);
    echo json_encode(["erro" => "ID do participante não informado"]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT
        s.id,
        s.nome_sorteio,
        s.estado,
        s.data_sorteio,
        ps.data_inscricao
    FROM participantes_sorteios ps
    JOIN sorteios s ON s.id = ps.sorteio_id
    WHERE ps.participante_id = :id
    ORDER BY ps.data_inscricao DESC
");

$stmt->execute([":id" => $participante_id]);

echo json_encode($stmt->fetchAll());

// retorna sorteis do participante