<?php 	

	require("latis/class.phpmailer.php");
	set_time_limit(999000);
		
	function obtenerFormatoFecha()
	{
		return 'd/m/Y';
		
	}

	function cv($valor,$hTrim=true)
	{
		/*if($hTrim)
			return mysql_real_escape_string(html_entity_decode(trim(str_replace("#R","",$valor)),ENT_NOQUOTES, 'UTF-8'));
		else
			return mysql_real_escape_string(html_entity_decode(str_replace("#R","",$valor),ENT_NOQUOTES, 'UTF-8'));*/
		$cadena="";
		$search = array("\\",  "\x00", "\n",  "\r",  "'",  '"', "\x1a");
		$replace = array("\\\\","\\0","\\n", "\\r", "\'", '\"', "\\Z");
		if($hTrim)
			$cadena=html_entity_decode(trim(str_replace("#R","",$valor)),ENT_NOQUOTES, 'UTF-8');
		else
			$cadena=html_entity_decode((str_replace("#R","",$valor)),ENT_NOQUOTES, 'UTF-8');
		return str_replace($search, $replace, $cadena);
	}
	
	function dv($valor)
	{
		$cadena="";
		$search = array("\\",  "\x00", "\n",  "\r",  "'",  '"', "\x1a");
		$replace = array("\\\\","\\0","\\n", "\\r", "\'", '\"', "\\Z");
		$cadena=utf8_decode(trim($valor));
		return str_replace($search, $replace, $cadena);

	}
	
	function cambiarFormatoFecha($fecha,$separadorBase="-",$separadorFinal="/",$validar=false)
	{
		list($anio,$mes,$dia)=explode($separadorBase,$fecha);
		
		if(!$validar)
			return $dia.$separadorFinal.$mes.$separadorFinal.$anio;
		else
		{
			if(strlen($anio)==4)
				return $dia.$separadorFinal.$mes.$separadorFinal.$anio;
			return $anio.$separadorFinal.$mes.$separadorFinal.$dia;
		}
	}
	
	function dvJs($cadena)
	{
		return str_replace('\\"','"',$cadena);
	} 
	
	function cvJs($cadena)
	{
		return str_replace('"','\\"',$cadena);
	}
	
	function cambiaraFechaMysql($fecha)
	{

		list($dia,$mes,$anios)=explode("/",$fecha);
		return $anios.'-'.$mes."-".$dia;
	}
	
	function obtenerNombreEmpresaCliente($id)
	{
		global $con;
		$consulta="SELECT IF(tipoEmpresa=2,razonSocial ,CONCAT(razonSocial,' ',apPaterno,' ',apMaterno))AS nombre 
				FROM 10_empresa WHERE idEmpresa='".$id."'";
		$nombre=$con->obtenerValor($consulta);
		
		return $nombre;
	}
	
	function uE($cadena)
	{
		global $codificarUTF8uE;
		if($codificarUTF8uE)
			return utf8_encode($cadena);
		else
			return $cadena;
	}
	
	function uEJ($cadena)
	{
		global $codificarUTF8uEJ;
		if($codificarUTF8uEJ)
			return utf8_decode($cadena);
		else
			return $cadena;
	}
		
	function uDJ($cadena)
	{
		global $decodificarUTF8uEJ;
		if($decodificarUTF8uEJ)
			return utf8_decode($cadena);
		else
			return $cadena;
	}
		
	function uEJ2($cadena)
	{
		return $cadena;
	}
		
	function quitarAcentos($text,$quitarSignosPuntuacion=false)
	{
		global $SO;
		//if($SO==1)
		$text = utf8_decode($text);
		$text=strtolower($text);
		$text=str_replace("�","a",$text);
		$text=str_replace("�","e",$text);
		$text=str_replace("�","i",$text);
		$text=str_replace("�","o",$text);
		$text=str_replace("�","u",$text);
		$text=str_replace("�","n",$text);
		
		return $text;
	}
	
function convertirFraseCamelCase($cadena)
{
	$arrPalabras=explode(' ',$cadena);
	$tamArr=sizeof($arrPalabras);
	$palCamel="";
	for($ct=0;$ct<$tamArr;$ct++)
	{
		if(trim($arrPalabras[$ct])!='')
		{
			if($ct==0)
			
				$palCamel=strtolower($arrPalabras[$ct]);
			else
				$palCamel.=strtoupper($arrPalabras[$ct][0]).strtolower(substr($arrPalabras[$ct],1));
		}
	}
	return $palCamel;
}
	
function generarNombreTabla($cadena)
{
	$cadenaFinal=str_replace(',','',$cadena);
	$cadenaFinal=str_replace('-','',$cadenaFinal);
	$cadenaFinal=str_replace('+','',$cadenaFinal);
	$cadenaFinal=str_replace('*','',$cadenaFinal);
	$cadenaFinal=str_replace('/','',$cadenaFinal);
	$cadenaFinal=str_replace('#','',$cadenaFinal);
	$cadenaFinal=str_replace('"','',$cadenaFinal);
	$cadenaFinal=str_replace('\\','',$cadenaFinal);
	$cadenaFinal=str_replace('!','',$cadenaFinal);
	$cadenaFinal=str_replace('�','',$cadenaFinal);
	$cadenaFinal=str_replace('�','',$cadenaFinal);
	$cadenaFinal=str_replace('?','',$cadenaFinal);
	$cadenaFinal=str_replace('=','',$cadenaFinal);
	$cadenaFinal=str_replace('$','',$cadenaFinal);
	$cadenaFinal=str_replace('%','',$cadenaFinal);
	$cadenaFinal=str_replace('&','',$cadenaFinal);
	$cadenaFinal=str_replace('(','',$cadenaFinal);
	$cadenaFinal=str_replace(')','',$cadenaFinal);
	$cadenaFinal=str_replace('.','',$cadenaFinal);
	$cadenaFinal=str_replace(';','',$cadenaFinal);
	$cadenaFinal=str_replace('{','',$cadenaFinal);
	$cadenaFinal=str_replace('}','',$cadenaFinal);
	$cadenaFinal=str_replace('[','',$cadenaFinal);
	$cadenaFinal=str_replace(']','',$cadenaFinal);	
	$cadenaFinal=str_replace('^','',$cadenaFinal);
	$cadenaFinal=str_replace("'","",$cadenaFinal);
	
	return convertirFraseCamelCase(quitarAcentos($cadenaFinal));
}
	
function existeRol($idRol)
{
	
	if(isset($_SESSION)&&isset($_SESSION["idRol"]))
	{
		$arrRol=explode(',',$_SESSION["idRol"]);
		
		$ct=sizeof($arrRol);
		for($x=0;$x<$ct;$x++)
		{
			
			if(strcmp($arrRol[$x],$idRol)==0)
			{
				return true;
			}
		}
	}
	return false;
}
	
function existeValor($arr,$valor)
{
	$res=false;
	$ct=sizeof($arr);

	for($x=0;$x<$ct;$x++)
	{
		
		if($arr[$x]==$valor)
			return true;
	}
	return false;
}
	
function existeValorMatriz($arr,$valor,$col=0)
{
	$ct=sizeof($arr);
	for($x=0;$x<$ct;$x++)
	{
		if($arr[$x][$col]==$valor)
			return $x;
	}
	return "-1";
}
	
function obtenerValorParametro($parametro,$valorDefault="")
{
	$valor=$valorDefault;
	if(isset($_POST[$parametro]))
		$valor=$_POST[$parametro];
	else
		if(isset($_GET[$parametro]))
			$valor=$_GET[$parametro];
		else
		{
			if(isset($_POST["configuracion"]))	
			{
				$parametros=$_SESSION["configuracionesPag"][$_POST["configuracion"]]["parametros"];			
				$objParametros=json_decode($parametros);	

				eval('if(isset($objParametros->'.$parametro.'))$valor=$objParametros->'.$parametro.";");
			
			}
		}
	return $valor;
}
	
function enviarCircular($parametros2=null,$remitente="")
{
	global $con;
	$mostrarXML=false;
	if($parametros2==null)
		$parametros=$_POST["parametros"];
	else
		$parametros=$parametros2;
	
	$objParametro=json_decode($parametros);
	
	$arrParametros=$objParametro->arrParametros;
	$cadDest=$objParametro->destinatarios;
	$numParam=sizeof($arrParametros);
	$idAccion=$objParametro->idAccion;
	$consulta="select asunto,cuerpo from 2004_mensajesAcciones where idIdioma=".$_SESSION["leng"]." and idAccionEnvio=".$idAccion;
	
	$fila=$con->obtenerPrimeraFila($consulta);
	$asunto=$fila[0];
	$cuerpo=$fila[1];
	
	for($x=0;$x<$numParam;$x++)
		$cuerpo=str_replace($arrParametros[$x][0],$arrParametros[$x][1],$cuerpo);

	$arrDestinatario=explode(',',$cadDest);
	$numDest=sizeof($arrDestinatario);
	for($x=0;$x<$numDest;$x++)
	{
		if(!enviarMail($arrDestinatario[$x],$asunto,$cuerpo,$remitente))
		{
			$consulta="insert into 228_circularesEnviadas(destinatario,asunto,cuerpo,fechaEnvio,idUsuarioResponsable) 
							values('".cv($arrDestinatario[$x])."','".cv($asunto)."','".cv($cuerpo)."','".date('Y-m-d')."',".$_SESSION["idUsr"].")";
			if(!$con->ejecutarConsulta($consulta))
				return false;
		}
	}
	
	
	return true;
}

function enviarMailProceso($parametros2=null,$remitente="")
{
	global $con;
	$mostrarXML=false;
	if($parametros2==null)
		$parametros=$_POST["parametros"];
	else
		$parametros=$parametros2;
	
	$objParametro=json_decode($parametros);
	
	$arrParametros=$objParametro->arrParametros;
	$cadDest=$objParametro->destinatarios;
	$numParam=sizeof($arrParametros);
	$idCircular=$objParametro->idCircular;
	$consulta="select asunto,cuerpo from 9020_mensajesAccionProceso where idIdioma=1 and idGrupoMensaje=".$idCircular;
	
	$fila=$con->obtenerPrimeraFila($consulta);
	$asunto=$fila[0];
	$cuerpo=$fila[1];
	
	for($x=0;$x<$numParam;$x++)
		$cuerpo=str_replace($arrParametros[$x][0],$arrParametros[$x][1],$cuerpo);

	$arrDestinatario=explode(',',$cadDest);
	$numDest=sizeof($arrDestinatario);
	for($x=0;$x<$numDest;$x++)
	{
		if(!enviarMail($arrDestinatario[$x],$asunto,$cuerpo,$remitente))
		{
			return false;
			/*$consulta="insert into 228_circularesEnviadas(destinatario,asunto,cuerpo,fechaEnvio,idUsuario) 
							values('".cv($arrDestinatario[$x])."','".cv($asunto)."','".cv($cuerpo)."','".date('Y-m-d')."',".$_SESSION["idUsr"].")";
			if(!$con->ejecutarConsulta($consulta))
				return false;*/
		}
	}
	
	
	return true;
}


function enviarMail($destinatario,$asunto,$mensaje,$emisor="",$nombreEmisor="",$arrArchivos=null,$arrCopia=null,$arrCopiaOculta=null)
{
	global $habilitarEnvioCorreo;
	global $mailAdministrador;
	global $registroBitacoraMail;
	global $con;
	global $SO;
	if($destinatario=="")
		return false;
	if(!$habilitarEnvioCorreo)
		return true;
	$em=$mailAdministrador;
	if($emisor!="")
		$em=$emisor;
	$nomEmisor=$nombreEmisor;
	$mail = new PHPMailer();
	$mail->IsSMTP();                                      // set mailer to use SMTP
	$mail->Host = "localhost";  // specify main and backup server
	$mail->SMTPAuth = false;     // turn on SMTP authentication
	/*$mail->Username = "jswan";  // SMTP username
	$mail->Password = "secret"; // SMTP password*/
	$mail->From = $em;
	if($nombreEmisor!="")
		$mail->FromName=$nombreEmisor;
	$mail->AddAddress($destinatario);
	$mail->AddReplyTo($em, $nomEmisor);
	
	$mail->WordWrap = 70; 
	
	if($arrCopia!=null)
	{ 
		if(sizeof($arrCopia)>0)
		{
			
			foreach($arrCopia as $c)
				$mail->AddCC($c[0],$c[1]);
		}
	}
	if($arrCopiaOculta!=null)
	{
		if(sizeof($arrCopiaOculta)>0)
		{
			foreach($arrCopiaOculta as $c)
				$mail->AddBCC($c[0],$c[1]);
		}
	}
	if($arrArchivos!=null)
	{
		$nArchivos=sizeof($arrArchivos);
		for($x=0;$x<$nArchivos;$x++)
		{
			
			$mail->AddAttachment($arrArchivos[$x][0],$arrArchivos[$x][1]);         
		}
	}
	$mail->IsHTML(true); 
                                 
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
	$resultado=$mail->Send();
	$resultado=true;
	$situacion=0;
	if($resultado)
		$situacion=1;
	$idUsuarioDestino=-1;
	

	
	if(isset($registroBitacoraMail)&&($registroBitacoraMail)&&(isset($_SESSION["idUsr"])))
	{

		$consulta="select idUsuario from 805_mails where Mail='".$destinatario."'";
		$idUsuarioDestino=$con->obtenerValor($consulta);
		if($idUsuarioDestino=="")
			$idUsuarioDestino=-1;
		$consulta="insert into 228_circularesEnviadas(destinatario,asunto,cuerpo,fechaEnvio,idUsuarioResponsable,situacion,idUsuarioDestino) 
								values('".cv($destinatario)."','".cv($asunto)."','".cv($mensaje)."','".date('Y-m-d H:i')."',".$_SESSION["idUsr"].",".$situacion.",".$idUsuarioDestino.")";

		$con->ejecutarConsulta($consulta);
	}
	return $resultado;
	
} 


function enviarMailMultiplesDestinatarios($destinatarios,$asunto,$mensaje,$emisor="",$nombreEmisor="",$arrArchivos=null,$arrCopiaOculta=null)
{
	global $habilitarEnvioCorreo;
	global $mailAdministrador;
	global $nombreEmisorAdministrador;
	global $SO;
	
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
		$mail->AddCC($em);
		$nEmisor=$nombreEmisor;
	}
	
	$mail->IsSMTP();                                      // set mailer to use SMTP
	$mail->Host = "localhost";  // specify main and backup server
	$mail->SMTPAuth = false;     // turn on SMTP authentication
	/*$mail->Username = "jswan";  // SMTP username
	$mail->Password = "secret"; // SMTP password*/
	$mail->From = $em;
	if($nombreEmisor!="")
		$mail->FromName=$nombreEmisor;
	foreach($destinatarios as $destinatario)
	{
		
		if($destinatario[0]!="")
			$mail->AddAddress($destinatario[0],$destinatario[1]);
	}
	$mail->AddReplyTo($em, $nEmisor);
	
	$mail->WordWrap = 70;  
	if(sizeof($arrCopiaOculta)>0)
	{
		foreach($arrCopiaOculta as $c)
			$mail->AddBCC($c[0],$c[1]);
	}
	if($arrArchivos!=null)
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

function generarPassword()  
{  
	$longitud = 8; // longitud del password  
	$pass = substr(md5(rand()),0,$longitud);  
	return($pass); // devuelve el password   
}  

function obtenerTituloRol($rol)
{
	global $con;
	if(trim($rol)=="")
		return "";
	$arrRol=explode("_",$rol);
	if($arrRol[0]=="")
		return "";
	$consulta="select nombreGrupo,extensionRol from 8001_roles where idRol=".$arrRol[0];

	$filaExt=$con->obtenerPrimeraFila($consulta);
	if(!$filaExt)
		return "";
	$rol1=$filaExt[0];
	$extension=$filaExt[1];
	$nombreRol="";
	if($rol1!="")
	{
		$rol2="";
		if($extension!="0")
		{
			if($arrRol[1]=="-1")
				$rol2=" (Todos)";
			else
			{
				if($extension!="")
				{
					$consulta="select * from 4085_categoriasRol where idCategoria=".$extension;
					$filaCat=$con->obtenerPrimeraFila($consulta);
					if($filaCat)
					{
						if($filaCat[8]=="1")
						{
							$consulta="select unidadRol from 4084_unidadesRoles where idUnidadesRoles=".$arrRol[1];
							$rol2=" (".$con->obtenerValor($consulta).")";
						}
						else
						{
							$consulta="select ".$filaCat[11]." from ".$filaCat[9]." where ".$filaCat[10]."='".$arrRol[1]."'";
							$rol2=" (".$con->obtenerValor($consulta).")";
						}
					}
				}
				else
					$rol2="";
			}
		}
		$nombreRol=$rol1.$rol2;
	}
	return $nombreRol; 
}

function obtenerParametrosBase64($parametros)
{
	$arrParam=array();
	$param=base64_decode($parametros);
	
	$arrP=explode("&",$param);
	$nParam=sizeof($arrP);
	for($x=0;$x<$nParam;$x++)
	{
		$objP=explode("=",$arrP[$x]);
		$arrParam[$objP[0]]=$objP[1];
	}
	
	return $arrParam;
}

function restaHoras($horaIni, $horaFin)
{
	
    return  strtotime("00:00:00") + strtotime($horaFin) - strtotime($horaIni);
}



function colisionaTiempo($tiempoI1,$tiempoF1,$tiempoI2,$tiempoF2,$cosiderarLimites=false)
{

	$tInicio1=strtotime($tiempoI1);
	$tFin1=strtotime($tiempoF1);
	$tInicio2=strtotime($tiempoI2);
	$tFin2=strtotime($tiempoF2);	
	if(!$cosiderarLimites)
	{
		if(($tInicio1>=$tInicio2)&&($tInicio1<$tFin2))
			return true;
		else
			if(($tFin1>$tInicio2)&&($tFin1<=$tFin2))
				return true;
		
		if(($tInicio2>=$tInicio1)&&($tInicio2<$tFin1))
			return true;
		else
			if(($tFin2>$tInicio1)&&($tFin2<=$tFin1))
				return true;
	}
	else
	{
		if(($tInicio1>=$tInicio2)&&($tInicio1<=$tFin2))
			return true;
		else
			if(($tFin1>=$tInicio2)&&($tFin1<=$tFin2))
				return true;
		
		if(($tInicio2>=$tInicio1)&&($tInicio2<=$tFin1))
			return true;
		else
			if(($tFin2>=$tInicio1)&&($tFin2<=$tFin1))
				return true;
	}
	return false;
}

function generarCheckBoxMatriz($matriz,$valoresSel,$nombre,$id="chk")
{
	$nCheck=sizeof($matriz);
	$arrCheck=array();
	
	for($x=0;$x<$nCheck;$x++)
	{
		$checado='';
		if(existeValor($valoresSel,$matriz[$x][0]))
			$checado='checked="checked"';
		$check='<input type="checkbox" id="'.$id.'_'.$matriz[$x][0].'" value="'.$matriz[$x][0].'" name="'.$nombre.'" '.$checado.' >&nbsp;<label>'.$matriz[$x][1]."</label>";
		$arrCheck[$x]=$check;
	}
	return $arrCheck;
}

function obtenerNombreUsuario($idUsuario,$prefijo=false)
{
	global $con;
	$consulta="";
	
	$consulta="SELECT nombre FROM usuarios WHERE idUsuario=".$idUsuario;
	$usuario=$con->obtenerValor($consulta);
	
	return $usuario;
}

function obtenerNombreUsuarioCompleto($idUsuario)
{
	global $con;
	$consulta="";
	
	$consulta="SELECT CONCAT(nombre,' ',apPaterno,' ',apMaterno) AS nombre FROM usuarios WHERE idUsuario=".$idUsuario;
	$usuario=$con->obtenerValor($consulta);
	
	return $usuario;
}

function obtenerNombreAreas($idArea)
{
	global $con;
	$consulta="";
	
	$consulta="SELECT nombreArea FROM 11_cat_areas WHERE idArea=".$idArea;
	$resp=$con->obtenerValor($consulta);
	
	return $resp;
}

function obtenerAfiliacion($idUsuario)
{
	global $con;
	$consulta="select Institucion,codigoUnidad from 801_adscripcion where idUsuario=".$idUsuario;

	$filaAds=$con->obtenerPrimeraFila($consulta);
	$datosFinales="";
	if($filaAds[0]!="")
	{
		$consulta="select idOrganigrama,unidad from 817_organigrama where codigoUnidad='".$filaAds[0]."'";
		
		$filaInst=$con->obtenerPrimeraFila($consulta);
		$institucion=$filaInst[1];
		$idInstitucion=$filaInst[0];
		if($idInstitucion=="")
			$idInstitucion="-1";
		$consulta="select unidad from 817_organigrama where codigoUnidad='".$filaAds[1]."'";
		$depto=$con->obtenerValor($consulta);
		$consulta="select i.ciudad,i.estado, p.nombre,i.cp,i.municipio,i.email from 247_instituciones i,238_paises p where p.idPais=i.idPais and i.idOrganigrama=".$idInstitucion;
		$datosInst=$con->obtenerPrimeraFila($consulta);
		$datos="";
		if($datosInst[0]!="")
		{
			$consulta="select localidad FROM 822_localidades WHERE cveLocalidad='".$datosInst[0]."'";
			$localidad=$con->obtenerValor($consulta);
			if($localidad!="")
				$datos=$localidad;
			else
				$datos=$datosInst[0];
		}
		if($datosInst[4]!="")
		{
			
			$particula="";
			$consulta="select municipio FROM 821_municipios WHERE cveMunicipio='".$datosInst[4]."'";
			$mpio=$con->obtenerValor($consulta);
			if($mpio!="")
				$particula=$mpio;
			else
				$particula=$datosInst[4];
			
			if($datos=="")
				$datos=$particula;
			else
				$datos.=", ".$particula;
		}
		
		if($datosInst[1]!="")
		{
			
			$particula="";
			$consulta="select estado FROM 820_estados WHERE cveEstado='".$datosInst[1]."'";
			$estado=$con->obtenerValor($consulta);
			if($estado!="")
				$particula=$estado;
			else
				$particula=$datosInst[1];
			
			if($datos=="")
				$datos=$particula;
			else
				$datos.=", ".$particula;
		}
		if($datosInst[2]!="")
		{
			$particula=$datosInst[2];
			if($datos=="")
				$datos=$particula;
			else
				$datos.=", ".$particula;
			
		}
		
		if($datosInst[3]!="")
		{
			if($datos=="")
				$datos="CP. ".$datosInst[3];
			else
				$datos.=". CP. ".$datosInst[3];
			
		}
		
		if($datosInst[5]!="")
		{
			if($datos=="")
				$datos="<br><b>Email:</b> ".$datosInst[5];
			else
				$datos.="<br><b>Email:</b> ".$datosInst[5];
			
		}
		
		$datosInst=$institucion.". ".$datos;
		if(($depto!="")&&($depto!=$institucion))
		{
			if($datosInst!="")
				$datosFinales=$depto.", ".$datosInst;
			else
				$datosFinales=$depto;
		}
		else
			$datosFinales=$datosInst;
	}
	
	return $datosFinales;
}

function guardarBitacoraAdmon($tipo,$pagina,$idUsuario,$param)
{
	global $con;
	$fechaActual=date("Y-m-d");
	$horaActual=date("H:i:s");

	$consulta="INSERT INTO 8000_logSistema(fecha,hora,idUsuario,pagina,tipo,consultaSql)VALUES('".$fechaActual."','".$horaActual."',
			'".$idUsuario."','".$pagina."','".$tipo."','".$param."')";
	$con->ejecutarConsultaLog($consulta);
}

