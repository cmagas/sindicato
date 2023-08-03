<?php 






function dispararOrdenesServicio($idSemana,$idMaster)
{
	global $con;
	
	
	$consulta="SELECT * FROM 5001_situacionProgramacionSemanal WHERE idSemana=".$idSemana." AND idMaster=".$idMaster;
	$fSituacionSemanal=$con->obtenerPrimeraFilaAsoc($consulta);
	$consulta="SELECT idRegistro FROM 5000_programacionEventos WHERE idSemana=".$idSemana." AND idMaster=".$idMaster." AND tipoEmision in(3,4)";
	
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		generarOrdenServicio($fila[0],$fSituacionSemanal["version"],$idSemana,$idMaster);
	}
	
	$consulta="SELECT id__602_tablaDinamica FROM _602_tablaDinamica WHERE idSemana=".$idSemana." AND idMaster=".$idMaster." AND idOrdenServicioPadre IS NULL 
				AND idProgramacion NOT IN(
				SELECT idRegistro FROM 5000_programacionEventos WHERE idSemana=".$idSemana." AND idMaster=".$idMaster.")";
	
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		cancelarOTBase($fila[0],"Se ha removido el evento de la programación semanal",12);
	}
		
	
}

function generarOrdenServicio($iProgramacion,$versionLiberacion,$idSemana,$idMaster)
{
	global $con;
	$fechaAplicacion=date("Y-m-d");
	$arrValores=array();
	$arrDocumentosReferencia=NULL;
	$arrCancelados[10]=1;
	$arrCancelados[11]=1;
	$arrCancelados[12]=1;
	$arrCancelados[40]=1;
	
	$consulta="SELECT * FROM _602_tablaDinamica WHERE idProgramacion=".$iProgramacion;
	$fOrdenServicio=$con->obtenerPrimeraFila($consulta);
	
	$versionOrden=1;
	
	
	$consulta="SELECT idPrograma,horaInicio,horaFin,detalleEvento,comentariosAdicionales,tipoEmision,idSemana,idMaster,cveTransmision,porConfirmar 
			FROM 5000_programacionEventos WHERE idRegistro=".$iProgramacion;
	$fProgramacionEvento=$con->obtenerPrimeraFila($consulta);
	$llaveServicio=bE($fProgramacionEvento[0]."_@_".$fProgramacionEvento[1]."_@_".$fProgramacionEvento[2]."_@_".$fProgramacionEvento[3].
					"_@_".$fProgramacionEvento[4]."_@_".$fProgramacionEvento[5]."_@_".$fProgramacionEvento[8]."_@_".$fProgramacionEvento[9]);
	
	$consulta="SELECT llaveServicio,versionOrden,codigo,id__602_tablaDinamica,idEstado FROM _602_tablaDinamica WHERE idProgramacion=".$iProgramacion.
			" and idOrdenServicioPadre is null ORDER BY fechaCreacion DESC";
	$fFilaOrdenTrabajoAnterior=$con->obtenerPrimeraFilaAsoc($consulta);
	
	if($fFilaOrdenTrabajoAnterior)
	{
		if(($llaveServicio==$fFilaOrdenTrabajoAnterior["llaveServicio"]) &&(!isset($arrCancelados[$fFilaOrdenTrabajoAnterior["idEstado"]])))
		{
			$versionOrden=0;
		}
		else
		{
			$versionOrden=$fFilaOrdenTrabajoAnterior["versionOrden"]+1;
		}
	}
	if($versionOrden==0)
		return;
	
	$folioBase="";
	if($fFilaOrdenTrabajoAnterior)
	{
		$arrFolioAnt=explode("-",$fFilaOrdenTrabajoAnterior["codigo"]);
		$folioBase=$arrFolioAnt[0];
		
		
		
		if(!cancelarOTBase($fFilaOrdenTrabajoAnterior["id__602_tablaDinamica"],"Se ha generado una nueva versión del mismo",11))
		{
			return;
		}
	}
	
	$arrValores["idSemana"]=$idSemana;
	$arrValores["idMaster"]=$idMaster;
	$arrValores["folioBase"]=$folioBase;
	$arrValores["llaveServicio"]=$llaveServicio;
	$arrValores["versionOrden"]=$versionOrden;
	$arrValores["versionLiberacion"]=$versionLiberacion;
	$arrValores["idProgramacion"]=$iProgramacion;
	$arrValores["tipoOrden"]=1;
	$arrValores["detallesAdicionales"]=$fProgramacionEvento[3];
	$arrValores["nombrePrograma"]=$fProgramacionEvento[0];
	$arrValores["comentariosAdicionales"]=$fProgramacionEvento[4];

	$idRegistroSolicitud=crearInstanciaRegistroFormulario(602,-1,2,$arrValores,$arrDocumentosReferencia,-1,1057);			
	if($fProgramacionEvento[5]==4)
	{
		$arrValoresProduccion=array();
		$arrValoresProduccion["fechaProduccion"]=date("Y-m-d",strtotime($fProgramacionEvento[1]));
		$arrValoresProduccion["horaProduccion"]=date("H:i",strtotime($fProgramacionEvento[1]));
		
		$minutos=obtenerDiferenciaHoraEnMinutos($fProgramacionEvento[1],$fProgramacionEvento[2]);
		$horasEstimadasProduccion=parteEntera($minutos/60);
		$minutosEstimadosProduccion=$minutos-($horasEstimadasProduccion*60);
		$arrValoresProduccion["horasEstimadasProduccion"]=$horasEstimadasProduccion;
		
		$arrValoresProduccion["minutosEstimadosProduccion"]=$minutosEstimadosProduccion;
		$arrValoresProduccion["horaEstimadaTermino"]=date("Y-m-d H:i:s",strtotime($fProgramacionEvento[2]));
		$arrValoresProduccion["procesoProduccion"]=$fProgramacionEvento[5]==4?1:2;

		$idRegistroPlaneacion=crearInstanciaRegistroFormulario(628,$idRegistroSolicitud,0,$arrValoresProduccion,$arrDocumentosReferencia,-1,0);		
	}
	
	$idPrograma=$fProgramacionEvento[0];
	$consulta="SELECT idActividad FROM _589_tablaDinamica WHERE id__589_tablaDinamica=".$idPrograma;
	$idActividad=$con->obtenerValor($consulta);
	$consulta="SELECT idRegistro FROM _589_perfilHorarioTransmision WHERE idActividad=".$idActividad.
				" AND fechaAplicacion<='".$fechaAplicacion."' ORDER BY fechaAplicacion DESC";
	$idPerfilHorario=$con->obtenerValor($consulta);
	$fechaInicio=strtotime($fProgramacionEvento[1]);
	$dia=date("w",$fechaInicio);
	$horaInicio=date("H:i:s",$fechaInicio);
	if($idPerfilHorario=="")
		$idPerfilHorario=-1;
	
	$consulta="SELECT idRegistro FROM _589_horariosTransmision WHERE iPerfilHorario=".$idPerfilHorario.
			" AND dia=".$dia." and horaInicio='".$horaInicio."'";

	
	$idHorarioAplica=$con->obtenerValor($consulta);		
	if($idHorarioAplica=="")
	{
		$consulta="SELECT idRegistro FROM _589_horariosTransmision WHERE iPerfilHorario=".$idPerfilHorario.
			" AND dia=8";

		$idHorarioAplica=$con->obtenerValor($consulta);		
		if($idHorarioAplica=="")
		{
			$idHorarioAplica=-1;
		}
	}
	
	$consulta="SELECT p.idPerfil FROM _589_horarioAplicaPerfilProduccion hA,_589_perfilesRecursosProduccion p 
				WHERE idHorarioAplica=".$idHorarioAplica."  AND p.idPerfil=hA.idPerfil ORDER BY fechaCreacion DESC";
	$idPerfilRecursos=$con->obtenerValor($consulta);		
	if(($idPerfilRecursos=="")&&($dia==8))
	{
		$idPerfilRecursos=-1;
	}
	else
	{
		$dia=8;
		$consulta="SELECT idRegistro FROM _589_horariosTransmision WHERE iPerfilHorario=".$idPerfilHorario.
				" AND dia=".$dia;
		$idHorarioAplica=$con->obtenerValor($consulta);		
		if($idHorarioAplica=="")
			$idHorarioAplica=-1;
		$consulta="SELECT p.idPerfil FROM _589_horarioAplicaPerfilProduccion hA,_589_perfilesRecursosProduccion p 
				WHERE idHorarioAplica=".$idHorarioAplica."  AND p.idPerfil=hA.idPerfil ORDER BY fechaCreacion DESC";
	
		$idPerfilRecursos=$con->obtenerValor($consulta);		
		if($idPerfilRecursos=="")
		{
			$idPerfilRecursos=-1;
		}
	}
	
	
	$hInicioProduccion="NULL";
	$hFinProduccion="NULL";
	$consulta="SELECT fechaProduccion,horaProduccion,horaEstimadaTermino FROM _628_tablaDinamica WHERE idReferencia=".$idRegistroSolicitud;
	$fHoraProduccion=$con->obtenerPrimeraFila($consulta);
	if($fHoraProduccion)
	{
		$hInicioProduccion="'".$fHoraProduccion[0]." ".$fHoraProduccion[1]."'";
		$hFinProduccion="'".$fHoraProduccion[2]."'";
	}
	
	$consulta="SELECT tipoRecurso,idRecurso,datosComplementarios FROM _589_recursosProduccion WHERE idPerfil=" .$idPerfilRecursos;
	
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		$horaInicio=$fProgramacionEvento[1];
		$horaTermino=$fProgramacionEvento[2];
		$idAsignacion=-1;
		$idSituacionAutorizacionRecurso=0;
		if($fila[0]==2)
		{

			$oConf=json_decode($fila[2]);	
			$horaInicio=date("Y-m-d H:i:s",strtotime("-".$oConf->tiempoRequerido." minutes",strtotime($horaInicio)));
			switch($oConf->tipoAsignacion)
			{
				case 1:
				break;
				case 2:
					/*if(($oConf->operadorTitular!="-1")&&($oConf->operadorTitular!="0"))
					{
						$objParametros=json_decode('{"idPuesto":"'.$fila[1].'","iProgramacion":"'.$iProgramacion.'","horaInicio":"'.$horaInicio.'","horaTermino":"'.$horaTermino.'"}');
						$cache=NULL;
						$idAsignacion=removerComillasLimite(resolverExpresionCalculoPHP($oConf->operadorTitular,$objParametros,$cache));
						if($idAsignacion=="")
							$idAsignacion=0;

						
					}*/
				break;
				case 3:
					$idAsignacion=$oConf->operadorTitular;
					$idSituacionAutorizacionRecurso=1;
				break;
			}
			
			
		}
		else
		{
			$oConf=json_decode($fila[2]);	
			$horaInicio=date("Y-m-d H:i:s",strtotime("-".$oConf->tiempoRequerido." minutes",strtotime($horaInicio)));
			switch($oConf->tipoAsignacion)
			{
				case 1:
				break;
				case 2:
					/*if(($oConf->operadorTitular!="-1")&&($oConf->operadorTitular!="0"))
					{
						$objParametros=json_decode('{"tipoRecurso":"'.$fila[1].'","iProgramacion":"'.$iProgramacion.'","horaInicio":"'.$horaInicio.'","horaTermino":"'.$horaTermino.'"}');
						$cache=NULL;
						$idAsignacion=removerComillasLimite(resolverExpresionCalculoPHP($oConf->operadorTitular,$objParametros,$cache));
						if($idAsignacion=="")
							$idAsignacion=0;

						
					}*/
				break;
				case 3:
					$idAsignacion=$oConf->operadorTitular;
					$idSituacionAutorizacionRecurso=1;
				break;
			}
			
			/*$consulta="SELECT metodoApartado FROM _591_tablaDinamica WHERE id__591_tablaDinamica=".$fila[1];
			$metodoApartado=$con->obtenerValor($consulta);
			if($metodoApartado==2)
			{
				$idAsignacion=1;
			}*/
		}
		$consulta="INSERT INTO _602_recursosOrdenServicios(idFormulario,idReferencia,tipoRecurso,idRecurso,datosComplementarios,horarioInicio,horaTermino,idAsignacion,idSituacionAutorizacionRecurso)
					values(602,".$idRegistroSolicitud.",".$fila[0].",".$fila[1].",'".cv($fila[2])."',".$hInicioProduccion.",".$hFinProduccion.",".$idAsignacion.",".$idSituacionAutorizacionRecurso.")";
					
		$con->ejecutarConsulta($consulta);	
		$idRecurso=$con->obtenerUltimoID();		
		
		if($fila[0]==1)
		{
			registrarContenidosRecursoMaterial($idRecurso,602,$idRegistroSolicitud,$idAsignacion);
		}
	}
}

