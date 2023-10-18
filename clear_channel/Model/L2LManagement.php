<?php
//iniciando sessao para enviar as msgs
    session_start();
    $id = (int) filter_input(INPUT_GET,'id');

    echo getLans($id);
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
            return json_encode($rows);
        }
    }
