<?php

include_once "../../db/db_config_mysql.php";

if ($conectar->connect_error) {
    die("Erro ao conectar ao banco de dados: " . $conectar->connect_error);
}

$code = isset($_POST['code']) ? $conectar->real_escape_string($_POST['code']) : null;

if (empty($code)) {
    echo json_encode(['status' => 'error', 'message' => 'Código não fornecido.']);
    exit;
}

$sql = "DELETE FROM error_codes WHERE code = '$code'";

if ($conectar->query($sql) === TRUE) {
    echo json_encode(['status' => 'success', 'message' => 'Item removido com sucesso.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Erro ao remover o item: ' . $conectar->error]);
}

$conectar->close();
?>

