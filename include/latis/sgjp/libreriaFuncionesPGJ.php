<?php 
include_once("latis/conexionBD.php");
include_once("latis/utiles.php");

//set_time_limit(999000);
function reportarPromocionPGJ($idFormulario,$idRegistro)
{

	if(isset($_SESSION["deshabilitarNotificaciones"])&&($_SESSION["deshabilitarNotificaciones"]))
		return true;
	global $con;
	global $pruebasPGJ;
	global $servidorPruebas;
	global $cancelarNotificacionesPGJ;
	global $urlPruebas;
	global $urlProduccion;

	$xml="";
	try
	{
		if((($servidorPruebas)&&(!$pruebasPGJ))||($cancelarNotificacionesPGJ))
			return true;
	
		
		$url = $urlProduccion;
		if($pruebasPGJ)
			$url =$urlPruebas;
		
		$consulta="SELECT * FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
		$fPromocion=$con->obtenerPrimeraFilaAsoc($consulta);
		$arrCarpetasPadre=array();
		$consulta="SELECT tipoCarpetaAdministrativa FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$fPromocion["carpetaAdministrativa"]."'";
		
		$tCarpeta=$con->obtenerValor($consulta);
		$carpetaJudicialBase="";
		switch($tCarpeta)
		{
			case 1:
				$carpetaJudicialBase=$fPromocion["carpetaAdministrativa"];
			break;
			case 5:
				$tCarpeta=3;
				obtenerCarpetasPadreIdCarpeta($fPromocion["carpetaAdministrativa"],$arrCarpetasPadre,-1);
				
				$carpetaJudicialBase=sizeof($arrCarpetasPadre)>0?$arrCarpetasPadre[0]["carpetaAdministrativa"]:"";
			break;
			case 6:
				$tCarpeta=2;
				
				obtenerCarpetasPadreIdCarpeta($fPromocion["carpetaAdministrativa"],$arrCarpetasPadre,-1);
				$carpetaJudicialBase=sizeof($arrCarpetasPadre)>0?$arrCarpetasPadre[0]["carpetaAdministrativa"]:"";
				
				
				
			break;
			default:
				return true;
			break;
		}
		
		$tipoSolicitud="2";
		$nombreSolicitud="";
		if($idFormulario==96)
		{
			$nombreSolicitud=$fPromocion["asuntoPromocion"];
			$tipoSolicitud="2";
		}
		else
		{
			$fPromocion["tipoPromociones"]=2;
			$tipoSolicitud="1";
		}
		if($fPromocion["tipoPromociones"]==2)
		{
			$consulta="SELECT tipoAudiencia FROM _4_tablaDinamica WHERE id__4_tablaDinamica=".$fPromocion["tipoAudiencia"];
			if($idFormulario==538)
			{
				$consulta="SELECT tipoAudiencia FROM _4_tablaDinamica WHERE id__4_tablaDinamica=".$fPromocion["tipoSolicitud"];
			}
			$nombreSolicitud=$con->obtenerValor($consulta);
			
		}
		else
		{
			if($nombreSolicitud=="")
				$nombreSolicitud="Promocion de Tramite";
		}
			
		if($idFormulario!=96)
		{
			$fPromocion["asuntoPromocion"]=$nombreSolicitud;
			
		}
		
		if(($idFormulario!=538) && ($fPromocion["ctrlSolicitud"]!=""))
		{
			return true;
		}
		
		
		$consulta="SELECT tipoCarpetaAdministrativa,idFormulario,idRegistro FROM 7006_carpetasAdministrativas 
					WHERE carpetaAdministrativa='".$carpetaJudicialBase."'";
					
					
		$fDatosCarpeta=$con->obtenerPrimeraFila($consulta);
		$tCarpeta=$fDatosCarpeta[0];
		$ctrlUniv="";
		$sistema=0;
		if(($fDatosCarpeta[1]!="")&&($fDatosCarpeta[1]!=-1))
		{
			if($con->existeCampo("ctrluinv","_".$fDatosCarpeta[1]."_tablaDinamica"))
			{
				$consulta="SELECT ctrluinv,sistema FROM _".$fDatosCarpeta[1]."_tablaDinamica WHERE carpetaAdministrativa='".$carpetaJudicialBase.
					"' ORDER BY  id__".$fDatosCarpeta[1]."_tablaDinamica";
				$fRegistroSolicitud=$con->obtenerPrimeraFila($consulta);
				$ctrlUniv=$fRegistroSolicitud[0];
				$sistema=$fRegistroSolicitud[1];
			}
		}
		
		if($ctrlUniv=="")
			return true;
			
		$arrDocumentos="";
		$consulta="SELECT idArchivo,nomArchivoOriginal,tamano FROM 9074_documentosRegistrosProceso d,908_archivos a WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro."
					AND a.idArchivo=d.idDocumento";
		$res=$con->obtenerFilas($consulta);				
		while($fila=mysql_fetch_row($res))
		{
			if($fila[2]<=10485760)
			{
				$documentoBase64=obtenerCuerpoDocumentoB64($fila[0]);
				$arrDocumentos.='<documentoAdjunto>
										<nombreDocumento>'.$fila[1].'</nombreDocumento><documentoBase64>'.$documentoBase64.'</documentoBase64>
								</documentoAdjunto>
								';
			}
		}
		$genero=2;
		
		if($idFormulario==96)
		{
			
			if(($fPromocion["usuarioPromovente"]!=-1)&&($fPromocion["usuarioPromovente"]!=""))
				$consulta="SELECT nombre,apellidoPaterno,apellidoMaterno,rfcEmpresa,genero,tipoPersona FROM _47_tablaDinamica WHERE id__47_tablaDinamica=".$fPromocion["usuarioPromovente"];
			else
				$consulta="SELECT '".cv($fPromocion["nombre"])."' as nombre,'".cv($fPromocion["apPaterno"])."' as apellidoPaterno,'".cv($fPromocion["apMaterno"]).
							"' as apellidoMaterno,'' as rfcEmpresa,2 as genero,1 as tipoPersona";
			
		}
		else
		{
			$consulta="SELECT '' as nombre,'' as apellidoPaterno,'' as apellidoMaterno,''as rfcEmpresa,2 as genero,1 as tipoPersona ";
			
		}
		
		$fPromovente=$con->obtenerPrimeraFilaAsoc($consulta);
		switch($fPromovente["genero"])
		{
			case 0:
				$genero=1;
			break;
			case 1:
				$genero=2;
			break;
			case 2:
				$genero=3;
			break;
		}
		if($idFormulario==96)
		{
			$consulta="SELECT id__284_tablaDinamica,cveCalidad FROM _284_tablaDinamica WHERE figuraEquivalente=".($fPromocion["figuraPromovente"]==""?-1:$fPromocion["figuraPromovente"]);
			
		}
		else
		{
			$consulta="SELECT 9999,9999";
		}
		$fCalidad=$con->obtenerPrimeraFila($consulta);
		if(!$fCalidad)
		{
			$fCalidad[0]=9999;
			$fCalidad[1]=9999;
		}
		$xml='<?xml version="1.0" encoding="ISO-8859-1"?>
				<Solicitud>
					<ctrlUinv>'.$ctrlUniv.'</ctrlUinv>
					<tipoCarpeta>'.$tCarpeta.'</tipoCarpeta>
					<carpetaAdministrativa>'.$fPromocion["carpetaAdministrativa"].'</carpetaAdministrativa>
					<descripcion><![CDATA['.$fPromocion["asuntoPromocion"].']]></descripcion>
					<tipoSolicitud>'.$tipoSolicitud.'</tipoSolicitud>  
					<fechaSolicitud>'.date("Y-m-d\TH:i:s",strtotime($fPromocion["fechaCreacion"])).'</fechaSolicitud>
					<nombreSolicitud><![CDATA['.$nombreSolicitud.']]></nombreSolicitud>
					<documentosAdjuntos>'.$arrDocumentos.'</documentosAdjuntos>				
					<Promoventes>
						<promovente>
							<calidadJuridica>'.$fCalidad[1].'</calidadJuridica>
							<figuraJuridica>'.$fCalidad[0].'</figuraJuridica>
							<tipoPersona>'.$fPromovente["tipoPersona"].'</tipoPersona>
							<nombre><![CDATA['.($fPromovente["tipoPersona"]==1?$fPromovente["nombre"]:"").']]></nombre>
							<paterno><![CDATA['.($fPromovente["tipoPersona"]==1?$fPromovente["apellidoPaterno"]:"").']]></paterno>
							<materno><![CDATA['.($fPromovente["tipoPersona"]==1?$fPromovente["apellidoPaterno"]:"").']]></materno>
							<cveSexo>'.$genero.'</cveSexo>
							<cveGenero>'.$genero.'</cveGenero>
							<razonSocial><![CDATA['.($fPromovente["tipoPersona"]==2?$fPromovente["nombre"]:"").']]></razonSocial>
							<rfc><![CDATA['.$fPromovente["rfcEmpresa"].']]></rfc>
						</promovente>
					</Promoventes>
				</Solicitud>';

		escribirContenidoArchivo("C:\\xampp\\notificacion.xml",$xml);

		$client = new nusoap_client($url,"wsdl");
		
		$parametros=array();
		$parametros["xmlSolicitud"]=$xml;
		$response = $client->call("TSJ_RegistrarSolicitud".($sistema==1?"":"FSIAP"), $parametros);

		if(isset($response["faultstring"]))
		{
			$response["RegistrarRespuestadeSolicitudResult"]["mensaje"]=0;
			$response["RegistrarRespuestadeSolicitudResult"]["error"]=$response["faultstring"];
			
		}
		else
		{
			$response["RegistrarRespuestadeSolicitudResult"]["mensaje"]=$response["TSJ_RegistrarSolicitud".($sistema==1?"":"FSIAP")."Result"]["mensaje"];
			$response["RegistrarRespuestadeSolicitudResult"]["error"]=$response["TSJ_RegistrarSolicitud".($sistema==1?"":"FSIAP")."Result"]["error"];
		}
		if($response["RegistrarRespuestadeSolicitudResult"]["mensaje"]>0)
		{
			$consulta="UPDATE _".$idFormulario."_tablaDinamica SET idSolicitud=".$response["RegistrarRespuestadeSolicitudResult"]["mensaje"].
					" WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
			$con->ejecutarConsulta($consulta);
		}
		
		$xml='<?xml version="1.0" encoding="ISO-8859-1"?>
				<Solicitud>
					<ctrlUinv>'.$ctrlUniv.'</ctrlUinv>
					<tipoCarpeta>'.$tCarpeta.'</tipoCarpeta>
					<carpetaAdministrativa>'.$fPromocion["carpetaAdministrativa"].'</carpetaAdministrativa>
					<descripcion><![CDATA['.$fPromocion["asuntoPromocion"].']]></descripcion>
					<tipoSolicitud>'.$tipoSolicitud.'</tipoSolicitud>  
					<fechaSolicitud>'.date("Y-m-d\TH:i:s",strtotime($fPromocion["fechaCreacion"])).'</fechaSolicitud>
					<nombreSolicitud><![CDATA['.$nombreSolicitud.']]></nombreSolicitud>
					<documentosAdjuntos></documentosAdjuntos>				
					<Promoventes>
						<promovente>
							<calidadJuridica>'.$fCalidad[1].'</calidadJuridica>
							<figuraJuridica>'.$fCalidad[0].'</figuraJuridica>
							<tipoPersona>'.$fPromovente["tipoPersona"].'</tipoPersona>
							<nombre><![CDATA['.($fPromovente["tipoPersona"]==1?$fPromovente["nombre"]:"").']]></nombre>
							<paterno><![CDATA['.($fPromovente["tipoPersona"]==1?$fPromovente["apellidoPaterno"]:"").']]></paterno>
							<materno><![CDATA['.($fPromovente["tipoPersona"]==1?$fPromovente["apellidoPaterno"]:"").']]></materno>
							<cveSexo>'.$genero.'</cveSexo>
							<cveGenero>'.$genero.'</cveGenero>
							<razonSocial><![CDATA['.($fPromovente["tipoPersona"]==2?$fPromovente["nombre"]:"").']]></razonSocial>
							<rfc><![CDATA['.$fPromovente["rfcEmpresa"].']]></rfc>
						</promovente>
					</Promoventes>
				</Solicitud>';
		@registrarBitacoraNotificacionPGJ($response,-1,$xml,$idFormulario,$idRegistro,2);				
		
	}
	catch(Exception $e)
	{
		$xml='<?xml version="1.0" encoding="ISO-8859-1"?>
				<Solicitud>
					<ctrlUinv>'.$ctrlUniv.'</ctrlUinv>
					<tipoCarpeta>'.$tCarpeta.'</tipoCarpeta>
					<carpetaAdministrativa>'.$fPromocion["carpetaAdministrativa"].'</carpetaAdministrativa>
					<descripcion><![CDATA['.$fPromocion["asuntoPromocion"].']]></descripcion>
					<tipoSolicitud>'.$tipoSolicitud.'</tipoSolicitud>  
					<fechaSolicitud>'.date("Y-m-d\TH:i:s",strtotime($fPromocion["fechaCreacion"])).'</fechaSolicitud>
					<nombreSolicitud><![CDATA['.$nombreSolicitud.']]></nombreSolicitud>
					<documentosAdjuntos></documentosAdjuntos>				
					<Promoventes>
						<promovente>
							<calidadJuridica>'.$fCalidad[1].'</calidadJuridica>
							<figuraJuridica>'.$fCalidad[0].'</figuraJuridica>
							<tipoPersona>'.$fPromovente["tipoPersona"].'</tipoPersona>
							<nombre><![CDATA['.($fPromovente["tipoPersona"]==1?$fPromovente["nombre"]:"").']]></nombre>
							<paterno><![CDATA['.($fPromovente["tipoPersona"]==1?$fPromovente["apellidoPaterno"]:"").']]></paterno>
							<materno><![CDATA['.($fPromovente["tipoPersona"]==1?$fPromovente["apellidoPaterno"]:"").']]></materno>
							<cveSexo>'.$genero.'</cveSexo>
							<cveGenero>'.$genero.'</cveGenero>
							<razonSocial><![CDATA['.($fPromovente["tipoPersona"]==2?$fPromovente["nombre"]:"").']]></razonSocial>
							<rfc><![CDATA['.$fPromovente["rfcEmpresa"].']]></rfc>
						</promovente>
					</Promoventes>
				</Solicitud>';
		$response["RegistrarRespuestadeSolicitudResult"]["mensaje"]=0;
		$response["RegistrarRespuestadeSolicitudResult"]["error"]=$e->getMessage();
		@registrarBitacoraNotificacionPGJ($response,-1,$xml,$idFormulario,$idRegistro,2);
	}
	
	return true;
}

