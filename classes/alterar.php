<?php
        include_once "../db/db_config_mysql.php";
        // Inicia sessões 
        session_start();

        if (!mysqli_connect_errno())
        {
                if( isset($_SESSION["id_usuario"]) && isset($_POST["contrato"]) && isset($_POST["serial"]) && isset($_POST["pacote"]) )
                {
                    echo $usuario = $_SESSION["id_usuario"];
                    echo $contrato = $_POST["contrato"];
                    echo $serial = $_POST["serial"];
                    echo $pacote = $_POST["pacote"];
                
                    $sql_muda_plano_onu = ("UPDATE ont SET pacote = '$pacote' WHERE contrato = '$contrato' AND pon_mac = '$serial'" );

                    $novo_pacote = mysqli_query($conectar,$sql_muda_plano_onu);
                    if ( $novo_pacote )               
                    {
                        $_SESSION['menssagem'] = "Velocidade Alterada!";
                        header('Location: ../ont_change.php');
                        mysqli_close($conectar);
                        exit;
                    }else{
                        $_SESSION['menssagem'] = "Velocidade Não Alterada! \n 'Houve erro na execuão da query SQL: '.mysqli_error($conectar)";
                        header('Location: ../ont_change.php');
                        mysqli_close($conectar);
                        exit;
                    }
                }
                else
                {
                    $_SESSION['menssagem'] = "Campos Faltando!";
                    header('Location: ../ont_change.php');
                    mysqli_close($conectar);
                    exit;
                }
        }else{
            $_SESSION['menssagem'] = "Não Consegui Contato com Servidor!";
            header('Location: ../ont_change.php');
            mysqli_close($conectar);
            exit;
        }

        
/*
SQL PARA SALVAR NO RADIUS
    INSERT INTO radcheck( username, attribute, op, value) VALUES ( '2500/13/0/485754439C96D58B@vertv', 'User-Name', ':=', '2500/13/0/485754430CEA4E9A@vertv' );

    INSERT INTO radcheck( username, attribute, op, value) VALUES ( '2500/13/0/485754439C96D58B@vertv', 'User-Password', ':=', ‘vlan’ );

    INSERT INTO radcheck( username, attribute, op, value) VALUES ( '2500/13/0/485754439C96D58B@vertv', 'Huawei-Qos-Profile-Name', ':=', 'CORPF_10M' );
*/
?>