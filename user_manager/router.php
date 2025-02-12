<?php
include_once "Middleware/auth.php"; // Middleware para validar token

$dados_usuario = validarToken(); // Decodifica o JWT e retorna as permissões

$redirecionamentos = [
    "cadastrar_onu" => "../ont_classes/ont_register.php",
    "cadastrar_onu_corp" => "../ont_corp/select_cto.php",
    "modificar_onu" => "../ont_classes/ont_change.php",
    "deletar_onu" => "../ont_classes/ont_delete.php",
    "alterar_mac_ont" => "../ont_classes/alterar_mac_ont.php",
    "desativar_ativar_onu" => "../ont_classes/ont_disable.php",
    "cadastrar_cto" => "../cto_classes/cto_create.php",
    "cadastrar_olt" => "../cto_classes/pon_create.php",
    "cadastrar_velocidade" => "../planos/planos_create.php",
    "cadastrar_usuario" => "../users/usuario_new.php",
    "alterar_usuario" => "../users/alteracao_usuario.php",
    "cadastrar_equipamento" => "../equipamento/cadastro_equipamento.php",
    "consulta_ont" => "../consultas/get_status.php",
    "consulta_cto" => "../consultas/get_info_cto.php",
    "relatorio_sinal" => "../consultas/relatorio_sinal_ruim.php",
    "remover_cto" => "../cto_classes/remover_cto.php",
    "remover_olt" => "../cto_classes/remover_olt.php",
    "transferir_celula" => "../cto_classes/transfer_olt_select.php",
    "migrar_cgnat" => "../ont_classes/troca_nat.php"
];

// Percorre as permissões do usuário
foreach ($redirecionamentos as $permissao => $pagina) {
    if (!empty($dados_usuario[$permissao])) {
        header("Location: $pagina");
        exit;
    }
}

// Se nenhuma permissão for válida
http_response_code(403);
echo json_encode(["error" => "Acesso negado"]);
?>