function guardarBitacoraAccesoPagina($rutaNomPagina,$parametros)
{
	global $con;
	$consulta="insert into 8000_logSistema(fecha,hora,idUsuario,pagina,parametros,tipo,dirIP) values ('".date('Y-m-d')."','".date("H:i:s")."',".$_SESSION["idUsr"].",'".$rutaNomPagina."','".cv($parametros)."',0,'".obtenerIP()."')";
	$con->ejecutarConsultaLog($consulta);
}

function guardarBitacoraConsultaBD($consulta)
{
	global $logSistemaConsultaBD;
	global $con;
	if(($logSistemaConsultaBD)&&(isset($_SESSION["idUsr"])))
	{
		$consulta="insert into 8000_logSistema(fecha,hora,idUsuario,consultaSql,tipo,dirIP) values ('".date('Y-m-d')."','".date("H:i:s")."',".$_SESSION["idUsr"].",'".cv($consulta)."',1,'".obtenerIP()."')";
		$con->ejecutarConsultaLog($consulta);
	}
}

function guardarBitacoraModificacionBD($consulta)
{
	global $logSistemaModificacionBD;
	global $con;
	if(($logSistemaModificacionBD)&&(isset($_SESSION["idUsr"])))
	{
		$consulta="insert into 8000_logSistema(fecha,hora,idUsuario,consultaSql,tipo,dirIP) values ('".date('Y-m-d')."','".date("H:i:s")."',".$_SESSION["idUsr"].",'".cv($consulta)."',2,'".obtenerIP()."')";
		$con->ejecutarConsultaLog($consulta);
	}
}

function guardarBitacoraInicioSesion()
{
	global $logInicioSesion;
	global $con;
	if($logInicioSesion)
	{
		$consulta="insert into 8000_logSistema(fecha,hora,idUsuario,pagina,tipo,dirIP) values ('".date('Y-m-d')."','".date("H:i:s")."',".$_SESSION["idUsr"].",'./',3,'".obtenerIP()."')";
		$con->ejecutarConsultaLog($consulta);
	}
}

function guardarBitacoraInicioSesionFallida($login)
{
	global $logInicioSesion;
	global $con;
	if($logInicioSesion)
	{
		$consulta="insert into 8000_logSistema(fecha,hora,pagina,parametros,tipo,dirIP) values ('".date('Y-m-d')."','".date("H:i:s")."','./','".$login."',5,'".obtenerIP()."')";
		$con->ejecutarConsultaLog($consulta);
	}
}

function guardarBitacoraFinSesion()
{
	global $logFinSesion;
	global $con;
	if($logFinSesion)
	{
		$consulta="insert into 8000_logSistema(fecha,hora,idUsuario,pagina,parametros,tipo,dirIP) values ('".date('Y-m-d')."','".date("H:i:s")."',".$_SESSION["idUsr"].",'./','".$_SESSION["login"]."',4,'".obtenerIP()."')";
		$con->ejecutarConsultaLog($consulta);
	}
}

function obtenerIP()
{
   	if ( isset($_SERVER['HTTP_X_FORWARDED_FOR']) != '' )
    {

        $client_ip =
            ( !empty($_SERVER['REMOTE_ADDR']) ) ?
                $_SERVER['REMOTE_ADDR']
                :
                ( ( !empty($_ENV['REMOTE_ADDR']) ) ?
                    $_ENV['REMOTE_ADDR']
                    :
                    "unknown" );
    
        // los proxys van a�adiendo al final de esta cabecera
        // las direcciones ip que van "ocultando". Para localizar la ip real
        // del usuario se comienza a mirar por el principio hasta encontrar
        // una direcci�n ip que no sea del rango privado. En caso de no
        // encontrarse ninguna se toma como valor el REMOTE_ADDR
    
        $entries = explode('[, ]', $_SERVER['HTTP_X_FORWARDED_FOR']);
    
        reset($entries);
        while (list(, $entry) = each($entries))
        {
            $entry = trim($entry);
            if ( preg_match("/^([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/", $entry, $ip_list) )
            {
                // http://www.faqs.org/rfcs/rfc1918.html
                $private_ip = array(
                        '/^0\./',
                        '/^127\.0\.0\.1/',
                        '/^192\.168\..*/',
                        '/^172\.((1[6-9])|(2[0-9])|(3[0-1]))\..*/',
                        '/^10\..*/');
    
                $found_ip = preg_replace($private_ip, $client_ip, $ip_list[1]);
    
                if ($client_ip != $found_ip)
                {
                    $client_ip = $found_ip;
                    break;
                }
            }
        }
    }
    else
    {
		
        $client_ip =
            ( !empty($_SERVER['REMOTE_ADDR']) ) ?
                $_SERVER['REMOTE_ADDR']
                :
                ( ( !empty($_ENV['REMOTE_ADDR']) ) ?
                    $_ENV['REMOTE_ADDR']
                    :
                    "unknown" );
    }
    
    return $client_ip;
}

function obtenerMAC()
{
   	
	// direcci�n IP
	$ip=getenv("REMOTE_ADDR");
	// direcci�n mac
	echo"IP: $ip<br>MAC: ";
	$cmd = "arp $ip | grep $ip | awk '{ print $3 }'";
	system($cmd);

}

function generarCadenaRepetible($caracter,$numRepeticiones)
{
	$cadena="";
	for($x=0;$x<$numRepeticiones;$x++)
	{
		$cadena.=$caracter;
	}
	return $cadena;
}

function compararArreglos($arreglo1,$arreglo2)
{
	$nArreglo1=sizeof($arreglo1);
	$nArreglo2=sizeof($arreglo2);
	if($nArreglo1!=$nArreglo2)
		return false;
	for($x=0;$x<$nArreglo1;$x++)
	{
		if($arreglo1[$x]!=$arreglo2[$x])
			return false;
	}
	return true;
}

function normalizarNumero($valor)
{
	$cadAux="";
	$lCadena=strlen($valor);
	for($x=0;$x<$lCadena;$x++)
	{
		if((($valor[$x]>='0')&&($valor[$x]<='9'))||($valor[$x]=='.')||($valor[$x]=="-"))
			$cadAux.=$valor[$x];
	}
	return $cadAux;
}

function generarCadenaConsultasFiltro($filter)
{
	global $con;
	$consulta="SELECT campoUsr,campoMysql FROM 9017_camposControlFormulario";
	$arrCampos=$con->obtenerFilasArregloAsocPHP($consulta);

	$nFiltros=sizeof($filter);
	$condWhere=" 1=1";
	for($x=0;$x<$nFiltros;$x++)
	{
		$f=$filter[$x];
		$campo=$f["field"];
		
		if(isset($arrCampos[$campo]))
			$campo=$arrCampos[$campo];
		
		$condicion=" like ";
		if(isset($f["data"]["comparison"]))
		{
			switch($f["data"]["comparison"])
			{
				case "gt":
					$condicion=">";
				break;
				case "lt":
					$condicion="<";
				break;
				case "eq":
					$condicion="=";
				break;
			}
		}
		
		$valor="";
		switch($f["data"]["type"])
		{
			case "numeric":
				$valor=$f["data"]["value"];
			break;
			case "date":
				$fecha=$f["data"]["value"];
				$arrFecha=explode('/',$fecha);
				$valor="'".$arrFecha[2]."-".$arrFecha[1]."-".$arrFecha[0]."'";
			break;
			case "list":
				$condicion=" in ";
				$arrValores=explode(',',$f["data"]["value"]);
				$nCt=sizeof($arrValores);
				for($xAux=0;$xAux<$nCt;$xAux++)
				{
					if($valor=='')
						$valor=$arrValores[$xAux];
					else
						$valor.=",".$arrValores[$xAux];
				}
				
				
				$valor="(".$valor.")";
			break;
			default:
				$valor="'".$f["data"]["value"]."%'";
			break;
		}
		
		$condWhere.=" and ".$campo.$condicion.$valor;
	}
	return $condWhere;
	
}

function enviarPagina($pagina,$arrParametros=null,$metodo="POST")
{
	?>
		<form method="<?php echo $metodo?>" action="<?php echo $pagina?>" id="frmEnviaPagina">
	<?php
		$nParametros=sizeof($arrParametros);
		for($x=0;$x<$nParametros;$x++)
		{
			echo "<input type='hidden' name='".$arrParametros[$x][0]."' value=\"".$arrParametros[$x][1]."\">";
		}
	?>
		</form>
		<script type="text/javascript">
			document.getElementById('frmEnviaPagina').submit();
		</script>
	<?php	
}

function bE($valor)
{
	return base64_encode($valor);
}

function bD($valor)
{
	return base64_decode($valor);
}

function obtenerDiferenciaDias($fechaI,$fechaF)
{
	$fecha1=strtotime($fechaI);
	$fecha2=strtotime($fechaF);
	$ct=0;
	while($fecha1<$fecha2)
	{
		$fecha1=strtotime("+1 days",$fecha1);
		$ct++;
	}
	return $ct;
}

function obtenerDiferenciaMeses($fechaF,$fechaI)
{
	
	$fecha1=strtotime($fechaF);
	$fecha2=strtotime($fechaI);
	$ct=0;
	while($fecha2<$fecha1)
	{
		$fecha2=strtotime("+1 month",$fecha2);
		$ct++;
	}
	if($fecha1!=$fecha2)
		$ct--;
	return $ct;

	
}

function obtenerDiferenciaSemanas($fechaF,$fechaI)
{
	
	$fecha1=strtotime($fechaF);
	$fecha2=strtotime($fechaI);
	$diferencia=$fecha1-$fecha2;
	return (int)($diferencia/(60*60*24*7));
	
}

function obtenerDiferenciaAnios($fechaF,$fechaI)
{
	$fecha1=strtotime($fechaF);
	$fecha2=strtotime($fechaI);
	$diferencia=$fecha1-$fecha2;
	return (int )($diferencia/(60*60*24*365));
}

function obtenerCodigoInstitucion($codUnidad)
{
	return substr($codUnidad,0,4);
}

function compararCadenas($a, $b)
{
	
	return strcasecmp($a, $b);
}

function cambiarValorObjParametros($nConf,$param,$valor)
{
	if(!isset($_SESSION["configuracionesPag"][$nConf]))
	{
		return false;
	}
	
	$cadObj=$_SESSION["configuracionesPag"][$nConf]["parametros"];
	$obj=json_decode($cadObj);
	$existe=false;
	eval('$existe=isset($obj->'.$param.");");
	if($existe)
	{
		eval('$vValor=$obj->'.$param.";");
		$cadObj=str_replace('"'.$param.'":"'.$vValor.'"','"'.$param.'":"'.$valor.'"',$cadObj);
	}
	else
	{
		$cadAux=substr($cadObj,0,strlen($cadObj)-1);
		$cadAux.=',"'.$param.'":"'.$valor.'"}';
		$cadObj=$cadAux;
	}
	
	$_SESSION["configuracionesPag"][$nConf]["parametros"]=$cadObj;
	return true;
	
}

function eC($consulta)
{
	global $con;
	if($con->ejecutarConsulta($consulta))
		echo "1|";
	else
		echo "|";
}

function eB($consulta)
{
	global $con;
	if($con->ejecutarBloque($consulta))
		echo "1|";
	else
		echo "|";
}

function removerCerosDerecha($valor)
{
	$cad=$valor."";

	if(strpos($valor,".")===false)
		return $valor;
	$tamCad=strlen($cad);
	$x=0;
	for($x=$tamCad-1;$x>=0;$x--)
	{
		if($cad[$x]!="0")
		{
			if($cad[$x]==".")
				$x--;
			break;
		}
	}
	$nCadena=substr($cad,0,$x+1);
	
	return $nCadena;
}

function esHorarioAsignable($idPerfil,$idGrupo,$dia,$hInicioMateria,$hFinMateria,$tipoMateria,$alinearBloqueMayor=true)
{
	global $con;
	$arrHorario=array();
	$consulta="select horaInicio from 4060_perfilHorarios where idPerfil=".$idPerfil;
	$hInicio=date("H:i",strtotime($con->obtenerValor($consulta)));
	generarHorarioDiaPerfil($idPerfil,$dia,$hInicio,"-1",$arrHorario);
	
	$arrHorarioGrupo=array();
	$x=0;
	$hIBloque;
	$hFBloque;
	$hIMateria=strtotime($hInicioMateria);
	$hFMateria=strtotime($hFinMateria);
	$catBloque;
	$idBloque;
	foreach($arrHorario as $horario)
	{
		$hIBloque=strtotime($horario[0]);
		$hFBloque=strtotime($horario[1]);
		$catBloque=$horario[2];
		$idBloque=$horario[4];
		if(($hIBloque)>=($hIMateria))
			break;
		$x++;
	}
	$resultado="";
	$horarioBloques="[]";
	$mensajeError="";
	$arrHorariosAfectados=array();
	if($x<sizeof($arrHorario))
	{
		if(($hIBloque==$hIMateria)&&($catBloque==1))
		{
			if($hFBloque==$hFMateria)
			{
				$objHorarioVal[0]=date("H:i",$hIBloque);
				$objHorarioVal[1]=date("H:i",$hFBloque);
				$objHorarioVal[2]=$idBloque;
				//
				$resMsg=validarAsignacionMateria($tipoMateria,$dia,$objHorarioVal[0],$objHorarioVal[1],$idGrupo,$objHorarioVal[2]);
				if($resMsg=="")
				{
					array_push($arrHorariosAfectados,$objHorarioVal);
					$horarioBloques=json_encode($arrHorariosAfectados);
					$resultado="true";			
				}
				else
				{
					$resultado="false";
					$mensajeError=$resMsg;		
				}
			}
			else
			{
	
				if($hFBloque>$hFMateria)
				{
					if($alinearBloqueMayor)
					{
						$objHorarioVal[0]=date("H:i",$hIBloque);
						$objHorarioVal[1]=date("H:i",$hFBloque);
						$objHorarioVal[2]=$idBloque;
						$resMsg=validarAsignacionMateria($tipoMateria,$dia,$objHorarioVal[0],$objHorarioVal[1],$idGrupo,$objHorarioVal[2]);
						if($resMsg=="")
						{
							array_push($arrHorariosAfectados,$objHorarioVal);
							$horarioBloques=json_encode($arrHorariosAfectados);
							$resultado="true";			
							$mensajeError="El bloque o conjunto de bloques bajo el cual podr&iacute;a la materia ser alineada es m&aacute;s grande que lo requerido, no obstante &eacute;sta puede ser alineada";
						}
						else
						{
							$resultado="false";
							$mensajeError=$resMsg;		
						}
					}
					else
					{
						$resultado="false";
						$mensajeError="El bloque o conjunto de bloques bajo el cual podr&iacute;a la materia ser alineada, no tienen una hora de t&eacute;mino compatible con la materia";
					}
				}	
				else
				{
					$objHorarioVal[0]=date("H:i",$hIBloque);
					$objHorarioVal[1]=date("H:i",$hFBloque);
					$objHorarioVal[2]=$idBloque;
					$resMsg=validarAsignacionMateria($tipoMateria,$dia,$objHorarioVal[0],$objHorarioVal[1],$idGrupo,$objHorarioVal[2]);
					if($resMsg=="")
					{
						array_push($arrHorariosAfectados,$objHorarioVal);
						$objHorario=esHorarioAsignable($idPerfil,$idGrupo,$dia,date("H:i",$hFBloque),$hFinMateria,$tipoMateria,$alinearBloqueMayor);
						if($objHorario->resultado)
						{
							foreach($objHorario->horarioBloques as $hAfectado)
							{
								$objHorarioVal[0]=$hAfectado[0];
								$objHorarioVal[1]=$hAfectado[1];
								$objHorarioVal[2]=$hAfectado[2];
								array_push($arrHorariosAfectados,$objHorarioVal);
							}
							$horarioBloques=json_encode($arrHorariosAfectados);
							$mensajeError=$objHorario->mensajeError;
							$resultado="true";
						}
						else
						{
							$mensajeError=$objHorario->mensajeError;
							$resultado="false";
						}
					}
					else
					{
						$resultado="false";
						$mensajeError=$resMsg;		
					}
					
				}		
			}
		}
		else
		{
			if($catBloque=="0")
				$mensajeError="El bloque o conjunto de bloques bajo el cual podr&iacute;a la materia ser alineada, est&aacute; configurado como un tipo de bloque que no permite la asignaci&oacute;n de materias";
			else
				$mensajeError="No existe un bloque o conjunto de bloques que coincida con el horario requerido por la materia";
			$resultado="false";
		}
	}
	else
	{
		$mensajeError="No existe un bloque o conjunto de bloques que coincida con el horario requerido por la materia";
		$resultado="false";
	}
	
	$cadObj='	{
					"resultado":'.$resultado.',
					"horarioBloques":'.$horarioBloques.',
					"mensajeError":"'.$mensajeError.'"
				}';
	$obj=json_decode($cadObj);
	return $obj;
}

function validarAsignacionMateria($tipoMateria,$dia,$hInicio,$hFin,$idGrupo,$idBloque)
{
	global $con;
	global $arrDiasSemana;
	$consulta="SELECT idMateria,idPrograma,ciclo,tipoMateriaVirtual FROM 4065_materiaVSGrupo WHERE idBloque=".$idBloque." and horaInicio='".$hInicio."' 
			AND horaFin='".$hFin."'	AND idGrupo=".$idGrupo;	
	
	$resMaterias=$con->obtenerFilas($consulta);
	if($con->filasAfectadas==0)
		return "";
	if($tipoMateria==1)
	{
		return "La materia que desea alinear es de tipo Obligatoria, &eacute;sto impide que pueda compartir horario con otras materias. El bloque del d&iacute;a ".$arrDiasSemana[$dia]." con horario de: ".$hInicio."-".$hFin." ya cuenta con al menos una materia asignada";
	}
	else
	{
		while($filaMateria=mysql_fetch_row($resMaterias))
		{
			
			$tMateria=$filaMateria[3];
			if($tMateria==1)
			{
				$consulta="select titulo FROM 4013_materia WHERE idMateria=".$filaMateria[0];
				$nMateria=$con->obtenerValor($consulta);
				return " El bloque del d&iacute;a ".$arrDiasSemana[$dia]." con horario de: ".$hInicio."-".$hFin." ya cuenta con una materia de tipo Obligatoria(".$nMateria."), la cual no puede compartir horario con otra materia";
			}
		}
		return "";
	}
	
}

function obtenerMapaCurricularHerenciaMateria($idMateria,$idGrado)
{
	global $con;
	$consulta="SELECT idMateria FROM 4031_elementosMapa WHERE idGrado=".$idGrado." and  idTipoComponente IN (1,3)";
	//echo $consulta;
	$resMateria=$con->obtenerFilas($consulta);
	$arrMapaMateria=array();
	while($filaMateria=mysql_fetch_row($resMateria))
	{
		
		if(buscarMateria($filaMateria[0],$idMateria,$arrMapaMateria))
		{
			return $arrMapaMateria[0];
		}
	}
	return "";
	
}

function buscarMateria($idMateriaPadre,$idMateriaBusqueda,&$arrMapaMateria)
{
	global $con;
	global $rec;
	$consulta="select idPrograma,ciclo from 4013_materia where idMateria=".$idMateriaPadre;
	$filaMat=$con->obtenerPrimeraFila($consulta);
	$idMapa=obtenerMapaCurricular($filaMat[0],$filaMat[1]);
	$consulta="select idMateria,perteneceMapa,idTipoComponente from 4031_elementosMapa where idPadre=".$idMateriaPadre;
	//echo $consulta;
	$resMatHijas=$con->obtenerFilas($consulta);
	while($filaMatHija=mysql_fetch_row($resMatHijas))
	{
		
		if($filaMatHija[0]==$idMateriaBusqueda)
		{
			array_push($arrMapaMateria,$idMapa);
			return true;
		}
		else
		{
			//echo "entra<br>";
			if(($filaMatHija[2]==1)||($filaMatHija[2]==3))
			{
				$resBusqueda=buscarMateria($filaMatHija[0],$idMateriaBusqueda,$arrMapaMateria);
				if($resBusqueda)
				{
					array_push($arrMapaMateria,$idMapa);
					return $resBusqueda;
				}
			}
		}
	}
	return false;
}

function obtenerTipoDatoElementoFormulario($tipoElemento)
{
	switch($tipoElemento)
	{
		case 2:
		case 3:
		case 4:
		case 5:
			return 'varchar';
		break;
		case 6:
		case 7:
			return 'int';
		break;
		case 8:
			return 'date';
		break;
		case 9:
		case 10:
		case 11:
			return 'varchar';
		break;	
		case 12:
			return 'file';
		break;
		case 14:
		case 15:
		case 16:
		case 17:
		case 18:
		case 19:
			return 'varchar';
		break;
		case 20:
		case 21:
			return 'time';
		break;
		case 22:
			return 'float';
		break;
		case 23:
			return 'image';
		break;
		case 24:
			return 'float';
		break;
	}
}

function removerCampo($cadena,$valor,$separador=",")
{
	global $con;	
	$cadAux="";
	$arrCadena=explode($separador,$cadena);
	foreach($arrCadena as $token)
	{
		if($token!=$valor)
		{
			if($cadAux=="")
				$cadAux=$token	;
			else
				$cadAux.=$separador.$token;
		}
		
	}
	return $cadAux;
}

function generarObjetoUsuarioMail($listUsuario)
{
	$cadUsuario="";
	if(is_array($listUsuario))
	{
		foreach($listUsuario as $usuario)
		{
			$obj='{"tipo":"0","idUsuario":"'.$usuario.'","ciclo":"-1","idPrograma":"-1","idGrado":"-1","idMateria":"-1","idGrupo":"-1"}';
			if($cadUsuario=="")
				$cadUsuario=$obj;
			else
				$cadUsuario.=",".$obj;
		}
	}
	else
	{
		if(is_string($listUsuario))
		{
			$arrUsuarios=explode(",",$listUsuario);
			foreach($arrUsuarios as $usuario)
			{
				$obj='{"tipo":"0","idUsuario":"'.$usuario.'","ciclo":"-1","idPrograma":"-1","idGrado":"-1","idMateria":"-1","idGrupo":"-1"}';
				if($cadUsuario=="")
					$cadUsuario=$obj;
				else
					$cadUsuario.=",".$obj;
			}		
		}	
	}
	return bE('{"destinatario":['.$cadUsuario.']}');
}

