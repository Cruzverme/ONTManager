<?php 

  include_once "../u2000/tl1_sender.php";

  function checar_contrato($contrato)
  {
    $json_file = file_get_contents("http://192.168.80.5/sisspc/demos/get_contrato_status_ftth_cplus.php?contra=$contrato");
    $json_str = json_decode($json_file, true);
    
    $contrato = $json_str['contrato'];
    
    if(empty($contrato))
    {
      return null;
    }else{
      return 'ok';
    }
  }

  function reiniciaONT($deviceName,$framePar,$slotPar,$ponPar,$ontIDPar)
  {
    $reseta_equipamento = reseta_ont($deviceName,$framePar,$slotPar,$ponPar,$ontIDPar);

    $tira_ponto_virgula = explode(";",$reseta_equipamento);
    $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
    $remove_desc = explode("ENDESC=",$check_sucesso[1]);
    $errorCode = trim($remove_desc[0]);
    
    return $errorCode;
  }


?>