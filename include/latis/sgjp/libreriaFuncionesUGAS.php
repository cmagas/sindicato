<?php 
include_once("latis/conexionBD.php");
include_once("latis/utiles.php");

function registrarActivacionImputadoCarpetaJudicial($idFormulario,$idRegistro)
{
	global $con;
	$consulta="SELECT imputado FROM _523_tablaDinamica WHERE id__523_tablaDinamica=".$idRegistro;
	$lImputados=$con->obtenerListaValores($consulta);
	$arrImputados=explode(",",$lImputados);
	
	$cAdministrativa=obtenerCarpetaAdministrativaProceso($idFormulario,$idRegistro);
	foreach($arrImputados as $i)
	{
		activarImputadoCarpetaJudicial($idFormulario,$idRegistro,$i,$cAdministrativa);
	}
	return true;
}

function activarImputadoCarpetaJudicial($idFormulario,$idRegistro,$idImputado,$cAdministrativa)
{
	global $con;
	//
	
	$query="SELECT idActividad FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$cAdministrativa."'";
	$idActividad=$con->obtenerValor($query);
	
	$x=0;
	$consulta[$x]="begin";
	$x++;
	$consulta[$x]="INSERT INTO 7005_bitacoraCambiosFigurasJuridicas(idActividad,idParticipante,idFiguraJuridica,situacionAnterior,situacionActual,
				fechaCambio,responsableCambio,iFormulario,iReferencia)
				SELECT idActividad,idParticipante,idFiguraJuridica,situacion,1,'".date("Y-m-d H:i:s")."',".$_SESSION["idUsr"].
				",".$idFormulario.",".$idRegistro." FROM 7005_relacionFigurasJuridicasSolicitud WHERE
				idActividad=".$idActividad." AND idParticipante=".$idImputado;
	$x++;
	$consulta[$x]="UPDATE 7005_relacionFigurasJuridicasSolicitud SET situacion=1 WHERE idActividad=".$idActividad." AND idParticipante=".$idImputado;
	$x++;
	$consulta[$x]="commit";
	$x++;
	
	return $con->ejecutarBloque($consulta);
	
	
}

function desActivarImputadoCarpetaJudicial($idFormulario,$idRegistro,$idImputado,$cAdministrativa)
{
	global $con;

	
	$query="SELECT idActividad FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$cAdministrativa."'";
	$idActividad=$con->obtenerValor($query);
	
	$x=0;
	$consulta[$x]="begin";
	$x++;
	$consulta[$x]="INSERT INTO 7005_bitacoraCambiosFigurasJuridicas(idActividad,idParticipante,idFiguraJuridica,situacionAnterior,situacionActual,
				fechaCambio,responsableCambio,iFormulario,iReferencia)
				SELECT idActividad,idParticipante,idFiguraJuridica,situacion,3,'".date("Y-m-d H:i:s")."',".$_SESSION["idUsr"].
				",".$idFormulario.",".$idRegistro." FROM 7005_relacionFigurasJuridicasSolicitud WHERE
				idActividad=".$idActividad." AND idParticipante=".$idImputado;
	$x++;
	$consulta[$x]="UPDATE 7005_relacionFigurasJuridicasSolicitud SET situacion=1 WHERE idActividad=".$idActividad." AND idParticipante=".$idImputado;
	$x++;
	$consulta[$x]="commit";
	$x++;
	
	return $con->ejecutarBloque($consulta);
	
	
}

function generarFolioCarpetaUnidadEjecucionIncompetencia($idFormulario,$idRegistro)
{
	global $con;	
	
	$anio=date("Y");
	
	$query="SELECT carpetaBase,carpetaEjecucion,idActividad,codigoInstitucion FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$fDatosCarpeta=$con->obtenerPrimeraFila($query);
	if($fDatosCarpeta[1]=="N/E")
		$fDatosCarpeta[1]="";
		
	$cAdministrativaBase=$fDatosCarpeta[0];
	$carpetaEnjuiciamiento=$fDatosCarpeta[1];
	if($carpetaEnjuiciamiento!="")
		return true;
	
	$carpetaInvestigacion="";
	if($cAdministrativaBase!="")
	{
		$consulta="SELECT unidadGestion,carpetaInvestigacion FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$cAdministrativaBase."'";	
		$fCarpetaBase=$con->obtenerPrimeraFila($consulta);
		$carpetaInvestigacion=$fCarpetaBase[1];
	}
	$consulta="SELECT id__17_tablaDinamica FROM _17_tablaDinamica WHERE claveUnidad='".$fDatosCarpeta[3]."'";
	$idUnidadGestion=$con->obtenerValor($consulta);
	
	$carpetaAdministrativa=obtenerSiguienteCarpetaAdministrativa($idUnidadGestion,$anio,6,$idFormulario,$idRegistro);
		
	$query=" SELECT lj.clave FROM _26_tablaDinamica lj,_26_tipoJuez tj WHERE lj.idReferencia=".$idUnidadGestion." AND 
				tj.idPadre =lj.id__26_tablaDinamica AND tj.idOpcion=3  ORDER BY usuarioJuez";
	$listaJueces=$con->obtenerListaValores($query,"'");
	if($listaJueces=="")
		$listaJueces=-1;
	$idJuezEjecucion=obtenerSiguienteJuez(10,$listaJueces,-1);
	$fechaCreacionCarpeta=date("Y-m-d H:i:s");
	$idActividadEjecucion=generarIDActividad($idFormulario,$idRegistro);
	
	$cveUnidadGestion=$fDatosCarpeta[3];
	
	$consulta=array();
	$x=0;
	$consulta[$x]="begin";
	$x++;
	
	$consulta[$x]="INSERT INTO 7006_carpetasAdministrativas(carpetaAdministrativa,fechaCreacion,responsableCreacion,idFormulario,
				idRegistro,unidadGestion,etapaProcesalActual,idActividad,carpetaAdministrativaBase,idJuezTitular,
				tipoCarpetaAdministrativa,situacion,carpetaInvestigacion,llaveCarpetaInvestigacion,unidadGestionOriginal) 
				VALUES('".$carpetaAdministrativa."','".$fechaCreacionCarpeta."',".$_SESSION["idUsr"].",".$idFormulario.",'".
				$idRegistro."','".$cveUnidadGestion."',6,".$idActividadEjecucion.",'".$cAdministrativaBase.
				"',".$idJuezEjecucion.",6,1,(SELECT UPPER('".$carpetaInvestigacion."')),'".cv(generarLlaveCarpetaInvestigacion($carpetaInvestigacion)).
				"','".$cveUnidadGestion."')";
	$x++;
	$consulta[$x]="update _".$idFormulario."_tablaDinamica set carpetaEjecucion='".$carpetaAdministrativa."',idActividad=".$idActividadEjecucion.
				" where id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$x++;
			
	
	$consulta[$x]="commit";
	$x++;
	
	if($con->ejecutarBloque($consulta))
	{
		$query="SELECT idDocumento FROM 9074_documentosRegistrosProceso WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro;
		$rDocumentos=$con->obtenerFilas($query);
		while($fDocumento=mysql_fetch_row($rDocumentos))
		{
			registrarDocumentoCarpetaAdministrativa($carpetaAdministrativa,$fDocumento[0],$idFormulario,$idRegistro);	
		}
		return true;

	}
		
	
	return false;
	
}

function registrarProcesoLibroGobierno($idFormulario,$idRegistro,$tipoLibro,$fechaRegistro="",$unidadGestion="")
{
	global $con;

	$fRegistro=date("Y-m-d H:i:s");
	if($fechaRegistro!="")
		$fRegistro=$fechaRegistro;
	$fRegistro=strtotime($fRegistro);

	$consulta="SELECT COUNT(*) FROM 7044_procesosLibrosGobierno WHERE tipoLibro=".$tipoLibro.
			" AND iFormulario=".$idFormulario." AND iRegistro=".$idRegistro;

	$nReg=$con->obtenerValor($consulta);
	if($nReg>0)
		return true;
	else
	{
		if($unidadGestion=="")
		{
			$consulta="SELECT codigoInstitucion FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
			$unidadGestion=$con->obtenerValor($consulta);
		}
		$arrParametros["unidadGestion"]=$unidadGestion;
		$arrParametros["tipoLibro"]=$tipoLibro;
		$arrParametros["anio"]=date("Y",$fRegistro);
		$arrParametros["fechaRegistro"]=date("Y-m-d H:i:s",$fRegistro);
		$arrParametros["idFormulario"]=$idFormulario;
		$arrParametros["idRegistro"]=$idRegistro;		
		$arrParametros["complementario1"]="";
		$arrParametros["complementario2"]="";
		
		return registrarRegistroLibroGobierno($arrParametros);
	}
}

function registrarRegistroLibroGobierno($arrParametros)
{
	global $con;
	
	$x=0;
	$consulta[$x]="begin";
	$x++;

	$query="SELECT folioActual FROM 7045_foliosLibroGobierno WHERE tipoLibro=".$arrParametros["tipoLibro"].
			" AND unidadGestion='".$arrParametros["unidadGestion"]."' and anio=".$arrParametros["anio"];
	$folioActual=$con->obtenerValor($query);
	
	if($folioActual=="")
	{
		$folioActual=1;
		
		$consulta[$x]="INSERT INTO 7045_foliosLibroGobierno(tipoLibro,unidadGestion,folioActual,anio) VALUES(".
						$arrParametros["tipoLibro"].",'".$arrParametros["unidadGestion"]."',1,".$arrParametros["anio"].")";
				
	}
	else
	{
		$folioActual++;		
		$consulta[$x]="UPDATE 7045_foliosLibroGobierno SET folioActual=".$folioActual." WHERE tipoLibro=".$arrParametros["tipoLibro"].
			" AND unidadGestion='".$arrParametros["unidadGestion"]."'";
	}
	$x++;
	
	$consulta[$x]="INSERT INTO 7044_procesosLibrosGobierno(tipoLibro,anio,fechaRegistro,noRegistro,iFormulario,iRegistro,unidadGestion,
				complementario1,complementario2,situacion)
					VALUES(".$arrParametros["tipoLibro"].",".$arrParametros["anio"].",'".$arrParametros["fechaRegistro"]."',".$folioActual.
					",".$arrParametros["idFormulario"].",".$arrParametros["idRegistro"].",'".$arrParametros["unidadGestion"].
					"','".cv($arrParametros["complementario1"])."','".cv($arrParametros["complementario2"])."',1)";
	$x++;
	$consulta[$x]="commit";
	$x++;
	
	return $con->ejecutarBloque($consulta);
	
}

