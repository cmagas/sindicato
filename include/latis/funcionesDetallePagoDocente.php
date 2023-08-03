<?php
include_once("latis/funcionesNeotrai.php");

function diasNominasD($idNomina)
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

function totalPercepcionD($idUsuario,$idNomina,$Sede,$tipo) //CALCULO NOMINA UGM
{
	//Tipo=retornar importe 2=Retornar Arreglo
	global $con;
	$calculo="totalPercepcion";
	$Sueldo=0;
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
	$arCostosSede2=array();	
  	$consulCiclo="SELECT idCiclo FROM 4526_ciclosEscolares WHERE '".$fechaIni."' BETWEEN fechaInicio AND fechaTermino 
  					AND '".$fechaFin."' BETWEEN fechaInicio AND fechaTermino";
	$idCiclo=$con->obtenerValor($consulCiclo);
	
	$consultaNominaPagada="SELECT n.idGrupo FROM 675_nominaPagada n,4520_grupos g,672_nominasEjecutadas ne WHERE n.idGrupo=g.idGrupos 
							AND n.idNomina=ne.idNomina AND g.Plantel='".$Sede."' AND ne.etapa='1000' AND ne.ciclo='".$periodoNomina[2]."' 
							AND ne.quincenaAplicacion='".$periodoNomina[3]."' AND n.idUsuario='".$idUsuario."'";
	$listGrupos=$con->obtenerListaValores($consultaNominaPagada);
	if($listGrupos=="")
		$listGrupos=-1;

		$sumarHoras=0;
		$ImporteSede=0;
		$costoClase=0;
			$consulDatos="SELECT distinct g.idGrupos,g.Plantel,g.idMateria,p.fechaAsignacion,p.fechaBaja,p.situacion,p.idFormularioAccion,p.idRegistroAccion,
							g.idInstanciaPlanEstudio FROM 4520_grupos AS g,4519_asignacionProfesorGrupo AS p WHERE g.idGrupos=p.idGrupo 
							AND  p.idUsuario='".$idUsuario."' and Plantel='".$Sede."' AND p.participacionPrincipal='1' AND p.situacion<>'4' 
							AND (('".$fechaIni."'>=p.fechaAsignacion AND '".$fechaIni."'<=p.fechaBaja) or ('".$fechaFin."'>=p.fechaAsignacion 
							AND '".$fechaFin."'<=p.fechaBaja) or ('".$fechaIni."'<=p.fechaAsignacion and '".$fechaFin."'>=p.fechaBaja)) 
							AND g.idGrupos NOT IN(".$listGrupos.")"; 
		$datos=$con->obtenerFilas($consulDatos);
		$arrGruposIgnorar=array();
		while ($row= mysql_fetch_row($datos))
		{
			if(isset($arrGruposIgnorar[$row[0]."_".$row[3]."_".$row[4]]))
				continue;
			else
				$arrGruposIgnorar[$row[0]."_".$row[3]."_".$row[4]]=1;
			$obtenerNivelplan="SELECT nivelPlanEstudio FROM 4513_vistaInstanciaPlanEstudio WHERE idInstanciaPlanEstudio='".$row[8]."'";
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

					if(!esDiaInhabilEscolar($Sede,$fechaActualNomina))
					{
						if(($fechaIniNomina>=$fInicioGrupo)&&($fechaIniNomina<=$fFinGrupo)) // && = y
						{
							  $diaSemana=date('w',$fechaIniNomina);
								
							  $obtenerHora="SELECT horaFin,horaInicio FROM 4522_horarioGrupo WHERE idGrupo='".$row[0]."' 
										  and dia=".$diaSemana." and '".$fechaActualNomina."'>=fechaInicio and '".$fechaActualNomina."'<=fechaFin 
										  and (fechaFin>fechaInicio) " ;
							  $horas=$con->obtenerFilas($obtenerHora);
							  if($con->filasAfectadas>0)
							  {
								  while ($fila= mysql_fetch_row($horas))
								  {
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
							if($fechaIniNomina>$fFinGrupo)
								break;
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
	if($tipo==1)
	{
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
							$ImporteSede=obtenerCostoProfesor($idUsuario,$plantel,$idInstancia);//$idUsuario,$codigoUnidad,$idNivel
							$costoClase=$ImporteSede*$valor;
							$Sueldo+=$costoClase;
						}
					}
				}
			}
		}
		return $Sueldo;
	}
	else
	{
		return $arCostosSede2;
	}
}

