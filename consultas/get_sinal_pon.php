<?php 
  include_once "../db/db_config_mysql.php";
  
  $pon = filter_input(INPUT_POST,'frame_slot_pon');
  
  $sql = "SELECT sin.contrato,sin.cto,sin.porta_atendimento,sin.porta_pon,sin.olt,sin.sinal,sin.data_registro
          FROM todos_sinais sin
          WHERE sin.porta_pon='$pon'
          ORDER BY sin.olt,sin.porta_pon ASC";
  
  $executaSQL = mysqli_query($conectar,$sql);
  echo "
          <script>
            $(document).keyup(function(e) {
              if (e.keyCode == 27) {
                
              }
            });
          </script>
          
          <div style='' class='table-responsive'>
            <table class='table'>
            <thead>
              <tr>
                <th>Contrato</th>
                <th>Nome OLT</th>
                <th>CTO</th>
                <th>Frame-Slot-Pon</th>
                <th>Porta Atendimento</th>
                <th>Sinal RX</th>
                <th>Data de Registro</th>
              </tr>
            </thead>
            <tbody>";
    
  while($row = mysqli_fetch_array($executaSQL))
  {
    $contrato = $row['contrato'];
    $cto = $row['cto'];
    $porta_atendimento = $row['porta_atendimento'];
    $porta_pon = $row['porta_pon'];
    $olt = $row['olt'];
    $sinal = $row['sinal'];
    $data = $row['data_registro'];
    
    echo"        
          <tr>    
            <td>$contrato</td>
            <td>$olt</td>
            <td>$cto</td>
            <td>$porta_pon</td>
            <td>$porta_atendimento</td>
            <td>$sinal</td>
            <td>$data</td>
          </tr>";
  }
  echo "
          </tbody>
        </table>
      </div>";
?>