function registrarContenidosRecursoMaterial($idRegistroPadre,$idFormulario,$idRegistro,$idRecurso)
{
	global $con;	
	
	
	$hInicioProduccion="NULL";
	$hFinProduccion="NULL";
	$consulta="SELECT fechaProduccion,horaProduccion,horaEstimadaTermino FROM _628_tablaDinamica WHERE idReferencia=".$idRegistro;
	$fHoraProduccion=$con->obtenerPrimeraFila($consulta);
	if($fHoraProduccion)
	{
		$hInicioProduccion="'".$fHoraProduccion[0]." ".$fHoraProduccion[1]."'";
		$hFinProduccion="'".$fHoraProduccion[2]."'";
	}
	
	$consulta="SELECT idProgramacion FROM _602_tablaDinamica WHERE id__602_tablaDinamica=".$idRegistro;

	$iProgramacion=$con->obtenerValor($consulta);
	if($iProgramacion=="N/E")
		$iProgramacion=-1;
	
	$consulta="SELECT idPrograma,horaInicio,horaFin FROM 5000_programacionEventos WHERE idRegistro=".$iProgramacion;
	$fProgramacionEvento=$con->obtenerPrimeraFila($consulta);
	
	$consulta="SELECT g.id__591_gridOperadores,p.clavePuesto,p.nombrePuesto,
				g.tipoAsignacion,g.operadorTitular,g.tiempoRequerido,g.puestoOperador FROM 
				_591_gridOperadores g,_590_tablaDinamica p WHERE g.idReferencia=".$idRecurso." and
				p.id__590_tablaDinamica=g.puestoOperador order by p.nombrePuesto";
	
	$res=$con->obtenerFilas($consulta);
	while($fRecurso=mysql_fetch_row($res))
	{
		$oDatosComplementarios='{"tipoAsignacion":"'.$fRecurso[3].'","operadorTitular":"'.$fRecurso[4].'","tiempoRequerido":"'.$fRecurso[5].'"}';
		//$horaInicio=$fProgramacionEvento[1];
		//$horaTermino=$fProgramacionEvento[2];
		$idAsignacion=-1;
		$idSituacionAutorizacionRecurso=0;
		$oConf=json_decode($oDatosComplementarios);	
		//$horaInicio=date("Y-m-d H:i:s",strtotime("-".$oConf->tiempoRequerido." minutes",strtotime($horaInicio)));
		switch($oConf->tipoAsignacion)
		{
			case 1:
			break;
			case 2:
				/*if(($oConf->operadorTitular!="-1")&&($oConf->operadorTitular!="0"))
				{
					$objParametros=json_decode('{"idPuesto":"'.$fRecurso[6].'","iProgramacion":"'.$iProgramacion.
								'","horaInicio":"'.$horaInicio.'","horaTermino":"'.$horaTermino.'"}');
					$cache=NULL;
					$idAsignacion=removerComillasLimite(resolverExpresionCalculoPHP($oConf->operadorTitular,$objParametros,$cache));
					if($idAsignacion=="")
						$idAsignacion=0;

					
				}*/
			break;
			case 3:
				$idAsignacion=$oConf->operadorTitular;
				$idSituacionAutorizacionRecurso=1;
			break;
		}
			
		$consulta="INSERT INTO _602_recursosOrdenServicios(idFormulario,idReferencia,tipoRecurso,idRecurso,datosComplementarios,
					horarioInicio,horaTermino,idAsignacion,idPadre,idSituacionAutorizacionRecurso)
					VALUES(".$idFormulario.",".$idRegistro.",2,".$fRecurso[6].",'".cv($oDatosComplementarios)."',".$hInicioProduccion.",".$hFinProduccion.",".$idAsignacion.
					",".$idRegistroPadre.",".$idSituacionAutorizacionRecurso.")";
		$con->ejecutarConsulta($consulta);		
	}
	
	$consulta="SELECT rec.id__591_tablaDinamica,claveRecurso,nombreRecurso,metodoApartado,categoriaRecuso  FROM _591_recursosContenidos r,_591_tablaDinamica rec 
			WHERE iRegistro=".$idRecurso." 	AND rec.id__591_tablaDinamica=r.idRecurso ORDER BY rec.nombreRecurso";
	
	$res=$con->obtenerFilas($consulta);
	while($fRecurso=mysql_fetch_row($res))
	{
		
		$consulta="INSERT INTO _602_recursosOrdenServicios(idFormulario,idReferencia,tipoRecurso,idRecurso,datosComplementarios,horarioInicio,horaTermino,idAsignacion,idPadre,idSituacionAutorizacionRecurso)
					values(".$idFormulario.",".$idRegistro.",1,".$fRecurso[4].",'',".$hInicioProduccion.",".$hFinProduccion.",".$fRecurso[0].",".$idRegistroPadre.",1)";
					
		$con->ejecutarConsulta($consulta);	
		$idRecursoAux=$con->obtenerUltimoID();		
		registrarContenidosRecursoMaterial($idRecursoAux,$idFormulario,$idRegistro,$fRecurso[0]);
	}
	
}


function obtenerResponsableAreaAtencion($idFormulario,$idRegistro,$actorDestinatario)
{
	global $con;
	$arrDestinatario=array();
	$rolActor=obtenerTituloRol($actorDestinatario);
	$nombreUsuario=obtenerNombreUsuario(1)." (".$rolActor.")";
	$o='{"idUsuarioDestinatario":"1","nombreUsuarioDestinatario":"'.$nombreUsuario.'"}';
	$o=json_decode($o);
	array_push($arrDestinatario,$o);
	
	return $arrDestinatario;
	
	
}

function obtenerConflictosRecurso($tipoRecurso,$idRecurso,$idOrdenServicio,$idRecursoRegistro,$minInicioProduccion,$minFinProduccion)
{
	global $con;
	global $arrDiasSemana;
	$campo="idAsignacion";
	$arrRegistros="";
	
	$consulta="SELECT DATE_ADD(horarioInicio, INTERVAL ".$minInicioProduccion." MINUTE) as horarioInicio,
				DATE_ADD(horaTermino, INTERVAL ".$minFinProduccion." MINUTE) as horaTermino,
				idRecursoOrigen FROM _602_recursosOrdenServicios WHERE idRegistro=".$idRecursoRegistro.
				" and horarioInicio is not null AND idSituacionAutorizacionRecurso<>4";
	
	$fDatosBase=$con->obtenerPrimeraFila($consulta);
	if(!$fDatosBase)
		return "";
	
	
	$qAux=generarConsultaIntervalos($fDatosBase[0],$fDatosBase[1],"horarioInicio","horaTermino",false);
	$consulta.=" and ".$qAux;
	$listaIgnorar=$idRecursoRegistro;
	if($fDatosBase[2]!=-1)
	{
		$listaIgnorar.=",".$fDatosBase[2];
	}
	
	$arrRegistrosEventos=array();
	
	$consulta="SELECT * FROM _602_recursosOrdenServicios rO WHERE tipoRecurso=".$tipoRecurso." AND ".$campo."=".$idRecurso.
			" and idFormulario=602 AND idRegistro not in(".$listaIgnorar.") and idRecursoOrigen<>".$idRecursoRegistro." and horarioInicio is not null 
			and  idSituacionAutorizacionRecurso<>4 and ".$qAux." ORDER BY horarioInicio";
	
	$res=$con->obtenerFilas($consulta);
	while($fRecurso=mysql_fetch_assoc($res))
	{
		
		if(isset($arrRegistrosEventos[$fRecurso["idRecursoOrigen"]]))
		{
			continue;
		}
		
		$arrRegistrosEventos[$fRecurso["idRegistro"]]=1;
		
		$fHorarioProgramas[0]=$fRecurso["horarioInicio"];
		$fHorarioProgramas[1]=$fRecurso["horaTermino"];
		
		$nombrePrograma="";
		
		$consulta="SELECT * FROM _602_tablaDinamica WHERE id__602_tablaDinamica=".$fRecurso["idReferencia"];
		$fOrdenBase=$con->obtenerPrimeraFilaAsoc($consulta);
		if(($fOrdenBase["nombrePrograma"]!="")&&($fOrdenBase["nombrePrograma"]!="-1"))
		{
			$consulta="SELECT clavePrograma,nombrePrograma FROM _589_tablaDinamica WHERE id__589_tablaDinamica=".$fOrdenBase["nombrePrograma"];
			$fPrograma=$con->obtenerPrimeraFila($consulta);
			$nombrePrograma="[".$fPrograma[0]."] ".$fPrograma[1];
		}
		else
		{
			$nombrePrograma=$fOrdenBase["descripcionOrden"];	
		}
		$lblDetalle="Conflicto con: <b>".$nombrePrograma."</b>.- ".utf8_encode($arrDiasSemana[date("w",strtotime($fHorarioProgramas[0]))])." ".date("d/m/Y",strtotime($fHorarioProgramas[0]))." de las ".date("H:i",strtotime($fHorarioProgramas[0]))." hrs. a las ".date("H:i",strtotime($fHorarioProgramas[1]))." hrs.";
	
		
		$o="['".$fRecurso["idFormulario"]."','".$fRecurso["idReferencia"]."','".cv($lblDetalle)."']";
		if($arrRegistros=="")
			$arrRegistros=$o;
		else
			$arrRegistros.=",".$o;
	}
	
	
	if($tipoRecurso==2)
	{
		$consulta="SELECT '605' AS idFormulario,id__605_tablaDinamica AS idRegistro,fechaInicio,horaInicial,fechaTermino,horaFinal,
					descripcionIncidencia FROM _605_tablaDinamica WHERE  idReferencia=".$idRecurso." AND idEstado=2";
	}
	else
	{
		$consulta="SELECT '606' AS idFormulario,id__606_tablaDinamica AS idRegistro,fechaInicio,horaInicial,fechaTermino,horaFinal,
				descripcionIncidencia FROM _606_tablaDinamica WHERE  recurso=".$idRecurso." AND idEstado=2";
	}
	$qAux=generarConsultaIntervalos(date("Y-m-d",strtotime($fDatosBase[0])),date("Y-m-d",strtotime($fDatosBase[1])),"fechaInicio","fechaTermino",false);
	
	$consulta.=" and ".$qAux;
	
	
	
	$res=$con->obtenerFilas($consulta);
	while($fRecurso=mysql_fetch_assoc($res))
	{
		if(colisionaTiempo($fDatosBase[0],$fDatosBase[1],$fRecurso["fechaInicio"]." ".$fRecurso["horaInicial"],$fRecurso["fechaTermino"]." ".$fRecurso["horaFinal"],false))
		{
			$leyenda="El recurso presenta una incidencia, motivo: ".$fRecurso["descripcionIncidencia"];
			$o="['".$fRecurso["idFormulario"]."','".$fRecurso["idRegistro"]."','".cv($leyenda)."']";
			if($arrRegistros=="")
				$arrRegistros=$o;
			else
				$arrRegistros.=",".$o;
		}
	}
		
	return $arrRegistros;
}




function ejecutarRegistroProvedor($idRegistro)
{
	global $con;
	$consulta="SELECT proveedor FROM 625_vistaProveedores WHERE id__625_tablaDinamica=".$idRegistro;
	$lblProveedor=$con->obtenerValor($consulta);
	
	echo 'window.parent.parent.asignarProveedorAgregado('.$idRegistro.',"'.cv($lblProveedor).'");return;';
}


