<?php


include_once "../classes/html_inicio.php";
include_once "../db/db_config_mysql.php";

if ($_SESSION["cadastrar_ip"] == 0) {
    echo '
        <script language= "JavaScript">
            alert("Sem Permissão de Acesso!");
            location.href="../classes/redirecionador_pagina.php";
        </script>
    ';
}

$sqlShowIP = "select * from ips_valido";
$executeShowIP = mysqli_query($conectar, $sqlShowIP);
$listaIp = mysqli_fetch_all($executeShowIP, MYSQLI_ASSOC);

$ipsVagos = 0;
?>
<div id="page-wrapper">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="login-panel panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Lista de IP</h3>
                </div>
                <div class="panel-body">
                    <div class='table-responsive'>
                        <table class='table display' id='tabelaSinais' data-link='row'>
                            <thead>
                                <tr>
                                    <th>Ip</th>
                                    <th>Disponível</th>
                                    <th>Utilizado Por</th>
                                    <th>Pon Number</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($listaIp as $ip) {
                                    $ip['utilizado'] == 1 ? $ipsVagos += 1 : $ipsVagos;
                                    ?>
                                    <tr>
                                        <td><?php echo $ip['numero_ip']; ?></td>
                                        <td><?php if ($ip['utilizado'] == 1) echo "Não";
                                                else echo "Sim"; ?></td>
                                        <td><?php echo $ip['utilizado_por']; ?></td>
                                        <td><?php echo $ip['mac_serial']; ?></td>
                                    </tr>
                                <?php } ?>
                                <div style="text-align:center; font-weight:bold"><?php echo "Ainda restam $ipsVagos IPs disponíveis."; ?></div>
                            </tbody>

                    </div>

                </div>
            </div>
        </div>
    </div>
    <?php include_once "../classes/html_fim.php"; ?>