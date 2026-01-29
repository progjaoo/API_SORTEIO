<?php
require_once "../config/conexao.php";
require_once "../config/cors.php";

$cpf = $_GET['cpf'] ?? null;

if (!$cpf) {
    http_response_code(400);
    echo json_encode(["erro" => "CPF não informado"]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT
        s.nome_sorteio,
        s.data_sorteio,
        ps.codigo_sorteado
    FROM participantes p
    INNER JOIN participantes_sorteios ps
        ON ps.participante_id = p.id
    INNER JOIN sorteios s
        ON s.id = ps.sorteio_id
    WHERE p.cpf = :cpf
    ORDER BY s.data_sorteio DESC
");

$stmt->execute([":cpf" => $cpf]);
$resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$resultado) {
    echo json_encode([]);
    exit;
}

echo json_encode($resultado);
