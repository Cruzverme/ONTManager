<?php 
  set_time_limit(0);
  //Inclui a classe 'class.ezpdf.php'
  include("../vendor/pdf-php/src/Cezpdf.php");

  include_once "../db/db_config_mysql.php";
  

  $pon = filter_input(INPUT_POST,'frame_slot_pon');

  $sql = "SELECT sin.contrato,sin.cto,sin.porta_atendimento,sin.porta_pon,sin.olt,sin.sinal,sin.data_registro
  FROM todos_sinais sin
  WHERE sin.porta_pon='$pon'
  ORDER BY sin.olt,sin.porta_pon ASC";
  
  $executaSQL = mysqli_query($conectar,$sql);

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
  
  list($frame,$slot,$pon) = explode('-',$pon);
  $pdf -> ezText("Lista de Sinais do Slot $slot na Porta $pon da OLT", 20, array(justification => 'center', spacing => 2.0));
  
  $arrayPON = array();
  $arrayInternoSinal = array();
  while($row = mysqli_fetch_array($executaSQL))
  {
    $contrato = $row['contrato'];
    $cto = $row['cto'];
    $porta_atendimento = $row['porta_atendimento'];
    $olt = $row['olt'];
    $sinal = $row['sinal'];
    $data = $row['data_registro'];
    
    $arrayInternoSinal = array('contrato'=>$contrato,'cto'=>$cto,'porta atendimento'=>$porta_atendimento,
      'sinal'=> $sinal,'data'=>$data);
      
    array_push($arrayPON,$arrayInternoSinal);
  }
  
  $pdf -> ezTable($arrayPON,'',"$olt",array('gridlines'=>EZ_GRIDLINE_DEFAULT,'width'=>500));
  $pdf -> ezText('VerTV Comunicações', 10, array(justification => 'right', spacing => 1.0));
  
  //Gera o PDF
  $pdf -> ezStream();
?>