<?php
require_once "../config/conexao.php";
require_once "../config/cors.php";

$stmt = $pdo->prepare("
    SELECT 
        id,
        nome_sorteio,
        descricao,
        data_sorteio,
        data_final_cadastro,
        estado,
        criado_por,
        imagem
    FROM sorteios
    WHERE
        estado <> 'FINALIZADO'
        AND DATE(data_sorteio) >= CURDATE()
    ORDER BY data_sorteio ASC
");

$stmt->execute();

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
