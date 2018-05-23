$('input[name="optionsRadios"]').change(function () {
    if ($('input[name="optionsRadios"]:checked').val() === "VAS_Internet-VoIP" || 
    $('input[name="optionsRadios"]:checked').val() === "VAS_Internet-VoIP-IPTV" ) {
        $('.camposTelefone').show();
    } else {
        $('.camposTelefone').hide();
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

