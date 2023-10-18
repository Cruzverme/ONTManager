<?php
//iniciando sessao para enviar as msgs
session_start();
$contract = (int)filter_input(INPUT_GET, 'contract');

echo getLans($contract);
function getLans(int $contract)
{
    include_once "../../db/db_config_mysql.php";

    $json_file = file_get_contents("http://192.168.80.5/sisspc/demos/get_pacote_ftth_cplus.php?contra=$contrato");

    $json_str = json_decode($json_file, true);
    $itens = $json_str['velocidade'];
    $cplusCode = implode(',', $itens);

    $sqlLanLan = "SELECT name, vas_profile, line_profile, service_profile, gem_ports, pla.referencia_cplus 
                    FROM `lan_lan` ll, planos pla 
                    WHERE pla.plano_id = ll.planos_id AND pla.referencia_cplus IN ($cplusCode)";

    $result = $conectar->query($sqlLanLan);

    if ($result->num_rows > 0) {
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        return json_encode($rows);
    }

    return '{}';
}
