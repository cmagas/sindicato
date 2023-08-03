<?php 
include_once("latis/conexionBD.php");
include_once("latis/utiles.php");


function enviarMailNotificacionAlertaMediatico($arrDestinatarios,$arrDocumentosAdjuntos,$tituloMensaje,$cuerpoMensaje)
{
	global $con;
	global $leyendaTribunal;
	$arrCopia=array();
	$cuerpoMail='<table width="800">
						<tbody>
							<tr>
								<td align="left" style="width:100px"></td>
								<td style="width:520px;text-align: right;">
								<table width="100%">
									<tbody>
										<tr>
											<td style="text-align: right;"><span style="font-size:10.0pt;mso-bidi-font-size:12.0pt;font-family:&quot;Lucida Calligraphy&quot;">'.$leyendaTribunal.'</span></td>
										</tr>
										
									</tbody>
								</table>
								</td>
							</tr>
							<tr>
								<td colspan="2"><br><br><span style="font-size:14px">'.$cuerpoMensaje.'</span><br><br>
								</td>
							</tr>
						</tbody>
					</table>';
	$resultado=@enviarMailGMail("notificacionesTSJCDMX@grupolatis.net",$tituloMensaje,$cuerpoMail,$arrDocumentosAdjuntos,$arrCopia,$arrDestinatarios);
	
	return $resultado;

}

function notificarSeguimientoMediaticoProceso($idFormulario,$idRegistro)
{
	global $con;
	$notificarMail=true;
	
	$consulta="SELECT codigo FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$folio=$con->obtenerValor($consulta);
	$carpetaAdministrativa=obtenerCarpetaAdministrativaProceso($idFormulario,$idRegistro);
	$resultado=esCarpetaSeguimientoMediatico($carpetaAdministrativa);

	
	if(!$resultado[0])
	{
		return true;
	}
	
	$arrDestinatario=array();
	$aDestinatario=array();
	$consulta="SELECT nombre,email FROM _586_gNotificacionCorreo WHERE idReferencia in(".$resultado[1].")";
	$rDestinatarios=$con->obtenerFilas($consulta);
	while($fDestinatario=mysql_fetch_row($rDestinatarios))
	{
		$aDestinatario[$fDestinatario[1]]=$fDestinatario[0];
	}
	
	foreach($aDestinatario as $m=>$d)
	{
		$o= array();
		$o[0]=$m;
		$o[1]=$d;
		array_push($arrDestinatario,$o);
	}	

	$arrDocumentosAdjuntos=array();
	$consulta="SELECT idDocumento FROM 9074_documentosRegistrosProceso WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro;
	$rDocumentos=$con->obtenerFilas($consulta);
	while($fDocumento=mysql_fetch_row($rDocumentos))
	{
		$consulta="SELECT nomArchivoOriginal,tamano FROM 908_archivos WHERE idArchivo=".$fDocumento[0];
		$fArchivo=$con->obtenerPrimeraFila($consulta);

		if($fArchivo[1]<=6291456)
		{
			$o= array();
			$o[0]=obtenerRutaDocumento($fDocumento[0]);
			$o[1]=$fArchivo[0];
			array_push($arrDocumentosAdjuntos,$o);
		}
	}
	
	
	$tituloMensaje="";
	$cuerpoMensaje="";
	switch($idFormulario)
	{
		case 96:
			$tituloMensaje="TSJCDMX: Nueva promoción recibida, Carpeta Judicial: ".$resultado[2];
			$cuerpoMensaje="Se ha registrado una nueva promoción asociada a la Carpeta Judicial <b>".$resultado[2]."</b>, folio de la promoción: <b>".$folio."</b>";
		break;
		case 451:
			$tituloMensaje="TSJCDMX: Nueva recurso de apelación registrado, Carpeta Judicial: ".$resultado[2];
			$cuerpoMensaje="Se ha registrado un nuevo recurso de apelación asociado a la Carpeta Judicial <b>".$resultado[2]."</b>, folio del recurso: <b>".$folio."</b>";
		break;
		case 346:
			$tituloMensaje="TSJCDMX: Nuevo Juicio de Amparo registrado, Carpeta Judicial: ".$resultado[2];
			$cuerpoMensaje="Se ha registrado un nuevo Juicio de Amparo asociado a la Carpeta Judicial <b>".$resultado[2]."</b>, folio del Juicio: <b>".$folio."</b>";
		break;
		case 460:
			$consulta="SELECT idReferencia FROM _460_tablaDinamica WHERE id__460_tablaDinamica=".$idRegistro;
			$idReferencia=$con->obtenerValor($consulta);
			$consulta="SELECT id__460_tablaDinamica FROM _460_tablaDinamica WHERE idReferencia=".$idReferencia." ORDER BY id__460_tablaDinamica";
			$idPromocion1=$con->obtenerValor($consulta);
			
			if($idRegistro==$idPromocion1)
			{
				
				$notificarMail=false;
			}
			$tituloMensaje="TSJCDMX: Nueva Promoción en Juicio de Amparo registrado, Carpeta Judicial: ".$resultado[2];
			$cuerpoMensaje="Se ha registrado una nueva promoción  dentro de un Juicio de Amparo asociado a la Carpeta Judicial <b>".$resultado[2]."</b>, folio del promocion: <b>".$folio."</b>";
		break;
		case 434:
			$tituloMensaje="TSJCDMX: Nueva registro de orden de aprehensi&oacute;n registrado, Carpeta Judicial: ".$resultado[2];
			$cuerpoMensaje="Se ha registrado una nueva orden de aprehensi&oacute;n asociada a la Carpeta Judicial <b>".$resultado[2]."</b>, folio del registro: <b>".$folio."</b>";
		break;
	}

	if($notificarMail && @enviarMailNotificacionAlertaMediatico($arrDestinatario,$arrDocumentosAdjuntos,$tituloMensaje,$cuerpoMensaje))
	{
		$consulta="UPDATE _".$idFormulario."_tablaDinamica SET notificacionCorreoMediatico=1 WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
		$con->ejecutarConsulta($consulta);
	}
	else
	{
		if(!$notificarMail)
		{
			$consulta="UPDATE _".$idFormulario."_tablaDinamica SET notificacionCorreoMediatico=2 WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
			$con->ejecutarConsulta($consulta);
		}
	}
	
}

