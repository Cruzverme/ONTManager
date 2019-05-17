<?php 
  include_once "../db/db_config_mysql.php";

  $quantidade = filter_input(INPUT_POST,'quantidade');
  $oltID = filter_input(INPUT_POST,'area_id');
  $listaClientes = [];

  ########### SELECT DE LISTA ALEATORIA DE ASSINANTES ELE NAO IRA PEGAR ASSINANTES Q JA FORAM INSERIDOS NA NAT_EXECUTADO
  $SQL = "SELECT * FROM ont equip
          INNER JOIN `ctos` caixa ON caixa.serial = equip.serial AND caixa.porta_atendimento_disponivel = 1
          INNER JOIN pon olt ON olt.pon_id = $oltID AND olt.pon_id = caixa.pon_id_fk
          WHERE equip.contrato NOT IN ( SELECT natExec.contrato FROM nat_executado natExec) AND (equip.mac = 'NULL' AND
          equip.ip = 'NULL' || equip.mac IS NULL AND equip.ip IS NULL) 
          order by RAND() limit $quantidade";

  $executaSQL = mysqli_query($conectar,$SQL);

  while ($clientes = mysqli_fetch_array($executaSQL, MYSQLI_ASSOC))
  {    
    array_push($listaClientes,$clientes);
  }

  echo json_encode($listaClientes);
?>