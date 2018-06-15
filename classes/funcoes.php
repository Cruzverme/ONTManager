<?php 

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

?>