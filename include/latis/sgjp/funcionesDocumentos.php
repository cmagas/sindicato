<?php 
include_once("latis/conexionBD.php");
include_once("latis/utiles.php");
include_once("latis/numeroToLetra.php");
include_once("latis/PDFMerger.php");

function funcionLlenadoPrueba()
{
	global $arrMesLetra;
	global $leyendaTribunal;
	$arrValores=array();
	$arrValores["leyendaTribunal"]=$leyendaTribunal;
	
	
	return $arrValores;
}

function generarDocumentoPDFFormato($idRegistroFormato,$descomponerDocumentoMarcadores=true,$bloquearDocumento=0,$conversorPDF=1,$documentosAnexos="")
{
	global $con;
	global $baseDir;
	global $comandoLibreOffice;
	$arrExtensionesImagen["jpg"]=1;
	$arrExtensionesImagen["jpeg"]=1;
	$arrExtensionesImagen["png"]=1;
	$arrExtensionesImagen["gif"]=1;

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
	if($conversorPDF==-1)
	{
		$consulta="SELECT metodoConversionPDF FROM _10_tablaDinamica WHERE id__10_tablaDinamica=".$fDocumento[3];
		$conversorPDF=$con->obtenerValor($consulta);
		if($conversorPDF=="")
			$conversorPDF=1;
	}
	if($conversorPDF==2)
	{
		$cuerpoFormato=prepararFormatoImpresionWord($cuerpoFormato);
		
	}

	if(escribirContenidoArchivo($archivoTemporal,$cuerpoFormato))
	{
		$directorioDestino=$baseDir."/archivosTemporales/";
		
		if($conversorPDF==-1)
		{
			$consulta="SELECT metodoConversionPDF FROM _10_tablaDinamica WHERE id__10_tablaDinamica=".$fDocumento[3];
			$conversorPDF=$con->obtenerValor($consulta);
			if($conversorPDF=="")
				$conversorPDF=1;
		}
		

		
		switch($conversorPDF)
		{
			case 2:
				generarDocumentoPDF($archivoTemporal,false,false,false,"","MS_OFFICE",$directorioDestino);
			break;
			default:
				generarDocumentoPDF($archivoTemporal,false,false,false,"",$comandoLibreOffice,$directorioDestino);
			break;
		}
		
		
		if(file_exists($directorioDestino."/".$nombreArchivo.".pdf"))
		{
			
			if($documentosAnexos!="")
			{
				$arrRutasEliminar=array();
				$merge = new PDFMerger();
				$merge->addPDF($directorioDestino."/".$nombreArchivo.".pdf");
				$arrDocumentosAnexos=explode(",",$documentosAnexos);
				foreach($arrDocumentosAnexos as $d)
				{
					$consulta="SELECT LOWER(nomArchivoOriginal) FROM 908_archivos WHERE idArchivo=".$d;
					$nArchivo=$con->obtenerValor($consulta);
					
					$aDocumentoAnexo=explode(".",$nArchivo);
					$rutaDocumentoAnexo=obtenerRutaDocumento($d);
					if($aDocumentoAnexo[sizeof($aDocumentoAnexo)-1]=='pdf')
					{
						$merge->addPDF($rutaDocumentoAnexo);
						
					}
					else
					{
						if(isset($arrExtensionesImagen[$aDocumentoAnexo[sizeof($aDocumentoAnexo)-1]]))
						{
							$rutaDocumentoAnexoTmp=$directorioDestino."/".rand()."_".date("dmY_Hms");
							
							$rutaImagen=$rutaDocumentoAnexoTmp.".".$aDocumentoAnexo[sizeof($aDocumentoAnexo)-1];
							$rutaPdf=$rutaDocumentoAnexoTmp.".pdf";
							
							copy($rutaDocumentoAnexo,$rutaImagen);
							array_push($arrRutasEliminar,$rutaImagen);
							array_push($arrRutasEliminar,$rutaPdf);
							$arrDatosImagen=getimagesize($rutaImagen);
							$pdf = new FPDF();
							$pdf->AddPage();							
							$pdf->image($rutaImagen,0,0);							
							$pdf->Output($rutaPdf,'F');
							$merge->addPDF($rutaPdf);
						}
					}
					
					
					
					
				}
				

				$merge->merge("file",$directorioDestino."/".$nombreArchivo.".pdf");
				foreach($arrRutasEliminar as $d)
				{
					if(file_exists($d))
					{
						unlink($d);
					}
				}
			}
			
			rename($directorioDestino."/".$nombreArchivo.".pdf",$directorioDestino."/".$nombreArchivo);
			
			$actualizarFormato=true;
			if($descomponerDocumentoMarcadores)
			{
				$actualizarFormato=descomponerDocumentoMarcadores($idRegistroFormato,$cuerpoFormato);
			}
			$consulta="SELECT nombreFormato,categoriaDocumento FROM _10_tablaDinamica WHERE id__10_tablaDinamica=".$fDocumento[3];
			$fDocumentoFinal=$con->obtenerPrimeraFila($consulta);
			if(!$fDocumentoFinal)
			{
				$fDocumentoFinal[0]="Documento General";
				$fDocumentoFinal[1]=0;
			}
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

function prepararFormatoImpresionWord($cuerpoFormato)
{
	$cuerpoFormato=	'<html xmlns:v="urn:schemas-microsoft-com:vml"
						xmlns:o="urn:schemas-microsoft-com:office:office"
						xmlns:w="urn:schemas-microsoft-com:office:word"
						xmlns:m="http://schemas.microsoft.com/office/2004/12/omml"
						xmlns="http://www.w3.org/TR/REC-html40">'.$cuerpoFormato;
	$cuerpoFormato=preg_replace('/[^(\x20-\x7F)\x0A\x0D]*/','', $cuerpoFormato);
	$arrTagSistema=array();
	$arrTagSistema["horageneracion"]=date("d/m/Y H:i:s");
	
	foreach($arrTagSistema as $tag=>$valor)
	{
		$cuerpoFormato=str_replace("<".$tag."></".$tag.">",$valor,$cuerpoFormato);
	}
	return $cuerpoFormato;						
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

function registrarDocumentoCarpetaAdministrativa($carpetaAdministrativa,$idDocumento,$idFormulario=-1,$idRegistro=-1,$idCarpetaAdministrativa=-1)
{
	global $con;
	global $registrarIDCarpeta;
	
	$iFormulario=$idFormulario;
	$iRegistro=$idRegistro;
	
	if($iFormulario==-2)
	{
		$consulta="SELECT idFormulario,idReferencia,tipoDocumento,tituloDocumento FROM 7035_informacionDocumentos WHERE idRegistro=".$iRegistro;
		$fDatosInformacion=$con->obtenerPrimeraFila($consulta);
		$iFormulario=$fDatosInformacion[0];
		$iRegistro=$fDatosInformacion[1];
	}
	
	if($registrarIDCarpeta)
	{
		if(($idCarpetaAdministrativa==-1)&&($idFormulario!=-1))
		{
			if($idFormulario>0)
			{
				$nomTabla="_".$idFormulario."_tablaDinamica";
				if($con->existeCampo("idExpediente",$nomTabla))
				{
					$consulta="SELECT idExpediente FROM ".$nomTabla." WHERE id_".$nomTabla."=".$idRegistro." AND idExpediente NOT IN('','N/E')";
					$idCarpetaAdministrativa=$con->obtenerValor($consulta);
				}
				else
				{
					if($con->existeCampo("idCarpeta",$nomTabla))
					{
						$consulta="SELECT idCarpeta FROM ".$nomTabla." WHERE id_".$nomTabla."=".$idRegistro." AND idCarpeta NOT IN('','N/E')";
						$idCarpetaAdministrativa=$con->obtenerValor($consulta);
					}
					else
					{
						$consulta="SELECT codigoInstitucion FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
						$unidadAdministrativa=$con->obtenerValor($consulta);
						
						$consulta="SELECT idCarpeta FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$carpetaAdministrativa.
									"' AND unidadGestion='".$unidadAdministrativa."'";
						$idCarpetaAdministrativa=$con->obtenerValor($consulta);
						
					}
					
				}
				
			}
			else
			{
				$consulta="SELECT idFormulario,idReferencia,tipoDocumento,tituloDocumento FROM 7035_informacionDocumentos WHERE idRegistro=".$idRegistro;
				$fDatosInformacion=$con->obtenerPrimeraFila($consulta);
				$consulta="SELECT codigoInstitucion FROM _".$fDatosInformacion[0]."_tablaDinamica WHERE id__".$fDatosInformacion[0]."_tablaDinamica=".$fDatosInformacion[1];
				$unidadAdministrativa=$con->obtenerValor($consulta);
				$consulta="SELECT idCarpeta FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$carpetaAdministrativa.
									"' AND unidadGestion='".$unidadAdministrativa."'";
				$idCarpetaAdministrativa=$con->obtenerValor($consulta);
			}
			if($idCarpetaAdministrativa=="")
				$idCarpetaAdministrativa=-1;
				
		}
	}
	else
		$idCarpetaAdministrativa=-1;

	

	$consulta="select count(*) from 7007_contenidosCarpetaAdministrativa where carpetaAdministrativa='".$carpetaAdministrativa.
				"' and idRegistroContenidoReferencia=".$idDocumento." and tipoContenido=1 and idCarpetaAdministrativa=".$idCarpetaAdministrativa;

	$nReg=$con->obtenerValor($consulta);
	if($nReg>0)
		return true;
	
	$consulta="SELECT sha512 FROM 908_archivos WHERE idArchivo=".$idDocumento;
	$sha512=$con->obtenerValor($consulta);
	
	if($sha512!="")
	{
		$consulta="SELECT COUNT(*) FROM 7007_contenidosCarpetaAdministrativa con,908_archivos a
					WHERE con.carpetaAdministrativa='".$carpetaAdministrativa.
					"' and idCarpetaAdministrativa=".$idCarpetaAdministrativa.
					" AND  con.tipoContenido=1 AND a.idArchivo=con.idRegistroContenidoReferencia 
					 AND a.sha512='".$sha512."'";
		$nReg=$con->obtenerValor($consulta);
		if($nReg>0)
			return true;
	}
	
	
	$etapaProcesal=obtenerEtapaProcesalCarpetaAdministrativa($carpetaAdministrativa,$idCarpetaAdministrativa);
	if($etapaProcesal=="")
		$etapaProcesal=1;
	$consulta="INSERT INTO 7007_contenidosCarpetaAdministrativa(carpetaAdministrativa,fechaRegistro,responsableRegistro,tipoContenido,descripcionContenido,
				idRegistroContenidoReferencia,idFormulario,idRegistro,etapaProcesal,idCarpetaAdministrativa)
				VALUES('".$carpetaAdministrativa."','".date("Y-m-d H:i:s")."',".$_SESSION["idUsr"].",1,'',".$idDocumento.",".$iFormulario.",".$iRegistro.
				",".$etapaProcesal.",".$idCarpetaAdministrativa.")";

	if($con->ejecutarConsulta($consulta))
	{
		subirDocumentoCSDOCS($idDocumento,$idCarpetaAdministrativa);
		return true;
	}
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
	
	$idCarpetaAdministrativa=-1;
	$nombreTablaBase="_".$idFormulario."_tablaDinamica";
	$campoLlave="id_".$nombreTablaBase;
	
	$carpetaAdministrativa=obtenerCarpetaAdministrativaProceso($idFormulario,$idRegistro);
	if($con->existeCampo("idCarpetaAdministrativa",$nombreTablaBase))
	{
		$query="select idCarpetaAdministrativa from ".$nombreTablaBase." where ".$campoLlave."=".$idRegistro;
		$idCarpetaAdministrativa=$con->obtenerValor($query);
		
		
	}
	
	$etapaProcesal=obtenerEtapaProcesalCarpetaAdministrativa($carpetaAdministrativa,$idCarpetaAdministrativa);
	if($carpetaAdministrativa=="")
	{
		return true;
	}
	$consulta="select count(*) from 7007_contenidosCarpetaAdministrativa where carpetaAdministrativa='".$carpetaAdministrativa."' and idRegistroContenidoReferencia=".$idEventoAudiencia.
			" and tipoContenido=3";


	if($idCarpetaAdministrativa!=-1)
	{
		$consulta.=" and idCarpetaAdministrativa=".$idCarpetaAdministrativa;
	}
			
			

	$nReg=$con->obtenerValor($consulta);
	if($nReg>0)
		return true;
	
	$consulta="INSERT INTO 7007_contenidosCarpetaAdministrativa(carpetaAdministrativa,fechaRegistro,responsableRegistro,tipoContenido,
				descripcionContenido,idRegistroContenidoReferencia,idFormulario,idRegistro,etapaProcesal,idCarpetaAdministrativa)
				VALUES('".$carpetaAdministrativa."','".date("Y-m-d H:i:s")."',".$_SESSION["idUsr"].",3,'',".$idEventoAudiencia.",".$idFormulario.
				",".$idRegistro.",".$etapaProcesal.",".$idCarpetaAdministrativa.")";
	
	return $con->ejecutarConsulta($consulta);
}

function registrarDocumentoResultadoProceso($idFormulario,$idRegistro,$idDocumento)
{
	global $con;	
	
	$iFormulario=$idFormulario;
	$iRegistro=$idRegistro;
	if($idFormulario==-2)
	{
		$consulta="SELECT idFormulario,idReferencia,tipoDocumento,tituloDocumento FROM 7035_informacionDocumentos WHERE idRegistro=".$idRegistro;
		$fDatosInformacion=$con->obtenerPrimeraFila($consulta);
		$iFormulario=$fDatosInformacion[0];
		$iRegistro=$fDatosInformacion[1];
	}
	
	$consulta="select count(*) from 9074_documentosRegistrosProceso where idFormulario=".$iFormulario." 
			and idRegistro=".$iRegistro." and idDocumento=".$idDocumento;

	$nReg=$con->obtenerValor($consulta);
	if($nReg>0)
		return true;
	
	$consulta="INSERT INTO 9074_documentosRegistrosProceso(idFormulario,idRegistro,idDocumento,tipoDocumento) 
			VALUES(".$iFormulario.",".$iRegistro.",".$idDocumento.",2)";
	if( $con->ejecutarConsulta($consulta))
	{
		$carpetaAdministrativa=obtenerCarpetaAdministrativaProceso($idFormulario,$idRegistro);
		if($carpetaAdministrativa=="")
			return true;
		registrarDocumentoCarpetaAdministrativa($carpetaAdministrativa,$idDocumento,$iFormulario,$iRegistro);
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
		
		$idCarpetaAdministrativa=-1;
		$nombreTablaBase="_".$idFormulario."_tablaDinamica";
		$campoLlave="id_".$nombreTablaBase;
		if($con->existeCampo("idCarpetaAdministrativa",$nombreTablaBase))
		{

			$query="select idCarpetaAdministrativa,codigoInstitucion from ".$nombreTablaBase." where ".$campoLlave."=".$idRegistro;

			$fRegistro=$con->obtenerPrimeraFila($query);
			$idCarpetaAdministrativa=$fRegistro[0];
			$unidad=$fRegistro[1];
			
			
			
		}	

		registrarDocumentoCarpetaAdministrativa($carpetaAdministrativa,$idDocumento,$idFormulario,$idRegistro,$idCarpetaAdministrativa);
		
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
	global $leyendaTribunal;
	$arrValores=array();
	
	$cacheCalculos=NULL;
	$consulta="SELECT * FROM 7028_actaNotificacion WHERE idRegistro=".$idActa;
	$fActa=$con->obtenerPrimeraFilaAsoc($consulta);

	$consulta="SELECT idActividad,tipoCarpetaAdministrativa,idFormulario,idRegistro,etapaProcesalActual FROM 7006_carpetasAdministrativas 
			WHERE carpetaAdministrativa='".$fActa["carpetaAdministrativa"]."'";
	$fDatosCarpeta=$con->obtenerPrimeraFila($consulta);
	$idActividad=$fDatosCarpeta[0];
	$etapaProcesalActual=$fDatosCarpeta[4];
	$tCarpeta="CARPETA JUDICIAL";
	$lblSentenciado="Imputado";
	

	$idActividadBase=-1;
	if($fDatosCarpeta[1]==1)
		$idActividadBase=$idActividad;
	else
	{
		$arrCarpetas=obtenerCarpetasAntecesoras($fActa["carpetaAdministrativa"]);
		$arrCarpetas=array_reverse ($arrCarpetas);
		foreach($arrCarpetas as $c)
		{
			$consulta="SELECT idActividad,tipoCarpetaAdministrativa,idFormulario,idRegistro,etapaProcesalActual FROM 7006_carpetasAdministrativas 
			WHERE carpetaAdministrativa='".$c."'";
			$fDatosCarpetaAntecesora=$con->obtenerPrimeraFila($consulta);
			if($fDatosCarpetaAntecesora[1]==1)
			{
				$idActividadBase=$fDatosCarpetaAntecesora[0];
				break;
			}
		}
		
		
	}
	
	$consultaDelitos="SELECT d.denominacionDelito FROM _35_denominacionDelito d,_61_tablaDinamica di 
				WHERE di.idActividad=".$idActividadBase." AND di.denominacionDelito=d.id__35_denominacionDelito ORDER BY d.denominacionDelito";
	
	
	
	switch($fDatosCarpeta[1])
	{
		case "1" : //Control
		break;
		case "2": //Exhorto
			
			$tCarpeta="CARPETA JUDICIAL DE EXHORTO";
			$consultaDelitos="SELECT UPPER(delitos) FROM (SELECT GROUP_CONCAT(IF(delito=0,otroDelito,
			(SELECT denominacionDelito FROM _35_denominacionDelito WHERE id__35_denominacionDelito=g.delito))) delitos
				FROM _92_gDelitos g WHERE idReferencia=".$fDatosCarpeta[3].") AS tmp ORDER BY delitos";
			
		break;
		case "3": //Amparo
			$tCarpeta="CUADERNILLO DE AMPARO";
			switch($etapaProcesalActual)
			{
				case 5:
					$lblSentenciado="Acusado";
				break;
				case 6:
					$lblSentenciado="Sentenciado";
				break;
			}
			
		break;
		case "4": //Apelación
			$tCarpeta="CARPETA DE APELACIÓN";

			switch($etapaProcesalActual)
			{
				case 5:
					$lblSentenciado="Acusado";
				break;
				case 6:
					$lblSentenciado="Sentenciado";
				break;
			}
		break;
		case "5":
			$lblSentenciado="Acusado";
			$tCarpeta="CARPETA DE JUICIO ORAL";
			
		break;
		case "6":
			$lblSentenciado="Sentenciado";
			$tCarpeta="CARPETA DE EJECUCIÓN";
		break;
		case "8":
			$tCarpeta="TOCA";
			switch($etapaProcesalActual)
			{
				case 5:
					$lblSentenciado="Acusado";
				break;
				case 6:
					$lblSentenciado="Sentenciado";
				break;
			}
		break;
		
	}
	
	$delitos=$con->obtenerListaValores($consultaDelitos);
	
	$consulta="SELECT upper(CONCAT(nombre,' ',apellidoPaterno,' ',apellidoMaterno)) FROM _47_tablaDinamica d,7005_relacionFigurasJuridicasSolicitud r
			WHERE r.idActividad=".$idActividad." AND r.idParticipante=d.id__47_tablaDinamica AND r.idFiguraJuridica=4 
			ORDER BY nombre,apellidoPaterno,apellidoMaterno";
			
	$sentenciados="";		
	$rImputados=$con->obtenerFilas($consulta);
	while($fImputado=mysql_fetch_row($rImputados))
	{
		if($sentenciados=="")
			$sentenciados=trim($fImputado[0]);
		else
			$sentenciados.=", ".trim($fImputado[0]);
	}
	
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
	$consulta="SELECT COUNT(*) FROM 7029_diligenciaActaNotificacion WHERE idActaCircunstanciada=".$fActa["idRegistro"]." AND tipoDiligencia in(0,3)";
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
	
	$consulta="SELECT * from  7029_diligenciaActaNotificacion WHERE idActaCircunstanciada=".$fActa["idRegistro"]." order by orden";
	$res=$con->obtenerFilas($consulta);
	while($fFila=mysql_fetch_assoc($res))
	{
		
		$consulta="SELECT parteProcesal FROM _414_tablaDinamica WHERE id__414_tablaDinamica=".$fFila["idParteProcesal"];
		$parteProcesal=$con->obtenerValor($consulta);
		
		$oFigura=determinarLeyendaFiguraJuridica($fFila["idParteProcesal"],-1,$fActa["carpetaAdministrativa"])	;
		$parteProcesal=$oFigura[1];
		if($fFila["idDetalleParteProcesal"]!="")
		{

			
			$consulta="SELECT etiquetaDetalle FROM _5_gDetallesTipo WHERE idReferencia=".$fFila["idParteProcesal"]." AND idDetalle=".$fFila["idDetalleParteProcesal"];
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
			case 3:
				$medioCitacion="NOTIFICACIÓN Y CITACIÓN";
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
			$consulta="SELECT upper(nombreElemento) FROM 1018_catalogoVarios WHERE tipoElemento=22 AND 
					claveElemento='".removerCerosDerecha($fMedio["resultadoNotificacion"])."'";
			
			$resultadoNotificacion=$con->obtenerValor($consulta);
			
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
			
			if($fMedio["detalle2"]!="")
			{
				$llaveMedio.="_".$fMedio["detalle2"];
			}
			
			
			
			if($fMedio["detalle3"]!="")
			{
				$medio=trim($medio).", ".strtoupper(trim($fAux2["detalle3"]));
				
			}
			else
			{
				if($fMedio["detalle2"]!="")
				{
					$consulta="SELECT prefijo,descripcion FROM  _415_gEspecificaciones WHERE idDetalle=".$fMedio["detalle2"];
	
					$fAux3=$con->obtenerPrimeraFilaAsoc($consulta);
					$medio=trim($medio).", ".trim($fAux3["descripcion"]);
					
				}
			}
			
			$medio=trim($medio);
			if($fMedio["resultadoNotificacion"]!=3)
			{
				$medio.=". ".$resultadoNotificacion.".";
			}
			
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
				<strong><font face="Verdana, serif" size="1">'.$parteProcesal.'</font></strong><br>
		
				<font face="Verdana, serif" size="1">'.$nombreParte.'</font></strong>
				</td>
				<td style="border: 1px solid #dbdbdb; padding-top: 0cm; padding-bottom: 0cm; padding-left: 0.2cm; padding-right: 0.19cm; text-align:justify;" width="272">
				<strong><font face="Verdana, serif"><span style="font-size: 10.6667px;">'.$medioCitacion.'</span></font></strong><br>
		
				<font face="Verdana, serif"><font size="1" style="font-size: 8pt">'.$fFila["exposicionDiligencia"].'.</font></font><br><br>
		
				
				</td>
				<td style="border: 1px solid #dbdbdb; padding-top: 0cm; padding-bottom: 0cm; padding-left: 0.2cm; padding-right: 0.19cm;text-align:justify;" width="127">
				<font face="Verdana, serif"><font size="1"></font><font size="1" style="font-size: 8pt">'.$fundamento.'</font><font size="1"></font></font>
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
	$arrValores["lblSentenciado"]=mb_strtoupper($lblSentenciado);
	$arrValores["tipoCarpeta"]=$tCarpeta;
	$arrValores["carpetaEjecucion"]=$fActa["carpetaAdministrativa"];
	$arrValores["sentenciado"]=$sentenciados;
	$arrValores["delito"]=$delitos;
	if($arrValores["delito"]=="")
	{
		$arrValores["delito"]="(NO ESPECIFICADO)";
	}
	$arrValores["leyendaActa"]=$tituloDocumento;
	$arrValores["nombreDeterminacion"]=$leyendaActa;
	$arrValores["fecha"]=utf8_encode(convertirFechaToLetra($fActa["fechaDeterminacion"],false,false));
	$arrValores["tabla"]=$tblTablaDiligencias;
	$arrValores["nombreResponsable"]=obtenerNombreUsuario($fActa["idResponsableRegistro"]);
	$arrValores["leyendaTribunal"]=$leyendaTribunal;
	
	$consulta="SELECT DISTINCT u.Nombre FROM 7029_diligenciaActaNotificacion d,800_usuarios u WHERE idActaCircunstanciada=".$idActa."
				AND u.idUsuario=d.idResponsableDiligencia ORDER BY u.Nombre";
	$rActores=$con->obtenerFilas($consulta);
	
	$tamanoMax=40;
	$filasFirmantes="";
	$fFila="";
	$nPos=1;
	while($fActor=mysql_fetch_row($rActores))
	{
		if($nPos==1)
			$fFila='<tr>';
		
		$diferencia=$tamanoMax-strlen($fActor[0]);
		$tamano=parteEntera($diferencia/2);
		$cadToken="";
		for($x=0;$x<$tamano;$x++)
		{
			$cadToken.="_";
		}
		
		
		$fFila.='<td><br><br><p align="center" class="MsoNormal" style="text-align:center"><u><span style="font-size:7.0pt;line-height:115%;font-family:&quot;Verdana&quot;,&quot;sans-serif&quot;">'.$cadToken.$fActor[0].$cadToken.'</span></u></p></td>';
		if($nPos==1)
			$nPos=2;
		else
		{
			$fFila.='</tr>';
			$nPos=1;
			$filasFirmantes.=$fFila;
		}
	
	}
	
	
	if($nPos==2)
	{
		if($con->filasAfectadas>1)
			$fFila.='<td><p align="center" class="MsoNormal" style="text-align:center"><u><span style="font-size:7.0pt;line-height:115%;font-family:&quot;Verdana&quot;,&quot;sans-serif&quot;"></span></u></p></td>';
		
		$filasFirmantes.=$fFila."</tr>";
	}
	
	
	
	$tblFirmas=	'<table width="100%""><tr><td align="center"><table cellpadding="7" cellspacing="0" width="604">
				<colgroup>'.($con->filasAfectadas>1?'<col width="260" />':'<col width="260" /><col width="260" />').'
					
				</colgroup>
				<tbody>
					'.$filasFirmantes.'		
				</tbody>
			</table>
			</td>
			</tr>
			</table>';
	
	
	
	$arrValores["tblFirmas"]=$tblFirmas;
	
	
	return $arrValores;
	
}


function esCitacionPublico($idDiligencia)
{
	global $con;
	$consulta="SELECT tipoDiligencia,idParteProcesal,idDetalleParteProcesal FROM 7029_diligenciaActaNotificacion WHERE idRegistro=".$idDiligencia;
	$fRegistro=$con->obtenerPrimeraFila($consulta);
	
	if($fRegistro[0]==2)
	{
		if($fRegistro[2]==1)
		{
			return 1;
		}
	}
	return 0;
	
}

function esCitacionPrivado($idDiligencia)
{
	global $con;
	$consulta="SELECT tipoDiligencia,idParteProcesal,idDetalleParteProcesal FROM 7029_diligenciaActaNotificacion WHERE idRegistro=".$idDiligencia;
	$fRegistro=$con->obtenerPrimeraFila($consulta);
	
	if($fRegistro[0]==2)
	{
		if($fRegistro[2]==2)
		{
			return 1;
		}
	}
	return 0;
	
}

function esNotificacionPublico($idDiligencia)
{
	global $con;
	$consulta="SELECT tipoDiligencia,idParteProcesal,idDetalleParteProcesal FROM 7029_diligenciaActaNotificacion WHERE idRegistro=".$idDiligencia;
	$fRegistro=$con->obtenerPrimeraFila($consulta);
	
	if($fRegistro[0]==1)
	{
		if($fRegistro[2]==1)
		{
			return 1;
		}
	}
	return 0;
	
}

function esNotificacionPrivado($idDiligencia)
{
	global $con;
	$consulta="SELECT tipoDiligencia,idParteProcesal,idDetalleParteProcesal FROM 7029_diligenciaActaNotificacion WHERE idRegistro=".$idDiligencia;
	$fRegistro=$con->obtenerPrimeraFila($consulta);
	
	if($fRegistro[0]==1)
	{
		if($fRegistro[2]==2)
		{
			return 1;
		}
	}
	return 0;
	
}

function esNotificacionCitacionPublico($idDiligencia)
{
	global $con;
	$consulta="SELECT tipoDiligencia,idParteProcesal,idDetalleParteProcesal FROM 7029_diligenciaActaNotificacion WHERE idRegistro=".$idDiligencia;
	$fRegistro=$con->obtenerPrimeraFila($consulta);
	
	if($fRegistro[0]==3)
	{
		if($fRegistro[2]==1)
		{
			return 1;
		}
	}
	return 0;
	
}

function esNotificacionCitacionPrivado($idDiligencia)
{
	global $con;
	$consulta="SELECT tipoDiligencia,idParteProcesal,idDetalleParteProcesal FROM 7029_diligenciaActaNotificacion WHERE idRegistro=".$idDiligencia;
	$fRegistro=$con->obtenerPrimeraFila($consulta);
	
	if($fRegistro[0]==3)
	{
		if($fRegistro[2]==2)
		{
			return 1;
		}
	}
	return 0;
	
}


//
function esCitacion($idDiligencia)
{
	global $con;
	$consulta="SELECT tipoDiligencia,idParteProcesal,idDetalleParteProcesal FROM 7029_diligenciaActaNotificacion WHERE idRegistro=".$idDiligencia;
	$fRegistro=$con->obtenerPrimeraFila($consulta);
	
	if($fRegistro[0]==2)
	{
		return true;
	}
	return 0;
	
}

function esNotificacion($idDiligencia)
{
	global $con;
	$consulta="SELECT tipoDiligencia,idParteProcesal,idDetalleParteProcesal FROM 7029_diligenciaActaNotificacion WHERE idRegistro=".$idDiligencia;
	$fRegistro=$con->obtenerPrimeraFila($consulta);
	
	if($fRegistro[0]==1)
	{
		return true;
	}
	return 0;
	
}

function esNotificacionYCitacion($idDiligencia)
{
	global $con;
	$consulta="SELECT tipoDiligencia,idParteProcesal,idDetalleParteProcesal FROM 7029_diligenciaActaNotificacion WHERE idRegistro=".$idDiligencia;
	$fRegistro=$con->obtenerPrimeraFila($consulta);
	
	if($fRegistro[0]==3)
	{
		return true;
	}
	return 0;
	
}

function esNotificacionEjecucion($idDiligencia)
{
	global $con;
	
	$consulta="SELECT tipoDiligencia,idParteProcesal,idDetalleParteProcesal,idActaCircunstanciada FROM 7029_diligenciaActaNotificacion WHERE idRegistro=".$idDiligencia;
	$fRegistro=$con->obtenerPrimeraFila($consulta);
	
	$consulta="SELECT carpetaAdministrativa FROM 7028_actaNotificacion WHERE idRegistro=".$fRegistro[3];
	$carpetaAdministrativa=$con->obtenerValor($consulta);
	
	$consulta="SELECT tipoCarpetaAdministrativa FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$carpetaAdministrativa."'";
	$tCarpetaAdministrativa=$con->obtenerValor($consulta);
	if($tCarpetaAdministrativa!=6)
		return 0;
	return 1;
	
}

function esNotificacionPrivada($idDiligencia)
{
	global $con;
	
	$consulta="SELECT tipoDiligencia,idParteProcesal,idDetalleParteProcesal,idActaCircunstanciada FROM 7029_diligenciaActaNotificacion WHERE idRegistro=".$idDiligencia;
	$fRegistro=$con->obtenerPrimeraFila($consulta);
	
	if($fRegistro[2]==2)
	{
		return 1;
	}
	return 0;
	
}

function esNotificacionYCitacionEjecucion($idDiligencia)
{
	global $con;
	$consulta="SELECT tipoDiligencia,idParteProcesal,idDetalleParteProcesal FROM 7029_diligenciaActaNotificacion WHERE idRegistro=".$idDiligencia;
	$fRegistro=$con->obtenerPrimeraFila($consulta);
	
	if($fRegistro[0]==3)
	{
		return esNotificacionEjecucion($idDiligencia)==1?1:0;

	}
	return 0;
	
}

function esCitacionEjecucion($idDiligencia)
{
	global $con;
	$consulta="SELECT tipoDiligencia,idParteProcesal,idDetalleParteProcesal FROM 7029_diligenciaActaNotificacion WHERE idRegistro=".$idDiligencia;
	$fRegistro=$con->obtenerPrimeraFila($consulta);
	
	if($fRegistro[0]==2)
	{
		return (esNotificacionEjecucion($idDiligencia)==1)?1:0;
	}
	return 0;
	
}

function esNotificacionNotificacionEjecucion($idDiligencia)
{
	global $con;
	$consulta="SELECT tipoDiligencia,idParteProcesal,idDetalleParteProcesal FROM 7029_diligenciaActaNotificacion WHERE idRegistro=".$idDiligencia;
	$fRegistro=$con->obtenerPrimeraFila($consulta);
	
	if($fRegistro[0]==1)
	{
		return (esNotificacionEjecucion($idDiligencia)==1)?1:0;
	}
	return 0;
	
}
//Ejecucion

function esCitacionPublicoEjecucion($idDiligencia)
{
	global $con;
	
	$consulta="SELECT tipoDiligencia,idParteProcesal,idDetalleParteProcesal,idActaCircunstanciada FROM 7029_diligenciaActaNotificacion WHERE idRegistro=".$idDiligencia;
	$fRegistro=$con->obtenerPrimeraFila($consulta);
	
	$consulta="SELECT carpetaAdministrativa FROM 7028_actaNotificacion WHERE idRegistro=".$fRegistro[3];
	$carpetaAdministrativa=$con->obtenerValor($consulta);
	
	$consulta="SELECT tipoCarpetaAdministrativa FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$carpetaAdministrativa."'";
	$tCarpetaAdministrativa=$con->obtenerValor($consulta);
	if($tCarpetaAdministrativa!=6)
		return 0;
	
	
	if($fRegistro[0]==2)
	{
		if($fRegistro[2]==1)
		{
			return 1;
		}
	}
	return 0;
	
}

function esCitacionPrivadoEjecucion($idDiligencia)
{
	global $con;
	
	$consulta="SELECT tipoDiligencia,idParteProcesal,idDetalleParteProcesal,idActaCircunstanciada FROM 7029_diligenciaActaNotificacion WHERE idRegistro=".$idDiligencia;
	$fRegistro=$con->obtenerPrimeraFila($consulta);
	
	$consulta="SELECT carpetaAdministrativa FROM 7028_actaNotificacion WHERE idRegistro=".$fRegistro[3];
	$carpetaAdministrativa=$con->obtenerValor($consulta);
	
	$consulta="SELECT tipoCarpetaAdministrativa FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$carpetaAdministrativa."'";
	$tCarpetaAdministrativa=$con->obtenerValor($consulta);
	if($tCarpetaAdministrativa!=6)
		return 0;
	
	if($fRegistro[0]==2)
	{
		if($fRegistro[2]==2)
		{
			return 1;
		}
	}
	return 0;
	
}

function esNotificacionPublicoEjecucion($idDiligencia)
{
	global $con;
	
	
	$consulta="SELECT tipoDiligencia,idParteProcesal,idDetalleParteProcesal,idActaCircunstanciada FROM 7029_diligenciaActaNotificacion WHERE idRegistro=".$idDiligencia;
	$fRegistro=$con->obtenerPrimeraFila($consulta);
	
	$consulta="SELECT carpetaAdministrativa FROM 7028_actaNotificacion WHERE idRegistro=".$fRegistro[3];
	$carpetaAdministrativa=$con->obtenerValor($consulta);
	
	$consulta="SELECT tipoCarpetaAdministrativa FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$carpetaAdministrativa."'";
	$tCarpetaAdministrativa=$con->obtenerValor($consulta);
	if($tCarpetaAdministrativa!=6)
		return 0;
	
	if($fRegistro[0]==1)
	{
		if($fRegistro[2]==1)
		{
			return 1;
		}
	}
	return 0;
	
}

function esNotificacionPrivadoEjecucion($idDiligencia)
{
	global $con;
	
	$consulta="SELECT tipoDiligencia,idParteProcesal,idDetalleParteProcesal,idActaCircunstanciada FROM 7029_diligenciaActaNotificacion WHERE idRegistro=".$idDiligencia;
	$fRegistro=$con->obtenerPrimeraFila($consulta);
	
	$consulta="SELECT carpetaAdministrativa FROM 7028_actaNotificacion WHERE idRegistro=".$fRegistro[3];
	$carpetaAdministrativa=$con->obtenerValor($consulta);
	
	$consulta="SELECT tipoCarpetaAdministrativa FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$carpetaAdministrativa."'";
	$tCarpetaAdministrativa=$con->obtenerValor($consulta);
	if($tCarpetaAdministrativa!=6)
		return 0;
	
	if($fRegistro[0]==1)
	{
		if($fRegistro[2]==2)
		{
			return 1;
		}
	}
	return 0;
	
}

function esNotificacionCitacionPublicoEjecucion($idDiligencia)
{
	global $con;
	
	$consulta="SELECT tipoDiligencia,idParteProcesal,idDetalleParteProcesal,idActaCircunstanciada FROM 7029_diligenciaActaNotificacion WHERE idRegistro=".$idDiligencia;
	$fRegistro=$con->obtenerPrimeraFila($consulta);
	
	$consulta="SELECT carpetaAdministrativa FROM 7028_actaNotificacion WHERE idRegistro=".$fRegistro[3];
	$carpetaAdministrativa=$con->obtenerValor($consulta);
	
	$consulta="SELECT tipoCarpetaAdministrativa FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$carpetaAdministrativa."'";
	$tCarpetaAdministrativa=$con->obtenerValor($consulta);
	if($tCarpetaAdministrativa!=6)
		return 0;
	
	if($fRegistro[0]==3)
	{
		if($fRegistro[2]==1)
		{
			return 1;
		}
	}
	return 0;
	
}

function esNotificacionCitacionPrivadoEjecucion($idDiligencia)
{
	global $con;
	
	$consulta="SELECT tipoDiligencia,idParteProcesal,idDetalleParteProcesal,idActaCircunstanciada FROM 7029_diligenciaActaNotificacion WHERE idRegistro=".$idDiligencia;
	$fRegistro=$con->obtenerPrimeraFila($consulta);
	
	$consulta="SELECT carpetaAdministrativa FROM 7028_actaNotificacion WHERE idRegistro=".$fRegistro[3];
	$carpetaAdministrativa=$con->obtenerValor($consulta);
	
	$consulta="SELECT tipoCarpetaAdministrativa FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$carpetaAdministrativa."'";
	$tCarpetaAdministrativa=$con->obtenerValor($consulta);
	if($tCarpetaAdministrativa!=6)
		return 0;
	
	if($fRegistro[0]==3)
	{
		if($fRegistro[2]==2)
		{
			return 1;
		}
	}
	return 0;
	
}




function llenarFormato_601($carpetaAdministrativa)
{	
	global $con;
	global $arrMesLetra;
	$fechactual=date("Y-m-d");
	$consulta="SELECT idActividad,fechaCreacion,unidadGestion,carpetaAdministrativaBase FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$carpetaAdministrativa."'";
	$fCarpeta=$con->obtenerPrimeraFila($consulta);
	$consulta="SELECT nombreDirector,nombreUnidad,id__17_tablaDinamica FROM _17_tablaDinamica WHERE claveUnidad='".$fCarpeta[2]."'";
	$fDatosCarpetaEjecucion=$con->obtenerPrimeraFila($consulta);
	
	$idActividad=$fCarpeta[0];
	$consulta="SELECT idActividad,fechaCreacion,unidadGestion,carpetaAdministrativaBase FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$fCarpeta[3]."'";
	$fCarpetaBase=$con->obtenerPrimeraFila($consulta);
	
	$consulta="SELECT nombreDirector,nombreUnidad FROM _17_tablaDinamica WHERE claveUnidad='".$fCarpetaBase[2]."'";
	$fDatosCarpetaBase=$con->obtenerPrimeraFila($consulta);
	
	
	$consulta="SELECT GROUP_CONCAT(CONCAT(nombre,' ',apellidoPaterno,' ',apellidoMaterno)) FROM _47_tablaDinamica i,7005_relacionFigurasJuridicasSolicitud r 
				WHERE r.idParticipante=i.id__47_tablaDinamica AND r.idActividad=".$idActividad." AND idFiguraJuridica=4";
	$sentenciados=$con->obtenerValor($consulta);
	
	$consulta="SELECT GROUP_CONCAT(CONCAT(nombre,' ',apellidoPaterno,' ',apellidoMaterno)) FROM _47_tablaDinamica i,7005_relacionFigurasJuridicasSolicitud r 
				WHERE r.idParticipante=i.id__47_tablaDinamica AND r.idActividad=".$idActividad." AND idFiguraJuridica=2";
	
	
	$victimas=$con->obtenerValor($consulta);
	
	$consulta="SELECT * FROM _385_tablaDinamica WHERE carpetaEjecucion='".$carpetaAdministrativa."'";
	$fRegistroBaseSentencia=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$consulta="SELECT fechaEvento FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$fRegistroBaseSentencia["fechaAudiencia"];
	$fSentencia=$con->obtenerValor($consulta);
	
	$consulta="SELECT idJuez FROM 7001_eventoAudienciaJuez WHERE idRegistroEvento=".$fRegistroBaseSentencia["fechaAudiencia"];
	
	$idJuezAudiencia=$con->obtenerValor($consulta);
	
	$consulta="SELECT clave FROM _26_tablaDinamica WHERE usuarioJuez=".$idJuezAudiencia;
	$noJuez=$con->obtenerValor($consulta);
	
	$fAuto=$fRegistroBaseSentencia["fechaEjecutoria"];
		
	$consulta="SELECT * FROM _412_tablaDinamica WHERE idReferencia=".$fRegistroBaseSentencia["id__385_tablaDinamica"];
	$fDocumentacion=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$consulta="SELECT de.denominacionDelito FROM _61_tablaDinamica d,_35_denominacionDelito de 
				WHERE idActividad=".$idActividad." AND de.id__35_denominacionDelito=d.denominacionDelito";
	$delitos=$con->obtenerListaValores($consulta);
	
	$listaAudienciasDVD="";
	$consulta="SELECT idEventoAudiencia FROM _412_gridAudienciasRemitidas WHERE idReferencia=".$fDocumentacion["id__412_tablaDinamica"];
	$res=$con->obtenerFilas($consulta);
	$numEventos=1;
	$totalEventos=$con->filasAfectadas;
	while($fEventoAudiencia=mysql_fetch_row($res))
	{
		$consulta="SELECT fechaEvento,tipoAudiencia FROM 7000_eventosAudiencia 
				WHERE idRegistroEvento=".$fEventoAudiencia[0];
		$fEventoDVD=$con->obtenerPrimeraFila($consulta);
		
		$lblAudiencia=convertirFechaLetra($fEventoDVD[0]);
		$consulta="SELECT tipoAudiencia FROM _4_tablaDinamica WHERE id__4_tablaDinamica=".$fEventoDVD[1];
		$tAudiencia=$con->obtenerValor($consulta);
		$lblAudiencia.=" correspondiente a la audiencia de ".$tAudiencia;
		
		if($listaAudienciasDVD=="")
			$listaAudienciasDVD=$lblAudiencia;
		else
		{
			if($numEventos==$totalEventos)
				$listaAudienciasDVD.=" y ".$lblAudiencia;
			else
				$listaAudienciasDVD.=", ".$lblAudiencia;
		}
		$numEventos++;
		
	}
	
	$listaDcumentos="<ol>
						<li>Copias certificadas de la sentencia de fecha ".convertirFechaLetra($fSentencia).
						", emitida por el Licenciado ".obtenerNombreUsuario($idJuezAudiencia).", Juez ".$noJuez." 
						del Sistema Procesal Acusatorio de la Ciudad de M&eacute;xico, dentro de la carpeta judicial 
						".$fCarpeta[3]."<strong>,</strong> del &iacute;ndice de la ".$fDatosCarpetaBase[1].", 
						seguida en contra del sentenciado ".$sentenciados.", por el delito de <strong>".$delitos."</strong>, 
						en agravio de ".$victimas.".</li>
						<li>Copia certificada del auto de ".convertirFechaLetra($fAuto).", en el que se reconoce 
						la ejecutoria de la sentencia aludida.</li>
						<li>Copia certificada de ".($fDocumentacion["noDVD"]==1?" DVD":" DVD&acute;S")." de ".($numEventos==1?"la audiencia":"las audiencias")." de: 
						".$listaAudienciasDVD."</li>";
	
	$consulta="SELECT noBillete,montoBillete FROM _412_gridBilletes WHERE idReferencia=".$fDocumentacion["id__412_tablaDinamica"];
	$rDocumentos=$con->obtenerFilas($consulta);
	while($fDocumentos=mysql_fetch_row($rDocumentos))
	{
		
		$listaDcumentos.="<li>Billete de dep&oacute;sito n&uacute;mero ".$fDocumentos[0]." expedido por BANSEFI, por la cantidad de $ ".number_format($fDocumentos[1],2).
						" (".convertirNumeroLetra($fDocumentos[1],true,true).").</li>";
	}
	$listaDcumentos.="					
						<li>Un sobre cerrado que contiene los datos personales de las partes.</li>
					</ol>";
	
	
	$arrValores=array();
	
	$arrValores["carpetaEjecucion"]=$carpetaAdministrativa;
	$arrValores["delito"]=$delitos;
	$arrValores["sentenciado"]=$sentenciados;
	$arrValores["fecha"]=convertirFechaLetra($fechactual);
	$arrValores["horas"]="__________";
	$arrValores["sede"]="";
	$arrValores["noOficio"]="_______";
	$arrValores["nombreEmisor"]=$fDatosCarpetaBase[0];
	$arrValores["puesto"]="Director de la ".str_replace("Judicial","Judicial número",$fDatosCarpetaBase[1]);
	$arrValores["listaRecepcion"]=$listaDcumentos;
	$arrValores["horasBPM"]=date("H",strtotime($fCarpetaBase[1]));
	$arrValores["minutosBPM"]=date("i",strtotime($fCarpetaBase[1]));
	
	$consulta="SELECT e.* FROM 7007_contenidosCarpetaAdministrativa c,7000_eventosAudiencia e WHERE carpetaAdministrativa='".$carpetaAdministrativa."' 
				AND tipoContenido=3 AND idRegistroContenidoReferencia=e.idRegistroEvento AND e.situacion NOT IN(3) 
				AND e.tipoAudiencia=87 ORDER BY horaInicioEvento DESC";
	$fEvento=$con->obtenerPrimeraFilaAsoc($consulta);
	if($fEvento)
	{
		
		$consulta="SELECT u.Nombre FROM 7001_eventoAudienciaJuez e,800_usuarios u WHERE 
				e.idRegistroEvento=".$fEvento["idRegistroEvento"]." AND u.idUsuario=e.idJuez";
		$juezAudiencia=$con->obtenerValor($consulta);
		
		$hEvento=strtotime($fEvento["horaInicioEvento"]);
		
		$consulta="SELECT nombreSala FROM _15_tablaDinamica WHERE id__15_tablaDinamica=".$fEvento["idSala"];
		$nombreSala=$con->obtenerValor($consulta);
		$arrSala=explode(" ",$nombreSala)	;
		$arrValores["horasAudiencia"]=date("H:i",$hEvento);
		$arrValores["diaAudiencia"]=date("d",$hEvento);
		$arrValores["mesAudiencia"]=mb_strtoupper($arrMesLetra[(date("m",$hEvento)*1)-1]);
		$arrValores["anioAudiencia"]=date("Y",$hEvento);
		$arrValores["salaAudiencia"]=$arrSala[sizeof($arrSala)-1];
		$arrValores["juezAudiencia"]=$juezAudiencia;
	}
	else
	{
		$arrValores["horasAudiencia"]="_____";
		$arrValores["diaAudiencia"]="__________";
		$arrValores["mesAudiencia"]="__________";
		$arrValores["anioAudiencia"]="_____";
		$arrValores["salaAudiencia"]="_____";
		$arrValores["juezAudiencia"]="_____";
	}
	
	$consulta="SELECT j.usuarioJuez FROM _26_tablaDinamica j,_292_tablaDinamica jt WHERE jt.idEstado=1 and jt.nombreJueces=j.usuarioJuez
					and j.idReferencia=".$fDatosCarpetaEjecucion[2]." and '".$fechactual."'>=fechaInicial and '".$fechactual."'<=fechaFinal";
	$idUsuarioDestinatario=	$con->obtenerValor($consulta);	
	if($idUsuarioDestinatario=="")
		$idUsuarioDestinatario=-1;
	$arrValores["juezTramite"]=$idUsuarioDestinatario!=-1?obtenerNombreUsuario($idUsuarioDestinatario):"No definido";
	$arrValores["directorEjecucion"]=$fDatosCarpetaEjecucion[0];
	$arrValores["responsableCreacion"]=obtenerNombreUsuario($_SESSION["idUsr"]);
	$arrValores["lugarSentenciado"]="_____________";
	
	return $arrValores;
}

function llenarFormatoPlantillaEnvioCopiaSentencia($carpetaEjecucion,$idSentenciado,$idPena)
{	
	global $con;
	global $arrMesLetra;
	global $baseDir;
	
	$PHPWord = new PHPWord();
	$document = $PHPWord->loadTemplate($baseDir.'\\modulosEspeciales_SGJP\\formatos\\plantillaEnvioCopiaSentencia.docx');	
	
	$fechactual=date("Y-m-d");
	$consulta="SELECT idActividad,fechaCreacion,unidadGestion,carpetaAdministrativaBase FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$carpetaEjecucion."'";
	
	$fCarpeta=$con->obtenerPrimeraFila($consulta);
	
	$idActividad=$fCarpeta[0];	
	
	$consulta="SELECT CONCAT(nombre,' ',apellidoPaterno,' ',apellidoMaterno) FROM _47_tablaDinamica i
				WHERE i.id__47_tablaDinamica=".$idSentenciado;
	$sentenciados=$con->obtenerValor($consulta);
	
	
	
		
	$consulta="SELECT de.denominacionDelito FROM 7032_delitosPena d,_35_denominacionDelito de 
				WHERE d.idPena=".$idPena." AND de.id__35_denominacionDelito=d.idDelito";
	$delitos=$con->obtenerListaValores($consulta);
	
	$arrValores=array();
	$arrValores["carpetaAdministrativa"]=$fCarpeta[3];
	$arrValores["carpetaEjecucion"]=$carpetaEjecucion;
	$arrValores["sentenciado"]=$sentenciados;
	$arrValores["fecha"]=convertirFechaLetra($fCarpeta[1]);
	$arrValores["delitos"]=$delitos;
	foreach($arrValores as $llave=>$valor)
	{
		$document->setValue("[".$llave."]",utf8_decode($valor));	
	}
	
	$nombreAleatorio=generarNombreArchivoTemporal();
	$nomArchivo=$nombreAleatorio;
	$document->save($nomArchivo);
	
	//$nombreFinal=str_replace(".docx",".pdf",$nomArchivo);
	generarDocumentoPDF($nomArchivo,false,false,true,$nomArchivo,"",$baseDir."/archivosTemporales");
	
	$idDocumento=registrarDocumentoServidorRepositorio($nomArchivo.".pdf","ConstanciaEnvioCopiaSentencia.pdf",13,"");
	return $idDocumento;
}

function llenarFormatoPlantillaAcuseEnvioCopiaSentencia($idFormulario,$idRegistro)
{	
	global $con;
	global $arrMesLetra;
	global $baseDir;
	
	$PHPWord = new PHPWord();
	$document = $PHPWord->loadTemplate($baseDir.'\\modulosEspeciales_SGJP\\formatos\\plantillaRecepcionCopiaSentencia.docx');	
	
	$fechactual=date("Y-m-d");
	
	$consulta="SELECT carpetaEjecucion,carpetaAdministrativa,idSentenciado,fechaCreacion,idPena FROM _428_tablaDinamica WHERE id__428_tablaDinamica=".$idRegistro;
	$fRegistro=$con->obtenerPrimeraFila($consulta);
	
	$consulta="SELECT idActividad,fechaCreacion,unidadGestion,carpetaAdministrativaBase FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$fRegistro[0]."'";
	$fCarpeta=$con->obtenerPrimeraFila($consulta);

	$idActividad=$fCarpeta[0];	
	
	$consulta="SELECT CONCAT(nombre,' ',apellidoPaterno,' ',apellidoMaterno) FROM _47_tablaDinamica i
				WHERE i.id__47_tablaDinamica=".$fRegistro[2];
	$sentenciados=$con->obtenerValor($consulta);	
		
	$consulta="SELECT de.denominacionDelito FROM 7032_delitosPena d,_35_denominacionDelito de 
				WHERE d.idPena=".$fRegistro[4]." AND de.id__35_denominacionDelito=d.idDelito";
	$delitos=$con->obtenerListaValores($consulta);
	
	$arrValores=array();
	$arrValores["carpetaAdministrativa"]=$fCarpeta[3];
	$arrValores["carpetaEjecucion"]=$fRegistro[0];
	$arrValores["sentenciado"]=$sentenciados;
	$arrValores["fecha"]=convertirFechaLetra($fRegistro[3]);
	$arrValores["hora"]=date("H:i",strtotime($fRegistro[3]))." hrs.";
	$arrValores["delitos"]=$delitos;
	foreach($arrValores as $llave=>$valor)
	{
		$document->setValue("[".$llave."]",utf8_decode($valor));	
	}
	
	
	$nombreAleatorio=generarNombreArchivoTemporal();
	$nomArchivo=$nombreAleatorio;
	$document->save($nomArchivo);
	
	//$nombreFinal=str_replace(".docx",".pdf",$nomArchivo);
	generarDocumentoPDF($nomArchivo,false,false,true,$nomArchivo,"",$baseDir."/archivosTemporales");
	
	$idDocumento=registrarDocumentoServidorRepositorio($nomArchivo.".pdf","ConstanciaRecepcionCopiaSentencia.pdf",13,"");
	
	
	registrarDocumentoCarpetaAdministrativa($arrValores["carpetaAdministrativa"],$idDocumento,$idFormulario,$idRegistro);
	$x=0;
	$query[$x]="begin";
	$x++;
	$query[$x]="INSERT INTO 9074_documentosRegistrosProceso(idFormulario,idRegistro,idDocumento,tipoDocumento) 
				VALUES(428,".$idRegistro.",".$idDocumento.",2)";
	$x++;
	$query[$x]="commit";
	$x++;
	$con->ejecutarBloque($query);
	
	return $idDocumento;
}


function obtenerPermisosActor($actor,$idDocumento,$tipoDocumento,$iFormulario,$iRegistro)
{
	global $con;
	

	$idDocumentoAdjunto=-1;
	$actorEtapa=$actor;
	$consulta="SELECT perfilValidacion FROM _10_tablaDinamica WHERE id__10_tablaDinamica=".$tipoDocumento;

	$idPerfilEvaluacion=$con->obtenerValor($consulta);
	if($idPerfilEvaluacion=="")
		$idPerfilEvaluacion=-1;
	
	
	$consulta="SELECT noEtapa FROM _429_gridEtapas WHERE idReferencia=".$idPerfilEvaluacion." AND etapaInicial=1";
	$situacionActual=$con->obtenerValor($consulta);
	if($situacionActual=="")
		$situacionActual=0;
	
	
	$idFormularioBaseDocumento=-1;
	$idRegistroBaseDocumento=-1;
	
	if($idDocumento!=-1)
	{
		$consulta="SELECT situacionActual,idPerfilEvaluacion,idDocumentoAdjunto,idFormulario,idRegistro FROM 3000_formatosRegistrados 
				WHERE idRegistroFormato=".$idDocumento;
		
		$fDatosDocumento=$con->obtenerPrimeraFila($consulta);
		
		$idFormularioBaseDocumento=$fDatosDocumento[3];
		$idRegistroBaseDocumento=$fDatosDocumento[4];
		
		switch($idFormularioBaseDocumento)
		{
			case -2:
				$consulta="SELECT idFormulario,idReferencia FROM 7035_informacionDocumentos WHERE idRegistro=".$idRegistroBaseDocumento;
				$fAux=$con->obtenerPrimeraFila($consulta);
				$idFormularioBaseDocumento=$fAux[0];
				$idRegistroBaseDocumento=$fAux[1];
				
			break;
		}
		
		$situacionActual=$fDatosDocumento[0];
		$idPerfilEvaluacion=$fDatosDocumento[1];
		$consulta="SELECT rolCambio,rolActual FROM 3000_bitacoraFormatos WHERE idRegistroFormato=".$idDocumento.
				" AND idEstadoActual=".$situacionActual." ORDER BY fechaCambio DESC";

		$fBitacora=$con->obtenerPrimeraFila($consulta);
		if(($fBitacora)&&($fBitacora[0]!=$fBitacora[1]))
		{
			if(strpos($fBitacora[0],"@@")!==false)
			{
				$arrDestinatariosAux=explode(",",$fBitacora[0]);
				foreach($arrDestinatariosAux as $uDestinatario)
				{
					$dDestinatario=explode("@@",$uDestinatario);
					if($dDestinatario[0]==$actor)
					{
						$fBitacora[0]=$dDestinatario[0];
						break;
					}
				}
			}
			$actorEtapa=$fBitacora[0];
		}
		$idDocumentoAdjunto=$fDatosDocumento[2];
		if($idDocumentoAdjunto=="")
			$idDocumentoAdjunto=-1;
	}
	
	$objPermisos='{"etapaActual":"'.$situacionActual.'","permisosRol":"'.$actor.'","permiteEditar":"0","permiteEvaluar":"0","confEvaluacion":{"arrOpciones":[]},"permiteFirmar":"0",
				"confFirma":{"etapaEnvioFirma":"","rolDestinatarioEnvioFirma":""},"permiteTurnar":"0","permiteReprocesar":"0",
				"confTurno":{"arrOpciones":[]}}';
	if($actorEtapa!=$actor)
			$actor="0_0";
	
	if(($idPerfilEvaluacion!="-1")&&($actor!="0_0"))
	{
		
		$arrRol=explode("_",$actor);
		$consulta="SELECT * FROM _430_tablaDinamica WHERE idReferencia=".$idPerfilEvaluacion." and etapa=".$situacionActual." AND rol=".$arrRol[0];
		$fPermisos=$con->obtenerPrimeraFilaAsoc($consulta);

		if(!$fPermisos)
		{
			$fPermisos=array();
			$fPermisos["id__430_tablaDinamica"]=-1;
			$fPermisos["permiteEditar"]=0;
			$fPermisos["permiteEvaluar"]=0;
			$fPermisos["permiteFirmar"]=0;
			$fPermisos["etapaEnvioFirma"]=0;
			
			$fPermisos["permiteTurnar"]=0;
			$fPermisos["permiteReprocesar"]=0;
		}
		$arrOpciones="";
		$consulta="SELECT idEValuacion,descripcion,etapaEnvio,rolDestinatario FROM _430_gridOpcionesEvaluacion 
					WHERE idReferencia=".$fPermisos["id__430_tablaDinamica"]." ORDER BY idEValuacion";
		$resOpciones=$con->obtenerFilas($consulta);
		while($fOpciones=mysql_fetch_row($resOpciones))
		{
			$rolDestinatario=$fOpciones[3]."_0";
			$idUsuarioDestino="0";
			switch($fOpciones[3])
			{
				case 126: //Remitente envio
					$consulta="SELECT * FROM 3000_bitacoraFormatos WHERE idRegistroFormato=".$idDocumento.
									" AND idEstadoActual=".$situacionActual." ORDER BY fechaCambio DESC";
					$fDatosBitacora=$con->obtenerPrimeraFilaAsoc($consulta);
					$rolDestinatario=$fDatosBitacora["rolActual"];
					$idUsuarioDestino=$fDatosBitacora["responsableCambio"];
					
					break;
				case 127:
					$consulta="SELECT rolSuperior FROM _429_gridRoles WHERE idReferencia=".$idPerfilEvaluacion." AND rol='".$arrRol[0]."'";
					$rolDestinatario=$con->obtenerValor($consulta);
					if($rolDestinatario!="")
						$rolDestinatario.="_0";
					break;
				
				case 138: //Puesto organizacional superior
					$consulta="SELECT claveNivel,e.idReferencia FROM _421_tablaDinamica e,_420_tablaDinamica p ,_420_unidadGestion uGP, _17_tablaDinamica uG
					WHERE usuarioAsignado=".$_SESSION["idUsr"]." AND e.idReferencia=p.id__420_tablaDinamica AND uGP.idPadre=p.id__420_tablaDinamica 
					AND uG.id__17_tablaDinamica=uGP.idOpcion AND uG.claveUnidad='".$_SESSION["codigoInstitucion"]."'";
					
					$fDatosOrganigrama=$con->obtenerPrimeraFila($consulta);
					
					$arrPuestos=array();
					$arrNombreUsuarios=array();
					obtenerPuestosSuperiores($fDatosOrganigrama[1],$fDatosOrganigrama[0],$arrPuestos,false);
					
					foreach($arrPuestos as $p)
					{
						$rolDestinatario=obtenerActorParticipacionDocumento($p["idUsuario"],$tipoDocumento,$fOpciones[1]);
						if($rolDestinatario=="0_0")
							continue;
						
						
						$o='{"IDEvaluacion":"'.$fOpciones[0].'","leyenda":"'.cv($fOpciones[1]).'","etapaEnvio":"'.$fOpciones[2].
							'","rolDestinatario":"'.$rolDestinatario.'","usuarioDestino":"0"}';
						$arrNombreUsuarios[$lblRol]=$o;
						break;
						
						
					}
					
					
					ksort($arrNombreUsuarios);
					
					foreach($arrNombreUsuarios as $o)
					{
						if($arrOpcionesTurno=="")
							$arrOpcionesTurno=$o;
						else
							$arrOpcionesTurno.=",".$o;
					}
					$agregar=false;
					break;
				case 139: //Puesto organizacional inferior
						$consulta="SELECT claveNivel,e.idReferencia FROM _421_tablaDinamica e,_420_tablaDinamica p ,_420_unidadGestion uGP, _17_tablaDinamica uG
						WHERE usuarioAsignado=".$_SESSION["idUsr"]." AND e.idReferencia=p.id__420_tablaDinamica AND uGP.idPadre=p.id__420_tablaDinamica 
						AND uG.id__17_tablaDinamica=uGP.idOpcion AND uG.claveUnidad='".$_SESSION["codigoInstitucion"]."'";

						$fDatosOrganigrama=$con->obtenerPrimeraFila($consulta);

						$arrPuestos=array();
						$arrNombreUsuarios=array();
						obtenerPuestosHijosAsignaciones($fDatosOrganigrama[1],$fDatosOrganigrama[0],$arrPuestos,false);

						foreach($arrPuestos as $p)
						{
							$rolDestinatario=obtenerActorParticipacionDocumento($p["idUsuario"],$tipoDocumento,$fOpciones[1]);
							if($rolDestinatario=="0_0")
								continue;
							
							$o='{"IDEvaluacion":"'.$fOpciones[0].'","leyenda":"'.cv($fOpciones[1]).'","etapaEnvio":"'.$fOpciones[2].
							'","rolDestinatario":"'.$rolDestinatario.'","usuarioDestino":"0"}';
							$arrNombreUsuarios[$lblRol]=$o;
							break;


						}


						ksort($arrNombreUsuarios);

						foreach($arrNombreUsuarios as $o)
						{
							if($arrOpcionesTurno=="")
								$arrOpcionesTurno=$o;
							else
								$arrOpcionesTurno.=",".$o;
						}
						$agregar=false;
					break;
				case 140:
					$consulta="SELECT responsableCambio,rolActual FROM 3000_bitacoraFormatos 
								WHERE idRegistroFormato=".$idDocumento." AND idEstadoAnterior<>idEstadoActual 
								AND idEstadoAnterior<>0 ORDER BY fechaCambio ASC";
					$fDatosBitacora=$con->obtenerPrimeraFilaAsoc($consulta);
					$rolDestinatario=$fDatosBitacora["rolActual"];
					$idUsuarioDestino=$fDatosBitacora["responsableCambio"];
					
					break;
				case 143: //Auxiliar judicial
					
					$consulta="SELECT idOpcion FROM _445_auxiliarJudicial a,_445_tablaDinamica j WHERE a.idPadre=j.id__445_tablaDinamica AND
								j.juez= ".$_SESSION["idUsr"];
					$fAuxiliar=$con->obtenerPrimeraFilaAsoc($consulta);
					if(!$fAuxiliar)
					{
						$consulta="SELECT * FROM 3000_bitacoraFormatos WHERE idRegistroFormato=".$idDocumento.
										" AND idEstadoActual=".$situacionActual." ORDER BY fechaCambio DESC";
						$fDatosBitacora=$con->obtenerPrimeraFilaAsoc($consulta);
						$rolDestinatario=$fDatosBitacora["rolActual"];
						$idUsuarioDestino=$fDatosBitacora["responsableCambio"];
					}
					else
					{
						$idUsuarioDestino=$fAuxiliar["idOpcion"];
					}
					break;
			}
			$o='{"IDEvaluacion":"'.$fOpciones[0].'","leyenda":"'.cv($fOpciones[1]).'","etapaEnvio":"'.$fOpciones[2].
				'","rolDestinatario":"'.$rolDestinatario.'","usuarioDestino":"'.$idUsuarioDestino.'"}';
			if($arrOpciones=="")
				$arrOpciones=$o;
			else
				$arrOpciones.=",".$o;
		}
		$arrOpcionesTurno="";
		$consulta="SELECT destinatario,etapa,etiqueta,funcionDestinatario,tipoFuncion FROM _430_gridRolesTurno WHERE idReferencia=".$fPermisos["id__430_tablaDinamica"];


		$rOpcionesTurno=$con->obtenerFilas($consulta);
		while($fOpciones=mysql_fetch_row($rOpcionesTurno))
		{
			$rolDestinatario=$fOpciones[0]."_0";
			$idUsuarioDestino="0";
			$agregar=true;
			switch($fOpciones[0])
			{
				case 126: //Remitente envio
					$consulta="SELECT * FROM 3000_bitacoraFormatos WHERE idRegistroFormato=".$idDocumento.
									" AND idEstadoActual=".$situacionActual." ORDER BY fechaCambio DESC";
					$fDatosBitacora=$con->obtenerPrimeraFilaAsoc($consulta);
					$rolDestinatario=$fDatosBitacora["rolActual"];
					$idUsuarioDestino=$fDatosBitacora["responsableCambio"];
					
					break;
				case 127:
					$consulta="SELECT rolSuperior FROM _429_gridRoles WHERE idReferencia=".$idPerfilEvaluacion." AND rol='".$arrRol[0]."'";

					$rolDestinatario=$con->obtenerValor($consulta);
					if($rolDestinatario!="")
						$rolDestinatario.="_0";
					break;
					
				case 136: //Puestos organizacionales superiores
					
					$consulta="SELECT claveNivel,e.idReferencia FROM _421_tablaDinamica e,_420_tablaDinamica p ,_420_unidadGestion uGP, _17_tablaDinamica uG
					WHERE usuarioAsignado=".$_SESSION["idUsr"]." AND e.idReferencia=p.id__420_tablaDinamica AND uGP.idPadre=p.id__420_tablaDinamica 
					AND uG.id__17_tablaDinamica=uGP.idOpcion AND uG.claveUnidad='".$_SESSION["codigoInstitucion"]."'";
					
					$fDatosOrganigrama=$con->obtenerPrimeraFila($consulta);
					
					$arrPuestos=array();
					$arrNombreUsuarios=array();
					obtenerPuestosSuperiores($fDatosOrganigrama[1],$fDatosOrganigrama[0],$arrPuestos,true);
					
					foreach($arrPuestos as $p)
					{
						$rolDestinatario=obtenerActorParticipacionDocumento($p["idUsuario"],$tipoDocumento,$fOpciones[1]);
						if($rolDestinatario=="0_0")
							continue;
						$lblRol=obtenerNombreUsuario($p["idUsuario"])." (".obtenerTituloRol($rolDestinatario).")";
						$o='{"etapaEnvio":"'.$fOpciones[1].'","lblRol":"'.$lblRol.'","rolDestinatario":"'.$rolDestinatario.'","usuarioDestino":"'.$p["idUsuario"].'"}';
						
						$arrNombreUsuarios[$lblRol]=$o;
						
						
					}
					
					
					ksort($arrNombreUsuarios);
					
					foreach($arrNombreUsuarios as $o)
					{
						if($arrOpcionesTurno=="")
							$arrOpcionesTurno=$o;
						else
							$arrOpcionesTurno.=",".$o;
					}
					$agregar=false;
				break;
				case 137: //Puestos organizacionales inferiores
					$consulta="SELECT claveNivel,e.idReferencia FROM _421_tablaDinamica e,_420_tablaDinamica p ,_420_unidadGestion uGP, _17_tablaDinamica uG
					WHERE usuarioAsignado=".$_SESSION["idUsr"]." AND e.idReferencia=p.id__420_tablaDinamica AND uGP.idPadre=p.id__420_tablaDinamica 
					AND uG.id__17_tablaDinamica=uGP.idOpcion AND uG.claveUnidad='".$_SESSION["codigoInstitucion"]."'";
					
					$fDatosOrganigrama=$con->obtenerPrimeraFila($consulta);
					
					$arrPuestos=array();
					$arrNombreUsuarios=array();
					obtenerPuestosHijosAsignaciones($fDatosOrganigrama[1],$fDatosOrganigrama[0],$arrPuestos,true);
					
					foreach($arrPuestos as $p)
					{
						$rolDestinatario=obtenerActorParticipacionDocumento($p["idUsuario"],$tipoDocumento,$fOpciones[1]);
						if($rolDestinatario=="0_0")
							continue;
						$lblRol=obtenerNombreUsuario($p["idUsuario"])." (".obtenerTituloRol($rolDestinatario).")";
						$o='{"etapaEnvio":"'.$fOpciones[1].'","lblRol":"'.$lblRol.'","rolDestinatario":"'.$rolDestinatario.'","usuarioDestino":"'.$p["idUsuario"].'"}';
						
						$arrNombreUsuarios[$lblRol]=$o;
						
						
					}
					
					
					ksort($arrNombreUsuarios);
					
					foreach($arrNombreUsuarios as $o)
					{
						if($arrOpcionesTurno=="")
							$arrOpcionesTurno=$o;
						else
							$arrOpcionesTurno.=",".$o;
					}
					$agregar=false;
				break;
				case 138: //Puesto organizacional superior
					$consulta="SELECT claveNivel,e.idReferencia FROM _421_tablaDinamica e,_420_tablaDinamica p ,_420_unidadGestion uGP, _17_tablaDinamica uG
					WHERE usuarioAsignado=".$_SESSION["idUsr"]." AND e.idReferencia=p.id__420_tablaDinamica AND uGP.idPadre=p.id__420_tablaDinamica 
					AND uG.id__17_tablaDinamica=uGP.idOpcion AND uG.claveUnidad='".$_SESSION["codigoInstitucion"]."'";
					
					$fDatosOrganigrama=$con->obtenerPrimeraFila($consulta);
					
					$arrPuestos=array();
					$arrNombreUsuarios=array();
					obtenerPuestosSuperiores($fDatosOrganigrama[1],$fDatosOrganigrama[0],$arrPuestos,false);
					
					foreach($arrPuestos as $p)
					{
						$rolDestinatario=obtenerActorParticipacionDocumento($p["idUsuario"],$tipoDocumento,$fOpciones[1]);
						if($rolDestinatario=="0_0")
							continue;
						$lblRol=obtenerNombreUsuario($p["idUsuario"])." (".obtenerTituloRol($rolDestinatario).")";
						$o='{"etapaEnvio":"'.$fOpciones[1].'","lblRol":"'.$lblRol.'","rolDestinatario":"'.$rolDestinatario.'","usuarioDestino":"'.$p["idUsuario"].'"}';
						
						$arrNombreUsuarios[$lblRol]=$o;
						
						
					}
					
					
					ksort($arrNombreUsuarios);
					
					foreach($arrNombreUsuarios as $o)
					{
						if($arrOpcionesTurno=="")
							$arrOpcionesTurno=$o;
						else
							$arrOpcionesTurno.=",".$o;
					}
					$agregar=false;
				break;
				case 139: //Puesto organizacional inferior
						$consulta="SELECT claveNivel,e.idReferencia FROM _421_tablaDinamica e,_420_tablaDinamica p ,_420_unidadGestion uGP, _17_tablaDinamica uG
						WHERE usuarioAsignado=".$_SESSION["idUsr"]." AND e.idReferencia=p.id__420_tablaDinamica AND uGP.idPadre=p.id__420_tablaDinamica 
						AND uG.id__17_tablaDinamica=uGP.idOpcion AND uG.claveUnidad='".$_SESSION["codigoInstitucion"]."'";

						$fDatosOrganigrama=$con->obtenerPrimeraFila($consulta);

						$arrPuestos=array();
						$arrNombreUsuarios=array();
						obtenerPuestosHijosAsignaciones($fDatosOrganigrama[1],$fDatosOrganigrama[0],$arrPuestos,false);

						foreach($arrPuestos as $p)
						{
							$rolDestinatario=obtenerActorParticipacionDocumento($p["idUsuario"],$tipoDocumento,$fOpciones[1]);
							if($rolDestinatario=="0_0")
								continue;
							$lblRol=obtenerNombreUsuario($p["idUsuario"])." (".obtenerTituloRol($rolDestinatario).")";
							$o='{"etapaEnvio":"'.$fOpciones[1].'","lblRol":"'.$lblRol.'","rolDestinatario":"'.$rolDestinatario.'","usuarioDestino":"'.$p["idUsuario"].'"}';

							$arrNombreUsuarios[$lblRol]=$o;


						}


						ksort($arrNombreUsuarios);

						foreach($arrNombreUsuarios as $o)
						{
							if($arrOpcionesTurno=="")
								$arrOpcionesTurno=$o;
							else
								$arrOpcionesTurno.=",".$o;
						}
						$agregar=false;
				break;
				case 140:
					$consulta="SELECT responsableCambio,rolActual FROM 3000_bitacoraFormatos 
								WHERE idRegistroFormato=".$idDocumento." AND idEstadoAnterior<>idEstadoActual 
								AND idEstadoAnterior<>0 ORDER BY fechaCambio ASC";
					$fDatosBitacora=$con->obtenerPrimeraFilaAsoc($consulta);
					$rolDestinatario=$fDatosBitacora["rolActual"];
					$idUsuarioDestino=$fDatosBitacora["responsableCambio"];
					
				break;
				case 143: //Auxiliar judicial
					
					$consulta="SELECT idOpcion FROM _445_auxiliarJudicial a,_445_tablaDinamica j WHERE a.idPadre=j.id__445_tablaDinamica AND
								j.juez= ".$_SESSION["idUsr"];
					$fAuxiliar=$con->obtenerPrimeraFilaAsoc($consulta);
					if(!$fAuxiliar)
					{
						$consulta="SELECT * FROM 3000_bitacoraFormatos WHERE idRegistroFormato=".$idDocumento.
										" AND idEstadoActual=".$situacionActual." ORDER BY fechaCambio DESC";
						$fDatosBitacora=$con->obtenerPrimeraFilaAsoc($consulta);
						$rolDestinatario=$fDatosBitacora["rolActual"];
						$idUsuarioDestino=$fDatosBitacora["responsableCambio"];
					}
					else
					{
						$idUsuarioDestino=$fAuxiliar["idOpcion"];
					}
					break;
			}
			
			
			
			
			
			if(($fOpciones[3]!="")&&($fOpciones[3]!="-1"))
			{
				$cadObjParam='{"rolDestinatario":"'.$rolDestinatario.'","idRegistroFormato":"'.$idDocumento.
							'","tipoDocumento":"'.$tipoDocumento.'","iFormulario":"'.$iFormulario.'","iRegistro":"'.$iRegistro.'"}';
				$oParamFuncion=json_decode($cadObjParam);
				
				$cache=NULL;
				$resultado=resolverExpresionCalculoPHP($fOpciones[3],$oParamFuncion,$cache);
				
				
				$lblRol=obtenerTituloRol($rolDestinatario);
				if($fOpciones[2]!="")
				{
					$lblRol=$fOpciones[2];
				}
				
				switch($fOpciones[4])
				{
					case 1://Generadora de opcion
						$listaDestinatarios="";
						$listaNombreDestinatarios="";	
						$arrOpcionesTurnoAux='';				
						foreach($resultado as $r)
						{
							$o='{"etapaEnvio":"'.$fOpciones[1].'","lblRol":"'.cv(isset($r->etiqueta)?$r->etiqueta:obtenerNombreUsuario($r->idUsuario)).'","rolDestinatario":"'.$rolDestinatario.'","usuarioDestino":"'.$r->idUsuario.'"}';
							if($arrOpcionesTurnoAux=="")
								$arrOpcionesTurnoAux=$o;
							else
								$arrOpcionesTurnoAux.=",".$o;
						}
						
						if($arrOpcionesTurno=="")
							$arrOpcionesTurno='{"lblRol":"'.cv($lblRol).'","hijos":['.$arrOpcionesTurnoAux.']}';
						else
							$arrOpcionesTurno.=',{"lblRol":"'.cv($lblRol).'","hijos":['.$arrOpcionesTurnoAux.']}';
						
					
					

						$agregar=false;
					break;
					case 2://Determinadora de destinatarios
						
						
						$listaDestinatarios="";
						$listaNombreDestinatarios="";
						
						if(sizeof($resultado)==1)
						{
							$r=	$resultado[0];
							$etiqueta=isset($r->etiqueta)?$r->etiqueta:obtenerNombreUsuario($r->idUsuario);
							$listaDestinatarios=$r->idUsuario;
							$listaNombreDestinatarios=$etiqueta;
						}
						else
						{
							if(sizeof($resultado)>1)
							{
								$listaDestinatarios=-1;
								$rDestinatario="";
								foreach($resultado as $r)
								{
									$etiqueta=isset($r->etiqueta)?$r->etiqueta:obtenerNombreUsuario($r->idUsuario);
									if($listaNombreDestinatarios=="")
									{
										$rDestinatario=$rolDestinatario."@@".$r->idUsuario;
										$listaNombreDestinatarios=$etiqueta;
									}
									else
									{
										$rDestinatario.=",".$rolDestinatario."@@".$r->idUsuario;
										$listaNombreDestinatarios.=", ".$etiqueta;
									}
								}
								$rolDestinatario=$rDestinatario;
							}
						}
						if($listaDestinatarios=="")
						{
							$listaDestinatarios=0;
							$lblRol.=" (Función NO ejecutada)";
						}
						else
						{
							$lblRol.=" (".$listaNombreDestinatarios.")";
						}
						
						$o='{"etapaEnvio":"'.$fOpciones[1].'","lblRol":"'.cv($lblRol).'","rolDestinatario":"'.$rolDestinatario.
							'","usuarioDestino":"'.$listaDestinatarios.'"}';

						
						
						if($arrOpcionesTurno=="")
							$arrOpcionesTurno=$o;
						else
							$arrOpcionesTurno.=",".$o;
					
					
						$agregar=false;
					break;
				}
			}
			
			if($agregar)
			{
				$lblRol=obtenerTituloRol($rolDestinatario);
				if($fOpciones[2]!="")
				{
					$lblRol=$fOpciones[2];
				}
				$o='{"etapaEnvio":"'.$fOpciones[1].'","lblRol":"'.$lblRol.'","rolDestinatario":"'.$rolDestinatario.'","usuarioDestino":"'.$idUsuarioDestino.'"}';
				if($arrOpcionesTurno=="")
					$arrOpcionesTurno=$o;
				else
					$arrOpcionesTurno.=",".$o;
			}
		}		
		
		$rolDestinatarioEnvioFirma="";
		$usuarioDestinoFirma="0";
		if($fPermisos["permiteFirmar"]==1)
		{

			switch($fPermisos["rolDestinatarioEnvioFirma"])
			{
				case 0:
					$cache=NULL;
					$cadObj='{"idFormulario":"'.$idFormularioBaseDocumento.'","idRegistro":"'.$idRegistroBaseDocumento.'","tipoDocumento":"'.$tipoDocumento.'"}';
					
					$obj=json_decode($cadObj);
					$resultado=resolverExpresionCalculoPHP($fPermisos["funcionDestinatario"],$obj,$cache);
					
					if(gettype($resultado)=="array")
					{
						$arrRolesDestinatarios="";						
						foreach($resultado as $r)
						{
							if($arrRolesDestinatarios=="")
								$arrRolesDestinatarios=$r;
							else	
								$arrRolesDestinatarios.=",".$r;
						}
						
						$rolDestinatarioEnvioFirma=$arrRolesDestinatarios;
						$usuarioDestinoFirma=-1;
					}
					else
					{
						$rolDestinatarioEnvioFirma=removerComillasLimite($resultado);
						$usuarioDestinoFirma=0;
						if(strpos($rolDestinatarioEnvioFirma,"@@")!==false)
						{
							$arrRol=explode("@@",$rolDestinatarioEnvioFirma);
							$rolDestinatarioEnvioFirma=$arrRol[0];
							$usuarioDestinoFirma=$arrRol[1];
						}

						
					}
				break;
				case 126: //Remitente envio
					$consulta="SELECT * FROM 3000_bitacoraFormatos WHERE idRegistroFormato=".$idDocumento.
									" AND idEstadoActual=".$situacionActual." ORDER BY fechaCambio DESC";
					$fDatosBitacora=$con->obtenerPrimeraFilaAsoc($consulta);
					$rolDestinatarioEnvioFirma=$fDatosBitacora["rolActual"];
					$usuarioDestinoFirma=$fDatosBitacora["responsableCambio"];
					
					break;
				case 127: //Actor particpante siguiente
					$consulta="SELECT rolSuperior FROM _429_gridRoles WHERE idReferencia=".$idPerfilEvaluacion." AND rol='".$arrRol[0]."'";
					$rolDestinatarioEnvioFirma=$con->obtenerValor($consulta);
					if($rolDestinatarioEnvioFirma!="")
						$rolDestinatarioEnvioFirma.="_0";
				break;
					
				
				case 140: //Rol q empezo proceso
					$consulta="SELECT responsableCambio,rolActual FROM 3000_bitacoraFormatos 
								WHERE idRegistroFormato=".$idDocumento." AND idEstadoAnterior<>idEstadoActual 
								AND idEstadoAnterior<>0 ORDER BY fechaCambio ASC";
					
					$fDatosBitacora=$con->obtenerPrimeraFilaAsoc($consulta);
					$rolDestinatarioEnvioFirma=$fDatosBitacora["rolActual"];
					$usuarioDestinoFirma=$fDatosBitacora["responsableCambio"];
					
				break;
				default:
					$rolDestinatarioEnvioFirma=$fPermisos["rolDestinatarioEnvioFirma"]."_0";
					$usuarioDestinoFirma=0;
					
					
					
				break;
			}
		}
		
		if($idDocumentoAdjunto!=-1)
		{
			
			$fPermisos["permiteReprocesar"]=0;
		}
		
		$objPermisos='{"permiteReprocesar":"'.$fPermisos["permiteReprocesar"].'","permiteEditar":"'.$fPermisos["permiteEditar"].'","permiteEvaluar":"'.$fPermisos["permiteEvaluar"].
						'","confEvaluacion":{"arrOpciones":['.$arrOpciones.']},"permiteFirmar":"'.$fPermisos["permiteFirmar"].
						'","permisosRol":"'.$actor.'","etapaActual":"'.$situacionActual.'","confFirma":{"etapaEnvioFirma":"'.$fPermisos["etapaEnvioFirma"].
						'","rolDestinatarioEnvioFirma":"'.$rolDestinatarioEnvioFirma.'","usuarioDestino":"'.$usuarioDestinoFirma.'"},"permiteTurnar":"'.
						$fPermisos["permiteTurnar"].'","confTurno":{"arrOpciones":['.$arrOpcionesTurno.']}}';

	}
	
	return $objPermisos;	
		
		
		
	
	
}

function obtenerActorParticipacionDocumento($idUsuario,$tipoDocumento,$noEtapa)
{
	global $con;
	
	$consulta="SELECT perfilValidacion FROM _10_tablaDinamica WHERE id__10_tablaDinamica=".$tipoDocumento;
	$idPerfilEvaluacion=$con->obtenerValor($consulta);
	if($idPerfilEvaluacion=="")
		$idPerfilEvaluacion=-1;
	
	$consulta="SELECT codigoRol FROM 807_usuariosVSRoles WHERE idUsuario=".$idUsuario;
	$listaRoles=$con->obtenerListaValores($consulta,"'");
	
	
	$consulta="select rol from (SELECT concat(e.rol,'_0') as rol,jerarquia FROM _430_tablaDinamica e,_429_gridRoles r WHERE 
								e.idReferencia=".$idPerfilEvaluacion." and e.idReferencia=r.idReferencia 
								 AND etapa=".$noEtapa." AND  e.rol=r.rol) as tmp where rol in (".$listaRoles.") 
								 order by jerarquia desc";
	$rolActual=$con->obtenerValor($consulta);
	
	
	if($rolActual=="")
		$rolActual="0_0";
	return $rolActual;
	
}

function cambiarSituacionDocumento($iRegistro,$actor,$etapaCambio,$comentarios,$rolActual,$resultadoEvaluacion,$idUsuarioDestinatario=0)
{
	global $con;

	
	$consulta="select situacionActual,idPerfilEvaluacion from 3000_formatosRegistrados where idRegistroFormato=".$iRegistro;
	$fDocument=$con->obtenerPrimeraFila($consulta);
	$idPerfilValidacion=$fDocument[1];
	
	if($idPerfilValidacion=="")
		$idPerfilValidacion=-1;
	
	$etapaOrigen=$fDocument[0];
	if($etapaOrigen=="")
		$etapaOrigen=0;
	
	
	$x=0;
	$query[$x]="begin";
	$x++;
	$query[$x]="INSERT INTO 3000_bitacoraFormatos(idRegistroFormato,idEstadoAnterior,fechaCambio,idEstadoActual,responsableCambio,
				comentariosAdicionales,rolCambio,rolActual,resultadoEvaluacion,idDestinatario) VALUES(".$iRegistro.",".$etapaOrigen.",'".date("Y-m-d H:i:s")."',".$etapaCambio.
		",".$_SESSION["idUsr"].",'".cv($comentarios)."','".$actor."','".$rolActual."',".$resultadoEvaluacion.",".$idUsuarioDestinatario.")";
	$x++;
	$query[$x]="set @idRegistroBitacora:=(select last_insert_id())";
	$x++;
	$query[$x]="UPDATE 3000_formatosRegistrados SET situacionActual=".$etapaCambio." WHERE idRegistroFormato=".$iRegistro;
	$x++;
	$query[$x]="commit";
	$x++;
	if( $con->ejecutarBloque($query))
	{
		
		$cache=NULL;
		$consulta="select @idRegistroBitacora";
		$idRegistroBitacora=$con->obtenerValor($consulta);
		$cadObj='{"idRegistroBitacora":"'.$idRegistroBitacora.'","idRegistroFormato":"'.$iRegistro.'"}';
		
		$obj=json_decode($cadObj);
		$arrFuncionesEjecucion=array();
		
		$consulta="SELECT funcionEjecucion FROM _429_gridEtapas WHERE idReferencia=".$idPerfilValidacion." AND noEtapa=".$etapaCambio;
		$funcionEjecucion=$con->obtenerValor($consulta);
		if(($funcionEjecucion!="")&&($funcionEjecucion!=-1))
			array_push($arrFuncionesEjecucion,$funcionEjecucion);
		
		
		$listaEtapas="";
		
		$arrRolesCambio=explode(",",$actor);
		foreach($arrRolesCambio as $rActor)
		{
			$arrActorAux=explode('@@',$rActor);
			$consulta="SELECT idRegistro FROM ( 
						 SELECT id__430_tablaDinamica AS idRegistro,etapa,CONCAT(rol,'_0') AS rol FROM _430_tablaDinamica 
						 WHERE idReferencia=".$idPerfilValidacion."
						 ) AS tmp WHERE  etapa=".$etapaCambio." AND rol='".$arrActorAux[0]."'";
			$etapasAux=$con->obtenerListaValores($consulta);
			if($listaEtapas=="")
				$listaEtapas=$etapasAux;
			else
				$listaEtapas.=",".$etapasAux;
		}
		
		if($listaEtapas=="")
			$listaEtapas=-1;
		$consulta="SELECT funcionEjecucion FROM _430_gridFuncionesEjecuta WHERE idReferencia IN(".$listaEtapas.")";
		$rFunciones=$con->obtenerFilas($consulta);
		
		while($fFuncion=mysql_fetch_row($rFunciones))
		{
			array_push($arrFuncionesEjecucion,$fFuncion[0]);
		}
		
		foreach($arrFuncionesEjecucion as $f)
		{
			resolverExpresionCalculoPHP($f,$obj,$cache);
		}
		return true;
	}
	
	return false;
}


function obtenerRolActualDocumento($obj,$idPerfilEvaluacion,$etapaCambio,$idDocumento)
{
	global $con;
	
	
	
	$rolActual="";
	if($obj)
	{
		if(isset($obj->actor))
		{
			$consulta="SELECT actor FROM 944_actoresProcesoEtapa WHERE idActorProcesoEtapa=".$obj->actor;
			$rolActual=$con->obtenerValor($consulta);
			
		}
		else
		{
			if(isset($obj->rol))
			{
				$rolActual=$obj->rol;
			}
			
		}
	}
	
	if($rolActual=="")
	{
		$consulta="";
		if($idDocumento==-1)
		{
			$consulta="select rol from (SELECT concat(e.rol,'_0') as rol,jerarquia FROM _430_tablaDinamica e,_429_gridRoles r WHERE 
								e.idReferencia=".$idPerfilEvaluacion." and e.idReferencia=r.idReferencia 
								 AND etapa=".$etapaCambio." AND permiteEditar=1 and e.rol=r.rol) as tmp where rol in (".$_SESSION["idRol"].
								") order by jerarquia desc";
			$rolActual=$con->obtenerValor($consulta);
		}
		else
		{
			$consulta="SELECT rolCambio,rolActual,idDestinatario FROM 3000_bitacoraFormatos WHERE idRegistroFormato=".$idDocumento.
						" AND idEstadoActual=".$etapaCambio." ORDER BY fechaCambio DESC";
			$fDatosBitacora=$con->obtenerPrimeraFila($consulta);
			
			if($fDatosBitacora[0]==$fDatosBitacora[1])
			{
				
				$consulta="select rol from (SELECT concat(e.rol,'_0') as rol,jerarquia FROM _430_tablaDinamica e,_429_gridRoles r WHERE 
								e.idReferencia=".$idPerfilEvaluacion." and e.idReferencia=r.idReferencia AND etapa=".$etapaCambio.
							" AND permiteEditar=1 and e.rol=r.rol) as tmp 
								where rol in (".$_SESSION["idRol"].") order by jerarquia desc";
				
				$rolActual=$con->obtenerValor($consulta);
			}
			else
			{
				if($fDatosBitacora[2]!=0)
				{
					if($_SESSION["idUsr"]==$fDatosBitacora[2])
						$rolActual=$fDatosBitacora[0];
				}
				else
				{
					if(existeRol("'".$fDatosBitacora[0]."'"))
						$rolActual=$fDatosBitacora[0];
				}
			}
			
		}
		
	}
	
	if($rolActual=="")
		$rolActual="0_0";
	return $rolActual;
	
}

function ejecutarFuncionCambioEtapa($idRegistrBitacora)
{
	global $con;
	
	
	$consulta="SELECT * FROM 3000_bitacoraFormatos WHERE idRegistro=".$idRegistrBitacora;
	$fBitacora=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$consulta="SELECT idPerfilEvaluacion,idRegistro FROM 3000_formatosRegistrados WHERE idRegistroFormato=".$fBitacora["idRegistroFormato"];
	$fDocumento=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$consulta="SELECT * FROM 7035_informacionDocumentos WHERE idRegistro=".$fDocumento["idRegistro"];
	
	$fDocumentoComp=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$tNotificacion="";
	
	$consulta="SELECT unidadGestion FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$fDocumentoComp["carpetaAdministrativa"]."'";
	$unidadGestion=$con->obtenerValor($consulta);
	
	$consulta="SELECT tituloEtapa FROM _429_gridEtapas WHERE idReferencia=".$fDocumento["idPerfilEvaluacion"]." AND noEtapa=".$fBitacora["idEstadoActual"];
	$tNotificacion=$con->obtenerValor($consulta);
	
	$esEtapaFinal=false;
	$esEtapaInicial=false;
	
	$consulta="SELECT etapaFinal,etapaInicial FROM _429_gridEtapas WHERE idReferencia=".$fDocumento["idPerfilEvaluacion"]." AND noEtapa=".$fBitacora["idEstadoActual"];
	$fGEtapa=$con->obtenerPrimeraFila($consulta);

	$esEtapaFinal=$fGEtapa[0];
	$esEtapaInicial=$fGEtapa[1];
	
	$esEtapaInicial=($esEtapaInicial==1);
	$esEtapaFinal=($esEtapaFinal==1);
	
	if($esEtapaFinal)
	{
		$tNotificacion="(".$tNotificacion.") ".$fDocumentoComp["tituloDocumento"];
	}
	
	if($esEtapaInicial)
		return;
	


	if($fBitacora["idDestinatario"]!=0)
	{
		if($fBitacora["idDestinatario"]==-1)
		{
			$arrRolesDestinatarios=explode(",",$fBitacora["rolCambio"]);
			foreach($arrRolesDestinatarios as $rDestinatario)
			{
				$arrDatosRol=explode("@@",$rDestinatario);
				$queryDestinatarios="";
				if($arrDatosRol[1]==0)
				{
					$queryDestinatarios="SELECT ad.idUsuario FROM 801_adscripcion ad,807_usuariosVSRoles r WHERE r.idUsuario=ad.idUsuario AND 
					r.codigoRol='".$arrDatosRol[0]."' AND ad.Institucion='".$unidadGestion."'";

				}
				else
				{
					$queryDestinatarios="SELECT idUsuario FROM 800_usuarios WHERE idUsuario=".$arrDatosRol[1];
				}
				

				
				$res=$con->obtenerFilas($queryDestinatarios);
				while($fila=mysql_fetch_row($res))
				{
					if(!$esEtapaFinal)
						registrarNotificacionDocumento(9,$tNotificacion,$fila[0],$arrDatosRol[0],$fDocumentoComp["carpetaAdministrativa"],$fBitacora["responsableCambio"],$fBitacora["rolActual"],$fDocumento["idRegistro"]);
					else
						registrarNotificacionDocumentoFinal(5,$tNotificacion,$fila[0],$arrDatosRol[0],$fDocumentoComp["carpetaAdministrativa"],$fBitacora["responsableCambio"],$fBitacora["rolActual"],$fDocumento["idRegistro"]);
				}
			}
		}
		else
		{
		
			if(!$esEtapaFinal)
				registrarNotificacionDocumento(9,$tNotificacion,$fBitacora["idDestinatario"],$fBitacora["rolCambio"],$fDocumentoComp["carpetaAdministrativa"],$fBitacora["responsableCambio"],$fBitacora["rolActual"],$fDocumento["idRegistro"]);
			else
				registrarNotificacionDocumentoFinal(5,$tNotificacion,$fBitacora["idDestinatario"],$fBitacora["rolCambio"],$fDocumentoComp["carpetaAdministrativa"],$fBitacora["responsableCambio"],$fBitacora["rolActual"],$fDocumento["idRegistro"]);
		}
	}
	else
	{
		if($fBitacora["rolCambio"]=="56_0")
		{
			ejecutarFuncionCambioEtapaJuezTramite($idRegistrBitacora);
		}
		else
		{
		
			$arrDestinatario=array();
			$consulta="SELECT ad.idUsuario FROM 801_adscripcion ad,807_usuariosVSRoles r WHERE r.idUsuario=ad.idUsuario AND 
						r.codigoRol='".$fBitacora["rolCambio"]."' AND ad.Institucion='".$unidadGestion."'";
	
			
			$res=$con->obtenerFilas($consulta);
			while($fila=mysql_fetch_row($res))
			{
				if(!$esEtapaFinal)
					registrarNotificacionDocumento(9,$tNotificacion,$fila[0],$fBitacora["rolCambio"],$fDocumentoComp["carpetaAdministrativa"],$fBitacora["responsableCambio"],$fBitacora["rolActual"],$fDocumento["idRegistro"]);
				else
					registrarNotificacionDocumentoFinal(5,$tNotificacion,$fila[0],$fBitacora["rolCambio"],$fDocumentoComp["carpetaAdministrativa"],$fBitacora["responsableCambio"],$fBitacora["rolActual"],$fDocumento["idRegistro"]);
			}
		}
	}
}




function ejecutarFuncionCambioEtapaJuezTramite($idRegistrBitacora)
{
	global $con;
	$consulta="SELECT * FROM 3000_bitacoraFormatos WHERE idRegistro=".$idRegistrBitacora;
	$fBitacora=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$tNotificacion="";
	
	$consulta="SELECT idPerfilEvaluacion,idRegistro FROM 3000_formatosRegistrados WHERE idRegistroFormato=".$fBitacora["idRegistroFormato"];
	$fDocumento=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$consulta="SELECT * FROM 7035_informacionDocumentos WHERE idRegistro=".$fDocumento["idRegistro"];
	$fDocumentoComp=$con->obtenerPrimeraFilaAsoc($consulta);
	
	
	$consulta="SELECT unidadGestion FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$fDocumentoComp["carpetaAdministrativa"]."'";
	$unidadGestion=$con->obtenerValor($consulta);
	
	$consulta="SELECT tituloEtapa FROM _429_gridEtapas WHERE idReferencia=".$fDocumento["idPerfilEvaluacion"]." AND noEtapa=".$fBitacora["idEstadoActual"];
	$tNotificacion=$con->obtenerValor($consulta);
	
	$horaActual=date("Y-m-d H:i:s");
	$fechactual=date("Y-m-d");
	
	if($fBitacora["idDestinatario"]!=0)
		registrarNotificacionDocumento(9,$tNotificacion,$fBitacora["idDestinatario"],$fBitacora["rolCambio"],$fDocumentoComp["carpetaAdministrativa"],$fBitacora["responsableCambio"],$fBitacora["rolActual"],$fDocumento["idRegistro"]);
	else
	{
		$arrJueces=array();
		$idProcesoPadre=obtenerIdProcesoFormulario($fDocumentoComp["idFormulario"]);
		
		if(($idProcesoPadre!=-1)&&($idProcesoPadre!=""))
		{
			$consulta="SELECT idUsuarioDestinatario FROM _578_tablaDinamica WHERE idProcesoPadre=".$idProcesoPadre.
					" AND idReferencia=".$fDocumentoComp["idReferencia"]." order by id__578_tablaDinamica desc";
			$idUsuarioDestinatario=$con->obtenerValor($consulta);
			if($idUsuarioDestinatario!="")
			{
				array_push($arrJueces,$idUsuarioDestinatario);
			}
		}
		
		if(sizeof($arrJueces)==0)
		{
			$consulta="SELECT id__17_tablaDinamica FROM _17_tablaDinamica WHERE claveUnidad='".$unidadGestion."'";
			$idUGJ=$con->obtenerValor($consulta);
			$consulta="SELECT j.usuarioJuez FROM _26_tablaDinamica j,_292_tablaDinamica jt WHERE jt.idEstado=1 and jt.nombreJueces=j.usuarioJuez
						and j.idReferencia=".$idUGJ." and '".$fechactual."'>=fechaInicial and '".$fechactual."'<=fechaFinal and
						jt.idEstado=1";
			$res=$con->obtenerFilas($consulta);
			while($fila=mysql_fetch_row($res))
			{
				array_push($arrJueces,$fila[0]);
				
			}
			
			
			
			if($fDocumentoComp["idFormulario"]==460)
			{
				$query="SELECT * FROM _460_tablaDinamica WHERE id__460_tablaDinamica=".$fDocumentoComp["idReferencia"];	
				$fAmparoPromocion=$con->obtenerPrimeraFilaAsoc($query);
				
				if($fAmparoPromocion["resolverAmparoTransitorio"]==1)
				{
					$query="SELECT * FROM _536_tablaDinamica WHERE idReferencia=".$fDocumentoComp["idReferencia"];
					$fResolucionAmparo=	$con->obtenerPrimeraFilaAsoc($query);
					if($fResolucionAmparo["existeJuezConoce"]==1)
					{
						$query="SELECT rj.usuarioJuez FROM _26_tablaDinamica rj WHERE 
								rj.id__26_tablaDinamica=".$fResolucionAmparo["juezConoce"]." and
							rj.usuarioJuez<>-1 AND rj.usuarioJuez IS NOT NULL";
						$rJuezAmparo=$con->obtenerFilas($query);
						if($con->filasAfectadas>0)
						{
							$arrJuecesAux=array();
							while($filaJuez=mysql_fetch_row($rJuezAmparo))
							{
								if(esJuezDisponibleIncidencia($filaJuez[0],$fechactual))
									array_push($arrJuecesAux,$filaJuez[0]);
								
							}
							
							if(sizeof($arrJuecesAux)>0)
							{
								$arrJueces=$arrJuecesAux;
							}
						}
					}
					
					
				}
				else
				{
					$query="SELECT rj.usuarioJuez FROM _460_juezReferido j,_26_tablaDinamica rj WHERE 
							idPadre=".$fDocumentoComp["idReferencia"]." AND rj.id__26_tablaDinamica=j.idOpcion AND 
							rj.usuarioJuez<>-1 AND rj.usuarioJuez IS NOT NULL";
					$rJuezAmparo=$con->obtenerFilas($query);
					if($con->filasAfectadas>0)
					{
						$arrJuecesAux=array();
						while($filaJuez=mysql_fetch_row($rJuezAmparo))
						{
							if(esJuezDisponibleIncidencia($filaJuez[0],$fechactual))
									array_push($arrJuecesAux,$filaJuez[0]);
							
						}
						if(sizeof($arrJuecesAux)>0)
						{
							$arrJueces=$arrJuecesAux;
						}
					}
				
					
				}
				
			}
		}
	
		
		
		foreach($arrJueces as $juez)
		{
			registrarNotificacionDocumento(9,$tNotificacion,$juez,$fBitacora["rolCambio"],$fDocumentoComp["carpetaAdministrativa"],$fBitacora["responsableCambio"],$fBitacora["rolActual"],$fDocumento["idRegistro"]);
			
		}
		
	}
}

function registrarNotificacionDocumento($idTablero,$tNotificacion,$idUsuarioDestinatario,$actor,$carpetaAdministrativa,$idUsuarioRemitente,$rolRemitente,$idRegistro)
{
	global $con;
	$arrValores=array();
	
	
	$consulta="SELECT idCarpeta FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$carpetaAdministrativa."'";
	$idCarpetaAdministrativa=$con->obtenerValor($consulta);
	
	
	$consulta="SELECT tipoDocumento,idFormulario,idReferencia,tituloDocumento FROM 7035_informacionDocumentos WHERE idRegistro=".$idRegistro;
	$fRegistroDocumento=$con->obtenerPrimeraFilaAsoc($consulta);
	$consulta="SELECT idRegistroFormato FROM 3000_formatosRegistrados WHERE idFormulario=-2 AND idRegistro=".$idRegistro;
	$idRegistroFormato=$con->obtenerValor($consulta);
	$rolActor=obtenerTituloRol($actor);
	$nombreUsuario=obtenerNombreUsuario($idUsuarioDestinatario)." (".$rolActor.")";
	$arrValores["fechaAsignacion"]=date("Y-m-d H:i:s");
	$arrValores["tipoNotificacion"]=$tNotificacion;
	$arrValores["usuarioRemitente"]=obtenerNombreUsuario($idUsuarioRemitente)." (".obtenerTituloRol($rolRemitente).")";
	$arrValores["idUsuarioRemitente"]=$idUsuarioRemitente;
	$arrValores["usuarioDestinatario"]=str_replace(" (Suplantado)","",$nombreUsuario);
	$arrValores["idUsuarioDestinatario"]=$idUsuarioDestinatario;
	$arrValores["nombreDocumento"]=$fRegistroDocumento["tituloDocumento"];
	$arrValores["tipoDocumento"]=$fRegistroDocumento["tipoDocumento"];
	$arrValores["idEstado"]="1";
	$arrValores["contenidoMensaje"]="";
	$arrValores["objConfiguracion"]='{"tipoDocumento":"'.$fRegistroDocumento["tipoDocumento"].'","idFormulario":"'.$fRegistroDocumento["idFormulario"].'","idRegistro":"'.
									$fRegistroDocumento["idReferencia"].'","idRegistroFormato":"'.$idRegistroFormato.'","actorAccesoProceso":"'.$actor.
									'","funcionApertura":"window.parent.parent.mostrarVentanaAperturaDocumento","idRegistroInformacionDocumento":"'.$idRegistro.'"}';
	$arrValores["permiteAbrirProceso"]="1";
	$arrValores["idNotificacion"]="0";
	$arrValores["carpetaJudicial"]=$carpetaAdministrativa;
	$arrValores["tipoTarea"]=$tNotificacion;
	$arrValores["iFormulario"]=$fRegistroDocumento["idFormulario"];
	$arrValores["iRegistro"]=$fRegistroDocumento["idReferencia"];
	$arrValores["iReferencia"]=-1;
	$consulta="SELECT Institucion FROM 801_adscripcion WHERE idUsuario=".$idUsuarioDestinatario;
	$codigoUnidad=$con->obtenerValor($consulta);
	
	$arrValores["codigoUnidad"]=$codigoUnidad;
	
	
	
	$consulta="";
	$camposInsert="";
	$camposValues="";
	foreach($arrValores as $campo=>$valor)
	{
		if($camposInsert=="")
			$camposInsert=$campo;
		else
			$camposInsert.=",".$campo;

		if($camposValues=="")
			$camposValues=($valor==""?"NULL":"'".cv($valor)."'");
		else
			$camposValues.=",".($valor==""?"NULL":"'".cv($valor)."'");
	}

	$x=0;
	$query=array();
	$query[$x]="begin";
	$x++;
	$query[$x]="insert into 9060_tableroControl_".$idTablero."(".$camposInsert.") values(".$camposValues.")";
	$x++;
	$query[$x]="set @idTarea:=(select last_insert_id())";
	$x++;
	$query[$x]="INSERT INTO 3000_documentosAsignadosAtencion(idDocumentoFormato,situacionActual,fechaAsignacion,idCarpetaAdministrativa,
				iFormulario,iReferencia,idResponsableAtencion,actor,idInformacionDocumento,comentariosAdicionales,idTareaAsociada,idTableroTarea)
				values(".$idRegistroFormato.",0,'".$arrValores["fechaAsignacion"]."',".$idCarpetaAdministrativa.",".$fRegistroDocumento["idFormulario"].
				",".$fRegistroDocumento["idReferencia"].",".$arrValores["idUsuarioDestinatario"].",'".$actor."',".$idRegistro.",'',@idTarea,".$idTablero.")";
	$x++;
	$query[$x]="commit";
	$x++;
	
	
	return $con->ejecutarBloque($query);
}
	
function registrarNotificacionDocumentoFinal($idTablero,$tNotificacion,$idUsuarioDestinatario,$actor,$carpetaAdministrativa,$idUsuarioRemitente,$rolRemitente,$idRegistro)
{
	global $con;
	$arrValores=array();
	
	$consulta="SELECT idCarpeta FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$carpetaAdministrativa."'";
	$idCarpetaAdministrativa=$con->obtenerValor($consulta);
	
	$consulta="SELECT tipoDocumento,idFormulario,idReferencia,tituloDocumento FROM 7035_informacionDocumentos WHERE idRegistro=".$idRegistro;
	$fRegistroDocumento=$con->obtenerPrimeraFilaAsoc($consulta);

	$consulta="SELECT idRegistroFormato,idDocumento FROM 3000_formatosRegistrados WHERE idFormulario=-2 AND idRegistro=".$idRegistro;
	$fRegistroD=$con->obtenerPrimeraFila($consulta);
	
	$idRegistroFormato=$fRegistroD[0];
	$rolActor=obtenerTituloRol($actor);
	$nombreUsuario=obtenerNombreUsuario($idUsuarioDestinatario)." (".$rolActor.")";
	$arrValores["fechaAsignacion"]=date("Y-m-d H:i:s");
	$arrValores["tipoNotificacion"]=$tNotificacion;
	$arrValores["usuarioRemitente"]=obtenerNombreUsuario($idUsuarioRemitente)." (".obtenerTituloRol($rolRemitente).")";
	$arrValores["idUsuarioRemitente"]=$idUsuarioRemitente;
	$arrValores["usuarioDestinatario"]=str_replace(" (Suplantado)","",$nombreUsuario);
	$arrValores["idUsuarioDestinatario"]=$idUsuarioDestinatario;
	
	$arrValores["idEstado"]="1";
	$arrValores["contenidoMensaje"]="";
	$arrValores["objConfiguracion"]='{"idDocumentoFinal":"'.$fRegistroD[1].'","idFormulario":"'.$fRegistroDocumento["idFormulario"].'","idRegistro":"'.
									$fRegistroDocumento["idReferencia"].'","idRegistroFormato":"'.$idRegistroFormato.'","actorAccesoProceso":"'.$actor.
									'","funcionApertura":"window.parent.parent.mostrarVentanaDocumentoAutorizado","idRegistroInformacionDocumento":"'.$idRegistro.'"}';
	$arrValores["permiteAbrirProceso"]="1";
	$arrValores["idNotificacion"]="0";
	$arrValores["numeroCarpetaAdministrativa"]=$carpetaAdministrativa;
	$arrValores["iFormulario"]=$fRegistroDocumento["idFormulario"];
	$arrValores["iRegistro"]=$fRegistroDocumento["idReferencia"];
	$arrValores["iReferencia"]=-1;
	$consulta="SELECT Institucion FROM 801_adscripcion WHERE idUsuario=".$idUsuarioDestinatario;
	$codigoUnidad=$con->obtenerValor($consulta);
	
	$arrValores["codigoUnidad"]=$codigoUnidad;
	
	
	
	$consulta="";
	$camposInsert="";
	$camposValues="";
	foreach($arrValores as $campo=>$valor)
	{
		if($camposInsert=="")
			$camposInsert=$campo;
		else
			$camposInsert.=",".$campo;

		if($camposValues=="")
			$camposValues=($valor==""?"NULL":"'".cv($valor)."'");
		else
			$camposValues.=",".($valor==""?"NULL":"'".cv($valor)."'");
	}


	
	$x=0;
	$query=array();
	$query[$x]="begin";
	$x++;
	$query[$x]="insert into 9060_tableroControl_".$idTablero."(".$camposInsert.") values(".$camposValues.")";
	$x++;
	/*$query[$x]="INSERT INTO 3000_documentosAsignadosAtencion(idDocumentoFormato,situacionActual,fechaAsignacion,idCarpetaAdministrativa,
				iFormulario,iReferencia,idResponsableAtencion,actor,idInformacionDocumento,comentariosAdicionales)
				values(".$idRegistroFormato.",0,'".$arrValores["fechaAsignacion"]."',".$idCarpetaAdministrativa.",".$fRegistroDocumento["idFormulario"].
				",".$fRegistroDocumento["idReferencia"].",".$arrValores["idUsuarioDestinatario"].",'".$actor."',".$idRegistro.",'')";
	$x++;*/
	$query[$x]="commit";
	$x++;
	
	
	return $con->ejecutarBloque($query);
	
	
}

function llenarFormatoTrasladoImputado($idDocumento,$idReferencia,$idRegistro,$idFormulario)
{
	global $con;
	global $leyendaTribunal;
	$consulta="SELECT idEvento,imputado,reclusorios,carpetaAdministrativa FROM _293_tablaDinamica WHERE id__293_tablaDinamica=".$idRegistro;
	$fDatosTraslado=$con->obtenerPrimeraFila($consulta);
	
	$consulta="SELECT horaInicioEvento,idEdificio,idCentroGestion,tipoAudiencia FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$fDatosTraslado[0];
	$fEvento=$con->obtenerPrimeraFila($consulta);
	
	$arrFechaActual=convertirFechaLeyenda(date("Y-m-d"));
	$arrValores=array();
	
	$arrValores["leyendaTribunal"]=($leyendaTribunal);
	$arrValores["dia"]=$arrFechaActual["dia"];
	$arrValores["mes"]=$arrFechaActual["mes"];
	$arrValores["anio"]=$arrFechaActual["anio"];
	$arrValores["carpetaJudicial"]=$fDatosTraslado[3];
	
	$consulta="SELECT UPPER(CONCAT(IF(nombre IS NULL,'',nombre),' ',IF(apellidoPaterno IS NULL,'',apellidoPaterno),' ',IF(apellidoMaterno IS NULL,'',apellidoMaterno)))
				 FROM _47_tablaDinamica WHERE id__47_tablaDinamica IN(".$fDatosTraslado[1].")";
	$lImputado=$con->obtenerListaValores($consulta);
	
	$arrValores["imputado"]=$lImputado;	
	
	$carpetaBase=obtenerCarpetaBaseOriginal($fDatosTraslado[3]);
	$consulta="SELECT upper(folioCarpetaInvestigacion) FROM _46_tablaDinamica WHERE carpetaAdministrativa='".$carpetaBase."'";
	
	$arrValores["carpetaInvestigacion"]=$con->obtenerValor($consulta);
	
	$arrValores["horaAudiencia"]=date("H:i",strtotime($fEvento[0]));
	$arrFechaEvento=convertirFechaLeyenda($fEvento[0]);
	$arrValores["diaAudiencia"]=$arrFechaEvento["dia"];
	$arrValores["mesAudiencia"]=$arrFechaEvento["mes"];
	$arrValores["anioAudiencia"]=$arrFechaEvento["anio"];
	$consulta="SELECT upper(direccion) FROM _1_tablaDinamica WHERE id__1_tablaDinamica=".$fEvento[1];
	$arrValores["domicilioUGA"]=$con->obtenerValor($consulta);
	
	$consulta="SELECT upper(tipoAudiencia) FROM _4_tablaDinamica WHERE id__4_tablaDinamica=".$fEvento[3];
	$tAudiencia=$con->obtenerValor($consulta);
	if(strpos($tAudiencia,"Audiencia")===false)
		$tAudiencia="Audiencia de ".$tAudiencia;
	
	
	
	$arrValores["audiencia"]=$tAudiencia;
	
	$consulta="SELECT UPPER(tituloUnidad) FROM _17_tablaDinamica WHERE id__17_tablaDinamica=".$fEvento[2];
	$arrValores["noUGA"]=$con->obtenerValor($consulta);
	
	
	
	
	return $arrValores;
}

function convertirFechaLeyenda($fecha)
{
	global $arrMesLetra;
	global $arrDiasSemana;
	$arrFecha="";
	
	$fecha=strtotime($fecha);
	$arrFecha["dia"]=date("d",$fecha);
	$arrFecha["diaLetra"]=$arrDiasSemana[date("w",$fecha)];
	$arrFecha["mes"]=mb_strtoupper($arrMesLetra[(date("m",$fecha)*1)-1]);
	$arrFecha["anio"]=date("Y",$fecha);
	
	return $arrFecha;
}

function obtenerTipoDocumentoSolicitudTraslado($idRegistro)
{
	global $con;
	$consulta="SELECT idEvento,imputado,reclusorios,carpetaAdministrativa FROM _293_tablaDinamica WHERE id__293_tablaDinamica=".$idRegistro;
	$fDatosTraslado=$con->obtenerPrimeraFila($consulta);
	
	$consulta="SELECT tipoCarpetaAdministrativa FROM  7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$fDatosTraslado[3]."'";
	$tCarpetaAdministrativa=$con->obtenerValor($consulta);
	if($tCarpetaAdministrativa==6)
		return 349;
	return 350;
}

function domicilioSeEncuentraDestinatarioNoCitatorio($resultado,$citatorio)
{
	if(($resultado==1)&&($citatorio==0))
	{
		return 1;
	}
	return 0;
}

function domicilioNoSeEncuentraDestinatarioSiCitatorio($resultado,$citatorio)
{
	if(($resultado==1.5)&&($citatorio==1))
	{
		return 1;
	}
	return 0;
}

function domicilioSiSeEncuentraDestinatarioSiCitatorio($resultado,$citatorio)
{
	if(($resultado==1)&&($citatorio==1))
	{
		return 1;
	}
	return 0;
}

function domicilioNoSeEncuentraNadieSiCitatorio($resultado,$citatorio)
{
	if($resultado==3)
	{
		return 1;
	}
	return 0;
}

function generarDocumentoPDFFormatoFirmaElectronica($idRegistroFormato,$descomponerDocumentoMarcadores=true,$tipoFirma,$archivoCER,$archivoKEY,$passwd,$documentosAnexos="",$bloquearDocumento=1)
{
	global $con;
	global $baseDir;
	global $comandoLibreOffice;
	global $URLServidorFirma;
	global $nombreFuncionFirma;
	global $llaveFirmado;
	global $utilizarServidorQR;
	global $respaldarDocumentoPrevioFirma;
	
	$arrExtensionesImagen["jpg"]=1;
	$arrExtensionesImagen["jpeg"]=1;
	$arrExtensionesImagen["png"]=1;
	$arrExtensionesImagen["gif"]=1;

	$objResultado=json_decode('{"resultado":"","mensaje":""}');
	
	
	$conversorPDF=-1;
	$consulta="SELECT cuerpoFormato,cadenaFirma,documentoBloqueado,tipoFormato,idFormulario,idRegistro FROM 3000_formatosRegistrados 
			WHERE idRegistroFormato=".$idRegistroFormato;
	
	$fDocumento=$con->obtenerPrimeraFila($consulta);
	$iFormulario=$fDocumento[4];
	$iRegistro=$fDocumento[5];
	
	
	if($iFormulario==-2)
	{
		$consulta="SELECT idFormulario,idReferencia,tipoDocumento,tituloDocumento FROM 7035_informacionDocumentos WHERE idRegistro=".$iRegistro;
		$fDatosInformacion=$con->obtenerPrimeraFila($consulta);
		
		$iFormulario=$fDatosInformacion[0];
		$iRegistro=$fDatosInformacion[1];
	
		
	}
	
	if($fDocumento[2]==1)
	{
		$objResultado->resultado=true;
		
		return $objResultado;
	}
	
	$cuerpoFormato=bD($fDocumento[0]);
	
	$nombreArchivo=rand()."_".date("dmY_Hms");
	
	$archivoTemporal=$baseDir."/archivosTemporales/".$nombreArchivo.".html";
	if($conversorPDF==-1)
	{
		$consulta="SELECT metodoConversionPDF FROM _10_tablaDinamica WHERE id__10_tablaDinamica=".$fDocumento[3];
		$conversorPDF=$con->obtenerValor($consulta);
		if($conversorPDF=="")
			$conversorPDF=1;
	}
	
	if($conversorPDF==2)
	{
		$cuerpoFormato=prepararFormatoImpresionWord($cuerpoFormato);
	}

	if(escribirContenidoArchivo($archivoTemporal,$cuerpoFormato))
	{
		
		$directorioDestino=$baseDir."/archivosTemporales/";
		
		if($conversorPDF==-1)
		{
			$consulta="SELECT metodoConversionPDF FROM _10_tablaDinamica WHERE id__10_tablaDinamica=".$fDocumento[3];
			$conversorPDF=$con->obtenerValor($consulta);
			if($conversorPDF=="")
				$conversorPDF=1;
		}
		

		switch($conversorPDF)
		{
			case 2:
				generarDocumentoPDF($archivoTemporal,false,false,true,"","MS_OFFICE",$directorioDestino);
			break;
			default:
				generarDocumentoPDF($archivoTemporal,false,false,false,"",$comandoLibreOffice,$directorioDestino);
			break;
		}
		
				
		
		if(file_exists($directorioDestino."/".$nombreArchivo.".pdf"))
		{
			
			if($documentosAnexos!="")
			{
				$arrRutasEliminar=array();
				$merge = new PDFMerger();
				$merge->addPDF($directorioDestino."/".$nombreArchivo.".pdf");
				$arrDocumentosAnexos=explode(",",$documentosAnexos);
				foreach($arrDocumentosAnexos as $d)
				{
					$consulta="SELECT LOWER(nomArchivoOriginal) FROM 908_archivos WHERE idArchivo=".$d;
					$nArchivo=$con->obtenerValor($consulta);
					
					$aDocumentoAnexo=explode(".",$nArchivo);
					$rutaDocumentoAnexo=obtenerRutaDocumento($d);
					if($aDocumentoAnexo[sizeof($aDocumentoAnexo)-1]=='pdf')
					{
						$merge->addPDF($rutaDocumentoAnexo);
						
					}
					else
					{
						if(isset($arrExtensionesImagen[$aDocumentoAnexo[sizeof($aDocumentoAnexo)-1]]))
						{
							$rutaDocumentoAnexoTmp=$directorioDestino."/".rand()."_".date("dmY_Hms");
							
							$rutaImagen=$rutaDocumentoAnexoTmp.".".$aDocumentoAnexo[sizeof($aDocumentoAnexo)-1];
							$rutaPdf=$rutaDocumentoAnexoTmp.".pdf";
							
							copy($rutaDocumentoAnexo,$rutaImagen);
							array_push($arrRutasEliminar,$rutaImagen);
							array_push($arrRutasEliminar,$rutaPdf);
							$arrDatosImagen=getimagesize($rutaImagen);
							$pdf = new FPDF();
							$pdf->AddPage();							
							$pdf->image($rutaImagen,0,0);							
							$pdf->Output($rutaPdf,'F');
							$merge->addPDF($rutaPdf);
						}
					}
					
					
					
					
				}
				

				$merge->merge("file",$directorioDestino."/".$nombreArchivo.".pdf");
				foreach($arrRutasEliminar as $d)
				{
					if(file_exists($d))
					{
						unlink($d);
					}
				}
			}
			
			rename($directorioDestino."/".$nombreArchivo.".pdf",$directorioDestino."/".$nombreArchivo);
			
			$actualizarFormato=true;
			if($descomponerDocumentoMarcadores)
			{
				$actualizarFormato=descomponerDocumentoMarcadores($idRegistroFormato,$cuerpoFormato);
			}
			$consulta="SELECT nombreFormato,categoriaDocumento FROM _10_tablaDinamica WHERE id__10_tablaDinamica=".$fDocumento[3];
			$fDocumentoFinal=$con->obtenerPrimeraFila($consulta);
			if(!$fDocumentoFinal)
			{
				$fDocumentoFinal[0]="Documento General";
				$fDocumentoFinal[1]=0;
			}
			$nombreArchivoPDF=$fDocumentoFinal[0];
			$nombreArchivoPDF.=".pdf";
			
			
			$tipoCertificado=$tipoFirma;
			$cuerpoDocumento=bE(leerContenidoArchivo($directorioDestino."/".$nombreArchivo));
			$cuerpoDocumentoSinFirma=$cuerpoDocumento;
			
			
			if($utilizarServidorQR && ($bloquearDocumento==1))
			{
				$pdf = new FPDI();

				$pdf->setSourceFile($directorioDestino."/".$nombreArchivo); 
				// import page 1 
				$tplIdx = $pdf->importPage(1); 
				$arrTamano=$pdf->getTemplateSize($tplIdx);
				$pdf->Close();
				
				$consulta="SELECT unidad FROM 817_organigrama WHERE codigoUnidad='".$_SESSION["codigoInstitucion"]."'";
				$areaEmisora=$con->obtenerValor($consulta);
				
				$consulta="SELECT Nombre FROM 800_usuarios WHERE idUsuario=".$_SESSION["idUsr"];
				$usuarioEmisor=$con->obtenerValor($consulta);
				
				$objDocumentoPDF=array();
				$objDocumentoPDF["areaEmisora"]=$areaEmisora;
				$objDocumentoPDF["usuarioEmisor"]=$usuarioEmisor;
				$objDocumentoPDF["documentoPDF"]=$cuerpoDocumento;
				$objDocumentoPDF["nombreDocumento"]=$nombreArchivoPDF;
				$objDocumentoPDF["fechaDocumento"]=date("Y-m-d");
				$objDocumentoPDF["posX"]=182;
				$factorEscala=(0.94779819-((342.9-$arrTamano["h"])*0.00042543));
				$objDocumentoPDF["posY"]=$arrTamano["h"]*$factorEscala;
				$respuestaResultado=generarCodigoQRPDF($objDocumentoPDF);
				if(!$respuestaResultado)
				{
					$objResultado->resultado=false;
					$objResultado->mensaje=bE("NO se ha podido generar el c&oacute;digo QR del documento");
					return $objResultado;
				}
				else
				{
					if($respuestaResultado["estatus"]==1)
					{
						$folioQR=$respuestaResultado["n_documento"];
						$cuerpoDocumento=$respuestaResultado["pdfSellado"];
					}
					else
					{
						$objResultado->resultado=false;
						$objResultado->mensaje=bE($respuestaResultado["mensaje"]);
						return $objResultado;
						
					}
				}
			}
			
			
			$client = new nusoap_client($URLServidorFirma."?wsdl","wsdl");

			$parametros=array();
			$parametros["tipoCertificado"]=$tipoCertificado;
			$parametros["documentoDestino"]=$nombreArchivo.".pdf";
			$parametros["contenidoDocument"]=$cuerpoDocumento;
			$parametros["contenidoCer"]=$archivoCER;
			$parametros["contenidoKey"]=$archivoKEY;
			$parametros["passwd"]=$passwd;
			$parametros["llaveFirmado"]=$llaveFirmado;
			unlink($directorioDestino."/".$nombreArchivo);
			$response = $client->call($nombreFuncionFirma, $parametros);
			$oResp=json_decode($response[$nombreFuncionFirma."Result"]);
			
			if($oResp->resultado==1)
			{
				$r1=escribirContenidoArchivo($directorioDestino."/".$nombreArchivo,bD($oResp->documento));
				$r2=escribirContenidoArchivo($directorioDestino."/".$nombreArchivo.".pkcs7",bD($oResp->PKCS7));
				
				if($r1 && $r2)
				{
					
					
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
								registrarDocumentoCarpetaAdministrativa($carpetaAdministrativa,$idRegistro,$iFormulario,$iRegistro);
							}
							registrarDocumentoResultadoProceso($iFormulario,$iRegistro,$idRegistro);
						}
						$consulta="update 3000_formatosRegistrados set formatoPDF=1,documentoBloqueado=".$bloquearDocumento.
									",idDocumento=".$idDocumento.",idDocumentoAdjunto=".$idDocumento." where idRegistroFormato=".$idRegistroFormato;
						$con->ejecutarConsulta($consulta);

						$objResultado->resultado=true;					
						return $objResultado;
					}
				}
				else
				{
					$objResultado->resultado=false;
					$objResultado->mensaje=bE("No se han podido guardar los documentos firmados");
					return $objResultado;
				}
		
			}
			else
			{
				$objResultado->resultado=false;
				$objResultado->mensaje=$oResp->mensaje;
				return $objResultado;
			}
		}
		else
		{
			$objResultado->resultado=false;
			$objResultado->mensaje=bE("No se ha podido generar la versión PDF del documento");
			return $objResultado;
		}
	}
	
	
	
	$objResultado->resultado=false;
		
	return $objResultado;
	
	
	
}


