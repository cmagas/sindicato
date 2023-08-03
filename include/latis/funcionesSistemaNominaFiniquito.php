<?php include_once("latis/funcionesNeotrai.php");

function totalPercepcionFiniquito($idUsuario,$idNomina,$Sede) //CALCULO NOMINA UGM   Modificado
{
	global $con;
	$calculo="totalPercepcion";
	$Sueldo=0;
	$consultaNomina="SELECT fechaInicioIncidencias,fechaFinIncidencias,ciclo,quincenaAplicacion,idPerfil FROM 672_nominasEjecutadas 
					WHERE idNomina='".$idNomina."'";
	$periodoNomina=$con->obtenerPrimeraFila($consultaNomina);
  	$fechaIni="NULL";
  	$fechaFin="NULL";

	if($periodoNomina)
	{
		$fechaIni=$periodoNomina[0];
		$fechaFin=$periodoNomina[1];
		$difeDias=obtenerDiferenciaDias($fechaIni,$fechaFin);
	}
	$arCostosSede=array();
	$arCostosSede2=array();	
  	
	$sumarHoras=0;
	$ImporteSede=0;
	$costoClase=0;
	$consulDatos="SELECT distinct g.idGrupos,g.Plantel,g.idMateria,p.fechaAsignacion,p.fechaBaja,p.situacion,p.idFormularioAccion,p.idRegistroAccion ,
				g.idInstanciaPlanEstudio,idAsignacionProfesorGrupo,g.idCiclo,g.idPeriodo,i.idModalidad,g.idPlanEstudio FROM 4520_grupos AS g,
				4519_asignacionProfesorGrupo AS p,4513_instanciaPlanEstudio i WHERE p.idNominaFiniquito in (0,".$idNomina.") 
				AND g.idGrupos=p.idGrupo AND g.idInstanciaPlanEstudio=i.idInstanciaPlanEstudio AND  p.fechaAsignacion<=p.fechaBaja and p.situacion<>4
				AND p.idUsuario='".$idUsuario."' AND g.Plantel='".$Sede."' AND p.idParticipacion='37' AND p.fechaBaja>='".$fechaIni."' 
				AND p.fechaBaja<='".$fechaFin."' ORDER BY g.idGrupos ";
	$datos=$con->obtenerFilas($consulDatos);
	$arrGruposIgnorar=array();
	while ($row= mysql_fetch_row($datos))
	{
		$idModalidad=$row[12];
		if(isset($arrGruposIgnorar[$row[0]."_".$row[3]."_".$row[4]]))
			continue;
		else
			$arrGruposIgnorar[$row[0]."_".$row[3]."_".$row[4]]=1;
			
		/*$obtenerNivelplan="SELECT nivelPlanEstudio FROM 4500_planEstudio WHERE idPlanEstudio=".$row[13];
		$idNivelPlan=$con->obtenerValor($obtenerNivelplan);*/
		
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
				$tieneSuplencia=grupoTieneSuplencia($idUsuario,$row[0],$fechaActualNomina);
				if($tieneSuplencia!=1)
				{
					
					if(finiquitarGrupo($row[0],$idNomina,$idUsuario))
					{
						if(!esDiaInhabilEscolar($Sede,$fechaActualNomina))
						{
							if(($fechaIniNomina>=$fInicioGrupo)&&($fechaIniNomina<=$fFinGrupo)) // && = y
							{
								  $diaSemana=date('w',$fechaIniNomina);
									
								  $obtenerHora="SELECT horaFin,horaInicio FROM 4522_horarioGrupo WHERE idGrupo='".$row[0]."' 
											  and dia=".$diaSemana." and '".$fechaActualNomina."'>=fechaInicio 
											  and '".$fechaActualNomina."'<=fechaFin and (fechaFin>fechaInicio) " ;
								  $horas=$con->obtenerFilas($obtenerHora);
								  if($con->filasAfectadas>0)
								  {
									  while ($fila= mysql_fetch_row($horas))
									  {
										  $diferencia=(strtotime($fila[0])-strtotime($fila[1]))/60;
										  $sumarHoras+=$diferencia;
											if(isset($arCostosSede2[$Sede][$row[8]][$row[0]][$fechaActualNomina][$fila[1]]))//sede,Instancia,Grupo,fecha,HoraInicio
												$arCostosSede2[$Sede][$row[8]][$row[0]][$fechaActualNomina][$fila[1]]+=$diferencia/$vTtiempo;
											else	
												$arCostosSede2[$Sede][$row[8]][$row[0]][$fechaActualNomina][$fila[1]]=$diferencia/$vTtiempo;
									  }
								  }
							}
							else
							{
								if($fechaIniNomina>$fFinGrupo)
									break;
							}
						}
					}
				}
				$fechaIniNomina=strtotime("+1 days",$fechaIniNomina);
			}
		}
	}
		//varDump($arCostosSede2);
	$x=0;
	$consulta[$x]="begin";
	$x++;
	foreach($arCostosSede2 as $plantel=>$arreglo)
	{
		foreach($arreglo as $idInstancia=>$Grupo)
		{
			foreach($Grupo as $cveGrupo =>$fecha)
			{
				foreach($fecha as $bloque =>$hora)
				{
					foreach($hora as $horainicioBloque =>$valor)
					{
						$ImporteSede=obtenerCostoProfesor($idUsuario,$plantel,$idInstancia,$cveGrupo);//$idUsuario,$codigoUnidad,$idNivel
						$costoClase=$ImporteSede*$valor;
						$consulta[$x]="INSERT INTO 4556_costoHoraDocentes(idUsuario,idGrupo,horas,costoHora,idNomina,fechasesion,
										horaInicioBloque,calculo)VALUES('".$idUsuario."','".$cveGrupo."','".$valor."','".$ImporteSede."',
										'".$idNomina."','".$bloque."','".$horainicioBloque."','".$calculo."')";
						$x++;
						$Sueldo+=$costoClase;
					}
				}
						$consulta[$x]="INSERT INTO 675_nominaPagada(idNomina,idGrupo,idUsuario)VALUES('".$idNomina."','".$cveGrupo."','".$idUsuario."')";
						$x++;
						$consulta[$x]="UPDATE 4519_asignacionProfesorGrupo SET idNominaFiniquito='".$idNomina."' WHERE idGrupo='".$cveGrupo."' 
										AND idUsuario='".$idUsuario."'";
						$x++;
			}
		}
	}
	$consulta[$x]="commit";
	$x++;
	$con->ejecutarBloque($consulta);
	return $Sueldo;
}