function registrarDocumentosFormulario($idFormulario,$idRegistro,$nombreCampo,$nombreDocumento,$tipoDocumento,$iFormulario,$iRegistro)
{
	global $con;
	$consulta="SELECT ".$nombreCampo." FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$idDocumento=$con->obtenerValor($consulta);
	
	if($idDocumento!="")
	{
		convertirDocumentoUsuarioDocumentoResultadoProceso($idDocumento,$iFormulario,$iRegistro,$nombreDocumento,$tipoDocumento);
	}
	
	return true;
	
}

function registrarDocumentoRegistroSalasApelacion($idFormulario,$idRegistro)
{
	global $con;
	$consulta="SELECT idReferencia,documentoSala FROM _500_tablaDinamica WHERE id__500_tablaDinamica=".$idRegistro;
	$fRegistro=$con->obtenerPrimeraFila($consulta);
	if($fRegistro[1]!="")
		convertirDocumentoUsuarioDocumentoResultadoProceso($fRegistro[1],497,$fRegistro[0],"Resolucion de sala",52);
	
}

function asignarSiguienteJuezSecuencial($arrParametros)
{
	global $con;

	$habilitarDebug=false;
	$oDatosAudiencia=array();
	$validarFechaMaxima=true;	
	$objParametros=json_decode(bD($arrParametros));
	
	if((strtotime($objParametros->fechaMaxima))<(strtotime($objParametros->fechaMinima)))
	{
		$validarFechaMaxima=false;
		$objParametros->criterioProgramacion=1;
	}
	
	$fechaAudiencia=$objParametros->fechaInicialBusqueda;
		
	$tipoJuez="";
	$consulta="SELECT * FROM _4_tablaDinamica WHERE id__4_tablaDinamica=".$objParametros->tipoAudiencia;
	$fDatosAudiencia=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$duracionAudiencia=$fDatosAudiencia["promedioDuracion"];
	
	switch($objParametros->criterioProgramacion)
	{
		case 2://Lo menos pronto posible
			if($fDatosAudiencia["agendaDiaNoHabil"]==0)
				$fechaAudiencia=obtenerAnteriorDiaHabil($fechaAudiencia);
					
		break;
		case 3:	//Lo medianamente posible
			if($fDatosAudiencia["agendaDiaNoHabil"]==0)
				$fechaAudiencia=obtenerSiguienteDiaHabil($fechaAudiencia);
		
		break;
	}
	
	$consulta="SELECT tipoJuez,titulo,funcionAplicacion FROM _4_gridJuecesRequeridos WHERE idReferencia=".$objParametros->tipoAudiencia;
	$rJuez=$con->obtenerFilas($consulta);
	while($fJuez=mysql_fetch_row($rJuez))
	{
		$tJuez=$fJuez[0];
		$considerarJuez=true;
		if($fJuez[2]!="")
		{
			$cache=NULL;
			$resultado=resolverExpresionCalculoPHP($fJuez[2],$objParametros,$cache);
			if(gettype($resultado)=="boolean")
			{
				if(!$resultado)
					$considerarJuez=false;
			}
			else
			{
				$resultado=removerComillasLimite($resultado);
				if($resultado==0)
					$considerarJuez=false;

				
			}
			
		}
		
		if($considerarJuez)
		{
			if($tipoJuez=="")
				$tipoJuez=$fJuez[0];
			else
				$tipoJuez.=",".$fJuez[0];
		}
	
	}
	
	$consulta="SELECT * FROM _567_tablaDinamica WHERE id__567_tablaDinamica=".$fDatosAudiencia["perfilProgramacionAudiencia"];
	$fDatosPerfil=$con->obtenerPrimeraFilaAsoc($consulta);
	if($habilitarDebug)
		echo "1<br>";
	$arrHorarios=array();
	$consulta="SELECT dia,horaInicio,horaFin FROM _567_gHorariosProgramacion WHERE idReferencia=".$fDatosAudiencia["perfilProgramacionAudiencia"];
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		$arrHorarios[$fila[0]]["horaInicio"]=$fila[1];
		$arrHorarios[$fila[0]]["horaFin"]=$fila[2];
	}

	$consulta="SELECT cveCategoria FROM _567_gCategoriasAudiencias WHERE id__567_gCategoriasAudiencias=".$objParametros->perfilCategoriaAudiencia;
	$cveCategoria=$con->obtenerValor($consulta);
	
	$oAsignacion["tipoRonda"]=$cveCategoria;
	$oAsignacion["noRonda"]="";
	$oAsignacion["idJuez"]="";
	
	$juecesGuardia=$objParametros->esGuardia==1;
	
	$encontrado=false;
	$listaJuecesIgnorar=-1;
	if($habilitarDebug)
		echo "2<br>";

	$consulta="SELECT usuarioJuez FROM _26_tablaDinamica j,_26_tipoJuez tj WHERE j.idReferencia=".$objParametros->idUnidadGestion."
						and tj.idPadre=j.id__26_tablaDinamica and tj.idOpcion in(".$tipoJuez.") and usuarioJuez not in(".$listaJuecesIgnorar.
						") order by j.clave";

	$listaJuecesUnidadGestion=$con->obtenerListaValores($consulta);
	if($listaJuecesUnidadGestion=="")
		$listaJuecesUnidadGestion=-1;
	
	
	if($juecesGuardia)
	{
		$consulta="SELECT j.usuarioJuez FROM _13_tablaDinamica t,_26_tablaDinamica j WHERE '".$fechaAudiencia."'>=fechaInicio AND '".
							$fechaAudiencia."'<=fechaFinalizacion and j.usuarioJuez in(".$listaJuecesUnidadGestion.
							") and t.idEstado=1 and j.usuarioJuez=t.usuarioJuez and j.idReferencia=".$objParametros->idUnidadGestion.
							"  and usuarioJuez not in(".$listaJuecesIgnorar.")order by j.clave";

		$listaJuecesGuardia=$con->obtenerListaValores($consulta);
		
		if($listaJuecesGuardia!="")
			$listaJuecesUnidadGestion=$listaJuecesGuardia;
	}
	if($habilitarDebug)
		echo "3<br>";
	$ciclos=0;
	
	$objJuez=NULL;
	$arrJuecesBloque=array();
	$arrJuecesIgnorar=array();
	if(!$juecesGuardia)
	{
		$nRonda=obtenerNoRondaAsignacion($objParametros->idUnidadGestion,$oAsignacion["tipoRonda"]);
		while(!$encontrado)
		{
			
			$aJueces=explode(",",$listaJuecesUnidadGestion);

			$arrJueces=array();
			$objJuez=NULL;
			foreach($aJueces as $idJuez)
			{
				if(($idJuez=="")||($idJuez==-1)||(isset($arrJuecesIgnorar[$idJuez])))
				{

					continue;
				}
				
				
				$fInicioTemp=strtotime(date("Y-m-d",strtotime($objParametros->fechaMinima)));
				$fMaximoTemp=strtotime(date("Y-m-d",strtotime($objParametros->fechaMaxima)));
				
				
				$totalDias=0;
				$totalDiasIncidencia=0;
				$totalDiasTramite=0;
				if($validarFechaMaxima)
				{
					while($fInicioTemp<=$fMaximoTemp)
					{
						if(!esJuezDisponibleIncidencia($idJuez,date("Y-m-d",$fInicioTemp)))
						{
							$totalDiasIncidencia++;
						}
						if($fDatosAudiencia["asignableJuezTramite"]==0)
						{
							if(esJuezTramite($idJuez,date("Y-m-d",$fInicioTemp)))
							{
								$totalDiasTramite++;
							}
						}
						
						$fInicioTemp=strtotime("+1 days",$fInicioTemp);
						$totalDias++;
					}
				}
				else
				{
					$totalDias=1;
				}
				$incidenciasPeriodo=$totalDiasIncidencia==$totalDias?1:0;
				$incidenciasJuezTamite=$totalDiasTramite==$totalDias?1:0;
				
				if($incidenciasPeriodo>0)
				{
					continue;
				}
				
				if(($fDatosAudiencia["asignableJuezTramite"]==0)&&($incidenciasJuezTamite>0))
				{
					continue;
				}
				
				$arrJueces[$idJuez]["incidenciasPeriodo"]=$incidenciasPeriodo;
				$arrJueces[$idJuez]["incidenciasJuezTamite"]=$incidenciasJuezTamite;
				$asignacionesRonda= obtenerAsignacionesRonda($idJuez,$oAsignacion["tipoRonda"],$objParametros->idUnidadGestion,$nRonda);
				$nAdeudos=obtenerAsignacionesPendientes($idJuez,$oAsignacion["tipoRonda"],$objParametros->idUnidadGestion,$nRonda);
				$nPagadas=obtenerAsignacionesPagadasRonda($idJuez,$oAsignacion["tipoRonda"],$objParametros->idUnidadGestion,$nRonda);
				$arrJueces[$idJuez]["nAsignaciones"]=$asignacionesRonda;
				$arrJueces[$idJuez]["nAdeudos"]=$nAdeudos;
				$arrJueces[$idJuez]["nPagadas"]=$nPagadas;
				
			}
			
			
			//varDUmp($arrJueces);
			if($habilitarDebug)
				echo "4<br>";
			if($fDatosPerfil["consideraAdeudos"]==1)
			{
				foreach($arrJueces as $idJuez=>$resto)
				{
					if	(
							($resto["nAdeudos"]>0)&&
							(($resto["nPagadas"]<$fDatosPerfil["limitePagoRondas"])||($fDatosPerfil["limitePagoRondas"]==0))&&
							(($resto["esJuezTramite"]+$resto["esJuezIncidencia"])==0)
						)
					{
						$consulta="SELECT clave FROM _26_tablaDinamica WHERE idReferencia=".$objParametros->idUnidadGestion." AND usuarioJuez=".$idJuez;
						$clave=$con->obtenerValor($consulta);
						
						$cadJuez='{"tipoJuez":"'.$tipoJuez.'","participacion":"'.cv($fJuez[1]).'","serieRonda":"'.$oAsignacion["tipoRonda"].
								'","noRonda":"'.$nRonda.'","pagoAdeudo":"1","idJuez":"'.$idJuez.
								'","nombreJuez":"'.cv("[".$clave."] ".obtenerNombreUsuario($idJuez)).'","arrJuecesBloquear":"'.bE($arrJuecesBloquear).'"}';
						$objJuez=json_decode($cadJuez);
						break;
						
						
					}
				}
			}
		
			if($objJuez==NULL)
			{
				foreach($arrJueces as $idJuez=>$resto)
				{
					if($resto["nAsignaciones"]==0)
					{
						$consulta="SELECT clave FROM _26_tablaDinamica WHERE idReferencia=".$objParametros->idUnidadGestion." AND usuarioJuez=".$idJuez;
						$clave=$con->obtenerValor($consulta);
						$cadJuez= '{"tipoJuez":"'.$tipoJuez.'","participacion":"'.cv($fJuez[1]).'","serieRonda":"'.$oAsignacion["tipoRonda"].
						'","noRonda":"'.$nRonda.'","pagoAdeudo":"0","idJuez":"'.$idJuez.
						'","nombreJuez":"'.cv("[".$clave."] ".obtenerNombreUsuario($idJuez)).'","arrJuecesBloquear":""}';
						$objJuez=json_decode($cadJuez);
						break;
						/*if(($resto["esJuezTramite"]+$resto["esJuezIncidencia"])==0)
						{
							
						}
						else
						{
							$oBloqueo="";
							if($resto["esJuezTramite"]==1)
							{
								$oBloqueo='{"idJuez":"'.$idJuez.'","tipoBloqueo":"2","serieRonda":"'.$oAsignacion["tipoRonda"].
										'","noRonda":"'.$nRonda.'"}';
							}
							else
							{
								if($resto["esJuezIncidencia"]==1)
								{
									$oBloqueo='{"idJuez":"'.$idJuez.'","tipoBloqueo":"3","serieRonda":"'.$oAsignacion["tipoRonda"].
											'","noRonda":"'.$nRonda.'"}';
								}
								else
								{
									$oBloqueo='{"idJuez":"'.$idJuez.'","tipoBloqueo":"5","serieRonda":"'.$oAsignacion["tipoRonda"].
											'","noRonda":"'.$nRonda.'","comentariosAdicionales":"'.cv($arrJuecesExcusa[$idJuez]).'"}';
								}
							}
							if($arrJuecesBloquear=="")
								$arrJuecesBloquear=$oBloqueo;
							else
								$arrJuecesBloquear.=",".$oBloqueo;
								
								
						}	*/
					}
					
				}
			}
			if($habilitarDebug)
				echo "5<br>";
			
			if($objJuez!=NULL)
			{
				$arrJuecesIgnorar[$objJuez->idJuez]=1;
				
				$fechaInicial=$fechaAudiencia;
				$esFechaOriginal=true;
				$listaJuecesIgnorar.=",".$objJuez->idJuez;
				$fechaEncontrada=false;
				while(!$fechaEncontrada)
				{
					$fAux=strtotime($fechaInicial);
					$salaEncontrada=false;
					$listaSalaIng=-1;
					while(!$salaEncontrada)
					{
						if($habilitarDebug)
							echo "6<br>";
						$idSalaAsignar=obtenerSalaAsignacionAudiencia(date("Y-m-d"),$objParametros,$listaSalaIng);
						if($habilitarDebug)
							echo "7<br>";
						if($idSalaAsignar!=-1)
						{
							$listaSalaIng.=",".$idSalaAsignar;
							$arrHorarioSala=generarHorarioSala($idSalaAsignar,$fDatosAudiencia["perfilProgramacionAudiencia"],$fechaInicial,$objJuez->idJuez,$objParametros->idUnidadGestion,"",date("H:i:s",$fAux));
							
							
							
							if($habilitarDebug)
								echo "8<br>";
							foreach($arrHorarioSala as $h)
							{
								$incidenciaHorario=0;
								$horaInicioAudiencia="";
								$horaFinAudiencia="";
								if($h["tiempoMinutos"]>=$duracionAudiencia)
								{
									$incidenciaHorario=1;	
									$horaInicioAudiencia=$h["hInicial"];
									$horaFinAudiencia=date("Y-m-d H:i:s",strtotime("+".$duracionAudiencia." minutes",strtotime($h["hInicial"])));
									if(!$esFechaOriginal)
									{
										$dia=date("w",strtotime($fechaInicial));
										$fechaLimite=date("Y-m-d",strtotime($fechaInicial));
										$fechaLimite.=" ".$arrHorarios[$dia]["horaFin"];
										$fLimite=strtotime($fechaLimite);
										if(strtotime($horaInicioAudiencia)<=$fechaLimite)
										{
											$incidenciaHorario=2;
										}
		
									}
									
									
									if(($incidenciaHorario==1)&&($validarFechaMaxima))
									{
										if(strtotime($horaInicioAudiencia)>strtotime($objParametros->fechaMaxima))
										{
											$incidenciaHorario=3;
										}
									}
									if($habilitarDebug)
										echo "9<br>";
									if(($incidenciaHorario==1)&&($fDatosAudiencia["asignableJuezTramite"]==0))
									{
										if(esJuezTramite($idJuez,date("Y-m-d",strtotime($horaInicioAudiencia))))
										{
											$incidenciaHorario=4;
										}
									}
									if($habilitarDebug)
										echo "10<br>";
									if($incidenciaHorario==1)
									{
										if(!esJuezDisponibleIncidenciaV2($objJuez->idJuez,date("Y-m-d",strtotime($horaInicioAudiencia)),$horaInicioAudiencia,$horaFinAudiencia))
										{
											$incidenciaHorario=5;
										}
									}
									
									if($habilitarDebug)
										echo "11<br>";
								}
								
								
								
								if($incidenciaHorario==1)
								{
									$consulta="SELECT idReferencia FROM _15_tablaDinamica WHERE id__15_tablaDinamica=".$idSalaAsignar;
									$idEdificio=$con->obtenerValor($consulta);
									$consulta="SELECT titulo FROM _4_gridJuecesRequeridos WHERE idReferencia=".$objParametros->tipoAudiencia.
											" AND tipoJuez=".$objJuez->tipoJuez;
									$titulo=$con->obtenerValor($consulta);
									
									$consulta="SELECT idRegistroEvento FROM 7000_eventosAudiencia WHERE idFormulario=".$objParametros->idFormulario.
											" AND idRegistroSolicitud=".$objParametros->idRegistro;
									$idRegistroEvento=$con->obtenerValor($consulta);
									if($habilitarDebug)
										echo "12<br>";
									$oDatosAudiencia=array();
									$oDatosAudiencia["idRegistroEvento"]=$idRegistroEvento==""?-1:$idRegistroEvento;
									$oDatosAudiencia["idEdificio"]=$idEdificio;
									$oDatosAudiencia["idUnidadGestion"]=$objParametros->idUnidadGestion;
									$oDatosAudiencia["idSala"]=$idSalaAsignar;
									$oDatosAudiencia["fecha"]=date("Y-m-d",$fAux);
									$oDatosAudiencia["horaInicio"]=$horaInicioAudiencia;
									$oDatosAudiencia["horaFin"]=$horaFinAudiencia;
									$oDatosAudiencia["jueces"]=array();
									
									$oJuezAsignado=array();
									$oJuezAsignado["idUsuario"]=$objJuez->idJuez;
									$oJuezAsignado["tipoJuez"]=$objJuez->tipoJuez;
									$oJuezAsignado["titulo"]=$titulo;
									$oJuezAsignado["noRonda"]=$objJuez->noRonda;
									$oJuezAsignado["tipoRonda"]=$objJuez->serieRonda;
									$oJuezAsignado["objJuez"]=$objJuez;
									array_push($oDatosAudiencia["jueces"],$oJuezAsignado);
									$encontrado=true;
									$salaEncontrada=true;
									$fechaEncontrada=true;
									
									return $oDatosAudiencia;
								}
								
							}
						}
						else
						{
							$salaEncontrada=true;
					
						}
					}
					if($habilitarDebug)
						echo "13<br>";
					$fechaInicial=obteneSiguienteFechaAsignacion($fechaInicial,$objParametros);
					if($habilitarDebug)
						echo "14<br>";
					$esFechaOriginal=false;
					if(($fechaInicial=="")||((strtotime($fechaInicial)>strtotime($objParametros->fechaMaxima))&&($validarFechaMaxima)))
					{
						$fechaEncontrada=true;
					}
					
				}
				
				
				
			}
			else
			{
				$ciclos++;
				$nRonda++;
				if($ciclos>5)
				{
					if($habilitarDebug)
						echo "15<br>";
					$nRonda=obtenerNoRondaAsignacion($objParametros->idUnidadGestion,$oAsignacion["tipoRonda"])-1;
					if($habilitarDebug)
						echo "16<br>";
					$arrJuecesIgnorar=array();
					$validarFechaMaxima=false;
				}
			}
			
			
			
			/*$ciclos++;
			if($ciclos>2)
				return;*/
		}
	}
	else
	{
		/*$oAsignacion["tipoRonda"]="G";
		$arrJuecesBloquear="";
		
		$idPeriodoGuardia=obtenerIdPeriodoGuardia($oDatosParametros["idUnidadGestion"]);	
		
		$nRonda=obtenerNoRondaAsignacionGuardia($oDatosParametros["idUnidadGestion"],$oAsignacion["tipoRonda"],$idPeriodoGuardia);
		while($ciclos<20)
		{
			$consulta="SELECT j.usuarioJuez FROM _13_tablaDinamica t,_26_tablaDinamica j WHERE '".$oDatosParametros["fechaBaseSolicitud"]."'>=fechaInicio AND '".
						$oDatosParametros["fechaBaseSolicitud"]."'<=fechaFinalizacion and j.usuarioJuez in(".$listaJuecesUnidadGestion.
						") and t.idEstado=1 and j.usuarioJuez=t.usuarioJuez and j.idReferencia=".$oDatosParametros["idUnidadGestion"].
						" order by j.clave";

			$listaJuecesGuardia=$con->obtenerListaValores($consulta);
	
			
			$oDatosParametros["validaIncidenciaJuez"]=false;
			$oDatosParametros["validaJuezTramite"]=false;
			if($obj->idFormulario==185)
			{
				//$oDatosParametros["validaJuezTramite"]=true;
					//			$oDatosParametros["validaIncidenciaJuez"]=true;

			}
			if($listaJuecesGuardia!="")
				$listaJuecesUnidadGestion=$listaJuecesGuardia;
				
				
			$aJueces=explode(",",$listaJuecesUnidadGestion);

			foreach($aJueces as $idJuez)
			{
				if(($idJuez=="")||($idJuez==-1))
					continue;
				$asignacionesRonda= obtenerAsignacionesRonda($idJuez,$oAsignacion["tipoRonda"],$oDatosParametros["idUnidadGestion"],$nRonda);
				//$nAdeudos=obtenerAsignacionesPendientes($idJuez,$oAsignacion["tipoRonda"],$oDatosParametros["idUnidadGestion"],$nRonda);
				//$nPagadas=obtenerAsignacionesPagadasRonda($idJuez,$oAsignacion["tipoRonda"],$oDatosParametros["idUnidadGestion"],$nRonda);
				$arrJueces[$idJuez]["nAsignaciones"]=$asignacionesRonda;
				$arrJueces[$idJuez]["nAdeudos"]=0;
				$arrJueces[$idJuez]["nPagadas"]=0;
				if($oDatosParametros["validaJuezTramite"])	
					$arrJueces[$idJuez]["esJuezTramite"]=esJuezTramite($idJuez,$fechaAudiencia)?1:0;
				else
					$arrJueces[$idJuez]["esJuezTramite"]=0;
				if($oDatosParametros["validaIncidenciaJuez"])	
					$arrJueces[$idJuez]["esJuezIncidencia"]=esJuezDisponibleIncidencia($idJuez,$fechaAudiencia)?0:1;
				else
					$arrJueces[$idJuez]["esJuezIncidencia"]=0;
					
				
				$arrJueces[$idJuez]["horasDia"]=0;
				
				
				$arrJueces[$idJuez]["esJuezExcusa"]=isset($arrJuecesExcusa[$idJuez])?1:0;
				
			}
			

			
			foreach($arrJueces as $idJuez=>$resto)
			{
				if(($resto["nAsignaciones"]==0)&&($resto["esJuezExcusa"]==0))
				{
					$consulta="SELECT clave FROM _26_tablaDinamica WHERE idReferencia=".$oDatosParametros["idUnidadGestion"]." AND usuarioJuez=".$idJuez;
					$clave=$con->obtenerValor($consulta);
					echo '1|{"tipoJuez":"'.$tipoJuez.'","participacion":"'.cv($fJuez[1]).'","serieRonda":"'.$oAsignacion["tipoRonda"].
					'","noRonda":"'.$nRonda.'","pagoAdeudo":"0","idJuez":"'.$idJuez.
					'","nombreJuez":"'.cv("[".$clave."] ".obtenerNombreUsuario($idJuez)).'","arrJuecesBloquear":"'.bE($arrJuecesBloquear).'"}';
					return;
				}
				else
				{
					if($resto["nAsignaciones"]==0)
					{
						$oBloqueo='{"idJuez":"'.$idJuez.'","tipoBloqueo":"5","serieRonda":"'.$oAsignacion["tipoRonda"].
									'","noRonda":"'.$nRonda.'","comentariosAdicionales":"'.cv($arrJuecesExcusa[$idJuez]).'"}';
						if($arrJuecesBloquear=="")
							$arrJuecesBloquear=$oBloqueo;
						else
							$arrJuecesBloquear.=",".$oBloqueo;
					}
				}
				
			}
			$nRonda++;
			$ciclos++;
			
			
		}*/
	}
	
	
	return $NULL;

}