function esRegistroCJF($idFormulario,$idReferencia,$actor)
{
	global $con;
	$neun="";
	if(esSubdirectorCausasSimilar($actor)==1)
	{
	
		$consulta="SELECT idReferencia FROM _460_tablaDinamica WHERE id__460_tablaDinamica=".$idReferencia;
	
		$idRegistro=$con->obtenerValor($consulta);
		$consulta="SELECT neunCJF FROM _346_tablaDinamica WHERE id__346_tablaDinamica=".$idRegistro." AND neunCJF<>'N/E'";
		$neun=$con->obtenerValor($consulta);
	}
	
	return $neun==""?"0":"1";
	
}

function registrarNotificacionOrdenNotificacion($idTablero,$tNotificacion,$idUsuarioDestinatario,$actor,$carpetaAdministrativa,$idUsuarioRemitente,$rolRemitente,$idRegistro)
{
	global $con;
	$arrValores=array();
	
	$rolActor=obtenerTituloRol($actor);
	$nombreUsuario=obtenerNombreUsuario($idUsuarioDestinatario)." (".$rolActor.")";
	$arrValores["fechaAsignacion"]=date("Y-m-d H:i:s");
	$arrValores["fechaRegistroSistema"]=$arrValores["fechaAsignacion"];
	$arrValores["tipoNotificacion"]=$tNotificacion;
	$arrValores["usuarioRemitente"]=obtenerNombreUsuario($idUsuarioRemitente).($rolRemitente!=""?" (".obtenerTituloRol($rolRemitente).")":"");
	$arrValores["idUsuarioRemitente"]=$idUsuarioRemitente;
	$arrValores["usuarioDestinatario"]=str_replace(" (Suplantado)","",$nombreUsuario);
	$arrValores["idUsuarioDestinatario"]=$idUsuarioDestinatario;
	
	$arrValores["idEstado"]="1";
	$arrValores["contenidoMensaje"]="";
	$arrValores["objConfiguracion"]='{"idOrden":"'.$idRegistro.'","actorAccesoProceso":"'.$actor.
									'","funcionApertura":"window.parent.parent.mostrarVentanaAperturaNotificacionJUD"}';
	$arrValores["permiteAbrirProceso"]="1";
	$arrValores["idNotificacion"]="0";
	$arrValores["numeroCarpetaAdministrativa"]=$carpetaAdministrativa;
	$arrValores["iFormulario"]=-7042;
	$arrValores["iRegistro"]=$idRegistro;
	$arrValores["iReferencia"]=-1;
	$consulta="SELECT Institucion FROM 801_adscripcion WHERE idUsuario=".$idUsuarioDestinatario;
	$codigoUnidad=$con->obtenerValor($consulta);
	
	$arrValores["codigoUnidad"]=$codigoUnidad;
	
	
	
	$consulta="";
	$camposInsert="";
	$camposValues="";
	foreach($arrValores as $campo=>$valor)
	{
		if($camposInsert=="")
			$camposInsert=$campo;
		else
			$camposInsert.=",".$campo;

		if($camposValues=="")
			$camposValues=($valor==""?"NULL":"'".cv($valor)."'");
		else
			$camposValues.=",".($valor==""?"NULL":"'".cv($valor)."'");
	}

	$consulta="insert into 9060_tableroControl_".$idTablero."(".$camposInsert.") values(".$camposValues.")";
	
	return $con->ejecutarConsulta($consulta);
}

