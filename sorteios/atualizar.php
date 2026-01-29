<?php
require_once "../config/conexao.php";
require_once "../config/cors.php";

header("Content-Type: application/json");

// ================= DADOS =================
$id          = $_POST['id'] ?? null;
$nome        = $_POST['nome_sorteio'] ?? null;
$descricao   = $_POST['descricao'] ?? null;
$dataSorteio = $_POST['data_sorteio'] ?? null;
$dataFinal   = $_POST['data_final_cadastro'] ?? null;
$estado      = $_POST['estado'] ?? null;

// ================= VALIDAÇÃO =================
if (!$id || !$nome || !$dataSorteio || !$dataFinal) {
    http_response_code(400);
    echo json_encode(["erro" => "Campos obrigatórios não enviados"]);
    exit;
}

// ================= IMAGEM =================
$imagemBase64 = null;

if (!empty($_FILES['imagem']['tmp_name'])) {
    $tipo = mime_content_type($_FILES['imagem']['tmp_name']);
    $permitidos = ['image/jpeg', 'image/png', 'image/webp'];

    if (!in_array($tipo, $permitidos)) {
        http_response_code(400);
        echo json_encode(["erro" => "Formato de imagem inválido"]);
        exit;
    }

    $conteudo = file_get_contents($_FILES['imagem']['tmp_name']);
    $imagemBase64 = "data:$tipo;base64," . base64_encode($conteudo);
}

// ================= SQL DINÂMICO =================
$sql = "
    UPDATE sorteios SET
        nome_sorteio = :nome,
        descricao = :descricao,
        data_sorteio = :data_sorteio,
        data_final_cadastro = :data_final,
        estado = :estado,
        atualizado_em = NOW()
";

if ($imagemBase64) {
    $sql .= ", imagem = :imagem";
}

$sql .= " WHERE id = :id";

// ================= PARAMS =================
$params = [
    ":id" => $id,
    ":nome" => $nome,
    ":descricao" => $descricao,
    ":data_sorteio" => $dataSorteio,
    ":data_final" => $dataFinal,
    ":estado" => $estado
];

if ($imagemBase64) {
    $params[":imagem"] = $imagemBase64;
}

// ================= EXECUÇÃO =================
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    echo json_encode(["success" => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "erro" => "Erro ao atualizar sorteio",
        "detalhe" => $e->getMessage()
    ]);
}
