<?php 
include_once("latis/funcionesNeotrai.php");


function calcularSalarioIntegrado($idUsuario,$plantel,$idNomina)//Nomina UGM
{
	global $con;
	$Sueldo=0;
	$tHoraMateria=0;
	$importeSede=0;
	$sdi=0;
	$valor=0;
	$fechaNomina="SELECT fechaInicioIncidencias,fechaFinIncidencias,ciclo,quincenaAplicacion FROM 672_nominasEjecutadas WHERE idNomina='".$idNomina."'";
	$periodo=$con->obtenerPrimeraFila($fechaNomina);
	
	$fechaIni="NULL";
	$fechaFin="NULL";
	$ciclo="NULL";
	
	if($periodo)
	{
		$fechaIni=$periodo[0];
		$fechaFin=$periodo[1];
		$anio=$periodo[2];
		$bimestre=obtenerBimestre($periodo[3]);
	}

	$consultaFechaActualizacion="SELECT MAX(dteFechaReinicio)FROM _671_tablaDinamica t,_671_gridPlanteles g WHERE t.id__671_tablaDinamica=g.idReferencia 
									AND g.sede='".$plantel."' AND dteFechaReinicio<='".$fechaIni."'";
	$fechaReinicio=$con->obtenerValor($consultaFechaActualizacion);
	if($fechaReinicio)
	{
		$consultaDatos="SELECT id__497_tablaDinamica FROM _497_tablaDinamica WHERE dteFechaInicioVigencia>='".$fechaReinicio."' AND cmbDocente='".$idUsuario."' 
						AND codigoInstitucion='".$plantel."'";
		$registro=$con->obtenerValor($consultaDatos);
		if($registro)
		{
			$consulta1="SELECT id__497_tablaDinamica FROM _497_tablaDinamica WHERE dteFechaInicioVigencia>='".$fechaReinicio."' AND cmbDocente='".$idUsuario."'
						AND cmbBimestre2='".$bimestre."' AND codigoInstitucion='".$plantel."'";
			$registro1=$con->obtenerValor($consulta1);
			if($registro1)
			{
				$valor=1;//Traer SDI de BD 
			}
			else
			{
				$valor=2;
			}
			
		}
		else
		{
			$valor=3; // generar nuevo SDI
		}
	}
	switch($valor)
	{
		case 1:
			$sdi=obtenerSDI($registro1,$plantel);
		break;
		case 2:
			$sdi=generarSDI($idUsuario,$plantel,$idNomina,$bimestre,$fechaReinicio);
		break;
		case 3:
			$sdi=generarSDI($idUsuario,$plantel,$idNomina,$bimestre,$fechaReinicio);
		break;
		
	}
	return $sdi;
}

function obtenerBimestre($quincena)
{
	global $con;
	switch($quincena)
	{
		case ($quincena>=1)&&($quincena<=4) :
			$bimestre=1;
		break;
		case ($quincena>=5)&&($quincena<=8) :
		$bimestre=2;
		break;
		case ($quincena>=9)&&($quincena<=12) :
		$bimestre=3;
		break;
		case ($quincena>=13)&&($quincena<=16) :
		$bimestre=4;
		break;
		case ($quincena>=17)&&($quincena<=20) :
		$bimestre=5;
		break;
		case ($quincena>=11)&&($quincena<=24) :
		$bimestre=6;
		break;
	}
	return $bimestre;
}

function obtenerSDI($idRegistro,$plantel)
{
	global $con;
	$consulta="SELECT txtSDI FROM _497_tablaDinamica WHERE id__497_tablaDinamica='".$idRegistro."' AND codigoInstitucion='".$plantel."'";
	$importe=$con->obtenerValor($consulta);
	if(!$importe)
		$importe=0;
		
	return $importe;
}

