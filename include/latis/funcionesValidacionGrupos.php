<?php 	include_once("latis/diccionarioTerminos.php");
//Funciones validacion casos
function validarHorarioNulo($idGrupo,$idSolicitudAME)
{
	global $con;
	$noError=0;
	$complementario="";
	$leyenda="";
	$consulta="SELECT datosSolicitud,idSolicitudMovimiento FROM 4548_solicitudesMovimientoGrupo s,4549_cabeceraSolicitudAME c,4548_gruposSolicitudesMovimiento g 
				WHERE c.idSolicitudAME=".$idSolicitudAME."  and c.idSolicitudAME= s.idSolicitudAME and s.idSolicitudMovimiento=g.idSolicitud and   
				tipoSolicitud in(4,6) AND g.idGrupo=".$idGrupo." AND s.situacion in (1,2) ORDER BY idSolicitudMovimiento desc";	
			
	$fSolicitud=$con->obtenerPrimeraFila($consulta);				
	if($fSolicitud)
	{
		$objDatos=json_decode($fSolicitud[0]);
		if(sizeof($objDatos->horarioCambio)==0)
		{
			$noError=14;
			$leyenda="El grupo <b>".obtenerNombreGrupoMateria($idGrupo)."</b> no cuenta con un horario asignado";
			
		}
	}
	return '{"noError":"'.$noError.'","compl":"'.$complementario.'","leyenda":"'.$leyenda.'"}';
}

function validarHorarioIncompletoDuracionCurso($idGrupo,$idSolicitudAME)
{
	$noError=0;
	$complementario="";
	$leyenda="";
	$fVirtualGrupo=obtenerFechasDuracionGrupo($idGrupo,$idSolicitudAME);
	$arrHorarios=obtenerFechasHorarioGrupoV2($idGrupo,$idSolicitudAME,true,true);
	if(sizeof($arrHorarios)>0)
	{
		$encontrado=false;
		$maxFecha=$fVirtualGrupo[0];
		foreach($arrHorarios as $h)
		{
			if($h[2]!=10)
			{
				if($h[7]==$fVirtualGrupo[1])
				{
					$encontrado=true;
				}
				$maxFecha=$h[7];
			}	
		}
		
		if(!$encontrado)
		{
			$noError="15";	
			$complementario="";

			$leyenda="El grupo <b>".obtenerNombreGrupoMateria($idGrupo)."</b> no cuenta con un horario asignado a partir del d&iacute;a <b>".date("d/m/Y",strtotime("+1 days",strtotime($maxFecha)))."</b>";
		}
		
	}
	
	return '{"noError":"'.$noError.'","compl":"'.$complementario.'","leyenda":"'.$leyenda.'"}';
}

function validarHorarioAulaV2($idAula,$horaI,$horaF,$dia,$fechaInicio,$fechaFin,$idRegistroIgnorar=-1,$idGrupo=-1,$idSolicitudAME=-1)//Modificado
{
	global $con;
	$colision=false;
	$noError=0;
	$complementario="";
	$leyenda="";
	
	
	
	if($dia!=10)
	{
		$tablaColisiones="<table><tr><td width='300' align='center'><pan class='corpo8_bold'>Materia</td><td width='120' align='center'><pan class='corpo8_bold'>Grupo</span></td><td width='150' align='center'><pan class='corpo8_bold'>Horario problema</span></td></tr>";
		
		$arrHorarioAula=obtenerHorarioAula($idAula,$idSolicitudAME,$dia,$fechaInicio,$fechaFin,$idRegistroIgnorar,$idGrupo);
		
		if(sizeof($arrHorarioAula)>0)
		{
			foreach($arrHorarioAula as $filaMatP)
			{
				if($filaMatP[5]<>$idGrupo)
				{
					if(colisionaTiempo($filaMatP[1],$filaMatP[2],$horaI,$horaF))
					{
						$colision=true;
						$consulta="SELECT nombreGrupo,nombreMateria,o.unidad,a.cveMateria,g.idInstanciaPlanEstudio FROM 4520_grupos g,4502_Materias m,817_organigrama o,4512_aliasClavesMateria a  
								  WHERE m.idMateria=g.idMateria AND idGrupos=".$filaMatP[5]." AND o.codigoUnidad=g.plantel AND a.idMateria=m.idMateria 
								  AND a.sede=g.plantel";

						$filaMat=$con->obtenerPrimeraFila($consulta);
						$nombre=$filaMat[1];
						$diaProblema=obtenerNombreDiaExtendido($filaMatP[0]);
						$grupo=$filaMat[0];
						$tablaColisiones.="<tr height='21'><td align='left'><span class='letraExt'>[".$filaMat[3]."] ".$nombre."<br><b>Plantel</b>: ".$filaMat[2]."<br><b>Plan de estudios:</b> (".$filaMat[4].") ".obtenerNombreInstanciaPlan($filaMat[4])."</span></td><td align='left'><span class='letraExt'>".$grupo."</span></td><td align='left'><span class='letraExt'>".$diaProblema." ".
										  date("H:i",strtotime($filaMatP[1]))." - ".date("H:i",strtotime($filaMatP[2]))."<br>Del (".date("d/m/Y",strtotime($filaMatP[3])).") al (".date("d/m/Y",strtotime($filaMatP[4])).")</span></td></tr>" ;
					}
				}
			}
		}
		$tablaColisiones.="</table>";
	
		if($colision)
		{
			$noError=12;
			$complementario=$tablaColisiones;
			$consulta="SELECT nombreArea FROM 9309_ubicacionesFisicas WHERE idAreaFisica=".$idAula;
			$nombreAula=$con->obtenerValor($consulta);
			$leyenda="El aula <b>".$nombreAula."</b> asignada ya se encuentra ocupada por otras materias en el horario indicado";
		}
	}
	return '{"noError":"'.$noError.'","compl":"'.$complementario.'","leyenda":"'.$leyenda.'"}';
}

function validarDisponibilidadHorarioAlumnoV2($idGrupo,$dia,$horaI,$horaF,$fechaInicio,$fechaFin,$idSolicitudAME=-1,$idAlumno=-1,$idGrupoIgnorar=-1)//Modificado
{
	global $con;
	$colision=false;
	$noError=0;
	$complementario="";
	$leyenda="";
	if($dia!=10)
	{
		$tablaColisiones="<table><tr><td width='200' align='center'><pan class='corpo8_bold'>Alumno</td><td width='200' align='center'><pan class='corpo8_bold'>Materia/Grupo problema</span></td><td width='150' align='center'><pan class='corpo8_bold'>Horario problema</span></td></tr>";		  
		$consulta="SELECT idUsuario FROM 4517_alumnosVsMateriaGrupo WHERE  idGrupo=".$idGrupo." AND situacion=1";
		if($idAlumno!=-1)
			$consulta="SELECT ".$idAlumno;
		
		$res=$con->obtenerFilas($consulta);
		$noFilas=$con->filasAfectadas;
		
		$mensajeColision="";
		while($fila=mysql_fetch_row($res))
		{
			$conMatAlum="SELECT idGrupo FROM 4517_alumnosVsMateriaGrupo WHERE idGrupo not in (".$idGrupo.",".$idGrupoIgnorar.") AND situacion=1 and idUsuario=".$fila[0];
		   
			$resAlum=$con->obtenerFilas($conMatAlum);
			while($filaMat=mysql_fetch_row($resAlum))
			{
				$arrHorario=obtenerFechasHorarioGrupoV2($filaMat[0],$idSolicitudAME,true,true,$fechaInicio,$fechaFin);
				if(sizeof($arrHorario)>0)
				{
					foreach($arrHorario as $h)
					{
						$filaAlum[0]=$h[2];
						$filaAlum[1]=$h[3];
						$filaAlum[2]=$h[4];
						if($filaAlum[0]==$dia)
						{
							if(colisionaTiempo($filaAlum[1],$filaAlum[2],$horaI,$horaF))
							{
								
								$nombreAlumno=obtenerNombreUsuario($fila[0]);
								$colision=true;
								$consulta="SELECT nombreGrupo,nombreMateria,o.unidad,a.cveMateria  FROM 4520_grupos g,4502_Materias m,817_organigrama o,4512_aliasClavesMateria a  
											WHERE m.idMateria=g.idMateria AND idGrupos=".$filaMat[0]." AND o.codigoUnidad=g.plantel AND a.idMateria=m.idMateria AND a.sede=g.plantel";
								
								$filaM=$con->obtenerPrimeraFila($consulta);
								$nombre=$filaM[1];
								$diaProblema=obtenerNombreDiaExtendido($filaAlum[0]);
								$grupo=$filaM[0];
								
								$tablaColisiones.="<tr height='21'><td align='left'><span class='letraExt'>".$nombreAlumno."</span></td><td align='left'><span class='letraExt'>[".$filaM[3]."] ".cv($nombre)." (Grupo: ".$grupo.")<br><b>Plantel:</b> ".$filaM[2]."</span></td><td align='left'><span class='letraExt'>".$diaProblema." ".
													date("H:i",strtotime($filaAlum[1]))." - ".date("H:i",strtotime($filaAlum[2]))."</span></td></tr>" ;
								
							}
						}
					}
				}
			}
		}
		$tablaColisiones.="</table>";
		if($colision)
		{
			$noError=11;
			$complementario=$tablaColisiones;
			$leyenda="El horario ocasiona que algunos alumnos inscritos en la materia <b>".cv(obtenerNombreGrupoCompleto($idGrupo))."</b> presenten problemas de horario";
		}
	}
	return '{"noError":"'.$noError.'","compl":"'.$complementario.'","leyenda":"'.$leyenda.'"}';
}

function validarTotalHorasAsignadasGrupoV2($idGrupo,$dia,$horaI,$horaF,$fechaInicio,$fechaFin,$idRegistroIgnorar=-1,$idSolicitudAME=-1)//Modificado--
{
	global $con;
	$noError=0;
	$complementario="";
	if($dia!=10)
	{
		$minutosReales=0;
		$sumatoriaMinutos=0;
		$consulta="SELECT idInstanciaPlanEstudio,idMateria,Plantel,idPeriodo FROM 4520_grupos WHERE idGrupos=".$idGrupo;
		$fGrupo=$con->obtenerPrimeraFila($consulta);
		$idInstancia=$fGrupo[0];
		$idMateria=$fGrupo[1];
		$idPeriodo=$fGrupo[3];
		$sedeG=$fGrupo[2];
		if($sedeG=="")
			$sedeG="-1";
		
		$arrDatosMateriaHoras=obtenerDatosMateriaHorasGrupo($idGrupo);
		$noHoras=$arrDatosMateriaHoras["horasSemana"];

		
		$unidadM=obtenenerDuracionHoraGrupo($idGrupo);
		/*$tiempoMat=((strtotime('0:00:00'))+(strtotime($horaF)))-(strtotime($horaI));
		$nuevosMin=(date('H',$tiempoMat)*60)+(date('i',$tiempoMat));	*/
		
		$nuevosMin=obtenerDiferenciaHoraMinutos($horaI,$horaF);
		
		$arrHorario=obtenerFechasHorarioGrupoV2($idGrupo,$idSolicitudAME,true,true,$fechaInicio,$fechaFin,$idRegistroIgnorar);

		if(sizeof($arrHorario)>0)
		{
			foreach($arrHorario as $h)
			{
				if($h[0]!=0)
				{
					$queryAux="SELECT horarioCompleto FROM 4522_horarioGrupo WHERE idHorarioGrupo=".$h[0];
					$horarioCompleto=$con->obtenerValor($queryAux);
					if($horarioCompleto==0)
						continue;
				}
				
				$hMat[0]=$h[2];
				$hMat[1]=$h[3];
				$hMat[2]=$h[4];
				$tiempoMat=obtenerDiferenciaHoraMinutos($hMat[1],$hMat[2]);
				$sumatoriaMinutos+=$tiempoMat;
			}
		}
		
		
		$minutosReales=$sumatoriaMinutos+$nuevosMin;
		$validar=$minutosReales/$unidadM;
		if(($idMateria>0)&&($validar>$noHoras))
		{
			$noError=4;
			$complementario="Número de horas permitidas por semana: <b>".$noHoras."</b>, Número de horas asignadas: <b>".$validar."</b>";
		}
	}
	return '{"noError":"'.$noError.'","compl":"'.$complementario.'"}';
}

function validarChoqueGruposHermanosV2($idGrupo,$dia,$horaI,$horaF,$fechaInicio,$fechaFin,$idGrupoIgnora=-1,$idSolicitudAME=-1)//Modificado
{
	global $con;
	global $arrDiasSemana;
	$noError=0;
	$complementario="<table>";
	$leyenda="";
	if($dia!=10)
	{
		$colisiona=false;
		$consulta="(SELECT idGrupoPadre FROM 4520_grupos WHERE idGrupos=".$idGrupo.") union 
				(SELECT distinct g.idGrupoPadre FROM 4539_gruposCompartidos gc,4520_grupos g WHERE gc.idGrupo=".$idGrupo." AND g.idGrupos=gc.idGrupoReemplaza)";

		$idGrupoPadre=$con->obtenerListaValores($consulta);
		if($idGrupoPadre!="")
		{
			$arrCasos="";
			$compFechas=generarConsultaIntervalos($fechaInicio,$fechaFin,"fechaInicio","fechaFin");
			$consulta="SELECT distinct idGrupos,situacion FROM 4520_grupos WHERE idGrupoPadre in (".$idGrupoPadre.") AND idGrupos not in (".$idGrupo.",".$idGrupoIgnora.") 
						and ".$compFechas;
			
			$resGrupos=$con->obtenerFilas($consulta);
	
			while($fGrupoAux=mysql_fetch_row($resGrupos))
			{
				$comp="";
				$idGrupoComp=$fGrupoAux[0];
				if($fGrupoAux[1]==2)
				{
					$consulta="SELECT idGrupo FROM 4539_gruposCompartidos WHERE idGrupoReemplaza=".$idGrupoComp." and idGrupo not in (".$idGrupo.",".$idGrupoIgnora.")"; 
	
					$idGrupoComp=$con->obtenerValor($consulta);
					if($idGrupoComp=="")
						$idGrupoComp=-1;
					$comp="compartida ";
				}
				$arrHorario=obtenerFechasHorarioGrupoV2($idGrupoComp,$idSolicitudAME,true,true,$fechaInicio,$fechaFin);
				
				if(sizeof($arrHorario)>0)
				{
					foreach($arrHorario as $h)
					{
						$fHorario[0]=$h[1];
						$fHorario[1]=$h[3];
						$fHorario[2]=$h[4];
						$fHorario[3]=$h[6];
						$fHorario[4]=$h[7];
						$fHorario[5]=$h[2];
						if($fHorario[5]==$dia)
						{
							if(colisionaTiempo($horaI,$horaF,$fHorario[1],$fHorario[2]))
							{
								$colisiona=true;
								$nMateria=obtenerNombreCurso($fHorario[0]);	
								$consulta="SELECT idInstanciaPlanEstudio FROM 4520_grupos WHERE idGrupos=".$fHorario[0];
								$idInstancia=$con->obtenerValor($consulta);
								$complementario.="<tr><td><img src='../images/bullet_green.png'> </td><td><b>Plan de Estudios:</b> ".obtenerNombreInstanciaPlan($idInstancia)."<br><b>Materia:</b> ".$comp." ".$nMateria." el día ".utf8_encode($arrDiasSemana[$dia])." de ".date("H:i",strtotime($fHorario[1]))." a ".date("H:i",strtotime($fHorario[2]))."</td></tr><tr height='10'><td></td></tr>";
							}
						}
					}
				}
				
			}
			if($colisiona)
			{
				$noError=13;
				$leyenda="El grupo <b>".obtenerNombreGrupoCompleto($idGrupo)."</b> presenta problemas de horario con al menos otro grupo";
			}
			
		}
		$complementario.="</table>";
	}
	return '{"noError":"'.$noError.'","compl":"'.$complementario.'","leyenda":"'.$leyenda.'"}';
}

