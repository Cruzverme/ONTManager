<?php 

  function tratar_errors($errorCode)
  {
    include_once "../db/db_config_mysql.php";
  
    if (mysqli_connect_errno()) {
      return "Erro ao conectar ao banco de dados: " . mysqli_connect_error();
    }   

    if (empty($errorCode)) {
      return "Código do erro não fornecido.";
    }
    
    $sql = "SELECT description, occurrences_number FROM error_codes WHERE code = ?";
    $stmt = mysqli_prepare($conectar, $sql);

    if ($stmt) {
      mysqli_stmt_bind_param($stmt, "s", $errorCode);
      mysqli_stmt_execute($stmt);
      $result = mysqli_stmt_get_result($stmt);

      if ($result && mysqli_num_rows($result) > 0) {
          $row = mysqli_fetch_assoc($result);

          // Incrementar o número de ocorrências
          $newOccurrences = $row['occurrences_number'] + 1;

          // Atualizar o registro
          $updateSql = "UPDATE error_codes SET occurrences_number = ? WHERE code = ?";
          $updateStmt = mysqli_prepare($conectar, $updateSql);

          if ($updateStmt) {
              mysqli_stmt_bind_param($updateStmt, "is", $newOccurrences, $errorCode);
              mysqli_stmt_execute($updateStmt);
              mysqli_stmt_close($updateStmt);
          }

          mysqli_stmt_close($stmt);
          return $row['description'];
      } 
      mysqli_stmt_close($stmt);
    }


    $insertSql = "INSERT INTO error_codes (code, description, occurrences_number) VALUES (?, 'NULL', 1)";
    $insertStmt = mysqli_prepare($conectar, $insertSql);

    if ($insertStmt) {
      mysqli_stmt_bind_param($insertStmt, "s", $errorCode);
      mysqli_stmt_execute($insertStmt);
      mysqli_stmt_close($insertStmt);
    }
    
    return "OCORREU UM ERRO DESCONHECIDO DE CODIGO $errorCode";

  }

  function logar_tl1()
  {
    include_once "telnet_config.php";
    $fp = fsockopen($servidor, $porta, $errno, $errstr, 30);

    if(!$fp) 
    {
      echo "ERROR: $errno - $errstr<br />\n";
    }else
    {
      $login_command = "LOGIN:::1::UN=$user_tl1,PWD=$psw_tl1; \n\r\n";
      fwrite($fp,$login_command);
      
      $retorno_endesc = explode('ENDESC=',fread($fp,1024));
      
      //CADASTRO DE ONT
      $explo1 = explode('.',$retorno_endesc[1]);

      if($explo1[0] == "Succeeded")
      {
        return "CONECTADO";
        
      }else{
        return "NAO CONECTADO";
      }
      
    }
  }

  function cadastrar_ont($dev,$frame,$slot,$pon,$contrato,$alias,$splitter,$splitterPort,$serial,$equipment,$vasProfile,$tipoNAT=0,$designacao=null)
  {
    include "telnet_config.php";
    $fp = fsockopen($servidor, $porta, $errno, $errstr, 30);

    if(!$fp) 
    {
      echo "ERROR: $errno - $errstr<br />\n";
    }else{     
      $login_command = "LOGIN:::1::UN=$user_tl1,PWD=$psw_tl1; \n\r\n";
      
      // $comando_cadastra_ont = "ADD-ONT::DEV=A1_VERTV-01,FN=0,SN=13,PN=2:1::NAME=CONTRATO,ALIAS=CONTRATO,
      //                        SPLITTER=Splitter(1C2.3),SPLITTERPN=3,LINEPROF=line-profile_11,SRVPROF=srv-profile_10,
      //                        SERIALNUM=48575443909B298B,AUTH=SN,VENDORID=HWTC,EQUIPMENTID=HGW839M,
      //                        MAINSOFTVERSION=V3R016C10S130,VAPROFILE=VAS_Internet-VoIP-IPTV,BUILDTOPO=TRUE; \n\r\n";
      if($vasProfile == "VAS_Internet-CORP-IP" || $vasProfile == "VAS_Internet-VoIP-CORP-IP" || $vasProfile == "VAS_Internet-CORP-IP-Bridge"  || $vasProfile == "VAS_Internet-CORP-IP-Bridge" || $vasProfile == "VAS_Internet-IPTV-CORP-IP-Bridge" 
        || $vasProfile == "VAS_Internet-VoIP-IPTV-CORP-IP" || $vasProfile == "VAS_Internet-VoIP-IPTV-CORP-IP-B" || $vasProfile == "VAS_Internet-VoIP-CORP-IP-Bridge")
        $comando_cadastra_ont = "ADD-ONT::DEV=$dev,FN=$frame,SN=$slot,PN=$pon:1::NAME=$contrato,ALIAS=$alias,LINEPROF=line-profile-corp-ip,SRVPROF=srv-profile-corp-ip,SERIALNUM=$serial,AUTH=SN,VENDORID=HWTC,EQUIPMENTID=$equipment,MAINSOFTVERSION=V3R016C10S130,VAPROFILE=$vasProfile,BUILDTOPO=TRUE;";
      elseif($tipoNAT == 1)
        $comando_cadastra_ont = "ADD-ONT::DEV=$dev,FN=$frame,SN=$slot,PN=$pon:1::NAME=$contrato,ALIAS=$alias,LINEPROF=line-profile_real,SRVPROF=srv-profile_real,SERIALNUM=$serial,AUTH=SN,VENDORID=HWTC,EQUIPMENTID=$equipment,MAINSOFTVERSION=V3R016C10S130,VAPROFILE=$vasProfile,BUILDTOPO=TRUE;";
      elseif($designacao != null)
      {
        $line_designacao = "LINE-$designacao";
        $service_designacao = "SRV-$designacao";
        $comando_cadastra_ont = "ADD-ONT::DEV=$dev,FN=$frame,SN=$slot,PN=$pon:1::NAME=$contrato,ALIAS=$alias,LINEPROF=$line_designacao,SRVPROF=$service_designacao,SERIALNUM=$serial,AUTH=SN,VENDORID=HWTC,EQUIPMENTID=$equipment,MAINSOFTVERSION=V3R016C10S130,VAPROFILE=$vasProfile,BUILDTOPO=TRUE;";
      }
      else
        $comando_cadastra_ont = "ADD-ONT::DEV=$dev,FN=$frame,SN=$slot,PN=$pon:1::NAME=$contrato,ALIAS=$alias,LINEPROF=line-profile_11,SRVPROF=srv-profile_10,SERIALNUM=$serial,AUTH=SN,VENDORID=HWTC,EQUIPMENTID=$equipment,MAINSOFTVERSION=V3R016C10S130,VAPROFILE=$vasProfile,BUILDTOPO=TRUE;"; 
      
      fwrite($fp,$login_command);
      fwrite($fp,$comando_cadastra_ont);

      stream_set_timeout($fp,8);
      while($c = fgetc($fp)!==false)
      {
       $retornoTL1 = fread($fp,2024);
       return $retornoTL1;
      }
      fclose($fp);
    }
  }

  function cadastrar_ont_l2l(
      $dev,
      $frame,
      $slot,
      $pon,
      $contrato,
      $alias,
      $serial,
      $equipment,
      $vasProfile,
      $lineProfile,
      $serviceProfile
  ) {
    include "telnet_config.php";
    $fp = fsockopen($servidor, $porta, $errno, $errstr, 30);

    if(!$fp) {
      echo "ERROR: $errno - $errstr<br />\n";
      return false;
    }
    $login_command = "LOGIN:::1::UN=$user_tl1,PWD=$psw_tl1; \n\r\n";
    $comando_cadastra_ont = "ADD-ONT::DEV=$dev,FN=$frame,SN=$slot,PN=$pon:1::NAME=$contrato,ALIAS=$alias,LINEPROF=$lineProfile,SRVPROF=$serviceProfile,SERIALNUM=$serial,AUTH=SN,VENDORID=HWTC,EQUIPMENTID=$equipment,MAINSOFTVERSION=V3R016C10S130,VAPROFILE=$vasProfile,BUILDTOPO=TRUE;";

    fwrite($fp,$login_command);
    fwrite($fp,$comando_cadastra_ont);

    stream_set_timeout($fp,8);

    while($c = fgetc($fp)!==false)
    {
      $retornoTL1 = fread($fp,2024);
      return $retornoTL1;
    }
    fclose($fp);
    return false;
  }

  function ativa_telefonia($dev,$frame,$slot,$pon,$ontID,$userNameSIP,$userPSWSip,$sipNameNumber,$userNameSIP2 = null,$userPSWSip2 = null,$sipNameNumber2 = null)
  {
    include "telnet_config.php";
    $fp = fsockopen($servidor, $porta, $errno, $errstr, 30);

    if(!$fp)
    {
      echo "ERROR: $errno - $errstr<br />\n";
    }else{     
      $login_command = "LOGIN:::1::UN=$user_tl1,PWD=$psw_tl1; \n\r\n";
      //echo "$userNameSIP2 e $userPSWSip2 <br>";
      //CFG-ONTVAINDIV::DEV=A1_VERTV-01,FN=0,SN=13,PN=1,ONTID=0,SIPUSERNAME_1=2202300000,
      //SIPUSERPWD_1=123456,SIPNAME_1=2202300000:1::;
      if($userNameSIP2 == NULL || $userPSWSip2 == NULL || $sipNameNumber2 == NULL)
      {
        $comando_cadastra_sip = "CFG-ONTVAINDIV::DEV=$dev,FN=$frame,SN=$slot,PN=$pon,ONTID=$ontID,SIPUSERNAME_1=$userNameSIP,SIPUSERPWD_1=$userPSWSip,SIPNAME_1=$sipNameNumber:1::;";
      }else{
        $comando_cadastra_sip = "CFG-ONTVAINDIV::DEV=$dev,FN=$frame,SN=$slot,PN=$pon,ONTID=$ontID,SIPUSERNAME_1=$userNameSIP,SIPUSERPWD_1=$userPSWSip,SIPNAME_1=$sipNameNumber,SIPUSERNAME_2=$userNameSIP2,SIPUSERPWD_2=$userPSWSip2,SIPNAME_2=$sipNameNumber2:1::;";
        //echo "<br> SOU TEL : $userNameSIP2, $userPSWSip2 <br>";
        echo $comando_cadastra_sip;
      }
      fwrite($fp,$login_command);
      fwrite($fp,$comando_cadastra_sip);
      
      stream_set_timeout($fp,8);
      while($c = fgetc($fp)!==false)
      {
       $retornoTL1 = fread($fp,2024);
       return $retornoTL1;
      }  
      fclose($fp);
    }
  }


  function deletar_onu_2000($dev,$frame,$slot,$pon,$ontID,$ip,$servPortIPTV)
  {
    include "telnet_config.php";
    include "../db/db_config_mysql.php";
    $fp = fsockopen($servidor, $porta, $errno, $errstr, 30);

    if(!$fp) 
    {
      return "ERROR: $errno - $errstr<br />\n";
    }else
    {
      // $tl1_reset = reset_fabric_ont($dev,$frame,$slot,$pon,$ontID);
      // $tira_ponto_virgula = explode(";",$tl1_reset);
      // $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
      // $remove_desc = explode("ENDESC=",$check_sucesso[1]);
      // $errorCode = trim($remove_desc[0]);
      // if($errorCode != "0")
      // {
      //   return $errorCode;
      // }else
      // {
        if($servPortIPTV != NULL)
        {
          deleta_btv_iptv($dev,$frame,$slot,$pon,$ontID);
        }
        
        $login_command = "LOGIN:::1::UN=$user_tl1,PWD=$psw_tl1; \n\r\n";
        
        $comando_deletar = "DEL-ONT::DEV=$dev,FN=$frame,SN=$slot,PN=$pon,ONTID=$ontID,DELCONFIG=TRUE:1::;";

        fwrite($fp,$login_command);
        fwrite($fp,$comando_deletar);

        //$retornoTL1="";
        stream_set_timeout($fp,8);
        while($c = fgetc($fp)!==false)
        {
          $retornoTL1 = fread($fp,2024);
          return $retornoTL1;
        }
      //}
    }
    fclose($fp);
  }

  function get_service_port_internet($dev,$frame,$slot,$pon,$ontID,$contrato,$vasProfile,$modo, $tipoNAT = 0, $customVlan = null)
  {
    include "telnet_config.php";
    $fp = fsockopen($servidor, $porta, $errno, $errstr, 30);

    if(!$fp) 
    {
      echo "ERROR: $errno - $errstr<br />\n";
    }else{     
      $login_command = "LOGIN:::1::UN=$user_tl1,PWD=$psw_tl1; \n\r\n";

      $comando = "CRT-SERVICEPORT::DEV=$dev,FN=$frame,SN=$slot,PN=$pon:3::VLANID=2500,SVPID=INTERNET-$contrato,ONTID=$ontID,GEMPORTID=6,UV=2500,RETURID=TRUE;";

      if($vasProfile == "VAS_Internet-CORP-IP" || $vasProfile == "VAS_Internet-VoIP-CORP-IP" || $modo == 'mac_externo'
        || $vasProfile == "VAS_Internet-VoIP-IPTV-CORP-IP" || $vasProfile == "VAS_Internet-VoIP-IPTV-CORP-IP-B")
        $comando = "CRT-SERVICEPORT::DEV=$dev,FN=$frame,SN=$slot,PN=$pon:3::VLANID=2503,SVPID=INTERNET-$contrato,ONTID=$ontID,GEMPORTID=6,UV=2503,RETURID=TRUE;";
      elseif($tipoNAT == 1)
        $comando = "CRT-SERVICEPORT::DEV=$dev,FN=$frame,SN=$slot,PN=$pon:3::VLANID=2504,SVPID=INTERNET-$contrato,ONTID=$ontID,GEMPORTID=6,UV=2504,RETURID=TRUE;";
      elseif ($customVlan) {
        $comando = "CRT-SERVICEPORT::DEV=$dev,FN=$frame,SN=$slot,PN=$pon:3::VLANID=$customVlan,SVPID=INTERNET-$contrato,ONTID=$ontID,GEMPORTID=6,UV=$customVlan,RETURID=TRUE;";
      }

        
      fwrite($fp,$login_command);
      fwrite($fp,$comando);

      stream_set_timeout($fp,8);
      while($c = fgetc($fp)!==false)
      {
       $retornoTL1 = fread($fp,2024);
       return $retornoTL1;
      }  
      fclose($fp);
    }
  }

  function get_service_port_l2l($dev,$frame,$slot,$pon,$ontID,$contrato,$vlanltwol, $gemport)
  {
    include "telnet_config.php";
    $fp = fsockopen($servidor, $porta, $errno, $errstr, 30);

    if(!$fp) 
    {
      echo "ERROR: $errno - $errstr<br />\n";
    }else{
      $login_command = "LOGIN:::1::UN=$user_tl1,PWD=$psw_tl1; \n\r\n";
    
      $comando = "CRT-SERVICEPORT::DEV=$dev,FN=$frame,SN=$slot,PN=$pon:3::VLANID=$vlanltwol,SVPID=LAN_TO_LAN-$contrato-$vlanltwol,ONTID=$ontID,GEMPORTID=$gemport,UV=$vlanltwol,RETURID=TRUE;";

      fwrite($fp,$login_command);
      fwrite($fp,$comando);

      stream_set_timeout($fp,8);
      while($c = fgetc($fp)!==false)
      {
        $retornoTL1 = fread($fp,2024);
        return $retornoTL1;
      }
      fclose($fp);
    }
  }

  function get_service_port_iptv($dev,$frame,$slot,$pon,$ontID,$contrato)
  {
    include "telnet_config.php";
    $fp = fsockopen($servidor, $porta, $errno, $errstr, 30);

    if(!$fp) 
    {
      echo "ERROR: $errno - $errstr<br />\n";
    }else{     
      $login_command = "LOGIN:::1::UN=$user_tl1,PWD=$psw_tl1; \n\r\n";
    
      $comando = "CRT-SERVICEPORT::DEV=$dev,FN=$frame,SN=$slot,PN=$pon:3::VLANID=2502,SVPID=IPTV-$contrato,ONTID=$ontID,GEMPORTID=8,UV=2502,RETURID=TRUE;";
      
      fwrite($fp,$login_command);
      fwrite($fp,$comando);

      stream_set_timeout($fp,8);
      while($c = fgetc($fp)!==false)
      {
       $retornoTL1 = fread($fp,2024);
       return $retornoTL1;
      }  
      fclose($fp);
    }
  }

  function get_service_port_telefone($dev,$frame,$slot,$pon,$ontID,$contrato)
  {
    include "telnet_config.php";
    $fp = fsockopen($servidor, $porta, $errno, $errstr, 30);

    if(!$fp) 
    {
      echo "ERROR: $errno - $errstr<br />\n";
    }else{     
      $login_command = "LOGIN:::1::UN=$user_tl1,PWD=$psw_tl1; \n\r\n";
    
      $comando = "CRT-SERVICEPORT::DEV=$dev,FN=$frame,SN=$slot,PN=$pon:3::VLANID=2501,SVPID=TELEFONE-$contrato,ONTID=$ontID,GEMPORTID=7,UV=2501,RETURID=TRUE;";

      fwrite($fp,$login_command);
      fwrite($fp,$comando);

      stream_set_timeout($fp,8);
      while($c = fgetc($fp)!==false)
      {
       $retornoTL1 = fread($fp,2024);
       return $retornoTL1;
      }  
      fclose($fp);
    }
  }

  function insere_btv_iptv($deviceName,$frame,$slot,$pon,$ontID)
  {
    include "telnet_config.php";
    $fp = fsockopen($servidor, $porta, $errno, $errstr, 30);
    
    if(!$fp) 
    {
      echo "ERROR: $errno - $errstr<br />\n";
    }else
    {
      $login_command = "LOGIN:::1::UN=$user_tl1,PWD=$psw_tl1; \n\r\n";
      $comando_insere_btv = "JOIN-NTVUSR::DEV=$deviceName,FN=$frame,SN=$slot,PN=$pon,ONTID=$ontID,GEMPORTID=8:15::AUTH=2,IGMPVLAN=2502; \n\r\n";
      fwrite($fp,$login_command);
      fwrite($fp,$comando_insere_btv);
      stream_set_timeout($fp,8);
      while($c = fgetc($fp)!==false)
      {
        $retornoTL1 = fread($fp,2024);
        return $retornoTL1;
      }
      fclose($fp);
    }
  }
  
  function deleta_btv_iptv($deviceName,$frame,$slot,$pon,$ontID)
  {
    include "telnet_config.php";
    $fp = fsockopen($servidor, $porta, $errno, $errstr, 30);
    
    if(!$fp) 
    {
      echo "ERROR: $errno - $errstr<br />\n";
    }else
    {
      $login_command = "LOGIN:::1::UN=$user_tl1,PWD=$psw_tl1; \n\r\n";
      $comando_deleta_btv = "QUIT-NTV::DEV=$deviceName,FN=$frame,SN=$slot,PN=$pon,ONTID=$ontID,GEMPORTID=8:16::; \n\r\n";
      fwrite($fp,$login_command);
      fwrite($fp,$comando_deleta_btv);

      //$retornoTL1="";
      stream_set_timeout($fp,8);
      while($c = fgetc($fp)!==false)
      {
        $retornoTL1 = fread($fp,2024);
        return $retornoTL1;
      }
    }  
  }

  function desabilita_inadimplente($dev,$frame,$slot,$pon,$ontID)
  {
    include "telnet_config.php";
    $fp = fsockopen($servidor, $porta, $errno, $errstr, 30);

    if(!$fp) 
    {
      echo "ERROR: $errno - $errstr<br />\n";
    }else{
      $login_command = "LOGIN:::1::UN=$user_tl1,PWD=$psw_tl1; \n\r\n";
    
      $comando_desabilita = "DACT-ONT::DEV=$dev,FN=$frame,SN=$slot,PN=$pon,ONTID=$ontID:1::; \n\r\n";
        
      fwrite($fp,$login_command);
      fwrite($fp,$comando_desabilita);

      stream_set_timeout($fp,8);
      while($c = fgetc($fp)!==false)
      {
       $retornoTL1 = fread($fp,2024);
       return $retornoTL1;
      }
      fclose($fp);
    }
  }

  function ativa_inadimplente($dev,$frame,$slot,$pon,$ontID)
  {
    include "telnet_config.php";
    $fp = fsockopen($servidor, $porta, $errno, $errstr, 30);

    if(!$fp) 
    {
      echo "ERROR: $errno - $errstr<br />\n";
    }else{
      $login_command = "LOGIN:::1::UN=$user_tl1,PWD=$psw_tl1; \n\r\n";
    
      $comando_ativa = "ACT-ONT::DEV=$dev,FN=$frame,SN=$slot,PN=$pon,ONTID=$ontID:1::; \n\r\n";
        
      fwrite($fp,$login_command);
      fwrite($fp,$comando_ativa);

      stream_set_timeout($fp,8);
      while($c = fgetc($fp)!==false)
      {
       $retornoTL1 = fread($fp,2024);
       return $retornoTL1;
      }
      fclose($fp);
    } 
  }

  function reset_fabric_ont($dev,$frame,$slot,$pon,$ontID)
  {
    include "telnet_config.php";
    $fp = fsockopen($servidor, $porta, $errno, $errstr, 30);

    if(!$fp) 
    {
      echo "ERROR: $errno - $errstr<br />\n";
    }else{
      $login_command = "LOGIN:::1::UN=$user_tl1,PWD=$psw_tl1; \n\r\n";
    
      $comando_reset = "RST-ONT::DEV=$dev,FN=$frame,SN=$slot,PN=$pon,ONTID=$ontID:CTAG::;";

      fwrite($fp,$login_command);
      fwrite($fp,$comando_reset);

      stream_set_timeout($fp,8);
      while($c = fgetc($fp)!==false)
      {
        $retornoTL1 = fread($fp,2024);
        return $retornoTL1;
      }
      fclose($fp);
    }
  }

  function modificar_pon_ont($dev,$frame,$slot,$pon,$ontID,$serial)//NAO UTILIZADO
  {
    include "telnet_config.php";
    $fp = fsockopen($servidor, $porta, $errno, $errstr, 30);

    if(!$fp) 
    {
      echo "ERROR: $errno - $errstr<br />\n";
    }else{

      $tl1_reset = reset_fabric_ont($dev,$frame,$slot,$pon,$ontID);
      $tira_ponto_virgula = explode(";",$tl1_reset);
      $check_sucesso = explode("EN=",$tira_ponto_virgula[1]);
      $remove_desc = explode("ENDESC=",$check_sucesso[1]);
      $errorCode = trim($remove_desc[0]);
      if($errorCode != "0" && $errorCode != 2689014724)
      {
        return $errorCode;
      }else{
        $login_command = "LOGIN:::1::UN=$user_tl1,PWD=$psw_tl1; \n\r\n";
        
        $comando_troca_ont = "REPLACE-ONT::DEV=$dev,FN=$frame,SN=$slot,PN=$pon,ONTID=$ontID:1::SERIALNUM=$serial,DCUPDATE=TRUE,NEEDPOLL=TRUE;";
          
        fwrite($fp,$login_command);
        fwrite($fp,$comando_troca_ont);

        stream_set_timeout($fp,8);
        while($c = fgetc($fp)!==false)
        {
          $retornoTL1 = fread($fp,2024);
          return $retornoTL1;
        }
      }
      fclose($fp);
    }
  }

  function get_status_ont($dev,$frame,$slot,$pon,$ontID)
  {
    include "telnet_config.php";
    $fp = fsockopen($servidor, $porta, $errno, $errstr, 30);

    if(!$fp) 
    {
      echo "ERROR: $errno - $errstr<br />\n";
    }else
    {
      $login_command = "LOGIN:::1::UN=$user_tl1,PWD=$psw_tl1; \n\r\n";
      
      $comando = "LST-ONTRUNINFO::DEV=$dev,FN=$frame,SN=$slot,PN=$pon,ONTID=$ontID:1::;";

      fwrite($fp,$login_command);
      fwrite($fp,$comando);

      stream_set_timeout($fp,8);
      while($c = fgetc($fp)!==false)
      {
        $retornoTL1 = fread($fp,2024);
        return $retornoTL1;
      }
      fclose($fp);
    } 
  }

  function get_status_sip($dev,$frame,$slot,$pon,$ontID)
  {
    
    include "telnet_config.php";
    $fp = fsockopen($servidor, $porta, $errno, $errstr, 30);

    if(!$fp) 
    {
      echo "ERROR: $errno - $errstr<br />\n";
    }else
    {
      $login_command = "LOGIN:::1::UN=$user_tl1,PWD=$psw_tl1; \n\r\n";
      
      $comando = "LST-ONTPOTSSTATE::DEV=$dev,FN=$frame,SN=$slot,PN=$pon,ONTID=$ontID:1::;";

      fwrite($fp,$login_command);
      fwrite($fp,$comando);

      stream_set_timeout($fp,8);
      while($c = fgetc($fp)!==false)
      {
        $retornoTL1 = fread($fp,2024);
        return $retornoTL1;
      }
      fclose($fp);
    }
  }

  function get_signal_ont($dev,$frame,$slot,$pon,$ontID)
  {
    
    include "telnet_config.php";
    $fp = fsockopen($servidor, $porta, $errno, $errstr, 30);

    if(!$fp) 
    {
      echo "ERROR: $errno - $errstr<br />\n";
    }else
    {
      $login_command = "LOGIN:::1::UN=$user_tl1,PWD=$psw_tl1; \n\r\n";
      
      $comando = "LST-ONTDDMDETAIL::DEV=$dev,FN=$frame,SN=$slot,PN=$pon,ONTID=$ontID:CTAG::SHOWOPTION=OPTICSRXPOWERbyOLT;";

      fwrite($fp,$login_command);
      fwrite($fp,$comando);

      stream_set_timeout($fp,8);
      while($c = fgetc($fp)!==false)
      {
        $retornoTL1 = fread($fp,2024);
        return $retornoTL1;
      }
      fclose($fp);
    }
  }

  function reseta_ont($deviceName,$frame,$slot,$pon,$ontID)
  {
    include "telnet_config.php";
    $fp = fsockopen($servidor, $porta, $errno, $errstr, 30);

    if(!$fp) 
    {
      echo "ERROR: $errno - $errstr<br />\n";
    }else
    {
      $login_command = "LOGIN:::1::UN=$user_tl1,PWD=$psw_tl1; \n\r\n";
      $comando = "RESET-ONT::DEV=$deviceName,FN=$frame,SN=$slot,PN=$pon,ONTID=$ontID:1::;";

      fwrite($fp,$login_command);
      fwrite($fp,$comando);

      stream_set_timeout($fp,15);
      while($c = fgetc($fp)!==false)
      {
        $retornoTL1 = fread($fp,2024);
        return $retornoTL1;
      }
      fclose($fp);
    }
  }

  function verificar_wan($deviceName,$frame,$slot,$pon,$ontID)
  {
    include "telnet_config.php";
    $fp = fsockopen($servidor, $porta, $errno, $errstr, 30);

    if(!$fp) 
    {
      echo "ERROR: $errno - $errstr<br />\n";
    }else
    {
      $login_command = "LOGIN:::1::UN=$user_tl1,PWD=$psw_tl1; \n\r\n";
      $comando = "LST-ONTWAN::DEV=$deviceName,FN=$frame,SN=$slot,PN=$pon,ONTID=$ontID:1::SHOWOPTION=WANNAME WANIPADDR WANSUBMASK WANVLAN WANGW ADDTYPE;";

      fwrite($fp,$login_command);
      fwrite($fp,$comando);

      stream_set_timeout($fp,12);
      while($c = fgetc($fp)!==false)
      {
        $retornoTL1 = fread($fp,2024);
        return $retornoTL1;
      }
      fclose($fp);
    }
  }

  function verificar_portas_ont($deviceName,$frame,$slot,$pon,$ontID,$numero_porta)
  {
    include "telnet_config.php";
    $fp = fsockopen($servidor, $porta, $errno, $errstr, 30);

    if(!$fp) 
    {
      echo "ERROR: $errno - $errstr<br />\n";
    }else
    {
      $login_command = "LOGIN:::1::UN=$user_tl1,PWD=$psw_tl1; \n\r\n";
      $comando = "LST-ONTPORTDETAIL::DEV=$deviceName,FN=$frame,SN=$slot,PN=$pon,ONTID=$ontID,ONTPORTTYPE=ETH,ONTPORTID=$numero_porta:1::;";

      fwrite($fp,$login_command);
      fwrite($fp,$comando);

      stream_set_timeout($fp,8);
      while($c = fgetc($fp)!==false)
      {
        $retornoTL1 = fread($fp,2024);
        return $retornoTL1;
      }
      fclose($fp);
    }
  }
  
  function verificar_service_port($deviceName,$frame,$slot,$pon,$ontID)
  {
    include "telnet_config.php";
    $fp = fsockopen($servidor, $porta, $errno, $errstr, 30);

    if(!$fp) 
    {
      echo "ERROR: $errno - $errstr<br />\n";
    }else
    {
      $login_command = "LOGIN:::1::UN=$user_tl1,PWD=$psw_tl1; \n\r\n";
      $comando = "LST-SERVICEPORT::DEV=$deviceName,FN=$frame,SN=$slot,PN=$pon:1::ONTID=$ontID;";

      fwrite($fp,$login_command);
      fwrite($fp,$comando);

      stream_set_timeout($fp,8);
      while($c = fgetc($fp)!==false)
      {
        $retornoTL1 = fread($fp,2024);
        return $retornoTL1;
      }
      fclose($fp);
    }
  }

  function criar_vlan($deviceName,$frame,$slot,$pon,$vlanID,$alias)
  {
    include "telnet_config.php";
    $fp = fsockopen($servidor, $porta, $errno, $errstr, 30);

    if(!$fp) 
    {
      echo "ERROR: $errno - $errstr<br />\n";
    }else
    {
      $login_command = "LOGIN:::1::UN=$user_tl1,PWD=$psw_tl1; \n\r\n";
      $add_vlan = "ADD-VLAN::DEV=$deviceName:1::VLANID=$vlanID,VLANALIAS=$alias,VLANTYPE=SMART;";
      $association_vlan_pon = "ASS-ETHPORTANDVLAN::DEV=$deviceName,FN=0,SN=18,PN=0:1::VLANID=$vlanID;";

      fwrite($fp,$login_command);
      fwrite($fp,$add_vlan);
      sleep(4);
      fwrite($fp,$association_vlan_pon);

      stream_set_timeout($fp,8);
      while($c = fgetc($fp)!==false)
      {
        $retornoTL1 = fread($fp,2024);
        return $retornoTL1;
      }
      fclose($fp);
    }
  }

  function vlan_external_association($deviceName,$vlanID)
  {
    include "telnet_config.php";
    $fp = fsockopen($servidor, $porta, $errno, $errstr, 30);

    if(!$fp) 
    {
      echo "ERROR: $errno - $errstr<br />\n";
    }else
    {
      $login_command = "LOGIN:::1::UN=$user_tl1,PWD=$psw_tl1; \n\r\n";
      $association_vlan_pon = "ASS-ETHPORTANDVLAN::DEV=$deviceName,FN=0,SN=18,PN=0:1::VLANID=$vlanID;";

      fwrite($fp,$login_command);
      fwrite($fp,$association_vlan_pon);

      stream_set_timeout($fp,8);
      while($c = fgetc($fp)!==false)
      {
        $retornoTL1 = fread($fp,2024);
        return $retornoTL1;
      }
      fclose($fp);
    }
  }

  function deleta_vlan($deviceName,$frame,$slot,$pon,$vlanID)
  {
    include "telnet_config.php";
    $fp = fsockopen($servidor, $porta, $errno, $errstr, 30);
    
    if(!$fp) 
    {
      echo "ERROR: $errno - $errstr<br />\n";
    }else
    {
      $login_command = "LOGIN:::1::UN=$user_tl1,PWD=$psw_tl1; \n\r\n";
      $dessasociation_pon_vlan = "DASS-ETHPORTANDVLAN::DEV=$deviceName,FN=$frame,SN=$slot,PN=$pon:1::VLANID=$vlanID;";
      $remove_vlan = "DEL-VLAN::DEV=$deviceName:1::VLANID=$vlanID;";

      fwrite($fp,$login_command);
      fwrite($fp,$dessasociation_pon_vlan);
      sleep(2);
      fwrite($fp,$remove_vlan);

      //$retornoTL1="";
      stream_set_timeout($fp,8);
      while($c = fgetc($fp)!==false)
      {
        $retornoTL1 = fread($fp,2024);
        return $retornoTL1;
      }
    }  
  }
?>
