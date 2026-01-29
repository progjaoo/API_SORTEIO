<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . "/../config/conexao.php";
require_once "../config/cors.php";

/* ================= VALIDAR PARAMETRO ================= */
$sorteio_id = $_GET['sorteio_id'] ?? null;
if (!$sorteio_id) {
    http_response_code(400);
    exit;
}

/* ================= BUSCAR PARTICIPANTES ================= */
$stmt = $pdo->prepare("
    SELECT
        p.nome_completo,
        p.email,
        p.telefone,
        p.cpf,
        ps.codigo_sorteado,
        p.cep,
        p.logradouro,
        p.numero,
        p.bairro,
        p.cidade,
        p.estado
    FROM participantes_sorteios ps
    INNER JOIN participantes p ON p.id = ps.participante_id
    WHERE ps.sorteio_id = :id
    ORDER BY p.nome_completo ASC
");
$stmt->execute([":id" => $sorteio_id]);
$participants = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ================= HEADERS CSV ================= */
$filename = "participantes_sorteio_{$sorteio_id}.csv";

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

/* ================= OUTPUT ================= */
$output = fopen('php://output', 'w');

// BOM para Excel (acentos corretos)
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Cabeçalho
fputcsv($output, [
    'Id',
    'Nome',
    'Email',
    'Telefone',
    'CPF',
    'Código Sorteado',
    'CEP',
    'Endereço Completo',
    'Bairro',
    'Cidade',
    'Estado'
], ';');

// Dados
$contador = 1;

foreach ($participants as $p) {
    $endereco = "{$p['logradouro']}, {$p['numero']} - {$p['bairro']} - {$p['cidade']}/{$p['estado']} - CEP {$p['cep']}";

    fputcsv($output, [
        $contador,
        $p['nome_completo'],
        $p['email'],
        $p['telefone'],
        $p['cpf'],
        $p['codigo_sorteado'],
        $p['cep'],
        $endereco,
        $p['bairro'],
        $p['cidade'],
        $p['estado']
    ], ';');

    $contador++;
}

fclose($output);
exit;
?>