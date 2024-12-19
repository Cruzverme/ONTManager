<?php

include_once "../../db/db_config_mysql.php";

if ($conectar->connect_error) {
    die("Erro ao conectar ao banco de dados: " . $conectar->connect_error);
}

$code = isset($_POST['code']) ? $conectar->real_escape_string($_POST['code']) : null;
$description = isset($_POST['description']) ? $conectar->real_escape_string($_POST['description']) : null;

if (empty($code) || empty($description)) {
    echo json_encode(['status' => 'error', 'message' => 'Código ou descrição não fornecidos.']);
exit;
}

$sql = "UPDATE error_codes SET description = '$description', updated_at = NOW() WHERE code = '$code'";

if ($conectar->query($sql) === TRUE) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Descrição atualizada com sucesso.',
        'updated_at' => date('Y-m-d H:i:s') // Retorna a data/hora atualizada
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Erro ao atualizar: ' . $conectar->error
    ]);
}

$conectar->close();