function esCarpetaSeguimientoMediatico($carpetaAdministrativa)
{
	global $con;
	
	
	$listaCarpetasDerivadas=obtenerCarpetasVinculadas($carpetaAdministrativa,-1);

	$consulta="SELECT idCarpeta FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa in(".$listaCarpetasDerivadas.")";
//	echo $consulta;
	$idCarpetaAdministrativa=$con->obtenerListaValores($consulta);

	$consulta="SELECT id__586_tablaDinamica FROM _586_tablaDinamica WHERE idCarpetaAdministrativa in(".$idCarpetaAdministrativa.") AND idEstado=2";
	$listaRegistros=$con->obtenerListaValores($consulta);
	if($listaRegistros=="")
		$listaRegistros=-1;
		
	$consulta=" SELECT DISTINCT c.carpetaAdministrativa FROM _586_tablaDinamica s,7006_carpetasAdministrativas c 
			 WHERE id__586_tablaDinamica IN(".$listaRegistros.") AND c.idCarpeta=s.idCarpetaAdministrativa";	
	$listaCarpetas=$con->obtenerListaValores($consulta);	
	$arrResultado=array();
	$arrResultado[0]=$con->filasAfectadas==0?false:true;
	$arrResultado[1]=$listaRegistros;	
	$arrResultado[2]=$listaCarpetas;
	return $arrResultado;
}


