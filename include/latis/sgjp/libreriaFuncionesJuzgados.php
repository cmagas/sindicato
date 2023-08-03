<?php 
include_once("latis/conexionBD.php");
include_once("latis/utiles.php");
include_once("latis/sgjp/funcionesAgenda.php");
include_once('latis/fpdf/fpdf.php'); 
include_once('latis/fpdi/fpdi.php'); 
include_once('latis/tcpdf/tcpdf_barcodes_2d.php'); 
include_once("latis/cCodigoBarras.php");
include_once("latis/nusoap/nusoap.php");
//include_once("latis/latisErrorHandler.php");


function generarFolioCarpetaExpedienteJuzgadoVersionAutomatica($idFormulario,$idRegistro)
{
	global $con;
	
	$anio=date("Y");
	$query="SELECT idActividad,codigoInstitucion,carpetaAdministrativa,juez FROM _478_tablaDinamica WHERE id__478_tablaDinamica=".$idRegistro;

	$fRegistro=$con->obtenerPrimeraFila($query);

	if(($fRegistro[2]!="N/E")&&($fRegistro[2]!=""))
	{
		return true;
	}
	
	$idActividad=$fRegistro[0];
	$carpetaInvestigacion="";
	$query="SELECT claveFolioCarpetas,claveUnidad,id__17_tablaDinamica FROM _17_tablaDinamica WHERE claveUnidad='".$fRegistro[1]."'";
	$fRegistroUnidad=$con->obtenerPrimeraFila($query);
	$cvAdscripcion=$fRegistroUnidad[1];
	$idUnidadGestion=$fRegistroUnidad[2];
	
	
	$carpetaAdministrativa=obtenerSiguienteCarpetaAdministrativa($idUnidadGestion,$anio,1,$idFormulario,$idRegistro,true);
	
	$x=0;
	$consulta[$x]="begin";
	$x++;
	$consulta[$x]="INSERT INTO 7006_carpetasAdministrativas(carpetaAdministrativa,fechaCreacion,responsableCreacion,idFormulario,idRegistro,unidadGestion,etapaProcesalActual,idActividad,tipoCarpetaAdministrativa,carpetaInvestigacion,llaveCarpetaInvestigacion,idJuezTitular) 
					VALUES('".$carpetaAdministrativa."','".date("Y-m-d H:i:s")."',".$_SESSION["idUsr"].",".$idFormulario.",'".$idRegistro."','".$cvAdscripcion."',1,".$idActividad.",1,(SELECT UPPER('".$carpetaInvestigacion."')),'".
					cv(generarLlaveCarpetaInvestigacion($carpetaInvestigacion))."',".$fRegistro[3].")";
	$x++;
	$consulta[$x]="update _".$idFormulario."_tablaDinamica set carpetaAdministrativa='".$carpetaAdministrativa."' where id__".$idFormulario."_tablaDinamica=".$idRegistro;
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

function generarFolioCarpetaExpedienteJuzgado($idFormulario,$idRegistro)
{
	global $con;
	
	$anio=date("Y");
	$query="SELECT idActividad,codigoInstitucion,carpetaAdministrativa,juez,secretario,tipoExpediente FROM _478_tablaDinamica WHERE id__478_tablaDinamica=".$idRegistro;

	$fRegistro=$con->obtenerPrimeraFila($query);

	$tipoExpediente=$fRegistro[5];
	$tipoExpediente=1;
	$idActividad=$fRegistro[0];
	$carpetaInvestigacion="";
	$query="SELECT claveFolioCarpetas,claveUnidad,id__17_tablaDinamica FROM _17_tablaDinamica WHERE claveUnidad='".$fRegistro[1]."'";
	
	$fRegistroUnidad=$con->obtenerPrimeraFila($query);
	$cvAdscripcion=$fRegistroUnidad[1];
	$idUnidadGestion=$fRegistroUnidad[2];
	

	$carpetaAdministrativa=$fRegistro[2];
	if(existeCarpetaAdministrativa($carpetaAdministrativa,$cvAdscripcion))
	{
		
		return true;
	}
		
	$x=0;
	$consulta[$x]="begin";
	$x++;
	$consulta[$x]="INSERT INTO 7006_carpetasAdministrativas(carpetaAdministrativa,fechaCreacion,responsableCreacion,idFormulario,
					idRegistro,unidadGestion,etapaProcesalActual,idActividad,tipoCarpetaAdministrativa,carpetaInvestigacion,
					llaveCarpetaInvestigacion,idJuezTitular,secretariaAsignada) 
					VALUES('".$carpetaAdministrativa."','".date("Y-m-d H:i:s")."',".$_SESSION["idUsr"].",".$idFormulario.",'".$idRegistro."','".$cvAdscripcion."',1,".$idActividad.",".$tipoExpediente.",(SELECT UPPER('".$carpetaInvestigacion."')),'".
					cv(generarLlaveCarpetaInvestigacion($carpetaInvestigacion))."','".$fRegistro[3]."','".$fRegistro[4]."')";
	$x++;
	$consulta[$x]="update _".$idFormulario."_tablaDinamica set carpetaAdministrativa='".$carpetaAdministrativa."' where id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$x++;
	$consulta[$x]="set @idCarpeta:=(select  last_insert_id())";
	$x++;
	$consulta[$x]="commit";


	if($con->ejecutarBloque($consulta))
	{

		$query="select @idCarpeta";
		$idCarpeta=$con->obtenerValor($query);
		$query="SELECT idDocumento FROM 9074_documentosRegistrosProceso WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro." and tipoDocumento<>3";
		$rDocumentos=$con->obtenerFilas($query);
		while($fDocumento=mysql_fetch_row($rDocumentos))
		{
			registrarDocumentoCarpetaAdministrativa($carpetaAdministrativa,$fDocumento[0],$idFormulario,$idRegistro,$idCarpeta);	
		}

		/*$query="SELECT idRegistroEvento FROM 7000_eventosAudiencia WHERE idFormulario=".$idFormulario." AND idRegistroSolicitud=".$idRegistro;
		$rAudiencias=$con->obtenerFilas($query);
		while($fAudiencia=mysql_fetch_row($rAudiencias))
		{
			registrarAudienciaCarpetaAdministrativa($idFormulario,$idRegistro,$fAudiencia[0]);
		}*/
		
	}
	
	return false;
	
}


function generarPropuestaFechaAudienciaJuzgado($idFormulario,$idRegistro)
{

	global $con;
	$consulta="SELECT tipoAudiencia FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;

	$tipoAudiencia=$con->obtenerValor($consulta);
	

	$idEventoAudiencia=obtenerFechaAudienciaSolicitudJuzgado($idFormulario,$idRegistro,-1,$tipoAudiencia);
	if($idEventoAudiencia!=-1)
	{
		$idCarpetaAdministrativa=-1;
		$nombreTablaBase="_".$idFormulario."_tablaDinamica";
		$campoLlave="id_".$nombreTablaBase;
		if($con->existeCampo("idCarpetaAdministrativa",$nombreTablaBase))
		{
			$query="select idCarpetaAdministrativa from ".$nombreTablaBase." where ".$campoLlave."=".$idRegistro;
			$idCarpetaAdministrativa=$con->obtenerValor($query);
			
			
		}
		$carpetaAdministrativa=obtenerCarpetaAdministrativaProceso($idFormulario,$idRegistro);
		$query="SELECT idDocumento FROM 9074_documentosRegistrosProceso WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro;
		$rDocumentos=$con->obtenerFilas($query);
		while($fDocumento=mysql_fetch_row($rDocumentos))
		{
			registrarDocumentoCarpetaAdministrativa($carpetaAdministrativa,$fDocumento[0],$idFormulario,$idRegistro,$idCarpetaAdministrativa);	
		}
		return true;
	}
	return false;
}

function obtenerFechaAudienciaSolicitudJuzgado($idFormulario,$idRegistro,$idReferencia,$tipoAudiencia,$nivel=4)
{
	global $con;
	$consulta="SELECT idRegistroEvento,fechaEvento,horaInicioEvento,horaFinEvento,idEdificio,idCentroGestion,idSala 
				FROM 7000_eventosAudiencia where  idFormulario=".$idFormulario." and idRegistroSolicitud=".$idRegistro.
				" and idReferencia=".$idReferencia;

	$fEventoAudiencia=$con->obtenerPrimeraFila($consulta);
	
	$idEvento=$fEventoAudiencia[0];
	$fEventoAudiencia=NULL;
	if((!$fEventoAudiencia)||($fEventoAudiencia[2]==""))
	{
		$oDatosAudiencia=array();
		
		
		$consulta="SELECT * FROM _185_tablaDinamica WHERE id__185_tablaDinamica=".$idRegistro;
		
		$fDatosSolicitud=$con->obtenerPrimeraFilaAsoc($consulta);
		
		$consulta="SELECT unidadGestion FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$fDatosSolicitud["carpetaAdministrativa"]."'
					and idCarpeta=".$fDatosSolicitud["idCarpetaAdministrativa"];
		
		$unidadGestion=$con->obtenerValor($consulta);
		
		$consulta="SELECT id__17_tablaDinamica,idReferencia FROM _17_tablaDinamica WHERE claveUnidad='".$unidadGestion."'";
		$fUnidadGestion=$con->obtenerPrimeraFila($consulta);
		
		
		$oDatosAudiencia["idRegistroEvento"]=$fEventoAudiencia[0];
		$oDatosAudiencia["idEdificio"]=$fUnidadGestion[1];
		$oDatosAudiencia["idUnidadGestion"]=$fUnidadGestion[0];
		
		$oDatosAudiencia["idSala"]="";
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
		$oDatosParametros["nivelAsignacion"]=$nivel; //1 Hasta UGJ; 2 Total
		
		
		
		
		$consulta="SELECT * FROM _27_tablaDinamica";
		$margen=10;
		$fConfiguracion=$con->obtenerPrimeraFilaAsoc($consulta);	
		
		$fechaSolicitud="";
		
		$cache=NULL;
		$cadObj='{"idFormulario":"'.$idFormulario.'","idRegistro":"'.$idRegistro.'","idReferencia":"'.$idReferencia.'","tipoAudiencia":"'.$tipoAudiencia.'"}';
		
		$obj=json_decode($cadObj);
		
		$consulta="SELECT fechaCreacion FROM _185_tablaDinamica WHERE id__185_tablaDinamica=".$idRegistro;
		
		$fechaSolicitud=$con->obtenerValor($consulta);
	
		
		$consulta="SELECT promedioDuracion,horasMaximaAgendaAudiencia,agendaDiaNoHabil,tipoAtencion,horaMinimasAudiencia
				FROM _4_tablaDinamica WHERE id__4_tablaDinamica=".$tipoAudiencia;
		$fDatosTiposAudiencia=$con->obtenerPrimeraFila($consulta);
		
		$fechaMaximaAudiencia=NULL;  //Parametro
		
		$esSolicitudUgente=($fDatosTiposAudiencia[3]==2); //Parametro
		
		$considerarDiaHabil=false;	//Parametro
		$minutosMiniminosFecha=0;
		$numeroHoraMaximaHoras=0;
		$duracionAudiencia=0;		//Parametro
		
		$funcionDiaHabil=$fConfiguracion["funcionDiHabil"];  //Parametro
		$totalJueces=1;
		
		if($esSolicitudUgente)
		{
			$minutosMiniminosFecha=(($fDatosTiposAudiencia[4]==0)||($fDatosTiposAudiencia[4]==""))?$fConfiguracion["minutosMinimoUrgencias"]:($fDatosTiposAudiencia[4]*60);
			$numeroHoraMaximaHoras=$fConfiguracion["numeroHoraMaximasUrgencias"];
		}
		else
		{
			
			$minutosMiniminosFecha=(($fDatosTiposAudiencia[4]==0)||($fDatosTiposAudiencia[4]==""))?($fConfiguracion["horasMinimasOrdinaria"]*60):($fDatosTiposAudiencia[4]*60);
			$numeroHoraMaximaHoras=0;
			$considerarDiaHabil=($fDatosTiposAudiencia[2]==0);
		}
			
		
		
		$fechaMinimaAudiencia=date("Y-m-d H:i:s",strtotime("+".$minutosMiniminosFecha." minutes",strtotime($fechaSolicitud)));  //Parametro

		$duracionAudiencia=$fDatosTiposAudiencia[0];
		$horasMaximas=$fDatosTiposAudiencia[1];
		if($horasMaximas>0)
		{
			$numeroHoraMaximaHoras=$horasMaximas;
		}
			
			
			
			
			
		if($fDatosSolicitud["fechaEstimadaAudiencia"]!="")
		{
			$fechaMinimaAudiencia=$fDatosSolicitud["fechaEstimadaAudiencia"];
			$fechaMaximaAudiencia=NULL;
			if($fDatosSolicitud["duracionRequerida"]!="")
				$duracionAudiencia=$fDatosSolicitud["duracionRequerida"];
				
			if($fechaMinimaAudiencia==date("Y-m-d"))
			{
				$fechaMinimaAudiencia=date("Y-m-d H:i:s",strtotime("+ ".$minutosMiniminosFecha." minutes",strtotime(date("Y-m-d H:i:s"))));	
			}
		}
		else
		{	
			if(($fechaMaximaAudiencia=="")&&($numeroHoraMaximaHoras>0))
				$fechaMaximaAudiencia=date("Y-m-d H:i:s",strtotime("+".$numeroHoraMaximaHoras." hours",strtotime($fechaSolicitud)));
			else
				if(($fechaMaximaAudiencia=="")&&($numeroHoraMaximaHoras==0))
					$fechaMaximaAudiencia=NULL;	
		}
		
		
		
		

		
		if($fechaMaximaAudiencia!=NULL &&  (strtotime($fechaMaximaAudiencia)<strtotime(date("Y-m-d H:i:s"))))
		{
			$fechaMaximaAudiencia=NULL;
		}
		
		
		
		if(($fechaMaximaAudiencia!=NULL)&&($considerarDiaHabil))
		{
			
			$fechaMaximaAudiencia=obtenerHorasAjusteDiasNoHabiles($fechaMinimaAudiencia,$fechaMaximaAudiencia);

		}
		
		
		if($fConfiguracion["promedioTiempo"]>0)	
		{
			$consulta="SELECT horaTerminoReal,horaInicioReal FROM 7000_eventosAudiencia WHERE tipoAudiencia=".$tipoAudiencia." AND  horaInicioReal IS NOT NULL order by horaInicioEvento desc limit 0,".$fConfiguracion["promedioTiempo"];
			$resAudiencia=$con->obtenerFilas($consulta);
			if($con->filasAfectadas==$fConfiguracion["promedioTiempo"])
			{
				$totalMinutos=0;
				while($fAudiencia=mysql_fetch_row($resAudiencia))
				{
					$totalMinutos+=obtenerDiferenciaMinutos($fAudiencia[1],$fAudiencia[0]);
				}
				
				$duracionAudiencia=floor($totalMinutos/$fConfiguracion["promedioTiempo"])+$margen;
				
			}
		}
		
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
		
		$oDatosParametros["idRegistroConfiguracionAgenda"]=$fConfiguracion["id__27_tablaDinamica"];
		$oDatosParametros["criterioBalanceoEdificio"]=$fConfiguracion["tipoBalanceoEdificio"];
		$oDatosParametros["criterioBalanceoUnidadGestion"]=$fConfiguracion["criterioBalanceoUnidadGestion"];
		$oDatosParametros["criterioBalanceoSala"]=$fConfiguracion["tipoBalanceoAsignacionSala"];
		$oDatosParametros["criterioBalanceoJuez"]=$fConfiguracion["tipoBalanceoAsignacionJuez"];	
		$oDatosParametros["horasMaximaAsignablesJuez"]=$fConfiguracion["horasMaximaAsignablesJuez"];		
		$oDatosParametros["duracionAudiencia"]=$duracionAudiencia;			
		$oDatosParametros["fechaMaximaAudiencia"]=$fechaMaximaAudiencia;
		$oDatosParametros["fechaMinimaAudiencia"]=$fechaMinimaAudiencia;
		$oDatosParametros["considerarDiaHabil"]=$considerarDiaHabil;
		$oDatosParametros["funcionDiaHabil"]=$funcionDiaHabil;
		$oDatosParametros["esSolicitudUgente"]=$esSolicitudUgente;		
		$oDatosParametros["fechaBasePeriodo"]=strtotime($fechaSolicitud);
		$oDatosParametros["validaJuezTramite"]=false;
		$oDatosParametros["validaIncidenciaJuez"]=true;
		$oDatosParametros["permitirExcederHoraFinal"]=1;
		$oDatosParametros["intervaloTiempoEvento"]=10;
		$diaBase=date("N",$oDatosParametros["fechaBasePeriodo"]);
		
		$oDatosParametros["fechaBasePeriodo"]=date("Y-m-d",strtotime("-".($diaBase-1)." days",$oDatosParametros["fechaBasePeriodo"]));
		
		$oEvento=generarFechaAudienciaSolicitudV3($oDatosParametros);
		/*if($_SESSION["idUsr"]==1)
		{
			
			varDump($oEvento);
			return;
		}*/

		
		
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

function asignarJuezAudienciaJuzgado($oDatosAudiencia,$oDatosParametros,$tipoJuez,$listaJuecesIgn,$fechaAudiencia)
{
	global $con;	
	
	$cAdministrativaBase=obtenerCarpetaAdministrativaProceso($oDatosParametros["idFormulario"],$oDatosParametros["idRegistro"]);
	
	$idCarpetaAdministrativa=-1;
	$nombreTablaBase="_".$oDatosParametros["idFormulario"]."_tablaDinamica";
	$campoLlave="id_".$nombreTablaBase;
	if($con->existeCampo("idCarpetaAdministrativa",$nombreTablaBase))
	{
		$query="select idCarpetaAdministrativa from ".$nombreTablaBase." where ".$campoLlave."=".$oDatosParametros["idRegistro"];
		$idCarpetaAdministrativa=$con->obtenerValor($query);
		
		
	}
	
	$consulta="SELECT idJuezTitular FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$cAdministrativaBase."'";
	if($idCarpetaAdministrativa!=-1)
	{
		$consulta.=" and idCarpeta=".$idCarpetaAdministrativa;
	}
	$idJuez=$con->obtenerValor($consulta);
	if($idJuez=="")
		$idJuez=-1;
		
	
		
	$cadObj='{';
	foreach($oDatosAudiencia as $campo=>$valor)
	{
		if(($campo=="listaEdificiosIgnorar")||($campo=="listaUnidadesGestionIgnorar")||($campo=="listaSalasIgnorar"))
		{
			continue;
		}
		if((gettype($valor)!='array')&&(gettype($valor)!='object'))
		{
			if($cadObj=='{')
				$cadObj.='"'.$campo.'":"'.$valor.'"';
			else
				$cadObj.=',"'.$campo.'":"'.$valor.'"';
		}
	}
	
	foreach($oDatosParametros as $campo=>$valor)
	{
		if((gettype($valor)!='array')&&(gettype($valor)!='object'))
		{
			if(gettype($valor)=='boolean')
			{
				if($valor)
					$valor=1;
				else
					$valor=0;
			}
			
			if($cadObj=='{')
				$cadObj.='"'.$campo.'":"'.$valor.'"';
			else
				$cadObj.=',"'.$campo.'":"'.$valor.'"';
		}
	}
	$cadObj.=',"tipoJuez":"'.$tipoJuez.'","metodoBalanceoEventosJuez":"0"';
	$cadObj.='}';
	
	$objParametro=json_decode($cadObj);	
	
	//if(existeDisponibilidadHorarioJuez($idJuez,$objParametro->fecha,$objParametro->horaInicio,$objParametro->horaFin,-1,$oDatosParametros["fechaSolicitud"],true))
	//{

		if(esJuezDisponibleIncidencia($idJuez,$fechaAudiencia))
		{
			return $idJuez;
		}
//	}
	return -1;
	
}

function asignarJuzgadoNuevoExpediente($idFormulario,$idRegistro)
{
	global $con;
	$idUga=-1;
	$idJuez=-1;
	$idSecretario=-1;
	
	$consulta="SELECT id__17_tablaDinamica,(SELECT COUNT(*) FROM 7006_carpetasAdministrativas WHERE unidadGestion=u.claveUnidad) AS totalSolicitudes 
				FROM _17_tablaDinamica u WHERE cmbCategoria=1";
	
	$arrBalanceo=array();
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		$arrBalanceo[$fila[0]]=$fila[1];
	}
	
	
	$minValor=-1;
	
	foreach($arrBalanceo as $idUga=>$totalSolicitudes)
	{
		if($minValor==-1)
		{
			$minValor=$totalSolicitudes;
		}
		
		if($minValor>$totalSolicitudes)
		{
			$minValor=$totalSolicitudes;
		}
		
	}
	
	
	$arrFinal=array();
	foreach($arrBalanceo as $idUga=>$totalSolicitudes)
	{
		if($minValor==$totalSolicitudes)
		{
			array_push($arrFinal,$idUga);
		}
		
		
		
	}
	
	if(sizeof($arrFinal)==1)
		$idUga=$arrFinal[0];
	else
		$idUga=$arrFinal[rand(0,sizeof($arrFinal)-1)];
	$consulta="SELECT claveUnidad FROM _17_tablaDinamica WHERE id__17_tablaDinamica=".$idUga;
	$codigoUnidad=$con->obtenerValor($consulta);
	
	$consulta="SELECT usuarioJuez,(SELECT COUNT(*) FROM 7006_carpetasAdministrativas WHERE unidadGestion='".$codigoUnidad."' AND idJuezTitular=j.usuarioJuez) AS totalAsuntos 
			FROM _26_tablaDinamica j WHERE idReferencia=".$idUga." AND usuarioJuez<>-1 AND usuarioJuez<>'' AND usuarioJuez is not null";
	
	$arrBalanceo=array();
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		$arrBalanceo[$fila[0]]=$fila[1];
	}
	
	
	$minValor=-1;
	
	foreach($arrBalanceo as $idJuez=>$totalSolicitudes)
	{
		if($minValor==-1)
		{
			$minValor=$totalSolicitudes;
		}
		
		if($minValor>$totalSolicitudes)
		{
			$minValor=$totalSolicitudes;
		}
		
	}
	
	
	$arrFinal=array();
	foreach($arrBalanceo as $idJuez=>$totalSolicitudes)
	{
		if($minValor==$totalSolicitudes)
		{
			array_push($arrFinal,$idJuez);
		}
		
		
		
	}
	
	if(sizeof($arrFinal)==1)
		$idJuez=$arrFinal[0];
	else
		$idJuez=$arrFinal[rand(0,sizeof($arrFinal)-1)];
	
	
	
	$consulta="SELECT a.idUsuario,(SELECT COUNT(*) FROM _478_tablaDinamica WHERE codigoInstitucion='".$codigoUnidad."' AND secretario=a.idUsuario AND idEstado>1) AS totalSolicitudes 
			FROM 807_usuariosVSRoles uR,801_adscripcion a WHERE uR.codigoRol='16_0' AND uR.idUsuario=a.idUsuario AND a.Institucion='".$codigoUnidad."'";
	
	$arrBalanceo=array();
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		$arrBalanceo[$fila[0]]=$fila[1];
	}
	
	$minValor=-1;
	
	foreach($arrBalanceo as $idSecretario=>$totalSolicitudes)
	{
		if($minValor==-1)
		{
			$minValor=$totalSolicitudes;
		}
		
		if($minValor>$totalSolicitudes)
		{
			$minValor=$totalSolicitudes;
		}
		
	}
	
	
	$arrFinal=array();
	foreach($arrBalanceo as $idSecretario=>$totalSolicitudes)
	{
		if($minValor==$totalSolicitudes)
		{
			array_push($arrFinal,$idSecretario);
		}
		
		
		
	}
	
	if(sizeof($arrFinal)==1)
		$idSecretario=$arrFinal[0];
	else
		$idSecretario=$arrFinal[rand(0,sizeof($arrFinal)-1)];
	
	
	$consulta="UPDATE _".$idFormulario."_tablaDinamica SET codigoInstitucion='".$codigoUnidad."',juez=".$idJuez.
			",secretario=".$idSecretario." WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	
	
	if($con->ejecutarConsulta($consulta))
	{
		cambiarEtapaFormulario($idFormulario,$idRegistro,1.6,"",-1,"NULL","NULL",857);
		return true;
	}
	
	
}


function guardarCarpetasAdministrativaRegistro($idFormulario,$idRegistro)
{
	global $con;
	$consulta="SELECT noExpediente,anioExpediente,numeracionExpediente FROM _478_tablaDinamica WHERE id__478_tablaDinamica=".$idRegistro;
	$fRegistro=$con->obtenerPrimeraFila($consulta);
	$carpetaAdministrativa=str_pad($fRegistro[0],4,"0",STR_PAD_LEFT)."/".intval($fRegistro[1]);
	$secretaria=determinarSecretariaExpediente($carpetaAdministrativa);
	if(($fRegistro[2]!="")&&($fRegistro[2]>1))
	
	{
		$consulta="SELECT nombreElemento FROM 1018_catalogoVarios WHERE tipoElemento=30 and claveElemento='".$fRegistro[2]."'";
		$sufijo=$con->obtenerValor($consulta);
		$carpetaAdministrativa.=$sufijo;
	}

	$consulta="UPDATE _478_tablaDinamica SET secretariaAsignada='".$secretaria."',secretario='".$secretaria."',carpetaAdministrativa='".$carpetaAdministrativa."'  WHERE id__478_tablaDinamica=".$idRegistro;

	return $con->ejecutarConsulta($consulta);
	
}




function generarFechaAudienciaSolicitudV2($oDatosParametros)
{
	global $con;
	global $tipoMateria;
	$nInteraciones=0;	
	
	$idFormulario=$oDatosParametros["idFormulario"];
	$idRegistro=$oDatosParametros["idRegistro"];
	$idReferencia=$oDatosParametros["idReferencia"];
	$tipoAudiencia=$oDatosParametros["tipoAudiencia"];
	
	$oDatosAudiencia=array();
	$oDatosAudiencia["idRegistroEvento"]=$oDatosParametros["oDatosAudiencia"]["idRegistroEvento"];
	$oDatosAudiencia["idEdificio"]="";
	$oDatosAudiencia["listaEdificiosIgnorar"]=-1;
	$oDatosAudiencia["idUnidadGestion"]="";
	$oDatosAudiencia["listaUnidadesGestionIgnorar"]=-1;
	$oDatosAudiencia["idSala"]="";
	$oDatosAudiencia["listaSalasIgnorar"]=-1;
	$oDatosAudiencia["fecha"]="";
	$oDatosAudiencia["horaInicio"]="";
	$oDatosAudiencia["horaFin"]="";
	$oDatosAudiencia["jueces"]="";
	
	$notificarMAJO=$oDatosParametros["notificarMAJO"];
	$idRegistroConfiguracionAgenda=$oDatosParametros["idRegistroConfiguracionAgenda"];
	
	$listaSalas="";
	$arrSalas=array();

	$cadObj='{';
	foreach($oDatosParametros as $campo=>$valor)
	{
		if((gettype($valor)!='array')&&(gettype($valor)!='object'))
		{
			if($cadObj=='{')
				$cadObj.='"'.$campo.'":"'.$valor.'"';
			else
				$cadObj.=',"'.$campo.'":"'.$valor.'"';
		}
	}
	$cadObj.='}';
	
	$cache=NULL;
	
	$objFuncion=json_decode($cadObj);
	
	if(isset($oDatosParametros["oDatosAudiencia"])&&($oDatosParametros["oDatosAudiencia"]["idSala"]!="")&&($oDatosParametros["oDatosAudiencia"]["idSala"]!="-1"))
	{
		$listaSalas=$oDatosParametros["oDatosAudiencia"]["idSala"];
	}
	else
	{
		$consulta="SELECT id__15_tablaDinamica FROM _15_tablaDinamica where 1=1";

		if(isset($oDatosParametros["oDatosAudiencia"])&&($oDatosParametros["oDatosAudiencia"]["idEdificio"]!="")&&($oDatosParametros["oDatosAudiencia"]["idEdificio"]!="-1"))
		{
			$consulta.=" and idReferencia=".$oDatosParametros["oDatosAudiencia"]["idEdificio"];
		}
		
		$consulta.=" and perfilSala=2";
		
		$resSalas=$con->obtenerFilas($consulta);
		while($fSalas=mysql_fetch_row($resSalas))
		{
			$arrSalas[$fSalas[0]]=1;
		}
		$consulta="SELECT funcionModificadora FROM _27_gridSeleccionSala WHERE idReferencia=".$idRegistroConfiguracionAgenda;
		
	
		
		$resFuncion=$con->obtenerFilas($consulta);
		while($fFuncion=mysql_fetch_row($resFuncion))
		{
			
			$listaSalasFuncion=removerComillasLimite(resolverExpresionCalculoPHP($fFuncion[0],$objFuncion,$cache));
			$arrSalasFuncion=explode(",",$listaSalasFuncion);
			foreach($arrSalas as $idSala=>$resto)
			{
				if(existeValor($arrSalasFuncion,$idSala))
				{
					$arrAux[$idSala]="1";
				}
				
			}
			$arrSalas=$arrAux;
		}
		
		foreach($arrSalas as $idSala=>$resto)
		{
			if($listaSalas=="")
				$listaSalas=$idSala;
			else
				$listaSalas.=",".$idSala;
		}	
	
	}
	
	
	
	
	$listaEdificios="";
	
	if(isset($oDatosParametros["oDatosAudiencia"])&&($oDatosParametros["oDatosAudiencia"]["idEdificio"]!="")&&($oDatosParametros["oDatosAudiencia"]["idEdificio"]!="-1"))
	{
		$listaEdificios=$oDatosParametros["oDatosAudiencia"]["idEdificio"];
	}
	else
	{
		$consulta="select distinct e.id__1_tablaDinamica from _15_tablaDinamica s,_1_tablaDinamica e where  
					id__15_tablaDinamica in (".$listaSalas.") and e.id__1_tablaDinamica=s.idReferencia and e.idEstado=2";
		
		$listaEdificios=$con->obtenerListaValores($consulta);

		if($listaEdificios=="")
		{
			return -10;
		}
	
		$arrEdificios=explode(",",$listaEdificios);

		$consulta="SELECT funcionModificadora FROM _27_gridFuncionesSeleccionEdificio WHERE idReferencia=".$idRegistroConfiguracionAgenda;
		$resFuncion=$con->obtenerFilas($consulta);
		while($fFuncion=mysql_fetch_row($resFuncion))
		{
			
			$arrAux=array();
			$listaEdificiosFuncion=removerComillasLimite(resolverExpresionCalculoPHP($fFuncion[0],$objFuncion,$cache));
			$arrEdificiosFuncion=explode(",",$listaEdificiosFuncion);
			foreach($arrEdificios as $idEdificio)
			{
				if(existeValor($arrEdificiosFuncion,$idEdificio))
				{
					array_push($arrAux,$idEdificio);
				}
				
			}
			$arrEdificios=$arrAux;
		}
		

		$listaEdificios="";
		
		
		if(sizeof($arrEdificios)>0)
			$listaEdificios=implode(",",$arrEdificios);


		
		if($listaEdificios=="")
		{
			
			return -10;  //No existen edificios disponibles
		}
	}
	
	
	
	$fechaBasePeriodo=$oDatosParametros["fechaBasePeriodo"]." 00:00:01";

	$fechaInicialPeriodo=date("Y-m-d",strtotime("- 0 days",strtotime($oDatosParametros["fechaBasePeriodo"])))." 00:00:00";	
	$fechaFinalPeriodo=date("Y-m-d ",strtotime("+ 15 days",strtotime($fechaInicialPeriodo)))." 23:59:59";		
	
	$fechaInicialPeriodo=date("Y-06-16",strtotime($oDatosParametros["fechaBasePeriodo"]));	
	$fechaFinalPeriodo=date("Y-m-d");	
	
	$fechaMaximaPermitida=$oDatosParametros["fechaMaximaAudiencia"];
	if($fechaMaximaPermitida!=NULL)
	{
		$fechaMaximaPermitida=strtotime($oDatosParametros["fechaMaximaAudiencia"]);
	}
	
	
	$edificioAsignado=false;
	$determinacionFechaSiguiente=false;
	$diasIncremento=3;
	
	
	$nCiclos=0;
	
	while(!$edificioAsignado)	
	{
		if(isset($oDatosParametros["oDatosAudiencia"])&&($oDatosParametros["oDatosAudiencia"]["idEdificio"]!="")&&($oDatosParametros["oDatosAudiencia"]["idEdificio"]!="-1"))
		{
			$oDatosAudiencia["idEdificio"]=$oDatosParametros["oDatosAudiencia"]["idEdificio"];
			if($oDatosAudiencia["listaEdificiosIgnorar"]!="-1")
			{
				$arrEdificiosIgnorar=explode(",",$oDatosAudiencia["listaEdificiosIgnorar"]);
				if(existeValor($arrEdificiosIgnorar,$oDatosAudiencia["idEdificio"]))
				{
					$oDatosAudiencia["idEdificio"]=-1;
				}
			}
			
			
			
		}
		else
		{
			switch($oDatosParametros["criterioBalanceoEdificio"])//Asignacion Unidad gestion
			{
				case "1"://No. horas asignadas
					$oDatosAudiencia["idEdificio"]=obtenerEdificioNumeroHoras($tipoAudiencia,$fechaInicialPeriodo,$fechaFinalPeriodo,$listaEdificios,$oDatosAudiencia["listaEdificiosIgnorar"]);
				break;
				case "2"://No. eventos asignados
					$oDatosAudiencia["idEdificio"]=obtenerEdificioNumeroEventos($tipoAudiencia,$fechaInicialPeriodo,$fechaFinalPeriodo,$listaEdificios,$oDatosAudiencia["listaEdificiosIgnorar"]);
				break;
				case "3"://Secuencial				
					$oDatosAudiencia["idEdificio"]=obtenerSiguienteEntidad(4,$tipoAudiencia,$listaEdificios,$ignEdificio);
				break;
			}
		}
				
		
		
				
		if($oDatosAudiencia["idEdificio"]==-1)
		{
			break;
		}
		$oDatosAudiencia["listaEdificiosIgnorar"].=",".$oDatosAudiencia["idEdificio"];	
		
		$listaUnidades=-1;			
		
		
		
		if(isset($oDatosParametros["oDatosAudiencia"])&&($oDatosParametros["oDatosAudiencia"]["idUnidadGestion"]!="")&&($oDatosParametros["oDatosAudiencia"]["idUnidadGestion"]!="-1"))
		{
			$listaUnidades=$oDatosParametros["oDatosAudiencia"]["idUnidadGestion"];
		}
		else
		{
			$consulta="SELECT DISTINCT idReferencia FROM _55_tablaDinamica WHERE salasVinculadas IN(".$listaSalas.") and idReferencia>0";
			
			$listaUnidades=$con->obtenerListaValores($consulta);
			if($listaUnidades=="")
			{
				$listaUnidades=-1;
			}
			
			$consulta="SELECT id__17_tablaDinamica FROM _17_tablaDinamica WHERE idReferencia=".$oDatosAudiencia["idEdificio"]." 
					and id__17_tablaDinamica in(".$listaUnidades.") and idEstado=2";
			$listaUnidades=$con->obtenerListaValores($consulta);
					
			$arrUnidades=explode(",",$listaUnidades);
			
			$consulta="SELECT funcionModificadora FROM _27_gridFuncionesSeleccionUnidadGestion WHERE idReferencia=".$idRegistroConfiguracionAgenda;
			$resFuncion=$con->obtenerFilas($consulta);
			while($fFuncion=mysql_fetch_row($resFuncion))
			{
				
				$arrAux=array();
				
				$listaUnidadesFuncion=removerComillasLimite(resolverExpresionCalculoPHP($fFuncion[0],$objFuncion,$cache));
				$arrUnidadesFuncion=explode(",",$listaUnidadesFuncion);

				foreach($arrUnidades as $idUnidad)
				{
					if(existeValor($arrUnidadesFuncion,$idUnidad))
					{
						array_push($arrAux,$idUnidad);
					}
					
				}
				$arrUnidades=$arrAux;
			}
			
			$listaUnidades="";
			if(sizeof($arrUnidades)>0)
				$listaUnidades=implode(",",$arrUnidades);
			if($listaUnidades=="")
			{
				continue;
			}
		}		
		
		
		
		$unidadControlAsignada=false;
		
		while(!$unidadControlAsignada)
		{

			if(isset($oDatosParametros["oDatosAudiencia"])&&($oDatosParametros["oDatosAudiencia"]["idUnidadGestion"]!="")&&($oDatosParametros["oDatosAudiencia"]["idUnidadGestion"]!="-1"))
			{
				$oDatosAudiencia["idUnidadGestion"]=$oDatosParametros["oDatosAudiencia"]["idUnidadGestion"];
				if($oDatosAudiencia["listaUnidadesGestionIgnorar"]!="-1")
				{
					$arrUnidadesGestionIgnorar=explode(",",$oDatosAudiencia["listaUnidadesGestionIgnorar"]);
					if(existeValor($arrUnidadesGestionIgnorar,$oDatosAudiencia["idUnidadGestion"]))
					{
						return -20;
					}
				}
			}
			else
			{
				
				$oDatosParametros["criterioBalanceoUnidadGestion"]=4;
				switch($oDatosParametros["criterioBalanceoUnidadGestion"])//Asignacion Unidad gestion
				{
					case "1"://No. horas asignadas
						$oDatosAudiencia["idUnidadGestion"]=obtenerUnidadGestionNumeroHoras($tipoAudiencia,$fechaInicialPeriodo,$fechaFinalPeriodo,$listaUnidades,$oDatosAudiencia["listaUnidadesGestionIgnorar"]);
					break;
					case "2"://No. eventos asignados
						$oDatosAudiencia["idUnidadGestion"]=obtenerUnidadGestionNumeroEventos($tipoAudiencia,$fechaInicialPeriodo,$fechaFinalPeriodo,$listaUnidades,$oDatosAudiencia["listaUnidadesGestionIgnorar"],$oDatosParametros);
					break;
					case "3"://Secuencial
						$oDatosAudiencia["idUnidadGestion"]=obtenerSiguienteEntidad(1,$tipoAudiencia,$listaUnidades,$oDatosAudiencia["listaUnidadesGestionIgnorar"]);
					break;
					case "4"://Secuencial
						$oDatosAudiencia["idUnidadGestion"]=obtenerUnidadGestionNumeroAsignaciones($tipoAudiencia,$fechaInicialPeriodo,$fechaFinalPeriodo,$listaUnidades,$oDatosAudiencia["listaUnidadesGestionIgnorar"],$oDatosParametros);
					break;
					
				}
			}
			
			
			
			
			
			
			$oDatosAudiencia["listaUnidadesGestionIgnorar"].=",".$oDatosAudiencia["idUnidadGestion"];
			
			if($oDatosAudiencia["idUnidadGestion"]!=-1)
			{
				
				if($oDatosParametros["nivelAsignacion"]>=2)
				{
					
					$fechaInicialBusqueda=strtotime($oDatosParametros["fechaMinimaAudiencia"]);
					if(isset($oDatosParametros["oDatosAudiencia"])&&($oDatosParametros["oDatosAudiencia"]["idSala"]!="")&&($oDatosParametros["oDatosAudiencia"]["idSala"]!="-1"))
					{
						$listaSalasUnidadGestion=$oDatosParametros["oDatosAudiencia"]["idSala"];
					}
					else
					{
						$consulta="SELECT salasVinculadas FROM _55_tablaDinamica WHERE idReferencia IN(".$oDatosAudiencia["idUnidadGestion"].") 
									AND salasVinculadas in (".$listaSalas.") ";
						/*if(($oDatosAudiencia["idEdificio"]==4))
						{
							$consulta.=" and salasVinculadas in(59,60,61,62,63,64,65)";
						}*/
						
						$listaSalasUnidadGestion=$con->obtenerListaValores($consulta);
						
						if($listaSalasUnidadGestion=="")
							continue;
					}
						
						
						
						
					$salaAsignada=false;
					while(!$salaAsignada)
					{
						
						$oDatosAudiencia["jueces"]=array();						
						if(isset($oDatosParametros["oDatosAudiencia"])&&($oDatosParametros["oDatosAudiencia"]["idSala"]!="")&&($oDatosParametros["oDatosAudiencia"]["idSala"]!="-1"))
						{
							$oDatosAudiencia["idSala"]=$oDatosParametros["oDatosAudiencia"]["idSala"];
							if($oDatosAudiencia["listaSalasIgnorar"]!="-1")
							{
								$arrSalasIgnorar=explode(",",$oDatosAudiencia["listaSalasIgnorar"]);
								if(existeValor($arrSalasIgnorar,$oDatosAudiencia["idSala"]))
								{
									$fechaInicialBusqueda=strtotime("+".$diasIncremento." days",strtotime(date("Y-m-d",$fechaInicialBusqueda)));	
									if(($fechaMaximaPermitida!=NULL) &&($fechaInicialBusqueda>$fechaMaximaPermitida))
									{
										return -30;
									}
									else
									{
										$oDatosAudiencia["listaSalasIgnorar"]=-1;
										continue;
									}
								}
							}
						}
						else
						{

							$oDatosParametros["criterioBalanceoSala"]=1;

							switch($oDatosParametros["criterioBalanceoSala"])//Asignacion Sala
							{
								case "1":

									$fechaFPeriodo=date("Y-m-d");
									$fechaIPeriodo=date("Y-m-d",strtotime("-7 days",strtotime($fechaFPeriodo)));									
									$oDatosAudiencia["idSala"]=obtenerSalaAsignacionNumeroHoras($listaSalasUnidadGestion,$tipoAudiencia,$fechaIPeriodo,$fechaFPeriodo,$oDatosAudiencia["listaSalasIgnorar"]);

								break;
								case "2":
									$oDatosAudiencia["idSala"]=obtenerSalaAsignacionNumeroEventos($listaSalasUnidadGestion,$tipoAudiencia,$fechaInicialPeriodo,$fechaFinalPeriodo,$oDatosAudiencia["listaSalasIgnorar"]);			
								break;
								case "3":
									$oDatosAudiencia["idSala"]=obtenerSiguienteEntidad(2,$listaSalasUnidadGestion,$tipoAudiencia,$oDatosAudiencia["listaSalasIgnorar"]);
								break;		
							}
						}					
						
						
						
						
						
						
						$nInteraciones++;
						
						$oDatosAudiencia["listaSalasIgnorar"].=",".$oDatosAudiencia["idSala"];
						if($oDatosAudiencia["idSala"]==-1)
						{
							
							$fechaInicialBusqueda=strtotime("+".$diasIncremento." days",strtotime(date("Y-m-d",$fechaInicialBusqueda)));
								
							if(($fechaMaximaPermitida!=NULL) &&($fechaInicialBusqueda>$fechaMaximaPermitida))
							{
								$salaAsignada=true;
							}
							else
							{
								$oDatosAudiencia["listaSalasIgnorar"]=-1;
								continue;
							}
						}
						else
						{
							
							if($oDatosParametros["nivelAsignacion"]>=3)
							{
								$consulta="SELECT perfilSala FROM _15_tablaDinamica WHERE id__15_tablaDinamica=".$oDatosAudiencia["idSala"];
								$idPerfilSala=$con->obtenerValor($consulta);
								$consulta="SELECT * FROM _8_tablaDinamica WHERE id__8_tablaDinamica=".$idPerfilSala;
								$fDatosPerfil=$con->obtenerPrimeraFilaAsoc($consulta);
								$intervaloTiempoEvento=0;
								$permitirExcederHoraFinal=0;
								if($fDatosPerfil)
								{
									$intervaloTiempoEvento=$fDatosPerfil["intervaloTiempo"];
									$permitirExcederHoraFinal=$oDatosParametros["esSolicitudUgente"]?1:$fDatosPerfil["permiteExceder"];
								}
								if($intervaloTiempoEvento=="")
									$intervaloTiempoEvento=0;
									
								$fechaAsignada=false;
								
								$diasBusqueda=0;
								$obtenerSiguienteFecha=true;
								$arrHorarioIgn=array();								
								
								
								while(!$fechaAsignada)
								{
									$agendaEvento=-1;
									$considerarFechaAudiencia=true;
									$obtenerSiguienteFecha=true;
									if($oDatosParametros["considerarDiaHabil"])
									{
										$cadObj='{"fecha":"'.date("Y-m-d",$fechaInicialBusqueda).'"}';
										$arrAux=array();
										$objFuncion=json_decode($cadObj);
										$esDiaHabil=removerComillasLimite(resolverExpresionCalculoPHP($oDatosParametros["funcionDiaHabil"],$objFuncion,$cache));
										$considerarFechaAudiencia=($esDiaHabil==1);
										
									}
									
									if($considerarFechaAudiencia)
									{
										
										$arrHorarioIgn=array();
										$horaAsignada=false;
										while(!$horaAsignada)
										{

											$cadObjParametrosEvento='{"fechaSolicitud":"'.$oDatosParametros["fechaSolicitud"].'","fechaMinimaAudiencia":"'.$oDatosParametros["fechaMinimaAudiencia"].
																	'","idSala":"'.$oDatosAudiencia["idSala"].'","fechaInicialAgenda":"'.date("Y-m-d H:i:s",$fechaInicialBusqueda).'","duracionAudiencia":"'.$oDatosParametros["duracionAudiencia"]
																	.'","intervaloTiempoEvento":"'.$intervaloTiempoEvento.'","fechaMaxima":"","permitirExcederHoraFinal":"'.$permitirExcederHoraFinal.
																	'","arrHorarioIgn":[],"idUnidadGestion":"'.$oDatosAudiencia["idUnidadGestion"].'","esSolicitudUgente":"'.($oDatosParametros["esSolicitudUgente"]?1:0).'"}';
															
											$oParametrosEvento=json_decode($cadObjParametrosEvento);
											$oParametrosEvento->arrHorarioIgn=$arrHorarioIgn;		
											
											
											if($fechaMaximaPermitida!=NULL)
												$oParametrosEvento->fechaMaxima=date("Y-m-d H:i:s",$fechaMaximaPermitida);	
											else
												$oParametrosEvento->fechaMaxima=NULL;
											
											$agendaEvento=obtenerFechaEventoAudienciaV2($oParametrosEvento);
											
											/*if($_SESSION["idUsr"]==1)
											{
												
												varDump($oParametrosEvento);
												return;
												
												if(($nInteraciones>10)&&($agendaEvento==-1))
												{
													
													return;
												}
											}*/
											$nInteraciones++;	
											
											if($agendaEvento!=-1)
											{						
												
												$oHorario=array();
												$oHorario["horaInicial"]=$agendaEvento["horaInicial"];
												$oHorario["horaFinal"]=$agendaEvento["horaFinal"];
												$fHorarioIgn=array();
												$fHorarioIgn[0]=$oHorario["horaInicial"];
												$fHorarioIgn[1]=$oHorario["horaFinal"];
												
												array_push($arrHorarioIgn,$fHorarioIgn);
												
												
												$oDatosAudiencia["fecha"]=$agendaEvento["fechaEvento"];
												$oDatosAudiencia["horaInicio"]=$agendaEvento["horaInicial"];
												$oDatosAudiencia["horaFin"]=$agendaEvento["horaFinal"];
												
												$juecesAsignados=true;
												$listaJuecesIgn=-1;
												
												
												if($nInteraciones>10)
												{
													$juecesAsignados=true;
													for($x=0;$x<sizeof($oDatosParametros["juecesRequeridos"]);$x++)
													{
														
														if($oDatosParametros["juecesRequeridos"][$x]["idUsuario"]=="")
															$oDatosParametros["juecesRequeridos"][$x]["idUsuario"]=-1;
													}
													
												}
												else
												{
													
													for($x=0;$x<sizeof($oDatosParametros["juecesRequeridos"]);$x++)
													{
														if($tipoMateria=="P")
															$oDatosParametros["juecesRequeridos"][$x]["idUsuario"]=asignarJuezAudiencia($oDatosAudiencia,$oDatosParametros,$oDatosParametros["juecesRequeridos"][$x]["tipoJuez"],$listaJuecesIgn,$fechaInicialPeriodo,$fechaFinalPeriodo);
														else
															$oDatosParametros["juecesRequeridos"][$x]["idUsuario"]=asignarJuezAudienciaJuzgado($oDatosAudiencia,$oDatosParametros,$oDatosParametros["juecesRequeridos"][$x]["tipoJuez"],$listaJuecesIgn,$fechaInicialPeriodo,$fechaFinalPeriodo);
														
														/*if($_SESSION["idUsr"]==1)
															varDUmp($oDatosParametros);*/
														
														if($oDatosParametros["juecesRequeridos"][$x]["idUsuario"]==-1)
														{
															
															$juecesAsignados=false;
															break;
															
														}
														else
														{
															$listaJuecesIgn.=",".$oDatosParametros["juecesRequeridos"][$x]["idUsuario"];
														}
													}
												}
												if($juecesAsignados)
												{
													foreach($oDatosParametros["juecesRequeridos"] as $j)									
													{
														array_push($oDatosAudiencia["jueces"],$j);
													}
													$obtenerSiguienteFecha=false;
													$horaAsignada=true;
													$fechaAsignada=true;
													$salaAsignada=true;
													$unidadControlAsignada=true;
													$edificioAsignado=true;
												}
											}
											else
											{
												
												
												$horaAsignada=true;
											}
											
										}
									}
									else
										$nInteraciones++;	
									if($nInteraciones>20)
											return;
									/*if($_SESSION["idUsr"]==1)
									{
										if($nInteraciones>9)
											return;
									}*/
									

								
									if($obtenerSiguienteFecha)
									{
										$arrHorarioIgn=array();
										$diasBusqueda++;
										
										if($diasBusqueda>$diasIncremento)
										{

											$fechaInicialBusqueda=strtotime("-".($diasBusqueda-1)." days",strtotime(date("Y-m-d",$fechaInicialBusqueda)));
											$fechaAsignada=true;
										}
										else
										{

											$fechaInicialBusqueda=strtotime("+1 days",strtotime(date("Y-m-d",$fechaInicialBusqueda)));	
																						
											
											if(($fechaMaximaPermitida!=NULL) &&($fechaInicialBusqueda>$fechaMaximaPermitida))
											{
												$fechaAsignada=true;
												$salaAsignada=true;
											}
										}
									}
								}
							}
							else
							{
								$salaAsignada=true;
								$unidadControlAsignada=true;
								$edificioAsignado=true;
							}
						}
					}
				}
				else
				{
					$unidadControlAsignada=true;
					$edificioAsignado=true;
				}
			}
			else
				$unidadControlAsignada=true;
		}
	}	
	
	
	if($oDatosAudiencia["idEdificio"]==-1)
		return -10;
	
	if($oDatosAudiencia["idUnidadGestion"]==-1)
		return -20;	
	
	if($oDatosAudiencia["idSala"]==-1)
		return -30;		
	
	if($oDatosAudiencia["fecha"]==-1)
		return -40;	
		
	if(sizeof($oDatosAudiencia["jueces"])==0)
		return -50;	
		
	return $oDatosAudiencia;	
}

function obtenerFechaEventoAudienciaV2($objDatos)
{
	global $con;	
	global $tipoMateria;	
	$consulta="";

	$fechaActual=date("Y-m-d");
	$fecha=date("Y-m-d",strtotime($objDatos->fechaInicialAgenda));
	
	$dia=date("N",strtotime($fecha));
	
	$consulta="SELECT  tipoDelito FROM _17_gridDelitosAtiende WHERE idReferencia=".$objDatos->idUnidadGestion;
	$tipificacion=$con->obtenerValor($consulta);

	$tipoHorario="";
	switch($tipificacion)
	{
		case "A":
		case "M":
			$horarioMaximo="20:00:00";
			$tipoHorario=determinarHorarioA($objDatos->fechaMinimaAudiencia);
		break;
		
		case "B":
			$tipoHorario=determinarHorarioB($objDatos->fechaMinimaAudiencia);
			if(($tipoHorario==3)||($tipoHorario==4))
				$tipoHorario=2;
		break;
		default:
			$tipoHorario=5;
		break;
	}
	


	if($objDatos->esSolicitudUgente)
	{
	
		$horarioMaximo="23:00:00";
	
		/*$consulta="SELECT horaInicial,horaFinal FROM _17_horario WHERE idReferencia=".$objDatos->idUnidadGestion." and  dia=".$dia;
		$consulta="SELECT '00:00:00', '23:59:59'";*/
		
		/*if($fecha!=$fechaActual)
			$consulta="SELECT '00:00:00', '23:59:59'";
		else*/
		
		if(date("Y-m-d H:i:s",strtotime($objDatos->fechaMinimaAudiencia))==date("Y-m-d H:i:s",strtotime($objDatos->fechaInicialAgenda)))
		{
			if($tipoHorario==2)
			{
				if(strtotime(date("H:i:s",strtotime($objDatos->fechaMinimaAudiencia)))<=strtotime("09:00:00"))
				{
					$objDatos->fechaInicialAgenda=date("Y-m-d 11:00:00",strtotime($objDatos->fechaInicialAgenda));	
				}
	
			}
			/*else
				if(($tipoHorario==4)||($tipoHorario==3))
				{
					
					$horarioMaximo=date("H:i:s",strtotime("-1 minute",strtotime($objDatos->fechaMinimaAudiencia)));
				}*/
			
			if($tipoHorario!=5)
			{
				if(strtotime($objDatos->fechaInicialAgenda)<strtotime(date("Y-m-d 11:00:00",strtotime($objDatos->fechaInicialAgenda))))
				{
					$objDatos->fechaInicialAgenda=date("Y-m-d 11:00:00",strtotime($objDatos->fechaInicialAgenda));	
				}
			}
			else
				{
					$horarioMaximo=date("Y-m-d 23:59:00",strtotime("+1 days",strtotime($objDatos->fechaMinimaAudiencia)));
				}
			$consulta="SELECT '".date("H:i:s",strtotime($objDatos->fechaInicialAgenda))."', '".$horarioMaximo."'";


		}
		else
		{
			$objDatos->fechaInicialAgenda=date("Y-m-d 11:00:00",strtotime($objDatos->fechaInicialAgenda));
			$consulta="SELECT '09:00:00', '".$horarioMaximo."'";
		}

	}
	else
	{
		//$consulta="SELECT '09:00:00', '13:30:00'";

		/*$horarioFinal="20:00:00";
		if(($tipoHorario==3)||($tipoHorario==4))
		{
			$horarioFinal="15:00:00";
		}
		
		$consulta="SELECT '".date("H:i:s",strtotime($objDatos->fechaInicialAgenda))."', '".$horarioFinal."'";
		if(date("Y-m-d H:i:s",strtotime($objDatos->fechaMinimaAudiencia))==date("Y-m-d H:i:s",strtotime($objDatos->fechaInicialAgenda)))
		{
			
		}
		else
		{
			$consulta="SELECT '09:00',".$horarioFinal."'";
		}*/

		$consulta="SELECT '09:00','21:00'";
		if($tipoHorario==2)
		{
			$consulta="SELECT '09:00','21:00'";
		}
	}
	switch($tipoMateria)
	{
		case "F" :
			$consulta="SELECT '09:30','16:00'";
		break;
	}
	
//$consulta="SELECT '11:00','17:00'";
	$fHorario=$con->obtenerPrimeraFila($consulta);
	
	
	
	if(!$fHorario)
		return -1;
		
	$arrHorarios=array();
	
	$regHorario["hInicial"]=$fecha." ".date("H:i:s",strtotime($fHorario[0]));
	$regHorario["hFinal"]=$fecha." ".date("H:i:s",strtotime($fHorario[1]));
	
			
	if($objDatos->esSolicitudUgente)
	{
		$regHorario["hFinal"]=date("Y-m-d H:i:s",strtotime("+1 days",strtotime($regHorario["hFinal"])));	
	}
	
	$regHorario["tiempoMinutos"]=floor(obtenerDiferenciaMinutos($regHorario["hInicial"],$regHorario["hFinal"]));
	
	
	
	array_push($arrHorarios,$regHorario);	
	
	
	$arrEventosSala=array();
	$consulta="SELECT horaInicioEvento,horaFinEvento FROM 7000_eventosAudiencia WHERE idSala=".$objDatos->idSala.
			" AND fechaEvento='".$fecha."' and situacion in(SELECT idSituacion FROM 7011_situacionEventosAudiencia 
			WHERE considerarDiponibilidad=1) ORDER BY horaInicioEvento";
	
	if($objDatos->idSala==156)
	{
		$consulta="SELECT horaInicioEvento,horaFinEvento FROM 7000_eventosAudiencia WHERE idSala in (156,3021) 
			AND fechaEvento='".$fecha."' and situacion in(SELECT idSituacion FROM 7011_situacionEventosAudiencia 
			WHERE considerarDiponibilidad=1) ORDER BY horaInicioEvento";
	}
	
	$resEvento=$con->obtenerFilas($consulta);			
	while($fEvento=mysql_fetch_row($resEvento))
	{
		array_push($arrEventosSala,$fEvento);
	}
	
	$aEventos=obtenerAudienciasProgramadasSede($objDatos->idSala,$fecha,$fecha,-1);
	foreach($aEventos as $fEvento)	
	{
		array_push($arrEventosSala,$fEvento);
	}
	
	
	
	
	
	$horaMinimaDia="";
	
	if($fecha==$fechaActual)
	{
		$horaMinimaDia=strtotime(date("H:i:s",strtotime($objDatos->fechaInicialAgenda)));
	}
	
	$arrHorariosBloquear=$objDatos->arrHorarioIgn;
	
	if(($tipoMateria=="C")&&($objDatos->idSala==70))//Eliminar Sala
	{
		if(date("w",strtotime($fecha))==4)
		{
			$fRegIncidencia[0]=$fecha." 00:00";
			$fRegIncidencia[1]=$fecha." 23:59";
			
			array_push($arrHorariosBloquear,$fRegIncidencia);
		}
	}
	
	
	$consulta="SELECT idPadre FROM _25_chkUnidadesAplica WHERE idOpcion=".$objDatos->idUnidadGestion;	
	$listaIncidencias=$con->obtenerValor($consulta);
	if($listaIncidencias=="")
		$listaIncidencias=-1;
		
	$consulta="SELECT fechaInicial,horaInicial,fechaFinal,horaFinal,id__25_tablaDinamica,tipoPeriodo FROM _25_tablaDinamica i,_25_Salas s 
				WHERE s.idReferencia=i.id__25_tablaDinamica AND s.nombreSala=".$objDatos->idSala." AND '".$fecha."'>=i.fechaInicial AND 
				'".$fecha."'<=i.fechaFinal AND i.idEstado=2 and aplicaTodasUnidades=1
				union
				SELECT fechaInicial,horaInicial,fechaFinal,horaFinal,id__25_tablaDinamica,tipoPeriodo FROM _25_tablaDinamica i,_25_Salas s 
				WHERE s.idReferencia=i.id__25_tablaDinamica AND s.nombreSala=".$objDatos->idSala." AND '".$fecha."'>=i.fechaInicial AND 
				'".$fecha."'<=i.fechaFinal AND i.idEstado=2 and aplicaTodasUnidades=0 and id__25_tablaDinamica in(".$listaIncidencias.")";
	
	$rIncidenciasSala=$con->obtenerFilas($consulta);
	while($fIncidencia=mysql_fetch_row($rIncidenciasSala))
	{
		$fRegIncidencia=array();
		
		$horaInicial="00:00:00";
		$horaFinal="23:59:59";
		
		if($fIncidencia[1]=="")
			$fIncidencia[1]=$horaInicial;
		
		if($fIncidencia[3]=="")
			$fIncidencia[3]=$horaFinal;
			
		
		
		if($fIncidencia[5]==2)
		{
			
			if($fIncidencia[0]==$fecha)
			{
				$horaInicial=$fIncidencia[1];
			}
			
			
			if($fIncidencia[2]==$fecha)
			{
				$horaFinal=$fIncidencia[3];
			}
			
		}
		else
		{
			$horaInicial=$fIncidencia[1];
			$horaFinal=$fIncidencia[3];
		}
		
		$fRegIncidencia[0]=$fecha." ".$horaInicial;
		$fRegIncidencia[1]=$fecha." ".$horaFinal;
		
		array_push($arrHorariosBloquear,$fRegIncidencia);
		
	}
	
	if(sizeof($arrHorariosBloquear)>0)
	{
		
		foreach($arrHorariosBloquear as $fEvento)	
		{
			array_push($arrEventosSala,$fEvento)	;
						
			
		}
		
		
	}
	
	
	
	usort($arrEventosSala, "ordenarPorFecha");

	foreach($arrEventosSala as $fEvento)	
	{
		$hInicioA=date("Y-m-d H:i:s",strtotime($fEvento[0]));
		$hFinA=date("Y-m-d H:i:s",strtotime($fEvento[1]));
		$arrAux=array();		
		for($pos=0;$pos<sizeof($arrHorarios);$pos++)
		{
			$horario=$arrHorarios[$pos];
			
			if(colisionaTiempo($hInicioA,$hFinA,$horario["hInicial"],$horario["hFinal"],true))
			{
				if(strtotime($hInicioA)<=strtotime($horario["hInicial"]))
				{
					
					if(strtotime($hFinA)<strtotime($horario["hFinal"]))
					{
						
						$nHorario=array();
						$nHorario["hInicial"]=date("Y-m-d H:i:s",strtotime("+0 minute",strtotime($hFinA)));
						$nHorario["hFinal"]=date("Y-m-d H:i:s",strtotime($horario["hFinal"]));
						$nHorario["tiempoMinutos"]=floor(obtenerDiferenciaMinutos($nHorario["hInicial"],$nHorario["hFinal"]));
						array_push($arrAux,$nHorario);
						
					}
						
				}
				else
				{
					$nHorario=array();
					$nHorario["hInicial"]=date("Y-m-d H:i:s",strtotime($horario["hInicial"]));
					$nHorario["hFinal"]=date("Y-m-d H:i:s",strtotime("-0 minute",strtotime($hInicioA)));
					$nHorario["tiempoMinutos"]=floor(obtenerDiferenciaMinutos($nHorario["hInicial"],$nHorario["hFinal"]));
					array_push($arrAux,$nHorario);
					
					if(strtotime($hFinA)<strtotime($horario["hFinal"]))
					{
						$nHorario=array();
						$nHorario["hInicial"]=date("Y-m-d H:i:s",strtotime("+0 minute",strtotime($hFinA)));
						$nHorario["hFinal"]=date("Y-m-d H:i:s",strtotime($horario["hFinal"]));
						$nHorario["tiempoMinutos"]=floor(obtenerDiferenciaMinutos($nHorario["hInicial"],$nHorario["hFinal"]));
						array_push($arrAux,$nHorario);
					}
					
					
				}
			}
			else
			{
				array_push($arrAux,$horario);	
			}
		}
		
		$arrHorarios=$arrAux;
		
	}

	
	/*if($_SESSION["idUsr"]==1)
	{
		

		varDump($arrEventosSala);
		varDump($arrHorarios);
		return;
	}*/
	

	$agendaEvento=array();
	$agendaEvento["fechaEvento"]=$fecha;
	
	$horaInicial="";
	$horaFinal="";
	
	$arrHorarioAux=array();
	foreach($arrHorarios as $h)
	{
		$agregar=true;
		if(strtotime($h["hInicial"])<$horaMinimaDia)	
		{
			if(strtotime($h["hFinal"])<$horaMinimaDia)
			{
				$agregar=false;
			}
			else
			{
				$h["hInicial"]=date("Y-m-d H:i:s",$horaMinimaDia);
				$h["tiempoMinutos"]=floor(obtenerDiferenciaMinutos($h["hInicial"],$h["hFinal"]));
				
				
			}
		}
		if($agregar)
			array_push($arrHorarioAux,$h);
	}
	$arrHorarios=$arrHorarioAux;
	
	$nPos=0;
	foreach($arrHorarios as $h)
	{
		$incrementoMinutos=0;
		
		if(($h["hInicial"]!=date("Y-m-d H:i:s",strtotime($fecha." ".$fHorario[0])))&&($nPos>0))
		{
			$incrementoMinutos=$objDatos->intervaloTiempoEvento;
		}
		
		if(($h["tiempoMinutos"]-$incrementoMinutos)>=$objDatos->duracionAudiencia)
		{
			$horaInicial=strtotime("+".$incrementoMinutos." minutes",strtotime($h["hInicial"]));

			if(($horaMinimaDia=="")||($horaInicial>=$horaMinimaDia))
			{
				
				break;
			}
			else
				$horaInicial="";
		}
		else
		{
			if($h["hFinal"]==date("Y-m-d H:i:s",strtotime($fecha." ".$fHorario[1])))
			{
				$horaInicial=strtotime("+".$incrementoMinutos." minutes",strtotime($h["hInicial"]));
				if(($horaMinimaDia=="")||($horaInicial>=$horaMinimaDia))
					break;	
				else
					$horaInicial="";
			}
		}
		$nPos++;
	}

	
	if($horaInicial=="")
		return -1;
	
	

	if($objDatos->fechaMaxima!=NULL)
	{
		if($horaInicial>strtotime($objDatos->fechaMaxima))	
		{
			
			return -1;	
		}
	}



	$horaFinal=strtotime("+".$objDatos->duracionAudiencia." minutes",$horaInicial);
	
	if($objDatos->permitirExcederHoraFinal==0)
	{
		if($horaFinal>strtotime($fecha." ".date("H:i:s",strtotime($fHorario[1]))))
		{
			return -1;
		}
	}
	else
	{
		if($horaInicial>strtotime($fecha." ".date("H:i:s",strtotime($fHorario[1]))))
		{

			return -1;
		}
	}
	
	
	$agendaEvento["horaInicial"]=date("Y-m-d H:i:s",$horaInicial);
	$agendaEvento["horaFinal"]=date("Y-m-d H:i:s",$horaFinal);	
	
	return $agendaEvento;	
	
}

