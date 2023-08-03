<?php

	function enviarInvitacionesReunion($idReunion,$idParticipante="",$tipoNotificacion=1)
	{
		global $con;
		global $arrDiasSemana;
		global $arrMesLetra;
		global $urlPantallaAccesoReunion;
		global $urlSitio;
		global $versionLatis;


		$urlAccesoAudiencia=$urlPantallaAccesoReunion;
		$consulta="SELECT idCategoria FROM 2010_categoriasMensajeEnvio WHERE nombreCategoria LIKE '%Invitación Latis Meeting%'";
		if($tipoNotificacion==0)
			$consulta="SELECT idCategoria FROM 2010_categoriasMensajeEnvio WHERE nombreCategoria LIKE '%Cancelación Latis Meeting%'";
		
		$idCategoria=$con->obtenerValor($consulta);
		
		$consulta="SELECT idMensajeEnvio FROM 2011_mensajesEnvio WHERE idCategoria=".$idCategoria;
		$idMensajeEnvio=$con->obtenerValor($consulta);
		
		$consulta="SELECT * FROM 7050_reunionesVirtualesProgramadas WHERE idRegistro=".$idReunion;
		$fReunion=$con->obtenerPrimeraFilaAsoc($consulta);
		
		$fechaReunion=strtotime($fReunion["fechaProgramada"]);
		
		$lblHoras="";
		$horas=floor ($fReunion["duracion"]/60);
		
		if($horas>0)
		{
			$lblHoras=$horas." ".($horas==1?"hora":"horas");
			
		}
		
		$minutos=$fReunion["duracion"]-($horas*60);
		if($minutos>0)
		{
			$lblHoras.=", ".$minutos." minutos";
		}
		
		$datosReunion=$fReunion["nombreReunion"]."<br>".utf8_encode($arrDiasSemana[date("w",$fechaReunion)*1]).", ".date("d",$fechaReunion)." de ".$arrMesLetra[(date("m",$fechaReunion)*1)-1]." de ".date("Y",$fechaReunion)."<br>";
		$datosReunion.=date("h:i a",$fechaReunion)." | (UTC-05:00) Guadalajara, Ciudad de México, Monterrey | ".$lblHoras;

		$formatoReunionWhat=$fReunion["nombreReunion"].". ".utf8_encode($arrDiasSemana[date("w",$fechaReunion)*1]).", ".date("d",$fechaReunion)." de ".$arrMesLetra[(date("m",$fechaReunion)*1)-1]." de ".date("Y",$fechaReunion).". ";
		$formatoReunionWhat.=date("h:i a",$fechaReunion)." | (UTC-05:00) Guadalajara, Ciudad de México, Monterrey | ".$lblHoras;
		
		$consulta="SELECT * FROM 7051_participantesReunionesVirtuales WHERE idReunion=".$idReunion;
		if($idParticipante!="")
		{
			$consulta="SELECT * FROM 7051_participantesReunionesVirtuales WHERE idRegistro=".$idParticipante;
		}

		$res=$con->obtenerFilas($consulta);

		while($fila=mysql_fetch_assoc($res))
		{
			
			
			$nombreParticipante="";
			switch($fila["tipoParticipante"])
			{
				case 1:
					$nombreParticipante=obtenerNombreUsuario($fila["nombreParticipante"]);
				break;
				case 2:
				case 3:
					$nombreParticipante=$fila["nombreParticipante"];
				break;
			}
			$consulta="select HEX(AES_ENCRYPT('".$fReunion["reunionID"]."_".$fila["passwdReunion"]."', '".bD($versionLatis)."'))";
			$claveAcceso=$con->obtenerValor($consulta);
			
			$enlaceAcceso=$urlAccesoAudiencia."?meeting=".$claveAcceso;
			if($fila["telefono"]!="")
			{
				$notifica="se ha programado una nueva audiencia virtual,";
				$mensajeEnvio="Estimado/a *".$nombreParticipante."*, se le notifica que ".$notifica." los detalles se describen a continuación: ".$formatoReunionWhat.". -- Podra ingresar a través del siguiente enlace: ".$enlaceAcceso."\n";
				
				

				$arrTelefono=explode(",",$fila["telefono"]);

				foreach($arrTelefono as $t)
				{
					$aTel=explode(") ",$t);
					
					$prefijoCelular=str_replace("(","",$aTel[0]."1");
					
					$telefono=str_replace("-","",$aTel[1]);
					
					$totalIntentos=0;
					
					$resultado=json_decode('{"resultado":"0"}');
					
					while(($resultado->resultado==0)&&($totalIntentos<11))
					{

						$resultado=sendMensajeWhatApp($telefono,$mensajeEnvio,$prefijoCelular);

						$totalIntentos++;

					}
					
				}
			}
			
			
			
		}
		
		if(mysql_num_rows($res)>0)
		{
			mysql_data_seek($res,0);

			while($fila=mysql_fetch_assoc($res))
			{
				
				$consulta="select HEX(AES_ENCRYPT('".$fReunion["reunionID"]."_".$fila["passwdReunion"]."', '".bD($versionLatis)."'))";
				$claveAcceso=$con->obtenerValor($consulta);
				$notificado=false;
				if($fila["eMail"]!="")
				{
					$arrMail=explode(",",$fila["eMail"]);
	
					foreach($arrMail as $m)
					{
						$arrParam["mail"]=trim($m);
						$arrParam["codigoAcceso"]=$fReunion["reunionID"];
						$arrParam["passwdAcceso"]=$fila["passwdReunion"];
						$arrParam["datosReunion"]=$datosReunion;
						$arrParam["urlAcceso"]="<a href='".$urlAccesoAudiencia."'>".$urlAccesoAudiencia."</a>";
						$arrParam["btnAcceso"]="<div style='text-align:center;vertical-align:middle;height:30px; width:230px;font-weight:bold; background-color:#900; color:#FFF;border-radius: 20px;  line-height: 30px;'><a style='color:#FFF;text-decoration:none;' href='".$urlAccesoAudiencia."?meeting=".$claveAcceso."'>Entrar a reuni&oacute;n</a></div>";

						if(enviarMensajeEnvio($idMensajeEnvio,$arrParam,"sendMensajeEnvioWebMailMeeting"))
						{
							$notificado=true;
						}
					}
				}
				
				if($notificado)
				{
					$consulta="UPDATE 7051_participantesReunionesVirtuales SET notificado=1 WHERE idRegistro=".$fila["idRegistro"];
					$con->ejecutarConsulta($consulta);
				}
				
			}
		}
		return true;
		
	}

	/*function sendMensajeWhatApp($numeroCel,$mensaje,$prefijoCelular=521)
	{
		
		global $urlWhatsAppWebServices;
		
		$client = new nusoap_client($urlWhatsAppWebServices."?wsdl","wsdl");
		$objResp=NULL;
		$parametros=array();
		$parametros["numeroDestino"]=$numeroCel;
		$parametros["MensajeDestino"]=$mensaje;
		$parametros["prefijoCelular"]=$prefijoCelular;
		$parametros["numeroOrigen"]="16474927546";
		$resultado = $client->call("sendMessageWhatApp", $parametros);
		
		if(gettype($resultado)=="array")
		{
			$objResp=json_decode($resultado["sendMessageWhatAppResult"]);
		}
		else
		{
			$objResp=json_decode($resultado);
		}
	
		return $objResp;
	
	}*/


	function sendMensajeEnvioGmailMeeting($arrDestinatario,$asunto,$mensaje,$emisor="",$nombreEmisor="",$arrArchivos=null,$arrCopiaOculta=null,$arrCopia=null)
	{
		global $habilitarEnvioCorreo;
		global $mailAdministrador;
		global $nombreEmisorAdministrador;
		global $SO;
		global $urlSitio;
		
		
		if(!$habilitarEnvioCorreo)
			return true;
		$em=$mailAdministrador;
	
		if($emisor!="")
			$em=$emisor;
		$nomEmisor=$nombreEmisor;
		$mail = new PHPMailer();
		if($emisor!="")
		{
			$em=$emisor;
			$nomEmisor=$nombreEmisor;
		}
		
		$mail->IsSMTP();        
		
		$mail->From = $em;
	
		if($nombreEmisor!="")
			$mail->FromName=$nomEmisor;
		
		$mail->SMTPDebug = 0;
		$mail->Debugoutput = 'html';
		$mail->Host = "smtp.gmail.com";  // specify main and backup server
		$mail->Port = 25 ;
		$mail->SMTPAuth = true;     // turn on SMTP authentication
		$mail->Username = "notificaciones.sgjp@tsjcdmx.gob.mx";  // SMTP username
		$mail->Password = "TsjdfBPM";
		
		$mail->SetFrom ("notificaciones.sgjp@tsjcdmx.gob.mx","notificaciones.sgjp@tsjcdmx.gob.mx");
		
		foreach($arrDestinatario as $destinatario)
		{
			
			if($destinatario[0]!="")
				$mail->AddAddress(trim($destinatario[0]));
		}
		//$mail->AddReplyTo($em, $nomEmisor);
		$mail->WordWrap = 70;  
		if(sizeof($arrCopiaOculta)>0)
		{
			foreach($arrCopiaOculta as $c)
				$mail->AddBCC($c[0],$c[1]);
		}
		if(sizeof($arrCopia)>0)
		{
			foreach($arrCopia as $c)
				$mail->AddCC($c[0],$c[1]);
		}
		if(sizeof($arrArchivos)>0)
		{
			$nArchivos=sizeof($arrArchivos);
			for($x=0;$x<$nArchivos;$x++)
			{
				
				$mail->AddAttachment($arrArchivos[$x][0],$arrArchivos[$x][1]);         
			}
		}
	
		if($SO==2)
		{
			$mail->Subject = utf8_decode($asunto);
			$mail->Body    = utf8_decode($mensaje);	
		}
		else
		{
			$mail->Subject = utf8_decode($asunto);
			$mail->Body    = utf8_decode($mensaje);
		}
		$mail->IsHTML(true);                                  
		return $mail->Send();
	}
	
	function sendMensajeEnvioWebMailMeeting($arrDestinatario,$asunto,$mensaje,$emisor="",$nombreEmisor="",$arrArchivos=null,$arrCopiaOculta=null,$arrCopia=null)
	{
		global $habilitarEnvioCorreo;
		global $mailAdministrador;
		global $nombreEmisorAdministrador;
		global $SO;
		global $urlSitio;
		
		
		if(!$habilitarEnvioCorreo)
			return true;
		$em=$mailAdministrador;
	
		if($emisor!="")
			$em=$emisor;
		$nomEmisor=$nombreEmisor;
		$mail = new PHPMailer();
		if($emisor!="")
		{
			$em=$emisor;
			$nomEmisor=$nombreEmisor;
		}
		
		$mail->IsSMTP();        
		
		$mail->From = $em;
	
		if($nombreEmisor!="")
			$mail->FromName=$nomEmisor;
		
		$mail->SMTPDebug = 0;
		$mail->Debugoutput = 'html';
		$mail->Host = "localhost";  // specify main and backup server
		$mail->SMTPAuth = false;     // turn on SMTP authentication
		$mail->SMTPAutoTLS = false;
		
		
		foreach($arrDestinatario as $destinatario)
		{
			
			if($destinatario[0]!="")
				$mail->AddAddress(trim($destinatario[0]));
		}
		//$mail->AddReplyTo($em, $nomEmisor);
		$mail->WordWrap = 70;  
		if(sizeof($arrCopiaOculta)>0)
		{
			foreach($arrCopiaOculta as $c)
				$mail->AddBCC($c[0],$c[1]);
		}
		if(sizeof($arrCopia)>0)
		{
			foreach($arrCopia as $c)
				$mail->AddCC($c[0],$c[1]);
		}
		if(sizeof($arrArchivos)>0)
		{
			$nArchivos=sizeof($arrArchivos);
			for($x=0;$x<$nArchivos;$x++)
			{
				
				$mail->AddAttachment($arrArchivos[$x][0],$arrArchivos[$x][1]);         
			}
		}
	
		if($SO==2)
		{
			$mail->Subject = utf8_decode($asunto);
			$mail->Body    = utf8_decode($mensaje);	
		}
		else
		{
			$mail->Subject = utf8_decode($asunto);
			$mail->Body    = utf8_decode($mensaje);
		}
		$mail->IsHTML(true);                                  
		
		return $mail->Send();
	}


?>