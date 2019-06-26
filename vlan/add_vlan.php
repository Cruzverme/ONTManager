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
  
  $olt = filter_input(INPUT_POST,"olt");
?>
  <div id="page-wrapper">
    <!-- <div class="container"> -->
      <div class="row">
        <div class="col-md-10 col-md-offset-1">
          <div class="login-panel panel panel-default">
            <div class="panel-heading">
              <h3 class="panel-title">Cadastro de Vlan</h3>
            </div>
            <div class="panel-body">
              <form action="../classes/create_vlan.php" method="post">
                <div class='form-group'>
                  <label for="device">OLT</label>
                  <input type="text" class='form-control' name="deviceName" id="device" readonly value=<?php echo $olt; ?> />
                </div>

                <div class='form-group'>
                  <label for="vlan">Numero da Vlan</label>
                  <input type="number" class='form-control' name="nVlan" id="vlan"/>
                </div>

                <div class='form-group'>
                  <label for="descricao">Descrição da Vlan</label>
                  <input type="text" class='form-control' name="aliasVlan" id="descricao"/>
                </div>

                <div class='form-group'>
                  <button class='btn form-control'>Criar Vlan</button>
                </div>
              </form>  
            </div>
          </div>
        </div>
      </div>
    <!-- </div> -->
  </div>



<?php include_once "../classes/html_fim.php";   ?>