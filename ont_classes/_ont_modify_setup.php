<?php 
  include_once "../classes/html_inicio.php";
  include_once "../classes/funcoes.php";
  include "../db/db_config_mysql.php";

  #### Pegando Contrato
  $contrato = filter_input(INPUT_POST,'contrato');

  if(checar_contrato($contrato) == null)
  {
    mysqli_close($conectar);
    echo '
      <script language= "JavaScript">
        alert("Contrato Inexistente ou Cancelado");
        location.href="../ont_classes/ont_change.php";
      </script>
    ';
  }

  ##### PEGA INFORMACOES NO ERP DO ASSINANTE
  $json_file = file_get_contents("http://192.168.80.5/sisspc/demos/get_pacote_ftth_cplus.php?contra=$contrato");
  $json_str = json_decode($json_file, true);
  $codigosDeProgramacaoCplus = $json_str['velocidade'];
  $verificacao = 0;

  ###### PEGA INFORMAÇÔES CADASTRADAS NO BANCO LOCAL
  $sql_consulta_perfil = "SELECT serial,pacote,tel_number,tel_password,tel_number2,tel_password2,perfil,cgnat,mac,ip,equipamento FROM ont
    WHERE contrato = '$contrato' ";
  $executa_query_perfil = mysqli_query($conectar,$sql_consulta_perfil);
  $ont = mysqli_fetch_assoc($executa_query_perfil);
    //seta as variáveis do cliente
  $pacote = $ont['pacote'];
  $numeroTel = $ont['tel_number'];
  $passwordTel = $ont['tel_password'];
  $numeroTel2 = $ont['tel_number2'];
  $passwordTel2 = $ont['tel_password2'];
  $profile = $ont['perfil'];
  $serial = $ont['serial'];
  $cgnat_status = $ont['cgnat'];
  $mac = $ont['mac'];
  $ip = $ont['ip'];
  $equipamento_cliente = $ont['equipamento'];
  
  ### VERIFICA SE ESTA NO REDIRECIONAMENTO
  $cgnat_status == true? $checkCgnat = '' : $checkCgnat = "checked";

  ### VAS PROFILE BRIDGE EM TRIPLE PLAY 
  $profile == "VAS_Internet-VoIP-IPTV-CORP-IP-B"? $profile = "VAS_Internet-VoIP-IPTV-CORP-IP-B" : $profile;

  ###### VERIFICA SE O CONTRATO TEM MAC CADASTRADO
  if(empty($serial))
  {
    mysqli_close($conectar);
    echo '
      <script language= "JavaScript">
        alert("Não Há Equipamento!");
        location.href="ont_change.php";
      </script>
      ';
  }

  ##pega os planos cadastrados no banco
  $sql_lista_velocidades = "SELECT nome,nomenclatura_velocidade, referencia_cplus FROM planos";
  $executa_query = mysqli_query($conectar,$sql_lista_velocidades); 

  while ($listaPlanos = mysqli_fetch_array($executa_query, MYSQLI_ASSOC)) 
  {  
    ### PEGA O NOME DO PACOTE ATUAL
    if($pacote == $listaPlanos['nomenclatura_velocidade'])
      $planoAtual = $listaPlanos['nome'];
    
    #### LISTA O PLANO QUE FOI ALTERADO NO ERP
    foreach ($codigosDeProgramacaoCplus as $codigoCplus) {
      if($codigoCplus == $listaPlanos['referencia_cplus'])
      {
        $codigo = $listaPlanos['referencia_cplus'];
        $planoAtualCplus = $listaPlanos['nomenclatura_velocidade'];
        $nomePlanoAtualCplus = $listaPlanos['nome'];
        if($planoAtualCplus == $pacote)
          $optionVelocidade =" $optionVelocidade <option value='$planoAtualCplus' selected>$nomePlanoAtualCplus</option>";
        else
          $optionVelocidade =" $optionVelocidade <option value='$planoAtualCplus'>$nomePlanoAtualCplus</option>";
        $verificacao = 1;
      }
    }
  }
  //Trata o Select da Velocidade
  if($verificacao != 1)
  $optionVelocidade ="<option value='none'>Velocidade Não Cadastrada no Contrato, Favor Verificar no Control Plus</option>";

  //Seleciona qual array irá aprecer pro cliente.
  if($cgnat_status == true)
  {
    $arrayVasProfileCGNAT = [
                              'Internet' => 'VAS_Internet',
                              'IPTV' => 'VAS_IPTV',
                              'CONVERSOR' => 'conversorHFC',
                              'Internet e IPTV' => 'VAS_Internet-IPTV',
                              'Internet e Telefone' => 'VAS_Internet-VoIP',
                              'IPTV e Telefone' => 'VAS_IPTV-VoIP',
                              'Internet Telefone e IPTV' => 'VAS_Internet-VoIP-IPTV'
                            ];
  }else{
    $arrayVasProfileCGNAT = [
                              'Internet' => 'VAS_Internet-REAL',
                              'IPTV' => 'VAS_IPTV',
                              'CONVERSOR' => 'conversorHFC',
                              'Internet e IPTV' => 'VAS_Internet-IPTV-REAL',
                              'Internet e Telefone' => 'VAS_Internet-VoIP-REAL',
                              'IPTV e Telefone' => 'VAS_IPTV-VoIP',
                              'Internet Telefone e IPTV' => 'VAS_Internet-VoIP-IPTV-REAL'
                            ];
  }

  //Option de PON(Serial)
  $sql_consulta_serial = "SELECT serial FROM ont WHERE contrato = $contrato ";
  $executa_query = mysqli_query($conectar,$sql_consulta_serial);
  
  while ($ont = mysqli_fetch_array($executa_query, MYSQLI_BOTH)) 
  {
    $optionMAC = "$optionMAC <option value=$ont[serial]>$ont[serial]</option>";
  }

  //Verifica se tem IPTV para exibir ou não!
  if($profile == "VAS_IPTV" || $profile == "VAS_IPTV-VoIP" ) 
    $visivelInternet = "style=display:none;";
  else
    $visivelInternet = "style=display:visible;";

  //Trata o Option de equipamentos
  $sql_consulta_equipamentos = "SELECT * FROM equipamentos";
  $executa_query_equipamentos = mysqli_query($conectar,$sql_consulta_equipamentos);
  while ($equipamentos = mysqli_fetch_array($executa_query_equipamentos, MYSQLI_BOTH)) 
  {
    if($equipamento_cliente == $equipamentos['modelo'])
      $equipamento_selecionado = "<option value=$equipamentos[modelo] selected>$equipamentos[modelo]</option>";
    else 
      $equipamento_selecionado = "<option value=$equipamentos[modelo]>$equipamentos[modelo]</option>";
    
    $optionEquipamento = "$optionEquipamento $equipamento_selecionado";
  }