function registrarRecursosOT($idFormulario,$idRegistro)
{
	global $con;
	
	$fechaAplicacion=date("Y-m-d");
	$consulta="delete from _602_recursosOrdenServicios where idFormulario=".$idFormulario." and idReferencia=".$idRegistro;
	$con->ejecutarConsulta($consulta);

	$consulta="SELECT nombrePrograma,idProgramacion FROM _602_tablaDinamica WHERE id__602_tablaDinamica=".$idRegistro;
	$fOT=$con->obtenerPrimeraFila($consulta);
	$idProgramacion=$fOT[1];
	if(($idProgramacion=="") || ($idProgramacion=="N/E"))
	{
		$idProgramacion=-1;
	}
	
	$idPrograma=$fOT[0];
	if(($idPrograma=="")||($idPrograma=="N/E"))
		$idPrograma=-1;
		
	$consulta="SELECT idActividad FROM _589_tablaDinamica WHERE id__589_tablaDinamica=".$idPrograma;
	$idActividad=$con->obtenerValor($consulta);
	
	$consulta="SELECT idRegistro FROM _589_perfilHorarioTransmision WHERE idActividad=".$idActividad.
				" AND fechaAplicacion<='".$fechaAplicacion."' ORDER BY fechaAplicacion DESC";

	$idPerfilHorario=$con->obtenerValor($consulta);
	
	
	$consulta="SELECT horaInicio FROM 5000_programacionEventos WHERE idRegistro=".$idProgramacion;
	$hInicio=$con->obtenerValor($consulta);
	$dteHoraInicio=strtotime($hInicio);
	$dia=date("w",$dteHoraInicio);

	$consulta="SELECT idRegistro FROM _589_horariosTransmision WHERE iPerfilHorario=".$idPerfilHorario.
			" AND dia=".$dia." and horaInicio='".date("H:i:s",$dteHoraInicio)."'";
	$idHorarioAplica=$con->obtenerValor($consulta);
	if($idHorarioAplica=="")
	{
		$dia=8;
		$consulta="SELECT idRegistro FROM _589_horariosTransmision WHERE iPerfilHorario=".$idPerfilHorario.
				" AND dia=".$dia;
		$idHorarioAplica=$con->obtenerValor($consulta);		
		if($idHorarioAplica=="")
			$idHorarioAplica=-1;
	}

	$consulta="SELECT p.idPerfil FROM _589_horarioAplicaPerfilProduccion hA,_589_perfilesRecursosProduccion p WHERE idHorarioAplica=".$idHorarioAplica."
			 AND p.idPerfil=hA.idPerfil ORDER BY fechaCreacion DESC";
	
	$idPerfilRecursos=$con->obtenerValor($consulta);		
	if(($idPerfilRecursos=="")&&($dia==8))
	{
		$idPerfilRecursos=-1;
	}
	else
	{
		$dia=8;
		$consulta="SELECT idRegistro FROM _589_horariosTransmision WHERE iPerfilHorario=".$idPerfilHorario.
				" AND dia=".$dia;
		$idHorarioAplica=$con->obtenerValor($consulta);		
		if($idHorarioAplica=="")
			$idHorarioAplica=-1;
		$consulta="SELECT p.idPerfil FROM _589_horarioAplicaPerfilProduccion hA,_589_perfilesRecursosProduccion p 
				WHERE idHorarioAplica=".$idHorarioAplica."
			 AND p.idPerfil=hA.idPerfil ORDER BY fechaCreacion DESC";
	
		$idPerfilRecursos=$con->obtenerValor($consulta);		
		if($idPerfilRecursos=="")
		{
			$idPerfilRecursos=-1;
		}

	}
		
	
	
	$consulta="SELECT tipoRecurso,idRecurso,datosComplementarios FROM _589_recursosProduccion WHERE idPerfil=" .$idPerfilRecursos;
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		$idSituacionAutorizacionRecurso=0;
		$idAsignacion=-1;
		if($fila[0]==2)
		{

			$oConf=json_decode($fila[2]);	
			//$horaInicio=date("Y-m-d H:i:s",strtotime("-".$oConf->tiempoRequerido." minutes",strtotime($horaInicio)));
			switch($oConf->tipoAsignacion)
			{
				case 1:
				break;
				case 2:
					/*if(($oConf->operadorTitular!="-1")&&($oConf->operadorTitular!="0"))
					{
						$objParametros=json_decode('{"idPuesto":"'.$fila[1].'","iProgramacion":"'.$iProgramacion.'","horaInicio":"'.$horaInicio.'","horaTermino":"'.$horaTermino.'"}');
						$cache=NULL;
						$idAsignacion=removerComillasLimite(resolverExpresionCalculoPHP($oConf->operadorTitular,$objParametros,$cache));
						if($idAsignacion=="")
							$idAsignacion=0;

						
					}*/
				break;
				case 3:
					$idAsignacion=$oConf->operadorTitular;
					$idSituacionAutorizacionRecurso=1;
				break;
			}
			
			
		}
		else
		{
			$oConf=json_decode($fila[2]);	
			//$horaInicio=date("Y-m-d H:i:s",strtotime("-".$oConf->tiempoRequerido." minutes",strtotime($horaInicio)));
			switch($oConf->tipoAsignacion)
			{
				case 1:
				break;
				case 2:
					/*if(($oConf->operadorTitular!="-1")&&($oConf->operadorTitular!="0"))
					{
						$objParametros=json_decode('{"tipoRecurso":"'.$fila[1].'","iProgramacion":"'.$iProgramacion.'","horaInicio":"'.$horaInicio.'","horaTermino":"'.$horaTermino.'"}');
						$cache=NULL;
						$idAsignacion=removerComillasLimite(resolverExpresionCalculoPHP($oConf->operadorTitular,$objParametros,$cache));
						if($idAsignacion=="")
							$idAsignacion=0;

						
					}*/
				break;
				case 3:
					$idAsignacion=$oConf->operadorTitular;
					$idSituacionAutorizacionRecurso=1;
				break;
			}
			
			/*$consulta="SELECT metodoApartado FROM _591_tablaDinamica WHERE id__591_tablaDinamica=".$fila[1];
			$metodoApartado=$con->obtenerValor($consulta);
			if($metodoApartado==2)
			{
				$idAsignacion=1;
			}*/
		}
		$consulta="INSERT INTO _602_recursosOrdenServicios(idFormulario,idReferencia,tipoRecurso,idRecurso,datosComplementarios,horarioInicio,horaTermino,idAsignacion,idSituacionAutorizacionRecurso)
					values(602,".$idRegistro.",".$fila[0].",".$fila[1].",'".cv($fila[2])."',NULL,NULL,".$idAsignacion.",".$idSituacionAutorizacionRecurso.")";
		
		$con->ejecutarConsulta($consulta);	
		$idRecurso=$con->obtenerUltimoID();		
		
		if($fila[0]==1)
		{
			registrarContenidosRecursoMaterial($idRecurso,602,$idRegistro,$idAsignacion);
		}
	}
	
}


function obtenerDiferenciaHoraEnMinutos($horaInicial,$horaFinal)
{
	$hFinal=strtotime($horaFinal);
	$hInicial=strtotime($horaInicial);
	$diferencia=($hFinal)-$hInicial;
	return ($diferencia/60);
}


function generarSubOrdenesServicio($idFormulario,$idRegistro)
{
	global $con;
	
	$arrAreasInvolucradas=array();
	
	$consulta="SELECT * FROM _602_tablaDinamica WHERE id__602_tablaDinamica=".$idRegistro;
	$fAsignacionRecursos=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$consulta="SELECT * FROM _602_recursosOrdenServicios WHERE idFormulario=".$idFormulario." AND idReferencia=".$idRegistro;
	$res=$con->obtenerFilas($consulta);
	while($fRegistro=mysql_fetch_assoc($res))
	{
		$areaResponsable="";
		if($fRegistro["tipoRecurso"]==1)
		{
			if(($fRegistro["idAsignacion"]==0)||($fRegistro["idAsignacion"]==-1))
			{
				$consulta="SELECT areaResponsable FROM _585_tablaDinamica WHERE id__585_tablaDinamica=".$fRegistro["idRecurso"];
				
				$areaResponsable=$con->obtenerValor($consulta);
			}
			else
			{
				$consulta="SELECT areaResponsable FROM _591_tablaDinamica WHERE id__591_tablaDinamica=".$fRegistro["idAsignacion"];
				$areaResponsable=$con->obtenerValor($consulta);
			}
		}
		
		else
		{
			$consulta="SELECT r.areaResponsable FROM _590_tablaDinamica p,_590_gAreaResponsable r WHERE id__590_tablaDinamica=".$fRegistro["idRecurso"]."
						and r.idReferencia=p.id__590_tablaDinamica";
			$areaResponsable=$con->obtenerListaValores($consulta);
		}
		
		
		$arrAreaResponsable=explode(",",$areaResponsable);
		foreach($arrAreaResponsable as $areaResponsable)
		{
			if(!isset($arrAreasInvolucradas[$areaResponsable]))
			{
				$arrAreasInvolucradas[$areaResponsable]=array();
			}
			
			
			array_push($arrAreasInvolucradas[$areaResponsable],$fRegistro);
		}
	}	
	
	foreach($arrAreasInvolucradas as $area=>$resto)
	{
		$areaNOAsignada=false;
		
		$consulta="SELECT COUNT(*) FROM _602_tablaDinamica WHERE folioBase='".$fAsignacionRecursos["codigo"].
					"' AND codigoInstitucion='".$area."'";
		$numReg=$con->obtenerValor($consulta);
		
		$areaAsignada=$numReg>0;
		
		if(($area!="")&&(!$areaAsignada))
		{
			$arrValores=array();
			$arrDocumentosReferencia=NULL;
			$arrValores["codigoInstitucion"]=$area;
			$arrValores["idProgramacion"]=$fAsignacionRecursos["idProgramacion"];
			$arrValores["tipoOrden"]=2;
			$arrValores["detallesAdicionales"]=$fAsignacionRecursos["detallesAdicionales"];
			$arrValores["nombrePrograma"]=$fAsignacionRecursos["nombrePrograma"];
			$arrValores["comentariosAdicionales"]=$fAsignacionRecursos["comentariosAdicionales"];
			$arrValores["descripcionOrden"]=$fAsignacionRecursos["descripcionOrden"];
			$arrValores["idOrdenServicioPadre"]=$idRegistro;
			$arrValores["folioBase"]=$fAsignacionRecursos["codigo"];
			$arrValores["idSemana"]=$fAsignacionRecursos["idSemana"];
			$arrValores["idMaster"]=$fAsignacionRecursos["idMaster"];
			
			$idRegistroSolicitud=crearInstanciaRegistroFormulario(602,-1,20,$arrValores,$arrDocumentosReferencia,-1,1057);
			
			
			$consulta="SELECT * FROM _628_tablaDinamica WHERE idReferencia=".$idRegistro;
			$fProduccion=$con->obtenerPrimeraFilaAsoc($consulta);
			
			$arrDatosproduccion=array();
			$arrDatosproduccion["fechaProduccion"]=$fProduccion["fechaProduccion"];
			$arrDatosproduccion["horaProduccion"]=$fProduccion["horaProduccion"];
			$arrDatosproduccion["horasEstimadasProduccion"]=$fProduccion["horasEstimadasProduccion"];
			$arrDatosproduccion["minutosEstimadosProduccion"]=$fProduccion["minutosEstimadosProduccion"];
			$arrDatosproduccion["horaEstimadaTermino"]=$fProduccion["horaEstimadaTermino"];
			$arrDatosproduccion["comentariosAdicionales"]=$fProduccion["comentariosAdicionales"];
			$arrDatosproduccion["procesoProduccion"]=$fProduccion["procesoProduccion"];
			crearInstanciaRegistroFormulario(628,$idRegistroSolicitud,0,$arrDatosproduccion,$arrDocumentosReferencia,-1,0);
			
			
			
			$query=array();
			$x=0;
			$query[$x]="begin";
			$x++;
			foreach($resto as $recurso)
			{
				$idSituacionAutorizacionRecurso=0;
				$idAsignacion=$recurso["idAsignacion"];
				if((($idAsignacion==0)||($idAsignacion==-1))&&($recurso["datosComplementarios"]!=""))
				{
					$oConf=json_decode($recurso["datosComplementarios"]);	
					if($oConf->tipoAsignacion==2)
					{
						$objParametros=json_decode('{"tipoRecurso":"'.$recurso["tipoRecurso"].
												'","idOrdenServicio":"'.$idRegistroSolicitud.'","horaInicio":"'.$recurso["horarioInicio"].
												'","horaTermino":"'.$recurso["horaTermino"].'","idRegistroBase":"'.$recurso["idRegistro"].'"}');
						$cache=NULL;
						$idAsignacion=removerComillasLimite(resolverExpresionCalculoPHP($oConf->operadorTitular,$objParametros,$cache));
					}
					
					if($idAsignacion=="")
						$idAsignacion=-1;
				}
				
				if(($idAsignacion!=-1)&&($idAsignacion!=0))
					$idSituacionAutorizacionRecurso=1;
					
				$query[$x]="INSERT INTO _602_recursosOrdenServicios(idFormulario,idReferencia,tipoRecurso,idRecurso,datosComplementarios,
							horarioInicio,horaTermino,idAsignacion,idPadre,comentariosAdicionales,idSituacionAutorizacionRecurso,
							ignorarRecurso,idRecursoOrigen)
							values(".$idFormulario.",".$idRegistroSolicitud.",".$recurso["tipoRecurso"].",".$recurso["idRecurso"].
							",'".cv($recurso["datosComplementarios"])."','".$recurso["horarioInicio"]."','".$recurso["horaTermino"].
							"',".$idAsignacion.",-1,'".$recurso["comentariosAdicionales"]."',".$idSituacionAutorizacionRecurso.",0,".$recurso["idRegistro"].
							")";
				$x++;
				
			}
			
			$query[$x]="commit";
			$x++;
		
			$con->ejecutarBloque($query);
		}
	}
	
	
	
	
}


function asignarFechaProduccion($idFormulario,$idRegistro)
{
	global $con;
	$consulta="SELECT  * FROM _628_tablaDinamica WHERE id__628_tablaDinamica=".$idRegistro;
	$fRegistro=$con->obtenerPrimeraFilaAsoc($consulta);
	$consulta="SELECT * FROM _602_recursosOrdenServicios WHERE idFormulario= 602 AND idReferencia=".$fRegistro["idReferencia"];
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		$horarioInicio=$fRegistro["fechaProduccion"]." ".$fRegistro["horaProduccion"];
		$horaTermino=$fRegistro["horaEstimadaTermino"];
		if($fila[5]!="")
		{
			$obj=json_decode($fila[5]);
			$horarioInicio=date("Y-m-d H:i:s",strtotime("-".$obj->tiempoRequerido." minutes",strtotime($horarioInicio)));
			
		}
		$consulta="UPDATE _602_recursosOrdenServicios SET horarioInicio='".$horarioInicio."',horaTermino='".$horaTermino."' WHERE idRegistro=".$fila[0];
		$con->ejecutarConsulta($consulta);
	}
	
	
	
	
}


function obtenerTitularAreaAsignacionRecursos($idFormulario,$idRegistro,$actorDestinatario)
{
	global $con;
	global $tipoMateria;
	$carpetaAdministrativa="";
	$nombreTablaBase="_".$idFormulario."_tablaDinamica";
	
	$continuar=true;
	$unidadGestion="";

	$consulta="SELECT codigoInstitucion FROM ".$nombreTablaBase." WHERE id__602_tablaDinamica=".$idRegistro;
	$areaResponsable=$con->obtenerValor($consulta);
	
	
	$rolActor=obtenerTituloRol($actorDestinatario);
	
	$arrDestinatario=array();
	$consulta="SELECT responsableAutorizacion,responsableAusencia FROM _600_tablaDinamica WHERE nombreArea='".$areaResponsable."'";
	
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		$nombreUsuario=obtenerNombreUsuario($fila[0])." (".$rolActor.")";
		$o='{"idUsuarioDestinatario":"'.$fila[0].'","nombreUsuarioDestinatario":"'.$nombreUsuario.'"}';
		$o=json_decode($o);
		array_push($arrDestinatario,$o);
		if($fila[1]!=-1)
		{
			$nombreUsuario=obtenerNombreUsuario($fila[1])." (".$rolActor.")";
			$o='{"idUsuarioDestinatario":"'.$fila[1].'","nombreUsuarioDestinatario":"'.$nombreUsuario.'"}';
			$o=json_decode($o);
			array_push($arrDestinatario,$o);
		}
	}
	
	
	return $arrDestinatario;
}

