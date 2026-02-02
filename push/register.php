<?php
/**
 * register.php - Registra tokens FCM dos dispositivos
 */

require_once "../config/conexao.php";
require_once "../config/cors.php";

// Recebe os dados
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['token']) || empty($data['token'])) {
    http_response_code(400);
    echo json_encode(['erro' => 'Token não enviado']);
    exit;
}

$token = $data['token'];
$plataforma = strtoupper($data['plataforma'] ?? 'ANDROID');

// Valida a plataforma
if (!in_array($plataforma, ['ANDROID', 'IOS', 'WEB'])) {
    http_response_code(400);
    echo json_encode(['erro' => 'Plataforma inválida']);
    exit;
}

try {
    // Insere ou atualiza o token
    $stmt = $pdo->prepare("
        INSERT INTO dispositivos_push (expo_push_token, plataforma, ativo, criado_em)
        VALUES (:token, :plataforma, 1, NOW())
        ON DUPLICATE KEY UPDATE 
            plataforma = :plataforma,
            ativo = 1,
            criado_em = NOW()
    ");

    $stmt->execute([
        ':token' => $token,
        ':plataforma' => $plataforma
    ]);

    echo json_encode([
        'success' => true,
        'mensagem' => 'Token registrado com sucesso'
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'erro' => 'Erro ao registrar token',
        'detalhes' => $e->getMessage()
    ]);
}