function obteneSiguienteFechaAsignacion($fechaActual,$objParametros)
{
	global $con;
	
	$ajustarHorarioFecha=true;
	$fechaActualSistema=strtotime(date("Y-m-d H:i"));
	$consulta="SELECT * FROM _4_tablaDinamica WHERE id__4_tablaDinamica=".$objParametros->tipoAudiencia;
	$fDatosAudiencia=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$consulta="SELECT * FROM _567_tablaDinamica WHERE id__567_tablaDinamica=".$fDatosAudiencia["perfilProgramacionAudiencia"];
	$fDatosPerfil=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$arrHorarios=array();
	$consulta="SELECT dia,horaInicio,horaFin FROM _567_gHorariosProgramacion WHERE idReferencia=".$fDatosAudiencia["perfilProgramacionAudiencia"];
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		$arrHorarios[$fila[0]]["horaInicio"]=$fila[1];
		$arrHorarios[$fila[0]]["horaFin"]=$fila[2];
	}
	
	switch($objParametros->criterioProgramacion)
	{
		case 1://Lo más pronto posible
			$fechaSiguiente=date("Y-m-d",strtotime("+1 days",strtotime($fechaActual)));

			if($fDatosAudiencia["agendaDiaNoHabil"]==0)
			{
				$fechaSiguiente=obtenerSiguienteDiaHabil($fechaSiguiente);
			}
		break;
		case 2://Lo menos pronto posible
			$fechaSiguiente=date("Y-m-d",strtotime("-1 days",strtotime($fechaActual)));
			if($fDatosAudiencia["agendaDiaNoHabil"]==0)
			{
				$fechaSiguiente=obtenerAnteriorDiaHabil($fechaSiguiente);
			}
			
			if(date("Y-m-d",strtotime($fechaSiguiente))==date("Y-m-d",strtotime($objParametros->fechaMinima)))
			{
				$fechaSiguiente=$objParametros->fechaMinima;
				$ajustarHorarioFecha=false;
			}
			
			if((strtotime($fechaSiguiente)<$fechaActualSistema)||(strtotime($fechaSiguiente)<strtotime($objParametros->fechaMinima)))
			{
				return "";
			}
			
		break;
		case 3://Lo medianamente posible
			
			if(strtotime(date("Y-m-d",strtotime($fechaActual)))<=strtotime(date("Y-m-d",strtotime($objParametros->fechaInicialBusqueda))))
			{
				$fechaSiguiente=date("Y-m-d",strtotime("-1 days",strtotime($fechaActual)));
				if($fDatosAudiencia["agendaDiaNoHabil"]==0)
				{
					$fechaSiguiente=obtenerAnteriorDiaHabil($fechaSiguiente);
				}
				
				if(date("Y-m-d",strtotime($fechaSiguiente))==date("Y-m-d",strtotime($objParametros->fechaMinima)))
				{
					$fechaSiguiente=$objParametros->fechaMinima;
					$ajustarHorarioFecha=false;
				}
				
				if((strtotime($fechaSiguiente)<$fechaActual)||(strtotime($fechaSiguiente)<strtotime($objParametros->fechaMinima)))
				{
					$fechaSiguiente=date("Y-m-d",strtotime("+1 days",strtotime($objParametros->fechaInicialBusqueda)));
					if($fDatosAudiencia["agendaDiaNoHabil"]==0)
					{
						$fechaSiguiente=obtenerSiguienteDiaHabil($fechaSiguiente);
					}
				}
			}
			else
			{
				$fechaSiguiente=date("Y-m-d",strtotime("+1 days",strtotime($fechaActual)));
				if($fDatosAudiencia["agendaDiaNoHabil"]==0)
				{
					$fechaSiguiente=obtenerSiguienteDiaHabil($fechaSiguiente);
				}
			}
		break;
	}
	
	if($ajustarHorarioFecha)
	{
		$dia=date("w",strtotime($fechaSiguiente));
		$fechaSiguiente=$fechaSiguiente." ".$arrHorarios[$dia]["horaInicio"];
	}
	return $fechaSiguiente;
}

