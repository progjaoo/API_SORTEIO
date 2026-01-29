<?php
require_once "../config/cors.php";
require_once "../config/conexao.php";

header("Content-Type: application/json; charset=UTF-8");

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

if (!$data || !isset($data['email'], $data['senha'])) {
    http_response_code(400);
    echo json_encode(["erro" => "Dados inválidos"]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT id, nome, email, senha, perfil, ativo
        FROM usuarios
        WHERE email = :email AND ativo = 1
    ");

    $stmt->execute([
        ":email" => $data['email']
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($data['senha'], $user['senha'])) {
        http_response_code(401);
        echo json_encode(["erro" => "Login inválido"]);
        exit;
    }

    unset($user['senha']);
    echo json_encode($user);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "erro" => "Erro interno no servidor",
        "detalhe" => $e->getMessage()
    ]);
}