function reportarTramitePGJ($idFormulario,$idRegistro)
{
	global $con;
	global $pruebasPGJ;
	global $servidorPruebas;
	global $cancelarNotificacionesPGJ;
	global $urlPruebas;
	global $urlProduccion;
	
	if((($servidorPruebas)&&(!$pruebasPGJ))||($cancelarNotificacionesPGJ))
		return true;


	$url = $urlProduccion;
	if($pruebasPGJ)
		$url =$urlPruebas;
	
	$consulta="SELECT * FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$fPromocion=$con->obtenerPrimeraFilaAsoc($consulta);
	$arrCarpetasPadre=array();
	$consulta="SELECT tipoCarpetaAdministrativa FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$fPromocion["carpetaAdministrativa"]."'";
	$tCarpeta=$con->obtenerValor($consulta);
	$carpetaJudicialBase="";
	switch($tCarpeta)
	{
		case 1:
			$carpetaJudicialBase=$fPromocion["carpetaAdministrativa"];
		break;
		case 5:
			$tCarpeta=3;
			obtenerCarpetasPadreIdCarpeta($fPromocion["carpetaAdministrativa"],$arrCarpetasPadre,-1);
			
			$carpetaJudicialBase=sizeof($arrCarpetasPadre)>0?$arrCarpetasPadre[0]["carpetaAdministrativa"]:"";
		break;
		case 6:
			$tCarpeta=2;
			obtenerCarpetasPadreIdCarpeta($fPromocion["carpetaAdministrativa"],$arrCarpetasPadre,-1);
			$carpetaJudicialBase=sizeof($arrCarpetasPadre)>0?$arrCarpetasPadre[0]["carpetaAdministrativa"]:"";
		break;
		default:
			return true;
		break;
	}
	
	$tipoSolicitud="2";
	$nombreSolicitud="";
	
	
	if(($idFormulario!=538) && ($fPromocion["ctrlSolicitud"]!=""))
	{
		return true;
	}
	
	
	$consulta="SELECT tipoCarpetaAdministrativa,idFormulario,idRegistro FROM 7006_carpetasAdministrativas 
				WHERE carpetaAdministrativa='".$carpetaJudicialBase."'";
	$fDatosCarpeta=$con->obtenerPrimeraFila($consulta);
	$tCarpeta=$fDatosCarpeta[0];
	$ctrlUniv="";
	$sistema=0;
	if(($fDatosCarpeta[1]!="")&&($fDatosCarpeta[1]!=-1))
	{
		if($con->existeCampo("ctrluinv","_".$fDatosCarpeta[1]."_tablaDinamica"))
		{
			$consulta="SELECT ctrluinv,sistema FROM _".$fDatosCarpeta[1]."_tablaDinamica WHERE carpetaAdministrativa='".$carpetaJudicialBase.
				"' ORDER BY  id__".$fDatosCarpeta[1]."_tablaDinamica";
			$fRegistroSolicitud=$con->obtenerPrimeraFila($consulta);
			$ctrlUniv=$fRegistroSolicitud[0];
			$sistema=$fRegistroSolicitud[1];
		}
	}
	
	if($ctrlUniv=="")
		return true;
	$arrDocumentos="";
	$consulta="SELECT idArchivo,nomArchivoOriginal FROM 9074_documentosRegistrosProceso d,908_archivos a WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro."
				AND a.idArchivo=d.idDocumento";
	$res=$con->obtenerFilas($consulta);				
	while($fila=mysql_fetch_row($res))
	{
		
		$documentoBase64=obtenerCuerpoDocumentoB64($fila[0]);
		$arrDocumentos.='<documentoAdjunto>
								<nombreDocumento>'.$fila[1].'</nombreDocumento><documentoBase64>'.$documentoBase64.'</documentoBase64>
						</documentoAdjunto>
						';
	}
	
	$consulta="SELECT comentarios FROM 941_bitacoraEtapasFormularios WHERE idFormulario=".$idFormulario."  AND idRegistro=".$idRegistro." AND etapaActual=6";
	$comentario=$con->obtenerValor($consulta);
	
	$xml='<?xml version="1.0" encoding="ISO-8859-1"?>
			<Tramite>
				<ctrlUinv>'.$ctrlUniv.'</ctrlUinv>
				<tipoCarpeta>'.$tCarpeta.'</tipoCarpeta>
				<idSolicitud>'.$fPromocion["idSolicitud"].'</idSolicitud>
				<carpetaAdministrativa>'.$fPromocion["carpetaAdministrativa"].'</carpetaAdministrativa>
				<fechaGeneracion>'.date("Y-m-d\TH:i:s").'</fechaGeneracion>
				<comentario><![CDATA['.cv($comentario).']]></comentario>
				<documentosAdjuntos>'.$arrDocumentos.'</documentosAdjuntos>				
			</Tramite>';
	
	//escribirContenidoArchivo("prueba.xml",$xml);
	$client = new nusoap_client($url,"wsdl");
	$parametros=array();
	$parametros["xmlTramite"]=$xml;
	$response = $client->call("TSJ_RegistrarSolucionTramite".($sistema==1?"":"FSIAP"), $parametros);
	
	if(isset($response["faultstring"]))
	{
		$response["RegistrarRespuestadeSolicitudResult"]["mensaje"]=0;
		$response["RegistrarRespuestadeSolicitudResult"]["error"]=$response["faultstring"];
		
	}
	else
	{
		$response["RegistrarRespuestadeSolicitudResult"]["mensaje"]=$response["TSJ_RegistrarSolucionTramite".($sistema==1?"":"FSIAP")."Result"]["mensaje"];
		$response["RegistrarRespuestadeSolicitudResult"]["error"]=$response["TSJ_RegistrarSolucionTramite".($sistema==1?"":"FSIAP")."Result"]["error"];
	}
	
	
	$xml='<?xml version="1.0" encoding="ISO-8859-1"?>
			<Tramite>
				<ctrlUinv>'.$ctrlUniv.'</ctrlUinv>
				<tipoCarpeta>'.$tCarpeta.'</tipoCarpeta>
				<idSolicitud>'.$fPromocion["idSolicitud"].'</idSolicitud>
				<carpetaAdministrativa>'.$fPromocion["carpetaAdministrativa"].'</carpetaAdministrativa>
				<fechaGeneracion>'.date("Y-m-d\TH:i:s").'</fechaGeneracion>
				<comentario><![CDATA['.cv($comentario).']]></comentario>
				<documentosAdjuntos></documentosAdjuntos>				
			</Tramite>';

	@registrarBitacoraNotificacionPGJ($response,-1,$xml,$idFormulario,$idRegistro,3);
	return true;	
}

