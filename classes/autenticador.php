<?php
        include_once "../db/db_config_mysql.php";
        // Inicia sessões 
        session_start();

        if (!mysqli_connect_errno())
        {
                if( isset($_POST["usuario"]) && isset($_POST["password"]) 
                    && !empty($_POST["usuario"]) && !empty($_POST["password"]) )
                {
                        $usuario = $_POST["usuario"];
                        $senha = md5($_POST["password"]);

                        $sql_verifica_login = ("SELECT usuario_id, usuario, nome FROM usuarios WHERE usuario = '$usuario' " );
                        $sql_verifica_password = ("SELECT usuario,senha FROM usuarios WHERE usuario = '$usuario' AND senha = '$senha'" );

                        $checar_login = mysqli_query($conectar,$sql_verifica_login);
                        $checar_password = mysqli_query($conectar,$sql_verifica_password);

                        if (mysqli_num_rows($checar_login) == 0)
                        {
                            $_SESSION['menssagem'] = "Usuario inexistente!";
                            header('Location: ../index.php');
                            mysqli_close($conectar);
                            exit;
                        }elseif (mysqli_num_rows($checar_password) == 0) {
                            $_SESSION['menssagem'] = "Senha Incorreta!";
                            header('Location: ../index.php');
                            mysqli_close($conectar);
                            exit;
                        }else{
                            $dados = @mysqli_fetch_array($checar_login); 
                            // TUDO OK! Agora, passa os dados para a sessão e redireciona o usuário
                            $_SESSION["id_usuario"]= $dados["usuario_id"];
                            $_SESSION["nome_usuario"] = $dados["nome"];

                            $sql_select_permissoes = "SELECT cadastrar_onu, deletar_onu, modificar_onu, desativar_ativar_onu, 
                                cadastrar_cto, cadastrar_olt, cadastrar_velocidade, cadastrar_usuario, cadastrar_equipamento,
                                alterar_mac_ont 
                                FROM usuario_permissao WHERE usuario=$_SESSION[id_usuario]";
                            $execute_sql_select_permissoes = mysqli_query($conectar,$sql_select_permissoes);
                            $permissoes = @mysqli_fetch_array($execute_sql_select_permissoes); //@ qualquer mensagem de erro sera ignorada

                            $_SESSION["cadastrar_onu"] = $permissoes['cadastrar_onu'];
                            $_SESSION["deletar_onu"] = $permissoes['deletar_onu'];
                            $_SESSION["modificar_onu"] = $permissoes['modificar_onu'];
                            $_SESSION["desativar_ativar_onu"] = $permissoes['desativar_ativar_onu'];
                            $_SESSION["cadastrar_cto"] = $permissoes['cadastrar_cto'];
                            $_SESSION["cadastrar_olt"] = $permissoes['cadastrar_olt'];
                            $_SESSION["cadastrar_velocidade"] = $permissoes['cadastrar_velocidade'];
                            $_SESSION["cadastrar_usuario"] = $permissoes['cadastrar_usuario'];
                            $_SESSION["cadastrar_equipamento"] = $permissoes['cadastrar_equipamento'];
                            $_SESSION["alterar_macONT"] = $permissoes['alterar_mac_ont'];
                            header('Location: redirecionador_pagina.php');
                            mysqli_close($conectar);
                            exit;
                        }
                }
                else
                {
                    $_SESSION['menssagem'] = "Campo Faltando!";
                    header('Location: ../index.php');
                    mysqli_close($conectar);
                    exit;
                }
        }else{
            $_SESSION['menssagem'] = "Nao consegui entrar no servidor";
            header('Location: ../index.php');
            mysqli_close($conectar);
            exit;
        }
?>
