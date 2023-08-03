<?php  session_start();
include("latis/conexionBD.php"); 



function clonarMapaCurricular($idMapaO,$cicloN)
{
	global $con;
	$consulta="select ciclo,idPrograma from 4029_mapaCurricular where idMapaCurricular=".$idMapaO;
	$filaO=$con->obtenerPrimeraFila($consulta);
	
	$consulta="select idMapaCurricular from 4029_mapaCurricular where idPrograma=".$filaO[1]." and ciclo=".$cicloN;
	$filaMapa=$con->obtenerPrimeraFila($consulta);
	if($filaMapa)
	{
		return "-1"; // exise un mapa para el programa y ciclo especificado
	}
	
	$consulta="INSERT INTO 4029_mapaCurricular (ciclo,idPrograma,estadoMapa,idTipoHorario,esquemaGrupos,tipoGrupos,idPerfilParticipacion,
				idParticipacionPrincipal,idParticipacionInvitado,obligarBloques,noBloques,creditosHoraPractica,creditosHoraTeorica,
				esquemaEvaluacion,diasPeriodo,idParticipacionCoordinador) 
				SELECT  " .$cicloN. ",idPrograma,estadoMapa,idTipoHorario,esquemaGrupos,tipoGrupos,idPerfilParticipacion,
				idParticipacionPrincipal,idParticipacionInvitado,obligarBloques,noBloques,creditosHoraPractica,
				creditosHoraTeorica,esquemaEvaluacion,diasPeriodo,idParticipacionCoordinador FROM 4029_mapaCurricular 
				WHERE  idMapaCurricular=" .$idMapaO;

	if($con->ejecutarConsulta($consulta))
	{
		$idMapaN=$con->obtenerUltimoID();
		return $idMapaN;
	}
	else
		return "-10";
}

function clonarHabilidades($idMapaO,$idMapaD)
{
	global $con;
	$consulta="INSERT INTO 4006_habilidades(titulo,descripcion,fechaCreacion,responsable,fechaModif,respModif,idMapaCurricular) 
				SELECT titulo,descripcion,'".date('Y-m-d')."',".$_SESSION["idUsr"].",NULL,NULL,".$idMapaD." from 4006_habilidades WHERE idMapaCurricular=".$idMapaO;

	if($con->ejecutarConsulta($consulta))
		return true;
	else
		return false;
}

function clonarCompetencia($idMapaO,$idMapaD)
{
	global $con;
	$consulta="INSERT INTO 4007_competencias (titulo,descripcion,fechaCreacion,responsable,fechaModif,respModif,idMapaCurricular)
				select titulo,descripcion,'".date('Y-m-d')."',".$_SESSION["idUsr"].",NULL,NULL,".$idMapaD." from 4007_competencias where idMapaCurricular=".$idMapaO;
				
	if($con->ejecutarConsulta($consulta))
		return true;
	else
		return false;
}

function clonarActitudes($idMapaO,$idMapaD)
{
	global $con;
	$consulta="INSERT INTO 4008_actitudes (titulo,descripcion,fechaCreacion,responsable,fechaModif,respModif,idMapaCurricular)
				select titulo,descripcion,'".date('Y-m-d')."',".$_SESSION["idUsr"].",NULL,NULL,".$idMapaD." from 4008_actitudes where idMapaCurricular=".$idMapaO;
				
	if($con->ejecutarConsulta($consulta))
		return true;
	else
		return false;
}

function clonarEvaluacion($idMapaO,$idMapaD)
{
	global $con;
	$consulta="INSERT INTO 4010_evaluaciones(titulo,descripcion,fechaCreacion,responsable,fechaModif,respModif,idTipoEvaluacion,idMapaCurricular,idEscalaCalificacion)
				select titulo,descripcion,'".date('Y-m-d')."',".$_SESSION["idUsr"].",NULL,NULL,idTipoEvaluacion,".$idMapaD.",idEscalaCalificacion from 4010_evaluaciones where idMapaCurricular=".$idMapaO;
				
	if($con->ejecutarConsulta($consulta))
		return true;
	else
		return false;
}

function clonarTecnicasColaborativas($idMapaO,$idMapaD)
{
	global $con;
	$consulta="INSERT INTO 4011_tecnicasColaborativas (titulo,descripcion,fechaCreacion,responsable,fechaModif,respModif,idMapaCurricular)
				select titulo,descripcion,'".date('Y-m-d')."',".$_SESSION["idUsr"].",NULL,NULL,".$idMapaD." from 4011_tecnicasColaborativas where idMapaCurricular=".$idMapaO;
				
	if($con->ejecutarConsulta($consulta))
		return true;
	else
		return false;
}

