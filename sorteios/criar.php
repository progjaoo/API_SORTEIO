<?php
session_start();

require_once "../config/conexao.php";
require_once "../push/sendPush.php";
require_once "../config/cors.php";

if (!isset($_SESSION['usuario']['id'])) {
    http_response_code(401);
    echo json_encode(["erro" => "Usuário não autenticado"]);
    exit;
}

$usuarioId = $_SESSION['usuario']['id'];

$nome = $_POST['nome_sorteio'] ?? null;
$descricao  = $_POST['descricao'] ?? null;
$dataSorteio= $_POST['data_sorteio'] ?? null;
$dataFinal  = $_POST['data_final_cadastro'] ?? null;

if (!$nome || !$dataSorteio || !$dataFinal) {
    http_response_code(400);
    echo json_encode(["erro" => "Dados obrigatórios não enviados"]);
    exit;
}
/*
|--------------------------------------------------------------------------
| CONVERTE IMAGEM PARA BASE64
|--------------------------------------------------------------------------
*/
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

/*
|--------------------------------------------------------------------------
| INSERT
|--------------------------------------------------------------------------
*/
$stmt = $pdo->prepare("
INSERT INTO sorteios
(nome_sorteio, descricao, data_sorteio, data_final_cadastro, estado, criado_por, imagem)
VALUES
(:nome, :descricao, :data_sorteio, :data_final, 'ABERTO', :usuario, :imagem)
");

$stmt->execute([
    ":nome" => $nome,
    ":descricao" => $descricao,
    ":data_sorteio" => $dataSorteio,
    ":data_final" => $dataFinal,
    ":usuario" => $usuarioId,
    ":imagem" => $imagemBase64
]);

$sorteioId = $pdo->lastInsertId();

$mensagem = "Novo sorteio na Rádio 89 Maravilha - Inscreva-se Agora";

enviarPushNovoSorteio(
    $pdo,
    $mensagem,
    $sorteioId
);

file_get_contents(
  "https://grupogtf.com.br/89fm/adminsorteio/push/enviar.php",
  false,
  stream_context_create([
    'http' => [
      'method'  => 'POST',
      'header'  => "Content-Type: application/json\r\n",
      'content' => json_encode([
        'mensagem' => $mensagem,
        'sorteio_id' => $sorteioId 
      ])
    ]
  ])
);

echo json_encode([
    "mensagem" => "Sorteio criado com sucesso"
]);
