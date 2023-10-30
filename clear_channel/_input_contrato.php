<?php
include_once "../classes/html_inicio.php";

  if($_SESSION['gerenciar_l2l'] == 0) {//$_SESSION["cadastrar_onu"] == 0 &&
    echo '
    <script language= "JavaScript">
      alert("Sem Permissão de Acesso!");
      location.href="../classes/redirecionador_pagina.php";
    </script>
    ';
  }

$porta_selecionado = filter_input(INPUT_GET, 'porta_atendimento');
$frame = filter_input(INPUT_GET, 'frame');
$slot = filter_input(INPUT_GET, 'slot');
$pon = filter_input(INPUT_GET, 'pon');
$cto = filter_input(INPUT_GET, 'cto');
$device = filter_input(INPUT_GET, 'device');
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
                            <form role="form" action="register_customer.php" method="post" class="formL2l">
                                <label for="contract">Contrato</label>
                                <div class="form-group">
                                    <input id="contract" class="form-control" placeholder="Contrato" name="contrato" type="search"
                                           autofocus onfocusout="initializePacketByCplus()" required>
                                </div>

                                <div class=form-group>
                                    <label for="vlanInfo">L2L</label>
                                    <select id="vlanInfo" class="opa form-control" name="vlanName" disabled></select>
                                </div>
                                <div>

                                </div>

                                <input type='hidden' name='porta_atendimento' value=<?php echo $porta_selecionado; ?>>
                                <input type='hidden' name='frame' value=<?php echo $frame; ?>>
                                <input type='hidden' name='slot' value=<?php echo $slot; ?>>
                                <input type='hidden' name='pon' value=<?php echo $pon; ?>>
                                <input type='hidden' name='cto' value=<?php echo $cto; ?>>
                                <input type='hidden' name='device' value=<?php echo $device; ?>>
                                <input type="hidden" name="gems" class="gems"  >
                                <input type="hidden" name="vas"  class="vas" >
                                <input type="hidden" name="line" class="line"  >
                                <input type="hidden" name="serv" class="serv"  >

                                <span class="form-group">
                                  <button class="btn btn-secondary col-md-12 btnContinue" type="submit" disabled>Continuar</button>
                                </span>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php include_once "../classes/html_fim.php"; ?>
