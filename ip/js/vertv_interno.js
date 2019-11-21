var body = $('#page-wrapper');

function cadastrarIp() {

  let ip_inicial = $("input[name='ipInicial']").val();
  let ip_final = $("input[name='ipFinal']").val();

  $(document).on({
    ajaxStart: () => { body.addClass("loading"); },
  })

  $.post('../classes/cadastrar_ip.php',{ip_inicial,ip_final},function(msg){
    body.removeClass("loading");
    alert(msg);
  });

}