function registrarNotificacionOrdenNotificacionNotificador($idTablero,$tNotificacion,$idUsuarioDestinatario,$actor,$carpetaAdministrativa,$idUsuarioRemitente,$rolRemitente,$idRegistro)
{
	global $con;
	$arrValores=array();
	$nombreTablaBase="9060_tableroControl_".$idTablero;
	$rolActor=obtenerTituloRol($actor);
	$nombreUsuario=obtenerNombreUsuario($idUsuarioDestinatario)." (".$rolActor.")";
	$arrValores["fechaAsignacion"]=date("Y-m-d H:i:s");
	
	if($con->existeCampo("fechaRegistroSistema",$nombreTablaBase))
		$arrValores["fechaRegistroSistema"]=$arrValores["fechaAsignacion"];
		
	$arrValores["tipoNotificacion"]=$tNotificacion;
	$arrValores["usuarioRemitente"]=obtenerNombreUsuario($idUsuarioRemitente).($rolRemitente!=""?" (".obtenerTituloRol($rolRemitente).")":"");
	$arrValores["idUsuarioRemitente"]=$idUsuarioRemitente;
	$arrValores["usuarioDestinatario"]=str_replace(" (Suplantado)","",$nombreUsuario);
	$arrValores["idUsuarioDestinatario"]=$idUsuarioDestinatario;
	
	$arrValores["idEstado"]="1";
	$arrValores["contenidoMensaje"]="";
	$arrValores["objConfiguracion"]='{"idOrden":"'.$idRegistro.'","actorAccesoProceso":"'.$actor.
									'","funcionApertura":"window.parent.parent.mostrarVentanaAtencionOrdenNotificacionNotificador"}';
	$arrValores["permiteAbrirProceso"]="1";
	$arrValores["idNotificacion"]="0";
	$arrValores["numeroCarpetaAdministrativa"]=$carpetaAdministrativa;
	$arrValores["iFormulario"]=-7042;
	$arrValores["iRegistro"]=$idRegistro;
	$arrValores["iReferencia"]=-1;
	$consulta="SELECT Institucion FROM 801_adscripcion WHERE idUsuario=".$idUsuarioDestinatario;
	$codigoUnidad=$con->obtenerValor($consulta);
	
	$arrValores["codigoUnidad"]=$codigoUnidad;
	
	
	
	$consulta="";
	$camposInsert="";
	$camposValues="";
	foreach($arrValores as $campo=>$valor)
	{
		if($camposInsert=="")
			$camposInsert=$campo;
		else
			$camposInsert.=",".$campo;

		if($camposValues=="")
			$camposValues=($valor==""?"NULL":"'".cv($valor)."'");
		else
			$camposValues.=",".($valor==""?"NULL":"'".cv($valor)."'");
	}

	$consulta="insert into 9060_tableroControl_".$idTablero."(".$camposInsert.") values(".$camposValues.")";
	
	return $con->ejecutarConsulta($consulta);
}

