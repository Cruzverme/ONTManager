$(".informacoes_legend").click(function(){
  
  var icone = $(this).find("i.fa");
  
  if(icone.attr('class') == "fa fa-chevron-down")
  {
    icone.removeClass("fa-chevron-down");
    icone.addClass("fa-chevron-up");
  }else
  {
    icone.removeClass("fa-chevron-up")
    icone.addClass("fa-chevron-down")
  }

  $(this).find('.hider_infos').toggle();
});

$(".radio-planos-legend").click(function(){
  $(".hider_planos").toggle();
});

function cadastrar()
{
  var body = $("#page-wrapper");

  $(document).on({
     ajaxStart: function() {body.addClass("loading")}
  })

  var pacote = $("select[name='pacote']").val() ;
  var vasProfile = $("input[name='optionsRadios']:checked").val();
  var cto = $("input[name='caixa_atendimento_select']").val();
  var frame = $("input[name='frame']").val();
  var slot = $("input[name='slot']").val();
  var pon = $("input[name='pon']").val();
  var contrato = $("input[name='contrato']").val();
  var nome = $("input[name='nome']").val();
  var serial = $("input[name='serial']").val();
  var equipamento = $("select[name='equipamentos']").val();
  var numeroTel = $("input[name='numeroTel']").val();
  var passwordTel = $("input[name='passwordTel']").val();
  var numeroTelNovo2 = $("input[name='numeroTelNovo2']").val();
  var passwordTelNovo2 = $("input[name='passwordTelNovo2']").val();
  var porta_atendimento = $("input[name='porta_atendimento']").val();
  var deviceName = $("input[name='deviceName']").val();
  var mac = $("input[name='mac']").val();
  var ip_fixo = $("select[name='ipFixo']").val();
  var modo_bridge = $("input[name='modo_bridge']:checked").val();
  
  $.post("../classes/cadastrar.php",
  {
    pacote,
    caixa_atendimento_select: cto,
    frame,slot,pon,contrato,nome,serial,
    equipamentos: equipamento,
    numeroTel,passwordTel,numeroTelNovo2,passwordTelNovo2,
    optionsRadios: vasProfile,
    porta_atendimento,deviceName,mac,
    ipFixo: ip_fixo,
    modo_bridge
  },
  function(msg){
    bootbox.alert({
      message: msg,
      callback: function(){
        if(msg.includes("sucesso"))
        {
          window.location.replace('../ont_classes/ont_register.php');
          body.removeClass("loading");
        }else{
          body.removeClass("loading");
        }
      }
    });
  })
}


//  TRATAMENTO DE REMOCAO
($("#contrato")).keypress(function(e) {
  if(e.which == 13)  consultar();
});

$("#contrato-remocao").keypress(function(e){
  if(e.which == 13) check_contrato();
})

function check_contrato(){
  var contrato = $("#contrato-remocao").val();
  
  if(contrato.trim() === '')
  {
    bootbox.alert({message:"<p style='text-align:center;color:blue'>Favor Inserir Contrato</p>",size:'small'});
  }else{
    
    var url = '../ont_classes/_ont_delete_search_result.php';

    var form = $('<form action="' + url + '" method="post">' +
      '<input type="text" name="contrato" value="' + contrato + '" />' +
      '</form>');
    $('body').append(form);
    form.submit();
  }
}

function deletar(){
  var contrato = $("input[name='contrato']").val();
  var serial = $("select[name='serial']").val();

  var body = $("#page-wrapper");

  $(document).on({
    ajaxStart: function() {body.addClass("loading")}
  })

  bootbox.confirm({
    message:"<p style='text-align: center;'>Deseja Remover o Contrato "+contrato+"?</p>",
    buttons: {
      confirm: {
          label: 'Sim',
          className: 'btn-success'
      },
      cancel: {
          label: 'Não',
          className: 'btn-danger'
      }
    },
    callback: function(retorno){
      if(retorno)
      {
        $.post("../classes/deletar.php",{contrato,serial: serial},function(msg)
        {
          bootbox.alert({
            message:msg,
            callback: function(){
              if(msg.includes("deletada"))
              {
                window.location.replace('../ont_classes/ont_delete.php');
                body.removeClass("loading");
              }else{
                body.removeClass("loading");
              }
            }
          });
        })
      }else{
        bootbox.alert({
          size:'small',
          message: "<p style='text-align:center;'>Operação Cancelada!</p>"
        })
      }
    }
  })
}

