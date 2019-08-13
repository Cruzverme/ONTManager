$('input[name="optionsRadios"]').change(function () {
    if ($('input[name="optionsRadios"]:checked').val() === "VAS_Internet-VoIP" || 
        $('input[name="optionsRadios"]:checked').val() === "VAS_Internet-VoIP-IPTV" ||
        $('input[name="optionsRadios"]:checked').val() === "VAS_IPTV-VoIP" ||
        $('input[name="optionsRadios"]:checked').val() === "VAS_Internet-VoIP-IPTV-REAL" ||
        $('input[name="optionsRadios"]:checked').val() === "VAS_Internet-VoIP-REAL" ||
        $('input[name="optionsRadios"]:checked').val() === "VAS_Internet-twoVoIP" ||
        $('input[name="optionsRadios"]:checked').val() === "VAS_Internet-twoVoIP-IPTV" ||
        $('input[name="optionsRadios"]:checked').val() === "VAS_Internet-twoVoIP-REAL" ||
        $('input[name="optionsRadios"]:checked').val() === "VAS_Internet-twoVoIP-IPTV-REAL" ||
        $('input[name="optionsRadios"]:checked').val() === "VAS_Internet-VoIP-CORP-IP" ||
        $('input[name="optionsRadios"]:checked').val() === "VAS_Internet-VoIP-CORP-IP-Bridge")
    {
      $('input[name="numeroTel"]').attr("required", "required");
      $('input[name="passwordTel"]').attr("required", "required");
      $('.camposTelefone').show();
    } else {
      $('input[name="numeroTel"]').removeAttr("required");
      $('input[name="passwordTel"]').removeAttr("required");
      $('.camposTelefone').hide();
    }
    console.log($("input[name='numeroTel']").attr("required"));
});

$('input[name="optionsRadios"]').change(function () {
    if ($('input[name="optionsRadios"]:checked').val() === "VAS_IPTV" ||
        $('input[name="optionsRadios"]:checked').val() === "VAS_IPTV-VoIP" ||
        $('input[name="optionsRadios"]:checked').val() === "VAS_IPTV-VoIP-REAL" ||
        $('input[name="optionsRadios"]:checked').val() === "VAS_IPTV-twoVoIP" )
    {
      $('select[name="pacote"]').removeAttr("required","required");
      $('.camposPacotes').hide();
    } else {
      $('select[name="pacote"]').attr("required","required");
      $('.camposPacotes').show();
    }
});

$('input[name="cgnat_status"]').change(function(){
  if( $('input[name="cgnat_status"]').val() === 'ip_real_ativo'&&
      ($('input[name="profileVas"]').val() === "VAS_Internet-REAL" ||
      $('input[name="profileVas"]').val() === "VAS_Internet-IPTV-REAL" ||
      $('input[name="profileVas"]').val() === "VAS_Internet-VoIP-REAL" ||
      $('input[name="profileVas"]').val() === "VAS_Internet-VoIP-IPTV-REAL" ||
      $('input[name="profileVas"]').val() === "VAS_Internet-twoVoIP-REAL" ||
      $('input[name="profileVas"]').val() === "VAS_Internet-twoVoIP-IPTV-REAL") ) 
  {
    switch ($('input[name="profileVas"]').val()) {
      case 'VAS_Internet-REAL':
        $('input[name="profileVas"]').val('VAS_Internet')
        break;
      case 'VAS_Internet-IPTV-REAL':
        $('input[name="profileVas"]').val('VAS_Internet-IPTV')
        break;
      case 'VAS_Internet-VoIP-REAL':
        $('input[name="profileVas"]').val('VAS_Internet-VoIP')
        break;
      case 'VAS_Internet-VoIP-IPTV-REAL':
        $('input[name="profileVas"]').val('VAS_Internet-VoIP-IPTV')
        break;
      case 'VAS_Internet-twoVoIP-REAL':
        $('input[name="profileVas"]').val('VAS_Internet-twoVoIP')
        break;
      case 'VAS_Internet-twoVoIP-IPTV-REAL':
        $('input[name="profileVas"]').val('VAS_Internet-twoVoIP-IPTV')
        break;
      default:
        break;
    }
    $('#optionsRadios1').val("VAS_Internet")
    $('#optionsRadios3').val("VAS_Internet-IPTV")
    $('#optionsRadios4').val("VAS_Internet-VoIP")
    $('#optionsRadios6').val("VAS_Internet-VoIP-IPTV")
  }else{

    switch ($('input[name="profileVas"]').val()) {
      case 'VAS_Internet':
        $('input[name="profileVas"]').val('VAS_Internet-REAL')
        break;
      case 'VAS_Internet-IPTV':
        $('input[name="profileVas"]').val('VAS_Internet-IPTV-REAL')
        break;
      case 'VAS_Internet-VoIP':
        $('input[name="profileVas"]').val('VAS_Internet-VoIP-REAL')
        break;
      case 'VAS_Internet-VoIP-IPTV':
        $('input[name="profileVas"]').val('VAS_Internet-VoIP-IPTV-REAL')
        break;
      default:
        break;
    }

    $('#optionsRadios1').val("VAS_Internet-REAL")
    $('#optionsRadios3').val("VAS_Internet-IPTV-REAL")
    $('#optionsRadios4').val("VAS_Internet-VoIP-REAL")
    $('#optionsRadios6').val("VAS_Internet-VoIP-IPTV-REAL")
  }
});

