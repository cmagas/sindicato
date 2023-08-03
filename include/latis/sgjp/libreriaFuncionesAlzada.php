<?php 
include_once("latis/conexionBD.php");
include_once("latis/utiles.php");
include_once("latis/numeroToLetra.php");
include_once("latis/sgjp/funcionesAgenda.php");


function generarFolioCarpetaTocaAlzada($idFormulario,$idRegistro)
{
	global $con;
	
	$anio=date("Y");
	$query="SELECT tribunalAlzada,carpetaAdministrativa,carpetaJudicialApelada,carpetaJudicialApelacion 
			FROM _487_tablaDinamica WHERE id__487_tablaDinamica=".$idRegistro;
	$fRegistro=$con->obtenerPrimeraFila($query);
	
	if($fRegistro[1]!="")
	{
		cambiarEtapaFormulario(487,$idRegistro,2,"",-1,"NULL","NULL",872);
		return true;
	}
	$query="SELECT idActividad,carpetaInvestigacion,etapaProcesalActual FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$fRegistro[3]."'";
	$fCarpetaApelacion=$con->obtenerPrimeraFila($query);
	$idActividad=$fCarpetaApelacion[0];
	$etapaProcesalActual=$fCarpetaApelacion[2];
	
	$carpetaInvestigacion=$fCarpetaApelacion[1];
	$query="SELECT claveUnidad,id__17_tablaDinamica FROM _17_tablaDinamica WHERE claveUnidad='".$fRegistro[0]."'";
	$fRegistroUnidad=$con->obtenerPrimeraFila($query);
	$cvAdscripcion=$fRegistroUnidad[0];
	$idUnidadGestion=$fRegistroUnidad[1];
	
	$carpetaAdministrativa=obtenerSiguienteCarpetaAdministrativa($idUnidadGestion,$anio,8,$idFormulario,$idRegistro);	
	
		
	$x=0;
	$consulta[$x]="begin";
	$x++;
	$consulta[$x]="INSERT INTO 7006_carpetasAdministrativas(carpetaAdministrativa,fechaCreacion,responsableCreacion,idFormulario,
					idRegistro,unidadGestion,etapaProcesalActual,idActividad,tipoCarpetaAdministrativa,carpetaInvestigacion,
					llaveCarpetaInvestigacion,idJuezTitular,unidadGestionOriginal,carpetaAdministrativaBase) 
					VALUES('".$carpetaAdministrativa."','".date("Y-m-d H:i:s")."',".$_SESSION["idUsr"].",".$idFormulario.",'".$idRegistro.
					"','".$cvAdscripcion."',".$etapaProcesalActual.",".$idActividad.",8,(SELECT UPPER('".$carpetaInvestigacion."')),'".
					cv(generarLlaveCarpetaInvestigacion($carpetaInvestigacion))."',-1,'".$cvAdscripcion."','".$fRegistro[3]."')";
	$x++;
	
	$consulta[$x]="set @idCarpeta:=(select  last_insert_id())";
	$x++;
	
	$consulta[$x]="UPDATE _487_tablaDinamica SET idCarpeta=@idCarpeta,carpetaAdministrativa='".$carpetaAdministrativa."' WHERE id__487_tablaDinamica=".$idRegistro;
	$x++;
	
	$consulta[$x]="commit";
	$x++;

	if($con->ejecutarBloque($consulta))
	{
		$query="select @idCarpeta";
		$idCarpeta=$con->obtenerValor($query);
		$query="SELECT idDocumento FROM 9074_documentosRegistrosProceso WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro;
		$rDocumentos=$con->obtenerFilas($query);
		while($fDocumento=mysql_fetch_row($rDocumentos))
		{
			registrarDocumentoCarpetaAdministrativa($carpetaAdministrativa,$fDocumento[0],$idFormulario,$idRegistro,$idCarpeta);	
		}

		
		cambiarEtapaFormulario(487,$idRegistro,2,"",-1,"NULL","NULL",872);
	}
	
	return false;
	
}

