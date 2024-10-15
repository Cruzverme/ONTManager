<?php
    $backgroundOntInfo = $informationProcessed['informations']['ont']['status'] == 'ONLINE' ? '#b3d1ff' : '#ff3333';
    $ontStatusColor = $informationProcessed['informations']['ont']['status'] == 'ONLINE' ? '#3bbb03' : '#ff8c1a';
    $signalId = $informationProcessed['informations']['signal']['rx'] >= "-2400" ? 'consulta_ont_positivo' : 'consulta_ont_negativo';

?>
<!-- HTML COMEÇA AQUI | HTML START HERE-->
<div class='row'>
    <div class='col-lg-12'>
        <div class='table-responsive'>

            <!-- INFORMAÇÕES ONT -->
            <table class='table info-table'>
                <thead>
                <h4 class="section-title">INFORMAÇÕES ONT</h4>
                <tr>
                    <th>CONTRATO</th>
                    <th>MAC</th>
                    <th>CTO-PORTA DE ATENDIMENTO</th>
                    <th>STATUS</th>
                    <th>ULTIMA VEZ OFFLINE</th>
                </tr>
                </thead>
                <tbody>
                <tr style='background-color: <?php echo $backgroundOntInfo ?>'>
                    <td><?php echo $contrato ?> </td>
                    <td><?php echo $serial ?></td>
                    <td>
                        <?php
                            echo "{$informationProcessed['cto']}-{$informationProcessed['porta_atendimento']}"
                        ?>
                    </td>
                    <td style='font-weight: bold; color: <?php echo $ontStatusColor ?> !important'>
                        <?php echo $informationProcessed['informations']['ont']['status']?>
                    </td>
                    <td style='font-weight: bold'>
                        <?php echo $informationProcessed['informations']['ont']['last_timeout']?>
                    </td>
                </tr>
                </tbody>
            </table>

            <!-- INFORMAÇÕES SINAL -->
            <table class='table info-table'>
                <thead>
                <h4 class="section-title">INFORMAÇÕES SINAL</h4>
                <tr>
                    <th>RX</th>
                    <th>TX</th>
                    <th>RX By OLT</th>
                    <th>SIP STATUS</th>
                    <th>SIP SERVICE STATUS</th>
                </tr>
                </thead>
                <tbody>
                <tr id="<?php echo $signalId ?>">
                    <td><?php echo $informationProcessed['informations']['signal']['rx'] ?> dBm</td>
                    <td><?php echo $informationProcessed['informations']['signal']['tx'] ?> dBm</td>
                    <td><?php echo $informationProcessed['informations']['signal']['rx_olt'] ?> dBm</td>
                    <td><?php echo $informationProcessed['informations']['signal']['status_sip'] ?></td>
                    <td><?php echo $informationProcessed['informations']['signal']['service_status_sip'] ?></td>
                </tr>
                </tbody>
            </table>

            <!-- INFORMAÇÕES OLT -->
            <table class='table info-table'>
                <thead>
                <h4 class="section-title">INFORMAÇÕES OLT</h4>
                <tr>
                    <th>ONT ID</th>
                    <th>OLT</th>
                    <th>SLOT</th>
                    <th>PON</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <?php echo $informationProcessed['ontId']?>
                    </td>
                    <td>
                        <?php echo $informationProcessed['device']?>
                    </td>
                    <td>
                        <?php echo $informationProcessed['informations']['ont']['slot']?>
                    </td>
                    <td>
                        <?php echo $informationProcessed['informations']['ont']['pon']?>
                    </td>
                </tr>
                </tbody>
            </table>

            <!-- INFORMAÇÕES ADICIONAIS -->
            <h4 class='informacoes_legend section-title' onclick='levanta();'>
                INFORMAÇÕES ADICIONAIS<i class='fa fa-chevron-down'></i>
            </h4>

            <div class='hider_infos' style='display:none'>
                <table class='table info-table'>
                    <thead>
                    <tr>
                        <th>TIPO WAN</th>
                        <th>IPV4</th>
                        <th>WAN MASK</th>
                        <th>WAN GATEWAY</th>
                        <th>TIPO CONEXÃO</th>
                    </tr>
                    </thead>
                    <?php foreach ($informationProcessed['informations']['wan'] as $wan): ?>
                        <tbody>
                            <tr>
                                <td><?php echo $wan['wan_type']?></td>
                                <td>
                                    <a target="_blank" href="https://<?php echo $wan['ipv4']?>:80">
                                        <?php echo $wan['ipv4']?>
                                    </a>
                                </td>
                                <td><?php echo $wan['wan_mask']?></td>
                                <td><?php echo $wan['wan_gateway']?></td>
                                <td><?php echo $wan['conection_type'] ?></td>
                            </tr>
                        </tbody>
                    <?php endforeach; ?>
                </table>

                <!-- SERVICE PORTS -->
                <legend class="legend_label">SERVICE PORT</legend>
                <table class='table info-table'>
                    <tbody>
                    <tr>
                        <?php foreach ($informationProcessed['informations']['service_port'] as $svrPort): ?>
                            <td style="font-weight: bold"><?php echo $svrPort ?></td>
                        <?php endforeach;?>
                    </tr>
                    </tbody>
                </table>
            </div>
            <hr/>
            <!-- STATUS DE PORTA -->
            <legend class="legend_label">STATUS DE PORTA</legend>
            <table class='table info-table'>
                <thead>
                <tr>
                    <th>PORTA 1</th>
                    <th>PORTA 2</th>
                    <th>PORTA 3</th>
                    <th>PORTA 4</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <?php foreach ($informationProcessed['informations']['eth_port'] as $eth): ?>
                        <?php $ethClass = $eth == 'CONECTADA' ? 'connected' : 'disconnected'; ?>
                        <td class="<?php echo $ethClass ?>"><?php echo $eth ?></td>
                    <?php endforeach; ?>
                </tr>
                </tbody>
            </table>

            <!-- AÇÕES -->
            <div style='text-align:center'>
                <button class='btn btn-secondary' onClick="consultar();">
                    <i class="fa fa-refresh fa-spin fa-fw"></i> ATUALIZAR DADOS
                </button>
                <button class='btn btn-secondary' onClick="return acordaONT('OLT01', '2', '3', 'ONT12345', 'reset');">
                    <i class='fa fa-spinner fa-spin fa-fw'></i> REINICIAR
                </button>
                <button class='btn btn-secondary' onClick="return acordaONT('OLT01', '2', '3', 'ONT12345', 'fabric');">
                    <i class='fa fa-cog fa-spin fa-fw'></i> PADRÃO DE FÁBRICA
                </button>
            </div>

        </div> <!-- end table-responsive -->
    </div> <!-- end col -->
</div><!-- End Row -->

<!-- HTML TERMINA AQUI | HTML END HERE-->
