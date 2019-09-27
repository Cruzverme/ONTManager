//// CONSULTA ONT
($("#contrato")).keypress(function(e) {
  if(e.which == 13)  consultar();
});

$("#mac_pon").keypress(function(e) {
  if(e.which == 13)  consultar();
});

function levanta()
{
  var icone = $(".informacoes_legend").find("i.fa");
  
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
}


function consultar(){
  var body = $("#page-wrapper");

  $(document).on({
     ajaxStart: function() {body.addClass("loading")}
  })

  var contrato = $("#contrato").val();
  var mac = $("#mac_pon").val();

  if(mac != '' && mac.length < 16)
  {
    bootbox.alert("<p style='text-align:center'>MAC deve ter no mínimo 16 caracteres!</p>");
  }

  if(contrato == '' && mac == '')
  { 
    body.removeClass("loading");
    bootbox.alert({
      message: "<center>Insira o MAC ou o Contrato!</center>",
      size:"small"
    })
  }
  else{
    $.post("_show_status.php",{mac,contrato},function(msg)
    {
      body.removeClass("loading");
      $("#show_status").empty();

      $("#show_status").append(msg);
    });
  }
}

function wait(ms){
  var start = new Date().getTime();
  var end = start;
  while(end < start + ms) {
    end = new Date().getTime();
  }
}

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
            size: 'small',
            callback: function(){
              wait(5000);
              console.log('perei 5 sec');
              consultar();
            }
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