function generarTocaAlzadaManual($idFormulario,$idRegistro)
{
	global $con;
	
	$anio=date("Y");
	$query="SELECT tribunalAlzada,carpetaAdministrativa,carpetaJudicialApelada,carpetaJudicialApelacion, iFormulario,iRegistro
			FROM _487_tablaDinamica WHERE id__487_tablaDinamica=".$idRegistro;
	$fRegistro=$con->obtenerPrimeraFila($query);
	
	$consulta="SELECT COUNT(*) FROM 7006_carpetasAdministrativas WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro;
	$nReg=$con->obtenerValor($consulta);
	if($nReg>0)
		return true;
	
	$query="SELECT idActividad,carpetaInvestigacion FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$fRegistro[3]."'";
	$fCarpetaApelacion=$con->obtenerPrimeraFila($query);
	$idActividad=$fCarpetaApelacion[0];
	
	$carpetaInvestigacion=$fCarpetaApelacion[1];
	$query="SELECT claveUnidad,id__17_tablaDinamica FROM _17_tablaDinamica WHERE claveUnidad='".$fRegistro[0]."'";
	$fRegistroUnidad=$con->obtenerPrimeraFila($query);
	$cvAdscripcion=$fRegistroUnidad[0];
	$idUnidadGestion=$fRegistroUnidad[1];
	
	$consulta="SELECT noToca FROM _614_tablaDinamica WHERE idReferencia=".$idRegistro;
	$carpetaAdministrativa=$con->obtenerValor($consulta);
	
	$consulta=array();
	$x=0;
	$consulta[$x]="begin";
	$x++;
	$consulta[$x]="INSERT INTO 7006_carpetasAdministrativas(carpetaAdministrativa,fechaCreacion,responsableCreacion,idFormulario,
					idRegistro,unidadGestion,etapaProcesalActual,idActividad,tipoCarpetaAdministrativa,carpetaInvestigacion,
					llaveCarpetaInvestigacion,idJuezTitular,unidadGestionOriginal,carpetaAdministrativaBase) 
					VALUES('".$carpetaAdministrativa."','".date("Y-m-d H:i:s")."',".$_SESSION["idUsr"].",".$idFormulario.",'".$idRegistro.
					"','".$cvAdscripcion."',0,".$idActividad.",8,(SELECT UPPER('".$carpetaInvestigacion."')),'".
					cv(generarLlaveCarpetaInvestigacion($carpetaInvestigacion))."',-1,'".$cvAdscripcion."','".$fRegistro[3]."')";
	$x++;
	
	$consulta[$x]="set @idCarpeta:=(select  last_insert_id())";
	$x++;
	
	$consulta[$x]="UPDATE _487_tablaDinamica SET idCarpeta=@idCarpeta,carpetaAdministrativa='".$carpetaAdministrativa."' WHERE id__487_tablaDinamica=".$idRegistro;
	$x++;
	
	$consulta[$x]="UPDATE _451_tablaDinamica SET noToca='".$carpetaAdministrativa."' WHERE id__451_tablaDinamica=".$fRegistro[5];
	$x++;
	$consulta[$x]="commit";
	$x++;

	if($con->ejecutarBloque($consulta))
	{
		$query="select @idCarpeta";
		$idCarpeta=$con->obtenerValor($query);
		$query="SELECT idDocumento FROM 9074_documentosRegistrosProceso WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro;
		$rDocumentos=$con->obtenerFilas($query);
		while($fDocumento=mysql_fetch_row($rDocumentos))
		{
			registrarDocumentoCarpetaAdministrativa($carpetaAdministrativa,$fDocumento[0],$idFormulario,$idRegistro,$idCarpeta);	
		}
		
		cambiarEtapaFormulario(451,$fRegistro[5],2.1,"",-1,"NULL","NULL",909);
		
		
		
	}

	return false;
	
}

function asignarCodigoInstitucionRegistro($idFormulario,$idRegistro)
{
	global $con;
	$consulta="SELECT tribunalAlzada FROM _487_tablaDinamica WHERE id__487_tablaDinamica=".$idRegistro;
	$fRegistro=$con->obtenerPrimeraFila($consulta);
	
	$consulta="UPDATE _487_tablaDinamica SET codigoInstitucion='".$fRegistro[0]."'  WHERE id__487_tablaDinamica=".$idRegistro;
	return $con->ejecutarConsulta($consulta);
	
}

function registrarAudienciaToca($idFormulario,$idRegistro,$idEventoAudiencia,$toca,$tribunalAlzada)
{
	global $con;
		
	$carpetaAdministrativa=$toca;
	
	$consulta="select count(*) from 7007_contenidosCarpetaAdministrativa where carpetaAdministrativa='".$carpetaAdministrativa.
			"' and idRegistroContenidoReferencia=".$idEventoAudiencia." and tipoContenido=3";
	$nReg=$con->obtenerValor($consulta);
	if($nReg>0)
		return true;
	
	$consulta="SELECT claveUnidad FROM _17_tablaDinamica WHERE id__17_tablaDinamica=".$tribunalAlzada;
	$unidadGestion=$con->obtenerValor($consulta);
	
	$consulta="SELECT idCarpeta FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$toca."' AND unidadGestion='".$unidadGestion."'";
	$idCarpetaAdministrativa=$con->obtenerValor($consulta);
	
	$consulta="INSERT INTO 7007_contenidosCarpetaAdministrativa(carpetaAdministrativa,fechaRegistro,responsableRegistro,tipoContenido,descripcionContenido,
				idRegistroContenidoReferencia,idFormulario,idRegistro,etapaProcesal,idCarpetaAdministrativa)
				VALUES('".$carpetaAdministrativa."','".date("Y-m-d H:i:s")."',".$_SESSION["idUsr"].",3,'',".$idEventoAudiencia.",".$idFormulario.
				",".$idRegistro.",0,".$idCarpetaAdministrativa.")";
	return $con->ejecutarConsulta($consulta);
}

