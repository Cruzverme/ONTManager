<?php
include_once "../classes/html_inicio.php";
include_once "../db/db_config_mysql.php";
//TODO ajustar permissao

if($_SESSION["gerenciar_l2l"] == 0) {
    echo '
    <script language= "JavaScript">
      alert("Sem Permissão de Acesso!");
      location.href="../classes/redirecionador_pagina.php";
    </script>
    ';
}
?>
    <div id="page-wrapper">
         <div class="container">
            <div class="col-md-7">
                <div class="login-panel panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Gerenciador de Lan to Lan</h3>
                    </div>
                    <div class="panel-body" style="display: inline-flex; width: 100%">
                        <div id="l2lform" style="width: 50%; padding-right:10px;">
                            <form role="form" action="./create_l2l_configs.php" method="post">
                                <div class="form-group">
                                    <div class="form-group">
                                        <label for="Nome">Nome do L2L:</label>
                                        <input type="text" class="form-control" id="cc_name" name="cc_name" placeholder="Digite a descrição do L2L" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="VAS_PROFILE">VAS Profile:</label>
                                        <input type="text" class="form-control" id="VAS_PROFILE" name="VAS_PROFILE" placeholder="Digite o VAS Profile" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="LINE_PROFILE">Line Profile:</label>
                                        <input type="text" class="form-control" id="LINE_PROFILE" name="LINE_PROFILE" placeholder="Digite o Line Profile" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="SERVICE_PROFILE">Service Profile:</label>
                                        <input type="text" class="form-control" id="SERVICE_PROFILE" name="SERVICE_PROFILE" placeholder="Digite o Service Profile" required>
                                    </div>
                                    <div class="form-group">
                                        <p>Selecione o Plano:</p>
                                        <select id="planos" class="form-group packetListClass" name="packet" data-size=5 data-live-search="true">
                                        </select>
                                    </div>

                                    <input type="hidden" id="typeRequisition" name="type_requisition" value="add">
                                    <input type="hidden" id="editVlanId" name="edit_vlan">

                                    <div id='portgem' class="form-group" style="display: inline-flex;">
                                        <div id="gemport">
                                            <div>
                                                <label for="gem_port">Gem Port:</label>
                                                <input type="number" class="form-control" id="gem_port" name="gem_port[]" style="display: inline-block; width: 100%" required>
                                            </div>
                                            <div>
                                                <label for="vlan_id">Vlan Id:</label>
                                                <input type="number" class="form-control" id="vlan_id" name="vlan_id[]"  style="display: inline-block; width: 100%" required>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" id="clonar-div" class="btn btn-primary  btn-block" onclick="clonarDiv()" style="margin-bottom: 10px;width: 50%; margin-left: 25%">Adicionar</button>
                                    <div id="clones"></div>
                                    <button id="buttonAddEdit" type="submit" class="btn btn-primary  btn-block">Salvar</button>
                                </div>
                            </form>
                        </div>

                        <div id="l2ledit" style="border-left: solid; padding-left: 10px; width: 50%">
                            <p>Clear Channel Cadastrados</p>
                            <select id="l2l_select" size="13" style="width: 100%; margin-bottom: 16%">
                            </select>

                            <button class="btn btn-primary  btn-block" onclick="changeLanLan()" style="">Alterar</button>
                            <button id="cancelEditButton" class="btn btn-primary btn-block" onclick='window.location.reload()' style="" disabled>Cancelar Alteração</button>
                            <button id="deleteButton" class="btn btn-primary  btn-block" onclick="deleteLanLan()" style="" disabled>Excluir</button>
                        </div>
                    </div>
                </div>
            </div>
         </div>
    </div>



<?php include_once "../classes/html_fim.php";   ?>
<script>
    initializeLanLans();
    initializePacket();

    document.getElementById('gem_port').addEventListener("keydown", preventSpace);
    document.getElementById('vlan_id').addEventListener("keydown", preventSpace);
</script>