function obtenerNoRondaAsignacionAsunto($cveMateria,$tipoRonda)
{
	global $con;
	
	$consulta="SELECT noRonda FROM 7004_seriesRondaAsignacionAsuntos WHERE cveMateria='".$cveMateria."' AND serieRonda='".$tipoRonda."'";
	$noRonda=$con->obtenerValor($consulta);
	if($noRonda=="")
	{
		$noRonda=1;
		$consulta="INSERT INTO 7004_seriesRondaAsignacionAsuntos(cveMateria,serieRonda,noRonda) VALUES('".$cveMateria."','".$tipoRonda."',1)";
		$con->ejecutarConsulta($consulta);		
	}	
	return $noRonda;	
}

function asignarJuzgadoAsignacionBalanceada($idFormulario,$idRegistro,$tipoMateria)
{
	global $con;
	$oAsignacion["tipoRonda"]=$tipoMateria;
	$ciclos=0;
	
	$idUGAAsignada=-1;
	$nRonda=obtenerNoRondaAsignacionAsunto($tipoMateria,$oAsignacion["tipoRonda"]);
	while(($ciclos<10)&&($idUGAAsignada=-1))
	{
		
		$consulta="SELECT id__17_tablaDinamica FROM _17_tablaDinamica ug,_17_gridDelitosAtiende d WHERE d.idReferencia=ug.id__17_tablaDinamica
				AND d.tipoDelito='".$tipoMateria."' and idEstado=1 ORDER BY prioridad";
		$listaUGAS=$con->obtenerListaValores($consulta);


		$arrUGAS=array();

		
		$aUGAS=explode(",",$listaUGAS);

		foreach($aUGAS as $iUGA)
		{
			
			$asignacionesRonda= obtenerAsignacionesRondaAsuntos($iUGA,$oAsignacion["tipoRonda"],$nRonda);
			/*$nAdeudos=obtenerAsignacionesPendientes($idJuez,$oAsignacion["tipoRonda"],$oDatosParametros["idUnidadGestion"],$nRonda);
			$nPagadas=obtenerAsignacionesPagadasRonda($idJuez,$oAsignacion["tipoRonda"],$oDatosParametros["idUnidadGestion"],$nRonda);*/
			$arrUGAS[$iUGA]["nAsignaciones"]=$asignacionesRonda;
			$arrUGAS[$iUGA]["nAdeudos"]=0;
			$arrUGAS[$iUGA]["nPagadas"]=0;
			
		}
		
		foreach($arrUGAS as $iUGA=>$resto)
		{
			if($resto["nAsignaciones"]==0)
			{
				return $iUGA;
			}

		}
		$nRonda++;
		$ciclos++;


	}
	
	return -1;
	
}