// MODIFICAR ONT
//exibe informacoes de telefonia
$('select[name="vasProfile"]').change(function () {
  if ($(this).val() === "VAS_Internet-VoIP" || 
      $(this).val() === "VAS_Internet-VoIP-IPTV" ||
      $(this).val() === "VAS_IPTV-VoIP" ||
      $(this).val() === "VAS_Internet-VoIP-IPTV-REAL" ||
      $(this).val() === "VAS_Internet-VoIP-REAL" ||
      $(this).val() === "VAS_Internet-twoVoIP" ||
      $(this).val() === "VAS_Internet-twoVoIP-IPTV" ||
      $(this).val() === "VAS_Internet-twoVoIP-REAL" ||
      $(this).val() === "VAS_Internet-twoVoIP-IPTV-REAL" ||
      $(this).val() === "VAS_Internet-VoIP-CORP-IP" ||
      $(this).val() === "VAS_Internet-VoIP-IPTV-CORP-IP" ||
      $(this).val() === "VAS_Internet-VoIP-CORP-IP-Bridge" ||
      $(this).val() === "VAS_Internet-VoIP-IPTV-CORP-IP-B")
  {
    $('input[name="numeroTel"]').attr("required", "required");
    $('input[name="passwordTel"]').attr("required", "required");
    $('.camposTelefone').show();
  } else {
    $('input[name="numeroTel"]').removeAttr("required");
    $('input[name="passwordTel"]').val("");
    $('input[name="numeroTel"]').val("");
    $('input[name="passwordTelNovo2"]').val("");
    $('input[name="numeroTelNovo2"]').val("");
    $('input[name="passwordTel"]').removeAttr("required");
    $('.camposTelefone').hide();
  }
});

//mostra bridge 
$('input[name="modo_bridge_check"]').change(function () {
  if($('input[name="modo_bridge_check"]:checked').val() === "mac_externo" &&
    ($('select[name="vasProfile"]').val() === "VAS_Internet-CORP-IP-Bridge" ||
    $('select[name="vasProfile"]').val() === "VAS_Internet-IPTV-CORP-IP-Bridge" ||
    $('select[name="vasProfile"]').val() === "VAS_Internet-VoIP-CORP-IP-Bridge" ||
    $('select[name="vasProfile"]').val() === "VAS_Internet-VoIP-IPTV-CORP-IP-B" ||
    $('select[name="vasProfile"]').val() === "VAS_Internet-VoIP-CORP-IP-Bridge")
  )
  {
    $(".bridge_check").show();
    $(".bridge_modify").show();
  }
  else
  {
    $(".bridge_check").show();
    $(".bridge_modify").hide();
    $("input[name='mac']").val("");
  }
});


// mostra ou não o MAC do modo bridge
$('select[name="vasProfile"]').change(function () {
  if($(this).val() === "VAS_Internet-CORP-IP-Bridge" ||
      $(this).val() === "VAS_Internet-IPTV-CORP-IP-Bridge" ||
      $(this).val() === "VAS_Internet-VoIP-CORP-IP-Bridge" ||
      $(this).val() === "VAS_Internet-VoIP-IPTV-CORP-IP-B" ||
      $(this).val() === "VAS_Internet-VoIP-CORP-IP-Bridge")
  {
    $("input[name='modo_bridge_check']").prop('checked',true);
    $(".bridge_check").show();
    $(".bridge_modify").show();
  }
  else
  {
    $("input[name='modo_bridge_check']").prop('checked',false);
    $(".bridge_check").hide();
    $(".bridge_modify").hide();
    $("input[name='mac']").val("");
  }
});

// coloca campo de telefonia para 2 SIPs
$('select[name="equipamentos"]').change(function(){
  var second_phone_user = $("#tel2_user_modify");
  var second_phone_pass = $("#tel2_pass_modify");
  if($('select[name="equipamentos"] option:selected').val() === "EG8245H5" ) {
    second_phone_user.append(
                              "<label for='telefone2' class='col-sm-2 control-label'>Telefone</label>" +
                              "<div class='col-sm-4'>" +
                                "<input id='telefone2' class='form-control' placeholder='Segundo Telefone' name='numeroTelNovo2' type='text' autofocus>"+
                              "</div>"
                            );
    second_phone_pass.append(
                              "<label for='senha2' class='col-sm-2 control-label'>Senha</label>" +
                              "<div class='col-sm-4'>" +
                                "<input id='senha2' class='form-control' placeholder='Senha do Segundo Telefone' name='passwordTelNovo2' type='text' autofocus>"+
                              "</div>"
                            );
  }else{
    second_phone_user.val() === ""? second_phone_user.empty() : "";
    second_phone_pass.val() === ""? second_phone_pass.empty() : "";
  }
});


/// REMOVE PACOTE INTERNET QND SOMENTE IPTV
$('select[name="vasProfile"]').change(function () {
  if ($(this).val() === "VAS_IPTV" ||
      $(this).val() === "VAS_IPTV-VoIP" ||
      $(this).val() === "VAS_IPTV-VoIP-REAL" ||
      $(this).val() === "VAS_IPTV-twoVoIP" )
  {
    $('select[name="pacote"]').removeAttr("required","required");
    $('.camposPacotes').hide();
  } else {
    $('select[name="pacote"]').attr("required","required");
    $('.camposPacotes').show();
  }
});

