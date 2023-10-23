<?php

    include_once "../../db/db_config_mysql.php";
    require_once '../../classes/Packages.php';
    //iniciando sessao para enviar as msgs
    session_start();

    $packets = new Packages();
    $packs = $packets->getVelocityPack($conectar);

    $packetList = [];

    foreach ($packs as $pack) {
        $packetList[] = [
            'name' => mb_convert_encoding($pack['nome'], 'UTF-8', 'UTF-8'),
            'id' => mb_convert_encoding($pack['plano_id'], 'UTF-8', 'UTF-8')
        ];
    }
    $jsonData = json_encode($packetList);

    if (!$jsonData) {
        echo 'ERROR ' . json_last_error_msg();
    } else {
        echo $jsonData;
    }