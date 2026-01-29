<?php
header('Content-Type: application/json');

require_once "../config/conexao.php";
require_once "../config/cors.php";

try {
    $stmt = $pdo->prepare("
        SELECT 
            id,
            nome_sorteio,
            descricao,
            data_sorteio,
            estado,
            imagem
        FROM sorteios
        WHERE estado = 'ABERTO'
        ORDER BY data_sorteio DESC
        LIMIT 1
    ");

    $stmt->execute();
    $sorteio = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($sorteio) {
        echo json_encode([
            'existe' => true,
            'sorteio' => $sorteio
        ]);
    } else {
        echo json_encode([
            'existe' => false
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'erro' => true,
        'mensagem' => 'Erro ao verificar sorteio em aberto'
    ]);
}
