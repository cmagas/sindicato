<?php


function obtenerProfesoresInteresMateria($idGrupo)
{
	global $con;
	$consulta="SELECT idMateria,idCiclo,idPeriodo FROM 4520_grupos WHERE idGrupos=".$idGrupo;
	$fGrupo=$con->obtenerPrimeraFila($consulta);
	
	$consulta="SELECT t.id__1025_tablaDinamica FROM _1025_periodoEscolar p,_1025_tablaDinamica t WHERE p.idPadre=t.id__1025_tablaDinamica AND t.idEstado in (0,2) 
			and t.cicloEscolar=".$fGrupo[1]." AND p.idOpcion IN (".$fGrupo[2].")";
	$lConvocatorias=$con->obtenerListaValores($consulta);
	if($lConvocatorias=="")
		$lConvocatorias=-1;

	$consulta="SELECT idUsuario FROM _1026_materiasInteresProfesor WHERE idMateria=".$fGrupo[0]." AND idConvocatoria in (".$lConvocatorias.")";	

	$lUsuarios=$con->obtenerListaValores($consulta);
	
	if($lUsuarios=="")
		$lUsuarios=-1;
	
	return "'".$lUsuarios."'";
	
	
}

function obtenerProfesoresCompatiblesHorarioGrupoV3($idGrupo,$fechaAplicacion,$fechaTermino,$idSolicitud=-1)
{
	global $con;

	$listCandidatos="";
	$arrHorarioGpo=array();
	$arrHorarioGrupo=obtenerFechasHorarioGrupoV2($idGrupo,$idSolicitud,true,true,$fechaAplicacion,$fechaTermino);
	
	if(sizeof($arrHorarioGrupo)>0)
	{
		foreach($arrHorarioGrupo as $fila)
		{
			if(!isset($arrHorarioGpo[$fila[2]]))
				$arrHorarioGpo[$fila[2]]=array();
			$obj[0]=$fila[3];
			$obj[1]=$fila[4];
			$obj[2]=$fila[6];
			$obj[3]=$fila[7];
			array_push($arrHorarioGpo[$fila[2]],$obj);
		}
	}
	
	

	
	$consulta="select g.fechaInicio,g.fechaFin,g.idPeriodo,g.idCiclo,g.idInstanciaPlanEstudio,g.Plantel from 4520_grupos g where g.idGrupos=".$idGrupo;
	
	$filaFechas=$con->obtenerPrimeraFila($consulta);
	
	$idCiclo=$filaFechas[3];
	$idPeriodo=$filaFechas[2];
	$plantel=$filaFechas[5];
	
	$consulta="SELECT distinct idUsuario FROM 4065_disponibilidadHorario d	WHERE d.ciclo=".$idCiclo;

	$resFilas=$con->obtenerFilas($consulta);
	while($fProfesor=mysql_fetch_row($resFilas))
	{
		$idPeriodoRef=$idPeriodo;
		$idCicloRef=$idCiclo;
		$considerarProfesor=true;

		if(sizeof($arrHorarioGpo)>0)
		{
			
			$idUsuario=$fProfesor[0];
			
			
			$consulta="SELECT id__1025_tablaDinamica FROM _1025_tablaDinamica c,_1025_periodoEscolar pe  WHERE pe.idPadre=c.id__1025_tablaDinamica AND c.cicloEscolar=".$idCiclo." AND pe.idOpcion=".$idPeriodo;
			
			$listaConvocatorias=$con->obtenerListaValores($consulta);
			if($listaConvocatorias=="")
				$listaConvocatorias=-1;		
			
			
			$consulta="SELECT id__1026_tablaDinamica FROM _1026_tablaDinamica WHERE idUsuario=".$idUsuario." AND idConvocatoria IN (".$listaConvocatorias.")";
			$listaRegistroConvocatorias=$con->obtenerListaValores($consulta);
			
			if($listaRegistroConvocatorias=="")
				$listaRegistroConvocatorias=-1;
				
			$consulta="SELECT idDiaSemana,horaInicio,horaFin FROM 4065_disponibilidadHorario d WHERE d.ciclo=".$idCicloRef." and idPeriodo=0 and idUsuario=".$fProfesor[0].
					" and tipo=1 and idFormulario=1026 and idReferencia in (".$listaRegistroConvocatorias.")";
			$resH=$con->obtenerFilas($consulta);
			
			if($con->filasAfectadas>0)
			{
				$arrHorarioProf=array();
				while($fila=mysql_fetch_row($resH))
				{
					if(!isset($arrHorarioProf[$fila[0]]))
						$arrHorarioProf[$fila[0]]=array();
					$obj[0]=$fila[1];
					$obj[1]=$fila[2];
					array_push($arrHorarioProf[$fila[0]],$obj);
				}
				
				foreach($arrHorarioProf as $h=>$resto)
				{
					$arrHorarioProf[$h]=organizarBloquesHorario($resto);
				}
				
				foreach($arrHorarioGpo as $d=>$horario)
				{
					if($d!=10)
					{
						if(isset($arrHorarioProf[$d]))
						{
							
							foreach($horario as $h)
							{
								
								$encontrado=false;
								foreach($arrHorarioProf[$d] as $intervalo)
								{
									if(cabeEnIntervaloTiempo($h,$intervalo))
									{
										$encontrado=true;
										break;
									}
								}
								
								if($encontrado)
								{
									$fechaInicio=$h[2];
									$fechaFin=$h[3];
									$comp=generarConsultaIntervalos($fechaInicio,$fechaFin,"h.fechaInicio","h.fechaFin");
									$comp2=generarConsultaIntervalos($fechaInicio,$fechaFin,"a.fechaAsignacion","a.fechaBaja");
									$arrAsignaciones=obtenerAsignacionesProfesor($fProfesor[0],$idSolicitud,$fechaInicio,$fechaFin,$idGrupo);
									foreach($arrAsignaciones as $a)
									{
										$fInicioProf=$fechaInicio;
										$fFinProf=$fechaFin;
										
										if(strtotime($a[6])>strtotime($fInicioProf))
										{
											$fInicioProf=$a[6];
										}
										
										if(strtotime($a[7])<strtotime($fFinProf))
										{
											$fFinProf=$a[7];
										}
										
										$arrHorario=obtenerFechasHorarioGrupoV2($a[1],$idSolicitud,true,true,$fInicioProf,$fFinProf,-1);
										if(sizeof($arrHorario)>0)
										{
											
											foreach($arrHorario as $horario)
											{
												if($horario[2]==$d)
												{
													$fOcupa[1]=$horario[3];
													$fOcupa[2]=$horario[4];
													if(colisionaTiempo($h[0],$h[1],$fOcupa[1],$fOcupa[2]))
													{
														$considerarProfesor=false;
														break;
													}
												}
											}
										}
									}
									$encontrado=false;
								}
								else
									$considerarProfesor=false;
								if((!$considerarProfesor)||($encontrado))
									break;
							}
							if(!$considerarProfesor)
								break;
	
						}
						else
							$considerarProfesor=false;
						if(!$considerarProfesor)
						break;
					}
					
				}
			
			}
			else
				$considerarProfesor=false;
		}
		
		if($considerarProfesor)
		{
			if($listCandidatos=="")
				$listCandidatos=$fProfesor[0];
			else
				$listCandidatos.=",".$fProfesor[0];
			
		}
	}

	if($listCandidatos=="")
		$listCandidatos=-1;

	return "'".$listCandidatos."'";
}
?>