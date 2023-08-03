<?php 
include_once("latis/conexionBD.php");
include_once("latis/utiles.php");


function turnarSolicitudAmparoUnidad($idFormulario,$idRegistro)
{
	global $con;
	
	$query="SELECT id__486_tablaDinamica FROM _486_tablaDinamica WHERE idReferencia=".$idRegistro;
	$idRegistroComplementario=$con->obtenerValor($query);
	
	$query="SELECT * FROM _485_tablaDinamica WHERE id__485_tablaDinamica=".$idRegistro;
	$fRegistroAmparo=$con->obtenerPrimeraFilaAsoc($query);
	
	$arrUgasDestinataria=array();
	$query="SELECT unidadGestion FROM _485_unidadesGestionReferidas WHERE idReferencia=".$idRegistro;
	$rUnidades=$con->obtenerFilas($query);
		
	if(($fRegistroAmparo["categoriaAmparo"]==1)&&($con->filasAfectadas==0))
	{
		array_push($arrUgasDestinataria,$fRegistroAmparo["unidadGestionReferida"]);
	}
	else
	{
		
		while($fUnidad=mysql_fetch_row($rUnidades))
		{
			$query="SELECT COUNT(*) FROM _346_tablaDinamica WHERE iFormulario=485 AND iRegistro=".$idRegistro;
			$nReg=$con->obtenerValor($query);
			if($nReg==0)
				array_push($arrUgasDestinataria,$fUnidad[0]);
		}
	}
	
	foreach($arrUgasDestinataria as $UGA)
	{
	
	
		$consulta=array();
		$x=0;
		$consulta[$x]="begin";
		$x++;
		
		
		$consulta[$x]="INSERT INTO _346_tablaDinamica(fechaCreacion,responsable,idEstado,codigoInstitucion,fechaRecepcion,
						horaRecepcion,tipoAmparo,noJuicioAmparo,noOficio,organoJurisdiccionalRequiriente,carpetaAdministrativa,quejoso,
						nombre,apPaterno,apMaterno,categoriaAmparo,figuraJuridica,entidadFederativa,folioEnvioCJF,expedienteElectronicoCJF,urlEECJF,
						tipoCuadernoCJF,neunCJF,numeroExpedienteCJF,tipoProcedimientoCJF,tipoSubNivelCJF,tipoMateriaCJF,ICOIJ_Solicitud,iFormulario,
						iRegistro,juezRefiere)					
						SELECT '".date("Y-m-d H:i:s")."' AS fechaCreacion,a.responsable,1,'".$UGA."' AS codigoInstitucion,a.fechaRecepcion,
						horaRecepcion,tipoAmparo,noJuicioAmparo,noOficio,organoJurisdiccionalRequiriente,carpetaAdministrativa,quejoso,
						nombre,apPaterno,apMaterno,categoriaAmparo,figuraJuridica,entidadFederativa,folioEnvioCJF,expedienteElectronicoCJF,urlEECJF,
						tipoCuadernoCJF,neunCJF,numeroExpedienteCJF,tipoProcedimientoCJF,tipoSubNivelCJF,tipoMateriaCJF,ICOIJSolicitud,485,
						id__485_tablaDinamica,juezReferido FROM _485_tablaDinamica a,_486_tablaDinamica q WHERE id__485_tablaDinamica=".$idRegistro."
						AND q.idReferencia=a.id__485_tablaDinamica";
		$x++;
		
		$consulta[$x]="set @idRegistro:=(select last_insert_id())";
		$x++;
		
		$consulta[$x]="INSERT INTO _346_gActosReclamados(idReferencia,idActoReclamado,detalles)
						SELECT @idRegistro,idActoReclamado,detalles FROM _486_gActosReclamados WHERE idReferencia=".$idRegistroComplementario;
		$x++;	
		
		$consulta[$x]="INSERT INTO _346_otroQuejoso(idPadre,idOpcion)
						SELECT @idRegistro,idOpcion FROM  _486_chkOtro WHERE idPadre=".$idRegistroComplementario;
		$x++;
		$consulta[$x]="INSERT INTO 9074_documentosRegistrosProceso(idFormulario,idRegistro,idDocumento,tipoDocumento)
						SELECT 346,@idRegistro,idDocumento,tipoDocumento from 9074_documentosRegistrosProceso WHERE 
						idFormulario=485 AND idRegistro=".$idRegistro;
		$x++;
		$consulta[$x]="commit";
		$x++;
		if($con->ejecutarBloque($consulta))
		{
			$query="select @idRegistro";
			$idRegistro=$con->obtenerValor($query);		
			asignarFolioRegistro(346,$idRegistro);		
			cambiarEtapaFormulario(346,$idRegistro,1.5,"",-1,"NULL","NULL",840);
			cambiarEtapaFormulario(346,$idRegistro,2,"",-1,"NULL","NULL",840);
		}
	}
}

