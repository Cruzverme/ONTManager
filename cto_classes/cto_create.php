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
?>

<div id="page-wrapper">

<div class="container">
    <div class="row">
        <div class="col-md-4 col-md-offset-4">
            <div class="login-panel panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Cadastro de CTO</h3>
                </div>
                <div class="panel-body">
                    <form role="form" action="../classes/cadastrar_cto.php" method="post">
                            <div class="form-group">
                                <label>Nome CTO</label> 
                                <input class="form-control" placeholder="CTO" name="cto" type="text" pattern="[a-zA-Z0-9._%]+" title="Sem Caracteres Especiais ou utilização de espaço" autofocus required>
                            </div>
                            <div class="form-group">
                              <label>PON</label> 
                              <select class="form-control" name="pon">
                                <?php 
                                  $sql_consulta_serial = "SELECT pon_id,frame,slot,porta FROM pon";
                                  $executa_query = mysqli_query($conectar,$sql_consulta_serial);
                                  while ($ont = mysqli_fetch_array($executa_query, MYSQLI_BOTH))
                                  {
                                    for($porta = 0;$porta < $ont['porta'];$porta++)
                                    {
                                      echo "<option value=$ont[pon_id]-$ont[frame]-$ont[slot]-$porta>Frame: $ont[frame] Slot: $ont[slot]  Porta: $porta </option>";
                                    }
                                  }
                                ?>
                              </select>
                            </div>
                            <div class="form-group">
                                <label>Porta</label>                                                
                                <select class="form-control" name="porta">
                                    echo "<option value=8>8</option>";
                                </select>
                            </div>                                                    
                        <button class="btn btn-lg btn-success btn-block">Cadastrar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "../classes/html_fim.php";?>