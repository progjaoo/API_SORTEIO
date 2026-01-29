<?php
require_once "../config/conexao.php";
require_once "../config/cors.php";

$stmt = $pdo->query("
    SELECT
        s.id AS sorteio_id,
        s.nome_sorteio,
        s.descricao,
        s.data_sorteio,

        p.nome_completo AS vencedor_nome,
        p.email AS vencedor_email,
        p.telefone AS vencedor_telefone,
        p.cpf AS vencedor_cpf,
        p.cep AS vencedor_cep,
        p.logradouro AS vencedor_logradouro,
        p.numero AS vencedor_numero,
        p.bairro AS vencedor_bairro,
        p.cidade AS vencedor_cidade,
        p.estado AS vencedor_estado,

        ps.codigo_sorteado,

        sv.re_sorteado

    FROM sorteios s

    LEFT JOIN sorteios_vencedores sv 
        ON sv.id = (
            SELECT sv2.id
            FROM sorteios_vencedores sv2
            WHERE sv2.sorteio_id = s.id
              AND sv2.ativo = 1
            ORDER BY sv2.data_sorteio DESC
            LIMIT 1
        )

    LEFT JOIN participantes p 
        ON p.id = sv.participante_id

    LEFT JOIN participantes_sorteios ps
        ON ps.participante_id = p.id
       AND ps.sorteio_id = s.id

    WHERE s.estado = 'FINALIZADO'
    ORDER BY s.data_sorteio DESC
");

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