function obtenerAsignacionesRondaAsuntos($idUnidadGestion,$tipoRonda,$noRonda)
{
	global $con;
	$consulta="SELECT  COUNT(*) FROM 7041_asignacionUGAAsuntos WHERE idUnidadGestion=".$idUnidadGestion." AND tipoRonda='".
			$tipoRonda."' AND  situacion in(1,2,3,4,6,7) and noRonda=".$noRonda.
			" and rondaPagada is null";
	$nAsignaciones=$con->obtenerValor($consulta);
	return $nAsignaciones;
}

function remitirAsuntoJuzgado($idFormulario,$idRegistro)
{
	global $con;
	$arrParticipantes="";
	$consulta="SELECT * FROM _484_tablaDinamica WHERE id__484_tablaDinamica=".$idRegistro;
	
	$fRegistro=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$consulta="SELECT p.id__47_tablaDinamica,r.idFiguraJuridica,p.apellidoPaterno,apellidoMaterno,genero,nombre FROM 7005_relacionFigurasJuridicasSolicitud r,_47_tablaDinamica p 
				WHERE r.idActividad=".$fRegistro["idActividad"]." AND r.idParticipante=p.id__47_tablaDinamica";
	
	
	$res=$con->obtenerFilas($consulta);
	while($fParticipante=mysql_fetch_row($res))
	{
		$aParticipantesAsoc="";
		$consulta="SELECT idOpcion FROM _47_personasAsocia WHERE idPadre=".$fParticipante[0];
		$rAsociados=$con->obtenerFilas($consulta);
		while($fAsociados=mysql_fetch_row($rAsociados))
		{
			$aParticipantesAsoc.='<idParticipante>'.$fAsociados[0].'</idParticipante>';
		}
		
		$o='	<parte>
					<idParticipante>'.$fParticipante[0].'</idParticipante>
					<participacion>'.$fParticipante[1].'</participacion>
					<apPaterno>'.utf8_encode($fParticipante[2]).'</apPaterno>
					<apMaterno>'.utf8_encode($fParticipante[3]).'</apMaterno>
					<nombre>'.utf8_encode($fParticipante[5]).'</nombre>
					<genero>'.$fParticipante[4].'</genero>
					<participantesAsociados>'.$aParticipantesAsoc.'</participantesAsociados>
				</parte>';
		$arrParticipantes.=$o;
		
	}
	$xml='<?xml version="1.0" encoding="ISO-8859-1"?>
		<expediente>
			<fechaRecepcion>'.$fRegistro["fechaRecepcion"].' '.$fRegistro["horaRecepcion"].'</fechaRecepcion>
			<tipoJuicio>'.$fRegistro["tipoJuicio"].'</tipoJuicio>
			<partes>'.$arrParticipantes.'</partes>
		</expediente>';
	
	$consulta="SELECT CONCAT(ip1,'.',ip2,'.',ip3,'.',ip4),claveMateria FROM _485_tablaDinamica WHERE id__485_tablaDinamica=".$fRegistro["cmbMateria"];
	$fServidor=$con->obtenerPrimeraFila($consulta);
	

	$client = new nusoap_client("http://".$fServidor[0].":9090/webServices/wsJuzgados.php?wsdl","wsdl");
	
	$parametros=array();
	$parametros["xml"]=$xml;
	$parametros["tipoMateria"]=$fServidor[1];
	$parametros["iFormulario"]=$idFormulario;
	$parametros["iRegistro"]=$idRegistro;
	$response = $client->call("registrarAsunto", $parametros);
	
	$oResp=simplexml_load_string($response);
	
	if(((string)$oResp->resultado)==1)
	{
		$consulta="UPDATE _484_tablaDinamica SET noExpediente='".((string)$oResp->expediente)."',fechaEnvio='".date("Y-m-d H:i:s").
					"',juzgado='".((string)$oResp->juzgado)."' WHERE id__484_tablaDinamica=".$idRegistro;
		if($con->ejecutarConsulta($consulta))
		{
			
			
		}
	}
	
	
	
	
	
}

