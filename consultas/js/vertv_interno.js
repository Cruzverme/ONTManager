function bloquear(contrato,serial){
  $.post("../classes/gerencia_bloqueios.php",{motivo: 2,contrato,serial},function(msg){
    if(msg == "Cliente desativado")
    {
      alert(msg);
      window.location.reload();
    }else{
      alert(msg);
    }
  });
}

function desbloquear(contrato,serial){
  $.post("../classes/gerencia_bloqueios.php",{motivo: 1,contrato,serial},function(msg){
    if(msg == "Cliente reativado")
    {
      alert(msg);
      window.location.reload();
    }else{
      alert(msg);
    }
  });
}

function cancelar(contrato,serial)
{
  alert("cancela nao cara!" + contrato +" com "+ serial);
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
      alert(msg)
      window.location.reload();
    }else{
      body.removeClass("loading");
      alert(msg);
      window.location.reload();
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
      alert(msg)
      window.location.reload();
    }else{
      body.removeClass("loading");
      alert(msg);
      window.location.reload();
    }
  })
}