function notificarSeguimientoMediaticoAudiencia($idAudiencia)
{
	global $con;
	$notificarMail=true;
	
	$consulta="SELECT carpetaAdministrativa FROM 7007_contenidosCarpetaAdministrativa WHERE 
			idRegistroContenidoReferencia=".$idAudiencia." AND tipoContenido=3";
	$carpetaAdministrativa=$con->obtenerValor($consulta);
	$resultado=esCarpetaSeguimientoMediatico($carpetaAdministrativa);
	
	
	if(!$resultado[0])
	{
		return true;
	}
	
	$arrDestinatario=array();
	$aDestinatario=array();
	$consulta="SELECT nombre,email FROM _586_gNotificacionCorreo WHERE idReferencia in(".$resultado[1].")";
	$rDestinatarios=$con->obtenerFilas($consulta);
	while($fDestinatario=mysql_fetch_row($rDestinatarios))
	{
		$aDestinatario[$fDestinatario[1]]=$fDestinatario[0];
	}
	
	foreach($aDestinatario as $m=>$d)
	{
		$o= array();
		$o[0]=$m;
		$o[1]=$d;
		array_push($arrDestinatario,$o);
	}	
	
	$arrDocumentosAdjuntos=array();
	
	
	$datosEvento=obtenerDatosEventoAudiencia($idAudiencia);
	
	$jueces="";
	foreach($datosEvento->jueces as $j)
	{
		
		if($jueces=="")
			$jueces=$j->nombreJuez." (".$j->titulo.")";
		else
			$jueces.="<br>".$j->nombreJuez." (".$j->titulo.")";
	}
	
	$tlDatosAudiencia='<table>
							<tr>
								<td width="350"><b>Tipo de audiencia:</b></td>
								<td width="450">'.$datosEvento->tipoAudiencia.'</td>
							</tr>
							<tr>
								<td ><b>Carpeta Judicial:</b></td>
								<td >'.$datosEvento->carpetaAdministrativa.'</td>
							</tr>
							<tr>
								<td ><b>Fecha de la audiencia:</b></td>
								<td >'.date("d/m/Y",strtotime($datosEvento->fechaEvento)).'</td>
							</tr>
							<tr>
								<td ><b>Hora de la audiencia:</b></td>
								<td >'.date("H:i",strtotime($datosEvento->horaInicio)).' hrs.</td>
							</tr>
							<tr>
								<td ><b>Inmueble:</b></td>
								<td >'.$datosEvento->edificio.'</td>
							</tr>
							<tr>
								<td ><b>Sala:</b></td>
								<td >'.$datosEvento->sala.'</td>
							</tr>
							<tr>
								<td ><b>Juez:</b></td>
								<td >'.$jueces.'</td>
							</tr>
						</table>
					';
	
	
	$consulta="SELECT notificacionCorreoMediatico FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$idAudiencia;
	$notificacionCorreoMediatico=$con->obtenerValor($consulta);
	
	$tituloMensaje="TSJCDMX: Nueva audiencia programada, Carpeta Judicial: ".$resultado[2];
	$cuerpoMensaje="Se ha programado una nueva audiencia asociada a la Carpeta Judicial <b>".$resultado[2]."</b>, ID del Evento: <b>".$idAudiencia."</b>.<br><br>Datos de la audiencia:<br><br>".$tlDatosAudiencia;
	if($notificacionCorreoMediatico==1)
	{
		$tituloMensaje="TSJCDMX: Modificación de audiencia, Carpeta Judicial: ".$resultado[2];
		$cuerpoMensaje="Se ha registrado una modificación a una audiencia asociada a la Carpeta Judicial <b>".$resultado[2]."</b>, ID del Evento: <b>".$idAudiencia."</b>.<br><br>Datos de la audiencia:<br><br>".$tlDatosAudiencia;
	}
	
	if($notificarMail && @enviarMailNotificacionAlertaMediatico($arrDestinatario,$arrDocumentosAdjuntos,$tituloMensaje,$cuerpoMensaje))
	{
		$consulta="UPDATE 7000_eventosAudiencia SET notificacionCorreoMediatico=1 WHERE idRegistroEvento=".$idAudiencia;
		$con->ejecutarConsulta($consulta);
	}
	else
	{
		if(!$notificarMail)
		{
			$consulta="UPDATE 7000_eventosAudiencia SET notificacionCorreoMediatico=1 WHERE idRegistroEvento=".$idAudiencia;
			$con->ejecutarConsulta($consulta);
		}
	}
	
}

