<?php 
include_once("latis/conexionBD.php");
include_once("latis/utiles.php");
include_once("latis/sgjp/funcionesAgenda.php");
include_once("latis/numeroToLetra.php");
//include_once("latis/latisErrorHandler.php");

function registrarIDExpedienteRegistro($idFormulario,$idRegistro)
{
	global $con;
	$consulta="SELECT expediente,juzgado FROM _480_tablaDinamica WHERE id__480_tablaDinamica=".$idRegistro;
	$fRegistro=$con->obtenerPrimeraFila($consulta);
	$consulta="SELECT idCarpeta FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$fRegistro[0]."' AND unidadGestion='".$fRegistro[1]."'";
	$idCarpeta=$con->obtenerValor($consulta);
	if($idCarpeta=="")
		$idCarpeta=-1;
	$consulta="UPDATE _480_tablaDinamica SET idExpediente=".$idCarpeta." WHERE id__480_tablaDinamica=".$idRegistro;
	if($con->ejecutarConsulta($consulta))
	{
		asignarSecretariaRegistro($idFormulario,$idRegistro);
		return true;
	}
}


function registrarIDExpedienteRegistroPromocion($idFormulario,$idRegistro)
{
	global $con;
	$consulta="SELECT carpetaAdministrativa,codigoInstitucion FROM _96_tablaDinamica WHERE id__96_tablaDinamica=".$idRegistro;
	$fRegistro=$con->obtenerPrimeraFila($consulta);
	$consulta="SELECT idCarpeta FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$fRegistro[0]."' AND unidadGestion='".$fRegistro[1]."'";
	$idCarpeta=$con->obtenerValor($consulta);
	if($idCarpeta=="")
		$idCarpeta=-1;
	$consulta="UPDATE _96_tablaDinamica SET idExpediente=".$idCarpeta." WHERE id__96_tablaDinamica=".$idRegistro;
	return $con->ejecutarConsulta($consulta);
}

