<?php 
include_once("latis/conexionBD.php");
include_once("latis/utiles.php");
include_once("latis/sgjp/funcionesAgenda.php");
include_once("latis/sgjp/funcionesDocumentos.php");
include_once("latis/nusoap/nusoap.php");
include_once("latis/numeroToLetra.php"); 
include_once("latis/PHPWord.php");
include_once("latis/zip.lib.php"); 
include_once("latis/PHPMailer/PHPMailerAutoload.php"); 
include_once("latis/PdfToText/PdfToText.php");
include_once("latis/sgjp/funcionesAlgoritmosAsignacion.php");

function actualizarGridFigurasJuridicas($idFormulario,$idRegistro)
{
	
	global $con;
	$consulta="SELECT idActividad,figuraJuridica FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$fRegistro=$con->obtenerPrimeraFila($consulta);
	
	$x=0;
	$query[$x]="begin";
	$x++;
	
	
	$query[$x]="DELETE FROM 7005_relacionFigurasJuridicasSolicitud WHERE idParticipante=".$idRegistro;
	$x++;
	
	$query[$x]="INSERT INTO 7005_relacionFigurasJuridicasSolicitud(idActividad,idParticipante,idFiguraJuridica) values(".$fRegistro[0].",".$idRegistro.",".$fRegistro[1].")";
	$x++;
	
	if($con->existeTabla("_47_chParticipacionJuridica"))
	{
		$consulta="SELECT idOpcion FROM _47_chParticipacionJuridica WHERE idPadre=".$idRegistro;
		$rParticipaciones=$con->obtenerFilas($consulta);
		
		
		while($fParticipacion=mysql_fetch_row($rParticipaciones))
		{
			$query[$x]="INSERT INTO 7005_relacionFigurasJuridicasSolicitud(idActividad,idParticipante,idFiguraJuridica) values(".$fRegistro[0].",".$idRegistro.",".$fParticipacion[0].")";
			$x++;
		}
	}
	$query[$x]="commit";
	$x++;
	
	$con->ejecutarBloque($query);
	
	echo 'if(window.parent.parent)
		{
			
			window.parent.parent.ejecutarFuncionIframe(\'recargarGridParticipantes\',\'\');
		};'	;
}

function actualizarGridDelitos($idFormulario,$idRegistro)
{
	echo 'if(window.parent.parent)
		{
			
			window.parent.parent.ejecutarFuncionIframe(\'recargarGridDelitos\',\'\');window.parent.parent.cerrarVentanaFancy();return;;
		};'	;
}

function generarFolioProcesos($idFormulario,$idRegistro)
{
	global $con;
	
	$anio=date("Y");
	
	$query="begin";
	if($con->ejecutarConsulta($query))
	{
		$query="select folioActual FROM 7003_administradorFoliosProcesos WHERE idFormulario=".$idFormulario." AND anio=".$anio." for update";

		$folioActual=$con->obtenerValor($query);
		if($folioActual=="")
		{
			$folioActual=1;
			
			$query="INSERT INTO 7003_administradorFoliosProcesos(idFormulario,anio,folioActual) VALUES(".$idFormulario.",".$anio.",".$folioActual.")";
			
		}
		else
		{
			$folioActual++;
			$query="update 7003_administradorFoliosProcesos set folioActual=".$folioActual." where idFormulario=".$idFormulario." and anio=".$anio;
		}
			
		if($con->ejecutarConsulta($query))
		{
			$query="commit";
			$con->ejecutarConsulta($query);
			
			
			
			return str_pad($folioActual,5,"0",STR_PAD_LEFT)."/".$anio;
			
		}
			
		
	}
	
	return 0;
	
}


function generarPropuestaFechaAudiencia($idFormulario,$idRegistro)
{
	global $con;
	global $tipoMateria;
	return generarPropuestaFechaAudienciaV3($idFormulario,$idRegistro);
	
}



function asignarCarpetaAdministrativaSolicitudAudienciaInicial($idFormulario,$idRegistro)
{
	global $con;
	$consulta="SELECT tipoAudiencia FROM _46_tablaDinamica WHERE id__46_tablaDinamica=".$idRegistro;
	$tipoAudiencia=$con->obtenerValor($consulta);
	
	$consulta="SELECT COUNT(*) FROM 7000_eventosAudiencia WHERE idFormulario=".$idFormulario." AND idRegistroSolicitud=".$idRegistro;
	$nRegistros=$con->obtenerValor($consulta);
	
	if($nRegistros==0)
	{
		
		obtenerFechaAudienciaSolicitudInicialV3($idFormulario,$idRegistro,-1,$tipoAudiencia,$nivel=1);
		
	}
	
}

function actualizarPropuestaAgenda($idFormulario,$idRegistro)
{
	global $con;
	
	
	$consulta="SELECT idReferencia,dteFechaAudiencia,horaInicioPropuesta,horaFinalPropuesta,dictamenFinal 
				FROM _81_tablaDinamica WHERE id__81_tablaDinamica=".$idRegistro;
				
	$fReferencia=$con->obtenerPrimeraFila($consulta);				
	if($fReferencia[4]==1)
	{
		return true;
	}
	$idReferencia=$fReferencia[0];
	
	
	$consulta="SELECT idRegistroEvento FROM 7000_eventosAudiencia WHERE idFormulario=46 AND idRegistroSolicitud=".$idReferencia;
	$idRegistroEvento=$con->obtenerValor($consulta);
	
	$horaFinEvento=$fReferencia[1];
	if(strtotime($fReferencia[2])>strtotime($fReferencia[3]))
	{
		$horaFinEvento=date("Y-m-d",strtotime("+1 days",strtotime($horaFinEvento)));
	}
	$horaFinEvento.=" ".$fReferencia[3];
	
	$consulta="UPDATE 7000_eventosAudiencia SET fechaEvento='".$fReferencia[1]."', horaInicioEvento='".$fReferencia[1]." ".$fReferencia[2]."',horaFinEvento='".$horaFinEvento."' WHERE idRegistroEvento=".$idRegistroEvento;
	
	return $con->ejecutarConsulta($consulta);
	
	
	
	
}

function asignarCarpetaAdministrativa($idFormulario,$idRegistro)
{
	global $con;
	
	$consulta="SELECT carpetaAdministrativa FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$nCapeta=$con->obtenerValor($consulta);

	if($nCapeta=="")
	{
		$consulta="SELECT idCentroGestion FROM 7000_eventosAudiencia WHERE idFormulario=".$idFormulario." AND idRegistroSolicitud=".$idRegistro;
		
		$idCentroGestion=$con->obtenerValor($consulta);
		generarFolioCarpetaAdministrativa($idFormulario,$idRegistro,$idCentroGestion);
	}
	
}

function registrarSolicitudNotificacionesCitaciones($idFormulario,$idRegistro)
{
	global $con;
		
	$consulta="SELECT idDocumento FROM 9074_documentosRegistrosProceso WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro.
			" AND tipoDocumento=2";
	
	
	$rDocumentos=$con->obtenerFilas($consulta);
	
	$consulta="select carpetaAdministrativa,iFormulario,iRegistro,tipoDocumento from _".$idFormulario."_tablaDinamica 
			where id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$fDatosSolicitud=$con->obtenerPrimeraFila($consulta);
	
	while($fDocumento=mysql_fetch_row($rDocumentos))
	{
		registrarDocumentoResultadoProceso($fDatosSolicitud[1],$fDatosSolicitud[2],$fDocumento[0]);
	}
	
	if($fDatosSolicitud[3]==9)
	{
		
		return true;
	}
	
	$carpetaAdministrativa=$fDatosSolicitud[0];
	
	
	$consulta="SELECT idRegistroEvento FROM 7000_eventosAudiencia where idFormulario=".$fDatosSolicitud[1]." and idRegistroSolicitud=".$fDatosSolicitud[2];
	$idEventoAudiencia=$con->obtenerValor($consulta);
	if($idEventoAudiencia=="")
		$idEventoAudiencia=-1;
	
	$arrValores=array();
	$arrValores["idEvento"]=$idEventoAudiencia;
	$arrValores["tipoSolicitud"]=($idEventoAudiencia!=-1)?1:2;
	
	if($arrValores["tipoSolicitud"]==1)
	{
		$idFormulario=$fDatosSolicitud[1];
		$idRegistro=$fDatosSolicitud[2];
	}
	$arrValores["carpetaAdministrativa"]=$carpetaAdministrativa;
	$arrValores["idFormularioPadre"]=$idFormulario;
	
	$arrDocumentos=array();
	$consulta="SELECT idDocumento FROM 9074_documentosRegistrosProceso WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro.
	" AND tipoDocumento=2";

	$rDocumentos=$con->obtenerFilas($consulta);
	while($fDocumento=mysql_fetch_row($rDocumentos))
	{
		array_push($arrDocumentos,$fDocumento[0]);
	}
	
	

	$idRegistroSolicitudCitacion=crearInstanciaRegistroFormulario(67,$idRegistro,1,$arrValores,$arrDocumentos,-1,315);
	
	return true;
	
	
	
}

function obtenerNombreImplicado($idUsuario)
{
	global $con;
	$consulta="SELECT CONCAT(if(nombre is null,'',nombre),' ',if(apellidoPaterno is null,'',apellidoPaterno),' ',if(apellidoMaterno is null,'',apellidoMaterno)) FROM _47_tablaDinamica WHERE id__47_tablaDinamica=".$idUsuario;
	$nombre=$con->obtenerValor($consulta);
	return $nombre;
}

function determinarResultadoAnalisisSolicitud($idFormulario,$idRegistro)
{
	global $con;	
	$etapaContinuacion=0;
	$consulta="SELECT tipoAudiencia,delitoGrave,carpetaAdministrativa FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario.
				"_tablaDinamica=".$idRegistro;

	$filaSolicitud=$con->obtenerPrimeraFila($consulta);
	
	
	$consulta="SELECT resolucionSolicitud FROM _4_tablaDinamica WHERE id__4_tablaDinamica=".$filaSolicitud[0];
	$resolucionSolicitud=$con->obtenerValor($consulta);
	
	switch($resolucionSolicitud)
	{
		case 1:
			$etapaContinuacion=3;
		break;
		case 2:
			$etapaContinuacion=6;
		break;
		case 3:
			$etapaContinuacion=1.5;
		break;
	}
		
		
	
	if($filaSolicitud[2]=="")
	{
		
		$idEvento=asignarCarpetaAdministrativaSolicitudAudienciaInicial($idFormulario,$idRegistro);
		
		asignarCarpetaAdministrativa($idFormulario,$idRegistro);
	}

	cambiarEtapaFormulario($idFormulario,$idRegistro,$etapaContinuacion,"",-1,"NULL","NULL",334);
		
}

function esPmoventePGJ($idFormulario,$idRegistro)
{
	global $con;
	$consulta="SELECT tipoAudiencia,delitoGrave,carpetaAdministrativa FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario.
				"_tablaDinamica=".$idRegistro;
	$filaSolicitud=$con->obtenerPrimeraFila($consulta);
	
	switch($filaSolicitud[0])
	{
		case 18:
			return false;
		break;
		default:
			return true;
		break;
	}
}

function existeDisponibilidadAuxiliar($idUsuario,$fechaInicio,$fechaFin,$idEvento)
{
	global $con;
	$qAux=generarConsultaIntervalos($fechaInicio,$fechaFin,"a.horaInicioEvento","a.horaFinEvento",false,true);
	$consulta="SELECT a.idRegistroEvento, a.fechaEvento, a.horaInicioEvento, a.horaFinEvento, idSala, situacion, fechaAsignacion
			 FROM 7000_eventosAudiencia a WHERE   idAuxiliarSala = ".$idUsuario." and  ".$qAux." and idRegistroEvento<>".$idEvento." 
			 and a.situacion in(SELECT idSituacion FROM 7011_situacionEventosAudiencia WHERE considerarDiponibilidad=1)";

	$res=$con->obtenerFilas($consulta);
	if($con->filasAfectadas>0)
		return false;
	return true;
}

function validarAuxiliarSalaAsignado($idFormulario,$idRegistro)
{
	global $con;
	$cadRes="";
	$consulta="SELECT idAuxiliarSala FROM 3007_auxiliarSalaEvento WHERE idFormulario=".$idFormulario." AND idReferencia=".$idRegistro;
	$idAuxiliar=$con->obtenerValor($consulta);
	if($idAuxiliar!=0)
	{
		$consulta="SELECT horaInicioEvento,horaFinEvento,idRegistroEvento FROM 7000_eventosAudiencia WHERE idFormulario=".$idFormulario." AND idRegistroSolicitud=".$idRegistro;
		$fEvento=$con->obtenerPrimeraFila($consulta);
		
		if(!existeDisponibilidadAuxiliar($idAuxiliar,$fEvento[0],$fEvento[1],$fEvento[2]))
		{
			$cadRes="['Datos de la audiencia','El auxiliar de sala asignado NO cuenta con disponibilidad de tiempo']";
		}
	}
	
	return "[".$cadRes."]";	
}

function determinarRutaSolicitudInicial3_5($idFormulario,$idRegistro)
{
	global $con;
	
	$etapaContinuacion=0;
	$consulta="SELECT tipoAudiencia,delitoGrave,carpetaAdministrativa FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario.
				"_tablaDinamica=".$idRegistro;
	$filaSolicitud=$con->obtenerPrimeraFila($consulta);
	
	switch($filaSolicitud[0])
	{
		case 26:
			$etapaContinuacion=13;
		break;
		default:
			$etapaContinuacion=5;
		break;
	}
	
	cambiarEtapaFormulario($idFormulario,$idRegistro,$etapaContinuacion,"",-1,"NULL","NULL",341);
		
}

function determinarRutaDocumento4_5($idFormulario,$idRegistro)
{
	global $con;
	$consulta="SELECT iFormulario,iRegistro,tipoDocumento FROM _123_tablaDinamica WHERE id__123_tablaDinamica=".$idRegistro;
	$fDocumento=$con->obtenerPrimeraFila($consulta);
	$etapaContinuacion=0;
	switch($fDocumento[2])
	{
		case 3:
		case 4:
			$etapaContinuacion=4;
		break;
		
		default:
			$etapaContinuacion=4.5;
		break;
	}
	cambiarEtapaFormulario($idFormulario,$idRegistro,$etapaContinuacion,"",-1,"NULL","NULL",377);
}

function generarOficiosControlDetencion($idFormulario,$idRegistro)
{
	global $con;
	registrarDocumentosConfiguradosSolicitudAudiencia($idFormulario,$idRegistro);
	return true;
		
}

function obtenerDelitosImputados($idImputado,$carpetaAdministrativa)
{
	global $con;

	$consulta="SELECT d.denominacionDelito FROM _61_chkDelitosImputado c,_35_denominacionDelito d ,_61_tablaDinamica del
				WHERE idOpcion=".$idImputado." AND d.id__35_denominacionDelito=del.denominacionDelito AND del.id__61_tablaDinamica=c.idPadre";
	$listaDelitos=$con->obtenerListaValores($consulta);
	
	return $listaDelitos;
}

function validarDocumentoAdjunto($idFormulario,$idRegistro)
{
	global $con;
	$cadRes="";
	$consulta="SELECT COUNT(*) FROM 9074_documentosRegistrosProceso WHERE idFormulario=".$idFormulario.
				" AND idRegistro=".$idRegistro." AND tipoDocumento=1";
	
	$nReg=$con->obtenerValor($consulta);
	if($nReg==0)
	{
		$cadRes="['General','Debe adjuntar el documento de acuse de entrega del documento']";
	}
	
	
	return "[".$cadRes."]";	
}

function mostrarDocumentoProceso($idFormulario)
{
	global $con;
}

function obtenerDatosJuez($idEvento)
{
	global $con;
	
	$consulta="SELECT idJuez FROM 7001_eventoAudienciaJuez WHERE idRegistroEvento=".$idEvento;
	$idJuez=$con->obtenerValor($consulta);
	
	$consulta="SELECT clave FROM _26_tablaDinamica WHERE usuarioJuez=".$idJuez;
	$clave=$con->obtenerValor($consulta);
	$arrClave=explode(" ",$clave);
	
	if(sizeof($arrClave)>1)
		$noJuez=$arrClave[1];
	else
		$noJuez=$arrClave[0];
	$arrDatos=array();
	$arrDatos["noJuez"]=$noJuez;
	$arrDatos["nombreJuez"]=obtenerNombreUsuario($idJuez);	
	return $arrDatos;
}

function obtenerDatosSujetosProcesalesDelitosCarpetaAdministrativa($carpeta)
{
	global $con;	
	$consulta="SELECT idActividad FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$carpeta."'";

	$idActividad=$con->obtenerValor($consulta);
	
	if($idActividad=="")
		$idActividad=-1;
	$arrSujetosProcesales=array();
	$arrFiguras="";
	$consulta="SELECT id__5_tablaDinamica,etiquetaPlural FROM _5_tablaDinamica ORDER BY codigo";
	$rFiguras=$con->obtenerFilas($consulta);
	while($fFiguras=mysql_fetch_row($rFiguras))
	{
		$arrSujetosProcesales[$fFiguras[1]]=array();
		
		$consulta="SELECT idPadre FROM _47_chParticipacionJuridica p,_47_tablaDinamica s WHERE s.idActividad=".$idActividad.
				" AND  p.idPadre=s.id__47_tablaDinamica and p.idOpcion=".$fFiguras[0];
		
		$lPersonas=$con->obtenerListaValores($consulta);
		if($lPersonas=="")
			$lPersonas=-1;
			
		$arrPersonas="";
		$consulta="SELECT id__47_tablaDinamica,tipoPersona,apellidoPaterno,apellidoMaterno,nombre,requiereDefensoria FROM _47_tablaDinamica WHERE idActividad=".$idActividad.
				" and (figuraJuridica=".$fFiguras[0]." or id__47_tablaDinamica in(".$lPersonas.")) order by nombre,apellidoPaterno,apellidoMaterno";
		

		$rPersona=$con->obtenerFilas($consulta);
		while($fPersona=mysql_fetch_row($rPersona))
		{
			$oFigura=array();
			
			$oFigura["idRegistro"]=$fPersona[0];
			$oFigura["tipoPersona"]=$fPersona[1];
			$oFigura["apellidoPaterno"]=$fPersona[2];
			$oFigura["apellidoMaterno"]=$fPersona[3];
			$oFigura["nombre"]=$fPersona[4];
			
			
			switch($fFiguras[0])
			{
				case 3:
					$oFigura["asesorados"]=array();					
					$consulta="SELECT id__47_tablaDinamica,tipoPersona,apellidoPaterno,apellidoMaterno,nombre FROM _47_tablaDinamica d,_47_chkVictimas v WHERE 
								v.idPadre=".$fPersona[0]."  AND v.idOpcion=d.id__47_tablaDinamica  order by nombre,apellidoPaterno,apellidoMaterno";
								
					$rDependientes=$con->obtenerFilas($consulta);			
					while($fDependientes=mysql_fetch_row($rDependientes))
					{
						$asesorado=array();
						$asesorado["idRegistro"]=$fDependientes[0];
						$asesorado["tipoPersona"]=$fDependientes[1];
						$asesorado["apellidoPaterno"]=$fDependientes[2];
						$asesorado["apellidoMaterno"]=$fDependientes[3];
						$asesorado["nombre"]=$fDependientes[4];
						array_push($oFigura["asesorados"],$asesorado);
					}
					
				break;
				case 4:
					$oFigura["requiereDefensorOficio"]=$fPersona[5]==1?1:0;
					$oFigura["delitos"]=array();
					
					$consulta="SELECT idPadre FROM _61_chkDelitosImputado WHERE idOpcion=".$fPersona[0];
					$listaDelitosImputado=$con->obtenerListaValores($consulta);
					if($listaDelitosImputado=="")
						$listaDelitosImputado=-1;
					
					$consulta="SELECT tituloDelito,capituloDelito,denominacionDelito,calificativo,
										'-1' as formaComision,modalidadDelito,gradoRealizacion FROM _61_tablaDinamica where
										id__61_tablaDinamica IN(".$listaDelitosImputado.")";

					$resDelitos=$con->obtenerFilas($consulta);
					while($fDelito=mysql_fetch_row($resDelitos))
					{
						$consulta="SELECT d.idReferencia AS capitulo,c.idReferencia AS titulo FROM _35_denominacionDelito AS d,_35_tablaDinamica c 
									WHERE id__35_denominacionDelito=".$fDelito[2]." AND c.id__35_tablaDinamica=d.idReferencia";

						$filaDelito=$con->obtenerPrimeraFila($consulta);
						
						$fDelito[0]=$filaDelito[1];
						$fDelito[1]=$filaDelito[0];
						
						
						$delito=array();
						$consulta="SELECT claveTituloDelito,tituloDelito FROM _34_tablaDinamica WHERE id__34_tablaDinamica=".($fDelito[0]==""?-1:$fDelito[0]);

						$fTituloDelito=$con->obtenerPrimeraFila($consulta);
						$delito["idTituloDelito"]=$fDelito[0];
						$delito["cveTituloDelito"]=$fTituloDelito[0];
						$delito["tituloDelito"]=$fTituloDelito[1];
						
						$consulta="SELECT claveCapituloDelito,capituloDelito FROM _35_tablaDinamica WHERE id__35_tablaDinamica=".($fDelito[1]==""?-1:$fDelito[1]);

						$fCapituloDelito=$con->obtenerPrimeraFila($consulta);
						$delito["idCapituloDelito"]=$fDelito[1];
						$delito["cveCapituloDelito"]=$fCapituloDelito[0];
						$delito["capituloDelito"]=$fCapituloDelito[1];
						
						$consulta="SELECT claveDenominacionDelito,denominacionDelito FROM _35_denominacionDelito 
								WHERE id__35_denominacionDelito=".($fDelito[2]==""?-1:$fDelito[2]);

						$fDenominacionDelito=$con->obtenerPrimeraFila($consulta);
						$delito["idDenominacionDelito"]=$fDelito[2];
						$delito["cveDenominacionDelito"]=$fDenominacionDelito[0];
						$delito["denominacionDelito"]=$fDenominacionDelito[1];
						
						$consulta="SELECT clave,nombreModalidad FROM _62_clasificacion WHERE id__62_clasificacion=".($fDelito[3]==""?-1:$fDelito[3]);

						$fClasificacionDelito=$con->obtenerPrimeraFila($consulta);
						$delito["idClasificacionDelito"]=$fDelito[3];
						$delito["cveClasificacionDelito"]=$fClasificacionDelito[0];
						$delito["clasificacionDelito"]=$fClasificacionDelito[1];
						
						$consulta="SELECT claveFormaComision,formaComision FROM _41_tablaDinamica WHERE id__41_tablaDinamica=".($fDelito[4]==""?-1:$fDelito[4]);


						$fFormaComision=$con->obtenerPrimeraFila($consulta);
						$delito["idFormaComision"]=$fDelito[4];
						$delito["cveFormaComision"]=$fFormaComision[0];
						$delito["formaComision"]=$fFormaComision[1];
						
						$consulta="SELECT claveModalidadDelito,modalidadDelito FROM _42_tablaDinamica WHERE id__42_tablaDinamica=".($fDelito[5]==""?-1:$fDelito[5]);
						$fModalidadDelito=$con->obtenerPrimeraFila($consulta);
						$delito["idModalidadDelito"]=$fDelito[5];
						$delito["cveModalidadDelito"]=$fModalidadDelito[0];
						$delito["modalidadDelito"]=$fModalidadDelito[1];
						
						$consulta="SELECT claveGradoRealizacion,gradoRealizacion FROM _43_tablaDinamica WHERE id__43_tablaDinamica=".($fDelito[6]==""?-1:$fDelito[6]);
						$fGradoRealizacion=$con->obtenerPrimeraFila($consulta);
						
						$delito["idGradoRealizacion"]=$fDelito[6];
						$delito["cveGradoRealizacion"]=$fGradoRealizacion[0];
						$delito["gradoRealizacion"]=$fGradoRealizacion[1];
						
						array_push($oFigura["delitos"],$delito);
						
						
					}
					
				break;
				case 5:
					$oFigura["defendidos"]=array();			
					
					$consulta="SELECT id__47_tablaDinamica,tipoPersona,apellidoPaterno,apellidoMaterno,nombre FROM _47_tablaDinamica d,_47_chkImputados i WHERE 
								i.idPadre=".$fPersona[0]."  AND i.idOpcion=d.id__47_tablaDinamica  order by nombre,apellidoPaterno,apellidoMaterno";
								
					$rDependientes=$con->obtenerFilas($consulta);			
					while($fDependientes=mysql_fetch_row($rDependientes))
					{
						$asesorado=array();
						$asesorado["idRegistro"]=$fDependientes[0];
						$asesorado["tipoPersona"]=$fDependientes[1];
						$asesorado["apellidoPaterno"]=$fDependientes[2];
						$asesorado["apellidoMaterno"]=$fDependientes[3];
						$asesorado["nombre"]=$fDependientes[4];
						array_push($oFigura["defendidos"],$asesorado);
					}
				break;
				case 6:
					$oFigura["representados"]=array();		
					$consulta="SELECT id__47_tablaDinamica,tipoPersona,apellidoPaterno,apellidoMaterno,nombre FROM _47_tablaDinamica d,_47_chkImputadosVictimas i WHERE 
								i.idPadre=".$fPersona[0]."  AND i.idOpcion=d.id__47_tablaDinamica  order by nombre,apellidoPaterno,apellidoMaterno";
								
					$rDependientes=$con->obtenerFilas($consulta);			
					while($fDependientes=mysql_fetch_row($rDependientes))
					{
						$asesorado=array();
						$asesorado["idRegistro"]=$fDependientes[0];
						$asesorado["tipoPersona"]=$fDependientes[1];
						$asesorado["apellidoPaterno"]=$fDependientes[2];
						$asesorado["apellidoMaterno"]=$fDependientes[3];
						$asesorado["nombre"]=$fDependientes[4];
						array_push($oFigura["representados"],$asesorado);
					}
				break;
			}
			array_push($arrSujetosProcesales[$fFiguras[1]],$oFigura);	
		}
	}
	
	
	
	
	$arrSujetosProcesales["delitos"]=array();
	
	$consulta="SELECT tituloDelito,capituloDelito,denominacionDelito,calificativo,
				'-1' as formaComision,modalidadDelito,gradoRealizacion FROM _61_tablaDinamica WHERE idActividad=".$idActividad;

	$resDelitos=$con->obtenerFilas($consulta);
	while($fDelito=mysql_fetch_row($resDelitos))
	{
		$consulta="SELECT d.idReferencia AS capitulo,c.idReferencia AS titulo FROM _35_denominacionDelito AS d,_35_tablaDinamica c 
					WHERE id__35_denominacionDelito=".$fDelito[2]." AND c.id__35_tablaDinamica=d.idReferencia";
		$filaDelito=$con->obtenerPrimeraFila($consulta);
		
		$fDelito[0]=$filaDelito[1];
		$fDelito[1]=$filaDelito[0];
		if($fDelito[0]=="")
			$fDelito[0]=-1;
		
		if($fDelito[1]=="")
			$fDelito[1]=-1;
			

		$delito=array();
		$consulta="SELECT claveTituloDelito,tituloDelito FROM _34_tablaDinamica WHERE id__34_tablaDinamica=".$fDelito[0];
		$fTituloDelito=$con->obtenerPrimeraFila($consulta);
		$delito["idTituloDelito"]=$fDelito[0];
		$delito["cveTituloDelito"]=$fTituloDelito[0];
		$delito["tituloDelito"]=$fTituloDelito[1];
		
		$consulta="SELECT claveCapituloDelito,capituloDelito FROM _35_tablaDinamica WHERE id__35_tablaDinamica=".$fDelito[1];
		$fCapituloDelito=$con->obtenerPrimeraFila($consulta);
		$delito["idCapituloDelito"]=$fDelito[1];
		$delito["cveCapituloDelito"]=$fCapituloDelito[0];
		$delito["capituloDelito"]=$fCapituloDelito[1];
		
		$consulta="SELECT claveDenominacionDelito,denominacionDelito FROM _35_denominacionDelito WHERE id__35_denominacionDelito=".$fDelito[2];
		$fDenominacionDelito=$con->obtenerPrimeraFila($consulta);
		$delito["idDenominacionDelito"]=$fDelito[2];
		$delito["cveDenominacionDelito"]=$fDenominacionDelito[0];
		$delito["denominacionDelito"]=$fDenominacionDelito[1];
		
		$consulta="SELECT clave,nombreModalidad FROM _62_clasificacion WHERE id__62_clasificacion=".$fDelito[3];
		$fClasificacionDelito=$con->obtenerPrimeraFila($consulta);
		$delito["idClasificacionDelito"]=$fDelito[3];
		$delito["cveClasificacionDelito"]=$fClasificacionDelito[0];
		$delito["clasificacionDelito"]=$fClasificacionDelito[1];
		
		$consulta="SELECT claveFormaComision,formaComision FROM _41_tablaDinamica WHERE id__41_tablaDinamica=".$fDelito[4];
		$fFormaComision=$con->obtenerPrimeraFila($consulta);
		$delito["idFormaComision"]=$fDelito[4];
		$delito["cveFormaComision"]=$fFormaComision[0];
		$delito["formaComision"]=$fFormaComision[1];
		
		$consulta="SELECT claveModalidadDelito,modalidadDelito FROM _42_tablaDinamica WHERE id__42_tablaDinamica=".$fDelito[5];
		$fModalidadDelito=$con->obtenerPrimeraFila($consulta);
		$delito["idModalidadDelito"]=$fDelito[5];
		$delito["cveModalidadDelito"]=$fModalidadDelito[0];
		$delito["modalidadDelito"]=$fModalidadDelito[1];
		
		$consulta="SELECT claveGradoRealizacion,gradoRealizacion FROM _43_tablaDinamica WHERE id__43_tablaDinamica=".$fDelito[6];
		$fGradoRealizacion=$con->obtenerPrimeraFila($consulta);
		
		$delito["idGradoRealizacion"]=$fDelito[6];
		$delito["cveGradoRealizacion"]=$fGradoRealizacion[0];
		$delito["gradoRealizacion"]=$fGradoRealizacion[1];
		
		array_push($arrSujetosProcesales["delitos"],$delito);
		
		
	}
	
	
	
	return $arrSujetosProcesales;
	
	
}

//  Funciones pendientes
function obtenerDomicilioSala($idSala)
{
	return "[Pendiente]";
}

function generarNumeroOficioCarpetaAdministrativa($carpetaAdministrativa,$idFormulario,$idRegistro)
{
	global $con;
	
	return "[POR DEFINIR]";
	
}

function obtenerJuezTramite($idFormulario,$idRegistro,$actorDestinatario)
{
	global $con;

	$arrDestinatario=array();
	$rolActor=obtenerTituloRol($actorDestinatario);
	//$nombreUsuario=obtenerNombreUsuario(2400)." (".$rolActor.")";
	/*$o='{"idUsuarioDestinatario":"2400","nombreUsuarioDestinatario":"'.$nombreUsuario.'"}';
	$o=json_decode($o);
	array_push($arrDestinatario,$o);
	
	return $arrDestinatario;*/

	$carpetaAdministrativa=obtenerCarpetaAdministrativaProceso($idFormulario,$idRegistro);
	
	//DeterminaciÛn juez tramite
	$arrDestinatario=array();
	$idUsuarioDestinatario=-1;
	$horaActual=date("Y-m-d H:i:s");
	$fechactual=date("Y-m-d");
	
	$rolActor=obtenerTituloRol($actorDestinatario);
	$tipoHorario="";
	$consulta="SELECT unidadGestion FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$carpetaAdministrativa."'";
	$unidadGestion=$con->obtenerValor($consulta);

	switch($unidadGestion)
	{
		case "001":
		case "002":
		case "003":
		case "004":
			$tipoHorario=determinarHorarioA($horaActual);
		break;
		
		case "008":
			$tipoHorario=1;
		
		case "006":
			$tipoHorario=1;
		break;
		case "005":
		case "007":
		case "009":
		case "010":
		case "011":
		case "209":
			$tipoHorario=determinarHorarioB($horaActual);
		break;
	}
	
	$tipoHorario=1;
	if($tipoHorario==1)
	{
		$consulta="SELECT id__17_tablaDinamica FROM _17_tablaDinamica WHERE claveUnidad='".$unidadGestion."'";
		$idUGJ=$con->obtenerValor($consulta);
		$consulta="SELECT j.usuarioJuez FROM _26_tablaDinamica j,_292_tablaDinamica jt WHERE jt.idEstado=1 and jt.nombreJueces=j.usuarioJuez
					and j.idReferencia=".$idUGJ." and '".$fechactual."'>=fechaInicial and '".$fechactual."'<=fechaFinal";
		$idUsuarioDestinatario=	$con->obtenerValor($consulta);	
		if($idUsuarioDestinatario=="")
			$idUsuarioDestinatario=-1;
	}
	
	if($idUsuarioDestinatario=="-1")
	{
		switch($idFormulario)
		{
			case 123:
			
					$consulta="SELECT 	iFormulario,iRegistro FROM _123_tablaDinamica WHERE id__123_tablaDinamica=".$idRegistro;
					$fRegistro=$con->obtenerPrimeraFila($consulta);
					$consulta="SELECT idJuez FROM 7000_eventosAudiencia e,7001_eventoAudienciaJuez ej WHERE ej.idRegistroEvento=e.idRegistroEvento
								AND idFormulario=".$fRegistro[0]." AND idRegistroSolicitud=".$fRegistro[1];

					$idUsuarioDestinatario=$con->obtenerValor($consulta);
					if($idUsuarioDestinatario=="")
						$idUsuarioDestinatario=-1;
					

			
			break;
		}
	}
	
	
	if(($idUsuarioDestinatario=="-1")&&(esCarpetaTramite($carpetaAdministrativa)))
	{
		$consulta="SELECT id__17_tablaDinamica FROM _17_tablaDinamica WHERE claveUnidad='".$unidadGestion."'";
		$idUGJ=$con->obtenerValor($consulta);
		$consulta="SELECT j.usuarioJuez FROM _26_tablaDinamica j,_292_tablaDinamica jt WHERE jt.idEstado=1 and jt.nombreJueces=j.usuarioJuez
					and j.idReferencia=".$idUGJ." and '".$fechactual."'>=fechaInicial and '".$fechactual."'<=fechaFinal";
		$idUsuarioDestinatario=	$con->obtenerValor($consulta);	
		if($idUsuarioDestinatario=="")
			$idUsuarioDestinatario=-1;
	}
	
	/*$nombreUsuario=obtenerNombreUsuario($idUsuarioDestinatario)." (".$rolActor.")";
	$o='{"idUsuarioDestinatario":"'.$idUsuarioDestinatario.'","nombreUsuarioDestinatario":"'.$nombreUsuario.'"}';
	$o=json_decode($o);
	array_push($arrDestinatario,$o);
		*/
	if($idUsuarioDestinatario!=-1)
	{
		$nombreUsuario=obtenerNombreUsuario($idUsuarioDestinatario)." (".$rolActor.")";
		$o='{"idUsuarioDestinatario":"'.$idUsuarioDestinatario.'","nombreUsuarioDestinatario":"'.$nombreUsuario.'"}';
		$o=json_decode($o);
		array_push($arrDestinatario,$o);
	}
	
	$nombreUsuario=obtenerNombreUsuario(1)." (".$rolActor.")";
	$o='{"idUsuarioDestinatario":"1","nombreUsuarioDestinatario":"'.$nombreUsuario.'"}';
	$o=json_decode($o);
	array_push($arrDestinatario,$o);
	
	return $arrDestinatario;
	 
	
}

function esCarpetaTramite($carpeta)
{

	if(strpos($carpeta,"-EX")!==false)
		return true;
	return false;

}

function obtenerMagistradoSemanero($idFormulario,$idRegistro,$actorDestinatario)
{
	global $con;
	
	
	//DeterminaciÛn juez tramite
	$arrDestinatario=array();
	$idUsuarioDestinatario=1;
	$rolActor=obtenerTituloRol($actorDestinatario);
	
	
	
	
	/*$nombreUsuario=obtenerNombreUsuario($idUsuarioDestinatario)." (".$rolActor.")";
	$o='{"idUsuarioDestinatario":"'.$idUsuarioDestinatario.'","nombreUsuarioDestinatario":"'.$nombreUsuario.'"}';
	$o=json_decode($o);
	array_push($arrDestinatario,$o);
		*/
	$nombreUsuario=obtenerNombreUsuario($idUsuarioDestinatario)." (".$rolActor.")";
	$o='{"idUsuarioDestinatario":"'.$idUsuarioDestinatario.'","nombreUsuarioDestinatario":"'.$nombreUsuario.'"}';
	$o=json_decode($o);
	array_push($arrDestinatario,$o);
	
	return $arrDestinatario;
	 
	
}

function obtenerDatosJuezControlPlantillas($idDocumento,$idReferencia,$idRegistro,$idFormulario)
{
	$arrDatos=array();
	$arrDatos["noJuez"]="[Pendiente]";
	$arrDatos["nombreJuez"]="[Pendiente]";	
	return $arrDatos;
}

function obtenerProximoDiaHabil($fecha)
{
	$diaHabil=false;
	while(!$diaHabil)
	{
		if(esDiaHabil($fecha)==1)
		{
			return $fecha;
		}
		$fecha=date("Y-m-d",strtotime("+1 days",strtotime($fecha)));
	}
	
	return NULL;
}



///-----
function mostrarSeccionEdicionDocumento1($idFormulario,$idRegistro,$idFormularioEvaluacion)
{
	global $con;
	
	$consulta="SELECT documentoBloqueado FROM 3000_formatosRegistrados WHERE idFormulario=".$idFormulario.
				" AND idRegistro=".$idRegistro." AND idFormularioProceso=".$idFormularioEvaluacion;

	$documentoBloqueado=$con->obtenerValor($consulta);	
	if($documentoBloqueado==1)
		return 0;
	return 1;
	
}

function determinarRutaRegistroResultadoNotificacion($idFormulario,$idRegistro)
{
	global $con;
	
	$consulta="SELECT * FROM _148_tablaDinamica WHERE id__148_tablaDinamica=".$idRegistro;
	$fila=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$etapaContinuacion=0;
	
	if($fila["citatorioEsperaNotificador"]==1)
	{
		$etapaContinuacion=3;
	}
	else
	{
		$consulta="select * from _72_tablaDinamica WHERE id__72_tablaDinamica=".$fila["idReferencia"];
		$filaSolicitud=$con->obtenerPrimeraFilaAsoc($consulta);
		$idPersonaNotificar=$filaSolicitud["idPersonaNotificar"];
		$idFiguraJuridica=$filaSolicitud["idFiguraJuridica"];
		
		$consulta="SELECT tipoSolicitud FROM _67_tablaDinamica WHERE id__67_tablaDinamica=".$filaSolicitud["iRegistro"];
		$tipoSolicitud=$con->obtenerValor($consulta);
		
		if($idFiguraJuridica==0)
		{
			$consulta="SELECT formatoActaCircunstanciada FROM _149_gridConfiguracionCitacionesNotificaciones WHERE idReferencia=".$idFiguraJuridica." AND
					aplicableA=".$tipoSolicitud." AND formatoNotificacion=".$filaSolicitud["tipoDocumento"];
			$formato=$con->obtenerValor($consulta);
		}
		else
		{
			$consulta="SELECT formatoActaCircunstanciada FROM _5_gridCitacionNotificacion WHERE idReferencia=".$idFiguraJuridica.
						" AND aplicableA=".$tipoSolicitud." AND formatoNotificacion=".$filaSolicitud["tipoDocumento"];
			$formato=$con->obtenerValor($consulta);
			
		}
		
		
		if($formato=="")
		{
			$etapaContinuacion=4;
		}
		else
		{
			$etapaContinuacion=3.5;
		}
		
		
	}
		
	convertirDocumentoUsuarioDocumentoResultadoProceso($fila["gridAcuseDiligencia"],72,$fila["idReferencia"],"Acuse de notificaciÛn",10);
	cambiarEtapaFormulario(72,$fila["idReferencia"],$etapaContinuacion,"",-1,"NULL","NULL",379);
	
	
	echo "window.parent.regresar1Pagina(true);return;";
	
	
}

function determinarRutaRegistroResultadoNotificacion2daVisita($idFormulario,$idRegistro)
{
	global $con;
	
	$consulta="SELECT * FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$fila=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$etapaContinuacion=0;
	
	
	$consulta="select * from _72_tablaDinamica WHERE id__72_tablaDinamica=".$fila["idReferencia"];
	$filaSolicitud=$con->obtenerPrimeraFilaAsoc($consulta);
	$idPersonaNotificar=$filaSolicitud["idPersonaNotificar"];
	$idFiguraJuridica=$filaSolicitud["idFiguraJuridica"];
	
	$consulta="SELECT tipoSolicitud FROM _67_tablaDinamica WHERE id__67_tablaDinamica=".$filaSolicitud["iRegistro"];
	$tipoSolicitud=$con->obtenerValor($consulta);
	
	if($idFiguraJuridica==0)
	{
		$consulta="SELECT formatoActaCircunstanciada FROM _149_gridConfiguracionCitacionesNotificaciones WHERE idReferencia=".$idFiguraJuridica." AND
				aplicableA=".$tipoSolicitud." AND formatoNotificacion=".$filaSolicitud["tipoDocumento"];
		$formato=$con->obtenerValor($consulta);
	}
	else
	{
		$consulta="SELECT formatoActaCircunstanciada FROM _5_gridCitacionNotificacion WHERE idReferencia=".$idFiguraJuridica.
					" AND aplicableA=".$tipoSolicitud." AND formatoNotificacion=".$filaSolicitud["tipoDocumento"];
		$formato=$con->obtenerValor($consulta);
		
	}
	
	
	if($formato=="")
	{
		$etapaContinuacion=4;
	}
	else
	{
		$etapaContinuacion=3.5;
	}
	
	
	convertirDocumentoUsuarioDocumentoResultadoProceso($fila["acuseDiligencia"],72,$fila["idReferencia"],"Acuse de notificaciÛn 2da visita",10);
	cambiarEtapaFormulario(72,$fila["idReferencia"],$etapaContinuacion,"",-1,"NULL","NULL",380);
	
	
	echo "window.parent.regresar1Pagina(true);return;";
	
	
}

function convertirDocumentoUsuarioDocumentoResultadoProceso($idDocumentoBase,$idFormulario,$idRegistro,$nombreFinal,$tipo)
{
	global $con;
	global $baseDir;
	global $urlRepositorioDocumentos;
	$eliminarDocumentoOriginal=false;
	if(!file_exists($baseDir."/documentosUsr/archivo_".$idDocumentoBase))
		return false;
	if(copy($baseDir."/documentosUsr/archivo_".$idDocumentoBase,$urlRepositorioDocumentos."/repositorioDocumentos/documento_".$idDocumentoBase))
	{

		$consulta="SELECT nomArchivoOriginal FROM 908_archivos WHERE idArchivo=".$idDocumentoBase;
		$nombreArchivo=$con->obtenerValor($consulta);
		
		$arrArchivo=explode(".",$nombreArchivo);
		
		$nombreDocumento=$nombreFinal;
		if($nombreDocumento!="")
		{
			if(strpos($nombreArchivo,".")!==false)
			{
				$nombreDocumento.=".".$arrArchivo[sizeof($arrArchivo)-1];
			}
		}
		else
			$nombreDocumento=$nombreArchivo;
		$consulta="UPDATE 908_archivos SET nomArchivoOriginal='".$nombreDocumento."',tipoDocumento=2,categoriaDocumentos=".$tipo." WHERE idArchivo=".$idDocumentoBase;
		
		if(	$con->ejecutarConsulta($consulta))
		{
			if($eliminarDocumentoOriginal)
			{
				unlink($baseDir."/documentosUsr/archivo_".$idDocumentoBase);
			}
			
			if(($idFormulario!=-1)&&($idRegistro!=-1))
				registrarDocumentoResultadoProceso($idFormulario,$idRegistro,$idDocumentoBase);
			return true;
		}
		
		
	}
	return false;
}

function verificarAccionesMedidaCautelar($idFormulario,$idRegistro)
{
	global $con;
	$idProceso=obtenerIdProcesoFormulario($idFormulario);
	
	$consulta="SELECT carpetaAdministrativa,idUsuario,idEvento FROM _111_tablaDinamica WHERE id__111_tablaDinamica=".$idRegistro;
	$fBaseSolicitud=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$consulta="SELECT * FROM _152_tablaDinamica WHERE idProcesoPadre=".$idProceso." AND idReferencia=".$idRegistro;
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_assoc($res))
	{
		switch($fila["medidaCautelar"])
		{
			case 2: //Garantia economica
				$arrValores=array();
				$arrDocumentosReferencia=NULL;
				$arrValores["carpetaAdministrativa"]=$fBaseSolicitud["carpetaAdministrativa"];
				$arrValores["idEvento"]=$fBaseSolicitud["idEvento"];
				$arrValores["idUsuario"]=$fBaseSolicitud["idUsuario"];
				$arrValores["montoGarantia"]=$fila["importe"];
				$arrValores["iFormulario"]=$idFormulario;
				$arrValores["iRegistro"]=$idRegistro;
				$arrValores["detallesAdicionales"]=$fila["detalle"];
				
				$arrCondiciones=array();
				$arrCondiciones["idEvento"]=$fBaseSolicitud["idEvento"];
				$arrCondiciones["idUsuario"]=$fBaseSolicitud["idUsuario"];
				$arrCondiciones["iFormulario"]=$idFormulario;
				$arrCondiciones["iRegistro"]=$idRegistro;				
				
				$idRegistroAux=obtenerInstanciaRegistroBusqueda(120,$arrCondiciones);
				if($idRegistroAux==-1)
				{
					crearInstanciaRegistroFormulario(120,-1,1,$arrValores,$arrDocumentosReferencia,-1,423);
				}
			break;
			case 14: //Centro reclusion
				$arrValores=array();
				$arrDocumentosReferencia=NULL;
				$arrValores["carpetaAdministrativa"]=$fBaseSolicitud["carpetaAdministrativa"];
				$arrValores["idEvento"]=$fBaseSolicitud["idEvento"];
				$arrValores["idUsuario"]=$fBaseSolicitud["idUsuario"];
				$arrValores["reclusorios"]=$fila["reclusorio"];
				$arrValores["iFormulario"]=$idFormulario;
				$arrValores["iRegistro"]=$idRegistro;
				
				$arrCondiciones=array();
				$arrCondiciones["idEvento"]=$fBaseSolicitud["idEvento"];
				$arrCondiciones["idUsuario"]=$fBaseSolicitud["idUsuario"];
				$arrCondiciones["iFormulario"]=$idFormulario;
				$arrCondiciones["iRegistro"]=$idRegistro;		
				
				$consulta="INSERT INTO 7013_imputadosCentroReclusion(idImputado,centroReclusion,situacion) 
						VALUES(".$fBaseSolicitud["idUsuario"].",".$fila["reclusorio"].",1)";
				$con->ejecutarConsulta($consulta);
				$idRegistroAux=obtenerInstanciaRegistroBusqueda(157,$arrCondiciones);
				if($idRegistroAux==-1)
					crearInstanciaRegistroFormulario(157,-1,2,$arrValores,$arrDocumentosReferencia,-1,422);
			break;
			default:
			break;
			
		}
	}
	insertarDocumentosMedidaCautelar($idFormulario,$idRegistro);
	return true;
	
	
	
}

function insertarDocumentosInternamientoImputado($idFormulario,$idRegistro)
{
	global $con;
	$arrDocumentos["98"]="";
	$arrDocumentos["99"]="";
	$arrDocumentos["7"]="";
	$arrDocumentos["5"]="";
	
	
	$consulta="SELECT * FROM _157_tablaDinamica WHERE id__157_tablaDinamica=".$idRegistro;
	$fRegistro=$con->obtenerPrimeraFilaAsoc($consulta);
	
	foreach($arrDocumentos as $documentos=>$resto)
	{
		$arrValores=array();
		$arrDocumentosReferencia=NULL;
		$arrValores["carpetaAdministrativa"]=$fRegistro["carpetaAdministrativa"];
		$arrValores["idPersona"]=$fRegistro["idUsuario"];
		$arrValores["tipoFigura"]=4;
		$arrValores["tipoDocumento"]=$documentos;
		$arrValores["iFormulario"]=$idFormulario;
		$arrValores["iRegistro"]=$idRegistro;
		$arrValores["iEvento"]=$fRegistro["idEvento"];
		
		crearInstanciaRegistroFormulario(123,-1,1,$arrValores,$arrDocumentosReferencia,-1,343);
	}
}

function inicialesNombre($nombre)
{
	global $con;
	$cadNombre="";
	$arrNombre=explode(" ",$nombre);
	foreach($arrNombre as $n)
	{
		if(strlen($n)>2)
		{
			$l=substr($n,0,1);
			
			if($cadNombre=="")
				$cadNombre=$l;
			else
				$cadNombre.=".".$l;
			
		}
		
	}
	
	return $cadNombre;
}

function obtenerUnidadGestionCarpetaAdmnistrativa($carpeta)
{
	global $con;
	$consulta="SELECT unidadGestion FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$carpeta."'";
	$unidadGestion=$con->obtenerValor($consulta);
	return $unidadGestion;
	
	
}

function registrarNotificacionRealizada($idFormulario,$idRegistro)
{
	global $con;
	$consulta="SELECT iFormulario,iRegistro FROM _72_tablaDinamica WHERE id__72_tablaDinamica=".$idRegistro;
	$fRegistro=$con->obtenerPrimeraFila($consulta);
	$consulta="SELECT idFormularioPadre,idReferencia FROM _".$fRegistro[0]."_tablaDinamica WHERE id__".$fRegistro[0]."_tablaDinamica=".$fRegistro[1];
	$fDatosSolicitud=$con->obtenerPrimeraFila($consulta);
	
	switch($fDatosSolicitud[0])
	{
		case 123:

			cambiarEtapaFormulario($fDatosSolicitud[0],$fDatosSolicitud[1],12,"",-1,"NULL","NULL",432);
			$arrDocumentos=array();
			$consulta="SELECT idDocumento FROM 9074_documentosRegistrosProceso WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro.
						" AND tipoDocumento=2";
			$rDocumentos=$con->obtenerFilas($consulta);
			while($fDocumento=mysql_fetch_row($rDocumentos))
			{
				registrarDocumentoResultadoProceso($fDatosSolicitud[0],$fDatosSolicitud[1],$fDocumento[0]);
			}
			
			
		break;
	}
	
	
}

function registrarAcuseEntregaDocumento($idFormulario,$idRegistro)
{
	global $con;
		
	$consulta="select * from _".$idFormulario."_tablaDinamica where id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$fila=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$consulta="SELECT etapaActual FROM 941_bitacoraEtapasFormularios WHERE idFormulario=123 AND 
				idRegistro=".$fila["idReferencia"]." ORDER BY etapaActual DESC";
	$etapaActual=$con->obtenerValor($consulta);
	
	$actor="";
	if($etapaActual==11)
		$actor=433;
	else
		$actor=434;
	
	convertirDocumentoUsuarioDocumentoResultadoProceso($fila["documentoAcuse"],123,$fila["idReferencia"],"Acuse de entrega de documento",11);
	cambiarEtapaFormulario(123,$fila["idReferencia"],12,$fila["comentariosAdicionales"],-1,"NULL","NULL",$actor);

	echo "window.parent.regresar1Pagina(true);return;";
}

function generarPropuestaFechaAudienciaIntermedia($idFormulario,$idRegistro)
{

	global $con;
	global $tipoMateria;
	
	return generarPropuestaFechaAudienciaIntermediaV3($idFormulario,$idRegistro);
	
}


function registrarDocumentosConfiguradosSolicitudAudiencia($idFormulario,$idRegistro)
{
	global $con;
	$arrDocumentosReferencia=NULL;	
	$consulta="SELECT tipoAudiencia,carpetaAdministrativa FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$fRegistroAudiencia=$con->obtenerPrimeraFila($consulta);
	$tipoAudiencia=$fRegistroAudiencia[0];
	
	
	$consulta="SELECT idRegistroEvento FROM 7000_eventosAudiencia WHERE idFormulario=".$idFormulario." AND idRegistroSolicitud=".$idRegistro;
	$idEvento=$con->obtenerValor($consulta);
	if($idEvento=="")
		$idEvento=-1;
	
	$consulta="SELECT * FROM _4_gridConfiguracionDocumentos WHERE idReferencia=".$tipoAudiencia." AND tiempoDocumento=1";

	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_assoc($res))
	{
					
		
		if($fila["figuraAudiencia"]=="")
		{
		
			$considerar=true;		
			if($fila["funcionAplicacion"]!="")
			{
				$cache=NULL;
				$cadObj='{"idFormulario":"'.$idFormulario.'","idRegistro":"'.$idRegistro.'","tipoAudiencia":"'.$tipoAudiencia.'"}';
				
				$obj=json_decode($cadObj);
				$resultado=removerComillasLimite(resolverExpresionCalculoPHP($fila["funcionAplicacion"],$obj,$cache));
				
				$considerar=($resultado==1);
				
			}
			
			if(!$considerar)
			{
				continue;
			}
		
		
			$arrValores=array();
			$arrDocumentosReferencia=NULL;
			$arrValores["idPersona"]=0;
			$arrValores["tipoFigura"]=0;
			$arrValores["tipoDocumento"]=$fila["documento"];
			$arrValores["iFormulario"]=$idFormulario;
			$arrValores["iRegistro"]=$idRegistro;
			$arrValores["carpetaAdministrativa"]=$fRegistroAudiencia[1];
			$arrValores["adjuntarDocumentoFinal"]=$fila["adjuntarDocumentoFinal"];
			$arrValores["iEvento"]=$idEvento;
			
			$idRegistroDocumento=crearInstanciaRegistroFormulario(123,-1,$fila["etapaInicial"],$arrValores,$arrDocumentosReferencia,-1,440);	
			
		
		}
		else
		{
			$consulta="SELECT idFormulario,idRegistro FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$fRegistroAudiencia[1]."'";
			
			$fDatosCarpeta=$con->obtenerPrimeraFila($consulta);
			
			$consulta="SELECT idActividad FROM _".$fDatosCarpeta[0]."_tablaDinamica WHERE id__".$fDatosCarpeta[0]."_tablaDinamica=".$fDatosCarpeta[1];

			$idActividad=$con->obtenerValor($consulta);
			if($idActividad=="")
				$idActividad=-1;
			
			$consulta="SELECT idParticipante FROM 7005_relacionFigurasJuridicasSolicitud WHERE idActividad=".$idActividad.
						" AND idFiguraJuridica=".$fila["figuraAudiencia"];

			$rFigura=$con->obtenerFilas($consulta);
			while($fFigura=mysql_fetch_row($rFigura))
			{
				$considerar=true;		
				if($fila["funcionAplicacion"]!="")
				{
					$cache=NULL;
					$cadObj='{"idFormulario":"'.$idFormulario.'","idRegistro":"'.$idRegistro.'","tipoAudiencia":"'.$tipoAudiencia.
							'","figuraJuridica":"'.$fila["figuraAudiencia"].'","idUsuario":"'.$fFigura[0].'"}';
					
					$obj=json_decode($cadObj);
					$resultado=removerComillasLimite(resolverExpresionCalculoPHP($fila["funcionAplicacion"],$obj,$cache));
					
					$considerar=($resultado==1);
					
				}
				
				if(!$considerar)
				{
					continue;
				}
				
				
				$arrValores=array();
				$arrDocumentosReferencia=NULL;
				$arrValores["idPersona"]=($fFigura[0]=="")?0:$fFigura[0];
				$arrValores["tipoFigura"]=$fila["figuraAudiencia"]==""?0:$fila["figuraAudiencia"];
				$arrValores["tipoDocumento"]=$fila["documento"];
				$arrValores["iFormulario"]=$idFormulario;
				$arrValores["iRegistro"]=$idRegistro;
				$arrValores["carpetaAdministrativa"]=$fRegistroAudiencia[1];
				$arrValores["adjuntarDocumentoFinal"]=$fila["adjuntarDocumentoFinal"];
				$arrValores["iEvento"]=$idEvento;
				$idRegistroDocumento=crearInstanciaRegistroFormulario(123,-1,$fila["etapaInicial"],$arrValores,$arrDocumentosReferencia,-1,440);	
				
			}
		}
		
		
		
	}
	
	return true;
	
}

function registrarDocumentoResultadoProcesoPadre($idFormulario,$idRegistro)
{
	global $con;
	
	$consulta="SELECT adjuntarDocumentoFinal,iFormulario,iRegistro FROM _123_tablaDinamica WHERE id__123_tablaDinamica=".$idRegistro;
	$filaDocumento=$con->obtenerPrimeraFila($consulta);
	
	
	$consulta="SELECT idDocumento FROM 9074_documentosRegistrosProceso WHERE idFormulario=123 
			AND idRegistro=".$idRegistro." AND tipoDocumento=2";

	$res=$con->obtenerFilas($consulta);
	while($fDocumento=mysql_fetch_row($res))
	{
		registrarDocumentoResultadoProceso($filaDocumento[1],$filaDocumento[2],$fDocumento[0]);
	}
	
	
}

function generarSolicitudAudienciaIntermedia($idFormulario,$idRegistro)
{
	global $con;
	global $servidorPruebas;
	$consulta="SELECT * FROM _96_tablaDinamica WHERE id__96_tablaDinamica=".$idRegistro;
	$fSolicitud=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$consulta="SELECT COUNT(*) FROM _185_tablaDinamica WHERE iFormulario=96 AND iRegistro=".$idRegistro;
	$numReg=$con->obtenerValor($consulta);
	if($numReg>0)
		return true;
	$arrValores=array();
	$arrDocumentosReferencia=array();
	
	$consulta="SELECT idDocumento FROM 9074_documentosRegistrosProceso WHERE idFormulario=".$idFormulario." 
				AND idRegistro=".$idRegistro." AND tipoDocumento in(1,2)";

	$res=$con->obtenerFilas($consulta);
	while($fDocumento=mysql_fetch_row($res))
	{
		array_push($arrDocumentosReferencia,$fDocumento[0]);	
	}
	
	$arrValores["carpetaAdministrativa"]=$fSolicitud["carpetaAdministrativa"];
	$arrValores["tipoAudiencia"]=(($fSolicitud["tipoAudiencia"]=="")||($fSolicitud["tipoAudiencia"]=="-1"))?103:$fSolicitud["tipoAudiencia"];
	$arrValores["tipoPromovente"]=1;
	$arrValores["figuraJuridica"]=$fSolicitud["figurasJuridicas"];
	$arrValores["promovente"]=$fSolicitud["imputado"];
	$arrValores["iFormulario"]=$idFormulario;
	$arrValores["iRegistro"]=$idRegistro;
	$arrValores["parametrosFechaMinima"]=0;
	$arrValores["fechaPromocion"]=$fSolicitud["fechaRecepcion"]." ".$fSolicitud["horaRecepcion"];
	$etapa=1.1;
	
	$idRegistroDocumento=crearInstanciaRegistroFormulario(185,-1,$etapa,$arrValores,$arrDocumentosReferencia,-1,462);
	
	return true;
}


function registrarSolicitudNotificacionesExhorto($idFormulario,$idRegistro)
{
	global $con;
		
	$consulta="select carpetaAdministrativa,idEvento from _".$idFormulario."_tablaDinamica 
			where id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$fDatosSolicitud=$con->obtenerPrimeraFila($consulta);
	
	$carpetaAdministrativa=$fDatosSolicitud[0];
	
	$arrValores=array();
	$arrValores["idEvento"]=$fDatosSolicitud[1];
	$arrValores["tipoSolicitud"]=2;
	
	
	$arrValores["carpetaAdministrativa"]=$carpetaAdministrativa;
	$arrValores["idFormularioPadre"]=$idFormulario;
	
	$arrDocumentos=array();
	$consulta="SELECT idDocumento FROM 9074_documentosRegistrosProceso WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro." AND tipoDocumento=2";
	$rDocumentos=$con->obtenerFilas($consulta);
	while($fDocumento=mysql_fetch_row($rDocumentos))
	{
		array_push($arrDocumentos,$fDocumento[0]);
	}
	$idRegistroSolicitudCitacion=crearInstanciaRegistroFormulario(67,$idRegistro,1,$arrValores,$arrDocumentos,-1,315);
	
	return true;
	
	
	
}

function recargarGridSujetos()
{
	echo "window.parent.parent.recargarArbolSujetos(); window.parent.parent.cerrarVentanaFancy();return;";
}

function registrarCambioEtapaProcesalCarpeta($carpeta,$etapaActual,$idFormulario,$idRegistro,$idEvento)
{
	global $con;
	$consulta="SELECT etapaProcesalActual FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$carpeta."'";
	$etapaProcesal=$con->obtenerValor($consulta);
	
	$x=0;
	$query[$x]="begin";
	$x++;
	$query[$x]="INSERT INTO 7010_bitacoraCambioEtapaProcesal(fechaCambio,etapaAnterior,etapaActual,responsableCambio,idFormulario,idRegistro,idEvento)
			VALUES('".date("Y-m-d H:i:s")."',".$etapaProcesal.",".$etapaActual.",".$_SESSION["idUsr"].",".$idFormulario.",".$idRegistro.",".$idEvento.")";
	$x++;
	$query[$x]="UPDATE 7006_carpetasAdministrativas SET etapaProcesalActual=".$etapaActual." WHERE carpetaAdministrativa='".$carpeta."'";
	$x++;
	$query[$x]="commit";
	$x++;
	return $con->ejecutarBloque($query);
}

function registrarCambioSituacionCarpeta($carpeta,$etapaActual,$idFormulario,$idRegistro,$idEvento,$comentarios="",$idCarpeta=-1)
{
	global $con;
	
	
	if(($idCarpeta==-1)&&($idFormulario!=-1))
	{
		$nomTabla="_".$idFormulario."_tablaDinamica";
		if($con->existeCampo("idExpediente",$nomTabla))
		{
			$consulta="SELECT idExpediente FROM ".$nomTabla." WHERE id_".$nomTabla."=".$idRegistro." AND idExpediente NOT IN('','N/E')";
			$idCarpeta=$con->obtenerValor($consulta);
		}
		else
		{
			if($con->existeCampo("idCarpeta",$nomTabla))
			{
				$consulta="SELECT idCarpeta FROM ".$nomTabla." WHERE id_".$nomTabla."=".$idRegistro." AND idExpediente NOT IN('','N/E')";
				$idCarpeta=$con->obtenerValor($consulta);
			}
		}
			
		if($idCarpeta=="")
			$idCarpeta=-1;
			
	}
	
	$consulta="SELECT situacion FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$carpeta."'";
	if($idCarpeta!=-1)
		$consulta.=" and idCarpeta=".$idCarpeta;

	$situacion=$con->obtenerValor($consulta);
	
	$x=0;
	$query[$x]="begin";
	$x++;
	
	$query[$x]="INSERT INTO 7015_bitacotaCambioSituacionCarpetaAdministrativa(carpetaAdministrativa,fechaCambio,
				idEstadoAnterior,idEstadoActual,responsableCambio,idFormulario,idRegistro,idEventoAudiencia,
				comentariosAdiciones,idCarpeta)
			VALUES('".$carpeta."','".date("Y-m-d H:i:s")."',".$situacion.",".$etapaActual.",".$_SESSION["idUsr"].
			",".$idFormulario.",".$idRegistro.",".$idEvento.",'".cv($comentarios)."',".$idCarpeta.")";

	$x++;
	
	$query[$x]="UPDATE 7006_carpetasAdministrativas SET situacion=".$etapaActual." WHERE carpetaAdministrativa='".$carpeta."'";
	if($idCarpeta!=-1)
		$query[$x].=" and idCarpeta=".$idCarpeta;
	
	
		
	$x++;
	
	$query[$x]="commit";
	$x++;
	
	return $con->ejecutarBloque($query);
	
}

function registrarAccionAudiencia($idFormulario,$idRegistro)
{
	global $con;
	$consulta="SELECT * FROM _233_tablaDinamica WHERE id__233_tablaDinamica=".$idRegistro;
	$fRegistro=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$etapaActual="";
	switch($fRegistro["idAccion"])
	{
		case 1: //TerminaciÛn por causa diversa
			$etapaActual=6;
			registrarCambioSituacionCarpeta($fRegistro["carpetaAdministrativa"],$etapaActual,$idFormulario,$idRegistro,$fRegistro["idEvento"]);
		break;
		case 2: //Reapertura de proceso
			$etapaActual=7;
			registrarCambioSituacionCarpeta($fRegistro["carpetaAdministrativa"],$etapaActual,$idFormulario,$idRegistro,$fRegistro["idEvento"]);
		break;
		case 3:  //Causa sujeta a mediaciÛn
			$etapaActual=4;
			registrarCambioSituacionCarpeta($fRegistro["carpetaAdministrativa"],$etapaActual,$idFormulario,$idRegistro,$fRegistro["idEvento"]);
		break;
		case 4:  //Cierre de investigaciÛn complementaria
			$etapaActual=4;
			registrarCambioEtapaProcesalCarpeta($fRegistro["carpetaAdministrativa"],$etapaActual,$idFormulario,$idRegistro,$fRegistro["idEvento"]);
		break;
		case 5:  //Registrar vinculaciÛn a proceso
			$etapaActual=5;
			registrarCambioEtapaProcesalCarpeta($fRegistro["carpetaAdministrativa"],$etapaActual,$idFormulario,$idRegistro,$fRegistro["idEvento"]);
		break;
		case 6:  //Causa sujeta a justicia alternativa
			$etapaActual=5;
			registrarCambioSituacionCarpeta($fRegistro["carpetaAdministrativa"],$etapaActual,$idFormulario,$idRegistro,$fRegistro["idEvento"]);
		break;
		case 7:  //ReparaciÛn del daÒo
			$etapaActual=3;
			registrarCambioSituacionCarpeta($fRegistro["carpetaAdministrativa"],$etapaActual,$idFormulario,$idRegistro,$fRegistro["idEvento"]);
		break;
	}
	registrarResolutivo($idFormulario,$idRegistro);
	return registrarDocumentosConfiguradosResolutivo($idFormulario,$idRegistro);
	//echo "window.parent.parent.recargarPagina();window.parent.parent.cerrarVentanaFancy();return;";

	
	
}

function registrarDocumentosConfiguradosResolutivo($idFormulario,$idRegistro)
{
	global $con;
	$arrDocumentosReferencia=NULL;	
	
	
	
	$consulta="SELECT idAccion,carpetaAdministrativa,idEvento FROM _233_tablaDinamica WHERE id__233_tablaDinamica=".$idRegistro;
	$fRegistro=$con->obtenerPrimeraFila($consulta);
	
	$consulta="SELECT documento FROM _234_gridDocumento WHERE idReferencia=".$fRegistro[0];
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_assoc($res))
	{
					
		
		/*if($fila["figuraAudiencia"]=="")
		{
		
			$considerar=true;		
			if($fila["funcionAplicacion"]!="")
			{
				$cache=NULL;
				$cadObj='{"idFormulario":"'.$idFormulario.'","idRegistro":"'.$idRegistro.'","tipoAudiencia":"'.$tipoAudiencia.'"}';
				
				$obj=json_decode($cadObj);
				$resultado=removerComillasLimite(resolverExpresionCalculoPHP($fila["funcionAplicacion"],$obj,$cache));
				
				$considerar=($resultado==1);
				
			}
			
			if(!$considerar)
			{
				continue;
			}*/
		
		
			$arrValores=array();
			$arrDocumentosReferencia=NULL;
			$arrValores["idPersona"]=0;
			$arrValores["tipoFigura"]=0;
			$arrValores["tipoDocumento"]=$fila["documento"];
			$arrValores["iFormulario"]=$idFormulario;
			$arrValores["iRegistro"]=$idRegistro;
			$arrValores["carpetaAdministrativa"]=$fRegistro[1];
			$arrValores["adjuntarDocumentoFinal"]=1;
			$arrValores["iEvento"]=$fRegistro[2];
			$idRegistroDocumento=crearInstanciaRegistroFormulario(123,-1,1.1,$arrValores,$arrDocumentosReferencia,-1,440);	
			
		
		/*}
		else
		{
			$consulta="SELECT idFormulario,idRegistro FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$fRegistroAudiencia[1]."'";
			$fDatosCarpeta=$con->obtenerPrimeraFila($consulta);
			
			$consulta="SELECT idActividad FROM _".$fDatosCarpeta[0]."_tablaDinamica WHERE id__".$fDatosCarpeta[0]."_tablaDinamica=".$fDatosCarpeta[1];
			$idActividad=$con->obtenerValor($consulta);
			
			
			$consulta="SELECT idParticipante FROM 7005_relacionFigurasJuridicasSolicitud WHERE idActividad=".$idActividad.
						" AND idFiguraJuridica=".$fila["figuraAudiencia"];
			$rFigura=$con->obtenerFilas($consulta);
			while($fFigura=mysql_fetch_row($rFigura))
			{
				$considerar=true;		
				if($fila["funcionAplicacion"]!="")
				{
					$cache=NULL;
					$cadObj='{"idFormulario":"'.$idFormulario.'","idRegistro":"'.$idRegistro.'","tipoAudiencia":"'.$tipoAudiencia.
							'","figuraJuridica":"'.$fila["figuraAudiencia"].'","idUsuario":"'.$fFigura[0].'"}';
					
					$obj=json_decode($cadObj);
					$resultado=removerComillasLimite(resolverExpresionCalculoPHP($fila["funcionAplicacion"],$obj,$cache));
					
					$considerar=($resultado==1);
					
				}
				
				if(!$considerar)
				{
					continue;
				}
				
				
				$arrValores=array();
				$arrDocumentosReferencia=NULL;
				$arrValores["idPersona"]=$fFigura[0];
				$arrValores["tipoFigura"]=$fila["figuraAudiencia"];
				$arrValores["tipoDocumento"]=$fila["documento"];
				$arrValores["iFormulario"]=$idFormulario;
				$arrValores["iRegistro"]=$idRegistro;
				$arrValores["carpetaAdministrativa"]=$fRegistroAudiencia[1];
				$arrValores["adjuntarDocumentoFinal"]=$fila["adjuntarDocumentoFinal"];
				$arrValores["iEvento"]=$idEvento;
				$idRegistroDocumento=crearInstanciaRegistroFormulario(123,-1,$fila["etapaInicial"],$arrValores,$arrDocumentosReferencia,-1,440);	
				
			}
		}
		*/
		
		
	}
	
	return true;
	
}

function registrarContenidoEventoAudiencia($idRegistroEvento,$tipoContenido,$tituloContenido,$idFormulario,$idRegistro)
{
	global $con;
	
	$consulta="INSERT INTO 7016_contenidosEventoAudiencia(idRegistroEvento,tipoContenido,tituloContenido,idFormulario,idRegistro,
					fechaRegistro,responsableRegistro) VALUES(".$idRegistroEvento.",".$tipoContenido.",'".cv($tituloContenido)."',".$idFormulario.",".$idRegistro
					.",'".date("Y-m-d H:i:s")."',".$_SESSION["idUsr"].")";
	
	$con->ejecutarConsulta($consulta);
}

function registrarResolutivo($idFormulario,$idRegistro)
{
	global $con;
	
	$consulta="SELECT * FROM 7016_contenidosEventoAudiencia WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro;
	$fRegistro=$con->obtenerPrimeraFila($consulta);
	
	
	if(!$fRegistro)
	{
		$consulta="SELECT * FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
		$fRegistro=$con->obtenerPrimeraFilaAsoc($consulta);
		$consulta="SELECT resolutivo FROM _234_tablaDinamica WHERE id__234_tablaDinamica=".$fRegistro["idAccion"];
		$tituloContenido=$con->obtenerValor($consulta);
		return registrarContenidoEventoAudiencia($fRegistro["idEvento"],1,$tituloContenido,$idFormulario,$idRegistro);
	}
	return false;
}

function generarSolicitudAudienciaPorAcuerdo($idFormulario,$idRegistro)
{
	global $con;
	
	global $con;
	$arrDocumentosReferencia=NULL;	
	$consulta="SELECT tipoAudiencia,carpetaAdministrativa FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$fRegistroAudiencia=$con->obtenerPrimeraFila($consulta);
	$tipoAudiencia=$fRegistroAudiencia[0];
	
	
	$arrValores=array();
	$arrDocumentosReferencia=NULL;
	$arrValores["idPersona"]=0;
	$arrValores["tipoFigura"]=0;
	$arrValores["tipoDocumento"]=124;
	$arrValores["iFormulario"]=$idFormulario;
	$arrValores["iRegistro"]=$idRegistro;
	$arrValores["carpetaAdministrativa"]=$fRegistroAudiencia[1];
	$arrValores["adjuntarDocumentoFinal"]=1;
	$arrValores["iEvento"]=-1;
	$idRegistroDocumento=crearInstanciaRegistroFormulario(123,-1,1.1,$arrValores,$arrDocumentosReferencia,-1,440);	
	
	return true;
}

function registrarSolicitudDefensorPublico($idFormulario,$idRegistro)
{
	global $con;
	return true;
	
	
	$consulta="SELECT tipoFormato FROM 3000_formatosRegistrados WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro;
	$tipoFormato=$con->obtenerValor($consulta);
	if($tipoFormato==24)
	{
		$consulta="SELECT carpetaAdministrativa,idEvento FROM _72_tablaDinamica WHERE id__72_tablaDinamica=".$idRegistro;
		$fRegistroSolicitud=$con->obtenerPrimeraFila($consulta);
		$carpetaAdministrativa=$fRegistroSolicitud[0];
		$idEventoAudiencia=$fRegistroSolicitud[1];
		
		$consulta="SELECT idActividad FROM _46_tablaDinamica WHERE carpetaAdministrativa='".$carpetaAdministrativa."'";
		$idActividad=$con->obtenerValor($consulta);
		
		$arrDocumentosReferencia=array();
		
		$consulta="SELECT idDocumento FROM 9074_documentosRegistrosProceso WHERE idFormulario=".$idFormulario." 
				AND idRegistro=".$idRegistro." AND tipoDocumento=2";

		$res=$con->obtenerFilas($consulta);
		while($fDocumento=mysql_fetch_row($res))
		{
			array_push($arrDocumentosReferencia,$fDocumento[0]);	
		}
		
		
		$arrValores=array();
		$arrValores["idEvento"]=$idEventoAudiencia;
		$arrValores["carpetaAdministrativa"]=$carpetaAdministrativa;
		
		$idRegistroSolicitud=crearInstanciaRegistroFormulario(80,-1,2,$arrValores,$arrDocumentosReferencia,-1,521);
		
		
		$x=0;
		$query[$x]="begin";
		$x++;
		
		$consulta="SELECT id__47_tablaDinamica FROM _47_tablaDinamica WHERE idActividad=".$idActividad." AND requiereDefensoria=1";

		$resImputados=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($resImputados))
		{
			$query[$x]="INSERT INTO _80_impuados(idPadre,idOpcion) VALUES(".$idRegistroSolicitud.",".$fila[0].")";
			$x++;
		}
		
		$query[$x]="commit";
		$x++;
		return $con->ejecutarBloque($query);
	}
	
	
	return true;
	
}

function mostrarDocumentoOrdenCitacion($idFormulario,$idReferencia,$idFormularioEvaluacion)
{
	
	global $con;
	$consulta="SELECT tipoDocumento,idEstado FROM _72_tablaDinamica WHERE id__72_tablaDinamica=".$idReferencia;
	
	$fDatos=$con->obtenerPrimeraFila($consulta);
	
	
	if($fDatos[0]!=0)
	{
		
		return mostrarSeccionEdicionDocumento1($idFormulario,$idReferencia,$idFormularioEvaluacion);
	}
	return 0;
	
	
}

function registrarSolicitudTrasladoImputado($idFormulario,$idRegistro)
{
	global $con;
	$arrDocumentosReferencia=array();
	$consulta="SELECT carpetaAdministrativa FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$carpeta=$con->obtenerValor($consulta);
	
	
	$consulta="SELECT idActividad FROM _46_tablaDinamica WHERE carpetaAdministrativa='".$carpeta."'";
	$idActividad=$con->obtenerValor($consulta);
	if($idActividad=="")
		$idActividad=-1;
	
	$consulta="SELECT idRegistroEvento FROM 7000_eventosAudiencia WHERE idFormulario=".$idFormulario." AND idRegistroSolicitud=".$idRegistro;
	$idEventoAudiencia=$con->obtenerValor($consulta);
	
	$consulta="SELECT idParticipante FROM 7005_relacionFigurasJuridicasSolicitud WHERE idActividad=".$idActividad." AND idFiguraJuridica=4";
	$resParticipante=$con->obtenerFilas($consulta);
	while($fParticipante=mysql_fetch_row($resParticipante))
	{
		$centroReclusion=obtenerIDCentroReclusionImputado($fParticipante[0]);
		if($centroReclusion!="-1")
		{
			
			$arrValores=array();
			$arrValores["idEvento"]=$idEventoAudiencia;
			$arrValores["carpetaAdministrativa"]=$carpeta;
			$arrValores["idUsuario"]=$fParticipante[0];
			$arrValores["idReclusorio"]=$centroReclusion;
			
			$idRegistroSolicitud=crearInstanciaRegistroFormulario(84,-1,2,$arrValores,$arrDocumentosReferencia,-1,524);
		}
	}
	
	return true;
	
	
	
	
}

function insertarDocumentosMedidaCautelar($idFormulario,$idRegistro)
{
	global $con;
	$arrDocumentos["6"]="";
	
	
	
	$consulta="SELECT * FROM _111_tablaDinamica WHERE id__111_tablaDinamica=".$idRegistro;
	$fRegistro=$con->obtenerPrimeraFilaAsoc($consulta);
	
	foreach($arrDocumentos as $documentos=>$resto)
	{
		$arrValores=array();
		$arrDocumentosReferencia=NULL;
		$arrValores["carpetaAdministrativa"]=$fRegistro["carpetaAdministrativa"];
		$arrValores["idPersona"]=$fRegistro["idUsuario"];
		$arrValores["tipoFigura"]=4;
		$arrValores["tipoDocumento"]=$documentos;
		$arrValores["iFormulario"]=$idFormulario;
		$arrValores["iRegistro"]=$idRegistro;
		$arrValores["iEvento"]=$fRegistro["idEvento"];
		
		crearInstanciaRegistroFormulario(123,-1,1.1,$arrValores,$arrDocumentosReferencia,-1,343);
	}
}

function insertarDocumentosConfiguracionProceso($idFormulario,$idRegistro)
{
	global $con;
	
	$idProceso=obtenerIdProcesoFormulario($idFormulario);
	
	$consulta="SELECT * FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$fRegistro=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$consulta="SELECT d.nombreDocumento FROM _155_tablaDinamica t,_155_configuraciones d 
				WHERE cmbProcesos=".$idProceso." AND d.idReferencia=t.id__155_tablaDinamica";
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		$arrValores=array();
		$arrDocumentosReferencia=NULL;
		$arrValores["carpetaAdministrativa"]=$fRegistro["carpetaAdministrativa"];
		$arrValores["idPersona"]=0;
		$arrValores["tipoFigura"]=0;
		$arrValores["tipoDocumento"]=$fila[0];
		$arrValores["iFormulario"]=$idFormulario;
		$arrValores["iRegistro"]=$idRegistro;
		$arrValores["iEvento"]=isset($fRegistro["idEvento"])?$fRegistro["idEvento"]:"-1";
		
		crearInstanciaRegistroFormulario(123,-1,1.1,$arrValores,$arrDocumentosReferencia,-1,343);
	}
}

function ejecutarAccionAsignacionDocumentoAtencionUsuario($idFormulario,$idRegistro)
{
	global $con;
	$consulta="SELECT * FROM _123_tablaDinamica WHERE id__123_tablaDinamica=".$idRegistro;
	$fDocumento=$con->obtenerPrimeraFilaAsoc($consulta);
	
	if($fDocumento["tipoDocumento"]==141)
	{
		$arrValores=array();
		$arrDocumentosReferencia=array();
		
		$consulta="SELECT idDocumento FROM 3000_formatosRegistrados WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro;
		$resDocumento=$con->obtenerFilas($consulta);
		while($fDocumentoAux=mysql_fetch_row($resDocumento))
		{
			array_push($arrDocumentosReferencia,$fDocumentoAux[0]);
		}
		
		$arrValores["carpetaAdministrativa"]=$fDocumento["carpetaAdministrativa"];

		$consulta="SELECT * FROM _172_tablaDinamica WHERE id__172_tablaDinamica=".$fDocumento["iRegistro"];
		
		$fDatosRegistro=$con->obtenerPrimeraFilaAsoc($consulta);
		$arrValores["idEvento"]=$fDatosRegistro["idEvento"];
		$arrValores["iFormulario"]=$fDocumento["iFormulario"];
		$arrValores["iRegistro"]=$fDocumento["iRegistro"];
		$arrValores["numCopiasCertificadas"]=0;
		$arrValores["numCopiasNoCertificadas"]=0;
		$arrValores["sumaCopias"]=0;
		
		$idRegistroSolicitud=crearInstanciaRegistroFormulario(274,-1,1.1,$arrValores,$arrDocumentosReferencia,-1,609);
		registrarAsociacionProcesoFormulario($fDocumento["iFormulario"],$fDocumento["iRegistro"],274,$idRegistro,"Solicitud de copias de audio y video");
		
		
	}
	
}

function esDocumentoAmparoRecibido($idFormulario,$idRegistro)
{
	$consulta="SELECT * FROM _123_tablaDinamica WHERE id__123_tablaDinamica=".$idRegistro;
	$fDocumento=$con->obtenerPrimeraFilaAsoc($consulta);
	if($fDocumento["tipoDocumento"]==141)
	{
		return 1;
	}
	return 0;
}

function esDocumentoEsperaEntrega($idFormulario,$idRegistro)
{
	$consulta="SELECT * FROM _123_tablaDinamica WHERE id__123_tablaDinamica=".$idRegistro;
	$fDocumento=$con->obtenerPrimeraFilaAsoc($consulta);
	switch($fDocumento["tipoDocumento"])
	{
		case 141:
			return 0;
		break;
		default:
			return 1;
		break;
	}

}

function registrarAsociacionProcesoFormulario($idFormulario,$idRegistro,$iFormulario,$iRegistro,$descripcion="")
{
	global $con;
	
	$consulta="INSERT INTO 3008_procesosAsociadosFormulario(idFormulario,idRegistro,iFormulario,iRegistro,descripcion) 
				VALUES(".$idFormulario.",".$idRegistro.",".$iFormulario.",".$iRegistro.",'".cv($descripcion)."')";
	return $con->ejecutarConsulta($consulta);
}

function reportarAudienciaPGJ($idFormulario,$idRegistro)
{
	
	global $con;
	global $servidorPruebas;
	global $pruebasPGJ;	
	global $cancelarNotificacionesPGJ;
	global $tipoMateria;
	global $urlPruebas;
	global $urlProduccion;
	$sistema=0;
	if($tipoMateria!="P")
		return true;

	if((($servidorPruebas)&&(!$pruebasPGJ))||($cancelarNotificacionesPGJ))
		return true;
	$url = $urlProduccion;
	if($pruebasPGJ)
		$url =$urlPruebas;

	$client = new nusoap_client($url,"wsdl");

	$parametros=array();


	$consulta="SELECT * FROM 7000_eventosAudiencia WHERE  idFormulario=".$idFormulario." AND idRegistroSolicitud=".$idRegistro;
	
	$fRegistro=$con->obtenerPrimeraFilaAsoc($consulta);

	$idEvento=$fRegistro["idRegistroEvento"]==""?-1:$fRegistro["idRegistroEvento"];
	$fRegistroEvento["ctrlSolicitud"]="";
	
	$consulta="UPDATE 7000_eventosAudiencia SET notificadoPGJ=2 WHERE idRegistroEvento=".$idEvento;
	$con->ejecutarConsulta($consulta);
	
	if(($fRegistro["idFormulario"]!="N/E")&&($fRegistro["idFormulario"]!="")&&($fRegistro["idFormulario"]!="-1"))
	{
		if($idFormulario!=185)
		{
			$consulta="SELECT ctrlSolicitud,idSolicitud,cveSolicitud,carpetaAdministrativa,sistema FROM _".$fRegistro["idFormulario"]."_tablaDinamica 
					WHERE id__".$fRegistro["idFormulario"]."_tablaDinamica=".$fRegistro["idRegistroSolicitud"];
			
			$fRegistroEvento=$con->obtenerPrimeraFilaAsoc($consulta);
		}
		else
		{
			$consulta="SELECT iFormulario,iRegistro,carpetaAdministrativa FROM _185_tablaDinamica WHERE id__185_tablaDinamica=".$idRegistro;
			$fRegistroPromocion=$con->obtenerPrimeraFila($consulta)	;
			
			if(($fRegistroPromocion[0]!="N/E")&&($fRegistroPromocion[0]!="")&&($fRegistroPromocion[0]!="-1"))
			{
			
				$consulta="SELECT ctrlSolicitud,idSolicitud,cveSolicitud,carpetaAdministrativa,sistema FROM _".$fRegistroPromocion[0]."_tablaDinamica 
					WHERE id__".$fRegistroPromocion[0]."_tablaDinamica=".$fRegistroPromocion[1];
		
				
			}
			else
			{
				$consulta="SELECT '' as ctrlSolicitud,'' as idSolicitud,'0' as cveSolicitud,'".$fRegistroPromocion[2]."' as carpetaAdministrativa,'1' as sistema";
			}
			$fRegistroEvento=$con->obtenerPrimeraFilaAsoc($consulta);
		}
	}
	
	$datosEvento=obtenerDatosEventoAudiencia($idEvento);
	$consulta="SELECT idJuez FROM 7001_eventoAudienciaJuez WHERE idRegistroEvento=".$idEvento;
	$res=$con->obtenerFilas($consulta);

	$arrJueces="";
	while($fJuez=mysql_fetch_row($res))
	{
		$consulta="SELECT Nom,Paterno,Materno FROM 802_identifica WHERE idUsuario=".$fJuez[0];
		$fNombreJuez=$con->obtenerPrimeraFila($consulta);
		
		$consulta="SELECT clave FROM _26_tablaDinamica WHERE usuarioJuez=".$fJuez[0];

		$clave=$con->obtenerValor($consulta);

		$arrClaveJuez=explode(" ",$clave);
		if(sizeof($arrClaveJuez)==1)
		{
			$arrClaveJuez[1]=$arrClaveJuez[0];
		}
		$o='<juez><cveJuez>'.($arrClaveJuez[1]*1).'</cveJuez><apPaterno>'.$fNombreJuez[1].'</apPaterno><apMaterno>'.$fNombreJuez[2].'</apMaterno><nombre>'.$fNombreJuez[0].'</nombre></juez>';
		$arrJueces.=$o;
	}


	
	$consulta="SELECT COUNT(*) FROM 3010_bitacoraNotificacionPGJ WHERE idEvento=".$idEvento." AND resultado>0 and tipoNotificacion=1";        

	$nNotificaciones=$con->obtenerValor($consulta);
	
	if(($fRegistroEvento["ctrlSolicitud"]!="") &&($fRegistroEvento["cveSolicitud"]!="") )
	{
		$sistema=$fRegistroEvento["sistema"];
		$consulta="SELECT id__46_tablaDinamica FROM _46_tablaDinamica WHERE carpetaAdministrativa='".$fRegistroEvento["carpetaAdministrativa"]."'";
		$idRegistroInicial=$con->obtenerValor($consulta);
		
		if($idRegistroInicial=="")
		{
			$arrCarpetas=array();
			obtenerCarpetasPadre($fRegistroEvento["carpetaAdministrativa"],$arrCarpetas);
			$consulta="SELECT id__46_tablaDinamica FROM _46_tablaDinamica WHERE carpetaAdministrativa='".$arrCarpetas[0]."'";
			$idRegistroInicial=$con->obtenerValor($consulta);
		}
		
		$consulta="SELECT claveFiscalia,claveUnidad,claveAgencia FROM _100_tablaDinamica WHERE idReferencia=".$idRegistroInicial;
		$fFiscalia=$con->obtenerPrimeraFila($consulta);
		$consulta="SELECT cveFiscalia FROM _286_tablaDinamica WHERE id__286_tablaDinamica=".($fFiscalia[0]==""?-1:$fFiscalia[0]);
		$cveFiscalia=$con->obtenerValor($consulta);
		$xml='<?xml version="1.0" encoding=\'ISO-8859-1\'?>
				<asignacionAudiencia>
					<tipoNotificacion>'.($nNotificaciones==0?1:2).'</tipoNotificacion>
					<idCtrProcedimiento>'.$fRegistroEvento["idSolicitud"].'</idCtrProcedimiento>
					<ctrSolicitud>'.$fRegistroEvento["ctrlSolicitud"].'</ctrSolicitud> 
					<cveSolicitud>'.$fRegistroEvento["cveSolicitud"].'</cveSolicitud> 
					<idSolicitud>'.$fRegistroEvento["idSolicitud"].'</idSolicitud> 
					<fechaAudiencia>'.date("Y-m-d H:i:s",strtotime($fRegistro["horaInicioEvento"])).'</fechaAudiencia>
					<fechaFinAudiencia>'.date("Y-m-d H:i:s",strtotime($fRegistro["horaFinEvento"])).'</fechaFinAudiencia>
					<cveFiscalia>'.$cveFiscalia.'</cveFiscalia>
					<cveAgencia>'.$fFiscalia[2].'</cveAgencia>
					<cveUnidad>'.$fFiscalia[1].'</cveUnidad>
					<edificio>'.$datosEvento->edificio.'</edificio> 
					<sala>'.$datosEvento->sala.'</sala> 
					<jueces>'.$arrJueces.'</jueces> 
					<unidadControl>'.$datosEvento->unidadGestion.'</unidadControl> 
					<textoDocumento></textoDocumento>
					<documentoAdjunto></documentoAdjunto > 
					<comentario></comentario>
					<carpetaAdministrativa>'.$fRegistroEvento["carpetaAdministrativa"].'</carpetaAdministrativa>
				</asignacionAudiencia>';

		$consulta="SELECT COUNT(*) FROM 3010_bitacoraNotificacionPGJ WHERE idEvento=".$idEvento." AND documentoXML='".bE($xml)."' AND resultado>0 and tipoNotificacion=1";
		$nReg=$con->obtenerValor($consulta);
		if($nReg>0)
		{
			$consulta="UPDATE 7000_eventosAudiencia SET notificadoPGJ=1 WHERE idRegistroEvento=".$idEvento;
			$con->ejecutarConsulta($consulta);
			return true;
		}
		$parametros["xmlAudiencia"]=$xml;
		$response = $client->call("RegistrarRespuestadeSolicitud".($sistema==1?"":"FSIAP"), $parametros);
		
		@registrarBitacoraNotificacionPGJ($response,$idEvento,$xml,$idFormulario,$idRegistro,1);
		
	}
	else
	{
		
		$consulta="SELECT tipoCarpetaAdministrativa,idFormulario,idRegistro FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".
					$fRegistroEvento["carpetaAdministrativa"]."'";
		$fDatosCarpeta=$con->obtenerPrimeraFila($consulta);
		$tCarpeta=$fDatosCarpeta[0];
		$arrCarpetasPadre=array();
		$carpetaJudicialBase="";
		switch($tCarpeta)
		{
			case 1:
				$carpetaJudicialBase=$fRegistroEvento["carpetaAdministrativa"];
			break;
			case 5:
				$tCarpeta=3;
				obtenerCarpetasPadreIdCarpeta($fRegistroEvento["carpetaAdministrativa"],$arrCarpetasPadre,-1);
				
				$carpetaJudicialBase=sizeof($arrCarpetasPadre)>0?$arrCarpetasPadre[0]["carpetaAdministrativa"]:"";
			break;
			case 6:
				$tCarpeta=2;
				obtenerCarpetasPadreIdCarpeta($fRegistroEvento["carpetaAdministrativa"],$arrCarpetasPadre,-1);
				$carpetaJudicialBase=sizeof($arrCarpetasPadre)>0?$arrCarpetasPadre[0]["carpetaAdministrativa"]:"";
			break;
			default:
				$consulta="UPDATE 7000_eventosAudiencia SET notificadoPGJ=1 WHERE idRegistroEvento=".$idEvento;
				$con->ejecutarConsulta($consulta);
				return true;
			break;
		}
		
		$consulta="SELECT tipoCarpetaAdministrativa,idFormulario,idRegistro FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$carpetaJudicialBase."'";
		$fDatosCarpeta=$con->obtenerPrimeraFila($consulta);
		$idSolicitud="";
		$ctrlUniv="";
		if(($fDatosCarpeta[1]!="")&&($fDatosCarpeta[1]!=-1))
		{
			if($con->existeCampo("ctrluinv","_".$fDatosCarpeta[1]."_tablaDinamica"))
			{
				$consulta="SELECT ctrluinv,idSolicitud,sistema FROM _".$fDatosCarpeta[1]."_tablaDinamica WHERE carpetaAdministrativa='".$carpetaJudicialBase.
							"' ORDER BY  id__".$fDatosCarpeta[1]."_tablaDinamica";
				$fRegistroSolicitudInicial=$con->obtenerPrimeraFila($consulta);

				$ctrlUniv=$fRegistroSolicitudInicial[0];
				$idSolicitud=$fRegistroSolicitudInicial[1];
				$sistema=$fRegistroSolicitudInicial[2];
			}
		}
		
		
		if($ctrlUniv=="")
		{
			$consulta="UPDATE 7000_eventosAudiencia SET notificadoPGJ=1 WHERE idRegistroEvento=".$idEvento;
			$con->ejecutarConsulta($consulta);
			return true;
		}
		$arrDocumentos="";
		$consulta="SELECT idArchivo,nomArchivoOriginal FROM 9074_documentosRegistrosProceso d,908_archivos a WHERE idFormulario=".
					$idFormulario." AND idRegistro=".$idRegistro."
					AND a.idArchivo=d.idDocumento";
		$res=$con->obtenerFilas($consulta);				
		while($fila=mysql_fetch_row($res))
		{
			
			$documentoBase64=obtenerCuerpoDocumentoB64($fila[0]);
			$arrDocumentos.='<documentoAdjunto><nombreDocumento>'.$fila[1].'</nombreDocumento><documentoBase64>'.$documentoBase64.'</documentoBase64>
							</documentoAdjunto>
							';
		}
		

		$consulta="SELECT tipoAudiencia FROM _4_tablaDinamica WHERE id__4_tablaDinamica=".$fRegistro["tipoAudiencia"];
		$tipoAudiencia=$con->obtenerValor($consulta);
		$xml='<?xml version="1.0" encoding=\'ISO-8859-1\'?>
				<DatosAudiencia>
					<tipoNotificacion>'.($nNotificaciones==0?1:2).'</tipoNotificacion>
					<ctrlUinv>'.$ctrlUniv.'</ctrlUinv>
					<idSolicitud>'.$idSolicitud.'</idSolicitud> 
					<fechaAudiencia>'.date("Y-m-d\TH:i:s",strtotime($fRegistro["horaInicioEvento"])).'</fechaAudiencia>
					<fechaFinAudiencia>'.date("Y-m-d\TH:i:s",strtotime($fRegistro["horaFinEvento"])).'</fechaFinAudiencia>
					<edificio>'.$datosEvento->edificio.'</edificio> 
					<sala>'.$datosEvento->sala.'</sala> 
					<jueces>'.$arrJueces.'</jueces> 
					<idEvento>'.$fRegistro["idRegistroEvento"].'</idEvento>
					<tipoAudiencia><![CDATA['.$tipoAudiencia.']]></tipoAudiencia>
					<unidadControl>'.$datosEvento->unidadGestion.'</unidadControl> 
					<documentosAdjuntos>'.$arrDocumentos.'</documentosAdjuntos>	
					<tipoCarpeta>'.$tCarpeta.'</tipoCarpeta>	
					<carpetaAdministrativa>'.$fRegistroEvento["carpetaAdministrativa"].'</carpetaAdministrativa>
				</DatosAudiencia>';

		$consulta="SELECT COUNT(*) FROM 3010_bitacoraNotificacionPGJ WHERE idEvento=".$idEvento." AND documentoXML='".bE($xml)."' AND resultado>0 and tipoNotificacion=1";
		
		$nReg=$con->obtenerValor($consulta);
		if($nReg>0)
		{
			$consulta="UPDATE 7000_eventosAudiencia SET notificadoPGJ=1 WHERE idRegistroEvento=".$idEvento;
			$con->ejecutarConsulta($consulta);
			return true;
		}
		$parametros["xmlAudiencia"]=$xml;
		$response = $client->call("TSJ_RegistrarAudiencia".($sistema==1?"":"FSIAP"), $parametros);

		if(isset($response["faultstring"]))
		{
			$response["RegistrarRespuestadeSolicitudResult"]["mensaje"]=0;
			$response["RegistrarRespuestadeSolicitudResult"]["error"]=$response["faultstring"];
			
		}
		else
		{
			$response["RegistrarRespuestadeSolicitudResult"]["mensaje"]=$response["TSJ_RegistrarAudiencia".($sistema==1?"":"FSIAP")."Result"]["mensaje"];
			$response["RegistrarRespuestadeSolicitudResult"]["error"]=$response["TSJ_RegistrarAudiencia".($sistema==1?"":"FSIAP")."Result"]["error"];
		}
		
		$xml='<?xml version="1.0" encoding=\'ISO-8859-1\'?>
				<DatosAudiencia>
					<tipoNotificacion>'.($nNotificaciones==0?1:2).'</tipoNotificacion>
					<ctrlUinv>'.$ctrlUniv.'</ctrlUinv>
					<idSolicitud>'.$idSolicitud.'</idSolicitud> 
					<fechaAudiencia>'.date("Y-m-d\TH:i:s",strtotime($fRegistro["horaInicioEvento"])).'</fechaAudiencia>
					<fechaFinAudiencia>'.date("Y-m-d\TH:i:s",strtotime($fRegistro["horaFinEvento"])).'</fechaFinAudiencia>
					<edificio>'.$datosEvento->edificio.'</edificio> 
					<sala>'.$datosEvento->sala.'</sala> 
					<jueces>'.$arrJueces.'</jueces> 
					<idEvento>'.$fRegistro["idRegistroEvento"].'</idEvento>
					<tipoAudiencia><![CDATA['.$tipoAudiencia.']]></tipoAudiencia>
					<unidadControl>'.$datosEvento->unidadGestion.'</unidadControl> 
					<documentosAdjuntos></documentosAdjuntos>	
					<tipoCarpeta>'.$tCarpeta.'</tipoCarpeta>	
					<carpetaAdministrativa>'.$fRegistroEvento["carpetaAdministrativa"].'</carpetaAdministrativa>
				</DatosAudiencia>';
		
		@registrarBitacoraNotificacionPGJ($response,$idEvento,$xml,$idFormulario,$idRegistro,4);
		
	}
	return true;
}

function registrarBitacoraNotificacionPGJ($response,$idEvento,$xml,$idFormulario,$idRegistro,$tipoNotificacion=1)
{
	global $con;
	if(isset($response["faultstring"]))
	{
		$response["RegistrarRespuestadeSolicitudResult"]["mensaje"]=0;
		$response["RegistrarRespuestadeSolicitudResult"]["error"]=$response["faultstring"];
		
	}
	else
	{
		if(isset($response["ActualizarCarpetaJudicialResult"]))
		{
			$response["RegistrarRespuestadeSolicitudResult"]["mensaje"]=$response["ActualizarCarpetaJudicialResult"]["mensaje"];
			$response["RegistrarRespuestadeSolicitudResult"]["error"]=$response["ActualizarCarpetaJudicialResult"]["error"];
			
		}
		else
			if(isset($response["ResultadoRespuesta"]))
			{
				$response["RegistrarRespuestadeSolicitudResult"]["mensaje"]=$response["ResultadoRespuesta"]["mensaje"];
				$response["RegistrarRespuestadeSolicitudResult"]["error"]=$response["ResultadoRespuesta"]["error"];
				
			}
			else
				if(isset($response["RegistrarRespuestadeSolicitudFSIAPResult"]))
				{
					$response["RegistrarRespuestadeSolicitudResult"]["mensaje"]=$response["RegistrarRespuestadeSolicitudFSIAPResult"]["mensaje"];
					$response["RegistrarRespuestadeSolicitudResult"]["error"]=$response["RegistrarRespuestadeSolicitudFSIAPResult"]["error"];
					
				}
				else
					if(isset($response["RegistrarRespuestadeSolicitudFSIAPResult"]))
					{
						$response["RegistrarRespuestadeSolicitudResult"]["mensaje"]=$response["RegistrarRespuestadeSolicitudFSIAPResult"]["mensaje"];
						$response["RegistrarRespuestadeSolicitudResult"]["error"]=$response["RegistrarRespuestadeSolicitudFSIAPResult"]["error"];
						
					}
					else
						if(isset($response["ActualizarCarpetaJudicialFSIAPResult"]))
						{
							$response["RegistrarRespuestadeSolicitudResult"]["mensaje"]=$response["ActualizarCarpetaJudicialFSIAPResult"]["mensaje"];
							$response["RegistrarRespuestadeSolicitudResult"]["error"]=$response["ActualizarCarpetaJudicialFSIAPResult"]["error"];
							
						}
					
		
	}
	$consulta="insert into 3010_bitacoraNotificacionPGJ(fechaNotificacion,idEvento,documentoXML,iFormulario,iRegistro,resultado,mensaje,tipoNotificacion)
				values('".date("Y-m-d H:i:s")."',".$idEvento.",'".bE($xml)."',".$idFormulario.",".$idRegistro.",".
				$response["RegistrarRespuestadeSolicitudResult"]["mensaje"].",'".
				cv(utf8_encode($response["RegistrarRespuestadeSolicitudResult"]["error"]))."',".$tipoNotificacion.")";

	if($con->ejecutarConsulta($consulta))
	{
		if(($tipoNotificacion==1)||($tipoNotificacion==4))
		{
			$consulta="UPDATE 7000_eventosAudiencia SET notificadoPGJ=".($response["RegistrarRespuestadeSolicitudResult"]["mensaje"]!="1"?3:1)." WHERE idRegistroEvento=".$idEvento;
			$con->ejecutarConsulta($consulta);
		}
		return true;
	}
	
	
}

function reportarActaMinimaPGJ($idFormulario,$idRegistro)
{
	global $con;
	global $directorioInstalacion;
	global $urlRepositorioDocumentos;
	global $servidorPruebas;
	global $cancelarNotificacionesPGJ;
	global $urlPruebas;
	global $urlProduccion;
	
	if((($servidorPruebas)&&(!$pruebasPGJ))||($cancelarNotificacionesPGJ))
		return true;
	
	$url = $urlProduccion;
	if($pruebasPGJ)
		$url =$urlPruebas;
		
	$consulta="SELECT idEvento,carpetaAdministrativa FROM _210_tablaDinamica WHERE id__210_tablaDinamica=".$idRegistro;
	$fFormularioDocumento=$con->obtenerPrimeraFilaAsoc($consulta);
	$idEvento=$fFormularioDocumento["idEvento"];
	
	$consulta="SELECT idDocumento FROM 3000_formatosRegistrados WHERE 
			idFormulario=".$idFormulario." AND idRegistro=".$idRegistro;
	$idDocumentoActaMinima=$con->obtenerValor($consulta);
	
	$consulta="SELECT nomArchivoOriginal FROM 908_archivos WHERE idArchivo=".$idDocumentoActaMinima;
	$nombreDocumento=$con->obtenerValor($consulta);

	$sistema=0;
	$consulta="SELECT idFormulario,idRegistroSolicitud FROM 7000_eventosAudiencia where idRegistroEvento=".$idEvento;

	$fRegistro=$con->obtenerPrimeraFilaAsoc($consulta);

	$fRegistroEvento=array();
	if($fRegistro["idFormulario"]!=185)
	{
		$consulta="SELECT ctrlSolicitud,idSolicitud,cveSolicitud,carpetaAdministrativa,sistema FROM _".$fRegistro["idFormulario"]."_tablaDinamica 
				WHERE id__".$fRegistro["idFormulario"]."_tablaDinamica=".$fRegistro["idRegistroSolicitud"];
		$fRegistroEvento=$con->obtenerPrimeraFilaAsoc($consulta);
	}
	else
	{
		$consulta="SELECT iFormulario,iRegistro FROM _185_tablaDinamica WHERE id__185_tablaDinamica=".$fRegistro["idRegistroSolicitud"];
		$fRegistroPromocion=$con->obtenerPrimeraFila($consulta)	;
		$consulta="SELECT ctrlSolicitud,idSolicitud,cveSolicitud,carpetaAdministrativa,sistema FROM _".$fRegistroPromocion[0]."_tablaDinamica 
			WHERE id__".$fRegistroPromocion[0]."_tablaDinamica=".$fRegistroPromocion[1];

		$fRegistroEvento=$con->obtenerPrimeraFilaAsoc($consulta);
	
	}
	
	
	if($fRegistroEvento["ctrlSolicitud"]=="")
		return true;
	$sistema=$fRegistroEvento["sistema"];
	
	$directorioDestino=obtenerRutaDocumento($idDocumentoActaMinima);
	$cuerpoDocumento=bE(file_get_contents($directorioDestino));
	
	$cadXML='<?xml version="1.0" encoding="ISO-8859-1"?> 
                <notificacionDocumento>
					<idCtrProcedimiento>'.$idEvento.'</idCtrProcedimiento>
					<idAcuse>'.$idEvento.'</idAcuse>
					<ctrSolicitud>'.$fRegistroEvento["ctrlSolicitud"].'</ctrSolicitud>
					<idSolicitud>'.$fRegistroEvento["idSolicitud"].'</idSolicitud> 
					<carpetaAdministrativa>'.$fFormularioDocumento["carpetaAdministrativa"].'</carpetaAdministrativa>
					<nombreDocumento>'.$nombreDocumento.'</nombreDocumento>
					<textoDocumento></textoDocumento>
					<documentoAdjunto>'.$cuerpoDocumento.'</documentoAdjunto>
					<comentarios></comentarios>
				</notificacionDocumento>';


	$client = new nusoap_client($url,"wsdl");
	$parametros=array();
		
	$parametros["xmlActaMinima"]=$cadXML;
	$response = $client->call("RegistrarActaMinima".($sistema==1?"":"FSIAP"), $parametros);
	if(isset($response["faultstring"]))
	{
		$response["RegistrarRespuestadeSolicitudResult"]["mensaje"]=0;
		$response["RegistrarRespuestadeSolicitudResult"]["error"]=$response["faultstring"];
		
	}
	else
	{
		$response["RegistrarRespuestadeSolicitudResult"]["mensaje"]=$response["RegistrarActaMinima".($sistema==1?"":"FSIAP")."Result"]["mensaje"];
		$response["RegistrarRespuestadeSolicitudResult"]["error"]=$response["RegistrarActaMinima".($sistema==1?"":"FSIAP")."Result"]["error"];
	}
	@registrarBitacoraNotificacionPGJ($response,$idEvento,$xml,$idFormulario,$idRegistro,5);
	
	return true;
}

function reportarURLAudiencia($idEvento)
{
	global $con;
	global $servidorPruebas;
	global $cancelarNotificacionesPGJ;
	global $urlPruebas;
	global $urlProduccion;
	if((($servidorPruebas)&&(!$pruebasPGJ))||($cancelarNotificacionesPGJ))
		return true;
	
	$url = $urlProduccion;
	if($pruebasPGJ)
		$url =$urlPruebas;
		
	$consulta="SELECT idFormulario,idRegistroSolicitud,urlMultimedia FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$idEvento;
	$fRegistro=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$fRegistroEvento=array();
	if($fRegistro["idFormulario"]!=185)
	{
		$consulta="SELECT ctrlSolicitud,idSolicitud,cveSolicitud,carpetaAdministrativa,sistema FROM _".$fRegistro["idFormulario"]."_tablaDinamica 
				WHERE id__".$fRegistro["idFormulario"]."_tablaDinamica=".$fRegistro["idRegistroSolicitud"];
		$fRegistroEvento=$con->obtenerPrimeraFilaAsoc($consulta);
	}
	else
	{
		$consulta="SELECT iFormulario,iRegistro FROM _185_tablaDinamica WHERE id__185_tablaDinamica=".$fRegistro["idRegistroSolicitud"];
		$fRegistroPromocion=$con->obtenerPrimeraFila($consulta)	;
		if($fRegistroPromocion[0]=="N/E")
		{
			return true;
		}
		$consulta="SELECT ctrlSolicitud,idSolicitud,cveSolicitud,carpetaAdministrativa,sistema FROM _".$fRegistroPromocion[0]."_tablaDinamica 
			WHERE id__".$fRegistroPromocion[0]."_tablaDinamica=".$fRegistroPromocion[1];
		$fRegistroEvento=$con->obtenerPrimeraFilaAsoc($consulta);
	
	}
	
	if($fRegistroEvento["ctrlSolicitud"]=="")
		return true;
	
	$sistema=$fRegistroEvento["sistema"];

	$client = new nusoap_client($url,"wsdl");
	$parametros=array();
	$parametros["ctrSolicitud"]=$fRegistroEvento["ctrlSolicitud"];
	$parametros["idSolicitud"]=$fRegistroEvento["idSolicitud"];
	$parametros["URL"]=$fRegistro["urlMultimedia"];
	$parametros["fechaCreacionVideo"]=date("Y-m-d H:i:s");

	$response = $client->call("RegistrarLinkVideo".($sistema==1?"":"FSIAP"), $parametros);

	$responseProcesado["RegistrarRespuestadeSolicitudResult"]["mensaje"]=$response["RegistrarLinkVideo".($sistema==1?"":"FSIAP")."Result"]["mensaje"];
	$responseProcesado["RegistrarRespuestadeSolicitudResult"]["error"]=$response["RegistrarLinkVideo".($sistema==1?"":"FSIAP")."Result"]["error"];
	
	@registrarBitacoraNotificacionPGJ($responseProcesado,$idEvento,'',$fRegistro["idFormulario"],$fRegistro["idRegistroSolicitud"],6);

	return true;
}

function obtenerStatusActividades()
{
	$arrActividades=array();
	
	$oStatus=array();
	
	$oStatus["valor"]="";
	$oStatus["etiqueta"]=utf8_encode("Cualquiera");
	array_push($arrActividades,$oStatus);
	
	$oStatus=array();
	
	$oStatus["valor"]=1;
	$oStatus["etiqueta"]=utf8_encode("En espera de atenciÛn");
	
	array_push($arrActividades,$oStatus);
	
	$oStatus=array();
	
	$oStatus["valor"]="2";
	$oStatus["etiqueta"]="Atendida";
	
	array_push($arrActividades,$oStatus);
	
	$oStatus=array();
	
	$oStatus["valor"]=10;
	$oStatus["etiqueta"]="Delegada";
	
	array_push($arrActividades,$oStatus);
	return $arrActividades;
}

function cambiarCarpetaJudicial($carpeta,$uDestino)
{
	global $con;
	
	$x=0;
	$uDestino=str_pad($uDestino,3,"0",STR_PAD_LEFT);
	$query=array();
	$query[$x]="begin";
	$x++;
	
	$consulta="SELECT id__46_tablaDinamica,carpetaAdministrativa FROM _46_tablaDinamica WHERE carpetaAdministrativa='".$carpeta."'";
	$fSolicitud=$con->obtenerPrimeraFila($consulta);
	
	$idRegistro=$fSolicitud[0];
	$carpetaOriginal=$fSolicitud[1];
	
	$consulta="SELECT id__17_tablaDinamica,idReferencia FROM _17_tablaDinamica WHERE claveUnidad='".$uDestino."'";
	$fCentroGestion=$con->obtenerPrimeraFila($consulta);
	$iCentroGestion=$fCentroGestion[0];
	$idEdificio=$fCentroGestion[1];
	
	$consulta="SELECT folioActual FROM 7004_seriesUnidadesGestion FROM WHERE anio='".date("Y")."' AND idUnidadGestion=".$iCentroGestion;
	$folioActual=$con->obtenerValor($consulta);
	$folioActual++;
	
	$folioCarpeta=$uDestino."/".str_pad($folioActual,4,"0",STR_PAD_LEFT)."/".date("Y");
	
	$query[$x]="update _46_tablaDinamica set carpetaAdministrativa='".$folioCarpeta."' where id__46_tablaDinamica=".$idRegistro;
	$x++;
	$query[$x]="UPDATE 7007_contenidosCarpetaAdministrativa SET carpetaAdministrativa='".$folioCarpeta."' 
				WHERE carpetaAdministrativa='".$carpetaOriginal."'";
	$x++;
	
	$query[$x]="UPDATE 7006_carpetasAdministrativas SET carpetaAdministrativa='".$folioCarpeta."',
				unidadGestion='".$uDestino."' WHERE carpetaAdministrativa='".$carpetaOriginal."'";
	$x++;
	
	$query[$x]="UPDATE 7000_eventosAudiencia SET idEdificio=".$idEdificio.",idCentroGestion=".$iCentroGestion." WHERE idRegistroEvento IN 
					(
					SELECT idRegistroContenidoReferencia FROM 7007_contenidosCarpetaAdministrativa WHERE carpetaAdministrativa='".$folioCarpeta."' 
					AND tipoContenido=3
					)";
	$x++;
	$query[$x]="delete from  7001_eventoAudienciaJuez  WHERE idRegistroEvento IN 
					(
					SELECT idRegistroContenidoReferencia FROM 7007_contenidosCarpetaAdministrativa WHERE carpetaAdministrativa='".$folioCarpeta."' 
					AND tipoContenido=3
					)";
	$x++;
	
	
	$query[$x]="update 7004_seriesUnidadesGestion set folioActual=folioActual+1 WHERE anio='".date("Y")."' AND idUnidadGestion=".$iCentroGestion;
	$x++;
	
	$query[$x]="DELETE FROM 9060_tableroControl_4 WHERE numeroCarpetaAdministrativa='".$carpetaOriginal."'";
	$x++;
	$query[$x]="DELETE FROM 9060_tableroControl_5 WHERE numeroCarpetaAdministrativa='".$carpetaOriginal."'";
	$x++;
	
	$query[$x]="commit";
	$x++;
	return $con->ejecutarBloque($query);
	
	
}



//----
function esHorarioNormalV2($horario)
{
	$horaSolicitud=strtotime($horario);
	$horaInicio=strtotime(date("Y-m-d ",$horaSolicitud)." 09:00:00");
	$horarioFinal=strtotime(date("Y-m-d ",$horaSolicitud)." 13:30:00");
	
	if(esDiaHabil($horario))
	{
		if(($horaSolicitud>=$horaInicio)&&($horaSolicitud<=$horarioFinal))
		{
			return true;
		}
		
	}
	return false;
}

function esHorarioGuardiaV2($horario)
{
	$horaSolicitud=strtotime($horario);
	$horarioInicial=strtotime(date("Y-m-d ",$horaSolicitud)." 13:30");
	//$horarioFinal=strtotime(date("Y-m-d ",$horaSolicitud)." 16:30");
	
	if(date("w",$horaSolicitud)==0)
	{
		if($horaSolicitud>$horarioInicial)
		{
			return false;
		}
	}
	
	/*if(date("w",$horaSolicitud)==1)
	{
		if($horaSolicitud<$horarioInicial)
		{
			return false;
		}
	}*/
	
	if(date("w",$horaSolicitud)==5)
	{
		if($horaSolicitud>$horarioInicial)
		{
			return true;
		}
	}
	
	
	if(!esDiaHabil($horario))
	{
		return true;
	}
	
	
	return false;
}

function esHorarioNocturnoV2($horario)
{
	if((!esHorarioNormalV2($horario))&&(!esHorarioGuardiaV2($horario)))
		return true;
	return false;
}

//----

function esHorarioNormalTipoA($horario)
{
	$horaSolicitud=strtotime($horario);
	$horaInicio=strtotime(date("Y-m-d ",$horaSolicitud)." 09:00:00");
	$horarioFinal=strtotime(date("Y-m-d ",$horaSolicitud)." 14:00:00");
	if(date("w",$horaSolicitud)==5)
	{
		$horarioFinal=strtotime(date("Y-m-d ",$horaSolicitud)." 13:00:00");
	}
	
	/*if(date("w",$horaSolicitud)==0)
	{
		if($horaSolicitud>$horarioFinal)
		{
			return true;
		}
	}
	
	if(date("w",$horaSolicitud)==1)
	{
		if($horaSolicitud<$horarioFinal)
		{
			return true;
		}
	}*/
	
	
	if(esDiaHabil($horario))
	{
		if(($horaSolicitud>=$horaInicio)&&($horaSolicitud<=$horarioFinal))
		{
			return true;
		}
		
	}
	return false;

}

function esHorarioNormalTipoB($horario)
{
	
	$horaSolicitud=strtotime($horario);
	$horarioInicial=strtotime(date("Y-m-d ",$horaSolicitud)." 09:00");
	$horarioFinal=strtotime(date("Y-m-d ",$horaSolicitud)." 13:30");
	
	if(!esDiaHabil($horario))
	{
		return false;
	}
	
	/*if(date("w",$horaSolicitud)==0)
	{
		
		if($horaSolicitud>$horarioFinal)
		{
			return false;
		}
	}
	
	if(date("w",$horaSolicitud)==1)
	{
		if($horaSolicitud<$horarioInicial)
		{
			return false;
		}
	}*/
	
	if(($horaSolicitud>=$horarioInicial)&&($horaSolicitud<=$horarioFinal))
	{
		return true;
	}
	return false;
	
}

function esHorarioGuardiaTipoB($horario)
{
	$horaSolicitud=strtotime($horario);
	$horarioInicial=strtotime(date("Y-m-d ",$horaSolicitud)." 13:30");
	//$horarioFinal=strtotime(date("Y-m-d ",$horaSolicitud)." 16:30");
	
	if(date("w",$horaSolicitud)==0)
	{
		if($horaSolicitud>$horarioInicial)
		{
			return false;
		}
	}
	
	/*if(date("w",$horaSolicitud)==1)
	{
		if($horaSolicitud<$horarioInicial)
		{
			return false;
		}
	}*/
	
	if(date("w",$horaSolicitud)==5)
	{
		if($horaSolicitud>$horarioInicial)
		{
			return true;
		}
	}
	
	
	if(!esDiaHabil($horario))
	{
		return true;
	}
	
	
	return false;
}

function esHorarioNocturnoTipoB($horario)
{
	if((!esHorarioNormalTipoB($horario))&&(!esHorarioGuardiaTipoB($horario)))
		return true;
	return false;
}

function determinarHorarioA($horario)
{

	if(esHorarioNormalV2($horario))
		return 1;
	else
		if(esHorarioGuardiaV2($horario))
			return 2;
		
	return 3;
}

function determinarHorarioB($horario)
{

	if(esHorarioNormalTipoB($horario))
		return 1;
	else
		if(esHorarioGuardiaTipoB($horario))
			return 2;
		else
			return 3;
}

function enviarCorreoNotificaciones($idFormulario,$idRegistro)
{
	
	global $con;
	global $baseDir;
	global $servidorPruebas;
	global $urlRepositorioDocumentos;
	$prueba=$servidorPruebas;	
	/*if(isset($_SESSION["deshabilitarNotificaciones"])&&($_SESSION["deshabilitarNotificaciones"]))
		return true;*/


	$destinatario="ugestion01.sspenitenciariodf@gmail.com";
	
	$fechaActual=date("Y-m-d");
	
	$horaActual=strtotime(date("Y-m-d H:i:s"));
	
	/*$fechaInicial=strtotime($fechaActual." 16:30");
	$fechaFinal=strtotime($fechaActual." 06:30");*/
	$aplicaEnvio=true;
	
	if(!$aplicaEnvio)
		return true;
	
	
	$consulta="SELECT delitoGrave FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$tipificacion=$con->obtenerValor($consulta);
	
	
	$consulta="SELECT ctrlSolicitud,fechaCreacion,carpetaAdministrativa,tipoAudiencia FROM _46_tablaDinamica WHERE id__46_tablaDinamica=".$idRegistro;
	$fSolicitud=$con->obtenerPrimeraFila($consulta);
	
	$consulta="SELECT tipoAudiencia FROM _4_tablaDinamica WHERE id__4_tablaDinamica=".$fSolicitud[3];
	$tAudiencia=$con->obtenerValor($consulta);
	
	/*if(($horaActual>=$fechaFinal)&&($horaActual<=$fechaInicial))
		return true;*/
	
	$arrArchivos=array();	 
	
	$arrCopia=array();
	$arrCopiaOculta=array();
	$nCopias=0;
	if(!$prueba)
	{
		$arrCopia[0][0]="alejandroha99@yahoo.com";
		$arrCopia[0][1]="";
	
		
	}
	else
	{
		$arrCopia[0][0]="marco.magana@grupolatis.net";
		$arrCopia[0][1]="";
	}	
	
	$idEdificio="";
	
	$consulta="SELECT folioCarpetaInvestigacion,carpetaAdministrativa,ctrlSolicitud,tipoAudiencia FROM _46_tablaDinamica WHERE id__46_tablaDinamica=".$idRegistro;
	$fSolicitud=$con->obtenerPrimeraFila($consulta);	

	$arrTiposAudiencia=array();
	$arrTiposAudiencia[26]=1;
	$arrTiposAudiencia[99]=1;
	$arrTiposAudiencia[104]=1;
	$arrTiposAudiencia[19]=1;
	
	$notificaPGJ=false;
	if(isset($arrTiposAudiencia[$fSolicitud[3]]))
	{
		$notificaPGJ=true;
	}
	
	$consulta="SELECT unidadGestion FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$fSolicitud[1]."'";
	$unidadGestion=$con->obtenerValor($consulta);

	if(!$prueba)
	{
		$consulta="SELECT id__17_tablaDinamica FROM _17_tablaDinamica WHERE claveUnidad='".$unidadGestion."'";
		$idCentroGestion=$con->obtenerValor($consulta);
		
		
		$nDestinatario=0;
		$consulta="SELECT mail FROM _362_tablaDinamica f,_362_chkTipoNotificacion tn WHERE  
					f.idReferencia=".$idCentroGestion." AND tn.idPadre=f.id__362_tablaDinamica AND tn.idOpcion=1";
		
		$rMail=$con->obtenerFilas($consulta);
		while($fMail=mysql_fetch_row($rMail))
		{
			$arrCopiaOculta[$nCopias][0]=$fMail[0];
			$arrCopiaOculta[$nCopias][1]="";
			$nCopias++;
		}
		
		
	}
	
	$titulo="".$fSolicitud[0].", Tipo de audiencia: ".$tAudiencia;;
	$cuerpo="Acuse de recepci&oacute;n de solicitud de audiencia, tipo de audiencia: ".$tAudiencia;
	
	$nPos=0;
	
	$nomArchivo="";
	$consulta="SELECT idReferencia FROM _17_tablaDinamica WHERE claveUnidad='".$unidadGestion."'";

	$idEdificio=$con->obtenerValor($consulta);
	
	
	if(!esHorarioNormalV2(date("Y-m-d H:i:s",$horaActual)))
	{

		switch($idEdificio)
		{
			case 4:

				$nomArchivo=generarDocumentoAcuseSullivan($idFormulario,$idRegistro);
			break;
			case 5:
				$nomArchivo=generarDocumentoAcuseLaVista($idFormulario,$idRegistro);
			break;
			default:
				$nomArchivo=generarDocumentoAcuse($idFormulario,$idRegistro);
			break;
		}
	}
	else
	{

		$notificaPGJ=false;
	}
	
	
	if(($nomArchivo===false)&&($fSolicitud[2]!=""))
		return true;
	
	if((!$notificaPGJ)||($fSolicitud[2]==""))
	{

		$nomArchivo=generarDocumentoAcuseOrdinario($idFormulario,$idRegistro);
		$arrCopia=array();
		$destinatario="notificaciones.sgjp@tsjcdmx.gob.mx";
	}
	
	if(($nomArchivo!==false) && ($nomArchivo!=""))
	{
		$arrArchivos[$nPos][0]="./".$nomArchivo;
		$arrArchivos[$nPos][1]="Acuse Recepcion de solicitud de Audiencia".(str_replace("/","-",str_replace(" ","-",$fSolicitud[0]))).".pdf";
		$nPos++;
	}
	
	$nDocumentoSolicitud=1;
	$consulta="SELECT idDocumento FROM 9074_documentosRegistrosProceso WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro;
	$rDocumentos=$con->obtenerFilas($consulta);
	while($fDocumento=mysql_fetch_row($rDocumentos))
	{
		$consulta="SELECT nomArchivoOriginal,tamano FROM 908_archivos WHERE idArchivo=".$fDocumento[0];
		$fDatosArchivos=$con->obtenerPrimeraFila($consulta);
		$nomArchivoOriginal=$fDatosArchivos[0];
		if($fDatosArchivos[1]>6291456)
			continue;
		$aNombreArchivo=explode(".",$nomArchivoOriginal);
		$extension=$aNombreArchivo[sizeof($aNombreArchivo)-1];
		$directorioDestino=obtenerRutaDocumento($fDocumento[0]);
		$arrArchivos[$nPos][0]=$directorioDestino;
		$arrArchivos[$nPos][1]="Solicitud de audiencia documento_".$nDocumentoSolicitud.".".$extension;
		$nPos++;
		$nDocumentoSolicitud++;
		
	}

	
	$resultadoEnvio=false;
	if(!$prueba)
	{
		
		$resultadoEnvio=enviarMailGMail($destinatario,$titulo,$cuerpo,$arrArchivos,$arrCopia,$arrCopiaOculta);
		
	}
	else
	{
		$resultadoEnvio=enviarMailGMail("notificacionesTSJCDMX@grupolatis.net",$titulo,$cuerpo,$arrArchivos,$arrCopia,$arrCopiaOculta);
		
	
	}
	/*if($_SESSION["idUsr"]==1)
		var_dump( $resultadoEnvio);*/
	if($resultadoEnvio)
	{
		$consulta="UPDATE _46_tablaDinamica SET notificacionCorreo=1 WHERE id__46_tablaDinamica=".$idRegistro;
		$con->ejecutarConsulta($consulta);
	}
	unlink($nomArchivo);
}

function enviarCorreoWebServicesSolicitudInicial($idRegistro)
{
	global $baseDir;
	global $con;
	global $urlRepositorioDocumentos;
	$arrCopia=array();
	$arrCopiaOculta=array();
	$idFormulario=46;
	
	
	$arrCopia[0][0]="recepcionsolicitudespgj@hotmail.com";
	$arrCopia[0][1]="";
	
	
	$nCopias=1;
		
	$consulta="SELECT folioCarpetaInvestigacion,carpetaAdministrativa FROM _46_tablaDinamica WHERE id__46_tablaDinamica=".$idRegistro;
	$fSolicitud=$con->obtenerPrimeraFila($consulta);	
	
	
	$nPos=0;
	$titulo="Solicitud ".$fSolicitud[0];
	$cuerpo="Nueva solicitud registrada";
	
	
	$nDocumentoSolicitud=1;
	$consulta="SELECT idDocumento FROM 9074_documentosRegistrosProceso WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro;
	$rDocumentos=$con->obtenerFilas($consulta);
	while($fDocumento=mysql_fetch_row($rDocumentos))
	{
		$consulta="SELECT nomArchivoOriginal FROM 908_archivos WHERE idArchivo=".$fDocumento[0];
		$nomArchivoOriginal=$con->obtenerValor($consulta);
		$aNombreArchivo=explode(".",$nomArchivoOriginal);
		$extension=$aNombreArchivo[sizeof($aNombreArchivo)-1];
		$directorioDestino=obtenerRutaDocumento($fDocumento[0]);
		$arrArchivos[$nPos][0]=$directorioDestino;
		$arrArchivos[$nPos][1]="Solicitud de audiencia documento_".$nDocumentoSolicitud.".".$extension;
		$nPos++;
		$nDocumentoSolicitud++;
		
	}
	
	enviarMailGMail("notificacionesTSJCDMX@grupolatis.net",$titulo,$cuerpo,$arrArchivos,$arrCopia,$arrCopiaOculta);
	
}

function enviarCorreoWebServicesSolicitudPromocionUrgente($idRegistro)
{
	global $baseDir;
	global $con;
	global $urlRepositorioDocumentos;
	$arrCopia=array();
	$arrCopiaOculta=array();
	$idFormulario=96;
	
	
	$arrCopia[0][0]="recepcionsolicitudespgj@hotmail.com";
	$arrCopia[0][1]="";
	
	
	$nCopias=1;
		
	$consulta="SELECT carpetaAdministrativa FROM _96_tablaDinamica WHERE id__96_tablaDinamica=".$idRegistro;
	$fSolicitud=$con->obtenerPrimeraFila($consulta);	
	
	
	$nPos=0;
	$titulo="Solicitud ".$fSolicitud[0]." promocion urgente";
	$cuerpo="Promocion urgente recibida";
	
	$nDocumentoSolicitud=1;
	$consulta="SELECT idDocumento FROM 9074_documentosRegistrosProceso WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro;
	$rDocumentos=$con->obtenerFilas($consulta);
	while($fDocumento=mysql_fetch_row($rDocumentos))
	{
		$consulta="SELECT nomArchivoOriginal FROM 908_archivos WHERE idArchivo=".$fDocumento[0];
		$nomArchivoOriginal=$con->obtenerValor($consulta);
		$aNombreArchivo=explode(".",$nomArchivoOriginal);
		$extension=$aNombreArchivo[sizeof($aNombreArchivo)-1];
		$directorioDestino=obtenerRutaDocumento($fDocumento[0]);
		$arrArchivos[$nPos][0]=$directorioDestino;
		$arrArchivos[$nPos][1]="Solicitud Promocion documento_".$nDocumentoSolicitud.".".$extension;
		$nPos++;
		$nDocumentoSolicitud++;
		
	}
	
	
	enviarMailGMail("notificacionesTSJCDMX@grupolatis.net",$titulo,$cuerpo,$arrArchivos,$arrCopia,$arrCopiaOculta);
	
}

function enviarCorreoWebServicesSolicitudPromocionUrgenteUGJ($idRegistro)
{
		
	global $baseDir;
	global $con;
	global $urlRepositorioDocumentos;
	
	
	$arrSolicitudesCopiaDEGJ=array();
	$arrSolicitudesCopiaDEGJ[160]=1;
	$arrSolicitudesCopiaDEGJ[165]=1;
	$arrSolicitudesCopiaDEGJ[168]=1;
	
	
	/*if(isset($_SESSION["deshabilitarNotificaciones"])&&($_SESSION["deshabilitarNotificaciones"]))
		return true;*/
	$arrArchivos=array();
	$arrCopia=array();
	$arrCopiaOculta=array();
	$idFormulario=96;
	
	$destinatario="";
	
	$nCopias=1;
		
	$consulta="SELECT carpetaAdministrativa,tipoPromociones,tipoAudiencia,relacionPromocion,codigoInstitucion,
				carpetaAdministrativaReferida,cveSolicitud 
				FROM _96_tablaDinamica WHERE id__96_tablaDinamica=".$idRegistro;
	
	
		
	$fSolicitud=$con->obtenerPrimeraFila($consulta);	

	
	$tipoAtencion=0;
	if($fSolicitud[1]==2)
	{
		$consulta="SELECT tipoAtencion FROM _4_tablaDinamica WHERE id__4_tablaDinamica=".$fSolicitud[2];
		$tipoAtencion=$con->obtenerValor($consulta);
		if($tipoAtencion=="")
			$tipoAtencion=0;
	}
	else
	{
		if($fSolicitud["6"]!="")
		{
			$consulta="SELECT naturalezaSolicitud FROM _285_tablaDinamica 
						WHERE cveTipoSolicitud='".$fSolicitud["6"]."'";
			$fTipoAudiencia=$con->obtenerPrimeraFila($consulta);
			if($fTipoAudiencia)
			{
				$tipoAtencion=$fTipoAudiencia[0];
			}
		}
	}
	
	$tipoSolicitud=0;
	if($tipoAtencion==2)
		$tipoSolicitud=2;
	else
		$tipoSolicitud=3;
		
	if($fSolicitud[3]==1)
	{
		$consulta="SELECT unidadGestion FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$fSolicitud[0]."'";
		
		$unidadGestion=$con->obtenerValor($consulta);
	}
	else
	{
		$unidadGestion=$fSolicitud[4];
	}
	
	
	
	$consulta="SELECT id__17_tablaDinamica FROM _17_tablaDinamica WHERE claveUnidad='".$unidadGestion."'";
	
	$idCentroGestion=$con->obtenerValor($consulta);
	if($idCentroGestion=="")
		$idCentroGestion=-1;
	$nDestinatario=0;
	$consulta="SELECT mail FROM _362_tablaDinamica f,_362_chkTipoNotificacion tn WHERE  
				f.idReferencia=".$idCentroGestion." AND tn.idPadre=f.id__362_tablaDinamica AND tn.idOpcion=".$tipoSolicitud;

	$rMail=$con->obtenerFilas($consulta);
	while($fMail=mysql_fetch_row($rMail))
	{
		if($nDestinatario==0)
		{
			$destinatario=$fMail[0];
			$nDestinatario++;
		}
		else
		{
			
			$arrCopia[($nDestinatario-1)][0]=$fMail[0];
			$arrCopia[($nDestinatario-1)][1]="";
			$nDestinatario++;
			
	
		
		}
	}
	
	
	
	
	$tipoPromocion="Promocion";
	if(($fSolicitud[1]!="")&&($fSolicitud[1]!="-1"))
	{
		$consulta="SELECT nombreTipoPromocion FROM _97_tablaDinamica WHERE id__97_tablaDinamica=".$fSolicitud[1];
		$tipoPromocion=$con->obtenerValor($consulta);
	}
	$carpeta="(Sin carpeta)";
	if($fSolicitud[0]!="")
		$carpeta=$fSolicitud[0];
	else
		if($fSolicitud[5]!="")
			$carpeta=$fSolicitud[5];
	$nPos=0;
	$titulo=$tipoPromocion." turnada a la unidad de gestion, carpeta ".$carpeta."";
	$cuerpo=$titulo;

	if(($fSolicitud[2]!=-1)&&(($fSolicitud[2]!="")))
	{
		$consulta="SELECT tipoAudiencia FROM _4_tablaDinamica WHERE id__4_tablaDinamica=".$fSolicitud[2];
		$nombreAudiencia=$con->obtenerValor($consulta);
		$titulo="Solicitud de audiencia: ".$nombreAudiencia.", carpeta ".$carpeta."";
	}
	else
	{
		if($fSolicitud[6]!="")
		{
			$consulta="SELECT tipoSolicitud FROM _285_tablaDinamica WHERE cveTipoSolicitud='".$fSolicitud[6]."'";
			
			$nombreAudiencia=$con->obtenerValor($consulta);
			$titulo="Solicitud: ".$nombreAudiencia.", carpeta ".$carpeta."";
		}
		
	}
	
	$nDocumentoSolicitud=1;
	$consulta="SELECT idDocumento FROM 9074_documentosRegistrosProceso WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro;
	$rDocumentos=$con->obtenerFilas($consulta);
	while($fDocumento=mysql_fetch_row($rDocumentos))
	{
		$consulta="SELECT nomArchivoOriginal,tamano FROM 908_archivos WHERE idArchivo=".$fDocumento[0];
		
		$fDatosArchivo=$con->obtenerPrimeraFila($consulta);
		if($fDatosArchivo[1]<=6291456)
		{
			$nomArchivoOriginal=$fDatosArchivo[0];
			$aNombreArchivo=explode(".",$nomArchivoOriginal);
			$extension=$aNombreArchivo[sizeof($aNombreArchivo)-1];
			$directorioDestino=obtenerRutaDocumento($fDocumento[0]);
			$arrArchivos[$nPos][0]=$directorioDestino;
			$arrArchivos[$nPos][1]="Solicitud Promocion documento_".$nDocumentoSolicitud.".".$extension;
			$nPos++;
			$nDocumentoSolicitud++;
		}
		
	}


	if(isset($arrSolicitudesCopiaDEGJ[$fSolicitud[6]]))
	{
		$oDestinatario=array();
		$oDestinatario[0]="brendarely.garcia@tsjcdmx.gob.mx";
		$oDestinatario[1]="";
		
		array_push($arrCopia,$oDestinatario);
		$oDestinatario=array();
		$oDestinatario[0]="brenda.guzman@tsjcdmx.gob.mx";
		$oDestinatario[1]="";
		
		array_push($arrCopia,$oDestinatario);
		$oDestinatario=array();
		$oDestinatario[0]="jose.ornelas@tsjcdmx.gob.mx";
		$oDestinatario[1]="";
		
		array_push($arrCopia,$oDestinatario);
	}

	/*if($_SESSION["idUsr"]==1)
	{
		varDUmp($arrArchivos);
		varDUmp($destinatario);
		varDUmp($arrCopia);
		
		varDUmp($arrCopiaOculta);
		
		return;
	}*/

	if($nDestinatario>0)
	{
		if(enviarMailGMail($destinatario,$titulo,$cuerpo,$arrArchivos,$arrCopia,$arrCopiaOculta))
		{
			$consulta="UPDATE _96_tablaDinamica SET notificacionCorreo=1 WHERE id__96_tablaDinamica=".$idRegistro;
			$con->ejecutarConsulta($consulta);
	
		}
	}
}

function enviarMailGMail($destinatario,$asunto,$mensaje,$arrArchivos=null,$arrCopia=null,$arrCopiaOculta=null)
{
	global $con;
	global $servidorPruebas;
	try
	{
		if($servidorPruebas)
		{
			
			return true;
		}
		if($destinatario=="")
		{
			
			return true;
		}
		if($arrCopiaOculta==null)
		{
			$arrCopiaOculta=array();
		}
		
		
		$oMail=array();
		$oMail[0]="verificacion_sigjp@tsjcdmx.gob.mx";
		$oMail[1]="";
		array_push($arrCopiaOculta,$oMail);
		/*
		$mail = new PHPMailer();
		$mail->SMTPDebug = 0;
		$mail->Debugoutput = 'html';
		$mail->IsSMTP();                                      // set mailer to use SMTP
		
		$mail->Host = "email-smtp.us-west-2.amazonaws.com";  // specify main and backup server
		$mail->Port = 25;
		$mail->SMTPAuth = true;     // turn on SMTP authentication
		$mail->Username = "AKIAIYASXHU7RDYGU3PA";  // SMTP username
		$mail->Password = "Aoc1+5ioi+Pe7Hb+zDAgS07AqIxHYXoc+IPm5MtMv1uy"; 
		
		*/
		$mail = new PHPMailer();
		$mail->SMTPDebug = 0;
		//@$mail->SMTPDebug=100;
		
		/*if($_SESSION["idUsr"]==1)
		{
			$mail->SMTPDebug=100;
		}*/
		$mail->Debugoutput = 'html';
		$mail->IsSMTP();                                      // set mailer to use SMTP
		
		$mail->Host = "email-smtp.us-west-2.amazonaws.com";  // specify main and backup server
		$mail->Port = 587 ;
		$mail->SMTPAuth = true;     // turn on SMTP authentication
		$mail->SMTPOptions = array(
									'ssl' => array(
													'verify_peer' => false,
													'verify_peer_name' => false,
													'allow_self_signed' => true
												)
								);
		$mail->Username = "AKIAIYASXHU7RDYGU3PA";  // SMTP username
		$mail->Password = "Aoc1+5ioi+Pe7Hb+zDAgS07AqIxHYXoc+IPm5MtMv1uy";
		
		$mail->SetFrom ("notificaciones.sgjp@tsjcdmx.gob.mx","notificaciones.sgjp@tsjcdmx.gob.mx");
		$mail->AddAddress($destinatario);
	
		
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
		
		$mail->Subject = utf8_decode($asunto);
		$mail->Body    = utf8_decode($mensaje);
		
		$resultado=$mail->Send();
		return $resultado;
	}
	catch(Exception $e)
	{
		
		$consulta="INSERT INTO 3009_bitacoraNotificacionCorreo(fechaError,asunto,destinatario,msgError)
					VALUES('".date("Y-m-d H:i:s")."','".cv($asunto)."','".cv($destinatario)."','".cv($e->getMessage())."')";
		$con->ejecutarConsulta($consulta);
		return false;
	}
} 

function generarDocumentoAcuse($idFormulario,$idRegistro)
{
	global $con;
	global $baseDir;
	global $arrMesLetra;	
	
	$consulta="SELECT solicitudXML,fechaCreacion,carpetaAdministrativa,tipoAudiencia FROM _46_tablaDinamica WHERE id__46_tablaDinamica=".$idRegistro;
	$fSolicitud=$con->obtenerPrimeraFila($consulta);
	
	$consulta="SELECT tipoAudiencia FROM _4_tablaDinamica WHERE id__4_tablaDinamica=".$fSolicitud[3];
	$tAudiencia=$con->obtenerValor($consulta);
	
	$xmlSolicitud=bD($fSolicitud[0]);
	if($xmlSolicitud=="")
		$xmlSolicitud=obtenerContenidoArchivoDatosPeticion($idFormulario,$idRegistro);
	$fechaCreacion=strtotime($fSolicitud[1]);
	if($xmlSolicitud=="")
	{
		return false;
	}
	
	
	$oXml=$xmlSolicitud;
	$cXML=simplexml_load_string($oXml);
	

	$consulta="SELECT fiscalia FROM _286_tablaDinamica WHERE id__286_tablaDinamica=".(string)$cXML->DatosSolicitud[0]->cvefiscalia ;
	$fiscalia=$con->obtenerValor($consulta);
	$PHPWord = new PHPWord();
	$document = $PHPWord->loadTemplate($baseDir.'\\modulosEspeciales_SGJP\\formatos\\plantillaNotificacion.docx');	
	
	$dia=date("d",$fechaCreacion);
	$mes=date("m",$fechaCreacion);
	$anio=date("Y",$fechaCreacion);
	
	$fecha=$dia." ".strtolower(convertirNumeroLetra($dia,false,false))." de ".strtolower($arrMesLetra[($mes*1)-1])." de ".$anio." ".strtolower(convertirNumeroLetra($anio,false,false));
	
	
	$fecha=str_replace("veintiun","veintiuno",$fecha);
	$fecha=str_replace("treinta y un","treinta y uno",$fecha);
 
	
	$arrValores=array();
	$arrValores["Carpeta"]=(string)$cXML->DatosSolicitud[0]->carpetainvestigacion;
	$arrValores["fecha"]=$fecha;
	$arrValores["hora"]=date("H:i:s",$fechaCreacion);
	$arrValores["agente"]=utf8_decode((string)$cXML->DatosSolicitud[0]->mpsolicitante);
	$arrValores["fiscalia"]=$fiscalia;
	$arrValores["tipoAudiencia"]=$tAudiencia;
	
	$consulta="select u.claveFolioCarpetas,(select descripcion from _1_tablaDinamica where id__1_tablaDinamica= u.idReferencia) 
				as ubicacion  from 7006_carpetasAdministrativas c,_17_tablaDinamica u where carpetaAdministrativa='".$fSolicitud[2]."'
				and u.claveUnidad=c.unidadGestion";
	$fUbicacion=$con->obtenerPrimeraFila($consulta);
	
	
	$arrValores["noUnidadGestion"]=$fUbicacion[0];
	$arrValores["lugarPresentacion"]=$fUbicacion[1];
	
	
	$delitos="";
	foreach($cXML->Delitos[0] as $delito)
	{

		$d=(string)$delito->descdelito." - ".((string)$delito->descmodalidad).", ";
		if($delitos=="")
			$delitos=$d;
		else
			$delitos.=", ".$d;
	}
		
	$arrValores["delitos"]=utf8_decode($delitos);
	$imputado="";
	$victima="";
		

	
	foreach($cXML->Personas[0] as $p)
	{
		$iFigura=(string)$p->figurajuridica;
		$consulta="SELECT figuraEquivalente FROM _284_tablaDinamica WHERE id__284_tablaDinamica=".$iFigura;
		$figuraJuridica=$con->obtenerValor($consulta);
		if($figuraJuridica=="")
			$figuraJuridica=9;		
		
		
		$nombre=utf8_decode(((string)$p->nombre)." ".((string)$p->paterno)." ".((string)$p->materno));

		switch($figuraJuridica)
		{
			case 4:
				if($imputado=="")
					$imputado=$nombre;
				else
					$imputado.=", ".$nombre;
			break;
			case 2:
			case 1:
			case 6:
				if($victima=="")
					$victima=$nombre;
				else
					$victima.=", ".$nombre;
			break;
		}
		
				
	}
	
	foreach($cXML->PersonasJuridicas[0] as $p)
	{
		$iFigura=(string)$p->figurajuridica;
		if($iFigura==0)
			continue;
		$consulta="SELECT figuraEquivalente FROM _284_tablaDinamica WHERE id__284_tablaDinamica=".$iFigura;
		$figuraJuridica=$con->obtenerValor($consulta);
		if($figuraJuridica=="")
			$figuraJuridica=9;		
		
		
		$nombre=utf8_decode((string)$p->razonsocial);
		
		switch($figuraJuridica)
		{
			case 4:
				if($imputado=="")
					$imputado=$nombre;
				else
					$imputado.=", ".$nombre;
			break;
			case 2:
			case 1:
				if($victima=="")
					$victima=$nombre;
				else
					$victima.=", ".$nombre;
			break;
		}
	}
	
	$arrValores["carpetaJudicial"]=$fSolicitud[2];
	$arrValores["imputado"]=($imputado=="")?"[EL REFERIDO EN LA SOLICITUD]":$imputado;
	
	$arrValores["victima"]=($victima=="")?"[EL REFERIDO EN LA SOLICITUD]":$victima;
	

	foreach($arrValores as $llave=>$valor)
	{
		$document->setValue("[".$llave."]",utf8_decode($valor));	
	}
	$nombreAleatorio=generarNombreArchivoTemporal();
	$nomArchivo=$nombreAleatorio.".docx";
	$document->save($nomArchivo);
	
	$nombreFinal=str_replace(".docx",".pdf",$nomArchivo);
	generarDocumentoPDF($nomArchivo,false,false,true,$nombreFinal,"","./");
	
	return $nombreFinal;
}

function generarDocumentoAcuseLaVista($idFormulario,$idRegistro)
{
	global $con;
	global $baseDir;
	global $arrMesLetra;
	
	
	
	$consulta="SELECT solicitudXML,fechaCreacion,carpetaAdministrativa,tipoAudiencia FROM _46_tablaDinamica WHERE id__46_tablaDinamica=".$idRegistro;
	$fSolicitud=$con->obtenerPrimeraFila($consulta);
	
	$consulta="SELECT tipoAudiencia FROM _4_tablaDinamica WHERE id__4_tablaDinamica=".$fSolicitud[3];
	$tAudiencia=$con->obtenerValor($consulta);

	$xmlSolicitud=bD($fSolicitud[0]);
	if($xmlSolicitud=="")
		$xmlSolicitud=obtenerContenidoArchivoDatosPeticion($idFormulario,$idRegistro);
	if($xmlSolicitud=="")
	{
		$consulta="SELECT idRegistroSolicitud FROM 3011_solicitudRecibidasPGJ WHERE idFormulario=46 AND idRegistro=".$idRegistro;
		$idRegistroSolicitud=$con->obtenerValor($consulta);
		if($idRegistroSolicitud!="")
		{
			$xmlSolicitud=obtenerContenidoArchivoDatosPeticion(3011,$idRegistroSolicitud);

		}
	}
	$fechaCreacion=strtotime($fSolicitud[1]);
	if($xmlSolicitud=="")
	{
		return false;
	}
	
	
	$oXml=$xmlSolicitud;
	$cXML=simplexml_load_string($oXml);


	$consulta="SELECT fiscalia FROM _286_tablaDinamica WHERE id__286_tablaDinamica=".(string)$cXML->DatosSolicitud[0]->cvefiscalia ;

	$fiscalia=$con->obtenerValor($consulta);
	
	$PHPWord = new PHPWord();
	$document = $PHPWord->loadTemplate($baseDir.'\\modulosEspeciales_SGJP\\formatos\\plantillaNotificacionLaVista.docx');	
	
	$dia=date("d",$fechaCreacion);
	$mes=date("m",$fechaCreacion);
	$anio=date("Y",$fechaCreacion);
	
	$fecha=$dia." ".strtolower(convertirNumeroLetra($dia,false,false))." de ".strtolower($arrMesLetra[($mes*1)-1])." de ".$anio." ".strtolower(convertirNumeroLetra($anio,false,false));
	
	
	$fecha=str_replace("veintiun","veintiuno",$fecha);
	$fecha=str_replace("treinta y un","treinta y uno",$fecha);
 
	
	$arrValores=array();
	$arrValores["Carpeta"]=(string)$cXML->DatosSolicitud[0]->carpetainvestigacion;
	$arrValores["fecha"]=$fecha;
	$arrValores["hora"]=date("H:i:s",$fechaCreacion);
	$arrValores["agente"]=utf8_decode((string)$cXML->DatosSolicitud[0]->mpsolicitante);
	$arrValores["fiscalia"]=$fiscalia;
	$arrValores["tipoAudiencia"]=$tAudiencia;

	$consulta="select u.claveFolioCarpetas,(select descripcion from _1_tablaDinamica where id__1_tablaDinamica= u.idReferencia) 
				as ubicacion  from 7006_carpetasAdministrativas c,_17_tablaDinamica u where carpetaAdministrativa='".$fSolicitud[2]."'
				and u.claveUnidad=c.unidadGestion";
	$fUbicacion=$con->obtenerPrimeraFila($consulta);
	
	
	$arrValores["noUnidadGestion"]=$fUbicacion[0];
	$arrValores["lugarPresentacion"]=$fUbicacion[1];
	
	
	$delitos="";
	foreach($cXML->Delitos[0] as $delito)
	{

		$d=(string)$delito->descdelito." - ".((string)$delito->descmodalidad).", ";
		if($delitos=="")
			$delitos=$d;
		else
			$delitos.=", ".$d;
	}
		
	$arrValores["delitos"]=utf8_decode($delitos);
	$imputado="";
	$victima="";
		


	foreach($cXML->Personas[0] as $p)
	{
		if(!isset($p->figurajuridica))
			continue;
		$iFigura=(string)$p->figurajuridica;
		$consulta="SELECT figuraEquivalente FROM _284_tablaDinamica WHERE id__284_tablaDinamica=".$iFigura;
		$figuraJuridica=$con->obtenerValor($consulta);
		if($figuraJuridica=="")
			$figuraJuridica=9;		
		
		
		$nombre=utf8_decode(((string)$p->nombre)." ".((string)$p->paterno)." ".((string)$p->materno));

		switch($figuraJuridica)
		{
			case 4:
				if($imputado=="")
					$imputado=$nombre;
				else
					$imputado.=", ".$nombre;
			break;
			case 2:
			case 1:
			case 6:
				if($victima=="")
					$victima=$nombre;
				else
					$victima.=", ".$nombre;
			break;
		}
		
				
	}
	
	foreach($cXML->PersonasJuridicas[0] as $p)
	{
		$iFigura=(string)$p->figurajuridica;
		if($iFigura==0)
			continue;
		$consulta="SELECT figuraEquivalente FROM _284_tablaDinamica WHERE id__284_tablaDinamica=".$iFigura;
		$figuraJuridica=$con->obtenerValor($consulta);
		if($figuraJuridica=="")
			$figuraJuridica=9;		
		
		
		$nombre=utf8_decode((string)$p->razonsocial);
		
		switch($figuraJuridica)
		{
			case 4:
				if($imputado=="")
					$imputado=$nombre;
				else
					$imputado.=", ".$nombre;
			break;
			case 2:
			case 1:
				if($victima=="")
					$victima=$nombre;
				else
					$victima.=", ".$nombre;
			break;
		}
	}
	
	
	$arrValores["imputado"]=($imputado=="")?"[EL REFERIDO EN LA SOLICITUD]":$imputado;
	
	$arrValores["victima"]=($victima=="")?"[EL REFERIDO EN LA SOLICITUD]":$victima;
	
	$arrValores["horas"]="08:00 ocho";
	$arrValores["carpetaJudicial"]=$fSolicitud[2];
	$fechaReferenciaInicio=strtotime(date("Y-m-d",$fechaCreacion)." 04:30");
	$fechaReferenciaFin=strtotime(date("Y-m-d",$fechaCreacion)." 08:00");
	
	if(($fechaCreacion>=$fechaReferenciaInicio)&&($fechaCreacion<=$fechaReferenciaFin))
	{
		$arrValores["horas"]="11 once";
	}

	foreach($arrValores as $llave=>$valor)
	{
		$document->setValue("[".$llave."]",utf8_decode($valor));	
	}
	$nombreAleatorio=generarNombreArchivoTemporal();
	$nomArchivo=$nombreAleatorio.".docx";
	$document->save($nomArchivo);
	
	$nombreFinal=str_replace(".docx",".pdf",$nomArchivo);
	generarDocumentoPDF($nomArchivo,false,false,true,$nombreFinal,"","./");
	
	return $nombreFinal;
}

function generarDocumentoAcuseSullivan($idFormulario,$idRegistro)
{
	global $con;
	global $baseDir;
	global $arrMesLetra;
	
	
	
	$consulta="SELECT solicitudXML,fechaCreacion,carpetaAdministrativa,tipoAudiencia FROM _46_tablaDinamica WHERE id__46_tablaDinamica=".$idRegistro;
	$fSolicitud=$con->obtenerPrimeraFila($consulta);
	
	$consulta="SELECT tipoAudiencia FROM _4_tablaDinamica WHERE id__4_tablaDinamica=".$fSolicitud[3];
	$tAudiencia=$con->obtenerValor($consulta);
	
	$xmlSolicitud=bD($fSolicitud[0]);
	if($xmlSolicitud=="")
		$xmlSolicitud=obtenerContenidoArchivoDatosPeticion($idFormulario,$idRegistro);
	$fechaCreacion=strtotime($fSolicitud[1]);
	if($xmlSolicitud=="")
	{
		return false;
	}
	
	
	$oXml=$xmlSolicitud;
	$cXML=simplexml_load_string($oXml);
	

	$consulta="SELECT fiscalia FROM _286_tablaDinamica WHERE id__286_tablaDinamica=".(string)$cXML->DatosSolicitud[0]->cvefiscalia ;
	$fiscalia=$con->obtenerValor($consulta);
	$PHPWord = new PHPWord();
	$document = $PHPWord->loadTemplate($baseDir.'\\modulosEspeciales_SGJP\\formatos\\plantillaNotificacionSullivan.docx');	
	
	$dia=date("d",$fechaCreacion);
	$mes=date("m",$fechaCreacion);
	$anio=date("Y",$fechaCreacion);
	
	$fecha=$dia." ".strtolower(convertirNumeroLetra($dia,false,false))." de ".strtolower($arrMesLetra[($mes*1)-1])." de ".$anio." ".strtolower(convertirNumeroLetra($anio,false,false));
	
	
	$fecha=str_replace("veintiun","veintiuno",$fecha);
	$fecha=str_replace("treinta y un","treinta y uno",$fecha);
 
	
	$arrValores=array();
	$arrValores["Carpeta"]=(string)$cXML->DatosSolicitud[0]->carpetainvestigacion;
	$arrValores["fecha"]=$fecha;
	$arrValores["hora"]=date("H:i:s",$fechaCreacion);
	$arrValores["agente"]=utf8_decode((string)$cXML->DatosSolicitud[0]->mpsolicitante);
	$arrValores["fiscalia"]=$fiscalia;
	$arrValores["tipoAudiencia"]=$tAudiencia;
	
	$consulta="select u.claveFolioCarpetas,(select descripcion from _1_tablaDinamica where id__1_tablaDinamica= u.idReferencia) 
				as ubicacion  from 7006_carpetasAdministrativas c,_17_tablaDinamica u where carpetaAdministrativa='".$fSolicitud[2]."'
				and u.claveUnidad=c.unidadGestion";
	$fUbicacion=$con->obtenerPrimeraFila($consulta);
	
	
	$arrValores["noUnidadGestion"]=$fUbicacion[0];
	$arrValores["lugarPresentacion"]=$fUbicacion[1];
	
	
	$delitos="";
	foreach($cXML->Delitos[0] as $delito)
	{

		$d=(string)$delito->descdelito." - ".((string)$delito->descmodalidad).", ";
		if($delitos=="")
			$delitos=$d;
		else
			$delitos.=", ".$d;
	}
		
	$arrValores["delitos"]=utf8_decode($delitos);
	$imputado="";
	$victima="";
		

	
	foreach($cXML->Personas[0] as $p)
	{
		$iFigura=(string)$p->figurajuridica;
		$consulta="SELECT figuraEquivalente FROM _284_tablaDinamica WHERE id__284_tablaDinamica=".$iFigura;
		$figuraJuridica=$con->obtenerValor($consulta);
		if($figuraJuridica=="")
			$figuraJuridica=9;		
		
		
		$nombre=utf8_decode(((string)$p->nombre)." ".((string)$p->paterno)." ".((string)$p->materno));

		switch($figuraJuridica)
		{
			case 4:
				if($imputado=="")
					$imputado=$nombre;
				else
					$imputado.=", ".$nombre;
			break;
			case 2:
			case 1:
			case 6:
				if($victima=="")
					$victima=$nombre;
				else
					$victima.=", ".$nombre;
			break;
		}
		
				
	}
	
	foreach($cXML->PersonasJuridicas[0] as $p)
	{
		$iFigura=(string)$p->figurajuridica;
		if($iFigura==0)
			continue;
		$consulta="SELECT figuraEquivalente FROM _284_tablaDinamica WHERE id__284_tablaDinamica=".$iFigura;
		$figuraJuridica=$con->obtenerValor($consulta);
		if($figuraJuridica=="")
			$figuraJuridica=9;		
		
		
		$nombre=utf8_decode((string)$p->razonsocial);
		
		switch($figuraJuridica)
		{
			case 4:
				if($imputado=="")
					$imputado=$nombre;
				else
					$imputado.=", ".$nombre;
			break;
			case 2:
			case 1:
				if($victima=="")
					$victima=$nombre;
				else
					$victima.=", ".$nombre;
			break;
		}
	}
	
	
	$arrValores["imputado"]=($imputado=="")?"[EL REFERIDO EN LA SOLICITUD]":$imputado;
	
	$arrValores["victima"]=($victima=="")?"[EL REFERIDO EN LA SOLICITUD]":$victima;
	
	$arrValores["horas"]="07:00 siete";
	$arrValores["carpetaJudicial"]=$fSolicitud[2];
	$fechaReferenciaInicio=strtotime(date("Y-m-d",$fechaCreacion)." 04:30");
	$fechaReferenciaFin=strtotime(date("Y-m-d",$fechaCreacion)." 08:00");
	
	if(($fechaCreacion>=$fechaReferenciaInicio)&&($fechaCreacion<=$fechaReferenciaFin))
	{
		//$arrValores["horas"]="11 once";
	}

	foreach($arrValores as $llave=>$valor)
	{
		$document->setValue("[".$llave."]",utf8_decode($valor));	
	}
	

	$nombreAleatorio=generarNombreArchivoTemporal();
	$nomArchivo=$nombreAleatorio.".docx";
	$document->save($nomArchivo);
	
	$nombreFinal=str_replace(".docx",".pdf",$nomArchivo);
	generarDocumentoPDF($nomArchivo,false,false,true,$nombreFinal,"","./");
	
	return $nombreFinal;
}

function generarDocumentoAcuseOrdinario($idFormulario,$idRegistro)
{
	global $con;
	global $baseDir;
	global $arrMesLetra;
	
	
	
	$consulta="SELECT solicitudXML,fechaCreacion,carpetaAdministrativa,folioCarpetaInvestigacion,tipoAudiencia FROM _46_tablaDinamica WHERE id__46_tablaDinamica=".$idRegistro;
	$fSolicitud=$con->obtenerPrimeraFila($consulta);
	$consulta="SELECT tipoAudiencia FROM _4_tablaDinamica WHERE id__4_tablaDinamica=".$fSolicitud[4];
	$tAudiencia=$con->obtenerValor($consulta);
	
	$fechaCreacion=strtotime($fSolicitud[1]);
	

	
	$PHPWord = new PHPWord();
	$document = $PHPWord->loadTemplate($baseDir.'\\modulosEspeciales_SGJP\\formatos\\plantillaNotificacionOrdinario.docx');	
	
	$dia=date("d",$fechaCreacion);
	$mes=date("m",$fechaCreacion);
	$anio=date("Y",$fechaCreacion);
	
	$fecha=$dia." ".strtolower(convertirNumeroLetra($dia,false,false))." de ".strtolower($arrMesLetra[($mes*1)-1])." de ".$anio." ".strtolower(convertirNumeroLetra($anio,false,false));
	
	
	$fecha=str_replace("veintiun","veintiuno",$fecha);
	$fecha=str_replace("treinta y un","treinta y uno",$fecha);
 
	
	$arrValores=array();
	$arrValores["Carpeta"]=$fSolicitud[3];
	$arrValores["fecha"]=$fecha;
	$arrValores["hora"]=date("H:i:s",$fechaCreacion);
	
	
	$arrValores["carpetaJudicial"]=$fSolicitud[2];
	$arrValores["tipoAudiencia"]=$tAudiencia;
	foreach($arrValores as $llave=>$valor)
	{
		$document->setValue("[".$llave."]",utf8_decode($valor));	
	}
	
	$nombreAleatorio=generarNombreArchivoTemporal();
	$nomArchivo=$nombreAleatorio.".docx";
	$document->save($nomArchivo);
	
	$nombreFinal=str_replace(".docx",".pdf",$nomArchivo);
	generarDocumentoPDF($nomArchivo,false,false,true,$nombreFinal,"","./");
	
	return $nombreFinal;
}

function registrarIncompetenciaUnidadGestion($idFormulario,$idRegistro)
{
	global $con;
	$consulta="select carpetaAdministrativa,txtMotivoRemision,motivoIncompetencia,materiaDestino,fiscalia FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$fDatosRemision=$con->obtenerPrimeraFila($consulta);
	
	$cAdministrativa=$fDatosRemision[0];
	registrarCambioSituacionCarpeta($cAdministrativa,8,$idFormulario,$idRegistro,-1);
	
	$consulta="SELECT id__46_tablaDinamica FROM _46_tablaDinamica  WHERE carpetaAdministrativa='".$cAdministrativa."'";
	$idRegistroSolicitud=$con->obtenerValor($consulta);
	if($idRegistro!=-1)
	{
		
		$oParamAdicionales["materiaDestino"]=$fDatosRemision[3];
		$oParamAdicionales["delitoGrave"]=$fDatosRemision[2]==1?0:1;
		$oParamAdicionales["fiscalia"]=$fDatosRemision[4];
		

		$consulta="SELECT COUNT(*) FROM _46_tablaDinamica s,7006_carpetasAdministrativas c 
				WHERE tipoAudiencia=91 AND carpetaRemitida='".$cAdministrativa."'
				and s.carpetaAdministrativa=c.carpetaAdministrativa and c.situacion<>19";
		$nReg=$con->obtenerValor($consulta);
		if($nReg==0)
			$idRegistroClon=clonarSolicitudAudiencia($idRegistroSolicitud,$idFormulario,$idRegistro,$fDatosRemision[1],$oParamAdicionales);
	}
	return true;
	
}

function registrarRemisionUnidadGestion($idFormulario,$idRegistro) //UGA 4
{
	global $con;
	$idEtapaCarpeta=10;
	$consulta="select carpetaAdministrativa,comentariosAdicionales,calsificacion FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	if($idFormulario==382)
	{
		$idEtapaCarpeta=14;
		$consulta="select carpetaAdministrativa,motivoRemision, 0 as calsificacion FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	}
	
	$fDatosRemision=$con->obtenerPrimeraFila($consulta);

	$cAdministrativa=$fDatosRemision[0];
	registrarCambioSituacionCarpeta($cAdministrativa,$idEtapaCarpeta,$idFormulario,$idRegistro,-1);	
	
	$arrParametros["idEtapa"]=2.5;
	$arrParametros["tipoAudiencia"]=102;
	$arrParametros["copiarDocumentosSolicitudOriginal"]=false;
	$arrParametros["delitoGrave"]=$fDatosRemision[2];
	
	if($idFormulario==382)
	{
		$arrParametros["tipoAudiencia"]=114;
		unset($arrParametros["delitoGrave"]);
	}
	
	$consulta="SELECT id__46_tablaDinamica FROM _46_tablaDinamica WHERE carpetaAdministrativa='".$cAdministrativa."'";
	
	$idRegistroSolicitud=$con->obtenerValor($consulta);
	if($idRegistro!=-1)
	{
	
		$consulta="SELECT COUNT(*) FROM _46_tablaDinamica s,7006_carpetasAdministrativas c 
				WHERE tipoAudiencia=".$arrParametros["tipoAudiencia"]." AND carpetaRemitida='".$cAdministrativa."'
				and s.carpetaAdministrativa=c.carpetaAdministrativa and c.situacion<>19";
		$nReg=$con->obtenerValor($consulta);
		
		if($nReg==0)
		{
			$idRegistroClon=clonarSolicitudAudiencia($idRegistroSolicitud,$idFormulario,$idRegistro,$fDatosRemision[1],$arrParametros);
		}
	}
	return true;	
}

function generarAsignacionUnidadGestionCarpetaJudicial($idFormulario,$idRegistro)
{
	global $con;
	$registrarCarpetaJudicial=true;
	$consulta="SELECT tipoAudiencia,carpetaAdministrativa,ctrlSolicitud,idSolicitud,folioCarpetaInvestigacion,materiaDestino,corregidoAlgoritmo FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$fDatosRegistro=$con->obtenerPrimeraFila($consulta);
	$tipoAudiencia=$fDatosRegistro[0];
	if(($fDatosRegistro[2]!="")&&($fDatosRegistro[1]=="")&&($tipoAudiencia!=91)&&($fDatosRegistro[6]!=488))
	{
		$llaveCarpeta=generarLlaveCarpetaInvestigacion($fDatosRegistro[4]);
		
		$esMateriaAdolescentes=$fDatosRegistro[5]==2;
			
		$cAdministrativa="";		
		
		switch($tipoAudiencia)
		{
			case 9:
			case 56:
			case 32:
			case 97:
			case 69:
			case 35:
				if($llaveCarpeta!="")
				{
					$consulta="SELECT carpetaAdministrativa FROM 7006_carpetasAdministrativas WHERE llaveCarpetaInvestigacion='".
						cv($llaveCarpeta)."' and tipoCarpetaAdministrativa=1 and unidadGestion ='012' order by fechaCreacion DESC";

					$cAdministrativa=$con->obtenerValor($consulta);
					if($cAdministrativa=="")
					{
						$llaveCarpeta="";		
					}
				}
				
			break;
		}
					
		if(($llaveCarpeta!="")&&($cAdministrativa==""))
		{
			if(!$esMateriaAdolescentes)
			{
				
				$consulta="SELECT c.carpetaAdministrativa FROM 7006_carpetasAdministrativas c,_46_tablaDinamica s WHERE 
							s.carpetaAdministrativa=c.carpetaAdministrativa and llaveCarpetaInvestigacion='".
							cv($llaveCarpeta)."' and s.tipoAudiencia in (1,102,114,136,26,91,52) and tipoCarpetaAdministrativa=1 
							and unidadGestion not in ('012','301','302') order by c.fechaCreacion DESC";
			}
			else
			{
				$consulta="SELECT c.carpetaAdministrativa FROM 7006_carpetasAdministrativas c,_46_tablaDinamica s WHERE 
							s.carpetaAdministrativa=c.carpetaAdministrativa and llaveCarpetaInvestigacion='".
							cv($llaveCarpeta)."' and s.tipoAudiencia in (1,102,114,136,26,91,52) and tipoCarpetaAdministrativa=1 
							and unidadGestion in ('301') order by c.fechaCreacion DESC";
			}
			$cAdministrativa=$con->obtenerValor($consulta);
			
		}
		
		if($cAdministrativa!="")
		{
			$consulta="SELECT unidadGestion FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$cAdministrativa."'";
			$unidadGestion=$con->obtenerValor($consulta);
			
			$consulta="SELECT id__17_tablaDinamica,idReferencia FROM _17_tablaDinamica WHERE claveUnidad='".$unidadGestion."'";
			$fRegistroUnidad=$con->obtenerPrimeraFila($consulta);
			$consulta="DELETE FROM 7000_eventosAudiencia WHERE idFormulario=".$idFormulario." AND idRegistroSolicitud=".$idRegistro;
			$con->ejecutarConsulta($consulta);
			$consulta="INSERT INTO 7000_eventosAudiencia(situacion,fechaAsignacion,idEdificio,idCentroGestion,
					idFormulario,idRegistroSolicitud,idReferencia,idEtapaProcesal,tipoAudiencia)
					values(0,'".date("Y-m-d H:i:s")."',".$fRegistroUnidad[1].",".$fRegistroUnidad[0].",46,".
					$idRegistro.",-1,1,".$tipoAudiencia.")";
			$con->ejecutarConsulta($consulta);
			$consulta="UPDATE _46_tablaDinamica SET carpetaAdministrativa='".$cAdministrativa."' WHERE id__46_tablaDinamica=".$idRegistro;
			$con->ejecutarConsulta($consulta);
			$registrarCarpetaJudicial=false;
			$query="SELECT idDocumento FROM 9074_documentosRegistrosProceso WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro;
			$rDocumentos=$con->obtenerFilas($query);
			while($fDocumento=mysql_fetch_row($rDocumentos))
			{
				registrarDocumentoCarpetaAdministrativa($cAdministrativa,$fDocumento[0],$idFormulario,$idRegistro);	
			}
		}
		
		
		
		
	}
	
	
	
	if($registrarCarpetaJudicial)
	{
		
		
		obtenerFechaAudienciaSolicitudInicialV3($idFormulario,$idRegistro,-1,$tipoAudiencia,1);
	
		if($fDatosRegistro[1]=="")
		{
			
			$consulta="SELECT idCentroGestion FROM 7000_eventosAudiencia WHERE idFormulario=".$idFormulario." AND  idRegistroSolicitud=".$idRegistro;
			$idUnidadGestion=$con->obtenerValor($consulta);
		
			if($idUnidadGestion!="")
			{	
				generarFolioCarpetaAdministrativa($idFormulario,$idRegistro,$idUnidadGestion);
			}
		}
	}
	return true;
}


function clonarSolicitudAudiencia($idRegistro,$iFormulario,$iRegistro,$motivoRemision,$arrParametros=NULL)
{
	global $con;
	
	$tipoAudiencia=91;
	if(($arrParametros!=NULL)&&(isset($arrParametros["tipoAudiencia"])))
	{
		$tipoAudiencia=$arrParametros["tipoAudiencia"];
	}
	
	$idEtapa=3;
	if(($arrParametros!=NULL)&&(isset($arrParametros["idEtapa"])))
	{
		$idEtapa=$arrParametros["idEtapa"];
	}
	
	$copiarDocumentosSolicitudOriginal=true;
	if(($arrParametros!=NULL)&&(isset($arrParametros["copiarDocumentosSolicitudOriginal"])))
	{
		$copiarDocumentosSolicitudOriginal=$arrParametros["copiarDocumentosSolicitudOriginal"];
	}
	
	$fechaRemision=date("Y-m-d H:i:s");
	$arrValores=array();
	$idActividad=generarIDActividad(46,$idRegistro);
	$consulta=" select folioCarpetaInvestigacion,tipoProgramacionAudiencia,tipoAudiencia,requiereResguardo,requiereTelePresencia,
				carpetaAdministrativa,idActividad, requiereMesaEvidencia, requiereTestigoProtegido,  delitoGrave, ctrlSolicitud, idSolicitud,
				cveSolicitud, solicitudXML, noFojas, textoFojas, fechaFenece,declaratoria,materiaDestino from _46_tablaDinamica where id__46_tablaDinamica=".$idRegistro;

	$arrValores=$con->obtenerPrimeraFilaAsoc($consulta);

	$idActividadBase=$arrValores["idActividad"];
	$arrValores["idActividad"]=$idActividad;
	$arrValores["tipoAudiencia"]=$tipoAudiencia;
	$arrValores["iFormulario"]=$iFormulario;
	$arrValores["iReferencia"]=$iRegistro;
	$cCarpetaBase=$arrValores["carpetaAdministrativa"];
	$arrValores["carpetaAdministrativa"]="";
	$arrValores["carpetaRemitida"]=$cCarpetaBase;
	$arrValores["materiaDestino"]=$arrValores["materiaDestino"];
	if($iFormulario==307)
	{
		$idEtapa=2.5;		
	}

	if(($arrParametros!=NULL)&&(isset($arrParametros["delitoGrave"])))
	{
		$arrValores["delitoGrave"]=$arrParametros["delitoGrave"];
	}
	
	if(($arrParametros!=NULL)&&(isset($arrParametros["materiaDestino"])))
	{
		$arrValores["materiaDestino"]=$arrParametros["materiaDestino"];
	}

	$arrDocumentosReferencia=array();						
	
	if($copiarDocumentosSolicitudOriginal)
	{
		$consulta="SELECT idDocumento FROM 9074_documentosRegistrosProceso WHERE idFormulario=46 
					AND idRegistro=".$idRegistro." AND tipoDocumento in(2,1)";
	
		$res=$con->obtenerFilas($consulta);
		while($fDocumento=mysql_fetch_row($res))
		{
			array_push($arrDocumentosReferencia,$fDocumento[0]);	
		}	
	}
	
	
	
	$consulta="SELECT idDocumento FROM 9074_documentosRegistrosProceso WHERE idFormulario=".$iFormulario." 
				AND idRegistro=".$iRegistro." AND tipoDocumento in(2,1)";

	$res=$con->obtenerFilas($consulta);
	while($fDocumento=mysql_fetch_row($res))
	{
		array_push($arrDocumentosReferencia,$fDocumento[0]);	
	}	
	
	$arrValores["fechaRecepcion"]=date("Y-m-d",strtotime($fechaRemision));
	$arrValores["horaRecepcion"]=date("H:i:s",strtotime($fechaRemision));
	
	$actor=299;
	$idRegistroSolicitud=crearInstanciaRegistroFormulario(46,-1,1,$arrValores,$arrDocumentosReferencia,-1,$actor,$motivoRemision);
	@registrarDatosPeticionRefiere(46,$idRegistroSolicitud,46,$idRegistro);
	/*$consulta="update _46_tablaDinamica set carpetaRemitida='".$cCarpetaBase."' where id__46_tablaDinamica=".$idRegistroSolicitud;
	$con->ejecutarConsulta($consulta);*/	
	
	$idRegistroFiscalia="-1";
	$arrValores=array();
	if(($arrParametros!=NULL)&&(isset($arrParametros["fiscalia"])))
	{
		$consulta="SELECT claveCoorTerMP,nombre,apPaterno,apMaterno,curp,claveFiscalia,claveAgencia,claveUnidad,id__100_tablaDinamica 
				FROM _100_tablaDinamica WHERE idReferencia=".$idRegistro." and claveFiscalia=".$arrParametros["fiscalia"];

		$arrValores=$con->obtenerPrimeraFilaAsoc($consulta);
		if(!$arrValores)
		{
			$arrValores["claveCoorTerMP"]="";
			$arrValores["nombre"]="";
			$arrValores["apPaterno"]="";
			$arrValores["apMaterno"]="";
			$arrValores["curp"]="";
			
			$arrValores["claveFiscalia"]=$arrParametros["fiscalia"];
			$arrValores["claveAgencia"]="";
			$arrValores["claveUnidad"]="";

			$idRegistroFiscalia=0;
			
		}
		else	
			$idRegistroFiscalia=$arrValores["id__100_tablaDinamica"];
			
	}
	else
	{
		$consulta="SELECT claveCoorTerMP,nombre,apPaterno,apMaterno,curp,claveFiscalia,claveAgencia,claveUnidad,id__100_tablaDinamica 
					FROM _100_tablaDinamica WHERE idReferencia=".$idRegistro;
		$arrValores=$con->obtenerPrimeraFilaAsoc($consulta);
		$idRegistroFiscalia=$arrValores["id__100_tablaDinamica"];
	}
	
	if(isset($arrValores["id__100_tablaDinamica"]))
	{
		unset($arrValores["id__100_tablaDinamica"]);
	}
	
	
	
	if(($arrValores) &&($idRegistroFiscalia!=-1)) //Datos de fiscalia
	{
		$arrDocumentosReferencia=array();
		$idFiscalia=crearInstanciaRegistroFormulario(100,$idRegistroSolicitud,1,$arrValores,$arrDocumentosReferencia,-1,299);

		if($idRegistroFiscalia>0)
		{
			$consulta="SELECT id__100_tablaDinamica FROM _100_tablaDinamica WHERE idReferencia=".$idRegistro;
			$idFiscaliaBase=$con->obtenerValor($consulta);
					
			$consulta="INSERT INTO _100_gridCorreosFiscal(idReferencia,correoElectronico)
						SELECT '".$idFiscalia."' AS idReferencia,correoElectronico FROM _100_gridCorreosFiscal WHERE idReferencia=".$idFiscaliaBase;
			
			$con->ejecutarConsulta($consulta);	
		}

	}
	
	
	$consulta="select numeroExpediente,nombre,apPaterno,apMaterno,fechaRecepcion,horaRecepcion,juzgado 
				from _222_tablaDinamica where idReferencia=".$idRegistro;
				
	$arrValores=$con->obtenerPrimeraFilaAsoc($consulta);
	if($arrValores)	//Datos de incompetencia
	{
		$arrDocumentosReferencia=array();
		crearInstanciaRegistroFormulario(222,$idRegistroSolicitud,1,$arrValores,$arrDocumentosReferencia,-1,299);
	}	
	
	
	$consulta="SELECT id__61_tablaDinamica as idRegistro,-1 AS tituloDelito,-1 AS capituloDelito,denominacionDelito,calificativo,gradoRealizacion,'".$idActividad."' as idActividad,
				modalidadDelito FROM _61_tablaDinamica WHERE idActividad=".$idActividadBase;
	$res=$con->obtenerFilas($consulta);
	while($fDelito=mysql_fetch_assoc($res))
	{	
	
		$idRegistroDelito=$fDelito["idRegistro"];
		unset($fDelito["idRegistro"]);
		$arrDocumentosReferencia=array();
		$arrValores=$fDelito;
		$idDelito=crearInstanciaRegistroFormulario(61,-1,1,$arrValores,$arrDocumentosReferencia,-1,299);
		
		$consulta="INSERT INTO _61_chkDelitosImputado(idPadre,idOpcion)
					SELECT '".$idDelito."' AS idPadre,idOpcion FROM _61_chkDelitosImputado WHERE idPadre=".$idRegistroDelito;
		$con->ejecutarConsulta($consulta);
		
	}


	$consulta="SELECT id__47_tablaDinamica,tipoPersona,apellidoPaterno,apellidoMaterno,genero,edad,curp,fechaNacimiento,estadoCivil,tipoIdentificacion,
				folioIdentificacion,otraNacionalidad,nombre,rfcEmpresa,esMexicano,idActividad,figuraJuridica,requiereDefensoria,imputadoDetenido, 
				lugarReclusorio,reclusorioDetencion,cedulaProfesional,tipoDefensor,tipoFiguraPGJ,nombreHospital,otroLugarRetencion 
				FROM _47_tablaDinamica WHERE idActividad=".$idActividadBase;

	
	$resParticipante=$con->obtenerFilas($consulta);

	while($fParticipante=mysql_fetch_assoc($resParticipante))
	{
		$idPersona=$fParticipante["id__47_tablaDinamica"];
		unset($fParticipante["id__47_tablaDinamica"]);
		$arrValores=$fParticipante;
		$arrValores["idActividad"]=$idActividad;
		$arrDocumentosReferencia=array();
		

		$idPersonaRegistrada=crearInstanciaRegistroFormulario(47,-1,1,$arrValores,$arrDocumentosReferencia,-1,299);
		
		
		
		$consulta="INSERT INTO 7005_relacionFigurasJuridicasSolicitud(idActividad,idParticipante,idFiguraJuridica)
					SELECT ".$idActividad." as idActividad,".$idPersonaRegistrada." as idParticipante,idFiguraJuridica FROM 7005_relacionFigurasJuridicasSolicitud WHERE idActividad=".$idActividadBase.
					" AND idParticipante=".$idPersona;
		
		$con->ejecutarConsulta($consulta);
			
		$consulta="SELECT id__48_tablaDinamica as idDomicilio,entreCalle,yCalle,otrasReferencias,entidadFederativa,municipio,localidad,codigoPostal,calle,noExt,noInterior,colonia 
					FROM _48_tablaDinamica WHERE idReferencia=".$idPersona;
		$arrValores=$con->obtenerPrimeraFilaAsoc($consulta);
		if($arrValores)
		{
			$idRegistroDomicilio=$arrValores["idDomicilio"];
			unset($arrValores["idDomicilio"]);
			
			
			
			$idDomicilio=crearInstanciaRegistroFormulario(48,$idPersonaRegistrada,1,$arrValores,$arrDocumentosReferencia,-1,299);
			
			
			$consulta="INSERT INTO _48_correosElectronico(idReferencia,correo)
						SELECT '".$idDomicilio."',correo FROM _48_correosElectronico WHERE idReferencia=".$idRegistroDomicilio;
			$con->ejecutarConsulta($consulta);
			
			$consulta="INSERT INTO _48_telefonos(idReferencia,tipoTelefono,lada,numero)
					SELECT '".$idDomicilio."',tipoTelefono,lada,numero FROM _48_telefonos WHERE idReferencia=".$idRegistroDomicilio;
			$con->ejecutarConsulta($consulta);
		}
		
		
		$consulta="SELECT nivelEscolaridad,tipoOcupacion,otraEscolaridad,otraOcupacion,religion,otraReligion,lgbttti,grupoEtnico,
				otroGrupoEtnico,requiereTraductor,requiereInterprete,capacidadesDiferente,idiomaTraductor,tipoInterprete,capacidaDiferente,
				poblacion,otraPoblacion,perteneceGrupoEtnico,entiendeIdiomaEspanol,otroIdiomaTraductor, descripcionDiscapacidad,
				sabeLeerEscribir, poblacionCallejera, lengua FROM _49_tablaDinamica WHERE idReferencia=".$idPersona;
		$arrValores=$con->obtenerPrimeraFilaAsoc($consulta);
		if($arrValores)
		{
		
			$idDatosComplementarios=crearInstanciaRegistroFormulario(49,$idPersonaRegistrada,1,$arrValores,$arrDocumentosReferencia,-1,299);
		
		}
		$consulta="SELECT id__64_tablaDinamica FROM _64_tablaDinamica WHERE idReferencia=".$idPersona;
		$idRegistroMedioNotificacion=$con->obtenerValor($consulta);
		
		if($idRegistroMedioNotificacion!="")
		{
			$arrValores=array();
			
			$idMediosNotificacion=crearInstanciaRegistroFormulario(64,$idPersonaRegistrada,1,$arrValores,$arrDocumentosReferencia,-1,299);
			
			$consulta="INSERT INTO _64_tipoMedioNotificacion(idPadre,idOpcion)
						SELECT '".$idMediosNotificacion."',idOpcion FROM _64_tipoMedioNotificacion WHERE idPadre=".$idRegistroMedioNotificacion;
			$con->ejecutarConsulta($consulta);
		
			
		}
		$consulta="UPDATE _61_chkDelitosImputado SET idOpcion=".$idPersonaRegistrada." WHERE idOpcion=".$idPersona;
		$con->ejecutarConsulta($consulta);
	
	}
	
	
	cambiarEtapaFormulario(46,$idRegistroSolicitud,$idEtapa,$motivoRemision,-1,"NULL","NULL",$actor);
	$consulta="select * from _46_tablaDinamica where id__46_tablaDinamica=".$idRegistroSolicitud;
	$fRegistroClon=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$consulta="SELECT u.id__17_tablaDinamica FROM 7006_carpetasAdministrativas c,_17_tablaDinamica u WHERE carpetaAdministrativa='".$cCarpetaBase."' 
			AND c.unidadGestion=u.claveUnidad";
	$idUnidadRemite=$con->obtenerValor($consulta);
	
	$consulta="SELECT u.id__17_tablaDinamica FROM 7006_carpetasAdministrativas c,_17_tablaDinamica u WHERE carpetaAdministrativa='".$fRegistroClon["carpetaAdministrativa"]."' 
			AND c.unidadGestion=u.claveUnidad";
	$idUnidadReceptora=$con->obtenerValor($consulta);
	
	$consulta="UPDATE 7006_carpetasAdministrativas SET carpetaAdministrativaBase='".$cCarpetaBase."' WHERE carpetaAdministrativa='".$fRegistroClon["carpetaAdministrativa"]."'";
	$con->ejecutarConsulta($consulta);
	
	
	switch($iFormulario)
	{
		case "307":
			$arrValores=array();
			$arrValores["carpetaRemitida"]=$cCarpetaBase;
			$arrValores["carpetaAsignada"]=$fRegistroClon["carpetaAdministrativa"];
			$arrValores["fechaRemision"]=$fechaRemision;
			$arrValores["fechaAsignacion"]=$fRegistroClon["fechaCreacion"];
			$arrValores["idUnidadRemite"]=$idUnidadRemite;
			$arrValores["idUnidadReceptora"]=$idUnidadReceptora;
			$arrDocumentosReferencia=array();			
			$idRegistroSolicitudFormato=crearInstanciaRegistroFormulario(315,$iRegistro,1,$arrValores,$arrDocumentosReferencia,-1,647);
		break;
		case "329":
			$arrValores=array();
			$arrValores["carpetaRemitida"]=$cCarpetaBase;
			$arrValores["carpetaAsignada"]=$fRegistroClon["carpetaAdministrativa"];
			$arrValores["fechaRemision"]=$fechaRemision;
			$arrValores["idUnidadRemite"]=$idUnidadRemite;
			$arrValores["idUnidadReceptora"]=$idUnidadReceptora;
			$arrDocumentosReferencia=array();			
			$idRegistroSolicitudFormato=crearInstanciaRegistroFormulario(330,$iRegistro,1,$arrValores,$arrDocumentosReferencia,-1,647);
		break;
		case "382":
			$arrValores=array();
			$arrValores["carpetaRemitida"]=$cCarpetaBase;
			$arrValores["carpetaAsignada"]=$fRegistroClon["carpetaAdministrativa"];
			$arrValores["fechaRemision"]=$fechaRemision;
			$arrValores["idUnidadRemite"]=$idUnidadRemite;
			$arrValores["idUnidadReceptora"]=$idUnidadReceptora;
			$arrDocumentosReferencia=array();			
			$idRegistroSolicitudFormato=crearInstanciaRegistroFormulario(383,$iRegistro,1,$arrValores,$arrDocumentosReferencia,-1,647);
		break;
		
	}
}

function asignarSalaAtencionApelacion($idFormulario,$idRegistro)
{
	global $con;	
	$query="SELECT carpetaAdministrativa FROM _310_tablaDinamica WHERE id__310_tablaDinamica=".$idRegistro;
	$cAdministrativaBase=$con->obtenerValor($query);
	
	
	
	$consulta="SELECT id__1_tablaDinamica FROM _1_tablaDinamica WHERE tipoInmueble=3";
	$listaEdificiosAlzada=$con->obtenerListaValores($consulta);
	if($listaEdificiosAlzada=="")
		$listaEdificiosAlzada=-1;
	
	$consulta="SELECT claveUnidad FROM _17_tablaDinamica WHERE idReferencia=".$listaEdificiosAlzada;
	$listaSalasAlzada=$con->obtenerListaValores($consulta);
	if($listaSalasAlzada=="")
		$listaSalasAlzada=-1;
		
		
	$idSalaAlzada=-1;	
	$consulta="SELECT unidadGestion,etapaProcesalActual FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativaBase='".$cAdministrativaBase."' 
				and unidadGestion in(".$listaSalasAlzada.") ORDER BY fechaCreacion DESC";
	
	$idSalaAlzadaIgnorar="-1";
	$fCarpetaAlzada=$con->obtenerPrimeraFila($consulta);
	if($fCarpetaAlzada)
	{
		
		$consulta="SELECT id__17_tablaDinamica FROM _17_tablaDinamica WHERE claveUnidad='".$fCarpetaAlzada[0]."'";
		$registroAsignado=$con->obtenerValor($consulta);
		
		$query="SELECT situacion FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$cAdministrativaBase."'";
		$situacionBase=$con->obtenerValor($query);
		
		if($fCarpetaAlzada[1]==$situacionBase)
		{
			$idSalaAlzada=$registroAsignado;
		}
		else
		{
			$idSalaAlzadaIgnorar=$registroAsignado;
		}
		
	}
	
	$idSalaAlzada=37;
	
	
	if($idSalaAlzada==-1)
	{
		$consulta="SELECT * FROM (
					SELECT id__17_tablaDinamica,claveUnidad,(SELECT IF(folioActual IS NULL,0,folioActual) 
					FROM 7004_seriesUnidadesGestion WHERE idUnidadGestion=t.id__17_tablaDinamica AND anio='2016') AS nFolio FROM _17_tablaDinamica t WHERE idReferencia IN
					(".$listaEdificiosAlzada.") and id__17_tablaDinamica<>".$idSalaAlzadaIgnorar.") AS tmp ORDER BY nFolio ASC,claveUnidad ASC
					";
		$fSala=$con->obtenerPrimeraFila($consulta);
		$idSalaAlzada=$fSala[0];
	}
	return generarNumeroTocaApelacion($idFormulario,$idRegistro,$idSalaAlzada);
}

function generarNumeroTocaApelacion($idFormulario,$idRegistro,$idUnidadGestion)
{
	global $con;
	$anio=date("Y");
	
	$query="SELECT carpetaAdministrativa FROM _310_tablaDinamica WHERE id__310_tablaDinamica=".$idRegistro;
	$cAdministrativaBase=$con->obtenerValor($query);
	
	$query="SELECT situacion FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$cAdministrativaBase."'";
	$situacionBase=$con->obtenerValor($query);
	
	
	$query="begin";
	if($con->ejecutarConsulta($query))
	{
		$query="select folioActual FROM 7004_seriesUnidadesGestion WHERE idUnidadGestion=".$idUnidadGestion." AND anio=".$anio;
		$query.=" for update";
		$folioActual=$con->obtenerValor($query);
		if($folioActual=="")
		{
			$folioActual=1;		
			$query="INSERT INTO 7004_seriesUnidadesGestion(idUnidadGestion,anio,folioActual,tipoDelito) VALUES(".$idUnidadGestion.
					",".$anio.",".$folioActual.",'')";
			
		}
		else
		{
			$folioActual++;
			$query="update 7004_seriesUnidadesGestion set folioActual=".$folioActual." where idUnidadGestion=".$idUnidadGestion." and anio=".$anio.
					" and tipoDelito=''";
		}
			
		if($con->ejecutarConsulta($query))
		{
			$query="commit";
			$con->ejecutarConsulta($query);
			
			$query="SELECT claveFolioCarpetas FROM _17_tablaDinamica WHERE id__17_tablaDinamica=".$idUnidadGestion;
			$cveUnidadGestion=$con->obtenerValor($query);
			$clave=($cveUnidadGestion-100)*1;
			$clave="S".$clave;
			
			$carpetaAdministrativa= "SP1-".$clave."/".str_pad($folioActual,4,"0",STR_PAD_LEFT)."/".$anio;
			
			$x=0;
			$consulta[$x]="begin";
			$x++;
			$consulta[$x]="INSERT INTO 7006_carpetasAdministrativas(carpetaAdministrativa,fechaCreacion,responsableCreacion,idFormulario,idRegistro,unidadGestion,etapaProcesalActual,carpetaAdministrativaBase,tipoCarpetaAdministrativa) 
							VALUES('".$carpetaAdministrativa."','".date("Y-m-d H:i:s")."',".$_SESSION["idUsr"].",".$idFormulario.",'".$idRegistro."','".$cveUnidadGestion."',".$situacionBase.",'".$cAdministrativaBase."',4)";
			$x++;
			$consulta[$x]="update _".$idFormulario."_tablaDinamica set noToca='".$carpetaAdministrativa."',salaAsignada='".$idUnidadGestion."' where id__".$idFormulario."_tablaDinamica=".$idRegistro;
			$x++;
			
			$consulta[$x]="commit";
			$x++;
			
			if($con->ejecutarBloque($consulta))
			{
				$query="SELECT idDocumento FROM 9074_documentosRegistrosProceso WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro;
				$rDocumentos=$con->obtenerFilas($query);
				while($fDocumento=mysql_fetch_row($rDocumentos))
				{
					registrarDocumentoCarpetaAdministrativa($carpetaAdministrativa,$fDocumento[0],$idFormulario,$idRegistro);	
				}
				
				return true;
				
				
			}
		}
	}
	return false;
	
}

function etapaTransitoriaJuicioAmparo($idFormulario,$idRegistro)
{
	global $con;
	
	$consulta="SELECT accionRecurso,requiereAudiencia FROM _314_tablaDinamica WHERE idReferencia=".$idRegistro;
	$fRegistro=$con->obtenerPrimeraFila($consulta);
	$etapa="";
	if($fRegistro[0]==2)
		$etapa="5";
	else
		if($fRegistro[1]==0)
			$etapa="4";
		else
			$etapa="4.5";
	
	cambiarEtapaFormulario($idFormulario,$idRegistro,$etapa,"",-1,"NULL","NULL",653);
	
	
}

function generarPropuestaFechaAudienciaAmparo($idFormulario,$idRegistro)
{
	global $con;
	
	$tipoAudiencia=93;
	

	$idEventoAudiencia=obtenerFechaAudienciaSolicitudAmparo($idFormulario,$idRegistro,-1,$tipoAudiencia);
	return ($idEventoAudiencia!=-1);
}

function obtenerFechaAudienciaSolicitudAmparo($idFormulario,$idRegistro,$idReferencia,$tipoAudiencia,$nivel=4)
{
	global $con;
	$consulta="SELECT idRegistroEvento,fechaEvento,horaInicioEvento,horaFinEvento,idEdificio,idCentroGestion,idSala 
				FROM 7000_eventosAudiencia where  idFormulario=".$idFormulario." and idRegistroSolicitud=".$idRegistro.
				" and idReferencia=".$idReferencia;
	$fEventoAudiencia=$con->obtenerPrimeraFila($consulta);
	
	$idEvento=$fEventoAudiencia[0];
	//$fEventoAudiencia=NULL;
	if((!$fEventoAudiencia)||($fEventoAudiencia[2]==""))
	{
		$oDatosAudiencia=array();
		
		$consulta="SELECT * FROM _310_tablaDinamica WHERE id__310_tablaDinamica=".$idRegistro;
		$fDatosSolicitud=$con->obtenerPrimeraFilaAsoc($consulta);
		
		$consulta="SELECT unidadGestion FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$fDatosSolicitud["noToca"]."'";
		$unidadGestion=$con->obtenerValor($consulta);
		
		$consulta="SELECT id__17_tablaDinamica,idReferencia FROM _17_tablaDinamica WHERE claveUnidad='".$unidadGestion."'";
		$fUnidadGestion=$con->obtenerPrimeraFila($consulta);
		
		$oDatosAudiencia["idRegistroEvento"]=$fEventoAudiencia[0];
		$oDatosAudiencia["idEdificio"]=$fUnidadGestion[1];
		$oDatosAudiencia["idUnidadGestion"]=$fUnidadGestion[0];
		
		
		$consulta="SELECT salasVinculadas FROM _55_tablaDinamica WHERE idReferencia=".$fUnidadGestion[0];
		$idSala=$con->obtenerValor($consulta);
		
		$oDatosAudiencia["idSala"]=$idSala;
		
		$oDatosAudiencia["fecha"]="";
		$oDatosAudiencia["horaInicio"]="";
		$oDatosAudiencia["horaFin"]="";
		$oDatosAudiencia["jueces"]="";		
		
		$oDatosParametros=array();
		$oDatosParametros["idFormulario"]=$idFormulario;
		$oDatosParametros["fechaSolicitud"]=$fDatosSolicitud["fechaCreacion"];
		$oDatosParametros["idRegistro"]=$idRegistro;
		$oDatosParametros["idReferencia"]=$idReferencia;
		$oDatosParametros["tipoAudiencia"]=$tipoAudiencia;
		$oDatosParametros["oDatosAudiencia"]=$oDatosAudiencia;
		$oDatosParametros["notificarMAJO"]=false;
		$oDatosParametros["nivelAsignacion"]=4; //1 Hasta UGJ; 2 Total
		
		
		
		$consulta="SELECT * FROM _27_tablaDinamica";
		$margen=10;
		$fConfiguracion=$con->obtenerPrimeraFilaAsoc($consulta);	
		
		$fechaSolicitud="";
		
		$cache=NULL;
		$cadObj='{"idFormulario":"'.$idFormulario.'","idRegistro":"'.$idRegistro.'","idReferencia":"'.$idReferencia.'","tipoAudiencia":"'.$tipoAudiencia.'"}';
		
		$obj=json_decode($cadObj);
		
		$consulta="SELECT fechaCreacion FROM _310_tablaDinamica WHERE id__310_tablaDinamica=".$idRegistro;
		
		$fechaSolicitud=$con->obtenerValor($consulta);
	
		$fechaMaximaAudiencia=NULL;  //Parametro
		
		$esSolicitudUgente=false; //Parametro
			
		$considerarDiaHabil=false;	//Parametro
		$minutosMiniminosFecha=0;
		$numeroHoraMaximaHoras=0;
		$duracionAudiencia=0;		//Parametro
		
		$funcionDiaHabil=$fConfiguracion["funcionDiHabil"];  //Parametro
		$totalJueces=1;
		
		$considerarDiaHabil=($fConfiguracion["agendarOrdinariaHabiles"]==0);
		$minutosMiniminosFecha=($fConfiguracion["horasMinimasOrdinaria"]*60);
		$numeroHoraMaximaHoras=0;
			
		
		
		$fechaMinimaAudiencia=date("Y-m-d H:i:s",strtotime("+".$minutosMiniminosFecha." minutes",strtotime($fechaSolicitud)));  //Parametro
		
		
		$consulta="SELECT promedioDuracion,horasMaximaAgendaAudiencia FROM _4_tablaDinamica WHERE id__4_tablaDinamica=".$tipoAudiencia;
		$fDatosTiposAudiencia=$con->obtenerPrimeraFila($consulta);
		$duracionAudiencia=$fDatosTiposAudiencia[0];
		$horasMaximas=$fDatosTiposAudiencia[1];
		if($horasMaximas>0)
		{
			$numeroHoraMaximaHoras=$horasMaximas;
		}
			
			
		if(($fechaMaximaAudiencia=="")&&($numeroHoraMaximaHoras>0))
			$fechaMaximaAudiencia=date("Y-m-d H:i:s",strtotime("+".$numeroHoraMaximaHoras." hours",strtotime($fechaSolicitud)));
		else
			if(($fechaMaximaAudiencia=="")&&($numeroHoraMaximaHoras==0))
				$fechaMaximaAudiencia=NULL;	
			
		
		
		$oDatosParametros["juecesRequeridos"]=array();
		$consulta="SELECT tipoJuez,titulo FROM _4_gridJuecesRequeridos WHERE idReferencia=".$tipoAudiencia;
		$rJueces=$con->obtenerFilas($consulta);	
		while($fJueces=mysql_fetch_row($rJueces))
		{
			$oJuez=array();
			$oJuez["tipoJuez"]=$fJueces[0];
			$oJuez["titulo"]=$fJueces[1];
			$oJuez["idUsuario"]="";
			if($oJuez["titulo"]=="")
			{
				$consulta="SELECT tipoJuez FROM _18_tablaDinamica WHERE id__18_tablaDinamica=".$oJuez["tipoJuez"];
				$oJuez["titulo"]=$con->obtenerValor($consulta);
			}
			array_push($oDatosParametros["juecesRequeridos"],$oJuez);
		}
		
		$oDatosParametros["duracionAudiencia"]=$duracionAudiencia;			
		$oDatosParametros["fechaMaximaAudiencia"]=$fechaMaximaAudiencia;
		$oDatosParametros["fechaMinimaAudiencia"]=$fechaMinimaAudiencia;
		$oDatosParametros["considerarDiaHabil"]=$considerarDiaHabil;
		$oDatosParametros["funcionDiaHabil"]=$funcionDiaHabil;
		$oDatosParametros["esSolicitudUgente"]=$esSolicitudUgente;		
		$oDatosParametros["fechaBasePeriodo"]=strtotime($fechaSolicitud);
		$diaBase=date("N",$oDatosParametros["fechaBasePeriodo"]);
		
		
		$oEvento=generarFechaAudienciaSolicitudAmparo($oDatosParametros);
		varDump($oEvento);
		
		return;
		
		if(gettype($oEvento)=="array")
		{
			$situacion=0;
			$etapaProcesal=1;
			$idEvento=registrarEventoAudiencia($oEvento,$idFormulario,$idRegistro,$idReferencia,$tipoAudiencia,$etapaProcesal,$situacion,$oDatosParametros);
		}
		else
			$idEvento=$oEvento;
		
	}
	return $idEvento;
}

function registrarBajaRegistroGeneral($iFormulario,$iRegistro,$campo,$motivoBaja)
{
	global $con;
	
	$consulta="select ".$campo." from _".$iFormulario."_tablaDinamica where id__".$iFormulario."_tablaDinamica=".$iRegistro;
	$valorCampo=$con->obtenerValor($consulta);
	$valorCampo="-".$valorCampo;
	$consulta="update  _".$iFormulario."_tablaDinamica set ".$campo."='".$valorCampo."' where id__".$iFormulario."_tablaDinamica=".$iRegistro;
	return $con->ejecutarConsulta($consulta);
	
	
	
	
}

function registrarFechaRegistroSolicitud($idFormulario,$idRegistro)
{
	global $con;
	
	
	$consulta="SELECT fechaCreacion FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$fechaCreacion=$con->obtenerValor($consulta);
	if(!(esDiaHabil($fechaCreacion)))
	{
		$fechaReferencia=date("Y-m-d",strtotime("+1 days",strtotime($fechaCreacion)));
		$fechaCreacion=obtenerProximoDiaHabil($fechaReferencia)." 09:00:00";
	}
	else
	{
		$fechaRegistro=strtotime($fechaCreacion);
		$fechaActual=date("Y-m-d",$fechaRegistro);
		$horaInicio=strtotime($fechaActual." 09:00:00");
		$horaFinal=strtotime($fechaActual." 14:00:00");
		
		if(($fechaRegistro>=$horaInicio)&&($fechaRegistro<=$horaFinal))
		{
			
		}
		else
		{
			if($fechaRegistro>$horaFinal)
			{
				$fechaReferencia=date("Y-m-d",strtotime("+1 days",$fechaRegistro));

				$fechaCreacion=obtenerProximoDiaHabil($fechaReferencia)." 09:00:00";
			}
			else
			{
				$fechaCreacion=	date("Y-m-d H:i:s",$horaInicio);
			}
		}
	}
	
	$consulta="update _".$idFormulario."_tablaDinamica set fechaCreacion='".$fechaCreacion."',fechaRecepcion='".date("Y-m-d",strtotime($fechaCreacion))."',horaRecepcion='".date("H:i:s",strtotime($fechaCreacion))."' WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	return $con->ejecutarConsulta($consulta);
}

function generarFolioCarpetaExhorto($idFormulario,$idRegistro)
{
	global $con;
	
	$consulta="SELECT codigoInstitucion FROM _92_tablaDinamica WHERE id__92_tablaDinamica=".$idRegistro;
	$codigoInstitucion=$con->obtenerValor($consulta);
	$idUnidadGestion=-1;
	$consulta="SELECT id__17_tablaDinamica FROM _17_tablaDinamica WHERE claveUnidad='".$codigoInstitucion."'";
	$idUnidadGestion=$con->obtenerValor($consulta);
	if($idUnidadGestion=="")
		$idUnidadGestion=-1;
		
	$anio=date("Y");
	
	$consulta="SELECT carpetaExhorto,idActividad FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$fDatosCarpeta=$con->obtenerPrimeraFila($consulta);

	
	if($fDatosCarpeta[0]!="")
		return true;
	$carpetaAdministrativa=obtenerSiguienteCarpetaAdministrativa($idUnidadGestion,$anio,2,$idFormulario,$idRegistro);	
	
	$idActividad=$fDatosCarpeta[1];
	
	$consulta=array();
	$x=0;
	$consulta[$x]="begin";
	$x++;
	$consulta[$x]="INSERT INTO 7006_carpetasAdministrativas(carpetaAdministrativa,fechaCreacion,responsableCreacion,idFormulario,
					idRegistro,unidadGestion,etapaProcesalActual,idActividad,carpetaAdministrativaBase,
					tipoCarpetaAdministrativa,unidadGestionOriginal) 
					VALUES('".$carpetaAdministrativa."','".date("Y-m-d H:i:s")."',".$_SESSION["idUsr"].",".$idFormulario.",'".$idRegistro."','".
					$codigoInstitucion."',0,".$idActividad.",'',2,'".$codigoInstitucion."')";
	$x++;
	$consulta[$x]="update _".$idFormulario."_tablaDinamica set carpetaExhorto='".$carpetaAdministrativa."' where id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$x++;

	$consulta[$x]="commit";
	$x++;

	if($con->ejecutarBloque($consulta))
	{

		$query="SELECT idDocumento FROM 9074_documentosRegistrosProceso WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro;

		$rDocumentos=$con->obtenerFilas($query);
		while($fDocumento=mysql_fetch_row($rDocumentos))
		{
			registrarDocumentoCarpetaAdministrativa($carpetaAdministrativa,$fDocumento[0],$idFormulario,$idRegistro);	
		}

		return true;

	}
	return false;
	
	
	
	
}

/*function generarFolioCarpetaExhorto($idFormulario,$idRegistro)
{
	global $con;
	
	$consulta="SELECT codigoInstitucion FROM _92_tablaDinamica WHERE id__92_tablaDinamica=".$idRegistro;
	$codigoInstitucion=$con->obtenerValor($consulta);
	$idUnidadGestion=-1;
	$consulta="SELECT id__17_tablaDinamica FROM _17_tablaDinamica WHERE claveUnidad='".$codigoInstitucion."'";
	$idUnidadGestion=$con->obtenerValor($consulta);
	if($idUnidadGestion=="")
		$idUnidadGestion=-1;
		
	$anio=date("Y");
	
	$consulta="SELECT carpetaExhorto FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$fDatosCarpeta=$con->obtenerPrimeraFila($consulta);

	
	if($fDatosCarpeta[0]!="")
		return true;
		
	
	$idActividad=-1;
		
	$tipoDelito="EX";	
	$query="begin";
	
	$carpetaAdministrativa="";
	
	switch($idUnidadGestion)
	{
		case 15:
			
			break;
		case 33:
			//$carpetaAdministrativa="006/0406/2017-EX";

			break;
		case 34:
		
			
		break;
		case 35:
			//$carpetaAdministrativa="008/0276/2017-EX";
		break;
		case 36:
			//$carpetaAdministrativa="009/0124/2017-EX";
		break;
		case 47:
			$carpetaAdministrativa="010/0530/2017-EX";
		break;
		case 48:
			$carpetaAdministrativa="009/0582/2017-EXNT";
		break;
		case 53:
			//$carpetaAdministrativa="EJEC-OTE-EXH/0008/2017";
		break;
		
		
	}
	
	if($carpetaAdministrativa!="")
	{
		$query="SELECT COUNT(*) FROM _92_tablaDinamica WHERE carpetaExhorto='".$carpetaAdministrativa."' and idEstado>1";
		$nReg=$con->obtenerValor($query);
		if($nReg==0)
		{
		
			$consulta=array();
			$x=0;
			$consulta[$x]="begin";
			$x++;
			$consulta[$x]="INSERT INTO 7006_carpetasAdministrativas(carpetaAdministrativa,fechaCreacion,responsableCreacion,idFormulario,idRegistro,unidadGestion,etapaProcesalActual,idActividad,carpetaAdministrativaBase,tipoCarpetaAdministrativa) 
							VALUES('".$carpetaAdministrativa."','".date("Y-m-d H:i:s")."',".$_SESSION["idUsr"].",".$idFormulario.",'".$idRegistro."','".$fDatosUnidad[1]."',0,-1,'',2)";
			$x++;
			$consulta[$x]="update _".$idFormulario."_tablaDinamica set carpetaExhorto='".$carpetaAdministrativa."' where id__".$idFormulario."_tablaDinamica=".$idRegistro;
			$x++;
			
			$consulta[$x]="commit";
			$x++;
			
			if($con->ejecutarBloque($consulta))
			{
				
				$query="SELECT idDocumento FROM 9074_documentosRegistrosProceso WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro;
	
				$rDocumentos=$con->obtenerFilas($query);
				while($fDocumento=mysql_fetch_row($rDocumentos))
				{
					registrarDocumentoCarpetaAdministrativa($carpetaAdministrativa,$fDocumento[0],$idFormulario,$idRegistro);	
				}
	
				return true;
				
			}
			return false;
		}
	}
	
	if($con->ejecutarConsulta($query))
	{
		$query="select folioActual FROM 7004_seriesUnidadesGestion WHERE idUnidadGestion=".$idUnidadGestion." AND anio=".$anio;
		$query.=" and tipoDelito='".$tipoDelito."' for update";
		
		$folioActual=$con->obtenerValor($query);
		if($folioActual=="")
		{
			$folioActual=1;		
			$query="INSERT INTO 7004_seriesUnidadesGestion(idUnidadGestion,anio,folioActual,tipoDelito) VALUES(".$idUnidadGestion.
					",".$anio.",".$folioActual.",'".$tipoDelito."')";
			
		}
		else
		{
			$folioActual++;
			$query="update 7004_seriesUnidadesGestion set folioActual=".$folioActual." where idUnidadGestion=".$idUnidadGestion." and anio=".$anio.
					" and tipoDelito='".$tipoDelito."'";
		}
			
		if($con->ejecutarConsulta($query))
		{
			$query="commit";
			$con->ejecutarConsulta($query);
			
			$query="SELECT claveFolioCarpetas,claveUnidad,siglasClave FROM _17_tablaDinamica WHERE id__17_tablaDinamica=".$idUnidadGestion;
			$fDatosUnidad=$con->obtenerPrimeraFila($query);
			$cveUnidadGestion=$fDatosUnidad[0];
			
			$carpetaAdministrativa= str_pad($cveUnidadGestion,3,"0",STR_PAD_LEFT)."/".str_pad($folioActual,4,"0",STR_PAD_LEFT)."/".$anio."-EX";
			if($idUnidadGestion==48)
				$carpetaAdministrativa.=$fDatosUnidad[2];
			
			switch($idUnidadGestion)
			{
				case 51: //Norte
					$carpetaAdministrativa= "EJEC-NTE-EXH/".str_pad($folioActual,4,"0",STR_PAD_LEFT)."/".$anio;
				break;
				case 53: //Oriente
					$carpetaAdministrativa= "EJEC-OTE-EXH/".str_pad($folioActual,4,"0",STR_PAD_LEFT)."/".$anio;
				break;
			}
			
			$consulta=array();
			$x=0;
			$consulta[$x]="begin";
			$x++;
			$consulta[$x]="INSERT INTO 7006_carpetasAdministrativas(carpetaAdministrativa,fechaCreacion,responsableCreacion,idFormulario,idRegistro,unidadGestion,etapaProcesalActual,idActividad,carpetaAdministrativaBase,tipoCarpetaAdministrativa) 
							VALUES('".$carpetaAdministrativa."','".date("Y-m-d H:i:s")."',".$_SESSION["idUsr"].",".$idFormulario.",'".$idRegistro."','".$fDatosUnidad[1]."',0,-1,'',2)";
			$x++;
			$consulta[$x]="update _".$idFormulario."_tablaDinamica set carpetaExhorto='".$carpetaAdministrativa."' where id__".$idFormulario."_tablaDinamica=".$idRegistro;
			$x++;
			
			$consulta[$x]="commit";
			$x++;
			
			if($con->ejecutarBloque($consulta))
			{
				
				$query="SELECT idDocumento FROM 9074_documentosRegistrosProceso WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro;

				$rDocumentos=$con->obtenerFilas($query);
				while($fDocumento=mysql_fetch_row($rDocumentos))
				{
					registrarDocumentoCarpetaAdministrativa($carpetaAdministrativa,$fDocumento[0],$idFormulario,$idRegistro);	
				}

				return true;
				
			}
		}
	}
	return false;
	
}*/




function generarFolioCarpetaTribunalEnjuciamiento($idFormulario,$idRegistro)
{
	global $con;

	$idUnidadGestion=32;
	$anio=date("Y");
	
	$query="SELECT carpetaAdministrativa,carpetaTribunalEnjuiciamiento,prisionPreventiva,reclusorio FROM _".$idFormulario."_tablaDinamica 
	WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	
	$fDatosCarpeta=$con->obtenerPrimeraFila($query);

	if($fDatosCarpeta[1]=="N/E")
		$fDatosCarpeta[1]="";
		
	$cAdministrativaBase=$fDatosCarpeta[0];
	$carpetaEnjuiciamiento=$fDatosCarpeta[1];
	if($carpetaEnjuiciamiento!="")
		return true;
		
	$query="SELECT idActividad,unidadGestion,carpetaInvestigacion,idCarpeta,carpetaAdministrativa FROM 7006_carpetasAdministrativas WHERE 
			carpetaAdministrativa='".$cAdministrativaBase."'";
	$fDatosCarpetaJudicial=$con->obtenerPrimeraFila($query);

	$idActividadBase=$fDatosCarpetaJudicial[0];
	$carpetaInvestigacion=$fDatosCarpetaJudicial[2];
	if($idActividadBase=="")
		$idActividadBase=-1;
	
	$idActividad=generarIDActividad($idFormulario);
	$unidadGestionCarpeta=$fDatosCarpetaJudicial[1];
	
	$query="SELECT * FROM _17_tablaDinamica WHERE claveUnidad='".$unidadGestionCarpeta."'";
	$fDatosUnidadGestion=$con->obtenerPrimeraFilaAsoc($query);

	if(($fDatosUnidadGestion["consideraCentroReclusion"]==1)&&($fDatosCarpeta[2]==1))
	{
		switch($unidadGestionCarpeta)
		{
			case "001":
			case "003":
			case "004":
			case "005":
				switch($fDatosCarpeta[3])
				{
					case "00020008"://Centro Femenil de ReinserciÛn Social (Santa Martha)
					case "00020004"://Centro Varonil de RehabilitaciÛn Psicosocial (CEVAREPSI)
						$fDatosCarpeta[3]="00020003";//Reclusorio Preventivo Varonil Sur
					break;
					
				}
			
			break;
			case "002":
			case "006":
			case "010":
				$fDatosCarpeta[3]="00020002";//Reclusorio Preventivo Varonil Oriente
			break;
			case "007":
			case "011":
				$fDatosCarpeta[3]="00020003";//Reclusorio Preventivo Varonil Sur
			break;
			case "008":
			case "209":
				$fDatosCarpeta[3]="00020001";//Reclusorio Preventivo Varonil Norte
				
			break;
		}
		
		
		
		
		
			
		$query="SELECT id__17_tablaDinamica FROM  _17_tablaDinamica u,_1_tablaDinamica e WHERE 
				u.idReferencia=e.id__1_tablaDinamica AND e.cveInmueble='".$fDatosCarpeta[3]."' AND  id__17_tablaDinamica 
				IN(SELECT idPadre FROM _17_tiposCarpetasAdministra WHERE idOpcion=5)";
		
		$idUnidadGestion=$con->obtenerListaValores($query);
		if(($idUnidadGestion==-1)||($idUnidadGestion==""))
			return ;
		
	}
	else
		$idUnidadGestion=$fDatosUnidadGestion["tribunalEnjuiciamiento"];
	
	$idUnidadGestionTmp=-1;
	$encontrado=false;
	$listaIng=-1;
	$validaConoceCausa=true;
	while(!$encontrado)
	{
		
		$arrCarga=array();
		$query="SELECT claveUnidad,id__17_tablaDinamica FROM _17_tablaDinamica WHERE id__17_tablaDinamica IN(".$idUnidadGestion.") and
		id__17_tablaDinamica not in(".$listaIng.")";
		$rTribunales=$con->obtenerFilas($query);
		
		if($con->filasAfectadas>0)
		{
		
			while($fTribunal=mysql_fetch_row($rTribunales))
			{
				$query="SELECT COUNT(*) FROM 7006_carpetasAdministrativas WHERE fechaCreacion>='2018-09-03' AND unidadGestion='".$fTribunal[0].
						"' AND tipoCarpetaAdministrativa=5";
				$nCarpetas=$con->obtenerValor($query);		
				$arrCarga[$fTribunal[1]]=$nCarpetas;
				
			}
			
			
			
			$nCargaMinima=-1;
			foreach($arrCarga as $iTribunal=>$total)
			{
				if($nCargaMinima==-1)
				{
					$nCargaMinima=$total;
				}
				
				if($nCargaMinima>$total)
				{
					$nCargaMinima=$total;
				}
			}
			
			
			
			foreach($arrCarga as $iTribunal=>$total)
			{
				if($total==$nCargaMinima)
				{
					$idUnidadGestionTmp=$iTribunal;
					
					if(!conoceJuezTribunalCarpeta($cAdministrativaBase,$iTribunal) || !$validaConoceCausa)	
					{
						$encontrado=true;
						$idUnidadGestion=$idUnidadGestionTmp;
						
					}
					break;
				}
			}
			$listaIng.=",".$idUnidadGestionTmp;
		}
		else
		{
			$listaIng=-1;
			$validaConoceCausa=false;
		}
	}
	
	$carpetaAdministrativa=obtenerSiguienteCarpetaAdministrativa($idUnidadGestion,$anio,5,$idFormulario,$idRegistro);
	
	$query=" SELECT lj.clave FROM _26_tablaDinamica lj,_26_tipoJuez tj WHERE lj.idReferencia=".$idUnidadGestion." AND 
				tj.idPadre =lj.id__26_tablaDinamica AND tj.idOpcion=2  ORDER BY usuarioJuez";
	$listaJueces=$con->obtenerListaValores($query,"'");

	$idJuezTribunal=obtenerSiguienteJuez(20,$listaJueces,-1);

	$query="SELECT claveUnidad,claveFolioCarpetas FROM _17_tablaDinamica WHERE id__17_tablaDinamica=".$idUnidadGestion;
	$fDatosUnidadTribunalE=$con->obtenerPrimeraFila($query);
	$cveUnidadGestion=$fDatosUnidadTribunalE[0];
	
	
	$x=0;
	$consulta[$x]="begin";
	$x++;
	$consulta[$x]="INSERT INTO 7006_carpetasAdministrativas(carpetaAdministrativa,fechaCreacion,responsableCreacion,
					idFormulario,idRegistro,unidadGestion,etapaProcesalActual,idActividad,carpetaAdministrativaBase,
					idJuezTitular,tipoCarpetaAdministrativa,carpetaInvestigacion,llaveCarpetaInvestigacion,unidadGestionOriginal) 
					VALUES('".$carpetaAdministrativa."','".date("Y-m-d H:i:s")."',".$_SESSION["idUsr"].",".$idFormulario.",'".$idRegistro.
					"','".$cveUnidadGestion."',5,".$idActividad.",'".$cAdministrativaBase."',".$idJuezTribunal.",5,(SELECT UPPER('".
					$carpetaInvestigacion."')),'".cv(generarLlaveCarpetaInvestigacion($carpetaInvestigacion))."','".$cveUnidadGestion."')";
	$x++;
	$consulta[$x]="update _".$idFormulario."_tablaDinamica set carpetaTribunalEnjuiciamiento='".$carpetaAdministrativa."',
					iUnidadReceptora=".$idUnidadGestion." where id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$x++;
	
	$consulta[$x]="INSERT INTO _338_tablaDinamica(idReferencia,fechaCreacion,responsable,idEstado,codigoInstitucion,carpetaTribunalEnjuiciamiento,juezTitularCarpeta)
				 VALUES(".$idRegistro.",'".date("Y-m-d H:i:s")."',".$_SESSION["idUsr"].",1,'".$_SESSION["codigoInstitucion"]
				 ."','".$carpetaAdministrativa."',".$idJuezTribunal.")";
	$x++;
	
	$consulta[$x]="commit";
	$x++;
	
	if($con->ejecutarBloque($consulta))
	{
		
		$query="SELECT idDocumento FROM 9074_documentosRegistrosProceso WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro;
		$rDocumentos=$con->obtenerFilas($query);
		while($fDocumento=mysql_fetch_row($rDocumentos))
		{
			registrarDocumentoCarpetaAdministrativa($carpetaAdministrativa,$fDocumento[0],$idFormulario,$idRegistro);	
		}
		
		$consulta="SELECT idOpcion FROM _320_chkImputadosRemite WHERE idPadre=".$idRegistro;
		$listaImputados=$con->obtenerListaValores($consulta);
		if($listaImputados=="")
			$listaImputados=-1;
		
		registrarImputadosNuevaCarpeta($listaImputados,$idActividad,$idActividadBase);
		registrarDelitosNuevaCarpeta($listaImputados,$idActividad,$idActividadBase,5);
		registrarCambioEtapaProcesalCarpeta($cAdministrativaBase,5,$idFormulario,$idRegistro,-1);
		
		$arrImputados=explode(",",$listaImputados);
		foreach($arrImputados as $imputado)
		{
			registrarCambioSituacionImputado($fDatosCarpetaJudicial[4],$fDatosCarpetaJudicial[3],$imputado,9,"","");
		}
		
		
		determinarSituacionCarpeta($fDatosCarpetaJudicial[4],$fDatosCarpetaJudicial[3]);
		
	}
		
	return false;
	
}



function registrarImputadosRemisionCarpetaJudicialEjecucion($idFormulario,$idRegistro)
{
	global $con;
	
	$arrDocumentos=array();
	$consulta="SELECT carpetaAdministrativa FROM _316_tablaDinamica WHERE id__316_tablaDinamica=".$idRegistro;
	$cAdministrativa=$con->obtenerValor($consulta);
	
	$consulta="SELECT idActividad FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$cAdministrativa."'";
	$idActividad=$con->obtenerValor($consulta);
	
	$consulta="SELECT idParticipante FROM 7005_relacionFigurasJuridicasSolicitud WHERE idActividad=".$idActividad." AND idFiguraJuridica=4";
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		$arrValores=array();
		$arrValores["idImputado"]=$fila[0];
		$idRegistroSolicitudCitacion=crearInstanciaRegistroFormulario(319,$idRegistro,1,$arrValores,$arrDocumentos,-1,659);
		
	}
}

function validarRemisionCarpetaJudicial($idFormulario,$idRegistro)
{
	global $con;
	
	$comp="<br><span style='color:#F00'><b>*</b></span> ";
	
	$resultado="";
	$consulta="SELECT  COUNT(*) FROM 9074_documentosRegistrosProceso WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro;
	$nReg=$con->obtenerValor($consulta);
	if($nReg==0)
	{
		$resultado.=$comp."Debe ingresar el documento de acuerdo para la remisi&oacute;n de la carpeta judicial";
	}
	
	return $resultado;
}

function validarResolucionPorAcuerdo($idFormulario,$idRegistro)
{
	global $con;
	
	
	$comp="<br><span style='color:#F00'><b>*</b></span> ";
	
	$resultado="";
	$consulta="SELECT  COUNT(*) FROM 9074_documentosRegistrosProceso WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro;
	$nReg=$con->obtenerValor($consulta);
	if($nReg==0)
	{
		$resultado.=$comp."Debe ingresar el documento de acuerdo";
	}
	
	return $resultado;
	
}

function registrarFinalizacionAudiencia($idFormulario,$idRegistro)
{
	global $con;
	$consulta="SELECT idEvento,fechaFinalizacion,horaTermino FROM _321_tablaDinamica WHERE id__321_tablaDinamica=".$idRegistro;
	$fDatosRegistro=$con->obtenerPrimeraFila($consulta);
	
	$consulta="SELECT * FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$fDatosRegistro[0];
	$fDatosAudiencia=$con->obtenerPrimeraFilaAsoc($consulta);
	
	
	
	$consulta="UPDATE 7000_eventosAudiencia SET situacion=2,horaInicioReal=horaInicioEvento,horaTerminoReal='".$fDatosRegistro[1]." ".$fDatosRegistro[2]."'
			 WHERE idRegistroEvento=".$fDatosRegistro[0];
	
	
	
	 if($con->ejecutarConsulta($consulta))
	 {
			return registrarTerminacionRecursosEventos($fDatosRegistro[0],$fDatosAudiencia["horaInicioEvento"],$fDatosRegistro[1]." ".$fDatosRegistro[2]);
	 }
	
	
}

function registrarCancelacionAudiencia($idFormulario,$idRegistro)
{
	global $con;
	$consulta="SELECT idEvento FROM _323_tablaDinamica WHERE id__323_tablaDinamica=".$idRegistro;
	$fDatosRegistro=$con->obtenerPrimeraFila($consulta);
	
	$consulta="UPDATE 7000_eventosAudiencia SET situacion=3 WHERE idRegistroEvento=".$fDatosRegistro[0];

	if($con->ejecutarConsulta($consulta))
	{
		@registrarCancelacionRecursosEventos($fDatosRegistro[0],"Se cancela la audiencia");
		@notificarCancelacionEventoMAJO($fDatosRegistro[0]);
		$consulta="SELECT situacion FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$fDatosRegistro[0];
		$status=$con->obtenerValor($consulta);
		if($status<>3)
		{
			$consulta="UPDATE 7000_eventosAudiencia SET situacion=3 WHERE idRegistroEvento=".$fDatosRegistro[0];
			 return $con->ejecutarConsulta($consulta);
		}
	}
	return false;
	
}

function registrarTerminacionPorAcuerdoAudiencia($idFormulario,$idRegistro)
{
	global $con;
	
	$consulta="SELECT idEvento,concedePeticion,cerrarCarpeta FROM _322_tablaDinamica WHERE id__322_tablaDinamica=".$idRegistro;
	$fDatosRegistro=$con->obtenerPrimeraFila($consulta);	
	
	$consulta="SELECT idFormulario,idRegistroSolicitud FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$fDatosRegistro[0];
	$fDatosEvento=$con->obtenerPrimeraFila($consulta);
	
	$consulta="UPDATE _".$fDatosEvento[0]."_tablaDinamica SET idEstado=4.5 WHERE id__".$fDatosEvento[0]."_tablaDinamica=".$fDatosEvento[1];
	$con->ejecutarConsulta($consulta);
	
	$consulta="UPDATE 7000_eventosAudiencia SET situacion=6 WHERE idRegistroEvento=".$fDatosRegistro[0];
	$con->ejecutarConsulta($consulta);

	$consulta="UPDATE 7001_asignacionesJuezAudiencia SET situacion=7 WHERE idEventoAudiencia=".$fDatosRegistro[0]." AND situacion=1";
	$con->ejecutarConsulta($consulta);
	@registrarCancelacionRecursosEventos($fDatosRegistro[0],"Se resuelve mediante acuerdo");
	@notificarCancelacionEventoMAJO($fDatosRegistro[0]);
	
	$consulta="SELECT carpetaAdministrativa FROM 7007_contenidosCarpetaAdministrativa WHERE tipoContenido=3 AND 
				idRegistroContenidoReferencia=".$fDatosRegistro[0];
				
	$cAdministrativa=$con->obtenerValor($consulta);
	
	if($fDatosRegistro[2]==1)
	{
		registrarCambioSituacionCarpeta($cAdministrativa,9,$idFormulario,$idRegistro,$fDatosRegistro[0]);
	}
	
	$consulta="SELECT idDocumento FROM 9074_documentosRegistrosProceso WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro.
			" AND tipoDocumento in(1,2)";
	$rDocumentos=$con->obtenerFilas($consulta);
	while($fDocumento=mysql_fetch_row($rDocumentos))
	{
		registrarDocumentoCarpetaAdministrativa($cAdministrativa,$fDocumento[0],$idFormulario,$idRegistro);

	}
	
	return $con->ejecutarConsulta($consulta);
	
}


function notificarCancelacionEventoMAJO($idEvento)
{
	global $con;
	global $servidorPruebas;
	global $tipoMateria;

	//@notificarCancelacionEventoAudienciaSIAJOPCabina($idEvento);
	if($servidorPruebas)
	{
		
		return true;
	}
	
	if($tipoMateria=="PT")
	{
		@enviarNotificacionMailCancelacion($idEvento);
		return true;
	}
	
	$consulta="SELECT idEdificio,idCentroGestion,idSala FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$idEvento;
	$fDatosEvento=$con->obtenerPrimeraFila($consulta);
	
	$consulta="SELECT perfilSala FROM _15_tablaDinamica WHERE id__15_tablaDinamica=".$fDatosEvento[2];
	$perfilSala=$con->obtenerValor($consulta);
	
	if(($fDatosEvento[0]==0)||($perfilSala==3))
		return true;
	$consulta="SELECT ip1,ip2,ip3,ip4,funcionCancelacion FROM _55_tablaDinamica WHERE salasVinculadas=".($fDatosEvento[2]==""?-1:$fDatosEvento[2])." AND idReferencia=".$fDatosEvento[1];

	$fDatosConexion=$con->obtenerPrimeraFila($consulta);
	if((!$fDatosConexion)||(($fDatosConexion[0]=="")||($fDatosConexion[1]=="")||($fDatosConexion[2]=="")||($fDatosConexion[3]=="")||($fDatosConexion[4]=="")))
	{
		$consulta="SELECT ip1,ip2,ip3,ip4,funcionCancelacion FROM _16_tablaDinamica WHERE idReferencia=".$fDatosEvento[0]	;
		$fDatosConexion=$con->obtenerPrimeraFila($consulta);
		
	}
	if(($fDatosConexion)&&($fDatosConexion[4]!=""))
	{
		$dirIP=$fDatosConexion[0].".".$fDatosConexion[1].".".$fDatosConexion[2].".".$fDatosConexion[3];
		$cache=NULL;
		$cObj='{"direccionIP":"'.$dirIP.'","idEvento":"'.$idEvento.'"}';
		$objFuncion=json_decode($cObj);
	
		$resultado=@removerComillasLimite(resolverExpresionCalculoPHP($fDatosConexion[4],$objFuncion,$cache));
		
	}
	
	return true;
}

function obtenerSiguienteJuez($tipoJuez,$listaJueces=-1,$listaJuecesIgnorar=-1)
{
	global $con;
	$fResultado=NULL;	
	$idResultado=-1;
	switch($tipoJuez)//10 Ejecucion; 20 Enjuiciamiento
	{
		case 10: //ejecucion
			
			
			
			$consulta="select idEntidad from 7002_controlSecuencialEntidades where tipoEntidad=".$tipoJuez." and idEntidad in (".$listaJueces.") 
						order by idRegistroSecuencia desc";
			$idEntidad=$con->obtenerValor($consulta);
			if($idEntidad=="")
				$idEntidad=0;
				
			$consulta=" SELECT clave,usuarioJuez FROM _26_tablaDinamica lj,_26_tipoJuez tj WHERE lj.idReferencia=36 AND 
 						tj.idPadre =lj.id__26_tablaDinamica AND tj.idOpcion=3 AND clave>".$idEntidad."   AND clave IN(".$listaJueces.") 
						 AND clave NOT IN(".$listaJuecesIgnorar.")  ORDER BY clave";
			$fResultado=$con->obtenerPrimeraFila($consulta);
			
			if(!$fResultado)
			{
				$consulta=" SELECT clave,usuarioJuez FROM _26_tablaDinamica lj,_26_tipoJuez tj WHERE lj.idReferencia=36 AND 
 						tj.idPadre =lj.id__26_tablaDinamica AND tj.idOpcion=3 AND clave<".$idEntidad."   AND clave IN(".$listaJueces.") 
						 AND clave NOT IN(".$listaJuecesIgnorar.")  ORDER BY clave";
				$fResultado=$con->obtenerPrimeraFila($consulta);
			}
			if((!$fResultado)&&($idEntidad!="0"))
			{
				
				$idResultado=$idEntidad;	
			}
						
				
		break;
		case 20: //tribunal enjuiciamiento
			
			$consulta="select idEntidad from 7002_controlSecuencialEntidades where tipoEntidad=".$tipoJuez." and idEntidad in (".$listaJueces.") 
						order by idRegistroSecuencia desc";
			$idEntidad=$con->obtenerValor($consulta);
			if($idEntidad=="")
				$idEntidad=0;
				
			$consulta=" SELECT clave,usuarioJuez FROM _26_tablaDinamica lj,_26_tipoJuez tj WHERE lj.idReferencia=32 AND 
 						tj.idPadre =lj.id__26_tablaDinamica AND tj.idOpcion=2 AND clave>".$idEntidad."   AND clave IN(".$listaJueces.") 
						 AND clave NOT IN(".$listaJuecesIgnorar.")  ORDER BY clave";

			$fResultado=$con->obtenerPrimeraFila($consulta);
			if(!$fResultado)
			{
				$consulta=" SELECT clave,usuarioJuez FROM _26_tablaDinamica lj,_26_tipoJuez tj WHERE lj.idReferencia=32 AND 
 						tj.idPadre =lj.id__26_tablaDinamica AND tj.idOpcion=2 AND clave<".$idEntidad."   AND clave IN(".$listaJueces.") 
						 AND clave NOT IN(".$listaJuecesIgnorar.")  ORDER BY clave";
				$fResultado=$con->obtenerPrimeraFila($consulta);
			}
			if((!$fResultado)&&($idEntidad!="0"))
			{
				
				$idResultado=$idEntidad;	
			}
			
		break;
		
		
	}
	if($fResultado)
	{
		$consulta="insert into 7002_controlSecuencialEntidades(tipoEntidad,idEntidad) values(".$tipoJuez.",".$fResultado[0].")";
		if($con->ejecutarConsulta($consulta))
			return $fResultado[1];
	}
	return -1;
}


function actualizarJuezTitularCarpeta($idFormulario,$idRegistro)
{
	global $con;
	$query="";
	$x=0;
	$tipoJuez=0;
	$consulta[$x]="begin";
	$x++;
	switch($idFormulario)
	{
		case 338:
			$query="SELECT juezTitularCarpeta,carpetaTribunalEnjuiciamiento FROM _338_tablaDinamica WHERE id__338_tablaDinamica=".$idRegistro;
			$tipoJuez=20;
		break;
		case 335:
			$query="SELECT juezEjecucion,carpetaEjecucion FROM _335_tablaDinamica WHERE id__335_tablaDinamica=".$idRegistro;
			$tipoJuez=10;
		break;
		


	}
	
	$fila=$con->obtenerPrimeraFila($query);
	
	$consulta[$x]="UPDATE 7006_carpetasAdministrativas SET idJuezTitular=".$fila[0]." WHERE carpetaAdministrativa='".$fila[1]."'";
	$x++;
	
	$consulta[$x]="insert into 7002_controlSecuencialEntidades(tipoEntidad,idEntidad) values(".$tipoJuez.",".$fila[0].")";
	$x++;
	$consulta[$x]="commit";
	$x++;
	
	
	return $con->ejecutarBloque($consulta);
	
	
	
}


function registrarSolicitudDefensorPublicoV2($idFormulario,$idRegistro)
{
	global $con;
	
	$consuta="SELECT idOpcion FROM _80_impuados WHERE idPadre=".$idRegistro;
	
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		
	}
	
	
}

function validarDocumentoAdjuntoEnvioSolicitud($idFormulario,$idRegistro)
{
	global $con;
	
	$comp="<br><span style='color:#F00'><b>*</b></span> ";
	
	$resultado="";
	$consulta="SELECT  COUNT(*) FROM 9074_documentosRegistrosProceso WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro;
	$nReg=$con->obtenerValor($consulta);
	if($nReg==0)
	{
		$resultado.=$comp."Debe ingresar el documento adjunto ha enviar junto a la solicitud";
	}
	
	return $resultado;
}


function registrarExhortoUnidadCorrespondiente($idFormulario,$idRegistro)
{
	global $con;
		
	$consulta="SELECT COUNT(*) FROM _92_tablaDinamica WHERE iFormulario=".$idFormulario." AND iRegistro=".$idRegistro;
	$nRegistro=$con->obtenerValor($consulta);
	if($nRegistro>0)
		return true;
	
	
	$consulta="SELECT * FROM _345_tablaDinamica WHERE id__345_tablaDinamica=".$idRegistro;
	$fRegistro=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$unidadGestion="";
	switch($fRegistro["turnarExhorto"])
	{
		case 1:  //Querella
			$tipoDelito="A";
			$consulta="SELECT idReferencia FROM _17_gridDelitosAtiende WHERE tipoDelito='".$tipoDelito."'";
			$lista=$con->obtenerListaValores($consulta);
			if($lista=="")
				$lista=-1;
				
			$arrResultados=array();	
			$consulta="SELECT claveUnidad,
				
						(SELECT folioActual FROM 7004_seriesUnidadesGestion WHERE 
						idUnidadGestion=t.id__17_tablaDinamica AND anio='".date("Y")."' AND tipoDelito='EX' ) 
						FROM _17_tablaDinamica t WHERE id__17_tablaDinamica IN (".$lista.")";
			$res=$con->obtenerFilas($consulta);
			while($fila=mysql_fetch_row($res))
			{
				$arrResultados[$fila[0]]=($fila[1]=="")?0:$fila[1];
			}
			
			$minNumero=-1;
			foreach($arrResultados as $uga=>$total)	
			{
				if($minNumero==-1)
					$minNumero=$total;
				
				if($total<$minNumero)
					$minNumero=$total;
			}
			
			
			$arrFinal=array();
			foreach($arrResultados as $uga=>$total)	
			{
				if($total==$minNumero)
				{
					array_push($arrFinal,$uga);
				}
			}
			
			
			$posFinal=0;
			if(sizeof($arrFinal)>1)
			{
				$posFinal=rand(0,sizeof($arrFinal)-1);
				
			}
			
			if(isset($arrFinal[$posFinal]))
				$unidadGestion=$arrFinal[$posFinal];
			else
				$unidadGestion="000";
			
				
		break;
		case 2:  //oficioso
			$tipoDelito="B";
			$consulta="SELECT idReferencia FROM _17_gridDelitosAtiende WHERE tipoDelito='".$tipoDelito."'";
			$lista=$con->obtenerListaValores($consulta);
			if($lista=="")
				$lista=-1;
			
			
			$arrResultados=array();	
			$consulta="SELECT claveUnidad,
				
						(SELECT folioActual FROM 7004_seriesUnidadesGestion WHERE 
						idUnidadGestion=t.id__17_tablaDinamica AND anio='".date("Y")."' AND tipoDelito='EX' ) 
						FROM _17_tablaDinamica t WHERE id__17_tablaDinamica IN (".$lista.")";
			$res=$con->obtenerFilas($consulta);
			while($fila=mysql_fetch_row($res))
			{
				$arrResultados[$fila[0]]=($fila[1]=="")?0:$fila[1];
			}
			
			$minNumero=-1;
			foreach($arrResultados as $uga=>$total)	
			{
				if($minNumero==-1)
					$minNumero=$total;
				
				if($total<$minNumero)
					$minNumero=$total;
			}
			
			
			$arrFinal=array();
			foreach($arrResultados as $uga=>$total)	
			{
				if($total==$minNumero)
				{
					array_push($arrFinal,$uga);
				}
			}
			
			
			$posFinal=0;
			if(sizeof($arrFinal)>1)
			{
				$posFinal=rand(0,sizeof($arrFinal)-1);
				
			}
			
			if(isset($arrFinal[$posFinal]))
				$unidadGestion=$arrFinal[$posFinal];
			else
				$unidadGestion="000";
				
		break;
		case 3:  //Unidad gestion
			$consulta="SELECT claveUnidad FROM _17_tablaDinamica WHERE id__17_tablaDinamica=".$fRegistro["unidadGestion"];
			$unidadGestion=$con->obtenerValor($consulta);
		break;
	}
	
	
	$arrValores=array();
	
	$arrValores["codigoInstitucion"]=$unidadGestion;
	$arrValores["numeroCausaOrigen"]=$fRegistro["numeroCausaOrigen"];
	$arrValores["autoridaExhortante"]=$fRegistro["autoridadExhortante"];
	$arrValores["resumen"]=$fRegistro["resumenExhorto"];
	//$arrValores["delito"]=$fRegistro["delito"];
	$arrValores["fechaRecepcion"]=$fRegistro["fechaCreacion"];
	$arrValores["horaRepepcion"]=$fRegistro["horaRecepcion"];
	$arrValores["numeroExhorto"]=$fRegistro["folioExhorto"];
	$arrValores["entidadFederativa"]=$fRegistro["entidadFederativa"];
	//$arrValores["otroDelito"]=$fRegistro["otroDelito"];
	$arrValores["noOficio"]=$fRegistro["noOficio"];
	$arrValores["iFormulario"]=$idFormulario;
	$arrValores["iRegistro"]=$idRegistro;
	$arrValores["idActividad"]=$fRegistro["idActividad"];
	$arrValores["juezExhortante"]=$fRegistro["juezExhortante"];
	$arrValores["delegacionExhorto"]=$fRegistro["delegacionExhorto"];
	
	$arrDocumentos=array();
	$consulta="SELECT idDocumento FROM 9074_documentosRegistrosProceso WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro." AND tipoDocumento in(1,2)";
	$rDocumentos=$con->obtenerFilas($consulta);
	while($fDocumento=mysql_fetch_row($rDocumentos))
	{
		array_push($arrDocumentos,$fDocumento[0]);
	}
	
	$idRegistroExhorto=crearInstanciaRegistroFormulario(92,-1,5,$arrValores,$arrDocumentos,-1,717);
	
	$x=0;
	$consulta=array();
	$consulta[$x]="begin";
	$x++;
	
	$consulta[$x]="INSERT INTO _92_gDelitos(idReferencia,otroDelito,delito)
					SELECT '".$idRegistroExhorto."' AS idReferencia,otroDelito,delito
					FROM _345_gDelitos WHERE idReferencia=".$idRegistro;
	$x++;
	
	$consulta[$x]="set @carpetExhorto:=(SELECT carpetaExhorto FROM _92_tablaDinamica WHERE id__92_tablaDinamica=".$idRegistroExhorto.")";
	$x++;
	$consulta[$x]="UPDATE _345_tablaDinamica SET carpetaExhorto=@carpetExhorto WHERE id__345_tablaDinamica=".$idRegistro;
	$x++;	
	
	$consulta[$x]="commit";
	$x++;
	return $con->ejecutarBloque($consulta);
	
	
}


function esFiguraJuridica($idFormulario,$idRegistro,$figuraJuridica)
{
	global $con;
	$consulta="";
	
	
	switch($idFormulario)
	{
		case 47:
			$consulta="SELECT figuraJuridica FROM _47_tablaDinamica WHERE id__47_tablaDinamica=".$idRegistro;
		break;
		case 225:
			$consulta="SELECT figuraJuridica FROM _225_tablaDinamica WHERE id__225_tablaDinamica=".$idRegistro;
		break;
	}	
	
	$figura=$con->obtenerValor($consulta);
	if($figura==$figuraJuridica)
		return 1;
	return 0;
}


function obtenerResponsablePenitenciaria($idFormulario,$idRegistro,$actorDestinatario)
{
	global $con;
	
	
	
	$arrDestinatario=array();
	$idUsuarioDestinatario=1;
	$rolActor=obtenerTituloRol($actorDestinatario);
	
	
	$consulta="SELECT reclusorios FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	if(($idFormulario==297)||($idFormulario==428))
		$consulta="SELECT reclusorio FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	
	
	//echo $consulta;
	$reclusorio=$con->obtenerValor($consulta);
	$cveReclusorio=$reclusorio;
	if($idFormulario!=428)
	{
		$consulta="SELECT clave FROM _2_tablaDinamica WHERE id__2_tablaDinamica=".$reclusorio;
		$cveReclusorio=$con->obtenerValor($consulta);
	}
	$consulta="SELECT idUsuario FROM 801_adscripcion WHERE Institucion='".$cveReclusorio."'";
	$rUsuarios=$con->obtenerFilas($consulta);
	while($fUsuario=mysql_fetch_row($rUsuarios))
	{
		$nombreUsuario=obtenerNombreUsuario($fUsuario[0])." (".$rolActor.")";
		$o='{"idUsuarioDestinatario":"'.$fUsuario[0].'","nombreUsuarioDestinatario":"'.$nombreUsuario.'"}';
		$o=json_decode($o);
		array_push($arrDestinatario,$o);
	}
	return $arrDestinatario;
}


function determinarRuta2_5SolicitudTraslado($idFormulario,$idRegistro)
{
	global $con;
	$consulta="SELECT atiendeSolicitud FROM _301_tablaDinamica WHERE idReferencia=".$idRegistro;
	$atiendeSolicitud=$con->obtenerValor($consulta);
	$etapaContinuacion=6;
	if($atiendeSolicitud==1)
	{
		$etapaContinuacion=3;
	}
	
	cambiarEtapaFormulario($idFormulario,$idRegistro,$etapaContinuacion,"",-1,"NULL","NULL",700);
	
	
}


function determinarRuta3_5SolicitudTraslado($idFormulario,$idRegistro)
{
	global $con;
	$consulta="SELECT enviaImputado FROM _358_tablaDinamica WHERE idReferencia=".$idRegistro;
	$atiendeSolicitud=$con->obtenerValor($consulta);
	$etapaContinuacion=6;
	if($atiendeSolicitud==1)
	{
		$etapaContinuacion=4;
	}
	
	cambiarEtapaFormulario($idFormulario,$idRegistro,$etapaContinuacion,"",-1,"NULL","NULL",698);
	
	
}

function determinarRuta4_5SolicitudTraslado($idFormulario,$idRegistro)
{
	global $con;
	$consulta="SELECT recibeImputado FROM _359_tablaDinamica WHERE idReferencia=".$idRegistro;
	$atiendeSolicitud=$con->obtenerValor($consulta);
	$etapaContinuacion=7;
	if($atiendeSolicitud==1)
	{
		$etapaContinuacion=5;
	}
	
	cambiarEtapaFormulario($idFormulario,$idRegistro,$etapaContinuacion,"",-1,"NULL","NULL",699);
	
	
}

function turnarUnidadGestionPromocion($idFormulario,$idRegistro)
{
	global $con;
	$consulta="SELECT unidadGestion,idReferencia FROM _360_tablaDinamica WHERE id__360_tablaDinamica=".$idRegistro;
	$fDatosRemision=$con->obtenerPrimeraFila($consulta);
	$unidadGestion=$fDatosRemision[0];
	
	$consulta="UPDATE _96_tablaDinamica SET codigoUnidad='".$unidadGestion."',codigoInstitucion='".$unidadGestion."' WHERE id__96_tablaDinamica=".$fDatosRemision[1];
	return $con->ejecutarConsulta($consulta);
	
	
}

function obtenerHorasAjusteDiasNoHabiles($fechaMinima,$fechaMaxima)
{
	
	$nIteraciones=0;
	$horasIncremento=0;
	$fechaMinima=strtotime($fechaMinima);

	while(strtotime(date("Y-m-d",$fechaMinima))<=strtotime(date("Y-m-d",strtotime($fechaMaxima))))
	{
		
		if(esDiaHabilInstitucion(date("Y-m-d",$fechaMinima))==0)
		{
			$horasIncremento+=24;
		}		
		$fechaMinima=strtotime("+1 days",$fechaMinima);	
		
			
	}
	
	$fechaFinal=strtotime("+".$horasIncremento." hours",strtotime($fechaMaxima));	
	return date("Y-m-d H:i:s",$fechaFinal);
}

function registrarBitacoraSolicitudWebServicesOperador($operador,$parametrosRecibido)
{
	global $con;
	$consulta="INSERT INTO 3017_solicitudesWebServicesOperadores(fechaSolicitud,xmlSolicitud,tipoOperador) VALUES('".date("Y-m-d H:i:s")."','".cv($parametrosRecibido)."',".$operador.")";
	if($con->ejecutarConsulta($consulta))
		return $con->obtenerUltimoID();
	return -1;
}

function actualizarSituacionBitacoraWebServices($idRegistroBitacora,$situacion,$comentarios)
{
	global $con;
	$consulta="UPDATE 3017_solicitudesWebServicesOperadores SET resultado=".$situacion.",mensajeComplementario='".cv($comentarios)."' WHERE idRegistroSolicitud=".$idRegistroBitacora;
	return $con->ejecutarConsulta($consulta);
}

function registrarCambioStatusAudiencia($idEvento,$statusActual,$idResponsable=-1,$iFormulario=-1,$iRegistro=-1,$cXML=NULL)
{
	global $con;
	$consulta="SELECT situacion FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$idEvento;


	$situacion=$con->obtenerValor($consulta);
	
	
	$x=0;
	$query[$x]="begin";
	$x++;
	$query[$x]="INSERT INTO 7017_bitacoraCambiosSituacionEventoAudiencia(idEventoAudiencia,idEstadoAnterior,idEstadoActual,fechaCambio,responsableCambio,iFormulario,iRegistro) VALUES
				(".$idEvento.",".$situacion.",".$statusActual.",'".date("Y-m-d H:i:s")."',".$idResponsable.",".$iFormulario.",".$iRegistro.")";
	$x++;
	$query[$x]="UPDATE 7000_eventosAudiencia SET situacion=".$statusActual." WHERE idRegistroEvento=".$idEvento;
	$x++;
	
	
	$horaInicio="NULL";
	$horaInicio=(string)$cXML->audience[0]->recording_start[0];
	if($horaInicio=="")
		$horaInicio="NULL";
	else
		$horaInicio="'".formatearFechaEventoSIAJOP($horaInicio)."'";
	
	
	$horaFin="NULL";
	$horaFin=(string)$cXML->audience[0]->recording_end[0];
	if($horaFin=="")
		$horaFin="NULL";
	else
		$horaFin="'".formatearFechaEventoSIAJOP($horaFin)."'";
	
	
	$idStatus=(string)$cXML->audience[0]["status"];


	switch($idStatus)
	{
		case 2:  //Finalizada
			$horaTerminoReal=formatearFechaEventoSIAJOP((string)$cXML->audience[0]->recording_end[0]);
			$consulta="SELECT horaTerminoReal FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$idEvento;
			$fFin=$con->obtenerValor($consulta);
			if($fFin=="")
			{
				$query[$x]="UPDATE 7000_eventosAudiencia SET horaTerminoRealMAJO='".$horaTerminoReal."'
							 WHERE idRegistroEvento=".$idEvento;
				$x++;
				
			}
		break;
		case 4:  //En desarrollo
			$horaInicioReal=formatearFechaEventoSIAJOP((string)$cXML->audience[0]->recording_start[0]);
			$consulta="SELECT horaInicioRealMAJO FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$idEvento;
			$fInicio=$con->obtenerValor($consulta);
			if($fInicio=="")
			{
				$query[$x]="UPDATE 7000_eventosAudiencia SET horaInicioRealMAJO='".$horaInicioReal."'
							 WHERE idRegistroEvento=".$idEvento;
				$x++;
				
			}
		break;
		
		
	}	
		
	$idMotivoPausa="NULL";
	$duracion="NULL";	
	
	$idMotivoPausa=(string)$cXML->audience[0]->pause_reason[0];
	$duracion=(string)$cXML->audience[0]->pause_duration[0];
	
	if($idMotivoPausa=="")
		$idMotivoPausa="NULL";
		
	if($duracion=="")
		$duracion="NULL";
			
	$query[$x]="INSERT INTO 7019_registroEventoSIAJOP(fechaNotificacion,horaInicio,horaFin,idMotivoPausa,duracion,tipoEvento,idEvento) VALUES ('".date("Y-m-d H:i:s").
				"',".$horaInicio.",".$horaFin.",".$idMotivoPausa.",".$duracion.",".((string)$cXML->audience[0]["status"]).",".$idEvento.")";
	
	

	$x++;
	
	$query[$x]="commit";
	$x++;
	return $con->ejecutarBloque($query);
	
}

function registrarBitacoraNotificacionSIAJOP($idEvento)
{
	global $con;
	$consulta="INSERT INTO 3009_bitacoraVideoGrabacion(fecha,idEvento) values('".date("Y-m-d H:i:s")."',".$idEvento.")";
	if($con->ejecutarConsulta($consulta))
	{
		return $con->obtenerUltimoID();
	}
	return -1;
}

function actualizarBitacoraNotificacionSIAJOP($idRegistro,$resultado,$comentarios,$puntoRuptura,$respuestaXML,$ws)
{
	global $con;
	$consulta="UPDATE 3009_bitacoraVideoGrabacion SET resultado=".$resultado.",comentario='".cv($comentarios)."',puntoRuptura=".$puntoRuptura.",servicioWeb=".$ws.",respuestaXML='".cv($respuestaXML)."' where idRegistro=".$idRegistro;
	return ($con->ejecutarConsulta($consulta));

}

function registrarBitacoraNotificacionOperadores($xml,$tipoOperador,$tipoSolicitud,$ws)
{
	global $con;
	$consulta="INSERT INTO 3018_notificacionesWebServicesOperadores(fechaNotificacion,xmlSolicitud,tipoOperador,tipoSolicitud,webServices) 
				VALUES('".date("Y-m-d H:i:s")."','".cv($xml)."',".$tipoOperador.",".$tipoSolicitud.",".$ws.")";
	if($con->ejecutarConsulta($consulta))
	{
		return $con->obtenerUltimoID();
	}
	return -1;
}

function actualizarBitacoraNotificacionOperadores($idRegistro,$resultado,$comentarios,$respuestaWS)
{
	global $con;
	$consulta="UPDATE 3018_notificacionesWebServicesOperadores SET resultado=".$resultado.",mensajeComplementario='".cv($comentarios)."',respuestaWebServices='".bE($respuestaWS)."' where idRegistroSolicitud=".$idRegistro;
	return ($con->ejecutarConsulta($consulta));

}

function similitudPalabras($frase1,$frase2)
{
	$frase1=normalizaToken(normalizarEspaciosFrase($frase1));
	$frase2=normalizaToken(normalizarEspaciosFrase($frase2));
	
	
	$arrFrase1=explode(" ",$frase1);
	$arrFrase2=explode(" ",$frase2);
	
	
	$arrBase;
	$arrReferencia;
	
	
	if(sizeof($arrFrase1)>sizeof($arrFrase2))
	{
		$arrBase=$arrFrase1;
		$arrReferencia=$arrFrase2;
	}
	else
	{
		$arrBase=$arrFrase2;
		$arrReferencia=$arrFrase1;
	}
	
	$similitud=0;
	$tamanoMuestra=100/sizeof($arrReferencia);
	
	foreach($arrReferencia as $token)
	{
		foreach($arrBase as $tokenBase)
		{
			if($token==$tokenBase)
			{
				$similitud++;
				break;
			}
		}
	}
	
	$similitud*=$tamanoMuestra;
	
	return $similitud;
	
}

function normalizarEspaciosFrase($frase)
{
	$enc=true;
	while($enc)
	{
		$frase=str_replace("  "," ",$frase);
		if(strpos($frase,"  ")===false)
			$enc=false;
	}
	return $frase;
}

function normalizaToken($cadena)
{
	return $cadena;
    $originales  = '¿¡¬√ƒ≈∆«»… ÀÃÕŒœ–“”‘’÷ÿŸ⁄€‹›ﬁﬂ‡·‚„‰ÂÊÁËÈÍÎÏÌÓÔÚÛÙıˆ¯˘˙˚˝˝˛ˇRr';
    $modificadas = 'AAAAAAACEEEEIIIIDOOOOOOUUUUYbsaaaaaaaceeeeiiiidoooooouuuyybyRr';
    $cadena = utf8_decode($cadena);
    $cadena = strtr($cadena, utf8_decode($originales), $modificadas);
    
    return utf8_encode($cadena);
}

function formatearNombreBusqueda($frase,$arrValores)
{
	$cadFrase="";
	$frase=normalizaToken($frase);
	$arrTokenFrases=explode(" ",$frase);
	foreach($arrTokenFrases as $t)
	{
		foreach($arrValores as $v)
		{
			if (strcasecmp($t, $v) == 0) 
			{
				$t="<span style='color:#900;font-weight:bold;'>".$t."</span>";
				$break;
			}
		}
		
		if($cadFrase=="")
			$cadFrase=$t;
		else
			$cadFrase.=" ".$t;
		
	}
	return $cadFrase;
}

function verificarTipificacionDelito($idFormulario,$idRegistro)
{
	global $con;	
	global $baseDir;
	$consulta="SELECT idActividad FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$idActividad=$con->obtenerValor($consulta);	
	
	$consulta="SELECT COUNT(*) FROM _61_tablaDinamica ds,_35_denominacionDelito d WHERE idActividad=".$idActividad." 
				AND ds.denominacionDelito=d.id__35_denominacionDelito AND d.delitoOficioso=1";
	$nReg=$con->obtenerValor($consulta);
	if($nReg>0)
	{
		registrarCorreccionAlgoritmo(1,$idRegistro,3);
		return 1;
	}
	else
	{
		
		$arrFrases[0]='225 FRACCI”N';
		$arrFrases[1]='225 FRACCION';
		$arrFrases[2]='225 FRAC.';
		$arrFrases[3]='223 FRACCI”N VIII';
		$arrFrases[4]='223 FRACCION VIII';
		$arrFrases[5]='223 FRACCI”N IX';
		$arrFrases[6]='223 FRACCION IX';
		$arrFrases[7]='223 FRAC. VIII';
		$arrFrases[8]='223 FRAC. IX';
		$arrFrases[9]='223 FRAC VIII';
		$arrFrases[10]='223 FRAC IX';
		$arrFrases[11]='224 FRACCI”N I ';
		$arrFrases[12]='224 FRACCION I ';
		$arrFrases[13]='224 FRACCI”N VIII';
		$arrFrases[14]='224 FRACCION VIII';
		$arrFrases[15]='224 FRAC. I ';
		$arrFrases[16]='224 FRAC. VIII';
		$arrFrases[17]='224 FRAC I ';
		$arrFrases[18]='224 FRAC VIII';
		$arrFrases[19]='QUERELLA, EXCEPTO CUANDO';
		$arrFrases[20]='QUERELLA EXCEPTO CUANDO';
		$arrFrases[21]='200 BIS FRACCION';
		$arrFrases[22]='200 BIS FRACCI”N';
		$arrFrases[23]='200-BIS FRACCI”N';
		$arrFrases[24]='200-BIS FRACCION';
		$arrFrases[25]='200 BIS FRAC.';
		$arrFrases[26]='200-BIS FRAC.';
		$arrFrases[27]='200 BIS PARRAFO PRIMERO, FRACCI”N';
		$arrFrases[28]='200 BIS PARRAFO PRIMERO, FRACCION';
		$arrFrases[29]='200-BIS PARRAFO PRIMERO, FRACCI”N';
		$arrFrases[30]='200-BIS PARRAFO PRIMERO, FRACCION';
		$arrFrases[31]='200 BIS PARRAFO PRIMERO, FRAC.';
		$arrFrases[32]='200 BIS PARRAFO PRIMERO, FRAC.';
		$arrFrases[33]='200-BIS PARRAFO PRIMERO, FRAC.';
		$arrFrases[34]='200-BIS PARRAFO PRIMERO, FRAC.';
		$arrFrases[35]='DENUNCIA COMO REQUISITO DE PROCEDIBILIDAD';
		$arrFrases[36]='200 BIS PARRAFO PRIMERO FRACCI”N';
		$arrFrases[37]='200 BIS PARRAFO PRIMERO FRACCION';
		$arrFrases[38]='200-BIS PARRAFO PRIMERO FRACCI”N';
		$arrFrases[39]='200-BIS PARRAFO PRIMERO FRACCION';
		$arrFrases[40]='200 BIS PARRAFO PRIMERO FRAC.';
		$arrFrases[41]='200 BIS PARRAFO PRIMERO FRAC.';
		$arrFrases[42]='200-BIS PARRAFO PRIMERO FRAC.';
		$arrFrases[43]='200-BIS PARRAFO PRIMERO FRAC.';
		
		$consulta="SELECT solicitudXML FROM _46_tablaDinamica WHERE id__46_tablaDinamica=".$idRegistro;
		$solicitud=bD($con->obtenerValor($consulta));
		if($solicitud=="")
			$solicitud=obtenerContenidoArchivoDatosPeticion(46,$idRegistro);
		
		if($solicitud!="")
		{
			$cXML=simplexml_load_string($solicitud);
			$datos=(string)$cXML->documentoAnexo[0]->contenido;
			$idDocumento=rand(1000,9999).".pdf";
			$filename=$baseDir.'\\archivosTemporales\\'.$idDocumento;
			$datos=bD($datos);
			$f=file_put_contents($filename,$datos);
			$pdfText= new PdfToText ($filename) ;			
			$pdfText=str_replace("\r"," ",$pdfText);	
			$pdfText=str_replace("\n"," ",$pdfText);	
			$pdfText=str_replace("\t"," ",$pdfText);	
			$pdfText=normalizarEspacios($pdfText);
			foreach($arrFrases as $token)
			{
				$posEnc=stripos($pdfText,$token);
				if($posEnc!==false)
				{
					registrarCorreccionAlgoritmo(1,$idRegistro,4);
					return 1;
				}
			}
			
			
		}
	}
	
	return -1;
}

function formatearFechaEventoSIAJOP($fecha)
{
	$fecha=str_replace("-","/",$fecha);
	$arrFecha=explode(" ",$fecha);

	$fecha=cambiaraFechaMysql($arrFecha[0])." ".$arrFecha[1];
	return $fecha;
	
}

function generarNotificacionMedidaCautelar($idEvento)
{
	global $con;
	
	$consulta="SELECT carpetaAdministrativa FROM 7007_contenidosCarpetaAdministrativa WHERE tipoContenido=3 
				AND idRegistroContenidoReferencia=".$idEvento;

	$cAdministrativa=$con->obtenerValor($consulta);
	$consulta="SELECT * FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$idEvento;
	$fRegistro=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$consulta="SELECT folioCarpetaInvestigacion,idActividad FROM _46_tablaDinamica WHERE carpetaAdministrativa='".$cAdministrativa."'";
	
	$fDatosSolicitud=$con->obtenerPrimeraFila($consulta);
	
	$folioCarpetaInv=$fDatosSolicitud[0];
	$idActividad=$fDatosSolicitud[1];
	
	$consulta="SELECT * FROM 7001_eventoAudienciaJuez WHERE idRegistroEvento=".$idEvento;
	$fJuez=$con->obtenerPrimeraFilaAsoc($consulta);	
	
	$consulta="SELECT Paterno,Materno,Nom FROM 802_identifica WHERE idUsuario=".$fJuez["idJuez"];
	$fDatosJuez=$con->obtenerPrimeraFilaAsoc($consulta);	
	
	$consulta="SELECT clave FROM _26_tablaDinamica WHERE usuarioJuez=".$fJuez["idJuez"];
	$noJuez=$con->obtenerValor($consulta);
	
	$arrImputados="";
	
	$consulta="SELECT idParticipante FROM 7005_relacionFigurasJuridicasSolicitud WHERE idActividad=".$fDatosSolicitud[1]." and idFiguraJuridica=4";
	$resParticipante=$con->obtenerFilas($consulta);
	while($fParticipante=mysql_fetch_row($resParticipante))
	{
		
		$consulta="SELECT * FROM _47_tablaDinamica WHERE id__47_tablaDinamica=".$fParticipante[0];
		$fDatosParticipantes=$con->obtenerPrimeraFilaAsoc($consulta);
		
		$arrDelitos="";
		
		
		$consulta="SELECT d.id__35_denominacionDelito,d.denominacionDelito FROM _61_tablaDinamica t,_61_chkDelitosImputado i, _35_denominacionDelito d
					WHERE i.idPadre=t.id__61_tablaDinamica AND t.denominacionDelito=d.id__35_denominacionDelito AND t.idActividad= ".$idActividad."
					ORDER BY d.denominacionDelito";
		$rDelitos=$con->obtenerFilas($consulta);
		
		if($con->filasAfectadas==0)
		{
			$consulta="SELECT d.id__35_denominacionDelito,d.denominacionDelito FROM _61_tablaDinamica t, _35_denominacionDelito d
					WHERE  t.denominacionDelito=d.id__35_denominacionDelito AND t.idActividad= ".$idActividad."
					ORDER BY d.denominacionDelito";
			$rDelitos=$con->obtenerFilas($consulta);
		}
		
		while($fDelitos=mysql_fetch_row($rDelitos))
		{
			$oDelito='{
							"cveDelito":"'.$fDelitos[0].'",
							"delito":"'.cv($fDelitos[1]).'"
					  }';
			if($arrDelitos=="")
				$arrDelitos=$oDelito;
			else
				$arrDelitos.=",".$oDelito;
		}
		
		$arrAlias="";
		$consulta="SELECT alias FROM _47_gridAlias WHERE idReferencia=".$fParticipante[0];
		$rAlias=$con->obtenerFilas($consulta);
		while($fAlias=mysql_fetch_row($rAlias))
		{
			$a='{"alias":"'.cv($fAlias[0]).'"}';
			if($arrAlias=="")
				$arrAlias=$a;
			else
				$arrAlias.=",".$a;
		}
		
		
		$consulta="SELECT * FROM _48_tablaDinamica WHERE idReferencia=".$fParticipante[0];
		$fDatosContacto=$con->obtenerPrimeraFilaAsoc($consulta);
		
		$arrTelefonos="";
		$consulta="SELECT tipoTelefono,lada,numero,extension FROM _48_telefonos WHERE idReferencia=".$fDatosContacto["id__48_tablaDinamica"];
		$rTelefono=$con->obtenerFilas($consulta);
		while($fTelefono=mysql_fetch_row($rTelefono))
		{
			$o='{
					"lada":"'.$fTelefono[1].'", 
					"numero":"'.$fTelefono[2].'", 
					"extension":"'.$fTelefono[3].'"
				}';
		
			if($arrTelefonos=="")
				$arrTelefonos=$o;
			else
				$arrTelefonos.=",".$o;
		
		}
		
		$arrMail="";
		$consulta="SELECT correo FROM _48_correosElectronico WHERE idReferencia=".$fDatosContacto["id__48_tablaDinamica"];
		$rMail=$con->obtenerFilas($consulta);
		while($fMail=mysql_fetch_row($rMail))
		{
			$o='{
						"email":"'.$fMail[0].'"
					}';
			if($arrMail=="")
				$arrMail=$o;
			else
				$arrMail.=",".$o;
		}
		
		
		$arrMedidaCautelar="";
		$consulta="SELECT tipoMedida,comentariosAdicionales,valorComp1,valorComp2,valorComp3 
					FROM 3014_registroMedidasCautelares WHERE idEventoAudiencia=".$idEvento." AND idImputado=".$fParticipante[0];
		
		$rMedidaCautelar=$con->obtenerFilas($consulta);
		
		while($fMedidaCautelar=mysql_fetch_row($rMedidaCautelar))
		{
			$oMedida="";
			if($fMedidaCautelar[0]==2)
			{
				$oMedida='{
							  "cveMedidaCautelar":"'.$fMedidaCautelar[0].'",
							  "detalleMedida":"(No pagos/exhibiciones: '.$fMedidaCautelar[3].'). '.cv($fMedidaCautelar[1]).'",
							  "montoMedida":"'.$fMedidaCautelar[2].'" 
						  }';
			}
			else
			{
				$comp="";
				if($fMedidaCautelar[0]==1)
				{

					$consulta="select nombreAutoridad FROM _328_tablaDinamica where id__328_tablaDinamica=".$fMedidaCautelar[2];
					$nombreAutoridad=$con->obtenerValor($consulta);
					$comp=" (Autoridad ante la cual debe presentarse: ".cv($nombreAutoridad).").";
				}
				$oMedida='{
							  "cveMedidaCautelar":"'.$fMedidaCautelar[0].'",
							  "detalleMedida":"'.cv($fMedidaCautelar[1]).$comp.'",
							  "montoMedida":"" 
						  }';
			}
			if($arrMedidaCautelar=="")
				$arrMedidaCautelar=$oMedida;
			else
				$arrMedidaCautelar.=",".$oMedida;
		}
		
		if($arrMedidaCautelar=="")
			continue;
		
		$o='{
				"idImputado":"'.$fParticipante[0].'",
				"tipoPersona":"'.$fDatosParticipantes["tipoPersona"].'",   
				"apPaterno":"'.cv($fDatosParticipantes["apellidoPaterno"]).'",  
				"apMaterno":"'.cv($fDatosParticipantes["apellidoMaterno"]).'",	
				"nombre":"'.cv($fDatosParticipantes["nombre"]).'",   
				"curp":"'.$fDatosParticipantes["curp"].'",
				"rfc":"'.$fDatosParticipantes["rfcEmpresa"].'",
				"fechaNacimiento":"'.$fDatosParticipantes["fechaNacimiento"].'",
				"genero":"'.$fDatosParticipantes["genero"].'", 
				"delitos": 	['.$arrDelitos.'],
				"alias":	['.$arrAlias.'],
				"datosContacto":	{
										"domicilio":	{
															"calle":"'.cv($fDatosContacto["calle"]).'",
															"numeroInt":"'.cv($fDatosContacto["noInterior"]).'",  
															"numeroExt":"'.cv($fDatosContacto["noExt"]).'", 
															"colonia":"'.cv($fDatosContacto["colonia"]).'",	
															"cp":"'.cv($fDatosContacto["codigoPostal"]).'",	
															"estado":"'.cv($fDatosContacto["entidadFederativa"]).'", 
															"municipio":"'.cv($fDatosContacto["municipio"]).'", 
															"localidad":"'.cv($fDatosContacto["localidad"]).'" 
														},
										"telefono":	[
														'.$arrTelefonos.'
													],
										"email":	[
														'.$arrMail.'
													]
									},
				"medidasCautelares":	[
											'.$arrMedidaCautelar.'
										]           
		
			}';
		
		if($arrImputados=="")
			$arrImputados=$o;
		else
			$arrImputados.=",".$o;
	}
	
	$arrVictimas="";
	$consulta="SELECT idParticipante FROM 7005_relacionFigurasJuridicasSolicitud WHERE idActividad=".$fDatosSolicitud[1]." and idFiguraJuridica=2";
	$resParticipante=$con->obtenerFilas($consulta);
	while($fParticipante=mysql_fetch_row($resParticipante))
	{
		
		$consulta="SELECT * FROM _47_tablaDinamica WHERE id__47_tablaDinamica=".$fParticipante[0];
		$fDatosParticipantes=$con->obtenerPrimeraFilaAsoc($consulta);
		
		
		
		$consulta="SELECT * FROM _48_tablaDinamica WHERE idReferencia=".$fParticipante[0];
		$fDatosContacto=$con->obtenerPrimeraFilaAsoc($consulta);
		
		$arrTelefonos="";
		$consulta="SELECT tipoTelefono,lada,numero,extension FROM _48_telefonos WHERE idReferencia=".$fDatosContacto["id__48_tablaDinamica"];
		$rTelefono=$con->obtenerFilas($consulta);
		while($fTelefono=mysql_fetch_row($rTelefono))
		{
			$o='{
					"lada":"'.$fTelefono[1].'", 
					"numero":"'.$fTelefono[2].'", 
					"extension":"'.$fTelefono[3].'"
				}';
		
			if($arrTelefonos=="")
				$arrTelefonos=$o;
			else
				$arrTelefonos.=",".$o;
		
		}
		
		$arrMail="";
		$consulta="SELECT correo FROM _48_correosElectronico WHERE idReferencia=".$fDatosContacto["id__48_tablaDinamica"];
		$rMail=$con->obtenerFilas($consulta);
		while($fMail=mysql_fetch_row($rMail))
		{
			$o='{
						"email":"'.$fMail[0].'"
					}';
			if($arrMail=="")
				$arrMail=$o;
			else
				$arrMail.=",".$o;
		}
		
		$o="";
		
		if($fDatosParticipantes["tipoPersona"]==1)
		{
		
			$o='{
					"idVictima":"'.$fParticipante[0].'",
					"tipoPersona":"'.$fDatosParticipantes["tipoPersona"].'",   
					"apPaterno":"'.cv($fDatosParticipantes["apellidoPaterno"]).'",  
					"apMaterno":"'.cv($fDatosParticipantes["apellidoMaterno"]).'",	
					"nombre":"'.cv($fDatosParticipantes["nombre"]).'",   
					"curp":"'.$fDatosParticipantes["curp"].'",
					"rfc":"'.$fDatosParticipantes["rfcEmpresa"].'",
					"fechaNacimiento":"'.$fDatosParticipantes["fechaNacimiento"].'",
					"genero":"'.$fDatosParticipantes["genero"].'", 
					"datosContacto":	{
											"domicilio":	{
																"calle":"'.cv($fDatosContacto["calle"]).'",
																"numeroInt":"'.cv($fDatosContacto["noInterior"]).'",  
																"numeroExt":"'.cv($fDatosContacto["noExt"]).'", 
																"colonia":"'.cv($fDatosContacto["colonia"]).'",	
																"cp":"'.cv($fDatosContacto["codigoPostal"]).'",	
																"estado":"'.cv($fDatosContacto["entidadFederativa"]).'", 
																"municipio":"'.cv($fDatosContacto["municipio"]).'", 
																"localidad":"'.cv($fDatosContacto["localidad"]).'" 
															},
											"telefono":	[
															'.$arrTelefonos.'
														],
											"email":	[
															'.$arrMail.'
														]
										}          
			
				}';
		}
		else
		{
			$o='{
					"idVictima":"'.$fParticipante[0].'",
					"tipoPersona":"'.$fDatosParticipantes["tipoPersona"].'",   
					"razoSocial":"'.cv($fDatosParticipantes["nombre"]).'",   
					"rfc":"'.$fDatosParticipantes["rfcEmpresa"].'",
					"datosContacto":	{
											"domicilio":	{
																"calle":"'.cv($fDatosContacto["calle"]).'",
																"numeroInt":"'.cv($fDatosContacto["noInterior"]).'",  
																"numeroExt":"'.cv($fDatosContacto["noExt"]).'", 
																"colonia":"'.cv($fDatosContacto["colonia"]).'",	
																"cp":"'.cv($fDatosContacto["codigoPostal"]).'",	
																"estado":"'.cv($fDatosContacto["entidadFederativa"]).'", 
																"municipio":"'.cv($fDatosContacto["municipio"]).'", 
																"localidad":"'.cv($fDatosContacto["localidad"]).'" 
															},
											"telefono":	[
															'.$arrTelefonos.'
														],
											"email":	[
															'.$arrMail.'
														]
										}          
			
				}';
		}
		if($arrVictimas=="")
			$arrVictimas=$o;
		else
			$arrVictimas.=",".$o;
	}
	
	
	$arrDefensores="";
	$consulta="SELECT idParticipante FROM 7005_relacionFigurasJuridicasSolicitud WHERE idActividad=".$fDatosSolicitud[1]." and idFiguraJuridica=5";
	$resParticipante=$con->obtenerFilas($consulta);
	while($fParticipante=mysql_fetch_row($resParticipante))
	{	
		$consulta="SELECT * FROM _47_tablaDinamica WHERE id__47_tablaDinamica=".$fParticipante[0];
		$fDatosParticipantes=$con->obtenerPrimeraFilaAsoc($consulta);
		
		$consulta="SELECT * FROM _48_tablaDinamica WHERE idReferencia=".$fParticipante[0];
		$fDatosContacto=$con->obtenerPrimeraFilaAsoc($consulta);
		
		$arrTelefonos="";
		$consulta="SELECT tipoTelefono,lada,numero,extension FROM _48_telefonos WHERE idReferencia=".$fDatosContacto["id__48_tablaDinamica"];
		$rTelefono=$con->obtenerFilas($consulta);
		while($fTelefono=mysql_fetch_row($rTelefono))
		{
			$o='{
					"lada":"'.$fTelefono[1].'", 
					"numero":"'.$fTelefono[2].'", 
					"extension":"'.$fTelefono[3].'"
				}';
		
			if($arrTelefonos=="")
				$arrTelefonos=$o;
			else
				$arrTelefonos.=",".$o;
		
		}
		
		$arrMail="";
		$consulta="SELECT correo FROM _48_correosElectronico WHERE idReferencia=".$fDatosContacto["id__48_tablaDinamica"];
		$rMail=$con->obtenerFilas($consulta);
		while($fMail=mysql_fetch_row($rMail))
		{
			$o='{
						"email":"'.$fMail[0].'"
					}';
			if($arrMail=="")
				$arrMail=$o;
			else
				$arrMail.=",".$o;
		}
		
		$arrImputadoDefendido="";
		$consulta="SELECT i.idOpcion FROM _353_tablaDinamica fi,_353_chkImputados i WHERE i.idPadre=fi.id__353_tablaDinamica AND fi.idReferencia=".$fParticipante[0];
		$rImputadoDefendido=$con->obtenerFilas($consulta);
		while($fImputado=mysql_fetch_row($rImputadoDefendido))
		{
			$i='{
					"idImputado":"'.$fImputado[0].'"
				}';
			if($arrImputadoDefendido=="")
				$arrImputadoDefendido=$i;
			else
				$arrImputadoDefendido.=",".$i;
		}
		
		$o='{
				"idDefensor":"'.$fParticipante[0].'",
				"apPaterno":"'.cv($fDatosParticipantes["apellidoPaterno"]).'",  
				"apMaterno":"'.cv($fDatosParticipantes["apellidoMaterno"]).'",	
				"nombre":"'.cv($fDatosParticipantes["nombre"]).'", 
				"imputadosDefendidos":	[
											'.$arrImputadoDefendido.'
										],    
				"datosContacto":	{
										"domicilio":	{
															"calle":"'.cv($fDatosContacto["calle"]).'",
															"numeroInt":"'.cv($fDatosContacto["noInterior"]).'",  
															"numeroExt":"'.cv($fDatosContacto["noExt"]).'", 
															"colonia":"'.cv($fDatosContacto["colonia"]).'",	
															"cp":"'.cv($fDatosContacto["codigoPostal"]).'",	
															"estado":"'.cv($fDatosContacto["entidadFederativa"]).'", 
															"municipio":"'.cv($fDatosContacto["municipio"]).'", 
															"localidad":"'.cv($fDatosContacto["localidad"]).'" 
														},
										"telefono":	[
														'.$arrTelefonos.'
													],
										"email":	[
														'.$arrMail.'
													]
									}          
		
			}';
		
		
		if($arrDefensores=="")
			$arrDefensores=$o;
		else
			$arrDefensores.=",".$o;
	}
	
	$arrAsesores="";
	$consulta="SELECT idParticipante FROM 7005_relacionFigurasJuridicasSolicitud WHERE idActividad=".$fDatosSolicitud[1]." and idFiguraJuridica=3";
	$resParticipante=$con->obtenerFilas($consulta);
	while($fParticipante=mysql_fetch_row($resParticipante))
	{	
		$consulta="SELECT * FROM _47_tablaDinamica WHERE id__47_tablaDinamica=".$fParticipante[0];
		$fDatosParticipantes=$con->obtenerPrimeraFilaAsoc($consulta);
		
		$consulta="SELECT * FROM _48_tablaDinamica WHERE idReferencia=".$fParticipante[0];
		$fDatosContacto=$con->obtenerPrimeraFilaAsoc($consulta);
		
		$arrTelefonos="";
		$consulta="SELECT tipoTelefono,lada,numero,extension FROM _48_telefonos WHERE idReferencia=".$fDatosContacto["id__48_tablaDinamica"];
		$rTelefono=$con->obtenerFilas($consulta);
		while($fTelefono=mysql_fetch_row($rTelefono))
		{
			$o='{
					"lada":"'.$fTelefono[1].'", 
					"numero":"'.$fTelefono[2].'", 
					"extension":"'.$fTelefono[3].'"
				}';
		
			if($arrTelefonos=="")
				$arrTelefonos=$o;
			else
				$arrTelefonos.=",".$o;
		
		}
		
		$arrMail="";
		$consulta="SELECT correo FROM _48_correosElectronico WHERE idReferencia=".$fDatosContacto["id__48_tablaDinamica"];
		$rMail=$con->obtenerFilas($consulta);
		while($fMail=mysql_fetch_row($rMail))
		{
			$o='{
						"email":"'.$fMail[0].'"
					}';
			if($arrMail=="")
				$arrMail=$o;
			else
				$arrMail.=",".$o;
		}
		
		$arrVictimasAsesoradas="";
		$consulta="SELECT i.idOpcion FROM _354_tablaDinamica fi,_354_chkVictimas i WHERE i.idPadre=fi.id__354_tablaDinamica 
				AND fi.idReferencia=".$fParticipante[0];
		$rImputadoDefendido=$con->obtenerFilas($consulta);
		while($fImputado=mysql_fetch_row($rImputadoDefendido))
		{
			$i='{
					"idVictima":"'.$fImputado[0].'"
				}';
			if($arrVictimasAsesoradas=="")
				$arrVictimasAsesoradas=$i;
			else
				$arrVictimasAsesoradas.=",".$i;
		}
		
		$o='{
				"idAsesor":"'.$fParticipante[0].'",
				"apPaterno":"'.cv($fDatosParticipantes["apellidoPaterno"]).'",  
				"apMaterno":"'.cv($fDatosParticipantes["apellidoMaterno"]).'",	
				"nombre":"'.cv($fDatosParticipantes["nombre"]).'", 
				"victimasAsesoradas":	[
											'.$arrVictimasAsesoradas.'
										],    
				"datosContacto":	{
										"domicilio":	{
															"calle":"'.cv($fDatosContacto["calle"]).'",
															"numeroInt":"'.cv($fDatosContacto["noInterior"]).'",  
															"numeroExt":"'.cv($fDatosContacto["noExt"]).'", 
															"colonia":"'.cv($fDatosContacto["colonia"]).'",	
															"cp":"'.cv($fDatosContacto["codigoPostal"]).'",	
															"estado":"'.cv($fDatosContacto["entidadFederativa"]).'", 
															"municipio":"'.cv($fDatosContacto["municipio"]).'", 
															"localidad":"'.cv($fDatosContacto["localidad"]).'" 
														},
										"telefono":	[
														'.$arrTelefonos.'
													],
										"email":	[
														'.$arrMail.'
													]
									}          
		
			}';
		
		
		if($arrAsesores=="")
			$arrAsesores=$o;
		else
			$arrAsesores.=",".$o;
	}
	
	$arrRepresentantes="";
	$consulta="SELECT idParticipante FROM 7005_relacionFigurasJuridicasSolicitud WHERE idActividad=".$fDatosSolicitud[1]." and idFiguraJuridica=6";
	$resParticipante=$con->obtenerFilas($consulta);
	while($fParticipante=mysql_fetch_row($resParticipante))
	{	
		$consulta="SELECT * FROM _47_tablaDinamica WHERE id__47_tablaDinamica=".$fParticipante[0];
		$fDatosParticipantes=$con->obtenerPrimeraFilaAsoc($consulta);
		
		$consulta="SELECT * FROM _48_tablaDinamica WHERE idReferencia=".$fParticipante[0];
		$fDatosContacto=$con->obtenerPrimeraFilaAsoc($consulta);
		
		$arrTelefonos="";
		$consulta="SELECT tipoTelefono,lada,numero,extension FROM _48_telefonos WHERE idReferencia=".$fDatosContacto["id__48_tablaDinamica"];
		$rTelefono=$con->obtenerFilas($consulta);
		while($fTelefono=mysql_fetch_row($rTelefono))
		{
			$o='{
					"lada":"'.$fTelefono[1].'", 
					"numero":"'.$fTelefono[2].'", 
					"extension":"'.$fTelefono[3].'"
				}';
		
			if($arrTelefonos=="")
				$arrTelefonos=$o;
			else
				$arrTelefonos.=",".$o;
		
		}
		
		$arrMail="";
		$consulta="SELECT correo FROM _48_correosElectronico WHERE idReferencia=".$fDatosContacto["id__48_tablaDinamica"];
		$rMail=$con->obtenerFilas($consulta);
		while($fMail=mysql_fetch_row($rMail))
		{
			$o='{
						"email":"'.$fMail[0].'"
					}';
			if($arrMail=="")
				$arrMail=$o;
			else
				$arrMail.=",".$o;
		}
		
		$arrRepresentados="";
		$consulta="SELECT i.idOpcion FROM _355_tablaDinamica fi,_355_chkImplicados i WHERE i.idPadre=fi.id__355_tablaDinamica 
				AND fi.idReferencia=".$fParticipante[0];
		$rImputadoDefendido=$con->obtenerFilas($consulta);
		while($fImputado=mysql_fetch_row($rImputadoDefendido))
		{
			$i='{
					"idRepresentado":"'.$fImputado[0].'"
				}';
			if($arrRepresentados=="")
				$arrRepresentados=$i;
			else
				$arrRepresentados.=",".$i;
		}
		
		$o='{
				"idRepresentante":"'.$fParticipante[0].'",
				"apPaterno":"'.cv($fDatosParticipantes["apellidoPaterno"]).'",  
				"apMaterno":"'.cv($fDatosParticipantes["apellidoMaterno"]).'",	
				"nombre":"'.cv($fDatosParticipantes["nombre"]).'", 
				"representados":	[
											'.$arrRepresentados.'
										],    
				"datosContacto":	{
										"domicilio":	{
															"calle":"'.cv($fDatosContacto["calle"]).'",
															"numeroInt":"'.cv($fDatosContacto["noInterior"]).'",  
															"numeroExt":"'.cv($fDatosContacto["noExt"]).'", 
															"colonia":"'.cv($fDatosContacto["colonia"]).'",	
															"cp":"'.cv($fDatosContacto["codigoPostal"]).'",	
															"estado":"'.cv($fDatosContacto["entidadFederativa"]).'", 
															"municipio":"'.cv($fDatosContacto["municipio"]).'", 
															"localidad":"'.cv($fDatosContacto["localidad"]).'" 
														},
										"telefono":	[
														'.$arrTelefonos.'
													],
										"email":	[
														'.$arrMail.'
													]
									}          
		
			}';
		
		
		if($arrRepresentantes=="")
			$arrRepresentantes=$o;
		else
			$arrRepresentantes.=",".$o;
	}
	
	$arrTestigos="";
	$consulta="SELECT idParticipante FROM 7005_relacionFigurasJuridicasSolicitud WHERE idActividad=".$fDatosSolicitud[1]." and idFiguraJuridica=7";
	$resParticipante=$con->obtenerFilas($consulta);
	while($fParticipante=mysql_fetch_row($resParticipante))
	{	
		$consulta="SELECT * FROM _47_tablaDinamica WHERE id__47_tablaDinamica=".$fParticipante[0];
		$fDatosParticipantes=$con->obtenerPrimeraFilaAsoc($consulta);
		
		$consulta="SELECT * FROM _48_tablaDinamica WHERE idReferencia=".$fParticipante[0];
		$fDatosContacto=$con->obtenerPrimeraFilaAsoc($consulta);
		
		$arrTelefonos="";
		$consulta="SELECT tipoTelefono,lada,numero,extension FROM _48_telefonos WHERE idReferencia=".$fDatosContacto["id__48_tablaDinamica"];
		$rTelefono=$con->obtenerFilas($consulta);
		while($fTelefono=mysql_fetch_row($rTelefono))
		{
			$o='{
					"lada":"'.$fTelefono[1].'", 
					"numero":"'.$fTelefono[2].'", 
					"extension":"'.$fTelefono[3].'"
				}';
		
			if($arrTelefonos=="")
				$arrTelefonos=$o;
			else
				$arrTelefonos.=",".$o;
		
		}
		
		$arrMail="";
		$consulta="SELECT correo FROM _48_correosElectronico WHERE idReferencia=".$fDatosContacto["id__48_tablaDinamica"];
		$rMail=$con->obtenerFilas($consulta);
		while($fMail=mysql_fetch_row($rMail))
		{
			$o='{
						"email":"'.$fMail[0].'"
					}';
			if($arrMail=="")
				$arrMail=$o;
			else
				$arrMail.=",".$o;
		}
		
		$o='{
				"idTestigo":"'.$fParticipante[0].'",
				"apPaterno":"'.cv($fDatosParticipantes["apellidoPaterno"]).'",  
				"apMaterno":"'.cv($fDatosParticipantes["apellidoMaterno"]).'",	
				"nombre":"'.cv($fDatosParticipantes["nombre"]).'", 
				"datosContacto":	{
										"domicilio":	{
															"calle":"'.cv($fDatosContacto["calle"]).'",
															"numeroInt":"'.cv($fDatosContacto["noInterior"]).'",  
															"numeroExt":"'.cv($fDatosContacto["noExt"]).'", 
															"colonia":"'.cv($fDatosContacto["colonia"]).'",	
															"cp":"'.cv($fDatosContacto["codigoPostal"]).'",	
															"estado":"'.cv($fDatosContacto["entidadFederativa"]).'", 
															"municipio":"'.cv($fDatosContacto["municipio"]).'", 
															"localidad":"'.cv($fDatosContacto["localidad"]).'" 
														},
										"telefono":	[
														'.$arrTelefonos.'
													],
										"email":	[
														'.$arrMail.'
													]
									}          
		
			}';
		
		
		if($arrTestigos=="")
			$arrTestigos=$o;
		else
			$arrTestigos.=",".$o;
	}
	
	$cadJSON='{
				  "carpetaAdministrativa":"'.$cAdministrativa.'",
				  "carpetaInvestigacion":"'.$folioCarpetaInv.'",
				  "idEventoAudiencia":"'.$idEvento.'", 
				  "fechaAudienciaMedida":"'.$fRegistro["fechaEvento"].'",
				  "horaInicioAudienciaMedida":"'.date("H:i:s",strtotime($fRegistro["horaInicioEvento"])).'", 
				  "juezAudienciaMedida":	{
											  "idJuez":"'.$fJuez["idJuez"].'",
											  "noJuez":"'.$noJuez.'",
											  "apPaterno":"'.cv($fDatosJuez["Paterno"]).'",
											  "apMaterno":"'.cv($fDatosJuez["Materno"]).'",
											  "nombre": "'.cv($fDatosJuez["Nom"]).'"
										  	},
				  "imputados":	[
								  '.$arrImputados.'
							  	],
							  
				  "victimas":	[
								 '.$arrVictimas.'
							  ] ,
				  "defensores":	[
								  '.$arrDefensores.'
							  ] ,
							  
				  "asesores":	[
								'.$arrAsesores.'  
							  ] ,                
							  
				  "representatesLegales":	[
											'.$arrRepresentantes.'
											] ,
				
				
				  "testigos":	[
								  '.$arrTestigos.'
							 ]  
				
			  }   ';
	
	return $cadJSON;
	
}

function obtenerCarpetasVinculadas($carpeta,$tipoCarpetas=1)
{
	global $con;
	$cAdministrativaBase=obtenerCarpetaBaseOriginal($carpeta);
	
	$listaCarpetas="'".$cAdministrativaBase."'";
	$lCarpetasHijas=obtenerCarpetasDerivadas($cAdministrativaBase,$tipoCarpetas)	;
	if($lCarpetasHijas!="")
	{
		$listaCarpetas.=",".$lCarpetasHijas;
	}
	
	return $listaCarpetas;
}

function obtenerCarpetaBaseOriginal($carpeta)
{
	global $con;
	$consulta="SELECT carpetaAdministrativaBase FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$carpeta."'";
	$cAdministrativa=$con->obtenerValor($consulta);
	if($cAdministrativa=="")
		return $carpeta;
	return obtenerCarpetaBaseOriginal($cAdministrativa);
}

function obtenerCarpetasAntecesoras($carpeta)
{
	global $con;
	$carpetas=$carpeta;
	$consulta="SELECT carpetaAdministrativaBase FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$carpeta."'";
	$cBase=$con->obtenerValor($consulta);
	while($cBase!="")
	{
		$carpetas.=",".$cBase;
		$consulta="SELECT carpetaAdministrativaBase FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$cBase."'";
		$cBase=$con->obtenerValor($consulta);
	}
	
	return explode(",",$carpetas);
}

function obtenerCarpetasDerivadas($carpeta,$tipoCarpeta=-1)
{
	global $con;
	
	$arrCarpetas=array();
	$consulta="SELECT carpetaAdministrativa FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativaBase='".$carpeta."'";
	if($tipoCarpeta!=-1)
		$consulta.=" and tipoCarpetaAdministrativa in (".$tipoCarpeta.")";
	$resCarpeta=$con->obtenerFilas($consulta);
	while($filaCarpeta=mysql_fetch_row($resCarpeta))
	{
		$arrCarpetas[$filaCarpeta[0]]=1;
	}
	
	
	$listaCarpetas="";
	
	foreach($arrCarpetas as $cAdministrativa=>$resto)
	{
		if($listaCarpetas=="")
			$listaCarpetas="'".$cAdministrativa."'";
		else
			$listaCarpetas.=",'".$cAdministrativa."'";
			
		$lCarpetasHijas=obtenerCarpetasDerivadas($cAdministrativa,$tipoCarpeta)	;
		if($lCarpetasHijas!="")
		{
			$listaCarpetas.=",".$lCarpetasHijas;
		}
			
	}
	
	return $listaCarpetas;
}

function registrarJuecesSolicitudAmparo($idRegistro,$cJueces)
{
	global $con;
	$cJueces=json_decode(bD($cJueces));
	
	$x=0;
	$query[$x]="begin";
	$x++;
	$query[$x]="DELETE FROM _346_juecesSolicitadosAmparo WHERE iFormulario=346 AND iRegistro=".$idRegistro;
	$x++;	
	
	foreach($cJueces->jueces as $j)
	{
		$consulta="SELECT clave FROM _26_tablaDinamica WHERE usuarioJuez=".$j->idJuez;
		$noJuez=$con->obtenerValor($consulta);
		$query[$x]="INSERT INTO _346_juecesSolicitadosAmparo(iFormulario,iRegistro,idJuez,noJuez) VALUES(346,".$idRegistro.",".$j->idJuez.",'".$noJuez."')";
		$x++;
	}
	
	$query[$x]="commit";
	$x++;
	
	return $con->ejecutarBloque($query);
	
}

function generarFolioCarpetaAmparo($idFormulario,$idRegistro)
{
	
	global $con;
	$carpetaInvestigacion="";
	$consulta="SELECT * FROM _346_tablaDinamica WHERE id__346_tablaDinamica=".$idRegistro;
	$fRegistro=$con->obtenerPrimeraFilaAsoc($consulta);
	if($fRegistro["carpetaAmparo"]!="")
		return true;
	$codigoInstitucion=$fRegistro["codigoInstitucion"];
	
	$idUnidadGestion=-1;
	$consulta="SELECT id__17_tablaDinamica FROM _17_tablaDinamica WHERE claveUnidad='".$codigoInstitucion."'";
	$idUnidadGestion=$con->obtenerValor($consulta);
	if($idUnidadGestion=="")
		$idUnidadGestion=-1;
		
	$anio=date("Y");

	$etapaProcesal=1;
	$idActividad=$fRegistro["idActividadCarpetaAmparo"];	

	if(($fRegistro["carpetaAdministrativa"]!="")&&(($fRegistro["carpetaAdministrativa"]!="-1")))	
	{
		$consulta="SELECT idActividad,etapaProcesalActual FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$fRegistro["carpetaAdministrativa"]."'";	
		$fCarpetaBase=$con->obtenerPrimeraFila($consulta);
		if($idActividad=="")
			$idActividad=$fCarpetaBase[0];
		$etapaProcesal=$fCarpetaBase[1];
	}
			
	if($idActividad=="")
		$idActividad=-1;			
			
	$query="SELECT claveUnidad,claveFolioCarpetas,siglasClave FROM _17_tablaDinamica WHERE id__17_tablaDinamica=".$idUnidadGestion;

	$fDatosUnidadBase=$con->obtenerPrimeraFila($query);
	$cveUnidadGestion=$fDatosUnidadBase[0];
	$carpetaAdministrativa=	obtenerSiguienteCarpetaAdministrativa($idUnidadGestion,$anio,3,$idFormulario,$idRegistro);
	
	$consulta=array();
	$x=0;
	$consulta[$x]="begin";
	$x++;
	$consulta[$x]="INSERT INTO 7006_carpetasAdministrativas(carpetaAdministrativa,fechaCreacion,responsableCreacion,
					idFormulario,idRegistro,unidadGestion,etapaProcesalActual,idActividad,carpetaAdministrativaBase,
					tipoCarpetaAdministrativa,carpetaInvestigacion,llaveCarpetaInvestigacion,unidadGestionOriginal) 
					VALUES('".$carpetaAdministrativa."','".date("Y-m-d H:i:s")."',".$_SESSION["idUsr"].",".$idFormulario.
					",'".$idRegistro."','".$cveUnidadGestion."',".$etapaProcesal.",".$idActividad.",'".$fRegistro["carpetaAdministrativa"]."',3,(SELECT UPPER('".
					$carpetaInvestigacion."')),'".cv(generarLlaveCarpetaInvestigacion($carpetaInvestigacion))."','".$cveUnidadGestion."')";
	$x++;
	
	
	
	$consulta[$x]="update _".$idFormulario."_tablaDinamica set carpetaAmparo='".$carpetaAdministrativa."' where id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$x++;
	
	$numEtapaPromocion=2;
	if($fRegistro["categoriaAmparo"]==2)
		$numEtapaPromocion=1.8;
	
	$consulta[$x]="INSERT INTO _460_tablaDinamica(idReferencia,fechaCreacion,responsable,idEstado,codigoInstitucion,fechaRecepcion,
					horaRecepcion,carpetaAdministrativa,tipoPromocion,espeifique,comentariosAdicionales,fundamentoInformePrevio,
					fundamentoInformeJustificado,fechaBaseInforme,horaBaseInforme,lblDiaHoraPlazoInforme,diasPlazo,horas,idProcesoPadre,
					resolverAmparoTransitorio)
					SELECT idReferencia,'".date("Y-m-d H:i:s")."',responsable,1,'".$cveUnidadGestion."','".$fRegistro["fechaRecepcion"].
					"','".$fRegistro["horaRecepcion"]."','".$carpetaAdministrativa."',tipoPromocion,espeifique,comentariosAdicionales,
					fundamentoInformePrevio,fundamentoInformeJustificado, fechaBaseInforme,horaBaseInforme,lblDiaHoraPlazoInforme,
					diasPlazo,horas,164,".($fRegistro["categoriaAmparo"]==2?1:0)." FROM _535_tablaDinamica WHERE idReferencia=".$idRegistro;
	$x++;
	$consulta[$x]="set @idRegistroPromocion:=(select last_insert_id())";
	$x++;
	$consulta[$x]="INSERT INTO _460_juezReferido(idPadre,idOpcion)
					SELECT @idRegistroPromocion,idOpcion FROM _346_juecesAmparo WHERE idPadre=".$idRegistro;
	$x++;
	
	
	$consulta[$x]="INSERT INTO 9074_documentosRegistrosProceso(idFormulario,idRegistro,idDocumento,tipoDocumento)
					SELECT 460,@idRegistroPromocion,idDocumento,tipoDocumento from 9074_documentosRegistrosProceso 
					WHERE idFormulario=346 AND idRegistro=".$idRegistro;
	$x++;
	$consulta[$x]="commit";
	$x++;
	
	if($con->ejecutarBloque($consulta))
	{
		$query="select @idRegistroPromocion";
		$idRegistroPromocion=$con->obtenerValor($query);

		
		asignarFolioRegistro(460,$idRegistroPromocion);
			
		cambiarEtapaFormulario(460,$idRegistroPromocion,$numEtapaPromocion,"",-1,"NULL","NULL",829);
		$query="SELECT idDocumento FROM 9074_documentosRegistrosProceso WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro;
		$rDocumentos=$con->obtenerFilas($query);
		while($fDocumento=mysql_fetch_row($rDocumentos))
		{
			
			registrarDocumentoCarpetaAdministrativa($carpetaAdministrativa,$fDocumento[0],$idFormulario,$idRegistro);	
		}
		
		return true;
		
	}
		
	return false;
	
}

function determinarRutaAtencionExhorto($idFormulario,$idRegistro)
{
	global $con;
	
	$consulta="SELECT carpetaExhorto FROM _92_tablaDinamica WHERE id__92_tablaDinamica=".$idRegistro;
	$fExhorto=$con->obtenerPrimeraFila($consulta);
	$consulta="SELECT dictamenFinal,id__370_tablaDinamica FROM _370_tablaDinamica WHERE idReferencia=".$idRegistro;
	$fAtencion=$con->obtenerPrimeraFila($consulta);

	$cumplePeticion=$fAtencion[0];
	$numEtapa=3;
	if($cumplePeticion==4)
	{
		$numEtapa=4;
	}
	
	cambiarEtapaFormulario($idFormulario,$idRegistro,$numEtapa,"",-1,"NULL","NULL",717);
	$query="SELECT documento,descripcion FROM _370_documentosAsociados WHERE idReferencia=".($fAtencion[1]==""?-1:$fAtencion[1]);

	$rDocumentos=$con->obtenerFilas($query);
	while($fDocumento=mysql_fetch_row($rDocumentos))
	{
		convertirDocumentoUsuarioDocumentoResultadoProceso($fDocumento[0],-1,-1,$fDocumento[1],16);
		registrarDocumentoCarpetaAdministrativa($fExhorto[0],$fDocumento[0],$idFormulario,$idRegistro);	
	}
	
	return true;
}

function validarDocumentoAdjuntoEnvioSolicitudInicial($idFormulario,$idRegistro)
{
	global $con;
	
	
	return  validarDocumentoAdjuntoEnvioSolicitud($idFormulario,$idRegistro);
	
}


function buscarCoincidenciasCriterio($tipoCriterio,$valor,$porcentaje,$figuraJuridica=0,$idFormulario=46,$idEstado=1)
{
	
	global $con;
	$arrValoresBusqueda=explode(" ",trim($valor));
	for($x=0;$x<sizeof($arrValoresBusqueda);$x++)
	{
		$arrValoresBusqueda[$x]=normalizaToken($arrValoresBusqueda[$x]);
	}

	$aActividad=array();
	$aParticipante=array();

	switch($tipoCriterio)
	{
		case 1://Por nombre participante
		
			$valor=normalizarEspaciosFrase($valor);
			$expresion=str_replace(" ","|",$valor);
			$listaActividad=array();
			$consulta="SELECT DISTINCT r.idActividad,apellidoPaterno, apellidoMaterno,nombre,id__47_tablaDinamica
						FROM _47_tablaDinamica t,7005_relacionFigurasJuridicasSolicitud r WHERE match(apellidoPaterno, apellidoMaterno,nombre)
						against ('".$valor."') and r.idParticipante=t.id__47_tablaDinamica";
						
			if($figuraJuridica!=0)			
			{
				$consulta.=" and r.idFiguraJuridica in (".$figuraJuridica.")";
			}
			
			$rResultados=$con->obtenerFilas($consulta);
			while($fila=mysql_fetch_row($rResultados))
			{
				if(!isset($listaActividad[$fila[0]]))
					$listaActividad[$fila[0]]=array();
				array_push($listaActividad[$fila[0]],$fila);
			}
		
			
			$lActividad="";
			
			foreach($listaActividad as $iActividad=>$resto)
			{
				
				foreach($resto as $fRegistro)
				{
					
					$porcenjateSimilitud=0;
					$porcenjateSimilitud=similitudPalabras(mb_strtoupper(trim($fRegistro[1]))." ".mb_strtoupper(trim($fRegistro[2])).
										" ".mb_strtoupper(trim($fRegistro[3])),mb_strtoupper(trim($valor)));
					
					
					if($porcenjateSimilitud>=$porcentaje)
					{
						if(isset($aActividad[$iActividad]))
						{
							if($aActividad[$iActividad]<$porcenjateSimilitud)
								$aActividad[$iActividad]=$porcenjateSimilitud;
						}
						else
							$aActividad[$iActividad]=$porcenjateSimilitud;
							
							
							
						$aParticipante[$iActividad]=$fRegistro[4];
					}
					
					
				}
			}
					
			
			foreach($aActividad as $iActividad=>$resto)
			{
				if($iActividad=="")
					continue;
				if($lActividad=="")
					$lActividad=$iActividad;
				else
					$lActividad.=",".$iActividad;
			}
			
			if($lActividad=="")
				$lActividad=-1;
		
			$consulta="SELECT * FROM _".$idFormulario."_tablaDinamica t WHERE idEstado>".$idEstado." and idActividad in
						(
						".$lActividad."
						)";
		
		break;
		case 2://Por carpeta de investigacion
			$consulta="SELECT * FROM _46_tablaDinamica WHERE idEstado>1 and folioCarpetaInvestigacion LIKE '%".$valor."%'";
		break;
		case 3:
			$valor=normalizarEspaciosFrase($valor);
			$expresion=str_replace(" ","|",$valor);
			$listaActividad=array();
			$consulta="SELECT DISTINCT idActividad,apellidoPaterno, apellidoMaterno,nombre
						FROM _47_tablaDinamica WHERE match(apellidoPaterno, apellidoMaterno,nombre)
						against ('".$valor."')
						union
						SELECT DISTINCT t.idActividad,'', '',a.alias
						FROM _47_tablaDinamica t,_47_gridAlias a WHERE MATCH(a.alias)
						AGAINST ('".$valor."') AND a.idReferencia=t.id__47_tablaDinamica
						
							";
						
			if($figuraJuridica!=0)			
			{
				$consulta="SELECT DISTINCT t.idActividad,apellidoPaterno, apellidoMaterno,nombre,id__47_tablaDinamica
						FROM _47_tablaDinamica t,7005_relacionFigurasJuridicasSolicitud r WHERE match(apellidoPaterno, apellidoMaterno,nombre)
						against ('".$valor."') and r.idActividad=t.idActividad and r.idFiguraJuridica in (".$figuraJuridica.")
						union
						SELECT DISTINCT t.idActividad,'', '',a.alias,id__47_tablaDinamica
						FROM _47_tablaDinamica t,_47_gridAlias a,7005_relacionFigurasJuridicasSolicitud r WHERE MATCH(a.alias)
						AGAINST ('".$valor."') AND a.idReferencia=t.id__47_tablaDinamica and r.idActividad=t.idActividad and 
						r.idFiguraJuridica in (".$figuraJuridica.")
						
						";
			}
						
			$rResultados=$con->obtenerFilas($consulta);
			while($fila=mysql_fetch_row($rResultados))
			{
				if(!isset($listaActividad[$fila[0]]))
					$listaActividad[$fila[0]]=array();
				array_push($listaActividad[$fila[0]],$fila);
			}
		
			
			$lActividad="";
			
			foreach($listaActividad as $iActividad=>$resto)
			{
				
				foreach($resto as $fRegistro)
				{
					$porcenjateSimilitud=0;
					$palabraBase=trim(mb_strtoupper(trim($fRegistro[1]))." ".mb_strtoupper(trim($fRegistro[2]))." ".mb_strtoupper(trim($fRegistro[3])));

					$porcenjateSimilitud=similitudPalabras($palabraBase,mb_strtoupper(trim($valor)));
					

					if($porcenjateSimilitud>=$porcentaje)
					{
						
						if(isset($aActividad[$fRegistro[4]]))
						{
							if($aActividad[$fRegistro[4]]<$porcenjateSimilitud)
								$aActividad[$fRegistro[4]]=$porcenjateSimilitud;
						}
						else
							$aActividad[$fRegistro[4]]=$porcenjateSimilitud;
						

					}
					
					
				}
			}
					
			
			
		
			$consulta="select 1";
		break;
	}
	
	$res=$con->obtenerFilas($consulta);
	
	$arrResultado=array();
	$arrResultado[0]=$res;
	$arrResultado[1]=$aActividad;
	$arrResultado[2]=$aParticipante;
	
	return $arrResultado;
}

function aplicarAjusteDocumentosAmparoJuez($idFormulario,$idRegistro)
{
	global $con;
	
	$consulta="SELECT COUNT(*) FROM _346_chkNoRefiereCarpetaJudicial WHERE idPadre=".$idRegistro;
	$refiereCarpeta=$con->obtenerFilas($consulta);
	
	
	
	if($refiereCarpeta==1)
		return true;

	$consulta="SELECT * FROM _346_tablaDinamica WHERE id__346_tablaDinamica=".$idRegistro;
	$fDatosSolicitud=$con->obtenerPrimeraFilaAsoc($consulta);

	$x=0;
	$query[$x]="begin";
	$x++;
	$query[$x]="UPDATE _346_juecesSolicitadosAmparo SET tieneConocimiento=1,carpetaConocimiento='".$fDatosSolicitud["carpetaAdministrativa"].
			"',idImputadoConocimiento='".$fDatosSolicitud["quejoso"]." WHERE iFormulario=".$idFormulario." AND iRegistro=".$idRegistro;
	$x++;
	
	$query[$x]="commit";
	$x++;
	
	if($con->ejecutarBloque($query))
	{
		$consulta="SELECT count(*) FROM _346_juecesSolicitadosAmparo WHERE iFormulario=".$idFormulario." AND iRegistro=".$idRegistro;
		$nReg=$con->obtenerValor($consulta);		
		if($nReg>1)
		{
			
		}
		
	}
	
}

function generarDocumentosSolicitudesAmparo($idFormulario,$idRegistro)
{
	global $con;
	$consulta="SELECT carpetaAmparo,
			(SELECT COUNT(*) FROM _346_chkInfomePreveio WHERE idPadre=id__346_tablaDinamica) as informePrevio,
			( SELECT COUNT(*) FROM _346_chkInformeJustificado WHERE idPadre=id__346_tablaDinamica) as informeJustificado 
			FROM _346_tablaDinamica WHERE id__346_tablaDinamica=".$idRegistro;
			
	$fRegistroSolicitud=$con->obtenerPrimeraFila($consulta);
	$carpetaAdministrativa=$fRegistroSolicitud[0];
	$x=0;
	$query[$x]="begin";
	$x++;
	
	$query[$x]="DELETE FROM _363_tablaDinamica WHERE iFormulario=".$idFormulario." AND iRegistro=".$idRegistro." AND idEstado=1";
	$x++;
	
	
	$query[$x]="commit";
	$x++;
	
	if($con->ejecutarBloque($query))
	{
	
		/*
		 211   Informe previo
	 
		 212   Informe justificado
		 
		 213    Informe de NO conocimiento
		*/
		
		$consulta="SELECT idJuez,noJuez,u.Nombre,j.tieneConocimiento,j.carpetaConocimiento,j.idImputadoConocimiento 
				FROM _346_juecesSolicitadosAmparo j,800_usuarios u WHERE 
				iFormulario=".$idFormulario." AND iRegistro=".$idRegistro." and u.idUsuario=j.idJuez order by j.noJuez";
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			
			$tipoDocumento=213;
			
			if($fila[3]==1)
			{
				if(($fRegistroSolicitud[1]>0)&&($fRegistroSolicitud[2]>0))
				{
					$tipoDocumento=211;
					$arrValores=array();
					$arrDocumentosReferencia=NULL;
					$arrValores["carpetaAdministrativa"]=$carpetaAdministrativa;
					$arrValores["tipoDocumento"]=$tipoDocumento;
					$arrValores["iFormulario"]=$idFormulario;
					$arrValores["iRegistro"]=$idRegistro;
					$arrValores["idJuez"]=$fila[0];
					$arrValores["noJuez"]=$fila[1];
					$arrValores["carpetaConocimiento"]=$fila[4];
					$arrValores["idImputadoConocimiento"]=$fila[5];					
					
					crearInstanciaRegistroFormulario(363,-1,1,$arrValores,$arrDocumentosReferencia,-1,708);
					
					$tipoDocumento=212;
				}
				else
				{
					if($fRegistroSolicitud[1]>0)
						$tipoDocumento=211;
					else
						$tipoDocumento=212;
				}
			}
			
			$arrValores=array();
			$arrDocumentosReferencia=NULL;
			$arrValores["carpetaAdministrativa"]=$carpetaAdministrativa;
			$arrValores["tipoDocumento"]=$tipoDocumento;
			$arrValores["iFormulario"]=$idFormulario;
			$arrValores["iRegistro"]=$idRegistro;
			$arrValores["idJuez"]=$fila[0];
			$arrValores["noJuez"]=$fila[1];
			$arrValores["carpetaConocimiento"]=$fila[4];
			$arrValores["idImputadoConocimiento"]=$fila[5];	
			
			
			crearInstanciaRegistroFormulario(363,-1,1,$arrValores,$arrDocumentosReferencia,-1,708);	
		}
		
		
		return true;
	}
}

function obtenerTipoDocumentoAmparo($idFormulario,$idRegistro)
{
	global $con;
	
	$consulta="SELECT tipoDocumento FROM _363_tablaDinamica WHERE id__363_tablaDinamica=".$idRegistro;
	
	$tipoDocumento=$con->obtenerValor($consulta);
	
	return $tipoDocumento;
	
	
}

function determinarRutaSolicitudAmparo1_5($idFormulario,$idRegistro)
{
	global $con;
	
	$etapaContinuacion=1;
	
	
	$consulta="SELECT carpetaAmparo,
			(SELECT COUNT(*) FROM _346_chkInfomePreveio WHERE idPadre=id__346_tablaDinamica) as informePrevio,
			( SELECT COUNT(*) FROM _346_chkInformeJustificado WHERE idPadre=id__346_tablaDinamica) as informeJustificado 
			FROM _346_tablaDinamica WHERE id__346_tablaDinamica=".$idRegistro;
			
	$fRegistroSolicitud=$con->obtenerPrimeraFila($consulta);
	
	if($fRegistroSolicitud[1]==1)
	{
		$etapaContinuacion=2;
	}
	else
	{
		if($fRegistroSolicitud[2]==1)
		{
			$etapaContinuacion=3;
		}
	}
	cambiarEtapaFormulario($idFormulario,$idRegistro,$etapaContinuacion,"",-1,"NULL","NULL",719);
		
}

function determinarRutaSolicitudAmparo3_3($idFormulario,$idRegistro)
{
	global $con;
	
	$etapaContinuacion=1;
	
	
	$consulta="SELECT carpetaAmparo,
			(SELECT COUNT(*) FROM _346_chkInfomePreveio WHERE idPadre=id__346_tablaDinamica) as informePrevio,
			( SELECT COUNT(*) FROM _346_chkInformeJustificado WHERE idPadre=id__346_tablaDinamica) as informeJustificado 
			FROM _346_tablaDinamica WHERE id__346_tablaDinamica=".$idRegistro;
			
	$fRegistroSolicitud=$con->obtenerPrimeraFila($consulta);
	
	/*if($fRegistroSolicitud[1]==1)
	{
		$etapaContinuacion=2;
	}
	else
	{
		if($fRegistroSolicitud[2]==1)
		{
			$etapaContinuacion=3;
		}
	}*/
	
	
	$consulta="SELECT etapaAnterior FROM 941_bitacoraEtapasFormularios WHERE 
			idFormulario=".$idFormulario." AND idRegistro=".$idRegistro." AND etapaActual=3.3 
			ORDER BY idRegistroEstado DESC";
	
	$etapaAnterior=$con->obtenerValor($consulta);
	
	$documentoAuto="-1";
	
	$tipoDocumento="-1";
	switch($etapaAnterior)
	{
		case "2":	
			
			if($fRegistroSolicitud[2]==1)
				$etapaContinuacion=3;
			else
				$etapaContinuacion=3.5;

			$tipoDocumento.=",".$documentoAuto.",211,213";
		break;		
		case "3":
			if($fRegistroSolicitud[1]==1)
			{
				$tipoDocumento.=",212";
			}
			else
			{
				$tipoDocumento.=",".$documentoAuto.",212,213";
			}
			$etapaContinuacion=3.5;
			
		break;
	}
	$etapaContinuacion=3.5;
	//generarOrdenNotificacionAmparo($idFormulario,$idRegistro,$tipoDocumento);
	cambiarEtapaFormulario($idFormulario,$idRegistro,$etapaContinuacion,"",-1,"NULL","NULL",719);
		
}

function determinarRutaSolicitudAmparo3_7($idFormulario,$idRegistro)
{
	global $con;
	
	$etapaContinuacion=0;
	$consulta="SELECT concedeAmparo FROM _371_tablaDinamica WHERE id__371_tablaDinamica=".$idRegistro;
	$fDatosRegistro=$con->obtenerPrimeraFila($consulta);
	
	if($fDatosRegistro[0]==1)
		$etapaContinuacion=4;
	else
		$etapaContinuacion=5;	
	
	cambiarEtapaFormulario($idFormulario,$idRegistro,$etapaContinuacion,"",-1,"NULL","NULL",719);
		
}

function insertarDocumentoAutoEjecutoriaAmparo($idRegistro)//recepcion exhorto
{
	global $con;
	$fechaA=date("Y-m-d");
	
	$borrar="DELETE FROM _123_tablaDinamica WHERE iFormulario='346' AND iRegistro='".$idRegistro."'";
	$con->ejecutarConsulta($borrar);
	
	$consulDatos="SELECT fechaCreacion,responsable,codigoInstitucion FROM _346_tablaDinamica WHERE id__346_tablaDinamica='".$idRegistro."'";
	$res=$con->obtenerPrimeraFila($consulDatos);
	
	$idEstado=1;
	$codigoUnidad=$res[2];
	
	$x=0;
	$consulta[$x]="begin";
	$x++;
	
	$consulta[$x]="INSERT INTO _123_tablaDinamica(fechaCreacion,responsable,idEstado,codigoInstitucion,idPersona,tipoFigura,tipoDocumento,
				iFormulario,iRegistro) VALUE('".date("Y-m-d H:i:s")."','".$_SESSION["idUsuario"]."','".$idEstado."','".
				$codigoUnidad."','0','0','".$tipoDocumento."','346','".$idRegistro."')";
	$x++;
	
	$consulta[$x]="commit";
	$con->ejecutarBloque($consulta);
}

function generarOrdenNotificacionAmparo($idFormulario,$idRegistro,$tipoDocumentos)
{
	global $con;
		
	$consulta="SELECT idDocumento FROM 9074_documentosRegistrosProceso WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro.
			" AND tipoDocumento=2";
	$rDocumentos=$con->obtenerFilas($consulta);
	
	$consulta="select carpetaAdministrativa,iFormulario,iRegistro,tipoDocumento from _".$idFormulario."_tablaDinamica 
			where id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$fDatosSolicitud=$con->obtenerPrimeraFila($consulta);
	
	
	while($fDocumento=mysql_fetch_row($rDocumentos))
	{
		registrarDocumentoResultadoProceso($fDatosSolicitud[1],$fDatosSolicitud[2],$fDocumento[0]);
	}
	
	if($fDatosSolicitud[3]==9)
	{
		
		return true;
	}
	
	$carpetaAdministrativa=$fDatosSolicitud[0];
	
	
	
	$idEventoAudiencia=-1;
	
	$arrValores=array();
	$arrValores["idEvento"]=$idEventoAudiencia;
	$arrValores["tipoSolicitud"]=($idEventoAudiencia!=-1)?1:2;
	
	if($arrValores["tipoSolicitud"]==1)
	{
		$idFormulario=$fDatosSolicitud[1];
		$idRegistro=$fDatosSolicitud[2];
	}
	$arrValores["carpetaAdministrativa"]=$carpetaAdministrativa;
	$arrValores["idFormularioPadre"]=$idFormulario;
	
	$arrDocumentos=array();
	$consulta="SELECT idDocumento FROM 9074_documentosRegistrosProceso WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro." AND tipoDocumento=2";
	$rDocumentos=$con->obtenerFilas($consulta);
	while($fDocumento=mysql_fetch_row($rDocumentos))
	{
		array_push($arrDocumentos,$fDocumento[0]);
	}
	$idRegistroSolicitudCitacion=crearInstanciaRegistroFormulario(67,$idRegistro,1,$arrValores,$arrDocumentos,-1,315);
	
	return true;
	
	
	
}


function esActorPermisosGeneracionInformesAmparo($actor)
{
	global $con;
	$consulta="SELECT actor FROM 944_actoresProcesoEtapa WHERE idActorProcesoEtapa=".$actor;
	$rol=$con->obtenerValor($consulta);
	

	if(($rol=='110_0')||($rol=='19_0')||($rol=='63_0')||($rol=='39_0'))
	{
		return "'1'";
	}
	
	return "'0'";
}

function esActorPermisosGeneracionDocumentosSolicitudAudiencia($actor)
{
	global $con;
	$consulta="SELECT actor FROM 944_actoresProcesoEtapa WHERE idActorProcesoEtapa=".$actor;
	$rol=$con->obtenerValor($consulta);
	

	if(($rol=='36_0')||($rol=='19_0'))
	{
		return "'1'";
	}
	
	return "'0'";
}

function asignarCarpetaGuardia($carpetaAdministrativa,$fechaRegistro)
{
	global $con;
	$tipoHorario=1;
	$consulta="SELECT unidadGestion FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$carpetaAdministrativa."'";
	$unidad=$con->obtenerValor($consulta);
	
	
	$tipoDelito="";
	
	switch($unidad)
	{
		case "001":
		case "005":
		case "003":
		case "004":
			$tipoDelito="A";
			$tipoHorario=determinarHorarioA($fechaRegistro);
			//$tipoHorario=2;
			if(strtotime(date("Y-m-d H:i:s"))<strtotime("2017-11-20 13:30"))
			{
				$tipoHorario=2;
				return "003";
			}
		break;
		case "002":
		
		case "006":
		case "007":
		case "008":
		case "209":
		case "010":
		case "011":
			$tipoDelito="B";
			$tipoHorario=determinarHorarioB($fechaRegistro);
			//$tipoHorario=2;
			if(strtotime(date("Y-m-d H:i:s"))<strtotime("2017-11-20 13:30"))
			{
				$tipoHorario=2;
				return "007";
			}
		break;
	}
	
	if($tipoHorario==2)
	{
		$consulta="SELECT idReferencia FROM _17_gridDelitosAtiende WHERE tipoDelito='".$tipoDelito."' and idReferencia in 
						(SELECT unidadGestion FROM _290_tablaDinamica WHERE '".date("Y-m-d",strtotime($fechaRegistro)).
								"'>=fechaInicial AND '".date("Y-m-d",strtotime($fechaRegistro))."'<=fechaFinal)";
		$idUnidadGestion=$con->obtenerValor($consulta);
		
		if($idUnidadGestion=="")
			return -1;
		else
		{
			$consulta="SELECT claveUnidad FROM _17_tablaDinamica WHERE id__17_tablaDinamica=".$idUnidadGestion;
			$unidadGuardia=$con->obtenerValor($consulta);	
			if($unidad==$unidadGuardia)
				return -1;
			return $unidadGuardia;
		}
		
	}
	
	return -1;
	
	
}

function asignarCarpetaUnidadGestionGuardia($carpeta,$unidadGestion)
{
	global $con;
	$consulta="SELECT unidadGestion FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$carpeta."'";
	$unidadOrigen=$con->obtenerValor($consulta);
	
	$x=0;
	$query[$x]="begin";
	$x++;
	$query[$x]="UPDATE 7006_carpetasAdministrativas SET unidadGestion='".$unidadGestion."' WHERE carpetaAdministrativa='".$carpeta."'";
	$x++;
	$query[$x]="INSERT INTO 7021_carpetasAsignadasGuardia(carpetaAdministrativa,unidadGestionOrigen,unidadGestionGuardia,situacion,fechaAsignacion) 
			VALUES('".$carpeta."','".$unidadOrigen."','".$unidadGestion."',1,'".date("Y-m-d H:i:s")."')";
	$x++;
	$query[$x]="commit";
	$x++;
	return $con->ejecutarBloque($query);
	
}

function obtenerUnidadGestionDestinatariaDocumento($idRegistro)
{
	global  $con;
	
	$consulta="SELECT asuntoPromocion FROM _96_tablaDinamica WHERE id__96_tablaDinamica=".$idRegistro;
	$asuntoPromocion=$con->obtenerValor($consulta);
	
	$arrUnidadesGestion["UNO"]=15;
	$arrUnidadesGestion["DOS"]=16;
	$arrUnidadesGestion["TRES"]=17;
	$arrUnidadesGestion["CUATRO"]=25;
	$arrUnidadesGestion["CINCO"]=32;
	$arrUnidadesGestion["SEIS"]=33;
	$arrUnidadesGestion["SIETE"]=34;
	$arrUnidadesGestion["OCHO"]=35;
	$arrUnidadesGestion["NUEVE"]=48;
	$arrUnidadesGestion["DIEZ"]=47;
	$arrUnidadesGestion["ONCE"]=46;
		
	$arrUnidadesGestion["NUMERO UNO"]=15;
	$arrUnidadesGestion["NUMERO DOS"]=16;
	$arrUnidadesGestion["NUMERO TRES"]=17;
	$arrUnidadesGestion["NUMERO CUATRO"]=25;
	$arrUnidadesGestion["NUMERO CINCO"]=32;
	$arrUnidadesGestion["NUMERO SEIS"]=33;
	$arrUnidadesGestion["NUMERO SIETE"]=34;
	$arrUnidadesGestion["NUMERO OCHO"]=35;
	$arrUnidadesGestion["NUMERO NUEVE"]=48;
	$arrUnidadesGestion["NUMERO DIEZ"]=47;
	$arrUnidadesGestion["NUMERO ONCE"]=46;
	
	$arrUnidadesGestion["1"]=15;
	$arrUnidadesGestion["2"]=16;
	$arrUnidadesGestion["3"]=17;
	$arrUnidadesGestion["4"]=25;
	$arrUnidadesGestion["5"]=32;
	$arrUnidadesGestion["6"]=33;
	$arrUnidadesGestion["7"]=34;
	$arrUnidadesGestion["8"]=35;
	$arrUnidadesGestion["9"]=48;
	$arrUnidadesGestion["10"]=47;
	$arrUnidadesGestion["11"]=46;
	
	$arrUnidadesGestion["01"]=15;
	$arrUnidadesGestion["02"]=16;
	$arrUnidadesGestion["03"]=17;
	$arrUnidadesGestion["04"]=25;
	$arrUnidadesGestion["05"]=32;
	$arrUnidadesGestion["06"]=33;
	$arrUnidadesGestion["07"]=34;
	$arrUnidadesGestion["08"]=35;
	$arrUnidadesGestion["09"]=48;
	$arrUnidadesGestion["010"]=47;
	$arrUnidadesGestion["011"]=46;
	
	$idUnidadGestion=-1;
	foreach($arrUnidadesGestion as $letraNumero=>$iCentroGestion)
	{
		
		$cadena=utf8_encode("DIRECTOR DE LA UNIDAD DE GESTI”N JUDICIAL ".$letraNumero);

		$pos=strpos($asuntoPromocion,$cadena);
		if($pos!==false)
		{
			$idUnidadGestion=$iCentroGestion;
			return $idUnidadGestion;
		}
	}
	
	
	return $idUnidadGestion;
}


function enviarCorreoWebServicesSolicitudPromocionNOIdentificada($idRegistro)
{
	global $baseDir;
	global $con;
	global $urlRepositorioDocumentos;
	$arrCopia=array();
	$arrCopiaOculta=array();
	$idFormulario=96;
	

	$arrCopia[0][0]="recepcionsolicitudespgj@hotmail.com";
	$arrCopia[0][1]="";
	
	
	$nCopias=1;
	$cuerpo="Solicitud NO identificada";
	$consulta="SELECT carpetaAdministrativa FROM _96_tablaDinamica WHERE id__96_tablaDinamica=".$idRegistro;
	$fSolicitud=$con->obtenerPrimeraFila($consulta);	
	
	
	$nPos=0;
	$titulo="Solicitud ".$fSolicitud[0]." promocion NO identificada";
	$nDocumentoSolicitud=1;
	$consulta="SELECT idDocumento FROM 9074_documentosRegistrosProceso WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro;
	$rDocumentos=$con->obtenerFilas($consulta);
	while($fDocumento=mysql_fetch_row($rDocumentos))
	{
		$consulta="SELECT nomArchivoOriginal FROM 908_archivos WHERE idArchivo=".$fDocumento[0];
		$nomArchivoOriginal=$con->obtenerValor($consulta);
		$aNombreArchivo=explode(".",$nomArchivoOriginal);
		$extension=$aNombreArchivo[sizeof($aNombreArchivo)-1];
		$directorioDestino=obtenerRutaDocumento($fDocumento[0]);
		$arrArchivos[$nPos][0]=$directorioDestino;
		$arrArchivos[$nPos][1]="Solicitud Promocion documento_".$nDocumentoSolicitud.".".$extension;
		$nPos++;
		$nDocumentoSolicitud++;
		
	}
	
	
	enviarMailGMail("notificacionesTSJCDMX@grupolatis.net",$titulo,$cuerpo,$arrArchivos,$arrCopia,$arrCopiaOculta);
	
}

function esActorResponsableModificacion($idFormulario,$idRegistro,$idActor)
{
	global $con;
	
	$consulta="SELECT actorCambio FROM 941_bitacoraEtapasFormularios WHERE idFormulario=".$idFormulario." 
			AND idRegistro=".$idRegistro." and etapaAnterior<>etapaActual ORDER BY idRegistroEstado ASC";
	
	$actorCambio=$con->obtenerValor($consulta);
	
	
	$rolDestino=$idActor;
	
			
	
	$consulta="SELECT actor FROM 944_actoresProcesoEtapa WHERE idActorProcesoEtapa=".$actorCambio;
	$rolOrigen=$con->obtenerValor($consulta);
	
	

	if($rolOrigen==$rolDestino)	
		return "'1'";	
	return "'0'";
}

function obtenerHistoriaCarpeta($cAministrativa)
{
	global $con;	
	
	$arrAudienciasInocmpetencia[91]=1;
	$arrAudienciasInocmpetencia[102]=1;
	$arrAudienciasInocmpetencia[114]=1;
	
	$arrHistorialCarpeta=array();

	$cBase=obtenerCarpetaBaseOriginal($cAministrativa);

	$consulta="SELECT idTipoCarpeta FROM 7020_tipoCarpetaAdministrativa";
	$listaCarpetas=$con->obtenerListaValores($consulta);
	$arrCarpetas=obtenerCarpetasDerivadas($cBase,$listaCarpetas);

	$arrCarpetasHistoria=array();
	$arrCarpetasHistoria[$cBase]=1;
	if($arrCarpetas!="")
	{
		$aTemp=explode(",",$arrCarpetas);
		foreach($aTemp as $t)
		{
			$cAux=str_replace("'","",$t);
			$consulta="SELECT tipoCarpetaAdministrativa FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$cAux."'";
			$arrCarpetasHistoria[$cAux]=$con->obtenerValor($consulta);
		}
	}
	

	$arrSabanaCarpeta=array();
	$arrSabanaCarpeta["carpeta"]=$cAministrativa;
	$arrSabanaCarpeta["etapaInicial"]=array();
	$arrSabanaCarpeta["etapaIntermedia"]=array();
	$arrSabanaCarpeta["etapaJuicioOral"]=array();
	$arrSabanaCarpeta["etapaEjecucion"]=array();
	
	$etapaIntermedia=false;
	foreach($arrCarpetasHistoria as $carpeta=>$resto)
	{
		$arrHistorialCarpeta[$carpeta]=array();
		
		$consulta="SELECT e.*  FROM 7007_contenidosCarpetaAdministrativa c,7000_eventosAudiencia e 
					WHERE carpetaAdministrativa='".$carpeta."' AND tipoContenido=3 AND e.idRegistroEvento=
					c.idRegistroContenidoReferencia ORDER BY fechaEvento ASC";

		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_assoc($res))
		{
			
			if(!isset($arrAudienciasInocmpetencia[$fila["tipoAudiencia"]]))
				$oResolutivo=obtenerResolutivosAudienciaHistorial($fila["idRegistroEvento"]);
			else
				$oResolutivo=obtenerInformacionAudienciaIncompetenciaHistorial($fila["idRegistroEvento"]);

			if($fila["tipoAudiencia"]==15)
			{
				$etapaIntermedia=true;
			}
			
			if(($resto==1)&&($etapaIntermedia))
			{
				$resto=2;
			}
			
			switch($resto)
			{
				case 1:  //Inicial
					array_push($arrSabanaCarpeta["etapaInicial"],$oResolutivo);
				break;
				case 2:  //Intermedia
					array_push($arrSabanaCarpeta["etapaIntermedia"],$oResolutivo);
				break;
				case 5:	//Juicio oral
					array_push($arrSabanaCarpeta["etapaJuicioOral"],$oResolutivo);
				break;
				case 6:	//enjuiciamiento
					array_push($arrSabanaCarpeta["etapaEjecucion"],$oResolutivo);
				break;
			}
			
			
			

		}
		
		
	}
	return $arrSabanaCarpeta;
}

function obtenerResolutivosAudienciaHistorial($idEvento)
{
	global $con;	
	global $arrDiasSemana;
	global $arrMesLetra;
	$arrResultado=array();
	
	$consulta="SELECT * FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$idEvento;
	$fEvento=$con->obtenerPrimeraFilaAsoc($consulta);
	$consulta="SELECT tipoAudiencia FROM _4_tablaDinamica WHERE id__4_tablaDinamica=".$fEvento["tipoAudiencia"];
	$arrResultado["tipoAudiencia"]=$con->obtenerValor($consulta);

	$dEvento=obtenerDatosEventoAudiencia($idEvento);
	
	
	$fechaEvento=strtotime($dEvento->fechaEvento!=""?$dEvento->fechaEvento:$fEvento["fechaAsignacion"]);
	$consulta="SELECT carpetaAdministrativa FROM 7007_contenidosCarpetaAdministrativa WHERE tipoContenido=3 
			AND idRegistroContenidoReferencia=".$idEvento;
	$arrResultado["carpetaJudicial"]=$con->obtenerValor($consulta);
	$arrResultado["idEventoAudiencia"]=$idEvento;
	$arrResultado["lugar"]=$dEvento->edificio;
	$arrResultado["fechaAudiencia"]=utf8_encode($arrDiasSemana[date("w",$fechaEvento)])." ".date("d",$fechaEvento)." de ".$arrMesLetra[(date("m",$fechaEvento)*1)-1]." de ".date("Y",$fechaEvento);
	$arrResultado["fechaAudienciaRaw"]=date("Y-m-d",$fechaEvento);
	$arrResultado["horaProgramada"]=date("H:i",strtotime($dEvento->horaInicio));
	$arrResultado["fechaAsignacion"]=$fEvento["fechaAsignacion"];
	$consulta="SELECT descripcionSituacion FROM 7011_situacionEventosAudiencia WHERE idSituacion=".$fEvento["situacion"];
	$arrResultado["situacion"]=$con->obtenerValor($consulta);
	$arrResultado["desarrollo"]="";
	
	
	
	if($fEvento["horaInicioReal"]!="")
	{
		$hInicioReal=strtotime($fEvento["horaInicioReal"]);
		$horaTerminoReal=strtotime($fEvento["horaTerminoReal"]);
		if(date("d/m/Y",$hInicioReal)==date("d/m/Y",$horaTerminoReal))
		{
			$fechaAudiencia=$arrDiasSemana[date("w",$hInicioReal)]." ".date("d",$hInicioReal)." de ".$arrMesLetra[(date("m",$hInicioReal)*1)-1]." de ".date("Y",$hInicioReal);
			
			$arrResultado["desarrollo"]=" De las ".date("H:i",$hInicioReal)." a las ".date("H:i",$horaTerminoReal)." hrs. del ".$fechaAudiencia;
		}
		else
		{
			$fechaAudiencia=$arrDiasSemana[date("w",$hInicioReal)]." ".date("d",$hInicioReal)." de ".$arrMesLetra[(date("m",$hInicioReal)*1)-1]." de ".date("Y",$hInicioReal);
			$fechaAudienciaFinal=$arrDiasSemana[date("w",$horaTerminoReal)]." ".date("d",$horaTerminoReal)." de ".$arrMesLetra[(date("m",$horaTerminoReal)*1)-1]." de ".date("Y",$horaTerminoReal);
			$arrResultado["desarrollo"]=" De las ".date("H:i",$hInicioReal)." hrs. del ".$fechaAudiencia." a las ".date("H:i",$horaTerminoReal)." hrs. del ".$fechaAudienciaFinal;
		}
	}
	$arrResultado["urlVideo"]="";
	if($fEvento["urlMultimedia"]!="")
	{
		$arrResultado["urlVideo"]=$fEvento["urlMultimedia"];
	}
	
	
	$arrResultado["unidadGestion"]=$dEvento->unidadGestion;
	$arrResultado["sala"]=$dEvento->sala;
	$consulta="SELECT u.nombre FROM 800_usuarios u,7001_eventoAudienciaJuez j WHERE j.idRegistroEvento=".$idEvento." AND  
			idUsuario=j.idJuez ORDER BY u.nombre";
	$arrResultado["jueces"]=$con->obtenerListaValores($consulta);
	$arrResultado["idFormulario"]=$fEvento["idFormulario"];
	$arrResultado["idRegistro"]=$fEvento["idRegistroSolicitud"];
	$arrResultado["arrDocumentos"]=array();
	
	if($arrResultado["idFormulario"]=="")
		$arrResultado["idFormulario"]=-1;
	
	if($arrResultado["idRegistro"]=="")
		$arrResultado["idRegistro"]=-1;
		
	$consulta="SELECT a.idArchivo,a.nomArchivoOriginal FROM 9074_documentosRegistrosProceso d,908_archivos a WHERE 
			idFormulario=".$arrResultado["idFormulario"]." AND idRegistro=".$arrResultado["idRegistro"]." AND a.idArchivo=d.idDocumento";
	$rDocumento=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($rDocumento))
	{
		array_push($arrResultado["arrDocumentos"],$fila);
	}
	
	
	$arrResultado["resolutivos"]=array();
	$consulta="SELECT tipoResolutivo,c.descripcionResolutivo,valor,comentariosAdicionales,c.tipoResultado FROM 3013_registroResolutivosAudiencia r,_327_tablaDinamica c 
				WHERE idEvento=".$idEvento." AND c.id__327_tablaDinamica=r.tipoResolutivo";

	$rEvento=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($rEvento))
	{
		$tipoValor=$fila[4];
		$oResolutivo=array();
		$oResolutivo["tipoResolutivo"]=$fila[0];
		$oResolutivo["descripcionResolutivo"]=$fila[1];
		$oResolutivo["valor"]="";
		
		switch($tipoValor)
		{
			case '1':			
				if($fila[2]==0)
					$oResolutivo["valor"]="No";
				else
					$oResolutivo["valor"]="S&iacute;";					
			break;
			case '2':
				if($fila[2]!='')
				{
					$oVal=json_decode($fila[2]);
					$oResolutivo["valor"]=' ('.$oVal->anios.' a&ntilde;os, '.$oVal->meses.' meses, '.$oVal->dias.' d&iacute;as):'.date("d/m/Y",strtotime($oVal->fechaFinal));
				}				
			break;
			case '3':
				return formatearValorRenderer(arrTotalAudiencia,val);		
			break;
			case '4':
				if($fila[2]!='')
				{
					$oResolutivo["valor"]=date("d/m/Y",strtotime($fila[2]));
				}
			break;
			case '5':
				$oResolutivo["valor"]=number_format($fila[2],2);

			break;
			case '6':
				$oResolutivo["valor"]=$fila[2];
			break;
			case '7':
				$oResolutivo["valor"]="N/A";	
			break;
			case '8':
				$lblImputados='';
				$aImputados=explode(",",$fila[2]);
				
				foreach($aImputados as $i)
				{
					$consulta="SELECT CONCAT(nombre,' ',apellidoPaterno,' ',apellidoMaterno)  FROM _47_tablaDinamica 
								WHERE id__47_tablaDinamica=".($i==""?-1:$i);
					$nImputado=$con->obtenerValor($consulta);
					if($lblImputados=='')
						$lblImputados=$nImputado;
					else
						$lblImputados.='<br>'.$nImputado;
				}
			
				$oResolutivo["valor"]=$lblImputados;	
			break;
			
		}
		
		$oResolutivo["comentarios"]=$fila[3];
		array_push($arrResultado["resolutivos"],$oResolutivo);
	}
	
	$arrResultado["medidasCautelares"]=array();
	$consulta="SELECT (SELECT CONCAT(nombre,' ',apellidoPaterno,' ',apellidoMaterno)  FROM _47_tablaDinamica 
				WHERE id__47_tablaDinamica=idImputado) AS imputado,(SELECT tipoMedidaCautelar FROM _110_tablaDinamica 
				WHERE id__110_tablaDinamica=tipoMedida) AS medida,comentariosAdicionales,valorComp1,valorComp2,valorComp3,tipoMedida 
				FROM 3014_registroMedidasCautelares r WHERE idEventoAudiencia=".$idEvento;
	$rEvento=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($rEvento))
	{
		$fMedidaCautelar=array();
		$fMedidaCautelar["imputado"]=$fila[0];
		$fMedidaCautelar["medida"]=$fila[1];		
		$fMedidaCautelar["comentarios"]=$fila[2];
		switch($fila[6])
		{
			case 1:
				$consulta="select nombreAutoridad FROM _328_tablaDinamica where id__328_tablaDinamica=".$fila[3];
				$nombreAutoridad=$con->obtenerValor($consulta);
				$fMedidaCautelar["medida"].=" (Presentarse ante autoridad: ".$nombreAutoridad.")";
			break;
			case 2:
				$fMedidaCautelar["medida"].=" (Monto de la garant&iacute;a: $ ".number_format($fila[3],2)." en ".($fila[4]==1?" pago":"pagos").")";
			break;
		}
		
		array_push($arrResultado["medidasCautelares"],$fMedidaCautelar);
	}
	
	
	$arrResultado["acuerdosReparatorios"]=array();	
	$consulta="SELECT idRegistro,(SELECT GROUP_CONCAT(CONCAT(nombre,' ',apellidoPaterno,' ',apellidoMaterno)) FROM 3014_imputadosAcuerdoReparatorio a,
				_47_tablaDinamica i WHERE a.idAcuerdo=r.idRegistro AND i.id__47_tablaDinamica=a.idImputado) as imputados,
				resumenAcuerdo,if(tipoCumplimiento=1,'Inmediato','Diferido') as tipoCumplimiento,if(acuerdoAprobado=1,'SÌ','No') as acuerdoAprobado,
				fechaExtincionAccionPenal,comentariosAdicionales FROM 3014_registroAcuerdosReparatorios r WHERE idEvento=".$idEvento;
	$rEvento=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($rEvento))
	{
		array_push($arrResultado["acuerdosReparatorios"],$fila);
	}
	
	$arrResultado["medidasProteccion"]=array();
	$consulta="SELECT (SELECT CONCAT(nombre,' ',apellidoPaterno,' ',apellidoMaterno)  FROM _47_tablaDinamica 
				WHERE id__47_tablaDinamica=idImputado) AS imputado,
				(SELECT medidaProteccion FROM _333_tablaDinamica WHERE id__333_tablaDinamica=r.tipoMedida) AS medidaProteccion,
				comentariosAdicionales FROM 3014_registroMedidasProteccion r WHERE idEventoAudiencia=".$idEvento;
	$rEvento=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($rEvento))
	{
		array_push($arrResultado["medidasProteccion"],$fila);
	}
	
	$arrResultado["suspensionCondicional"]=array();	
	$consulta="SELECT (SELECT CONCAT(nombre,' ',apellidoPaterno,' ',apellidoMaterno)  FROM _47_tablaDinamica 
				WHERE id__47_tablaDinamica=idImputado) AS imputado,
				(SELECT nombreCondicion FROM _334_tablaDinamica WHERE id__334_tablaDinamica=r.tipoMedida) AS condicionSuspencion,
				comentariosAdicionales FROM 3014_registroMedidasProteccion r WHERE idEventoAudiencia=".$idEvento;
	$rEvento=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($rEvento))
	{
		array_push($arrResultado["suspensionCondicional"],$fila);
	}
	return $arrResultado;
}

function reportarStatusSolicitudPGJ($idFormulario,$idRegistro,$status)
{
	global $con;
	global $servidorPruebas;
	global $cancelarNotificacionesPGJ;
	global $tipoMateria;
	global $urlPruebas;
	global $urlProduccion;
	if((($servidorPruebas)&&(!$pruebasPGJ))||($cancelarNotificacionesPGJ)|| ($tipoMateria!="P"))
		return true;
	
	$url = $urlProduccion;
	if($pruebasPGJ)
		$url =$urlPruebas;
	
	$sistema=0;	
	$fRegistroEvento=array();
	if($idFormulario!=185)
	{
		$consulta="SELECT ctrlSolicitud,idSolicitud,cveSolicitud,carpetaAdministrativa,sistema FROM _".$idFormulario."_tablaDinamica 
				WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
		$fRegistroEvento=$con->obtenerPrimeraFilaAsoc($consulta);
	}
	else
	{
		$consulta="SELECT iFormulario,iRegistro FROM _185_tablaDinamica WHERE id__185_tablaDinamica=".$idRegistro;
		$fRegistroPromocion=$con->obtenerPrimeraFila($consulta)	;
		
		if($fRegistroPromocion[0]!="N/E")
		{
		
			$consulta="SELECT ctrlSolicitud,idSolicitud,cveSolicitud,carpetaAdministrativa,sistema FROM _".$fRegistroPromocion[0]."_tablaDinamica 
				WHERE id__".$fRegistroPromocion[0]."_tablaDinamica=".$fRegistroPromocion[1];
	
			$fRegistroEvento=$con->obtenerPrimeraFilaAsoc($consulta);
		}
		else
		{
			return true;
		}
	}
	
	if($fRegistroEvento["ctrlSolicitud"]=="")
		return true;
	
	$sistema=$fRegistroEvento["sistema"];
	$client = new nusoap_client($url,"wsdl");
	$parametros=array();
	$parametros["ctrSolicitud"]=$fRegistroEvento["ctrlSolicitud"];
	$parametros["idSolicitud"]=$fRegistroEvento["idSolicitud"];
	$parametros["Status"]=$status;
	$response = $client->call("RegistrarEstatusSolicitudTSJ".($sistema==1?"":"FSIAP"), $parametros);
	
	/*if(isset($response["faultstring"]))
	{
		$response["RegistrarRespuestadeSolicitudResult"]["mensaje"]=0;
		$response["RegistrarRespuestadeSolicitudResult"]["error"]=$response["faultstring"];
		
	}
	else
	{
		$response["RegistrarRespuestadeSolicitudResult"]["mensaje"]=$response["RegistrarEstatusSolicitudTSJ".($sistema==1?"":"FSIAP")."Result"]["mensaje"];
		$response["RegistrarRespuestadeSolicitudResult"]["error"]=$response["RegistrarEstatusSolicitudTSJ".($sistema==1?"":"FSIAP")."Result"]["error"];
	}*/

	

	return true;
}

function esRegistroAdolescentes($idFormulario,$idRegistro)
{
	global $con;
	
	global $tipoMateria;
	if($tipoMateria!="P")
		return 0;
	
	$codigoUnidad=$_SESSION["codigoInstitucion"];
	if($idRegistro!=-1)
	{
		switch($idFormulario)
		{
			case 46:
					$consulta="SELECT materiaDestino FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;

					$tMateria=$con->obtenerValor($consulta);
					return ($tMateria==2)?1:0;
			break;
			case 47:
					$consulta="SELECT s.codigoInstitucion  FROM _47_tablaDinamica r,_46_tablaDinamica s WHERE s.idActividad=r.idActividad AND r.id__47_tablaDinamica=".$idRegistro;
					$codigoUnidad=$con->obtenerValor($consulta);
					if($codigoUnidad=="")
						$codigoUnidad=-1;
			break;
		}
	}
	
	$consulta="SELECT tipoMateria FROM _17_tablaDinamica WHERE claveUnidad='".$codigoUnidad."'";
	
	$tMateria=$con->obtenerValor($consulta);
	
	
	return ($tMateria==2)?1:0;
}

function obtenerIDActividadCarpetaJudicial($carpetaJudicial)
{
	global $con;
	$idActividad="";
	$encontrado=false;
	while(!$encontrado)
	{
		$consulta="SELECT carpetaAdministrativaBase,idActividad FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$carpetaJudicial."'";
		$fila=$con->obtenerPrimeraFila($consulta);
		$idActividad=$fila[1];
		if($idActividad=="")
			$idActividad=-1;
			
		if(($idActividad<>-1)||($fila[0]==""))
		{
			$encontrado=true;
		}
		else
		{
			$carpetaJudicial=$fila[0];
		}
	}
	return $idActividad;
}

function registrarComputoPrisionCumplida($idRegistro,$cComputo)
{
	global $con;
	$cComputo=json_decode(bD($cComputo));
	
	$x=0;
	$query[$x]="begin";
	$x++;
	$query[$x]="DELETE FROM _405_computoPrisionCumplida WHERE iFormulario=405 AND iRegistro=".$idRegistro;
	$x++;	
	
	foreach($cComputo->arrComputo as $c)
	{
		
		$query[$x]="INSERT INTO _405_computoPrisionCumplida(iFormulario,iRegistro,anios,meses,dias,lugarDetencion,especifique)
					VALUES(405,".$idRegistro.",".$c->anos.",".$c->meses.",".$c->dias.",'".$c->lugarDetencion."','".cv($c->especifique)."')";
		$x++;
	}
	
	$query[$x]="commit";
	$x++;
	
	return $con->ejecutarBloque($query);
	
}

function generarFolioCarpetaUnidadEjecucionV2($idFormulario,$idRegistro)
{
	global $con;			
	$idUnidadGestion=36;	
	$consulta="SELECT count(*) FROM _405_tablaDinamica WHERE idReferencia=".$idRegistro." and sentenciadoEnLibertad=0";
	$nPenas=$con->obtenerValor($consulta);
	
	$anio=date("Y");
	$query="SELECT carpetaAdministrativa,carpetaEjecucion,idActividad FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$fDatosCarpeta=$con->obtenerPrimeraFila($query);
	if($fDatosCarpeta[1]=="N/E")
		$fDatosCarpeta[1]="";
		
	$cAdministrativaBase=$fDatosCarpeta[0];
	$carpetaEnjuiciamiento=$fDatosCarpeta[1];
	if($carpetaEnjuiciamiento!="")
		return true;
	
	$consulta="SELECT unidadGestion,carpetaInvestigacion FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$cAdministrativaBase."'";	
	$fCarpetaBase=$con->obtenerPrimeraFila($consulta);
	$unidadOrigen=$fCarpetaBase[0];
	$carpetaInvestigacion=$fCarpetaBase[1];
	
	switch($unidadOrigen)
	{
		
		case "005":
		case "007":
		case "011":
			
				$idUnidadGestion=36; //Sullivan
			
		
		break;
		case "001":		
		case "003":		
		case "008":
		case "209":
				$idUnidadGestion=51; //Norte
		break;
		case "004":
		case "002":
		case "006":
		case "010":
				$idUnidadGestion=53; //Oriente
		
		break;
	}
	/*switch($unidadOrigen)
	{
		case "001":
		case "002":
		case "003":
		case "004":
		case "005":
		case "007":
		case "011":
			if($nPenas==0)
				$idUnidadGestion=36; //Sullivan
			else
			{
				$consulta="SELECT reclusorio FROM _405_tablaDinamica WHERE idReferencia=".$idRegistro." and sentenciadoEnLibertad=0";
				$reclusorio=$con->obtenerValor($consulta);
				switch($reclusorio)
				{
					case "00020001": //Norte
						$idUnidadGestion=51; 
					break;
					case "00020002"://Oriente
						$idUnidadGestion=53; 
					break;
					case "00020003": //Sur
						$idUnidadGestion=53;
					break;
					case "00020008"://Santa Martha
						$idUnidadGestion=53; 
					break;
				}
			}
		
		break;
		case "008":
		case "209":
			if($nPenas==0)
				$idUnidadGestion=51; //Norte
			else
			{
				$consulta="SELECT reclusorio FROM _405_tablaDinamica WHERE idReferencia=".$idRegistro." and sentenciadoEnLibertad=0";
				$reclusorio=$con->obtenerValor($consulta);
				switch($reclusorio)
				{
					case "00020001": //Norte
						$idUnidadGestion=51; 
					break;
					case "00020002"://Oriente
						$idUnidadGestion=53; 
					break;
					case "00020003": //Sur
						$idUnidadGestion=53;
					break;
					case "00020008"://Santa Martha
						$idUnidadGestion=53; 
					break;
				}
			}
			
		break;
		
		case "006":
		case "010":
		
			if($nPenas==0)
				$idUnidadGestion=53; //Oriente
			else
			{
				$consulta="SELECT reclusorio FROM _405_tablaDinamica WHERE idReferencia=".$idRegistro." and sentenciadoEnLibertad=0";
				$reclusorio=$con->obtenerValor($consulta);
				switch($reclusorio)
				{
					case "00020001": //Norte
						$idUnidadGestion=51; 
					break;
					case "00020002"://Oriente
						$idUnidadGestion=53; 
					break;
					case "00020003": //Sur
						$idUnidadGestion=53;
					break;
					case "00020008"://Santa Martha
						$idUnidadGestion=53; 
					break;
				}
			}
			
			
		break;
	}*/
	
	$carpetaAdministrativa=obtenerSiguienteCarpetaAdministrativa($idUnidadGestion,$anio,6,$idFormulario,$idRegistro);
	$consulta="SELECT juezResponsable FROM _621_tablaDinamica WHERE carpetaAdministrativa='".$carpetaAdministrativa."' 
				and idEstado>1 order by id__621_tablaDinamica desc";
	$idJuezEjecucion=$con->obtenerValor($consulta);
	if($idJuezEjecucion=="")
		$idJuezEjecucion=-1;
	if($idJuezEjecucion=="-1")	
		$idJuezEjecucion=asignarJuezEjecucionCarpetaUnica($idUnidadGestion,$idFormulario,$idRegistro);
	$fechaCreacionCarpeta=date("Y-m-d H:i:s");
	$idActividadEjecucion=$fDatosCarpeta[2];
	
	$query="SELECT claveUnidad FROM _17_tablaDinamica WHERE id__17_tablaDinamica=".$idUnidadGestion;
	$cveUnidadGestion=$con->obtenerValor($query);
	
	$consulta=array();
	$x=0;
	$consulta[$x]="begin";
	$x++;
	
	$consulta[$x]="INSERT INTO 7006_carpetasAdministrativas(carpetaAdministrativa,fechaCreacion,responsableCreacion,idFormulario,
				idRegistro,unidadGestion,etapaProcesalActual,idActividad,carpetaAdministrativaBase,idJuezTitular,
				tipoCarpetaAdministrativa,situacion,carpetaInvestigacion,llaveCarpetaInvestigacion,unidadGestionOriginal) 
				VALUES('".$carpetaAdministrativa."','".$fechaCreacionCarpeta."',".$_SESSION["idUsr"].",".$idFormulario.",'".
				$idRegistro."','".$cveUnidadGestion."',6,".$idActividadEjecucion.",'".$cAdministrativaBase.
				"',".$idJuezEjecucion.",6,15,(SELECT UPPER('".$carpetaInvestigacion."')),'".cv(generarLlaveCarpetaInvestigacion($carpetaInvestigacion)).
				"','".$cveUnidadGestion."')";
	$x++;
	$consulta[$x]="update _".$idFormulario."_tablaDinamica set carpetaEjecucion='".$carpetaAdministrativa."',idUnidadReceptora=".$idUnidadGestion.
				",fechaRecepcion='".$fechaCreacionCarpeta."' where id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$x++;
			
	
	$consulta[$x]="commit";
	$x++;
	
	if($con->ejecutarBloque($consulta))
	{
		registrarCambioEtapaProcesalCarpeta($cAdministrativaBase,6,$idFormulario,$idRegistro,-1);
		//registrarCambioSituacionCarpeta($cAdministrativaBase,11,$idFormulario,$idRegistro,-1);
		
		$query="SELECT carpetaAdministrativaBase,tipoCarpetaAdministrativa FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$cAdministrativaBase."'";
		$fCBase=$con->obtenerPrimeraFila($query);
		if(($fCBase[1]==5)&&($fCBase[0]!=""))
		{
			registrarCambioEtapaProcesalCarpeta($fCBase[0],6,$idFormulario,$idRegistro,-1);
		}
		autorizarRecepcionCarpetaEjecucionV2($idFormulario,$idRegistro);

	}
	return false;
	
}

function generarFolioCarpetaUnidadEjecucionLeyNacional($idFormulario,$idRegistro)
{
	global $con;
	$idUnidadGestion=36;		
	
	$anio=date("Y");
	
	$query="SELECT carpetaAdministrativa,carpetaEjecucion FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$fDatosCarpeta=$con->obtenerPrimeraFila($query);
	if($fDatosCarpeta[1]=="N/E")
		$fDatosCarpeta[1]="";
		
	$cAdministrativaBase=$fDatosCarpeta[0];
	$carpetaEnjuiciamiento=$fDatosCarpeta[1];
	if($carpetaEnjuiciamiento!="")
		return true;
	
	$consulta="SELECT unidadGestion,carpetaInvestigacion FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$cAdministrativaBase."'";	
	$fCarpetaBase=$con->obtenerPrimeraFila($consulta);
	$unidadOrigen=$fCarpetaBase[0];
	$carpetaInvestigacion=$fCarpetaBase[1];
	
	$consulta="SELECT reclusorio,unidadEjecucionDestino FROM _493_tablaDinamica WHERE idReferencia=".$idRegistro;
	$fDatosImputado=$con->obtenerPrimeraFila($consulta);
	$reclusorio=$fDatosImputado[0];
	/*switch($reclusorio)
	{
		case "00020001": //Norte
		case "00020002"://Oriente
		case "00020003": //Sur
		case "00020008"://Santa Martha
		break;
		default:
			$reclusorio=$fDatosImputado[1];
		break;
		
	}
	
	
	switch($reclusorio)
	{
		case "00020001": //Norte
			$idUnidadGestion=51; 
		break;
		case "00020002"://Oriente
			$idUnidadGestion=53; 
		break;
		case "00020003": //Sur
			$idUnidadGestion=53;
		break;
		case "00020008"://Santa Martha
			$idUnidadGestion=53; 
		break;
		
	}*/
	
	
	if($unidadOrigen=="012")
	{
		$consulta="SELECT carpetaAdministrativa,unidadGestion FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativaBase='".$cAdministrativaBase.
				"' AND tipoCarpetaAdministrativa=1";
		$fCarpetaIncompetencia=$con->obtenerPrimeraFila($consulta);
		if($fCarpetaIncompetencia)
			$unidadOrigen=$fCarpetaIncompetencia[1];
	}
	
	$idUnidadGestion=-1;
	switch($unidadOrigen)
	{
		
		case "005":
		case "007":
		case "011":
		case "012":
				$idUnidadGestion=36; //Sullivan
			
		
		break;
		case "001":		
		case "003":		
		case "008":
		case "209":
				$idUnidadGestion=51; //Norte
		break;
		case "004":
		case "002":
		case "006":
		case "010":
				$idUnidadGestion=53; //Oriente
		
		break;
	}
	
	$carpetaAdministrativa=obtenerSiguienteCarpetaAdministrativa($idUnidadGestion,$anio,9,$idFormulario,$idRegistro);
	
	$query=" SELECT lj.clave FROM _26_tablaDinamica lj,_26_tipoJuez tj WHERE lj.idReferencia=".$idUnidadGestion." AND 
				tj.idPadre =lj.id__26_tablaDinamica AND tj.idOpcion=3  ORDER BY usuarioJuez";
	$listaJueces=$con->obtenerListaValores($query,"'");
	if($listaJueces=="")
		$listaJueces=-1;
	$idJuezEjecucion=obtenerSiguienteJuez(10,$listaJueces,-1);
	$fechaCreacionCarpeta=date("Y-m-d H:i:s");
	
	$idActividadEjecucion=generarIDActividad($idFormulario,$idRegistro);
	
	$query="SELECT claveUnidad FROM _17_tablaDinamica WHERE id__17_tablaDinamica=".$idUnidadGestion;
	$cveUnidadGestion=$con->obtenerValor($query);
	
	$consulta=array();
	$x=0;
	$consulta[$x]="begin";
	$x++;
	
	$consulta[$x]="INSERT INTO 7006_carpetasAdministrativas(carpetaAdministrativa,fechaCreacion,responsableCreacion,idFormulario,
				idRegistro,unidadGestion,etapaProcesalActual,idActividad,carpetaAdministrativaBase,idJuezTitular,
				tipoCarpetaAdministrativa,situacion,carpetaInvestigacion,llaveCarpetaInvestigacion,unidadGestionOriginal) 
				VALUES('".$carpetaAdministrativa."','".$fechaCreacionCarpeta."',".$_SESSION["idUsr"].",".$idFormulario.",'".
				$idRegistro."','".$cveUnidadGestion."',6,".$idActividadEjecucion.",'".$cAdministrativaBase.
				"',".$idJuezEjecucion.",9,1,(SELECT UPPER('".$carpetaInvestigacion."')),'".cv(generarLlaveCarpetaInvestigacion($carpetaInvestigacion)).
				"','".$cveUnidadGestion."')";
	$x++;
	$consulta[$x]="update _".$idFormulario."_tablaDinamica set carpetaEjecucion='".$carpetaAdministrativa."',idUnidadDestino=".$idUnidadGestion.
				",fechaEnvio='".$fechaCreacionCarpeta."' where id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$x++;
	
	$consulta[$x]="INSERT INTO 7005_relacionFigurasJuridicasSolicitud(idActividad,idParticipante,idFiguraJuridica)
					SELECT ".$idActividadEjecucion.",imputado,4 FROM _493_tablaDinamica WHERE idReferencia=".$idRegistro	;
	$x++;
	
	
	$consulta[$x]="commit";
	$x++;
	
	if($con->ejecutarBloque($consulta))
	{
		$query="SELECT idDocumento FROM 9074_documentosRegistrosProceso WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro;
		$rDocumentos=$con->obtenerFilas($query);
		while($fDocumento=mysql_fetch_row($rDocumentos))
		{
			registrarDocumentoCarpetaAdministrativa($carpetaAdministrativa,$fDocumento[0],$idFormulario,$idRegistro);	
		}

	}
	return false;
}



function autorizarRecepcionCarpetaEjecucionV2($idFormulario,$idRegistro)
{
	global $con;
	$query="SELECT carpetaAdministrativa,carpetaEjecucion,idActividad FROM _".$idFormulario."_tablaDinamica 
			WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$fDatosCarpeta=$con->obtenerPrimeraFila($query);
	$cAdministrativaBase=$fDatosCarpeta[0];
	
	$idActividadEjecucion=$fDatosCarpeta[2];
	if($idActividadEjecucion=="")
		$idActividadEjecucion=-1;
	

	

	$query="SELECT idCarpeta FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$cAdministrativaBase."'";
	$idCarpetaBase=$con->obtenerValor($query);
	
	$aDelitos=array();
	$x=0;
	$consulta[$x]="begin";
	$x++;
	
	
	$query="SELECT sentenciado,idActividad,id__405_tablaDinamica FROM _405_tablaDinamica WHERE idReferencia=".$idRegistro;
	$rSetenciado=$con->obtenerFilas($query);
	while($fSentencia=mysql_fetch_row($rSetenciado))
	{
		if(existeParticipanteRegistro($idActividadEjecucion,$fSentencia[0],4))
		{
			continue;
		}
		$consulta[$x]="INSERT INTO 7005_relacionFigurasJuridicasSolicitud(idActividad,idParticipante,idFiguraJuridica)
					values(".$idActividadEjecucion.",".$fSentencia[0].",4)";
		$x++;
		$consulta[$x]="UPDATE _405_tablaDinamica SET idActividadEjecucion=".$idActividadEjecucion." WHERE id__405_tablaDinamica=".$fSentencia[2];
		$x++;

		$consulta[$x]="UPDATE 7024_registroPenasSentenciaEjecucion SET idActividadEjecucion=".$idActividadEjecucion.
					" WHERE idActividad=".$fSentencia[1];
		$x++;
		
		$consulta[$x]="INSERT INTO 7046_penasVSCarpetasEjecucion(idActividad,idImputado,idPena) 
						SELECT '".$idActividadEjecucion."' as idActividad,'".$fSentencia[0]."' as idImputado,idRegistro as  idPena
						FROM 7024_registroPenasSentenciaEjecucion WHERE idActividad=".$fSentencia[1];
		$x++;
		
		$query="SELECT  DISTINCT d.idDelito FROM 7024_registroPenasSentenciaEjecucion p,7032_delitosPena d WHERE p.idActividad= ".$fSentencia[1]."
					AND d.idPena=p.idRegistro";
		$rDelitos=$con->obtenerFilas($query);
		while($fDelito=mysql_fetch_row($rDelitos))
		{
			if(!isset($aDelitos[$fDelito[0]]))
			{
				$aDelitos[$fDelito[0]]=array();
			}
			array_push($aDelitos[$fDelito[0]],$fSentencia[0]);
		}


	}
	
	$query="SELECT asesor,id__426_tablaDinamica FROM _426_tablaDinamica WHERE idReferencia=".$idRegistro;
	$rSetenciado=$con->obtenerFilas($query);
	while($fSentencia=mysql_fetch_row($rSetenciado))
	{
		if(existeParticipanteRegistro($idActividadEjecucion,$fSentencia[0],3))
		{
			continue;
		}
		$consulta[$x]="INSERT INTO 7005_relacionFigurasJuridicasSolicitud(idActividad,idParticipante,idFiguraJuridica)
					values(".$idActividadEjecucion.",".$fSentencia[0].",3)";
		$x++;
	}
	
	$query="SELECT defensor,id__424_tablaDinamica FROM _424_tablaDinamica WHERE idReferencia=".$idRegistro;
	$rSetenciado=$con->obtenerFilas($query);
	while($fSentencia=mysql_fetch_row($rSetenciado))
	{
		if(existeParticipanteRegistro($idActividadEjecucion,$fSentencia[0],5))
		{
			continue;
		}
		$consulta[$x]="INSERT INTO 7005_relacionFigurasJuridicasSolicitud(idActividad,idParticipante,idFiguraJuridica)
					values(".$idActividadEjecucion.",".$fSentencia[0].",5)";
		$x++;
	}
	
	$query="SELECT ministerioPublico,id__427_tablaDinamica FROM _427_tablaDinamica WHERE idReferencia=".$idRegistro;
	$rSetenciado=$con->obtenerFilas($query);
	while($fSentencia=mysql_fetch_row($rSetenciado))
	{
		if(existeParticipanteRegistro($idActividadEjecucion,$fSentencia[0],10))
		{
			continue;
		}
		$consulta[$x]="INSERT INTO 7005_relacionFigurasJuridicasSolicitud(idActividad,idParticipante,idFiguraJuridica)
					values(".$idActividadEjecucion.",".$fSentencia[0].",10)";
		$x++;
	}
	
	$query="SELECT victima,id__425_tablaDinamica FROM _425_tablaDinamica WHERE idReferencia=".$idRegistro;
	$rSetenciado=$con->obtenerFilas($query);
	while($fSentencia=mysql_fetch_row($rSetenciado))
	{
		if(existeParticipanteRegistro($idActividadEjecucion,$fSentencia[0],2))
		{
			continue;
		}
		$consulta[$x]="INSERT INTO 7005_relacionFigurasJuridicasSolicitud(idActividad,idParticipante,idFiguraJuridica)
					values(".$idActividadEjecucion.",".$fSentencia[0].",2)";
		$x++;
	}
	
	
	foreach($aDelitos as $iDelito=>$resto)
	{
		$consulta[$x]="INSERT INTO _61_tablaDinamica(fechaCreacion,responsable,idEstado,codigoInstitucion,denominacionDelito,idActividad)
						VALUES('".date("Y-m-d H:i:s")."',".$_SESSION["idUsr"].",1,'".$_SESSION["codigoInstitucion"].
						"',".$iDelito.",".$idActividadEjecucion.")";
		$x++;
		$consulta[$x]="set @iDelito:=(select last_insert_id())";
		$x++;
		foreach($resto as $sentenciado)
		{
			$consulta[$x]="INSERT INTO _61_chkDelitosImputado(idPadre,idOpcion) VALUES(@iDelito,".$sentenciado.")";
			$x++;
		}
	}
	
	$consulta[$x]="commit";
	$x++;
	
	if( $con->ejecutarBloque($consulta))
	{

		mysql_data_seek($rSetenciado,0);
		while($fSentencia=mysql_fetch_row($rSetenciado))
		{
			$imputado=$fSentencia[0];
			registrarCambioSituacionImputado($cAdministrativaBase,$idCarpetaBase,$imputado,10,"","Incompetencia");
		}
		determinarSituacionCarpeta($cAdministrativaBase,$idCarpetaBase);

		return true;
	}
}

//Calculo matrices fechas
function convertirLeyendaComputo($arrValores)
{
	$leyenda='';
    $arrValores=normalizarValoresComputo($arrValores);
    
    if(($arrValores[0]==0)&&($arrValores[1]==0)&&($arrValores[2]==0))
    {
    	return '0 a&ntilde;os, 0 meses, 0 dias';
    }
    
    
    if($arrValores[0]>0)
    {
    	$leyenda.=$arrValores[0].($arrValores[0]==1?' a&ntilde;o':' a&ntilde;os');
    }
    
    if($arrValores[1]>0)
    {
    	if($leyenda=='')
    		$leyenda.=$arrValores[1].($arrValores[1]==1?' mes':' meses');
        else
        	$leyenda.=', '.$arrValores[1].($arrValores[1]==1?' mes':' meses');
    }
    
    if($arrValores[2]>0)
    {
    	if($leyenda=='')
    		$leyenda.=$arrValores[2].($arrValores[2]==1?' dia':' dias');
        else
        	$leyenda.=', '.$arrValores[2].($arrValores[2]==1?' dia':' dias');
    }
    
    return $leyenda;
    
}

function sumarComputo($arrValores1,$arrValores2)
{
	$diasMes=30;
	$arrValores1=normalizarValoresComputo($arrValores1);
    $arrValores2=normalizarValoresComputo($arrValores2);
    
	$arrValoresResultado=array();
    $arrValoresResultado[0]=0;
    $arrValoresResultado[1]=0;
    $arrValoresResultado[2]=0;
    
    $arrValoresResultado[2]=$arrValores1[2]+$arrValores2[2];
    if($arrValoresResultado[2]>$diasMes)
    {
    	$meses=parteEntera($arrValoresResultado[2]/$diasMes,false);
        $arrValoresResultado[2]-=($meses*$diasMes);
        $arrValoresResultado[1]=$meses;
    }
    
    $arrValoresResultado[1]+=$arrValores1[1]+$arrValores2[1];
    if($arrValoresResultado[1]>12)
    {
    	$anios=parteEntera($arrValoresResultado[1]/12,false);
        $arrValoresResultado[1]-=($anios*12);
        $arrValoresResultado[0]=$anios;
    }
    
    $arrValoresResultado[0]+=$arrValores1[0]+$arrValores2[0];
    
    return $arrValoresResultado;
}

function restarComputo($arrValores1,$arrValores2)
{
	$diasMes=30;
	$arrValores1=normalizarValoresComputo($arrValores1);
    $arrValores2=normalizarValoresComputo($arrValores2);
    
	$arrValoresResultado=array();
    $arrValoresResultado[0]=0;
    $arrValoresResultado[1]=0;
    $arrValoresResultado[2]=0;
    
    $arrValoresAux1=array();
    $arrValoresAux1[0]=($arrValores1[0]*12)+$arrValores1[1];
    $arrValoresAux1[1]=$arrValores1[2];
    
    $arrValoresAux2=array();
    $arrValoresAux2[0]=($arrValores2[0]*12)+$arrValores2[1];
    $arrValoresAux2[1]=$arrValores2[2];
    
    $arrValoresResultadoAux=array();
    $arrValoresResultadoAux[0]=0;
    $arrValoresResultadoAux[1]=0;
    if($arrValoresAux1[1]<$arrValoresAux2[1])
    {
    	$diferencia=$arrValoresAux2[1]-$arrValoresAux1[1];
      	$nMeses=parteEntera($diferencia/$diasMes,false);
        if(($diferencia%$diasMes)>0)
        	$nMeses++;
        
        if($arrValoresAux1[0]>$nMeses)
        {
        	$arrValoresAux1[0]-=$nMeses;
            $arrValoresAux1[1]+=($nMeses*$diasMes);
        }
        
        else
        {
        	$arrValoresResultado[0]=0;
            $arrValoresResultado[1]=0;
            $arrValoresResultado[2]=0;            
        	return $arrValoresResultado;
        }
    }
    
    $arrValoresResultadoAux[1]=$arrValoresAux1[1]-$arrValoresAux2[1];
    $arrValoresResultadoAux[0]=$arrValoresAux1[0]-$arrValoresAux2[0];
	
    if($arrValoresResultadoAux[0]<0)
    {
    	$arrValoresResultado[0]=0;
        $arrValoresResultado[1]=0;
        $arrValoresResultado[2]=0; 
    }
    else
    {
    	$arrValoresResultado[0]=parteEntera($arrValoresResultadoAux[0]/12,false);
        $arrValoresResultado[1]=$arrValoresResultadoAux[0]-($arrValoresResultado[0]*12);
        $arrValoresResultado[2]=$arrValoresResultadoAux[1];
    }
    return $arrValoresResultado;
}

function normalizarValoresComputo($arrValores)
{
	$arrValores[0]=($arrValores[0]=='')?0:$arrValores[0];
    $arrValores[1]=($arrValores[1]=='')?0:$arrValores[1];
    $arrValores[2]=($arrValores[2]=='')?0:$arrValores[2];
    return $arrValores;
}

function participanteAgregado($iRegistro)
{
	global $con;
	
	$consulta="SELECT upper(CONCAT(if(nombre is null,'',nombre),' ',if(apellidoPaterno is null,'',apellidoPaterno),' ',if(apellidoMaterno is null,'',apellidoMaterno))),idActividad,figuraJuridica FROM _47_tablaDinamica WHERE id__47_tablaDinamica=".$iRegistro;
	$fRegistro=$con->obtenerPrimeraFila($consulta);
	$nombre=$fRegistro[0];
	$consulta="INSERT INTO 7005_relacionFigurasJuridicasSolicitud(idActividad,idParticipante,idFiguraJuridica) 
			VALUES(".$fRegistro[1].",".$iRegistro.",".$fRegistro[2].")";
	$con->ejecutarConsulta($consulta);
	
	echo "window.parent.participanteAgregado(".$iRegistro.",'".$nombre."');window.parent.cerrarVentanaFancy();return;";
}

function obtenerCarpetasHijas($cA)
{
	global $con;
	$arrHijos="";
	$consulta="SELECT carpetaAdministrativa FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativaBase='".$cA."'";
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		$arrHijos2="[".obtenerCarpetasHijas($fila[0])."]";	
		
		$obj='{expanded:true,"icon":"../images/s.gif","id":"'.$fila[0].'","text":"'.$fila[0].'",children:'.$arrHijos2.',leaf:'.(($arrHijos2=="[]")?"true":"false").'}';
		if($arrHijos=="")
			$arrHijos=$obj;
		else
			$arrHijos.=",".$obj;
	}
	
	
	
	
	return $arrHijos;
}

function obtenerCarpetasPadre($cA,&$arrCarpetasPadre)
{
	global $con;
	$arrHijos="";
	$consulta="SELECT carpetaAdministrativaBase FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$cA."'";
	$carpetaAdministrativaBase=$con->obtenerValor($consulta);
	if($carpetaAdministrativaBase!="")
	{
		obtenerCarpetasPadre($carpetaAdministrativaBase,$arrCarpetasPadre);
		array_push($arrCarpetasPadre,$carpetaAdministrativaBase);
		
	}
	
	return true;
}


function obtenerReclusorioDestinoCopiaSentencia($idRegistro)
{
	global $con;
	$consulta="SELECT o.unidad FROM _428_tablaDinamica c,817_organigrama o WHERE id__428_tablaDinamica=".$idRegistro." AND o.codigoUnidad=c.reclusorio";
	
	$reclusorio=$con->obtenerValor($consulta);
	return "'".$reclusorio."'";
}

function obtenerSetenciadoCopiaSentencia($idRegistro)
{
	global $con;
	$consulta="SELECT concat(s.nombre,' ',s.apellidoPaterno,' ',s.apellidoMaterno) FROM _428_tablaDinamica c,
				_47_tablaDinamica s WHERE id__428_tablaDinamica=".$idRegistro." AND s.id__47_tablaDinamica=c.idSentenciado";
	$sentenciado=$con->obtenerValor($consulta);
	return "'".$sentenciado."'";
}


function obtenerFechaHoraActual()
{
	global $con;
	return date("Y-m-d H:i:s");
}

function generarEnvioCopiaSentencia($idFormulario,$idRegistro)
{
	global $con;
	$consulta="SELECT carpetaEjecucion,idActividad,carpetaAdministrativa FROM _385_tablaDinamica WHERE id__385_tablaDinamica=".$idRegistro;
	$fDatosRegistro=$con->obtenerPrimeraFila($consulta);
	
	$query="SELECT setencia,auto FROM _412_tablaDinamica WHERE idReferencia=".$idRegistro;
	$fDocumentos=$con->obtenerPrimeraFila($query);
	
	$doc1=convertirDocumentoUsuarioDocumentoResultadoProceso($fDocumentos[0],-1,-1,"",3);
	$doc2=convertirDocumentoUsuarioDocumentoResultadoProceso($fDocumentos[1],-1,-1,"",3);
	
	$consulta="SELECT sentenciado,idActividad FROM _405_tablaDinamica WHERE idReferencia=".$idRegistro;
	$rSentenciado=$con->obtenerFilas($consulta);
	while($fSentenciado=mysql_fetch_row($rSentenciado))
	{
		$arrValores=array();
		
		$arrValores["carpetaAdministrativa"]=$fDatosRegistro[2];
		$arrValores["carpetaEjecucion"]=$fDatosRegistro[0];
		$arrValores["idSentenciado"]=$fSentenciado[0];
		$arrValores["carpetaAdministrativa"]=$fDatosRegistro[2];
		
		$consulta="SELECT centroDetencion,idRegistro FROM 7024_registroPenasSentenciaEjecucion WHERE idActividad=".$fSentenciado[1]." 
					AND centroDetencion IS NOT NULL AND centroDetencion<>-1 AND centroDetencion<>''";
		$rCentroDetencion=$con->obtenerFilas($consulta);					
		
		while($fCentroDetencion=mysql_fetch_row($rCentroDetencion))
		{
			
			$arrDocumentosReferencia=array();
				
			$idDocumento=@llenarFormatoPlantillaEnvioCopiaSentencia($arrValores["carpetaEjecucion"],$arrValores["idSentenciado"],$fCentroDetencion[1]);
			
			registrarDocumentoCarpetaAdministrativa($arrValores["carpetaAdministrativa"],$idDocumento,$idFormulario,$idRegistro);
			
			
			$arrValores["reclusorio"]=$fCentroDetencion[0];
			$arrValores["idPena"]=$fCentroDetencion[1];
			$idRegistroEnvio=crearInstanciaRegistroFormulario(428,-1,2,$arrValores,$arrDocumentosReferencia,-1,781);
			
			$x=0;
			$query=array();
			$query[$x]="begin";
			$x++;
			$query[$x]="INSERT INTO 9074_documentosRegistrosProceso(idFormulario,idRegistro,idDocumento,tipoDocumento) 
						VALUES(428,".$idRegistroEnvio.",".$fDocumentos[0].",2)";
			$x++;
			$query[$x]="INSERT INTO 9074_documentosRegistrosProceso(idFormulario,idRegistro,idDocumento,tipoDocumento) 
						VALUES(428,".$idRegistroEnvio.",".$fDocumentos[1].",2)";
			$x++;
			$query[$x]="INSERT INTO 9074_documentosRegistrosProceso(idFormulario,idRegistro,idDocumento,tipoDocumento) 
						VALUES(428,".$idRegistroEnvio.",".$idDocumento.",2)";
			$x++;
			$query[$x]="commit";
			$x++;
			
			$con->ejecutarBloque($query);
			
		}
		
		
		
		
	}
}

function validarEnvioRegistroEjecucion($idFormulario,$idRegistro)
{
	global $con;
	$arrErrores="";
	$arrSetenciado=array();
	$arrVictimas=array();
	$consulta="SELECT sentenciado FROM _405_tablaDinamica WHERE idReferencia=".$idRegistro;
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		$arrSetenciado[$fila[0]]=array();
	}
	
	$consulta="SELECT victima FROM _425_tablaDinamica WHERE idReferencia=".$idRegistro;
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		$arrVictimas[$fila[0]]=array();
	}
	
	$consulta="SELECT asesor,v.idOpcion FROM _426_tablaDinamica t,_426_chkVictimas v WHERE 
				v.idPadre=t.id__426_tablaDinamica AND t.idReferencia=".$idRegistro;
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		if(isset($arrVictimas[$fila[1]]))
			array_push($arrVictimas[$fila[1]],$fila[0]);
	}
	
	$consulta="SELECT defensor,v.idOpcion FROM _424_tablaDinamica t,_424_chkSentenciadosDefiende v WHERE 
				v.idPadre=t.id__424_tablaDinamica AND t.idReferencia=".$idRegistro;
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		if(isset($arrSetenciado[$fila[1]]))
			array_push($arrSetenciado[$fila[1]],$fila[0]);
	}
	
	foreach($arrSetenciado as $iSentenciado=>$resto)
	{
		if(sizeof($resto)==0)
		{
			$o="['General','Debe indicar el defensor asociado al sentenciado: ".cv(obtenerNombreParticipante($iSentenciado))."']";
			if($arrErrores=="")
				$arrErrores=$o;
			else
				$arrErrores.=",".$o;
		}
	}
	
	foreach($arrVictimas as $iVictima=>$resto)
	{
		if(sizeof($resto)==0)
		{
			$o="['General','Debe indicar el asesor jur&iacute;dico asociado a la v&iacute;ctima/ofendido: ".cv(obtenerNombreParticipante($iVictima))."']";
			if($arrErrores=="")
				$arrErrores=$o;
			else
				$arrErrores.=",".$o;
		}
	}
	
	
	return "[".$arrErrores."]";
}

function obtenerNombreParticipante($idParticipante)
{
	global $con;
	$consulta="SELECT CONCAT(IF(nombre IS NULL,'',nombre),' ',IF(apellidoPaterno IS NULL,'',apellidoPaterno),' ',IF(apellidoMaterno IS NULL,'',apellidoMaterno)) 
				FROM _47_tablaDinamica WHERE id__47_tablaDinamica=".$idParticipante;
	return $con->obtenerValor($consulta);
	
}

function obtenerPuestosSuperiores($idPerfil,$puestoBase,&$arrPuestos,$todosPuestosSuperiores=true)
{
	global $con;
	$clavePuesto=substr($puestoBase,0,strlen($puestoBase)-4);
	
	if($clavePuesto!="")
	{
		$consulta="SELECT claveNivel,(SELECT nombrePuesto FROM _416_tablaDinamica WHERE id__416_tablaDinamica=puestoOrganozacional) 
						AS puesto,u.Nombre,u.idUsuario FROM _421_tablaDinamica p,800_usuarios u WHERE p.idReferencia=".$idPerfil." 
						AND p.claveNivel ='".$clavePuesto."' AND p.usuarioAsignado=u.idUsuario
						 ORDER BY u.Nombre";
		
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$oPuesto["idUsuario"]=$fila[3];
			$oPuesto["nombre"]=$fila[2];
			$oPuesto["clave"]=$fila[0];
			$oPuesto["nombrePuesto"]=$fila[1];
			array_push($arrPuestos,$oPuesto);
			if($todosPuestosSuperiores)
				obtenerPuestosSuperiores($idPerfil,$clavePuesto,$arrPuestos,$todosPuestosSuperiores);
		}
	}
	
}

function obtenerPuestosHijosAsignaciones($idPerfil,$clavePuesto,&$arrPuestos,$todosPuestosInferiores=true)
{
	global $con;
	
	$consulta="";
	if($todosPuestosInferiores)
	{
		$consulta="SELECT claveNivel,(SELECT nombrePuesto FROM _416_tablaDinamica WHERE id__416_tablaDinamica=puestoOrganozacional) 
					AS puesto,u.Nombre,u.idUsuario FROM _421_tablaDinamica p,800_usuarios u WHERE p.idReferencia=".$idPerfil." 
					AND p.claveNivel LIKE '".$clavePuesto."%' AND p.claveNivel<>'".$clavePuesto."' AND p.usuarioAsignado=u.idUsuario
					 ORDER BY u.Nombre";

	}
	else
	{
		$consulta="SELECT claveNivel,(SELECT nombrePuesto FROM _416_tablaDinamica WHERE id__416_tablaDinamica=puestoOrganozacional) 
					AS puesto,u.Nombre,u.idUsuario FROM _421_tablaDinamica p,800_usuarios u WHERE p.idReferencia=".$idPerfil." 
					AND p.claveNivel LIKE '".$clavePuesto."____' AND p.claveNivel<>'".$clavePuesto."' AND p.usuarioAsignado=u.idUsuario
					 ORDER BY u.Nombre";
	}
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		$oPuesto["idUsuario"]=$fila[3];
		$oPuesto["nombre"]=$fila[2];
		$oPuesto["clave"]=$fila[0];
		$oPuesto["nombrePuesto"]=$fila[1];
		array_push($arrPuestos,$oPuesto);

	}
}

function esCarpetaEjecucion($idReferencia)
{
	global $con;
	$consulta="SELECT carpetaAdministrativa FROM _96_tablaDinamica WHERE id__96_tablaDinamica=".$idReferencia;
	$cAdministrativa=$con->obtenerValor($consulta);
	$consulta="SELECT tipoCarpetaAdministrativa FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$cAdministrativa."'";
	$tCarpeta=$con->obtenerValor($consulta);
	
	if($tCarpeta==6)
	{
		return 1;
	}
	
	return 0;
}

function esCarpetaExhortoEjecucion($idReferencia)
{
	global $con;
	$consulta="SELECT carpetaExhorto FROM _92_tablaDinamica WHERE id__92_tablaDinamica=".$idReferencia;
	$cAdministrativa=$con->obtenerValor($consulta);
	$consulta="SELECT unidadGestion FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$cAdministrativa."'";
	$unidadGestion=$con->obtenerValor($consulta);
	$consulta="SELECT COUNT(*) FROM _17_tablaDinamica u,_17_gridDelitosAtiende d WHERE u.claveUnidad='".$unidadGestion.
				"' AND d.idReferencia=u.id__17_tablaDinamica AND d.tipoDelito='E'";
	$nCarpeta=$con->obtenerValor($consulta);
	
	if($nCarpeta>0)
	{
		return 1;
	}
	
	return 0;
}

function turnarRutaExhorto($idFormulario,$idRegistro)
{
	global $con;
	$numEtapa=2;
	/*if(esCarpetaExhortoEjecucion($idRegistro)==1)
		$numEtapa=2.1;*/
	cambiarEtapaFormulario($idFormulario,$idRegistro,$numEtapa,"",-1,"NULL","NULL",717);
}

function obtenerFechaAudienciaEvento($idFormulario,$idRegistro)
{
	global $con;
	$consulta="SELECT fechaEvento FROM 7000_eventosAudiencia WHERE idFormulario=".$idFormulario." AND idRegistroSolicitud=".$idRegistro;
	
	$fechaEvento=$con->obtenerValor($consulta);
	return "'".$fechaEvento."'";
}

function obtenerHoraAudienciaEvento($idFormulario,$idRegistro)
{
	global $con;
	$consulta="SELECT horaInicioEvento FROM 7000_eventosAudiencia WHERE idFormulario=".$idFormulario." AND idRegistroSolicitud=".$idRegistro;
	
	$fechaEvento=$con->obtenerValor($consulta);
	return "'".$fechaEvento."'";
}

function obtenerTipoAudienciaEvento($idFormulario,$idRegistro)
{
	global $con;
	$consulta="SELECT tipoAudiencia FROM 7000_eventosAudiencia WHERE idFormulario=".$idFormulario." AND idRegistroSolicitud=".$idRegistro;
	
	$fechaEvento=$con->obtenerValor($consulta);
	return "'".$fechaEvento."'";
}

function determinarRutaEntregaImputado($idFormulario,$idRegistro)
{
	global $con;
	
	$etapaContinuacion=0;
	$consulta="SELECT * FROM _440_tablaDinamica WHERE idReferencia=".$idRegistro;
	$filaSolicitud=$con->obtenerPrimeraFila($consulta);
	
	switch($filaSolicitud[10])
	{
		case 1:
			$etapaContinuacion=2;
		break;
		default:
			$etapaContinuacion=5;
		break;
	}
	
	cambiarEtapaFormulario($idFormulario,$idRegistro,$etapaContinuacion,"",-1,"NULL","NULL",800);
		
}

function registrarEntregaImputado($idFormulario,$idRegistro)
{
	global $con;
	$consulta="SELECT * FROM  _293_tablaDinamica WHERE id__293_tablaDinamica=".$idRegistro;
	$fRegistro=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$arrValores=array();
	$arrDocumentos=array();
	$arrValores["eventoAudiencia"]=$fRegistro["idEvento"];
	$arrValores["reclusorios"]=$fRegistro["reclusorios"];
	$arrValores["carpetaAdministrativa"]=$fRegistro["carpetaAdministrativa"];
	$arrValores["imputado"]=$fRegistro["imputado"];
	$arrValores["noSolicitud"]=$fRegistro["codigo"];
	
	$idRegistroSolicitud=crearInstanciaRegistroFormulario(294,$idRegistro,1.2,$arrValores,$arrDocumentos,-1,690);
	
	$consulta="INSERT INTO 9058_imagenesControlGaleria(idElementoFormulario,idArchivoImagen,idRegistro)
			SELECT 6966,idArchivoImagen,".$idRegistroSolicitud." FROM 9058_imagenesControlGaleria 
			WHERE idElementoFormulario=6940 AND idRegistro=".$idRegistro;
	return $con->ejecutarConsulta($consulta);
	
	
}

function mostrarGeneracionDocumentosV2($idReferencia,$actor)
{
	global $con;
	
	/*$consulta="SELECT actor FROM 944_actoresProcesoEtapa WHERE idActorProcesoEtapa=".$actor;
	$rol=$con->obtenerValor($consulta);*/
	if(esSubdirectorCausasSimilar($actor)==1)
	{
		$consulta="SELECT iFormulario FROM _185_tablaDinamica WHERE id__185_tablaDinamica=".$idReferencia;
		$iFormulario=$con->obtenerValor($consulta);
		if($iFormulario=="N/E")
		{
			return 1;
		}
	}
	return 0;

}


function determinarRutaPrescripcion($idFormulario,$idRegistro)
{
	global $con;
	$consulta="SELECT statusOrden,motivoCancelacion,comentariosAdicionales,fechaCumplimiento,idReferencia FROM 
				_447_tablaDinamica WHERE idReferencia=".$idRegistro;
	$fRegistro=$con->obtenerPrimeraFila($consulta);
	$statusOrden=$fRegistro[0];
	
	
	$consulta="SELECT carpetaAdministrativa,oficio,resolucion,actaMinima FROM _434_tablaDinamica WHERE id__434_tablaDinamica=".$fRegistro[4];
	$fRegistroOrden=$con->obtenerPrimeraFila($consulta);
	
	$motivo="";
	$numEtapa=0;
	switch($statusOrden)
	{
		case 1:  	//Cumplimientada
			$numEtapa=3;
			$motivo="Orden cumplimentada el ".date("d/m/Y",strtotime($fRegistro[3])).". ".$fRegistro[2];
			break;
		case 2:		//Prescrita
			$numEtapa=4;
			$motivo="Fecha de prescripciÛn alcanzada. ".$fRegistro[2];
			break;
		case 3:	//Cancelada
			$numEtapa=5;
			$motivo=$fRegistro[1];
			break;
	}
	
	if(cambiarEtapaFormulario($idFormulario,$idRegistro,$numEtapa,"",-1,"NULL","NULL",794))
	{
		
		$consulta="UPDATE 7034_prescripciones SET fechaCancelacion='".date("Y-m-d H:i:s")."',idResponsableCancelacion=".$_SESSION["idUsr"].
				",motivoCancelacion='".cv($motivo)."',situacion=2 WHERE idFormulario=".$idFormulario." AND idReferencia=".$idRegistro;
		return $con->ejecutarConsulta($consulta);
	}
}

function adjuntarDocumentosProcesoOrden($idFormulario,$idRegistro)
{
	global $con;
	
	
	$consulta="SELECT carpetaAdministrativa,oficio,resolucion,actaMinima FROM _434_tablaDinamica WHERE id__434_tablaDinamica=".$idRegistro;
	$fRegistroOrden=$con->obtenerPrimeraFila($consulta);
	convertirDocumentoUsuarioDocumentoResultadoProceso($fRegistroOrden[1],434,$idRegistro,"Oficio_orden",14);
	convertirDocumentoUsuarioDocumentoResultadoProceso($fRegistroOrden[2],434,$idRegistro,"Resolucion_orden",3);
	convertirDocumentoUsuarioDocumentoResultadoProceso($fRegistroOrden[3],434,$idRegistro,"Acta_minima",12);
}

function registrarAlertaNotificacionSistema($arrValores)
{
	global $con;
	
	$idTitularAlerta="NULL";
	if(isset($arrValores["idTitularAlerta"]))
		$idTitularAlerta=$arrValores["idTitularAlerta"];
	
	$x=0;
	$consulta[$x]="begin";
	$x++;
	$consulta[$x]="INSERT INTO 7036_alertasNotificaciones(carpetaAdministrativa,situacion,descripcion,valorReferencia1,valorReferencia2,
			fechaRegistro,responsableRegistro,tipoAlerta,fechaAlerta,idTitularAlerta) values('".$arrValores["carpetaAdministrativa"]."',1,'".cv($arrValores["descripcion"]).
			"',".($arrValores["valorReferencia1"]==""?"NULL":("'".cv($arrValores["valorReferencia1"]))."'").",".
			($arrValores["valorReferencia2"]==""?"NULL":"'".cv($arrValores["valorReferencia2"])."'").",'".date("Y-m-d H:i:s")."',".$_SESSION["idUsr"].",
			".$arrValores["tipoAlerta"].",'".$arrValores["fechaAlerta"]."',".$idTitularAlerta.")";
	$x++;
	$consulta[$x]="set @idRegistro:=(select last_insert_id())";
	$x++;
	$consulta[$x]="INSERT INTO 7037_recordatoriosPreviosNotificacion(fechaRecordatorio,idAlertaNotificacion) VALUES('".
				(strpos($arrValores["fechaAlerta"],":")==false?$arrValores["fechaAlerta"]." 00:00:01":$arrValores["fechaAlerta"])."',@idRegistro)";
	$x++;
	if(isset($arrValores["fechasRecordatorio"] ))
	{
		foreach($arrValores["fechasRecordatorio"] as $fecha)
		{
			$consulta[$x]="INSERT INTO 7037_recordatoriosPreviosNotificacion(fechaRecordatorio,idAlertaNotificacion) VALUES('".$fecha."',@idRegistro)";
			$x++;
		}
	}
	$consulta[$x]="commit";
	$x++;

	return $con->ejecutarBloque($consulta);
	
}

function cancelarAlertaNotificacionSistema($arrValores)
{
	global $con;
	$x=0;
	$consulta[$x]="begin";
	$x++;
	$consulta[$x]="update 7036_alertasNotificaciones set situacion=2,fechaCancelacion='".date("Y-m-d H:i:s")."',responsableCancelacion=".$_SESSION["idUsr"].
				",motivoCancelacion='".cv($arrValores["motivoCancelacion"])."' WHERE idRegistro=".$arrValores["idRegistro"];
	$x++;
	$consulta[$x]="commit";
	$x++;
	
	return $con->ejecutarBloque($consulta);
	
}

function marcarAlertaNotificacionSistema($arrValores)
{
	global $con;
	$x=0;
	$consulta[$x]="begin";
	$x++;
	$consulta[$x]="update 7036_alertasNotificaciones set situacion=3,fechaCancelacion='".date("Y-m-d H:i:s")."',responsableCancelacion=".$_SESSION["idUsr"].
				",motivoCancelacion='".cv($arrValores["comentariosAdicionales"])."' WHERE idRegistro=".$arrValores["idRegistro"];
	$x++;
	$consulta[$x]="commit";
	$x++;
	
	return $con->ejecutarBloque($consulta);
	
}

function formatearPena($idPena)
{
	global $con;
	$consulta="SELECT * FROM 7024_registroPenasSentenciaEjecucion WHERE idRegistro=".$idPena;
	$filaPena=$con->obtenerPrimeraFilaAsoc($consulta);

	$consulta="SELECT pena,tipoEntrada,privativaLibertad FROM _406_tablaDinamica WHERE id__406_tablaDinamica=".$idPena;	

	$fConfPena=$con->obtenerPrimeraFilaAsoc($consulta);

	$descripcion='<b>'.$fConfPena["pena"].'</b>';
	$esPrivativaLibertad=$fConfPena["privativaLibertad"];
	$tipoEntrada=$fConfPena["tipoEntrada"];
	$objDetalle=$filaPena["objDetalle"];
	$periodoCompurga="";
	$periodoPena="[]";
	$lblDetallePena="";
	if($objDetalle!="")
	{
		$oDetalle=json_decode($objDetalle);

		if(isset($oDetalle->monto))
		{
			$lblDetallePena=", Monto: $ ".number_format($oDetalle->monto);
		}
		else
		{
			$arrPena=array();
			$arrPena[0]=$oDetalle->anios;
			$arrPena[1]=$oDetalle->meses;
			$arrPena[2]=$oDetalle->dias;

			$periodoCompurga=$arrPena[0]."_".$arrPena[1]."_".$arrPena[2];

			$lblDetallePena=", Periodo a compurgar: ".convertirLeyendaComputo($arrPena);

		}

	}
	$descripcion.=$lblDetallePena;
	return $descripcion;
}

function marcarSolicitudCopiasEnEsperaEntrega($idFormulario,$idRegistro)
{
	global $con;
	$consulta="UPDATE _442_tablaDinamica SET idEstado=2 WHERE idReferencia=".$idRegistro." AND idEstado=1";
	return $con->ejecutarConsulta($consulta);
}

function registrarEntregaCopias($idFormulario,$idRegistro)
{
	global $con;
	$consulta="SELECT idReferencia FROM _449_tablaDinamica WHERE id__449_tablaDinamica=".$idRegistro;
	$idReferencia=$con->obtenerValor($consulta);
	
	$consulta="SELECT SUM(totalEntregadas) FROM _449_tablaDinamica WHERE idReferencia=".$idReferencia." and idEstado=2";
	$totalEntregado=$con->obtenerValor($consulta);
	
	$consulta="SELECT totalCopias,idReferencia FROM _442_tablaDinamica WHERE id__442_tablaDinamica=".$idReferencia;
	$fSolicitudCopias=$con->obtenerPrimeraFila($consulta);
	$restante=$fSolicitudCopias[0]-$totalEntregado;
	if($restante<=0)
	{
		cambiarEtapaFormulario(442,$idReferencia,3,"",-1,"NULL","NULL",812);
		
		$consulta="SELECT SUM(totalCopias) FROM _442_tablaDinamica WHERE idReferencia=".$fSolicitudCopias[1];
		$totalCopiasBase=$con->obtenerValor($consulta);
		$consulta="SELECT SUM(totalCopias) FROM _442_tablaDinamica WHERE idReferencia=".$fSolicitudCopias[1]." and idEstado=3";
		$totalCopiasEntregadas=$con->obtenerValor($consulta);
		$diferencia=$totalCopiasBase-$totalCopiasEntregadas;
		if($diferencia<=0)
		{
			cambiarEtapaFormulario(441,$fSolicitudCopias[1],3,"",-1,"NULL","NULL",806);
		}
		
	}
	//echo "window.parent.parent.regresar1Pagina()";
	
	return true;
}

function generarFolioCarpetaApelacion($idFormulario,$idRegistro)
{
	
	global $con;
	
	$anio=date("Y");
	
	$consulta="SELECT carpetaApelacion,carpetaAdministrativa FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$fDatosCarpeta=$con->obtenerPrimeraFila($consulta);
	if($fDatosCarpeta[0]=="N/E")
		$fDatosCarpeta[0]="";
	if($fDatosCarpeta[0]!="")
		return true;
		
	$consulta="SELECT unidadGestion,idActividad,carpetaInvestigacion,etapaProcesalActual FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$fDatosCarpeta[1]."'";
	$fDatosCarpetaBase=$con->obtenerPrimeraFila($consulta);
	$codigoInstitucion=$fDatosCarpetaBase[0];
	$idActividadBase=$fDatosCarpetaBase[1];
	$carpetaInvestigacion=$fDatosCarpetaBase[2];
	$etapaProcesalActual=$fDatosCarpetaBase[3];
	$idUnidadGestion=-1;
	$consulta="SELECT id__17_tablaDinamica FROM _17_tablaDinamica WHERE claveUnidad='".$codigoInstitucion."'";
	$idUnidadGestion=$con->obtenerValor($consulta);
	if($idUnidadGestion=="")
		$idUnidadGestion=-1;
		
			
	$query="SELECT claveUnidad,claveFolioCarpetas,siglasClave FROM _17_tablaDinamica WHERE id__17_tablaDinamica=".$idUnidadGestion;
	$fDatosUnidadBase=$con->obtenerPrimeraFila($query);
	$cveUnidadGestion=$fDatosUnidadBase[0];
	
	
	$carpetaAdministrativa=obtenerSiguienteCarpetaAdministrativa($idUnidadGestion,$anio,4,$idFormulario,$idRegistro);
	$consulta=array();
	$x=0;
	$consulta[$x]="begin";
	$x++;
	$consulta[$x]="INSERT INTO 7006_carpetasAdministrativas(carpetaAdministrativa,fechaCreacion,responsableCreacion,idFormulario,
					idRegistro,unidadGestion,etapaProcesalActual,idActividad,carpetaAdministrativaBase,tipoCarpetaAdministrativa,
					carpetaInvestigacion,llaveCarpetaInvestigacion,unidadGestionOriginal) 
					VALUES('".$carpetaAdministrativa."','".date("Y-m-d H:i:s")."',".$_SESSION["idUsr"].",".$idFormulario.",'".
					$idRegistro."','".$cveUnidadGestion."',".$etapaProcesalActual.",".$idActividadBase.",'".$fDatosCarpeta[1]."',4,(SELECT UPPER('".
					$carpetaInvestigacion."')),'".cv(generarLlaveCarpetaInvestigacion($carpetaInvestigacion))."','".$cveUnidadGestion."')";
	$x++;
	$consulta[$x]="update _".$idFormulario."_tablaDinamica set carpetaApelacion='".$carpetaAdministrativa."' where id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$x++;
	
	$consulta[$x]="commit";
	$x++;
	
	if($con->ejecutarBloque($consulta))
	{
		
		$query="SELECT idDocumento FROM 9074_documentosRegistrosProceso WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro;
		$rDocumentos=$con->obtenerFilas($query);
		while($fDocumento=mysql_fetch_row($rDocumentos))
		{
			registrarDocumentoCarpetaAdministrativa($carpetaAdministrativa,$fDocumento[0],$idFormulario,$idRegistro);	
		}
		
		return true;
		
	}
		
	return false;
	
}

function registrarNotificacionCausas($idFormulario,$idRegistro)
{
	global $con;
	
	$consulta="SELECT * FROM _385_tablaDinamica WHERE id__385_tablaDinamica=".$idRegistro;
	$fRegistro=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$consulta="SELECT * FROM _412_tablaDinamica WHERE idReferencia=".$idRegistro;
	$fRegistroComplementario=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$arrDocumentos=NULL;
	$arrValores=array();
	$arrValores["carpetaOrigen"]=$fRegistro["carpetaAdministrativa"];
	$arrValores["carpetaEjecucion"]=$fRegistro["carpetaEjecucion"];
	$arrValores["copiaCertificadaSentencia"]=$fRegistroComplementario["setencia"];
	$arrValores["copiaSentenciaReconoce"]=$fRegistroComplementario["auto"];
	$arrValores["actaMinima"]=$fRegistroComplementario["actaMinima"];
	$arrValores["iFormulario"]=$idFormulario;
	$arrValores["iRegistro"]=$idRegistro;
	
	$consulta="UPDATE 7006_carpetasAdministrativas SET situacion=1 WHERE carpetaAdministrativa='".$fRegistro["carpetaEjecucion"]."'";
	$con->ejecutarConsulta($consulta);


	if(($fRegistroComplementario["setencia"]!="")&&($fRegistroComplementario["setencia"]!="-1"))
	{
		//convertirDocumentoUsuarioDocumentoResultadoProceso();
		convertirDocumentoUsuarioDocumentoResultadoProceso($fRegistroComplementario["setencia"],-1,-1,"",3);
		registrarDocumentoCarpetaAdministrativa($fRegistro["carpetaEjecucion"],$fRegistroComplementario["setencia"],$idFormulario,$idRegistro);
	}
		
	if(($fRegistroComplementario["auto"]!="")&&($fRegistroComplementario["auto"]!="-1"))
	{
		convertirDocumentoUsuarioDocumentoResultadoProceso($fRegistroComplementario["auto"],-1,-1,"",3);
		registrarDocumentoCarpetaAdministrativa($fRegistro["carpetaEjecucion"],$fRegistroComplementario["auto"],$idFormulario,$idRegistro);
	}
	if(($fRegistroComplementario["actaMinima"]!="")&&($fRegistroComplementario["actaMinima"]!="-1"))
	{
		convertirDocumentoUsuarioDocumentoResultadoProceso($fRegistroComplementario["actaMinima"],-1,-1,"",12);
		registrarDocumentoCarpetaAdministrativa($fRegistro["carpetaEjecucion"],$fRegistroComplementario["actaMinima"],$idFormulario,$idRegistro);
	}
	$consulta="select count(*) from _453_tablaDinamica where idReferencia=".$idRegistro;
	$nReg=$con->obtenerValor($consulta);
	if($nReg==0)
		crearInstanciaRegistroFormulario(453,$idRegistro,2,$arrValores,$arrDocumentos,-1,819);
}

function registrarRelacionSetenciadoEjecucion($idFormulario,$idRegistro)
{
	global $con;
	$consulta="SELECT * FROM _405_tablaDinamica WHERE id__405_tablaDinamica=".$idRegistro;
	$fRegistro=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$consulta="SELECT idEstado FROM _385_tablaDinamica WHERE id__385_tablaDinamica=".$fRegistro["idReferencia"];
	$idEstado=$con->obtenerValor($consulta);
	if($idEstado<7)
		return;
	$consulta="SELECT COUNT(*) FROM 7005_relacionFigurasJuridicasSolicitud WHERE idActividad=".$fRegistro["idActividadEjecucion"].
			" AND idParticipante=".$fRegistro["sentenciado"]." and idFiguraJuridica=4";
	$nReg=$con->obtenerValor($consulta);
	
	if($nReg==0)
	{
		$consulta="INSERT INTO 7005_relacionFigurasJuridicasSolicitud(idActividad,idParticipante,idFiguraJuridica)
					VALUES(".$fRegistro["idActividadEjecucion"].",".$fRegistro["sentenciado"].",4)";
		$con->ejecutarConsulta($consulta);
	}
	return;
	
}


function mostrarDatosNotificacionApelacion($idReferencia)
{
	global $con;
	$consulta="SELECT domicilioDiferenteNotificaciones FROM _451_tablaDinamica WHERE id__451_tablaDinamica=".$idReferencia;
	return $con->obtenerValor($consulta);
	
}


function validarDocumentoAdjuntoEnvioPromocionAmparo($idFormulario,$idRegistro)
{
	global $con;
	
	$consulta="SELECT idReferencia FROM _460_tablaDinamica WHERE id__460_tablaDinamica=".$idRegistro;
	$idReferencia=$con->obtenerValor($consulta);
	$consulta="SELECT COUNT(*) FROM _460_tablaDinamica WHERE idReferencia=".$idReferencia;
	$nPromociones=$con->obtenerValor($consulta);
	if($nPromociones>1)
	{
		return  validarDocumentoAdjuntoEnvioSolicitud($idFormulario,$idRegistro);
	}
	return true;
	
	
	
}

function registrarDocumentoAdjuntoEnvioPromocionAmparo($idFormulario,$idRegistro)
{
	global $con;
	
	$consulta="SELECT idReferencia FROM _460_tablaDinamica WHERE id__460_tablaDinamica=".$idRegistro;
	$idReferencia=$con->obtenerValor($consulta);
	$consulta="SELECT COUNT(*) FROM _460_tablaDinamica WHERE idReferencia=".$idReferencia;
	$nPromociones=$con->obtenerValor($consulta);
	if($nPromociones==1)
	{
		$consulta="INSERT INTO 9074_documentosRegistrosProceso(idFormulario,idRegistro,idDocumento,tipoDocumento)
					SELECT '460','".$idRegistro."',idDocumento,tipoDocumento FROM 9074_documentosRegistrosProceso WHERE idFormulario=346 
					AND idRegistro=".$idReferencia." and idDocumento not in
					(select idDocumento from 9074_documentosRegistrosProceso where idFormulario=460 and idRegistro=".$idRegistro.")";
		return $con->ejecutarConsulta($consulta);	
	}
	return true;
	
	
	
}


function registrarAlertasPromocionAmparo($idFormulario,$idRegistro)
{
	global $con;
	$fechaActual=strtotime(date("Y-m-d H:i:s"));
	$consulta="SELECT * FROM _460_tablaDinamica WHERE id__460_tablaDinamica=".$idRegistro;
	$fPromocion=$con->obtenerPrimeraFilaAsoc($consulta);
	$iRegistroAmparo=$fPromocion["idReferencia"];
	if($iRegistroAmparo==-1)
	{
		$consulta="SELECT id__346_tablaDinamica FROM _346_tablaDinamica WHERE carpetaAmparo='".$fPromocion["carpetaAdministrativa"]."'";
		$iRegistroAmparo=$con->obtenerValor($consulta);
		$consulta="UPDATE _460_tablaDinamica SET idReferencia=".$iRegistroAmparo.",idProcesoPadre=164 WHERE id__460_tablaDinamica=".$idRegistro;
		$con->ejecutarConsulta($consulta);
	}
	
	
	if($fPromocion["resolverAmparoTransitorio"]==1)
	{
		$consulta="INSERT INTO _537_tablaDinamica(idReferencia,fechaCreacion,responsable,idEstado,codigoInstitucion,
				existeJuezConoce,juezConoce,comentariosAdicionales)
				SELECT ".$iRegistroAmparo.",fechaCreacion,responsable,idEstado,codigoInstitucion,existeJuezConoce,juezConoce,comentariosAdicionales 
				FROM _536_tablaDinamica WHERE idReferencia=".$idRegistro;
		$con->ejecutarConsulta($consulta);
	}
	
	if(($fPromocion["lblDiaHoraPlazoInforme"]!="")&&($fPromocion["lblDiaHoraPlazoInforme"]!="N/E"))
	{
		$arrValoresAlerta=array();
		$arrValoresAlerta["carpetaAdministrativa"]=$fPromocion["carpetaAdministrativa"];
		$arrValoresAlerta["descripcion"]=utf8_encode("Plazo de atenciÛn a promociÛn de amparo por cumplirse");
		$arrValoresAlerta["valorReferencia1"]=$idFormulario;
		$arrValoresAlerta["valorReferencia2"]=$idRegistro;
		$arrValoresAlerta["tipoAlerta"]=4;
		$arrValoresAlerta["fechaAlerta"]=$fPromocion["lblDiaHoraPlazoInforme"];
		
		$fechaSegundoRecordatorio=strtotime("-24 hours",strtotime($fPromocion["lblDiaHoraPlazoInforme"]));
		if($fechaActual<$fechaSegundoRecordatorio)
		{
			$arrValoresAlerta["fechasRecordatorio"]=array();
			array_push($arrValoresAlerta["fechasRecordatorio"],date("Y-m-d H:i:s",$fechaSegundoRecordatorio));
		}

		registrarAlertaNotificacionSistema($arrValoresAlerta);
	}
	return true;
	
	
	
}

function restaurarDocumentoProceso($idFormulario,$idRegistro)
{
	global $con;
	global $directorioInstalacion;
	
	$consulta="SELECT idRegistroContenidoReferencia FROM 7007_contenidosCarpetaAdministrativa WHERE 
				idFormulario=".$idFormulario." AND idRegistro=".$idRegistro." and tipoContenido=1";
	$idDocumento=$con->obtenerValor($consulta);
	
	$consulta="SELECT solicitudXML FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;

	$solicitud=bD($con->obtenerValor($consulta));
	
	if($solicitud=="")
		$xmlSolicitud=obtenerContenidoArchivoDatosPeticion($idFormulario,$idRegistro);
	
	$cXML=simplexml_load_string($solicitud);

	$datosDocumento=array();	
	if(isset($cXML->documentoAnexo))		
	{
		$datosDocumento["nombreDocumento"]=(string)$cXML->documentoAnexo[0]->nombreDocumento;
		$datosDocumento["descripcionDocumento"]=(string)$cXML->documentoAnexo[0]->descripcionDocumento;
		$datosDocumento["contenido"]=(string)$cXML->documentoAnexo[0]->contenido;
	}
	else
	{
		$datosDocumento["nombreDocumento"]=(string)$cXML->DatosSolicitud[0]->nombreDocumento;
		$datosDocumento["nombreDocumento"]=str_replace(".","",$datosDocumento["nombreDocumento"]);
		$datosDocumento["descripcionDocumento"]="";//(string)$cXML->DatosSolicitud[0]->descripcionDocumento;
		$datosDocumento["contenido"]=(string)$cXML->DatosSolicitud[0]->documentoAdjunto;
	}
	
	
	if($datosDocumento["contenido"]!="")
	{
		$directorioDestino=str_replace("/","\\",$directorioInstalacion.'\\repositorioDocumentos\\documento_'.$idDocumento);		
		$datos=bD($datosDocumento["contenido"]);
		$f=file_put_contents($directorioDestino,$datos);
		
		if($f)
		{
			echo "Listo!!1";
		}
		else
			echo "Error!!!";
	}	
	
}

function moverDocumentoRepositorio($idDocumento,$carpetaDestino)
{
	global $con;
	global $baseDir;
	if(file_exists($baseDir."/repositorioDocumentos/documento_".$idDocumento))
	{
		if(copy($baseDir."/repositorioDocumentos/documento_".$idDocumento,$carpetaDestino."/documento_".$idDocumento))
		{
			unlink($baseDir."/repositorioDocumentos/documento_".$idDocumento);
			return true;
		}
		return false;
	}
	return true;
	
}

function moverDocumentoRepositorioFecha($fechaInicio,$fechaFin,$carpetaDestino)
{
	global $con;
	$consulta="SELECT idArchivo FROM 908_archivos WHERE fechaCreacion>='".$fechaInicio."' AND fechaCreacion<='".$fechaFin."'";
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		if(!moverDocumentoRepositorio($fila[0],$carpetaDestino))
			return false;
		if(!moverDocumentoRepositorio($fila[0].".pkcs7",$carpetaDestino))
			return false;
	}
	return true;
}


function esCarpetaEjecucionProceso($idFormulario,$idReferencia)
{
	global $con;
	$consulta="SELECT carpetaAdministrativa FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idReferencia;
	$cAdministrativa=$con->obtenerValor($consulta);
	$consulta="SELECT tipoCarpetaAdministrativa FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$cAdministrativa."'";
	$tCarpeta=$con->obtenerValor($consulta);
	
	if($tCarpeta==6)
	{
		return 1;
	}
	
	return 0;
}

function obtenerInformacionAudienciaIncompetenciaHistorial($idEvento)
{
	global $con;	
	global $arrDiasSemana;
	global $arrMesLetra;
	$arrResultado=array();
	
	$arrResultado["incompetencia"]=1;
	$consulta="SELECT * FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$idEvento;
	$fEvento=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$consulta="SELECT tipoAudiencia FROM _4_tablaDinamica WHERE id__4_tablaDinamica=".$fEvento["tipoAudiencia"];
	$arrResultado["tipoAudiencia"]=$con->obtenerValor($consulta);
	$dEvento=obtenerDatosEventoAudiencia($idEvento);
	$fechaEvento=strtotime($fEvento["fechaAsignacion"]);
	$consulta="SELECT carpetaAdministrativa FROM 7007_contenidosCarpetaAdministrativa WHERE tipoContenido=3 
			AND idRegistroContenidoReferencia=".$idEvento;
	$arrResultado["carpetaJudicial"]=$con->obtenerValor($consulta);
	$arrResultado["idEventoAudiencia"]=$idEvento;
	
	$arrResultado["fechaAudiencia"]=utf8_encode($arrDiasSemana[date("w",$fechaEvento)])." ".date("d",$fechaEvento)." de ".$arrMesLetra[(date("m",$fechaEvento)*1)-1]." de ".date("Y",$fechaEvento);
	
	$arrResultado["fechaAudienciaRaw"]=date("Y-m-d",$fechaEvento);
	$consulta="SELECT descripcionSituacion FROM 7011_situacionEventosAudiencia WHERE idSituacion=".$fEvento["situacion"];
	$arrResultado["situacion"]=$con->obtenerValor($consulta);
	$arrResultado["desarrollo"]="";
	
	
	$arrResultado["unidadGestion"]=$dEvento->unidadGestion;
	
	$arrResultado["idFormulario"]=$fEvento["idFormulario"];
	$arrResultado["idRegistro"]=$fEvento["idRegistroSolicitud"];
	$arrResultado["arrDocumentos"]=array();
	
	if($arrResultado["idFormulario"]=="")
		$arrResultado["idFormulario"]=-1;
	
	if($arrResultado["idRegistro"]=="")
		$arrResultado["idRegistro"]=-1;
		
	$consulta="SELECT a.idArchivo,a.nomArchivoOriginal FROM 9074_documentosRegistrosProceso d,908_archivos a WHERE 
			idFormulario=".$arrResultado["idFormulario"]." AND idRegistro=".$arrResultado["idRegistro"]." AND a.idArchivo=d.idDocumento";
	$rDocumento=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($rDocumento))
	{
		array_push($arrResultado["arrDocumentos"],$fila);
	}
	
	
	
	return $arrResultado;
}

function esAdscripcionUnidadGestion()
{
	global $con;
	$consulta="SELECT COUNT(*) FROM _17_tablaDinamica WHERE claveUnidad='".$_SESSION["codigoInstitucion"]."' AND cmbCategoria=1";
	return $con->obtenerValor($consulta);
}

function esAdscripcionUnidadEjecucion()
{
	global $con;
	$consulta="SELECT COUNT(*) FROM _17_tablaDinamica u,_17_gridDelitosAtiende d WHERE 
			claveUnidad='".$_SESSION["codigoInstitucion"]."' and d.idReferencia=u.id__17_tablaDinamica and d.tipoDelito 
			like '%E%'";
	$nReg=$con->obtenerValor($consulta);
	return ($nReg>0?1:0);
}

function generarLlaveCarpetaInvestigacion($cI)
{
	$llave="";

	
	$llaveAux=str_replace(" ","",mb_strtoupper($cI));
	$arrLlaves=explode("/",$llaveAux);
	foreach($arrLlaves as $token)
	{
		$temp="";
		$arrToken=explode("-",$token);
		foreach($arrToken as $t)
		{
			$temp.=removerCerosIzquierda($t);
			
		}

		if($llave=="")
			$llave=$temp;
		else
			$llave.="_".$temp;
		
	}
	
	
	
	return $llave;
	
	
}

function removerCerosIzquierda($token)
{
	$temp="";
	$token=trim($token);
	$x=0;
	$encontrado=false;
	for($x=0;$x<strlen($token);$x++)
	{

		if($token[$x]!="0")
		{
			$encontrado=true;
			break;
		}
	}
	if($encontrado)
	{
		$token=substr($token,$x);
		return $token;
	}
	else
		return "";
}

function existeCarpetaAdministrativa($carpetaAdministrativa,$UGA="")
{
	global $con;
	$arrVariantes[0]="-SD";
	$arrVariantes[1]="-TM";
	$arrVariantes[2]="-OA";
	$arrVariantes[3]="-OC";
	//$arrVariantes[4]="-LN";
	$arrVariantes[4]="-AI";
	$carpetaAux=$carpetaAdministrativa;

	foreach($arrVariantes as $sufijo)
	{
		$carpetaAux=str_replace($sufijo,"",$carpetaAux);
	}
	$cadCarpetaJudicial="'".$carpetaAdministrativa."','".$carpetaAux."'";
	foreach($arrVariantes as $sufijo)
	{
		$cadCarpetaJudicial.=",'".$carpetaAux.$sufijo."'";

	}
	
	
	
	$consulta="SELECT COUNT(*) FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa in (".$cadCarpetaJudicial.")";
	
	if($UGA!="")
		$consulta.=" and unidadGestion='".$UGA."'";

	$nReg=$con->obtenerValor($consulta);

	return $nReg>0;
}

function obtenerSiguienteCarpetaAdministrativa($idUnidadGestion,$anio,$tipoCarpeta,$idFormulario,$idRegistro,$considerarUGA=false)
{
	global $con;
	$UGA="";
	
	if($considerarUGA)
	{
		$consulta="SELECT claveUnidad FROM _17_tablaDinamica WHERE id__17_tablaDinamica=".$idUnidadGestion;
		$UGA=$con->obtenerValor($consulta);
	}
	
	$consulta="SELECT funcionGeneradoraFolio FROM _465_tablaDinamica WHERE idReferencia=".$idUnidadGestion." AND tipoCarpeta=".$tipoCarpeta;
	$funcionGeneradoraFolio=$con->obtenerValor($consulta);
	if($funcionGeneradoraFolio=="")
		return "ERROR SIN GENERADOR DE CARPETAS";
	
	
	$cache=NULL;
	$cadObj='{"idUnidadGestion":"'.$idUnidadGestion.'","anio":"'.$anio.'","tipoCarpeta":"'.$tipoCarpeta.
			'","folioActual":"0","idFormulario":"'.$idFormulario.'","idRegistro":"'.$idRegistro.'"}';
	
	$obj=json_decode($cadObj);
	$arrDatosCarpeta=resolverExpresionCalculoPHP($funcionGeneradoraFolio,$obj,$cache);

	while(existeCarpetaAdministrativa($arrDatosCarpeta[0],$UGA))
	{
		$obj->folioActual=$arrDatosCarpeta[1]+1;
		$arrDatosCarpeta=resolverExpresionCalculoPHP($funcionGeneradoraFolio,$obj,$cache);
	}
	
	
	
	
	return $arrDatosCarpeta[0];
	
	
	
}

function aplicarRemisionCarpetaJudicial($idFormulario,$idRegistro)
{
	global $con;
	
	$arrCamposCarpeta=array();
	$arrCamposCarpeta[0]="carpetaJudicial";
	$arrCamposCarpeta[1]="carpetaAdministrativa";
	$arrCamposCarpeta[2]="numeroCarpetaAdministrativa";
	$arrCamposCarpeta[3]="carpetaEjecucion";
	
	$query="SELECT * FROM _466_tablaDinamica WHERE id__466_tablaDinamica=".$idRegistro;
	$fRegistro=$con->obtenerPrimeraFilaAsoc($query);
	
	$query="SELECT idFormulario,idRegistro,unidadGestion,fechaCreacion,idJuezTitular FROM 7006_carpetasAdministrativas 
			WHERE carpetaAdministrativa='".$fRegistro["carpetaJudicial"]."'";

	$fDatosRegistro=$con->obtenerPrimeraFila($query);
	if(!$fDatosRegistro)
		return true;
		
	$idUnidadGestion=-1;
	$idEdificio=-1;	
	$tipoCarpeta=$fRegistro["tipoCarpetaJudicial"];
	if($fRegistro["tipoAsignacion"]	==1)
	{
		
		$cveUnidadDestino=$fRegistro["unidadGestionDestino"];
		
		
		$query="SELECT id__17_tablaDinamica,idReferencia FROM _17_tablaDinamica WHERE claveUnidad='".$cveUnidadDestino."'";
		$fDatosUgaDestino=$con->obtenerPrimeraFila($query);
		
		$idUnidadGestion=$fDatosUgaDestino[0];
		$idEdificio=$fDatosUgaDestino[1];
	}
	else
	{
		$idEdificio=$fRegistro["inmuebleDestino"];
		$lista=$fRegistro["uGASDestino"];
		
		switch($tipoCarpeta)
		{
			case 1: //Carpeta de Control
				$query="SELECT * FROM _46_tablaDinamica WHERE id__46_tablaDinamica=".$fDatosRegistro[1];
				$fDatosSolicitud=$con->obtenerPrimeraFilaAsoc($query);
				$tipoAudiencia=$fDatosSolicitud["tipoAudiencia"];
				$idUnidadGestion=obtenerUnidadGestionSiguienteAsignacion($tipoAudiencia,$lista);
			break;
			case 2: //Carpeta de Exhorto
				
				$arrUnidades=explode(",",$lista);
				if(sizeof($arrUnidades)>1)
				{
					$tipoAsignacion="";
					switch($fRegistro["tipoUnidadDestino"])
					{
						case 1:
							switch($fRegistro["inmuebleDestino"])
							{
								case 5:
									$tipoAsignacion="2A";
								break;
								case 7:
									$tipoAsignacion="3BNTE";
								break;
								case 8:
									$tipoAsignacion="3BOTE";
								break;
								case 9:
									$tipoAsignacion="3BSUR";
								break;
							}
						break;
						case 6:
							switch($fRegistro["inmuebleDestino"])
							{
								case 7:
									$tipoAsignacion="4ENTE-EX";
								break;
								case 12:
									$tipoAsignacion="4EOTE-EX";
								break;
								case 4:
									$tipoAsignacion="4ESUR-EX";
								break;
								
							}
						break;
					}
					$arrConfiguracion["tipoAsignacion"]=$tipoAsignacion;
					$arrConfiguracion["serieRonda"]="EX";
					$arrConfiguracion["universoAsignacion"]=$lista;
					$arrConfiguracion["idObjetoReferencia"]=-1;
					$arrConfiguracion["considerarDeudasMismaRonda"]=false;
					$arrConfiguracion["limitePagoRonda"]=0;
					$arrConfiguracion["escribirAsignacion"]=true;
					$arrConfiguracion["idFormulario"]=$fDatosRegistro[0];
					$arrConfiguracion["idRegistro"]=$fDatosRegistro[1];
					$resultado= obtenerSiguienteAsignacionObjeto($arrConfiguracion,true);
					$idUnidadGestion=$resultado["idUnidad"];
				}
				else
				{
					$idUnidadGestion=$lista;
				}
			break;
			case 5: //Carpeta Tribunal enjuiciamiento
			
			
				$consulta="SELECT carpetaAdministrativaBase FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$fRegistro["carpetaJudicial"]."'";
				$cAdministrativaBase=$con->obtenerValor($consulta);
			
				$consulta="SELECT id__17_tablaDinamica FROM 7006_carpetasAdministrativas c,_17_tablaDinamica u WHERE c.unidadGestion=u.claveUnidad
						AND c.carpetaAdministrativa='".$fRegistro["carpetaJudicial"]."'";
				$idUnidadBase=$con->obtenerValor($consulta);
				$arrUnidades=explode(",",$lista);
				if(sizeof($arrUnidades)>1)
				{
					$listaIng=$idUnidadBase;
					$validaConoceCausa=true;
					$encontrado=false;					
					while(!$encontrado)
					{
						
						$arrCarga=array();
						$query="SELECT claveUnidad,id__17_tablaDinamica FROM _17_tablaDinamica WHERE id__17_tablaDinamica IN(".$lista.") and
						id__17_tablaDinamica not in(".$listaIng.")";
						$rTribunales=$con->obtenerFilas($query);
						
						if($con->filasAfectadas>0)
						{
						
							while($fTribunal=mysql_fetch_row($rTribunales))
							{
								$query="SELECT COUNT(*) FROM 7006_carpetasAdministrativas WHERE fechaCreacion>='2018-09-03' 
											AND unidadGestion='".$fTribunal[0]. "' AND tipoCarpetaAdministrativa=5";
								$nCarpetas=$con->obtenerValor($query);		
								$arrCarga[$fTribunal[1]]=$nCarpetas;
								
							}
							
							$nCargaMinima=-1;
							foreach($arrCarga as $iTribunal=>$total)
							{
								if($nCargaMinima==-1)
								{
									$nCargaMinima=$total;
								}
								
								if($nCargaMinima>$total)
								{
									$nCargaMinima=$total;
								}
							}
							
							foreach($arrCarga as $iTribunal=>$total)
							{
								if($total==$nCargaMinima)
								{
									$idUnidadGestionTmp=$iTribunal;
									
									if(!$validaConoceCausa || !conoceJuezTribunalCarpeta($cAdministrativaBase,$iTribunal))	
									{
										$encontrado=true;
										$idUnidadGestion=$idUnidadGestionTmp;
										
									}
									break;
								}
							}
							$listaIng.=",".$idUnidadGestionTmp;
						}
						else
						{
							$listaIng=$idUnidadBase;
							$validaConoceCausa=false;
						}
					}
				}
				else
				{
					$idUnidadGestion=$lista;
				}
			break;
			case 6: //Carpeta EjecuciÛn
				$arrUnidades=explode(",",$lista);
				if(sizeof($arrUnidades)>1)
				{
					$idUnidadGestion=obtenerUnidadGestionSiguienteAsignacion(26,$lista);
				}
				else
				{
					$idUnidadGestion=$lista;
				}
				
			break;
			case 9: //Cuadernillo LN
				$arrUnidades=explode(",",$lista);
				if(sizeof($arrUnidades)>1)
				{
					$idUnidadGestion=obtenerUnidadGestionSiguienteAsignacion(1,$lista);
				}
				else
				{
					$idUnidadGestion=$lista;
				}
			break;
			
				
		}
		
		
	}
	
	$query="SELECT claveUnidad FROM _17_tablaDinamica WHERE id__17_tablaDinamica=".$idUnidadGestion;
	$cveUnidadDestino=$con->obtenerValor($query);
	$anio=date("Y");
	

	
	$query="SELECT id__17_tablaDinamica FROM _17_tablaDinamica WHERE claveUnidad='".$fDatosRegistro[2]."'";
	$idUGJOrigen=$con->obtenerValor($query);
	$tipoDelito="";
	
	
	$carpetaJudicial=obtenerSiguienteCarpetaAdministrativa($idUnidadGestion,$anio,$tipoCarpeta,$fDatosRegistro[0],$fDatosRegistro[1]);
	
	$x=0;
	$consulta=array();
	$consulta[$x]="begin";
	$x++;
	if($tipoCarpeta==6)
	{
		$query="SELECT juezResponsable FROM _621_tablaDinamica WHERE carpetaAdministrativa='".$carpetaJudicial.
				"' and idEstado>1 order by id__621_tablaDinamica desc";
		$idJuezEjecucion=$con->obtenerValor($query);
		if($idJuezEjecucion=="")
			$idJuezEjecucion=-1;
		if($idJuezEjecucion=="-1")
			$idJuezEjecucion=asignarJuezEjecucionCarpetaUnica($idUnidadGestion,$fDatosRegistro[0],$fDatosRegistro[1]);
		$consulta[$x]="UPDATE 7006_carpetasAdministrativas SET carpetaAdministrativa='".$carpetaJudicial."',unidadGestion='".$cveUnidadDestino.
					"',unidadGestionOriginal='".$cveUnidadDestino."',fechaCreacion='".date("Y-m-d H:i:s")."',
					idJuezTitular=".$idJuezEjecucion." WHERE carpetaAdministrativa='".$fRegistro["carpetaJudicial"]."'";
		$x++;
	}
	else
	{
		$consulta[$x]="UPDATE 7006_carpetasAdministrativas SET carpetaAdministrativa='".$carpetaJudicial."',unidadGestion='".$cveUnidadDestino.
					"',unidadGestionOriginal='".$cveUnidadDestino."',fechaCreacion='".date("Y-m-d H:i:s")."' WHERE carpetaAdministrativa='".$fRegistro["carpetaJudicial"]."'";
		$x++;
	}
	$consulta[$x]="UPDATE 7007_contenidosCarpetaAdministrativa SET carpetaAdministrativa='".$carpetaJudicial.
					"' WHERE carpetaAdministrativa='".$fRegistro["carpetaJudicial"]."'";
	$x++;
	
	$arrAudienicas=array();
	$query="SELECT * FROM 7000_eventosAudiencia WHERE idRegistroEvento IN(SELECT idRegistroContenidoReferencia 
			FROM 7007_contenidosCarpetaAdministrativa WHERE 
			carpetaAdministrativa='".$fRegistro["carpetaJudicial"]."' and tipoContenido=3)";
	$rAudiencias=$con->obtenerFilas($query);
	while($fAudiencias=mysql_fetch_assoc($rAudiencias))
	{
		if(($fAudiencias["situacion"]==1)||($fAudiencias["situacion"]==2)||($fAudiencias["situacion"]==4)||($fAudiencias["situacion"]==5))
		
			@notificarCancelacionEventoMAJO($fAudiencias["idRegistroEvento"]);
			
			
		$consulta[$x]="UPDATE 7001_asignacionesJuezAudiencia SET situacion=7 WHERE idEventoAudiencia=".$fAudiencias["idRegistroEvento"]." AND situacion=1";
		$x++;
			
		$consulta[$x]="UPDATE 7000_eventosAudiencia SET fechaEvento=NULL,horaInicioEvento=NULL,horaFinEvento=NULL,situacion=0,
						idEdificio=".$idEdificio.",idCentroGestion=".$idUnidadGestion.",idSala=NULL WHERE 
						idRegistroEvento=".$fAudiencias["idRegistroEvento"];
		$x++;
		$consulta[$x]="DELETE FROM 7001_eventoAudienciaJuez WHERE idRegistroEvento=".$fAudiencias["idRegistroEvento"];
		$x++;
		
		$arrAudienicas[$fAudiencias["idRegistroEvento"]]=$fAudiencias["tipoAudiencia"];
		
	}
	
	$query="SELECT idTableroControl FROM 9060_tablerosControl";
	$rTableros=$con->obtenerFilas($query);
	while($fTablero=mysql_fetch_row($rTableros))
	{
		$tablaTablero="9060_tableroControl_".$fTablero[0];
		if($con->existeTabla($tablaTablero))
		{
			foreach($arrCamposCarpeta as $campo)
			{
				if($con->existeCampo($campo,$tablaTablero))
				{
					$consulta[$x]="DELETE FROM ".$tablaTablero." WHERE ".$campo."='".$fRegistro["carpetaJudicial"]."'";
					$x++;
				}
			}
		}
	}
	
	$funcionFinal="";
	
	switch($tipoCarpeta)
	{
		case 1: //Carpeta de Control
			$query="SELECT * FROM _46_tablaDinamica WHERE id__46_tablaDinamica=".$fDatosRegistro[1];
			$fDatosSolicitud=$con->obtenerPrimeraFilaAsoc($query);
			
			$consulta[$x]="UPDATE _46_tablaDinamica SET carpetaAdministrativa='".$carpetaJudicial."' WHERE carpetaAdministrativa='".$fRegistro["carpetaJudicial"]."'";
			$x++;
			
			$idEtapa=2.7;
			$idFormularioActualiza=0;
			switch($fDatosSolicitud["iFormulario"])
			{
				case 307:
					$idFormularioActualiza=315;
					break;
				case 329:
					$idFormularioActualiza=330;
					$idEtapa=2.5;
					break;
				case 382:
					$idFormularioActualiza=383;
					$idEtapa=2.5;
					break;
				case 554:
					$idFormularioActualiza=0;
					$idEtapa=2.5;
					$consulta[$x]="UPDATE 3250_asignacionIncompetencias SET idUnidadDestino=".$idUnidadGestion." WHERE iFormulario=".$fDatosSolicitud["iFormulario"].
								" AND iRegistro=".$fDatosSolicitud["iReferencia"];
								
					$x++;
				break;
			}
			
			if($idFormularioActualiza!=0)
			{
				$consulta[$x]="UPDATE _".$idFormularioActualiza."_tablaDinamica SET carpetaAsignada='".$carpetaJudicial."',idUnidadReceptora=".$idUnidadGestion.
						" WHERE idReferencia=".$fDatosSolicitud["iReferencia"];
				$x++;
			}
			
			
			$funcionFinal='cambiarEtapaFormulario(46,'.$fDatosRegistro[1].','.$idEtapa.',"",-1,"NULL","NULL",264);';
			
		break;
		case 2: //Carpeta de Exhorto
			$tipoDelito="EX";
			$query="SELECT * FROM _92_tablaDinamica WHERE id__92_tablaDinamica=".$fDatosRegistro[1];
			$fDatosSolicitud=$con->obtenerPrimeraFilaAsoc($query);
			
			$consulta[$x]="UPDATE _92_tablaDinamica SET carpetaExhorto='".$carpetaJudicial."',codigoInstitucion='".$cveUnidadDestino.
					"' WHERE carpetaExhorto='".$fRegistro["carpetaJudicial"]."'";
			$x++;
			
			$idEtapa=2;
			$idFormularioActualiza=0;
			switch($fDatosSolicitud["iFormulario"])
			{
				case 345:
					$idFormularioActualiza=345;
				break;
				
				case 524:
					$idFormularioActualiza=524;
				break;
				
			}
			
			if($idFormularioActualiza!=0)
			{
				if($idFormularioActualiza==345)
				{
					$consulta[$x]="UPDATE _".$idFormularioActualiza."_tablaDinamica SET carpetaExhorto='".$carpetaJudicial."',unidadGestion=".$idUnidadGestion.
							" WHERE id__".$idFormularioActualiza."_tablaDinamica=".$fDatosSolicitud["iRegistro"];
				}
				else
				{
					$consulta[$x]="UPDATE _".$idFormularioActualiza."_tablaDinamica SET carpetaExhorto='".$carpetaJudicial."',unidadAsignada=".$idUnidadGestion.
							" WHERE id__".$idFormularioActualiza."_tablaDinamica=".$fDatosSolicitud["iRegistro"];
				}
				$x++;
			}
			
			
			$funcionFinal='cambiarEtapaFormulario(92,'.$fDatosRegistro[1].','.$idEtapa.',"",-1,"NULL","NULL",355);';
		break;
		case 3: //Carpeta de Amparo
			if(strpos($fRegistro["carpetaJudicial"],"AC")!==false)
				$tipoDelito="AC";
			else
				$tipoDelito="AT";
				
			$consulta[$x]="UPDATE _346_tablaDinamica SET carpetaAmparo='".$carpetaJudicial."' WHERE carpetaAmparo='".$fRegistro["carpetaJudicial"]."'";
			$x++;
			$funcionFinal='cambiarEtapaFormulario(346,'.$fDatosRegistro[1].',2,"",-1,"NULL","NULL",705);';
		break;
		case 4: //Carpeta ApelaciÛn
			$tipoDelito="APEL";
			$consulta[$x]="UPDATE _451_tablaDinamica SET carpetaApelacion='".$carpetaJudicial."' WHERE carpetaApelacion='".$fRegistro["carpetaJudicial"]."'";
			$x++;
			$funcionFinal='cambiarEtapaFormulario(451,'.$fDatosRegistro[1].',1.5,"",-1,"NULL","NULL",816);';
		break;
		case 5: //Carpeta Tribunal enjuiciamiento
			$tipoDelito="TE";
			if($fDatosRegistro[0]==320)
			{
				$consulta[$x]="UPDATE _320_tablaDinamica SET carpetaTribunalEnjuiciamiento='".$carpetaJudicial."',iUnidadReceptora='".$idUnidadGestion.
						"' WHERE carpetaTribunalEnjuiciamiento='".$fRegistro["carpetaJudicial"]."'";
				$x++;
				$idEtapa=3;
				$funcionFinal='cambiarEtapaFormulario(320,'.$fDatosRegistro[1].','.$idEtapa.',"",-1,"NULL","NULL",684);';
			}
			else
			{
				$consulta[$x]="UPDATE _538_tablaDinamica SET carpetaAdministrativa='".$carpetaJudicial."',unidadAsignada='".$idUnidadGestion.
						"' WHERE id__538_tablaDinamica='".$fDatosRegistro[1]."'";
				$x++;
				$idEtapa=2;
				$funcionFinal='cambiarEtapaFormulario(538,'.$fDatosRegistro[1].','.$idEtapa.',"",-1,"NULL","NULL",948);';
			}
		break;
		case 6: //Carpeta EjecuciÛn
		
			$tipoDelito="EJEC";
			if($fDatosRegistro[0]==385)
			{
				$consulta[$x]="UPDATE _385_tablaDinamica SET carpetaEjecucion='".$carpetaJudicial."',idUnidadReceptora='".$idUnidadGestion.
						"' WHERE carpetaEjecucion='".$fRegistro["carpetaJudicial"]."'";
				$x++;
				
				$funcionFinal='cambiarEtapaFormulario(385,'.$fDatosRegistro[1].',3,"",-1,"NULL","NULL",759);';
				$query="SELECT id__453_tablaDinamica FROM _453_tablaDinamica WHERE iFormulario=".$fDatosRegistro[0]." AND iRegistro=".$fDatosRegistro[1];
				$idRegistroAux=$con->obtenerValor($query);
				
				if($idRegistroAux!="")
				{
					$consulta[$x]="UPDATE _453_tablaDinamica SET carpetaEjecucion='".$carpetaJudicial."' where id__453_tablaDinamica=".$idRegistroAux;
					$x++;
					$funcionFinal.='cambiarEtapaFormulario(453,'.$idRegistroAux.',2,"",-1,"NULL","NULL",819);';
				}
			}
			else
			{
				$consulta[$x]="UPDATE _538_tablaDinamica SET carpetaAdministrativa='".$carpetaJudicial."',unidadAsignada='".$idUnidadGestion.
						"' WHERE id__538_tablaDinamica='".$fDatosRegistro[1]."'";
				$x++;
				$idEtapa=2;
				$funcionFinal='cambiarEtapaFormulario(538,'.$fDatosRegistro[1].','.$idEtapa.',"",-1,"NULL","NULL",948);';
			}
		break;
		case 9: //Cuadernillo LN
		
			$tipoDelito="LN";
			
			$consulta[$x]="UPDATE _491_tablaDinamica SET carpetaEjecucion='".$carpetaJudicial."',idUnidadDestino='".$idUnidadGestion.
					"' WHERE carpetaEjecucion='".$fRegistro["carpetaJudicial"]."'";
			$x++;
			
			$funcionFinal='cambiarEtapaFormulario(491,'.$fDatosRegistro[1].',3,"",-1,"NULL","NULL",880);';
			
			
		break;
		
			
	}
	
	$anioCarpetaOrigen=date("Y",strtotime($fDatosRegistro[3]));
	
	$folioCarpeta=obtenerNumerFolioCarpetaJudicial($fRegistro["carpetaJudicial"]);
	$folioCarpeta--;
	if($folioCarpeta<0)
		$folioCarpeta=0;
	
	$query="SELECT folioActual FROM 7004_seriesUnidadesGestion WHERE anio='".$anioCarpetaOrigen.
				"' AND idUnidadGestion=".$idUGJOrigen." AND tipoDelito='".$tipoDelito."'";
	$fActual=$con->obtenerValor($query);
	
	if($folioCarpeta<$fActual)
	{
	
		$consulta[$x]="UPDATE 7004_seriesUnidadesGestion SET folioActual=".$folioCarpeta." WHERE anio='".$anioCarpetaOrigen.
					"' AND idUnidadGestion=".$idUGJOrigen." AND tipoDelito='".$tipoDelito."'";
		$x++;
	}
	$consulta[$x]="UPDATE _466_tablaDinamica SET carpetaAdministrativaAsignada='".$carpetaJudicial."', fechaEnvio='".date("Y-m-d H:i:s").
				"',carpetaRemitida='".$fRegistro["carpetaJudicial"]."' where id__466_tablaDinamica=".$idRegistro;
	$x++;
	$consulta[$x]="commit";
	$x++;
	
	if($con->ejecutarBloque($consulta))
	{
		if($tipoCarpeta==6)
		{
			$arrDocumentos=array();
			$arrValores=array();
			$arrValores["unidadEjecucion"]=$fDatosRegistro[2];
			$arrValores["carpetaAdministrativa"]=$fRegistro["carpetaJudicial"];
			$arrValores["comentariosAdicionales"]="";
			$arrValores["juezResponsable"]=$fDatosRegistro[4];
			$idRegistroAux=crearInstanciaRegistroFormulario(621,-1,3,$arrValores,$arrDocumentos,-1,0);
		}
		
		$query="SELECT * FROM _96_tablaDinamica WHERE carpetaAdministrativa='".$fRegistro["carpetaJudicial"].
				"' AND idEstado>1.4";
		$res=$con->obtenerFilas($query);
		while($fila=mysql_fetch_assoc($res))
		{
			$query="UPDATE _96_tablaDinamica SET codigoInstitucion='".$cveUnidadDestino."',carpetaAdministrativa='".$carpetaJudicial."' WHERE id__96_tablaDinamica=".$fila["id__96_tablaDinamica"];
			if($con->ejecutarConsulta($query))
			{
				cambiarEtapaFormulario(96,$fila["id__96_tablaDinamica"],$fila["idEstado"],"",-1,"NULL","NULL",613);
			}
		}
		
		if($funcionFinal!="")
		{
			eval($funcionFinal);
			return true;
		}
	}
}


function obtenerNumerFolioCarpetaJudicial($carpetaOrigen)
{
	global $con;
	
	$consulta="SELECT fechaCreacion FROM 7006_carpetasAdministrativas 
			WHERE 	carpetaAdministrativa='".$carpetaOrigen."'";
	$fechaCreacion=$con->obtenerValor($consulta);
	$anioCarpetaOrigen=date("Y",strtotime($fechaCreacion));
	
	if(substr_count($carpetaOrigen,$anioCarpetaOrigen)==2)
	{
		return $anioCarpetaOrigen;
	}
	else
	{
		$pos=0;
		$carpetaOrigen=str_replace("-","/",$carpetaOrigen);
		$carpetaOrigen=str_replace($anioCarpetaOrigen,"",$carpetaOrigen);
		$arrCarpetaOrigen=explode("/",$carpetaOrigen);
		
		foreach($arrCarpetaOrigen as $token)
		{
			
			if($pos==0)
			{
				$pos++;
				continue;
			}
			
			if(is_numeric($token))
			{
				
					return ($token*1);
			}
		}
	}
	
	return 0;
}

function esSubdirectorCausasSimilar($actor)
{
	global $con;
	

	$consulta="SELECT actor FROM 944_actoresProcesoEtapa WHERE idActorProcesoEtapa=".$actor;
	$rol=$con->obtenerValor($consulta);
	
	$arrRolesSimilares=array();
	$arrRolesSimilares["1_0"]="Root";
	$arrRolesSimilares["19_0"]="Subdirector de Causa y Ejecuciones";
	$arrRolesSimilares["74_0"]="Subdirector de EjecuciÛn de Sanciones";
	$arrRolesSimilares["39_0"]="JUD de Amparo y Apelaciones";
	$arrRolesSimilares["217_0"]="Auxiliar de EjecuciÛn";
	if(isset($arrRolesSimilares[$rol]))
	{
		return 1;
	}
	return 0;
	
}

function registrarBitacoraCambioPenas($idPena,$situacionCambio,$comentarios,$datosComplementarios)
{
	global $con;
	$consulta="SELECT situacion FROM 7024_registroPenasSentenciaEjecucion WHERE idRegistro=".$idPena;
	$situacion=$con->obtenerValor($consulta);
	$x=0;
	$query[$x]="begin";
	$x++;
	$query[$x]="update 7024_registroPenasSentenciaEjecucion set situacion=".$situacionCambio." where idRegistro=".$idPena;
	$x++;
	$query[$x]="INSERT INTO 7024_bitacoraCambiosPena(idPena,fechaCambio,idUsuarioResponsable,etapaActual,etapaCambio,comentariosAdicionales,datosComplementarios)
				VALUES(".$idPena.",'".date("Y-m-d H:i:s")."',".$_SESSION["idUsr"].",".$situacion.",".$situacionCambio.",'".cv($comentarios)."','".
			cv($datosComplementarios)."')";
	$x++;
	$query[$x]="commit";
	$x++;
	
	
	return $con->ejecutarBloque($query);

}



function cambiarAdscripcionRegistroCarpetaJudicial($idFormulario,$idRegistro)
{
	global $con;
	
	$nombreTablaBase="_".$idFormulario."_tablaDinamica";
	$campoLlave="id_".$nombreTablaBase;
	
	
	$carpetaAdministrativa=obtenerCarpetaAdministrativaProceso($idFormulario,$idRegistro);
	$consulta="SELECT unidadGestion FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$carpetaAdministrativa."'";
	
	if($con->existeCampo("idCarpetaAdministrativa",$nombreTablaBase))
	{
		$query="select idCarpetaAdministrativa from ".$nombreTablaBase." where ".$campoLlave."=".$idRegistro;
		$idCarpetaAdministrativa=$con->obtenerValor($query);
		$consulta.=" and idCarpeta=".$idCarpetaAdministrativa;
		
	}

	$codigoUnidad=$con->obtenerValor($consulta);
	$consulta="UPDATE _".$idFormulario."_tablaDinamica SET codigoInstitucion='".$codigoUnidad."' WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	return $con->ejecutarConsulta($consulta);
}


function permiteModificarActaMinima($numEtapa,$actor)
{
	$permiteEditar=0;
	switch($numEtapa)
	{
		case 1.5:
			if($actor==844)
				$permiteEditar=1;
		break;
		case 2:
			if($actor==845)
				$permiteEditar=1;
		break;
		case 3:
			if($actor==847)
				$permiteEditar=1;
		break;
		case 4:
			if($actor==846)
				$permiteEditar=1;
		break;
	}
	
	return $permiteEditar;
}

function mostrarSeccionEdicionDocumentoSeleccionFormato($idFormulario,$idRegistro,$idFormularioEvaluacion)
{
	global $con;
	
	$documentoBloqueado=0;
	$consulta="SELECT idRegistro FROM 7035_informacionDocumentos WHERE idFormulario=".$idFormulario.
				" AND idReferencia=".$idRegistro." AND idFormularioProceso=".$idFormularioEvaluacion;

	$iRegistro=$con->obtenerValor($consulta);	
	if($iRegistro!="")
	{
		$consulta="SELECT documentoBloqueado FROM 3000_formatosRegistrados WHERE idFormulario=-2 AND idRegistro=".$iRegistro." AND idFormularioProceso=".$idFormularioEvaluacion;
		
		$documentoBloqueado=$con->obtenerValor($consulta);	
	}
	if($documentoBloqueado==1)
		return 0;
	return 1;
	
}

function permiteModificarTranscripion($numEtapa,$actor)
{
	$permiteEditar=0;
	switch($numEtapa)
	{
		case 1.5:
			if($actor==851)
				$permiteEditar=1;
		break;
		case 2:
			if($actor==852)
				$permiteEditar=1;
		break;
		case 3:
			if($actor==853)
				$permiteEditar=1;
		break;
		case 4:
			if($actor==854)
				$permiteEditar=1;
		break;
		case 6:
			if($actor==856)
				$permiteEditar=1;
		break;
	}
	
	return $permiteEditar;
}

function permiteModificarResolucion($numEtapa,$actor)
{
	$permiteEditar=0;
	switch($numEtapa)
	{
		case 7:
			if($actor==859)
				$permiteEditar=1;
		break;
		case 10:
			if($actor==857)
				$permiteEditar=1;
		break;
		case 11:
			if($actor==858)
				$permiteEditar=1;
		break;
		
	}
	
	return $permiteEditar;
}


function validarIncidenciasSala($idFormulario,$idRegistro)
{
	global $con;
	$cadRes="";
	$consulta="SELECT idRegistroEvento,fechaEvento,horaInicioEvento,horaFinEvento,idSala FROM 7000_eventosAudiencia WHERE idFormulario=".$idFormulario." AND idRegistroSolicitud=".$idRegistro;
	$fEvento=$con->obtenerPrimeraFila($consulta);
	
	
	
	
	if(!existeDisponibilidadSala($fEvento[0],$fEvento[4],$fEvento[1],$fEvento[2],$fEvento[3]))
	{
		$cadRes="['Datos de la audiencia','El evento a confirmar tiene conflicto con otro evento en dicha sala']";
	}
	
	
	return "[".$cadRes."]";	
}


function conoceJuezTribunalCarpeta($cAdministrativaBase,$idUnidadGestion)
{
	global $con;
	$carpeta=obtenerCarpetaBaseOriginal($cAdministrativaBase);

	$listaCarpetas="'".$carpeta."'";
	$lCarpetasHijas=obtenerCarpetasDerivadas($carpeta,"1,5")	;
	
	if($lCarpetasHijas=="")
		$lCarpetasHijas="'".$carpeta."'";
	else
		$lCarpetasHijas.=",'".$carpeta."'";
	
	$consulta="SELECT usuarioJuez FROM _26_tablaDinamica j,_26_tipoJuez tj WHERE idReferencia=".$idUnidadGestion." AND 
				tj.idPadre=j.id__26_tablaDinamica AND idOpcion=2";
	$lJueces=$con->obtenerListaValores($consulta);
	if($lJueces=="")
		$lJueces=-1;
		
	$consulta="SELECT e.idRegistroEvento,fechaEvento,horaInicioEvento,horaFinEvento,idCentroGestion,idSala,tipoAudiencia,
				ej.noJuez,idJuez,(SELECT Nombre FROM 800_usuarios WHERE idUsuario=ej.idJuez) AS nombreJuez,
				(SELECT carpetaAdministrativa FROM 7007_contenidosCarpetaAdministrativa 
				WHERE tipoContenido=3 AND idRegistroContenidoReferencia=e.idRegistroEvento) AS carpeta ,e.situacion
			 	FROM 7000_eventosAudiencia e,7001_eventoAudienciaJuez ej,7007_contenidosCarpetaAdministrativa co
			   WHERE  ej.idRegistroEvento=e.idRegistroEvento AND co.tipoContenido=3 AND co.idRegistroContenidoReferencia=e.idRegistroEvento
   			AND co.carpetaAdministrativa IN(".$lCarpetasHijas.") AND ej.idJuez IN(".$lJueces.") and situacion in(1,2,4,5)";
	$con->obtenerFilas($consulta);

	return $con->filasAfectadas>0;
	
	
}

function obtenerCarpetasPadreIdCarpeta($cA,&$arrCarpetasPadre,$iCarpeta)
{
	global $con;
	$arrHijos="";
	$consulta="SELECT carpetaAdministrativaBase FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$cA."'";
	
	
	if($iCarpeta!=-1)
	 	$consulta.=" and idCarpeta=".$iCarpeta;
	
	$iCarpetaBase="";
	$carpetaAdministrativaBase=$con->obtenerValor($consulta);
	if($carpetaAdministrativaBase!="")
	{
		if(strpos($carpetaAdministrativaBase,"[")===false)
		{
			$consulta="SELECT idCarpeta FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$carpetaAdministrativaBase."'";
			$iCarpetaBase=$con->obtenerValor($consulta);
		}
		else
		{
			$iCarpetaBase=str_replace("[","",str_replace("]","",$carpetaAdministrativaBase));
			$consulta="select carpetaAdministrativa from 7006_carpetasAdministrativas where idCarpeta=".$iCarpetaBase;
			$carpetaAdministrativaBase=$con->obtenerValor($consulta);
		}
		obtenerCarpetasPadreIdCarpeta($carpetaAdministrativaBase,$arrCarpetasPadre,$iCarpetaBase);
		$oCarpeta["idCarpeta"]=$iCarpetaBase;
		$oCarpeta["carpetaAdministrativa"]=$carpetaAdministrativaBase;
		array_push($arrCarpetasPadre,$oCarpeta);
		
	}
	
	return true;
}

function aplicarAjusteSolicitudInicial($idFormulario,$idRegistro)
{
	global $con;
	$tipoDelito="";
	$x=0;
	$query[$x]="begin";
	$x++;	
				
	$consulta="SELECT * FROM _488_tablaDinamica WHERE id__488_tablaDinamica=".$idRegistro;
	$fRegistro=$con->obtenerPrimeraFilaAsoc($consulta);
	

	$tipoOperacion=$fRegistro["tipoOperacion"];
	
	$consulta="SELECT carpetaAdministrativa,tipoAudiencia FROM _46_tablaDinamica WHERE id__46_tablaDinamica=".$fRegistro["folioSolicitudAjuste"];
	$fDatosSolicitudAjuste=$con->obtenerPrimeraFila($consulta);	

	$cAdministrativa=$fDatosSolicitudAjuste[0];
	$tipoAudiencia=$fDatosSolicitudAjuste[1];
	
	$consulta="SELECT id__17_tablaDinamica,c.fechaCreacion FROM 7006_carpetasAdministrativas c,_17_tablaDinamica u 
				WHERE c.carpetaAdministrativa='".$cAdministrativa."' AND u.claveUnidad=c.unidadGestion";
	$fDatosCarpeta=$con->obtenerPrimeraFila($consulta);	
	$idUGJOrigen=$fDatosCarpeta[0];
	if($idUGJOrigen=="")
		$idUGJOrigen=-1;
	
	$anioCarpetaOrigen=date("Y",strtotime($fDatosCarpeta[1]));	
	
	
	$consulta="SELECT count(*) FROM _46_tablaDinamica WHERE carpetaAdministrativa='".$cAdministrativa.
				"' and id__46_tablaDinamica<>".$fRegistro["folioSolicitudAjuste"];
	$nReg=$con->obtenerValor($consulta);
	
	$consulta="SELECT idRegistroEvento,situacion FROM 7000_eventosAudiencia WHERE idFormulario=46 AND 
				idRegistroSolicitud=".$fRegistro["folioSolicitudAjuste"];
	$fDatosAudiencia=$con->obtenerPrimeraFila($consulta);
	
	$idRegistroEvento=$fDatosAudiencia[0];
	
	if($idRegistroEvento=="")
	{
		$idRegistroEvento=-1;
	}
	
	if($tipoOperacion==1) 
	{
		$query[$x]="update 7007_contenidosCarpetaAdministrativa set carpetaAdministrativa=concat('Cancelado: [',carpetaAdministrativa,']')  
					WHERE carpetaAdministrativa='".$cAdministrativa."' AND 
						idFormulario=46 AND idRegistro=".$fRegistro["folioSolicitudAjuste"];
		$x++;
	}
	else
	{
		$query[$x]="delete from 7007_contenidosCarpetaAdministrativa WHERE carpetaAdministrativa='".$cAdministrativa."' AND 
						idFormulario=46 AND idRegistro=".$fRegistro["folioSolicitudAjuste"];
		$x++;
	}
	
	
	$query[$x]="DELETE FROM 9060_tableroControl_4 WHERE numeroCarpetaAdministrativa='".$cAdministrativa."' AND 
				iFormulario=46 AND iRegistro=".$fRegistro["folioSolicitudAjuste"];
	$x++;
	
	
	if($nReg==0)
	{
		$query[$x]="DELETE FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$cAdministrativa."'";
		$x++;
		
		
		$folioCarpeta=obtenerNumerFolioCarpetaJudicial($cAdministrativa);
		$folioCarpeta--;
		if($folioCarpeta<0)
			$folioCarpeta=0;
		
		$consulta="SELECT folioActual FROM 7004_seriesUnidadesGestion WHERE anio='".$anioCarpetaOrigen.
					"' AND idUnidadGestion=".$idUGJOrigen." AND tipoDelito='".$tipoDelito."'";
		$fActual=$con->obtenerValor($consulta);
		
		if($folioCarpeta<$fActual)
		{
		
			$query[$x]="UPDATE 7004_seriesUnidadesGestion SET folioActual=".$folioCarpeta." WHERE anio='".$anioCarpetaOrigen.
						"' AND idUnidadGestion=".$idUGJOrigen." AND tipoDelito='".$tipoDelito."'";
			$x++;
		}
		
		
	}
	
	if($tipoOperacion==1) 
	{
		$query[$x]="update 7000_eventosAudiencia set situacion=3 WHERE idRegistroEvento=".$idRegistroEvento;
		$x++;
		
		$query[$x]="update 7001_eventoAudienciaJuez set  idJuez=idJuez*-1 WHERE idRegistroEvento=".$idRegistroEvento;
		$x++;
		
		
	}
	else
	{
		$query[$x]="delete from 7000_eventosAudiencia  WHERE idRegistroEvento=".$idRegistroEvento;
		$x++;
		
		$query[$x]="delete from 7001_eventoAudienciaJuez WHERE idRegistroEvento=".$idRegistroEvento;
		$x++;
	}
	$query[$x]="UPDATE 7001_asignacionesJuezAudiencia SET situacion=7 WHERE idEventoAudiencia=".$idRegistroEvento." AND situacion=1";
	$x++;
	
	if($fDatosAudiencia)
	{
		if($fDatosAudiencia[1]==1)
		{
			@notificarCancelacionEventoMAJO($fDatosAudiencia[1]);
		}
	}
	
	if($tipoOperacion==1) //Cancelar
	{
		$query[$x]="UPDATE _46_tablaDinamica SET idEstado=0,carpetaAdministrativa=concat('Cancelado: [',carpetaAdministrativa,']') WHERE id__46_tablaDinamica=".$fRegistro["folioSolicitudAjuste"];
		$x++;
	}
	else
	{
		$query[$x]="UPDATE _46_tablaDinamica SET carpetaAdministrativa='',corregidoAlgoritmo=488 WHERE id__46_tablaDinamica=".$fRegistro["folioSolicitudAjuste"];
		$x++;
		switch($fRegistro["tipoGeneracionCarpeta"])
		{
			case 1: //Misma unidad de gestiÛn
				$consulta="SELECT id__17_tablaDinamica,idReferencia FROM _17_tablaDinamica WHERE id__17_tablaDinamica=".$idUGJOrigen;
				$fRegistroUnidad=$con->obtenerPrimeraFila($consulta);
				$query[$x]="INSERT INTO 7000_eventosAudiencia(situacion,fechaAsignacion,idEdificio,idCentroGestion,
						idFormulario,idRegistroSolicitud,idReferencia,idEtapaProcesal,tipoAudiencia)
						values(0,'".date("Y-m-d H:i:s")."',".$fRegistroUnidad[1].",".$fRegistroUnidad[0].",46,".
						$fRegistro["folioSolicitudAjuste"].",-1,1,".$tipoAudiencia.")";
				$x++;
			break;
			case 2: //Unidad aleatoria
			break;
			case 3: //Unidad especaific
				$consulta="SELECT id__17_tablaDinamica,idReferencia FROM _17_tablaDinamica WHERE id__17_tablaDinamica=".$fRegistro["unidadDestino"];
				$fRegistroUnidad=$con->obtenerPrimeraFila($consulta);
				$query[$x]="INSERT INTO 7000_eventosAudiencia(situacion,fechaAsignacion,idEdificio,idCentroGestion,
						idFormulario,idRegistroSolicitud,idReferencia,idEtapaProcesal,tipoAudiencia)
						values(0,'".date("Y-m-d H:i:s")."',".$fRegistroUnidad[1].",".$fRegistroUnidad[0].",46,".
						$fRegistro["folioSolicitudAjuste"].",-1,1,".$tipoAudiencia.")";
				$x++;
			break;
		}
		
		
		
	}
	
	$query[$x]="commit";
	$x++;


	if($con->ejecutarBloque($query))
	{
		if($tipoOperacion==2)
		{
			if(cambiarEtapaFormulario(46,$fRegistro["folioSolicitudAjuste"],2.7,"",-1,"NULL","NULL",264))
			{
				$consulta="SELECT carpetaAdministrativa FROM _46_tablaDinamica WHERE id__46_tablaDinamica=".$fRegistro["folioSolicitudAjuste"];
				$cAdministrativaDestino=$con->obtenerValor($consulta);
				
				$consulta="UPDATE _488_tablaDinamica SET carpetaOriginal='".$cAdministrativa."',carpetaRemitida='".$cAdministrativaDestino."',fechaEnvio='".date("Y-m-d H:i:s").
							"' WHERE id__488_tablaDinamica=".$idRegistro;
				$con->ejecutarConsulta($consulta);
				
			}
			
		}
		return true;
	}
	
}


function aplicarAjusteSolicitudPromocion($idFormulario,$idRegistro)
{
	global $con;
	$tipoDelito="";
	$x=0;
	$query[$x]="begin";
	$x++;
	
			
				
	$consulta="SELECT * FROM _489_tablaDinamica WHERE id__489_tablaDinamica=".$idRegistro;
	$fRegistro=$con->obtenerPrimeraFilaAsoc($consulta);
	$tipoOperacion=$fRegistro["tipoOperacion"];
	
	$consulta="SELECT carpetaAdministrativa,tipoAudiencia,tipoPromociones FROM _96_tablaDinamica WHERE id__96_tablaDinamica=".$fRegistro["folioPromocion"];
	$fDatosSolicitudAjuste=$con->obtenerPrimeraFila($consulta);	
	$cAdministrativa=$fDatosSolicitudAjuste[0];
	$tipoAudiencia=$fDatosSolicitudAjuste[1];
	$tipoPromocion=$fDatosSolicitudAjuste[2];
	
	$consulta="SELECT id__185_tablaDinamica FROM _185_tablaDinamica WHERE iFormulario=96 AND iRegistro=".$fRegistro["folioPromocion"];
	$idSolicitudIntermedia=$con->obtenerValor($consulta);
	
	if($idSolicitudIntermedia=="")
		$idSolicitudIntermedia=-1;
	
	
	$consulta="SELECT idRegistroEvento,situacion FROM 7000_eventosAudiencia WHERE idFormulario=185 AND 
				idRegistroSolicitud=".$idSolicitudIntermedia;
	$fDatosAudiencia=$con->obtenerPrimeraFila($consulta);
	
	$idRegistroEvento=$fDatosAudiencia[0];
	
	if($idRegistroEvento=="")
	{
		$idRegistroEvento=-1;
	}
	
	
	$query[$x]="DELETE FROM 7007_contenidosCarpetaAdministrativa WHERE carpetaAdministrativa='".$cAdministrativa."' AND 
					idFormulario=96 AND idRegistro=".$fRegistro["folioPromocion"];
	$x++;
	
	$query[$x]="DELETE FROM 9060_tableroControl_4 WHERE numeroCarpetaAdministrativa='".$cAdministrativa."' AND 
				iFormulario=96 AND iRegistro=".$fRegistro["folioPromocion"];
	$x++;
	
	$query[$x]="DELETE FROM 9060_tableroControl_4 WHERE numeroCarpetaAdministrativa='".$cAdministrativa."' AND 
				iFormulario=185 AND iRegistro=".$idSolicitudIntermedia;
	$x++;
	
	
	
	
	$query[$x]="DELETE FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$idRegistroEvento;
	$x++;
	
	$query[$x]="DELETE FROM 7001_eventoAudienciaJuez WHERE idRegistroEvento=".$idRegistroEvento;
	$x++;
	
	$query[$x]="UPDATE 7001_asignacionesJuezAudiencia SET situacion=7 WHERE idEventoAudiencia=".$idRegistroEvento." AND situacion=1";
	$x++;	
		
	$query[$x]="DELETE FROM _185_tablaDinamica WHERE id__185_tablaDinamica=".$idSolicitudIntermedia;
	$x++;
	
	if($fDatosAudiencia)
	{
		if($fDatosAudiencia[1]==1)
		{
			@notificarCancelacionEventoMAJO($fDatosAudiencia[1]);
		}
	}
	
	if($tipoOperacion==1) //Cancelar
	{
		$query[$x]="UPDATE _96_tablaDinamica SET idEstado=0,carpetaAdministrativa=concat('Cancelado: [',carpetaAdministrativa,']') 
					WHERE id__96_tablaDinamica=".$fRegistro["folioPromocion"];
		$x++;
	}
	else
	{
		$consulta="SELECT unidadGestion FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$fRegistro["carpetaJudicialDestino"]."'";
		$codigoInstitucion=$con->obtenerValor($consulta);
		$query[$x]="UPDATE _96_tablaDinamica SET carpetaAdministrativa='".$fRegistro["carpetaJudicialDestino"].
					"',codigoInstitucion='".$codigoInstitucion."' WHERE id__96_tablaDinamica=".$fRegistro["folioPromocion"];
		$x++;
		
		
	}
	
	$query[$x]="commit";
	$x++;
	
	if($con->ejecutarBloque($query))
	{
		if($tipoOperacion==2)
		{
			if(cambiarEtapaFormulario(96,$fRegistro["folioPromocion"],2,"",-1,"NULL","NULL",657))
			{
				
				
				$consulta="UPDATE _489_tablaDinamica SET carpetaOriginal='".$cAdministrativa."',fechaEnvio='".date("Y-m-d H:i:s").
							"' WHERE id__489_tablaDinamica=".$idRegistro;
				$con->ejecutarConsulta($consulta);
				
			}
			
		}
		return true;
	}
	
}


function registrarCorreccionAlgoritmo($tipificacion,$idRegistro,$tipoCorreccion)
{
	global $con;
	$consulta="select delitoGrave from _46_tablaDinamica where id__46_tablaDinamica=".$idRegistro;
	$tBase=$con->obtenerValor($consulta);
	if($tBase!=$tipificacion)
	{
		$consulta="update _46_tablaDinamica set  corregidoAlgoritmo=".$tipoCorreccion." where id__46_tablaDinamica=".$idRegistro;
		$con->ejecutarConsulta($consulta);
	}
	return true;
}

function obtenerNoRondaAsignacion($idUGA,$tipoRonda)
{
	global $con;
	
	$consulta="SELECT noRonda FROM 7004_seriesRondaAsignacion WHERE idUGARonda=".$idUGA." AND serieRonda='".$tipoRonda."'";
	$noRonda=$con->obtenerValor($consulta);
	if($noRonda=="")
	{
		$noRonda=1;
		$consulta="INSERT INTO 7004_seriesRondaAsignacion(idUGARonda,serieRonda,noRonda) VALUES(".$idUGA.",'".$tipoRonda."',1)";
		$con->ejecutarConsulta($consulta);		
	}	
	return $noRonda;	
}


function actualizarNoRondaAsignacion($idUGA,$tipoRonda,$noRonda)
{
	global $con;
	
	$consulta="update 7004_seriesRondaAsignacion set noRonda=".$noRonda." WHERE idUGARonda=".$idUGA." AND serieRonda='".$tipoRonda."'";
	return $con->ejecutarConsulta($consulta);
}

function incrementarNoRondaAsignacion($idUGA,$tipoRonda)
{
	global $con;
	
	$consulta="update 7004_seriesRondaAsignacion set noRonda=noRonda+1 where idUGARonda=".$idUGA." and serieRonda='".$tipoRonda."'";
	return $con->ejecutarConsulta($consulta);
	
}

function obtenerTipoCarpeta($carpeta)
{
	global $con;
	$consulta="SELECT tipoCarpetaAdministrativa FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$carpeta."'";
	$tipo=$con->obtenerValor($consulta);
	return $tipo;
	
}

function existeParticipanteRegistro($idActividad,$idParticipante,$figura)
{
	global $con;
	$consulta="SELECT COUNT(*) FROM 7005_relacionFigurasJuridicasSolicitud WHERE idActividad=".$idActividad." AND idParticipante=".$idParticipante.
			" AND idFiguraJuridica=".$figura;
	$nReg=$con->obtenerValor($consulta);
	return $nReg>0;
}


function validarCancelacionAudiencia($idFormulario,$idRegistro)
{
	global $con;
	$consulta="SELECT idEvento FROM _323_tablaDinamica WHERE id__323_tablaDinamica=".$idRegistro;
	$fDatosRegistro=$con->obtenerPrimeraFila($consulta);
	
	$consulta="select situacion FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$fDatosRegistro[0];
	$fDatosRegistroEvento=$con->obtenerPrimeraFila($consulta);
	
	if($fDatosRegistroEvento[0]!=3)
	{
		$consulta="UPDATE 7000_eventosAudiencia SET situacion=3 WHERE idRegistroEvento=".$fDatosRegistro[0];
		$con->ejecutarConsulta($consulta);
		$consulta="UPDATE 7001_asignacionesJuezAudiencia SET situacion=7 WHERE idEventoAudiencia=".$fDatosRegistro[0]." AND situacion=1";
		$con->ejecutarConsulta($consulta);
		@notificarCancelacionEventoMAJO($fDatosRegistro[0]);
	
		
		return true;
	}
	return true;
	
}




function asignarTribunalAlzadaApelacion($idFormulario,$idRegistro)
{
	global $con;
	global $servidorPruebas;
	
	$idRegistroBase=$idRegistro;

	$consulta="SELECT  * FROM _451_tablaDinamica WHERE id__451_tablaDinamica=".$idRegistro;

	$fRegistro=$con->obtenerPrimeraFilaAsoc($consulta);

	$fResultado=NULL;
	
	$consulta="SELECT  * FROM _618_tablaDinamica WHERE idReferencia=".$idRegistroBase;
	$fRegistroResultadoAuxJUDControl=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$consulta="SELECT  * FROM _617_tablaDinamica WHERE idReferencia=".$idRegistroBase;
	$fRegistroResultadoJUDControl=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$consulta="SELECT  * FROM _581_tablaDinamica WHERE idReferencia=".$idRegistroBase;
	$fRegistroResultadoSubControl=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$consulta="SELECT  * FROM _582_tablaDinamica WHERE idReferencia=".$idRegistroBase;
	$fRegistroResultadoDirectorControl=$con->obtenerPrimeraFilaAsoc($consulta);
	
	
	$consulta="SELECT  * FROM _583_tablaDinamica WHERE idReferencia=".$idRegistroBase;
	$fRegistroResultadoDirectorEjecutivo=$con->obtenerPrimeraFilaAsoc($consulta);
	
	
	if($fRegistroResultadoDirectorEjecutivo)
		$fResultado=$fRegistroResultadoDirectorEjecutivo;
	else
		if($fRegistroResultadoDirectorControl)
			$fResultado=$fRegistroResultadoDirectorControl;
		else
			if($fRegistroResultadoSubControl)
				$fResultado=$fRegistroResultadoSubControl;
			else
			{
				if($fRegistroResultadoJUDControl)
					$fResultado=$fRegistroResultadoJUDControl;
				else
					$fResultado=$fRegistroResultadoAuxJUDControl;
			}
	
	$cveTribunal=$fResultado["salaPenal"];
	$consulta="SELECT COUNT(*) FROM _487_tablaDinamica WHERE iFormulario=".$idFormulario." AND iRegistro=".$idRegistro;
	$nReg=$con->obtenerValor($consulta);
	
	if($nReg==0)
	{
	
		$arrDocumentosReferencia=array();
		$arrValores=array();			
		$arrValores["tribunalAlzada"]=$cveTribunal;
		$arrValores["carpetaJudicialApelada"]=$fRegistro["carpetaAdministrativa"];
		$arrValores["resolucionImpugnada"]=$fRegistro["resolucionImpugnada"];
		$arrValores["nombreResolucion"]=$fRegistro["nombreResolucion"];
		$arrValores["fechaEmision"]=$fRegistro["fechaEmision"];
		$arrValores["juezResolucion"]=$fRegistro["juezResolucion"];
		$arrValores["eventoResolucion"]=$fRegistro["eventoResolucion"];
		$arrValores["carpetaJudicialApelacion"]=$fRegistro["carpetaApelacion"];
		
		$arrValores["iFormulario"]=$idFormulario;
		$arrValores["iRegistro"]=$idRegistro;	
		

		$consulta="SELECT idRegistroContenidoReferencia FROM 7007_contenidosCarpetaAdministrativa 
				WHERE carpetaAdministrativa='".$arrValores["carpetaJudicialApelacion"]."' AND tipoContenido=1";
	
		$res=$con->obtenerFilas($consulta);
		while($fDocumento=mysql_fetch_row($res))
		{
			$consulta="SELECT tipoFormato FROM 3000_formatosRegistrados WHERE idDocumento=".$fDocumento[0];
			$tipoFormato=$con->obtenerValor($consulta);
			if($tipoFormato==527)
				continue;
			
			
			array_push($arrDocumentosReferencia,$fDocumento[0]);	
		}
		
		
		$idRegistroSolicitud=crearInstanciaRegistroFormulario(487,-1,1,$arrValores,$arrDocumentosReferencia,-1,871);
		
		$consulta="INSERT INTO _487_apelantes(idReferencia,idApelante,figuraJuridica) VALUES(".$idRegistroSolicitud.
				",".$fRegistro["nombreApelante"].",".$fRegistro["figuraJuridica"].")";
				
		$con->ejecutarConsulta($consulta);		
		
		//cambiarEtapaFormulario(487,$idRegistroSolicitud,1,"",-1,"NULL","NULL",871);
		
		/*$consulta="SELECT carpetaAdministrativa FROM _487_tablaDinamica WHERE id__487_tablaDinamica=".$idRegistroSolicitud;
		$cAlzada=$con->obtenerValor($consulta);*/
		$consulta="INSERT INTO _495_tablaDinamica(idReferencia,fechaCreacion,responsable,idEstado,tribunalAlzada,fechaEnvio)
					VALUES(".$idRegistro.",'".date("Y-m-d H:i:s")."',".$_SESSION["idUsr"].",1,'".$cveTribunal."','".date("Y-m-d H:i:s")."')";
		
		if($con->ejecutarConsulta($consulta))
		{
			$consulta="UPDATE _451_tablaDinamica SET salaPenal='".$cveTribunal."' WHERE id__451_tablaDinamica=".$idRegistro;
			return 	$con->ejecutarConsulta($consulta);		
		
		}
	
	}
}

function esSubdirectorSalasSimilar($actor)
{
	global $con;
	

	$consulta="SELECT actor FROM 944_actoresProcesoEtapa WHERE idActorProcesoEtapa=".$actor;
	$rol=$con->obtenerValor($consulta);
	
	$arrRolesSimilares=array();
	$arrRolesSimilares["1_0"]="Root";
	$arrRolesSimilares["69_0"]="Subdirector de Causa y Sala";
	$arrRolesSimilares["81_0"]="Subdirector de Sala";
//	$arrRolesSimilares["170_0"]="Modificador Audiencias";
	if(isset($arrRolesSimilares[$rol])||(existeRol("'170_0'")))
	{
		return 1;
	}
	return 0;
	
}


function validarConfiguracionAudiencia($idFormulario,$idRegistro)
{
	global $con;
	$cadRes="";
	$consulta="SELECT tipoAudiencia FROM _185_tablaDinamica WHERE id__185_tablaDinamica=".$idRegistro;
	$tAudiencia=$con->obtenerValor($consulta);
	if($tAudiencia=="")
	{
		$cadRes="['Solicitud de audiencia','Debe indicar el tipo de audiencia a programar']";
	}
	
	return "[".$cadRes."]";	
}

function mostrarSeccionEdicionDocumento1InformacionDocumento($idFormulario,$idRegistro,$idFormularioEvaluacion)
{
	global $con;	
	
	$consulta="SELECT documentoBloqueado FROM 7035_informacionDocumentos i,3000_formatosRegistrados f WHERE i.idFormulario=".$idFormulario." AND i.idReferencia=".$idRegistro." AND 
				i.idFormularioProceso=".$idFormularioEvaluacion." AND f.idFormulario=-2 AND f.idRegistro=i.idRegistro AND f.idFormularioProceso=i.idFormularioProceso";

	$documentoBloqueado=$con->obtenerValor($consulta);	
	if($documentoBloqueado==1)
		return 0;
	return 1;
	
}


function subirDocumentoCSDOCS($idDocumento,$idExpediente)
{
	global $con;
	global $tipoMateria;
	global $habilitarSubidaCSDOCS;
	if(!$habilitarSubidaCSDOCS)
		return true;
	if($tipoMateria=="P")
		return true;
	$rutaDocumento=obtenerRutaDocumento($idDocumento);
	$base64Document=leerContenidoArchivo($rutaDocumento);	
	$consulta="SELECT nomArchivoOriginal,documento,tipoArchivo,tamano,enBD,documentoRepositorio FROM 908_archivos WHERE idArchivo=".$idDocumento;
	$fDocumento=$con->obtenerPrimeraFila($consulta);
	
	$consulta="SELECT idFormulario,idRegistro FROM 7006_carpetasAdministrativas WHERE idCarpeta=".$idExpediente;
	$fCarpeta=$con->obtenerPrimeraFila($consulta);
	
	$consulta="SELECT tipoJuicio FROM _478_tablaDinamica WHERE id__478_tablaDinamica=".$fCarpeta[1];
	$tJuicio=$con->obtenerValor($consulta);
	
	$consulta="SELECT tipoJuicio,tipoMateria FROM _477_tablaDinamica WHERE id__477_tablaDinamica=".$tJuicio;
	$fTipoJuicio=$con->obtenerPrimeraFila($consulta);
	$lblTipoJuicio=$fTipoJuicio[0];
	
	$consulta="SELECT idFormulario FROM 900_formularios WHERE categoriaFormulario=1";
	$iFormularioMateria=$con->obtenerValor($consulta);
	
	$consulta="SELECT idCsDocs FROM _485_tablaDinamica WHERE claveMateria='".$fTipoJuicio[1]."'";
	$idCsDocs=$con->obtenerValor($consulta);	
	
	$cadJSON='{"idInstance":"6","idRepository":"1","idDirectory":"1","fileName":"'.$fDocumento[0].
			'","metadata":{"IdDocumento":"'.$idDocumento.'","IdAplicativo":"'.$idCsDocs.
			'","juicio":"'.$lblTipoJuicio.'"},"file64":"'.bE($base64Document).'"}';

	$service_url = 'http://172.19.202.115:8000/api/document/store';
    $curl = curl_init($service_url);
    $curl_post_data = $cadJSON;
	
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array(                                                                          
													'Content-Type: application/json',                                                                                
													'Content-Length: ' . strlen($curl_post_data))                                                                       
												);                                                                                                                   
                                
	$curl_response = curl_exec($curl);
	$objRespuesta=json_decode($curl_response);
	if($objRespuesta->status==1)
	{
		$consulta="UPDATE 908_archivos SET  documentoRepositorio=".$objRespuesta->idGlobal." WHERE idArchivo=".$idDocumento;
		$con->ejecutarConsulta($consulta);
	}
	curl_close($curl);
	

}


function determinarFechaRecepcionDocumento($fecha,$materia)
{
	global $con;
	
	$consulta="SELECT idFormulario FROM 900_formularios WHERE categoriaFormulario=3";
	$idFormulario=$con->obtenerValor($consulta);
	$fechaHabil=obtenerSiguienteDiaHabil(date("Y-m-d",strtotime($fecha)));
	
	$consulta="SELECT id__".$idFormulario."_tablaDinamica FROM _".$idFormulario."_tablaDinamica WHERE materia='".$materia."'";
	$idReferencia=$con->obtenerValor($consulta);
	
	$dia=date("w",strtotime($fechaHabil));
	$consulta="SELECT * FROM _".$idFormulario."_gHorario WHERE idReferencia=".$idReferencia." and dia=".$dia;
	$fHorario=$con->obtenerPrimeraFila($consulta);
	
	if($fechaHabil==date("Y-m-d",strtotime($fecha)))
	{
		if((strtotime($fecha)>=strtotime($fechaHabil." ".$fHorario[3]))&&(strtotime($fecha)<=strtotime($fechaHabil." ".$fHorario[4])))
		{
			return $fecha;
		}
		
		$fechaHabil=strtotime("+1 days",strtotime($fechaHabil));
		$fechaHabil=obtenerSiguienteDiaHabil(date("Y-m-d",$fechaHabil));
	
		$dia=date("w",strtotime($fechaHabil));
		$consulta="SELECT * FROM _".$idFormulario."_gHorario WHERE idReferencia=".$idReferencia." and dia=".$dia;
		$fHorario=$con->obtenerPrimeraFila($consulta);
		
	}
	
	return $fechaHabil." ".$fHorario[3];
	
	
}


function enviarMailSMTP($datosSMTP,$destinatario,$asunto,$mensaje,$arrArchivos=null,$arrCopia=null,$arrCopiaOculta=null)
{
	global $con;
	global $servidorPruebas;
	
	/*if($servidorPruebas)
		return true;*/
	if($destinatario=="")
		return false;
	

	
	$mail = new PHPMailer();
	$mail->SMTPDebug = 0;
	$mail->Debugoutput = 'html';
	$mail->IsSMTP();                                      // set mailer to use SMTP
	
	$mail->Host = $datosSMTP["hostSMTP"];  // specify main and backup server
	$mail->Port = $datosSMTP["puerto"] ;
	$mail->SMTPAuth = $datosSMTP["requiereAutenticacion"]==1;     // turn on SMTP authentication
	if($mail->SMTPAuth )
	{
		$mail->Username = $datosSMTP["mail"];  // SMTP username
		$mail->Password = $datosSMTP["password"];
	}
	
	$mail->SetFrom ($datosSMTP["mail"],$datosSMTP["mail"]);
	$mail->AddAddress($destinatario);

	
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
	
	$mail->Subject = utf8_decode($asunto);
	$mail->Body    = utf8_decode($mensaje);
	
	$resultado[0]=$mail->Send();
	$resultado[1]=$mail->ErrorInfo;
	return $resultado;
	
} 


function registrarDatosPeticion($idFormulario,$idRegistro,$contenido)
{
	global $con;
	global $baseDir;
	
	$archivoDestino=$baseDir."/repositorioDocumentosXMLSolicitudes/".$idFormulario."_".$idRegistro;
	if(file_exists($archivoDestino))
	{
		$x=1;
		$archivoDestinoAux=$archivoDestino.="_".$x;
		while(file_exists($archivoDestinoAux))
		{
			$archivoDestinoAux=$archivoDestino.="_".$x;
			$x++;
		}
		$archivoDestino=$archivoDestinoAux;
	}
	return escribirContenidoArchivo($archivoDestino,$contenido);
}

function registrarDatosPeticionRefiere($idFormulario,$idRegistro,$idFormularioRefiere,$idRegistroRefiere)
{
	global $con;
	global $baseDir;
	$contenido="<refiere>[".$idFormularioRefiere.",".$idRegistroRefiere."]</refiere>";
	$archivoDestino=$baseDir."/repositorioDocumentosXMLSolicitudes/".$idFormulario."_".$idRegistro;
	escribirContenidoArchivo($archivoDestino,$contenido);
}

function obtenerRutaArchivoDatosPeticion($idFormulario,$idRegistro)
{
	global $arrRutasAlmacenamientoXMLSolicitudes;
	foreach($arrRutasAlmacenamientoXMLSolicitudes as $iRuta=>$ruta)
	{
		if(file_exists($ruta."/".$idFormulario."_".$idRegistro))
		{
			return $ruta."/".$idFormulario."_".$idRegistro;
		}
	}
	return -1;
}

function obtenerContenidoArchivoDatosPeticion($idFormulario,$idRegistro)
{
	$contenido="";
	$ruta=obtenerRutaArchivoDatosPeticion($idFormulario,$idRegistro);
	if($ruta!=-1)
	{
		$contenido=leerContenidoArchivo($ruta);

		if(strpos($contenido,"<refiere>[")!==false)
		{
			$arrDatos=explode("<refiere>[",$contenido);
			$arrDatos=explode("]</refiere>",$arrDatos[1]);
			$arrInfo=explode(",",$arrDatos[0]);
			return obtenerContenidoArchivoDatosPeticion($arrInfo[0],$arrInfo[1]);
		}
		
			
		
	}
	
	return $contenido;
	
}

function determinarRutaIncompetencia($idFormulario,$idRegistro)
{
	global $con;
	
	$consulta="SELECT * FROM _516_tablaDinamica WHERE id__516_tablaDinamica=".$idRegistro;
	$fRegistro=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$cAdministrativa=$fRegistro["carpetaJudicialDeclinaCompetencia"];
	
	$carpetasAntecesoras=obtenerCarpetasAntecesoras($cAdministrativa);
	
	$arrCarpetasAntecesoras=explode(",",$carpetasAntecesoras);
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
	
	$consulta="SELECT idActividad,unidadGestion FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$cAdministrativa."'";
	$fCarpetaRemite=$con->obtenerPrimeraFilaAsoc($consulta);
	$idActividadBase=$fCarpetaRemite["idActividad"];
	
	
	$arrUnidadesDestino=array();
	$consulta="SELECT * FROM _517_tablaDinamica WHERE idReferencia=".$fRegistro["id__516_tablaDinamica"];
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_assoc($res))
	{
		if(!isset($arrUnidadesDestino[$fila["unidadEjecucionDestino"]]))
		{
			$arrUnidadesDestino[$fila["unidadEjecucionDestino"]]=array();
			$arrUnidadesDestino[$fila["unidadEjecucionDestino"]]["sinCarpeta"]=array();
			$arrUnidadesDestino[$fila["unidadEjecucionDestino"]]["conCarpeta"]=array();
		}
		
		$consulta="SELECT carpetaAdministrativa,c.idActividad FROM 7006_carpetasAdministrativas c,7005_relacionFigurasJuridicasSolicitud r 
				WHERE c.unidadGestion='".$fila["unidadEjecucionDestino"]."' AND c.tipoCarpetaAdministrativa=6 AND r.idActividad=c.idActividad 
				AND r.idParticipante=".$fila["imputado"]." AND idFiguraJuridica=4 and c.carpetaAdministrativaBase in(".$listaCarpetasAntecesoras.")";
		$fImputado=$con->obtenerPrimeraFila($consulta);
		
		$oImputado["idRegistro"]=$fila["id__517_tablaDinamica"];
		$oImputado["idImputado"]=$fila["imputado"];
		$oImputado["documentosAsociados"]=array();
		
		$consulta="SELECT idDocumento FROM 9074_documentosRegistrosProceso WHERE idFormulario=517 AND idRegistro=".$fila["id__517_tablaDinamica"];
		$resDocs=$con->obtenerFilas($consulta);
		while($fDocumento=mysql_fetch_row($resDocs))
		{
			array_push($oImputado["documentosAsociados"],$fDocumento[0]);	
		}
		
		if($fImputado)
		{
			if(!isset($arrUnidadesDestino[$fila["unidadEjecucionDestino"]]["conCarpeta"][$fImputado[0]]))
			{
				$arrUnidadesDestino[$fila["unidadEjecucionDestino"]]["conCarpeta"][$fImputado[0]]=array();
			}
			$oImputado["idImputado"]["idActividad"]=$fImputado[1];
			array_push($arrUnidadesDestino[$fila["unidadEjecucionDestino"]]["conCarpeta"][$fImputado[0]],$oImputado);
		}
		else
		{
			array_push($arrUnidadesDestino[$fila["unidadEjecucionDestino"]]["sinCarpeta"],$oImputado);
		}
	}
	
	
	$arrDocumentosReferencia=array();
	
	$consulta="SELECT idDocumento FROM 9074_documentosRegistrosProceso WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro;
	$res=$con->obtenerFilas($consulta);
	while($fDocumento=mysql_fetch_row($res))
	{
		array_push($arrDocumentosReferencia,$fDocumento[0]);	
	}
	
	
	$arrCarpetasGeneradas=array();
	
	foreach($arrUnidadesDestino as $unidadDestino=>$resto)
	{
		foreach($resto["conCarpeta"] as $carpeta => $arrImputados)
		{
			$arrDocumentosRegistro=array();
			
			foreach($arrDocumentosReferencia as $iDocumento)
			{
				array_push($arrDocumentosRegistro,$iDocumento);
			}
			
			$listaRegistros="";
			
			$lImputados="";
			foreach($arrImputados as $oImputado)
			{
				if($lImputados=="")
				{
					$lImputados=$oImputado["idImputado"];
					$listaRegistros=$oImputado["idRegistro"];
				}
				else
				{
					$lImputados.=",".$oImputado["idImputado"];
					$listaRegistros.=",".$oImputado["idRegistro"];
				}
					
				foreach($oImputado["documentosAsociados"] as $iDocumento)
				{
					array_push($arrDocumentosRegistro,$iDocumento);
				}	
					
			}
			
			
			$arrValores=array();			
			$arrValores["codigoInstitucion"]=$unidadDestino;
			$arrValores["carpetaAdministrativa"]=$carpeta;
			$arrValores["imputado"]=$lImputados;
			$arrValores["carpetaRemite"]=$cAdministrativa;
			$arrValores["iFormulario"]=$idFormulario;
			$arrValores["iRegistro"]=$idRegistro;
			
			$idRegistroSolicitud=crearInstanciaRegistroFormulario(453,-1,2,$arrValores,$arrDocumentosRegistro,-1,920);
			$consulta="UPDATE _517_tablaDinamica SET carpetaEjecucionAsignada='".$carpeta."',unidadEjecucionDestino='".
			$unidadDestino."' WHERE id__517_tablaDinamica in(".$listaRegistros.")";
			foreach($arrImputados as $oImputado)
			{
				desActivarImputadoCarpetaJudicial($idFormulario,$idRegistro,$oImputado["idImputado"],$cAdministrativa);
			}
			$arrCarpetasGeneradas[$carpeta."_".$unidadDestino]=1;
			
		}
		
		
		$arrDocumentosRegistro=array();
			
		foreach($arrDocumentosReferencia as $iDocumento)
		{
			array_push($arrDocumentosRegistro,$iDocumento);
		}
		
		foreach($resto["sinCarpeta"] as $oImputado)
		{
			foreach($oImputado["documentosAsociados"] as $iDocumento)
			{
				array_push($arrDocumentosRegistro,$iDocumento);
			}	
			
			
			
		}
		
			
			
		$arrValores=array();	
		
		$arrValores["codigoInstitucion"]=$unidadDestino;
		$arrValores["expediente"]=$cAdministrativa;
		$arrValores["unidadRemite"]=$fCarpetaRemite["unidadGestion"];
		$arrValores["motivoRemision"]=2;
		$arrValores["iFormulario"]=$idFormulario;
		$arrValores["iRegistro"]=$idRegistro;
		
		$idRegistroSolicitud=crearInstanciaRegistroFormulario(522,-1,2,$arrValores,$arrDocumentosRegistro,-1,915);
		
		$consulta="SELECT carpetaEjecucion,idActividad FROM _522_tablaDinamica WHERE id__522_tablaDinamica=".$idRegistroSolicitud;
		$fRegistroCarpeta=$con->obtenerPrimeraFila($consulta);
		$carpeta=$fRegistroCarpeta[0];
		$idActividad=$fRegistroCarpeta[1];
		$listaRegistros="";
		$lImputados="";
		
		foreach($resto["sinCarpeta"] as $oImputado)
		{
			if($lImputados=="")
			{
				$lImputados=$oImputado["idImputado"];
				$listaRegistros=$oImputado["idRegistro"];
			}
			else
			{
				$lImputados.=",".$oImputado["idImputado"];
				$listaRegistros.=",".$oImputado["idRegistro"];
			}
			
			
			
		}
		
		$consulta="INSERT INTO 7005_relacionFigurasJuridicasSolicitud(idActividad,idParticipante,idFiguraJuridica,situacion)
				SELECT ".$idActividad.",idParticipante,idFiguraJuridica,1,idCuentaAcceso FROM 7005_relacionFigurasJuridicasSolicitud
				WHERE idActividad=".$idActividadBase." AND idParticipante IN(".$lImputados.")";
		$con->ejecutarConsulta($consulta);
		
		$consulta="INSERT INTO 7005_relacionParticipantes(idActividad,idParticipante,idFiguraJuridica,idActorRelacionado,situacion)
				SELECT ".$idActividad.",idParticipante,idFiguraJuridica,idActorRelacionado,situacion FROM 7005_relacionParticipantes
				WHERE idActividad=".$idActividadBase." AND idActorRelacionado IN(".$lImputados.") and situacion=1";
		$con->ejecutarConsulta($consulta);
		
		$consulta="INSERT INTO 7005_relacionParticipantes(idActividad,idParticipante,idFiguraJuridica,idActorRelacionado,situacion)
				SELECT ".$idActividad.",idParticipante,idFiguraJuridica,idActorRelacionado,situacion FROM 7005_relacionParticipantes
				WHERE idActividad=".$idActividadBase." AND idParticipante IN(".$lImputados.") and situacion=1";
		$con->ejecutarConsulta($consulta);
		
		$consulta="UPDATE _517_tablaDinamica SET carpetaEjecucionAsignada='".$carpeta."',unidadEjecucionDestino='".
		$unidadDestino."' WHERE id__517_tablaDinamica in(".$listaRegistros.")";
		foreach($resto["sinCarpeta"] as $oImputado)
		{
			desActivarImputadoCarpetaJudicial($idFormulario,$idRegistro,$oImputado["idImputado"],$cAdministrativa);
		}
		$arrCarpetasGeneradas[$carpeta."_".$unidadDestino]=1;
	}
	
	$lblResultado="";
	foreach($arrCarpetasGeneradas as $carpeta=>$resto)
	{
		if($arrCarpetasGeneradas=="")
			$arrCarpetasGeneradas=$carpeta;
		else
			$arrCarpetasGeneradas.=",'".$carpeta."'";
			
	}
	
	$arrCarpetasGeneradas='['.$arrCarpetasGeneradas.']';
	$consulta="update _516_tablaDinamica= set carpetaAdministrativa='".cv($arrCarpetasGeneradas)."' where id__516_tablaDinamica=".$idRegistro;
	return $con->ejecutarConsulta($consulta);

	
	
}

function obtenerSiguienteAsignacionObjeto($arrConfiguracion)
{
	global $con;
	$arrUniverso=array();
	$tipoAsignacion=$arrConfiguracion["tipoAsignacion"];
	$serieRonda=$arrConfiguracion["serieRonda"];
	$universoAsignacion=$arrConfiguracion["universoAsignacion"];
	$idObjetoReferencia=$arrConfiguracion["idObjetoReferencia"];
	$considerarDeudasMismaRonda=isset($arrConfiguracion["considerarDeudasMismaRonda"])?$arrConfiguracion["considerarDeudasMismaRonda"]:true;
	$limitePagoRonda=isset($arrConfiguracion["limitePagoRonda"])?$arrConfiguracion["limitePagoRonda"]:0;
	$escribirAsignacion=isset($arrConfiguracion["escribirAsignacion"])?$arrConfiguracion["escribirAsignacion"]:true;
	$pagarDeudasAsignacion=isset($arrConfiguracion["pagarDeudasAsignacion"])?$arrConfiguracion["pagarDeudasAsignacion"]:true;
	$funcValidacionPagoDeuda=isset($arrConfiguracion["funcValidacionPagoDeuda"])?$arrConfiguracion["funcValidacionPagoDeuda"]:"";
	$funcValidacionSeleccion=isset($arrConfiguracion["funcValidacionSeleccion"])?$arrConfiguracion["funcValidacionSeleccion"]:"";	
	$ciclos=0;
	$noRonda=obtenerNoRondaAsignacionObjeto($tipoAsignacion,$serieRonda,$idObjetoReferencia);
	$idRegistroAsignacion=-1;
	
	while($ciclos<10)
	{
		
		$aUniverso=explode(",",$universoAsignacion);
		
		foreach($aUniverso as $idUnidad)
		{
			if(($idUnidad=="")||($idUnidad==-1))
				continue;
			$asignacionesRonda= obtenerAsignacionesRondaObjeto($idUnidad,$tipoAsignacion,$serieRonda,$noRonda,$idObjetoReferencia,!$pagarDeudasAsignacion);
			$nAdeudos=obtenerAsignacionesPendientesObjeto($idUnidad,$tipoAsignacion,$serieRonda,$noRonda,$considerarDeudasMismaRonda,$idObjetoReferencia);
			if(!$pagarDeudasAsignacion)
				$nAdeudos=0;
				
			$nPagadas=obtenerAsignacionesPagadasRondaObjeto($idUnidad,$tipoAsignacion,$serieRonda,$noRonda,$idObjetoReferencia);
			$arrUniverso[$idUnidad]["nAsignaciones"]=$asignacionesRonda;
			$arrUniverso[$idUnidad]["nAdeudos"]=$nAdeudos;			
			$arrUniverso[$idUnidad]["nPagadas"]=$nPagadas;
			
		}
		
		
		foreach($arrUniverso as $idUnidad=>$resto)
		{
			if(($resto["nAdeudos"]>0)&&(($limitePagoRonda==0) ||($resto["nPagadas"]<$limitePagoRonda)))
			{
				$aplicaPagoDeuda=true;
				if($funcValidacionPagoDeuda!="")
				{
					eval('$aplicaPagoDeuda='.str_replace("@idUnidad",$idUnidad,$funcValidacionPagoDeuda).";");
				}
				if($aplicaPagoDeuda)
				{
					$consulta="SELECT  idAsignacion,noRonda FROM 7001_asignacionesObjetos WHERE tipoAsignacion='".$tipoAsignacion."' AND tipoRonda='".
							$serieRonda."' AND idUnidadReferida='".$idUnidad."' and idObjetoReferido=".$idObjetoReferencia.
							" and situacion=2  order by idAsignacion asc";
					$fAsignacionPago=$con->obtenerPrimeraFila($consulta);
					$idAsignacionPagada=$fAsignacionPago[0];
					if($escribirAsignacion)
					{
						if($fAsignacionPago[1]!=$noRonda)
						{
							$consulta="UPDATE 7001_asignacionesObjetos SET situacion=3,rondaPagada=".$noRonda." WHERE idAsignacion=".$idAsignacionPagada;
							$con->ejecutarConsulta($consulta);
							$consulta="INSERT INTO 7001_asignacionesObjetos(idFormulario,idRegistro,idObjetoReferido,idUnidadReferida,fechaAsignacion,
									tipoRonda,noRonda,situacion,rondaPagada,comentariosAdicionales,tipoAsignacion,idAsignacionPagada) values(".
									$arrConfiguracion["idFormulario"].",".$arrConfiguracion["idRegistro"].",'".$idObjetoReferencia."','".$idUnidad.
									"','".date("Y-m-d H:i:s")."','".$serieRonda."',".$noRonda.",1,NULL,'','".$tipoAsignacion."',".$idAsignacionPagada.")";	
							$con->ejecutarConsulta($consulta);
							$idRegistroAsignacion=$con->obtenerUltimoID();
						}
						else
						{
							
							
							$consulta="UPDATE 7001_asignacionesObjetos SET situacion=1,rondaPagada=".$noRonda.
										",idFormulario=".$arrConfiguracion["idFormulario"].",idRegistro=".$arrConfiguracion["idRegistro"].
										",fechaAsignacion='".date("Y-m-d H:i:s")."',comentariosAdicionales='' WHERE idAsignacion=".$idAsignacionPagada;
							$con->ejecutarConsulta($consulta);
							$idRegistroAsignacion=$idAsignacionPagada;
							
							
						}
					}
					
					$arrResultado["idUnidad"]=$idUnidad;
					$arrResultado["noRonda"]=$noRonda;
					$arrResultado["pagoDeuda"]=1;
					$arrResultado["idAsignacionPago"]=$idAsignacionPagada;
					$arrResultado["idRegistroAsignacion"]=$idRegistroAsignacion;
					return $arrResultado;
				}
			}
		}
		
		
		foreach($arrUniverso as $idUnidad=>$resto)
		{
			if($resto["nAsignaciones"]==0)
			{
				$comentariosAdicionales="";
				$aplicaAsignacion=true;
				if($funcValidacionSeleccion!="")
				{
					eval('$aplicaAsignacion='.str_replace("@idUnidad",$idUnidad,$funcValidacionSeleccion));
				}

				if(gettype($aplicaAsignacion)!="boolean")
				{
					$fAplicacion=$aplicaAsignacion;
					$aplicaAsignacion=$fAplicacion[0];
					$comentariosAdicionales=$fAplicacion[1];
				}
				
				
				
				if($aplicaAsignacion)
				{
					$consulta="SELECT  idAsignacion FROM 7001_asignacionesObjetos WHERE tipoAsignacion='".$tipoAsignacion."' AND tipoRonda='".
							$serieRonda."' AND idUnidadReferida='".$idUnidad."' and idObjetoReferido=".$idObjetoReferencia.
							" and situacion=2 and noRonda=".$noRonda;
					
					$idAsignacionPagada=$con->obtenerValor($consulta);
					if($idAsignacionPagada=="")
						$idAsignacionPagada=-1;
					if($escribirAsignacion)
					{
						if($idAsignacionPagada==-1)
						{
							$consulta="INSERT INTO 7001_asignacionesObjetos(idFormulario,idRegistro,idObjetoReferido,idUnidadReferida,fechaAsignacion,
									tipoRonda,noRonda,situacion,comentariosAdicionales,tipoAsignacion,idAsignacionPagada) values(".
									$arrConfiguracion["idFormulario"].",".$arrConfiguracion["idRegistro"].",'".$idObjetoReferencia."','".$idUnidad.
									"','".date("Y-m-d H:i:s")."','".$serieRonda."',".$noRonda.",1,'".cv($comentariosAdicionales)."','".$tipoAsignacion."',-1)";	
							$con->ejecutarConsulta($consulta);
							$idRegistroAsignacion=$con->obtenerUltimoID();
						}
						else
						{
							$aplicaPagoDeuda=true;
							if($funcValidacionPagoDeuda!="")
							{
								eval('$aplicaPagoDeuda='.str_replace("@idUnidad",$idUnidad,$funcValidacionPagoDeuda).";");
							}
							if($aplicaPagoDeuda)
							{
								$consulta="UPDATE 7001_asignacionesObjetos SET situacion=1,rondaPagada=".$noRonda.
										",idFormulario=".$arrConfiguracion["idFormulario"].",idRegistro=".$arrConfiguracion["idRegistro"].
										",fechaAsignacion='".date("Y-m-d H:i:s")."',comentariosAdicionales='".cv($comentariosAdicionales)."' WHERE idAsignacion=".$idAsignacionPagada;
								$con->ejecutarConsulta($consulta);
								$idRegistroAsignacion=$idAsignacionPagada;
							}
							else
								continue;
						}
						
					}
					
					$arrResultado["idUnidad"]=$idUnidad;
					$arrResultado["noRonda"]=$noRonda;
					$arrResultado["pagoDeuda"]=0;
					$arrResultado["idAsignacionPago"]=$idAsignacionPagada;
					$arrResultado["idRegistroAsignacion"]=$idRegistroAsignacion;
					
					return $arrResultado;
				}
				else
				{	
					$consulta="INSERT INTO 7001_asignacionesObjetos(idFormulario,idRegistro,idObjetoReferido,idUnidadReferida,fechaAsignacion,
									tipoRonda,noRonda,situacion,comentariosAdicionales,tipoAsignacion,idAsignacionPagada) values(".
									$arrConfiguracion["idFormulario"].",".$arrConfiguracion["idRegistro"].",'".$idObjetoReferencia."','".$idUnidad.
									"','".date("Y-m-d H:i:s")."','".$serieRonda."',".$noRonda.",2,'".cv($comentariosAdicionales)."','".$tipoAsignacion."',-1)";	
					$con->ejecutarConsulta($consulta);
					
				}
			}
			
		}
		
		$noRonda++;
		if($escribirAsignacion)
		{
			incrementarNoRondaAsignacionObjeto($tipoAsignacion,$serieRonda,$idObjetoReferencia);
		}
		$ciclos++;
		
		
	}
	
	return NULL;	
}

function obtenerNoRondaAsignacionObjeto($tipoAsignacion,$serieRonda,$idObjetoReferencia=-1)
{
	global $con;
	$consulta="SELECT noRonda FROM 7004_seriesRondaAsignacionObjetos WHERE tipoAsignacion='".$tipoAsignacion.
			"' and idObjetoReferencia='".$idObjetoReferencia."' and serieRonda='".$serieRonda."'";
	$noRonda=$con->obtenerValor($consulta);
	if($noRonda=="")
	{
		$noRonda=1;
		$consulta="INSERT INTO 7004_seriesRondaAsignacionObjetos(tipoAsignacion,idObjetoReferencia,serieRonda,noRonda) 
				VALUES('".$tipoAsignacion."','".$idObjetoReferencia."','".$serieRonda."',1)";
		$con->ejecutarConsulta($consulta);		
	}	
	return $noRonda;	
}

function incrementarNoRondaAsignacionObjeto($tipoAsignacion,$serieRonda,$idObjetoReferencia=-1)
{
	global $con;
	
	$consulta="update 7004_seriesRondaAsignacionObjetos set noRonda=noRonda+1 where 
				tipoAsignacion='".$tipoAsignacion."' and idObjetoReferencia='".$idObjetoReferencia."' and serieRonda='".$serieRonda."'";
	return $con->ejecutarConsulta($consulta);
	
}

function obtenerAsignacionesRondaObjeto($idUnidadReferedida,$tipoAsignacion,$tipoRonda,$noRonda,$idObjetoReferencia=-1,$considerarAsigancionesCanceladas=false)
{
	global $con;
	$consulta="SELECT  COUNT(*) FROM 7001_asignacionesObjetos WHERE tipoAsignacion='".$tipoAsignacion."' AND tipoRonda='".
			$tipoRonda."' AND idUnidadReferida='".$idUnidadReferedida."' and idObjetoReferido='".$idObjetoReferencia.
			"' and noRonda=".$noRonda." and situacion in(".($considerarAsigancionesCanceladas?"1,2,10":"1").") and (idAsignacionPagada is null or idAsignacionPagada=-1)";

	$nAsignaciones=$con->obtenerValor($consulta);
	return $nAsignaciones;
}

function obtenerAsignacionesPendientesObjeto($idUnidadReferedida,$tipoAsignacion,$tipoRonda,$noRonda,$considerarDeudasMismaRonda=true,$idObjetoReferencia=-1)
{
	global $con;
	$consulta="SELECT  COUNT(*) FROM 7001_asignacionesObjetos WHERE tipoAsignacion='".$tipoAsignacion."' AND tipoRonda='".
			$tipoRonda."' AND idUnidadReferida='".$idUnidadReferedida."' and idObjetoReferido='".$idObjetoReferencia.
			"' and situacion=2";
	if(!$considerarDeudasMismaRonda)
	 	$consulta.=" and noRonda<>".$noRonda;
			
	$nAsignaciones=$con->obtenerValor($consulta);
	return $nAsignaciones;
}

function obtenerAsignacionesPagadasRondaObjeto($idUnidadReferedida,$tipoAsignacion,$tipoRonda,$noRonda,$idObjetoReferencia=-1)
{
	global $con;
	
	$consulta="SELECT  COUNT(*) FROM 7001_asignacionesObjetos WHERE tipoAsignacion='".$tipoAsignacion."' AND tipoRonda='".
			$tipoRonda."' AND idUnidadReferida='".$idUnidadReferedida."' and idObjetoReferido='".$idObjetoReferencia.
			"' and noRonda=".$noRonda." and situacion=1 and idAsignacionPagada is not null and idAsignacionPagada<>-1";
	$nAsignaciones=$con->obtenerValor($consulta);
	
	
	return $nAsignaciones;
}


function registrarExhortoUnidadCorrespondienteOficialiaPartes($idFormulario,$idRegistro)
{
	global $con;	

	$_SESSION["funcionCargaProceso"]="obtenerAcuseExhorto()";
	$_SESSION["funcionCargaUnicaProceso"]=1;
	$_SESSION["funcionRetrasoCargaProceso"]=1000;
	
	$idRegistroExhorto=-1;
	$consulta="SELECT carpetaExhorto FROM _92_tablaDinamica WHERE iFormulario=".$idFormulario." AND iRegistro=".$idRegistro." and idEstado>0";

	$carpetaExhorto=$con->obtenerValor($consulta);
	if($carpetaExhorto!="")
	{
		$query="SELECT u.id__17_tablaDinamica FROM  7006_carpetasAdministrativas c,_17_tablaDinamica u 
				WHERE carpetaAdministrativa='".$carpetaExhorto."' AND u.claveUnidad=c.unidadGestion";
		
		$idUnidad=$con->obtenerValor($query);
		$query="UPDATE _524_tablaDinamica SET carpetaExhorto='".$carpetaExhorto."',unidadAsignada=".$idUnidad.
				" WHERE id__524_tablaDinamica=".$idRegistro;

		return $con->obtenerValor($query);
	}
	$consulta="SELECT * FROM _92_tablaDinamica WHERE iFormulario=".$idFormulario." AND iRegistro=".$idRegistro." and idEstado>0";
	$fExhorto=$con->obtenerPrimeraFila($consulta);
	if($fExhorto)
	{
		$idRegistroExhorto=$fExhorto[0];
		
		
	}

	$consulta="SELECT * FROM _524_tablaDinamica WHERE id__524_tablaDinamica=".$idRegistro;
	$fRegistro=$con->obtenerPrimeraFilaAsoc($consulta);
	
	if($idRegistroExhorto==-1)
	{
		$unidadGestion="";
		$delegacionExhorto=$fRegistro["delegacionExhorto"];
		$tipoDelito =$fRegistro["tipoDelito"]; //1 Querella; 2 Oficio
		$materiaDestino=$fRegistro["materiaDestino"]; // 1 Adultos; 2 Adolescentes
		$tipoUnidadDestino=$fRegistro["tipoUnidadDestino"]; // 1 control; 2 ejecucion
		
		$resultado=array();
		
		$ugaDestino="";
		
		
		
		$totalImputados=0;
		$totalMujeres=0;
		if($tipoDelito==2)
		{
			$consulta="SELECT p.* FROM 7005_relacionFigurasJuridicasSolicitud r,_47_tablaDinamica p 
						WHERE r.idActividad=".$fRegistro["idActividad"]." AND idFiguraJuridica=4 AND p.id__47_tablaDinamica=r.idParticipante";
			$res=$con->obtenerFilas($consulta);
			while($fila=mysql_fetch_assoc($res))
			{
				if($fila["genero"]==1)
					$totalMujeres++;
				$totalImputados++;
			}
			
			if(($totalImputados>0)&&($totalImputados==$totalMujeres))
				$ugaDestino=46;
		}
		if($ugaDestino!="")
		{
			
			$resultado["idUnidad"]=$ugaDestino;
			$resultado["noRonda"]=0;
			$resultado["idAsignacionPago"]=0;
			$resultado["pagoDeuda"]=0;
		}
		else
		{
		
			if($fRegistro["tipoUnidadDestino"]==3)
			{
				$resultado["idUnidad"]=$fRegistro["unidadDestino"];
				$resultado["noRonda"]=0;
				$resultado["idAsignacionPago"]=0;
				$resultado["pagoDeuda"]=0;
			}
			else
			{
				$consulta="SELECT idUnidadReferida,noRonda,idAsignacionPagada FROM 7001_asignacionesObjetos 
						WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro." AND situacion=1";

				$fAsignacion=$con->obtenerPrimeraFila($consulta);	

				if($fAsignacion)
				{
					$resultado["idUnidad"]=$fAsignacion[0];
					$resultado["noRonda"]=$fAsignacion[1];
					$resultado["idAsignacionPago"]=$fAsignacion[2];
					$resultado["pagoDeuda"]=$resultado["idAsignacionPago"]==-1?0:1;
					
					
					
				}
				else
				{
					$cveTipoUGA="";
					
					if($materiaDestino=="1")
					{
						if($tipoUnidadDestino==1)
						{
							switch($tipoDelito)
							{
								case 1:
									$tipoAsignacion="2A";
									$cveTipoUGA="A";
									$consulta="SELECT u.id__17_tablaDinamica FROM _17_gridDelitosAtiende g, _17_tablaDinamica u 
											WHERE tipoDelito='".$cveTipoUGA."' and u.id__17_tablaDinamica=g.idReferencia order by prioridad";
								break;
								case 2:
									$tipoAsignacion=3;
									$cveTipoUGA="B";
									$consulta="SELECT idReferencia FROM _17_gridDelitosAtiende WHERE tipoDelito='".$cveTipoUGA."'";
									$listaBase=$con->obtenerListaValores($consulta);
									
									$consulta="SELECT fu.idReferencia FROM _286_tablaDinamica f,_361_tablaDinamica fu WHERE delegacion='".$delegacionExhorto."'
											AND fu.fiscalias=f.id__286_tablaDinamica and fu.sistema=1";
									
									$idUGA=$con->obtenerValor($consulta);
									
									$consulta="SELECT claveElemento FROM 1018_catalogoVarios WHERE tipoElemento=30";
									$listaClavesReclusorios=$con->obtenerListaValores($consulta,"'");
									
									$consulta="SELECT tipoDelito FROM _17_gridDelitosAtiende WHERE idReferencia=".$idUGA." AND tipoDelito IN(".$listaClavesReclusorios.")";
									$claveReclusorio=$con->obtenerValor($consulta);
									$tipoAsignacion.="B".$claveReclusorio;
									$consulta="SELECT idReferencia FROM _17_gridDelitosAtiende WHERE idReferencia in(".$listaBase.
									") and tipoDelito='".$claveReclusorio."'";
									
									
								break;
							}
						}
						else
						{
							$tipoAsignacion="4E";
							$cveTipoUGA="E";
							$consulta="SELECT idReferencia FROM _17_gridDelitosAtiende WHERE tipoDelito='".$cveTipoUGA."'";
							$listaBase=$con->obtenerListaValores($consulta);
							
							$consulta="SELECT fu.idReferencia FROM _286_tablaDinamica f,_361_tablaDinamica fu WHERE delegacion='".$delegacionExhorto."'
									AND fu.fiscalias=f.id__286_tablaDinamica and fu.sistema=1";
			
							$idUGA=$con->obtenerValor($consulta);
			
							$consulta="SELECT claveElemento FROM 1018_catalogoVarios WHERE tipoElemento=31";
							$listaClavesReclusorios=$con->obtenerListaValores($consulta,"'");
							
							$consulta="SELECT tipoDelito FROM _17_gridDelitosAtiende WHERE idReferencia=".$idUGA." AND tipoDelito IN(".$listaClavesReclusorios.")";
			
							$claveReclusorio=$con->obtenerValor($consulta);
							$tipoAsignacion.=$claveReclusorio;
							$consulta="SELECT idReferencia FROM _17_gridDelitosAtiende WHERE idReferencia in(".$listaBase.
							") and tipoDelito='".$claveReclusorio."'";
							
			
						}
					}
					else
					{
						if($tipoUnidadDestino==1)
						{
							$tipoAsignacion="5D";
							$cveTipoUGA="D";
						}
						else
						{
							$tipoAsignacion="6EA";
							$cveTipoUGA="EA";
						}
						
						
						$consulta="SELECT u.id__17_tablaDinamica FROM _17_gridDelitosAtiende g, _17_tablaDinamica u 
									WHERE tipoDelito='".$cveTipoUGA."' and u.id__17_tablaDinamica=g.idReferencia order by prioridad";
						
					}

					$listaUGAS=$con->obtenerListaValores($consulta);
					$arrConfiguracion["tipoAsignacion"]=$tipoAsignacion;
					$arrConfiguracion["serieRonda"]="EX";
					$arrConfiguracion["universoAsignacion"]=$listaUGAS;
					$arrConfiguracion["idObjetoReferencia"]=-1;
					$arrConfiguracion["considerarDeudasMismaRonda"]=false;
					$arrConfiguracion["limitePagoRonda"]=0;
					$arrConfiguracion["escribirAsignacion"]=true;
					$arrConfiguracion["idFormulario"]=$idFormulario;
					$arrConfiguracion["idRegistro"]=$idRegistro;
					$resultado= obtenerSiguienteAsignacionObjeto($arrConfiguracion,true);
				}
			}
		
		}
		$consulta="SELECT claveUnidad FROM _17_tablaDinamica WHERE id__17_tablaDinamica=".$resultado["idUnidad"];
		$unidadGestion=$con->obtenerValor($consulta);
		$arrValores=array();
		$arrValores["codigoInstitucion"]=$unidadGestion;
		$arrValores["numeroCausaOrigen"]=$fRegistro["numeroCausaOrigen"];
		$arrValores["autoridaExhortante"]=$fRegistro["juzgadoExhortante"];
		$arrValores["resumen"]=$fRegistro["resumenExhorto"];
		$arrValores["fechaRecepcion"]=$fRegistro["fechaRecepcion"];
		$arrValores["horaRepepcion"]=$fRegistro["horaRecepcion"];
		$arrValores["entidadFederativa"]=$fRegistro["estadoEntidadExhortante"];
		$arrValores["noOficio"]=$fRegistro["noOficio"];
		$arrValores["iFormulario"]=$idFormulario;
		$arrValores["iRegistro"]=$idRegistro;
		$arrValores["idActividad"]=$fRegistro["idActividad"];
		$arrValores["juezExhortante"]=$fRegistro["juezExhortante"];
		$arrValores["delegacionExhorto"]=$fRegistro["delegacionExhorto"];
		
		$arrDocumentos=array();
		$consulta="SELECT idDocumento FROM 9074_documentosRegistrosProceso WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro." AND tipoDocumento in(1,2)";
		$rDocumentos=$con->obtenerFilas($consulta);
		while($fDocumento=mysql_fetch_row($rDocumentos))
		{
			array_push($arrDocumentos,$fDocumento[0]);
		}
		
		$idRegistroExhorto=crearInstanciaRegistroFormulario(92,-1,5,$arrValores,$arrDocumentos,-1,717);
		
	}
	else
	{
		$x=0;
		$consulta=array();
		$consulta[$x]="begin";
		$x++;
		$consulta[$x]="delete from _92_gDelitos(idReferencia,otroDelito,delito) where idReferencia=".$idRegistroExhorto;
		$x++;
		$consulta[$x]="delete from 9074_documentosRegistrosProceso where idFormulario=92 AND idRegistro=".$idRegistroExhorto;
		$x++;
		$consulta[$x]="INSERT INTO 9074_documentosRegistrosProceso(idFormulario,idRegistro,idDocumento,tipoDocumento)
						SELECT '92','".$idRegistroExhorto."' AS idRegistro,idDocumento,tipoDocumento FROM 9074_documentosRegistrosProceso 
						WHERE idFormulario=524 AND idRegistro=".$idRegistro;
		$x++;
		$consulta[$x]="commit";
		$x++;
		$con->ejecutarBloque($consulta);
		cambiarEtapaFormulario(92,$idRegistroExhorto,5,"",-1,"NULL","NULL",717);
	}
	$x=0;
	$consulta=array();
	$consulta[$x]="begin";
	$x++;
	
	$consulta[$x]="INSERT INTO _92_gDelitos(idReferencia,otroDelito,delito)
					SELECT '".$idRegistroExhorto."' AS idReferencia,otroDelito,delito
					FROM _524_gDelitos WHERE idReferencia=".$idRegistro;
	$x++;
	
	$consulta[$x]="set @carpetExhorto:=(SELECT carpetaExhorto FROM _92_tablaDinamica WHERE id__92_tablaDinamica=".$idRegistroExhorto.")";
	$x++;
	$consulta[$x]="UPDATE _524_tablaDinamica SET carpetaExhorto=@carpetExhorto,unidadAsignada=".$resultado["idUnidad"].
				" WHERE id__524_tablaDinamica=".$idRegistro;
	$x++;	
	
	$consulta[$x]="commit";
	$x++;
	return $con->ejecutarBloque($consulta);
	
	
}

function reportarAtencionExhorto($idFormulario,$idRegistro)
{
	global $con;
	$consulta="SELECT iRegistro FROM _92_tablaDinamica WHERE id__92_tablaDinamica=".$idRegistro." AND iFormulario=524";

	$idRegistroBase=$con->obtenerValor($consulta);
	
	if($idRegistroBase!="")
	{
		$consulta="INSERT INTO _525_tablaDinamica(idReferencia,fechaCreacion,responsable,fechaAtencion,resultadoExhorto,motivoIncumplimiento,comentariosAdicionales)
				SELECT ".$idRegistroBase.",'".date("Y-m-d H:i:s")."',".$_SESSION["idUsr"].",fechaAtencion,dictamenFinal,motivoIncumplimiento,comentariosAdicionales 
				FROM _370_tablaDinamica WHERE idReferencia= ".$idRegistro;

		if($con->ejecutarConsulta($consulta))
		{
			$consulta="SELECT id__370_tablaDinamica FROM _370_tablaDinamica WHERE idReferencia=".$idRegistro;
			$idReferencia=$con->obtenerValor($consulta);
			
			$consulta="SELECT documento FROM _370_documentosAsociados WHERE idReferencia=".($idReferencia==""?-1:$idReferencia);

			$res=$con->obtenerFilas($consulta);
			while($fila=mysql_fetch_row($res))
			{
				convertirDocumentoUsuarioDocumentoResultadoProceso($fila[0],524,$idRegistroBase,"acuseExhorto",6);
			}
			

			cambiarEtapaFormulario(524,$idRegistroBase,3,"",-1,"NULL","NULL",923);		
		}
	}
	return true;
	
	
}


function formatearDomicilio($oDomicilio,$convertirMayusculas=false)
{
	global $con;
	$cadDomicilio="";
	
	if(trim($oDomicilio->calle)!="")
	{
		$cadDomicilio="Calle ".trim($oDomicilio->calle);
	}
	
	if(trim($oDomicilio->noExt)!="")
	{
		if($cadDomicilio=="")
			$cadDomicilio="No. ".trim($oDomicilio->noExt);
		else
			$cadDomicilio.=" No. ".trim($oDomicilio->noExt);
	}
	
	if(trim($oDomicilio->noInt)!="")
	{
		if($cadDomicilio=="")
			$cadDomicilio="Int. ".trim($oDomicilio->noInt);
		else
			$cadDomicilio.=" Int. ".trim($oDomicilio->noInt);
	}
	
	if(trim($oDomicilio->colonia)!="")
	{
		if($cadDomicilio=="")
			$cadDomicilio="Colonia ".trim($oDomicilio->colonia);
		else
			$cadDomicilio.=" Colonia ".trim($oDomicilio->colonia);
	}
	
	if(trim($oDomicilio->cp)!="")
	{
		if($cadDomicilio=="")
			$cadDomicilio="C.P. ".trim($oDomicilio->cp);
		else
			$cadDomicilio.=" C.P. ".trim($oDomicilio->cp);
	}
	
	$lblEstado="";
	if(trim($oDomicilio->localidad)!="")
	{
		$lblEstado=trim($oDomicilio->localidad);
	}
	
	if(trim($oDomicilio->lblMunicipio)!="")
	{
		if($lblEstado=="")
			$lblEstado=trim($oDomicilio->lblMunicipio);
		else
			$lblEstado.=", ".trim($oDomicilio->lblMunicipio);
	}
	
	if(trim($oDomicilio->lblEstado)!="")
	{
		if($lblEstado=="")
			$lblEstado=trim($oDomicilio->lblEstado);
		else
			$lblEstado.=", ".trim($oDomicilio->lblEstado);
	}
	
	if($lblEstado!="")
	{
		if($cadDomicilio=="")
			$cadDomicilio=$lblEstado;
		else
			$cadDomicilio.=". ".$lblEstado;
	}
	
	$lblEntreCalle="";
	
	if(trim($oDomicilio->entreCalle)!="")
	{
		if(trim($oDomicilio->yCalle)!="")
		{
			$lblEntreCalle="Entre la calle ".trim($oDomicilio->entreCalle)." y la calle ".trim($oDomicilio->yCalle);
		}
		else
		{
			$lblEntreCalle="Por la calle ".trim($oDomicilio->entreCalle);
		}
		
	}
	else
	{
		if(trim($oDomicilio->yCalle)!="")
		{
			$lblEntreCalle="Por la calle ".trim($oDomicilio->yCalle);
		}
	}
	
	if($lblEntreCalle!="")
	{
		if($cadDomicilio=="")
			$cadDomicilio=$lblEntreCalle;
		else
			$cadDomicilio.=". ".$lblEntreCalle;
	}
	
	if(trim($oDomicilio->referencias)!="")
	{
		if($cadDomicilio=="")
			$cadDomicilio="Otras referencias: ".trim($oDomicilio->referencias);
		else
			$cadDomicilio.=". Otras referencias: ".trim($oDomicilio->referencias);
	}
	
	$lblTelefonos="";
	foreach($oDomicilio->telefonos as $t)
	{
		$lTel='';
		if(trim($t->lada)!="")
		{
			$lTel.="(".trim($t->lada).") ";
		}
		
		$lTel.=trim($t->numero);
		
		if(trim($t->extension)!="")
		{
			$lTel.="Ext. ".trim($t->extension);
		}
		
		if($t->tipoTelefono==1)
			$lTel="[Fijo] ".$lTel;
		else
			$lTel="[Celular] ".$lTel;
		if($lblTelefonos=="")
			$lblTelefonos=$lTel;
		else
			$lblTelefonos.=", ".$lTel;
		
	}
	
	if($lblTelefonos!="")
	{
		if($cadDomicilio=="")
			$cadDomicilio.=" Tel&eacute;fonos de contacto: ".$lblTelefonos;
		else
			$cadDomicilio.=". Tel&eacute;fonos de contacto: ".$lblTelefonos;
	}
	
	
	$lblMail="";
	foreach($oDomicilio->correos as $m)
	{
		$lblMail='';
		
		
		
		if($lblMail=="")
			$lblMail=$m->mail;
		else
			$lblMail.=", ".$m->mail;
		
	}
	
	if($lblMail!="")
	{
		if($cadDomicilio=="")
			$cadDomicilio.=" Correo electr&oacute;nico de contacto: ".$lblMail;
		else
			$cadDomicilio.=". Correo electr&oacute;nico de contacto: ".$lblMail;
	}
	
	if($cadDomicilio=="")
		$cadDomicilio="(Sin domicilio registrado)";
	if($convertirMayusculas)
		return mb_strtoupper($cadDomicilio);
	return $cadDomicilio;
}

function validarConfiguracionProgramacionAudiencia($idFormulario,$idRegistro)
{
	global $con;
	
	$comp="<br><span style='color:#F00'><b>*</b></span> ";
	
	$resultado="";
	$consulta="SELECT tipoAudiencia FROM _185_tablaDinamica WHERE id__185_tablaDinamica=".$idRegistro;
	$tipoAudiencia=$con->obtenerValor($consulta);
	if($tipoAudiencia=="")
	{
		$resultado.=$comp."Primero debe configurar el tipo de audiencia a programar";
	}
	
	return $resultado;
}

function obtenerUltimoDomicilioFiguraJuridica($idParticipante)
{
	global $con;
	$tblMail="7025_correosElectronico";
	$tblTelefono="7025_telefonos";
	$consulta="SELECT calle,noExt,noInterior,colonia,codigoPostal,entidadFederativa,municipio,localidad,entreCalle,
				yCalle,otrasReferencias,idRegistro  FROM 7025_datosContactoParticipante 
				WHERE idParticipante=".$idParticipante." order by fechaCreacion desc";
	$fila=$con->obtenerPrimeraFilaAsoc($consulta);
	if(!$fila)
	{
		$tblMail="_48_correosElectronico";
		$tblTelefono="_48_telefonos";
		$consulta="SELECT calle,noExt,noInterior,colonia,codigoPostal,entidadFederativa,municipio,localidad,entreCalle,
					yCalle,otrasReferencias,id__48_tablaDinamica as idRegistro FROM _48_tablaDinamica WHERE idReferencia=".$idParticipante;	
		
		$fila=$con->obtenerPrimeraFilaAsoc($consulta);
		
		if(!$fila)
		{
			$consulta="SELECT '' as calle,'' as  noExt,'' as noInterior,'' as colonia,'' as codigoPostal,-1 as entidadFederativa,-1 as municipio,'' as localidad,'' as entreCalle,
					'' as yCalle,'' as  otrasReferencias,'-1' as idRegistro ";	
		
			$fila=$con->obtenerPrimeraFilaAsoc($consulta);
		
		}
	}
	
	$arrCorreos="";
	$consulta="SELECT correo FROM ".$tblMail." WHERE idReferencia=".$fila["idRegistro"];
	$res=$con->obtenerFilas($consulta);
	while($f=mysql_fetch_row($res))
	{
		$oM='{"mail":"'.$f[0].'"}';
		if($arrCorreos=="")
			$arrCorreos=$oM;
		else
			$arrCorreos.=",".$oM;
	}
	
	$arrTelefonos="";
 	$consulta="SELECT tipoTelefono,lada,numero,extension FROM ".$tblTelefono." WHERE idReferencia=".$fila["idRegistro"];
	$res=$con->obtenerFilas($consulta);
	while($f=mysql_fetch_row($res))
	{
		$oM='{"tipoTelefono":"'.$f[0].'","lada":"'.$f[1].'","numero":"'.$f[2].'","extension":"'.$f[3].'"}';
		if($arrTelefonos=="")
			$arrTelefonos=$oM;
		else
			$arrTelefonos.=",".$oM;
	}
	
	$consulta="SELECT estado FROM 820_estados WHERE cveEstado='".$fila["entidadFederativa"]."'";
	$lblEstado=$con->obtenerValor($consulta);
	
	$consulta="SELECT municipio FROM 821_municipios WHERE cveMunicipio='".$fila["municipio"]."'";
	$lblMunicipio=$con->obtenerValor($consulta);
	
	$lblDireccion=$fila["calle"];
	
	if($fila["noExt"]!="")
	{
		if($lblDireccion!="")
			$lblDireccion.=" #".$fila["noExt"];
		else
			$lblDireccion="#".$fila["noExt"];
	}
	
	if($fila["noInterior"]!="")
	{
		if($lblDireccion!="")
			$lblDireccion.=" Int. ".$fila["noInterior"];
		else
			$lblDireccion="Int. ".$fila["noInterior"];
	}
	
	if($fila["colonia"]!="")
	{
		if($lblDireccion!="")
			$lblDireccion.=" Colonia ".$fila["colonia"];
		else
			$lblDireccion="Colonia ".$fila["colonia"];
	}
	
	
	if($fila["codigoPostal"]!="")
	{
		if($lblDireccion!="")
			$lblDireccion.=" C.P. ".$fila["codigoPostal"];
		else
			$lblDireccion="C.P. ".$fila["codigoPostal"];
	}
	
	if($fila["localidad"]!="")
	{
		if($lblDireccion!="")
			$lblDireccion.=". ".$fila["localidad"];
		else
			$lblDireccion=$fila["localidad"];
	}
	
	if($lblMunicipio!="")
	{
		if($fila["localidad"]!="")
			$lblDireccion.=", ".$lblMunicipio;
		else
		{
			if($lblDireccion!="")
				$lblDireccion.=". ".$lblMunicipio;
			else
				$lblDireccion=$lblMunicipio;
		}
	}
	
	if($lblEstado!="")
	{
		if(($fila["localidad"]!="")||($lblMunicipio!=""))
			$lblDireccion.=", ".$lblEstado;
		else
		{
			if($lblDireccion!="")
				$lblDireccion.=". ".$lblEstado;
			else
				$lblDireccion=$lblEstado;
		}
	}
	
	
	$obj='{"calle":"'.cv($fila["calle"]).'","noExt":"'.cv($fila["noExt"]).'","noInt":"'.cv($fila["noInterior"]).'","colonia":"'.cv($fila["colonia"]).
		'","cp":"'.$fila["codigoPostal"].'","estado":"'.($fila["entidadFederativa"]==-1?"":$fila["entidadFederativa"]).'","lblEstado":"'.$lblEstado.'","municipio":"'.cv($fila["municipio"]=="-1"?"":$fila["municipio"]).
		'","lblMunicipio":"'.$lblMunicipio.'","localidad":"'.cv($fila["localidad"]).'","entreCalle":"'.cv($fila["entreCalle"]).'","yCalle":"'.cv($fila["yCalle"]).
		'","referencias":"'.cv($fila["otrasReferencias"]).'","telefonos":['.$arrTelefonos.'],"correos":['.$arrCorreos.'],"lblDireccion":"'.cv($lblDireccion).'"}';

	return $obj;
}

function validarGeneracionCarpetaAmparoSolicitud($idFormulario,$idRegistro)
{
	global $con;
	
	$comp="<br><span style='color:#F00'><b>*</b></span> ";
	
	$resultado="";
	$consulta="SELECT  categoriaAmparo FROM _346_tablaDinamica WHERE id__346_tablaDinamica=".$idRegistro;
	$categoriaAmparo=$con->obtenerValor($consulta);
	if($categoriaAmparo==0)
	{
		$resultado.=$comp."Debe indicar la categor&iacute;a del amparo recibido<br>";
	}
	$consulta="SELECT COUNT(*) FROM _346_gActosReclamados WHERE idReferencia=".$idRegistro;
	$nReg=$con->obtenerValor($consulta);
	if($nReg==0)
	{
		$resultado.=$comp."Debe ingresar el acto reclamado";
	}
	
	return $resultado;
}

function validarPromocionCarpetaAmparoSolicitud($idFormulario,$idRegistro)
{
	global $con;
	
	$comp="<br><span style='color:#F00'><b>*</b></span> ";
	
	$resultado="";
	$consulta="SELECT tipoPromocion FROM _460_tablaDinamica WHERE id__460_tablaDinamica=".$idRegistro;
	$tipoPromocion=$con->obtenerValor($consulta);
	if($tipoPromocion=="")
	{
		$resultado.=$comp."Debe indicar el tipo de promoci&oacute;n recibido<br>";
	}

	
	return $resultado;
}

function mostrarSeccionResolucionAmparoTransitorio($idReferencia)
{
	global $con;
	$consulta="SELECT idEstado,resolverAmparoTransitorio FROM _460_tablaDinamica WHERE id__460_tablaDinamica=".$idReferencia;

	$fAmparo=$con->obtenerPrimeraFila($consulta);
	
	if(($fAmparo[0]=="1.8")&&($fAmparo[1]=="1"))
		return 1;
	return 0;
	
	
}

function determinarRolesDestinatariosFirmaDocumento($idFormulario,$idRegistro)
{
	global $con;
	
	$arrDestinatarios=array();
	switch($idFormulario)
	{
		case 460:
			$arrUsuariosNotifica["39_0"]=1; //Jud de amparos y apelaciones
			$arrUsuariosNotifica["19_0"]=1; //Sub de causa y ejecucion
			
			
			foreach($arrUsuariosNotifica as $idRol=>$resto)
			{
				$nDestinatario=$idRol."@@0";
				array_push($arrDestinatarios,$nDestinatario);
			}
			
			return $arrDestinatarios;
		break;
		default:
			return "'19_0'";
		break;
	}
	
}

function esRegistroExhortoTurnadoUnidad($idRegistro)
{
	global $con;
	$consulta="SELECT idEstado FROM _524_tablaDinamica WHERE id__524_tablaDinamica=".$idRegistro;
	$idEstado=$con->obtenerValor($consulta);
	if($idEstado==2)
	{
		return 1;
	}
	return 0;
}


function generarFolioCarpetaUnidadEjecucion_ModuloRegistro($idFormulario,$idRegistro)
{
	global $con;	
		
	$idUnidadGestion=-1;		
	
	$anio=date("Y");
	$query="SELECT (SELECT carpetaAdministrativa FROM 7006_carpetasAdministrativas WHERE idCarpeta=carpetaJudicialOrigen) as carpetaJudicialOrigen,
			carpetaAdministrativa,
			idActividad,
			materiaDestino,
			tipoUnidadDestino,
			tribunalReceptor,
			unidadEjecucionReceptora 
			FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$fDatosCarpeta=$con->obtenerPrimeraFila($query);
	if($fDatosCarpeta[1]=="N/E")
		$fDatosCarpeta[1]="";
		
	$cAdministrativaBase=$fDatosCarpeta[0];
	$carpetaEnjuiciamiento=$fDatosCarpeta[1];
	$tipoUnidadDestino=$fDatosCarpeta[4];
	
	if($carpetaEnjuiciamiento!="")
		return true;
	
	$consulta="SELECT unidadGestion,carpetaInvestigacion FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$cAdministrativaBase."'";	
	$fCarpetaBase=$con->obtenerPrimeraFila($consulta);
	$unidadOrigen=$fCarpetaBase[0];
	$carpetaInvestigacion=$fCarpetaBase[1];

	switch($fDatosCarpeta[6])
	{
		case 1: //Norte
			$idUnidadGestion=51;
		break;
		case 2: //Oriente
		
			$idUnidadGestion=53;
		break;
		case 3: //Sullivan
			$idUnidadGestion=36;
		break;
		case 4: //Adolescentes
			$idUnidadGestion=52;
		break;
	}
	
	$carpetaAdministrativa=obtenerSiguienteCarpetaAdministrativa($idUnidadGestion,$anio,6,$idFormulario,$idRegistro);
	$consulta="SELECT juezResponsable FROM _621_tablaDinamica WHERE carpetaAdministrativa='".$carpetaAdministrativa."' 
			and idEstado>1 order by id__621_tablaDinamica desc";
	$idJuezEjecucion=$con->obtenerValor($consulta);
	if($idJuezEjecucion=="")
		$idJuezEjecucion=-1;
	if($idJuezEjecucion=="-1")	
		$idJuezEjecucion=asignarJuezEjecucionCarpetaUnica($idUnidadGestion,$idFormulario,$idRegistro);
	$fechaCreacionCarpeta=date("Y-m-d H:i:s");
	$idActividadEjecucion=$fDatosCarpeta[2];
	
	$query="SELECT claveUnidad FROM _17_tablaDinamica WHERE id__17_tablaDinamica=".$idUnidadGestion;
	$cveUnidadGestion=$con->obtenerValor($query);
	
	$consulta=array();
	$x=0;
	$consulta[$x]="begin";
	$x++;
	
	$consulta[$x]="INSERT INTO 7006_carpetasAdministrativas(carpetaAdministrativa,fechaCreacion,responsableCreacion,idFormulario,
				idRegistro,unidadGestion,etapaProcesalActual,idActividad,carpetaAdministrativaBase,idJuezTitular,
				tipoCarpetaAdministrativa,situacion,carpetaInvestigacion,llaveCarpetaInvestigacion,unidadGestionOriginal) 
				VALUES('".$carpetaAdministrativa."','".$fechaCreacionCarpeta."',".$_SESSION["idUsr"].",".$idFormulario.",'".
				$idRegistro."','".$cveUnidadGestion."',6,".$idActividadEjecucion.",'".$cAdministrativaBase.
				"',".$idJuezEjecucion.",6,1,(SELECT UPPER('".$carpetaInvestigacion."')),'".cv(generarLlaveCarpetaInvestigacion($carpetaInvestigacion)).
				"','".$cveUnidadGestion."')";
	$x++;
	$consulta[$x]="update _".$idFormulario."_tablaDinamica set carpetaAdministrativa='".$carpetaAdministrativa."',unidadAsignada=".$idUnidadGestion.
				",fechaRecepcion='".$fechaCreacionCarpeta."' where id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$x++;
			
	
	$consulta[$x]="commit";
	$x++;
	
	if($con->ejecutarBloque($consulta))
	{
		if($cAdministrativaBase!="")
		{
			registrarCambioEtapaProcesalCarpeta($cAdministrativaBase,6,$idFormulario,$idRegistro,-1);
			registrarCambioSituacionCarpeta($cAdministrativaBase,11,$idFormulario,$idRegistro,-1);
			
			$query="SELECT carpetaAdministrativaBase,tipoCarpetaAdministrativa FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$cAdministrativaBase."'";
			$fCBase=$con->obtenerPrimeraFila($query);
			if(($fCBase[1]==5)&&($fCBase[0]!=""))
			{
				registrarCambioEtapaProcesalCarpeta($fCBase[0],6,$idFormulario,$idRegistro,-1);
			}
			
			$query="SELECT idActividad FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$cAdministrativaBase."'";
			$idActividadBase=$con->obtenerValor($query);
			if($idActividadBase=="")
				$idActividadBase=-1;
			
			$query=	"SELECT idParticipante FROM 7005_relacionFigurasJuridicasSolicitud WHERE idActividad=".$idActividadEjecucion." AND idFiguraJuridica=4";
			$listParticipantes=$con->obtenerValor($query);
			if($listParticipantes=="")
				$listParticipantes=-1;	
				
			$query="UPDATE 7005_relacionFigurasJuridicasSolicitud SET situacion=6 WHERE idActividad=".$idActividadBase." AND 
					idParticipante IN
					(
					".$listParticipantes."
					)";
			$con->ejecutarConsulta($query);		
			
		}
		
		$query="SELECT idDocumento FROM 9074_documentosRegistrosProceso WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro;
		$rDocumentos=$con->obtenerFilas($query);
		while($fDocumento=mysql_fetch_row($rDocumentos))
		{
			registrarDocumentoCarpetaAdministrativa($carpetaAdministrativa,$fDocumento[0],$idFormulario,$idRegistro);	
		}
		

	}
		
	
	return false;
}

function generarFolioCarpetaTribunalEnjuciamiento_ModuloRegistro($idFormulario,$idRegistro)
{
	global $con;

	$idUnidadGestion=32;
	$anio=date("Y");
	
	$query="SELECT (SELECT carpetaAdministrativa FROM 7006_carpetasAdministrativas WHERE idCarpeta=carpetaJudicialOrigen) as carpetaJudicialOrigen,
			carpetaAdministrativa,
			idActividad,
			materiaDestino,
			tipoUnidadDestino,
			tribunalReceptor,
			unidadEjecucionReceptora 
			FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	
	$fDatosCarpeta=$con->obtenerPrimeraFila($query);
	if($fDatosCarpeta[1]=="N/E")
		$fDatosCarpeta[1]="";	
		
	$cAdministrativaBase=$fDatosCarpeta[0];
	$carpetaEnjuiciamiento=$fDatosCarpeta[1];
	if($carpetaEnjuiciamiento!="")
		return true;
		
	$query="SELECT idActividad,unidadGestion,carpetaInvestigacion FROM 7006_carpetasAdministrativas WHERE 
			carpetaAdministrativa='".$cAdministrativaBase."'";
	$fDatosCarpetaJudicial=$con->obtenerPrimeraFila($query);

	$idActividad=$fDatosCarpeta[2];
	$carpetaInvestigacion=$fDatosCarpetaJudicial[2];
	if($idActividad=="")
		$idActividad=-1;
	
	$unidadGestionCarpeta=$fDatosCarpetaJudicial[1];
	
	$query="SELECT * FROM _17_tablaDinamica WHERE claveUnidad='".$unidadGestionCarpeta."'";
	$fDatosUnidadGestion=$con->obtenerPrimeraFilaAsoc($query);

	$idUnidadGestion=-1;
	switch($fDatosCarpeta[5])
	{
		case 1://Norte
			$query="SELECT id__17_tablaDinamica FROM  _17_tablaDinamica u,_1_tablaDinamica e WHERE 
			u.idReferencia=e.id__1_tablaDinamica AND e.cveInmueble='00020001' AND  id__17_tablaDinamica 
			IN(SELECT idPadre FROM _17_tiposCarpetasAdministra WHERE idOpcion=5)";
	
			$idUnidadGestion=$con->obtenerListaValores($query);
		break;
		case 2://Sur
			$query="SELECT id__17_tablaDinamica FROM  _17_tablaDinamica u,_1_tablaDinamica e WHERE 
			u.idReferencia=e.id__1_tablaDinamica AND e.cveInmueble='00020003' AND  id__17_tablaDinamica 
			IN(SELECT idPadre FROM _17_tiposCarpetasAdministra WHERE idOpcion=5)";
	
			$idUnidadGestion=$con->obtenerListaValores($query);
		break;
		case 3://Oriente
			$query="SELECT id__17_tablaDinamica FROM  _17_tablaDinamica u,_1_tablaDinamica e WHERE 
			u.idReferencia=e.id__1_tablaDinamica AND e.cveInmueble='00020002' AND  id__17_tablaDinamica 
			IN(SELECT idPadre FROM _17_tiposCarpetasAdministra WHERE idOpcion=5)";
			$idUnidadGestion=$con->obtenerListaValores($query);

		break;
		case 4://Sullivan
			$idUnidadGestion=32;
		break;
		case 5://Adolescentes
			$idUnidadGestion=49;
		break;
	}


	if(($idUnidadGestion==-1)||($idUnidadGestion==""))
		return ;
		
	
	
	
	$idUnidadGestionTmp=-1;
	$encontrado=false;
	$listaIng=-1;
	$validaConoceCausa=true;
	
	if(($fDatosCarpeta[3]==2)||($cAdministrativaBase==""))
		$validaConoceCausa=false;
	
	while(!$encontrado)
	{
		
		$arrCarga=array();
		$query="SELECT claveUnidad,id__17_tablaDinamica FROM _17_tablaDinamica WHERE id__17_tablaDinamica IN(".$idUnidadGestion.") and
		id__17_tablaDinamica not in(".$listaIng.")";
		$rTribunales=$con->obtenerFilas($query);
		
		if($con->filasAfectadas>0)
		{
		
			while($fTribunal=mysql_fetch_row($rTribunales))
			{
				$query="SELECT COUNT(*) FROM 7006_carpetasAdministrativas WHERE fechaCreacion>='2018-09-03' AND unidadGestion='".$fTribunal[0].
						"' AND tipoCarpetaAdministrativa=5";
				$nCarpetas=$con->obtenerValor($query);		
				$arrCarga[$fTribunal[1]]=$nCarpetas;
				
			}
			
			
			
			$nCargaMinima=-1;
			foreach($arrCarga as $iTribunal=>$total)
			{
				if($nCargaMinima==-1)
				{
					$nCargaMinima=$total;
				}
				
				if($nCargaMinima>$total)
				{
					$nCargaMinima=$total;
				}
			}
			
			
			
			foreach($arrCarga as $iTribunal=>$total)
			{
				if($total==$nCargaMinima)
				{
					$idUnidadGestionTmp=$iTribunal;
					
					if(!$validaConoceCausa || !conoceJuezTribunalCarpeta($cAdministrativaBase,$iTribunal))	
					{
						$encontrado=true;
						$idUnidadGestion=$idUnidadGestionTmp;
						
					}
					break;
				}
			}
			$listaIng.=",".$idUnidadGestionTmp;
		}
		else
		{
			$listaIng=-1;
			$validaConoceCausa=false;
		}
	}
	
	
	
	
	$carpetaAdministrativa=obtenerSiguienteCarpetaAdministrativa($idUnidadGestion,$anio,5,$idFormulario,$idRegistro);
	
	$query=" SELECT lj.clave FROM _26_tablaDinamica lj,_26_tipoJuez tj WHERE lj.idReferencia=".$idUnidadGestion." AND 
				tj.idPadre =lj.id__26_tablaDinamica AND tj.idOpcion=2  ORDER BY usuarioJuez";
	$listaJueces=$con->obtenerListaValores($query,"'");

	$idJuezTribunal=obtenerSiguienteJuez(20,$listaJueces,-1);

	$query="SELECT claveUnidad,claveFolioCarpetas FROM _17_tablaDinamica WHERE id__17_tablaDinamica=".$idUnidadGestion;
	$fDatosUnidadTribunalE=$con->obtenerPrimeraFila($query);
	$cveUnidadGestion=$fDatosUnidadTribunalE[0];
	
	
	$x=0;
	$consulta[$x]="begin";
	$x++;
	$consulta[$x]="INSERT INTO 7006_carpetasAdministrativas(carpetaAdministrativa,fechaCreacion,responsableCreacion,
					idFormulario,idRegistro,unidadGestion,etapaProcesalActual,idActividad,carpetaAdministrativaBase,
					idJuezTitular,tipoCarpetaAdministrativa,carpetaInvestigacion,llaveCarpetaInvestigacion,unidadGestionOriginal) 
					VALUES('".$carpetaAdministrativa."','".date("Y-m-d H:i:s")."',".$_SESSION["idUsr"].",".$idFormulario.",'".$idRegistro.
					"','".$cveUnidadGestion."',5,".$idActividad.",'".$cAdministrativaBase."',".$idJuezTribunal.",5,(SELECT UPPER('".
					$carpetaInvestigacion."')),'".cv(generarLlaveCarpetaInvestigacion($carpetaInvestigacion))."','".$cveUnidadGestion."')";
	$x++;
	$consulta[$x]="update _".$idFormulario."_tablaDinamica set carpetaAdministrativa='".$carpetaAdministrativa."',
					unidadAsignada=".$idUnidadGestion.",fechaRecepcion='".date("Y-m-d H:i:s")."' where id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$x++;
	
	
	
	$consulta[$x]="commit";
	$x++;
	
	if($con->ejecutarBloque($consulta))
	{
		if($cAdministrativaBase!="")
		{
			registrarCambioEtapaProcesalCarpeta($cAdministrativaBase,5,$idFormulario,$idRegistro,-1);
			registrarCambioSituacionCarpeta($cAdministrativaBase,12,$idFormulario,$idRegistro,-1);
			
			
			$query="SELECT idActividad FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$cAdministrativaBase."'";
			$idActividadBase=$con->obtenerValor($query);
			if($idActividadBase=="")
				$idActividadBase=-1;
				
			$query=	"SELECT idParticipante FROM 7005_relacionFigurasJuridicasSolicitud WHERE idActividad=".$idActividad." AND idFiguraJuridica=4";
			$listParticipantes=$con->obtenerValor($query);
			if($listParticipantes=="")
				$listParticipantes=-1;
					
			$query="UPDATE 7005_relacionFigurasJuridicasSolicitud SET situacion=5 WHERE idActividad=".$idActividadBase." AND 
					idParticipante IN
					(
					".$listParticipantes."
					)";
			$con->ejecutarConsulta($query);	
		}
		$query="SELECT idDocumento FROM 9074_documentosRegistrosProceso WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro;
		$rDocumentos=$con->obtenerFilas($query);
		while($fDocumento=mysql_fetch_row($rDocumentos))
		{
			registrarDocumentoCarpetaAdministrativa($carpetaAdministrativa,$fDocumento[0],$idFormulario,$idRegistro);	
		}
		
		
		
	}
		
	return false;
	
}

function generarFolioCarpetaUnidadControl_ModuloRegistro($idFormulario,$idRegistro)
{
	global $con;	
		
	$idUnidadGestion=-1;		
	
	$anio=date("Y");
	$query="SELECT (SELECT carpetaAdministrativa FROM 7006_carpetasAdministrativas WHERE idCarpeta=carpetaJudicialOrigen) as carpetaJudicialOrigen,
			carpetaAdministrativa,
			idActividad,
			materiaDestino,
			tipoUnidadDestino,
			tribunalReceptor,
			unidadEjecucionReceptora ,
			unidadGestionReceptora
			FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$fDatosCarpeta=$con->obtenerPrimeraFila($query);
	
	
	if($fDatosCarpeta[1]=="N/E")
		$fDatosCarpeta[1]="";
		
	$cAdministrativaBase=$fDatosCarpeta[0];
	$carpetaControl=$fDatosCarpeta[1];
	$tipoUnidadDestino=$fDatosCarpeta[4];
	
	if($carpetaControl!="")
		return true;
	
	$consulta="SELECT unidadGestion,carpetaInvestigacion,etapaProcesalActual FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$cAdministrativaBase."'";	
	$fCarpetaBase=$con->obtenerPrimeraFila($consulta);
	$unidadOrigen=$fCarpetaBase[0];
	$carpetaInvestigacion=$fCarpetaBase[1];
	$idUnidadGestion=$fDatosCarpeta[7];
	
	
	$carpetaAdministrativa=obtenerSiguienteCarpetaAdministrativa($idUnidadGestion,$anio,1,$idFormulario,$idRegistro);

	
	$idJuezEjecucion=-1;
	$fechaCreacionCarpeta=date("Y-m-d H:i:s");
	$idActividadEjecucion=$fDatosCarpeta[2];
	
	$query="SELECT claveUnidad FROM _17_tablaDinamica WHERE id__17_tablaDinamica=".$idUnidadGestion;
	$cveUnidadGestion=$con->obtenerValor($query);
	
	$consulta=array();
	$x=0;
	$consulta[$x]="begin";
	$x++;
	
	$consulta[$x]="INSERT INTO 7006_carpetasAdministrativas(carpetaAdministrativa,fechaCreacion,responsableCreacion,idFormulario,
				idRegistro,unidadGestion,etapaProcesalActual,idActividad,carpetaAdministrativaBase,idJuezTitular,
				tipoCarpetaAdministrativa,situacion,carpetaInvestigacion,llaveCarpetaInvestigacion,unidadGestionOriginal) 
				VALUES('".$carpetaAdministrativa."','".$fechaCreacionCarpeta."',".$_SESSION["idUsr"].",".$idFormulario.",'".
				$idRegistro."','".$cveUnidadGestion."',".($fCarpetaBase?$fCarpetaBase[2]:1).",".$idActividadEjecucion.",'".$cAdministrativaBase.
				"',".$idJuezEjecucion.",1,1,(SELECT UPPER('".$carpetaInvestigacion."')),'".cv(generarLlaveCarpetaInvestigacion($carpetaInvestigacion)).
				"','".$cveUnidadGestion."')";
	$x++;
	$consulta[$x]="update _".$idFormulario."_tablaDinamica set carpetaAdministrativa='".$carpetaAdministrativa."',unidadAsignada=".$idUnidadGestion.
				",fechaRecepcion='".$fechaCreacionCarpeta."' where id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$x++;
			
	
	$consulta[$x]="commit";
	$x++;
	
	if($con->ejecutarBloque($consulta))
	{
		if($cAdministrativaBase!="")
		{
			registrarCambioSituacionCarpeta($cAdministrativaBase,8,$idFormulario,$idRegistro,-1);
			
			$query="SELECT idActividad FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$cAdministrativaBase."'";
			$idActividadBase=$con->obtenerValor($query);
			if($idActividadBase=="")
				$idActividadBase=-1;
			
			$query=	"SELECT idParticipante FROM 7005_relacionFigurasJuridicasSolicitud WHERE idActividad=".$idActividadEjecucion." AND idFiguraJuridica=4";
			$listParticipantes=$con->obtenerValor($query);
			if($listParticipantes=="")
				$listParticipantes=-1;	
				
			$query="UPDATE 7005_relacionFigurasJuridicasSolicitud SET situacion=6 WHERE idActividad=".$idActividadBase." AND 
					idParticipante IN
					(
					".$listParticipantes."
					)";
			$con->ejecutarConsulta($query);		
			
		}
		
		$query="SELECT idDocumento FROM 9074_documentosRegistrosProceso WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro;
		$rDocumentos=$con->obtenerFilas($query);
		while($fDocumento=mysql_fetch_row($rDocumentos))
		{
			registrarDocumentoCarpetaAdministrativa($carpetaAdministrativa,$fDocumento[0],$idFormulario,$idRegistro);	
		}
		

	}
		
	
	return false;
}

function generarFolioCarpetaUnidadEjecucionTribunalEnjuiciamiento_ModuloRegistro($idFormulario,$idRegistro)
{
	global $con;
	$query="SELECT (SELECT carpetaAdministrativa FROM 7006_carpetasAdministrativas WHERE idCarpeta=carpetaJudicialOrigen) as carpetaJudicialOrigen,
			carpetaAdministrativa,
			idActividad,
			materiaDestino,
			tipoUnidadDestino,
			tribunalReceptor,
			unidadEjecucionReceptora 
			FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	
	$fDatosCarpeta=$con->obtenerPrimeraFila($query);
	
	if($fDatosCarpeta[4]==5)
	{
		generarFolioCarpetaTribunalEnjuciamiento_ModuloRegistro($idFormulario,$idRegistro);
	}
	else
	{
		if($fDatosCarpeta[4]==6)
		{
			generarFolioCarpetaUnidadEjecucion_ModuloRegistro($idFormulario,$idRegistro);
		}
		else
		{
			generarFolioCarpetaUnidadControl_ModuloRegistro($idFormulario,$idRegistro);
		}
	}
	
}


function esCarpetaTribunalEnjuciamiento_ModuloRegistro($idFormulario,$idRegistro)
{
	global $con;
	$consulta="SELECT tipoUnidadDestino FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$tUnidadDestino=$con->obtenerValor($consulta);
	
	if($tUnidadDestino==5)
		return 1;
	return 0;
	
}

function esCarpetaEjecucion_ModuloRegistro($idFormulario,$idRegistro)
{
	global $con;
	$consulta="SELECT tipoUnidadDestino FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$tUnidadDestino=$con->obtenerValor($consulta);
	
	if($tUnidadDestino==6)
		return 1;
	return 0;
	
}


function esCarpetaControl_ModuloRegistro($idFormulario,$idRegistro)
{
	global $con;
	$consulta="SELECT tipoUnidadDestino FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$tUnidadDestino=$con->obtenerValor($consulta);
	
	if($tUnidadDestino==12)
		return 1;
	return 0;
	
}

function obtenerCuerpoDocumentoB64($idDocumento)
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
		
	}
}

function determinarSituacionCarpeta($cCarpeta,$iCarpeta)
{
	global $con;
	$consulta="SELECT idActividad,tipoCarpetaAdministrativa FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$cCarpeta."'";
	if($iCarpeta!=-1)
	 	$consulta.=" AND idCarpeta=".$iCarpeta;
	
	$idActividad=$con->obtenerValor($consulta);
	
	$consulta="SELECT COUNT(*) FROM 7005_relacionFigurasJuridicasSolicitud WHERE idActividad=".$idActividad.
			" AND idFiguraJuridica=4 AND situacion=1";

	$nReg=$con->obtenerValor($consulta);
	if($nReg==0)
	{
		registrarCambioSituacionCarpeta($cCarpeta,3,-1,-1,-1,"",$iCarpeta);

	}
	else
	{
		registrarCambioSituacionCarpeta($cCarpeta,1,-1,-1,-1,"",$iCarpeta);
	}
	
}

function registrarCambioSituacionImputado($cCarpeta,$iCarpeta,$imputado,$statusImputado,$detalleStatus,$motivoCambio)
{
	global $con;
	$situacion=1;
	
	$x=0;
	$query="SELECT idActividad,tipoCarpetaAdministrativa FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$cCarpeta."'";
	if($iCarpeta!=-1)
	 	$query.=" AND idCarpeta=".$iCarpeta;

	$fCarpeta=$con->obtenerPrimeraFila($query);
	$idActividad=$fCarpeta[0];

	$query="SELECT carpetaCerrada FROM 7014_situacionImputado WHERE idRegistro=".$statusImputado;

	$carpetaCerrada=$con->obtenerValor($query);
	switch($carpetaCerrada)
	{
		case 0:
			if($detalleStatus!="")
			{
				$query="SELECT carpetaCerrada FROM 7014_detalleSituacionImputado WHERE idRegistro=".$detalleStatus;
				$carpetaCerrada=$con->obtenerValor($query);
				if($carpetaCerrada==1)
					$situacion=0;
			}
		break;
		case 1:
			$situacion=0;
		break;
		case 2:
			switch($fCarpeta[1])
			{
				case 1:

					if(($statusImputado==10)||($statusImputado==9))
						$situacion=0;
				break;
				case 5:
					if($statusImputado!=9)
						$situacion=0;
				break;
				case 6:
					if($statusImputado!=10)
						$situacion=0;
				break;
			}
		
		break;
		
	}
	
	
	
	$query="SELECT situacionProcesal,detalleSituacion FROM 7005_relacionFigurasJuridicasSolicitud WHERE  idActividad=".$idActividad.
				" AND idParticipante=".$imputado." AND idFiguraJuridica=4";


	$fSituacionActual=$con->obtenerPrimeraFila($query);
	$situacionActual=$fSituacionActual[0];
	$detalleSituacionActual=$fSituacionActual[1];
	
	$consulta[$x]="begin";
	$x++;
	
	$consulta[$x]="INSERT INTO 7005_bitacoraCambiosFigurasJuridicas(idActividad,idParticipante,idFiguraJuridica,
					idActorRelacionado,situacionAnterior,situacionActual,fechaCambio,responsableCambio,
					comentariosAdicionales,iFormulario,iReferencia,detalleSituacion,detalleSituacionAnterior)
					values(".$idActividad.",".$imputado.",4,-1,".($situacionActual==""?"NULL":$situacionActual).",".
					$statusImputado.",'".date("Y-m-d H:i:s")."',".$_SESSION["idUsr"].
					",'".cv($motivoCambio)."',-1,-1,".($detalleStatus==""?"NULL":$detalleStatus).
					",".($detalleSituacionActual==""?"NULL":$detalleSituacionActual).")";
	
	$x++;
	
	$consulta[$x]="UPDATE 7005_relacionFigurasJuridicasSolicitud SET situacion=".$situacion.", situacionProcesal=".$statusImputado.
				",detalleSituacion=".($detalleStatus==""?"NULL":$detalleStatus)." WHERE
				idActividad=".$idActividad." AND idParticipante=".$imputado." AND idFiguraJuridica=4";
	$x++;
	$consulta[$x]="commit";
	$x++;
	
	return $con->ejecutarBloque($consulta);
}

function validarFiscaliaEnvioSolicitudInicial($idFormulario,$idRegistro)
{
	global $con;
	$arrSolicitudes[18]=1;
	$comp="<br><span style='color:#F00'><b>*</b></span> ";
	
	$resultado="";
	$consulta="SELECT delitoGrave,tipoAudiencia FROM _46_tablaDinamica WHERE id__46_tablaDinamica=".$idRegistro;
	$fSolicitud=$con->obtenerPrimeraFila($consulta);
	$delitoGrave=$fSolicitud[0];
	if(($delitoGrave==1) &&(!isset($arrSolicitudes[$fSolicitud[1]])))
	{
		$consulta="SELECT claveFiscalia FROM _100_tablaDinamica WHERE idReferencia=".$idRegistro;
		$claveFiscalia=$con->obtenerValor($consulta);
		if($claveFiscalia=="")
			$resultado.=$comp."Debe indicar la fiscalia a la cual pertenece la solicitud";
	}
	
	if($resultado=="")
	{
		$resultado=validarDocumentoAdjuntoEnvioSolicitud($idFormulario,$idRegistro);
	}
	return  $resultado;
	
}

function registrarSolicitudAccesoVideoGrabacion($idFormulario,$idRegistro)
{
	global $con;	
	registrarAccesoVideoGrabacionCentral($idFormulario,$idRegistro);
	$_SESSION["funcionCargaProceso"]="obtenerDocumentoAccesoVideoGrabacion()";
	$_SESSION["funcionCargaUnicaProceso"]=1;
	$_SESSION["funcionRetrasoCargaProceso"]=1000;
	
}

function registrarAccesoVideoGrabacionCentral($idFormulario,$idRegistro)
{
	global $con;
	global $tipoMateria;
	
	$horasVigencia=48;
	
	
	
	$consulta="SELECT * FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$fRegistro=$con->obtenerPrimeraFilaAsoc($consulta);
	
	if(($fRegistro["codigoAcceso"]=="")||($fRegistro["codigoAcceso"]=="N/E"))
	{
		$fechaActual=strtotime($fRegistro["fechaCreacion"]);
	
		$codigoAcceso=str_pad(($tipoMateria=="P"?"PO":$tipoMateria),4,"0",STR_PAD_LEFT);
		$codigoAcceso.=str_pad($fRegistro["idEventoAudiencia"],10,"0",STR_PAD_LEFT);
		$codigoAcceso.=str_pad(rand(1,9999),4,"0",STR_PAD_LEFT);
		
		$fechaVigencia=strtotime("+ ".$horasVigencia." hours",$fechaActual);
		
		$consulta="UPDATE _".$idFormulario."_tablaDinamica SET codigoAcceso='".$codigoAcceso."',vigenciaAcceso='".date("Y-m-d H:i:s",$fechaVigencia).
				"' WHERE id__".$idFormulario."_tablaDinamica= ".$idRegistro;

		
		$con->ejecutarConsulta($consulta);
		$consulta="SELECT * FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
		$fRegistro=$con->obtenerPrimeraFilaAsoc($consulta);
	}
	
	$consulta="SELECT idCarpeta FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$fRegistro["carpetaAdministrativa"]."'";
	$idExpediente=$con->obtenerValor($consulta);
	$consulta="SELECT e.idRegistroEvento AS idEvento,con.carpetaAdministrativa,e.fechaEvento,e.horaInicioEvento AS horaInicial,
					e.horaFinEvento AS horaFinal,e.horaInicioReal , e.horaTerminoReal, e.urlMultimedia, 
					(SELECT tipoAudiencia FROM _4_tablaDinamica WHERE id__4_tablaDinamica=e.tipoAudiencia) AS tipoAudiencia,
					(SELECT nombreSala FROM _15_tablaDinamica WHERE id__15_tablaDinamica=e.idSala) AS sala,
					(SELECT nombreUnidad FROM _17_tablaDinamica WHERE id__17_tablaDinamica=e.idCentroGestion) AS unidadGestion,
					e.situacion, (SELECT GROUP_CONCAT(nombre) FROM 800_usuarios WHERE idUsuario=ej.idJuez) AS juez,
					(SELECT nombreInmueble FROM _1_tablaDinamica WHERE id__1_tablaDinamica=e.idEdificio) AS edificio
					 FROM 7000_eventosAudiencia e,7007_contenidosCarpetaAdministrativa con,7001_eventoAudienciaJuez eJ
					 WHERE  con.tipoContenido=3 AND con.idRegistroContenidoReferencia=e.idRegistroEvento
					 AND ej.idRegistroEvento=e.idRegistroEvento and e.idRegistroEvento=".$fRegistro["idEventoAudiencia"];
			
	$fila=$con->obtenerPrimeraFila($consulta);
	
	$o='{"idEvento":"'.$fila[0].'","carpetaAdministrativa":"'.$fila[1].'","fechaEvento":"'.$fila[2].'","horaInicial":"'.$fila[3].
			'","horaFinal":"'.$fila[4].'","horaInicioReal":"'.$fila[5].'","horaTerminoReal":"'.$fila[6].'"
		,"urlMultimedia":"'.$fila[7].'","tipoAudiencia":"'.cv($fila[8]).'","sala":"'.$fila[9].
		'","unidadGestion":"'.cv($fila[10]).'","situacion":"'.$fila[11].'","juez":"'.$fila[12].'","edificio":"'.cv($fila[13]).'"}';
	
	$materia=str_replace("0","",substr($fRegistro["codigoAcceso"],0,4));	

	$cadObj='{"codigoAcceso":"'.$fRegistro["codigoAcceso"].'","materia":"'.$materia.'","urlMultimedia":"'.$fila[7].
			'","expediente":"'.$fRegistro["carpetaAdministrativa"].'","idExpediente":"'.$idExpediente.
			'","datosComplementarios":"'.bE($o).'","fechaExpira":"'.$fRegistro["vigenciaAcceso"].'"}'	;	

	$fDatosServidor=obtenerURLComunicacionServidorMateria("SW");
	$url=$fDatosServidor[0].($fDatosServidor[1]!=""?":".$fDatosServidor[1]:"");

	try
	{
		$client = new nusoap_client("http://".$url."/webServices/wsInterconexionSistemasBPM.php?wsdl","wsdl");
		$parametros=array();
		$parametros["cadObj"]=$cadObj;
		
		$response = $client->call("registrarCodigoAccesoVideoGrabacion", $parametros);

		@$objResp=json_decode($response);
		if(($objResp)&&($objResp->resultado==1))
		{
			$consulta="UPDATE _".$idFormulario."_tablaDinamica SET idAcuseNotificacion=".$objResp->idAcuse.
						" WHERE id__".$idFormulario."_tablaDinamica= ".$idRegistro;
			
			$con->ejecutarConsulta($consulta);
		}
	}
	catch(Exception $e)
	{
		echo $e->getMessage();
		
		
	}
	
		
}

function esRegistroEtapa2($idFormulario,$idRegistro)
{
	global $con;
	$consulta="SELECT idEstado FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$idEstado=$con->obtenerValor($consulta);
	if($idEstado==2)
	{
		return 1;
	}
	return 0;
}

function noEsRolExcluyeUsuarioLogueado()
{
	global $con;
	$arrRolExcluye["112_0"]="DEGT";
	$arrRolExcluye["172_0"]="JUD Oralidad";
	
	foreach($arrRolExcluye as $rol=>$resto)
	{
		if(existeRol("'".$rol."'"))
		{
			return 0;
		}
	}
	return 1;
}

function obtenerCarpetasJudicialesAntecedentes($carpetaAdministrativa)
{
	global $con;
	$arrCarpetasAntecedentes=array();
	
	$arrCarpetas=array();
	obtenerCarpetasPadre($carpetaAdministrativa,$arrCarpetas);
	foreach($arrCarpetas as $carpeta)
	{
		$arrCarpetasAntecedentes[$carpeta]=1;
	}
	$aCarpetasDerivadas=obtenerCarpetasDerivadas($carpetaAdministrativa,"1,5,6");
	if($aCarpetasDerivadas!="")
	{
		$arrCarpetas=explode(",",$aCarpetasDerivadas);
		foreach($arrCarpetas as $carpeta)
		{
			$arrCarpetasAntecedentes[str_replace("'","",$carpeta)]=1;
		}
	}
	return $arrCarpetasAntecedentes;
}

function determinarLeyendaFiguraJuridica($idFigura,$tipoCarpeta=-1,$carpetaJudicial=-1)
{
	global $con;
	$arrResultado=array();
	if(($carpetaJudicial!=-1)&&($carpetaJudicial!=""))
	{
		$consulta="SELECT tipoCarpetaAdministrativa,unidadGestion FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$carpetaJudicial."'";
		$fDatosCarpeta=$con->obtenerPrimeraFila($consulta);
		$tipoCarpeta=$fDatosCarpeta[0];
		if($tipoCarpeta=="")
			$tipoCarpeta=1;
		$consulta="SELECT tipoMateria FROM _17_tablaDinamica WHERE claveUnidad='".$fDatosCarpeta[1]."'";	
		$tipoMateria=$con->obtenerValor($consulta);
		if($tipoMateria==2)
			$tipoCarpeta*=10;
	}
	
	$arrLeyenda[1][4]["leyenda"]="Imputado";
	$arrLeyenda[1][4]["IDFigura"]="8";
	$arrLeyenda[1][4]["leyendaPlural"]="Imputados";
	$arrLeyenda[5][4]["leyenda"]="Acusado";
	$arrLeyenda[5][4]["IDFigura"]="9";
	$arrLeyenda[5][4]["leyendaPlural"]="Acusados";
	$arrLeyenda[6][4]["leyenda"]="Sentenciado";
	$arrLeyenda[6][4]["IDFigura"]="5";
	$arrLeyenda[6][4]["leyendaPlural"]="Sentenciados";
	$arrLeyenda[1][2]["leyenda"]="Victima/Ofendido";
	$arrLeyenda[1][2]["IDFigura"]="3";
	$arrLeyenda[1][2]["leyendaPlural"]="Victimas/Ofendidos";
	$arrLeyenda[5][2]["leyenda"]="Victima/Ofendido";
	$arrLeyenda[5][2]["IDFigura"]="3";
	$arrLeyenda[5][2]["leyendaPlural"]="Victimas/Ofendidos";
	$arrLeyenda[6][2]["leyenda"]="Victima/Ofendido";
	$arrLeyenda[6][2]["IDFigura"]="3";
	$arrLeyenda[6][2]["leyendaPlural"]="Victimas/Ofendidos";
	//Adolescentes
	$arrLeyenda[10][4]["leyenda"]="Menor";
	$arrLeyenda[10][4]["IDFigura"]="8";
	$arrLeyenda[10][4]["leyendaPlural"]="Menores";
	$arrLeyenda[50][4]["leyenda"]="Acusado";
	$arrLeyenda[50][4]["IDFigura"]="9";
	$arrLeyenda[50][4]["leyendaPlural"]="Acusados";
	$arrLeyenda[60][4]["leyenda"]="Sentenciado";
	$arrLeyenda[60][4]["IDFigura"]="5";
	$arrLeyenda[60][4]["leyendaPlural"]="Sentenciados";
	$arrLeyenda[10][2]["leyenda"]="Victima/Ofendido";
	$arrLeyenda[10][2]["IDFigura"]="3";
	$arrLeyenda[10][2]["leyendaPlural"]="Victimas/Ofendidos";
	$arrLeyenda[50][2]["leyenda"]="Victima/Ofendido";
	$arrLeyenda[50][2]["IDFigura"]="3";
	$arrLeyenda[50][2]["leyendaPlural"]="Victimas/Ofendidos";
	$arrLeyenda[60][2]["leyenda"]="Victima/Ofendido";
	$arrLeyenda[60][2]["IDFigura"]="3";
	$arrLeyenda[60][2]["leyendaPlural"]="Victimas/Ofendidos";
	

	if(isset($arrLeyenda[$tipoCarpeta][$idFigura]))
	{
		$arrResultado[0]=$idFigura;
		$arrResultado[1]=$arrLeyenda[$tipoCarpeta][$idFigura]["leyenda"];
		$arrResultado[2]=$arrLeyenda[$tipoCarpeta][$idFigura]["IDFigura"];
		$arrResultado[3]=$arrLeyenda[$tipoCarpeta][$idFigura]["leyendaPlural"];
		
	}
	else
	{
		$consulta="SELECT nombreTipo,etiquetaPlural FROM _5_tablaDinamica WHERE id__5_tablaDinamica=".$idFigura;
		$fFiguraJuridica=$con->obtenerPrimeraFila($consulta);
		$arrResultado[0]=$idFigura;
		$arrResultado[1]=$fFiguraJuridica[0];
		$consulta="SELECT id__414_tablaDinamica FROM _414_tablaDinamica WHERE figuraJuridica=".$idFigura;
		$arrResultado[2]=$con->obtenerValor($consulta);
		
		$consulta="SELECT nombreTipo FROM _5_tablaDinamica WHERE id__5_tablaDinamica=".$idFigura;
		$arrResultado[3]=$fFiguraJuridica[1];
	}
	
	return $arrResultado;
	
}

function generarCodigoQRPDF($objDocumentoPDF)
{
	global $urlServidorQR;
	global $llaveQR;
	global $utilizarServidorQR;
	$docXML=NULL;
	try
	{
		if($utilizarServidorQR)
		{
			$client = new nusoap_client($urlServidorQR."?wsdl","wsdl");
			
			$datos_documento_entrada = array( "datos_documento_entrada" => array(
																					 'token' => $llaveQR,
																					 't_documento' => $objDocumentoPDF["nombreDocumento"],
																					 'fecha_documento' => $objDocumentoPDF["fechaDocumento"],
																					 'ecc' => "H",
																					 'size' => "1",
																					 'publico' => 1,
																					 'documento' => $objDocumentoPDF["documentoPDF"],
																					 'areaEmite' => $objDocumentoPDF["areaEmisora"],
																					 'usuarioEmite' => $objDocumentoPDF["usuarioEmisor"],
																					 'CoordX' => isset($objDocumentoPDF["posX"])?$objDocumentoPDF["posX"]:'182',
																					 'CoordY' => isset($objDocumentoPDF["posY"])?$objDocumentoPDF["posY"]:'275'
																					)
												);
		
			
			
			$docXML = $client->call("getQR", $datos_documento_entrada);
			
	
		}
		else
		{
			$docXML["estatus"]=1;
			$docXML["mensaje"]=1;
			$docXML["n_documento"]="";
			$docXML["codigoQR"]="";
			$docXML["pdfSellado"]=$objDocumentoPDF["documentoPDF"];
		}
	}

	catch(Exception $e)
	{

		$docXML["estatus"]=0;
		$docXML["mensaje"]=$e->getMessage();
		$docXML["n_documento"]="";
		$docXML["codigoQR"]="";
		$docXML["pdfSellado"]="";
  
	}
	return $docXML;
}

function cancelarRegistroExhortoOficialiaPartes($idFormulario,$idRegistro)
{
	global $con;
	
	$consulta="SELECT carpetaExhorto FROM _524_tablaDinamica WHERE id__524_tablaDinamica=".$idRegistro;
	$carpetaJudicial=$con->obtenerValor($consulta);
	$x=0;
	$query[$x]="begin";
	$x++;
	
	$consulta="SELECT c.fechaCreacion,u.id__17_tablaDinamica FROM 7006_carpetasAdministrativas c,_17_tablaDinamica u WHERE carpetaAdministrativa='".$carpetaJudicial."'
			AND u.claveUnidad=c.unidadGestion";
	$fDatosCarpeta=$con->obtenerPrimeraFila($consulta);
	if($fDatosCarpeta)
	{
		$arrCarpeta=explode("/",$carpetaJudicial);
		$folio=($arrCarpeta[1]*1)-1;
		
		
		$query[$x]="DELETE FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$carpetaJudicial."'";
		$x++;
		$query[$x]="DELETE FROM 7007_contenidosCarpetaAdministrativa WHERE carpetaAdministrativa='".$carpetaJudicial."'";
		$x++;
		$query[$x]="DELETE FROM 9060_tableroControl_4 WHERE numeroCarpetaAdministrativa='".$carpetaJudicial."'";
		$x++;
		$query[$x]="UPDATE _92_tablaDinamica SET carpetaExhorto='Cancelado [".$carpetaJudicial."]', idEstado=0 WHERE iFormulario=".$idFormulario.
					" AND iRegistro=".$idRegistro." and idEstado>0";
		$x++;
		
		$consulta="select folioActual from 7004_seriesUnidadesGestion WHERE idUnidadGestion=".$fDatosCarpeta[1].
				" AND anio=".date("Y",strtotime($fDatosCarpeta[0]))." AND tipoDelito='EX'";
		$folioActual=$con->obtenerValor($consulta);
		if($folioActual>$folio)
		{
		
			$query[$x]="UPDATE 7004_seriesUnidadesGestion SET folioActual=".$folio." WHERE idUnidadGestion=".$fDatosCarpeta[1].
					" AND anio=".date("Y",strtotime($fDatosCarpeta[0]))." AND tipoDelito='EX'";
			$x++;
		}
	}
	$query[$x]="UPDATE _524_tablaDinamica SET unidadAsignada=-1,carpetaExhorto='[Cancelado ".$carpetaJudicial."]' WHERE id__524_tablaDinamica=".$idRegistro;
	$x++;
	$query[$x]="commit";
	$x++;
	
	return $con->ejecutarBloque($query);
}

function buscarParticipanteExhorto($figuraJuridica,$nombreParticipante)
{
	global $con;
	
	$listaRegistros="";
	$resultado=buscarCoincidenciasCriterio(1,$nombreParticipante,60,$figuraJuridica,524,0);
	
	$resResultado=$resultado[0];
	while($fila=mysql_fetch_row($resResultado))
	{
		if($listaRegistros=="")
			$listaRegistros=$fila[0];
		else
			$listaRegistros.=",".$fila[0];
	}
	if($listaRegistros=="")
		$listaRegistros=-1;
	
	
	return "'".bE($listaRegistros)."'";
}

function desHabilitaraTareaAsignacionEventoJuez($idFormulario,$idRegistro,$lJuecesAsignados)
{
	global $con;
	$consulta='UPDATE 9060_tableroControl_4 SET idEstado=4 WHERE iFormulario='.$idFormulario.' AND iRegistro='.$idRegistro.' AND idNotificacion IN(7,9,58) 
				AND objConfiguracion=\'{"actorAccesoProceso":"13_0","funcionApertura":"mostrarVentanaAperturaProcesoNotificacion"}\' 
				AND idUsuarioDestinatario NOT IN ('.($lJuecesAsignados==""?-1:$lJuecesAsignados).')';
	$con->ejecutarConsulta($consulta);
}

function registrarRecuperacionFolio($idUnidad,$folio,$tipoCarpeta)
{
	global $con;
	$consulta="INSERT INTO 7048_registroRecuperacionFolio(idUnidadGestion,folioRecuperado,fechaRecuperacion,tipoCarpeta) 
			VALUES(".$idUnidad.",".$folio.",'".date("Y-m-d H:i:s")."','".$tipoCarpeta."')";
	return $con->ejecutarConsulta($consulta);
}


function removerDocumentoSistema($idDocumento)
{
	global $con;
	$x=0;
	$consulta[$x]="begin";
	$x++;
	$consulta[$x]="UPDATE 908_archivos SET eliminado=1 WHERE idArchivo=".$idDocumento;
	$x++;
	$consulta[$x]="DELETE FROM 7007_contenidosCarpetaAdministrativa WHERE tipoContenido=1 AND idRegistroContenidoReferencia=".$idDocumento;
	$x++;
	$consulta[$x]="DELETE FROM 9074_documentosRegistrosProceso WHERE idDocumento=".$idDocumento;
	$x++;
	$consulta[$x]="commit";
	$x++;
	return $con->ejecutarBloque($consulta);
}


function esUsuarioAdscriptoDEGJ()
{
	if($_SESSION["codigoInstitucion"]=="902")
	{
		return 1;
	}
	
	return 0;
}

function sendMensajeWhatApp($numeroCel,$mensaje,$prefijoCelular)
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

}

function registrarJuecesGuardiaLeyMujeres($idRegistro,$cadObj)
{
	global $con;
	$cObj=bD($cadObj);
	$obj=json_decode($cObj);
	
	$x=0;
	$consulta[$x]="begin";
	$x++;
	$consulta[$x]="DELETE FROM _624_juecesGuardia where idReferencia=".$idRegistro;
	$x++;
	foreach($obj->arrJueces as $r)
	{
		$consulta[$x]="insert into _624_juecesGuardia(idReferencia,idJuez,noJuez,idUnidadGestion)
					VALUES(".$idRegistro.",".$r->idJuez.",'".$r->noJuez."',".$r->nombreUnidadGestion.")";
		$x++;
	}
	$consulta[$x]="commit";
	$x++;
	return $con->ejecutarBloque($consulta);
}


function generarFolioCarpetaLeyMujeresLibreViolencia($idFormulario,$idRegistro)
{
	global $con;
	$modoDebug=false;
	$validaPreAsignacion=true;
	$anio=date("Y");
	
	$query="SELECT idUsuario FROM 807_usuariosVSRoles WHERE idRol=218";
	$listaExclusion=$con->obtenerListaValores($query);
	if($listaExclusion=="")
		$listaExclusion=-1;
	$query="SELECT idActividad,carpetaAdministrativa,fechaCreacion FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$fRegistroSolicitud=$con->obtenerPrimeraFila($query);
	
	if($validaPreAsignacion)
	{
		$query="SELECT COUNT(*) FROM 7006_carpetasAdministrativas WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro." AND tipoCarpetaAdministrativa=1";
		$nCarpetas=$con->obtenerValor($query);
		if($nCarpetas>0)
			return true;
	}
	$idActividad=$fRegistroSolicitud[0];
	$carpetaInvestigacion="";
	$lista="";
	//$fRegistroSolicitud[2]="2020-04-01 15:00";
	$tmeFechaRegistro=strtotime($fRegistroSolicitud[2]);
	

	$tmeFechaHoraReferenciaGuardia="";
	$tipoHorario=determinarTipoHorarioLeyMujeresLibreViolencia($fRegistroSolicitud[2]);
	if($modoDebug)
	{
		echo "Fecha de referencia: ".$fRegistroSolicitud[2]."<br>";
		echo "Tipo de horario: ".$tipoHorario."<br>";
	}
	$llaveGuardia="";
	$aplicarFiltro=true;
	switch($tipoHorario)
	{
		case 1: //Normal
			$aplicarFiltro=false;
			$llaveGuardia=0;
			
			$consulta="SELECT id__17_tablaDinamica FROM _17_tablaDinamica u,_17_tiposCarpetasAdministra tC WHERE 
					tC.idPadre=u.id__17_tablaDinamica AND tC.idOpcion=10 ORDER BY prioridad";
			$listaUnidadGestion=$con->obtenerListaValores($consulta);
			
			$arrConfiguracion["tipoAsignacion"]="UGJ";
			$arrConfiguracion["serieRonda"]="UGJ_LMLV";
			$arrConfiguracion["universoAsignacion"]=$listaUnidadGestion;
			$arrConfiguracion["idObjetoReferencia"]=-1;
			$arrConfiguracion["pagarDeudasAsignacion"]=false;
			$arrConfiguracion["considerarDeudasMismaRonda"]=false;
			$arrConfiguracion["limitePagoRonda"]=0;
			$arrConfiguracion["escribirAsignacion"]=true;
			$arrConfiguracion["idFormulario"]=$idFormulario;
			$arrConfiguracion["idRegistro"]=$idRegistro;
			
			$resultado= obtenerSiguienteAsignacionObjeto($arrConfiguracion,true);
//			$resultado["idUnidad"]=33;
			$listaUnidadGestion=$resultado["idUnidad"];
			
			
			if($listaUnidadGestion=="")
				$listaUnidadGestion=-1;
			
			
			
			$consulta="SELECT t.usuarioJuez FROM _26_tablaDinamica t,_26_tipoJuez j WHERE idReferencia in(".$listaUnidadGestion.") AND j.idPadre=t.id__26_tablaDinamica
						AND j.idOpcion=1 and usuarioJuez <>-1 and usuarioJuez is not null  and juezPrestado=0  order by clave";
			
			$lista=$con->obtenerListaValores($consulta);
			$llaveGuardia="UGJ_".$resultado["idUnidad"];
		break;
		case 2: //Guardia
		
			$lista="";
			$listaUnidadGestion="";
			$arrNivel["A"]=1;
			$arrNivel["B"]=1;
			$tmeFechaHoraReferenciaGuardia=strtotime(date("Y-m-d ",$tmeFechaRegistro)." 13:30");
			foreach($arrNivel as $nivel=>$resto)
			{
				$iGuardia="";
				$consulta="SELECT idReferencia FROM _17_gridDelitosAtiende WHERE tipoDelito='".$nivel."' and idReferencia in 
							(SELECT unidadGestion FROM _290_tablaDinamica WHERE '".date("Y-m-d",$tmeFechaRegistro).
									"'>=fechaInicial AND '".date("Y-m-d",$tmeFechaRegistro)."'<=fechaFinal) limit 0,2";
	
				$lista=$con->obtenerListaValores($consulta);
	
				if($con->filasAfectadas>1)
				{
					if($tmeFechaRegistro<$tmeFechaHoraReferenciaGuardia)
					{
						$consulta="SELECT id__290_tablaDinamica FROM _290_tablaDinamica WHERE fechaFinal='".date("Y-m-d",$tmeFechaRegistro)."'
									and unidadGestion in(".$lista.") order by id__290_tablaDinamica limit 0,1";
						$iGuardia=$con->obtenerValor($consulta);
						
						
						$consulta="SELECT unidadGestion FROM _290_tablaDinamica WHERE fechaFinal='".date("Y-m-d",$tmeFechaRegistro)."'
									and unidadGestion in(".$lista.") order by id__290_tablaDinamica limit 0,1";
						$lista=$con->obtenerListaValores($consulta);	
						
						
						
						
					}
					else
					{
						$consulta="SELECT id__290_tablaDinamica FROM _290_tablaDinamica WHERE fechaInicial='".date("Y-m-d",$tmeFechaRegistro)."'
									and unidadGestion in(".$lista.") order by id__290_tablaDinamica limit 0,1";
						$iGuardia=$con->obtenerValor($consulta);
						
						$consulta="SELECT unidadGestion FROM _290_tablaDinamica WHERE fechaInicial='".date("Y-m-d",$tmeFechaRegistro)."'
									and unidadGestion in(".$lista.") order by id__290_tablaDinamica limit 0,1";
						$lista=$con->obtenerListaValores($consulta);
					}
					
					
					
				}
				else
				{
					$consulta="SELECT id__290_tablaDinamica FROM _290_tablaDinamica g,_17_gridDelitosAtiende d WHERE '".date("Y-m-d",$tmeFechaRegistro).
									"'>=fechaInicial AND '".date("Y-m-d",$tmeFechaRegistro)."'<=fechaFinal and
									g.unidadGestion=d.idReferencia and d.tipoDelito='".$nivel."'";
				
					$iGuardia=$con->obtenerValor($consulta);
					
				
				}
				
				
				if($llaveGuardia=="")
					$llaveGuardia=$iGuardia;
				else
					$llaveGuardia.="_".$iGuardia;
				
				
				if($listaUnidadGestion=="")
					$listaUnidadGestion=$lista;
				else
					$listaUnidadGestion.=",".$lista;
				
			}
			if($modoDebug)
				echo "Unidades Guardia: ".$listaUnidadGestion."<br>";
			//$arrUnidadesGestion=explode(",",$listaUnidadGestion);
			
			$arrConfiguracion["tipoAsignacion"]="UGJ";
			$arrConfiguracion["serieRonda"]="UGJ_LMLV_".$llaveGuardia;
			$arrConfiguracion["universoAsignacion"]=$listaUnidadGestion;
			$arrConfiguracion["idObjetoReferencia"]=-1;
			$arrConfiguracion["pagarDeudasAsignacion"]=false;
			$arrConfiguracion["considerarDeudasMismaRonda"]=false;
			$arrConfiguracion["limitePagoRonda"]=0;
			$arrConfiguracion["escribirAsignacion"]=true;
			$arrConfiguracion["idFormulario"]=$idFormulario;
			$arrConfiguracion["idRegistro"]=$idRegistro;

			$resultado= obtenerSiguienteAsignacionObjeto($arrConfiguracion,true);
			
			$listaUnidadGestion=$resultado["idUnidad"];
			if($listaUnidadGestion=="")
				$listaUnidadGestion=-1;
			$lista="";
			$arrUnidadesGestion=array();
			array_push($arrUnidadesGestion,$listaUnidadGestion);
			
			foreach($arrUnidadesGestion as $u)
			{
				$consulta="SELECT distinct j.usuarioJuez FROM _13_tablaDinamica gj,_26_tablaDinamica j, _26_tipoJuez tJ WHERE 
							j.idReferencia=".$u." AND j.usuarioJuez=gj.usuarioJuez
							AND '".date("Y-m-d H:i",$tmeFechaRegistro)."'>=fechaInicio AND '".date("Y-m-d H:i",$tmeFechaRegistro)."'<=fechaFinalizacion AND j.idEstado=1 

							and tJ.idPadre=j.id__26_tablaDinamica and tJ.idOpcion=1 and juezPrestado=0  ORDER BY j.clave";

				$lJueces=$con->obtenerListaValores($consulta);
				
				if($lJueces=="")
				{
					$consulta="SELECT j.usuarioJuez FROM _26_tablaDinamica j,_26_tipoJuez tJ WHERE j.idReferencia=".$u." AND tJ.idPadre=j.id__26_tablaDinamica 
								and usuarioJuez <>-1 and usuarioJuez is not null and juezPrestado=0  ORDER BY clave";
					$lJueces=$con->obtenerListaValores($consulta);
				}
				
				if($lista=="")
					$lista=$lJueces;
				else
					$lista.=",".$lJueces;
					
				
			}
			
		break;
		case 3: //Guardia Ley Mujeres
			$tmeFechaHoraReferenciaGuardia=strtotime(date("Y-m-d ",$tmeFechaRegistro)." 08:59");
			$consulta="SELECT * FROM _624_tablaDinamica WHERE '".date("Y-m-d H:i:s",$tmeFechaRegistro)."'>=CAST(CONCAT(fechaInicio,' ',horaInicio) AS DATETIME)
					 AND '".date("Y-m-d H:i:s",$tmeFechaRegistro)."'<=CAST(CONCAT(fechaFin,' ',horaFin) AS DATETIME) AND idEstado=2 order by id__624_tablaDinamica";
			
						$resFechas=$con->obtenerFilas($consulta);
			while($fila=mysql_fetch_assoc($resFechas))
			{
				if(colisionaTiempo($fila["fechaInicio"]." ".$fila["horaInicio"],$fila["fechaFin"]." ".$fila["horaFin"],$fRegistroSolicitud[2],$fRegistroSolicitud[2],false))
				{
					if($llaveGuardia=="")
					{
						$llaveGuardia=$fila["id__624_tablaDinamica"];
					}
					else
						$llaveGuardia.="_".$fila["id__624_tablaDinamica"];
					
					$consulta="SELECT idJuez,noJuez,idUnidadGestion FROM _624_juecesGuardia WHERE idReferencia=".$fila["id__624_tablaDinamica"];
					$res=$con->obtenerFilas($consulta);
					while($filaJuez=mysql_fetch_row($res))
					{
						if($lista=="")
						{
							$lista=$filaJuez[0];
						}
						else
							$lista.=",".$filaJuez[0];
					}
				}
			}
		break;
		case 4: //Guardia Especial
		
			$lista="";
			$listaUnidadGestion="";
			$arrNivel["A"]=1;
			$arrNivel["B"]=1;
			
			
			
			$tmeFechaHoraReferenciaGuardia=strtotime(date("Y-m-d ",$tmeFechaRegistro)." 23:59");
			if($tmeFechaRegistro>strtotime("2020-12-16"))
			{
				$tmeFechaRegistro=strtotime("2020-12-15 23:00");
			}
			foreach($arrNivel as $nivel=>$resto)
			{
				$iGuardia="";
				$consulta="SELECT idReferencia FROM _17_gridDelitosAtiende WHERE tipoDelito='".$nivel."' and idReferencia in 
							(SELECT unidadGestion FROM _290_tablaDinamica WHERE '".date("Y-m-d",$tmeFechaRegistro).
									"'>=fechaInicial AND '".date("Y-m-d",$tmeFechaRegistro)."'<=fechaFinal) limit 0,2";
	
				$lista=$con->obtenerListaValores($consulta);
	
				if($con->filasAfectadas>1)
				{
					if($tmeFechaRegistro<$tmeFechaHoraReferenciaGuardia)
					{
						$consulta="SELECT id__290_tablaDinamica FROM _290_tablaDinamica WHERE fechaFinal='".date("Y-m-d",$tmeFechaRegistro)."'
									and unidadGestion in(".$lista.") order by id__290_tablaDinamica limit 0,1";
						$iGuardia=$con->obtenerValor($consulta);
						
						
						$consulta="SELECT unidadGestion FROM _290_tablaDinamica WHERE fechaFinal='".date("Y-m-d",$tmeFechaRegistro)."'
									and unidadGestion in(".$lista.") order by id__290_tablaDinamica limit 0,1";
						$lista=$con->obtenerListaValores($consulta);	
						
						
						
						
					}
					else
					{
						$consulta="SELECT id__290_tablaDinamica FROM _290_tablaDinamica WHERE fechaInicial='".date("Y-m-d",$tmeFechaRegistro)."'
									and unidadGestion in(".$lista.") order by id__290_tablaDinamica limit 0,1";
						$iGuardia=$con->obtenerValor($consulta);
						
						$consulta="SELECT unidadGestion FROM _290_tablaDinamica WHERE fechaInicial='".date("Y-m-d",$tmeFechaRegistro)."'
									and unidadGestion in(".$lista.") order by id__290_tablaDinamica limit 0,1";
						$lista=$con->obtenerListaValores($consulta);
					}
					
					
					
				}
				else
				{
					$consulta="SELECT id__290_tablaDinamica FROM _290_tablaDinamica g,_17_gridDelitosAtiende d WHERE '".date("Y-m-d",$tmeFechaRegistro).
									"'>=fechaInicial AND '".date("Y-m-d",$tmeFechaRegistro)."'<=fechaFinal and
									g.unidadGestion=d.idReferencia and d.tipoDelito='".$nivel."'";
				
					$iGuardia=$con->obtenerValor($consulta);
					
				
				}
				
				
				if($llaveGuardia=="")
					$llaveGuardia=$iGuardia;
				else
					$llaveGuardia.="_".$iGuardia;
				
				
				if($listaUnidadGestion=="")
					$listaUnidadGestion=$lista;
				else
					$listaUnidadGestion.=",".$lista;
				
			}
			if($modoDebug)
				echo "Unidades Guardia: ".$listaUnidadGestion."<br>";
			//$arrUnidadesGestion=explode(",",$listaUnidadGestion);
			
			$arrConfiguracion["tipoAsignacion"]="UGJ";
			$arrConfiguracion["serieRonda"]="UGJ_LMLV_".$llaveGuardia;
			$arrConfiguracion["universoAsignacion"]=$listaUnidadGestion;
			$arrConfiguracion["idObjetoReferencia"]=-1;
			$arrConfiguracion["pagarDeudasAsignacion"]=false;
			$arrConfiguracion["considerarDeudasMismaRonda"]=false;
			$arrConfiguracion["limitePagoRonda"]=0;
			$arrConfiguracion["escribirAsignacion"]=true;
			$arrConfiguracion["idFormulario"]=$idFormulario;
			$arrConfiguracion["idRegistro"]=$idRegistro;

			$resultado= obtenerSiguienteAsignacionObjeto($arrConfiguracion,true);
			
			$listaUnidadGestion=$resultado["idUnidad"];
			if($listaUnidadGestion=="")
				$listaUnidadGestion=-1;
			$lista="";
			$arrUnidadesGestion=array();
			array_push($arrUnidadesGestion,$listaUnidadGestion);
			
			foreach($arrUnidadesGestion as $u)
			{
				$consulta="SELECT distinct j.usuarioJuez FROM _13_tablaDinamica gj,_26_tablaDinamica j, _26_tipoJuez tJ WHERE 
							j.idReferencia=".$u." AND j.usuarioJuez=gj.usuarioJuez
							AND '".date("Y-m-d H:i",$tmeFechaRegistro)."'>=fechaInicio AND '".date("Y-m-d H:i",$tmeFechaRegistro)."'<=fechaFinalizacion AND j.idEstado=1 

							and tJ.idPadre=j.id__26_tablaDinamica and tJ.idOpcion=1 and juezPrestado=0  ORDER BY j.clave";

				$lJueces=$con->obtenerListaValores($consulta);
				
				if($lJueces=="")
				{
					$consulta="SELECT j.usuarioJuez FROM _26_tablaDinamica j,_26_tipoJuez tJ WHERE j.idReferencia=".$u." AND tJ.idPadre=j.id__26_tablaDinamica 
								and usuarioJuez <>-1 and usuarioJuez is not null and juezPrestado=0  ORDER BY clave";
					$lJueces=$con->obtenerListaValores($consulta);
				}
				
				if($lista=="")
					$lista=$lJueces;
				else
					$lista.=",".$lJueces;
					
				
			}
			
		break;
		
	}
	
	if($modoDebug)
	{
		$arrJueces=explode(",",$lista);
		foreach($arrJueces as $j)
		{
			$consulta="SELECT clave,(SELECT Nombre FROM 800_usuarios WHERE idUsuario=t.usuarioJuez) AS juez,
					(SELECT nombreUnidad FROM _17_tablaDinamica WHERE id__17_tablaDinamica=t.idReferencia),usuarioJuez 
					FROM _26_tablaDinamica t WHERE usuarioJuez in(".$lista.")";
		
			$fila=$con->obtenerPrimeraFila($consulta);
			
			echo $fila[0]." (".$fila[3].") ".$fila[1]." ".$fila[2]."<br>";
			
		}
	}
	
	
	
	$arrConfiguracion["tipoAsignacion"]=$llaveGuardia;
	$arrConfiguracion["serieRonda"]="LMLV";
	$arrConfiguracion["universoAsignacion"]=$lista;
	$arrConfiguracion["idObjetoReferencia"]=-1;
	$arrConfiguracion["pagarDeudasAsignacion"]=false;
	$arrConfiguracion["considerarDeudasMismaRonda"]=true;
	$arrConfiguracion["limitePagoRonda"]=1;
	$arrConfiguracion["escribirAsignacion"]=true;
	$arrConfiguracion["idFormulario"]=$idFormulario;
	$arrConfiguracion["idRegistro"]=$idRegistro;
	$arrConfiguracion["funcValidacionPagoDeuda"]="esJuezAsignableLeyMujeresLV(@idUnidad,'".date("Y-m-d",$tmeFechaRegistro)."','".date("H:i:s",$tmeFechaRegistro)."','".date("H:i:s",$tmeFechaRegistro)."');";
	$arrConfiguracion["funcValidacionSeleccion"]="esJuezAsignableLeyMujeresLV(@idUnidad,'".date("Y-m-d",$tmeFechaRegistro)."','".date("H:i:s",$tmeFechaRegistro)."','".date("H:i:s",$tmeFechaRegistro)."');";
	
	$resultado= obtenerSiguienteAsignacionObjeto($arrConfiguracion,true);
	
	$consulta="SELECT idReferencia,clave FROM _26_tablaDinamica j,_26_tipoJuez tJ WHERE usuarioJuez=".$resultado["idUnidad"]." AND juezPrestado=0
				AND tJ.idPadre=j.id__26_tablaDinamica AND tJ.idOpcion=1";
	$fDatosJuez=$con->obtenerPrimeraFila($consulta);

	$idUnidadGestion=$fDatosJuez[0];

	$query="SELECT claveFolioCarpetas,claveUnidad FROM _17_tablaDinamica WHERE id__17_tablaDinamica=".$idUnidadGestion;
	$fRegistroUnidad=$con->obtenerPrimeraFila($query);
	$cvAdscripcion=$fRegistroUnidad[1];
	
	$query="SELECT carpetaAdministrativa FROM 7006_carpetasAdministrativas WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro;
	$carpetaAdministrativa=$con->obtenerValor($query);
	$agregarCarpeta=true;
	
	if($carpetaAdministrativa=="")
	{
		$carpetaAdministrativa=obtenerSiguienteCarpetaAdministrativa($idUnidadGestion,$anio,1,$idFormulario,$idRegistro);
	}
	else
		$agregarCarpeta=false;
	$consulta=array();
	$x=0;
	$consulta[$x]="begin";
	$x++;
	if($agregarCarpeta)
	{
		$consulta[$x]="INSERT INTO 7006_carpetasAdministrativas(carpetaAdministrativa,fechaCreacion,responsableCreacion,idFormulario,idRegistro,
						unidadGestion,etapaProcesalActual,idActividad,tipoCarpetaAdministrativa,carpetaInvestigacion,llaveCarpetaInvestigacion,
						unidadGestionOriginal,carpetaAdministrativaBase,idJuezTitular,permiteAudienciaVirtual) 
						VALUES('".$carpetaAdministrativa."','".date("Y-m-d H:i:s")."',".$_SESSION["idUsr"].",".$idFormulario.",'".$idRegistro."','".
						$cvAdscripcion."',1,".$idActividad.",1,(SELECT UPPER('".$carpetaInvestigacion."')),'".
						cv(generarLlaveCarpetaInvestigacion($carpetaInvestigacion))."','".$cvAdscripcion."','',".$resultado["idUnidad"].",1)";
		$x++;
	}
	$consulta[$x]="update _".$idFormulario."_tablaDinamica set carpetaAdministrativa='".$carpetaAdministrativa.
				"',codigoInstitucion='".$cvAdscripcion."',idJuez='".$resultado["idUnidad"]."',unidadGestion='".$cvAdscripcion.
				"' where id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$x++;
	
	$consulta[$x]="commit";
	$x++;
	
	if($con->ejecutarBloque($consulta))
	{
		$query="SELECT documento FROM _622_gDocumentosComplementarios WHERE idReferencia=".$idRegistro;
		$rDocumentos=$con->obtenerFilas($query);
		while($fila=mysql_fetch_row($rDocumentos))
		{
			$query="SELECT nomArchivoOriginal FROM 908_archivos WHERE idArchivo=".$fila[0];
			$nombreFinal=$con->obtenerValor($query);
			convertirDocumentoUsuarioDocumentoResultadoProceso($fila[0],$idFormulario,$idRegistro,$nombreFinal,6);
		}
		return true;
		
	}
	
	return false;
	
}


function obtenerListaIntercalada($arrJueces)
{
	$lista="";
	$arrJuecesOrden=array();
	$arrContadores=array();
	$arrGlobal=array();
	
	$maxTotal=0;
	foreach($arrJueces as $u=>$resto)
	{
		$total=0;
		$arrContadores[$total]=0;
		$arrAuxiliar=array();
		foreach($resto as $juez)
		{
			array_push($arrAuxiliar,$juez);
			$total++;
		}
		$total--;
		if($maxTotal<$total)
			$maxTotal=$total;
		array_push($arrGlobal,$arrAuxiliar);
		
	}
	
	
	for($x=0;$x<=$maxTotal;$x++)
	{
		for($e=0;$e<count($arrGlobal);$e++)
		{
			if(isset($arrGlobal[$e][$x]))
				array_push($arrJuecesOrden,$arrGlobal[$e][$x]);
		}
	}
	
	
	
	$lista=implode(",",$arrJuecesOrden);
	return $lista;
}


function mostrarSeccionEdicionDocumentoLMVLV($idFormulario,$idRegistro,$actor)
{
	global $con;
	
	
	$documentoBloqueado=0;
	$consulta="SELECT idRegistro FROM 7035_informacionDocumentos WHERE idFormulario=".$idFormulario.
				" AND idReferencia=".$idRegistro;
	
	$iRegistro=$con->obtenerValor($consulta);	
	if($iRegistro!="")
	{
		$consulta="SELECT documentoBloqueado FROM 3000_formatosRegistrados WHERE idFormulario=-2 AND idRegistro=".$iRegistro;
		
		$documentoBloqueado=$con->obtenerValor($consulta);	
	}
	if($documentoBloqueado==1)
		return 0;
		
	$arrActores=array();
	$arrActores[1155]=1;
	$arrActores[1168]=1;
	$arrActores[1169]=1;
	$arrActores[1170]=1;
	$arrActores[1171]=1;
	$arrActores[1172]=1;
	$arrActores[1173]=1;
	$arrActores[1175]=1;
	$arrActores[1176]=1;
	$arrActores[1177]=1;
	$arrActores[1178]=1;
	$arrActores[1179]=1;
	
	if(isset($arrActores[$actor]))
		return 1;
	return 0;
	
	
}

function SYS_obtenerJuezTitularCarpeta($idFormulario,$idRegistro,$actorDestinatario)
{
	global $con;
	$carpetaAdministrativa=obtenerCarpetaAdministrativaProceso($idFormulario,$idRegistro);
	
	$consulta="SELECT idJuezTitular FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$carpetaAdministrativa."'";
	$idJuezTitular=$con->obtenerValor($consulta);
	$rolActor=obtenerTituloRol($actorDestinatario);


	$arrDestinatario=array();
	
	$nombreUsuario=obtenerNombreUsuario($idJuezTitular)." (".$rolActor.")";
	$o='{"idUsuarioDestinatario":"'.$idJuezTitular.'","nombreUsuarioDestinatario":"'.$nombreUsuario.'"}';
	$o=json_decode($o);
	array_push($arrDestinatario,$o);
	
	return $arrDestinatario;
}

function notificarAcuerdoSolicitudLeyViolenciaMujeres($idFormulario,$idRegistro)
{
	global $con;
	
	$consulta="SELECT * FROM _622_tablaDinamica WHERE id__622_tablaDinamica=".$idRegistro;
	$fRegistroBase=$con->obtenerPrimeraFilaAsoc($consulta);
	if($fRegistroBase["respNotificada"]==1)
		return true;
	$documentoBloqueado=0;
	$consulta="SELECT idRegistro FROM 7035_informacionDocumentos WHERE idFormulario=".$idFormulario.
				" AND idReferencia=".$idRegistro;
	
	$iRegistro=$con->obtenerValor($consulta);	
	if($iRegistro!="")
	{
		$consulta="SELECT * FROM 3000_formatosRegistrados WHERE idFormulario=-2 AND idRegistro=".$iRegistro;		
		$fDatosDocumento=$con->obtenerPrimeraFilaAsoc($consulta);
		
		$consulta="SELECT nomArchivoOriginal FROM 908_archivos WHERE idArchivo=".$fDatosDocumento["idDocumento"];
		$nombreArchivo=$con->obtenerValor($consulta);
		
		$objRespuesta='{"idRegistro":"'.$fRegistroBase["idRegistroPromocion"].'","idDocumento":"'.$fDatosDocumento["idDocumento"].
						'","nombreDocumento":"'.cv($nombreArchivo).'","cuerpoDocumento":"'.
						obtenerCuerpoDocumentoB64($fDatosDocumento["idDocumento"]).
						'","idRegistroRecepcion":"'.$idRegistro.'"}';
		$datosServidor=obtenerURLComunicacionServidorMateria("SW");
		$urlWebServices="http://".$datosServidor[0].":".$datosServidor[1]."/webServices/wsInterconexionSistemasBPM.php?wsdl";
		
		$client = new nusoap_client($urlWebServices,"wsdl");
		
		$parametros=array();
		$parametros["cadObj"]=bE($objRespuesta);
		$response = $client->call("registrarRespuestaLAVLV", $parametros);

		$oResp=json_decode($response);	

		if($oResp->resultado==1)
		{
			
			$consulta="UPDATE _622_tablaDinamica SET respNotificada=1 WHERE id__622_tablaDinamica=".$idRegistro;
			
			return $con->ejecutarConsulta($consulta);	
		}
		else
		{
			return false;
		}
	}
	else
		return true;
		
	
	
	
}



function obtenerSalasAudiencia($idUnidadGestion,$idEdificio,$tipoAudiencia,$carpetaAdministrativa,$fechaAudiencia)
{
	global $con;
	
	$fechaBase=strtotime("2020-08-03");
	
	
	
	$consulta="SELECT COUNT(*) FROM _55_tablaDinamica se,_15_tablaDinamica s WHERE se.idReferencia=".$idUnidadGestion." 
				AND salasVinculadas=s.id__15_tablaDinamica AND s.id__15_tablaDinamica not in(152) and s.idReferencia=".$idEdificio."
				and perfilSala in(1,2)";

	$nSalas=$con->obtenerValor($consulta);
	if($nSalas>0)
	{
		$consulta="SELECT distinct id__15_tablaDinamica,CONCAT('[',if(s.claveSala is null,'',s.claveSala),'] ',nombreSala) as nombreSala,perfilSala  FROM _55_tablaDinamica t,
			_15_tablaDinamica s WHERE (t.idReferencia=".$idUnidadGestion." AND s.id__15_tablaDinamica=t.salasVinculadas AND 
			s.idReferencia=".$idEdificio." and perfilSala in(1,2)) or (id__15_tablaDinamica in (152,154))";
	
	}
	else
	{
		$consulta="SELECT distinct id__15_tablaDinamica,CONCAT('[',if(s.claveSala is null,'',s.claveSala),'] ',nombreSala)  as nombreSala,perfilSala FROM 
			_15_tablaDinamica s WHERE (s.idReferencia=".$idEdificio." and perfilSala in(1,2)) or (id__15_tablaDinamica in (152,154))";
	
	
	}
	
	$agregarClausulaOrder=true;
	
	if(($tipoAudiencia!=-1)&&($carpetaAdministrativa!=""))
	{
		$query="SELECT permiteAudienciaVirtual FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$carpetaAdministrativa."'";
		$permiteAudienciaVirtual=$con->obtenerValor($query);
		if($permiteAudienciaVirtual==1)
		{
			$query="SELECT permiteAgendarAudienciaVirtual FROM _4_tablaDinamica WHERE id__4_tablaDinamica=".$tipoAudiencia;
			$permiteAgendarAudienciaVirtual=$con->obtenerValor($query);
			if($permiteAgendarAudienciaVirtual==1)
			{
				$query="SELECT idReferencia FROM _17_tablaDinamica WHERE id__17_tablaDinamica=".$idUnidadGestion;
				$iEdificioUnidad=$con->obtenerValor($query);
				if($iEdificioUnidad==$idEdificio)
				{
					$consulta.=" union
								(SELECT id__15_tablaDinamica,CONCAT('[',if(s.claveSala is null,'',s.claveSala),'] ',nombreSala) as nombreSala,perfilSala  FROM 
								_15_tablaDinamica s WHERE s.idReferencia=".$iEdificioUnidad." and perfilSala in(3,4))";
					$consulta="select * from(".$consulta.") as tmp order by perfilSala,nombreSala";
					$agregarClausulaOrder=false;
				}
				
			}
			
		}
	}
	
	if($agregarClausulaOrder)
		$consulta.=" ORDER BY s.nombreSala";

	$arrSalas=$con->obtenerFilasArreglo($consulta);
	
	return $arrSalas;
}


function seleccionRutaPromocionEjecucion($idFormulario,$idRegistro)
{
	global $con;
	
	$consulta="SELECT carpetaAdministrativa,fechaRecepcion,horaRecepcion FROM _96_tablaDinamica WHERE id__96_tablaDinamica=".$idRegistro;
	$fPromocion=$con->obtenerPrimeraFila($consulta);
	$carpetaAdministrativa=$fPromocion[0];
	$consulta="SELECT tipoCarpetaAdministrativa,idJuezTitular FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$carpetaAdministrativa."'";
	$fCarpeta=$con->obtenerPrimeraFila($consulta);
	
	$idEtapa=2;
	if(($fCarpeta[0]==6)&&($fCarpeta[1]!=""))
	{
		$consulta="SELECT auxiliarAsignado FROM _631_tablaDinamica WHERE juezEjecucion=".$fCarpeta[1];
		$auxiliarAsignado=$con->obtenerValor($consulta);
		if($auxiliarAsignado!="")
		{
			$idEtapa=1.7;
		}
	}
	$actualizar="UPDATE _96_tablaDinamica SET fechaHoraRecepcionPromocion='".$fPromocion[1]." ".$fPromocion[2].
				"' WHERE id__96_tablaDinamica='".$idRegistro."'";

	$con->ejecutarConsulta($actualizar);
	cambiarEtapaFormulario($idFormulario,$idRegistro,$idEtapa,"",-1,"NULL","NULL",613);
}


function obtenerAuxiliarJuezEjecucion($idFormulario,$idRegistro,$actorDestinatario)
{
	global $con;
	
	
	
	$consulta="SELECT carpetaAdministrativa FROM _96_tablaDinamica WHERE id__96_tablaDinamica=".$idRegistro;
	$carpetaAdministrativa=$con->obtenerValor($consulta);
	
	$consulta="SELECT tipoCarpetaAdministrativa,idJuezTitular FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$carpetaAdministrativa."'";
	$fCarpeta=$con->obtenerPrimeraFila($consulta);
	
	$rolActor=obtenerTituloRol($actorDestinatario);
	
	$arrDestinatario=array();
	$consulta="SELECT auxiliarAsignado FROM _631_tablaDinamica WHERE juezEjecucion=".$fCarpeta[1];
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		
		$nombreUsuario=obtenerNombreUsuario($fila[0])." (".$rolActor.")";
		$o='{"idUsuarioDestinatario":"'.$fila[0].'","nombreUsuarioDestinatario":"'.$nombreUsuario.'"}';
		$o=json_decode($o);
		array_push($arrDestinatario,$o);
	}
	$nombreUsuario=obtenerNombreUsuario(1)." (".$rolActor.")";
	$o='{"idUsuarioDestinatario":"1","nombreUsuarioDestinatario":"'.$nombreUsuario.'"}';
	$o=json_decode($o);
	array_push($arrDestinatario,$o);
	return $arrDestinatario;
}


function enviarCorreoWebServicesSolicitudLAVLV($idRegistro)
{
	global $baseDir;
	global $con;
	global $urlRepositorioDocumentos;
	global $servidorPruebas;
	
	if($servidorPruebas)
	{
		return true;
	}
	$arrArchivos=array();
	$arrCopia=array();
	$arrCopiaOculta=array();
	$idFormulario=622;
	
	$consulta="SELECT carpetaAdministrativa FROM _622_tablaDinamica WHERE id__622_tablaDinamica=".$idRegistro;

	$carpetaAdministrativa=$con->obtenerValor($consulta);
	
	$consulta="SELECT unidadGestion FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$carpetaAdministrativa."'";
	
	$unidadGestion=$con->obtenerValor($consulta);

	$consulta="SELECT id__17_tablaDinamica FROM _17_tablaDinamica WHERE claveUnidad='".$unidadGestion."'";
	$idCentroGestion=$con->obtenerValor($consulta);
	$nCopias=0;	
	$nDestinatario=0;
	$consulta="SELECT mail FROM _362_tablaDinamica f,_362_chkTipoNotificacion tn WHERE  
				f.idReferencia=".$idCentroGestion." AND tn.idPadre=f.id__362_tablaDinamica AND tn.idOpcion=1";
	
	$rMail=$con->obtenerFilas($consulta);
	while($fMail=mysql_fetch_row($rMail))
	{
		$arrCopiaOculta[$nCopias][0]=$fMail[0];
		$arrCopiaOculta[$nCopias][1]="";
		$nCopias++;
	}

	$nPos=0;
	$titulo="Nueva solicitud registrada LAMVLVCDMX, Carpeta Judicial: ".$carpetaAdministrativa;
	$cuerpo="Ha recibido una nueva solicitud para su atenci&oacute;n: Ley de Acceso a Mujeres para una Vida Libre de Violencia";
	
	
	/*$nDocumentoSolicitud=1;
	$consulta="SELECT idDocumento FROM 9074_documentosRegistrosProceso WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro;
	$rDocumentos=$con->obtenerFilas($consulta);
	while($fDocumento=mysql_fetch_row($rDocumentos))
	{
		$consulta="SELECT nomArchivoOriginal FROM 908_archivos WHERE idArchivo=".$fDocumento[0];
		$nomArchivoOriginal=$con->obtenerValor($consulta);
		$aNombreArchivo=explode(".",$nomArchivoOriginal);
		$extension=$aNombreArchivo[sizeof($aNombreArchivo)-1];
		$directorioDestino=obtenerRutaDocumento($fDocumento[0]);
		$arrArchivos[$nPos][0]=$directorioDestino;
		$arrArchivos[$nPos][1]="Solicitud LAMVLVCDMX_".$nDocumentoSolicitud.".".$extension;
		$nPos++;
		$nDocumentoSolicitud++;
		
	}*/
	

	if(enviarMailGMail("medidas.proteccion@tsjcdmx.gob.mx",$titulo,$cuerpo,$arrArchivos,$arrCopia,$arrCopiaOculta))
	{
		
		$consulta="UPDATE _622_tablaDinamica SET notificacionCorreo=1 WHERE id__622_tablaDinamica=".$idRegistro;
		$con->ejecutarConsulta($consulta);
	}
	
}

function esJuezAsignableLeyMujeresLV($idJuez,$fecha,$horaInicio,$horaFin)
{
	global $con;
	$arrResultado=array();
	if(!esJuezDisponibleIncidenciaV2($idJuez,$fecha,$horaInicio,$horaFin))
	{

		$arrResultado[0]=false;
		$arrResultado[1]='El Juez NO cuenta con disponibilidad, periodo: '.$fecha.' '.$horaInicio.' - '.$horaFin;
		return $arrResultado;
	}
	
	$consulta="SELECT count(*) FROM 807_usuariosVSRoles WHERE idRol=218 and idUsuario=".$idJuez;
	$numReg=$con->obtenerValor($consulta);
	
	if($numReg>0)
	{

		$arrResultado[0]=false;
		$arrResultado[1]='El Juez ha sido marcado como de Tramite';
		return $arrResultado;
	}
	
	return true;
}

function esUsuarioPuedeCambiarJuezTitular()
{
	global $con;
	return usuarioTieneRolPermitidoProceso(248);
}


function registrarJuezActualCarpeta($idFormulario,$idRegistro)
{
	global $con;
	$consulta="SELECT carpetaAdministrativa FROM _620_tablaDinamica WHERE id__620_tablaDinamica=".$idRegistro;
	$carpetaAdministrativa=$con->obtenerValor($consulta);
	$consulta="SELECT idJuezTitular FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$carpetaAdministrativa."'";
	$idJuezTitular=$con->obtenerValor($consulta);
	if($idJuezTitular=="")
		$idJuezTitular=-1;
	$consulta="UPDATE _620_tablaDinamica SET idJuezAnterior=".$idJuezTitular." WHERE id__620_tablaDinamica=".$idRegistro;
	return $con->ejecutarConsulta($consulta);
}




?>