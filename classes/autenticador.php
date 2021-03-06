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
                            echo $_SESSION['menssagem'] = "Usuario inexistente!";
                            header('Location: ../index.php');
                            mysqli_close($conectar);
                            exit;
                        }elseif (mysqli_num_rows($checar_password) == 0) {
                            echo $_SESSION['menssagem'] = "Senha Incorreta!";
                            header('Location: ../index.php');
                            mysqli_close($conectar);
                            exit;
                        }else{
                            $dados = @mysqli_fetch_array($checar_login); 
                            // TUDO OK! Agora, passa os dados para a sessão e redireciona o usuário
                            $_SESSION["id_usuario"]= $dados["usuario_id"];
                            $_SESSION["nome_usuario"] = $dados["nome"];

                            $nome = $_SESSION['nome_usuario'];
                            $usuario_id = $_SESSION["id_usuario"];
                            $sql_insert_log = "INSERT INTO log (registro,codigo_usuario) 
                                VALUES ('Usuário $nome Entrou no Sistema!','$usuario_id')";
                            $executa_log = mysqli_query($conectar,$sql_insert_log);

                            $sql_select_permissoes = "SELECT *
                                FROM usuario_permissao WHERE usuario=$_SESSION[id_usuario]";
                            $execute_sql_select_permissoes = mysqli_query($conectar,$sql_select_permissoes);
                            $permissoes = @mysqli_fetch_array($execute_sql_select_permissoes); //@ qualquer mensagem de erro sera ignorada

                            $_SESSION["cadastrar_onu"] = $permissoes['cadastrar_onu'];
                            $_SESSION["cadastrar_onu_corp"] = $permissoes['cadastrar_onu_corp'];
                            $_SESSION["deletar_onu"] = $permissoes['deletar_onu'];
                            $_SESSION["modificar_onu"] = $permissoes['modificar_onu'];
                            $_SESSION["desativar_ativar_onu"] = $permissoes['desativar_ativar_onu'];
                            $_SESSION["cadastrar_cto"] = $permissoes['cadastrar_cto'];
                            $_SESSION["cadastrar_olt"] = $permissoes['cadastrar_olt'];
                            $_SESSION["cadastrar_velocidade"] = $permissoes['cadastrar_velocidade'];
                            $_SESSION["cadastrar_usuario"] = $permissoes['cadastrar_usuario'];
                            $_SESSION["cadastrar_equipamento"] = $permissoes['cadastrar_equipamento'];
                            $_SESSION["alterar_macONT"] = $permissoes['alterar_mac_ont'];
                            $_SESSION["consulta_onts"] = $permissoes['consulta_ont'];
                            $_SESSION["consulta_ctos"] = $permissoes['consulta_cto'];
                            $_SESSION["remover_cto"] = $permissoes['remover_cto'];
                            $_SESSION["remover_olt"] = $permissoes['remover_olt'];
                            $_SESSION["cadastrar_ip"] = $permissoes['cadastrar_ip'];
                            $_SESSION["alterar_usuario"] = $permissoes['alterar_usuario'];
                            $_SESSION["consulta_relatorio_sinal"] = $permissoes['relatorio_sinal'];
                            $_SESSION["transferir_celula"] = $permissoes['transferir_celula'];
                            $_SESSION["transferir_cgnat"] = $permissoes['migrar_cgnat'];
                            header('Location: redirecionador_pagina.php');
                            mysqli_close($conectar);
                            exit;
                        }
                }
                else
                {
                    echo $_SESSION['menssagem'] = "Campo Faltando!";
                    header('Location: ../index.php');
                    mysqli_close($conectar);
                    exit;
                }
        }else{
            echo $_SESSION['menssagem'] = "Nao consegui entrar no servidor";
            header('Location: ../index.php');
            mysqli_close($conectar);
            exit;
        }
?>
