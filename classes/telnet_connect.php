<?php
require_once "PHPTelnet.php";
$telnet = new PHPTelnet();
$result = $telnet->Connect('','','');



// $telnet->DoCommand("10.126.150.97", $result);
// $telnet->DoCommand("SYSTEM", $result);
// $telnet->DoCommand("SYSTEM", $result);
// $telnet->DoCommand("ZNBI:::550310000000:;", $result);
echo $result;
// $telnet->DoCommand("ZZZZZZZZ;", $result);
$telnet->Disconnect();
?>