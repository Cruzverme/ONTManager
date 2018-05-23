<?php include "../classes/html_inicio.php"; ?>

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
                        <input class="form-control" placeholder="Nome do Dispositivo" name="frame" type="text" autofocus required>
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
                        <input class="form-control" placeholder="Quantidade de Portas" name="porta" min=0 type="number" autofocus required>
                      </div>
                      <button class="btn btn-lg btn-success btn-block">Cadastrar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "../classes/html_fim.php";?>