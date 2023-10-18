<?php

    include_once "../../db/db_config_mysql.php";
    require_once '../../classes/Packages.php';
    //iniciando sessao para enviar as msgs
    session_start();

    //    echo json_encode(['name'=> 'nomin', 'id' => 123]);
//    echo getL2lList();
    $packets = new Packages();
    $packs = $packets->getVelocityPack($conectar);

    $packetList = [];

    foreach ($packs as $pack) {
        $packetList[] = [
            'name' => $pack['nome'],
            'id' => $pack['plano_id']
        ];
    }

    echo json_encode($packetList);