function esActorCoordinador($actor)
{
	global $con;
	$consuta="SELECT actor FROM 944_actoresProcesoEtapa WHERE idActorProcesoEtapa=".$actor;

	$actor=$con->obtenerValor($consuta);
	
	$arrActoresPermitidos["185_0"]=1;
	$arrActoresPermitidos["184_0"]=1;
	$arrActoresPermitidos["200_0"]=1;
	if(isset($arrActoresPermitidos[$actor]))
		return 1;
	return 0;
	
}

function esActorResponsableAsignacionRecurso($actor)
{
	global $con;
	$consuta="SELECT actor FROM 944_actoresProcesoEtapa WHERE idActorProcesoEtapa=".$actor;
	$actor=$con->obtenerValor($consuta);
	$arrActoresPermitidos["186_0"]=1;
	$arrActoresPermitidos["200_0"]=1;

	if(isset($arrActoresPermitidos[$actor]))
		return 1;
	return 0;
	
}

function enviarNotificacionConfirmacionRecurso($idFormulario,$idRegistro)
{
	global $con;
	$consulta="SELECT idOrdenServicioPadre,codigo,nombrePrograma FROM _602_tablaDinamica WHERE id__602_tablaDinamica=".$idRegistro;
	$fRegistroBase=$con->obtenerPrimeraFila($consulta);
	
	$idOrdenServicioPadre=$fRegistroBase[0];
	
	
	$consulta="SELECT COUNT(*) FROM _602_tablaDinamica WHERE idOrdenServicioPadre=".$idOrdenServicioPadre." AND idEstado=20";
	$nRestantes=$con->obtenerValor($consulta);
	if($nRestantes==0)
		return true;
	
	$consulta="SELECT idUsuarioCambio FROM 941_bitacoraEtapasFormularios WHERE idFormulario=602 AND idRegistro=".$idOrdenServicioPadre." AND etapaActual=2.5";
	$idUsuarioDestinatario=$con->obtenerValor($consulta);
	
	$consulta="SELECT COUNT(*) FROM 9060_tableroControl_5 WHERE idUsuarioDestinatario=".$idUsuarioDestinatario.
			" AND idNotificacion=0 AND iFormulario=602 AND iRegistro=".$idOrdenServicioPadre.
			" AND tipoNotificacion='Orden de solicitud de recursos confirmada'";
	$nRestantes=$con->obtenerValor($consulta);
	if($nRestantes>0)
		return true;
		
	
	
	$idUsuarioRemitente=$_SESSION["idUsr"];
	
	$nombreUsuario=obtenerNombreUsuario($idUsuarioDestinatario);
	$arrValores["fechaAsignacion"]=date("Y-m-d H:i:s");
	$arrValores["tipoNotificacion"]="Orden de Solicitud de Recursos Confirmada";
	$arrValores["usuarioRemitente"]=obtenerNombreUsuario($idUsuarioRemitente);
	$arrValores["idUsuarioRemitente"]=$idUsuarioRemitente;
	$arrValores["usuarioDestinatario"]=str_replace(" (Suplantado)","",$nombreUsuario);
	$arrValores["idUsuarioDestinatario"]=$idUsuarioDestinatario;
	$arrValores["idEstado"]="1";
	$arrValores["contenidoMensaje"]="";
	$arrValores["objConfiguracion"]='{"actorAccesoProceso":"185_0","funcionApertura":"mostrarVentanaAperturaProcesoNotificacion"}';
	$arrValores["permiteAbrirProceso"]="1";
	$arrValores["idNotificacion"]="233";
	$arrValores["numeroCarpetaAdministrativa"]="";
	$arrValores["iFormulario"]=602;
	$arrValores["iRegistro"]=$idOrdenServicioPadre;
	$arrValores["iReferencia"]=-1;
	$arrValores["nombrePrograma"]=$fRegistroBase[2];
	$consulta="SELECT codigo FROM _602_tablaDinamica WHERE id__602_tablaDinamica=".$idOrdenServicioPadre;
	$folioOT=$con->obtenerValor($consulta);
	
	$arrValores["folioOT"]=$fRegistroBase[1];
	$consulta="SELECT Institucion FROM 801_adscripcion WHERE idUsuario=".$idUsuarioDestinatario;
	$codigoUnidad=$con->obtenerValor($consulta);
	$arrValores["codigoUnidad"]=$codigoUnidad;
	
	
	$idTablero=4;
	$consulta="";
	$camposInsert="";
	$camposValues="";
	foreach($arrValores as $campo=>$valor)
	{
		if($camposInsert=="")
			$camposInsert=$campo;
		else
			$camposInsert.=",".$campo;

		if($camposValues=="")
			$camposValues=($valor==""?"NULL":"'".cv($valor)."'");
		else
			$camposValues.=",".($valor==""?"NULL":"'".cv($valor)."'");
	}

	$consulta="insert into 9060_tableroControl_".$idTablero."(".$camposInsert.") values(".$camposValues.")";
	
	return $con->ejecutarConsulta($consulta);
	
}

function enviarNotificacionCancelacionRecurso($idRegistro)
{
	global $con;
	
	$arrOrdenesNotifica=array();
	
	$consulta="SELECT idReferencia FROM _602_recursosOrdenServicios WHERE idRecursoOrigen=".$idRegistro;
	$resOrden=$con->obtenerFilas($consulta);
	while($filaOrden=mysql_fetch_row($resOrden))
	{
		$idOrden=$filaOrden[0];
		$arrOrdenesNotifica[$idOrden]=1;
		
		$consulta="SELECT * FROM _602_recursosOrdenServicios WHERE idPadre=".$idRegistro;
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$consulta="SELECT idReferencia FROM _602_recursosOrdenServicios WHERE idRecursoOrigen=".$fila[0];
			$resOrden2=$con->obtenerFilas($consulta);
			while($filaOrden2=mysql_fetch_row($resOrden2))
			{
				$idOrden=$filaOrden2[0];
				$arrOrdenesNotifica[$idOrden]=1;
				obtenerOrdenesReferenciaHijos($arrOrdenesNotifica,$fila[0]);
			}
		}
	}
	
	
	foreach($arrOrdenesNotifica as $idOrden=>$resto)
	{
	
		$consulta="SELECT idUsuarioDestinatario FROM 9060_tableroControl_4 WHERE iFormulario=602 AND iRegistro=".$idOrden." AND idNotificacion=226";
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$idUsuarioDestinatario=$fila[0];
			
			$idUsuarioRemitente=$_SESSION["idUsr"];
			
			$consulta="SELECT codigo,nombrePrograma FROM _602_tablaDinamica WHERE id__602_tablaDinamica=".$idOrden;
			$fOrdenServicio=$con->obtenerPrimeraFila($consulta);
			
			$nombreUsuario=obtenerNombreUsuario($idUsuarioDestinatario);
			$arrValores["fechaAsignacion"]=date("Y-m-d H:i:s");
			$arrValores["tipoNotificacion"]="Cancelación de Recurso Solicitado";
			$arrValores["usuarioRemitente"]=obtenerNombreUsuario($idUsuarioRemitente);
			$arrValores["idUsuarioRemitente"]=$idUsuarioRemitente;
			$arrValores["usuarioDestinatario"]=str_replace(" (Suplantado)","",$nombreUsuario);
			$arrValores["idUsuarioDestinatario"]=$idUsuarioDestinatario;
			$arrValores["idEstado"]="1";
			$arrValores["contenidoMensaje"]="";
			$arrValores["objConfiguracion"]='{"actorAccesoProceso":"186_0","funcionApertura":"mostrarVentanaAperturaProcesoNotificacion"}';
			$arrValores["permiteAbrirProceso"]="1";
			$arrValores["idNotificacion"]="0";
			$arrValores["numeroCarpetaAdministrativa"]="";
			$arrValores["iFormulario"]=602;
			$arrValores["iRegistro"]=$idOrden;
			$arrValores["iReferencia"]=-1;
			$arrValores["nombrePrograma"]=$fOrdenServicio[1];
			$arrValores["idNotificacion"]="234";
			$arrValores["folioOT"]=$fOrdenServicio[0];
			$consulta="SELECT Institucion FROM 801_adscripcion WHERE idUsuario=".$idUsuarioDestinatario;
			$codigoUnidad=$con->obtenerValor($consulta);
			$arrValores["codigoUnidad"]=$codigoUnidad;
			
			
			$idTablero=5;
			$consulta="";
			$camposInsert="";
			$camposValues="";
			foreach($arrValores as $campo=>$valor)
			{
				if($camposInsert=="")
					$camposInsert=$campo;
				else
					$camposInsert.=",".$campo;
		
				if($camposValues=="")
					$camposValues=($valor==""?"NULL":"'".cv($valor)."'");
				else
					$camposValues.=",".($valor==""?"NULL":"'".cv($valor)."'");
			}
		
			$consulta="insert into 9060_tableroControl_".$idTablero."(".$camposInsert.") values(".$camposValues.")";
			
			$con->ejecutarConsulta($consulta);
		}
	}
	return true;
}

function obtenerOrdenesReferenciaHijos(&$arrOrdenesNotifica,$idRegistro)
{
	global $con;
	$consulta="SELECT * FROM _602_recursosOrdenServicios WHERE idPadre=".$idRegistro;
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		$consulta="SELECT idReferencia FROM _602_recursosOrdenServicios WHERE idRecursoOrigen=".$fila[0];
		$resOrden2=$con->obtenerFilas($consulta);
		while($filaOrden2=mysql_fetch_row($resOrden2))
		{
			$idOrden=$filaOrden2[0];
			$arrOrdenesNotifica[$idOrden]=1;
			obtenerOrdenesReferenciaHijos($arrOrdenesNotifica,$fila[0]);
		}	
	}
}

function enviarNotificacionComentarioRecurso($idComentario)
{
	global $con;
	
	$idUsuarioDestinatario=-1;
	$consulta="SELECT idRegistroResponde,idRegistroRecurso FROM 602_comentariosRecursosOrdenServicio WHERE idRegistro=".$idComentario;
	$fComentario=$con->obtenerPrimeraFila($consulta);
	$idRegistro=$fComentario[1];
	$consulta="SELECT idReferencia FROM _602_recursosOrdenServicios WHERE idRegistro=".$idRegistro;
	$idRegistroBase=$con->obtenerValor($consulta);
		
	$actorAccesoProceso="";
	if($fComentario[0]==-1)
	{
		$consulta="SELECT idOrdenServicioPadre FROM _602_tablaDinamica WHERE id__602_tablaDinamica=".$idRegistroBase;
		$idOrdenServicioPadre=$con->obtenerValor($consulta);
		$consulta="SELECT idUsuarioCambio FROM 941_bitacoraEtapasFormularios WHERE idFormulario=602 AND idRegistro=".$idOrdenServicioPadre." AND etapaActual=2.5";
		$idUsuarioDestinatario=$con->obtenerValor($consulta);
	}
	else
	{
		$consulta="SELECT respComentario,idReferencia FROM 602_comentariosRecursosOrdenServicio WHERE idRegistro=".$fComentario[0];
		$fDatosComentariosRespuesta=$con->obtenerPrimeraFila($consulta);
		$idUsuarioDestinatario=$fDatosComentariosRespuesta[0];
		$idOrdenServicioPadre=$fDatosComentariosRespuesta[1];
	}
	
	$idRegistroFolio=-1;
	$consulta="SELECT idOrdenServicioPadre,codigo,nombrePrograma FROM _602_tablaDinamica WHERE id__602_tablaDinamica=".$idOrdenServicioPadre;
	$fOrdenOT=$con->obtenerPrimeraFila($consulta);
	$iOrdenPadre=$fOrdenOT[0];
	if($iOrdenPadre=="")
	{
		$actorAccesoProceso="185_0";
		
	}
	else
	{
		$actorAccesoProceso="186_0";
		
	}
	$idUsuarioRemitente=$_SESSION["idUsr"];
	
	$nombreUsuario=obtenerNombreUsuario($idUsuarioDestinatario);
	$arrValores["fechaAsignacion"]=date("Y-m-d H:i:s");
	$arrValores["tipoNotificacion"]="Nuevo Comentario de Recurso Recibido";
	$arrValores["usuarioRemitente"]=obtenerNombreUsuario($idUsuarioRemitente);
	$arrValores["idUsuarioRemitente"]=$idUsuarioRemitente;
	$arrValores["usuarioDestinatario"]=str_replace(" (Suplantado)","",$nombreUsuario);
	$arrValores["idUsuarioDestinatario"]=$idUsuarioDestinatario;
	$arrValores["idNotificacion"]="235";
	$arrValores["idEstado"]="1";
	$arrValores["contenidoMensaje"]="";
	$arrValores["objConfiguracion"]='{"actorAccesoProceso":"'.$actorAccesoProceso.'","funcionApertura":"mostrarVentanaAperturaProcesoNotificacion"}';
	$arrValores["permiteAbrirProceso"]="1";
	$arrValores["numeroCarpetaAdministrativa"]="";
	$arrValores["iFormulario"]=602;
	$arrValores["iRegistro"]=$idOrdenServicioPadre;
	$arrValores["iReferencia"]=-1;
	$arrValores["nombrePrograma"]=$fOrdenOT[2];
	$consulta="SELECT codigo FROM _602_tablaDinamica WHERE id__602_tablaDinamica=".$idOrdenServicioPadre;
	$folioOT=$con->obtenerValor($consulta);
	$arrValores["folioOT"]=$folioOT;
	$consulta="SELECT Institucion FROM 801_adscripcion WHERE idUsuario=".$idUsuarioDestinatario;
	$codigoUnidad=$con->obtenerValor($consulta);
	$arrValores["codigoUnidad"]=$codigoUnidad;
	
	
	$idTablero=5;
	$consulta="";
	$camposInsert="";
	$camposValues="";
	foreach($arrValores as $campo=>$valor)
	{
		if($camposInsert=="")
			$camposInsert=$campo;
		else
			$camposInsert.=",".$campo;

		if($camposValues=="")
			$camposValues=($valor==""?"NULL":"'".cv($valor)."'");
		else
			$camposValues.=",".($valor==""?"NULL":"'".cv($valor)."'");
	}

	$consulta="insert into 9060_tableroControl_".$idTablero."(".$camposInsert.") values(".$camposValues.")";
	
	return $con->ejecutarConsulta($consulta);
	
}

