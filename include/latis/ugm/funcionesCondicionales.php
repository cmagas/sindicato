<?php include_once("latis/ugm/funcionesCalificaciones.php");

	function cumplePorcentajeAsistencia($idUsuario,$idGrupo,$tipoEvaluacion,$noEvaluacion)
	{
		global $con;	

		$porcentaje=0;
		$porcentajeMinimo=obtenerValorPorcentajeAsistencia($idGrupo,$tipoEvaluacion,$noEvaluacion);
		
		$maxAsistencia=obtenerMaximaAsistencia($idGrupo,$tipoEvaluacion,$noEvaluacion);
		
		$totalAsistencia=obtenerNumeroAsistencia($idUsuario,$tipoEvaluacion,$idGrupo,$noEvaluacion);
		
		if($maxAsistencia==0)
			$porcentaje=100;
		else
			$porcentaje=($totalAsistencia/$maxAsistencia)*100;
		if($porcentaje>=$porcentajeMinimo)
			return 1;
		else
			return 0;
			
	}
	
	function esPrimeraInscripcion($idUsuario,$idGrupo,$tipoEvaluacion,$noEvaluacion)
	{
		global $con;	
		
		$idMateria=-1;
		
		$consulta="SELECT idGrupoOrigen FROM 4517_alumnosVsMateriaGrupo WHERE idUsuario=".$idUsuario." AND idGrupo=".$idGrupo;
		$idGrupoBase=$con->obtenerValor($consulta);
		
		if(($idGrupoBase=="")||($idGrupoBase==-1))
			$idGrupoBase=$idGrupo;
			
			
			
		$consulta="SELECT idMateria,idInstanciaPlanEstudio FROM 4520_grupos WHERE idGrupos=".$idGrupoBase;
		$fMateria=$con->obtenerPrimeraFila($consulta);
		$idMateria=$fMateria[0];
		
		$consulta="SELECT COUNT(*) FROM 4517_alumnosVsMateriaGrupo a,4520_grupos g WHERE idUsuario=".$idUsuario." AND a.idMateria=".$idMateria." AND a.idGrupo not in(".$idGrupo.",".$idGrupoBase.") 
				AND  g.idGrupos=a.idGrupo AND a.situacion=1 and  g.idInstanciaPlanEstudio=".$fMateria[1];
		$nReg=$con->obtenerValor($consulta);
		if($nReg>0)
		{
			return 0;	
		}
		else
		{
			$consulta="SELECT COUNT(*) FROM 4517_alumnosVsMateriaGrupo a,4520_grupos g WHERE idUsuario=".$idUsuario." AND  g.idGrupos=a.idGrupoOrigen AND g.idMateria=".$idMateria." AND a.idGrupo not in(".$idGrupo.",".$idGrupoBase.") 
				 AND a.situacion=1 and g.idInstanciaPlanEstudio=".$fMateria[1];
			$nReg=$con->obtenerValor($consulta);
			if($nReg>0)
			{
				return 0;	
			}
		}
		return 1;
	}
	
	function esCalificacionCursoReprobatoria($idUsuario,$idGrupo)
	{
		global $con;
		$consulta="SELECT id__721_tablaDinamica FROM _721_tablaDinamica WHERE cmbCalificacionFinal=1";
		$listEvaluaciones=$con->obtenerListaValores($consulta);
		if($listEvaluaciones=="")
			$listEvaluaciones=-1;
	
		$consulta="SELECT tipoExamen FROM 4593_situacionEvaluacionCurso WHERE idGrupo=".$idGrupo." AND tipoExamen IN (".$listEvaluaciones.") ORDER BY idSituacionAplicacionEvaluacion DESC";
		$tEvaluacion=$con->obtenerValor($consulta);
		if($tEvaluacion=="")
			return 0;
		$consulta="SELECT aprobado FROM 4569_calificacionesEvaluacionAlumnoPerfilMateria WHERE idAlumno=".$idUsuario." AND idGrupo=".$idGrupo." AND tipoEvaluacion=".$tEvaluacion;
		$aprobado=$con->obtenerValor($consulta);
		if($aprobado==0)
		{
			return 1;	
		}
		return 0;
			
	}
	
	function esAlumnoSinAdeudoVencidosConcepto($idUsuario,$idInstanciaPlanEstudio)
	{
		global $con;
		$consulta="	SELECT c.idConcepto FROM 561_conceptosIngreso c,564_conceptosVSCategorias cat
					WHERE cat.idConcepto=c.idConcepto AND idCategoria=19";
		$listaConceptos=$con->obtenerListaValores($consulta);
		if($listaConceptos=="")
			$listaConceptos=-1;
		$consulta="SELECT idMovimiento FROM 6011_movimientosPago WHERE idUsuario=".$idUsuario." AND idConcepto IN (".$listaConceptos.") AND fechaVencimiento<='".date("Y-m-d")."' AND situacion=1 and pagado=0";
		$listaMovimientos=$con->obtenerListaValores($consulta);
		if($listaMovimientos=="")
			return 1;
		
		
		$consulta="SELECT COUNT(*) FROM 6012_detalleAsientoPago WHERE idAsientoPago in (".$listaMovimientos.") AND idDimension=7 AND valorCampo=".$idInstanciaPlanEstudio;
		$nReg=$con->obtenerValor($consulta);
		if($nReg==0)
			return 1;
		
		return 0;
		
		
			
	}
?>