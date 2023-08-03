<?php

	function enviarMensajeEnvio($idMensaje,$arrParam,$funcionEnvio="",$codificarUtf8=false)
	{
		global $con;
		global $urlRepositorioDocumentos;
		$lParametros="";
		$consulta="SELECT parametro FROM 2012_parametrosMensajeEnvio WHERE idMensaje=".$idMensaje." ORDER BY orden";

		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$valor="";
			if(isset($arrParam[$fila[0]]))
				$valor=$arrParam[$fila[0]];
			if($lParametros=="")
				$lParametros='"'.$fila[0].'":"'.cv($valor).'"';
			else
				$lParametros.=',"'.$fila[0].'":"'.cv($valor).'"';
		}
		
		$cadObj='{"p17":{'.$lParametros.'}}';
		
		$paramObj=json_decode($cadObj);

		$arrQueries=resolverQueries($idMensaje,10,$paramObj,true);
		
		$consulta="select * from 2011_mensajesEnvio WHERE idMensajeEnvio=".$idMensaje;
		$fMensaje=$con->obtenerPrimeraFila($consulta);
		$asunto=$fMensaje[6];
		$objValoresCuerpo=json_decode('{"registros":'.$fMensaje[7].'}');
		
		$consulta="SELECT cuerpoMensaje FROM 2013_cuerposMensajes WHERE idMensaje=".$idMensaje;
		$cuerpo=$con->obtenerValor($consulta);

		$cuerpo=str_replace("<strong>","<b>",$cuerpo);
		$cuerpo=str_replace("</strong>","</b>",$cuerpo);
		if($codificarUtf8)
			$cuerpo=utf8_encode($cuerpo);
		if(sizeof($objValoresCuerpo->registros)>0)
		{
			foreach($objValoresCuerpo->registros as $r)
			{
				$cadParametro=$r->lblVariable."||".$r->tVariable."||".$r->valor1."||".$r->valor2."||".$r->renderer;
				$valor=resolverParametroMensaje($cadParametro,$arrParam,$arrQueries);
				$cuerpo=str_replace($r->lblVariable,$valor,$cuerpo);
			}
		}
		
		$arrDestinatario=array();
		$objDestinatario=json_decode('{"registros":'.$fMensaje[8].'}');
		
		if(sizeof($objDestinatario->registros)>0)
		{
			foreach($objDestinatario->registros as $r)
			{
				$cadResultado=resolverParametroMensaje($r->idDestinatario,$arrParam,$arrQueries);
				$arrMail=explode(",",$cadResultado);
				if(sizeof($arrMail)>0)
				{
					foreach($arrMail as $m)
					{
						$m=str_replace("'","",trim($m));
						$objMail[0]=$m;
						$objMail[1]="";
						if(esEmail($m))
						{
							if(existeValorMatriz($arrDestinatario,$m)==-1)
								array_push($arrDestinatario,$objMail);
						}
					}
				}
			}
		}
		

		$arrCC=array();
		$objCC=json_decode('{"registros":'.$fMensaje[9].'}');
		if(sizeof($objCC->registros)>0)
		{
			foreach($objCC->registros as $r)
			{
				$cadResultado=resolverParametroMensaje($r->idDestinatario,$arrParam,$arrQueries);
				$arrMail=explode(",",$cadResultado);
				if(sizeof($arrMail)>0)
				{
					foreach($arrMail as $m)
					{
						$m=str_replace("'","",trim($m));
						$objMail[0]=$m;
						$objMail[1]="";
						if(esEmail($m))
						{
							if(existeValorMatriz($arrCC,$m)==-1)
								array_push($arrCC,$objMail);
						}
					}
				}
			}
		}
		$arrCCO=array();
		$objCCO=json_decode('{"registros":'.$fMensaje[10].'}');
		if(sizeof($objCCO->registros)>0)
		{
			foreach($objCCO->registros as $r)
			{
				$cadResultado=resolverParametroMensaje($r->idDestinatario,$arrParam,$arrQueries);
				$arrMail=explode(",",$cadResultado);
				if(sizeof($arrMail)>0)
				{
					foreach($arrMail as $m)
					{
						$m=str_replace("'","",trim($m));
						$objMail[0]=$m;
						$objMail[1]="";
						if(esEmail($m))
						{
							if(existeValorMatriz($arrCCO,$m)==-1)
								array_push($arrCCO,$objMail);
						}
					}
				}
			}
		}
		$arrDocumentos=array();
		$objDocumento=json_decode('{"registros":'.$fMensaje[11].'}');
		if(sizeof($objDocumento->registros)>0)
		{
			foreach($objDocumento->registros as $r)
			{
				$nombreArchivo="";
				$nDocumento="";
				if($r->tDocumento==1)
				{
					
					$nombreArchivo=obtenerRutaDocumento($r->idDocumento);
					$consulta="SELECT nombreDocumento FROM 9048_galeriaDocumentos WHERE idGaleriaDocumentos=".$r->idDocumento;
					$fDocumento=$con->obtenerPrimeraFila($consulta);
					$nDocumento=$fDocumento[0];
				}
				else
				{
					
					$cadResultado=resolverParametroMensaje("0||".$r->tDocumento."||".$r->idDocumento,$arrParam,$arrQueries);
					$nombreArchivo="../documentosUsr/archivo_".$cadResultado;
					
					$consulta="SELECT nomArchivoOriginal FROM 908_archivos WHERE idArchivo=".$cadResultado;
					$nDocumento=$con->obtenerValor($consulta);
				}
				$obj[0]=$nombreArchivo;
				$obj[1]=$nDocumento;
				if(existeValorMatriz($arrDocumentos,$r->idDocumento)==-1)
					array_push($arrDocumentos,$obj);
			}
		}
		
		$remitente="";
		$objRemitente=json_decode('{"registros":'.$fMensaje[12].'}');
		
		if(sizeof($objRemitente->registros)>0)
		{
			foreach($objRemitente->registros as $r)
			{
				$cadResultado=resolverParametroMensaje($r->idDestinatario,$arrParam,$arrQueries);

				$arrMail=explode(",",$cadResultado);
				if(sizeof($arrMail)>0)
				{
					foreach($arrMail as $m)
					{
						$m=str_replace("'","",trim($m));
						
						$remitente=$m;
						break;
					}
				}
			}
		}
		if($funcionEnvio=="")
			return sendMensajeEnvio($arrDestinatario,$asunto,$cuerpo,$remitente,"",$arrDocumentos,$arrCCO,$arrCC);
		else
		{
			$resultado="";
			eval('$resultado='.$funcionEnvio.'($arrDestinatario,$asunto,$cuerpo,$remitente,"",$arrDocumentos,$arrCCO,$arrCC);');
			return $resultado;
		}
		
	}
		
	function resolverParametroMensaje($cadParametro,$arrParametros,$arrQueries)
	{
		global $con;

		$datosParametros=explode("||",$cadParametro);
		$obj["lblVariable"]=$datosParametros[0];
		$obj["tVariable"]=$datosParametros[1];
		$obj["valor1"]=$datosParametros[2];
		$obj["valor2"]=$datosParametros[3];
		$obj["renderer"]=0;
		if(isset($datosParametros[4]))
			$obj["renderer"]=$datosParametros[4];
		$valor="";

		switch($obj["tVariable"])
		{
			case 1:   	//V. Sesion
				$consulta="select valorSesion,valorReemplazo from 8003_valoresSesion where idValorSesion=".$obj["valor1"];
				$filaSesion=$con->obtenerPrimeraFila($consulta);
				if(($obj["valor1"]==1)||($obj["valor1"]==4))
					$valor=$_SESSION[$filaSesion[0]];
				else
					$valor=$_SESSION[$filaSesion[0]];
				
			break;
			case 2:		//V. Sistema
				$valorSistema="";
				switch($obj["valor1"])
				{
					case "8":
						$valorSistema=date("Y-m-d");
					break;
					case "9":
						$valorSistema=date("H:i");
					break;
				}
				$valor=$valorSistema;
			break;
			case 3:		//Consulta aux.
				if(isset($arrQueries[$obj["valor1"]]))
				{
					if($arrQueries[$obj["valor1"]]["ejecutado"]==1)
					{
						$valor=substr($arrQueries[$obj["valor1"]]["resultado"],1,strlen($arrQueries[$obj["valor1"]]["resultado"])-2);
					}
				}
			break;
			case 4:		//Alm. datos
				if(isset($arrQueries[$obj["valor1"]]))
				{
					
					if($arrQueries[$obj["valor1"]]["ejecutado"]==1)
					{
						$res=$arrQueries[$obj["valor1"]]["resultado"];
						
						$conAux=$arrQueries[$obj["valor1"]]["conector"];
						$conAux->inicializarRecurso($res);
						while($f=$conAux->obtenerSiguienteFilaAsoc($res))
						{
							$nCampo=str_replace(".","_",$obj["valor2"]);
							
							$valorAux="";
							
							if(isset($f[$nCampo]))
							{
								$valorAux=$f[$nCampo];
								
							}
							else
								break;
							if($valor=="")
								$valor=$valorAux;
							else
								$valor.=", ".$valorAux;
						}
					}
				}
			break;
			case 5:		//V. Parametro
				if(isset($arrParametros[$obj["valor1"]]))
					$valor=$arrParametros[$obj["valor1"]];
			break;
			case 6:		//V. Manual
				$valor=$obj["valor1"];
			break;
		}
		if($obj["renderer"]!=0)
		{
			$cadObj='{"param1":"'.$valor.'"}';
			$objParam=json_decode($cadObj);	
			$nulo=NULL;		
			$valor=resolverExpresionCalculoPHP($obj["renderer"],$objParam,$nulo);
			if(substr($valor,0,1)=="'")
			{
				$valor=substr($valor,1);
			}
			if(substr($valor,strlen($valor)-1)=="'")
			{
				$valor=substr($valor,0,strlen($valor)-1);
			}
		}
		return $valor;
		
	}	
	
	function sendMensajeEnvio($arrDestinatario,$asunto,$mensaje,$emisor="",$nombreEmisor="",$arrArchivos=null,$arrCopiaOculta=null,$arrCopia=null)
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
        //$mail->SMTPDebug  = 1;                  // set mailer to use SMTP
		$mail->Host = "localhost";  // specify main and backup server
		$mail->SMTPAuth = false;     // turn on SMTP authentication
		/*$mail->Username = "jswan";  // SMTP username
		$mail->Password = "secret"; // SMTP password*/
		
		$mail->From = $em;

		if($nombreEmisor!="")
			$mail->FromName=$nomEmisor;
		
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
	
	function enviarMensajeElectronico($idFormulario,$idRegistro,$idMensaje)
	{
		$arrParam=array();
		$arrParam["idFormulario"]=$idFormulario;
		$arrParam["idRegistro"]=$idRegistro;
		enviarMensajeEnvio($idMensaje,$arrParam);
	}
	
?>