function crearCaratulaExpedienteExhorto($idFormulario,$idRegistro)
{
	global $con;
	
	$consulta="SELECT tipoExpediente FROM _478_tablaDinamica WHERE id__478_tablaDinamica=".$idRegistro;
	$tDocumento=$con->obtenerValor($consulta);
	
	$consulta="SELECT carpetaAdministrativa,tipoCarpetaAdministrativa,unidadGestion,tc.nombreTipoCarpeta,fechaCreacion,idActividad 
			FROM 7006_carpetasAdministrativas c,7020_tipoCarpetaAdministrativa tc WHERE idFormulario=".$idFormulario.
			" AND idRegistro=".$idRegistro." AND tc.idTipoCarpeta=c.tipoCarpetaAdministrativa";

	$fCarpeta=$con->obtenerPrimeraFila($consulta);
	if(!$fCarpeta)
		return true;
	$consulta="SELECT idFormulario FROM 900_formularios WHERE categoriaFormulario=1";
	$iFormularioMaterias=$con->obtenerValor($consulta);
	
	$consulta="SELECT id__17_tablaDinamica,nombreUnidad,claveOPC FROM _17_tablaDinamica WHERE  claveUnidad='".$fCarpeta[2]."'";

	$fJuzgado=$con->obtenerPrimeraFila($consulta);
	
	$consulta="SELECT tipoDelito FROM _17_gridDelitosAtiende WHERE idReferencia=".$fJuzgado[0];

	$listaMateria=$con->obtenerListaValores($consulta,"'");
	$consulta="SELECT claveOPC,materia,idCsDocs FROM _".$iFormularioMaterias."_tablaDinamica WHERE claveMateria in(".$listaMateria.")";
	$fMateria=$con->obtenerPrimeraFila($consulta);
	$idMateria=$fMateria[0];
	$tMateria=$fMateria[1];
	$idCsDocs=$fMateria[2];
	
	
	$consulta="SELECT idFormulario FROM 900_formularios WHERE categoriaFormulario=2";
	$iFormularioCarpeta=$con->obtenerValor($consulta);
	
	$idJuicio="";
	$tJuicio="";

	switch($iFormularioCarpeta)
	{
		case 478:
				$consulta="SELECT tipoJuicio FROM _478_tablaDinamica WHERE carpetaAdministrativa='".$fCarpeta[0].
							"' AND codigoInstitucion='".$fCarpeta[2]."'";
				
				$tJuicio=$con->obtenerValor($consulta);
				$consulta="SELECT tipoJuicio,id__477_tablaDinamica FROM _477_tablaDinamica WHERE id__477_tablaDinamica=".$tJuicio;
				
				$fJuicio=$con->obtenerPrimeraFila($consulta);
			
				$tJuicio=$fJuicio[0];
				$idJuicio=$fJuicio[1];
		break;
		default:
				$consulta="SELECT d.id__35_denominacionDelito,d.denominacionDelito FROM _61_tablaDinamica sd,
						_35_denominacionDelito d WHERE sd.idActividad=".($fCarpeta[5]==""?-1:$fCarpeta[5])." AND 
						d.id__35_denominacionDelito=sd.denominacionDelito";
				$fJuicio=$con->obtenerPrimeraFila($consulta);
				$tJuicio=$fJuicio[0];
				$idJuicio=$fJuicio[1];
		break;
	}
	
	$idDocumento=generarIDDocumentoEscanner($idFormulario,$idRegistro,$tDocumento==2?4:51);

	$arrCampos=array();
	$arrCampos["Documento"]=$idDocumento;
	$arrCampos["Instancia"]=6;
	$arrCampos["Repositorio"]=1;
	$arrCampos["Aplicativo"]=$idCsDocs;
	$arrCampos["Materia"]=$idMateria;
	$arrCampos["Juzgado"]=$fJuzgado[2]==""?$fJuzgado[0]:$fJuzgado[2];
	$arrCampos["Año"]=date("Y",strtotime($fCarpeta[4]));
	$arrCampos["Expediente"]=$fCarpeta[0];
	$arrCampos["Juicio"]=$tJuicio;
	
	
	generarCaratula($idFormulario,$idRegistro,2,$arrCampos,$arrCampos["Documento"]);
}

function crearCaratulaAmparo($idFormulario,$idRegistro)
{
	global $con;
	if($con->existeCampo("idCarpetaAdministrativa","_501_tablaDinamica"))
		$consulta="SELECT (SELECT carpetaAdministrativa FROM 7006_carpetasAdministrativas WHERE idCarpeta=p.idCarpetaAdministrativa)as carpetaAdministrativa,
				idCarpetaAdministrativa,fechaCreacion FROM _501_tablaDinamica p WHERE id__501_tablaDinamica=".$idRegistro;
	else
		if($con->existeCampo("idExpediente","_501_tablaDinamica"))
			$consulta="SELECT carpetaAdministrativa,idExpediente,fechaCreacion FROM _501_tablaDinamica WHERE id__501_tablaDinamica=".$idRegistro;
		else
			$consulta="SELECT carpetaAdministrativa,-1 as idExpediente,fechaCreacion FROM _501_tablaDinamica WHERE id__501_tablaDinamica=".$idRegistro;
		
	$fPromocion=$con->obtenerPrimeraFila($consulta);
	
	$consulta="SELECT carpetaAdministrativa,tipoCarpetaAdministrativa,unidadGestion,tc.nombreTipoCarpeta,fechaCreacion,idActividad 
			FROM 7006_carpetasAdministrativas c,7020_tipoCarpetaAdministrativa tc WHERE carpetaAdministrativa='".$fPromocion[0].
			"' AND idCarpeta=".$fPromocion[1]." AND tc.idTipoCarpeta=c.tipoCarpetaAdministrativa";

	$fCarpeta=$con->obtenerPrimeraFila($consulta);
	
	$consulta="SELECT idFormulario FROM 900_formularios WHERE categoriaFormulario=1";
	$iFormularioMaterias=$con->obtenerValor($consulta);
	
	$consulta="SELECT id__17_tablaDinamica,nombreUnidad,claveOPC FROM _17_tablaDinamica WHERE  claveUnidad='".$fCarpeta[2]."'";
	$fJuzgado=$con->obtenerPrimeraFila($consulta);

	
	$consulta="SELECT tipoDelito FROM _17_gridDelitosAtiende WHERE idReferencia=".$fJuzgado[0];
	
	$listaMateria=$con->obtenerListaValores($consulta,"'");
	$consulta="SELECT claveOPC,materia,idCsDocs FROM _".$iFormularioMaterias."_tablaDinamica WHERE claveMateria in(".$listaMateria.")";
	$fMateria=$con->obtenerPrimeraFila($consulta);
	$idMateria=$fMateria[0];
	$tMateria=$fMateria[1];
	$idCsDocs=$fMateria[2];
	
	
	$consulta="SELECT idFormulario FROM 900_formularios WHERE categoriaFormulario=2";
	$iFormularioCarpeta=$con->obtenerValor($consulta);
	
	$idJuicio="";
	$tJuicio="";
		
	switch($iFormularioCarpeta)
	{
		case 478:
				$consulta="SELECT tipoJuicio FROM _478_tablaDinamica WHERE carpetaAdministrativa='".$fCarpeta[0].
							"' AND codigoInstitucion='".$fCarpeta[2]."'";
				
				$tJuicio=$con->obtenerValor($consulta);
				$consulta="SELECT tipoJuicio,id__477_tablaDinamica FROM _477_tablaDinamica WHERE id__477_tablaDinamica=".$tJuicio;
				
				$fJuicio=$con->obtenerPrimeraFila($consulta);
			
				$tJuicio=$fJuicio[0];
				$idJuicio=$fJuicio[1];
		break;
		default:
				$consulta="SELECT d.id__35_denominacionDelito,d.denominacionDelito FROM _61_tablaDinamica sd,
						_35_denominacionDelito d WHERE sd.idActividad=".($fCarpeta[5]==""?-1:$fCarpeta[5])." AND 
						d.id__35_denominacionDelito=sd.denominacionDelito";
				$fJuicio=$con->obtenerPrimeraFila($consulta);
				$tJuicio=$fJuicio[0];
				$idJuicio=$fJuicio[1];
		break;
	}
	
	$idDocumento=generarIDDocumentoEscanner($idFormulario,$idRegistro,45);

	/*$arrCampos=array();
	$arrCampos["ID DOCUMENTO"]=$idDocumento;
	$arrCampos["ID APLICATIVO"]=$idCsDocs;
	$arrCampos["ID MATERIA"]=$idMateria;
	$arrCampos["MATERIA"]=$tMateria;
	$arrCampos["ID JUICIO"]=$idJuicio;
	$arrCampos["JUICIO"]=$tJuicio;
	
	$arrCampos["ID JUZGADO"]=$fJuzgado[0];
	$arrCampos["JUZGADO"]=substr($fJuzgado[1],0,50);
	
	$arrCampos["AÑO EXPEDIENTE"]=date("Y",strtotime($fCarpeta[4]));
	$arrCampos["NÚMERO EXPEDIENTE"]=$fCarpeta[0];
	$arrCampos["ASUNTO"]="REGISTRO DE PROMOCIÓN";
	$arrCampos["ID TIPO EXPEDIENTE"]=$fCarpeta[1];
	$arrCampos["TIPO EXPEDIENTE"]=$fCarpeta[1];
	$arrCampos["ID SALA"]="";
	$arrCampos["SALA"]="";
	$arrCampos["NÚMERO DE TOCA"]="";
	$arrCampos["AÑO DE TOCA"]="";
	
	$arrCampos["TIPO DE DOCUMENTO"]="PROMOCIÓN";
	$arrCampos["FECHA DE DOCUMENTO"]=$fPromocion[2];
	$arrCampos["FECHA CARGA"]="";*/
	
	$arrCampos=array();
	$arrCampos["Documento"]=$idDocumento;
	$arrCampos["Instancia"]=6;
	$arrCampos["Repositorio"]=1;
	$arrCampos["Aplicativo"]=$idCsDocs;
	$arrCampos["Materia"]=$idMateria;
	$arrCampos["Juzgado"]=$fJuzgado[2]==""?$fJuzgado[0]:$fJuzgado[2];
	$arrCampos["Año"]=date("Y",strtotime($fCarpeta[4]));
	$arrCampos["Expediente"]=$fCarpeta[0];
	$arrCampos["Juicio"]=$tJuicio;
	
	
	generarCaratula($idFormulario,$idRegistro,2,$arrCampos,$arrCampos["Documento"]);
}

function crearCaratulaApelacion($idFormulario,$idRegistro)
{
	global $con;
	if($con->existeCampo("idCarpetaAdministrativa","_497_tablaDinamica"))
		$consulta="SELECT (SELECT carpetaAdministrativa FROM 7006_carpetasAdministrativas WHERE idCarpeta=p.idCarpetaAdministrativa)as carpetaAdministrativa,
				idCarpetaAdministrativa,fechaCreacion FROM _497_tablaDinamica p WHERE id__497_tablaDinamica=".$idRegistro;
	else
		if($con->existeCampo("idExpediente","_497_tablaDinamica"))
			$consulta="SELECT carpetaAdministrativa,idExpediente,fechaCreacion FROM _497_tablaDinamica WHERE id__497_tablaDinamica=".$idRegistro;
		else
			$consulta="SELECT carpetaAdministrativa,-1 as idExpediente,fechaCreacion FROM _497_tablaDinamica WHERE id__497_tablaDinamica=".$idRegistro;
		
	$fPromocion=$con->obtenerPrimeraFila($consulta);
	
	$consulta="SELECT carpetaAdministrativa,tipoCarpetaAdministrativa,unidadGestion,tc.nombreTipoCarpeta,fechaCreacion,idActividad 
			FROM 7006_carpetasAdministrativas c,7020_tipoCarpetaAdministrativa tc WHERE carpetaAdministrativa='".$fPromocion[0].
			"' AND idCarpeta=".$fPromocion[1]." AND tc.idTipoCarpeta=c.tipoCarpetaAdministrativa";

	$fCarpeta=$con->obtenerPrimeraFila($consulta);
	
	$consulta="SELECT idFormulario FROM 900_formularios WHERE categoriaFormulario=1";
	$iFormularioMaterias=$con->obtenerValor($consulta);
	
	$consulta="SELECT id__17_tablaDinamica,nombreUnidad,claveOPC FROM _17_tablaDinamica WHERE  claveUnidad='".$fCarpeta[2]."'";
	$fJuzgado=$con->obtenerPrimeraFila($consulta);

	
	$consulta="SELECT tipoDelito FROM _17_gridDelitosAtiende WHERE idReferencia=".$fJuzgado[0];
	
	$listaMateria=$con->obtenerListaValores($consulta,"'");
	$consulta="SELECT claveOPC,materia,idCsDocs FROM _".$iFormularioMaterias."_tablaDinamica WHERE claveMateria in(".$listaMateria.")";
	$fMateria=$con->obtenerPrimeraFila($consulta);
	$idMateria=$fMateria[0];
	$tMateria=$fMateria[1];
	$idCsDocs=$fMateria[2];
	
	
	$consulta="SELECT idFormulario FROM 900_formularios WHERE categoriaFormulario=2";
	$iFormularioCarpeta=$con->obtenerValor($consulta);
	
	$idJuicio="";
	$tJuicio="";
		
	switch($iFormularioCarpeta)
	{
		case 478:
				$consulta="SELECT tipoJuicio FROM _478_tablaDinamica WHERE carpetaAdministrativa='".$fCarpeta[0].
							"' AND codigoInstitucion='".$fCarpeta[2]."'";
				
				$tJuicio=$con->obtenerValor($consulta);
				$consulta="SELECT tipoJuicio,id__477_tablaDinamica FROM _477_tablaDinamica WHERE id__477_tablaDinamica=".$tJuicio;
				
				$fJuicio=$con->obtenerPrimeraFila($consulta);
			
				$tJuicio=$fJuicio[0];
				$idJuicio=$fJuicio[1];
		break;
		default:
				$consulta="SELECT d.id__35_denominacionDelito,d.denominacionDelito FROM _61_tablaDinamica sd,
						_35_denominacionDelito d WHERE sd.idActividad=".($fCarpeta[5]==""?-1:$fCarpeta[5])." AND 
						d.id__35_denominacionDelito=sd.denominacionDelito";
				$fJuicio=$con->obtenerPrimeraFila($consulta);
				$tJuicio=$fJuicio[0];
				$idJuicio=$fJuicio[1];
		break;
	}
	
	$idDocumento=generarIDDocumentoEscanner($idFormulario,$idRegistro,46);

	/*$arrCampos=array();
	$arrCampos["ID DOCUMENTO"]=$idDocumento;
	$arrCampos["ID APLICATIVO"]=$idCsDocs;
	$arrCampos["ID MATERIA"]=$idMateria;
	$arrCampos["MATERIA"]=$tMateria;
	$arrCampos["ID JUICIO"]=$idJuicio;
	$arrCampos["JUICIO"]=$tJuicio;
	
	$arrCampos["ID JUZGADO"]=$fJuzgado[0];
	$arrCampos["JUZGADO"]=substr($fJuzgado[1],0,50);
	
	$arrCampos["AÑO EXPEDIENTE"]=date("Y",strtotime($fCarpeta[4]));
	$arrCampos["NÚMERO EXPEDIENTE"]=$fCarpeta[0];
	$arrCampos["ASUNTO"]="REGISTRO DE PROMOCIÓN";
	$arrCampos["ID TIPO EXPEDIENTE"]=$fCarpeta[1];
	$arrCampos["TIPO EXPEDIENTE"]=$fCarpeta[1];
	$arrCampos["ID SALA"]="";
	$arrCampos["SALA"]="";
	$arrCampos["NÚMERO DE TOCA"]="";
	$arrCampos["AÑO DE TOCA"]="";
	
	$arrCampos["TIPO DE DOCUMENTO"]="PROMOCIÓN";
	$arrCampos["FECHA DE DOCUMENTO"]=$fPromocion[2];
	$arrCampos["FECHA CARGA"]="";*/
	
	$arrCampos=array();
	$arrCampos["Documento"]=$idDocumento;
	$arrCampos["Instancia"]=6;
	$arrCampos["Repositorio"]=1;
	$arrCampos["Aplicativo"]=$idCsDocs;
	$arrCampos["Materia"]=$idMateria;
	$arrCampos["Juzgado"]=$fJuzgado[2]==""?$fJuzgado[0]:$fJuzgado[2];
	$arrCampos["Año"]=date("Y",strtotime($fCarpeta[4]));
	$arrCampos["Expediente"]=$fCarpeta[0];
	$arrCampos["Juicio"]=$tJuicio;
	
	
	generarCaratula($idFormulario,$idRegistro,2,$arrCampos,$arrCampos["Documento"]);
}

function crearCaratulaPromocion($idFormulario,$idRegistro)
{
	global $con;
	if($con->existeCampo("idCarpetaAdministrativa","_96_tablaDinamica"))
		$consulta="SELECT (SELECT carpetaAdministrativa FROM 7006_carpetasAdministrativas WHERE idCarpeta=p.idCarpetaAdministrativa)as carpetaAdministrativa,
				idCarpetaAdministrativa,fechaCreacion FROM _96_tablaDinamica p WHERE id__96_tablaDinamica=".$idRegistro;
	else
		if($con->existeCampo("idExpediente","_96_tablaDinamica"))
			$consulta="SELECT carpetaAdministrativa,idExpediente,fechaCreacion FROM _96_tablaDinamica WHERE id__96_tablaDinamica=".$idRegistro;
		else
			$consulta="SELECT carpetaAdministrativa,-1 as idExpediente,fechaCreacion FROM _96_tablaDinamica WHERE id__96_tablaDinamica=".$idRegistro;
		
	$fPromocion=$con->obtenerPrimeraFila($consulta);
	
	$consulta="SELECT carpetaAdministrativa,tipoCarpetaAdministrativa,unidadGestion,tc.nombreTipoCarpeta,fechaCreacion,idActividad 
			FROM 7006_carpetasAdministrativas c,7020_tipoCarpetaAdministrativa tc WHERE carpetaAdministrativa='".$fPromocion[0].
			"' AND idCarpeta=".$fPromocion[1]." AND tc.idTipoCarpeta=c.tipoCarpetaAdministrativa";

	$fCarpeta=$con->obtenerPrimeraFila($consulta);
	
	$consulta="SELECT idFormulario FROM 900_formularios WHERE categoriaFormulario=1";
	$iFormularioMaterias=$con->obtenerValor($consulta);
	
	$consulta="SELECT id__17_tablaDinamica,nombreUnidad,claveOPC FROM _17_tablaDinamica WHERE  claveUnidad='".$fCarpeta[2]."'";
	$fJuzgado=$con->obtenerPrimeraFila($consulta);

	
	$consulta="SELECT tipoDelito FROM _17_gridDelitosAtiende WHERE idReferencia=".$fJuzgado[0];
	
	$listaMateria=$con->obtenerListaValores($consulta,"'");
	$consulta="SELECT claveOPC,materia,idCsDocs FROM _".$iFormularioMaterias."_tablaDinamica WHERE claveMateria in(".$listaMateria.")";
	$fMateria=$con->obtenerPrimeraFila($consulta);
	$idMateria=$fMateria[0];
	$tMateria=$fMateria[1];
	$idCsDocs=$fMateria[2];
	
	
	$consulta="SELECT idFormulario FROM 900_formularios WHERE categoriaFormulario=2";
	$iFormularioCarpeta=$con->obtenerValor($consulta);
	
	$idJuicio="";
	$tJuicio="";
		
	switch($iFormularioCarpeta)
	{
		case 478:
				$consulta="SELECT tipoJuicio FROM _478_tablaDinamica WHERE carpetaAdministrativa='".$fCarpeta[0].
							"' AND codigoInstitucion='".$fCarpeta[2]."'";
				
				$tJuicio=$con->obtenerValor($consulta);
				$consulta="SELECT tipoJuicio,id__477_tablaDinamica FROM _477_tablaDinamica WHERE id__477_tablaDinamica=".$tJuicio;
				
				$fJuicio=$con->obtenerPrimeraFila($consulta);
			
				$tJuicio=$fJuicio[0];
				$idJuicio=$fJuicio[1];
		break;
		default:
				$consulta="SELECT d.id__35_denominacionDelito,d.denominacionDelito FROM _61_tablaDinamica sd,
						_35_denominacionDelito d WHERE sd.idActividad=".($fCarpeta[5]==""?-1:$fCarpeta[5])." AND 
						d.id__35_denominacionDelito=sd.denominacionDelito";
				$fJuicio=$con->obtenerPrimeraFila($consulta);
				$tJuicio=$fJuicio[0];
				$idJuicio=$fJuicio[1];
		break;
	}
	
	$idDocumento=generarIDDocumentoEscanner($idFormulario,$idRegistro,1);

	/*$arrCampos=array();
	$arrCampos["ID DOCUMENTO"]=$idDocumento;
	$arrCampos["ID APLICATIVO"]=$idCsDocs;
	$arrCampos["ID MATERIA"]=$idMateria;
	$arrCampos["MATERIA"]=$tMateria;
	$arrCampos["ID JUICIO"]=$idJuicio;
	$arrCampos["JUICIO"]=$tJuicio;
	
	$arrCampos["ID JUZGADO"]=$fJuzgado[0];
	$arrCampos["JUZGADO"]=substr($fJuzgado[1],0,50);
	
	$arrCampos["AÑO EXPEDIENTE"]=date("Y",strtotime($fCarpeta[4]));
	$arrCampos["NÚMERO EXPEDIENTE"]=$fCarpeta[0];
	$arrCampos["ASUNTO"]="REGISTRO DE PROMOCIÓN";
	$arrCampos["ID TIPO EXPEDIENTE"]=$fCarpeta[1];
	$arrCampos["TIPO EXPEDIENTE"]=$fCarpeta[1];
	$arrCampos["ID SALA"]="";
	$arrCampos["SALA"]="";
	$arrCampos["NÚMERO DE TOCA"]="";
	$arrCampos["AÑO DE TOCA"]="";
	
	$arrCampos["TIPO DE DOCUMENTO"]="PROMOCIÓN";
	$arrCampos["FECHA DE DOCUMENTO"]=$fPromocion[2];
	$arrCampos["FECHA CARGA"]="";*/
	
	$arrCampos=array();
	$arrCampos["Documento"]=$idDocumento;
	$arrCampos["Instancia"]=6;
	$arrCampos["Repositorio"]=1;
	$arrCampos["Aplicativo"]=$idCsDocs;
	$arrCampos["Materia"]=$idMateria;
	$arrCampos["Juzgado"]=$fJuzgado[2]==""?$fJuzgado[0]:$fJuzgado[2];
	$arrCampos["Año"]=date("Y",strtotime($fCarpeta[4]));
	$arrCampos["Expediente"]=$fCarpeta[0];
	$arrCampos["Juicio"]=$tJuicio;
	
	
	generarCaratula($idFormulario,$idRegistro,2,$arrCampos,$arrCampos["Documento"]);
}



function generarIDDocumentoEscanner($idFormulario,$idRegistro,$tipoDocumento)
{
	global $con;
	
	$query="SELECT idArchivo FROM 908_archivos a,9074_documentosRegistrosProceso r WHERE a.idArchivo=r.idDocumento
			AND r.idFormulario=".$idFormulario." AND r.idRegistro=".$idRegistro." AND a.categoriaDocumentos=".$tipoDocumento;
	$idDocumento=$con->obtenerValor($query);
	if($idDocumento!="")
	{
		return $idDocumento;
	}

	
	$x=0;
	$consulta[$x]="begin";
	$x++;
	$consulta[$x]="INSERT INTO 908_archivos(categoriaDocumentos,tipoDocumento,enBD) VALUES(".$tipoDocumento.",2,0)";
	$x++;
	$consulta[$x]="set @idDocumento:=(select last_insert_id())";
	$x++;
	$consulta[$x]="INSERT INTO 9074_documentosRegistrosProceso(idFormulario,idRegistro,idDocumento,tipoDocumento)
					VALUES(".$idFormulario.",".$idRegistro.",@idDocumento,3)";
	$x++;
	$consulta[$x]="commit";
	$x++;
	
	if($con->ejecutarBloque($consulta))
	{
		$query="select @idDocumento";
		$idDocumento=$con->obtenerValor($query);
		return $idDocumento;
	}
	return -1;
}
	
