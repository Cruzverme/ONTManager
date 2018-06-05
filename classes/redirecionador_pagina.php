<?php 

//if (!isset($_SESSION)) {}
  session_start();
  
  if($_SESSION["cadastrar_onu"] == 1)
  {
    header('Location: ../ont_classes/ont_register.php');
  }elseif ($_SESSION["modificar_onu"] == 1) {
    header('Location: ../ont_classes/ont_change.php');
  }elseif ($_SESSION["deletar_onu"] == 1) {
    header('Location: ../ont_classes/ont_delete.php');
  }elseif($_SESSION["alterar_macONT"] == 1){
    header('Location: ../ont_classes/alterar_mac_ont.php');
  }elseif ($_SESSION["desativar_ativar_onu"] == 1) {
    header('Location: ../ont_classes/ont_disable.php');
  }elseif ($_SESSION["cadastrar_cto"] == 1) {
    header('Location: ../cto_classes/cto_create.php');
  }elseif ($_SESSION["cadastrar_olt"] == 1) {
    header('Location: ../cto_classes/pon_create.php');
  }elseif ($_SESSION["cadastrar_velocidade"] == 1) {
    header('Location: ../planos/planos_create.php');
  }elseif ($_SESSION["cadastrar_usuario"] == 1) {
    header('Location: ../users/usuario_new.php');
  }elseif ($_SESSION["cadastrar_equipamento"] == 1) {
    header('Location: ../equipamento/cadastro_equipamento.php');
  }elseif ($_SESSION["consulta_onts"] == 1) {
    header('Location: ../consultas/get_status.php');
  }elseif ($_SESSION["consulta_ctos"] == 1) {
    header('Location: ../consultas/get_info_cto.php');
  }else{
    session_destroy();
    header('Location: ../index.php');
  }
  
?>