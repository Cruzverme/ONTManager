$('input[name="internet_checked"]').change(function () {
  if ($('input[name="internet_checked"]:checked').val() === "Internet")
  {
    $('select[name="pacote"]').attr("required","required");
    $('.camposPacotes').show();
  }else{
    $('select[name="pacote"]').removeAttr("required","required");
    $('.camposPacotes').hide();
  }
});


$('input[name="telefone_checked"]').change(function () {
  if ($('input[name="telefone_checked"]:checked').val() === "Telefone")
  {
    $('input[name="numeroTel"]').attr("required", "required");
    $('input[name="passwordTel"]').attr("required", "required");
    $('.camposTelefone').show();
  }else{
    $('input[name="numeroTel"]').removeAttr("required");
    $('input[name="passwordTel"]').removeAttr("required");
    $('.camposTelefone').hide();
  }
});

function cadastrar_corporativo(){
  
  var body = $('#page-wrapper');

  $(document).on({
    ajaxStart: function() {body.addClass("loading");}
  });

  var nome = $("input[name='nome']").val(),
  vasProfile = $("input[name='vasProfile']").val(),
  serial= $("input[name='serial']").val(),
  pacote_internet= $("select[name='pacote']").val(),
  modelo_ont= $("select[name='equipamentos']").val(),
  sip_number= $("input[name='numeroTel']").val(),
  sip_password= $("input[name='passwordTel']").val(),
  porta_atendimento= $("input[name='porta_atendimento']").val(),
  frame= $("input[name='frame']").val(),
  slot= $("input[name='slot']").val(),
  pon= $("input[name='pon']").val(),
  cto= $("input[name='caixa_atendimento_select']").val(),
  device= $("input[name='deviceName']").val(),
  contrato= $("input[name='contrato']").val(),
  designacao= $("input[name='designacao']").val(),
  vlan_number= $("input[name='vlan_number']").val(),
  internet_check = $("input[name='internet_checked']:checked").val(),
  vlan_check = $("input[name='l_to_l']:checked").val(),
  iptv = $("input[name='iptv_checked']:checked").val(),
  voip = $("input[name='telefone_checked']:checked").val();

  $.post('../classes/cadastrar_corporativo.php',
  {nome,vasProfile,serial,pacote_internet,
    modelo_ont,sip_number,sip_password,porta_atendimento,frame,slot,pon,cto,device: device,contrato,
    designacao,vlan_number,internet_check,vlan_check,iptv,voip},function(msg_retorno)
    {
      alert(msg_retorno)
      if(msg_retorno)
        body.removeClass("loading");
  });
}