<?php 
include_once("latis/conexionBD.php");
include_once("latis/utiles.php");
include_once("latis/numeroToLetra.php");



function funcionLlenadoPrueba($idDocumento,$idReferencia,$idRegistro,$idFormulario)
{
	global $arrMesLetra;
	$arrValores=array();
	$arrValores["horaInicio"]=date("H:i:s");
	$arrValores["diaInicio"]=date("d");
	$arrValores["mesInicio"]=$arrMesLetra[(date("m")*1)-1];
	$arrValores["anioInicio"]=date("Y");
	$arrValores["leyendaAnioInicio"]=convertirNumeroLetra($arrValores["anioInicio"],false,false);
	$arrValores["noSala"]="Sala 100";
	$arrValores["tipoAudiencia"]="Audiencia de prueba";
	$arrValores["noCarpetaJudicial"]="001/005/2016";
	$arrValores["imputado"]="Juan Luis López Fuentes";
	$arrValores["delito"]="Robo con arma de fuego";
	$arrValores["noJuezAudiencia"]="10";
	$arrValores["nombreJuezAudiencia"]="Jacobo Miranda Herrera";
	
	
	return $arrValores;
}

function generarDocumentoPDFFormato($idRegistroFormato,$descomponerDocumentoMarcadores=true,$bloquearDocumento=0)
{
	global $con;
	global $baseDir;
	global $comandoLibreOffice;
	$consulta="SELECT cuerpoFormato,cadenaFirma,documentoBloqueado,tipoFormato,idFormulario,idRegistro FROM 3000_formatosRegistrados WHERE idRegistroFormato=".$idRegistroFormato;
	
	$fDocumento=$con->obtenerPrimeraFila($consulta);
	if($fDocumento[2]==1)
		return true;
	$cuerpoFormato=bD($fDocumento[0]);
	$firma=$fDocumento[1];
	
	if($firma!="")
	{
		$piePagina='<div title="footer">
					<p align="left" style="margin-top: 0.5cm; margin-bottom: 0cm; line-height: 100%; orphans: 0; widows: 0">
					<font size="1" style="font-size: 8pt">'.$firma.'</font></p>
					</div>';
		
		$cuerpoFormato.=$piePagina;
	}
	
	$nombreArchivo=rand()."_".date("dmY_Hms");
	
	$archivoTemporal=$baseDir."/archivosTemporales/".$nombreArchivo.".html";
	
	if(escribirContenidoArchivo($archivoTemporal,$cuerpoFormato))
	{
		
		$directorioDestino=$baseDir."/archivosTemporales/";
		
		generarDocumentoPDF($archivoTemporal,false,false,false,"",$comandoLibreOffice,$directorioDestino);
		
		if(file_exists($directorioDestino."/".$nombreArchivo.".pdf"))
		{
			
			rename($directorioDestino."/".$nombreArchivo.".pdf",$directorioDestino."/".$nombreArchivo);
			
			$actualizarFormato=true;
			if($descomponerDocumentoMarcadores)
			{
				$actualizarFormato=descomponerDocumentoMarcadores($idRegistroFormato,$cuerpoFormato);
			}
			$consulta="SELECT nombreFormato,categoriaDocumento FROM _10_tablaDinamica WHERE id__10_tablaDinamica=".$fDocumento[3];
			$fDocumentoFinal=$con->obtenerPrimeraFila($consulta);
			$nombreArchivoPDF=$fDocumentoFinal[0];
			$nombreArchivoPDF.=".pdf";
			$idRegistro=registrarDocumentoServidorRepositorio($nombreArchivo,$nombreArchivoPDF,$fDocumentoFinal[1]);
			
			if($idRegistro==-1)
			{
				return false;
			}	
			$idDocumento=$idRegistro;
			
			
			if($actualizarFormato)
			{
				if($bloquearDocumento==1)
				{
					$carpetaAdministrativa=obtenerCarpetaAdministrativaProceso($fDocumento[4],$fDocumento[5]);
					
					if($carpetaAdministrativa!="")
					{
						registrarDocumentoCarpetaAdministrativa($carpetaAdministrativa,$idRegistro,$fDocumento[4],$fDocumento[5]);
					}
					registrarDocumentoResultadoProceso($fDocumento[4],$fDocumento[5],$idRegistro);
				}
				$consulta="update 3000_formatosRegistrados set formatoPDF=1,documentoBloqueado=".$bloquearDocumento.",idDocumento=".$idDocumento." where idRegistroFormato=".$idRegistroFormato;
				$con->ejecutarConsulta($consulta);
				return true;
			}
		}
	}
	
	return false;
	
	
}

function descomponerDocumentoMarcadores($idRegistroFormato,$cuerpoFormato)
{
	global $con;
	
	if(strpos($cuerpoFormato,"<marcadortexto")===false)
		return true;
	
	$cuerpoFormato=strip_tags($cuerpoFormato,"<marcadortexto>");
	$arrMarcadores=$cuerpoFormato=explode("<marcadortexto ",$cuerpoFormato);
	array_splice($arrMarcadores,0,1);
	
	foreach($arrMarcadores as $idMarcador=>$resto)
	{
		$arrResto=explode("</marcadortexto>",$resto);
		$arrMarcadores[$idMarcador]=$arrResto[0];
	}
	
	foreach($arrMarcadores as $idMarcador=>$resto)
	{
		$arrResto=explode('tipomarcador="',$resto);
		$arrMarcadores[$idMarcador]=$arrResto[1];
	}
	
	$arrMarcadoresFinal=array();
	foreach($arrMarcadores as $idMarcador=>$resto)
	{
		$arrResto=explode('">',$resto);
		$oDatos=array();
		$oDatos["tipoMarcador"]=$arrResto[0];
		$oDatos["valor"]=$arrResto[1];
		
		array_push($arrMarcadoresFinal,$oDatos);
		
	}
	
	$x=0;
	$query[$x]="begin";
	$x++;
	$query[$x]="DELETE FROM 3002_marcadoresDocumentos WHERE idRegistroFormato=".$idRegistroFormato;
	$x++;
	foreach($arrMarcadoresFinal as $oDatos)
	{
		$query[$x]="INSERT INTO 3002_marcadoresDocumentos(idRegistroFormato,tipoMarcador,valorMarcador) VALUES(".$idRegistroFormato.
					",".$oDatos["tipoMarcador"].",'".cv($oDatos["valor"])."')";
		$x++;
	}
	
	
	$query[$x]="commit";
	$x++;
	return $con->ejecutarBloque($query);
	
	
	
}

function registrarDocumentoCarpetaAdministrativa($carpetaAdministrativa,$idDocumento,$idFormulario=-1,$idRegistro=-1)
{
	global $con;
	
	$consulta="select count(*) from 7007_contenidosCarpetaAdministrativa where carpetaAdministrativa='".$carpetaAdministrativa."' and idRegistroContenidoReferencia=".$idDocumento.
			" and tipoContenido=1";
	$nReg=$con->obtenerValor($consulta);
	if($nReg>0)
		return true;
	
	$etapaProcesal=obtenerEtapaProcesalCarpetaAdministrativa($carpetaAdministrativa);
	$consulta="INSERT INTO 7007_contenidosCarpetaAdministrativa(carpetaAdministrativa,fechaRegistro,responsableRegistro,tipoContenido,descripcionContenido,idRegistroContenidoReferencia,idFormulario,idRegistro,etapaProcesal)
				VALUES('".$carpetaAdministrativa."','".date("Y-m-d H:i:s")."',".$_SESSION["idUsr"].",1,'',".$idDocumento.",".$idFormulario.",".$idRegistro.",".$etapaProcesal.")";
	return $con->ejecutarConsulta($consulta);
}

function registrarProcesoCarpetaAdministrativa($idFormulario,$idRegistro)//--
{
	global $con;	
	$carpetaAdministrativa=obtenerCarpetaAdministrativaProceso($idFormulario,$idRegistro);
	if($carpetaAdministrativa=="")
		return true;
	$etapaProcesal=obtenerEtapaProcesalCarpetaAdministrativa($carpetaAdministrativa);
	
	$consulta="select count(*) from 7007_contenidosCarpetaAdministrativa where carpetaAdministrativa='".$carpetaAdministrativa."' and  idFormulario=".$idFormulario." and idRegistro=".$idRegistro;
	$nReg=$con->obtenerValor($consulta);
	if($nReg>0)
		return true;
	
	$consulta="INSERT INTO 7007_contenidosCarpetaAdministrativa(carpetaAdministrativa,fechaRegistro,responsableRegistro,tipoContenido,descripcionContenido,idFormulario,idRegistro,etapaProcesal)
				VALUES('".$carpetaAdministrativa."','".date("Y-m-d H:i:s")."',".$_SESSION["idUsr"].",2,'',".$idFormulario.",".$idRegistro.",".$etapaProcesal.")";
	return $con->ejecutarConsulta($consulta);
}

function registrarAudienciaCarpetaAdministrativa($idFormulario,$idRegistro,$idEventoAudiencia)
{
	global $con;
		
	$carpetaAdministrativa=obtenerCarpetaAdministrativaProceso($idFormulario,$idRegistro);
	$etapaProcesal=obtenerEtapaProcesalCarpetaAdministrativa($carpetaAdministrativa);
	if($carpetaAdministrativa=="")
	{
		return true;
	}
	$consulta="select count(*) from 7007_contenidosCarpetaAdministrativa where carpetaAdministrativa='".$carpetaAdministrativa."' and idRegistroContenidoReferencia=".$idEventoAudiencia.
			" and tipoContenido=3";
	$nReg=$con->obtenerValor($consulta);
	if($nReg>0)
		return true;
	
	$consulta="INSERT INTO 7007_contenidosCarpetaAdministrativa(carpetaAdministrativa,fechaRegistro,responsableRegistro,tipoContenido,descripcionContenido,idRegistroContenidoReferencia,idFormulario,idRegistro,etapaProcesal)
				VALUES('".$carpetaAdministrativa."','".date("Y-m-d H:i:s")."',".$_SESSION["idUsr"].",3,'',".$idEventoAudiencia.",".$idFormulario.",".$idRegistro.",".$etapaProcesal.")";
	return $con->ejecutarConsulta($consulta);
}

