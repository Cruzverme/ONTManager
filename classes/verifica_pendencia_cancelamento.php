<?php 

  ######## este script apenas olha no ERP quem está inadimplente e renova tabela do banco

  include "/var/www/html/ontManager/db/db_config_mysql.php";
  include "/var/www/html/ontManager/db/db_config_radius.php";

  ####### PEGAR CLIENTES FTTH 
  $json_file = @file_get_contents("http://localhost/sisspc/demos/get_cancelados.php");
  $json_str = json_decode($json_file, true);
  
  if($json_str['success'] == 1)
  {
    ########## REMOVE LISTA ATUAL #########
    $sql_remove_lista_inadimplente = "DELETE FROM canceled_costumer";
    $execute_remove_lista_inadimplente = mysqli_query($conectar,$sql_remove_lista_inadimplente);

    #### contrato dos assinantes
    $sql_assinantes_cadastrados = "SELECT contrato,status,serial from ont";
    $execute_contrato = mysqli_query($conectar,$sql_assinantes_cadastrados);
    $status_contrato_assinante = mysqli_fetch_all($execute_contrato);
    
    $lista = array();

    #### assinantes que estão indimplentes no cplus contrato | nome
    $listaAssinantes = $json_str['dados'];
    
    foreach($listaAssinantes as $dados)
    {
      $nomeCompleto = $dados[1];
      $contrato = $dados[0];
      
      foreach($status_contrato_assinante as $assinante)
      {
        ##### Se tiver ont no banco cadastrado, irá realizar entrar na lista.
        if($assinante[0] == $contrato)
        {
          $sql_insert_contrato_inadimplente = "INSERT INTO canceled_costumer (contrato,nome,status,serial) VALUES ($contrato,'$nomeCompleto',$assinante[1],'$assinante[2]')";
          $execute_insert_contrato_inadimplente = mysqli_query($conectar,$sql_insert_contrato_inadimplente);
        }
      }
    }
    echo "concluido";
  }else{
    echo "Não Consegui Pegar os Contratos!";
  }

  ########## JOGAR EM ARRAY E INARRAY
?>
