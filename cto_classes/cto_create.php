<script>

    function calcular() 
    {
        var area = Number(document.getElementById("area").value);
        var celula = Number(document.getElementById("celula").value);
        var cto = Number(document.getElementById("nCtos").value);
        var elemResult = document.getElementById("ctoNome");

        if (elemResult.textContent === undefined) {
            elemResult.value = String(area) + "C" + String(celula) + ".1" + " até " + String(area) + "C" + String(celula) + "." + String(cto);//elemResult.innerText = "O resultado é " + String(area) + "C" + String(celula) + "." + String(cto);
        }
        else { // IE
            elemResult.value = String(area) + "C" + String(celula) + ".1" + " até " + String(area) + "C" + String(celula) + "." + String(cto);//elemResult.innerText = "O resultado é " + String(area) + "C" + String(celula) + "." + String(cto);
        }
    }

</script>

<?php 
    include "../classes/html_inicio.php"; 
    include "../db/db_config_mysql.php"; 

    if($_SESSION["cadastrar_cto"] == 0) {
      echo '
      <script language= "JavaScript">
        alert("Sem Permissão de Acesso!");
        location.href="../classes/redirecionador_pagina.php";    
      </script>
      ';
    }
    
    $olt = filter_input(INPUT_POST,'olt');

    $sql_nome = "SELECT deviceName, frame, slot FROM pon WHERE pon_id = $olt";

    $executa_nome = mysqli_query($conectar,$sql_nome);
    $nomeDispositivo = mysqli_fetch_array($executa_nome,MYSQLI_BOTH);
    
?>

<div id="page-wrapper">

<div class="container">
    <div class="row">
        <div class="col-md-4 col-md-offset-4">
            <div class="login-panel panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Cadastro de CTO na OLT <?php echo $nomeDispositivo['deviceName']; ?></h3>
                </div>
                <div class="panel-body">
                    <form role="form" action="../classes/cadastrar_cto.php" method="post">
                            <div class="form-group">
                                <label>Range de CTO</label> 
                                <!-- <p id="ctoNome"></p> -->
                                <input class="form-control" placeholder="CTO" id="ctoNome" name="ctoNome" type="text" pattern="[a-zA-Z0-9.%]+.[0-9]+" title="O Formato da CTO é AreaCelula.NumeroDaCTO(3C1.10)" readonly autofocus>
                            </div>

                            <div class="form-group" style=''>
                                <label>Area</label>
                                <input class="" placeholder="Area" id="area" name="area" type="number" pattern="[0-9]" min=0 title="Digite a área" onblur="calcular();" autofocus required>
                                <label>Célula</label>
                                <input class="" placeholder="Celula" id="celula" name="celula" type="number" pattern="[0-9]" min=0 title="Digite a " onblur="calcular();" autofocus required>
                                <label>Quantidade de CTOs</label>
                                <input class="" placeholder="No. CTOs" id="nCtos" name="nCtos" type="number" pattern="[0-9]" min=1 max=8 title="Digite a quantidade de CTOs que irão ser criadas" onblur="calcular();" autofocus required>
                            </div>

                            <!-- <div class="form-group" style=''>
                                <label>Quantidade de Portas</label>
                                <input class="form-control" placeholder="CTO" name="cto" type="number" pattern="[0-9]" title="Digite a quantidade de CTOs que irão ser criadas" autofocus required>
                            </div> -->
                            <div class="form-group">
                              <label>PON</label> 
                              <select class="form-control" name="pon">
                                <?php 
                                //    $sql_check = 'SELECT DISTINCT caixa_atendimento,frame_slot_pon FROM ctos 
                                //     WHERE pon_id_fk = $olt AND frame_slot_pon = "$frame-$slot-$pon"';
                                   $sql_check = "SELECT DISTINCT * FROM ctos WHERE pon_id_fk = $olt";
                                   $executa_check = mysqli_query($conectar,$sql_check);
                                   if(mysqli_num_rows($executa_check) > 0) //checa se ja existe CTO cadastrada na pon
                                   {
                                      $sql_consulta_serial = "SELECT DISTINCT olt.frame,olt.slot,olt.porta FROM pon olt 
                                        INNER JOIN ctos cto ON cto.pon_id_fk = $olt
                                        WHERE olt.pon_id = $olt";//"SELECT frame,slot,porta FROM pon WHERE pon_id = $olt";
                                      
                                      $array_ctos = array();

                                      $executa_query = mysqli_query($conectar,$sql_consulta_serial) or die(mysqli_error($conectar));

                                      $sql_get_fsp = "SELECT DISTINCT caixa_atendimento,frame_slot_pon FROM `ctos` 
                                        WHERE pon_id_fk = $olt" ;
                                      $executa_get_fsp = mysqli_query($conectar,$sql_get_fsp);

                                      while ($porta_pon_cadastrada = mysqli_fetch_array($executa_get_fsp,MYSQLI_BOTH))
                                      {
                                        array_push($array_ctos,$porta_pon_cadastrada['frame_slot_pon']);
                                      }
                                      $conta = array_count_values($array_ctos);
                                      while ($ont = mysqli_fetch_array($executa_query, MYSQLI_BOTH))
                                      {
                                        for($porta = 0;$porta < $ont['porta'];$porta++)
                                        {
                                            if($conta["$ont[frame]-$ont[slot]-$porta"] < 16)
                                                echo "<option value=$olt-$ont[frame]-$ont[slot]-$porta>  Slot: $ont[slot]  Porta: $porta </option>";
                                        }
                                      }
                                    }else{
                                      $sql_consulta_serial = "SELECT frame, slot, porta FROM pon WHERE pon_id = $olt";

                                      $executa_query = mysqli_query($conectar,$sql_consulta_serial) or die(mysqli_error($conectar));
                                      
                                      while ($ont = mysqli_fetch_array($executa_query, MYSQLI_BOTH))
                                      {
                                        for($porta = 0;$porta < $ont['porta'];$porta++)
                                        {
                                            //if(!in_array("$ont[frame]-$ont[slot]-$porta",$array_ctos))
                                                echo "<option value=$olt-$ont[frame]-$ont[slot]-$porta> Slot: $ont[slot]  Porta: $porta </option>";
                                        }
                                      }
                                    }
                                ?>
                              </select>
                            </div>
                            <div class="form-group">
                                <label>Quantidade de Portas de Atendimento</label>                                                
                                <select class="form-control" name="porta">
                                    echo "<option value=8>8</option>";
                                </select>
                            </div>
                            <div class="form-group">
                              disponibilizar: <input type='checkbox' name='cto_disponivel' value=1/>
                            </div>                                                    
                        <button class="btn btn-lg btn-success btn-block">Cadastrar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "../classes/html_fim.php";?>