function validarDisponibilidadHorarioProfesorV2($idUsuario,$horaI,$horaF,$dia,$fechaInicio,$fechaFin,$idRegistroIgnorar=-1,$idGrupo=-1,$idSolicitudAME=-1,$idGruposIgnorar=-1)//Modificado
{
	global $con;
	$colision=false;
	$tablaColisiones=0;
	$noError=0;
	$complementario="";
	$leyenda="";
	$arrGruposIgnorar=array();
	if($idGruposIgnorar!=-1)
	{
		$arrGruposIgnorar=explode(",",$idGruposIgnorar);	
	}
	if(($dia!=10)&&($idUsuario!=2))
	{
		$tablaColisiones="<br><b>Grupos/Materias con los cuales el profesor tiene conflicto:</b><br><br><table><tr><td width='300' align='center'><pan class='corpo8_bold'>Materia</td><td width='190' align='center'>".
						"<pan class='corpo8_bold'>Grupo</span></td><td width='170' align='center'><pan class='corpo8_bold'>Horario problema</span></td></tr><tr height='1'><td colspan='3' style='background-color:#253778'></td></tr>";		
		$arrAsignaciones=obtenerAsignacionesProfesor($idUsuario,$idSolicitudAME,$fechaInicio,$fechaFin,$idGrupo);
		/*if($dia==2)
		{
			echo $horaI."-".$horaF."<br>";
			echo $fechaInicio."-".$fechaFin."<br>";
			varDump($arrAsignaciones);
		}*/
		if(sizeof($arrAsignaciones)>0)
		{
			$mensajeDeColision="";
			foreach($arrAsignaciones as $f)
			{
				if(existeValor($arrGruposIgnorar,$f[1]))
				{
					continue;	
				}
				$fila[0]=$f[5];
				$fila[1]=$f[1];
				$fInicioProf=$fechaInicio;
				$fFinProf=$fechaFin;

				if(strtotime($f[6])>strtotime($fInicioProf))
				{
					
					$fInicioProf=$f[6];
				}
				
				if(strtotime($f[7])<strtotime($fFinProf))
				{
					$fFinProf=$f[7];
				}
				
				$arrHorario=obtenerFechasHorarioGrupoV2($fila[1],$idSolicitudAME,true,true,$fInicioProf,$fFinProf,$idRegistroIgnorar);
				if(sizeof($arrHorario)>0)
				{
					foreach($arrHorario as $h)
					{
						$filaMatP[0]=$h[2];
						$filaMatP[1]=$h[3];
						$filaMatP[2]=$h[4];
						$filaMatP[3]=$h[6];
						$filaMatP[4]=$h[7];
						if($dia==$filaMatP[0])
						{
							if(colisionaTiempo($filaMatP[1],$filaMatP[2],$horaI,$horaF))
							{
								$colision=true;
								$consulta="SELECT nombreGrupo,nombreMateria,o.unidad,a.cveMateria,g.idInstanciaPlanEstudio FROM 4520_grupos g,4502_Materias m,817_organigrama o,4512_aliasClavesMateria a   
										  WHERE m.idMateria=g.idMateria AND idGrupos=".$fila[1]." AND o.codigoUnidad=g.plantel AND a.idMateria=m.idMateria AND a.sede=g.plantel";
								
								$filaMat=$con->obtenerPrimeraFila($consulta);
								$nombre=$filaMat[1];
								$diaProblema=obtenerNombreDiaExtendido($filaMatP[0]);
								$grupo=$filaMat[0];
								
								$pInicioChoque=strtotime($f[6]);
								if($pInicioChoque<strtotime($h[6]))
									$pInicioChoque=strtotime($h[6]);
								$pFinChoque=strtotime($f[7]);
								if($pFinChoque>strtotime($h[7]))
									$pFinChoque=strtotime($h[7]);
								
								$tablaColisiones.="<tr height='21'><td align='left'><span class='letraExt'>[(".$fila[1].") ".$filaMat[3]."] ".$nombre."<br><b>Plantel:</b> ".$filaMat[2]."<br><b>Plan de estudio:</b> ".obtenerNombreInstanciaPlan($filaMat[4])."</span></td><td align='left'><span class='letraExt'>".$grupo."</span></td><td align='left'><span class='letraExt'>".$diaProblema." ".
												  date("H:i",strtotime($filaMatP[1]))." - ".date("H:i",strtotime($filaMatP[2]))."<br>(Del ".date("d/m/Y",$pInicioChoque)." al ".date("d/m/Y",$pFinChoque).")</span></td></tr>" ;
								
							}
						}
					}
				}
			}
			$tablaColisiones.="</table><br><br>";
			$leyenda="";
			if($colision)
			{
				$noError=10;
				$complementario=$tablaColisiones;
				$nombreGrupoBase="";
				if($idGrupo<>-1)
				{
					$consulta="SELECT nombreGrupo,nombreMateria,a.cveMateria FROM 4520_grupos g,4502_Materias m,4512_aliasClavesMateria a   
								  WHERE m.idMateria=g.idMateria AND idGrupos=".$idGrupo." AND  a.idMateria=m.idMateria AND a.sede=g.plantel";
					$fLeyendaGpo=$con->obtenerPrimeraFila($consulta);
					$nombreGrupoBase="(".$fLeyendaGpo[0].") [".$fLeyendaGpo[2]."] ".$fLeyendaGpo[1];
				}
				$leyenda="El horario del grupo <b>".$nombreGrupoBase."</b> presenta problemas con otras materias en las cuales el profesor <b>".obtenerNombreUsuario($idUsuario)."</b> es titular";
			}
		}
	}
	return '{"noError":"'.$noError.'","compl":"'.$complementario.'","leyenda":"'.$leyenda.'"}';
}

function validarRegistroDisponibilidadHorarioDocenteV2($dia,$horaI,$horaF,$idCiclo,$idUsuario,$idPeriodo,$idInstancia,$idGrupo=-1)
{
	global $con; 
	global $urlSitio;
	$noError=0;
	$complementario="";
	$leyenda="";
	if(($dia!=10)&&($idUsuario!=2))
	{
		$consulta="SELECT count(*) FROM 4065_disponibilidadHorario d,_1026_tablaDinamica r WHERE d.idUsuario=".$idUsuario." AND d.ciclo=".$idCiclo." and d.idPeriodo=".$idPeriodo." and d.idFormulario=1026 
				and r.id__1026_tablaDinamica=d.idReferencia and r.idEstado=2";
		$nReg=$con->obtenerValor($consulta);
		if(($nReg==0)&&($idCiclo<=10))
		{
			if(!esPeriodoBase($idPeriodo))
			{
				$consulta="select fechaInicial,fechaFinal from 4544_fechasPeriodo where idPeriodo=".$idPeriodo." and idCiclo=".$idCiclo." and idInstanciaPlanEstudio=".$idInstancia;
				$fechasPeriodo=$con->obtenerPrimeraFila($consulta);
				$consulta="SELECT idPeriodo,idCiclo FROM 4544_fechasPeriodo WHERE '".$fechasPeriodo[0]."'>=fechaInicial AND '".$fechasPeriodo[0]."'<=fechaFinal AND idPeriodo IN (".obtenerPeriodoBase().")  and idInstanciaPlanEstudio=".$idInstancia;
				$fPeriodo=$con->obtenerPrimeraFila($consulta);
				if($fPeriodo)
				{
					$idCiclo=$fPeriodo[1];
					$idPeriodo=$fPeriodo[0];
				}
			}
		}
		
		$consulta="SELECT idDiaSemana,horaInicio,horaFin FROM 4065_disponibilidadHorario WHERE idUsuario=".$idUsuario." AND ciclo=".$idCiclo." and idPeriodo=".$idPeriodo." order by idDiasemana,horaInicio";
		
		$resHorarios=$con->obtenerFilas($consulta);
		$noHorarios=$con->filasAfectadas;
		
		//AjusteHorario
		if((strpos($urlSitio,"ugmex")!==false))
		{
			if($con->filasAfectadas==0)
			{
				switch($idPeriodo)
				{
					case 11:
						$consulta="SELECT id__464_gridPeriodos FROM _464_gridPeriodos WHERE idReferencia=1 AND periodoDefaultActivo=1";
	
						$idPeriodo=$con->obtenerValor($consulta);
						
					break;
				}
				$consulta="SELECT idDiaSemana,horaInicio,horaFin FROM 4065_disponibilidadHorario d
						WHERE d.ciclo=".$idCiclo." AND d.idPeriodo=".$idPeriodo.
						" AND  tipo=1 AND idUsuario=".$idUsuario."  ORDER BY idDiaSemana,horaInicio";
				$resHorarios=$con->obtenerFilas($consulta);
				$noHorarios=$con->filasAfectadas;
			}
		}
		//--
	
		if($noHorarios==0)
		{
			$noError=6;
			$leyenda="El profesor ".obtenerNombreUsuarioPaterno($idUsuario)." no cuenta con un registro de disponibilidad de horario para este ciclo";
		}
		else
		{
			$cadenaColision="";
			$encontrado=0;
			$arrHorarioProf=array();
			while($fila=mysql_fetch_row($resHorarios))
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
	
			$encontrado=false;
			$h=array();
			$h[0]=$horaI;
			$h[1]=$horaF;
			if(isset($arrHorarioProf[$dia]))
			{
				foreach($arrHorarioProf[$dia] as $intervalo)
				{
					if(cabeEnIntervaloTiempo($h,$intervalo))
					{
						$encontrado=true;
						break;
					}
					
				}
				if(!$encontrado)
				{
					$obj=obtenerNombreDiaExtendido($dia)."&nbsp;De&nbsp;".date('H:i',strtotime($horaI))."&nbsp;A&nbsp;".date('H:i',strtotime($horaF));
					if($cadenaColision=="")	  
						$cadenaColision=$obj;
					else
						$cadenaColision.=$obj;
				}
			}
			
				
			
			if(!$encontrado)
			{
				$noError=7;
				$complementario=$cadenaColision;
				if($idGrupo!=-1)
					$leyenda="El horario que desea asignar al grupo <b>".obtenerNombreGrupoCompleto($idGrupo)."</b> no concuerda con la disponibilidad de horario que el profesor (".obtenerNombreUsuarioPaterno($idUsuario).") estableci&oacute; para este ciclo";
				else
					$leyenda="El horario que desea asignar a la materia no concuerda con la disponibilidad de horario que el profesor (".obtenerNombreUsuarioPaterno($idUsuario).") estableci&oacute; para este ciclo";
			}
		}
	}
	return '{"noError":"'.$noError.'","compl":"'.$complementario.'","leyenda":"'.$leyenda.'"}';
}

function validarPerfilProfesorGrupoV2($idUsuario,$idGrupo)
{
	global $con;
	
	$noCumplePerfil=true;
	$noError=0;
	$complementario="";
	if($idUsuario!=2)
	{
		$query="select idMateria,idCiclo,Plantel,idPeriodo,idInstanciaPlanEstudio,fechaInicio,fechaFin from 4520_grupos where idGrupos=".$idGrupo;
		$fGrupo=$con->obtenerPrimeraFila($query);
		$query="SELECT idEspecialidad FROM 4502_perfilMateria WHERE idMateria=".$fGrupo[0];
		$listEspecialidades=$con->obtenerListaValores($query);
		if($listEspecialidades=="")
			$noCumplePerfil=false;
		else
		{
			$query="SELECT cmbEspecialidad FROM _262_tablaDinamica WHERE responsable=".$idUsuario." AND cmbEspecialidad IN (".$listEspecialidades.")";
			
			$res=$con->obtenerFilas($query);
			if($con->filasAfectadas>0)
				$noCumplePerfil=false;
		}
		if($noCumplePerfil)
			$noError=1;
	}
	return '{"noError":"'.$noError.'","compl":"'.$complementario.'"}';
}

function validarColisionRecesoV2($idGrupo,$dia,$horaI,$horaF)
{
	global $con;
	global $arrDiasSemana;
	$noError=0;
	$complementario="";
	if($dia!=10)
	{
		$consulta="SELECT idInstanciaPlanEstudio FROM 4520_grupos WHERE idGrupos=".$idGrupo;
		$idInstancia=$con->obtenerValor($consulta);
		$consulta="SELECT * FROM _476_gridRecesos WHERE idReferencia IN (
					SELECT idReferencia FROM _476_gridPlanesEstudio WHERE idInstanciaPlanEstudio=".$idInstancia.")";
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			if(colisionaTiempo($horaI,$horaF,$fila[3],$fila[4]))
			{
				$noError=5;
				$complementario="Horario de receso con el cual presenta problemas: D&iacute;a ".utf8_encode($arrDiasSemana[$dia])." de ".date("H:i",strtotime($fila[3]))." a ".date("H:i",strtotime($fila[4]));
			}
		}
	}
	return '{"noError":"'.$noError.'","compl":"'.$complementario.'"}';
}

function validarColisionHoraMismoGrupoV2($idGrupo,$dia,$horaI,$horaF,$fechaInicio,$fechaFin,$idRegistroIgnorar=-1)
{
	global $con;
	$noError=0;
	$complementario="";
	if($dia!=10)
	{
		$comp=generarConsultaIntervalos($fechaInicio,$fechaFin,"fechaInicio","fechaFin");
		$conHorMat="SELECT dia,horaInicio,horaFin FROM 4522_horarioGrupo WHERE  idGrupo=".$idGrupo." and  ".$comp." and dia=".$dia." and idHorarioGrupo not in(".$idRegistroIgnorar.") and fechaInicio<=fechaFin and horarioCompleto=1 for update";
		$resHorMat=$con->obtenerFilas($conHorMat);
		while($hMat=mysql_fetch_row($resHorMat))			
		{
			if(colisionaTiempo($horaI,$horaF,$hMat[1],$hMat[2]))
			{
				$noError=2;
				break;
			}
		}
	}
	return '{"noError":"'.$noError.'","compl":"'.$complementario.'"}';
}

function validarDisponibilidadHorarioRecesoAlumnoV2($idGrupo,$dia,$horaI,$horaF)
{
	global $con;
	global $arrDiasSemana;
	$colision=false;
	$noError=0;
	
	$complementario="";
	if($dia!=10)
	{
		$tablaColisiones="<table><tr><td width='200' align='center'><pan class='corpo8_bold'>Alumno</td><td width='200' align='center'><pan class='corpo8_bold'>Plan de estudios/Plantel</span></td><td width='150' align='center'><pan class='corpo8_bold'>Receso problema</span></td></tr>";		  
		$consulta="SELECT idUsuario,idGrupoOrigen FROM 4517_alumnosVsMateriaGrupo WHERE  idGrupo=".$idGrupo." AND situacion=1";
		
		$res=$con->obtenerFilas($consulta);
		$noFilas=$con->filasAfectadas;
		
		$mensajeColision="";
		while($fila=mysql_fetch_row($res))
		{
			if($fila[1]!="")
			{
				$consulta="SELECT idInstanciaPlanEstudio FROM 4520_grupos WHERE idGrupos=".$fila[1];
				$idInstancia=$con->obtenerValor($consulta);
				$consulta="SELECT * FROM _476_gridRecesos WHERE idReferencia IN (
							SELECT idReferencia FROM _476_gridPlanesEstudio WHERE idInstanciaPlanEstudio=".$idInstancia.")";
							
				$resReceso=$con->obtenerFilas($consulta);
				while($filaReceso=mysql_fetch_row($resReceso))
				{
					if(colisionaTiempo($horaI,$horaF,$filaReceso[3],$filaReceso[4]))
					{
						$colision=true;
						$nombreAlumno=obtenerNombreUsuario($fila[0]);
						$consulta="SELECT nombreGrupo,nombreMateria,o.unidad,a.cveMateria  FROM 4520_grupos g,4502_Materias m,817_organigrama o,4512_aliasClavesMateria a  
									WHERE m.idMateria=g.idMateria AND idGrupos=".$fila[1]." AND o.codigoUnidad=g.plantel AND a.idMateria=m.idMateria AND a.sede=g.plantel";
						
						$filaM=$con->obtenerPrimeraFila($consulta);
						$diaProblema=$arrDiasSemana[$dia];
						$tablaColisiones.="<tr height='21'><td align='left'><span class='letraExt'>".$nombreAlumno."</span></td><td align='left'><span class='letraExt'><b>Plan de Estudios:</b> ".obtenerNombreInstanciaPlan($idInstancia)."<br><b>Plantel:</b> ".$filaM[2]."</span></td><td align='left'>".
										"<span class='letraExt'>".utf8_encode($diaProblema)." ".date("H:i",strtotime($filaReceso[3]))." - ".date("H:i",strtotime($filaReceso[4]))."</span></td></tr><tr height='10'><td colspan='3'></td></tr>" ;
						
					}
				}			
							
			}
		}
		
		$tablaColisiones.="</table><br>";
		if($colision)
		{
			$noError=3;
			$complementario=$tablaColisiones;
		}
	}
	return '{"noError":"'.$noError.'","compl":"'.$complementario.'"}';
}


//Funciones auxiliares

function normalizarFechasBloque($arrFinalHorario,$validarMismoTipo=false)
{
	$fechaInicio="";
	$reprocesar=true;
	while($reprocesar)
	{
		$fechaInicio="";
		$reprocesar=false;
		if(!$validarMismoTipo)
		{
			foreach($arrFinalHorario as $fHorario)
			{
				if($fechaInicio=="")
					$fechaInicio=$fHorario[6];
				if($fechaInicio!=$fHorario[6])
				{
					for($x=0;$x<sizeof($arrFinalHorario);$x++)
					{
						if(($arrFinalHorario[$x][6]==$fechaInicio)&&(strtotime($arrFinalHorario[$x][7])>=strtotime($fHorario[6])))
						{
							$arrFinalHorario[$x][7]=date("Y-m-d",strtotime("-1 days",strtotime($fHorario[6])));
							$reprocesar=true;
						}
					}
					$fechaInicio=$fHorario[6];
				}
			}
		}
		else
		{
			$arrTipos=array();
			foreach($arrFinalHorario as $fHorario)
			{
				$arrTipos[$fHorario[8]]=1;
				
			}
			if(sizeof($arrTipos)>0)
			{
				foreach($arrTipos as $t=>$resto)	
				{
					$fechaInicio="";
					foreach($arrFinalHorario as $fHorario)
					{
						if($fHorario[8]==$t)
						{
							if($fechaInicio=="")
								$fechaInicio=$fHorario[6];
								
							if($fechaInicio!=$fHorario[6])
							{
								for($x=0;$x<sizeof($arrFinalHorario);$x++)
								{
									if(($arrFinalHorario[$x][6]==$fechaInicio)&&(strtotime($arrFinalHorario[$x][7])>=strtotime($fHorario[6])))
									{
										$arrFinalHorario[$x][7]=date("Y-m-d",strtotime("-1 days",strtotime($fHorario[6])));
										$reprocesar=true;
									}
								}
								$fechaInicio=$fHorario[6];
							}
						}
						
						
					}
				}
			}
		}
		
		$arrAux=array();
		foreach($arrFinalHorario as $h)
		{
			
			if(strtotime($h[6])<=strtotime($h[7]))
			{
				array_push($arrAux,$h);
			}
		}
		$arrFinalHorario=$arrAux;
		
		
	}

	return $arrFinalHorario;
}

function ordenarFechasArreglo($arrHorario)
{
	$arrFinalHorario=array();
	$arrFechas=array();
	foreach($arrHorario as $fila)
	{
		$fI=strtotime($fila[6]);
		$fF=strtotime($fila[7]);
		$dia=$fila[2];
		$hI=strtotime($fila[3]);

		if(!isset($arrFechas[$fI]))
		{
			$arrFechas[$fI]=array();
		}
		if(!isset($arrFechas[$fI][$fF]))
		{
			$arrFechas[$fI][$fF]=array();
		}
		if(!isset($arrFechas[$fI][$fF][$dia]))
		{
			$arrFechas[$fI][$fF][$dia]=array();
		}
		if(!isset($arrFechas[$fI][$fF][$dia][$hI]))
		{
			$arrFechas[$fI][$fF][$dia][$hI]=array();
		}
		array_push($arrFechas[$fI][$fF][$dia][$hI],$fila);
		
	}
	if(sizeof($arrFechas)>0)
	{
		foreach($arrFechas as $fI=>$resto)
		{
			ksort($arrFechas[$fI]);
			foreach($arrFechas[$fI] as $fF=>$resto)
			{
				ksort($arrFechas[$fI][$fF]);
				foreach($arrFechas[$fI][$fF] as $dia=>$resto)
				{
					ksort($arrFechas[$fI][$fF][$dia]);
				}
				
			}
		}
		
		foreach($arrFechas as $fI=>$resto)
		{
			foreach($arrFechas[$fI] as $fF=>$resto)
			{
				foreach($arrFechas[$fI][$fF] as $dia=>$resto)
				{
					foreach($arrFechas[$fI][$fF][$dia] as $arrDias)
					{
						foreach($arrDias as $filaFechas)
							array_push($arrFinalHorario,$filaFechas);
					}
				}
				
			}
		}
	}
	return $arrFinalHorario;
}

