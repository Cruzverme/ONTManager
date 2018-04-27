<?php include_once "../classes/html_inicio.php";?>
<?php include_once "../classes/html_inicio.php";?>

        <div id="page-wrapper">

            <div class="container">
                <div class="row">
                    <div class="col-md-4 col-md-offset-4">
                        <div class="login-panel panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">Remover ONT</h3>
                            </div>
                            <div class="panel-body">
                                <form role="form" action="../classes/deletar.php" method="post">
                                        <div class="form-group">
                                            <label>Contrato</label> 
                                            <input class="form-control" placeholder="Contrato" name="contrato" type="text" autofocus required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>Pon MAC</label>                                                
                                            <input class="form-control" placeholder="MAC PON" name="serial" type="text" required>
                                        </div>                                                                                                    
                                        
                                        <div class="camposTelefone" style="display:none" >                                   
                                            <div class="form-group">
                                                <label>telefone</label>
                                                <input class="form-control" placeholder="telefone" name="numeroTel" type="text" autofocus>
                                            </div>

                                            
                                            <div class="form-group">
                                                <label>password telefone</label>
                                                <input class="form-control" placeholder="password telefone" name="passwordTel" type="text" autofocus>
                                            </div>

                                            
                                            <div class="form-group">
                                                <label>userTel</label>
                                                <input class="form-control" placeholder="userTel" name="telUser" type="text" autofocus>
                                            </div>
                                        </div>

                                    <button class="btn btn-lg btn-success btn-block">Remover</button>

                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
<?php include_once "../classes/html_fim.php";   ?>