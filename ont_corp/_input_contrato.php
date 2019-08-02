<?php
  include_once "../classes/html_inicio.php";
  include_once "../db/db_config_mysql.php";

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

  $sql_lista_vlans = "SELECT vlan,descricao,contrato FROM vlans";
  $executa_query = mysqli_query($conectar,$sql_lista_vlans);
?>
  <div id="page-wrapper">
    <div class="container">
      <div class="row">
        <div class="col-md-4 col-md-offset-4">
          <div class="login-panel panel panel-default">
            <div class="panel-heading">
              <h3 class="panel-title">Cadastro de L2L ONT</h3>
            </div>
            <div class="panel-body">
              <form role="form" action="ont_corp_register.php" method="post">
                <div class="form-group">  
                  <input class="form-control" placeholder="Contrato" name="contrato" type="search" autofocus required>
                </div>
                
                <div class="form-group">
                  <input class="form-control" type="text" placeholder="Designação do Circuito" name="designacao" 
                  pattern="[a-zA-Z-]+[0-9-]+[0-9]+"  title="Designação deve ser no formato SIGLA-Contrato-VLAN e conter no mínimo 3 caracteres na SIGLA" required>
                </div>
                
                <div class=form-group>
                  <select class="form-control" name="vlan_number">
                    <?php 
                      while ($vlan = mysqli_fetch_array($executa_query, MYSQLI_BOTH))
                      { 
                        if($vlan['contrato'] == NULL) {
                        ?>
                        <option value="<?php echo $vlan['vlan'];?>">
                          <?php echo "$vlan[vlan]-$vlan[descricao]";?>
                        </option>
                    <?php
                        }  
                      }
                    ?>
                  </select>
                </div>
                
                <input type='hidden' name='porta_atendimento' value=<?php echo $porta_selecionado;?>>
                <input type='hidden' name='frame' value=<?php echo $frame;?>>
                <input type='hidden' name='slot' value=<?php echo $slot;?>>
                <input type='hidden' name='pon' value=<?php echo $pon;?>>
                <input type='hidden' name='cto' value=<?php echo $cto;?>>
                <input type='hidden' name='device' value=<?php echo $device;?>>
                  
                <span class="form-group">
                  <button class="btn btn-secondary col-md-12" type="submit">Buscar</button>
                </span>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
<?php include_once "../classes/html_fim.php";   ?>