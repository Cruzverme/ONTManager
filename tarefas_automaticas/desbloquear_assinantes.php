<?php 

  ######## este script apenas olha no ERP quem está inadimplente e renova tabela do banco 
  ######## e bloqueia quem está inadimplente

  include "/var/www/html/ontManager/db/db_config_mysql.php";
  include "/var/www/html/ontManager/db/db_config_radius.php";
  include "/var/www/html/ontManager/classes/funcoes.php";

  ####### PEGAR CLIENTES FTTH 
  $json_file = @file_get_contents("http://localhost/sisspc/demos/get_pendente_pagamento.php");
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
              <th>Data Bloqueio</th>
              <th>Status</th>
              <th>Serial</th>
            </tr>
          </thead>
          <tbody>
      ';
    
    #### contrato dos assinantes
    $sql_assinantes_cadastrados_bloqueados = "SELECT contrato,inadimplente,serial,nome from blocked_costumer where inadimplente = 1";
    $execute_contrato = mysqli_query($conectar,$sql_assinantes_cadastrados_bloqueados);
    $status_contrato_assinante = mysqli_fetch_all($execute_contrato);
    
    $lista = array();
    $cliente_ativo = array();

    #### assinantes que estão indimplentes no cplus contrato | nome
    $listaAssinantes = $json_str['dados'];
    

    foreach($listaAssinantes as $dados)
      array_push($lista,$dados[0]);

    foreach ($lista as $dados) 
    {
      foreach($status_contrato_assinante as $assinante)
      {  
        if(!in_array($assinante[0],$lista) AND !in_array($assinante[0],$cliente_ativo))
        {
          array_push($cliente_ativo,$assinante[0]); // insere contrato no array para não repetir

          $retorno_bloqueio = "";
          ##### SE O STATUS FOR CONECTADO, ELE IRÁ EXECUTAR O BLOQUEIO
          if($assinante[1] == 1)
            $retorno_bloqueio = send_to_block_unblock($assinante[1],$assinante[0],$assinante[2]);
          
          if($retorno_bloqueio == 1)
          {
            $sql_insert_contrato_inadimplente = "INSERT INTO unblocked_costumer (contrato,nome,status,serial) VALUES ($assinante[0],'$assinante[3]',2,'$assinante[2]')";
            $execute_insert_contrato_inadimplente = mysqli_query($conectar,$sql_insert_contrato_inadimplente);

            $data = date('d/m/Y h:i');
            $html .= "<tr>
                  <td>$assinante[0]</td>
                  <td>$assinante[3]</td>
                  <td>$data</td>
                  <td>Desbloqueado</td>
                  <td>$assinante[2]</td>
            </tr>";

            #### SALVA LOG DO BLOQUEIO ###
            $sql_log_estado = "INSERT INTO log_estado (contrato,user_id,estado) VALUES ($assinante[0],0,'Desloqueado Automatico')";
            mysqli_query($conectar,$sql_log_estado);

            $arquivo = "OK";
          }
        }
      }
    }
    $html .= '
        </tbody>
      </table>
    ';

    send_email("Clientes Desbloqueados",$html,"cobranca@vertv.com.br","TI");
    echo "concluido";
  }else{
    echo "Não Consegui Pegar os Contratos!";
  }
?>
