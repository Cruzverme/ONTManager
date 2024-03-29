<?php
include_once "../db/db_config_mysql.php";
//iniciando sessao para enviar as msgs
session_start();

$nome_velocidade = filter_input(INPUT_POST, 'nome_velocidade');
$velocidade_download = filter_input(INPUT_POST, 'velocidade_download');
$velocidade_upload = filter_input(INPUT_POST, 'velocidade_upload');
$tipo_plano = filter_input(INPUT_POST, 'optionTipoVelocidade');
$codigo_cplus = filter_input(INPUT_POST, 'codigoCplus');
$usuario = filter_input(INPUT_SESSION, 'id_ususario');

$nomenclatura = str_replace('??', "$velocidade_download", $tipo_plano);

if ($nome_velocidade && $velocidade_download && $velocidade_upload && $tipo_plano) {
    $velocidade_download = $velocidade_download * 1024;
    $velocidade_upload = $velocidade_upload * 1024;

    if (!mysqli_connect_errno()) {
        $sql_insert_velocidade = "INSERT INTO planos (
                    nome, 
                    nomenclatura_velocidade, 
                    velocidade_download, 
                    velocidade_upload, 
                    referencia_cplus
                ) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conectar, $sql_insert_velocidade);

        mysqli_stmt_bind_param($stmt, "sssii",
            $nome_velocidade,
            $nomenclatura,
            $velocidade_download,
            $velocidade_upload,
            $codigo_cplus
        );
        $result = mysqli_stmt_execute($stmt);

        if ($result) {
            $sql_insert_log = "INSERT INTO log (registro, codigo_usuario) VALUES (?, ?)";
            $stmt_log = mysqli_prepare($conectar, $sql_insert_log);
            $logMessage = "Velocidade Criada pelo Usuario de Codigo $usuario";
            mysqli_stmt_bind_param($stmt_log, "si", $logMessage, $usuario);
            $executa_log = mysqli_query($conectar, $sql_insert_log);

            $_SESSION['menssagem'] = "Velocidade Registrada!";
            header('Location: ../planos/planos_create.php');
            mysqli_close($conectar);
            exit;
        }
        $erro = mysqli_stmt_error($conectar);
        $_SESSION['menssagem'] = "Velocidade Não Salva! SQL: $erro";
        header('Location: ../planos/planos_create.php');
        mysqli_close($conectar);
        exit;
    }
    $_SESSION['menssagem'] = "Não Consegui Contato com Servidor!";
    header('Location: ../index.php');
    mysqli_close($conectar);
    exit;
}

$_SESSION['menssagem'] = "Campos Faltando!";
header('Location: ../planos/planos_create.php');
mysqli_close($conectar);
exit;

/*
SQL PARA SALVAR NO RADIUS
INSERT INTO radcheck( username, attribute, op, value) VALUES ( 'vlan2500/slot13/porta0/485754439C96D58B@vertv',
'User-Name', ':=', '2500/13/0/485754430CEA4E9A@vertv' ); qual olt

INSERT INTO radcheck( username, attribute, op, value) VALUES ( '2500/13/0/485754439C96D58B@vertv', 'User-Password', ':=', ‘vlan’ );

INSERT INTO radcheck( username, attribute, op, value) VALUES ( '2500/13/0/485754439C96D58B@vertv', 'Huawei-Qos-Profile-Name', ':=', 'CORPF_10M' );
*/
?>