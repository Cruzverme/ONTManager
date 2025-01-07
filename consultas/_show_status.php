<?php

include_once "../db/db_config_mysql.php";
include_once "../u2000/tl1_sender.php";
include_once "../classes/funcoes.php";
include_once "../classes/Packages.php";

// Funções auxiliares
function fetchContratoByMac($mac, $conectar) {
    $query = "SELECT contrato FROM ont WHERE serial = ?";
    $stmt = mysqli_prepare($conectar, $query);
    mysqli_stmt_bind_param($stmt, 's', $mac);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $contrato);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
    return $contrato;
}

function fetchSerialByContrato($contrato, $conectar) {
    $query = "SELECT serial FROM ont WHERE contrato = ?";
    $stmt = mysqli_prepare($conectar, $query);
    mysqli_stmt_bind_param($stmt, 's', $contrato);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $serial = mysqli_fetch_assoc($result)['serial'] ?? null;
    mysqli_stmt_close($stmt);
    return $serial;
}

function fetchOntInfo($serial, $contrato, $conectar) {
    $query = "SELECT onu.ontID, onu.cto, onu.porta, onu.perfil, ct.frame_slot_pon, p.deviceName,
              onu.service_port_l2l, onu.service_port_internet, onu.service_port_iptv, onu.service_port_telefone
              FROM ont onu 
              INNER JOIN ctos ct ON ct.serial = ? AND ct.caixa_atendimento = onu.cto 
              INNER JOIN pon p ON p.pon_id = ct.pon_id_fk 
              WHERE onu.serial = ? AND onu.contrato = ?";
    $stmt = mysqli_prepare($conectar, $query);
    mysqli_stmt_bind_param($stmt, 'ssi', $serial, $serial, $contrato);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $info = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $info;
}

function processOntInformation($info) {
    list($frame, $slot, $pon) = explode('-', $info['frame_slot_pon']);
    $vasProfile = $info['perfil'] ?? null;

    $ontInformation = initializeOntInformation($info, $frame, $slot, $pon);

    // Obter informações do U2000
    $ontInformation['informations']['ont'] = getOntStatus($ontInformation, $frame, $slot, $pon);
    $ontInformation['informations']['signal'] = getSignalStatus($ontInformation, $frame, $slot, $pon);
    $ontInformation['informations']['wan'] = getWanStatus($ontInformation, $frame, $slot, $pon);
    $ontInformation['informations']['service_port'] = getServicePortStatus($ontInformation, $frame, $slot, $pon);

    if (shouldCheckSip($vasProfile)) {
        $ontInformation['informations']['signal'] = array_merge(
            $ontInformation['informations']['signal'],
            getSipStatus($ontInformation, $frame, $slot, $pon)
        );
    }

    $ontInformation['informations']['eth_port'] = getEthPortStatus($ontInformation, $frame, $slot, $pon);

    return $ontInformation;
}

function initializeOntInformation($info, $frame, $slot, $pon) {
    return [
        'ontId' => $info['ontID'] ?? null,
        'frame' => $frame,
        'slot' => $slot,
        'pon' => $pon,
        'device' => $info['deviceName'] ?? null,
        'cto' => $info['cto'] ?? null,
        'porta_atendimento' => $info['porta'] ?? null,
        'svr_l2l' => $info['service_port_l2l'] ?? null,
        'svr_internet' => $info['service_port_internet'] ?? null,
        'svr_iptv' => $info['service_port_iptv'] ?? null,
        'svr_telefone' => $info['service_port_telefone'] ?? null,
        'informations' => [
            'ont' => [],
            'signal' => [],
            'wan' => [],
            'service_port' => [],
            'eth_port' => array_fill(0, 4, false), // Assume 4 portas ETH
        ]
    ];
}

function getOntStatus($ontInformation, $frame, $slot, $pon) {
    $status = get_status_ont($ontInformation['device'], $frame, $slot, $pon, $ontInformation['ontId']);
    $infoExtracted = extractErrorCode($status);

    if (trim($infoExtracted[0]) == '0') {
        return extractOntInfo($infoExtracted[1]);
    }

    return [];
}

