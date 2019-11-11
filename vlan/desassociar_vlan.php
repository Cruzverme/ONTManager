<?php 
  include_once "../classes/html_inicio.php";
  include_once "../db/db_config_mysql.php";

  if($_SESSION["modificar_onu"] == 0) {
    echo '
    <script language= "JavaScript">
      alert("Sem Permissão de Acesso!");
      location.href="../classes/redirecionador_pagina.php";
    </script>
    ';
  }
  
  $vlan = filter_input(INPUT_GET,"vlan");

  $sql = "SELECT contrato FROM ont WHERE vlan_id=$vlan";
  $execute_sql = mysqli_query($conectar,$sql);
  $listaClientes = array();
  
  while($row = mysqli_fetch_array($execute_sql,MYSQLI_BOTH))
  {
    array_push($listaClientes,$row[0]);
  }

?>

<div id="page-wrapper">
  <div class="row">
    <div class="col-md-10 col-md-offset-1">
      <div class="login-panel panel panel-default">
        <div class="panel-heading">
          <h3 class="panel-title">Desassociar de Vlan</h3>
        </div>
        <div class="panel-body">
          <form action="../classes/vlan_desassociation.php" method="post">
            <div class="form-group">
              <label for="cliente_vlan">Selecione o contrato na VLAN <?php echo $vlan; ?></label>
              <select name="clientes_vlan" id="cliente_vlan" class='form-control'>
                <?php 
                  if(!sizeof($listaClientes) < 1)
                  
                    foreach ($listaClientes as $contrato) {
                      echo "<option> $contrato </option>";
                    }
                  else
                    echo "<option> Não Há Assinantes nesta Vlan</option>";
                ?>
              </select>
              <input type="hidden" name="nVlan" value=<?php echo "$vlan"; ?> />
            </div>
            <div class="form-group">
              <button type='submit' class='btn form-control'>Dessasociar</button>
            </div>
            
          </form>
        </div>
      </div>
    </div>
  </div>
</div>