function obtenerTitularesPuesto($idPuesto)
{
	global $con;
	
	$arrPuestos="";
	$fechaActual=date("Y-m-d");
	$consulta="SELECT u.idUsuario,u.Nombre FROM _598_tablaDinamica p,800_usuarios u WHERE Puesto in(".$idPuesto.") AND p.fechaInicio<='".$fechaActual."'
			and u.idUsuario=p.idReferencia order by u.Nombre";

	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		$agregar=false;
		
		$consulta="SELECT Puesto,idEstado,id__598_tablaDinamica FROM _598_tablaDinamica WHERE idReferencia=".$fila[0]." AND  fechaInicio<='".$fechaActual.
				"'  ORDER BY fechaInicio DESC";
		$fRegistro=$con->obtenerPrimeraFila($consulta);
		
		
		
		if(($fRegistro[0]==$idPuesto)&&($fRegistro[1]==1))
		{
			$agregar=true;
		}
		else
		{
			if(($fRegistro[0]==$idPuesto)&&($fRegistro[1]==2))
			{
				$consulta="SELECT count(*) FROM _599_tablaDinamica WHERE idReferencia=".$fRegistro[2]." and fechaUltimoDia>='".$fechaActual."'";
				$nReg=$con->obtenerValor($consulta);
				if($nReg>0)
					$agregar=true;
			}
		}
		if($agregar)
		{
			$o="['".$fila[0]."','".cv($fila[1])."']";
			if($arrPuestos=="")
				$arrPuestos=$o;
			else
				$arrPuestos.=",".$o;
		}
	}
	
	return "[".$arrPuestos."]";
	
}

function obtenerCoordinadorTitularFabrica($idFormulario,$idRegistro,$actorDestinatario)
{
	global $con;
	
	
	$consulta="SELECT nombrePrograma FROM _602_tablaDinamica WHERE id__602_tablaDinamica=".$idRegistro;
	$idPrograma=$con->obtenerValor($consulta);
	$consulta="SELECT fabrica FROM _589_tablaDinamica WHERE id__589_tablaDinamica=".$idPrograma;
	$idFabrica=$con->obtenerValor($consulta);
	
	$rolActor=obtenerTituloRol($actorDestinatario);
	
	$arrDestinatario=array();
	$consulta="SELECT idCoordinador FROM _581_gridCoordinador WHERE idReferencia=".$idFabrica;
	
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		$nombreUsuario=obtenerNombreUsuario($fila[0])." (".$rolActor.")";
		$o='{"idUsuarioDestinatario":"'.$fila[0].'","nombreUsuarioDestinatario":"'.$nombreUsuario.'"}';
		$o=json_decode($o);
		array_push($arrDestinatario,$o);
		
	}
	
	
	
	
	return $arrDestinatario;
}

function enviarNotificacionRecursoAgregado($idRegistro)
{
	global $con;
	
	$idUsuarioDestinatario=-1;
	
	
	$actorAccesoProceso="186_0";
	$idUsuarioRemitente=$_SESSION["idUsr"];
	
	$consulta="SELECT codigoInstitucion FROM _602_tablaDinamica WHERE id__602_tablaDinamica=".$idRegistro;
	$nombreArea=$con->obtenerValor($consulta);
	
	$consulta="SELECT responsableAutorizacion,responsableAusencia FROM _600_tablaDinamica WHERE nombreArea='".$nombreArea."'";
	$filaDestinatario=$con->obtenerPrimeraFila($consulta);
	
	
	$arrDestinatarios=array();
	array_push($arrDestinatarios,$filaDestinatario[0]);
	if($filaDestinatario[1]!=-1)
	{
		array_push($arrDestinatarios,$filaDestinatario[1]);
	}
	
	foreach($arrDestinatarios as $idUsuarioDestinatario)
	{
		$nombreUsuario=obtenerNombreUsuario($idUsuarioDestinatario);
		$arrValores["fechaAsignacion"]=date("Y-m-d H:i:s");
		$arrValores["tipoNotificacion"]="Nuevo Recurso Agregado a la Orden de Servicio";
		$arrValores["usuarioRemitente"]=obtenerNombreUsuario($idUsuarioRemitente);
		$arrValores["idUsuarioRemitente"]=$idUsuarioRemitente;
		$arrValores["usuarioDestinatario"]=str_replace(" (Suplantado)","",$nombreUsuario);
		$arrValores["idUsuarioDestinatario"]=$idUsuarioDestinatario;
		$arrValores["idEstado"]="1";
		$arrValores["contenidoMensaje"]="";
		$arrValores["objConfiguracion"]='{"actorAccesoProceso":"'.$actorAccesoProceso.'","funcionApertura":"mostrarVentanaAperturaProcesoNotificacion"}';
		$arrValores["permiteAbrirProceso"]="1";
		$arrValores["idNotificacion"]="236";
		$arrValores["numeroCarpetaAdministrativa"]="";
		$arrValores["iFormulario"]=602;
		$arrValores["iRegistro"]=$idRegistro;
		$arrValores["iReferencia"]=-1;
		$consulta="SELECT codigo,nombrePrograma FROM _602_tablaDinamica WHERE id__602_tablaDinamica=".$idRegistro;
		$fOrdenServicio=$con->obtenerPrimeraFila($consulta);
		
		$arrValores["folioOT"]=$fOrdenServicio[0];
		$consulta="SELECT Institucion FROM 801_adscripcion WHERE idUsuario=".$idUsuarioDestinatario;
		$codigoUnidad=$con->obtenerValor($consulta);
		$arrValores["codigoUnidad"]=$codigoUnidad;
		$arrValores["nombrePrograma"]=$fOrdenServicio[1];
		
		$idTablero=5;
		$consulta="";
		$camposInsert="";
		$camposValues="";
		foreach($arrValores as $campo=>$valor)
		{
			if($camposInsert=="")
				$camposInsert=$campo;
			else
				$camposInsert.=",".$campo;
	
			if($camposValues=="")
				$camposValues=($valor==""?"NULL":"'".cv($valor)."'");
			else
				$camposValues.=",".($valor==""?"NULL":"'".cv($valor)."'");
		}
	
		$consulta="insert into 9060_tableroControl_".$idTablero."(".$camposInsert.") values(".$camposValues.")";
		
		$con->ejecutarConsulta($consulta);
	}
	return true;
	
}


function cancelarSubOT($idRegistro,$motivo)
{
	global $con;	
	
	$consulta="SELECT idOrdenServicioPadre,codigo FROM _602_tablaDinamica WHERE id__602_tablaDinamica=".$idRegistro;
	$filaSubOT=$con->obtenerPrimeraFila($consulta);
	
	cambiarEtapaFormulario(602,$idRegistro,40,$motivo,-1,"NULL","NULL",1061);
	
	$consulta="UPDATE _602_recursosOrdenServicios SET idSituacionAutorizacionRecurso=4 WHERE idFormulario=602 AND idReferencia=".$idRegistro;
	$con->ejecutarConsulta($consulta);
	
	$consulta="UPDATE 9060_tableroControl_4 SET idEstado=2 WHERE iFormulario=602 AND iRegistro=".$idRegistro." AND idNotificacion=226";
	return $con->ejecutarConsulta($consulta);
	
	/*$idRegistroPadre=$filaSubOT[0];
	cambiarEtapaFormulario(602,$idRegistroPadre,10,"Se cancela Sub OT ".$filaSubOT[1].". Comentario adicional: ".$motivo,-1,"NULL","NULL",1061);
		
	$consulta="SELECT * FROM _602_tablaDinamica WHERE idOrdenServicioPadre=".$idRegistroPadre." and id__602_tablaDinamica<>".$idRegistro;
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		cancelarSubOTAux($fila[0],"Se cancela Sub OT ".$filaSubOT[1].". Comentario adicional: ".$motivo);
	}
	$consulta="UPDATE 9060_tableroControl_4 SET idEstado=2 WHERE iFormulario=602 AND iRegistro=".$idRegistroPadre." AND idNotificacion=221";
	$con->ejecutarConsulta($consulta);
	$consulta="UPDATE _602_recursosOrdenServicios SET idSituacionAutorizacionRecurso=4 WHERE idFormulario=602 AND idReferencia=".$idRegistroPadre;
	
	return $con->ejecutarConsulta($consulta);*/
	
	
}

function cancelarSubOTAux($idRegistro,$motivo)
{
	
	global $con;
	
	cambiarEtapaFormulario(602,$idRegistro,40,$motivo,-1,"NULL","NULL",1061);
	
	$consulta="UPDATE _602_recursosOrdenServicios SET idSituacionAutorizacionRecurso=4 WHERE idFormulario=602 AND idReferencia=".$idRegistro;
	$con->ejecutarConsulta($consulta);
	$consulta="UPDATE 9060_tableroControl_4 SET idEstado=2 WHERE iFormulario=602 AND iRegistro=".$idRegistro." AND idNotificacion=226";
	return $con->ejecutarConsulta($consulta);
}


function esUsuarioCoordinador()
{
	if((existeRol("'185_0'"))||(existeRol("'200_0'")))
	{
		return 1;
	}
	return 0;
}

function esUsuarioResponsableGeneradorOT()
{
	if(existeRol("'184_0'"))
	{
		return 1;
	}
	return 0;
}

function esUsuarioResponsableCierreOT()
{
	if((existeRol("'187_0'"))||(existeRol("'200_0'")))
	{
		return 1;
	}
	return 0;
}

function esUsuarioResponsableConciliacion()
{
	if(existeRol("'182_0'"))
	{
		return 1;
	}
	return 0;
}


function esUsuarioAsignadorRecursos()
{
	global $con;
	$consulta="SELECT responsableAutorizacion,responsableAusencia FROM _600_tablaDinamica WHERE 
			responsableAutorizacion=".$_SESSION["idUsr"]." or responsableAusencia=".$_SESSION["idUsr"];
	$filaRecurso=$con->obtenerPrimeraFila($consulta);
	if($filaRecurso)
		return 1;
	return 0;
}

function enviarNotificacionPersonalRequerido($idRegistro)
{
	global $con;
	
	$idUsuarioDestinatario=-1;
	
	
	$actorAccesoProceso="188_0";
	$idUsuarioRemitente=$_SESSION["idUsr"];
	
	$consulta="SELECT idAsignacion FROM _602_recursosOrdenServicios WHERE idFormulario=602 AND idReferencia=".$idRegistro.
				" AND tipoRecurso=2 and idAsignacion<>-1 and idSituacionAutorizacionRecurso<>4";
	
	$resPersonal=$con->obtenerFilas($consulta);
	while($filaPersonal=mysql_fetch_row($resPersonal))
	{
		$nombreUsuario=obtenerNombreUsuario($filaPersonal[0]);
		$arrValores["fechaAsignacion"]=date("Y-m-d H:i:s");
		$arrValores["tipoNotificacion"]="Notificación de Asignaci&oacute;n a Producci&oacute;n";
		$arrValores["usuarioRemitente"]=obtenerNombreUsuario($idUsuarioRemitente);
		$arrValores["idUsuarioRemitente"]=$idUsuarioRemitente;
		$arrValores["usuarioDestinatario"]=str_replace(" (Suplantado)","",$nombreUsuario);
		$arrValores["idUsuarioDestinatario"]=$filaPersonal[0];
		$arrValores["idEstado"]="1";
		$arrValores["contenidoMensaje"]="";
		$arrValores["objConfiguracion"]='{"actorAccesoProceso":"'.$actorAccesoProceso.'","funcionApertura":"mostrarVentanaAperturaProcesoNotificacion"}';
		$arrValores["permiteAbrirProceso"]="1";
		$arrValores["idNotificacion"]="237";
		$arrValores["numeroCarpetaAdministrativa"]="";
		$arrValores["iFormulario"]=602;
		$arrValores["iRegistro"]=$idRegistro;
		$consulta="SELECT codigo,nombrePrograma FROM _602_tablaDinamica WHERE id__602_tablaDinamica=".$idRegistro;
		$fOrdenServicio=$con->obtenerPrimeraFila($consulta);
		$arrValores["folioOT"]=$fOrdenServicio[0];
		$arrValores["iReferencia"]=-1;
		$consulta="SELECT Institucion FROM 801_adscripcion WHERE idUsuario=".$idUsuarioDestinatario;
		$codigoUnidad=$con->obtenerValor($consulta);
		$arrValores["codigoUnidad"]=$codigoUnidad;
		$arrValores["nombrePrograma"]=$fOrdenServicio[1];
		
		$idTablero=4;
		$consulta="";
		$camposInsert="";
		$camposValues="";
		foreach($arrValores as $campo=>$valor)
		{
			if($camposInsert=="")
				$camposInsert=$campo;
			else
				$camposInsert.=",".$campo;
	
			if($camposValues=="")
				$camposValues=($valor==""?"NULL":"'".cv($valor)."'");
			else
				$camposValues.=",".($valor==""?"NULL":"'".cv($valor)."'");
		}
	
		$consulta="insert into 9060_tableroControl_".$idTablero."(".$camposInsert.") values(".$camposValues.")";
		
		$con->ejecutarConsulta($consulta);
	}
	return true;
	
}

