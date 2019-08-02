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

$(window).on('load',function () {
  $(".loader").fadeOut("slow"); //retire o delay quando for copiar!
  $("#tudo_page").toggle("fast");
});