function comprobarReglasMateria($idUsuario,$idMateria,$idElementoMapa)
{
	global $con;
	$nombre=obtenerNombreUsuario($idUsuario);
	$conMateriaR="SELECT idMateria FROM 4190_materiaRequisitoVSElemento WHERE idElementoMapa=".$idElementoMapa." AND idTipoRequisito=1";
	$res=$con->obtenerFilas($conMateriaR);
	$nMat=$con->filasAfectadas;
	$cadenaErrores="";
	$errorCreditos="";
	$cadenaFinal="";
	while($fila=mysql_fetch_row($res))
	{
		$conAproboA="SELECT calificacion FROM 4159_calificaciones WHERE idUsuario=".$idUsuario." AND idMateria=".$fila[0]." AND aprobado=1";
		$aprobado=$con->obtenerValor($conAproboA);
		if($aprobado=="")
		{
			
			$conNombreMat="SELECT titulo FROM 4013_materia WHERE idMateria=".$fila[0];
			$nombreMat=$con->obtenerValor($conNombreMat);
			
			$obj="El alumno&nbsp;".$nombre." no ha aprobado la materia ".$nombreMat." la cual es requisito para incribirse a esta materia";
			if($cadenaErrores=="")
				$cadenaErrores=$obj;
			else
				$cadenaErrores.="<br>".$obj;
		}
	}
	
	
		$conReglasMat="SELECT noCreditosCubiertos FROM 4189_elementoMapaVSReglas WHERE idElementoMapa=".$idElementoMapa;
		$nCreditos=$con->obtenerValor($conReglasMat);
		if(($nCreditos=="")|| ($nCreditos==0))
		{
			$pasaNcreditos=true;
		}
		else	
		{
			$conNcreditos="SELECT SUM(IF(nCreditos IS NULL,0,nCreditos)) AS sumatoria FROM 4013_materia m, 4159_calificaciones c WHERE c.idMateria=m.idMateria AND idUsuario=".$idUsuario." AND aprobado=1";
			$credAprob=$con->obtenerValor($conNcreditos);
			if($credAprob=="")
				$credAprob=0;
				
			if($credAprob<$nCreditos)	
				$errorCreditos="El alumno&nbsp;".$nombre."&nbsp; no cuenta con el n&uacute;mero de cr&eacute;ditos solicitados para inscribirse a esta materia";
		}
	
	if($cadenaErrores!="")
		$cadenaFinal=$cadenaErrores;
		
	if($cadenaFinal!="")	
		$cadenaFinal.="<br>".$errorCreditos;
	else
		$cadenaFinal=$errorCreditos;
		
	if($cadenaFinal=="")
		return "1|";
	else	
		return "2|".$cadenaFinal;
	
}

function comprobarHorarioAlumno($idUsuario,$idMateria,$idGrupo)
{
	global $con;
	
	$nombreA=obtenerNombreUsuario($idUsuario);
	
	$conCiclo="SELECT ciclo FROM 4013_materia WHERE idMateria=".$idMateria;
	$idCiclo=$con->obtenerValor($conCiclo);
	
	$conHorarioMatI="SELECT p.dia,m.horaInicio,m.horaFin,m.idPrograma,p.idPerfil FROM 4062_perfilVSBloque p,4065_materiaVSGrupo m 
					WHERE  m.idBloque=idPerfilVSBloque AND idMateria=".$idMateria." AND idGrupoCompartido=".$idGrupo." and ciclo=".$idCiclo;
	$res3=$con->obtenerFilas($conHorarioMatI);
	$filas=$con->filasAfectadas;
	if($filas==0)
	{
		$conHorarioMatI="SELECT p.dia,m.horaInicio,m.horaFin,m.idPrograma,p.idPerfil FROM 4062_perfilVSBloque p,4065_materiaVSGrupo m 
					WHERE  m.idBloque=idPerfilVSBloque AND idMateria=".$idMateria." AND idGrupo=".$idGrupo." and ciclo=".$idCiclo;
		$res3=$con->obtenerFilas($conHorarioMatI);
		$filas=$con->filasAfectadas;
		
		if($filas==0)
		{
			$conHorarioMatI="SELECT dia,horaInicio,horaFin,idPrograma FROM 4065_materiaVSGrupo  
						WHERE  idMateria=".$idMateria." AND idGrupo=".$idGrupo." and ciclo=".$idCiclo;
			$res3=$con->obtenerFilas($conHorarioMatI);
			$filas=$con->filasAfectadas;
		}
	}
	
	if($filas==0)
	{
		return "1|" ;
	}
	else
	{
		$mensajeDeColision="";
		$consulta="SELECT idMateria,idGrupo FROM 4120_alumnosVSElementosMapa WHERE idUsuario=".$idUsuario." AND situacion=1 AND idGrupo IS NOT NULL";
		$res=$con->obtenerFilas($consulta);
		$numMateriasA=$con->filasAfectadas;
		if($numMateriasA==0)
		{
			return "1|";
		}
		else
		{
			  while($filaMatA=mysql_fetch_row($res))
			  {
				  $conHorarioMat="SELECT p.dia,m.horaInicio,m.horaFin,m.idPrograma,p.idPerfil FROM 4062_perfilVSBloque p,4065_materiaVSGrupo m 
						  WHERE  m.idBloque=idPerfilVSBloque AND idMateria=".$filaMatA[0]." AND idGrupoCompartido=".$filaMatA[1]." and ciclo=".$idCiclo;
				  $res1=$con->obtenerFilas($conHorarioMat);
				  $filas2=$con->filasAfectadas;
				  if($filas2==0)
				  {
					  $conHorarioMat="SELECT p.dia,m.horaInicio,m.horaFin,m.idPrograma,p.idPerfil FROM 4062_perfilVSBloque p,4065_materiaVSGrupo m 
								  WHERE  m.idBloque=idPerfilVSBloque AND idMateria=".$filaMatA[0]." AND idGrupo=".$filaMatA[1]." and ciclo=".$idCiclo;
					  $res1=$con->obtenerFilas($conHorarioMat);
					  $filas2=$con->filasAfectadas;
					  
					  if($filas==0)
					  {
						  $conHorarioMatI="SELECT dia,horaInicio,horaFin,idPrograma FROM 4065_materiaVSGrupo  
									  WHERE  idMateria=".$idMateria." AND idGrupo=".$idGrupo." and ciclo=".$idCiclo;
						  $res3=$con->obtenerFilas($conHorarioMatI);
						  $filas=$con->filasAfectadas;
					  }
				  }
			  
				  while($filaHmatA=mysql_fetch_row($res1))	
				  {
						mysql_data_seek($res3,0);
						while($filaHmat=mysql_fetch_row($res3))
						{
							if($filaHmatA[0]==$filaHmat[0])
							{
								if(colisionaTiempo($filaHmatA[1],$filaHmatA[2],$filaHmat[1],$filaHmat[2]))
								{
									$nombreMat="SELECT titulo FROM 4013_materia WHERE idMateria=".$filaMatA[0];
									$nombre=$con->obtenerValor($nombreMat);
									
									switch($filaHmat[0])
									{
									  case "1":
										  $dia="Lunes";
									  break;
									  case "2":
										  $dia="Martes";
									  break;
									  case "3":
										  $dia="Miercoles";
									  break;
									  case "4":
										  $dia="Jueves";
									  break;
									  case "5":
										 $dia="Viernes";
									  break;
									  case "6":
										  $dia="Sabado";
									  break;
									  case "7":
										 $dia="Domingo";
									}
	  
									$colision="Tiene asignada la materia:&nbsp;<b>".$nombre."</b>&nbsp;el dia&nbsp;<b>".$dia."</b>&nbsp;de&nbsp;<b>".$filaHmat[1]."</b>&nbsp;a&nbsp;<b>".$filaHmat[2]."</b><br/>";
									//echo"2|".$dia."|".$filaHmat[1]."|".$filaHmat[2]."|".$nombre;
									//return ;	
									if($mensajeDeColision=="")
									  $mensajeDeColision=$colision;
								   else
									  $mensajeDeColision.=$colision;
								}
							}
						}
				  }
			  }
			 if($mensajeDeColision=="")	
				return "1|";
			 else
				return "2|".$mensajeDeColision;
		}
	}
}

function obtenerCandidatosArmarGrupos($idMateria,$idMapaCurricular)
{
	global $con;
	$conCandidatos="SELECT idUsuario FROM 4120_alumnosVSElementosMapa WHERE idMateria=".$idMateria." AND situacion=1";
	$resCandidatos=$con->obtenerFilas($conCandidatos);
	
	$cadenaCandidatos="";
	while($filaC=mysql_fetch_row($resCandidatos))
	{
		if($cadenaCandidatos=="")
			$cadenaCandidatos=$filaC[0];
		else
			$cadenaCandidatos.=",".$filaC[0];
	}
		
	return $cadenaCandidatos;
}

function obtenerCandidatosInscribir($idMateria,$idMapaCurricular)
{
	global $con;
	$consulta="select idPadre,idGrado,idTipoMateria from 4031_elementosMapa where idMateria=".$idMateria." and idMapaCurricular=".$idMapaCurricular;
	$padre=$con->obtenerPrimeraFila($consulta);
	$idMapaC=$idMapaCurricular;
	if(!$padre)
	{
		$consulta="select idPadre,idGrado,idTipoMateria,idMapaCurricular from 4031_elementosMapa where idMateria=".$idMateria;
		$padre=$con->obtenerPrimeraFila($consulta);	
		$idMapaC=$padre[3];
	}
	if($padre[0]==0)
	{
		if($padre[2]==2)
		{
			$maximoMat=obtenerMaximoMatPadre($padre[1],$idMapaC,1);
			$cadenaCandidatos=obtenerCandidatosValidos($padre[1],$maximoMat,1);
			return $cadenaCandidatos;
			
		}
	}
	else
	{
		if($padre[2]==2)
		{
			$maximoMat=obtenerMaximoMatPadre($padre[0],$idMapaC,2);
			$cadenaCandidatos=obtenerCandidatosValidos($padre[0],$maximoMat,2);
			return $cadenaCandidatos;
			
		}
	}
}

function obtenerMaximoMatPadre($idPadre,$idMapaCurricular,$bandera)
{
	global $con;
	
	if($bandera==1)
	{
		$consulta="SELECT noMaxMateriaOpC FROM 4014_grados WHERE idGrado=".$idPadre;
		$maximo=$con->obtenerValor($consulta);
		return $maximo;
	}
	else
	{
		$consulta="SELECT noMateriasMax FROM 4031_elementosMapa WHERE idMateria=".$idPadre." AND idMapaCurricular=".$idMapaCurricular;
		$maximo=$con->obtenerValor($consulta);
		return $maximo;
	}
}

function obtenerCandidatosValidos($idPadre,$maximoMat,$bandera)
{
	global $con;
	
	$conAspirantes="";
	
	if($bandera==1)
	{
		$conAspirantes="select idUsuario from 4118_alumnos where idGrado=".$idPadre." and estado=1";
		$resAspirantes=$con->obtenerFilas($conAspirantes);
	
		$consulta="SELECT idMateria FROM 4031_elementosMapa WHERE idGrado=".$idPadre." and idTipoMateria=2";
		
	}
	else
	{
		$conAspirantes="select idUsuario from 4120_alumnosVSElementosMapa where idMateria=".$idPadre." and situacion=1";
		$resAspirantes=$con->obtenerFilas($conAspirantes);
		
		$consulta="SELECT idMateria FROM 4031_elementosMapa WHERE idPadre=".$idPadre." and idTipoMateria=2";
	}
	
	$aspirantes="";
	
	$materias=$con->obtenerListaValores($consulta);
	$nMaterias=$con->filasAfectadas;
	
	if($materias=="")
	{
		$materias="-1";
	}
	
	while($filaA=mysql_fetch_row($resAspirantes))
	{
		$conInscrito="SELECT COUNT(idAlumnosElementoMapa) AS sumatoria FROM 4120_alumnosVSElementosMapa WHERE idUsuario=".$filaA[0]." and idGrupo IS NOT NULL AND idMateria IN (".$materias.")";
		$incripciones=$con->obtenerValor($conInscrito);
		
		if($incripciones<$maximoMat)
		{
			if($aspirantes=="")
				$aspirantes=$filaA[0];
			else	
				$aspirantes.=",".$filaA[0];
		}
	}
	
	return $aspirantes;
}

function convertirFilasAlmacenArrayAsoc($idAlmacen,$filaRegAux)
{
	global $con;
	$consulta="select camposProy from 9014_almacenesDatos where idDataSet=".$idAlmacen;
	$camposProy=$con->obtenerValor($consulta);
	$arrCampos=explode(",",$camposProy);
	$posCampo=0;
	$fila=array();
	foreach($arrCampos as $campo)
	{
	  $fila[$campo]=$filaRegAux[$posCampo];
	  if($fila[$campo]==NULL)
	  	$fila[$campo]="";
	  $posCampo++;
	}
	return $fila;
}

function crearGridDinamico($consulta,$configuracion,$divDestino,$anchoGrid=700,$altoGrid=450,$tamPagina="25",$condExtra="")
{
	$conf=json_decode($configuracion);
	
	$arrCM="";
	$camposJava="";
	$idRegistro="";
	$campoOrden="";
	$arrFiltros="";
	$objConf=$conf->confCampos;
	$funcInicializar="";
	$agruparPor="";
	$multiSel="true";
	if(isset($conf->multiSeleccion)&&($conf->multiSeleccion==1))
	{
		$multiSel="false";
	}
	if(isset($conf->agruparPor))
		$agruparPor=$conf->agruparPor;
	$direccion="ASC";
	if(isset($conf->inicializar)&&($conf->inicializar==1))
		$funcInicializar="if(typeof(Ext)!='undefined') Ext.onReady(inicializarGridDinamico); else window.onload=inicializarGridDinamico;";
	foreach($objConf as $obj)
	{
		$titulo="";
		$ancho="100";
		$indice="";
		$alineacion="left";
		$renderer="function (val)
								{
									return val;
								}";
		$oculto="false";
		if((isset($obj->oculto))&&($obj->oculto==1))
			$oculto="true";
		if(isset($obj->titulo))
			$titulo=$obj->titulo;
		if(isset($obj->ancho))
			$ancho=$obj->ancho;
		if(isset($obj->campo))
			$indice=$obj->campo;
		if(isset($obj->alineacion))
		{
			switch($obj->alineacion)	
			{
				case "D":
					$alineacion="right";
				break;
				case "C":
					$alineacion="center";
				break;
				case "I":
					$alineacion="left";
				break;
				default:
					$alineacion="left";
				break;
				
			}
		}
		
		
		if(isset($obj->formato))
		{
			switch($obj->formato)	
			{
				case "decimal":
					$renderer="function(val)
								{
									
									return Ext.util.Format.number(val,'0.00');	
								}";
				break;
				case "entero":
					$renderer="function(val)
								{
									return Ext.util.Format.number(val,'0');	
								}";
				break;
				case "fechaHora":
					$renderer="function(val)
								{
									if(val=='')
										return '';
									var tiempo=Date.parseDate(val,'Y-m-d H:i:s');
									return tiempo.format('d/m/Y  h:i A');
								}";
				break;
				case "hora":
					$renderer="function(val)
								{
									if(val=='')
										return '';
									var tiempo=Date.parseDate(val,'Y-m-d H:i:s');
									return tiempo.format('h:i A');
								}";
				break;
				case "fecha":
					$renderer="function(val)
								{
									if(val=='')
										return '';
									var arrDatosFecha=val.split(' ');
									
									var arrFecha=(arrDatosFecha[0]+'').split('-');
									return arrFecha[2]+'/'+arrFecha[1]+'/'+arrFecha[0];	
								}";
				break;
				case "moneda":
					$renderer="'usMoney'";
				break;
				case "funcionRenderer":
					$renderer=$obj->funcionRenderer;
				break;
			}
		}
		$plugins=",summary,summary2";
		$sumary="";
		if(isset($obj->sumary))
		{
			$sumary=',summaryType:"'.$obj->sumary.'"';
		}
		
		if(isset($obj->campoID))
			$idRegistro=$indice;
		if(isset($obj->campoOrden))
		{
			$campoOrden=$indice;
		
			if(isset($obj->direccionOrden))
				$direccion=$obj->direccionOrden;
		}
		
		$editor="null";	
		if(isset($obj->editor))
		{
			switch($obj->editor)
			{
				case "texto":
					$editor='	{
									xtype:"textfield"
								}';
				break;
				case "decimal":
					$editor='	{
									xtype:"numberfield",
									allowDecimals:true,
									allowNegative:false
								}';
				
				break;
				case "entero":
					$editor='	{
									xtype:"numberfield",
									allowDecimals:false,
									allowNegative:false
								}';
				
				break;
				case "fecha":
					$editor='	{
									xtype:"datefield"
								}';
				break;
				default:
					$editor=$obj->editor;
				break;
				
			}
		}
		$cadObj="	{
						header:'".$titulo."',
						width:".$ancho.",
						sortable:true,
						dataIndex:'".$indice."',
						align:'".$alineacion."',
						renderer:".$renderer.",
						hidden:".$oculto.",
						editor:".$editor.$sumary."
						
					}";
		if($arrCM=="")
			$arrCM=$cadObj;
		else
			$arrCM.=",".$cadObj;
		$cadObj="{name:'".$indice."'}";
		if($camposJava=="")
			$camposJava=$cadObj;
		else
			$camposJava.=",".$cadObj;
		
		$cadObj="{type: 'string', dataIndex: '".$indice."'}";
		if(isset($obj->filtroConf))
			$cadObj=$obj->filtroConf;
		
		if(isset($obj->tipoFiltro))
			$cadObj="{type: '".$obj->tipoFiltro."', dataIndex: '".$indice."'}";
		
		if(isset($obj->filtro))
			$cadObj=$obj->filtro;
		if($arrFiltros=="")
			$arrFiltros=$cadObj;
		else
			$arrFiltros.=",".$cadObj;
			
	
	}
	
	
	$confBotones=$conf->confBotones;
	$arrBotones="";
	$numBoton=1;
	foreach($confBotones as $boton)
	{
		$idBoton="btn_".$numBoton;
		if(isset($boton->id))
			$idBoton=$boton->id;
		$numBoton++;
		$objBoton="";
		$tituloBtn="";
		$icono="";
		$cuerpoFuncion="";
		$nParamID="-1";
		if(isset($boton->nParamID))
			$nParamID=$boton->nParamID;
		$pagDestino="";
		if(isset($boton->paginaEnvio))
			$pagDestino=$boton->paginaEnvio;
		
		
		$paramComp=NULL;
		if(isset($boton->paramComp))
			$paramComp=$boton->paramComp;			
		$cadParamComp="";
		if($paramComp!=NULL)
		{
			foreach($paramComp as $p)
			{
				$objParamComp="['".$p->param."','".$p->valor."']";
				if($cadParamComp=="")
					$cadParamComp=$objParamComp;
				else
					$cadParamComp.=",".$objParamComp;		
			}
		}
		
		if($cadParamComp!="")
			$cadParamComp=",".$cadParamComp;
		switch($boton->tipo)
		{
			case "R":
				
				$icono="salir.gif";
				if(!isset($boton->leyenda))
					$tituloBtn="Regresar";
				else
					$tituloBtn=$boton->leyenda;
				$cuerpoFuncion="if(gE('configuracionRegresar').value!='')
								{
									gE('frmRegresar').submit();
									return;
								}
								location.href='../principal/inicio.php';";
			break;
			case "A":
				
				$icono="add.png";
				if(!isset($boton->leyenda))
					$tituloBtn="Agregar";
				else
					$tituloBtn=$boton->leyenda;
				$cuerpoFuncion="
									var arrDatos=[['".$nParamID."','-1']".$cadParamComp."];
									enviarFormularioDatos('".$pagDestino."',arrDatos);
									
								";
			break;
			case "E":
				$icono="delete.png";
				if(!isset($boton->leyenda))
					$tituloBtn="Eliminar";
				else
					$tituloBtn=$boton->leyenda;
				$nTabla=$boton->tablaDel;	
				if(gettype($nTabla)=='array')
				{
					$cadTmp="";
					$o="";
					foreach($nTabla as $t)
					{
						$o='{"tabla":"'.$t->tabla.'","campo":"'.$t->campo.'"}';
						if($cadTmp=="")
							$cadTmp=$o;
						else
							$cadTmp.=",".$o;
					}
					$cadTmp='{"objTabla":['.$cadTmp.']}';
					$nTabla=$cadTmp;
				}
				$tblVal="";
				if(isset($boton->tablaRef))
					$tblVal=$boton->tablaRef;
				
				$error="El elemento";
				if(isset($boton->msgError))	
					$error=$boton->msgError;
					
				$idRegistroReferencia=$idRegistro;
				if(isset($boton->campoReferencia)&&($boton->campoReferencia!=""))
				{
					$idRegistroReferencia=$boton->campoReferencia;
				}
				
					
				$cuerpoFuncion="
									var gridDestino=gEx('grid_".$divDestino."');
									var fila=gridDestino.getSelectionModel().getSelected();
									if(fila==null)
									{
										msgBox('Primero debe seleccionar el elemento que desea eliminar');
										return;
									}
									function resp(btn)
									{
										if(btn=='yes')
										{
											var id=fila.get('".$idRegistro."');
											var idReferencia=fila.get('".$idRegistroReferencia."');
											function funcAjax()
											{
												var resp=peticion_http.responseText;
												arrResp=resp.split('|');
												if(arrResp[0]=='1')
												{
													gridDestino.getStore().remove(fila);
												}
												else
												{
													msgBox('No se ha podido llevar cabo la operaci&oacute;n debido al siguiente problema: <br /><br>'+arrResp[0]);
												}
											}
											obtenerDatosWeb('../paginasFunciones/funcionesGridDinamico.php',funcAjax, 'POST','funcion=2&tblVal=".$tblVal."&msgError=".$error."&tb=".bE($nTabla)."&id='+id+'&idReferencia='+idReferencia+'&cId=".bE($nParamID)."',true);
										}
									}
									msgConfirm('Est&aacute; seguro de querer eliminar el elemento seleccionado?',resp);
									
								";
			break;
			case "M":
				$icono="pencil.png";
				if(!isset($boton->leyenda))
					$tituloBtn="Modificar";
				else
					$tituloBtn=$boton->leyenda;
				$cuerpoFuncion="
									var gridDestino=gEx('grid_".$divDestino."');
									var fila=gridDestino.getSelectionModel().getSelected();
									if(fila==null)
									{
										msgBox('Primero debe seleccionar el elemento a modificar');
										return;
									}
									var id=fila.get('".$idRegistro."');
									var arrDatos=[['".$nParamID."',id]".$cadParamComp."];
									enviarFormularioDatos('".$pagDestino."',arrDatos);
									
								";	
			break;	
			case "V":
				$icono="magnifier.png";
				if(!isset($boton->leyenda))
					$tituloBtn="Ver ficha";
				else
					$tituloBtn=$boton->leyenda;
				$cuerpoFuncion="
									var gridDestino=gEx('grid_".$divDestino."');
									var fila=gridDestino.getSelectionModel().getSelected();
									if(fila==null)
									{
										msgBox('Primero debe seleccionar el elemento cuya ficha desea ver');
										return;
									}
									var id=fila.get('".$idRegistro."');
									var arrDatos=[['".$nParamID."',id]".$cadParamComp."];
									enviarFormularioDatos('".$pagDestino."',arrDatos);
									
								";	
			break;	
			case "C":
				$icono="icon_code.gif";
				
				$tituloBtn="";
				if(isset($boton->leyenda))
					$tituloBtn=$boton->leyenda;
				
				
			break;
			case "-":
				
				$tituloBtn="@-@";
				
				
			break;
			
		}
		if(isset($boton->icono))
			$icono=$boton->icono;
				
		if(isset($boton->cuerpoFuncion))
			$cuerpoFuncion=$boton->cuerpoFuncion;
		if($tituloBtn!="@-@")
		{
			if($boton->tipo!="Ext")
			{
				$hidden="false";
				$disabled="false";
				
				if(isset($boton->oculto)&&($boton->oculto==1))
					$hidden="true";
				if(isset($boton->habilitado)&&($boton->habilitado==0))
					$disabled="true";
					
				$objBoton='	{
								id:"'.$idBoton.'",
								text:"'.$tituloBtn.'",
								icon:"../images/'.$icono.'",
								hidden:'.$hidden.',
								disabled:'.$disabled.',
								cls:"x-btn-text-icon",
								handler:function()
										{
											'.$cuerpoFuncion.'
										}
							}';
			}
			else
			{
				$objBoton=$boton->cuerpoExt;
			}
		}
		else
			$objBoton="'-'";
			
		if($arrBotones=="")
			$arrBotones=$objBoton;
		else
			$arrBotones.=",".$objBoton;
	}
	if($arrBotones!="")
	{
		$arrBotones=',"tbar":['.$arrBotones.']';
	}
	$cadFunGrid="
					var summary = new Ext.ux.grid.HybridSummary();
					var summary2 = new Ext.ux.grid.GridSummary();
					var chkRow=new Ext.grid.CheckboxSelectionModel({singleSelect:".$multiSel.",checkOnly :true});
					var camposJava=new Array( ".$camposJava.");
					var columnasJava=new Array(new  Ext.grid.RowNumberer({width:30}),chkRow,".$arrCM.");";
	
	if(isset($conf->selCheck))
	{
		$cadFunGrid.="chkRow.on('rowselect',".$conf->selCheck.");";
	}
	if(isset($conf->selUncheck))
	{
		$cadFunGrid.="chkRow.on('rowdeselect',".$conf->selUncheck.");";
	}
	if($agruparPor=="")
	{					
		$cadFunGrid.="
					var dsTablaRegistros = new Ext.data.JsonStore	(
																			{
																				root: 'registros',
																				totalProperty: 'numReg',
																				idProperty: '".$idRegistro."',
																				fields: camposJava,
																				remoteSort:true,
																				proxy: new Ext.data.HttpProxy	(
																													{
																														url: '../paginasFunciones/funcionesGridDinamico.php',
																														timeout :600000
																													}
																												)
																			}
																		);";
	}
	else
	{
		$cadFunGrid.="
					var lector= new Ext.data.JsonReader({
															totalProperty:'numReg',
															root: 'registros',
															totalProperty: 'numReg',
															idProperty: '".$idRegistro."',
															fields: camposJava
														  }
													  );
																		
					var dsTablaRegistros=new Ext.data.GroupingStore({
                                                            reader: lector,
                                                            proxy: new Ext.data.HttpProxy	(
																								{
																									url: '../paginasFunciones/funcionesGridDinamico.php',
																									timeout :600000	
																								}
																							),
                                                            sortInfo: {field: '".$campoOrden."', direction: '".$direccion."'},
                                                            groupField: '".$agruparPor."',
                                                            remoteGroup:false,
				                                            remoteSort: true
                                                            
                                                        }) 
		
					";
	}
	
	
	if(isset($conf->cargaAlmacen))
	{
		$cadFunGrid.="dsTablaRegistros.on('load',".$conf->cargaAlmacen.");";
	}
	
	
	
	
	$confGroupView="";
	$claseFila="";
	$colapsado="false";
	if(isset($conf->claseFila))
		$claseFila="getRowClass:".$conf->claseFila.",";
	if(isset($conf->colapsado)&&($conf->colapsado==1))
		$colapsado="true";
	
	if($agruparPor!="")
	{
		$confGroupView=",view: new Ext.grid.GroupingView(	{
																  forceFit:false,".$claseFila."
																  showGroupName: false,
																  enableNoGroups:false,
																  enableGroupingMenu:true,
																  hideGroupedColumn: true,
																  startCollapsed:".$colapsado."
															  }   
														  )";
	}
	else
	{
		$confGroupView=",view: 	new Ext.grid.GridView	(
															  {
																  ".$claseFila."
																  showGroupName: false
															  }   
														  )
							";
	}
	$renderTo="renderTo:'".$divDestino."',";
	
	
	
	$codPantalla="";
	
	if(isset($conf->pantallaCompleta))
	{
		$compControles="";
		if(isset($conf->arrControlesLayOut)&&($conf->arrControlesLayOut!=""))
		{
			$compControles.=",".$conf->arrControlesLayOut;		
		}
		$renderTo="region:'center',";
		$codPantalla="new Ext.Viewport(	{
                            layout: 'border',
                            items: [
                            			{
                                        	xtype:'panel',
                                            region:'center',
                                            layout:'border',
											title:'<span class=\"letraRojaSubrayada8\" style=\"font-size:14px !important\"><b>".$conf->pantallaCompleta."</b></span>',
                                            items:	[
                                           				gridRegistros".$compControles."
	                                           		]
                                        }
                                     ]
						}
                    );   ";

	}

	$border="true";
	if(isset($conf->ocultarBordes))
		$border="false";
	
	$compAntesCarga="";
	if(isset($conf->antesCargaAlmacen))
	{
		$compAntesCarga.=$conf->antesCargaAlmacen."(proxy,parametros);";
	}
	
	$funcionInicial="";
	if(isset($conf->funcionInicial))
		$funcionInicial=$conf->funcionInicial;
	
	
	$compRecargar="dsTablaRegistros.reload();";
	if(isset($conf->noReload))
		$compRecargar="";
	
	$cadFunGrid.="																	
					var filters = new Ext.ux.grid.GridFilters	(
    												{
                                                    	filters:	[ ".$arrFiltros."]
                                                    }
                                                );                                                    
                                                    
					dsTablaRegistros.setDefaultSort('".$campoOrden."', '".$direccion."');
					
					function cargarDatos(proxy,parametros)
					{
						proxy.baseParams.consulta='".bE($consulta)."';
						proxy.baseParams.funcion=1;
						proxy.baseParams.condExtra='".bE($condExtra)."';
						proxy.baseParams.agrupacion='".bE($agruparPor)."';
						".$compAntesCarga."
						
						
					}                                      
																	
					dsTablaRegistros.on('beforeload',cargarDatos);                                                 
					var modelColumn= new Ext.grid.ColumnModel   	(
																		columnasJava
																	);
					var tamPagina =	".$tamPagina.";     
																							
					var paginador=	new Ext.PagingToolbar	(
																{
																	  pageSize: tamPagina,
																	  store: dsTablaRegistros,
																	  displayInfo: true,
																	  disabled:false
																  }
															   )                                                    
                    
					
					                                    
       var gridRegistros=	new Ext.grid.EditorGridPanel	(
																  {
																	  id:'grid_".$divDestino."',
																	  store:dsTablaRegistros,
																	  frame:false,
																	  border:".$border.",
																	  cm: modelColumn,
																	  sm:chkRow,
																	  height:".$altoGrid.",
																	  width:".$anchoGrid.",
																	  ".$renderTo."
																	  trackMouseOver:false,
																	  loadMask: true,
																	  plugins: [filters".$plugins."],
																	  enableColLock: false,
																	  stripeRows:true,
																	  clicksToEdit:1,
																	  columnLines:true,
																	  bbar: [paginador]
																	  ".$arrBotones.$confGroupView."
																  }
															  );
		gridRegistros.on('beforeedit',function(e)
										{
											
											if(typeof(gridBeforeEdit)!='undefined')
											{
												gridBeforeEdit(e);
											}
										}
						)		
		gridRegistros.on('afteredit',function(e)
									{
										
										if(typeof(gridAfterEdit)!='undefined')
										{
											gridAfterEdit(e);
										}
									}
						)
		
																	  
		dsTablaRegistros.load({params:{start:0, limit:tamPagina,funcion:1,consulta:'".bE($consulta)."'}});

		".$codPantalla."
		".$compRecargar."
		".$funcionInicial."
		return gridRegistros;
		";
																		
	$cad='	<link rel="stylesheet" type="text/css"  href="../Scripts/ux/resources/style.css" media="screen" />
			<link rel="stylesheet" type="text/css"  href="../Scripts/ux/grid/GridFilters.css" media="screen" />
			<script type="text/javascript" src="../Scripts/ux/menu/EditableItem.js"></script>
			<script type="text/javascript" src="../Scripts/ux/menu/RangeMenu.js"></script>
			<script type="text/javascript" src="../Scripts/ux/menu/ListMenu.js"></script>
			<script type="text/javascript" src="../Scripts/ux/grid/GridFilters.js"></script>
			<script type="text/javascript" src="../Scripts/ux/grid/filter/Filter.js"></script>
			<script type="text/javascript" src="../Scripts/ux/grid/filter/StringFilter.js"></script>
			<script type="text/javascript" src="../Scripts/ux/grid/filter/DateFilter.js"></script>
			<script type="text/javascript" src="../Scripts/ux/grid/filter/ListFilter.js"></script>
			<script type="text/javascript" src="../Scripts/ux/grid/filter/NumericFilter.js"></script>
			<script type="text/javascript" src="../Scripts/ux/grid/filter/BooleanFilter.js"></script>
			<script type="text/javascript" src="../Scripts/base64.js"></script>';
	/*if($agruparPor!="")
	{*/
		$cad.='<link rel="stylesheet" type="text/css" href="../Scripts/ux/grid/GridSummary.css" media="screen" />
				<script type="text/javascript" src="../Scripts/ux/grid/HybritGroupSummary.js"></script>
				<script type="text/javascript" src="../Scripts/ux/grid/GridSummary.js"></script>
				';
	//}
	
	
	
	$cad.='
			<script>
				'.$funcInicializar.'	
				function inicializarGridDinamico()
				{

					'.$cadFunGrid.'
					
					
					
				}
			</script>';
				
	return $cad;
}

