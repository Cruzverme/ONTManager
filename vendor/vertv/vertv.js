$('input[name="optionsRadios"]').change(function () {
    if ($('input[name="optionsRadios"]:checked').val() === "Sim") {
        $('.camposTelefone').show();
    } else {
        $('.camposTelefone').hide();
    }
});

$("tr.porta").on('click',function() {
    var horario;
     var tableData = $(this).children("td").map(function()         {
     return $(this).text();
     }).get();
     horario = $.trim(tableData[0]);
     window.location.href='../classes/salva_porta_atendimento.php?porta_atendimento_selecionada='+horario;
});

//ACAO AO CLICAR NO BOTAO IMPLEMENTAR FUTURAMENTE
$('.btn-salvar').on('click',function(){
  alert('Salvo');
   $('#modal-texto').modal('hide');
});