##### TRATA TELEFONIA

  $arrayVasProfileTelefonia = [
                                "VAS_Internet-VoIP-IPTV",
                                "VAS_IPTV-VoIP",
                                "VAS_Internet-VoIP",
                                "VAS_Internet-VoIP-IPTV-REAL",
                                "VAS_Internet-VoIP-REAL",
                                "VAS_Internet-twoVoIP",
                                "VAS_Internet-twoVoIP-IPTV",
                                "VAS_Internet-twoVoIP-IPTV-REAL",
                                "VAS_Internet-twoVoIP-REAL",
                                "VAS_Internet-VoIP-CORP-IP",
                                "VAS_Internet-VoIP-CORP-IP-Bridge"
  ];

  if(in_array($profile,$arrayVasProfileTelefonia))
    $telefoniaVisivel = "style=display:visible";
  else
    $telefoniaVisivel = "style=display:none";

#####Verifica Programaçãoq ue contém IPFixo
  $arrayProgramacaoIpFixo = array(330,331,332,333,334,335,336,349,350,351,352,353,354,358,372,374,377,380,381,388,389);
  $arrayVasProfileIpFixoBridge = [
                            'Internet - Bridge' => "VAS_Internet-CORP-IP-Bridge",
                            'Internet e IPTV - Bridge' => "VAS_Internet-IPTV-CORP-IP-Bridge",
                            'Internet e Telefone - Bridge' => "VAS_Internet-VoIP-CORP-IP-Bridge",
                            'Internet Telefone e IPTV - Bridge' => "VAS_Internet-VoIP-IPTV-CORP-IP-B"
  ];

  $arrayVasProfileIpFixo = [
                            'Internet - IP' => "VAS_Internet-CORP-IP",
                            'Internet e IPTV - IP' => "VAS_Internet-IPTV-CORP-IP",
                            'Internet e Telefone - IP' => "VAS_Internet-VoIP-CORP-IP",
                            'Internet Telefone e IPTV - IP' => "VAS_Internet-VoIP-IPTV-CORP-IP"
  ];

  //Exibe option de IP Fixo
  if(in_array($codigo,$arrayProgramacaoIpFixo) || in_array($profile,$arrayVasProfileIpFixoBridge) || in_array($profile,$arrayVasProfileIpFixo))
  {
    $optionVasProfileFixo = '<optgroup label="Para IP Fixo">';
    $optionVasProfileFixoBridge = '<optgroup label="Para IP Fixo Modo Bridge">';


    foreach($arrayVasProfileIpFixo as $nome => $vasProfileFixo){
      
      if($profile == $vasProfileFixo)
        $optionVasProfileFixo = "$optionVasProfileFixo <option value=$vasProfileFixo selected>$nome</option>";
      else
        $optionVasProfileFixo = "$optionVasProfileFixo <option value=$vasProfileFixo >$nome</option>";
    }

    foreach($arrayVasProfileIpFixoBridge as $nome => $vasProfileFixoBridge)
    {
      if($profile == $vasProfileFixoBridge)
        $optionVasProfileFixoBridge = "$optionVasProfileFixoBridge <option value=$vasProfileFixoBridge selected>$nome</option>";
      else
        $optionVasProfileFixoBridge = "$optionVasProfileFixoBridge <option value=$vasProfileFixoBridge >$nome</option>";
    }

    $optionVasProfileFixo = "$optionVasProfileFixo </optgroup>";
    $optionVasProfileFixoBridge = "$optionVasProfileFixoBridge </optgroup>";

  }

  //trata o ip fixo

  $marcado = "";
  $visivel = "style=display:none;";
  $visivelIP = "style=display:none;";

  if(in_array($codigo,$arrayProgramacaoIpFixo))
  {
    $lista_ip = "select numero_ip from ips_valido WHERE utilizado = false";
    $executa_ip = mysqli_query($conectar,$lista_ip);
    
    if($ip != NULL)
      $optionIPFixo = "<option>$ip</option>";
    else
      $optionIPFixo = "<option disabled selected>Seleciona um IP</option>";

    while ($listaIP = mysqli_fetch_array($executa_ip, MYSQLI_ASSOC))
    {
      $optionIPFixo = "$optionIPFixo <option>$listaIP[numero_ip]</option>";
    }

    //verifica se ele esta em modo bridge
    if(in_array($profile,$arrayVasProfileIpFixoBridge))
    {
      $marcado = "checked";
      $visivel = "style=display:visible;";
      $visivelIP = "style=display:visible;";
    }elseif(in_array($profile,$arrayVasProfileIpFixo)){
      $visivelIP = "style=display:visible;";
    }
  }
