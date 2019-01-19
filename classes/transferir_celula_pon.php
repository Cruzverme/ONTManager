<?php 
  set_time_limit(300); // TEMPO PARA EXECUTAR 5MIN
  
  include "../db/db_config_mysql.php";
  include "./funcoes.php";

  $olt = filter_input(INPUT_POST,"olt");
  $nomeDispositivo = filter_input(INPUT_POST,"dispositivo");
  $frame = filter_input(INPUT_POST,'frame');
  $slot = filter_input(INPUT_POST,'slot');
  $porta_pon = filter_input(INPUT_POST,"pon");
  $ctos = filter_input(INPUT_POST,'cto_ativa',FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);

  $array_migracao_falha = array();
  $array_migracao_sucesso = array();

  if(sizeof($ctos) == 1)
  {
    $cto_com_porta = explode('-',$ctos[0]);
    $celula = $cto_com_porta[0];

    $sql_frame_slot_pon_cto = "SELECT DISTINCT p.olt_ip,c.frame_slot_pon FROM ctos c
                                INNER JOIN pon p ON p.pon_id = c.pon_id_fk
                                WHERE caixa_atendimento LIKE '$celula%'";
    $executa_sql_frame_slot_pon_cto = mysqli_query($conectar,$sql_frame_slot_pon_cto);

    $frame_slot_pon = "";
    $olt_ip = "";
    while($teste = mysqli_fetch_array($executa_sql_frame_slot_pon_cto,MYSQLI_ASSOC))
    {
      $frame_slot_pon = $teste['frame_slot_pon'];
      $olt_ip = $teste['olt_ip'];
    }

    list($frame_atual,$slot_atual,$pon_atual) = explode('-',$frame_slot_pon);

    echo "celula $celula <br>";

    $sql_listar_todas_onts = "SELECT * FROM ont WHERE cto LIKE '$celula%'";
    
    $executa_lista = mysqli_query($conectar,$sql_listar_todas_onts);
    
    while($r = mysqli_fetch_array($executa_lista,MYSQLI_ASSOC))
    {
      ### INSERE NA TABELA DE BKP ###
      $insere_backup = "insert into transicao_ont (onu_id,contrato,serial,status,cto,porta,usuario_id,tel_number,tel_user,tel_password,
      perfil,pacote,limite_equipamentos,equipamento,ontID,service_port_internet,service_port_iptv,service_port_telefone,mac,ip)
      values ($r[onu_id],$r[contrato],'$r[serial]',$r[status],'$r[cto]',$r[porta],$r[usuario_id],$r[tel_number],$r[tel_user],$r[tel_password],
      '$r[perfil]','$r[pacote]',$r[limite_equipamentos],'$r[equipamento]','$r[ontID]','$r[service_port_internet]','$r[service_port_iptv]','$r[service_port_telefone]',
      '$r[mac]', '$r[ip]')";
    
      $executa_insere_backup = mysqli_query($conectar,$insere_backup);
    
      ### FIM INSERE NA TABELA DE BKP ###

      $nomeCompleto =  get_nome_alias_cplus($r['contrato']);

      ##### REMOVE DO U2000 PARA INSERIR NOVAMENTE NA FUNCAO DEU_RUIM
      $deletar_2000 = deletar_onu_2000($nomeDispositivo,$frame_atual,$slot_atual,$pon_atual,$r['ontID'],$olt_ip,$r['service_port_iptv']);
      $tira_ponto_virgula = explode(";",$deletar_2000);
      $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
      $remove_desc = explode("ENDESC=",$check_sucesso[1]);
      $errorCode = trim($remove_desc[0]);
      
      if($errorCode != "0" && $errorCode != "1615331086") //se der erro ao deletar a ONT
      {
        $trato = tratar_errors($errorCode);
        array_push($array_migracao_falha,$r['contrato']);
      }else{
        $retorno = deu_ruim_callback($nomeDispositivo,$frame,$slot,$porta_pon,$r['contrato'],$nomeCompleto,$r['cto'],$r['porta'],$r['serial'],
        $r['equipamento'],$r['perfil'],$r['tel_number'],$r['tel_password'],$r['pacote']);
        
        array_push($array_migracao_sucesso,$r['contrato']);
      }
    }
    $sql_atualiza_pon_cto = "UPDATE ctos SET frame_slot_pon = '$frame-$slot-$porta_pon' WHERE caixa_atendimento LIKE '$celula%'";
    $executa = mysqli_query($conectar,$sql_atualiza_pon_cto);
    $totaldeCTOsMigradas = mysqli_affected_rows($conectar); 

    session_start();  
    $_SESSION['sucesso'] = $array_migracao_sucesso;
    $_SESSION['falha'] = $array_migracao_falha;
    $_SESSION['ctosMigradas'] = $totaldeCTOsMigradas;
    
    echo('<meta http-equiv="refresh" content="0;URL=../cto_classes/transfer_resumo_migracao.php">');
    mysqli_close($conectar);
    exit;
  }
?>