function existeCambioFechaActiva($idGrupo)
{
	global $con;
	$consulta="SELECT COUNT(*) FROM 4548_solicitudesMovimientoGrupo s,4549_cabeceraSolicitudAME c WHERE c.idGrupo=".$idGrupo." AND  c.idSolicitudAME= s.idSolicitudAME and   tipoSolicitud =6  AND c.situacion in (1,2) ORDER BY idSolicitudMovimiento"; //Cambi de horario	

	$nReg=$con->obtenerValor($consulta);
	if($nReg>0)
		return true;
	$consulta="SELECT COUNT(*) FROM 4548_solicitudesMovimientoGrupo s,4548_gruposSolicitudesMovimiento c WHERE c.idGrupo=".$idGrupo." AND  c.idSolicitud= s.idSolicitudAME and   tipoSolicitud =7  AND s.situacion in (1,2) ORDER BY idSolicitudMovimiento"; //Cambi de horario	
	$nReg=$con->obtenerValor($consulta);
	if($nReg>0)
		return true;
	return false;
}

//Funciones simulacion ambiente;
function obtenerFechasHorarioGrupoV2($idGrupo,$idSolicitudAME=-1,$normalizarFecha=true,$considerarHorarioBD=true,$fechaInicio="",$fechaFin="",$idRegistroIgnorar=-1)
{
	global $con;
	$arrFinalHorario=array();
	$arrFechas=array();
	$comp="";
	if($fechaInicio!="")
		$comp=" and ".generarConsultaIntervalos($fechaInicio,$fechaFin,"fechaInicio","fechaFin");
	if($considerarHorarioBD)
	{
		$consulta="SELECT idHorarioGrupo,idGrupo,dia,horaInicio,horaFin,idAula,fechaInicio,fechaFin FROM 4522_horarioGrupo WHERE idGrupo=".$idGrupo." AND fechaInicio<=fechaFin ".$comp." and idHorarioGrupo not in (".$idRegistroIgnorar.") ORDER BY fechaInicio,fechaFin,dia,horaInicio,horaFin asc";

		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			array_push($arrFechas,$fila);
		}
	}
	$arrFechasComp=array();
	$fechaCambioInicio="";
	$consulta="SELECT datosSolicitud FROM 4548_solicitudesMovimientoGrupo s,4549_cabeceraSolicitudAME c,4548_gruposSolicitudesMovimiento g WHERE 
				c.idSolicitudAME=".$idSolicitudAME."  and c.idSolicitudAME= s.idSolicitudAME and tipoSolicitud =6 and g.idGrupo=".$idGrupo." 
				and s.idSolicitudMovimiento=g.idSolicitud	AND s.situacion in (1,2) ORDER BY idSolicitudMovimiento"; //Cambio de fecha
	
	$res=$con->obtenerFilas($consulta);
	if($con->filasAfectadas>0)
		$arrFechas=array();
	while($fila=mysql_fetch_row($res))
	{
		$objDatos=json_decode($fila[0]);
		if($fechaCambioInicio=="")
			$fechaCambioInicio=strtotime($objDatos->fechaAplicacion);
		else
		{
			if($fechaCambioInicio<strtotime($objDatos->fechaAplicacion))
				$fechaCambioInicio=strtotime($objDatos->fechaAplicacion);
		}
		foreach($objDatos->horarioCambio as $h)
		{
			$objFecha=array();
			$objFecha[0]="0";
			$objFecha[1]=$idGrupo;
			$objFecha[2]=$h->dia;
			$objFecha[3]=$h->horaInicial;
			$objFecha[4]=$h->horaFinal;
			$objFecha[5]=$h->idAula;
			$objFecha[6]=$objDatos->fechaAplicacion;
			$objFecha[7]=$objDatos->fechaTermino;

			array_push($arrFechas,$objFecha);
		}
	}

	$consulta="SELECT datosSolicitud FROM 4548_solicitudesMovimientoGrupo s,4549_cabeceraSolicitudAME c,4548_gruposSolicitudesMovimiento g WHERE 
			c.idSolicitudAME=".$idSolicitudAME."  and c.idSolicitudAME= s.idSolicitudAME and   tipoSolicitud =7 AND g.idGrupo=".$idGrupo." 
				and s.idSolicitudMovimiento=g.idSolicitud AND s.situacion in (1,2) ORDER BY idSolicitudMovimiento"; //Intercambio de curso
	
	$res=$con->obtenerFilas($consulta);
	if($con->filasAfectadas>0)
		$arrFechas=array();
	while($fila=mysql_fetch_row($res))
	{
		$objDatos=json_decode($fila[0]);
		foreach($objDatos->arrCambios as $oGrupo)
		{
			if($oGrupo->idGrupo==$idGrupo)
			{
				if($fechaCambioInicio=="")
					$fechaCambioInicio=strtotime($oGrupo->fechaInicioC);
				else
				{
					if($fechaCambioInicio<strtotime($oGrupo->fechaInicioC))
						$fechaCambioInicio=strtotime($oGrupo->fechaInicioC);
				}
				foreach($oGrupo->arrHorarioC as $h)
				{
					$objFecha=array();
					$objFecha[0]="0";
					$objFecha[1]=$idGrupo;
					$objFecha[2]=$h->dia;
					$objFecha[3]=$h->hInicio;
					$objFecha[4]=$h->hFin;
					$objFecha[5]=0;
					$objFecha[6]=$h->fInicio;
					$objFecha[7]=$h->fFIn;
					array_push($arrFechas,$objFecha);
				}
			}
		}
	}
	
	$consulta="SELECT datosSolicitud,idSolicitudMovimiento FROM 4548_solicitudesMovimientoGrupo s,4549_cabeceraSolicitudAME c,4548_gruposSolicitudesMovimiento g 
				WHERE c.idSolicitudAME=".$idSolicitudAME."  and c.idSolicitudAME= s.idSolicitudAME and s.idSolicitudMovimiento=g.idSolicitud and   
				tipoSolicitud =4 AND g.idGrupo=".$idGrupo." AND s.situacion in (1,2) ORDER BY idSolicitudMovimiento"; //Cambio de horario

	$res=$con->obtenerFilas($consulta);


	while($fila=mysql_fetch_row($res))
	{
		$posSave=0;
		if(($fechaCambioInicio=="")||($fechaCambioInicio<=strtotime($objDatos->fechaAplicacion)))
		{
			$objDatos=json_decode($fila[0]);
			
			if(sizeof($objDatos->horarioCambio)>0)
			{
				
				foreach($objDatos->horarioCambio as $h)
				{
					$removerRegistros=false;
					foreach($arrFechas as $hTmp)
					{
						if(!isset($hTmp[8]))
							$hTmp[8]=-1;
						$removerRegistros=false;
						if(($hTmp[6]==$objDatos->fechaAplicacion)&&($hTmp[8]!=$posSave)&&(strtotime($hTmp[6])<strtotime($hTmp[7])))
						{
							
							$removerRegistros=true;
							break;
						}
					}
					
					if($removerRegistros)
					{
						
						for($nTmp=0;$nTmp<sizeof($arrFechas);$nTmp++)
						{
							if(!isset($arrFechas[$nTmp][8]))
								$arrFechas[$nTmp][8]=-1;
							if(($arrFechas[$nTmp][6]==$objDatos->fechaAplicacion)&&($arrFechas[$nTmp][8]!=$posSave))
							{
								$arrFechas[$nTmp][7]=date("Y-m-d",strtotime("-1 days",strtotime($arrFechas[$nTmp][6])));
							}
						}
					}
					
					$objFecha=array();
					$objFecha[0]="0";
					$objFecha[1]=$idGrupo;
					$objFecha[2]=$h->dia;
					$objFecha[3]=$h->horaInicial;
					$objFecha[4]=$h->horaFinal;
					$objFecha[5]=$h->idAula;
					$objFecha[6]=$objDatos->fechaAplicacion;
					$objFecha[7]=$objDatos->fechaTermino;
					$objFecha[8]=$posSave;
					
					array_push($arrFechas,$objFecha);
				}
				
			}
			else
			{
				$consulta="SELECT datosSolicitud,idSolicitudMovimiento FROM 4548_solicitudesMovimientoGrupo s,4549_cabeceraSolicitudAME c,4548_gruposSolicitudesMovimiento g 
				WHERE c.idSolicitudAME=".$idSolicitudAME."  and c.idSolicitudAME= s.idSolicitudAME and s.idSolicitudMovimiento=g.idSolicitud and   
				tipoSolicitud =4 AND g.idGrupo=".$idGrupo." AND s.situacion in (1,2) and idSolicitudMovimiento>".$fila[1]." ORDER BY idSolicitudMovimiento";
				$fRegistro=$con->obtenerPrimeraFila($consulta);
				if(!$fRegistro)
				{
					$arrFechas=array();
					$objFecha=array();
					$objFecha[0]="0";
					$objFecha[1]=$idGrupo;
					$objFecha[2]=10;
					$objFecha[3]="00:00:00";
					$objFecha[4]="00:00:00";
					$objFecha[5]=0;
					$objFecha[6]=$objDatos->fechaAplicacion;
					$objFecha[7]=$objDatos->fechaTermino;
					array_push($arrFechas,$objFecha);
				}
			}
		}
		$posSave++;
	}	
	
	
	
	if(sizeof($arrFechas)>0)
	{
		$arrFechasAux=array();
		foreach($arrFechas as $hTmp)
		{
			if(strtotime($hTmp[6])<=strtotime($hTmp[7]))
				array_push($arrFechasAux,$hTmp);
		}
		$arrFechas=$arrFechasAux;
		
		$arrFinalHorario=ordenarFechasArreglo($arrFechas);
		
		if($normalizarFecha)
		{
			$arrFinalHorario=normalizarFechasBloque($arrFinalHorario);
		}
		
		$arrAux=array();
		foreach($arrFinalHorario as $h)
		{
			if(strtotime($h[6])<=strtotime($h[7]))
			{
				if($fechaInicio!="")
				{
					if(!colisionaTiempo($h[6],$h[7],$fechaInicio,$fechaFin,true))
						continue;
				}
				array_push($arrAux,$h);
			}
		}
		$arrFinalHorario=$arrAux;
	}
	
	return $arrFinalHorario;

}

function obtenerFechasAsignacionGrupoV2($idGrupo,$idSolicitudAME=-1,$normalizarFecha=true,$considerarAsignacionesBD=true,$fechaInicio="",$fechaFin="",$tipoAsignacion=0,$normalizarSuplencias=false)//0 indistinto,1= profesor titular;2 suplencia
{
	global $con;
	$tipoSolicitud="1,3";
	$comp2="1=1";
	if($tipoAsignacion!=0)
	{
		$tipoSolicitud=$tipoAsignacion;
		if($tipoAsignacion==1)
		{
			$comp2="idParticipacion=37";
		}
		else
		{

			$comp2="idParticipacion=45";
		}
	}
	$arrFechas=array();
	$comp="";
	if($fechaInicio!="")
		$comp=" and ".generarConsultaIntervalos($fechaInicio,$fechaFin,"fechaAsignacion","fechaBaja");
	
	if($considerarAsignacionesBD)
	{
		$arrAsignacionesBaja=array();
		$consulta="SELECT datosSolicitud,idAsignacion FROM 4548_solicitudesMovimientoGrupo s,4549_cabeceraSolicitudAME c,4548_gruposSolicitudesMovimiento g 
				WHERE c.idSolicitudAME=".$idSolicitudAME."  and c.idSolicitudAME= s.idSolicitudAME and tipoSolicitud in(2,5) AND g.idGrupo=".$idGrupo." and 
				s.idSolicitudMovimiento=g.idSolicitud AND s.situacion in(1,2) ORDER BY idSolicitudMovimiento"; //Cambio de fecha

		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$objDatos=json_decode($fila[0]);
			$arrAsignacionesBaja[$fila[1]]=$objDatos->fechaBaja;
		}
		
		$consulta="SELECT idAsignacionProfesorGrupo,fechaAsignacion,fechaBaja,idUsuario,idParticipacion FROM 4519_asignacionProfesorGrupo WHERE idGrupo=".$idGrupo." AND fechaAsignacion<=fechaBaja 
					".$comp." and ".$comp2." ORDER BY fechaAsignacion,fechaBaja asc";

			
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			
			if(isset($arrAsignacionesBaja[$fila[0]]))
				$fila[2]=$arrAsignacionesBaja[$fila[0]];
			$objDatos=json_decode($fila[0]);	
			$objFecha=array();
			$objFecha[0]=$fila[0];
			$objFecha[1]=$idGrupo;
			$objFecha[2]="0";
			$objFecha[3]="00:00:00";
			$objFecha[4]="00:00:00";
			$objFecha[5]=$fila[3];
			$objFecha[6]=$fila[1];
			$objFecha[7]=$fila[2];
			$objFecha[8]=$fila[4];
			$objFecha[9]=0;
			array_push($arrFechas,$objFecha);
		}
	}
	
	$arrFechasComp=array();
	$fechaCambioInicio="";
	$consulta="SELECT datosSolicitud FROM 4548_solicitudesMovimientoGrupo s,4549_cabeceraSolicitudAME c,4548_gruposSolicitudesMovimiento g WHERE c.idSolicitudAME=".$idSolicitudAME."  
			and c.idSolicitudAME= s.idSolicitudAME and  tipoSolicitud =6 AND g.idGrupo=".$idGrupo." and s.idSolicitudMovimiento=g.idSolicitud AND s.situacion in(1,2) ORDER BY idSolicitudMovimiento"; //Cambio de fecha
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		
		$objDatos=json_decode($fila[0]);
		if($fechaCambioInicio=="")
			$fechaCambioInicio=strtotime($objDatos->fechaAplicacion);
		else
		{
			if($fechaCambioInicio<strtotime($objDatos->fechaAplicacion))
				$fechaCambioInicio=strtotime($objDatos->fechaAplicacion);
		}
	}	
	
	if($fechaCambioInicio!="")
		$arrFechas=array();

	$idProfesorAnterior=-1;
	$idProfesorCambio=-1;
	
	$consulta="SELECT datosSolicitud,idSolicitudMovimiento FROM 4548_solicitudesMovimientoGrupo s,4549_cabeceraSolicitudAME c,4548_gruposSolicitudesMovimiento g WHERE c.idSolicitudAME=".$idSolicitudAME."  and c.idSolicitudAME= s.idSolicitudAME and   tipoSolicitud =7 
			AND g.idGrupo=".$idGrupo." AND s.situacion in(1,2) and s.idSolicitudMovimiento=g.idSolicitud ORDER BY idSolicitudMovimiento"; //Cambio de fecha
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		
		$objDatos=json_decode($fila[0]);
		foreach($objDatos->arrCambios as $oGrupo)
		{
			if($oGrupo->idGrupo==$idGrupo)
			{
				$arrFechas=array();
				$fechaInicio=$oGrupo->fechaInicioC;
				$fechaFin=$oGrupo->fechaFinC;
				if($tipoAsignacion==1)
				{
					if($fechaCambioInicio=="")
						$fechaCambioInicio=strtotime($oGrupo->fechaInicioC);
					else
					{
						if($fechaCambioInicio<strtotime($oGrupo->fechaInicioC))
							$fechaCambioInicio=strtotime($oGrupo->fechaInicioC);
					}
					$idProfesorAnterior=$oGrupo->idProfesorO;
					$idProfesorCambio=$oGrupo->idProfesorC;
					if($oGrupo->idProfesorC!=0)	
					{
						$objFecha=array();
						$objFecha[0]="0";
						$objFecha[1]=$idGrupo;
						$objFecha[2]="0";
						$objFecha[3]="00:00:00";
						$objFecha[4]="00:00:00";
						$objFecha[5]=$oGrupo->idProfesorC;
						$objFecha[6]=$oGrupo->fechaInicioC;
						$objFecha[7]=$oGrupo->fechaFinC;
						$objFecha[8]=37;
						$objFecha[9]=$fila[1];
						array_push($arrFechas,$objFecha);
					}
				}
			}
		}
	}

	$consulta="SELECT datosSolicitud,tipoSolicitud,idSolicitudMovimiento FROM 4548_solicitudesMovimientoGrupo s,4549_cabeceraSolicitudAME c,4548_gruposSolicitudesMovimiento g WHERE c.idSolicitudAME=".$idSolicitudAME."  and c.idSolicitudAME= s.idSolicitudAME and   
				tipoSolicitud in (".$tipoSolicitud.") AND g.idGrupo=".$idGrupo." AND s.situacion in(1,2) and s.idSolicitudMovimiento=g.idSolicitud ORDER BY idSolicitudMovimiento"; //Cambi de horario
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		$objDatos=json_decode($fila[0]);
		if(($fechaCambioInicio=="")||($fechaCambioInicio<=strtotime($objDatos->fechaAplicacion)))
		{

			if(($idProfesorAnterior!=$objDatos->idProfesor)&&($idProfesorCambio!=$objDatos->idProfesor))
			{

				$objFecha=array();
				$objFecha[0]="0";
				$objFecha[1]=$idGrupo;
				$objFecha[2]="0";
				$objFecha[3]="00:00:00";
				$objFecha[4]="00:00:00";
				$objFecha[5]=$objDatos->idProfesor;
				$objFecha[6]=$objDatos->fechaAplicacion;
				$objFecha[7]=$objDatos->fechaTermino;
				$objFecha[8]=37;
				$objFecha[9]=$fila[2];
				if($fila[1]==3)
					$objFecha[8]=45;

				array_push($arrFechas,$objFecha);
			}
		}
	}
	
	$arrFinalHorario=array();
	if(sizeof($arrFechas)>0)
	{
		$arrFinalHorario=ordenarFechasArreglo($arrFechas);
		
		if($normalizarFecha)
		{
			$arrFinalHorario=normalizarFechasBloque($arrFinalHorario,true);
		}
		
		
		
		$arrAux=array();
		
		
		foreach($arrFinalHorario as $h)
		{
			if(strtotime($h[6])<=strtotime($h[7]))
			{
				if($fechaInicio!="")
				{
					
					//varDump($h);
					if(!colisionaTiempo($h[6],$h[7],$fechaInicio,$fechaFin,true))
						continue;
				}
				
				array_push($arrAux,$h);
			}
		}
		$arrFinalHorario=$arrAux;
	}
		
	if($normalizarSuplencias)
	{
		$arrSuplencias=array();
		$arrTitulares=array();
		foreach($arrFinalHorario as $h)
		{
			if($h[8]==37)
				array_push($arrTitulares,$h);
			else	
				array_push($arrSuplencias,$h);	
		}
		if(sizeof($arrSuplencias)>0)
		{
			foreach($arrSuplencias as $s)
			{
				if(sizeof($arrTitulares)>0)
				{
					$arrTemporal=array();
					foreach($arrTitulares as $t)	
					{
						if(colisionaTiempo($s[6],$s[7],$t[6],$t[7],true))
						{
							if(strtotime($s[6])<=strtotime($t[6]))
							{
								if(strtotime($s[7])>=strtotime($t[7]))
								{
									
								}
								else
								{
									$t[6]=date("Y-m-d",strtotime("+1 days",strtotime($s[7])));	
									array_push($arrTemporal,$t);
								}	
							}
							else
							{
								$titularFin=$t[7];
								$t[7]=date("Y-m-d",strtotime("-1 days",strtotime($s[6])));		
								array_push($arrTemporal,$t);
								if(strtotime($titularFin)>strtotime($s[7]))
								{
									for($ct=0;$ct<10;$ct++)
										$tAux[$ct]=$t[$ct];
									$tAux[6]=date("Y-m-d",strtotime("+1 days",strtotime($s[7])));	
									$tAux[7]=$titularFin;
									
									$arrTemporal[sizeof($arrTemporal)-1][0]=0;
									$arrTemporal[sizeof($arrTemporal)-1][9]=0;
									array_push($arrTemporal,$tAux);
								}
							}
						}
						else
						{
							array_push($arrTemporal,$t);	
						}
					}
					$arrTitulares=$arrTemporal;
					
				}
				
				//
			}
			foreach($arrSuplencias as $s)
			{
				array_push($arrTemporal,$s);
			}
			
			
			$arrFinalHorario=array();
			
			foreach($arrTemporal as $h)
			{
				if(strtotime($h[6])<=strtotime($h[7]))
				{
					if($fechaInicio!="")
					{
						if(!colisionaTiempo($h[6],$h[7],$fechaInicio,$fechaFin,true))
							continue;
					}
					
					array_push($arrFinalHorario,$h);
				}
				
			}
			
		}
	}
	
	
	return $arrFinalHorario;

}

