<!DOCTYPE html>
<html lang="pt">

    <?php 
        // Verificador de sessão 
        include "classes/verifica_sessao.php"; 

        #capturar mensagem
        if(isset($_SESSION['menssagem']) && !empty($_SESSION['menssagem']))
        {
            print "<script>alert(\"{$_SESSION['menssagem']}\")</script>";
            unset( $_SESSION['menssagem'] );
        }
    ?>

    <head>
        <?php include_once "classes/head.php"; ?>
    </head>

    <?php require_once "db/db_config_mysql.php"; ?>
    
    <body>               
    <div id="wrapper">

        <!-- Navigation -->
        <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="index.html">Gerenciador ONT</a>
            </div>
            <!-- /.navbar-header -->

            <ul class="nav navbar-top-links navbar-right">

                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                        <i class="fa fa-user fa-fw"></i> <?php echo $_SESSION["nome_usuario"]; ?> <i class="fa fa-caret-down"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-user">
                        <li><a href="#"><i class="fa fa-user fa-fw"></i> User Profile</a>
                        </li>
                        <li><a href="#"><i class="fa fa-gear fa-fw"></i> Settings</a>
                        </li>
                        <li class="divider"></li>
                        <li><a href="classes/logout.php"><i class="fa fa-sign-out fa-fw"></i> Logout</a>
                        </li>
                    </ul>
                    <!-- /.dropdown-user -->
                </li>
                <!-- /.dropdown -->
                
            </ul>
        </nav>

                <div class="navbar-default sidebar" role="navigation">
                <div class="sidebar-nav navbar-collapse">
                    <ul class="nav" id="side-menu">
                        <li>
                            <a href="ont_register.php"><i class="fa fa-table fa-fw"></i> Cadastrar ONT</a>
                        </li>
                        <li>
                            <a href="ont_change.php"><i class="fa fa-table fa-fw"></i> Alterar ONT</a>
                        </li>
                        <li>
                            <a href="ont_delete.php"><i class="fa fa-table fa-fw"></i> Remover ONT</a>
                        </li>
                    </ul>
                </div>
                <!-- /.sidebar-collapse -->
        </div>
        <!-- /.navbar-static-side -->

        <div id="page-wrapper">

            <div class="container">
                <div class="row">
                    <div class="col-md-4 col-md-offset-4">
                        <div class="login-panel panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">Cadastro de ONT</h3>
                            </div>
                            <div class="panel-body">
                                <form role="form" action="classes/cadastrar.php" method="post">
                                    <fieldset>
                                        <div class="form-group">
                                                <label>Qual Plano</label>
                                                <div class="radio">
                                                    <label>
                                                        <input type="radio" name="optionsRadios" id="optionsRadios1" value="option1" checked>INTERNET
                                                    </label>
                                                </div>
                                                <div class="radio">
                                                    <label>
                                                        <input type="radio" name="optionsRadios" id="optionsRadios2" value="option2">IPTV
                                                    </label>
                                                </div>
                                                <div class="radio">
                                                    <label>
                                                        <input type="radio" name="optionsRadios" id="optionsRadios3" value="Sim">INTERNET | VOIP | IPTV
                                                    </label>
                                                </div>
                                                <div class="radio">
                                                    <label>
                                                        <input type="radio" name="optionsRadios" id="optionsRadios3" value="option3">INTERNET | IPTV
                                                    </label>
                                                </div>
                                                <div class="radio">
                                                    <label>
                                                        <input type="radio" name="optionsRadios" id="optionsRadios3" value="Sim">INTERNET | VOIP
                                                    </label>
                                                </div>
                                        </div>
                                    </fieldset>

                                    <fieldset>
                                        <div class="form-group">
                                            <label>Contrato</label> 
                                            <input class="form-control" placeholder="Contrato" name="contrato" type="text" autofocus>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>Pon MAC</label>                                                
                                            <input class="form-control" placeholder="MAC PON" name="serial" type="text">
                                        </div>
                                        <div class="form-group">
                                            <?php include "classes/listaPlanos.php" ?>
                                            <label>Pacote</label>
                                            <select class="form-control" name="pacote">
                                                <?php 
                                                    foreach($listaPlanosInternet as $planoInternet) 
                                                    {
                                                        echo "<option value='$planoInternet'>$planoInternet</option>"; 
                                                    }                                                
                                                ?>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>CTO</label>
                                            <input class="form-control" placeholder="CTO" name="cto" type="text" autofocus>
                                        </div>
                                    
                                        <div class="form-group">
                                            <label>Porta Atendimento</label>
                                            <input class="form-control" placeholder="1" name="porta" type="number" autofocus>
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

                                    </fieldset>
                                    <button class="btn btn-lg btn-success btn-block">Cadastrar</button>

                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>



        <!-- jQuery -->
        <script src="vendor/jquery/jquery.min.js"></script>

        <!-- Bootstrap Core JavaScript -->
        <script src="vendor/bootstrap/js/bootstrap.min.js"></script>

        <!-- Metis Menu Plugin JavaScript -->
        <script src="vendor/metisMenu/metisMenu.min.js"></script>

        <!-- Custom Theme JavaScript -->
        <script src="dist/js/sb-admin-2.js"></script>

        <!-- Metis Menu Plugin JavaScript -->
        <script src="vendor/vertv/vertv.js"></script>

    </body>

    </div>


</html>
