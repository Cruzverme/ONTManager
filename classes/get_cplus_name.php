<?php 
  $contrato = filter_input(INPUT_POST,"nContra");
  
  $json_file = @file_get_contents("http://192.168.80.5/sisspc/demos/get_pacote_ftth_cplus.php?contra=$contrato");
  $json_str = json_decode($json_file, true);

  if($json_str['success'] == 1)
    if($json_str['nome'] != null) echo $json_str['nome'][0]; else echo "Nome não encontrado!";
  else
    echo "Nome não encontrado!";

?>