function registrarApelantes($idRegistro,$objRegistro)
{
	global $con;
	$objRegistro=bD($objRegistro);
	
	$oRegistro=json_decode($objRegistro);
	
	$x=0;
	$query[$x]="begin";
	$x++;
	$query[$x]="DELETE FROM _487_apelantes WHERE idReferencia=".$idRegistro;
	$x++;	
	
	foreach($oRegistro->arrApelantes as $a)
	{
		$query[$x]="INSERT INTO _487_apelantes(idReferencia,idApelante,figuraJuridica)
					values(".$idRegistro.",".$a->idApelante.",".$a->figuraJuridica.")";
		$x++;
	}
	$query[$x]="commit";
	$x++;
	
	
	return $con->ejecutarBloque($query);
	
}

function obtenerSiguienteSalaAsignacion($idFormulario,$idRegistro)
{
	global $con;
	
	$consulta="SELECT u.tipoMateria FROM 7006_carpetasAdministrativas c,_451_tablaDinamica a,_17_tablaDinamica u 
				WHERE a.id__451_tablaDinamica=".$idRegistro." AND c.carpetaAdministrativa=a.carpetaAdministrativa  
				AND claveUnidad=c.unidadGestion";
	$tMateria=$con->obtenerValor($consulta);			
				
	$consulta="SELECT idUnidadReferida,noRonda,idAsignacionPagada FROM 7001_asignacionesObjetos 
			WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro." AND situacion=1";
	$fAsignacion=$con->obtenerPrimeraFila($consulta);	

	if($fAsignacion)
	{
		$arrResultado["idUnidad"]=$fAsignacion[0];
		$arrResultado["noRonda"]=$fAsignacion[1];
		$arrResultado["idAsignacionPago"]=$fAsignacion[2];
		$arrResultado["pagoDeuda"]=$arrResultado["idAsignacionPago"]==-1?0:1;
		return $arrResultado;
		
	}
	
	
	
	$consulta="SELECT claveUnidad FROM _17_tablaDinamica WHERE cmbCategoria=3 and tipoMateria=".$tMateria." ORDER BY prioridad";
	$listaSalas=$con->obtenerListaValores($consulta);
	$arrConfiguracion["tipoAsignacion"]=1;
	$arrConfiguracion["serieRonda"]="TOCA_".$tMateria;
	$arrConfiguracion["universoAsignacion"]=$listaSalas;
	$arrConfiguracion["idObjetoReferencia"]=-1;
	$arrConfiguracion["pagarDeudasAsignacion"]=true;
	
	$arrConfiguracion["limitePagoRonda"]=0;
	$arrConfiguracion["escribirAsignacion"]=true;
	$arrConfiguracion["idFormulario"]=$idFormulario;
	$arrConfiguracion["idRegistro"]=$idRegistro;
	return obtenerSiguienteAsignacionObjeto($arrConfiguracion);
}


function actualizarSalaPenalNoToca($idFormulario,$idRegistro)
{
	global $con;
	/*$consulta="SELECT idReferencia,salaPenal,numeroToca FROM _452_tablaDinamica WHERE id__452_tablaDinamica=".$idRegistro;
	$fRegistro=$con->obtenerPrimeraFila($consulta);
	$consulta="UPDATE _451_tablaDinamica SET salaPenal='".$fRegistro[1]."', noToca='".$fRegistro[2]."' WHERE id__451_tablaDinamica=".$fRegistro[0];
	return $con->ejcutarConsulta($consulta);*/
	return true;
	
}


