<?php include_once("latis/conectoresAccesoDatos/administradorConexiones.php");
		
	$conGalileo=generarInstanciaConector(9);	
	function obtenerTotalInscritosCurso($idCurso)
	{
		global $con;
		global $conGalileo;
		$lCursos=obtenerCursosMoodleLatis($idCurso);
		$consulta="SELECT COUNT(iduser) FROM inscritosxsede  curso IN (".$lCursos.")";
		return $conGalileo->obtenerValor($consulta);
	}
	
	function obtenerTotalInscritosCursoPlantel($idCurso,$cctPlantel)
	{
		global $con;
		global $conGalileo;
		$lCursos=obtenerCursosMoodleLatis($idCurso);
		$consulta="SELECT COUNT(iduser) FROM inscritosxsede i,mdl_user u WHERE u.id=i.iduser and curso IN (".$lCursos.") AND u.institution IN (".$cctPlantel.")";

		return $conGalileo->obtenerValor($consulta);
	}
	
	function obtenerTotalInscritosCursoSede($idCurso,$cctSede)
	{
		global $con;
		global $conGalileo;
		$lCursos=obtenerCursosMoodleLatis($idCurso);
		$consulta="SELECT COUNT(iduser) FROM inscritosxsede WHERE curso IN (".$lCursos.") AND cct_sede IN (".$cctSede.")";

		return $conGalileo->obtenerValor($consulta);
	}
	
	function obtenerTotalInscritosCursoSubsistema($idCurso,$idSubsistema)
	{
		global $con;
		global $conGalileo;
		$lCursos=obtenerCursosMoodleLatis($idCurso);

		$consulta="SELECT CCT FROM jos_escuela WHERE SubsistemaEMS_IdSubsistema=".$idSubsistema;

		$cctPlantel=$conGalileo->obtenerListaValores($consulta,"'");
		if($cctPlantel=="")
			$cctPlantel=-1;

		$consulta="SELECT count(iduser) FROM inscritosxsede i,mdl_user u WHERE u.id=i.iduser and curso IN (".$lCursos.") AND u.institution IN (".$cctPlantel.")";

		return $conGalileo->obtenerValor($consulta);
	}
		
	function obtenerUsuariosInscritosCurso($idCurso)
	{
		global $con;
		global $conGalileo;
		$lCursos=obtenerCursosMoodleLatis($idCurso);
		if($lCursos=="")
			$lCursos=-1;
		$consulta="SELECT distinct iduser FROM inscritosxsede  curso IN (".$lCursos.")";
		return $conGalileo->obtenerListaValores($consulta);
	}
	
	function obtenerUsuariosInscritosCursoPlantel($idCurso,$cctPlantel)
	{
		global $con;
		global $conGalileo;
		$lCursos=obtenerCursosMoodleLatis($idCurso);
		$consulta="SELECT distinct iduser FROM inscritosxsede i,mdl_user u WHERE curso IN (".$lCursos.") and u.id=i.iduser AND u.institution IN (".$cctPlantel.")";
		return $conGalileo->obtenerListaValores($consulta);
	}
	
	function obtenerUsuariosInscritosCursoSede($idCurso,$cctSede)
	{
		global $con;
		global $conGalileo;
		$lCursos=obtenerCursosMoodleLatis($idCurso);
		$consulta="SELECT distinct iduser FROM inscritosxsede WHERE cct_sede IN (".$cctSede.") and curso IN (".$lCursos.")";
		return $conGalileo->obtenerListaValores($consulta);
	}
	
	function obtenerUsuariosInscritosCursoSubsistema($idCurso,$idSubsistema)
	{
		global $con;
		global $conGalileo;
		$lCursos=obtenerCursosMoodleLatis($idCurso);

		$consulta="SELECT CCT FROM jos_escuela WHERE SubsistemaEMS_IdSubsistema=".$idSubsistema;
		$cctPlantel=$conGalileo->obtenerListaValores($consulta,"'");
		if($cctPlantel=="")
			$cctPlantel=-1;

		$consulta="SELECT distinct iduser FROM inscritosxsede i,mdl_user u WHERE u.id=i.iduser and curso IN (".$lCursos.") AND u.institution IN (".$cctPlantel.")";

		return $conGalileo->obtenerListaValores($consulta);
	}
	
	function obtenerCursosMoodleLatis($idCurso)
	{
		global $con;
		$consulta="SELECT cursoMoodle FROM _372_gridCursosMoodle g,_372_tablaDinamica t WHERE g.idReferencia=t.id__372_tablaDinamica AND t.idReferencia in (".$idCurso.")";
		$listCursos=$con->obtenerListaValores($consulta);
		return $listCursos;
		
	}
		
	function obtenerIDTareas($idCurso,$noTarea)
	{
		global $con;
		$consulta="SELECT DISTINCT tareaMoodle FROM _337_gridTareasCurso2 g,_337_tablaDinamica t WHERE g.noTarea=".$noTarea." AND g.idReferencia=t.id__337_tablaDinamica AND t.idReferencia=".$idCurso;

		$listaTareas=$con->obtenerListaValores($consulta);
		return $listaTareas;
		
	}
	
	function obtenerEstadisticasTareaUsuario($idUsuario,$idCurso,$noTarea)
	{
		global $con;
		global $conGalileo;
		$arrEstadisticas["totalEnviadas"]=0;
		$arrEstadisticas["aceptadas"]=0;
		$arrEstadisticas["rechazadas"]=0;
		$listTareas=obtenerIDTareas($idCurso,$noTarea);
		$consulta="SELECT id,grade FROM mdl_assignment_submissions WHERE userid=".$idUsuario." AND assignment IN (".$listTareas.") and grade in (1,2)";
		$res=$conGalileo->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$arrEstadisticas["totalEnviadas"]++;
			if($fila[1]==1)
				$arrEstadisticas["aceptadas"]++;
			else
				$arrEstadisticas["rechazadas"]++;
		}
		return $arrEstadisticas;
	}
	
	function obtenerEstadisticasTareaPlantel($cctPlantel,$idCurso,$noTarea)
	{
		global $con;
		global $conGalileo;
		$arrEstadisticas["totalEnviadas"]=0;
		$arrEstadisticas["aceptadas"]=0;
		$arrEstadisticas["rechazadas"]=0;
		$listTareas=obtenerIDTareas($idCurso,$noTarea);
		$listUsuarios=obtenerUsuariosInscritosCursoPlantel($idCurso,$cctPlantel);
		if($listUsuarios!="")
		{
			$arrUsuarios=explode(",",$listUsuarios);
			foreach($arrUsuarios as $idUsuario)
			{
				$consulta="SELECT id,grade FROM mdl_assignment_submissions WHERE userid=".$idUsuario." AND assignment IN (".$listTareas.") and grade in (1,2)";
	
				$res=$conGalileo->obtenerFilas($consulta);
				while($fila=mysql_fetch_row($res))
				{
					$arrEstadisticas["totalEnviadas"]++;
					if($fila[1]==1)
						$arrEstadisticas["aceptadas"]++;
					else
						$arrEstadisticas["rechazadas"]++;
				}
			}
		}
		return $arrEstadisticas;
	}
	
	function obtenerAsistenciaUsuario($idUsuario,$idCurso,$noSesion)
	{
		global $con;
		global $conGalileo;
		$consulta="SELECT COUNT(*) FROM 0_paseListaAsistencia WHERE idUsuario=".$idUsuario." AND  idCurso=".$idCurso." AND noSesion=".$noSesion;
		$nAsistencia=$con->obtenerValor($consulta);
		return $nAsistencia;
	}
	
	function obtenerTotalAsistenciaPlantel($idCurso,$noSesion,$cctPlantel)
	{
		global $con;
		global $conGalileo;

		$totalAsistencia=0;

		$listUsuarios=obtenerUsuariosInscritosCursoPlantel($idCurso,$cctPlantel);

		if($listUsuarios!="")
		{
			$arrUsuarios=explode(",",$listUsuarios);
	
			if(sizeof($arrUsuarios)>0)
			{
				foreach($arrUsuarios as $idUsuario)
				{
					$totalAsistencia+=obtenerAsistenciaUsuario($idUsuario,$idCurso,$noSesion);
				}
			}
		}
		return $totalAsistencia;
	}
	
	function obtenerTotalAsistenciaSede($idCurso,$noSesion,$cctSede)
	{
		global $con;
		global $conGalileo;
		$totalAsistencia=0;
		$listUsuarios=obtenerUsuariosInscritosCursoSede($idCurso,$cctSede);
		if($listUsuarios!="")
		{
			$arrUsuarios=explode(",",$listUsuarios);
			foreach($arrUsuarios as $idUsuario)
			{
				$totalAsistencia+=obtenerAsistenciaUsuario($idUsuario,$idCurso,$noSesion);
			}
		}
		return $totalAsistencia;
	}	
	
	function obtenerFechaSesionCursoGalileo($idCurso,$noSesion,$subsistema)
	{
		global $con;
		$consulta="SELECT id__337_tablaDinamica FROM _337_subsistemas s,_337_tablaDinamica t WHERE s.idReferencia=t.id__337_tablaDinamica AND s.subsistema='".$subsistema."' AND t.idReferencia=".$idCurso;
		$idReferencia=$con->obtenerValor($consulta);
		if($idReferencia=="")
			$idReferencia=-1;
		$consulta="SELECT fechaSesion FROM _337_gridFechas WHERE idReferencia=".$idReferencia." AND noSesion=".$noSesion;
		return $con->obtenerValor($consulta);
		
	}
	
	function obtenerTotalTareasDiplomado($idGrupo,$noSesion,$idModulo)
	{
		global $con;
		$evalMinima=11;
		if($idModulo>=4)
			$evalMinima=1;
		$conAux=generarInstanciaConector(11);
		$consulta="SELECT id FROM mdl_groups WHERE courseid=".$idModulo." and NAME LIKE '%\_".$idGrupo."\_%'";
		$listGrupos=$conAux->obtenerListaValores($consulta);
		if($listGrupos=="")
			$listGrupos="-1";
		$consulta="SELECT id FROM mdl_assignment WHERE course=".$idModulo." and name like '%Tarea ".$noSesion."%'";
		$listTareas=$conAux->obtenerListaValores($consulta);
		if($listTareas=="")
			$listTareas="-1";
		$consulta="SELECT userid FROM mdl_groups_members WHERE  groupid IN(".$listGrupos.")";
		$listUsr=$conAux->obtenerListaValores($consulta);
		if($listUsr=="")
			$listUsr="-1";
		$consulta="SELECT COUNT(*) FROM mdl_assignment_submissions WHERE userid IN(".$listUsr.")
					AND assignment IN(".$listTareas.") and grade>=".$evalMinima;
		$nTareas=$conAux->obtenerValor($consulta);
		return $nTareas;
	}
	
	function obtenerTotalTareasModuloDiplomado($idGrupo,$noModulo)
	{
		global $con;
		$evalMinima=11;
		if($noModulo>=3)
			$evalMinima=1;
		$conAux=generarInstanciaConector(11);
		$consulta="SELECT id FROM mdl_course WHERE category=1 ORDER BY sortorder LIMIT ".($noModulo-1).",1";

		$idModulo=$conAux->obtenerValor($consulta);
		$consulta="SELECT id FROM mdl_groups WHERE courseid=".$idModulo." and NAME LIKE '%\_".$idGrupo."\_%'";
		$listGrupos=$conAux->obtenerListaValores($consulta);
		if($listGrupos=="")
			$listGrupos="-1";
		$consulta="SELECT id FROM mdl_assignment WHERE course=".$idModulo;
		$listTareas=$conAux->obtenerListaValores($consulta);
		if($listTareas=="")
			$listTareas="-1";
		$consulta="SELECT userid FROM mdl_groups_members WHERE  groupid IN(".$listGrupos.")";
		$listUsr=$conAux->obtenerListaValores($consulta);
		if($listUsr=="")
			$listUsr="-1";
		$consulta="SELECT COUNT(*) FROM mdl_assignment_submissions WHERE userid IN(".$listUsr.")
					AND assignment IN(".$listTareas.") and grade>=".$evalMinima;
		$nTareas=$conAux->obtenerValor($consulta);
		return $nTareas;
	}
	
	function generarDireccionPlantel($idPlantel)
	{
		global $con;
		$consulta="SELECT calle,numero,colonia,cp,(SELECT estado FROM 820_estadosV2 WHERE cveEstado=t.estado),(SELECT municipio FROM 821_municipiosV2 WHERE cveMunicipio=t.municipio),
					(SELECT localidad FROM 822_localidadesV2 WHERE cveLocalidad=t.localidad) FROM _395_tablaDinamica t where id__395_tablaDinamica=".$idPlantel;
		$fPlantel=$con->obtenerPrimeraFila($consulta);
		$direccion=$fPlantel[0];
		if($fPlantel[1]!="")
		{
			$direccion=" No. ".$fPlantel[1];
		}
		if($fPlantel[2]!="")
		{
			$direccion.=" Colonia ".$fPlantel[2];
		}
		if($fPlantel[3]!="")
		{
			$direccion.=" C.P. ".$fPlantel[3];
		}
		
		if($direccion!="")
			$direccion.=". ";
		
		$direccion.=" Localidad: ".$fPlantel[6];
		$direccion.=", Municipio: ".$fPlantel[5];
		$direccion.=", ".$fPlantel[4];
		return $direccion;
	}
		
?>