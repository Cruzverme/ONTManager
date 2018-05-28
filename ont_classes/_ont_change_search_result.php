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
                
              <label>Qual Plano</label>
              <div class="radio">
                  <label>
                      <input type="radio" name="optionsRadios" id="optionsRadios1" value="VAS_Internet" checked>INTERNET
                  </label>
              </div>
              <div class="radio">
                  <label>
                      <input type="radio" name="optionsRadios" id="optionsRadios2" value="VAS_IPTV">IPTV
                  </label>
              </div>
              
              <div class="radio">
                  <label>
                      <input type="radio" name="optionsRadios" id="optionsRadios3" value="VAS_Internet-IPTV">INTERNET | IPTV
                  </label>
              </div>
              <div class="radio">
                  <label>
                      <input type="radio" name="optionsRadios" id="optionsRadios3" value="VAS_Internet-VoIP">INTERNET | TELEFONE
                  </label>
              </div>
              <div class="radio">
                  <label>
                      <input type="radio" name="optionsRadios" id="optionsRadios3" value="VAS_Internet-VoIP-IPTV">INTERNET | TELEFONE | IPTV
                  </label>
              </div>
            
              
              
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
                  <label>Pacote</label>
                  <select class="form-control" name="pacote">
                    <?php 
                      $sql_lista_velocidades = "SELECT nome,nomenclatura_velocidade FROM planos";
                      $executa_query = mysqli_query($conectar,$sql_lista_velocidades);
                      while ($listaPlanos = mysqli_fetch_array($executa_query, MYSQLI_BOTH)) 
                      {
                        echo "<option value='$listaPlanos[nomenclatura_velocidade]'>$listaPlanos[nome]</option>"; 
                      }
                      mysqli_free_result($executa_query);                                                
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