function generarXMLTabla($nTabla,$titulo,$cond="")
{
	global $con;
	$tagA='<';
	$tagC='>';
	$campoSepara='';
	$consulta="";
	$arrElementos=array();
	$arrCamposVinculados=array();
	if(strpos($nTabla,'tablaDinamica')===false)
	{
		$consulta="select * from 9013_relacionesTablaSistema where tablaOrigen='".$nTabla."'";
		$resTablaO=$con->obtenerFilas($consulta);
		while($filaO=mysql_fetch_row($resTablaO))
		{
			$objCampo=array();
			$objCampo["tablaVinculo"]=$filaO[3];
			$objCampo["campoVinculo"]=$filaO[4];
			$objCampo["campoProyeccion"]=$filaO[5];
			$objCampo["complementario"]=$filaO[6];
			$arrCamposVinculados[$filaO[2]]=$objCampo;
		}
		$consulta="select COLUMN_NAME from information_schema.COLUMNS where TABLE_SCHEMA='".$con->bdActual."' AND TABLE_NAME='".$nTabla."'";
		$resColunm=$con->obtenerFilas($consulta);
		$listCampos="";
		while($filaColunm=mysql_fetch_row($resColunm))
		{
			$campo='`'.$filaColunm[0].'`';
			if(isset($arrCamposVinculados[$filaColunm[0]]))
			{
				$qComplementario="";
				if($arrCamposVinculados[$filaColunm[0]]["complementario"]!="")
					$qComplementario=" and ".$arrCamposVinculados[$filaColunm[0]]["complementario"];
				$campo="(select ".$arrCamposVinculados[$filaColunm[0]]["campoProyeccion"]." from ".$arrCamposVinculados[$filaColunm[0]]["tablaVinculo"]."
							where ".$arrCamposVinculados[$filaColunm[0]]["campoVinculo"]."=".$nTabla.".".$filaColunm[0].$qComplementario.") as ".$campo;
			}
			if($listCampos=="")
				$listCampos=$campo;
			else	
				$listCampos.=",".$campo;
		}
		$consulta="select ".$listCampos." from ".$nTabla;
	}
	else
	{
		$consulta="select idFormulario from 900_formularios where nombreTabla='".$nTabla."'";
		$idFormulario=$con->obtenerValor($consulta);
		$consulta="SELECT tipoElemento,idGrupoElemento,nombreCampo FROM 901_elementosFormulario WHERE idFormulario=".$idFormulario;
		$resElementoFormulario=$con->obtenerFilas($consulta);
		$arrElementos=array();
		while($filaElemento=mysql_fetch_row($resElementoFormulario))
		{
			$obj=array();
			switch($filaElemento[0])
			{
				case 2:
				case 14:
				case 4:
				case 16:
				case 17:
				case 19:
					$obj[0]=$filaElemento[2]; //Nombre
					$obj[1]=$filaElemento[0]; //Tipo
					$obj[2]=$filaElemento[1]; //Grupo
					array_push($arrElementos,$obj);
				break;
			}
			
		}
		
		$consulta="select COLUMN_NAME from information_schema.COLUMNS where TABLE_SCHEMA='".$con->bdActual."' AND TABLE_NAME='".$nTabla."'";
		$resColunm=$con->obtenerFilas($consulta);
		$listCampos="";
		while($filaColunm=mysql_fetch_row($resColunm))
		{
			$campo='`'.$filaColunm[0].'`';
			$pos=existeValorMatriz($arrElementos,$filaColunm[0]);
			
			if($pos!=-1)
			{
				$obj=$arrElementos[$pos];
				switch($obj[1])
				{
					case 2:
					case 14:
						$consultaRefTablas="(select contenido from 902_opcionesFormulario where idIdioma=".$_SESSION["leng"]." and idGrupoElemento=".$obj[2]." and valor=".$nTabla.".".$campo." )";
						$campo=$consultaRefTablas." as ".$campo."";
					break;
					case 4:
					case 16:
						$queryConf="select * from 904_configuracionElemFormulario where idElemFormulario=".$obj[2];
						$filaConf=$con->obtenerPrimeraFila($queryConf);
						$tablaD=$filaConf[2];
						$campoP=$filaConf[3];
						$campoId=$filaConf[4];
						if((($filaConf[9]==0)&&($obj[1]==4))||($obj[1]==16))
							$consultaRefTablas="(select tc.".$campoP." from ".$tablaD." tc where tc.".$campoId."=".$nTabla.".".$campo.")";
						else
							$consultaRefTablas="(select concat(tc.".$campoP.") from ".$tablaD." tc where tc.".$campoId."=".$nTabla.".".$campo.")";
						$campo=$consultaRefTablas." as ".$campo."";
					break;	
				}
				
			}
			if($listCampos=="")
				$listCampos=$campo;
			else	
				$listCampos.=",".$campo;
		}
		$consulta="select ".$listCampos." from ".$nTabla;
	}
	if($cond!="")
		$consulta.=" where ".$cond;
	
	$cuerpoXML=$tagA.'t'.$nTabla.' tituloTabla="'.$titulo.'"'.$tagC.$campoSepara;
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_assoc($res))
	{
		$idReg='';
		foreach($fila as $nCampo=>$vCampo)
		{
			if($idReg=='')
			{
				$idReg=$vCampo;
				$xFila=$tagA.'reg_'.$idReg.$tagC.$campoSepara;
			}
			$obj=$tagA.$nCampo.$tagC.$tagA.'![CDATA['.$vCampo.']]'.$tagC.$tagA.'/'.$nCampo.$tagC.$campoSepara;
			$xFila.=$obj;
		}
		$xFila.=$tagA.'/reg_'.$idReg.$tagC.$campoSepara;
		$cuerpoXML.=$xFila;
	}
	$cuerpoXML.=$tagA.'/t'.$nTabla.$tagC.$campoSepara;
	
	return $cuerpoXML;
}

function respaldarProceso($idProceso,$idRegistro)
{
	global $con;
	$tagA='<';
	$tagC='>';
	$campoSepara='';
	
	$idFormularioBase=obtenerFormularioBase($idProceso);
	$consulta="select nombreTabla,nombreFormulario from 900_formularios where idFormulario=".$idFormularioBase;
	$filaFrm=$con->obtenerPrimeraFila($consulta);
	
	$frmBase=$filaFrm[0];
	$nTabla=$filaFrm[1];
	$xmlBase=generarXMLTabla($frmBase,$nTabla," id_".$frmBase."=".$idRegistro);
	
	$consulta="SELECT nombreTabla,nombreFormulario,f.idFormulario,f.tipoFormulario FROM 203_elementosDTD e,900_formularios f WHERE f.idFormulario=e.idFormulario AND e.idProceso=".$idProceso;
	$resTablas=$con->obtenerFilas($consulta);
	$xmlProceso=$tagA.'proceso'.$tagC;
	$xmlProceso.=$xmlBase;
	while($fila=mysql_fetch_row($resTablas))
	{
		if($fila[3]=="0")
			$xmlModulo=generarXMLTabla($fila[0],$fila[1]," idReferencia=".$idRegistro);
		else
			$xmlModulo=generarXMLTabla($fila[0],$fila[1]," idFormulario=".$idFormularioBase." and idReferencia=".$idRegistro);
		$xmlProceso.=$xmlModulo;
	}
	$xmlProceso.=$tagA.'/proceso'.$tagC;
	$consulta="select max(version) from 9036_respaldosProceso where idProceso=".$idProceso." and idRegistro=".$idRegistro;
	$mVersion=$con->obtenerValor($consulta);
	if($mVersion=="")
		$mVersion=1;
	else
		$mVersion++;
	$consulta="insert into 9036_respaldosProceso(idProceso,idRegistro,version,documentoXML,idUsuarioResp,fechaRegistro) values(".$idProceso.",".$idRegistro.",".$mVersion.",'".cv($xmlProceso)."',".$_SESSION["idUsr"].",'".date('Y-m-d')."')";
	if($con->ejecutarConsulta($consulta))
		return true;
	else
		return false;

}

function obtenerXMLRespaldoProceso($idProceso,$idRegistro,$version)
{
	global $con;
	$consulta="select documentoXML from 9036_respaldosProceso where idProceso=".$idProceso." and idRegistro=".$idRegistro;
	$docXML=$con->obtenerValor($consulta);
	return $docXML;
}

function obtenerVersionProceso($idProceso,$idRegistro)
{
	global $con;
	$tagA='<';
	$tagC='>';
	$campoSepara='';
	
	$idFormularioBase=obtenerFormularioBase($idProceso);
	$consulta="select nombreTabla,nombreFormulario from 900_formularios where idFormulario=".$idFormularioBase;
	$filaFrm=$con->obtenerPrimeraFila($consulta);
	
	$frmBase=$filaFrm[0];
	$nTabla=$filaFrm[1];
	$xmlBase=generarXMLTabla($frmBase,$nTabla," id_".$frmBase."=".$idRegistro);
	
	$consulta="SELECT nombreTabla,nombreFormulario,f.idFormulario,f.tipoFormulario FROM 203_elementosDTD e,900_formularios f WHERE f.idFormulario=e.idFormulario AND e.idProceso=".$idProceso;
	$resTablas=$con->obtenerFilas($consulta);
	$xmlProceso=$tagA.'proceso'.$tagC;
	$xmlProceso.=$xmlBase;
	while($fila=mysql_fetch_row($resTablas))
	{
		if($fila[3]=="0")
			$xmlModulo=generarXMLTabla($fila[0],$fila[1]," idReferencia=".$idRegistro);
		else
			$xmlModulo=generarXMLTabla($fila[0],$fila[1]," idFormulario=".$idFormularioBase." and idReferencia=".$idRegistro);
		$xmlProceso.=$xmlModulo;
	}
	$xmlProceso.=$tagA.'/proceso'.$tagC;
	return $xmlProceso;
}

function obtenerCampoIDTabla($nTabla)
{
	global $con;
	$consulta="SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='".$con->bdActual."' AND TABLE_NAME='".$nTabla."' AND COLUMN_KEY='PRI'";
	$columnaID=$con->obtenerValor($consulta);
	return $columnaID;
	
		
}

function listarContenidoArreglo($arreglo)
{
	
	foreach($arreglo as $elem)	
	{
		switch(gettype($elem))
		{
			case 'string':
				echo $elem."<br>";	
			break;	
			case 'object':
				echo convertirCadenaObjeto($elem)."<br>";
			break;
			case 'array':
				echo convertirCadenaArray($elem)."<br>";
			break;
			default:
				echo $elem."<br>";	
		}
			
	}
}

function convertirCadenaObjeto($obj)
{
	$objParam='';
	$reflectionClase = new ReflectionObject($objParametros);
	foreach ($reflectionClase->getProperties() as $property => $value) 
	{
		$nombre=$value->getName();
		$valor=$value->getValue($objParametros);
		$obj="'".$nombre."':'".$valor."'";
		if($objParam=="")
			$objParam=$obj;
		else
			$objParam.=",".$obj;
	}
	return '{'.$objParam.'}';	

}

function convertirCadenaArray($arreglo)
{
	$objParam="";
	$ct=0;
	if(is_arr_num($arreglo))
	{
		
		foreach($arreglo as $e)
		{
			$obj="'".$ct."':'".$e."'";
			if($objParam=="")
				$objParam=$obj;
			else
				$objParam.=",".$obj;
			$ct++;
		}
		return "[".$objParam."]";
	}
	else
	{
			
		foreach($arreglo as $nombre=>$valor)
		{
			$obj="'".$nombre."':'".$valor."'";
			if($objParam=="")
				$objParam=$obj;
			else
				$objParam.=",".$obj;
		}
		return '{'.$objParam.'}';
	}
	
}

function is_arr_num($arr)
{
	if(!is_array($arr))
		return false;
	foreach($arr as $i =>$v)
	{
		if(!is_numeric($i))
			return false;
	}
	return true;
}

function generarIntervaloNumeros($inicio,$fin,$intervalo)
{
	$arrElemento='';
	if($inicio<$fin)
	{
		for($numE=$inicio;$numE<=$fin;$numE+=$intervalo)
		{
			$objElem="['".$numE."','".$numE."']";
			if($arrElemento=='')
				$arrElemento=$objElem;
			else
				$arrElemento.=",".$objElem;
			
		}
	}
	else
	{
		for($numE=$inicio;$numE>=$fin;$numE-=$intervalo)
		{
			$objElem="['".$numE."','".$numE."']";
			if($arrElemento=='')
				$arrElemento=$objElem;
			else
				$arrElemento.=",".$objElem;
			
		}
	}
	
	$arrDatos="[".$arrElemento."]";	
	return $arrDatos;
}