function enviarRespuestaSolicitudAcceso($idFormulario,$idRegistro)
{
	global $con;
	$fDatosServidor=obtenerURLComunicacionServidorMateria("SW");
	$url=$fDatosServidor[0].($fDatosServidor[1]!=""?":".$fDatosServidor[1]:"");
		
	$consulta="SELECT idRegistroSolicitud FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$idRegistroSolicitud=$con->obtenerValor($consulta);
	
	$consulta="SELECT * FROM 941_bitacoraEtapasFormularios WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro." order by idRegistroEstado desc";
	$fBitacora=$con->obtenerPrimeraFilaAsoc($consulta);

	$cadObj='{"idSolicitudAcceso":"'.$idRegistroSolicitud.'","comentarios":"'.cv($fBitacora["comentarios"]).
			'","resultado":"'.($fBitacora["etapaActual"]==2?1:0).'"}';
	

	$client = new nusoap_client("http://".$url."/webServices/wsInterconexionSistemasBPM.php?wsdl","wsdl");
	$parametros=array();
	$parametros["cadObj"]=$cadObj;
	$response = $client->call("registarAutorizacionAcceso", $parametros);

	$oResp=json_decode($response);	

	if($oResp->resultado==1)
	{
		$consulta="UPDATE _".$idFormulario."_tablaDinamica SET resultadoNotificado=1 WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
		
	}
	else
	{
		$consulta="UPDATE _".$idFormulario."_tablaDinamica SET resultadoNotificado=0 WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	}
	
	return $con->ejecutarConsulta($consulta);
}

function generarDocumentoSolicitudAcceso_v1($obj,$asociarUsuarioDocumento=false)
{
	global $con;
	global $baseDir;
	
	
	$consulta="SELECT idFormulario FROM 900_formularios WHERE categoriaFormulario=1";
	$idFormulario=$con->obtenerValor($consulta);
	
	$consulta="SELECT upper(materia) FROM _".$idFormulario."_tablaDinamica WHERE claveMateria='".$obj->cveMateria."'";
	$nombreMateria=$con->obtenerValor($consulta);
	$noJuez=substr($obj->unidadGestion,1)*1;
	$noJuez=convertirNumeroOrdinal($noJuez);
	
	
	$arrValores=array();
	$arrValores["Actor"]=$obj->detalleExpediente->actor;
	$arrValores["Demandado"]=$obj->detalleExpediente->demandado==""?"______":$obj->detalleExpediente->demandado;
	$arrValores["noExpediente"]=$obj->carpetaAdministrativa;
	$arrValores["tipoJuicio"]=$obj->detalleExpediente->tipoJuicio;
	$arrValores["folioSICORE"]=date("YmdHis").rand(10,99);
	$arrValores["noJuez"]=$noJuez;
	$arrValores["nombreMateria"]=$nombreMateria;
	$arrValores["unidadGestion"]=$obj->detalleExpediente->tituloUnidad;
	
	$consulta="SELECT nombre FROM 800_usuarios WHERE idUsuario=".$_SESSION["idUsr"];
	$arrValores["nombreSolicitante"]=$con->obtenerValor($consulta);
	
	$PHPWord = new PHPWord();
	$document = $PHPWord->loadTemplate($baseDir.'\\modulosEspeciales_SICORE\\plantillas\\documentoSicore_v2.docx');	
	foreach($arrValores as $llave=>$valor)
	{
		$document->setValue("[".$llave."]",utf8_decode($valor));	
	}
	
	$nombreAleatorio=generarNombreArchivoTemporal();
	$nomArchivo=$baseDir."/archivosTemporales/".$nombreAleatorio;
	$document->save($nomArchivo);
	generarDocumentoPDF($nomArchivo,false,false,true,$nombreAleatorio,"MS_OFFICE",$baseDir."/archivosTemporales");
	
	if($asociarUsuarioDocumento)
	{
		
		$idDocumento=registrarDocumentoServidorRepositorio($nombreAleatorio.".pdf","solicitudAcceso.pdf",6,"");
	}
	else
	{
		$idDocumento=$nombreAleatorio;
		rename($baseDir."/archivosTemporales/".$nombreAleatorio.".pdf",$baseDir."/archivosTemporales/".$nombreAleatorio);
	}
	
	
	$arrResp[0]=$idDocumento;
	$arrResp[1]=$arrValores["folioSICORE"];
	return $arrResp;
	
}


function generarDocumentoSolicitudAcceso_v3($obj,$asociarUsuarioDocumento=false)
{
	global $con;
	global $baseDir;
	
	
	$consulta="SELECT idFormulario FROM 900_formularios WHERE categoriaFormulario=1";
	$idFormulario=$con->obtenerValor($consulta);
	
	$consulta="SELECT upper(materia) FROM _".$idFormulario."_tablaDinamica WHERE claveMateria='".$obj->cveMateria."'";
	$nombreMateria=$con->obtenerValor($consulta);
	$noJuez=substr($obj->unidadGestion,1)*1;
	$noJuez=convertirNumeroLetra($noJuez,false,false);
	

	$arrValores=array();
	$arrValores["Actor"]=$obj->detalleExpediente->victima;
	$arrValores["Demandado"]=$obj->detalleExpediente->imputado==""?"______":$obj->detalleExpediente->imputado;
	$arrValores["noExpediente"]=$obj->carpetaAdministrativa;
	$arrValores["delito"]=$obj->detalleExpediente->delito;
	$arrValores["folioSICORE"]=date("YmdHis").rand(10,99);
	$arrValores["unidadGestion"]=$obj->detalleExpediente->tituloUnidad;
	$arrValores["nombreDirector"]=$obj->detalleExpediente->nombreDirector;
	$consulta="SELECT nombre FROM 800_usuarios WHERE idUsuario=".$_SESSION["idUsr"];
	$arrValores["nombreSolicitante"]=$con->obtenerValor($consulta);
	
	$PHPWord = new PHPWord();
	$document = $PHPWord->loadTemplate($baseDir.'\\modulosEspeciales_SICORE\\plantillas\\documentoSicore_v3.docx');	
	foreach($arrValores as $llave=>$valor)
	{
		$document->setValue("[".$llave."]",utf8_decode($valor));	
	}
	
	$nombreAleatorio=generarNombreArchivoTemporal();
	$nomArchivo=$baseDir."/archivosTemporales/".$nombreAleatorio;
	$document->save($nomArchivo);
	generarDocumentoPDF($nomArchivo,false,false,true,$nombreAleatorio,"MS_OFFICE",$baseDir."/archivosTemporales");
	
	if($asociarUsuarioDocumento)
	{
		
		$idDocumento=registrarDocumentoServidorRepositorio($nombreAleatorio.".pdf","solicitudAcceso.pdf",6,"");
	}
	else
	{
		$idDocumento=$nombreAleatorio;
		rename($baseDir."/archivosTemporales/".$nombreAleatorio.".pdf",$baseDir."/archivosTemporales/".$nombreAleatorio);
	}
	
	
	$arrResp[0]=$idDocumento;
	$arrResp[1]=$arrValores["folioSICORE"];
	return $arrResp;
	
}


function setAcuerdoSicor($idFormulario,$idRegistro,$ejecucionManual=true)
{
	global $con;
	global $urlServidorSicore;
	return true;
	$consulta="SELECT * FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$fAcuerdo=$con->obtenerPrimeraFilaAsoc($consulta);
	$consulta="INSERT INTO 8000_bitacoraNotificacionABoletin(fechaNotificacion,documentoXML,iFormulario,iRegistro,resultado,mensaje,metodo,idUsuarioResponsable)
				VALUES('".date("Y-m-d H:i:s")."','',".$idFormulario.",".$idRegistro.",0,'','setAcuerdo(".($fAcuerdo["notificadoSICOR"]==0?"P":"M").
				")',".$_SESSION["idUsr"].")";
		$con->ejecutarConsulta($consulta);
	
	$idNotificacion=$con->obtenerUltimoID();
	try
	{
		$consulta="SELECT * FROM _489_tablaDinamica WHERE idReferencia=".$fAcuerdo["id__".$idFormulario."_tablaDinamica"];
		$fInfoPubicacion=$con->obtenerPrimeraFilaAsoc($consulta);
		
		$consulta="SELECT * FROM  7006_carpetasAdministrativas WHERE idCarpeta=".$fAcuerdo["idExpediente"];	
		$fExpediente=$con->obtenerPrimeraFilaAsoc($consulta);
		
		$consulta="SELECT folioSICOR FROM _478_tablaDinamica WHERE id__478_tablaDinamica=".$fExpediente["idRegistro"];
		$folioSICOR=$con->obtenerValor($consulta);
		$consulta="SELECT fechaFirma,idDocumento FROM 3000_formatosRegistrados WHERE idDocumento=".$fAcuerdo["idAcuerdo"];
		
		$fInfoDoc=$con->obtenerPrimeraFila($consulta);
		
		$rutaDocumento=obtenerRutaDocumento($fInfoDoc[1]);
		
		$consulta="SELECT COUNT(*) FROM _489_permisos WHERE idPadre=".$fInfoPubicacion["id__489_tablaDinamica"]." AND idOpcion=1";
		$permiso_parte1=$con->obtenerValor($consulta);
		
		$consulta="SELECT COUNT(*) FROM _489_permisos WHERE idPadre=".$fInfoPubicacion["id__489_tablaDinamica"]." AND idOpcion=2";
		$permiso_parte2=$con->obtenerValor($consulta);
		
		$consulta="SELECT COUNT(*) FROM _489_permisos WHERE idPadre=".$fInfoPubicacion["id__489_tablaDinamica"]." AND idOpcion=3";
		$permiso_parte3=$con->obtenerValor($consulta);
		
		$client = new nusoap_client($urlServidorSicore);
		
		$arrDatosExp=explode("/",$fExpediente["carpetaAdministrativa"]);
		$parametros=array();
		$parametros["id_acuerdo"]=$idRegistro;
		$parametros["id_juicio"]=$folioSICOR==""?$fAcuerdo["idExpediente"]:$folioSICOR;
		$parametros["acuerdo"]=(($arrDatosExp[0]*1)."/".$arrDatosExp[1])."-".$fAcuerdo["noAcuerdo"];
		$parametros["fechaAcuerdo"]=date("Y-m-d",strtotime($fInfoPubicacion["fechaResolucion"]));
		$parametros["fechaUltimaFirma"]=$fInfoDoc[0];
		$parametros["permiso_parte1"]=$permiso_parte1==1?"S":"N";
		$parametros["permiso_parte2"]=$permiso_parte2==1?"S":"N";
		$parametros["permiso_parte3"]=$permiso_parte3==1?"S":"N";
		$parametros["publicar_en"]=$fInfoPubicacion["publicarEn"];
		$parametros["concepto"]=$fInfoPubicacion["comentariosAdicionales"];
		$parametros["especial"]=$fInfoPubicacion["casoEspecial"]=='ninguno'?"":$fInfoPubicacion["casoEspecial"];
		$parametros["visibilidad"]=$fAcuerdo["visibilidad"];
		$parametros["tipoAcuerdo"]=$fInfoPubicacion["tipoResolucion"];
		$parametros["accion"]=$fAcuerdo["notificadoSICOR"]==0?"P":"M";	
		$parametros["acuerdoBase64"]=bE(leerContenidoArchivo($rutaDocumento));
		
		$response = $client->call("setAcuerdo", $parametros);
		$oResp=json_decode($response);	

		if(($oResp)&& isset($oResp->respuesta) && ($oResp->respuesta==1))
		{
			$consulta="UPDATE 8000_bitacoraNotificacionABoletin SET resultado=1 WHERE idNotificacion=".$idNotificacion;
			$con->ejecutarConsulta($consulta);
			
			//cambiarEtapaFormulario($idFormulario,$idRegistro,2,"",-1,"NULL","NULL",886);
			if($parametros["accion"]=="P")
				$consulta="UPDATE _487_tablaDinamica SET notificadoSICOR=1,fechaEstimadaPublicacion='".$oResp->dia_publicacion."',fechaNotificacion='".date("Y-m-d H:i:s")."',folioSICOR=".$oResp->folioAcuse." WHERE id__487_tablaDinamica=".$idRegistro;
			else
				$consulta="UPDATE _487_tablaDinamica SET notificadoSICOR=2,fechaEstimadaPublicacion='".$oResp->dia_publicacion."',fechaNotificacion='".date("Y-m-d H:i:s")."' WHERE id__487_tablaDinamica=".$idRegistro;
			
			$con->ejecutarConsulta($consulta);
			if($ejecucionManual)
				return "";
			return true;
		}
		else
		{
			$consulta="UPDATE 8000_bitacoraNotificacionABoletin SET resultado=2,mensaje='".cv($oResp->mensajeError)."' WHERE idNotificacion=".$idNotificacion;
			$con->ejecutarConsulta($consulta);
			if($ejecucionManual)
				return "<br><br>".$oResp->mensajeError;
			return false;		
		}
	}
	catch(Exception $e)
	{
		$consulta="UPDATE 8000_bitacoraNotificacionABoletin SET resultado=2,mensaje='".cv($e->getMessage())."' WHERE idNotificacion=".$idNotificacion;
		$con->ejecutarConsulta($consulta);
		if($ejecucionManual)
			return "<br><br>".$e->getMessage();
		return true;
	}
}

function cancelarAcuerdoSicor($idFormulario,$idRegistro)
{
	global $con;
	global $urlServidorSicore;
	global $arrRutasAlmacenamientoDocumentos;
	return true;
	$consulta="INSERT INTO 8000_bitacoraNotificacionABoletin(fechaNotificacion,documentoXML,iFormulario,iRegistro,resultado,mensaje,metodo,idUsuarioResponsable)
				VALUES('".date("Y-m-d H:i:s")."','',".$idFormulario.",".$idRegistro.",0,'','setAcuerdo(C)','".$_SESSION["idUsr"]."')";
	$con->ejecutarConsulta($consulta);
	
	$idNotificacion=$con->obtenerUltimoID();
	try
	{
		$consulta="SELECT * FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
		$fAcuerdo=$con->obtenerPrimeraFilaAsoc($consulta);
		
		$consulta="SELECT * FROM _489_tablaDinamica WHERE idReferencia=".$fAcuerdo["id__".$idFormulario."_tablaDinamica"];
		$fInfoPubicacion=$con->obtenerPrimeraFilaAsoc($consulta);
		
		$consulta="SELECT * FROM  7006_carpetasAdministrativas WHERE idCarpeta=".$fAcuerdo["idExpediente"];	
		$fExpediente=$con->obtenerPrimeraFilaAsoc($consulta);
		
		$consulta="SELECT folioSICOR FROM _478_tablaDinamica WHERE id__478_tablaDinamica=".$fExpediente["idRegistro"];
		$folioSICOR=$con->obtenerValor($consulta);
		$consulta="SELECT fechaFirma,idDocumento FROM 3000_formatosRegistrados WHERE idDocumento=".$fAcuerdo["idAcuerdo"];
		$fInfoDoc=$con->obtenerPrimeraFila($consulta);

		$rutaDocumento=obtenerRutaDocumento($fInfoDoc[1]);
		
		$consulta="SELECT COUNT(*) FROM _489_permisos WHERE idPadre=".$fInfoPubicacion["id__489_tablaDinamica"]." AND idOpcion=1";
		$permiso_parte1=$con->obtenerValor($consulta);
		
		$consulta="SELECT COUNT(*) FROM _489_permisos WHERE idPadre=".$fInfoPubicacion["id__489_tablaDinamica"]." AND idOpcion=2";
		$permiso_parte2=$con->obtenerValor($consulta);
		
		$consulta="SELECT COUNT(*) FROM _489_permisos WHERE idPadre=".$fInfoPubicacion["id__489_tablaDinamica"]." AND idOpcion=3";
		$permiso_parte3=$con->obtenerValor($consulta);
		
		$client = new nusoap_client($urlServidorSicore);
		
		$arrDatosExp=explode("/",$fExpediente["carpetaAdministrativa"]);
		$parametros=array();
		$parametros["id_acuerdo"]=$idRegistro;
		$parametros["id_juicio"]=$folioSICOR==""?$fAcuerdo["idExpediente"]:$folioSICOR;
		$parametros["acuerdo"]=(($arrDatosExp[0]*1)."/".$arrDatosExp[1])."-".$fAcuerdo["noAcuerdo"];
		$parametros["fechaAcuerdo"]=date("Y-m-d",strtotime($fInfoPubicacion["fechaResolucion"]));
		$parametros["fechaUltimaFirma"]=$fInfoDoc[0];
		$parametros["permiso_parte1"]=$permiso_parte1==1?"S":"N";
		$parametros["permiso_parte2"]=$permiso_parte2==1?"S":"N";
		$parametros["permiso_parte3"]=$permiso_parte3==1?"S":"N";
		$parametros["publicar_en"]=$fInfoPubicacion["publicarEn"];
		$parametros["concepto"]=$fInfoPubicacion["comentariosAdicionales"];
		$parametros["especial"]=$fInfoPubicacion["casoEspecial"]=='ninguno'?"":$fInfoPubicacion["casoEspecial"];
		$parametros["visibilidad"]=$fAcuerdo["visibilidad"];
		$parametros["tipoAcuerdo"]=$fInfoPubicacion["tipoResolucion"];
		$parametros["accion"]="C";	
		$parametros["acuerdoBase64"]=bE(leerContenidoArchivo($rutaDocumento));

		$response = $client->call("setAcuerdo", $parametros);
		
		$oResp=json_decode($response);	

		if(($oResp)&& isset($oResp->respuesta) && ($oResp->respuesta==1))
		{
			$consulta="UPDATE 8000_bitacoraNotificacionABoletin SET resultado=1 WHERE idNotificacion=".$idNotificacion;
			$con->ejecutarConsulta($consulta);
			$consulta="UPDATE _487_tablaDinamica SET notificadoSICORCancelacion=1,fechaEstimadaPublicacion=NULL,fechaPublicacion=NULL,fechaNotificacionCancelacion='".date("Y-m-d H:i:s")."',folioSICORCancelacion=NULL WHERE id__487_tablaDinamica=".$idRegistro;
			$con->ejecutarConsulta($consulta);
			$consulta="SELECT * FROM 3000_formatosRegistrados WHERE idDocumento=".$fAcuerdo["idAcuerdo"];
			$fInformacionDocumento=$con->obtenerPrimeraFilaAsoc($consulta);
			
			if($fInformacionDocumento["cuerpoFormato"]!="")
			{
				removerDocumentoSistema($fAcuerdo["idAcuerdo"]);
				$consulta="UPDATE 3000_formatosRegistrados SET firmado=0,documentoBloqueado=0,idDocumento=NULL,idDocumentoAdjunto=NULL 
							WHERE idRegistroFormato=".$fInformacionDocumento["idRegistroFormato"];
				$con->ejecutarConsulta($consulta);
			}
			else
			{
				$consulta="SELECT nomArchivoOriginal FROM 908_archivos WHERE idArchivo=".$fAcuerdo["idAcuerdo"];
				$nomArchivoOriginal=$con->obtenerValor($consulta);
				$nomArchivoOriginal=str_replace(".pdf",".doc",$nomArchivoOriginal);
				$consulta="UPDATE 908_archivos SET nomArchivoOriginal='".$nomArchivoOriginal."' WHERE idArchivo=".$fAcuerdo["idAcuerdo"];
				$con->ejecutarConsulta($consulta);
				
				$consulta="UPDATE 3000_formatosRegistrados SET firmado=0,documentoBloqueado=0 	WHERE idRegistroFormato=".$fInformacionDocumento["idRegistroFormato"];
				$con->ejecutarConsulta($consulta);
				
				
				$consulta="DELETE FROM 7007_contenidosCarpetaAdministrativa WHERE tipoContenido=1 AND idRegistroContenidoReferencia=".$fAcuerdo["idAcuerdo"];
				$con->ejecutarConsulta($consulta);

				$consulta="DELETE FROM 9074_documentosRegistrosProceso WHERE idDocumento=".$fAcuerdo["idAcuerdo"];
				$con->ejecutarConsulta($consulta);
				
				if(file_exists($arrRutasAlmacenamientoDocumentos[0]."\\documento_".$fAcuerdo["idAcuerdo"]))
				{
					$numVersion=1;
					$nombreDestino=$arrRutasAlmacenamientoDocumentos[0]."\\documento_".$fAcuerdo["idAcuerdo"].".ant";
					while(file_exists($nombreDestino))
					{
						$nombreDestino=$arrRutasAlmacenamientoDocumentos[0]."\\documento_".$fAcuerdo["idAcuerdo"].".ant".$numVersion;
						$numVersion++;
					}
					rename($arrRutasAlmacenamientoDocumentos[0]."\\documento_".$fAcuerdo["idAcuerdo"],$nombreDestino);
				}
				
				if(file_exists($arrRutasAlmacenamientoDocumentos[0]."\\documento_".$fAcuerdo["idAcuerdo"].".resp"))
				{
					rename($arrRutasAlmacenamientoDocumentos[0]."\\documento_".$fAcuerdo["idAcuerdo"].".resp",$arrRutasAlmacenamientoDocumentos[0]."\\documento_".$fAcuerdo["idAcuerdo"]);

				}
				
			}
			
			$etapaCambio=6.5;
			
			if($fAcuerdo["iFormulario"]==478)
			{
				$consulta="SELECT * FROM _".$fAcuerdo["iFormulario"]."_tablaDinamica WHERE id__".$fAcuerdo["iFormulario"].
						"_tablaDinamica=".$fAcuerdo["iReferencia"];
				$fRegistroExpediente=$con->obtenerPrimeraFilaAsoc($consulta);
				
				if($fRegistroExpediente["tipoExpediente"]==2)
				{
					$etapaCambio=6.6;
				}
				
			}
			
			cambiarEtapaFormulario($fAcuerdo["iFormulario"],$fAcuerdo["iReferencia"],$etapaCambio,"",-1,"NULL","NULL",0);
			return "";
		}
		else
		{
			$consulta="UPDATE 8000_bitacoraNotificacionABoletin SET resultado=2,mensaje='".cv($oResp->mensajeError)."' WHERE idNotificacion=".$idNotificacion;
			$con->ejecutarConsulta($consulta);
			return "<br><br>".$oResp->mensajeError;

			
		}
	}
	catch(Exception $e)
	{
		$consulta="UPDATE 8000_bitacoraNotificacionABoletin SET resultado=2,mensaje='".cv($e->getMessage())."' WHERE idNotificacion=".$idNotificacion;
		$con->ejecutarConsulta($consulta);
		return "<br><br>".$e->getMessage();

	}
}

function setJuicioSicor($idFormulario,$idRegistro)
{
	global $con;
	return true;
	global $urlServidorSicore;
	$idNotificacion="";
	
	try
	{
	
		$consulta="SELECT * FROM _478_tablaDinamica WHERE id__478_tablaDinamica=".$idRegistro;
		
		$fDatosRegistro=$con->obtenerPrimeraFilaAsoc($consulta);
		
		if($fDatosRegistro["notificadoSICOR"]!=0)
		{

			return true;
		}
		$tipo_expediente="";
		
		switch($fDatosRegistro["tipoExpediente"])
		{
			case 1:
				$tipo_expediente="EXPEDIENTE";
			break;
			case 9:
				$tipo_expediente="EXPEDIENTILLO";
			break;
		}

		$consulta="INSERT INTO 8000_bitacoraNotificacionABoletin(fechaNotificacion,documentoXML,iFormulario,iRegistro,resultado,mensaje,metodo,idUsuarioResponsable)
				VALUES('".date("Y-m-d H:i:s")."','',".$idFormulario.",".$idRegistro.",0,'','setJuicioSicor(A)','".$_SESSION["idUsr"]."')";
		$con->ejecutarConsulta($consulta);
		
		$idNotificacion=$con->obtenerUltimoID();
		$consulta="SELECT * FROM _551_tablaDinamica WHERE idReferencia=".$idRegistro;
		$fDatosExpediente=$con->obtenerPrimeraFilaAsoc($consulta);
		
		$consulta="SELECT idRegistro FROM 0_juiciosSICOR WHERE jp='".$fDatosExpediente["jP"]."' AND cveMateria='".$fDatosRegistro["materia"].
				"' AND j='".$fDatosExpediente["tiposJuicio"]."' AND ta='".$fDatosExpediente["tipoAccion"]."' AND acc='".$fDatosExpediente["accion"]."'";
		$id_catalogo_juicios=$con->obtenerValor($consulta);
		
		$client = new nusoap_client($urlServidorSicore);
		$parametros=array();
		$parametros["id_juicio_latis"]=$fDatosRegistro["id__478_tablaDinamica"];
		$parametros["tipo_expediente"]=$tipo_expediente;
		$parametros["accion"]=$fDatosExpediente["accion"];
		$parametros["expediente"]=$fDatosRegistro["noExpediente"]*1;
		$parametros["anio"]=$fDatosRegistro["anioExpediente"]*1;
		$parametros["bis"]="";
		$parametros["juzgado"]=$fDatosRegistro["codigoInstitucion"];
		$parametros["secretaria"]=$fDatosRegistro["secretariaAsignada"];
		$parametros["fecha_publicacion"]=date("Y-m-d",strtotime($fDatosRegistro["fechaCreacion"]));
		$parametros["estatus"]='abierto';//enum('abierto','cerrado','apelacion')
		$parametros["id_tipojuicio"]=$id_catalogo_juicios;//$fDatosExpediente["tiposJuicio"];
		$parametros["id_catalogo_juicios"]=$id_catalogo_juicios;
		
		
		$consulta="SELECT GROUP_CONCAT(CONCAT(IF(nombre IS NULL,'',nombre),' ',IF(apellidoPaterno IS NULL,'',apellidoPaterno),' ',
					IF(apellidoMaterno IS NULL,'',apellidoMaterno))) AS nombre FROM _47_tablaDinamica t,7005_relacionFigurasJuridicasSolicitud r 
					WHERE r.idActividad=".$fDatosRegistro["idActividad"]." AND r.idParticipante=t.id__47_tablaDinamica AND r.idFiguraJuridica=2";
		$lblActor=$con->obtenerValor($consulta);	
		
		$consulta="SELECT GROUP_CONCAT(CONCAT(IF(nombre IS NULL,'',nombre),' ',IF(apellidoPaterno IS NULL,'',apellidoPaterno),' ',
					IF(apellidoMaterno IS NULL,'',apellidoMaterno))) AS nombre FROM _47_tablaDinamica t,7005_relacionFigurasJuridicasSolicitud r 
					WHERE r.idActividad=".$fDatosRegistro["idActividad"]." AND r.idParticipante=t.id__47_tablaDinamica AND r.idFiguraJuridica=4";
		$lblDemandado=$con->obtenerValor($consulta);	
		
		$consulta="SELECT GROUP_CONCAT(CONCAT(IF(nombre IS NULL,'',nombre),' ',IF(apellidoPaterno IS NULL,'',apellidoPaterno),' ',
					IF(apellidoMaterno IS NULL,'',apellidoMaterno))) AS nombre FROM _47_tablaDinamica t,7005_relacionFigurasJuridicasSolicitud r 
					WHERE r.idActividad=".$fDatosRegistro["idActividad"]." AND r.idParticipante=t.id__47_tablaDinamica AND r.idFiguraJuridica=100";
		$lblTercerista=$con->obtenerValor($consulta);
		
		$parametros["parte1"]=$lblActor;
		$parametros["parte2"]=$lblDemandado;
		$parametros["parte3"]=$lblTercerista;
		$parametros["id_etapaprocesal"]=$fDatosExpediente["etapaProcesal"];
	
		$response = $client->call("setJuicio", $parametros);
		
		$oResp=json_decode($response);	

		if(($oResp)&& isset($oResp->respuesta) && ($oResp->respuesta==1))
		{
			$consulta="UPDATE 8000_bitacoraNotificacionABoletin SET resultado=1 WHERE idNotificacion=".$idNotificacion;
			$con->ejecutarConsulta($consulta);
			
			$x=0;
			$query=array();
			$query[$x]="begin";
			$x++;
			
			$query[$x]="UPDATE _478_tablaDinamica SET notificadoSICOR=1,fechaNotificacion='".date("Y-m-d H:i:s")."',folioSICOR=".$oResp->folioAcuse." WHERE id__478_tablaDinamica=".$idRegistro;
			$x++;
			$query[$x]="commit";
			$x++;
			$con->ejecutarBloque($query);
		}
		else
		{
			$consulta="UPDATE 8000_bitacoraNotificacionABoletin SET resultado=2,mensaje='".cv($oResp->mensajeError)."' WHERE idNotificacion=".$idNotificacion;
			$con->ejecutarConsulta($consulta);
		}
	}
	catch(Exception $e)
	{
		$consulta="UPDATE 8000_bitacoraNotificacionABoletin SET resultado=2,mensaje='".cv($e->getMessage())."' WHERE idNotificacion=".$idNotificacion;
		$con->ejecutarConsulta($consulta);
		echo "<br><br>".$e->getMessage();

	}
}