function notificarSeguimientoAcuerdoReparatorio($idAudiencia)
{
	global $con;
	$notificarMail=true;
	
	$consulta="SELECT carpetaAdministrativa FROM 7007_contenidosCarpetaAdministrativa WHERE 
			idRegistroContenidoReferencia=".$idAudiencia." AND tipoContenido=3";
	$carpetaAdministrativa=$con->obtenerValor($consulta);
	$resultado=esCarpetaSeguimientoMediatico($carpetaAdministrativa);
	
	
	if(!$resultado[0])
	{
		return true;
	}
	
	$consulta="SELECT * FROM 3014_registroAcuerdosReparatorios WHERE idEvento=".$idAudiencia;
	$resAcuerdos=$con->obtenerFilas($consulta);
	if($con->filasAfectadas==0)
	{
		return true;
	}
	
	$arrDestinatario=array();
	$aDestinatario=array();
	$consulta="SELECT nombre,email FROM _586_gNotificacionCorreo WHERE idReferencia in(".$resultado[1].")";
	$rDestinatarios=$con->obtenerFilas($consulta);
	while($fDestinatario=mysql_fetch_row($rDestinatarios))
	{
		$aDestinatario[$fDestinatario[1]]=$fDestinatario[0];
	}
	
	foreach($aDestinatario as $m=>$d)
	{
		$o= array();
		$o[0]=$m;
		$o[1]=$d;
		array_push($arrDestinatario,$o);
	}	
	
	$arrDocumentosAdjuntos=array();
	$consulta="SELECT idDocumento FROM 3014_documentosAcuerdoRepatatorio WHERE idEvento=".$idAudiencia;
	$rDocumentos=$con->obtenerFilas($consulta);
	while($fDocumento=mysql_fetch_row($rDocumentos))
	{
		$consulta="SELECT nomArchivoOriginal,tamano FROM 908_archivos WHERE idArchivo=".$fDocumento[0];
		$fArchivo=$con->obtenerPrimeraFila($consulta);

		if($fArchivo[1]<=6291456)
		{
			$o= array();
			$o[0]=obtenerRutaDocumento($fDocumento[0]);
			$o[1]=$fArchivo[0];
			array_push($arrDocumentosAdjuntos,$o);
		}
	}
	
	$tblAcuerdo="<table width='800'>";
	$consulta="SELECT * FROM 3014_registroAcuerdosReparatorios WHERE idEvento=".$idAudiencia;
	$rAcuerdos=$con->obtenerFilas($consulta);
	while($fAcuerdo=mysql_fetch_assoc($rAcuerdos))
	{
		$tblAcuerdo.='<tr><td><b>Tipo de acuerdo: </b>'.($fAcuerdo["tipoCumplimiento"]==1?"Inmediato":"Diferido").'<br><br><b>Fecha de extinsi&oacute;n de la acci&oacute;n penal: </b>'.
		($fAcuerdo["fechaExtincionAccionPenal"]==""?"NO especificado":date("d/m/Y",strtotime($fAcuerdo["fechaExtincionAccionPenal"]))).'<br><br><b>Resumen del acuerdo:</b><br><br>'.$fAcuerdo["resumenAcuerdo"].
						'<br><br><b>Comentarios adicionales:</b><br><br>'.($fAcuerdo["comentariosAdicionales"]==""?"(Sin comentarios)":$fAcuerdo["comentariosAdicionales"]).'<br><br></td></tr>';
	}
	
	$tblAcuerdo.="</table>";
	
	
	$tituloMensaje="TSJCDMX: Nuevo acuerdo reparatorio registrado, Carpeta Judicial: ".$resultado[2];
	$cuerpoMensaje="Se ha registrado un nuevo acuerdo reparatorio asociado a la Carpeta Judicial <b>".$resultado[2]."</b>, Folio de registro: <b>".$idAudiencia."</b>.<br><br><b>Acuerdo registrado:</b><br><br>".$tblAcuerdo;
	
	
	if($notificarMail && @enviarMailNotificacionAlertaMediatico($arrDestinatario,$arrDocumentosAdjuntos,$tituloMensaje,$cuerpoMensaje))
	{
		$consulta="UPDATE _586_comentariosAsuntoMediatico SET notificacionCorreoMediatico=1 WHERE idRegistro=".$idAudiencia;
		$con->ejecutarConsulta($consulta);
	}
	else
	{
		if(!$notificarMail)
		{
			$consulta="UPDATE 3014_registroResutadoAudiencia SET notificacionCorreoMediatico=1 WHERE idEvento=".$idAudiencia;
			$con->ejecutarConsulta($consulta);
		}
	}
	
}