function generarFormatoOpcionesQuery($objParam) //1 array javascript; 2 opciones select;
{
	$cadFinal="";
	global $con;

	$arregloAux=array();
	if($objParam->formato!=4)
	{
		$finalizar=false;
		while(($fila=$objParam->conector->obtenerSiguienteFilaAsoc($objParam->resQuery))&&(!$finalizar))
		{
			
			$textoProy="";			
			foreach($objParam->arrCamposProy as $campo)
			{
				
				$cNormalizado=str_replace(".","_",$campo);				
				if(isset($fila[$cNormalizado]))
					$textoProy.=$fila[$cNormalizado];
				else	
				{
					$arrCampo=explode(".",$campo);
					if(sizeof($arrCampo)>1)
					{
						if($objParam->conector->existeTabla(cv($arrCampo[0])))
							continue;
					}
					$textoProy.=str_replace("'","",$campo);
				}
			}
			$cNormalizado=str_replace(".","_",$objParam->campoID);
			switch($objParam->formato)
			{
				case "1": //Arreglo javascript;
				
					$elemento="['".$fila[$cNormalizado]."','".str_replace("'","\'",str_replace("' '"," ",$textoProy))."']";
					$elemento=str_replace("|","_@_",$elemento);
					if($cadFinal=="")
						$cadFinal=$elemento;
					else
						$cadFinal.=",".$elemento;
				break;	
				case "2": //opciones select
					$s="";
					if(isset($objParam->itemSelect))
					{
						if($fila[$cNormalizado]==$objParam->itemSelect)
							$s="selected='selected'";
					}
					
					$cadenaTooltip="";
					$cadenaCierreTooltip="";
									
					if(isset($objParam->campoToolTip)&&($objParam->campoToolTip!=""))
					{
						$cToolNormalizado=str_replace(".","_",$objParam->campoToolTip);

						if(isset($fila[$cToolNormalizado])&&(trim($fila[$cToolNormalizado])!=""))
						{
							//$cadenaTooltip='data-ot="'.htmlentities($fila[$cToolNormalizado]).'" data-ot-border-width="2" data-ot-stem-length="18" data-ot-stem-base="20" data-ot-tip-joint="top" data-ot-border-color="#317CC5" data-ot-style="glass"';
							
					

							

						}
					}
					
					
					
					$elemento="<option value='".$fila[$cNormalizado]."' ".$s." ".$cadenaTooltip."><div>".str_replace("'","\'",str_replace("' '"," ",$textoProy))."</div></option>";
					/*if($cadFinal=="")
						$cadFinal=$elemento;
					else*/
					$cadFinal.="".$elemento;
				break;
				case "3":  //opciones arreglo
					$obj=array();
					$obj[0]=$fila[$cNormalizado];
					$obj[1]=str_replace("'","\'",str_replace("' '"," ",$textoProy));
					$obj[2]="";
					
					
					if(isset($objParam->campoToolTip)&&($objParam->campoToolTip!=""))
					{
						$cToolNormalizado=str_replace(".","_",$objParam->campoToolTip);
						if(isset($fila[$cToolNormalizado]))
						{
							
							//varDUmp($fila);
							//varDump($objParam->campoToolTip);
							$obj[2]=$fila[$cToolNormalizado];
						}
					}
					
					
					array_push($arregloAux,$obj);
				break;
				case "5": //primer valor
				
					$cadFinal=$textoProy;
					$finalizar=true;
				break;
				case "6":
					
					if($cadFinal=="")
						$cadFinal=$textoProy;
					else
						$cadFinal.=", ".$textoProy;
					
				break;
			}
				
		}
	}
	else
	{
		
		$consulta=$objParam->query;
		if(isset($objParam->queryReemplazo))
			$consulta=$objParam->queryReemplazo;
		$arrDatosConsulta=explode("order by",$consulta);
		$consulta=$arrDatosConsulta[0];
		
		$arrAux=explode(".",$objParam->campoID);
		$idTablaCampo=$arrAux[0];
		
		$arrTablas=obtenerTablasInvolucradasQuery($consulta);
		$arrTablasProyeccion=obtenerTablasCamposProyeccionQuery($consulta,$arrTablas);

		
		$arrDatosCampoLlave=explode(".",$objParam->campoID);
		$queryKey="SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='".$objParam->conector->bdActual."' AND TABLE_NAME='".$arrDatosCampoLlave[0].
				"' AND COLUMN_NAME='".$arrDatosCampoLlave[1]."' AND COLUMN_KEY='PRI'";
		$nKey=$con->obtenerValor($queryKey);
		$esCampoLlave=$nKey>0;
		if((sizeof($arrTablas)==1)&&($esCampoLlave))
		{
			
			$arrConsulta=explode("where",$consulta);
			if(strpos($objParam->itemSelect,",")===false)
				$consulta=$arrConsulta[0]." where ".$objParam->campoID."='".$objParam->itemSelect."'";
			else	
				$consulta=$arrConsulta[0]." where ".$objParam->campoID." in(".$objParam->itemSelect.")";	
			
		}
		else
		{
			if((sizeof($arrTablasProyeccion)==1)&&(isset($arrTablasProyeccion[$idTablaCampo]))&&($esCampoLlave))
			{
				$arrConsulta=explode("from",$consulta);
				if(strpos($objParam->itemSelect,",")===false)
					$consulta=$arrConsulta[0]."from ".$idTablaCampo." where ".$objParam->campoID."='".$objParam->itemSelect."'";
				else
					$consulta=$arrConsulta[0]."from ".$idTablaCampo." where ".$objParam->campoID." in(".$objParam->itemSelect.")";
			}
			else
			{
				if(strpos($consulta,"where")!==false)
				{
					$arrConsulta=explode("where",$consulta);
					$consulta=$arrConsulta[0]." where (".$arrConsulta[1].")";
					if(strpos($objParam->itemSelect,",")===false)
						$consulta.=" and ".$objParam->campoID."='".$objParam->itemSelect."'";
					else
						$consulta.=" and ".$objParam->campoID." in(".$objParam->itemSelect.")";
				}
				else
				{
					if(strpos($objParam->itemSelect,",")===false)
						$consulta.=" where ".$objParam->campoID."='".$objParam->itemSelect."'";
					else
						$consulta.=" where ".$objParam->campoID."in (".$objParam->itemSelect.")";
				}
			}
		}
		
		
		

		/*if(isset($arrDatosConsulta[1]))
			$consulta.=" order by ".$arrDatosConsulta[1];*/
		
		
		$arrConsultaAux=explode(" from ",$consulta);
		$arrConsultaAux[0]=str_replace("select ","",$arrConsultaAux[0]);
		$arrConsultaAux=explode(",",$arrConsultaAux[0]);
		$arrCamposFinal=array();
		foreach($arrConsultaAux as $campoTmp)
		{
			$aTmp=explode(" as ",$campoTmp);
			array_push($arrCamposFinal,$aTmp[0]);
			
		}
		
		
		
		if(!isset($objParam->mutiValor) || !$objParam->mutiValor)
		{
			$fila=$objParam->conector->obtenerPrimeraFilaAsoc($consulta);
			
			$textoProy="";
		
			foreach($objParam->arrCamposProy as $campo)
			{
				$cNormalizado=str_replace(".","_",$campo);
				
				if(isset($fila[$cNormalizado]))
					$textoProy.=$fila[$cNormalizado];
				else	
				{
					if(!existeValor($arrCamposFinal,$campo))
						$textoProy.=str_replace("'","",$campo);
				}
				
			}
			$cadFinal=str_replace("'","\'",str_replace("' '"," ",$textoProy));
			
		}
		else
		{
			$resultado=$objParam->conector->obtenerFilas($consulta);
			
			$cadFinal="";
			while($fila=mysql_fetch_assoc($resultado))
			{
				$textoProy="";
				foreach($objParam->arrCamposProy as $campo)
				{
					$cNormalizado=str_replace(".","_",$campo);
					
					if(isset($fila[$cNormalizado]))
						$textoProy.=$fila[$cNormalizado];
					else	
					{
						if(!existeValor($arrCamposFinal,$campo))
							$textoProy.=str_replace("'","",$campo);
					}
					
				}
				$oAux=str_replace("'","\'",str_replace("' '"," ",$textoProy));
				if($cadFinal=="")
					$cadFinal=$oAux;
				else
					$cadFinal.=", ".$oAux;
			}
			
			
			
		}
		
		
		
		
		
	}
	switch($objParam->formato)
	{
		
		case "3":
			return $arregloAux;
		break;
		default:
			if($objParam->imprimir==1)
				echo $cadFinal;
			else
				return $cadFinal;
	}
}

function obtenerTablasInvolucradasQuery($query)
{
	$arrAux=explode(" from ",$query);
	$token=$arrAux[1];
	$arrAux=explode(" where ",$token);
	if(sizeof($arrAux)>1)
	{
		$token=$arrAux[0];
	}
	else
	{
		$arrAux=explode(" order ",$token);
		if(sizeof($arrAux)>1)
		{
			$token=$arrAux[0];
		}
		else
		{
			$arrAux=explode(" limit ",$token);
			$token=$arrAux[0];
		}
	}
	$arrTablas=array();
	$arrTablasAux=explode(",",$token);
	foreach($arrTablasAux as $t)
	{
		array_push($arrTablas,trim($t));
	}
	return $arrTablas;
}

function obtenerTablasCamposProyeccionQuery($query,$tablas)
{
	$arrAux=explode(" from ",$query);
	$token=$arrAux[0];
	$arrAux=explode("select ",$token);
	$token=$arrAux[1];
	$arrCampos=explode(",",$token);
	$arrTablas=array();
	foreach($tablas as $t)
	{
		foreach($arrCampos as $c)
		{
			if(strpos($c,$t.".")!==false)	
				$arrTablas[$t]=1;
		}
	}
	
	
	return $arrTablas;
}

function obtenerCamposProyeccionQuery($query)
{
	$arr1=explode("select ",$query);
	$campos=$arr1[1];
	$arr2=explode(" from ",$campos);
	$campos=$arr2[0];
	$campos=trim($campos);
	$arrCampos=explode(",",$campos);
	$cTmp="";
	$aCampos=array();
	foreach($arrCampos as $c)
	{
		$cLimpio=str_replace("distinct","",$c);
		
		if(strpos($cLimpio," as ")===false)	
		{
			$cTmp="if(".$cLimpio." is null,'',".$cLimpio.") as ".$cLimpio;
		}
		else
		{
			$arrTemp=explode(" as ",$cLimpio);
			$cTmp="if(".$arrTemp[0]." is null,'',".$arrTemp[0].") as ".$arrTemp[1];
		}
		array_push($aCampos,$cTmp);
	}
	
	
	return $aCampos;
}

function normalizarQueryProyeccion($query)
{
	$arrCampos=	obtenerCamposProyeccionQuery($query);

	$arr2=explode(" from ",$query);
	$condiciones=" from ".$arr2[1];
	$campos='';

	foreach($arrCampos as $c)
	{
		if(strpos($c," as ")===false)
		{
			$c=str_replace("distinct","",$c);
			if($campos=='')
				$campos=$c." as '".str_replace(".","__",$c)."'";
			else	
				$campos.=",".$c." as '".str_replace(".","__",$c)."'";
		}
		else
		{
			$arrDatos=explode(" as ",$c);
			$arrDatos[1]=str_replace(".","__",$arrDatos[1]);
			$c=$arrDatos[0]." as ".$arrDatos[1];
			if($campos!='')
				$campos.=",".$c;
			else
				$campos.=$c;
		}
	}
	$consulta="select ".$campos.$condiciones;

	return $consulta;
}

function descomponerExpresion($arrExpresion,&$nInicio)
{

	$arrTokens=array();
	$arrTokens["expresion"]=array();
	$nTokens=0;
	$arrTokens["tokensReemplazo"]=array();
	$tamArreglo=sizeof($arrExpresion);
	$x=0;

	for($x=$nInicio;$x<$tamArreglo;$x++)
	{
		$exp=$arrExpresion[$x];
		switch($exp->tipoToken)
		{
			case "0":
				switch($exp->tokenMysql)
				{
					case "(":
						if(sizeof($arrTokens)>0)			
						{
							$expToken=json_decode('{"tipoToken":"20","nToken":"'.$nTokens.'"}');
							array_push($arrTokens["expresion"],$expToken);
							$x++;
							$arrTokens["tokensReemplazo"][$nTokens]=descomponerExpresion($arrExpresion,$x);
							$nTokens++;
						}
					break;
					case ")":
						$nInicio=$x;
						return $arrTokens;
					break;	
					default:
						
						array_push($arrTokens["expresion"],$exp);
					break;
				}
			break;
			default:
				array_push($arrTokens["expresion"],$exp);
			break;	
		}
	}
	$nInicio=$x;
	return $arrTokens;
}

function evaluarCadenaExpresion($expresion,$arrConsultas,$debug=false)
{
	global $con;
	$cadObj='{"expresion":'.$expresion.'}';
	$obj=json_decode($cadObj);
	$arrExpresion=$obj->expresion;
	$ini=0;
	
	$arrExp=descomponerExpresion($arrExpresion,$ini);
	/*if($debug)
		varDump($arrExp);*/
	$resultado=resolverExpresion($arrExp,$arrConsultas,$debug); 
	return $resultado;
}

function varDump($exp)
{
	echo "<pre>";
	print_r($exp);
	echo "</pre>";
}

function resolverExpresion($arrExpresion,$arrConsultas,$debug=false)
{
	$nArreglo=sizeof($arrExpresion["expresion"]);
	$acumulador=$arrExpresion["expresion"][0];
	$tokenResuelto=false;
	/*if($debug)
		varDump($arrExpresion);*/
	if($acumulador->tipoToken==20)
	{
		$acumulador=resolverExpresion($arrExpresion["tokensReemplazo"][$acumulador->nToken],$arrConsultas);
		$acumulador->tokenMysql;
		$acumulador=setAtributoObjJson($acumulador,"resuelto",1);
		$tokenResuelto=true;
	}

	$operando=NULL;
	$operador=NULL;
	
	if($nArreglo==1)
	{
		
		if(!$tokenResuelto)
		{
			$acumulador->tokenMysql=resolverOperando($acumulador,$arrConsultas,$debug);
			
		}
		return $acumulador;
	}
	
	for($x=1;$x<$nArreglo;$x++)
	{
		if(($x%2)==0)
			$operando=$arrExpresion["expresion"][$x];
		else
		{
			$operador=$arrExpresion["expresion"][$x];
			if($operador->tipoToken==20)
			{
				$operador=resolverExpresion($arrExpresion["tokensReemplazo"][$operador->nToken],$arrConsultas);
			}
		}
		if($operando!=NULL)
		{
			/*varDump($acumulador);
			varDump($operador);
			varDump($operando);*/
			$acumulador=resolverOperacionBinaria($acumulador,$operador,$operando,$arrConsultas,$debug);
			$operando=NULL;
		}
	}

	return $acumulador;
}

function resolverOperacionBinaria($operando1,$operador,$operando2,$arrConsultas,$debug=false)
{
	/*if($debug)
	{
		varDump($operando1);
		varDump($operador);
		varDump($operando2);
	}*/
	$op1=resolverOperando($operando1,$arrConsultas);

	$op2=resolverOperando($operando2,$arrConsultas);
	/*if($debug)
	{
		varDump($op1);
		varDump($operador);
		varDump($op2);
	}*/
	$tokenResult=NULL;
	$resultado="1970-01-01";
	if(($operando1->tipoValor=='date')||($operando2->tipoValor=='date'))
	{
		if(($operando1->tipoValor=='date')&&($operando2->tipoValor=='date'))
		{
			switch($operador->tokenMysql)
			{
				case "+":
					$resultado=date("Y-m-d",strtotime($operando1->tokenMysql)+strtotime($operando2->tokenMysql));
					$cadToken='{"tokenUsr":"'.$resultado.'","tokenMysql":"'.$resultado.'","tipoToken":"1","tipoValor":"date"}';
					$tokenResult=json_decode($cadToken);
				break;
				case "-":
					$resultado=obtenerDiferenciaDias($operando1->tokenMysql,$operando2->tokenMysql);
					$cadToken='{"tokenUsr":"'.$resultado.'","tokenMysql":"'.$resultado.'","tipoToken":"1","tipoValor":"day"}';
					$tokenResult=json_decode($cadToken);
				break;
			}
		}
		else
		{
			if($operando1->tipoValor=='day')
			{
				if($operando2->tipoValor=='date')
				{
					$arrValor1=explode("|",$op1);
					switch($arrValor1[1])
					{
						case "1": //dias
							switch($operador->tokenMysql)
							{
								case "+":
									$resultado=date('Y-m-d',sumarDias(strtotime($op2),$arrValor1[0]));
								break;
								case '-':
									$resultado=date('Y-m-d',sumarDias(strtotime($op2),$arrValor1[0]*-1));
								break;
							}
						break;
						case "2": //semanas
							switch($operador->tokenMysql)
							{
								case "+":
									$resultado=date('Y-m-d',sumarSemanas(strtotime($op2),$arrValor1[0]));
								break;
								case '-':
									$resultado=date('Y-m-d',sumarSemanas(strtotime($op2),$arrValor1[0]*-1));
								break;
							}
						break;
						case "3":  //meses
							switch($operador->tokenMysql)
							{
								case "+":
									$resultado=date('Y-m-d',sumarMeses(strtotime($op2),$arrValor1[0]));
								break;
								case '-':
									$resultado=date('Y-m-d',sumarMeses(strtotime($op2),$arrValor1[0]*-1));
								break;
							}
						break;
						case "4": //anios
							switch($operador->tokenMysql)
							{
								case "+":
									$resultado=date('Y-m-d',sumarAnios(strtotime($op2),$arrValor1[0]));
								break;
								case '-':
									$resultado=date('Y-m-d',sumarAnios(strtotime($op2),$arrValor1[0]*-1));
								break;
							}
						break;
					}
					$cadToken='{"tokenUsr":"'.$resultado.'","tokenMysql":"'.$resultado.'","tipoToken":"1","tipoValor":"date"}';
					$tokenResult=json_decode($cadToken);
				}
				else
				{
					$cadToken='{"tokenUsr":"1970-01-01","tokenMysql":"1970-01-01","tipoToken":"1","tipoValor":"date"}';
					$tokenResult=json_decode($cadToken);
				}
			}
			else
			{
				if($operando2->tipoValor=='day')
				{
					if($operando1->tipoValor=='date')
					{
						$arrValor2=explode('|',$op2);
						switch($arrValor2[1])
						{
							case "1": //dias
								
								switch($operador->tokenMysql)
								{
									case "+":
										$resultado=date('Y-m-d',sumarDias(strtotime($op1),$arrValor2[0]));
									break;
									case '-':
										$resultado=date('Y-m-d',sumarDias(strtotime($op1),$arrValor2[0]*-1));
									break;
								}
							break;
							case "2": //semanas
								switch($operador->tokenMysql)
								{
									case "+":
										$resultado=date('Y-m-d',sumarSemanas(strtotime($op1),$arrValor2[0]));
									break;
									case '-':
										$resultado=date('Y-m-d',sumarSemanas(strtotime($op1),$arrValor2[0]*-1));
									break;
								}
							break;
							case "3":  //meses
								switch($operador->tokenMysql)
								{
									case "+":
										$resultado=date('Y-m-d',sumarMeses(strtotime($op1),$arrValor2[0]));
									break;
									case '-':
										$resultado=date('Y-m-d',sumarMeses(strtotime($op1),$arrValor2[0]*-1));
									break;
								}
							break;
							case "4": //anios
								switch($operador->tokenMysql)
								{
									case "+":
										$resultado=date('Y-m-d',sumarAnios(strtotime($op1),$arrValor2[0]));
									break;
									case '-':
										$resultado=date('Y-m-d',sumarAnios(strtotime($op1),$arrValor2[0]*-1));
									break;
								}
							break;
						}
						$cadToken='{"tokenUsr":"'.$resultado.'","tokenMysql":"'.$resultado.'","tipoToken":"1","tipoValor":"date"}';
						$tokenResult=json_decode($cadToken);
					}
					else
					{
						$cadToken='{"tokenUsr":"1970-01-01","tokenMysql":"1970-01-01","tipoToken":"1","tipoValor":"date"}';
						$tokenResult=json_decode($cadToken);
					}
				}
			}
		}
	}
	else
	{
		if(($operando1->tipoValor=='day')||($operando2->tipoValor=='day'))
		{
			if(($operando1->tipoValor=='day')&&($operando2->tipoValor=='day'))
			{
				$arrValor1=explode("|",$op1);
				$arrValor2=explode("|",$op2);
				$unidadDestino="1";
				if($arrValor1[1]<$arrValor2[1])
				{
					$op1=convertirValorUnidad($arrValor2[0],$arrValor2[1],$arrValor1[1]);
					$unidadDestino=$arrValor1[1];
				}
				else
				{
					$op2=convertirValorUnidad($arrValor1[0],$arrValor1[1],$arrValor2[1]);
					$unidadDestino=$arrValor2[1];
				}
				eval('$resultado='.$arrValor1[0].$operador.$arrValor2[0]);
				$cadToken='{"tokenUsr":"'.$resultado.'","tokenMysql":"'.$resultado."|".$unidadDestino.'","tipoToken":"1","tipoValor":"day"}';
				$tokenResult=json_decode($cadToken);
			}
			else
			{
				if($operando1->tipoValor=='day')
				{
					if($operando2->tipoValor=='num')
					{
						$arrValor1=explode("|",$op1);
						$unidadDestino=$arrValor1[1];
						eval('$resultado='.$arrValor1[0].$operador.$op2);
						$cadToken='{"tokenUsr":"'.$resultado.'","tokenMysql":"'.$resultado."|".$unidadDestino.'","tipoToken":"1","tipoValor":"day"}';
						$tokenResult=json_decode($cadToken);
					}
					else
					{
						$arrValor1=explode("|",$op1);
						$unidadDestino=$arrValor1[1];
						eval('$resultado='.$arrValor1[0]);
						$cadToken='{"tokenUsr":"'.$resultado.'","tokenMysql":"'.$resultado."|".$unidadDestino.'","tipoToken":"1","tipoValor":"day"}';
						$tokenResult=json_decode($cadToken);
					}
						
				}
				else
				{
					if($operando1->tipoValor=='num')
					{
						$arrValor2=explode("|",$op2);
						$unidadDestino=$arrValor2[1];
						eval('$resultado='.$arrValor2[0].$operador.$op1);
						$cadToken='{"tokenUsr":"'.$resultado.'","tokenMysql":"'.$resultado."|".$unidadDestino.'","tipoToken":"1","tipoValor":"day"}';
						$tokenResult=json_decode($cadToken);
					}
					else
					{
						$arrValor2=explode("|",$op2);
						$unidadDestino=$arrValor2[1];
						eval('$resultado='.$arrValor2[0]);
						$cadToken='{"tokenUsr":"'.$resultado.'","tokenMysql":"'.$resultado."|".$unidadDestino.'","tipoToken":"1","tipoValor":"day"}';
						$tokenResult=json_decode($cadToken);
					}
				}
			}
			
		}
		else
		{
			eval('$resultado=$operando1->tokenMysql'.$operador.'$operando2->tokenMysql');
			$cadToken='{"tokenUsr":"'.$resultado.'","tokenMysql":"'.$resultado.'","tipoToken":"1","tipoValor":"num"}';
			$tokenResult=json_decode($cadToken);
		}
	}
	return $tokenResult;
}

function resolverOperando($op,$arrConsultas,$debug=false)
{
	$valor="";
	/*if($debug)
		varDump($op);*/
	switch($op->tipoToken)
	{
		case "1":
			$valor=$op->tokenMysql;
		break;
		case "4":
			switch($op->tokenMysql)
			{
				case "8":
					$valor=date("Y-m-d");
				break;
				case "9":
					$valor=date("H:i");
				break;
			}
		break;
		case "7":
		case "11":
			if($op->tipoValor=='num')
				$valor="0";
			else
				$valor="1970-01-01";
			$dToken=explode("|",$op->tokenMysql);
			if((!isset($op->resuelto))||($op->resuelto==0))
			{
				if(isset($arrConsultas[$dToken[0]]))
				{
					if($arrConsultas[$dToken[0]]["ejecutado"]==1)
					{
						$resQuery=$arrConsultas[$dToken[0]]["resultado"];
						$conAux=$arrConsultas[$dToken[0]]["conector"];
						$conAux->inicializarRecurso($resQuery);	
						
						$filaRes=$conAux->obtenerSiguienteFilaAsoc($resQuery);
						if($filaRes)
						{
							$cNormalizado=str_replace(".","_",$dToken[1]);
							$valor=$filaRes[$cNormalizado]."|".$dToken[2];
						}
					}
				}
			}
			else
				$valor=$dToken[0];
		break;
		
	}
	return $valor;
}

function expresionResuelta($arrExpresion)
{
	
	foreach($arrExpresion as $exp)
	{
		//varDump($exp);
		if($exp->tipoToken==20)
			return false;
	}	
	return true;
}