function ObtenerImporteFaltaQuincenaActualD($idUsuario,$idNomina,$Sede,$tipo) //CALCULO NOMINA UGM
{
	//Tipo=1=Importe,2=Arreglo
	global $con;
	$calculo="ObtenerImporteFalta";
	$Sueldo=0;
	$sumaCosto=0;
	$costoClase=0;
	
	$consultaNominaActual="SELECT fechaInicioIncidencias,fechaFinIncidencias,fechaCorteAsistencia,idPerfil,ciclo,fechaInicioFalta,quincenaAplicacion FROM 672_nominasEjecutadas 
							WHERE idNomina='".$idNomina."'";
	$periodoNomina=$con->obtenerPrimeraFila($consultaNominaActual);
	$fechaInicioNomina=$periodoNomina[0];
	$fechaFinNomina=$periodoNomina[2];
	
  	$fechaIni="NULL";
  	$fechaFin="NULL";
	$arrRecesos=obtenerArregloRecesos();
	//varDump($arrRecesos);
		
	$consultaNominaPagada="SELECT n.idGrupo FROM 675_nominaPagada n,4520_grupos g,672_nominasEjecutadas ne WHERE n.idGrupo=g.idGrupos 
							AND n.idNomina=ne.idNomina AND g.Plantel='".$Sede."' AND ne.etapa='1000' AND ne.ciclo='".$periodoNomina[4]."' 
							AND ne.quincenaAplicacion='".$periodoNomina[6]."' AND n.idUsuario='".$idUsuario."'";
	$listGrupos=$con->obtenerListaValores($consultaNominaPagada);
	if($listGrupos=="")
		$listGrupos=-1;
	
		$consultarFalta="SELECT distinct v.idGrupo,v.fechaFalta,v.horainicial,v.horaFinal,i.idInstanciaPlanEstudio,v.estadoFalta,v.idRegistroJustificacion,v.registroFalta 
							FROM 4559_vistaFalta v,4520_profesorGrupoMateriaSede AS i,4520_grupos g WHERE v.idUsuario='".$idUsuario."' AND i.idGrupos=v.idGrupo 
							AND v.fechaFalta>='".$fechaInicioNomina."' AND v.fechaFalta<='".$fechaFinNomina."' AND v.Plantel='".$Sede."' AND v.estadoFalta IN(0,1,3) 
							AND v.idGrupo NOT IN(".$listGrupos.") ORDER BY v.idGrupo";
	$faltas=$con->obtenerFilas($consultarFalta);
	$arrGruposIgnorar=array();	
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
			
			//varDump($arrGruposIgnorar);
		
		$diferencia=obtenerNumeroHorasBloque($row[0],$row[2],$row[3],$Sede,$arrRecesos,2);
		$duracionHora=obtenenerDuracionHoraGrupo($row[0]);
		$difHoras=$diferencia/$duracionHora;
		
		$ImporteSede=obtenerCostoProfesor($idUsuario,$Sede,$row[4]);//$idUsuario,$codigoUnidad,$idNivel

		$costoClase=$ImporteSede*$difHoras;
		$sumaCosto+=$costoClase;
	}
	if($tipo==1)
	{
		return $sumaCosto;
	}
	else
	{
		return $arrGruposIgnorar;
	}
}