function seleccionarTribunalAlzadaApelacion($idFormulario,$idRegistro)
{
	global $con;
	global $servidorPruebas;
	
	
	$consulta="SELECT * FROM _451_tablaDinamica WHERE id__451_tablaDinamica=".$idRegistro;
	$fRegistro=$con->obtenerPrimeraFilaAsoc($consulta);
	$cveTribunal=$fRegistro["salaPenalSugerida"];
	if(($cveTribunal=="N/E")||($cveTribunal==-1))
		$cveTribunal="";
	
	
	if($fRegistro["existeAntecedenteSala"]==1)
	{
		$cveTribunal=$fRegistro["salaAntecedente"];
	}
	
	if($cveTribunal=="")
	{
		$arrResultado=obtenerSiguienteSalaAsignacion($idFormulario,$idRegistro);
		$cveTribunal=$arrResultado["idUnidad"];
		
		
	}
	
	$consulta="UPDATE _451_tablaDinamica SET salaPenalSugerida='".$cveTribunal."' WHERE id__451_tablaDinamica=".$idRegistro;
	
	return $con->ejecutarConsulta($consulta);
}

function permiteGenerarOficioRespuestaAlzada($idFormulario,$idRegistro,$actor,$idFormularioEvaluacion)
{
	global $con;
	
	$consulta="SELECT idRegistro FROM 7035_informacionDocumentos WHERE idFormulario=".$idFormulario." AND idReferencia=".$idRegistro;
	$idDocumentos=$con->obtenerListaValores($consulta);
	if($idDocumentos=="")
	{
		$idDocumentos=-1;
	}
	
	$consulta="SELECT * FROM 3000_formatosRegistrados WHERE idFormulario=-2 AND idRegistro in(".$idDocumentos.") AND idFormularioProceso=".$idFormularioEvaluacion;

	$fRegistroDocumento=$con->obtenerPrimeraFilaAsoc($consulta);
	if($fRegistroDocumento &&($fRegistroDocumento["documentoBloqueado"]==1))
		return 0;	
	$consulta="SELECT actor FROM 944_actoresProcesoEtapa WHERE idActorProcesoEtapa=".$actor;
	$rol=$con->obtenerValor($consulta);
	
	switch($rol)
	{
		case "206_0"://JUD de Control de Gestión - B
			$consulta="SELECT  COUNT(*) FROM _617_tablaDinamica WHERE idReferencia=".$idRegistro;//Lleno JUD
			$numReg1=$con->obtenerValor($consulta);
			
			$consulta="SELECT  COUNT(*) FROM _618_tablaDinamica WHERE idReferencia=".$idRegistro;//Lleno Aux
			$numReg2=$con->obtenerValor($consulta);

			if(($numReg1+$numReg2)>0)
				return 1;
		break;
		case "209_0"://Aux. JUD de Control de Gestión - B
			$consulta="SELECT  COUNT(*) FROM _617_tablaDinamica WHERE idReferencia=".$idRegistro;//Lleno JUD
			$numReg1=$con->obtenerValor($consulta);
			
			$consulta="SELECT  COUNT(*) FROM _618_tablaDinamica WHERE idReferencia=".$idRegistro;//Lleno Aux
			$numReg2=$con->obtenerValor($consulta);

			if(($numReg1+$numReg2)>0)
				return 1;
		break;
		case "70_0": //Subdirector de control de gestión
			$consulta="SELECT  COUNT(*) FROM _617_tablaDinamica WHERE idReferencia=".$idRegistro;//Lleno JUD
			$numReg1=$con->obtenerValor($consulta);
			
			$consulta="SELECT  COUNT(*) FROM _618_tablaDinamica WHERE idReferencia=".$idRegistro;//Lleno Aux
			$numReg2=$con->obtenerValor($consulta);

			
			$consulta="SELECT  COUNT(*) FROM _581_tablaDinamica WHERE idReferencia=".$idRegistro; //Lleno SUB
			$numReg3=$con->obtenerValor($consulta);
			
			if(($numReg1+$numReg2+$numReg3)>0)
				return 1;
		
		break;
		case "199_0": //Director de unidad de gestión
			return 1;
		break;
		case "203_0": //Director Ejecutivo
			return 1;
		break;
	}
	
	return 0;
	
}

function generarOficioRespuestaAlzada($idFormulario,$idRegistro,$actor,$idFormularioEvaluacion)
{
	global $con;
	
	return 527;
	
	/*$consulta="SELECT actor FROM 944_actoresProcesoEtapa WHERE idActorProcesoEtapa=".$actor;
	$rol=$con->obtenerValor($consulta);
	
	switch($rol)
	{
		case "70_0": //Subdirector de control de gestión
			$consulta="SELECT  * FROM _581_tablaDinamica WHERE idReferencia=".$idRegistro;
			$fRegistroResultado=$con->obtenerPrimeraFilaAsoc($consulta);
			if($fRegistroResultado["antecedenteSala"]==1)
				return 527;
			return 528;
		break;
		case "199_0": //Director de unidad de gestión
		break;
		case "203_0": //Director Ejecutivo
		break;
	}
	
	return 0;*/
	
	
}