function respaldarProgramacionSemanal($idSemana,$idMaster)
{
	global $con;
	
	$version=0;
	$consulta="SELECT situacionActual,version FROM  5001_situacionProgramacionSemanal WHERE idSemana=".$idSemana." AND idMaster= ".$idMaster;
	$fBitacora=$con->obtenerPrimeraFila($consulta);
	if(!$fBitacora)
	{
		$fBitacora[0]=0;
		$fBitacora[1]=0;
	}
	
	$consulta="SELECT idRegistroEvento FROM 5001_programacionEventosRespaldoVersion WHERE idMaster=".$idMaster." AND idSemana=".$idSemana." AND version=".$fBitacora[1];
	$listaRegistro=$con->obtenerListaValores($consulta);
	if($listaRegistro=="")
		$listaRegistro=-1;
		
	$x=0;
	$query[$x]="begin";
	$x++;
	$query[$x]="DELETE FROM 5001_programacionEventosRespaldoVersion WHERE idMaster=".$idMaster." AND idSemana=".$idSemana." AND version=".$fBitacora[1];
	$x++;
	$query[$x]="DELETE FROM 5001_programacionEventosFeedTransmitionRespaldoVersion WHERE idProgramacion IN(".$listaRegistro.")
				AND version=".$fBitacora[1];
	$x++;
	$query[$x]="INSERT INTO 5001_programacionEventosRespaldoVersion
				(idRegistroEvento,VERSION,horaInicio,horaFin,idMaster,idSemana,idPrograma,cveTransmision,detalleEvento,
				tipoEmision,porConfirmar,eventoEspecial,comentariosAdicionales,dia,esEventoAlterno,colorEvento)
				SELECT idRegistro,'".$fBitacora[1]."' as version,horaInicio,horaFin,idMaster,idSemana,idPrograma,cveTransmision,detalleEvento,
				tipoEmision,porConfirmar,eventoEspecial,comentariosAdicionales,dia,esEventoAlterno,colorEvento FROM 5000_programacionEventos WHERE idMaster=".$idMaster."
				AND idSemana=".$idSemana;
	$x++;
	
	$query[$x]="INSERT INTO 5001_programacionEventosFeedTransmitionRespaldoVersion(idProgramacion,idFeedTransmition,VERSION)
				SELECT idProgramacion,idFeedTransmition,'".$fBitacora[1]."' AS VERSION FROM 5000_programacionEventosFeedTransmition pr,
				5000_programacionEventos e WHERE pr.idProgramacion=e.idRegistro AND e.idMaster=".$idMaster." AND e.idSemana=".$idSemana;
	$x++;
	$query[$x]="UPDATE 5001_situacionProgramacionSemanal SET version=".($fBitacora[1]+1)." WHERE idSemana=".$idSemana." AND idMaster=".$idMaster;
	$x++;
	$query[$x]="commit";
	$x++;
	
	return $con->ejecutarBloque($query);
	
}

function enviarNotificacionSolicitaAutorizacionProgramacionSemanal($idSemana,$idMaster)
{
	global $con;
	
	$idUsuarioRemitente=$_SESSION["idUsr"];
	$consulta="SELECT nombre FROM _593_tablaDinamica WHERE id__593_tablaDinamica=".$idMaster;
	$master=$con->obtenerValor($consulta);
	$consulta="SELECT etiqueta FROM 1050_semanasAnio WHERE idSemana=".$idSemana;
	$semana=$con->obtenerValor($consulta);
	
	
	$consulta="SELECT idUsuario FROM 807_usuariosVSRoles WHERE idRol=190";
	$rDestinatario=$con->obtenerFilas($consulta);
	while($fDestintario=mysql_fetch_row($rDestinatario))
	{
		$idUsuarioDestinatario=$fDestintario[0];
		$idUsuarioRemitente=$_SESSION["idUsr"];
		$nombreUsuario=obtenerNombreUsuario($idUsuarioDestinatario);
		
		$arrValores["fechaAsignacion"]=date("Y-m-d H:i:s");
		$arrValores["tipoNotificacion"]="Se Solicita Validación de Programación ".$semana." (".$master.") ";
		$arrValores["usuarioRemitente"]=obtenerNombreUsuario($idUsuarioRemitente);
		$arrValores["idUsuarioRemitente"]=$idUsuarioRemitente;
		$arrValores["usuarioDestinatario"]=str_replace(" (Suplantado)","",$nombreUsuario);
		$arrValores["idUsuarioDestinatario"]=$idUsuarioDestinatario;
		$arrValores["idEstado"]="1";
		$arrValores["contenidoMensaje"]="";
		$arrValores["objConfiguracion"]='{"idSemana":"'.$idSemana.'","idMaster":"'.$idMaster.'","actorAccesoProceso":"190_0","funcionApertura":"mostrarVentanaAperturaSemanaProgramacionAutorizador"}';
		$arrValores["permiteAbrirProceso"]="1";
		$arrValores["idNotificacion"]="-11";
		$arrValores["numeroCarpetaAdministrativa"]="";
		$arrValores["iFormulario"]=$idSemana*-1;
		$arrValores["iRegistro"]=$idMaster*-1;
		$arrValores["iReferencia"]=-1;
		$consulta="SELECT Institucion FROM 801_adscripcion WHERE idUsuario=".$idUsuarioDestinatario;
		$codigoUnidad=$con->obtenerValor($consulta);
		$arrValores["codigoUnidad"]=$codigoUnidad;
		
		
		$idTablero=5;
		$consulta="";
		$camposInsert="";
		$camposValues="";
		foreach($arrValores as $campo=>$valor)
		{
			if($camposInsert=="")
				$camposInsert=$campo;
			else
				$camposInsert.=",".$campo;
	
			if($camposValues=="")
				$camposValues=($valor==""?"NULL":"'".cv($valor)."'");
			else
				$camposValues.=",".($valor==""?"NULL":"'".cv($valor)."'");
		}
	
		$consulta="insert into 9060_tableroControl_".$idTablero."(".$camposInsert.") values(".$camposValues.")";
		$con->ejecutarConsulta($consulta);
	}
	return true;
}


function enviarNotificacionSolicitaCambiosProgramacionSemanal($idSemana,$idMaster)
{
	global $con;
	
	$idUsuarioRemitente=$_SESSION["idUsr"];
	$consulta="SELECT nombre FROM _593_tablaDinamica WHERE id__593_tablaDinamica=".$idMaster;
	$master=$con->obtenerValor($consulta);
	$consulta="SELECT etiqueta FROM 1050_semanasAnio WHERE idSemana=".$idSemana;
	$semana=$con->obtenerValor($consulta);
	
	
	$consulta="SELECT idUsuario FROM 807_usuariosVSRoles WHERE idRol=189";
	$rDestinatario=$con->obtenerFilas($consulta);
	while($fDestintario=mysql_fetch_row($rDestinatario))
	{
		$idUsuarioDestinatario=$fDestintario[0];
		$idUsuarioRemitente=$_SESSION["idUsr"];
		$nombreUsuario=obtenerNombreUsuario($idUsuarioDestinatario);
		
		$arrValores["fechaAsignacion"]=date("Y-m-d H:i:s");
		$arrValores["tipoNotificacion"]="Se Envían Observaciones ".$semana." (".$master.") ";
		$arrValores["usuarioRemitente"]=obtenerNombreUsuario($idUsuarioRemitente);
		$arrValores["idUsuarioRemitente"]=$idUsuarioRemitente;
		$arrValores["usuarioDestinatario"]=str_replace(" (Suplantado)","",$nombreUsuario);
		$arrValores["idUsuarioDestinatario"]=$idUsuarioDestinatario;
		$arrValores["idEstado"]="1";
		$arrValores["contenidoMensaje"]="";
		$arrValores["objConfiguracion"]='{"idSemana":"'.$idSemana.'","idMaster":"'.$idMaster.'","actorAccesoProceso":"189_0","funcionApertura":"mostrarVentanaAperturaSemanaProgramacion"}';
		$arrValores["permiteAbrirProceso"]="1";
		$arrValores["idNotificacion"]="-10";
		$arrValores["numeroCarpetaAdministrativa"]="";
		$arrValores["iFormulario"]=$idSemana*-1;
		$arrValores["iRegistro"]=$idMaster*-1;
		$arrValores["iReferencia"]=-1;
		$consulta="SELECT Institucion FROM 801_adscripcion WHERE idUsuario=".$idUsuarioDestinatario;
		$codigoUnidad=$con->obtenerValor($consulta);
		$arrValores["codigoUnidad"]=$codigoUnidad;
		
		
		$idTablero=5;
		$consulta="";
		$camposInsert="";
		$camposValues="";
		foreach($arrValores as $campo=>$valor)
		{
			if($camposInsert=="")
				$camposInsert=$campo;
			else
				$camposInsert.=",".$campo;
	
			if($camposValues=="")
				$camposValues=($valor==""?"NULL":"'".cv($valor)."'");
			else
				$camposValues.=",".($valor==""?"NULL":"'".cv($valor)."'");
		}
	
		$consulta="insert into 9060_tableroControl_".$idTablero."(".$camposInsert.") values(".$camposValues.")";
		$con->ejecutarConsulta($consulta);
	}
	return true;
}


function enviarNotificacionAutorizaProgramacionSemanal($idSemana,$idMaster)
{
	global $con;
	
	$idUsuarioRemitente=$_SESSION["idUsr"];
	$consulta="SELECT nombre FROM _593_tablaDinamica WHERE id__593_tablaDinamica=".$idMaster;
	$master=$con->obtenerValor($consulta);
	$consulta="SELECT etiqueta FROM 1050_semanasAnio WHERE idSemana=".$idSemana;
	$semana=$con->obtenerValor($consulta);
	
	
	$consulta="SELECT idUsuario FROM 807_usuariosVSRoles WHERE idRol=189";
	$rDestinatario=$con->obtenerFilas($consulta);
	while($fDestintario=mysql_fetch_row($rDestinatario))
	{
		$idUsuarioDestinatario=$fDestintario[0];
		$idUsuarioRemitente=$_SESSION["idUsr"];
		$nombreUsuario=obtenerNombreUsuario($idUsuarioDestinatario);
		
		$arrValores["fechaAsignacion"]=date("Y-m-d H:i:s");
		$arrValores["tipoNotificacion"]="Se Autoriza Programaci&oacute;n ".$semana." (".$master.") ";
		$arrValores["usuarioRemitente"]=obtenerNombreUsuario($idUsuarioRemitente);
		$arrValores["idUsuarioRemitente"]=$idUsuarioRemitente;
		$arrValores["usuarioDestinatario"]=str_replace(" (Suplantado)","",$nombreUsuario);
		$arrValores["idUsuarioDestinatario"]=$idUsuarioDestinatario;
		$arrValores["idEstado"]="1";
		$arrValores["contenidoMensaje"]="";
		$arrValores["objConfiguracion"]='{"idSemana":"'.$idSemana.'","idMaster":"'.$idMaster.'","actorAccesoProceso":"189_0","funcionApertura":"mostrarVentanaAperturaSemanaProgramacion"}';
		$arrValores["permiteAbrirProceso"]="1";
		$arrValores["idNotificacion"]="0";
		$arrValores["numeroCarpetaAdministrativa"]="";
		$arrValores["iFormulario"]=-1;
		$arrValores["iRegistro"]=-1;
		$arrValores["iReferencia"]=-1;
		$consulta="SELECT Institucion FROM 801_adscripcion WHERE idUsuario=".$idUsuarioDestinatario;
		$codigoUnidad=$con->obtenerValor($consulta);
		$arrValores["codigoUnidad"]=$codigoUnidad;
		
		
		$idTablero=5;
		$consulta="";
		$camposInsert="";
		$camposValues="";
		foreach($arrValores as $campo=>$valor)
		{
			if($camposInsert=="")
				$camposInsert=$campo;
			else
				$camposInsert.=",".$campo;
	
			if($camposValues=="")
				$camposValues=($valor==""?"NULL":"'".cv($valor)."'");
			else
				$camposValues.=",".($valor==""?"NULL":"'".cv($valor)."'");
		}
	
		$consulta="insert into 9060_tableroControl_".$idTablero."(".$camposInsert.") values(".$camposValues.")";
		$con->ejecutarConsulta($consulta);
	}
	return true;
}


