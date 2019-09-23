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