function registrarDocumentoResultadoProceso($idFormulario,$idRegistro,$idDocumento)
{
	global $con;	
	
	
	$consulta="select count(*) from 9074_documentosRegistrosProceso where idFormulario=".$idFormulario." 
			and idRegistro=".$idRegistro." and idDocumento=".$idDocumento;
	$nReg=$con->obtenerValor($consulta);
	if($nReg>0)
		return true;
	
	$consulta="INSERT INTO 9074_documentosRegistrosProceso(idFormulario,idRegistro,idDocumento,tipoDocumento) 
			VALUES(".$idFormulario.",".$idRegistro.",".$idDocumento.",2)";
	if( $con->ejecutarConsulta($consulta))
	{
		$carpetaAdministrativa=obtenerCarpetaAdministrativaProceso($idFormulario,$idRegistro);
		if($carpetaAdministrativa=="")
			return true;
		registrarDocumentoCarpetaAdministrativa($carpetaAdministrativa,$idDocumento,$idFormulario,$idRegistro);
	}
	return true;
}

function registrarDocumentoReferenciaProceso($idFormulario,$idRegistro,$idDocumento)
{
	global $con;	
	$consulta="INSERT INTO 9074_documentosRegistrosProceso(idFormulario,idRegistro,idDocumento,tipoDocumento) VALUES(".$idFormulario.",".$idRegistro.",".$idDocumento.",1)";

	if( $con->ejecutarConsulta($consulta))
	{

		$carpetaAdministrativa=obtenerCarpetaAdministrativaProceso($idFormulario,$idRegistro);

		if($carpetaAdministrativa=="")
			return true;
		registrarDocumentoCarpetaAdministrativa($carpetaAdministrativa,$idDocumento,$idFormulario,$idRegistro);
	}
	return true;
}

function llenarFormato_0109($idDocumento,$idReferencia,$idRegistro,$idFormulario)
{
	global $con;
	global $arrMesLetra;
	
	
	
	$consulta="select iFormulario,iRegistro FROM _123_tablaDinamica WHERE id__123_tablaDinamica=".$idRegistro;
	$fDatosSolicitud=$con->obtenerPrimeraFila($consulta);
	$idFormulario=$fDatosSolicitud[0];
	$idRegistro=$fDatosSolicitud[1];
	
	$fechaActual=strtotime(date("Y-m-d"));
	$consulta="SELECT idRegistroEvento  FROM 7000_eventosAudiencia WHERE idFormulario=".$idFormulario." AND idRegistroSolicitud=".$idRegistro;
	$idRegistroEvento=$con->obtenerValor($consulta);
	
	
	$datosEvento=obtenerDatosEventoAudiencia($idRegistroEvento);
	$fechaEvento=strtotime($datosEvento->fechaEvento);
	
	
	$jueces="";
	$noJuez="";
	$idJuez=-1;
	foreach($datosEvento->jueces as $j) 
	{
		if($jueces=="")
		{
			$jueces=$j->nombreJuez;
			
		}
		else
		{
			$jueces.=", ".$j->nombreJuez;
			
		}
		$idJuez=$j->idJuez;
	}

	$datosCarpetaAdministrativa=obtenerDatosSujetosProcesalesDelitosCarpetaAdministrativa($datosEvento->carpetaAdministrativa);
	$consulta="SELECT clave FROM _26_tablaDinamica WHERE usuarioJuez=".$idJuez;
	$noJuez=$con->obtenerValor($consulta);
	
	$domicilioSala="";
	$victima="";
	foreach($datosCarpetaAdministrativa["Víctimas"] as $v)
	{
		$nombre=$v["nombre"]." ".$v["apellidoPaterno"]." ".$v["apellidoMaterno"];
		if($victima=="")
			$victima=$nombre;
		else
			$victima.=", ".$nombre;
	}
	
	$nombreDirectorConsignaciones="";
	$nombreDefensorParticularVictima="";
	
	$imputado="";
	foreach($datosCarpetaAdministrativa["Imputados"] as $i)
	{
		$nombre=$i["nombre"]." ".$i["apellidoPaterno"]." ".$i["apellidoMaterno"];
		if($imputado=="")
			$imputado=$nombre;
		else
			$imputado.=", ".$nombre;
	}
	
	$nombreDefensorParticularImputado="";
	
	$consulta="SELECT claveSala FROM _15_tablaDinamica WHERE id__15_tablaDinamica=".$datosEvento->idSala;
	$claveSala=$con->obtenerValor($consulta);
	$arrValores=array();
	$arrValores["diaInicio"]=date("d",$fechaActual);
	$arrValores["mesInicio"]=$arrMesLetra[(date("m",$fechaActual)*1)-1];
	$arrValores["anioInicio"]=date("Y",$fechaActual);
	$arrValores["leyendaAnioInicio"]=convertirNumeroLetra($arrValores["anioInicio"],false,false);
	$arrValores["diaAudiencia"]=date("d",$fechaEvento);
	$arrValores["mesAudiencia"]= $arrMesLetra[(date("m",$fechaEvento)*1)-1];
	$arrValores["anioAudiencia"]=date("Y",$fechaEvento);
	$arrValores["leyendaAnioAudiencia"]=convertirNumeroLetra($arrValores["anioAudiencia"],false,false);
	$arrValores["horaAudiencia"]=date("H:i",strtotime($datosEvento->horaInicio));
	$arrValores["noSala"]=$claveSala;
	$arrValores["domicilioSala"]=$domicilioSala;
	$arrValores["noCarpeta"]=$datosEvento->carpetaAdministrativa;
	$arrValores["nombreJuezAudiencia"]=$jueces;
	$arrValores["noJuezAudiencia"]=$noJuez;
	$arrValores["victima"]=$victima;
	$arrValores["nombreDirectorConsignaciones"]=$nombreDirectorConsignaciones;
	$arrValores["nombreDefensorParticularVictima"]=$nombreDefensorParticularVictima;
	$arrValores["imputado"]=$imputado;
	$arrValores["nombreDefensorParticularImputado"]=$nombreDefensorParticularImputado;
	$consulta="SELECT folioCarpetaInvestigacion FROM _46_tablaDinamica WHERE id__46_tablaDinamica=".$idRegistro;
	
	$arrValores["carpetaInvestigacion"]=$con->obtenerValor($consulta);
	$arrValores["noJuezTramite"]="__";
	$arrValores["nombreJuezTramite"]="__";
	$arrValores["nombreApoderadoLegal"]="_";
	
	
	return $arrValores;
	
	
	
}

function registrarProcesoEventoAudiencia($idFormulario,$idRegistro,$idEventoAudiencia)//--
{
	global $con;	
	$consulta="INSERT INTO 7012_historialAccionesEvento(idRegistroEvento,fechaAccion,idResponsableAccion,tipoAccion,iFormulario,iRegistro)
				VALUES(".$idEventoAudiencia.",'".date("Y-m-d H:i:s")."',".$_SESSION["idUsr"].",2,".$idFormulario.",".$idRegistro.")";

	return $con->ejecutarConsulta($consulta);
}

function determinarAutoNotificacionAudiencia($idFormulario,$idRegistro)
{
	global $con;
	$consulta="SELECT tipoAudiencia FROM _46_tablaDinamica WHERE id__46_tablaDinamica=".$idRegistro;
	$tipoAudiencia=$con->obtenerValor($consulta);
	switch($tipoAudiencia)
	{
		case 18:
			return 106;
		break;
		case 26:
			return 119;
		break;
		default: 
			return 113;
		break;
	}
}

function confirmarAudienciaEvento($idFormulario,$idRegistro)
{
	global $con;
	
	$consulta="SELECT idRegistroEvento FROM 7000_eventosAudiencia WHERE idFormulario=".$idFormulario." AND idRegistroSolicitud=".$idRegistro;
	$idRegistroEvento=$con->obtenerValor($consulta);
	@enviarNotificacionMAJO($idRegistroEvento);
	$consulta="UPDATE 7000_eventosAudiencia SET situacion=1 WHERE idRegistroEvento=".$idRegistroEvento;
	return $con->ejecutarConsulta($consulta);
	
}

