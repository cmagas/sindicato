<?php 
include_once("latis/conexionBD.php");
include_once("latis/utiles.php");
include_once("latis/sgjp/funcionesAgenda.php");
include_once("latis/numeroToLetra.php");


function activarCentrosGestoresConvocatoria($idFormulario,$idRegistro)
{
	global $con;
	
	$consulta="SELECT * FROM _543_tablaDinamica WHERE id__543_tablaDinamica=".$idRegistro;
	$fConvocatoria=$con->obtenerPrimeraFilaAsoc($consulta);
	$consulta="SELECT planInstitucional FROM 3206_estructurasProgramaticas WHERE idRegistro=".$fConvocatoria["estructuraProgramatica"];
	$planInstitucional=$con->obtenerValor($consulta);
	
	$arrDocumentos=array();
	$consulta="SELECT idDocumento FROM 9074_documentosRegistrosProceso WHERE idFormulario=".$idFormulario.
			" AND idRegistro=".$idRegistro." AND tipoDocumento in(1,2)";
	$rDocumentos=$con->obtenerFilas($consulta);
	while($fDocumento=mysql_fetch_row($rDocumentos))
	{
		array_push($arrDocumentos,$fDocumento[0]);
	}
	
	$consulta="SELECT * FROM 817_organigrama WHERE institucion='11' and status=1 ORDER BY unidad";
	$rOganizaciones=$con->obtenerFilas($consulta);
	while($fOrganizacion=mysql_fetch_row($rOganizaciones))
	{
	
		$arrValores=array();		
		$arrValores["codigoInstitucion"]=$fOrganizacion[3];
		$arrValores["planInstitucional"]=$planInstitucional;
		$arrValores["ejercicioFiscal"]=$fConvocatoria["ejercicioFiscal"];
		$arrValores["descripcion"]=$fConvocatoria["descripcionConvocatoria"];
		$arrValores["estructuraProgramatica"]=$fConvocatoria["estructuraProgramatica"];
		$arrValores["idProcesoPadre"]=223;
		crearInstanciaRegistroFormulario(539,$idRegistro,1,$arrValores,$arrDocumentos,-1,953);
	}
	
	return true;
}

function guardarDatosIndicador($idRegistro,$cadObj)
{
	global $con;
	$obj=json_decode(bD($cadObj));

	$x=0;
	$consulta[$x]="begin";
	$x++;
	$consulta[$x]="DELETE FROM _571_lineaBaseRegistro WHERE idReferencia=".$idRegistro;
	$x++;
	foreach($obj->arrLineaBase as $l)
	{
		for($p=0;$p<=$obj->totalPeridos;$p++)
		{
			$valor=0;
			eval('$valor=$l->absoluto_'.$p.";");
			$consulta[$x]="INSERT INTO _571_lineaBaseRegistro(idReferencia,anio,mesValor,tipoValor,valor,calculado) 
						VALUES(".$idRegistro.",".$l->anio.",".$p.",0,".$valor.",".$l->calculado.")";
			$x++;
			
			$valor=0;
			eval('$valor=$l->porcentaje_'.$p.";");
			$consulta[$x]="INSERT INTO _571_lineaBaseRegistro(idReferencia,anio,mesValor,tipoValor,valor,calculado) 
						VALUES(".$idRegistro.",".$l->anio.",".$p.",1,".$valor.",".$l->calculado.")";
			$x++;
		}
	}
	$consulta[$x]="commit";
	$x++;
	
	return $con->ejecutarBloque($consulta);
	
}

function generarCalendarioReporteIndicadores($idFormulario,$idRegistro)
{
	global $con;
	
	$consulta="SELECT ejercicioFiscal FROM _539_tablaDinamica WHERE id__539_tablaDinamica=".$idRegistro;
	$ejercicioFiscal=$con->obtenerValor($consulta);
	
	$dteFechaReporte=strtotime($ejercicioFiscal."-01-01");
	
	
	$x=0;
	$query[$x]="begin";
	$x++;
	
	$query[$x]="DELETE FROM 539_calendarioReportesIndicadores WHERE idReferencia=".$idRegistro;
	$x++;
	
	
	
	$consulta="SELECT distinct iB.frecuenciaMedicion,iM.nombreResponsable FROM _550_tablaDinamica iB,_571_tablaDinamica iM WHERE iB.idProcesoPadre=222 AND iB.idReferencia=".$idRegistro."
				AND iM.idReferencia=iB.id__550_tablaDinamica";
	$res=$con->obtenerFilas($consulta);				
	while($fila=mysql_fetch_row($res))
	{
		$totalPeridos=12/$fila[0];
		for($periodo=1;$periodo<=$totalPeridos;$periodo++)
		{
	
			$dteFechaReporte=strtotime("+".$fila[0]." months",$dteFechaReporte);
			$fechaReporte=date("Y-m",$dteFechaReporte)."-01";
			

			$query[$x]="INSERT INTO 539_calendarioReportesIndicadores(idReferencia,fechaReporte,reportado,noPeriodo,periodicidad,responsable)
						VALUES(".$idRegistro.",'".$fechaReporte."',0,".$periodo.",".$fila[0].",".$fila[1].")";
			$x++;
		
		}
		
		
		
	}
	
	$query[$x]="commit";
	$x++;
	
	return $con->ejecutarBloque($query);
	
}


function registrarNotificacionReporteIndicador($tNotificacion,$idUsuarioDestinatario,$idUsuarioRemitente,$rolRemitente,$idRegistro)
{
	global $con;
	$arrValores=array();
	$nombreTablaBase="9060_tableroControl_4";
	$rolActor=obtenerTituloRol("173_0");
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
	$arrValores["objConfiguracion"]='{"actorAccesoProceso":"173_0","funcionApertura":"mostrarVentanaAperturaProcesoNotificacion"}';
	$arrValores["permiteAbrirProceso"]="1";
	$arrValores["idNotificacion"]=217;
	$arrValores["numeroCarpetaAdministrativa"]="N/A";
	$arrValores["iFormulario"]=572;
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

	$consulta="insert into 9060_tableroControl_4(".$camposInsert.") values(".$camposValues.")";
	
	return $con->ejecutarConsulta($consulta);
}


?>