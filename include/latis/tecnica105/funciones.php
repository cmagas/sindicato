<?php
	function registrarAlumno($idFormulario,$idRegistro)
	{
		global $con;
		$consulta="SELECT ApPat,apMat,Nombre,planEstudio,grado,grupo,taller FROM _1047_tablaDinamica WHERE id__1047_tablaDinamica=".$idRegistro;
		$fDatosAlumno=$con->obtenerPrimeraFila($consulta);
		$idUsuario=crearBaseUsuario($fDatosAlumno[0],$fDatosAlumno[1],$fDatosAlumno[2],"","","","7");
		$x=0;
		$query[$x]="begin";
		$x++;
		
		$consulta="SELECT idCiclo,idPeriodo,idGrado FROM 4546_estructuraPeriodo WHERE idEstructuraPeriodo=".$fDatosAlumno[4];
		$fDatosGrados=$con->obtenerPrimeraFila($consulta);
		
		
		$query[$x]="DELETE FROM 4529_alumnos WHERE idUsuario=".$idUsuario;
		$x++;
		$query[$x]="DELETE FROM 4517_alumnosVsMateriaGrupo WHERE idUsuario=".$idUsuario;
		$x++;
		
		$query[$x]="INSERT INTO 4529_alumnos(idCiclo,idPeriodo,idInstanciaPlanEstudio,idGrado,idGrupo,idUsuario,estado)
					VALUES(".$fDatosGrados[0].",".$fDatosGrados[1].",".$fDatosAlumno[3].",".$fDatosGrados[2].",".$fDatosAlumno[5].",".$idUsuario.",1)";
		$x++;	
		
		$query[$x]="update _1047_tablaDinamica set idUsuario=".$idUsuario." where id__1047_tablaDinamica=".$idRegistro;
		$x++;
		$query[$x]="INSERT INTO 4517_alumnosVsMateriaGrupo(idUsuario,situacion,idGrupo,idMateria,idPlanEstudio,idCiclo,idPeriodo,idInstanciaPlan)
					SELECT ".$idUsuario.",1,idGrupos,idMateria,idPlanEstudio,idCiclo,idPeriodo,idInstanciaPlanEstudio FROM 4520_grupos WHERE (idGrupoPadre=".$fDatosAlumno[5]." OR idGrupos=".$fDatosAlumno[6].")";
		$x++;
		$query[$x]="commit";
		$x++;
		
		return $con->ejecutarBloque($query);
		
	}
	
	function modificarAlumno($idFormulario,$idRegistro)
	{
		global $con;
		$consulta="SELECT ApPat,apMat,Nombre,idUsuario,planEstudio,grado,grupo,taller FROM _1047_tablaDinamica WHERE id__1047_tablaDinamica=".$idRegistro;
		$fDatosAlumno=$con->obtenerPrimeraFila($consulta);
		$idUsuario=$fDatosAlumno[3];
		
		$x=0;
		$query[$x]="begin";
		$x++;
		
		$consulta="SELECT idCiclo,idPeriodo,idGrado FROM 4546_estructuraPeriodo WHERE idEstructuraPeriodo=".$fDatosAlumno[5];

		$fDatosGrados=$con->obtenerPrimeraFila($consulta);
		
		
		$query[$x]="DELETE FROM 4529_alumnos WHERE idUsuario=".$idUsuario;
		$x++;
		$query[$x]="DELETE FROM 4517_alumnosVsMateriaGrupo WHERE idUsuario=".$idUsuario;
		$x++;
		
		$query[$x]="UPDATE 802_identifica SET Paterno='".cv($fDatosAlumno[0])."',Materno='".cv($fDatosAlumno[1])."',Nom='".cv($fDatosAlumno[2])."' WHERE idUsuario=".$fDatosAlumno[3];
		$x++;	
		
		$query[$x]="INSERT INTO 4529_alumnos(idCiclo,idPeriodo,idInstanciaPlanEstudio,idGrado,idGrupo,idUsuario,estado)
					VALUES(".$fDatosGrados[0].",".$fDatosGrados[1].",".$fDatosAlumno[4].",".$fDatosGrados[2].",".$fDatosAlumno[6].",".$idUsuario.",1)";
		$x++;	
		
		$query[$x]="INSERT INTO 4517_alumnosVsMateriaGrupo(idUsuario,situacion,idGrupo,idMateria,idPlanEstudio,idCiclo,idPeriodo,idInstanciaPlan)
					SELECT ".$idUsuario.",1,idGrupos,idMateria,idPlanEstudio,idCiclo,idPeriodo,idInstanciaPlanEstudio FROM 4520_grupos WHERE (idGrupoPadre=".$fDatosAlumno[6]." OR idGrupos=".$fDatosAlumno[7].")";
		
		
		$x++;
		$query[$x]="commit";
		$x++;
		
		
		if($con->ejecutarBloque($query))
		{
			$x=0;
			$query=array();
			$query[$x]="begin";
			$x++;	
			$consulta="SELECT a.idGrupo,a.idMateria,a.idCiclo,a.idPeriodo,m.tipoMateria FROM 4517_alumnosVsMateriaGrupo a,4502_Materias m WHERE idUsuario=".$idUsuario." and m.idMateria=a.idMateria";
			$rMaterias=$con->obtenerFilas($consulta);	
			while($fMaterias=mysql_fetch_row($rMaterias))
			{
				if($fMaterias[4]!=2)
				{
					$query[$x]="UPDATE 3010_calificacionesListado SET idGrupo=(".$fMaterias[0]."*(idGrupo/ABS(idGrupo))) WHERE idAlumno=".$idUsuario." AND idCiclo=".$fMaterias[2]." AND idMateria=".$fMaterias[1];
					$x++;
					$query[$x]="UPDATE 3010_calificacionesListadoRecuperacion SET idGrupo=(".$fMaterias[0]."*(idGrupo/ABS(idGrupo))) WHERE idAlumno=".$idUsuario." AND idCiclo=".$fMaterias[2]." AND idMateria=".$fMaterias[1];
					$x++;
				}
				else
				{
					$consulta="SELECT idGrupo FROM 3010_calificacionesListado c,4502_Materias m WHERE idAlumno=".$idUsuario." AND idCiclo=".$fMaterias[2]." AND m.idMateria=c.idMateria AND m.tipoMateria=2";
					$iGpo=$con->obtenerValor($consulta);
					
					$query[$x]="UPDATE 3010_calificacionesListado SET idGrupo=".$fDatosAlumno[7]." WHERE idAlumno=".$idUsuario." AND idGrupo=".$iGpo;
					$x++;
					$query[$x]="UPDATE 3010_calificacionesListadoRecuperacion SET idGrupo=".$fDatosAlumno[7]." WHERE idAlumno=".$idUsuario." AND idGrupo=".$iGpo;
					$x++;
					
						
				}
				
				
				
			}
			$query[$x]="commit";
			$x++;
			return $con->ejecutarBloque($query);
			
		}
		
	}
	
	function ajustarEdad()
	{
		global $con;	
		
		$x=0;
		$query[$x]="begin";
		$x++;
		
		$fechaReferencia=strtotime(date("Y-09-01"));
		$fNacimiento="";
		$consulta="SELECT id__1047_tablaDinamica,fechaNacimiento FROM _1047_tablaDinamica";
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$fNacimiento=strtotime($fila[1]);
			$anio=date("Y",$fNacimiento);
			$mes=date("m",$fNacimiento);
			$dia=date("d",$fNacimiento);
			$edad=date("Y")-$anio;
			$nacimientoActual=strtotime(date("Y")."-".$mes."-".$dia);
			if($fechaReferencia<$nacimientoActual)
				$edad--;
				
				
			$query[$x]="UPDATE _1047_tablaDinamica SET edad=".$edad." WHERE id__1047_tablaDinamica=".$fila[0];
			$x++;
		}
		
		$query[$x]="commit";
		$x++;
		varDump($query);
		return $con->ejecutarBloque($query);
		
		
	}
?>