<?php
      // Verificador de sessão 
      include "../classes/html_inicio.php"; 
?>
  
    <div id="page-wrapper">
      <div class="container">
        <div class="row">
          <div class="col-md-4 col-md-offset-4">
            <div class="login-panel panel panel-default">
              <div class="panel-heading">
                  <h3 class="panel-title">Cadastrar Usuario</h3>
              </div>
              <div class="panel-body">
                <form role="form" action="../classes/cadastrar_equipamento.php" method="post">
                  <div class="form-group">
                      <label>Modelo</label>
                      <input class="form-control" placeholder="Modelo" name="modelo" type="text" autofocus required>
                  </div>                  
                  <button class="btn btn-lg btn-success btn-block">Cadastrar</button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

<?php include_once "../classes/html_fim.php";//session_destroy(); ?>