function pagoPorSuplenciaFiniquito($idUsuario,$idNomina,$Sede) //CALCULO NOMINA UGM  Modificado
{
	global $con;
	$calculo="pagoPorSuplenciaFiniquito";
	$Sueldo=0;
	$consultaNomina="SELECT fechaInicioIncidencias,fechaFinIncidencias,ciclo,quincenaAplicacion,idPerfil FROM 672_nominasEjecutadas 
					WHERE idNomina='".$idNomina."'";
	$periodoNomina=$con->obtenerPrimeraFila($consultaNomina);
  	$fechaIni="NULL";
  	$fechaFin="NULL";
	if($periodoNomina)
	{
		$fechaIni=$periodoNomina[0];
		$fechaFin=$periodoNomina[1];
		$difeDias=obtenerDiferenciaDias($fechaIni,$fechaFin);
	}
	
	$arCostosSede=array();
	$arCostosSede2=array();
  	
	$sumarHoras=0;
	$ImporteSede=0;
	$costoClase=0;
	$arrGruposIgnorar=array();
	$consulDatos="SELECT distinct g.idGrupos,g.Plantel,g.idMateria,p.fechaAsignacion,p.fechaBaja,p.situacion,p.idFormularioAccion,p.idRegistroAccion ,
					g.idInstanciaPlanEstudio,idAsignacionProfesorGrupo,g.idCiclo,g.idPeriodo,i.idModalidad,g.idPlanEstudio  FROM 4520_grupos AS g,
					4519_asignacionProfesorGrupo AS p ,4513_instanciaPlanEstudio i WHERE  p.idNominaFiniquito in (0,".$idNomina.") 
					and g.idGrupos=p.idGrupo AND g.idInstanciaPlanEstudio=i.idInstanciaPlanEstudio AND p.fechaAsignacion<=p.fechaBaja and p.situacion<>4
					AND  p.idUsuario='".$idUsuario."' AND g.Plantel='".$Sede."' and p.idParticipacion='45' AND p.fechaBaja>='".$fechaIni."' 
					AND p.fechaBaja<='".$fechaFin."' ORDER BY g.idGrupos ";
	$datos=$con->obtenerFilas($consulDatos);

	while ($row= mysql_fetch_row($datos))// 01-01-2011
	{
		$idModalidad=$row[12];
		if(isset($arrGruposIgnorar[$row[0]."_".$row[3]."_".$row[4]]))
			continue;
		else
			$arrGruposIgnorar[$row[0]."_".$row[3]."_".$row[4]]=1;
		/*$obtenerNivelplan="SELECT nivelPlanEstudio FROM 4500_planEstudio WHERE idPlanEstudio=".$row[13];
		$idNivelPlan=$con->obtenerValor($obtenerNivelplan);*/
		
		$vTtiempo=obtenenerDuracionHoraGrupo($row[0]);
		
		if(colisionaTiempo($fechaIni,$fechaFin,$row[3],$row[4],true))//Grupo activo en fechas
		{
			$fIniSuplencia=strtotime($row[3]);
			$fFinSuplencia=strtotime($row[4]);
			$fechaIniNomina=strtotime($fechaIni);
			$fchaFinNomina=strtotime($fechaFin);
			$sumarHoras=0;
			while($fechaIniNomina<=$fchaFinNomina)
			{
				$fechaActualNomina=date("Y-m-d",$fechaIniNomina);
				
			  	if(finiquitarGrupo($row[0],$idNomina,$idUsuario))
			  	{
					if(!esDiaInhabilEscolar($Sede,$fechaActualNomina))
					{
						if(($fechaIniNomina>=$fIniSuplencia)&&($fechaIniNomina<=$fFinSuplencia))
						{
							$diaSemana=date('w',$fechaIniNomina);
							$obtenerHora="SELECT horaFin,horaInicio FROM 4522_horarioGrupo WHERE idGrupo='".$row[0]."' 
										and dia=".$diaSemana." and '".$fechaActualNomina."'>=fechaInicio and '".$fechaActualNomina."'<=fechaFin and fechaInicio<=fechaFin";
							$horas=$con->obtenerFilas($obtenerHora);
							if($con->filasAfectadas>0)
							{
							  while ($fila= mysql_fetch_row($horas))
							  {
								 $duracionHora=obtenenerDuracionHoraGrupo($row[0]);
								  $diferencia=(strtotime($fila[0])-strtotime($fila[1]))/60;//strtotime("00:00:00")+strtotime($fila[0])-strtotime($fila[1]);
								  $sumarHoras+=$diferencia;
								  
								  if(isset($arCostosSede2[$Sede][$row[8]][$row[0]][$fechaActualNomina][$fila[1]]))//sede,Instancia,Grupo,fecha,HoraInicio
									  $arCostosSede2[$Sede][$row[8]][$row[0]][$fechaActualNomina][$fila[1]]+=$diferencia/$vTtiempo;
								  else	
									  $arCostosSede2[$Sede][$row[8]][$row[0]][$fechaActualNomina][$fila[1]]=$diferencia/$vTtiempo;
							  }
							}
						}
						else
						{
							if($fechaIniNomina>$fFinSuplencia)
								break;
						}
					}
				}
				$fechaIniNomina=strtotime("+1 days",$fechaIniNomina);
			}
		}
	}
	$x=0;
	$consulta[$x]="begin";
	$x++;
	//varDump($arCostosSede2);
	foreach($arCostosSede2 as $plantel=>$arreglo)
	{
		foreach($arreglo as $idInstancia=>$Grupo)
		{
			foreach($Grupo as $cveGrupo =>$fecha)
			{
				foreach($fecha as $bloque =>$hora)
				{
					foreach($hora as $horainicioBloque =>$valor)
					{
						$ImporteSede=obtenerCostoProfesor($idUsuario,$plantel,$idInstancia,$cveGrupo);//$idUsuario,$codigoUnidad,$idNivel
						$costoClase=$ImporteSede*$valor;
						
						$consulta[$x]="INSERT INTO 4556_costoHoraDocentes(idUsuario,idGrupo,horas,costoHora,idNomina,fechasesion,horaInicioBloque,calculo)VALUES('".$idUsuario."',
										'".$cveGrupo."','".$valor."','".$ImporteSede."','".$idNomina."','".$bloque."','".$horainicioBloque."','".$calculo."')";
						$x++;
						$Sueldo+=$costoClase;
					}
				}
						$consulta[$x]="INSERT INTO 675_nominaPagada(idNomina,idGrupo,idUsuario)VALUES('".$idNomina."','".$cveGrupo."','".$idUsuario."')";
						$x++;
						$consulta[$x]="UPDATE 4519_asignacionProfesorGrupo SET idNominaFiniquito='".$idNomina."' WHERE idGrupo='".$cveGrupo."' 
										AND idUsuario='".$idUsuario."' AND idNominaFiniquito in (0,".$idNomina.")";
						$x++;
			}
		}
	}
	$consulta[$x]="commit";
	$x++;
	$con->ejecutarBloque($consulta);
	return $Sueldo;
}