$('select[name="equipamentos"]').change(function(){
  var second_phone_user = $("#tel2_user");
  var second_phone_pass = $("#tel2_pass");
  if($('select[name="equipamentos"] option:selected').val() === "EG8245H5" ) {
    second_phone_user.append(
                              "<label>Telefone</label>" +
                              "<input class='form-control' placeholder='Segundo Telefone' name='numeroTelNovo2' type='text' autofocus>"
                            );
    second_phone_pass.append(
                              "<label>Senha do Telefone</label>" +
                              "<input class='form-control' placeholder='Senha do Segundo Telefone' name='passwordTelNovo2' type='text' autofocus>"
                            );                           
  }else{
    second_phone_user.val() === ""? second_phone_user.empty() : "";
    second_phone_pass.val() === ""? second_phone_pass.empty() : "";
  }
});

$('input[name="nivel"]').change(function () {
    if ($('input[name="nivel"]:checked').val() === "1" ) {
        $('.camposPermissao').hide();
    } else {
        $('.camposPermissao').show();
    }
});

$('input[name="optionsRadiosConsulta"]').change(function () {
    if ($('input[name="optionsRadiosConsulta"]:checked').val() === "cto" )
    {
        $('.campoCto').show();
    } else {
        $('.campoCto').hide();
    }
});

$('input[name="optionsRadiosConsulta"]').change(function () {
  if ($('input[name="optionsRadiosConsulta"]:checked').val() === "pon" )
  {
      $('.camposOLT').show();
  } else {
      $('.camposOLT').hide();
  }
});


$('input[name="optionsRadiosConsulta"]').change(function () {
  if ($('input[name="optionsRadiosConsulta"]:checked').val() === "disponibilizaCTO" )
  {
      $('.campoCtoDisponibiliza').show();
  } else {
      $('.campoCtoDisponibiliza').hide();
  }
});

$('input[name="modo_bridge"]').change(function () {
  if($('input[name="modo_bridge"]:checked').val() === "mac_externo")
  {
    $(".bridge").show();
  }
  else
  {
    $(".bridge").hide();
  }
});


$('input[name="optionsRadios"]').change(function () {
  if($('input[name="optionsRadios"]:checked').val() === "VAS_Internet-CORP-IP-Bridge" ||
      $('input[name="optionsRadios"]:checked').val() === "VAS_Internet-IPTV-CORP-IP-Bridge" ||
      $('input[name="optionsRadios"]:checked').val() === "VAS_Internet-VoIP-CORP-IP-Bridge" ||
      $('input[name="optionsRadios"]:checked').val() === "VAS_Internet-VoIP-IPTV-CORP-IP-Bridge" ||
      $('input[name="optionsRadios"]:checked').val() === "VAS_Internet-VoIP-CORP-IP-Bridge")
  {
    $("input[name='modo_bridge']").attr('checked',true);
    $(".bridge").show();
  }
  else
  {
    $("input[name='modo_bridge']").attr('checked',false);
    $(".bridge").hide();
  }
});

