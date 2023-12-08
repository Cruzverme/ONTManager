function addOptions(response, id) {
    const selectElement = $(id);
    selectElement.empty();

    response.forEach((l2l) => {
        selectElement.append($('<option>', {
            value: l2l.id,
            text: l2l.name
        }));
    });
}

async function initializePacket() {
    try {
        const response = await fetch('Model/PacketManager.php', {
            headers: {
                'Accept': 'application/json',
            },
        });
        const text = await response.json();
        addOptions(text, '#planos');
        $('#planos').selectpicker();
    } catch (error) {
        console.error('Erro ao buscar pacotes:', error);
    }
}

async function initializeLanLans() {
    try {
        const response = await fetch('Model/L2LManagement.php', {
            headers: {
                'Accept': 'application/json',
            }
        });
        const text = await response.json();
        addOptions(text, '#l2l_select');
    } catch (error) {
        console.error('Erro ao buscar Lan to Lan:', error);
    }
}

async function initializePacketByCplus() {
    let contract = document.getElementById('contract').value;
    if (contract.length === 0) return;

    try {
        let url = `Model/L2LByCplusCode.php?contract=${contract}`
        const response = await fetch(url, {
            headers: {
                'Accept': 'application/json',
            },
        });
        const text = await response.json();
        $('#vlanInfo').empty();

        if (text.length > 0) {
            $('#vlanInfo').removeAttr('disabled')
            $(".btnContinue").removeAttr('disabled');
            text.forEach((l2l) => {
                $('#vlanInfo').append($('<option>', {
                    class: 'l2lName',
                    value: l2l.name,
                    text: l2l.name
                }));
            });
            return;
        }
        $('.gems').val('');
        $('.vas').val('');
        $('.line').val('');
        $('.serv').val('');
        $('#vlanInfo').attr('disabled', true);
        $(".btnContinue").attr('disabled', true);
    } catch (error) {
        console.error('Erro ao buscar pacotes:', error);
    }
}

function adicionarBotaoRemover(div) {
    const botaoRemover = document.createElement("button");
    botaoRemover.textContent = "Remover";
    botaoRemover.style.backgroundColor = 'orange';
    botaoRemover.style.borderRadius = '10px';
    botaoRemover.addEventListener("click", function () {
        div.remove();
    });
    div.appendChild(botaoRemover);
}

function clonarDiv(gemport = '', vlanId = '') {
    const l2lOptions = document.getElementById("portgem");
    const novaDivClonada = l2lOptions.cloneNode(true);

    // Exibindo a div clonada, caso ela esteja oculta
    novaDivClonada.style.display = "block";

    // Limpando os valores dos inputs na nova div clonada
    const inputs = novaDivClonada.querySelectorAll("input");
    inputs.forEach((input) => (input.value = ""));
    const cloneLocal = document.getElementById('clones')
    // Adicionando a div clonada após a última div original
    cloneLocal.appendChild(novaDivClonada);

    // Adicionar botão "Remover" à nova div clonada
    adicionarBotaoRemover(novaDivClonada);

    // Adicionar atributos required e evento keydown aos inputs clonados
    const inputsClone = novaDivClonada.querySelectorAll("input");
    inputsClone.forEach((input) => {
        input.setAttribute("required", "required");
        input.addEventListener("keydown", preventSpace);

        if (input.id === 'vlan_id' && vlanId.length > 0) {
            input.value = vlanId;
        }
        if (input.id === 'gem_port' && gemport.length > 0) {
            input.value = gemport;
        }
    });
}

function preventSpace(event) {
    if (event.keyCode === 32) {
        event.preventDefault();
    }
}

function deleteLanLan() {
    bootbox.confirm({
        message: `Deseja realmente Excluir?`,
        buttons: {
            confirm: {
                label: '<i class="fa fa-check"></i> SIM',
                className: 'btn-success'
            },
            cancel: {
                label: '<i class="fa fa-times"></i> NAO',
                className: 'btn-danger'
            }
        },
        centerVertical: true,
        callback: (buttonResponse) => {
            if (buttonResponse) {
                let lanIdToDelete = document.getElementById('l2l_select').value;

                if (lanIdToDelete.length > 0) {
                    removeLanLan(lanIdToDelete);
                    window.location.reload();
                }
            }

        }
    });
}

function removeLanLan(lanlanId)
{
    let url = `Model/L2LManagement.php?id=${lanlanId}&method=delete`
    $.ajax({
        url: url,
        type: 'DELETE',
        async: false,
        success: function (response) {
            responseMessage = response;
        },
        error: function (error) {
            responseMessage = "Erro ao excluir.";
        }
    });
    return responseMessage;
}