function descuentoPorSuplenciaFiniquito($idUsuario,$idNomina,$plantel) //CALCULO NOMINA UGM
{
	global $con;
	$Sueldo=0;
	$consultaNomina="SELECT fechaInicioIncidencias,fechaFinIncidencias,ciclo,quincenaAplicacion,idPerfil FROM 672_nominasEjecutadas 
					WHERE idNomina='".$idNomina."'";
	$periodoNomina=$con->obtenerPrimeraFila($consultaNomina);
  	$fechaIni="NULL";
  	$fechaFin="NULL";
	if($periodoNomina)
	{
		$fechaIni=$periodoNomina[0];
		$fechaFin=$periodoNomina[1];
		$difeDias=obtenerDiferenciaDias($fechaIni,$fechaFin);
	}
	$arCostosSede=array();
  	$consulCiclo="SELECT idCiclo FROM 4526_ciclosEscolares WHERE '".$fechaIni."' BETWEEN fechaInicio AND fechaTermino 
  				AND '".$fechaFin."' BETWEEN fechaInicio AND fechaTermino";
	$idCiclo=$con->obtenerValor($consulCiclo);
	$sumarHoras=0;
	$ImporteSede=0;
	$costoClase=0;
	$arrGruposIgnorar=array();
	
	$consulDatos="SELECT DISTINCT g.idGrupos,g.Plantel,g.idMateria,p.fechaAsignacion,p.fechaBaja,p.situacion,p.idFormularioAccion,p.idRegistroAccion,
					g.idInstanciaPlanEstudio FROM 4520_grupos AS g,4519_asignacionProfesorGrupo AS p WHERE g.idGrupos=p.idGrupo 
					AND  p.idUsuario='".$idUsuario."' AND Plantel='".$plantel."' AND p.participacionPrincipal='1' AND p.situacion <>4 and p.fechaAsignacion<=p.fechaBaja
					AND p.fechaBaja BETWEEN '".$fechaIni."' AND '".$fechaFin."' AND p.idNominaFiniquito in(0,".$idNomina.")"; 
	
	$datos=$con->obtenerFilas($consulDatos);
	while ($row= mysql_fetch_row($datos))// 01-01-2011
	{
		
		if(finiquitarGrupo($row[0],$idNomina,$idUsuario))
		{
			if(isset($arrGruposIgnorar[$row[0]."_".$row[3]."_".$row[4]]))
				continue;
			else
				$arrGruposIgnorar[$row[0]."_".$row[3]."_".$row[4]]=1;
				
			/*$obtenerNivelplan="SELECT nivelPlanEstudio FROM 4500_planEstudio WHERE idPlanEstudio IN(SELECT DISTINCT idPlanEstudio FROM 4520_grupos 
								WHERE idInstanciaPlanEstudio='".$row[8]."')";
			$idNivelPlan=$con->obtenerValor($obtenerNivelplan);*/
			
			$vTtiempo=obtenenerDuracionHoraGrupo($row[0]);
			
			if(colisionaTiempo($fechaIni,$fechaFin,$row[3],$row[4],true))//Grupo activo en fechas
			{
				$consultaF="SELECT idFormularioAccion,idRegistroAccion,situacion,idAsignacionProfesorGrupo,fechaAsignacion,fechaBaja 
							FROM 4519_asignacionProfesorGrupo WHERE idParticipacion='45' AND situacion='1' AND idGrupo='".$row[0]."'";
				$resGrupoa=$con->obtenerFilas($consultaF);
				while($fGrupo1=mysql_fetch_row($resGrupoa))
				{
					$fIniSuplencia=strtotime($fGrupo1[4]);
					$fFinSuplencia=strtotime($fGrupo1[5]);
					$fechaIniNomina=strtotime($fechaIni);
					$fchaFinNomina=strtotime($fechaFin);
					
					if(colisionaTiempo($fechaIniNomina,$fchaFinNomina,$fIniSuplencia,$fFinSuplencia,true))
					{
						$sumarHoras=0;
						while($fechaIniNomina<=$fchaFinNomina)
						{
							$fechaActualNomina=date("Y-m-d",$fechaIniNomina);
							if(!esDiaInhabilEscolarNomina($plantel,$fechaActualNomina))
							{
								if(($fechaIniNomina>=$fIniSuplencia)&&($fechaIniNomina<=$fFinSuplencia))
								{
									$diaSemana=date('w',$fechaIniNomina);
									$obtenerHora="SELECT horaFin,horaInicio FROM 4522_horarioGrupo WHERE idGrupo='".$row[0]."' 
												and dia=".$diaSemana." and '".$fechaActualNomina."'>=fechaInicio and '".$fechaActualNomina."'<=fechaFin and fechaInicio<=fechaFin";
									$horas=$con->obtenerFilas($obtenerHora);
									if($con->filasAfectadas>0)
									{
										while ($fila= mysql_fetch_row($horas))
										{
											  $diferencia=(strtotime($fila[0])-strtotime($fila[1]))/60;//strtotime("00:00:00")+strtotime($fila[0])-strtotime($fila[1]);
											  $sumarHoras+=$diferencia;
										}
									}
								}
								else
								{
									if($fechaIniNomina>$fFinSuplencia)
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
			}
		}
	}
		//varDump($arCostosSede);
	foreach($arCostosSede as $plantel2=>$arreglo)
	{
		foreach($arreglo as $idInstancia=>$sede)
		{
			foreach($sede as $grupo =>$valor)
			{
				$ImporteSede=obtenerCostoProfesor($idUsuario,$plantel2,$idInstancia,$grupo);//$idUsuario,$codigoUnidad,$idNivel
				$costoClase=$ImporteSede*$valor;
				$Sueldo+=$costoClase;
			}
		}
	}
	return $Sueldo;
}

function ObtenerImporteFaltaFiniquito($idUsuario,$idNomina,$Sede) //CALCULO NOMINA UGM
{
	global $con;
	$calculo="ObtenerImporteFalta";
	$Sueldo=0;
	$sumaCosto=0;
	$costoClase=0;

	$consultaNominaActual="SELECT fechaInicioIncidencias,fechaFinIncidencias,fechaCorteAsistencia,idPerfil,ciclo,fechaInicioFalta,
							quincenaAplicacion FROM 672_nominasEjecutadas WHERE idNomina='".$idNomina."'";
	$periodoNomina=$con->obtenerPrimeraFila($consultaNominaActual);
	$fechaNominaInicial=$periodoNomina[0];
	$fechaNominaFinal=$periodoNomina[1];
	$fechaInicioFalta=$periodoNomina[5];
	$fechaFinFalta=$periodoNomina[2];
	
  	$fechaIni="NULL";
  	$fechaFin="NULL";
	$arrRecesos=obtenerArregloRecesos();

	$consultarFalta="SELECT f.idGrupo,f.fechaFalta,f.horainicial,f.horaFinal,g.idInstanciaPlanEstudio,f.estadoFalta,
					f.idRegistroJustificacion,f.idFalta FROM 4559_controlDeFalta f,4520_grupos g,4519_asignacionProfesorGrupo a 
					WHERE g.idGrupos=f.idGrupo AND f.idGrupo=a.idGrupo AND f.fechaFalta>='".$fechaInicioFalta."' 
					AND f.fechaFalta<='".$fechaFinFalta."' AND f.idUsuario='".$idUsuario."' AND g.Plantel='".$Sede."' 
					AND f.estadoFalta IN(0,1,3) AND ((pagado='0' AND IdNomina='0') OR (pagado='2' AND idNomina='".$idNomina."')) 
					AND a.fechaBaja>='".$fechaNominaInicial."' AND a.fechaBaja<='".$fechaNominaFinal."' and a.fechaAsignacion<=a.fechaBaja and a.situacion<>4";	
	$faltas=$con->obtenerFilas($consultarFalta);
	$arrGruposIgnorar=array();	
	$x=0;
	$consultaF[$x]="begin";
	$x++;
	while ($row= mysql_fetch_row($faltas))
	{
		if(finiquitarGrupo($row[0],$idNomina,$idUsuario))
		{
		
			if($row[5]==1)
			{
				$consulta="select * from _481_tablaDinamica where id__481_tablaDinamica=".$row[6];
				$fReg=$con->obtenerPrimeraFila($consulta);
				if($fReg[13]==1)
					continue;
			}
			
			if(isset($arrGruposIgnorar[$row[0]."_".$row[1]."_".$row[2]]))
				continue;
			else
				$arrGruposIgnorar[$row[0]."_".$row[1]."_".$row[2]]=1;
			
			$diferencia=obtenerNumeroHorasBloque($row[0],$row[2],$row[3],$Sede,$arrRecesos,2);
			$duracionHora=obtenenerDuracionHoraGrupo($row[0]);
			$difHoras=$diferencia/$duracionHora;
			
			$ImporteSede=obtenerCostoProfesor($idUsuario,$Sede,$row[4],$row[0]);//$idUsuario,$codigoUnidad,$idNivel
	
			$costoClase=$ImporteSede*$difHoras;
			$sumaCosto+=$costoClase;
			$consultaF[$x]="INSERT INTO 4556_costoHoraDocentes(idUsuario,idGrupo,horas,costoHora,idNomina,fechasesion,
							horaInicioBloque,calculo)VALUES('".$idUsuario."','".$row[0]."','".$difHoras."','".$ImporteSede."','".$idNomina."',
							'".$row[1]."','".$row[2]."','".$calculo."')";
			$x++;
			$consultaF[$x]="UPDATE 4559_controlDeFalta SET quincena='".$periodoNomina[6]."',pagado='2',idNomina='".$idNomina."' 
							WHERE idFalta='".$row[7]."'";
			$x++;	
		}
	}
	$consultaF[$x]="commit";
	$x++;
	$con->ejecutarBloque($consultaF);
	return $sumaCosto;
}

//Verificar si opera

///Eliminado NO se ocupa

/*function obtenerImporteFaltaSuplenciaFiniquito($idUsuario,$idNomina,$Sede) //CALCULO NOMINA UGM
{
	global $con;
	$calculo="ImporteFaltaSuplencia";
	$Sueldo=0;

	//$plantel='';
	$consultaNomina="SELECT fechaInicioIncidencias,fechaFinIncidencias,fechaCorteAsistencia,ciclo,quincenaAplicacion 
					FROM 672_nominasEjecutadas WHERE idNomina='".$idNomina."'";
	$periodoNomina=$con->obtenerPrimeraFila($consultaNomina);
  	$fechaIni="NULL";
  	$fechaFin="NULL";
	$fechaFinFalta=strtotime("-1 days",strtotime(date("Y-m-d")));
	if($periodoNomina)
	{
		$fechaIni=$periodoNomina[0];
		$fechaFin=$periodoNomina[1];
		$fechaFinFalta=strtotime("-1 days",strtotime($periodoNomina[2]));
		$difeDias=obtenerDiferenciaDias($fechaIni,$fechaFin);
	}
	
		$consultaNominaPagada="SELECT n.idGrupo FROM 675_nominaPagada n,4520_grupos g,672_nominasEjecutadas ne WHERE n.idGrupo=g.idGrupos 
							AND n.idNomina=ne.idNomina AND g.Plantel='".$Sede."' AND ne.etapa='1000' AND ne.ciclo='".$periodoNomina[3]."' 
							AND ne.quincenaAplicacion='".$periodoNomina[4]."' AND n.idUsuario='".$idUsuario."'";
	$listGrupos=$con->obtenerListaValores($consultaNominaPagada);
	if($listGrupos=="")
		$listGrupos=-1;

	$arCostosSede=array();
  	$consulCiclo="SELECT idCiclo FROM 4526_ciclosEscolares WHERE '".$fechaIni."' BETWEEN fechaInicio AND fechaTermino 
  				AND '".$fechaFin."' BETWEEN fechaInicio AND fechaTermino";
	$idCiclo=$con->obtenerValor($consulCiclo);

	$sumarHoras=0;
	$ImporteSede=0;
	$costoClase=0;
	$consulDatos="SELECT g.idGrupos,g.Plantel,g.idMateria,p.fechaAsignacion,p.fechaBaja,p.situacion,p.idFormularioAccion,p.idRegistroAccion,
					g.idInstanciaPlanEstudio FROM 4520_grupos AS g,4519_asignacionProfesorGrupo AS p WHERE g.idGrupos=p.idGrupo 
					AND  p.idUsuario='".$idUsuario."' and Plantel='".$Sede."' and p.participacionPrincipal='1' 
					AND p.esperaContrato='1' AND g.idGrupos NOT IN(".$listGrupos.")";//debo cambiar el valor de espera de contrato
	$datos=$con->obtenerFilas($consulDatos);
	while ($row= mysql_fetch_row($datos))
	{
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
			while($fechaIniNomina<=$fechaFinFalta)
			{
				$fechaActual=date("Y-m-d",$fechaIniNomina);
				if(($fechaIniNomina>=$fInicioGrupo)||($fechaIniNomina<=$fFinGrupo)) // && = y
				{
					$diaSemana=date('w',$fechaIniNomina);
					$obtenerHora="SELECT distinct horaFin,horaInicio FROM 4522_horarioGrupo WHERE idGrupo='".$row[0]."' 
								and dia=".$diaSemana." and '".$fechaActual."'>=fechaInicio and '".$fechaActual."'<=fechaFin";
					$horas=$con->obtenerFilas($obtenerHora);
					if($con->filasAfectadas>0)
					{
						if(pagarDiaSesion($fechaIniNomina,$Sede,$idUsuario))
						{
							while ($fila= mysql_fetch_row($horas))
							{
								$duracionHora=obtenenerDuracionHoraGrupo($row[0]);
								$diferencia=(strtotime($fila[0])-strtotime($fila[1]))/$duracionHora;
								
								$sumarHoras+=((date("H",$diferencia)*60)+date("i",$diferencia));

								  if(isset($arCostosSede2[$Sede][$row[8]][$row[0]][$fechaActualNomina][$fila[1]]))//sede,Instancia,Grupo,fecha,HoraInicio
									  $arCostosSede2[$Sede][$row[8]][$row[0]][$fechaActualNomina][$fila[1]]+=$diferencia/$vTtiempo;
								  else	
									  $arCostosSede2[$Sede][$row[8]][$row[0]][$fechaActualNomina][$fila[1]]=$diferencia/$vTtiempo;
							}
						}
					}
				}
				else
					break;
					
				$fechaIniNomina=strtotime("+1 days",$fechaIniNomina);
			}
		}
		//varDump($arCostosSede);
	}

	foreach($arCostosSede2 as $plantel=>$arreglo)
	{
		foreach($arreglo as $idInstancia=>$Grupo)
		{
			foreach($Grupo as $cveGrupo =>$fecha)
			{
				foreach($fecha as $bloque =>$hora)
				{
					foreach($hora as $horainicioBloque =>$valor)
					{
						$ImporteSede=obtenerCostoProfesor($idUsuario,$plantel,$idInstancia,$cveGrupo);//$idUsuario,$codigoUnidad,$idNivel
						$costoClase=$ImporteSede*$valor;
						
						$GuardarCosto="INSERT INTO 4556_costoHoraDocentes(idUsuario,idGrupo,horas,costoHora,idNomina,fechasesion,horaInicioBloque,calculo)VALUES('".$idUsuario."',
										'".$cveGrupo."','".$valor."','".$ImporteSede."','".$idNomina."','".$bloque."','".$horainicioBloque."','".$calculo."')";
						$con->ejecutarConsulta($GuardarCosto);
						$Sueldo+=$costoClase;

					}
				}
			}
		}
	}
	return $Sueldo;
}*/ 

function obtenerImporteReposicionFiniquito($idUsuario,$idNomina,$plantel)
{
	global $con;
	$calculo="importeReposicion";
	$Sueldo=0;
	
	$consultaNomina="SELECT fechaInicioIncidencias,fechaFinIncidencias FROM 672_nominasEjecutadas WHERE idNomina='".$idNomina."'";
	$periodoNomina=$con->obtenerPrimeraFila($consultaNomina);
  	$fechaIni="NULL";
  	$fechaFin="NULL";
	
	if($periodoNomina)
	{
		$fechaIni=$periodoNomina[0];
		$fechaFin=$periodoNomina[1];
		$difeDias=obtenerDiferenciaDias($fechaIni,$fechaFin);
	}
	$listGrupos=2;
	if($listGrupos=='-1')
	{
		return $Sueldo;
	}
	else
	{
							
		$consultaSesiones="SELECT idSesion,fechaSesion,horario,s.idGrupo,a.fechaAsignacion,a.fechaBaja  FROM 4530_sesiones s,4520_grupos g,4519_asignacionProfesorGrupo a 
						WHERE  g.idGrupos=s.idGrupo AND g.idGrupos=a.idGrupo AND g.Plantel='".$plantel."' 
						AND fechaSesion>='".$fechaIni."' AND fechaSesion<='".$fechaFin."' AND a.idUsuario='".$idUsuario."'  AND tipoSesion='15' 
						and ((a.idNominaFiniquito=0)or(a.idNominaFiniquito=".$idNomina.")) and a.fechaAsignacion<=a.fechaBaja and a.situacion<>4
						ORDER BY fechaSesion,horario";
		$datosSesiones=$con->obtenerFilas($consultaSesiones);
			$horaIniciobloque="";
			$horaFinBloque="";
			$arCostosSede=array();
			$arCostosSede2=array();
			
		while ($row= mysql_fetch_row($datosSesiones))
		{
			if(finiquitarGrupo($row[3],$idNomina,$idUsuario))
			{
				$sumarHoras=0;
				$horario=explode("-",$row[2]);
				$horaIniciobloque=trim($horario[0]);
				$horaFinBloque=trim($horario[1]);
				
				$consulta="SELECT DISTINCT idUsuario FROM 4562_registroReposicionSesion r,4563_sesionesReposicion s WHERE r.idRegistroReposicion=s.idRegistroReposicion
							AND s.idGrupo=".$row[3]." AND s.fechaReposicion='".$row[1]."' AND s.horaInicio='".$horaIniciobloque."'";
				$idUsrRepone=$con->obtenerValor($consulta);
				if($idUsrRepone!=$idUsuario)
					continue;
				$obtenerAsistencia="SELECT idFalta FROM 4559_controlDeFalta WHERE idUsuario='".$idUsuario."' AND fechaFalta='".$row[1]."' 
									AND idGrupo='".$row[3]."' AND horaInicial='".$horaIniciobloque."' AND estadoFalta='0'";
				$hayRegistro=$con->obtenerValor($obtenerAsistencia);
				if($hayRegistro=="")
				{
					$diferencia=(strtotime($horaFinBloque)-strtotime($horaIniciobloque))/60;
					$sumarHoras+=$diferencia;
					
					$obtenerInstanciaPlan="SELECT idInstanciaPlanEstudio FROM 4520_profesorGrupoMateriaSede where idGrupos='".$row[3]."'";
					$idPlan=$con->obtenerValor($obtenerInstanciaPlan);
					
					$obtenerNivelplan="SELECT nivelPlanEstudio FROM 4500_planEstudio WHERE idPlanEstudio IN(SELECT DISTINCT idPlanEstudio FROM 4520_grupos 
										WHERE idInstanciaPlanEstudio='".$idPlan."')";
					$idNivelPlan=$con->obtenerValor($obtenerNivelplan);
					
					$obtenerTiempo="SELECT dracionHora FROM _472_tablaDinamica WHERE idReferencia='".$idPlan."'";
					$valorTiempo=$con->obtenerValor($obtenerTiempo);
					$vTtiempo=60;
					if($valorTiempo)
					{	
						$vTtiempo=$valorTiempo;
					}
							
					if(isset($arCostosSede2[$plantel][$idPlan][$row[3]][$row[1]][$horaIniciobloque]))//sede,Instancia,Grupo,fecha,HoraInicio
						$arCostosSede2[$plantel][$idPlan][$row[3]][$row[1]][$horaIniciobloque]+=$diferencia/$vTtiempo;
					else	
						$arCostosSede2[$plantel][$idPlan][$row[3]][$row[1]][$horaIniciobloque]=$diferencia/$vTtiempo;
				}
				
				
				
				
			}
		}
		foreach($arCostosSede2 as $plantel=>$arreglo)
		{
			foreach($arreglo as $idInstancia=>$Grupo)
			{
				foreach($Grupo as $cveGrupo =>$fecha)
				{
					foreach($fecha as $bloque =>$hora)
					{
						foreach($hora as $horainicioBloque =>$valor)
						{
							$ImporteSede=obtenerCostoProfesor($idUsuario,$plantel,$idInstancia,$cveGrupo);//$idUsuario,$codigoUnidad,$idNivel
							$costoClase=$ImporteSede*$valor;
							
							$GuardarCosto="INSERT INTO 4556_costoHoraDocentes(idUsuario,idGrupo,horas,costoHora,idNomina,fechasesion,horaInicioBloque,calculo)VALUES('".$idUsuario."',
											'".$cveGrupo."','".$valor."','".$ImporteSede."','".$idNomina."','".$bloque."','".$horainicioBloque."','".$calculo."')";
							$con->ejecutarConsulta($GuardarCosto);
							$Sueldo+=$costoClase;
						}
					}
				}
			}
		}
		return $Sueldo;
	}
}

function ImporteDiaFestivoFiniquito($idUsuario,$idNomina,$plantel) //CALCULO NOMINA UGM
{
	global $con;
	$calculo="ImporteDiaFestivo";
	$Sueldo=0;
	$consulta="SELECT cmbSePaga FROM _503_tablaDinamica WHERE cmbPlanteles='".$plantel."'";
	$sePaga=$con->obtenerValor($consulta);
	if($sePaga=='1')
	{
		$consultaNomina="SELECT fechaInicioIncidencias,fechaFinIncidencias,ciclo,quincenaAplicacion FROM 672_nominasEjecutadas 
						WHERE idNomina='".$idNomina."'";
		$periodoNomina=$con->obtenerPrimeraFila($consultaNomina);
		$fechaIni="NULL";
		$fechaFin="NULL";
	
		if($periodoNomina)
		{
			$fechaIni=$periodoNomina[0];
			$fechaFin=$periodoNomina[1];
			$difeDias=obtenerDiferenciaDias($fechaIni,$fechaFin);
		}
		
		$consultaNominaPagada="SELECT n.idGrupo FROM 675_nominaPagada n,4520_grupos g,672_nominasEjecutadas ne 
			WHERE n.idGrupo=g.idGrupos AND n.idNomina=ne.idNomina AND g.Plantel='".$plantel."' AND n.idNomina='".$idNomina."' 
			AND n.idUsuario='".$idUsuario."'";
		$listGrupos=$con->obtenerListaValores($consultaNominaPagada);
		if($listGrupos=="")
			$listGrupos=-1;
		
		$arCostosSede=array();
		$arCostosSede2=array();
		
		$consulCiclo="SELECT idCiclo FROM 4526_ciclosEscolares WHERE '".$fechaIni."' BETWEEN fechaInicio AND fechaTermino 
					AND '".$fechaFin."' BETWEEN fechaInicio AND fechaTermino";
		$idCiclo=$con->obtenerValor($consulCiclo);
	
			$sumarHoras=0;
			$ImporteSede=0;
			$costoClase=0;
			$consulDatos="SELECT DISTINCT g.idGrupos,g.Plantel,g.idMateria,p.fechaAsignacion,p.fechaBaja,
				p.situacion,p.idFormularioAccion,p.idRegistroAccion,g.idInstanciaPlanEstudio FROM 4520_grupos AS g,
				4519_asignacionProfesorGrupo AS p WHERE g.idGrupos=p.idGrupo AND  p.idUsuario='".$idUsuario."' 
				AND Plantel='".$plantel."' AND p.situacion <>'4' and p.fechaAsignacion<=p.fechaBaja AND p.fechaBaja='".$fechaFin."' 
				AND g.idGrupos IN(".$listGrupos.")";
			$datos=$con->obtenerFilas($consulDatos);
			$arrGruposIgnorar=array();
			while ($row= mysql_fetch_row($datos))
			{ 
				if(isset($arrGruposIgnorar[$row[0]."_".$row[3]."_".$row[4]]))
					continue;
				else
					$arrGruposIgnorar[$row[0]."_".$row[3]."_".$row[4]]=1;
				/*$obtenerNivelplan="SELECT nivelPlanEstudio FROM 4500_planEstudio 
				WHERE idPlanEstudio IN(SELECT DISTINCT idPlanEstudio 
							FROM 4520_grupos WHERE idInstanciaPlanEstudio='".$row[8]."')";
				$idNivelPlan=$con->obtenerValor($obtenerNivelplan);*/
				$vTtiempo=obtenenerDuracionHoraGrupo($row[0]);

				if(colisionaTiempo($fechaIni,$fechaFin,$row[3],$row[4]))//Grupo activo en fechas
				{
					$fechaIniNomina=strtotime($fechaIni);
					$fchaFinNomina=strtotime($fechaFin);
					$fInicioGrupo=strtotime($row[3]);
					$fFinGrupo=strtotime($row[4]);
					$sumarHoras=0;
					while($fechaIniNomina<=$fchaFinNomina)
					{
						$fechaActualNomina=date("Y-m-d",$fechaIniNomina);
							if(esDiaInhabilEscolarNomina($plantel,$fechaActualNomina))
							{
								if(($fechaIniNomina>=$fInicioGrupo)&&($fechaIniNomina<=$fFinGrupo)) // && = y
								{
									  $diaSemana=date('w',$fechaIniNomina);
										$obtenerDias="SELECT idOpcion FROM _503_radDiaSemana AS d,_503_tablaDinamica AS t 
											WHERE d.idPadre=t.id__503_tablaDinamica AND t.cmbPlanteles='".$plantel."' 
											and d.idOpcion='".$diaSemana."' ORDER BY d.idOpcion";
										$estaDia=$con->obtenerValor($obtenerDias);
										if($estaDia)
										{
											$continuar= 1;
										}
										else
										{
											$continuar=2;
										}
	
										if($continuar==1)
										{
//												$obtenerHora="SELECT horaInicioBloque,horaFinBloque 
//													FROM 9105_controlAsistenciaDiaFestivo WHERE  idGrupo='".$row[0]."' 
//													and fecha='".$fechaActualNomina."' ";
											$obtenerHora="SELECT horaFin,horaInicio FROM 4522_horarioGrupo 
												WHERE idGrupo='".$row[0]."' and dia=".$diaSemana." 
												and '".$fechaActualNomina."'>=fechaInicio 
												and '".$fechaActualNomina."'<=fechaFin and (fechaFin>fechaInicio) ";
												//echo $obtenerHora."<br>";
												$horas=$con->obtenerFilas($obtenerHora);
											  if($con->filasAfectadas>0)
											  {
												  while ($fila= mysql_fetch_row($horas))
												  {
													  $diferencia=(strtotime($fila[0])-strtotime($fila[1]))/60;//strtotime("00:00:00")+strtotime($fila[0])-strtotime($fila[1]);
													  $sumarHoras+=$diferencia;

														if(isset($arCostosSede2[$plantel][$row[8]][$row[0]][$fechaActualNomina][$fila[0]]))//sede,Instancia,Grupo,fecha,HoraInicio
															$arCostosSede2[$plantel][$row[8]][$row[0]][$fechaActualNomina][$fila[0]]+=$diferencia/$vTtiempo;
														else	
															$arCostosSede2[$plantel][$row[8]][$row[0]][$fechaActualNomina][$fila[0]]=$diferencia/$vTtiempo;
												  }
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
				}
			}
		
			foreach($arCostosSede2 as $plantel=>$arreglo)
			{
				foreach($arreglo as $idInstancia=>$Grupo)
				{
					foreach($Grupo as $cveGrupo =>$fecha)
					{
						foreach($fecha as $bloque =>$hora)
						{
							foreach($hora as $horainicioBloque =>$valor)
							{
								$ImporteSede=obtenerCostoProfesor($idUsuario,$plantel,$idInstancia,$cveGrupo);//$idUsuario,$codigoUnidad,$idNivel
								$costoClase=$ImporteSede*$valor;
								
								$GuardarCosto="INSERT INTO 4556_costoHoraDocentes(idUsuario,idGrupo,horas,costoHora,idNomina,fechasesion,horaInicioBloque,
												calculo)VALUES('".$idUsuario."','".$cveGrupo."','".$valor."','".$ImporteSede."','".$idNomina."','".$bloque."',
												'".$horainicioBloque."','".$calculo."')";
								$con->ejecutarConsulta($GuardarCosto);
								$Sueldo+=$costoClase;
							}
						}
					}
				}
			}
			return $Sueldo;
	}
	else
		 return $Sueldo;
}




function obtenerPercepcionEspecialesFiniquito($idUsuario,$idNomina,$idConcepto,$plantel)
{
	global $con;
	$importe=0;
	
	$fechaNomina="SELECT fechaInicioIncidencias,fechaFinIncidencias,ciclo,quincenaAplicacion FROM 672_nominasEjecutadas WHERE idNomina='".$idNomina."'";
	$periodo=$con->obtenerPrimeraFila($fechaNomina);
	
	$consulta="SELECT radNominaAplicar,id__451_tablaDinamica FROM _451_tablaDinamica WHERE cmbCiclo='".$periodo[2]."' AND cmbUsuarios='".$idUsuario."' 
				AND codigoInstitucion='".$plantel."' AND cmbConceptos='".$idConcepto."' AND (cmbQuincenaNomina='".$periodo[3]."' OR cmbNomina='".$idNomina."') 
				AND idEstado='2'";
	$res=$con->obtenerFilas($consulta);
	while ($fila= mysql_fetch_row($res))
	{
		$valor=0;
		switch($fila[0])
		{
			case 1:
					$consulta="SELECT idGrupo FROM 4519_asignacionProfesorGrupo a,4520_grupos g WHERE a.idGrupo=g.idGrupos AND a.fechaBaja>'".$periodo[1]."' 
								AND a.idUsuario='".$idUsuario."' AND g.Plantel='".$plantel."'";
					$materia=$con->obtenerPrimeraFila($consulta);
					$numero=(count($materia)*1);
					if($numero>='1')
					{
						
					}
					else
					{
						$consultaImporte="SELECT txtImporte FROM _451_tablaDinamica WHERE cmbUsuarios='".$idUsuario."' AND cmbConceptos='".$idConcepto."' 
											AND cmbCiclo='".$periodo[2]."' AND codigoInstitucion='".$plantel."' and id__451_tablaDinamica='".$fila[1]."'";
						$valor=$con->obtenerValor($consultaImporte);
						$importe+=$valor;
					}
			break;
			case 2:
				$consultaImporte="SELECT txtImporte FROM _451_tablaDinamica WHERE cmbUsuarios='".$idUsuario."' AND cmbConceptos='".$idConcepto."' 
									AND cmbCiclo='".$periodo[2]."' AND codigoInstitucion='".$plantel."' AND id__451_tablaDinamica='".$fila[1]."'";
				$valor=$con->obtenerValor($consultaImporte);
					$importe+=$valor;
			break;
		}
	}
	return $importe;
}

function marcarFaltaPagadas($idNomina)
{
	global $con;
	$query="SELECT idPerfil FROM 672_nominasEjecutadas WHERE idNomina=".$idNomina;
	$idPerfil=$con->obtenerValor($query);
	
	$x=0;
	$consulta[$x]="begin";
	$x++;
	$consulta[$x]="UPDATE 4559_controlDeFalta SET pagado='1' WHERE idNomina='".$idNomina."'";
	$x++;
	$consulta[$x]="UPDATE 677_descuentoFiniquito SET situacion='1' WHERE idNominaAplicacion='".$idNomina."' AND situacion='0'";
	$x++;
	if($idPerfil==8)
	{
		$query="SELECT DISTINCT idUsuario,idGrupo FROM 4556_costoHoraDocentes WHERE idNomina=".$idNomina;
		$res=$con->obtenerFilas($query);
		while($fila=mysql_fetch_row($res))
		{
			$consulta[$x]="UPDATE 4519_asignacionProfesorGrupo SET idNominaFiniquito=".$idNomina." WHERE idGrupo=".$fila[1]." AND idUsuario=".$fila[0];
			$x++;
		}
	}
	$consulta[$x]="commit";
	$x++;
	return $con->ejecutarBloque($consulta);
}

function desmarcarFaltaPagadas($idNomina)
{
	global $con;
	$x=0;
	$consulta[$x]="begin";
	$x++;
	$consulta[$x]="UPDATE 4559_controlDeFalta SET pagado='0',idNomina=0 WHERE idNomina='".$idNomina."'";
	$x++;
	$consulta[$x]="DELETE FROM 677_descuentoFiniquito WHERE idNomina='".$idNomina."'";
	$x++;
	$consulta[$x]="UPDATE 677_descuentoFiniquito SET idNominaAplicacion='0' WHERE idNominaAplicacion='".$idNomina."'";
	$x++;
	$consulta[$x]="DELETE FROM 4556_costoHoraDocentes WHERE idNomina='".$idNomina."'";
	$x++;
	$consulta[$x]="DELETE FROM 675_nominaPagada WHERE idNomina='".$idNomina."'";
	$x++;
	$consulta[$x]="DELETE FROM 671_asientosCalculosNomina WHERE idNomina='".$idNomina."'";
	$x++;
	$query="SELECT DISTINCT idUsuario,idGrupo FROM 4556_costoHoraDocentes WHERE idNomina=".$idNomina;
	$res=$con->obtenerFilas($query);
	while($fila=mysql_fetch_row($res))
	{
		$consulta[$x]="UPDATE 4519_asignacionProfesorGrupo SET idNominaFiniquito=0 WHERE idGrupo=".$fila[1]." AND idUsuario=".$fila[0];
		$x++;
	}
	
	$consulta[$x]="commit";
	$x++;
	$con->ejecutarBloque($consulta);
}

function obtenerDocenteAsimiladosFiniquito($idNomina,$plantel)//Modificado
{
	global $con;
	$consultaNomina="SELECT fechaInicioIncidencias,fechaFinIncidencias,ciclo,quincenaAplicacion,idPerfil FROM 672_nominasEjecutadas WHERE idNomina='".$idNomina."'";
	
	$periodoNomina=$con->obtenerPrimeraFila($consultaNomina);
  	$fechaIni="NULL";
  	$fechaFin="NULL";

	if($periodoNomina)
	{
		$fechaIni=$periodoNomina[0];
		$fechaFin=$periodoNomina[1];
	}
	
	$listCandidatos="";
	
	$consultaAdmon="SELECT DISTINCT idUsuario FROM 979_horariosLaborUsuario order by idUsuario";
	$listAdmon=$con->obtenerListaValores($consultaAdmon);
	if($listAdmon=="")
		$listAdmon=-1;
		
	$consultaProfes="SELECT DISTINCT idUsuario FROM 801_adscripcion WHERE tipoContratacion='14' order by idUsuario";		
	$listProfes=$con->obtenerListaValores($consultaProfes);
	if($listProfes=="")
		$listProfes=-1;
	
	$consulta="SELECT DISTINCT idUsuario FROM 4519_asignacionProfesorGrupo a,4520_grupos AS g WHERE a.idGrupo=g.idGrupos AND g.Plantel='".$plantel."' 
				AND a.situacion  <>4 AND a.fechaBaja>='".$fechaIni."' and a.fechaBaja<='".$fechaFin."' 
				AND idUsuario NOT IN(".$listAdmon.") AND a.idUsuario NOT IN(".$listProfes.") AND idUsuario<>'2' 
				AND ((a.idNominaFiniquito=0 OR a.idNominaFiniquito=".$idNomina.")) ORDER BY a.idUsuario";
				

	$datos=$con->obtenerFilas($consulta);
	$num=0;
	while($fila=mysql_fetch_row($datos))
	{
		$consideraProfesor=false;
		$cosultarGrupos="SELECT a.idGrupo,g.idCiclo,g.idPeriodo,i.idModalidad,p.nivelPlanEstudio FROM 4519_asignacionProfesorGrupo a,4520_grupos AS g,4513_instanciaPlanEstudio i,4500_planEstudio p
							WHERE a.idGrupo=g.idGrupos AND g.idInstanciaPlanEstudio=i.idInstanciaPlanEstudio AND g.Plantel='".$plantel."' AND a.situacion <>4 
							AND a.fechaBaja>='".$fechaIni."' AND a.fechaBaja<='".$fechaFin."' AND idUsuario='".$fila[0]."' AND ((a.idNominaFiniquito=0 
							OR a.idNominaFiniquito=".$idNomina.")) and g.idPlanEstudio=p.idPlanEstudio ORDER BY a.idGrupo";
		
		$resGrupo=$con->obtenerFilas($cosultarGrupos);
		while($filaG=mysql_fetch_row($resGrupo))
		{
			if(finiquitarGrupo($filaG[0],$idNomina,$fila[0]))
			{
				$consideraProfesor=true;
				break;	
			}
		}
		
		if($consideraProfesor)
		{
			if($listCandidatos=="")
				$listCandidatos=$fila[0];
			else
				$listCandidatos.=",".$fila[0];
		}
	}
	if($listCandidatos=="")
			$listCandidatos=-1;
	
	return "'".$listCandidatos."'";
}

function guardarImssFiniquito($idNomina,$idUsuario,$plantel,$importe)
{
	global $con;
	$consulNomina="SELECT ciclo,quincenaAplicacion FROM 672_nominasEjecutadas WHERE idNomina='".$idNomina."'";
	$dNomina=$con->obtenerPrimeraFila($consulNomina);
	
	$consulta="INSERT INTO 677_descuentoFiniquito(idUsuario,plantel,tipoDescuento,importe,situacion,idNomina,ciclo,
				quincena)VALUES('".$idUsuario."','".$plantel."','1','".$importe."','0','".$idNomina."','".$dNomina[0]."','".$dNomina[1]."')";
	$con->ejecutarConsulta($consulta);
}

function obtenerPagosImss($idUsuario,$plantel,$idNomina)
{
	global $con;
	$suma=0;
	
	$consulNomina="SELECT ciclo,quincenaAplicacion FROM 672_nominasEjecutadas WHERE idNomina='".$idNomina."'";
	$dNomina=$con->obtenerPrimeraFila($consulNomina);

	$consultar="SELECT f.idDescuento,f.importe,f.situacion FROM 677_descuentoFiniquito f,672_nominasEjecutadas n WHERE f.idNomina=n.idNomina 
				AND idUsuario='".$idUsuario."' AND quincena='".$dNomina[1]."' AND plantel='".$plantel."' AND tipoDescuento='1' 
				AND f.ciclo='".$dNomina[0]."' AND f.ciclo=n.ciclo AND f.quincena=n.quincenaAplicacion";
	$datos=$con->obtenerFilas($consultar);
	$imss=0;
	while($fila=mysql_fetch_row($datos))
	{
		switch($fila[2])
		{
			case 0:
					$imss=$fila[1];
					$actualizar="UPDATE 677_descuentoFiniquito SET idNominaAplicacion='".$idNomina."',situacion='1' 
								WHERE idDescuento='".$fila[0]."'";
					$con->ejecutarConsulta($actualizar);
			break;
			case 1:
					$imss=$fila[1];
			break;
		}
		$suma=$suma+$imss;	
	}
	return $suma;
}

function tieneMateriasVigente($idNomina,$idUsuario,$plantel,$idNivelPlan,$idCiclo,$idPeriodo)//Modificado
{
	//verifico que el docente tiene materias vigentes despues de esta nomina para el mismo nivel y plantel.
	global $con;
	$resp=0; //0=No tiene, 1=Tiene
	$consultaNomina="SELECT fechaInicioIncidencias,fechaFinIncidencias FROM 672_nominasEjecutadas WHERE idNomina='".$idNomina."'";
	$periodoNomina=$con->obtenerPrimeraFila($consultaNomina);
  	$fechaIni="NULL";
  	$fechaFin="NULL";

	if($periodoNomina)
	{
		$fechaIni=$periodoNomina[0];
		$fechaFin=$periodoNomina[1];
	}
	
	$consultarMaterias="SELECT a.idGrupo FROM 4519_asignacionProfesorGrupo a,4520_grupos g,4500_planEstudio p,4513_instanciaPlanEstudio i 
						WHERE a.idGrupo=g.idGrupos AND g.idPlanEstudio=p.idPlanEstudio AND g.idInstanciaPlanEstudio=i.idInstanciaPlanEstudio 
						AND p.nivelPlanEstudio='".$idNivelPlan."' AND  i.idModalidad='6' AND a.situacion<>4 and a.fechaAsignacion<=a.fechaBaja and a.fechaBaja>'".$fechaFin."' AND a.idUsuario='".$idUsuario."' 
						AND g.Plantel='".$plantel."' and g.idCiclo='".$idCiclo."' and g.idPeriodo='".$idPeriodo."'";
	$conMaterias=$con->obtenerValor($consultarMaterias);
	
	if($conMaterias!="")
	{
		$resp=1;
	}
	return $resp;
}

function finiquitarGrupo($idGrupo,$idNomina,$idUsuario)
{
	
	global $con;
	
	$consulta="SELECT idPlanEstudio,idInstanciaPlanEstudio,Plantel,idCiclo,idPeriodo FROM 4520_grupos WHERE idGrupos=".$idGrupo;
	$fGrupos=$con->obtenerPrimeraFila($consulta);
	$consulta="SELECT idModalidad FROM 4513_instanciaPlanEstudio WHERE idInstanciaPlanEstudio=".$fGrupos[1];
	$idModalidad=$con->obtenerValor($consulta);
	$consulta="SELECT nivelPlanEstudio FROM 4500_planEstudio WHERE idPlanEstudio=".$fGrupos[0];
	$idNivel=$con->obtenerValor($consulta);
	if($idModalidad!=6)
	{
		return true;
	}
	else
	{
		$res=tieneMateriasVigente($idNomina,$idUsuario,$fGrupos[2],$idNivel,$fGrupos[3],$fGrupos[4]);//nomina,docente,plantel,nivelPlan,ciclo,periodo
		if($res==0)
		{
			return true;
		}
	}
	return false;
}

?>