<?php 

  include_once "../db/db_config_mysql.php";
  session_start();

  $contrato = $_POST['clientes_vlan'];
  $vlan = filter_input(INPUT_POST,"nVlan");
  
  $sql_update_vlan = "UPDATE ont SET vlan_id = null WHERE contrato = $contrato";
  $execute_sql_update_vlan = mysqli_query($conectar,$sql_update_vlan);

  echo $_SESSION['menssagem'] = "Contrato Desassociado da Vlan $vlan";
  header("Location: ../vlan/vlan_list.php");

?>