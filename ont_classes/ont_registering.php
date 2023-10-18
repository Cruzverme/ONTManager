<?php
include_once "../classes/html_inicio.php";
include_once "../db/db_config_mysql.php";
include_once "../classes/funcoes.php";
include_once "../classes/Packages.php";

// Verifica permissão de acesso
if (!$_SESSION["cadastrar_onu"]) {
    echo '
            <script language= "JavaScript">
                alert("Sem Permissão de Acesso!");
                location.href="../classes/redirecionador_pagina.php";
            </script>
        ';
    exit;
}

// Restante do código continua aqui, pois o usuário possui permissão de acesso

$porta_selecionado = filter_input(INPUT_POST, 'porta_atendimento');
$frame = filter_input(INPUT_POST, 'frame');
$slot = filter_input(INPUT_POST, 'slot');
$pon = filter_input(INPUT_POST, 'pon');
$cto = filter_input(INPUT_POST, 'cto');
$device = filter_input(INPUT_POST, 'device');
$contrato = filter_input(INPUT_POST, 'contrato');
const CONVERSOR_KEY = 358;

// Verifica se o contrato existe e não está cancelado
if (checar_contrato($contrato) == null) {
    echo '
            <script language= "JavaScript">
                alert("Contrato Inexistente ou Cancelado");
                location.href="../ont_classes/ont_register.php";
            </script>
        ';
    exit;
}

// Obtem dados do pacote FTTH do Control Plus
$json_file = file_get_contents("http://192.168.80.5/sisspc/demos/get_pacote_ftth_cplus.php?contra=$contrato");
$json_str = json_decode($json_file, true);
$itens = $json_str['velocidade'];
$nome = $json_str['nome'];

