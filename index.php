<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Bootstrap Core CSS -->
    <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- MetisMenu CSS -->
    <link href="vendor/metisMenu/metisMenu.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="dist/css/sb-admin-2.css" rel="stylesheet">

    <!-- Custom Fonts -->
    <link href="vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">

    <!-- VERTV CSS -->
    <link href="vendor/vertv/vertv.css" rel="stylesheet">
</head>

<?php include "db/db_config_mysql.php" ?>

<body>
<?php
#capturar mensagem
if(isset($_SESSION['menssagem']) && !empty($_SESSION['menssagem']))
{
    print "<script>alert(\"{$_SESSION['menssagem']}\")</script>";
    unset( $_SESSION['menssagem'] );
}
?>

<div id="wrapper">
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
    </nav>

    <div id="page-wrapper" style="border-left:none">
        <div class="row">
            <div class="login col-md-4 col-md-offset-4">
                <div class="login-panel panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Por Favor Realize o Login</h3>
                    </div>
                    <div class="panel-body">
                        <form id="loginForm" role="form">
                            <div class="form-group">
                                <input class="form-control" placeholder="Usuario" name="usuario" type="text" autofocus required>
                            </div>
                            <div class="form-group">
                                <input class="form-control" placeholder="Password" name="password" type="password" required>
                            </div>
                            <button class="btn btn-lg btn-success btn-block">Login</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script src="vendor/jquery/jquery.min.js"></script>
<!-- jQuery -->
<script src="vendor/jquery/jquery.min.js"></script>

<!-- Bootstrap Core JavaScript -->
<script src="vendor/bootstrap/js/bootstrap.min.js"></script>

<!-- Metis Menu Plugin JavaScript -->
<script src="vendor/metisMenu/metisMenu.min.js"></script>

<!-- Custom Theme JavaScript -->
<script src="dist/js/sb-admin-2.js"></script>
<script>
    $("#loginForm").submit(function(event) {
        event.preventDefault(); // Impede o reload da página

        $.ajax({
            url: "user_manager/authenticator.php",
            type: "POST",
            data: $(this).serializeArray(),
            dataType: "json",
            success: function(response) {
                localStorage.setItem("jwt_token", response.token); // Salva o token

                // Decodifica o token JWT
                const tokenPayload = parseJwt(response.token);

                // Verifica permissões e redireciona
                redirectByPermission(tokenPayload);
            },
            error: function(xhr) {
                alert("Erro ao fazer login: " + xhr.responseJSON.error);
            }
        });
    });


    /**
     * Decodifica um token JWT (Base64).
     */
    function parseJwt(token) {
        try {
            let base64Url = token.split('.')[1];
            let base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
            let jsonPayload = decodeURIComponent(atob(base64).split('').map(function(c) {
                return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
            }).join(''));

            return JSON.parse(jsonPayload);
        } catch (e) {
            console.error("Erro ao decodificar token:", e);
            return {};
        }
    }

    /**
     * Redireciona o usuário com base nas permissões.
     */
    function redirectByPermission(payload) {
        if (payload.roles.cadastrar_onu == 1) {
            window.location.href = "ont_classes/ont_register.php";
        } else if (payload.roles.cadastrar_onu_corp == 1) {
            window.location.href = "ont_corp/select_cto.php";
        } else if (payload.roles.modificar_onu == 1) {
            window.location.href = "ont_classes/ont_change.php";
        } else if (payload.roles.deletar_onu == 1) {
            window.location.href = "ont_classes/ont_delete.php";
        } else if (payload.roles.alterar_mac_ont == 1) {
            window.location.href = "ont_classes/alterar_mac_ont.php";
        } else if (payload.roles.desativar_ativar_onu == 1) {
            window.location.href = "ont_classes/ont_disable.php";
        } else if (payload.roles.cadastrar_cto == 1) {
            window.location.href = "cto_classes/cto_create.php";
        } else if (payload.roles.cadastrar_olt == 1) {
            window.location.href = "cto_classes/pon_create.php";
        } else if (payload.roles.cadastrar_velocidade == 1) {
            window.location.href = "planos/planos_create.php";
        } else if (payload.roles.cadastrar_usuario == 1) {
            window.location.href = "users/usuario_new.php";
        } else if (payload.roles.alterar_usuario == 1) {
            window.location.href = "users/alteracao_usuario.php";
        } else if (payload.roles.cadastrar_equipamento == 1) {
            window.location.href = "equipamento/cadastro_equipamento.php";
        } else if (payload.roles.consulta_onts == 1) {
            window.location.href = "consultas/get_status.php";
        } else if (payload.roles.consulta_ctos == 1) {
            window.location.href = "consultas/get_info_cto.php";
        } else if (payload.roles.consulta_relatorio_sinal == 1) {
            window.location.href = "consultas/relatorio_sinal_ruim.php";
        } else if (payload.roles.remover_cto == 1) {
            window.location.href = "cto_classes/remover_cto.php";
        } else if (payload.roles.remover_olt == 1) {
            window.location.href = "cto_classes/remover_olt.php";
        } else if (payload.roles.transferir_celula == 1) {
            window.location.href = "cto_classes/transfer_olt_select.php";
        } else if (payload.roles.transferir_cgnat == 1) {
            window.location.href = "ont_classes/troca_nat.php";
        } else {
            alert("Sem permissões adequadas! Redirecionando para login.");
            localStorage.removeItem("jwt_token");
            window.location.href = "index.php";
        }
    }
</script>
