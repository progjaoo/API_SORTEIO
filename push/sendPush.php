<?php
/**
 * sendPush.php - Funções para envio de notificações via Expo Push Service
 * VERSÃO CORRIGIDA para usar Expo Notifications (não FCM)
 */
require_once "../config/conexao.php";
require_once "../config/cors.php";

/**
 * Envia notificação push para novos sorteios usando Expo Push Service
 * 
 * @param PDO $pdo Conexão com banco de dados
 * @param string $mensagem Mensagem da notificação
 * @param int $sorteioId ID do sorteio
 * @return array Resultado do envio
 */
function enviarPushNovoSorteio(PDO $pdo, string $mensagem, int $sorteioId): array {
    // Busca todos os tokens ativos
    $stmt = $pdo->query("
        SELECT expo_push_token, plataforma
        FROM dispositivos_push
        WHERE ativo = 1
    ");

    $dispositivos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!$dispositivos || count($dispositivos) === 0) {
        return [
            'success' => false,
            'erro' => 'Nenhum dispositivo registrado'
        ];
    }

    $tokens = array_column($dispositivos, 'expo_push_token');
    
    // Prepara mensagens para o Expo Push Service
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
            'channelId' => 'sorteios', // Canal Android específico
            'priority' => 'high',
            'badge' => 1
        ];
    }

    // Expo aceita até 100 notificações por requisição
    // Vamos enviar em lotes para garantir
    $lotes = array_chunk($messages, 100);
    
    $totalEnviadas = 0;
    $totalFalhas = 0;
    $tokensInvalidos = [];

    foreach ($lotes as $lote) {
        $resultado = enviarLoteExpo($pdo, $lote, $tokens);
        $totalEnviadas += $resultado['sucesso'];
        $totalFalhas += $resultado['falhas'];
        $tokensInvalidos = array_merge($tokensInvalidos, $resultado['tokens_invalidos']);
    }

    // Remove tokens inválidos do banco
    if (!empty($tokensInvalidos)) {
        removerTokensInvalidos($pdo, $tokensInvalidos);
    }

    // Log opcional
    error_log("Push notifications - Sucesso: $totalEnviadas, Falha: $totalFalhas, Removidos: " . count($tokensInvalidos));

    return [
        'success' => true,
        'total_dispositivos' => count($tokens),
        'enviadas' => $totalEnviadas,
        'falhas' => $totalFalhas,
        'tokens_removidos' => count($tokensInvalidos)
    ];
}

/**
 * Envia um lote de notificações via Expo Push Service
 */
function enviarLoteExpo(PDO $pdo, array $messages, array $tokens): array {
    $sucesso = 0;
    $falhas = 0;
    $tokensInvalidos = [];

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
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_TIMEOUT => 30
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    curl_close($ch);

    if ($httpCode !== 200) {
        error_log("Erro ao enviar Expo Push: HTTP $httpCode - $response");
        return [
            'sucesso' => 0,
            'falhas' => count($messages),
            'tokens_invalidos' => []
        ];
    }

    $responseData = json_decode($response, true);

    // Processa a resposta
    if (isset($responseData['data'])) {
        foreach ($responseData['data'] as $index => $result) {
            if ($result['status'] === 'ok') {
                $sucesso++;
            } else {
                $falhas++;
                
                // Verifica se o token é inválido
                if (isset($result['details']['error'])) {
                    $error = $result['details']['error'];
                    
                    // Erros que indicam token inválido
                    if (in_array($error, ['DeviceNotRegistered', 'InvalidCredentials', 'MessageTooBig', 'MessageRateExceeded'])) {
                        $tokensInvalidos[] = $messages[$index]['to'];
                        error_log("Token inválido: {$messages[$index]['to']} - Erro: $error");
                    }
                }
            }
        }
    }

    return [
        'sucesso' => $sucesso,
        'falhas' => $falhas,
        'tokens_invalidos' => $tokensInvalidos
    ];
}

function removerTokensInvalidos(PDO $pdo, array $tokens): void {
    if (empty($tokens)) return;

    $placeholders = str_repeat('?,', count($tokens) - 1) . '?';
    
    $stmt = $pdo->prepare("
        UPDATE dispositivos_push 
        SET ativo = 0 
        WHERE expo_push_token IN ($placeholders)
    ");

    $stmt->execute($tokens);
    
    error_log("Tokens inválidos removidos: " . count($tokens));
}

/**
 * Envia notificação de teste para um dispositivo específico
 * Útil para debug
 */
function enviarNotificacaoTeste(string $token, string $mensagem): array {
    $message = [
        'to' => $token,
        'sound' => 'default',
        'title' => '🧪 Teste de Notificação',
        'body' => $mensagem,
        'data' => [
            'tipo' => 'TESTE'
        ],
        'priority' => 'high',
        'badge' => 1
    ];

    $ch = curl_init('https://exp.host/--/api/v2/push/send');
    
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'Content-Type: application/json'
        ],
        CURLOPT_POSTFIELDS => json_encode([$message])
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    curl_close($ch);

    return [
        'success' => $httpCode === 200,
        'http_code' => $httpCode,
        'response' => json_decode($response, true)
    ];
}