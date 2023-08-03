<?php
	
function generarFechaAudienciaSolicitudV3(&$oDatosParametros) //OK
{
	global $con;
	global $tipoMateria;
	
	$tipoError=0;	
	$nInteraciones=0;	
	
	$idFormulario=$oDatosParametros["idFormulario"];
	$idRegistro=$oDatosParametros["idRegistro"];
	$idReferencia=$oDatosParametros["idReferencia"];
	$tipoAudiencia=$oDatosParametros["tipoAudiencia"];
	
	$oDatosAudiencia=array();
	$oDatosAudiencia["idRegistroEvento"]=$oDatosParametros["oDatosAudiencia"]["idRegistroEvento"];
	$oDatosAudiencia["idEdificio"]="";
	$oDatosAudiencia["idUnidadGestion"]="";
	$oDatosAudiencia["idSala"]="";
	$oDatosAudiencia["listaSalasIgnorar"]=-1;
	$oDatosAudiencia["fecha"]="";
	$oDatosAudiencia["horaInicio"]="";
	$oDatosAudiencia["horaFin"]="";
	$oDatosAudiencia["jueces"]=array();
	$oDatosAudiencia["listaJuecesIgnorar"]=-1;
	$notificarMAJO=$oDatosParametros["notificarMAJO"];
	
	
	if($oDatosParametros["oDatosAudiencia"]["idUnidadGestion"]!="")
	{
		$oDatosAudiencia["idUnidadGestion"]=$oDatosParametros["oDatosAudiencia"]["idUnidadGestion"];
	}
	else
	{
		$oDatosAudiencia["idUnidadGestion"]=removerComillasLimite(obtenerUnidadesGestionTipoDelitoV3($idFormulario,$idRegistro,$oDatosParametros["fechaBaseSolicitud"]));
	}


	
	
	if($oDatosParametros["nivelAsignacion"]<2)
	{
		$consulta="SELECT idReferencia FROM _17_tablaDinamica WHERE id__17_tablaDinamica=".$oDatosAudiencia["idUnidadGestion"];
		$idEdificio=$con->obtenerValor($consulta);
		$oDatosAudiencia["idEdificio"]=$idEdificio;
		return $oDatosAudiencia;
	}

	
	
	
	$finalizarBusqueda=false;
	$fechaAsignada=false;
	while(!$finalizarBusqueda)
	{
		$fechaAsignada=false;
		$fechaInicialBusqueda=date("Y-m-d",strtotime($oDatosParametros["fechaMinimaAudiencia"]));
		
		
		$fechaInicialBusqueda=obtenerSiguienteFechaAsignable($fechaInicialBusqueda,$oDatosParametros["fechaMaximaAudiencia"],
															$oDatosParametros["considerarDiaHabil"]);
		

		while(!$fechaAsignada)
		{
			$oDatosAudiencia["jueces"]=array();
		  	$juecesAsignados=true;

			for($x=0;$x<sizeof($oDatosParametros["juecesRequeridos"]);$x++)
			{
				
				if($tipoMateria=="P")
				{
					
					$asignacionJuez=asignarJuezAudienciaV3($oDatosAudiencia,$oDatosParametros,$oDatosParametros["juecesRequeridos"][$x]["tipoJuez"],$oDatosAudiencia["listaJuecesIgnorar"],$fechaInicialBusqueda);
					
					$oDatosParametros["juecesRequeridos"][$x]["idUsuario"]=$asignacionJuez["idJuez"];
					$oDatosParametros["juecesRequeridos"][$x]["noRonda"]=$asignacionJuez["noRonda"];
					$oDatosParametros["juecesRequeridos"][$x]["tipoRonda"]=$asignacionJuez["tipoRonda"];
				
				}
				else
				{
					
					$oDatosParametros["juecesRequeridos"][$x]["idUsuario"]=asignarJuezAudienciaJuzgado($oDatosAudiencia,$oDatosParametros,$oDatosParametros["juecesRequeridos"][$x]["tipoJuez"],$oDatosAudiencia["listaJuecesIgnorar"],$fechaInicialBusqueda);
				
					$oDatosParametros["juecesRequeridos"][$x]["noRonda"]=0;
					$oDatosParametros["juecesRequeridos"][$x]["tipoRonda"]="";
				}
				
				if($oDatosParametros["juecesRequeridos"][$x]["idUsuario"]==-1)
				{
					
					break;
					
				}
				else
				{
					$oDatosAudiencia["listaJuecesIgnorar"].=",".$oDatosParametros["juecesRequeridos"][$x]["idUsuario"];
				}
				
			}
			
		
			
			if($juecesAsignados)
			{
				foreach($oDatosParametros["juecesRequeridos"] as $j)									
				{
					array_push($oDatosAudiencia["jueces"],$j);
				}
				
				$fechaAudienciaAsignada=false;
				$iteraciones=0;
				while(!$fechaAudienciaAsignada)
				{
					
					$agendaEvento=obtenerFechaEventoAudienciaV3($oDatosParametros,$oDatosAudiencia,$fechaInicialBusqueda);
					
					
					
					
					
					if($agendaEvento!=-1)
					{				
	
						$oDatosAudiencia["idSala"]=$agendaEvento["idSala"];
						
						$consulta="SELECT idReferencia FROM _15_tablaDinamica WHERE id__15_tablaDinamica=".$oDatosAudiencia["idSala"];
						$idEdificio=$con->obtenerValor($consulta);
						$oDatosAudiencia["idEdificio"]=$idEdificio;
						$oDatosAudiencia["fecha"]=$agendaEvento["fechaEvento"];
						$oDatosAudiencia["horaInicio"]=$agendaEvento["horaInicial"];
						$oDatosAudiencia["horaFin"]=$agendaEvento["horaFinal"];
						$fechaAudienciaAsignada=true;
						$fechaAsignada=true;
						$finalizarBusqueda=true;
					}
					else
					{

						$fechaInicialBusqueda=date("Y-m-d",strtotime("+1 days",strtotime($fechaInicialBusqueda)));
						$fechaInicialBusqueda=obtenerSiguienteFechaAsignable($fechaInicialBusqueda,$oDatosParametros["fechaMaximaAudiencia"],
																			$oDatosParametros["considerarDiaHabil"]);
																
						if(!$fechaInicialBusqueda)													
						{
							$consulta="SELECT idReferencia FROM _17_tablaDinamica WHERE id__17_tablaDinamica=".$oDatosAudiencia["idUnidadGestion"];
							$idEdificio=$con->obtenerValor($consulta);
							$oDatosAudiencia["idEdificio"]=$idEdificio;
							$oDatosAudiencia["idSala"]=0;
							$oDatosAudiencia["fecha"]=date("Y-m-d",strtotime($oDatosParametros["fechaMinimaAudiencia"]));
							$oDatosAudiencia["horaInicio"]=$oDatosParametros["fechaMinimaAudiencia"];
							$oDatosAudiencia["horaFin"]=date("Y-m-d H:i:s",strtotime("+ ".$oDatosParametros["duracionAudiencia"]." minutes",strtotime($oDatosAudiencia["horaInicio"])));
							$fechaAudienciaAsignada=true;
							$fechaAsignada=true;
							$finalizarBusqueda=true;
						}
					}
					
				}
			}
			else
			{
				break;
				$finalizarBusqueda=true;
				$tipoError=-20;
			}
		}
	}
	if($fechaAsignada)
		return $oDatosAudiencia;
	else
		return $tipoError;
}

function determinarTipoHorarioGeneral($horario)
{
	if(esHorarioGuardiaV3($horario))
		return 2;
	return 1;
}

//Funciones Horario//
function esHorarioGuardiaV3($horario)
{
	$horaSolicitud=strtotime($horario);
	$fechaSolicitud=date("Y-m-d",$horaSolicitud);
	$horarioInicial=strtotime(date("Y-m-d ",$horaSolicitud)." 13:30");
	$dia=date("w",$horaSolicitud);
	

	switch($dia)
	{
		case 5://Viernes
			if(!esDiaHabilInstitucion($fechaSolicitud))
			{
				return true;
				
				
			}
			if($horaSolicitud>=$horarioInicial)
				return true;
		break;
		case 0://Domingo
			if($horaSolicitud<$horarioInicial)
				return true;
			else
			{
				$diaSiguiente=date("Y-m-d",strtotime("+1 days",strtotime($fechaSolicitud)));
				if(!esDiaHabilInstitucion($diaSiguiente))
					return true;
			}
		break;
		default:
			

			if(!esDiaHabilInstitucion($fechaSolicitud))
			{
				
				if($horaSolicitud<$horarioInicial)
					return true;
				else
				{
					$diaSiguiente=date("Y-m-d",strtotime("+1 days",strtotime($fechaSolicitud)));
					if(!esDiaHabilInstitucion($diaSiguiente))
						return true;
				}
			}
			else
			{
				
				$diaSiguiente=date("Y-m-d",strtotime("+1 days",strtotime($fechaSolicitud)));
				if(!esDiaHabilInstitucion($diaSiguiente))
				{
					if($horaSolicitud>=$horarioInicial)
						return true;
				}
				
			}
		
		break;
	}
	return false;
	
}




function esHorarioNormalDiaHabil($horario,$tipoMateria=1)
{
	if(!esDiaHabilInstitucion($horario))
	{
		return false;
	}
	$horaSolicitud=strtotime($horario);
	$fechaSolicitud=date("Y-m-d",$horaSolicitud);
	$horarioInicial=strtotime(date("Y-m-d ",$horaSolicitud)." 09:00");
	$horarioFinalNormal=strtotime(date("Y-m-d ",$horaSolicitud)." 15:00");
	$horarioFinalNormalViernes=strtotime(date("Y-m-d ",$horaSolicitud)." 14:00");
	if($tipoMateria=="2")
	{
		$horarioFinalNormal=strtotime(date("Y-m-d ",$horaSolicitud)." 13:30");
		$horarioFinalNormalViernes=strtotime(date("Y-m-d ",$horaSolicitud)." 12:30");
	}
	$dia=date("w",$horaSolicitud);
	
	switch($dia)
	{
		case 5://Viernes
			if(($horaSolicitud>=$horarioInicial)&&($horaSolicitud<$horarioFinalNormalViernes))
				return true;
		break;
		default:
			if(($horaSolicitud>=$horarioInicial)&&($horaSolicitud<$horarioFinalNormal))
				return true;	
		break;
	}
	
	return false;
	
}

function obtenerFechaRecepcionHorarioNormal($horario,$tipoMateria=1)
{
	$horaSolicitud=strtotime($horario);
	$horarioInicial=strtotime(date("Y-m-d ",$horaSolicitud)." 09:00");
	
	if(esHorarioNormalDiaHabil($horario,$tipoMateria))
	{
		return $horario;
	}
	
	if(!esDiaHabilInstitucion($horario))
	{
		$fechaHabil=obtenerProximoDiaHabil(date("Y-m-d",strtotime("+1 days",$horaSolicitud)));
		$horario=date("Y-m-d H:i:s",strtotime($fechaHabil." 09:00:00"));
		return $horario;
	}
	
	if($horaSolicitud<$horarioInicial)
	{
		$horario=date("Y-m-d",$horaSolicitud)." 09:00:00";
		return $horario;
	}
	else
	{
		$fechaHabil=obtenerProximoDiaHabil(date("Y-m-d",strtotime("+1 days",$horaSolicitud)));
		$horario=date("Y-m-d H:i:s",strtotime($fechaHabil." 09:00:00"));
		return $horario;
	}
	
}


