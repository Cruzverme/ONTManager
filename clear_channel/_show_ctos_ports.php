<?php
$caixa_atendimento = filter_input(INPUT_POST, 'ctoSelect');
$deviceName = filter_input(INPUT_POST, 'pon');

$select_ont_infos = "SELECT ct.frame_slot_pon, ct.porta_atendimento, ct.porta_atendimento_disponivel, ct.serial, p.deviceName, p.olt_ip 
    FROM ctos ct 
    INNER JOIN pon p ON p.pon_id = ct.pon_id_fk 
    WHERE ct.caixa_atendimento = '$caixa_atendimento'";

$execute_ont_infos = mysqli_query($conectar, $select_ont_infos);

if ($caixa_atendimento || $deviceName) {
?>
    <div class="row">
        <div class="col-lg-16">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                    <tr>
                        <th>OLT</th>
                        <th>CTO</th>
                        <th>FRAME</th>
                        <th>SLOT</th>
                        <th>PON</th>
                        <th>Porta Atendimento</th>
                        <th>MAC do Equipamento</th>
                        <th>Contrato</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    while ($info = mysqli_fetch_array($execute_ont_infos, MYSQLI_BOTH)) {
                        $porta_atendimento = $info['porta_atendimento'];
                        list($frame, $slot, $pon) = explode('-', $info['frame_slot_pon']);
                        $device = $info['deviceName'];
                        $porta_disponivel = $info['porta_atendimento_disponivel'];
                        $serial = $info['serial'];
                        $ipOLT = $info['olt_ip'];
                        ?>
                        <tr>
                            <td><?= $device ?></td>
                            <td><?= $caixa_atendimento ?></td>
                            <td><?= $frame ?></td>
                            <td><?= $slot ?></td>
                            <td><?= $pon ?></td>
                            <td><?= $porta_atendimento ?></td>
                            <?php
                            if ($porta_disponivel == 0) {
                                if ($_SESSION["cadastrar_onu"] == 1) {
                                    ?>
                                    <td>
                                        <a href="./_input_contrato.php?porta_atendimento=<?= $porta_atendimento ?>&frame=<?= $frame ?>&slot=<?= $slot ?>&pon=<?= $pon ?>&cto=<?= $caixa_atendimento ?>&device=<?= $device ?>">
                                            DISPONÍVEL
                                        </a>
                                    </td>
                                    <td>----------------</td>
                                    <?php
                                } else {
                                    ?>
                                    <td>DISPONÍVEL</td>
                                    <td>----------------</td>
                                    <?php
                                }
                            } else {
                                $contrato_select = "SELECT contrato FROM ont WHERE serial = '$serial'";
                                $execute_contrato = mysqli_query($conectar, $contrato_select);
                                $contrato = mysqli_fetch_array($execute_contrato, MYSQLI_BOTH);
                                ?>
                                <td><?= $serial ?></td>
                                <td><?= $contrato['contrato'] ?></td>
                                <?php
                            }
                            ?>
                        </tr>
                        <?php
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php
}
?>
