<?php
  include_once "../classes/html_inicio.php";
  if($_SESSION["cadastrar_onu"] == 0) {
    echo '
    <script language= "JavaScript">
      alert("Sem Permissão de Acesso!");
      location.href="../classes/redirecionador_pagina.php";    
    </script>
    ';
  }

  $porta_selecionado = filter_input(INPUT_GET,'porta_atendimento');
  $frame = filter_input(INPUT_GET,'frame');
  $slot = filter_input(INPUT_GET,'slot');
  $pon = filter_input(INPUT_GET,'pon');
  $cto = filter_input(INPUT_GET,'cto');
  $device = filter_input(INPUT_GET,'device');

?>
  <div id="page-wrapper">
    <div class="row">
      <div class="col-md-6 col-md-offset-3">
        <div class="login-panel panel panel-default">
          <div class="panel-heading">
              <h3 class="panel-title">Cadastro de ONT</h3>
          </div>
          <div class="panel-body">
            <form role="form" action="ont_registering.php" method="post">
              <div class="form-group">
                <div class="input-group">
                  <label>Contrato</label> 
                  <input class="form-control" placeholder="Contrato" name="contrato" type="search" autofocus required>
                  <input type='hidden' name='porta_atendimento' value=<?php echo $porta_selecionado;?>>
                  <input type='hidden' name='frame' value=<?php echo $frame;?>>
                  <input type='hidden' name='slot' value=<?php echo $slot;?>>
                  <input type='hidden' name='pon' value=<?php echo $pon;?>>
                  <input type='hidden' name='cto' value=<?php echo $cto;?>>
                  <input type='hidden' name='device' value=<?php echo $device;?>>
                  <span class="input-group-btn">
                      <button class="btn btn-secondary" type="submit">Buscar</button>
                  </span>
                </div>    
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
<?php include_once "../classes/html_fim.php";   ?>