<?php
require_once "../config/conexao.php";
require_once "../config/cors.php";

$stmt = $pdo->query("
    SELECT
        s.id,
        s.nome_sorteio,
        s.data_sorteio,
        s.imagem,
        p.nome_completo AS vencedor_nome
    FROM sorteios s
    INNER JOIN sorteios_vencedores sv 
        ON sv.sorteio_id = s.id
       AND sv.ativo = 1
    INNER JOIN participantes p
        ON p.id = sv.participante_id
    WHERE s.estado = 'FINALIZADO'
      AND s.data_sorteio >= DATE_SUB(CURDATE(), INTERVAL 60 DAY)
    ORDER BY s.data_sorteio DESC
");

echo json_encode(
    $stmt->fetchAll(PDO::FETCH_ASSOC),
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
);