function getAcuerdoPDF($idAcuerdo)
{
	global $con;
	global $urlServidorSicore;
	$client = new nusoap_client($urlServidorSicore);
	$parametros=array();
	$parametros["id_acuerdo"]=$idAcuerdo;
	
	

	$response = $client->call("getAcuerdoURL", $parametros);
	
	$oResp=json_decode($response);	
	
	if(($oResp)&& isset($oResp->respuesta) && ($oResp->respuesta==1))
	{
		return $oResp->acuerdoBase64;
	}
}


function llenarPlantillaDocumentoJuzgado($idFormulario,$idRegistro)
{
	global $con;
	$juzgado=$_SESSION["codigoInstitucion"];
	$consulta="SELECT idFormulario,idReferencia FROM 7035_informacionDocumentos WHERE idRegistro=".$idRegistro;
	$fDatosBase=$con->obtenerPrimeraFila($consulta);
	if($fDatosBase)
	{
		$consulta="SELECT codigoInstitucion FROM  _".$fDatosBase[0]."_tablaDinamica WHERE id__".$fDatosBase[0]."_tablaDinamica=".$fDatosBase[1];
		$juzgado=$con->obtenerValor($consulta);
	}
	$consulta="SELECT upper(nombreUnidad) FROM _17_tablaDinamica WHERE claveUnidad='".$juzgado."'";

	$arrValores["juzgado"]=trim($con->obtenerValor($consulta));
	
	return $arrValores;
}

