<?php 
  include_once "../classes/html_inicio.php";
  include_once "../classes/funcoes.php";

  if($_SESSION["modificar_onu"] == 0) 
  {
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

    $contrato = $_POST['contrato'];

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

    $json_file = file_get_contents("http://192.168.80.5/sisspc/demos/get_pacote_ftth_cplus.php?contra=$contrato");
    $json_str = json_decode($json_file, true);
    $itens = $json_str['velocidade'];
    $codigoCplus = '';
    $verificacao = 0;

    $sql_consulta_perfil = "SELECT serial,pacote,tel_number,tel_password,tel_number2,tel_password2,perfil,cgnat,mac,ip,equipamento FROM ont
    WHERE contrato = '$contrato' ";
    $executa_query_perfil = mysqli_query($conectar,$sql_consulta_perfil);
    while ($ont = mysqli_fetch_array($executa_query_perfil, MYSQLI_BOTH)) 
    {
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
    }
    
    $sql_lista_velocidades = "SELECT nome,nomenclatura_velocidade, referencia_cplus FROM planos";
    $executa_query = mysqli_query($conectar,$sql_lista_velocidades); //pega os planos cadastrados no banco

    while ($listaPlanos = mysqli_fetch_array($executa_query, MYSQLI_BOTH)) 
    {  
      if($pacote == $listaPlanos['nomenclatura_velocidade'])
      { 
         $planoAtual = $listaPlanos['nome'];
      }

      foreach ( $itens as $codigoPlano )
      {
        $codigoCplus = $codigoPlano;
        if($codigoCplus == $listaPlanos[2])
        {
          $codigo = $listaPlanos['referencia_cplus'];
          $planoAtualCplus = $listaPlanos['nomenclatura_velocidade'];
          $nomePlanoAtualCplus = $listaPlanos['nome'];
          $verificacao = 1;
        }
      }
    }
        
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

  ?>
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
                <?php
                  $cgnat_status == true? $checkCgnat = '' : $checkCgnat = "checked";
                  
                  echo "<input type='hidden' name='profileVas' value=$profile>";
                  echo "<div>
                          <input type=checkbox name='cgnat_status' value='ip_real_ativo' id=checkCGNAT $checkCgnat /><label for=checkCGNAT>Ativar Redirecionamento de Porta</label>
                        </div>";

                  if($cgnat_status == true)
                  {
                    $arrayVasProfileCGNAT = ['VAS_Internet','VAS_IPTV','VAS_Internet-IPTV','VAS_Internet-VoIP','VAS_IPTV-VoIP','VAS_Internet-VoIP-IPTV'];
                  }else{
                    $arrayVasProfileCGNAT = ['VAS_Internet-REAL','VAS_IPTV','VAS_Internet-IPTV-REAL','VAS_Internet-VoIP-REAL','VAS_IPTV-VoIP','VAS_Internet-VoIP-IPTV-REAL'];
                  }
                    
                ?>
                <label>Qual Plano</label>              
                <div class="radio">
                  <label>
                    <input type="radio" name="optionsRadios" id="optionsRadios1" value="<?php echo "$arrayVasProfileCGNAT[0]"; ?>" <?php if($profile == "VAS_Internet" || $profile == "VAS_Internet-REAL" ) echo "checked"; ?>>INTERNET
                  </label>
                </div>
                <div class="radio">
                  <label>
                    <input type="radio" name="optionsRadios" id="optionsRadios2" value="<?php echo $arrayVasProfileCGNAT[1]; ?>"<?php if($profile == "VAS_IPTV" ) echo "checked"; ?>>IPTV
                  </label>
                </div>
                
                <div class="radio">
                  <label>
                    <input type="radio" name="optionsRadios" id="optionsRadios3" value="<?php echo $arrayVasProfileCGNAT[2]; ?>" <?php if($profile == "VAS_Internet-IPTV" || $profile == "VAS_Internet-IPTV-REAL" ) echo "checked"; ?>>INTERNET | IPTV
                  </label>
                </div>
                <div class="radio">
                  <label>
                    <input type="radio" name="optionsRadios" id="optionsRadios4" value="<?php echo $arrayVasProfileCGNAT[3]; ?>" <?php if($profile == "VAS_Internet-VoIP" || $profile == "VAS_Internet-VoIP-REAL" || $profile == "VAS_Internet-twoVoIP" || $profile == "VAS_Internet-twoVoIP-REAL") echo "checked"; ?>>INTERNET | TELEFONE
                  </label>
                </div>
                <div class="radio">
                    <label>
                        <input type="radio" name="optionsRadios" id="optionsRadios5" value="<?php echo $arrayVasProfileCGNAT[4]; ?>" <?php if($profile == "VAS_IPTV-VoIP" || $profile == "VAS_IPTV-twoVoIP-REAL" || $profile == "VAS_IPTV-twoVoIP") echo "checked" ; ?>> IPTV | TELEFONE
                    </label>
                </div>
                <div class="radio">
                  <label>
                    <input type="radio" name="optionsRadios" id="optionsRadios6" value="<?php echo $arrayVasProfileCGNAT[5]; ?>" <?php if($profile == "VAS_Internet-VoIP-IPTV" || $profile == "VAS_Internet-VoIP-IPTV-REAL" || $profile == "VAS_Internet-twoVoIP-IPTV" || $profile == "VAS_Internet-twoVoIP-IPTV-REAL" ) echo "checked"; ?>>INTERNET | TELEFONE | IPTV
                  </label>
                </div>
                <?php //codigos do cplus
                  if($codigo == 330 || $codigo == 331 || $codigo == 332 || $codigo == 333 || $codigo == 334 || $codigo == 335  || $codigo == 336 || $codigo == 349
                  || $codigo == 350 || $codigo == 351 || $codigo == 352 || $codigo == 353 || $codigo == 354
                  || $profile == "VAS_Internet-CORP-IP" || $profile == "VAS_Internet-CORP-IP-Bridge" || $profile == "VAS_Internet-IPTV-CORP-IP-Bridge" 
                  || $vasProfile == "VAS_Internet-VoIP-IPTV-CORP-IP" || $vasProfile == "VAS_Internet-VoIP-IPTV-CORP-IP-Bridge" 
                  || $vasProfile == "VAS_Internet-VoIP-CORP-IP" || $vasProfile == "VAS_Internet-VoIP-CORP-IP-Bridge")
                  { echo '
                    <div class="radio">
                      <label>
                        <input type="radio" name="optionsRadios" id="optionsRadios7" value="VAS_Internet-CORP-IP"';if($profile == "VAS_Internet-CORP-IP" ) echo "checked";echo '>INTERNET - IP';
                  echo'    
                      </label>
                    </div>
                    <div class="radio">
                      <label>
                        <input type="radio" name="optionsRadios" id="optionsRadios8" value="VAS_Internet-CORP-IP-Bridge"'; if($profile == "VAS_Internet-CORP-IP-Bridge" ) echo "checked"; echo'>INTERNET - IP - Modo Bridge
                      </label>
                    </div>';
                  echo '
                    <div class="radio">
                      <label>
                        <input type="radio" name="optionsRadios" id="optionsRadios9" value="VAS_Internet-IPTV-CORP-IP-Bridge"'; if($profile == "VAS_Internet-IPTV-CORP-IP-Bridge" ) echo "checked"; echo'>INTERNET - IP | IPTV - Modo Bridge
                      </label>
                    </div>';
                  echo '
                    <div class="radio">
                      <label>
                        <input type="radio" name="optionsRadios" id="optionsRadios10" value="VAS_Internet-VoIP-IPTV-VoIP-CORP-IP-Bridge"'; if($profile == "VAS_Internet-VoIP-IPTV-CORP-IP-Bridge" ) echo "checked"; echo'>INTERNET - IP | VoIP | IPTV - Modo Bridge
                      </label>
                    </div>';
                  echo '
                    <div class="radio">
                      <label>
                        <input type="radio" name="optionsRadios" id="optionsRadios11" value="VAS_Internet-VoIP-IPTV-CORP-IP"'; if($profile == "VAS_Internet-IPTV-VoIP-CORP-IP" ) echo "checked"; echo'>INTERNET - IP | VoIP | IPTV
                      </label>
                    </div>';
                   echo '
                    <div class="radio">
                      <label>
                        <input type="radio" name="optionsRadios" id="optionsRadios12" value="VAS_Internet-VoIP-CORP-IP"'; if($profile == "VAS_Internet-VoIP-CORP-IP" ) echo "checked"; echo'>INTERNET - IP | VoIP
                      </label>
                    </div>
                    ';
                  }
                ?>
            
                <div class="form-group">
                  <label>Contrato</label> 
                  <input class="form-control" placeholder="Contrato" name="contrato" type="text" value='<?php echo $contrato; ?>' autofocus readonly>
                </div>
                
                <div class="form-group">
                  <label>Pon MAC</label>                                                
                  <select class="form-control" name="serial">
                    <?php 
                      $sql_consulta_serial = "SELECT serial FROM ont WHERE contrato = $contrato ";
                      $executa_query = mysqli_query($conectar,$sql_consulta_serial);
                      while ($ont = mysqli_fetch_array($executa_query, MYSQLI_BOTH)) 
                      {
                        echo "<option value=$ont[serial]>$ont[serial]</option>";
                      }
                    ?>
                  </select>
                </div>
              <?php 
                if($profile == "VAS_IPTV" || $profile == "VAS_IPTV-VoIP" ) 
                  $visivelInternet = "style=display:none;";
                else
                  $visivelInternet = "style=display:visible;";
              ?>
               <div class="camposPacotes" <?php echo $visivelInternet; ?> > 
                <div class="form-group">
                  <label>Pacote</label>
                  <select class="form-control" name="pacote">
                    <?php                      
                      if($verificacao != 1)
                        echo "<option value='none'>Velocidade Não Cadastrada no Contrato, Favor Verificar no Control Plus</option>";
                      else
                        echo "<option value='$planoAtualCplus'>$nomePlanoAtualCplus</option>";
                      
                      mysqli_free_result($executa_query);                                                
                    ?>
                  </select>
                  <?php echo "<div class='form-group'> Pacote Atual: $planoAtual</div>"; ?>                  
                </div>
               </div>
                
                <!-- <div class='pull-left'>Velocidade Atual:  </div><br> -->
                <?php
                  if($codigo == 330 || $codigo == 331 || $codigo == 332 || $codigo == 333 || $codigo == 334 || $codigo == 335 || $codigo == 336 || $codigo == 349
                  || $codigo == 350 || $codigo == 351 || $codigo == 352 || $codigo == 353 || $codigo == 354)
                  {
                    if($profile == "VAS_Internet-CORP-IP-Bridge" || $profile == "VAS_Internet-IPTV-CORP-IP-Bridge" ||
                      $profile == "VAS_Internet-VoIP-IPTV-CORP-IP-Bridge" ||  $profile == "VAS_Internet-VoIP-CORP-IP-Bridge" ||
                      $profile == "VAS_Internet-VoIP-CORP-IP-Bridge")
                    {
                      $marcado = "checked";
                      $visivel = "style=display:visible;";
                      $visivelIP = "style=display:visible;";
                    }
                    elseif($profile == "VAS_Internet-CORP-IP" || $profile == "VAS_Internet-VoIP-IPTV-CORP-IP" || $profile == "VAS_Internet-VoIP-CORP-IP"
                          || $profile == "VAS_Internet-VoIP-IPTV-CORP-IP")
                    {
                      $marcado = "";
                      $visivel = "style=display:none;";
                      $visivelIP = "style=display:visible;";
                    }
                    else
                    {
                      $marcado = "";
                      $visivel = "style=display:none;";
                      $visivelIP = "style=display:none;";
                    }
                    
                    echo "
                        <div class='form-group bridge' $visivel>
                          <input type=checkbox name='modo_bridge' value='mac_externo' $marcado> IP Utilizado em Equipamento Externo</checkbox>
                        </div>
                          
                        <div class='form-group bridge' $visivel'>
                          <label>MAC do Equipamento</label>
                          <input type=text class=form-control id=mac name=mac value=";if($mac != null && $mac != $serial)echo "$mac";echo "/>
                        </div>
                        
                        <div class='form-group ipFixoSelector' $visivelIP>
                        <label>IP</label>";
                          $lista_ip = "select numero_ip from ips_valido WHERE utilizado = false";
                          $executa_ip = mysqli_query($conectar,$lista_ip);
                          echo"<select  class=form-control name=ipFixo>";
                          
                          if($ip != 'NULL')
                          {
                            echo"<option>$ip</option>";  
                          }  
                          
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
                <!-- model -->
                <div class="form-group">
                  <label>Equipamento</label>
                  <select id="equipamentoID" class="form-control" name="equipamentos">
                    <?php 
                      $sql_consulta_equipamentos = "SELECT * FROM equipamentos";
                      $executa_query_equipamentos = mysqli_query($conectar,$sql_consulta_equipamentos);
                      while ($equipamentos = mysqli_fetch_array($executa_query_equipamentos, MYSQLI_BOTH)) 
                      {
                        echo "<option value=$equipamentos[modelo]"; 
                          $equipamento_cliente == $equipamentos['modelo']? print " selected" : "" ; 
                        echo "> $equipamentos[modelo]";
                        
                        echo "</option>";
                      }
                    ?>
                  </select>
                </div>
            
                <?php 
                  if( $profile == "VAS_Internet-VoIP-IPTV" || $profile == "VAS_IPTV-VoIP" || $profile == "VAS_Internet-VoIP"
                    || $profile == "VAS_Internet-VoIP-IPTV-REAL" || $profile == "VAS_Internet-VoIP-REAL" || $profile == "VAS_Internet-twoVoIP"
                    || $profile == "VAS_Internet-twoVoIP-IPTV" || $profile == "VAS_Internet-twoVoIP-IPTV-REAL" || $profile == "VAS_Internet-twoVoIP-REAL" 
                    || $profile == "VAS_Internet-VoIP-CORP-IP" ){
                    $visivel = "style=display:visible";
                  }else{
                    $visivel = "style=display:none";
                  }
                    
                    echo "
                    <div class='camposTelefone' $visivel>
                      <div class='form-group'>
                        <label>Telefone</label>
                        <input class='form-control' placeholder='Telefone' name='numeroTelNovo' type='text' value='$numeroTel' autofocus>
                      </div>
                      <div class='form-group'>
                        <label>Senha do Telefone</label>
                        <input class='form-control' placeholder='Senha do Telefone' name='passwordTelNovo' type='text' value='$passwordTel' autofocus>
                      </div>";
                      if($equipamento_cliente == "EG8245H5"){
                        echo "
                        <div id=tel2_user class='form-group'>
                          <label>Telefone</label>
                          <input class='form-control' placeholder='Segundo Telefone' name='numeroTelNovo2' type='text' value='$numeroTel2' autofocus>
                        </div>";
                        echo "
                        <div id=tel2_pass class='form-group'>
                          <label>Senha do Telefone</label>
                          <input class='form-control' placeholder='Senha do Segundo Telefone' name='passwordTelNovo2' type='text' value='$passwordTel2' autofocus>
                        </div>";
                      }else{
                        echo"  
                          <div id=tel2_user class='form-group'></div>
                          <div id=tel2_pass class='form-group'></div>
                        ";
                      }
                    echo "
                    </div>";
                  //}  
                ?>

                

                <button class="btn btn-lg btn-success btn-block">Alterar</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
<?php include_once "../classes/html_fim.php";   ?>