function registrarCostoServicioRenta($idFormulario,$idRegistro)
{
	global $con;
	
	$consulta="SELECT * FROM _623_tablaDinamica WHERE id__623_tablaDinamica=".$idRegistro;
	$fDatosSolicitudRenta=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$consulta="SELECT cotizaciones,comentariosAdicionales FROM _626_tablaDinamica WHERE idReferencia=".$idRegistro;
	$fPropuestaSelFinal=$con->obtenerPrimeraFila($consulta);
	
	$consulta="	SELECT pr.proveedor,descripcionServicio,costoServicio,monedaCotizacion,comentariosAdicionales,c.proveedor as idProveedor 
				FROM _624_tablaDinamica c,625_vistaProveedores pr WHERE c.id__624_tablaDinamica=".$fPropuestaSelFinal[0].
				" AND pr.id__625_tablaDinamica=c.proveedor";
	$fPropuestaSel=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$consulta="SELECT catRecurso FROM _585_tablaDinamica WHERE id__585_tablaDinamica=".$fDatosSolicitudRenta["conceptoContratacion"];
	$lblServicio=$con->obtenerValor($consulta);
	
	$consulta="INSERT INTO _602_otrosRecursosOrdenServicioCosto(idFormulario,idReferencia,conceptoGasto,iFormulario,iReferencia,costoServicio,moneda,idProveedor)
				values(602,".$fDatosSolicitudRenta["idReferencia"].",'Renta: ".$lblServicio."',".$idFormulario.",".$idRegistro.",".
				$fPropuestaSel["costoServicio"].",1,".$fPropuestaSel["idProveedor"].")";
	return $con->ejecutarConsulta($consulta);
}


function enviarNotificacionComentarioSemanaProgramacion($idComentario)
{
	global $con;
	
	$arrDestinatarios=array();
	$idUsuarioDestinatario=-1;
	$consulta="SELECT idRegistroResponde,idSemana,idMaster FROM 602_comentariosProgramacionSemanal WHERE idRegistro=".$idComentario;
	$fComentario=$con->obtenerPrimeraFila($consulta);
	$idSemana=$fComentario[1];
	$idMaster=$fComentario[2];
	
	$consulta="SELECT clave FROM _593_tablaDinamica WHERE id__593_tablaDinamica=".$idMaster;
	$master=$con->obtenerValor($consulta);

	$consulta="SELECT etiqueta FROM 1050_semanasAnio WHERE idSemana=".$idSemana;
	$semana=$con->obtenerValor($consulta);

	
	if($fComentario[0]==-1)
	{
		if(existeRol("'190_0'"))
		{
			$consulta="SELECT responsableCambio FROM 5001_bitacoraProgramacionSemanal WHERE idSemana=".$idSemana." AND idMaster=".$idMaster.
						" AND situacionActual=1 ORDER BY fechaCambio DESC";
			$responsableCambio=$con->obtenerValor($consulta);
			if($responsableCambio=="")
			{
				$consulta="SELECT idUsuario FROM 807_usuariosVSRoles WHERE codigoRol in('189_0','1_0')";
			}
			else
			{
				$consulta="SELECT ".$responsableCambio;
			}
		}
		else
		{
			$consulta="SELECT idUsuario FROM 807_usuariosVSRoles WHERE codigoRol='190_0'";
		}
		
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			array_push($arrDestinatarios,$fila[0]);
		}
	}
	else
	{
		$consulta="SELECT respComentario FROM 602_comentariosProgramacionSemanal WHERE idRegistro=".$fComentario[0];
		$idUsuarioDestinatario=$con->obtenerValor($consulta);
		array_push($arrDestinatarios,$idUsuarioDestinatario);
	}
	
	
	foreach($arrDestinatarios as $idUsuarioDestinatario)
	{
		$idUsuarioRemitente=$_SESSION["idUsr"];	
		$nombreUsuario=obtenerNombreUsuario($idUsuarioDestinatario);
		$arrValores["fechaAsignacion"]=date("Y-m-d H:i:s");
		$arrValores["tipoNotificacion"]="Nuevo Comentario de Programación ".$semana." - ".$master;
		$arrValores["usuarioRemitente"]=obtenerNombreUsuario($idUsuarioRemitente);
		$arrValores["idUsuarioRemitente"]=$idUsuarioRemitente;
		$arrValores["usuarioDestinatario"]=str_replace(" (Suplantado)","",$nombreUsuario);
		$arrValores["idUsuarioDestinatario"]=$idUsuarioDestinatario;
		$arrValores["idEstado"]="1";
		$arrValores["contenidoMensaje"]="";
		//$arrValores["folioOT"]=$semana." - ".$master;
		
		$consulta="SELECT COUNT(*) FROM 807_usuariosVSRoles WHERE idUsuario=".$idUsuarioDestinatario." AND codigoRol in('189_0','1_0') ";
		$nUsuario=$con->obtenerValor($consulta);
		if($nUsuario>0)
			$arrValores["objConfiguracion"]='{"idSemana":"'.$idSemana.'","idMaster":"'.$idMaster.'","actorAccesoProceso":"189_0","funcionApertura":"mostrarVentanaAperturaSemanaProgramacion"}';
		else	
			$arrValores["objConfiguracion"]='{"idSemana":"'.$idSemana.'","idMaster":"'.$idMaster.'","actorAccesoProceso":"190_0","funcionApertura":"mostrarVentanaAperturaSemanaProgramacionAutorizador"}';
			
			
		$arrValores["permiteAbrirProceso"]="1";
		$arrValores["idNotificacion"]="0";
		$arrValores["numeroCarpetaAdministrativa"]="";
		$arrValores["iFormulario"]=-1;
		$arrValores["iRegistro"]=-1;
		$arrValores["iReferencia"]=-1;
		$consulta="SELECT Institucion FROM 801_adscripcion WHERE idUsuario=".$idUsuarioDestinatario;
		$codigoUnidad=$con->obtenerValor($consulta);
		$arrValores["codigoUnidad"]=$codigoUnidad;		
		
		$idTablero=5;
		$consulta="";
		$camposInsert="";
		$camposValues="";
		foreach($arrValores as $campo=>$valor)
		{
			if($camposInsert=="")
				$camposInsert=$campo;
			else
				$camposInsert.=",".$campo;
	
			if($camposValues=="")
				$camposValues=($valor==""?"NULL":"'".cv($valor)."'");
			else
				$camposValues.=",".($valor==""?"NULL":"'".cv($valor)."'");
		}
	
		$consulta="insert into 9060_tableroControl_".$idTablero."(".$camposInsert.") values(".$camposValues.")";
	
		$con->ejecutarConsulta($consulta);
	}
	return true;
}


function generarFolioProcesosOT($idFormulario,$idRegistro)
{
	global $con;
	
	$anio=date("Y");
	
	
	$consulta="select folioBase,versionOrden,idOrdenServicioPadre FROM _602_tablaDinamica WHERE id__602_tablaDinamica=".$idRegistro;
	
	$fInfoBase=$con->obtenerPrimeraFila($consulta);
	
	if(($fInfoBase[0]=="")||($fInfoBase[0]=="N/E"))
	{
		$query="begin";
		if($con->ejecutarConsulta($query))
		{
			//$query="select folioActual FROM 7003_administradorFoliosProcesos WHERE idFormulario=".$idFormulario." AND anio=".$anio." for update";
			$query="select folioActual FROM 7003_administradorFoliosProcesos WHERE idFormulario=".$idFormulario." for update";
	
			$folioActual=$con->obtenerValor($query);
			if($folioActual=="")
			{
				$folioActual=1;
				
				$query="INSERT INTO 7003_administradorFoliosProcesos(idFormulario,anio,folioActual) VALUES(".$idFormulario.",".$anio.",".$folioActual.")";
				
			}
			else
			{
				$folioActual++;
				//$query="update 7003_administradorFoliosProcesos set folioActual=".$folioActual." where idFormulario=".$idFormulario." and anio=".$anio;
				$query="update 7003_administradorFoliosProcesos set folioActual=".$folioActual." where idFormulario=".$idFormulario;
			}
				
			if($con->ejecutarConsulta($query))
			{
				if($fInfoBase[1]=="N/E")
				{
					$fInfoBase[1]=1;
					$query="UPDATE _".$idFormulario."_tablaDinamica SET versionOrden=1 WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
					$con->ejecutarConsulta($query);
					
				}
				$query="commit";
				$con->ejecutarConsulta($query);
				
				
				
				return str_pad($folioActual,5,"0",STR_PAD_LEFT)."-".$fInfoBase[1];
				
			}
				
			
		}
	}
	else
	{
		if($fInfoBase[2]=="")
			return $fInfoBase[0]."-".str_pad($fInfoBase[1],2,"0",STR_PAD_LEFT);
		else
		{
			$consulta="select count(*) from _602_tablaDinamica where idOrdenServicioPadre=".$fInfoBase[2]." and codigo is not null";
			$numReg=$con->obtenerValor($consulta);
			$numReg++;
			return $fInfoBase[0]."-".str_pad($numReg,2,"0",STR_PAD_LEFT);
		}
	}
	
	
	
	return 0;
	
}




function cancelarOTBase($idRegistro,$motivo,$etapa)
{
	global $con;
	cambiarEtapaFormulario(602,$idRegistro,$etapa,$motivo,-1,"NULL","NULL",1061);
		
	$consulta="SELECT * FROM _602_tablaDinamica WHERE idOrdenServicioPadre=".$idRegistro;
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		cancelarSubOT($fila[0],$motivo);
	}
	$consulta="UPDATE 9060_tableroControl_4 SET idEstado=2 WHERE iFormulario=602 AND iRegistro=".$idRegistro." AND idNotificacion=221";
	$con->ejecutarConsulta($consulta);
	$consulta="UPDATE _602_recursosOrdenServicios SET idSituacionAutorizacionRecurso=4 WHERE idFormulario=602 AND idReferencia=".$idRegistro;
	return $con->ejecutarConsulta($consulta);
}

function obtenerUsuariosPuesto($fechaActual,$idPuesto,$formato,$listUsuariosIng=-1)//Formato =1 array javascrip, 2 lista de idUsuarios
{
	global $con;
	$arrRegistros="";
	$consulta="SELECT u.idUsuario,u.Nombre FROM _598_tablaDinamica p,800_usuarios u WHERE Puesto=".$idPuesto." AND p.fechaInicio<='".$fechaActual."'
				and u.idUsuario=p.idReferencia and idUsuario not in(".$listUsuariosIng.")  order by u.Nombre";
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		$agregar=false;
		
		$consulta="SELECT Puesto,idEstado,id__598_tablaDinamica FROM _598_tablaDinamica WHERE idReferencia=".$fila[0]." AND  fechaInicio<='".$fechaActual.
				"'  ORDER BY fechaInicio DESC";
		$fRegistro=$con->obtenerPrimeraFila($consulta);
		
		
		
		if(($fRegistro[0]==$idPuesto)&&($fRegistro[1]==1))
		{
			$agregar=true;
		}
		else
		{
			if(($fRegistro[0]==$idPuesto)&&($fRegistro[1]==2))
			{
				$consulta="SELECT count(*) FROM _599_tablaDinamica WHERE idReferencia=".$fRegistro[2]." and fechaUltimoDia>='".$fechaActual."'";
				$nReg=$con->obtenerValor($consulta);
				if($nReg>0)
					$agregar=true;
			}
		}
		if($agregar)
		{
			if($formato==1)
			{
				$o="['".$fila[0]."','".cv($fila[1])."']";
				if($arrRegistros=="")
					$arrRegistros=$o;
				else
					$arrRegistros.=",".$o;
			}
			else
			{
				if($arrRegistros=="")
					$arrRegistros=$fila[0];
				else
					$arrRegistros.=",".$fila[0];
			}
		}
	}
	if($formato==1)
	{
		$arrRegistros="[".$arrRegistros."]";
	}
	return $arrRegistros;
	
}


function marcarTareaAtendidaOTCoordinador($idFormulario,$idRegistro)
{
	global $con;
	$consulta="UPDATE 9060_tableroControl_4 SET idEstado=2,idUsuarioAtendio=".$_SESSION["idUsr"].",usuarioAtendio='".obtenerNombreUsuario($_SESSION["idUsr"])."',fechaAtencion='".date("Y-m-d H:i:s")."'
			 WHERE iFormulario=".$idFormulario." AND iRegistro=".$idRegistro." AND idNotificacion=221";
	$con->ejecutarConsulta($consulta);
}

