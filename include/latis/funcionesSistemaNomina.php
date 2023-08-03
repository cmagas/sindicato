<?php include_once("latis/funcionesNeotrai.php");

function totalPercepcion($idUsuario,$idNomina,$Sede) //CALCULO NOMINA UGM
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
	$consulDatos="SELECT distinct g.idGrupos,g.Plantel,g.idMateria,p.fechaAsignacion,p.fechaBaja,p.situacion,p.idFormularioAccion,
				p.idRegistroAccion,g.idInstanciaPlanEstudio,p.idParticipacion FROM 4520_grupos AS g,
				4519_asignacionProfesorGrupo AS p WHERE g.idGrupos=p.idGrupo AND  p.idUsuario='".$idUsuario."' 
				and g.Plantel='".$Sede."' AND p.situacion NOT IN(4)  and p.idParticipacion='37' 
				AND (('".$fechaIni."'>=p.fechaAsignacion AND '".$fechaIni."'<=p.fechaBaja) or ('".$fechaFin."'>=p.fechaAsignacion 
				AND '".$fechaFin."'<=p.fechaBaja) or ('".$fechaIni."'<=p.fechaAsignacion and '".$fechaFin."'>=p.fechaBaja)) 
				AND p.idNominaFiniquito=0 and  p.fechaAsignacion<=p.fechaBaja  ORDER BY g.idGrupos";
	$datos=$con->obtenerFilas($consulDatos);
	$arrGruposIgnorar=array();
	while ($row= mysql_fetch_row($datos))
	{
		if(isset($arrGruposIgnorar[$row[0]."_".$row[3]."_".$row[4]]))
			continue;
		else
			$arrGruposIgnorar[$row[0]."_".$row[3]."_".$row[4]]=1;
		$obtenerNivelplan="SELECT nivelPlanEstudio FROM 4500_planEstudio WHERE idPlanEstudio IN(SELECT DISTINCT idPlanEstudio 
						FROM 4520_grupos WHERE idInstanciaPlanEstudio='".$row[8]."')";
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
				$tieneSuplencia=grupoTieneSuplencia($idUsuario,$row[0],$fechaActualNomina);
				
				if($tieneSuplencia!=1)
				{
					if(!esDiaInhabilEscolar($Sede,$fechaActualNomina))
					{
						if(($fechaIniNomina>=$fInicioGrupo)&&($fechaIniNomina<=$fFinGrupo)) // && = y
						{
							  $diaSemana=date('w',$fechaIniNomina);
								
							  $obtenerHora="SELECT horaFin,horaInicio FROM 4522_horarioGrupo WHERE idGrupo='".$row[0]."' 
										  and dia=".$diaSemana." and '".$fechaActualNomina."'>=fechaInicio 
										  and '".$fechaActualNomina."'<=fechaFin and (fechaFin>=fechaInicio) " ;
							  $horas=$con->obtenerFilas($obtenerHora);
							  if($con->filasAfectadas>0)
							  {
								  while ($fila= mysql_fetch_row($horas))
								  {
									  $diferencia=(strtotime($fila[0])-strtotime($fila[1]))/60;
									  $sumarHoras+=$diferencia;
									  
										if(isset($arCostosSede2[$Sede][$row[8]][$row[0]][$fechaActualNomina][$fila[1]]))
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
				$fechaIniNomina=strtotime("+1 days",$fechaIniNomina);
			}
		}
	}
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
//						if($idUsuario=='894'&&$cveGrupo=='17765')
//						{
//							if($bloque>='2014-07-12')
//							{
//								$valor=$valor-6;
//							}
//						}

						
						$ImporteSede=obtenerCostoProfesor($idUsuario,$plantel,$idInstancia,$cveGrupo);//$idUsuario,$codigoUnidad,$idNivel
						$costoClase=$ImporteSede*$valor;
						//echo "fechaSesion :".$bloque." Grupo :".$cveGrupo." Horas :".$valor." costo :".$ImporteSede."<br>";
						$consulta[$x]="INSERT INTO 4556_costoHoraDocentes(idUsuario,idGrupo,horas,costoHora,idNomina,fechasesion,horaInicioBloque,calculo)VALUES('".$idUsuario."',
										'".$cveGrupo."','".$valor."','".$ImporteSede."','".$idNomina."','".$bloque."','".$horainicioBloque."','".$calculo."')";
						$x++;
						$Sueldo+=$costoClase;
					}
				}
						$consulta[$x]="INSERT INTO 675_nominaPagada(idNomina,idGrupo,idUsuario)VALUES('".$idNomina."','".$cveGrupo."','".$idUsuario."')";
						$x++;
			}
		}
	}
	$consulta[$x]="commit";
	$x++;
	$con->ejecutarBloque($consulta);
	return $Sueldo;
}

function pagoPorSuplencia($idUsuario,$idNomina,$Sede) //CALCULO NOMINA UGM
{
	global $con;
	$calculo="pagoPorSuplencia";
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
	$consulDatos="SELECT distinct g.idGrupos,g.Plantel,g.idMateria,p.fechaAsignacion,p.fechaBaja,p.situacion,p.idFormularioAccion,
					p.idRegistroAccion,g.idInstanciaPlanEstudio,idAsignacionProfesorGrupo FROM 4520_grupos AS g,
					4519_asignacionProfesorGrupo AS p WHERE g.idGrupos=p.idGrupo AND  p.idUsuario='".$idUsuario."' and g.Plantel='".$Sede."' 
					and p.idParticipacion='45' AND  (('".$fechaIni."'>=p.fechaAsignacion 
							and '".$fechaIni."'<=p.fechaBaja) or ('".$fechaFin."'>=p.fechaAsignacion and '".$fechaFin."'<=p.fechaBaja)
							or ('".$fechaIni."'<=p.fechaAsignacion and '".$fechaFin."'>=p.fechaBaja)) and p.idNominaFiniquito=0
							and  p.fechaAsignacion<=p.fechaBaja ";
	$datos=$con->obtenerFilas($consulDatos);

	while ($row= mysql_fetch_row($datos))// 01-01-2011
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
			$fIniSuplencia=strtotime($row[3]);
			$fFinSuplencia=strtotime($row[4]);
			$fechaIniNomina=strtotime($fechaIni);
			$fchaFinNomina=strtotime($fechaFin);
			$sumarHoras=0;
			while($fechaIniNomina<=$fchaFinNomina)
			{
				$fechaActualNomina=date("Y-m-d",$fechaIniNomina);

				if(!esDiaInhabilEscolar($Sede,$fechaActualNomina))
				{
					if(($fechaIniNomina>=$fIniSuplencia)&&($fechaIniNomina<=$fFinSuplencia))
					{
						$diaSemana=date('w',$fechaIniNomina);
						$obtenerHora="SELECT horaFin,horaInicio FROM 4522_horarioGrupo WHERE idGrupo='".$row[0]."' 
									and dia=".$diaSemana." and '".$fechaActualNomina."'>=fechaInicio and '".$fechaActualNomina."'<=fechaFin";
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
				$fechaIniNomina=strtotime("+1 days",$fechaIniNomina);
			}
		}
	}
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
						
						$consulta[$x]="INSERT INTO 4556_costoHoraDocentes(idUsuario,idGrupo,horas,costoHora,idNomina,fechasesion,horaInicioBloque,calculo)VALUES('".$idUsuario."',
										'".$cveGrupo."','".$valor."','".$ImporteSede."','".$idNomina."','".$bloque."','".$horainicioBloque."','".$calculo."')";
						$x++;
						$Sueldo+=$costoClase;
					}
				}
						$consulta[$x]="INSERT INTO 675_nominaPagada(idNomina,idGrupo,idUsuario)VALUES('".$idNomina."','".$cveGrupo."','".$idUsuario."')";
						$x++;
			}
		}
	}
	$consulta[$x]="commit";
	$x++;
	$con->ejecutarBloque($consulta);
	return $Sueldo;
}

