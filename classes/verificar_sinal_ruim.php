<?php 

  include_once "../db/db_config_mysql.php";
  include_once "../u2000/tl1_sender.php";
  include_once "funcoes.php";


  $lista_onts = array();
  $array_key_lista_onts = array('contrato','cto','deviceName','ontID','');
  $select_ont_infos = "SELECT onu.contrato, onu.ontID,onu.cto,onu.porta,onu.perfil,onu.service_port_iptv,onu.service_port_internet,
    onu.equipamento,ct.frame_slot_pon,ct.pon_id_fk,p.deviceName,p.olt_ip 
  FROM ont onu 
  INNER JOIN ctos ct ON ct.serial=onu.serial AND ct.caixa_atendimento= onu.cto 
  INNER JOIN pon p ON p.pon_id = ct.pon_id_fk ";

  $execute_ont_infos = mysqli_query($conectar,$select_ont_infos);
  mysqli_query($conectar,'DELETE FROM sinais_diarios');
 
  while($info = mysqli_fetch_array($execute_ont_infos, MYSQLI_BOTH))
  {
    $contrato = $info['contrato'];
    $ontID = $info['ontID'];
    list($frame,$slot,$pon) = explode('-',$info['frame_slot_pon']);
    $device = $info['deviceName'];
    $vasProfile = $info['perfil'];
    $cto = $info['cto'];
    $porta_atendimento = $info['porta'];

    $status_signal = get_signal_ont($device,$frame,$slot,$pon,$ontID);
    $tira_ponto_virgula_sip = explode(";",$status_signal);
    $check_sucesso_sip = explode("EN=",$tira_ponto_virgula_sip[1]);
    $remove_desc_sip = explode("ENDESC=",$check_sucesso_sip[1]);
    $errorCode_sip = trim($remove_desc_sip[0]);
    $remove_barra_sip = explode("-----------------------------------------------------------------------------------------",$remove_desc_sip[1]);
    $filtra_enter_sip = explode(PHP_EOL,$remove_barra_sip[1]);
    $filtra_resultados_sip = preg_split('/\s+/', $filtra_enter_sip[2]);//explode('',$filtra_enter[2]);
    
    if( -2500 <= $filtra_resultados_sip[7] && -3000 <= $filtra_resultados_sip[13] && $filtra_resultados_sip[7] != "--"  ) // Se for mais proximo de 0 e for maior que -25 ele esta ok
    {
      //echo "SINAL $filtra_resultados_sip[7] ABAIXO DE - 2505  com  ByOLT $filtra_resultados_sip[13] <br>";
    }else
    {
      if($filtra_resultados_sip[7] == '--' )
      {  $filtra_resultados_sip[7] = 0;}

      $insere_relatorio = " INSERT INTO `sinais_diarios`(`contrato`, `sinal`, `cto`, `porta_atendimento`, `sinalByOLT`, `sinalTX`) VALUES ('$contrato','$filtra_resultados_sip[7]','$cto','$porta_atendimento','$filtra_resultados_sip[13]','$filtra_resultados_sip[8]')";
      $execute_select_relatorio = mysqli_query($conectar,$insere_relatorio);
      
      echo "VERMELHO $filtra_resultados_sip[7] VAI EXPLODIR  OLT: $filtra_resultados_sip[13] <br>";
    }

  }
?>
