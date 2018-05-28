<?php 

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

  function cadastrar_ont($dev,$frame,$slot,$pon,$contrato,$splitter,$splitterPort,$serial,$equipment,$vasProfile)
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

      $comando_cadastra_ont = "ADD-ONT::DEV=$dev,FN=$frame,SN=$slot,PN=$pon:1::
NAME=$contrato,ALIAS=$contrato,LINEPROF=line-profile_11,SRVPROF=srv-profile_10,
SERIALNUM=$serial,AUTH=SN,VENDORID=HWTC,EQUIPMENTID=$equipment,MAINSOFTVERSION=V3R016C10S130,VAPROFILE=$vasProfile,BUILDTOPO=TRUE;";

      fwrite($fp,$login_command);
      fwrite($fp,$comando_cadastra_ont);

      stream_set_timeout($fp,5);
      while($c = fgetc($fp)!==false)
      {
       $retornoTL1 = fread($fp,2024);
       return $retornoTL1;
      }  
      fclose($fp);
    }
  }

  function ativa_telefonia($dev,$frame,$slot,$pon,$ontID,$userNameSIP,$userPSWSip,$sipNameNumber)
  {
    include "telnet_config.php";
    $fp = fsockopen($servidor, $porta, $errno, $errstr, 30);

    if(!$fp) 
    {
      echo "ERROR: $errno - $errstr<br />\n";
    }else{     
      $login_command = "LOGIN:::1::UN=$user_tl1,PWD=$psw_tl1; \n\r\n";
    
    //CFG-ONTVAINDIV::DEV=A1_VERTV-01,FN=0,SN=13,PN=1,ONTID=0,SIPUSERNAME_1=2202300000,
    //SIPUSERPWD_1=123456,SIPNAME_1=2202300000:1::;
      $comando_cadastra_sip = "CFG-ONTVAINDIV::DEV=$dev,FN=$frame,SN=$slot,PN=$pon,ONTID=$ontID,SIPUSERNAME_1=$userNameSIP,SIPUSERPWD_1=$userPSWSip,SIPNAME_1=$sipNameNumber:1::;";
      
      fwrite($fp,$login_command);
      fwrite($fp,$comando_cadastra_sip);

      stream_set_timeout($fp,5);
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
    include_once "telnet_config.php";
    include_once "../db/db_config_mysql.php";
    $fp = fsockopen($servidor, $porta, $errno, $errstr, 30);

    if(!$fp) 
    {
      return "ERROR: $errno - $errstr<br />\n";
    }else{     
      if($servPortIPTV != NULL)
      {
        deleta_btv_iptv($ip,$servPortIPTV);
      }
      

      $login_command = "LOGIN:::1::UN=$user_tl1,PWD=$psw_tl1; \n\r\n";
      
      $comando_deletar = "DEL-ONT::DEV=$dev,FN=$frame,SN=$slot,PN=$pon,ONTID=$ontID,DELCONFIG=TRUE:1::;";

      fwrite($fp,$login_command);
      fwrite($fp,$comando_deletar);

      //$retornoTL1="";
      stream_set_timeout($fp,5);
      while($c = fgetc($fp)!==false)
      {
        $retornoTL1 = fread($fp,2024);
        return $retornoTL1;
      }
    }
    fclose($fp);
  }


  function alterar_ont()
  {

  }

  function get_service_port_internet($dev,$frame,$slot,$pon,$ontID,$contrato)
  {
    include "telnet_config.php";
    $fp = fsockopen($servidor, $porta, $errno, $errstr, 30);

    if(!$fp) 
    {
      echo "ERROR: $errno - $errstr<br />\n";
    }else{     
      $login_command = "LOGIN:::1::UN=$user_tl1,PWD=$psw_tl1; \n\r\n";
    
      $comando = "CRT-SERVICEPORT::DEV=$dev,FN=$frame,SN=$slot,PN=$pon:3::VLANID=2500,SVPID=INTERNET-$contrato,ONTID=$ontID,GEMPORTID=6,UV=2500,RETURID=TRUE;";
        
      fwrite($fp,$login_command);
      fwrite($fp,$comando);

      stream_set_timeout($fp,5);
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

      stream_set_timeout($fp,5);
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
      var_dump($comando);
      fwrite($fp,$login_command);
      fwrite($fp,$comando);

      stream_set_timeout($fp,5);
      while($c = fgetc($fp)!==false)
      {
       $retornoTL1 = fread($fp,2024);
       return $retornoTL1;
      }  
      fclose($fp);
    }
  }

  function insere_btv_iptv($ip_olt,$servicePortIPTV)
  {
    include 'ssh_config.php';
    //include '/vendor/autoload.php'; 
    //set_include_path(get_include_path() . PATH_SEPARATOR . 'phpseclib');
    include('ssh2/Net/SSH2.php');
    
    include('ssh2/File/ANSI.php');
    
    $ssh = new Net_SSH2($ip_olt);
    
    if (!$ssh->login($username, $psk)) {
      return exit("IP:$ip_olt Login Failed");
    }

    $comando_insere_btv = "igmp user add service-port $servicePortIPTV no-auth\n";
    $comando_insere_multicastVlan = "igmp multicast-vlan member service-port $servicePortIPTV\n";

    $ansi = new File_ANSI();
    $ansi->appendString($ssh->read('MA5680T>'));

    $ssh->write("en\n");
    $ssh->write("conf\n");
    $ssh->write("btv\n");
    $ssh->write("$comando_insere_btv");
    $ssh->write("\n"); //CONFIRMA a insersão do btv
    $ssh->write("multicast-vlan 2502\n");
    $ssh->write($comando_insere_multicastVlan);
    $ssh->write("btv\n");
    $ssh->write("display current-configuration\n");
    $ssh->setTimeout(1);
    $ansi->appendString($ssh->read());
    
    $retorno =  $ansi->getScreen(); // outputs HTML

    ######## FILTRA O RESULTADO PARA MOSTRAR SE FOI OU NAO ######## 

    $explo = explode(PHP_EOL, $retorno);
    $filtraNulos = array_filter($explo, 'strlen');
    $array_result = array_values($filtraNulos);

    if(trim($array_result[13]) == "igmp user add service-port $servicePortIPTV no-auth") // se o retorno na posicao 13 for Command:, ele cai no Else
    {
      return "invalido";
    }else{ //se a posicao 13 for o comando do igmp user
      return "valido" ;
    }

    ########  FIM FILTRA O RESULTADO PARA MOSTRAR SE FOI OU NAO ########

  }

  function deleta_btv_iptv($ip_olt,$servicePortIPTV)
  {
    include 'ssh_config.php';
    //include '/vendor/autoload.php'; 
    //set_include_path(get_include_path() . PATH_SEPARATOR . 'phpseclib');
    include('ssh2/Net/SSH2.php');
    
    include('ssh2/File/ANSI.php');
    
    $ssh = new Net_SSH2($ip_olt);
    
    if (!$ssh->login($username, $psk)) {
      return exit("Falha no Login");
    }

    $comando_deleta_btv = "igmp user delete service-port $servicePortIPTV \n";
    
    $ansi = new File_ANSI();
    $ansi->appendString($ssh->read('MA5680T>'));

    $ssh->write("en\n");
    $ssh->write("conf\n");
    $ssh->write("btv\n");
    $ssh->write("$comando_deleta_btv");
    $ssh->write("y\n");
    $ssh->write("display current-configuration\n");
    $ssh->setTimeout(1);
    $ansi->appendString($ssh->read());
    
    $retorno =  $ansi->getScreen(); // outputs HTML
  
    ######## FILTRA O RESULTADO PARA MOSTRAR SE FOI OU NAO ######## 

    //$explo = explode(PHP_EOL, $retorno);
    // $filtraNulos = array_filter($explo, 'strlen');
    // $array_result = array_values($filtraNulos);

    // if(trim($array_result[13]) == "igmp user add service-port $servicePortIPTV no-auth") // se o retorno na posicao 13 for Command:, ele cai no Else
    // {
    //   return "invalido";
    // }else{ //se a posicao 13 for o comando do igmp user
    //   return "valido" ;
    // }

    ########  FIM FILTRA O RESULTADO PARA MOSTRAR SE FOI OU NAO ########

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

      stream_set_timeout($fp,5);
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

      stream_set_timeout($fp,5);
      while($c = fgetc($fp)!==false)
      {
       $retornoTL1 = fread($fp,2024);
       return $retornoTL1;
      }
      fclose($fp);
    } 
  }
?>
