<?php
include_once "../../db/db_config_mysql.php";
include "../../classes/verifica_sessao.php";

if ($_SESSION["block_customer_changes"] == 0) {
    echo '
        <script language= "JavaScript">
            alert("Sem Permissão de Acesso!");
            location.href="../classes/redirecionador_pagina.php";
        </script>
    ';
}

$_POST = json_decode(file_get_contents('php://input'), true);
$contract = $_POST['contract'] ?? null;
$contractToRemove = $_GET['contract'] ?? null;

// Verifique se o contrato está presente no array de dados
if ($contract) {
    addContract($contract, $conectar);
}

if ($contractToRemove) {
    unblockContract($contractToRemove, $conectar);
}

$conectar->close();


function addContract($contract, $conectar)
{
    // Prepare e execute a consulta SQL para inserir o contrato na tabela
    $contract = $conectar->real_escape_string($contract);
    $sql = "INSERT INTO customer_change_blocked (contract) VALUES ('$contract')";

    if ($conectar->query($sql) === TRUE) {
        echo json_encode(['success' => true, 'message' => 'Contrato ' . $contract . ' inserido com sucesso na tabela.']);
        return;
    }
    echo json_encode(['success' => false, 'message' => 'Erro ao inserir contrato na tabela: ' . $conectar->error]);
}

function unblockContract($contract, $conectar)
{
    // Prepare e execute a consulta SQL para inserir o contrato na tabela
    $contract = $conectar->real_escape_string($contract);
    $sql = "DELETE FROM customer_change_blocked WHERE contract = $contract";

    if ($conectar->query($sql) === TRUE) {
        echo json_encode(['success' => true, 'message' => 'Contrato ' . $contract . ' desbloqueado.']);
        return;
    }
    echo json_encode(['success' => false, 'message' => 'Erro ao desbloquear contrato: ' . $conectar->error]);
}