function clonarProductos($idMapaO,$idMapaD)
{
	global $con;
	$consulta="INSERT INTO 4012_productos (titulo,descripcion,fechaCreacion,responsable,fechaModif,respModif,idMapaCurricular)
				select titulo,descripcion,'".date('Y-m-d')."',".$_SESSION["idUsr"].",NULL,NULL,".$idMapaD." from 4012_productos where idMapaCurricular=".$idMapaO;
				
	if($con->ejecutarConsulta($consulta))
		return true;
	else
		return false;
}

function clonarMateria($idMateriaO,$cicloN)
{
	global $con;
	$consulta="INSERT INTO 4013_materia (idPrograma,cve_materia,titulo,objetivo,objetivos_especificos,proposito,fechaCreacion,responsable,status,keywords,fechaModif,respModif,bloques,ciclo,abreviatura,horasSemana,horasTotal,descripcion,horasTeoricas,horasPracticas,tipoTemario,esquemaEvaluacion,compartida,fechaInicio,fechaFin,nCreditos,fechaInicioInsc,fechaFinInsc)
				select  idPrograma,cve_materia,titulo,objetivo,objetivos_especificos,proposito,'".date('Y-m-d')."',".$_SESSION["idUsr"].",status,keywords,null,null,bloques,".$cicloN.",abreviatura,horasSemana,horasTotal,descripcion,horasTeoricas,horasPracticas,tipoTemario,esquemaEvaluacion,compartida,fechaInicio,fechaFin,nCreditos,fechaInicioInsc,fechaFinInsc
				from 4013_materia where idMateria=".$idMateriaO;
				
	if($con->ejecutarConsulta($consulta))
		return true;
	else
		return false;
}

function clonarGrado($idGradoO,$cicloN)
{
	global $con;
	$consulta="INSERT INTO 4014_grados (idPrograma,descripcion,grado,leyenda,fechaCreacion,responsable,fechaModif,respModif,idGradoAnt,ciclo,idGradoSig,esquemaEvaluacion,materiasLibres,porcentajeLibres,materiasOptativasC,porcentajeOptativaC,noMateriaOpC,ponderacion,fechaInicio,fechaFin,fechaInicioInsc,fechaFinInsc)
				select  idPrograma,descripcion,grado,leyenda,'".date('Y-m-d')."',".$_SESSION["idUsr"].",null,null,idGradoAnt,".$cicloN.",idGradoSig,esquemaEvaluacion,materiasLibres,porcentajeLibres,materiasOptativasC,porcentajeOptativaC,noMateriaOpC,ponderacion,fechaInicio,fechaFin,fechaInicioInsc,fechaFinInsc
				from 4014_grados where idGrado=".$idGradoO;
				
	if($con->ejecutarConsulta($consulta))
		return true;
	else
		return false;
}

function clonarElementosMapa($idElementoMapaO,$idMapaCurricularN,$idGradoN,$idMateriaN)
{
	global $con;
	$consulta="INSERT INTO 4031_elementosMapa (idMapaCurricular,idGrado,idPadre,idMateria,idTipoMateria,idTipoBoleta,promedia,idIdioma,idTipoComponente,idEscalaCalificacionOf,idEscalaCalificacionNof,idNivel,ponderacion,noSemanas,noMaterias,idTipoHorario,perteneceMapa,criterioEvaluacion,ponderaHijos,esquemaEvaluacion,materiasLibres,porcentajeLibres,materiasOptativasC,porcentajeOptativaC,fechaInicioInsc,fechaFinInsc,sumaPorcentaje)
				select ".$idMapaCurricularN.",".$idGradoN.",idPadre,".$idMateriaN.",idTipoMateria,idTipoBoleta,promedia,idIdioma,idTipoComponente,idEscalaCalificacionOf,idEscalaCalificacionNof,idNivel,ponderacion,noSemanas,noMaterias,idTipoHorario,perteneceMapa,criterioEvaluacion,ponderaHijos,esquemaEvaluacion,materiasLibres,porcentajeLibres,materiasOptativasC,porcentajeOptativaC,fechaInicioInsc,fechaFinInsc,sumaPorcentaje
				from 4031_elementosMapa where idElementoMapa=".$idElementoMapaO;
				
	if($con->ejecutarConsulta($consulta))
		return true;
	else
		return false;
}