function llenarFormatoAutoIncompetencia($idDocumento,$idReferencia,$idRegistro,$idFormulario)
{
	global $con;
	global $arrMesLetra;
	
	$consulta="select iFormulario,iRegistro,carpetaAdministrativa FROM _123_tablaDinamica WHERE id__123_tablaDinamica=".$idRegistro;
	$fDatosSolicitud=$con->obtenerPrimeraFila($consulta);
	$idFormulario=$fDatosSolicitud[0];
	$idRegistro=$fDatosSolicitud[1];
	
	$fechaActual=strtotime(date("Y-m-d"));
	$consulta="SELECT idRegistroEvento  FROM 7000_eventosAudiencia WHERE idFormulario=".$idFormulario." AND idRegistroSolicitud=".$idRegistro;
	$idRegistroEvento=$con->obtenerValor($consulta);
	
	
	$consulta="SELECT unidadGestion FROM  7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$fDatosSolicitud[2]."'";
	$noUnidadGestion=$con->obtenerValor($consulta);
	
	$datosEvento=obtenerDatosEventoAudiencia($idRegistroEvento);
	$fechaEvento=strtotime($datosEvento->fechaEvento);
	
	
	$jueces="";
	$noJuez="";
	$idJuez=-1;
	foreach($datosEvento->jueces as $j) 
	{
		if($jueces=="")
		{
			$jueces=$j->nombreJuez;
			
		}
		else
		{
			$jueces.=", ".$j->nombreJuez;
			
		}
		$idJuez=$j->idJuez;
	}

	$datosCarpetaAdministrativa=obtenerDatosSujetosProcesalesDelitosCarpetaAdministrativa($datosEvento->carpetaAdministrativa);
	$consulta="SELECT clave FROM _26_tablaDinamica WHERE usuarioJuez=".$idJuez;
	$noJuez=$con->obtenerValor($consulta);
	
	$domicilioSala="";
	$victima="";
	foreach($datosCarpetaAdministrativa["Víctimas"] as $v)
	{
		$nombre=$v["nombre"]." ".$v["apellidoPaterno"]." ".$v["apellidoMaterno"];
		if($victima=="")
			$victima=$nombre;
		else
			$victima.=", ".$nombre;
	}
	
	$nombreDirectorConsignaciones="";
	$nombreDefensorParticularVictima="";
	
	$imputado="";
	
	$centroReclusion="";
	foreach($datosCarpetaAdministrativa["Imputados"] as $i)
	{	
		
		$nombre=$i["nombre"]." ".$i["apellidoPaterno"]." ".$i["apellidoMaterno"];
		if($imputado=="")
			$imputado=$nombre;
		else
			$imputado.=", ".$nombre;
		
		$cRelusion=obtenerCentroReclusionImputado($i["idRegistro"]);
		if($centroReclusion=="")
			$centroReclusion=$cRelusion;
		else
			$centroReclusion.=", ".$cRelusion;
		
	}
	
	$datosJuez=obtenerDatosJuezControlPlantillas($idDocumento,$idReferencia,$idRegistro,$idFormulario);
	
	$nombreDefensorParticularImputado="";
	
	
	$delitos="";
	foreach($datosCarpetaAdministrativa["delitos"] as $r)
	{
		$nombre=$r["denominacionDelito"];
		
		if($delitos=="")
			$delitos=$nombre;
		else
			$delitos.=", ".$nombre;
		
	}
	
	$consulta="SELECT claveSala FROM _15_tablaDinamica WHERE id__15_tablaDinamica=".$datosEvento->idSala;
	$claveSala=$con->obtenerValor($consulta);
	$arrValores=array();
	$arrValores["noGestionJudicial"]=$noUnidadGestion;
	$arrValores["diaInicio"]=date("d",$fechaActual);
	$arrValores["mesInicio"]=$arrMesLetra[(date("m",$fechaActual)*1)-1];
	$arrValores["anioInicio"]=date("Y",$fechaActual);
	$arrValores["leyendaAnioInicio"]=convertirNumeroLetra($arrValores["anioInicio"],false,false);
	$arrValores["diaAudiencia"]=date("d",$fechaEvento);
	$arrValores["mesAudiencia"]= $arrMesLetra[(date("m",$fechaEvento)*1)-1];
	$arrValores["anioAudiencia"]=date("Y",$fechaEvento);
	$arrValores["leyendaAnioAudiencia"]=convertirNumeroLetra($arrValores["anioAudiencia"],false,false);
	$arrValores["horaAudiencia"]=date("H:i",strtotime($datosEvento->horaInicio));
	$arrValores["noSala"]=$claveSala;
	$arrValores["domicilioSala"]=$domicilioSala;
	$arrValores["noCarpeta"]=$datosEvento->carpetaAdministrativa;
	$arrValores["nombreJuezAudiencia"]=$jueces;
	$arrValores["noJuezAudiencia"]=$noJuez;
	$arrValores["victima"]=$victima;
	$arrValores["nombreDirectorConsignaciones"]=$nombreDirectorConsignaciones;
	$arrValores["nombreDefensorParticularVictima"]=$nombreDefensorParticularVictima;
	$arrValores["imputado"]=$imputado;
	$arrValores["delito"]=$delitos;
	$arrValores["nombreDefensorParticularImputado"]=$nombreDefensorParticularImputado;
	
	$fDatosIncompetencia=array();
	if($idFormulario!=185)
	{
		$consulta="SELECT numeroExpediente FROM _222_tablaDinamica WHERE idReferencia=".$idRegistro;
		$fDatosIncompetencia=$con->obtenerPrimeraFila($consulta);
	}
	else
	{
		$fDatosIncompetencia[0]=$fDatosSolicitud[2];
		
	}
	$arrValores["noExpediente"]=$fDatosIncompetencia[0];
	if($centroReclusion=="")
	{
		$centroReclusion="[Imputado NO recluído]";
	}
	$arrValores["reclusorioPreventivo"]=$centroReclusion;
	$arrValores["carpetaInvestigacion"]=$fDatosSolicitud[2];
	$arrValores["noJuezTramite"]=$datosJuez["noJuez"];
	$arrValores["nombreJuezTramite"]=$datosJuez["nombreJuez"];
	$arrValores["nombreApoderadoLegal"]="_";
	return $arrValores;
	
	
	
}

function obtenerCentroReclusionImputado($idUsuario)
{
	global $con;
	$centroReclusion=-1;
	$consulta="SELECT centroReclusion,situacion FROM 7013_imputadosCentroReclusion WHERE idImputado=".$idUsuario." order by idRegistroImputa desc";
	$fCentroReclusion=$con->obtenerPrimeraFila($consulta);
	if(!$fCentroReclusion)
	{
		$consulta="SELECT reclusorioDetencion FROM _47_tablaDinamica WHERE id__47_tablaDinamica=".$idUsuario;
		$centroReclusion=$con->obtenerValor($consulta);
		if(($centroReclusion=="")||(($centroReclusion=="-1")))
			$centroReclusion=-1;
	}
	else
	{
		if($fCentroReclusion[1]==0)
			$centroReclusion= -1;
		else
			$centroReclusion=$fCentroReclusion[0];
	}
	
	
	$consulta="SELECT nombre FROM _2_tablaDinamica WHERE id__2_tablaDinamica=".$centroReclusion;
	$nombreCentro=$con->obtenerValor($consulta);
	
	
	return $nombreCentro;
	
	
}

function obtenerIDCentroReclusionImputado($idUsuario)
{
	global $con;
	$centroReclusion=-1;
	$consulta="SELECT centroReclusion,situacion FROM 7013_imputadosCentroReclusion WHERE idImputado=".$idUsuario." order by idRegistroImputado desc";
	$fCentroReclusion=$con->obtenerPrimeraFila($consulta);
	if(!$fCentroReclusion)
	{
		$consulta="SELECT reclusorioDetencion FROM _47_tablaDinamica WHERE id__47_tablaDinamica=".$idUsuario;
		$centroReclusion=$con->obtenerValor($consulta);
		if($centroReclusion=="")
			$centroReclusion=-1;
	}
	else
	{
		$centroReclusion=$fCentroReclusion[0];
		if($fCentroReclusion[1]==0)
		{
			return -1;
		}
	}
	
	return $centroReclusion;
	
	
}

//Notificaciones
function determinacionDocumentoCitatorioNotificacion($idFormulario,$idRegistro)
{
	global $con;
	/*$consulta="SELECT idFiguraJuridica,idPersonaNotificar,iFormulario,iRegistro FROM _72_tablaDinamica WHERE id__72_tablaDinamica=".$idRegistro;
	$fDatosNotificacion=$con->obtenerPrimeraFila($consulta);
	
	
	$consulta="SELECT idFormularioPadre FROM _67_tablaDinamica WHERE id__67_tablaDinamica=".$fDatosNotificacion[3];
	$idFormularioBase=$con->obtenerValor($consulta);
	switch($idFormularioBase)
	{
		case 46:
			switch($fDatosNotificacion[0])
			{
				case 2: //Victima
					return 80;
				break;
				
				case 4: //imputado
					return 22;
				break;
				case 5: //defensor ppublico
					return 25;
				break;
				default:
					return 80;  //Formato de victima
				break;
			}
		break;
	}*/
	$consulta="SELECT tipoDocumento FROM _72_tablaDinamica WHERE  id__72_tablaDinamica=".$idRegistro;
	$tipoDocumento=$con->obtenerValor($consulta);
	return $tipoDocumento;
}

function llenarFormatosCitatorioNotificacion($idDocumento,$idReferencia,$idRegistro,$idFormulario)
{
	global $con;
	
	global $arrMesLetra;
	$arrValores=array();
	
	$fechaActual=strtotime(date("Y-m-d"));
	$consulta="SELECT carpetaAdministrativa,idPersonaNotificar,idFiguraJuridica,idEvento FROM _72_tablaDinamica WHERE id__72_tablaDinamica=".$idRegistro;
	$fDatosCitatorio=$con->obtenerPrimeraFila($consulta);
	
	$datosCarpeta=explode("/",$fDatosCitatorio[0]);
	
	$datosEvento=obtenerDatosEventoAudiencia($fDatosCitatorio[3]);
	$fechaEvento=strtotime($datosEvento->fechaEvento);
	$consulta="SELECT claveSala FROM _15_tablaDinamica WHERE id__15_tablaDinamica=".$datosEvento->idSala;
	$claveSala=$con->obtenerValor($consulta);
	
	$arDatosCarpetaAdministrativa=obtenerDatosSujetosProcesalesDelitosCarpetaAdministrativa($fDatosCitatorio[0]);
	
	
	$imputados="";
	
	foreach($arDatosCarpetaAdministrativa["Imputados"] as $r)
	{
		$nombre=$r["nombre"]." ".$r["apellidoPaterno"]." ".$r["apellidoMaterno"];
		
		if($imputados=="")
			$imputados=$nombre;
		else
			$imputados.=", ".$nombre;
		
	}
	
	$victimas="";
	foreach($arDatosCarpetaAdministrativa["Víctimas"] as $r)
	{
		$nombre=$r["nombre"]." ".$r["apellidoPaterno"]." ".$r["apellidoMaterno"];
		
		if($victimas=="")
			$victimas=$nombre;
		else
			$victimas.=", ".$nombre;
		
	}
	$delitos="";
	foreach($arDatosCarpetaAdministrativa["delitos"] as $r)
	{
		$nombre=$r["denominacionDelito"];
		
		if($delitos=="")
			$delitos=$nombre;
		else
			$delitos.=", ".$nombre;
		
	}
	
	
	
	$datosJuez=obtenerDatosJuezControlPlantillas($idDocumento,$idReferencia,$idRegistro,$idFormulario);
	
	$datosJuezAudiencia=obtenerDatosJuez($fDatosCitatorio[3]);
	
	$arrValores["diaInicio"]=date("d",$fechaActual);
	$arrValores["mesInicio"]=strtoupper($arrMesLetra[(date("m",$fechaActual)*1)-1]);
	$arrValores["anioInicio"]=date("Y",$fechaActual);
	$arrValores["noCarpeta"]=$fDatosCitatorio[0];
	$arrValores["destinatario"]=obtenerNombreImplicado($fDatosCitatorio[1]);
	$arrValores["imputados"]=$imputados;
	$arrValores["victimas"]=$victimas;
	$arrValores["delito"]=$delitos;
	$arrValores["noSala"]=$claveSala;
	$arrValores["horaAudiencia"]=date("H:i",strtotime($datosEvento->horaInicio));
	$arrValores["diaAudiencia"]=date("d",$fechaEvento);
	$arrValores["mesAudiencia"]= $arrMesLetra[(date("m",$fechaEvento)*1)-1];
	$arrValores["anioAudiencia"]=date("Y",$fechaEvento);
	$arrValores["tipoAudiencia"]=$datosEvento->tipoAudiencia;
	$arrValores["noGestionJudicial"]=$datosCarpeta[0];
	$arrValores["leyendaAnioAudiencia"]=convertirNumeroLetra($arrValores["anioAudiencia"],false,false);
	$arrValores["noJuezTramite"]=$datosJuez["noJuez"];
	$arrValores["nombreJuezTramite"]=$datosJuez["nombreJuez"];
	$arrValores["noOficio"]=generarNumeroOficioCarpetaAdministrativa($fDatosCitatorio[0],$idFormulario,$idRegistro);
	$arrValores["domicilioSala"]=obtenerDomicilioSala($datosEvento->idSala);
	$arrValores["noJuezAudiencia"]=$datosJuezAudiencia["noJuez"];
	$arrValores["nombreJuezAudiencia"]=$datosJuezAudiencia["nombreJuez"];
	
	return $arrValores;
	
}

