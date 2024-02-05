<?php
include_once "../db/db_config_mysql.php";
session_start();

const CODIGO_CADASTRAR_ONU = 1;
const CODIGO_MODIFICAR_ONU = 2;
const CODIGO_DELETAR_ONU = 3;
const CODIGO_CADASTRAR_CTO = 4;
const CODIGO_DESABILITAR_HABILITAR = 5;
const CODIGO_CADASTRAR_EQUIPAMENTO = 6;
const CODIGO_CADASTRAR_OLT = 7;
const CODIGO_CADASTRAR_VELOCIDADE = 8;
const CODIGO_CADASTRAR_USUARIOS = 9;
const CODIGO_ALTERAR_MAC = 10;
const CODIGO_CONSULTAR_ONU = 11;
const CODIGO_CONSULTAR_CTO = 12;
const CODIGO_REMOVER_CTO = 13;
const CODIGO_REMOVER_OLT = 14;
const CODIGO_LISTAR_USUARIO = 15;
const CODIGO_RELATORIO_SINAL = 16;
const CODIGO_TRANSFERENCIA_CELULA = 17;
const CODIGO_CADASTRO_CORPORATIVO = 18;
const CODIGO_CADASTRO_IP = 19;
const CODIGO_GERENCIA_L2L = 20;
const CODIGO_CONSULTA_LOG = 21;
const CODIGO_BLOQUEIO_ALTERACAO_CONTRATO = 22;
const CODIGO_PERMITIR_ALTERACAO_IP = 23;

function checkPermission($userPermission, $permissionCode)
{
    return ($userPermission == $permissionCode) ? 1 : 0;
}

function redirectToPage($message, $location)
{
    $_SESSION['menssagem'] = $message;
    header("Location: $location");
    exit;
}

