//// CONSULTA ONT
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
  $(".hider_infos").toggle();
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


///// FIM CONSULTA ONT

//// CONSULTA BLOQUEADO E CANCELADOS E DESBLOQUEADOS
function bloquear(contrato,serial){
  $.post("../classes/gerencia_bloqueios.php",{motivo: 2,contrato,serial},function(msg){
    if(msg == "Cliente desativado")
    {
      bootbox.alert({
        size:"small",
        message: msg,
        callback: function(){ 
            window.location.reload();
        }
      });
    }else{
      bootbox.alert({
        size:"small",
        message: msg
      });
    }
  });
}

function desbloquear(contrato,serial){
  $.post("../classes/gerencia_bloqueios.php",{motivo: 1,contrato,serial},function(msg){
    if(msg == "Cliente reativado")
    {
      bootbox.alert({
        size:"small",
        message: msg,
        callback: function(){ 
          window.location.reload();
        }
      });
    }else{
      bootbox.alert({
        size:"small",
        message: msg
      });
    }
  });
}

function cancelar(contrato,serial)
{
  bootbox.confirm({
    size: "small",
    message: "Tem Certeza de Cancelar?",
    callback: function(result){
      if(result)
      {
        $.post("../classes/gerencia_cancelamento.php",{contrato,serial},function(msg){
          if(msg != "")
          {
            bootbox.alert({
              size:"small",
              message: msg,
              callback: function(){ 
                  window.location.reload();
              }
            });
          }
        });
      }
      else{
        bootbox.alert({size:"small",message:"Obrigado por me salvar!"});
      }
    }
  });
}

function verificar_inadimplente_erp()
{
  var body = $('#page-wrapper');

  $(document).on({
    ajaxStart: function() {body.addClass("loading");}
  });

  $.post("../classes/verifica_pendencia_pagamento.php",function(msg){
    if(msg == "concluido")
    {  
      body.removeClass("loading");
      bootbox.alert({
        size:"small",
        message: msg,
        callback: function(){ 
            window.location.reload();
        }
      });
    }else{
      body.removeClass("loading");
      bootbox.alert({
        size:"small",
        message: msg,
        callback: function(){ 
            window.location.reload();
        }
      });
    }
  })
}

function verificar_cancelados_erp()
{
  var body = $('#page-wrapper');

  $(document).on({
    ajaxStart: function() {body.addClass("loading");}
  });

  $.post("../classes/verifica_pendencia_cancelamento.php",function(msg){
    if(msg == "concluido")
    {  
      body.removeClass("loading");
      bootbox.alert({
        size:"small",
        message: msg,
        callback: function(){ 
            window.location.reload();
        }
      });
    }else{
      body.removeClass("loading");
      bootbox.alert({
        size:"small",
        message: msg,
        callback: function(){ 
            window.location.reload();
        }
      });
    }
  })
}