function descuentoPorSuplencia($idUsuario,$idNomina,$plantel) //CALCULO NOMINA UGM
{
	global $con;
	$Sueldo=0;
	$calculo="DescuentoPorSuplencia";
	$consultaNomina="SELECT fechaInicioIncidencias,fechaFinIncidencias,ciclo,quincenaAplicacion,idPerfil FROM 672_nominasEjecutadas WHERE idNomina='".$idNomina."'";
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
  	
	
	$sumarHoras=0;
	$ImporteSede=0;
	$costoClase=0;
	$arrGruposIgnorar=array();
	$arCostosSede=array();
	
		$consulDatos="SELECT distinct g.idGrupos,g.Plantel,g.idMateria,p.fechaAsignacion,p.fechaBaja,p.situacion,p.idFormularioAccion,p.idRegistroAccion,
						g.idInstanciaPlanEstudio FROM 4520_grupos AS g,4519_asignacionProfesorGrupo AS p WHERE g.idGrupos=p.idGrupo 
						AND  p.idUsuario='".$idUsuario."' and Plantel='".$plantel."' AND p.participacionPrincipal='1' AND p.situacion <>4
						and  p.fechaAsignacion<=p.fechaBaja 
						AND (('".$fechaIni."'>=p.fechaAsignacion AND '".$fechaIni."'<=p.fechaBaja) OR ('".$fechaFin."'>=p.fechaAsignacion 
						AND '".$fechaFin."'<=p.fechaBaja) OR ('".$fechaIni."'<=p.fechaAsignacion and '".$fechaFin."'>=p.fechaBaja)) 
						AND p.idNominaFiniquito=0";
	$datos=$con->obtenerFilas($consulDatos);
	while ($row= mysql_fetch_row($datos))// 01-01-2011
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

			$consultaF="SELECT idFormularioAccion,idRegistroAccion,situacion,idAsignacionProfesorGrupo,fechaAsignacion,fechaBaja 
						FROM 4519_asignacionProfesorGrupo WHERE idParticipacion='45' AND (('".$fechaIni."'>=fechaAsignacion 
						AND '".$fechaIni."'<=fechaBaja) OR ('".$fechaFin."'>=fechaAsignacion AND '".$fechaFin."'<=fechaBaja) 
						OR ('".$fechaIni."'<=fechaAsignacion and '".$fechaFin."'>=fechaBaja)) AND idGrupo='".$row[0]."'";
						
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
											and dia=".$diaSemana." and '".$fechaActualNomina."'>=fechaInicio and '".$fechaActualNomina."'<=fechaFin";
								$horas=$con->obtenerFilas($obtenerHora);
								if($con->filasAfectadas>0)
								{
									while ($fila= mysql_fetch_row($horas))
									{
										  $diferencia=(strtotime($fila[0])-strtotime($fila[1]))/60;//strtotime("00:00:00")+strtotime($fila[0])-strtotime($fila[1]);
										  $sumarHoras=$diferencia;
										  if(isset($arCostosSede[$plantel][$row[8]][$row[0]][$fechaActualNomina][$fila[1]]))
												$arCostosSede[$plantel][$row[8]][$row[0]][$fechaActualNomina][$fila[1]]+=$sumarHoras/$vTtiempo;
											else	
												$arCostosSede[$plantel][$row[8]][$row[0]][$fechaActualNomina][$fila[1]]=$sumarHoras/$vTtiempo;
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
				}
			}
		}
	}
		//varDump($arCostosSede);
	$x=0;
	$consulta[$x]="begin";
	$x++;
	foreach($arCostosSede as $plantel=>$arreglo)
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
			}
		}
	}
	$consulta[$x]="commit";
	$x++;
	$con->ejecutarBloque($consulta);
	return $Sueldo;
}

function ObtenerImporteFalta($idUsuario,$idNomina,$Sede) //CALCULO NOMINA UGM
{
	global $con;
	$calculo="ObtenerImporteFalta";
	$Sueldo=0;
	$sumaCosto=0;
	$costoClase=0;
	
	$consultaNominaActual="SELECT fechaInicioIncidencias,fechaFinIncidencias,fechaCorteAsistencia,idPerfil,ciclo,fechaInicioFalta,quincenaAplicacion 
							FROM 672_nominasEjecutadas 
							WHERE idNomina='".$idNomina."'";
	$periodoNomina=$con->obtenerPrimeraFila($consultaNominaActual);
	$fechaInicioFalta=$periodoNomina[5];
	$fechaFinFalta=$periodoNomina[2];
	
  	$fechaIni="NULL";
  	$fechaFin="NULL";
	$arrRecesos=obtenerArregloRecesos();
	//varDump($arrRecesos);
	$consultarFalta="SELECT DISTINCT v.idGrupo,v.fechaFalta,v.horainicial,v.horaFinal,g.idInstanciaPlanEstudio,v.estadoFalta,v.idRegistroJustificacion,
						v.idFalta,v.quincena,v.pagado FROM 4559_controlDeFalta v,4520_grupos g WHERE v.idGrupo  AND v.fechaFalta>='".$fechaInicioFalta."' AND 
						v.fechaFalta<='".$fechaFinFalta."' AND  v.idUsuario='".$idUsuario."' AND g.Plantel='".$Sede."' AND v.estadoFalta IN(0,1,3) 
						AND g.idGrupos=v.idGrupo AND ((pagado='0' and IdNomina='0') OR (pagado='2' and idNomina='".$idNomina."')) ORDER BY v.idGrupo";
	$faltas=$con->obtenerFilas($consultarFalta);
	$arrGruposIgnorar=array();	
	
	$x=0;
	$consultaF[$x]="begin";
	$x++;
	
	while ($row= mysql_fetch_row($faltas))
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
		
		$consultaF[$x]="INSERT INTO 4556_costoHoraDocentes(idUsuario,idGrupo,horas,costoHora,idNomina,fechasesion,horaInicioBloque,calculo)VALUES('".$idUsuario."',
						'".$row[0]."','".$difHoras."','".$ImporteSede."','".$idNomina."','".$row[1]."','".$row[2]."','".$calculo."')";
		$x++;
		$consultaF[$x]="UPDATE 4559_controlDeFalta SET quincena='".$periodoNomina[6]."',pagado='2',idNomina='".$idNomina."' WHERE idFalta='".$row[7]."'";
		$x++;
	}
	$consultaF[$x]="commit";
	$x++;
	$con->ejecutarBloque($consultaF);
	return $sumaCosto;
}