function reportarCarpetaJudicalAsignadaPGJ($idFormulario,$idRegistro)
{
	global $con;
	global $pruebasPGJ;
	global $servidorPruebas;
	global $cancelarNotificacionesPGJ;
	global $urlPruebas;
	global $urlProduccion;
	
	if((($servidorPruebas)&&(!$pruebasPGJ))||($cancelarNotificacionesPGJ))
		return true;
	
	$url = $urlProduccion;
	if($pruebasPGJ)
		$url =$urlPruebas;
	$sistema=0;
	$consulta="SELECT ctrlSolicitud,idSolicitud,id__".$idFormulario."_tablaDinamica,carpetaAdministrativa,sistema FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	if($idFormulario==488)
	{
		$consulta="SELECT ctrlSolicitud,idSolicitud,s.id__46_tablaDinamica,s.carpetaAdministrativa,s.sistema FROM _488_tablaDinamica aF,_46_tablaDinamica s WHERE 
					s.id__46_tablaDinamica=aF.folioSolicitudAjuste 	AND id__488_tablaDinamica=".$idRegistro;
	}
	
	$fRegistro=$con->obtenerPrimeraFilaAsoc($consulta);
	if($fRegistro["ctrlSolicitud"]=="")
	{
		return true;
	}
	
	$sistema=$fRegistro["sistema"];
	$consulta="SELECT nombreUnidad FROM _17_tablaDinamica u,7006_carpetasAdministrativas c WHERE c.carpetaAdministrativa='".$fRegistro["carpetaAdministrativa"]."' AND 
			u.claveUnidad=c.unidadGestion";
	$unidadControl=$con->obtenerValor($consulta);
	$cadXML='<?xml version="1.0" encoding="ISO-8859-1"?> 
                <datosNotificacion>
					<idAcuse>'.$idRegistro.'</idAcuse>
					<ctrSolicitud>'.$fRegistro["ctrlSolicitud"].'</ctrSolicitud>
					<idSolicitud>'.$fRegistro["idSolicitud"].'</idSolicitud> 
					<carpetaAdministrativa>'.$fRegistro["carpetaAdministrativa"].'</carpetaAdministrativa>
					<unidadControl>'.$unidadControl.'</unidadControl>
				</datosNotificacion>';
	$client = new nusoap_client($url,"wsdl");
	$parametros=array();
		
	$parametros["xml"]=$cadXML;
	$response = $client->call("ActualizarCarpetaJudicial".($sistema==1?"":"FSIAP"), $parametros);

	@registrarBitacoraNotificacionPGJ($response,-1,$cadXML,$idFormulario,$idRegistro,7);
	
	
	
	return true;	
}

