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
  
  $.post("../classes/cadastrar_novo.php",
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