if (!mysqli_connect_errno()) {
    $usuario_logado = $_SESSION["id_usuario"];
    $usuario = filter_input(INPUT_POST, 'usuario');
    $botao = filter_input(INPUT_POST, 'botao_validador');

    if ($botao == 'remover') {
        $sql_remove = "DELETE FROM usuarios WHERE usuario = ?";
        $stmt_remove = mysqli_prepare($conectar, $sql_remove);
        mysqli_stmt_bind_param($stmt_remove, "s", $usuario);
        $execute_remove = mysqli_stmt_execute($stmt_remove);

        if ($execute_remove) {
            $sql_insert_log = "INSERT INTO log (registro, codigo_usuario) 
                            VALUES (?, ?)";
            $stmt_log = mysqli_prepare($conectar, $sql_insert_log);
            $registro_log = "Usuário $usuario Removido Pelo Usuário de Código $usuario_logado";
            mysqli_stmt_bind_param($stmt_log, "si", $registro_log, $usuario_logado);
            mysqli_stmt_execute($stmt_log);

            redirectToPage("Usuário Excluído!", "../users/alteracao_usuario.php");
        } else {
            $erro = mysqli_error($conectar);
            redirectToPage("Houve erro ao Remover o Usuário: $erro", "../users/alteracao_usuario.php");
        }
    } else {
        $senha = filter_input(INPUT_POST,'senha');
        $nome = filter_input(INPUT_POST,'nome');

        //variaveis de permissao
        $cadastrarONU = filter_input(INPUT_POST,'personalizada1') ?? 0;
        $modificarONU = filter_input(INPUT_POST,"personalizada2") ?? 0;
        $deletarONU = filter_input(INPUT_POST,"personalizada3") ?? 0;
        $cadastrarCTO = filter_input(INPUT_POST,"personalizada4") ?? 0;
        $desabilitarHabilitarONU = filter_input(INPUT_POST,"personalizada5") ?? 0;
        $cadastrarEquipamento = filter_input(INPUT_POST,"personalizada6") ?? 0;
        $cadastrarOLT = filter_input(INPUT_POST,"personalizada7") ?? 0;
        $cadastrarVelocidade = filter_input(INPUT_POST,"personalizada8") ?? 0;
        $cadastrarUsuarios = filter_input(INPUT_POST,"personalizada9") ?? 0;
        $alterarMacONT = filter_input(INPUT_POST,"personalizada10") ?? 0;
        $consulta_onu = filter_input(INPUT_POST,"personalizada11") ?? 0;
        $consulta_cto = filter_input(INPUT_POST,"personalizada12") ?? 0;
        $remover_cto = filter_input(INPUT_POST,"personalizada13") ?? 0;
        $remover_olt = filter_input(INPUT_POST,"personalizada14") ?? 0;
        $alterar_usuario = filter_input(INPUT_POST,"personalizada15") ?? 0;
        $consulta_relatorio_sinal = filter_input(INPUT_POST,"personalizada16") ?? 0;
        $transferir_celula = filter_input(INPUT_POST,"personalizada17") ?? 0;
        $cadastrar_corporativo = filter_input(INPUT_POST,"personalizada18") ?? 0;
        $cadastrar_ip = filter_input(INPUT_POST,"personalizada19") ?? 0;
        $gerenciar_l2l = filter_input(INPUT_POST, "personalizada20") ?? 0;
        $consulta_log = filter_input(INPUT_POST,"personalizada21") ?? 0;
        $blockCustomerChanges = filter_input(INPUT_POST,"personalizada22") ?? 0;
        $allowIpChange = filter_input(INPUT_POST,"personalizada23") ?? 0;

        //fim variaveis de permissao

        ########## permissoes personalizadas ########

        $permitir_cadastrar_ONU = checkPermission($cadastrarONU, CODIGO_CADASTRAR_ONU);
        $permitir_alterarONU = checkPermission($modificarONU, CODIGO_MODIFICAR_ONU);
        $permitir_removerONU = checkPermission($deletarONU, CODIGO_DELETAR_ONU);
        $permitir_cadastrarCTO = checkPermission($cadastrarCTO, CODIGO_CADASTRAR_CTO);
        $permitir_desabilitarHabilitar = checkPermission($desabilitarHabilitarONU, CODIGO_DESABILITAR_HABILITAR);
        $permitir_cadastrarEquipamento = checkPermission($cadastrarEquipamento, CODIGO_CADASTRAR_EQUIPAMENTO);
        $permitir_cadastrarOLT = checkPermission($cadastrarOLT, CODIGO_CADASTRAR_OLT);
        $permitir_cadastrarVelocidade = checkPermission($cadastrarVelocidade, CODIGO_CADASTRAR_VELOCIDADE);
        $permitir_cadastrarUsuarios = checkPermission($cadastrarUsuarios, CODIGO_CADASTRAR_USUARIOS);
        $permitir_alterar_MAC = checkPermission($alterarMacONT, CODIGO_ALTERAR_MAC);
        $permitir_consulta_onu = checkPermission($consulta_onu, CODIGO_CONSULTAR_ONU);
        $permitir_consulta_cto = checkPermission($consulta_cto, CODIGO_CONSULTAR_CTO);
        $permitir_removerCTO = checkPermission($remover_cto, CODIGO_REMOVER_CTO);
        $permitir_removerOLT = checkPermission($remover_olt, CODIGO_REMOVER_OLT);
        $permitir_listar_usuario = checkPermission($alterar_usuario, CODIGO_LISTAR_USUARIO);
        $permitir_relatorio_sinal = checkPermission($consulta_relatorio_sinal, CODIGO_RELATORIO_SINAL);
        $permitir_transferencia_celula = checkPermission($transferir_celula, CODIGO_TRANSFERENCIA_CELULA);
        $permitir_cadastro_corporativo = checkPermission($cadastrar_corporativo, CODIGO_CADASTRO_CORPORATIVO);
        $permitir_cadastro_ip = checkPermission($cadastrar_ip, CODIGO_CADASTRO_IP);
        $permitir_gerenciar_l2l = checkPermission($gerenciar_l2l, CODIGO_GERENCIA_L2L);
        $permitir_consulta_log = checkPermission($consulta_log, CODIGO_CONSULTA_LOG);
        $permitir_bloqueio_alteracao_contrato = checkPermission($blockCustomerChanges, CODIGO_BLOQUEIO_ALTERACAO_CONTRATO);
        $permitir_alteracao_ip = checkPermission($allowIpChange, CODIGO_PERMITIR_ALTERACAO_IP);
        #######  FIM PERMISSOES PERSONALIZADAS ########
        // Consulta preparada para atualizar informações do usuário
        if ($senha == null) {
            $sql_altera_usuario = "UPDATE usuarios SET nome=? WHERE usuario=?";
            $stmt_altera_usuario = mysqli_prepare($conectar, $sql_altera_usuario);
            mysqli_stmt_bind_param($stmt_altera_usuario, "ss", $nome, $usuario);
        } else {
            $senhaCript = md5($senha);
            $sql_altera_usuario = "UPDATE usuarios SET nome=?, senha=? WHERE usuario=?";
            $stmt_altera_usuario = mysqli_prepare($conectar, $sql_altera_usuario);
            mysqli_stmt_bind_param($stmt_altera_usuario, "sss", $nome, $senhaCript, $usuario);
        }
        $cadastrar = mysqli_stmt_execute($stmt_altera_usuario);
        if ($cadastrar) {
            $sql_get_userID = "SELECT usuario_id FROM usuarios WHERE usuario=?";
            $stmt_get_userID = mysqli_prepare($conectar, $sql_get_userID);
            mysqli_stmt_bind_param($stmt_get_userID, "s", $usuario);
            mysqli_stmt_execute($stmt_get_userID);
            mysqli_stmt_store_result($stmt_get_userID);

            mysqli_stmt_bind_result($stmt_get_userID, $userID);
            mysqli_stmt_fetch($stmt_get_userID);

            $sql_cadastrar_permissao = "UPDATE usuario_permissao SET 
                cadastrar_onu = ?,
                cadastrar_onu_corp = ?,
                deletar_onu = ?,
                modificar_onu = ?,
                desativar_ativar_onu = ?,
                cadastrar_cto = ?,
                cadastrar_olt = ?,
                cadastrar_velocidade = ?,
                cadastrar_usuario = ?,
                cadastrar_equipamento = ?,
                alterar_mac_ont = ?,
                consulta_ont = ?,
                consulta_cto = ?,
                remover_cto = ?,
                remover_olt = ?,
                alterar_usuario = ?,
                relatorio_sinal = ?,
                transferir_celula = ?,
                cadastrar_ip = ?,
                gerenciar_l2l = ?,
                consulta_log = ?,
                block_customer_changes = ?,
                allow_ip_change = ?
                WHERE usuario = ?";

            $stmt_cadastrar_permissao = mysqli_prepare($conectar, $sql_cadastrar_permissao);
            mysqli_stmt_bind_param($stmt_cadastrar_permissao, "iiiiiiiiiiiiiiiiiiiiiiii",
                $permitir_cadastrar_ONU,
                $permitir_cadastro_corporativo,
                $permitir_removerONU,
                $permitir_alterarONU,
                $permitir_desabilitarHabilitar,
                $permitir_cadastrarCTO,
                $permitir_cadastrarOLT,
                $permitir_cadastrarVelocidade,
                $permitir_cadastrarUsuarios,
                $permitir_cadastrarEquipamento,
                $permitir_alterar_MAC,
                $permitir_consulta_onu,
                $permitir_consulta_cto,
                $permitir_removerCTO,
                $permitir_removerOLT,
                $permitir_listar_usuario,
                $permitir_relatorio_sinal,
                $permitir_transferencia_celula,
                $permitir_cadastro_ip,
                $permitir_gerenciar_l2l,
                $permitir_consulta_log,
                $permitir_bloqueio_alteracao_contrato,
                $permitir_alteracao_ip,
                $userID
            );

            $permissoes = mysqli_stmt_execute($stmt_cadastrar_permissao);

            if ($permissoes) {
                $sql_insert_log = "INSERT INTO log (registro,codigo_usuario) 
                    VALUES ('Usuario $usuario Alterado Pelo Usuario de Codigo $usuario_logado','$usuario_logado')";
                $executa_log = mysqli_query($conectar,$sql_insert_log);
                $_SESSION['menssagem'] = "Informações Alteradas!";
                header('Location: ../users/alteracao_usuario.php');
                mysqli_close($conectar);
                exit;
            } else {
                $erro = mysqli_error($conectar);
                $sql_insert_log = "INSERT INTO log (registro,codigo_usuario) 
                    VALUES ('Usuario $usuario Alterado Sem Permissões Pelo Usuario de Codigo $usuario_logado', 'erro: $erro','$usuario_logado')";
                $executa_log = mysqli_query($conectar, $sql_insert_log);
                $_SESSION['menssagem'] = "Informações Alteradas, porém sem alterar permissões! $erro";
                header('Location: ../users/alteracao_usuario.php');
                mysqli_close($conectar);
                exit;
            }
        } else {
            $erro = mysqli_error($conectar);
            redirectToPage("Informações Não Alteradas! $erro", "../users/alteracao_usuario.php");
        }
    }
} else {
    redirectToPage("Não Consegui Contato com Servidor!", "../users/alteracao_usuario.php");
}
