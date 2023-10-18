<?php
include_once "../db/db_config_mysql.php";
//iniciando sessao para enviar as msgs
session_start();

$clearChannelName = filter_input(INPUT_POST, 'cc_name');
$vasProfile = filter_input(INPUT_POST, 'VAS_PROFILE');
$lineProfile = filter_input(INPUT_POST, 'LINE_PROFILE');
$servProfile = filter_input(INPUT_POST,'SERVICE_PROFILE');
$packet = (int) filter_input(INPUT_POST, 'packet');
$gemports = filter_input_array(INPUT_POST,['gem_port' => ['filter' => FILTER_DEFAULT, 'flags' => FILTER_REQUIRE_ARRAY]]);
$vlans = filter_input_array(INPUT_POST,['vlan_id' => ['filter' => FILTER_DEFAULT, 'flags' => FILTER_REQUIRE_ARRAY]]);
$usuario = filter_input(INPUT_SESSION,'id_ususario');
$typeRequisition = filter_input(INPUT_POST, 'type_requisition');
$vlanToEdit = filter_input(INPUT_POST, 'edit_vlan');

$vlansGem = array_combine($vlans['vlan_id'], $gemports['gem_port']);
$gem = json_encode($vlansGem);
//var_dump($vlansGem, $gem); exit;

if (!mysqli_connect_errno()) {
    $sqlLanLan = "INSERT INTO lan_lan (
                     name,
                     vas_profile,
                     line_profile,
                     service_profile,
                     gem_ports,
                     planos_id 
                    ) VALUES (?, ?, ?, ?, ?, ?)";

    if ($typeRequisition === 'edit') {
        $sqlLanLan = "UPDATE lan_lan SET 
                     name = ?,
                     vas_profile = ?,
                     line_profile = ?,
                     service_profile = ?,
                     gem_ports = ?,
                     planos_id = ?
                     WHERE id = $vlanToEdit";
    }

    $stmt = mysqli_prepare($conectar, $sqlLanLan);
    $stmt->bind_param('sssssi',
        $clearChannelName,
        $vasProfile,
        $lineProfile,
        $servProfile,
        $gem,
        $packet
    );

    $result = $stmt->execute();

    if ($result) {
        $sql_insert_log = "INSERT INTO log (registro,codigo_usuario) VALUES (?, ?)";
        $stmt_log = mysqli_prepare($conectar, $sql_insert_log);
        $logMessage = "Lan to Lan Criado pelo Usuario de Codigo $usuario";
        $stmt_log->bind_param("si", $logMessage, $usuario);
        $stmt_log->execute();

        $_SESSION['menssagem'] = "Lan to Lan Registrado!";
        header('Location: ../clear_channel/channel_config.php');
        mysqli_close($conectar);
        exit;
    }
}

$_SESSION['menssagem'] = "Não Consegui Contato com Servidor!";
header('Location: ../clear_channel/channel_config.php');
mysqli_close($conectar);
exit;

