<?php
	include_once("latis/conexionBD.php"); 
	include_once("latis/utiles.php");
				
				 
	class cBigBlueButton
	{
		var $urlHost;
		var $secretPasswd;	

		
		function cBigBlueButton($host="",$secretKey="")  //constructor de la clase cBigBlueButton
		{
			global $URLServidorBBB;
			global $llaveServidorBBB;
			global $prefijoUsuarioBBB;
			$this->urlHost=$host==""?$URLServidorBBB:$host;
			$this->secretPasswd=$secretKey==""?$llaveServidorBBB:$secretKey;
		}
		
		function programarReunion($arrParams)
		{
			global $urlPaginaRedireccionEndMeeting;
			$arrParametros=array();
			$arrParametros["name"]="";//--
			$arrParametros["meetingID"]=str_replace("}","",str_replace("{","",$this->getGUID()));
			$arrParametros["attendeePW"]=$this->crearPasswordAleatorio();//--
			$arrParametros["moderatorPW"]=$this->crearPasswordAleatorio();//--
			$arrParametros["welcome"]="Poder Judicial de la Ciudad de México";//--
			$arrParametros["dialNumber"]="";
			$arrParametros["voiceBridge"]="";
			$arrParametros["maxParticipants"]="10"; //--
			$arrParametros["logoutURL"]=$urlPaginaRedireccionEndMeeting;
			$arrParametros["record"]="true"; //--
			$arrParametros["duration"]="60"; //--
			$arrParametros["isBreakout"]="false";
			$arrParametros["parentMeetingID"]="";
			$arrParametros["sequence"]="";
			$arrParametros["freeJoin"]="";
			$arrParametros["meta"]="";
			$arrParametros["moderatorOnlyMessage"]="";
			$arrParametros["autoStartRecording"]="false"; //--
			$arrParametros["allowStartStopRecording"]="true"; //--
			$arrParametros["webcamsOnlyForModerator"]="false"; //--
			$arrParametros["logo"]="https://grupolatis.net/principal/disenoLatis/assets/images/logolatis.png";
			$arrParametros["bannerText"]="";
			$arrParametros["bannerColor"]="";
			$arrParametros["copyright"]="By Latis Co.";
			$arrParametros["muteOnStart"]="true"; //--
			$arrParametros["allowModsToUnmuteUsers"]="true"; //--
			$arrParametros["lockSettingsDisableCam"]="false"; //--
			$arrParametros["lockSettingsDisableMic"]="false"; //--
			$arrParametros["lockSettingsDisablePrivateChat"]="false"; //--
			$arrParametros["lockSettingsDisablePublicChat"]="false"; //--
			$arrParametros["lockSettingsDisableNote"]="false"; //--
			$arrParametros["lockSettingsLockedLayout"]="false";
			$arrParametros["lockSettingsLockOnJoin"]="false";
			$arrParametros["lockSettingsLockOnJoinConfigurable"]="false";
			$arrParametros["guestPolicy"]="ALWAYS_ACCEPT";
			$arrParametros["meta_bbb-recording-ready-url"]="";
			$arrParametros["meta_endCallbackUrl"]="";
			$arrParametros["userdata-bbb_custom_style"]=":root{--loader-bg:#900;}.overlay--1aTlbi{background-color:#900!important;}body{background-color:#900!important;}";
			
			foreach($arrParametros as $llave=>$valor)
			{
				if(isset($arrParams[$llave]))
				{
					$arrParametros[$llave]=$arrParams[$llave];
				}
			}
			
			foreach($arrParametros as $llave=>$valor)
			{
				$arrParametros[$llave]=urlencode($valor);
			}
			
			$urlPeticion="";
			foreach($arrParametros as $llave=>$valor)
			{
				if($valor!="")
				{
					$token=$llave."=".$valor;
					if($urlPeticion=="")
						$urlPeticion=$token;
					else
						$urlPeticion.="&".$token;
				}
			}
			$urlToken="create".$urlPeticion.$this->secretPasswd;
			$checksum=sha1($urlToken);
			
			$urlPeticion.="&checksum=".$checksum;

			$url=$this->urlHost."/api/create";
			
			
			$options = array();
			$context  = stream_context_create($options);
			$result = file_get_contents($url."?".$urlPeticion, false, $context);
			
			$cXML=simplexml_load_string($result);

			$objResp=array();
			
			if($cXML->returncode=="SUCCESS")
			{
				$objResp["resultado"]=true;
				$objResp["meetingID"]=(string)$cXML->meetingID;
				$objResp["attendeePW"]=(string)$cXML->attendeePW;
				$objResp["moderatorPW"]=(string)$cXML->moderatorPW;
				$objResp["dialNumber"]=(string)$cXML->dialNumber;
				$objResp["duration"]=(string)$cXML->duration;
				
			}
			else
			{
				$objResp["resultado"]=false;
				$objResp["msgKey"]=(string)$cXML->messageKey;
				$objResp["msgError"]=(string)$cXML->message;
			}
			
			return $objResp;
		}

		function compartirReunion($arrParams)
		{
			$arrParametros=array();
			$arrParametros["fullName"]="";//--
			$arrParametros["meetingID"]="";//--
			$arrParametros["password"]="";//--
			$arrParametros["createTime"]="";
			$arrParametros["userID"]="";//--
			$arrParametros["webVoiceConf"]="";
			$arrParametros["configToken"]="";
			$arrParametros["defaultLayout"]="";
			$arrParametros["avatarURL"]="";
			$arrParametros["redirect"]="true";
			$arrParametros["clientURL"]="";
			$arrParametros["joinViaHtml5"]="false";
			$arrParametros["guest"]="false";
			
			
			foreach($arrParametros as $llave=>$valor)
			{
				if(isset($arrParams[$llave]))
				{
					$arrParametros[$llave]=$arrParams[$llave];
				}
			}
			
			foreach($arrParametros as $llave=>$valor)
			{
				$arrParametros[$llave]=urlencode($valor);
			}
			
			$urlPeticion="";
			foreach($arrParametros as $llave=>$valor)
			{
				if($valor!="")
				{
					$token=$llave."=".$valor;
					if($urlPeticion=="")
						$urlPeticion=$token;
					else
						$urlPeticion.="&".$token;
				}
			}
			

			$urlToken="join".$urlPeticion.$this->secretPasswd;

			$checksum=sha1($urlToken);
			
			$urlPeticion.="&checksum=".$checksum;

			$url=$this->urlHost."/api/join";
			
			return $url."?".$urlPeticion;
			
			
			$resultado="";
			$result=fopen($url."?".$urlPeticion,"r");
			while(($linea=fgets($result))!==false)
			{
				$resultado.=$linea;
			}
			
			
			$cXML=simplexml_load_string($resultado);
			$arrMetaData=stream_get_meta_data($result);

			if($cXML->returncode=="SUCCESS")
			{
				foreach($arrMetaData["wrapper_data"] as $linea)
				{
					if(strpos($linea,"JSESSIONID=")!==false)
					{
						$arrLinea=explode("JSESSIONID=",$linea);
						$arrLinea2=explode(";",$arrLinea[1]);
						$objResp["cookie"]=$arrLinea2[0];
					}
				}
				$objResp["resultado"]=true;
				$objResp["url"]=(string)$cXML->url;
				$objResp["session_token"]=(string)$cXML->session_token;
				$objResp["auth_token"]=(string)$cXML->auth_token;
				$objResp["user_id"]=(string)$cXML->user_id;
				$objResp["meeting_id"]=(string)$cXML->meeting_id;
				
			}
			else
			{
				$objResp["resultado"]=false;
				$objResp["msgKey"]=(string)$cXML->messageKey;
				$objResp["msgError"]=(string)$cXML->message;
			}
			
			return $objResp;
		}
		
		function esReunionActiva($meetingID)
		{
			$arrParametros=array();
			$arrParametros["meetingID"]=$meetingID;
			
			
			$urlPeticion="";
			foreach($arrParametros as $llave=>$valor)
			{
				if($valor!="")
				{
					$token=$llave."=".$valor;
					if($urlPeticion=="")
						$urlPeticion=$token;
					else
						$urlPeticion.="&".$token;
				}
			}
			$urlToken="isMeetingRunning".$urlPeticion.$this->secretPasswd;
			$checksum=sha1($urlToken);
			
			$urlPeticion.="&checksum=".$checksum;

			$url=$this->urlHost."/api/isMeetingRunning";
			
			$options = array();
			$context  = stream_context_create($options);
			$result = file_get_contents($url."?".$urlPeticion, false, $context);
			
			$cXML=simplexml_load_string($result);

			$objResp=array();
			
			if($cXML->returncode=="SUCCESS")
			{
				$objResp["resultado"]=true;
				$objResp["running"]=((string)$cXML->running)=="true";
				
			}
			else
			{
				$objResp["resultado"]=false;
				$objResp["msgKey"]=(string)$cXML->messageKey;
				$objResp["msgError"]=(string)$cXML->message;
			}
			
			return $objResp;
		}
		
		function crearPasswordAleatorio() 
		{
			$alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
			$pass = array(); //remember to declare $pass as an array
			$alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
			for ($i = 0; $i < 8; $i++) 
			{
				$n = rand(0, $alphaLength);
				$pass[] = $alphabet[$n];
			}
			return implode($pass); //turn the array into a string
		}
		
		function obtenerGrabaciones($meetingID)
		{
			$arrParametros=array();
			$arrParametros["meetingID"]=$meetingID;
			
			
			$urlPeticion="";
			foreach($arrParametros as $llave=>$valor)
			{
				if($valor!="")
				{
					$token=$llave."=".$valor;
					if($urlPeticion=="")
						$urlPeticion=$token;
					else
						$urlPeticion.="&".$token;
				}
			}
			$urlToken="getRecordings".$urlPeticion.$this->secretPasswd;
			$checksum=sha1($urlToken);
			
			$urlPeticion.="&checksum=".$checksum;

			$url=$this->urlHost."/api/getRecordings";
			
			$options = array();
			$context  = stream_context_create($options);
			
			
			return $url."?".$urlPeticion;
			
			$result = file_get_contents($url."?".$urlPeticion, false, $context);
			
			$cXML=simplexml_load_string($result);
			$objResp=array();
			
			if($cXML->returncode=="SUCCESS")
			{
				
				$objResp["resultado"]=true;
				$objResp["urlVideo"]=(string)$cXML->recordings[0]->recording[0]->playback[0]->format[0]->url[0];
				
				
			}
			else
			{
				$objResp["resultado"]=false;
				$objResp["msgKey"]=(string)$cXML->messageKey;
				$objResp["msgError"]=(string)$cXML->message;
			}
			
			return $objResp;
		}
		
		function obtenerConfiguracion($meetingID)
		{

			$arrParametros=array();
			$arrParametros["meetingID"]=$meetingID;
			
			
			$urlPeticion="";
			foreach($arrParametros as $llave=>$valor)
			{
				if($valor!="")
				{
					$token=$llave."=".$valor;
					if($urlPeticion=="")
						$urlPeticion=$token;
					else
						$urlPeticion.="&".$token;
				}
			}
			$urlToken="getDefaultConfigXML".$urlPeticion.$this->secretPasswd;
			$checksum=sha1($urlToken);
			
			$urlPeticion.="&checksum=".$checksum;

			$url=$this->urlHost."/api/getDefaultConfigXML";
			
			$options = array();
			$context  = stream_context_create($options);
			return $url."?".$urlPeticion;
			$result = file_get_contents($url."?".$urlPeticion, false, $context);
			varDUmp($result);
			return;
			$cXML=simplexml_load_string($result);

			$objResp=array();
			
			if($cXML->returncode=="SUCCESS")
			{
				$objResp["resultado"]=true;
				$objResp["running"]=((string)$cXML->running)=="true";
				
			}
			else
			{
				$objResp["resultado"]=false;
				$objResp["msgKey"]=(string)$cXML->messageKey;
				$objResp["msgError"]=(string)$cXML->message;
			}
			
			return $objResp;
		}
		
		
		function getGUID()
		{
       		if (function_exists('com_create_guid'))
			{
           		return com_create_guid();
       		}
       		else 
			{
           		mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
           		$charid = strtoupper(md5(uniqid(rand(), true)));
           		$hyphen = chr(45);// "-"
           		$uuid = chr(123)// "{"
               .substr($charid, 0, 8).$hyphen
               .substr($charid, 8, 4).$hyphen
               .substr($charid,12, 4).$hyphen
               .substr($charid,16, 4).$hyphen
               .substr($charid,20,12)
               .chr(125);// "}"
           		return $uuid;
       		}
		}
		
	}
?>