?>

  <div id="page-wrapper">
    <div class="row">
    <div class="col-md-4 col-md-offset-4">
      <div class="login-panel panel panel-default">
        <div class="panel-heading">
          <h3 class="panel-title">Mudança de ONT</h3>
        </div>

        <div class="panel-body">
          <form  class="form-horizontal">
            <input type='hidden' name='profileVas' value=<?php echo $profile ?>>

            <div class="form-group form-group-sm">
              <label for="vasProfile" class="col-sm-4 control-label">Selecione o Plano</label>
              <div class="col-sm-8">
                <select class="form-control" name="vasProfile" id="vasProfile">
                  <div id="selectIpRealNat">
                  <?php 
                    foreach ($arrayVasProfileCGNAT as $key => $vasProfile) 
                    {
                      if($profile == $vasProfile)
                      {
                        switch ($profile) {
                          case 'VAS_Internet':
                            echo "<option id='$vasProfile' value='$vasProfile' selected>$key</option>";
                            break;
                          case 'VAS_Internet-IPTV':
                            echo "<option id='$vasProfile' value='$vasProfile' selected>$key </option>";
                            break;
                          case 'VAS_Internet-VoIP':
                            echo "<option id='$vasProfile' value='$vasProfile' selected>$key</option>";
                            break;
                          case 'VAS_Internet-VoIP-IPTV':
                            echo "<option id='$vasProfile' value='$vasProfile' selected>$key</option>";
                            break;
                          default:
                            echo "<option id='$vasProfile' value='$vasProfile' selected>$key</option>";
                            break;
                        }
                      }
                      else
                      {
                        switch ($vasProfile) {
                          case 'VAS_Internet':
                            echo "<option id='$vasProfile' value='$vasProfile'>$key</option>";
                            break;
                          case 'VAS_Internet-IPTV':
                            echo "<option id='$vasProfile' value='$vasProfile'>$key</option>";
                            break;
                          case 'VAS_Internet-VoIP':
                            echo "<option id='$vasProfile' value='$vasProfile'>$key</option>";
                            break;
                          case 'VAS_Internet-VoIP-IPTV':
                            echo "<option id='$vasProfile' value='$vasProfile'>$key</option>";
                            break;
                          default:
                            echo "<option id='$vasProfile' value='$vasProfile'>$key</option>";
                            break;
                        }
                      }
                    }
                  ?>
                  </div>
                  <!--IP FIXO OPTIONS VASPROFILE-->
                  <?php 
                    echo "$optionVasProfileFixo";
                  ?>
                  <!--IP FIXO BRIDGE OPTIONS VASPROFILE-->
                  <?php 
                    echo "$optionVasProfileFixoBridge";
                  ?>
                </select>
              </div>
            </div>

            <div class="form-group form-group-sm">
              <label for="contrato" class="col-sm-2 control-label">Contrato</label>
              <div class="col-sm-10">
                <input id="contrato" class="form-control" placeholder="Contrato" name="contrato" type="text" value='<?php echo $contrato; ?>' readonly>
              </div>
            </div>

            <div class="form-group form-group-sm conversorHide">
              <label for="serial" class="col-sm-2 control-label">Pon(MAC)</label>
              <div class="col-sm-10">
                <select class="form-control" name="serial" id="serial">
                  <?php echo "$optionMAC";?>
                </select>
              </div>
            </div>

            <div class="form-group form-group-sm camposPacotes" <?php echo $visivelInternet; ?>>
              <label for="pacote" class="control-label col-sm-2">Pacote</label>
              <div class="col-sm-10">
                <select class="form-control" name="pacote" id="pacote">
                  <?php echo "$optionVelocidade"; ?>
                </select>
              </div>
            </div>

            <div class="form-group form-group-sm conversorHide">
              <label for="equipamentoID" class="control-label col-sm-3">Equipamento</label>
              <div class="col-sm-9">
                <select class="form-control" name="equipamentos" id="equipamentoID">
                  <?php echo "$optionEquipamento"; ?>
                </select>
              </div>
            </div>

          <!--TRATATIVAS IP FIXO-->
          <?php if(in_array($codigo,$arrayProgramacaoIpFixo)){ ?> 
            <hr>
            <div class="form-group form-group-sm ipFixoSelector" <?php echo $visivelIP; ?>>
              <label for="ipFixo" class="control-label col-sm-3">IP Fixo</label>
              <div class="col-sm-9">
                <select class="form-control" name="ipFixo" id="ipFixo">
                  <?php echo "$optionIPFixo"; ?>
                </select>
              </div>
            </div>

           <div style="display: none;"> 
            <div class='form-group form-group-sm bridge_check' <?php echo $visivel;?> style='display: none'>
              <label for="bridge_modify_check" class="control-label col-sm-8">IP Utilizado em Equipamento Externo</label>
              <div class="col-sm-2">
                <input id="bridge_modify_check" type=checkbox name='modo_bridge_check' value='mac_externo' <?php echo $marcado; ?>/> 
              </div>
            </div>
              
            <div class='form-group form-group-sm bridge_modify' <?php echo $visivel;?> style='display: none'>
              <label for="mac" class="control-label col-sm-6">MAC do Equipamento</label>
              <div class="col-sm-6">
                <input type=text class="form-control" id=mac name=mac value='<?php if($mac != null && $mac != $serial)echo "$mac";?>' />
              </div>
            </div>
          </div>
            
          <?php }?>
          <!-- FIM TRATATIVAS IP FIXO -->
          
          <!-- INICIO TELEFONIA -->
          
            <div class='camposTelefone' <?php echo $telefoniaVisivel ?>>
              <hr>
              <div class="form-group form-group-sm">
                <label for="telefone" class="col-sm-2 control-label">Telefone</label>
                <div class="col-sm-4">
                  <input id="telefone" class='form-control' placeholder='Telefone' name='numeroTelNovo' type='text' value='<?php echo $numeroTel?>'>
                </div>
                <label for="telefone" class="col-sm-2 control-label">Senha</label>
                <div class="col-sm-4">
                  <input class='form-control' placeholder='Senha do Telefone' name='passwordTelNovo' type='text' value='<?php echo $passwordTel ?>' autofocus>
                </div>
              </div>
            </div>

            <?php if($equipamento_cliente == "EG8245H5"){?>
              <div class="form-group form-group-sm">
                <label for="telefone2" class="col-sm-2 control-label">Telefone 2</label>
                <div class="col-sm-4">
                  <input id="telefone2" class='form-control' placeholder='Segundo Telefone' name='numeroTelNovo2' type='text' value='<?php $numeroTel2 ?>' autofocus>
                </div>

                <label for="senha2" class="col-sm-2 control-label">Senha</label>
                <div class="col-sm-4">
                  <input id="senha2" class='form-control' placeholder='Senha do Segundo Telefone' name='passwordTelNovo2' type='text' value='<?php $passwordTel2 ?>' autofocus>
                </div>
              </div>
            <?php }else{?>
              <div class="form-group form-group-sm">
                <div id=tel2_user_modify></div>
                <div id=tel2_pass_modify></div>
              </div>
            <?php }?>
          <!-- FIM TELEFONIA -->

            <div class="form-group form-group-sm conversorHFC">
              <label for="checkCGNAT" class="control-label col-sm-7">Ativar Redirecionamento de Porta</label>
              <div class="col-sm-2">
                <input type=checkbox name='cgnat_status' value='ip_real_ativo' id=checkCGNAT <?php echo $checkCgnat;?> /> 
              </div>
            </div>
            
            <div class="form-group form-group-sm">
              <div class="col-sm-12">
                <button type="button" class="btn btn-block" onClick="alterar();">Alterar</button>
              </div>
            </div>
            
          </form>
        </div>
        <div class="modal modal-espera"></div>
      </div>
    </div>
  </div>




<?php include_once "../classes/html_fim.php";   ?>
