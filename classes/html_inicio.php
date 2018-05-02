<?php

echo '
<!DOCTYPE html>
<html lang="pt">
';
    
    // Verificador de sessão 
    include "../classes/verifica_sessao.php"; 

    #capturar mensagem
    if(isset($_SESSION['menssagem']) && !empty($_SESSION['menssagem']))
    {
      print "<script>alert(\"{$_SESSION['menssagem']}\")</script>";
      unset( $_SESSION['menssagem'] );
    }
    
echo '
    <head>';
        include_once "head.php";
echo '   </head>
    
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
                <a class="navbar-brand" href="#">Gerenciador ONT</a>
            </div>
            <!-- /.navbar-header -->

            <ul class="nav navbar-top-links navbar-right">

                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                        <i class="fa fa-user fa-fw"></i>'; echo $_SESSION["nome_usuario"]; echo ' <i class="fa fa-caret-down"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-user">
                        <!--<li><a href="#"><i class="fa fa-user fa-fw"></i> User Profile</a>
                        </li> -->
                        <li><a href="../users/usuario_edit.php"><i class="fa fa-gear fa-fw"></i> Alterar Senha</a>
                        </li>
                        <li class="divider"></li>
                        <li><a href="../classes/logout.php"><i class="fa fa-sign-out fa-fw"></i> Logout</a>
                        </li>
                    </ul>
                    <!-- /.dropdown-user -->
                </li>
                <!-- /.dropdown -->
                
            </ul>
        </nav>

                <div class="navbar-default sidebar" role="navigation">
                <div class="sidebar-nav navbar-collapse">
                    <ul class="nav" id="side-menu">';
echo '                  <li>
                            <a href="../ont_classes/ont_register.php"><i class="fa fa-cloud-upload fa-fw"></i> Cadastrar ONT</a>
                        </li>
                        <li>
                            <a href="../ont_classes/ont_change.php"><i class="fa fa-wrench fa-fw"></i> Alterar ONT</a>
                        </li>
                        <li>
                            <a href="../ont_classes/ont_delete.php"><i class="fa fa-ban fa-fw"></i> Remover ONT</a>
                        </li>';
            if($_SESSION["nivel_usuario"] > 0)
            {
              echo '  <li>
                          <a href="../cto_classes/cto_create.php"><i class="fa fa-columns fa-fw"></i> Cadastrar CTO</a>
                      </li>
                      <li>
                          <a href="../cto_classes/pon_create.php"><i class="fa fa-road fa-fw"></i> Cadastrar de PON </a>
                      </li>
                      <li>
                          <a href="../users/usuario_new.php"><i class="fa fa-users fa-fw"></i> Cadastrar Usuario</a>
                      </li>';
            }
            echo'   </ul>
                </div>
                <!-- /.sidebar-collapse -->
        </div>
        <!-- /.navbar-static-side -->
        ';
?>