function convertirValorUnidad($valor,$unidadOrigen,$unidadDestino)
{
	switch($unidadOrigen)
	{
		case "1":
			return $valor;
		break;
		case "2":
			switch($unidadDestino)
			{
				case "1":
					return $valor*7;
				break;
				default:
					return $valor;
			}
		break;
		case "3":
			switch($unidadDestino)
			{
				case "1":
					return $valor*30;
				break;
				case "2":
					return $valor*4;
				break;
				default:
					return $valor;
			}
		break;
		case "4":
			switch($unidadDestino)
			{
				case "1":
					return $valor*365;
				break;
				case "2":
					return $valor*52;
				break;
				case "3":
					return $valor*12;
				break;
				default:
					return $valor;
			}
		break;
	}
}

function sumarDias($fecha,$dias)
{
	return strtotime('+'.$dias.' days',$fecha);
}

function sumarSemanas($fecha,$semanas)
{
	return strtotime('+'.$semanas.' week',$fecha);
}

function sumarMeses($fecha,$meses)
{
	return strtotime('+'.$meses.' month',$fecha);
}

function sumarAnios($fecha,$anio)
{
	return strtotime('+'.$anio.' year',$fecha);
}

function pCon($cadena)
{
	if(strpos($cadena,'C')===false)
		return false;
	else
		return true;
}

function pAgr($cadena)
{
	if(strpos($cadena,'A')===false)
		return false;
	else
		return true;

}

function pMod($cadena)
{
	if(strpos($cadena,'M')===false)
		return false;
	else
		return true;

}

function pEli($cadena)
{
	if(strpos($cadena,'E')===false)
		return false;
	else
		return true;

}

function obtenerOpcionesElemento($idElemento)
{
	global $con;
	$idControl=$idElemento;
	$consulta="select tipoElemento from 901_elementosFormulario where idGrupoElemento=".$idControl;
	$tipoElemento=$con->obtenerValor($consulta);
	$arrOpciones="";
	$arrOpciones=array();
	switch($tipoElemento)
	{
		case "2":
		case "14":
		case "17":
			$consulta="select valor,contenido from 902_opcionesFormulario where idGrupoElemento=".$idControl." and idIdioma=".$_SESSION["leng"];
			$arrOpciones=$con->obtenerFilasArregloPHP($consulta);	
		break;
		case "3":
		case "15":
		case "18":
			$consulta="select * from 904_configuracionElemFormulario where idElemFormulario=".$idControl;
			$fila=$con->obtenerPrimeraFila($consulta);
			$inicio=$fila[2];
			$fin=$fila[3];
			$intervalo=$fila[4];
			
			if($inicio<$fin)
			{
				for($x=$inicio;$x<=$fin;$x+=$intervalo)
				{
					$obj=array();
					$obj[0]=$x;
					$obj[1]=$x;
					array_push($arrOpciones,$obj);
				}
			}
			else
			{
				for($x=$inicio;$x>=$fin;$x-=$intervalo)
				{
					$obj=array();
					$obj[0]=$x;
					$obj[1]=$x;
					array_push($arrOpciones,$obj);
					
				}
			}
			
		break;
		case "4":
		case "16":
		case "19":
			$consulta="select * from 904_configuracionElemFormulario where idElemFormulario=".$idControl;
			$fila=$con->obtenerPrimeraFila($consulta);		
			$nTabla=$fila[2];
			$campo=$fila[3];
			$columnaID=$fila[4];
			if(strpos($nTabla,"[")===false)
			{
				$consulta="select ".$columnaID.",".$campo." from ".$nTabla." where ".$columnaID." ";
				$arrOpciones=$con->obtenerFilasArregloPHP($consulta);	
			}
			else
			{
				$nTabla=str_replace("]","",str_replace("[","",$nTabla));
				$query=bD($_POST["qy"]);
				$resQuery=$con->obtenerFilas($query);
				$arrCamposProy=explode("@@",$campo);
				$cadObj='{"conector":null,"resQuery":null,"idAlmacen":"","arrCamposProy":[],"formato":"1","imprimir":"0","campoID":"'.$columnaID.'"}';
				$obj=json_decode($cadObj);
				$obj->resQuery=$resQuery;
				$obj->idAlmacen=$nTabla;
				$obj->arrCamposProy=$arrCamposProy;
				$obj->formato=3;
				$obj->conector=$con;
				$arrOpciones=generarFormatoOpcionesQuery($obj);
			}
		break;
	}
	return $arrOpciones;
}

function generarCadenaAleatoria($caracteres,$longitudCadena)
{
	$cad="";
	for($x=0;$x<$longitudCadena;$x++)	
	{
		$cad.=substr($caracteres,rand(0,strlen($caracteres)-1),1);
	}
	return $cad;
}

function importarImagenes($dirImagen)
{
	global $con;
	$dir = opendir($dirImagen);
	$x=0;
	$consulta[$x]="begin";
	$x++;
	
	while ($elemento = readdir($dir))
	{
		if(($elemento!='.')&&($elemento!='..'))
		{
			$query="select idUsuario from 806_fotos where idUsuario=".$nombre;
			$idUsr=$con->obtenerValor($query);
			if($idUsr!="")
			{
				$binario_nombre=$elemento;
				$binario_peso=filesize($dirImagen."/".$binario_nombre);
				$binario_contenido = addslashes(fread(fopen($dirImagen."/".$binario_nombre, "rb"), $binario_peso));
				$extensiones = explode(".",$elemento) ;
				$nombre = $extensiones[0] ;
				$nombre2  = $extensiones[1] ;
				$binario_tipo="image/jpeg";
				switch($nombre2)
				{
					case "jpg":
						$binario_tipo="image/jpeg";
					break;
					case "png":
						$binario_tipo="image/png";
					break;
					case "gif":
						$binario_tipo="image/gif";
					break;
					
				}
				$consulta[$x] = "UPDATE 806_fotos SET Binario='".$binario_contenido."', Nombre='".$binario_nombre."', Tamano='".$binario_peso."', Tipo='".$binario_tipo."' WHERE idUsuario=".$nombre;
				$x++;
			}
			else
				echo "Usuario no encontrado: ".$nombre."<br>";
		}
	}
	$consulta[$x]="commit";
	$x++;
	return $con->ejecutarBloque($consulta);
}

function generarFolioMovimiento($idMovimiento,$transaccion=true)
{
	global $con;
	$x=0;
	if($transaccion)
	{
		$consulta="begin";
		if($con->ejecutarConsulta($consulta))
		{
			$consulta="SELECT * FROM _649_gridFoliosMovimiento WHERE idReferencia=".$idMovimiento." and activo=1 for update";
			$fila=$con->obtenerPrimeraFila($consulta);
			$prefijo=$fila[1];
			$separador=$fila[2];
			$longitud=$fila[3];
			$numInicial=$fila[4];
			$incremento=$fila[5];
			$folioActual=$fila[6];
			if(!$con->ejecutarConsulta($consulta))
			{
				$nFolio=$prefijo.$separador.str_pad($folioActual,$longitud,"0",STR_PAD_RIGHT);						
				$query[$x]="update _649_gridFoliosMovimiento set folioActual=folioActual+".$incremento." where activo=1 and idReferencia=".$idMovimiento;
				$x++;
				$query[$x]="commit";
				$x++;
				if($con->ejecutarBloque($query))
					return $nFolio;
			}
		}
	}
	else
	{
		$consulta="SELECT * FROM _649_gridFoliosMovimiento WHERE idReferencia=".$idMovimiento." and activo=1 for update";
		$fila=$con->obtenerPrimeraFila($consulta);
		$prefijo=$fila[1];
		$separador=$fila[2];
		$longitud=$fila[3];
		$numInicial=$fila[4];
		$incremento=$fila[5];
		$folioActual=$fila[6];
		if(!$con->ejecutarConsulta($consulta))
		{
			$nFolio=$prefijo.$separador.str_pad($folioActual,$longitud,"0",STR_PAD_RIGHT);						
			$query="update _649_gridFoliosMovimiento set folioActual=folioActual+".$incremento." where activo=1 and idReferencia=".$idMovimiento;
			if($con->ejecutarBloque($query))
				return $nFolio;
		}
	}
	return 0;

}

function explotarCadena($cadena,$separador,$posObtener)
{
	$arrDatos=explode($separador,$cadena);
	if(isset($arrDatos[$posObtener]))
		return $arrDatos[$posObtener];
	return "";
}

function obtenerSubcadena($cadena,$posInicio,$nCaracteres)
{
	return substr($cadena,$posInicio,$nCaracteres);

}

function obtenerInstitucionDepto($depto)
{
	global $con;
	$consulta="select * from 817_organigrama where codigoUnidad='".$depto."'";
	$fila=$con->obtenerPrimeraFila($consulta);
	if($fila)
	{
		$consulta="SELECT COUNT(*) FROM 817_categoriasUnidades WHERE idCategoriaUnidadOrganigrama=".$fila[5]." AND adscribeInstitucion=1";
		$nReg=$con->obtenerValor($consulta);
		if($nReg>0)
			return $depto;
		else
			return obtenerInstitucionDepto($fila[7]);
	}
	return "";
}

function obtenerInstitucionPadre($depto)
{
	global $con;
	$consulta="select * from 817_organigrama where codigoUnidad='".$depto."'";
	$fila=$con->obtenerPrimeraFila($consulta);
	if($fila)
	{

		$consulta="SELECT COUNT(*) FROM 817_categoriasUnidades WHERE idCategoriaUnidadOrganigrama=".$fila[5]." AND adscribeInstitucion=1";
		$nReg=$con->obtenerValor($consulta);
		if($nReg>0)
			return $depto;
		else
		{
			if($fila[7]=="") 
				return $depto;
			else
				return obtenerInstitucionDepto($fila[7]);
		}
	}
	return "";
}

function convertirCadenaJson($obj,$arrAtributosDel=null)
{
	$reflectionClase = new ReflectionObject($obj);
	$objParam="";
	
	foreach ($reflectionClase->getProperties() as $property => $value) 
	{
		$nombre=$value->getName();
		if(($arrAtributosDel==null)||(!existeValor($arrAtributosDel,$nombre)))
		{
			$valor=$value->getValue($obj);
			$objAux='"'.$nombre.'":"'.$valor.'"';
			if($objParam=="")
				$objParam=$objAux;
			else
				$objParam.=",".$objAux;
		}
	}
	$objParam='{'.$objParam.'}';
	return $objParam;
}

function setAtributoObjJson($obj,$atributo,$valor)
{
	$resultado=false;
	eval('$resultado=isset($obj->'.$atributo.');');
	if($resultado)
	{
		eval('$obj->'.$atributo.'='.$valor.";");
		return $obj;
	}
	$cadObj=convertirCadenaJson($obj);
	
	$cadObj=substr($cadObj,0,strlen($cadObj)-1);
	$cadObj.=',"'.$atributo.'":"'.$valor.'"}';
	
	return json_decode($cadObj);
	
}

function setAtributoCadJson($cadObj,$atributo,$valor)
{
	$resultado=false;
	$obj=json_decode($cadObj);
	
	eval('$resultado=isset($obj->'.$atributo.');');
	if($resultado)
	{
		eval('$obj->'.$atributo.'="'.$valor.'";');

		$cadObj=convertirCadenaJson($obj);
	}
	else
	{

		$cadObj=substr($cadObj,0,strlen($cadObj)-1);
		$cadObj.=',"'.$atributo.'":"'.$valor.'"}';
	}

	return $cadObj;
	
}


function delAtributoObjJson($obj,$arrAtributosDel)
{
	$resultado=false;
	eval('$resultado=isset($obj->'.$atributo.');');
	if($resultado)
	{
		$cadObj=convertirCadenaJson($obj,$arrAtributosDel);
		$obj=json_decode($cadObj);
	}
	return $obj;

}

function convertirCadenaArregloJavaToArregloPHP($arrJava)
{
	$cadAux=str_replace("[[","[",$arrJava);
	$cadAux=str_replace("]]","]",$cadAux);
	$cadAux=str_replace(", ","@@",$cadAux);
	$arrAux=explode("],[",$cadAux);
	$arrFinal=array();
	foreach($arrAux as $a)
	{
		$cad=str_replace("]","",str_replace("[","",$a));
		$arrObj=explode(",",str_replace("'","",$cad));
		$temp=json_encode($arrObj);
		$temp=str_replace("@@",", ",$temp);
		$arrObj=json_decode($temp);
		array_push($arrFinal,$arrObj);
	}
	
	
	return $arrFinal;
}

function organizarBloquesHorario($arrHorario)
{
	for($x=0;$x<sizeof($arrHorario);$x++)
	{
		$h=$arrHorario[$x];
		for($y=($x+1);$y<sizeof($arrHorario);$y++)
		{
			$h2=$arrHorario[$y];
			$h1Inicio=strtotime("1984-05-10 ".$h[0]);
			$h1Fin=strtotime("1984-05-10 ".$h[1]);
			$h2Inicio=strtotime("1984-05-10 ".$h2[0]);
			$h2Fin=strtotime("1984-05-10 ".$h2[1]);
			
			if(colisionaTiempo("1984-05-10 ".$h[0],"1984-05-10 ".$h[1],"1984-05-10 ".$h2[0],"1984-05-10 ".$h2[1],true))		
			{
				//echo $h[0]."-".$h[1]. " -- ".$h2[0]."-".$h2[1]."<br>";
				if($h1Inicio<$h2Inicio)
					$arrHorario[$y][0]=date("H:i",$h1Inicio);
				if($h1Fin>$h2Fin)
					$arrHorario[$y][1]=date("H:i",$h1Fin);
				
				array_splice($arrHorario,$x,1);
				$x--;
				break;
			}
		}

	}

	return $arrHorario;	
	
}


function cabeEnIntervaloTiempo($referencia,$intervalo)
{
	$tb1=strtotime($referencia[0]);
	$tb2=strtotime($referencia[1]);
	$ti1=strtotime($intervalo[0]);
	$ti2=strtotime($intervalo[1]);
	if(($ti1<=$tb1)&&($ti1<=$tb2)&&($ti2>=$tb1)&&($ti2>=$tb2))
		return true;
	return false;
	
}

function esEliminable($tabla,$campoId,$idRegistro,$msgFinal="")
{
	global $con;
	$consulta="select count(*) from ".$tabla." where ".$campoId."=".$idRegistro;
	$nReg=$con->obtenerValor($consulta);
	if($nReg>0)
	{
		if($msgFinal!="")
			return "Este registro esta siendo refererido por otros elementos, para preservar la integridad de la informaci&oacute;n no puede ser eliminado";
	}
	return "";
}

function eliminarElementoTabla($tabla,$campoId,$idRegistro)
{
	global $con;
	$consulta="delete from ".$tabla." where ".$campoId."=".$idRegistro;

	if($con->ejecutarConsulta($consulta))
		echo  "1|";
}



function exp_to_dec($float_str)
// formats a floating point number string in decimal notation, supports signed floats, also supports non-standard formatting e.g. 0.2e+2 for 20
// e.g. '1.6E+6' to '1600000', '-4.566e-12' to '-0.000000000004566', '+34e+10' to '340000000000'
// Author: Bob
{
    // make sure its a standard php float string (i.e. change 0.2e+2 to 20)
    // php will automatically format floats decimally if they are within a certain range
    $float_str = (string)((float)($float_str));

    // if there is an E in the float string
    if(($pos = strpos(strtolower($float_str), 'e')) !== false)
    {
        // get either side of the E, e.g. 1.6E+6 => exp E+6, num 1.6
        $exp = substr($float_str, $pos+1);
        $num = substr($float_str, 0, $pos);
        
        // strip off num sign, if there is one, and leave it off if its + (not required)
        if((($num_sign = $num[0]) === '+') || ($num_sign === '-')) $num = substr($num, 1);
        else $num_sign = '';
        if($num_sign === '+') $num_sign = '';
        
        // strip off exponential sign ('+' or '-' as in 'E+6') if there is one, otherwise throw error, e.g. E+6 => '+'
        if((($exp_sign = $exp[0]) === '+') || ($exp_sign === '-')) $exp = substr($exp, 1);
        else trigger_error("Could not convert exponential notation to decimal notation: invalid float string '$float_str'", E_USER_ERROR);
        
        // get the number of decimal places to the right of the decimal point (or 0 if there is no dec point), e.g., 1.6 => 1
        $right_dec_places = (($dec_pos = strpos($num, '.')) === false) ? 0 : strlen(substr($num, $dec_pos+1));
        // get the number of decimal places to the left of the decimal point (or the length of the entire num if there is no dec point), e.g. 1.6 => 1
        $left_dec_places = ($dec_pos === false) ? strlen($num) : strlen(substr($num, 0, $dec_pos));
        
        // work out number of zeros from exp, exp sign and dec places, e.g. exp 6, exp sign +, dec places 1 => num zeros 5
        if($exp_sign === '+') $num_zeros = $exp - $right_dec_places;
        else $num_zeros = $exp - $left_dec_places;
        
        // build a string with $num_zeros zeros, e.g. '0' 5 times => '00000'
        $zeros = str_pad('', $num_zeros, '0');
        
        // strip decimal from num, e.g. 1.6 => 16
        if($dec_pos !== false) $num = str_replace('.', '', $num);
        
        // if positive exponent, return like 1600000
        if($exp_sign === '+') return $num_sign.$num.$zeros;
        // if negative exponent, return like 0.0000016
        else return $num_sign.'0.'.$zeros.$num;
    }
    // otherwise, assume already in decimal notation and return
    else return $float_str;
}

function removeDirectory($path)
{
    $path = rtrim( strval( $path ), '/' ) ;
 
    $d = dir( $path );
 
    if( ! $d )
        return false;
 
    while ( false !== ($current = $d->read()) )
    {
        if( $current === '.' || $current === '..')
            continue;
 
        $file = $d->path . '/' . $current;
 
        if( is_dir($file) )
            removeDirectory($file);
 
        if( is_file($file) )
            unlink($file);
    }
 
    rmdir( $d->path );
    $d->close();
    return true;
}


function obtenerNombreUsuarioPaterno($idUsuario)
{
	global $con;
	$consulta="SELECT CONCAT(if(Paterno is null,'',Paterno),' ',if(Materno is null,'',Materno),' ',if(Nom is null,'',Nom)) AS nUsuario from 802_identifica WHERE idUsuario=".$idUsuario;

	return $con->obtenerValor($consulta);
}

function generarFolioPedido($idAlmacen)
{
	global $con;
	$x=0;
	$consulta="begin";
	if($con->ejecutarConsulta($consulta))
	{
		$consulta="SELECT folioPedidoActual FROM 903_variablesSistema FOR update";
		$folioPedido=$con->obtenerValor($consulta);
		$consulta="update 903_variablesSistema set folioPedidoActual=folioPedidoActual+1";
		if($con->ejecutarConsulta($consulta))
		{
			$consulta="commit";
			if($con->ejecutarConsulta($consulta))
				return $folioPedido;
		}
	}
	return -1;
}

function parteEntera($num,$redondear=true)
{
	$arrNum=explode(".",$num);
	
	if($redondear)
	{
		$decimal=0;
		if(sizeof($arrNum)>1)
			$decimal="0.".$arrNum[1];
		if($decimal>0.5)
			$arrNum[0]++;
	}
	if($arrNum[0]=="")
		$arrNum[0]=0;
	return $arrNum[0];
}

function parteDecimal($num)
{
	$arrNum=explode(".",$num);
	if(sizeof($arrNum)>1)
		return $arrNum[1];
	return 0;
}

function truncarValor($valor,$numDecimales)
{
	$arrValor=explode(".",$valor);
	if($numDecimales==0)
		return $arrValor[0];
	else
	{
		$cadena="";
		$decimales=0;
		if(sizeof($arrValor)>1)
		{
			$decimales=substr($arrValor[1],0,$numDecimales);
		}
		$cadena=str_pad($decimales,$numDecimales,"0",STR_PAD_RIGHT);
		return $arrValor[0].".".$cadena;
	}
}

function convertirValoresPorcentage($arrValores,$precision=0)
{
	$arrValoresNuevo=array();
	$nElementos=sizeof($arrValores);
	$total=0;
	$nElementos=0;
	foreach($arrValores as $id=>$valor)
	{
		$total+=$valor;
		$nElementos++;
	}

	$totalAcumulado=0;
	$ct=1;
	$ignorar=false;
	$ultimoValor="";
	foreach($arrValores as $id=>$valor)
	{
		$ultimoValor=$id;
		
		if((!$ignorar)&($ct!=$nElementos))
		{
			if($total!=0)
				$porcentaje=truncarValor(($valor/$total)*100,$precision);
			else
				$porcentaje=0;	
			
			$arrValoresNuevo[$id]=$porcentaje;
			$totalAcumulado+=$porcentaje;
			if(($ct+1)!=$nElementos)
				$ct++;
			else
				$ignorar=true;
			
		}
	}
	$porcentaje=truncarValor((100-$totalAcumulado),$precision);
	$arrValoresNuevo[$ultimoValor]=$porcentaje;
	return $arrValoresNuevo;
}

function verificarComprobanteSATSelloDigital($rfc,$tipoComprobante,$serie,$folio,$nAprobacion)
{
	$escape="\r\n";
	$host="ssl://www.consulta.sat.gob.mx";
	$carpeta="/SICOFI_WEB/ModuloSituacionFiscal/VerificacionComprobantes.asp";
	
	$c = fsockopen($host, 443, $errno, $errstr, 30);
	$datos = "RFCExp=".$rfc."&TipoComp=".$tipoComprobante."&Serie=".$serie."&FolComp=".$folio."&NumApro=".$nAprobacion;
	$size = strlen($datos);
	$host="www.consulta.sat.gob.mx";
	fputs($c, "POST " . $carpeta . " HTTP/1.1".$escape);
	fputs($c, "Host: " . $host .$escape);
	fputs($c, "Content-Type: application/x-www-form-urlencoded".$escape);
	fputs($c, "Content-Length: " . $size .$escape.$escape);
	fputs($c, $datos .$escape.$escape);
	fputs($c, "Connection: close".$escape.$escape);
	$datoss="";
	while(!feof($c))
	{
		$datoss .= fgets($c,4096);
	}
	fclose($c);
	if(strpos($datoss,"error '80004005'")!==false)
		return 2;
	if(strpos($datoss,"se encuentran registrados en los controles del Servicio de")===false)
		return 0;
	return 1;
}

function obtenerPosFilaAsoc($arreglo,$columna,$valor)
{
	$ct=0;
	foreach($arreglo as $a)
	{
		if($a[$columna]==$valor)
		{
			return $ct;
		}
		$ct++;
	}
	return -1;
}

function esEmail($email)  
{  
    $reg = "#^(((([a-z\d][\.\-\+_]?)*)[a-z0-9])+)\@(((([a-z\d][\.\-_]?){0,62})[a-z\d])+)\.([a-z\d]{2,6})$#i";  
    return preg_match($reg, $email);  
} 

function validarCurp($curp)
{
	$validador="#^[A-Z]{4}[0-9]{6}[A-Z]{6}[0-9]{2}$#i";
	return preg_match($validador, $curp);  
}

function validadRfc($rfc)
{
	$okRfc = 0;
	$rfc = str_replace("-", "", $rfc);
	if(strlen($rfc)>=10 and strlen($rfc)<=13)
	{
		$subsLetras = substr($rfc, 0, 4);
		$subsNumeros = substr($rfc, 4, 10);
		if ( !is_numeric($subsLetras) )
		{
			if(is_numeric($subsNumeros))
			{
				$okRfc = 1;  
			}
			else
			{
				$okRfc = 0;
			}
		}
		else
		{
			$okRfc = 0;
		}
	}
	else
	{
		$okRfc = 0;
	}
}


