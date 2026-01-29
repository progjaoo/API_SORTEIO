<?php

$host = "sorteiosbh.mysql.dbaas.com.br";
$port = "3306";
$db   = "sorteiosbh";
$user = "sorteiosbh";
$pass = "Maravilha89#";

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Erro na conexão com o banco",
        "error" => $e->getMessage()
    ]);
    exit;
}