function enviarRespuestaAmparo($idFormulario,$idRegistro)
{
	global $con;
	
	$client = new nusoap_client("http:/10.19.5.9:9092/wsCJFPromociones.asmx?wsdl","wsdl");
	
	$consulta="SELECT idReferencia FROM _460_tablaDinamica WHERE id__460_tablaDinamica=".$idRegistro;
	$idReferencia=$con->obtenerValor($consulta);	
	
	$consulta="SELECT * FROM _346_tablaDinamica WHERE id__346_tablaDinamica=".$idReferencia;
	$fDatosAmparo=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$consulta="SELECT idCarpeta,unidadGestion FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa=".$fDatosAmparo["carpetaAmparo"]."'";
	$fCarpeta=$con->obtenerPrimeraFila($consulta);
	
	$identificadorExpediente=$fCarpeta[0];
	$cveUnidad=$fCarpeta[1];
	
	$consulta="SELECT claveCJF FROM _17_tablaDinamica WHERE claveUnidad='".$cveUnidad."'";
	$organoImpartidorJusticia=$con->obtenerValor($consulta);
	
	
	$arrDocumentos=array();
	foreach($arrDocumentos as $d)
	{
		$consulta="SELECT nomArchivoOriginal FROM 908_archivos WHERE idArchivo=".$d;
		$fArchivo=$con->obtenerPrimeraFila($consulta);
		$ruta=obtenerRutaDocumento($d);
		
		$archivo=bE(leerContenidoArchivo($ruta));
		$archivoPKCS7=bE(leerContenidoArchivo($ruta.".pkcs7"));
		
		$fechaFirmado=date("Y-m-d H:i:s",filectime($ruta.".pkcs7"));
		$oDocumento='{"IdDocumento":"'.$d.'","Nombre":"'.$fArchivo[0].'","Extension":".pdf","FileBase64":"'.$archivo.'","Longitud":"'.filesize($ruta).'","Firmado":"1",
					"Pkcs7Base64":"'.$archivoPKCS7.'","FechaFirmado":"'.$fechaFirmado.'","HashDocumentoOriginal":"","ClasificacionArchivo":"0"}';
		if($arrDocumentos=="")
			$arrDocumentos=$oDocumento;
		else
			$arrDocumentos.=",".$oDocumento;
	}
	$cadObjConsulta='{"fec_envio":"'.date("Y-m-d H:i:s").'","solicitud_Id":"'.$idRegistro.'","organoImpartidorJusticia":"'.$organoImpartidorJusticia.'",
						"numeroExpedienteOIJ":"'.$fDatosAmparo["carpetaAmparo"].'","identificadorExpediente":"'.$identificadorExpediente.
						'","existeEE":"0","UrlEE":"","neun":"'.$fDatosAmparo["neunCJF"].'","idOrganoDestino":"'.$fDatosAmparo["organoJurisdiccionalRequiriente"].
						'","numeroExpedienteDestino":"'.$fDatosAmparo["numeroExpedienteCJF"].'","tipoAsuntoDestino":"'.$fDatosAmparo["tipoAmparo"].
						'","tipoProcedimientoDestino":"'.$fDatosAmparo["tipoProcedimientoCJF"].'","tipoSubNivelDestino":"'.$fDatosAmparo["tipoSubNivelCJF"].
						'","tipoMateriaDestino":"'.$fDatosAmparo["tipoMateriaCJF"].'","tipoCuaderno":"'.$fDatosAmparo["tipoCuadernoCJF"].'","Documentos":['.$arrDocumentos.']}';
	
	$parametros=array();
	$parametros["cadObjConsulta"]=$cadObjConsulta;
	$parametros["esSolicitudPrueba"]="0";
	 
	$response = $client->call("Promocion", $parametros);
	
	$objResp=json_decode($response);
	
	return true;
}