//Verificar si opera

function obtenerImporteFaltaSuplencia($idUsuario,$idNomina,$Sede) //CALCULO NOMINA UGM
{
	global $con;
	$calculo="ImporteFaltaSuplencia";
	$Sueldo=0;
	//$plantel='';
	$consultaNomina="SELECT fechaInicioIncidencias,fechaFinIncidencias,fechaCorteAsistencia,ciclo,quincenaAplicacion FROM 672_nominasEjecutadas 
						WHERE idNomina='".$idNomina."'";
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
					AND  g.idGrupos NOT IN(".$listGrupos.")";//debo cambiar el valor de espera de contrato
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
}

function obtenerCostoProfesor($idUsuario,$codigoUnidad,$idInstanciaPlan,$idGrupo)
{
	global $con;
	$resp=0;
	$costoHora=0;
	$nivelDocente=obtenerNivelDocente($idUsuario);
	$consulPagoGrupo="SELECT txtCostoHora FROM _762_tablaDinamica t1,_761_tablaDinamica t2 WHERE t1.idReferencia=t2.id__761_tablaDinamica 
						AND t2.cmbDocente='".$idUsuario."' AND t1.cmbGrupos='".$idGrupo."'";
	$resPago=$con->obtenerValor($consulPagoGrupo);
	if($resPago>0)
	{
		$costoHora=$resPago;
		return $costoHora;
	}
	else
	{
			$tipoEsquema="SELECT cmbEsquemaCosto FROM _486_tablaDinamica WHERE cmbPlanteles='".$codigoUnidad."' 
							AND cmbInstanciaPlan='".$idInstanciaPlan."'";
			$esquema=$con->obtenerValor($tipoEsquema);
			
			$consultaEsquema="SELECT cmbTipoTabulacion FROM _485_tablaDinamica WHERE id__485_tablaDinamica='".$esquema."'";
			$tipoTabulacion=$con->obtenerValor($consultaEsquema);
			  if($tipoTabulacion!="")
			  {
				  $costoHora=0;
				  switch($tipoTabulacion)
				  {
					  case 1:
							$costo="SELECT costo FROM _485_gridNivelCosto g,_485_tablaDinamica t WHERE g.idReferencia=t.id__485_tablaDinamica 
									AND t.id__485_tablaDinamica='".$esquema."' AND nivel='".$nivelDocente."'";
							$costoHora=$con->obtenerValor($costo);
							return $costoHora;
					  break;
					  case 2:
							$costo="SELECT txtCostoHoraFijo FROM _485_tablaDinamica WHERE id__485_tablaDinamica='".$esquema."'";
							$costoHora=$con->obtenerValor($costo);
							return $costoHora;
					  break;
				  }
			  }
	}
}

function obtenerNivelDocente($idUsuario)
{
	global $con;
	$consultaImporte="SELECT MAX(intPrioridad) FROM _246_tablaDinamica WHERE codigo IN(SELECT n.cmbNivelEstudio FROM _262_tablaDinamica AS n 
		  					WHERE n.responsable='".$idUsuario."' AND n.idEstado='3' AND cmbNivelEstudio<>'4') ";
	$valor=$con->obtenerValor($consultaImporte);
	if(!$valor)	
	{
		$valor=1;
	}
		$obtenerNivel="SELECT codigo FROM _246_tablaDinamica WHERE intPrioridad= '".$valor."'";
		$nivel=$con->obtenerValor($obtenerNivel); 
		return $nivel;
}

function obtenerHorasTotalMateriaPeriodo($idGrupo)
{
	global $con;
	$horas=0;
	$sumarHoras=0;
	$consulta="SELECT fechaInicio,fechaFin,idInstanciaPlanEstudio FROM 4520_grupos WHERE idGrupos='".$idGrupo."'";
	$filasFechas=$con->obtenerPrimeraFila($consulta);
	$fechaIni="NULL";
	$fechaFin="NULL";
	if($filasFechas)
	{
		$fechaIni=$filasFechas[0];
		$fechaFin=$filasFechas[1];
	}
		$fInicioGrupo=strtotime($fechaIni);
		$fFinGrupo=strtotime($fechaFin);
		
		$obtenerTiempo="SELECT dracionHora FROM _472_tablaDinamica WHERE idReferencia='".$filasFechas[2]."'";
		$valorTiempo=$con->obtenerValor($obtenerTiempo);
		$vTtiempo=60;
		if($valorTiempo)
		{	
			$vTtiempo=$valorTiempo;
		}
		
	while($fInicioGrupo<=$fFinGrupo)
	{
			$diaSemana=date('w',$fInicioGrupo);
			$obtenerHora="SELECT horaFin,horaInicio FROM 4522_horarioGrupo WHERE idGrupo='".$idGrupo."' 
						and dia=".$diaSemana."";
			$horas=$con->obtenerFilas($obtenerHora);
			if($con->filasAfectadas>0)
			{
				while ($fila= mysql_fetch_row($horas))
				{
					$diferencia=strtotime("00:00:00")+strtotime($fila[0])-strtotime($fila[1]);
					$sumarHoras+=((date("H",$diferencia)*60)+date("i",$diferencia));
				}
			}
		$fInicioGrupo=strtotime("+1 days",$fInicioGrupo);
	}
	$horas=$sumarHoras/$vTtiempo;
	return $horas;
}

function obtenerDiaFestivo($fechaNomina,$plantel)//1328508000
{
	global $con;
	$resp=-1;
	$fechaNomina2=date("Y-m-d",$fechaNomina);
	$consulFecha="SELECT idFechaCalendario FROM 4525_fechaCalendarioDiaHabil WHERE '".$fechaNomina2."' BETWEEN fechaInicio AND fechaFin 
					AND plantel='' and afectaClases=1";
					//echo $consulFecha;
	$fecha=$con->obtenerValor($consulFecha);
	if(!$fecha)
	{
			$consulFecha="SELECT idFechaCalendario FROM 4525_fechaCalendarioDiaHabil WHERE '".$fechaNomina2."' BETWEEN fechaInicio AND fechaFin 
						AND plantel='".$plantel."' and afectaClases=1";
			$fecha=$con->obtenerValor($consulFecha);
			if(!$fecha)
			{
				return -1;
			}
			else
				return 1;
	}
	else
		return 1;
}

