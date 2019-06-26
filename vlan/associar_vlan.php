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
  
  $vlan = filter_input(INPUT_GET,"vlan");
?>
  <div id="page-wrapper">
    <!-- <div class="container"> -->
      <div class="row">
        <div class="col-md-10 col-md-offset-1">
          <div class="login-panel panel panel-default">
            <div class="panel-heading">
              <h3 class="panel-title">Associar de Vlan</h3>
            </div>
            <div class="panel-body">
              <form action="#" method="post">
                <div class='form-group'>
                  <label for="device">Digite os Contratos</label>
                  <input type="text" class='form-control' placeholder="contrato1,contrato2,contrato3,..." name="contrato" id="contratoID" />
                </div>

                <div class='form-group'>
                  <label for="vlan">Numero da Vlan</label>
                  <input type="number" class='form-control' name="nVlan" id="vlan" value=<?php echo "$vlan"; ?> readonly/>
                </div>

                <div class='form-group'>
                  <div class='col-md-6'>
                    <button class='btn form-control' type='button' id='add_association'>Associar Vlan</button>
                  </div>
                  <div class='col-md-6'>
                    <button class='btn form-control' type='button' onclick="javascript:history.back()">Voltar</button>
                  </div>
                </div>
              </form>  
            </div>
          </div> <!-- fim panel -->
          
          <div class="panel panel_associados" style='visibility: hidden'>
            <div class="panel-heading">Resumo de Associação</div>
            <div class="panel-body">
              <form action="../classes/vlan_association.php" method="post">
                <div id="contrato" style="overflow:auto;height:200px;"></div>
                <input type="hidden" name="nVlan" value=<?php echo "$vlan"; ?> />
                <button type="submit" class="btn form-control">Confirmar</button>
              </form>
            </div>
          </div>

        </div>
      </div>
    <!-- </div> -->
  </div>



<?php include_once "../classes/html_fim.php";   ?>