function generarCaratula($idFormulario,$idRegistro,$formato,$arrCampos,$idDocumento)
{
	global $con;
	global $baseDir;
	
	
	$query="SELECT idArchivo FROM 908_archivos a,9074_documentosRegistrosProceso r WHERE a.idArchivo=r.idDocumento
			AND r.idFormulario=".$idFormulario." AND r.idRegistro=".$idRegistro." AND a.categoriaDocumentos=53";
	$idDocumento=$con->obtenerValor($query);
	if($idDocumento!="")
	{
		return true;
	}
	
	if($idFormulario==96)
	{
		$consulta="SELECT idPromocionSICORE FROM _96_tablaDinamica WHERE id__96_tablaDinamica=".$idRegistro;
		$idPromocionSICORE=$con->obtenerValor($consulta);
		if($idPromocionSICORE!="")
			return true;
	}
	
	$pdf = new FPDI();
	$pdf->AddPage(); 
	$pdf->setSourceFile($baseDir.'/modulosEspeciales_SGJP/formatos/formatoPatch_'.$formato.'.pdf');
	$tplIdx = $pdf->importPage(1); 
	$pdf->useTemplate($tplIdx, 0, 0); 
	$pdf->SetFont('Arial','B',11); 
	$pdf->SetTextColor(0,0,0); 
	
	
	$posYInicial=150;
	$incremento=0;
	$cadQR="";
	foreach($arrCampos as $campo=>$valor)
	{
		$pdf->SetFont('Arial','B',12); 
		$pdf->SetTextColor(0,0,0); 
		$pdf->SetXY(45, 125+$incremento); 
		$pdf->Write(0, utf8_decode($campo)); 
		
		$pdf->SetFont('Arial','',12); 
		$pdf->SetTextColor(0,0,0); 
		$pdf->SetXY(81, 125+$incremento); 
		$pdf->Write(0, utf8_decode($valor)); 
		
		if($cadQR=="")
			$cadQR=($valor);
		else
			$cadQR.="|".($valor);
		$incremento+=10;
	}	
	
	$cadQR=utf8_decode($cadQR);
	
	$nombreArchivoCBB=generarNombreArchivoTemporal(2);
	$urlArchivoCBB=$baseDir."/archivosTemporalesCodigoBarras/".$nombreArchivoCBB;
	
	$cBarras=new cCodigoBarras($cadQR,"QR","",1,2,60);
	$nombreArchivoCBB=$cBarras->generarCodigoBarrasImagenArchivo();
	$urlArchivoCBB=$baseDir."/archivosTemporalesCodigoBarras/".$nombreArchivoCBB;
	
	/*$barcodeobj = new TCPDF2DBarcode(normalizarCaracteres($cadQR), 'PDF417');
	$barcodeobj->getBarcodePNGSave($urlArchivoCBB);*/
	
	$h_img = fopen($urlArchivoCBB, "rb");
	$img = fread($h_img, filesize($urlArchivoCBB));
	fclose($h_img);
	
	$pic = 'data://text/plain;base64,' . base64_encode($img);
	$info = getimagesize($pic);
	$pdf->Image($pic, 90, 67, $info[0]/2.5, $info[1]/2.5, 'png');
	
	
	$nArchivo=generarNombreArchivoTemporal();
	$pdf->Output(str_replace("/","\\",$baseDir.'/archivosTemporales/'.$nArchivo),"F"); 
	
	unlink($urlArchivoCBB);
	$idDocumentoServidor=registrarDocumentoServidor($nArchivo,"caratula_documento_".$idDocumento.".pdf");
	convertirDocumentoUsuarioDocumentoResultadoProceso($idDocumentoServidor,$idFormulario,$idRegistro,"caratula_documento_".$idDocumento.".pdf",53);
	
	
}

function obtenerStatusPublicacion()
{
	global $con;
	$arrActividades=array();
	$oStatus=array();
	
	$oStatus["valor"]="1,1.2,2,4,6";
	$oStatus["etiqueta"]="Cualquiera";
	array_push($arrActividades,$oStatus);
	
	$consulta="SELECT  numEtapa,nombreEtapa FROM 4037_etapas WHERE idProceso= 213 and numEtapa in(1,1.2,2,4,6) ORDER BY numEtapa";
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		$oStatus=array();
	
		$oStatus["valor"]=$fila[0];
		$oStatus["etiqueta"]=$fila[1];
		array_push($arrActividades,$oStatus);
	}

	return $arrActividades;
}

function registrarAcuerdoPublicacionPromocionProcesoAcuerdo($idFormulario,$idRegistro)
{
	global $con;
	
	$consulta="";
	$nomTabla="_".$idFormulario."_tablaDinamica";
	if($con->existeCampo("idExpediente",$nomTabla))
		$consulta="SELECT idExpediente FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	else
		if($con->existeCampo("idCarpetaAdministrativa",$nomTabla))
			$consulta="SELECT idCarpetaAdministrativa FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
		else
			if($con->existeCampo("idCarpeta",$nomTabla))
				$consulta="SELECT idCarpeta FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
			else
			{
				if($con->existeCampo("carpetaAdministrativa",$nomTabla))
				{
					$consulta="SELECT carpetaAdministrativa,codigoInstitucion FROM _".$idFormulario.
							"_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
					$fRegistro=$con->obtenerPrimeraFila($consulta);
					$consulta="SELECT idCarpeta FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$fRegistro[0].
							"' AND unidadGestion='".$fRegistro[1]."'";
					
					
				}
			}

	$fExpediente=$con->obtenerPrimeraFila($consulta);
	
	$consulta="select secretariaAsignada FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$secretaria=$con->obtenerValor($consulta);
	
	$consulta="SELECT carpetaAdministrativa,unidadGestion,idFormulario,idRegistro,tipoCarpetaAdministrativa FROM 7006_carpetasAdministrativas WHERE idCarpeta=".$fExpediente[0];
	$fCarpeta=$con->obtenerPrimeraFila($consulta);

	$consulta="SELECT tipoJuicio FROM _478_tablaDinamica WHERE id__478_tablaDinamica=".$fCarpeta[3];

	$tJuicio=$con->obtenerValor($consulta);
	
	
	$consulta="SELECT tm.id__485_tablaDinamica FROM _477_tablaDinamica tj,_485_tablaDinamica tm WHERE id__477_tablaDinamica=".$tJuicio."
			AND tm.claveMateria=tj.tipoMateria";
			
	$tMateria=$con->obtenerValor($consulta);

	$consulta="SELECT idRegistro FROM 7035_informacionDocumentos WHERE idFormulario=".$idFormulario." AND idReferencia=".$idRegistro;
	$idRegistroInfo=$con->obtenerValor($consulta);

	$consulta="SELECT fechaFirma,idDocumento,configuracionDocumento FROM 3000_formatosRegistrados WHERE idFormulario=-2 AND idRegistro=".$idRegistroInfo;
	$fInfoDoc=$con->obtenerPrimeraFila($consulta);
	$configuracionDocumento=$fInfoDoc[2];
	
	$consulta="SELECT COUNT(*) FROM _487_tablaDinamica WHERE idExpediente=".$fExpediente[0];
	$nAcuerdo=$con->obtenerValor($consulta);
	$nAcuerdo++;
	
	$consulta="SELECT * FROM _487_tablaDinamica WHERE iFormulario=".$idFormulario." AND iReferencia=".$idRegistro;
	$fPublicacion=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$idRegistroSolicitud="";
	if(!$fPublicacion)
	{
		$arrDocumentosReferencia=NULL;
		$arrValores=array();
		$arrValores["iFormulario"]=$idFormulario;
		$arrValores["iReferencia"]=$idRegistro;
		$arrValores["codigoInstitucion"]=$fCarpeta[1];
		$arrValores["idAcuerdo"]=$fInfoDoc[1];
		$arrValores["tipoExpediente"]=$fCarpeta[4];
		$arrValores["idExpediente"]=$fExpediente[0];
		$arrValores["carpetaAdministrativa"]=$fCarpeta[0];
		$arrValores["juzgados"]=$arrValores["codigoInstitucion"];
		$arrValores["tipoJuicio"]=$tJuicio;
		$arrValores["materia"]=$tMateria;
		$arrValores["fechaAcuerdo"]=$fInfoDoc[0];
		$arrValores["noAcuerdo"]=$nAcuerdo;
		$arrValores["secretaria"]=$secretaria;
		
		$arrDocumentosReferencia=NULL;
		
	
		
		$idRegistroSolicitud=crearInstanciaRegistroFormulario(487,-1,1,$arrValores,$arrDocumentosReferencia,-1,886);
	}
	else
	{
		
		$idRegistroSolicitud=$fPublicacion["id__487_tablaDinamica"];
		
		$consulta="UPDATE _487_tablaDinamica SET idAcuerdo=".$fInfoDoc[1]." WHERE id__487_tablaDinamica=".$idRegistroSolicitud;
		$con->ejecutarConsulta($consulta);
		
		if($fPublicacion["idEstado"]==4)
		{
			$consulta="delete from _489_tablaDinamica where idReferencia=".$idRegistroSolicitud;
			$con->ejecutarConsulta($consulta);
		
			
		}
		else
			return true;
	}
	
	if($configuracionDocumento!="")
	{
		$objConf=json_decode(bD($configuracionDocumento));
		$query=array();
		$x=0;
		$query[$x]="begin";
		$x++;
		$query[$x]="INSERT INTO _489_tablaDinamica(idReferencia,fechaCreacion,idEstado,codigoInstitucion,comentariosAdicionales,
					visibilidad,tipoResolucion,fechaResolucion,publicarEn,otroLugarPublicacion,casoEspecial)
					values(".$idRegistroSolicitud.",'".date("Y-m-d H:i:s")."',1,'".$_SESSION["codigoInstitucion"].
					"','".cv($objConf->comentarios)."',".$objConf->visibilidad.",'".$objConf->tipoResolucion.
					"','".$objConf->fechaResolucion."','".$objConf->publicarEn."','".cv($objConf->otroLugarPublicacion).
					"','".$objConf->casoEspecial."')";
		$x++;
		$query[$x]="UPDATE _487_tablaDinamica SET visibilidad='".$objConf->visibilidad."',detallesAdicionales='".cv($objConf->comentarios).
					"',fechaAcuerdo='".$objConf->fechaResolucion."' WHERE id__487_tablaDinamica=".$idRegistroSolicitud;
		$x++;
		$query[$x]="set @idRegistro:=(select last_insert_id())";
		$x++;	
		
		if($objConf->permisos1==1)
		{
			$query[$x]="INSERT INTO _489_permisos(idPadre,idOpcion) values(@idRegistro,1)";
			$x++;	
		}
		
		if($objConf->permisos2==1)
		{
			$query[$x]="INSERT INTO _489_permisos(idPadre,idOpcion) values(@idRegistro,2)";
			$x++;	
		}
		
		if($objConf->permisos3==1)
		{
			$query[$x]="INSERT INTO _489_permisos(idPadre,idOpcion) values(@idRegistro,3)";
			$x++;	
		}
		
		$query[$x]="commit";
		$x++;	
		
		if(	$con->ejecutarBloque($query))
		{

			cambiarEtapaFormulario(487,$idRegistroSolicitud,1.5,"",-1,"NULL","NULL",886);
		}
	}
	else
	{
	
		cambiarEtapaFormulario(487,$idRegistroSolicitud,1.2,"",-1,"NULL","NULL",886);
	}
	
	
	
	return true;
}

function actualizarFichaPublicacion($idFormulario,$idRegistro)
{
	global $con;
	$consulta="SELECT idReferencia,comentariosAdicionales,visibilidad,fechaResolucion FROM _489_tablaDinamica WHERE id__489_tablaDinamica=".$idRegistro;
	$fRegistro=$con->obtenerPrimeraFila($consulta);
	
	$consulta="UPDATE _487_tablaDinamica SET visibilidad=".$fRegistro[2].",detallesAdicionales='".cv($fRegistro[1])."',fechaAcuerdo='".$fRegistro[3]."' where id__487_tablaDinamica=".$fRegistro[0];
	return $con->ejecutarConsulta($consulta);
	
	
}


function enviarPublicacionAcuerdoBoletin($idFormulario,$idRegistro)
{
	global $con;
	global $arrMesLetra;
	if(@setAcuerdoSicor($idFormulario,$idRegistro,false))
		return cambiarEtapaFormulario($idFormulario,$idRegistro,2,"",-1,"NULL","NULL",886);
	
	
}


function enviarCancelacionPublicacionAcuerdoBoletin($idFormulario,$idRegistro)
{
	global $con;
	global $arrMesLetra;
	
	return cancelarAcuerdoSicor($idFormulario,$idRegistro);

	
	/*$consulta="SELECT * FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$fAcuerdo=$con->obtenerPrimeraFilaAsoc($consulta);	
	
	$consulta="SELECT idFormulario FROM 900_formularios WHERE categoriaFormulario=1";
	$idFrmMateria=$con->obtenerValor($consulta);
	
	$consulta="SELECT * FROM _".$idFrmMateria."_tablaDinamica tm WHERE id__".$idFrmMateria."_tablaDinamica=".$fAcuerdo["materia"];
	$fMateria=$con->obtenerPrimeraFilaAsoc($consulta);		
		
	
	$client = new nusoap_client("http://monitor.tsjcdmx.gob.mx/boletin/webservice/index.php?wsdl","wsdl");
	$parametros=array();
	$parametros["idSistema"]=$fMateria["idCsDocs"];
	$parametros["idGlobal"]=$fAcuerdo["idPublicacion"];
	$parametroEnvio=json_encode($parametros);
	
	$consulta="INSERT INTO 8000_bitacoraNotificacionABoletin(fechaNotificacion,documentoXML,iFormulario,iRegistro,resultado,mensaje,metodo)
				VALUES('".date("Y-m-d H:i:s")."','".bE($parametroEnvio)."',".$idFormulario.",".$idRegistro.",0,'','deleteAcuerdo')";
	$con->ejecutarConsulta($consulta);
	
	$idNotificacion=$con->obtenerUltimoID();	
	
	$response = $client->call("deleteAcuerdo", $parametros);
	
	$oResp=json_decode($response);	
	
	$numEtapaCambio=0;
	$x=0;
	$query[$x]="begin";
	$x++;
	
	if($oResp->respuesta==1)
	{
		$query[$x]="UPDATE 8000_bitacoraNotificacionABoletin SET resultado=1 WHERE idNotificacion=".$idNotificacion;
		$x++;	
	}
	else
	{
		$query[$x]="UPDATE 8000_bitacoraNotificacionABoletin SET resultado=0,mensaje='".cv($oResp->mensajeError).
					"' WHERE idNotificacion=".$idNotificacion;
		$x++;
		$numEtapaCambio=2;
	}
	
	$x=0;
	$query[$x]="commit";
	$x++;
	
	if($con->ejecutarBloque($query))
	{
		if($numEtapaCambio>0)
		{

			return cambiarEtapaFormulario($idFormulario,$idRegistro,$numEtapaCambio,"",-1,"NULL","NULL",886);
		}
	}*/
}

function obtenerURLComunicacionServidorMateria($cveMateria)
{
	global $con;
	$consulta="SELECT idFormulario FROM 900_formularios WHERE categoriaFormulario=1";
	$idFormulario=$con->obtenerValor($consulta);
	
	$consulta="SELECT ipServidor,puerto FROM _".$idFormulario."_tablaDinamica WHERE claveMateria='".$cveMateria."'";
	$fDatosServidor=$con->obtenerPrimeraFila($consulta);
	return $fDatosServidor;
	
}

function obtenerNombreJuzgadoWS($cveJuzgado,$cveMateria)
{
	global $con;
	$fDatosServidor=obtenerURLComunicacionServidorMateria($cveMateria);
	$url=$fDatosServidor[0].($fDatosServidor[1]!=""?":".$fDatosServidor[1]:"");

	$client = new nusoap_client("http://".$url."/webServices/wsInterconexionSistemasBPM.php?wsdl","wsdl");
	$parametros=array();
	$parametros["cveJuzgado"]=$cveJuzgado;
	
	$response = $client->call("obtenerDatosJuzgadoUGA", $parametros);
	
	$oJuzgado=json_decode($response);
	
	$x=0;
	$consulta[$x]="begin";
	$x++;
	
	$consulta[$x]="INSERT INTO _17_tablaDinamica(id__17_tablaDinamica,claveUnidad,nombreUnidad,tituloUnidad,claveCJF,claveOPC) 
				VALUES(".$oJuzgado->idUnidad.",'".$oJuzgado->claveUnidad."','".cv($oJuzgado->nombreUnidad)."','".cv($oJuzgado->tituloUnidad).
				"','".$oJuzgado->claveCJF."','".$oJuzgado->claveOPC."')";
	$x++;
	
	$arrDelitos=explode(",",$oJuzgado->delitosAtiende);
	foreach($arrDelitos as $d)
	{
		if($d!="")
		{
			$consulta[$x]="INSERT INTO _17_gridDelitosAtiende(idReferencia,tipoDelito) values(".$oJuzgado->idUnidad.",'".$d."')";
			$x++;
		}
	}
	
	$consulta[$x]="commit";
	$x++;
	$con->ejecutarBloque($consulta);
	return $oJuzgado->nombreUnidad;
}

function sendMensajeEnvioGmailJuzgado($arrDestinatario,$asunto,$mensaje,$emisor="",$nombreEmisor="",$arrArchivos=null,$arrCopiaOculta=null,$arrCopia=null)
{
	
	global $habilitarEnvioCorreo;
	global $mailAdministrador;
	global $nombreEmisorAdministrador;
	global $SO;
	global $urlSitio;
	
	if(!$habilitarEnvioCorreo)
		return true;
	$em=$mailAdministrador;
	
	if($arrCopiaOculta==null)
	{
		$arrCopiaOculta=array();
	}
	
	
	$oMail=array();
	$oMail[0]="verificacion_sigjp@tsjcdmx.gob.mx";
	$oMail[1]="";
	array_push($arrCopiaOculta,$oMail);
	
	
	
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
	
	$mail->From = $em;

	if($nombreEmisor!="")
		$mail->FromName=$nomEmisor;
	
	$mail->SMTPDebug = 0;
	$mail->Debugoutput = 'html';
	
	$mail->SetFrom ("notificaciones.sgjp@tsjcdmx.gob.mx","notificaciones.sgjp@tsjcdmx.gob.mx");
	
	
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
		$mail->Subject = ($asunto);
		$mail->Body    = ($mensaje);	
	}
	else
	{
		$mail->Subject = ($asunto);
		$mail->Body    = ($mensaje);
	}

	$mail->IsHTML(true);                                  
	return $mail->Send();
}