/// REMOVE PACOTE INTERNET QND SOMENTE CONVERSOR ALTERAR
$('select[name="vasProfile"]').change(function () {
  if ($(this).val() === "conversorHFC" )
  {
    $('select[name="serial"]').removeAttr("required","required");
    $('select[name="pacote"]').removeAttr("required","required");

    $('.camposPacotes').hide();
    $('.conversorHide').hide();
  } else {
    $('select[name="serial"]').attr("required","required");
    $('select[name="pacote"]').attr("required","required");

    $('.camposPacotes').show();
    $('.conversorHide').show();
  }
});
/// REMOVE PACOTE INTERNET QND SOMENTE CONVERSOR CADASTRO
$('input[name="optionsRadios"]').change(function () {
  if ($('input[name="optionsRadios"]:checked').val() === "conversorHFC")
  {
    $('input[name="serial"]').removeAttr("required","required");
    $('select[name="pacote"]').removeAttr("required","required");

    $('.camposPacotes').hide();
    $('.conversorHide').hide();

  } else {
    $('input[name="serial"]').attr("required","required");
    $('select[name="pacote"]').attr("required","required");

    $('.camposPacotes').show();
    $('.conversorHide').show();
  }
});


//exibe ou esconde selector de ip fixo
$('select[name="vasProfile"]').change(function(){
  if($(this).val() === "VAS_Internet-CORP-IP" ||
      $(this).val() === "VAS_Internet-VoIP-CORP-IP" ||
      $(this).val() === "VAS_Internet-IPTV-CORP-IP" ||
      $(this).val() === "VAS_Internet-VoIP-IPTV-CORP-IP" ||
      $(this).val() === "VAS_Internet-CORP-IP-Bridge" ||
      $(this).val() === "VAS_Internet-IPTV-CORP-IP-Bridge" ||
      $(this).val() === "VAS_Internet-VoIP-CORP-IP-Bridge" ||
      $(this).val() === "VAS_Internet-VoIP-IPTV-CORP-IP-B")
  {
    $(".ipFixoSelector").show();
  }else{
    $(".ipFixoSelector").hide();
    $("#ipFixo").val('');
  }
});

$('input[name="cgnat_status"]').change(function(){
  if( $(this).val() === 'ip_real_ativo'&&
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
    $('#VAS_Internet-REAL').val("VAS_Internet")
    $('#VAS_Internet-IPTV-REAL').val("VAS_Internet-IPTV")
    $('#VAS_Internet-VoIP-REAL').val("VAS_Internet-VoIP")
    $('#VAS_Internet-VoIP-IPTV-REAL').val("VAS_Internet-VoIP-IPTV")
    //altera ID
    $('#VAS_Internet-REAL').attr("id","VAS_Internet")
    $('#VAS_Internet-IPTV-REAL').attr("id","VAS_Internet-IPTV")
    $('#VAS_Internet-VoIP-REAL').attr("id","VAS_Internet-VoIP")
    $('#VAS_Internet-VoIP-IPTV-REAL').attr("id","VAS_Internet-VoIP-IPTV")
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

    $('#VAS_Internet').val("VAS_Internet-REAL")
    $('#VAS_Internet-IPTV').val("VAS_Internet-IPTV-REAL")
    $('#VAS_Internet-VoIP').val("VAS_Internet-VoIP-REAL")
    $('#VAS_Internet-VoIP-IPTV').val("VAS_Internet-VoIP-IPTV-REAL")
    
    //Altera ID
    $('#VAS_Internet').attr("id","VAS_Internet-REAL")
    $('#VAS_Internet-IPTV').attr("id","VAS_Internet-IPTV-REAL")
    $('#VAS_Internet-VoIP').attr("id","VAS_Internet-VoIP-REAL")
    $('#VAS_Internet-VoIP-IPTV').attr("id","VAS_Internet-VoIP-IPTV-REAL")
  }
});


function alterar()
{
  var body = $("#page-wrapper");

  var pacote = $("select[name='pacote']").val() ;
  var vasProfile = $("select[name='vasProfile']").val();
  var contrato = $("input[name='contrato']").val();
  var serial = $("select[name='serial']").val();
  var equipamento = $("select[name='equipamentos']").val();
  var numeroTel = $("input[name='numeroTelNovo']").val();
  var passwordTel = $("input[name='passwordTelNovo']").val();
  var numeroTelNovo2 = $("input[name='numeroTelNovo2']").val();
  var passwordTelNovo2 = $("input[name='passwordTelNovo2']").val();
  var mac = $("input[name='mac']").val();
  var ipFixo = $("select[name='ipFixo']").val();
  var modo_bridge = $("input[name='modo_bridge_check']:checked").val();
  var cgnat = $("input[name='cgnat_status']:checked").val();
  
  $(document).on({
    ajaxStart: function() {body.addClass("loading")}
  })

  $.post("../classes/alterar.php",{contrato,
                                        serial,
                                        pacote,
                                        vasProfile,
                                        equipamento,
                                        numeroTel,passwordTel,
                                        numeroTelNovo2,passwordTelNovo2,
                                        mac,ipFixo,modo_bridge,cgnat
                                      },
  function(msg)
  {
    bootbox.alert({
      message:msg,
      callback: function(){
        if(msg.includes("recadastrado"))
        {
          window.location.replace('../ont_classes/ont_change.php');
          body.removeClass("loading");
        }else{
          body.removeClass("loading");
        }
      }
    });
  });
}
