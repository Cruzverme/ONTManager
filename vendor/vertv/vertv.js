$('input[name="optionsRadios"]').change(function () {
    if ($('input[name="optionsRadios"]:checked').val() === "Sim") {
        $('.camposTelefone').show();
    } else {
        $('.camposTelefone').hide();
    }
});