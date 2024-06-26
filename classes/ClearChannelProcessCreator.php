<?php

    include_once "../db/db_config_mysql.php";
    include_once "../db/db_config_radius.php";
    include_once "../u2000/tl1_sender.php";
    // Inicia sessões
    session_start();

    $nome = filter_input(INPUT_POST, 'nome');
    $vasProfile = filter_input(INPUT_POST, 'vasProfile');
    $lineProfile = filter_input(INPUT_POST, 'line');
    $serviceProfile = filter_input(INPUT_POST, 'serv');
    $serial_number = filter_input(INPUT_POST, 'serial');
    $pacote_internet = filter_input(INPUT_POST, 'pacote_internet');
    $modelo_ont = filter_input(INPUT_POST, 'modelo_ont');
    $sip_number = filter_input(INPUT_POST, 'sip_number');
    $sip_password = filter_input(INPUT_POST, 'sip_password');
    $usuario = $_SESSION["id_usuario"];

    $porta_selecionado = filter_input(INPUT_POST, 'porta_atendimento');
    $frame = filter_input(INPUT_POST, 'frame');
    $slot = filter_input(INPUT_POST, 'slot');
    $pon = filter_input(INPUT_POST, 'pon');
    $cto = filter_input(INPUT_POST, 'cto');
    $device = filter_input(INPUT_POST, 'device');
    $contrato = filter_input(INPUT_POST, 'contrato');

    $internet = filter_input(INPUT_POST, "internet_check");
    $lanToLan = filter_input(INPUT_POST, "vlan_check");
    $iptv = filter_input(INPUT_POST, "iptv");
    $voip = filter_input(INPUT_POST, "voip");
    $modo_bridge = filter_input(INPUT_POST, 'modo_bridge');
    $haveMoreServices = ($internet || $iptv || $voip) ?? false;

    $gemsPort = filter_input(INPUT_POST, 'gems');
    $gemPortList = array_flip(json_decode($gemsPort, true));
    ## ALIAS DO ASSINANTE PARA U2000

    $nomeAlias = str_replace(" ", "_", $nome);

    ## CODIGO TAVA AKI
    $array_process_result = [];

    // Função para sair com mensagens
    function exitWithMessage($messages, $connections, $isError = true)
    {
        foreach ($connections as $connection) {
            mysqli_close($connection);
        }

        $finalMessage = "Ocorreu um erro \r";
        $status = 400;
        if (!$isError) {
            $finalMessage = "SERVIÇOS ATIVADOS \r";
            $status = 200;
        }

        foreach ($messages as $message) {
            $finalMessage = $finalMessage . "$message \r";
        }

        echo json_encode(['message' => $finalMessage, 'status' => $status]);
    }

    function obterInformacoesOnt($ontID)
    {
        if (!$ontID) {
            return ['errorCode' => 'vtv99999'];
        }

        $tira_ponto_virgula = explode(";", $ontID);
        $check_sucesso = explode("EN=", $tira_ponto_virgula[1]);
        $remove_desc = explode("ENDESC=", $check_sucesso[1]);
        $errorCode = trim($remove_desc[0]);

        return ['errorCode' => $errorCode, 'ontResult' => $tira_ponto_virgula];
    }

    function createServicePortL2L($device, $frame, $slot, $pon, $onuID, $contrato, $gemPortList)
    {
        $servicePortIdList = [
            'error' => 0,
            'servicesPortsIds' => ''
        ];
        foreach ($gemPortList as $gem => $vlan) {
            if ($gem == 6) continue;

            $servicePortl2l = get_service_port_l2l($device, $frame, $slot, $pon, $onuID, $contrato, $vlan, $gem);
            $tira_ponto_virgula = explode(";", $servicePortl2l);
            $check_sucesso = explode("EN=", $tira_ponto_virgula[1]);
            $remove_desc = explode("ENDESC=", $check_sucesso[1]);
            $errorCode = trim($remove_desc[0]);
            if ($errorCode != 0) {
                $servicePortIdList['error'] = $errorCode;
                return $servicePortIdList;
            }

            $remove_barras_para_pegar_id = explode("--------------", $tira_ponto_virgula[1]);
            $pegar_servicePorta_ID = explode("\r\n", $remove_barras_para_pegar_id[1]);
            $pega_id = explode("\t", $pegar_servicePorta_ID[2]);//servicePort sempre será -1
            $servicePortIdList['servicesPortsIds'] = $servicePortIdList['servicesPortsIds'] . ' ' . ($pega_id[0] - 1);
            sleep(2);
        }
        return $servicePortIdList;
    }

    function createUniqueServicePortL2L($device, $frame, $slot, $pon, $onuID, $contrato, $gemVlan, $gem)
    {
        $servicePortIdList = [
            'error' => 0,
            'servicesPortsIds' => ''
        ];

        $servicePortl2l = get_service_port_l2l($device, $frame, $slot, $pon, $onuID, $contrato, $gemVlan, $gem);
        $tira_ponto_virgula = explode(";", $servicePortl2l);
        $check_sucesso = explode("EN=", $tira_ponto_virgula[1]);
        $remove_desc = explode("ENDESC=", $check_sucesso[1]);
        $errorCode = trim($remove_desc[0]);
        if ($errorCode != 0) {
            $servicePortIdList['error'] = $errorCode;
            return $servicePortIdList;
        }

        $remove_barras_para_pegar_id = explode("--------------", $tira_ponto_virgula[1]);
        $pegar_servicePorta_ID = explode("\r\n", $remove_barras_para_pegar_id[1]);
        $pega_id = explode("\t", $pegar_servicePorta_ID[2]);//servicePort sempre será -1
        $servicePortIdList['servicesPortsIds'] = $servicePortIdList['servicesPortsIds'] . ', ' . ($pega_id[0] - 1);
        return $servicePortIdList;
    }

    $connectionsList = [$conectar, $conectar_radius];

    // CHECA O LIMITE DE ONT NO CLIENTE
    $sql_verifica_limite = "SELECT limite_equipamentos FROM ont WHERE contrato = ?";
    $stmt = mysqli_prepare($conectar, $sql_verifica_limite);
    mysqli_stmt_bind_param($stmt, "s", $contrato);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $limite_registro);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    if ($limite_registro < 1 && $limite_registro !== null) {
        $errorMessage[] = "Favor, entrar em contato com o TI, para solicitar aumento de registro de equipamentos";

        return exitWithMessage($errorMessage, $connectionsList);
    }

    // VERIFICA O MAC SE JA FOI CADASTRADO
    $sql_verifica_limite_ont = "SELECT serial, contrato FROM ont WHERE serial = ? LIMIT 1";
    $stmt = mysqli_prepare($conectar, $sql_verifica_limite_ont);
    mysqli_stmt_bind_param($stmt, "s", $serial_number);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        mysqli_stmt_bind_result($stmt, $serial, $contrato);
        mysqli_stmt_fetch($stmt);

        $errorMessage[] = "MAC Já Cadastrado no contrato $contrato";
        return exitWithMessage($errorMessage, $connectionsList);
    }

    ########## VERIFICA SE O CHECKBOX DOS SERVIÇOS FOI MARCADO  ################
    if (($internet != "Internet" && $lanToLan != "l2l" && $iptv != "IPTV" && $voip != "Telefone")) {
        $errorMessage[] = "Nenhum Serviço Selecionado";
        return exitWithMessage($errorMessage, $connectionsList);

    }

    ################### CADASTRA A ONT NO BANCO LOCAL ######################
    $sql_registra_onu = "INSERT INTO ont (contrato, serial, cto, tel_number, tel_user,
                            tel_password, perfil, pacote, usuario_id, equipamento, porta)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conectar, $sql_registra_onu);
    mysqli_stmt_bind_param($stmt, "sssssssssss",
        $contrato, $serial_number, $cto,
        $sip_number, $sip_number, $sip_password,
        $vasProfile, $pacote_internet, $usuario,
        $modelo_ont, $porta_selecionado
    );
    $cadastrar = mysqli_stmt_execute($stmt);

    if (!$cadastrar) {
        $errorMessage[] = "Erro ao inserir o registro da ONT: " . mysqli_error($conectar);
        return exitWithMessage($errorMessage, $connectionsList);
    }

    $array_process_result[] = "Cadastrado no Banco Local";