function registrarNotificacionAvisoRespuestaPromocionRecibida($idTablero,$tNotificacion,$idUsuarioDestinatario,$carpetaAdministrativa,$idRegistro)
{
	global $con;
	$arrValores=array();
	

	$nombreUsuario=obtenerNombreUsuario($idUsuarioDestinatario);
	$arrValores["fechaAsignacion"]=date("Y-m-d H:i:s");
	$arrValores["tipoNotificacion"]=$tNotificacion;
	$arrValores["usuarioRemitente"]="";
	$arrValores["idUsuarioRemitente"]=-1;
	$arrValores["usuarioDestinatario"]=$nombreUsuario;
	$arrValores["idUsuarioDestinatario"]=$idUsuarioDestinatario;
	
	$arrValores["idEstado"]="1";
	$arrValores["contenidoMensaje"]="";
	$arrValores["objConfiguracion"]='';
	$arrValores["permiteAbrirProceso"]="0";
	$arrValores["idNotificacion"]="0";
	$arrValores["numeroCarpetaAdministrativa"]=$carpetaAdministrativa;
	
	$arrValores["iFormulario"]=-7071;
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

function enviarRespuestaPromocionAcuerdo($idFormulario,$idRegistro)
{
	global $con;
	global $arrMesLetra;
	
	$consulta="SELECT * FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$fAcuerdo=$con->obtenerPrimeraFilaAsoc($consulta);
	
	if($fAcuerdo["iFormulario"]==96)
	{
		
		$consulta="SELECT idPromocionSICORE FROM _96_tablaDinamica WHERE id__96_tablaDinamica=".$fAcuerdo["iReferencia"];
		$idPromocionSICORE=$con->obtenerValor($consulta);
		if($idPromocionSICORE!="")
		{
			$consulta="UPDATE _96_tablaDinamica SET notificarSICORE=1 WHERE id__96_tablaDinamica=".$fAcuerdo["iReferencia"];
			$con->ejecutarConsulta($consulta);
			
			$consulta="SELECT nomArchivoOriginal FROM 908_archivos WHERE idArchivo=".$fAcuerdo["idAcuerdo"];
			$nomArchivoOriginal=$con->obtenerValor($consulta);
			
			$archivoOrigen=obtenerRutaDocumento($fAcuerdo["idAcuerdo"]);
			$documentoPromocion=bE(leerContenidoArchivo($archivoOrigen));
			$documentoPromocionPKCS7="";
			if(file_exists($archivoOrigen.".pkcs7"))
			{
				$documentoPromocionPKCS7=bE(leerContenidoArchivo($archivoOrigen.".pkcs7"));
			}
			
			$cadObj='{"idPromocion":"'.$idPromocionSICORE.'","nombreDocumentoPromocion":"'.$nomArchivoOriginal.
					'","documentoPromocion":"'.$documentoPromocion.'","documentoPromocionPKCS7":"'.$documentoPromocionPKCS7.'"}';	
			
			$fDatosServidor=obtenerURLComunicacionServidorMateria("SW");
			$url=$fDatosServidor[0].($fDatosServidor[1]!=""?":".$fDatosServidor[1]:"");
		
			$client = new nusoap_client("http://".$url."/webServices/wsInterconexionSistemasBPM.php?wsdl","wsdl");
			$parametros=array();
			$parametros["cadObj"]=$cadObj;
			$response = $client->call("registrarRespuestaPromocion", $parametros);

			$oResp=json_decode($response);	
			if($oResp->resultado==1)
			{
				$consulta="UPDATE _96_tablaDinamica SET notificacionRealizada=1 WHERE id__96_tablaDinamica=".$fAcuerdo["iReferencia"];
				$con->ejecutarConsulta($consulta);
			}
			
			
			
		}
	}
	return true;
}

function enviarNotificacionPublicacionAcuerdo($idFormulario,$idRegistro)
{
	global $con;
	global $arrMesLetra;
	
	$consulta="SELECT idFormulario FROM 900_formularios WHERE categoriaFormulario=5";
	$idFrmPublicacion=$con->obtenerValor($consulta);
	
	$consulta="SELECT * FROM _".$idFrmPublicacion."_tablaDinamica tm WHERE idReferencia=".$idRegistro;
	$fConfiguracion=$con->obtenerPrimeraFilaAsoc($consulta);
	if($fConfiguracion["visibilidad"]==3)
	{
		return true;
	}
	$consulta="SELECT * FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$fAcuerdo=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$consulta="SELECT idFormulario FROM 900_formularios WHERE categoriaFormulario=1";
	$idFrmMateria=$con->obtenerValor($consulta);
	
	$consulta="SELECT * FROM _".$idFrmMateria."_tablaDinamica tm WHERE id__".$idFrmMateria."_tablaDinamica=".$fAcuerdo["materia"];
	$fMateria=$con->obtenerPrimeraFilaAsoc($consulta);
	
		
	$fDatosServidor=obtenerURLComunicacionServidorMateria("SW");
	$url=$fDatosServidor[0].($fDatosServidor[1]!=""?":".$fDatosServidor[1]:"");
	
	$consulta="SELECT nomArchivoOriginal FROM 908_archivos WHERE idArchivo=".$fAcuerdo["idAcuerdo"];
	$nomArchivoOriginal=$con->obtenerValor($consulta);
	$archivoOrigen=obtenerRutaDocumento($fAcuerdo["idAcuerdo"]);
	$documento=bE(leerContenidoArchivo($archivoOrigen));
	$documentPKCS7="";
	if(file_exists($archivoOrigen.".pkcs7"))
	{
		$documentPKCS7=bE(leerContenidoArchivo($archivoOrigen.".pkcs7"));
	}
		
	$cadObj='{"idExpediente":"'.$fAcuerdo["idExpediente"].'","cveMateria":"'.$fMateria["claveMateria"].'","unidadGestion":"'.
			$fAcuerdo["juzgados"].'","contenido":"'.$documento.'","nombreDocumento":"'.$nomArchivoOriginal.
			'","contenidoPKCS7":"'.$documentPKCS7.'","idRegistroAcuerdo":"'.$idRegistro.
			'","fechaAcuerdo":"'.$fAcuerdo["fechaAcuerdo"].'"}';
	
	$client = new nusoap_client("http://".$url."/webServices/wsInterconexionSistemasBPM.php?wsdl","wsdl");
	$parametros=array();
	$parametros["cadObj"]=$cadObj;
	$response = $client->call("registarPublicacionAcuerdo", $parametros);

	$oResp=json_decode($response);	
	if($oResp->resultado==1)
	{
		$consulta="UPDATE _".$idFormulario."_tablaDinamica SET publicacionNotificada=1 WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	}
	else
	{
		$consulta="UPDATE _".$idFormulario."_tablaDinamica SET publicacionNotificada=0 WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	}
	return $con->ejecutarConsulta($consulta);
}

function notificarNORecepcionExpediente($idFormulario,$idRegistro)
{
	global $con;
	$consulta="SELECT * FROM _478_tablaDinamica WHERE id__478_tablaDinamica=".$idRegistro;
	$fRegistro=$con->obtenerPrimeraFilaAsoc($consulta);
	$client = new nusoap_client("http://10.2.5.78:4545/opc/swexp/swOPC.php?wsdl","wsdl");
	$parametros=array();
	$parametros["folioOPC"]=$fRegistro["idExpedienteOP"];
	$parametros["numero_exp"]=$fRegistro["noExpediente"];
	$parametros["anyo_exp"]=removerCerosDerecha($fRegistro["anioExpediente"]);
	
	$consulta="SELECT claveOPC FROM _17_tablaDinamica WHERE claveUnidad='".$fRegistro["codigoInstitucion"]."'";
	$parametros["juzgado"]=$con->obtenerValor($consulta);
	
	$consulta="SELECT comentarios FROM 941_bitacoraEtapasFormularios WHERE idFormulario=".$idFormulario.
			" AND idRegistro=".$idRegistro." and etapaActual=5 order by fechaCambio desc";
	$parametros["motivo"]=$con->obtenerValor($consulta);
	
	$parametros["exhorto"]=$fRegistro["tipoExpediente"]==2?1:0;
	$response = $client->call("expNoAdmitido", $parametros);
varDump($response);
return;
	$oResp=json_decode($response);	
	if($oResp->resultado==1)
	{
		$consulta="UPDATE _".$idFormulario."_tablaDinamica SET notificacionOPC=1 WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
		return $con->ejecutarConsulta($consulta);
	}
}

function obtenerResponsableSecretariaJuzgadoAsignada($idFormulario,$idRegistro,$actorDestinatario)
{
	global $con;
	$arrDestinatario=array();
	$secretaria="";
	$unidadGestion="";
	$consulta="";
	if($idFormulario!=513)
	{
		$consulta="SELECT secretariaAsignada,codigoInstitucion FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;

	}
	else
	{
		$consulta="SELECT secretariaAsignada,cA.unidadGestion  FROM _513_tablaDinamica e,7006_carpetasAdministrativas cA,_478_tablaDinamica rE 
					WHERE id__513_tablaDinamica=".$idRegistro." AND cA.idCarpeta=e.carpetaAdministrativa AND rE.id__478_tablaDinamica=cA.idRegistro";
		
	}
	
	$fRegistro=$con->obtenerPrimeraFila($consulta);
	$secretaria=$fRegistro[0];
	$unidadGestion=$fRegistro[1];
	
	$consulta="SELECT idRol FROM 8001_roles WHERE nombreGrupo='Secretaría ".$secretaria."'";
	$rol=$con->obtenerValor($consulta);
	$rol.="_0";
	
	$rolActor=obtenerTituloRol($rol);
	$consulta="SELECT ad.idUsuario FROM 801_adscripcion ad,807_usuariosVSRoles r WHERE r.idUsuario=ad.idUsuario AND 
				r.codigoRol='".$rol."' AND ad.Institucion='".$unidadGestion."'";


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

function obtenerRemitenteTarea($idFormulario,$idRegistro)
{
	global $con;
	
	global $con;
	$arrDestinatario=array();
	
	
	$consulta="SELECT idUsuarioCambio FROM 941_bitacoraEtapasFormularios WHERE idFormulario=".$idFormulario.
			" AND idRegistro=".$idRegistro." AND etapaActual in(3,3.1) ORDER BY fechaCambio DESC LIMIT 0,1";

	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		
		$nombreUsuario=obtenerNombreUsuario($fila[0])."";
		$o='{"idUsuarioDestinatario":"'.$fila[0].'","nombreUsuarioDestinatario":"'.$nombreUsuario.'"}';
		$o=json_decode($o);
		array_push($arrDestinatario,$o);
	}
	$nombreUsuario=obtenerNombreUsuario(1)."";
	$o='{"idUsuarioDestinatario":"1","nombreUsuarioDestinatario":"'.$nombreUsuario.'"}';
	$o=json_decode($o);
	array_push($arrDestinatario,$o);
	
	return $arrDestinatario;
	
}

function enviarPublicacionEdictoBoletin($idFormulario,$idRegistro)
{
	try
	{

		global $con;
		global $arrMesLetra;
		
		$consulta="SELECT idRegistro FROM 7035_informacionDocumentos WHERE idFormulario=".$idFormulario.
				" AND idReferencia=".$idRegistro." AND idFormularioProceso=521";
		
		$idRegistroDocumento=$con->obtenerValor($consulta);
		
		$consulta="SELECT cuerpoFormato FROM 3000_formatosRegistrados WHERE idFormulario=-2 AND idRegistro=".$idRegistroDocumento;
		$cuerpoEdicto=bD($con->obtenerValor($consulta));
		$arrCuerpo=explode('<contenidoedicto>',$cuerpoEdicto);
		$arrCuerpo=explode('</contenidoedicto>',$arrCuerpo[1]);
		$cuerpoEdicto=trim($arrCuerpo[0]);
		
		$consulta="SELECT codigoInstitucion,noPublicaciones,tituloEdicto,diasPublicacion FROM _513_tablaDinamica WHERE id__513_tablaDinamica=".$idRegistro;
		$fEdicto=$con->obtenerPrimeraFila($consulta);
		
		$consulta="SELECT tipoDelito FROM _17_gridDelitosAtiende d,_17_tablaDinamica u WHERE 
					u.claveUnidad='".$fEdicto[0]."' AND d.idReferencia=u.id__17_tablaDinamica";
		$listaTipoUnidad=$con->obtenerListaValores($consulta,"'");
		if($listaTipoUnidad=="")
			$listaTipoUnidad=-1;
			
		$consulta="SELECT idFormulario FROM 900_formularios WHERE categoriaFormulario=1";
		$idFrmMateria=$con->obtenerValor($consulta);
		
		$consulta="SELECT * FROM _".$idFrmMateria."_tablaDinamica tm WHERE claveMateria in(".$listaTipoUnidad.")";
		$fMateria=$con->obtenerPrimeraFilaAsoc($consulta);
		
		$fAcuerdo["fechaAcuerdo"]=date("Y-m-d");	
		$client = new nusoap_client("http://monitor.tsjcdmx.gob.mx/boletin/webservice/index.php?wsdl","wsdl");
		$parametros=array();
		$parametros["idSistema"]=$fMateria["idCsDocs"];
		$parametros["idEdicto"]=$idRegistro;
		$parametros["edicto"]=$cuerpoEdicto;
		$parametros["fechaEdicto"]=$fAcuerdo["fechaAcuerdo"];//;"EDICTO DEL ".date("d",strtotime($fAcuerdo["fechaAcuerdo"]))." DE ".mb_strtoupper($arrMesLetra[((date("m",strtotime($fAcuerdo["fechaAcuerdo"])))*1)-1])." DEL ".date("Y",strtotime($fAcuerdo["fechaAcuerdo"]));
		$parametros["noPublicaciones"]=$fEdicto[1];
		$parametros["periodoPublicacion"]=$fEdicto[3];
		
		$parametroEnvio=json_encode($parametros);
		
		$consulta="INSERT INTO 8000_bitacoraNotificacionABoletin(fechaNotificacion,documentoXML,iFormulario,iRegistro,resultado,mensaje,metodo)
					VALUES('".date("Y-m-d H:i:s")."','".bE($parametroEnvio)."',".$idFormulario.",".$idRegistro.",0,'','setEdicto')";
		$con->ejecutarConsulta($consulta);
		
		$idNotificacion=$con->obtenerUltimoID();
		
		$response = $client->call("setEdicto", $parametros);
		$oResp=json_decode($response);	
		
		$numEtapaCambio=0;
		$x=0;
		$query[$x]="begin";
		$x++;
		
		if($oResp->respuesta==1)
		{
			$query[$x]="UPDATE _513_tablaDinamica SET folioBoletin=".$oResp->idGlobal.",fechaPosiblePublicacion='".$oResp->fechaPosiblePublicacion.
					"',numeroPosiblePublicacion=".$oResp->numeroPosiblePublicacion." WHERE id__513_tablaDinamica=".$idRegistro;
			$x++;
			$query[$x]="UPDATE 8000_bitacoraNotificacionABoletin SET resultado=1 WHERE idNotificacion=".$idNotificacion;
			$x++;
			
		}
		else
		{
			$query[$x]="UPDATE 8000_bitacoraNotificacionABoletin SET resultado=0,mensaje='".cv($oResp->mensajeError).
						"' WHERE idNotificacion=".$idRegistro;
			$x++;
			
		}
		
		$x=0;
		$query[$x]="commit";
		$x++;
	
		if($con->ejecutarBloque($query))
		{
			return cambiarEtapaFormulario($idFormulario,$idRegistro,5,"",-1,"NULL","NULL",964);
		}
	}
	catch(Exception $e)
	{
		cambiarEtapaFormulario($idFormulario,$idRegistro,6,"",-1,"NULL","NULL",964);
		$consulta="UPDATE 8000_bitacoraNotificacionABoletin SET resultado=0,mensaje='".cv(utf8_encode($e->getMessage())).
				"' WHERE idNotificacion=".$idRegistro;
		return $con->ejecutarConsulta($consulta);
	}
}


function asignarSecretariaAsunto($idFormulario,$idRegistro)
{
	global $con;
	
	$actorCambio="";
	$tipoAsignacion="";
	$seriaAsignacion="";
	$etapaCambio="";
	$secretaria="";
	$campoExpediente="";
	switch($idFormulario)
	{
		
		case 497://Apelacion
			$actorCambio=895;
			$etapaCambio=2;
			$campoExpediente="idCarpetaAdministrativa";
		break;
		case 501://Amparo
			$actorCambio=905;
			$etapaCambio=2;
			$campoExpediente="idCarpetaAdministrativa";
		break;
		case 96://Promocion
			$actorCambio=913;
			$etapaCambio=2;
			$campoExpediente="idCarpetaAdministrativa";
		break;
		case 478://NUevo expediente-Exhorto
			$actorCambio=928;
			$etapaCambio=7;
			$consulta="SELECT tipoExpediente FROM _478_tablaDinamica WHERE id__478_tablaDinamica=".$idRegistro;
			$tExpediente=$con->obtenerValor($consulta);
			if($tExpediente==2)
			{
				return;
			}
		case 480://Acuerdo resolucion
		
			$etapaCambio=0;
			$campoExpediente="idExpediente";
		break;
		case 532:
			$etapaCambio=0;
			$campoExpediente="idCarpetaAdministrativa";
			
		break;
	}
	
	if($idFormulario==478)
	{
		$consulta="SELECT secretariaAsignada FROM _478_tablaDinamica where id__478_tablaDinamica=".$idRegistro;
		$secretaria=$con->obtenerValor($consulta);	
		
	}
	else
	{
		$consulta="SELECT ".$campoExpediente." FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;	
		$expediente=$con->obtenerValor($consulta);
		$consulta="SELECT c.secretariaAsignada FROM _478_tablaDinamica rE,7006_carpetasAdministrativas c WHERE
				 c.idCarpeta=".$expediente." AND rE.id__478_tablaDinamica=c.idRegistro";
		
		$secretaria=$con->obtenerValor($consulta);	
	}
	
	$consulta="UPDATE _".$idFormulario."_tablaDinamica SET secretariaAsignada='".$secretaria."' WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	if($con->ejecutarConsulta($consulta) && ($etapaCambio!=0))
	{
		cambiarEtapaFormulario($idFormulario,$idRegistro,$etapaCambio,"",-1,"NULL","NULL",$actorCambio);
	}
	
}

function mostrarSeccionEdicionDocumentoJuzgado($idFormulario,$idRegistro,$idFormularioEvaluacion,$actor)
{
	global $con;	
	
	$arrUsuarioPermitidos["158_0"]=1;	//Secretaría (Juzgados)
	$arrUsuarioPermitidos["153_0"]=1;	//Secretaria A
	$arrUsuarioPermitidos["155_0"]=1;	//Secretaría B
	$arrUsuarioPermitidos["156_0"]=1;	//Secretaría C
	$arrUsuarioPermitidos["56_0"]=1;	//Juez
	$arrUsuarioPermitidos["97_0"]=1;	//Resp. Digitalización
	$arrUsuarioPermitidos["159_0"]=1;	//Responsable conciliación
	$arrUsuarioPermitidos["-100_0"]=1;	//Usuario logueado
	$arrUsuarioPermitidos["163_0"]=1;	//Auxiliar Secretaria A
	$arrUsuarioPermitidos["164_0"]=1;	//Auxiliar Secretaria B
	$arrUsuarioPermitidos["165_0"]=1;	//Auxiliar Secretaria C
	$consulta="SELECT actor FROM 944_actoresProcesoEtapa WHERE idActorProcesoEtapa=".$actor;
	$rol=$con->obtenerValor($consulta);
	if(!isset($arrUsuarioPermitidos[$rol]))
	{
		return 0;
	}
	
	$consulta="SELECT documentoBloqueado FROM 7035_informacionDocumentos i,3000_formatosRegistrados f WHERE i.idFormulario=".$idFormulario." AND i.idReferencia=".$idRegistro." AND 
				i.idFormularioProceso=".$idFormularioEvaluacion." AND f.idFormulario=-2 AND f.idRegistro=i.idRegistro AND f.idFormularioProceso=i.idFormularioProceso";
	$documentoBloqueado=$con->obtenerValor($consulta);	
	if($documentoBloqueado==1)
		return 0;
	return 1;
	
}

function designarRutaNuevoExpediente($idFormulario,$idRegistro)
{
	global $con;
	$numEtapa="";
	$consulta="SELECT tipoExpediente FROM _478_tablaDinamica WHERE id__478_tablaDinamica=".$idRegistro;
	$tExpediente=$con->obtenerValor($consulta);
	if($tExpediente==2)
	{
		//asignarSecretariaExhorto($idFormulario,$idRegistro);
		$numEtapa=1.6;
	}
	else
		$numEtapa=1.5;
	
	cambiarEtapaFormulario($idFormulario,$idRegistro,$numEtapa,"",-1,"NULL","NULL",921);
	
}

function asignarSecretariaExhorto($idFormulario,$idRegistro)
{
	global $con;
	
	$tipoAsignacion=5;
	$seriaAsignacion="NEXP";
	
	
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
	

	$listaSecretarias="0,1";
	$arrConfiguracion["tipoAsignacion"]=$tipoAsignacion;
	$arrConfiguracion["serieRonda"]=$seriaAsignacion;
	$arrConfiguracion["universoAsignacion"]=$listaSecretarias;
	$arrConfiguracion["idObjetoReferencia"]=-1;
	$arrConfiguracion["considerarDeudasMismaRonda"]=false;
	$arrConfiguracion["limitePagoRonda"]=0;
	$arrConfiguracion["escribirAsignacion"]=true;
	$arrConfiguracion["idFormulario"]=$idFormulario;
	$arrConfiguracion["idRegistro"]=$idRegistro;
	$resultado= obtenerSiguienteAsignacionObjeto($arrConfiguracion,true);
	
	$secretaria=chr(65+$resultado["idUnidad"]);
	
	$consulta="UPDATE _".$idFormulario."_tablaDinamica SET secretariaAsignada='".$secretaria."' WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;

	return $con->ejecutarConsulta($consulta);
	
	
}

function registrarAccionNuevoExpediente($idFormulario,$idRegistro)
{
	global $con;
	
	
	$consulta="SELECT  dictamenFinal,idReferencia FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$fDictamen=$con->obtenerPrimeraFila($consulta);
	
	$consulta="select tipoExpediente from _478_tablaDinamica where id__478_tablaDinamica=".$fDictamen[1];
	$tExpediente=$con->obtenerValor($consulta);
	$accion=$fDictamen[0];
	
	$consulta="UPDATE _478_tablaDinamica SET tipoAccionRealizada=".$accion." WHERE id__478_tablaDinamica=".$fDictamen[1];
	if ($con->ejecutarConsulta($consulta))
	{
		if($tExpediente!=2)
		{
			setJuicioSicor(478,$fDictamen[1]);
		}
		else
			return true;
	}
	
	
}

function registrarBilleteDeposito($idFormulario,$idRegistro)
{
	global $con;
	$idActividad=generarIDActividad($idFormulario,$idRegistro);	
	$consulta="SELECT * FROM _96_tablaDinamica WHERE id__96_tablaDinamica=".$idRegistro;

	$fRegistro=$con->obtenerPrimeraFilaAsoc($consulta);
	
	
	if($fRegistro["tipoPromociones"]!=5)
	{
		return true;
	}
	
	$arrDocumentosReferencia=array();
	$arrValores=array();
	$arrValores["iFormulario"]=$idFormulario;
	$arrValores["iRegistro"]=$idRegistro;
	$arrValores["codigoInstitucion"]=$fRegistro["codigoInstitucion"];
	$arrValores["idCarpetaAdministrativa"]=$fRegistro["idCarpetaAdministrativa"];
	$arrValores["noBillete"]=$fRegistro["noBillete"];
	$arrValores["fechaRecepcion"]=$fRegistro["fechaRecepcion"];
	$arrValores["horaRecepcion"]=$fRegistro["horaRecepcion"];
	$arrValores["promovente"]=$fRegistro["usuarioPromovente"];
	$arrValores["idActividad"]=$idActividad;
	
	$consulta="SELECT idDocumento FROM 9074_documentosRegistrosProceso WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro;
	$res=$con->obtenerFilas($consulta);
	while($fDocumento=mysql_fetch_row($res))
	{
		array_push($arrDocumentosReferencia,$fDocumento[0]);	
	}
	
	
	
	$idRegistroSolicitud=crearInstanciaRegistroFormulario(509,-1,1.5,$arrValores,$arrDocumentosReferencia,-1,937);
	
	
	
	
}

function esEncargadoApelacionSimilar($actor,$numEtapa)
{
	global $con;
	

	$consulta="SELECT actor FROM 944_actoresProcesoEtapa WHERE idActorProcesoEtapa=".$actor;
	$rol=$con->obtenerValor($consulta);
	
	$arrRolesSimilares=array();
	$arrRolesSimilares["1_0"]="Root";
	$arrRolesSimilares["161_0"]="Encargado de apelación";
	$arrRolesSimilares["158_0"]="Secretaria Juzgado";
	$arrRolesSimilares["153_0"]="Secretario A";
	$arrRolesSimilares["155_0"]="Secretario B";
	if(isset($arrRolesSimilares[$rol])&&($numEtapa==6))
	{
		return 1;
	}
	return 0;
	
}

function seleccionRutaFirmaExpediente($idFormulario,$idRegistro)
{
	global $con;
	
	$consulta="SELECT codigoInstitucion FROM _478_tablaDinamica WHERE id__478_tablaDinamica=".$idRegistro;
	$codigoInstitucion=$con->obtenerValor($consulta);
	
	
	$consulta="SELECT figuraFirmanteAcuerdo FROM _512_tablaDinamica WHERE codigoInstitucion='".$codigoInstitucion."'";
	$figuraFirmanteAcuerdo=$con->obtenerValor($consulta);
	
	if($figuraFirmanteAcuerdo==1)
	{
		cambiarEtapaFormulario($idFormulario,$idRegistro,5,"",-1,"NULL","NULL",924);
	}
	else
	{
		asignarSecretariaAsuntoNoCambiaStatus($idFormulario,$idRegistro);
		cambiarEtapaFormulario($idFormulario,$idRegistro,5.7,"",-1,"NULL","NULL",924);
	}
	
}


function asignarSecretariaAsuntoNoCambiaStatus($idFormulario,$idRegistro)
{
	global $con;
	
	$actorCambio="";
	$tipoAsignacion="";
	$seriaAsignacion="";
	$etapaCambio="";
	
	switch($idFormulario)
	{
		
		case 497:
			$actorCambio=895;
			$tipoAsignacion=2;
			$seriaAsignacion="APE";
			$etapaCambio=2;
		break;
		case 501:
			$actorCambio=905;
			$tipoAsignacion=3;
			$seriaAsignacion="AMP";
			$etapaCambio=2;
		break;
		case 96:
			$actorCambio=913;
			$tipoAsignacion=4;
			$seriaAsignacion="PRO";
			$etapaCambio=2;
		break;
		case 478:
			$actorCambio=928;
			$tipoAsignacion=5;
			$seriaAsignacion="NEXP";
			$etapaCambio=7;
			$consulta="SELECT tipoExpediente FROM _478_tablaDinamica WHERE id__478_tablaDinamica=".$idRegistro;
			$tExpediente=$con->obtenerValor($consulta);
			if($tExpediente==2)
			{
				return;
			}
			
			
		break;
	}
	
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
	

	$listaSecretarias="0,1";
	$arrConfiguracion["tipoAsignacion"]=$tipoAsignacion;
	$arrConfiguracion["serieRonda"]=$seriaAsignacion;
	$arrConfiguracion["universoAsignacion"]=$listaSecretarias;
	$arrConfiguracion["idObjetoReferencia"]=-1;
	$arrConfiguracion["considerarDeudasMismaRonda"]=false;
	$arrConfiguracion["limitePagoRonda"]=0;
	$arrConfiguracion["escribirAsignacion"]=true;
	$arrConfiguracion["idFormulario"]=$idFormulario;
	$arrConfiguracion["idRegistro"]=$idRegistro;

	$resultado= obtenerSiguienteAsignacionObjeto($arrConfiguracion,true);
	$secretaria=chr(65+$resultado["idUnidad"]);
	
	$consulta="UPDATE _".$idFormulario."_tablaDinamica SET secretariaAsignada='".$secretaria."' WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;

	if($con->ejecutarConsulta($consulta))
	{
		
	}
	
}

function registrarPublicacionBoletin($param)
{
	global $con;
	$objParam=json_decode(bD($param));
	
	$consulta="SELECT idFormulario,idRegistro,fechaFirma,idDocumento FROM 3000_formatosRegistrados WHERE idRegistroFormato=".$objParam->idRegistroFormato;
	
	$fDocumento=$con->obtenerPrimeraFila($consulta);
	$idFormulario=$fDocumento[0];
	$idRegistro=$fDocumento[1];
	
	switch($idFormulario)
	{
		case -2:
			$consulta="SELECT * FROM 7035_informacionDocumentos WHERE idRegistro=".$idRegistro;
			$fDocumentoInfo=$con->obtenerPrimeraFila($consulta);
			
			$idFormulario=$fDocumentoInfo[15];
			$idRegistro=$fDocumentoInfo[16];
		break;
	}
	

	$nomTabla="_".$idFormulario."_tablaDinamica";
	if($con->existeCampo("idExpediente",$nomTabla))
		$consulta="SELECT idExpediente FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	else
		if($con->existeCampo("idCarpetaAdministrativa",$nomTabla))
			$consulta="SELECT idCarpetaAdministrativa FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
		else
			if($con->existeCampo("idCarpeta",$nomTabla))
				$consulta="SELECT idCarpeta FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
			else
			{
				if($con->existeCampo("carpetaAdministrativa",$nomTabla))
				{
					$consulta="SELECT carpetaAdministrativa,codigoInstitucion FROM _".$idFormulario.
							"_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
					$fRegistro=$con->obtenerPrimeraFila($consulta);
					$consulta="SELECT idCarpeta FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$fRegistro[0].
							"' AND unidadGestion='".$fRegistro[1]."'";
					
					
				}
			}

	$fExpediente=$con->obtenerPrimeraFila($consulta);
	

	$consulta="SELECT carpetaAdministrativa,unidadGestion,idFormulario,idRegistro,secretariaAsignada,tipoCarpetaAdministrativa FROM 7006_carpetasAdministrativas WHERE idCarpeta=".$fExpediente[0];
	
	$fCarpeta=$con->obtenerPrimeraFila($consulta);
	
	$consulta="SELECT tipoJuicio FROM _478_tablaDinamica WHERE id__478_tablaDinamica=".$fCarpeta[3];

	$tJuicio=$con->obtenerValor($consulta);
	
	
	$consulta="SELECT tm.id__485_tablaDinamica FROM _477_tablaDinamica tj,_485_tablaDinamica tm WHERE id__477_tablaDinamica=".$tJuicio."
			AND tm.claveMateria=tj.tipoMateria";
	
	$tMateria=$con->obtenerValor($consulta);
	
	$consulta="SELECT idRegistro FROM 7035_informacionDocumentos WHERE idFormulario=".$idFormulario." AND idReferencia=".$idRegistro;
	$idRegistroInfo=$con->obtenerValor($consulta);

	
	
	$consulta="SELECT max(noAcuerdo) FROM _487_tablaDinamica WHERE idExpediente=".$fExpediente[0];

	$nAcuerdo=$con->obtenerValor($consulta);
	if($nAcuerdo=="")
		$nAcuerdo=0;
	$nAcuerdo++;

	$idRegistroSolicitud="";
	$consulta="SELECT * FROM _487_tablaDinamica WHERE iFormulario=".$idFormulario." AND iReferencia=".$idRegistro;
	$fPublicacion=$con->obtenerPrimeraFilaAsoc($consulta);
	if(!$fPublicacion)
	{
		$arrDocumentosReferencia=NULL;
		$arrValores=array();
		$arrValores["iFormulario"]=$idFormulario;
		$arrValores["iReferencia"]=$idRegistro;
		$arrValores["codigoInstitucion"]=$fCarpeta[1];
		$arrValores["idAcuerdo"]=$fDocumento[3];
		$arrValores["idExpediente"]=$fExpediente[0];
		$arrValores["tipoExpediente"]=$fCarpeta[5];
		$arrValores["carpetaAdministrativa"]=$fCarpeta[0];
		$arrValores["juzgados"]=$arrValores["codigoInstitucion"];
		
		$arrValores["tipoJuicio"]=$tJuicio;
		$arrValores["materia"]=$tMateria;
		$arrValores["fechaAcuerdo"]=$objParam->fechaResolucion;
		$arrValores["noAcuerdo"]=$nAcuerdo;
		$arrValores["secretaria"]=$fCarpeta[4];
		$arrValores["visibilidad"]=$objParam->visibilidad;
		$arrValores["detallesAdicionales"]=urldecode($objParam->comentarios);
	
	
		$arrDocumentosReferencia=NULL;
		
		$idRegistroSolicitud=crearInstanciaRegistroFormulario(487,-1,1,$arrValores,$arrDocumentosReferencia,-1,886);
		
		
	}
	else
	{
		$idRegistroSolicitud=$fPublicacion["id__487_tablaDinamica"];
		if($fPublicacion["idEstado"]==4)
		{
			$x=0;
			$query=array();
			$query[$x]="begin";
			$x++;
			$query[$x]="UPDATE _487_tablaDinamica SET fechaAcuerdo='".$objParam->fechaResolucion."',visibilidad=".$objParam->visibilidad.
						",detallesAdicionales='".cv(urldecode($objParam->comentarios))."' WHERE id__487_tablaDinamica=".$idRegistroSolicitud;
			$x++;
			$query[$x]="delete from _489_tablaDinamica where idReferencia=".$idRegistroSolicitud;
			$x++;
			$query[$x]="commit";
			$x++;
			eB($query);
			
			
		}
		else
			return true;
	}
	
	$consulta="INSERT INTO _489_tablaDinamica(idReferencia,fechaCreacion,responsable,idEstado,codigoInstitucion,comentariosAdicionales,visibilidad,
					tipoResolucion,fechaResolucion,publicarEn,otroLugarPublicacion,casoEspecial) 
				VALUES(".$idRegistroSolicitud.",'".date("Y-m-d H:i:s")."',".$_SESSION["idUsr"].",1,'".
				$_SESSION["codigoInstitucion"]."','".cv(urldecode($objParam->comentarios))."',".$objParam->visibilidad.
				",'".cv($objParam->tipoResolucion)."','".cv($objParam->fechaResolucion)."','".cv($objParam->publicarEn).
				"','".cv($objParam->otroLugarPublicacion)."','".$objParam->casoEspecial."')";
	
	if($con->ejecutarConsulta($consulta))
	{
		$idConfiguracion=$con->obtenerUltimoID();
		if($objParam->permisos1==1)
		{
			$consulta="INSERT INTO _489_permisos(idPadre,idOpcion) VALUES(".$idConfiguracion.",1)";
			$con->ejecutarConsulta($consulta);
		}
		
		if($objParam->permisos2==1)
		{
			$consulta="INSERT INTO _489_permisos(idPadre,idOpcion) VALUES(".$idConfiguracion.",2)";
			$con->ejecutarConsulta($consulta);
		}
		
		if($objParam->permisos3==1)
		{
			$consulta="INSERT INTO _489_permisos(idPadre,idOpcion) VALUES(".$idConfiguracion.",3)";
			$con->ejecutarConsulta($consulta);
		}
		cambiarEtapaFormulario(487,$idRegistroSolicitud,1.5,"",-1,"NULL","NULL",886);
	}
	
	
	
	return true;
	
}

function obtenerRemitenteTareaEnvio($idFormulario,$idRegistro)
{
	global $con;
	$arrDestinatario=array();
	
	$consulta="SELECT * FROM 941_bitacoraEtapasFormularios WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro.
			"  ORDER BY fechaCambio DESC LIMIT 0,1";
	$fEtapaActual=$con->obtenerPrimeraFilaAsoc($consulta);
	
	
	$consulta="SELECT idUsuarioCambio FROM 941_bitacoraEtapasFormularios WHERE idFormulario=".$idFormulario.
			" AND idRegistro=".$idRegistro." AND etapaActual in(".$fEtapaActual["etapaAnterior"].
			") ORDER BY fechaCambio DESC LIMIT 0,1";

	
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		
		$nombreUsuario=obtenerNombreUsuario($fila[0])."";
		$o='{"idUsuarioDestinatario":"'.$fila[0].'","nombreUsuarioDestinatario":"'.$nombreUsuario.'"}';
		$o=json_decode($o);
		array_push($arrDestinatario,$o);
	}
	$nombreUsuario=obtenerNombreUsuario(1)."";
	$o='{"idUsuarioDestinatario":"1","nombreUsuarioDestinatario":"'.$nombreUsuario.'"}';
	$o=json_decode($o);
	array_push($arrDestinatario,$o);
	
	return $arrDestinatario;
	
}

function registrarCuerpoBaseEdicto($idFormulario,$idRegistro)
{
	global $con;
	$query="SELECT idCarpetaAdministrativa FROM _513_tablaDinamica WHERE id__513_tablaDinamica=".$idRegistro;
	$carpetaAdministrativa=$con->obtenerValor($query);
	
	$query="SELECT txtPlantillaDocumento FROM _10_tablaDinamica WHERE id__10_tablaDinamica=500";
	$plantilla=$con->obtenerValor($query);
	
	$query="SELECT COUNT(*) FROM 7035_informacionDocumentos WHERE idFormulario=".$idFormulario.
			" AND idReferencia=".$idRegistro." AND idFormularioProceso=521";
	$nReg=$con->obtenerValor($query);
	if($nReg>0)
		return true;
	$consulta=array();
	$x=0;
	$consulta[$x]="begin";
	$x++;
	$consulta[$x]="INSERT INTO 7035_informacionDocumentos(fechaCreacion,idResponsableCreacion,tipoDocumento,tituloDocumento,
				modificaSituacionCarpeta,carpetaAdministrativa,situacionDocumento,perfilValidacion,idFormulario,idReferencia,idFormularioProceso)
				VALUES('".date("Y-m-d H:i:s")."',".$_SESSION["idUsr"].",500,'Edicto',0,'".$carpetaAdministrativa."',1,0,".
				$idFormulario.",".$idRegistro.",521)";
	$x++;
	$consulta[$x]="set @idRegistro:=(select last_insert_id())";
	$x++;
	$consulta[$x]="INSERT INTO 3000_formatosRegistrados(fechaRegistro,idResponsableRegistro,tipoFormato,cuerpoFormato,idFormulario,
					idRegistro,idReferencia,firmado,formatoPDF,documentoBloqueado,idFormularioProceso,situacionActual,idPerfilEvaluacion)
					values('".date("Y-m-d H:i:s")."',".$_SESSION["idUsr"].",500,'".bE($plantilla)."',-2,@idRegistro,@idRegistro,0,'',0,521,1,-1)";
	$x++;
	
	$consulta[$x]="commit";
	$x++;
	
	return $con->ejecutarBloque($consulta);
}

function registrarTocaAlzadaCivil($idFormulario,$idRegistro)
{
	global $con;
	
	$consulta="SELECT COUNT(*) FROM _522_tablaDinamica WHERE iFormulario=".$idFormulario." AND iRegistro=".$idRegistro;
	$nReg=$con->obtenerValor($consulta);
	if($nReg>0)
		return true;
	
	$fechaEnvio=date("Y-m-d H:i:s");
	$consulta="SELECT * FROM _497_tablaDinamica WHERE id__497_tablaDinamica=".$idRegistro;
	$fAmparo=$con->obtenerPrimeraFilaAsoc($consulta);
	$consulta="SELECT carpetaAdministrativa FROM 7006_carpetasAdministrativas WHERE idCarpeta=".$fAmparo["idCarpetaAdministrativa"];
	$carpetaAdministrativa=$con->obtenerValor($consulta);
	$consulta="SELECT oficioSala,id__499_tablaDinamica FROM _499_tablaDinamica WHERE idReferencia=".$idRegistro;
	$fEnvio=$con->obtenerPrimeraFilaAsoc($consulta);
	$arrResultado=obtenerSiguienteSalaCivilsignacion($idFormulario,$idRegistro);
	$cveTribunal=$arrResultado["idUnidad"];
	
	$arrValores=array();
	$arrValores["carpetaJudicialApelacion"]=$fAmparo["idCarpetaAdministrativa"];
	$arrValores["noApelacion"]=$fAmparo["noApelacion"];
	$arrValores["fechaActoAdmite"]=$fAmparo["fechaActoAdmite"];
	$arrValores["tipoApelacion"]=$fAmparo["tipoApelacion"];
	$arrValores["resolucionImpugnada"]=$fAmparo["resolucionImpugnada"];
	$arrValores["iFormulario"]=$idFormulario;
	$arrValores["iRegistro"]=$idRegistro;
	$arrValores["codigoInstitucion"]=$cveTribunal;
	$arrDocumentosReferencia=array();
	
	$consulta="SELECT idDocumento FROM 9074_documentosRegistrosProceso WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro;
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		array_push($arrDocumentosReferencia,$fila[0]);
	}
			
	$idRegistroSolicitud=crearInstanciaRegistroFormulario(522,-1,1,$arrValores,$arrDocumentosReferencia,-1,974);
	convertirDocumentoUsuarioDocumentoResultadoProceso($fEnvio["oficioSala"],522,$idRegistroSolicitud,"Oficio_de_envio_a_sala",14);
	registrarDocumentoCarpetaAdministrativa($carpetaAdministrativa,$fEnvio["oficioSala"],$idFormulario,$idRegistro,$fAmparo["idCarpetaAdministrativa"]);
	$x=0;
	$query=array();
	$query[$x]="begin";
	$x++;
	
	$consulta="SELECT ".$idRegistroSolicitud.",fechaCreacion,responsable,idEstado,codigoUnidad,codigoInstitucion,codigo,tipoAccion,
				fechaRecepcion,horaRecepcion,promovente,comentariosAdicionales,id__524_tablaDinamica
				FROM _524_tablaDinamica WHERE idReferencia=".$idRegistro." AND idProcesoPadre=216";
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		$query[$x]="INSERT INTO _524_tablaDinamica(idReferencia,fechaCreacion,responsable,idEstado,codigoUnidad,codigoInstitucion,
				codigo,tipoAccion,fechaRecepcion,horaRecepcion,promovente,comentariosAdicionales,idProcesoPadre)
				values(".$fila[0].",'".date("Y-m-d H:i:s")."',".$fila[2].",".$fila[3].",'".$fila[4]."','".$fila[5]."','".$fila[6].
				"',".$fila[7].",'".$fila[8]."','".$fila[9]."',".$fila[10].",'".cv($fila[11])."',221)";
		$x++;
		
		$query[$x]="set @iContestacion:=(select last_insert_id())";
		$x++;
		
		$query[$x]="INSERT INTO 9074_documentosRegistrosProceso(idFormulario,idRegistro,idDocumento,tipoDocumento)
					SELECT idFormulario,@iContestacion,idDocumento,tipoDocumento FROM 9074_documentosRegistrosProceso 
					WHERE idFormulario=524 AND idRegistro=".$fila[12];
		$x++;
		$consulta="SELECT idDocumento,idFormulario,idRegistro FROM 9074_documentosRegistrosProceso 
					WHERE idFormulario=524 AND idRegistro=".$fila[12];
		$rDocumentos=$con->obtenerFilas($consulta);
		while($fDocumentos=mysql_fetch_row($rDocumentos))
		{
			registrarDocumentoCarpetaAdministrativa($carpetaAdministrativa,$fDocumentos[0],$fDocumentos[1],$fDocumentos[2],$fAmparo["idCarpetaAdministrativa"]);
		}
	}
	
	$query[$x]="UPDATE _499_tablaDinamica SET fechaEnvioSala='".$fechaEnvio."' WHERE id__499_tablaDinamica=".$fEnvio["id__499_tablaDinamica"];
	$x++;
	$query[$x]="commit";
	$x++;
	
	if($con->ejecutarBloque($query))
	{
		cambiarEtapaFormulario(522,$idRegistroSolicitud,1.5,"",-1,"NULL","NULL",974);
		
		$consulta="SELECT codigoInstitucion,carpetaAdministrativa,idCarpeta FROM _522_tablaDinamica WHERE id__522_tablaDinamica=".$idRegistroSolicitud;
		$fApelacion=$con->obtenerPrimeraFila($consulta);
		$consulta="UPDATE _497_tablaDinamica SET fechaEnvioSala='".date("Y-m-d H:i:s")."',noToca='".$fApelacion[1]."',sala='".$fApelacion[0].
				"' WHERE  id__497_tablaDinamica=".$idRegistro;
		return $con->ejecutarConsulta($consulta);
		
		
		
	}
	
	
	
}

