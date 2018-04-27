<!DOCTYPE html>
<html lang="pt">


  <?php
      // Verificador de sessão 
      include "../classes/html_inicio.php"; 

      //session_start(); ?>
  
    <div id="page-wrapper">
      <div class="container">
        <div class="row">
          <div class="col-md-4 col-md-offset-4">
            <div class="login-panel panel panel-default">
              <div class="panel-heading">
                  <h3 class="panel-title">Cadastrar Usuario</h3>
              </div>
              <div class="panel-body">
                <form role="form" action="classes/cadastrar_usuario.php" method="post">
                  <div class="form-group">
                      <input class="form-control" placeholder="Usuario" name="usuario" type="text" autofocus>
                  </div>
                  <div class="form-group">
                      <input class="form-control" placeholder="Nome" name="nome_usuario" type="text">
                  </div>
                  <div class="form-group">
                      <input class="form-control" placeholder="Password" name="password" type="password">
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