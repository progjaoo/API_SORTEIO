<?php
require_once "../config/conexao.php";
require_once "../config/cors.php";

header("Content-Type: application/json; charset=UTF-8");



$sorteio_id = $_GET['sorteio_id'] ?? null;

if (!$sorteio_id) {
    http_response_code(400);
    echo json_encode([
        "erro" => "ID do sorteio não informado"
    ]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT
        p.id                AS participante_id,
        p.nome_completo,
        p.email,
        p.telefone,
        p.cpf,
        p.cep,
        p.logradouro,
        p.numero,
        p.bairro,
        p.cidade,
        p.estado,
        ps.data_inscricao
    FROM participantes_sorteios ps
    INNER JOIN participantes p 
        ON p.id = ps.participante_id
    WHERE ps.sorteio_id = :sorteio_id
    ORDER BY ps.data_inscricao DESC
");

$stmt->execute([
    ":sorteio_id" => $sorteio_id
]);

$inscritos = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    "sorteio_id" => (int)$sorteio_id,
    "total_inscritos" => count($inscritos),
    "participantes" => $inscritos
]);
// lista participantes inscritos no sorteio