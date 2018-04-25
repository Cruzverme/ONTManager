<?php include "../classes/html_inicio.php"; ?>

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
                                <input class="form-control" placeholder="CTO" name="cto" type="text" autofocus required>
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