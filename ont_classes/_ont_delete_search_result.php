<?php 
  include_once "../classes/html_inicio.php";
  include_once "../classes/funcoes.php";

  if($_SESSION["deletar_onu"] == 0) {
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
    $contrato = filter_input(INPUT_POST,"contrato");
    
    if(checar_contrato($contrato) == null)
    {
      echo '
        <script language= "JavaScript">
          alert("Contrato Inexistente ou Cancelado");
          location.href="../ont_classes/ont_delete.php";
        </script>
      ';
    }
    $options = "";

    $sql_consulta_serial = "SELECT serial FROM ont WHERE contrato = $contrato ";
    $executa_query = mysqli_query($conectar,$sql_consulta_serial);

    while($ont=mysqli_fetch_array($executa_query,MYSQLI_ASSOC))
    {
      $serial = $ont['serial'];
      $options = "$options <option value=$serial>$serial</option>";
    }

    if(empty($serial))
    {
      echo "Contrato Sem Cadastro";
      
    }
    // print_r($a); echo "<br>";
    if(empty($serial))
    {
      mysqli_close($conectar);
      echo '
        <script language= "JavaScript">
          alert("Não Há Equipamento!");
          location.href="ont_delete.php";
        </script>
      ';
    }
  ?>

  <div id="page-wrapper">
    <div class="row">
      <div class="col-md-6 col-md-offset-3">
        <div class="login-panel panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title">Remoção de ONT</h3>
          </div>
          <div class="panel-body">
            <form role="form" >
              <div class="form-group">
                <label for="contrato">Contrato</label> 
                <input id="contrato" class="form-control" placeholder="Contrato" name="contrato" type="text" value='<?php echo $contrato; ?>' autofocus readonly>
              </div>
              
              <div class="form-group">
                <label for="serial">Pon MAC</label>                                                
                <select id="serial" class="form-control" name="serial">
                  <?php 
                    echo "$options";
                  ?>
                </select>
              </div>
              <?php if(!empty($serial)) echo'<button class="btn btn-lg btn-success btn-block" onclick=deletar(); type=button>Deletar</button>'; ?>
              
            </form>
          </div>
        </div>
      </div>
    </div>
    <div class="modal modal-espera"></div>
  </div>
<?php include_once "../classes/html_fim.php";   ?>