function registrarAsignacionDocumentosAtencion($idFormulario,$idRegistro)
{
	global $con;
	$consulta="SELECT idEstado FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$idEstado=$con->obtenenrValor($consulta);
}

function enviarResponsableUltimaFirma($idFormulario,$idRegistro,$actor)
{
	global $con;
	
	$consulta="SELECT * FROM 7035_informacionDocumentos WHERE idFormulario=".$idFormulario." AND idReferencia=".$idRegistro;
	$fDatosDocumento=$con->obtenerPrimeraFila($consulta);
	$consulta="SELECT idRegistroFormato FROM 3000_formatosRegistrados WHERE idFormulario=-2 AND idRegistro=".$fDatosDocumento[0];
	$idDocumentoFormato=$con->obtenerValor($consulta);
	$idCarpetaAdministrativa="";
	$nomTabla="_".$idFormulario."_tablaDinamica";
	if($con->existeCampo("idExpediente",$nomTabla))
	{
		$consulta="SELECT idExpediente FROM ".$nomTabla." WHERE id_".$nomTabla."=".$idRegistro;
		$idCarpetaAdministrativa=$con->obtenerValor($consulta);
	}
	else
	{
		if($con->existeCampo("idCarpetaAdministrativa",$nomTabla))
		{
			$consulta="SELECT idCarpetaAdministrativa FROM ".$nomTabla." WHERE id_".$nomTabla."=".$idRegistro;
			$idCarpetaAdministrativa=$con->obtenerValor($consulta);
		}
		else
		{
			$consulta="SELECT codigoInstitucion FROM ".$nomTabla." WHERE id_".$nomTabla."=".$idRegistro;
			$codigoInstitucion=$con->obtenerValor($consulta);
			$consulta="SELECT idCarpeta FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$fDatosDocumento[9].
					"' AND unidadGestion='".$codigoInstitucion."'";
			$idCarpetaAdministrativa=$con->obtenerValor($consulta);
		}
	}
	
	
	$arrRemitentes=obtenerRemitenteTarea($idFormulario,$idRegistro);
	foreach($arrRemitentes as $r)
	{
		$consulta="SELECT COUNT(*) FROM 3000_documentosAsignadosAtencion WHERE iFormulario=".$idFormulario." AND iReferencia=".
				$idRegistro." AND idResponsableAtencion=".$r->idUsuarioDestinatario;
		
		$nRegistros=$con->obtenerValor($consulta);
		if($nRegistros==0)
		{
		
			$consulta="INSERT INTO 3000_documentosAsignadosAtencion(idDocumentoFormato,situacionActual,fechaAsignacion,idCarpetaAdministrativa,
						iFormulario,iReferencia,idResponsableAtencion,actor,idInformacionDocumento) values(
						".$idDocumentoFormato.",0,'".date("Y-m-d H:i:s")."',".$idCarpetaAdministrativa.",".$idFormulario.",".$idRegistro.",".
						$r->idUsuarioDestinatario.",".$actor.",".$fDatosDocumento[0].")";
		
			if($con->ejecutarConsulta($consulta))
			{
				return true;
			}
		}
		
	}
}

