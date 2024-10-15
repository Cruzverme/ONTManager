<?php
class Packages
{
    /**
     * Get vasProfile List to show in packages list
     *
     * @param $conversor
     * @return string[]
     */
    public static function getVasProfileList($conversor): array
    {
        $vasList = array(
            'VAS_Internet' => 'INTERNET',
            'VAS_IPTV' => 'IPTV',
            'VAS_Internet-IPTV' => 'INTERNET | IPTV',
            'VAS_Internet-VoIP' => 'INTERNET | TELEFONE',
            'VAS_IPTV-VoIP' => 'IPTV | TELEFONE',
            'VAS_Internet-VoIP-IPTV' => 'INTERNET | TELEFONE | IPTV'
        );
        if ($conversor) {
            $conversorElement = array('conversorHFC' => 'CONVERSOR');
            array_splice($vasList, 1, 0, $conversorElement);
        }

        return $vasList;
    }

    /**
     * Return list vasProfile with voip
     *
     * @return string[]
     */
    public static function getVasProfileWithVoip(): array
    {
        return [
            "VAS_Internet-VoIP", "VAS_IPTV-VoIP", "VAS_Internet-VoIP-IPTV",
            "VAS_Internet-twoVoIP-IPTV", "VAS_Internet-twoVoIP",
            "VAS_Internet-VoIP-CORP-IP", "VAS_Internet-VoIP-IPTV-CORP-IP",
            "VAS_Internet-VoIP-IPTV-CORP-IP-B", "VAS_Internet-VoIP-CORP-IP-Bridge"
        ];
    }

    /**
     * Get velocity values
     *
     * @param $conectar
     * @return array
     */
    public static function getVelocityPack($conectar)
    {
        $sql_lista_velocidades = "SELECT plano_id, nome, nomenclatura_velocidade, referencia_cplus FROM planos";
        $executa_query = mysqli_query($conectar, $sql_lista_velocidades);

        $planos = array();
        while ($listaPlanos = mysqli_fetch_array($executa_query, MYSQLI_ASSOC)) {
            $planos[] = $listaPlanos;
        }

        mysqli_free_result($executa_query);
        return $planos;
    }

    public static function  getFixedIpPackCode(): array
    {
        return array(
          358, 389, 330, 388, 331, 332,
          333, 334, 335, 336, 349, 350,
          351, 352, 353, 354, 372, 374,
          377, 380, 381, 392, 400, 401,
          402
        );
    }

    public static function getStaticIpListAvailable($connection, $contrato = 0)
    {
        $ipList = [];
        $sqlIpList = "SELECT numero_ip FROM ips_valido WHERE utilizado_por = ? AND utilizado = 0";
        $stmt = mysqli_prepare($connection, $sqlIpList);

        if (!$stmt) {
            return false;
        }
        mysqli_stmt_bind_param($stmt, "i", $contrato);

        if (!mysqli_stmt_execute($stmt)) {
            return false;
        }

        $result = mysqli_stmt_get_result($stmt);

        while ($row = mysqli_fetch_assoc($result)) {
            $ipList[] = $row['numero_ip'];
        }


        if (empty($ipList)) {
            $sqlIpList = "SELECT numero_ip FROM ips_valido WHERE utilizado = 0";
            $stmt = mysqli_prepare($connection, $sqlIpList);

            if (!$stmt) {
                return false;
            }

            if (!mysqli_stmt_execute($stmt)) {
                return false;
            }

            $result = mysqli_stmt_get_result($stmt);

            while ($row = mysqli_fetch_assoc($result)) {
                $ipList[] = $row['numero_ip'];
            }
        }


        mysqli_stmt_close($stmt);

        return $ipList;
    }
}