function normalizarHorarioAsignacion($horaAudiencia)
{
	$fTemp=strtotime($horaAudiencia);
	$minutos=date("i",$fTemp);

	if($minutos<=15)
		$horaAudiencia=date("Y-m-d H:00:00",$fTemp);
	else
		if($minutos<=30)
			$horaAudiencia=date("Y-m-d H:30:00",$fTemp);
		else
		{
			if($minutos<=45)
				$horaAudiencia=date("Y-m-d H:45:00",$fTemp);
			else
				$horaAudiencia=date("Y-m-d H:00:00",strtotime("+1 hour",$fTemp));
		}
	return $horaAudiencia;
}

function generarHorarioSala($idSala,$perfilProgramacionAudiencia,$fechaMinima,$idJuez,$idUnidadGestion,$fechaMaxima="",$horaMinima="")
{
	global $con;
	
	$fechaAudiencia=date("Y-m-d",strtotime($fechaMinima));
	$arrHorariosDias=array();
	
	$consulta="SELECT * FROM _567_tablaDinamica WHERE id__567_tablaDinamica=".$perfilProgramacionAudiencia;
	$fDatosPerfil=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$consulta="SELECT dia,horaInicio,horaFin FROM _567_gHorariosProgramacion WHERE idReferencia=".$perfilProgramacionAudiencia;
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		$arrHorariosDias[$fila[0]]["horaInicio"]=$fila[1];
		$arrHorariosDias[$fila[0]]["horaFin"]=$fila[2];
	}
	
	$dia=date("w",strtotime($fechaMinima));
	
	$regHorario["hInicial"]=$fechaAudiencia." ".$arrHorariosDias[$dia]["horaInicio"];
	$fAuxFinal=strtotime("+1 days",strtotime($fechaAudiencia));
	$regHorario["hFinal"]=date("Y-m-d",$fAuxFinal)." 23:59:59";	
	$regHorario["tiempoMinutos"]=floor(obtenerDiferenciaMinutos($regHorario["hInicial"],$regHorario["hFinal"]));
	
	$arrHorarios=array();
	array_push($arrHorarios,$regHorario);	
	
	$arrEventosSala=array();
	$consulta="SELECT if(horaInicioReal is null,horaInicioEvento,horaInicioReal),if(horaTerminoReal is null,horaFinEvento,horaTerminoReal) FROM 7000_eventosAudiencia WHERE idSala=".$idSala.
			" AND fechaEvento='".$fechaAudiencia."' and situacion in(SELECT idSituacion FROM 7011_situacionEventosAudiencia 
			WHERE considerarDiponibilidad=1) ORDER BY horaInicioEvento";

	$resEvento=$con->obtenerFilas($consulta);			
	while($fEvento=mysql_fetch_row($resEvento))
	{
		$fEvento[1]=date("Y-m-d H:i",strtotime("+".$fDatosPerfil["minutosAudiencias"]." minutes",strtotime($fEvento[1])));
		
		$arrDatosEventos=explode(" ",$fEvento[1]);
		$arrDatosHora=explode(":",$arrDatosEventos[1]);
		
		if($arrDatosHora[1]<10)	
		{
			
			$arrDatosHora[1]=10;
		}
		else
			if($arrDatosHora[1]<50)	
			{

				$arrDatosHora[1]=(substr($arrDatosHora[1],0,1)+1)."0";
			}
			else
			{

				$arrDatosHora[0]=str_pad($arrDatosHora[0]+1,2,"0",STR_PAD_LEFT);
				$arrDatosHora[1]="00";	
			}
		$fEvento[1]=$arrDatosEventos[0]." ".$arrDatosHora[0].":".$arrDatosHora[1]; 
		array_push($arrEventosSala,$fEvento);
	}
	
	
	
	
	$aEventos=obtenerAudienciasProgramadasSede($idSala,$fechaAudiencia,$fechaAudiencia,-1);
	foreach($aEventos as $fEvento)	
	{
		array_push($arrEventosSala,$fEvento);
	}
	
	$arrEventosJueces=array();
	if($idJuez!=-1)
	{
		$eJuez=obtenerEventosJuez($idJuez,$fechaAudiencia,$fechaAudiencia);
		foreach($eJuez as $e)
		{
			$fRegIncidencia=array();
			$fRegIncidencia[0]=$e[6]!=""?$e[6]:$e[2];
			$fRegIncidencia[1]=$e[7]!=""?$e[7]:$e[3];
			array_push($arrEventosJueces,$fRegIncidencia);
		}
	}
	
	
	
	$arrHorariosBloquear=array();
	
	if($fechaMaxima!="")
	{
		$fRegIncidencia=array();
		$fRegIncidencia[0]=date("Y-m-d H:i:s",strtotime($fechaAudiencia." ".date("H:i:s",strtotime($fechaMaxima))));
		$fAuxFinal=strtotime("+1 days",strtotime($fechaAudiencia));
		$fRegIncidencia[1]=date("Y-m-d",$fAuxFinal)." 23:59:59";
		
		array_push($arrHorariosBloquear,$fRegIncidencia);
	}
	
	if($horaMinima!="")
	{
		$fRegIncidencia=array();
		$fRegIncidencia[0]=date("Y-m-d H:i:s",strtotime($fechaAudiencia." 00:00:01"));
		$fRegIncidencia[1]=date("Y-m-d H:i:s",strtotime($fechaAudiencia." ".$horaMinima));
		
		array_push($arrHorariosBloquear,$fRegIncidencia);
	}
	foreach($arrEventosJueces as $fRegIncidencia)
		array_push($arrHorariosBloquear,$fRegIncidencia);
	
	
	
	$consulta="SELECT idPadre FROM _25_chkUnidadesAplica WHERE idOpcion=".$idUnidadGestion;	
	$listaIncidencias=$con->obtenerValor($consulta);
	if($listaIncidencias=="")
		$listaIncidencias=-1;
			
	$consulta="SELECT fechaInicial,horaInicial,fechaFinal,horaFinal,id__25_tablaDinamica,tipoPeriodo FROM _25_tablaDinamica i,_25_Salas s 
				WHERE s.idReferencia=i.id__25_tablaDinamica AND s.nombreSala=".$idSala." AND '".$fechaAudiencia."'>=i.fechaInicial AND 
				'".$fechaAudiencia."'<=i.fechaFinal AND i.idEstado=2 and aplicaTodasUnidades=1
				union
				SELECT fechaInicial,horaInicial,fechaFinal,horaFinal,id__25_tablaDinamica,tipoPeriodo FROM _25_tablaDinamica i,_25_Salas s 
				WHERE s.idReferencia=i.id__25_tablaDinamica AND s.nombreSala=".$idSala." AND '".$fechaAudiencia."'>=i.fechaInicial AND 
				'".$fechaAudiencia."'<=i.fechaFinal AND i.idEstado=2 and aplicaTodasUnidades=0 and id__25_tablaDinamica in(".$listaIncidencias.")";
	
	$rIncidenciasSala=$con->obtenerFilas($consulta);
	while($fIncidencia=mysql_fetch_row($rIncidenciasSala))
	{
		$fRegIncidencia=array();
		
		$horaInicial="00:00:00";
		$horaFinal="23:59:59";
		
		if($fIncidencia[1]=="")
			$fIncidencia[1]=$horaInicial;
		
		if($fIncidencia[3]=="")
			$fIncidencia[3]=$horaFinal;
			
		
		
		if($fIncidencia[5]==2)
		{
			
			if($fIncidencia[0]==$fechaAudiencia)
			{
				$horaInicial=$fIncidencia[1];
			}
			
			
			if($fIncidencia[2]==$fechaAudiencia)
			{
				$horaFinal=$fIncidencia[3];
			}
			
		}
		else
		{
			$horaInicial=$fIncidencia[1];
			$horaFinal=$fIncidencia[3];
		}
		
		$fRegIncidencia[0]=$fechaAudiencia." ".$horaInicial;
		$fRegIncidencia[1]=$fechaAudiencia." ".$horaFinal;
		
		array_push($arrHorariosBloquear,$fRegIncidencia);
		
	}
	
	if(sizeof($arrHorariosBloquear)>0)
	{
		
		foreach($arrHorariosBloquear as $fEvento)	
		{
			array_push($arrEventosSala,$fEvento)	;
						
			
		}
		
		
	}
	
	usort($arrEventosSala, "ordenarPorFecha");
	
	foreach($arrEventosSala as $fEvento)	
	{
		$hInicioA=date("Y-m-d H:i:s",strtotime($fEvento[0]));
		$hFinA=date("Y-m-d H:i:s",strtotime($fEvento[1]));
		$arrAux=array();		
		for($pos=0;$pos<sizeof($arrHorarios);$pos++)
		{
			$horario=$arrHorarios[$pos];
			
			if(colisionaTiempo($hInicioA,$hFinA,$horario["hInicial"],$horario["hFinal"],true))
			{

				if(strtotime($hInicioA)<=strtotime($horario["hInicial"]))
				{
					
					if(strtotime($hFinA)<strtotime($horario["hFinal"]))
					{
						
						$nHorario=array();
						$nHorario["hInicial"]=date("Y-m-d H:i:s",strtotime("+0 minute",strtotime($hFinA)));
						$nHorario["hFinal"]=date("Y-m-d H:i:s",strtotime($horario["hFinal"]));
						$nHorario["tiempoMinutos"]=floor(obtenerDiferenciaMinutos($nHorario["hInicial"],$nHorario["hFinal"]));
						array_push($arrAux,$nHorario);
						
					}
						
				}
				else
				{
					$nHorario=array();
					$nHorario["hInicial"]=date("Y-m-d H:i:s",strtotime($horario["hInicial"]));
					$nHorario["hFinal"]=date("Y-m-d H:i:s",strtotime("-0 minute",strtotime($hInicioA)));
					$nHorario["tiempoMinutos"]=floor(obtenerDiferenciaMinutos($nHorario["hInicial"],$nHorario["hFinal"]));
					array_push($arrAux,$nHorario);
					
					if(strtotime($hFinA)<strtotime($horario["hFinal"]))
					{
						$nHorario=array();
						$nHorario["hInicial"]=date("Y-m-d H:i:s",strtotime("+0 minute",strtotime($hFinA)));
						$nHorario["hFinal"]=date("Y-m-d H:i:s",strtotime($horario["hFinal"]));
						$nHorario["tiempoMinutos"]=floor(obtenerDiferenciaMinutos($nHorario["hInicial"],$nHorario["hFinal"]));
						array_push($arrAux,$nHorario);
					}
					
					
				}
			}
			else
			{
				array_push($arrAux,$horario);	
			}
		}
		
		$arrHorarios=$arrAux;
		
	}
	
	return $arrHorarios;
}

