<?php
require_once "../config/conexao.php";
require_once "../config/cors.php";

$data = json_decode(file_get_contents("php://input"), true);

$participante_id = $data['participante_id'] ?? null;
$sorteio_id      = $data['sorteio_id'] ?? null;

if (!$participante_id || !$sorteio_id) {
    http_response_code(400);
    echo json_encode(["erro" => "Dados obrigatórios não informados"]);
    exit;
}

/**
 * 1️⃣ Valida sorteio
 */
$stmt = $pdo->prepare("
    SELECT id, estado, data_final_cadastro
    FROM sorteios
    WHERE id = :id
");
$stmt->execute([":id" => $sorteio_id]);
$sorteio = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$sorteio) {
    http_response_code(404);
    echo json_encode(["erro" => "Sorteio não encontrado"]);
    exit;
}

if ($sorteio['estado'] !== 'ABERTO') {
    http_response_code(400);
    echo json_encode(["erro" => "Sorteio não está aberto para inscrições"]);
    exit;
}

if (date('Y-m-d') > $sorteio['data_final_cadastro']) {
    http_response_code(403);
    echo json_encode([
        "erro" => "INSCRICOES_ENCERRADAS",
        "mensagem" => "Período de inscrição encerrado"
    ]);
    exit;
}

/**
 * 2️⃣ Verifica se o participante já está inscrito
 */
$stmt = $pdo->prepare("
    SELECT id
    FROM participantes_sorteios
    WHERE participante_id = :participante
      AND sorteio_id = :sorteio
");
$stmt->execute([
    ":participante" => $participante_id,
    ":sorteio"      => $sorteio_id
]);

if ($stmt->fetch()) {
    http_response_code(409);
    echo json_encode(["erro" => "Participante já inscrito neste sorteio"]);
    exit;
}

try {
    $pdo->beginTransaction();

    /**
     * 3️⃣ Insere inscrição
     */
    $stmt = $pdo->prepare("
        INSERT INTO participantes_sorteios (participante_id, sorteio_id)
        VALUES (:participante, :sorteio)
    ");
    $stmt->execute([
        ":participante" => $participante_id,
        ":sorteio"      => $sorteio_id
    ]);

    // ID único da inscrição
    $inscricaoId = $pdo->lastInsertId();

    /**
     * 4️⃣ Gera código do sorteado
     * FORMATO: ANO_IDINSCRICAO_IDSORTEIO
     * Ex: 2026_153_9
     */
    $ano = date('Y');

    $codigoSorteado = "{$ano}_{$inscricaoId}000_{$sorteio_id}";

    /**
     * 5️⃣ Atualiza código do sorteado
     */
    $stmt = $pdo->prepare("
        UPDATE participantes_sorteios
        SET codigo_sorteado = :codigo
        WHERE id = :id
    ");
    $stmt->execute([
        ":codigo" => $codigoSorteado,
        ":id"     => $inscricaoId
    ]);

    $pdo->commit();

    echo json_encode([
        "success"         => true,
        "message"         => "Inscrição realizada com sucesso",
        "codigo_sorteado" => $codigoSorteado
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode([
        "erro" => "Erro ao realizar inscrição"
    ]);
}