function enviarNotificacionCJF($idNotificacion)
{
	global $con;
	
	$client = new nusoap_client("http://172.19.202.116:9092/wsCJFPromociones.asmx?wsdl","wsdl");
	
	$consulta="SELECT idReferencia,documentosAsociados,juezNotifica FROM 7041_notificacionesCJF WHERE idRegistro=".$idNotificacion;
	
	$fNotificacion=$con->obtenerPrimeraFila($consulta);
	$arrDocumentos="";
	$aDocumentos=array();
	$idRegistro=$fNotificacion[0];	
	$obj=json_decode('{"arrDocumentos":['.$fNotificacion[1].']}');

	foreach($obj->arrDocumentos as $d)
	{
		array_push($aDocumentos,$d->idDocumento);
	}

	$consulta="SELECT idReferencia,datosCJF FROM _460_tablaDinamica WHERE id__460_tablaDinamica=".$idRegistro;
	$fPromocion=$con->obtenerPrimeraFila($consulta);
	
	$idReferencia=$fPromocion[0];	
	$datosCJF=$fPromocion[1];
	
	if($datosCJF=="")
	{
		$consulta="SELECT claveCJF FROM _26_tablaDinamica WHERE id__26_tablaDinamica=".$fNotificacion[2];
		$claveCJF=$con->obtenerValor($consulta);
		if($claveCJF=="")
			$claveCJF=-1;
		$consulta="SELECT datosCJF FROM _346_organosImpartidoresJusticiaCJF WHERE idRegistroAmparo=".$idReferencia." AND organoImpartidorJusticia=".$claveCJF;
		$datosCJF=$con->obtenerValor($consulta);
		
	}
	
	if($datosCJF=="")
	{
		$consulta="UPDATE 7041_notificacionesCJF SET folioNotificacion=NULL,codigoRetorno=0,
			mensaje='".cv("El organo impartidor de justicia (Juez) no se encuentra asociado a la carpeta de Amparo")."',fechaEnvioCJF='".date("Y-m-d H:i:s").
			"',responsableEnvio=".$_SESSION["idUsr"].",situacion=3 WHERE idRegistro=".$idNotificacion;
		return $con->ejecutarConsulta($consulta);
	}
	$oDatosCJF=json_decode(bD($datosCJF));
	
	$consulta="SELECT * FROM _346_tablaDinamica WHERE id__346_tablaDinamica=".$idReferencia;
	$fDatosAmparo=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$organoImpartidorJusticia=$oDatosCJF->organoImpartidorJusticia;

	$consulta="SELECT idCarpeta,unidadGestion FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$fDatosAmparo["carpetaAmparo"]."'";

	$fCarpeta=$con->obtenerPrimeraFila($consulta);
	
	$identificadorExpediente=$fCarpeta[0];
	$cveUnidad=$fCarpeta[1];
	
	foreach($aDocumentos as $d)
	{
		$consulta="SELECT nomArchivoOriginal FROM 908_archivos WHERE idArchivo=".$d;
		$fArchivo=$con->obtenerPrimeraFila($consulta);
		$ruta=obtenerRutaDocumento($d);
		
		$archivo=bE(leerContenidoArchivo($ruta));
		$archivoPKCS7="";
		$firmado=0;
		$fechaFirmado=date("Y-m-d H:i:s",filectime($ruta));
		if(file_exists($ruta.".pkcs7"))
		{
			$archivoPKCS7=bE(leerContenidoArchivo($ruta.".pkcs7"));
			$fechaFirmado=date("Y-m-d H:i:s",filectime($ruta.".pkcs7"));
			$firmado=1;
		}
		$oDocumento='{"IdDocumento":"'.$d.'","Nombre":"'.$fArchivo[0].'","Extension":".pdf","FileBase64":"'.$archivo.'","Longitud":"'.filesize($ruta).
					'","Firmado":"'.$firmado.'","Pkcs7Base64":"'.$archivoPKCS7.'","FechaFirmado":"'.$fechaFirmado.
					'","HashDocumentoOriginal":"","ClasificacionArchivo":"0"}';
		if($arrDocumentos=="")
			$arrDocumentos=$oDocumento;
		else
			$arrDocumentos.=",".$oDocumento;
	}
	$cadObjConsulta='{"fec_envio":"'.date("Y-m-d H:i:s").'","solicitud_Id":"1'.$idNotificacion.'","organoImpartidorJusticia":"'.$organoImpartidorJusticia.'",
						"numeroExpedienteOIJ":"'.$fDatosAmparo["carpetaAmparo"].'","identificadorExpediente":"'.$identificadorExpediente.
						'","existeEE":"0","UrlEE":"","neun":"'.$fDatosAmparo["neunCJF"].'","idOrganoDestino":"'.$oDatosCJF->idOrganoOrigen.
						'","numeroExpedienteDestino":"'.$fDatosAmparo["numeroExpedienteCJF"].'","tipoAsuntoDestino":"'.$fDatosAmparo["tipoAmparo"].
						'","tipoProcedimientoDestino":"'.$fDatosAmparo["tipoProcedimientoCJF"].'","tipoSubNivelDestino":"'.$fDatosAmparo["tipoSubNivelCJF"].
						'","tipoMateriaDestino":"'.$fDatosAmparo["tipoMateriaCJF"].'","tipoCuaderno":"'.$fDatosAmparo["tipoCuadernoCJF"].
						'","Documentos":['.$arrDocumentos.']}';
	
	$parametros=array();
	$parametros["cadObjConsulta"]=$cadObjConsulta;
	$parametros["esSolicitudPrueba"]="0";
	$response = $client->call("Promocion", $parametros);
	
	$objResp=json_decode(utf8_encode($response["PromocionResult"]));
	
	$situacion=3;
	if($objResp->CodigoRetorno==1)
		$situacion=2;
	
	$consulta="UPDATE 7041_notificacionesCJF SET folioNotificacion='".($objResp->FolioConfirmacion==""?"NULL":$objResp->FolioConfirmacion)."',codigoRetorno='".$objResp->CodigoRetorno.
			"',mensaje='".cv($objResp->Mensaje)."',fechaEnvioCJF='".date("Y-m-d H:i:s")."',responsableEnvio=".$_SESSION["idUsr"].
			",situacion=".$situacion." WHERE idRegistro=".$idNotificacion;

	return $con->ejecutarConsulta($consulta);
}

?>