function obtenerSalaAsignacionAudiencia($fechaAudiencia,$objParametros,$salasIgnorar)
{
	global $con;
	
	$tipoAudiencia=$objParametros->tipoAudiencia;
	
	$consulta="SELECT * FROM _4_tablaDinamica WHERE id__4_tablaDinamica=".$tipoAudiencia;
	$fDatosAudiencia=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$consulta="SELECT * FROM _567_tablaDinamica WHERE id__567_tablaDinamica=".$fDatosAudiencia["perfilProgramacionAudiencia"];
	$fDatosPerfil=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$consulta="SELECT id__15_tablaDinamica,s.nombreSala FROM _55_tablaDinamica us,_15_tablaDinamica s WHERE us.idReferencia=".
			$objParametros->idUnidadGestion." AND us.salasVinculadas=s.id__15_tablaDinamica AND s.perfilSala=2
			and s.id__15_tablaDinamica not in(".$salasIgnorar.") ORDER BY s.nombreSala";

	$listaSalas=$con->obtenerListaValores($consulta);
	
	if($listaSalas=="")
		return -1;
	$arrListaSalas=explode(",",$listaSalas);
	
	$universoTiempo=1440; //minutos al dia
	$arrSalas=array();
	switch($fDatosPerfil["criterioAsignacionSala"])
	{
		case 1: //Secuencial

			foreach($arrListaSalas as $idSala)
			{
				$consulta="SELECT MAX(fechaAsignacion) FROM 7000_eventosAudiencia WHERE idSala=".$idSala." AND situacion 
						IN (SELECT idSituacion FROM 7011_situacionEventosAudiencia WHERE considerarDiponibilidad=1)
						AND fechaAsignacion IS NOT NULL";
				$fechaAsignacion=$con->obtenerValor($consulta);
				if($fechaAsignacion=="")
					$fechaAsignacion="1970-01-01 00:00:01";
				$arrSalas[$idSala]["ultimaAsignacion"]=strtotime($fechaAsignacion);
				$arrSalas[$idSala]["ultimaAsignacionDate"]=$fechaAsignacion;
				
			}
				
			$cargaMenor=-1;
			foreach($arrSalas as $s=>$resto)
			{
				if($cargaMenor==-1)		
					$cargaMenor=$resto["ultimaAsignacion"];
				if($resto["ultimaAsignacion"]<$cargaMenor)
					$cargaMenor=$resto["ultimaAsignacion"];
			}
			
			$arrFinal=array();
			foreach($arrSalas as $s=>$resto)
			{
				if($resto["ultimaAsignacion"]==$cargaMenor)	
					array_push($arrFinal,$s);
			}
			
			$posFinal=0;
			if(sizeof($arrFinal)>1)
			{
				$posFinal=rand(0,sizeof($arrFinal)-1);
				
			}
		
			if(isset($arrFinal[$posFinal]))
				return $arrFinal[$posFinal];
			return -1;
			
		break;
		case 2: //Por uso de sala (Tiempo)
			
			foreach($arrListaSalas as $idSala)
			{
				$resultado=obtenerTotalTiempoAsignado($idSala,$fechaAudiencia,$fechaAudiencia);
				$arrSalas[$idSala]["totalTiempo"]=$resultado[0];
				$arrSalas[$idSala]["porcentajeOcupacion"]=($resultado[0]/$universoTiempo)*100;
			}
			
			
			$cargaMenor=-1;
			foreach($arrSalas as $s=>$resto)
			{
				if($cargaMenor==-1)		
					$cargaMenor=$resto["porcentajeOcupacion"];
				if($resto["porcentajeOcupacion"]<$cargaMenor)
					$cargaMenor=$resto["porcentajeOcupacion"];
			}
			
			$arrFinal=array();
			foreach($arrSalas as $s=>$resto)
			{
				if($resto["porcentajeOcupacion"]==$cargaMenor)	
					array_push($arrFinal,$s);
			}
			
			$posFinal=0;
			if(sizeof($arrFinal)>1)
			{
				$posFinal=rand(0,sizeof($arrFinal)-1);
				
			}
		
			if(isset($arrFinal[$posFinal]))
				return $arrFinal[$posFinal];
			return -1;
		
		break;
		case 3: //Por audiencias asignadas
			foreach($arrListaSalas as $idSala)
			{
				$resultado=obtenerTotalTiempoAsignado($idSala,$fechaAudiencia,$fechaAudiencia);
				$arrSalas[$idSala]["totalAudiencias"]=$resultado[2];
				
			}
			
			
			$cargaMenor=-1;
			foreach($arrSalas as $s=>$resto)
			{
				if($cargaMenor==-1)		
					$cargaMenor=$resto["totalAudiencias"];
				if($resto["totalAudiencias"]<$cargaMenor)
					$cargaMenor=$resto["totalAudiencias"];
			}
			
			$arrFinal=array();
			foreach($arrSalas as $s=>$resto)
			{
				if($resto["totalAudiencias"]==$cargaMenor)	
					array_push($arrFinal,$s);
			}
			
			$posFinal=0;
			if(sizeof($arrFinal)>1)
			{
				$posFinal=rand(0,sizeof($arrFinal)-1);
				
			}
			
			if(isset($arrFinal[$posFinal]))
				return $arrFinal[$posFinal];
			return -1;
		break;
	}
	
}

