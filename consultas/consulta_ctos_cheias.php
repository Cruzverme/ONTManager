<?php 

  include_once "/var/www/html/ontManager/db/db_config_mysql.php";
  
  ini_set('display_errors', 1);

  error_reporting(E_ALL);

  $from = "monitoramento@vertv.com.br";

  $to = "ti@vertv.com.br,rede@vertv.com.br";

  $subject = "CTOs No Limite de Portas Disponíveis";

  $headers = "De:". $from;  

  $sql_get_qtd_porta_cto_disponivel = "SELECT count(porta_atendimento_disponivel),caixa_atendimento,frame_slot_pon 
                                        FROM `ctos` WHERE porta_atendimento_disponivel = 0 
                                        GROUP BY  porta_atendimento_disponivel,caixa_atendimento,frame_slot_pon 
                                        order by caixa_atendimento";

  $executa_sql_get_qtd_porta_cto_disponivel = mysqli_query($conectar,$sql_get_qtd_porta_cto_disponivel);
  
  $mensagemCTOCritica = array();

  while($row = mysqli_fetch_array($executa_sql_get_qtd_porta_cto_disponivel,MYSQLI_BOTH))
  {
    if($row[0] <= 2) 
      array_push($mensagemCTOCritica,"CTO: $row[1] Quantidade Disponivel: $row[0] Frame-Slot-Pon: $row[2] \r\n ");
  }
  $implo = implode('',$mensagemCTOCritica);
  $message = $implo;
  
  mail($to, $subject, $message, $headers);

?>
