<?php include_once "../classes/html_inicio.php";?>
  
  <?php 
    include "../db/db_config_mysql.php";
    $contrato = $_POST['contrato'];?>
  <div id="page-wrapper">

    <div class="container">
      <div class="row">
        <div class="col-md-4 col-md-offset-4">
          <div class="login-panel panel panel-default">
            <div class="panel-heading">
              <h3 class="panel-title">Mudança de ONT</h3>
            </div>
            <div class="panel-body">
              <form role="form" action="../classes/alterar.php" method="post">
                <div class="form-group">
                  <label>Contrato</label> 
                  <input class="form-control" placeholder="Contrato" name="contrato" type="text" value='<?php echo $contrato; ?>' autofocus readonly>
                </div>
                
                <div class="form-group">
                  <label>Pon MAC</label>                                                
                  <select class="form-control" name="serial">
                    <?php 
                      $sql_consulta_serial = "SELECT serial,pacote FROM ont
                        WHERE contrato = $contrato ";
                      $executa_query = mysqli_query($conectar,$sql_consulta_serial);
                      while ($ont = mysqli_fetch_array($executa_query, MYSQLI_BOTH)) 
                      {
                        echo "<option value=$ont[serial]>$ont[serial]</option>";
                        $pacote = $ont['pacote'];
                      }
                      
                    ?>
                  </select>
                </div>
                
                <div class="form-group">
                
                  <?php include "../classes/listaPlanos.php" ?>
                  <label>Pacote</label>
                  <select class="form-control" name="pacote">
                    <?php 
                      foreach($listaPlanosInternet as $planoInternet) 
                      {
                        echo "<option value='$planoInternet'>$planoInternet</option>"; 
                      }                                                
                    ?>
                  </select>
                </div>                                                                         
                <div class='pull-left'>Plano Atual: <?php echo $pacote; ?> </div><br>
                <button class="btn btn-lg btn-success btn-block">Alterar</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
<?php include_once "../classes/html_fim.php";   ?>