function obtenerSesionesPeriodo($idGrupo,$idSolicitudAME,$fechaInicio,$fechaFin)
{
	global $con;
	$consulta="select Plantel from 4520_grupos where idGrupos=".$idGrupo;
	$plantel=$con->obtenerValor($consulta);
	$arrDiasSesion=obtenerFechasHorarioGrupoV2($idGrupo,$idSolicitudAME,true,true,$fechaInicio,$fechaFin,-1);
	
	$arrTemp=array();
	
	
	
	$arrSesionesPeriodo=array();
	if(sizeof($arrDiasSesion)>0)
	{
		foreach($arrDiasSesion as $s)
		{
			if(!isset($arrTemp[$s[2]]))
				$arrTemp[$s[2]]=array();
			array_push($arrTemp[$s[2]],$s);
		}
		$arrDiasSesion=$arrTemp;
		
		$finalizar=false;
		$fechaAplicacion=strtotime($fechaInicio);
		$fechaFinPeriodo=strtotime($fechaFin);
		while(!$finalizar)
		{
			if((isset($arrDiasSesion[date("w",$fechaAplicacion)]))&&(!esDiaInhabilEscolar($plantel,date("Y-m-d",$fechaAplicacion))))
			{
				$arrHorario=$arrDiasSesion[date("w",$fechaAplicacion)];
				
				foreach($arrHorario as $h)	
				{
					if(($fechaAplicacion>=strtotime($h[6]))&&($fechaAplicacion<=strtotime($h[7])))
					{
						if(!isset($arrSesionesPeriodo[date("d/m/Y",$fechaAplicacion)]))
							$arrSesionesPeriodo[date("d/m/Y",$fechaAplicacion)]=array();
						array_push($arrSesionesPeriodo[date("d/m/Y",$fechaAplicacion)],$h);
					}
				}
			}
			$fechaAplicacion=strtotime("+1 days",$fechaAplicacion);

			if($fechaAplicacion>$fechaFinPeriodo)
			{
				$finalizar=true;
			}
		}	
	}
	return $arrSesionesPeriodo;
}

function obtenerFechasGrupoFinalV2($idGrupo,$idMovimiento,$considerarBD=true)
{
	global $con;
	$arrFinalHorario=array();
	$arrFechas=array();
	
	$removerRegistros=false;
	if($considerarBD)
	{
		$consulta="SELECT idHorarioGrupo,idGrupo,dia,horaInicio,horaFin,idAula,fechaInicio,fechaFin FROM 4522_horarioGrupo WHERE idGrupo=".$idGrupo." AND fechaInicio<=fechaFin  ORDER BY fechaInicio,fechaFin,dia,horaInicio,horaFin asc";
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			array_push($arrFechas,$fila);
		}
	}
	
	$consulta="SELECT datosSolicitud,idSolicitudAME,tipoSolicitud,idAsignacion FROM 4548_solicitudesMovimientoGrupo WHERE idSolicitudMovimiento=".$idMovimiento;
	$fSolicitudes=$con->obtenerPrimeraFila($consulta);
	$idSolicitudAME=$fSolicitudes[1];
	$posSave=0;
	$objDatos=json_decode($fSolicitudes[0]);
	
	if(sizeof($objDatos->horarioCambio)>0)
	{
		foreach($objDatos->horarioCambio as $h)
		{
			foreach($arrFechas as $hTmp)
			{
				if(!isset($hTmp[8]))
					$hTmp[8]=-1;
					
				
				$removerRegistros=false;
				if(($hTmp[6]==$objDatos->fechaAplicacion)&&($hTmp[8]!=$posSave)&&(strtotime($hTmp[6])<strtotime($hTmp[7])))
				{
					$removerRegistros=true;
					break;
				}
			}
			
			if($removerRegistros)
			{
				
				for($nTmp=0;$nTmp<sizeof($arrFechas);$nTmp++)
				{
					if(!isset($arrFechas[$nTmp][8]))
						$arrFechas[$nTmp][8]=-1;
						
					if(($arrFechas[$nTmp][6]==$objDatos->fechaAplicacion)&&($arrFechas[$nTmp][8]!=$posSave))
					{
						$arrFechas[$nTmp][7]=date("Y-m-d",strtotime("-1 days",strtotime($arrFechas[$nTmp][6])));
					}
				}
			}
			
			$objFecha=array();
			$objFecha[0]="0";
			$objFecha[1]=$idGrupo;
			$objFecha[2]=$h->dia;
			$objFecha[3]=$h->horaInicial;
			$objFecha[4]=$h->horaFinal;
			$objFecha[5]=$h->idAula;
			$objFecha[6]=$objDatos->fechaAplicacion;
			$objFecha[7]=$objDatos->fechaTermino;
			$objFecha[8]=$posSave;
			array_push($arrFechas,$objFecha);
		}
	
	}
	else
	{
		$consulta="SELECT datosSolicitud,idSolicitudMovimiento FROM 4548_solicitudesMovimientoGrupo s,4549_cabeceraSolicitudAME c,4548_gruposSolicitudesMovimiento g 
		WHERE c.idSolicitudAME=".$idSolicitudAME."  and c.idSolicitudAME= s.idSolicitudAME and s.idSolicitudMovimiento=g.idSolicitud and   
		tipoSolicitud =4 AND g.idGrupo=".$idGrupo." AND s.situacion in (1,2) and idSolicitudMovimiento>".$idMovimiento." ORDER BY idSolicitudMovimiento";
		$fRegistro=$con->obtenerPrimeraFila($consulta);
		if(!$fRegistro)
		{
			$objFecha=array();
			$objFecha[0]="0";
			$objFecha[1]=$idGrupo;
			$objFecha[2]=10;
			$objFecha[3]="00:00:00";
			$objFecha[4]="00:00:00";
			$objFecha[5]=0;
			$objFecha[6]=$objDatos->fechaAplicacion;
			$objFecha[7]=$objDatos->fechaTermino;
			array_push($arrFechas,$objFecha);
		}
	}
	$posSave++;
	
	if(sizeof($arrFechas)>0)
	{
		$arrFechasAux=array();
		foreach($arrFechas as $hTmp)
		{
			if(strtotime($hTmp[6])<=strtotime($hTmp[7]))
				array_push($arrFechasAux,$hTmp);
		}
		$arrFechas=$arrFechasAux;
		$arrFinalHorario=ordenarFechasArreglo($arrFechas);
		$arrFinalHorario=normalizarFechasBloque($arrFinalHorario);
		$arrAux=array();
		foreach($arrFinalHorario as $h)
		{
			if(strtotime($h[6])<=strtotime($h[7]))
			{
				array_push($arrAux,$h);
			}
		}
		$arrFinalHorario=$arrAux;
	}

	return $arrFinalHorario;

}

function obtenerHorarioAula($idAula,$idSolicitudAME,$dia,$fechaInicio,$fechaFin,$idRegistroIgnorar=-1,$idGrupo=-1)
{
	global $con;
	$arrHorarioAula=array();
	$compl=generarConsultaIntervalos($fechaInicio,$fechaFin,"h.fechaInicio","h.fechaFin");
	$consulta="SELECT DISTINCT h.idGrupo FROM 4522_horarioGrupo h, 4520_grupos g WHERE g.idGrupos<>".$idGrupo." and g.idGrupos=h.idGrupo and g.fechaInicio<=g.fechaFin and idAula=".$idAula." and dia=".$dia." and ".$compl;
	$filas=$con->obtenerFilas($consulta);
	if($con->filasAfectadas>0)
	{
		$mensajeDeColision="";
		while($fila=mysql_fetch_row($filas))	
		{
			$arrHorarioAula[$fila[0]]=array();
			$horaMatProf="SELECT dia,horaInicio,horaFin,fechaInicio,fechaFin,idGrupo FROM 4522_horarioGrupo h where idGrupo=".$fila[0]." and dia=".$dia."  and idHorarioGrupo not in (".$idRegistroIgnorar.") and ".$compl;

			$respuesta=$con->obtenerFilas($horaMatProf);				 
			$numFilas=$con->filasAfectadas;
			while($filaMatP=mysql_fetch_row($respuesta))
			{
				array_push($arrHorarioAula[$fila[0]],$filaMatP);
			}
		}
	}

	$consulta="SELECT distinct g.idGrupo FROM 4548_solicitudesMovimientoGrupo s,4549_cabeceraSolicitudAME c,4548_gruposSolicitudesMovimiento g WHERE 
				c.idSolicitudAME=".$idSolicitudAME."  and c.idSolicitudAME= s.idSolicitudAME and   tipoSolicitud in(4,6,7) and s.idSolicitudMovimiento=g.idSolicitud
				and s.situacion in (1,2) ORDER BY idSolicitudMovimiento";

	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		$arrHorarioGpo=obtenerFechasHorarioGrupoV2($fila[0],$idSolicitudAME,true,true,$fechaInicio,$fechaFin);
		$arrHorarioAula[$fila[0]]=array();
		foreach($arrHorarioGpo as $h)
		{
			if(($h[5]==$idAula)&($h[2]==$dia))
			{
				$oHorario[0]=$h[2];
				$oHorario[1]=$h[3];
				$oHorario[2]=$h[4];
				$oHorario[3]=$h[6];
				$oHorario[4]=$h[7];
				$oHorario[5]=$h[1];
				
				array_push($arrHorarioAula[$fila[0]],$oHorario);
			}
		}
	}
	
	
	
	
	$arrHorarioFinal=array();
	foreach($arrHorarioAula as $iGrupo=>$resto)
	{
		if(sizeof($resto)>0)
		{
			foreach($resto as $horario)
				array_push($arrHorarioFinal,$horario);
		}
	}
	
	return $arrHorarioFinal;
	
}

function obtenerAsignacionesProfesor($idProfesor,$idSolicitudAME,$fechaInicio,$fechaFin,$idGrupo=-1)//Pendiente
{
	global $con;
	$arrHorarioProf=array();
	
	$comp=generarConsultaIntervalos($fechaInicio,$fechaFin,"a.fechaAsignacion","a.fechaBaja");
	$consulta="select distinct a.idGrupo from 4519_asignacionProfesorGrupo a,4520_grupos g where    a.idGrupo<>".$idGrupo." and
			 g.situacion=1 and g.idGrupos=a.idGrupo AND a.idUsuario=".$idProfesor." and ".$comp;
	
	$res=$con->obtenerFilas($consulta);
	
	while($fila=mysql_fetch_row($res))	
	{
		$arrAsignacion=obtenerFechasAsignacionGrupoV2($fila[0],$idSolicitudAME,true,true,$fechaInicio,$fechaFin,0,true);
		foreach($arrAsignacion as $h)
		{
			if($h[5]==$idProfesor)
			{
				if(!isset($arrHorarioProf[$fila[0]]))
					$arrHorarioProf[$fila[0]]=array();
				array_push($arrHorarioProf[$fila[0]],$h);
			}
		}
	}
	

	$consulta="SELECT distinct g.idGrupo FROM 4548_solicitudesMovimientoGrupo s,4549_cabeceraSolicitudAME c,4548_gruposSolicitudesMovimiento g WHERE 
				c.idSolicitudAME=".$idSolicitudAME."  and c.idSolicitudAME= s.idSolicitudAME and   s.idSolicitudMovimiento=g.idSolicitud and g.idGrupo<>".$idGrupo."
				and s.situacion in (1,2) ORDER BY idSolicitudMovimiento";
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{

		$arrHorarioProf[$fila[0]]=array();

		$arrAsignacionAME=obtenerFechasAsignacionGrupoV2($fila[0],$idSolicitudAME,true,true,$fechaInicio,$fechaFin,0,true);
		foreach($arrAsignacionAME as $h)
		{
			if($h[5]==$idProfesor)
				array_push($arrHorarioProf[$fila[0]],$h);
		}
	}
	
	
	
	$arrAsignacionFinal=array();
	foreach($arrHorarioProf as $resto)
	{
		if(sizeof($resto)>0)
		{
			foreach($resto as $horario)
				array_push($arrAsignacionFinal,$horario);
		}
	}
	
	//varDump($arrAsignacionFinal);
	
	return $arrAsignacionFinal;
	
}

function obtenerFechasDuracionGrupo($idGrupo,$idSolicitudAME=-1)
{
	global $con;
	$arrFinalHorario=array();
	$arrFechas=array();
	$comp="";	
	
	$consulta="SELECT fechaInicio,fechaFin FROM 4520_grupos WHERE idGrupos=".$idGrupo;
	$fDatosGrupo=$con->obtenerPrimeraFila($consulta);
	
	$arrFechasComp=array();
	$fechaCambioInicio="";
	$consulta="SELECT datosSolicitud,tipoSolicitud FROM 4548_solicitudesMovimientoGrupo s,4549_cabeceraSolicitudAME c,4548_gruposSolicitudesMovimiento g WHERE 
				c.idSolicitudAME=".$idSolicitudAME."  and c.idSolicitudAME= s.idSolicitudAME and g.idGrupo=".$idGrupo." 
				and s.idSolicitudMovimiento=g.idSolicitud	AND s.situacion in (1,2) ORDER BY idSolicitudMovimiento"; //Cambio de fecha

	$res=$con->obtenerFilas($consulta);
	
	while($fila=mysql_fetch_row($res))
	{
		$objDatos=json_decode($fila[0]);
		switch($fila[1])
		{
			case 4:
				
				if(isset($objDatos->recalcularFechaTermino)&&($objDatos->recalcularFechaTermino==1))
				{
					$fDatosGrupo[1]=$objDatos->fechaTermino;
				}
				
				
			break;	
			case 6:
				$fDatosGrupo[0]=$objDatos->fechaAplicacion;
				$fDatosGrupo[1]=$objDatos->fechaTermino;
			break;	
			case 7:
				foreach($objDatos->arrCambios as $oGrupo)
				{
					if($oGrupo->idGrupo==$idGrupo)
					{
						$fDatosGrupo[0]=$oGrupo->fechaInicioC;
						$fDatosGrupo[1]=$oGrupo->fechaFinC;
						break;
					}
						
				}
			break;	
		}
	}

	
	return $fDatosGrupo;
	

}


//Funciones de formateo