function clonarMateriaVsCompetencia($idMatComO,$idMateriaN,$idCometenciaN)
{
	global $con;
	$consulta="INSERT INTO 4041_materiaVsCompetencias (idMateria,idCompetencia,criterioEval)
				select ".$idMateriaN.",".$idCometenciaN.",criterioEval
				from 4041_materiaVsCompetencias where idMateriaVsCompetencias=".$idMatComO;
				
	if($con->ejecutarConsulta($consulta))
		return true;
	else
		return false;
}

function clonarMateriaVsHabilidades($idMatHabO,$idMateriaN,$idHabilidadN)
{
	global $con;
	$consulta="INSERT INTO 4042_materiaVsHabilidades(idMateria,idHabilidad,criterioEval)
				select ".$idMateriaN.",".$idHabilidadN.",criterioEval
				from 4042_materiaVsHabilidades where idMateriaVsHabilidad=".$idMatHabO;
				
	if($con->ejecutarConsulta($consulta))
		return true;
	else
		return false;
}

function clonarMateriaVsActitudes($idMatActO,$idMateriaN,$idActitudN)
{
	global $con;
	$consulta="INSERT INTO 4043_materiaVsActitudes (idMateria,idActitud,criterioEval)
				select ".$idMateriaN.",".$idActitudN.",criterioEval
				from 4043_materiaVsActitudes where idMateriaVsActitud=".$idMatActO;
	if($con->ejecutarConsulta($consulta))
		return true;
	else
		return false;
}

function clonarMateriaVsEvaluaciones($idMatEvaO,$idMateriaN,$idEvaluacionN)
{
	global $con;
	$consulta="INSERT INTO 4044_materiaVsEvaluaciones(idMateria,idEvaluacion)
				select ".$idMateriaN.",".$idEvaluacionN."
				from 4044_materiaVsEvaluaciones where idMateriaVsEvaluacion=".$idMatEvaO;
	if($con->ejecutarConsulta($consulta))
		return true;
	else
		return false;
}

function clonarMateriaVsRecursos($idMatRecO,$idMateriaN,$idGrupoN)
{
	global $con;
	$consulta="INSERT INTO 4045_materiaVsRecursos (idMateria,idRecurso,idGrupo,idTema,noSesion,fecha,criterioEval)
				select ".$idMateriaN.",idRecurso,".$idGrupoN.",idTema,noSesion,fecha,criterioEval
				from  4045_materiaVsRecursos where idMateriaVsRecurso=".$idMatRecO;
	if($con->ejecutarConsulta($consulta))
		return true;
	else
		return false;
}

function clonarMateriaVsTecnicas($idMatTecO,$idMateria,$idTecnicaN,$idGrupoN)
{
	global $con;
	$consulta="INSERT INTO 4046_materiaVsTecnicas(idMateria,idTecnicaC,idGrupo,idTema,noSesion,fecha,criterioEval)
				select ".$idMateria.",".$idTecnicaN.",".$idGrupoN.",idTema,noSesion,fecha,criterioEval
				from  4046_materiaVsTecnicas where idMateriaVsTecnica=".$idMatTecO;
	if($con->ejecutarConsulta($consulta))
		return true;
	else
		return false;
}

function clonarParticipacionMateria($idPartiMatO,$idMateriaN,$idGrupoN)
{
	global $con;
	$consulta="INSERT INTO 4047_participacionesMateria(idUsuario,idMateria,idGrupo,idParticipacion,participacionP,estado)
				select ".$_SESSION["idUsr"].",".$idMateriaN.",".$idGrupoN.",idParticipacion,participacionP,estado
				from  4047_participacionesMateria where idParticipante=".$idPartiMatO;
	if($con->ejecutarConsulta($consulta))
		return true;
	else
		return false;
}

function clonarGrupos($idGrupoO,$cicloN,$idGradoN,$idMateriaN)
{
	global $con;
	$consulta="INSERT INTO 4048_grupos (ciclo,idPrograma,idGrado,nombreGrupo,cupoMinimo,cupoMaximo,idSituacion,idMateria)
				select ".$cicloN.",idPrograma,".$idGradoN.",nombreGrupo,cupoMinimo,cupoMaximo,idSituacion,".$idMateriaN."
				from  4048_grupos where idGrupo=".$idGrupoO;
	if($con->ejecutarConsulta($consulta))
		return true;
	else
		return false;
}

