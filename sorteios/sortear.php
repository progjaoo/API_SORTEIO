<?php
header('Content-Type: application/json; charset=UTF-8');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "../config/conexao.php";
require_once "../services/MailService.php";
require_once "../emails/sorteado.php";
require_once "../config/cors.php";

$data = json_decode(file_get_contents("php://input"), true);
$sorteioId = $data['sorteio_id'] ?? null;

if (!$sorteioId) {
    http_response_code(400);
    echo json_encode(["erro" => "sorteio_id obrigatório"]);
    exit;
}

try {
    $pdo->beginTransaction();

    // Participantes
    $stmt = $pdo->prepare("
        SELECT p.id, p.nome_completo, p.email
        FROM participantes_sorteios ps
        JOIN participantes p ON p.id = ps.participante_id
        WHERE ps.sorteio_id = ?
    ");
    $stmt->execute([$sorteioId]);
    $participantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$participantes) {
        throw new Exception("Nenhum participante inscrito");
    }

    $vencedor = $participantes[array_rand($participantes)];

    // Sorteio
    $stmt = $pdo->prepare("SELECT nome_sorteio FROM sorteios WHERE id = ?");
    $stmt->execute([$sorteioId]);
    $sorteio = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$sorteio) {
        throw new Exception("Sorteio não encontrado");
    }

    // Re-sorteio?
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM sorteios_vencedores
        WHERE sorteio_id = ? AND ativo = 1
    ");
    $stmt->execute([$sorteioId]);
    $reSorteado = $stmt->fetchColumn() > 0 ? 1 : 0;

    // Desativa anterior
    $pdo->prepare("
        UPDATE sorteios_vencedores SET ativo = 0 WHERE sorteio_id = ?
    ")->execute([$sorteioId]);

    // Insere vencedor
    $pdo->prepare("
        INSERT INTO sorteios_vencedores
        (sorteio_id, participante_id, re_sorteado, ativo)
        VALUES (?, ?, ?, 1)
    ")->execute([$sorteioId, $vencedor['id'], $reSorteado]);

    // Finaliza sorteio
    $pdo->prepare("
        UPDATE sorteios SET estado = 'FINALIZADO' WHERE id = ?
    ")->execute([$sorteioId]);

    $pdo->commit();

    // Email (não quebra fluxo)
    $emailEnviado = false;
    try {
        MailService::enviar(
            $vencedor['email'],
            $vencedor['nome_completo'],
            "🎉 Você foi sorteado!",
            emailSorteado($vencedor['nome_completo'], $sorteio['nome_sorteio'])
        );
        $emailEnviado = true;
    } catch (Exception $e) {
        error_log($e->getMessage());
    }

    echo json_encode([
        "success" => true,
        "vencedor" => $vencedor['nome_completo'],
        "email" => $vencedor['email'],
        "email_enviado" => $emailEnviado
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(500);
    echo json_encode([
        "erro" => "Erro ao sortear",
        "detalhe" => $e->getMessage()
    ]);

    $data = json_decode(file_get_contents("php://input"), true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode([
            "erro" => "JSON inválido",
            "detalhe" => json_last_error_msg()
        ]);
        exit;
    }
    $sorteioId = $data['sorteio_id'] ?? null;
}