function generarFolioCarpetaTocaAlzadaCivil($idFormulario,$idRegistro)
{
	global $con;
	
	$anio=date("Y");
	$query="SELECT codigoInstitucion,carpetaAdministrativa,carpetaJudicialApelacion from _522_tablaDinamica WHERE id__522_tablaDinamica=".$idRegistro;
	$fRegistro=$con->obtenerPrimeraFila($query);
	
	$query="SELECT idActividad FROM 7006_carpetasAdministrativas WHERE idCarpeta=".$fRegistro[2];
	$idActividad=$con->obtenerValor($query);
	if($fRegistro[1]!="")
	{
		cambiarEtapaFormulario(522,$idRegistro,2,"",-1,"NULL","NULL",974);
		return true;
	}
	
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
					"','".$cvAdscripcion."',7,".$idActividad.",8,'','',-1,'".$cvAdscripcion."','[".$fRegistro[2]."]')";
	$x++;
	
	$consulta[$x]="set @idCarpeta:=(select  last_insert_id())";
	$x++;
	
	$consulta[$x]="UPDATE _522_tablaDinamica SET idCarpeta=@idCarpeta,carpetaAdministrativa='".$carpetaAdministrativa."' WHERE id__522_tablaDinamica=".$idRegistro;
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
		$query="SELECT id__524_tablaDinamica FROM _524_tablaDinamica WHERE idProcesoPadre=221 AND idReferencia=".$idRegistro;
		$resAdhesion=$con->obtenerFilas($query);
		while($fAdhesion=mysql_fetch_row($resAdhesion))
		{
			$query="SELECT idDocumento FROM 9074_documentosRegistrosProceso WHERE idFormulario=524 AND idRegistro=".$fAdhesion[0];
			$rDocumentos=$con->obtenerFilas($query);
			while($fDocumento=mysql_fetch_row($rDocumentos))
			{
				registrarDocumentoCarpetaAdministrativa($carpetaAdministrativa,$fDocumento[0],524,$fDocumento[0],$idCarpeta);	
			}
		}
		
		cambiarEtapaFormulario(522,$idRegistro,2,"",-1,"NULL","NULL",974);
	}
	
	return false;
	
}

function obtenerSiguienteSalaCivilsignacion($idFormulario,$idRegistro)
{
	global $con;
	
	
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
	
	$consulta="SELECT idReferencia FROM _17_gridDelitosAtiende WHERE tipoDelito='MCT-S2I'";
	$listaSalas=$con->obtenerListaValores($consulta);
	$arrConfiguracion["tipoAsignacion"]=1;
	$arrConfiguracion["serieRonda"]="TOCA";
	$arrConfiguracion["universoAsignacion"]=$listaSalas;
	$arrConfiguracion["idObjetoReferencia"]=-1;
	$arrConfiguracion["considerarDeudasMismaRonda"]=true;
	$arrConfiguracion["limitePagoRonda"]=0;
	$arrConfiguracion["escribirAsignacion"]=true;
	$arrConfiguracion["idFormulario"]=$idFormulario;
	$arrConfiguracion["idRegistro"]=$idRegistro;
	return obtenerSiguienteAsignacionObjeto($arrConfiguracion);
}

function registrarResultadoApelacionCivil($idFormulario,$idRegistro)
{
	global $con;
	$consulta="SELECT iRegistro FROM _522_tablaDinamica WHERE id__522_tablaDinamica=".$idRegistro;
	$iReferencia=$con->obtenerValor($consulta);
	
	$consulta="SELECT documentoSala FROM _523_tablaDinamica WHERE idReferencia=".$idRegistro;
	$documentoSala=$con->obtenerValor($consulta);
	
	$consulta="INSERT INTO _500_tablaDinamica(idReferencia,fechaCreacion,responsable,codigoInstitucion,codigo,fechaRespuesta,respuestaSala,documentoSala,comentariosAdicionales)
				SELECT ".$iReferencia.",fechaCreacion,responsable,codigoInstitucion,codigo,fechaResolucion,respuestaSala,documentoSala,comentariosAdicionales 
				FROM _523_tablaDinamica WHERE idReferencia=".$idRegistro;
	if($con->ejecutarConsulta($consulta))
	{
		convertirDocumentoUsuarioDocumentoResultadoProceso($documentoSala,522,$idRegistro,"Respuesta_sala",2);
		convertirDocumentoUsuarioDocumentoResultadoProceso($documentoSala,497,$iReferencia,"Respuesta_sala",2);
		cambiarEtapaFormulario(497,$iReferencia,8,"",-1,"NULL","NULL",975);
	}
				
}

function obtenerResponsablePublicacionSecretaria($idFormulario,$idRegistro,$actorDestinatario)
{
	global $con;
	global $tipoMateria;
	
	$consulta="SELECT idExpediente,iFormulario FROM _487_tablaDinamica WHERE id__487_tablaDinamica=".$idRegistro;
	$fDPublicacion=$con->obtenerPrimeraFila($consulta);
	
	$idCarpeta=$fDPublicacion[0];
	
	$consulta="SELECT c.secretariaAsignada,c.unidadGestion,re.tipoExpediente FROM _478_tablaDinamica rE,7006_carpetasAdministrativas c WHERE
				 c.idCarpeta=".$idCarpeta." AND rE.id__478_tablaDinamica=c.idRegistro";
	$fDatosExpediente=$con->obtenerPrimeraFila($consulta);
	$secretaria=$fDatosExpediente[0];

	
	$consulta="SELECT concat(idRol,'_0') as rol FROM 8001_roles WHERE nombreGrupo like '%Secretaría ".$secretaria."%'";
	
	if(($fDPublicacion[1]==478)&&($fDatosExpediente[2]==1))
	{
		$consulta="SELECT concat(idRol,'_0') as rol FROM 8001_roles WHERE idRol=159";
	}
	else
	{
		if($fDPublicacion[1]==532)
		{
			$consulta="SELECT concat(idRol,'_0') as rol FROM 8001_roles WHERE idRol=166";
		}
	}

	$roles=$con->obtenerListaValores($consulta,"'");

	$consulta="SELECT idUsuario FROM 807_usuariosVSRoles WHERE codigoRol IN(".$roles.")";

	$listaUsuarios=$con->obtenerListaValores($consulta);
	if($listaUsuarios=="")
		$listaUsuarios=-1;
	


	$rolActor=obtenerTituloRol($actorDestinatario);
	
	$arrDestinatario=array();
	$consulta="SELECT u.idUsuario FROM 807_usuariosVSRoles u,801_adscripcion a WHERE codigoRol='154_0' AND 
				u.idUsuario IN(".$listaUsuarios.") and u.idUsuario=a.idUsuario and a.Institucion='".$fDatosExpediente[1]."'";


	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		
		$nombreUsuario=obtenerNombreUsuario($fila[0])." (".$rolActor.")";
		$o='{"idUsuarioDestinatario":"'.$fila[0].'","nombreUsuarioDestinatario":"'.$nombreUsuario.'"}';
		$o=json_decode($o);
		array_push($arrDestinatario,$o);
	}
	

	return $arrDestinatario;
}

function aplicarIncumplimientoFianza($idFormulario,$idRegistro)
{
	global $con;
	
	$consulta="SELECT fianza FROM _531_tablaDinamica WHERE id__531_tablaDinamica=".$idRegistro;
	$fianza=$con->obtenerValor($consulta);
	
	cambiarEtapaFormulario(509,$fianza,4,"",-1,"NULL","NULL",936);

}

function validarDocumentoParaValidacion($idFormulario,$idRegistro)
{
	global $con;
	
	$comp="<br><span style='color:#F00'><b>*</b></span> Debe ingresar primero el documento que desea enviar a validaci&oacute;n";
	
	$consulta="SELECT * FROM 7035_informacionDocumentos i WHERE i.idFormulario=".$idFormulario." AND i.idReferencia=".$idRegistro;
	$fDocumento=$con->obtenerPrimeraFila($consulta);	
	if($fDocumento)
	{
		$comp="";
	}
		
	return $comp;
}

function mostrarSeccionEdicionDocumentoMultas($idFormulario,$idRegistro,$idFormularioEvaluacion,$actor)
{
	global $con;

	$mostrarSeccion=mostrarSeccionEdicionDocumentoJuzgado($idFormulario,$idRegistro,$idFormularioEvaluacion,$actor);

	if($mostrarSeccion==0)
		return 0;
	$consulta="SELECT * FROM 7035_informacionDocumentos i WHERE i.idFormulario=".$idFormulario." AND i.idReferencia=".$idRegistro;
	$fDocumento=$con->obtenerPrimeraFila($consulta);	
	if(!$fDocumento)
	{
		$consulta="SELECT idEstado FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
		$idEstado=$con->obtenerValor($consulta);
		if($idEstado>1)
			return 0;
	}
	return 1;

}