function obtenerLlavesRegistrosInformes($idSemana,$tipoInforme)
{
	global $con;
	$arrFechasProduccion=array();
	$consulta="SELECT * FROM 1050_semanasAnio WHERE idSemana=".$idSemana;
	$fSemana=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$fInicio=strtotime($fSemana["fechaInicio"]);
	$fFin=strtotime($fSemana["fechaFin"]);
	
	$arrFilasReporte=array();
	
	if($tipoInforme==1)
	{
		while($fInicio<=$fFin)
		{
			$arrFechasProduccion[date("Y-m-d",$fInicio)]=array();
			
			
			$consulta="SELECT id__591_tablaDinamica,nombreRecurso FROM _591_tablaDinamica WHERE categoriaRecuso=10
						UNION
						SELECT '0','Sin Estudio Asignado' AS nombreRecurso
						 ORDER BY nombreRecurso";
			$res=$con->obtenerFilas($consulta);
			while($filaEstudio=mysql_fetch_row($res))
			{
				$arrFechasProduccion[date("Y-m-d",$fInicio)][$filaEstudio[0]]=array();
				
				$arrEstudios[$filaEstudio[0]]=1;
				
			}
			
			$fInicio=strtotime("+1 days",$fInicio);
		}
	
		$consulta="SELECT oT.*,fP.fechaProduccion FROM _628_tablaDinamica fP,_602_tablaDinamica oT 
					WHERE fechaProduccion>='".$fSemana["fechaInicio"]."' AND fechaProduccion<='".$fSemana["fechaFin"]."'
					AND oT.id__602_tablaDinamica=fP.idReferencia AND folioBase IS NULL  order by fechaProduccion";
		
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_assoc($res))
		{
			$consulta="SELECT idAsignacion FROM _602_recursosOrdenServicios WHERE idFormulario=602 AND idReferencia=".$fila["id__602_tablaDinamica"].
					" AND tipoRecurso=1 AND idRecurso=10";
			$idEstudio=$con->obtenerValor($consulta);
			if(($idEstudio=="")||($idEstudio==-1))
				$idEstudio=0;
				
			if(isset($arrEstudios[$idEstudio]))
				array_push($arrFechasProduccion[$fila["fechaProduccion"]][$idEstudio],$fila);
				
		}
	
		
		
		foreach($arrFechasProduccion as $fechaProduccion=>$resto)
		{
			foreach($resto as $idEstudio=>$arrProgramas)
			{
				if(sizeof($arrProgramas)==0)
				{
					continue;
				}
				foreach($arrProgramas  as $p)
				{	
					$consulta="SELECT cP.nombrePrograma as lblNombrePrograma FROM _589_tablaDinamica cP WHERE  cP.id__589_tablaDinamica=".$p["nombrePrograma"];
					$filaOT=$con->obtenerPrimeraFilaAsoc($consulta);
				
					$consulta="SELECT * FROM _628_tablaDinamica WHERE idReferencia=".$p["id__602_tablaDinamica"];
					$fProduccion=$con->obtenerPrimeraFilaAsoc($consulta);
				
					$lblPrograma=$filaOT["lblNombrePrograma"];
					
					$consulta="SELECT idRegistro FROM _602_recursosOrdenServicios WHERE idFormulario=602 AND idReferencia=".$p["id__602_tablaDinamica"].
							" AND tipoRecurso=1 AND idRecurso=10";
					$idRecursoEstudio=$con->obtenerValor($consulta);
					
					$consulta="SELECT idAsignacion,idRegistro FROM _602_recursosOrdenServicios WHERE idFormulario=602 AND idReferencia=".$p["id__602_tablaDinamica"].
							" AND tipoRecurso=2 AND idRecurso=23";
					$fRegProductor=$con->obtenerPrimeraFila($consulta);
					$idProductor=$fRegProductor[0];
					if($idProductor=="")
						$idProductor=-1;
						
					$arrDatosFila=array();
					$idRecursoProductor=$fRegProductor[1];
					if($idRecursoProductor=="")
						$idRecursoProductor=-1;
					
					$consulta="SELECT comentariosAdicionales FROM _602_serviciosCoordinacion WHERE idOrdenTrabajo=".$p["id__602_tablaDinamica"];
					$servicios=$con->obtenerValor($consulta);
					
					$consulta="select nombreRecurso from _591_tablaDinamica where id__591_tablaDinamica=".$idEstudio;
					$nombreEstudio=$con->obtenerValor($consulta);
					
					$consulta="SELECT nombre FROM _593_tablaDinamica WHERE id__593_tablaDinamica=".$p["idMaster"];
					$lblMaster=$con->obtenerValor($consulta);
					
					$arrDatosFila["A"]=$nombreEstudio==""?"@NULL@":$nombreEstudio;
					$arrDatosFila["B"]=date("H:i",strtotime($fProduccion["horaProduccion"]));
					$arrDatosFila["C"]=date("H:i",strtotime($fProduccion["horaEstimadaTermino"]));
					
					
					if(date("Y-m-d",strtotime($fProduccion["horaEstimadaTermino"]))!=$fechaProduccion)
					{
						$lblFechaTermino=date("H:i",strtotime($fProduccion["horaEstimadaTermino"]))."\n(".date("d/m/Y",strtotime($fProduccion["horaEstimadaTermino"])).")";
						$arrDatosFila["C"]=$lblFechaTermino;
						
					}
					
					$arrDatosFila["D"]=formatearCampoMultilinea($lblPrograma);
					$arrDatosFila["E"]=$fProduccion["procesoProduccion"]==1?"Vivo":"Grabado";
					$arrDatosFila["F"]=$lblMaster;
					$lblNombreProductor=obtenerNombreUsuario($idProductor);
					$arrDatosFila["G"]=$lblNombreProductor;
					$arrDatosFila["H"]=formatearCampoMultilinea($servicios);
					$arrDatosFila["IO"]=$p["id__602_tablaDinamica"];
					array_push($arrFilasReporte,$arrDatosFila);
					
				}
	
			}
		}
		
	}
	else
	{
		$arrFechasProduccion=array();

		$fInicio=strtotime($fSemana["fechaInicio"]);
		$fFin=strtotime($fSemana["fechaFin"]);
		while($fInicio<=$fFin)
		{
			$arrFechasProduccion[date("Y-m-d",$fInicio)]=array();
			$consulta="SELECT id__581_tablaDinamica,nombreFabrica FROM _581_tablaDinamica ORDER BY nombreFabrica";
			$rFabricas=$con->obtenerFilas($consulta);
			while($fFabrica=mysql_fetch_row($rFabricas))
			{
				$arrFechasProduccion[date("Y-m-d",$fInicio)][$fFabrica[1]]=array();
			}
			$fInicio=strtotime("+1 days",$fInicio);
		}
		
		$maxLlamado=0;
		
		$arrLlamados=array();
		
		$consulta="SELECT oT.*,fP.fechaProduccion FROM _628_tablaDinamica fP,_602_tablaDinamica oT 
					WHERE fechaProduccion>='".$fSemana["fechaInicio"]."' AND fechaProduccion<='".$fSemana["fechaFin"]."'
					AND oT.id__602_tablaDinamica=fP.idReferencia AND folioBase IS NOT NULL order by fechaProduccion";
		
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_assoc($res))
		{
			$totalLlamados=0;
			
				
			$consulta="SELECT * FROM _602_recursosOrdenServicios WHERE idFormulario=602 and idReferencia=".$fila["id__602_tablaDinamica"].
						" AND tipoRecurso=2 AND idRecurso=19";	
			$resTalestos=$con->obtenerFilas($consulta);
			if($con->filasAfectadas>0)
			{
				if(!isset($arrLlamados[$fila["id__602_tablaDinamica"]]))
					$arrLlamados[$fila["id__602_tablaDinamica"]]=array();
			}
			else
			{
				continue;
			}
			
			while($fTalento=mysql_fetch_assoc($resTalestos))
			{
				array_push($arrLlamados[$fila["id__602_tablaDinamica"]],$fTalento);
				$totalLlamados++;
			}
			
			if($maxLlamado<$totalLlamados)
				$maxLlamado=$totalLlamados;
			
			
			
			$consulta="SELECT fabrica FROM _589_tablaDinamica WHERE id__589_tablaDinamica=".$fila["nombrePrograma"];
			$idFabrica=$con->obtenerValor($consulta);
			$consulta="SELECT nombreFabrica FROM _581_tablaDinamica WHERE id__581_tablaDinamica=".$idFabrica;
			$lblFabrica=$con->obtenerValor($consulta);
			
			if(!isset($arrFechasProduccion[$fila["fechaProduccion"]][$lblFabrica][$fila["id__602_tablaDinamica"]]))
				$arrFechasProduccion[$fila["fechaProduccion"]][$lblFabrica][$fila["id__602_tablaDinamica"]]=array();
				
			array_push($arrFechasProduccion[$fila["fechaProduccion"]][$lblFabrica][$fila["id__602_tablaDinamica"]],$arrLlamados[$fila["id__602_tablaDinamica"]]);
				
		}
		
		$numReg=0;
		$arrRegistros='';
		
		
		$fabrica="";
		$fechaProduccionActual="";
		
		
		foreach($arrFechasProduccion as $fechaProduccion=>$resto)
		{
			foreach($resto as $fabricas=>$arrProgramas)
			{
				if(sizeof($arrProgramas)>0)
				{
					
				}
				else
					continue;
	
				foreach($arrProgramas as $idPrograma=>$arrOrdenes)
				{
	
					$consulta="SELECT oT.*,cP.nombrePrograma as lblNombrePrograma FROM _602_tablaDinamica oT,_589_tablaDinamica cP WHERE id__602_tablaDinamica=".$idPrograma." AND cP.id__589_tablaDinamica=oT.nombrePrograma";
					$filaOT=$con->obtenerPrimeraFilaAsoc($consulta);
					
					
					$arrValoresTalentos="";
					
					for($t=1;$t<=$maxLlamado;$t++)
					{
						$valor="";
						
						if(isset($arrOrdenes[0][$t-1]))
						{
	
							$valor=$arrOrdenes[0][$t-1]["idAsignacion"];
						}
						
						$arrValoresTalentos.=",\"talento_".$t."\":\"".$valor."\",\"aplicaTalento_".$t."\":\"".($valor!=""?1:0)."\"";
					}
					
					$consulta="SELECT * FROM _628_tablaDinamica WHERE idReferencia=".$idPrograma;
					$fProduccion=$con->obtenerPrimeraFilaAsoc($consulta);
					
					$consulta="SELECT * FROM _602_llamadosTalentos WHERE idOrdenTrabajo=".$idPrograma;
					$fRegistroLlamadoTalento=$con->obtenerPrimeraFilaAsoc($consulta);
					
					$lblPrograma=$filaOT["lblNombrePrograma"];
					$o='{"idFormulario":"602","idRegistro":"'.$idPrograma.'","idServicio":"'.$idPrograma.'","programa":"'.cv($lblPrograma).'","fecha":"'.$fechaProduccion.'",'.
					'"horaIn":"'.$fProduccion["fechaProduccion"].' '.$fProduccion["horaProduccion"].'","horaOut":"'.$fProduccion["horaEstimadaTermino"].
					'","procesoProduccion":"'.$fProduccion["procesoProduccion"].'","comentariosAdicionales":"'.cv($fProduccion["comentariosAdicionales"]).
					'","master":"'.$filaOT["idMaster"].'","editable":"1","tipoFila":"3","situacionOT":"'.$filaOT["idEstado"].
					'","colorFondo":"FF0000","comentariosAdicionalesLlamado":"'.cv($fRegistroLlamadoTalento["comentariosAdicionales"]).'","totalTalentos":"'.
					$totalLlamados.'"'.$arrValoresTalentos.'}';
					
					if($arrRegistros=="")
						$arrRegistros=$o;
					else
						$arrRegistros.=",".$o;
					$numReg++;
	
				}
			}
		}
		
		$cadObj='{"registros":['.$arrRegistros.']}';
		$oRegistro=json_decode($cadObj);
		
		
		
		foreach($oRegistro->registros as $r)
		{
			$consulta="SELECT * FROM _628_tablaDinamica WHERE idReferencia=".$r->idRegistro;
			$fProduccion=$con->obtenerPrimeraFilaAsoc($consulta);
			
			$consulta="SELECT * FROM _602_llamadosTalentos WHERE idOrdenTrabajo=".$r->idRegistro;
			$fRegistroLlamadoTalento=$con->obtenerPrimeraFilaAsoc($consulta);
			$comentarios=$fRegistroLlamadoTalento["comentariosAdicionales"];
			$comentarios=str_replace("<br />","\n",$comentarios);
			$hInicio=strtotime($fProduccion["horaProduccion"]);
			$hFin=strtotime($fProduccion["horaEstimadaTermino"]);
			$lblHorario=date("H:i",$hInicio)." A ".date("H:i",$hFin);
			if($fProduccion["procesoProduccion"]==2)
			{
				$lblHorario.="\nGRABADO";
				
			}
			else
			{
				$lblHorario.="\nVIVO";
			}
			$altoTotal=1;	
			$idTalento="";	
			$listaTalento="";
			
			$arrTalentos=array();
			
			for($nTalento=1;$nTalento<=$maxLlamado;$nTalento++)
			{
				
				eval('$idTalento=isset($r->talento_'.$nTalento.')?$idTalento=$r->talento_'.$nTalento.":-1;");
				
				
				if(($idTalento!="")&&($idTalento!="-1"))
				{
					array_push($arrTalentos,$idTalento);
					
				}
			}
			
			$lblTalento="";
			$totalTalentos=sizeof($arrTalentos);
			for($nTalento=1;$nTalento<=$totalTalentos;$nTalento++)
			{
				$texto="";
				if($nTalento>1)
				{
					$texto=",";
				}
				
				if($lblTalento=="")
					$lblTalento=trim($arrTalentos[$nTalento-1]);
				else
					$lblTalento.=$texto.trim($arrTalentos[$nTalento-1]);
					
				
			
			}
			$master="";
			$consulta="SELECT nombre FROM _593_tablaDinamica WHERE id__593_tablaDinamica=".$r->master;
			$master=$con->obtenerValor($consulta);
			$arrDatosFila=array();
			$arrDatosFila["A"]=formatearCampoMultilinea($r->programa);
			$arrDatosFila["B"]=$lblHorario;
			$arrDatosFila["C"]=$master;
			$arrDatosFila["D"]=$lblTalento;
			$arrDatosFila["E"]=formatearCampoMultilinea($comentarios);
			$arrDatosFila["IO"]=$r->idRegistro;
			array_push($arrFilasReporte,$arrDatosFila);			
			
		}
		
		
	}
	return $arrFilasReporte;
}


function esUsuarioResponsableTalentos()
{
	if((existeRol("'199_0'"))||(existeRol("'200_0'")))
	{
		return 1;
	}
	return 0;
}

function esUsuarioAccesoFormatosInventario()
{
	global $con;
	if(existeRol("'201_0'"))
	{
		return 1;
	}
	
	$consulta="SELECT count(*) FROM _657_tablaDinamica WHERE designacionHardware=".$_SESSION["idUsr"]." and asignacionHardware is not null";
	$numReg=$con->obtenerValor($consulta);
	if($numReg>0)
		return 1;
	return 0;
}
?>