function generarSDI($idUsuario,$plantel,$idNomina,$bimestre,$fVigencia)
{
	global $con;
	$sdi=0;
	$importeSede=0;
	$fechaHoy=date("Y-m-d");
	$fechaNomina="SELECT fechaInicioIncidencias,fechaFinIncidencias,ciclo,quincenaAplicacion FROM 672_nominasEjecutadas WHERE idNomina='".$idNomina."'";
	$periodo=$con->obtenerPrimeraFila($fechaNomina);
	$fechaIni="NULL";
	$fechaFin="NULL";
	$ciclo="NULL";

	if($periodo)
	{
		$fechaIni=$periodo[0];
		$fechaFin=$periodo[1];
		$ciclo=$periodo[2];
	}
	$fechaInicial="SELECT MIN(p.fechaAsignacion) FROM 4519_asignacionProfesorGrupo AS p,4520_grupos AS g WHERE p.idGrupo=g.idGrupos 
					AND g.Plantel='".$plantel."' AND p.idUsuario='".$idUsuario."' AND (('".$fechaIni."'>=p.fechaAsignacion 
					and '".$fechaIni."'<=p.fechaBaja) or ('".$fechaFin."'>=p.fechaAsignacion and '".$fechaFin."'<=p.fechaBaja)
					or ('".$fechaIni."'<=p.fechaAsignacion and '".$fechaFin."'>=p.fechaBaja))";
	$fInicial=$con->obtenerValor($fechaInicial);
	
	$inicio=strtotime($fInicial);
	$fin=strtotime("+7 days",$inicio);
	$finalizaPeriodo=date("Y-m-d",$fin);
	
	$consulDatos="SELECT g.idMateria,g.idInstanciaPlanEstudio FROM 4520_grupos AS g,4519_asignacionProfesorGrupo AS p
					WHERE g.idGrupos=p.idGrupo AND p.idUsuario='".$idUsuario."' AND g.Plantel='".$plantel."' AND (('".$fechaIni."'>=p.fechaAsignacion 
					and '".$fechaIni."'<=p.fechaBaja) or ('".$finalizaPeriodo."'>=p.fechaAsignacion and '".$finalizaPeriodo."'<=p.fechaBaja)
					or ('".$fechaIni."'<=p.fechaAsignacion and '".$finalizaPeriodo."'>=p.fechaBaja))					";
	$datos=$con->obtenerFilas($consulDatos);
	while ($row= mysql_fetch_row($datos))
	{
		$consulMateria="SELECT horasSemana FROM 4502_Materias WHERE idMateria='".$row[0]."'";
		$horaMateria=$con->obtenerValor($consulMateria);
		$costo=obtenerCostoProfesor($idUsuario,$plantel,$row[1]);
		$valor=$horaMateria*$costo;
		$importeSede+=$valor;
	}
	$sdi=number_format($importeSede/7,2);
	
	$insertarDatos="INSERT INTO _497_tablaDinamica(fechaCreacion,codigoUnidad,codigoInstitucion,cmbCiclo,cmbDocente,txtSDI,cmbBimestre2,
					dteFechaInicioVigencia)VALUES('".$fechaHoy."','".$plantel."','".$plantel."','".$ciclo."','".$idUsuario."','".$sdi."','".$bimestre."','".$fVigencia."')";
	$con->ejecutarConsulta($insertarDatos);
	
	return $sdi;
}

function calculoSDI($idUsuario,$plantel,$bimestre,$ciclo)
{
	global $con;
	$arCostosSede=array();
		$sumarHoras=0;
		$ImporteSede=0;
		$costoClase=0;
		$fechaIni=$FI;
		$fechaFin=$FF;
		$diasTrabajados=0;
		$diasT=array();
		$Sueldo=0;
		$valorSDI=0;
		
		$fechaBimestreAnterio=obtenerFechaBimestreAnterior($ciclo,$bimestre);
		
		$consulDatos="SELECT distinct g.idGrupos,g.Plantel,g.idMateria,p.fechaAsignacion,p.fechaBaja,p.situacion,p.idFormularioAccion,p.idRegistroAccion,
						g.idInstanciaPlanEstudio FROM 4520_grupos AS g,4519_asignacionProfesorGrupo AS p WHERE g.idGrupos=p.idGrupo 
						AND  p.idUsuario='".$idUsuario."' and Plantel='".$plantel."' and p.participacionPrincipal='1' 
						AND (('".$FI."'>=p.fechaAsignacion and '".$FI."'<=p.fechaBaja) or ('".$FF."'>=p.fechaAsignacion and '".$FF."'<=p.fechaBaja)
						or ('".$FI."'<=p.fechaAsignacion and '".$FF."'>=p.fechaBaja))"; 
		$datos=$con->obtenerFilas($consulDatos);
		$arrGruposIgnorar=array();
		while ($row= mysql_fetch_row($datos))
		{
			if(isset($arrGruposIgnorar[$row[0]."_".$row[3]."_".$row[4]]))
				continue;
			else
				$arrGruposIgnorar[$row[0]."_".$row[3]."_".$row[4]]=1;
			$obtenerNivelplan="SELECT nivelPlanEstudio FROM 4500_planEstudio WHERE idPlanEstudio IN(SELECT DISTINCT idPlanEstudio FROM 4520_grupos 
								WHERE idInstanciaPlanEstudio='".$row[8]."')";
			$idNivelPlan=$con->obtenerValor($obtenerNivelplan);
			$vTtiempo=obtenenerDuracionHoraGrupo($row[0]);
			
			if(colisionaTiempo($fechaIni,$fechaFin,$row[3],$row[4],true))//Grupo activo en fechas
			{
				$fechaIniNomina=strtotime($fechaIni);
				$fchaFinNomina=strtotime($fechaFin);
				$fInicioGrupo=strtotime($row[3]);
				$fFinGrupo=strtotime($row[4]);
				$sumarHoras=0;
				
				while($fechaIniNomina<=$fchaFinNomina)
				{
					$fechaActualNomina=date("Y-m-d",$fechaIniNomina);

					if(!esDiaInhabilEscolar($plantel,$fechaActualNomina))
					{
						if(($fechaIniNomina>=$fInicioGrupo)&&($fechaIniNomina<=$fFinGrupo)) // && = y
						{
							  $diaSemana=date('w',$fechaIniNomina);
								
							  $obtenerHora="SELECT horaFin,horaInicio FROM 4522_horarioGrupo WHERE idGrupo='".$row[0]."' 
										  and dia=".$diaSemana." and '".$fechaActualNomina."'>=fechaInicio and '".$fechaActualNomina."'<=fechaFin";
							  $horas=$con->obtenerFilas($obtenerHora);
							  if($con->filasAfectadas>0)
							  {
								  while ($fila= mysql_fetch_row($horas))
								  {
									  $diferencia=(strtotime($fila[0])-strtotime($fila[1]))/60;//strtotime("00:00:00")+strtotime($fila[0])-strtotime($fila[1]);
									  $sumarHoras+=$diferencia;
									  if(!isset($diasT[$fechaIniNomina]))
											$diasT[$fechaIniNomina]=1;
								  }
							  }
						}
						else
						{
							if($fechaIniNomina>$fFinGrupo)
								break;
						}
					}
					$fechaIniNomina=strtotime("+1 days",$fechaIniNomina);
				}
				
				if(isset($arCostosSede[$plantel][$row[8]][$row[0]]))
					$arCostosSede[$plantel][$row[8]][$row[0]]+=$sumarHoras/$vTtiempo;
				else	
					$arCostosSede[$plantel][$row[8]][$row[0]]=$sumarHoras/$vTtiempo;
			}
		}
		//varDump($arCostosSede);
	foreach($arCostosSede as $plantel=>$arreglo)
	{
		foreach($arreglo as $idInstancia=>$sede)
		{
			foreach($sede as $grupo =>$valor)

			{
				$ImporteSede=obtenerCostoProfesor($idUsuario,$plantel,$idInstancia);//$idUsuario,$codigoUnidad,$idNivel
				$costoClase=$ImporteSede*$valor;
				$Sueldo+=$costoClase;
			}
		}
	}
	 
	$diasTrabajados=count($diasT);
	if($Sueldo>0 && $diasTrabajados>0)
	{
		$valorSDI=$Sueldo/$diasTrabajados;
		return $valorSDI;
	}
	else
	return 0;
}