function llenarFormatosCitatorioDefensoriaPublica($idDocumento,$idReferencia,$idRegistro,$idFormulario)
{
	global $con;
	
	global $arrMesLetra;
	$arrValores=array();
	
	$fechaActual=strtotime(date("Y-m-d"));
	$consulta="SELECT carpetaAdministrativa,idPersonaNotificar,idFiguraJuridica,idEvento FROM _72_tablaDinamica WHERE id__72_tablaDinamica=".$idRegistro;
	$fDatosCitatorio=$con->obtenerPrimeraFila($consulta);

	$datosCarpeta=explode("/",$fDatosCitatorio[0]);
	
	$datosEvento=obtenerDatosEventoAudiencia($fDatosCitatorio[3]);
	$fechaEvento=strtotime($datosEvento->fechaEvento);
	$consulta="SELECT claveSala FROM _15_tablaDinamica WHERE id__15_tablaDinamica=".$datosEvento->idSala;
	$claveSala=$con->obtenerValor($consulta);
	
	$arDatosCarpetaAdministrativa=obtenerDatosSujetosProcesalesDelitosCarpetaAdministrativa($fDatosCitatorio[0]);
	
	
	$imputados="";
	
	foreach($arDatosCarpetaAdministrativa["Imputados"] as $r)
	{
		if($r["requiereDefensorOficio"]==1)
		{
			$nombre=$r["nombre"]." ".$r["apellidoPaterno"]." ".$r["apellidoMaterno"];
			
			if($imputados=="")
				$imputados=$nombre;
			else
				$imputados.=", ".$nombre;
		}
	}
	
	if($imputados=="")
	{
		$imputados="[NO EXISTE IMPUTADO REQUIRIENDO DEFENSORIA]";
	}
	
	$victimas="";
	foreach($arDatosCarpetaAdministrativa["Víctimas"] as $r)
	{
		$nombre=$r["nombre"]." ".$r["apellidoPaterno"]." ".$r["apellidoMaterno"];
		
		if($victimas=="")
			$victimas=$nombre;
		else
			$victimas.=", ".$nombre;
		
	}
	$delitos="";
	foreach($arDatosCarpetaAdministrativa["delitos"] as $r)
	{
		$nombre=$r["denominacionDelito"];
		
		if($delitos=="")
			$delitos=$nombre;
		else
			$delitos.=", ".$nombre;
		
	}
	
	
	
	$datosJuez=obtenerDatosJuezControlPlantillas($idDocumento,$idReferencia,$idRegistro,$idFormulario);
	
	$datosJuezAudiencia=obtenerDatosJuez($fDatosCitatorio[3]);
	
	$arrValores["diaInicio"]=date("d",$fechaActual);
	$arrValores["mesInicio"]=strtoupper($arrMesLetra[(date("m",$fechaActual)*1)-1]);
	$arrValores["anioInicio"]=date("Y",$fechaActual);
	$arrValores["noCarpeta"]=$fDatosCitatorio[0];
	$arrValores["destinatario"]=obtenerNombreImplicado($fDatosCitatorio[1]);
	$arrValores["imputados"]=$imputados;
	$arrValores["victimas"]=$victimas;
	$arrValores["delito"]=$delitos;
	$arrValores["noSala"]=$claveSala;
	$arrValores["horaAudiencia"]=date("H:i",strtotime($datosEvento->horaInicio));
	$arrValores["diaAudiencia"]=date("d",$fechaEvento);
	$arrValores["mesAudiencia"]= $arrMesLetra[(date("m",$fechaEvento)*1)-1];
	$arrValores["anioAudiencia"]=date("Y",$fechaEvento);
	$arrValores["tipoAudiencia"]=$datosEvento->tipoAudiencia;
	$arrValores["noGestionJudicial"]=$datosCarpeta[0];
	$arrValores["leyendaAnioAudiencia"]=convertirNumeroLetra($arrValores["anioAudiencia"],false,false);
	$arrValores["noJuezTramite"]=$datosJuez["noJuez"];
	$arrValores["nombreJuezTramite"]=$datosJuez["nombreJuez"];
	$arrValores["noOficio"]=generarNumeroOficioCarpetaAdministrativa($fDatosCitatorio[0],$idFormulario,$idRegistro);
	$arrValores["domicilioSala"]=obtenerDomicilioSala($datosEvento->idSala);
	$arrValores["noJuezAudiencia"]=$datosJuezAudiencia["noJuez"];
	$arrValores["nombreJuezAudiencia"]=$datosJuezAudiencia["nombreJuez"];
	
	return $arrValores;
	
}

function llenarFormatosMedicoLegista($idDocumento,$idReferencia,$idRegistro,$idFormulario)
{
	global $con;
	
	global $arrMesLetra;
	$arrValores=array();
	
	$fechaActual=strtotime(date("Y-m-d"));
	$consulta="SELECT carpetaAdministrativa,idPersona,tipoFigura,iEvento FROM _123_tablaDinamica WHERE id__123_tablaDinamica=".$idRegistro;
	$fDatos=$con->obtenerPrimeraFila($consulta);
	
	$datosCarpeta=explode("/",$fDatos[0]);
	
	$consulta="SELECT nombre FROM 800_usuarios u,807_usuariosVSRoles r,801_adscripcion a 
				WHERE r.idUsuario=u.idUsuario AND r.idRol=12 AND a.idUsuario=u.idUsuario AND a.codigoUnidad='".$datosCarpeta[0]."'";
	$nombreDirectorUGJ=$con->obtenerValor($consulta);
	
	$consulta="SELECT CONCAT(nombre,' ',apellidoPaterno,' ',apellidoMaterno) FROM _47_tablaDinamica WHERE id__47_tablaDinamica=".$fDatos[1];
	$nombreImputado=$con->obtenerValor($consulta);
	$arrValores["diaInicio"]=date("d",$fechaActual);
	$arrValores["mesInicio"]=strtoupper($arrMesLetra[(date("m",$fechaActual)*1)-1]);
	$arrValores["anioInicio"]=date("Y",$fechaActual);
	$arrValores["noCarpeta"]=$fDatos[0];
	$arrValores["imputado"]=$nombreImputado;
	$arrValores["nombreDirectorUGJ"]=$nombreDirectorUGJ;
	$arrValores["noGestionJudicial"]=strtoupper(convertirNumeroLetra($datosCarpeta[0]*1,false,false));
	$arrValores["noOficio"]=generarNumeroOficioCarpetaAdministrativa($fDatos[0],$idFormulario,$idRegistro);
	
	return $arrValores;
	
}

function llenarFormatoTrasladoGuardiaCustodia($idDocumento,$idReferencia,$idRegistro,$idFormulario) //OK
{
	global $con;
	
	global $arrMesLetra;
	$arrValores=array();
	
	$fechaActual=strtotime(date("Y-m-d"));
	$consulta="SELECT carpetaAdministrativa,idPersona,tipoFigura,iEvento FROM _123_tablaDinamica WHERE id__123_tablaDinamica=".$idRegistro;
	$fDatos=$con->obtenerPrimeraFila($consulta);
	
	$datosCarpeta=explode("/",$fDatos[0]);
	
	$datosEvento=obtenerDatosEventoAudiencia($fDatos[3]);
	$fechaEvento=strtotime($datosEvento->fechaEvento);
	$consulta="SELECT nombre FROM 800_usuarios u,807_usuariosVSRoles r,801_adscripcion a 
				WHERE r.idUsuario=u.idUsuario AND r.idRol=12 AND a.idUsuario=u.idUsuario AND a.codigoUnidad='".$datosCarpeta[0]."'";
	$nombreDirectorUGJ=$con->obtenerValor($consulta);
	
	$consulta="SELECT CONCAT(nombre,' ',apellidoPaterno,' ',apellidoMaterno) FROM _47_tablaDinamica WHERE id__47_tablaDinamica=".$fDatos[1];
	$nombreImputado=$con->obtenerValor($consulta);
	$arrValores["diaInicio"]=date("d",$fechaActual);
	$arrValores["mesInicio"]=strtoupper($arrMesLetra[(date("m",$fechaActual)*1)-1]);
	$arrValores["anioInicio"]=date("Y",$fechaActual);
	$arrValores["noCarpeta"]=$fDatos[0];
	$arrValores["imputado"]=$nombreImputado;
	$arrValores["nombreDirectorUGJ"]=$nombreDirectorUGJ;
	$arrValores["noGestionJudicial"]=strtoupper(convertirNumeroLetra($datosCarpeta[0]*1,false,false));
	$arrValores["noOficio"]=generarNumeroOficioCarpetaAdministrativa($fDatos[0],$idFormulario,$idRegistro);
	
	$consulta="SELECT claveSala FROM _15_tablaDinamica WHERE id__15_tablaDinamica=".$datosEvento->idSala;
	$claveSala=$con->obtenerValor($consulta);
	
	$arrValores["noSala"]=$claveSala;
	$arrValores["horaAudiencia"]=date("H:i",strtotime($datosEvento->horaInicio));
	$arrValores["diaAudiencia"]=date("d",$fechaEvento);
	$arrValores["mesAudiencia"]= $arrMesLetra[(date("m",$fechaEvento)*1)-1];
	$arrValores["anioAudiencia"]=date("Y",$fechaEvento);
	
	
	$consulta="SELECT d.denominacionDelito FROM _61_chkDelitosImputado c,_35_denominacionDelito d ,_61_tablaDinamica del
				WHERE idOpcion=".$fDatos[1]." AND d.id__35_denominacionDelito=del.denominacionDelito AND del.id__61_tablaDinamica=c.idPadre";
	$listaDelitos=$con->obtenerListaValores($consulta);
	$arrValores["delito"]=$listaDelitos;
	return $arrValores;
	
}