function ObtenerImporteFaltaQuincenaAnteriorD($idUsuario,$idNomina,$Sede,$tipo) //CALCULO NOMINA UGM
{
	//Tipo=1=Importe,2=Arreglo
	global $con;
	$calculo="ObtenerImporteFalta";
	$Sueldo=0;
	$sumaCosto=0;
	$costoClase=0;
	
	$consultaNominaActual="SELECT fechaInicioIncidencias,fechaFinIncidencias,fechaCorteAsistencia,idPerfil,ciclo,fechaInicioFalta,quincenaAplicacion FROM 672_nominasEjecutadas 
							WHERE idNomina='".$idNomina."'";
	$periodoNomina=$con->obtenerPrimeraFila($consultaNominaActual);
	$fechaInicioNomina=$periodoNomina[5];
	$fechaFinNomina=$periodoNomina[0];
	
  	$fechaIni="NULL";
  	$fechaFin="NULL";
	$arrRecesos=obtenerArregloRecesos();
	//varDump($arrRecesos);
		
	$consultaNominaPagada="SELECT n.idGrupo FROM 675_nominaPagada n,4520_grupos g,672_nominasEjecutadas ne WHERE n.idGrupo=g.idGrupos 
							AND n.idNomina=ne.idNomina AND g.Plantel='".$Sede."' AND ne.etapa='1000' AND ne.ciclo='".$periodoNomina[4]."' 
							AND ne.quincenaAplicacion='".$periodoNomina[6]."' AND n.idUsuario='".$idUsuario."'";
	$listGrupos=$con->obtenerListaValores($consultaNominaPagada);
	if($listGrupos=="")
		$listGrupos=-1;
	
		$consultarFalta="SELECT distinct v.idGrupo,v.fechaFalta,v.horainicial,v.horaFinal,i.idInstanciaPlanEstudio,v.estadoFalta,v.idRegistroJustificacion,v.registroFalta 
							FROM 4559_vistaFalta v,4520_profesorGrupoMateriaSede AS i,4520_grupos g WHERE v.idUsuario='".$idUsuario."' AND i.idGrupos=v.idGrupo 
							AND v.fechaFalta>='".$fechaInicioNomina."' AND v.fechaFalta<'".$fechaFinNomina."' AND v.Plantel='".$Sede."' AND v.estadoFalta IN(0,1,3) 
							AND v.idGrupo NOT IN(".$listGrupos.") ORDER BY v.idGrupo";
	$faltas=$con->obtenerFilas($consultarFalta);
	$arrGruposIgnorar=array();	
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
			
			//varDump($arrGruposIgnorar);
		
		$diferencia=obtenerNumeroHorasBloque($row[0],$row[2],$row[3],$Sede,$arrRecesos,2);
		$duracionHora=obtenenerDuracionHoraGrupo($row[0]);
		$difHoras=$diferencia/$duracionHora;
		
		$ImporteSede=obtenerCostoProfesor($idUsuario,$Sede,$row[4]);//$idUsuario,$codigoUnidad,$idNivel

		$costoClase=$ImporteSede*$difHoras;
		$sumaCosto+=$costoClase;
	}
	if($tipo==1)
	{
		return $sumaCosto;
	}
	else
	{
		return $arrGruposIgnorar;
	}
}

