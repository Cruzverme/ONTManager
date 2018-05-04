<?php 
include_once "../classes/html_inicio.php";
    include_once "../db/db_config_mysql.php";
?>
    
  <div id="page-wrapper">
    <div class="container">
      <div class="row">
        <div class="col-md-4 col-md-offset-4">
          <div class="login-panel panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Cadastro de ONT</h3>
            </div>
            <div class="panel-body">
              <form role="form" action="../classes/cadastrar.php" method="post">
                <fieldset class="radio-planos">
                    <div class="form-group">
                      <label>Qual Plano</label>
                      <div class="radio">
                          <label>
                              <input type="radio" name="optionsRadios" id="optionsRadios1" value="option1" checked>INTERNET
                          </label>
                      </div>
                      <div class="radio">
                          <label>
                              <input type="radio" name="optionsRadios" id="optionsRadios2" value="option2">IPTV
                          </label>
                      </div>
                      <div class="radio">
                          <label>
                              <input type="radio" name="optionsRadios" id="optionsRadios3" value="Sim">INTERNET | TELEFONE | IPTV
                          </label>
                      </div>
                      <div class="radio">
                          <label>
                              <input type="radio" name="optionsRadios" id="optionsRadios3" value="option3">INTERNET | IPTV
                          </label>
                      </div>
                      <div class="radio">
                          <label>
                              <input type="radio" name="optionsRadios" id="optionsRadios3" value="Sim">INTERNET | TELEFONE
                          </label>
                      </div>
                    </div>
                </fieldset>

                <fieldset>
                    <div class="form-group">
                        <label>Contrato</label> 
                        <input class="form-control" placeholder="Contrato" 
                          name="contrato" type="text" autofocus required>
                    </div>
                    
                    <div class="form-group">
                        <label>Pon MAC</label>                                                
                        <input class="form-control" placeholder="MAC PON" name="serial" type="text" required>
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

                    <div class="form-group">
                      <label>CTO</label>
                      <select class="form-control" name="caixa_atendimento_select">
                        <?php 
                          $sql_consulta_cto = "SELECT DISTINCT caixa_atendimento FROM ctos 
                            WHERE porta_atendimento_disponivel = 0";
                          $executa_query = mysqli_query($conectar,$sql_consulta_cto);
                          while ($cto = mysqli_fetch_array($executa_query, MYSQLI_BOTH)) 
                          {
                            echo "<option value=$cto[caixa_atendimento]>$cto[caixa_atendimento]</option>";
                          }

                          $caixa_selecionada = $_POST['caixa_atendimento_select'];
                        ?>
                      </select>
                    </div>
                    
                    <div class="camposTelefone" style="display:none" >                                   
                      <div class="form-group">
                          <label>Telefone</label>
                          <input class="form-control" placeholder="Telefone" name="numeroTel" type="text" autofocus>
                      </div>

                      
                      <div class="form-group">
                          <label>Senha do Telefone</label>
                          <input class="form-control" placeholder="Senha do Telefone" name="passwordTel" type="text" autofocus>
                      </div>

                      
                      <div class="form-group">
                          <label>Usuario do Telefone</label>
                          <input class="form-control" placeholder="Usuario do Telefone" name="telUser" type="text" autofocus>
                      </div>
                    </div>
                </fieldset>
                <button class="btn btn-lg btn-success btn-block">Avançar</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
<?php include_once "../classes/html_fim.php";   ?>