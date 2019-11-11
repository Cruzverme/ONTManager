<?php 
  include_once "../classes/html_inicio.php";
  include_once "../db/db_config_mysql.php";

  if($_SESSION["modificar_onu"] == 0) {
    echo '
    <script language= "JavaScript">
      alert("Sem Permissão de Acesso!");
      location.href="../classes/redirecionador_pagina.php";
    </script>
    ';
  }
  
  $sql_select_list_vlan = "SELECT * FROM vlans";
  $executa_query = mysqli_query($conectar,$sql_select_list_vlan);
?>

  <div id="page-wrapper">
    <div class="row">
      <div class="col-md-10 col-md-offset-1">
        <div class="login-panel panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title">Vlans Cadastradas</h3>
          </div>
          <div class="panel-body">
            <form action="#" method="post">
              <div class='table-responsive'>
                <table class='table table-hover display' id='tabelaSinais' data-link='row'>
                  <thead>
                    <tr>
                      <th>Vlan</th>
                      <th>Descrição</th>
                      <th>Opções</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php 
                      while ($vlan = mysqli_fetch_array($executa_query, MYSQLI_BOTH))
                      { 
                        echo "
                          <tr id='linha_$vlan[vlan]' class='linha_vlan'>
                            <td class='$vlan[vlan]'>
                              <div id='vlan'>$vlan[vlan]</div>
                            </td>
                            <td>
                              <label>$vlan[descricao]</label>
                            </td>
                            <td>
                              <div class='btn-group' role='group' aria-label='Botões de Controle'>
                                <button class='btn' type='button' id='$vlan[vlan]' data-toggle='modal' data-target='.bd-example-modal-sm' onClick='showModalVlan(this.id)'>Listar Assinantes</button>
                                <button class='btn' type='button' id='$vlan[vlan]' onClick='vlan_association(this.id)'>Associar Novo</button>
                                <button class='btn' type='button' id='$vlan[vlan]' onClick='vlan_dissociation(this.id)'>Desassociar</button>
                              </div>
                            </td>
                          </tr>
                        ";
                      }
                    ?>
                  </tbody>
                </table>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div id="listaClientesVlanModal" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
      <div class="modal-content">
        <div id="listaCostumerDetails" style="overflow:auto;height:200px;"></div>
      </div>
    </div>
  </div>
<?php include_once "../classes/html_fim.php";   ?>