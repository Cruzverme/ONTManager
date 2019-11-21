<?php

  include_once "../classes/html_inicio.php";
  include_once "../db/db_config_mysql.php";
  include_once "../classes/funcoes.php";

  // if($_SESSION["cadastrar_ip"] == 0) {
  //   echo '
  //   <script language= "JavaScript">
  //     alert("Sem Permissão de Acesso!");
  //     location.href="../classes/redirecionador_pagina.php";    
  //   </script>
  //   ';
  // }
?>
  <div id="page-wrapper">
    <div class="row">
      <div class="col-md-6 col-md-offset-3">
        <div class="login-panel panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title">Cadastro IP</h3>
          </div>
          <div class="panel-body">
            <form role="form" method="post">
              <div class="form-group">
                <span><center>Ip Inicial - Ip Final</center></span>
                <div class='col form-group'>
                  <input type="text" class="col-md-5 " name="ipInicial" id="ipInicial" pattern="^([0-9]{1,3}\.){3}[0-9]{1,3}$" required>
                  <span class='col-md-2'><center>até</center></span>
                  <input type="text" class="col-md-5 " name="ipFinal" id="ipFinal" pattern="^([0-9]{1,3}\.){3}[0-9]{1,3}$" required>
                </div>
                <hr>
              </div>
              <button type='button' class='btn btn-block' onclick='cadastrarIp();'>
                Cadastrar
              </button>
              
            </form>
          </div>

        </div> 
      </div>
    </div> 
    <div class="modal modal-espera"></div>
  </div> 

<?php include_once "../classes/html_fim.php"; ?>