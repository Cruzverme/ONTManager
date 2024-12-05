<?php 

  ######## este script apenas olha no ERP quem está inadimplente e renova tabela do banco 
  ######## e bloqueia quem está inadimplente

  include "/var/www/html/ontManager/db/db_config_mysql.php";
  include "/var/www/html/ontManager/db/db_config_radius.php";
  include "/var/www/html/ontManager/classes/funcoes.php";

  ####### PEGAR CLIENTES FTTH 
  $json_file = @file_get_contents("http://localhost/sisspc/demos/get_inadimplentes.php");
  $json_str = json_decode($json_file, true);
  
  $retorno_bloqueio = "";

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
              <th>Data Vencimento</th>
              <th>Data Bloqueio</th>
              <th>Status</th>
              <th>Serial</th>
            </tr>
          </thead>
          <tbody>
      ';
    ########## REMOVE LISTA ATUAL #########
    $sql_remove_lista_inadimplente = "DELETE FROM blocked_costumer_daily";
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
      $data_vencimento = converteDataOracleMySQL($dados[3]);
      $diasAtraso = intval($dados[4]);
      if ($contrato == $testInv) continue;
      foreach($status_contrato_assinante as $assinante)
      {
        ##### Se tiver ont no banco cadastrado, irá realizar entrar na lista.
        if($assinante[0] == $contrato)
        {
          if(!in_array($assinante[0],$lista))
          {
            $retorno_bloqueio = "";
            ### ADICIONA A LISTA PARA QUE NAO SEJA INSERIDO NOVAMENTE
            array_push($lista,$contrato);
            

    
            ##### SE O STATUS FOR CONECTADO, ELE IRÁ EXECUTAR O BLOQUEIO
            if($assinante[1] == 2) 
              $retorno_bloqueio = send_to_block_unblock($assinante[1],$contrato,$assinante[2]);
            
            if($retorno_bloqueio == 1)
            {
            
              $sql_insert_contrato_inadimplente = "INSERT INTO blocked_costumer_daily (contrato,nome,inadimplente,serial,dataVencimento) VALUES ($contrato,'$nomeCompleto',$assinante[1],'$assinante[2]','$data_vencimento')";
              
              $execute_insert_contrato_inadimplente = mysqli_query($conectar,$sql_insert_contrato_inadimplente);
              $data = date('d/m/Y h:i');
              $html .= "<tr>
                    <td>$contrato</td>
                    <td>$nomeCompleto</td>
                    <td>$data_vencimento</td>
                    <td>$data</td>
                    <td>Bloqueado</td>
                    <td>$assinante[2]</td>
              </tr>";

              #### SALVA LOG DO BLOQUEIO ###
              $sql_log_estado = "INSERT INTO log_estado (contrato,user_id,estado) VALUES ($contrato,0,'Bloqueado Automatico')";
              mysqli_query($conectar,$sql_log_estado);

              ### INSERE INFO NO CPLUS ###
              $comando = "curl http://192.168.80.5/sisspc/demos/push_information_in_cplus.php\?contrato\=$contrato\&assunto\=Contrato%20Bloqueado%20Automaticamente";
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
      send_email("Clientes Bloqueados",$html,"cobranca@vertv.com.br","Inadimplencia",$arquivo);
      
      $sql_remove_lista_inadimplente = "DELETE FROM unblocked_costumer";
      $execute_remove_lista_inadimplente = mysqli_query($conectar,$sql_remove_lista_inadimplente);
    }else{
      send_email("Clientes Bloqueados","<p style='font-weight:bold;'>Nenhum Cliente Bloqueado!</p>","cobranca@vertv.com.br","Inadimplencia");
    }
    
    echo "concluido";
  }else{
    echo "Não Consegui Pegar os Contratos!";
  }

  ########## JOGAR EM ARRAY E INARRAY
?>