function llenarFormatoConstanciaRecepcionSolicitudAudiencia($idDocumento,$idReferencia,$idRegistro,$idFormulario)
{
	global $con;
	global $arrMesLetra;
	$arrValores=array();
	
	$fechaActual=strtotime(date("Y-m-d"));
	$consulta="SELECT carpetaAdministrativa,idPersona,tipoFigura,iEvento,iFormulario,iRegistro 
			FROM _123_tablaDinamica WHERE id__123_tablaDinamica=".$idRegistro;
			
	$fDatos=$con->obtenerPrimeraFila($consulta);
	
	
	$datosCarpeta=explode("/",$fDatos[0]);
	
	$datosEvento=obtenerDatosEventoAudiencia($fDatos[3]);

	
	$arDatosCarpetaAdministrativa=obtenerDatosSujetosProcesalesDelitosCarpetaAdministrativa($fDatos[0]);


	$fechaEvento=strtotime($datosEvento->fechaEvento);
	$unidadGestion=obtenerUnidadGestionCarpetaAdmnistrativa($fDatos[0]);
	$consulta="SELECT nombre FROM 800_usuarios u,807_usuariosVSRoles r,801_adscripcion a 
				WHERE r.idUsuario=u.idUsuario AND r.idRol=12 AND a.idUsuario=u.idUsuario AND a.codigoUnidad='".$unidadGestion."'";
	$nombreDirectorUGJ=$con->obtenerValor($consulta);
	
	//$consulta="SELECT CONCAT(nombre,' ',apellidoPaterno,' ',apellidoMaterno) FROM _47_tablaDinamica WHERE id__47_tablaDinamica=".$fDatos[1];
	//$nombreImputado=$con->obtenerValor($consulta);
	
	$jueces="";
	$noJuez="";
	$idJuez=-1;
	foreach($datosEvento->jueces as $j) 
	{
		if($jueces=="")
		{
			$jueces=$j->nombreJuez;
			
		}
		else
		{
			$jueces.=", ".$j->nombreJuez;
			
		}
		$idJuez=$j->idJuez;
	}
	
	$consulta="SELECT clave FROM _26_tablaDinamica WHERE usuarioJuez=".$idJuez;
	$noJuez=$con->obtenerValor($consulta);
	
	
	$consulta="SELECT fechaCreacion,tipoAudiencia FROM _".$fDatos[4]."_tablaDinamica WHERE id__".$fDatos[4]."_tablaDinamica=".$fDatos[5];

	$fDatosSolicitud=$con->obtenerPrimeraFila($consulta);
	
	
	$fechaSolicitud=strtotime($fDatosSolicitud[0]);
	
	$consulta="SELECT tipoAudiencia FROM _4_tablaDinamica WHERE id__4_tablaDinamica=".$fDatosSolicitud[1];
	$tipoAudiencia=$con->obtenerValor($consulta);
	
	$arrValores["horaRecepcion"]=date("H",$fechaSolicitud);
	$arrValores["minutosRecepcion"]=date("i",$fechaSolicitud);
	$arrValores["diaRecepcion"]=date("d",$fechaSolicitud);
	$arrValores["mesRecepcion"]=$arrMesLetra[(date("m",$fechaSolicitud)*1)-1];
	$arrValores["anioRecepcion"]=date("Y",$fechaSolicitud);
	
	$arrValores["leyendaAnioRecepcion"]=convertirNumeroLetra($arrValores["anioRecepcion"],false,false);
	
	$arrValores["noCarpeta"]=$fDatos[0];
	$arrValores["imputado"]="";//$nombreImputado;
	$arrValores["nombreDirectorUGJ"]=$nombreDirectorUGJ;
	$arrValores["noGestionJudicial"]=strtoupper(convertirNumeroLetra($datosCarpeta[0]*1,false,false));
	$arrValores["tipoAudiencia"]=$tipoAudiencia;
	
	$consulta="SELECT claveSala FROM _15_tablaDinamica WHERE id__15_tablaDinamica=".$datosEvento->idSala;
	$claveSala=$con->obtenerValor($consulta);	
	$arrValores["noSala"]=$claveSala;
	$arrValores["horaAudiencia"]=date("H",strtotime($datosEvento->horaInicio));
	$arrValores["minutosAudiencia"]=date("i",strtotime($datosEvento->horaInicio));
	$arrValores["diaAudiencia"]=date("d",$fechaEvento);
	$arrValores["mesAudiencia"]= $arrMesLetra[(date("m",$fechaEvento)*1)-1];
	$arrValores["anioAudiencia"]=date("Y",$fechaEvento);
	$arrValores["leyendaAnioAudiencia"]=convertirNumeroLetra($arrValores["anioAudiencia"],false,false);
	$arrValores["noJuezAudiencia"]=$noJuez;
	$arrValores["nombreJuezAudiencia"]=$jueces;
	$arrValores["nombreJuezTramite"]="[POR DEFINIR]";
	
	$listaDelitos="";
	foreach($arDatosCarpetaAdministrativa["delitos"] as $d)
	{
		if($listaDelitos=="")
			$listaDelitos=$d["denominacionDelito"];
		else
			$listaDelitos.=", ".$d["denominacionDelito"];
	}
	
	$arrValores["delito"]=$listaDelitos;
	
	$listaImputados="";
	foreach($arDatosCarpetaAdministrativa["Imputados"] as $i)
	{
		$nombreImputado=$i["nombre"]." ".$i["apellidoPaterno"]." ".$i["apellidoMaterno"];
		if($listaImputados=="")
			$listaImputados=$nombreImputado;
		else
			$listaImputados.=", ".$nombreImputado;
	}
	
	$arrValores["imputado"]=$listaImputados;
	
	$listaVictimas="";
	foreach($arDatosCarpetaAdministrativa["Víctimas"] as $v)
	{
		$nombreVictima=$v["nombre"]." ".$v["apellidoPaterno"]." ".$v["apellidoMaterno"];
		if($listaVictimas=="")
			$listaVictimas=$nombreVictima;
		else
			$listaVictimas.=", ".$nombreVictima;
	}
	$arrValores["victima"]=$listaVictimas;
	
	return $arrValores;
	
}

function llenarFormatosAcuerdoGeneral($idDocumento,$idReferencia,$idRegistro,$idFormulario)
{
	global $con;
	
	global $arrMesLetra;
	$arrValores=array();
	
	$fechaActual=strtotime(date("Y-m-d"));
	$consulta="SELECT carpetaAdministrativa FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$fDatos=$con->obtenerPrimeraFila($consulta);
	
	$datosCarpeta=explode("/",$fDatos[0]);
	
	
	
	
	$arrValores["diaInicio"]=date("d",$fechaActual);
	$arrValores["mesInicio"]=strtoupper($arrMesLetra[(date("m",$fechaActual)*1)-1]);
	$arrValores["anioInicio"]=date("Y",$fechaActual);
	$arrValores["fecha"]=$arrValores["diaInicio"]." de ".$arrValores["mesInicio"]." de ".$arrValores["anioInicio"];
	$arrValores["noCarpeta"]=$fDatos[0];
	
	$arrValores["noOficio"]=generarNumeroOficioCarpetaAdministrativa($fDatos[0],$idFormulario,$idRegistro);
	
	return $arrValores;
	
}

function llenarFormato007PoliciaProcesal($idDocumento,$idReferencia,$idRegistro,$idFormulario)
{
	global $con;
	
	global $arrMesLetra;
	$arrValores=array();
	
	$fechaActual=strtotime(date("Y-m-d"));
	$consulta="SELECT carpetaAdministrativa,idPersona,tipoFigura,iEvento,iRegistro FROM _123_tablaDinamica WHERE id__123_tablaDinamica=".$idRegistro;
	$fDatosCitatorio=$con->obtenerPrimeraFila($consulta);
	
	$datosCarpeta=explode("/",$fDatosCitatorio[0]);
	
	$datosEvento=obtenerDatosEventoAudiencia($fDatosCitatorio[3]);
	$fechaEvento=strtotime($datosEvento->fechaEvento);
	$consulta="SELECT claveSala FROM _15_tablaDinamica WHERE id__15_tablaDinamica=".$datosEvento->idSala;
	$claveSala=$con->obtenerValor($consulta);
	
	$arDatosCarpetaAdministrativa=obtenerDatosSujetosProcesalesDelitosCarpetaAdministrativa($fDatosCitatorio[0]);
	
	
	$imputados="";
	
	$consulta="SELECT CONCAT(nombre,' ',apellidoPaterno,' ',apellidoMaterno) FROM _47_tablaDinamica WHERE id__47_tablaDinamica=".$fDatosCitatorio[1];
	$imputados=$con->obtenerValor($consulta);
	
	$victimas="";
	foreach($arDatosCarpetaAdministrativa["Víctimas"] as $r)
	{
		$nombre=$r["nombre"]." ".$r["apellidoPaterno"]." ".$r["apellidoMaterno"];
		
		if($victimas=="")
			$victimas=inicialesNombre($nombre).".";
		else
			$victimas.="".inicialesNombre($nombre);
		
	}
	$delitos="";
	foreach($arDatosCarpetaAdministrativa["delitos"] as $r)
	{
		$nombre=$r["denominacionDelito"];
		
		if($delitos=="")
			$delitos=$nombre;
		else
			$delitos.=", ".$nombre;
		
	}
	
	$consulta="SELECT reclusorios FROM _157_tablaDinamica WHERE id__157_tablaDinamica=".$fDatosCitatorio[4];
	$idReclusorio=$con->obtenerValor($consulta);
	$consulta="SELECT upper(nombre) FROM _2_tablaDinamica WHERE id__2_tablaDinamica=".$idReclusorio;
	$nombreReclusorio=$con->obtenerValor($consulta);
	
	
	$unidadGestion=obtenerUnidadGestionCarpetaAdmnistrativa($fDatosCitatorio[0]);
	$consulta="SELECT nombre FROM 800_usuarios u,807_usuariosVSRoles r,801_adscripcion a 
				WHERE r.idUsuario=u.idUsuario AND r.idRol=12 AND a.idUsuario=u.idUsuario AND a.codigoUnidad='".$unidadGestion."'";
	$nombreDirectorUGJ=$con->obtenerValor($consulta);
	
	
	$consulta="SELECT nombreTitular,apPaterno,apMaterno FROM _149_tablaDinamica WHERE id__149_tablaDinamica=4";
	$fPoliciaProcesal=$con->obtenerPrimeraFila($consulta);
	
	
	$datosJuez=obtenerDatosJuezControlPlantillas($idDocumento,$idReferencia,$idRegistro,$idFormulario);
	
	$datosJuezAudiencia=obtenerDatosJuez($fDatosCitatorio[3]);
	
	$arrValores["diaInicio"]=date("d",$fechaActual);
	$arrValores["mesInicio"]=ucwords($arrMesLetra[(date("m",$fechaActual)*1)-1]);
	$arrValores["anioInicio"]=date("Y",$fechaActual);
	
	
	$arrValores["fechaHoy"]=" a ".$arrValores["diaInicio"]." de ".$arrValores["mesInicio"]." de ".$arrValores["anioInicio"];
	
	$arrValores["noCarpeta"]=$fDatosCitatorio[0];
	$arrValores["destinatario"]=obtenerNombreImplicado($fDatosCitatorio[1]);
	$arrValores["imputados"]=$imputados;
	$arrValores["inicialesVictima"]=$victimas;
	$arrValores["delito"]=$delitos;
	$arrValores["noSala"]=$claveSala;
	$arrValores["horaAudiencia"]=date("H",strtotime($datosEvento->horaInicio));
	$arrValores["minutosAudiencia"]=date("i",strtotime($datosEvento->horaInicio));
	$arrValores["diaAudiencia"]=date("d",$fechaEvento);
	$arrValores["mesAudiencia"]= $arrMesLetra[(date("m",$fechaEvento)*1)-1];
	$arrValores["anioAudiencia"]=date("Y",$fechaEvento);
	$arrValores["tipoAudiencia"]=$datosEvento->tipoAudiencia;
	$arrValores["noGestionJudicial"]=$datosCarpeta[0];
	$arrValores["leyendaAnioAudiencia"]=convertirNumeroLetra($arrValores["anioAudiencia"],false,false);
	$arrValores["noJuezTramite"]=$datosJuez["noJuez"];
	$arrValores["nombreJuezTramite"]=$datosJuez["nombreJuez"];
	$arrValores["noOficio"]=generarNumeroOficioCarpetaAdministrativa($fDatosCitatorio[0],$idFormulario,$idRegistro);
	$arrValores["domicilioSala"]=obtenerDomicilioSala($datosEvento->idSala);
	$arrValores["noJuezAudiencia"]=$datosJuezAudiencia["noJuez"];
	$arrValores["nombreJuezAudiencia"]=$datosJuezAudiencia["nombreJuez"];
	$arrValores["nombreDirectorUGJ"]=$nombreDirectorUGJ;
	$arrValores["noGestionJudicial"]=strtoupper(convertirNumeroLetra($datosCarpeta[0]*1,false,false));
	$arrValores["reclusorioPreventivo"]=$nombreReclusorio;
	$arrValores["nombrePrimerOficial"]=$fPoliciaProcesal[0]." ".$fPoliciaProcesal[1]." ".$fPoliciaProcesal[2];
	return $arrValores;
}

