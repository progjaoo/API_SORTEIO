<?php
require_once "../config/conexao.php";
require_once "../config/cors.php";

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;

$factory = (new Factory)
    ->withServiceAccount(__DIR__ . '/firebase-service-account.json');

$messaging = $factory->createMessaging();

$data = json_decode(file_get_contents("php://input"), true);

$mensagem  = $data['mensagem'] ?? '🎉 Novo sorteio!';
$sorteioId = $data['sorteio_id'] ?? null;

$stmt = $pdo->query("
  SELECT fcm_token 
  FROM dispositivos_push
  WHERE ativo = 1
");

$tokens = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (!$tokens) {
  echo json_encode(['success' => false, 'erro' => 'Nenhum token']);
  exit;
}

foreach ($tokens as $token) {
  $message = CloudMessage::fromArray([
    'token' => $token,
    'notification' => [
      'title' => '📢 Rádio 89 Maravilha',
      'body'  => $mensagem,
    ],
    'data' => [
      'tipo' => 'NOVO_SORTEIO',
      'sorteio_id' => (string)$sorteioId
    ],
  ]);

  $messaging->send($message);
}

echo json_encode(['success' => true]);