function obtenerUnidadesGestionTipoDelitoV3($idFormulario,$idRegistro,$fechaReferencia="") //OK
{
	global $con;		
	$lista="";
		
	$arrTratamiento=array();
	$consulta="SELECT idActividad,fechaCreacion,tipoAudiencia,delitoGrave,iFormulario,iReferencia,carpetaRemitida 
				FROM _46_tablaDinamica WHERE id__46_tablaDinamica=".$idRegistro;
	$fRegistroAudiencia=$con->obtenerPrimeraFila($consulta);

	$idActividad=$fRegistroAudiencia[0];
	$fechaRegistro=$fRegistroAudiencia[1];
	$tipoAudiencia=$fRegistroAudiencia[2];
	$tipificacion=$fRegistroAudiencia[3];
	$iFormularioBase=$fRegistroAudiencia[4];
	$iReferenciaBase=$fRegistroAudiencia[5];
	$carpetaRemitida=$fRegistroAudiencia[6];
	$listaUnidades="";
	if(($iFormularioBase==554)||($iFormularioBase==556))
	{
		$idUnidadDestino=determinarUnidadDestinoIncompetencia($iFormularioBase,$iReferenciaBase);
		
		return "'".$idUnidadDestino."'";
	}
	
	$consulta="SELECT  tipoAtencion FROM _4_tablaDinamica WHERE id__4_tablaDinamica=".$tipoAudiencia;
	$tipoAtencion=$con->obtenerValor($consulta);

	if($tipoAtencion=="")
		$tipoAtencion=2;//Urgente
	
	$nOtrosDelitos=0;
	$nDelitosNarco=0;
	$nDelitosNarcoComercio=0;
	
	$consulta="SELECT COUNT(*) FROM _61_tablaDinamica WHERE  idActividad=".$idActividad." AND denominacionDelito=10241 AND 
				modalidadDelito IN(21752,20718)";
	$nDelitosNarco=$con->obtenerValor($consulta);
	
	$consulta="SELECT COUNT(*) FROM _61_tablaDinamica WHERE  idActividad=".$idActividad." AND denominacionDelito=10241 AND 
				modalidadDelito not IN(21752,20718)";
	$nDelitosNarcoComercio=$con->obtenerValor($consulta);	
	
	$consulta="SELECT COUNT(*) FROM _61_tablaDinamica WHERE  idActividad=".$idActividad." AND denominacionDelito<>10241";
	$nOtrosDelitos=$con->obtenerValor($consulta);
	
	
	if(($tipoAudiencia!=91)&&($tipoAudiencia!=102)&&($tipoAudiencia!=114))
	{
		if($tipificacion==1)
		{
			if($nDelitosNarcoComercio>0)
			{
				$tipificacion=1;
				//registrarCorreccionAlgoritmo($tipificacion,$idRegistro,1);
				$consulta="update _46_tablaDinamica set delitoGrave=1  WHERE id__46_tablaDinamica=".$idRegistro;
				$con->ejecutarConsulta($consulta);
			}
			else
			{
				if(($nDelitosNarco>0)&&($nOtrosDelitos==0))
				{
					$tipificacion=0;
					//registrarCorreccionAlgoritmo($tipificacion,$idRegistro,2);
					$consulta="update _46_tablaDinamica set delitoGrave=0  WHERE id__46_tablaDinamica=".$idRegistro;
					$con->ejecutarConsulta($consulta);
				}
				else
				{
					/*if($nDelitosNarco>0)
					{
						$tipificacionResultado=verificarTipificacionDelito($idFormulario,$idRegistro,$idActividad);
						if($tipificacionResultado!=-1)
						{
							$tipificacion=$tipificacionResultado;
							$consulta="UPDATE _46_tablaDinamica SET delitoGrave=".$tipificacionResultado." WHERE id__46_tablaDinamica=".$idRegistro;
							$con->ejecutarConsulta($consulta);
						}
					}*/
				}
				
				
			}
		}
	}

	$consulta="SELECT unidadGestion FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$carpetaRemitida."'";
	$ugaRemitente=$con->obtenerValor($consulta);

	if(($tipoAudiencia=="9")||($tipoAudiencia=="56")||($tipoAudiencia=="32")||($tipoAudiencia=="97")||($tipoAudiencia=="69")||($tipoAudiencia=="35"))//&&($tipificacion==1)
	{
		$tipificacion=3;
	}
	
	if($idActividad=="")
		$idActividad=-1;

	if(esRegistroAdolescentes($idFormulario,$idRegistro)==1)
	{
		$tipificacion=4;
	}

	$consulta="SELECT COUNT(genero) FROM _47_tablaDinamica i,7005_relacionFigurasJuridicasSolicitud r 
						WHERE i.id__47_tablaDinamica=r.idParticipante AND r.idFiguraJuridica IN(4) AND 
						r.idActividad=".$idActividad." AND genero=1";

	$nMujeres=$con->obtenerValor($consulta);
	$consulta="SELECT COUNT(genero) FROM _47_tablaDinamica i,7005_relacionFigurasJuridicasSolicitud r 
						WHERE i.id__47_tablaDinamica=r.idParticipante AND r.idFiguraJuridica IN(4) AND 
						r.idActividad=".$idActividad." AND genero=0";
	$nHombres=$con->obtenerValor($consulta);
	
	
	$fechaRegistro=date("Y-m-d H:i:s"); //Omitir cuando no se requiera tipificacion
	if($fechaReferencia!="")
		$fechaRegistro=$fechaReferencia;
	
	
	
	$tmeFechaRegistro=strtotime($fechaRegistro);
	$tmeFechaHoraReferenciaGuardia=strtotime(date("Y-m-d ",$tmeFechaRegistro)." 13:30");
	
	$tipoHorario=0;
	
	if($tipificacion==0)
	{
		$nivel="A";		
		$tipoHorario=determinarTipoHorarioGeneral($fechaRegistro);		

	}
	else
	{
		if($tipificacion==1)
		{
		
			$nivel="B";
			$tipoHorario=determinarTipoHorarioGeneral($fechaRegistro);		
			
		}
		else
		{
			if($tipificacion==4)
			{
				$nivel="D";
				$tipoHorario=1;
			}
			else
			{
				$nivel="X";
				$tipoHorario=1;
			}
		}
	}

	if($ugaRemitente!='011')
	{
		/*if((($nivel=="A")||($nivel=="B"))&&($tipoHorario<>2))
		{
			if(($nMujeres>0)&&($nHombres==0))
			{
				$nivel="M";
			}
		}*/
		
		
		if($tipoHorario<>2)
		{
			if(($nivel=="A")||($nivel=="B"))
			{
				if(($nMujeres>0)&&($nHombres==0))
				{
					if((($tipoAudiencia!=1)&&($tipoAudiencia!=52))||($nivel=="B"))
					{
						$nivel="M";
					}
					
				}
			}
		}
		
		
	}
	
	

	
	if(($nivel=="A")||($nivel=="B"))
	{
		switch($iFormularioBase)
		{
			case 329: //Vinculacion a proceso			
				$esPrisionPreventivaOficiosa=false;			
				
				$consulta="SELECT prisionPreventiva FROM _329_tablaDinamica WHERE id__329_tablaDinamica=".$iReferenciaBase;
				$prisionPreventiva=$con->obtenerValor($consulta);
				
				$esDelitoRobo=false;
				
				$esPrisionPreventivaOficiosa=($prisionPreventiva==1);
				
				$consulta="SELECT COUNT(s.denominacionDelito) FROM _61_tablaDinamica d,_35_denominacionDelito s WHERE idActividad=".$idActividad." AND
							s.id__35_denominacionDelito=d.denominacionDelito AND s.denominacionDelito LIKE '%robo%'";
				
				$nRegistrosDelito=$con->obtenerValor($consulta);
				$esDelitoRobo=($nRegistrosDelito>0);
				
				if(($nivel=="B")&&($esDelitoRobo)&&(!$esPrisionPreventivaOficiosa))
				{
					$nivel="A";
				}
				
			break;
		}
	}
	
	if($nivel!="B")
	{
		$lista="";
		$consulta="SELECT idReferencia FROM _17_gridDelitosAtiende WHERE tipoDelito='".$nivel."'";//and idReferencia<>15
		
		if(($tipoAudiencia!=102)&&($tipoAudiencia!=91)&&($tipoAudiencia!=24)&&($tipoHorario==2)&&($tipoAudiencia!=114)&&($tipoAtencion==2))
		{
			$consulta.=" and idReferencia in (SELECT unidadGestion FROM _290_tablaDinamica WHERE '".date("Y-m-d",$tmeFechaRegistro).
								"'>=fechaInicial AND '".date("Y-m-d",$tmeFechaRegistro)."'<=fechaFinal ) limit 0,2";
			
			$lista=$con->obtenerListaValores($consulta);
			if($con->filasAfectadas>1)
			{
				if($tmeFechaRegistro<$tmeFechaHoraReferenciaGuardia)
				{
					$consulta="SELECT unidadGestion FROM _290_tablaDinamica WHERE fechaFinal='".date("Y-m-d",$tmeFechaRegistro)."'
								and unidadGestion in(".$lista.")  order by id__290_tablaDinamica limit 0,1";
					$lista=$con->obtenerListaValores($consulta);	
				}
				else
				{
					$consulta="SELECT unidadGestion FROM _290_tablaDinamica WHERE fechaInicial='".date("Y-m-d",$tmeFechaRegistro)."'
								and unidadGestion in(".$lista.")  order by id__290_tablaDinamica limit 0,1";
					$lista=$con->obtenerListaValores($consulta);
				}
			}
			
			
			
		}
		else
		{
			if(($tipoAtencion==2)&&($nivel=="A"))
			{
				$query="SELECT unidadGestion,fechaFinal FROM _290_tablaDinamica g,_17_gridDelitosAtiende d WHERE d.idReferencia=g.unidadGestion 
						and tipoDelito='".$nivel."' and fechaFinal in('".date("Y-m-d",$tmeFechaRegistro)."','".
						date("Y-m-d",strtotime("-1 days",$tmeFechaRegistro))."') 
						 order by fechaFinal desc limit 0,1";
//				echo $query;
				$fGuardia=$con->obtenerPrimeraFila($query);	
				$ugasGuardia=$fGuardia[0]; 
				if(!$fGuardia)
					$ugasGuardia=-1; 									
				$ugasGuardia=$con->obtenerListaValores($query);	
				if(!$fGuardia)
					$ugasGuardia=-1;
				else
				{
					$ugasGuardia=$fGuardia[0];  
					if($fGuardia[1]!=date("Y-m-d",$tmeFechaRegistro))
					{
						$fechaActual=date("Y-m-d");
						
						if(strtotime(date("Y-m-d H:i:s"))>=strtotime($fechaActual." 13:30"))
						{
							$ugasGuardia=-1;
						}
							
					}
					  
				}
				//$consulta.=" and idReferencia not in(".$ugasGuardia.")";

			}
			$lista=$con->obtenerListaValores($consulta);
			$lista=obtenerUnidadGestionSiguienteAsignacion($tipoAudiencia,$lista);
		}
		
		if($lista=="")	
			$lista=-1;
		
	}
	else
	{
		$aplicaGuardia=false;
		
	
		if(($tipoAudiencia!=102)&&($tipoAudiencia!=91)&&($tipoAudiencia!=24)&&($tipoHorario==2)&&($tipoAudiencia!=114)&&($tipoAtencion==2))
		{
			$aplicaGuardia=true;
			
		}
		
		if($aplicaGuardia)
		{
			
			$consulta="SELECT idReferencia FROM _17_gridDelitosAtiende WHERE tipoDelito='".$nivel."' and idReferencia in 
						(SELECT unidadGestion FROM _290_tablaDinamica WHERE '".date("Y-m-d",$tmeFechaRegistro).
								"'>=fechaInicial AND '".date("Y-m-d",$tmeFechaRegistro)."'<=fechaFinal) limit 0,2";

			$lista=$con->obtenerListaValores($consulta);

			if($con->filasAfectadas>1)
			{
				if($tmeFechaRegistro<$tmeFechaHoraReferenciaGuardia)
				{
					$consulta="SELECT unidadGestion FROM _290_tablaDinamica WHERE fechaFinal='".date("Y-m-d",$tmeFechaRegistro)."'
								and unidadGestion in(".$lista.") order by id__290_tablaDinamica limit 0,1";
					$lista=$con->obtenerListaValores($consulta);	
				}
				else
				{
					$consulta="SELECT unidadGestion FROM _290_tablaDinamica WHERE fechaInicial='".date("Y-m-d",$tmeFechaRegistro)."'
								and unidadGestion in(".$lista.") order by id__290_tablaDinamica limit 0,1";
					$lista=$con->obtenerListaValores($consulta);
				}
			}
			
			
		}
		else
		{
			$consulta="SELECT * FROM _46_tablaDinamica WHERE id__46_tablaDinamica=".$idRegistro;
			$fila=$con->obtenerPrimeraFilaAsoc($consulta);
			
			$idUnidadGestion=-1;
						
			$consulta="SELECT claveFiscalia,sistema FROM _100_tablaDinamica WHERE idReferencia=".$fila["id__46_tablaDinamica"];
			$filaFiscalia=$con->obtenerPrimeraFila($consulta);
			$fiscalia=$filaFiscalia[0];
			$sistema=$filaFiscalia[1];
			if($fiscalia=="")
				$fiscalia=-1;
			
			if($nDelitosNarcoComercio>0)
			{
				$consulta="SELECT fiscalias FROM _361_tablaDinamica WHERE idReferencia=35";
				$fiscalia=$con->obtenerValor($consulta);
				$sistema=1;
			}
			
			
			$consulta="SELECT idReferencia FROM _361_tablaDinamica WHERE fiscalias=".$fiscalia." and sistema=".$sistema;			
			$idUnidadGestion=$con->obtenerListaValores($consulta);
			if($idUnidadGestion=="")
			{
				$consulta="SELECT id__17_tablaDinamica FROM _17_tablaDinamica WHERE id__17_tablaDinamica IN(
						SELECT idReferencia FROM _17_gridDelitosAtiende WHERE tipoDelito='B')";
				$lista=$con->obtenerListaValores($consulta);
				$idUnidadGestion=obtenerUnidadGestionSiguienteAsignacion($tipoAudiencia,$lista);
				
			}
			else
			{
				$idUnidadGestion=obtenerUnidadGestionSiguienteAsignacion($tipoAudiencia,$idUnidadGestion);
			}
			
			
			
			$lista=$idUnidadGestion;			

			if($lista=="")	
				$lista=-1;
		}
		
	}

	if($_SESSION["idUsr"]==1)	
	{
		//$lista=25;
		
		/*
if($_SESSION["codigoInstitucion"]=='006')
			$lista=32;*/
			
		/*if($_SESSION["codigoInstitucion"]=='007')
			$lista=25;*/
		/*if($_SESSION["codigoInstitucion"]=='008')
	$lista=32;*/
	}

	//$lista=;
	
	
	
	/*if($idRegistro==52558)
		$lista=49;*/
	//$lista=15;
	return "'".$lista."'";	

}


function obtenerUnidadGestionSiguienteAsignacion($tAudiencia,$listaUnidades) //OK
{
	//Version 2;
	
	global $con;	
	
	$consulta="SELECT  tipoAtencion FROM _4_tablaDinamica WHERE id__4_tablaDinamica=".$tAudiencia;
	$tAtencion=$con->obtenerValor($consulta);
	
	if($tAtencion=="")
		$tAtencion=2;
	
	
	$consulta="SELECT id__4_tablaDinamica FROM _4_tablaDinamica WHERE tipoAtencion=".$tAtencion;
	$lAudiencias=$con->obtenerListaValores($consulta);
	if($lAudiencias=="")
		$lAudiencias=-1;


	$arrUnidadesGestion=array();	
		
	$arrUnidadesPosibles=explode(",",$listaUnidades);
	
	foreach($arrUnidadesPosibles as $u)	
	{
		$consulta="SELECT claveUnidad FROM _17_tablaDinamica WHERE id__17_tablaDinamica=".$u;
		$ugj=$con->obtenerValor($consulta);
		
		$consulta="SELECT c.fechaCreacion,c.carpetaAdministrativa FROM _46_tablaDinamica t,7006_carpetasAdministrativas c 
					WHERE t.idEstado>1.4 and t.tipoAudiencia in(".$lAudiencias.") 
					AND t.carpetaAdministrativa =c.carpetaAdministrativa AND c.unidadGestion='".$ugj.
					"'   order by c.fechaCreacion desc limit 0,1";
		$fSolicitud=$con->obtenerPrimeraFila($consulta);
		
		$arrUnidadesGestion[$u]["ultimaAsignacion"]=$fSolicitud[0];
		$arrUnidadesGestion[$u]["ultimaCarpeta"]=$fSolicitud[1];
			
	}
	
	
	$idUnidad=NULL;
	$fechaReferencia=NULL;
	foreach($arrUnidadesGestion as $u=>$resto)
	{
		if($idUnidad==NULL)	
		{
			$idUnidad=$u;
			$fechaReferencia=strtotime($resto["ultimaAsignacion"]);
		}
		
		if($fechaReferencia>strtotime($resto["ultimaAsignacion"]))
		{
			$idUnidad=$u;
			$fechaReferencia=strtotime($resto["ultimaAsignacion"]);
		}
		
		
	}
	

	if($idUnidad==NULL)
		return -1;
	else
		return $idUnidad;
	
}

function asignarCarpetaGuardiaV3($carpetaAdministrativa,$fechaRegistro)
{
	global $con;
	
	
	
	
	$tmeFechaRegistro=strtotime($fechaRegistro);
	$tmeFechaHoraReferenciaGuardia=strtotime(date("Y-m-d ",$tmeFechaRegistro)." 13:30");
	
	$tipoHorario=1;
	$consulta="SELECT unidadGestion FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$carpetaAdministrativa."'";
	$unidad=$con->obtenerValor($consulta);
	
	$consulta="SELECT tipoDelito FROM _17_gridDelitosAtiende d,_17_tablaDinamica u WHERE d.idReferencia=u.id__17_tablaDinamica
				AND u.claveUnidad='".$unidad."' and tipoDelito in('A','B','M')";

	$tipoDelito=$con->obtenerValor($consulta);
	if($tipoDelito=="M")
		$tipoDelito="B";
	if(($tipoDelito=="A")||($tipoDelito=="B"))
		$tipoHorario=determinarTipoHorarioGeneral($fechaRegistro);
	
	
	
	if($tipoHorario==2)
	{
		$consulta="SELECT idReferencia FROM _17_gridDelitosAtiende WHERE tipoDelito='".$tipoDelito."' and idReferencia in 
						(SELECT unidadGestion FROM _290_tablaDinamica WHERE '".date("Y-m-d",$tmeFechaRegistro).
								"'>=fechaInicial AND '".date("Y-m-d",$tmeFechaRegistro)."'<=fechaFinal) limit 0,2";

	
		$idUnidadGestion=$con->obtenerListaValores($consulta);

		if($con->filasAfectadas>1)
		{
			if($tmeFechaRegistro<$tmeFechaHoraReferenciaGuardia)
			{
				$consulta="SELECT unidadGestion FROM _290_tablaDinamica WHERE fechaFinal='".date("Y-m-d",$tmeFechaRegistro)."'
							and unidadGestion in(".$idUnidadGestion.") order by id__290_tablaDinamica limit 0,1";
				$idUnidadGestion=$con->obtenerListaValores($consulta);	
			}
			else
			{
				$consulta="SELECT unidadGestion FROM _290_tablaDinamica WHERE fechaInicial='".date("Y-m-d",$tmeFechaRegistro)."'
							and unidadGestion in(".$idUnidadGestion.") order by id__290_tablaDinamica limit 0,1";
				$idUnidadGestion=$con->obtenerListaValores($consulta);
			}
		}
		
		
		
		if($idUnidadGestion=="")
			return -1;
		else
		{
			$consulta="SELECT claveUnidad FROM _17_tablaDinamica WHERE id__17_tablaDinamica=".$idUnidadGestion;
			$unidadGuardia=$con->obtenerValor($consulta);	
			if($unidad==$unidadGuardia)
				return -1;
			return $unidadGuardia;
		}
		
	}
	
	return -1;
	
	
}

function generarPropuestaFechaAudienciaV3($idFormulario,$idRegistro) //OK
{
	global $con;
	$consulta="SELECT tipoAudiencia FROM _46_tablaDinamica WHERE id__46_tablaDinamica=".$idRegistro;
	$tipoAudiencia=$con->obtenerValor($consulta);
		
	$idEventoAudiencia=obtenerFechaAudienciaSolicitudInicialV3($idFormulario,$idRegistro,-1,$tipoAudiencia);
	return ($idEventoAudiencia!=-1);
}

function obtenerFechaAudienciaSolicitudInicialV3($idFormulario,$idRegistro,$idReferencia,$tipoAudiencia,$nivel=4) //Ok
{
	global $con;
	$horarioMinimo="09:00";
	$horarioMaximo="19:00";
	
	$totalUltimasAudienciasEstimaDuracion=0;
	$consulta="SELECT idRegistroEvento,fechaEvento,horaInicioEvento,horaFinEvento,idEdificio,idCentroGestion,idSala 
				FROM 7000_eventosAudiencia where  idFormulario=".$idFormulario." and idRegistroSolicitud=".$idRegistro.
				" and idReferencia=".$idReferencia;

	$fEventoAudiencia=$con->obtenerPrimeraFila($consulta);

	$idEvento=$fEventoAudiencia[0];
	/*if($_SESSION["idUsr"]==1)
		$fEventoAudiencia=NULL;*/
		
	//$fEventoAudiencia=NULL;
	
	if((!$fEventoAudiencia)||($fEventoAudiencia[2]==""))
	{
		$oDatosAudiencia=array();
		
		$fechaAudiencia="";
		
		$oDatosAudiencia["idRegistroEvento"]=$fEventoAudiencia[0];
		$oDatosAudiencia["idEdificio"]=$fEventoAudiencia[4];
		$oDatosAudiencia["idUnidadGestion"]=$fEventoAudiencia[5];
		$oDatosAudiencia["idSala"]=$fEventoAudiencia[6];
		$oDatosAudiencia["fecha"]="";
		$oDatosAudiencia["horaInicio"]="";
		$oDatosAudiencia["horaFin"]="";
		$oDatosAudiencia["jueces"]="";		
		
		$oDatosParametros=array();
		$oDatosParametros["idFormulario"]=$idFormulario;
		$oDatosParametros["idRegistro"]=$idRegistro;
		$oDatosParametros["idReferencia"]=$idReferencia;
		$oDatosParametros["tipoAudiencia"]=$tipoAudiencia;
		$oDatosParametros["oDatosAudiencia"]=$oDatosAudiencia;
		$oDatosParametros["notificarMAJO"]=false;
		$oDatosParametros["nivelAsignacion"]=$nivel; //1 Hasta UGJ; 2 Total
		

		$consulta="SELECT  horaMinimasAudiencia,agendaDiaNoHabil,horasMaximaAgendaAudiencia,tipoAtencion,promedioDuracion FROM 
					_4_tablaDinamica WHERE id__4_tablaDinamica=".$tipoAudiencia;
		$fDatosTipoAudiencia=$con->obtenerPrimeraFila($consulta);
		
		
		$margenPreparacionSala=15;			
		
		$fechaSolicitud="";
		
		$cache=NULL;
		$cadObj='{"idFormulario":"'.$idFormulario.'","idRegistro":"'.$idRegistro.'","idReferencia":"'.$idReferencia.'","tipoAudiencia":"'.$tipoAudiencia.'"}';
		
		$obj=json_decode($cadObj);
		
		$consulta="SELECT fechaCreacion FROM _46_tablaDinamica WHERE id__46_tablaDinamica=".$idRegistro;
		$fechaSolicitud=$con->obtenerValor($consulta);
		
		
		$esSolicitudUgente=$fDatosTipoAudiencia[3]==2; 
		$considerarDiaHabil=$fDatosTipoAudiencia[1]==0;	

		$horasMinimasAudiencia=$fDatosTipoAudiencia[0];
		$horasMaximaAudiencia=$fDatosTipoAudiencia[2];
		$duracionAudiencia=$fDatosTipoAudiencia[4];		
		
		$fechaBaseAudiencia=date("Y-m-d H:i:s");	
		

		$fechaMaximaAudiencia=NULL;
		if($horasMaximaAudiencia>0)
			$fechaMaximaAudiencia=date("Y-m-d H:i:s",strtotime("+".($horasMaximaAudiencia*1)." hours",strtotime($fechaSolicitud)));
		
		
		/*if($fechaAudiencia!=date("Y-m-d",strtotime($fechaBaseAudiencia)))
		{
			$fechaMinimaAudiencia=$fechaAudiencia." ".$horarioMinimo;
		}
		else*/
			$fechaMinimaAudiencia=date("Y-m-d H:i:s",strtotime("+".($horasMinimasAudiencia*1)." hours",strtotime($fechaBaseAudiencia)));  //Parametro

		if(($fechaMaximaAudiencia!=NULL)&&($considerarDiaHabil))
		{
			$fechaMaximaAudiencia=obtenerHorasAjusteDiasNoHabiles($fechaMinimaAudiencia,$fechaMaximaAudiencia);
		}
		
		if(($fechaMaximaAudiencia!=NULL)&&(strtotime($fechaMinimaAudiencia)>strtotime($fechaMaximaAudiencia)))
		{
			$fechaMaximaAudiencia=NULL;
		}
		
		$fTemp=strtotime($fechaMinimaAudiencia);
		$minutos=date("i",$fTemp);

		if($minutos<=15)
			$fechaMinimaAudiencia=date("Y-m-d H:00:00",$fTemp);
		else
			if($minutos<=30)
				$fechaMinimaAudiencia=date("Y-m-d H:30:00",$fTemp);
			else
			{
				
				if(date("H",$fTemp)!=23)
				{
					$fechaMinimaAudiencia=date("Y-m-d ".(date("H",strtotime("+1 hours",$fTemp))).":00:00",$fTemp);
				}
				else
				{
					$fechaMinimaAudiencia=date("Y-m-d ",strtotime("+1 days",$fTemp))." 00:00:00";
				}
			}
			
		if($totalUltimasAudienciasEstimaDuracion>0)	
		{
			$consulta="SELECT horaTerminoReal,horaInicioReal FROM 7000_eventosAudiencia WHERE tipoAudiencia=".$tipoAudiencia.
					" AND  horaInicioReal IS NOT NULL order by horaInicioEvento desc limit 0,".$totalUltimasAudienciasEstimaDuracion;
			$resAudiencia=$con->obtenerFilas($consulta);
			if($con->filasAfectadas==$totalUltimasAudienciasEstimaDuracion)
			{
				$totalMinutos=0;
				while($fAudiencia=mysql_fetch_row($resAudiencia))
				{
					$totalMinutos+=obtenerDiferenciaMinutos($fAudiencia[1],$fAudiencia[0]);
				}
				
				$duracionAudiencia=floor($totalMinutos/$fConfiguracion["promedioTiempo"])+$margen;
				
			}
		}
		
		$oDatosParametros["juecesRequeridos"]=array();
		$consulta="SELECT tipoJuez,titulo FROM _4_gridJuecesRequeridos WHERE idReferencia=".$tipoAudiencia;
		$rJueces=$con->obtenerFilas($consulta);	
		while($fJueces=mysql_fetch_row($rJueces))
		{
			$oJuez=array();
			$oJuez["tipoJuez"]=$fJueces[0];
			$oJuez["titulo"]=$fJueces[1];
			$oJuez["idUsuario"]="";
			if($oJuez["titulo"]=="")
			{
				$consulta="SELECT tipoJuez FROM _18_tablaDinamica WHERE id__18_tablaDinamica=".$oJuez["tipoJuez"];
				$oJuez["titulo"]=$con->obtenerValor($consulta);
			}
			array_push($oDatosParametros["juecesRequeridos"],$oJuez);
		}		
		
		$oDatosParametros["fechaSolicitud"]=$fechaSolicitud;
		$oDatosParametros["duracionAudiencia"]=$duracionAudiencia;			
		$oDatosParametros["fechaMaximaAudiencia"]=$fechaMaximaAudiencia;
		$oDatosParametros["fechaMinimaAudiencia"]=$fechaMinimaAudiencia;
		$oDatosParametros["considerarDiaHabil"]=false;//$considerarDiaHabil;
		$oDatosParametros["esSolicitudUgente"]=$esSolicitudUgente;		
		$oDatosParametros["fechaBaseSolicitud"]=date("Y-m-d H:i:s");
		$oDatosParametros["idJuezSugerido"]=-1;
		$oDatosParametros["intervaloTiempoEvento"]=$margenPreparacionSala;
		$oDatosParametros["permitirExcederHoraFinal"]=1;
		$oDatosParametros["validaJuezTramite"]=true;
		$oDatosParametros["validaIncidenciaJuez"]=true;
		
		$oDatosParametros["tipoRonda"]=$oDatosParametros["esSolicitudUgente"]?"AU":"AN";

		$oEvento=generarFechaAudienciaSolicitudV3($oDatosParametros);
		/*if($_SESSION["idUsr"]==1)
		{
			
			varDump($oEvento);
			return;
		}*/
		
		
		if(gettype($oEvento)=="array")
		{
			$situacion=0;
			$etapaProcesal=1;
			$idEvento=registrarEventoAudiencia($oEvento,$idFormulario,$idRegistro,$idReferencia,$tipoAudiencia,$etapaProcesal,$situacion,$oDatosParametros);
		}
		else
			$idEvento=$oEvento;
		
	}
	return $idEvento;
}

function obtenerSiguienteFechaAsignable($fechaBase,$fechaMaxima,$considerarDiaHabil) //OK
{
	global $con;
	
	
	$fechaInicialBusqueda=strtotime($fechaBase);
	
	if($considerarDiaHabil)
	{				
		$esDiaHabil=removerComillasLimite(esDiaHabilInstitucion(date("Y-m-d",$fechaInicialBusqueda)));
		
		if(!$esDiaHabil)
		{
			$fBase=date("Y-m-d",strtotime("+1 days",$fechaInicialBusqueda));
			$fechaInicialBusqueda=obtenerSiguienteFechaAsignable($fBase,$fechaMaxima,$considerarDiaHabil);
			return $fechaInicialBusqueda;
		}
			
	}
	if($fechaMaxima)
	{
		if($fechaInicialBusqueda>strtotime($fechaMaxima))
			return NULL;
	}
	return date("Y-m-d",$fechaInicialBusqueda); 	

	
}

function generarPropuestaFechaAudienciaIntermediaV3($idFormulario,$idRegistro)//OK
{

	global $con;
	$consulta="SELECT tipoAudiencia FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;

	$tipoAudiencia=$con->obtenerValor($consulta);
	

	$idEventoAudiencia=obtenerFechaAudienciaSolicitudIntermediaV3($idFormulario,$idRegistro,-1,$tipoAudiencia);
	return ($idEventoAudiencia!=-1);
}

function obtenerFechaAudienciaSolicitudIntermediaV3($idFormulario,$idRegistro,$idReferencia,$tipoAudiencia,$nivel=4)//ok
{
	global $con;
	$horarioMinimo="09:00";
	$horarioMaximo="18:00";
	
	$margenPreparacionSala=15;
	$totalUltimasAudienciasEstimaDuracion=0;
	$consulta="SELECT idRegistroEvento,fechaEvento,horaInicioEvento,horaFinEvento,idEdificio,idCentroGestion,idSala 
				FROM 7000_eventosAudiencia where  idFormulario=".$idFormulario." and idRegistroSolicitud=".$idRegistro.
				" and idReferencia=".$idReferencia;

	$fEventoAudiencia=$con->obtenerPrimeraFila($consulta);

	$idEvento=$fEventoAudiencia[0];
	//$fEventoAudiencia=NULL;
	if((!$fEventoAudiencia)||($fEventoAudiencia[2]==""))
	{
		$oDatosAudiencia=array();
		
		
		$consulta="SELECT * FROM _185_tablaDinamica WHERE id__185_tablaDinamica=".$idRegistro;
		$fDatosSolicitud=$con->obtenerPrimeraFilaAsoc($consulta);
		$fechaSolicitud=$fDatosSolicitud["fechaCreacion"];	
		
		$consulta="SELECT unidadGestion FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$fDatosSolicitud["carpetaAdministrativa"]."'";
		$unidadGestion=$con->obtenerValor($consulta);
		
		$consulta="SELECT id__17_tablaDinamica,idReferencia FROM _17_tablaDinamica WHERE claveUnidad='".$unidadGestion."'";
		$fUnidadGestion=$con->obtenerPrimeraFila($consulta);
		
		
		$oDatosAudiencia["idRegistroEvento"]=$fEventoAudiencia[0];
		$oDatosAudiencia["idEdificio"]=$fUnidadGestion[1];
		$oDatosAudiencia["idUnidadGestion"]=$fUnidadGestion[0];
		
		$oDatosAudiencia["idSala"]="";
		$oDatosAudiencia["fecha"]="";
		$oDatosAudiencia["horaInicio"]="";
		$oDatosAudiencia["horaFin"]="";
		$oDatosAudiencia["jueces"]="";		
		
		
		$oDatosParametros=array();
		$oDatosParametros["idFormulario"]=$idFormulario;
		
		$oDatosParametros["idRegistro"]=$idRegistro;
		$oDatosParametros["idReferencia"]=$idReferencia;
		$oDatosParametros["tipoAudiencia"]=$tipoAudiencia;
		$oDatosParametros["oDatosAudiencia"]=$oDatosAudiencia;
		$oDatosParametros["notificarMAJO"]=false;
		$oDatosParametros["nivelAsignacion"]=$nivel; //1 Hasta UGJ; 2 Total
		
		
		$consulta="SELECT promedioDuracion,horasMaximaAgendaAudiencia,agendaDiaNoHabil,tipoAtencion,horaMinimasAudiencia
				FROM _4_tablaDinamica WHERE id__4_tablaDinamica=".$tipoAudiencia;
		$fDatosTiposAudiencia=$con->obtenerPrimeraFila($consulta);
		
		$esSolicitudUgente=($fDatosTiposAudiencia[3]==2); //Parametro
		
		$considerarDiaHabil=$fDatosTiposAudiencia[2]==0;	
		
		$horasMinimasAudiencia=$fDatosTiposAudiencia[4];
		$horasMaximaAudiencia=$fDatosTiposAudiencia[1];
		$duracionAudiencia=$fDatosTiposAudiencia[0];	
		$fechaBaseAudiencia=date("Y-m-d H:i:s");
		
		$fechaMaximaAudiencia=NULL;
		if($horasMaximaAudiencia>0)
			$fechaMaximaAudiencia=date("Y-m-d H:i:s",strtotime("+".($horasMaximaAudiencia*1)." hours",strtotime($fechaSolicitud)));
			
		$fechaMinimaAudiencia=date("Y-m-d H:i:s",strtotime("+".($horasMinimasAudiencia*1)." hours",strtotime($fechaBaseAudiencia)));  //Parametro

		if(($fechaMaximaAudiencia!=NULL)&&($considerarDiaHabil))
		{
			$fechaMaximaAudiencia=obtenerHorasAjusteDiasNoHabiles($fechaMinimaAudiencia,$fechaMaximaAudiencia);
		}
		
		if(($fechaMaximaAudiencia!=NULL) &&  (strtotime($fechaMaximaAudiencia)<strtotime(date("Y-m-d H:i:s"))))
		{
			$fechaMaximaAudiencia=NULL;
		}
		
		
		
		if($fDatosSolicitud["fechaEstimadaAudiencia"]!="")
		{
			if($fDatosSolicitud["fechaEstimadaAudiencia"]!=date("Y-m-d"))
				$fechaMinimaAudiencia=$fDatosSolicitud["fechaEstimadaAudiencia"]." ".$horarioMinimo;
			//$fechaMaximaAudiencia=NULL;
		}
		
		
		
		if(($fechaMaximaAudiencia!=NULL)&&(strtotime($fechaMinimaAudiencia)>strtotime($fechaMaximaAudiencia)))
		{
			$fechaMaximaAudiencia=NULL;
		}
		
		$fTemp=strtotime($fechaMinimaAudiencia);
		$minutos=date("i",$fTemp);

		if($minutos<=15)
			$fechaMinimaAudiencia=date("Y-m-d H:00:00",$fTemp);
		else
			if($minutos<=30)
				$fechaMinimaAudiencia=date("Y-m-d H:30:00",$fTemp);
			else
			{
				
				if(date("H",$fTemp)!=23)
				{
					$fechaMinimaAudiencia=date("Y-m-d ".(date("H",strtotime("+1 hours",$fTemp))).":00:00",$fTemp);
				}
				else
				{
					$fechaMinimaAudiencia=date("Y-m-d ",strtotime("+1 days",$fTemp))." 00:00:00";
				}
			}
		
		
		if($totalUltimasAudienciasEstimaDuracion>0)	
		{
			$consulta="SELECT horaTerminoReal,horaInicioReal FROM 7000_eventosAudiencia WHERE tipoAudiencia=".$tipoAudiencia.
						" AND  horaInicioReal IS NOT NULL order by horaInicioEvento desc limit 0,".$totalUltimasAudienciasEstimaDuracion;
			$resAudiencia=$con->obtenerFilas($consulta);
			if($con->filasAfectadas==$totalUltimasAudienciasEstimaDuracion)
			{
				$totalMinutos=0;
				while($fAudiencia=mysql_fetch_row($resAudiencia))
				{
					$totalMinutos+=obtenerDiferenciaMinutos($fAudiencia[1],$fAudiencia[0]);
				}
				
				$duracionAudiencia=floor($totalMinutos/$fConfiguracion["promedioTiempo"])+$margen;
				
			}
		}
		
		$oDatosParametros["juecesRequeridos"]=array();
		$consulta="SELECT tipoJuez,titulo FROM _4_gridJuecesRequeridos WHERE idReferencia=".$tipoAudiencia;

		$rJueces=$con->obtenerFilas($consulta);	
		while($fJueces=mysql_fetch_row($rJueces))
		{
			$oJuez=array();
			$oJuez["tipoJuez"]=$fJueces[0];
			$oJuez["titulo"]=$fJueces[1];
			$oJuez["idUsuario"]="";
			if($oJuez["titulo"]=="")
			{
				$consulta="SELECT tipoJuez FROM _18_tablaDinamica WHERE id__18_tablaDinamica=".$oJuez["tipoJuez"];
				$oJuez["titulo"]=$con->obtenerValor($consulta);
			}
			array_push($oDatosParametros["juecesRequeridos"],$oJuez);
		}
		
		$oDatosParametros["fechaSolicitud"]=$fechaSolicitud;	
		$oDatosParametros["duracionAudiencia"]=$duracionAudiencia;			
		$oDatosParametros["fechaMaximaAudiencia"]=$fechaMaximaAudiencia;
		$oDatosParametros["fechaMinimaAudiencia"]=$fechaMinimaAudiencia;
		$oDatosParametros["considerarDiaHabil"]=$considerarDiaHabil;
		
		$oDatosParametros["esSolicitudUgente"]=$esSolicitudUgente;		
		$oDatosParametros["fechaBaseSolicitud"]=date("Y-m-d H:i:s");
		$oDatosParametros["idJuezSugerido"]=(($fDatosSolicitud["juezAsignar"]!=0)&&($fDatosSolicitud["juezAsignar"]!="")&&($fDatosSolicitud["juezAsignar"]!=1))?$fDatosSolicitud["juezAsignar"]:-1;
		$oDatosParametros["intervaloTiempoEvento"]=$margenPreparacionSala;
		$oDatosParametros["permitirExcederHoraFinal"]=1;
		$oDatosParametros["validaJuezTramite"]=$oDatosParametros["idJuezSugerido"]!=-1?false:true;
		$oDatosParametros["validaIncidenciaJuez"]=true;
		$oDatosParametros["tipoRonda"]=$oDatosParametros["esSolicitudUgente"]?"AU":"AN";
		
		
		$arrAudienciasIntermedias["15"]=1;
		$arrAudienciasIntermedias["142"]=1;
		$arrAudienciasIntermedias["223"]=1;
		if(isset($arrAudienciasIntermedias[$tipoAudiencia]))
		{
			$oDatosParametros["tipoRonda"]="I";
		}
		else
		{
			
			if($fDatosSolicitud["idEventoReferencia"]!="")
			{
				$fDatosSolicitudAuxiliar=$fDatosSolicitud;
				$encontrado=false;
				while(!$encontrado)
				{
					
					$consulta="SELECT tipoAudiencia,idRegistroEvento,idFormulario,idRegistroSolicitud FROM 
								7000_eventosAudiencia WHERE idRegistroEvento=".$fDatosSolicitudAuxiliar["idEventoReferencia"];
					$fDatosEventoBusqueda=$con->obtenerPrimeraFila($consulta);
					
					$tAudiencia=$fDatosEventoBusqueda[0];
					if(isset($arrAudienciasIntermedias[$tAudiencia]))
					{
						$oDatosParametros["tipoRonda"]="I-C";
						$encontrado=true;
					}
					else
					{
						if((($tAudiencia==25)||($tAudiencia==203))&&($fDatosEventoBusqueda[2]==185))
						{
							$consulta="SELECT * FROM _185_tablaDinamica WHERE id__185_tablaDinamica=".$fDatosEventoBusqueda[3];
							
							$fDatosSolicitudAuxiliar=$con->obtenerPrimeraFilaAsoc($consulta);
							
							
							if($fDatosSolicitudAuxiliar["idEventoReferencia"]=="")
							{
								
								$encontrado=true;
							}
						}
						else
						{
							$encontrado=true;
						}
					}
				}
			}
		}
		
		
		$oEvento=generarFechaAudienciaSolicitudV3($oDatosParametros);

		/*if($_SESSION["idUsr"]==1)
		{
			varDump($oEvento);
		}*/
		

		
		
		if(gettype($oEvento)=="array")
		{
			$situacion=0;
			$etapaProcesal=1;
			$idEvento=registrarEventoAudiencia($oEvento,$idFormulario,$idRegistro,$idReferencia,$tipoAudiencia,$etapaProcesal,$situacion,$oDatosParametros);
		}
		else
			$idEvento=$oEvento;
		
	}
	return $idEvento;
}

function obtenerFechaEventoAudienciaV3($oDatosParametros,$oDatosAudiencia,$fechaAudiencia) //OK
{
	
	global $con;	
	global $tipoMateria;	
	$consulta="";

	$fechaActual=date("Y-m-d");
	$horaActual=date("Y-m-d H:i:s");
	
	$horarioInicial="09:00";
	$horarioFinalInicial="20:00";	
	
	$consulta="SELECT  tipoDelito FROM _17_gridDelitosAtiende WHERE idReferencia=".$oDatosAudiencia["idUnidadGestion"];
	$tipificacion=$con->obtenerValor($consulta);
	$idSala="";
	
	
	
	$horaMinimaDia=$horarioInicial;		
	
	if($fechaActual==$fechaAudiencia)
	{
		$horaMinimaDia=strtotime(date("H:i:s",strtotime($oDatosParametros["fechaMinimaAudiencia"])));//--- Pendiente
		
	}	
	
	$consulta="SELECT '".$horarioInicial."','".$horarioFinalInicial."'";
	$fHorario=$con->obtenerPrimeraFila($consulta);	
		
	$regHorario["hInicial"]=$fechaAudiencia." ".$fHorario[0];
	$regHorario["hFinal"]=$fechaAudiencia." ".$fHorario[1];	
	
	if(strtotime($regHorario["hInicial"])>=strtotime($regHorario["hFinal"]))
		return -1;
	
	
	if(strtotime($horaActual)>strtotime($regHorario["hFinal"]))
		return -1;
	
	$arrEventosJueces=array();
	foreach($oDatosAudiencia["jueces"]	as $juez)
	{
		$esJuezTramite=false;
		if($oDatosParametros["validaJuezTramite"])
			$esJuezTramite=esJuezTramite($juez["idUsuario"],$fechaAudiencia);
	
		$esJuezDisponibleAudiencia=true;
		if($oDatosParametros["validaIncidenciaJuez"])
			$esJuezDisponibleAudiencia=esJuezDisponibleIncidencia($juez["idUsuario"],$fechaAudiencia);
		
		if($esJuezTramite||!$esJuezDisponibleAudiencia)
			return -1;
			
		$eJuez=obtenerEventosJuez($juez["idUsuario"],$fechaAudiencia,$fechaAudiencia);
		foreach($eJuez as $e)
		{
			$fRegIncidencia=array();
			$fRegIncidencia[0]=$e[6]!=""?$e[6]:$e[2];
			$fRegIncidencia[1]=$e[7]!=""?$e[7]:$e[3];
			array_push($arrEventosJueces,$fRegIncidencia);
		}
			
	}
		
	$regHorario["tiempoMinutos"]=floor(obtenerDiferenciaMinutos($regHorario["hInicial"],$regHorario["hFinal"]));
	$salaEncontrada=false;
	
	$listaSalasIgn=-1;
	
	while(!$salaEncontrada)
	{
		$idSala=seleccionarSalaAudiencia($oDatosAudiencia["idUnidadGestion"],$fechaAudiencia,$listaSalasIgn);

		if($idSala==-1)
		{
			return -1;
		}
		
		$listaSalasIgn.=$idSala;
		
		$arrHorarios=array();
		
		array_push($arrHorarios,$regHorario);	
		
		$arrEventosSala=array();
		$consulta="SELECT horaInicioEvento,horaFinEvento FROM 7000_eventosAudiencia WHERE idSala=".$idSala.
				" AND fechaEvento='".$fechaAudiencia."' and situacion in(SELECT idSituacion FROM 7011_situacionEventosAudiencia 
				WHERE considerarDiponibilidad=1) ORDER BY horaInicioEvento";
		
		if($idSala==156)
		{
			$consulta="SELECT horaInicioEvento,horaFinEvento FROM 7000_eventosAudiencia WHERE idSala in (156,3021) 
				AND fechaEvento='".$fechaAudiencia."' and situacion in(SELECT idSituacion FROM 7011_situacionEventosAudiencia 
				WHERE considerarDiponibilidad=1) ORDER BY horaInicioEvento";
		}
		
		$resEvento=$con->obtenerFilas($consulta);			
		while($fEvento=mysql_fetch_row($resEvento))
		{
			array_push($arrEventosSala,$fEvento);
		}
		
		$aEventos=obtenerAudienciasProgramadasSede($idSala,$fechaAudiencia,$fechaAudiencia,-1);
		foreach($aEventos as $fEvento)	
		{
			array_push($arrEventosSala,$fEvento);
		}
				
		
		
		$arrHorariosBloquear=array();
		
		
		foreach($arrEventosJueces as $fRegIncidencia)
			array_push($arrHorariosBloquear,$fRegIncidencia);
		
		if(($tipoMateria=="C")&&($idSala==70))//Eliminar Sala
		{
			if(date("w",strtotime($fechaAudiencia))==4)
			{
				$fRegIncidencia[0]=$fechaAudiencia." 00:00";
				$fRegIncidencia[1]=$fechaAudiencia." 23:59";
				
				array_push($arrHorariosBloquear,$fRegIncidencia);
			}
		}
		
		
		$consulta="SELECT idPadre FROM _25_chkUnidadesAplica WHERE idOpcion=".$oDatosAudiencia["idUnidadGestion"];	
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
	
		$agendaEvento=array();
		$agendaEvento["fechaEvento"]=$fechaAudiencia;
		
		$horaInicial="";
		$horaFinal="";
		
		$arrHorarioAux=array();
		foreach($arrHorarios as $h)
		{
			$agregar=true;
			if(strtotime($h["hInicial"])<$horaMinimaDia)	
			{
				if(strtotime($h["hFinal"])<$horaMinimaDia)
				{
					$agregar=false;
				}
				else
				{
					$h["hInicial"]=date("Y-m-d H:i:s",$horaMinimaDia);
					$h["tiempoMinutos"]=floor(obtenerDiferenciaMinutos($h["hInicial"],$h["hFinal"]));
					
					
				}
			}
			if($agregar)
				array_push($arrHorarioAux,$h);
		}
		$arrHorarios=$arrHorarioAux;
		
		$nPos=0;
		foreach($arrHorarios as $h)
		{
			$incrementoMinutos=0;
			
			if(($h["hInicial"]!=date("Y-m-d H:i:s",strtotime($fechaAudiencia." ".$fHorario[0])))&&($nPos>0))
			{
				$incrementoMinutos=$oDatosParametros["intervaloTiempoEvento"];
			}
			
			if(($h["tiempoMinutos"]-$incrementoMinutos)>=$oDatosParametros["duracionAudiencia"])
			{
				$horaInicial=strtotime("+".$incrementoMinutos." minutes",strtotime($h["hInicial"]));
	
				if(($horaMinimaDia=="")||($horaInicial>=$horaMinimaDia))
				{
					
					break;
				}
				else
					$horaInicial="";
			}
			else
			{
				if($h["hFinal"]==date("Y-m-d H:i:s",strtotime($fechaAudiencia." ".$fHorario[1])))
				{
					$horaInicial=strtotime("+".$incrementoMinutos." minutes",strtotime($h["hInicial"]));
					if(($horaMinimaDia=="")||($horaInicial>=$horaMinimaDia))
						break;	
					else
						$horaInicial="";
				}
			}
			$nPos++;
		}
			
		if($horaInicial=="")
			return -1;
	
		if($oDatosParametros["fechaMaximaAudiencia"]!=NULL)
		{
			if($horaInicial>strtotime($oDatosParametros["fechaMaximaAudiencia"]))	
			{
				return -1;	
			}
		}
		
		$horaFinal=strtotime("+".$oDatosParametros["duracionAudiencia"]." minutes",$horaInicial);
		
		if($oDatosParametros["permitirExcederHoraFinal"]==0)
		{
			if($horaFinal>strtotime($fechaAudiencia." ".date("H:i:s",strtotime($fHorario[1]))))
			{
				return -1;
			}
		}
		else
		{
			if($horaInicial>strtotime($fechaAudiencia." ".date("H:i:s",strtotime($fHorario[1]))))
			{
	
				return -1;
			}
		}
		
		
		$agendaEvento["horaInicial"]=date("Y-m-d H:i:s",$horaInicial);
		$agendaEvento["horaFinal"]=date("Y-m-d H:i:s",$horaFinal);	
		$agendaEvento["idSala"]=$idSala;
		return $agendaEvento;	
	}
}

function seleccionarSalaAudiencia($idUnidadGestion,$fechaAudiencia,$listaSalasIgn)//OK
{
	global $con;
	
	
	$arrSalas=array();
	
	$universoTiempo=1440; //minutos al dia
	
	$arrSalasIgnorar=explode(",",$listaSalasIgn);
	$consulta="SELECT salasVinculadas FROM _55_tablaDinamica WHERE idReferencia=".$idUnidadGestion;
	
	$listaSalas=$con->obtenerListaValores($consulta);
	if($listaSalas=="")
		$listaSalas=-1;
		
	$arrSalasPosibles=explode(",",$listaSalas);

	foreach($arrSalasPosibles as $s)	
	{
		if(existeValor($arrSalasIgnorar,$s))
			continue;
		$resultado=obtenerTotalTiempoAsignado($s,$fechaAudiencia,$fechaAudiencia);
		$arrSalas[$s]["totalTiempo"]=$resultado[0];
		$arrSalas[$s]["porcentajeOcupacion"]=($resultado[0]/$universoTiempo)*100;
		
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
	
}

function asignarJuezAudienciaV3(&$oDatosAudiencia,&$oDatosParametros,$tipoJuez,$listaJuecesIgn,$fechaAudiencia) //OK
{
	global $con;	
	
	
	
	$oAsignacion["tipoRonda"]=$oDatosParametros["tipoRonda"];
	$oAsignacion["noRonda"]="";
	$oAsignacion["idJuez"]="";
	
	$arrJueces=array();
	$esAudienciaInicial=false;
	$fechaAudienciaVacacion=$fechaAudiencia;	
	
	$tipoHorario=determinarTipoHorarioGeneral($oDatosParametros["fechaBaseSolicitud"]);

	$juecesGuardia=$tipoHorario==2;

	$seleccionAleatoria=true;
	$situacionCarpeta=0;
	$cAdministrativaBase=obtenerCarpetaAdministrativaProceso($oDatosParametros["idFormulario"],$oDatosParametros["idRegistro"]);

	if($cAdministrativaBase!="")
	{
		$consulta="SELECT etapaProcesalActual FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$cAdministrativaBase."'"	;
		$situacionCarpeta=$con->obtenerValor($consulta);	
	}
	
	
	if(($oDatosParametros["idJuezSugerido"]!="")&&($oDatosParametros["idJuezSugerido"]!="-1"))
	{
		$oAsignacion["noRonda"]=0;
		$oAsignacion["tipoRonda"]="AD";
		$oAsignacion["idJuez"]= $oDatosParametros["idJuezSugerido"];
		return $oAsignacion;
	}
	$nRonda=obtenerNoRondaAsignacion($oDatosAudiencia["idUnidadGestion"],$oAsignacion["tipoRonda"]);
	
	$consulta="SELECT usuarioJuez FROM _26_tablaDinamica j,_26_tipoJuez tj WHERE j.idReferencia=".$oDatosAudiencia["idUnidadGestion"]."
					and tj.idPadre=j.id__26_tablaDinamica and tj.idOpcion=".$tipoJuez;
	$listaJuecesUnidadGestion=$con->obtenerListaValores($consulta);
	if($listaJuecesUnidadGestion=="")
		$listaJuecesUnidadGestion=-1;
			
			
	if($juecesGuardia)
	{
		
		$consulta="SELECT usuarioJuez FROM _13_tablaDinamica WHERE '".$oDatosParametros["fechaSolicitud"]."'>=fechaInicio AND '".
					$oDatosParametros["fechaSolicitud"]."'<=fechaFinalizacion and usuarioJuez in(".$listaJuecesUnidadGestion.
					") and idEstado=1";

		$listaJuecesGuardia=$con->obtenerListaValores($consulta);

		if($listaJuecesGuardia!="")
		{
			$oDatosParametros["validaIncidenciaJuez"]=false;
			$oDatosParametros["validaJuezTramite"]=false;
			$listaJuecesUnidadGestion=$listaJuecesGuardia;
		}		
	}

	
	$aJueces=explode(",",$listaJuecesUnidadGestion);

	foreach($aJueces as $idJuez)
	{
		if(($idJuez=="")||($idJuez==-1))
			continue;
			
		$esJuezTramite=false;
		if($oDatosParametros["validaJuezTramite"])	
			$esJuezTramite=esJuezTramite($idJuez,$fechaAudiencia);
		
		$esJuezDisponible=true;
		if($oDatosParametros["validaIncidenciaJuez"])	
			$esJuezDisponible=esJuezDisponibleIncidencia($idJuez,$fechaAudiencia);
		
		if((!$esJuezTramite)&&($esJuezDisponible))
		{
			$arrJueces[$idJuez]="1";
		}
		
	}

	if(sizeof($arrJueces)==0)
	{
		$oAsignacion["idJuez"]= -1;
		return $oAsignacion;
	}
	
	$aJuecesAuxiliar=array();
	foreach($arrJueces as $j=>$resto)	
	{
		$consulta="SELECT fechaAsignacion FROM 7000_eventosAudiencia e,7001_eventoAudienciaJuez j,_4_tablaDinamica t WHERE j.idJuez=".$j." 
					AND j.idRegistroEvento=e.idRegistroEvento AND e.situacion  IN
					(
					SELECT idSituacion FROM 7011_situacionEventosAudiencia WHERE considerarDiponibilidad=1
					) and t.id__4_tablaDinamica=e.tipoAudiencia  ORDER BY fechaAsignacion DESC LIMIT 0,1";
		
		$aJuecesAuxiliar[$j]["ultimaAsignacion"]=$con->obtenerValor($consulta);	
	}
	
	
	
	$idJuez=NULL;
	$fechaReferencia=NULL;



	foreach($aJuecesAuxiliar as $j=>$resto)
	{
		if($idJuez==NULL)	
		{
			$idJuez=$j;
			$fechaReferencia=strtotime($resto["ultimaAsignacion"]);
		}
		
		if($fechaReferencia>strtotime($resto["ultimaAsignacion"]))
		{
			$idJuez=$j;
			$fechaReferencia=strtotime($resto["ultimaAsignacion"]);
		}
		
		
	}
	
	$oAsignacion["idJuez"]= $idJuez;
	return $oAsignacion;
	
}

function asignarJuezAudienciaV4(&$oDatosAudiencia,&$oDatosParametros,$tipoJuez,$listaJuecesIgn,$fechaAudiencia) //OK
{
	global $con;	
	$horaInicioGuardia="13:30";
	$maximoHoras=12;	
	
	$oAsignacion["tipoRonda"]=$oDatosParametros["tipoRonda"];
	$oAsignacion["noRonda"]="";
	$oAsignacion["idJuez"]="";
	
	$arrJueces=array();
	$esAudienciaInicial=false;
	$fechaAudienciaVacacion=$fechaAudiencia;	
	
	$tipoHorario=determinarTipoHorarioGeneral($oDatosParametros["fechaBaseSolicitud"]);

	$juecesGuardia=$tipoHorario==2;

	$seleccionAleatoria=true;
	$situacionCarpeta=0;
	$cAdministrativaBase=obtenerCarpetaAdministrativaProceso($oDatosParametros["idFormulario"],$oDatosParametros["idRegistro"]);

	if($cAdministrativaBase!="")
	{
		$consulta="SELECT etapaProcesalActual FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$cAdministrativaBase."'"	;
		$situacionCarpeta=$con->obtenerValor($consulta);	
	}
	
	
	if(($oDatosParametros["idJuezSugerido"]!="")&&($oDatosParametros["idJuezSugerido"]!="-1"))
	{
		$oAsignacion["noRonda"]=0;
		$oAsignacion["tipoRonda"]="AD";
		$oAsignacion["idJuez"]= $oDatosParametros["idJuezSugerido"];
		return $oAsignacion;
	}
	$nRonda=obtenerNoRondaAsignacion($oDatosAudiencia["idUnidadGestion"],$oAsignacion["tipoRonda"]);

	
	$consulta="SELECT usuarioJuez FROM _26_tablaDinamica j,_26_tipoJuez tj WHERE j.idReferencia=".$oDatosAudiencia["idUnidadGestion"]."
					and tj.idPadre=j.id__26_tablaDinamica and tj.idOpcion=".$tipoJuez;
	$listaJuecesUnidadGestion=$con->obtenerListaValores($consulta);
	if($listaJuecesUnidadGestion=="")
		$listaJuecesUnidadGestion=-1;
			
	$juecesGuardia=false;		
	if($juecesGuardia)
	{
		$oAsignacion["noRonda"]=0;
		$oAsignacion["tipoRonda"]="G";
		
		$consulta="SELECT usuarioJuez FROM _13_tablaDinamica WHERE '".$oDatosParametros["fechaSolicitud"]."'>=fechaInicio AND '".
					$oDatosParametros["fechaSolicitud"]."'<=fechaFinalizacion and usuarioJuez in(".$listaJuecesUnidadGestion.
					") and idEstado=1";

		$listaJuecesGuardia=$con->obtenerListaValores($consulta);

		
		$oDatosParametros["validaIncidenciaJuez"]=false;
		$oDatosParametros["validaJuezTramite"]=false;
		
		if($listaJuecesGuardia!="")
			$listaJuecesUnidadGestion=$listaJuecesGuardia;
			
			
		$aJueces=explode(",",$listaJuecesUnidadGestion);
		foreach($aJueces as $idJuez)
		{
			if(($idJuez=="")||($idJuez==-1))
				continue;
				
			$esJuezTramite=false;
			if($oDatosParametros["validaJuezTramite"])	
				$esJuezTramite=esJuezTramite($idJuez,$fechaAudiencia);
			
			$esJuezDisponible=true;
			if($oDatosParametros["validaIncidenciaJuez"])	
				$esJuezDisponible=esJuezDisponibleIncidencia($idJuez,$fechaAudiencia);
			
			if((!$esJuezTramite)&&($esJuezDisponible))
			{
				$arrJueces[$idJuez]="1";
			}
			
		}
	
		if(sizeof($arrJueces)==0)
		{
			$oAsignacion["idJuez"]= -1;
			return $oAsignacion;
		}
		
		$consulta="SELECT fechaInicial FROM _290_tablaDinamica WHERE unidadGestion=".$oDatosAudiencia["idUnidadGestion"].
				" AND fechaInicial<='".date("Y-m-d")."' ORDER BY fechaInicial DESC";
		
		$horaGuardiaUGA=$con->obtenerValor($consulta);
		
		$horaGuardiaUGA.=" ".$horaInicioGuardia;
		
		$aJuecesAuxiliar=array();
		foreach($arrJueces as $j=>$resto)	
		{
			$consulta="SELECT fechaAsignacion FROM 7000_eventosAudiencia e,7001_eventoAudienciaJuez j WHERE j.idJuez=".$j." 
						AND j.idRegistroEvento=e.idRegistroEvento AND e.situacion IN
						(0,1,2,4,5) and fechaAsignacion>='".$horaGuardiaUGA."'  
						ORDER BY fechaAsignacion DESC LIMIT 0,1";
			$aJuecesAuxiliar[$j]["ultimaAsignacion"]=$con->obtenerValor($consulta);	
		}
		
		$idJuez=NULL;
		$fechaReferencia=NULL;
	
	
	
		foreach($aJuecesAuxiliar as $j=>$resto)
		{
			if($idJuez==NULL)	
			{
				$idJuez=$j;
				$fechaReferencia=strtotime($resto["ultimaAsignacion"]);
			}
			
			if($fechaReferencia>strtotime($resto["ultimaAsignacion"]))
			{
				$idJuez=$j;
				$fechaReferencia=strtotime($resto["ultimaAsignacion"]);
			}
			
			
		}
		
		$oAsignacion["idJuez"]= $idJuez;
		return $oAsignacion;
	}
	else
	{
		$aJueces=explode(",",$listaJuecesUnidadGestion);
		foreach($aJueces as $idJuez)
		{
			if(($idJuez=="")||($idJuez==-1))
				continue;
			$asignacionesRonda= obtenerAsignacionesRonda($idJuez,$oAsignacion["tipoRonda"],$oDatosAudiencia["idUnidadGestion"],$nRonda);
			$nAdeudos=obtenerAsignacionesPendientes($idJuez,$oAsignacion["tipoRonda"],$oDatosAudiencia["idUnidadGestion"]);
			$arrJueces[$idJuez]["nAsignaciones"]=$asignacionesRonda;
			$arrJueces[$idJuez]["nAdeudos"]=$nAdeudos;
			if($oDatosParametros["validaJuezTramite"])	
				$arrJueces[$idJuez]["esJuezTramite"]=esJuezTramite($idJuez,$fechaAudiencia)?1:0;
			else
				$arrJueces[$idJuez]["esJuezTramite"]=0;
			if($oDatosParametros["validaIncidenciaJuez"])	
				$arrJueces[$idJuez]["esJuezIncidencia"]=esJuezDisponibleIncidencia($idJuez,$fechaAudiencia)?0:1;
			else
				$arrJueces[$idJuez]["esJuezIncidencia"]=0;
				
			/*	
			
			
			

			
			if((!$esJuezTramite)&&($esJuezDisponible))
			{
				$arrJueces[$idJuez]=$asignacionesRonda;
			}
			else
			{
				if($asignacionesRonda==0)
				{
					$arrParametros["idFormulario"]=$oDatosParametros["idFormulario"];
					$arrParametros["idRegistro"]=$oDatosParametros["idRegistro"];
					$arrParametros["fechaEvento"]=$fechaAudiencia;
					$arrParametros["idJuez"]=$idJuez;
					$arrParametros["tipoRonda"]=$oAsignacion["tipoRonda"];
					$arrParametros["noRonda"]=$nRonda;
					
					if($esJuezTramite)
					{
						$arrParametros["situacion"]=2;
					}
					else
					{
						$arrParametros["situacion"]=3;
					}
					registrarAsignacionJuez($arrParametros);
				}
				
			}*/
			$arrJueces[$idJuez]["horasDia"]=obtenerHorasAudienciaJuez($fechaAudiencia,$idJuez,true);
		}
		
		
		$menorValor=-1;
		foreach($arrJueces as $idJuez=>$resto)
		{
			if($menorValor==-1)
				$menorValor=$resto["nAsignaciones"];
			if($menorValor>$resto["nAsignaciones"])
				$menorValor=$resto["nAsignaciones"];
		}
		
		$menorCarga=-1;
		foreach($arrJueces as $idJuez=>$resto)
		{
			if($menorCarga==-1)
				$menorCarga=$resto["horasDia"];
			if($menorCarga>$resto["horasDia"])
				$menorCarga=$resto["horasDia"];
		}		
		
		$arrJuecesMenorAsignacion=array();
		$arrJuecesNoDisponibles=array();
		$arrJuecesMenorCarga=array();
		$arrUnionJuecesMenor=array();
		foreach($arrJueces as $j=>$nAsignaciones)
		{
			if(($nAsignaciones["esJuezTramite"]+$nAsignaciones["esJuezIncidencia"])>0)
			{
				$arrJuecesNoDisponibles[$j]=$nAsignaciones;
			}
			else
			{
				if($nAsignaciones["nAsignaciones"]==$menorValor)
				{
					$arrJuecesMenorAsignacion[$j]=$nAsignaciones;
	
				}
			}
		}
		
		foreach($arrJueces as $j=>$nAsignaciones)
		{
			if(($nAsignaciones["esJuezTramite"]+$nAsignaciones["esJuezIncidencia"])==0)
			{
				
				if($nAsignaciones["horasDia"]==$menorCarga)
				{
					$arrJuecesMenorCarga[$j]=$nAsignaciones;
	
				}
			}
		}
		
		foreach($arrJuecesMenorAsignacion as $j=>$nAsignaciones)
		{
			if(isset($arrJuecesMenorCarga[$j]))
			{
				array_push($arrUnionJuecesMenor,$j);

			}
		}
		
		
		if(sizeof($arrUnionJuecesMenor)>0) //Caso 1
		{
			$oAsignacion["noRonda"]=$nRonda;
			$oAsignacion["idJuez"]=$arrUnionJuecesMenor[0];
			return $oAsignacion;
		}
		else
		{
			
		}
		
		
		
		if(sizeof($arrJueces)==0)
		{
			$oAsignacion["idJuez"]= -1;
			return $oAsignacion;
		}
		
	}
	
	
	
	
}

function registrarAsignacionJuez($arrParametros)
{
	
	global $con;
	if($con->existeTabla("7001_asignacionesJuezAudiencia"))
	{
		$consulta="INSERT INTO 7001_asignacionesJuezAudiencia(idFormulario,idRegistro,fechaEvento,idJuez,tipoRonda,noRonda,
					situacion,fechaRegistro,idUnidadGestion,idEventoAudiencia,comentariosAdicionales) 
					VALUES(".$arrParametros["idFormulario"].",".$arrParametros["idRegistro"].",'".$arrParametros["fechaEvento"]."',".$arrParametros["idJuez"].
					",'".$arrParametros["tipoRonda"]."',".$arrParametros["noRonda"].",".$arrParametros["situacion"].",'".date("Y-m-d H:i:s")."',".
					$arrParametros["idUnidadGestion"].",".
					((isset($arrParametros["idEventoAudiencia"])&&($arrParametros["idEventoAudiencia"]!=""))?$arrParametros["idEventoAudiencia"]:-1).
					",'".(isset($arrParametros["comentariosAdicionales"])?cv($arrParametros["comentariosAdicionales"]):"")."')";
		if(strpos("_G",$arrParametros["tipoRonda"])===false)					
			actualizarNoRondaAsignacion($arrParametros["idUnidadGestion"],$arrParametros["tipoRonda"],$arrParametros["noRonda"]);
		return $con->ejecutarConsulta($consulta);

	}
	return true;


}

function obtenerAsignacionesPendientes($idJuez,$tipoRonda,$idUnidadGestion,$noRonda)
{
	global $con;
	$consulta="SELECT  COUNT(*) FROM 7001_asignacionesJuezAudiencia WHERE idJuez=".$idJuez." AND tipoRonda='".
			$tipoRonda."' AND idUnidadGestion=".$idUnidadGestion." AND situacion=4 and noRonda<>".$noRonda;
	$nAsignacionesPendientes=$con->obtenerValor($consulta);
	return $nAsignacionesPendientes;
}

function obtenerAsignacionesRonda($idJuez,$tipoRonda,$idUnidadGestion,$noRonda)
{
	global $con;
	$consulta="SELECT  COUNT(*) FROM 7001_asignacionesJuezAudiencia WHERE idJuez=".$idJuez." AND tipoRonda='".
			$tipoRonda."' AND idUnidadGestion=".$idUnidadGestion." AND situacion in(1,2,3,4,5,6,7) and noRonda=".$noRonda.
			" and rondaPagada is null";

	$nAsignaciones=$con->obtenerValor($consulta);
	return $nAsignaciones;
}

function  obtenerHorasAudienciaJuez($fecha,$idJuez,$considerarSinConfirmar=false)
{
	global $con;
	
	
	
	$situacion="1,2,4,5";
	if($considerarSinConfirmar)
		$situacion="0,1,2,4,5";
	
	$totalMinutos=0;
	$consulta="SELECT IF(horaInicioReal IS NULL,horaInicioEvento,horaInicioReal),
				IF(horaTerminoReal IS NULL,horaFinEvento,horaTerminoReal) FROM 7000_eventosAudiencia e,7001_eventoAudienciaJuez ej
				WHERE fechaEvento='".$fecha."' and e.situacion in(".$situacion.") 
				and ej.idRegistroEvento=e.idRegistroEvento and ej.idJuez=".$idJuez;
				

	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))			
	{
		$diferenciaHoras=obtenerDiferenciaMinutos($fila[0],$fila[1]);
		$totalMinutos+=$diferenciaHoras<0?0:$diferenciaHoras;
	}
	$totalHoras=$totalMinutos/60;
	return $totalHoras;
	
	
}

