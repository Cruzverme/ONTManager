<?php 
  
  include_once "../classes/html_inicio.php";
  
  if($_SESSION["alterar_macONT"] == 0) 
  {
    echo '
    <script language= "JavaScript">
      alert("Sem Permissão de Acesso!");
      location.href="../classes/redirecionador_pagina.php";
    </script>
    ';
  }
?>
  <?php 
    include "../db/db_config_mysql.php";
    $contrato = $_POST['contrato'];?>
  <div id="page-wrapper">

    <div class="container">
      <div class="row">
        <div class="col-md-4 col-md-offset-4">
          <div class="login-panel panel panel-default">
            <div class="panel-heading">
              <h3 class="panel-title">Alteração de MAC</h3>
            </div>
            <div class="panel-body">
              <form role="form" action="../classes/trocar_mac.php" method="post">
                <div class="form-group">
                  <label>Contrato</label> 
                  <input class="form-control" placeholder="Contrato" name="contrato" type="text" value='<?php echo $contrato; ?>' autofocus readonly>
                </div>
                <div class="form-group">
                  <label>Pon MAC</label>
                  <select class="form-control" name="serial">
                    <?php
                      $sql_consulta_serial = "SELECT serial FROM ont
                        WHERE contrato = $contrato";
                      $executa_query = mysqli_query($conectar,$sql_consulta_serial);
                      while ($ont = mysqli_fetch_array($executa_query, MYSQLI_BOTH))
                      {
                        echo "<option value=$ont[serial]>$ont[serial]</option>";
                        $serial = $ont['serial'];
                      }
                      if(empty($serial))
                      {
                        mysqli_close($conectar);
                        echo '
                          <script language= "JavaScript">
                            alert("Não Há Equipamento!");
                            location.href="alterar_mac_ont.php";
                          </script>
                          ';
                      }
                    ?>
                  </select>
                  <input name="contrato" type='hidden' value=<?php echo $contrato; ?> />
                </div>
                <div class="form-group">
                  <label>Novo MAC</label> 
                  <input class="form-control" placeholder="MAC" name="novoSerial" minlength=16 maxlength=16 type="text" autofocus>
                </div>
                
                <button class="btn btn-lg btn-success btn-block">Alterar</button>
                
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
<?php include_once "../classes/html_fim.php";   ?>