function obtenerCuerpoDocumentoSICORB64($idDocumento)
{
	global $con;
	$consulta = "SELECT nomArchivoOriginal,documento,tipoArchivo,tamano,enBD,documentoRepositorio FROM 908_archivos WHERE idArchivo=".$idDocumento;
	$fila=$con->obtenerPrimeraFila($consulta);
	$rutaDocumento=obtenerRutaDocumento($idDocumento);
	if($rutaDocumento!="")
	{
		if(strpos($rutaDocumento,"http")!==false)
		{
			$cuerpoDocumento=file_get_contents($rutaDocumento);
			return $cuerpoDocumento;
		}
		else
		{
			$cuerpoDocumento=leerContenidoArchivo($rutaDocumento);
			return bE($cuerpoDocumento);
		}
	}
	else
	{
		if($fila[5]!="")
		{
			$cadObj=file_get_contents("http://10.2.51.41:8000/api/document?instanceName=tsj&idGlobal=".$fila[5]);
			$objDocumento=json_decode($cadObj);
			return $objDocumento->file64;
			
		}
		else
			return bE(file_get_contents("http://10.19.5.9/paginasFunciones/obtenerDocumentoEditorArchivos.php?id=".$idDocumento."&nombreArchivo=".$fila[0]));
	}
}


function determinarSecretariaExpediente($carpetaAdministrativa)
{
	$arrExpediente=explode("/",$carpetaAdministrativa);
	
	$secretaria=($arrExpediente[0]%2)==1?"A":"B";
	return $secretaria;
}

function asignarSecretariaRegistro($idFormulario,$idRegistro)
{
	global $con;
	$carpetaAdministrativa=obtenerCarpetaAdministrativaProceso($idFormulario,$idRegistro);
	$consulta="SELECT codigoInstitucion FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$codigoInstitucion=$con->obtenerValor($consulta);
	
	$consulta="SELECT secretariaAsignada FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$carpetaAdministrativa."' AND unidadGestion='".$codigoInstitucion."'";
	$secretariaAsignada=$con->obtenerValor($consulta);
	if($secretariaAsignada=="")
		$secretariaAsignada=determinarSecretariaExpediente($carpetaAdministrativa);
	$consulta="update _".$idFormulario."_tablaDinamica set secretariaAsignada='".$secretariaAsignada."' WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	return $con->ejecutarConsulta($consulta);
}


function esRegistroAdscritoSecretariaA($idFormulario,$idRegistro)
{
	global $con;
	$carpetaAdministrativa=obtenerCarpetaAdministrativaProceso($idFormulario,$idRegistro);
	$consulta="SELECT codigoInstitucion FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$codigoInstitucion=$con->obtenerValor($consulta);
	$consulta="SELECT secretariaAsignada FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$carpetaAdministrativa."' AND unidadGestion='".$codigoInstitucion."'";
	$secretariaAsignada=$con->obtenerValor($consulta);
	if($secretariaAsignada=="A")
		return 1;
	return 0;
}

function esRegistroAdscritoSecretariaB($idFormulario,$idRegistro)
{
	global $con;
	$carpetaAdministrativa=obtenerCarpetaAdministrativaProceso($idFormulario,$idRegistro);
	$consulta="SELECT codigoInstitucion FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$codigoInstitucion=$con->obtenerValor($consulta);
	$consulta="SELECT secretariaAsignada FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$carpetaAdministrativa."' AND unidadGestion='".$codigoInstitucion."'";
	$secretariaAsignada=$con->obtenerValor($consulta);
	if($secretariaAsignada=="B")
		return 1;
	return 0;
}


function esUsuarioSecretario()
{
	global $con;
	if(existeRol("'153_0'")||existeRol("'155_0'")||existeRol("'156_0'"))
		return 1;
	return 0;
}

function esUsuarioAuxiliarSecretario()
{
	global $con;
	if(existeRol("'163_0'")||existeRol("'164_0'")||existeRol("'165_0'"))
		return 1;
	return 0;
}