function obtenerAsignacionesPagadasRonda($idJuez,$tipoRonda,$idUnidadGestion,$noRonda)
{
	global $con;
	$consulta="SELECT  COUNT(*) FROM 7001_asignacionesJuezAudiencia WHERE idJuez=".$idJuez." AND tipoRonda='".
			$tipoRonda."' AND idUnidadGestion=".$idUnidadGestion." AND situacion=1 and rondaPagada=".$noRonda;
	$nAsignacionesPagadas=$con->obtenerValor($consulta);
	return $nAsignacionesPagadas;
}

function obtenerNoRondaAsignacionGuardia($idUGA,$tipoRonda,$idPeriodoGuardia)
{
	global $con;
	
	$consulta="SELECT noRonda FROM 7004_seriesRondaAsignacionGuardia WHERE idUGARonda=".$idUGA." AND serieRonda='".$tipoRonda.
				"' and idPeriodoGuardia=".$idPeriodoGuardia;
	$noRonda=$con->obtenerValor($consulta);
	if($noRonda=="")
	{
		$consulta="SELECT MAX(noRonda) FROM 7004_seriesRondaAsignacionGuardia WHERE idUGARonda=".$idUGA." AND serieRonda='".$tipoRonda."'";
		
		$noRonda=$con->obtenerValor($consulta);
		if($noRonda=="")
			$noRonda=1;
		else
			$noRonda++;
		$consulta="INSERT INTO 7004_seriesRondaAsignacionGuardia(idUGARonda,serieRonda,noRonda,idPeriodoGuardia) 
					VALUES(".$idUGA.",'".$tipoRonda."',".$noRonda.",".$idPeriodoGuardia.")";
		$con->ejecutarConsulta($consulta);		
	}	
	return $noRonda;	

}

