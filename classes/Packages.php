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
     * Get velocity values
     *
     * @param $conectar
     * @return array
     */
    public static function getVelocityPack($conectar)
    {
        $sql_lista_velocidades = "SELECT plano_id, nome, nomenclatura_velocidade, referencia_cplus FROM planos WHERE is_active IS FALSE";
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
            358,
            389,
            330,
            388,
            331,
            332,
            333,
            334,
            335,
            336,
            349,
            350,
            351,
            352,
            353,
            354,
            372,
            374,
            377,
            380,
            381
        );
    }
}