function obtenerUsuariosInvolucradosDocumento($idFormulario,$idRegistro,$idActorProceso)
{
	global $con;
	
	$rolesExclusion=array();
	$rolesExclusion["157_0"]=1;
	$arrDestinatario=array();
	$consulta="SELECT * FROM 941_bitacoraEtapasFormularios WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro;
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_assoc($res))
	{
		$arrDestinatario[$fila["idUsuarioCambio"]]=1;
	}
	
	$consulta="SELECT actor FROM 944_actoresProcesoEtapa WHERE idActorProcesoEtapa=".$idActorProceso;

	$rolActor=$con->obtenerValor($consulta);
	
	$arrDestinatarios=array();	
	
	$rolActor=obtenerTituloRol($rolActor);
	foreach($arrDestinatario as $idUsuario=>$resto)
	{
		$considerarUsuario=true;
		foreach($rolesExclusion as $rol=>$resto)
		{
			$consulta="SELECT COUNT(*) FROM 807_usuariosVSRoles WHERE idUsuario=".$idUsuario." AND codigoRol='".$rol."'";

			$nReg=$con->obtenerValor($consulta);
			if($nReg>0)
			{
				$considerarUsuario=false;
				break;
			}
		
		}
		
		if($considerarUsuario)
		{
			$nombreUsuario=obtenerNombreUsuario($idUsuario)." (".$rolActor.")";
			
			$o='{"idUsuarioDestinatario":"'.$idUsuario.'","nombreUsuarioDestinatario":"'.$nombreUsuario.'"}';
			$oDestinatario=json_decode($o);
			array_push($arrDestinatarios,$oDestinatario);
		}
	}
	
	
	return $arrDestinatarios;
	
}


function validarDuplicidadExpediente($idFormulario,$idRegistro)
{
	global $con;
	$query="SELECT idActividad,codigoInstitucion,carpetaAdministrativa,juez,secretario,tipoExpediente FROM _478_tablaDinamica WHERE id__478_tablaDinamica=".$idRegistro;
	$fRegistro=$con->obtenerPrimeraFila($query);
	$carpetaAdministrativa=$fRegistro[2];
	
	$consulta="SELECT * FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa = '".$carpetaAdministrativa."' AND unidadGestion='".$fRegistro[1].
				"' and idRegistro<>".$idRegistro;

	$fila=$con->obtenerPrimeraFilaAsoc($consulta);
	
	if($fila)
	{
		$consulta="SELECT GROUP_CONCAT(CONCAT(IF(nombre IS NULL,'',nombre),' ',IF(apellidoPaterno IS NULL,'',apellidoPaterno),' ',IF(apellidoMaterno IS NULL,'',apellidoMaterno))) FROM 7005_relacionFigurasJuridicasSolicitud r,_47_tablaDinamica p 
				WHERE r.idActividad=".$fila["idActividad"]." and idFiguraJuridica=4 AND r.idParticipante=p.id__47_tablaDinamica order by nombre,apellidoPaterno,apellidoMaterno";

		$demandados=$con->obtenerValor($consulta);
		
		$consulta="SELECT GROUP_CONCAT(CONCAT(IF(nombre IS NULL,'',nombre),' ',IF(apellidoPaterno IS NULL,'',apellidoPaterno),' ',IF(apellidoMaterno IS NULL,'',apellidoMaterno))) FROM 7005_relacionFigurasJuridicasSolicitud r,_47_tablaDinamica p 
				WHERE r.idActividad=".$fila["idActividad"]." and idFiguraJuridica=2 AND r.idParticipante=p.id__47_tablaDinamica order by nombre,apellidoPaterno,apellidoMaterno";

		$actores=$con->obtenerValor($consulta);
		
		$consulta="SELECT * FROM _478_tablaDinamica WHERE id__478_tablaDinamica=".$fila["idRegistro"];
		$fRegistro=$con->obtenerPrimeraFilaAsoc($consulta);
		$lblRegistro="<b>Folio de registro:</b> ".$fRegistro["codigo"]."<br>";
		$lblRegistro="<b>Fecha de registro:</b> ".date("d/m/Y H:i",strtotime($fRegistro["fechaCreacion"]))."<br>";
		$lblRegistro.="<b>Expediente:</b> <span style='color:#F00'>".$fRegistro["carpetaAdministrativa"]."</span><br>";
		$lblRegistro.="<b>Actor:</b> ".$actores."<br>";
		$lblRegistro.="<b>Demandado:</b> ".$demandados."<br><br>";
		
		$leyenda="El n&uacute;mero de Expediente <b>".$fila["carpetaAdministrativa"]."</b> ya ha sido registrado previamente con los siguientes datos:<br><br>".$lblRegistro.
					"<br>Si NO se trata de un expediente repetido, deber&aacute; asignar un nuevo sufijo al actual Expediente, para ello deber&aacute; dar click en el icono
					<img src=\"../images/arrow_refresh.PNG\"> en la secci&oacute;n Registro de Expediente/Exhorto (Deber&aacute; estar en modo edici&oacute;n)";
		return "[['Registro de Expediente/Exhorto','".cv($leyenda)."']]";
	}
	else
		return "[]";
	
	
	
}


function validarErrorPrueba()
{
	$arrErrores=array();
	
	$oError=array();
	$oError["seccion"]="Seccion 1";
	$oError["mensajeError"]="Error 1";
	
	array_push($arrErrores,$oError);
	$oError=array();
	$oError["seccion"]="Seccion 2";
	$oError["mensajeError"]="Error 2";
	
	array_push($arrErrores,$oError);
	
	return $arrErrores;
}

function enviarSolicitudLeyAMVLV($idFormulario,$idRegistro)
{
	global $con;
	
	$consulta="SELECT * FROM _483_tablaDinamica WHERE id__483_tablaDinamica=".$idRegistro;
	$fila=$con->obtenerPrimeraFilaAsoc($consulta);

	if(!esMateriaPenalRegistro($fila["codigoInstitucion"]))
	{
		return enviarSolicitudLeyAMVLVJuzgadoSICOREProxy($idFormulario,$idRegistro);
	}

	$arrDocumentos="";
	$consulta="SELECT g.documento,g.descripcion,a.nomArchivoOriginal,g.documento FROM _483_gDocumentosComplementarios g,908_archivos a WHERE g.idReferencia=".$idRegistro."
				AND g.documento=a.idArchivo";
	$res=$con->obtenerFilas($consulta);
	while($filaDoc=mysql_fetch_assoc($res))
	{
		$o='{"idDocumento":"'.$filaDoc["documento"].'","contenido":"'.obtenerCuerpoDocumentoB64($filaDoc["documento"]).'","descripcion":"'.cv($filaDoc["descripcion"]).
				'","nombreArchivo":"'.cv($filaDoc["nomArchivoOriginal"]).'"}';
		if($arrDocumentos=="")
			$arrDocumentos=$o;
		else
			$arrDocumentos.=",".$o;
	}

	$arrPartes="";
	
	$consulta="SELECT r.idFiguraJuridica,p.apellidoPaterno,p.apellidoMaterno,p.genero,p.curp,p.tipoPersona,
				p.fechaNacimiento,p.estadoCivil,p.nombre,p.esMexicano FROM 
				7005_relacionFigurasJuridicasSolicitud r,_47_tablaDinamica p WHERE p.id__47_tablaDinamica=r.idParticipante
				AND r.idActividad=".$fila["idActividad"];

	$arrPartes=utf8_encode($con->obtenerFilasJSON($consulta));

	$consulta="SELECT unidad FROM 817_organigrama o,801_adscripcion a WHERE o.codigoUnidad=a.Institucion
				AND a.idUsuario=".$_SESSION["idUsr"];
	$adscripcionRegistrante=$con->obtenerValor($consulta);	


	$consulta="SELECT Mail FROM 805_mails WHERE idUsuario=".$_SESSION["idUsr"];
	$emailRegistrante=$con->obtenerListaValores($consulta);
	
	$cadObj='{"relatoriaHechos":"'.cv($fila["relatoriaHechos"]).'","folioSolicitud":"'.$fila["codigo"].'","idRegistroSolicitud":"'.$idRegistro.
			'","documentos":['.$arrDocumentos.'],"registradoPor":"'.cv(obtenerNombreUsuario($_SESSION["idUsr"])).
			'","adscripcionRegistrante":"'.cv($adscripcionRegistrante).'","emailRegistrante":"'.cv($emailRegistrante).'","partes":'.$arrPartes.'}';
	$cadObj=preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $cadObj);

	$cadObj=preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $cadObj);

	$cadObj=preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $cadObj);
	
	$datosServidor=obtenerURLComunicacionServidorMateria("PO");
	
	$client = new nusoap_client("http://".$datosServidor[0].":".$datosServidor[1]."/webServices/wsInterconexionSistemasBPM.php?wsdl","wsdl");
	$parametros=array();
	$parametros["cadObj"]=bE($cadObj);
	$response = $client->call("registrarSolicitudLAVLV", $parametros);

	$oResp=json_decode($response);	

	if($oResp->resultado==1)
	{
		
		$consulta="UPDATE _483_tablaDinamica SET carpetaAdministrativa='".$oResp->carpetaAdministrativa."',unidadGestion='".$oResp->unidadGestion.
				"',folioRecepcion='".$oResp->folioRegistro."',fechaRecepcion='".$oResp->fechaRecepcion.
				"',idRegistroRecepcion='".$oResp->idRegistroRecepcion."' WHERE id__483_tablaDinamica=".$idRegistro;
		
		return $con->ejecutarConsulta($consulta);	
	}
	else
	{
		return false;
	}

	
}





