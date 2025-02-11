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
        $newRow = [
            'code' => mb_convert_encoding($row['code'], 'UTF-8', 'UTF-8'),
            'description' => mb_convert_encoding($row['description'], 'UTF-8', 'UTF-8'),
            'occurrences_number' => mb_convert_encoding($row['occurrences_number'], 'UTF-8', 'UTF-8'),
            'updated_at' => mb_convert_encoding($row['updated_at'], 'UTF-8', 'UTF-8'),
        ];
        $data[] = $newRow;
    }
}

echo json_encode(['status' => 'success', 'data' => $data]);

$conectar->close();