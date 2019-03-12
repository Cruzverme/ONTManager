<script>

    function calcular() 
    {
        var area = Number(document.getElementById("area").value);
        var celula = Number(document.getElementById("celula").value);
        var cto = Number(document.getElementById("nCtos").value);
        var elemResult = document.getElementById("ctoNome");
        var tipoCTO = document.getElementsByName("tipoCTO")[0].value;

        if (elemResult.textContent === undefined) {
            if(tipoCTO === "associada")
            {
                elemResult.value = String(area) + "C" + String(celula) + ".1B" + " até " + String(area) + "C" + String(celula) + "." + String(cto) + "B";
            }else{
                elemResult.value = String(area) + "C" + String(celula) + ".1" + " até " + String(area) + "C" + String(celula) + "." + String(cto);//elemResult.innerText = "O resultado é " + String(area) + "C" + String(celula) + "." + String(cto);
            }
        }
        else { // IE 
            if(tipoCTO === "associada")
            {
                elemResult.value = String(area) + "C" + String(celula) + ".1B" + " até " + String(area) + "C" + String(celula) + "." + String(cto) + "B";
            }
            else{
                elemResult.value = String(area) + "C" + String(celula) + ".1" + " até " + String(area) + "C" + String(celula) + "." + String(cto);//elemResult.innerText = "O resultado é " + String(area) + "C" + String(celula) + "." + String(cto);
            }
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
    $tipoCTO = filter_input(INPUT_POST,'tipoCTO');

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
                        <?php
                            if($tipoCTO == "range")
                                include_once "./partials/_cto_range.php";
                            elseif($tipoCTO == "especifica")
                                include_once "./partials/_cto_especifica.php";
                            elseif($tipoCTO == "expansao")
                                include_once "./partials/_cto_expansao.php";
                            elseif($tipoCTO == "associada")
                                include_once "./partials/_cto_associada.php";
                        ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "../classes/html_fim.php";?>