function pagoPorSuplenciaD($idUsuario,$idNomina,$Sede,$tipo) //CALCULO NOMINA UGM
{
	global $con;
	$calculo="pagoPorSuplencia";
	$Sueldo=0;
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
	$arCostosSede2=array();

  	$consulCiclo="SELECT idCiclo FROM 4526_ciclosEscolares WHERE '".$fechaIni."' BETWEEN fechaInicio AND fechaTermino 
  				AND '".$fechaFin."' BETWEEN fechaInicio AND fechaTermino";
	$idCiclo=$con->obtenerValor($consulCiclo);
	
	$consultaNominaPagada="SELECT n.idGrupo FROM 675_nominaPagada n,4520_grupos g,672_nominasEjecutadas ne WHERE n.idGrupo=g.idGrupos 
							AND n.idNomina=ne.idNomina AND g.Plantel='".$Sede."' AND ne.etapa='1000' AND ne.ciclo='".$periodoNomina[2]."' 
							AND ne.quincenaAplicacion='".$periodoNomina[3]."' AND n.idUsuario='".$idUsuario."'";
	$listGrupos=$con->obtenerListaValores($consultaNominaPagada);
	if($listGrupos=="")
		$listGrupos=-1;

	$sumarHoras=0;
	$ImporteSede=0;
	$costoClase=0;
	$arrGruposIgnorar=array();
	$consulDatos="SELECT distinct g.idGrupos,g.Plantel,g.idMateria,p.fechaAsignacion,p.fechaBaja,p.situacion,p.idFormularioAccion,p.idRegistroAccion ,
					g.idInstanciaPlanEstudio,idAsignacionProfesorGrupo FROM 4520_grupos AS g,4519_asignacionProfesorGrupo AS p WHERE g.idGrupos=p.idGrupo 
					AND  p.idUsuario='".$idUsuario."' and g.Plantel='".$Sede."' and p.idParticipacion='45' AND p.situacion='1' AND (('".$fechaIni."'>=p.fechaAsignacion 
							and '".$fechaIni."'<=p.fechaBaja) or ('".$fechaFin."'>=p.fechaAsignacion and '".$fechaFin."'<=p.fechaBaja)
							or ('".$fechaIni."'<=p.fechaAsignacion and '".$fechaFin."'>=p.fechaBaja)) AND g.idGrupos NOT IN(".$listGrupos.")";
	$datos=$con->obtenerFilas($consulDatos);

	while ($row= mysql_fetch_row($datos))// 01-01-2011
	{
		if(isset($arrGruposIgnorar[$row[0]."_".$row[3]."_".$row[4]]))
			continue;
		else
			$arrGruposIgnorar[$row[0]."_".$row[3]."_".$row[4]]=1;
		$obtenerNivelplan="SELECT nivelPlanEstudio FROM 4513_vistaInstanciaPlanEstudio WHERE idInstanciaPlanEstudio='".$row[8]."'";
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
						$ImporteSede=obtenerCostoProfesor($idUsuario,$plantel,$idInstancia);//$idUsuario,$codigoUnidad,$idNivel
						$costoClase=$ImporteSede*$valor;
						$Sueldo+=$costoClase;
					}
				}
			}
		}
	}
	return $Sueldo;
}