function funcionLlenadoOficioRespuestaAlzada($idFormularioBase,$idRegistroBase,$tipoDocumento,$actor,$iFormularioProceso)
{
	global $con;	
	global $leyendaTribunal;
	
	$existeAntecedente=false;
	
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
	
	
	$consulta="SELECT actor FROM 944_actoresProcesoEtapa WHERE idActorProcesoEtapa=".$actor;
	$rol=$con->obtenerValor($consulta);
	switch($rol)
	{
		case "209_0"://Aux. JUD de Control de Gestión - B
			if($fRegistroResultadoAuxJUDControl)
				$fResultado=$fRegistroResultadoAuxJUDControl;
			else
				$fResultado=$fRegistroResultadoJUDControl;
		break;
		case "206_0"://JUD de Control de Gestión - B
			if($fRegistroResultadoJUDControl)
				$fResultado=$fRegistroResultadoJUDControl;
			else
				$fResultado=$fRegistroResultadoAuxJUDControl;
		break;
		
		case "70_0": //Subdirector de control de gestión
			if($fRegistroResultadoSubControl)
				$fResultado=$fRegistroResultadoSubControl;
			else
			{
				if($fRegistroResultadoJUDControl)
					$fResultado=$fRegistroResultadoJUDControl;
				else
					$fResultado=$fRegistroResultadoAuxJUDControl;
			}
		break;
		case "199_0": //Director de unidad de gestión
			
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
				
			
			
		break;
		case "203_0": //Director Ejecutivo
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
		break;
	}
	
	$existeAntecedente=$fResultado["antecedenteSala"]==1;
	$salaPenal=$fResultado["salaPenal"];
	
	$arrValores["leyendaTribunal"]=$leyendaTribunal;
	$arrValores["fecha"]=convertirFechaLetra(date("Y-m-d"),false,false);
	
	$consulta="SELECT * FROM _451_tablaDinamica WHERE id__451_tablaDinamica=".$idRegistroBase;
	$fDatosBase=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$consulta="SELECT unidadGestion FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$fDatosBase["carpetaAdministrativa"]."'";
	$unidadGestion=$con->obtenerValor($consulta);
	
	$consulta="SELECT * FROM _17_tablaDinamica WHERE claveUnidad='".$unidadGestion."'";
	$fDatosUnidadGestion=$con->obtenerPrimeraFilaAsoc($consulta);
	
	
	$unidadGestion=$fDatosUnidadGestion["claveUnidad"]*1;
	if($unidadGestion==209)
		$unidadGestion=9;
	
	$arrValores["nombreDirector"]=$fDatosUnidadGestion["nombreDirector"];
	$arrValores["noUGA"]="DIRECTOR DE LA UNIDAD DE GESTIÓN<br>JUDICIAL NÚMERO ".convertirNumeroLetra($unidadGestion,false,false)." DEL SISTEMA";
	if($fDatosUnidadGestion["tipoMateria"]==2)
	{
		$arrValores["noUGA"]="DIRECTOR DE LA UNIDAD ESPECIALIZADA EN ADOLESCENTES DEL SISTEMA";
	}
	
	


	
	$folio="";
	$consulta="SELECT noOficioAsignado FROM _600_tablaDinamica WHERE iFormulario=".$idFormularioBase." AND iRegistro=".$idRegistroBase.
				" AND iFormularioProceso=".$iFormularioProceso." order by id__600_tablaDinamica  desc";

	$folio=$con->obtenerValor($consulta);
	
	if($folio=="")
	{
		$arrDocumentos=array();
		$arrValoresOficio=array();
		$arrValoresOficio["dirigidoA"]=$fDatosUnidadGestion["nombreDirector"];
		$arrValoresOficio["unidadDestinataria"]=$fDatosUnidadGestion["claveUnidad"];
		$arrValoresOficio["asunto"]="Se informa Sala Penal asignada";
		$arrValoresOficio["institucionDestino"]=999;
		$arrValoresOficio["iFormulario"]=$idFormularioBase;
		$arrValoresOficio["iRegistro"]=$idRegistroBase;
		$arrValoresOficio["iFormularioProceso"]=$iFormularioProceso;
		
		$idRegistroOficio=crearInstanciaRegistroFormulario(600,-1,61,$arrValoresOficio,$arrDocumentos,-1,264);
		$consulta="SELECT noOficioAsignado FROM _600_tablaDinamica WHERE id__600_tablaDinamica=".$idRegistroOficio;
		$folio=$con->obtenerValor($consulta);
	}
	$arrValores["folio"]=$folio;
	
	

	$consulta="SELECT * FROM _599_tablaDinamica WHERE idReferencia=".$fDatosUnidadGestion["id__17_tablaDinamica"];
	$fRegistro=$con->obtenerPrimeraFilaAsoc($consulta);
	$arrParametros=array();
	$arrParametros["anio"]=date("Y",strtotime($fDatosBase["fechaCreacion"]));
	$arrParametros["folio"]=str_pad($fDatosBase["noOficioDEGJ"],$fRegistro["longitudCampoFolio"],"0",STR_PAD_LEFT);
	
	$folioAsignar=$fRegistro["formatoFolioOficio"];
	foreach($arrParametros as $campo=>$valor)
	{
		$folioAsignar=str_replace('{'.$campo.'}',$valor,$folioAsignar);
	}
	
	$arrValores["folioOficio"]=$folioAsignar;
	$arrValores["carpetaJudicial"]=$fDatosBase["carpetaAdministrativa"];
	
	
	
	
	
	$consulta="SELECT * FROM _17_tablaDinamica WHERE claveUnidad='".$salaPenal."'";
	$fDatosUnidadGestion=$con->obtenerPrimeraFilaAsoc($consulta);
	$arrValores["salaPenal"]=$fDatosUnidadGestion["nombreUnidad"];
	
	$consulta="SELECT (SELECT UPPER(Nombre) FROM 800_usuarios WHERE idUsuario=p.usuarioAsignado) AS nombre,
			(SELECT nombrePuesto FROM _416_tablaDinamica WHERE id__416_tablaDinamica=p.puestoOrganozacional) AS nombrePuesto 
				FROM _421_tablaDinamica p WHERE puestoOrganozacional=67";
	$fSubdirectorControl=$con->obtenerPrimeraFila($consulta);
	
	$arrValores["nombreDirectorGJA"]=$fSubdirectorControl[0];
	
	$arrValores["puestoResponsable"]=!$fSubdirectorControl?"____________________":$fSubdirectorControl[1];
	
	if($existeAntecedente)
	{
		$arrValores["ultimoParrafo"]="y toda vez que en la carpeta judicial se advierte antecedente en la <b>".$arrValores["salaPenal"].
					"</b> le corresponde a esta última conocer del <b>Recurso de Apelación</b> de la carpeta judicial <b>".$arrValores["carpetaJudicial"]."</b>.";
	}
	else
	{
		$arrValores["ultimoParrafo"]=" le corresponde a la <b>".$arrValores["salaPenal"]."</b> conocer del <b>Recurso de Apelación</b> de la carpeta judicial <b>".$arrValores["carpetaJudicial"]."</b>.";
	}
	return $arrValores;
	
	
	
}