function obtenerIdPeriodoGuardia($idUnidadGestion)
{
	global $con;
	$consulta="SELECT id__290_tablaDinamica FROM _290_tablaDinamica WHERE unidadGestion=".$idUnidadGestion.
					" AND fechaInicial<='".date("Y-m-d")."' ORDER BY fechaInicial DESC";
	$idPeriodoGuardia=$con->obtenerValor($consulta);		
	if($idPeriodoGuardia=="")
		$idPeriodoGuardia=-1;
	return $idPeriodoGuardia;
}

function generarCarpetasRemisionIncompetencia($idFormulario,$idRegistro)
{
	global $con;

	$consulta="SELECT COUNT(*) FROM _46_tablaDinamica WHERE iFormulario=".$idFormulario." AND iReferencia=".$idRegistro;

	$nRegistros=$con->obtenerValor($consulta);
	if($nRegistros>0)
	{
		return true;
	}
	
	$arrDocumentosReferencia=array();
	
	$consulta="SELECT idDocumento FROM 9074_documentosRegistrosProceso WHERE idFormulario=".$idFormulario." 
					AND idRegistro=".$idRegistro." AND tipoDocumento in(2,1)";
	
	$res=$con->obtenerFilas($consulta);
	while($fDocumento=mysql_fetch_row($res))
	{
		array_push($arrDocumentosReferencia,$fDocumento[0]);	
	}	
	
	$arrCarpetasHistorial=array();
	$arrNuevasCarpetas=array();
	$consulta="select * from _".$idFormulario."_tablaDinamica where id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$fRegistro=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$consulta="SELECT u.id__17_tablaDinamica,c.idActividad,c.carpetaAdministrativa FROM 7006_carpetasAdministrativas c,_17_tablaDinamica u WHERE 
				u.claveUnidad=c.unidadGestion AND c.idCarpeta=".$fRegistro["iCarpeta"];
	$fDatosUnidadOrigen=$con->obtenerPrimeraFila($consulta);

	$aCarpetasAntecedente=obtenerCarpetasJudicialesAntecedentes($fDatosUnidadOrigen[2]);
	$consulta="SELECT tipoCarpetaAdministrativa,idFormulario,idActividad FROM 7006_carpetasAdministrativas WHERE idCarpeta=".$fRegistro["iCarpeta"];
	
	$fInfoCarpetaJudicial=$con->obtenerPrimeraFila($consulta);
	$tCarpeta=$fInfoCarpetaJudicial[0];

	$consulta="SELECT * FROM _".$idFormulario."_imputadosRemite WHERE idPadre=".$fRegistro["id__".$idFormulario."_tablaDinamica"];

	$rImputados=$con->obtenerFilas($consulta);
	while($fImputado=mysql_fetch_row($rImputados))
	{
		$existeHistorial=false;
		if($fRegistro["motivoIncompetencia"]==4)
		{
			$consulta="SELECT c.carpetaAdministrativa,c.unidadGestion,u.id__17_tablaDinamica,c.idCarpeta FROM 7006_carpetasAdministrativas c,
					_17_tablaDinamica u,7005_relacionFigurasJuridicasSolicitud r WHERE u.claveUnidad=c.unidadGestion
					AND c.tipoCarpetaAdministrativa=".$tCarpeta." AND r.idActividad=c.idActividad AND r.idParticipante=".$fImputado[2].
					" AND r.idFiguraJuridica=4 	AND u.id__17_tablaDinamica=".$fRegistro["unidadDestino"];//considerar actividades afines
		}
		else
		{
			$consulta="SELECT c.carpetaAdministrativa,c.unidadGestion,u.id__17_tablaDinamica,c.idCarpeta FROM 7006_carpetasAdministrativas c,
					_17_tablaDinamica u,7005_relacionFigurasJuridicasSolicitud r WHERE u.claveUnidad=c.unidadGestion
					AND c.tipoCarpetaAdministrativa=".$tCarpeta." AND r.idActividad=c.idActividad AND r.idParticipante=".$fImputado[2].
					" AND r.idFiguraJuridica=4 	AND u.idReferencia=".$fRegistro["unidadReceptora"]."
					and u.id__17_tablaDinamica in(".$fRegistro["unidadesDestino"].")";//considerar actividad afines
		}
		$fDatosHistorial= NULL;
		$rDatosHistorial=$con->obtenerFilas($consulta);
		while($fDatosHistorial=mysql_fetch_row($rDatosHistorial))
		{
			
			if(isset($aCarpetasAntecedente[$fDatosHistorial[0]]))
			{
				$existeHistorial=true;
				break;
			}
			
		}
		if(!$existeHistorial)
		{
			$arrNuevasCarpetas[$fImputado[2]]=1;
		}
		else
		{ 
			if(!isset($arrCarpetasHistorial[$fDatosHistorial[3]]))
			{
				$arrCarpetasHistorial[$fDatosHistorial[3]]=array();
			}
			array_push($arrCarpetasHistorial[$fDatosHistorial[3]],$fImputado[2]);
		}
	}
	
	
	
	$consulta="SELECT contenido FROM 902_opcionesFormulario WHERE idGrupoElemento=8943 AND valor=".$fRegistro["motivoIncompetencia"];

	$motivoIncompetencia=$con->obtenerValor($consulta);
	if($fRegistro["motivoIncompetencia"]==5)
	{
		$motivoIncompetencia.=": ".$fRegistro["otroMotivoIncompetencia"];
	}
	
	
	$x=0;
	$query[$x]="begin";
	$x++;
	
	foreach($arrCarpetasHistorial as $iCarpeta=>$resto)
	{
		$consulta="SELECT u.id__17_tablaDinamica,c.idActividad,c.carpetaAdministrativa FROM 7006_carpetasAdministrativas c,_17_tablaDinamica u WHERE 
				u.claveUnidad=c.unidadGestion AND c.idCarpeta=".$iCarpeta;
		$fDatosCarpeta=$con->obtenerPrimeraFila($consulta);
		
		$idUnidadDestino=$fDatosCarpeta[0];
		foreach($resto as $imputado)
		{
			$consulta="select count(*) from 3250_asignacionIncompetencias where iFormulario=".$idFormulario.
				" and iRegistro=".$idRegistro." and idImputado=".$imputado;
			$nReg=$con->obtenerValor($consulta);
			if($nReg==0)
			{
				$query[$x]="INSERT INTO 3250_asignacionIncompetencias(iFormulario,iRegistro,idCarpeta,idImputado,tipoAccion,idUnidadDestino,fechaAsignacion)
							values(".$idFormulario.",".$idRegistro.",".$iCarpeta.",".$imputado.",0,".$idUnidadDestino.",'".date("Y-m-d H:i:s")."')";
				$x++;
			}
		}
		
		
	}
		
	foreach($arrCarpetasHistorial as $iCarpeta=>$resto)
	{
		$consulta="SELECT u.id__17_tablaDinamica,c.idActividad,c.carpetaAdministrativa FROM 7006_carpetasAdministrativas c,_17_tablaDinamica u WHERE 
				u.claveUnidad=c.unidadGestion AND c.idCarpeta=".$iCarpeta;
		$fDatosCarpeta=$con->obtenerPrimeraFila($consulta);
		$listaImputados="";
		foreach($resto as $imputado)
		{
			if($listaImputados=="")
				$listaImputados=$imputado;
			else
				$listaImputados.=",".$imputado;
		}
		$arrValores=array();
		$arrValores["idActividad"]=$fDatosCarpeta[1];
		$arrValores["motivoIncompetencia"]=$motivoIncompetencia;
		$arrValores["tipoCarpeta"]=$tCarpeta;
		$arrValores["fechaRemision"]=date("Y-m-d H:i:s");
		$arrValores["unidadAsignada"]=$fDatosCarpeta[0];
		$arrValores["carpetaAsignada"]=$fDatosCarpeta[2];
		$arrValores["carpetaOrigen"]=$fRegistro["carpetaAdministrativa"];
		$arrValores["unidadOrigen"]=$fDatosUnidadOrigen[0];
		$arrValores["listaImputados"]=$listaImputados;
		$arrValores["iFormulario"]=$idFormulario;
		$arrValores["iRegistro"]=$idRegistro;
		$arrValores["imputadoPrivadoLibertad"]=$fRegistro["privadoLibertad"];
		$arrValores["lugarInternamiento"]=$fRegistro["lugarInternamiento"];
		$arrValores["comentariosAdicionales"]=$fRegistro["comentariosAdicionales"];
		
		crearInstanciaRegistroFormulario(558,$idRegistro,3,$arrValores,$arrDocumentosReferencia,-1,992,"");
	}
	
	
	
	if(sizeof($arrNuevasCarpetas)>0)
	{
		
		switch($idFormulario)
		{
			case 554:
			
				switch($fInfoCarpetaJudicial[1])
				{
					case 46:
						$consulta="SELECT id__46_tablaDinamica,delitoGrave FROM _46_tablaDinamica  WHERE carpetaAdministrativa='".
							$fRegistro["carpetaAdministrativa"]."'";

						$fSolicitudInicial=$con->obtenerPrimeraFila($consulta);
						$idRegistroSolicitud=$fSolicitudInicial[0];
						if($idRegistro!=-1)
						{
							$delitoGrave=$fSolicitudInicial[1];
							switch($fRegistro["motivoIncompetencia"])
							{
								case 0:
									
								break;
								case 4: // Por mandanto Judicial
									$consulta="SELECT count(*) FROM _17_gridDelitosAtiende WHERE idReferencia=".$fRegistro["unidadDestino"].
											" AND tipoDelito='A'";
									$nQuerella=$con->obtenerValor($consulta);
									
									$consulta="SELECT count(*) FROM _17_gridDelitosAtiende WHERE idReferencia=".$fRegistro["unidadDestino"].
											" AND tipoDelito='B'";
									$nOficio=$con->obtenerValor($consulta);
									
									if(($nQuerella+$nOficio)>0)
									{
										if($nQuerella>0)
											$delitoGrave=0;
										else
											$delitoGrave=1;
									}
									
								break;
								default:
									$consulta="SELECT count(*) FROM _17_gridDelitosAtiende WHERE idReferencia 
											in(".$fRegistro["unidadesDestino"].
											") AND tipoDelito='A' limit 0,1";
		
									$nQuerella=$con->obtenerValor($consulta);
									
									$consulta="SELECT count(*) FROM _17_gridDelitosAtiende WHERE idReferencia 
											in(".$fRegistro["unidadesDestino"].
											") AND tipoDelito='B' limit 0,1";
		
									$nOficio=$con->obtenerValor($consulta);
		
									if(($nQuerella+$nOficio)>0)
									{
										if($nQuerella>0)
											$delitoGrave=0;
										else
											$delitoGrave=1;
									}
								break;
								
							}					
							
							$oParamAdicionales["materiaDestino"]=$fRegistro["materiaDestino"];
							$oParamAdicionales["delitoGrave"]=$delitoGrave;
							$oParamAdicionales["imputados"]=$arrNuevasCarpetas;
							$oParamAdicionales["copiarDocumentosSolicitudOriginal"]=false;
							$oParamAdicionales["motivoIncompetencia"]=$motivoIncompetencia;
							$consulta="SELECT claveFiscalia FROM _100_tablaDinamica WHERE idReferencia=".$idRegistroSolicitud;
							$claveFiscalia=$con->obtenerValor($consulta);
							
							if($claveFiscalia!=$fRegistro["fiscalia"])
								$oParamAdicionales["fiscalia"]=$fRegistro["fiscalia"];
							
							
							$idRegistroInicial=generarRegistroSolicitudInicial($idRegistroSolicitud,$idFormulario,$idRegistro,$fRegistro["comentariosAdicionales"],$oParamAdicionales);
							
							
						}
					break;
					case 622:
							$delitoGrave=0;
							switch($fRegistro["motivoIncompetencia"])
							{
								case 0:
									
								break;
								case 4: // Por mandanto Judicial
									$consulta="SELECT count(*) FROM _17_gridDelitosAtiende WHERE idReferencia=".$fRegistro["unidadDestino"].
											" AND tipoDelito='A'";
									$nQuerella=$con->obtenerValor($consulta);
									
									$consulta="SELECT count(*) FROM _17_gridDelitosAtiende WHERE idReferencia=".$fRegistro["unidadDestino"].
											" AND tipoDelito='B'";
									$nOficio=$con->obtenerValor($consulta);
									
									if(($nQuerella+$nOficio)>0)
									{
										if($nQuerella>0)
											$delitoGrave=0;
										else
											$delitoGrave=1;
									}
									
								break;
								default:
									$consulta="SELECT count(*) FROM _17_gridDelitosAtiende WHERE idReferencia 
											in(".$fRegistro["unidadesDestino"].
											") AND tipoDelito='A' limit 0,1";
		
									$nQuerella=$con->obtenerValor($consulta);
									
									$consulta="SELECT count(*) FROM _17_gridDelitosAtiende WHERE idReferencia 
											in(".$fRegistro["unidadesDestino"].
											") AND tipoDelito='B' limit 0,1";
		
									$nOficio=$con->obtenerValor($consulta);
		
									if(($nQuerella+$nOficio)>0)
									{
										if($nQuerella>0)
											$delitoGrave=0;
										else
											$delitoGrave=1;
									}
								break;
								
							}					
							$oParamAdicionales["idActividad"]=$fInfoCarpetaJudicial[2];
							$oParamAdicionales["materiaDestino"]=2;
							$oParamAdicionales["delitoGrave"]=$delitoGrave;
							$oParamAdicionales["imputados"]=$arrNuevasCarpetas;
							$oParamAdicionales["copiarDocumentosSolicitudOriginal"]=false;
							$oParamAdicionales["motivoIncompetencia"]=$motivoIncompetencia;
							$oParamAdicionales["fiscalia"]=$fRegistro["fiscalia"];
							$oParamAdicionales["carpetaRemitida"]=$fRegistro["carpetaAdministrativa"];	
							
							
							$idRegistroInicial=generarRegistroSolicitudInicialProMujer($idFormulario,$idRegistro,$fRegistro["comentariosAdicionales"],$oParamAdicionales);
							
							
					break;
				}
				
			break;
			case 556:
			
			
			
			break;
			
		}
	}
	
	$query[$x]="commit";
	$x++;
	if($con->ejecutarBloque($query))
	{
		foreach($arrCarpetasHistorial as $iCarpeta=>$resto)
		{
			$consulta="SELECT u.id__17_tablaDinamica,c.idActividad,c.carpetaAdministrativa FROM 7006_carpetasAdministrativas c,_17_tablaDinamica u WHERE 
					u.claveUnidad=c.unidadGestion AND c.idCarpeta=".$iCarpeta;
			$fDatosCarpeta=$con->obtenerPrimeraFila($consulta);
			
			$idUnidadDestino=$fDatosCarpeta[0];
			foreach($resto as $imputado)
			{
				registrarCambioSituacionImputado($fDatosCarpeta[2],$iCarpeta,$imputado,1,"","Incompetencia");
				registrarCambioSituacionImputado($fRegistro["carpetaAdministrativa"],$fRegistro["iCarpeta"],$imputado,21,"","Incompetencia");
			}
			determinarSituacionCarpeta($fDatosCarpeta[2],$iCarpeta);			
		}
		determinarSituacionCarpeta($fRegistro["carpetaAdministrativa"],$fRegistro["iCarpeta"]);
		return true;
	}
	
	
}

function generarRegistroSolicitudInicial($idRegistro,$iFormulario,$iRegistro,$comentariosAdicionales,$arrParametros=NULL)
{
	global $con;
	$consulta="SELECT COUNT(*) FROM _46_tablaDinamica WHERE iFormulario=".$iFormulario." AND iReferencia=".$iRegistro;
	$nRegistros=$con->obtenerValor($consulta);
	if($nRegistros>0)
	{
		return true;
	}
	$tipoAudiencia=91;
	if(($arrParametros!=NULL)&&(isset($arrParametros["tipoAudiencia"])))
	{
		$tipoAudiencia=$arrParametros["tipoAudiencia"];
	}
	
	$idEtapa=2.5;
	if(($arrParametros!=NULL)&&(isset($arrParametros["idEtapa"])))
	{
		$idEtapa=$arrParametros["idEtapa"];
	}
	
	$copiarDocumentosSolicitudOriginal=true;
	if(($arrParametros!=NULL)&&(isset($arrParametros["copiarDocumentosSolicitudOriginal"])))
	{
		$copiarDocumentosSolicitudOriginal=$arrParametros["copiarDocumentosSolicitudOriginal"];
	}
	
	$fechaRemision=date("Y-m-d H:i:s");
	$arrValores=array();
	$idActividad=generarIDActividad(46,$idRegistro);
	$consulta=" select folioCarpetaInvestigacion,tipoProgramacionAudiencia,tipoAudiencia,requiereResguardo,requiereTelePresencia,
				carpetaAdministrativa,idActividad, requiereMesaEvidencia, requiereTestigoProtegido,  delitoGrave, ctrlSolicitud, idSolicitud,
				cveSolicitud, solicitudXML, noFojas, textoFojas, ''  as fechaFenece,declaratoria,materiaDestino 
				from _46_tablaDinamica where id__46_tablaDinamica=".$idRegistro;

	$arrValores=$con->obtenerPrimeraFilaAsoc($consulta);

	$idActividadBase=$arrValores["idActividad"];
	$arrValores["idActividad"]=$idActividad;
	$arrValores["tipoAudiencia"]=$tipoAudiencia;
	$arrValores["iFormulario"]=$iFormulario;
	$arrValores["iReferencia"]=$iRegistro;
	$cCarpetaBase=$arrValores["carpetaAdministrativa"];
	$arrValores["carpetaAdministrativa"]="";
	$arrValores["carpetaRemitida"]=$cCarpetaBase;
	$arrValores["materiaDestino"]=$arrValores["materiaDestino"];
	

	if(($arrParametros!=NULL)&&(isset($arrParametros["delitoGrave"])))
	{
		$arrValores["delitoGrave"]=$arrParametros["delitoGrave"];
	}
	
	if(($arrParametros!=NULL)&&(isset($arrParametros["materiaDestino"])))
	{
		$arrValores["materiaDestino"]=$arrParametros["materiaDestino"];
	}
	
	if(($arrParametros!=NULL)&&(isset($arrParametros["motivoIncompetencia"])))
	{
		$arrValores["motivoIncompetencia"]=$arrParametros["motivoIncompetencia"];
	}

	$arrDocumentosReferencia=array();						
	
	if($copiarDocumentosSolicitudOriginal)
	{
		$consulta="SELECT idDocumento FROM 9074_documentosRegistrosProceso WHERE idFormulario=46 
					AND idRegistro=".$idRegistro." AND tipoDocumento in(2,1)";
	
		$res=$con->obtenerFilas($consulta);
		while($fDocumento=mysql_fetch_row($res))
		{
			array_push($arrDocumentosReferencia,$fDocumento[0]);	
		}	
	}
	
	$consulta="SELECT idDocumento FROM 9074_documentosRegistrosProceso WHERE idFormulario=".$iFormulario." 
				AND idRegistro=".$iRegistro." AND tipoDocumento in(2,1)";

	$res=$con->obtenerFilas($consulta);
	while($fDocumento=mysql_fetch_row($res))
	{
		array_push($arrDocumentosReferencia,$fDocumento[0]);	
	}	
	
	$arrValores["fechaRecepcion"]=date("Y-m-d",strtotime($fechaRemision));
	$arrValores["horaRecepcion"]=date("H:i:s",strtotime($fechaRemision));
	
	$actor=299;
	$idRegistroSolicitud=crearInstanciaRegistroFormulario(46,-1,1,$arrValores,$arrDocumentosReferencia,-1,$actor,"");

	@registrarDatosPeticionRefiere(46,$idRegistroSolicitud,46,$idRegistro);
	
	
	$idRegistroFiscalia="-1";
	$arrValores=array();
	if(($arrParametros!=NULL)&&(isset($arrParametros["fiscalia"])))
	{
		$consulta="SELECT claveCoorTerMP,nombre,apPaterno,apMaterno,curp,claveFiscalia,claveAgencia,claveUnidad,id__100_tablaDinamica 
				FROM _100_tablaDinamica WHERE idReferencia=".$idRegistro." and claveFiscalia=".$arrParametros["fiscalia"];

		$arrValores=$con->obtenerPrimeraFilaAsoc($consulta);
		if(!$arrValores)
		{
			$arrValores["claveCoorTerMP"]="";
			$arrValores["nombre"]="";
			$arrValores["apPaterno"]="";
			$arrValores["apMaterno"]="";
			$arrValores["curp"]="";
			
			$arrValores["claveFiscalia"]=$arrParametros["fiscalia"];
			$arrValores["claveAgencia"]="";
			$arrValores["claveUnidad"]="";
			$idRegistroFiscalia=0;
		}
		else	
			$idRegistroFiscalia=$arrValores["id__100_tablaDinamica"];
			
	}
	else
	{
		$consulta="SELECT claveCoorTerMP,nombre,apPaterno,apMaterno,curp,claveFiscalia,claveAgencia,claveUnidad,id__100_tablaDinamica 
					FROM _100_tablaDinamica WHERE idReferencia=".$idRegistro;
		$arrValores=$con->obtenerPrimeraFilaAsoc($consulta);
		$idRegistroFiscalia=$arrValores["id__100_tablaDinamica"];
	}
	
	if(isset($arrValores["id__100_tablaDinamica"]))
	{
		unset($arrValores["id__100_tablaDinamica"]);
	}

	
	
	if(($arrValores) &&($idRegistroFiscalia!=-1)) //Datos de fiscalia
	{
		$arrDocumentosReferencia=array();
		$idFiscalia=crearInstanciaRegistroFormulario(100,$idRegistroSolicitud,1,$arrValores,$arrDocumentosReferencia,-1,299);
		if($idRegistroFiscalia>0)
		{
			$consulta="SELECT id__100_tablaDinamica FROM _100_tablaDinamica WHERE idReferencia=".$idRegistro;
			$idFiscaliaBase=$con->obtenerValor($consulta);
					
			$consulta="INSERT INTO _100_gridCorreosFiscal(idReferencia,correoElectronico)
						SELECT '".$idFiscalia."' AS idReferencia,correoElectronico FROM _100_gridCorreosFiscal WHERE idReferencia=".$idFiscaliaBase;
			
			$con->ejecutarConsulta($consulta);	
		}
		
	}
	
	
	$consulta="select numeroExpediente,nombre,apPaterno,apMaterno,fechaRecepcion,horaRecepcion,juzgado 
				from _222_tablaDinamica where idReferencia=".$idRegistro;
				
	$arrValores=$con->obtenerPrimeraFilaAsoc($consulta);
	if($arrValores)	//Datos de incompetencia
	{
		$arrDocumentosReferencia=array();
		crearInstanciaRegistroFormulario(222,$idRegistroSolicitud,1,$arrValores,$arrDocumentosReferencia,-1,299);
	}	
	
	
	$consulta="SELECT id__61_tablaDinamica as idRegistro,-1 AS tituloDelito,-1 AS capituloDelito,denominacionDelito,calificativo,gradoRealizacion,'".$idActividad."' as idActividad,
				modalidadDelito FROM _61_tablaDinamica WHERE idActividad=".$idActividadBase;
	$res=$con->obtenerFilas($consulta);
	while($fDelito=mysql_fetch_assoc($res))
	{	
	
		$idRegistroDelito=$fDelito["idRegistro"];
		unset($fDelito["idRegistro"]);
		$arrDocumentosReferencia=array();
		$arrValores=$fDelito;
		$idDelito=crearInstanciaRegistroFormulario(61,-1,1,$arrValores,$arrDocumentosReferencia,-1,299);
		
		$consulta="INSERT INTO _61_chkDelitosImputado(idPadre,idOpcion)
					SELECT '".$idDelito."' AS idPadre,idOpcion FROM _61_chkDelitosImputado WHERE idPadre=".$idRegistroDelito;
		$con->ejecutarConsulta($consulta);
		
	}



	$listaImputados="";
	foreach($arrParametros["imputados"] as $imputado=>$resto)
	{
		if($listaImputados=="")
			$listaImputados=$imputado;
		else
			$listaImputados.=",".$imputado;
	}
	$consulta="INSERT INTO 7005_relacionFigurasJuridicasSolicitud(idActividad,idParticipante,idFiguraJuridica,situacion,idCuentaAcceso,
			etapaProcesal,situacionProcesal,detalleSituacion)
			SELECT ".$idActividad." as idActividad,idParticipante,idFiguraJuridica,situacion,idCuentaAcceso,
			etapaProcesal,situacionProcesal,detalleSituacion FROM 7005_relacionFigurasJuridicasSolicitud
			WHERE idActividad=".$idActividadBase." AND ((idParticipante IN(".$listaImputados.") AND idFiguraJuridica=4) or idFiguraJuridica=2)";

	$con->ejecutarConsulta($consulta);
	$consulta="SELECT idParticipante FROM 7005_relacionFigurasJuridicasSolicitud WHERE idActividad= ".$idActividad;
	$listaParticipantes=$con->obtenerListaValores($consulta);
	if($listaParticipantes=="")
	{
		$listaParticipantes=-1;
	}
	
	$consulta="INSERT INTO 7005_relacionFigurasJuridicasSolicitud(idActividad,idParticipante,idFiguraJuridica,situacion,idCuentaAcceso,
			etapaProcesal,situacionProcesal,detalleSituacion)
			SELECT ".$idActividad." as idActividad,idParticipante,idFiguraJuridica,situacion,idCuentaAcceso,
			etapaProcesal,situacionProcesal,detalleSituacion FROM 7005_relacionFigurasJuridicasSolicitud
			WHERE idActividad=".$idActividadBase." AND idFiguraJuridica not in(2,4) and idParticipante in
			(
			SELECT idParticipante FROM 7005_relacionParticipantes WHERE idActividad=".$idActividadBase.
			" AND idActorRelacionado IN(".$listaParticipantes.")
			)";
			
	$con->ejecutarConsulta($consulta);

	$consulta="INSERT INTO 7005_relacionFigurasJuridicasSolicitud(idActividad,idParticipante,idFiguraJuridica,situacion,idCuentaAcceso,
			etapaProcesal,situacionProcesal,detalleSituacion)
			SELECT ".$idActividad." as idActividad,idParticipante,idFiguraJuridica,situacion,idCuentaAcceso,
			etapaProcesal,situacionProcesal,detalleSituacion FROM 7005_relacionFigurasJuridicasSolicitud
			WHERE idActividad=".$idActividadBase." AND idFiguraJuridica not in(2,4) and idParticipante not in
			(
			SELECT idParticipante FROM 7005_relacionParticipantes WHERE idActividad=".$idActividadBase."
			)";
			
	$con->ejecutarConsulta($consulta);

	$consulta="SELECT idParticipante FROM 7005_relacionFigurasJuridicasSolicitud WHERE idActividad= ".$idActividad;
	$listaParticipantes=$con->obtenerListaValores($consulta);
	if($listaParticipantes=="")
	{
		$listaParticipantes=-1;
	}
	
	$consulta="INSERT INTO 7005_relacionParticipantes(idActividad,idParticipante,idFiguraJuridica,idActorRelacionado,situacion)
			SELECT ".$idActividad.",idParticipante,idFiguraJuridica,idActorRelacionado,situacion FROM 7005_relacionParticipantes
			WHERE idActividad=".$idActividadBase." AND idParticipante IN(".$listaParticipantes.")";
	$con->ejecutarConsulta($consulta);		

	
	cambiarEtapaFormulario(46,$idRegistroSolicitud,$idEtapa,$comentariosAdicionales,-1,"NULL","NULL",$actor);
	return $idRegistroSolicitud;
}


function generarRegistroSolicitudInicialProMujer($iFormulario,$iRegistro,$comentariosAdicionales,$arrParametros=NULL)
{
	global $con;
	
	$idRegistro=-1;
	$tipoAudiencia=91;
	if(($arrParametros!=NULL)&&(isset($arrParametros["tipoAudiencia"])))
	{
		$tipoAudiencia=$arrParametros["tipoAudiencia"];
	}
	
	$idEtapa=2.5;
	if(($arrParametros!=NULL)&&(isset($arrParametros["idEtapa"])))
	{
		$idEtapa=$arrParametros["idEtapa"];
	}
	
	$copiarDocumentosSolicitudOriginal=true;
	if(($arrParametros!=NULL)&&(isset($arrParametros["copiarDocumentosSolicitudOriginal"])))
	{
		$copiarDocumentosSolicitudOriginal=$arrParametros["copiarDocumentosSolicitudOriginal"];
	}
	
	$fechaRemision=date("Y-m-d H:i:s");
	$arrValores=array();
	$idActividad=generarIDActividad(46,$idRegistro);
	$consulta=" select '' as folioCarpetaInvestigacion,'1' as tipoProgramacionAudiencia,'0' as requiereResguardo,'0' requiereTelePresencia,
				'' as carpetaAdministrativa,'".$arrParametros["idActividad"]."' as idActividad,'0' as  requiereMesaEvidencia,'0' as requiereTestigoProtegido,  
				'0' as delitoGrave, '' as ctrlSolicitud, '' as idSolicitud,
				'' as cveSolicitud,'' as solicitudXML,'0' as noFojas,'' as textoFojas, 
				''  as fechaFenece,'0' as declaratoria,'2'  as materiaDestino"	;

	$arrValores=$con->obtenerPrimeraFilaAsoc($consulta);

	$idActividadBase=$arrParametros["idActividad"];
	$arrValores["idActividad"]=$idActividad;
	$arrValores["tipoAudiencia"]=$tipoAudiencia;
	$arrValores["iFormulario"]=$iFormulario;
	$arrValores["iReferencia"]=$iRegistro;
	$arrValores["carpetaAdministrativa"]="";
	$arrValores["carpetaRemitida"]=$arrParametros["carpetaRemitida"];
	$arrValores["materiaDestino"]=$arrValores["materiaDestino"];
	

	if(($arrParametros!=NULL)&&(isset($arrParametros["delitoGrave"])))
	{
		$arrValores["delitoGrave"]=$arrParametros["delitoGrave"];
	}
	
	if(($arrParametros!=NULL)&&(isset($arrParametros["materiaDestino"])))
	{
		$arrValores["materiaDestino"]=$arrParametros["materiaDestino"];
	}
	
	if(($arrParametros!=NULL)&&(isset($arrParametros["motivoIncompetencia"])))
	{
		$arrValores["motivoIncompetencia"]=$arrParametros["motivoIncompetencia"];
	}

	$arrDocumentosReferencia=array();						
	
	if($copiarDocumentosSolicitudOriginal)
	{
		$consulta="SELECT idDocumento FROM 9074_documentosRegistrosProceso WHERE idFormulario=46 
					AND idRegistro=".$idRegistro." AND tipoDocumento in(2,1)";
	
		$res=$con->obtenerFilas($consulta);
		while($fDocumento=mysql_fetch_row($res))
		{
			array_push($arrDocumentosReferencia,$fDocumento[0]);	
		}	
	}
	
	$consulta="SELECT idDocumento FROM 9074_documentosRegistrosProceso WHERE idFormulario=".$iFormulario." 
				AND idRegistro=".$iRegistro." AND tipoDocumento in(2,1)";

	$res=$con->obtenerFilas($consulta);
	while($fDocumento=mysql_fetch_row($res))
	{
		array_push($arrDocumentosReferencia,$fDocumento[0]);	
	}	
	
	$arrValores["fechaRecepcion"]=date("Y-m-d",strtotime($fechaRemision));
	$arrValores["horaRecepcion"]=date("H:i:s",strtotime($fechaRemision));
	
	$actor=299;
	$idRegistroSolicitud=crearInstanciaRegistroFormulario(46,-1,1,$arrValores,$arrDocumentosReferencia,-1,$actor,"");

	@registrarDatosPeticionRefiere(46,$idRegistroSolicitud,46,$idRegistro);
	
	
	$idRegistroFiscalia="-1";
	$arrValores=array();
	if(($arrParametros!=NULL)&&(isset($arrParametros["fiscalia"])))
	{
		$consulta="SELECT claveCoorTerMP,nombre,apPaterno,apMaterno,curp,claveFiscalia,claveAgencia,claveUnidad,id__100_tablaDinamica 
				FROM _100_tablaDinamica WHERE idReferencia=".$idRegistro." and claveFiscalia=".$arrParametros["fiscalia"];

		$arrValores=$con->obtenerPrimeraFilaAsoc($consulta);
		if(!$arrValores)
		{
			$arrValores["claveCoorTerMP"]="";
			$arrValores["nombre"]="";
			$arrValores["apPaterno"]="";
			$arrValores["apMaterno"]="";
			$arrValores["curp"]="";
			
			$arrValores["claveFiscalia"]=$arrParametros["fiscalia"];
			$arrValores["claveAgencia"]="";
			$arrValores["claveUnidad"]="";
			$arrValores["sistema"]="1";
			$idRegistroFiscalia=0;
		}
		else	
			$idRegistroFiscalia=$arrValores["id__100_tablaDinamica"];
			
	}
	else
	{
		$consulta="SELECT claveCoorTerMP,nombre,apPaterno,apMaterno,curp,claveFiscalia,claveAgencia,claveUnidad,id__100_tablaDinamica 
					FROM _100_tablaDinamica WHERE idReferencia=".$idRegistro;
		$arrValores=$con->obtenerPrimeraFilaAsoc($consulta);
		$idRegistroFiscalia=$arrValores["id__100_tablaDinamica"];
	}
	
	if(isset($arrValores["id__100_tablaDinamica"]))
	{
		unset($arrValores["id__100_tablaDinamica"]);
	}

	
	
	if(($arrValores) &&($idRegistroFiscalia!=-1)) //Datos de fiscalia
	{
		$arrDocumentosReferencia=array();
		$idFiscalia=crearInstanciaRegistroFormulario(100,$idRegistroSolicitud,1,$arrValores,$arrDocumentosReferencia,-1,299);
		if($idRegistroFiscalia>0)
		{
			$consulta="SELECT id__100_tablaDinamica FROM _100_tablaDinamica WHERE idReferencia=".$idRegistro;
			$idFiscaliaBase=$con->obtenerValor($consulta);
					
			$consulta="INSERT INTO _100_gridCorreosFiscal(idReferencia,correoElectronico)
						SELECT '".$idFiscalia."' AS idReferencia,correoElectronico FROM _100_gridCorreosFiscal WHERE idReferencia=".$idFiscaliaBase;
			
			$con->ejecutarConsulta($consulta);	
		}
		
	}
	
	
	$listaImputados="";
	foreach($arrParametros["imputados"] as $imputado=>$resto)
	{
		if($listaImputados=="")
			$listaImputados=$imputado;
		else
			$listaImputados.=",".$imputado;
	}
	$consulta="INSERT INTO 7005_relacionFigurasJuridicasSolicitud(idActividad,idParticipante,idFiguraJuridica,situacion,idCuentaAcceso,
			etapaProcesal,situacionProcesal,detalleSituacion)
			SELECT ".$idActividad." as idActividad,idParticipante,idFiguraJuridica,situacion,idCuentaAcceso,
			etapaProcesal,situacionProcesal,detalleSituacion FROM 7005_relacionFigurasJuridicasSolicitud
			WHERE idActividad=".$idActividadBase." AND ((idParticipante IN(".$listaImputados.") AND idFiguraJuridica=4) or idFiguraJuridica=2)";
	$con->ejecutarConsulta($consulta);
	$consulta="SELECT idParticipante FROM 7005_relacionFigurasJuridicasSolicitud WHERE idActividad= ".$idActividad;
	$listaParticipantes=$con->obtenerListaValores($consulta);
	if($listaParticipantes=="")
	{
		$listaParticipantes=-1;
	}
	
	$consulta="INSERT INTO 7005_relacionFigurasJuridicasSolicitud(idActividad,idParticipante,idFiguraJuridica,situacion,idCuentaAcceso,
			etapaProcesal,situacionProcesal,detalleSituacion)
			SELECT ".$idActividad." as idActividad,idParticipante,idFiguraJuridica,situacion,idCuentaAcceso,
			etapaProcesal,situacionProcesal,detalleSituacion FROM 7005_relacionFigurasJuridicasSolicitud
			WHERE idActividad=".$idActividadBase." AND idFiguraJuridica not in(2,4) and idParticipante in
			(

			SELECT idParticipante FROM 7005_relacionParticipantes WHERE idActividad=".$idActividadBase.
			" AND idActorRelacionado IN(".$listaParticipantes.")
			)";
			
	$con->ejecutarConsulta($consulta);

	$consulta="INSERT INTO 7005_relacionFigurasJuridicasSolicitud(idActividad,idParticipante,idFiguraJuridica,situacion,idCuentaAcceso,
			etapaProcesal,situacionProcesal,detalleSituacion)
			SELECT ".$idActividad." as idActividad,idParticipante,idFiguraJuridica,situacion,idCuentaAcceso,
			etapaProcesal,situacionProcesal,detalleSituacion FROM 7005_relacionFigurasJuridicasSolicitud
			WHERE idActividad=".$idActividadBase." AND idFiguraJuridica not in(2,4) and idParticipante not in
			(
			SELECT idParticipante FROM 7005_relacionParticipantes WHERE idActividad=".$idActividadBase."
			)";
			
	$con->ejecutarConsulta($consulta);

	$consulta="SELECT idParticipante FROM 7005_relacionFigurasJuridicasSolicitud WHERE idActividad= ".$idActividad;
	$listaParticipantes=$con->obtenerListaValores($consulta);
	if($listaParticipantes=="")
	{
		$listaParticipantes=-1;
	}
	
	$consulta="INSERT INTO 7005_relacionParticipantes(idActividad,idParticipante,idFiguraJuridica,idActorRelacionado,situacion)
			SELECT ".$idActividad.",idParticipante,idFiguraJuridica,idActorRelacionado,situacion FROM 7005_relacionParticipantes
			WHERE idActividad=".$idActividadBase." AND idParticipante IN(".$listaParticipantes.")";
	$con->ejecutarConsulta($consulta);		

	
	cambiarEtapaFormulario(46,$idRegistroSolicitud,$idEtapa,$comentariosAdicionales,-1,"NULL","NULL",$actor);
	return $idRegistroSolicitud;
}