//-----
function funcionFundamento_318($tipoDiligencia,$idDetalleParteProcesal,$carpetaAdministrativa) //esCitacion
{
	if($tipoDiligencia==2)
	{
		return true;
	}
	return false;
	
}

function funcionFundamento_319($tipoDiligencia,$idDetalleParteProcesal,$carpetaAdministrativa) //esNotificacion
{
	if($tipoDiligencia==1)
	{
		return true;
	}
	return false;
	
}

function funcionFundamento_314($tipoDiligencia,$idDetalleParteProcesal,$carpetaAdministrativa)//esNotificacion y citación
{
	if($tipoDiligencia==3)
	{
		return true;
	}
	return false;
	
}

function funcionFundamento_315($tipoDiligencia,$idDetalleParteProcesal,$carpetaAdministrativa) //esNotificacion ejecución
{
	global $con;
	$consulta="SELECT tipoCarpetaAdministrativa FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$carpetaAdministrativa."'";
	$tCarpetaAdministrativa=$con->obtenerValor($consulta);
	if($tCarpetaAdministrativa!=6)
		return false;
	return true;
	
}

function funcionFundamento_316($tipoDiligencia,$idDetalleParteProcesal,$carpetaAdministrativa) //esNotificacionPrivada
{
	if($idDetalleParteProcesal==2)
	{
		return true;
	}
	return false;
	
}

