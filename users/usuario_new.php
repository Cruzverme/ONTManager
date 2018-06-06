  <?php
      // Verificador de sessão 
    include "../classes/html_inicio.php"; 
    
    if($_SESSION["cadastrar_usuario"] == 0) {
      echo '
      <script language= "JavaScript">
        alert("Sem Permissão de Acesso!");
        location.href="../classes/redirecionador_pagina.php";    
      </script>
      ';
    }

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
                <form role="form" action="../classes/cadastrar_usuario.php" method="post">
                  <div class="form-group">
                      <label>Usuario</label>
                      <input class="form-control" placeholder="Usuario" name="usuario" type="text" autofocus required>
                  </div>
                  <div class="form-group">
                      <label>Nome</label>
                      <input class="form-control" placeholder="Nome" name="nome_usuario" type="text" required>
                  </div>
                  <div class="form-group">
                      <label>Senha</label>
                      <input class="form-control" placeholder="Password" name="password" type="password" required>
                  </div>
                  <div class="form-group">
                    <input name="nivel" value=1 type="checkbox"> Permissão de Administrador<br/>
                  </div>
                  
                  <div class="camposPermissao" style="display:visible" >                                   
                    <div class="form-group">
                      <fieldset>
                        <legend>Permissões Personalizadas</legend>
                        <input name="personalizada1" value=1 type="checkbox"> Cadastrar ONT<br/>
                        <input name="personalizada2" value=2 type="checkbox"> Modificar ONT<br/>
                        <input name="personalizada3" value=3 type="checkbox"> Deletar ONT<br/>
                        <input name="personalizada4" value=4 type="checkbox"> Cadastrar CTO<br/>
                        <input name="personalizada5" value=5 type="checkbox"> Desabilitar e Habilitar ONT<br/>
                        <input name="personalizada6" value=6 type="checkbox"> Cadastrar Equipamento<br/>
                        <input name="personalizada7" value=7 type="checkbox"> Cadastrar OLT<br/>
                        <input name="personalizada8" value=8 type="checkbox"> Cadastrar Velocidade<br/>
                        <input name="personalizada9" value=9 type="checkbox"> Cadastrar Usuários<br/>
                        <input name="personalizada10" value=10 type="checkbox"> Alterar Mac de ONT<br/>
                        <input name="personalizado11" value=11 type="checkbox"> Consulta de ONU <br/>
                        <input name="personalizado12" value=11 type="checkbox"> Consulta de CTO <br/>
                        <input name="personalizado12" value=11 type="checkbox"> Remover de CTO <br/>
                        <input name="personalizado12" value=11 type="checkbox"> Remover de OLT <br/>
                      </fieldset>
                    </div>
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