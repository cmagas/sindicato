<?php 
	include_once("latis/funcionesNeotrai.php");
	

function obtenerEmpleadoEmpresa($idEmpresa)
{
	global $con;
	$consulta="SELECT DISTINCT idEmpleado FROM 693_empleadosNominaV2 WHERE idEmpresa='".$idEmpresa."' and situacion='1'";
	$datos=$con->obtenerFilas($consulta);
	$num=0;
	while($fila=mysql_fetch_row($datos))
	{
				if($listCandidatos=="")
					$listCandidatos=$fila[0];
				else
					$listCandidatos.=",".$fila[0];
	}
	if($listCandidatos=="")
			$listCandidatos=-1;
			
	return "".$listCandidatos."";
}

function obtenerAntiguedad($idUsuario)
{
	global $con;
	$anioActual=date("Y");
	$consulta="SELECT fechaIniRelLab FROM 693_empleadosNominaV2 WHERE idEmpleado='".$idUsuario."'";
	$res=$con->obtenerValor($consulta);
	$anioInicio=date("Y",$res);
	$anti=$anioActual-$anioInicio;
	return $anti;
}

function obtenerDiasVacaciones($antiguedad)
{
	global $con;
	$consulta="SELECT diasVacaciones FROM _1033_tablaVacaciones WHERE '".$antiguedad."' BETWEEN de AND hasta";
	$res=$con->obtenerValor($consulta);
	return $res;
}

function obtenerSueldoDiario($idUsuario)
{
	global $con;
	$res=0;
	$consultarNomina="SELECT idCentroCosto FROM 672_nominasEjecutadas WHERE idNomina='".$idNomina."'";
	$resCentro=$con->$obtenerValor($consultarNomina);
	
	$consultar="SELECT valor FROM 714_empleadosConceptoBase WHERE idConcepto='2' AND idEmpleado='".$idUsuario."'";
	$res=$con->obtenerValor($consultar);
	
	if($resCentro==1 || $resCentro==3)
	{
		$costoHora=$res/8;
		$res=$costoHora*8;
	}
	return $res;
}

function obtenerApoyosConceptos($idUsuario,$idConcepto)
{
	global $con;
	$res=0;
	$consultar="SELECT SUM(valor) FROM 714_empleadosConceptoBase WHERE idEmpleado='".$idUsuario."' AND idConcepto='".$idConcepto."'";
	$res=$con->obtenerValor($consultar);
	if($res)
	{
		return $res;
	}
	else
	{
		return 0;
	}
}

function diasNominasKuri($idNomina,$idEmpleado)
{
	global $con;
	$dias=0;
	$consultaNomina="SELECT fechaInicioIncidencias,fechaFinIncidencias,ciclo,quincenaAplicacion,institucion FROM 672_nominasEjecutadas 
			WHERE idNomina='".$idNomina."'";
	$periodoNomina=$con->obtenerPrimeraFila($consultaNomina);
  	$fechaIni="NULL";
  	$fechaFin="NULL";

	if($periodoNomina)
	{
		$fechaIni=$periodoNomina[0];
		$fechaFin=$periodoNomina[1];
	}
	
	$consultarBaja="SELECT fechaBaja FROM _1034_tablaDinamica WHERE cmbEmpleado='".$idEmpleado."' AND cmbEmpresa='".$periodoNomina[4]."' and idEstado='2'";
	$res=$con->obtenerValor($consultarBaja);
	if($res)
	{
		$fechaFin=$res;
	}

    $dias=obtenerDiferenciaDias($fechaIni,$fechaFin)+1;
	return $dias;
}