function funcionFundamento_317($tipoDiligencia,$idDetalleParteProcesal,$carpetaAdministrativa) //esNotificacionYCitacionEjecucion
{
	if($tipoDiligencia==3)
	{
		return funcionFundamento_315($tipoDiligencia,$idDetalleParteProcesal,$carpetaAdministrativa);

	}
	return false;
	
}

function funcionFundamento_320($tipoDiligencia,$idDetalleParteProcesal,$carpetaAdministrativa) //esCitacionEjecucion
{
	global $con;
	$consulta="SELECT tipoDiligencia,idParteProcesal,idDetalleParteProcesal FROM 7029_diligenciaActaNotificacion WHERE idRegistro=".$idDiligencia;
	$fRegistro=$con->obtenerPrimeraFila($consulta);
	
	if($tipoDiligencia==2)
	{
		return funcionFundamento_315($tipoDiligencia,$idDetalleParteProcesal,$carpetaAdministrativa)==1;
	}
	return false;
	
}


//---

function funcionResultado_305($resultadoDiligencia) //domicilioSeEncuentraDestinatarioNoCitatorio
{
	if($resultadoDiligencia==1)
	{
		return 1;
	}
	return 0;
}

function funcionResultado_306($citatorio,$resultado2daVisita) //domicilioNoSeEncuentraDestinatarioSiCitatorio
{
	if(($citatorio==1)&&($resultado2daVisita==2))//2da visita recibe otro
	{
		return 1;
	}
	return 0;
}