function determinarUnidadDestinoIncompetencia($idFormulario,$idRegistro)
{
	global $con;
	$tipoAudiencia=91;
	$consulta="SELECT unidadReceptora,unidadDestino,materiaDestino,motivoIncompetencia,unidadesDestino FROM 
			_554_tablaDinamica WHERE id__554_tablaDinamica=".$idRegistro;

	$fUnidad=$con->obtenerPrimeraFila($consulta);

	switch($fUnidad[3])
	{
		case 4:
			return $fUnidad[1];
		break;
		default:
			$lista=obtenerUnidadGestionSiguienteAsignacion($tipoAudiencia,$fUnidad[4]);
			return $lista;		
		break;
	}
}

function aplicarRegistroAsignacionIncompetencia($idFormulario,$idRegistro)
{
	global $con;
	
	$consulta="SELECT * FROM _46_tablaDinamica WHERE id__46_tablaDinamica=".$idRegistro;
	$fRegistroInicial=$con->obtenerPrimeraFilaAsoc($consulta);
	if($fRegistroInicial["iFormulario"]!=554)
	{

		return true;
	}
	
	$consulta="SELECT idCarpeta,carpetaAdministrativa FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$fRegistroInicial["carpetaRemitida"]."'";
	$fDatosCarpeta=$con->obtenerPrimeraFila($consulta);
	$x=0;
	$query[$x]="begin";
	$x++;
	
	$consulta="SELECT idCarpeta FROM 7006_carpetasAdministrativas WHERE idFormulario=46 AND idRegistro=".$idRegistro;
	$iCarpetaGenerada=$con->obtenerValor($consulta);
	

	$consulta="SELECT u.id__17_tablaDinamica,c.idActividad,c.carpetaAdministrativa FROM 7006_carpetasAdministrativas c,_17_tablaDinamica u WHERE 
			u.claveUnidad=c.unidadGestion AND c.idCarpeta=".$iCarpetaGenerada;
	$fDatosCarpetaDestino=$con->obtenerPrimeraFila($consulta);
	
	$idUnidadDestino=$fDatosCarpetaDestino[0];
	$consulta="SELECT idParticipante FROM 7005_relacionFigurasJuridicasSolicitud WHERE idActividad=".$fRegistroInicial["idActividad"]."
				and idFiguraJuridica=4";
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		$consulta="select count(*) from 3250_asignacionIncompetencias where iFormulario=".$fRegistroInicial["iFormulario"].
				" and iRegistro=".$fRegistroInicial["iReferencia"]." and idImputado=".$fila[0];
		$nReg=$con->obtenerValor($consulta);
		if($nReg==0)
		{
			$query[$x]="INSERT INTO 3250_asignacionIncompetencias(iFormulario,iRegistro,idCarpeta,idImputado,tipoAccion,idUnidadDestino,fechaAsignacion)
						values(".$fRegistroInicial["iFormulario"].",".$fRegistroInicial["iReferencia"].",".$iCarpetaGenerada.
						",".$fila[0].",1,".$idUnidadDestino.",'".date("Y-m-d H:i:s")."')";
			$x++;
		
			registrarCambioSituacionImputado($fDatosCarpeta[1],$fDatosCarpeta[0],$fila[0],21,"","Incompetencia");
		}
	}

	$query[$x]="commit";
	$x++;
	if($con->ejecutarBloque($query))
	{
		determinarSituacionCarpeta($fDatosCarpeta[1],$fDatosCarpeta[0]);
	}
}


