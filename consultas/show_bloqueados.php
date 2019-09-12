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
            <center> <button class='btn' onclick='verificar_inadimplente_erp();'>Atualizar Lista Inconsistente</button> </center>
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
                  $sql_tabela_bloqueados = "select contrato,nome,inadimplente,serial from blocked_costumer";
                  $execute_tabela_bloqueados = mysqli_query($conectar,$sql_tabela_bloqueados);
                  $assinantes_inadimplentes = mysqli_fetch_all($execute_tabela_bloqueados);

                  foreach ($assinantes_inadimplentes as $assinante) {
                    $serial = "$assinante[3]";
                    $contrato_assinante = "$assinante[0]";
                    if($assinante[2] == 2)
                    {
                      $inicio = "<tr style='background-color:#a42423;'>";
                      $botao = "<button class='btn' onclick=bloquear($contrato_assinante,'$serial');>Bloquear</button>";
                      $status = "Conectado";
                    }else{
                      $inicio = "<tr>";
                      $botao = "<button class='btn' onclick=desbloquear($contrato_assinante,'$serial');>Desbloquear</button>";
                      $status = "Bloqueado";
                    }
                    $data = date('d/m/Y h:i');
                    echo "$inicio
                            <td>$assinante[0]</td>
                            <td>$assinante[1]</td>
                            <td>$status</td>
                            <td>$assinante[3]</td>
                            <td>$data</td>
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
  <div class="modal modal-espera"></div>
</div> <!-- fim page -->

<?php include_once "../classes/html_fim.php"; ?>
