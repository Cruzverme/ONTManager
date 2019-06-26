<?php 

  include_once "../db/db_config_mysql.php";

  $vlan = filter_input(INPUT_POST,'vlan');
  $costumers = array();
  
  $sql_get_costumers = "SELECT contrato, serial FROM ont WHERE vlan_id = $vlan";
  $execute_get_costumers = mysqli_query($conectar,$sql_get_costumers);

  while($result = mysqli_fetch_array($execute_get_costumers,MYSQLI_ASSOC)){
    array_push($costumers,$result['contrato']);
  }

  

  if( count($costumers) >=1 )
  {  $retorno = "";
    for ($i=0; $i < count($costumers); $i++) { 
      $retorno = $retorno ."<p>$costumers[$i]</p>";  
    }
    echo $retorno;
  }else{
    $retorno = "";
    $retorno = "<p>Não há clientes associados nesta Vlan</p>";
    echo $retorno;
  }
  
  

?>