############### DEFINE LIMITE DE ONTs PARA 0 ###############
    $sql_atualiza_limite = "UPDATE ont SET limite_equipamentos = 0 WHERE contrato = ?";
    $stmt = mysqli_prepare($conectar, $sql_atualiza_limite);
    mysqli_stmt_bind_param($stmt, "s", $contrato);
    $diminui_limite = mysqli_stmt_execute($stmt);

############ CADASTRA A ONT NO U2000 ############
    $ontID = cadastrar_ont_l2l(
        $device, $frame, $slot, $pon,
        $contrato, $nomeAlias,
        $serial_number, $modelo_ont,
        $vasProfile, $lineProfile, $serviceProfile
    );

    // Aguarde por 1 segundo para dar tempo de processar
    sleep(1);

    $ontInfo = obterInformacoesOnt($ontID);
    $errorCode = $ontInfo['errorCode'];
    if ($errorCode !== "0") {
        $trato = tratar_errors($errorCode);
        $array_process_result[] = "!!!! Houve erro ao inserir a ONT no u2000: $trato !!!!";

        //se der erro ele irá apagar o registro salvo na tabela local ont
        $sql_apagar_onu = ("DELETE FROM ont WHERE contrato = '$contrato' AND serial = '$serial_number'");
        mysqli_query($conectar, $sql_apagar_onu);
        deletar_onu_2000($device,$frame,$slot,$pon,$ontID,null,NULL);

        $sql_insert_log = "INSERT INTO log (registro,codigo_usuario, mac, cto, contrato)
                VALUES ('ERRO NO U2000 AO GERAR ONTID $trato Número Sem Tratamento: $errorCode e U2000: $ontID 
                informações relatadas: OLT: $device, PON: $pon, Frame: $frame,
                Porta de Atendimento: $porta_selecionado, Slot: $slot, CTO: $cto Contrato: $contrato,
                MAC: $serial_number, Perfil: $vasProfile, Internet: $pacote_internet, Telefone: $sip_number,
                Senha Telefone: $sip_password','$usuario', '$serial_number', '$cto', '$contrato')";
        $executa_log = mysqli_query($conectar,$sql_insert_log);

        $array_process_result[] = "Removido do Banco Local!";
        return exitWithMessage($array_process_result, $connectionsList);
    }

    $array_process_result[] = "ONT Adicionada ao U2000!";
########## PEGANDO ID DA ONT PARA SALVAR ############
    $remove_barras_para_pegar_id = explode("---------------------------",$ontInfo['ontResult'][1]);
    $filtra_espaco = explode("\r\n",$remove_barras_para_pegar_id[1]);
    $pega_id = explode("\t",$filtra_espaco[2]);//posicao 4 será sempre o ONTID
    $onuID=trim($pega_id[4]);

// Atualiza o ID da ONT no banco de dados local
    $insere_ont_id = "UPDATE ont SET ontID='$onuID' WHERE serial = '$serial_number'";
    $executa_insere_ont_id = mysqli_query($conectar,$insere_ont_id);

    $array_process_result[] = "Inserido ID da ONT!";
    $sql_insert_log = "INSERT INTO log (registro,codigo_usuario, mac, cto, contrato)
                VALUES ('ONT Cadastrada
                informações relatadas: OntID: $onuID, OLT: $device, PON: $pon, Frame: $frame,
                Porta de Atendimento: $porta_selecionado, Slot: $slot, CTO: $cto Contrato: $contrato,
                MAC: $serial_number, Perfil: $vasProfile, Internet: $pacote_internet, Telefone: $sip_number,
                Senha Telefone: $sip_password','$usuario', '$serial_number', '$cto', '$contrato')";
    $executa_log = mysqli_query($conectar,$sql_insert_log);

#### SELECT OLT IP ####
    $sql_pega_olt_ip = "SELECT olt_ip FROM pon WHERE deviceName = ? LIMIT 1";
    $stmt = mysqli_prepare($conectar, $sql_pega_olt_ip);
    mysqli_stmt_bind_param($stmt, "s", $device);
    mysqli_stmt_execute($stmt);

    // Vincular o resultado
    mysqli_stmt_bind_result($stmt, $ip_olt);
    // Obter o resultado (deve haver apenas um)
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

###### ASSOCIANDO CTO A ONT ######
    $sql_insere_porta = "UPDATE ctos SET porta_atendimento_disponivel = 1, serial = '$serial_number'
        WHERE caixa_atendimento = '$cto' AND porta_atendimento= '$porta_selecionado'";
    $executa_insere_porta = mysqli_query($conectar,$sql_insere_porta);
    $array_process_result[] ="Reservada Porta $porta_selecionado da CTO $cto";

############ INICIO DA ATIVACAO DOS SERVIÇOS #######
    ##################################### L A N t o L A N ##############################################
    if ($lanToLan) {
        $array_process_result[] = "### LAN TO LAN ###";

        ######## ADICIONA A ONT NO RADIUS PARA PEGAR BANDA E IP ########
        $insere_ont_radius_username = "INSERT INTO radcheck( username, attribute, op, value)
              VALUES ( '$gemPortList[6]/$slot/$pon/$serial_number@vertv', 'User-Name', ':=', '$gemPortList[6]/$slot/$pon/$serial_number@vertv' )";

        $insere_ont_radius_password = "INSERT INTO radcheck( username, attribute, op, value)
              VALUES ( '$gemPortList[6]/$slot/$pon/$serial_number@vertv', 'User-Password', ':=', 'vlan' )";

        $executa_query_username= mysqli_query($conectar_radius,$insere_ont_radius_username);
        $executa_query_password= mysqli_query($conectar_radius,$insere_ont_radius_password);

        if (!$executa_query_username || !$executa_query_password) {
            $array_process_result[] = "Ocorreu um erro ao Inserir no Radius!";

            //se der erro ele irá apagar o registro salvo na tabela local ont
            $sql_apagar_onu = ("DELETE FROM ont WHERE contrato = '$contrato' AND serial = '$serial_number'" );
            mysqli_query($conectar,$sql_apagar_onu);
            $array_process_result[] = "Removido do Banco Local";

            //Desativa Porta CTO
            $sql_insere_porta = "UPDATE ctos SET porta_atendimento_disponivel = 0, serial = '$serial_number'
                WHERE caixa_atendimento = '$cto' AND porta_atendimento= '$porta_selecionado'";
            $executa_insere_porta = mysqli_query($conectar,$sql_insere_porta);
            $array_process_result[] = "Desassociada Porta $porta_selecionado da CTO $cto";

            $deletar_onu_radius = " DELETE FROM radcheck WHERE username='$gemPortList[6]/$slot/$pon/$serial_number@vertv'";
            $executa_query_radius = mysqli_query($conectar_radius,$deletar_onu_radius);
            $array_process_result[] = "Removido do Radius";

            deletar_onu_2000($device,$frame,$slot,$pon,$onuID,$ip_olt,NULL);
            $array_process_result[] = "Removido do do u2000";

            return exitWithMessage($array_process_result, $connectionsList);
        }

        $array_process_result[] = '1-ONT inserida no Radius IP Gerencia!';
        ###### CRIA SERVICE PORT VLAN ######
        if (!$haveMoreServices) {
            $servicePortL2LIds = createServicePortL2L($device, $frame, $slot, $pon, $onuID, $contrato, $gemPortList);
        } else {
            $servicePortL2LIds = createUniqueServicePortL2L($device, $frame, $slot, $pon, $onuID, $contrato, $gemPortList[9], 9);
        }

        if ($servicePortL2LIds['error'] != "0") {//se der erro na service port internet
            $trato = tratar_errors($servicePortL2LIds['error']);
            $errorCode = $servicePortL2LIds['error'];
            $array_process_result[] = "Erro ao criar o service porta de Lan to Lan: $trato";

            //se der erro ele irá apagar o registro salvo na tabela local ont
            $sql_apagar_onu = ("DELETE FROM ont WHERE contrato = '$contrato' AND serial = '$serial_number'");
            mysqli_query($conectar, $sql_apagar_onu);

            $array_process_result[] = "Removido do banco local";

                $deletar_onu_radius_banda = "DELETE FROM radcheck WHERE username='$gemPortList[6]/$slot/$pon/$serial_number@vertv'
                AND attribute='Huawei-Qos-Profile-Name' ";
                mysqli_query($conectar_radius, $deletar_onu_radius_banda);

            $array_process_result[] = "Removido do radius";

            deletar_onu_2000($device, $frame, $slot, $pon, $onuID, $ip_olt, NULL);
            $array_process_result[] = "Removido do u2000";

            $sql_insert_log = "INSERT INTO log (registro,codigo_usuario, mac, cto, contrato)
                VALUES ('ERRO NO U2000 AO GERAR SERVIÇO L2L $trato Número Sem Tratamento: $errorCode e U2000: $ontID 
                informações relatadas: OLT: $device, PON: $pon, Frame: $frame,
                Porta de Atendimento: $porta_selecionado, Slot: $slot, CTO: $cto Contrato: $contrato,
                MAC: $serial_number, Perfil: $vasProfile, Internet: $pacote_internet, Telefone: $sip_number,
                Senha Telefone: $sip_password','$usuario', '$serial_number', '$cto', '$contrato')";
            $executa_log = mysqli_query($conectar,$sql_insert_log);

            return exitWithMessage($array_process_result, $connectionsList);
        }
        $array_process_result[] = "2-Service Port de Clear Channel Criada $servicePortL2LIds[servicesPortsIds]!";

        $insere_service_l2l = "UPDATE ont SET service_port_l2l='$servicePortL2LIds[servicesPortsIds]' WHERE serial = '$serial_number'";
        $executa_insere_service_l2l = mysqli_query($conectar, $insere_service_l2l);

        ############## CRIA O SERVICE PORT DE GERENCIA ####################
        $servicePortInternet = get_service_port_internet(
            $device,
            $frame,
            $slot,
            $pon,
            $onuID,
            $contrato,
            null,
            null,
            null,
            $gemPortList[6]);
        $tira_ponto_virgula = explode(";", $servicePortInternet);
        $check_sucesso = explode("EN=", $tira_ponto_virgula[1]);
        $remove_desc = explode("ENDESC=", $check_sucesso[1]);
        $errorCode = trim($remove_desc[0]);
        if ($errorCode != "0") //se der erro na service port internet
        {
            $trato = tratar_errors($errorCode);
            $array_process_result[] = "Erro ao criar o service port de Gerencia/Internet: $trato";

            //se der erro ele irá apagar o registro salvo na tabela local ont
            $sql_apagar_onu = ("DELETE FROM ont WHERE contrato = '$contrato' AND serial = '$serial_number'");
            mysqli_query($conectar, $sql_apagar_onu);

            array_push($array_process_result, "Removido do banco local");

                $deletar_onu_radius_banda = "DELETE FROM radreply WHERE username='$gemPortList[6]/$slot/$pon/$serial_number@vertv'
                  AND attribute='Huawei-Qos-Profile-Name' ";
                mysqli_query($conectar_radius, $deletar_onu_radius_banda);

            $array_process_result[] = "Removido do Radius";
            deletar_onu_2000($device, $frame, $slot, $pon, $onuID, $ip_olt, NULL);

            $sql_insert_log = "INSERT INTO log (registro,codigo_usuario, mac, cto, contrato)
                VALUES ('ERRO NO U2000 AO GERAR SERVICE PORT DE GERENCIA $trato Número Sem Tratamento: $errorCode e U2000: $ontID 
                informações relatadas: OLT: $device, PON: $pon, Frame: $frame,
                Porta de Atendimento: $porta_selecionado, Slot: $slot, CTO: $cto Contrato: $contrato,
                MAC: $serial_number, Perfil: $vasProfile, Internet: $pacote_internet, Telefone: $sip_number,
                Senha Telefone: $sip_password','$usuario', '$serial_number', '$cto', '$contrato')";
            $executa_log = mysqli_query($conectar,$sql_insert_log);

            $array_process_result[] = "Removido do u2000";

            return exitWithMessage($array_process_result, $connectionsList);
        }
        $remove_barras_para_pegar_id = explode("--------------", $tira_ponto_virgula[1]);
        $pegar_servicePorta_ID = explode("\r\n", $remove_barras_para_pegar_id[1]);
        $pega_id = explode("\t", $pegar_servicePorta_ID[2]);//posicao 4 será sempre o ONTID
        $servicePortInternetID = $pega_id[0] - 1;

        $array_process_result[] = "3-Service Port de Internet/Gerencia Criada $servicePortInternetID!";

        $insere_service_internet = "UPDATE ont SET service_port_internet=$servicePortInternetID WHERE serial = '$serial_number'";
        $executa_insere_service_internet = mysqli_query($conectar, $insere_service_internet);

        $array_process_result[] = "4-Lan to Lan Cadastrada!";

        $sql_insert_log = "INSERT INTO log (registro,codigo_usuario)
                VALUES ('ServicePort Gerencia/Internet Cadastrada  
                informações relatadas: OLT: $device, PON: $pon, Frame: $frame, 
                Porta de Atendimento: $porta_selecionado, Slot: $slot, CTO: $cto Contrato: $contrato,
                MAC: $serial_number, Perfil: $vasProfile, Internet: $pacote_internet, Telefone: $sip_number,
                Senha Telefone: $sip_password','$usuario')";
        $executa_log = mysqli_query($conectar,$sql_insert_log);

    }

##################################### I N T E R N E T ##############################################
    if ($internet) {
        $array_process_result[] = "### INTERNET ###";

        ##### SELECIONA O SERVICE PORT PARA VERIFICAR SE TEM A VLAN DE INTERNET CADASTRADA #########
        $select_servicePortInternet = "SELECT service_port_internet FROM ont WHERE contrato=$contrato";
        $executa_select_servicePortInternet = mysqli_query($conectar,$select_servicePortInternet);
        $row = mysqli_fetch_assoc($executa_select_servicePortInternet);
        if ($row['service_port_internet'] == NULL) {
            $array_process_result[] = "ERRO: Não encontrei o Service Port ID Criado! $row[service_port_internet]";

            //se der erro ele irá apagar o registro salvo na tabela local ont
            $sql_apagar_onu = ("DELETE FROM ont WHERE contrato = '$contrato' AND serial = '$serial_number'" );
            mysqli_query($conectar,$sql_apagar_onu);
            array_push($array_process_result,"Removido do Banco Local");

            //Desativa Porta CTO
            $sql_insere_porta = "UPDATE ctos SET porta_atendimento_disponivel = 0, serial = '$serial_number'
                WHERE caixa_atendimento = '$cto' AND porta_atendimento= '$porta_selecionado'";
            $executa_insere_porta = mysqli_query($conectar,$sql_insere_porta);
            $array_process_result[] = "Desassociada Porta $porta_selecionado da CTO $cto";

            $deletar_onu_radius_banda = "DELETE FROM radreply WHERE username='$gemPortList[6]/$slot/$pon/$serial_number@vertv'
              AND attribute='Huawei-Qos-Profile-Name' ";
            mysqli_query($conectar_radius,$deletar_onu_radius_banda);
            $array_process_result[] = "Removido do Radius";

            deletar_onu_2000($device,$frame,$slot,$pon,$onuID,$ip_olt,NULL);

            $sql_insert_log = "INSERT INTO log (registro,codigo_usuario, mac, cto, contrato)
                VALUES ('ERRO Não encontrei o Service Port ID Criado! 
                informações relatadas: OLT: $device, PON: $pon, Frame: $frame,
                Porta de Atendimento: $porta_selecionado, Slot: $slot, CTO: $cto Contrato: $contrato,
                MAC: $serial_number, Perfil: $vasProfile, Internet: $pacote_internet, Telefone: $sip_number,
                Senha Telefone: $sip_password','$usuario', '$serial_number', '$cto', '$contrato')";
            $executa_log = mysqli_query($conectar,$sql_insert_log);

            $array_process_result[] = "Removido do u2000";

            return exitWithMessage($array_process_result, $connectionsList);
        }
        ############### INSERE A BANDA NO RADIUS ################
        $insere_ont_radius_qos_profile = "INSERT INTO radreply( username, attribute, op, value)
            VALUES ( '$gemPortList[6]/$slot/$pon/$serial_number@vertv', 'Huawei-Qos-Profile-Name', ':=', '$pacote_internet' )";

        $executa_query_qos_profile = mysqli_query($conectar_radius, $insere_ont_radius_qos_profile);

        if (!$executa_query_qos_profile) {
            $array_process_result[] = "Ocorreu um erro ao Inserir no Radius!";

            //se der erro ele irá apagar o registro salvo na tabela local ont
            $sql_apagar_onu = ("DELETE FROM ont WHERE contrato = '$contrato' AND serial = '$serial_number'");
            mysqli_query($conectar, $sql_apagar_onu);
            $array_process_result[] = "Removido do Banco Local";

            $deletar_onu_radius_banda = "DELETE FROM radreply WHERE username='$gemPortList[6]/$slot/$pon/$serial_number@vertv'
                AND attribute='Huawei-Qos-Profile-Name' ";
            $executa_query = mysqli_query($conectar_radius, $deletar_onu_radius_banda);
            $array_process_result[] = "Removido do Radius";

            deletar_onu_2000($device, $frame, $slot, $pon, $onuID, $ip_olt, NULL);
            $array_process_result[] = "Removido do do u2000";
            return exitWithMessage($array_process_result, $connectionsList);
        }

        $array_process_result[] = "1-ONT inserida Banda no Radius!";
        $array_process_result[] = "2-Service Port de Internet Criada $servicePortInternetID!";

        $insere_service_internet = "UPDATE ont SET service_port_internet=$servicePortInternetID WHERE serial = '$serial_number'";
        $executa_insere_service_internet = mysqli_query($conectar, $insere_service_internet);

        $array_process_result[] = "3-Internet Cadastrada!";

        $sql_insert_log = "INSERT INTO log (registro,codigo_usuario)
                VALUES ('ServicePort Internet Ativada  
                informações relatadas: OLT: $device, PON: $pon, Frame: $frame, 
                Porta de Atendimento: $porta_selecionado, Slot: $slot, CTO: $cto Contrato: $contrato,
                MAC: $serial_number, Perfil: $vasProfile, Internet: $pacote_internet, Telefone: $sip_number,
                Senha Telefone: $sip_password','$usuario')";
        $executa_log = mysqli_query($conectar,$sql_insert_log);

    }

######################################### I P T V ##################################################
    if ($iptv) {
        $array_process_result[] = "### I P T V ###";
        ####### ATIVA SERVICE PORT IPTV ########
        $servicePortIPTV = get_service_port_iptv($device,$frame,$slot,$pon,$onuID,$contrato);

        $tira_ponto_virgula = explode(";",$servicePortIPTV);
        $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
        $remove_desc = explode("ENDESC=",$check_sucesso[1]);
        $errorCode = trim($remove_desc[0]);

        if($errorCode != "0") //se der erro na service port iptv
        {
            $trato = tratar_errors($errorCode);

            $array_process_result[] = "Houve erro Inserir a Service Port de IPTV: $trato";

            //se der erro ele irá apagar o registro salvo na tabela local ont
            $sql_apagar_onu = ("DELETE FROM ont WHERE contrato = '$contrato' AND serial = '$serial_number'");
            mysqli_query($conectar, $sql_apagar_onu);
            $array_process_result[] = "Removido do Banco Local";

            $deletar_onu_radius_banda = "DELETE FROM radreply WHERE username='$gemPortList[6]/$slot/$pon/$serial_number@vertv'
              AND attribute='Huawei-Qos-Profile-Name' ";
            $executa_query = mysqli_query($conectar_radius, $deletar_onu_radius_banda);

            $deletar_onu_radius = " DELETE FROM radcheck WHERE username='$gemPortList[6]/$slot/$pon/$serial_number@vertv' ";
            $executa_query_radius = mysqli_query($conectar_radius, $deletar_onu_radius);
            $array_process_result[] = "Removido do Radius";

            deletar_onu_2000($device, $frame, $slot, $pon, $onuID, $ip_olt, $servicePortIPTV);

            $sql_insert_log = "INSERT INTO log (registro,codigo_usuario, mac, cto, contrato)
                VALUES ('ERRO NO U2000 AO GERAR SERVICE PORT IPTV $trato Número Sem Tratamento: $errorCode e U2000: $ontID 
                informações relatadas: OLT: $device, PON: $pon, Frame: $frame,
                Porta de Atendimento: $porta_selecionado, Slot: $slot, CTO: $cto Contrato: $contrato,
                MAC: $serial_number, Perfil: $vasProfile, Internet: $pacote_internet, Telefone: $sip_number,
                Senha Telefone: $sip_password','$usuario', '$serial_number', '$cto', '$contrato')";
            $executa_log = mysqli_query($conectar,$sql_insert_log);

            $array_process_result[] = "Removido do do u2000";

            return exitWithMessage($array_process_result, $connectionsList);
        }
        $remove_barras_para_pegar_id = explode("--------------",$tira_ponto_virgula[1]);
        $pegar_servicePorta_ID = explode("\r\n",$remove_barras_para_pegar_id[1]);

        $pega_id = explode("\t",$pegar_servicePorta_ID[2]);//posicao 4 será sempre o ONTID

        $servicePortIptvID= $pega_id[0] - 1;
        $array_process_result[] = "Service Port IPTV Criado: $servicePortIptvID";

        $insere_service_iptv = "UPDATE ont SET service_port_iptv='$servicePortIptvID' WHERE serial = '$serial_number'";
        $executa_insere_service_iptv = mysqli_query($conectar,$insere_service_iptv);
        $array_process_result[] = "Atualizado Service Port na ONT";

        $sql_insert_log = "INSERT INTO log (registro,codigo_usuario)
                VALUES ('ServicePort IPTV Cadastrada  
                informações relatadas: OLT: $device, PON: $pon, Frame: $frame, 
                Porta de Atendimento: $porta_selecionado, Slot: $slot, CTO: $cto Contrato: $contrato,
                MAC: $serial_number, Perfil: $vasProfile, Internet: $pacote_internet, Telefone: $sip_number,
                Senha Telefone: $sip_password','$usuario')";
        $executa_log = mysqli_query($conectar,$sql_insert_log);


        ### BTV ###
        $btv_olt = insere_btv_iptv($device,$frame,$slot,$pon,$onuID);
        $tira_ponto_virgula = explode(";",$btv_olt);
        $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
        $remove_desc = explode("ENDESC=",$check_sucesso[1]);
        $errorCode = trim($remove_desc[0]);

        if($errorCode != "0") //se der erro na btv iptv
        {
            $trato = tratar_errors($errorCode);

            array_push($array_process_result, "Houve erro ao criar o BTV: $trato");

            //se der erro ele irá apagar o registro salvo na tabela local ont
            $sql_apagar_onu = ("DELETE FROM ont WHERE contrato = '$contrato' AND serial = '$serial_number'");
            mysqli_query($conectar, $sql_apagar_onu);
            $array_process_result[] = "Removido do Banco Local";

            $deletar_onu_radius_banda = "DELETE FROM radreply WHERE username='$gemPortList[6]/$slot/$pon/$serial_number@vertv'
              AND attribute='Huawei-Qos-Profile-Name' ";
            $executa_query = mysqli_query($conectar_radius, $deletar_onu_radius_banda);

            $deletar_onu_radius = " DELETE FROM radcheck WHERE username='$gemPortList[6]/$slot/$pon/$serial_number@vertv' ";
            $executa_query_radius = mysqli_query($conectar_radius, $deletar_onu_radius);
            $array_process_result[] = "Removido do Radius";

            deletar_onu_2000($device, $frame, $slot, $pon, $onuID, $ip_olt, $servicePortIPTV);
            $array_process_result[] = "Removido do do u2000";

            $sql_insert_log = "INSERT INTO log (registro,codigo_usuario, mac, cto, contrato)
                VALUES ('ERRO NO U2000 AO GERAR BTV $trato Número Sem Tratamento: $errorCode e U2000: $ontID 
                informações relatadas: OLT: $device, PON: $pon, Frame: $frame,
                Porta de Atendimento: $porta_selecionado, Slot: $slot, CTO: $cto Contrato: $contrato,
                MAC: $serial_number, Perfil: $vasProfile, Internet: $pacote_internet, Telefone: $sip_number,
                Senha Telefone: $sip_password','$usuario', '$serial_number', '$cto', '$contrato')";
            $executa_log = mysqli_query($conectar,$sql_insert_log);

            return exitWithMessage($array_process_result, $connectionsList);
        }
        $array_process_result[] = "BTV Criado na OLT";
        $array_process_result[] = "IPTV Ativada!";

        $sql_insert_log = "INSERT INTO log (registro,codigo_usuario)
                VALUES ('BTV CRIADO  
                informações relatadas: OLT: $device, PON: $pon, Frame: $frame, 
                Porta de Atendimento: $porta_selecionado, Slot: $slot, CTO: $cto Contrato: $contrato,
                MAC: $serial_number, Perfil: $vasProfile, Internet: $pacote_internet, Telefone: $sip_number,
                Senha Telefone: $sip_password','$usuario')";
        $executa_log = mysqli_query($conectar,$sql_insert_log);

    }
##################################### T E L E F O N E ##############################################
    if ($voip) {
        $array_process_result[] = "### TELEFONE ###";

        #### ATIVA A POTS DO TELEFONE #####
        $telefone_on = ativa_telefonia($device,$frame,$slot,$pon,$onuID,$sip_number,$sip_password,$sip_number);
        $tira_ponto_virgula = explode(";",$telefone_on);
        $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
        $remove_desc = explode("ENDESC=",$check_sucesso[1]);
        $errorCode = trim($remove_desc[0]);
        if ($errorCode != "0") {//se der erro na ativacao da telefonia
            $trato = tratar_errors($errorCode);

            $array_process_result[] = "Houve erro ao ativar os numeros na ONT: $trato";

            //se der erro ele irá apagar o registro salvo na tabela local ont
            $sql_apagar_onu = ("DELETE FROM ont WHERE contrato = '$contrato' AND serial = '$serial_number'" );
            mysqli_query($conectar,$sql_apagar_onu);
            $array_process_result[] = "Removido do Banco Local";

            //Desativa Porta CTO
            $sql_insere_porta = "UPDATE ctos SET porta_atendimento_disponivel = 0, serial = '$serial_number'
                WHERE caixa_atendimento = '$cto' AND porta_atendimento= '$porta_selecionado'";
            $executa_insere_porta = mysqli_query($conectar,$sql_insere_porta);
            $array_process_result[] = "Desassociada Porta $porta_selecionado da CTO $cto";

            $deletar_onu_radius_banda = "DELETE FROM radreply WHERE username='$gemPortList[6]/$slot/$pon/$serial_number@vertv'
              AND attribute='Huawei-Qos-Profile-Name' ";
            $executa_query= mysqli_query($conectar_radius,$deletar_onu_radius_banda);

            $deletar_onu_radius = " DELETE FROM radcheck WHERE username='$gemPortList[6]/$slot/$pon/$serial_number@vertv' ";
            $executa_query_radius = mysqli_query($conectar_radius,$deletar_onu_radius);
            $array_process_result[] = "Removido do Radius";

            deletar_onu_2000($device,$frame,$slot,$pon,$onuID,$ip_olt,NULL);

            $sql_insert_log = "INSERT INTO log (registro,codigo_usuario, mac, cto, contrato)
                VALUES ('ERRO NO U2000 AO ATIVAR POTS DE TELEFONIA $trato Número Sem Tratamento: $errorCode e U2000: $ontID 
                informações relatadas: OLT: $device, PON: $pon, Frame: $frame,
                Porta de Atendimento: $porta_selecionado, Slot: $slot, CTO: $cto Contrato: $contrato,
                MAC: $serial_number, Perfil: $vasProfile, Internet: $pacote_internet, Telefone: $sip_number,
                Senha Telefone: $sip_password','$usuario', '$serial_number', '$cto', '$contrato')";
            $executa_log = mysqli_query($conectar,$sql_insert_log);

            $array_process_result[] = "Removido do do u2000";

            return exitWithMessage($array_process_result, $connectionsList);
        }
        ## INICIO SERVICE PORT TELEFONE ##
        $servicePortTelefone = get_service_port_telefone($device,$frame,$slot,$pon,$onuID,$contrato);

        $tira_ponto_virgula = explode(";",$servicePortTelefone);
        $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
        $remove_desc = explode("ENDESC=",$check_sucesso[1]);
        $errorCode = trim($remove_desc[0]);
        if ($errorCode != "0") {//se der erro na service port telefone
            $trato = tratar_errors($errorCode);

            $array_process_result[] ="Houve erro ao criar a Service Port de Telefonia: $trato";

            //se der erro ele irá apagar o registro salvo na tabela local ont
            $sql_apagar_onu = ("DELETE FROM ont WHERE contrato = '$contrato' AND serial = '$serial_number'" );
            mysqli_query($conectar,$sql_apagar_onu);
            $array_process_result[] = "Removido do Banco Local";

            //Desativa Porta CTO
            $sql_insere_porta = "UPDATE ctos SET porta_atendimento_disponivel = 0, serial = '$serial_number'
                WHERE caixa_atendimento = '$cto' AND porta_atendimento= '$porta_selecionado'";
            $executa_insere_porta = mysqli_query($conectar,$sql_insere_porta);
            $array_process_result[] = "Desassociada Porta $porta_selecionado da CTO $cto";

            $deletar_onu_radius_banda = "DELETE FROM radreply WHERE username='$gemPortList[6]/$slot/$pon/$serial_number@vertv'
                AND attribute='Huawei-Qos-Profile-Name' ";
            $executa_query= mysqli_query($conectar_radius,$deletar_onu_radius_banda);

            $deletar_onu_radius = " DELETE FROM radcheck WHERE username='$gemPortList[6]/$slot/$pon/$serial_number@vertv' ";
            $executa_query_radius = mysqli_query($conectar_radius,$deletar_onu_radius);
            $array_process_result[] = "Removido do Radius";

            deletar_onu_2000($device,$frame,$slot,$pon,$onuID,$ip_olt,NULL);

            $sql_insert_log = "INSERT INTO log (registro,codigo_usuario, mac, cto, contrato)
                VALUES ('ERRO NO U2000 AO GERAR SERVICE PORT DE TELEFONIA $trato Número Sem Tratamento: $errorCode e U2000: $ontID 
                informações relatadas: OLT: $device, PON: $pon, Frame: $frame,
                Porta de Atendimento: $porta_selecionado, Slot: $slot, CTO: $cto Contrato: $contrato,
                MAC: $serial_number, Perfil: $vasProfile, Internet: $pacote_internet, Telefone: $sip_number,
                Senha Telefone: $sip_password','$usuario', '$serial_number', '$cto', '$contrato')";
            $executa_log = mysqli_query($conectar,$sql_insert_log);

            $array_process_result[] = "Removido do do u2000";

            return exitWithMessage($array_process_result, $connectionsList);
        }
        $remove_barras_para_pegar_id = explode("--------------",$tira_ponto_virgula[1]);
        $pegar_servicePortTel_ID = explode("\r\n",$remove_barras_para_pegar_id[1]);

        $pega_id = explode("\t",$pegar_servicePortTel_ID[2]);//posicao 4 será sempre o ONTID

        $servicePortTelefoneID= $pega_id[0] - 1;
        $array_process_result[] = "Service Port Telefonia Criado: $servicePortTelefoneID";

        $sql_insert_log = "INSERT INTO log (registro,codigo_usuario)
                VALUES ('ServicePort Telefone Cadastrada  
                informações relatadas: OLT: $device, PON: $pon, Frame: $frame, 
                Porta de Atendimento: $porta_selecionado, Slot: $slot, CTO: $cto Contrato: $contrato,
                MAC: $serial_number, Perfil: $vasProfile, Internet: $pacote_internet, Telefone: $sip_number,
                Senha Telefone: $sip_password','$usuario')";
        $executa_log = mysqli_query($conectar,$sql_insert_log);

        $insere_service_telefone = "UPDATE ont SET service_port_telefone='$servicePortTelefoneID' WHERE serial = '$serial_number'";
        $executa_insere_service_telefone = mysqli_query($conectar,$insere_service_telefone);
        $array_process_result[] = "Atualizado Service Port na ONT";
    }

return exitWithMessage($array_process_result, $connectionsList, false);