function registrarResolucionAlzadaUnidadQuejosa($idFormulario,$idRegistro)
{
	global $con;
	
	$consulta="SELECT * FROM _487_tablaDinamica WHERE iFormulario=".$idFormulario." AND iRegistro=".$idRegistro;
	$fRegistro=$con->obtenerPrimeraFilaAsoc($consulta);
	
	
	$consulta="SELECT COUNT(*) FROM _513_tablaDinamica WHERE idReferencia=".$fRegistro["id__487_tablaDinamica"];
	$nReg=$con->obtenerValor($consulta);
	if($nReg>0)
		return true;
	
	$consulta="SELECT * FROM _452_tablaDinamica WHERE idReferencia=".$idRegistro;
	$fResultado=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$arrDocumentosReferencia=NULL;
	$arrValores=array();			
	$arrValores["comentariosAdicionales"]=$fResultado["comentariosAdicionales"];
	$arrValores["documentoResolucion"]=$fResultado["documentoTribunalAlzada"];
	$arrValores["sentencia"]=$fResultado["sentencia"];
	$arrValores["dteFechaSentencia"]=$fResultado["fechaSentencia"];
	
	
	$idRegistroSolicitud=crearInstanciaRegistroFormulario(513,$fRegistro["id__487_tablaDinamica"],-1,$arrValores,$arrDocumentosReferencia,-1,0);
	if($fResultado["documentoTribunalAlzada"]!="")
	{	
		convertirDocumentoUsuarioDocumentoResultadoProceso($fResultado["documentoTribunalAlzada"],487,$fRegistro["id__487_tablaDinamica"],"",50);
		
		registrarDocumentoCarpetaAdministrativa($fRegistro["carpetaAdministrativa"],$fResultado["documentoTribunalAlzada"],487,$fRegistro["id__487_tablaDinamica"],$fRegistro["idCarpeta"]);	
	}
	
	registrarCambioSituacionCarpeta($fRegistro["carpetaAdministrativa"],6,487,$fRegistro["id__487_tablaDinamica"],-1,"",$fRegistro["idCarpeta"]);
	
	if(cambiarEtapaFormulario(487,$fRegistro["id__487_tablaDinamica"],4.1,"",-1,"NULL","NULL",0))
	{
		if($fResultado["documentoTribunalAlzada"]!="")
		{
			registrarDocumentoResultadoProceso(451,$idRegistro,$fResultado["documentoTribunalAlzada"]);	
			$consulta="SELECT carpetaApelacion FROM _451_tablaDinamica WHERE id__451_tablaDinamica=".$idRegistro;
			$carpetaApelacion=$con->obtenerValor($consulta);	
			registrarDocumentoCarpetaAdministrativa($carpetaApelacion,$fResultado["documentoTribunalAlzada"],451,$idRegistro);	
		}
		return true;
	}
	
	
	
}