function trimComillas($cadena)
{
	$cadAux;
	if((substr($cadena,0,1)=="'")||(substr($cadena,0,1)=="\""))
	{
		$cadena=substr($cadena,1);
	}
	
	if((substr($cadena,(strlen($cadena)-1),1)=="'")||(substr($cadena,(strlen($cadena)-1),1)=="\""))
	{
		$cadena=substr($cadena,0,(strlen($cadena)-2));
	}

	return $cadena;
	
}

function normalizarQueryProyeccionOptimizacion($consulta,$arrCampos)
{
	
	
	$arrConsulta=explode(" from ",$consulta);
	

	$arrCamposAux="";
	$aToken=explode(",",$arrCampos);
	foreach($aToken as $t)
	{
		
		if(strpos($t,"'")===false)
		{
			$t="if(".$t." is null,'',".$t.")";
		}
		
		if($arrCamposAux=="")	
			$arrCamposAux=$t;
		else
			$arrCamposAux.=",".$t;
	}


	$consulta="select concat(".$arrCamposAux.") from ".$arrConsulta[1];

	return $consulta;
}

function normalizarValorRGB($valor)
{
	$valorTmp="";
	if(strlen($valor)==3)
	{
		for($x=0;$x<3;$x++)	
		{
			$valorTmp.=$valor[$x].$valor[$x];
		}
	}
	else
		$valorTmp=$valor;
	return $valorTmp;
}

function normalizarEspacios($cadena)
{
	$pos=strpos($cadena,"  ");
	$continuar=true;
	if($pos===false)
		$continuar=false;
	while($continuar)
	{
		$cadena=str_replace("  "," ",$cadena);
		$pos=strpos($cadena,"  ");
		if($pos===false)
			$continuar=false;
	}
	return $cadena;
}


function convertirFechaLetra($fechaInicial,$incluirDia=false,$ordinal1=false)
{
	global $arrMesLetra;
	global $arrDiasSemana;
	$leyfecha="";
	$fecha=strtotime($fechaInicial);
	$diaMes=date("d",$fecha);
	if(($ordinal1)&&(($diaMes*1)==1))
		$diaMes="1ro.";
	$leyfecha=$diaMes." de ".$arrMesLetra[date("m",$fecha)-1]." de ".date("Y",$fecha);
	if($incluirDia)
	{
		$dia=date("w",$fecha);
		$leyfecha=$arrDiasSemana[$dia]." ".$leyfecha;
	}
	return $leyfecha;
	
}

function existeValorArregloObjetos($arreglo,$valor,$atributo)
{
	$nElementos=sizeof($arreglo);
	for($x=0;$x<$nElementos;$x++)
	{
		eval('$valObj=$arreglo[$x]->'.$atributo.";");
		
		if($valObj==$valor) 
		{
			
			return $x;
		}
	}
	return -1;
}

function convertirArregloAsociativoObj($arr)
{
	global $con;
	$cadObj='';
	foreach($arr as $atributo=>$valor)
	{
		$o='"'.$atributo.'":""';
		if($cadObj=="")
			$cadObj=$o;
		else
			$cadObj.=",".$o;
	}
	$cadObj='{'.$cadObj.'}';
	$obj=json_decode($cadObj);
	foreach($arr as $atributo=>$valor)
	{
		eval('$obj->'.$atributo.'=$valor;');
		
	}
	return $obj;
	
}

function generarDocumentoPDF($nombreArchivo,$descarga=false,$borrarPDF=false,$borrarOrigen=false,$nombreFinal="",$comando="libreoffice4.2",$directorioDestino="")
{

	global $baseDir;
	global $comandoLibreOffice;
	global $tipoServidor;
	$archivoOrigen=$nombreArchivo;
	if($directorioDestino=="")
		$directorioDestino=$baseDir."/archivosTmpPDF";
	
	if($baseDir=="")
		$directorioDestino="./";
	
	
	$separador="/";
	
	if(strpos($nombreArchivo,$separador)===false)
		$separador="\\";
	/*if($tipoServidor!=1)
		$separador="\\";*/

	$arrArchivo=explode($separador,$nombreArchivo);
	$arrArchivo=explode(".",$arrArchivo[sizeof($arrArchivo)-1]);
	$nombreArchivoDestino=$arrArchivo[0].".pdf";
	
	if($nombreFinal=="")
		$nombreFinal=$nombreArchivoDestino;

	$archivoDestino=$directorioDestino."/".$nombreArchivoDestino;

	if($comando=="")
		$comando="libreoffice";
	//$comando ="export HOME=/tmp && ".$comando." --headless --convert-to pdf ".$archivoOrigen." --outdir ".$directorioDestino;
	switch($comando)
	{
		case "MS_OFFICE":
			convertirWordToPDFServidorConversion($archivoOrigen,$archivoDestino);
		break;
		default:
			$comando=$comandoLibreOffice."  --headless --convert-to pdf ".$archivoOrigen." --outdir ".$directorioDestino;
			
		break;
	}
	
	$resultado=shell_exec($comando);

	if(($descarga)&&(file_exists($archivoDestino)))
	{
		header("Content-length: ".filesize($archivoDestino));
		header("Content-Disposition: attachment; filename=".$nombreFinal);
		readfile($archivoDestino);
	}
	
	
	
	if(($borrarPDF)&&(file_exists($archivoDestino)))
		unlink($archivoDestino);
	if($borrarOrigen)
		unlink($archivoOrigen);
}

function convertirWordToPDFServidorConversion($archivoOrigen,$archivoDestino)
{
	global $urlWebServicesConversionPDF;
	global $funcionWebServicesConversionPDF;
	$documentBody=leerContenidoArchivo($archivoOrigen);
	
	$cuerpoFormato="";
	$headerFooter="";
	$cabecera="";
	$footer="";
	if(strpos($documentBody,"<tagheader>")!==false)
	{
		$arrCuerpo=explode("<tagheader>",$documentBody);
		
		if(sizeof($arrCuerpo)>0)					
		{
			$cuerpoFormato=$arrCuerpo["0"];
			$arrCabecera=explode("</tagheader>",$arrCuerpo[1]);
			if($arrCabecera>0)
			{
				$cuerpoFormato.=$arrCabecera[1];
				$cabecera=trim($arrCabecera[0]);
			}
			
		}
	}
	else
		$cuerpoFormato=$documentBody;
	
	if(strpos($cuerpoFormato,"<tagfooter>")!==false)
	{	
		$arrCuerpo=explode("<tagfooter>",$cuerpoFormato);
		
		if(sizeof($arrCuerpo)>0)					
		{
			$cuerpoFormato=$arrCuerpo["0"];
			$arrCabecera=explode("</tagfooter>",$arrCuerpo[1]);
			if($arrCabecera>0)
			{
				$cuerpoFormato.=$arrCabecera[1];
				$footer=trim($arrCabecera[0]);
			}
			
		}
	}
	
	if(strpos($cuerpoFormato,"<tagfooter class=\"cwjdsjcs_not_editable\">")!==false)
	{	
		$arrCuerpo=explode("<tagfooter class=\"cwjdsjcs_not_editable\">",$cuerpoFormato);
		
		if(sizeof($arrCuerpo)>0)					
		{
			$cuerpoFormato=$arrCuerpo["0"];
			$arrCabecera=explode("</tagfooter>",$arrCuerpo[1]);
			if($arrCabecera>0)
			{
				$cuerpoFormato.=$arrCabecera[1];
				$footer=trim($arrCabecera[0]);
			}
			
		}
	}
	
	if($cuerpoFormato!="")
		$documentBody=$cuerpoFormato;
						
	
	
	if(($cabecera!="")||($footer!=""))
	{
		
		$headerFooter='<html xmlns:v="urn:schemas-microsoft-com:vml"
						xmlns:o="urn:schemas-microsoft-com:office:office"
						xmlns:w="urn:schemas-microsoft-com:office:word"
						xmlns:m="http://schemas.microsoft.com/office/2004/12/omml"
						xmlns="http://www.w3.org/TR/REC-html40">
							<head>
								<meta http-equiv=Content-Type content="text/html; charset=windows-1252">
								<meta name=ProgId content=Word.Document>
								<meta name=Generator content="Microsoft Word 15">
								<meta name=Originator content="Microsoft Word 15">
								
							</head>
							<body lang=ES-MX>';
		
		if($cabecera!="")
		{
			//$headerFooter.='<div style=\'mso-element:header\' id=h1>'.$cabecera.'</div>';
			$documentBody=str_replace("mso-header: h1;","mso-header:url(\"[archivoHeader]_archivos/[archivoHeader]_header\") h1;",$documentBody);
			$headerFooter.=$cabecera;
		}
		else
		{
			$documentBody=str_replace("mso-header: h1;","",$documentBody);
		}
		
		if($footer!="")
		{
			//$headerFooter.='<div style=\'mso-element:footer\' id=f1>'.$footer.'</div>';
			$documentBody=str_replace("mso-footer: f1;","mso-footer:url(\"[archivoHeader]_archivos/[archivoHeader]_header\") f1;",$documentBody);
			$headerFooter.=$footer;
		}
		else
		{
			$documentBody=str_replace("mso-footer: f1;","",$documentBody);
		}
		
		$headerFooter.='	</body>
						</html>';
						
						
						
						

	}

	if($headerFooter=="")
	{
		$documentBody=str_replace("mso-header: h1;","",$documentBody);
		$documentBody=str_replace("mso-footer: f1;","",$documentBody);
	}
	
	$documentBody=str_replace('xmlns="http://www.w3.org/TR/REC-html40">','xmlns="http://www.w3.org/TR/REC-html40"><head>',$documentBody);
	$documentBody=str_replace('<meta name=Originator content="Microsoft Word 15">','<meta name=Originator content="Microsoft Word 15"><link rel=File-List href="[archivoHeader]_archivos/filelist.xml">',$documentBody);;
	$documentBody=str_replace('</style>','</style></head><body>',$documentBody);  
	
	if(strpos($documentBody,'<link rel=File-List href="[archivoHeader]_archivos/filelist.xml">')!==false)
		$documentBody.='</body></html>';      

	$client = new nusoap_client($urlWebServicesConversionPDF."?wsdl","wsdl");
	$parametros=array();
	
	$parametros["documentBody"]=bE($documentBody);
	$parametros["headerFooter"]=bE($headerFooter);
	
	

	$response = $client->call($funcionWebServicesConversionPDF, $parametros);
	
	$obj=json_decode($response[$funcionWebServicesConversionPDF."Result"]);

	if($obj->resultado==1)
	{
		escribirContenidoArchivo($archivoDestino,bD($obj->cuerpo));
		return true;
	}
	else
	{
		return false;
		//echo "Error: ".bD($obj->cuerpo);
	}
}

function generarImagenJpgDocumento($nombreArchivo,$nombreFinal="",$directorioDestino="./",$densidad=500)
{
	global $baseDir;
	global $tipoServidor;
	if($nombreFinal=="")
	{
		$separador="/";
		if($tipoServidor!=1)
			$separador="\\";
		
		$arrArchivo=explode($separador,$nombreArchivo);
		
		$arrArchivo=explode(".",$arrArchivo[sizeof($arrArchivo)-1]);

		$nombreFinal=$arrArchivo[0].".jpg";
	}
	
	if($directorioDestino=="")
		$directorioDestino="./";
	$comando="convert -density ".$densidad." ".$nombreArchivo."  -background white  ".$directorioDestino.$nombreFinal;

	$resultado=0;
	$resultado=shell_exec($comando);
	if(file_exists($directorioDestino.$nombreFinal))
		return true;
	return false;

	
}

function fJSON($consulta)
{
	global $con;
	$arrReg=$con->obtenerFilasJSON($consulta);
	return '{"numReg":"'.$con->filasAfectadas.'","registros":'.utf8_encode($arrReg).'}';
	
}

function agregarDimensionArreglo(&$arreglo,$nombre,$valor)
{
	$oDimension=array();
	$oDimension["nombre"]=$nombre;
	$oDimension["valor"]=$valor;
	array_push($arreglo,$oDimension);
}

function obtenerReferenciaPagoFormulario($idFormulario,$idRegistro,$idConcepto,$idUsuario="")
{
	global $con;
	$comp="";
	if($idUsuario!="")
		$comp="and idUsuario=".$idUsuario;
	$consulta="SELECT idReferencia FROM (
					SELECT idReferencia,COUNT(*) AS nRegistros FROM 6011_movimientosPago m,6012_detalleAsientoPago d WHERE idConcepto=".$idConcepto." AND d.idAsientoPago=m.idMovimiento ".$comp."
					AND ((idDimension=11 AND valorCampo=".$idFormulario.")||(idDimension=12 AND valorCampo=".$idRegistro.")) GROUP BY idReferencia) AS tm WHERE nRegistros>=2";
		
	$referencia=$con->obtenerValor($consulta);
	return $referencia;
}

function obtenerMatriculaInternaInscripcion($plantel,$idPlanEstudio,$idCiclo)
{
	global $con;
	$anio=date('y');
	$consultaCampus="SELECT codigoDepto FROM 817_organigrama WHERE codigoUnidad='".$plantel."'";
	$campus=$con->obtenerValor($consultaCampus);
	$areaEstudio="SELECT areaEspecialidad FROM 4500_planEstudio WHERE idPlanEstudio='".$idPlanEstudio."'";
	$area=$con->obtenerValor($areaEstudio);
	
	$consulNumero="SELECT folioActual FROM 4572_folioMatricula WHERE codigoUnidad='".$plantel."' AND idCiclo='".$idCiclo."'";
	$consecutivo=$con->obtenerValor($consulNumero);
		$x=0;
		$consulta[$x]="begin";
		$x++;
	
	if($consecutivo=="")
	{
		$numero='1';
		$consulta[$x]="INSERT INTO 4572_folioMatricula(codigoUnidad,idCiclo,folioActual)VALUES('".$plantel."','".$idCiclo."','".$numero."')";
		$x++;
	}
	else
	{
		$numero=$consecutivo+1;
		$consulta[$x]="update 4572_folioMatricula set folioActual='".$numero."' where codigoUnidad='".$plantel."' and idCiclo='".$idCiclo."'";
		$x++;
	}
	$longitud=strlen($numero);
	switch($longitud)
	{
		case 1:
			$numConsecutivo='000'.$numero;
		break;
		case 2:
			$numConsecutivo='00'.$numero;
		break;
		case 3:
			$numConsecutivo='0'.$numero;
		break;
		default:
			$numConsecutivo=$numero;
	}
		$consulta[$x]="commit";
		$x++;
		$con->ejecutarBloque($consulta);
	
	//a�o-Campus-area-numero sConsecutivo
	$registro=$anio.$campus.$area.$numConsecutivo;
	return $registro;
}


function convertirFechaLetraFechaMysql($cadena)
{
	global $arrMesLetra;
	$cadena=normalizarEspacios($cadena);
	if(strpos($cadena,"de")===false)
		return $cadena;
	$arrFecha=explode(" ",$cadena);
	$mesComp=strtolower($arrFecha[2]);
	$posMes=0;
	$enc=false;
	for($ct=0;$ct<sizeof($arrMesLetra);$ct++)
	{
		if(strtolower($arrMesLetra[$ct])==$mesComp)
		{
			$posMes=$ct;
			$enc=true;
			break;
		}
	}
	if(!$enc)
		return $cadena;
	$posMes++;
	$mes=str_pad($posMes,2,"0",STR_PAD_LEFT);
	
	$fechaFinal=$arrFecha[4]."-".$mes."-".$arrFecha[0];

	return $fechaFinal;
	
	
}

function normalizarValorConsulta($valor)
{
	$cadenaTmp=$valor;
	if($cadenaTmp[0]=="'")
		$cadenaTmp=substr($cadenaTmp,1);
	if(substr($cadenaTmp,strlen($cadenaTmp)-1,1)=="'")
	{
		$cadenaTmp=substr($cadenaTmp,0,strlen($cadenaTmp)-1);
	}
	return $cadenaTmp;
}

function crearBaseUsuario($apPaterno,$apMaterno,$nombre,$email,$adscripcion="",$departamento="",$roles="",$loginUsuario="",$passwordUsuario="",$idUsuarioSugerido=-1)
{
	global $con;
	$fechaActual=date("Y-m-d");
	$nombreCompleto=trim($nombre." ".$apPaterno." ".$apMaterno);
	$login=generarPassword();
	if($login=="")
		$login=$email;
	
	if($loginUsuario!="")
	{
		$login=$loginUsuario;
	}
		
	$passwd=generarPassword();
	if($passwordUsuario!="")
		$passwd=$passwordUsuario;
	$x=0;
	$consulta[$x]="begin";
	$x++;
	if($idUsuarioSugerido==-1)
	{
		$consulta[$x]="insert into 800_usuarios (Login,Password,Nombre,FechaActualiza,cambiarDatosUsr,cuentaActiva)VALUES('".
					cv($login)."','".cv($passwd)."','".cv($nombreCompleto)."','".cv($fechaActual)."',0,1)";
		$x++;
		$consulta[$x]="set @idUsuario:=(select last_insert_id())";
		$x++;
	}
	else
	{
		$consulta[$x]="insert into 800_usuarios (idUsuario,Login,Password,Nombre,FechaActualiza,cambiarDatosUsr,cuentaActiva)VALUES(".$idUsuarioSugerido.",'".
					cv($login)."','".cv($passwd)."','".cv($nombreCompleto)."','".cv($fechaActual)."',0,1)";
		$x++;
		$consulta[$x]="set @idUsuario:=".$idUsuarioSugerido;
		$x++;
	}
	
			
	$consulta[$x]="insert into 801_adscripcion (idUsuario,Actualizado,Institucion,codigoUnidad)VALUES(@idUsuario,0,'".$adscripcion."','".$departamento."')";
	$x++;
	$nombreCompleto=trim($apPaterno." ".$apMaterno." ".$nombre);
	$consulta[$x]="insert into 802_identifica (idUsuario,Paterno,Materno,Nom,Nombre)VALUES(@idUsuario,'".cv($apPaterno)."','".cv($apMaterno)."','".cv($nombre)."','".cv($nombreCompleto)."')";
	$x++;
	$consulta[$x]="INSERT INTO 803_direcciones (Tipo,idUsuario)VALUES(0,@idUsuario)";
	$x++;
	$consulta[$x]="INSERT INTO 803_direcciones (Tipo,idUsuario)VALUES(1,@idUsuario)";
	$x++;
	$consulta[$x]="INSERT INTO 806_fotos (idUsuario)VALUES(@idUsuario)";
	$x++;
	/*$consulta[$x]="insert into 807_usuariosVSRoles(idUsuario,idRol,idExtensionRol,codigoRol) values(@idUsuario,-1000,0,'-1000_0')";
	$x++;*/
	$arrRoles=explode(",",$roles);
	foreach($arrRoles as $r)
	{
		if(trim($r)!="")
		{
			$consulta[$x]="insert into 807_usuariosVSRoles(idUsuario,idRol,idExtensionRol,codigoRol) values(@idUsuario,".$r.",0,'".$r."_0')";
			$x++;
		}
	}
	
	
	$arrMail=explode(",",$email);
	
	foreach($arrMail as $m)
	{
	
		if(trim($m)!="")
		{
			$consulta[$x]="insert into 805_mails(Mail,Tipo,Notificacion,idUsuario) values('".cv(trim($m))."',0,1,@idUsuario)";
			$x++;
		}
	}
	$consulta[$x]="commit";
	$x++;

	if($con->ejecutarBloque($consulta))
	{
		$query="select @idUsuario";
		$idUsuario=$con->obtenerValor($query);
		if($idUsuario=="")
			$idUsuario=0;
		return $idUsuario;
	}
	return 0;
}

function generar_color($rgb,$valor,$accion=1)
{ 
    //extrec les 3 parts del color: 
    $vermell= substr($rgb,1,2); 
    $verd = substr($rgb,3,2); 
    $blau = substr($rgb,5,2); 
     
    //Converteixo de hexadecimal a decimal 
    $enter_vermell= hexdec($vermell); 
    $enter_verd= hexdec($verd); 
    $enter_blau= hexdec($blau); 
     
    //Valor que li sumarem o restarem a cada component rgb: 
    $valor = hexdec($valor); 
     
    //Calculo l'umbral del color. 
    //$umbral = 255/2; //7F en hexadecimal. 
     
    //Calculo la foscor del color entrat: 
    $foscor= ($enter_vermell + $enter_verd + $enter_blau) /3; 
     
    //El color �s clar. Per tant tenim que enfosquirlo restant-li el $valor en cada component rgb. 
    if($accion<0) //oscurecer
	{ 
        $enter_vermell = ($enter_vermell-$valor<00) ? 00 : $enter_vermell-$valor; 
        $enter_verd = ($enter_verd-$valor<00) ? 00 : $enter_verd-$valor; 
        $enter_blau = ($enter_blau-$valor<00) ? 00 : $enter_blau-$valor; 
        //if($enter_vermell-$valor<00){ $nou_enter_vermell = 00; } else { $enter_vermell=$enter_vermell-$valor; } 
        //if($enter_vermell-$valor<00){ $nou_enter_vermell = 00; } else { $enter_vermell=$enter_vermell-$valor; } 
    } 
    else
	{ 
        $enter_vermell = ($enter_vermell+$valor>255) ? 255 : $enter_vermell+$valor; 
        $enter_verd = ($enter_verd+$valor>255) ?  255 : $enter_verd+$valor; 
        $enter_blau = ($enter_blau+$valor>255) ?  255 : $enter_blau+$valor; 
    } 
    $vermell=dechex($enter_vermell); 
    $verd=dechex($enter_verd); 
    $blau=dechex($enter_blau); 
     
    $rgb=$vermell.$verd.$blau; 
    return $rgb; 
} 

function obtenerFechaActual()
{
	return date("Y-m-d");
}

function obtenerHoraActual()
{
	return date("H:i:s");
}

function convertirEnterToBR($val)
{
	return  str_replace("\r\n","<br>",$val);
}

function removerComillasLimite($cadena)
{
	if(gettype($cadena)=="boolean" )
		return $cadena;
	if(substr($cadena,0,1)=="'")
		$cadena=substr($cadena,1);
	if(substr($cadena,strlen($cadena)-1,1)=="'")
		$cadena=substr($cadena,0,strlen($cadena)-1);
	return $cadena;	
	
}

function registrarRolUsuario($idUsuario,$rol)
{
	global $con;
	$consulta="SELECT COUNT(*) FROM 807_usuariosVSRoles WHERE idUsuario=".$idUsuario." AND codigoRol='".$rol."'";
	$nReg=$con->obtenerValor($consulta);
	if($nReg==0)
	{
		$arrRol=explode("_",$rol);
		$consulta="INSERT INTO 807_usuariosVSRoles(idUsuario,idRol,idExtensionRol,codigoRol) VALUES(".$idUsuario.",".$arrRol[0].",".$arrRol[1].",'".$rol."')";
		return $con->ejecutarConsulta($consulta);
	}
	return true;

	
}

function removerRolUsuario($idUsuario,$rol)
{
	global $con;
	$consulta="DELETE FROM 807_usuariosVSRoles WHERE idUsuario=".$idUsuario." AND codigoRol='".$rol."'";
	return $con->ejecutarConsulta($consulta);
	
}

function convertirFechaToLetra($fecha,$codificarDia=false)
{
	global $arrDiasSemana;
	global $arrMesLetra;
	
	$fechaTime=strtotime($fecha);
	$dia=$arrDiasSemana[date("w",$fechaTime)];
	if($codificarDia)
		$dia=utf8_encode($dia);
	return $dia." ".date("d",$fechaTime)." de ".$arrMesLetra[(date("m",$fechaTime)*1)-1]." de ".date("Y",$fechaTime);
}