function formatearSolicitudBajaFinalizacionSuplencia($fila)
{
	global $con;
	global $arrDiasSemana;
	global $dic;
	$cadObj="";
	$oDatos=json_decode($fila[3]);
	
	if($fila[14]==2)
	{
		$consulta="SELECT idUsuario,fechaAsignacion,fechaBaja FROM  4519_asignacionProfesorGrupo WHERE idAsignacionProfesorGrupo=".$fila[4];
		$fAsignacion=$con->obtenerPrimeraFila($consulta);
		$idUsuario=$fAsignacion[0];
		$nomProfesor=obtenerNombreUsuarioPaterno($idUsuario);
		$comp="";
		$consulta="SELECT CONCAT('(',nombreGrupo,') ',m.nombreMateria),g.idInstanciaPlanEstudio,g.idGrupos FROM 4520_grupos g,4502_Materias m,4548_gruposSolicitudesMovimiento s WHERE idGrupos=s.idGrupo and s.idSolicitud=".$fila[0]."
					AND m.idMateria=g.idMateria";
		$fGrupoSol=$con->obtenerPrimeraFila($consulta);	
		$lblGrupo=$fGrupoSol[0];
		$consulta="SELECT descripcionMotivo FROM 4588_motivosBajaProfesor WHERE idMotivoBaja=".$fila[10];
		$motivo=$con->obtenerValor($consulta);
		
		
		
		$arrHorario=array();
		
		
		if(strtotime($fAsignacion[1])>strtotime($oDatos->fechaBaja))
		{
			$consulta="SELECT * FROM 4522_horarioGrupo WHERE idGrupo=".$fGrupoSol[2]." and '".$fAsignacion[1]."'>=fechaInicio AND '".$fAsignacion[1]."'<=fechaFin AND  fechaInicio<=fechaFin order by dia";
			
			$resHorario=$con->obtenerFilas($consulta);
			while($fHorarioGrupo=mysql_fetch_row($resHorario))
			{
				array_push($arrHorario,	$fHorarioGrupo);
			}
			$comp=" (El profesor no imparte sesión alguna al grupo)";
		}
		else
		{
			$arrHorario=obtenerFechasHorarioGrupoV2($fGrupoSol[2],$fila[5],true,true,$fAsignacion[1],$oDatos->fechaBaja);
			
			
			switch($fila[2])
			{
				case 2:
					$comp=" (Fecha de inicio de asignación: ".date("d/m/Y",strtotime($fAsignacion[1])).")";
				break;
				case 5:
					$comp=" (Fecha de inicio de suplencia: ".date("d/m/Y",strtotime($fAsignacion[1])).")";
				break;
			}
		}
	
		$tblHorario="<br><table width='355'><tr height='21'><td width='70' align='lef'><span class='corpo8_bold'>Día</span></td><td width='100' align='left'>";
		
		$tblHorario.="<span class='corpo8_bold'>Horario</span></td><td width='220' align='left'><span class='corpo8_bold'>Aula</span></td></tr>";
		$tblHorario.="<tr height='1'><td colspan='3' style='background-color:#900'></td></tr>";
		foreach($arrHorario as $h)
		{
			$consulta="SELECT nombreArea FROM 9309_ubicacionesFisicas WHERE idAreaFisica=".$h[5];
			$nAula=$con->obtenerValor($consulta);
			$tblHorario.='<tr height=\'21\'><td>'.utf8_encode($arrDiasSemana[$h[2]]).'</td><td>'.date("H:i",strtotime($h[3])).' - '.date("H:i",strtotime($h[4])).'</td><td>'.$nAula.' (Del '.date("d/m/Y",strtotime($h[6])).' al '.date("d/m/Y",strtotime($h[7])).')</td></tr>';
		}
		$tblHorario.='</table>';
		
		switch($fila[2])
		{
			case 2:
				$cadObj="<table><tr height='21'><td width='160'><span class='letraRojaSubrayada8'><b>Grupo a afectar:</b></span></td><td valign='top' class='corpo8_bold'>".$lblGrupo."</td></tr>".
						"<tr height='21'><td width='160'><span class='letraRojaSubrayada8'><b>Plan de estudios:</b></span></td><td valign='top' >".cv(obtenerNombreInstanciaPlan($fGrupoSol[1]))."</td></tr>".
						"<tr height='21'><td width='160'><span class='letraRojaSubrayada8'><b>Descripción:</b></span></td><td valign='top'>Baja del profesor <b>".$nomProfesor."</b>, último día de labores ".date("d/m/Y",strtotime($oDatos->fechaBaja)).$comp."</td></tr>";
				$cadObj.="<tr height='21'><td ><span class='letraRojaSubrayada8'><b>Motivo del movimiento:</b></span></td><td valign='top'>".cv($motivo)."</td></tr>";
				$cadObj.="<tr height='21'><td width='160'><span class='letraRojaSubrayada8'><b>Horario del grupo:</b></span></td><td valign='top' >".$tblHorario."</td></tr>";
				$cadObj.="</table>";
			break;
			case 5:
				$cadObj="<table><tr height='21'><td width='160'><span class='letraRojaSubrayada8'><b>Grupo a afectar:</b></span></td><td valign='top' class='corpo8_bold'>".$lblGrupo."</td></tr>".
						"<tr height='21'><td width='160'><span class='letraRojaSubrayada8'><b>Plan de estudios:</b></span></td><td valign='top' >".cv(obtenerNombreInstanciaPlan($fGrupoSol[1]))."</td></tr>".
						"<tr height='21'><td width='160'><span class='letraRojaSubrayada8'><b>Descripción:</b></span></td><td valign='top'>Finalización de suplencia del profesor <b>".$nomProfesor."</b>, último día de labores ".date("d/m/Y",strtotime($oDatos->fechaBaja)).$comp."</td></tr>";
				$cadObj.="<tr height='21'><td ><span class='letraRojaSubrayada8'><b>Motivo del movimiento:</b></span></td><td valign='top'>".cv($motivo)."</td></tr>";
				$cadObj.="<tr height='21'><td width='160'><span class='letraRojaSubrayada8'><b>Horario del grupo:</b></span></td><td valign='top' >".$tblHorario."</td></tr>";
				$cadObj.="</table>";
			break;
			
		}
	}
	else
	{
		$consulta="SELECT idGrupo FROM 4548_gruposSolicitudesMovimiento WHERE idSolicitud=".$fila[0];
		$idGrupo=$con->obtenerValor($consulta);
		switch($fila[2])
		{
			case 2:
				$obj=$oDatos;
				$consulta="select concat(Paterno,' ',Materno,' ',Nom) as nombre from 802_identifica i where idUsuario=".$obj->idProfesorSuplencia;
				$nProfesor=$con->obtenerValor($consulta);
				$consulta="SELECT m.nombreMateria,nombreGrupo FROM 4520_grupos g,4502_Materias m WHERE m.idMateria=g.idMateria AND g.idGrupos=".$idGrupo;
				$fMateria=$con->obtenerPrimeraFila($consulta);
				$consulta="SELECT fechaBaja,m.motivo FROM _447_tablaDinamica t,_448_motivoBajaGrid m WHERE id__447_tablaDinamica=".$obj->idRegistro." 
							and m.id__448_motivoBajaGrid=t.motivoBaja";
				$fRegistro=$con->obtenerPrimeraFila($consulta);
				$fechaInicioBaja=date("d/m/Y",strtotime($fRegistro[0]));
				$motivo=$fRegistro[1];
				$descripcion="<span style='color:#900;font-weight:bold'>Baja del profesor</span> ".$nProfesor." del grupo <b>".$fMateria[1]."</b> de ".strtolower($dic["materia"]["s"]["el"]." ".$dic["materia"]["s"]["et"]).": <b>".$fMateria[0]."</b> a partir del día: ".$fechaInicioBaja." por el siguiente motivo: ".$motivo;
				if(isset($obj->idProfesorSuple)&&($obj->idProfesorSuple!=-1))
				{
					$consulta="select concat(Paterno,' ',Materno,' ',Nom) as nombre from 802_identifica i where idUsuario=".$obj->idProfesorSuple;
					$nProfesor=$con->obtenerValor($consulta);
					$descripcion.="<br><br>El <font color='red'><b>nuevo profesor</b></font> titular del grupo ser&aacute; <b>".$nProfesor."</b> a partir del día: ".date("d/m/Y",strtotime($obj->fechaReemplaza));
				}
				$consulta="SELECT MIN(fechaInicio) FROM 4522_horarioGrupo WHERE idGrupo=".$idGrupo;
				$tabla="";
				$fechaInicioH=$con->obtenerValor($consulta);
				if($fechaInicioH!="")
				{
					$fechaActual=strtotime(date("Y-m-d"));
					$fechaInicioH=strtotime($fechaInicioH);
					$consulta="";
					if($fechaInicioH>$fechaActual)
						$consulta="SELECT dia,horaInicio,horaFin,idAula FROM 4522_horarioGrupo WHERE idGrupo=".$idGrupo;
					else
						$consulta="SELECT dia,horaInicio,horaFin,idAula FROM 4522_horarioGrupo WHERE idGrupo=".$idGrupo." and '".date("Y-m-d",$fechaActual)."'>=fechaInicio and '".date("Y-m-d",$fechaActual)."'<=fechaFin";
					$resH=$con->obtenerFilas($consulta);
					$listHorarioAct="<table>";
					
					while($filaHorario=mysql_fetch_row($resH))
					{
						
						$consulta="SELECT nombreArea FROM 9309_ubicacionesFisicas WHERE idAreaFisica=".$filaHorario[3];
						$nAula=$con->obtenerValor($consulta);
						$listHorarioAct.='<tr><td><img src=\'../images/bullet_green.png\'></td><td>'.utf8_encode($arrDiasSemana[$filaHorario[0]])." ".date("H:i",strtotime($filaHorario[1]))."-".date("H:i",strtotime($filaHorario[2]))." (Aula: ".$nAula.")<br></td></tr>";
					}
					$listHorarioAct.="</table>";
					$tabla='<table>
								<tr>
									<td align=\'center\' width=\'600\'>
										<span class=\'letraFichaRespuesta\'>
									Horario actual
										</span>
									</td>
									
								</tr>
								<tr>
									<td align=\'left\'>
									'.$listHorarioAct.'
									</td>
									
								</tr>
							</table>';
				}
				$descripcion.="<br><br>".$tabla;
				$cadObj=$descripcion;
			break;
			case 5:
				$obj=$oDatos;
				$consulta="SELECT dteFechaBaja,idAsignacion FROM _449_tablaDinamica WHERE id__449_tablaDinamica=".$obj->idRegistro;
				$fDatos=$con->obtenerPrimeraFila($consulta);
				$consulta="SELECT idUsuario,fechaAsignacion,fechaBaja,idGrupo FROM 4519_asignacionProfesorGrupo WHERE idAsignacionProfesorGrupo=".$fDatos[1];
				$fAsignacion=$con->obtenerPrimeraFila($consulta);
				$consulta="select concat(Paterno,' ',Materno,' ',Nom) as nombre from 802_identifica i where idUsuario=".$fAsignacion[0];
				$nProfesor=$con->obtenerValor($consulta);
				$consulta="SELECT m.nombreMateria,nombreGrupo FROM 4520_grupos g,4502_Materias m WHERE m.idMateria=g.idMateria AND g.idGrupos=".$fAsignacion[3];
				$fMateria=$con->obtenerPrimeraFila($consulta);
				$descripcion="<span style='color:#900;font-weight:bold'>Finalización de suplencia</span> (del ".date("d/m/Y",strtotime($fAsignacion[1]))." al ".date("d/m/Y",strtotime($fAsignacion[2])).
								") del profesor ".$nProfesor." del grupo <b>".$fMateria[1]."</b> de ".strtolower($dic["materia"]["s"]["el"].
								" ".$dic["materia"]["s"]["et"]).": <b>".$fMateria[0]."</b>  a partir del día: ".date("d/m/Y",strtotime($fDatos[0]));
				$consulta="SELECT MIN(fechaInicio) FROM 4522_horarioGrupo WHERE idGrupo=".$idGrupo;
				$tabla="";
				$fechaInicioH=$con->obtenerValor($consulta);
				if($fechaInicioH!="")
				{
					$fechaActual=strtotime(date("Y-m-d"));
					$fechaInicioH=strtotime($fechaInicioH);
					$consulta="";
					if($fechaInicioH>$fechaActual)
						$consulta="SELECT dia,horaInicio,horaFin,idAula FROM 4522_horarioGrupo WHERE idGrupo=".$idGrupo;
					else
						$consulta="SELECT dia,horaInicio,horaFin,idAula FROM 4522_horarioGrupo WHERE idGrupo=".$idGrupo." and '".date("Y-m-d",$fechaActual)."'>=fechaInicio and '".date("Y-m-d",$fechaActual)."'<=fechaFin";
					$resH=$con->obtenerFilas($consulta);
					$listHorarioAct="<table>";
					
					while($filaHorario=mysql_fetch_row($resH))
					{
						
						$consulta="SELECT nombreArea FROM 9309_ubicacionesFisicas WHERE idAreaFisica=".$filaHorario[3];
						$nAula=$con->obtenerValor($consulta);
						$listHorarioAct.='<tr><td><img src=\'../images/bullet_green.png\'></td><td>'.utf8_encode($arrDiasSemana[$filaHorario[0]])." ".date("H:i",strtotime($filaHorario[1]))."-".date("H:i",strtotime($filaHorario[2]))." (Aula: ".$nAula.")<br></td></tr>";
					}
					$listHorarioAct.="</table>";
					$tabla='<table>
								<tr>
									<td align=\'center\' width=\'600\'>
										<span class=\'letraFichaRespuesta\'>
									Horario actual
										</span>
									</td>
									
								</tr>
								<tr>
									<td align=\'left\'>
									'.$listHorarioAct.'
									</td>
									
								</tr>
							</table>';
				}
				$descripcion.="<br><br>".$tabla;
				$cadObj=$descripcion;
			break;
			
		}
	}
	$cadObj=str_replace("\r","",$cadObj);
	$cadObj=str_replace("\n","",$cadObj);
	return $cadObj;
}