$('#l2l_select').change(() => {
    $('#deleteButton').attr('disabled', false);
})
async function changeLanLan() {
    let typeRequisition = document.getElementById('typeRequisition');
    let ccNameInput = document.getElementById('cc_name');
    let vasProfileInput = document.getElementById('VAS_PROFILE');
    let lineProfileInput = document.getElementById('LINE_PROFILE');
    let serviceProfileInput = document.getElementById('SERVICE_PROFILE');
    let planosSelect = document.getElementById('planos');
    let lanIdToEdit = document.getElementById('l2l_select').value;
    let button = document.getElementById('buttonAddEdit');
    let cancelEditButton = document.getElementById('cancelEditButton');
    let gemPortInputInitial = document.getElementById('gem_port');
    let vlanIdInputInitial = document.getElementById('vlan_id');
    let idVlanInput = document.getElementById('editVlanId');

    let url = `Model/L2LManagement.php?id=${lanIdToEdit}`

    if (!lanIdToEdit) {
        alert('Não foi selecionado um Clear Channel. Por favor, selecione');
        return;
    }
    const response = await fetch(url, {
        method: 'get',
        headers: {
            'Accept': 'application/json',
        },
    });
    const data = await response.json();
    const gemports = JSON.parse(data[0].gem_ports);

    ccNameInput.value = data[0].name;
    vasProfileInput.value = data[0].vas_profile;
    lineProfileInput.value = data[0].line_profile;
    serviceProfileInput.value = data[0].service_profile;
    idVlanInput.value = lanIdToEdit;

    for (var i = 0; planosSelect.options.length; i++) {
        let option = planosSelect.options[i];
        if (option.value === data[0].planos_id) {
            planosSelect.selectedIndex = i;
            $('#planos').selectpicker('refresh')
            break;
        }
    }
    button.textContent = 'Alterar';
    cancelEditButton.removeAttribute('disabled')
    typeRequisition.value = 'edit';

    document.getElementById('clones').innerText = ''

    let cont = 0;
    for (var vlanId in gemports) {
        if (!cont) {
            gemPortInputInitial.value = gemports[vlanId];
            vlanIdInputInitial.value = vlanId;
            cont++;
            continue;
        }

        this.clonarDiv(gemports[vlanId], vlanId)
    }
}

//Only in register_customer
$('input[name="internet_checked"]').change(function () {
    if ($('input[name="internet_checked"]:checked').val() === "Internet") {
        $('select[name="pacote"]').attr("required", "required");
        $('.camposPacotes').show();
    } else {
        $('select[name="pacote"]').removeAttr("required", "required");
        $('.camposPacotes').hide();
    }
});


$('input[name="telefone_checked"]').change(function () {
    if ($('input[name="telefone_checked"]:checked').val() === "Telefone") {
        $('input[name="numeroTel"]').attr("required", "required");
        $('input[name="passwordTel"]').attr("required", "required");
        $('.camposTelefone').show();
    } else {
        $('input[name="numeroTel"]').removeAttr("required");
        $('input[name="passwordTel"]').removeAttr("required");
        $('.camposTelefone').hide();
    }
});

function addUserWithLanProcess() {

    var body = $('#page-wrapper');

    $(document).on({
        ajaxStart: function () {
            body.addClass("loading");
        }
    });

    var nome = $("input[name='nome']").val(),
        vasProfile = $("input[name='vasProfile']").val(),
        serial = $("input[name='serial']").val(),
        pacote_internet = $("select[name='pacote']").val(),
        modelo_ont = $("select[name='equipamentos']").val(),
        sip_number = $("input[name='numeroTel']").val(),
        sip_password = $("input[name='passwordTel']").val(),
        porta_atendimento = $("input[name='porta_atendimento']").val(),
        frame = $("input[name='frame']").val(),
        slot = $("input[name='slot']").val(),
        pon = $("input[name='pon']").val(),
        cto = $("input[name='caixa_atendimento_select']").val(),
        device = $("input[name='deviceName']").val(),
        contrato = $("input[name='contrato']").val(),
        designacao = $("input[name='designacao']").val(),
        vlan_number = $("input[name='vlan_number']").val(),
        internet_check = $("input[name='internet_checked']:checked").val(),
        vlan_check = $("input[name='l_to_l']:checked").val(),
        iptv = $("input[name='iptv_checked']:checked").val(),
        voip = $("input[name='telefone_checked']:checked").val(),
        vas = $("input[name='vas']").val(),
        gems = $("input[name='gems']").val(),
        line = $("input[name='line']").val(),
        serv = $("input[name='serv']").val();

    $.post('../classes/ClearChannelProcessCreator.php',
        {
            nome, vasProfile, serial, pacote_internet,
            modelo_ont, sip_number, sip_password, porta_atendimento, frame, slot, pon, cto, device: device, contrato,
            designacao, vlan_number, internet_check, vlan_check, iptv, voip, vas, gems, line, serv
        }, function (msg_retorno) {
            response = JSON.parse(msg_retorno);
            alert(response.message)
            if (response.status  === 200)
                window.location.replace('select_cto.php')

            if (msg_retorno)
                body.removeClass("loading");
        });
}