function obtenerDiasTrabajadosDocente($idUsuario,$plantel,$idNomina)//ugmNomina
{
	global $con;
	$dias=0;
	$consultaNomina="SELECT fechaInicioIncidencias,fechaFinIncidencias FROM 672_nominasEjecutadas WHERE idNomina='".$idNomina."'";
	$periodoNomina=$con->obtenerPrimeraFila($consultaNomina);
  	$fechaIni="NULL";
  	$fechaFin="NULL";

	if($periodoNomina)
	{
		$fechaIni=$periodoNomina[0];
		$fechaFin=$periodoNomina[1];
	}
	
	$fAlta="SELECT MIN(fechaAsignacion) FROM 4519_asignacionProfesorGrupo a,4520_grupos g WHERE  a.idGrupo=g.idGrupos AND a.situacion='1' 
					AND a.idUsuario='".$idUsuario."' AND g.Plantel='".$plantel."'";
	$fechaAlta=$con->obtenerValor($fAlta);
	
	$fMaxima="SELECT MAX(g.fechaFin) FROM 4519_asignacionProfesorGrupo a,4520_grupos g WHERE  a.idGrupo=g.idGrupos AND a.situacion='1' 
				AND a.idUsuario='".$idUsuario."' AND g.Plantel='".$plantel."'";
	$fechaMaxima=$con->obtenerValor($fMaxima);
	if(!$fMaxima)	
	{
		$conFechaBaja="SELECT MAX(a.fechaBaja) FROM 4519_asignacionProfesorGrupo a,4520_grupos g WHERE  a.idGrupo=g.idGrupos AND a.situacion='0' 
						AND a.idUsuario='".$idUsuario."' AND g.Plantel='".$plantel."'";
		$fechaMaxima=$con->obtenerValor($conFechaBaja);
	}
	if($fechaAlta<$fechaFin)
	{
	
		if($fechaAlta>$fechaIni)
		{
			$fechaIniNomina=$fechaAlta;
		}
		else
		{
			$fechaIniNomina=$fechaIni;
		}
		if($fechaMaxima<$fechaFin)
		{
			$fechaFinNomina=$fechaMaxima;
		}
		else
		{
			$fechaFinNomina=$fechaFin;
		}
			$dias=obtenerDiferenciaDias($fechaIniNomina,$fechaFinNomina)+1;
	}
	return $dias;
}

function obtenerDeduciones($idUsuario,$idNomina,$idDeduccion,$Plantel,$baseGravable)
{
	global $con;
	$sumaImporte=0;
	$consultaNomina="SELECT fechaInicioIncidencias,fechaFinIncidencias,ciclo,quincenaAplicacion FROM 672_nominasEjecutadas WHERE idNomina='".$idNomina."'";
	$periodoNomina=$con->obtenerPrimeraFila($consultaNomina);
  	$fechaIni="NULL";
  	$fechaFin="NULL";

	if($periodoNomina)
	{
		$fechaIni=$periodoNomina[0];
		$fechaFin=$periodoNomina[1];
		$periodoCiclo=(($periodoNomina[2]*24)+($periodoNomina[3]*1));
	}
	$consultaD="SELECT id__460_tablaDinamica,txtValorDescuento,radTipoApliacacion,radTipoValor FROM _460_tablaDinamica WHERE cmbUsuario='".$idUsuario."' 
				AND cmbConceptos='".$idDeduccion."' AND radSituacion='1' AND codigoInstitucion='".$Plantel."' AND idEstado='2'
				AND (((cmbCiclo*24)+(cmbQuincenaNomina*1))<='".$periodoCiclo."' OR cmbNominaEspecifica='".$idNomina."')";
	$deducciones=$con->obtenerFilas($consultaD);
	$incidencia=array();
	while ($row= mysql_fetch_row($deducciones))
	{
		$valor=0;
		switch($row[2])
		{//Tipo de aplicacion descuento nomina continua
			case 1:
					$obtenerDatos="SELECT id__460_tablaDinamica,txtValorDescuento,radTipoValor FROM _460_tablaDinamica 
									WHERE id__460_tablaDinamica='".$row[0]."' AND (cmbQuincenaNomina<='".$periodoNomina[3]."' OR cmbNominaEspecifica<='".$idNomina."')";
					$importe=$con->obtenerPrimeraFila($obtenerDatos);
					if($importe[2]!=2)
					{
						$valor=$importe[1];
					}
					else
					{
						$valor=$baseGravable*($importe[1]/100);
					}
					
						$obj=array();
						$obj[0]=$importe[0];
						$obj[1]=$importe[1];
						$obj[2]=$importe[2];
						$obj[3]=$valor;
						array_push($incidencia,$obj);
			break;
			case 2://Tipo de aplicacion descuento nomina especifica
					$consulta2="SELECT id__460_tablaDinamica,txtValorDescuento,radTipoValor FROM _460_tablaDinamica WHERE  id__460_tablaDinamica='".$row[0]."' 
								AND (((cmbCiclo*24)+(cmbQuincenaNomina*1))='".$periodoCiclo."' OR cmbNominaEspecifica='".$idNomina."')";
					$dato=$con->obtenerPrimeraFila($consulta2);
					if($dato[2]!=2)
					{
						$valor=$dato[1];
					}
					else
					{
						$valor=$baseGravable*($dato[1]/100);
					}
						
					$obj=array();
					$obj[0]=$dato[0];
					$obj[1]=$dato[1];
					$obj[2]=$dato[2];
					$obj[3]=$valor;
					array_push($incidencia,$obj);
			break;
		}
	}
	foreach ($incidencia as $actual)
	{
		$idRegistro=$actual[0];
		$importe=$actual[3];
		$sumaImporte+=$importe;
		$insertar="INSERT INTO 4555_descuentoDeducciones(idUsuario,importe,idNomina,idConcepto,idReferencia,plantel)VALUES('".$idUsuario."',
					'".$importe."','".$idNomina."','".$idDeduccion."','".$idRegistro."','".$Plantel."')";
		$con->ejecutarConsulta($insertar);
	}
	return $sumaImporte;
}