function formatearSolicitudCambioHorarioFecha($fila)
{
	global $con;
	global $arrDiasSemana;
	global $dic;
	$cadObj="";

	$oDatos=json_decode($fila[3]);

	if($fila[14]==2)
	{
		$motivo=$oDatos->motivo;
		$consulta="SELECT CONCAT('(',nombreGrupo,') ',m.nombreMateria),g.idInstanciaPlanEstudio,g.idGrupos FROM 4520_grupos g,4502_Materias m,4548_gruposSolicitudesMovimiento s WHERE idGrupos=s.idGrupo and s.idSolicitud=".$fila[0]."
					AND m.idMateria=g.idMateria";
		
		$fGrupoSol=$con->obtenerPrimeraFila($consulta);	
		
		$lblGrupo=$fGrupoSol[0];
		$tblHorario="<table><tr><td  valign='top'>";
		
		
			$tblHorario.="<b>Horario anterior:</b><br><br><table width='500'><tr height='21'><td width='70' align='lef'><span class='corpo8_bold'>Día</span></td><td width='85' align='left'>";
			$tblHorario.="<span class='corpo8_bold'>Horario</span></td><td width='140' align='left'><span class='corpo8_bold'>Aula</span></td><td width='160' align='left'><span class='corpo8_bold'>Periodo</span></td></tr>";
			$tblHorario.="<tr height='1'><td colspan='4' style='background-color:#900'></td></tr>";
			if(sizeof($oDatos->horarioAnt)>0)
			{
				foreach($oDatos->horarioAnt as $h)
				{
					if($h->dia!=10)
					{
						if($h->idAula=="")
							$h->idAula=-1;
						$consulta="SELECT nombreArea FROM 9309_ubicacionesFisicas WHERE idAreaFisica=".$h->idAula;
						$nAula=$con->obtenerValor($consulta);
						$tblHorario.='<tr height=\'21\'><td>'.utf8_encode($arrDiasSemana[$h->dia]).'</td><td>'.date("H:i",strtotime($h->horaInicial)).' - '.date("H:i",strtotime($h->horaFinal)).'</td><td>'.$nAula.
									'</td><td>Del '.date("d/m/Y",strtotime($h->fechaInicial)).' al '.date("d/m/Y",strtotime($h->fechaFinal)).'</td></tr>';	
					}
				}
	
			}
		$tblHorario.="</table>";
		$tblHorario.="</td>";
		$tblHorario.="<td width='20'></td><td  valign='top'>";
		
		
		$tblHorario.="<b>Horario asignado:</b><br><br><table width='355'><tr height='21'><td width='70' align='lef'><span class='corpo8_bold'>Día</span></td><td width='85' align='left'>";
		$tblHorario.="<span class='corpo8_bold'>Horario</span></td><td width='140' align='left'><span class='corpo8_bold'>Aula</span></td></tr>";
		$tblHorario.="<tr height='1'><td colspan='3' style='background-color:#900'></td></tr>";
		if(sizeof($oDatos->horarioCambio)>0)
		{
			foreach($oDatos->horarioCambio as $h)
			{
				if($h->idAula=="")
						$h->idAula=-1;
				$consulta="SELECT nombreArea FROM 9309_ubicacionesFisicas WHERE idAreaFisica=".$h->idAula;
				$nAula=$con->obtenerValor($consulta);
				$tblHorario.='<tr height=\'21\'><td>'.utf8_encode($arrDiasSemana[$h->dia]).'</td><td>'.date("H:i",strtotime($h->horaInicial)).' - '.date("H:i",strtotime($h->horaFinal)).'</td><td>'.$nAula.'</td></tr>';
			}
			
		}
		$tblHorario.='</table>';
		$tblHorario.="</td>";
		$tblHorario.="</tr></table>";
	
		switch($fila[2])
		{
			case 4:

				$compFechaTermino="";
				if(isset($oDatos->recalcularFechaTermino)&&($oDatos->recalcularFechaTermino==1))
				{
					$compFechaTermino=" (Fecha original de finalizaci&oacute;n ".date("d/m/Y",strtotime($oDatos->fechaTerminoOriginal)).")";	
				}
				$cadObj="<table><tr height='21'><td width='160'><span class='letraRojaSubrayada8'><b>Grupo a afectar:</b></span></td><td valign='top' class='corpo8_bold'>".$lblGrupo."</td></tr>".
						"<tr height='21'><td width='160'><span class='letraRojaSubrayada8'><b>Plan de estudios:</b></span></td><td valign='top' >".cv(obtenerNombreInstanciaPlan($fGrupoSol[1]))."</td></tr>".
						"<tr height='21'><td width='160'><span class='letraRojaSubrayada8'><b>Descripción:</b></span></td><td valign='top'><span style='color:#900;font-weight:bold'>Cambio de horario</span> del curso, abarcando el periodo <b>del</b> ".date("d/m/Y",strtotime($oDatos->fechaAplicacion)).
						" <b>al</b> ".date("d/m/Y",strtotime($oDatos->fechaTermino))."".$compFechaTermino.", el curso será impartido en el siguiente horario:<br><br>".$tblHorario."</td></tr><tr height='10'><td colspan='2'></td></tr>";
				
				
				
				
				if(isset($oDatos->ajustarProfesorTitular))
				{
					if(($oDatos->ajustarProfesorTitular==1)&&($oDatos->ultimoProfesorTitular!=0))
					{
						$nProfesor="";
						$fechaInicio="";
						if($oDatos->ultimoProfesorTitular>0)
						{
							$consulta="SELECT idUsuario,fechaAsignacion FROM 4519_asignacionProfesorGrupo WHERE idAsignacionProfesorGrupo=".$oDatos->ultimoProfesorTitular;
							$fProfesor=$con->obtenerPrimeraFila($consulta);
							if($fProfesor)
							{
								$nProfesor=obtenerNombreUsuarioPaterno($fProfesor[0]);
								$fechaInicio=date("d/m/Y",strtotime($fProfesor[1]));
							}
						}
						else
						{
							$consulta="SELECT tipoSolicitud,datosSolicitud FROM 4548_solicitudesMovimientoGrupo WHERE idSolicitudMovimiento=".($oDatos->ultimoProfesorTitular*-1);
							$fProfesor=$con->obtenerPrimeraFila($consulta);
							
							switch($fProfesor[0])
							{
								case 1:
									$dMovimiento=json_decode($fProfesor[1]);
									$nProfesor=obtenerNombreUsuarioPaterno($dMovimiento->idProfesor);
									$fechaInicio=date("d/m/Y",strtotime($dMovimiento->fechaAplicacion));
									
								break;
								case 7:
									$objDatos=json_decode($fProfesor[1]);
									foreach($objDatos->arrCambios as $oGrupo)
									{
										if($oGrupo->idGrupo==$fGrupoSol[2])
										{
											$idProfesorCambio=$oGrupo->idProfesorC;
											if($oGrupo->idProfesorC!=0)	
											{
												$nProfesor=obtenerNombreUsuarioPaterno($oGrupo->idProfesorC);
												$fechaInicio=date("d/m/Y",strtotime($oGrupo->fechaInicioC));
									
											}
											
										}
									}
								break;	
							}
							
						}
						$compFecha="";
						if(isset($oDatos->fechaProfesorOriginal))
						{
							$compFecha=" (Fecha original de finalizaci&oacute;n ".date("d/m/Y",strtotime($oDatos->fechaProfesorOriginal)).")";	
						}
						$cadObj.="<tr height='21'><td ><span class='letraRojaSubrayada8'></span></td><td valign='top'>El profesor <b>".$nProfesor."</b> ser&aacute; titular en el periodo del ".$fechaInicio." al ".date("d/m/Y",strtotime($oDatos->fechaTermino)).
								$compFecha."</td></tr>";
					}
				}
				if(isset($oDatos->cursoFinaliza)&&($oDatos->cursoFinaliza==1))
				{
					$cadObj.="<tr height='21'><td ><span class='letraRojaSubrayada8'></span></td><td valign='top'><span class='letraRojaSubrayada8'>El curso finaliza con este movimiento</span></td></tr>";
				}
				$cadObj.="<tr height='21'><td ><span class='letraRojaSubrayada8'><b>Motivo del movimiento:</b></span></td><td valign='top'>".cv($motivo)."</td></tr></table>";
			break;
			case 6:
				$compBloque="";
				if(isset($oDatos->bloque))
					$compBloque=" (Asociado al bloque ".$oDatos->bloque.")";
				$cadObj="<table><tr height='21'><td width='160'><span class='letraRojaSubrayada8'><b>Grupo a afectar:</b></span></td><td valign='top' class='corpo8_bold'>".$lblGrupo."</td></tr>".
							"<tr height='21'><td width='160'><span class='letraRojaSubrayada8'><b>Plan de estudios:</b></span></td><td valign='top' >".cv(obtenerNombreInstanciaPlan($fGrupoSol[1]))."</td></tr>".
							"<tr height='21'><td width='160'><span class='letraRojaSubrayada8'><b>Descripción:</b></span></td><td valign='top'><span style='color:#900;font-weight:bold'>Cambio de fecha</span> del curso, abarcando el periodo <b>del</b> ".date("d/m/Y",strtotime($oDatos->fechaAplicacion)).
				" <b>al</b> ".date("d/m/Y",strtotime($oDatos->fechaTermino))."".$compBloque.", el curso será impartido en el siguiente horario:<br><br>".$tblHorario."</td></tr><tr height='10'><td colspan='2'></td></tr>";
				$cadObj.="<tr height='21'><td ><span class='letraRojaSubrayada8'><b>Motivo del movimiento:</b></span></td><td valign='top'>".cv($motivo)."</td></tr></table>";
			break;
		}
	}
	else
	{
		$consulta="SELECT idGrupo FROM 4548_gruposSolicitudesMovimiento WHERE idSolicitud=".$fila[0];
		$idGrupo=$con->obtenerValor($consulta);
		switch($fila[2])
		{
			case 4:
				$obj=$oDatos;
				$consulta="SELECT m.nombreMateria,nombreGrupo FROM 4520_grupos g,4502_Materias m WHERE m.idMateria=g.idMateria AND g.idGrupos=".$idGrupo;
				$fMateria=$con->obtenerPrimeraFila($consulta);
				$listHorarioAct="<table>";
				
				foreach($obj->horarioAnt as $d)
				{
					$consulta="SELECT nombreArea FROM 9309_ubicacionesFisicas WHERE idAreaFisica=".$d->idAula;
					$nAula=$con->obtenerValor($consulta);
					$listHorarioAct.='<tr><td><img src=\'../images/bullet_green.png\'></td><td>'.utf8_encode($arrDiasSemana[$d->dia])." ".$d->horaInicio."-".$d->horaFin."<br>(Aula: ".$nAula.")<br></td></tr>";
				}
				$listHorarioAct.="</table>";
				$listHorarioMod="<Table>";
				foreach($obj->horarioCambio as $d)
				{
					$consulta="SELECT nombreArea FROM 9309_ubicacionesFisicas WHERE idAreaFisica=".$d->idAula;
					$nAula=$con->obtenerValor($consulta);
					$listHorarioMod.='<tr><td><img src=\'../images/bullet_green.png\'></td><td>'.utf8_encode($arrDiasSemana[$d->dia])." ".$d->horaInicio."-".$d->horaFin."<br>(Aula: ".$nAula.")<br></td></tr>";
				}
				$listHorarioMod.="</Table>";
				$tabla='<table>
							<tr>
								<td align=\'center\' width=\'350\'>
									<span class=\'letraFichaRespuesta\'>
								Horario actual
									</span>
								</td>
								<td align=\'center\' width=\'350\'>
									<span class=\'letraFichaRespuesta\'>
								Propuesta de horario
									</span>
								</td>
							</tr>
							<tr>
								<td align=\'left\'>
								'.$listHorarioAct.'
								</td>
								<td align=\'left\'>
								'.$listHorarioMod.'
								</td>
							</tr>
						</table><br>
						<span class=\'letraFichaRespuesta\'><b>Motivo:</b></span> '.$obj->motivo.'<br>';
				$descripcion="<span style='color:#900;font-weight:bold'>Solicitud de cambio de horario</span> del grupo <b>".$fMateria[1]."</b> de ".strtolower($dic["materia"]["s"]["el"]." ".$dic["materia"]["s"]["et"]).
							": <b>".$fMateria[0]."</b> a partir del día: <b>".date("d/m/Y",strtotime($obj->fechaAplicacion))."</b> de la siguiente manera: <bR><br>".$tabla;
				$cadObj=$descripcion;					
			break;
			case 6:
				$obj=$oDatos;
				$tabla="";
				$consulta="SELECT m.nombreMateria,nombreGrupo,fechaInicio,fechaFin FROM 4520_grupos g,4502_Materias m WHERE m.idMateria=g.idMateria AND g.idGrupos=".$idGrupo;
				$fMateria=$con->obtenerPrimeraFila($consulta);
				$descripcion="<span style='color:#900;font-weight:bold'>Cambio de fecha de inicio de curso</span> del grupo <b>".$fMateria[1]."</b> de ".strtolower($dic["materia"]["s"]["el"]." ".$dic["materia"]["s"]["et"]).": <b>".$fMateria[0]."</b></td></tr>";
				$listHorarioAct="Del ".date("d/m/Y",strtotime($fMateria[2]))." al ".date("d/m/Y",strtotime($fMateria[3]));
				$fechaTermino=$obj->fechaTermino;
				$listHorarioMod="Del ".date("d/m/Y",strtotime($obj->fechaInicio))." al ".date("d/m/Y",strtotime($fechaTermino));
				$listHAct="";
				$listHMod="";
				$listHMod="<Table>";
				foreach($obj->arrHorario as $h)
				{
					$consulta="SELECT nombreArea FROM 9309_ubicacionesFisicas WHERE idAreaFisica=".$h->idAula;
					$nAula=$con->obtenerValor($consulta);
					$listHMod.="<tr><td><img src='../images/bullet_green.png'></td><td>".utf8_encode($arrDiasSemana[$h->dia])." ".$h->hInicio."-".$h->hFin."<br>(Aula: ".$nAula.")<br>";
				}
				$listHMod.="</Table>";
				$fechaActual=date("Y-m-d");

				$consulta="SELECT MIN(fechaInicio) FROM 4522_horarioGrupo WHERE idGrupo=".$idGrupo;
				$fechaInicioH=$con->obtenerValor($consulta);
				$consulta="SELECT idHorarioGrupo,dia,horaInicio,horaFin,idAula FROM 4522_horarioGrupo WHERE idGrupo=".$idGrupo;
				if($fechaInicioH!="")
				{
					$fechaInicioH=strtotime($fechaInicioH);
					if($fechaInicioH<=strtotime(date("Y-m-d")))
					{
						$consulta="SELECT max(fechaFin) FROM 4522_horarioGrupo WHERE idGrupo=".$idGrupo;
						
						$fecha=$con->obtenerValor($consulta);
						$fechaH=strtotime($fecha);
						if($fechaH<strtotime(date("Y-m-d")))
							$consulta="SELECT idHorarioGrupo,dia,horaInicio,horaFin,idAula FROM 4522_horarioGrupo WHERE idGrupo=".$idGrupo." and fechaFin='".date("Y-m-d",$fechaH)."' and fechaInicio<=fechaFin";
						else
							$consulta="SELECT idHorarioGrupo,dia,horaInicio,horaFin,idAula FROM 4522_horarioGrupo WHERE idGrupo=".$idGrupo." and '".$fechaActual."'>=fechaInicio and '".$fechaActual."'<=fechaFin";
					}
					else
					{
						$consulta="SELECT idHorarioGrupo,dia,horaInicio,horaFin,idAula FROM 4522_horarioGrupo WHERE idGrupo=".$idGrupo." and fechaInicio='".date("Y-m-d",$fechaInicioH)."' and fechaInicio<=fechaFin";
					}
				}
				$res=$con->obtenerFilas($consulta);
				$listHAct="<Table>";
				while($fHorario=mysql_fetch_row($res))
				{
					$consulta="SELECT nombreArea FROM 9309_ubicacionesFisicas WHERE idAreaFisica=".$fHorario[4];
					$nAula=$con->obtenerValor($consulta);
					$listHAct.="<tr><td><img src='../images/bullet_green.png'></td><td>".utf8_encode($arrDiasSemana[$fHorario[1]])." ".date("H:i A",strtotime($fHorario[2]))."-".date("H:i A",strtotime($fHorario[3]))."<br>(Aula: ".$nAula.")<br>";
				}
				$listHAct.="</Table>";
				$tabla='<table>
							<tr>
								<td align=\'center\' width=\'350\'>
									<span class=\'letraFichaRespuesta\'>
								Fecha actual del curso
									</span>
								</td>
								<td align=\'center\' width=\'350\'>
									<span class=\'letraFichaRespuesta\'>
								Propuesta de fecha de curso
									</span>
								</td>
							</tr>
							<tr>
								<td align=\'center\'>
								'.$listHorarioAct.'
								</td>
								<td align=\'center\'>
								'.$listHorarioMod.'
								</td>
							</tr>
							<tr>
								<td align=\'center\'>
								<br>
								<span class=\'letraFichaRespuesta\'>
								Horario
								</span>
								</td>
								<td align=\'center\'>
								<br>
								<span class=\'letraFichaRespuesta\'>
								Horario
								</span>
								</td>
							</tr>
							<tr>
								<td align=\'center\'>
								'.$listHAct.'
								</td>
								<td align=\'center\'>
								'.$listHMod.'
								</td>
							</tr>
						</table><br>
						<span class=\'letraFichaRespuesta\'><b>Motivo:</b></span> '.$obj->motivo.'<br>';
				
				
				
				$descripcion.="<br><br>".$tabla;
				$cadObj=$descripcion;
			break;
		}
	}
	$cadObj=str_replace("\r","",$cadObj);
	$cadObj=str_replace("\n","",$cadObj);
	return $cadObj;
}

function formatearSolicitudAltaSuplencia($fila)
{
	global $con;
	global $dic;
	global $arrDiasSemana;
	
	$cadObj="";
	$oDatos=json_decode($fila[3]);

	if($fila[14]==2)
	{
		
		$consulta="SELECT CONCAT('(',nombreGrupo,') ',m.nombreMateria),g.idInstanciaPlanEstudio,g.idGrupos FROM 4520_grupos g,4502_Materias m,4548_gruposSolicitudesMovimiento s WHERE idGrupos=s.idGrupo and s.idSolicitud=".$fila[0]." AND m.idMateria=g.idMateria";

		$fGrupoSol=$con->obtenerPrimeraFila($consulta);	
		if($fGrupoSol)
		{

			$lblGrupo=$fGrupoSol[0];

			$nomProfesor=obtenerNombreUsuarioPaterno($oDatos->idProfesor);
		
			$comp="";
			$arrHorario=obtenerFechasHorarioGrupoV2($fGrupoSol[2],$fila[5],true,true,$oDatos->fechaAplicacion,$oDatos->fechaTermino);
			
			
			$tblHorario="<br><table width='355'><tr height='21'><td width='70' align='lef'><span class='corpo8_bold'>Día</span></td><td width='100' align='left'>";
			
			$tblHorario.="<span class='corpo8_bold'>Horario</span></td><td width='220' align='left'><span class='corpo8_bold'>Aula</span></td></tr>";
			$tblHorario.="<tr height='1'><td colspan='3' style='background-color:#900'></td></tr>";

			foreach($arrHorario as $h)
			{
				if($h[2]!=10)
				{
					$consulta="SELECT nombreArea FROM 9309_ubicacionesFisicas WHERE idAreaFisica=".$h[5];
	
					$nAula=$con->obtenerValor($consulta);
					$tblHorario.='<tr height=\'21\'><td>'.utf8_encode($arrDiasSemana[$h[2]]).'</td><td>'.date("H:i",strtotime($h[3])).' - '.date("H:i",strtotime($h[4])).'</td><td>'.$nAula.' (Del '.date("d/m/Y",strtotime($h[6])).' al '.date("d/m/Y",strtotime($h[7])).')</td></tr>';
				}
			}
			$tblHorario.='</table>';
			
			switch($fila[2])
			{
				case 1:
				
					$cadObj="<table><tr height='21'><td width='160'><span class='letraRojaSubrayada8'><b>Grupo a afectar:</b></span></td><td valign='top' class='corpo8_bold'>".$lblGrupo."</td></tr>".
							"<tr height='21'><td width='160'><span class='letraRojaSubrayada8'><b>Plan de estudios:</b></span></td><td valign='top' >".cv(obtenerNombreInstanciaPlan($fGrupoSol[1]))."</td></tr>".
							"<tr height='21'><td width='160'><span class='letraRojaSubrayada8'><b>Descripción:</b></span></td><td valign='top'><span style='color:#900;font-weight:bold'>Alta</span> del profesor <b>".$nomProfesor."</b> en el periodo del ".date("d/m/Y",strtotime($oDatos->fechaAplicacion))." al ".date("d/m/Y",strtotime($oDatos->fechaTermino))."</td></tr>";
					$cadObj.="<tr height='21'><td ><span class='letraRojaSubrayada8'><b>Motivo del movimiento:</b></span></td><td valign='top'>".cv($oDatos->motivo)."</td></tr>";
					$cadObj.="<tr height='21'><td width='160'><span class='letraRojaSubrayada8'><b>Horario del grupo:</b></span></td><td valign='top' >".$tblHorario."</td></tr>";
					$cadObj.="</table>";
				break;
				case 3:
					$cadObj="<table><tr height='21'><td width='160'><span class='letraRojaSubrayada8'><b>Grupo a afectar:</b></span></td><td valign='top' class='corpo8_bold'>".$lblGrupo."</td></tr>".
							"<tr height='21'><td width='160'><span class='letraRojaSubrayada8'><b>Plan de estudios:</b></span></td><td valign='top' >".cv(obtenerNombreInstanciaPlan($fGrupoSol[1]))."</td></tr>".
							"<tr height='21'><td width='160'><span class='letraRojaSubrayada8'><b>Descripción:</b></span></td><td valign='top'><span style='color:#900;font-weight:bold'>Alta de suplencia</span> del profesor <b>".$nomProfesor."</b> en el periodo del ".date("d/m/Y",strtotime($oDatos->fechaAplicacion))." al ".date("d/m/Y",strtotime($oDatos->fechaTermino))."</td></tr>";
					$cadObj.="<tr height='21'><td ><span class='letraRojaSubrayada8'><b>Motivo del movimiento:</b></span></td><td valign='top'>".cv($oDatos->motivo)."</td></tr>";
					$cadObj.="<tr height='21'><td width='160'><span class='letraRojaSubrayada8'><b>Horario del grupo:</b></span></td><td valign='top' >".$tblHorario."</td></tr>";
					$cadObj.="</table>";
				break;
			}
		}
	}
	else
	{
		$consulta="SELECT idGrupo FROM 4548_gruposSolicitudesMovimiento WHERE idSolicitud=".$fila[0];
		$idGrupo=$con->obtenerValor($consulta);
		switch($fila[2])
		{
			case 1:
				$obj=$oDatos;
				$consulta="select concat(Paterno,' ',Materno,' ',Nom) as nombre from 802_identifica i where idUsuario=".$obj->idProfesorAsigna;

				$nProfesor=$con->obtenerValor($consulta);
				$consulta="SELECT m.nombreMateria,nombreGrupo FROM 4520_grupos g,4502_Materias m WHERE m.idMateria=g.idMateria AND g.idGrupos=".$idGrupo;
				$fMateria=$con->obtenerPrimeraFila($consulta);
				$descripcion="<span style='color:#900;font-weight:bold'>Asignación</span> del profesor ".$nProfesor." al grupo <b>".$fMateria[1]."</b> de ".strtolower($dic["materia"]["s"]["el"]." ".$dic["materia"]["s"]["et"]).": <b>".$fMateria[0]."</b>";
				if(isset($datos->fechaReemplaza))
				{
					$descripcion.=" a partir del día: ".date("d/m/Y",strtotime($obj->fechaReemplaza));
				}
				
				
				$consulta="SELECT MIN(fechaInicio) FROM 4522_horarioGrupo WHERE idGrupo=".$idGrupo;
				$tabla="";
				$fechaInicioH=$con->obtenerValor($consulta);
				if($fechaInicioH!="")
				{
					$fechaActual=strtotime(date("Y-m-d"));
					$fechaInicioH=strtotime($fechaInicioH);
					$consulta="";
					if($fechaInicioH>$fechaActual)
						$consulta="SELECT dia,horaInicio,horaFin,idAula FROM 4522_horarioGrupo WHERE idGrupo=".$idGrupo;
					else
						$consulta="SELECT dia,horaInicio,horaFin,idAula FROM 4522_horarioGrupo WHERE idGrupo=".$idGrupo." and '".date("Y-m-d",$fechaActual)."'>=fechaInicio and '".date("Y-m-d",$fechaActual)."'<=fechaFin";
					$resH=$con->obtenerFilas($consulta);
					$listHorarioAct="<table>";
					
					while($filaHorario=mysql_fetch_row($resH))
					{
						if($filaHorario[0]!=10)
						{
							$consulta="SELECT nombreArea FROM 9309_ubicacionesFisicas WHERE idAreaFisica=".$filaHorario[3];
							$nAula=$con->obtenerValor($consulta);
							$listHorarioAct.='<tr><td><img src=\'../images/bullet_green.png\'></td><td>'.utf8_encode($arrDiasSemana[$filaHorario[0]])." ".date("H:i",strtotime($filaHorario[1]))."-".date("H:i",strtotime($filaHorario[2]))." (Aula: ".$nAula.")<br></td></tr>";
						}
					}
					$listHorarioAct.="</table>";
					$tabla='<table>
								<tr>
									<td align=\'center\' width=\'600\'>
										<span class=\'letraFichaRespuesta\'>
									Horario actual
										</span>
									</td>
									
								</tr>
								<tr>
									<td align=\'left\'>
									'.$listHorarioAct.'
									</td>
									
								</tr>
							</table>';
				}
				$descripcion.="<br><br>".$tabla;	
				$cadObj=$descripcion;
			break;
			case 3:
				$obj=$oDatos;
				$consulta="select concat(Paterno,' ',Materno,' ',Nom) as nombre from 802_identifica i where idUsuario=".$obj->idProfesorSuplencia;
				$nProfesor=$con->obtenerValor($consulta);
				$consulta="select concat(Paterno,' ',Materno,' ',Nom) as nombre from 802_identifica i where idUsuario=".$obj->idProfesorSuple;
				$nProfesorSuple=$con->obtenerValor($consulta);
				
				$consulta="SELECT m.nombreMateria,nombreGrupo FROM 4520_grupos g,4502_Materias m WHERE m.idMateria=g.idMateria AND g.idGrupos=".$idGrupo;
				$fMateria=$con->obtenerPrimeraFila($consulta);
				$consulta="SELECT fechaBaja,m.motivo,fechaRegreso FROM _447_tablaDinamica t,_448_motivoBajaGrid m WHERE id__447_tablaDinamica=".$obj->idRegistro." 
							and m.id__448_motivoBajaGrid=t.motivoBaja";
				$fRegistro=$con->obtenerPrimeraFila($consulta);
				$fechaInicioBaja=date("d/m/Y",strtotime($fRegistro[0]));
				$fechaFinSuplencia=date("d/m/Y",strtotime($fRegistro[2]));
				$descripcion="<span style='color:#900;font-weight:bold'>Suplencia</span> del profesor ".$nProfesor." del grupo <b>".$fMateria[1]."</b> de ".strtolower($dic["materia"]["s"]["el"]." ".$dic["materia"]["s"]["et"]).": <b>".$fMateria[0]."</b> por el profesor: ".$nProfesorSuple." a partir del día: ".$fechaInicioBaja." hasta el día: ".$fechaFinSuplencia;
				$consulta="SELECT MIN(fechaInicio) FROM 4522_horarioGrupo WHERE idGrupo=".$idGrupo;
				$tabla="";
				$fechaInicioH=$con->obtenerValor($consulta);
				if($fechaInicioH!="")
				{
					$fechaActual=strtotime(date("Y-m-d"));
					$fechaInicioH=strtotime($fechaInicioH);
					$consulta="";
					if($fechaInicioH>$fechaActual)
						$consulta="SELECT dia,horaInicio,horaFin,idAula FROM 4522_horarioGrupo WHERE idGrupo=".$idGrupo;
					else
						$consulta="SELECT dia,horaInicio,horaFin,idAula FROM 4522_horarioGrupo WHERE idGrupo=".$idGrupo." and '".date("Y-m-d",$fechaActual)."'>=fechaInicio and '".date("Y-m-d",$fechaActual)."'<=fechaFin";
					$resH=$con->obtenerFilas($consulta);
					$listHorarioAct="<table>";
					
					while($filaHorario=mysql_fetch_row($resH))
					{
						if($filaHorario[0]!=10)
						{
							$consulta="SELECT nombreArea FROM 9309_ubicacionesFisicas WHERE idAreaFisica=".$filaHorario[3];
							$nAula=$con->obtenerValor($consulta);
							$listHorarioAct.='<tr><td><img src=\'../images/bullet_green.png\'></td><td>'.utf8_encode($arrDiasSemana[$filaHorario[0]])." ".date("H:i",strtotime($filaHorario[1]))."-".date("H:i",strtotime($filaHorario[2]))." (Aula: ".$nAula.")<br></td></tr>";
						}
					}
					$listHorarioAct.="</table>";
					$tabla='<table>
								<tr>
									<td align=\'center\' width=\'600\'>
										<span class=\'letraFichaRespuesta\'>
									Horario actual
										</span>
									</td>
									
								</tr>
								<tr>
									<td align=\'left\'>
									'.$listHorarioAct.'
									</td>
									
								</tr>
							</table>';
				}
				$descripcion.="<br><br>".$tabla;
				$cadObj=$descripcion;
			break;
		}
	}
	$cadObj=str_replace("\r","",$cadObj);
	$cadObj=str_replace("\n","",$cadObj);
	return $cadObj;
}