//Agregado
function generarCarpetasRemisionIncompetenciaEjecTE($idFormulario,$idRegistro)
{
	global $con;
	
	$consulta="SELECT COUNT(*) FROM _558_tablaDinamica WHERE iFormulario=".$idFormulario." AND iRegistro=".$idRegistro;

	$nRegistros=$con->obtenerValor($consulta);
	if($nRegistros>0)
	{
		return true;
	}
	
	
	
	
	$arrDocumentosReferencia=array();
	
	$consulta="SELECT idDocumento FROM 9074_documentosRegistrosProceso WHERE idFormulario=".$idFormulario." 
					AND idRegistro=".$idRegistro." AND tipoDocumento in(2,1)";
	
	$res=$con->obtenerFilas($consulta);
	while($fDocumento=mysql_fetch_row($res))
	{
		array_push($arrDocumentosReferencia,$fDocumento[0]);	
	}	
	
	$arrCarpetasHistorial=array();
	$arrNuevasCarpetas=array();
	$consulta="select * from _".$idFormulario."_tablaDinamica where id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$fRegistro=$con->obtenerPrimeraFilaAsoc($consulta);
	
	
	
	
	$consulta="SELECT u.id__17_tablaDinamica,c.idActividad,c.carpetaAdministrativa,c.idCarpeta,u.idReferencia as idEdificio FROM 7006_carpetasAdministrativas c,_17_tablaDinamica u WHERE 
				u.claveUnidad=c.unidadGestion AND c.idCarpeta=".$fRegistro["iCarpeta"];
	$fDatosUnidadOrigen=$con->obtenerPrimeraFila($consulta);
	
	$consulta="SELECT tipoCarpetaAdministrativa FROM 7006_carpetasAdministrativas WHERE idCarpeta=".$fRegistro["iCarpeta"];
	$tCarpeta=$con->obtenerValor($consulta);
	$campoDestino="";
	if($tCarpeta==5)
	{
		$campoDestino="unidadDestinoTribunalEnjuiciamiento";
	}
	else
	{
		$campoDestino="unidadDestinoEjecucion";
	}
	
	$consulta="SELECT * FROM _".$idFormulario."_imputadosRemite WHERE idPadre=".$fRegistro["id__".$idFormulario."_tablaDinamica"];

	$rImputados=$con->obtenerFilas($consulta);
	while($fImputado=mysql_fetch_row($rImputados))
	{
		$listaUnidadesDestino=-1;
		if($tCarpeta==6)
		{
			$listaUnidadesDestino=$fRegistro[$campoDestino];
		}
		else
		{
			$idEdificioPadre=0;
			switch($fRegistro[$campoDestino])
			{
				case 1: //Norte
					$idEdificioPadre=7;
				break;
				case 2: //Oriente
					$idEdificioPadre=8;
				break;
				case 3: //Sur
					$idEdificioPadre=9;
				break;
				case 4: //Sullivan
					$idEdificioPadre=5;
				break;
			}
			
			$consulta="SELECT id__17_tablaDinamica FROM _17_tablaDinamica u,_17_tiposCarpetasAdministra c WHERE u.idReferencia=".$idEdificioPadre.
						" AND c.idPadre=u.id__17_tablaDinamica AND  c.idOpcion=5 and u.claveUnidad not in (301,302)";
			if($idEdificioPadre==$fDatosUnidadOrigen[4])
			{
				$consulta.=" and id__17_tablaDinamica not in(".$fDatosUnidadOrigen[0].")";
			}
			
			$listaUnidadesDestino=$con->obtenerListaValores($consulta);
			if($listaUnidadesDestino=="")
				$listaUnidadesDestino=-1;
		}
		
		
		$consulta="SELECT c.carpetaAdministrativa,c.unidadGestion,u.id__17_tablaDinamica,c.idCarpeta FROM 7006_carpetasAdministrativas c,
						_17_tablaDinamica u,7005_relacionFigurasJuridicasSolicitud r WHERE u.claveUnidad=c.unidadGestion
						AND c.tipoCarpetaAdministrativa=".$tCarpeta." AND r.idActividad=c.idActividad AND r.idParticipante=".$fImputado[2].
						" AND r.idFiguraJuridica=4 	AND u.id__17_tablaDinamica in(".$listaUnidadesDestino.")";

		$fDatosHistorial=$con->obtenerPrimeraFila($consulta);
		if(!$fDatosHistorial)
		{
			$arrNuevasCarpetas[$fImputado[2]]=1;
		}
		else
		{ 
			if(!isset($arrCarpetasHistorial[$fDatosHistorial[3]]))
			{
				$arrCarpetasHistorial[$fDatosHistorial[3]]=array();
			}
			array_push($arrCarpetasHistorial[$fDatosHistorial[3]],$fImputado[2]);
		}
	}
	
	
	$consulta="SELECT contenido FROM 902_opcionesFormulario WHERE idGrupoElemento=8964 AND valor=".$fRegistro["motivoIncompetencia"];
	$motivoIncompetencia=$con->obtenerValor($consulta);
	if($fRegistro["motivoIncompetencia"]==5)
	{
		$motivoIncompetencia.=": ".$fRegistro["otroMotivoIncompetencia"];
	}
	
	$x=0;
	$query[$x]="begin";
	$x++;
	
	foreach($arrCarpetasHistorial as $iCarpeta=>$resto)
	{
		$consulta="SELECT u.id__17_tablaDinamica,c.idActividad,c.carpetaAdministrativa FROM 7006_carpetasAdministrativas c,_17_tablaDinamica u WHERE 
				u.claveUnidad=c.unidadGestion AND c.idCarpeta=".$iCarpeta;
		$fDatosCarpeta=$con->obtenerPrimeraFila($consulta);
		
		$idUnidadDestino=$fDatosCarpeta[0];
		foreach($resto as $imputado)
		{
			$consulta="select count(*) from 3250_asignacionIncompetencias where iFormulario=".$idFormulario.
				" and iRegistro=".$idRegistro." and idImputado=".$imputado;
			$nReg=$con->obtenerValor($consulta);
			if($nReg==0)
			{
				$query[$x]="INSERT INTO 3250_asignacionIncompetencias(iFormulario,iRegistro,idCarpeta,idImputado,tipoAccion,idUnidadDestino,fechaAsignacion)
							values(".$idFormulario.",".$idRegistro.",".$iCarpeta.",".$imputado.",0,".$idUnidadDestino.",'".date("Y-m-d H:i:s")."')";
				$x++;
			}
		}
		
		
	}
		
	foreach($arrCarpetasHistorial as $iCarpeta=>$resto)
	{
		$consulta="SELECT u.id__17_tablaDinamica,c.idActividad,c.carpetaAdministrativa FROM 7006_carpetasAdministrativas c,_17_tablaDinamica u WHERE 
				u.claveUnidad=c.unidadGestion AND c.idCarpeta=".$iCarpeta;
		$fDatosCarpeta=$con->obtenerPrimeraFila($consulta);
		$listaImputados="";
		foreach($resto as $imputado)
		{
			if($listaImputados=="")
				$listaImputados=$imputado;
			else
				$listaImputados.=",".$imputado;
		}
		$arrValores=array();
		$arrValores["idActividad"]=$fDatosCarpeta[1];
		$arrValores["motivoIncompetencia"]=$motivoIncompetencia;
		$arrValores["tipoCarpeta"]=$tCarpeta;
		$arrValores["fechaRemision"]=date("Y-m-d H:i:s");
		$arrValores["unidadAsignada"]=$fDatosCarpeta[0];
		$arrValores["carpetaAsignada"]=$fDatosCarpeta[2];
		$arrValores["carpetaOrigen"]=$fRegistro["carpetaAdministrativa"];
		$arrValores["unidadOrigen"]=$fDatosUnidadOrigen[0];
		$arrValores["listaImputados"]=$listaImputados;
		$arrValores["iFormulario"]=$idFormulario;
		$arrValores["iRegistro"]=$idRegistro;
		$arrValores["imputadoPrivadoLibertad"]=$fRegistro["privadoLibertad"];
		$arrValores["lugarInternamiento"]=$fRegistro["lugarInternamiento"];
		$arrValores["comentariosAdicionales"]=$fRegistro["comentariosAdicionales"];
		
		crearInstanciaRegistroFormulario(558,$idRegistro,3,$arrValores,$arrDocumentosReferencia,-1,992,"");
	}
	
	
	if(sizeof($arrNuevasCarpetas)>0)
	{
		
		$listaImputados="";
		foreach($arrNuevasCarpetas as $imputado=>$resto)
		{
			if($listaImputados=="")
				$listaImputados=$imputado;
			else
				$listaImputados.=",".$imputado;
		}
		$arrValores=array();
		$arrValores["idActividad"]=generarIDActividad($idFormulario);
		$arrValores["motivoIncompetencia"]=$motivoIncompetencia;
		$arrValores["tipoCarpeta"]=$tCarpeta;
		$arrValores["fechaRemision"]=date("Y-m-d H:i:s");				
		$arrValores["carpetaOrigen"]=$fRegistro["carpetaAdministrativa"];
		$arrValores["unidadOrigen"]=$fDatosUnidadOrigen[0];
		$arrValores["listaImputados"]=$listaImputados;
		$arrValores["iFormulario"]=$idFormulario;
		$arrValores["iRegistro"]=$idRegistro;
		$arrValores["imputadoPrivadoLibertad"]=$fRegistro["privadoLibertad"];
		$arrValores["lugarInternamiento"]=$fRegistro["lugarInternamiento"];
		$arrValores["comentariosAdicionales"]=$fRegistro["comentariosAdicionales"];
		
		$iRegistroNCarpeta=crearInstanciaRegistroFormulario(558,$idRegistro,1,$arrValores,$arrDocumentosReferencia,-1,992,"");

		if($tCarpeta==5)
			generarFolioCarpetaTribunalEnjuciamiento_ModuloIncompetencia(558,$iRegistroNCarpeta);
		else
			generarFolioCarpetaUnidadEjecucion_ModuloIncompetencia(558,$iRegistroNCarpeta);
		

		cambiarEtapaFormulario(558,$iRegistroNCarpeta,2,$arrValores["comentariosAdicionales"],-1,"NULL","NULL",992);	
		
		registrarImputadosNuevaCarpeta($listaImputados,$arrValores["idActividad"],$fDatosUnidadOrigen[1]);

		registrarDelitosNuevaCarpeta($listaImputados,$arrValores["idActividad"],$fDatosUnidadOrigen[1],$tCarpeta);
		if($tCarpeta==6)
			registrarPenasNuevaCarpeta($listaImputados,$arrValores["idActividad"],$fDatosUnidadOrigen[1]);
		$consulta="SELECT carpetaAsignada,unidadAsignada FROM _558_tablaDinamica WHERE id__558_tablaDinamica=".$iRegistroNCarpeta;
		$fDatosResultado=$con->obtenerPrimeraFila($consulta);
		
		$carpetaAsignada=$fDatosResultado[0];
		$idUnidadDestino=$fDatosResultado[1];
		
		$consulta="SELECT idCarpeta FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$carpetaAsignada."'";
		$iCarpetaAsignada=$con->obtenerValor($consulta);
		foreach($arrNuevasCarpetas as $imputado=>$resto)
		{
			registrarCambioSituacionImputado($carpetaAsignada,$iCarpetaAsignada,$imputado,1,"","Incompetencia");
			registrarCambioSituacionImputado($fDatosUnidadOrigen[2],$fDatosUnidadOrigen[3],$imputado,21,"","Incompetencia");
			
			$consulta="select count(*) from 3250_asignacionIncompetencias where iFormulario=".$idFormulario.
				" and iRegistro=".$idRegistro." and idImputado=".$imputado;
			$nReg=$con->obtenerValor($consulta);
			if($nReg==0)
			{
				$query[$x]="INSERT INTO 3250_asignacionIncompetencias(iFormulario,iRegistro,idCarpeta,idImputado,tipoAccion,idUnidadDestino,fechaAsignacion)
							values(".$idFormulario.",".$idRegistro.",".$iCarpetaAsignada.",".$imputado.",1,".$idUnidadDestino.",'".date("Y-m-d H:i:s")."')";
				$x++;
			}
		}
		
	}
	
	$query[$x]="commit";
	$x++;
	if($con->ejecutarBloque($query))
	{
		foreach($arrCarpetasHistorial as $iCarpeta=>$resto)
		{
			$consulta="SELECT u.id__17_tablaDinamica,c.idActividad,c.carpetaAdministrativa FROM 7006_carpetasAdministrativas c,_17_tablaDinamica u WHERE 
					u.claveUnidad=c.unidadGestion AND c.idCarpeta=".$iCarpeta;
			$fDatosCarpeta=$con->obtenerPrimeraFila($consulta);
			
			$idUnidadDestino=$fDatosCarpeta[0];
			foreach($resto as $imputado)
			{
				registrarCambioSituacionImputado($fDatosCarpeta[2],$iCarpeta,$imputado,1,"","Incompetencia");
				registrarCambioSituacionImputado($fRegistro["carpetaAdministrativa"],$fRegistro["iCarpeta"],$imputado,21,"","Incompetencia");
			}
			
		}
		determinarSituacionCarpeta($fRegistro["carpetaAdministrativa"],$fRegistro["iCarpeta"]);
		return true;
	}
	
	
}