function notificaAcuerdoProcedePromocion($idFormulario,$idRegistro)
{
	try
	{
		global $con;
		global $con;
		global $pruebasPGJ;
		global $servidorPruebas;
		global $cancelarNotificacionesPGJ;
		global $urlPruebas;
		global $urlProduccion;
		if((($servidorPruebas)&&(!$pruebasPGJ))||($cancelarNotificacionesPGJ))
			return true;

		$url = $urlProduccion;
		if($pruebasPGJ)
			$url =$urlPruebas;
		
		$consulta="SELECT * FROM _96_tablaDinamica WHERE id__96_tablaDinamica=".$idRegistro;
		$fRegistro=$con->obtenerPrimeraFilaAsoc($consulta);
		$consulta="SELECT procede FROM 3300_respuestasSolicitudPromocion WHERE idFormulario=".$idFormulario." AND idReferencia=".$idRegistro;
		$procede=$con->obtenerValor($consulta);
		$arrDocumentos="";
		$consulta="SELECT * FROM 3301_documentosAsociadosRespuestaSolicitudPromocion WHERE iFormulario=".$idFormulario."  AND iRegistro=".$idRegistro;
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$consulta="SELECT nomArchivoOriginal FROM 908_archivos WHERE idArchivo=".$fila[1];
			$nombreDocumento=$con->obtenerValor($consulta);
			$documentoBase64=obtenerCuerpoDocumentoB64($fila[1]);
			$o='<documentoAdjunto>
							<nombreDocumento>Prueba.pdf</nombreDocumento>
							<documentoBase64>'.$documentoBase64.'</documentoBase64>
				</documentoAdjunto>';
			$arrDocumentos.=$o;
		}
		
		$cadXML='<?xml version="1.0" encoding="utf-8"?>
				<Auto>
					<ctrlSolicitud>'.$fRegistro["ctrlSolicitud"].'</ctrlSolicitud>
					<idSolicitud>'.$fRegistro["idSolicitud"].'</idSolicitud>
					<procede>'.$procede.'</procede>                                                
					<ctrlSolicitudInvolucra>'.$fRegistro["ctrSolicitudInvolucra"].'</ctrlSolicitudInvolucra>
					<idSolicitudInvolucra>'.$fRegistro["idSolicitudInvolucra"].'</idSolicitudInvolucra>
					<documentosAdjuntos>'.$arrDocumentos.'</documentosAdjuntos>
				</Auto>';

		$client = new nusoap_client($url,"wsdl");
		$parametros=array();
		$parametros["xmlAuto"]=$cadXML;
		
		$response = $client->call("RegistrarRespuestadeAutoConProcede".($fRegistro["sistema"]==1?"":"FSIAP"), $parametros);
		
		
		
		if(isset($response["faultstring"]))
		{
			$response["ResultadoRespuesta"]["mensaje"]=0;
			$response["ResultadoRespuesta"]["error"]=$response["faultstring"];
			
		}
		else
		{
			$response["ResultadoRespuesta"]["mensaje"]=$response["RegistrarRespuestadeAutoConProcede".($fRegistro["sistema"]==1?"":"FSIAP")."Result"]["mensaje"]==1?0:1;
			$response["ResultadoRespuesta"]["error"]=$response["RegistrarRespuestadeAutoConProcede".($fRegistro["sistema"]==1?"":"FSIAP")."Result"]["error"];
		}
		
		
		if($response["ResultadoRespuesta"]["mensaje"]==0)
		{
			$consulta="UPDATE 3300_respuestasSolicitudPromocion SET notificadoPGJ=1, fechaNotificacion='".date("Y-m-d H:i:s").
						"' WHERE idFormulario=".$idFormulario." AND idReferencia=".$idRegistro;
						
		}
		else
		{
			$consulta="UPDATE 3300_respuestasSolicitudPromocion SET notificadoPGJ=2, fechaNotificacion='".date("Y-m-d H:i:s").
						"' WHERE idFormulario=".$idFormulario." AND idReferencia=".$idRegistro;
		}
		if($con->ejecutarConsulta($consulta))
		{
			$cadXML='<?xml version="1.0" encoding="utf-8"?>
					<Auto>
						<ctrlSolicitud>'.$fRegistro["ctrlSolicitud"].'</ctrlSolicitud>
						<idSolicitud>'.$fRegistro["idSolicitud"].'</idSolicitud>
						<procede></procede>                                                
						<ctrlSolicitudInvolucra>'.$fRegistro["ctrSolicitudInvolucra"].'</ctrlSolicitudInvolucra>
						<idSolicitudInvolucra>'.$fRegistro["idSolicitudInvolucra"].'</idSolicitudInvolucra>
					</Auto>';
		
			@registrarBitacoraNotificacionPGJ($response,-1,$cadXML,$idFormulario,$idRegistro,8);
			return true;
		}
		else
			return false;
	}
	catch(Exception $e)
	{
		echo $e->getMessage();
		return false;
	}
}

function esPromocionRequiereRespuesta($idFormulario,$idRegistro)
{
	global $con;
	
	
	$consulta="SELECT cveSolicitud,idEstado FROM _96_tablaDinamica WHERE id__96_tablaDinamica=".$idRegistro;
	$fPromocion=$con->obtenerPrimeraFila($consulta);
	if($fPromocion[1]>1.4)
	{
		$cveSolicitud=$fPromocion[0];
		$consulta="SELECT requiereRespuesta FROM _285_tablaDinamica  WHERE cveTipoSolicitud='".$cveSolicitud."'";
		$requiereRespuesta=$con->obtenerValor($consulta);
		if($requiereRespuesta==1)
		{
			return 1;
		}
	}
	return 0;
	
	
}

?>