function impuestoQuincConst($baseGrav,$Ciclo,$dato)//Calculo de impuesto de nomina
{
	//$dato= 'limite', 'porcentaje', 'cuota'
	//sueldo Efectivo trabajado menos descuento por suplencia.
	global $con;
	$valor=0;
	$consulta1="SELECT idCicloFiscal FROM 550_cicloFiscal WHERE ciclo='".$Ciclo."'";
	$valorCiclo=$con->obtenerValor($consulta1);

	$consulta="SELECT impuesto.limInferior,impuesto.porcentaje,impuesto.cuotaFija FROM _420_gridImpuesto AS impuesto,_420_tablaDinamica AS tipo
				WHERE impuesto.idReferencia=tipo.id__420_tablaDinamica AND '".$baseGrav."' BETWEEN impuesto.limInferior AND impuesto.limSuperior 
				AND tipo.cmbCicloFiscal='".$valorCiclo."'";
	$filas=$con->obtenerPrimeraFila($consulta);
	if($filas)
	{
		$limInferior=$filas[0];
		$porcentaje=$filas[1];
		$cuota=$filas[2];
		
		switch($dato)
	  	{
			case($dato=='limite'):
		  	$valor=$limInferior;
		  	break;
			case($dato=='porcentaje'):
			$valor=$porcentaje;
			break;
			case($dato=='cuota'):
			$valor=$cuota;
			break;
			default:
			$valor=0;
			break;
		}
		return $valor;
	}
	else
	{
		return "0";
	}
}

function obtenerCreditoSalario1($baseGravable)
{
	global $con;
	$consulta="SELECT creditoSalario FROM _1032_tablaCredito c,_1032_tablaDinamica t WHERE c.idReferencia= t.id__1032_tablaDinamica
				AND radSituacion='1' AND '".$baseGravable."' BETWEEN ingresoDe AND ingresoHasta";
	$res=$con->obtenerValor($consulta);
	return $res;
}

function obtenerDiasMes1($idNomina)
{
	global $con;
	$consulta="SELECT ciclo,quincenaAplicacion FROM 672_nominasEjecutadas WHERE idNomina='".$idNomina."'";
	$res=$con->obtenerPrimeraFila($consulta);
	$ciclo=$res[0];
	switch($res[1])
	{
		case (1||2):
					$mes=1;
		break;
		case (3||4):
					$mes=2;
		break;
		case (5||6):
					$mes=3;
		break;
		case (7||8):
					$mes=4;
		break;
		case (9||10):
					$mes=5;
		break;
		case (11||12):
					$mes=6;
		break;
		case (13||14):
					$mes=7;
		break;
		case (15||16):
					$mes=8;
		break;
		case (17||18):
					$mes=9;
		break;
		case (19||20):
					$mes=10;
		break;
		case (21||22):
					$mes=11;
		break;
		case (23||24):
					$mes=12;
		break;
		
	}
	$diasM=getMonthDays($mes, $ciclo);
	return $diasM;
}
function getMonthDays1($Month, $Year)
{
   //Si la extensión que mencioné está instalada, usamos esa.
   if( is_callable("cal_days_in_month"))
   {
      return cal_days_in_month(CAL_GREGORIAN, $Month, $Year);
   }
   else
   {
      //Lo hacemos a mi manera.
      return date("d",mktime(0,0,0,$Month+1,0,$Year));
   }
}

function impuestoMensualKuri1($baseGrav,$Ciclo,$dato)//Calculo de impuesto de nomina
{
	//$dato= 'limite', 'porcentaje', 'cuota'
	global $con;
	$valor=0;
	$consulta1="SELECT idCicloFiscal FROM 550_cicloFiscal WHERE ciclo='".$Ciclo."'";
	$valorCiclo=$con->obtenerValor($consulta1);

	$consulta="SELECT impuesto.limInferior,impuesto.porcentaje,impuesto.cuotaFija FROM _420_tablaMensual i,_420_tablaDinamica t
				WHERE i.idReferencia=t.id__420_tablaDinamica AND '".$baseGrav."' BETWEEN i.limInferior AND i.limSuperior 
				AND t.cmbCicloFiscal='".$valorCiclo."'";
	$filas=$con->obtenerPrimeraFila($consulta);
	if($filas)
	{
		$limInferior=$filas[0];
		$porcentaje=$filas[1];
		$cuota=$filas[2];
		
		switch($dato)
	  	{
			case($dato=='limite'):
		  	$valor=$limInferior;
		  	break;
			case($dato=='porcentaje'):
			$valor=$porcentaje;
			break;
			case($dato=='cuota'):
			$valor=$cuota;
			break;
			default:
			$valor=0;
			break;
		}
		return $valor;
	}
	else
	{
		return "0";
	}
}