function notificarSeguimientoComentarioNota($idRegistro)
{
	global $con;
	$notificarMail=true;
	
	$consulta="SELECT * FROM _586_comentariosAsuntoMediatico WHERE idRegistro=".$idRegistro;
	$fNota=$con->obtenerPrimeraFilaAsoc($consulta);
	$carpetaAdministrativa=$fNota["carpetaAdministrativa"];
	$resultado=esCarpetaSeguimientoMediatico($carpetaAdministrativa);
	
	
	if(!$resultado[0])
	{
		return true;
	}
	
	$arrDestinatario=array();
	$aDestinatario=array();
	$consulta="SELECT nombre,email FROM _586_gNotificacionCorreo WHERE idReferencia in(".$resultado[1].")";
	$rDestinatarios=$con->obtenerFilas($consulta);
	while($fDestinatario=mysql_fetch_row($rDestinatarios))
	{
		$aDestinatario[$fDestinatario[1]]=$fDestinatario[0];
	}
	
	foreach($aDestinatario as $m=>$d)
	{
		$o= array();
		$o[0]=$m;
		$o[1]=$d;
		array_push($arrDestinatario,$o);
	}	
	
	$arrDocumentosAdjuntos=array();
	$consulta="SELECT recurso FROM _586_adjuntosDocumentosMediaticos WHERE idComentarioAsunto=".$idRegistro." and tipoRecurso=5";
	$rDocumentos=$con->obtenerFilas($consulta);
	while($fDocumento=mysql_fetch_row($rDocumentos))
	{
		$consulta="SELECT nomArchivoOriginal,tamano FROM 908_archivos WHERE idArchivo=".$fDocumento[0];
		$fArchivo=$con->obtenerPrimeraFila($consulta);

		if($fArchivo[1]<=6291456)
		{
			$o= array();
			$o[0]=obtenerRutaDocumento($fDocumento[0]);
			$o[1]=$fArchivo[0];
			array_push($arrDocumentosAdjuntos,$o);
		}
	}
	
	$consulta="SELECT count(*) FROM _586_adjuntosDocumentosMediaticos WHERE idComentarioAsunto=".$idRegistro;
	$totalRecursos=$con->obtenerValor($consulta);
	$tblNota='<table width="800"><tr><td align="justify">'.$fNota["comentario"].'<br></td></tr><tr><td align="right"><span style="font-size:11px">Comentado por: '.obtenerNombreUsuario($fNota["respComentario"]).
			'<br>'.date("d/m/Y H:i",strtotime($fNota["fechaComentario"])).' hrs.<br>Total de recursos adjuntos: '.$totalRecursos.'.</span></td></tr></table>';
	
	
	
	$tituloMensaje="TSJCDMX: Nueva Nota registrada, Carpeta Judicial: ".$resultado[2];
	$cuerpoMensaje="Se ha registrado una nueva nota asociada a la Carpeta Judicial <b>".$resultado[2]."</b>, ID de la NOTA: <b>".$idRegistro."</b>.<br><br><b>NOTA:</b><br><br>".$tblNota;
	
	
	if($notificarMail && @enviarMailNotificacionAlertaMediatico($arrDestinatario,$arrDocumentosAdjuntos,$tituloMensaje,$cuerpoMensaje))
	{
		$consulta="UPDATE _586_comentariosAsuntoMediatico SET notificacionCorreoMediatico=1 WHERE idRegistro=".$idRegistro;
		$con->ejecutarConsulta($consulta);
	}
	else
	{
		if(!$notificarMail)
		{
			$consulta="UPDATE _586_comentariosAsuntoMediatico SET notificacionCorreoMediatico=1 WHERE idRegistro=".$idRegistro;
			$con->ejecutarConsulta($consulta);
		}
	}
	
}