function notificarAtencionSolicitudLMVLV($idFormulario,$idRegistro)
{
	global $con;
	return true;
	$consulta="SELECT * FROM _483_tablaDinamica WHERE id__483_tablaDinamica=".$idRegistro;
	$fRegistro=$con->obtenerPrimeraFilaAsoc($consulta);
	$arrParam["carpeta"]=$fRegistro["carpetaAdministrativa"];
	$arrParam["nombreUsuario"]=obtenerNombreUsuario($fRegistro["responsable"]);
	$arrParam["nombreUsuario"]=htmlentities($arrParam["nombreUsuario"]);
	$arrParam["idUsuario"]=$fRegistro["responsable"];
	return @enviarMensajeEnvio(23,$arrParam,"sendMensajeEnvioGmailJuzgado");
}





function enviarSolicitudLeyAMVLVJuzgadoSICOREProxy($idFormulario,$idRegistro)
{
	global $con;
	
	$consulta="SELECT * FROM _483_tablaDinamica WHERE id__483_tablaDinamica=".$idRegistro;
	$fila=$con->obtenerPrimeraFilaAsoc($consulta);

	

	$arrDocumentos="";
	
	
	$archivoDestino=generarDocumentoRelatoriaHechos($fila["relatoriaHechos"]);
	
	if(file_exists($archivoDestino))
	{
		$cuerpoDocumento=leerContenidoArchivo($archivoDestino);
		$arrDocumentos='{"idDocumento":"0","contenido":"'.bE($cuerpoDocumento).'","descripcion":"","urlDocumento":"","nombreArchivo":"relatohechos.pdf"}';
		unlink($archivoDestino);
	}
	
	
	
	$consulta="SELECT g.documento,g.descripcion,a.nomArchivoOriginal,g.documento FROM _483_gDocumentosComplementarios g,908_archivos a WHERE g.idReferencia=".$idRegistro."
				AND g.documento=a.idArchivo";
	$res=$con->obtenerFilas($consulta);
	while($filaDoc=mysql_fetch_assoc($res))
	{
		$o='{"idDocumento":"'.$filaDoc["documento"].'","contenido":"","urlDocumento":"'.cv(str_replace("/","\\",obtenerRutaDocumento($filaDoc["documento"]))).'","descripcion":"'.cv($filaDoc["descripcion"]).
				'","nombreArchivo":"'.cv($filaDoc["nomArchivoOriginal"]).'"}';
		if($arrDocumentos=="")
			$arrDocumentos=$o;
		else
			$arrDocumentos.=",".$o;
	}
	
	$arrPartes="";
	
	$consulta="SELECT r.idFiguraJuridica,p.apellidoPaterno,p.apellidoMaterno,p.genero,p.curp,p.tipoPersona,
				p.fechaNacimiento,p.estadoCivil,p.nombre,p.esMexicano FROM 
				7005_relacionFigurasJuridicasSolicitud r,_47_tablaDinamica p WHERE p.id__47_tablaDinamica=r.idParticipante
				AND r.idActividad=".$fila["idActividad"];

	$arrPartes=utf8_encode($con->obtenerFilasJSON($consulta));

	$consulta="SELECT idOrganigrama FROM 817_organigrama o,801_adscripcion a WHERE o.codigoUnidad=a.Institucion
				AND a.idUsuario=".$_SESSION["idUsr"];
	$adscripcionRegistrante=$con->obtenerValor($consulta);	


	$consulta="SELECT Mail FROM 805_mails WHERE idUsuario=".$_SESSION["idUsr"];
	$emailRegistrante=$con->obtenerListaValores($consulta);

	$cadObj='{"relatoriaHechos":"'.cv($fila["relatoriaHechos"]).'","folioSolicitud":"'.$fila["codigo"].'","idRegistroSolicitud":"'.$idRegistro.
			'","documentos":['.$arrDocumentos.'],"registradoPor":"'.cv(obtenerNombreUsuario($_SESSION["idUsr"])).
			'","adscripcionRegistrante":"'.cv($adscripcionRegistrante).'","emailRegistrante":"'.cv($emailRegistrante).'","partes":'.$arrPartes.'}';
	
	$cadObj=preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $cadObj);

	$client = new nusoap_client("http://localhost:8586/Service.asmx?wsdl","wsdl");
	$parametros=array();
	$parametros["cadObj"]=$cadObj;
	$response = $client->call("registrarSolicitudPromujer", $parametros);

	

	$oResp=json_decode($response["registrarSolicitudPromujerResult"]);	
	//varDUmp($oResp);

	

	if(($oResp) &&(isset($oResp->expediente)))
	{
		
		$consulta="SELECT codigoUnidad FROM 817_organigrama WHERE claveDepartamental='".$oResp->idJuzgado."'";
		$unidadGestion=$con->obtenerValor($consulta);
		
		$arrFechaRecepcion=explode("-",$oResp->fechaFolio);
		$consulta="UPDATE _483_tablaDinamica SET carpetaAdministrativa='".str_replace(" ","",$oResp->expediente)."',unidadGestion='".$unidadGestion.
				"',folioRecepcion='".$oResp->numFolio."',fechaRecepcion='".($arrFechaRecepcion[2]."-".$arrFechaRecepcion[1]."-".$arrFechaRecepcion[0]).
				"',idRegistroRecepcion='".$oResp->numFolio."' WHERE id__483_tablaDinamica=".$idRegistro;
		
		return $con->ejecutarConsulta($consulta);	
	}
	else
	{
		return false;
	}

	
}


function esMateriaPenalRegistro($codigoInstitucion)
{
	$arrJuzgadosSicore["00050001"]=1;
	$arrJuzgadosSicore["00050002"]=1;
	$arrJuzgadosSicore["00050003"]=1;
	return !isset($arrJuzgadosSicore[$codigoInstitucion]);
}

