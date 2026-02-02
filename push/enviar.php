<?php
/**
 * enviar.php - Envia notificações push via Expo Push Notification Service
 * 
 * Este arquivo envia notificações para todos os dispositivos registrados
 * usando o serviço de push do Expo
 */

require_once "../config/conexao.php";
require_once "../config/cors.php";

// Recebe os dados da requisição
$data = json_decode(file_get_contents("php://input"), true);

$mensagem  = $data['mensagem'] ?? 'Novo sorteio no Ar';
$sorteioId = $data['sorteio_id'] ?? null;

// Busca todos os tokens ativos do banco de dados
$stmt = $pdo->query("
    SELECT expo_push_token 
    FROM dispositivos_push
    WHERE ativo = 1
");

$tokens = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (!$tokens || count($tokens) === 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'erro' => 'Nenhum token registrado'
    ]);
    exit;
}

// Prepara as mensagens para o Expo Push Service
$messages = [];

foreach ($tokens as $token) {
    $messages[] = [
        'to' => $token,
        'sound' => 'default',
        'title' => '📢 Sorteio Rádio 89 Maravilha',
        'body' => $mensagem,
        'data' => [
            'tipo' => 'NOVO_SORTEIO',
            'sorteio_id' => (string)$sorteioId
        ],
        'channelId' => 'sorteios', // Canal específico para sorteios
        'priority' => 'high',
        'badge' => 1
    ];
}

// Envia para o Expo Push Notification Service
$ch = curl_init('https://exp.host/--/api/v2/push/send');

curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Accept: application/json',
        'Accept-Encoding: gzip, deflate',
        'Content-Type: application/json'
    ],
    CURLOPT_POSTFIELDS => json_encode($messages),
    CURLOPT_SSL_VERIFYPEER => true
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

if ($httpCode !== 200) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'erro' => 'Erro ao enviar notificações',
        'http_code' => $httpCode,
        'response' => $response
    ]);
    exit;
}

$responseData = json_decode($response, true);

// Processa a resposta e remove tokens inválidos
$tokensInvalidos = [];
$sucesso = 0;
$falha = 0;

if (isset($responseData['data'])) {
    foreach ($responseData['data'] as $index => $result) {
        if ($result['status'] === 'ok') {
            $sucesso++;
        } else {
            $falha++;
            
            // Se o token for inválido, marca para remoção
            if (isset($result['details']) && 
                isset($result['details']['error']) &&
                in_array($result['details']['error'], ['DeviceNotRegistered', 'InvalidCredentials'])) {
                $tokensInvalidos[] = $tokens[$index];
            }
        }
    }
}

// Remove tokens inválidos do banco
if (!empty($tokensInvalidos)) {
    $placeholders = str_repeat('?,', count($tokensInvalidos) - 1) . '?';
    
    $stmtDelete = $pdo->prepare("
        UPDATE dispositivos_push 
        SET ativo = 0 
        WHERE expo_push_token IN ($placeholders)
    ");
    
    $stmtDelete->execute($tokensInvalidos);
}

// Log dos resultados (opcional)
error_log("Push notifications - Sucesso: $sucesso, Falha: $falha, Removidos: " . count($tokensInvalidos));

echo json_encode([
    'success' => true,
    'total_enviadas' => count($tokens),
    'sucesso' => $sucesso,
    'falhas' => $falha,
    'tokens_removidos' => count($tokensInvalidos)
]);