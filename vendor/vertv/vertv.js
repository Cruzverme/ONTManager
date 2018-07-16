$('input[name="optionsRadios"]').change(function () {
    if ($('input[name="optionsRadios"]:checked').val() === "VAS_Internet-VoIP" || 
    $('input[name="optionsRadios"]:checked').val() === "VAS_Internet-VoIP-IPTV" ) {
        $('.camposTelefone').show();
    } else {
        $('.camposTelefone').hide();
    }
});

$('input[name="optionsRadios"]').change(function () {
    if ($('input[name="optionsRadios"]:checked').val() === "VAS_IPTV")
    {
        $('.camposPacotes').hide();
    } else {
        $('.camposPacotes').show();
    }
});

$('input[name="nivel"]').change(function () {
    if ($('input[name="nivel"]:checked').val() === "1" ) {
        $('.camposPermissao').hide();
    } else {
        $('.camposPermissao').show();
    }
});
$('input[name="optionsRadiosConsulta"]').change(function () {
    if ($('input[name="optionsRadiosConsulta"]:checked').val() === "cto" )
    {
        $('.campoCto').show();
    } else {
        $('.campoCto').hide();
    }
});

$("tr.porta").on('click',function() {
    var porta_selecionada;
    var serial;
    var caixa;
    var tableData = $(this).children("td").map(function()         {
    return $(this).text();
    }).get();

    caixa = $.trim(tableData[2]);
    serial = $.trim(tableData[1]);
    porta_selecionada = $.trim(tableData[0]);
    window.location.href='../classes/salva_porta_atendimento.php?porta_atendimento_selecionada='+porta_selecionada+
        '&caixa_atendimento='+caixa +'&serial='+serial;
});

//ACAO AO CLICAR NO BOTAO IMPLEMENTAR FUTURAMENTE
$('.btn-salvar').on('click',function(){
  alert('Salvo');
   $('#modal-texto').modal('hide');
});

 $('tr.usuarios').click(function() {
     window.location.href = $(this).attr('data-href');
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


//VALIDAR SEHA
if(document.getElementById("senha") != null || document.getElementById("confirma_senha") != null )
{
  var password = document.getElementById("senha"), confirm_password = document.getElementById("confirma_senha");
  password.onchange = validatePassword(password);
  confirm_password.onkeyup = validatePassword;

  function validatePassword(){
    if(password.value != confirm_password.value) {
      confirm_password.setCustomValidity("Senhas Não Conferem");
    } else {
      confirm_password.setCustomValidity('');
    }
  } 
}
//FIM VALIDAR