function reposicionDisponibilidad($idRegistro,$idFormulario)
{
	global $con;
	
	
	
	$arrNotificaciones=array();
	$x=0;
	$query[$x]="begin";
	$x++;
	
	
	$obtenerDatos="SELECT codigoUnidad,cmbDocentes,dteFechaInicial,dteFechaFinal,tipoComision,idRegistroModuloSesionesClase,fechaCreacion,responsable
				FROM _489_tablaDinamica AS uno, 4560_registroModuloSesionesClase AS r 
				WHERE r.idReferencia=uno.id__489_tablaDinamica AND r.idFormulario='".$idFormulario."' AND  uno.id__489_tablaDinamica='".$idRegistro."'";
	$dato=$con->obtenerPrimeraFila($obtenerDatos);
	
	$consulta1="SELECT idGrupo,horaInicioBloque,horaFinBloque,fechaSesion,noSesion,idSesionesClaseComision FROM 4561_sesionesClaseModulo WHERE idReferencia='".$dato[5]."' and fechaSesion>='".$dato[2]."' and fechaSesion<='".$dato[3]."'";	
	$valor=$con->obtenerFilas($consulta1);
	while ($row= mysql_fetch_row($valor))
	{
		$situacion=1;
		$resSituacion=situacionJustificacionComision($row[5]);
		$arrRes=explode("|",$resSituacion);
		
		
		switch($arrRes[0])
		{
			case 0:
				$situacion=1;
			break;
			case 5:
			case 6:
				$situacion=1;
				$o=array();
				$o["idNomina"]=$arrRes[1];
				
				$consulta="SELECT nombreGrupo FROM 4520_grupos WHERE idGrupos=".$row[0];
				$nombreGrupo=$con->obtenerValor($consulta);
				
				$o["notificacion"]="Se ha autorizado una comisión para el profesor: ".cv(obtenerNombreUsuario($dato[1]))." justificando la falta del grupo: ".cv($nombreGrupo)." el día: ".
								date("d/m/Y",strtotime($row[3]))." de las ".date("H:i",strtotime($row[1]))." a las ".date("H:i",strtotime($row[2]));
				array_push($arrNotificaciones,$o);
			break;
			default:
				$situacion=$arrRes[0];
			break;
				
		}
		
		//varDump($arrRes);
		if($situacion==1)
			registrarComision($row[0],$row[3],$row[1],$row[2],$dato[1],$dato[0],$dato[4],$idRegistro);
		
		
		$query[$x]="UPDATE 4561_sesionesClaseModulo SET aplicado=".$situacion." WHERE idSesionesClaseComision=".$row[5];
		$x++;
	}
	$query[$x]="delete from 4561_sesionesClaseModulo where idReferencia=".$dato[5]." and aplicado=0";
	$x++;
	$query[$x]="commit";
	$x++;
	if($con->ejecutarBloque($query))
	{
		foreach($arrNotificaciones as $n)
		{
			registrarNotificacionNomina($n["idNomina"],$n["notificacion"]);
		}
		return true;	
	}
}

function obtenerImporteReposicion($idUsuario,$plantel,$idNomina)
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
	
	$consultaSesiones="SELECT idSesion,fechaSesion,horario,s.idGrupo FROM 4530_sesiones s,4520_grupos g,4519_asignacionProfesorGrupo a 
						WHERE g.idGrupos=s.idGrupo AND g.idGrupos=a.idGrupo AND g.Plantel='".$plantel."' 
						AND fechaSesion>='".$fechaIni."' AND fechaSesion<='".$fechaFin."' AND a.idUsuario='".$idUsuario."'  
						AND tipoSesion='15' AND (('".$fechaIni."'>=a.fechaAsignacion AND '".$fechaIni."'<=a.fechaBaja) 
						or ('".$fechaFin."'>=a.fechaAsignacion AND '".$fechaFin."'<=a.fechaBaja) 
						or ('".$fechaIni."'<=a.fechaAsignacion and '".$fechaFin."'>=a.fechaBaja)) 
						AND a.idNominaFiniquito=0 and  a.fechaAsignacion<=a.fechaBaja
						ORDER BY fechaSesion,horario";
	$datosSesiones=$con->obtenerFilas($consultaSesiones);
		$horaIniciobloque="";
		$horaFinBloque="";
		$arCostosSede=array();
		$arCostosSede2=array();
		
	while ($row= mysql_fetch_row($datosSesiones))
	{
		$sumarHoras=0;
		$horario=explode("-",$row[2]);
		$horaIniciobloque=$horario[0];
		$horaFinBloque=$horario[1];
			
		$consutaTipoReposicion="SELECT idGrupo FROM 4561_sesionesClaseModulo WHERE idGrupo='".$row[3]."' 
				AND horaInicioBloque='".$horaIniciobloque."' AND fechaSesion='".$row[1]."'";
		$encuentra=$con->obtenerValor($consutaTipoReposicion);
		if($encuentra)
			continue;
		
			$diferencia=(strtotime($horaFinBloque)-strtotime($horaIniciobloque))/60;
			$sumarHoras+=$diferencia;
			
			$obtenerInstanciaPlan="SELECT idInstanciaPlanEstudio FROM 4520_grupos where idGrupos='".$row[3]."'";
			$idPlan=$con->obtenerValor($obtenerInstanciaPlan);
			
			$obtenerNivelplan="SELECT nivelPlanEstudio FROM 4500_planEstudio WHERE idPlanEstudio IN(SELECT DISTINCT idPlanEstudio 
				FROM 4520_grupos WHERE idInstanciaPlanEstudio='".$idPlan."')";
			$idNivelPlan=$con->obtenerValor($obtenerNivelplan);
			
			$obtenerNivelplan="SELECT nivelPlanEstudio FROM 4500_planEstudio WHERE idPlanEstudio IN(SELECT DISTINCT idPlanEstudio 
						FROM 4520_grupos WHERE idInstanciaPlanEstudio='".$row[3]."')";
		$idNivelPlan=$con->obtenerValor($obtenerNivelplan);
		
		$vTtiempo=obtenenerDuracionHoraGrupo($row[3]);

			
					
			if(isset($arCostosSede2[$plantel][$idPlan][$row[3]][$row[1]][$horaIniciobloque]))//sede,Instancia,Grupo,fecha,HoraInicio
				$arCostosSede2[$plantel][$idPlan][$row[3]][$row[1]][$horaIniciobloque]+=$diferencia/$vTtiempo;
			else	
				$arCostosSede2[$plantel][$idPlan][$row[3]][$row[1]][$horaIniciobloque]=$diferencia/$vTtiempo;
		//}
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
						
						$GuardarCosto="INSERT INTO 4556_costoHoraDocentes(idUsuario,idGrupo,horas,costoHora,idNomina,
							fechasesion,horaInicioBloque,calculo)VALUES('".$idUsuario."','".$cveGrupo."','".$valor."',
							'".$ImporteSede."','".$idNomina."','".$bloque."','".$horainicioBloque."','".$calculo."')";
						$con->ejecutarConsulta($GuardarCosto);
						$Sueldo+=$costoClase;
					}
				}
			}
		}
	}
	return $Sueldo;
}

