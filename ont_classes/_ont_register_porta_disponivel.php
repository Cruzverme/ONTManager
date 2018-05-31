<?php 
  include "../classes/html_inicio.php";

  if($_SESSION["cadastrar_onu"] == 0) {
    echo '
    <script language= "JavaScript">
      alert("Sem Permissão de Acesso!");
      location.href="../classes/redirecionador_pagina.php";    
    </script>
    ';
  }
  
  #capturar mensagem
  if(isset($_SESSION['portas']) && !empty($_SESSION['portas']))
  {
      print "<script>alert(\"{$_SESSION['portas']}\")</script>";
      print_r($_SESSION['portas']);
      unset( $_SESSION['portas'] );
  }

?>

<?php ?>

  <div id="page-wrapper">

  <div class="container">
    <div class="row">
      <div class="col-md-4 col-md-offset-4">
        <div class="login-panel panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title">Selecione a Porta de Atendimento Disponivel</h3>
          </div>

          <table class="table table-hover">
            <tbody>
                <?php 
                  require_once "../db/db_config_mysql.php";

                  if (!mysqli_connect_errno())
                  {
                    if( isset($_GET["caixa_atendimento_select"]) && isset($_GET["serial"]))
                    {
                      $caixa_selecionada = $_GET["caixa_atendimento_select"];
                      $serial = $_GET["serial"];
                      
                      $sql_consulta_cto = "SELECT porta_atendimento, porta_atendimento_disponivel 
                                            FROM ctos WHERE caixa_atendimento = '$caixa_selecionada'";
                      
                      $executa_query = mysqli_query($conectar,$sql_consulta_cto);
                      
                      while($porta_disponivel = mysqli_fetch_array($executa_query, MYSQLI_BOTH)) 
                      {
                        if($porta_disponivel['porta_atendimento_disponivel'] == 0)
                        {
                          echo "<tr class='porta'>
                                  <td>$porta_disponivel[porta_atendimento]</td>
                                  <td style='visibility: hidden';>$serial</td>
                                  <td style='visibility: hidden';>$caixa_selecionada</td>
                                </tr>";
                        }
                      }
                      mysqli_close($conectar);
                    }else{
                      $_SESSION['menssagem'] = "Campos Faltando!";
                      mysqli_close($conectar);
                    }
                  }else{
                    $_SESSION['menssagem'] = "Não Consegui Contato com Servidor!";
                    header('Location: ../usuario_new.php');
                    mysqli_close($conectar);
                    exit;
                  }
                ?>  
            </tbody>
          </table>

          


        </div>
      </div>
    </div>
  </div>
<?php ?>

<?php include "../classes/html_fim.php";?>