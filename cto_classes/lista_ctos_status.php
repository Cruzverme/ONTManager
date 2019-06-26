<?php 
  include "../classes/html_inicio.php"; 
  include_once "../db/db_config_mysql.php";


  if($_SESSION["cadastrar_olt"] == 0) {
    echo '
    <script language= "JavaScript">
      alert("Sem Permissão de Acesso!");
      location.href="../classes/redirecionador_pagina.php";    
    </script>
    ';
  }

  $olt = filter_input(INPUT_POST,'olt');

  $sql_ctos =  "SELECT DISTINCT caixa_atendimento,disponivel FROM ctos WHERE pon_id_fk = $olt";
  $executa_query = mysqli_query($conectar,$sql_ctos);
?>

<div id="page-wrapper">
  <div class="container">
    <div class="row">
      <div class="col-md-4 col-md-offset-4">
        <div class="login-panel panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title">Lista de Celulas</h3>
          </div>
          <div class="panel-body">
            <form action="#" method="post">
              <div class='table-responsive'>
                <table class='table table-hover display' id='tabelaSinais' data-link='row'>
                  <thead>
                    <tr>
                      <th>CTO</th>
                      <th>Status CTO</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php 
                      while ($ctos = mysqli_fetch_array($executa_query, MYSQLI_BOTH))
                      {
                        $ctos['disponivel'] == 1? $status = ' Disponivel': $status = ' Não Disponivel';
                        $status == ' Disponivel'? $checked = 'checked': $checked = '';
                        !$ctos['disponivel'] == 1? $status_color = 'style="background-color: red"': $status_color = 'style="background-color: green"';
                        
                        echo "
                          <tr id='linha_status_cto_disponivel_$ctos[caixa_atendimento]' class='linha_status_cto' $status_color>
                            <td class='$ctos[caixa_atendimento]'><label for=cto_libera_nome_$ctos[caixa_atendimento]>$ctos[caixa_atendimento]</label></td>
                            <td>
                              <input type=checkbox id=cto_libera_nome_$ctos[caixa_atendimento] name=status_cto value=$ctos[disponivel]_$ctos[caixa_atendimento] $checked/>
                              <label for=cto_libera_nome_$ctos[caixa_atendimento]>$status</label>
                            </td>
                          </tr>
                        ";
                      }
                      echo "</ul>";
                    ?>
                  </tbody>
                </table>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include "../classes/html_fim.php";?>