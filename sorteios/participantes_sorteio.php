<?php
require_once "../config/conexao.php";
require_once "../config/cors.php";

$sorteio_id = $_GET['id'] ?? null;

if (!$sorteio_id) {
    http_response_code(400);
    echo json_encode(["erro" => "ID do sorteio não informado"]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT
        p.id,
        p.nome_completo,
        p.email,
        p.telefone,
        p.cpf,
        ps.data_inscricao
    FROM participantes_sorteios ps
    JOIN participantes p ON p.id = ps.participante_id
    WHERE ps.sorteio_id = :id
    ORDER BY ps.data_inscricao DESC
");

$stmt->execute([":id" => $sorteio_id]);

echo json_encode($stmt->fetchAll());
// retorna participantes do sorteio