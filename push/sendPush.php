<?php
require_once "../config/cors.php";

function enviarPushNovoSorteio(PDO $pdo, string $mensagem, int $sorteioId) {
    $stmt = $pdo->query("
        SELECT expo_push_token
        FROM dispositivos_push
        WHERE ativo = 1
    ");

    $tokens = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (!$tokens) return;

    $payload = [];

    foreach ($tokens as $token) {
        $payload[] = [
            'to' => $token,
            'sound' => 'default',
            'title' => '🎉 Novo sorteio disponível!',
            'body' => $mensagem,
            'data' => [
                'sorteio_id' => $sorteioId,
                'tipo' => 'NOVO_SORTEIO'
            ]
        ];
    }

    $ch = curl_init("https://exp.host/--/api/v2/push/send");
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
        CURLOPT_POSTFIELDS => json_encode($payload)
    ]);

    curl_exec($ch);
    curl_close($ch);
}
