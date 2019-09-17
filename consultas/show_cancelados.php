<?php 

  include_once "../classes/html_inicio.php";
  include_once "../db/db_config_mysql.php";

  if($_SESSION["desativar_ativar_onu"] == 0) {
    echo '
    <script language= "JavaScript">
      alert("Sem Permissão de Acesso!");
      location.href="../classes/redirecionador_pagina.php";
    </script>
    ';
  }

?>

<div id="page-wrapper">
  <div class="row">
    <div class="col-md-12 col-md-offset-0">
      <div class="login-panel panel panel-default">
        <div class="panel-heading">
          <h3 class="panel-title">Consulta de Informações de ONT</h3>
        </div>
 
        <div class="panel-body">
          <div class='table-responsive'>
            <center> <button class='btn' onclick='verificar_cancelados_erp();'>Atualizar Lista Inconsistente de Cancelados</button> </center>
            <table class='table table-hover display' id='tabelaSinais' data-link='row'>
              <thead>
                <tr>
                  <th>Contrato</th>
                  <th>Nome</th>
                  <th>Status</th>
                  <th>Serial</th>
                  <th>Atualizado em</th>
                  <th class="action">Ações</th>
                </tr>
              </thead>
              <tbody>
                <?php 
                  $sql_tabela_cancelados = "select contrato,nome,status,serial,cancelado_em from canceled_costumer";
                  $execute_tabela_cancelados = mysqli_query($conectar,$sql_tabela_cancelados);
                  $assinantes_cancelados = mysqli_fetch_all($execute_tabela_cancelados);

                  foreach ($assinantes_cancelados as $assinante) {
                    $serial = "$assinante[3]";
                    $contrato_assinante = "$assinante[0]";
                    if($assinante[2] == 2)
                    {
                      $inicio = "<tr style='background-color:#a42423;'>";
                      $status = "Conectado";
                    }else{
                      $inicio = "<tr>";
                      $status = "Bloqueado";
                    }

                    echo "$inicio
                            <td>$assinante[0]</td>
                            <td>$assinante[1]</td>
                            <td>$status</td>
                            <td>$assinante[3]</td>
                            <td>$assinante[4]</td>
                            <td>
                              <button class='btn' onclick=cancelar($contrato_assinante,'$serial');>Cancelar</button>
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
  <div class="modal modal-espera"></div>
</div> <!-- fim page -->

<?php include_once "../classes/html_fim.php"; ?>
