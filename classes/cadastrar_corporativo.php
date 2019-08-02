<?php 
  
  include_once "../db/db_config_mysql.php";
  include_once "../db/db_config_radius.php";
  include_once "../u2000/tl1_sender.php";
  // Inicia sessões 
  session_start();

  $nome = filter_input(INPUT_POST,'nome');
  $vasProfile = filter_input(INPUT_POST,'vasProfile');
  $serial_number = filter_input(INPUT_POST,'serial');
  $pacote_internet = filter_input(INPUT_POST,'pacote');
  $modelo_ont = filter_input(INPUT_POST,'equipamentos');
  $sip_number = filter_input(INPUT_POST,'numeroTel');
  $sip_password = filter_input(INPUT_POST,'passwordTel');

  $porta_selecionado = filter_input(INPUT_POST,'porta_atendimento');
  $frame = filter_input(INPUT_POST,'frame');
  $slot = filter_input(INPUT_POST,'slot');
  $pon = filter_input(INPUT_POST,'pon');
  $cto = filter_input(INPUT_POST,'cto');
  $device = filter_input(INPUT_POST,'device');
  $contrato = filter_input(INPUT_POST,'contrato');
  $designacao_circuito = filter_input(INPUT_POST,'designacao');
  $vlan_associada = filter_input(INPUT_POST,'vlan_number');
?>