$('input[name="optionsRadios"]').change(function(){
  if($('input[name="optionsRadios"]:checked').val() === "VAS_Internet-CORP-IP" ||
      $('input[name="optionsRadios"]:checked').val() === "VAS_Internet-VoIP-CORP-IP" ||
      $('input[name="optionsRadios"]:checked').val() === "VAS_Internet-CORP-IP-Bridge" ||
      $('input[name="optionsRadios"]:checked').val() === "VAS_Internet-IPTV-CORP-IP-Bridge" ||
      $('input[name="optionsRadios"]:checked').val() === "VAS_Internet-VoIP-CORP-IP-Bridge" ||
      $('input[name="optionsRadios"]:checked').val() === "VAS_Internet-VoIP-IPTV-CORP-IP-Bridge" ||
      $('input[name="optionsRadios"]:checked').val() === "VAS_Internet-VoIP-IPTV-CORP-IP" || 
      $('input[name="optionsRadios"]:checked').val() === "VAS_Internet-VoIP-CORP-IP" ||
      $('input[name="optionsRadios"]:checked').val() === "VAS_Internet-VoIP-CORP-IP-Bridge")
  {
     $(".ipFixoSelector").show();
  }else{
     $(".ipFixoSelector").hide();
  }
});

$('input[name="status_cto"]').change(function(){
  var variavel = $(this).val();
  var por = variavel.split("_");
  var cto 
  var cto_disponibilidade;

  cto = por[1];
  cto_disponibilidade = por[0];

  if(cto_disponibilidade == 1)
  {
    cto_disponibilidade = 0;
    $.post("../classes/gerencia_liberacao_cto.php",{cto_disponibilidade, cto} ,function(msg_retorno){
      var msg = msg_retorno;
      
      if(msg == 1)
      {
        $(this).val('0_'+por[1]);
        alert('CTO Desativada');
        document.getElementById("linha_status_cto_disponivel_"+por[1]+"").style.backgroundColor = "red";
        location.reload();
      }else{
        document.getElementById("cto_libera_nome_"+por[1]+"").checked = true;
        alert('Não foi Possível desativar a CTO');
      }
    });
  }
  else{
    cto_disponibilidade = 1;
    $.post("../classes/gerencia_liberacao_cto.php",{cto_disponibilidade, cto} ,function(msg_retorno){
      var msg = msg_retorno;
      
      if(msg == 1)
      {
        $(this).val('1_'+por[1]);
        alert('CTO Ativada');
        document.getElementById("linha_status_cto_disponivel_"+por[1]+"").style.backgroundColor = 'green';
        location.reload();
      }else{
        document.getElementById("cto_libera_nome_"+por[1]+"").checked = false;
        alert('Não foi Possível ativar a CTO');
      }
    });
  }
});

// INICIO MODAL

$(function(){
  $('#listaSinaisModal').modal({
      keyboard: true,
      show:false,
  }).on('show.bs.modal', function(){ //subscribe to show method
        var modalVerb = $(this);
        var getIdFromRow = $(event.target).closest('tr').data('pon'); //get the id from tr
        $.post("../consultas/get_sinal_pon.php",{frame_slot_pon: getIdFromRow} ,function(msg_retorno){
          var msg = msg_retorno;
          //make your ajax call populate items or what even you need
          modalVerb.find('#listaSinaisDetails').html($(msg  + ''))
        });
  });
});
//FIm MODAL

function sair_da_tela(){
  window.location.href('./transfer_olt_select.php')
}

$(function(){
  $("#tabelaSinais").dataTable();
})

$("tr.porta").on('click',function() {
    var porta_selecionada;
    var serial;
    var caixa;
    var tableData = $(this).children("td").map(function(){
    return $(this).text();
    }).get();

    caixa = $.trim(tableData[2]);
    serial = $.trim(tableData[1]);
    porta_selecionada = $.trim(tableData[0]);
    window.location.href='../classes/salva_porta_atendimento.php?porta_atendimento_selecionada='+porta_selecionada+
        '&caixa_atendimento='+caixa +'&serial='+serial;
});

//ACAO AO CLICAR NO BOTAO IMPLEMENTAR FUTURAMENTE
$('.btn-salvar').on('click',function(){
  alert('Salvo');
   $('#modal-texto').modal('hide');
});

 $('tr.usuarios').click(function() {
     window.location.href = $(this).attr('data-href');
 });

