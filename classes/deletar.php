<?php
        include_once "../db/db_config_mysql.php";
        // Inicia sessões 
        session_start();

        if (!mysqli_connect_errno())
        {
                if( isset($_SESSION["id_usuario"]) && isset($_POST["contrato"]) && isset($_POST["serial"]) )
                {
                    echo $usuario = $_SESSION["id_usuario"];
                    echo $contrato = $_POST["contrato"];
                    echo $serial = $_POST["serial"];
                
                    $sql_apagar_onu = ("DELETE FROM ont WHERE contrato = '$contrato' AND pon_mac = '$serial'" );

                    $deletar_onu = mysqli_query($conectar,$sql_apagar_onu);
                    if($deletar_onu)
                    {
                        if ( $total = mysqli_affected_rows($conectar))    //retorna quantas rows foram afetadas           
                        {
                            $_SESSION['menssagem'] = "$total ONU Removida!";
                            header('Location: ../ont_classes/ont_delete.php');
                            mysqli_close($conectar);
                            exit;
                        }else{
                            $_SESSION['menssagem'] = "ONU Não Removida!";
                            header('Location: ../ont_classes/ont_delete.php');
                            mysqli_close($conectar);
                            exit;
                        }
                    }else{
                        $_SESSION['menssagem'] = 'Houve erro na execuão da query SQL: '.mysqli_error($conectar);
                        header('Location: ../ont_classes/ont_delete.php');
                        mysqli_close($conectar);
                        exit;
                    }
                    
                }
                else
                {
                    $_SESSION['menssagem'] = "Campos Faltando!";
                    header('Location: ../ont_classes/ont_delete.php');
                    mysqli_close($conectar);
                    exit;
                }
        }else{
            $_SESSION['menssagem'] = "Não Consegui Contato com Servidor!";
            header('Location: ../ont_classes/ont_delete.php');
            mysqli_close($conectar);
            exit;
        }
        /* close connection */

        
/*
SQL PARA SALVAR NO RADIUS
    INSERT INTO radcheck( username, attribute, op, value) VALUES ( '2500/13/0/485754439C96D58B@vertv', 'User-Name', ':=', '2500/13/0/485754430CEA4E9A@vertv' );

    INSERT INTO radcheck( username, attribute, op, value) VALUES ( '2500/13/0/485754439C96D58B@vertv', 'User-Password', ':=', ‘vlan’ );

    INSERT INTO radcheck( username, attribute, op, value) VALUES ( '2500/13/0/485754439C96D58B@vertv', 'Huawei-Qos-Profile-Name', ':=', 'CORPF_10M' );
*/
?>