function registrarLibroArchivoJudicial($idFormulario,$idRegistro)
{
	global $con;
	$registrar=false;
	$consulta="SELECT * FROM _546_tablaDinamica WHERE id__546_tablaDinamica=".$idRegistro;
	$fRegistro=$con->obtenerPrimeraFilaAsoc($consulta);
	$tipoLibro="";
	if($fRegistro["tipoAccion"]==2)
	{
		$tipoLibro=17;
		if($fRegistro["idEstado"]==3)
			$registrar=true;
	}
	else
	{
		if($fRegistro["envioDestruccion"]==1)
			$tipoLibro=18;
		else
			$tipoLibro=16;
		if($fRegistro["idEstado"]==2)
			$registrar=true;	
	}
	if($registrar)
		registrarProcesoLibroGobierno($idFormulario,$idRegistro,$tipoLibro);
	
}


function mostrarSeccionEdicionDocumentoArchivoJudicial($idFormulario,$idRegistro,$idFormularioEvaluacion,$actor)
{
	global $con;
	

	$mostrarSeccion=mostrarSeccionEdicionDocumentoJuzgado($idFormulario,$idRegistro,$idFormularioEvaluacion,$actor);

	if($mostrarSeccion==0)
		return 0;
	$consulta="SELECT * FROM 7035_informacionDocumentos i WHERE i.idFormulario=".$idFormulario." AND i.idReferencia=".$idRegistro;
	$fDocumento=$con->obtenerPrimeraFila($consulta);	
	if(!$fDocumento)
	{
		$consulta="SELECT idEstado FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
		$idEstado=$con->obtenerValor($consulta);
		if($idEstado>1)
			return 0;
	}
	return 1;

}


function registrarCambioStatusCarpetaArchivoJudicial($idFormulario,$idRegistro)
{
	global $con;
	$etapaActual="";
	$consulta="SELECT idCarpetaAdministrativa,idEstado,tipoAccion,envioDestruccion FROM _546_tablaDinamica WHERE id__546_tablaDinamica=".$idRegistro;
	$fCarpeta=$con->obtenerPrimeraFila($consulta);

	$idCarpetaAdministrativa=$fCarpeta[0];
	
	$consulta="SELECT carpetaAdministrativa FROM 7006_carpetasAdministrativas WHERE idCarpeta=".$idCarpetaAdministrativa;
	$carpeta=$con->obtenerValor($consulta);
	if($fCarpeta[1]==2)
	{
		
		if($fCarpeta[2]==1)
		{
			if($fCarpeta[3]==1)
			{
				$etapaActual=101;
			}
			else
			{
				$etapaActual=100;
			}
		}
		else
		{
			cambiarEtapaFormulario($idFormulario,$idRegistro,2.1,"",-1,"NULL","NULL",1013);
		}
	}
	else
	{
		if($fCarpeta[1]==3)
			$etapaActual=1;
	}

	if($etapaActual!="")
		registrarCambioSituacionCarpeta($carpeta,$etapaActual,$idFormulario,$idRegistro,-1,"",$idCarpetaAdministrativa);
	
}

function obtenerDocumentoAsociado($idFormulario,$idRegistro)
{
	global $con;
	$consulta="SELECT COUNT(*) FROM 9074_documentosRegistrosProceso WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro;
	$nReg=$con->obtenerValor($consulta);
	return $nReg;
}

function existeDocumentoAdjuntoRegistrado($idFormulario,$idRegistro)
{
	
	return obtenerDocumentoAsociado($idFormulario,$idRegistro)>0?1:0;
}

function existeDocumentoModuloDocumentosRegistrado($idFormulario,$idRegistro)
{
	global $con;
	$consulta="SELECT * FROM 7035_informacionDocumentos i WHERE i.idFormulario=".$idFormulario." AND i.idReferencia=".$idRegistro;
	$fDocumento=$con->obtenerPrimeraFila($consulta);
	if($fDocumento)
		return 1;
	return 0;
}

function mostrarEnvioDocumentoAdjunto($idFormulario,$idRegistro)
{
	return existeDocumentoAdjuntoRegistrado($idFormulario,$idRegistro);
	
}

function mostrarEnvioDocumentoRegistrado($idFormulario,$idRegistro)
{
	return existeDocumentoModuloDocumentosRegistrado($idFormulario,$idRegistro);
	
}

function obtenerStatusDefaultPublicacion()
{
	return '1,1.2,2,4,6';
}

function obtenerSecretarias()
{
	global $con;
	$arrActividades=array();
	
	$consulta="SELECT count(*) FROM _556_secretariaAsociada s,_556_tablaDinamica t WHERE s.idPadre=t.id__556_tablaDinamica
				AND t.rol IN(".$_SESSION["idRol"].") and idOpcion='A'";
	
	$secretariaA=$con->obtenerValor($consulta);
	
	$consulta="SELECT count(*) FROM _556_secretariaAsociada s,_556_tablaDinamica t WHERE s.idPadre=t.id__556_tablaDinamica
				AND t.rol IN(".$_SESSION["idRol"].") and idOpcion='B'";
	$secretariaB=$con->obtenerValor($consulta);
	
	if(($secretariaA=="1")&&($secretariaB=="1"))
	{
		$oStatus=array();
		
		$oStatus["valor"]='"A","B"';
		$oStatus["etiqueta"]="Cualquiera";
		array_push($arrActividades,$oStatus);
		
		$oStatus=array();
		$oStatus["valor"]='"A"';
		$oStatus["etiqueta"]="A";
		array_push($arrActividades,$oStatus);
		$oStatus=array();
		$oStatus["valor"]='"B"';
		$oStatus["etiqueta"]="B";
		array_push($arrActividades,$oStatus);
	}
	else
	{
		if($secretariaA=="1")
		{
			$oStatus=array();
			$oStatus["valor"]='"A"';
			$oStatus["etiqueta"]="A";
			array_push($arrActividades,$oStatus);
		}
		else
		{
			if($secretariaB=="1")
			{
				$oStatus=array();
				$oStatus["valor"]='"B"';
				$oStatus["etiqueta"]="B";
				array_push($arrActividades,$oStatus);
			}
		}
	}
	
	return $arrActividades;
}

function obtenerStatusDefaultSecretaria()
{
	global $con;
	$consulta="SELECT count(*) FROM _556_secretariaAsociada s,_556_tablaDinamica t WHERE s.idPadre=t.id__556_tablaDinamica
				AND t.rol IN(".$_SESSION["idRol"].") and idOpcion='A'";
	$secretariaA=$con->obtenerValor($consulta);
	
	$consulta="SELECT count(*) FROM _556_secretariaAsociada s,_556_tablaDinamica t WHERE s.idPadre=t.id__556_tablaDinamica
				AND t.rol IN(".$_SESSION["idRol"].") and idOpcion='B'";
	$secretariaB=$con->obtenerValor($consulta);
	
	if(($secretariaA=="1")&&($secretariaB=="1"))
	{
		return '\"A\",\"B\"';
	}
	else
	{
		if($secretariaA=="1")
		{
			return '\"A\"';
		}
		else
		{
			if($secretariaB=="1")
				return '\"B\"';
		}
	}
}


function guardarCarpetasAdministrativaRegistroCivilFamiliar($idFormulario,$idRegistro)
{
	global $con;
	$consulta="SELECT noExpediente,anioExpediente FROM _478_tablaDinamica WHERE id__478_tablaDinamica=".$idRegistro;
	$fRegistro=$con->obtenerPrimeraFila($consulta);
	$carpetaAdministrativa=str_pad($fRegistro[0],4,"0",STR_PAD_LEFT)."/".intval($fRegistro[1]);
	

	$consulta="UPDATE _478_tablaDinamica SET carpetaAdministrativa='".$carpetaAdministrativa."'  WHERE id__478_tablaDinamica=".$idRegistro;

	return $con->ejecutarConsulta($consulta);
	
}


function mostrarSeccionEdicionDocumentoPromocionCivilFamiliar($idFormulario,$idRegistro,$actor)
{
	global $con;
	global $tipoMateria;

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
	
	switch($tipoMateria)
	{
		case "C":
			$arrActores[865]=1;
			$arrActores[866]=1;
			$arrActores[867]=1;
			$arrActores[868]=1;
			$arrActores[869]=1;
		break;
		case "F":
			
			$arrActores[864]=1;
			$arrActores[865]=1;
			$arrActores[866]=1;
			$arrActores[867]=1;
			$arrActores[868]=1;
			
		break;	
	}
	
	
	
	if(isset($arrActores[$actor]))
		return 1;
	return 0;
	
	
}

function enviarRespuestaPromocionSICORECivilFamiliar($idFormulario,$idRegistro)
{
	try
	{
		global $con;
		global $arrMesLetra;
	
		$consulta="SELECT idRegistro FROM 7035_informacionDocumentos WHERE idFormulario=".$idFormulario.
				" AND idReferencia=".$idRegistro;
	
		$iRegistro=$con->obtenerValor($consulta);	
		if($iRegistro!="")
		{
			$consulta="SELECT * FROM 3000_formatosRegistrados WHERE idFormulario=-2 AND idRegistro=".$iRegistro;
			
			$fRegistroDocumento=$con->obtenerPrimeraFilaAsoc($consulta);
			if(!$fRegistroDocumento)
			{
				return true;
			}
		}
		else	
			return true;
		
		$consulta="SELECT idPromocionSICORE FROM _96_tablaDinamica WHERE id__96_tablaDinamica=".$idRegistro;
		$idPromocionSICORE=$con->obtenerValor($consulta);
		if($idPromocionSICORE!="")
		{
			$consulta="UPDATE _96_tablaDinamica SET notificarSICORE=1,notificacionRealizada=0 WHERE id__96_tablaDinamica=".$idRegistro;
			$con->ejecutarConsulta($consulta);
			
			if($fRegistroDocumento["idDocumentoAdjunto"]!="")
				$fRegistroDocumento["idDocumento"]=$fRegistroDocumento["idDocumentoAdjunto"];
				
			if($fRegistroDocumento["idDocumento"]=="")
				return true;	
				
			$consulta="SELECT nomArchivoOriginal FROM 908_archivos WHERE idArchivo=".$fRegistroDocumento["idDocumento"];
			$nomArchivoOriginal=$con->obtenerValor($consulta);
			
			$archivoOrigen=obtenerRutaDocumento($fRegistroDocumento["idDocumento"]);
			$documentoPromocion=bE(leerContenidoArchivo($archivoOrigen));
			$documentoPromocionPKCS7="";
			if(file_exists($archivoOrigen.".pkcs7"))
			{
				$documentoPromocionPKCS7=bE(leerContenidoArchivo($archivoOrigen.".pkcs7"));
			}
			
			$cadObj='{"idPromocion":"'.$idPromocionSICORE.'","nombreDocumentoPromocion":"'.$nomArchivoOriginal.
					'","documentoPromocion":"'.$documentoPromocion.'","documentoPromocionPKCS7":"'.$documentoPromocionPKCS7.'"}';	

			$fDatosServidor=obtenerURLComunicacionServidorMateria("SW");
			$url=$fDatosServidor[0].($fDatosServidor[1]!=""?":".$fDatosServidor[1]:"");
		
			$client = new nusoap_client("http://".$url."/webServices/wsInterconexionSistemasBPM.php?wsdl","wsdl");
			$parametros=array();
			$parametros["cadObj"]=$cadObj;
			$response = $client->call("registrarRespuestaPromocion", $parametros);

			$oResp=json_decode($response);	

			if($oResp->resultado==1)
			{
				$consulta="UPDATE _96_tablaDinamica SET notificacionRealizada=1 WHERE id__96_tablaDinamica=".$idRegistro;
				$con->ejecutarConsulta($consulta);
			}
		}
	}
	catch(Exception $e)
	{
		//echo $e->getMessage();	
	}
	return true;
}


function generarFolioCarpetaExpedienteJuzgadoPenal($idFormulario,$idRegistro)
{
	global $con;
	
	$anio=date("Y");
	$query="SELECT idActividad,codigoInstitucion,carpetaAdministrativa,noExpediente,anioExpediente FROM _486_tablaDinamica WHERE id__486_tablaDinamica=".$idRegistro;
	$fRegistro=$con->obtenerPrimeraFila($query);
	
	$carpetaAdministrativa=$fRegistro[2];
	if(($fRegistro[2]=="N/E")||($fRegistro[2]==""))
	{
		$carpetaAdministrativa=$fRegistro[3]."/".parteEntera($fRegistro[4]);
	}
	
	$tipoExpediente=1;
	$idActividad=$fRegistro[0];
	$carpetaInvestigacion="";
	$query="SELECT claveFolioCarpetas,claveUnidad,id__17_tablaDinamica FROM _17_tablaDinamica WHERE claveUnidad='".$fRegistro[1]."'";
	
	$fRegistroUnidad=$con->obtenerPrimeraFila($query);
	$cvAdscripcion=$fRegistroUnidad[1];
	$idUnidadGestion=$fRegistroUnidad[2];
	
	$query="SELECT usuarioJuez FROM _26_tablaDinamica WHERE idReferencia=".$idUnidadGestion;
	$idJuezTitular=$con->obtenerValor($query);

	if($idJuezTitular=="")
		$idJuezTitular=-1;

	
	if(existeCarpetaAdministrativa($carpetaAdministrativa,$cvAdscripcion))
	{
		$query="update _".$idFormulario."_tablaDinamica set carpetaAdministrativa='".$carpetaAdministrativa."' where id__".$idFormulario."_tablaDinamica=".$idRegistro;
		return $con->ejecutarConsulta($query);
	}
		
	$x=0;
	$consulta[$x]="begin";
	$x++;
	$consulta[$x]="INSERT INTO 7006_carpetasAdministrativas(carpetaAdministrativa,fechaCreacion,responsableCreacion,idFormulario,
					idRegistro,unidadGestion,etapaProcesalActual,idActividad,tipoCarpetaAdministrativa,carpetaInvestigacion,
					llaveCarpetaInvestigacion,idJuezTitular,secretariaAsignada) 
					VALUES('".$carpetaAdministrativa."','".date("Y-m-d H:i:s")."',".$_SESSION["idUsr"].",".$idFormulario.",'".$idRegistro."','".$cvAdscripcion."',1,".$idActividad.",".$tipoExpediente.",(SELECT UPPER('".$carpetaInvestigacion."')),'".
					cv(generarLlaveCarpetaInvestigacion($carpetaInvestigacion))."','".$idJuezTitular."','')";
	$x++;
	$consulta[$x]="update _".$idFormulario."_tablaDinamica set carpetaAdministrativa='".$carpetaAdministrativa."' where id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$x++;
	$consulta[$x]="set @idCarpeta:=(select  last_insert_id())";
	$x++;
	$consulta[$x]="commit";


	if($con->ejecutarBloque($consulta))
	{

		$query="select @idCarpeta";
		$idCarpeta=$con->obtenerValor($query);
		$query="SELECT idDocumento FROM 9074_documentosRegistrosProceso WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro." and tipoDocumento<>3";
		$rDocumentos=$con->obtenerFilas($query);
		while($fDocumento=mysql_fetch_row($rDocumentos))
		{
			registrarDocumentoCarpetaAdministrativa($carpetaAdministrativa,$fDocumento[0],$idFormulario,$idRegistro,$idCarpeta);	
		}

		
		
	}
	
	return false;
	
}


function enviarNotificacionMailConfirmacion($idEvento)
{
	global $con;
	$idRegistroBitacora=registrarBitacoraNotificacionSIAJOP($idEvento);
	$idWS=99;
	try
	{
		$consulta="SELECT idCentroGestion FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$idEvento;
		$idJuzgado=$con->obtenerValor($consulta);
		$arrDestinatarios=array();
		
		$consulta="SELECT mail FROM _362_tablaDinamica m,_362_chkTipoNotificacion c WHERE m.idReferencia=".$idJuzgado." AND  c.idPadre=m.id__362_tablaDinamica AND
				c.idOpcion=4";
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			array_push($arrDestinatarios,$fila[0]);
		}
		$datosAudiencia=formatearEventoAudienciaRendererPenalTradicional($idEvento);
		$arrParam["datosAudiencia"]=$datosAudiencia;
		foreach($arrDestinatarios as $destinatario)
		{
			$arrParam["destinatario"]=$destinatario;
			if(!enviarMensajeEnvio(12,$arrParam,"sendMensajeEnvioGmailJuzgado"))
			{
				@actualizarBitacoraNotificacionSIAJOP($idRegistroBitacora,0,'',0,'',$idWS);
				return;
			}
		}
		@actualizarBitacoraNotificacionSIAJOP($idRegistroBitacora,1,'',0,'',$idWS);
	}
	catch(Exception $e)
	{
		
		@actualizarBitacoraNotificacionSIAJOP($idRegistroBitacora,0,$e->getMessage(),1,'',$idWS);
	}	
}


function enviarNotificacionMailCancelacion($idEvento)
{
	global $con;
	$idRegistroBitacora=registrarBitacoraNotificacionSIAJOP($idEvento);
	$idWS=99;
	try
	{
		$consulta="SELECT idCentroGestion FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$idEvento;
		$idJuzgado=$con->obtenerValor($consulta);
		$arrDestinatarios=array();
		
		$consulta="SELECT mail FROM _362_tablaDinamica m,_362_chkTipoNotificacion c WHERE m.idReferencia=".$idJuzgado." AND  c.idPadre=m.id__362_tablaDinamica AND
				c.idOpcion=4";
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			array_push($arrDestinatarios,$fila[0]);
		}
		
		$datosAudiencia=formatearEventoAudienciaRendererPenalTradicional($idEvento);
		$arrParam["datosAudiencia"]=$datosAudiencia;
		foreach($arrDestinatarios as $destinatario)
		{
			$arrParam["destinatario"]=$destinatario;
			if(!enviarMensajeEnvio(13,$arrParam,"sendMensajeEnvioGmailJuzgado"))
			{
				@actualizarBitacoraNotificacionSIAJOP($idRegistroBitacora,0,'',0,'',$idWS);
				return;
			}
		}
		@actualizarBitacoraNotificacionSIAJOP($idRegistroBitacora,1,'',0,'',$idWS);
	}
	catch(Exception $e)
	{
		
		@actualizarBitacoraNotificacionSIAJOP($idRegistroBitacora,0,$e->getMessage(),1,'',$idWS);
	}	
}

function formatearEventoAudienciaRendererPenalTradicional($idEventoAudiencia)
{
	global $con;
	

	
	$datosEventos=obtenerDatosEventoAudiencia($idEventoAudiencia);
	
	
	$fechaEvento=utf8_encode(convertirFechaLetra($datosEventos->fechaEvento,true));
	$duracionEstimada=obtenerDiferenciaMinutos($datosEventos->horaInicio,$datosEventos->horaFin)." minutos";
	
	$lblHorario="";
	
	$fechaHoraInicio=strtotime($datosEventos->horaInicio);
	$fechaHoraFin=strtotime($datosEventos->horaFin);
	$comp='';
	if(date("Y-m-d",$fechaHoraInicio)!=date("Y-m-d",$fechaHoraFin))
	{
		$comp=' del '.utf8_encode(convertirFechaLetra(date("Y-m-d",$fechaHoraInicio),true));
	}
	
	$lblJueces='';            
            
	foreach($datosEventos->jueces as $j)
	{
		$lblJueces.=$j->nombreJuez.'<br>';
	}
	
	$lblHorario='De las '.date("h:i",$fechaHoraInicio).' hrs.'.$comp.' a las '.date("h:i",$fechaHoraFin).' hrs. del '.utf8_encode(convertirFechaLetra(date("Y-m-d",$fechaHoraFin),true));
	
	$tabla='	<table width="800px">';
	$tabla.='	<tr height="23"><td align="left" colspan="4" ><br><span style="width:800px;color: #900 !important; font-family: Arial, Helvetica, sans-serif !important; font-size: 12px !important; font-style: normal;  font-weight: bold !important;">Datos de la audiencia</span><br></td></tr>';
	$tabla.='	<tr height="23"><td align="left"><span style="color: rgb(86, 90, 92) !important; font-family: Arial, Helvetica, sans-serif !important; font-size: 12px !important; font-style: normal;  font-weight: bold !important;">Causa Penal:</span></td><td colspan="3" align="left"><span style="color: rgb(0,0,0) !important; font-family: Arial, Helvetica, sans-serif !important; font-size: 12px !important; font-style: normal;  font-weight: bold !important;">'.$datosEventos->carpetaAdministrativa.'</span></td></tr>';
	$tabla.='	<tr height="23"><td align="left"><span style="color: rgb(86, 90, 92) !important; font-family: Arial, Helvetica, sans-serif !important; font-size: 12px !important; font-style: normal;  font-weight: bold !important;">Fecha de la audiencia:</span></td><td colspan="3" align="left"><span style="color: rgb(0,0,0) !important; font-family: Arial, Helvetica, sans-serif !important; font-size: 12px !important; font-style: normal;  font-weight: bold !important;">'.$fechaEvento.'</span></td></tr>';
	$tabla.='	<tr height="23"><td align="left"><span style="color: rgb(86, 90, 92) !important; font-family: Arial, Helvetica, sans-serif !important; font-size: 12px !important; font-style: normal;  font-weight: bold !important;">Duraci&oacute;n estimada:</span></td><td colspan="3" align="left"><span style="color: rgb(0,0,0) !important; font-family: Arial, Helvetica, sans-serif !important; font-size: 12px !important; font-style: normal;  font-weight: bold !important;">'.$duracionEstimada.'</span></td></tr>';
	$tabla.='	<tr height="23"><td align="left"><span style="color: rgb(86, 90, 92) !important; font-family: Arial, Helvetica, sans-serif !important; font-size: 12px !important; font-style: normal;  font-weight: bold !important;">Horario:</span></td><td colspan="3" align="left"><span style="color: rgb(0,0,0) !important; font-family: Arial, Helvetica, sans-serif !important; font-size: 12px !important; font-style: normal;  font-weight: bold !important;">'.$lblHorario.'</span></td></tr>';
//	$tabla.='	<tr height="23"><td align="left"><span style="color: rgb(86, 90, 92) !important; font-family: Arial, Helvetica, sans-serif !important; font-size: 12px !important; font-style: normal;  font-weight: bold !important;">Sala de Tele:</span></td><td colspan="3" align="left"><span style="color: rgb(0,0,0) !important; font-family: Arial, Helvetica, sans-serif !important; font-size: 12px !important; font-style: normal;  font-weight: bold !important;">'.$datosEventos->sala.'</span></td></tr>';
	$tabla.='	<tr height="23"><td align="left"><span style="color: rgb(86, 90, 92) !important; font-family: Arial, Helvetica, sans-serif !important; font-size: 12px !important; font-style: normal;  font-weight: bold !important;">Juzgado:</span></td><td colspan="3" align="left"><span style="color: rgb(0,0,0) !important; font-family: Arial, Helvetica, sans-serif !important; font-size: 12px !important; font-style: normal;  font-weight: bold !important;">'.$datosEventos->unidadGestion.'</span></td></tr>';
	$tabla.='	<tr height="23"><td align="left"><span style="color: rgb(86, 90, 92) !important; font-family: Arial, Helvetica, sans-serif !important; font-size: 12px !important; font-style: normal;  font-weight: bold !important;">Edificio sede:</span></td><td colspan="3" align="left"><span style="color: rgb(0,0,0) !important; font-family: Arial, Helvetica, sans-serif !important; font-size: 12px !important; font-style: normal;  font-weight: bold !important;">'.$datosEventos->edificio.'</span></td></tr>';
	$tabla.='	<tr height="23"><td align="left" style="vertical-align:top; padding-top:4px"><span style="color: rgb(86, 90, 92) !important; font-family: Arial, Helvetica, sans-serif !important; font-size: 12px !important; font-style: normal;  font-weight: bold !important;">'.((sizeof($datosEventos->jueces)==1)?'Juez asignado:':'Jueces asignados:').'</span></td><td colspan="3" align="left"><span style="color: rgb(0,0,0) !important; font-family: Arial, Helvetica, sans-serif !important; font-size: 12px !important; font-style: normal;  font-weight: bold !important;">'.$lblJueces.'</span></td></tr>';
	$tabla.='	<tr height="23"><td align="left" width="200"></td><td width="200"></td><td width="200"></td><td width="200" align="left"></td></tr>';
	$tabla.='	</table>';


	
	return $tabla;
	
}

?>