function registrarResolucionAlzadaUnidadAlzada($idFormulario,$idRegistro)
{
	global $con;
	
	$consulta="SELECT * FROM _487_tablaDinamica WHERE id__487_tablaDinamica=".$idRegistro;
	$fRegistro=$con->obtenerPrimeraFilaAsoc($consulta);
	
	
	$consulta="SELECT COUNT(*) FROM _452_tablaDinamica WHERE idReferencia=".$fRegistro["iRegistro"];
	$nReg=$con->obtenerValor($consulta);
	if($nReg>0)
		return true;
	
	$consulta="SELECT * FROM _513_tablaDinamica WHERE idReferencia=".$idRegistro;
	$fResultado=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$arrDocumentosReferencia=NULL;
	$arrValores=array();			
	$arrValores["comentariosAdicionales"]=$fResultado["comentariosAdicionales"];
	$arrValores["documentoTribunalAlzada"]=$fResultado["documentoResolucion"];
	$arrValores["sentencia"]=$fResultado["sentencia"];
	$arrValores["fechaSentencia"]=$fResultado["dteFechaSentencia"];
	$arrValores["numeroToca"]=$fRegistro["carpetaAdministrativa"];	
	$arrValores["salaPenal"]=$fRegistro["tribunalAlzada"];
	
	
	$idRegistroSolicitud=crearInstanciaRegistroFormulario(452,$fRegistro["iRegistro"],-1,$arrValores,$arrDocumentosReferencia,-1,0);
	if($fResultado["documentoResolucion"]!="")
	{	
		convertirDocumentoUsuarioDocumentoResultadoProceso($fResultado["documentoResolucion"],487,$idRegistro,"",50);

		registrarDocumentoCarpetaAdministrativa($fRegistro["carpetaAdministrativa"],$fResultado["documentoResolucion"],487,$idRegistro,$fRegistro["idCarpeta"]);	
	}
	
	registrarCambioSituacionCarpeta($fRegistro["carpetaAdministrativa"],6,487,$idRegistro,-1,"",$fRegistro["idCarpeta"]);
	
	if(cambiarEtapaFormulario(451,$fRegistro["iRegistro"],3,"",-1,"NULL","NULL",816))
	{
		if($fResultado["documentoResolucion"]!="")
		{
			registrarDocumentoResultadoProceso(451,$fRegistro["iRegistro"],$fResultado["documentoResolucion"]);		
			registrarDocumentoCarpetaAdministrativa($fRegistro["carpetaJudicialApelacion"],$fResultado["documentoResolucion"],451,$fRegistro["iRegistro"]);	
		}
			
	}
	
	
	return true;
}

function permiteRegistroResultadoApelacionUnidadAlzada($idFormulario,$idRegistro,$actor)
{
	global $con;
	if($actor!=1140)
		return "0";
	
	$consulta="SELECT realizaAudiencia FROM _614_tablaDinamica WHERE idReferencia=".$idRegistro;
	$realizaAudiencia=$con->obtenerValor($consulta);
	return $realizaAudiencia==""?"0":$realizaAudiencia;
}


function permiteRegistroResultadoApelacionUnidaQuejosa($idFormulario,$idRegistro,$actor)
{
	global $con;
	
	if($actor!=817)
		return "0";
	
	$consulta="SELECT id__487_tablaDinamica FROM _487_tablaDinamica WHERE iFormulario=".$idFormulario." AND iRegistro=".$idRegistro." order by id__487_tablaDinamica desc";
	
	$idRegistroReferencia=$con->obtenerValor($consulta);
	$consulta="SELECT realizaAudiencia FROM _614_tablaDinamica WHERE idReferencia=".$idRegistroReferencia;

	$realizaAudiencia=$con->obtenerValor($consulta);
	return $realizaAudiencia==""?"0":($realizaAudiencia==1?0:1);
	
	
}