function funcionResultado_307($citatorio,$resultado2daVisita) //2da visita recibe destinatario
{

	if(($citatorio==1)&&($resultado2daVisita==1))
	{
		return 1;
	}
	return 0;
}

function funcionResultado_308($citatorio,$resultado2daVisita) //2da visita nadie atendio
{
	if(($citatorio==1)&&($resultado2daVisita==3))
	{
		return 1;
	}
	return 0;
}

function funcionLlenadoSobreseimiento($idRegistro)
{
	global $con;
	global $arrMesLetra;
	global $leyendaTribunal;
	$arrValores=array();
	$consulta="SELECT datosParametros,carpetaAdministrativa FROM 7035_informacionDocumentos WHERE idRegistro=".$idRegistro;
	$fila=$con->obtenerPrimeraFila($consulta);
	$datosParametros=json_decode(bD($fila[0]));
	
	
	$arrCarpetasAntecesoras=obtenerCarpetasAntecesoras($datosParametros->carpetaJudicial);
	$listaCarpetasAntecesoras="";
	foreach($arrCarpetasAntecesoras as $c)
	{
		if($listaCarpetasAntecesoras=="")
		{
			$listaCarpetasAntecesoras="'".$c."'";
		}
		else
			$listaCarpetasAntecesoras.=",'".$c."'";
	}
	
	$consulta="SELECT carpetaInvestigacion,idActividad,carpetaAdministrativaBase,unidadGestion FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$datosParametros->carpetaJudicial."'";
	$filaCarpeta=$con->obtenerPrimeraFila($consulta);
	
	$consulta="SELECT GROUP_CONCAT(upper(dl.denominacionDelito)) FROM _61_tablaDinamica d,_35_denominacionDelito dl WHERE d.idActividad=".$filaCarpeta[1]." AND
					dl.id__35_denominacionDelito=d.denominacionDelito";
		
	$delitos=$con->obtenerValor($consulta);
	
	
	$consulta="SELECT nombreUnidad,claveFolioCarpetas,tipoMateria FROM _17_tablaDinamica WHERE  claveUnidad='".$filaCarpeta[3]."'";
	$filaUGA=$con->obtenerPrimeraFila($consulta);
	
	$noUGA=$filaUGA[1]*1;
	if($filaUGA[2]==2)
		$noUGA="JA";
	
	$victimas="";
	$consulta="SELECT upper(CONCAT(IF(nombre IS NULL,'',nombre),' ',IF(apellidoPaterno IS NULL,'',apellidoPaterno),' ',IF(apellidoMaterno IS NULL,'',apellidoMaterno))) 
				FROM _47_tablaDinamica p,7005_relacionFigurasJuridicasSolicitud r WHERE r.idParticipante=p.id__47_tablaDinamica
				AND r.idActividad=".$filaCarpeta[1]." AND r.idFiguraJuridica=2 ORDER BY nombre,nombre,apellidoMaterno";
	
	$res=$con->obtenerFilas($consulta);
	while($filaImputado=mysql_fetch_row($res))
	{
		$nombre=$filaImputado[0];
		if($datosParametros->mostrarVictimasComo!=1)
			$nombre=inicialesNombre($filaImputado[0]);
		
		if($victimas=="")
			$victimas=$nombre;
		else
			$victimas.=", ".$nombre;
	}
	
	
	$consulta="SELECT upper(CONCAT(IF(nombre IS NULL,'',nombre),' ',IF(apellidoPaterno IS NULL,'',apellidoPaterno),' ',IF(apellidoMaterno IS NULL,'',apellidoMaterno))) 
				FROM _47_tablaDinamica p where id__47_tablaDinamica in(".$datosParametros->imputados.") ORDER BY nombre,nombre,apellidoMaterno";
	
	
	$arrValores["fecha"]=convertirFechaLetra(date("Y-m-d"),false,false);
	$arrValores["carpetaJudicial"]=$datosParametros->carpetaJudicial;
	$arrValores["carpetaJudicialAnterior"]=$filaCarpeta[2]==""?"Sin carpeta anterior":$filaCarpeta[2];
	$arrValores["carpetaInvestigacion"]=$filaCarpeta[0];
	$arrValores["imputado"]=$con->obtenerListaValores($consulta);
	$arrValores["victima"]=$victimas;
	
	$arrValores["folioOficio"]="UGJ".$noUGA."/".str_pad($datosParametros->noOficioAsignar,4,"0",STR_PAD_LEFT)."/".date("Y");
	
	$consulta="SELECT UPPER(u.nombre) FROM 7001_eventoAudienciaJuez ej,800_usuarios u WHERE ej.idRegistroEvento=".
				$datosParametros->audienciaSobreseimiento." AND ej.idJuez=u.idUsuario";
	$arrValores["juezAudiencia"]=$con->obtenerValor($consulta);
	$consulta="SELECT upper(tA.tipoAudiencia) FROM 7000_eventosAudiencia e,_4_tablaDinamica tA WHERE e.idRegistroEvento=".$datosParametros->audienciaSobreseimiento.
				" AND e.tipoAudiencia=tA.id__4_tablaDinamica";
	$arrValores["audiencia"]=$con->obtenerValor($consulta);
	if(strpos($arrValores["audiencia"],"audiencia")===false)
	{
		$arrValores["audiencia"]=" AUDIENCIA ".$arrValores["audiencia"];
	}
	$arrValores["delito"]=$delitos;
	$arrValores["victimaCabecera"]=$victimas==""?"________________":$victimas;
	$arrValores["fechaAudienciaInicial"]="";
	
	$arrValores["oficioConocimento"]="";
	if(isset($datosParametros->audienciaMedidaCautelar))
	{
		$consulta="SELECT fechaEvento FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$datosParametros->audienciaMedidaCautelar;
		$fechaAudienciaInicial=$con->obtenerValor($consulta);
		$arrValores["fechaAudienciaInicial"]=convertirFechaLetra($fechaAudienciaInicial,false,false);;
	}
	if(isset($datosParametros->noOficioMedidaCautelar))
	{
		$arrValores["oficioConocimento"]="UGJ".$noUGA."/".str_pad($datosParametros->noOficioMedidaCautelar,4,"0",STR_PAD_LEFT)."/".date("Y",strtotime($fechaAudienciaInicial));
	}
	
	$arrValores["fechaAudiencia"]="";
	if(isset($datosParametros->audienciaSobreseimiento))
	{
		$consulta="SELECT fechaEvento FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$datosParametros->audienciaSobreseimiento;
		$audienciaSobreseimiento=$con->obtenerValor($consulta);
		$arrValores["fechaAudiencia"]=convertirFechaLetra($audienciaSobreseimiento,false,false);;
	}
	
	$consulta="SELECT e.fechaEvento FROM 3013_registroResolutivosAudiencia  r,7000_eventosAudiencia e,7007_contenidosCarpetaAdministrativa c 
				WHERE c.carpetaAdministrativa in(".$listaCarpetasAntecesoras.") AND tipoContenido=3 AND idRegistroContenidoReferencia=r.idEvento 
				AND r.tipoResolutivo=50 AND e.idRegistroEvento=r.idEvento order by e.fechaEvento asc LIMIT 0,1";
	$fechaVinculacion=$con->obtenerValor($consulta);
	$arrValores["fechaVinculacion"]=$fechaVinculacion!=""?convertirFechaLetra($fechaVinculacion,false,false):"__________";
	
	
	
	
	
	$consulta=" SELECT 
				 IF((SELECT upper(nombre) FROM 800_usuarios WHERE idUsuario=usuarioAsignado) IS NULL,'(NO ASIGNADO)',
				 (SELECT upper(nombre) FROM 800_usuarios WHERE idUsuario=usuarioAsignado) ) AS nombre,
				 (SELECT upper(nombrePuesto) FROM _416_tablaDinamica WHERE id__416_tablaDinamica=puestoOrganozacional)  AS  puesto
				FROM _421_tablaDinamica WHERE id__421_tablaDinamica=".$datosParametros->firmante;
	
	$filaPuesto=$con->obtenerPrimeraFila($consulta);
	
	$arrValores["puestoFirmante"]=$filaPuesto[1];
	$arrValores["noUGA"]=convertirNumeroLetra($noUGA,false,false);
	if($arrValores["noUGA"]=="UN")
		$arrValores["noUGA"]="UNO";
	$arrValores["nombreFirmante"]=$filaPuesto[0];
	$arrValores["nombreResponsable"]=obtenerNombreUsuario($_SESSION["idUsr"]);
	$arrValores["leyendaTribunal"]=str_replace("<br>", " ",$leyendaTribunal);
	
	$arrValores["leyendaVictimaCabecera"]=($datosParametros->mostrarVictimasComo!=1)?"Víctima u Ofendido de Iniciales":"Víctima u Ofendido";
	
	
	
	return $arrValores;
}