function obtenerFechaBimestreAnterior($ciclo,$bimestre)
{
	global $con;
	
		if($bimestre=3)//se lee ele periodo del bimestre anterior para alcular el SDI del docente
		{
			$fechaPeriodo="SELECT fechaInicial,fechaFinal FROM _501_tablaDinamica WHERE cmbCiclo='".$ciclo."' AND txtBimestre='SEGUNDO BIMESTRE'";
			$periodo1=$con->obtenerPrimeraFila($fechaPeriodo);
			$fechaIni1="NULL";
			$fechaFin1="NULL";
	
			if($periodo)
			{
				$fechaIni1=$periodo1[0];
				$fechaFin1=$periodo1[1];
			}
		}
		if($bimestre=4)
		{
			$fechaPeriodo="SELECT fechaInicial,fechaFinal FROM _501_tablaDinamica WHERE cmbCiclo='".$anio."' AND txtBimestre='TERCER BIMESTRE'";
			$periodo1=$con->obtenerPrimeraFila($fechaPeriodo);
			$fechaIni1="NULL";
			$fechaFin1="NULL";
	
			if($periodo)
			{
				$fechaIni1=$periodo1[0];
				$fechaFin1=$periodo1[1];
			}
		}
		if($bimestre=5)
		{
			$fechaPeriodo="SELECT fechaInicial,fechaFinal FROM _501_tablaDinamica WHERE cmbCiclo='".$anio."' AND txtBimestre='CUARTO BIMESTRE'";
			$periodo1=$con->obtenerPrimeraFila($fechaPeriodo);
			$fechaIni1="NULL";
			$fechaFin1="NULL";
	
			if($periodo)
			{
				$fechaIni1=$periodo1[0];
				$fechaFin1=$periodo1[1];
			}
		}
		if($bimestre=6)
		{
			$fechaPeriodo="SELECT fechaInicial,fechaFinal FROM _501_tablaDinamica WHERE cmbCiclo='".$anio."' AND txtBimestre='QUINTO BIMESTRE'";
			$periodo1=$con->obtenerPrimeraFila($fechaPeriodo);
			$fechaIni1="NULL";
			$fechaFin1="NULL";
	
			if($periodo)
			{
				$fechaIni1=$periodo1[0];
				$fechaFin1=$periodo1[1];
			}
		}
}



?>