function obtenerUsuariosNominaExt($ciclo,$periodo)
{
	global $con;
	$listCandidatos="";
	$consulta="SELECT DISTINCT(cmbDocente) FROM _499_tablaDinamica WHERE cmbCiclo='".$ciclo."' AND cmbQuincenaNomina='".$periodo."'";
	$res=$con->obtenerListaValores($consulta,"'");	
	return $res;
}

function ImporteDiaFestivo($idUsuario,$idNomina,$plantel) //CALCULO NOMINA UGM
{
	global $con;
	$arrRecesos=obtenerArregloRecesos();
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
			WHERE n.idGrupo=g.idGrupos AND n.idNomina=ne.idNomina AND g.Plantel='".$plantel."' AND ne.etapa='1000' 
			AND ne.ciclo='".$periodoNomina[2]."' AND ne.quincenaAplicacion='".$periodoNomina[3]."' AND n.idUsuario='".$idUsuario."'";
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
			$consulDatos="SELECT distinct g.idGrupos,g.Plantel,g.idMateria,p.fechaAsignacion,p.fechaBaja,p.situacion,
				p.idFormularioAccion,p.idRegistroAccion,g.idInstanciaPlanEstudio,p.idParticipacion 
				FROM 4520_grupos AS g,4519_asignacionProfesorGrupo AS p WHERE g.idGrupos=p.idGrupo AND  p.idUsuario='".$idUsuario."' 	
				and Plantel='".$plantel."' AND p.situacion NOT IN(4) AND (('".$fechaIni."'>=p.fechaAsignacion 
				and '".$fechaIni."'<=p.fechaBaja) or ('".$fechaFin."'>=p.fechaAsignacion 
				and '".$fechaFin."'<=p.fechaBaja) or ('".$fechaIni."'<=p.fechaAsignacion and '".$fechaFin."'>=p.fechaBaja)) 
				AND g.idGrupos NOT IN(".$listGrupos.")"; 
			$datos=$con->obtenerFilas($consulDatos);
			$arrGruposIgnorar=array();
			while ($row= mysql_fetch_row($datos))
			{ 
				if(isset($arrGruposIgnorar[$row[0]."_".$row[3]."_".$row[4]]))
					continue;
				else
					$arrGruposIgnorar[$row[0]."_".$row[3]."_".$row[4]]=1;
				$obtenerNivelplan="SELECT nivelPlanEstudio FROM 4500_planEstudio 
					WHERE idPlanEstudio IN(SELECT DISTINCT idPlanEstudio FROM 4520_grupos 
					WHERE idInstanciaPlanEstudio='".$row[8]."')";
				$idNivelPlan=$con->obtenerValor($obtenerNivelplan);
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
						$tieneSuplencia=grupoTieneSuplencia($idUsuario,$row[0],$fechaActualNomina);
						if($tieneSuplencia!=1)
						{
							if(esDiaInhabilEscolarNomina($plantel,$fechaActualNomina))
							{
								
								if(($fechaIniNomina>=$fInicioGrupo)&&($fechaIniNomina<=$fFinGrupo)) // && = y
								{
									  $diaSemana=date('w',$fechaIniNomina);
									  
										$obtenerDias="SELECT idOpcion FROM _503_radDiaSemana AS d,_503_tablaDinamica AS t 
											WHERE d.idPadre=t.id__503_tablaDinamica AND t.cmbPlanteles='".$plantel."' 
											and d.idOpcion='".$diaSemana."' ORDER BY d.idOpcion";
										$estaDia=$con->obtenerPrimeraFila($obtenerDias);
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
											$obtenerHora="SELECT horaInicioBloque,horaFinBloque 
												FROM 9105_controlAsistenciaDiaFestivo WHERE  idGrupo='".$row[0]."' 
												and fecha='".$fechaActualNomina."'  ";
											$horas=$con->obtenerFilas($obtenerHora);
											  if($con->filasAfectadas>0)
											  {
												  while ($fila= mysql_fetch_row($horas))
												  {
													  $diferencia=obtenerNumeroHorasBloque($row[0],$fila[0],$fila[1],$plantel,$arrRecesos,2);
//													  if($row[0]=='13672'&&$diaSemana=='5')
//													  {
//													  	$diferencia=$diferencia+10;
//													  }
														
		
													  $sumarHoras+=$diferencia;

														if(isset($arCostosSede2[$plantel][$row[8]][$row[0]][$fechaActualNomina][$fila[0]]))//sede,Instancia,Grupo,fecha,HoraInicio
															$arCostosSede2[$plantel][$row[8]][$row[0]][$fechaActualNomina][$fila[0]]+=$diferencia/$vTtiempo;
														else	
															$arCostosSede2[$plantel][$row[8]][$row[0]][$fechaActualNomina][$fila[0]]=$diferencia/$vTtiempo;
												  }
											  }
											  else
											  {
													//echo " 2 Grupo ".$row[0]." fechaActual ".$fechaActualNomina." dia ".$diaSemana."<br>";											
												  
												  $diferencia=obtenerHorasFestivoNoAplicadas($diaSemana,$row[0],$fechaActualNomina,$plantel,$arrRecesos);
												  if($diferencia>0)
												  {
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
							//echo "FEcha :".$bloque." Grupo :".$cveGrupo." horas :".$valor." Costo  :".$costoClase."<br>";
							$GuardarCosto="INSERT INTO 4556_costoHoraDocentes(idUsuario,idGrupo,horas,costoHora,
								idNomina,fechasesion,horaInicioBloque,calculo)VALUES('".$idUsuario."','".$cveGrupo."',
								'".$valor."','".$ImporteSede."','".$idNomina."','".$bloque."','".$horainicioBloque."','".$calculo."')";
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

function diasNominas($idNomina)
{
	global $con;
	$dias=0;
	$consultaNomina="SELECT fechaInicioIncidencias,fechaFinIncidencias,ciclo,quincenaAplicacion FROM 672_nominasEjecutadas WHERE idNomina='".$idNomina."'";
	$periodoNomina=$con->obtenerPrimeraFila($consultaNomina);
  	$fechaIni="NULL";
  	$fechaFin="NULL";

	if($periodoNomina)
	{
		$fechaIni=$periodoNomina[0];
		$fechaFin=$periodoNomina[1];
	}

    $dias=obtenerDiferenciaDias($fechaIni,$fechaFin)+1;
	return $dias;
}

function validarSDI($idUsuario,$ciclo,$bimestre,$responsable,$idRegistro)
{
	global $con;
	$res="1|";
	
	$consultaPlantel="SELECT Institucion FROM 801_adscripcion WHERE idUsuario='".$responsable."'";
	$plantel=$con->obtenerValor($consultaPlantel);
	
	$consulNombre="SELECT Nom,Paterno,Materno FROM 802_identifica WHERE idUsuario='".$idUsuario."'";
	$docente=$con->obtenerPrimeraFila($consulNombre);
	$nombreDocente=$docente[0]." ".$docente[1]." ".$docente[2];
	if($idRegistro==-1)
	{
	$consulta="SELECT * FROM _497_tablaDinamica WHERE cmbCiclo='".$ciclo."' AND cmbDocente='".$idUsuario."' AND cmbBimestre='".$bimestre."' 
				AND codigoInstitucion='".$plantel."'";
	}
	else
	{
		$consulta="SELECT * FROM _497_tablaDinamica WHERE cmbCiclo='".$ciclo."' AND cmbDocente='".$idUsuario."' AND cmbBimestre='".$bimestre."' 
					AND codigoInstitucion='".$plantel."' AND id__497_tablaDinamica<>'".$idRegistro."'";
	}
	$datos=$con->obtenerPrimeraFila($consulta);
	if($datos)
	{
		$res="Ya existe SDI para ".$nombreDocente;
		return $res;
	}
	return $res;
}

function evaluacionDocentes($registro,$formulario)
{
	global $con;
	$fecha=date("Y-m-d");
	  $consultaP="SELECT cmbPeriodo,ciclo FROM _440_tablaDinamica WHERE id__440_tablaDinamica='".$registro."'";
	$datos=$con->obtenerPrimeraFila($consultaP);
	
	$consulta1="SELECT fechaCreacion,responsable,codigoInstitucion FROM _435_tablaDinamica WHERE idReferencia='".$registro."'";
	$resul=$con->obtenerPrimeraFila($consulta1);
	
	$docentes="SELECT DISTINCT a.idUsuario FROM 4519_asignacionProfesorGrupo a,4520_grupos AS g WHERE a.idGrupo=g.idGrupos 
				AND g.plantel='".$resul[2]."' AND fechaAsignacion<='".$fecha."' AND fechaBaja>='".$fecha."' ORDER BY a.idUsuario";
	$idUsuario=$con->obtenerFilas($docentes);
		$x=0;
		$consulta[$x]="begin";
		$x++;
		$consulta[$x]="INSERT INTO _437_tablaDinamica(idReferencia,fechaCreacion,responsable,codigoUnidad,codigoInstitucion)VALUES('".$registro."','".$resul[0]."',
						'".$resul[1]."','".$resul[2]."','".$resul[2]."')";
		$x++;
		$consulta[$x]="set @id=(select last_insert_id())";
		$x++;
		$consulta[$x]="UPDATE _437_tablaDinamica SET codigo=@id WHERE id__437_tablaDinamica=@id";
		$x++;
		while ($row= mysql_fetch_row($idUsuario))
		{
			$consulta[$x]="INSERT INTO _437_dtgAcademica(idReferencia,nombreDocente)VALUES(@id,'".$row[0]."')";
			$x++;
		}
		$numero=mysql_num_rows($idUsuario);
		if($numero>0)
		{
			mysql_data_seek($idUsuario,0);
		}
		$consulta[$x]="INSERT INTO _438_tablaDinamica(idReferencia,fechaCreacion,responsable,codigoUnidad,codigoInstitucion)VALUES('".$registro."','".$resul[0]."',
						'".$resul[1]."','".$resul[2]."','".$resul[2]."')";
		$x++;
		$consulta[$x]="set @idServicios=(select last_insert_id())";
		$x++;
		$consulta[$x]="UPDATE _438_tablaDinamica SET codigo=@idServicios WHERE id__438_tablaDinamica=@idServicios";
		$x++;
		while ($row= mysql_fetch_row($idUsuario))
		{
			$consulta[$x]="INSERT INTO _438_dtgServiciosEscolares(idReferencia,nombreProfesor)VALUES(@idServicios,'".$row[0]."')";
			$x++;
		}
		$numero1=mysql_num_rows($idUsuario);
		if($numero1>0)
		{
			mysql_data_seek($idUsuario,0);
		}
		
		$consulta[$x]="INSERT INTO _439_tablaDinamica(idReferencia,fechaCreacion,responsable,codigoUnidad,codigoInstitucion)VALUES('".$registro."','".$resul[0]."',
						'".$resul[1]."','".$resul[2]."','".$resul[2]."')";
		$x++;
		$consulta[$x]="set @idFinanzas=(select last_insert_id())";
		$x++;
		$consulta[$x]="UPDATE _439_tablaDinamica SET codigo=@idFinanzas WHERE id__439_tablaDinamica=@idFinanzas";
		$x++;
		while ($row= mysql_fetch_row($idUsuario))
		{
			$consulta[$x]="INSERT INTO _439_dtgEvaluacionFinanzas(idReferencia,nombreProfesor)VALUES(@idFinanzas,'".$row[0]."')";
			$x++;
		}
		$consulta[$x]="commit";
		$x++;
		$con->ejecutarBloque($consulta);
}

function obtenerDocenteAsimilados($idNomina,$plantel)
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
				AND a.situacion<>'4' AND (('".$fechaIni."'>=a.fechaAsignacion 
				AND '".$fechaIni."'<=a.fechaBaja) OR ('".$fechaFin."'>=a.fechaAsignacion AND '".$fechaFin."'<=a.fechaBaja) 
				OR ('".$fechaIni."'<=a.fechaAsignacion AND '".$fechaFin."'>=a.fechaBaja)) AND idUsuario NOT IN(".$listAdmon.") 
				AND a.idUsuario NOT IN(".$listProfes.") AND a.idUsuario<>'2' ORDER BY a.idUsuario";
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

function obtenerDocenteProfesionales($idNomina,$plantel)
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
				AND a.situacion<>'4' AND (('".$fechaIni."'>=a.fechaAsignacion 
				AND '".$fechaIni."'<=a.fechaBaja) OR ('".$fechaFin."'>=a.fechaAsignacion AND '".$fechaFin."'<=a.fechaBaja) 
				OR ('".$fechaIni."'<=a.fechaAsignacion AND '".$fechaFin."'>=a.fechaBaja)) AND idUsuario NOT IN(".$listAdmon.") 
				AND a.idUsuario IN(".$listProfes.") AND a.idUsuario<>'2' ORDER BY a.idUsuario";
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

function buscarFaltaPendientes($idUsuario,$idNomina,$Sede) //CALCULO NOMINA UGM
{
	global $con;
	$calculo="ImporteFaltaNoPagadas";
	$Sueldo=0;
	$sumaCosto=0;
	$costoClase=0;
	
	$consultaNominaActual="SELECT fechaInicioIncidencias,fechaFinIncidencias,fechaCorteAsistencia,idPerfil,ciclo,fechaInicioFalta,quincenaAplicacion 
							FROM 672_nominasEjecutadas WHERE idNomina='".$idNomina."'";
	$periodoNomina=$con->obtenerPrimeraFila($consultaNominaActual);
	$fechaInicioFalta=$periodoNomina[5];
	$fechaFinFalta=$periodoNomina[2];
	
  	$fechaIni="NULL";
  	$fechaFin="NULL";
	$arrRecesos=obtenerArregloRecesos();
	//varDump($arrRecesos);
		
		$consultarFalta="SELECT DISTINCT v.idGrupo,v.fechaFalta,v.horainicial,v.horaFinal,g.idInstanciaPlanEstudio,v.estadoFalta,v.idRegistroJustificacion,
							v.idFalta,v.quincena,v.pagado FROM 4559_controlDeFalta v,4520_grupos g WHERE v.fechaFalta>='".$fechaInicioFalta."' AND 
							v.fechaFalta<='".$fechaFinFalta."' AND  v.idUsuario='".$idUsuario."' AND g.Plantel='".$Sede."' AND v.estadoFalta IN(0,1,3) 
							AND g.idGrupos=v.idGrupo AND pagado='0'  AND (v.idNomina <>'".$idNomina."' OR v.idNomina IS NULL) ORDER BY v.idGrupo";
	$faltas=$con->obtenerFilas($consultarFalta);
	$arrGruposIgnorar=array();	
	
	$x=0;
	$consultaF[$x]="begin";
	$x++;
	
	while ($row= mysql_fetch_row($faltas))
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
		
						$consultaF[$x]="INSERT INTO 4556_costoHoraDocentes(idUsuario,idGrupo,horas,costoHora,idNomina,fechasesion,horaInicioBloque,calculo)VALUES('".$idUsuario."',
										'".$row[0]."','".$difHoras."','".$ImporteSede."','".$idNomina."','".$row[1]."','".$row[2]."','".$calculo."')";
						$x++;
	}
	$consultaF[$x]="commit";
	$x++;
	$con->ejecutarBloque($consultaF);
	
	return $sumaCosto;
}

function impuestoQuincUGM($baseGrav,$Ciclo,$dato)//UGM calculo de impuesto de nomina
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

function pagarDiaSesion($fecha,$sede,$usuario)
{
	global $con;
	$consulta="SELECT cmbPagarDiasFestivos FROM _456_tablaDinamica";
	$valor=$con->obtenerValor($consulta);
	if($valor=='1')
	{
		return true;
	}
	else
	{
		return false;
	}
}

function obtenerPercepcionEspeciales($idUsuario,$idNomina,$idConcepto,$plantel)
{
	global $con;
	$importe=0;
	$fechaNomina="SELECT fechaInicioIncidencias,fechaFinIncidencias,ciclo,quincenaAplicacion FROM 672_nominasEjecutadas WHERE idNomina='".$idNomina."'";
	$periodoNomina=$con->obtenerPrimeraFila($fechaNomina);
	if($periodoNomina)
	{
		$fechaIni=$periodoNomina[0];
		$fechaFin=$periodoNomina[1];
		$periodoCiclo=(($periodoNomina[2]*24)+($periodoNomina[3]*1));
	}

	$longQuincena=strlen($periodoNomina[3]);
	if($longQuincena=='1')
	{
		$quincena='0'.$periodoNomina[3];
	}
	else
	{
		$quincena=$periodoNomina[3];
	}
	
	$consulta="SELECT radNominaAplicar,id__451_tablaDinamica FROM _451_tablaDinamica WHERE cmbCiclo='".$periodoNomina[2]."' AND cmbUsuarios='".$idUsuario."' 
				AND codigoInstitucion='".$plantel."' AND cmbConceptos='".$idConcepto."' AND (cmbQuincenaNomina='".$quincena."' OR cmbNomina='".$idNomina."') 
				AND idEstado='2'";
	$res=$con->obtenerFilas($consulta);
	while ($fila= mysql_fetch_row($res))
	{
					$consultaImporte="SELECT txtImporte FROM _451_tablaDinamica WHERE id__451_tablaDinamica='".$fila[1]."'";
					$valor=$con->obtenerValor($consultaImporte);
					$importe+=$valor;
	}
	return $importe;
}

function grupoTieneSuplencia($idUsuario,$idGrupo,$fecha)
{
	global $con;
	$tieneSuplencia=0;//No
	
	$consulta="SELECT idParticipacion FROM 4519_asignacionProfesorGrupo WHERE idGrupo='".$idGrupo."' AND idUsuario='".$idUsuario."' 
				AND '".$fecha."' BETWEEN fechaAsignacion AND fechaBaja";
	$resp=$con->obtenerValor($consulta);
	if($resp=='37')
	{
		$consulta1="SELECT idAsignacionProfesorGrupo FROM 4519_asignacionProfesorGrupo WHERE idGrupo='".$idGrupo."' AND idParticipacion='45'
					AND '".$fecha."' BETWEEN fechaAsignacion AND fechaBaja";
		$respu=$con->obtenerValor($consulta1);
		if($respu!="")
		{
			$tieneSuplencia=1;
		}
		else
		{
			$tieneSuplencia=0;
		}
	}
	return $tieneSuplencia;
}

	function esDiaInhabilEscolarNomina($plantel,$fecha)
	{
		global $con;
		
		$consulta="SELECT idFechaCalendario,afectaPago FROM 4525_fechaCalendarioDiaHabil WHERE '".$fecha."' 
				BETWEEN FechaInicio AND fechaFin and (plantel is null or plantel ='' or plantel='".$plantel."')";
		$fila=$con->obtenerPrimeraFila($consulta);
		if($fila)
		{
			//_503_tablaDinamica
			switch($fila[1])
			{
				case 0:
						return false;
				break;
				case 1:
						return true;
				break;
			}
		}
		else
		{
			return false;
		}
	}

function obtenerHorasFestivoNoAplicadas($dia,$grupo,$fecha,$plantel,$arreglo)
{
	global $con;
	$diferencia=0;
	$consulta="SELECT horaInicio,horaFin FROM 4522_horarioGrupo WHERE idGrupo='".$grupo."' AND dia='".$dia."' 
				AND '".$fecha."' BETWEEN fechaInicio AND fechaFin";
	$res=$con->obtenerPrimeraFila($consulta);
	if($res)
	{
		$horaInicio=$res[0];
		$horaFin=$res[1];
		$diferencia=obtenerNumeroHorasBloque($grupo,$horaInicio,$horaFin,$plantel,$arrRecesos,2);
		
	}
	return $diferencia;
}
?>