function llenarFormatoActaMinima($idDocumento,$idReferencia,$idRegistro,$idFormulario)
{
	global $con;
	
	
	global $arrMesLetra;
	$arrValores=array();
	
	$fechaActual=strtotime(date("Y-m-d"));
	$consulta="SELECT carpetaAdministrativa,idEvento FROM _210_tablaDinamica WHERE id__210_tablaDinamica=".$idRegistro;
	$fDatosCitatorio=$con->obtenerPrimeraFila($consulta);
	
	$datosCarpeta=explode("/",$fDatosCitatorio[0]);
	
	$datosEvento=obtenerDatosEventoAudiencia($fDatosCitatorio[1]);
	$fechaEvento=strtotime($datosEvento->fechaEvento);
	$consulta="SELECT claveSala FROM _15_tablaDinamica WHERE id__15_tablaDinamica=".$datosEvento->idSala;
	$claveSala=$con->obtenerValor($consulta);
	
	$arDatosCarpetaAdministrativa=obtenerDatosSujetosProcesalesDelitosCarpetaAdministrativa($fDatosCitatorio[0]);
	
	
	$imputados="";
	
	foreach($arDatosCarpetaAdministrativa["Imputados"] as $r)
	{
		$nombre=$r["nombre"]." ".$r["apellidoPaterno"]." ".$r["apellidoMaterno"];
		
		if($imputados=="")
			$imputados=$nombre;
		else
			$imputados.=", ".$nombre;
		
	}
	
	$victimas="";
	foreach($arDatosCarpetaAdministrativa["Víctimas"] as $r)
	{
		$nombre=$r["nombre"]." ".$r["apellidoPaterno"]." ".$r["apellidoMaterno"];
		
		if($victimas=="")
			$victimas=$nombre;
		else
			$victimas.=", ".$nombre;
		
	}
	$delitos="";
	foreach($arDatosCarpetaAdministrativa["delitos"] as $r)
	{
		$nombre=$r["denominacionDelito"];
		
		if($delitos=="")
			$delitos=$nombre;
		else
			$delitos.=", ".$nombre;
		
	}
	
	
	
	$datosJuez=obtenerDatosJuezControlPlantillas($idDocumento,$idReferencia,$idRegistro,$idFormulario);
	
	$datosJuezAudiencia=obtenerDatosJuez($fDatosCitatorio[1]);
	
	$arrValores["diaInicio"]=date("d",$fechaActual);
	$arrValores["mesInicio"]=strtoupper($arrMesLetra[(date("m",$fechaActual)*1)-1]);
	$arrValores["anioInicio"]=date("Y",$fechaActual);
	$arrValores["noCarpeta"]=$fDatosCitatorio[0];
	$arrValores["destinatario"]=obtenerNombreImplicado($fDatosCitatorio[1]);
	$arrValores["imputados"]=$imputados;
	$arrValores["victimas"]=$victimas;
	$arrValores["delito"]=$delitos;
	$arrValores["noSala"]=$claveSala;
	$arrValores["horaAudiencia"]=date("H:i",strtotime($datosEvento->horaInicio));
	$arrValores["diaAudiencia"]=date("d",$fechaEvento);
	$arrValores["mesAudiencia"]= $arrMesLetra[(date("m",$fechaEvento)*1)-1];
	$arrValores["anioAudiencia"]=date("Y",$fechaEvento);
	$arrValores["tipoAudiencia"]=$datosEvento->tipoAudiencia;
	$arrValores["noGestionJudicial"]=$datosCarpeta[0];
	$arrValores["leyendaAnioAudiencia"]=convertirNumeroLetra($arrValores["anioAudiencia"],false,false);
	$arrValores["noJuezTramite"]=$datosJuez["noJuez"];
	$arrValores["nombreJuezTramite"]=$datosJuez["nombreJuez"];
	$arrValores["noOficio"]=generarNumeroOficioCarpetaAdministrativa($fDatosCitatorio[0],$idFormulario,$idRegistro);
	$arrValores["domicilioSala"]=obtenerDomicilioSala($datosEvento->idSala);
	$arrValores["noJuezAudiencia"]=$datosJuezAudiencia["noJuez"];
	$arrValores["nombreJuezAudiencia"]=$datosJuezAudiencia["nombreJuez"];
	
	
	$arrValores["nombreAgenteMinisterio"]="____";
	$arrValores["nombreAsesorJuridico"]="____";
	$arrValores["nombreDefensorParticular"]="____";
	$arrValores["horaAudienciaFinal"]="____";
	$arrValores["minutosAudienciaFinal"]="____";
	$arrValores["diaAudienciaFinal"]=date("d",$fechaEvento);
	$arrValores["mesAudienciaFinal"]=$arrMesLetra[(date("m",$fechaEvento)*1)-1];
	$arrValores["anioAudienciaFinal"]=date("Y",$fechaEvento);
	$arrValores["leyendaAnioAudienciaFinal"]=convertirNumeroLetra($arrValores["anioAudiencia"],false,false);
	return $arrValores;
	
}
	
function selecionarActaMinimaTipoAudiencia($idFormulario,$idRegistro)
{
	global $con;
	
	
	$consulta="SELECT idEvento FROM _210_tablaDinamica WHERE id__210_tablaDinamica=".$idRegistro;
	$idEvento=$con->obtenerValor($consulta);
	
	
	$consulta="SELECT tipoAudiencia FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$idEvento;
	$tipoAudiencia=$con->obtenerValor($consulta);
	
	$consulta="SELECT actaMinima FROM _4_tablaDinamica WHERE id__4_tablaDinamica=".$tipoAudiencia;
	$actaMinima=$con->obtenerValor($consulta);
	
	return $actaMinima;
	
	
}