function generarDocumentoRelatoriaHechos($cadenaTexto)
{
	global $baseDir;
	
	$cadenaTexto=str_replace("\n","<br />",$cadenaTexto);
	$cadenaTexto=str_replace("\r","",$cadenaTexto);
	$cadenaTexto=preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $cadenaTexto);
	$plantilla='<meta http-equiv=Content-Type content="text/html; charset=windows-1252"><meta name=ProgId content=Word.Document><meta name=Generator content="Microsoft Word 15"><meta name=Originator content="Microsoft Word 15">
			<style type="text/css">@font-face	{font-family:Calibri;	panose-1:2 15 5 2 2 2 4 3 2 4;	mso-font-charset:0;	mso-generic-font-family:swiss;	mso-font-pitch:variable;	mso-font-signature:-520092929 1073786111 9 0 415 0;}@font-face	{font-family:"Segoe UI";	panose-1:2 11 5 2 4 2 4 2 2 3;	mso-font-charset:0;	mso-generic-font-family:swiss;	mso-font-pitch:variable;	mso-font-signature:-520084737 -1073683329 41 0 479 0;}@font-face	{font-family:Verdana;	panose-1:2 11 6 4 3 5 4 4 2 4;	mso-font-charset:0;	mso-generic-font-family:swiss;	mso-font-pitch:variable;	mso-font-signature:-1593833729 1073750107 16 0 415 0;}@font-face	{font-family:"Bradley Hand ITC";	panose-1:3 7 4 2 5 3 2 3 2 3;	mso-font-charset:0;	mso-generic-font-family:script;	mso-font-pitch:variable;	mso-font-signature:3 0 0 0 1 0;}@font-face	{font-family:Centaur;	panose-1:2 3 5 4 5 2 5 2 3 4;	mso-font-charset:0;	mso-generic-font-family:roman;	mso-font-pitch:variable;	mso-font-signature:3 0 0 0 1 0;} /* Style Definitions */ p.MsoNormal, li.MsoNormal, div.MsoNormal	{mso-style-unhide:no;	mso-style-qformat:yes;	mso-style-parent:"";	margin-top:0cm;	margin-right:0cm;	margin-bottom:10.0pt;	margin-left:0cm;	line-height:115%;	mso-pagination:widow-orphan;	font-size:11.0pt;	font-family:"Calibri","sans-serif";	mso-fareast-font-family:"Times New Roman";	mso-fareast-theme-font:minor-fareast;}p.MsoHeader, li.MsoHeader, div.MsoHeader	{		mso-style-link:"Encabezado Car";		margin:0cm;		margin-bottom:.0001pt;		mso-pagination:widow-orphan;		font-size:11.0pt;		font-family:"Calibri","sans-serif";		mso-fareast-font-family:"Times New Roman";		mso-fareast-theme-font:minor-fareast;				}p.MsoFooter, li.MsoFooter, div.MsoFooter	{mso-style-priority:99;	mso-style-link:"Pie de página Car";	margin:0cm;	margin-bottom:.0001pt;	mso-pagination:widow-orphan;	font-size:11.0pt;	font-family:"Calibri","sans-serif";	mso-fareast-font-family:"Times New Roman";	mso-fareast-theme-font:minor-fareast;}p.MsoAcetate, li.MsoAcetate, div.MsoAcetate	{mso-style-noshow:yes;	mso-style-priority:99;	mso-style-link:"Texto de globo Car";	margin:0cm;	margin-bottom:.0001pt;	mso-pagination:widow-orphan;	font-size:9.0pt;	font-family:"Segoe UI","sans-serif";	mso-fareast-font-family:"Times New Roman";	mso-fareast-theme-font:minor-fareast;}p.MsoNoSpacing, li.MsoNoSpacing, div.MsoNoSpacing	{mso-style-priority:1;	mso-style-unhide:no;	mso-style-qformat:yes;	margin:0cm;	margin-bottom:.0001pt;	mso-pagination:widow-orphan;	font-size:11.0pt;	font-family:"Calibri","sans-serif";	mso-fareast-font-family:"Times New Roman";	mso-fareast-theme-font:minor-fareast;}p.MsoIntenseQuote, li.MsoIntenseQuote, div.MsoIntenseQuote	{mso-style-priority:30;	mso-style-unhide:no;	mso-style-qformat:yes;	mso-style-link:"Cita destacada Car";	margin-top:18.0pt;	margin-right:43.2pt;	margin-bottom:18.0pt;	margin-left:43.2pt;	text-align:center;	line-height:115%;	mso-pagination:widow-orphan;	font-size:11.0pt;	font-family:"Calibri","sans-serif";	mso-fareast-font-family:"Times New Roman";	mso-fareast-theme-font:minor-fareast;	color:#5B9BD5;	font-style:italic;}span.EncabezadoCar	{		mso-style-name:"Encabezado Car";		mso-style-unhide:no;		mso-style-locked:yes;		mso-style-link:Encabezado;		font-family:"Calibri","sans-serif";		mso-ascii-font-family:Calibri;		mso-hansi-font-family:Calibri;		mso-bidi-font-family:Calibri;	}span.PiedepginaCar	{mso-style-name:"Pie de página Car";	mso-style-priority:99;	mso-style-unhide:no;	mso-style-locked:yes;	mso-style-link:"Pie de página";	font-family:"Calibri","sans-serif";	mso-ascii-font-family:Calibri;	mso-hansi-font-family:Calibri;	mso-bidi-font-family:Calibri;}span.TextodegloboCar	{mso-style-name:"Texto de globo Car";	mso-style-noshow:yes;	mso-style-priority:99;	mso-style-unhide:no;	mso-style-locked:yes;	mso-style-link:"Texto de globo";	font-family:"Segoe UI","sans-serif";	mso-ascii-font-family:"Segoe UI";	mso-hansi-font-family:"Segoe UI";	mso-bidi-font-family:"Segoe UI";}span.CitadestacadaCar	{mso-style-name:"Cita destacada Car";	mso-style-priority:30;	mso-style-unhide:no;	mso-style-locked:yes;	mso-style-link:"Cita destacada";	font-family:"Calibri","sans-serif";	mso-ascii-font-family:Calibri;	mso-hansi-font-family:Calibri;	mso-bidi-font-family:Calibri;	color:#5B9BD5;	font-style:italic;}p.msochpdefault, li.msochpdefault, div.msochpdefault	{mso-style-name:msochpdefault;	mso-style-unhide:no;	mso-margin-top-alt:auto;	margin-right:0cm;	mso-margin-bottom-alt:auto;	margin-left:0cm;	mso-pagination:widow-orphan;	font-size:12.0pt;	font-family:"Calibri","sans-serif";	mso-fareast-font-family:"Times New Roman";	mso-fareast-theme-font:minor-fareast;}p.msopapdefault, li.msopapdefault, div.msopapdefault	{mso-style-name:msopapdefault;	mso-style-unhide:no;	mso-margin-top-alt:auto;	margin-right:0cm;	margin-bottom:8.0pt;	margin-left:0cm;	line-height:105%;	mso-pagination:widow-orphan;	font-size:12.0pt;	font-family:"Times New Roman","serif";	mso-fareast-font-family:"Times New Roman";	mso-fareast-theme-font:minor-fareast;}.MsoChpDefault	{mso-style-type:export-only;	mso-default-props:yes;	font-size:10.0pt;	mso-ansi-font-size:10.0pt;	mso-bidi-font-size:10.0pt;	font-family:"Calibri","sans-serif";	mso-ascii-font-family:Calibri;	mso-hansi-font-family:Calibri;	mso-bidi-font-family:Calibri;}@page WordSection1	{		size:612.0pt 1008.0pt;		margin:70.9pt 59.25pt 70.9pt 2.0cm;		mso-header-margin:35.4pt;		mso-footer-margin:35.4pt;		mso-header: h1;        mso-footer: f1;		mso-paper-source:0;	}div.WordSection1	{page:WordSection1;}-->table#hrdftrtbl{    margin:0in 0in 0in 200in;    width:1px !important;    height:1px !important;    overflow:hidden;}
							</style>
							<div class="WordSection1"><tagheader>
							<div id="h1" style="mso-element:header">
							<div class="cwjdsjcs_not_editable">&nbsp;</div>
							</div>
							</tagheader>
							
							<div>
							<div class="cwjdsjcs_not_editable">
							<table border="0" cellpadding="1" cellspacing="1" style="width:620px;">
								<tbody>
									<tr>
										<td style="text-align: center;">&nbsp;
										<div class="cwjdsjcs_editable"><strong>Relatoria de Hechos</strong></div>
							
										<div class="cwjdsjcs_editable" style="text-align: left;"><br /><p>
										​​​​​​​'.$cadenaTexto.'</p></div>
										</td>
									</tr>
								</tbody>
							</table>
							</div>
							</div>
							<tagfooter class="cwjdsjcs_not_editable">
							<div id="f1" style="mso-element:footer">
							<p class="MsoHeader" style="margin-left: -9pt; text-align: right;"><span style="mso-no-proof:yes"><span style="mso-tab-count:1">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;</span></span></p>
							</div>
							</tagfooter></div>
							';

	$plantilla=prepararFormatoImpresionWord($plantilla);
	$nombreArchivo=rand()."_".date("dmY_Hms");
	
	$archivoTemporal=$baseDir."/archivosTemporales/".$nombreArchivo.".html";
	if(escribirContenidoArchivo($archivoTemporal,$plantilla))
	{
		$directorioDestino=$baseDir."/archivosTemporales/";
		generarDocumentoPDF($archivoTemporal,false,false,true,"","MS_OFFICE",$directorioDestino);
		return $directorioDestino."/".$nombreArchivo.".pdf";
		
	}
}

?>