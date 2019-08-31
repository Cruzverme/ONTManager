<?php 

  include_once "../classes/html_inicio.php";
  include_once "../db/db_config_mysql.php";

  // if($_SESSION["cadastrar_onu"] == 0) {
  //   echo '
  //   <script language= "JavaScript">
  //     alert("Sem Permissão de Acesso!");
  //     location.href="../classes/redirecionador_pagina.php";    
  //   </script>
  //   ';
  // }

  ######### VERIFICAR PARA COLOCARO BOTAO BLOQUEADO OU DESBLOQUEADO
  // if($assinante[1] != 2) #### se status do cliente no ontmanager for diferente de 2, ele já se encontra bloqueado
  //         {
  //           ####### pega os clientes que já estão na tabela bloqueada
  //           $sql_verifica_contrato_bloqueado = "SELECT contrato FROM blocked_costumer where contrato = $contrato";
  //           $execute_sql_verifica_contrato_bloqueado = mysqli_query($conectar, $sql_verifica_contrato_bloqueado);
  //           $lista_contrato_bloqueado = mysqli_fetch_all($execute_sql_verifica_contrato_bloqueado);
  //           echo "<br> EU NAO PAGO NAO ! - $contrato <br>";
  //         }else{
  //           echo "<br> EU PAGO ! - $contrato <br>";
  //         }
?>


<div id="page-wrapper">
  <!-- <div class=""> -->
    <div class="row">
      <div class="col-md-12 col-md-offset-0">
        <div class="login-panel panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title">Consulta de Informações de ONT</h3>
          </div>
          <div class="panel-body">
            <div class='table-responsive'>
              <table class='table table-hover display' id='tabelaSinais' data-link='row'>
                <thead>
                  <tr>
                    <th>Contrato</th>
                    <th>Nome</th>
                    <th>Status</th>
                    <th class="action">Ações</th>
                  </tr>
                </thead>
                <tbody>
                  <?php 
                    $sql_tabela_bloqueados = "select contrato,nome,inadimplente from blocked_costumer";
                    $execute_tabela_bloqueados = mysqli_query($conectar,$sql_tabela_bloqueados);
                    $assinantes_inadimplentes = mysqli_fetch_all($execute_tabela_bloqueados);

                    foreach ($assinantes_inadimplentes as $assinante) {
                      if($assinante[2] == 2)
                      {
                        $inicio = "<tr style='background-color:#a42423;'>";
                        $botao = "<button class='btn' onclick='bloquear($assinante[0])'>Bloquear</button>";
                        $status = "Conectado";
                      }else{
                        $inicio = "<tr'>";
                        $botao = "<button class='btn' onclick='desbloquear($assinante[0])'>Desbloquear</button>";
                        $status = "Bloqueado";
                      }
                        
                      echo "$inicio
                              <td>$assinante[0]</td>
                              <td>$assinante[1]</td>
                              <td>$status</td>
                              <td>
                                $botao
                              </td>
                           </tr>";
                      
                    }
                  
                  ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  <!-- </div>  -->
</div> <!-- fim page -->

<?php include_once "../classes/html_fim.php"; ?>