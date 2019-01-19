<?php 
  session_start();

  $sucesso = $_SESSION['sucesso'];
  $falha = array(1,2,3,4,5,6,7,9);//$_SESSION['falha'];

  set_time_limit(0);
  //Inclui a classe 'class.ezpdf.php'
  include("../vendor/pdf-php/src/Cezpdf.php");

  // print_r($sucesso);
  // print_r($falha);

  //Instancia um novo documento com o nome de pdf
  $pdf = new Cezpdf(); 
  
  //Seleciona a fonte que será usada. As fontes estão localizadas na pasta "pdf-php/fonts". Use a de sua preferencia.
  $pdf -> selectFont('../vendor/pdf-php/src/fonts/Helvetica.afm'); 
    
  //Chama o método "ezText".
  //No 1° parametro passa o texto do documento
  //No 2° parametro define o tamanho da fonte.
  //No 3° parametro é do tipo array. A seguir uma explicação desse 3° parametro:
    
  // justification => seta a posição de um label, pode ser center, right, left, aright, ou aleft
  // leading = > define o tamanho que cada linha usará para se mostrada, deverá  ser um int
  // spacing => define o espaçamento entrelinhas, deverá ser um float
  // você pode usar apenas leading ou apenas spacing, nunca os dois
  //$pdf -> ezImage('img/logo.png',0,0,$resize,$just,$angle,'');
  
  $pdf -> ezText("Relatório de Transfêrencia de Pon", 20, array(justification => 'center', spacing => 2.0));
  
  //$pdf -> ezText("T R A N S F E R I D O S", 20, array(justification => 'center', spacing => 2.0));

  foreach($sucesso as $transferido)
  {
    $pdf -> ezText("$transferido", 10, array(justification => 'center', spacing => 1.0));
  }

  //$pdf -> ezText("C O M  P R O B L E M A S",10, array(justification => 'center', spacing => 2.0));

  if(!empty($falha))
  {
    foreach($falha as $failTransfer)
      $pdf -> ezText("$failTransfer",10,array(justification => 'center', spacing => 1.0));

  }else{
    $pdf -> ezText("<p>Sem Falhas de Transferencia!</p>", 10, array(justification => 'left', spacing => 1.0));
  }
  
  $pdf -> ezText('VerTV Comunicações', 10, array(justification => 'right', spacing => 1.0));
  
  //Gera o PDF
  $pdf -> ezStream();

?>