function ImporteDiaFestivoD($idUsuario,$idNomina,$plantel,$tipo) //CALCULO NOMINA UGM
{
	//1=Importe, 2=Arreglo
	global $con;
	$calculo="Importe dia festivo";
	$Sueldo=0;
	$consulta="SELECT cmbSePaga FROM _503_tablaDinamica WHERE cmbPlanteles='".$plantel."'";
	$sePaga=$con->obtenerValor($consulta);
	if($sePaga=='1')
	{
		$consultaNomina="SELECT fechaInicioIncidencias,fechaFinIncidencias,ciclo,quincenaAplicacion FROM 672_nominasEjecutadas WHERE idNomina='".$idNomina."'";
		$periodoNomina=$con->obtenerPrimeraFila($consultaNomina);
		$fechaIni="NULL";
		$fechaFin="NULL";
	
		if($periodoNomina)
		{
			$fechaIni=$periodoNomina[0];
			$fechaFin=$periodoNomina[1];
			$difeDias=obtenerDiferenciaDias($fechaIni,$fechaFin);
		}
		
		$consultaNominaPagada="SELECT n.idGrupo FROM 675_nominaPagada n,4520_grupos g,672_nominasEjecutadas ne WHERE n.idGrupo=g.idGrupos 
								AND n.idNomina=ne.idNomina AND g.Plantel='".$plantel."' AND ne.etapa='1000' AND ne.ciclo='".$periodoNomina[2]."' 
								AND ne.quincenaAplicacion='".$periodoNomina[3]."' AND n.idUsuario='".$idUsuario."'";
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
			$consulDatos="SELECT distinct g.idGrupos,g.Plantel,g.idMateria,p.fechaAsignacion,p.fechaBaja,p.situacion,p.idFormularioAccion,p.idRegistroAccion,
							g.idInstanciaPlanEstudio FROM 4520_grupos AS g,4519_asignacionProfesorGrupo AS p WHERE g.idGrupos=p.idGrupo 
							AND  p.idUsuario='".$idUsuario."' and Plantel='".$plantel."' and p.participacionPrincipal='1' AND p.situacion <>'4' AND (('".$fechaIni."'>=p.fechaAsignacion 
						and '".$fechaIni."'<=p.fechaBaja) or ('".$fechaFin."'>=p.fechaAsignacion and '".$fechaFin."'<=p.fechaBaja)
						or ('".$fechaIni."'<=p.fechaAsignacion and '".$fechaFin."'>=p.fechaBaja)) AND g.idGrupos NOT IN(".$listGrupos.")"; 
			
			$datos=$con->obtenerFilas($consulDatos);
			$arrGruposIgnorar=array();
			while ($row= mysql_fetch_row($datos))
			{ 
				if(isset($arrGruposIgnorar[$row[0]."_".$row[3]."_".$row[4]]))
					continue;
				else
					$arrGruposIgnorar[$row[0]."_".$row[3]."_".$row[4]]=1;
				$obtenerNivelplan="SELECT nivelPlanEstudio FROM 4513_vistaInstanciaPlanEstudio WHERE idInstanciaPlanEstudio='".$row[8]."'";
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
						
							if(esDiaInhabilEscolar($plantel,$fechaActualNomina))
							{
								if(($fechaIniNomina>=$fInicioGrupo)&&($fechaIniNomina<=$fFinGrupo)) // && = y
								{
									  $diaSemana=date('w',$fechaIniNomina);
									  
										$obtenerDias="SELECT idOpcion FROM _503_radDiaSemana AS d,_503_tablaDinamica AS t WHERE d.idPadre=t.id__503_tablaDinamica 
														AND t.cmbPlanteles='".$plantel."' and d.idOpcion='".$diaSemana."' ORDER BY d.idOpcion";
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
											$obtenerHora="SELECT horaInicioBloque,horaFinBloque FROM 9105_controlAsistenciaDiaFestivo WHERE  idGrupo='".$row[0]."' 
															and fecha='".$fechaActualNomina."'  ";
															$horas=$con->obtenerFilas($obtenerHora);
											  if($con->filasAfectadas>0)
											  {
												  while ($fila= mysql_fetch_row($horas))
												  {
													  $diferencia=(strtotime($fila[1])-strtotime($fila[0]))/60;//strtotime("00:00:00")+strtotime($fila[0])-strtotime($fila[1]);
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
		
		if($tipo==1)
		{
	
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
								$ImporteSede=obtenerCostoProfesor($idUsuario,$plantel,$idInstancia);//$idUsuario,$codigoUnidad,$idNivel
								$costoClase=$ImporteSede*$valor;
								$Sueldo+=$costoClase;
							}
						}
					}
				}
			}
			return $Sueldo;
		}
		else
		{
			return $arCostosSede2;
		}
	}
	else
		return $Sueldo;
}

function calcularISR($idUsuario,$baseGravable,$idNomina)
{
	global $con;
	
	$consultaNomina="SELECT fechaInicioIncidencias,fechaFinIncidencias,ciclo,quincenaAplicacion,idPerfil FROM 672_nominasEjecutadas WHERE idNomina='".$idNomina."'";
	$periodoNomina=$con->obtenerPrimeraFila($consultaNomina);
  	$fechaIni="NULL";
  	$fechaFin="NULL";
	if($periodoNomina)
	{
		$ciclo=$periodoNomina[2];
	}

	$limiteInferior=impuestoQuincUGM($baseGravable,$ciclo,'limite');
	$porcentaje= impuestoQuincUGM($baseGravable,$ciclo,'porcentaje');
	$cuotaFija=impuestoQuincUGM($baseGravable,$ciclo,'cuota');
	$excedente=$baseGravable-$limiteInferior;
	$porcentajeExc=$porcentaje/100;
	$calculo1=$excedente*$porcentajeExc;
	$descuentoISR=$calculo1 + $cuotaFija;
	$diasTrabajados=diasNominasD($idNomina);
	
	if($diasTrabajados==15)
	{
		$valor=$descuentoISR;
	}
	else
	{
		$valor=($descuentoISR/15)*$diasTrabajados;
	}
	return $valor;
}

