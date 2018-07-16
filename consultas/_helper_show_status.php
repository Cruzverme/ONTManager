<?php 

include_once "../u2000/tl1_sender.php";

$device = filter_input(INPUT_POST,'dev');
$frame = filter_input(INPUT_POST,'frame');
$slot = filter_input(INPUT_POST,'slot');
$pon = filter_input(INPUT_POST,'pon');
$ontID= filter_input(INPUT_POST,'ont');
$acao = filter_input(INPUT_POST,'acao');

if($acao == "fabric")
{
  $reseta_equipamento = reset_fabric_ont($device,$frame,$slot,$pon,$ontID);
  echo "Estou Sendo Redefinida para as Configurações de Fabrica, Favor Aguarde Meu Retorno!";
}elseif($acao == "reset")
{
  $reseta_equipamento = reseta_ont($device,$frame,$slot,$pon,$ontID);
  echo "Estou Sendo Reiniciada, Favor Aguarde Meu Retorno!";  
}else{
  echo "Não consegui entender o que é pra fazer!";
}
?>