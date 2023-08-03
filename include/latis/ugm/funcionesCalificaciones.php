<?php 

	function obtenerMaximaAsistencia($idGrupo,$tipoEvaluacion,$noEvaluacion)
	{
		global $con;
		$totalAsistencia=0;
		$fecha=date("Y-m-d");
		switch($tipoEvaluacion)
		{
			case 1:
					$consulta="SELECT fechaInicioIncidencia,fechaFinIncidencia,noSesionInicioIncidencia,noSesionFinIncidencia 
								FROM 4580_calendarioExamenesGrupo WHERE idGrupo='".$idGrupo."' AND tipoExamen='".$tipoEvaluacion."' 
								AND noExamen='".$noEvaluacion."'";
					$res=$con->obtenerPrimeraFila($consulta);
					$sesionI=$res[2];
					$sesionF=$res[3];
					$totalAsistencia=$sesionF-$sesionI;
			break;
			case 2:
			case 3:
			case 4:
			case 5:
					$cosultarSesiones="SELECT COUNT(idSesion) FROM 4530_sesiones WHERE idGrupo='".$idGrupo."' AND tipoSesion='9'";
					$totalAsistencia=$con->obtenerValor($cosultarSesiones);
			break;
		}
		return $totalAsistencia;
	}

	function obtenerNumeroAsistencia($idUsuario,$tipoEvaluacion,$idGrupo,$noEvaluacion)
	{
		global $con;
		$asistencia=0;
		
		switch($tipoEvaluacion)
		{
			case 1:
				$consulta="SELECT fechaInicioIncidencia,fechaFinIncidencia,noSesionInicioIncidencia,noSesionFinIncidencia 
							FROM 4580_calendarioExamenesGrupo WHERE idGrupo='".$idGrupo."' AND tipoExamen='".$tipoEvaluacion."' 
							AND noExamen='".$noEvaluacion."'";
				$res=$con->obtenerPrimeraFila($consulta);
				$fechaIni=$res[0];					
				$fechaFin=$res[1];
				$sesionI=$res[2];
				$sesionF=$res[3];
				
				$consulAsistencia="SELECT count(idAsistencia) FROM 4531_listaAsistencia WHERE idGrupo='".$idGrupo."' AND idAlumno='".$idUsuario."' 
									AND tipo<>'0' AND fechaSesion BETWEEN '".$fechaIni."' AND '".$fechaFin."'";
				$asistencia=$con->obtenerValor($consulAsistencia);							
			break;
			case 2:
			case 3:
			case 4:
			case 5:
					$consulta="SELECT idInstanciaPlanEstudio ,fechaInicio,fechaFin,noBloqueAsociado from 4520_grupos WHERE idGrupos=".$idGrupo;
					$fGrupo=$con->obtenerPrimeraFila($consulta);
		
					$idInstanciaPlanEstudio=$fGrupo[0];
					$fechaIni=$fGrupo[1];
					$fechaFin=$fGrupo[2];
					$consulAsistencia="SELECT count(idAsistencia) FROM 4531_listaAsistencia WHERE idGrupo='".$idGrupo."' AND idAlumno='".$idUsuario."' 
										AND tipo<>'0'";
					$asistencia=$con->obtenerValor($consulAsistencia);							
			break;
		}
		return $asistencia;
	}

	function obtenerValorPorcentajeAsistencia($idGrupo,$tipoEvaluacion,$noEvaluacion)
	{
		global $con;
		$porcentaje=0;
		$idFormulario=398;
		$porcentajeAsistencia=100;	
		$consulta="SELECT idInstanciaPlanEstudio ,idPlanEstudio,idMateria from 4520_grupos WHERE idGrupos=".$idGrupo;
		$fGrupo=$con->obtenerPrimeraFila($consulta);
		
		$idInstanciaPlanEstudio=$fGrupo[0];
			
		$fConfTipoE=obtenerConfiguracionPlanEstudio(398,"",$idInstanciaPlanEstudio);
	
		if($fConfTipoE)
		{
			$consulta="SELECT asistencia FROM _398_gridTiposExamen WHERE idReferencia=".$fConfTipoE[0]." AND tipoExamen=".$tipoEvaluacion." 
						AND noExamen=".$noEvaluacion;

			$porcentajeAsistencia=$con->obtenerValor($consulta);
			if($porcentajeAsistencia=="")
				$porcentajeAsistencia=100;
		}
		return $porcentajeAsistencia;			
	}

	function obtenerNumExamenesParciales($idGrupo)
	{
		global $con;
		$idFormulario=398;
		$numExamen=0;
		
		$consultaPlan="SELECT idPlanEstudio,idInstanciaPlanEstudio,noBloqueAsociado FROM 4520_grupos WHERE idGrupos='".$idGrupo."'";
		$grupo=$con->obtenerPrimeraFila($consultaPlan);
		$idPlanEstudio=$grupo[0];
		$idInstanciaPlanEstudio=$grupo[1];
		$bloque=$grupo[2];
		
		$consulta=obtenerConfiguracionPlanEstudio($idFormulario,$idPlanEstudio,$idInstanciaPlanEstudio);
		$referencia=$consulta[0];
		$consultaNum="SELECT COUNT(tipoExamen) FROM _398_gridTiposExamen WHERE idReferencia='".$referencia."' AND tipoEXamen='1'";
		$numExamen=$con->obtenerValor($consultaNum);
		return $numExamen;
	}

	function calcularPromedioParciales($idGrupo,$idUsuario)
	{
		//echo "Grupo ".$idGrupo," usuario ".$idUsuario." tipoEva ".$tipoEvaluacion." noEva ".$noEvaluacion."<br>";
		global $con;
		$idFormulario=398;
		$sumaCalif=0;
		$numExamen=0;	
		$consultaPlan="SELECT idPlanEstudio,idInstanciaPlanEstudio,noBloqueAsociado FROM 4520_grupos WHERE idGrupos='".$idGrupo."'";
		$grupo=$con->obtenerPrimeraFila($consultaPlan);
		$idPlanEstudio=$grupo[0];
		$idInstanciaPlanEstudio=$grupo[1];
		$bloque=$grupo[2];
		
		$consulta=obtenerConfiguracionPlanEstudio($idFormulario,$idPlanEstudio,$idInstanciaPlanEstudio);
		$referencia=$consulta[0];
		$consultaNum="SELECT COUNT(tipoExamen) FROM _398_gridTiposExamen WHERE idReferencia='".$referencia."' AND tipoEXamen='1'";
		$numExamen=$con->obtenerValor($consultaNum);
		for($x=1; $x<=$numExamen; $x++)
		{
			$calif=obtenerCalificacion($idUsuario,$idGrupo,'1',$x,$bloque);
			//echo "Calificacion ".$calif;
			if($calif!=-10)
			{
				if($calif<0)
				{
					$prom='50';
					//return $prom;
					return $prom;
				}
				else
				{
					$sumaCalif=$sumaCalif+$calif;
				}
			}
			else
			{
				return 0;
			}
		}
		//echo "suma calif ".$sumaCalif." numExamen ".$numExamen;
		$prom=$sumaCalif/$numExamen;
		return $prom;
		//echo "Promedio ". $prom."<br>";
	}

	function obtenerCalificacion($idUsuario,$idGrupo,$tipoEvaluacion,$noEvaluacion,$bloque)
	{
		global $con;
		$consulta="select valor from 4569_calificacionesEvaluacionAlumnoPerfilMateria where idAlumno='".$idUsuario."' and idGrupo='".$idGrupo."' 
					and bloque='".$bloque."' and tipoEvaluacion='".$tipoEvaluacion."' AND noEvaluacion='".$noEvaluacion."'";
		$calif=$con->obtenerValor($consulta);
		if($calif)
		{
			return $calif;
		}
		else
		{
			return '-10';
		}
	}

	function registraCalificacionPromedio($idGrupo)
	{
		global $con;
		$consulta="SELECT idUsuario FROM 4517_alumnosVsMateriaGrupo WHERE idGrupo=".$idGrupo." AND situacion=1";
		$arrAlumnos=array();
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$obj["idUsuario"]=$fila[0];
			$obj["registraCalificacion"]=0;
			$obj["calificacionFinal"]=-1;
			$obj["comentarios"]="";
			array_push($arrAlumnos,$obj);
		}
		return $arrAlumnos;		
	}
	
	function guardarCalificacionPromedio($idGrupo,$tipoEvaluacion,$noEvaluacion)
	{
		global $con;
		$idCriterio='26';
		$idTipoEvaluacionPromedio=6;
		$noEvaluacionPromedio='1';
		$codigoCriterio=obtenerCodigoCriterioEvaluacion($idGrupo,$idCriterio,$idTipoEvaluacionPromedio,$noEvaluacionPromedio);
		$consulta="SELECT DISTINCT idAlumno FROM 4568_calificacionesCriteriosAlumnoPerfilMateria WHERE idGrupo='".$idGrupo."' 
					AND tipoEvaluacion='".$tipoEvaluacion."' and noEvaluacion=".$noEvaluacion." ORDER BY idAlumno";
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$promedio=calcularPromedioParciales($idGrupo,$fila[0]);
			asentarCalificacionCriterio($idGrupo,$codigoCriterio,$idTipoEvaluacionPromedio,$noEvaluacionPromedio,$fila[0],$promedio);
		}
	}
	
	function obtenerListadoAlumnosPromedioGrupo($idGrupo)
	{
		global $con;

		$consulta="SELECT idUsuario FROM 4517_alumnosVsMateriaGrupo WHERE idGrupo=".$idGrupo." AND situacion=1";

		$arrAlumnos=array();
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$obj["idUsuario"]=$fila[0];
			$obj["registraCalificacion"]=0;
			array_push($arrAlumnos,$obj);
		}
		return $arrAlumnos;		
	}	
	
	function cerrarEvaluacionBloqueFinal($idGrupo)
	{
		$idTipoEvaluacionPromedio=6;
		$noEvaluacionPromedio='1';

		return cerrarRegistroEvaluacion($idGrupo,$idTipoEvaluacionPromedio,$noEvaluacionPromedio);
	}
	
	function inicializacionTotalInasistencia($idGrupo,$tipoEvaluacion,$noEvaluacion)
	{
		global $con;	
		$totalAsistencia=obtenerValorPorcentajeAsistencia($idGrupo,$tipoEvaluacion,$noEvaluacion);
		return "'var porcentajeInasistencia=".(100-$totalAsistencia).";'";
	}

	

?>