function permiteFinalizarResultadoApelacionUnidadAlzada($idFormulario,$idRegistro)
{
	global $con;
	$consulta="SELECT count(*) FROM _513_tablaDinamica WHERE idReferencia=".$idRegistro;
	$numReg=$con->obtenerValor($consulta);
	return $numReg;
}


function permiteFinalizarResultadoApelacionUnidaQuejosa($idFormulario,$idRegistro)
{
	global $con;
	$consulta="SELECT count(*) FROM _452_tablaDinamica WHERE idReferencia=".$idRegistro;
	$numReg=$con->obtenerValor($consulta);
	return $numReg;
	
	
}


function permiteRemisionSolicitudApelacionSubControl($idFormulario,$idRegistro)
{
	global $con; 
	
	
	
	$consulta="SELECT  count(*) FROM _618_tablaDinamica WHERE idReferencia=".$idRegistro;
	$fRegistroResultadoAuxJUDControl=$con->obtenerValor($consulta);
	
	$consulta="SELECT  count(*) FROM _617_tablaDinamica WHERE idReferencia=".$idRegistro;
	$fRegistroResultadoJUDControl=$con->obtenerValor($consulta);
	
	$consulta="SELECT  count(*) FROM _581_tablaDinamica WHERE idReferencia=".$idRegistro;
	$fRegistroResultadoSubControl=$con->obtenerValor($consulta);
	
	/*$consulta="SELECT count(*) FROM _582_tablaDinamica WHERE idReferencia=".$idRegistroBase;
	$fRegistroResultadoDirectorControl=$con->obtenerValor($consulta);
	
	
	$consulta="SELECT  count(*) FROM _583_tablaDinamica WHERE idReferencia=".$idRegistroBase;
	$fRegistroResultadoDirectorEjecutivo=$con->obtenerValor($consulta);*/
	
	
	
	return ($fRegistroResultadoAuxJUDControl+$fRegistroResultadoJUDControl+$fRegistroResultadoSubControl)>0?1:0;
}

function permiteRemisionSolicitudApelacionJudControl($idFormulario,$idRegistro)
{
	global $con; 
	
	
	
	$consulta="SELECT  count(*) FROM _618_tablaDinamica WHERE idReferencia=".$idRegistro;
	$fRegistroResultadoAuxJUDControl=$con->obtenerValor($consulta);
	
	$consulta="SELECT  count(*) FROM _617_tablaDinamica WHERE idReferencia=".$idRegistro;
	$fRegistroResultadoJUDControl=$con->obtenerValor($consulta);
	
	
	return ($fRegistroResultadoAuxJUDControl+$fRegistroResultadoJUDControl)>0?1:0;
}

function registrarDatosEvaluacionJUDControl($idFormulario,$idRegistro)
{
	global $con;
	
	$consulta="SELECT  * FROM _617_tablaDinamica WHERE idReferencia=".$idRegistro;
	$fRegistroResultadoJUDControl=$con->obtenerPrimeraFilaAsoc($consulta);
	
	if($fRegistroResultadoJUDControl)
	{
		$consulta="INSERT INTO _618_tablaDinamica(idReferencia,fechaCreacion,responsable,idEstado,codigoInstitucion,antecedenteSala,salaPenal)
				VALUES(".$fRegistroResultadoJUDControl["idReferencia"].",'".date("Y-m-d H:i:s")."',".$fRegistroResultadoJUDControl["responsable"].
				",1,'".$fRegistroResultadoJUDControl["codigoInstitucion"]."','".$fRegistroResultadoJUDControl["antecedenteSala"].
				"','".$fRegistroResultadoJUDControl["salaPenal"]."')";
		return $con->ejecutarConsulta($consulta);
	}
	
	return true;
	
	
	
	
	
}

function registrarDatosEvaluacionAuxJUDControl($idFormulario,$idRegistro)
{
	global $con;
	
	$consulta="SELECT  * FROM _618_tablaDinamica WHERE idReferencia=".$idRegistro;
	$fRegistroResultadoAuxJUDControl=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$consulta="SELECT  * FROM _617_tablaDinamica WHERE idReferencia=".$idRegistro;
	$fRegistroResultadoJUDControl=$con->obtenerPrimeraFilaAsoc($consulta);
	
	if($fRegistroResultadoJUDControl)
	{
		$consulta="update _617_tablaDinamica set antecedenteSala=".$fRegistroResultadoAuxJUDControl["antecedenteSala"].",salaPenal='".
					$fRegistroResultadoAuxJUDControl["salaPenal"]."' where idReferencia=".$idRegistro;
		
		return $con->ejecutarConsulta($consulta);
	}
	
	return true;
	
	
	
	
	
}

?>