if($("#cto_transfer_padrao").length){
  $(document).on('click', '.cto_transfer', function(){
    var limit = 1;
    var counter = $('.cto_transfer:checked').length;
    console.log("LIX");
    if(counter > limit) {
      this.checked = false;
      alert('Só é permitido transferir 1 por vez!');
    }
  });
}


  if($("#cto_transfer_desativada").length){
    $(document).on('click', '.cto_transfer', function(){
        var limit = 1;
        var counter = $('.cto_transfer:checked').length;
        console.log("LIX");
        if(counter > limit) {
          this.checked = false;
          alert('Limite atingido');
        }
    });
  }else{
    $(document).on('click', '.cto_transfer', function(){
      var limit = 2;
      var counter = $('.cto_transfer:checked').length;

      if(counter > limit) {
        this.checked = false;
        alert('Limite atingido');
      }
    });
  }
    
  function mudar_status_cto() {
    bootbox.confirm({
      title: "Atenção",
      message: "Deseja Alterar Essas Celulas?",
      buttons: {
                confirm: {
                  label: '<i class="fa fa-check"></i> SIM',
                  className: 'btn-success'
                },
                cancel: {
                  label: '<i class="fa fa-times"></i> NAO',
                  className: 'btn-danger'
                }
              },callback: function(escolhaDoUsuario){
                if(escolhaDoUsuario)
                {
                  var cto_dis = $(".cto_check:checked").serialize();
                  //console.log(cto_dis);
                  var cto_unchecked = [];

                  $(".cto_check").each(function(){
                    if($(this).find($(this)).not(":checked"))
                    {
                      cto_unchecked.push($(this).val())
                    }
                  });
                  //console.log(cto_unchecked);
                  $.post("../consultas/altera_dispo.php",{cto_disponibilidade: cto_dis, unchecked: cto_unchecked} ,function(msg_retorno){
                    bootbox.alert({
                        title: "Status das Células",
                        message: msg_retorno,
                        callback: function(){
                          location.reload();
                        }
                    });
                  });
                }else{

                }
              }
    });  
  }

  $(document).ready(function () { 
    var $seuCampoMAC = $("#mac");
    $seuCampoMAC.mask('00:00:00:00:00:00', 
                      {translation: {0: {pattern:/[a-f0-9]/} }});
  });

  function acordaONT(device,frame,slot,pon,ontID,acao) 
  { 
    var devName = device;
    var frame = frame;
    var slot = slot;
    var pon = pon;
    var ontID = ontID;
    var tipoDeAcao = acao;
    
    bootbox.confirm({
      message: "Deseja realizar esta operação ?",
      buttons: {
        confirm: {
          label: '<i class="fa fa-check"></i> SIM',
          className: 'btn-success'
        },
        cancel: {
          label: '<i class="fa fa-times"></i> NAO',
          className: 'btn-danger'
        }
      },callback: function(escolhaDoUsuario){
        if(escolhaDoUsuario)
        {
          $.post("../consultas/_helper_show_status.php",{dev: devName,frame: frame,slot: slot,pon: pon,ont: ontID,acao: tipoDeAcao} ,function(msg_retorno){
            bootbox.alert({
              message: msg_retorno,
              backdrop: true,
              size: 'small'
            });
          });
        }else{
          bootbox.alert({
            message: 'OPERAÇÃO CANCELADA PELO USUÁRIO',
            backdrop: true,
            size: 'small'
          });
        }
      }
    });
  }


  function listUserForDnat(olt)
  {
    let quantidade = $('#quantidade_clientes').val();
    let area_id = olt;
    $body = $("body");
    
    $(document).on({
      ajaxStart: function() { $body.addClass("loading"); },
    });

    $.post("../classes/lista_clientes_dnat.php",{quantidade, area_id} ,function(msg_retorno){
      console.log(msg_retorno);
      var msg = $.parseJSON(msg_retorno);
      console.log(msg);
      var i;
      if(msg.length > 0)
      {
        $("#listaClientes").empty();
        $("#listaClientes").append('<hr/>');
        for(i=0;i < msg.length;i++)
        {
          console.log("TOTAL: " + msg.length + " LISTA: " + msg[i]['serial'] + "<br>");
          
          $("#listaClientes").append('<div class="form-control" style="text-align: center;">' + 
                                        'Contrato:'+ msg[i]['contrato'] + 
                                        ' Serial:'+ msg[i]['serial'] +
                                        ' CTO:'+ msg[i]['cto'] 
                                    +'</div>');

          $("#listaClientes").append('<input type="hidden" name="contrato[]" value='+ 
                                          msg[i]['contrato']+ '.' +
                                          msg[i]['serial']+ '.' +
                                          msg[i]['status']+ '.' +
                                          msg[i]['cto'] + '.' +
                                          msg[i]['porta']+ '.' +
                                          msg[i]['usuario_id']+ '.' +
                                          msg[i]['tel_number']+ '.' +
                                          msg[i]['tel_user']+ '.' +
                                          msg[i]['tel_password']+ '.' +
                                          msg[i]['perfil']+ '.' +
                                          msg[i]['pacote']+ '.' +
                                          msg[i]['limite_equipamentos']+ '.' +
                                          msg[i]['equipamento']+ '.' +
                                          msg[i]['ontID']+ '.' +
                                          msg[i]['service_port_internet']+ '.' +
                                          msg[i]['service_port_iptv']+ '.' +
                                          msg[i]['service_port_telefone'] + '.' + 
                                          msg[i]['deviceName'] + '.' +
                                          msg[i]['frame_slot_pon'] + '.' +
                                          msg[i]['ip'] +
                                      '></input>');
        }
        $('.btn-efetua-nat').removeAttr("disabled");
        $('.btn-cancela-lista').removeAttr("disabled");
        $('.btn-listar-clientes').attr("disabled", true);
        $body.removeClass("loading");
      }else{
        $("#listaClientes").empty();
        $("#listaClientes").append('<hr/>');
        $("#listaClientes").append('<div class="form-control" style="text-align: center;">' +
                                      'Não encontrado assinantes neste SLOT!' +
                                  '<div/>'); 
        $body.removeClass("loading");
        console.log('Estou Vazio!')
      }
    });
  }

  function ativar_lista()
  {
    $('.btn-efetua-nat').attr("disabled", true);
    $('.btn-cancela-lista').attr("disabled", true);
    $('.btn-listar-clientes').removeAttr("disabled");
    $('#quantidade_clientes').val(0);
    $('#listaClientes').empty()
  }

  function showModalVlan(vlanID){
    $('#listaCostumerDetails').empty();
    $('#listaClientesVlanModal').modal({
        keyboard: true,
        show:false,
    }).on('show.bs.modal', function(){ //subscribe to show method
        
        var modalVerb = $(this);
        var vlan = vlanID; // $("#vlan").html();
        console.log(vlan);
        $.post("../classes/get_vlan_costumers_list.php",{vlan}, function(msg_retorno){          
          
          modalVerb.find('#listaCostumerDetails').html( "<div>"
                                                          +"<strong>Contrato Clientes</strong>"
                                                        +"</div> <hr>"
                                                        +msg_retorno );
        });
        vlan = "";
    });
  };

  // ASSOCIAÇÃO DE VLAN
  function vlan_association(vlanID){
    var vlan = vlanID;
    window.location.href='../vlan/associar_vlan.php?vlan='+vlan;
  };

  $('#add_association').on('click', function(){
    
    $(".panel_associados").css("visibility","visible");

    var contra = $("#contratoID").val();
    var contratos = contra.split(',');
    $("#contratoID").val('');
    if( contra.trim() !== "")
    {
      contratos.forEach(element => {
        $.post("../classes/get_cplus_name.php",{nContra: element}, function(msg_retorno){
          if(msg_retorno != "Nome não encontrado!")
          {  
            $("#contrato").append('<div type="text" form-control">'+ element +' Nome:'+ msg_retorno +'</div>');
            $("#contrato").append('<input type=hidden name="contratos[]" value="'+element+'" />');
          }
        });
      });
    }else{
      alert("Insira um contrato!");
    }
  });

  //DESASSOCIAÇÃO DE VLAN
  function vlan_dissociation(vlanID){
    var vlan = vlanID;
    window.location.href='../vlan/desassociar_vlan.php?vlan='+vlan;
  }
  
//VALIDAR SEHA
if(document.getElementById("senha") != null || document.getElementById("confirma_senha") != null )
{
  var password = document.getElementById("senha"), confirm_password = document.getElementById("confirma_senha");
  password.onchange = validatePassword(password);
  confirm_password.onkeyup = validatePassword;

  function validatePassword(){
    if(password.value != confirm_password.value) {
      confirm_password.setCustomValidity("Senhas Não Conferem");
    } else {
      confirm_password.setCustomValidity('');
    }
  } 
}
//FIM VALIDAR
