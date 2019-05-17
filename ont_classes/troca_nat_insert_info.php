<?php
  include_once "../classes/html_inicio.php";
  include_once "../db/db_config_mysql.php";
  include_once "../classes/funcoes.php";

  if($_SESSION["cadastrar_onu"] == 0) {
    echo '
    <script language= "JavaScript">
      alert("Sem Permissão de Acesso!");
      location.href="../classes/redirecionador_pagina.php";    
    </script>
    ';
  }

  $area = filter_input(INPUT_POST,'olt');
  $sql = "select deviceName from pon where pon_id = $area";
  $executaSQL = mysqli_query($conectar,$sql);
  $olt = mysqli_fetch_array($executaSQL,MYSQLI_ASSOC);

?>
  <div id="page-wrapper">
    <div class="container">
      <div class="row">
        <div class="col-md-4 col-md-offset-4">
          <div class="login-panel panel panel-default">
            <div class="panel-heading">
              <h3 class="panel-title">Mudança de NAT para área <?php echo "$olt[deviceName]" ?> </h3>
            </div>
            <div class="panel-body">
              <form action="../classes/redirect_port.php" method="post">
                <div class="form-group">
                  <label for="quantidade_clientes">Quantidade Clientes</label>
                  <input type="number" name="qtd_clientes" id="quantidade_clientes" class="form-control" value=0>
                </div>
                <div class='btn-group' role='group' aria-label="Botões de Efetivação de NAT">
                  <button class="btn btn-listar-clientes" type="button"  onclick="listUserForDnat('<?php echo $area; ?>')">Listar Clientes</button>
                  <button class="btn btn-efetua-nat" disabled>Realizar DNAT</button>
                  <button class="btn btn-cancela-lista" type="button" onclick="ativar_lista()" disabled>Cancelar Lista Clientes</button>
                </div>
                <div>
                  <div id="listaClientes" class=form-group></div>
                </div>
              </form>
              
              
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="modal modal-espera"><!-- Place at bottom of page --></div>
<?php include_once "../classes/html_fim.php";   ?>