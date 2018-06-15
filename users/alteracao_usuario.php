<?php 
    
  include "../classes/html_inicio.php"; 
  include_once "../db/db_config_mysql.php";
  
   if($_SESSION["alterar_usuario"] == 0) 
   {
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
        <div class="col-md-4 col-md-offset-2">
          <div class="login-panel panel panel-default">
            <div class="panel-heading">
              <h3 class="panel-title">Lista de Usuários</h3>
            </div>
            <div class="panel-body">
            <form method="post">
              <div class="form-group">         
                <div class='row'>
                  <div class='col-lg-16'>
                    <div class='table-responsive'>
                      <table class='table-user table'>
                        <thead>
                          <tr>
                            <th>NOME</th>
                            <th>USUARIO</th>
                          </tr>
                        </thead>
                        <tbody> 
                          <?php 
                          $select_all_users = "SELECT usuario,nome,senha FROM usuarios ";
                          
                          $execute_all_users = mysqli_query($conectar,$select_all_users);
                          
                          while($user = mysqli_fetch_array($execute_all_users, MYSQLI_BOTH))
                          {
                            $usuario = $user['usuario'];
                            $nome = $user['nome'];

                            echo "
                              <tr class=usuarios data-href=_alterar_permissao_usuario.php?user=$usuario>
                                <td>$usuario</td>
                                <td>$nome</td>
                              </tr>
                            ";       
                          }
                          ?>
                        </tbody>
                      </table>
                    </div>
                  </div>  
                </div>
            </form>
          </div><!-- fim panel -->
        </div>
      </div><!-- fim row -->
    </div>
  </div> <!-- fim pagewrapper -->

<?php include "../classes/html_fim.php"; ?>