<?php include "../classes/html_inicio.php"; ?>


    <div id="page-wrapper">
      <div class="container">
        <div class="row">
          <div class="col-md-4 col-md-offset-4">
            <div class="login-panel panel panel-default">
              <div class="panel-heading">
                  <h3 class="panel-title">Alterar Senha</h3>
              </div>
              <div class="panel-body">
                <form role="form" action="../classes/edit_usuario_save.php" method="post">
                  <div class="form-group">
                      <input class="form-control" placeholder="Senha Atual" name="senha" type="password" autofocus>
                  </div>
                  <div class="form-group">
                      <input class="form-control" placeholder="Nova Senha" name="nova_senha" type="password">
                  </div>
                  <div class="form-group">
                      <input class="form-control" placeholder="Repita a Nova Senha" name="repete_nova_senha" type="password">
                  </div>
                  <button class="btn btn-lg btn-success btn-block">Alterar</button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  
<?php include "../classes/html_fim.php";?>