function generarFolioCarpetaTribunalEnjuciamiento_ModuloIncompetencia($idFormulario,$idRegistro)
{
	global $con;

	$idUnidadGestion=32;
	$anio=date("Y");

	$query="SELECT carpetaOrigen,
			carpetaAsignada,
			idActividad,iFormulario,iRegistro
			FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	
	$fDatosCarpeta=$con->obtenerPrimeraFila($query);
	if($fDatosCarpeta[1]=="N/E")
		$fDatosCarpeta[1]="";	
		
	$cAdministrativaBase=$fDatosCarpeta[0];
	$carpetaEnjuiciamiento=$fDatosCarpeta[1];
	if($carpetaEnjuiciamiento!="")
		return true;

	$query="SELECT idActividad,unidadGestion,carpetaInvestigacion FROM 7006_carpetasAdministrativas WHERE 
			carpetaAdministrativa='".$cAdministrativaBase."'";
	$fDatosCarpetaJudicial=$con->obtenerPrimeraFila($query);

	$idActividad=$fDatosCarpeta[2];
	$carpetaInvestigacion=$fDatosCarpetaJudicial[2];
	if($idActividad=="")
		$idActividad=-1;
	
	$unidadGestionCarpeta=$fDatosCarpetaJudicial[1];
	
	$query="SELECT * FROM _17_tablaDinamica WHERE claveUnidad='".$unidadGestionCarpeta."'";
	$fDatosUnidadGestion=$con->obtenerPrimeraFilaAsoc($query);

	$idUnidadGestion=-1;
	

	$query="SELECT unidadDestinoTribunalEnjuiciamiento FROM _556_tablaDinamica WHERE id__556_tablaDinamica=".$fDatosCarpeta[4];
	$unidadDestinoTribunalEnjuiciamiento=$con->obtenerValor($query);
	
	switch($unidadDestinoTribunalEnjuiciamiento)
	{
		case 1://Norte
			$query="SELECT id__17_tablaDinamica FROM  _17_tablaDinamica u,_1_tablaDinamica e WHERE 
			u.idReferencia=e.id__1_tablaDinamica AND e.cveInmueble='00020001' AND  id__17_tablaDinamica 
			IN(SELECT idPadre FROM _17_tiposCarpetasAdministra WHERE idOpcion=5) and id__17_tablaDinamica<>".
			$fDatosUnidadGestion["id__17_tablaDinamica"];
	
			$idUnidadGestion=$con->obtenerListaValores($query);
		break;
		case 3://Sur
			$query="SELECT id__17_tablaDinamica FROM  _17_tablaDinamica u,_1_tablaDinamica e WHERE 
			u.idReferencia=e.id__1_tablaDinamica AND e.cveInmueble='00020003' AND  id__17_tablaDinamica 
			IN(SELECT idPadre FROM _17_tiposCarpetasAdministra WHERE idOpcion=5) and id__17_tablaDinamica<>".
			$fDatosUnidadGestion["id__17_tablaDinamica"];
	
			$idUnidadGestion=$con->obtenerListaValores($query);
		break;
		case 2://Oriente
			$query="SELECT id__17_tablaDinamica FROM  _17_tablaDinamica u,_1_tablaDinamica e WHERE 
			u.idReferencia=e.id__1_tablaDinamica AND e.cveInmueble='00020002' AND  id__17_tablaDinamica 
			IN(SELECT idPadre FROM _17_tiposCarpetasAdministra WHERE idOpcion=5) and id__17_tablaDinamica<>".
			$fDatosUnidadGestion["id__17_tablaDinamica"];
			$idUnidadGestion=$con->obtenerListaValores($query);

		break;
		case 4://Sullivan
			$idUnidadGestion=32;
		break;
		
	}


	if(($idUnidadGestion==-1)||($idUnidadGestion==""))
		return ;
	
	$idUnidadGestionTmp=-1;
	$encontrado=false;
	$listaIng=-1;
	$validaConoceCausa=true;
	
	if(($fDatosCarpeta[3]==2)||($cAdministrativaBase==""))
		$validaConoceCausa=false;

	while(!$encontrado)
	{
		
		$arrCarga=array();
		$query="SELECT claveUnidad,id__17_tablaDinamica FROM _17_tablaDinamica WHERE id__17_tablaDinamica IN(".$idUnidadGestion.") and
		id__17_tablaDinamica not in(".$listaIng.")";
		$rTribunales=$con->obtenerFilas($query);
		
		if($con->filasAfectadas>0)
		{
		
			while($fTribunal=mysql_fetch_row($rTribunales))
			{
				$query="SELECT COUNT(*) FROM 7006_carpetasAdministrativas WHERE fechaCreacion>='2018-09-03' AND unidadGestion='".$fTribunal[0].
						"' AND tipoCarpetaAdministrativa=5";
				$nCarpetas=$con->obtenerValor($query);		
				$arrCarga[$fTribunal[1]]=$nCarpetas;
				
			}
			
			
			
			$nCargaMinima=-1;
			foreach($arrCarga as $iTribunal=>$total)
			{
				if($nCargaMinima==-1)
				{
					$nCargaMinima=$total;
				}
				
				if($nCargaMinima>$total)
				{
					$nCargaMinima=$total;
				}
			}
			
			
			
			foreach($arrCarga as $iTribunal=>$total)
			{
				if($total==$nCargaMinima)
				{
					$idUnidadGestionTmp=$iTribunal;
					
					if(!$validaConoceCausa || !conoceJuezTribunalCarpeta($cAdministrativaBase,$iTribunal))	
					{
						$encontrado=true;
						$idUnidadGestion=$idUnidadGestionTmp;
						
					}
					break;
				}
			}
			$listaIng.=",".$idUnidadGestionTmp;
		}
		else
		{
			$listaIng=-1;
			$validaConoceCausa=false;
		}
	}
	
	$carpetaAdministrativa=obtenerSiguienteCarpetaAdministrativa($idUnidadGestion,$anio,5,$idFormulario,$idRegistro);

	$query=" SELECT lj.clave FROM _26_tablaDinamica lj,_26_tipoJuez tj WHERE lj.idReferencia=".$idUnidadGestion." AND 
				tj.idPadre =lj.id__26_tablaDinamica AND tj.idOpcion=2  ORDER BY usuarioJuez";
	$listaJueces=$con->obtenerListaValores($query,"'");

	$idJuezTribunal=obtenerSiguienteJuez(20,$listaJueces,-1);

	$query="SELECT claveUnidad,claveFolioCarpetas FROM _17_tablaDinamica WHERE id__17_tablaDinamica=".$idUnidadGestion;
	$fDatosUnidadTribunalE=$con->obtenerPrimeraFila($query);
	$cveUnidadGestion=$fDatosUnidadTribunalE[0];

	
	$x=0;
	$consulta[$x]="begin";
	$x++;
	$consulta[$x]="INSERT INTO 7006_carpetasAdministrativas(carpetaAdministrativa,fechaCreacion,responsableCreacion,
					idFormulario,idRegistro,unidadGestion,etapaProcesalActual,idActividad,carpetaAdministrativaBase,
					idJuezTitular,tipoCarpetaAdministrativa,carpetaInvestigacion,llaveCarpetaInvestigacion,unidadGestionOriginal) 
					VALUES('".$carpetaAdministrativa."','".date("Y-m-d H:i:s")."',".$_SESSION["idUsr"].",".$idFormulario.",'".$idRegistro.
					"','".$cveUnidadGestion."',5,".$idActividad.",'".$cAdministrativaBase."',".$idJuezTribunal.",5,(SELECT UPPER('".
					$carpetaInvestigacion."')),'".cv(generarLlaveCarpetaInvestigacion($carpetaInvestigacion))."','".$cveUnidadGestion."')";
	$x++;
	$consulta[$x]="update _".$idFormulario."_tablaDinamica set carpetaAsignada='".$carpetaAdministrativa."',
					unidadAsignada=".$idUnidadGestion." where id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$x++;
	
	
	
	$consulta[$x]="commit";
	$x++;
	
	if($con->ejecutarBloque($consulta))
	{
		if($cAdministrativaBase!="")
		{
			registrarCambioEtapaProcesalCarpeta($cAdministrativaBase,5,$idFormulario,$idRegistro,-1);
			
		}
		$query="SELECT idDocumento FROM 9074_documentosRegistrosProceso WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro;
		$rDocumentos=$con->obtenerFilas($query);
		while($fDocumento=mysql_fetch_row($rDocumentos))
		{
			registrarDocumentoCarpetaAdministrativa($carpetaAdministrativa,$fDocumento[0],$idFormulario,$idRegistro);	
		}
		
	}
		
	return false;
	
}

function generarFolioCarpetaUnidadEjecucion_ModuloIncompetencia($idFormulario,$idRegistro)
{
	global $con;	
		
	$idUnidadGestion=-1;		
	
	$anio=date("Y");
	$query="SELECT carpetaOrigen,
			carpetaAsignada,
			idActividad,iFormulario,iRegistro,listaImputados
			FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$fDatosCarpeta=$con->obtenerPrimeraFila($query);
	if($fDatosCarpeta[1]=="N/E")
		$fDatosCarpeta[1]="";
		
	$cAdministrativaBase=$fDatosCarpeta[0];
	$carpetaEnjuiciamiento=$fDatosCarpeta[1];
	
	if($carpetaEnjuiciamiento!="")
		return true;
	
	$consulta="SELECT unidadGestion,carpetaInvestigacion FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$cAdministrativaBase."'";	
	$fCarpetaBase=$con->obtenerPrimeraFila($consulta);
	$unidadOrigen=$fCarpetaBase[0];
	$carpetaInvestigacion=$fCarpetaBase[1];

	$consulta="SELECT unidadDestinoEjecucion FROM _556_tablaDinamica WHERE id__556_tablaDinamica=".$fDatosCarpeta[4];
	$idUnidadGestion=$con->obtenerValor($consulta);
	
	$carpetaAdministrativa=obtenerSiguienteCarpetaAdministrativa($idUnidadGestion,$anio,6,$idFormulario,$idRegistro);
	$consulta="SELECT juezResponsable FROM _621_tablaDinamica WHERE carpetaAdministrativa='".$carpetaAdministrativa.
				"' and idEstado>1 order by id__621_tablaDinamica desc";
	$idJuezEjecucion=$con->obtenerValor($consulta);
	if($idJuezEjecucion=="")
		$idJuezEjecucion=-1;
	if($idJuezEjecucion=="-1")
		$idJuezEjecucion=asignarJuezEjecucionCarpetaUnica($idUnidadGestion,$idFormulario,$idRegistro);
	$fechaCreacionCarpeta=date("Y-m-d H:i:s");
	$idActividadEjecucion=$fDatosCarpeta[2];
	
	$query="SELECT claveUnidad FROM _17_tablaDinamica WHERE id__17_tablaDinamica=".$idUnidadGestion;
	$cveUnidadGestion=$con->obtenerValor($query);
	
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
	$consulta[$x]="update _".$idFormulario."_tablaDinamica set carpetaAsignada='".$carpetaAdministrativa."',unidadAsignada=".$idUnidadGestion.
				" where id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$x++;
			
	
	$consulta[$x]="commit";
	$x++;
	
	if($con->ejecutarBloque($consulta))
	{
		if($cAdministrativaBase!="")
		{
			registrarCambioEtapaProcesalCarpeta($cAdministrativaBase,6,$idFormulario,$idRegistro,-1);
			
			$query="SELECT carpetaAdministrativaBase,tipoCarpetaAdministrativa FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$cAdministrativaBase."'";
			$fCBase=$con->obtenerPrimeraFila($query);
			if(($fCBase[1]==5)&&($fCBase[0]!=""))
			{
				registrarCambioEtapaProcesalCarpeta($fCBase[0],6,$idFormulario,$idRegistro,-1);
			}
			
			
		}
		
		$query="SELECT idDocumento FROM 9074_documentosRegistrosProceso WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro;
		$rDocumentos=$con->obtenerFilas($query);
		while($fDocumento=mysql_fetch_row($rDocumentos))
		{
			registrarDocumentoCarpetaAdministrativa($carpetaAdministrativa,$fDocumento[0],$idFormulario,$idRegistro);	
		}
		

	}
		
	
	return false;
}