function convertirSegundosTiempo($segundos)
{
	$horas=parteEntera($segundos/3600,false);
	$resto=$segundos-($horas*3600);

	$minutos=parteEntera($resto/60,false);
	
	$resto=$resto-($minutos*60);
	return str_pad($horas,2,"0",STR_PAD_LEFT).":".str_pad($minutos,2,"0",STR_PAD_LEFT).":".str_pad($resto,2,"0",STR_PAD_LEFT);
}


function obtenerDesgloceAbonoAdeudoProveedor($idPedido,$idAdeudo,$cantidadAbono)
{
	global $con;
	$query="SELECT ivaPedido,total FROM 6930_pedidos WHERE idPedido=".$idPedido;
	$fVenta=$con->obtenerPrimeraFila($query);
	
	$porcIva=$fVenta[0]/$fVenta[1];
	
	$query="select sum(montoAbono) from 6936_controlPagos where idAdeudo=".$idAdeudo;
	$montoAbonado=$con->obtenerValor($query);	
	
	$saldo=$fVenta[1]-$montoAbonado;

	
	$saldoVirtual=$saldo-$cantidadAbono;
	
	$subtotal=0;
	$iva=0;
	if($saldoVirtual<=0)
	{
		$query="select sum(iva) from 6936_controlPagos where idAdeudo=".$idAdeudo;
		$totalIVA=$con->obtenerValor($query);
		$diferenciaIVA=$fVenta[0]-$totalIVA;
		$iva=$diferenciaIVA;
		$subtotal=$montoAbonado-$iva;
	}
	else
	{

		$iva=str_replace(",","",number_format($cantidadAbono*$porcIva,2));	

		$subtotal=$cantidadAbono-$iva;
	}	
	
	$datosAbono["iva"]=$iva;
	$datosAbono["subtotal"]=$subtotal;
	$datosAbono["saldoVirtual"]=$saldoVirtual;
	return $datosAbono;
	
}


function generarSemanasAnio($anio)
{
	global $con;
	$fechaInicio=$anio."-01-01";
	$fechaFin=$anio."-12-31";
	$fechaFinDte=strtotime($fechaFin);
	
	
	$consulta="SELECT COUNT(*) FROM 1050_semanasAnio WHERE anio='".$anio."'";	
	$nReg=$con->obtenerValor($consulta);
	if($nReg==0)
	{
		$x=0;
		$query[$x]="begin";
		$x++;
		$noSemana=1;
		$diaFechaInicio=date("w",strtotime($fechaInicio));
		if($diaFechaInicio==0)
			$diaFechaInicio=7;
		$diferencia=$diaFechaInicio-1;
		
		$fechaSemanaInicio=strtotime("-".$diferencia." days",strtotime($fechaInicio));
		$fechaSemanaFin=strtotime("+6 days",$fechaSemanaInicio);
		while($fechaSemanaInicio<$fechaFinDte)
		{
			$etiqueta="Semana ".$noSemana." (Del ".date("d/m/Y",$fechaSemanaInicio)." al ".date("d/m/Y",$fechaSemanaFin).")";
			$query[$x]="INSERT INTO 1050_semanasAnio(anio,fechaInicio,fechaFin,noSemana,etiqueta)
					VALUES(".$anio.",'".date("Y-m-d",$fechaSemanaInicio)."','".date("Y-m-d",$fechaSemanaFin)."',".$noSemana.",'".$etiqueta."')";
			$x++;
			$fechaSemanaInicio=strtotime("+1 days",$fechaSemanaFin);
			$fechaSemanaFin=strtotime("+6 days",$fechaSemanaInicio);
			$noSemana++;

		}

		
		
		$query[$x]="commit";
		$x++;
		return $con->ejecutarBloque($query);
	}
	return true;
}

function uC($cadena)
{
	return str_replace("\\'","'",str_replace('\\"','"',$cadena));	
}

function formatearTituloPagina($titulo,$mostrarObligatorio=false,$idValor=0,$alineacion="left")
{
	global $nConfiguracion;
	global $con;
	$sql = "SELECT *FROM 4081_colorEstilo";
    $filaEstilo= $con->obtenerPrimeraFila($sql);
	
	
	$cadRuta=generarRutaAcceso($nConfiguracion);
	
	
	$complementario="";
	switch($idValor)
	{
		case -1:
			$complementario=" [<span style='color:#000'>Nuevo</span>]";
		break;
		case 0:
			
		break;	
		default:
			$complementario=" [<span style='color:#000'>Modificaci&oacute;n</span>]";
		break;
	}
	if(!$mostrarObligatorio)
		return '<table width="100%" style="border-spacing: 0px;"><tr><td align="'.$alineacion.'"><div class="titulo" style="color:#'.$filaEstilo[3].'; font-size:11px;  height:23px"><b>'.$cadRuta.$titulo.' '.$complementario.'</b></div></td></tr></table>';	
	else
		return '<table width="100%" style="border-spacing: 0px;"><tr><td width="60%"align="'.$alineacion.'"><div class="titulo" style="color:#'.$filaEstilo[3].'; font-size:11px; height:23pxpx"><b>'.$cadRuta.$titulo.' '.$complementario.'</b></div></td>'.
				'<td width="60%"align="left"><div class="info" style="height:23pxpx">Los datos marcados con <font color="#ff0000">*</font>&nbsp; son obligatorios</div></td></tr></table>';	
}

function generarRutaAcceso($nConfiguracion)
{
	global $con;
	
	$arrRutasIgnorar[0]="../principal/inicio.php";
	$arrRutasIgnorar[0]="../principalPortal/inicio.php";
	$arrRutasIgnorar[1]="../macroProcesos/vistaMacroProceso.php";
	
	$cadenaRuta="";
	$arrRuta=array();
	$sql = "SELECT *FROM 4081_colorEstilo";
    $filaEstilo= $con->obtenerPrimeraFila($sql);
	if(isset($_SESSION["configuracionesPag"][$nConfiguracion]))
	{
		$aConf=$_SESSION["configuracionesPag"][$nConfiguracion];

		while(isset($aConf["referencia"])&&($aConf["referencia"]!="")&&($aConf["referencia"]!=0))
		{
			
			$confRef=$aConf["referencia"];
			$aConf=$_SESSION["configuracionesPag"][$confRef];
			$oP=json_decode($aConf["parametros"]);

			$oRuta=array();
			$oRuta["titulo"]=$oP->paginaConf;
			$oRuta["url"]=$oP->paginaConf;
			$oRuta["configuracion"]=$confRef;
			if(isset($aConf["tituloModulo"])&&($aConf["tituloModulo"]!=""))
				$oRuta["titulo"]=$aConf["tituloModulo"];
				
			if(!existeValor($arrRutasIgnorar,$oP->paginaConf))	
				array_push($arrRuta,$oRuta);
		}
	}
	
	$nReg=sizeof($arrRuta)-1;
	for($x=$nReg;$x>=0;$x--)
	{
		$oLink='<a href="javascript:irRuta(\''.bE($arrRuta[$x]["configuracion"]).'\',\''.bE($arrRuta[$x]["url"]).'\')" style="color:#'.$filaEstilo[2].'">'.$arrRuta[$x]["titulo"].'</a>';
		if($cadenaRuta=="")
			$cadenaRuta=$oLink.'<span style="color:#000"> >> </span>';
		else
			$cadenaRuta.=$oLink.'<span style="color:#000"> >> </span>';
	}
	
	
	return $cadenaRuta;	
}

function obtenerValorObjParametros($nConf,$param)
{
	if(!isset($_SESSION["configuracionesPag"][$nConf]))
	{
		return "";
	}
	
	$cadObj=$_SESSION["configuracionesPag"][$nConf]["parametros"];
	$obj=json_decode($cadObj);
	$existe=false;
	eval('$existe=isset($obj->'.$param.");");
	if($existe)
	{
		eval('$vValor=$obj->'.$param.";");
		return $vValor;
	}
	else
	{
		return "";
	}
	
}

function formatearValorArregloAsoc($obj,$atributo,$nE=true)
{
	$valor="";
	if($nE)	
		$valor="No especificado";
	if(isset($obj[$atributo]))
		$valor=$obj[$atributo];
	return $valor;
		
}


function obtenerEdad($fechaNacimiento)
{
	$anioActual=date("Y");
	$mesActual=date("m");
	$diaActual=date("d");
	$fNac=strtotime($fechaNacimiento);
	$diaRef=date("d",$fNac);
	$mesRef=date("m",$fNac);
	$anioRef=date("Y",$fNac);
	
	$diferenciaAnio=$anioActual-$anioRef;
	$mesesDiferencia=0;
	if(strtotime($anioActual."-".$mesRef."-".$diaRef)>strtotime(date("Y-m-d")))
	{
		$diferenciaAnio--;
	}
	return $diferenciaAnio;
	
	
	
	
	
}


function normalizarIdUsuarioTipo($idUsuario,$tipoUsuario)
{
	global $con;
	$consulta="SELECT desplazamiento FROM 908_tiposUsuarioHuella WHERE idTipoUsuarioHuella=".$tipoUsuario;
	$desplazamiento=$con->obtenerValor($consulta);
	$idUsuario-=$desplazamiento;
	return $idUsuario;
		
	
}

function rC($valor)
{
	return str_replace('"',"",$valor);	
}

function recargarMenuPrincipalProceso()
{
	echo "window.parent.mostrarMenuDTD();";
	return;	
}

function generarNombreArchivoTemporal($numAleatorios=2,$separador="_")
{
	$nombreArchivo=date("YmdHis");
	for($x=0;$x<$numAleatorios;$x++)
		$nombreArchivo.=$separador.rand(1000,9999);
	return $nombreArchivo;
}

function leerContenidoArchivo($archivo)
{
	$cuerpoArchivo="";
	if(file_exists($archivo))
	{
		$fp=fopen($archivo,"r");
		while ( ($linea = fgets($fp)) !== false) 
		{
			
			$cuerpoArchivo.=$linea;
		}
		fclose($fp);
	}
	return $cuerpoArchivo;
}

function escribirContenidoArchivo($archivo,$contenido)
{
	$fp=fopen($archivo,"w");
	
	if(!fwrite($fp,$contenido))
	{
		return false;
	}
	fclose($fp);
	
	return true;
}

function eliminarArchivo($archivo)
{
	if(($archivo!="")&&(file_exists($archivo)))	
		return unlink($archivo);
	return true;
}


function bytesToSize($bytes, $p)
{  
	$precision=0;
	if($p)
    	$precision=$p;
    $kilobyte = 1024;
    $megabyte = $kilobyte * 1024;
    $gigabyte = $megabyte * 1024;
    $terabyte = $gigabyte * 1024;
   
    if (($bytes >= 0) && ($bytes < $kilobyte)) 
	{
        return $bytes.' B';
 
    } 
	else 
		if (($bytes >= $kilobyte) && ($bytes < $megabyte)) 
		{
        	return number_format(($bytes / $kilobyte),$precision).' KB';
 
    	} 
		else 
			if (($bytes >= $megabyte) && ($bytes < $gigabyte)) 
			{
        		return number_format(($bytes / $megabyte),$precision).' MB';
 
    		} 
			else 
				if (($bytes >= $gigabyte) && ($bytes < $terabyte)) 
				{
					return number_format(($bytes / $gigabyte),$precision).' GB';

 
    			} 
				else 
					if ($bytes >= $terabyte) 
					{
						return number_format(($bytes / $terabyte),$precision).' TB';
    				} 
					else 
					{
        				return $bytes + ' B';
    				}
}

function escarparBR($cad)
{
	return 	str_replace("<br />","\r\n",$cad);
}

function obtenerEdadReferencia($fechaBase,$fechaReferencia="")
{
	
	if($fechaReferencia=="")
		$fechaReferencia=date("Y-m-d");
		
	$fB=strtotime($fechaBase);
	$fR=strtotime($fechaReferencia);
	
	
	$edad=date("Y",$fR)-date("Y",$fB);
	
	$fBaseActual=date("Y")."-".date("m",$fB)."-".date("d",$fB);

	if($fR<=strtotime($fBaseActual))
		$edad--;

	return $edad;
	
	
	
	
	
}

function obtenerHoraMicrosegundos()
{
	$mInicio=microtime();
	$arrTime=explode(" ",$mInicio);
	$arrMili=explode(".",$arrTime[0]);
	$decIni=$arrTime[1].".".$arrMili[1];
	$hInicio=date("H:i:s",$arrTime[1]).".".$arrMili[1];	
	return $hInicio;
}

function formatearValorElementoCFDI($nombreAtributo,$valor)
{
		$atributo="";
		if(trim($valor)!="")
			$atributo=' '.$nombreAtributo.'="'.str_replace('"',"",$valor).'" ';
		return $atributo;
}

function formatearValorMonetarioCFDI($valor)
{
  $valor=trim($valor);
  if($valor=="")
	  $valor=0;
  $resultado=	str_replace(",","",number_format($valor,6));
  return $resultado;
}

function generarLlaveSitio()
{
	global $urlSitio;
	$llaveSitio=str_replace("http://","",$urlSitio);
	$llaveSitio=str_replace("/","",$llaveSitio);
	$llaveSitio=str_replace(".","_",$llaveSitio);
	return $llaveSitio;
}

function convertirObjInsert($tabla,$objConversion,$insertComp,$valuesComp)
{
	$aInsert="";
	$aValues="";
	
	$reflectionClase = new ReflectionObject($objConversion);
	
	foreach ($reflectionClase->getProperties() as $property => $value) 
	{
		$nombre=$value->getName();
		$valor=$value->getValue($objConversion);
		
		if($aInsert=="")
			$aInsert=$nombre;
		else
			$aInsert.=",".$nombre;
			
		if($valor=="")
			$valor="NULL";
		
		
		$cEncierre="";
		if(($valor[0]=="'")&&($valor[strlen($valor)-1]=="'"))
		{
			$cEncierre="'";
			$valor=substr($valor,1,strlen($valor)-2);
		}
		
		
		if($aValues=="")
			$aValues=$cEncierre.cv($valor).$cEncierre;
		else
			$aValues.=",".$cEncierre.cv($valor).$cEncierre;

	}
	
	$cadena="insert into ".$tabla."(".$aInsert.(($insertComp!="")?",".$insertComp:"").") values(".$aValues.(($valuesComp!="")?",".$valuesComp:"").")";
	return $cadena;
}

function obtenerDiferenciaMinutos($horaInicial,$horaFinal)
{
	$hInicial=strtotime($horaInicial);
	$hFinal=strtotime($horaFinal);
	$arrHoraFinal=explode(" ",$horaFinal);
	$arrHoras=explode(":",$arrHoraFinal[1]);
	if($arrHoras[0]>23)
	{
		$dias= floor(($arrHoras[0]-24)/24); 
		$dias+=1;
		$horaReal=$arrHoras[0]-(24*$dias);
		$horaReal=str_pad($horaReal,2,"0",STR_PAD_LEFT);
		$horaReal.=":".$arrHoras[1].":".$arrHoras[2];
		$hFinal=strtotime(date("Y-m-d",strtotime("+".$dias." days",$hInicial))." ".$horaReal);
		
	}
	
	$diferencia=($hFinal)-$hInicial;
	return ($diferencia/60);
}

function registrarConfiguracionTableroControl($iT,$llave,$valor)
{
	if(!isset($_SESSION["tablerosControl"]))
		$_SESSION["tablerosControl"]=array();
			
	if(!isset($_SESSION["tablerosControl"][$iT]))	
		$_SESSION["tablerosControl"][$iT]=array();
	
	$_SESSION["tablerosControl"][$iT][$llave]=$valor;	
		
		
		
}

function obtenerConfiguracionTableroControl($iT,$llave)
{
	if(!isset($_SESSION["tablerosControl"]))
		return NULL;
			
	if(!isset($_SESSION["tablerosControl"][$iT]))	
		return NULL;
	
	if(!isset($_SESSION["tablerosControl"][$iT][$llave]))
		return NULL;
	else
		return $_SESSION["tablerosControl"][$iT][$llave];
	
}

function obtenerEtapasProcesoArregloJavaScript($idProceso)
{
	global $con;
	$consulta="SELECT numEtapa,nombreEtapa FROM 4037_etapas WHERE idProceso=".$idProceso;
	$arrRegistros="";
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		$o="['".$fila[0]."','".removerCerosDerecha($fila[0]).".- ".cv($fila[1])."']";
		if($arrRegistros=="")
			$arrRegistros=$o;
		else
			$arrRegistros.=",".$o;
	}
	
	return "[".$arrRegistros."]";
}

function decodificarAES_Encrypt($val,&$valInicio,&$valFin)
{
	$val=bD($val);
	$valInicio=substr($val,0,8);
	$res=substr($val,8);
	$valFin=substr($res,strlen($res)-8);
	$res=substr($res,0,strlen($res)-8);
	$res=bD($res);
	$resFinal="";
	for($x=0;$x<strlen($res);$x+=2)
	{
		$resFinal.=$res[$x];
	}
	
	return $resFinal;
}

function obtenerRutaDocumento($idDocumento)
{
	global $con;	
	global $tipoServidor;
	global $arrRutasAlmacenamientoDocumentos;
	$ruta="";	
	foreach($arrRutasAlmacenamientoDocumentos  as $directorio)
	{
		$ruta=$directorio."\\documento_".$idDocumento;
		$ruta2=$directorio."\\archivo_".$idDocumento;
		
		if($tipoServidor==2)
		{
			$ruta=str_replace("/","\\",$ruta);
			$ruta2=str_replace("/","\\",$ruta2);
		}
		
		//echo $ruta." Existe: ".(file_exists($ruta)?"1":"0")."<br>";

		if(strpos($directorio,"http")!==false)
		{

			$urlRepositorio=  str_replace("\\","/",$directorio."/webServices/wsDocumentoRepositorioDocumentos.php");
			
			$client = new nusoap_client($urlRepositorio."?wsdl","wsdl");
			
			$parametros=array();
			$parametros["idDocumento"]=$idDocumento;
			$response = $client->call("existeRepositorioDocumentos", $parametros);

			if($response==1)
			{
				$urlObtenerDocumento=  str_replace("\\","/",$directorio."/paginasFunciones/obtenerDocumentoRepositorioExterno.php?id=".bE($idDocumento));
				
				return $urlObtenerDocumento;
			}
		}
		else
		{
			if(file_exists($ruta))
				return $ruta;
				
			if(file_exists($ruta2))
				return $ruta2;
		}
	}
	return "";
	
	
}

function dR($var)
{
	if($_SESSION["idUsr"]==1)
		varDump($var);
}

function obtenerDocumentoXMLSolicitud($idFormulario,$idRegistro)
{
	global $con;	
	global $arrRutasAlmacenamientoXMLSolicitudes;
	
	$ruta="";	
	foreach($arrRutasAlmacenamientoXMLSolicitudes  as $directorio)
	{
		$ruta=$directorio."\\".$idFormulario."_".$idRegistro;
		if(file_exists($ruta))
			return leerContenidoArchivo($ruta);
	}
	
	return 	"";
	
}

function registrarDocumentoXMLSolicitud($idFormulario,$idRegistro,$xml)
{
	global $arrRutasAlmacenamientoXMLSolicitudes;
	$ruta=$arrRutasAlmacenamientoXMLSolicitudes[0]."\\".$idFormulario."_".$idRegistro;
	return escribirContenidoArchivo($ruta,$xml);
}


function esDiaHabilInstitucion($fecha)
{
	global $con;
	$fechaTime=strtotime($fecha);
	$dia=date("w",$fechaTime)*1;
	if(($dia>=1)&&($dia<=5))
	{
		$consulta="SELECT COUNT(*) FROM 7022_diasNOHabiles WHERE '".date("Y-m-d",$fechaTime)."'>=fechaInicio AND 
					'".date("Y-m-d",$fechaTime)."'<=fechaTermino AND situacion=1";

		$nReg=$con->obtenerValor($consulta);
		if($nReg>0)
			return false;
		return true;
	}
	return false;
	
	
}

function obtenerSiguienteDiaHabil($fecha)
{
	if(esDiaHabilInstitucion($fecha))
		return $fecha;
	while(true)
	{
		$fecha=date("Y-m-d",strtotime("+1 day",strtotime($fecha)));
		if(esDiaHabilInstitucion($fecha))
			return $fecha;
		
	}
}

function obtenerAnteriorDiaHabil($fecha)
{
	if(esDiaHabilInstitucion($fecha))
		return $fecha;
	while(true)
	{
		$fecha=date("Y-m-d",strtotime("-1 day",strtotime($fecha)));
		if(esDiaHabilInstitucion($fecha))
			return $fecha;
		
	}
}

function ordenarPorFecha($a,$b)
{
	$hInicialA=strtotime($a[0]);
	$hFinalA=strtotime($a[1]);
	$hInicialB=strtotime($b[0]);
	$hFinalB=strtotime($b[1]);
	if($hInicialA==$hInicialB)
		return strtotime($a[1])-strtotime($b[1]);
	else
		return strtotime($a[0])-strtotime($b[0]);
}

function normalizarCaracteres($string)
{
    $string = trim($string);
    $string = str_replace(
        array('�', '�', '�', '�', '�', '�', '�', '�', '�'),
        array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
        $string
    );
 
    $string = str_replace(
        array('�', '�', '�', '�', '�', '�', '�', '�'),
        array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
        $string
    );
 
    $string = str_replace(
        array('�', '�', '�', '�', '�', '�', '�', '�'),
        array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
        $string
    );
 
    $string = str_replace(
        array('�', '�', '�', '�', '�', '�', '�', '�'),
        array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
        $string
    );
 
    $string = str_replace(
        array('�', '�', '�', '�', '�', '�', '�', '�'),
        array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
        $string
    );
 
    $string = str_replace(
        array('�', '�', '�', '�'),
        array('n', 'N', 'c', 'C',),
        $string
    );
 
    
 
 
    return $string;
}

function obtenerUltimoDiaMes($fecha)
{
	$fecha=date("Y-m-01",strtotime("+1 month",strtotime($fecha)));
	$fechaFinal=date("Y-m-d",strtotime("-1 days",strtotime($fecha)));
	return $fechaFinal;
	
	
}

function generarPasswordAleatorio($tamano=8) 
{
	$alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
	$pass = array(); //remember to declare $pass as an array
	$alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
	for ($i = 0; $i < $tamano; $i++) 
	{
		$n = rand(0, $alphaLength);
		$pass[] = $alphabet[$n];
	}
	return implode($pass); //turn the array into a string
}

function usuarioTieneRolPermitidoProceso($idProceso)
{
	global $con;
	$consulta="SELECT COUNT(*) FROM 944_actoresProcesoEtapa WHERE actor IN(".$_SESSION["idRol"].") AND tipoActor=1 AND idProceso=".$idProceso;
	$numReg=$con->obtenerValor($consulta);
	return $numReg>0;
}

function obtenerDiferenciaAniosCumplidos($fechaF,$fechaI)
{
	$fecha1=strtotime($fechaF);
	$fecha2=strtotime($fechaI);
	
	
	$diferenciaAnios=date("Y",$fecha1)-date("Y",$fecha2);
	if($diferenciaAnios>0)
	{
		$arrFechaAux=explode("-",$fechaI);
		
		$fechaAux=date("Y",$fecha1)."-".$arrFechaAux[1]."-".$arrFechaAux[2];
		if(strtotime($fechaAux)>$fecha1)
		{
			$diferenciaAnios--;
		}
		
		
	}
	return	$diferenciaAnios;
	
	
}


?>