// Verifica se há o valor 358 no array $itens para atribuir à variável $conversor
$conversor = in_array(CONVERSOR_KEY, $itens) ? true : false;
?>
<div id="page-wrapper">
    <div class="row">
        <div class="col-md-6 col-md-offset-3">
            <div class="login-panel panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Cadastro de ONT</h3>
                </div>
                <div class="panel-body">
                    <form role="form" method="post">
                        <fieldset class="informacoes_legend">
                            <legend>Informações<i class='fa fa-chevron-down'></i></legend>
                            <div class="hider_infos" style="display:none">
                                <p>
                                    <?php echo "PORTA: $porta_selecionado | OLT: $device | FRAME: $frame | SLOT: $slot | PON: $pon | CTO: $cto"; ?>
                                </p>
                            </div>
                        </fieldset>

                        <fieldset class="radio-planos">
                            <legend>Selecione o Plano</legend>
                            <div class="form-group hider_planos">
                                <?php
                                $planos = Packages::getVasProfileList($conversor);

                                foreach ($planos as $value => $label) {
                                    echo '<div class="radio">
                                                <label>
                                                <input type="radio" name="optionsRadios" value="' . $value . '"';
                                    if ($value === 'VAS_Internet') {
                                        echo ' checked';
                                    }
                                    echo '>' . $label . '</label>
                                            </div>';
                                }
                                ?>
                            </div>
                        </fieldset>

                        <fieldset>
                            <legend>Informações de Cadastro</legend>
                            <div class="form-group">
                                <label>Contrato</label>
                                <input class="form-control" placeholder="Contrato"
                                       name="contrato" value="<?php echo $contrato; ?>" type="text" autofocus readonly>
                            </div>

                            <div class="form-group">
                                <label>Nome do Assinante</label>
                                <input class="form-control" placeholder="Contrato"
                                       name="nome" value="<?php echo $nome[0]; ?>" type="text" autofocus readonly>
                            </div>

                            <div class="form-group conversorHide">
                                <label>Pon MAC</label>
                                <input class="form-control" placeholder="MAC PON"
                                       name="serial" type="text" minlength="16" maxlength="16" required>
                            </div>

                            <div class="camposPacotes" style="display: visible">
                                <div class="form-group">
                                    <label>Pacote</label>
                                    <select class="form-control" name="pacote" required>
                                        <option value="" selected disabled> Selecione a Velocidade </option>
                                        <?php
                                        $codigo = '';
                                        $hasCplusReference = false;

                                        $planos = Packages::getVelocityPack($conectar);

                                        foreach ($itens as $codigoPlano) :
                                            foreach ($planos as $plano) :
                                                if ($codigoPlano == $plano['referencia_cplus']) {
                                                    $codigo = $plano['referencia_cplus'];
                                                    echo "<option value='{$plano['nomenclatura_velocidade']}-{$plano['plano_id']}'>{$plano['nome']}</option>";
                                                    $hasCplusReference = true;
                                                }
                                            endforeach;
                                        endforeach;

                                        if (!$hasCplusReference) {
                                            echo "<option value='none'>Velocidade Não Cadastrada no Contrato, Favor Verificar no Control Plus</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div> <!-- fim div pacote -->

                            <?php if (in_array($codigo, Packages::getFixedIpPackCode())): ?>
                                <div class="form-group" style="display: none;">
                                    <input type="checkbox" name="modo_bridge" value="mac_externo">
                                    IP Utilizado em Equipamento Externo
                                    </input>
                                </div>

                                <div class="form-group bridge" style="display: none;">
                                    <label>MAC do Equipamento</label>
                                    <input type="text" class="form-control" id="mac" name="mac" />
                                </div>

                                <div class="form-group">
                                    <label>IP</label>
                                    <?php
                                    $sql_lista_ips = "SELECT numero_ip FROM ips_valido WHERE utilizado = false";
                                    $executa_ips = mysqli_query($conectar, $sql_lista_ips);
                                    ?>
                                    <select class="form-control" name="ipFixo">
                                        <option value="" selected disabled>Selecione o IP</option>
                                        <?php while ($listaIP = mysqli_fetch_array($executa_ips, MYSQLI_BOTH)): ?>
                                            <option><?php echo $listaIP[0]; ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            <?php else: ?>
                                <input type="hidden" name="mac" value="NULL">
                                <input type="hidden" name="ipFixo" value="NULL">
                            <?php endif; ?>

                            <div class="form-group conversorHide">
                                <label>Equipamento</label>
                                <select class="form-control" name="equipamentos">
                                    <?php
                                    $sql_consulta_equipamentos = "SELECT * FROM equipamentos";
                                    $executa_query_equipamentos = mysqli_query($conectar, $sql_consulta_equipamentos);
                                    while ($equipamentos = mysqli_fetch_array($executa_query_equipamentos, MYSQLI_BOTH)): ?>
                                        <option value="<?php echo $equipamentos['modelo']; ?>"><?php echo $equipamentos['modelo']; ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <input type="hidden" name="porta_atendimento" value="<?php echo $porta_selecionado; ?>">
                            <input type="hidden" name="frame" value="<?php echo $frame; ?>">
                            <input type="hidden" name="slot" value="<?php echo $slot; ?>">
                            <input type="hidden" name="pon" value="<?php echo $pon; ?>">
                            <input type="hidden" name="caixa_atendimento_select" value="<?php echo $cto; ?>">
                            <input type="hidden" name="deviceName" value="<?php echo $device; ?>">

                            <div class="camposTelefone" style="display:none">
                                <div class="form-group">
                                    <label>Telefone</label>
                                    <input class="form-control" placeholder="Telefone" name="numeroTel" type="text" autofocus>
                                </div>

                                <div class="form-group">
                                    <label>Senha do Telefone</label>
                                    <input class="form-control" placeholder="Senha do Telefone" name="passwordTel" type="text" autofocus>
                                </div>

                                <div id="tel2_user" class="form-group">
                                </div>

                                <div id="tel2_pass" class="form-group">
                                </div>
                            </div>
                        </fieldset>

                        <button class="btn btn-lg btn-success btn-block cadastrarCliente" type='button'
                                onClick='cadastrar();'>Cadastrar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal modal-espera"></div>
</div>
<?php include_once "../classes/html_fim.php"; ?>