function clonarMateriaProfesorGrupo($idmatProfGrupoO,$idGrupoN,$idMateriaN)
{
	global $con;
	$consulta="INSERT INTO 4049_materiaVSProfesorVSGrupo(idUsuario,idGrupo,estado,motivoCambio,horaInicio,horaFin,dia,idMateria,idParticipacion,ciclo,idPrograma)
				select ".$_SESSION["idUsr"].",".$idGrupoN.",estado,motivoCambio,horaInicio,horaFin,dia,".$idMateriaN.",idParticipacion,ciclo,idPrograma
				from  4049_materiaVSProfesorVSGrupo where idMateriaVSProfesorVSGrupo=".$idmatProfGrupoO;
	if($con->ejecutarConsulta($consulta))
		return true;
	else
		return false;
}

function clonarMaterialesVsTema($idMatTema,$idMateriaN,$idGrupoN)
{
	global $con;
	$consulta="INSERT INTO 4051_materialesVSTema (idTema,idMaterial,idMateria,idGrupo,noSesion,fecha)
				select idTema,idMaterial,".$idMateriaN.",".$idGrupoN.",noSesion,fecha
				from  4051_materialesVSTema where idMaterialVSTema=".$idMatTema;
	if($con->ejecutarConsulta($consulta))
		return true;
	else
		return false;
}

function clonarSesiones($idSesionesO,$idMateriaN,$idGrupoN)
{
	global $con;
	$consulta="INSERT INTO 4053_sesiones (tipo,idMateria,fecha,noSesion,idGrupo,horaInicio,horaFin,tipoSesion,estado,bloque)
				select tipo,".$idMateriaN.",fecha,noSesion,".$idGrupoN.",horaInicio,horaFin,tipoSesion,estado,bloque
				from  4053_sesiones where idSesion=".$idSesionesO;
	if($con->ejecutarConsulta($consulta))
		return true;
	else
		return false;
}

function clonarSesionesVsTemario($idSesionTemO,$idMateriaN,$idGrupoN)
{
	global $con;
	$consulta="INSERT INTO 4054_sesionesVSTemario (idTema,noSesion,idMateria,idGrupo,fecha)
				select idTema,noSesion,".$idMateriaN.",".$idGrupoN.",fecha
				from  4054_sesionesVSTemario where idSesionesVSTemario=".$idSesionTemO;
	if($con->ejecutarConsulta($consulta))
		return true;
	else
		return false;
}

function clonarPerfilHorarioVsGrados($idPerfilhoraO,$idPerfilN,$idGradoN,$idCicloN)
{
	global $con;
	$consulta="INSERT INTO 4063_PerfilHorarioVSGrados (idPerfil,idGrado,ciclo,idPrograma)
				select ".$idPerfilN.",".$idGradoN.",".$idCicloN.",idPrograma
				from  4063_PerfilHorarioVSGrados where idPerfilVsGrado=".$idPerfilhoraO;
	if($con->ejecutarConsulta($consulta))
		return true;
	else
		return false;
}

function clonarAsignacionSeccionBloque($idAsignaBloqueO,$idGradoN,$cicloN)
{
	global $con;
	$consulta="INSERT INTO 4064_asignacionSeccionesBloques (idPrograma,idGrado,ciclo,idBloque)
				select idPrograma,".$idGradoN.",".$cicloN.",idBloque
				from  4064_asignacionSeccionesBloques where idBloqueSeccion=".$idAsignaBloqueO;
	if($con->ejecutarConsulta($consulta))
		return true;
	else
		return false;
}

function clonarMateriaVsGrupo($idMatGrupoO,$idMateriaN,$idGrupoN,$cicloN)
{
	global $con;
	$consulta="INSERT INTO 4065_materiaVSGrupo (idBloque,idMateria,idGrupo,horaInicio,horaFin,idPrograma,ciclo,tipoMateriaVirtual,idGrupoCompartido)
				select idBloque,".$idMateriaN.",".$idGrupoN.",horaInicio,horaFin,idPrograma,".$cicloN.",tipoMateriaVirtual,idGrupoCompartido
				from  4065_materiaVSGrupo where idMateriaVSGrupo=".$idMatGrupoO;
	if($con->ejecutarConsulta($consulta))
		return true;
	else
		return false;
}

function clonarFechaCalendario($idFechaCalendarioO,$idMapaCurricularN,$idGradoN)
{
	global $con;
	$consulta="INSERT INTO 4068_fechaCalendario (fechaInicio,color,idEtiqueta,idMapaCurricular,fechaFin,idGrado,etiqueta)
				select fechaInicio,color,idEtiqueta,".$idMapaCurricularN.",fechaFin,".$idGradoN.",etiqueta
				from  4068_fechaCalendario where idFechaCalendario=".$idFechaCalendarioO;
	if($con->ejecutarConsulta($consulta))
		return true;
	else
		return false;

}



?>