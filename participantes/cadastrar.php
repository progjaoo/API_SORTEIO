<?php
require_once "../config/conexao.php";
require_once "../config/cors.php";

/*
|--------------------------------------------------------------------------
| FUNÇÃO VALIDAR CPF
|--------------------------------------------------------------------------
*/
function validarCPF($cpf) {
    $cpf = preg_replace('/\D/', '', $cpf);

    if (strlen($cpf) != 11) return false;
    if (preg_match('/^(\d)\1{10}$/', $cpf)) return false;

    for ($t = 9; $t < 11; $t++) {
        for ($d = 0, $c = 0; $c < $t; $c++) {
            $d += $cpf[$c] * (($t + 1) - $c);
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$c] != $d) return false;
    }

    return true;
}

$data = json_decode(file_get_contents("php://input"), true);


$data['cpf'] = preg_replace('/\D/', '', $data['cpf'] ?? '');

/*
|--------------------------------------------------------------------------
| VALIDAÇÃO DE CAMPOS
|--------------------------------------------------------------------------
*/

$campos = [
    'nome_completo',
    'email',
    'telefone',
    'cpf',
    'cep',
    'logradouro',
    'numero',
    'bairro',
    'cidade',
    'estado'
];

foreach ($campos as $campo) {
    if (!isset($data[$campo]) || trim($data[$campo]) === '') {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "error" => "Dados obrigatórios não informados"
        ]);
        exit;
    }
}

/*
|--------------------------------------------------------------------------
| VALIDAÇÃO DE CPF
|--------------------------------------------------------------------------
*/
if (!validarCPF($data['cpf'])) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error" => "CPF inválido"
    ]);
    exit;
}

$data['cpf'] = preg_replace('/\D/', '', $data['cpf']);

$data['cep'] = preg_replace('/\D/', '', $data['cep'] ?? '');

if (strlen($data['cep']) !== 8) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error" => "CEP inválido"
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| VERIFICA SE JÁ EXISTE (EMAIL)
|--------------------------------------------------------------------------
*/
$check = $pdo->prepare(
    "SELECT id FROM participantes WHERE email = :email"
);
$check->execute([
    ":email" => $data['email']
]);

if ($check->rowCount() > 0) {
    $existente = $check->fetch(PDO::FETCH_ASSOC);
    echo json_encode([
        "success" => true,
        "id" => (int)$existente['id'],
        "ja_existente" => true
    ]);
    exit;
}
/*
|--------------------------------------------------------------------------
| VERIFICA SE JÁ EXISTE CPF
|--------------------------------------------------------------------------
*/
$checkCpf = $pdo->prepare(
    "SELECT id FROM participantes WHERE cpf = :cpf"
);
$checkCpf->execute([
    ":cpf" => $data['cpf']
]);

if ($checkCpf->rowCount() > 0) {
    http_response_code(409);
    echo json_encode([
        "success" => false,
        "error" => "CPF já cadastrado"
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| INSERT
|--------------------------------------------------------------------------
*/

$sql = "
INSERT INTO participantes
(
    nome_completo,
    email,
    telefone,
    cpf,
    cep,
    logradouro,
    numero,
    bairro,
    cidade,
    estado
)
VALUES
(:nome_completo, :email, :telefone, :cpf, :cep, :logradouro, :numero, :bairro, :cidade, :estado)
";

$stmt = $pdo->prepare($sql);

try {
    $stmt->execute([
        ":nome_completo" => $data['nome_completo'],
        ":email"        => $data['email'],
        ":telefone"     => $data['telefone'],
        ":cpf"          => $data['cpf'],
        ":cep"          => $data['cep'],
        ":logradouro"   => $data['logradouro'],
        ":numero"       => $data['numero'],
        ":bairro"       => $data['bairro'],
        ":cidade"       => $data['cidade'],
        ":estado"       => strtoupper($data['estado'])
    ]);

    echo json_encode([
        "success" => true,
        "id" => (int)$pdo->lastInsertId()
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Erro ao cadastrar participante"
    ]);
}