function formatearSolicitudIntercambioCurso($fila)
{
	global $con;
	global $arrDiasSemana;
	$cadObj="";
	$oDatos=json_decode($fila[3]);
	if($fila[14]==2)
	{
		$motivo=$oDatos->motivo;
		$tblHorario="";
		$consulta="SELECT CONCAT('(',nombreGrupo,') ',m.nombreMateria) FROM 4520_grupos g,4502_Materias m WHERE idGrupos IN (SELECT idGrupo FROM 4548_gruposSolicitudesMovimiento WHERE idSolicitud=".$fila[0].")
					AND m.idMateria=g.idMateria";
		$lblGrupo=$con->obtenerListaValores($consulta);	
		$lblGrupo=str_replace(",","<br>",$lblGrupo);
		foreach($oDatos->arrCambios as $oGrupo)
		{
			$consulta="SELECT nombreGrupo,m.nombreMateria,g.idInstanciaPlanEstudio FROM 4520_grupos g,4502_Materias m WHERE idGrupos=".$oGrupo->idGrupo." AND m.idMateria=g.idMateria";
			$fGrupo=$con->obtenerPrimeraFila($consulta);
			$tblHorario.="<table><tr height='25' ><td colspan='3'><span class=''><br><span style='color:#900;font-weight:bold'>Cambio de fecha</span> de curso del grupo <b>".$fGrupo[0]."</b> de la materia: <b>".$fGrupo[1].
			"</b> del plan de estudios: <b>".cv(obtenerNombreInstanciaPlan($fGrupo[2]))."</b></td></tr><tr><td  valign='top' width='470'>";
			$nomProfesor="Sin profesor asignado";
			if($oGrupo->idProfesorO!=0)
			{
				$nomProfesor=obtenerNombreUsuario($oGrupo->idProfesorO);
			}
			$tblHorario.="<br>".
						"<fieldset class='frameHijo'><legend >Datos originales del grupo</legend>".
						"<table width='470'>".
						"<tr>".
						"<td>".
						"<table>".
						"<tr height='21'><td width='115'><b>Bloque:</b></td><td width='350'>".$oGrupo->noBloqueO."</td></tr>".
						"<tr height='21'><td ><b>Fechas del curso:</b></td><td >Del ".date("d/m/Y",strtotime($oGrupo->fechaInicioO))." al ".date("d/m/Y",strtotime($oGrupo->fechaFinO))."</td></tr>".
						"<tr height='21'><td ><b>Profesor:</b></td><td >".$nomProfesor."</td></tr>".
						"<tr height='21'><td ><b>Horario:</b></td><td ></td></tr>".
						"</table>";
			if(($oGrupo->arrHorarioO)>0)
			{
				$tblHorario.="<table width='355'><tr height='21'><td width='70' align='lef'><span class='corpo8_bold'>Día</span></td><td width='85' align='left'>";
				$tblHorario.="<span class='corpo8_bold'>Horario</span></td><td width='140' align='left'><span class='corpo8_bold'>Aula</span></td><td width='160' align='left'><span class='corpo8_bold'>Periodo</span></td></tr>";
				$tblHorario.="<tr height='1'><td colspan='4' style='background-color:#900'></td></tr>";
				foreach($oGrupo->arrHorarioO as $h)
				{
					$consulta="SELECT nombreArea FROM 9309_ubicacionesFisicas WHERE idAreaFisica=".$h->idAula;
					$nAula=$con->obtenerValor($consulta);
					$tblHorario.='<tr height=\'21\'><td>'.utf8_encode($arrDiasSemana[$h->dia]).'</td><td>'.date("H:i",strtotime($h->hInicio)).' - '.date("H:i",strtotime($h->hFin)).'</td><td>'.$nAula.
								'</td><td>Del '.date("d/m/Y",strtotime($h->fInicio)).' al '.date("d/m/Y",strtotime($h->fFIn)).'</td></tr>';	
				}
		
				$tblHorario.="</table>";
			}
			
			$tblHorario.="</td></tr></table><br></fieldset></td>";
			$tblHorario.="<td width='20'></td><td  valign='top'  width='470'>";
			$nomProfesor="Sin profesor asignado";
			if($oGrupo->idProfesorC!=0)
			{
				$nomProfesor=obtenerNombreUsuario($oGrupo->idProfesorC);
			}
			$tblHorario.="<br>".
						"<fieldset class='frameHijo'><legend >Datos del cambio del grupo</legend>".
						"<table width='470'>".
						"<tr>".
						"<td>".
						"<table>".
						"<tr height='21'><td width='115'><b>Bloque:</b></td><td width='350'>".$oGrupo->noBloqueC."</td></tr>".
						"<tr height='21'><td ><b>Fechas del curso:</b></td><td >Del ".date("d/m/Y",strtotime($oGrupo->fechaInicioC))." al ".date("d/m/Y",strtotime($oGrupo->fechaFinC))."</td></tr>".
						"<tr height='21'><td ><b>Profesor:</b></td><td >".$nomProfesor."</td></tr>".
						"<tr height='21'><td ><b>Horario:</b></td><td ></td></tr>".
						"</table>";
			if(($oGrupo->arrHorarioC)>0)
			{
				$tblHorario.="<table width='355'><tr height='21'><td width='70' align='lef'><span class='corpo8_bold'>Día</span></td><td width='85' align='left'>";
				$tblHorario.="<span class='corpo8_bold'>Horario</span></td><td width='140' align='left'><span class='corpo8_bold'>Aula</span></td><td width='160' align='left'><span class='corpo8_bold'>Periodo</span></td></tr>";
				$tblHorario.="<tr height='1'><td colspan='4' style='background-color:#900'></td></tr>";
				foreach($oGrupo->arrHorarioC as $h)
				{
					$consulta="SELECT nombreArea FROM 9309_ubicacionesFisicas WHERE idAreaFisica=".$h->idAula;
					$nAula=$con->obtenerValor($consulta);
					
					$tblHorario.='<tr height=\'21\'><td>'.utf8_encode($arrDiasSemana[$h->dia]).'</td><td>'.date("H:i",strtotime($h->hInicio)).' - '.date("H:i",strtotime($h->hFin)).'</td><td>'.$nAula.
								'</td><td>Del '.date("d/m/Y",strtotime($h->fInicio)).' al '.date("d/m/Y",strtotime($h->fFIn)).'</td></tr>';	
				}
				$tblHorario.='</table>';
			}
			$tblHorario.="</td></tr></table><br></fieldset></td>";
			$tblHorario.="</tr></table><br><br>";
		}
		switch($fila[2])
		{
			case 4:
			
				$cadObj="<table><tr height='21'><td width='160'><span class='letraRojaSubrayada8'><b>Grupo a afectar:</b></span></td><td valign='top' class='corpo8_bold'>".$lblGrupo."</td></tr>".
						"<tr height='21'><td width='160'><span class='letraRojaSubrayada8'><b>Descripción:</b></span></td><td valign='top'><span style='color:#900;font-weight:bold'>Cambio de horario</span> del curso, abarcando el periodo <b>del</b> ".date("d/m/Y",strtotime($oDatos->fechaAplicacion)).
						" <b>al</b> ".date("d/m/Y",strtotime($oDatos->fechaTermino)).", el curso será impartido en el siguiente horario:<br><br>".$tblHorario."</td></tr><tr height='10'><td colspan='2'></td></tr>";
				$cadObj.="<tr height='21'><td ><span class='letraRojaSubrayada8'><b>Motivo del movimiento:</b></span></td><td valign='top'>".cv($motivo)."</td></tr></table>";
			break;
			case 6:
				$compBloque="";
				if(isset($oDatos->bloque))
					$compBloque=" (Asociado al bloque ".$oDatos->bloque.")";
				$cadObj="<table><tr height='21'><td width='160'><span class='letraRojaSubrayada8'><b>Grupo a afectar:</b></span></td><td valign='top' class='corpo8_bold'>".$lblGrupo."</td></tr>".
						"<tr height='21'><td width='160'><span class='letraRojaSubrayada8'><b>Descripción:</b></span></td><td valign='top'><span style='color:#900;font-weight:bold'>Cambio de fecha</span> del curso, abarcando el periodo <b>del</b> ".date("d/m/Y",strtotime($oDatos->fechaAplicacion)).
				" <b>al</b> ".date("d/m/Y",strtotime($oDatos->fechaTermino))."".$compBloque.", el curso será impartido en el siguiente horario:<br><br>".$tblHorario."</td></tr><tr height='10'><td colspan='2'></td></tr>";
				$cadObj.="<tr height='21'><td ><span class='letraRojaSubrayada8'><b>Motivo del movimiento:</b></span></td><td valign='top'>".cv($motivo)."</td></tr></table>";
			break;
			case 7:
				
				$cadObj="<table><tr height='21'><td width='160'><span class='letraRojaSubrayada8'><b>Grupo a afectar:</b></span></td><td valign='top' class='corpo8_bold'>".$lblGrupo."</td></tr>".
							"<tr height='21'><td width='100'><span class='letraRojaSubrayada8'><b>Descripción:</b></span></td><td valign='top'>".
							$tblHorario."</td></tr><tr height='10'><td colspan='2'></td></tr>";
				$cadObj.="<tr height='21'><td ><span class='letraRojaSubrayada8'><b>Motivo del movimiento:</b></span></td><td valign='top'>".cv($motivo)."</td></tr></table>";
			break;
		}
	}
	return $cadObj;
	
}

//funcionesAutorizacionAmes
function autorizarAltaSuplencia($idMovimiento)
{
	global $con;	
	$consulta="SELECT datosSolicitud,idSolicitudAME,tipoSolicitud FROM 4548_solicitudesMovimientoGrupo WHERE idSolicitudMovimiento=".$idMovimiento;
	$fSolicitudes=$con->obtenerPrimeraFila($consulta);
	$objDatos=json_decode($fSolicitudes[0]);
	$participacion="";
	$pPrincipal="";
	$consulta="SELECT idGrupo FROM 4548_gruposSolicitudesMovimiento WHERE idSolicitud=".$idMovimiento;
	$idGrupo=$con->obtenerValor($consulta);
	switch($fSolicitudes[2])
	{
		case 1:
			$participacion=37;
			$pPrincipal=1;
		break;
		case 3:
			$participacion=45;
			$pPrincipal=0;
		break;
		
	}
	$x=0;
	$query[$x]="begin";
	$x++;
	$query[$x]="INSERT INTO 4519_asignacionProfesorGrupo(idGrupo,idUsuario,idParticipacion,esperaContrato,participacionPrincipal,situacion,fechaAsignacion,fechaBaja)
				VALUES(".$idGrupo.",".$objDatos->idProfesor.",".$participacion.",1,".$pPrincipal.",1,'".$objDatos->fechaAplicacion."','".$objDatos->fechaTermino."')";
	$x++;
	$query[$x]="set @idRegistro:=(select last_insert_id())";

	$x++;
	$query[$x]="UPDATE 4548_solicitudesMovimientoGrupo SET idAsignacion=@idRegistro,situacion=3 WHERE idSolicitudMovimiento=".$idMovimiento;
	$x++;
	
	$query[$x]="commit";
	$x++;
	
	return $con->ejecutarBloque($query);
}

function autorizarBajaFinalizacion($idMovimiento)
{
	global $con;
	$consulta="SELECT datosSolicitud,idSolicitudAME,tipoSolicitud,idAsignacion FROM 4548_solicitudesMovimientoGrupo WHERE idSolicitudMovimiento=".$idMovimiento;
	$fSolicitudes=$con->obtenerPrimeraFila($consulta);
	$objDatos=json_decode($fSolicitudes[0]);
	$consulta=" select fechaBaja from  4519_asignacionProfesorGrupo WHERE idAsignacionProfesorGrupo=".$fSolicitudes[3];
	$fechaBajaOriginal=$con->obtenerValor($consulta);
	$objDeshacer='{"fechaBajaOriginal":"'.$fechaBajaOriginal.'"}';
	$x=0;
	$query[$x]="begin";
	$x++;
	$query[$x]="UPDATE 4519_asignacionProfesorGrupo SET fechaBaja='".$objDatos->fechaBaja."' WHERE idAsignacionProfesorGrupo=".$fSolicitudes[3];
	$x++;
	$query[$x]="UPDATE 4548_solicitudesMovimientoGrupo SET datosDeshacer='".$objDeshacer."',situacion=3 WHERE idSolicitudMovimiento=".$idMovimiento;
	$x++;
	$query[$x]="commit";
	$x++;
	
	return $con->ejecutarBloque($query);
}

