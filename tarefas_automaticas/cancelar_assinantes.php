<?php 

  ######## este script apenas olha no ERP quem está cancelado e renova tabela do banco 
  ######## e bloqueia quem está cancelado

  include "/var/www/html/ontManager/db/db_config_mysql.php";
  include "/var/www/html/ontManager/db/db_config_radius.php";
  include "/var/www/html/ontManager/classes/funcoes.php";

  ####### PEGAR CLIENTES FTTH 
  $json_file = @file_get_contents("http://localhost/sisspc/demos/get_cancelados.php");
  $json_str = json_decode($json_file, true);
  
  if($json_str['success'] == 1)
  {
    $arquivo = NULL;
    ######## HTML TO EMAIL ########
    $html = '
          <table class="table table-hover display" id="tabelaSinais" data-link="row">
          <thead>
            <tr>
              <th>Contrato</th>
              <th>Nome</th>
              <th>Data Cancelamento</th>
              <th>Status</th>
              <th>Serial</th>
            </tr>
          </thead>
          <tbody>
      ';
    ########## REMOVE LISTA ATUAL #########
    $sql_remove_lista_cancelado = "DELETE FROM canceled_costumer_daily";
    $execute_remove_lista_cancelado = mysqli_query($conectar,$sql_remove_lista_cancelado);

    #### contrato dos assinantes
    $sql_assinantes_cadastrados = "SELECT contrato,status,serial from ont";
    $execute_contrato = mysqli_query($conectar,$sql_assinantes_cadastrados);
    $status_contrato_assinante = mysqli_fetch_all($execute_contrato);
    
    $lista = array();

    #### assinantes que estão cancelados no cplus contrato | nome
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
          if(!in_array($assinante[0],$lista))
          {
            ### ADICIONA A LISTA PARA QUE NAO SEJA INSERIDO NOVAMENTE
            array_push($lista,$contrato);
    
            ##### ELE IRÁ EXECUTAR O CANCELAMENTO
            $retorno_bloqueio = send_to_cancel($contrato,$assinante[2]);
            
            if($retorno_bloqueio == 1)
            {
              $sql_insert_contrato_cancelado = "INSERT INTO canceled_costumer_daily(contrato,nome,status,serial,cancelado_em) VALUES ($contrato,'$nomeCompleto',$assinante[1],'$assinante[2]')";
              
              $execute_insert_contrato_cancelado = mysqli_query($conectar,$sql_insert_contrato_cancelado);
              $data = date('d/m/Y h:i');
              $html .= "<tr>
                    <td>$contrato</td>
                    <td>$nomeCompleto</td>
                    <td>$data</td>
                    <td>Cancelado</td>
                    <td>$assinante[2]</td>
              </tr>";

              #### SALVA LOG DO BLOQUEIO ###
              $sql_log_estado = "INSERT INTO log_estado (contrato,user_id,estado) VALUES ($contrato,0,'Cancelado Automatico')";
              mysqli_query($conectar,$sql_log_estado);

              ### INSERE INFO NO CPLUS ###
              $comando = "curl http://192.168.80.5/sisspc/demos/push_information_in_cplus.php\?contrato\=$contrato\&assunto\=Contrato%20Cancelado%20Automaticamente";
              $exec = shell_exec($comando);

              $arquivo = "OK";
            }
          }
        }
      }
    }
    $html .= '
        </tbody>
      </table>
    ';
    if($arquivo != NULL)
    {
      send_email("Verificar Clientes Cancelados",$html,"gisele@vertv.com.br","Cancelamento",$arquivo);
    }else{
      send_email("Verificar Clientes Cancelados","<p style='font-weight:bold;'>Nenhum Cliente Cancelado!</p>","gisele@vertv.com.br","Cancelamento");
    }
    
    echo "concluido";
  }else{
    echo "Não Consegui Pegar os Contratos!";
  }
?>
