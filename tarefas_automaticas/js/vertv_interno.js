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

function verificar_inadimplente_erp()
{
  $.post("../classes/verifica_pendencia_pagamento.php",function(msg){
    if(msg == "concluido")
    {  
      alert(msg);
      window.location.reload();
    }else{
      alert(msg);
    }
  })
}