<?php
include_once "../db/db_config_mysql.php";
// Inicia sessões
session_start();

if (!mysqli_connect_errno()) {
    if (isset($_POST["usuario"]) && isset($_POST["password"])
        && !empty($_POST["usuario"]) && !empty($_POST["password"])
    ) {
        $usuario = $_POST["usuario"];
        $senha = md5($_POST["password"]);

        // Preparando as consultas
        $sql_verifica_login = "SELECT usuario_id, usuario, nome FROM usuarios WHERE usuario = ?";
        $sql_verifica_password = "SELECT usuario, senha FROM usuarios WHERE usuario = ? AND senha = ?";

        // Utilizando Prepared Statements
        if ($stmt_verifica_login = mysqli_prepare($conectar, $sql_verifica_login)) {
            mysqli_stmt_bind_param($stmt_verifica_login, "s", $usuario);
            mysqli_stmt_execute($stmt_verifica_login);
            mysqli_stmt_store_result($stmt_verifica_login);

            if (mysqli_stmt_num_rows($stmt_verifica_login) == 0) {
                redirectWithError("Usuário inexistente!");
            } else {
                if ($stmt_verifica_password = mysqli_prepare($conectar, $sql_verifica_password)) {
                    mysqli_stmt_bind_param($stmt_verifica_password, "ss", $usuario, $senha);
                    mysqli_stmt_execute($stmt_verifica_password);
                    mysqli_stmt_store_result($stmt_verifica_password);

                    if (mysqli_stmt_num_rows($stmt_verifica_password) == 0) {
                        redirectWithError("Senha incorreta!");
                    } else {
                        // Vinculando resultados às variáveis
                        mysqli_stmt_bind_result($stmt_verifica_login, $usuario_id, $usuario, $nome);
                        mysqli_stmt_fetch($stmt_verifica_login);

                        // TUDO OK! Agora, passa os dados para a sessão e redireciona o usuário
                        $_SESSION["id_usuario"] = $usuario_id;
                        $_SESSION["nome_usuario"] = $nome;

                        $nome = $_SESSION['nome_usuario'];
                        $usuario_id = $_SESSION["id_usuario"];
                        $sql_insert_log = "INSERT INTO log (registro, codigo_usuario) 
                            VALUES ('Usuário $nome Entrou no Sistema!', '$usuario_id')";
                        $executa_log = mysqli_query($conectar, $sql_insert_log);

                        $sql_select_permissoes = "SELECT * FROM usuario_permissao WHERE usuario = ?";
                        if ($stmt_select_permissoes = mysqli_prepare($conectar, $sql_select_permissoes)) {
                            mysqli_stmt_bind_param($stmt_select_permissoes, "i", $usuario_id);
                            mysqli_stmt_execute($stmt_select_permissoes);
                            mysqli_stmt_store_result($stmt_select_permissoes);

                            mysqli_stmt_bind_result($stmt_select_permissoes,  $id_permissao,
                                $usuario,
                                $cadastrar_onu,
                                $cadastrar_onu_corp,
                                $deletar_onu,
                                $modificar_onu,
                                $desativar_ativar_onu,
                                $cadastrar_cto,
                                $cadastrar_olt,
                                $cadastrar_ip,
                                $cadastrar_velocidade,
                                $cadastrar_equipamento,
                                $cadastrar_usuario,
                                $alterar_usuario,
                                $alterar_mac_ont,
                                $consulta_ont,
                                $consulta_cto,
                                $relatorio_sinal,
                                $remover_cto,
                                $remover_olt,
                                $transferir_celula,
                                $migrar_cgnat,
                                $permitir_cadastro_corporativo,
                                $consulta_log
                            );
                            mysqli_stmt_fetch($stmt_select_permissoes);

                            $_SESSION["cadastrar_onu"] = $cadastrar_onu;
                            $_SESSION["cadastrar_onu_corp"] = $cadastrar_onu_corp;
                            $_SESSION["deletar_onu"] = $deletar_onu;
                            $_SESSION["modificar_onu"] = $modificar_onu;
                            $_SESSION["desativar_ativar_onu"] = $desativar_ativar_onu;
                            $_SESSION["cadastrar_cto"] = $cadastrar_cto;
                            $_SESSION["cadastrar_olt"] = $cadastrar_olt;
                            $_SESSION["cadastrar_velocidade"] = $cadastrar_velocidade;
                            $_SESSION["cadastrar_usuario"] = $cadastrar_usuario;
                            $_SESSION["cadastrar_equipamento"] = $cadastrar_equipamento;
                            $_SESSION["alterar_macONT"] = $alterar_mac_ont;
                            $_SESSION["consulta_onts"] = $consulta_ont;
                            $_SESSION["consulta_ctos"] = $consulta_cto;
                            $_SESSION["remover_cto"] = $remover_cto;
                            $_SESSION["remover_olt"] = $remover_olt;
                            $_SESSION["cadastrar_ip"] = $cadastrar_ip;
                            $_SESSION["alterar_usuario"] = $alterar_usuario;
                            $_SESSION["consulta_relatorio_sinal"] = $relatorio_sinal;
                            $_SESSION["transferir_celula"] = $transferir_celula;
                            $_SESSION["transferir_cgnat"] = $migrar_cgnat;
                            $_SESSION["consulta_log"] = $consulta_log;

                            header('Location: redirecionador_pagina.php');
                            mysqli_close($conectar);
                            exit;
                        } else {
                            redirectWithError("Erro ao consultar permissões.");
                        }
                    }
                } else {
                    redirectWithError("Erro ao verificar a senha.");
                }
            }
        } else {
            redirectWithError("Erro ao verificar o usuário.");
        }
    } else {
        redirectWithError("Campo faltando!");
    }
} else {
    redirectWithError("Não foi possível conectar ao servidor.");
}

function redirectWithError($message) {
    $_SESSION['menssagem'] = $message;
    header('Location: ../index.php');
    mysqli_close($conectar);
    exit;
}
?>
