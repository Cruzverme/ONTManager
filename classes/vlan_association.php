<?php 
  include_once "../db/db_config_mysql.php";
  session_start();
  $teste = $_POST['contratos'];
  $vlan = filter_input(INPUT_POST,"nVlan");
  
  foreach($teste as $contrato){

    echo "$contrato e $vlan<br>";

    $SQL = "SELECT * FROM ont WHERE contrato = $contrato";
    $executa_SQL = mysqli_query($conectar,$SQL);

    while ($listaPlanos = mysqli_fetch_array($executa_SQL, MYSQLI_BOTH))
    {
      echo "<br>$listaPlanos[serial]<br>";
      $sql_update_vlan = "UPDATE ont SET vlan_id = $vlan WHERE contrato = $contrato";
      $execute_sql_update_vlan = mysqli_query($conectar,$sql_update_vlan);
    }
  }
  echo $_SESSION['menssagem'] = "Contratos Associados a Vlan $vlan";
  header("Location: ../vlan/vlan_list.php");



?>