function extractOntInfo($data) {
    $parts = explode("-------------------------------------------------------------------------------------------------------------------", $data);
    $results = explode(PHP_EOL, $parts[1]);
    $values = preg_split('/\s+/', $results[2]);

    return [
        'slot' => $values[2] ?? null,
        'pon' => $values[3] ?? null,
        'status' => $values[7] == 'Up' ? "ONLINE" : "OFFLINE",
        'last_timeout' => $values[12] != '--' ? "{$values[12]} - {$values[13]}" : 'SEM REGISTRO',
        'last_timeout_reason' => $values[7] == 'Up' ? '---' : getAcronymMeaning(strtoupper($values[14]))
    ];
}

function getSignalStatus($ontInformation, $frame, $slot, $pon) {
    $status = get_signal_ont($ontInformation['device'], $frame, $slot, $pon, $ontInformation['ontId']);
    $infoExtracted = extractErrorCode($status);

    if (trim($infoExtracted[0]) == '0') {
        return extractSignalInfo($infoExtracted[1]);
    }

    return [];
}

function extractSignalInfo($data) {
    $parts = explode("-----------------------------------------------------------------------------------------", $data);
    $results = explode(PHP_EOL, $parts[1]);
    $values = preg_split('/\s+/', $results[2]);

    return [
        'rx' => $values[7],
        'tx' => $values[8],
        'rx_olt' => $values[13],
        'status_sip' => 'NAO HA TELEFONE',
        'service_status_sip' => 'NAO HA TELEFONE',
    ];
}

function getWanStatus($ontInformation, $frame, $slot, $pon) {
    $status = verificar_wan($ontInformation['device'], $frame, $slot, $pon, $ontInformation['ontId']);
    $infoExtracted = extractErrorCode($status);

    if (trim($infoExtracted[0]) == '0') {
        return extractWanInfo($infoExtracted[1]);
    }

    return [];
}

function extractWanInfo($data) {
    $parts = explode("----------------------------------------------------------------------------------------------", $data);
    $results = explode(PHP_EOL, $parts[1]);
    $wanInfo = [];

    for ($i = 2; $i < count($results) - 1; $i++) {
        $values = preg_split('/\s+/', $results[$i]);
        $wanInfo[] = [
            'wan_type' => $values[5],
            'ipv4' => $values[6],
            'wan_mask' => $values[7],
            'wan_gateway' => $values[9],
            'conection_type' => $values[10] == '--' ? 'BRIDGE' : 'ROUTER',
        ];
    }

    return $wanInfo;
}

function getServicePortStatus($ontInformation, $frame, $slot, $pon) {
    $status = verificar_service_port($ontInformation['device'], $frame, $slot, $pon, $ontInformation['ontId']);
    $infoExtracted = extractErrorCode($status);

    if (trim($infoExtracted[0]) == '0') {
        return extractServicePortInfo($infoExtracted[1]);
    }

    return [];
}

function extractServicePortInfo($data) {
    $parts = explode("-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------", $data);
    $results = explode(PHP_EOL, $parts[1]);
    $servicePorts = [];

    for ($i = 2; $i < count($results) - 1; $i++) {
        $values = preg_split('/\s+/', $results[$i]);
        $servicePorts[] = $values[10]; // Altere conforme necessário
    }

    return $servicePorts;
}

function getSipStatus($ontInformation, $frame, $slot, $pon) {
    $status = get_status_sip($ontInformation['device'], $frame, $slot, $pon, $ontInformation['ontId']);
    $infoExtracted = extractErrorCode($status);

    if (trim($infoExtracted[0]) == '0') {
        return extractSipInfo($infoExtracted[1]);
    }

    return [
        'status_sip' => null,
        'service_status_sip' => null,
    ];
}

