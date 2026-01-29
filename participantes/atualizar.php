<?php
require_once "../config/conexao.php";
require_once "../config/cors.php";

$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['id'])) {
    http_response_code(400);
    echo json_encode(["erro" => "ID obrigatório"]);
    exit;
}

$sql = "UPDATE participantes SET
    nome_completo = :nome,
    email = :email,
    telefone = :telefone,
    cpf = :cpf,
    endereco = :endereco
WHERE id = :id";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ":nome"     => $data['nome_completo'],
    ":email"    => $data['email'],
    ":telefone" => $data['telefone'],
    ":cpf"      => $data['cpf'],
    ":endereco" => $data['endereco'],
    ":id"       => $data['id']
]);

echo json_encode(["success" => true]);
