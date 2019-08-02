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

  $porta_selecionado = filter_input(INPUT_POST,'porta_atendimento');
  $frame = filter_input(INPUT_POST,'frame');
  $slot = filter_input(INPUT_POST,'slot');
  $pon = filter_input(INPUT_POST,'pon');
  $cto = filter_input(INPUT_POST,'cto');
  $device = filter_input(INPUT_POST,'device');
  $contrato = filter_input(INPUT_POST,'contrato');
  $designacao_circuito = filter_input(INPUT_POST,'designacao');
  $vlan_associada = filter_input(INPUT_POST,'vlan_number');


  if(!empty($vlan_associada))
  {
    $designacao_circuito = "$designacao_circuito-$vlan_associada";
  }
    

  if(checar_contrato($contrato) == null)
  {
    mysqli_close('$conectar');
    echo '
      <script language= "JavaScript">
        alert("Contrato Inexistente ou Cancelado");
        location.href="../ont_classes/ont_register.php";
      </script>
    ';
  }

  $json_file = file_get_contents("http://192.168.80.5/sisspc/demos/get_pacote_ftth_cplus.php?contra=$contrato");
  $json_str = json_decode($json_file, true);
  $itens = $json_str['velocidade'];
  $nome = $json_str['nome'];

?>
<div id="page-wrapper">
  <div class="container">
    <div class="row">
      <div class="col-md-4 col-md-offset-4">
        <div class="login-panel panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title">Cadastro de ONT L2L</h3>
          </div>
          <div class="panel-body">
          <fieldset>
              <legend>Informações</legend>
                <p><?php echo "PORTA: $porta_selecionado | OLT: $device | FRAME: $frame | SLOT: $slot | PON: $pon | CTO: $cto | DESIGNAÇÃO OLT: $designacao_circuito";?></p>
          </fieldset>
          </div>
          <div class="panel-body">
            <form role="form" action="../classes/cadastrar_corporativo.php" method="post">
              <fieldset class="check-planos">
                  <div class="form-group">
                    <legend>Selecione o Plano</legend>
                    <div class="checkbox">
                      <label>
                        <input type="checkbox" name="l_to_l" value="l2l" checked> Lan To Lan
                      </label>

                      <label>
                        <input type="checkbox" name="internet_checked" value="Internet">INTERNET
                      </label>

                      <label>
                        <input type="checkbox" name="iptv_checked" value="IPTV">IPTV
                      </label>

                      <label>
                        <input type="checkbox" name="telefone_checked" value="Telefone"> TELEFONE
                      </label>
                    </div>
                  </div>
              </fieldset>
              
              <fieldset>
                <legend>Requisitos</legend>
                  <div class="form-group">
                      <label>Contrato</label> 
                      <input class="form-control" name="contrato" value='<?php echo $contrato;?>'type="text" readonly>
                  </div>

                  <div class="form-group">
                      <label>Nome do Assinante</label> 
                      <input class="form-control" name="nome" value='<?php echo $nome[0];?>'type="text" readonly>
                  </div>
                  
                  <div class="form-group">
                    <label>VAS Profile</label>
                    <input class="form-control" name="vasProfile" value='<?php echo "VAS_L2L-$designacao_circuito";?>'type="text" readonly>
                  </div>
                  
                  <div class="form-group">
                      <label>Pon MAC</label>                                                
                      <input class="form-control" placeholder="MAC PON" name="serial" type="text" minlength=16 maxlength=16 autofocus required>
                  </div>
                  <div class="camposPacotes" style="display:none" >                                   
                    <div class="form-group" >
                      <?php include "../classes/listaPlanos.php" ?>
                      <label>Pacote</label>
                      <select class="form-control" name="pacote" required>
                        <option value='' selected disabled>Selecione a Velocidade</option>
                      <?php 
                        $codigoCplus = '';
                        $codigo = '';
                        $verificacao = 0;
                        
                        $sql_lista_velocidades = "SELECT nome,nomenclatura_velocidade, referencia_cplus FROM planos";
                        $executa_query = mysqli_query($conectar,$sql_lista_velocidades);
                        while ($listaPlanos = mysqli_fetch_array($executa_query, MYSQLI_BOTH))
                        {
                          foreach ( $itens as $codigoPlano )
                          {
                            $codigoCplus = $codigoPlano;
                            
                            if($codigoCplus == $listaPlanos['referencia_cplus'])
                            {
                              $codigo = $listaPlanos['referencia_cplus'];
                              echo "<option value='$listaPlanos[nomenclatura_velocidade]'>$listaPlanos[nome]</option>";
                              $verificacao = 1;
                            }
                          }
                        }
                        if($verificacao != 1)
                          echo "<option value='none'>Velocidade Não Cadastrada no Contrato, Favor Verificar no Control Plus</option>";
                        mysqli_free_result($executa_query);
                      ?>
                      </select>
                    </div>
                  </div> <!-- fim div pacote -->
                  
                  <?php
                    if($codigo == 330 || $codigo == 331 || $codigo == 332 || $codigo == 333 || $codigo == 334 || $codigo == 335 || $codigo == 336 || 
                       $codigo == 349  || $codigo == 350 || $codigo == 351 || $codigo == 352 || $codigo == 353 || $codigo == 354 )
                    {
                      echo "
                      <div class='form-group'>
                        <input type=checkbox name='modo_bridge' value='mac_externo'> IP Utilizado em Equipamento Externo</checkbox>
                      </div>

                      <div class='form-group bridge' style='display: none;'>
                        <label>MAC do Equipamento</label>
                        <input type=text class=form-control id=mac name=mac />
                      </div>
                      
                      <div class=form-group>
                        <label>IP</label>";
                          $lista_ip = "select numero_ip from ips_valido WHERE utilizado = false";
                          $executa_ip = mysqli_query($conectar,$lista_ip);
                          echo"<select  class=form-control name=ipFixo>";
                          while ($listaIP = mysqli_fetch_array($executa_ip, MYSQLI_BOTH))
                          {
                            echo"<option>$listaIP[0]</option>";       
                          }
                          echo "</select>
                      </div>";

                    }else{
                      echo "<input type=hidden name=mac value=NULL>
                            <input type=hidden name=ipFixo value=NULL>";
                    }
                  ?>

                  <div class="form-group">
                    <label>Equipamento</label>
                    <select class="form-control" name="equipamentos">
                      <?php 
                        $sql_consulta_equipamentos = "SELECT * FROM equipamentos";
                        $executa_query_equipamentos = mysqli_query($conectar,$sql_consulta_equipamentos);
                        while ($equipamentos = mysqli_fetch_array($executa_query_equipamentos, MYSQLI_BOTH)) 
                        {
                          echo "<option value=$equipamentos[modelo]>$equipamentos[modelo]</option>";
                        }
                      ?>
                    </select>
                  </div>
                  
                  <?php
                    echo "<input type=hidden name=porta_atendimento value=$porta_selecionado>
                          <input type=hidden name=frame value=$frame>
                          <input type=hidden name=slot value=$slot>
                          <input type=hidden name=pon value=$pon>
                          <input type=hidden name=caixa_atendimento_select value=$cto>
                          <input type=hidden name=deviceName value=$device>
                    ";
                  ?>
              
                  <div class="camposTelefone" style="display:none" >
                    <div class="form-group">
                      <label>Telefone</label>
                      <input class="form-control" placeholder="Telefone" name="numeroTel" type="text" autofocus>
                    </div> 
                    
                    <div class="form-group">
                      <label>Senha do Telefone</label>
                      <input class="form-control" placeholder="Senha do Telefone" name="passwordTel" type="text" autofocus>
                    </div>
                    
                  </div>
              </fieldset>
              <button class="btn btn-lg btn-success btn-block">Cadastrar</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
include_once "../classes/html_fim.php";   ?>
