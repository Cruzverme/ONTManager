<?php

include_once "../../db/db_config_mysql.php";

if ($conectar->connect_error) {
    die("Erro ao conectar ao banco de dados: " . $conectar->connect_error);
}

$code = isset($_POST['code']) ? $conectar->real_escape_string($_POST['code']) : null;
$description = isset($_POST['description']) ? $conectar->real_escape_string($_POST['description']) : null;

if (empty($code) || empty($description)) {
    echo json_encode(['status' => 'error', 'message' => 'Todos os campos são obrigatórios.']);
    exit;
}

$checkSql = "SELECT id FROM error_codes WHERE code = '$code'";
$result = $conectar->query($checkSql);

if ($result->num_rows > 0) {
    // Código já existe, retorna erro
    echo json_encode(['status' => 'error', 'message' => 'O código já existe no banco de dados.']);
    exit;
}

$sql = "INSERT IGNORE INTO error_codes(code, description) VALUES ('$code', '$description')";

if ($conectar->query($sql) === TRUE) {
    $lastId = $conectar->insert_id;
    echo json_encode([
        'status' => 'success',
        'message' => 'Cadastro realizado com sucesso.',
        'data' => [
            'code' => $code,
            'description' => $description,
            'occurrences_number' => 0,
            'updated_at' => date('Y-m-d H:i:s')
        ]
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Erro ao cadastrar: ' . $conectar->error
    ]);
}

$conectar->close();
?>