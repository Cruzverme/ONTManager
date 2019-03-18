<?php 

  include_once "../db/db_config_mysql.php";
  
  ini_set('display_errors', 1);

  error_reporting(E_ALL);

  $from = "abobrinha@vertv.com.br";

  $to = "charles@vertv.com.br";

  $subject = "CTOs No Limite de Portas Disponíveis";

  $headers = "De:". $from;  

  $sql_get_qtd_porta_cto_disponivel = "SELECT count(porta_atendimento_disponivel),caixa_atendimento,frame_slot_pon 
                                        FROM `ctos` WHERE porta_atendimento_disponivel = 0 
                                        GROUP BY porta_atendimento_disponivel,caixa_atendimento,frame_slot_pon 
                                        order by caixa_atendimento";

  $executa_sql_get_qtd_porta_cto_disponivel = mysqli_query($conectar,$sql_get_qtd_porta_cto_disponivel);
  
  $mensagemCTOCritica = array();

  while($row = mysqli_fetch_array($executa_sql_get_qtd_porta_cto_disponivel,MYSQLI_BOTH))
  {
    if($row[0] <= 1)
      $estilo = "style='color:red;'";
    elseif($row[0] == 2)
      $estilo = "style='color:yellow;'";
    else
      $estilo = '';
    
    array_push($mensagemCTOCritica,"<p $estilo>CTO: $row[1] Quantidade Disponivel: $row[0] Frame-Slot-Pon: $row[2] </p>"); 
  }

  $message = "$mensagemCTOCritica";
  
  //mail($to, $subject, $message, $headers);

  echo "A mensagem de e-mail foi enviada.";




?>