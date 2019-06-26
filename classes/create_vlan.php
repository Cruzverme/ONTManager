<?php 

  include_once "../db/db_config_mysql.php";
  include_once "../u2000/tl1_sender.php";
  session_start();
  
  $device = filter_input(INPUT_POST,'deviceName');
  $vlanID = filter_input(INPUT_POST,'nVlan');
  $aliasVlan = filter_input(INPUT_POST,'aliasVlan');
  
  $frame = '0';
  $slot = '18';
  $pon = '0';

  $create_vlan =  criar_vlan($device,$frame,$slot,$pon,$vlanID,$aliasVlan);
  $tira_ponto_virgula = explode(";",$create_vlan);
  $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
  $remove_desc = explode("ENDESC=",$check_sucesso[1]);
  $errorCode = trim($remove_desc[0]);
  
  if($errorCode != "0")
  {
    $trato = tratar_errors($errorCode);
    $_SESSION['menssagem'] = "Houve erro criar a Vlan $vlanID no u2000: $trato";
    header('Location: ../vlan/select_olt.php');
    mysqli_close($conectar);
    exit;
  }else{

    $sql_add_vlan = "INSERT INTO vlans (vlan,descricao) VALUES ($vlanID,'$aliasVlan')";
    $execute_sql_add_vlan = mysqli_query($conectar,$sql_add_vlan);

    $_SESSION['menssagem'] = "Vlan $vlanID Criada!";
    header('Location: ../vlan/select_olt.php');
    mysqli_close($conectar);
    exit;
  }

?>