function generarProgramacionAudiencia($idFormulario,$idRegistro,$objParametrosConfiguracion=NULL)
{
	global $con;
	
	$consulta="SELECT carpetaAdministrativa,tipoAudiencia FROM _".$idFormulario."_tablaDinamica where id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$fDatosSolicitud=$con->obtenerPrimeraFila($consulta);
	
	$cAdministrativa=$fDatosSolicitud[0];
	$tipoAudiencia=$fDatosSolicitud[1];
	
	$fechaBase=date("Y-m-d H:i:s");
	$consulta="";
	switch($idFormulario)
	{
		case 46:
			$consulta="SELECT CONCAT(fechaRecepcion,' ',horaRecepcion) FROM _46_tablaDinamica WHERE id__46_tablaDinamica=".$idRegistro;
			$fechaBase=$con->obtenerValor($consulta);
		break;
		case 185:
		
			$consulta="SELECT fechaCreacion,iFormulario,iRegistro FROM _185_tablaDinamica WHERE id__185_tablaDinamica=".$idRegistro;
			$fRegistro=$con->obtenerPrimeraFila($consulta);
			if($fRegistro[1]!="")
			{
				$consulta="SELECT CONCAT(fechaRecepcion,' ',horaRecepcion) FROM _96_tablaDinamica WHERE id__96_tablaDinamica=".$idRegistro;
				$fechaBase=$con->obtenerValor($consulta);
			}
			else
				$fechaBase=$fRegistro[0];
		break;
	}
	
	if($objParametrosConfiguracion!=NULL)
	{
		if(isset($objParametrosConfiguracion["fechaBase"]))
		{
			$fechaBase=$objParametrosConfiguracion["fechaBase"];
		}
	}
	
	$consulta="SELECT * FROM _4_tablaDinamica WHERE id__4_tablaDinamica=".$tipoAudiencia;
	$fDatosAudiencia=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$consulta="SELECT * FROM _567_tablaDinamica WHERE id__567_tablaDinamica=".$fDatosAudiencia["perfilProgramacionAudiencia"];
	$fDatosPerfil=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$arrHorarios=array();
	$consulta="SELECT dia,horaInicio,horaFin FROM _567_gHorariosProgramacion WHERE idReferencia=".$fDatosAudiencia["perfilProgramacionAudiencia"];
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		$arrHorarios[$fila[0]]["horaInicio"]=$fila[1];
		$arrHorarios[$fila[0]]["horaFin"]=$fila[2];
	}
	
	$funcionAsignacionJuez=$fDatosPerfil["funcionAsignacionJuez"];
	$perfilCategoriaAudiencia=$fDatosAudiencia["perfilCategoriaAudiencia"];
	$fechaMinima=$fechaBase;
	$calcularMinutosAgenda=true;
	if($fDatosPerfil["horaAntesRecepcion"]!="")
	{
		$baseComparacion=strtotime(date("Y-m-d",strtotime($fechaBase))." ".$fDatosPerfil["horaAntesRecepcion"]);
		if(strtotime($fechaBase)<=$baseComparacion)
		{
			$fechaMinima=date("Y-m-d",strtotime($fechaBase))." ".$fDatosPerfil["agendaMismoDia"];
			$calcularMinutosAgenda=false;
		}
	}
	
	if($fDatosPerfil["horaDespuesRecepcion"]!="")
	{
		$baseComparacion=strtotime(date("Y-m-d",strtotime($fechaBase))." ".$fDatosPerfil["horaDespuesRecepcion"]);
		if(strtotime($fechaBase)>$baseComparacion)
		{
			$fechaMinima=date("Y-m-d",strtotime("+1 days",strtotime($fechaBase)))." ".$fDatosPerfil["agendaSiguienteDia"];
			$calcularMinutosAgenda=false;

		}
	}
	$intervaloMinimo=0;
	$intervaloMinimo=($fDatosAudiencia["horaMinimasAudiencia"]*60);
	if($calcularMinutosAgenda)
	{
		
		$fechaMinima=strtotime("+".$intervaloMinimo." minutes",strtotime($fechaBase));
		$fechaMinima=date("Y-m-d H:i:s",$fechaMinima);
	}
	
	$fechaMaxima=strtotime("+ ".($fDatosAudiencia["horasMaximaAgendaAudiencia"]*60)." minutes",strtotime($fechaBase));
	$fechaMaxima=date("Y-m-d H:i:s",$fechaMaxima);
	$nDias=0;
	if($fDatosAudiencia["agendaDiaNoHabil"]==0)
	{
		
		$fechaInicio=strtotime(date("Y-m-d",strtotime($fechaMinima)));
		$fechaFin=strtotime(date("Y-m-d",strtotime($fechaMaxima)));
		while($fechaInicio<=$fechaFin)
		{
			if(!esDiaHabilInstitucion(date("Y-m-d",$fechaInicio)))
				$nDias++;
			$fechaInicio=strtotime("+1 days",$fechaInicio);
		}
		if($nDias>0)
		{
			$fechaMaxima=date("Y-m-d H:i:s",strtotime("+".$nDias." days",strtotime($fechaMaxima)));
		}
	}

	$fechaMinima=normalizarHorarioAsignacion($fechaMinima);

	$validarFechaMinima=true;
	$fechaActual=strtotime(date("Y-m-d H:i:s"));
	if(strtotime($fechaMinima)<$fechaActual)
	{

		$fechaMinima=strtotime("+".$intervaloMinimo." minutes",$fechaActual);
		$fechaMinima=date("Y-m-d H:i:s",$fechaMinima);
		$fechaMinima=normalizarHorarioAsignacion($fechaMinima);
		if($fDatosAudiencia["tipoAtencion"]==2)
			$validarFechaMinima=false;
	}

	$fechaInicialBusqueda="";
	switch($fDatosAudiencia["criterioProgramacion"])
	{
		case 1: //Lo más pronto posible
			if($validarFechaMinima)
			{
				$dia=date("w",strtotime($fechaMinima));
				$fMinima=strtotime($fechaMinima);
				$fDiaHoy=strtotime(date("Y-m-d ".$arrHorarios[$dia]["horaFin"]));
				if((date("Y-m-d",$fMinima)==date("Y-m-d",$fDiaHoy))&&($fMinima>$fDiaHoy))
				{

					$fechaSiguiente=date("Y-m-d",strtotime("+1 days",strtotime($fechaMinima)));
					if($fDatosAudiencia["agendaDiaNoHabil"]==0)
						$fechaSiguiente=obtenerSiguienteDiaHabil($fechaSiguiente);
					
					$dia=date("w",strtotime($fechaSiguiente));
					
					$fechaMinima=$fechaSiguiente." ".$arrHorarios[$dia]["horaInicio"];
				}
				else
				{
					if(strtotime($fechaMinima)<strtotime(date("Y-m-d ".$arrHorarios[$dia]["horaInicio"])))
						$fechaMinima=date("Y-m-d",strtotime($fechaMinima))." ".$arrHorarios[$dia]["horaInicio"];
				}
			}
			
			$fechaInicialBusqueda=$fechaMinima;
		
		break;
		case 2: //Lo menos pronto posible
			$fechaInicialBusqueda=date("Y-m-d",strtotime($fechaMaxima));
			$dia=date("w",strtotime($fechaInicialBusqueda));
			$fechaInicialBusqueda=$fechaInicialBusqueda." ".$arrHorarios[$dia]["horaInicio"];
		break;
		case 3: //Lo medianamente posible
			$diasIntervalo=obtenerDiferenciaDias($fechaBase,$fechaMaxima);
			$diasIntervalo=ceil($diasIntervalo/2);
			if($diasIntervalo>0)
			{
				
				$fechaInicialBusqueda=date("Y-m-d",strtotime("+".$diasIntervalo." days",strtotime($fechaMinima)));
				if($fDatosAudiencia["agendaDiaNoHabil"]==0)
					$fechaInicialBusqueda=obtenerSiguienteDiaHabil($fechaInicialBusqueda);
				
				$dia=date("w",strtotime($fechaInicialBusqueda));
				$fechaInicialBusqueda=$fechaInicialBusqueda." ".$arrHorarios[$dia]["horaInicio"];
			}
			else
				$fechaInicialBusqueda=date("Y-m-d",strtotime($fechaMinima));
		break;
	}

	$esGuardia=determinarTipoHorarioGeneral($fechaMinima)==2;
	if($fDatosAudiencia["consideraGuardia"]==0)
		$esGuardia=0;
	$consulta="SELECT carpetaAdministrativa,unidadGestion,tipoCarpetaAdministrativa FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$cAdministrativa."'";
	$fCarpetaAdministrativa=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$consulta="SELECT id__17_tablaDinamica FROM _17_tablaDinamica WHERE claveUnidad='".$fCarpetaAdministrativa["unidadGestion"]."'";
	$idUnidadGestion=$con->obtenerValor($consulta);
	
	$objParam='{"perfilCategoriaAudiencia":"'.$perfilCategoriaAudiencia.'","tipoAudiencia":"'.$tipoAudiencia.'","esGuardia":"'.
			($esGuardia?1:0).'","cAdministrativa":"'.$cAdministrativa.'","unidadGestion":"'.$fCarpetaAdministrativa["unidadGestion"].
			'","tipoCarpeta":"'.$fCarpetaAdministrativa["tipoCarpetaAdministrativa"].'","fechaMinima":"'.$fechaMinima.
			'","fechaMaxima":"'.$fechaMaxima.'","fechaBase":"'.$fechaBase.'","fechaInicialBusqueda":"'.$fechaInicialBusqueda.
			'","criterioProgramacion":"'.$fDatosAudiencia["criterioProgramacion"].'","atencionUrgente":"'.
			($fDatosAudiencia["tipoAtencion"]==2?1:0).'","idUnidadGestion":"'.$idUnidadGestion.'","validarFechaMinima":"'.
			($validarFechaMinima?1:0).'","idFormulario":"'.$idFormulario.'","idRegistro":"'.$idRegistro.'","idJuezPrioridad":"-1"}';

	$oParam=json_decode($objParam);			
	
	
	$cadObj='{"arrParametros":"'.bE($objParam).'"}';
	$cache=NULL;
	$obj=json_decode($cadObj);
	varDUmp($oParam);

	$oEvento=resolverExpresionCalculoPHP($funcionAsignacionJuez,$obj,$cache);
	
	
	varDUmp($oEvento);
	return;
	if(gettype($oEvento)=="array")
	{
		$situacion=0;
		$etapaProcesal=1;
		$oDatosParametros["fechaMaximaAudiencia"]=$fechaMaxima;
		$oDatosParametros["notificarMAJO"]=false;
		
		$idEvento=registrarEventoAudiencia($oEvento,$idFormulario,$idRegistro,-1,$tipoAudiencia,$fCarpetaAdministrativa["tipoCarpetaAdministrativa"],1,$oDatosParametros);
	}
	else
		$idEvento=$oEvento;
}


