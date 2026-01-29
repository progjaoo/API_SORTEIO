<?php
require_once "../config/conexao.php";
require_once "../config/cors.php";

$data = json_decode(file_get_contents("php://input"), true);

if (
    empty($data['nome']) ||
    empty($data['email']) ||
    empty($data['senha'])
) {
    http_response_code(400);
    echo json_encode(["erro" => "Dados obrigatórios não informados"]);
    exit;
}

$sql = "INSERT INTO usuarios (nome, email, senha, perfil, ativo)
        VALUES (:nome, :email, :senha, :perfil, :ativo)";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ":nome"   => $data['nome'],
    ":email"  => $data['email'],
    ":senha"  => password_hash($data['senha'], PASSWORD_BCRYPT),
    ":perfil" => $data['perfil'] ?? 'ADMIN',
    ":ativo"  => 1
]);

echo json_encode(["success" => true]);