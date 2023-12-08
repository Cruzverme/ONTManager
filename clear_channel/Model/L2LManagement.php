<?php
//iniciando sessao para enviar as msgs
    session_start();
    $id = (int) filter_input(INPUT_GET,'id');
    $method = filter_input(INPUT_GET,'method');

    if (!$method) {
        echo getLans($id);
    } else {
        echo removeLanLan($id);
    }

    function getLans(int $id)
    {
        include_once "../../db/db_config_mysql.php";

        $sqlLanLan = "SELECT id, name FROM lan_lan;";
        if ($id) {
            $sqlLanLan = "SELECT id, name, vas_profile, line_profile, service_profile, gem_ports, planos_id FROM lan_lan WHERE id = $id";
        }

        $result = $conectar->query($sqlLanLan);

        if ($result->num_rows > 0) {
            $rows = $result->fetch_all(MYSQLI_ASSOC);
            $jsonData = json_encode($rows);

            if (!$jsonData) {
                return 'ERROR ' . json_last_error_msg();
            }

            return $jsonData;
        }
    }

    function removeLanLan(int $id)
    {
        include_once "../../db/db_config_mysql.php";

        $sqlLanLan = "DELETE FROM lan_lan WHERE id = $id";
        $conectar->query($sqlLanLan);

        if (mysqli_affected_rows($conectar) >= 1) {
            return 'clear channel removido!';
        }

        return '';
    }