function registrarCambioSituacionObjeto($tipoObjeto,$idRegistro,$situacionCambio,$comentariosAdicionales)
{
	global $con;
	$consulta="SELECT * FROM 3023_tiposObjetosBitacoraCambios WHERE idRegistro=".$tipoObjeto;
	$fRegistro=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$consulta="SELECT ".$fRegistro["nombreCampoSituacion"]." FROM ".$fRegistro["tablaAsociada"]." WHERE ".$fRegistro["nombreCampoID"]."=".$idRegistro;
	$situacionActual=$con->obtenerValor($consulta);
	
	$x=0;
	$query[$x]="begin";
	$x++;
	$query[$x]="INSERT INTO 3022_bitacoraCambioSituacionObjeto(tipoObjeto,idRegistroReferencia,idEstadoAnterior,idEstadoActual,
				fechaOperacion,idResponsableOperacion,comentariosAdicionales)
				VALUES(".$tipoObjeto.",".$idRegistro.",".$situacionActual.",".$situacionCambio.",'".date("Y-m-d H:i:s")."',".
				$_SESSION["idUsr"].",'".cv($comentariosAdicionales)."')";
	$x++;
	$query[$x]="update ".$fRegistro["tablaAsociada"]." set ".$fRegistro["nombreCampoSituacion"]."=".$situacionCambio.
			" WHERE ".$fRegistro["nombreCampoID"]."=".$idRegistro;
	$x++;
	$query[$x]="commit";
	$x++;
	return $con->ejecutarBloque($query);
	
}

