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
              <h3 class="panel-title">Ativa|Desativa Cliente</h3>
            </div>
            <div class="panel-body">
              <form role="form" action="../classes/desativa_ativa.php" method="post">
                <div class="form-group">
                  <label>Contrato</label> 
                  <input class="form-control" placeholder="Contrato" name="contrato" type="text" value='<?php echo $contrato; ?>' autofocus readonly>
                </div>
                
                <div class="form-group">
                  <label>Pon MAC</label>                                                
                  <select class="form-control" name="serial">
                    <?php 
                      $sql_consulta_serial = "SELECT serial,pacote,status FROM ont
                        WHERE contrato = $contrato";
                      $executa_query = mysqli_query($conectar,$sql_consulta_serial);
                      while ($ont = mysqli_fetch_array($executa_query, MYSQLI_BOTH))
                      {
                        echo "<option value=$ont[serial]>$ont[serial]</option>";
                        $pacote = $ont['pacote'];
                        $status = $ont['status'];
                      }                      
                    ?>
                  </select>
                  <input name="status" type='hidden' value=<?php echo $status; ?> />
                  <input name="contrato" type='hidden' value=<?php echo $contrato; ?> />
                </div>
                
                <?php 
                if($status == 1 ) 
                {
                  echo  '<button class="btn btn-lg btn-success btn-block">Ativar</button>';
                }else{
                  echo  '<button class="btn btn-lg btn-success btn-block">Desativar</button>';
                }
                ?>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
<?php include_once "../classes/html_fim.php";   ?>