<?php include "../classes/html_inicio.php"; ?>

    <div id="page-wrapper">
        <div class="container">
            <div class="row">
                <div class="col-md-4 col-md-offset-4">
                    <div class="login-panel panel panel-default">

                        <div class="panel-heading">
                            <h3 class="panel-title">Cadastro de Planos</h3>
                        </div>

                        <div class="panel-body">
                            <form role="form" action="../classes/cadastra_planos.php" method="post">
                                <div class="form-group">
                                    <label>Nome da Velocidade</label>
                                    <input class="form-control" placeholder="Nome da Velocidade"
                                           name="nome_velocidade" type="text" autofocus required>
                                </div>
                                <div class="form-group">
                                    <label>Codigo do Control Plus</label>
                                    <input class="form-control" placeholder="Codigo do Control Plus"
                                           name="codigoCplus" type="text" autofocus required
                                           oninput="validateNumericInput(this)">
                                </div>
                                <hr/>
                                <div class="form-group">
                                    <label>Qual Plano</label>
                                    <div class="radio">
                                        <label>
                                            <input type="radio" name="optionTipoVelocidade" id="optionsRadios1"
                                                   value="RESID_??M" required>Residencial
                                        </label>
                                        <label>
                                            <input type="radio" name="optionTipoVelocidade" id="optionsRadios1"
                                                   value="CORPF_??M">Corporativo
                                        </label>
                                        <label>
                                            <input type="radio" name="optionTipoVelocidade" id="optionsRadios1"
                                                   value="CORPL_??M">Corporativo Light
                                        </label>
                                    </div>
                                </div>
                                <hr/>
                                <div class="form-group">
                                    <label>Velocidade de Download</label>
                                    <input class="form-control" placeholder="Velocidade Download in MB ex.: 10"
                                           name="velocidade_download" type="number" min=0 autofocus required>
                                </div>

                                <div class="form-group">
                                    <label>Velocidade de Upload</label>
                                    <input class="form-control" placeholder="Velocidade Upload in MB ex.: 25"
                                           name="velocidade_upload" type="number" min=0 step=any autofocus required>
                                </div>
                                <button class="btn btn-lg btn-success btn-block">Cadastrar</button>
                            </form>
                        </div> <!-- end panel-body-->
                    </div> <!-- end login panel -->
                </div> <!-- end col -->
            </div> <!-- end row -->
        </div> <!-- end container -->
    </div> <!-- end page wrapper  -->

<?php include "../classes/html_fim.php"; ?>