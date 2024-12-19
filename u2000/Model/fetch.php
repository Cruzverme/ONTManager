<?php

include_once "../../db/db_config_mysql.php";

if ($conectar->connect_error) {
    die("Erro ao conectar ao banco de dados: " . $conectar->connect_error);
}

$sql = "SELECT code, description, occurrences_number, updated_at FROM error_codes";
$result = $conectar->query($sql);

$data = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

echo json_encode(['status' => 'success', 'data' => $data]);

$conectar->close();