function registrarImputadosNuevaCarpeta($listaImputados,$idActividadNuevaCarpeta,$idActividadCarpetaBase)
{
	global $con;
	
	$idActividad=$idActividadNuevaCarpeta;
	$idActividadBase=$idActividadCarpetaBase;
	$consulta="INSERT INTO 7005_relacionFigurasJuridicasSolicitud(idActividad,idParticipante,idFiguraJuridica,situacion,idCuentaAcceso,
			etapaProcesal,situacionProcesal,detalleSituacion)
			SELECT ".$idActividad." as idActividad,idParticipante,idFiguraJuridica,situacion,idCuentaAcceso,
			etapaProcesal,situacionProcesal,detalleSituacion FROM 7005_relacionFigurasJuridicasSolicitud
			WHERE idActividad=".$idActividadBase." AND ((idParticipante IN(".$listaImputados.") AND idFiguraJuridica=4) or idFiguraJuridica=2)";

	$con->ejecutarConsulta($consulta);
	$consulta="SELECT idParticipante FROM 7005_relacionFigurasJuridicasSolicitud WHERE idActividad= ".$idActividad;
	$listaParticipantes=$con->obtenerListaValores($consulta);
	if($listaParticipantes=="")
	{
		$listaParticipantes=-1;
	}
	
	$consulta="INSERT INTO 7005_relacionFigurasJuridicasSolicitud(idActividad,idParticipante,idFiguraJuridica,situacion,idCuentaAcceso,
			etapaProcesal,situacionProcesal,detalleSituacion)
			SELECT ".$idActividad." as idActividad,idParticipante,idFiguraJuridica,situacion,idCuentaAcceso,
			etapaProcesal,situacionProcesal,detalleSituacion FROM 7005_relacionFigurasJuridicasSolicitud
			WHERE idActividad=".$idActividadBase." AND idFiguraJuridica not in(2,4) and idParticipante in
			(
			SELECT idParticipante FROM 7005_relacionParticipantes WHERE idActividad=".$idActividadBase.
			" AND idActorRelacionado IN(".$listaParticipantes.")
			)";
			
	$con->ejecutarConsulta($consulta);

	$consulta="INSERT INTO 7005_relacionFigurasJuridicasSolicitud(idActividad,idParticipante,idFiguraJuridica,situacion,idCuentaAcceso,
			etapaProcesal,situacionProcesal,detalleSituacion)
			SELECT ".$idActividad." as idActividad,idParticipante,idFiguraJuridica,situacion,idCuentaAcceso,
			etapaProcesal,situacionProcesal,detalleSituacion FROM 7005_relacionFigurasJuridicasSolicitud
			WHERE idActividad=".$idActividadBase." AND idFiguraJuridica not in(2,4) and idParticipante not in
			(
			SELECT idParticipante FROM 7005_relacionParticipantes WHERE idActividad=".$idActividadBase."
			)";
			
	$con->ejecutarConsulta($consulta);

	$consulta="SELECT idParticipante FROM 7005_relacionFigurasJuridicasSolicitud WHERE idActividad= ".$idActividad;
	$listaParticipantes=$con->obtenerListaValores($consulta);
	if($listaParticipantes=="")
	{
		$listaParticipantes=-1;
	}
	
	$consulta="INSERT INTO 7005_relacionParticipantes(idActividad,idParticipante,idFiguraJuridica,idActorRelacionado,situacion)
			SELECT ".$idActividad.",idParticipante,idFiguraJuridica,idActorRelacionado,situacion FROM 7005_relacionParticipantes
			WHERE idActividad=".$idActividadBase." AND idParticipante IN(".$listaParticipantes.")";
	$con->ejecutarConsulta($consulta);	
	
	return true;
}

function registrarDelitosNuevaCarpeta($listaImputados,$idActividadNuevaCarpeta,$idActividadCarpetaBase,$tCarpeta)
{
	global $con;
			
	$consulta="SELECT denominacionDelito FROM _61_tablaDinamica WHERE idActividad=".$idActividadNuevaCarpeta;
	$listaDelitos=$con->obtenerListaValores($consulta);
	if($listaDelitos=="")
		$listaDelitos=-1;
	
	$consulta="INSERT INTO _61_tablaDinamica(idActividad,denominacionDelito,tituloDelito,capituloDelito,calificativo,gradoRealizacion,modalidadDelito)
			SELECT '".$idActividadNuevaCarpeta."' as idActividad,denominacionDelito,tituloDelito,capituloDelito,calificativo,
			gradoRealizacion,modalidadDelito FROM _61_tablaDinamica WHERE idActividad=".$idActividadCarpetaBase.
			" AND denominacionDelito NOT IN(".$idActividadCarpetaBase.")";
	return $con->ejecutarConsulta($consulta);
}

function registrarPenasNuevaCarpeta($listaImputados,$idActividadNuevaCarpeta,$idActividadCarpetaBase)
{
	global $con;
	
	$consulta="SELECT idPena FROM 7046_penasVSCarpetasEjecucion WHERE idActividad=".$idActividadNuevaCarpeta." AND idImputado IN(".$listaImputados.")";
	$listaPenas=$con->obtenerListaValores($consulta);
	if($listaPenas=="")
		$listaPenas=-1;
	$consulta="INSERT INTO 7046_penasVSCarpetasEjecucion(idActividad,idImputado,idPena)
				SELECT '".$idActividadNuevaCarpeta."' as idActividad,idImputado,idPena FROM 7046_penasVSCarpetasEjecucion 
				WHERE idActividad=".$idActividadCarpetaBase." AND idPena NOT IN(".$listaPenas.")";
	return $con->ejecutarConsulta($consulta);
}

function esHorarioNormalDiaHabilVacaciones($horario)
{
	/*if(!esDiaHabilInstitucion($horario))
	{
		return false;
	}*/
	$horaSolicitud=strtotime($horario);
	$dia=date("w",$horaSolicitud);
	if(($dia==0)||($dia==6))
		return false;
	
	$fechaSolicitud=date("Y-m-d",$horaSolicitud);
	$horarioInicial=strtotime(date("Y-m-d ",$horaSolicitud)." 09:00");
	$horarioFinalNormal=strtotime(date("Y-m-d ",$horaSolicitud)." 15:00");
	$horarioFinalNormalViernes=strtotime(date("Y-m-d ",$horaSolicitud)." 14:00");
	
	
	switch($dia)
	{
		case 5://Viernes
			if(($horaSolicitud>=$horarioInicial)&&($horaSolicitud<$horarioFinalNormalViernes))
				return true;
		break;
		default:
			if(($horaSolicitud>=$horarioInicial)&&($horaSolicitud<$horarioFinalNormal))
				return true;	
		break;
	}	
	return false;	
}


function asignarJuezEjecucionCarpetaUnica($idUnidadGestion,$idFormulario,$idRegistro)
{
	global $con;
	$fechaReferencia=date("Y-m-d");
	
	
	$consulta="SELECT idUnidadReferida FROM 7001_asignacionesObjetos WHERE tipoAsignacion='".$idUnidadGestion."' AND tipoRonda='CU' and 
				idFormulario=".$idFormulario." AND idRegistro=".$idRegistro." AND situacion=1";
	
	$idUnidadReferida=$con->obtenerValor($consulta);
	if($idUnidadReferida!="")
		return $idUnidadReferida; 
	
	$consulta="SELECT usuarioJuez FROM _26_tablaDinamica t,_26_tipoJuez j WHERE idReferencia=".$idUnidadGestion." AND j.idPadre=t.id__26_tablaDinamica
				AND j.idOpcion=3 order by clave";
	$lista=$con->obtenerListaValores($consulta);
	$arrConfiguracion["tipoAsignacion"]=$idUnidadGestion;
	$arrConfiguracion["serieRonda"]="CU";
	$arrConfiguracion["universoAsignacion"]=$lista;
	$arrConfiguracion["idObjetoReferencia"]=-1;
	$arrConfiguracion["pagarDeudasAsignacion"]=true;
	$arrConfiguracion["considerarDeudasMismaRonda"]=true;
	$arrConfiguracion["limitePagoRonda"]=1;
	$arrConfiguracion["escribirAsignacion"]=true;
	$arrConfiguracion["idFormulario"]=$idFormulario;
	$arrConfiguracion["idRegistro"]=$idRegistro;
	$arrConfiguracion["funcValidacionPagoDeuda"]="esJuezDisponibleIncidencia(@idUnidad,'".$fechaReferencia."')";
	$arrConfiguracion["funcValidacionSeleccion"]="";
	$resultado= obtenerSiguienteAsignacionObjeto($arrConfiguracion,true);
	
	/*$encontrado=false;
	
	while(!$encontrado)
	{
		$resultado= obtenerSiguienteAsignacionObjeto($arrConfiguracion,true);
		
		
		if(!esJuezDisponibleIncidencia($resultado["idUnidad"],$fechaReferencia))
		{
			if($arrConfiguracion["escribirAsignacion"])
			{
				if($arrConfiguracion["pagarDeudasAsignacion"])
				{
					$consulta="UPDATE 7001_asignacionesObjetos SET situacion=2 WHERE idAsignacion=".$resultado["idRegistroAsignacion"];
					$con->ejecutarConsulta($consulta);
				}
				else
				{
					$consulta="UPDATE 7001_asignacionesObjetos SET situacion=10 WHERE idAsignacion=".$resultado["idRegistroAsignacion"];
					$con->ejecutarConsulta($consulta);
				}
			}
		}
		else
			$encontrado=true;
	}*/

	return $resultado["idUnidad"];
}

function obtenerJuezBinomio($idUnidadGestion,$idJuez)
{
	global $con;
	
	$arrJuez=array();	
	$consulta="SELECT usuarioJuez FROM _26_tablaDinamica t,_26_tipoJuez j WHERE idReferencia=".$idUnidadGestion." AND j.idPadre=t.id__26_tablaDinamica
				AND j.idOpcion=3 order by clave";
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		array_push($arrJuez,$fila[0]);
	}
	
	$encontrado=false;
	$x=0;
	for($x=0;$x<sizeof($arrJuez);$x++)
	{
		if($arrJuez[$x]==$idJuez)
		{
			$encontrado=true;
			break;
			
		}
	}

	if(!$encontrado)
		return $arrJuez[0];
	if($x%2==0)
	{
		if(isset($arrJuez[$x+1]))
			return $arrJuez[$x+1];
		else
			return $arrJuez[0];
	}
	else
	{
		return $arrJuez[$x-1];
	}
	
	
}

function obtenerJuezAtencionCarpetaUnica($carpetaJudicial,$fechaReferencia=NULL)
{
	global $con;
	if($fechaReferencia==NULL)
		$fechaReferencia=date("Y-m-d");
	
	
	$consulta="SELECT idJuezTitular,unidadGestion FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$carpetaJudicial."'";
	$fDatosCarpeta=$con->obtenerPrimeraFila($consulta);	
	$idJuez=$fDatosCarpeta[0];
	if($idJuez=="")
		$idJuez=-1;	
	$consulta="SELECT id__17_tablaDinamica FROM _17_tablaDinamica WHERE claveUnidad='".$fDatosCarpeta[1]."'";
	$idUnidadGestion=$con->obtenerValor($consulta);
	if(!esJuezDisponibleIncidencia($idJuez,$fechaReferencia))
	{
		return obtenerJuezBinomio($idUnidadGestion,$idJuez);
	}
	else
		return $idJuez;
}

function asignarJuezTitularCarpeta($idFormulario,$idRegistro)
{
	global $con;
	$consulta="SELECT carpetaAdministrativa,juezAsignado,comentariosAdicionales FROM _620_tablaDinamica WHERE id__620_tablaDinamica=".$idRegistro;
	$fRegistro=$con->obtenerPrimeraFila($consulta);
	
	$consulta="SELECT idFormulario,idRegistro FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$fRegistro[0]."'";
	$fRegistroCarpeta=$con->obtenerPrimeraFila($consulta);
	
	$x=0;
	$query[$x]="begin";
	$x++;
	
	$query[$x]="UPDATE 7006_carpetasAdministrativas SET idJuezTitular=".$fRegistro[1]." WHERE carpetaAdministrativa='".$fRegistro[0]."'";
	$x++;
	
	$query[$x]="INSERT INTO 7001_asignacionesObjetos(idFormulario,idRegistro,idObjetoReferido,idUnidadReferida,
				fechaAsignacion,tipoRonda,noRonda,situacion,rondaPagada,comentariosAdicionales,tipoAsignacion,idAsignacionPagada)
				SELECT idFormulario,idRegistro,idObjetoReferido,".$fRegistro[1].",fechaAsignacion,tipoRonda,noRonda,situacion,rondaPagada,
				'Motivo asignación: ".cv($fRegistro[2])."' as comentariosAdicionales,tipoAsignacion,idAsignacionPagada from 7001_asignacionesObjetos
				WHERE idFormulario=".$fRegistroCarpeta[0]." AND idRegistro=".$fRegistroCarpeta[1]." AND tipoRonda IN('CU','LMLV') AND situacion=1";
	$x++;
	
	
	if($fRegistroCarpeta[0]==622)
	{
		$query[$x]="UPDATE _622_tablaDinamica SET idJuez=".$fRegistro[1]." WHERE id__622_tablaDinamica=".$fRegistroCarpeta[1];
		$x++;
	}
	
	$query[$x]="commit";
	$x++;
	
	
	return $con->ejecutarBloque($query);
}

function cancelarCarpetaEjecucion($idFormulario,$idRegistro)
{
	global $con;
	
	$query="SELECT * FROM _621_tablaDinamica WHERE id__621_tablaDinamica=".$idRegistro;
	$fRegistro=$con->obtenerPrimeraFilaAsoc($query);
	
	$query="SELECT idJuezTitular,idFormulario,idRegistro,fechaCreacion,unidadGestion FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$fRegistro["carpetaAdministrativa"]."'";
	$fRegistroCarpeta=$con->obtenerPrimeraFila($query);
	$idJuezTitular=$fRegistroCarpeta[0];
	if($idJuezTitular=="")
		$idJuezTitular=-1;
	
	$query="SELECT id__17_tablaDinamica FROM _17_tablaDinamica WHERE claveUnidad='".$fRegistroCarpeta[4]."'";
	$idUnidadGestion=$con->obtenerValor($query);
	
	$x=0;
	$consulta[$x]="begin";
	$x++;	
	$consulta[$x]="DELETE FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$fRegistro["carpetaAdministrativa"]."'";
	$x++;
	$consulta[$x]="UPDATE 7007_contenidosCarpetaAdministrativa SET carpetaAdministrativa=CONCAT('[',carpetaAdministrativa,']') 
					WHERE carpetaAdministrativa='".$fRegistro["carpetaAdministrativa"]."'";
	$x++;
	
	$consulta[$x]="UPDATE _621_tablaDinamica SET juezResponsable=".$idJuezTitular." WHERE id__621_tablaDinamica=".$idRegistro;
	$x++;
	
	
	$query="SELECT * FROM 9060_tablerosControl";
	$res=$con->obtenerFilas($query);
	while($fila=mysql_fetch_row($res))
	{
		$campoRefiere="";
		$tablero="9060_tableroControl_".$fila[0];
		if($con->existeCampo("carpetaJudicial",$tablero))
			$campoRefiere="carpetaJudicial";
		else
			if($con->existeCampo("carpetaAdministrativa",$tablero))
				$campoRefiere="carpetaAdministrativa";
			else
				if($con->existeCampo("numeroCarpetaAdministrativa",$tablero))
					$campoRefiere="numeroCarpetaAdministrativa";
				else
					if($con->existeCampo("carpetaEjecucion",$tablero))
						$campoRefiere="carpetaEjecucion";
		if($campoRefiere!="")
		{
			$consulta[$x]="DELETE FROM ".$tablero." WHERE ".$campoRefiere."='".$fRegistro["carpetaAdministrativa"]."'";
			$x++;
		}
	}
	
	
	
	$anioCarpetaOrigen=date("Y",strtotime($fRegistroCarpeta[3]));
	
	$folioCarpeta=obtenerNumerFolioCarpetaJudicial($fRegistro["carpetaAdministrativa"]);
	$folioCarpeta--;
	if($folioCarpeta<0)
		$folioCarpeta=0;
	
	$query="SELECT folioActual FROM 7004_seriesUnidadesGestion WHERE anio='".$anioCarpetaOrigen.
				"' AND idUnidadGestion=".$idUnidadGestion." AND tipoDelito='EJEC'";
	$fActual=$con->obtenerValor($query);

	if($folioCarpeta<$fActual)
	{
	
		$consulta[$x]="UPDATE 7004_seriesUnidadesGestion SET folioActual=".$folioCarpeta." WHERE anio='".$anioCarpetaOrigen.
					"' AND idUnidadGestion=".$idUnidadGestion." AND tipoDelito='EJEC'";
		$x++;
	}
	
	
	$consulta[$x]="commit";
	$x++;
	
	if ($con->ejecutarBloque($consulta))
	{
		if($fRegistroCarpeta[1]!=-1)
		{
			cambiarEtapaFormulario($fRegistroCarpeta[1],$fRegistroCarpeta[2],100,"",-1,"NULL","NULL",0);
			if($fRegistroCarpeta[1]==558)
			{
				$query="SELECT iFormulario,iRegistro FROM _558_tablaDinamica WHERE id__558_tablaDinamica=".$fRegistroCarpeta[2];
				$fDatosBase=$con->obtenerPrimeraFila($query);
				cambiarEtapaFormulario($fDatosBase[0],$fDatosBase[1],100,"",-1,"NULL","NULL",0);
			}
		}
	}
}



function actualizarCarpetaCancelada($idFormulario,$idRegistro)
{
	global $con;
	$consulta="UPDATE _621_tablaDinamica set carpetaAdministrativaCancelada=carpetaAdministrativa WHERE id__621_tablaDinamica=".$idRegistro;
	return $con->ejecutarConsulta($consulta);
}

function esHorarioNormalDiaHabilLeyMujeresLibreViolencia($horario,$tipoMateria=1)
{
	if(!esDiaHabilInstitucion($horario))
	{
		return false;
	}
	$horaSolicitud=strtotime($horario);
	$fechaSolicitud=date("Y-m-d",$horaSolicitud);
	$horarioInicial=strtotime(date("Y-m-d ",$horaSolicitud)." 09:00");
	$horarioFinalNormal=strtotime(date("Y-m-d ",$horaSolicitud)." 15:00");
	$horarioFinalNormalViernes=strtotime(date("Y-m-d ",$horaSolicitud)." 15:00");
	if($tipoMateria=="2")
	{
		$horarioFinalNormal=strtotime(date("Y-m-d ",$horaSolicitud)." 15:00");
		$horarioFinalNormalViernes=strtotime(date("Y-m-d ",$horaSolicitud)." 15:00");
	}
	$dia=date("w",$horaSolicitud);
	
	switch($dia)
	{
		case 5://Viernes
			if(($horaSolicitud>=$horarioInicial)&&($horaSolicitud<$horarioFinalNormalViernes))
				return true;
		break;
		default:
			
			if(($horaSolicitud>=$horarioInicial)&&($horaSolicitud<$horarioFinalNormal))
				return true;	
		break;
	}
	
	return false;
	
}

function esHorarioGuardiaV3LeyMujeres($horario)
{
	$horaSolicitud=strtotime($horario);
	$fechaSolicitud=date("Y-m-d",$horaSolicitud);
	$horarioInicial=strtotime(date("Y-m-d ",$horaSolicitud)." 15:00");
	$dia=date("w",$horaSolicitud);
	

	switch($dia)
	{
		case 5://Viernes
			if(!esDiaHabilInstitucion($fechaSolicitud))
			{
				return true;
				
				
			}
			if($horaSolicitud>=$horarioInicial)
				return true;
		break;
		case 0://Domingo
			if($horaSolicitud<$horarioInicial)
				return true;
			else
			{
				$diaSiguiente=date("Y-m-d",strtotime("+1 days",strtotime($fechaSolicitud)));
				if(!esDiaHabilInstitucion($diaSiguiente))
					return true;
			}
		break;
		default:
			

			if(!esDiaHabilInstitucion($fechaSolicitud))
			{

				if($horaSolicitud<$horarioInicial)
				{

					return true;
				}
				else
				{
					$diaSiguiente=date("Y-m-d",strtotime("+1 days",strtotime($fechaSolicitud)));

					if(!esDiaHabilInstitucion($diaSiguiente))
					{
						
						return true;
					}
				}
			}
			else
			{
				
				$diaSiguiente=date("Y-m-d",strtotime("+1 days",strtotime($fechaSolicitud)));
				if(!esDiaHabilInstitucion($diaSiguiente))
				{
					if($horaSolicitud>=$horarioInicial)
						return true;
				}
				
			}
		
		break;
	}
	return false;
	
}

function esHorarioGuardiaEspecialV3LeyMujeres($horario)
{
	$horaSolicitud=strtotime($horario);
	$horaInicio=strtotime("2020-12-06 15:00");
	$horaTermino=strtotime("2020-12-16 08:59");
	if(($horaSolicitud>= $horaInicio)&&($horaSolicitud<=$horaTermino))
	{

		return true;
	}
	return false;
}

function determinarTipoHorarioLeyMujeresLibreViolencia($horario)
{
	
	
	if(esHorarioGuardiaEspecialV3LeyMujeres($horario))
	{
		
		return 4;
	}
	else
	{
		if(esHorarioGuardiaV3LeyMujeres($horario))
			return 2;
		else
			if(esHorarioNormalDiaHabilLeyMujeresLibreViolencia($horario,1))
				return 1;
			else
				return 3;
	}
}
?>