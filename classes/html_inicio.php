<!DOCTYPE html>
<html lang="pt">
<?php
// Verificador de sessão
include "../classes/verifica_sessao.php";

#capturar mensagem
if (isset($_SESSION['menssagem']) && !empty($_SESSION['menssagem'])) {
    echo "<script>alert(\"{$_SESSION['menssagem']}\")</script>";
    unset($_SESSION['menssagem']);
}
?>

<head>
    <?php include_once "head.php"; ?>
</head>

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
                    <i class="fa fa-user fa-fw"></i> <?php echo $_SESSION["nome_usuario"]; ?> <i class="fa fa-caret-down"></i>
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
            <ul class="nav" id="side-menu">
                <!-- Cadastros -->
                <?php if ($_SESSION["cadastrar_onu"] == 1 || $_SESSION["cadastrar_cto"] == 1 ||
                    $_SESSION["cadastrar_olt"] == 1 || $_SESSION["cadastrar_velocidade"] == 1 ||
                    $_SESSION["cadastrar_usuario"] == 1 || $_SESSION["cadastrar_equipamento"] == 1) : ?>
                    <li>
                        <a class="tituloSubMenu" href="#">Cadastros</a>
                        <ul class="nav" id="side-menu">
                            <?php if ($_SESSION["cadastrar_onu"] == 1) : ?>
                                <li>
                                    <a href="../ont_classes/ont_register.php"><i class="fa fa-cloud-upload fa-fw"></i> Cadastrar ONT</a>
                                </li>
                            <?php endif; ?>
                            <?php if($_SESSION["cadastrar_cto"] == 1) : ?>
                                <li>
                                    <a href="../cto_classes/show_ctos.php"><i class="fa fa-sitemap fa-fw"></i> Cadastrar CTO</a>
                                </li>
                            <?php endif; ?>
                            <?php if($_SESSION["cadastrar_olt"] == 1) : ?>
                                <li>
                                    <a href="../cto_classes/pon_create.php"><i class="fa fa-columns fa-fw"></i> Cadastro de Slot da OLT </a>
                                </li>
                            <?php endif; ?>
                            <?php if($_SESSION["cadastrar_ip"] == 1) : ?>
                                <li>
                                    <a href="../ip/new_ip.php"><i class="fa fa-rss fa-fw"></i> Cadastrar IP</a>
                                </li>
                            <?php endif; ?>
                            <?php if($_SESSION["cadastrar_velocidade"] == 1) : ?>
                                <li>
                                    <a href="../planos/planos_create.php"><i class="fa fa-wifi fa-fw"></i> Cadastrar Nova Velocidade</a>
                                </li>
                            <?php endif; ?>
                            <?php if($_SESSION["cadastrar_equipamento"] == 1) : ?>
                                <li>
                                    <a href="../equipamento/cadastro_equipamento.php"><i class="fa fa-server fa-fw"></i> Cadastrar Equipamento</a>
                                </li>
                            <?php endif; ?>
                            <?php if($_SESSION["cadastrar_usuario"] == 1) : ?>
                                <li>
                                    <a href="../users/usuario_new.php"><i class="fa fa-users fa-fw"></i> Cadastrar Usuario</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>

                <!-- Corporativo -->
                <?php if ($_SESSION["cadastrar_onu_corp"] || $_SESSION["gerenciar_l2l"]) : ?>
                <li>
                    <a class="tituloSubMenu" href="#">Clear Channel</a>
                    <ul class="nav" id="side-menu">
                        <li>
                            <a href="../clear_channel/select_cto.php">
                                <i class="fa fa-cloud-upload fa-fw"></i> Cadastrar Cliente L2L
                            </a>
                        </li>
                        <?php if ($_SESSION['gerenciar_l2l']) : ?>
                        <li>
                            <a href="../clear_channel/channel_config.php">
                                <i class="fa fa-cloud-upload fa-fw"></i> Gerenciar L2L
                            </a>
                        </li>
                        <?php endif ?>
                    </ul>
                </li>
                <?php endif; ?>

                <!-- Modificações -->
                <?php if ($_SESSION["modificar_onu"] == 1 || $_SESSION["alterar_macONT"] == 1 ||
                    $_SESSION["desativar_ativar_onu"] == 1 || $_SESSION["alterar_usuario"] == 1) : ?>
                    <li>
                        <a class="tituloSubMenu" href="#">Modificações</a>
                        <ul class="nav" id="side-menu">
                            <?php if($_SESSION["modificar_onu"] == 1) : ?>
                                <li>
                                    <a href="../ont_classes/ont_change.php"><i class="fa fa-wrench fa-fw"></i> Alterar ONT</a>
                                </li>
                            <?php endif; ?>
                            <?php if($_SESSION["alterar_macONT"] == 1) : ?>
                                <li>
                                    <a href="../ont_classes/alterar_mac_ont.php"><i class="fa fa-exchange fa-fw"></i> Trocar ONT</a>
                                </li>
                            <?php endif; ?>
                            <?php if($_SESSION["desativar_ativar_onu"] == 1) : ?>
                                <li>
                                    <a href="../ont_classes/ont_disable.php"><i class="fa fa-pause-circle fa-fw"></i> Desabilitar e Habilitar Cliente </a>
                                </li>
                            <?php endif; ?>
                            <?php if($_SESSION["alterar_usuario"] == 1) : ?>
                                <li>
                                    <a href="../users/alteracao_usuario.php"><i class="fa fa-users fa-fw"></i> Listar e Alterar Usuario</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>

                <!-- Remoções -->
                <?php if ($_SESSION["deletar_onu"] == 1 || $_SESSION["remover_cto"] == 1 || $_SESSION["remover_olt"] == 1) : ?>
                    <li>
                        <a class="tituloSubMenu" href="#">Remoções</a>
                        <ul class="nav" id="side-menu">
                            <?php if($_SESSION["deletar_onu"] == 1) : ?>
                                <li>
                                    <a href="../ont_classes/ont_delete.php"><i class="fa fa-ban fa-fw"></i> Remover ONT</a>
                                </li>
                            <?php endif; ?>
                            <?php if($_SESSION["remover_cto"] == 1) : ?>
                                <li>
                                    <a href="../cto_classes/remover_cto.php"><i class="fa fa-ban fa-fw"></i> Remover CTO</a>
                                </li>
                            <?php endif; ?>
                            <?php if($_SESSION["remover_olt"] == 1) : ?>
                                <li>
                                    <a href="../cto_classes/remover_olt.php"><i class="fa fa-ban fa-fw"></i> Remover OLT</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>

                <!-- Consultas -->
                <?php if ($_SESSION["consulta_onts"] == 1 || $_SESSION["consulta_ctos"] == 1 ||
                    $_SESSION["consulta_relatorio_sinal"] == 1 || $_SESSION["desativar_ativar_onu"] == 1) : ?>
                    <li>
                        <a class="tituloSubMenu" href="#">Consultas</a>
                        <ul class="nav" id="side-menu">
                            <?php if($_SESSION["consulta_onts"] == 1) : ?>
                                <li>
                                    <a href="../consultas/get_status.php"><i class="fa fa-info fa-fw"></i> Consulta de ONT</a>
                                </li>
                            <?php endif; ?>
                            <?php if($_SESSION["consulta_ctos"] == 1) : ?>
                                <li>
                                    <a href="../consultas/get_info_cto.php"><i class="fa fa-info fa-fw"></i> Consulta de CTO e OLT</a>
                                </li>
                            <?php endif; ?>
                            <?php if($_SESSION["cadastrar_ip"] == 1) : ?>
                                <li>
                                    <a href="../ip/show_ip.php"><i class="fa fa-rss fa-fw"></i> Exibir IPs</a>
                                </li>
                            <?php endif; ?>
                            <?php if($_SESSION["consulta_relatorio_sinal"] == 1) : ?>
                                <li>
                                    <a href="../consultas/relatorio_sinal_ruim.php"><i class="fa fa-bug fa-fw"></i> Relatório de Sinais </a>
                                </li>
                                <li>
                                    <a href="../consultas/analise_pon.php"><i class="fa fa-bug fa-fw"></i> Relatório de Sinais Por Porta Pon </a>
                                </li>
                            <?php endif; ?>
                            <?php if($_SESSION["desativar_ativar_onu"] == 1) : ?>
                                <li>
                                    <a href="../consultas/show_bloqueados.php"><i class="fa fa-bug fa-fw"></i> Clientes Inconsistentes com Cplus </a>
                                </li>
                                <li>
                                    <a href="../consultas/show_cancelados.php"><i class="fa fa-bug fa-fw"></i> Clientes Cancelados no Cplus </a>
                                </li>
                            <?php endif; ?>
                            <?php if ($_SESSION["consulta_log"]) : ?>
                                <li>
                                    <a href="../logs/show_log.php"><i class="fa fa-rss fa-fw"></i> Consultar Log</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>

               <?php if($_SESSION["gerenciar_l2l"] == 1) : ?>
                    <li class="tituloSubMenu">
                        <a href="../u2000/create_error.php"><i class="fa fa-wrench fa-fw"></i> Gerenciar Erros u2000</a>
                    </li>
                <?php endif; ?>

                <?php if($_SESSION["consulta_ctos"] == 1) :?>
                    <li class="tituloSubMenu">
                        <a href="../cto_classes/liberar_cto.php"><i class="fa fa-info fa-fw"></i> Ativar/Desativar CTO Única </a>
                    </li>
                <?php endif; ?>

                <?php if($_SESSION["transferir_celula"] == 1) :?>
                    <li class="tituloSubMenu">
                        <a href="../cto_classes/transfer_olt_select.php"><i class="fa fa-bug fa-fw"></i> Transferir Celula </a>
                    </li>
                <?php endif; ?>

                <?php if($_SESSION["transferir_cgnat"] == 1) :?>
                    <li class="tituloSubMenu">
                        <a href="../ont_classes/troca_nat.php"><i class="fa fa-bug fa-fw"></i> Migrar para CGNAT </a>
                    </li>
                <?php endif; ?>
                <?php if($_SESSION['block_customer_changes']) :?>
                    <li class="tituloSubMenu">
                        <a href="../change_blocker/blockedList.php">
                            <i class="fa fa-bug fa-fw"></i> Bloqueio de Alteração de Contrato
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
        <!-- /.sidebar-collapse -->
    </div>
    <!-- /.navbar-static-side -->
<!-- /#wrapper -->