function obtenerValorCalculoK1($idNomina,$idCalculo,$idUsuario,$empresa,$identificador=0)
{
	global $con;	
	$buscarCalculo=false;
	$consulta="SELECT objDetalle FROM 671_asientosCalculosNomina WHERE idNomina=".$idNomina." AND idUsuario=".$idUsuario." 
			and institucion='".$empresa."' and identificador=".$identificador;
	$objDetalle=$con->obtenerValor($consulta);
	if($objDetalle=="")
		return 0;
	$obj=unserialize($objDetalle);

	foreach($obj->arrCalculosGlobales as $c)
	{
		if(isset($c["idConsulta"]))
		{
			if($c["idConsulta"]==$idCalculo)
			{
				return $c["valorCalculado"];
			}
		}
		else
		{
			$buscarCalculo=true;
			break;
		}
	}
	if($buscarCalculo)
	{
		$consulta="SELECT idPerfil FROM 672_nominasEjecutadas WHERE idNomina=".$idNomina;
	  	$idPerfil=$con->obtenerValor($consulta);
	  	$consulta="SELECT idCalculo FROM 662_calculosNomina WHERE idConsulta=".$idCalculo." AND idPerfil=".$idPerfil;
	  	$idCalculoNomina=$con->obtenerValor($consulta);
	  	if($idCalculoNomina=="")
			return 0;
	  	if(isset($obj->arrCalculosGlobales[$idCalculoNomina]))
	  		return $obj->arrCalculosGlobales[$idCalculoNomina]["valorCalculado"];
	}
	return 0;
}

function aplicarBajaEmpleado6($idRegistro)
{
	global $con;
	$idFormulario=1010;
	$consulta="SELECT idEmpleado,e.idEmpresa,fechaBaja FROM _1010_tablaDinamica t,6927_empresas e WHERE t.codigoInstitucion=e.referencia 
				AND id__1010_tablaDinamica='".$idRegistro."'";
	$res=$con->obtenerPrimeraFila($consulta);
	
	$actualizar="UPDATE 693_empleadosNominaV2 SET situacion='0',idFormularioBaja='".$idFormulario."',idRegistroBaja='".$idRegistro."', 
			fechaBaja='".$res[2]."' WHERE idEmpleado='".$res[0]."' AND idEmpresa='".$res[1]."'";
	$con->ejecutarConsulta($actualizar);
}

function obtenerImportePercepcionEspecial($idNomina,$idUsuario,$idConcepto)
{
	global $con;
}

function diasTrabajadosConstructora($idNomina,$idUsuario)
{
	global $con;
	
	return 10;	
	
}

function obtenerSalarioDiarioIntegrado($idUsuario)
{
	global $con;
	$sdi=0;
	$consulta="SELECT sdi FROM 693_empleadosNominaV2 WHERE idEmpleado='".$idUsuario."'";
	$res=$con->obtenerValor($consulta);
	return $res;
}

function HorasExtras($idUsuario,$idNomina)
{
	global $con;
	$res=0;
	$consultarNomina="SELECT idCentroCosto FROM 672_nominasEjecutadas WHERE idNomina='".$idNomina."'";
	$resCentro=$con->$obtenerValor($consultarNomina);
	
	$consultar="SELECT valor FROM 714_empleadosConceptoBase WHERE idConcepto='2' AND idEmpleado='".$idUsuario."'";
	$res=$con->obtenerValor($consultar);
	
	if($resCentro==1 || $resCentro==3)
	{
		$costoHora=$res/8;
		$res=$costoHora*4;
	}
	return $res;
}
?>