function llenarFormatoOficioMedidaCautelar($idDocumento,$idReferencia,$idRegistro,$idFormulario)
{
	global $con;
	
	global $arrMesLetra;
	$arrValores=array();
	
	$fechaActual=strtotime(date("Y-m-d"));
	$consulta="SELECT carpetaAdministrativa,idPersona,tipoFigura,iEvento,iRegistro FROM _123_tablaDinamica WHERE id__123_tablaDinamica=".$idRegistro;
	$fDatosCitatorio=$con->obtenerPrimeraFila($consulta);
	
	$datosCarpeta=explode("/",$fDatosCitatorio[0]);
	
	$datosEvento=obtenerDatosEventoAudiencia($fDatosCitatorio[3]);
	$fechaEvento=strtotime($datosEvento->fechaEvento);
	$consulta="SELECT claveSala FROM _15_tablaDinamica WHERE id__15_tablaDinamica=".$datosEvento->idSala;
	$claveSala=$con->obtenerValor($consulta);
	
	$arDatosCarpetaAdministrativa=obtenerDatosSujetosProcesalesDelitosCarpetaAdministrativa($fDatosCitatorio[0]);
	
	
	$imputados="";
	
	$consulta="SELECT CONCAT(nombre,' ',apellidoPaterno,' ',apellidoMaterno) FROM _47_tablaDinamica WHERE id__47_tablaDinamica=".$fDatosCitatorio[1];
	$imputados=$con->obtenerValor($consulta);
	
	$victimas="";
	foreach($arDatosCarpetaAdministrativa["Víctimas"] as $r)
	{
		$nombre=$r["nombre"]." ".$r["apellidoPaterno"]." ".$r["apellidoMaterno"];
		
		if($victimas=="")
			$victimas=($nombre).".";
		else
			$victimas.="".($nombre);
		
	}
	$delitos="";
	foreach($arDatosCarpetaAdministrativa["delitos"] as $r)
	{
		$nombre=$r["denominacionDelito"];
		
		if($delitos=="")
			$delitos=$nombre;
		else
			$delitos.=", ".$nombre;
		
	}
	
	
	
	$unidadGestion=obtenerUnidadGestionCarpetaAdmnistrativa($fDatosCitatorio[0]);
	$consulta="SELECT nombre FROM 800_usuarios u,807_usuariosVSRoles r,801_adscripcion a 
				WHERE r.idUsuario=u.idUsuario AND r.idRol=12 AND a.idUsuario=u.idUsuario AND a.codigoUnidad='".$unidadGestion."'";
	$nombreDirectorUGJ=$con->obtenerValor($consulta);
	
	
	
	$datosJuez=obtenerDatosJuezControlPlantillas($idDocumento,$idReferencia,$idRegistro,$idFormulario);
	
	$datosJuezAudiencia=obtenerDatosJuez($fDatosCitatorio[3]);
	
	$arrValores["diaInicio"]=date("d",$fechaActual);
	$arrValores["mesInicio"]=ucwords($arrMesLetra[(date("m",$fechaActual)*1)-1]);
	$arrValores["anioInicio"]=date("Y",$fechaActual);
	
	
	$arrValores["fechaHoy"]=" a ".$arrValores["diaInicio"]." de ".$arrValores["mesInicio"]." de ".$arrValores["anioInicio"];
	
	$arrValores["noCarpeta"]=$fDatosCitatorio[0];
	$arrValores["destinatario"]=obtenerNombreImplicado($fDatosCitatorio[1]);
	$arrValores["imputados"]=$imputados;
	$arrValores["inicialesVictima"]=$victimas;
	$arrValores["delito"]=$delitos;
	$arrValores["noSala"]=$claveSala;
	$arrValores["horaAudiencia"]=date("H",strtotime($datosEvento->horaInicio));
	$arrValores["minutosAudiencia"]=date("i",strtotime($datosEvento->horaInicio));
	$arrValores["diaAudiencia"]=date("d",$fechaEvento);
	$arrValores["mesAudiencia"]= $arrMesLetra[(date("m",$fechaEvento)*1)-1];
	$arrValores["anioAudiencia"]=date("Y",$fechaEvento);
	$arrValores["tipoAudiencia"]=$datosEvento->tipoAudiencia;
	$arrValores["noGestionJudicial"]=$datosCarpeta[0];
	$arrValores["leyendaAnioAudiencia"]=convertirNumeroLetra($arrValores["anioAudiencia"],false,false);
	$arrValores["noJuezTramite"]=$datosJuez["noJuez"];
	$arrValores["nombreJuezTramite"]=$datosJuez["nombreJuez"];
	$arrValores["noOficio"]=generarNumeroOficioCarpetaAdministrativa($fDatosCitatorio[0],$idFormulario,$idRegistro);
	$arrValores["domicilioSala"]=obtenerDomicilioSala($datosEvento->idSala);
	$arrValores["noJuezAudiencia"]=$datosJuezAudiencia["noJuez"];
	$arrValores["nombreJuezAudiencia"]=$datosJuezAudiencia["nombreJuez"];
	$arrValores["nombreDirectorUGJ"]=$nombreDirectorUGJ;
	$arrValores["noGestionJudicial"]=strtoupper(convertirNumeroLetra($datosCarpeta[0]*1,false,false));
	$consulta="SELECT CONCAT(nombreTitular,' ',apPaterno,' ',apMaterno) FROM _149_tablaDinamica WHERE id__149_tablaDinamica=3";
	$titular=$con->obtenerValor($consulta);
	
	$arrValores["nombreDirectorMedidasCautelares"]=$titular;
	
	$medidas="";
	$nMedias=1;
	$consulta="SELECT medidaCautelar,m.tipoMedidaCautelar FROM _152_tablaDinamica r,_110_tablaDinamica m WHERE r.idReferencia=".$idRegistro." 
				AND m.id__110_tablaDinamica=r.medidaCautelar ORDER BY m.tipoMedidaCautelar";
	
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		if($medidas=="")
			$medidas=$nMedias.".- ".$fila[1];
		else
			$medidas.="<br><br>".$nMedias.".- ".$fila[1];
		
		$nMedias++;
	}
	$arrValores["tipoMedidaCautelar"]=$medidas;
	
	$fechaHabil=strtotime(obtenerProximoDiaHabil(date("Y-m-d",strtotime("+1 days",$fechaActual))));
	
	
	$arrValores["dia"]=date("d",$fechaHabil);
	$arrValores["mes"]= $arrMesLetra[(date("m",$fechaHabil)*1)-1];
	$arrValores["anio"]=date("Y",$fechaHabil);
	$arrValores["leyendaAnio"]=convertirNumeroLetra($arrValores["anio"],false,false);
	return $arrValores;
}

function llenarFormatoEmisionExhortoV2($idDocumento,$idReferencia,$idRegistro,$idFormulario)
{
	global $con;
	$consulta="SELECT carpetaAdministrativa,autoridadExhortada FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$fDatosCitatorio=$con->obtenerPrimeraFila($consulta);

	$arrValores=array();
	$arrValores["carpeta"]=$fDatosCitatorio[0];
	$arrValores["juzgado"]=$fDatosCitatorio[1];
	return $arrValores;
	
}