function funcionLlenadoMedidaCautelar($idRegistro)
{
	global $con;
	global $arrMesLetra;
	global $leyendaTribunal;
	$arrValores=array();
	$consulta="SELECT datosParametros,carpetaAdministrativa FROM 7035_informacionDocumentos WHERE idRegistro=".$idRegistro;
	$fila=$con->obtenerPrimeraFila($consulta);
	$datosParametros=json_decode(bD($fila[0]));

	
	$arrCarpetasAntecesoras=obtenerCarpetasAntecesoras($datosParametros->carpetaJudicial);
	$listaCarpetasAntecesoras="";
	foreach($arrCarpetasAntecesoras as $c)
	{
		if($listaCarpetasAntecesoras=="")
		{
			$listaCarpetasAntecesoras="'".$c."'";
		}
		else
			$listaCarpetasAntecesoras.=",'".$c."'";
	}
	
	$consulta="SELECT carpetaInvestigacion,idActividad,carpetaAdministrativaBase,unidadGestion FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$datosParametros->carpetaJudicial."'";
	$filaCarpeta=$con->obtenerPrimeraFila($consulta);
	
	$consulta="SELECT GROUP_CONCAT(upper(dl.denominacionDelito)) FROM _61_tablaDinamica d,_35_denominacionDelito dl WHERE d.idActividad=".$filaCarpeta[1]." AND
					dl.id__35_denominacionDelito=d.denominacionDelito";
		
	$delitos=$con->obtenerValor($consulta);
	
	
	$consulta="SELECT nombreUnidad,claveFolioCarpetas FROM _17_tablaDinamica WHERE  claveUnidad='".$filaCarpeta[3]."'";
	$filaUGA=$con->obtenerPrimeraFila($consulta);
	
	$noUGA=$filaUGA[1]*1;
	
	$victimas="";
	$consulta="SELECT upper(CONCAT(IF(nombre IS NULL,'',nombre),' ',IF(apellidoPaterno IS NULL,'',apellidoPaterno),' ',IF(apellidoMaterno IS NULL,'',apellidoMaterno))) 
				FROM _47_tablaDinamica p,7005_relacionFigurasJuridicasSolicitud r WHERE r.idParticipante=p.id__47_tablaDinamica
				AND r.idActividad=".$filaCarpeta[1]." AND r.idFiguraJuridica=2 ORDER BY nombre,nombre,apellidoMaterno";
	
	$res=$con->obtenerFilas($consulta);
	while($filaImputado=mysql_fetch_row($res))
	{
		$nombre=$filaImputado[0];
		if($datosParametros->mostrarVictimasComo!=1)
			$nombre=inicialesNombre($filaImputado[0]);
		
		if($victimas=="")
			$victimas=$nombre;
		else
			$victimas.=", ".$nombre;
	}
	
	
	$consulta="SELECT upper(CONCAT(IF(nombre IS NULL,'',nombre),' ',IF(apellidoPaterno IS NULL,'',apellidoPaterno),' ',IF(apellidoMaterno IS NULL,'',apellidoMaterno))) 
				FROM _47_tablaDinamica p where id__47_tablaDinamica in(".$datosParametros->imputados.") ORDER BY nombre,nombre,apellidoMaterno";
	
	
	$arrValores["fecha"]=convertirFechaLetra(date("Y-m-d"),false,false);
	$arrValores["carpetaJudicial"]=$datosParametros->carpetaJudicial;
	$arrValores["carpetaJudicialAnterior"]=$filaCarpeta[2]==""?"Sin carpeta anterior":$filaCarpeta[2];
	$arrValores["carpetaInvestigacion"]=$filaCarpeta[0];
	$arrValores["imputado"]=$con->obtenerListaValores($consulta);
	$arrValores["victima"]=$victimas;
	
	$arrValores["folioOficio"]="UGJ".$noUGA."/".str_pad($datosParametros->noOficioAsignar,4,"0",STR_PAD_LEFT)."/".date("Y");
	
	$consulta="SELECT upper(tA.tipoAudiencia) FROM 7000_eventosAudiencia e,_4_tablaDinamica tA WHERE e.idRegistroEvento=".$datosParametros->audienciaMedidas.
				" AND e.tipoAudiencia=tA.id__4_tablaDinamica";
	$arrValores["audiencia"]=$con->obtenerValor($consulta);
	if(strpos($arrValores["audiencia"],"audiencia")===false)
	{
		$arrValores["audiencia"]=" AUDIENCIA ".$arrValores["audiencia"];
	}
	
	$consulta="SELECT UPPER(u.nombre) FROM 7001_eventoAudienciaJuez ej,800_usuarios u WHERE ej.idRegistroEvento=".
				$datosParametros->audienciaMedidas." AND ej.idJuez=u.idUsuario";
	$arrValores["juezAudiencia"]=$con->obtenerValor($consulta);
	$arrValores["delito"]=$delitos;
	$arrValores["victimaCabecera"]=$victimas;
	
	
	$numReg=1;
	$consulta=" SELECT 
				 IF((SELECT upper(nombre) FROM 800_usuarios WHERE idUsuario=usuarioAsignado) IS NULL,'(NO ASIGNADO)',
				 (SELECT upper(nombre) FROM 800_usuarios WHERE idUsuario=usuarioAsignado) ) AS nombre,
				 (SELECT upper(nombrePuesto) FROM _416_tablaDinamica WHERE id__416_tablaDinamica=puestoOrganozacional)  AS  puesto
				FROM _421_tablaDinamica WHERE id__421_tablaDinamica=".$datosParametros->firmante;
	
	$filaPuesto=$con->obtenerPrimeraFila($consulta);
	$arrMedidasCautelares="";
	
	
	foreach($datosParametros->medidadCautelares as $m)
	{
		if($m->idMedidaCautelar!=0)
		{
			$consulta="SELECT leyenda FROM _587_tablaDinamica WHERE id__587_tablaDinamica =".$m->idMedidaCautelar;
			$fLeyenda=$con->obtenerPrimeraFila($consulta);
			$arrMedidasCautelares.="<p>". ($numReg).") ".$fLeyenda[0]."".($m->detalles==""?".":": ".$m->detalles)."</p>";
		}
		else
		{
			$arrMedidasCautelares.="<p>". ($numReg).") ".$m->detalles."</p>";
		}
		
		$numReg++;
	}
	
	
	
	$arrValores["medidasCautelares"]=$arrMedidasCautelares;
	$arrValores["puestoFirmante"]=$filaPuesto[1];
	$arrValores["noUGA"]=convertirNumeroLetra($noUGA,false,false);
	if($arrValores["noUGA"]=="UN")
		$arrValores["noUGA"]="UNO";
	$arrValores["noUGAMinuscula"]=ucfirst(mb_strtolower($arrValores["noUGA"]));
	$arrValores["nombreFirmante"]=$filaPuesto[0];
	$arrValores["nombreResponsable"]=obtenerNombreUsuario($_SESSION["idUsr"]);
	$arrValores["leyendaTribunal"]=str_replace("<br>", " ",$leyendaTribunal);
	
	$arrValores["leyendaVictimaCabecera"]=($datosParametros->mostrarVictimasComo!=1)?"Víctima u Ofendido de Iniciales":"Víctima u Ofendido";
	
	return $arrValores;
}


function funcionLlenadoInformeSuspensionCondicional($idRegistro)
{
	global $con;
	global $arrMesLetra;
	global $leyendaTribunal;
	$arrValores=array();
	$consulta="SELECT datosParametros,carpetaAdministrativa FROM 7035_informacionDocumentos WHERE idRegistro=".$idRegistro;
	$fila=$con->obtenerPrimeraFila($consulta);
	$datosParametros=json_decode(bD($fila[0]));
	
	
	$arrCarpetasAntecesoras=obtenerCarpetasAntecesoras($datosParametros->carpetaJudicial);
	$listaCarpetasAntecesoras="";
	foreach($arrCarpetasAntecesoras as $c)
	{
		if($listaCarpetasAntecesoras=="")
		{
			$listaCarpetasAntecesoras="'".$c."'";
		}
		else
			$listaCarpetasAntecesoras.=",'".$c."'";
	}
	
	$consulta="SELECT carpetaInvestigacion,idActividad,carpetaAdministrativaBase,unidadGestion FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$datosParametros->carpetaJudicial."'";
	$filaCarpeta=$con->obtenerPrimeraFila($consulta);
	
	$consulta="SELECT GROUP_CONCAT(upper(dl.denominacionDelito)) FROM _61_tablaDinamica d,_35_denominacionDelito dl WHERE d.idActividad=".$filaCarpeta[1]." AND
					dl.id__35_denominacionDelito=d.denominacionDelito";
		
	$delitos=$con->obtenerValor($consulta);
	
	
	$consulta="SELECT nombreUnidad,claveFolioCarpetas FROM _17_tablaDinamica WHERE  claveUnidad='".$filaCarpeta[3]."'";
	$filaUGA=$con->obtenerPrimeraFila($consulta);
	
	$noUGA=$filaUGA[1]*1;
	
	$victimas="";
	$consulta="SELECT upper(CONCAT(IF(nombre IS NULL,'',nombre),' ',IF(apellidoPaterno IS NULL,'',apellidoPaterno),' ',IF(apellidoMaterno IS NULL,'',apellidoMaterno))) 
				FROM _47_tablaDinamica p,7005_relacionFigurasJuridicasSolicitud r WHERE r.idParticipante=p.id__47_tablaDinamica
				AND r.idActividad=".$filaCarpeta[1]." AND r.idFiguraJuridica=2 ORDER BY nombre,nombre,apellidoMaterno";
	
	$res=$con->obtenerFilas($consulta);
	while($filaImputado=mysql_fetch_row($res))
	{
		$nombre=$filaImputado[0];
		if($datosParametros->mostrarVictimasComo!=1)
			$nombre=inicialesNombre($filaImputado[0]);
		
		if($victimas=="")
			$victimas=$nombre;
		else
			$victimas.=", ".$nombre;
	}
	
	
	$consulta="SELECT upper(CONCAT(IF(nombre IS NULL,'',nombre),' ',IF(apellidoPaterno IS NULL,'',apellidoPaterno),' ',IF(apellidoMaterno IS NULL,'',apellidoMaterno))) 
				FROM _47_tablaDinamica p where id__47_tablaDinamica in(".$datosParametros->imputados.") ORDER BY nombre,nombre,apellidoMaterno";
	
	
	$arrValores["fecha"]=convertirFechaLetra(date("Y-m-d"),false,false);
	$arrValores["carpetaJudicial"]=$datosParametros->carpetaJudicial;
	$arrValores["carpetaJudicialAnterior"]=$filaCarpeta[2]==""?"Sin carpeta anterior":$filaCarpeta[2];
	$arrValores["carpetaInvestigacion"]=$filaCarpeta[0];
	$arrValores["imputados"]=$con->obtenerListaValores($consulta);
	$arrValores["imputado"]=$arrValores["imputados"];
	$nImputados=$con->filasAfectadas;
	$arrValores["victima"]=$victimas;
	
	$arrValores["folioOficio"]="UGJ".$noUGA."/".str_pad($datosParametros->noOficioAsignar,4,"0",STR_PAD_LEFT)."/".date("Y");
	
	$consulta="SELECT upper(tA.tipoAudiencia) FROM 7000_eventosAudiencia e,_4_tablaDinamica tA WHERE e.idRegistroEvento=".$datosParametros->audienciaCelebrar.
				" AND e.tipoAudiencia=tA.id__4_tablaDinamica";
	$arrValores["tipoAudiencia"]=$con->obtenerValor($consulta);
	if(strpos($arrValores["tipoAudiencia"],"AUDIENCIA")===false)
	{
		$arrValores["tipoAudiencia"]=" AUDIENCIA ".$arrValores["tipoAudiencia"];
	}
	$arrValores["delito"]=$delitos;
	$arrValores["victimaCabecera"]=$victimas;
	$consulta="SELECT fechaEvento,horaInicioEvento FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$datosParametros->audienciaCelebrar;
	$fAudiencia=$con->obtenerPrimeraFila($consulta);
	$fechaAudiencia=$fAudiencia[0];
	$arrValores["fechaAudiencia"]=convertirFechaLetra($fechaAudiencia,false,false);
	$arrValores["horasAudiencias"]=date("H:i",strtotime($fAudiencia[1]));
	$arrValores["etImputados"]= $nImputados<2?"del imputado":"de los imputados";
	$arrValores["etImputados2"]=$nImputados<2?"el imputado":"los imputados";
	$arrValores["etImputados3"]=$nImputados<2?"el imputado aludido":"los imputados aludidos";
	
	$consulta=" SELECT 
				 IF((SELECT upper(nombre) FROM 800_usuarios WHERE idUsuario=usuarioAsignado) IS NULL,'(NO ASIGNADO)',
				 (SELECT upper(nombre) FROM 800_usuarios WHERE idUsuario=usuarioAsignado) ) AS nombre,
				 (SELECT upper(nombrePuesto) FROM _416_tablaDinamica WHERE id__416_tablaDinamica=puestoOrganozacional)  AS  puesto
				FROM _421_tablaDinamica WHERE id__421_tablaDinamica=".$datosParametros->firmante;
	
	$filaPuesto=$con->obtenerPrimeraFila($consulta);
	
	$arrValores["puestoFirmante"]=$filaPuesto[1];
	$arrValores["noUGA"]=convertirNumeroLetra($noUGA,false,false);
	if($arrValores["noUGA"]=="UN")
		$arrValores["noUGA"]="UNO";
	$arrValores["noUGAMinuscula"]=ucfirst(mb_strtolower($arrValores["noUGA"]));
		
		
	$arrValores["nombreFirmante"]=$filaPuesto[0];
	$arrValores["nombreResponsable"]=obtenerNombreUsuario($_SESSION["idUsr"]);
	$arrValores["leyendaTribunal"]=str_replace("<br>", " ",$leyendaTribunal);
	
	$arrValores["leyendaVictimaCabecera"]=($datosParametros->mostrarVictimasComo!=1)?"Víctima u Ofendido de Iniciales":"Víctima u Ofendido";
	
	return $arrValores;
}

function notificarResponsableMedidasCautelares($idRegistrBitacora)
{
	global $con;
	
	$consulta="SELECT * FROM 3000_bitacoraFormatos WHERE idRegistro=".$idRegistrBitacora;
	$fBitacora=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$consulta="SELECT idPerfilEvaluacion,idRegistro,tipoFormato FROM 3000_formatosRegistrados WHERE idRegistroFormato=".$fBitacora["idRegistroFormato"];
	$fDocumento=$con->obtenerPrimeraFilaAsoc($consulta);
	$consulta="SELECT categoriaDocumento FROM _10_tablaDinamica WHERE id__10_tablaDinamica=".$fDocumento["tipoFormato"];
	$categoriaDocumento=$con->obtenerValor($consulta);
	
	$rolBusqueda="";
	switch($categoriaDocumento)
	{
		case 52://Oficio para USMECA
			$rolBusqueda="155_0";
		break;
		case 60://Oficio para INCIFO
			$rolBusqueda="191_0";
		break;
	}
	
	$consulta="SELECT * FROM 7035_informacionDocumentos WHERE idRegistro=".$fDocumento["idRegistro"];
	$fDocumentoComp=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$idAudienciaAsociada="";
	$datosAudiencia="";
	
	if($fDocumentoComp["datosParametros"]!="")
	{
		$oParametro=json_decode(bD($fDocumentoComp["datosParametros"]));
		switch($fDocumento["tipoFormato"])
		{
			case 502:
				$idAudienciaAsociada=$oParametro->audienciaSobreseimiento;
			break;
			case 503:
				$idAudienciaAsociada=$oParametro->audienciaMedidas;
			break;
			case 504:
				$idAudienciaAsociada=$oParametro->audienciaCelebrar;
			break;
			case 513:
				$idAudienciaAsociada=$oParametro->audienciaSobreseimiento;
			break;
			case 514:
				$idAudienciaAsociada=$oParametro->audienciaConcede;			
			break;
			case 515:
				$idAudienciaAsociada=$oParametro->audienciaCelebrar;			
			break;
			case 517:
				$idAudienciaAsociada=$oParametro->audienciaConcede;
			break;
			case 518:
				$idAudienciaAsociada=$oParametro->audienciaConcede;
			break;
		}
	}
	$hAudiencia="";
	if($idAudienciaAsociada!="")
	{
		$consulta="SELECT horaInicioEvento  FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$idAudienciaAsociada;
		$horaInicioEvento=strtotime($con->obtenerValor($consulta));
		$hAudiencia="<br>Fecha de audiencia: ".date("d/m/Y",$horaInicioEvento)." ".date("H:i",$horaInicioEvento)." hrs.";
	}

	$tNotificacion="Nuevo: ".str_replace(".doc",".pdf",str_replace(".docx",".pdf",$fDocumentoComp["tituloDocumento"])).$hAudiencia;
	$arrDestinatario=array();
	$consulta="SELECT r.idUsuario FROM 807_usuariosVSRoles r WHERE r.codigoRol='".$rolBusqueda."'";//191

	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		registrarNotificacionDocumentoFinalMedidasCautelares(4,$tNotificacion,$fila[0],$rolBusqueda,$fDocumentoComp["carpetaAdministrativa"],$fBitacora["responsableCambio"],$fBitacora["rolActual"],$fDocumento["idRegistro"]);
	}
	
	return 1;
}

