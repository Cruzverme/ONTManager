<?php

include "../classes/html_inicio.php";
include_once "../db/db_config_mysql.php";

if ($_SESSION["alterar_usuario"] == 0) {
    echo '
    <script language= "JavaScript">
      alert("Sem Permissão de Acesso!");
      location.href="../classes/redirecionador_pagina.php";
    </script>
    ';
}

$usuario = filter_input(INPUT_GET, 'user');

$select_info_user = "SELECT *
      FROM usuarios usuario
      INNER JOIN usuario_permissao permissao ON permissao.usuario = usuario.usuario_id
      WHERE usuario.usuario = '$usuario'";

$execute_info_users = mysqli_query($conectar, $select_info_user);
$user_detail = mysqli_fetch_array($execute_info_users, MYSQLI_ASSOC);

$nome = $user_detail['nome'];
$cadastrarONT = $user_detail['cadastrar_onu'];
$cadastrarCorporativo = $user_detail['cadastrar_onu_corp'];
$senha = $user_detail['senha'];
$deletarONT = $user_detail['deletar_onu'];
$alterarONT = $user_detail['modificar_onu'];
$desativarONT = $user_detail['desativar_ativar_onu'];
$cadastrarCTO = $user_detail['cadastrar_cto'];
$cadastrarOLT = $user_detail['cadastrar_olt'];
$cadastrarIP = $user_detail['cadastrar_ip'];
$cadastrarVelocidade = $user_detail['cadastrar_velocidade'];
$cadastrarEquipamento = $user_detail['cadastrar_equipamento'];
$cadastrarUsuario = $user_detail['cadastrar_usuario'];
$alterarMAC = $user_detail['alterar_mac_ont'];
$consultaONT = $user_detail['consulta_ont'];
$consultaCTO = $user_detail['consulta_cto'];
$deletarCTO = $user_detail['remover_cto'];
$deletarOLT = $user_detail['remover_olt'];
$alterar_usuario = $user_detail['alterar_usuario'];
$consulta_relatorio_sinal = $user_detail['relatorio_sinal'];
$transferir_celula = $user_detail['transferir_celula'];
$gerenciar_l2l = $user_detail['gerenciar_l2l'];
$consultaLog = $user_detail['consulta_log'];
$blockCustomerChanges = $user_detail['block_customer_changes'];
$allowIpChange = $user_detail['allow_ip_change'];
?>

    <div id="page-wrapper">
        <div class="container">
            <div class="row">
                <div class="col-md-4 col-md-offset-2">
                    <div class="login-panel panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">Editar Usuário <?php echo $nome; ?></h3>
                        </div>
                        <div class="panel-body">
                            <form role="form" action="../classes/alterar_permissao.php" method="post"
                                  name="formAlterar">
                                <div class="form-group">
                                    <label>Usuario</label>
                                    <input class="form-control" placeholder="Usuario" name="usuario"
                                           value="<?php echo $usuario; ?>" type="text" autofocus readonly>
                                </div>
                                <div class="form-group">
                                    <label>Nome do Usuário</label>
                                    <input class="form-control" placeholder="Nome do Usuario" name="nome"
                                           value="<?php echo $nome; ?>" type="text" autofocus>
                                </div>
                                <div class="form-group">
                                    <label>Nova Senha</label>
                                    <input class="form-control" placeholder="senha" name="senha" type="password"
                                           id="senha" autofocus>
                                </div>
                                <div class="form-group">
                                    <label>Repita a Nova Senha</label>
                                    <input class="form-control" placeholder="senha" name="senha" type="password"
                                           id="confirma_senha" autofocus>
                                </div>

                                <fieldset>
                                    <legend>Permissões</legend>
                                    <div class="form-group">
                                        <input name="personalizada1" value=1
                                               type="checkbox"<?php if ($cadastrarONT == 1) echo "checked"; ?>>
                                        Cadastrar ONT<br/>
                                        <input name="personalizada2" value=2
                                               type="checkbox" <?php if ($alterarONT == 1) echo "checked"; ?>> Modificar
                                        ONT<br/>
                                        <input name="personalizada3" value=3
                                               type="checkbox" <?php if ($deletarONT == 1) echo "checked"; ?>> Deletar
                                        ONT<br/>
                                        <input name="personalizada4" value=4
                                               type="checkbox" <?php if ($cadastrarCTO == 1) echo "checked"; ?>>
                                        Cadastrar CTO<br/>
                                        <input name="personalizada5" value=5
                                               type="checkbox" <?php if ($desativarONT == 1) echo "checked"; ?>>
                                        Desabilitar e Habilitar
                                        ONT<br/>
                                        <input name="personalizada6" value=6
                                               type="checkbox" <?php if ($cadastrarEquipamento == 1) echo "checked"; ?>>
                                        Cadastrar
                                        Equipamento<br/>
                                        <input name="personalizada7" value=7
                                               type="checkbox" <?php if ($cadastrarOLT == 1) echo "checked"; ?>>
                                        Cadastrar OLT<br/>
                                        <input name="personalizada8" value=8
                                               type="checkbox" <?php if ($cadastrarVelocidade == 1) echo "checked"; ?>>
                                        Cadastrar
                                        Velocidade<br/>
                                        <input name="personalizada9" value=9
                                               type="checkbox" <?php if ($cadastrarUsuario == 1) echo "checked"; ?>>
                                        Cadastrar
                                        Usuários<br/>
                                        <input name="personalizada10" value=10
                                               type="checkbox" <?php if ($alterarMAC == 1) echo "checked"; ?>> Alterar
                                        Mac de ONT<br/>
                                        <input name="personalizada11" value=11
                                               type="checkbox" <?php if ($consultaONT == 1) echo "checked"; ?>> Consulta
                                        de ONU <br/>
                                        <input name="personalizada12" value=12
                                               type="checkbox" <?php if ($consultaCTO == 1) echo "checked"; ?>> Consulta
                                        de CTO <br/>
                                        <input name="personalizada13" value=13
                                               type="checkbox" <?php if ($deletarCTO == 1) echo "checked"; ?>> Remover
                                        de CTO <br/>
                                        <input name="personalizada14" value=14
                                               type="checkbox" <?php if ($deletarOLT == 1) echo "checked"; ?>> Remover
                                        de OLT <br/>
                                        <input name="personalizada15" value=15
                                               type="checkbox" <?php if ($alterar_usuario == 1) echo "checked"; ?>>
                                        Alterar e Listar
                                        Usuário <br/>
                                        <input name="personalizada16" value=16
                                               type="checkbox" <?php if ($consulta_relatorio_sinal == 1) echo "checked"; ?>>
                                        Consultar
                                        Relatório de Sinal <br/>
                                        <input name="personalizada17" value=17
                                               type="checkbox" <?php if ($transferir_celula == 1) echo "checked"; ?>>
                                        Transferir Celula
                                        <br/>
                                        <input name="personalizada18" value=18
                                               type="checkbox" <?php if ($cadastrarCorporativo == 1) echo "checked"; ?>>
                                        Cadastrar
                                        Corporativo L2L <br/>
                                        <input name="personalizada19" value=19
                                               type="checkbox" <?php if ($cadastrarIP == 1) echo "checked"; ?>>
                                        Cadastrar IP <br/>
                                        <input name="personalizada20" value=20
                                               type="checkbox" <?php if ($gerenciar_l2l == 1) echo "checked"; ?>>
                                        Gerenciar L2L <br/>
                                        <input name="personalizada21" value=21
                                               type="checkbox" <?php if ($consultaLog == 1) echo "checked"; ?>>
                                        Consultar Log <br/>
                                        <input name="personalizada22" value=22
                                               type="checkbox" <?php if ($blockCustomerChanges == 1) echo "checked"; ?>>
                                        Bloquear Mudança de Contrato <br/>
                                        <input name="personalizada23" value=23
                                               type="checkbox" <?php if ($allowIpChange == 1) echo "checked"; ?>>
                                        Permitir Mudança de IP <br/>
                                    </div>
                                </fieldset>
                                <div class="form-group">
                                    <input class="btn" name='botao_validador' value='alterar' type="submit"
                                           value="Alterar">
                                    <input class="btn" name='botao_validador' value='remover' type="submit"
                                           value="Alterar">
                                </div>
                            </form>
                            <p>
                                <button class='btn' onClick="window.location.href='alteracao_usuario.php';">Voltar
                                </button>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php mysqli_close($conectar);
include "../classes/html_fim.php"; ?>