function bloquear(contrato){
  alert(contrato + " Bloqueado")
  $.post("../classes/gerencia_bloqueios.php",{},function(msg){

  });

}

function desbloquear(contrato){
  alert(contrato + " desloqueado")
  $.post("../classes/gerencia_bloqueios.php",{},function(msg){

  });
}