function autorizarCambioFechaHorario($idMovimiento)
{
	global $con;
	$objDeshacer='{';
	$x=0;
	$query[$x]="begin";
	$x++;
	$consulta="SELECT datosSolicitud,idSolicitudAME,tipoSolicitud FROM 4548_solicitudesMovimientoGrupo WHERE idSolicitudMovimiento=".$idMovimiento;
	$fSolicitudes=$con->obtenerPrimeraFila($consulta);
	$objDatos=json_decode($fSolicitudes[0]);

	$consulta="SELECT idGrupo FROM 4548_gruposSolicitudesMovimiento WHERE idSolicitud=".$idMovimiento;
	$idGrupo=$con->obtenerValor($consulta);
	$consulta="SELECT fechaInicio,fechaFin,noBloqueAsociado FROM 4520_grupos WHERE idGrupos=".$idGrupo;
	$fGrupo=$con->obtenerPrimeraFila($consulta);
	if($fGrupo[2]=="")
		$fGrupo[2]="null";
	$objDeshacer.='"fechaInicio":"'.$fGrupo[0].'","fechaFin":"'.$fGrupo[1].'","bloque":"'.$fGrupo[2].'"';
	$consulta="SELECT dia,horaInicio,horaFin,idAula,fechaInicio,fechaFin FROM 4522_horarioGrupo WHERE idGrupo=".$idGrupo." ORDER BY idHorarioGrupo";
	$resConsulta=$con->obtenerFilas($consulta);
	$cadHorarioResp="";
	while($fConsulta=mysql_fetch_row($resConsulta))
	{
		$o='{"dia":"'.$fConsulta[0].'","horaInicio":"'.$fConsulta[1].'","horaFin":"'.$fConsulta[2].'","idAula":"'.$fConsulta[3].'","fechaInicio":"'.$fConsulta[4].'","fechaFin":"'.$fConsulta[5].'"}';
		if($cadHorarioResp=="")
			$cadHorarioResp=$o;
		else
			$cadHorarioResp.=",".$o;
	}
	$objDeshacer.=',"arrHorario":['.$cadHorarioResp.']';
	$query[$x]="delete from 4522_horarioGrupo where idGrupo=".$idGrupo;
	$x++;
	$considerarBD=true;
	$reiniciarSesiones=false;
	switch($fSolicitudes[2])
	{
		case 4: //Cambio de horario
			if($objDatos->recalcularFechaTermino==1)
			{
				$query[$x]="update 4520_grupos set fechaFin='".$objDatos->fechaTermino."' where idGrupos=".$idGrupo;
				$x++;
			}
			
			if(isset($objDatos->ajustarProfesorTitular))
			{
				if(($objDatos->ajustarProfesorTitular==1)&&($objDatos->ultimoProfesorTitular!=0))
				{	
					$cadAsignacion="";
					$idAsignacion=$objDatos->ultimoProfesorTitular;
					$consulta="SELECT fechaAsignacion,fechaBaja,idAsignacionProfesorGrupo,situacion FROM 4519_asignacionProfesorGrupo WHERE idGrupo=".$idGrupo;
					$resAsig=$con->obtenerFilas($consulta);
					while($fAsig=mysql_fetch_row($resAsig))
					{
						$o='{"idAsignacion":"'.$fAsig[2].'","fechaAsignacion":"'.$fAsig[0].'","fechaBaja":"'.$fAsig[1].'","situacion":"'.$fAsig[3].'"}';
						if($cadAsignacion=="")
							$cadAsignacion=$o;
						else
							$cadAsignacion.=",".$o;
					}
					$objDeshacer.=',"arrAsignaciones":['.$cadAsignacion.']';
					if($objDatos->ultimoProfesorTitular<0)
					{
						$consulta="SELECT idAsignacion FROM 4548_solicitudesMovimientoGrupo WHERE idSolicitudMovimiento=".($objDatos->ultimoProfesorTitular*-1);
						$idAsignacion=$con->obtenerValor($consulta);
						$query[$x]="UPDATE 4519_asignacionProfesorGrupo SET fechaBaja='".$objDatos->fechaTermino."' WHERE idAsignacionProfesorGrupo=".$idAsignacion;
						$x++;
					}
					else
					{
						$query[$x]="UPDATE 4519_asignacionProfesorGrupo SET fechaBaja=DATE_SUB(fechaAsignacion,INTERVAL 1 DAY),ignoraContrato=1,situacion=4  WHERE idAsignacionProfesorGrupo=".$idAsignacion;
						$x++;
						
						$consulta="select idGrupo,idUsuario,idParticipacion,participacionPrincipal,fechaAsignacion,fechaBaja from 4519_asignacionProfesorGrupo WHERE idAsignacionProfesorGrupo=".$idAsignacion;
						$fProfesorBaja=$con->obtenerPrimeraFila($consulta);
						$query[$x]="INSERT INTO 4519_asignacionProfesorGrupo(idGrupo,idUsuario,idParticipacion,esperaContrato,participacionPrincipal,situacion,fechaAsignacion,fechaBaja,idContrato,idNominaFiniquito)
									VALUES(".$fProfesorBaja[0].",".$fProfesorBaja[1].",".$fProfesorBaja[2].",1,".$fProfesorBaja[3].",1,'".$fProfesorBaja[4]."','".$objDatos->fechaTermino."',-1,0)";
						$x++;
						$query[$x]="set @idRegistro:=(select last_insert_id())";
					
						$x++;
						$query[$x]="UPDATE 4548_solicitudesMovimientoGrupo SET idAsignacion=@idRegistro WHERE idSolicitudMovimiento=".$idMovimiento;
						$x++;
					}
				}
			}
		break;
		case 6: //Cambio fecha
			$cadAsignacion="";
			$reiniciarSesiones=true;
			$considerarBD=false;
			$consulta="SELECT fechaAsignacion,fechaBaja,idAsignacionProfesorGrupo FROM 4519_asignacionProfesorGrupo WHERE idGrupo=".$idGrupo;
			$resAsig=$con->obtenerFilas($consulta);
			while($fAsig=mysql_fetch_row($resAsig))
			{
				$o='{"idAsignacion":"'.$fAsig[2].'","fechaAsignacion":"'.$fAsig[0].'","fechaBaja":"'.$fAsig[1].'"}';
				if($cadAsignacion=="")
					$cadAsignacion=$o;
				else
					$cadAsignacion.=",".$o;
			}
			$objDeshacer.=',"arrAsignaciones":['.$cadAsignacion.']';
			$query[$x]="UPDATE 4519_asignacionProfesorGrupo SET fechaBaja=DATE_SUB(fechaAsignacion,INTERVAL 1 DAY) WHERE idGrupo=".$idGrupo;
			$x++;
			$bloque="null";
			if(isset($objDatos->bloque)&&($objDatos->bloque!=""))
				$bloque=$objDatos->bloque;
			$query[$x]="UPDATE 4520_grupos SET fechaInicio='".$objDatos->fechaAplicacion."',fechaFin='".$objDatos->fechaTermino."',noBloqueAsociado=".$bloque." WHERE idGrupos=".$idGrupo;
			$x++;
		break;
	}

	$arrFechas=obtenerFechasGrupoFinalV2($idGrupo,$idMovimiento,$considerarBD);


	$fechaAplicacion="";
	foreach($arrFechas as $h)
	{
		if($h[2]!=10)
		{
			$query[$x]="INSERT INTO 4522_horarioGrupo(idGrupo,dia,horaInicio,horaFin,idAula,fechaInicio,fechaFin)
						VALUES(".$idGrupo.",".$h[2].",'".$h[3]."','".$h[4]."',".$h[5].",'".$h[6]."','".$h[7]."')";
			$x++;
		}
		$fechaAplicacion=$h[6];
	}
	$objDeshacer.='}';
	$query[$x]="UPDATE 4548_solicitudesMovimientoGrupo SET datosDeshacer='".$objDeshacer."',situacion=3 WHERE idSolicitudMovimiento=".$idMovimiento;
	$x++;
	$query[$x]="commit";
	$x++;
	
	if($con->ejecutarBloque($query))
	{
		$x=0;
		$query=array();
		
//		$consulta="SELECT fechaInicio,fechaFin,noBloqueAsociado FROM 4520_grupos WHERE idGrupos=".$idGrupo;
//		$fGrupo=$con->obtenerPrimeraFila($consulta);
		if(ajustarFechaFinalCursoAME($idGrupo))
			return ajustarSesiones($idGrupo,$fechaAplicacion."",NULL,$query,$x,true,$reiniciarSesiones);
	}
}

function autorizarIntercambioCurso($idMovimiento)
{
	global $con;
	
	$x=0;
	$query[$x]="begin";
	$x++;
	$consulta="SELECT datosSolicitud,idSolicitudAME,tipoSolicitud FROM 4548_solicitudesMovimientoGrupo WHERE idSolicitudMovimiento=".$idMovimiento;
	$fSolicitudes=$con->obtenerPrimeraFila($consulta);
	$objDatos=json_decode($fSolicitudes[0]);
	$arrDeshacer="";
	foreach($objDatos->arrCambios as $oGrupo)
	{
		$objDeshacer='{';
		
		$consulta="SELECT fechaInicio,fechaFin,noBloqueAsociado FROM 4520_grupos WHERE idGrupos=".$oGrupo->idGrupo;
		$fGrupo=$con->obtenerPrimeraFila($consulta);
		if($fGrupo[2]=="")
			$fGrupo[2]="null";
		$objDeshacer.='"fechaInicio":"'.$fGrupo[0].'","fechaFin":"'.$fGrupo[1].'","bloque":"'.$fGrupo[2].'"';
		$consulta="SELECT dia,horaInicio,horaFin,idAula,fechaInicio,fechaFin FROM 4522_horarioGrupo WHERE idGrupo=".$oGrupo->idGrupo." ORDER BY idHorarioGrupo";
		$resConsulta=$con->obtenerFilas($consulta);
		$cadHorarioResp="";
		while($fConsulta=mysql_fetch_row($resConsulta))
		{
			$o='{"dia":"'.$fConsulta[0].'","horaInicio":"'.$fConsulta[1].'","horaFin":"'.$fConsulta[2].'","idAula":"'.$fConsulta[3].'","fechaInicio":"'.$fConsulta[4].'","fechaFin":"'.$fConsulta[5].'"}';
			if($cadHorarioResp=="")
				$cadHorarioResp=$o;
			else
				$cadHorarioResp.=",".$o;
		}
		$objDeshacer.=',"arrHorario":['.$cadHorarioResp.']';
		$cadAsignacion="";
		$considerarBD=false;
		$consulta="SELECT fechaAsignacion,fechaBaja,idAsignacionProfesorGrupo FROM 4519_asignacionProfesorGrupo WHERE idGrupo=".$oGrupo->idGrupo;
		$resAsig=$con->obtenerFilas($consulta);
		while($fAsig=mysql_fetch_row($resAsig))
		{
			$o='{"idAsignacion":"'.$fAsig[2].'","fechaAsignacion":"'.$fAsig[0].'","fechaBaja":"'.$fAsig[1].'"}';
			if($cadAsignacion=="")
				$cadAsignacion=$o;
			else
				$cadAsignacion.=",".$o;
		}
		$objDeshacer.=',"arrAsignaciones":['.$cadAsignacion.']';
		$query[$x]="UPDATE 4519_asignacionProfesorGrupo SET fechaBaja=DATE_SUB(fechaAsignacion,INTERVAL 1 DAY) WHERE idGrupo=".$oGrupo->idGrupo;
		$x++;
		
		$query[$x]="UPDATE 4520_grupos SET fechaInicio='".$oGrupo->fechaInicioC."',fechaFin='".$oGrupo->fechaFinC."',noBloqueAsociado=".$oGrupo->noBloqueC." WHERE idGrupos=".$oGrupo->idGrupo;
		$x++;
		if($oGrupo->idProfesorC!=0)
		{
			if($oGrupo->noBloqueO!=$oGrupo->noBloqueC)
			{
				$query[$x]="INSERT INTO 4519_asignacionProfesorGrupo(idGrupo,idUsuario,idParticipacion,esperaContrato,participacionPrincipal,situacion,fechaAsignacion,fechaBaja,idContrato,idNominaFiniquito)
							VALUES(".$oGrupo->idGrupo.",".$oGrupo->idProfesorC.",37,1,1,1,'".$oGrupo->fechaInicioC."','".$oGrupo->fechaFinC."',-1,0)";
				$x++;
				$objDeshacer.=',"datosAsignacion":{"nuevoRegistro":"1","idUsuario":"'.$oGrupo->idProfesorC.'","idGrupo":"'.$oGrupo->idGrupo.'","fechaAsignacion":"'.$oGrupo->fechaInicioC.'","fechaBaja":"'.$oGrupo->fechaFinC.'"}';
			}
			else
			{
				$query[$x]="UPDATE 4519_asignacionProfesorGrupo SET fechaAsignacion='".$oGrupo->fechaInicioC."',fechaBaja='".$oGrupo->fechaFinC."' WHERE idGrupo=".$oGrupo->idGrupo." and idUsuario=".$oGrupo->idProfesorC;
				$x++;
				$objDeshacer.=',"datosAsignacion":{"nuevoRegistro":"0","idUsuario":"'.$oGrupo->idProfesorO.'","idGrupo":"'.$oGrupo->idGrupo.'","fechaAsignacion":"'.$oGrupo->fechaInicioO.'","fechaBaja":"'.$oGrupo->fechaFinO.'"}';
			}
			
			
		}
		$query[$x]="delete from 4522_horarioGrupo where idGrupo=".$oGrupo->idGrupo;
		$x++;

		foreach($oGrupo->arrHorarioC as $h)
		{
			$query[$x]="INSERT INTO 4522_horarioGrupo(idGrupo,dia,horaInicio,horaFin,idAula,fechaInicio,fechaFin)
						VALUES(".$oGrupo->idGrupo.",".$h->dia.",'".$h->hInicio."','".$h->hFin."',".$h->idAula.",'".$h->fInicio."','".$h->fFIn."')";
			$x++;
			$fechaAplicacion=$h->fInicio;
		}
		$objDeshacer.='}';
		if($arrDeshacer=="")
			$arrDeshacer=$objDeshacer;
		else
			$arrDeshacer.=",".$objDeshacer;
		
	}
	$objGlobalDeshacer='{"arrDeshacer":['.$arrDeshacer.']}';
			
	$query[$x]="UPDATE 4548_solicitudesMovimientoGrupo SET datosDeshacer='".$objGlobalDeshacer."',situacion=3 WHERE idSolicitudMovimiento=".$idMovimiento;
	$x++;
	$query[$x]="commit";
	$x++;

	if($con->ejecutarBloque($query))
	{
		$x=0;
		$query=array();
		$query[$x]="begin";
		$x++;
		
		foreach($objDatos->arrCambios as $oGrupo)
		{
			$fechaAplicacion="";
			foreach($oGrupo->arrHorarioC as $h)
			{
				$fechaAplicacion=$h->fInicio;
			}
		 	ajustarSesiones($idGrupo,$fechaAplicacion."",NULL,$query,$x,false,true);
		}
		$query[$x]="commit";
		$x++;
		return $con->ejecutarBloque($query);
	}		
}

//---//
function existeAsignacionSolicitudAME($idGrupo,$idSolicitud)
{
	global $con;
	$consulta="SELECT COUNT(*) FROM 4548_solicitudesMovimientoGrupo s,4548_gruposSolicitudesMovimiento g WHERE g.idSolicitud=s.idSolicitudMovimiento AND
			g.idGrupo=".$idGrupo." AND s.idSolicitudAME=".$idSolicitud." and tipoSolicitud in(1,3)";
	$nReg=$con->obtenerValor($consulta);
	if($nReg>0)
		return true;
	return false;
}

function existeCambioFechaHorarioSolicitudAME($idGrupo,$idSolicitud)
{
	global $con;
	$consulta="SELECT COUNT(*) FROM 4548_solicitudesMovimientoGrupo s,4548_gruposSolicitudesMovimiento g WHERE g.idSolicitud=s.idSolicitudMovimiento AND
			g.idGrupo=".$idGrupo." AND s.idSolicitudAME=".$idSolicitud." and tipoSolicitud in(4,6,7)";
	$nReg=$con->obtenerValor($consulta);
	if($nReg>0)
		return true;
	return false;
}


function obtenerUsuarioAsignacionAME($idGrupo,$idSolicitud)
{
	global $con;
	$arrProfesores=array();
	$consulta="SELECT * FROM 4548_solicitudesMovimientoGrupo s,4548_gruposSolicitudesMovimiento g WHERE g.idSolicitud=s.idSolicitudMovimiento AND
			g.idGrupo=".$idGrupo." AND s.idSolicitudAME=".$idSolicitud." and tipoSolicitud in(1,3)";
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		$tipoAsignacion=37;
		if($fila[2]==3)
			$tipoAsignacion=45;
		$objDatos=json_decode($fila[3]);
		$o[0]=0;
		$o[1]=$idGrupo;
		$o[2]=0;
		$o[3]="00:00:00";
		$o[4]="00:00:00";
		$o[5]=$objDatos->idProfesor;
		$o[6]=$objDatos->fechaAplicacion;
		$o[7]=$objDatos->fechaTermino;
		$o[8]=$tipoAsignacion;
		array_push($arrProfesores,$o);
	}
	return $arrProfesores;
}

function obtenerCambioHorarioSolicitudAME($idGrupo,$idSolicitud)
{
	global $con;
	$arrCambiosHorario=array();
	$consulta="SELECT datosSolicitud FROM 4548_solicitudesMovimientoGrupo s,4548_gruposSolicitudesMovimiento g WHERE g.idSolicitud=s.idSolicitudMovimiento AND
			g.idGrupo=".$idGrupo." AND s.idSolicitudAME=".$idSolicitud." and tipoSolicitud in(4) order by idSolicitudMovimiento";
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		array_push($arrCambiosHorario,json_decode($fila[0]));
	}
	return $arrCambiosHorario;
}

function obtenerFechaBajaProfesorAME($idAsignacion,$idSolicitud)
{
	global $con;
	$consulta="SELECT datosSolicitud FROM 4548_solicitudesMovimientoGrupo WHERE idSolicitudAME=".$idSolicitud." and tipoSolicitud=2 AND idAsignacion=".$idAsignacion;
	$datos=$con->obtenerValor($consulta);
	if($datos!="")
	{
		return json_decode($datos);
	}
	else
	{
		$consulta="SELECT datosSolicitud FROM 4548_solicitudesMovimientoGrupo WHERE tipoSolicitud=2 and idAsignacion=".$idAsignacion." and situacion=3";
		$datos=$con->obtenerValor($consulta);
		return json_decode($datos);
	}
	return NULL;
}

function normalizarFechasAsignacionProfesores($arrProfesores,$idGrupo,$idSolicitud)
{
	global $con;
	$arrHorarios=obtenerCambioHorarioSolicitudAME($idGrupo,$idSolicitud);
	if(sizeof($arrHorarios)>0)
	{
		foreach($arrHorarios as $obj)
		{

			if(isset($obj->ajustarProfesorTitular))
			{
				if(($obj->ajustarProfesorTitular==1)&&($obj->ultimoProfesorTitular!=0))
				{	
						if($obj->ultimoProfesorTitular>0)
						{
							for($nPos=0;$nPos<sizeof($arrProfesores);$nPos++)
							{
								if($arrProfesores[$nPos][0]==$obj->ultimoProfesorTitular)
								{
									$arrProfesores[$nPos][7]=$obj->fechaTermino;
									break;	
								}
							}
						}
						else
						{
							for($nPos=0;$nPos<sizeof($arrProfesores);$nPos++)
							{
								if($arrProfesores[$nPos][9]==($obj->ultimoProfesorTitular*-1))
								{
									$arrProfesores[$nPos][7]=$obj->fechaTermino;
									break;	
								}
							}
						}
				}
			}
		}
	}
	return $arrProfesores;
}
?>