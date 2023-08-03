<?php 
include_once("latis/funcionesNeotrai.php");

function obtenerDeduccionesCADI($idUsuario,$idNomina,$idDeduccion)
{
	global $con;
	$consultaNomina="SELECT fechaInicioIncidencias,fechaFinIncidencias,ciclo,quincenaAplicacion FROM 672_nominasEjecutadas WHERE idNomina='".$idNomina."' AND tipoNomina='2'";
	$periodoNomina=$con->obtenerPrimeraFila($consultaNomina);
  	$fechaIni="NULL";
  	$fechaFin="NULL";

	if($periodoNomina)
	{
		$fechaIni=$periodoNomina[0];
		$fechaFin=$periodoNomina[1];
		$periodoCiclo=(($periodoNomina[2]*12)+($periodoNomina[3]*1));
	}
	
	$consultaD="SELECT id__986_tablaDinamica,txtImporte,radTipoMes FROM _986_tablaDinamica WHERE cmbEmpleado='".$idUsuario."' AND cmbDeducciones='1' 
				AND radSituacion='1' AND ((cmbCiclo*12)+(cmbMeses*1))<='".$periodoCiclo."'";
	$deducciones=$con->obtenerFilas($consultaD);
	$incidencia=array();
	while ($row= mysql_fetch_row($deducciones))
	{
		switch($row[2])
		{
			case 1:
					$obtenerDatos="SELECT id__986_tablaDinamica,txtImporte FROM _986_tablaDinamica WHERE id__986_tablaDinamica='".$row[0]."' 
									AND ((cmbCiclo*12)+(cmbMeses*1))='".$periodoCiclo."'";
					$importe=$con->obtenerPrimeraFila($obtenerDatos);
					
						$obj=array();
						$obj[0]=$importe[0];
						$obj[1]=$importe[1];
						array_push($incidencia,$obj);
			break;
			case 2:
					$obtenerDatos="SELECT id__986_tablaDinamica,txtImporte FROM _986_tablaDinamica WHERE id__986_tablaDinamica='".$row[0]."' 
									AND ((cmbCiclo*12)+(cmbMeses*1))<'".$periodoCiclo."'";
					$importe=$con->obtenerPrimeraFila($obtenerDatos);
					
						$obj=array();
						$obj[0]=$importe[0];
						$obj[1]=$importe[1];
						array_push($incidencia,$obj);
			break;
		}
	}
	
	foreach ($incidencia as $actual)
	{
		$idRegistro=$actual[0];
		$importe=$actual[1];
		$sumaImporte+=$importe;
		$insertar="INSERT INTO 4555_descuentoDeducciones(idUsuario,importe,idNomina,idConcepto,idReferencia)VALUES('".$idUsuario."',
					'".$importe."','".$idNomina."','".$idDeduccion."','".$idRegistro."')";
		$con->ejecutarConsulta($insertar);
	}
	return $sumaImporte;
}

function calcularISR($baseGravable,$ciclo)//se obtiene detalle de contrato
{
	global $con;
	$impuesto="SELECT limiteInferior,porcentajeFactor,cuotaFija FROM _983_dtgImpuesto g,_983_tablaDinamica t WHERE g.idReferencia=t.id__983_tablaDinamica 
		AND t.cmbCicloFiscal='".$ciclo."' AND limiteInferior<='".$baseGravable."' AND limiteSuperior>='".$baseGravable."'";
	$fImpuesto=$con->obtenerPrimeraFila($impuesto);
	
	$excedente=$baseGravable-$fImpuesto[0];
	$porcentajeExcedente=$fImpuesto[1]/100;
	$calculo=$excedente*$porcentajeExcedente;
	$retencionIsr=$calculo+$fImpuesto[2];
	return $retencionIsr;
}

function obtenerValoresContrato($tipoContrato)//se calcula las retenciones
{
	global $con;
	$fechaActual=date("Y-m-d");
	$iva=0;
	$porcentajeRetIva=0;
	$porcentajeRetIsr=0;
	$iva=0;
	$tipoRetencion=0;
	$consulta="SELECT retenerIvaSiNo,porcentajeRetencionIVA,tipoRetencionISR,porcentajeRetencionISR FROM _989_tablaDinamica 
				WHERE id__989_tablaDinamica='".$tipoContrato."'";
	$configuraciones=$con->obtenerPrimeraFila($consulta);
	
	if($configuraciones[0]==1)//se retiene iva
	{
		$obtenerIVA="SELECT porcentajeIVA FROM 6008_impuestoIVA WHERE apartirDe<='".$fechaActual."'";
		$iva=$con->obtenerValor($obtenerIVA);
		$porcentajeRetIva=$configuraciones[1];
	}
	if($configuraciones[2]==1)//fija
	{
		$porcentajeRetIsr=$configuraciones[3];
	}
	else
	{
		$tipoRetencion=$configuraciones[2];
	}
	$arreglo=array();
	$obj=array();
	
	$obj["iva"]=$iva;
	$obj["porcentajeRetIva"]=$porcentajeRetIva;
	$obj["porcentajeRetIsr"]=$porcentajeRetIsr;
	$obj["tipoRetencion"]=$tipoRetencion;	
		array_push($arreglo,$obj);
	
	return $arreglo;
}

function obtenerProyectosActivos()
{
	global $con;
	$fecha=date("Y-m-d");
	$arreglo=array();
	$consulta="SELECT id__278_tablaDinamica,codigo FROM _278_tablaDinamica WHERE fechatermino>='".$fecha."' ORDER BY id__278_tablaDinamica";
	$resp=$con->obtenerFilas($consulta);
	$numero=mysql_num_rows($resp);
		while ($row= mysql_fetch_row($resp))
		{
			$obj=array();
			$obj["codigoUnidad"]=$row[0];
			$obj["tituloUnidad"]=$row[1];
			array_push($arreglo,$obj);
		}
		return $arreglo;
}

function obtenerEmpleadoProyecto($idNomina,$idRegistroP)
{
	global $con;
	$consultaNomina="SELECT fechaInicioIncidencias,fechaFinIncidencias,ciclo,quincenaAplicacion FROM 672_nominasEjecutadas 
						WHERE idNomina='".$idNomina."'";
	$periodoNomina=$con->obtenerPrimeraFila($consultaNomina);
  	$fechaIni="NULL";
  	$fechaFin="NULL";

	if($periodoNomina)
	{
		$fechaIni=$periodoNomina[0];
		$fechaFin=$periodoNomina[1];
		$periodoCiclo=(($periodoNomina[2]*12)+($periodoNomina[3]*1));
	}
	$arreglo=array();
	$consulta="SELECT idUsuarioContradado,id__988_tablaDinamica,datNumContrato FROM _988_tablaDinamica WHERE idProyecto='".$idRegistroP."' AND (('".$fechaIni."'>=fechaInicio 
				AND '".$fechaIni."'<=fechaConclusion) OR ('".$fechaFin."'>=fechaInicio  AND '".$fechaFin."'<=fechaConclusion) 
				OR ('".$fechaIni."'<=fechaInicio AND '".$fechaFin."'>=fechaConclusion))";
	$resp=$con->obtenerFilas($consulta);
	while ($fila= mysql_fetch_row($resp))
	{
		$obj=array();
		$obj["idUsuario"]=$fila[0];
		$obj["distintor"]=$fila[1];
		$obj["etDistintor"]=$fila[2];
		array_push($arreglo,$obj);
	}

	return $arreglo;
}


?>