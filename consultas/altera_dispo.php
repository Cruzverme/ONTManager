<?php 
  include_once "../db/db_config_mysql.php";
  function multiexplode ($delimiters,$string)
  {
    $ready = str_replace($delimiters, $delimiters[0], $string);
    $launch = explode($delimiters[0], $ready);
    return  $launch;
  }

  $checked = filter_input(INPUT_POST,'cto_disponibilidade');
  
  $allCTOs = $_POST['unchecked'];
  
  $cto_ativas_raiz = multiexplode(array('cto_ativa%5B%5D%','=','&',' cto_ativa ',' ','',PHP_EOL),$checked);
  $cto_ativas_nutella =  str_replace('cto_ativa','', $cto_ativas_raiz);
  
  $arrayEscolhido = array();
  
  $ctos_desativadas = array_diff($allCTOs,$cto_ativas_nutella);
####Quando For Ativar Cai AQUI####


  if(!empty($cto_ativas_raiz)) //se nao for vazio
  {
    foreach($cto_ativas_nutella as $cto_ativa)
    {
      if($cto_ativa != '')
      {
        $remove_espacos_branco = explode(' ',$cto_ativa);
      
        $filtra_valor_da_cto = explode('-',$remove_espacos_branco[0]);

        $cto = $filtra_valor_da_cto[0];
      
        $sql_atualiza_disponivel_cto = "UPDATE ctos SET disponivel=1 WHERE caixa_atendimento LIKE '$cto%' ";
        $executa_atualiza_disponivel_cto = mysqli_query($conectar,$sql_atualiza_disponivel_cto);
        
        if($executa_atualiza_disponivel_cto == true) //se Executar ele incluira no array de efetuados
        {
          array_push($arrayEscolhido,$filtra_valor_da_cto[0]);
        }
        
        //echo "$cto";//2C1,2C4,Area e Celula...
        $status_disponivel_indisponivel = $filtra_valor_da_cto[1];
        
      }
    }
  }

### AQUI DESATIVA AS CTOS ####  
  ## ATUALIZA CTOs DESATIVADAS ##
  $arrayDesativados = [];

  if(!empty($ctos_desativadas))
  {
    foreach($ctos_desativadas as $cto_desativa)
    {
        $filtra_valor_da_cto_desativa = explode('-',$cto_desativa);
        $cto_desativada = $filtra_valor_da_cto_desativa[0];

        $sql_atualiza_disponivel_cto_desativa = "UPDATE ctos SET disponivel=0 WHERE caixa_atendimento LIKE '$cto_desativada%' ";
        
        $executa_atualiza_indisponivel_cto = mysqli_query($conectar,$sql_atualiza_disponivel_cto_desativa);
      
        if($executa_atualiza_indisponivel_cto) //se Executar ele incluira no array de efetuados
        {
          array_push($arrayDesativados,$cto_desativada);
        }
    }
  }
  echo "<br> ATIVADOS: ";
    foreach($arrayEscolhido as $ativos){ echo "$ativos ";}
  
  echo "<br> DESATIVADOS: ";
    foreach($arrayDesativados as $desativos){echo "$desativos ";}
?>