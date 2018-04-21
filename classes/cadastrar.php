<?php
        include_once "../db/db_config_mysql.php";
        // Inicia sessões 
        session_start();

        if (!mysqli_connect_errno())
        {
                if( isset($_SESSION["id_usuario"]) && isset($_POST["contrato"]) && isset($_POST["serial"]) && isset($_POST["cto"])
                && isset($_POST["porta"]) && isset($_POST["numeroTel"]) && isset($_POST["numeroTel"]) && isset($_POST["passwordTel"])
                && isset($_POST["telUser"]) && isset($_POST["pacote"]) )
                {
                       $usuario = $_SESSION["id_usuario"];
                       $contrato = $_POST["contrato"];
                       $serial = $_POST["serial"];
                       $cto = $_POST["cto"];
                       $porta = $_POST["porta"];
                       $telNumber = $_POST["numeroTel"];
                       $telPass = $_POST["passwordTel"];
                       $telUser = $_POST["telUser"];
                       $pacote = $_POST["pacote"];
                    
                        $sql_registra_onu = ("INSERT INTO ont (contrato, pon_mac, cto, tel_number, tel_user, tel_password, pacote, usuario_id, porta) 
                                                VALUES ('$contrato','$serial','$cto','$telNumber','$telUser','$telPass','$pacote','$usuario','$porta')" );

                        $cadastrar = mysqli_query($conectar,$sql_registra_onu);
                        if ($cadastrar )               
                        {
                            $_SESSION['menssagem'] = "ONU Cadastrada!";
                            header('Location: ../ontRegister.php');
                            xit;
                        }else{
                            $_SESSION['menssagem'] = "ONU Não Cadastrada!";
                            header('Location: ../ontRegister.php');
                            exit;
                        }

                }
                else
                {
                    $_SESSION['menssagem'] = "Campos Faltando!";
                    header('Location: ../ontRegister.php');
                    exit;
                }
        }else{
            $_SESSION['menssagem'] = "Não Consegui Contato com Servidor!";
            header('Location: ../ontRegister.php');
            exit;
        }
?>