function calcularIMSS($idUsuario,$baseGravable,$idNomina,$plantel)
{
	global $con;
	
	$consulta="";
}

function descuentoPorSuplenciaD($idUsuario,$idNomina,$plantel,$tipo) //CALCULO NOMINA UGM
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
  	$consulCiclo="SELECT idCiclo FROM 4526_ciclosEscolares WHERE '".$fechaIni."' BETWEEN fechaInicio AND fechaTermino 
  				AND '".$fechaFin."' BETWEEN fechaInicio AND fechaTermino";
	$idCiclo=$con->obtenerValor($consulCiclo);
	
	$consultaNominaPagada="SELECT n.idGrupo FROM 675_nominaPagada n,4520_grupos g,672_nominasEjecutadas ne WHERE n.idGrupo=g.idGrupos 
							AND n.idNomina=ne.idNomina AND g.Plantel='".$plantel."' AND ne.etapa='1000' AND ne.ciclo='".$periodoNomina[2]."' 
							AND ne.quincenaAplicacion='".$periodoNomina[3]."' AND n.idUsuario='".$idUsuario."'";
	$listGrupos=$con->obtenerListaValores($consultaNominaPagada);
	if($listGrupos=="")
		$listGrupos=-1;
	
		$sumarHoras=0;
		$ImporteSede=0;
		$costoClase=0;
		$arrGruposIgnorar=array();
		$arCostosSede=array();
		
			$consulDatos="SELECT distinct g.idGrupos,g.Plantel,g.idMateria,p.fechaAsignacion,p.fechaBaja,p.situacion,p.idFormularioAccion,p.idRegistroAccion,
							g.idInstanciaPlanEstudio FROM 4520_grupos AS g,4519_asignacionProfesorGrupo AS p WHERE g.idGrupos=p.idGrupo 
							AND  p.idUsuario='".$idUsuario."' and Plantel='".$plantel."' AND p.participacionPrincipal='1' AND p.situacion NOT IN(0,4) 
							AND (('".$fechaIni."'>=p.fechaAsignacion AND '".$fechaIni."'<=p.fechaBaja) OR ('".$fechaFin."'>=p.fechaAsignacion 
							AND '".$fechaFin."'<=p.fechaBaja) OR ('".$fechaIni."'<=p.fechaAsignacion and '".$fechaFin."'>=p.fechaBaja)) 
							AND g.idGrupos NOT IN(".$listGrupos.")";
		$datos=$con->obtenerFilas($consulDatos);
		while ($row= mysql_fetch_row($datos))// 01-01-2011
		{
			if(isset($arrGruposIgnorar[$row[0]."_".$row[3]."_".$row[4]]))
				continue;
			else
				$arrGruposIgnorar[$row[0]."_".$row[3]."_".$row[4]]=1;
				
			$obtenerNivelplan="SELECT nivelPlanEstudio FROM 4513_vistaInstanciaPlanEstudio WHERE idInstanciaPlanEstudio='".$row[8]."'";
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
							if(!esDiaInhabilEscolar($plantel,$fechaActualNomina))
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
		if($tipo==1)
		{
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
								$ImporteSede=obtenerCostoProfesor($idUsuario,$plantel,$idInstancia);//$idUsuario,$codigoUnidad,$idNivel
								$costoClase=$ImporteSede*$valor;
								$Sueldo+=$costoClase;
							}
						}
					}
				}
			}
			return $Sueldo;
		}
}

?>