function enviarRespuestaPromocionSICOREAutoConstancia($idRegistroFormato)
{
	try
	{
		global $con;
		global $arrMesLetra;
	
		$arrFormatosNotifica[122]=1;
		$arrFormatosNotifica[505]=1;
		
		
		$consulta="SELECT tipoFormato,idFormulario,idRegistro,idDocumento,idDocumentoAdjunto FROM 3000_formatosRegistrados WHERE idRegistroFormato=".$idRegistroFormato;
		$fRegistroDocumento=$con->obtenerPrimeraFilaAsoc($consulta);

		if((isset($arrFormatosNotifica[$fRegistroDocumento["tipoFormato"]]))&&($fRegistroDocumento["idFormulario"]==-2))
		{
			$consulta="SELECT idFormulario,idReferencia as iReferencia FROM 7035_informacionDocumentos WHERE idRegistro=".$fRegistroDocumento["idRegistro"];
			$fAcuerdo=$con->obtenerPrimeraFilaAsoc($consulta);
			
			if($fAcuerdo["idFormulario"]==96)
			{
				$consulta="SELECT idPromocionSICORE FROM _96_tablaDinamica WHERE id__96_tablaDinamica=".$fAcuerdo["iReferencia"];
				$idPromocionSICORE=$con->obtenerValor($consulta);
				if($idPromocionSICORE!="")
				{
					$consulta="UPDATE _96_tablaDinamica SET notificarSICORE=1,notificacionRealizada=0 WHERE id__96_tablaDinamica=".$fAcuerdo["iReferencia"];
					$con->ejecutarConsulta($consulta);
					
					if($fRegistroDocumento["idDocumentoAdjunto"]!="")
						$fRegistroDocumento["idDocumento"]=$fRegistroDocumento["idDocumentoAdjunto"];
					$consulta="SELECT nomArchivoOriginal FROM 908_archivos WHERE idArchivo=".$fRegistroDocumento["idDocumento"];
					$nomArchivoOriginal=$con->obtenerValor($consulta);
					
					$archivoOrigen=obtenerRutaDocumento($fRegistroDocumento["idDocumento"]);
					$documentoPromocion=bE(leerContenidoArchivo($archivoOrigen));
					$documentoPromocionPKCS7="";
					if(file_exists($archivoOrigen.".pkcs7"))
					{
						$documentoPromocionPKCS7=bE(leerContenidoArchivo($archivoOrigen.".pkcs7"));
					}
					
					$cadObj='{"idPromocion":"'.$idPromocionSICORE.'","nombreDocumentoPromocion":"'.$nomArchivoOriginal.
							'","documentoPromocion":"'.$documentoPromocion.'","documentoPromocionPKCS7":"'.$documentoPromocionPKCS7.'"}';	

					$fDatosServidor=obtenerURLComunicacionServidorMateria("SW");
					$url=$fDatosServidor[0].($fDatosServidor[1]!=""?":".$fDatosServidor[1]:"");
				
					$client = new nusoap_client("http://".$url."/webServices/wsInterconexionSistemasBPM.php?wsdl","wsdl");
					$parametros=array();
					$parametros["cadObj"]=$cadObj;
					$response = $client->call("registrarRespuestaPromocion", $parametros);
		
					$oResp=json_decode($response);	

					if($oResp->resultado==1)
					{
						$consulta="UPDATE _96_tablaDinamica SET notificacionRealizada=1 WHERE id__96_tablaDinamica=".$fAcuerdo["iReferencia"];
						$con->ejecutarConsulta($consulta);
					}
					
					
					
				}
			}
		}
	}
	catch(Exception $e)
	{
		//echo $e->getMessage();	
	}
	return true;
}


function enviarDiligenciaCentralNotificadores($idDiligencia)
{
	global $con;
	$objResp=json_decode('{"resultado":"","mensaje":""}');
	
	$idRegistroNotificacion=0;
	try
	{
		
		$consulta="SELECT * FROM 7029_diligenciaActaNotificacion WHERE idRegistro=".$idDiligencia;
		$fRegistro=$con->obtenerPrimeraFilaAsoc($consulta);
		
		
		$consulta="SELECT * FROM 7042_ordenesNotificacion WHERE idOrden=".$fRegistro["idOrden"];
		$fRegistroOrden=$con->obtenerPrimeraFilaAsoc($consulta);
		
	
		
		
		$consulta="SELECT idCarpeta FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$fRegistroOrden["carpetaJudicial"]."'";
		$idCarpeta=$con->obtenerValor($consulta);
		
		$tipoDiligencia="";
		
		switch($fRegistro["tipoDiligencia"])
		{
			case 1: //N
				$tipoDiligencia=1;
			break;
			case 2: //C
			case 3:// NC
				$tipoDiligencia=2;
			break;
			
		}
		
		$fechaAudiencia="";
		if($fRegistroOrden["idEventoDeriva"]!="")
		{
			$consulta="SELECT horaInicioEvento FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$fRegistroOrden["idEventoDeriva"];
			$fechaAudiencia=$con->obtenerValor($consulta);
		}
		
		$consulta="SELECT nombre,apellidoPaterno,apellidoMaterno FROM _47_tablaDinamica WHERE id__47_tablaDinamica=".$fRegistro["idNombreParteProcesal"];
		$fNombreParte=$con->obtenerPrimeraFila($consulta);
		
		$consulta="SELECT CONCAT(IF(nombre IS NULL,'',nombre),' ',IF(apPaterno IS NULL,'',apPaterno),' ',IF(apMaterno IS NULL,'',apMaterno)) 
					FROM _47_gAlias WHERE idReferencia=".$fRegistro["idNombreParteProcesal"];
		$otrosNombres=$con->obtenerValor($consulta);
		
		$arrDocumentos='';
		$consulta="SELECT idDocumento FROM 7043_documentosNotificacion WHERE idOrden=".$fRegistro["idOrden"];
		/*."
					UNION
					SELECT idDocumento FROM 7030_documentosAdjuntosDiligencia WHERE idDiligencia=".$idDiligencia;*/

		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$consulta="SELECT nomArchivoOriginal FROM 908_archivos WHERE idArchivo=".$fila[0];
			$nArchivo=$con->obtenerValor($consulta);
			
			$rutaArchivo=obtenerRutaDocumento($fila[0]);
			$documentoBase64=leerContenidoArchivo($rutaArchivo);
			$oDocumentos='{"nombre":"'.$nArchivo.'","documentoEnBase64":"'.bE($documentoBase64).'"}';
			if($documentoBase64=="")
				continue;
			if($arrDocumentos=="")
			{
				$arrDocumentos=$oDocumentos;
			}
			else
			{
				$arrDocumentos.=",".$oDocumentos;
			}
		}
		$oDireccion=json_decode(obtenerUltimoDomicilioFiguraJuridica($fRegistro["idNombreParteProcesal"]));
		$referencias="";
		if($oDireccion->entreCalle!="")
		{
			$referencias="Entre calle ".$oDireccion->entreCalle;
		}
		
		if($oDireccion->yCalle!="")
		{
			$referencias=" y calle ".$oDireccion->yCalle;
		}
		
		if($referencias!="")
			$referencias.=". ".$oDireccion->referencias;
		else
			$referencias.=$oDireccion->referencias;
		$direccion=$oDireccion->lblDireccion;
		
		
		
		$cadObj='{
					"tipoId": "'.$tipoDiligencia.'",
					"prioridadId": "1",
					"figuraOrdenamientoId": "1",
					"fechaOrdenamiento": "'.$fRegistroOrden["fechaDeterminacion"].'",
					"fechaHoraAudiencia": "'.$fechaAudiencia.'", 
					"figuraJuridicaId": "'.$fRegistro["idParteProcesal"].'",
					"carpetaJudicial": "'.$fRegistroOrden["carpetaJudicial"].'",
					"expediente": "'.$idCarpeta.'", 
					"oficio": "'.str_pad($idDiligencia,5,"0",STR_PAD_LEFT).'",
					"fechaVisita": "", 
					"horaVisita": "", 
					"nombre": "'.cv($fNombreParte[0]).'",
					"primerAp": "'.cv($fNombreParte[1]).'",
					"segundoAp": "'.cv($fNombreParte[2]).'",
					"otrosNombres": "'.cv($otrosNombres).'",
					"direccionHL": "'.cv($direccion).'",
					"referencias": "'.cv($referencias).'",
					"documentos":['.$arrDocumentos.']
				}';
			
		$consulta="SELECT n.usuario,n.password FROM _17_tablaDinamica u,7006_carpetasAdministrativas c,_579_tablaDinamica n 
					WHERE u.claveUnidad=c.unidadGestion AND c.carpetaAdministrativa='".$fRegistroOrden["carpetaJudicial"]."'
					AND n.idReferencia=u.id__17_tablaDinamica"	;
		$fDatosNotificadores=$con->obtenerPrimeraFila($consulta);	
			
		$usuario="Capturista1";
		$passwd="123456";		
		if($fDatosNotificadores)
		{
			$usuario=$fDatosNotificadores[0];
			$passwd=$fDatosNotificadores[1];		
			
		}
		
		$idRegistroNotificacion=registrarBitacoraNotificacionOperadores(bE($cadObj),7029,1,$idDiligencia);
		
		$service_url = 'http://172.19.202.51:8500/api/sgjp/crearDiligencia';
		$curl = curl_init($service_url);
		$curl_post_data = $cadObj;
			
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
	
		curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);
		
		
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(                                                                          
														'Content-type: application/json;charset="utf-8"',
														'Authorization: Basic '. base64_encode($usuario.":".$passwd)
													)
					);                                                                                                                   
									
		$curl_response = curl_exec($curl);

		$oResp=json_decode($curl_response);
		if(isset($oResp->Error))
		{
			$objResp->resultado=0;
			$objResp->mensaje=gettype($oResp->Error)=="object"?json_encode($oResp->Error):$oResp->Error;
			
			actualizarBitacoraNotificacionOperadores($idRegistroNotificacion,0,$objResp->mensaje,bE($curl_response));
			return $objResp;
		}
		else
		{
			actualizarBitacoraNotificacionOperadores($idRegistroNotificacion,1,"",bE($curl_response));
			$consulta="UPDATE 7029_diligenciaActaNotificacion SET enviadoCentralNotificadores=1,
					idAcuseEnvioCentralNotificadores=".$oResp->id.",folioAcuseEnvioCentralNotificadores=".$oResp->folio.
					",fechaEnvioCentralNotificadores='".date("Y-m-d H:i:s")."'  WHERE idRegistro=".$idDiligencia;
			if($con->ejecutarConsulta($consulta))
			{
				$objResp->resultado=1;
				return $objResp;
				
			}
	

		}
	
	}
	catch(Exception $e)
	{
		$objResp->resultado=0;
		$objResp->mensaje=$e->getMessage();
		actualizarBitacoraNotificacionOperadores($idRegistroNotificacion,0,$e->getMessage(),"");
		return $objResp;
	}
}


?>