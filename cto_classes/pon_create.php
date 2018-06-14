<?php 
  include "../classes/html_inicio.php"; 

  if($_SESSION["cadastrar_olt"] == 0) {
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
                    <h3 class="panel-title">Cadastro de PON</h3>
                </div>
                <div class="panel-body">
                    <form role="form" action="../classes/cadastrar_pon.php" method="post">
                      <div class="form-group">
                        <label>Nome</label> 
                        <input class="form-control" placeholder="Nome do Dispositivo" name="nomeDev" type="text" pattern="[a-zA-Z0-9._%.-%]+" title="Sem Caracteres Especiais ou utilização de espaço" autofocus required>
                      </div>
                      <div class="form-group">
                        <label>IP da OLT</label>
                        <input class="form-control" placeholder="IP da OLT" name="ipOLT" type="text" pattern="[0-9]+.[0-9]+.[0-9]+.[0-9]+" title="Digite o IP no formato: Ex. 10.10.10.2" autofocus required>
                      </div>
                      <div class="form-group">
                        <label>Frame</label> 
                        <input class="form-control" placeholder="FRAME" name="frame" type="number" min=0 autofocus required>
                      </div>
                      <div class="form-group">
                        <label>Slot</label> 
                        <input class="form-control" placeholder="SLOT" name="slot" type="number" min=0 autofocus required>
                      </div>
                      <div class="form-group">
                        <label>Quantidade de Portas</label>
                        <select class="form-control" name="porta" required>
                          <option>4</option>
                          <option>8</option>
                          <option>16</option>
                        </select>
                        <!-- <input class="form-control" placeholder="Quantidade de Portas" name="porta" min=1 type="number" autofocus required> -->
                      </div>
                      <button class="btn btn-lg btn-success btn-block">Cadastrar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "../classes/html_fim.php";?>