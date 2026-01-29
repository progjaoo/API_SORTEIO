<?php
ob_start();
// DEBUG TEMPORÁRIO (remova depois)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// NUNCA coloque header JSON em PDF
// header("Content-Type: application/json");

require_once __DIR__ . "/../config/conexao.php";
require_once "../config/cors.php";
require_once __DIR__ . "/../libs/fpdf/fpdf.php";

$sorteio_id = $_GET['sorteio_id'] ?? null;

if (!$sorteio_id) {
    die("ID do sorteio não informado");
}

/* ================= BUSCAR SORTEIO ================= */
$stmtSorteio = $pdo->prepare("
    SELECT nome_sorteio 
    FROM sorteios 
    WHERE id = :id
");

$stmtSorteio->execute([":id" => $sorteio_id]);

$sorteio = $stmtSorteio->fetch(PDO::FETCH_ASSOC);
$nomeSorteio = $sorteio ? $sorteio['nome_sorteio'] : "Sorteio";

/* ================= BUSCAR PARTICIPANTES ================= */
$stmt = $pdo->prepare("
    SELECT
    p.id AS participante_id,
    p.nome_completo,
    p.email,
    p.telefone,
    p.cpf,
    p.cep,
    p.logradouro,
    p.numero,
    p.bairro,
    p.cidade,
    p.estado
FROM participantes_sorteios ps
INNER JOIN participantes p 
    ON p.id = ps.participante_id
WHERE ps.sorteio_id = :sorteio_id
ORDER BY p.id ASC

");


$stmt->execute([
    ":sorteio_id" => $sorteio_id
]);

$participantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ================= PDF ================= */
$pdf = new FPDF('L', 'mm', 'A4');
$pdf->AddPage();

/* TÍTULO */
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, utf8_decode("Participantes do Sorteio: {$nomeSorteio}"), 0, 1, 'C');
$pdf->Ln(4);

/* INFO */
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, "Total de participantes: " . count($participantes), 0, 1);
$pdf->Cell(0, 6, "Gerado em: " . date('d/m/Y H:i'), 0, 1);
$pdf->Ln(5);

/* CABEÇALHO */
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(12, 8, "ID", 1);
$pdf->Cell(40, 8, "Nome", 1);
$pdf->Cell(55, 8, "Email", 1);
$pdf->Cell(28, 8, "Telefone", 1);
$pdf->Cell(28, 8, "CPF", 1);
$pdf->Cell(22, 8, "CEP", 1);
$pdf->Cell(110, 8, "Endereco", 1);
$pdf->Ln();

/* DADOS */
$pdf->SetFont('Arial', '', 8);

foreach ($participantes as $p) {

    $enderecoCompleto =
        "{$p['logradouro']}, {$p['numero']} - {$p['bairro']} - {$p['cidade']}/{$p['estado']}";

    $pdf->Cell(12, 8, $p['participante_id'], 1);
    $pdf->Cell(40, 8, utf8_decode($p['nome_completo']), 1);
    $pdf->Cell(55, 8, utf8_decode($p['email']), 1);
    $pdf->Cell(28, 8, $p['telefone'], 1);
    $pdf->Cell(28, 8, $p['cpf'], 1);
    $pdf->Cell(22, 8, $p['cep'], 1);
    $pdf->Cell(110, 8, utf8_decode($enderecoCompleto), 1);
    $pdf->Ln();
}


ob_end_clean();
/* SAÍDA FINAL — NADA PODE VIR DEPOIS DISSO */
$pdf->Output("I", "participantes_sorteio_{$sorteio_id}.pdf");

exit;