function extractSipInfo($data) {
    $parts = explode("-----------------------------------------------------------------------------------------", $data);
    $results = explode(PHP_EOL, $parts[1]);
    $values = preg_split('/\s+/', $results[2]);

    return [
        'status_sip' => processSipStatus($values[9]),
        'service_status_sip' => processServiceSipStatus($values[10]),
    ];
}

function getEthPortStatus($ontInformation, $frame, $slot, $pon) {
    $ethPorts = [];
    $portNumber = 1;

    foreach ($ontInformation['informations']['eth_port'] as $value) {
        $status = verificar_portas_ont($ontInformation['device'], $frame, $slot, $pon, $ontInformation['ontId'], $portNumber);
        $infoExtracted = extractErrorCode($status);

        if (trim($infoExtracted[0]) == '0') {
            $ethPortStatus = extractEthPortInfo($infoExtracted[1]);
            $ethPorts[$portNumber - 1] = $ethPortStatus['status'] ?? 'NÃO CONECTADA';
            $portNumber++;
        }
    }

    return $ethPorts;
}

function extractEthPortInfo($data) {
    $parts = explode("-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------", $data);
    $results = explode(PHP_EOL, $parts[1]);
    $values = preg_split('/\s+/', $results[2]);

    return [
        'status' => $values[8] == "Active" ? 'CONECTADA' : 'NÃO CONECTADA',
    ];
}

//Fim Process Ont Information Block

function extractErrorCode($status) {
    $parts = explode(";", $status);
    $successPart = explode("EN=", $parts[1] ?? "");
    $descriptionPart = explode("ENDESC=", $successPart[1] ?? "");
    return $descriptionPart ?? "";
}

function shouldCheckSip($vasProfile) {
    $telefonePlans = Packages::getVasProfileWithVoip();
    return in_array($vasProfile, $telefonePlans);
}

function processSipStatus($sipStatus) {
    switch($sipStatus)
    {
        case 'REGISTERING':
            return "TENTANDO REGISTRAR";
        case 'IDLE':
            return "REGISTRADO E AGUARDANDO";
        case 'DIALING':
            return "TELEFONE FORA DO GANCHO";
        case 'RINGING':
            return "TELEFONE TOCANDO";
        case'DEACTIVED':
            return "DESATIVADO";
        case 'CONNECTED':
            return "CONECTADO";
        case'FAILED-REGISTRATTION':
            return "A AUTENTICAÇÃO FALHOU";
        default:
            return "$sipStatus";
    }
}

function processServiceSipStatus($servSipStatus) {
    //pegara status do serviço
    switch($servSipStatus)
    {
        case 'REMOTE-BLOCKED':
            return "BLOQUEADO REMOTAMENTE";
        case 'NORMAL':
            return"FUNCIONANDO NORMAL";
        case 'REMOTE-FAULT':
            return"OCORREU UM ERRO NA AUTENTICAÇÃO.";
        default:
            return"$servSipStatus";
    }
}


$contrato = filter_input(INPUT_POST, 'contrato');
$mac = filter_input(INPUT_POST, 'mac');

if ($mac && empty($contrato)) {
    $contrato = fetchContratoByMac($mac, $conectar);
}

if ($contrato) {
    if (checar_contrato($contrato) === null) {
        echo "<p class='error_message'>Contrato Inexistente ou Cancelado!</p>";
        mysqli_close($conectar);
        exit;
    }

    $serial = fetchSerialByContrato($contrato, $conectar);
    if ($serial) {
        $info = fetchOntInfo($serial, $contrato, $conectar);
        if ($info) {
            $informationProcessed = processOntInformation($info);
            include "_ont_informations_table.php";
        } else {
            echo "<p class='error_message'>Ocorreu um erro ao buscar informações no banco de dados para o contrato $contrato!</p>";
        }
    } else {
        echo "<p class='error_message'>Nenhum equipamento cadastrado para o contrato $contrato !</p>";
    }
} else {
    echo "<p class='error_message'>Não foi encontrado contrato vinculado ao MAC!</p>";
}

mysqli_close($conectar);