function llenarFormato_214($idActa)
{
	global $con;
	
	$arrValores=array();
	
	$cacheCalculos=NULL;
	$consulta="SELECT * FROM 7028_actaNotificacion WHERE idRegistro=".$idActa;
	$fActa=$con->obtenerPrimeraFilaAsoc($consulta);

	$consulta="SELECT idActividad FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$fActa["carpetaAdministrativa"]."'";
	$idActividad=$con->obtenerValor($consulta);
	$consulta="SELECT CONCAT(nombre,' ',apellidoPaterno,' ',apellidoMaterno) FROM _47_tablaDinamica d,7005_relacionFigurasJuridicasSolicitud r
			WHERE r.idActividad=".$idActividad." AND r.idParticipante=d.id__47_tablaDinamica AND r.idFiguraJuridica=4 
			ORDER BY nombre,apellidoPaterno,apellidoMaterno";
	$sentenciados=$con->obtenerListaValores($consulta);		
	
	$consulta="SELECT d.denominacionDelito FROM _35_denominacionDelito d,_61_tablaDinamica di 
				WHERE di.idActividad=".$idActividad." AND di.denominacionDelito=d.id__35_denominacionDelito ORDER BY d.denominacionDelito";
	$delitos=	$con->obtenerListaValores($consulta);
	
	$leyendaActa="";
	if($fActa["tipoActa"]==1)
	{
		$leyendaActa=$fActa["nombreDeterminacion"];
	}
	else
	{
		$consulta="SELECT  a.tipoAudiencia FROM 7000_eventosAudiencia e,_4_tablaDinamica a WHERE 
				idRegistroEvento=".$fActa["idEventoAudiencia"]." AND a.id__4_tablaDinamica=e.tipoAudiencia";
		$tAudiencia=$con->obtenerValor($consulta);
		$tAudiencia=trim(str_replace("Audiencia","",$tAudiencia));
		$leyendaActa="Auto de programación de Audiencia: ".$tAudiencia;
	}
	
	$tituloDocumento="";
	$filasDiligencias="";
	$consulta="SELECT COUNT(*) FROM 7029_diligenciaActaNotificacion WHERE idActaCircunstanciada=".$fActa["idRegistro"]." AND tipoDiligencia=0";
	$nReg=$con->obtenerValor($consulta);
	if($nReg>0)
	{
		$tituloDocumento="NOTIFICACIONES Y CITACIONES";
	}
	else
	{
		$consulta="SELECT COUNT(*) FROM 7029_diligenciaActaNotificacion WHERE idActaCircunstanciada=".$fActa["idRegistro"]." AND tipoDiligencia=1";
		$nReg=$con->obtenerValor($consulta);
		if($nReg>0)
		{
			$tituloDocumento="NOTIFICACIONES";
		}
		
		$consulta="SELECT COUNT(*) FROM 7029_diligenciaActaNotificacion WHERE idActaCircunstanciada=".$fActa["idRegistro"]." AND tipoDiligencia=2";
		$nReg=$con->obtenerValor($consulta);
		if($nReg>0)
		{
			if($tituloDocumento=="")
				$tituloDocumento="CITACIONES";
			else
				$tituloDocumento.=" Y CITACIONES";
		}
	}
	
	
	$consulta="SELECT * from  7029_diligenciaActaNotificacion WHERE idActaCircunstanciada=".$fActa["idRegistro"];
	$res=$con->obtenerFilas($consulta);
	while($fFila=mysql_fetch_assoc($res))
	{
		$consulta="SELECT parteProcesal FROM _414_tablaDinamica WHERE id__414_tablaDinamica=".$fFila["idParteProcesal"];
		$parteProcesal=$con->obtenerValor($consulta);
		if($fFila["idDetalleParteProcesal"]!="")
		{
			$consulta="SELECT etEspecificacion FROM _414_gEspecificacion WHERE idReferencia=".$fFila["idParteProcesal"]." AND iEspecificacion=".$fFila["idDetalleParteProcesal"];
			$etEspecificacion=$con->obtenerValor($consulta);
			$parteProcesal.=" ".$etEspecificacion;
		}
		$fundamento="";
		
		$arrFundamentos=array();
		$arrFundamentosLeyes=array();
		$nombreParte=$fFila["nombreParte"];
		if(($fFila["idNombreParteProcesal"]!="")&&($fFila["idNombreParteProcesal"]!="0"))
		{
			$consulta="SELECT UPPER(CONCAT(nombre,' ',apellidoPaterno,' ',apellidoMaterno)) FROM _47_tablaDinamica WHERE id__47_tablaDinamica=".$fFila["idNombreParteProcesal"];
			$nombreParte=$con->obtenerValor($consulta);
		}	
		
		
		$medioCitacion="";
		switch($fFila["tipoDiligencia"])
		{
			case 0:
				$medioCitacion=mb_strtoupper($fFila["otroTipoDiligencia"]);
			break;
			case 1:
				$medioCitacion="NOTIFICACIÓN";
			break;
			case 2:
				$medioCitacion="CITACIÓN";
			break;
			
		}
		
		$consulta="SELECT * FROM 7030_medioNotificacionDiligencia WHERE idDiligencia=".$fFila["idRegistro"];
		$rMedio=$con->obtenerFilas($consulta);
		$nMedios=$con->filasAfectadas;
		$pMedios=1;
		$listaMedios="";
		$arrListaMedios=array();
		while($fMedio=mysql_fetch_assoc($rMedio))
		{
			$llaveMedio=$fMedio["idMedio"];
			
			$consulta="SELECT prefijo,medioNotificacion FROM  _415_tablaDinamica WHERE id__415_tablaDinamica=".$fMedio["idMedio"];
			$fAux=$con->obtenerPrimeraFilaAsoc($consulta);
			
			$medio=$fAux["prefijo"]." ".$fAux["medioNotificacion"];
			
			if($fMedio["detalle1"]!="")
			{
				$consulta="SELECT prefijo,descripcion FROM  _415_gEspecificaciones WHERE idReferencia=".$fMedio["idMedio"]." and idDetalle=".$fMedio["detalle1"];
				$fAux2=$con->obtenerPrimeraFilaAsoc($consulta);
				$medio=trim($medio)." ".trim($fAux2["prefijo"]." ".$fAux2["descripcion"]);
				$llaveMedio.="_".$fMedio["detalle1"];
				
			}
			
			/*if($fMedio["detalle2"]!="")
			{
				$llaveMedio.="_".$fMedio["detalle2"];
			}*/
			
			
			
			if($fMedio["detalle3"]!="")
			{
				$medio=trim($medio).", ".strtoupper(trim($fAux2["detalle3"]));
				
			}
			else
			{
				if($fMedio["detalle2"]!="")
				{
					$consulta="SELECT prefijo,medioNotificacion FROM  _415_tablaDinamica WHERE id__415_tablaDinamica=".$fMedio["detalle2"];
	
					$fAux3=$con->obtenerPrimeraFilaAsoc($consulta);
					$medio=trim($medio).", ".trim($fAux3["medioNotificacion"]);
					
				}
			}
			
			$medio=trim($medio);
			
			if($pMedios==1)
			{
				$medioCitacion.=" ".$medio;
			}
			else
			{
				if($nMedios==$pMedios)
				{
					$medioCitacion.=" Y ".$medio;
				}
				else
					$medioCitacion.=", ".$medio;
			}
			
			$pMedios++;
			
			if($listaMedios=="")
				$listaMedios="'".$llaveMedio."'";
			else
				$listaMedios.=",'".$llaveMedio."'";
			
			$arrListaMedios[$llaveMedio]=$fMedio["idRegistro"];
			
			
		}
		
		
		$consulta="SELECT *,l.nombreLey,l.prefijo FROM 7031_fundamentoLegalMedioNotificacion f,_422_tablaDinamica l WHERE llaveMedioNotificacion in (".$listaMedios.
						") and l.id__422_tablaDinamica=f.idLey order by l.nombreLey,articulo,fraccion,inciso,complementario";

			
		$rMedioFundamento=$con->obtenerFilas($consulta);
		while($fMedioFundameto=mysql_fetch_assoc($rMedioFundamento))
		{
			if($fMedioFundameto["articulo"]=="")
				$fMedioFundameto["articulo"]="-";
			
			if($fMedioFundameto["fraccion"]=="")
				$fMedioFundameto["fraccion"]="-";
			
			if($fMedioFundameto["inciso"]=="")
				$fMedioFundameto["inciso"]="-";
			if($fMedioFundameto["complementario"]=="")
				$fMedioFundameto["complementario"]="-";	
			
			$considerar=true;
			if($fMedioFundameto["idFuncionAplicacion"]!="")
			{
				$cadParametros='{"idDiligencia":"'.$fFila["idRegistro"].'","idRegistroMedio":"'.$arrListaMedios[$fMedioFundameto["llaveMedioNotificacion"]].'"}';
				$objParametros=json_decode($cadParametros);
				$resultadoEvaluacion=removerComillasLimite(resolverExpresionCalculoPHP($fMedioFundameto["idFuncionAplicacion"],$objParametros,$cacheCalculos));
				if($resultadoEvaluacion==0)
				{
					$considerar=false;
				}	
			}
			if($considerar)
			{
				$llaveFundamento=$fMedioFundameto["idLey"]."_".$fMedioFundameto["articulo"]."_".$fMedioFundameto["fraccion"]."_".$fMedioFundameto["inciso"]."_".$fMedioFundameto["complementario"];
				$arrFundamentos[$llaveFundamento]=0;
				$arrFundamentosLeyes[$fMedioFundameto["idLey"]]=trim($fMedioFundameto["prefijo"]." ".$fMedioFundameto["nombreLey"]);
			}
				
			
		}
		
		$nTokens=1;
		$fundamento="";
		
		//ksort($arrFundamentos);
	
		foreach($arrFundamentosLeyes as $idLey=>$nombreLey)
		{
			$tokenLey="";
			
			$token="";
			foreach($arrFundamentos as $f=>$leyFundamento)
			{
				
				$aFundamento=explode("_",$f);
				if($idLey==$aFundamento[0])
				{
					$token=$aFundamento[1];
					if($aFundamento[2]!="-")
					{
						$token.=" fracción ".$aFundamento[2];
					}
					
					if($aFundamento[3]!="-")
					{
						$token.="-_- inciso ".$aFundamento[3];
					}
					
					if($aFundamento[4]!="-")
					{
						$token.=" ".$aFundamento[4];
					}
					
					if($tokenLey=="")
						$tokenLey=$token;
					else
						$tokenLey.=", ".$token;
				}
			}
			
			
			$arrTokens=explode(",",$tokenLey);
			$tokenLey="";
			$nTokens=1;
			foreach($arrTokens as $t)
			{
				$t=trim($t);
				if($tokenLey=="")
					$tokenLey=$t;
				else
				{
					if($nTokens==sizeof($arrTokens))
						$tokenLey.=" y ".$t;
					else
						$tokenLey.=", ".$t;
				}
				$nTokens++;
			}
			
			$tokenLey=str_replace("-_-",",",$tokenLey);
			$tokenLey.=" ".$nombreLey;
			
			if($fundamento=="")
				$fundamento=$tokenLey;
			else
				$fundamento.=", así como ".$tokenLey;
		}
		
		
		
		
		if(sizeof($arrFundamentos)==1)
			$fundamento="Artículo: ".$fundamento;
		else
			$fundamento="Artículos: ".$fundamento;
		$oFila='<tr valign="top">
				<td height="111" style="border: 1px solid #dbdbdb; padding-top: 0cm; padding-bottom: 0cm; padding-left: 0.2cm; padding-right: 0.19cm" width="160">
				<p align="center" class="western" style="margin-bottom: 0cm"><strong><font face="Verdana, serif" size="1">'.$parteProcesal.'</font></strong></p>
		
				<p align="center" class="western" style="margin-bottom: 0cm"><strong><font face="Verdana, serif" size="1">'.$nombreParte.'</font></strong></p>
				</td>
				<td style="border: 1px solid #dbdbdb; padding-top: 0cm; padding-bottom: 0cm; padding-left: 0.2cm; padding-right: 0.19cm" width="272">
				<p align="justify" class="western" style="margin-bottom: 0cm"><strong><font face="Verdana, serif"><span style="font-size: 10.6667px;">'.$medioCitacion.'</span></font></strong></p>
		
				<p align="justify" class="western" style="margin-bottom: 0cm"><font face="Verdana, serif"><font size="1" style="font-size: 8pt">'.$fFila["exposicionDiligencia"].'.</font></font></p>
		
				<p align="justify" class="western">&nbsp;</p>
				</td>
				<td style="border: 1px solid #dbdbdb; padding-top: 0cm; padding-bottom: 0cm; padding-left: 0.2cm; padding-right: 0.19cm" width="127">
				<p align="justify" class="western"><font face="Verdana, serif"><font size="1"></font><font size="1" style="font-size: 8pt">'.$fundamento.'</font><font size="1"></font></font></p>
				</td>
			</tr>';
	
		$filasDiligencias.=$oFila;
	}
	$tblTablaDiligencias=	'
								<table cellpadding="7" cellspacing="0" width="604">
								<colgroup>
									<col width="160" />
									<col width="272" />
									<col width="127" />
								</colgroup>
								<tbody>
									<tr valign="top">
										<td bgcolor="#e7e6e6" height="12" style="border-top: 1px solid #dbdbdb; border-bottom: 1.50pt solid #c9c9c9; border-left: 1px solid #dbdbdb; border-right: 1px solid #dbdbdb; padding-top: 0cm; padding-bottom: 0cm; padding-left: 0.2cm; padding-right: 0.19cm" width="160">
										<p align="center" class="western"><b><font face="Verdana, serif"><font size="1" style="font-size: 7pt">DIRIGIDO A:</font></font></b></p>
										</td>
										<td bgcolor="#e7e6e6" style="border-top: 1px solid #dbdbdb; border-bottom: 1.50pt solid #c9c9c9; border-left: 1px solid #dbdbdb; border-right: 1px solid #dbdbdb; padding-top: 0cm; padding-bottom: 0cm; padding-left: 0.2cm; padding-right: 0.19cm" width="272">
										<p align="center" class="western"><b><font face="Verdana, serif"><font size="1" style="font-size: 7pt">RESULTADO DE LA DILIGENCIA</font></font></b></p>
										</td>
										<td bgcolor="#e7e6e6" style="border-top: 1px solid #dbdbdb; border-bottom: 1.50pt solid #c9c9c9; border-left: 1px solid #dbdbdb; border-right: 1px solid #dbdbdb; padding-top: 0cm; padding-bottom: 0cm; padding-left: 0.2cm; padding-right: 0.19cm" width="127">
										<p align="center" class="western"><b><font face="Verdana, serif"><font size="1" style="font-size: 7pt">FUNDAMENTOS </font></font></b></p>
										</td>
									</tr>'.$filasDiligencias.'		
								</tbody>
							</table>
							';
	
	
	$arrValores["carpetaEjecucion"]=$fActa["carpetaAdministrativa"];
	$arrValores["sentenciado"]=$sentenciados;
	$arrValores["delito"]=$delitos;
	$arrValores["leyendaActa"]=$tituloDocumento;
	$arrValores["nombreDeterminacion"]=$leyendaActa;
	$arrValores["fecha"]=convertirFechaToLetra($fActa["fechaDeterminacion"],false,false);
	$arrValores["tabla"]=$tblTablaDiligencias;
	$arrValores["nombreResponsable"]=obtenerNombreUsuario($fActa["idResponsableRegistro"]);
	return $arrValores;
	
}


function esCitacionPublico($idDiligencia)
{
	global $con;
	$consulta="SELECT tipoDiligencia,idParteProcesal,idDetalleParteProcesal FROM 7029_diligenciaActaNotificacion WHERE idRegistro=".$idDiligencia;
	$fRegistro=$con->obtenerPrimeraFila($consulta);
	if(($fRegistro[0]==1)||($fRegistro[2]==2))
		return 0;
	return 1;	
}

function esCitacionPrivado($idDiligencia)
{
	global $con;
	
	$consulta="SELECT tipoDiligencia FROM 7029_diligenciaActaNotificacion WHERE idRegistro=".$idDiligencia;
	$fRegistro=$con->obtenerPrimeraFila($consulta);
	if($fRegistro[0]==1)
		return 0;	
	return esCitacionPublico($idDiligencia)==0?1:0;
	
	
}

function esNotificacionPublico($idDiligencia)
{
	global $con;
	$consulta="SELECT tipoDiligencia,idParteProcesal,idDetalleParteProcesal FROM 7029_diligenciaActaNotificacion WHERE idRegistro=".$idDiligencia;
	$fRegistro=$con->obtenerPrimeraFila($consulta);
	if(($fRegistro[0]==2)||($fRegistro[0]==3)||($fRegistro[2]==2))
		return 0;
	return 1;	
	
	
	
}

function esNotificacionPrivado($idDiligencia)
{
	global $con;
	$consulta="SELECT tipoDiligencia FROM 7029_diligenciaActaNotificacion WHERE idRegistro=".$idDiligencia;
	$fRegistro=$con->obtenerPrimeraFila($consulta);
	if($fRegistro[0]==2)
		return 0;
	return esNotificacionPublico($idDiligencia)==0?1:0;
	
	
}

?>