function resultadoNotificacionMailNotificado($resultadoMail)
{
	global $con;
	if($resultadoMail==1)
	{
		return 1;
	}	
	return 0;
}

function resultadoNotificacionMailNoNotificado($resultadoMail)
{
	global $con;
	return resultadoNotificacionMailNotificado($resultadoMail)==1?0:1;
}

function resultadoNotificacionLlamadaNotificado($resultadoLlamada)
{
	global $con;
	$resultadoLlamada=json_decode(bD($resultadoLlamada));
	
	if(($resultadoLlamada->obtuvoRespuesta==1)&&($resultadoLlamada->respuestaObtenida==1))
	{
		return 1;
	}	
	return 0;
}

function resultadoNotificacionLlamadaNoNotificado($resultadoLlamada)
{
	global $con;
	return resultadoNotificacionLlamadaNotificado($resultadoLlamada)==1?0:1;
}

function registrarRespuestaNotificacionOrdenNotificacionNotificador($idTablero,$idUsuarioDestinatario,$actor,$carpetaAdministrativa,$idOrden,$idDiligencia)
{
	global $con;
	$arrValores=array();
	$nombreTablaBase="9060_tableroControl_".$idTablero;
	$rolActor=obtenerTituloRol($actor);
	$nombreUsuario=obtenerNombreUsuario($idUsuarioDestinatario)." (".$rolActor.")";
	$arrValores["fechaAsignacion"]=date("Y-m-d H:i:s");
	
	if($con->existeCampo("fechaRegistroSistema",$nombreTablaBase))
		$arrValores["fechaRegistroSistema"]=$arrValores["fechaAsignacion"];
		
	$arrValores["tipoNotificacion"]="Notificaci&oacute;n realizadada por Central de Notificadores";
	$arrValores["usuarioRemitente"]="Central de Notificadores";
	$arrValores["idUsuarioRemitente"]=0;
	$arrValores["usuarioDestinatario"]=str_replace(" (Suplantado)","",$nombreUsuario);
	$arrValores["idUsuarioDestinatario"]=$idUsuarioDestinatario;
	
	$arrValores["idEstado"]="1";
	$arrValores["contenidoMensaje"]="";
	$arrValores["objConfiguracion"]='{"idOrden":"'.$idOrden.'","actorAccesoProceso":"'.$actor.
									'","funcionApertura":"window.parent.parent.mostrarVentanaAtencionOrdenNotificacionNotificador","idDiligencia":"'.$idDiligencia.'"}';
	$arrValores["permiteAbrirProceso"]="1";
	$arrValores["idNotificacion"]="0";
	$arrValores["numeroCarpetaAdministrativa"]=$carpetaAdministrativa;
	$arrValores["iFormulario"]=-7029;
	$arrValores["iRegistro"]=$idDiligencia;
	$arrValores["iReferencia"]=-1;
	$consulta="SELECT Institucion FROM 801_adscripcion WHERE idUsuario=".$idUsuarioDestinatario;
	$codigoUnidad=$con->obtenerValor($consulta);
	
	$arrValores["codigoUnidad"]=$codigoUnidad;
	
	
	
	$consulta="";
	$camposInsert="";
	$camposValues="";
	foreach($arrValores as $campo=>$valor)
	{
		if($camposInsert=="")
			$camposInsert=$campo;
		else
			$camposInsert.=",".$campo;

		if($camposValues=="")
			$camposValues=($valor==""?"NULL":"'".cv($valor)."'");
		else
			$camposValues.=",".($valor==""?"NULL":"'".cv($valor)."'");
	}

	$consulta="insert into 9060_tableroControl_".$idTablero."(".$camposInsert.") values(".$camposValues.")";
	
	return $con->ejecutarConsulta($consulta);
}

function registrarNotificacionDocumentoFinalMedidasCautelares($idTablero,$tNotificacion,$idUsuarioDestinatario,$actor,$carpetaAdministrativa,$idUsuarioRemitente,$rolRemitente,$idRegistro)
{
	global $con;
	$arrValores=array();
	
	$consulta="SELECT tipoDocumento,idFormulario,idReferencia,tituloDocumento FROM 7035_informacionDocumentos WHERE idRegistro=".$idRegistro;
	$fRegistroDocumento=$con->obtenerPrimeraFilaAsoc($consulta);

	$consulta="SELECT idRegistroFormato,idDocumento,fechaFirma FROM 3000_formatosRegistrados WHERE idFormulario=-2 AND idRegistro=".$idRegistro;
	$fRegistroD=$con->obtenerPrimeraFila($consulta);
		
	$idRegistroFormato=$fRegistroD[0];
	$rolActor=obtenerTituloRol($actor);
	$nombreUsuario=obtenerNombreUsuario($idUsuarioDestinatario)." (".$rolActor.")";
	$arrValores["fechaAsignacion"]=date("Y-m-d H:i:s");
	$arrValores["fechaRegistroSistema"]=$fRegistroD[2];
	$arrValores["tipoNotificacion"]=$tNotificacion;
	$arrValores["usuarioRemitente"]=obtenerNombreUsuario($idUsuarioRemitente)." (".obtenerTituloRol($rolRemitente).")";
	$arrValores["idUsuarioRemitente"]=$idUsuarioRemitente;
	$arrValores["usuarioDestinatario"]=str_replace(" (Suplantado)","",$nombreUsuario);
	$arrValores["idUsuarioDestinatario"]=$idUsuarioDestinatario;
	
	$arrValores["idEstado"]="1";
	$arrValores["contenidoMensaje"]="";
	$arrValores["objConfiguracion"]='{"idDocumentoFinal":"'.$fRegistroD[1].'","idFormulario":"'.$fRegistroDocumento["idFormulario"].'","idRegistro":"'.
									$fRegistroDocumento["idReferencia"].'","idRegistroFormato":"'.$idRegistroFormato.'","actorAccesoProceso":"'.$actor.
									'","funcionApertura":"window.parent.parent.mostrarVentanaDocumentoAutorizadoRespuestaMedidas","idRegistroInformacionDocumento":"'.$idRegistro.'"}';
	$arrValores["permiteAbrirProceso"]="1";
	$arrValores["idNotificacion"]="-10";
	$arrValores["numeroCarpetaAdministrativa"]=$carpetaAdministrativa;
	$arrValores["iFormulario"]=-5;
	$arrValores["iRegistro"]=$fRegistroD[0];
	$arrValores["iReferencia"]=-1;
	$consulta="SELECT Institucion FROM 801_adscripcion WHERE idUsuario=".$idUsuarioDestinatario;
	$codigoUnidad=$con->obtenerValor($consulta);
	
	$arrValores["codigoUnidad"]=$codigoUnidad;
	
	
	
	$consulta="";
	$camposInsert="";
	$camposValues="";
	foreach($arrValores as $campo=>$valor)
	{
		if($camposInsert=="")
			$camposInsert=$campo;
		else
			$camposInsert.=",".$campo;

		if($camposValues=="")
			$camposValues=($valor==""?"NULL":"'".cv($valor)."'");
		else
			$camposValues.=",".($valor==""?"NULL":"'".cv($valor)."'");
	}
	$consulta=array();
	$x=0;
	$consulta[$x]="begin";
	$x++;

	$consulta[$x]="insert into 9060_tableroControl_".$idTablero."(".$camposInsert.") values(".$camposValues.")";
	$x++;
	
	$query="select COUNT(*) FROM 7047_registroDocumentosRelacion WHERE carpetaAdministrativa='".$carpetaAdministrativa.
			"' AND idRegistroFormato=".$idRegistroFormato;
	$nReg=$con->obtenerValor($query);		
	
	if($nReg==0)
	{
		$consulta[$x]="INSERT INTO 7047_registroDocumentosRelacion(carpetaAdministrativa,idRegistroFormato,fechaRegistro) 
					VALUES('".$carpetaAdministrativa."',".$idRegistroFormato.",'".date("Y-m-d H:i:s")."')";
				
		$x++;
	}
	$consulta[$x]="commit";
	$x++;
	return $con->ejecutarBloque($consulta);

}

function notificarResponsableRespuestaMedidasCautelares($idRegistrBitacora)
{
	global $con;
	
	$consulta="SELECT * FROM 3000_bitacoraFormatos WHERE idRegistro=".$idRegistrBitacora;
	$fBitacora=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$consulta="SELECT idPerfilEvaluacion,idRegistro,idFormulario,tipoFormato FROM 3000_formatosRegistrados WHERE idRegistroFormato=".$fBitacora["idRegistroFormato"];
	$fDocumento=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$consulta="SELECT categoriaDocumento FROM _10_tablaDinamica WHERE id__10_tablaDinamica=".$fDocumento["tipoFormato"];
	$categoriaDocumento=$con->obtenerValor($consulta);
	
	$carpetaAdministrativa=obtenerCarpetaAdministrativaProceso($fDocumento["idFormulario"],$fDocumento["idRegistro"]);
	$consulta="SELECT * FROM 7035_informacionDocumentos WHERE idRegistro=".$fDocumento["idRegistro"];

	$fDocumentoComp=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$tituloNotificacion="";
	switch($categoriaDocumento)
	{
		case 58: //Oficio de USMECA
			$tituloNotificacion="Respuesta USMECA";
		break;		
		case 59:	//Oficio de INCIFO
			$tituloNotificacion="Respuesta INCIFO";
		break;
	}
	$tNotificacion=$tituloNotificacion.": ".str_replace(".doc",".pdf",str_replace(".docx",".pdf",$fDocumentoComp["tituloDocumento"]));
	
	
	$arrDestinatario=array();
	$arrDestinatario["12_0"]="Director de UGA";
	$arrDestinatario["19_0"]="Subdirector de Causa y Ejecuciones";
	$arrDestinatario["69_0"]="Subdirector de Causa y Sala";
	$arrDestinatario["81_0"]="Subdirector de Sala";
	$arrDestinatario["74_0"]="Subdirector de Ejecución de Sanciones";
	
	$consulta="SELECT unidadGestion FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$carpetaAdministrativa."'";

	$unidadGestion=$con->obtenerValor($consulta);
	
	foreach($arrDestinatario as $rol=>$leyenda)
	{
		$consulta="SELECT r.idUsuario FROM 807_usuariosVSRoles r ,801_adscripcion a WHERE r.codigoRol='".$rol."'
					and r.idUsuario=a.idUsuario and a.Institucion='".$unidadGestion."'";

		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			registrarNotificacionRespuestaDocumentoFinalMedidasCautelares(5,$tNotificacion,$fila[0],$rol,$carpetaAdministrativa,$fBitacora["responsableCambio"],$fBitacora["rolActual"],$fDocumentoComp["idRegistro"]);
		}
	}
	return 1;
}


function registrarNotificacionRespuestaDocumentoFinalMedidasCautelares($idTablero,$tNotificacion,$idUsuarioDestinatario,$actor,$carpetaAdministrativa,$idUsuarioRemitente,$rolRemitente,$idRegistro)
{
	global $con;
	$arrValores=array();
	
	$consulta="SELECT tipoDocumento,idFormulario,idReferencia,tituloDocumento FROM 7035_informacionDocumentos WHERE idRegistro=".$idRegistro;
	$fRegistroDocumento=$con->obtenerPrimeraFilaAsoc($consulta);

	$consulta="SELECT idRegistroFormato,idDocumento,fechaFirma FROM 3000_formatosRegistrados WHERE idFormulario=-2 AND idRegistro=".$idRegistro;
	$fRegistroD=$con->obtenerPrimeraFila($consulta);

	$consulta="SELECT COUNT(*) FROM 9060_tableroControl_5 WHERE idUsuarioDestinatario=".$idUsuarioDestinatario.
			" AND iFormulario=".$fRegistroDocumento["idFormulario"]." AND iRegistro=".
			$fRegistroDocumento["idReferencia"]." AND iReferencia=".$fRegistroD[1];
	$nReg=$con->obtenerValor($consulta);
	if($nReg>0)
	{
		return;
	}
	
	$idRegistroFormato=$fRegistroD[0];
	$rolActor=obtenerTituloRol($actor);
	$nombreUsuario=obtenerNombreUsuario($idUsuarioDestinatario)." (".$rolActor.")";
	$arrValores["fechaAsignacion"]=date("Y-m-d H:i:s");
	$arrValores["tipoNotificacion"]=$tNotificacion;
	//$arrValores["fechaRegistroSistema"]=$fRegistroD[2];
	$lblRolRemitente=obtenerTituloRol($rolRemitente);
	
	$arrValores["usuarioRemitente"]=obtenerNombreUsuario($idUsuarioRemitente).($lblRolRemitente==""?"":" (".$lblRolRemitente.")");
	$arrValores["idUsuarioRemitente"]=$idUsuarioRemitente;
	$arrValores["usuarioDestinatario"]=str_replace(" (Suplantado)","",$nombreUsuario);
	$arrValores["idUsuarioDestinatario"]=$idUsuarioDestinatario;
	
	$arrValores["idEstado"]="1";
	$arrValores["contenidoMensaje"]="";
	$arrValores["objConfiguracion"]='{"idDocumentoFinal":"'.$fRegistroD[1].'","idFormulario":"'.$fRegistroDocumento["idFormulario"].'","idRegistro":"'.
									$fRegistroDocumento["idReferencia"].'","idRegistroFormato":"'.$idRegistroFormato.'","actorAccesoProceso":"'.$actor.
									'","funcionApertura":"window.parent.parent.mostrarVentanaDocumentoAutorizado","idRegistroInformacionDocumento":"'.$idRegistro.'"}';
	$arrValores["permiteAbrirProceso"]="1";
	$arrValores["idNotificacion"]="0";
	$arrValores["numeroCarpetaAdministrativa"]=$carpetaAdministrativa;

	$arrValores["iFormulario"]=-5;
	$arrValores["iRegistro"]=$fRegistroD[0];
	$arrValores["iReferencia"]=$fRegistroD[1];
	$consulta="SELECT Institucion FROM 801_adscripcion WHERE idUsuario=".$idUsuarioDestinatario;
	$codigoUnidad=$con->obtenerValor($consulta);
	
	$arrValores["codigoUnidad"]=$codigoUnidad;
	
	
	
	$consulta="";
	$camposInsert="";
	$camposValues="";
	foreach($arrValores as $campo=>$valor)
	{
		if($camposInsert=="")
			$camposInsert=$campo;
		else
			$camposInsert.=",".$campo;

		if($camposValues=="")
			$camposValues=($valor==""?"NULL":"'".cv($valor)."'");
		else
			$camposValues.=",".($valor==""?"NULL":"'".cv($valor)."'");
	}
	$consulta=array();
	$x=0;
	$consulta[$x]="begin";
	$x++;

	$consulta[$x]="insert into 9060_tableroControl_".$idTablero."(".$camposInsert.") values(".$camposValues.")";
	$x++;
	
	$query="select COUNT(*) FROM 7047_registroDocumentosRelacion WHERE carpetaAdministrativa='".$carpetaAdministrativa.
			"' AND idRegistroFormato=".$idRegistroFormato;
	$nReg=$con->obtenerValor($query);		
	
	if($nReg==0)
	{
		$consulta[$x]="INSERT INTO 7047_registroDocumentosRelacion(carpetaAdministrativa,idRegistroFormato,fechaRegistro) 
					VALUES('".$carpetaAdministrativa."',".$idRegistroFormato.",'".date("Y-m-d H:i:s")."')";
				
		$x++;
	}
	$consulta[$x]="commit";
	$x++;

	return $con->ejecutarBloque($consulta);

}


function generarFolioOficioUnidadAdministrativa($idFormulario,$idRegistro)
{
	global $con;
	
	$anioActual=date("Y");
	$arrParametros["anio"]=$anioActual;
	$folioActual=1;
	$consulta="SELECT codigoInstitucion,noOficioAsignado FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	
	$fRegistroFolio=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$codigoInstitucion=$fRegistroFolio["codigoInstitucion"];
	if(($fRegistroFolio["noOficioAsignado"]!="")&&($fRegistroFolio["noOficioAsignado"]!="N/E"))
	{
		return true;
	}
	$consulta="SELECT id__17_tablaDinamica FROM _17_tablaDinamica WHERE claveUnidad='".$codigoInstitucion."'";
	
	$idUnidadGestion=$con->obtenerValor($consulta);
	$consulta="SELECT * FROM _599_tablaDinamica WHERE idReferencia=".$idUnidadGestion;
	$fRegistro=$con->obtenerPrimeraFilaAsoc($consulta);
	$consulta="begin";
	if($con->ejecutarConsulta($consulta))
	{
		
		$consulta="SELECT * FROM _599_gridSerieFolios WHERE idReferencia=".($fRegistro?$fRegistro["id__599_tablaDinamica"]:"-1").
					" and anio=".$anioActual." for update";
		$fSeries=$con->obtenerPrimeraFilaAsoc($consulta);
		
		if(!$fSeries)
		{
			$folioActual=1;
		}
		else
		{
			$folioActual=$fSeries["folioActual"];	
		}
		
		$folioAsignar="";
		
		
		
		$enc=true;
		while($enc)
		{
			$arrParametros["folio"]=str_pad($folioActual,$fRegistro["longitudCampoFolio"],"0",STR_PAD_LEFT);
			
			$folioAsignar=$fRegistro["formatoFolioOficio"];
			foreach($arrParametros as $campo=>$valor)
			{
				$folioAsignar=str_replace('{'.$campo.'}',$valor,$folioAsignar);
			}
			
			$consulta="SELECT COUNT(*) FROM _".$idFormulario."_tablaDinamica WHERE noOficioAsignado='".$folioAsignar."' AND codigoInstitucion='".$codigoInstitucion."'";

			$numReg=$con->obtenerValor($consulta);
			if($numReg==0)
			{
				$enc=false;
			}
			else
			{
				$folioActual++;
			}
			
		}
		
		$consulta="UPDATE _".$idFormulario."_tablaDinamica SET noOficioAsignado='".$folioAsignar."',noOficioAsignadoNumerico=".$folioActual.
				" WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
		$con->ejecutarConsulta($consulta);
		
		$folioActual++;
		if($fSeries)
		{
			$consulta="UPDATE  _599_gridSerieFolios SET folioActual=".$folioActual." WHERE idReferencia=".$fRegistro["id__599_tablaDinamica"]." AND anio=".$anioActual;	
		}
		else
		{
			$consulta="INSERT INTO _599_gridSerieFolios(idReferencia,anio,folioActual) VALUES(".$fRegistro["id__599_tablaDinamica"].",".$anioActual.",".$folioActual.")";	
		}
		 $con->ejecutarConsulta($consulta);
		$consulta="commit";
		return $con->ejecutarConsulta($consulta);
	}
	
}

function registrarDocumentoMarcaFirma($idFormulario,$idRegistro)
{
	global $con;

	$arrValores=array();
	$idTablero=4;
	
	$consulta="SELECT idEstado FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$idEstado=$con->obtenerValor($consulta);
	
	
	$consulta="SELECT actorCambio FROM 941_bitacoraEtapasFormularios WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro." and etapaActual=".$idEstado;
	$actorCambio=$con->obtenerValor($consulta);
	
	$consulta="SELECT actor FROM 944_actoresProcesoEtapa WHERE idActorProcesoEtapa=".$actorCambio;
	$rolActor=$con->obtenerValor($consulta);
	
	
	$consulta="SELECT * FROM 9060_tableroControl_4 WHERE iFormulario=".$idFormulario." AND iRegistro=".$idRegistro." AND idUsuarioDestinatario=".
			$_SESSION["idUsr"]." order by idRegistro desc";
	$fTablero=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$consulta="SELECT * FROM 7035_informacionDocumentos WHERE idFormulario=".$idFormulario." AND idReferencia=".$idRegistro;
	$fInformacion=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$consulta="SELECT idRegistroFormato FROM 3000_formatosRegistrados WHERE idFormulario=-2 AND idRegistro=".$fInformacion["idRegistro"];
	$idRegistroFormato=$con->obtenerValor($consulta);
	
	$consulta="SELECT idCarpeta FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$fInformacion["carpetaAdministrativa"]."'";
	$idCarpetaAdministrativa=$con->obtenerValor($consulta);
	
	$x=0;
	$query=array();
	$query[$x]="begin";
	$x++;
	$query[$x]="set @idTarea:=".$fTablero["idRegistro"];
	$x++;
	$query[$x]="INSERT INTO 3000_documentosAsignadosAtencion(idDocumentoFormato,situacionActual,fechaAsignacion,idCarpetaAdministrativa,
				iFormulario,iReferencia,idResponsableAtencion,actor,idInformacionDocumento,comentariosAdicionales,idTareaAsociada,idTableroTarea)
				values(".$idRegistroFormato.",0,'".$fTablero["fechaAsignacion"]."',".$idCarpetaAdministrativa.",".$idFormulario.
				",".$idRegistro.",".$_SESSION["idUsr"].",'".$rolActor."',".$fInformacion["idRegistro"].",'',@idTarea,".$idTablero.")";
	
	
	$query[$x]="commit";
	$x++;
	

	return $con->ejecutarBloque($query);
}

function funcionLlenadoPlantillaBaseDEGJ($idFormularioBase,$idRegistroBase)
{
	global $arrMesLetra;
	global $leyendaTribunal;
	global $con;
	$consulta="SELECT  noOficioAsignado,upper(dirigidoA) FROM _".$idFormularioBase."_tablaDinamica WHERE id__".$idFormularioBase."_tablaDinamica=".$idRegistroBase;
	$fOficio=$con->obtenerPrimeraFila($consulta);
	$arrValores=array();
	$arrValores["leyendaTribunal"]=$leyendaTribunal;
	$arrValores["fecha"]=convertirFechaLetra(date("Y-m-d"),false,false);
	$arrValores["folio"]=$fOficio[0];
	$arrValores["nombreDestinatario"]=$fOficio[1];
	$arrValores["nombreGenerador"]=strtoupper(obtenerNombreUsuario($_SESSION["idUsr"]));
	
	$consulta=" SELECT p.nombrePuesto FROM _421_tablaDinamica r,_416_tablaDinamica p WHERE r.usuarioAsignado=".$_SESSION["idUsr"]."
				AND p.id__416_tablaDinamica=r.puestoOrganozacional AND r.fechaInicioFunciones<='".date("Y-m-d").
				"' ORDER BY fechaInicioFunciones DESC";
	
	$filaPuesto=$con->obtenerPrimeraFila($consulta);
	
	$arrValores["puestogenerador"]=!$filaPuesto?"____________________":$filaPuesto[0];
	return $arrValores;
}

?>