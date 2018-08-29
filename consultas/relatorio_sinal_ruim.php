<?php 
    
  include "../classes/html_inicio.php"; 
  include_once "../db/db_config_mysql.php";
  
  if($_SESSION["consulta_relatorio_sinal"] == 0) 
  {
    echo '
    <script language= "JavaScript">
      alert("Sem Permissão de Acesso!");
      location.href="../classes/redirecionador_pagina.php";
    </script>
    ';
  }

  $select_sinais = "SELECT contrato,cto,porta_atendimento,sinal,sinalByOLT,sinalTX,data_registro FROM sinais_diarios order  by cto DESC";
  $executa_select = mysqli_query($conectar,$select_sinais);

?>

  <div id="page-wrapper">
    <div class="container">
      <div class="row">
        <div class="col-md-11 col-md-offset-0">
          <div class="login-panel panel panel-default">
            <div class="panel-heading">
              <h3 class="panel-title">Consulta de OLT e CTO</h3>
            </div>
            <div class="panel-body">

            <div class='table-responsive'>
              <table class='table'>
                <thead>
                  <tr>
                    <th>CONTRATO</th>
                    <th>CTO-Porta de Atendimento</th>
                    <th>Sinal RX</th>
                    <th>Sinal TX</th>
                    <th>Sinal ONT para OLT</th>
                    <th>Data Registro</th>
                  </tr>
                </thead>
                
                <tbody>
                <?php
                  while($linha = mysqli_fetch_array($executa_select,MYSQLI_BOTH))
                  {
                    $contrato = $linha[0];
                    $cto = $linha[1];
                    $porta_atendimento = $linha[2];
                    $sinalRX = $linha[3];
                    $sinalRXByOLT = $linha[4];
                    $sinalTX = $linha[5];
                    $data_registro = $linha[6];

                    echo "
                    <tr>
                      <td> $contrato </td>
                      <td> $cto-$porta_atendimento </td>";
              if(-2500 >= $sinalRX) 
                echo "<td style=color:#990000> $sinalRX </td>";
              else
                echo "<td> $sinalRX </td>" ;  //fim else
                echo "<td> $sinalTX </td>";
              if(-3000 >= $sinalRXByOLT)
                echo "<td style=color:#990000> $sinalRXByOLT </td>";
              else
                echo "<td> $sinalRXByOLT</td>"; //fim else
                echo "<td> $data_registro</td>";
              echo "</tr>
                    ";
                  }
                
                ?>
                  
                </tbody>

              </table>
            </div>


            </div><!-- fim panel -->
          </div>
        </div><!-- fim col -->
      </div><!-- fim row -->
    </div><!-- fim container -->  
  </div> <!-- fim pagewrapper -->

<?php include "../classes/html_fim.php"; ?>
