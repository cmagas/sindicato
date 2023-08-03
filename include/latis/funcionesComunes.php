<?php include("latis/funcionesNomina.php"); 
include("latis/funcionesRendererListadoRegistros.php");
include_once("latis/funcionesFormularios.php");
include_once("latis/funcionesSistemaAux.php");

function generarNombre($nomCampo,$tipo)
{
	$nombreCampo="_".$nomCampo;
	$sufijo="";
	switch($tipo)
	{
		case 2: //pregunta cerrada-Opciones Manuales
			$sufijo="vch";
		break;					
		case 3: //pregunta cerrada-Opciones intervalo
			$sufijo="vch";
		break;
		case 4: //pregunta cerrada-Opciones tabla
			$sufijo="vch";
		break;
		case 5: //Texto Corto
			$sufijo="vch";
		break;
		case 6: //Número enterof
			$sufijo="int";
		break;
		case 7: //Número decimal
			$sufijo="flo";
		break;
		case 8: //Fecha
			$sufijo="dte";
		break;
		case 9://Texto Largo 
			$sufijo="mem";
		break;
		case 10: //Texto Enriquecido
			$sufijo="vch";
		break;
		case 11: //Correo Electrónico
			$sufijo="vch";
		break;
		case 12: //Archivo
			$sufijo="fil";
		break;
		case 14:
			$sufijo="vch";
		break;
		case 15:
			$sufijo="vch";
		break;
		case 16:
			$sufijo="vch";
		break;
		case 17:
			$sufijo="arr";
		break;
		case 18:
			$sufijo="arr";
		break;
		case 19:
			$sufijo="arr";
		break;
		case 20:
			$sufijo="vch";
		break;
		case 21:
			$sufijo="tme";
		break;
		case 22:
			$sufijo="flo";
		break;
		case 23:
			$sufijo="img";
		break;
		case 24:
			$sufijo="flo";
		break;
		case 25:
			$sufijo="tme";
		break;
		case 26:
			$sufijo='vch';
		break;
		case 30:
			$sufijo='vch';
		break;
		case 31:
			$sufijo='vch';
		break;
		case 33:
			$sufijo="img";
		break;
		default:
			$sufijo='vch';
		break;
	}
	$nombreCampoFinal=$nombreCampo.$sufijo;
	return $nombreCampoFinal;
}

function cambiarEtapaFormulario($idFormulario,$idRegistro,$etapa,$comentarios="",$idPerfil=-1,$res1="NULL",$res2="NULL",$idActorProceso="0")
{
	global $con;
	

	if($etapa==="")
	{
		echo "No se ha configurado la etapa a la cual pasar&aacute; el registro";
		return false;
	}
	$consulta="select nombreTabla from 900_formularios where idFormulario=".$idFormulario;

	$nomTabla=$con->obtenerValor($consulta);
	$consulta="select idEstado from ".$nomTabla." where id_".$nomTabla."=".$idRegistro;
	$estadoAnterior=$con->obtenerValor($consulta);
	
	$llaveTarea=bE($idFormulario."_".$idRegistro."_".removerCerosDerecha($estadoAnterior));

	
	$x100584=0;
	$query100584[$x100584]="begin";
	$x100584++;
	$query100584[$x100584]="update ".$nomTabla." set idEstado=".$etapa." where id_".$nomTabla."=".$idRegistro;

	$x100584++;
	$idUsuario=-1;
	if(isset($_SESSION["idUsr"])&&($_SESSION["idUsr"]!=""))
		$idUsuario=$_SESSION["idUsr"];
	
	
	
	$query100584[$x100584]="insert into 941_bitacoraEtapasFormularios(etapaActual,fechaCambio,idUsuarioCambio,etapaAnterior,idFormulario,idRegistro,comentarios,referencia1,referencia2,actorCambio) 
				values (".$etapa.",'".date('Y-m-d H:i:s')."',".$idUsuario.",".$estadoAnterior.",".$idFormulario.",".$idRegistro.",'".cv($comentarios)."',".($res1==""?"NULL":$res1).",".($res2==""?"NULL":$res2).",".$idActorProceso.")";
	$x100584++;
	$query100584[$x100584]=cancelarTemporizadoresProceso($idFormulario,$idRegistro,$estadoAnterior,false);
	$x100584++;
	

	$arrTemporizadores=registrarTemporizadorProceso($idFormulario,$idRegistro,$etapa,false);
	if(sizeof($arrTemporizadores)>0)
	{
		foreach($arrTemporizadores as $t)
		{
			$query100584[$x100584]=$t;
			$x100584++;
		}
	}

	$query100584[$x100584]="commit";
	$x100584++;
	
	if($con->ejecutarBloque($query100584))
	{
		$idProceso=obtenerIdProcesoFormulario($idFormulario);
		
		$consulta="SELECT * FROM 9071_configuracionNotificacionProceso WHERE idProceso=".$idProceso." AND etapa=".$estadoAnterior.
				" AND idPerfil=".$idPerfil;  

		$resNotificacionesAnterior=$con->obtenerFilas($consulta);		
		while($fNotificacion=mysql_fetch_assoc($resNotificacionesAnterior))
		{

			if(($fNotificacion["marcarAtendidaCambioEtapa"]==1)&&($fNotificacion["confComplementaria"]!=""))
			{
				$confComplementaria=json_decode(bD($fNotificacion["confComplementaria"]));
				
				$aplicarTareaAtendida=true;
				if(($confComplementaria->funcionAplicacion!="")&&($confComplementaria->funcionAplicacion!=-1))
				{
					$cacheCalculos=NULL;
					$cadParametros='{"idFormulario":"'.$idFormulario.'","idReferencia":"'.$idRegistro.'","idProceso":"'.$idProceso.'",
									"estadoOrigen":"'.$estadoAnterior.'","etapaDestino":"'.$etapa.'","idActorDetonante":"'.
									($idActorProceso==""?0:$idActorProceso).'"}';
					$objParametros=json_decode($cadParametros);
					$resultadoEvaluacion=removerComillasLimite(resolverExpresionCalculoPHP($confComplementaria->funcionAplicacion,$objParametros,$cacheCalculos));
					if(($resultadoEvaluacion==0)||($resultadoEvaluacion==false))
					{
						$aplicarTareaAtendida=false;
					}
				}
				
				
				if($aplicarTareaAtendida)
				{
					
					
					$consulta="SELECT tableroControlAsociado FROM 9067_notificacionesProceso WHERE idNotificacion=".$fNotificacion["tipoNotificacion"];
					$tableroControlAsociado=$con->obtenerValor($consulta);
					
					$afectarNotificionUsuario=$confComplementaria->afectarNotificionUsuario;
					
					$afectarNotificacionesDelegadas=$confComplementaria->afectarNotificacionesDelegadas==1?true:false;
					$afectarNotificacionesPadre=$confComplementaria->afectarNotificacionesPadre==1?true:false;
					
					
					$consulta="SELECT * FROM 9060_tableroControl_".$tableroControlAsociado." WHERE llaveTarea='".$llaveTarea."'";
					if($afectarNotificionUsuario==1)
					{
						$consulta.=" and idUsuarioDestinatario=".$_SESSION["idUsr"];
					}
					
					$rNotificacion=$con->obtenerFilas($consulta);
					while($fNotificacionMarca=mysql_fetch_assoc($rNotificacion))
					{
						if($afectarNotificionUsuario==1)
						{
							if(!$afectarNotificacionesPadre)
								setTareaAtendida($fNotificacionMarca["idRegistro"],$tableroControlAsociado,$_SESSION["idUsr"],$afectarNotificacionesDelegadas);
							else
							{
								if(!$afectarNotificacionesDelegadas)
								{
									setTareaAtendida($fNotificacionMarca["idRegistro"],$tableroControlAsociado,$_SESSION["idUsr"],$afectarNotificacionesDelegadas);
										
									$idNotificacionBase=$fNotificacionMarca["idNotificacionBase"];
									while($idNotificacionBase!="")
									{
										setTareaAtendida($idNotificacionBase,$tableroControlAsociado,$_SESSION["idUsr"],$afectarNotificacionesDelegadas);
										$consulta="SELECT idNotificacionBase FROM 9060_tableroControl_".$tableroControlAsociado." WHERE idRegistro=".$idNotificacionBase;
										$idNotificacionBase=$con->obtenerValor($consulta);
									}
									
									
								}
								else
									marcarTareaAtendida($fNotificacionMarca["idRegistro"],$tableroControlAsociado,$_SESSION["idUsr"],$afectarNotificacionesPadre,$afectarNotificacionesDelegadas);
							}
						}
						else
						{
							setTareaAtendida($fNotificacionMarca["idRegistro"],$tableroControlAsociado,$_SESSION["idUsr"],false);
						}
						
						
					}
					
				}
				
			}
		}
		
		
		if(esFormularioBase($idFormulario))
		{
			
			$cuerpo=generarCuerpoExpresion($idProceso,$etapa,$idFormulario,$idRegistro,$idPerfil);

			eval ($cuerpo);
			ejecutarListadoNotificaciones($idProceso,$idFormulario,$idRegistro,$etapa,$idPerfil,$idActorProceso);
			
			
		}
		
		return true;
	}
}


function ejecutarListadoNotificaciones($idProceso,$idFormulario,$idRegistro,$etapa,$idPerfil,$idActorProceso)
{
	global $con;
	
	$cacheCalculos=NULL;
	$consulta="SELECT idReferencia FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	
	$idReferencia=$con->obtenerValor($consulta);
	
	$cadParametros='{"idFormulario":"'.$idFormulario.'","idRegistro":"'.$idRegistro.'","idProceso":"'.$idProceso.'","idActorProceso":"",
					"etapa":"'.$etapa.'","idReferencia":"'.$idReferencia.'","idActorDetonante":"'.$idActorProceso.
					'","idUsuarioDestinatario":""}';
	$objParametros=json_decode($cadParametros);
	
	$consulta="SELECT * FROM 9071_configuracionNotificacionProceso WHERE idProceso=".$idProceso." AND etapa=".$etapa.
				" AND idPerfil=".$idPerfil." AND notificacionActiva=1";
	
	
	$resConfiguracion=$con->obtenerFilas($consulta);
	while($filaConfiguracion=mysql_fetch_row($resConfiguracion))
	{	

		$objParametros->idActorProceso=$filaConfiguracion[5];
		$rolActor="";
	
		if($idActorProceso!=0)
		{
			$consulta="SELECT actor,tipoActor FROM 944_actoresProcesoEtapa WHERE idActorProcesoEtapa=".$idActorProceso;

			$fActor=$con->obtenerPrimeraFila($consulta);
			if($fActor[1]==1)
				$rolActor=obtenerTituloRol($fActor[0]);
			else
			{
				$consulta="SELECT nombreComite FROM 2006_comites WHERE idComite=".$fActor[0];
				$rolActor='Comite: '.$con->obtenerValor($consulta);
			}
		}
		else
		{
			$rolActor="Actor de sólo lectura";
		}

		$nombreUsuarioRemitente=obtenerNombreUsuario($_SESSION["idUsr"]).' ('.$rolActor.')';
		
		$cadParametros='{"idFormulario":"'.$idFormulario.'","idRegistro":"'.$idRegistro.'","idReferencia":"'.$idReferencia.'","idProceso":"'.$idProceso.'","idActorProceso":"'.$idActorProceso.
					'","etapa":"'.$etapa.'","idUsuarioRemitente":"'.$_SESSION["idUsr"].'","nombreUsuarioRemitente":"'.$nombreUsuarioRemitente.'",
					"idUsuarioDestinatario":"","nombreUsuarioDestinatario":"","permiteAccesoProceso":"'.$filaConfiguracion[7].
					'","actorAccesoProceso":"'.($filaConfiguracion[8]==""?0:$filaConfiguracion[8]).'"}';
					
		$objParametrosNotificacion=json_decode($cadParametros);
		
		$actorDestinatario=$filaConfiguracion[5]==""?0:$filaConfiguracion[5];
		$funcionAsignacionDestinatario=$filaConfiguracion[6];
		
		$arrDestinatariosNotificacion=array();

		if(($funcionAsignacionDestinatario!="")&&($funcionAsignacionDestinatario!=-1))
		{

			$cadParametros='{"idFormulario":"'.$idFormulario.'","idRegistro":"'.$idRegistro.'","idProceso":"'.$idProceso.
							'","idActorProceso":"'.($idActorProceso==""?0:$idActorProceso).'","etapa":"'.$etapa.
							'","actorDestinatario":"'.$actorDestinatario.'"}';
			$objParametrosAsignacionDestinatario=json_decode($cadParametros);
			
			$arrDestinatariosNotificacion=resolverExpresionCalculoPHP($funcionAsignacionDestinatario,$objParametrosAsignacionDestinatario,$cacheCalculos);
			
		}
		else
		{
			if($actorDestinatario!="")		
			{
				$rolActor=obtenerTituloRol($actorDestinatario);

				$consulta="SELECT u.idUsuario FROM 800_usuarios u,807_usuariosVSRoles r WHERE u.idUsuario=r.idUsuario AND ";				
				
				$arrDestinatarios=explode("_",$actorDestinatario);
				if($arrDestinatarios[1]==0)
					$consulta.="r.idRol=".$arrDestinatarios[0];
				else
					$consulta.="r.codigoRol='".$actorDestinatario."'";						
					
				
					
				$resDestinatarios=$con->obtenerFilas($consulta);
				while($fDestinatario=mysql_fetch_row($resDestinatarios))	
				{
					
					$nombreUsuario=obtenerNombreUsuario($fDestinatario[0])." (".$rolActor.")";
					$nombreUsuario=str_replace(" (Suplantado)","",$nombreUsuario);
					$o='{"idUsuarioDestinatario":"'.$fDestinatario[0].'","nombreUsuarioDestinatario":"'.$nombreUsuario.'"}';
					$oDestinatario=json_decode($o);
					
					array_push($arrDestinatariosNotificacion,$oDestinatario);
				}
					
				
			}
		}

		foreach($arrDestinatariosNotificacion as $d)
		{
			
			$objParametros->idUsuarioDestinatario=$d->idUsuarioDestinatario;
			$considerarNotificacion=true;
			if($filaConfiguracion[4]!="")
			{
				$resultadoEvaluacion=removerComillasLimite(resolverExpresionCalculoPHP($filaConfiguracion[4],$objParametros,$cacheCalculos));
				if($resultadoEvaluacion==0)
				{
					$considerarNotificacion=false;
				}
			}
			if($considerarNotificacion)
			{
				if(isset($d->actorDestinatario))
				{
					$objParametrosNotificacion->actorAccesoProceso=$d->actorDestinatario;
				}
				else
				{
					$objParametrosNotificacion->actorAccesoProceso=$actorDestinatario;
				}
				$objParametrosNotificacion->idUsuarioDestinatario=$d->idUsuarioDestinatario;	
				$objParametrosNotificacion->nombreUsuarioDestinatario=$d->nombreUsuarioDestinatario;
				
				@registrarNotificacionTableroControl($filaConfiguracion[3],$objParametrosNotificacion);
			}
			
		}
	}
	
}

function registrarNotificacionTableroControl($idNotificacion,$objParametrosNotificacion)
{
	global $con;
	global $considerarSecretariasTareas;

	$cacheCalculos=NULL;
	
	$arrQueries=resolverQueries($idNotificacion,9067,$objParametrosNotificacion,true);
	
	$consulta="SELECT * FROM 9067_notificacionesProceso WHERE idNotificacion=".$idNotificacion;
	
	$fNotificacion=$con->obtenerPrimeraFilaAsoc($consulta);

	$tableroControlAsociado=$fNotificacion["tableroControlAsociado"];
	$nombreTabla="9060_tableroControl_".$tableroControlAsociado;
	
	$consulta="SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='".$con->bdActual."' AND TABLE_NAME='".$nombreTabla."' ORDER BY COLUMN_NAME";
	$listaCamposTabla=$con->obtenerListaValores($consulta,"'");
	if($listaCamposTabla=="")
		$listaCamposTabla=-1;
		
	$arrCamposValor=array();
	$listaCampos="";
	$listaValores="";
	$consulta="SELECT campoTablero,tipoLlenado,valor FROM 9068_configuracionNotificacionTableroControl WHERE 
				idNotificacion=".$idNotificacion." and tipoLlenado is not null and campoTablero in (".$listaCamposTabla.") and campoTablero not in('llaveTarea')";

		
	$rCamposTablero=$con->obtenerFilas($consulta);
	while($fCampoTablero=mysql_fetch_row($rCamposTablero))
	{
		
		$valor=$fCampoTablero[2];
		$valorFinal="NULL";
		switch($fCampoTablero[1])
		{
			case 1://Valor de sesión
				$consulta="SELECT valorSesion FROM 8003_valoresSesion WHERE idValorSesion=".$valor;
				
				$valor=$con->obtenerValor($consulta);
				$valorFinal="'".$_SESSION[$valor]."'";
			break;
			case 2://valor de sistema
				switch($valor)
				{
					case 8:  //Fecha del sistema
						$valorFinal="'".date("Y-m-d")."'";
					break;
					case 9:	//Hora del sistema
						$valorFinal="'".date("H:i:s")."'";
					break;
					case 10:	//Hora del sistema
						$valorFinal="'".date("Y-m-d H:i:s")."'";
					break;
				}
			break;
			case 3: //Consulta auxiliar			
				if(isset($arrQueries[$valor]))
				{
					if($arrQueries[$valor]["ejecutado"]==1)
					{
						$valorFinal="'".removerComillasLimite($arrQueries[$valor]["resultado"])."'";
					}
				}			
			break;
			case 4: //Almacén de datos	
				if($valor!="")
				{
					$valor=json_decode($valor);
					
					if(isset($arrQueries[$valor->idAlmacen]))
					{
						if($arrQueries[$valor->idAlmacen]["ejecutado"]==1)
						{
							$res=$arrQueries[$valor->idAlmacen]["resultado"];
							
							$conAux=$arrQueries[$valor->idAlmacen]["conector"];
							$conAux->inicializarRecurso($res);
							
							while($f=$conAux->obtenerSiguienteFilaAsoc($res))
							{
								$nCampo=str_replace(".","_",$valor->campo);
								
								
								if(isset($f[$nCampo]))
								{
									$valorFinal="'".$f[$nCampo]."'";
									
								}
								break;
							}
						}
					}			
				}
			break;
			case 5:
				eval('$valorFinal=isset($objParametrosNotificacion->'.$valor.')?"\'".$objParametrosNotificacion->'.$valor.'."\'":"NULL";');
				
			break;
			case 6:			
				if($valor!="")
				{
					if($valor>0)
					{
						
						$valorFinal="'".obtenerValorControlFormularioBase($valor,$objParametrosNotificacion->idRegistro)."'";
						
						
					}
					else
					{
						
						$consulta="SELECT campoMysql FROM 9017_camposControlFormulario WHERE tipoElemento=".$valor;
						$campoMysql=$con->obtenerValor($consulta);
						
						$consulta="SELECT ".$campoMysql." FROM _".$objParametrosNotificacion->idFormulario."_tablaDinamica WHERE id__".$objParametrosNotificacion->idFormulario."_tablaDinamica=".$objParametrosNotificacion->idRegistro;
						$valorFinal="'".$con->obtenerValor($consulta)."'";
						
					}
				}
				
			break;
			case 7:	
				//funcion de sistema
				
				$objParametros=$objParametrosNotificacion;
				if($valor!="")
					$valorFinal="'".removerComillasLimite(resolverExpresionCalculoPHP($valor,$objParametros,$cacheCalculos))."'";
				
			break;
			case 8:
				$valorFinal="'".$valor."'";				
				
			break;
			
			
		}
		
		
		$arrCamposValor[$fCampoTablero[0]]=$valorFinal;

		
		
	}	
	
	
	
	/*if($_SESSION["idUsr"]==1)
	{
		varDUMP($arrCamposValor);
		return;
	}*/

	
	foreach($arrCamposValor as $campo=>$valor)
	{
		$listaCampos.=",".$campo;
		$listaValores.=",".$valor;
		
	}
	
	$contenidoMensaje=$fNotificacion["cuerpoNotificacion"];
	
	$arrValoresCuerpo=$fNotificacion["arrValoresCuerpo"];

	if($arrValoresCuerpo!="[]")
	{
		$arrValoresCuerpo=json_decode('{"valores":'.$arrValoresCuerpo.'}');
	
		foreach($arrValoresCuerpo->valores as $r)
		{
			
			$valor=resolverParametroCuerpoNotificacion($r,$objParametrosNotificacion,$arrQueries);
			
			$contenidoMensaje=str_replace($r->lblVariable,$valor,$contenidoMensaje);
		}
	}
	$objConfiguracion="";	
	
	if($objParametrosNotificacion->permiteAccesoProceso==1)
	{
		$objConfiguracion='{"actorAccesoProceso":"'.$objParametrosNotificacion->actorAccesoProceso.'","funcionApertura":"mostrarVentanaAperturaProcesoNotificacion"}';
	}
	
	$llaveTarea=bE($objParametrosNotificacion->idFormulario."_".$objParametrosNotificacion->idRegistro."_".(removerCerosDerecha($objParametrosNotificacion->etapa)));

	if($objParametrosNotificacion->idReferencia=="")
		$objParametrosNotificacion->idReferencia=-1;
	
	$nReg=0;
	if($fNotificacion["repetible"]==0)
	{
		$consulta="SELECT COUNT(*) FROM ".$nombreTabla." WHERE idUsuarioDestinatario=".$objParametrosNotificacion->idUsuarioDestinatario.
				" AND idNotificacion=".$fNotificacion["idNotificacion"]." and iFormulario=".$objParametrosNotificacion->idFormulario.
				" and iRegistro=".$objParametrosNotificacion->idRegistro." and usuarioDestinatario='".
				cv($objParametrosNotificacion->nombreUsuarioDestinatario)."' and llaveTarea='".$llaveTarea."'";
		
		$nReg=$con->obtenerValor($consulta);	
	}
	
	$agregarSecretaria=false;
	if($nReg==0)
	{
		$consulta="SELECT Institucion FROM 801_adscripcion WHERE idUsuario=".$objParametrosNotificacion->idUsuarioDestinatario;
		$codigoUnidad=$con->obtenerValor($consulta);
		if(($considerarSecretariasTareas)&&(!isset($arrCamposValor["secretaria"]))&& ($con->existeCampo("secretaria",$nombreTabla)))
		{
			$agregarSecretaria=true;
		}

		if(!$agregarSecretaria)
		{
		
			$consulta="INSERT INTO ".$nombreTabla."(codigoUnidad,fechaAsignacion,idNotificacion,tipoNotificacion,
						usuarioRemitente,idUsuarioRemitente,usuarioDestinatario,idUsuarioDestinatario,idEstado,contenidoMensaje,
						iFormulario,iRegistro,iReferencia,objConfiguracion,permiteAbrirProceso,llaveTarea".$listaCampos.")
						values('".$codigoUnidad."','".date("Y-m-d H:i:s")."',".$fNotificacion["idNotificacion"].",'".
						cv($fNotificacion["tituloNotificacion"])."','".cv($objParametrosNotificacion->nombreUsuarioRemitente).
						"',".$objParametrosNotificacion->idUsuarioRemitente.",'".cv($objParametrosNotificacion->nombreUsuarioDestinatario).
						"',".$objParametrosNotificacion->idUsuarioDestinatario.",1,'".cv($contenidoMensaje)."',".
						$objParametrosNotificacion->idFormulario.",".$objParametrosNotificacion->idRegistro.",".
						$objParametrosNotificacion->idReferencia.",'".cv($objConfiguracion)."',".
						$objParametrosNotificacion->permiteAccesoProceso.",'".$llaveTarea."'".$listaValores.")";
		}
		else
		{
			$secretaria="";
			$tipoExpediente="";
			$cAdministrativa=obtenerCarpetaAdministrativaProceso($objParametrosNotificacion->idFormulario,$objParametrosNotificacion->idRegistro);
			if(($cAdministrativa!="")&&($cAdministrativa!=-1))
			{
				$consulta="SELECT codigoInstitucion FROM _".$objParametrosNotificacion->idFormulario.
							"_tablaDinamica WHERE id__".$objParametrosNotificacion->idFormulario."_tablaDinamica=".$objParametrosNotificacion->idRegistro;
							
				$cInstitucion=$con->obtenerValor($consulta);
				
				$consulta="SELECT secretariaAsignada,tipoCarpetaAdministrativa FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$cAdministrativa.
							"' AND unidadGestion='".$cInstitucion."'";
				$fSecretaria=$con->obtenerPrimeraFila($consulta);
				$secretaria=$fSecretaria[0];
				$tipoExpediente=$fSecretaria[1];
				if($secretaria=="")
				{
					$secretaria=determinarSecretariaExpediente($cAdministrativa);
				}
			}
			
			if((!isset($arrCamposValor["tipoCarpetaAdministrativa"]))&& ($con->existeCampo("tipoCarpetaAdministrativa",$nombreTabla)))
			{
				$listaCampos.=",tipoCarpetaAdministrativa";
				$listaValores.=",'".$tipoExpediente."'";
			}
			
			
			$consulta="INSERT INTO ".$nombreTabla."(codigoUnidad,fechaAsignacion,idNotificacion,tipoNotificacion,usuarioRemitente,
						idUsuarioRemitente,usuarioDestinatario,idUsuarioDestinatario,idEstado,contenidoMensaje,iFormulario,iRegistro,
						iReferencia,objConfiguracion,permiteAbrirProceso,secretaria,llaveTarea".$listaCampos.")
						values('".$codigoUnidad."','".date("Y-m-d H:i:s")."',".$fNotificacion["idNotificacion"].",'".
						cv($fNotificacion["tituloNotificacion"])."','".cv($objParametrosNotificacion->nombreUsuarioRemitente).
						"',".$objParametrosNotificacion->idUsuarioRemitente.",'".cv($objParametrosNotificacion->nombreUsuarioDestinatario).
						"',".$objParametrosNotificacion->idUsuarioDestinatario.",1,'".cv($contenidoMensaje)."',".
						$objParametrosNotificacion->idFormulario.",".$objParametrosNotificacion->idRegistro.",".
						$objParametrosNotificacion->idReferencia.",'".cv($objConfiguracion)."',".$objParametrosNotificacion->permiteAccesoProceso.
						",'".$secretaria."','".$llaveTarea."'".$listaValores.")";
		}
		
		if(@$con->ejecutarConsulta($consulta))
		{	
		
			return true;
		}
		else
		{
			registrarErrorBitacoraNotificacion($idNotificacion,$objParametrosNotificacion,$contenidoMensaje);
		}
	}
	return true;
}

function registrarErrorBitacoraNotificacion($idNotificacion,$objParametrosNotificacion,$cuerpoNotificacion)
{
	global $con;

	$consulta="INSERT INTO 9072_bitacoraRegistroNotificacionesProceso(fechaRegistro,resultado,idNotificacion,datosNotificacion,cuerpoNotificacion)
			VALUES('".date("Y-m-d H:i:s")."',0,".$idNotificacion.",'".cv(serialize($objParametrosNotificacion))."','".cv($cuerpoNotificacion)."')";
	return @$con->ejecutarConsulta($consulta);
}

function resolverParametroCuerpoNotificacion($oValor,$objParametrosNotificacion,$arrQueries)
{
	global $con;
	

	$valor="";

	switch($oValor->tVariable)
	{
		case 1:   	//V. Sesion
			$consulta="select valorSesion,valorReemplazo from 8003_valoresSesion where idValorSesion=".$oValor->valor1;
			$filaSesion=$con->obtenerPrimeraFila($consulta);
			$valor=$_SESSION[$filaSesion[0]];
			
		break;
		case 2:		//V. Sistema
			$valorSistema="";
			switch($oValor->valor1)
			{
				case "8":
					$valorSistema=date("Y-m-d");
				break;
				case "9":
					$valorSistema=date("H:i");
				break;
			}
			$valor=$valorSistema;
		break;
		case 3:		//Consulta aux.
			if(isset($arrQueries[$oValor->valor1]))
			{
				if($arrQueries[$oValor->valor1]["ejecutado"]==1)
				{
					
					$valor=removerComillasLimite($arrQueries[$oValor->valor1]["resultado"]);
					
				}
			}
		break;
		case 4:		//Alm. datos
			if(isset($arrQueries[$oValor->valor1]))
			{
				
				if($arrQueries[$oValor->valor1]["ejecutado"]==1)
				{
					$res=$arrQueries[$oValor->valor1]["resultado"];
					
					$conAux=$arrQueries[$oValor->valor1]["conector"];
					$conAux->inicializarRecurso($res);
					while($f=$conAux->obtenerSiguienteFilaAsoc($res))
					{
						$nCampo=str_replace(".","_",$oValor->valor2);
						
						$valorAux="";
						
						if(isset($f[$nCampo]))
						{
							$valorAux=$f[$nCampo];
							
						}
						else
							break;
						if($valor=="")
							$valor=$valorAux;
						else
							$valor.=", ".$valorAux;
					}
				}
			}
		break;
		case 5:		//V. Parametro				
			eval('$valor=isset($objParametrosNotificacion->'.$oValor->valor1.')?$objParametrosNotificacion->'.$oValor->valor1.':"";');
		break;
		case 6:		//V. Manual
			$valor=$oValor->valor1;
		break;
	}
	
	if($oValor->renderer!=0)
	{
		$cadObj='{"param1":"'.$valor.'"}';
		$objParam=json_decode($cadObj);
		
		$nulo=NULL;		
		
		$valor=bD(removerComillasLimite(resolverExpresionCalculoPHP($oValor->renderer,$objParam,$nulo)));

		
	}
	
	return $valor;
	
}	

function obtenerIdPerfilActivoProcesoActor($idActor,$idFormulario)
{
	global $con;
	
	
	$consulta="SELECT actor,tipoActor,idProceso FROM 944_actoresProcesoEtapa WHERE idActorProcesoEtapa=".$idActor;
	$fActor=$con->obtenerPrimeraFila($consulta);
	
	$consulta="SELECT idPerfil FROM 206_perfilesEscenarios WHERE tipoActor=".$fActor[1]." AND actor='".$fActor[0]."' AND situacion=1";
	$idPerfil=$con->obtenerValor($consulta);
	if($idPerfil=="")
		$idPerfil=-1;
	
	return $idPerfil;
	
	
}
	
function obtenerRegistros($idFormulario,$condWParam=" 1=1",$limite="",$cObj="")
{
	global $con;
	$consulta="select nombreTabla,idFrmEntidad from 900_formularios where idFormulario=".$idFormulario;
	$fila=$con->obtenerPrimeraFila($consulta);
	$tablaFormulario=$fila[0];
	$consulta="select f.idConfGrid,campoAgrupacion from 909_configuracionTablaFormularios f where f.idFormulario=".$idFormulario;
	$filaConfFrm=$con->obtenerPrimeraFila($consulta);
	$idConfiguracion=$filaConfFrm[0];
	$campoAgrupacion=$filaConfFrm[1];
	$idProceso=obtenerIdProcesoFormulario($idFormulario);
	$cadObj='{"p16":{"p1":"'.$idFormulario.'","p2":"'.$idProceso.'","p3":"-1","p4":"1","p5":"-1","p6":"-1"}}';
	if($cObj!="")
		$cadObj=$cObj;
	$paramObj=json_decode($cadObj);

	$arrQueries=resolverQueries($idFormulario,5,$paramObj,true);


	if($con->existeTabla($tablaFormulario))
	{
			//Valores directos de tabla
		$consulta="	select e.nombreCampo,e.idGrupoElemento from 901_elementosFormulario e,907_camposGrid cg 
					where tipoElemento not in(-1,0,1,2,4,14,16) and cg.idElementoFormulario=e.idGrupoElemento and cg.idConfGrid=".$idConfiguracion." order by idGrupoCampo";
		$listaCamposAux=$con->obtenerListaValores($consulta,"`");
		if($listaCamposAux!="")
			$listaCamposAux=",".$listaCamposAux;
		$camposAux="id_".$tablaFormulario." as idRegistro,idReferencia".$listaCamposAux;
		//valor de contenidos en otras tablas
		$consulta="	select e.nombreCampo,e.idGrupoElemento,tipoElemento from 901_elementosFormulario e,907_camposGrid cg 
					where (tipoElemento=4 or tipoElemento=16) and cg.idElementoFormulario=e.idGrupoElemento and cg.idConfGrid=".$idConfiguracion." order by idGrupoCampo";
		$res=$con->obtenerFilas($consulta);
		$camposRefTablas="";
		while($filas=mysql_fetch_row($res))
		{
			$consultaRefTablas=obtenerValorControlFormularioBase($filas[1],$paramObj->p16->p3,$paramObj);
			$camposRefTablas.=",'".$consultaRefTablas."' as `".$filas[0]."`";

		}

		//valor de opciones ingresadas por el usuario manualmente
		$consulta="	select e.nombreCampo,e.idGrupoElemento from 901_elementosFormulario e,907_camposGrid cg 
					where (tipoElemento=2 or tipoElemento=14) and cg.idElementoFormulario=e.idGrupoElemento and cg.idConfGrid=".$idConfiguracion." order by idGrupoCampo";
		$res=$con->obtenerFilas($consulta);
		$camposRefOpciones="";
		while($filas=mysql_fetch_row($res))
		{
			$consultaRefTablas="(select contenido from 902_opcionesFormulario where idIdioma=".$_SESSION["leng"]." and idGrupoElemento=".$filas[1]." and valor=".$tablaFormulario.".".$filas[0]." )";
			$camposRefOpciones.=",".$consultaRefTablas." as `".$filas[0]."`";
		}
		
		//valores de variables de sistema
		
		$consulta="select 
					case cg.idElementoFormulario
					 when '-10' then 'fechaCreacion'
					 when '-11' then 'responsableCreacion'
					 when '-12' then 'fechaModificacion'
					 when '-13' then 'responsableModificacion'
					 when '-14' then 'unidadUsuarioRegistro'
					 when '-15' then 'institucionUsuarioRegistro'
					 when '-16' then 'dtefechaSolicitud'
					 when '-17' then 'tmeHoraInicio'
					 when '-18' then 'tmeHoraFin'
					 when '-19' then 'dteFechaAsignada'
					 when '-20' then 'tmeHoraInicialAsignada'
					 when '-21' then 'tmeHoraFinalAsignada'
					 when '-22' then 'unidadReservada'
					 when '-23' then 'tmeHoraSalida'
					 when '-24' then 'idEstado'
					 when '-25' then 'codigoRegistro'
					 end as nombreCampo
					 ,cg.idElementoFormulario
					 from 907_camposGrid cg where
					 cg.idIdioma=".$_SESSION["leng"]." and cg.idElementoFormulario<0 and cg.idConfGrid=".$idConfiguracion;
		$res=$con->obtenerFilas($consulta);	
		$camposRefSistema="";
		while($filas=mysql_fetch_row($res))
		{
			$consulta="select sentenciaDefinicionValor from 9017_camposControlFormulario where tipoElemento=".$filas[1];
			$sentencia=$con->obtenerValor($consulta);
			$sentencia=str_replace('"',"",$sentencia);
			$sentencia=str_replace("@tablaFormulario","_".$paramObj->p16->p1."_tablaDinamica",$sentencia);
			$sentencia=str_replace("@idProceso",$paramObj->p16->p2,$sentencia);
			$consultaRefSistema=$sentencia;
			if($filas[1]!="-100")
				$camposRefSistema.=",".$consultaRefSistema." as `".$filas[0]."`";
		}

		$condWhere=" where 1=1 and ".$condWParam;
		$consulta="select  * from (select ".$camposAux.$camposRefTablas.$camposRefOpciones.$camposRefSistema." from ".$tablaFormulario." ".$condWhere.") as vQuery ".$limite;
		return $consulta;
	}
}

function formatearValorFicha($valor,$tipo)
{
	switch($tipo)
	{
		
		case "8": //formato fecha
		case "-10":
		case "-12":
		case "-16":
		case "-19":
			if($valor!="")
			{
				$arrDatos=explode(" ",$valor);
				if(sizeof($arrDatos)==1)
					return date('d/m/Y',strtotime($valor));
				else
					return date('d/m/Y H:i',strtotime($valor));
			}
			return "N/E";
		break;
		case 10:
			return rF($valor);
		break;
		case "12": //Archivo
		if($valor!="")
			return '<a href="../paginasFunciones/obtenerArchivos.php?id='.bE($valor).'"><img src="../images/download.png" alt="Descargar" title="Descargar" />&nbsp;&nbsp;Descargar</a>';
		else
			return "";
		break;
		
		case "-17": //Tiempo
		case "-18":
		case "-20":
		case "-21":
		case "-23":
		case 21:
			return date('h:i A',strtotime($valor));
		break;
		
		default:
			return $valor;
	}
}

function obtenerSemanasTrabajo($fI,$fF) // entrada en formato cadena YYYY-mm-dd
{
	$arrSemanas=array();
	
	$fInicio=strtotime($fI);
	$fFin=strtotime($fF);
	$dInicio=date("N",$fInicio);
	
	$fechaInicioSemana=$fInicio;
	$x=0;
	if($dInicio!=1)
		$fechaInicioSemana=strtotime('-'.($dInicio-1).' days',$fInicio);
	while($fechaInicioSemana<=$fFin)
	{
		
		$fechaFinSemana=strtotime("+ 6 days ",$fechaInicioSemana);		
		$arrSemanas[$x]['nSemana']=($x+1);
		$arrSemanas[$x]['fechaI']=$fechaInicioSemana;
		$arrSemanas[$x]['fechaF']=$fechaFinSemana;
		$x++;
		$fechaInicioSemana=strtotime('+7 days',$fechaInicioSemana);
		
	}
	
	return $arrSemanas;
}

function obtenerAvanceActividadUsuario($idActividad)
{
	global $con;
	$consulta="select porcentajeAvance from 973_reporteActividades where situacion=2 and idActividad=".$idActividad;
	$res=$con->obtenerFilas($consulta);
	$porcAvance=0;
	while($fila=mysql_fetch_row($res))
	{
		if($fila[0]!="")
			$porcAvance+=$fila[0];
	}
	return $porcAvance;
}

function obtenerInicialesFrase($frase)
{
	$arrPalabras=explode(" ",$frase);
	$nElementos=sizeof($arrPalabras);
	$abreviatura="";
	for($x=0;$x<$nElementos;$x++)
	{
		$abreviatura.=substr(($arrPalabras[$x]),0,1);
	}
	return  (str_replace(")","",str_replace("(","",$abreviatura)));
}

function obtenerNodosHijos($idFormulario,$idReferencia,$idPadre,$programaP=true)
{
	global $con;
	$consulta="SELECT idActividadPrograma,fechaInicio,fechaFin,actividad,idPadre,idUsuario,horasTotal FROM 965_actividadesUsuario WHERE idFormulario=".$idFormulario." AND idReferencia=".$idReferencia." AND idPadre=".$idPadre." order by fechaInicio";
	$res=$con->obtenerFilas($consulta);
	$color="0000FF";
	$arrTareas="";
	$hijos="";
	while($fila=mysql_fetch_row($res))
	{
		$padre=$fila[4];
		if($padre=="-1")
			$padre=0;
		$titulo=$fila[3];
		if(strlen($titulo)>65)
			$titulo=substr($titulo,0,65)."...";
		$hijos=obtenerNodosHijosActividades($fila[0],$programaP,"-10",1);
		if($hijos=="")
			$pGroup=0;
		else
			$pGroup=1;
		$pCaption="";
		if($fila[5]!="-1")
		{
			$nUsuario=obtenerNombreUsuario($fila[5]);
			$pCaption=strtoupper(obtenerInicialesFrase($nUsuario))."|".$nUsuario;
		}
		else
		{
			$pCaption="<![CDATA[<table><tr><td><font color=\"red\"><b>Sin responsable</b></font></td><td>&nbsp;<a href=\"javascript:asignaResponsable('".base64_encode($fila[0])."')\"><img src=\"../images/add.png\" title=\"Asignar responsable\" alt=\"Asignar responsable\"></a></td></tr></table>]]>";
		}
		$pagina="../modeloProyectos/fichaActividadProceso.php|".base64_encode($fila[0]);
		$cadTarea="<task>
						<pID>".$fila[0]."</pID>
						<pName>".$titulo."</pName>
						<pStart>".date('d/m/Y',strtotime($fila[1]))."</pStart>
						<pEnd>".date('d/m/Y',strtotime($fila[2]))."</pEnd>
						<pColor>".$color."</pColor>
						<pLink>".$pagina."</pLink>
						<pMile>0</pMile>
						<pRes></pRes>
						<pComp></pComp>
						<pGroup>".$pGroup."</pGroup>
						<pParent>".$padre."</pParent>
						<pOpen>1</pOpen>
						<pDepend></pDepend>
						<pCaption>".$pCaption."</pCaption>
						<pHoras>".$fila[6]."</pHoras>
					</task>";
		$arrTareas.=$cadTarea.$hijos;
	}
	return $arrTareas;
}
	
function obtenerNodosHijosActividades($idPadre,$programaP=true,$idUsuario="-1",$sl=false,$tActividad="",$lineasInv="",&$arrActividades)
{
	global $con;
	$queryAct="";
	if($tActividad!="")
	{
		$datosActividad=generarConsultaTipoActividad($tActividad);
		$queryAct=$datosActividad[0];
	}
	
	$queryLineasInv="";
	if($lineasInv!="")
	{
		$queryLineasInv=" and idActividadPrograma in (select idActividad from 969_actividadesLineasAccion where idLineaInvestigacion in(".$lineasInv."))";
	}
	
	$consulta="select idActividadPrograma as idActividad,a.actividad,fechaInicio,fechaFin,idPadre,color,'' as tipo,idUsuario,a.tipoActividadProgramada,a.horasTotal from 965_actividadesUsuario a, 
				967_prioridadActividad p where p.idPrioridad=a.prioridad and idPadre in(".$idPadre.") ".$queryLineasInv." ".$queryAct." order by fechaInicio";
	$res=$con->obtenerFilas($consulta);
	$arrTareas="";
	$hijos="";
	while($fila=mysql_fetch_row($res))
	{
		$titulo=$fila[1];
		if(strlen($titulo)>65)
			$titulo=substr($titulo,0,65)."...";
		
		$usuario="";
		if($arrActividades!=null)
		{
			if(existeValor($arrActividades,$fila[0]))
				continue;
		}
		$consulta=obtenerNombreUsuario($fila[7]);
		$usuario=$con->obtenerValor($consulta);
		$pCaption=obtenerInicialesFrase($usuario)."|".$usuario;
		$pId=$fila[0];
		
		$hijos=obtenerNodosHijosActividades($fila[0],$programaP,$idUsuario,$sl,$tActividad,$lineasInv,$arrActividades);
		if($hijos=="")
			$pGroup=0;
		else
			$pGroup=1;
			
		switch($fila[8])
		{
			case 1:
			case 2: //actividad Libre/ Aosc. Proceso
				if(($programaP==false)||($sl==1)||($idUsuario!=$fila[7]))
				{
					$pagina="../modeloProyectos/fichaActividadProceso.php|".base64_encode($fila[0]);
					$pAgregar="0";
					$pEliminar="0";
				}
				else
				{
					$pagina="../modeloProyectos/fichaActividad.php?p=".base64_encode($fila[0]);
					$pAgregar="1";
					if(($idUsuario==$fila[7])&&($pGroup==0))
						$pEliminar="1";
					else
						$pEliminar="0";
					
				}
			break;
			case 3: //cclase
			break;
			case 4: //Sesion Clase
			break;
			case 5: //Sesion Extra
			break;
		}
			
		$porcentaje=obtenerAvanceActividadUsuario($fila[0]);
		$color=$fila[5];
		$cadTarea="<task>
						<pID>".$pId."</pID>
						<pName>".($titulo)."</pName>
						<pStart>".date('d/m/Y',strtotime($fila[2]))."</pStart>
						<pEnd>".date('d/m/Y',strtotime($fila[3]))."</pEnd>
						<pColor>".$color."</pColor>
						<pLink>".$pagina."</pLink>
						<pMile>0</pMile>
						<pRes></pRes>
						<pComp>".$porcentaje."</pComp>
						<pGroup>".$pGroup."</pGroup>
						<pParent>".$idPadre."</pParent>
						<pOpen>1</pOpen>
						<pDepend></pDepend>
						<pCaption>".$pCaption."</pCaption>
						<pAgregar>".$pAgregar."</pAgregar>
						<pEliminar>".$pEliminar."</pEliminar>
						<pHoras>".$fila[9]."</pHoras>
					</task>";
		$arrTareas.=$cadTarea.$hijos;
		if($arrActividades!==null)
			array_push($arrActividades,$fila[0]);
	}
	return $arrTareas;
}

function generarTableroControlProceso($tipoProceso,$titulo,$sl=false,$idProceso="",$idUsr="",$verCV=false,$relacion="1",$actor="",$idProcesoP="",$idReferencia="-1",$tVista="")  //OK
{
	global $con;
	global $nomPagina;
	$idUsuario="";
	$roles="";
	
	if($idUsr=="")
	{
		$idUsuario=$_SESSION["idUsr"];
		$roles=$_SESSION["idRol"];
	}
	else
	{
		$idUsuario=$idUsr;
		$consulta="select codigoRol from 807_usuariosVSRoles where idUsuario=".$idUsuario;
		$roles=$con->obtenerListaValores($consulta,"'");
	}
 	if($actor!="")
		$roles=$actor;
	?>
 
 

    <br /><br />
    <input type="hidden" id="idProcesoP" value="<?php echo $idProcesoP?>" />
    <input type="hidden" id="idReferencia" value="<?php echo $idReferencia?>" />
    <input type="hidden" id="idUsuario" value="<?php echo $idUsuario?>" />
	<table width="850" class="" cellpadding="4">

	<tr height="23">
		<td align="left" class='celdaImagenAzul1'>
		<span class="speaker"><?php echo $titulo ?></span>
		</td>
	</tr>
	<tr>
		<td>
		</td>
	</tr>
	<?php
		if($relacion=="1")
		{
			if($idProceso=="")
				$consulta="select distinct(ap.idProceso),nombre,p.idTipoProceso,repetible,p.complementario from 950_actorVSProcesoInicio ap,4001_procesos p where p.idProceso=ap.idProceso and p.idTipoProceso=".$tipoProceso." and ap.actor in(".$roles.")";
			else
				$consulta="select distinct(ap.idProceso),nombre,p.idTipoProceso,repetible,p.complementario from 950_actorVSProcesoInicio ap,4001_procesos p where p.idProceso=ap.idProceso and p.idTipoProceso=".$tipoProceso." and ap.idProceso=".$idProceso." and ap.actor in(".$roles.")";
		}
		else
			$consulta="select distinct(ap.idProceso),nombre,p.idTipoProceso,repetible,p.complementario from 950_actorVSProcesoInicio ap,4001_procesos p where p.idProceso=ap.idProceso and p.idTipoProceso=".$tipoProceso." and ap.idProceso=".$idProceso;
		
		$arrPagNuevo="var arrPagNuevo=new Array();var arrAux;";
		$resP=$con->obtenerFilas($consulta);	
		if($con->filasAfectadas>0)
		{
			while($filaP=mysql_fetch_row($resP))
			{
				$idProceso=$filaP[0];
				$tipoProceso=$filaP[2];
				$repetible=$filaP[3];
				$consulta="select nombreTabla,idFormulario from 900_formularios where idProceso=".$idProceso." and formularioBase=1";
				$filaFrm=$con->obtenerPrimeraFila($consulta);
				$nTabla=$filaFrm[0];
				if($nTabla=="")
				{
					echo "<tr><td align='center'><br><span class='copyrigthSinPadding'>Este proceso no tiene configurado un formulario base</span><br><br></td></tr>";
					return;	
				}
				$idFormulario=$filaFrm[1];
				$pagNuevo="../modeloPerfiles/registroFormulario.php";
				$query="select pagVista,accion from 936_vistasProcesos where tipoProceso=".$tipoProceso." and accion=0";
				$resPagAccion=$con->obtenerFilas($query);
				while($filaAccion=mysql_fetch_row($resPagAccion))
				{
					switch($filaAccion[1])
					{
						case "0"://Nuevo
							$pagNuevo=$filaAccion[0];
						break;
					}
				}
				$arrPagNuevo.="arrAux=new Array();arrAux[0]='".$idFormulario."';arrAux[1]='".$pagNuevo."';arrPagNuevo.push(arrAux);";
				$consulta="select complementario from 949_actoresVSAccionesProceso where actor in(".$roles.") and idAccion=9 and idProceso=".$idProceso." order by complementario";
				$complementario=$con->obtenerValor($consulta);
				$consulta="select actor from 949_actoresVSAccionesProceso where actor in(".$roles.") and idAccion=8 and idProceso=".$idProceso." order by complementario";
				$pAgregar=$con->obtenerValor($consulta);
				$condWhere=" where responsable=".$idUsuario;
				$reemplazarConsulta=false;
				if(($idProcesoP=="")||($tVista=="2"))
				{
					if((!$verCV)&&($relacion=="1"))
					{
						switch($complementario)
						{
							case "1":
								$condWhere="";
							break;
							case "2":
								$condWhere=" where codigoInstitucion='".$_SESSION["codigoInstitucion"]."'";
							break;
							case "3":
								$condWhere=" where codigoUnidad like '".$_SESSION["codigoUnidad"]."%'";
							break;
							case "4":
								$condWhere=" where codigoUnidad='".$_SESSION["codigoUnidad"]."'";
							break;
							case "5":
								$idFrmAutores=incluyeModulo($idProceso,3);
								if($idFrmAutores=="-1")
									$condWhere=" where responsable=".$idUsuario;
								else
								{
									$idFormularioBase=obtenerFormularioBase($idProceso);	
									$condWhere="select id_".$nTabla.",idEstado from ".$nTabla." t,246_autoresVSProyecto a   
												WHERE t.id_".$nTabla."=a.idReferencia AND a.idUsuario=".$idUsuario." AND a.idFormulario=".$idFormularioBase.
												" union SELECT id_".$nTabla.",idEstado FROM ".$nTabla." t,9202_accionesSolicitudUsuario a 
												WHERE t.id_".$nTabla."=a.idRegistro AND a.idUsuario=".$idUsuario." AND a.idFormulario=".$idFormularioBase;

									$reemplazarConsulta=true;
									//$condWhere=" where id_".$nTabla." in (select distinct idReferencia from 246_autoresVSProyecto where idUsuario=".$idUsuario." and idFormulario=".$idFormularioBase."
										//		union SELECT idRegistro FROM 9202_accionesSolicitudUsuario WHERE idFormulario=".$idFormularioBase." and idUsuario=".$idUsuario." AND estado=1 AND accion=5)";
								}
								
							break;
						}
					}
					else
					{
						$idFrmAutores=incluyeModulo($idProceso,3);
						if($idFrmAutores=="-1")
							$condWhere=" where responsable=".$idUsuario;
						else
						{
							$idFormularioBase=obtenerFormularioBase($idProceso);	
							$condWhere="select id_".$nTabla.",idEstado from ".$nTabla." t,246_autoresVSProyecto a   
												WHERE t.id_".$nTabla."=a.idReferencia AND a.idUsuario=".$idUsuario." AND a.idFormulario=".$idFormularioBase.
												" union SELECT id_".$nTabla.",idEstado FROM ".$nTabla." t,9202_accionesSolicitudUsuario a 
												WHERE t.id_".$nTabla."=a.idRegistro AND a.idUsuario=".$idUsuario." AND a.idFormulario=".$idFormularioBase;
							$reemplazarConsulta=true;
							//$condWhere=" where id_".$nTabla." in (select distinct idReferencia from 246_autoresVSProyecto where idUsuario=".$idUsuario." and idFormulario=".$idFormularioBase."
								//		union SELECT idRegistro FROM 9202_accionesSolicitudUsuario WHERE idFormulario=".$idFormularioBase." and idUsuario=".$idUsuario." AND estado=1 AND accion=5)";
						}
					}
				}
				else
				{
					$condWhere=" where idProcesoPadre=".$idProcesoP." and idReferencia=".$idReferencia;
				}
				if(!$reemplazarConsulta)
					$consulta="select id_".$nTabla.",idEstado from ".$nTabla." ".$condWhere;
				else
					$consulta=$condWhere;
				
				$resReg=$con->obtenerFilas($consulta);
				$arrEtapas=array();
				while($filaReg=mysql_fetch_row($resReg))
				{
					if(isset($arrEtapas[$filaReg[1]]))
						$arrEtapas[$filaReg[1]]++;
					else
						$arrEtapas[$filaReg[1]]=1;
				}
				
				$totalRegistros=$con->filasAfectadas;
				if($relacion=="1")
				{
					$consulta="select idAccion from 949_actoresVSAccionesProceso where idProceso=".$idProceso." and idAccion in(8,9) and actor in(".$roles.")";
					$resPermisos=$con->obtenerFilas($consulta);
					$agregarRegistros=false;
					$verRegistros=false;
					$idActorAgregar="";
					while($fPermisos=mysql_fetch_row($resPermisos))
					{
						if($fPermisos[0]=="8")//Agregar
						{
							$agregarRegistros=true;
							$consulta="select idActorVSAccionesProceso from 949_actoresVSAccionesProceso where idProceso=".$idProceso." and idAccion=8 and actor in(".$roles.")";
							$idActorAgregar=$con->obtenerValor($consulta);
						}
						if($fPermisos[0]=="9")
							$verRegistros=true;
					}
					if($sl)
					{
						$agregarRegistros=false;
						$verRegistros=true;
					}
				}
				else
				{
					$agregarRegistros=false;
					$verRegistros=true;
				}
				
		?>
                <tr>
                <td>
					<table >
						<tr height="23">
						<td width="20" class="celdaImagenAzul2" align="right">
						<a href="javascript:mostrarOcultarEtapas(<?php echo $filaP[0]?>)">
						<img src="../images/verMenos.png" alt="Ocultar registros por etapas" title="Ocultar registros por etapas" id='imgEtapas_<?php echo $filaP[0] ?>'/>
						</a>
						</td>
						<td class="celdaImagenAzul2" width="760" align="left" colspan="2">
							&nbsp;&nbsp;<span class="letraRojaSubrayada8">Proceso:</span>&nbsp;&nbsp;<span class="corpo8_bold"><?php echo $filaP[1]?></span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="letraAzulSubrayada7"></span>
							<?php
							
								if(($repetible==0)&&($totalRegistros>0))
									$agregarRegistros=false;
								/*$mostrarConvocatoria=false;	
								if($idProceso==34)
								{
									$consulta="SELECT COUNT(responsable) FROM _293_tablaDinamica WHERE idEstado=2";
									$nRegistros=$con->obtenerValor($consulta);
									
									if(($nRegistros>=300)||(strtotime(date("Y-m-d H:i"))>=strtotime("2011-03-09 18:00")))
									{
										$agregarRegistros=false;
										$mostrarConvocatoria=true;	
									}	
								}*/
								if($agregarRegistros)
								{
									if(($filaP[4]=="")||(strpos($filaP[4],"{")===false))
									{
							?>
									<span class="copyrigthSinPadding">Si desea registrar una nueva ficha de <?php echo $filaP[1] ?> de click </span><a href="javascript:registrarNuevo('<?php echo bE($idFormulario)?>','<?php echo base64_encode($idActorAgregar)?>')"><span class="letraRoja">AQUÍ</span></a>
							<?php
									}
									else
									{
										$obj=json_decode($filaP[4]);
										if(isset($obj->funcionNuevoRegistro))
										{
							?>
                            			<span class="copyrigthSinPadding">Si desea registrar una nueva ficha de <?php echo $filaP[1] ?> de click </span><a href="javascript:registrarNuevoFuncion('<?php echo bE($idFormulario)?>','<?php echo base64_encode($idActorAgregar)?>','<?php echo base64_encode($idReferencia)?>')"><span class="letraRoja">AQUÍ</span></a>
							<?php
										}
										else
										{
								?>
											<span class="copyrigthSinPadding">Si desea registrar una nueva ficha de <?php echo $filaP[1] ?> de click </span><a href="javascript:registrarNuevo('<?php echo bE($idFormulario)?>','<?php echo base64_encode($idActorAgregar)?>')"><span class="letraRoja">AQUÍ</span></a>
							<?php
										}
                            		}
								}
								/*if($mostrarConvocatoria)
								{*/
							?>
									<!--<span class="copyrigthSinPadding">La convocatoria ha sido <font color="#FF0000">cerrada</font>, gracias por su participación</span>-->
							<?php
								//}
								
							?>
						</td>
						
						</tr>
						<tr>
						<td style="padding:7px" colspan="3">
							<table id="tbl_<?php echo $idProceso?>" style="display:">
							<?php
							$consulta="select * from 4037_categoriasEtapa where idProceso=".$idProceso." order by numCategoria";
							$resCat=$con->obtenerFilas($consulta);
							$totalElementos=0;
							
							while($filaCat=mysql_fetch_row($resCat))
							{
								$totalCategoria=0;
								$consulta="select numEtapa from  4037_etapas where situacion=1 and idProceso=".$idProceso." and idCategoria=".$filaCat[0];
								$resEt=$con->obtenerFilas($consulta);
								$nEtapas=$con->filasAfectadas;
								if($nEtapas>0)
								{
									while($filasEt=mysql_fetch_row($resEt))
									{
										if(isset($arrEtapas[$filasEt[0]]))
										{
											
											$totalCategoria+=$arrEtapas[$filasEt[0]];
										}
									}
									
									
									
								?>
									
									<tr height="21">
										<td colspan="3" align="left">
											<table width="80%">
												<tr height="21">
													<td >
														<table width="100%">
														<tr>
															<td width="70%" align="left">
																<span class="copyrigthSinPadding"><b>
																<?php echo $filaCat[1].".-".$filaCat[2] ?></b>
																</span>
															   
															</td>
															<td width="30%" align="left">
																<?php
																	$tagA="";
																	$tagC="";
																	if($totalCategoria>0)
																	{
																		$tagA="<a href=\"javascript:enviarVista('".base64_encode("idProceso=".$idProceso."&idCategoria=".$filaCat[0]."&sl=".$sl."&idUsuario=".$idUsuario."&verCV=".$verCV."&relacion=".$relacion."&actor=".$actor."&idProcesoP=".$idProcesoP."&idReferencia=".$idReferencia."&tVista=".$tVista)."')\">";
																		$tagC="</a>";
																	}
																	echo $tagA;
																?>
																<span  class="letraRoja">
																
																 <?php
																	echo $totalCategoria;
																?>
																</span>
																<span class="corpo8">Registros</span>
																<?php
																	echo $tagC;
																?>
															</td>
														</tr>
														</table>
														
													</td>
												</tr>
												<tr height="2">
													<td style="background-color:#006" >
													</td>
												</tr>
												<tr height="4">
													<td style="background-color:#FFF" >
														
													</td>
												</tr>
											</table>
										</td>
									</tr>
								   
								<?php	
								
									$consulta="select numEtapa,nombreEtapa from 4037_etapas where situacion=1 and idProceso=".$idProceso." and idCategoria=".$filaCat[0]." order by numEtapa";	
									$resEt=$con->obtenerFilas($consulta);
									$clase="celdaImagenAzul3";
									
									while($filasEt=mysql_fetch_row($resEt))
									{
										$tagA="";
										$tagC="";
										if(isset($arrEtapas[$filasEt[0]])&&$verRegistros)
										{
											$tagA="<a href=\"javascript:enviarVista('".base64_encode("idProceso=".$idProceso."&numEtapa=".$filasEt[0]."&sl=".$sl."&idUsuario=".$idUsuario."&verCV=".$verCV."&relacion=".$relacion."&actor=".$actor."&idProcesoP=".$idProcesoP."&idReferencia=".$idReferencia."&tVista=".$tVista)."')\">";
											$tagC="</a>";
										}
									?>
										<tr height="21">
											<td class="<?php echo $clase?>" width="50" align="right"><img src="../images/bullet_red.png" /></td>
											<td class="<?php echo $clase?>" width="570" align="left">&nbsp;&nbsp;<?php echo $tagA?><span class="tituloEtiqueta"><?php echo removerCerosDerecha($filasEt[0])?>.- <?php echo $filasEt[1]?></span><?php echo $tagC?></td>
											<td class="<?php echo $clase?>" width="80" align="center"><?php echo $tagA?><span class="corpo8_bold">
											<?php 
												
												if(isset($arrEtapas[$filasEt[0]]))
												{
													echo $arrEtapas[$filasEt[0]];
													$totalElementos+=$arrEtapas[$filasEt[0]];
												}
												else
													echo "0";
												
											?>
										   </span><?php echo $tagC?>
										   
										   </td>
										</tr>
									<?php
									
										if($clase=="celdaImagenAzul3")
											$clase="celdaBlancaSinImg";
										else
											$clase="celdaImagenAzul3";
									}
								?>
									 <tr height="21">
										<td colspan="3"></td>
									</tr>
								<?php
								}
							}
                            
                            $totalCategoria=0;
							$consulta="select numEtapa from  4037_etapas where situacion=1 and idProceso=".$idProceso." and idCategoria is null";
							$resEt=$con->obtenerFilas($consulta);
							if($con->filasAfectadas>0)
							{
								
								while($filasEt=mysql_fetch_row($resEt))
								{
									
									if(isset($arrEtapas[$filasEt[0]]))
									{
										
										$totalCategoria+=$arrEtapas[$filasEt[0]];
									}
								}
								?>
                                <tr height="21">
                                <td colspan="3" align="left" >
                                    <table width="80%">
                                    <tr>
                                        <td width="70%">
                                        </td>
                                        <td width="30%" align="left">
                                            <?php
												  $tagA="";
												  $tagC="";
												  if($totalCategoria>0)
												  {
													  $tagA="<a href=\"javascript:enviarVista('".base64_encode("idProceso=".$idProceso."&idCategoria=NULL&sl=".$sl."&idUsuario=".$idUsuario."&verCV=".$verCV."&relacion=".$relacion."&actor=".$actor."&idProcesoP=".$idProcesoP."&idReferencia=".$idReferencia."&tVista=".$tVista)."')\">";
													  $tagC="</a>";
												  }
												  echo $tagA;
											  ?>
											  <span  class="letraRoja">
											  
											   <?php
												  echo $totalCategoria;
											  ?>
											  </span>
											  <span class="corpo8">Registros</span>
											  <?php
												  echo $tagC;
											  ?>
                                        </td>
                                    </tr>
                                    <tr height="2">
                                        <td style="background-color:#006" colspan="2" >
                                        </td>
                                    </tr>
                                    <tr height="4">
                                        <td style="background-color:#FFF" colspan="2" >
                                            
                                        </td>
                                    </tr>
                                    </table>
                                    
                                </td>
                            </tr>
                            
                                <?php
							}
                           
							$consulta="select numEtapa,nombreEtapa from 4037_etapas where situacion=1 and idProceso=".$idProceso." and idCategoria is null order by numEtapa";	
							$resEt=$con->obtenerFilas($consulta);
							$clase="celdaImagenAzul3";
							
							while($filasEt=mysql_fetch_row($resEt))
							{
								$tagA="";
								$tagC="";
								if(isset($arrEtapas[$filasEt[0]])&&$verRegistros)
								{
									$tagA="<a href=\"javascript:enviarVista('".base64_encode("idProceso=".$idProceso."&numEtapa=".$filasEt[0]."&sl=".$sl."&idUsuario=".$idUsuario."&verCV=".$verCV."&relacion=".$relacion."&actor=".$actor."&idProcesoP=".$idProcesoP."&idReferencia=".$idReferencia."&tVista=".$tVista)."')\">";
									$tagC="</a>";
								}
									
							?>
								<tr height="21">
									<td class="<?php echo $clase?>" width="50" align="right"><img src="../images/bullet_red.png" /></td>
									<td class="<?php echo $clase?>" width="570" align="left">&nbsp;&nbsp;<?php echo $tagA?><span class="tituloEtiqueta"><?php echo removerCerosDerecha($filasEt[0])?>.- <?php echo $filasEt[1]?></span><?php echo $tagC?></td>
									<td class="<?php echo $clase?>" width="80" align="center"><?php echo $tagA?><span class="corpo8_bold">
									<?php 
										if(isset($arrEtapas[$filasEt[0]]))
										{
											echo $arrEtapas[$filasEt[0]];
											$totalElementos+=$arrEtapas[$filasEt[0]];
										}
										else
											echo "0";
										
									?>
								   </span><?php echo $tagC?>
                                   
                                   </td>
								</tr>
							<?php
							
								if($clase=="celdaImagenAzul3")
									$clase="celdaBlancaSinImg";
								else
									$clase="celdaImagenAzul3";
							}
							$tagA="";
							$tagC="";
							if(($totalElementos>0)&&$verRegistros)
							{
								$tagA="<a href=\"javascript:enviarVista('".base64_encode("idProceso=".$idProceso."&numEtapa=NULL&sl=".$sl."&idUsuario=".$idUsuario."&verCV=".$verCV."&relacion=".$relacion."&actor=".$actor."&idProcesoP=".$idProcesoP."&idReferencia=".$idReferencia."&tVista=".$tVista)."')\">";
								$tagC="</a>";
							}
							?>
                            <tr height="4">
                                <td colspan="4" style="background-color:#900">
                                </td>
                            </tr>
                            <tr height="21">
                                <td class="fondoGrid7" width="50" align="right"></td>
                                <td class="fondoGrid7" width="570" align="right">&nbsp;&nbsp;<?php echo $tagA?><span class="corpo8_bold"><font color="#FF0000">Total General: </font></span><?php echo $tagC?></td>
                                <td class="fondoGrid7" width="80" align="center">
                                <?php
									
									echo $tagA;
								?>
								
                                <span class="corpo8_bold">
								<?php 
										
                                        echo $totalElementos;
                                ?>
                               </span>
								<?php
                                	echo $tagC;
                                ?>
                               
                               </td>
							</tr>
							</table>
				
				</td>
				</tr>
			</table>
		</td>
		</tr>
		<tr>
			<td><br /></td>
		</tr>
		<?php
			}
		}
		else
		{
	?>
    		<tr>
            <td>
                <table width="100%">
                <tr>
                    <td align="center">
                        <span class="letraFicha">Usted no cuenta con privilegios para ver algún registro</span><br /><br /><br /><br />
                    </td>
                </tr>
                </table>
            </td>
            </tr>
    <?php
		}
	?>
	
</table>
<input type="hidden" name="idFormulario" id="idFormulario" value="<?php echo $idFormulario?>" />
<script>
	<?php 
		echo $arrPagNuevo;
	?>
</script>
<?php
}

function generarTableroControlObjetoComite($tipoProceso,$titulo,$idProceso=null,$idComiteParam=null)									//OK
{
	global $con;
	global $nomPagina;
	$roles=$_SESSION["idRol"];
	$idUsuario=$_SESSION["idUsr"];
?>
	<table width="750" class="" cellpadding="4">
    <tr>
        <td>
        <table width="100%">
    <?php 
		$conComp="1=1";
        if($idComiteParam!=null)
			$conComp=" idComite=".$idComiteParam;
		if($idProceso==null)
		{
			$consulta="select distinct(idComite),idComite from 2007_rolesVSComites rc where ".$conComp." and rc.rol in(".$roles.")";
			$arrComites=$con->obtenerFilasArregloPHP($consulta);
		}
		else
		{
			$consulta="select distinct(idComite),idComite from 2007_rolesVSComites rc where ".$conComp." and rc.rol in(".$roles.")";
			$listC=$con->obtenerListaValores($consulta);
			if($listC=="")
				$listC="-1";
			$consulta="select idComite,idComite from 235_proyectosVSComites where idProyecto=".$idProceso." and idComite in(".$listC.")";
			$arrComites=$con->obtenerFilasArregloPHP($consulta);
			
		}
        
        foreach($arrComites as $filaC)
        {
                $idComite=$filaC[0];
                $consulta="select nombreComite from 2006_comites where idComite=".$idComite;
                $nComite=$con->obtenerValor($consulta);
				if($nComite!='')
				{
    ?>
                    <tr height="23">
                        <td align="LEFT" class="celdaImagenAzul1">
                        <span class="speaker"><?php echo $titulo; ?> EN EVALUACI&Oacute;N POR COMITÉ: </span><span class="letraRojaSubrayada8"><?php echo strtoupper($nComite)?></span>
                        </td>
                    </tr>
                   
                    <?php
						$condWhere="";
						if($idProceso!=null)
							$condWhere=" and p.idProceso=".$idProceso;
						
                        $consulta="select distinct(ap.idProyecto),nombre,p.idTipoProceso from 235_proyectosVSComites ap,
									4001_procesos p where p.idProceso=ap.idProyecto and p.idTipoProceso=".$tipoProceso." 
									and ap.idComite=".$idComite.$condWhere;
						
                        $resP=$con->obtenerFilas($consulta);									
                        while($filaP=mysql_fetch_row($resP))
                        {
                            $idProceso=$filaP[0];
                            $tipoProceso=$filaP[2];
                            $consulta="select nombreTabla,idFormulario from 900_formularios where idProceso=".$idProceso." and formularioBase=1";
                            $filaFrm=$con->obtenerPrimeraFila($consulta);
                            $nTabla=$filaFrm[0];
                            $idFormulario=$filaFrm[1];
                            //$condWhere="where idEstado in (select numEtapa from 234_proyectosVSComitesVSEtapas where idComite=".$idComite." and idProyecto=".$idProceso.")";
                            $consulta="SELECT idActorProcesoEtapa FROM 234_proyectosVSComitesVSEtapas pc,944_actoresProcesoEtapa ae WHERE 
										actor=idProyectoVSComiteVSEtapa AND tipoActor=2 AND ae.asocAutomatico=0 and  pc.idProyecto=".$idProceso." AND pc.idComite=".$idComite;
							$listActores=$con->obtenerListaValores($consulta);
							if($listActores=="")
								$listActores="-1";
							$consulta="SELECT * FROM 998_actoresEvaluadoresAsignados WHERE idActorProcesoEtapa IN(".$listActores.")";
							$resRegistros=$con->obtenerFilas($consulta);
			
							$listadoRegistro="-1";
							while($filaActor=mysql_fetch_row($resRegistros))
							{
								$consulta="SELECT numEtapa FROM 944_actoresProcesoEtapa WHERE idActorProcesoEtapa=".$filaActor[1];
								$nEtapaAsig=$con->obtenerValor($consulta);
								$consulta="select idEstado from _".$filaActor[2]."_tablaDinamica where id__".$filaActor[2]."_tablaDinamica=".$filaActor[3];
								$idEstadoReg=$con->obtenerValor($consulta);
								if($nEtapaAsig==$idEstadoReg)
								{
									if($listadoRegistro=="")
										$listadoRegistro=$filaActor[3];	
									else
										$listadoRegistro.=",".$filaActor[3];	
								}
							}
							
							$consulta="SELECT ae.numEtapa FROM 234_proyectosVSComitesVSEtapas pc,944_actoresProcesoEtapa ae WHERE 
										actor=idProyectoVSComiteVSEtapa AND tipoActor=2 AND ae.asocAutomatico=1 and  pc.idProyecto=".$idProceso." AND pc.idComite=".$idComite;
							$nListaEtapa=$con->obtenerListaValores($consulta);
							if($nListaEtapa=="")
								$nListaEtapa="-1";
							
							$consulta="select id_".$nTabla." from ".$nTabla." where idEstado in(".$nListaEtapa.") and codigoInstitucion='".$_SESSION["codigoInstitucion"]."'";
							$listAux=$con->obtenerListaValores($consulta);
							if($listAux!="")
								$listadoRegistro.=",".$listAux;
								
							$condWhere=" where id_".$nTabla." in (".$listadoRegistro.") and codigoInstitucion='".$_SESSION["codigoInstitucion"]."'";
							
							$consulta="select distinct(id_".$nTabla."),idEstado from ".$nTabla." ".$condWhere;
                            $resReg=$con->obtenerFilas($consulta);
                            $arrEtapas=array();
                            while($filaReg=mysql_fetch_row($resReg))
                            {
                                if(isset($arrEtapas["".$filaReg[1].""]))
								{
                                    $arrEtapas["".$filaReg[1].""]["nReg"]++;
									$arrEtapas["".$filaReg[1].""]["listadoRegistro"].=",".$filaReg[0];
									
								}
                                else
								{
                                    $arrEtapas["".$filaReg[1].""]["nReg"]=1;
									$arrEtapas["".$filaReg[1].""]["listadoRegistro"]=$filaReg[0];
								}
                            }
                    ?>
                    <tr>
                    <td>
                        <table >
                            <tr height="23">
                            <td width="20" class="celdaImagenAzul2" align="right">
                            <a href="javascript:mostrarOcultarEtapas(<?php echo $filaP[0]?>,<?php echo $idComite ?>)">
                            <img src="../images/verMenos.png" alt="Ocultar registros por etapas" title="Ocultar registros por etapas" id='imgEtapas_<?php echo $filaP[0]?>_<?php echo $idComite?>'/>
                            </a>
                            
                            </td>
                            <td  class="celdaImagenAzul2" width="730" align="left" colspan="2">
                                &nbsp;&nbsp;<span class="letraRojaSubrayada8">Proceso:</span>&nbsp;&nbsp;<span class="corpo8_bold"><?php echo $filaP[1]?></span>&nbsp;<span class="letraAzulSubrayada7"></span>
                            </td>
                            </tr>
                            <tr>
                            <td style="padding:7px" colspan="3">
                                <table id="tbl_<?php echo $idProceso."_".$idComite?>" style="display:">
                                <?php
								//-----------------
								$consulta="select * from 4037_categoriasEtapa where idProceso=".$idProceso." order by numCategoria";
								$resCat=$con->obtenerFilas($consulta);
								$totalElementos=0;
								$totalRegistros="";
								while($filaCat=mysql_fetch_row($resCat))
								{
									$totalCategoria=0;
									$consulta="select numEtapa from  4037_etapas where situacion=1 and idProceso=".$idProceso." and idCategoria=".$filaCat[0];
									
									$resEt=$con->obtenerFilas($consulta);
									$listaCategoria="";
									
									$consulta="select distinct e.numEtapa,e.nombreEtapa from 4037_etapas e,234_proyectosVSComitesVSEtapas pc where e.situacion=1 and e.idProceso=pc.idProyecto and 
											e.numEtapa=pc.numEtapa and pc.idProyecto=".$idProceso." and pc.idComite=".$idComite." and e.numEtapa in 
											(SELECT numEtapa FROM 4037_etapas WHERE idCategoria=".$filaCat[0]." and idProceso=".$idProceso.") order by e.numEtapa";	
	
									$resEtEval=$con->obtenerFilas($consulta);
									
									$nEtapas=$con->filasAfectadas;
									if($nEtapas>0)
									{
										while($filasEt=mysql_fetch_row($resEt))
										{
											if(isset($arrEtapas[$filasEt[0]]))
											{
												$totalCategoria+=$arrEtapas[$filasEt[0]]["nReg"];
												if($listaCategoria=="")
													$listaCategoria=$arrEtapas[$filasEt[0]]["listadoRegistro"];
												else	
													$listaCategoria.=",".$arrEtapas[$filasEt[0]]["listadoRegistro"];
												
												
											}
										}
										
										
									?>
										
										<tr height="21">
											<td colspan="3" align="left">
												<table width="80%">
													<tr height="21">
														<td >
															<table width="100%">
															<tr>
																<td width="70%" align="left">
																	<span class="copyrigthSinPadding"><b>
																	<?php echo $filaCat[1].".-".$filaCat[2] ?></b>
																	</span>
																   
																</td>
																<td width="30%" align="left">
																	<?php
																		$tagA="";
																		$tagC="";
																		if($totalCategoria>0)
																		{
																			$tagA="<a href=\"javascript:enviarVista('".base64_encode("idProceso=".$idProceso."&idCategoria=".$filaCat[0]."&idComite=".$idComite."&listadoRegistros=".$listaCategoria)."')\">";
																			$tagC="</a>";
																		}
																		echo $tagA;
																	?>
																	<span  class="letraRoja">
																	
																	 <?php
																		echo $totalCategoria;
																	?>
																	</span>
																	<span class="corpo8">Registros</span>
																	<?php
																		echo $tagC;
																	?>
																</td>
															</tr>
															</table>
															
														</td>
													</tr>
													<tr height="2">
														<td style="background-color:#006" >
														</td>
													</tr>
													<tr height="4">
														<td style="background-color:#FFF" >
															
														</td>
													</tr>
												</table>
											</td>
										</tr>
									   
									<?php	
									
										
										$clase="celdaImagenAzul3";
										
										while($filasEt=mysql_fetch_row($resEtEval))
										{
											$tagA="";
											$tagC="";
											if(isset($arrEtapas[$filasEt[0]]))
											{
												if(!isset($arrEtapas[$filasEt[0]]["listadoRegistro"]))
													$arrEtapas[$filasEt[0]]["listadoRegistro"]="-1";
												$tagA="<a href=\"javascript:enviarVista('".base64_encode("idProceso=".$idProceso."&numEtapa=".$filasEt[0]."&idComite=".$idComite."&listadoRegistros=".$arrEtapas[$filasEt[0]]["listadoRegistro"])."')\">";
												$tagC="</a>";
											}
										?>
											<tr height="21">
												<td class="<?php echo $clase?>" width="50" align="right"><img src="../images/bullet_red.png" /></td>
												<td class="<?php echo $clase?>" width="570" align="left">&nbsp;&nbsp;<?php echo $tagA?><span class="tituloEtiqueta"><?php echo removerCerosDerecha($filasEt[0])?>.- <?php echo $filasEt[1]?></span><?php echo $tagC?></td>
												<td class="<?php echo $clase?>" width="80" align="center"><?php echo $tagA?><span class="corpo8_bold">
												<?php 
													
													if(isset($arrEtapas[$filasEt[0]]))
													{
														echo $arrEtapas[$filasEt[0]]["nReg"];
														$totalElementos+=$arrEtapas[$filasEt[0]]["nReg"];
														if($totalRegistros=="")
															$totalRegistros=$arrEtapas[$filasEt[0]]["listadoRegistro"];
														else
															$totalRegistros.=",".$arrEtapas[$filasEt[0]]["listadoRegistro"];
														
													}
													else
														echo "0";
													
												?>
											   </span><?php echo $tagC?>
											   
											   </td>
											</tr>
										<?php
										
											if($clase=="celdaImagenAzul3")
												$clase="celdaBlancaSinImg";
											else
												$clase="celdaImagenAzul3";
										}
									?>
										 <tr height="21">
											<td colspan="3"></td>
										</tr>
									<?php
									}
								}
								
								//----------------
								$totalCategoria=0;
								$consulta="select e.numEtapa,e.nombreEtapa from 4037_etapas e,234_proyectosVSComitesVSEtapas pc where e.situacion=1 and e.idProceso=pc.idProyecto and 
                                        e.numEtapa=pc.numEtapa and pc.idProyecto=".$idProceso." and pc.idComite=".$idComite." and e.numEtapa in 
										(SELECT numEtapa FROM 4037_etapas WHERE idCategoria is null and idProceso=".$idProceso.") order by e.numEtapa";		
								$resEt=$con->obtenerFilas($consulta);
								if($con->filasAfectadas>0)
								{
									$listaCategoria="";
									while($filasEt=mysql_fetch_row($resEt))
									{
										if(isset($arrEtapas[$filasEt[0]]))
										{
											$totalCategoria+=$arrEtapas[$filasEt[0]]["nReg"];
											if($listaCategoria=="")
												$listaCategoria=$arrEtapas[$filasEt[0]]["listadoRegistro"];
											else	
												$listaCategoria.=",".$arrEtapas[$filasEt[0]]["listadoRegistro"];
										}
									}
									?>
									<tr height="21">
									<td colspan="3" align="left" >
										<table width="80%">
										<tr>
											<td width="70%">
											   
											   
											</td>
											<td width="30%" align="left">
												<?php
													  $tagA="";
													  $tagC="";
													  if($totalCategoria>0)
													  {
														  $tagA="<a href=\"javascript:enviarVista('".base64_encode("idProceso=".$idProceso."&idCategoria=NULL&idComite=".$idComite."&listadoRegistros=".$listaCategoria)."')\">";
														  $tagC="</a>";
													  }
													  echo $tagA;
												  ?>
												  <span  class="letraRoja">
												  
												   <?php
													  echo $totalCategoria;
												  ?>
												  </span>
												  <span class="corpo8">Registros</span>
												  <?php
													  echo $tagC;
												  ?>
											</td>
										</tr>
                                        <tr height="2">
                                            <td style="background-color:#006" colspan="2" >
                                            </td>
                                        </tr>
                                        <tr height="4">
                                            <td style="background-color:#FFF" colspan="2" >
                                                
                                            </td>
                                        </tr>
										</table>
										
									</td>
								</tr>
									<?php
								}
								?>
								
									
								<?php
								$consulta="select e.numEtapa,e.nombreEtapa from 4037_etapas e,234_proyectosVSComitesVSEtapas pc where e.situacion=1 and e.idProceso=pc.idProyecto and 
                                        e.numEtapa=pc.numEtapa and pc.idProyecto=".$idProceso." and pc.idComite=".$idComite." and e.numEtapa in 
										(SELECT numEtapa FROM 4037_etapas WHERE idCategoria is null and idProceso=".$idProceso.") order by e.numEtapa";	
								$resEt=$con->obtenerFilas($consulta);
								$clase="celdaImagenAzul3";
								
								while($filasEt=mysql_fetch_row($resEt))
								{
									$tagA="";
									$tagC="";
									if(isset($arrEtapas[$filasEt[0]]))
									{
										$tagA="<a href=\"javascript:enviarVista('".base64_encode("idProceso=".$idProceso."&numEtapa=".$filasEt[0]."&idComite=".$idComite."&listadoRegistros=".$arrEtapas[$filasEt[0]]["listadoRegistro"])."')\">";
										$tagC="</a>";
									}
										
								?>
									<tr height="21">
										<td class="<?php echo $clase?>" width="50" align="right"><img src="../images/bullet_red.png" /></td>
										<td class="<?php echo $clase?>" width="570" align="left">&nbsp;&nbsp;<?php echo $tagA?><span class="tituloEtiqueta"><?php echo removerCerosDerecha($filasEt[0])?>.- <?php echo $filasEt[1]?></span><?php echo $tagC?></td>
										<td class="<?php echo $clase?>" width="80" align="center"><?php echo $tagA?><span class="corpo8_bold">
										<?php 
											if(isset($arrEtapas[$filasEt[0]]))
											{
												echo $arrEtapas[$filasEt[0]]["nReg"];
												$totalElementos+=$arrEtapas[$filasEt[0]]["nReg"];
												if($totalRegistros=="")
													$totalRegistros=$arrEtapas[$filasEt[0]]["listadoRegistro"];
												else
													$totalRegistros.=",".$arrEtapas[$filasEt[0]]["listadoRegistro"];
												
											}
											else
												echo "0";
											
										?>
									   </span><?php echo $tagC?>
									   
									   </td>
									</tr>
								<?php
								
									if($clase=="celdaImagenAzul3")
										$clase="celdaBlancaSinImg";
									else
										$clase="celdaImagenAzul3";
								}
								
								?>
                                <tr height="4">
                                    <td colspan="4" style="background-color:#900">
                                    </td>
                                </tr>
                                <tr height="21">
                               	 <?php
                                        $tagA="";
                                        $tagC="";
											
                                        if(($totalElementos>0))
                                        {
                                            $tagA="<a href=\"javascript:enviarVista('".base64_encode("idProceso=".$idProceso."&numEtapa=NULL&idComite=".$idComite."&listadoRegistros=".$totalRegistros)."')\">";
                                            $tagC="</a>";
                                        }
                                       
                                    ?>
                                    <td class="fondoGrid7" width="50" align="right"></td>
                                    <td class="fondoGrid7" width="570" align="right">&nbsp;&nbsp;<?php echo $tagA?><span class="corpo8_bold"><font color="#FF0000">Total General: </font></span><?php echo $tagC?></td>
                                    <td class="fondoGrid7" width="80" align="center">
                                    
                                    <?php
									 echo $tagA;
									?>
                                    <span class="corpo8_bold">
                                    <?php 
                                            
                                            echo $totalElementos;
                                    ?>
                                   </span>
                                    <?php
                                        echo $tagC;
                                    ?>
                                   
                                   </td>
                                </tr>
                                </table>
                            </td>
                            </tr>
                        </table>
                    </td>
                    </tr>
                    <tr>
                        <td><br /></td>
                    </tr>
                    <?php
                        }
				}
        }
    ?>
    
        </table>
        </td>
    
    </tr>
    
  </table>
<?php
}

function generarTableroControlObjetoUsuario($tipoProceso,$titulo,$idProceso=null,$actor="")							//OK
{
	global $con;
	if($actor=="")
		$roles=$_SESSION["idRol"];
	else
		$roles="'".($actor)."'";
	$idUsuario=$_SESSION["idUsr"];
?>
	<table width="750"  cellpadding="4">
    <tr>
        <td>
        <table>
   
                <tr height="23">
                    <td align="LEFT" class="celdaImagenAzul1">
                    <span class="speaker"><?php echo $titulo?> </span>
                    </td>
                </tr>
                
                <?php
					$condWhere="";
                    if($idProceso!=null)
						$condWhere=" and idProceso=".$idProceso;
						

					$consulta="select distinct(p.idProceso),nombre,p.idTipoProceso from 4001_procesos p where  p.idTipoProceso=".$tipoProceso.$condWhere;

                    $resP=$con->obtenerFilas($consulta);									
                    while($filaP=mysql_fetch_row($resP))
                    {
                        $idProceso=$filaP[0];
                        $tipoProceso=$filaP[2];
                        $consulta="select nombreTabla,idFormulario from 900_formularios where idProceso=".$idProceso." and formularioBase=1";
                        $filaFrm=$con->obtenerPrimeraFila($consulta);
                        $nTabla=$filaFrm[0];
                        $idFormulario=$filaFrm[1];
                        $condWhere="where idEstado in (select distinct(numEtapa) from 944_actoresProcesoEtapa where actor in(".$roles.") and idProceso=".$idProceso.")";
                        
						$consulta="SELECT * FROM 949_actoresVSAccionesProceso WHERE actor IN (".$roles.") and idAccion=9 and idProceso=".$idProceso;
						$aux="";

						$fila=$con->obtenerPrimeraFila($consulta);
						if($fila)
						{
							
							switch($fila[3])
							{
								case 1:  //Todos
								break;
								case 2: // En su institucion
									$aux=" codigoInstitucion='".$_SESSION["codigoInstitucion"]."'";
								break;
								case 3: // registados en su departamento y subdepartamentos
									$aux=" (codigoUnidad = '".$_SESSION["codigoUnidad"]."' or codigoUnidad like '".$_SESSION["codigoUnidad"]."%')";
								break;
								case 4: //registados en su departamento
									$aux=" codigoUnidad='".$_SESSION["codigoUnidad"]."'";
								break;
								case 5: //Solo en lo que el participa
								break;
							}
							if($aux!="")
								$aux=" and ".$aux;
						}

						if($_SESSION["idUsr"]==2947)
						{
							$queryAux="SELECT idOrganigrama FROM 817_organigrama WHERE 
										(codigoUnidad LIKE '0002%' OR codigoUnidad LIKE '0006%' OR codigoUnidad LIKE '0008%' OR codigoUnidad LIKE '0009%' or codigoUnidad like '0010%')
										AND STATUS=1";
							$listOrganigrama =$con->obtenerListaValores($queryAux);
							if($listOrganigrama=="")
								$listOrganigrama=-1;
							if($condWhere=="")
								$condWhere=" and CentroCosto not in ('0009','0011') and Sede in (".$listOrganigrama.")";
							else
								$condWhere.=" and CentroCosto not in ('0009','0011') and Sede in (".$listOrganigrama.")";
						}
						$consulta="select id_".$nTabla.",idEstado from ".$nTabla." ".$condWhere.$aux;
                        $resReg=$con->obtenerFilas($consulta);

                        $arrEtapas=array();
                        while($filaReg=mysql_fetch_row($resReg))
                        {
                            if(isset($arrEtapas["".$filaReg[1].""]))
                                $arrEtapas["".$filaReg[1].""]++;
                            else
                                $arrEtapas["".$filaReg[1].""]=1;
                        }
                        
                        
                ?>
                <tr>
                <td>
                    <table >
                        <tr height="23">
                        <td width="20" class="celdaImagenAzul2" align="right">
                        <a href="javascript:mostrarOcultarEtapas(<?php echo $filaP[0]?>)">
                        <img src="../images/verMenos.png" alt="Ocultar registros por etapas" title="Ocultar registros por etapas" id='imgEtapas_<?php echo $filaP[0]?>'/>
                        </a>
                        
                        </td>
                        <td  class="celdaImagenAzul2" width="730" align="left" colspan="2">
                            &nbsp;&nbsp;<span class="letraRojaSubrayada8">Proceso:</span>&nbsp;&nbsp;<span class="corpo8_bold"><?php echo $filaP[1]?></span>&nbsp;<span class="letraAzulSubrayada7"></span>
                        </td>
                        </tr>
                        <tr>
                        <td style="padding:7px" colspan="3">
                            <table id="tbl_<?php echo $idProceso?>" style="display:">
                            <?php
							
							//-----------------
								$consulta="select * from 4037_categoriasEtapa where idProceso=".$idProceso." order by numCategoria";
								$resCat=$con->obtenerFilas($consulta);
								$totalElementos=0;
								
								while($filaCat=mysql_fetch_row($resCat))
								{
									$totalCategoria=0;
									$consulta="select numEtapa from  4037_etapas where situacion=1 and idProceso=".$idProceso." and idCategoria=".$filaCat[0];
									$resEt=$con->obtenerFilas($consulta);
									$consulta="select distinct e.numEtapa,e.nombreEtapa from 4037_etapas e,944_actoresProcesoEtapa ap where e.situacion=1 and e.idProceso=ap.idProceso and 
                                    e.numEtapa=ap.numEtapa and ap.idProceso=".$idProceso." and ap.actor in(".$roles.") and e.numEtapa in 
										(SELECT numEtapa FROM 4037_etapas WHERE idCategoria=".$filaCat[0].") order by e.numEtapa";	
									$resEtEval=$con->obtenerFilas($consulta);
									$nEtapas=$con->filasAfectadas;
									if($nEtapas>0)
									{
										while($filasEt=mysql_fetch_row($resEt))
										{
											if(isset($arrEtapas[$filasEt[0]]))
											{
												$totalCategoria+=$arrEtapas[$filasEt[0]];
											}
										}
										
										
									?>
										
										<tr height="21">
											<td colspan="3" align="left">
												<table width="80%">
													<tr height="21">
														<td >
															<table width="100%">
															<tr>
																<td width="70%" align="left">
																	<span class="copyrigthSinPadding"><b>
																	<?php echo $filaCat[1].".-".$filaCat[2] ?></b>
																	</span>
																   
																</td>
																<td width="30%" align="left">
																	<?php
																		$tagA="";
																		$tagC="";
																		if($totalCategoria>0)
																		{
																			$tagA="<a href=\"javascript:enviarVista('".base64_encode("idProceso=".$idProceso."&idCategoria=".$filaCat[0]."&actor=".$roles)."')\">";
																			$tagC="</a>";
																		}
																		echo $tagA;
																	?>
																	<span  class="letraRoja">
																	
																	 <?php
																		echo $totalCategoria;
																	?>
																	</span>
																	<span class="corpo8">Registros</span>
																	<?php
																		echo $tagC;
																	?>
																</td>
															</tr>
															</table>
															
														</td>
													</tr>
													<tr height="2">
														<td style="background-color:#006" >
														</td>
													</tr>
													<tr height="4">
														<td style="background-color:#FFF" >
															
														</td>
													</tr>
												</table>
											</td>
										</tr>
									   
									<?php	
									
										
										
										$clase="celdaImagenAzul3";
										
										while($filasEt=mysql_fetch_row($resEtEval))
										{
											$tagA="";
											$tagC="";
											if(isset($arrEtapas[$filasEt[0]]))
											{
												$tagA="<a href=\"javascript:enviarVista('".base64_encode("idProceso=".$idProceso."&numEtapa=".$filasEt[0]."&actor=".$roles)."')\">";
												$tagC="</a>";
											}
										?>
											<tr height="21">
												<td class="<?php echo $clase?>" width="50" align="right"><img src="../images/bullet_red.png" /></td>
												<td class="<?php echo $clase?>" width="570" align="left">&nbsp;&nbsp;<?php echo $tagA?><span class="tituloEtiqueta"><?php echo removerCerosDerecha($filasEt[0])?>.- <?php echo $filasEt[1]?></span><?php echo $tagC?></td>
												<td class="<?php echo $clase?>" width="80" align="center"><?php echo $tagA?><span class="corpo8_bold">
												<?php 
													
													if(isset($arrEtapas[$filasEt[0]]))
													{
														echo $arrEtapas[$filasEt[0]];
														$totalElementos+=$arrEtapas[$filasEt[0]];
													}
													else
														echo "0";
													
												?>
											   </span><?php echo $tagC?>
											   
											   </td>
											</tr>
										<?php
										
											if($clase=="celdaImagenAzul3")
												$clase="celdaBlancaSinImg";
											else
												$clase="celdaImagenAzul3";
										}
									?>
										 <tr height="21">
											<td colspan="3"></td>
										</tr>
									<?php
									}
								}
								
								//----------------
								$totalCategoria=0;
								$consulta="select distinct e.numEtapa,e.nombreEtapa from 4037_etapas e,944_actoresProcesoEtapa ap where e.situacion=1 and e.idProceso=ap.idProceso and 
                                    e.numEtapa=ap.numEtapa and ap.idProceso=".$idProceso." and ap.actor in(".$roles.") and e.numEtapa in 
										(SELECT numEtapa FROM 4037_etapas WHERE idCategoria is null and idProceso=".$idProceso.") order by e.numEtapa";			
								$resEt=$con->obtenerFilas($consulta);
								if($con->filasAfectadas>0)
								{
									while($filasEt=mysql_fetch_row($resEt))
									{
										if(isset($arrEtapas[$filasEt[0]]))
										{
											$totalCategoria+=$arrEtapas[$filasEt[0]];
										}
									}
									?>
									<tr height="21">
									<td colspan="3" align="left" >
										<table width="80%">
										<tr>
											<td width="70%">
											   
											   
											</td>
											<td width="30%" align="left">
												<?php
													  $tagA="";
													  $tagC="";
													  if($totalCategoria>0)
													  {
														  $tagA="<a href=\"javascript:enviarVista('".base64_encode("idProceso=".$idProceso."&idCategoria=NULL&actor=".$roles)."')\">";
														  $tagC="</a>";
													  }
													  echo $tagA;
												  ?>
												  <span  class="letraRoja">
												  
												   <?php
													  echo $totalCategoria;
												  ?>
												  </span>
												  <span class="corpo8">Registros</span>
												  <?php
													  echo $tagC;
												  ?>
											</td>
										</tr>
                                        <tr height="2">
                                            <td style="background-color:#006" colspan="2" >
                                            </td>
                                        </tr>
                                        <tr height="4">
                                            <td style="background-color:#FFF" colspan="2">
                                                
                                            </td>
                                        </tr>
										</table>
										
									</td>
								</tr>
									<?php
								}
								?>
								
									
								<?php
								$consulta="select distinct e.numEtapa,e.nombreEtapa from 4037_etapas e,944_actoresProcesoEtapa ap where e.situacion=1 and e.idProceso=ap.idProceso and 
                                    e.numEtapa=ap.numEtapa and ap.idProceso=".$idProceso." and ap.actor in(".$roles.") and e.numEtapa in 
										(SELECT numEtapa FROM 4037_etapas WHERE idCategoria is null and idProceso=".$idProceso.") order by e.numEtapa";	
								$resEt=$con->obtenerFilas($consulta);
								$clase="celdaImagenAzul3";
								
								while($filasEt=mysql_fetch_row($resEt))
								{
									$tagA="";
									$tagC="";
									if(isset($arrEtapas[$filasEt[0]]))
									{
										$tagA="<a href=\"javascript:enviarVista('".base64_encode("idProceso=".$idProceso."&numEtapa=".$filasEt[0]."&actor=".$roles)."')\">";
										$tagC="</a>";
									}
										
								?>
									<tr height="21">
										<td class="<?php echo $clase?>" width="50" align="right"><img src="../images/bullet_red.png" /></td>
										<td class="<?php echo $clase?>" width="570" align="left">&nbsp;&nbsp;<?php echo $tagA?><span class="tituloEtiqueta"><?php echo removerCerosDerecha($filasEt[0])?>.- <?php echo $filasEt[1]?></span><?php echo $tagC?></td>
										<td class="<?php echo $clase?>" width="80" align="center"><?php echo $tagA?><span class="corpo8_bold">
										<?php 
											if(isset($arrEtapas[$filasEt[0]]))
											{
												echo $arrEtapas[$filasEt[0]];
												$totalElementos+=$arrEtapas[$filasEt[0]];
											}
											else
												echo "0";
											
										?>
									   </span><?php echo $tagC?>
									   
									   </td>
									</tr>
								<?php
								
									if($clase=="celdaImagenAzul3")
										$clase="celdaBlancaSinImg";
									else
										$clase="celdaImagenAzul3";
								}
								
                               
								
								?>
                                <tr height="4">
                                    <td colspan="4" style="background-color:#900">
                                    </td>
                                </tr>
                                <tr height="21">
                               	 <?php
                                        $tagA="";
                                        $tagC="";
                                        if(($totalElementos>0))
                                        {
                                            $tagA="<a href=\"javascript:enviarVista('".base64_encode("idProceso=".$idProceso."&numEtapa=NULL&actor=".$roles)."')\">";
                                            $tagC="</a>";
                                        }
                                       
                                    ?>
                                    <td class="fondoGrid7" width="50" align="right"></td>
                                    <td class="fondoGrid7" width="570" align="right">&nbsp;&nbsp;<?php echo $tagA?><span class="corpo8_bold"><font color="#FF0000">Total General: </font></span><?php echo $tagC?></td>
                                    <td class="fondoGrid7" width="80" align="center">
                                    
                                    <?php
									 echo $tagA;
									?>
                                    <span class="corpo8_bold">
                                    <?php 
                                            
                                            echo $totalElementos;
                                    ?>
                                   </span>
                                    <?php
                                        echo $tagC;
                                    ?>
                                   
                           			</td>
                                    </tr>
							
                            </table>
                        </td>
                        </tr>
                    </table>
                </td>
                </tr>
                <tr>
                    <td><br /></td>
                </tr>
                <?php
                    }
                ?>
        </table>
        </td>
    
    </tr>
    
  </table>
	
<?php
}

function generarTableroControlObjetoRevisor($tipoProceso,$titulo,$idProceso=null,$idActorProcesoEtapa="")									//OK
{
	global $con;
	global $nomPagina;
	$roles=$_SESSION["idRol"];
	$idUsuario=$_SESSION["idUsr"];
	
?>
	<table width="750" cellpadding="4">
    <tr>
        <td>
        <table>
   
                <tr height="23">
                    <td align="LEFT"  class="celdaImagenAzul1">
                    <span class="speaker"><?php echo $titulo ?></span>
                    </td>
                </tr>
                <?php
					$condWhere="";
					if($idProceso!=null)
						$condWhere=" and rp.idProceso=".$idProceso;
					if($idActorProcesoEtapa=="")
	                    $consulta="select distinct(p.idProceso),nombre,p.idTipoProceso,rp.idActorProcesoEtapa from 4001_procesos p,955_revisoresProceso rp where  
				                    p.idProceso=rp.idProceso and p.idTipoProceso=".$tipoProceso." and idUsuarioRevisor=".$idUsuario." ".$condWhere;
					else
						$consulta="select distinct(p.idProceso),nombre,p.idTipoProceso,rp.idActorProcesoEtapa from 4001_procesos p,955_revisoresProceso rp where  
				                    p.idProceso=rp.idProceso and rp.idActorProcesoEtapa=".$idActorProcesoEtapa." and p.idTipoProceso=".$tipoProceso." 
									and idUsuarioRevisor=".$idUsuario." ".$condWhere;

                    $resP=$con->obtenerFilas($consulta);	
					if($con->filasAfectadas>0)
					{
							while($filaP=mysql_fetch_row($resP))
							{
								$idProceso=$filaP[0];
								$tipoProceso=$filaP[2];
								$consulta="select nombreTabla,idFormulario from 900_formularios where idProceso=".$idProceso." and formularioBase=1";
								$filaFrm=$con->obtenerPrimeraFila($consulta);
								$nTabla=$filaFrm[0];
								$idFormulario=$filaFrm[1];
								$consulta="SELECT funcionExclusion FROM 203_funcionesExclusionRegistros WHERE idProceso=".$idProceso." AND tipoActor IN (1,0)";
								$funcionExc=$con->obtenerValor($consulta);
								
								$consulta="select id_".$nTabla.",idEstado,rp.idFormulario,rp.idReferencia from ".$nTabla." r,944_actoresProcesoEtapa pe,955_revisoresProceso rp 
												where rp.idActorProcesoEtapa=pe.idActorProcesoEtapa and rp.idUsuarioRevisor=".$idUsuario."  and  rp.estado in (1) and
												r.id_".$nTabla."=rp.idReferencia and rp.idActorProcesoEtapa=".$idActorProcesoEtapa;

								$resReg=$con->obtenerFilas($consulta);
								$arrEtapas=array();
								while($filaReg=mysql_fetch_row($resReg))
								{
									$considerar=true;
									if($funcionExc!="")
									{
										eval('$considerar='.$funcionExc."(".$filaReg[2].",".$filaReg[0].");");
										
									}
									
									if($considerar)
									{
										if(isset($arrEtapas["".$filaReg[1].""]))
											$arrEtapas["".$filaReg[1].""]++;
										else
											$arrEtapas["".$filaReg[1].""]=1;
									}
								}
								
						?>
                                <tr>
                                <td>
                                    <table >
                                        <tr height="23">
                                        <td width="20"  class="celdaImagenAzul2" align="right">
                                        <a href="javascript:mostrarOcultarEtapas(<?php echo $filaP[0]?>)">
                                        <img src="../images/verMenos.png" alt="Ocultar registros por etapas" title="Ocultar registros por etapas" id='imgEtapas_<?php echo $filaP[0]?>'/>
                                        </a>
                                        </td>
                                        <td  class="celdaImagenAzul2" width="730" align="left" colspan="2">
                                            &nbsp;&nbsp;<span class="letraRojaSubrayada8">Proceso:</span>&nbsp;&nbsp;<span class="corpo8_bold"><?php echo $filaP[1]?></span>&nbsp;<span class="letraAzulSubrayada7"></span>
                                        </td>
                                        </tr>
                                        <tr>
                                        <td style="padding:7px" colspan="3">
                                            <table id="tbl_<?php echo $idProceso?>" style="display:">
                                            <?php
                                            	$consulta="select * from 4037_categoriasEtapa where idProceso=".$idProceso." order by numCategoria";
												$resCat=$con->obtenerFilas($consulta);
												$totalElementos=0;
												
												while($filaCat=mysql_fetch_row($resCat))
												{
													$totalCategoria=0;
													$consulta="select numEtapa from  4037_etapas where situacion=1 and idProceso=".$idProceso." and idCategoria=".$filaCat[0];
													$resEt=$con->obtenerFilas($consulta);
													$consulta="	select distinct e.numEtapa,e.nombreEtapa from 4037_etapas e,944_actoresProcesoEtapa pe,955_revisoresProceso rp where e.situacion=1 and
													e.numEtapa=pe.numEtapa and e.idProceso=pe.idProceso and e.idProceso=".$idProceso." and e.idCategoria=".$filaCat[0]." and
													rp.idActorProcesoEtapa=pe.idActorProcesoEtapa and rp.idUsuarioRevisor=".$idUsuario." and estado in (1) and rp.idActorProcesoEtapa=".$idActorProcesoEtapa;
													
													$resEtEval=$con->obtenerFilas($consulta);
													$nEtapas=$con->filasAfectadas;
													if($nEtapas>0)
													{
														while($filasEt=mysql_fetch_row($resEt))
														{
															if(isset($arrEtapas[$filasEt[0]]))
															{
																$totalCategoria+=$arrEtapas[$filasEt[0]];
															}
														}
														
														
													?>
														
														<tr height="21">
															<td colspan="3" align="left">
																<table width="80%">
																	<tr height="21">
																		<td >
																			<table width="100%">
																			<tr>
																				<td width="70%" align="left">
																					<span class="copyrigthSinPadding"><b>
																					<?php echo $filaCat[1].".-".$filaCat[2] ?></b>
																					</span>
																				   
																				</td>
																				<td width="30%" align="left">
																					<?php
																						$tagA="";
																						$tagC="";
																						if($totalCategoria>0)
																						{
																							$tagA="<a href=\"javascript:enviarVista('".base64_encode("idProceso=".$idProceso."&idCategoria=".$filaCat[0]."&idActorProcesoEtapa=".$idActorProcesoEtapa)."')\">";
																							$tagC="</a>";
																						}
																						echo $tagA;
																					?>
																					<span  class="letraRoja">
																					
																					 <?php
																						echo $totalCategoria;
																					?>
																					</span>
																					<span class="corpo8">Registros</span>
																					<?php
																						echo $tagC;
																					?>
																				</td>
																			</tr>
																			</table>
																			
																		</td>
																	</tr>
																	<tr height="2">
																		<td style="background-color:#006" >
																		</td>
																	</tr>
																	<tr height="4">
																		<td style="background-color:#FFF" >
																			
																		</td>
																	</tr>
																</table>
															</td>
														</tr>
													   
													<?php	
													
														
														$clase="celdaImagenAzul3";
														
														while($filasEt=mysql_fetch_row($resEtEval))
														{
															$tagA="";
															$tagC="";
															if(isset($arrEtapas[$filasEt[0]]))
															{
																$tagA="<a href=\"javascript:enviarVista('".base64_encode("idProceso=".$idProceso."&numEtapa=".$filasEt[0]."&idActorProcesoEtapa=".$idActorProcesoEtapa)."')\">";
																$tagC="</a>";
															}
														?>
															<tr height="21">
																<td class="<?php echo $clase?>" width="50" align="right"><img src="../images/bullet_red.png" /></td>
																<td class="<?php echo $clase?>" width="570" align="left">&nbsp;&nbsp;<?php echo $tagA?><span class="tituloEtiqueta"><?php echo removerCerosDerecha($filasEt[0])?>.- <?php echo $filasEt[1]?></span><?php echo $tagC?></td>
																<td class="<?php echo $clase?>" width="80" align="center"><?php echo $tagA?><span class="corpo8_bold">
																<?php 
																	
																	if(isset($arrEtapas[$filasEt[0]]))
																	{
																		echo $arrEtapas[$filasEt[0]];
																		$totalElementos+=$arrEtapas[$filasEt[0]];
																	}
																	else
																		echo "0";
																	
																?>
															   </span><?php echo $tagC?>
															   
															   </td>
															</tr>
														<?php
														
															if($clase=="celdaImagenAzul3")
																$clase="celdaBlancaSinImg";
															else
																$clase="celdaImagenAzul3";
														}
													?>
														 <tr height="21">
															<td colspan="3"></td>
														</tr>
													<?php
													}
												}
                                                               
                                            
												$totalCategoria=0;
												$consulta="	select distinct e.numEtapa,e.nombreEtapa from 4037_etapas e,944_actoresProcesoEtapa pe,955_revisoresProceso rp where e.situacion=1 and
														e.numEtapa=pe.numEtapa and e.idProceso=pe.idProceso and e.idProceso=".$idProceso." and e.idCategoria is null and
														rp.idActorProcesoEtapa=pe.idActorProcesoEtapa and rp.idUsuarioRevisor=".$idUsuario." and estado in (1) and rp.idActorProcesoEtapa=".$idActorProcesoEtapa;				
												$resEt=$con->obtenerFilas($consulta);
												
													while($filasEt=mysql_fetch_row($resEt))
													{
														if(isset($arrEtapas[$filasEt[0]]))
														{
															$totalCategoria+=$arrEtapas[$filasEt[0]];
														}
													}
													?>
													<tr height="21">
													<td colspan="3" align="left" >
														<table width="80%">
														<tr>
															<td width="70%">
															   
															   
															</td>
															<td width="30%" align="left">
																<?php
																	  $tagA="";
																	  $tagC="";
																	  if($totalCategoria>0)
																	  {
																		  $tagA="<a href=\"javascript:enviarVista('".base64_encode("idProceso=".$idProceso."&idCategoria=NULL&idActorProcesoEtapa=".$idActorProcesoEtapa)."')\">";
																		  $tagC="</a>";
																	  }
																	  echo $tagA;
																  ?>
																  <span  class="letraRoja">
																  
																   <?php
																	  echo $totalCategoria;
																  ?>
																  </span>
																  <span class="corpo8">Registros</span>
																  <?php
																	  echo $tagC;
																  ?>
															</td>
														</tr>
                                                        <tr height="2">
                                                          <td style="background-color:#006" colspan="2" >
                                                          </td>
                                                      </tr>
                                                      <tr height="4">
                                                          <td style="background-color:#FFF" colspan="2">
                                                              
                                                          </td>
                                                      </tr>
														</table>
														
													</td>
												</tr>
                                                
                                                
                                                
                                            <?php
                                           $consulta="	select distinct e.numEtapa,e.nombreEtapa from 4037_etapas e,944_actoresProcesoEtapa pe,955_revisoresProceso rp where e.situacion=1 and
													e.numEtapa=pe.numEtapa and e.idProceso=pe.idProceso and e.idProceso=".$idProceso." and e.idCategoria is null and
													rp.idActorProcesoEtapa=pe.idActorProcesoEtapa and rp.idUsuarioRevisor=".$idUsuario." and estado in (1) and rp.idActorProcesoEtapa=".$idActorProcesoEtapa;	
                                            $resEt=$con->obtenerFilas($consulta);
                                            $clase="celdaImagenAzul3";
                                            
                                            while($filasEt=mysql_fetch_row($resEt))
                                            {
                                                $tagA="";
                                                $tagC="";
                                                if(isset($arrEtapas[$filasEt[0]]))
                                                {
                                                    $tagA="<a href=\"javascript:enviarVista('".base64_encode("idProceso=".$idProceso."&numEtapa=".$filasEt[0]."&idActorProcesoEtapa=".$idActorProcesoEtapa)."')\">";
                                                    $tagC="</a>";
                                                }
                                                    
                                            ?>
                                                <tr height="21">
                                                    <td class="<?php echo $clase?>" width="50" align="right"><img src="../images/bullet_red.png" /></td>
                                                    <td class="<?php echo $clase?>" width="570" align="left">&nbsp;&nbsp;<?php echo $tagA?><span class="tituloEtiqueta"><?php echo removerCerosDerecha($filasEt[0])?>.- <?php echo $filasEt[1]?></span><?php echo $tagC?></td>
                                                    <td class="<?php echo $clase?>" width="80" align="center"><?php echo $tagA?><span class="corpo8_bold">
                                                    <?php 
                                                        if(isset($arrEtapas[$filasEt[0]]))
                                                        {
                                                            echo $arrEtapas[$filasEt[0]];
                                                            $totalElementos+=$arrEtapas[$filasEt[0]];
                                                        }
                                                        else
                                                            echo "0";
                                                        
                                                    ?>
                                                   </span><?php echo $tagC?>
                                                   
                                                   </td>
                                                </tr>
                                            <?php
                                            
                                                if($clase=="celdaImagenAzul3")
                                                    $clase="celdaBlancaSinImg";
                                                else
                                                    $clase="celdaImagenAzul3";
                                            }
                                            
                                           
                                            
									?>
                                    <tr height="4">
                                      <td colspan="4" style="background-color:#900">
                                      </td>
                                  </tr>
									<tr height="21">
									 <?php
											$tagA="";
											$tagC="";
											if(($totalElementos>0))
											{
												$tagA="<a href=\"javascript:enviarVista('".base64_encode("idProceso=".$idProceso."&numEtapa=NULL&idActorProcesoEtapa=".$idActorProcesoEtapa)."')\">";
												$tagC="</a>";
											}
										   
										?>
										<td class="fondoGrid7" width="50" align="right"></td>
										<td class="fondoGrid7" width="570" align="right">&nbsp;&nbsp;<?php echo $tagA?><span class="corpo8_bold"><font color="#FF0000">Total General: </font></span><?php echo $tagC?></td>
										<td class="fondoGrid7" width="80" align="center">
										
										<?php
										 echo $tagA;
										?>
										<span class="corpo8_bold">
										<?php 
												
												echo $totalElementos;
										?>
									   </span>
										<?php
											echo $tagC;
										?>
                                            </table>
                                        </td>
                                        </tr>
                                    </table>
                                </td>
                                </tr>
                        <?php
							}
						?>
                <tr>
                    <td><br /></td>
                </tr>
                <?php
                    }
                ?>
        </table>
        </td>
    
    </tr>
    
</table>
<?php
}

function obtenerCamposConfiguracion($idFormulario,&$dComp)
{
	global $con;
	$consulta="select f.idConfGrid,campoAgrupacion,complementario from 909_configuracionTablaFormularios f where f.idFormulario=".$idFormulario;
	
	$filaConfFrm=$con->obtenerPrimeraFila($consulta);
	$idConfiguracion=$filaConfFrm[0];
	$campoAgrupacion=$filaConfFrm[1];
	$datosComp=$filaConfFrm[2];
	if($dComp!==null)
		$dComp=$datosComp;
	if($idConfiguracion=="")
		$idConfiguracion="-1";
	
	$consulta="	  select cg.titulo,if((select distinct(tipoElemento) from 901_elementosFormulario where 
					idGrupoElemento=cg.idElementoFormulario) is null,cg.idElementoFormulario,(select distinct(tipoElemento) 
					from 901_elementosFormulario where idGrupoElemento=cg.idElementoFormulario)) as tipoElemento,
					if((select distinct(nombreCampo) from 901_elementosFormulario where 
					idGrupoElemento=cg.idElementoFormulario) is null,
					case cg.idElementoFormulario
						 when '-10' then 'fechaCreacion'
						 when '-11' then 'responsableCreacion'
						 when '-12' then 'fechaModificacion'
						 when '-13' then 'responsableModificacion'
						 when '-14' then 'unidadUsuarioRegistro'
						 when '-15' then 'institucionUsuarioRegistro'
						 when '-16' then 'dtefechaSolicitud'
						 when '-17' then 'tmeHoraInicio'
						 when '-18' then 'tmeHoraFin'
						 when '-19' then 'dteFechaAsignada'
						 when '-20' then 'tmeHoraInicialAsignada'
						 when '-21' then 'tmeHoraFinalAsignada'
						 when '-22' then 'unidadReservada'
						 when '-23' then 'tmeHoraSalida'
						 when '-24' then 'idEstado'
						 when '-25' then 'codigoRegistro'
						 end
					   ,(select distinct(nombreCampo) 
					from 901_elementosFormulario where idGrupoElemento=cg.idElementoFormulario)) as nombreCampo,alineacionValores,idCamposGrid
					from 907_camposGrid cg where 
					cg.idIdioma=".$_SESSION["leng"]." and cg.idConfGrid=".$idConfiguracion." order by cg.idCamposGrid";
	 
	$resCampos=$con->obtenerFilas($consulta);
	if($con->filasAfectadas==0)
	{
		echo "
				  <tr>
				  <td>
					  <span class='letraFichaRespuesta'>
					  Este proceso no tiene configurado una vista de listado. Para solucionarlo, una persona con los privilegios suficientes debe llevar a cabo dicha configuración en la sección \"Configuración del listado de registros\" que se encuentra en la configuración del formulario principal del proceso.
					  </span>
				  </td>
				  </tr>
				  
			  ";
		return false;
	}
	$arrConfCampo=array();
	$pos=0;
	while($fcampos=mysql_fetch_row($resCampos))
	{
		switch($fcampos[3])
		{
			case 1:
			  $alineacion='left';
			break;
			case 2:
			  $alineacion='right';
			break;
			case 3:
			  $alineacion='center';
			break;
			case 4:
			  $alineacion='justify';
			break;
		}
	   
		$arrConfCampo[$pos]["titulo"]=$fcampos[0];
		$arrConfCampo[$pos]["tipoElemento"]=$fcampos[1];
		$arrConfCampo[$pos]["nombreCampo"]=$fcampos[2];
		$arrConfCampo[$pos]["alineacion"]=$alineacion;
		$arrConfCampo[$pos]["idCampoGrid"]=$fcampos[4];
		$pos++;
	}
	return $arrConfCampo;
}

function obtenerModulosDisponibles($idProceso,&$investigadores,&$anexos,&$programaTrabajo,&$ademdum,$datosComp,&$invAux)
{
	global $con;
	$consulta="select f.titulo,e.complementario from 203_elementosDTD e,900_formularios f where f.idFormulario=e.idFormulario and e.idProceso=".$idProceso." and e.tipoElemento=1 and titulo in(3,4,6,7)";
	$resEl=$con->obtenerFilas($consulta);
	while($filaEl=mysql_fetch_row($resEl))
	{
		switch($filaEl[0])
		{
			case "3":
				
				$investigadoresComp=explode(",",$filaEl[1]);
				$invAux=$investigadoresComp;
				if(strpos($datosComp,"A")!==false)
					$investigadores=true;
			break;
			case "4":
				if(strpos($datosComp,"D")!==false)
					$anexos=true;
			break;
			case "6":
				if(strpos($datosComp,"P")!==false)
					$programaTrabajo=true;
			break;
			case "7":
				if(strpos($datosComp,"M")!==false)
					$ademdum=true;
			break;
		}
	}
}

function generarFichaElemento($arrConfCampo,$nTabla,$idFormulario,$investigadores,$programaTrabajo,$anexos,$ademdum,$filaReg,$invPrincipal,$tituloInvestigador,$idProceso,$idReferencia)
{
	global $con;

	$nFilasConf=sizeof($arrConfCampo);

	for($x=1;$x<$nFilasConf;$x++)
	{
		
		if($arrConfCampo[$x]["tipoElemento"]!="-100")
		{
			if($arrConfCampo[$x]["nombreCampo"]=='responsableCreacion')
			{
				$consulta="select responsable from ".$nTabla." where id_".$nTabla."=".$filaReg["idRegistro"];
				$idResponsable=$con->obtenerValor($consulta);
				
	?>
				<tr>
					<td></td>
					<td align="left" class="etiquetaFicha"><span class="corpo8_bold"><?php  echo $arrConfCampo[$x]["titulo"] ?></span></td>
					<td class="valorFicha" colspan="2" align="<?php echo $arrConfCampo[$x]["alineacion"]?>" ><span class="corpo8"><a href="javascript:verUsrNuevaPagina('<?php echo base64_encode($idResponsable) ?>')"><?php echo formatearValorFicha($filaReg[$arrConfCampo[$x]["nombreCampo"]],$arrConfCampo[$x]["tipoElemento"])?></a></span></td>
				</tr>
	<?php
			}
			else
			{
				
				 
	?>
				<tr>
					<td></td>
					<td align="left" class="etiquetaFicha"><span class="corpo8_bold"><?php  echo $arrConfCampo[$x]["titulo"] ?></span></td>
					<td class="valorFicha" colspan="2" align="<?php echo $arrConfCampo[$x]["alineacion"]?>" ><span class="corpo8"><?php echo formatearValorFicha($filaReg[$arrConfCampo[$x]["nombreCampo"]],$arrConfCampo[$x]["tipoElemento"])?></span></td>
				</tr>
	<?php
			}
		}
		else
		{
			$consulta="SELECT configuracion FROM 907_camposGrid WHERE idCamposGrid=".$arrConfCampo[$x]["idCampoGrid"];

			$cadObj=$con->obtenerValor($consulta);
			$obj=json_decode($cadObj);
			$funcion=$obj->funcion;
		?>
				<tr>
					<td></td>
					<td align="left" class="etiquetaFicha" valign="top"><span class="corpo8_bold"><?php  echo $arrConfCampo[$x]["titulo"] ?></span></td>
					<td class="valorFicha" colspan="2" align="<?php echo $arrConfCampo[$x]["alineacion"]?>" >
                    <span class="corpo8">
                    <?php
						eval($funcion."('".$idFormulario."','".$filaReg["idRegistro"]."');");
					?>
                    </span></td>
				</tr>
	<?php	
		}
	}

?>
<?php
	if($investigadores)
	{
?>
 <tr>
	<td></td>
	<td class="etiquetaFicha" align="left" valign="top"><span class="corpo8_bold"><?php echo $tituloInvestigador?> participantes:</span></td><td class="valorFicha" colspan="4" align="left"><span class="corpo8"><?php echo $invPrincipal?></span></td>
</tr>
<?php
	}
?>
<?php
	if($programaTrabajo)
	{
		
//		http://accion111.grupolatis.net/gantt/showGantt.php?param=aWRGb3JtdWxhcmlvPTgzNSZpZFJlZmVyZW5jaWE9Mg==
?>
 <tr>
	<td></td>
	<td class="etiquetaFicha" align="left"><span class="corpo8_bold">Programa de trabajo:</span></td><td class="valorFicha" colspan="4" align="left"><span class="corpo8"><a href="javascript:verGantt('<?php echo base64_encode($filaReg["idRegistro"])?>')"><img src="../images/gantt.png" title="Ver programa de trabajo" alt="Ver programa de trabajo" /></a></span></td>
</tr>
<?php
	}
?>
<?php
	if($anexos)
	{
		?>
		<tr>
			<td></td>
			<td class="etiquetaFicha" colspan="" align="left"><span class="corpo8_bold">Anexos:</span></td>
			<td class="valorFicha" colspan="4" align="left">
			<table>
			<?php
			
			$consulta="select id_210_archivosProyectos,titulo from 210_archivosProyectos where idFormulario=".$idFormulario." and idReferencia=".$filaReg["idRegistro"]." and tipoDocumento=1 order by titulo desc";
			$resArch=$con->obtenerFilas($consulta);
			while($filaArch=mysql_fetch_row($resArch))
			{
			?>
				<tr>
				<td>
				<a href="../media/obtenerDocumento.php?doc=<?php echo base64_encode ($filaArch[0]) ?>">
				<img src='../images/icon_document.gif' />&nbsp;<span class="copyrigth"><?php echo $filaArch[1]?></</span>
				</a><br />
				</td>
				</tr>
			<?php
			}
		?>
			</table>
			</td>
		</tr>
		<?php
	
	}
	?>
	<?php
	if($ademdum)
	{
		?>
		<tr>
			<td></td>
			<td class="etiquetaFicha" colspan="" align="left" valign="top"><span class="corpo8_bold">Ademdum de:</span></td>
			<td class="valorFicha" colspan="4" align="left">
			<table>
			<?php
			$consulta="select id_981_ademdums,idFormularioAd,idReferenciaAd from 981_ademdums a where a.idFormulario=".$idFormulario." and a.idReferencia=".$filaReg["idRegistro"];
			$resArch=$con->obtenerFilas($consulta);
			while($fila=mysql_fetch_row($resArch))
			{
				$consulta="select f.nombreTabla,p.nombre from 900_formularios f,4001_procesos p where p.idProceso=f.idProceso and f.idFormulario=".$fila[1];
				$filaProc=$con->obtenerPrimeraFila($consulta);
				$nTabla=$filaProc[0];
				$tipo=$filaProc[1];
				$consulta="select * from ".$nTabla." where id_".$nTabla."=".$fila[2];
				$filaProy=$con->obtenerPrimeraFila($consulta);
				$nProy=$filaProy[9];
			?>
							<tr>
								
								<td align="left" ><span class="corpo8"><?php echo $nProy?></span></td>
								<td align="left" ><span class="corpo8">&nbsp;&nbsp;(<?php echo $tipo?>)</span></td>
							</tr>
		<?php
			}
		?>
			</table>
			</td>
		</tr>
		<?php
		
	}
										
}

function generarTablaElementosProceso($idProceso,$nEtapa,$titulo="Registros",$idUsuario,$sl,$verCV,$idCategoria,$pagina,$relacion,$actor,$idProcesoP,$idReferencia,$tVista)				//OK+
{
	global $con;

	$numEtapa=$nEtapa;
	$tituloListado="";
	$idFrmBase=obtenerFormularioBase($idProceso);
	$consulta="select numRegPag from 909_configuracionTablaFormularios where idFormulario=".$idFrmBase;
	$nElemento=$con->obtenerValor($consulta);
	$consulta="select p.idTipoProceso from 4001_procesos p where p.idProceso=".$idProceso;
	$tipoProceso=$con->obtenerValor($consulta);
	if($numEtapa!=null)
	{
		$consulta="select nombreEtapa from 4037_etapas where idProceso=".$idProceso." and numEtapa= ".$numEtapa;
		$nEtapa=$con->obtenerValor($consulta);
		$tituloListado= ($titulo).' (ETAPA: <span class="letraRojaSubrayada8">'.removerCerosDerecha($numEtapa).".- ".$nEtapa."</span>)";
	}
	else
	{
		if($idCategoria!=null)
		{
			if($idCategoria=="NULL")
				$nCategoria="SIN CATEGORÍA";
			else
			{
				$consulta="select nomCategoria from 4037_categoriasEtapa where idCategoria=".$idCategoria;
				$nCategoria=$con->obtenerValor($consulta);
			}
			$tituloListado=($titulo)." (CATEGORÍA:&nbsp;<span class='letraRojaSubrayada8'>".strtoupper($nCategoria)."</span>)";
		}
		else
			$tituloListado=($titulo)." (GENERAL)";
		$nEtapa="";
	}

	$ct=($nElemento*($pagina-1))+1;
	$consulta="select codigoRol from 807_usuariosVSRoles where idUsuario=".$idUsuario;
	$roles=$con->obtenerListaValores($consulta,"'");
	if($actor!="")
		$roles=$actor;
	?>
		<table width="750" class="" style="padding:7px">
			<tr height="23">
				<td align="left" style="background-image:url(../images/fondo_barra_mc_azul.gif)">
				<span class="speaker"><?php echo $tituloListado?></span>
				</td>
			</tr>
			<tr>
				<td>
				</td>
			</tr>
			<?php
				$consulta="select nombreTabla,idFormulario from 900_formularios where idProceso=".$idProceso." and formularioBase=1";
				$filaFrm=$con->obtenerPrimeraFila($consulta);
				$nTabla=$filaFrm[0];
				$idFormulario=$filaFrm[1];
				$pagNuevo="../modeloPerfiles/registroFormulario.php";
				$pagModificar="../modeloPerfiles/registroFormulario.php";
				$pagVer="../modeloPerfiles/verFichaFormulario.php";
				$query="select pagVista,accion from 936_vistasProcesos where tipoProceso=".$tipoProceso;
				$resPagAccion=$con->obtenerFilas($query);
				$arrPagNuevo="var arrPagNuevo=new Array();";
				while($filaAccion=mysql_fetch_row($resPagAccion))
				{
					$paginaRef="";
					switch($filaAccion[1])
					{
						case "0"://Nuevo
							$paginaRef=$filaAccion[0];
						break;
						case "1"://Modificar
							$paginaRef=$filaAccion[0];
						break;
						case "2"://Consultar/Ver
							$paginaRef=$filaAccion[0];
						break;
					}
					$arrPagNuevo.="arrPagNuevo[".$filaAccion[1]."]='".$paginaRef."';";
				}
				$elimina=false;
				$permitirParticipante=false;
				$claveParticipacion="";
								
				$consulta="select complementario from 949_actoresVSAccionesProceso where actor in(".$roles.") and idAccion=9 and idProceso=".$idProceso." order by complementario";
				$complementario=$con->obtenerValor($consulta);
				$condWhere=" 1=1";
				if($numEtapa!=null)
					$condWhere=" idEstado=".$numEtapa;
				else
					if($idCategoria!=null)
					{
						if($idCategoria!="NULL")
							$condWhere=" idEstado in (select numEtapa from 4037_etapas where idCategoria=".$idCategoria.")";
						else
							$condWhere=" idEstado in (select numEtapa from 4037_etapas where idCategoria is ".$idCategoria.")";
					}

				$idFrmAutores="-1";
				$reemplazarConsulta=false;
				if(($idProcesoP=="")||($tVista=="2"))
				{
					if((!$verCV)&&($relacion==1))
					{
						switch($complementario)
						{
							case "1":
							break;
							case "2":
								$condWhere.=" and codigoInstitucion='".$_SESSION["codigoInstitucion"]."'";
							break;
							case "3":
								$condWhere.=" and codigoUnidad like '".$_SESSION["codigoUnidad"]."%'";
							break;
							case "4":
								$condWhere.=" and codigoUnidad='".$_SESSION["codigoUnidad"]."'";
							break;
							case "5":
								$idFrmAutores=incluyeModulo($idProceso,3);
								if($idFrmAutores=="-1")
									$condWhere.=" and responsable=".$idUsuario;
								else
								{
									$idFormularioBase=obtenerFormularioBase($idProceso);	
									//$condWhere.=" and id_".$nTabla." in (select distinct idReferencia from 246_autoresVSProyecto where idUsuario=".$idUsuario." and idFormulario=".$idFormularioBase."
									//			union SELECT idRegistro FROM 9202_accionesSolicitudUsuario WHERE idFormulario=".$idFormularioBase." and idUsuario=".$idUsuario." AND estado=1 AND accion=5)";
									$condWhere="select id_".$nTabla." as idRegistro,t.idReferencia,idEstado from ".$nTabla." t,246_autoresVSProyecto a   
												WHERE t.id_".$nTabla."=a.idReferencia AND a.idUsuario=".$idUsuario." AND a.idFormulario=".$idFormularioBase." and ".$condWhere.
												" union SELECT id_".$nTabla." as idRegistro,t.idReferencia,idEstado FROM ".$nTabla." t,9202_accionesSolicitudUsuario a 
												WHERE t.id_".$nTabla."=a.idRegistro AND a.idUsuario=".$idUsuario." AND a.idFormulario=".$idFormularioBase." AND estado=1 AND accion=5 and ".$condWhere;

									$reemplazarConsulta=true;
								}
							
							break;
						}
					}
					else
					{
						$idFrmAutores=incluyeModulo($idProceso,3);
						if($idFrmAutores=="-1")
							$condWhere.=" and responsable=".$idUsuario;
						else
						{
							$idFormularioBase=obtenerFormularioBase($idProceso);	
							//$condWhere.=" and id_".$nTabla." in (select distinct idReferencia from 246_autoresVSProyecto where idUsuario=".$idUsuario." and idFormulario=".$idFormularioBase."
								//		union SELECT idRegistro FROM 9202_accionesSolicitudUsuario WHERE idFormulario=".$idFormularioBase." and idUsuario=".$idUsuario." AND estado=1 AND accion=5)							";
							$condWhere="select id_".$nTabla." as idRegistro,t.idReferencia,idEstado from ".$nTabla." t,246_autoresVSProyecto a   
												WHERE t.id_".$nTabla."=a.idReferencia AND a.idUsuario=".$idUsuario." AND a.idFormulario=".$idFormularioBase." and ".$condWhere.
												" union SELECT id_".$nTabla." as idRegistro,t.idReferencia,idEstado FROM ".$nTabla." t,9202_accionesSolicitudUsuario a 
												WHERE t.id_".$nTabla."=a.idRegistro AND a.idUsuario=".$idUsuario." AND a.idFormulario=".$idFormularioBase." AND estado=1 AND accion=5  and ".$condWhere;

							$reemplazarConsulta=true;
						}
					}
				}
				else
					$condWhere.=" and idProcesoPadre=".$idProcesoP." and idReferencia=".$idReferencia;
				if(!$reemplazarConsulta)
				{
					if($condWhere!="")
						$condWhere=" where ".$condWhere;
					$consulta="select id_".$nTabla." as idRegistro,idReferencia,idEstado from ".$nTabla." ".$condWhere;
				}
				else
					$consulta=$condWhere;

				$consulta=str_replace(", from"," from ",$consulta);

				$resReg=$con->obtenerFilas($consulta);
				$nRegistros=$con->filasAfectadas;
				
				$nPaginas= intval($nRegistros / $nElemento);
				$residuo=$nRegistros-($nPaginas*$nElemento);
				if($residuo>0)
					$nPaginas++;
				if($nRegistros==0)
					$nPaginas=1;
					
				?>
				<tr>
					<td align="left">
						<table width="100%">
						<tr>
						<td>
						<span class="corpo8_bold">Página:</span>&nbsp;&nbsp;
						<?php
							$cadPag="";
							for($x=1;$x<=$nPaginas;$x++)
							{
								if($cadPag=="")
									$cadPag="<a href='javascript:irPagina(".$x.")'>".$x."</a>";
								else
									$cadPag.=", "."<a href='javascript:irPagina(".$x.")'>".$x."</a>";
								
							}
							echo $cadPag;
						?>
						</td>
						</tr>
                        <?php
						$mostrarVincularRegistro=false;
						switch($idProceso)
						{
							case 96:
							case 109:
							case 99:
								$mostrarVincularRegistro=true;
							break;
							
						}

						$lblNoEncuentra="";
						$mostrarVincularRegistro=false;
						if($mostrarVincularRegistro)
						{
							switch($idProceso)
							{
								case "96":
									$lblNoEncuentra="<span class='letraRojaSubrayada8'>No encuentra un Artículo científico suyo?</span>, posiblemente dicho artículo Sí se encuentre registrado en el sistema, pero usted no está considerado como autor de él, para averiguarlo ";
								break;	
								case "109":
									$lblNoEncuentra="<span class='letraRojaSubrayada8'>No encuentra un Libro/capítulo suyo?</span>, posiblemente dicho Libro/capítulo Sí se encuentre registrado en el sistema, pero usted no está considerado como autor de él, para averiguarlo ";
								break;	
								case "99":
									$lblNoEncuentra="<span class='letraRojaSubrayada8'>No encuentra una Dirección de tesis suya?</span>, posiblemente dicho dirección de tesis Sí se encuentre registrada en el sistema, pero usted no está considerado como autor/tutor de él, para averiguarlo ";
								break;	
							}
						?>
                        <tr>
                        	<td align="left">
                            <br />
                            	<span class="letraFichaRespuesta"><?php echo $lblNoEncuentra?> de click <a href="javascript:buscarProducto('<?php echo bE($idProceso)?>')"><font color="red"><b>AQUÎ</b></font></a></span>
                            </td>
                        </tr>
                        <?php
						}
						?>
						</table>
                        <br /><br />
					</td>
				</tr>
                <?php

                if($nRegistros==0)
				{
				?>
                    <tr>
                        <TD align="center">
                            <span class="copyrigthSinPadding">Actualmente no existen registros que evaluar</span>
                        </TD>
                    </tr>
               <?php		
				}
				else
				{
					if($numEtapa!=null)
					{
						if($relacion==1)
						{
							$consulta="select idGrupoAccion from 944_actoresProcesoEtapa a,947_actoresProcesosEtapasVSAcciones ae where ae.idActorProcesoEtapa=a.idActorProcesoEtapa and a.tipoActor=1 
										and  a.actor in (".$roles.") and a.numEtapa=".$numEtapa." and a.idProceso=".$idProceso;
							$resAcciones=$con->obtenerFilas($consulta);
							while($filaAcciones=mysql_fetch_row($resAcciones))
							{
								switch($filaAcciones[0])
								{
									case "7":
										$elimina=true;
									break;
								}
							}
						}
						
					}

					$btnEliminar="";	
				  	$datosComp="";
					$arrConfCampo=obtenerCamposConfiguracion($idFrmBase,$datosComp);
					if(!$arrConfCampo)
						return;
					$nFilasConf=sizeof($arrConfCampo);
					$anexos=false;
					$investigadores=false;
					$investigadoresComp="";
					$programaTrabajo=false;
					$ademdum=false;

					obtenerModulosDisponibles($idProceso,$investigadores,$anexos,$programaTrabajo,$ademdum,$datosComp,$investigadoresComp);
					$idInvPrincipal="0";
					$aplicarExcepcion=false;
					$vista="";
					if(($relacion=="1")&&($actor!=""))
					{
						$consulta="SELECT complementario FROM 949_actoresVSAccionesProceso WHERE actor=".$actor." AND idProceso=".$idProceso." and idAccion=9";
						$vista=$con->obtenerValor($consulta);
					}
					//

					$arrRegistros=array();
					$consulta="SELECT campoOrden,direccionOrden FROM 909_configuracionTablaFormularios WHERE idFormulario=".$idFormulario;
					$filaOrden=$con->obtenerPrimeraFila($consulta);
					$idElementoOrden=obtenerIdElementoFormulario($filaOrden[0],$idFormulario);
					$ctAux=0;

					$arrListadoRegistros=array();
					$pos=0;
					$arrPosLlave=array();
					while($filaReg=mysql_fetch_assoc($resReg))
					{
						$idReferencia=$filaReg["idReferencia"];
						$numEtapaPadre=obtenerEtapaProcesoActual($filaReg["idRegistro"],$idReferencia,$idFormulario);
						$idUsuarioResp=obtenerResponsableProcesoActual($filaReg["idRegistro"],$idReferencia,$idFormulario);
						$cadObj='{"p16":{"p1":"'.$idFormulario.'","p2":"'.$idProceso.'","p3":"'.$filaReg["idRegistro"].'","p4":"'.$numEtapaPadre.'","p5":"'.$idReferencia.'","p6":"'.$idUsuarioResp.'"}}';
						$valorOrden=obtenerValorControlFormularioBase($idElementoOrden,$filaReg["idRegistro"],$cadObj);
						$arrListadoRegistros[$valorOrden."_".$pos]["idReferencia"]=$filaReg["idReferencia"];
						$arrListadoRegistros[$valorOrden."_".$pos]["idRegistro"]=$filaReg["idRegistro"];
						$arrListadoRegistros[$valorOrden."_".$pos]["cadObj"]=$cadObj;
						$pos++;
					}
					if($filaOrden[1]=='ASC')
						ksort($arrListadoRegistros);
					else
						krsort($arrListadoRegistros);
						
					foreach($arrListadoRegistros as $filaReg)
					{
						array_push($arrPosLlave,$filaReg);	
					}
					$nInicio=($nElemento*($pagina-1));
					$nFinFilaReg=$nInicio+$nElemento;
					if(sizeof($arrPosLlave)<$nFinFilaReg)
						$nFinFilaReg=sizeof($arrPosLlave);
					for($nFilaReg=$nInicio;$nFilaReg<$nFinFilaReg;$nFilaReg++)
					{
						$filaReg=$arrPosLlave[$nFilaReg];	
						$consultaBase=obtenerRegistros($idFormulario,"1=1","",$filaReg["cadObj"]);
						$consultaBase=str_replace(", from"," from ",$consultaBase);
						$consultaBase.=" where idRegistro=".$filaReg["idRegistro"];

						$rRegistro=$con->obtenerFilas($consultaBase);
						
						$filaReg=mysql_fetch_assoc($rRegistro);	
						if(isset($filaReg[$filaOrden[0]]))
							$arrRegistros[$filaReg[$filaOrden[0]]."_".$ctAux]=$filaReg;
						else
							$arrRegistros[$filaReg["idRegistro"]."_".$ctAux]=$filaReg;
						$ctAux++;
					}
					

					
					
					$nReg=sizeof($arrRegistros);
					if($nReg>0)
					{
						foreach($arrRegistros as $key=>$filaReg)
						
						{
							$ingresarComoRol=false;
							if($vista==5)
							{
								$consulta="select responsable from ".$nTabla." where id_".$nTabla."=".$filaReg["idRegistro"];
								$responsable=$con->obtenerValor($consulta);
								if($responsable==$idUsuario)
									$ingresarComoRol=true;
							}
							else
								$ingresarComoRol=true;

								
							if(($nEtapa==null)||($relacion=="2")||($vista==5))
							{
								$elimina=false;
								$btnEliminar="";
								$consulta="select idEstado from ".$nTabla." where id_".$nTabla."=".$filaReg["idRegistro"];
								$numEtapa=$con->obtenerValor($consulta);	
								if(($relacion==1)&&($ingresarComoRol))
								{
									
									$consulta="select idGrupoAccion from 944_actoresProcesoEtapa a,947_actoresProcesosEtapasVSAcciones ae where 
									ae.idActorProcesoEtapa=a.idActorProcesoEtapa and a.tipoActor=1 and  a.actor in (".$roles.") and a.numEtapa=".$numEtapa." and a.idProceso=".$idProceso;
									$resAcciones=$con->obtenerFilas($consulta);
									while($filaAcciones=mysql_fetch_row($resAcciones))
									{
										switch($filaAcciones[0])
										{
											case "7":
												$elimina=true;
											break;
										}
									}
								}
								else
								{
									$consulta="select claveParticipacion from 246_autoresVSProyecto where idUsuario=".$idUsuario." and idFormulario=".$idFormularioBase." and idReferencia=".$filaReg["idRegistro"];
									$claveParticipacion=$con->obtenerValor($consulta);
									if($claveParticipacion!="")
									{
										$consulta="SELECT idProyectoVSParticipanteVSEtapa FROM 995_proyectosVSParticipantesVSEtapas WHERE 
													numEtapa=".$numEtapa." AND idParticipante=".$claveParticipacion." and idProyecto=".$idProceso;
										$idParticipanteProy=$con->obtenerValor($consulta);
										if($idParticipanteProy!="")
										{
											$permitirParticipante=true;
											$consulta="select idAccionParticipanteProcesoEtapa from 997_accionesParticipanteVSProcesoVSEtapa where idGrupoAccion=7 
														and idProyectoParticipante=".$idParticipanteProy;
											$idAccionP=$con->obtenerValor($consulta);
											if($idAccionP!="")
												$elimina=true;
										}
									}
								}
							}
							
							if($elimina)
							{
								$btnEliminar="&nbsp;&nbsp;<a href='javascript:eliminarRegistro(\"".base64_encode($filaReg["idRegistro"])."\",".$filaReg["idRegistro"].")'><img src=\"../images/delete.png\" alt='Eliminar registro' title=\"Eliminar registro\" /></a>";
							}
							
							$invPrincipal="";
							$tituloInvestigador="Participante";
							$idInvs="";
							$respSeguimiento="Responsable seguimiento";
							if($investigadores)
							{
								if(isset($investigadoresComp[4]))
									$tituloInvestigador=$investigadoresComp[4];
								if(isset($investigadoresComp[5]))
									$respSeguimiento=$investigadoresComp[5];
								$idPerfilPart=$investigadoresComp[0];
								
								$consulta="select concat(if(Prefijo is null,'',Prefijo),' ',if(Nom is null,'',Nom),' ',if(Paterno is null,'',Paterno),' ',
											if(Materno is null,'',Materno)) as registrado,i.idUsuario,ap.responsable,claveParticipacion from 802_identifica i, 246_autoresVSProyecto ap 
											where i.idUsuario<>703585 and i.idUsuario=ap.idUsuario and ap.idReferencia=".$filaReg["idRegistro"]." and ap.idFormulario=".$idFormulario." order by registrado";

								$resInv=$con->obtenerFilas($consulta);
								$invPrincipal="<table>";
								while($filaInv=mysql_fetch_row($resInv))
								{
									$cveParticipacion=$filaInv[3];
									$consulta="select descParticipacion from 953_elementosPerfilesParticipacionAutor where idElementoPerfilAutor='".$cveParticipacion."'";
									$participacion=$con->obtenerValor($consulta);
									
									if($filaInv[2]=="1")
									{
										$invPrincipal.="<tr><td><a href='javascript:verUsrNuevaPagina(\"".base64_encode($filaInv[1])."\")'>".$filaInv[0]."</a> <font color='red'><span title='".$respSeguimiento."' alt='".$respSeguimiento."' style='cursor:help'>*</font></td><td>&nbsp;&nbsp;[".$participacion."]</td></tr>";
										$idInvPrincipal=$filaInv[1];
									}
									else
										$invPrincipal.="<tr><td><a href='javascript:verUsrNuevaPagina(\"".base64_encode($filaInv[1])."\")'>".$filaInv[0]."</a></td><td>&nbsp;&nbsp;[".$participacion."]</td></tr>";
									
									
								}
								$invPrincipal.="</table>";
							}
							if($invPrincipal=="<table></table>")
							{
								$invPrincipal="No especificado";
							}
							;
							
							
						?>
						 <tr id="fila_<?php echo $filaReg["idRegistro"]?>">
							<td align="center">
								<table width="100%">
								<tr>
									<td align="left">
										<table>
										<tr>
											<td class="" width="30"><span class="corpo8_bold"><?php echo $ct.".-&nbsp;"?></span></td>
											<td width="210" class="esquinaFicha" align="left" ><span class="corpo8_bold"><?php echo $arrConfCampo[0]["titulo"]?></span></td><td width="440" class="valorFicha" align="<?php echo $arrConfCampo[0]["alineacion"]?>"><span class="speaker"><?php echo formatearValorFicha($filaReg[$arrConfCampo[0]["nombreCampo"]],$arrConfCampo[0]["tipoElemento"])?></span></td>
											<td class="valorFicha" >
											<?php
											
												if(!$sl)
												{
													if(($relacion=="1")&&($ingresarComoRol))
													{
														$consulta="select distinct(actor),idActorProcesoEtapa from 944_actoresProcesoEtapa where idProceso=".$idProceso." and tipoActor=1 and actor in (".$roles.") and numEtapa=".$numEtapa;
														$resVAct=$con->obtenerFilas($consulta);
														if($con->filasAfectadas>0)
														{
															while($filaVAct=mysql_fetch_row($resVAct))
															{
																$nRol=obtenerTituloRol($filaVAct[0]);
																if(!$investigadores)
																	$idActorE=$filaVAct[1];
																else
																{
																	$consulta="select responsable from ".$nTabla." where id_".$nTabla."=".$filaReg["idRegistro"];
																	$idResponsable=$con->obtenerValor($consulta);
																	if(($idUsuario==$idInvPrincipal)||($idUsuario==$idResponsable))
																		$idActorE=$filaVAct[1];
																	else
																		$idActorE=0;
																}
																
														?>
															<a href="javascript:verRegistro('<?php echo base64_encode($filaReg["idRegistro"])?>',<?php echo $filaReg["idRegistro"] ?>,'<?php echo base64_encode($idActorE)?>','<?php echo base64_encode($filaReg["idReferencia"])?>')">
															<img src="../images/magnifier.png" alt='Ver registro' title="Ver registro, Actor: <?php echo $nRol?>" />
															</a>
															<?php
															}
														}
														else
														{
															$nRol=obtenerTituloRol(str_replace("'","",$roles));
															?>
															<a href="javascript:verRegistro('<?php echo base64_encode($filaReg["idRegistro"])?>',<?php echo $filaReg["idRegistro"] ?>,'<?php echo base64_encode(0)?>','<?php echo base64_encode($filaReg["idReferencia"])?>')">
															<img src="../images/magnifier.png" alt='Ver registro' title="Ver registro, Actor: Invitado (Sólo lectura)" />
															</a>
															<!--<img src="../images/lock.png" title='Usted no cuenta con privilegios en esta etapa para ver el detalle del registro' alt="Usted no cuenta con privilegios en esta etapa para ver el detalle del registro" />-->
														<?php
														}
														echo $btnEliminar;
													}
													else
													{
		
															
														if($permitirParticipante)
														{												
																$consulta="select descParticipacion from 953_elementosPerfilesParticipacionAutor where idElementoPerfilAutor=".$claveParticipacion;
																$tParticipacion=$con->obtenerValor($consulta);
															?>
																<a href="javascript:verRegistroParticipante('<?php echo base64_encode($filaReg["idRegistro"])?>','<?php echo base64_encode($claveParticipacion)?>','<?php echo base64_encode($filaReg["idReferencia"])?>')">
																	<img src="../images/magnifier.png" alt='Ver registro' title="Ver registro, Participante: <?php echo $tParticipacion?>" />
																	</a>
														   <?php
														}
														else
														{
															?>
															<!--<a href="javascript:verRegistro('<?php echo base64_encode($filaReg["idRegistro"])?>',<?php echo $filaReg["idRegistro"] ?>,'<?php echo base64_encode(0)?>')">
																<img src="../images/magnifier.png" alt='Ver registro' title="Ver registro, Actor: Invitado (Sólo lectura)" />
															</a>-->
															<img src="../images/lock.png" title='Usted no cuenta con privilegios en esta etapa para ver el detalle del registro' alt="Usted no cuenta con privilegios en esta etapa para ver el detalle del registro" />
															<?php
															
														}
														echo $btnEliminar;
													}
												}
												else
												{
													if($idProcesoP!="")
													{
												?>
													<a href="javascript:verRegistro('<?php echo base64_encode($filaReg["idRegistro"])?>',<?php echo $filaReg["idRegistro"] ?>,'<?php echo base64_encode(0)?>','<?php echo base64_encode($filaReg["idReferencia"])?>')">
														<img src="../images/magnifier.png" alt='Ver registro' title="Ver registro, Actor: Invitado (Sólo lectura)" />
													</a>
												<?php
													}
													else
													{
												?>
													<a href="javascript:verRegistro('<?php echo base64_encode($filaReg["idRegistro"])?>',<?php echo $filaReg["idRegistro"] ?>,'<?php echo base64_encode(0)?>','<?php echo base64_encode($filaReg["idReferencia"])?>')">
														<img src="../images/magnifier.png" alt='Ver registro' title="Ver registro, Actor: Invitado (Sólo lectura)" />
													</a>
													 <!--Modificar <img src="../images/lock.png" title='Usted no cuenta con privilegios para ver el detalle del registro' alt="Usted no cuenta con privilegios para ver el detalle del registro" />	-->
												<?php
													}
												}
												?>
											</td>
										</tr>
										 <tr>
											<td colspan="3">
												<?php
													
													generarFichaElemento($arrConfCampo,$nTabla,$idFrmBase,$investigadores,$programaTrabajo,$anexos,$ademdum,$filaReg,$invPrincipal,$tituloInvestigador,$idProceso,$idReferencia);
												?>
											</td>
										</tr>
											<?php
												$tablaComp="";
												$consulta="SELECT tituloAccion,accion,ac.ayuda,icono,funcionValidacion,tipoFuncion,resultadoMuestra,condicionEval,ap.leyenda,ap.ayuda,ac.msgConfirm,ap.msgConfirm,idAccionComplementaria 
																FROM 945_accionesActorComplementarias ac,945_accionesComplementariasVSProceso ap 
																WHERE (ac.idAccionComplementaria=ap.idAccion 
																AND  numEtapa=".$numEtapa." AND idFormulario=".$idFrmBase.") OR(ac.idAccionComplementaria=ap.idAccion  AND numEtapa=-1 AND idFormulario=".$idFrmBase.")
																OR(ac.idAccionComplementaria=ap.idAccion  AND numEtapa=-1 AND idFormulario=-1)
																ORDER BY tituloAccion";
													$resAccionComp=$con->obtenerFilas($consulta);
													while($filaAccion=mysql_fetch_row($resAccionComp))
													{
														$mostrarAccionComp=true;
														if($filaAccion[4]!="")
														{
															switch($filaAccion[5])
															{
																case 0:
																	$res="";
																	eval('$res='.$filaAccion[4].'('.$idFrmBase.','.$filaReg["idRegistro"].','.$_SESSION["idUsr"].');if($res'.$filaAccion[7].$filaAccion[6].'){$mostrarAccionComp=true;}else{$mostrarAccionComp=false;}');
																break;
															}
														}
														if($mostrarAccionComp)
														{
															$titulo=$filaAccion[0];
															$ayuda=$filaAccion[2];
															$msConfirm=$filaAccion[10];
															
															if($filaAccion[8]!="")
															{
																$titulo=$filaAccion[8];
															}
															if($filaAccion[9]!="")
															{
																$ayuda=$filaAccion[9];
															}
															if($filaAccion[11]!="")
															{
																$msConfirm=$filaAccion[11];
															}
															$tablaComp.="       
																				<tr height='23' id='filaAccion_".$filaAccion[12]."_".$filaReg["idRegistro"]."'>
																					<td>
																						<img src='../images/".$filaAccion[3]."'>
																					</td>
																					<td width='5'>
																					</td>
																					<td>
																						<a title='".$ayuda."' alt='".$ayuda."' href='javascript:".$filaAccion[1]."(\"".bE($idFrmBase)."\",\"".bE($filaReg["idRegistro"])."\",\"".bE($_SESSION["idUsr"])."\",\"".bE($msConfirm)."\",\"".bE($filaAccion[12])."\")'>
																						<span class='letraFicha' title='".$ayuda."' alt='".$ayuda."'>".$titulo."</span></a> <img title='".$ayuda."' alt='".$ayuda."' src='../images/icon_info.gif' height='12' width='12'>
																						
																					</td>
																				</tr>
																		   ";
														}
														
													}
										if($tablaComp!="")
										{
											?>
											<tr>
												<td>
													
												</td>
												<td valign="top" class="etiquetaFicha">
													<span class="letraRojaSubrayada8">Acciones disponibles:</span>
												</td>
												<td class="valorFicha" colspan="2">
													<table>
													<?php
														echo $tablaComp;
													?>
													</table>
												</td>
											</tr>
										<?php
										}
										?>
                                
										</table>
							   		</td>
                                </tr>
                                 
                                 <tr id="filaComp_<?php echo $filaReg["idRegistro"]?>">
                                    <td>
                                    <br /><br />
                                    <input type="hidden" value="<?php echo $idFormulario?>" id="idFormulario" />
                                    </td>
                                 </tr>
                                 </table>  
                   			</td>

                        </tr> 
                      <?php	
						$ct++;
						}
					
					}
					
					
				}
				
					 	if($mostrarVincularRegistro)
						{
					?>
                                <tr>
                                        <td align="left">
                                        <br />
                                            <span class="letraFichaRespuesta"><?php echo $lblNoEncuentra?> de click <a href="javascript:buscarProducto('<?php echo bE($idProceso)?>')"><font color="red"><b>AQUÎ</b></font></a></span>
                                        </td>
                                    </tr>
                    <?php	
						}
					 
					?>
		                                      		
		</table>
        <script>
		<?php
			echo $arrPagNuevo;
		?>
		</script>
		<?php        
}

function generarTablaElementosObjetoRevisor($idProceso,$nEtapa,$titulo="Registros",$idCategoria=null,$pagina=1,$idActorProcesoEtapa="",$idReferencia="-1")								//OK+
{
	global $con;
	$numEtapa=$nEtapa;
	$tituloListado="";
	$idFrmBase=obtenerFormularioBase($idProceso);
	$consulta="select numRegPag from 909_configuracionTablaFormularios where idFormulario=".$idFrmBase;
	$nElemento=$con->obtenerValor($consulta);
	$consulta="select p.idTipoProceso from 4001_procesos p where p.idProceso=".$idProceso;
	$tipoProceso=$con->obtenerValor($consulta);
	if($numEtapa!=null)
	{
		$consulta="select nombreEtapa from 4037_etapas where idProceso=".$idProceso." and numEtapa= ".$numEtapa;
		$nEtapa=$con->obtenerValor($consulta);
		$tituloListado=($titulo).' (ETAPA: <span class="letraRojaSubrayada8">'.removerCerosDerecha($numEtapa).".- ".$nEtapa."</span>)";
	}
	else
	{
		if($idCategoria!=null)
		{
			if($idCategoria!="NULL")
			{
				$consulta="select nomCategoria from 4037_categoriasEtapa where idCategoria=".$idCategoria;
				$nCategoria=$con->obtenerValor($consulta);
			}
			else
				$nCategoria="SIN CATEGORÍA";
			$tituloListado=($titulo)." (CATEGORÍA:&nbsp;<span class='letraRojaSubrayada8'>".strtoupper($nCategoria)."</span>)";
		}
		else
			$tituloListado=($titulo)." (GENERAL)";
		$nEtapa="";
	}
	
	$ct=($nElemento*($pagina-1))+1;
	$idUsuario=$_SESSION["idUsr"];
	$roles=$_SESSION["idRol"];
	?>
	
		<table width="750"  style="padding:7px">
			<tr height="23">
				<td align="left" style="background-image:url(../images/fondo_barra_mc_azul.gif)">
				<span class="speaker"><?php echo $tituloListado?></span>
				</td>
			</tr>
			<tr>
				<td>
				</td>
			</tr>
					<?php
					$consulta="select nombreTabla,idFormulario from 900_formularios where idProceso=".$idProceso." and formularioBase=1";
					$filaFrm=$con->obtenerPrimeraFila($consulta);
					$nTabla=$filaFrm[0];
					$idFormulario=$filaFrm[1];
					$pagNuevo="../modeloPerfiles/registroFormulario.php";
					$pagModificar="../modeloPerfiles/registroFormulario.php";
					$pagVer="../modeloPerfiles/verFichaFormulario.php";
					$query="select pagVista,accion from 936_vistasProcesos where tipoProceso=".$tipoProceso;
					$resPagAccion=$con->obtenerFilas($query);
					$arrPagNuevo="var arrPagNuevo=new Array();";
					while($filaAccion=mysql_fetch_row($resPagAccion))
					{
						$paginaRef="";
						switch($filaAccion[1])
						{
							case "0"://Nuevo
								$paginaRef=$filaAccion[0];
							break;
							case "1"://Modificar
								$paginaRef=$filaAccion[0];
							break;
							case "2"://Consultar/Ver
								$paginaRef=$filaAccion[0];
							break;
						}
						$arrPagNuevo.="arrPagNuevo[".$filaAccion[1]."]='".$paginaRef."';";
					}
					$elimina=false;
					
					$btnEliminar="";										
					$consulta="select complementario from 949_actoresVSAccionesProceso where actor in(".$roles.") and idAccion=9 and idProceso=".$idProceso." order by complementario";
					$complementario=$con->obtenerValor($consulta);
					
					$consulta="select idActorProcesoEtapa,estado from 955_revisoresProceso where estado in (1) and idActorProcesoEtapa=".$idActorProcesoEtapa." and idUsuarioRevisor=".$idUsuario;
					$filaRevisor=$con->obtenerPrimeraFila($consulta);
					$estado="";
					switch($filaRevisor[1])
					{
						
						case 1:
							$estado="<font color='green'>En espera de dictamen por parte de revisor</font>";
						break;
						case 2:
							$estado="<font color='red'>Dictaminado</font>";
						break;

					}
				
				$condWhere=" id_".$nTabla." in (select idReferencia from 955_revisoresProceso where idActorProcesoEtapa=".$idActorProcesoEtapa." and idUsuarioRevisor=".$idUsuario." and estado in (1))";
				if($numEtapa!=null)
					$condWhere=" idEstado=".$numEtapa." and id_".$nTabla." in (select idReferencia from 955_revisoresProceso where idActorProcesoEtapa=".$idActorProcesoEtapa." and idUsuarioRevisor=".$idUsuario." and estado in (1))";
				else
					if($idCategoria!=null)
						if($idCategoria!="NULL")
							$condWhere=" idEstado in (select numEtapa from 4037_etapas where idCategoria=".$idCategoria.") and id_".$nTabla." in (select idReferencia from 955_revisoresProceso where idActorProcesoEtapa=".$idActorProcesoEtapa." and idUsuarioRevisor=".$idUsuario." and estado in(1))";
						else
							$condWhere=" idEstado in (select numEtapa from 4037_etapas where idCategoria is ".$idCategoria.") and id_".$nTabla." in (select idReferencia from 955_revisoresProceso where idActorProcesoEtapa=".$idActorProcesoEtapa." and idUsuarioRevisor=".$idUsuario." and estado in(1))";
				
				/*$consulta=obtenerRegistros($idFormulario,$condWhere," limit ".($nElemento*($pagina-1)).",".$nElemento);
				$consulta=str_replace(", from"," from ",$consulta);*/
				if($condWhere!="")
					$condWhere=" where ".$condWhere;
				$consulta="select id_".$nTabla." as idRegistro,idReferencia,idEstado from ".$nTabla." ".$condWhere;
				$consulta.=" limit ".($nElemento*($pagina-1)).",".$nElemento;

				$resReg=$con->obtenerFilas($consulta);
				$consulta=str_replace(" limit ".($nElemento*($pagina-1)).",".$nElemento,"",$consulta);
				$con->obtenerFilas($consulta);
				$nRegistros=$con->filasAfectadas;
				$nPaginas= intval($nRegistros / $nElemento);
				$residuo=$nRegistros-($nPaginas*$nElemento);
				if($residuo>0)
					$nPaginas++;
				if($nRegistros==0)
					$nPaginas=1;					
				?>
				<tr>
					<td align="left">
						<table width="600">
						<tr>
						<td>
						<span class="corpo8_bold">Página:</span>&nbsp;&nbsp;
						<?php
							$cadPag="";
							for($x=1;$x<=$nPaginas;$x++)
							{
								if($cadPag=="")
									$cadPag="<a href='javascript:irPagina(".$x.")'>".$x."</a>";
								else
									$cadPag.=", "."<a href='javascript:irPagina(".$x.")'>".$x."</a>";
								
							}
							echo $cadPag;
						?>
						</td>
						</tr>
						</table>
                        <br /><br />
					</td>
				</tr>
                <?php

					if($nRegistros==0)
					{
				?>
                <tr>
                	<TD align="center">
                    	<span class="copyrigthSinPadding">Actualmente no existen registros que evaluar</span>
                    </TD>
                </tr>
               <?php		
					}
					else
					{
						$datosComp="";
						$arrConfCampo=obtenerCamposConfiguracion($idFrmBase,$datosComp);
						if(!$arrConfCampo)
							return;
						$nFilasConf=sizeof($arrConfCampo);
						$anexos=false;
						$investigadores=false;
						$investigadoresComp="";
						$programaTrabajo=false;
						$ademdum=false;
						obtenerModulosDisponibles($idProceso,$investigadores,$anexos,$programaTrabajo,$ademdum,$datosComp,$investigadoresComp);
						$idInvPrincipal="0";
						
						$arrRegistros=array();
						$consulta="SELECT campoOrden,direccionOrden FROM 909_configuracionTablaFormularios WHERE idFormulario=".$idFormulario;
						$filaOrden=$con->obtenerPrimeraFila($consulta);
						$ctAux=0;
						$consulta="SELECT funcionExclusion FROM 203_funcionesExclusionRegistros WHERE idProceso=".$idProceso." AND tipoActor IN (1,0)";
						$funcionExc=$con->obtenerValor($consulta);
						while($filaReg=mysql_fetch_assoc($resReg))
						{
							$considerar=true;
							if($funcionExc!="")
							{
								eval('$considerar='.$funcionExc."(".$idFormulario.",".$filaReg["idRegistro"].");");
								
							}
							
							if($considerar)
							{
								$numEtapaPadre=obtenerEtapaProcesoActual($filaReg["idRegistro"],$idReferencia,$idFormulario);
								$idUsuarioResp=obtenerResponsableProcesoActual($filaReg["idRegistro"],$idReferencia,$idFormulario);
								$cadObj='{"p16":{"p1":"'.$idFormulario.'","p2":"'.$idProceso.'","p3":"'.$filaReg["idRegistro"].'","p4":"'.$numEtapaPadre.'","p5":"'.$idReferencia.'","p6":"'.$idUsuarioResp.'"}}';
								$consultaBase=obtenerRegistros($idFormulario,"1=1","",$cadObj);
								$consultaBase=str_replace(", from"," from ",$consultaBase);
								$consultaBase.=" where idRegistro=".$filaReg["idRegistro"];
								$rRegistro=$con->obtenerFilas($consultaBase);
								$filaReg=mysql_fetch_assoc($rRegistro);	
								
								if(isset($filaReg[$filaOrden[0]]))
									$arrRegistros[$filaReg[$filaOrden[0]]."_".$ctAux]=$filaReg;
								else
									$arrRegistros[$filaReg["idRegistro"]."_".$ctAux]=$filaReg;
								$ctAux++;
							}
							
						}
						if($filaOrden[1]=='ASC')
							ksort($arrRegistros);
						else
							krsort($arrRegistros);
						
							
						$nReg=sizeof($arrRegistros);
						if($nReg>0)
						{
							foreach($arrRegistros as $key=>$filaReg)
							{
								if(($elimina))
								{
									$btnEliminar="&nbsp;&nbsp;<a href='javascript:eliminarRegistro(\"".base64_encode($filaReg["idRegistro"])."\",".$filaReg["idRegistro"].")'><img src=\"../images/delete.png\" alt='Eliminar registro' title=\"Eliminar registro\" /></a>";
								}
								
								$invPrincipal="";
								$tituloInvestigador="Participante";
								$idInvs="";
								$respSeguimiento="Responsable seguimiento";
								if($investigadores)
								{
									if(isset($investigadoresComp[4]))
										$tituloInvestigador=$investigadoresComp[4];
									if(isset($investigadoresComp[5]))
										$respSeguimiento=$investigadoresComp[5];
									$idPerfilPart=$investigadoresComp[0];
									
									$idPrincipal=$investigadoresComp[1];
									$consulta="select concat(if(Prefijo is null,'',Prefijo),' ',if(Nom is null,'',Nom),' ',if(Paterno is null,'',Paterno),' ',
												if(Materno is null,'',Materno)) as registrado,i.idUsuario,ap.responsable,claveParticipacion from 802_identifica i, 246_autoresVSProyecto ap 
												where i.idUsuario<>703585 and i.idUsuario=ap.idUsuario and ap.idReferencia=".$filaReg["idRegistro"]." and ap.idFormulario=".$idFormulario." order by registrado";
									$resInv=$con->obtenerFilas($consulta);
									$invPrincipal="<table>";
									while($filaInv=mysql_fetch_row($resInv))
									{
										$cveParticipacion=$filaInv[3];
										$consulta="select descParticipacion from 953_elementosPerfilesParticipacionAutor where idElementoPerfilAutor='".$cveParticipacion."'";
										$participacion=$con->obtenerValor($consulta);
										
										if($filaInv[2]=="1")
											$invPrincipal.="<tr><td><a href='javascript:verUsrNuevaPagina(\"".base64_encode($filaInv[1])."\")'>".$filaInv[0]."</a> <font color='red'><span title='".$respSeguimiento."' alt='".$respSeguimiento."' style='cursor:help'>*</font></td><td>&nbsp;&nbsp;[".$participacion."]</td></tr>";
										else
											$invPrincipal.="<tr><td><a href='javascript:verUsrNuevaPagina(\"".base64_encode($filaInv[1])."\")'>".$filaInv[0]."</a></td><td>&nbsp;&nbsp;[".$participacion."]</td></tr>";
										
										
									}
									$invPrincipal.="</table>";
								}
								if($invPrincipal=="<table></table>")
								{
									$invPrincipal="No especificado";
								}
								
								
							?>
							 <tr id="fila_<?php echo $filaReg["idRegistro"]?>">
								<td align="center">
							
									<table width="850">
											<tr>
												<td class="" width="30"><span class="corpo8_bold"><?php echo $ct.".-&nbsp;"?></span></td>
												<td width="180" class="esquinaFicha" align="left"><span class="corpo8_bold"><?php echo $arrConfCampo[0]["titulo"]?></span></td>
                                                <td width="440" class="valorFicha" align="<?php echo $arrConfCampo[0]["alineacion"]?>"><span class="speaker"><?php echo formatearValorFicha($filaReg[$arrConfCampo[0]["nombreCampo"]],$arrConfCampo[0]["tipoElemento"])?></span></td>
												<td class="valorFicha" width="50">
												<?php
												  $consulta="Select actor,tipoActor from 944_actoresProcesoEtapa where idActorProcesoEtapa=".$idActorProcesoEtapa;
												  $fActorC=$con->obtenerPrimeraFila($consulta);
												  $tipoActor=$fActorC[1];
												  $codActor=$fActorC[0];
												  if($tipoActor==1)
													  $nActor=obtenerTituloRol($codActor);
												  else
												  {
													  $consulta="select nombreComite from 234_proyectosVSComitesVSEtapas  ce,2006_comites c where c.idComite=ce.idComite and  idProyectoVSComiteVSEtapa=".$codActor;
													  $nActor=$con->obtenerValor($consulta);
												  }
											  ?>
												  <a href="javascript:verRegistro('<?php echo base64_encode($filaReg["idRegistro"])?>',<?php echo $filaReg["idRegistro"] ?>,'<?php echo base64_encode("-".$idActorProcesoEtapa)?>','<?php echo base64_encode($filaReg["idReferencia"])?>')">
												  <img src="../images/magnifier.png" alt='Ver registro' title="Ver registro como revisor de actor: <?php echo $nActor?>" />
												  </a>
													<?php
													
													echo $btnEliminar;
													
													?>
												</td>
											</tr>
											<tr>
												<td></td>
												<td  class="esquinaFicha" align="left"><span class="corpo8_bold">Estado revisión:</span></td>
												<td class="valorFicha" align="left" colspan="2"><span class="speaker"><?php echo $estado ?></span></td>
												
										   </tr>
											<?php
												generarFichaElemento($arrConfCampo,$nTabla,$idFrmBase,$investigadores,$programaTrabajo,$anexos,$ademdum,$filaReg,$invPrincipal,$tituloInvestigador,$idProceso,$idReferencia);
											?>
									</table>
								</td>
							 </tr>
							 <tr id="filaComp_<?php echo $filaReg["idRegistro"]?>">
								<td>
								<br /><br />
								<input type="hidden" value="<?php echo $idFormulario?>" id="idFormulario" />
								</td>
							 </tr>
							
							<?php	
								$ct++;
							}
						}
					}
					
					
					?>
		</table>
        <script>
		<?php
			echo $arrPagNuevo;
		?>
		</script>
		<?php        
}

function generarTablaElementosObjetoUsuario($idProceso,$nEtapa,$titulo="Registros",$idCategoria=null,$pagina=1,$actor="",$idReferencia="-1")								//OK+
{
	global $con;
	$numEtapa=$nEtapa;
	$idFrmBase=obtenerFormularioBase($idProceso);
	$consulta="select numRegPag from 909_configuracionTablaFormularios where idFormulario=".$idFrmBase;
	$nElemento=$con->obtenerValor($consulta);
	$consulta="select p.idTipoProceso from 4001_procesos p where p.idProceso=".$idProceso;
	$tipoProceso=$con->obtenerValor($consulta);
	if($numEtapa!=null)
	{
		$consulta="select nombreEtapa from 4037_etapas where idProceso=".$idProceso." and numEtapa= ".$numEtapa;
		$nEtapa=$con->obtenerValor($consulta);
		$tituloListado= strtoupper($titulo).' (ETAPA: <span class="letraRojaSubrayada8">'.removerCerosDerecha($numEtapa).".- ".$nEtapa."</span>)";
	}
	else
	{
		if($idCategoria!=null)
		{
			if($idCategoria!="NULL")
			{
				$consulta="select nomCategoria from 4037_categoriasEtapa where idCategoria=".$idCategoria;
				$nCategoria=$con->obtenerValor($consulta);
			}
			else
				$nCategoria="SIN CATEGORÍA";
			$tituloListado=strtoupper($titulo)." (CATEGORÍA:&nbsp;<span class='letraRojaSubrayada8'>".strtoupper($nCategoria)."</span>)";
		}
		else
			$tituloListado=strtoupper($titulo)." (GENERAL)";
		$nEtapa="";
	}
	$ct=($nElemento*($pagina-1))+1;
	$idUsuario=$_SESSION["idUsr"];
	if($actor=="")
		$roles=$_SESSION["idRol"];
	else
		$roles=$actor;
	?>
	
		<table width="750" style="padding:7px">
			<tr height="23">
				<td align="left" style="background-image:url(../images/fondo_barra_mc_azul.gif)">
				<span class="speaker"><?php echo $tituloListado ?></span>
				</td>
			</tr>
			<tr>
				<td>
				</td>
			</tr>
					<?php
					$consulta="select nombreTabla,idFormulario from 900_formularios where idProceso=".$idProceso." and formularioBase=1";
					$filaFrm=$con->obtenerPrimeraFila($consulta);
					$nTabla=$filaFrm[0];
					$idFormulario=$filaFrm[1];
					$pagNuevo="../modeloPerfiles/registroFormulario.php";
					$pagModificar="../modeloPerfiles/registroFormulario.php";
					$pagVer="../modeloPerfiles/verFichaFormulario.php";
					$query="select pagVista,accion from 936_vistasProcesos where tipoProceso=".$tipoProceso;
					$resPagAccion=$con->obtenerFilas($query);
					$arrPagNuevo="var arrPagNuevo=new Array();";
					while($filaAccion=mysql_fetch_row($resPagAccion))
					{
						$paginaRef="";
						switch($filaAccion[1])
						{
							case "0"://Nuevo
								$paginaRef=$filaAccion[0];
							break;
							case "1"://Modificar
								$paginaRef=$filaAccion[0];
							break;
							case "2"://Consultar/Ver
								$paginaRef=$filaAccion[0];
							break;
						}
						$arrPagNuevo.="arrPagNuevo[".$filaAccion[1]."]='".$paginaRef."';";
					}
					
					$elimina=false;
					if($numEtapa!=null)
					{
						$consulta="select idGrupoAccion from 944_actoresProcesoEtapa a,947_actoresProcesosEtapasVSAcciones ae where ae.idActorProcesoEtapa=a.idActorProcesoEtapa and a.tipoActor=1 and  a.actor in (".$roles.") and a.numEtapa=".$numEtapa." and a.idProceso=".$idProceso;
						$resAcciones=$con->obtenerFilas($consulta);
						while($filaAcciones=mysql_fetch_row($resAcciones))
						{
							switch($filaAcciones[0])
							{
								case "7":
									$elimina=true;
								break;
							}
						}
					}
					$btnEliminar="";
					$condWhere=" idEstado in (select numEtapa from 4037_etapas where idProceso=".$idProceso." and numEtapa in 
								(select numEtapa from 944_actoresProcesoEtapa where actor in (".$roles.") and tipoActor=1 and idProceso=".$idProceso." ))";

					if($numEtapa!=null)
						$condWhere=" idEstado=".$numEtapa;
					else
						if($idCategoria!=null)
						{
							if($idCategoria!="NULL")
								$condWhere=" idEstado in (select numEtapa from 4037_etapas where idCategoria=".$idCategoria." and numEtapa in 
											(select numEtapa from 944_actoresProcesoEtapa where actor in (".$roles.") and tipoActor=1 and idProceso=".$idProceso." ))";
							else
								$condWhere=" idEstado in (select numEtapa from 4037_etapas where idCategoria is ".$idCategoria." and idProceso=".$idProceso." and numEtapa in 
											(select numEtapa from 944_actoresProcesoEtapa where actor in (".$roles.") and tipoActor=1 and idProceso=".$idProceso." ))";
						}
						
					$consulta="SELECT * FROM 949_actoresVSAccionesProceso WHERE actor IN (".$roles.") and idAccion=9 and idProceso=".$idProceso;

					$fila=$con->obtenerPrimeraFila($consulta);
					if($fila)
					{
						$aux="";
						switch($fila[3])
						{
							case 1:  //Todos
							break;
							case 2: // En su institucion
								$aux=" codigoInstitucion='".$_SESSION["codigoInstitucion"]."'";
							break;
							case 3: // registados en su departamento y subdepartamentos
								$aux=" (codigoUnidad = '".$_SESSION["codigoUnidad"]."' or codigoUnidad like '".$_SESSION["codigoUnidad"]."%')";
							break;
							case 4: //registados en su departamento
								$aux=" codigoUnidad='".$_SESSION["codigoUnidad"]."'";
							break;
							case 5: //Solo en lo que el participa
							break;
						}
						if($aux!="")
						{
							if($condWhere!="")
								$condWhere.=" and ".$aux;
							else
								$condWhere=$aux;
						}
					}
					
					if($_SESSION["idUsr"]==2947)
					{
						$queryAux="SELECT idOrganigrama FROM 817_organigrama WHERE 
									(codigoUnidad LIKE '0002%' OR codigoUnidad LIKE '0006%' OR codigoUnidad LIKE '0008%' OR codigoUnidad LIKE '0009%' or codigoUnidad like '0010%')
									AND STATUS=1";
						$listOrganigrama =$con->obtenerListaValores($queryAux);
						if($listOrganigrama=="")
							$listOrganigrama=-1;
						if($condWhere=="")
							$condWhere=" and CentroCosto not in ('0009','0011') and Sede in (".$listOrganigrama.")";
						else
							$condWhere.=" and CentroCosto not in ('0009','0011') and Sede in (".$listOrganigrama.")";
					}
					
				    if($condWhere!="")
						$condWhere=" where ".$condWhere;
				 	$consulta="select id_".$nTabla." as idRegistro,idReferencia,idEstado from ".$nTabla." ".$condWhere;
					$consulta.=" limit ".($nElemento*($pagina-1)).",".$nElemento;

				  	$resReg=$con->obtenerFilas($consulta);
					$consulta=str_replace(" limit ".($nElemento*($pagina-1)).",".$nElemento,"",$consulta);
					$con->obtenerFilas($consulta);
					$nRegistros=$con->filasAfectadas;
					$nPaginas= intval($nRegistros / $nElemento);
					$residuo=$nRegistros-($nPaginas*$nElemento);
					if($residuo>0)
						$nPaginas++;
					if($nPaginas==0)
						$nPaginas=1;
				?>
				<tr>
					<td align="left">
						<table width="600">
						<tr>
						<td>
						<span class="corpo8_bold">Página:</span>&nbsp;&nbsp;
						<?php
							$cadPag="";
							for($x=1;$x<=$nPaginas;$x++)
							{
								if($cadPag=="")
									$cadPag="<a href='javascript:irPagina(".$x.")'>".$x."</a>";
								else
									$cadPag.=", "."<a href='javascript:irPagina(".$x.")'>".$x."</a>";
								
							}
							echo $cadPag;
						?>
						</td>
						</tr>
						</table>
                        <br /><br />
					</td>
				</tr>
                <?php
                if($nRegistros==0)
				{
				?>
                    <tr>
                        <TD align="center">
                            <span class="copyrigthSinPadding">Actualmente no existen registros que evaluar</span>
                        </TD>
                    </tr>
               <?php		
				}
				else
				{
					
					$datosComp="";
					$arrConfCampo=obtenerCamposConfiguracion($idFrmBase,$datosComp);
					if(!$arrConfCampo)
						return;
					$nFilasConf=sizeof($arrConfCampo);
					$anexos=false;
					$investigadores=false;
					$investigadoresComp="";
					$programaTrabajo=false;
					$ademdum=false;
					obtenerModulosDisponibles($idProceso,$investigadores,$anexos,$programaTrabajo,$ademdum,$datosComp,$investigadoresComp);
					$idInvPrincipal="0";
					$arrRegistros=array();
					$consulta="SELECT campoOrden,direccionOrden FROM 909_configuracionTablaFormularios WHERE idFormulario=".$idFormulario;
					$filaOrden=$con->obtenerPrimeraFila($consulta);
					$ctAux=0;
					while($filaReg=mysql_fetch_assoc($resReg))
					{
						$numEtapaPadre=obtenerEtapaProcesoActual($filaReg["idRegistro"],$idReferencia,$idFormulario);
						$idUsuarioResp=obtenerResponsableProcesoActual($filaReg["idRegistro"],$idReferencia,$idFormulario);
						$cadObj='{"p16":{"p1":"'.$idFormulario.'","p2":"'.$idProceso.'","p3":"'.$filaReg["idRegistro"].'","p4":"'.$numEtapaPadre.'","p5":"'.$idReferencia.'","p6":"'.$idUsuarioResp.'"}}';
						$consultaBase=obtenerRegistros($idFormulario,"1=1","",$cadObj);
						$consultaBase=str_replace(", from"," from ",$consultaBase);
						$consultaBase.=" where idRegistro=".$filaReg["idRegistro"];
						$rRegistro=$con->obtenerFilas($consultaBase);
						$filaReg=mysql_fetch_assoc($rRegistro);	
						if(isset($filaReg[$filaOrden[0]]))
							$arrRegistros[$filaReg[$filaOrden[0]]."_".$ctAux]=$filaReg;
						else
							$arrRegistros[$filaReg["idRegistro"]."_".$ctAux]=$filaReg;
						$ctAux++;
						
					}
					if($filaOrden[1]=='ASC')
						ksort($arrRegistros);
					else
						krsort($arrRegistros);
					
						
					$nReg=sizeof($arrRegistros);
					if($nReg>0)
					{
						foreach($arrRegistros as $key=>$filaReg)
						{
							if($nEtapa==null)
							{
								$elimina=false;
								$btnEliminar="";
								$consulta="select idEstado from ".$nTabla." where id_".$nTabla."=".$filaReg["idRegistro"];
								$numEtapa=$con->obtenerValor($consulta);	
								$consulta="select idGrupoAccion from 944_actoresProcesoEtapa a,947_actoresProcesosEtapasVSAcciones ae where ae.idActorProcesoEtapa=a.idActorProcesoEtapa and a.tipoActor=1 and  a.actor in (".$roles.") and a.numEtapa=".$numEtapa." and a.idProceso=".$idProceso;
								$resAcciones=$con->obtenerFilas($consulta);
								while($filaAcciones=mysql_fetch_row($resAcciones))
								{
									switch($filaAcciones[0])
									{
										
										case "7":
											$elimina=true;
										break;
									}
								}
							}
							if(($elimina))
							{
								$btnEliminar="&nbsp;&nbsp;<a href='javascript:eliminarRegistro(\"".base64_encode($filaReg["idRegistro"])."\",".$filaReg["idRegistro"].")'><img src=\"../images/delete.png\" alt='Eliminar registro' title=\"Eliminar registro\" /></a>";
							}
							
							$invPrincipal="";
							$tituloInvestigador="Participante";
							$idInvs="";
							$respSeguimiento="Responsable seguimiento";
							if($investigadores)
							{
								if(isset($investigadoresComp[4]))
									$tituloInvestigador=$investigadoresComp[4];
								if(isset($investigadoresComp[5]))
									$respSeguimiento=$investigadoresComp[5];
								$idPerfilPart=$investigadoresComp[0];
								
								$idPrincipal=$investigadoresComp[1];
								$consulta="select concat(if(Prefijo is null,'',Prefijo),' ',if(Nom is null,'',Nom),' ',if(Paterno is null,'',Paterno),' ',
											if(Materno is null,'',Materno)) as registrado,i.idUsuario,ap.responsable,claveParticipacion from 802_identifica i, 246_autoresVSProyecto ap 
											where i.idUsuario<>703585 and i.idUsuario=ap.idUsuario and ap.idReferencia=".$filaReg["idRegistro"]." and ap.idFormulario=".$idFormulario." order by registrado";

								$resInv=$con->obtenerFilas($consulta);
								$invPrincipal="<table>";
								while($filaInv=mysql_fetch_row($resInv))
								{
									$cveParticipacion=$filaInv[3];
									$consulta="select descParticipacion from 953_elementosPerfilesParticipacionAutor where idElementoPerfilAutor='".$cveParticipacion."'";

									$participacion=$con->obtenerValor($consulta);
									
									if($filaInv[2]=="1")
										$invPrincipal.="<tr><td><a href='javascript:verUsrNuevaPagina(\"".base64_encode($filaInv[1])."\")'>".$filaInv[0]."</a> <font color='red'><span title='".$respSeguimiento."' alt='".$respSeguimiento."' style='cursor:help'>*</font></td><td>&nbsp;&nbsp;[".$participacion."]</td></tr>";
									else
										$invPrincipal.="<tr><td><a href='javascript:verUsrNuevaPagina(\"".base64_encode($filaInv[1])."\")'>".$filaInv[0]."</a></td><td>&nbsp;&nbsp;[".$participacion."]</td></tr>";
									
									
								}
								$invPrincipal.="</table>";
							}
							if($invPrincipal=="<table></table>")
							{
								$invPrincipal="No especificado";
							}
							
							
							
						?>
						 <tr id="fila_<?php echo $filaReg["idRegistro"]?>">
							<td align="center">
								<table width="680">
										<tr>
											<td class="" width="30"><span class="corpo8_bold"><?php echo $ct.".-&nbsp;"?></span></td>
											<td width="180" class="esquinaFicha" align="left"><span class="corpo8_bold"><?php echo $arrConfCampo[0]["titulo"]?></span></td>
                                            <td width="440" class="valorFicha" align="<?php echo $arrConfCampo[0]["alineacion"]?>"><span class="speaker"><?php echo formatearValorFicha($filaReg[$arrConfCampo[0]["nombreCampo"]],$arrConfCampo[0]["tipoElemento"])?></span></td>
											<td class="valorFicha" width="30">
											
											<?php
												$consulta="select distinct(actor),idActorProcesoEtapa from 944_actoresProcesoEtapa where idProceso=".$idProceso." and tipoActor=1 and actor in (".$roles.") and numEtapa=".$numEtapa;
												$resVAct=$con->obtenerFilas($consulta);
												if($con->filasAfectadas>0)
												{
													while($filaVAct=mysql_fetch_row($resVAct))
													{
														$nRol=obtenerTituloRol($filaVAct[0]);
												?>
													<a href="javascript:verRegistro('<?php echo base64_encode($filaReg["idRegistro"])?>',<?php echo $filaReg["idRegistro"] ?>,'<?php echo base64_encode($filaVAct[1])?>','<?php echo base64_encode($filaReg["idReferencia"])?>')">
													<img src="../images/magnifier.png" alt='Ver registro' title="Ver registro, Actor: <?php echo $nRol?>" />
													</a>
													<?php
													}
												}
												else
												{?>
													<img src="../images/lock.png" title='Usted no cuenta con privilegios en esta etapa para ver el detalle del registro' alt="Usted no cuenta con privilegios en esta etapa para ver el detalle del registro" />
												<?php
												}
													echo $btnEliminar;
												?>
											</td>
										</tr>
										
										<?php
											generarFichaElemento($arrConfCampo,$nTabla,$idFrmBase,$investigadores,$programaTrabajo,$anexos,$ademdum,$filaReg,$invPrincipal,$tituloInvestigador,$idProceso,$idReferencia);
										?>
								</table>
							</td>
						 </tr>
						 <tr id="filaComp_<?php echo $filaReg["idRegistro"]?>">
							<td>
							<br /><br />
							<input type="hidden" value="<?php echo $idFormulario?>" id="idFormulario" />
							</td>
						 </tr>
						
						<?php	
							$ct++;
						}
					}
				}
					?>
		</table>
        <script>
		<?php
			echo $arrPagNuevo;
		?>
		</script>
		<?php        
}

function generarTablaElementosObjetoComite($idProceso,$nEtapa,$idComite,$titulo="Registros",$idCategoria=null,$pagina=1,$listRegistros="-1",$idReferencia="-1")						//OK+
{
	global $con;
	$numEtapa=$nEtapa;
	$tituloListado="";
	$idFrmBase=obtenerFormularioBase($idProceso);
	$consulta="select numRegPag from 909_configuracionTablaFormularios where idFormulario=".$idFrmBase;
	$nElemento=$con->obtenerValor($consulta);
	$consulta="select p.idTipoProceso from 4001_procesos p where p.idProceso=".$idProceso;
	$tipoProceso=$con->obtenerValor($consulta);
	if($numEtapa!=null)
	{
		$consulta="select nombreEtapa from 4037_etapas where idProceso=".$idProceso." and numEtapa= ".$numEtapa;
		$nEtapa=$con->obtenerValor($consulta);
		$tituloListado= strtoupper($titulo).' (ETAPA: <span class="letraRojaSubrayada8">'.removerCerosDerecha($numEtapa).".- ".$nEtapa."</span>)";
	}
	else
	{
		if($idCategoria!=null)
		{
			if($idCategoria!="NULL")
			{
				$consulta="select nomCategoria from 4037_categoriasEtapa where idCategoria=".$idCategoria;
				$nCategoria=$con->obtenerValor($consulta);
			}
			else
				$nCategoria="SIN CATEGORÍA";
			$tituloListado=strtoupper($titulo)." (CATEGORÍA:&nbsp;<span class='letraRojaSubrayada8'>".strtoupper($nCategoria)."</span>)";
		}
		else
			$tituloListado=strtoupper($titulo)." (GENERAL)";
		$nEtapa="";
	}
	
	$ct=($nElemento*($pagina-1))+1;
	
	$roles=$_SESSION["idRol"];
	?>
	
		<table width="750"  style="padding:7px">
			<tr height="23">
				<td align="left" style="background-image:url(../images/fondo_barra_mc_azul.gif)">
				<span class="speaker"><?php echo $tituloListado ?></span></span>
				</td>
			</tr>
			<tr>
				<td>
				</td>
			</tr>
					<?php
					$consulta="select nombreTabla,idFormulario from 900_formularios where idProceso=".$idProceso." and formularioBase=1";
					$filaFrm=$con->obtenerPrimeraFila($consulta);
					$nTabla=$filaFrm[0];
					$idFormulario=$filaFrm[1];
					$pagNuevo="../modeloPerfiles/registroFormulario.php";
					$pagModificar="../modeloPerfiles/registroFormulario.php";
					$pagVer="../modeloPerfiles/verFichaFormulario.php";
					$query="select pagVista,accion from 936_vistasProcesos where tipoProceso=".$tipoProceso;
					$resPagAccion=$con->obtenerFilas($query);
					$arrPagNuevo="var arrPagNuevo=new Array();";
					while($filaAccion=mysql_fetch_row($resPagAccion))
					{
						$paginaRef="";
						switch($filaAccion[1])
						{
							case "0"://Nuevo
								$paginaRef=$filaAccion[0];
							break;
							case "1"://Modificar
								$paginaRef=$filaAccion[0];
							break;
							case "2"://Consultar/Ver
								$paginaRef=$filaAccion[0];
							break;
						}
						$arrPagNuevo.="arrPagNuevo[".$filaAccion[1]."]='".$paginaRef."';";

					}
					
					$elimina=false;
					if($numEtapa!=null)
					{
						$consulta="select idGrupoAccion from 944_actoresProcesoEtapa a,947_actoresProcesosEtapasVSAcciones ae,234_proyectosVSComitesVSEtapas ace where 
											ace.idProyectoVSComiteVSEtapa=a.actor and ace.numEtapa=a.numEtapa and ace.idComite=".$idComite." and
											ae.idActorProcesoEtapa=a.idActorProcesoEtapa and a.tipoActor=2 and  a.numEtapa=".$numEtapa." and a.idProceso=".$idProceso;
						$resAcciones=$con->obtenerFilas($consulta);
						while($filaAcciones=mysql_fetch_row($resAcciones))
						{
							switch($filaAcciones[0])
							{
								case "7":
									$elimina=true;
								break;
							}
						}
					}
					$btnEliminar="";
											
					$consulta="select complementario from 949_actoresVSAccionesProceso where actor in(".$roles.") and idAccion=9 and idProceso=".$idProceso." order by complementario";
					$complementario=$con->obtenerValor($consulta);
					$condWhere=" 1=1";
					
					$condWhere=" id_".$nTabla." in (".$listRegistros.") and codigoInstitucion='".$_SESSION["codigoInstitucion"]."'";
				    $consulta=obtenerRegistros($idFormulario,$condWhere," limit ".($nElemento*($pagina-1)).",".$nElemento);
				  	$consulta=str_replace(", from"," from ",$consulta);
				  	$resReg=$con->obtenerFilas($consulta);
					$consulta=str_replace(" limit ".($nElemento*($pagina-1)).",".$nElemento,"",$consulta);
					$con->obtenerFilas($consulta);
					$nRegistros=$con->filasAfectadas;
					$nPaginas= intval($nRegistros / $nElemento);
					$residuo=$nRegistros-($nPaginas*$nElemento);
					if($residuo>0)
						$nPaginas++;
					if($nRegistros==0)
						$nPaginas=1;
				?>
				<tr>
					<td align="left">
						<table width="600">
						<tr>
						<td>
						<span class="corpo8_bold">Página:</span>&nbsp;&nbsp;
						<?php
							$cadPag="";
							for($x=1;$x<=$nPaginas;$x++)
							{
								if($cadPag=="")
									$cadPag="<a href='javascript:irPagina(".$x.")'>".$x."</a>";
								else
									$cadPag.=", "."<a href='javascript:irPagina(".$x.")'>".$x."</a>";
								
							}
							echo $cadPag;
						?>
						</td>
						</tr>
						</table>
                        <br /><br />
					</td>
				</tr>
                <?php

                if($nRegistros==0)
				{
				?>
                    <tr>
                        <TD align="center">
                            <span class="copyrigthSinPadding">Actualmente no existen registros que evaluar</span>
                        </TD>
                    </tr>
               <?php		
				}
				else
				{
				
					$datosComp="";
					$arrConfCampo=obtenerCamposConfiguracion($idFrmBase,$datosComp);
					if(!$arrConfCampo)
						return;
					$nFilasConf=sizeof($arrConfCampo);
					$anexos=false;
					$investigadores=false;
					$investigadoresComp="";
					$programaTrabajo=false;
					$ademdum=false;
					obtenerModulosDisponibles($idProceso,$investigadores,$anexos,$programaTrabajo,$ademdum,$datosComp,$investigadoresComp);
					$idInvPrincipal="0";$arrRegistros=array();
					$consulta="SELECT campoOrden,direccionOrden FROM 909_configuracionTablaFormularios WHERE idFormulario=".$idFormulario;
					$filaOrden=$con->obtenerPrimeraFila($consulta);
					$ctAux=0;
					while($filaReg=mysql_fetch_assoc($resReg))
					{
						$numEtapaPadre=obtenerEtapaProcesoActual($filaReg["idRegistro"],$idReferencia,$idFormulario);
						$idUsuarioResp=obtenerResponsableProcesoActual($filaReg["idRegistro"],$idReferencia,$idFormulario);
						$cadObj='{"p16":{"p1":"'.$idFormulario.'","p2":"'.$idProceso.'","p3":"'.$filaReg["idRegistro"].'","p4":"'.$numEtapaPadre.'","p5":"'.$idReferencia.'","p6":"'.$idUsuarioResp.'"}}';
						$consultaBase=obtenerRegistros($idFormulario,"1=1","",$cadObj);
						$consultaBase=str_replace(", from"," from ",$consultaBase);
						$consultaBase.=" where idRegistro=".$filaReg["idRegistro"];
						$rRegistro=$con->obtenerFilas($consultaBase);
						$filaReg=mysql_fetch_assoc($rRegistro);	
						if(isset($filaReg[$filaOrden[0]]))
							$arrRegistros[$filaReg[$filaOrden[0]]."_".$ctAux]=$filaReg;
						else
							$arrRegistros[$filaReg["idRegistro"]."_".$ctAux]=$filaReg;
						$ctAux++;
						
					}
					if($filaOrden[1]=='ASC')
						ksort($arrRegistros);
					else
						krsort($arrRegistros);
					
						
					$nReg=sizeof($arrRegistros);
					if($nReg>0)
					{
						foreach($arrRegistros as $key=>$filaReg)
						{
							if($nEtapa==null)
							{
								$elimina=false;
								$btnEliminar="";
								$consulta="select idEstado from ".$nTabla." where id_".$nTabla."=".$filaReg["idRegistro"];
								$numEtapa=$con->obtenerValor($consulta);	
								$consulta="select idGrupoAccion from 944_actoresProcesoEtapa a,947_actoresProcesosEtapasVSAcciones ae,234_proyectosVSComitesVSEtapas ace where 
											ace.idProyectoVSComiteVSEtapa=a.actor and ace.numEtapa=a.numEtapa and ace.idComite=".$idComite." and
											ae.idActorProcesoEtapa=a.idActorProcesoEtapa and a.tipoActor=2 and  a.numEtapa=".$numEtapa." and a.idProceso=".$idProceso;
								$resAcciones=$con->obtenerFilas($consulta);
								while($filaAcciones=mysql_fetch_row($resAcciones))
								{
									switch($filaAcciones[0])
									{
										
										case "7":
											$elimina=true;
										break;
									}
								}
							}
							if(($elimina))
							{
								$btnEliminar="&nbsp;&nbsp;<a href='javascript:eliminarRegistro(\"".base64_encode($filaReg["idRegistro"])."\",".$filaReg["idRegistro"].")'><img src=\"../images/delete.png\" alt='Eliminar registro' title=\"Eliminar registro\" /></a>";
							}
							
							$invPrincipal="";
							$tituloInvestigador="Participante";
							$idInvs="";
							$respSeguimiento="Responsable seguimiento";
							if($investigadores)
							{
								if(isset($investigadoresComp[4]))
									$tituloInvestigador=$investigadoresComp[4];
								if(isset($investigadoresComp[5]))
									$respSeguimiento=$investigadoresComp[5];
								$idPerfilPart=$investigadoresComp[0];
								
								$idPrincipal=$investigadoresComp[1];
								$consulta="select concat(if(Prefijo is null,'',Prefijo),' ',if(Nom is null,'',Nom),' ',if(Paterno is null,'',Paterno),' ',
											if(Materno is null,'',Materno)) as registrado,i.idUsuario,ap.responsable,claveParticipacion from 802_identifica i, 246_autoresVSProyecto ap 
											where i.idUsuario<>703585 and i.idUsuario=ap.idUsuario and ap.idReferencia=".$filaReg["idRegistro"]." and ap.idFormulario=".$idFormulario." order by registrado";
								$resInv=$con->obtenerFilas($consulta);
								$invPrincipal="<table>";
								while($filaInv=mysql_fetch_row($resInv))
								{
									$cveParticipacion=$filaInv[3];
									$consulta="select descParticipacion from 953_elementosPerfilesParticipacionAutor where idElementoPerfilAutor='".$cveParticipacion."'";
									$participacion=$con->obtenerValor($consulta);
									
									if($filaInv[2]=="1")
										$invPrincipal.="<tr><td><a href='javascript:verUsrNuevaPagina(\"".base64_encode($filaInv[1])."\")'>".$filaInv[0]."</a> <font color='red'><span title='".$respSeguimiento."' alt='".$respSeguimiento."' style='cursor:help'>*</font></td><td>&nbsp;&nbsp;[".$participacion."]</td></tr>";
									else
										$invPrincipal.="<tr><td><a href='javascript:verUsrNuevaPagina(\"".base64_encode($filaInv[1])."\")'>".$filaInv[0]."</a></td><td>&nbsp;&nbsp;[".$participacion."]</td></tr>";
									
									
								}
								$invPrincipal.="</table>";
							}
							if($invPrincipal=="<table></table>")
							{
								$invPrincipal="No especificado";
							}
							
						?>
						 <tr id="fila_<?php echo $filaReg["idRegistro"]?>">
							<td align="center">
						
								<table>
										<tr>
											<td class="" width="30"><span class="corpo8_bold"><?php echo $ct.".-&nbsp;"?></span></td>
											<td width="180" class="esquinaFicha" align="left"><span class="corpo8_bold"><?php echo $arrConfCampo[0]["titulo"]?></span></td><td width="440" class="valorFicha" align="<?php echo $arrConfCampo[0]["alineacion"]?>"><span class="speaker"><?php echo formatearValorFicha($filaReg[$arrConfCampo[0]["nombreCampo"]],$arrConfCampo[0]["tipoElemento"])?></span></td>
											<td class="valorFicha">
											<?php
												$consulta="select idActorProcesoEtapa from 944_actoresProcesoEtapa pe,234_proyectosVSComitesVSEtapas ce 
															where pe.tipoActor=2 and ce.idProyectoVSComiteVSEtapa=pe.actor and idComite=".$idComite." and idProyecto=".$idProceso." and ce.numEtapa=".$numEtapa;
												$resVAct=$con->obtenerFilas($consulta);
												$actor=$con->obtenerValor($consulta);
												
												if($actor!="")
												{
													$consulta="SELECT rc.idRolComiteFunciones FROM 2007_rolesVSComites r,2009_rolesComitesFunciones rc WHERE 
																rc.idRolVSComite=r.idRolVSComite AND r.rol IN(".$roles.") AND r.idComite=".$idComite." AND rc.idFuncionComite=1";
													$idRolComiteFunciones=$con->obtenerValor($consulta);
													if($idRolComiteFunciones!="")
													{	
												?>
														<a href="javascript:verRegistro('<?php echo base64_encode($filaReg["idRegistro"])?>',<?php echo $filaReg["idRegistro"] ?>,'<?php echo base64_encode($actor)?>','<?php echo base64_encode($filaReg["idReferencia"])?>')">
														<img src="../images/magnifier.png" alt='Ver registro' title="Ver registro" />
														</a>
												<?php
													}
													else
													{
												?>
													<img src="../images/lock.png" title='Usted no cuenta con privilegios dentro de su comit&eacute; para ver el detalle del registro' alt="Usted no cuenta con privilegios dentro de su comit&eacute; para ver el detalle del registro" />
												<?php
													}
												}
												else
												{?>
													<img src="../images/lock.png" title='Usted no cuenta con privilegios en esta etapa para ver el detalle del registro' alt="Usted no cuenta con privilegios en esta etapa para ver el detalle del registro" />
												<?php
												}
												echo $btnEliminar;
												?>
											</td>
										</tr>
										<?php
											generarFichaElemento($arrConfCampo,$nTabla,$idFrmBase,$investigadores,$programaTrabajo,$anexos,$ademdum,$filaReg,$invPrincipal,$tituloInvestigador,$idProceso,$idReferencia);
											?>
										
								</table>
							</td>
						 </tr>
						 <tr id="filaComp_<?php echo $filaReg["idRegistro"]?>">
							<td>
	
							<br /><br />
							<input type="hidden" value="<?php echo $idFormulario?>" id="idFormulario" />
							</td>
						 </tr>
						
						<?php	
						$ct++;
						}
					}
				}
				?>
		</table>
       <script>
		<?php
			echo $arrPagNuevo;
		?>
		</script>
		<?php        
}

function obtenerMapaCurricularElemento($idElementoMapa)
{
	global $con;
	$consulta="select idMapaCurricular from 4031_elementosMapa where idElementoMapa=".$idElementoMapa;
	return $con->obtenerValor($consulta);
}

function obtenerPrograma($idMapaCurricular)
{
	global $con;
	$consulta="select idPrograma from 4029_mapaCurricular where idMapaCurricular=".$idMapaCurricular;
	return $con->obtenerValor($consulta);
}

function obtenerProgramaElemento($idElementoMapa)
{
	$idMapaCurricular=obtenerMapaCurricularElemento($idElementoMapa);
	return obtenerPrograma($idMapaCurricular);
}

function obtenerMapaCurricular($idPrograma,$ciclo=false)
{
	global $con;
	if(!$ciclo)
		$consulta="select idMapaCurricular from 4029_mapaCurricular where idPrograma=".$idPrograma;
	else
		$consulta="select idMapaCurricular from 4029_mapaCurricular where idPrograma=".$idPrograma." and ciclo=".$ciclo;
	return $con->obtenerValor($consulta);
}

function obtenerCicloElemento($idElementoMapa)
{
	global $con;
	$consulta="select mc.ciclo from 4031_elementosMapa em,4029_mapaCurricular mc where mc.idMapaCurricular=em.idMapaCurricular and  em.idElementoMapa=".$idElementoMapa;
	return $con->obtenerValor($consulta);
}

function obtenerMateriaElemento($idElementoMapa)
{
	global $con;
	$consulta="select idMateria from 4031_elementosMapa where idElementoMapa=".$idElementoMapa;
	return $con->obtenerValor($consulta);
}

function repetirCadena($nVeces,$cadena)
{
	$x;
	$cad="";
	for($x=0;$x<$nVeces;$x++)
	{
		$cad.=$cadena;
	}
	return $cad;
}

function obtenerValorControlFormulario($idControl,$valor)
{
	global $con;

	$consulta="select tipoElemento from 901_elementosFormulario where idGrupoElemento=".$idControl;
	$tipoElemento=$con->obtenerValor($consulta);
	$arrOpciones="";
	switch($tipoElemento)
	{
		case "2":
		case "14":
		case "17":
			$consulta="select contenido from 902_opcionesFormulario where idGrupoElemento=".$idControl." and idIdioma=".$_SESSION["leng"]." and valor=".$valor;
			return $con->obtenerValor($consulta);
		break;
		case "3":
		case "15":
		case "18":
			return $valor;		
		break;
		case "4":
		case "16":
		case "19":
			$consulta="select * from 904_configuracionElemFormulario where idElemFormulario=".$idControl;
			$fila=$con->obtenerPrimeraFila($consulta);		
			$nTabla=$fila[2];
			$campo=$fila[3];
			$columnaID=$fila[4];
			$consulta="select ".$campo." from ".$nTabla." where ".$columnaID."=".$valor;
			return $con->obtenerValor($consulta);
		break;
	}
}

function obtenerDireccion($idUsuario,$tipoDir=0)
{
	global $con;
	$consulta="select Calle,Numero,Colonia,Ciudad,Municipio,Estado,Pais,CP from 803_direcciones where idUsuario=".$idUsuario." and Tipo=".$tipoDir;
	$filaDir=$con->obtenerPrimeraFila($consulta);
	$direccion=formatearDireccion($filaDir);
	return $direccion;
	
}

function formatearDireccion($fDireccion)
{
		global $con;
		$cadena="";
		$nVacios=0;
		$calle=$fDireccion[0];
		if($calle=='')
		{
			$calle='N/E';
			$nVacios++;
		}
		$numero=$fDireccion[1];
		if($numero=='')
		{
			$numero='N/E';
			$nVacios++;
		}
		$cp=$fDireccion[7];
		if($cp=='')
		{
			$cp='N/E';
			$nVacios++;
		}
		$colonia=$fDireccion[2];
		if($colonia=='')
		{
			$colonia='N/E';
			$nVacios++;
		}
		
		$ciudad=$fDireccion[3];
		$consulta="SELECT localidad FROM 822_localidades WHERE cveLocalidad='".$ciudad."'";
		$res=$con->obtenerValor($consulta);
		if($res!="")
			$ciudad=$res;
		else
		{
			$consulta="SELECT municipio FROM 821_municipios WHERE cveMunicipio='".$ciudad."'";
			$res=$con->obtenerValor($consulta);
			if($res!="")
				$ciudad=$res;
		}
		$municipio=$fDireccion[4];
		$consulta="SELECT municipio FROM 821_municipios WHERE cveMunicipio='".$municipio."'";
		$res=$con->obtenerValor($consulta);
		if($res!="")
			$municipio=$res;
		$estado=$fDireccion[5];
		$consulta="SELECT estado FROM 820_estados WHERE cveEstado='".$estado."'";
		$res=$con->obtenerValor($consulta);
		if($res!="")
			$estado=$res;
		
		$pais=$fDireccion[6];
		$consulta="SELECT nombre FROM 238_paises WHERE idPais=".$pais;
		$res=$con->obtenerValor($consulta);
		if($res!="")
			$pais=$res;
		$descCiudad="";
		
		if($ciudad!="")
			$descCiudad.=$ciudad.", ";
		else
			$nVacios++;
		if($municipio!="")

			$descCiudad.=$municipio.", ";
		else
			$nVacios++;		
		if($estado!="")
			$descCiudad.=$estado.", ";
		else
			$nVacios++;
		if($pais!="")
			$descCiudad.=$pais.".";
		else
		{
			$descCiudad.=".";
			$nVacios++;
		}
		if($nVacios==8)
			return "Sin dirección registrada";
		$descCiudad=str_replace(",.",".",$descCiudad);
		$direccion="Calle ".$calle." # ".$numero." CP. ".$cp." colonia ".$colonia.". ".$descCiudad;
		$direccion=str_replace(". .",".",$direccion);
		$direccion=str_replace(", .",".",$direccion);
		return uEJ($direccion);
}

function obtenerCicloActual()
{
	global $con;
	$consulta="select anio from 4015_ciclos where status=1";
	return $con->obtenerValor($consulta);
}

function obtenerEventosUsuario($idUsuario,$fecha,$horaInicio,$horaFin,$detalleColision=false,$considerarClases=true,$tipoEventos="0")
//tipoEventos= 0 considerar todos los tipos;"" No considerar eventos institucionales;>0 el tipo de evnto a considerar separado por ,
{
	global $con;
	$arrEventos="";
	$nColisiones=0;
	if($considerarClases)
	{
		$consulta=" (select * from (
				select s.* from (
							(select idMateria,idGrupo from 4120_alumnosVSElementosMapa where situacion=1 and idUsuario=".$idUsuario.")
							union
							(select idMateria,idGrupo from 4047_participacionesMateria where participacionP=1 and estado=1 and idUsuario=".$idUsuario.")
						) as tmp,4053_sesiones s where s.idGrupo=tmp.idGrupo and s.idMateria=tmp.idMateria and s.estado!='CANCELLED' and s.fecha='".$fecha."')
							as tmp2
			)
			union
			(
				select s.* from 4053_sesiones s, 4069_participantesInvitados pi where s.idGrupo=pi.idGrupo and s.idMateria=pi.idMateria and s.noSesion=pi.noSesion
				and pi.idUsuario=".$idUsuario." and and s.fecha='".$fecha."'
			)
	
					";
		$resSesionClase=$con->obtenerFilas($consulta);
		
		while($filaClase=mysql_fetch_row($resSesionClase))
		{
			if(colisionaTiempo($fila[6],$fila[7],$horaInicio,$horaFin))
			{
				if($detalleColision)
					$obj='{"tipo":"1","Subtipo":"'+$filaClase[8]+'","idMateria":"'.$filaClase[2].'","idGrupo":"'.$filaClase[5].'","noSesion":"'.$filaClase[4].'"}';
				else
					$obj='{"tipo":"1"}';
				if($arrEventos=="")
					$arrEventos=$obj;
				else
					$arrEventos.=",".$obj;
				$nColisiones++;
			}
		}
	}
	if($tipoEventos!="")
	{
		if($tipoEventos=="0")
		{
			$consulta="(select * from 4089_calendario where fecha='".$fecha."' and estado!='CANCELLED' and idUsuario=".$idUsuario.")
						union
						(
						 select c.* from 4094_eventosVsRol er,4089_calendario c where c.idFecha=er.idFecha and c.fecha='".$fecha."' and c.estado!='CANCELLED' and er.rol=".$idUsuario."
						 )";

		}
		else
		{
			$consulta="(select * from 4089_calendario where fecha='".$fecha."' and estado!='CANCELLED' and tipo in (".$tipoEventos.") and idUsuario=".$idUsuario.")
						union
						(
						 select c.* from 4094_eventosVsRol er,4089_calendario c where c.idFecha=er.idFecha and c.fecha='".$fecha."' and c.estado!='CANCELLED' and tipo in (".$tipoEventos.") and er.rol=".$idUsuario."
						 )";
		}
		
		$resEvento=$con->obtenerFilas($consulta);
		
		while($filaEvento=mysql_fetch_row($resEvento))
		{
			if(colisionaTiempo($fila[1],$fila[2],$horaInicio,$horaFin))
			{
				if($detalleColision)
					$obj='{"tipo":"2","Subtipo":"'+$filaEvento[7]+'"}';
				else
					$obj='{"tipo":"2"}';
				if($arrEventos=="")
					$arrEventos=$obj;
				else
					$arrEventos.=",".$obj;
				$nColisiones++;
			}
		}
		
	}
	
	echo "1|".$nColisiones."|".base64_encode("[".uEJ($arrEventos)."]");
	
}

function esMateriaCompartida($idMateria,$idElemento)
{
	global $con;
	$consulta="select compartida from 4013_materia where idMateria=".$idMateria;
	$compartida=$con->obtenerValor($consulta);
	if($compartida=="1")
		return true;
		
	$consulta="select idGrado,idPadre,idMapaCurricular from 4031_elementosMapa where idElementoMapa=".$idElemento;
	$filaElem=$con->obtenerPrimeraFila($consulta);
	if($filaElem[1]=="0")
		return false;
	
	$consulta="select idElementoMapa from 4031_elementosMapa where idMateria=".$filaElem[1]." and idMapaCurricular=".$filaElem[2];
	
	$idElemMapa=$con->obtenerValor($consulta);
	return esMateriaCompartida($filaElem[1],$idElemMapa);
}

function actualizarDireccionDependencias($idUsuarioF)
{
	global $con;
	$consulta="select idUsuario from 802_identifica where viveCon=".$idUsuarioF;
	
	$resU=$con->obtenerFilas($consulta);
	while($fU=mysql_fetch_row($resU))
	{
		if(!actualizarDireccion($idUsuarioF,$fU[0]))
			return false;
	}
	return actualizarDireccionDependenciasSuperiores($idUsuarioF);

}

function actualizarDireccionDependenciasSuperiores($idUsuarioF)
{
	global $con;
	$consulta="select viveCon from 802_identifica where idUsuario=".$idUsuarioF;
	$idPadre=$con->obtenerValor($consulta);
	if($idPadre>0)
	{
		if(actualizarDireccion($idUsuarioF,$idPadre,$idUsuarioF))
			return actualizarDireccionDependenciasSuperiores($idPadre);
		else
			return false;
	}
	return true;
}

function actualizarDireccion($idUsuarioF,$idUsuarioD,$ignorarUsuario="-1")
{
	global $con;
	$consulta="select Calle,Numero,Colonia,Ciudad,CP,Estado,Pais,Municipio from 803_direcciones where idUsuario=".$idUsuarioF;
	$fila=$con->obtenerPrimeraFila($consulta);
	$cp=$fila[4];
	if($cp=='')
		$cp='NULL';
	$consulta="update 803_direcciones set Calle='".$fila[0]."',Numero='".$fila[1]."',Colonia='".$fila[2]."',Ciudad='".$fila[3]."',CP=".$cp.",Estado='".$fila[5]."',Pais='".$fila[6]."',Municipio='".$fila[7]."' where idUsuario=".$idUsuarioD;
	if($con->ejecutarConsulta($consulta))		
	{
		$consulta="delete from 804_telefonos where Tipo=0 and Tipo2<>1 and idUsuario=".$idUsuarioD;
		if($con->ejecutarConsulta($consulta))
		{
			$consulta="select * from 804_telefonos where Tipo=0 and Tipo2<>1 and idUsuario=".$idUsuarioF;
			$resTel=$con->obtenerFilas($consulta);
			while($fTel=mysql_fetch_row($resTel))
			{
				$consulta="insert into 804_telefonos(Lada,Numero,Extension,Tipo,Tipo2,idUsuario) values('".$fTel[1]."','".$fTel[2]."','".$fTel[3]."',".$fTel[4].",".$fTel[5].",".$idUsuarioD.")";			
				if(!$con->ejecutarConsulta($consulta))
					return false;
			}
			
			$consulta="select idUsuario from 802_identifica where viveCon=".$idUsuarioD." and idUsuario<>".$ignorarUsuario;
			$resU=$con->obtenerFilas($consulta);
			while($fU=mysql_fetch_row($resU))
			{
				if(!actualizarDireccion($idUsuarioD,$fU[0]))
					return false;
			}
			
			return true;
		}
		else
			return false;
		
	}
	else
		return false;
}

function obtenerPeriodoTrabajo($fI,$fF,$nDias) // entrada en formato cadena YYYY-mm-dd
{
	$arrSemanas=array();
	
	$fInicio=strtotime($fI);
	$fFin=strtotime($fF);
	$dInicio=date("N",$fInicio);
	
	$fechaInicioSemana=$fInicio;
	$x=0;
	if($dInicio!=1)
		$fechaInicioSemana=strtotime('-'.($dInicio-1).' days',$fInicio);
	while($fechaInicioSemana<=$fFin)
	{
		
		$fechaFinSemana=strtotime("+ ".($nDias-1)." days ",$fechaInicioSemana);		
		$arrSemanas[$x]['nSemana']=($x+1);
		$arrSemanas[$x]['fechaI']=$fechaInicioSemana;
		$arrSemanas[$x]['fechaF']=$fechaFinSemana;
		$x++;
		$fechaInicioSemana=strtotime('+'.$nDias.' days',$fechaInicioSemana);
		
	}
	
	return $arrSemanas;
}

function calcularHorasComprometidas($idUsuario,$fIni,$fFin,&$desgloceTiempo)
{
	global $con;
	$consulta="";
	$consulta="select horasTotal,tipoActividadProgramada,idActividadPrograma,idUsuario,idProcesoAsociado,idFormulario,idReferencia,actividad from 965_actividadesUsuario where idUsuario=".$idUsuario."  and ((fechaInicio>='".$fIni."' and fechaInicio<='".$fFin."') or (fechaFin>='".$fIni."' and fechaFin<='".$fFin."') ) order by fechaInicio";
	$res=$con->obtenerFilas($consulta);	
	$totalHoras=0;
	while($filaAct=mysql_fetch_row($res))
	{
		switch($filaAct[1])
		{
			case 1:
			case 2:
			case 3:
			case 5:
				$totalHoras+=$filaAct[0];
			break;
		}

		if($desgloceTiempo!=NULL)
		{
			switch($filaAct[1])
			{
				case 1: //Libre 
					if(!isset($desgloceTiempo["Actividades@@Libres"]))
					{
						$desgloceTiempo["Actividades@@Libres"]["total"]=$filaAct[0];
						$desgloceTiempo["Actividades@@Libres"]["tipo"]=$filaAct[1];
					}
					else
						$desgloceTiempo["Actividades@@Libres"]["total"]+=$filaAct[0];
				break;
				case 2:  //asociado a un proceso
					$consulta="select nombre from 4001_procesos where idProceso=".$filaAct[4];
					$nProceso=str_replace(" ","@@",$con->obtenerValor($consulta));
					if(!isset($desgloceTiempo[$nProceso]))
					{
						$desgloceTiempo[$nProceso]["total"]=$filaAct[0];
						$desgloceTiempo[$nProceso]["tipo"]=$filaAct[1];
						$desgloceTiempo[$nProceso]["idProceso"]=$filaAct[4];
					}
					else
						$desgloceTiempo[$nProceso]["total"]+=$filaAct[0];
				break;
				case 3: //Clase
					if(!isset($desgloceTiempo["Sesiones@@de@@clase"]))
					{
						$desgloceTiempo["Sesiones@@de@@clase"]["total"]=$filaAct[0];
						$desgloceTiempo["Sesiones@@de@@clase"]["tipo"]=$filaAct[1];
					}
					else
						$desgloceTiempo["Sesiones@@de@@clase"]["total"]+=$filaAct[0];
				break;
				case 5:  //actividad extra
					if(!isset($desgloceTiempo["Actividades@@extra"]))
					{
						$desgloceTiempo["Actividades@@extra"]["total"]=$filaAct[0];
						$desgloceTiempo["Actividades@@extra"]["tipo"]=$filaAct[0];
					}
					else
						$desgloceTiempo["Actividades@@extra"]["total"]+=$filaAct[0];
				break;
			}
		}
		
		$totalHoras+=calcularHorasComprometidasHijos($idUsuario,$filaAct[2],$desgloceTiempo);
	}
	return $totalHoras;
	
}

function calcularHorasComprometidasHijos($idUsuario,$idActividadP,&$desgloceTiempo)
{
	global $con;
	$consulta="";
	$consulta="select horasTotal,tipoActividadProgramada,idActividadPrograma,idUsuario,idProcesoAsociado,idFormulario,idReferencia from 965_actividadesUsuario where idUsuario=".$idUsuario." and idPadre=".$idActividadP;
	$res=$con->obtenerFilas($consulta);	
	$totalHoras=0;
	while($filaAct=mysql_fetch_row($res))
	{
		if($idUsuario=$filaAct[3])
		{
			switch($filaAct[1])
			{
				case 1:
				case 2:
				case 3:
				case 5:
					$totalHoras+=$filaAct[0];
				break;
			}
		}
		
		if($desgloceTiempo!=null)
		{
			switch($filaAct[1])
			{
				case 1: //Libre 
					if(!isset($desgloceTiempo["Actividades@@Libres"]))
					{
						$desgloceTiempo["Actividades@@Libres"]["total"]=$filaAct[0];
						$desgloceTiempo["Actividades@@Libres"]["tipo"]=$filaAct[1];
					}
					else
						$desgloceTiempo["Actividades@@Libres"]["total"]+=$filaAct[0];
				break;
				case 2:  //asociado a un proceso
					$consulta="select nombre from 4001_procesos where idProceso=".$filaAct[4];
					$nProceso=str_replace(" ","@@",$con->obtenerValor($consulta));
					if(!isset($desgloceTiempo[$nProceso]))
					{
						$desgloceTiempo[$nProceso]["total"]=$filaAct[0];
						$desgloceTiempo[$nProceso]["tipo"]=$filaAct[1];
						$desgloceTiempo[$nProceso]["idProceso"]=$filaAct[4];
					}
					else
						$desgloceTiempo[$nProceso]["total"]+=$filaAct[0];
				break;
				case 3: //Clase
					if(!isset($desgloceTiempo["Clases"]))
					{
						$desgloceTiempo["Clases"]["total"]=$filaAct[0];
						$desgloceTiempo["Clases"]["tipo"]=$filaAct[1];
					}
					else
						$desgloceTiempo["Clases"]["total"]+=$filaAct[0];
				break;
				case 5:  //actividad extra
					if(!isset($desgloceTiempo["Actividad@@extra"]))
					{
						$desgloceTiempo["Actividad@@extra"]["total"]=$filaAct[0];
						$desgloceTiempo["Actividad@@extra"]["tipo"]=$filaAct[0];
					}
					else
						$desgloceTiempo["Actividad@@extra"]["total"]+=$filaAct[0];
				break;
			}
		}

		
		$totalHoras+=calcularHorasComprometidasHijos($idUsuario,$filaAct[2],$desgloceTiempo);
	}
	return $totalHoras;
}



function generarHorarioDiaPerfil($idPerfil,$dia,$hInicio,$bloqueAnt,&$arrHorario)
{
	global $con;
	$consulta="select tb.duracion,idPerfilVSBloque,tb.categoriaBloque from 4062_perfilVSBloque pb,4061_tipoBloques tb where tb.idBloque=pb.idBloque  and idPerfil=".$idPerfil." and dia=".$dia." and anterior=".$bloqueAnt;
	$fila=$con->obtenerPrimeraFila($consulta);
	if($fila)
	{
		$horaInicioBloque=$hInicio;
		$horaFinBloque=date("H:i",strtotime("+ ".$fila[0]." minutes",strtotime($horaInicioBloque)));
		$arrBloque[0]=$horaInicioBloque;
		$arrBloque[1]=$horaFinBloque;
		$arrBloque[2]=$fila[2];
		$arrBloque[3]=$fila[0];
		$arrBloque[4]=$fila[1];
		array_push($arrHorario,$arrBloque);
		generarHorarioDiaPerfil($idPerfil,$dia,$horaFinBloque,$fila[1],$arrHorario);
	}
	
}

function existeBloque($horario,$arrHorarioDia,$idMateria,$idGrupo,$idDia)
{
	global $con;
	$situacion=1;
	$validarSituacion=false;
	foreach($arrHorarioDia as $objHorario)
	{
		if(($horario[0]==$objHorario[0])&&($horario[1]==$objHorario[1]))
		{
			$validarSituacion=true;
			break;
		}
	}
	if($validarSituacion)
	{
		//verifiar situacion del bloque
		$conBloquesMateria="SELECT idMateria FROM 4065_materiaVSGrupo h ,4062_perfilVSBloque p WHERE idGrupo=".$idGrupo." AND dia=".$idDia." AND h.horaInicio='".$$horario[0]."' AND h.horaFin='".$$horario[1]."' AND h.idBloque=p.idPerfilVSBloque";
		$bloquesMateria=$con->obtenerFilas($conBloquesMateria);
		$nMaterias=$con->filasAfectadas;
		
		if($nMaterias==0)
		{
			$situacion=0;
		}
		else
		{
			while($matBloq=mysql_fetch_row($bloquesMateria))
			{
				$conDatosMat="SELECT idPrograma,ciclo FROM 4013_materia WHERE idMateria=".$matBloq[0];
				$datosMat=$con->obtenerPrimeraFila($conDatosMat);
				$conMapa2="SELECT idMapaCurricular FROM 4029_mapaCurricular WHERE idPrograma=".$datosMat[0]." AND ciclo=".$datosMat[1];
				$idMap=$con->obtenerValor($conMapa2);
				
				$conTipoMat="SELECT idTipoMateria FROM 4031_elementosMapa WHERE idMateria=".$matBloq[0]." AND idMapaCurricular=".$idMap;
				$tipoMat=$con->obtenerValor($conTipoMat);
				if($tipoMat==1)
				{
					$situacion=2;
				}
			}
			$situacion=1;
		}
	}
	return $situacion;
}

function validarHorarioMateria($idMateria,$idPerfil,$idGrupo)
{
	global $con;
	$conDatosMateria="SELECT idPrograma,ciclo FROM 4013_materia WHERE idMateria=".$idMateria;
	$datos=$con->obtenerPrimeraFila($conDatosMateria);
	
	$conGruposMat="SELECT idGrupo FROM 4048_grupos WHERE idMateria=".$idMateria;
	
	$grupos=$con->obtenerFilas($conGruposMat);
	$nGrupos=$con->filasAfectadas;
	
	$conMapaMateria="SELECT idMapaCurricular FROM 4029_mapaCurricular WHERE idPrograma=".$datos[0]." AND ciclo=".$datos[1];
	$mapaMateria=$con->obtenerValor($conMapaMateria);
	
	$conGrado="SELECT idGrado FROM 4031_elementosMapa WHERE idMateria=".$idMateria." AND idMapaCurricular=".$mapaMateria;
	$grado=$con->obtenerValor($conGrado);
	
	$conTipoHorarioMat="SELECT idTipoHorario FROM 4031_elementosMapa WHERE idMateria=".$idMateria." AND idMapaCurricular=".$mapaMateria." AND idGrado=".$grado;
	$tipoHorarioMat=$con->obtenerValor($conTipoHorarioMat);
	$arrGrupo=array();
	$consulta="select horaInicio from 4060_perfilHorarios where idPerfil=".$idPerfil;
	$horaInicial=$con->obtenerValor($consulta);
	if($tipoHorarioMat==1)
	{
		
		while($filaG=mysql_fetch_row($grupos))
		{
			$arrDias=array();
			$conBloquesMatGrupo="SELECT dia,h.horaInicio,h.horaFin FROM 4065_materiaVSGrupo h, 4062_perfilVSBloque p WHERE h.idBloque=p.idPerfilVSBloque AND idGrupo=".$filaG[0]." AND idMateria=".$idMateria;
			$bloquesMatGrupo=$con->obtenerFilas($conBloquesMatGrupo);
			while($filaH=mysql_fetch_row($bloquesMatGrupo))
			{
				
				$obj[0]=$filaH[1];
				$obj[1]=$filaH[2];
				if(!isset($arrDias["".$filaH[0]]))
					$arrDias["".$filaH[0]]=array();
					
				array_push($arrDias["".$filaH[0]],$obj);
			}
			if(!isset($arrGrupo[$filaG[0]]))
				$arrGrupo[$filaG[0]]=array();
			array_push($arrGrupo[$filaG[0]],$arrDias);
		}
		
		
		foreach($arrGrupo as $arrDia)
		{
			
			foreach($arrDia as $objDia)
			{
				foreach($objDia as $idDia=>$arrHorario)
				{
					$arrDiasPerfil=array();
					generarHorarioDiaPerfil($idPerfil,$idDia,$horaInicial,"-1",$arrDiasPerfil);
					foreach($arrHorario as $horario)
					{
						$resultado=existeBloque($horario,$arrDiasPerfil,$idMateria,$idGrupo,$idDia);
						if($resultado!=0)
						{
							return $resultado;
						}
					}
				}
			}
		}
		return  0;
		
	}
	return 0;
}

function incluyeModulo($idProceso,$idModulo)
{
	global $con;
	$consulta="select e.idFormulario from 203_elementosDTD e,900_formularios f where f.idFormulario=e.idFormulario and tipoFormulario=10 and titulo=".$idModulo." and e.idProceso=".$idProceso;
	$idFormulario=$con->obtenerValor($consulta);	
	if($idFormulario=="")
		return "-1";
	return $idFormulario;
}

function obtenerIdProcesoFormulario($idFormulario,$conAux=NULL)
{
	global $con;
	$conConexion=$con;
	if($conAux!=NULL)
		$conConexion=$conAux;
	$consulta="select idProceso from 900_formularios where idFormulario=".$idFormulario;
	$idProceso=$conConexion->obtenerValor($consulta);
	
	return $idProceso;
}

function obtenerNombreTabla($idFormulario)
{
	global $con;
	$consulta="select nombreTabla from 900_formularios where idFormulario=".$idFormulario;
	$idProceso=$con->obtenerValor($consulta);
	return $idProceso;
}

function obtenerFormularioBase($idProceso)
{
	global $con;
	$consulta="select idFormulario from 900_formularios where idProceso=".$idProceso." and formularioBase=1";
	$idFormulario=$con->obtenerValor($consulta);
	return $idFormulario;
}

function obtenerTipoProceso($idProceso)
{
	global $con;
	$consulta="select idTipoProceso from 4001_procesos where idProceso=".$idProceso;
	$tipoProceso=$con->obtenerValor($consulta);
	return $tipoProceso;
}

function esFormularioBase($idFormulario)
{
	global $con;
	$consulta="select formularioBase from 900_formularios where idFormulario=".$idFormulario;
	
	$frmBase=$con->obtenerValor($consulta);
	if($frmBase=="1")
		return true;
	else
		return false;
}

function obtenerIdFormularioNombreTabla($tabla)
{
	global $con;
	$consulta="select idFormulario from 900_formularios where nombreTabla='".$tabla."'";
	$idFormulario=$con->obtenerValor($consulta);
	return $idFormulario;
}

function obtenerNombreRegistro($tabla,$fila)
{
	global $con;
	if($con->existeCampo("codigo",$tabla)!="")
		$posNombre=10;
	else
		$posNombre=9;
	return $fila[$posNombre];
}

function obtenerCodigoRegistro($tabla,$fila)
{
	global $con;
	$posCod=$con->existeCampo("codigo",$tabla);
	if($posCod!="")
		return $fila[$posCod];
	return "";
	return $codigo;
}

function obtenerFolioFormulario($idFormulario)
{
	global $con;
	$consulta="begin";
	if($con->ejecutarConsulta($consulta))
	{
		$consulta="select * from 4001_foliosRegistros where idFormulario=".$idFormulario." and activo=1 for update";
		$fila=$con->obtenerPrimeraFila($consulta);
		if(!$fila)
		{
			$con->ejecutarConsulta("commit");
			return "";
		}
		$prefijo=$fila[1];
		$separador=$fila[2];
		$longitud=$fila[3];
		$incremento=$fila[4];
		$actual=$fila[6];
		$folio=$prefijo.$separador.str_pad($actual,$longitud,"0",STR_PAD_LEFT);
		$x=0;
		$query[$x]="update 4001_foliosRegistros set numActual=numActual+".$incremento." where idFolio=".$fila[0];
		$x++;
		$query[$x]="commit";
		$x++;
		if($con->ejecutarBloque($query))
			return $folio;
		else
			return "";
	}
	else
		return "";
}

function asignarFolioRegistro($idFormulario,$idRegistro)
{
	global $con;
	$nTabla=obtenerNombreTabla($idFormulario);
	if($con->existeCampo("codigo",$nTabla)=="")
		return true;
	$consulta="SELECT tipoGeneradorFolio,funcionGeneradoraFolio FROM 900_formularios WHERE  idFormulario=".$idFormulario;
	$fila=$con->obtenerPrimeraFila($consulta);	
	if($fila[0]==1)
		$folio=obtenerFolioFormulario($idFormulario);
	else
		eval('$folio='.$fila[1].'("'.$idFormulario.'","'.$idRegistro.'");');
	if($folio=="")
		$folio=$idRegistro;
	$consulta10="update ".$nTabla." set codigo='".$folio."' where id_".$nTabla."=".$idRegistro;
	if($con->ejecutarConsulta($consulta10))
		return true;
	else
		return false;
}

function obtenerTituloRegistro($fila,$idFormulario,$existeCampoCodigo)
{
	$consulta="select campoDescriptivo,nombreTabla from 900_formularios where idFormulario=".$idFormulario;
	$filaFormulario=$con->obtenerPrimeraFila($consulta);
	$campo=$filaFormulario[0];
	$nTabla=$filaFormulario[1];
	if($campo=="")
	{
		if(!$existeCampoCodigo)
			return $fila[9];
		else
			return $fila[10];
	}
	else
	{
		$pos=$con->existeCampo($campo,$nTabla);
		return $fila[$pos];
	}
}

function generarConsultaTipoActividad($tActividad)
{
	$datosActividad=array();
	$queryAct="";
	$queryProceso="";
	$queryRegistros="";
	$llaveCierre="";
	if($tActividad!="")
	{
		$arrActividad=explode("@",$tActividad);
		$qAux="";
		foreach($arrActividad as $actividad)
		{
			$objActividad=explode("|",$actividad);
			if(($objActividad[0]!=2)||($objActividad[1]==""))
			{
				if($qAux=="")
					$qAux=$objActividad[0];
				else
					$qAux.=",".$objActividad[0];
			}
			if($objActividad[0]==2)
			{
				if($objActividad[4]!="")
				{
					$idProceso=$objActividad[2];
					$idFormulario=obtenerFormularioBase($idProceso);
					$nTabla=obtenerNombreTabla($idFormulario);
					$etapas=$objActividad[4];	
					if($queryRegistros=="")
						$queryRegistros=" ((idFormulario=".$idFormulario." and idReferencia in (select id_".$nTabla." from ".$nTabla." where idEstado in (".$etapas.")))";
					else
						$queryRegistros.=" or (idFormulario=".$idFormulario." and idReferencia in (select id_".$nTabla." from ".$nTabla." where idEstado in (".$etapas.")))";
				}
				else
					if($objActividad[3]!="")
					{
						$idProceso=$objActividad[2];
						$idFormulario=obtenerFormularioBase($idProceso);
						$idReferencia=$objActividad[3];
						$etapas=$objActividad[4];	
						if($queryRegistros=="")
							$queryRegistros=" ((idFormulario=".$idFormulario." and idReferencia=".$idReferencia.")";
						else
							$queryRegistros.=" or (idFormulario=".$idFormulario." and idReferencia=".$idReferencia.")";
						
					}
					else
						if($objActividad[1]!="")
						{
							if($queryRegistros=="")
								$queryRegistros=" ((idProcesoAsociado in (select idProceso from 4001_procesos where idTipoProceso=".$objActividad[1]."))";
							else
								$queryRegistros.=" or (idProcesoAsociado in (select idProceso from 4001_procesos where idTipoProceso=".$objActividad[1]."))";	
						}
						
			}	
		}
		$incTipoAct=false;
		if($qAux!="")
		{
			$incTipoAct=true;
			$queryAct=" and (((tipoActividadProgramada in (".$qAux."))";
			
			$llaveCierre=")";
		}
		if($queryRegistros!="")
		{	
			if($qAux=="")
				$queryRegistros=" and (".$queryRegistros.")";	
			else		
				$queryRegistros=" or (".$queryRegistros.")";	
			$queryAct.=$queryRegistros.$llaveCierre;
		}
		if($incTipoAct)
			$queryAct.="))";
		else
			$queryAct.=")";
		
	}
	
	array_push($datosActividad,$queryAct);
	return $datosActividad;
}

function obtenerProgramaUsuario($idUsuario,$sl,$fIni,$fFin,$tActividad="",$lineasInv="",&$arrActividadesF,$conAux="")
{
	global $con;
	$queryAct="";
	if($tActividad!="")
	{
		$datosActividad=generarConsultaTipoActividad($tActividad);
		$queryAct=$datosActividad[0];
	}
	$queryLineasInv="";
	if($lineasInv!="")
	{
		$queryLineasInv=" and idActividadPrograma in (select idActividad from 969_actividadesLineasAccion where idLineaInvestigacion in(".$lineasInv."))";
	}
	$arrActividades=array();
	if($conAux!="")
	{
		if($idUsuario!="-1")
			$idUsuario=" and idUsuario in (".$idUsuario.")";
		else
			$idUsuario="";
		$consulta="select * from 965_actividadesUsuario where 1=1 ".$idUsuario." ".$queryLineasInv." and 
						((fechaInicio>='".$fIni."' and fechaInicio<='".$fFin."') or (fechaFin>='".$fIni."' and fechaFin<='".$fFin."')) 
						".$conAux." order by fechaInicio,idActividadPrograma";
		
	}
	else
		if($idUsuario!="-1")
		{
			$consulta="select * from 965_actividadesUsuario where idUsuario in (".$idUsuario.") ".$queryLineasInv."  and 
						((fechaInicio>='".$fIni."' and fechaInicio<='".$fFin."') or (fechaFin>='".$fIni."' and fechaFin<='".$fFin."')) 
						".$queryAct." order by fechaInicio,idActividadPrograma";
			
		}
		else
			$consulta="select * from 965_actividadesUsuario where idPadre=-1 ".$queryLineasInv." and ((fechaInicio>='".$fIni."' and fechaInicio<='".$fFin."') or 
					(fechaFin>='".$fIni."' and fechaFin<='".$fFin."') ) ".$queryAct." order by fechaInicio";	
	
	$res=$con->obtenerFilas($consulta);
	$arrTareas="";
	while($fila=mysql_fetch_row($res))
	{
		$padre=$fila[10];
		if($padre=="-1")
			$padre=0;
		$nHoras=$fila[11];
		$hijos="";
		if(existeValor($arrActividades,$fila[0]))
			continue;
		$hijos=obtenerNodosHijosActividades($fila[0],true,$idUsuario,$sl,$tActividad,$lineasInv,$arrActividades);
		
		if($hijos=="")
			$pGroup=0;
		else
			$pGroup=1;
		$pAgregar="1";
		if($pGroup==0)
			$pEliminar="1";
		else
			$pEliminar="0";
		$titulo=$fila[2];
		if(strlen($titulo)>40)
			$titulo=substr($titulo,0,40)."...";
			
		switch($fila[1])
		{
			case "1":
			case "2":
				if($sl==0)
					$pagina="../modeloProyectos/fichaActividad.php?p=".base64_encode($fila[0]);
				else
					$pagina="../modeloProyectos/fichaActividadProceso.php|".base64_encode($fila[0]);
			break;
			case 3: //cclase
				$pagina="";
			break;
			case 4: //Sesion Clase
			break;
			case 5: //Sesion Extra
			break;
		}
		
		
		if($sl=="1")
		{
			$pEliminar="0";
			$pAgregar="0";
		}
		$consulta="select color from 967_prioridadActividad where idPrioridad=".$fila[6];
		$porcentaje=obtenerAvanceActividadUsuario($fila[0]);
		$color=$con->obtenerValor($consulta);

		$usuario=obtenerNombreUsuario($fila[7]);
		$pCaption=obtenerInicialesFrase($usuario)."|".$usuario;
		$cadTarea="<task>
						<pID>".$fila[0]."</pID>
						<pName>".$titulo."</pName>
						<pStart>".date('d/m/Y',strtotime($fila[4]))."</pStart>
						<pEnd>".date('d/m/Y',strtotime($fila[5]))."</pEnd>
						<pColor>".$color."</pColor>
						<pLink>".$pagina."</pLink>
						<pMile>0</pMile>
						<pRes></pRes>
						<pComp>".$porcentaje."</pComp>
						<pGroup>".$pGroup."</pGroup>
						<pParent>0</pParent>
						<pOpen>1</pOpen>
						<pDepend></pDepend>
						<pHoras>".$nHoras."</pHoras>
						<pCaption>".$pCaption."</pCaption>
						<pAgregar>".$pAgregar."</pAgregar>
						<pEliminar>".$pEliminar."</pEliminar>
					</task>
					";
		$arrTareas.=$cadTarea.$hijos;
		if($arrActividades!==null)
			array_push($arrActividades,$fila[0]);
	}
	if(($arrActividadesF!==null)&&($arrActividadesF!=""))
		$arrActividadesF=$arrActividades;
	return $arrTareas;
}

//RcCorporativo
function generarTableroCreditos($tipoTablero,$parametro,$fInicio,$fFin)
{
	global $con;
	$comp="";
	$compTitulo="";
	$idGpo="";
	switch($tipoTablero)
	{
		case 1: //Todos
			$consulta="select idUsuario from 807_usuariosVSRoles where codigoRol like '38_%'";
			$listaUsr=$con->obtenerListaValores($consulta);
			$compTitulo=" (Vista General)";
			if($listaUsr=="")
				$listaUsr="-1";
			$comp=" ";
		break;
		case 2: //Grupo
			$idGpo=$parametro;
			$consulta="select idUsuario from 807_usuariosVSRoles where codigoRol='38_".$parametro."'";
			$listaUsr=$con->obtenerListaValores($consulta);
			if($listaUsr=="")
				$listaUsr="-1";
			$comp=" and idPromotor in(".$listaUsr.")";
			$consulta="select unidadRol from 4084_unidadesRoles where idUnidadesRoles=".$parametro;
			$compTitulo=$con->obtenerValor($consulta);
		break;
		case 3: //Usuario
			$comp=" and idPromotor=".$parametro;
			$compTitulo="[ <b>Usuario:</b> ".obtenerNombreUsuario($parametro)."]";
		break;
	}
	$consulta="select numEstado,estadoCredito from 752_estadosCredito order by numEstado";
	$resEdo=$con->obtenerFilas($consulta);
?>
	 <table>
        <tr height="23">
            <td align="LEFT"  class="celdaImagenAzul1">
            <span class="speaker">CRÉDITOS (SITUACIÓN) <?php echo $compTitulo?></span>
            </td>
        </tr>
     <tr height="21">
     <?php
			
			switch($tipoTablero)
			{
				case 1:
				
		?>
       		<td>
            
            	<table>
                <tr height="21">
                
                    <td align="LEFT"  class="celdaImagenAzul2" width="180">
                    <span class="letraExt"><a href="javascript:verGrupos()">Ver tablero de control por grupo</a></span>
                    </td>
                    <td width="180" class="celdaImagenAzul2">
                    <span class="letraExt"><a href="javascript:verDistGraficaTablero()">Ver distribución gráfica</a></span>
                    </td>
                    
                    <td>
                    </td>
				</tr>
                </table>
                <br />
			</td>                                    
        
        <?php
				break;
				
			}
		?>
    </tr>
    
    
        <?php
			if($tipoTablero==2)
			{
		?>
                <tr>
                	<td>
                    	<table>
                        <tr height="21">
                            <td align="LEFT"  class="celdaImagenAzul2" width="180">
                            <span class="letraExt"><a href="javascript:verPromotor('<?php echo bE($idGpo)?>')">Ver distribución por promotor</a></span>
                            </td>
                            <td width="180" class="celdaImagenAzul2">
                            <span class="letraExt"><a href="javascript:verDistGraficaTablero('<?php echo bE($idGpo)?>')">Ver distribución gráfica</a></span>
                            </td>
                            
                            <td>
                            </td>
                       </tr>
                       </table>
                	</td>
                </tr>
        <?php
			}
			if($tipoTablero==3)
			{
		?>
                <tr>
                	<td>
                    	<table>
                        <tr height="21">
                            <td width="180" class="celdaImagenAzul2">
                            <span class="letraExt"><a href="javascript:verDistGraficaUsr()">Ver distribución gráfica</a></span>
                            </td>
                            <td>
                            </td>
                       </tr>
                       </table>
                	</td>
                </tr>
        <?php
			}
		?>
        <tr>
        <td>
            <table >
                
                <tr>
                <td style="padding:7px" colspan="3">
                    <table id="tbl_<?php echo $idProceso?>" style="display:">
                    <tr>
                    	<td width="50"></td>
                    	<td align="center" width="410"><span class="copyrigthSinPadding">Etapa</span></td>
                        <td align="center" width="120"><span class="copyrigthSinPadding">Personas físicas</span></td>
                        <td align="center" width="120"><span class="copyrigthSinPadding">Personas morales</span></td>
                                                
                    </tr>
                    <?php
                    $clase="celdaBlancaSinImg";
					
					$totalPF=0;
					$totalPM=0;
                    while($filaEdo=mysql_fetch_row($resEdo))
                    {
						$tagC1="";
						$tagA1="";
						$tagC2="";
						$tagA2="";
                        $consulta="select idCredito from 750_creditos where fechaEntrada>='".$fInicio."' and fechaEntrada<='".$fFin."' and tipoCliente=1 and status=".$filaEdo[0].$comp; //Persona
                        $resCred=$con->obtenerFilas($consulta);
						$nPersona=$con->filasAfectadas;
						$listaCredPFisica="";
						while($filaCred=mysql_fetch_row($resCred))
						{
							if($listaCredPFisica=="")
								$listaCredPFisica=$filaCred[0];
							else
								$listaCredPFisica.=",".$filaCred[0];
						}
						
						
                        if($nPersona=="")
                            $nPersona=0;
						
						if($nPersona>0)
						{
							$tagA1="<a href='javascript:verListadoCredito(\"".bE($listaCredPFisica)."\",\"".bE('<b>LISTADO DE CRÉDITOS EN ESTADO:</b>&nbsp;'.($filaEdo[1])." ".$compTitulo)."\")'>";
							
							$tagC1="</a>";
						}
						
						$totalPF+=$nPersona;
                        $consulta="select idCredito from 750_creditos where fechaEntrada>='".$fInicio."' and fechaEntrada<='".$fFin."' and tipoCliente=2 and status=".$filaEdo[0].$comp; //Empresa
						
						$resCred=$con->obtenerFilas($consulta);
						$nEmpresa=$con->filasAfectadas;
						$listaCredPMoral="";
						while($filaCred=mysql_fetch_row($resCred))
						{
							if($listaCredPMoral=="")
								$listaCredPMoral=$filaCred[0];
							else
								$listaCredPMoral.=",".$filaCred[0];
						}						               
					    
                        if($nEmpresa=="")
                            $nEmpresa=0;
						if($nEmpresa>0)
						{
							$tagA2="<a href='javascript:verListadoCredito(\"".bE($listaCredPMoral)."\",\"".bE('<b>LISTADO DE CRÉDITOS EN ESTADO:</b>&nbsp;'.($filaEdo[1])." ".$compTitulo)."\")'>";
							$tagC2="</a>";
						}
						$totalPM+=$nEmpresa;
							
							
                            ?>
                                <tr height="21">
                                    <td class="<?php echo $clase?>" align="right"><img src="../images/bullet_red.png" /></td>
                                    <td class="<?php echo $clase?>"  align="left">
                                    &nbsp;&nbsp;
                                    <span class="tituloEtiqueta"><?php echo $filaEdo[0]?>.- <?php echo $filaEdo[1]?></span>
                                    </td>
                                    <td class="<?php echo $clase?>"  align="center"><?php echo $tagA1?>
                                    <span class="corpo8_bold">
                                    <?php 
                                        
                                        echo $nPersona;
                                    ?>
                                   </span><?php echo $tagC1?></td>
                                   
                                   <td class="<?php echo $clase?>"  align="center"><?php echo $tagA2?>
                                    <span class="corpo8_bold">
                                    <?php 
                                        
                                        echo $nEmpresa;
                                    ?>
                                   </span><?php echo $tagC2?>
                                   </td>
                                   
                                </tr>
                            <?php
                            
                                if($clase=="celdaImagenAzul3")
                                    $clase="celdaBlancaSinImg";
                                else
                                    $clase="celdaImagenAzul3";
                    }
                ?>
                <tr height="2">
                	<td colspan="4" style="background-color:#003"></td>
                </tr>
                <tr>
                	<td></td>
                    <td align="right"><font color="#FF0000"><b>Total:</b></font>&nbsp;&nbsp;</td>
                    <td align="center"><span class="corpo8"><b><?php echo $totalPF?></b></span></td>
                    <td align="center"><span class="corpo8"><b><?php echo $totalPM?></b></span></td>
                </tr>
            </table>
        </td>
        </tr>
    </table>
<?php	
}

function generarListadoCredito($tipoTablero,$parametro,$tUsuario,$estadoCred,$pagina,$titulo="")
{
	global $con;
	$nElemento=4;
	$comp="";
	$compTitulo="";
	switch($tipoTablero)
	{
		case 1: //Todos
		break;
		case 2: //Grupo
			$consulta="select idUsuario from 807_usuariosVSRoles where codigoRol='38_".$parametro."'";
			$listaUsr=$con->obtenerListaValores($consulta);
			if($listaUsr=="")
				$listaUsr="-1";
			$comp=" and idPromotor in(".$listaUsr.")";
			$consulta="select unidadRol from 4084_unidadesRoles where idUnidadesRoles=".$parametro;
			$compTitulo=$con->obtenerValor($consulta);
		break;
		case 3: //Usuario
			$comp=" and idPromotor=".$parametro;
			$compTitulo="[ <b>Usuario:</b> ".obtenerNombreUsuario($parametro)."]";
		break;
		
	}

	if($tipoTablero!=4)
	{
		$consulta="select * from 750_creditos where tipoCliente=".$tUsuario." and status=".$estadoCred.$comp." order by folio"; //Persona
		$resListado=$con->obtenerFilas($consulta);
		$nRegistros=$con->filasAfectadas;	
		$consulta="select * from 750_creditos where tipoCliente=".$tUsuario." and status=".$estadoCred.$comp." order by folio limit ".($nElemento*($pagina-1)).",".$nElemento; //Persona
		$resListado=$con->obtenerFilas($consulta);
	}
	else
	{
		$consulta="select * from 750_creditos where idCredito in (".$parametro.") order by folio"; //Persona
		$resListado=$con->obtenerFilas($consulta);
		$nRegistros=$con->filasAfectadas;	
		$consulta="select * from 750_creditos where  idCredito in (".$parametro.") order by folio limit ".($nElemento*($pagina-1)).",".$nElemento; //Persona
		$resListado=$con->obtenerFilas($consulta);
	}

	$ct=($nElemento*($pagina-1))+1;
	
	$consulta="select estadoCredito from 752_estadosCredito where numEstado=".$estadoCred;	
	
	$lblEstado=$con->obtenerValor($consulta);
	
	$nPaginas= intval($nRegistros / $nElemento);
		
	$residuo=$nRegistros-($nPaginas*$nElemento);
	if($residuo>0)
		$nPaginas++;

?>
	<table width="100%">
    <tr height="21">
    	<td class="celdaImagenAzul1" >
        <span class="tituloPagina">
        <?php 
		if($titulo=="")
		{
		?>
        <b>LISTADO DE CRÉDITOS EN ESTADO:</b> <?php echo strtoupper($lblEstado)?>&nbsp;<b><?php echo $compTitulo ?></b>
        <?php
		}
		else
			echo $titulo;
		?>
        </span>
        </td>
    </tr>
    <tr>
    <td>
    	<table width="600">
        <tr>
        <td>
    	<span class="corpo8_bold">Página:</span>&nbsp;&nbsp;
        <?php
			$cadPag="";
			for($x=1;$x<=$nPaginas;$x++)
			{
				if($cadPag=="")
					$cadPag="<a href='javascript:irPagina(".$x.")'>".$x."</a>";
				else
					$cadPag.=", "."<a href='javascript:irPagina(".$x.")'>".$x."</a>";
				
			}
			echo $cadPag;
		?>
        </td>
        </tr>
        </table>
    </td>
    </tr>
    </table>
    <br />
<?php		
		
		
	while($fila=mysql_fetch_row($resListado))	
	{
?>
	<table>
    <tr>
    	<td valign="top"></td>
        <td>
		<table>
    	<tr height="21">
        	<td class="celdaImagenAzul2" width="50"><span class="corpo8"><?php echo $ct.".-&nbsp;"?></span></td>
        	<td width="150" class="celdaImagenAzul2">
            	<b>Folio:</b>
            </td>
            <td class="celdaImagenAzul2" width="400">
            
            	<span class="corpo8">
                <a href="javascript:verCredito('<?php echo bE($fila[0])?>')">
            	<?php
					echo $fila[23];
				?>
                </a>
                </span>

            </td>
        </tr>
        <tr height="21">
        	<td></td>
        	<td class="celdaImagenAzul3">
            	<span class="corpo8">
            	Cliente:
                </span>
            </td>
            <td class="celdaImagenAzul3">
            	<span class="corpo8">
            	<?php
					$tUsuario=$fila[2];
					$tCliente="Persona física";
					if($tUsuario=="2")
						$tCliente="Persona moral";
					if($tUsuario=="1")
						$consulta="select concat(nombres,' ',paterno,' ',materno) as nombre from 703_clientes where idCliente=".$fila[1];
					else
						$consulta="select empresa from 700_empresas where idEmpresa=".$fila[1];
				
					echo $con->obtenerValor($consulta);
				?>
                </span>
            </td>
        </tr>
         <tr height="21">
         	<td></td>
        	<td class="celdaImagenAzul3">
            	<span class="corpo8">
            	Tipo:
                </span>
            </td>
            <td class="celdaImagenAzul3">
            	<span class="corpo8">
            	<?php
					echo $tCliente;
				?>
                </span>
            </td>
        </tr>
        
         <tr height="21">
         	<td></td>
        	<td class="celdaImagenAzul3">
            	<span class="corpo8">
            	Promotor:
                </span>
            </td>
            <td class="celdaImagenAzul3">
            	<span class="corpo8">
            	<?php
					echo obtenerNombreUsuario($fila[3]);
				?>
                </span>
            </td>
        </tr>
        <tr height="21">
        	<td></td>
        	<td class="celdaImagenAzul3">
            	<span class="corpo8">
            	Fecha Entrada:
                </span>
            </td>
            <td class="celdaImagenAzul3">
            	<span class="corpo8">
            	<?php
					echo date('d/m/Y',strtotime($fila[4]));
				?>
                </span>
            </td>
        </tr>
        <tr height="21">
        	<td></td>
        	<td class="celdaImagenAzul3">
            	<span class="corpo8">
            	Monto Solicitado:
                </span>
            </td>
            <td class="celdaImagenAzul3">
            	<span class="corpo8">
            	<?php
					echo "$ ".number_format($fila[8],2,".",",");
				?>
                </span>
            </td>
        </tr>
        <tr height="21">
        	<td></td>
        	<td class="celdaImagenAzul3">
            	<span class="corpo8">
            	Monto Autorizado:
                </span>
            </td>
            <td class="celdaImagenAzul3">
            	<span class="corpo8">
            	<?php
					echo "$ ".number_format($fila[9],2,".",",");
				?>
                </span>
            </td>
        </tr>
    </table>
    	</td>
    </tr>
    </table>
    <bR /><br>
<?php		
		$ct++;
	}
}

function crearCredito($idCliente,$tCliente)
{
	global $con;
	if($tCliente==2)
		$consulta="select idResponsable from 700_empresas where idEmpresa=".$idCliente;
	else
		$consulta="select idResponsable from 703_clientes where idCliente=".$idCliente;
	$idPromotor=$con->obtenerValor($consulta);
	$consulta="insert into 750_creditos(idCliente,tipoCliente,fechaEntrada,idPromotor) values(".$idCliente.",".$tCliente.",'".date('Y-m-d')."',".$idPromotor.")";
	if($con->ejecutarConsulta($consulta))
	{
		$idCredito=$con->obtenerUltimoID();
		$folio=str_pad($idCredito,7,"0",STR_PAD_LEFT);
		$consulta="update 750_creditos set folio='".$folio."' where idCredito=".$idCredito;
		if($con->ejecutarConsulta($consulta))
			echo "window.parent.mostrarCreditoNuevo(".$idCredito.");";
		return true;
	}	
	return false;
}

function llenarDatosRepresentante($idRep,$funPadreEject,$tCliente=1)
{
	global $con;
	if($tCliente==1)
		$consulta="select concat(nombres,' ',paterno,' ',materno) as nombre,email,telefonos from 703_clientes where idCliente=".$idRep;
	else
		$consulta="select empresa,email,telefonos from 700_empresas where idEmpresa=".$idRep;
	$fila=$con->obtenerPrimeraFila($consulta);
	
	$cadObj='[{"idRep":"'.$idRep.'","nombre":"'.($fila[0]).'","mail":"'.$fila[1].'","tel":"'.$fila[2].'"}]';
	echo "window.parent.".$funPadreEject."('".$cadObj."');return;";
	return true;	
}

function vincularClienteCredito($idCredito,$idCliente)
{
	global $con;
	$consulta="insert into 715_principalesClientes(idClientePrincipal,tipoCliente,idCredito,fechaRegistro,idResponsable) 
				values(".$idCliente.",2,".$idCredito.",'".date('Y-m-d')."',".$_SESSION["idUsr"].")";
	if($con->ejecutarConsulta($consulta))
		echo "window.parent.actualizaCredito(".$idCredito.");";
	return true;
}

//------------------------RC

function evaluarExpresion($token,$tipoToken,$objParams=null)
{
	$idConsulta=$tipoToken*-1;
	$cond="";
	switch($idConsulta)
	{
		case 1:
			$cond=str_replace('@Fecha actual sistema',date('Y-m-d'),$token);
		break;
		case 2:
			$cond=str_replace('@Usuario sesión',$_SESSION["idUsr"],$token);
		break;
		case 3:
			$idUsuario="-1";
			if((isset($objParams))&&(isset($objParams->idUsuario)))
				$idUsuario=$objParams->idUsuario;
			$cond=str_replace('@Usuario proceso',$idUsuario,$token);
		break;
		case 4:
			$fecha=date('Y-m-d');
			if((isset($objParams))&&(isset($objParams->fechaContratacion)))
				$fecha=$objParams->fechaContratacion;
			$cond=str_replace('@Fecha contratación usuario',date('Y-m-d',strtotime($fecha)),$token);
		break;
		case 5:
			$sueldoBase=0;
			if((isset($objParams))&&(isset($objParams->sueldoBase)))
				$sueldoBase=$objParams->sueldoBase;
			$cond=str_replace('@Sueldo base',$sueldoBase,$token);
		break;
	}
	return $cond;
}

function generarExpresionPHP($idConsulta,&$arrDependencias)
{
	global $con;
	$consulta="select * from 992_tokensConsulta where idConsulta=".$idConsulta;
	$resQuery=$con->obtenerFilas($consulta);
	$cad='';	
	while($fila=mysql_fetch_row($resQuery))
	{
		if($fila[3]<0)
		{
			$arrToken=array();
			$arrToken[0]=$fila[1];
			$arrToken[1]=$fila[3];
			$arrToken[2]=$fila[0];
			if(existeValorMatriz($arrDependencias,$fila[3],1)=="-1")
				array_push($arrDependencias,$arrToken);
		}
		$cad.=$fila[1]." ";
	}
	return $cad;
}

function generarExpresionPHPBD($idConsulta,&$arrDependencias)
{
	global $con;
	$consulta="select comp1,comp2,comp3 from 991_consultasSql where idConsulta=".$idConsulta;
	$filaCons=$con->obtenerPrimeraFila($consulta);
	switch($filaCons[2])
	{
		case 1:  //Promedio
			$cad="select avg(".$filaCons[1].") from ".$filaCons[0]." where ";
		break;
		case 2: //Proyecccion
			$cad="select ".$filaCons[1]." from ".$filaCons[0]." where ";
		break; //Sumatoria
		case 3:
			$cad="select sum(".$filaCons[1].") from ".$filaCons[0]." where ";
		break; //Val max
		case 4:
			$cad="select max(".$filaCons[1].") from ".$filaCons[0]." where ";
		break;
		case 5: //Val minimo
			$cad="select min(".$filaCons[1].") from ".$filaCons[0]." where ";
		break;
		case 6: //Num Reg
			$cad="select count(".$filaCons[1].") from ".$filaCons[0]." where ";
		break;
		case 7: // Raiz q
			$cad="select sqrt(".$filaCons[1].") from ".$filaCons[0]." where ";
		break;
	}
	
	$consulta="select * from 992_tokensConsulta where idConsulta=".$idConsulta;
	$resQuery=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($resQuery))
	{
		if($fila[3]<0)
		{
			$arrToken=array();
			$arrToken[0]=$fila[1];
			$arrToken[1]=$fila[3];
			if(existeValorMatriz($arrDependencias,$fila[3],1)=="-1")
				array_push($arrDependencias,$arrToken);
		}
		$cad.=$fila[1]." ";
	}
	$cad2=$cad;
	return $cad2;
}

function ejecutarExpresion($idConsulta,$objParams=null,$arrValorParametros=null)
{
	global $con;
	$resultado="";
	$arrDependencias=array();
	$expresion=generarExpresionPHP($idConsulta,$arrDependencias);

	//$arrParametros=$con->obtenerFilasArreglo1D($consulta);
	if($idConsulta==35)
	{
		if($objParams->idUsuario==337)
			return 1000;
		return 0;
	}
	if($idConsulta==37)
	{
		if($objParams->idUsuario==337)
			return 2000;
		return 0;
	}
	if((sizeof($arrDependencias)==0)&&(sizeof($arrValorParametros)==0))
	{
		return ejecutarOperacionCodigo($expresion);
	}
	else
	{
		foreach($arrDependencias as $dependencia)
		{
			$idCons=$dependencia[1]*-1;
			$consulta="select nombreConsulta,tipoConsulta from 991_consultasSql where idConsulta=".$idCons;
			$filaCon=$con->obtenerPrimeraFila($consulta);
			$valReemplazo='@'.$filaCon[0];
			$tipoConsulta=$filaCon[1];
			$resultado=resolverDependencia($idCons,$objParams,$tipoConsulta,$dependencia);
			$expresion=str_replace($valReemplazo,$resultado,$expresion);			
		}
		
		if(($arrValorParametros!=null)&&(sizeof($arrValorParametros)>0))
		{
			foreach($arrValorParametros as $llave=>$parametro)
			{
				$expresion=str_replace("$".$llave,$arrValorParametros[$llave],$expresion);
			}
		}
		if($expresion!="")
			return ejecutarOperacionCodigo($expresion);
		else
			return "";
	}
}

function obtenerValorConsultaExpresion($idConsulta,$objParams=null)
{
	global $con;
	$resultado="";
	$arrDependencias=array();
	$expresion=generarExpresionPHPBD($idConsulta,$arrDependencias);
	
	$consulta="select comp3 from 991_consultasSql where idConsulta=".$idConsulta;
	$tipoOp=$con->obtenerValor($consulta);
	
	if(sizeof($arrDependencias)==0)
	{
		$valor= $con->obtenerValor($expresion);
		if(($tipoOp<>2)&&($valor==''))
			$valor=0;
			
		return $valor;
	}
	else
	{
		foreach($arrDependencias as $dependencia)
		{
			$idCons=$dependencia[1]*-1;
			$consulta="select nombreConsulta,tipoConsulta from 991_consultasSql where idConsulta=".$idCons;
			$filaCon=$con->obtenerPrimeraFila($consulta);
			$valReemplazo='@'.$filaCon[0];
			$tipoConsulta=$filaCon[1];
			/*switch($idCons)
			{
				case 1:
					$resultado="".date("Y-m-d")."";
				break;
				case 2:
					$resultado=$_SESSION["idUsr"];
				break;
				case 3:
					$resultado="-1";
					if((isset($objParams))&&(isset($objParams->idUsuario)))
						$resultado=$objParams->idUsuario;
				break;
				case 4:
					$resultado="".date('Y-m-d')."";
					if((isset($objParams))&&(isset($objParams->fechaContratacion)))
						$resultado=$objParams->fechaContratacion;
				break;
				case 5:
					$resultado=0;
					if((isset($objParams))&&(isset($objParams->sueldoBase)))
						$resultado=$objParams->sueldoBase;
				break;
				default:
					switch($tipoConsulta)
					{
						case 2:
							$resultado=obtenerValorConsultaExpresion($idConsulta,$objParams);
						break;
						case 3:
							$resultado=ejecutarExpresion($idCons,$objParams);
						break;
					}
				break;
			}*/
			$resultado=resolverDependencia($idCons,$objParams,$tipoConsulta,$dependencia);
			$expresion=str_replace($valReemplazo,$resultado,$expresion);			
		}
		if($expresion!="")
			$valor= $con->obtenerValor($expresion);
		else
			$valor= "";
		if(($tipoOp<>2)&&($valor==''))
			$valor=0;
		return $valor;	
	}
}

function resolverDependencia($idCons,$objParams,$tipoConsulta,$dependencia)
{
	global $con;
	$resultado="";
	switch($idCons)
  	{
	  case 1: //Fecha actual sistema
		  $resultado="".date("Y-m-d")."";
	  break;
	  case 2: //Usuario sesión
		  $resultado=$_SESSION["idUsr"];
	  break;
	  case 3: //Usuario proceso
		  $resultado="-1";
		  if((isset($objParams))&&(isset($objParams->idUsuario)))
			  $resultado=$objParams->idUsuario;
	  break;
	  case 4: //Fecha contratación usuario
		  $resultado="".date('Y-m-d')."";

		  if((isset($objParams))&&(isset($objParams->fechaContratacion)))
			  $resultado="'".$objParams->fechaContratacion."'";
	  break;
	  case 5: //Sueldo base
		  $resultado=0;
		  if((isset($objParams))&&(isset($objParams->sueldoBase)))
			  $resultado=$objParams->sueldoBase;
	  break;
	  case 6: //Núm. faltas
		  $resultado=0;
		  if((isset($objParams))&&(isset($objParams->nFaltas)))
			  $resultado=$objParams->nFaltas;
	  break;
	  case 7: //Núm. retardos
		  $resultado=0;
		  if((isset($objParams))&&(isset($objParams->nRetardos)))
			  $resultado=$objParams->nRetardos;
	  break;
	  case 8: //Ciclo nómina
		  $resultado=0;
		  if((isset($objParams))&&(isset($objParams->ciclo)))
			  $resultado=$objParams->ciclo;
	  break;
	  case 9: //Quincena nómina
		  $resultado=0;
		  if((isset($objParams))&&(isset($objParams->quincena)))
			  $resultado=$objParams->quincena;
	  break;
	  
	  default:
		  switch($tipoConsulta)
		  {
			  case 2:
				  $resultado=obtenerValorConsultaExpresion($idCons,$objParams);
			  break;
			  case 3:
			  
				  $consulta="select idParametro,parametro from 993_parametrosConsulta where idConsulta=".$idCons;
				  $resParam=$con->obtenerFilas($consulta);
				  $objArrParam=array();
				  if(isset($dependencia[2]))
				  {
					  $idToken=$dependencia[2];
					  while($filaParam=mysql_fetch_row($resParam))
					  {
						  $consulta="select valor,tipoValor from 994_valoresTokens where idToken=".$idToken." and idParametro=".$filaParam[0];	
						  $filaValor=$con->obtenerPrimeraFila($consulta);
						  if($filaValor[1]=="1")
							  $valor=$filaValor[0];
						  else
							  $valor="$".$filaValor[0];
						  $objArrParam[$filaParam[1]]=$valor;
					  }
				  }
				  $resultado=ejecutarExpresion($idCons,$objParams,$objArrParam);
			  break;
		  }
			  
	  break;
  	}
	return $resultado;
}

function ejecutarOperacionCodigo($codigo)
{
	$resultadoFinal="";
	$codFinal=trim($codigo);
	if(substr($codFinal,strlen($codFinal)-1,1)!=";")
		$codFinal=$codFinal.';';
	//echo $codFinal;
	eval ($codFinal);
	
	return $resultadoFinal;
}

function realizarCalculosIndividuales(&$obj,$arrCalculosDef,$arrAcumuladores)
{
	global $con;
	$calculoInd=array();
	$consulta="select * from 662_calculosNomina where idUsuarioAplica=".$obj->idUsuario." order by orden";
	

	$resCalculos=$con->obtenerFilas($consulta);
	while($filaCalculo=mysql_fetch_row($resCalculos))
	{
		$afectacionNomina=$filaCalculo[2];
		$arrParametros=array();
		$idCalculo=$filaCalculo[0];
		$considerar=true;
		switch($afectacionNomina)
		{
			case 1: // Permanente
			break;
			case 2: //No afectar
				if($filaCalculo[3]=="")
					$considerar=false;
				else
				{
					if(($filaCalculo[3]>=$obj->ciclo)&&($filaCalculo[4]>=$obj->quincena)&&($filaCalculo[5]<$filaCalculo[10]))
						$considerar=true;
					else
						$considerar=false;
				}
			break;
			case 3: //aplicar a quincenas
				if(($filaCalculo[3]>=$obj->ciclo)&&($filaCalculo[4]>=$obj->quincena)&&($filaCalculo[5]<$filaCalculo[10]))
					$considerar=true;
				else
					$considerar=false;
			
			break;
		}

		if($considerar)
		{
			$obj->arrCalculosIndividuales[$idCalculo]["orden"]=$filaCalculo[9];
			$obj->arrCalculosIndividuales[$idCalculo]["tipoCalculo"]=$filaCalculo[8];
			$consulta="select idParametro,parametro from 993_parametrosConsulta where idConsulta=".$filaCalculo[1];
			$resParam=$con->obtenerFilas($consulta);
			$cadParametros='"objDatosUsr":""';	
			while($filaParam=mysql_fetch_row($resParam))
			{
				$consulta="select valor,tipoValor from 663_valoresCalculos where idCalculo=".$filaCalculo[0]." and idParametro=".$filaParam[0];
				$filaValor=$con->obtenerPrimeraFila($consulta);
				$valor=$filaValor[0];
				switch($filaValor[1])
				{
					case 2:
						if(isset($obj->arrCalculosIndividuales[$valor]))
							$valor=$obj->arrCalculosIndividuales[$valor]["valorCalculado"];
						else
							if(isset($obj->arrCalculosGlobales[$valor]))
								$valor=$obj->arrCalculosGlobales[$valor]["valorCalculado"];
							else
								$valor=0;
						
					break;
					case 21:
						if(isset($arrAcumuladores[$valor]))
							$valor=$arrAcumuladores[$valor];
						else
							$valor=0;
					break;
				}
				
				if($cadParametros=="")
					$cadParametros='"'.$filaParam[1].'":"'.$valor.'"';
				else
					$cadParametros.=',"'.$filaParam[1].'":"'.$valor.'"';
				
			}
			$cadParametros='{'.$cadParametros.'}';
			
			$objParametros=json_decode($cadParametros);
			$objParametros->objDatosUsr=$obj;
			$valCalculado=resolverExpresionCalculoPHP($filaCalculo[1],$objParametros);
			$consulta="select idAcumulador,operacion from 666_acumuladoresCalculo where idCalculo=".$idCalculo;
			$resAcum=$con->obtenerFilas($consulta);
			while($filaAcumulador=mysql_fetch_row($resAcum))
			{
				if(isset($arrAcumuladores[$filaAcumulador[0]]))	
				{
					switch($filaACumulador[1])	
					{
						case '+':
							$arrAcumuladores[$filaAcumulador[0]]+=$valCalculado;
						break;
						case '-':
							$arrAcumuladores[$filaAcumulador[0]]-=$valCalculado;
						break;
						case '*':
							$arrAcumuladores[$filaAcumulador[0]]*=$valCalculado;
						break;
						case '/':
							if($valor!=0)
								$arrAcumuladores[$filaAcumulador[0]]/=$valCalculado;
							else
								$arrAcumuladores[$filaAcumulador[0]]=0;
						break;
						case '=':
							$arrAcumuladores[$filaAcumulador[0]]=$valCalculado;
						break;	
					}
				}
			}

			$obj->arrCalculosIndividuales[$idCalculo]["nombreCalculo"]=$arrCalculosDef[$idCalculo];
			$obj->arrCalculosIndividuales[$idCalculo]["valorCalculado"]=$valCalculado;
			$obj->arrCalculosIndividuales[$idCalculo]["distriCuentas"]=array();
			$consulta="select codCuentaAfectacion,codCuentaAfectacionSimple,porcentaje,tipoAfectacion,idEstructura,idBeneficiario,tipoBeneficiario,idTipoPresupuesto
						from 661_afectacionesCuentasDeducPercepciones where idDeduccionPercepcion=".$idCalculo;
			$resAfectCuentas=$con->obtenerFilas($consulta);
			$objCuenta=array();
			while($filaAfectCuentas=mysql_fetch_row($resAfectCuentas))
			{
				$objCuenta["codCuentaAfectacion"]=$filaAfectCuentas[0];
				$objCuenta["codCuentaAfectacionSimple"]=$filaAfectCuentas[1];
				$objCuenta["porcentaje"]=$filaAfectCuentas[2];
				$objCuenta["tipoAfectacion"]=$filaAfectCuentas[3];
				$objCuenta["idEstructura"]=$filaAfectCuentas[4];
				$objCuenta["idBeneficiario"]=$filaAfectCuentas[5];
				$objCuenta["tipoBeneficiario"]=$filaAfectCuentas[6];
				$objCuenta["valorAsignado"]=$valCalculado*($objCuenta["porcentaje"]/100);
				$consulta="select codigo from 508_tiposPresupuesto where idTipoPresupuesto=".$filaAfectCuentas[7];
				$codigoPre=$con->obtenerValor($consulta);				
				$objCuenta["tipoPresupuesto"]=$codigoPre;
				
				if($filaAfectCuentas[5]=="0")
				{
					$consulta="select cu.cuenta,cu.idBanco from 801_adscripcion a,823_cuentasUsuario cu where cu.idCuentaUsuario=a.idCuentaDeposito and a.idUsuario=".$obj->idUsuario;

				}
				else
				{
					if($filaAfectCuentas[6]==1)
						$consulta="select txtCuenta,cmbbanco from _217_tablaDinamica where id__217_tablaDinamica=".$objCuenta["idBeneficiario"];
					else
						$consulta="select txtCuenta,cmbbanco from _216_tablaDinamica where id__216_tablaDinamica=".$objCuenta["idBeneficiario"];
					
				}

				$filaCuenta=$con->obtenerPrimeraFila($consulta);
				$objCuenta["cuentaBancaria"]=$filaCuenta[0];
				$objCuenta["idBanco"]=$filaCuenta[1];
				array_push($obj->arrCalculosIndividuales[$idCalculo]["distriCuentas"],$objCuenta);
			}
		}
		
	}
}

function realizarCalculosGlobales(&$obj,$arrCalculosDef,&$arrAcumuladores,&$cacheCalculos,$idPerfil=1,$precision=2,$accionPrecision=2)
{
	global $con;
	global $estadisticasCalculo;
	global $acumuladoBaseGravablePercepcion;
	global $acumuladoBaseGravableDeduccion;
	
	
	$modoDebugger=false;
	$calculoInd=array();
	$consulta="select * from 662_calculosNomina where idUsuarioAplica is null and idPerfil=".$idPerfil." order by orden";

	$resCalculos=$con->obtenerFilas($consulta);
	if($modoDebugger)
	{
		echo '<br><br><span class="letraRojaSubrayada8">Calculando n&oacute;mina de:</span> <span>'.$obj->idUsuario.'</span><br><br>';	
	}
	
	$pruebaArreglo=array();
	$referencia=NULL;
	while($filaCalculo=mysql_fetch_row($resCalculos))
	{
		$afectacionNomina=$filaCalculo[2];
		$arrParametros=array();
		$idCalculo=$filaCalculo[0];
		$considerar=true;
		switch($afectacionNomina)
		{
			case 1: // Permanente
			break;
			case 2: //No afectar
				if($filaCalculo[3]=="")
					$considerar=false;
				else
				{
					if(($filaCalculo[3]>=$obj->ciclo)&&($filaCalculo[4]>=$obj->quincena)&&($filaCalculo[5]<$filaCalculo[10]))
						$considerar=true;
					else
						$considerar=false;
				}
			break;
			case 3: //aplicar a quincenas
				if(($filaCalculo[3]>=$obj->ciclo)&&($filaCalculo[4]>=$obj->quincena)&&($filaCalculo[5]<$filaCalculo[10]))
					$considerar=true;
				else
					$considerar=false;
			
			break;
		}
		
		
		if(($obj->objConfiguracion[1]!=10)&&($obj->objConfiguracion[1]!=11))
		{
			$consulta="select cod_Puesto,tipoContratacion from 801_adscripcion where idUsuario=".$obj->idUsuario;
		
			$filaAds=$con->obtenerPrimeraFila($consulta);
			if(!$filaAds)
			{
				//echo "<font color='red'>El usuario ".$obj->idUsuario." no existe</font><br>";
			//	return;	
			}
		}
		
		
		
		$idTabulacion=$obj->puesto;
		$tipoPuesto=$obj->tipoContratacion;
		if(isset($arrCalculosDef[$idCalculo]))
		{
			$consulta="select count(*) from 660_afectacionesDeducPercepciones where idDeduccionPercepcion=".$idCalculo;	
			$nReg=$con->obtenerValor($consulta);
			if($nReg==0)
			{
				$consulta="select count(*) from 9115_calculosVSPuestos where idCalculo=".$idCalculo;	
				$nReg=$con->obtenerValor($consulta);
			}
			if($nReg>0)	
			{
				$consulta="select idTipoPuestoAfecta from 660_afectacionesDeducPercepciones where idDeduccionPercepcion=".$idCalculo." and afectacion=".$tipoPuesto;
				$idTipoPuestoAfecta=$con->obtenerValor($consulta);
				if($idTipoPuestoAfecta=="")
				{
					$consulta="SELECT idPuesto FROM `9115_calculosVSPuestos` WHERE idCalculo=".$idCalculo." AND cvePuesto=".$obj->puesto."";
					$idTipoPuestoAfecta=$con->obtenerValor($consulta);
					if($idTipoPuestoAfecta=="")
						$considerar=false;
				}
			}
		}
		else
			$considerar=false;
		if($considerar)
		{
			$obj->arrCalculosGlobales[$idCalculo]["idConsulta"]=$filaCalculo[1];
			$obj->arrCalculosGlobales[$idCalculo]["orden"]=$filaCalculo[9];
			$obj->arrCalculosGlobales[$idCalculo]["tipoCalculo"]=$filaCalculo[8];//2 Percepcion 1 Deduccion
			$consulta="select idParametro,parametro from 993_parametrosConsulta where idConsulta=".$filaCalculo[1];
			if($modoDebugger)
				echo $consulta;
			$resParam=$con->obtenerFilas($consulta);
			$cadParametros='"objDatosUsr":"","marcaParametro":""';	
			$marcaParam="";
			while($filaParam=mysql_fetch_row($resParam))
			{
				$consulta="select valor,tipoValor from 663_valoresCalculos where idCalculo=".$filaCalculo[0]." and idParametro=".$filaParam[0];
				if($modoDebugger)
					echo $consulta;
				$filaValor=$con->obtenerPrimeraFila($consulta);
				$valor=$filaValor[0];
				switch($filaValor[1])
				{
					case 2:
						if(isset($obj->arrCalculosGlobales[$valor]))
							$valor=$obj->arrCalculosGlobales[$valor]["valorCalculado"];
						else
							$valor=0;
					break;
					case 21:
						if(isset($arrAcumuladores[$valor]))
							$valor=$arrAcumuladores[$valor];
						else
							$valor=0;
					break;
				}
				if($cadParametros=="")
					$cadParametros='"'.$filaParam[1].'":"'.$valor.'"';
				else
					$cadParametros.=',"'.$filaParam[1].'":"'.$valor.'"';
				
				if($marcaParam=="")
				{
					$marcaParam=str_replace("'","",str_replace('$',"_",str_replace(".","_",$valor)));
					
				}
				else
					$marcaParam.="_".str_replace("'","",str_replace('$',"_",str_replace(".","_",$valor)));
			}
			
			
			$cadParametros='{'.$cadParametros.'}';
			
			$objParametros=json_decode($cadParametros);
			$objParametros->objDatosUsr=$obj;
			
			$objParametros->marcaParametro=$marcaParam;
			$valCalculado=0;

			$normalizarValor=true;

			if((gettype($arrCalculosDef[$idCalculo])=='array')&&(isset($obj->objImportacion))&&(sizeof($obj->objImportacion)>0))
			{
				if(isset($obj->objImportacion[$arrCalculosDef[$idCalculo]["columnaImportacion"]]))
				{
					$valCalculado=trim($obj->objImportacion[$arrCalculosDef[$idCalculo]["columnaImportacion"]]);
					
					$normalizarValor=false;
				}
				if($valCalculado=="")
					$valCalculado=0;
				
			}
			else
			{
				
				$valCalculado=str_replace("'","",resolverExpresionCalculoPHP($filaCalculo[1],$objParametros,$cacheCalculos));
			}
			if($valCalculado=="")
				$valCalculado=0;
			if((is_numeric($valCalculado))&&($normalizarValor))	
			{
				switch($accionPrecision)
				{
					case 1:
						$valCalculado=truncarValor($valCalculado,$precision);
					break;
					case 2:
						$valCalculado=str_replace(",","",number_format($valCalculado,$precision));
					break;
				}
			}
			
			if($valCalculado=="''")
				$valCalculado=0;
			$consulta="select idAcumulador,operacion from 666_acumuladoresCalculo where idCalculo=".$idCalculo;
			if($modoDebugger)
				echo $consulta;
			$resAcum=$con->obtenerFilas($consulta);
			
			while($filaAcumulador=mysql_fetch_row($resAcum))
			{
				if(isset($arrAcumuladores[$filaAcumulador[0]]))	
				{
					switch($filaAcumulador[1])	
					{
						case '+':
							$arrAcumuladores[$filaAcumulador[0]]+=$valCalculado;
						break;
						case '-':
							$arrAcumuladores[$filaAcumulador[0]]-=$valCalculado;
						break;
						case '*':
							$arrAcumuladores[$filaAcumulador[0]]*=$valCalculado;
						break;
						case '/':
							if($valor!=0)
								$arrAcumuladores[$filaAcumulador[0]]/=$valCalculado;
							else
								$arrAcumuladores[$filaAcumulador[0]]=0;
						break;
						case '=':
							$arrAcumuladores[$filaAcumulador[0]]=$valCalculado;
						break;	
					}
				}
			}

			if(gettype($arrCalculosDef[$idCalculo])=='array')
				$obj->arrCalculosGlobales[$idCalculo]["nombreCalculo"]=$arrCalculosDef[$idCalculo]["etiquetaCalculo"];
			else
				$obj->arrCalculosGlobales[$idCalculo]["nombreCalculo"]=$arrCalculosDef[$idCalculo];
			$obj->arrCalculosGlobales[$idCalculo]["valorCalculado"]=$valCalculado;
			$obj->arrCalculosGlobales[$idCalculo]["idCategoriaSAT"]=$filaCalculo[12];
			if($obj->arrCalculosGlobales[$idCalculo]["tipoCalculo"]==1) //Deduccion
			{
				$obj->arrCalculosGlobales[$idCalculo]["importeGravado"]=0;
				$obj->arrCalculosGlobales[$idCalculo]["importeExento"]=$valCalculado;
			}
			else
			{
				if($obj->arrCalculosGlobales[$idCalculo]["tipoCalculo"]==2)
				{
					$obj->arrCalculosGlobales[$idCalculo]["importeGravado"]=$valCalculado;
					$obj->arrCalculosGlobales[$idCalculo]["importeExento"]=0;
				}
				else
				{
					$obj->arrCalculosGlobales[$idCalculo]["importeGravado"]=0;
					$obj->arrCalculosGlobales[$idCalculo]["importeExento"]=$valCalculado;
				}
			}
			
			if(($filaCalculo[13]!="")&&($filaCalculo[13]!="-1"))
			{
				if($valCalculado=="")
					$valCalculado=0;
				$cObjFuncion='{"importe":"'.$valCalculado.'"}';
				$oFuncion=json_decode($cObjFuncion);
				$cache=NULL;
				
				
				$arrGravamen=resolverExpresionCalculoPHP($filaCalculo[13],$oFuncion,$cache);
		
				$obj->arrCalculosGlobales[$idCalculo]["importeGravado"]=$arrGravamen["importeGravado"];
				$obj->arrCalculosGlobales[$idCalculo]["importeExento"]=$arrGravamen["importeExento"];
				
				
			}
			
			if($obj->arrCalculosGlobales[$idCalculo]["tipoCalculo"]==2)
				$acumuladoBaseGravablePercepcion+=$obj->arrCalculosGlobales[$idCalculo]["importeGravado"];
			else
				if($obj->arrCalculosGlobales[$idCalculo]["tipoCalculo"]==1)
					$acumuladoBaseGravableDeduccion+$obj->arrCalculosGlobales[$idCalculo]["importeGravado"];
			
			$referencia=&$obj->arrCalculosGlobales[$idCalculo];
			$referencia["distriCuentas"]=array();
			$arrCuentas=array();
			$consulta="select codCuentaAfectacion,codCuentaAfectacionSimple,porcentaje,tipoAfectacion,idEstructura,idBeneficiario,tipoBeneficiario,idTipoPresupuesto
						from 661_afectacionesCuentasDeducPercepciones where idDeduccionPercepcion=".$idCalculo;
			
			if($modoDebugger)
				echo $consulta;
			$resAfectCuentas=$con->obtenerFilas($consulta);
			
			$objCuenta=array();
			while($filaAfectCuentas=mysql_fetch_row($resAfectCuentas))
			{
				$objCuenta["codCuentaAfectacion"]=$filaAfectCuentas[0];
				$objCuenta["codCuentaAfectacionSimple"]=$filaAfectCuentas[1];
				$objCuenta["porcentaje"]=$filaAfectCuentas[2];
				$objCuenta["tipoAfectacion"]=$filaAfectCuentas[3];
				$objCuenta["idEstructura"]=$filaAfectCuentas[4];
				$objCuenta["idBeneficiario"]=$filaAfectCuentas[5];
				$objCuenta["tipoBeneficiario"]=$filaAfectCuentas[6];
				$objCuenta["valorAsignado"]=$valCalculado*($objCuenta["porcentaje"]/100);
				$consulta="select codigo from 508_tiposPresupuesto where idTipoPresupuesto=".$filaAfectCuentas[7];
				if($modoDebugger)
					echo $consulta;
				$codigoPre=$con->obtenerValor($consulta);				
				$objCuenta["tipoPresupuesto"]=$codigoPre;
				$objCuenta["idTipoPresupuesto"]=$filaAfectCuentas[7];
				if($filaAfectCuentas[5]!='')
				{
					if($filaAfectCuentas[5]=="0")
						$consulta="select cu.cuenta,cu.idBanco from 801_adscripcion a,823_cuentasUsuario cu where cu.idCuentaUsuario=a.idCuentaDeposito and a.idUsuario=".$obj->idUsuario;
					else
					{
						if($filaAfectCuentas[6]==1)
							$consulta="select txtCuenta,cmbbanco from _217_tablaDinamica where id__217_tablaDinamica=".$objCuenta["idBeneficiario"];
						else
							$consulta="select txtCuenta,cmbbanco from _216_tablaDinamica where id__216_tablaDinamica=".$objCuenta["idBeneficiario"];
						
					}
					if($modoDebugger)
						echo $consulta;
					$filaCuenta=$con->obtenerPrimeraFila($consulta);
					$objCuenta["cuentaBancaria"]=$filaCuenta[0];
					$objCuenta["idBanco"]=$filaCuenta[1];
				}
				else
					$objCuenta["cuentaBancaria"]="";
				
				
				array_push($arrCuentas,$objCuenta);
			}
			$referencia["distriCuentas"]=$arrCuentas;
		}
	}
	
	

}

function generarEntradaLibroNomina($quincena,$ciclo)
{
	global $con;
	$nLibro="Libro Nómina (".$quincena."-".$ciclo.")";
	$query="select idLibro from 515_librosDiarios where tituloLibro='".$nLibro."'";
	$idLibro=$con->obtenerValor($query);
	$x=0;
	$consulta[$x]="begin";
	$x++;
	if($idLibro!="")
		$consulta[$x]="delete from 514_asientos where idLibro=".$idLibro;
	else
		$consulta[$x]="insert into 515_librosDiarios(tituloLibro) values('".$nLibro."')";
	$x++;
	if($con->ejecutarBloque($consulta))
	{
		
		if($idLibro=="")
		{
			$idLibro=$con->obtenerUltimoID();
			$queryAux[0]="insert into 516_rolesVSLibros(idLibro,rol,permisos) values(".$idLibro.",'1_0','CRM')";
			$queryAux[1]="commit";
			
			if($con->ejecutarBloque($queryAux))
				return $idLibro;
			else
				return false;

		}
		
		$query="commit";
		if($con->ejecutarConsulta($query))
			return $idLibro;
		else
			return false;
	}
	return false;
	
}

function guardarAsientoCalculoNomina($obj,$idLibro)
{

	global $con;
	$x=0;
	return;
	$consulta=array();
	$arrCalculosGlobales=$obj->arrCalculosGlobales;
	
	foreach($arrCalculosGlobales as $objCalculo)
	{
		$arrCuentas=$objCalculo["distriCuentas"];
		
		foreach($arrCuentas as $distribucion)
		{
			$montoDebe=$distribucion["valorAsignado"];
			$montoHaber=$distribucion["valorAsignado"];
			if($distribucion["tipoAfectacion"]==1)
				$montoHaber=0;
			else
				$montoDebe=0;
			$beneficiario="NULL";
			if($distribucion["idBeneficiario"]!='')
				$beneficiario=$distribucion["idBeneficiario"];
			$tBeneficiario="NULL";
			if($distribucion["tipoBeneficiario"]!='')
				$tBeneficiario=$distribucion["tipoBeneficiario"];
			$consulta[$x]="insert into 514_asientos(fechaMovimiento,idUsuario,tipoPresupuesto,cuentaBanco,cuentaMascara,cuentaSimple,montoDebe,montoHaber,
							idTipocumento,noDocumento,idLibro,concepto,estado,idUsuarioEmpleado,beneficiario,tipoBeneficiario) values
							('".date("Y-m-d")."',".$_SESSION["idUsr"].",'".$distribucion["tipoPresupuesto"]."','".$distribucion["cuentaBancaria"]."','".$distribucion["codCuentaAfectacion"]."',
							'".$distribucion["codCuentaAfectacionSimple"]."',".$montoDebe.",".$montoHaber.",3,'NOM-".$obj->quincena.$obj->ciclo."',".$idLibro.",
							'".$objCalculo["nombreCalculo"]."',1,".$obj->idUsuario.",".$beneficiario.",".$tBeneficiario.")";
			$x++;	
		}
		
	}
	
	$arrCalculosIndividuales=$obj->	arrCalculosIndividuales;
	foreach($arrCalculosIndividuales as $objCalculo)
	{
		$listValores="";
		foreach($objCalculo["distriCuentas"] as $distribucion)
		{
			$montoDebe=$distribucion["valorAsignado"];
			$montoHaber=$distribucion["valorAsignado"];
			if($distribucion["tipoAfectacion"]==1)
				$montoHaber=0;
			else
				$montoDebe=0;
			$beneficiario="NULL";
			if($distribucion["idBeneficiario"]!='')
				$beneficiario=$distribucion["idBeneficiario"];
			$tBeneficiario="NULL";
			if($distribucion["tipoBeneficiario"]!='')
				$tBeneficiario=$distribucion["tipoBeneficiario"];
			
			$cadena=	"('".date("Y-m-d")."',".$_SESSION["idUsr"].",'".$distribucion["tipoPresupuesto"]."','".$distribucion["cuentaBancaria"]."','".$distribucion["codCuentaAfectacion"]."',
							'".$distribucion["codCuentaAfectacionSimple"]."',".$montoDebe.",".$montoHaber.",3,'NOM-".$obj->quincena.$obj->ciclo."',".$idLibro.",
							'".$objCalculo["nombreCalculo"]."',1,".$obj->idUsuario.",".$beneficiario.",".$tBeneficiario.")";
			if($listValores=="")//echo $consulta[$x];
				$listValores=$cadena;
			else
				$listValores.=",".$cadena;
				
				
		}
		if($listValores!="")
		{
			$consulta[$x]="insert into 514_asientos(fechaMovimiento,idUsuario,tipoPresupuesto,cuentaBanco,cuentaMascara,cuentaSimple,montoDebe,montoHaber,
						idTipocumento,noDocumento,idLibro,concepto,estado,idUsuarioEmpleado,beneficiario,tipoBeneficiario) values ".$listValores;
			$x++;
		}
	}

	return $con->ejecutarBloque($consulta);
}

function obtenerMapaMateria($idMateria)
{
	global $con;
	
	$conDatosMatC="SELECT idPrograma,ciclo FROM 4013_materia WHERE idMateria=".$idMateria;
	$datosMatC=$con->obtenerPrimeraFila($conDatosMatC);
	 
	$conMapaMatC="SELECT idMapaCurricular FROM 4029_mapaCurricular WHERE idPrograma=".$datosMatC[0]." AND ciclo=".$datosMatC[1];
	$mapaMatC=$con->obtenerValor($conMapaMatC);	
	
	return $mapaMatC;
}

function obtenerNumeroSemana($arrfechas,$fecha)
{
	global $con;
	$tamano=sizeof($arrfechas);
	$fechaSql=cambiaraFechaMysql($fecha);
	
	for($x=0;$x<$tamano;$x++)
	{
		$numeroSemana=$arrfechas[$x]["nSemana"];
		$lunesT=$arrfechas[$x]["fechaI"];
		$domingoT=$arrfechas[$x]["fechaF"];
		$lunes=date("d/m/Y",$lunesT);
		$domingo=date("d/m/Y",$domingoT);
		$lunesConsulta=cambiaraFechaMysql($lunes);
		$domingoconsulta=cambiaraFechaMysql($domingo);
		
		if($fechaSql>= $lunesConsulta && $fechaSql<=$domingoconsulta) 
		{
			return $numeroSemana."-".$lunes."-".$domingo;
		}
	}
	return false;
}

function guardarSociosEmpresa($idCliente,$cadSocios)
{
	global $con;
	$x=0;
	$consulta[$x]="begin";
	$x++;
	$consulta[$x]="DELETE FROM 715_sociosCliente WHERE idCliente=".$idCliente;
	$x++;
	if($cadSocios!="")
	{
		$arrRegSocios=explode("~",$cadSocios);
		foreach($arrRegSocios as $resSocio)
		{
			$datosSocio=explode("_",$resSocio);
			$consulta[$x]="INSERT INTO 715_sociosCliente (idCliente,idClienteSocio,idCargo,porcentaje,experiencia,tipoSocio) VALUES(".$idCliente.",".$datosSocio[0]."
							,".$datosSocio[1].",".$datosSocio[2].",'".cv($datosSocio[3])."',".$datosSocio[4].")";
			
			$x++;
		}
	}
	$consulta[$x]="commit";
	$x++;
	return $con->ejecutarBloque($consulta);
}

function obtenerMateriasHijas($idMateria,&$arrMaterias,$considerarContenedores=false)
{
	global $con;
	$consulta="select idMateria,idTipoComponente from 4031_elementosMapa where idPadre=".$idMateria;	
	$resMat=$con->obtenerFilas($consulta);
	while($filasMat=mysql_fetch_row($resMat))
	{
		if(($filasMat[1]!="1")||($considerarContenedores))
			array_push($arrMaterias,$filasMat[0]);	
		obtenerMateriasHijas($filasMat[0],$arrMaterias);
	}
}



function obtenerIdProcesoFormularioBase($idFormulario)
{
	global $con;	
	$consulta="select idProceso from 900_formularios where idFormulario=".$idFormulario;
	return $con->obtenerValor($consulta);
}

function generarTablaFormularioThot($idFormulario)
{
	global $con;
	$consulta="select nombreTabla,idFrmEntidad from 900_formularios where idFormulario=".$idFormulario;
	$fila=$con->obtenerPrimeraFila($consulta);
	$tablaFormulario=$fila[0];
	
	if($con->existeTabla($tablaFormulario))
	{
			//Valores directos de tabla
		$consulta="	select e.nombreCampo,e.idGrupoElemento from 901_elementosFormulario e
					where tipoElemento not in(-1,-2,0,1,2,4,14,16) and idFormulario=".$idFormulario."  order by idGrupoElemento";
		$listaCamposAux=$con->obtenerListaValores($consulta,"`");
		if($listaCamposAux!="")
			$listaCamposAux=",".$listaCamposAux;
		$camposAux="id_".$tablaFormulario." as idRegistro".$listaCamposAux;
		//valor de contenidos en otras tablas
		$consulta="	select e.nombreCampo,e.idGrupoElemento,tipoElemento from 901_elementosFormulario e
					where (tipoElemento=4 or tipoElemento=16) and idFormulario=".$idFormulario." order by idGrupoElemento";
		$res=$con->obtenerFilas($consulta);
		$camposRefTablas="";
		while($filas=mysql_fetch_row($res))
		{
			$queryConf="select * from 904_configuracionElemFormulario where idElemFormulario=".$filas[1];
			$filaConf=$con->obtenerPrimeraFila($queryConf);
			$tablaD=$filaConf[2];
			$campoP=$filaConf[3];
			$campoId=$filaConf[4];
			if((($filaConf[9]==0)&&($filas[2]==4))||($filas[2]==16))
				$consultaRefTablas="(select tc.".$campoP." from ".$tablaD." tc where tc.".$campoId."=".$tablaFormulario.".".$filas[0].")";
			else
				$consultaRefTablas="(select concat(tc.".$campoP.") from ".$tablaD." tc where tc.".$campoId."=".$tablaFormulario.".".$filas[0].")";
			$camposRefTablas.=",".$consultaRefTablas." as `".$filas[0]."`";
		}
		//valor de opciones ingresadas por el usuario manualmente
		$consulta="	select e.nombreCampo,e.idGrupoElemento from 901_elementosFormulario e 
					where (tipoElemento=2 or tipoElemento=14) and idFormulario=".$idFormulario." order by idGrupoElemento";
		$res=$con->obtenerFilas($consulta);
		$camposRefOpciones="";
		while($filas=mysql_fetch_row($res))
		{
			$consultaRefTablas="(select contenido from 902_opcionesFormulario where idIdioma=".$_SESSION["leng"]." and idGrupoElemento=".$filas[1]." and valor=".$tablaFormulario.".".$filas[0]." )";
			$camposRefOpciones.=",".$consultaRefTablas." as `".$filas[0]."`";
		}
		//valores de variables de sistema
		
		$consulta="SELECT campoMysql,campoUsr FROM 9017_camposControlFormulario";
		$res=$con->obtenerFilas($consulta);	
		$camposRefSistema="";
		while($filas=mysql_fetch_row($res))
		{
			/*switch($filas[1])
			{
				case "-11":
				case "-13":
					$consultaRefSistema="(select Nombre from 802_identifica where idUsuario=".$tablaFormulario.".responsable)";
				break;
				case "-10":
					$consultaRefSistema="fechaCreacion";
				break;
				case "-12":
					$consultaRefSistema="fechaModif";
				break;
				case "-14":
					$consultaRefSistema="(select Unidad  from 817_organigrama where codigoUnidad=".$tablaFormulario.".codigoUnidad)";
				break;
				case "-15":
					$consultaRefSistema="(select Unidad  from 817_organigrama where codigoUnidad=".$tablaFormulario.".codigoInstitucion)";
				break;
				case "-16":
					$consultaRefSistema="dtefechaSolicitud";
				break;
				case "-17":
					$consultaRefSistema="tmeHoraInicio";
				break;
				case "-18":
					$consultaRefSistema="tmeHoraFin";
				break;
				case "-19":
					$consultaRefSistema="dteFechaAsignada";
				break;
				case "-20":
					$consultaRefSistema="tmeHoraInicialAsignada";
				break;
				case "-21":
					$consultaRefSistema="tmeHoraFinalAsignada";
				break;
				case "-22":
					$consultaRefSistema="unidadReservada";
				break;
				case "-23":
					$consultaRefSistema="tmeHoraSalida";
				break;
				case "-24":
					$query="select idProceso from 900_formularios where idFormulario=".$idFormulario;
					$idProceso=$con->obtenerValor($query);
					$consultaRefSistema="(select nombreEtapa from 4037_etapas where numEtapa=idEstado and idProceso=".$idProceso.")";
				break;
				case "-25":
					$consultaRefSistema="codigo";
				break;
			}*/
			$camposRefSistema.=",".$filas[0]." as `".$filas[1]."`";
		}
		
		$condWhere="";
		$consulta="(select ".$camposAux.$camposRefTablas.$camposRefOpciones.$camposRefSistema." from ".$tablaFormulario." ".$condWhere.") as ".$tablaFormulario;
		
		return $consulta;
	}
}

function generarCampoFormularioThot($campo,$arrCamposControlTablas,$arrElemento,$conAux)
{
	global $con;
	$datosCampo=explode(".",$campo);
	$tablaFormulario=$datosCampo[0];
	$pos=-1;
	if(isset($arrElemento[$tablaFormulario]))
		$pos=existeValorMatriz($arrElemento[$tablaFormulario],$datosCampo[1]);
	$campoFinal=$campo;
	if($pos!=-1)
	{
		$obj=$arrElemento[$tablaFormulario][$pos];
		switch($obj[1])
		{
			case 2:
			case 14:
				$consultaRefTablas="(select contenido from 902_opcionesFormulario where idIdioma=".$_SESSION["leng"]." and idGrupoElemento=".$obj[2]." and valor=".$tablaFormulario.".".$datosCampo[1]." )";
				$campoFinal=$consultaRefTablas." as `".$campo."`";
			break;
			case 4:
			case 16:
				$queryConf="select * from 904_configuracionElemFormulario where idElemFormulario=".$obj[2];
				$filaConf=$conAux->obtenerPrimeraFila($queryConf);
				$tablaD=$filaConf[2];
				$campoP=$filaConf[3];
				$campoId=$filaConf[4];
				if(strpos($tablaD,"[")===false)
				{
					if((($filaConf[9]==0)&&($obj[1]==4))||($obj[1]==16))
						$consultaRefTablas="(select tc.".$campoP." from ".$tablaD." tc where tc.".$campoId."=".$tablaFormulario.".".$datosCampo[1].")";
					else
						$consultaRefTablas="(select concat(tc.".$campoP.") from ".$tablaD." tc where tc.".$campoId."=".$tablaFormulario.".".$datosCampo[1].")";
					$campoFinal=$consultaRefTablas." as `".$campo."`";
				}
			break;	
		}
		
	}
	if(($pos==-1)&&(strpos($datosCampo[0],"tablaDinamica")!==false))
	{
		
		if(isset($arrCamposControlTablas[$datosCampo[1]]))
			$campoFinal=$tablaFormulario.".".$arrCamposControlTablas[$datosCampo[1]]." as `".$campo."`";
		
	}
	
	return $campoFinal;
}

function buscarNombreCriterio($idCriterio,$tipo)
{
	global $con;
	switch($tipo) 
	{
		case 0:
			$tabla="4010_evaluaciones";
			$idT="idEvaluacion";
		break;
		case 1:
			$tabla="4013_materia";
			$idT="idMateria";
		break;
		case 2:
			$tabla="4008_actitudes";
			$idT="idActitud";
		break;
		case 3:
			$tabla="4007_competencias";
			$idT="idCompetencia";
		break;
		case 4:
			$tabla="4006_habilidades";
			$idT="idHabilidad";
		break;
		case 5:
			$tabla="4011_tecnicasColaborativas";
			$idT="idTecnicaC";
		break;
		case 6:
			$tabla="4012_productos";
			$idT="idProducto";
		break;
	}
	$conNombre="select titulo from ".$tabla." where ".$idT."=".$idCriterio;
	$nombre=$con->obtenerValor($conNombre);
	return $nombre;
}

function generarCuerpoExpresion($idProceso,$numEtapa,$idFormulario,$idRegistro,$idPerfil=-1)
{
	global $con;
	$cadena="";
	$arrDatosProceso=array();
	$arrDatosProceso["idProceso"]=$idProceso;
	$arrDatosProceso["numEtapa"]=$numEtapa;
	$arrDatosProceso["idFormulario"]=$idFormulario;
	$arrDatosProceso["idRegistro"]=$idRegistro;
	$arrCamposFrmTablas=array();
	$consulta="SELECT campoUsr,campoMysql FROM 9017_camposControlFormulario";
	$resCampo=$con->obtenerFilas($consulta);
	while($filaCampo=mysql_fetch_row($resCampo))
	{
		$arrCamposFrmTablas[$filaCampo[0]]=$filaCampo[1];
	}
	$consulta="select nodosFiltro,camposProy,operacion,nTabla,parametros,idDataSet,tipoAlmacen,nombreDataSet from 9014_almacenesDatos where tipoDataSet=2 and idReporte=".$idProceso." order by tipoAlmacen desc,idDataSet asc";
	
	$resConAux=$con->obtenerFilas($consulta);
	$arrValorParamReporte=array();
	$arrResultConsulta=array();
	
	while($filaCon=mysql_fetch_row($resConAux))
	{
		$arrResp=validarConsultaAlmacen($filaCon[5],$con);
		if(sizeof($arrResp)>0)
			return $cadena;	
		
		$arrTablas=explode(",",$filaCon[3]);
		$listTabla="";
		$nTablaAux="";
		$arrElementosTabla=array();
		foreach($arrTablas as $nTabla)
		{
			$nTablaAux=$nTabla;
			if($listTabla=="")
				$listTabla=$nTablaAux;
			else
				$listTabla.=",".$nTablaAux;
			if(strpos($nTabla,"tablaDinamica")!==false)
			{
				
				$consulta="select idFormulario from 900_formularios where nombreTabla='".$nTabla."'";
				$idFormulario=$con->obtenerValor($consulta);
				$consulta="SELECT tipoElemento,idGrupoElemento,nombreCampo FROM 901_elementosFormulario WHERE idFormulario=".$idFormulario;
  
				$resElementoFormulario=$con->obtenerFilas($consulta);
				$arrElementos=array();
				while($filaElemento=mysql_fetch_row($resElementoFormulario))
				{
					$obj=array();
					switch($filaElemento[0])
					{
						case 2:
						case 14:
						case 4:
						case 16:
							$obj[0]=$filaElemento[2]; //Nombre
							$obj[1]=$filaElemento[0]; //Tipo
							$obj[2]=$filaElemento[1]; //Grupo
							array_push($arrElementos,$obj);
						break;
					}
					
				}
				$arrElementosTabla[$nTabla]=$arrElementos;
			}
		}
  
		$arrValorParamAlmacen=array();
		$nodosFiltro=$filaCon[0];
		$camposProy=$filaCon[1];
		$operacion=$filaCon[2];
		$tabla=$filaCon[3];
		$parametros=$filaCon[4];
		if($parametros!="")
		{
			$arrParametros=explode(",",$parametros);
			foreach($arrParametros as $param)
			{
				$consulta="select valor,tipoValor,parametro from 9014_valoresParametroAlmacenesDatos where idDataSet=".$filaCon[5]." and parametro='".$param."'";	
				$filaParam=$con->obtenerPrimeraFila($consulta);
				if(!$filaParam)
					$arrValorParamAlmacen[substr($param,1)]="-100584";
				else
				{
					$valor="";
					switch($filaParam[1])
					{
						case "1":
							$valor=$filaParam[0];
						break;
						case "3":
							$consulta="select valorSesion,valorReemplazo from 8003_valoresSesion where idValorSesion=".$filaParam[0];
							$filaSesion=$con->obtenerPrimeraFila($consulta);
							if(($filaParam[0]==1)||($filaParam[0]==4))
								$valor=$_SESSION[$filaSesion[0]];
							else
								$valor="'".$_SESSION[$filaSesion[0]]."'";
						break;
						case "4":
							$consulta="select valorSesion,valorReemplazo from 8003_valoresSesion where idValorSesion=".$filaParam[0];
							$filaSesion=$con->obtenerPrimeraFila($consulta);
							$valorSistema="";
							switch($filaParam[0])
							{
								case "8":
									$valorSistema="'".date("Y-m-d")."'";
								break;
								case "9":
									$valorSistema="'".date("H:i")."'";
								break;
							}
							$valor=$valorSistema;
						break;
						case "7":
							$valor=$arrResultConsulta[$filaParam[0]]["resultado"];	
						break;
						case "8":
							$valor=$filaParam[0]."|@|".$filaParam[2]."|@|8";
						break;
						case "15":
							$valor=$filaParam[0]."|@|".$filaParam[2]."|@|15";
						break;
						case "16":
							switch($filaParam[0])
							{
								case "1":
									$valor=$arrDatosProceso["idFormulario"];
								break;
								case "2":
									$valor=$arrDatosProceso["idProceso"];
								break;
								case "3":
									$valor=$arrDatosProceso["idRegistro"];
								break;
								case "4":
									$valor=$arrDatosProceso["numEtapa"];
								break;
							}
						break;
						
					}
					$arrValorParamAlmacen[substr($param,1)]=$valor;
				}
			}
		}
		$queryOp="";
		switch($operacion)
		{
			case 1:
				$queryOp="select avg(".$camposProy.")";
			break;
			case 2:
				$listCamposProy="";
				$arrCamposProy=explode(",",$camposProy);
				foreach($arrCamposProy as $campo)
				{
					$nCampo=generarCampoFormularioThot($campo,$arrCamposFrmTablas,$arrElementosTabla,$con);
					if($listCamposProy=="")	
						$listCamposProy=$nCampo;
					else
						$listCamposProy.=",".$nCampo;	
				}
				$queryOp="select ".$listCamposProy;	
							
			break;
			case 3:
				$queryOp="select sum(".$camposProy.")";
			break;
			case 4:
				$queryOp="select max(".$camposProy.")";			
			break;
			case 5:
				$queryOp="select min(".$camposProy.")";			
			break;
			case 6:
				$camposProy="1";
				$queryOp="select count(".$camposProy.") as nRegistros";
			break;
			case 7:
				$queryOp="select sqrt(".$camposProy.")";
			break;		
		}
		
		
		$queryOp.=" from ".$listTabla;
		
		$objNodos=json_decode($nodosFiltro);
		if(sizeof($objNodos)>0)
		{
			$condWhere="";
			$arrParamPendientes=array();
			$ejecutado=1;
			foreach($objNodos as $nodo)
			{
				$codMysql=$nodo->tokenMysql;
				$tokenTipo=$nodo->tokenTipo;
				$dToken=explode("|",$tokenTipo);
				
				switch($dToken[0])
				{
					case 0:
					case 1:
					break;
					
					case 3:
						$consulta="select valorSesion,valorReemplazo from 8003_valoresSesion where idValorSesion=".$dToken[1];
						$filaSesion=$con->obtenerPrimeraFila($consulta);
						if(($dToken[1]==1)||($dToken[1]==4))
							$codMysql=str_replace('@'.$filaSesion[1],$_SESSION[$filaSesion[0]],$codMysql);
						else
							$codMysql=str_replace('@'.$filaSesion[1],"'".$_SESSION[$filaSesion[0]]."'",$codMysql);
					break;
					case 4:
						$consulta="select valorSesion,valorReemplazo from 8003_valoresSesion where idValorSesion=".$dToken[1];
						$filaSesion=$con->obtenerPrimeraFila($consulta);
						$valorSistema="";
						switch($dToken[1])
						{
							case "8":
								$valorSistema="'".date("Y-m-d")."'";
							break;
							case "9":
								$valorSistema="'".date("H:i")."'";
							break;
						}
						$codMysql=str_replace('@'.$filaSesion[1],$valorSistema,$codMysql);
					break;
					case 5:
					case 6:
						$nParam=substr($dToken[1],1);
						if(strpos($arrValorParamAlmacen[$nParam],"|@|")===false)
							$codMysql=str_replace('@'.$nParam,$arrValorParamAlmacen[$nParam],$codMysql);
						else
						{
							$arrDatos=explode("|@|",$arrValorParamAlmacen[$nParam]);	
							$campoRef=$arrDatos[0];
							$ejecutado=0;
							if($arrDatos[2]=="8")
							{
								if(!existeValor($arrParamPendientes,$campoRef."|".$arrDatos[1]))
									array_push($arrParamPendientes,$campoRef."|".$arrDatos[1]);
							}
							else
							{
								$codMysql=str_replace($arrDatos[1],'\'".$'.$arrDatos[0].'."\'',$codMysql);
								if(!existeValor($arrParamPendientes,$campoRef."|".$arrDatos[1]))
									array_push($arrParamPendientes,$campoRef);
							}
						}
						
					break;
					case 7:
						$codMysql=str_replace("@","".$arrResultConsulta[$dToken[1]]["resultado"]."",$codMysql);
					break;
					case 16:
						switch($dToken[1])
						{
							case "1":
								$valor=$arrDatosProceso["idFormulario"];
							break;
							case "2":
								$valor=$arrDatosProceso["idProceso"];
							break;
							case "3":
								$valor=$arrDatosProceso["idRegistro"];
							break;
							case "4":
								$valor=$arrDatosProceso["numEtapa"];
							break;
						}
						$codMysql=str_replace("@","".$valor."",$codMysql);
					break;
					
				}
				$condWhere.=" ".$codMysql;
			}
			
			$condWhere=normalizarCondicionWhere($condWhere,$arrCamposFrmTablas);
			$queryOp.=' where '.$condWhere;
		}
		else
			$ejecutado=1;
		//echo $queryOp."<br>";
		$arrResultConsulta[$filaCon[5]]["query"]=$queryOp;
		//echo $queryOp."<br><br>";
	
		if($ejecutado==1)
		{
			if($filaCon[6]==1)	
			{	
				$resQuery=$con->obtenerListaValores($queryOp);		
				if($resQuery=="")
					$resQuery="-1";//-100584
				$arrResultConsulta[$filaCon[5]]["resultado"]=$resQuery;
			}
			else
				$arrResultConsulta[$filaCon[5]]["resultado"]=$con->obtenerFilas($queryOp);
		}
		$arrResultConsulta[$filaCon[5]]["filasAfectadas"]=$con->filasAfectadas;
		$arrResultConsulta[$filaCon[5]]["ejecutado"]=$ejecutado;
		$arrResultConsulta[$filaCon[5]]["paramPendientes"]=$arrParamPendientes;
	}
	if($idPerfil==-1)
		$consulta="select idConsulta from 991_consultasSql where (comp1='".$idProceso."' and (comp3='' or comp3 is null) and (comp2='".$numEtapa."' or cast(comp2 as decimal(11,4))=".$numEtapa."))";
	else
		$consulta="select idConsulta from 991_consultasSql where (comp1='".$idProceso."' and comp3='".$idPerfil."' and (comp2='".$numEtapa."' or cast(comp2 as decimal(11,4))=".$numEtapa."))";
	
	$idConsulta=$con->obtenerValor($consulta);
	if($idConsulta=="")
		return $cadena;
	$consulta="select tokenMysql,tipoToken from 992_tokensConsulta where idConsulta=".$idConsulta;
	
	$resFilas=$con->obtenerFilas($consulta);
	
	while($fila=mysql_fetch_row($resFilas))
	{	
		
		switch($fila[1])
		{
			case "1":
			case "5":
				$cadena.=$fila[0];
			break;	
			case 10:
				$cadena.=$fila[0];
			break;
			case 20:
				$objFuncion=json_decode($fila[0]);
				$consulta="select * from 9033_funcionesSistema where idFuncion=".$objFuncion->idFuncion;

				$fila=$con->obtenerPrimeraFila($consulta);
				$tipoFuncion=$fila[2];
				$nFuncion=$fila[1];
				$listaParametrosBD="";
				$listaParametros="";
				$cadVarParam="";
				
				if(isset($objFuncion->parametros))
				{
					foreach($objFuncion->parametros as $param)
					{
						
						$valor=$param->valorSistema;
						$tipo=$param->tipoValor;
						
						$valorParametro=obtenerValorParam($valor,$tipo,$arrResultConsulta,$arrDatosProceso);
						$cadVarParam.='$p'.$param->parametro.'='.$valorParametro.";";
						if($listaParametros=="")
							$listaParametros='$p'.$param->parametro;
						else
							$listaParametros.=',$p'.$param->parametro;	
						
						
						if($listaParametrosBD=="")
							$listaParametrosBD=$valorParametro;	
						else
							$listaParametrosBD.=",".$valorParametro;	
					}
				}
				
				switch($tipoFuncion)
				{
					case "0":	//PHP
						$cadena.=$cadVarParam;
						$cadena.=$nFuncion." (".$listaParametros.");";	
											
					break;
					case "1":  //Mysql
						$cadena.=' 	$query="call '.$nFuncion.' ('.$listaParametrosBD.')";
									$con->ejecutarConsulta($query);';
					
					break;
					
				}
				

			break;
			case 21:
				$cadena.=obtenerValorParam($fila[0],$fila[1],$arrResultConsulta);
			break;
		}
	}
	return $cadena;
}

function obtenerValorParam($val,$tipo,&$arrResultConsulta,$arrDatosProceso=null)
{
	
	global $con;
	$valor="";
	switch($tipo)
	{
		case "1":
			$valor="'".$val."'";
		break;	
		case "3":
			$consulta="select valorSesion,valorReemplazo from 8003_valoresSesion where idValorSesion=".$val;
			$filaSesion=$con->obtenerPrimeraFila($consulta);
			$valor="'".$_SESSION[$filaSesion[0]]."'";
			
		break;
		case "4":
			$consulta="select valorSesion,valorReemplazo from 8003_valoresSesion where idValorSesion=".$val;
			$filaSesion=$con->obtenerPrimeraFila($consulta);
			$valorSistema="";
			switch($val)
			{
				case "8":
					$valorSistema="'".date("Y-m-d")."'";
				break;
				case "9":
					$valorSistema="'".date("H:i")."'";
				break;
			}
			$valor=$valorSistema;
		break;
		case "15":
			$valor='$'.$val;
		break;
		case 16:
			
			switch($val)
			{
				case "1":
					$valor=$arrDatosProceso["idFormulario"];
				break;
				case "2":
					$valor=$arrDatosProceso["idProceso"];
				break;
				case "3":
					$valor=$arrDatosProceso["idRegistro"];
				break;
				case "4":
					$valor=$arrDatosProceso["numEtapa"];
				break;
			}
			return $valor;
		break;
		case 7:
		case "21":
			$arrVal=explode("|",$val);
			$idAlmacen=$arrVal[0];
			$campoProy="";
			if(isset($arrVal[1]))
				$campoProy=$arrVal[1];
			
			$objConsulta=$arrResultConsulta[$idAlmacen];
			if($objConsulta["ejecutado"]==1)
			{
				if(gettype($objConsulta["resultado"])=="resource")
				{
					$filaRef=mysql_fetch_row($objConsulta["resultado"]);
					if($filaRef)
					{
						$fila=convertirFilasAlmacenArrayAsoc($idAlmacen,$filaRef);	
						$valor="'".$fila[$campoProy]."'";
					}
					else
						$valor="";
				}
				else
					$valor="'".$objConsulta["resultado"]."'";
			}
			else
			{
				$valor='ejecutarConsultaExpresion("'.$objConsulta["query"].'","'.$campoProy.'");';
					
			}
		break;
	}
	return $valor;
}

function ejecutarConsultaExpresion($consulta,$campoProy)
{
	global $con;
	$cad="";
	$query=$consulta;
	$filaRef=$con->obtenerPrimeraFila($query);
	if($campoProy!="")
	{
		$fila=convertirFilasAlmacenArrayAsoc($idAlmacen,$filaRef);	
		return $fila[$campoProy];	
	}
	else
	{
		return $filaRef[0];
	}
}


function buscarTipoCalCriterio($idCriterio,$tipo)
{
	global $con;
	if($tipo==0)
	{
		$conTipo="select idTipoEvaluacion from 4010_evaluaciones where idEvaluacion=".$idCriterio;
		//echo $conTipo."<br/>";
		$tipo=$con->obtenerValor($conTipo);
		if($tipo=="")
		{
			$tipo=1;
		}
	}
	else
	{
		$tipo=1;
	}
	return $tipo;
}

function generarCabeceraControlesFormulario($idFormulario)
{
	$directorioPATH="../thotFormularios/controles";
	$directorio=dir($directorioPATH);
	$arrCabecera="";
	$arrCabeceras=array();
	while($archivo=$directorio->read())
	{
		if(($archivo!=".")&&($archivo!=".."))
		{
			if(!(is_dir($directorioPATH."/".$archivo)))
			{
				$contenido=file_get_contents($directorioPATH."/".$archivo);
				$obj=json_decode($contenido);
				$cabecera='<script type="text/javascript" src="'.$directorioPATH.'/controlesScripts/'.$obj->script.'"></script>';
				$cabecera=str_replace("@idFormulario",bE($idFormulario),$cabecera);
				if(!existeValor($arrCabeceras,$cabecera))
					array_push($arrCabeceras,$cabecera);

			}
		}
	}
	foreach($arrCabeceras as $cabecera)
		echo $cabecera;
	
}

function generarControlesFormulario(&$arrControlesDeshabilitados)
{
	$directorioPATH="../../thotFormularios/controles";
	$directorio=dir($directorioPATH);
	$arrControles="";
	$nFila=0;
	$nColumna=0;
	$arrArchivo=array();

	while($archivo=$directorio->read())
	{
		if(($archivo!=".")&&($archivo!=".."))
		{
			if(!(is_dir($directorioPATH."/".$archivo)))

				array_push($arrArchivo,$archivo);
		}
	}
	
	sort($arrArchivo);
	
	
	foreach($arrArchivo as $archivo)
	{
		$contenido=file_get_contents($directorioPATH."/".$archivo);
		$obj=json_decode($contenido);
		if((isset($obj->deshabilitado))&&($obj->deshabilitado==1))
			array_push($arrControlesDeshabilitados,$obj->id);
		$objCtrl='	
					{
						id:"'.$obj->id.'",
						xtype:"panel",
						x:'.(($nColumna*32)+1).',
						y:'.(($nFila*32)+1).',
						width:34,
						hideBorders:true,
						baseCls: "x-plain",
						items:	[
									{
										xtype:"button",
										width:32,
										height:32,
										icon:"../images/formularios/'.$obj->icono.'",
										handler:function()
												{
													'.$obj->funcion.'
													
												},
										cls:"x-btn-text-icon",
										tooltip:"'.$obj->titulo.'"
									}
								]
					}
									';
		
		if($arrControles=="")
			$arrControles=$objCtrl;
		else
			$arrControles.=",".$objCtrl;
		$nFila++;
		if($nFila>15)
		{
			$nFila=0;
			$nColumna++;
		}
		
	}
	$directorio->close();
	echo $arrControles;
}





function obtenerEtapaProcesoActual($idRegistro,$idReferencia,$idFormulario)
{
	global $con;
	$idProceso=obtenerIdProcesoFormulario($idFormulario);
	$idFrmBase=obtenerFormularioBase($idProceso);
	
	if($idFrmBase=="")
		return "1.00";
	$nTabla=obtenerNombreTabla($idFrmBase);
	if($idFormulario==$idFrmBase)
		$consulta="select idEstado from ".$nTabla." where id_".$nTabla."=".$idRegistro;
	else
		$consulta="select idEstado from ".$nTabla." where id_".$nTabla."=".$idReferencia;
	$nEtapa=$con->obtenerValor($consulta);
	if($nEtapa=="")
		$nEtapa="0.00";
	return $nEtapa;
}

function obtenerResponsableProcesoActual($idRegistro,$idReferencia,$idFormulario)
{
	global $con;
	$idProceso=obtenerIdProcesoFormulario($idFormulario);
	$idFrmBase=obtenerFormularioBase($idProceso);
	if($idFrmBase=="")
		return "-1";
	$nTabla=obtenerNombreTabla($idFrmBase);
	if($idFormulario==$idFrmBase)
		$consulta="select responsable from ".$nTabla." where id_".$nTabla."=".$idRegistro;
	else
		$consulta="select responsable from ".$nTabla." where id_".$nTabla."=".$idReferencia;
	$idResponsable=$con->obtenerValor($consulta);
	if($idResponsable=="")
		$idResponsable="-1";
	return $idResponsable;
}

function excluirFormulario($idProceso,$idFormulario,$idRegistro,$actor)
{
	global $con;
	$resultado=false;
	$consulta="SELECT funcionExclusion,TipoFuncion FROM 203_funcionesExclusion WHERE idFormulario=".$idFormulario;
	$fila=$con->obtenerPrimeraFila($consulta);
	if($fila)
	{
		switch($fila[1])
		{
			case 0:
				eval('$resultado='.$fila[0].'('.$idProceso.','.$idFormulario.','.$idRegistro.',"'.$actor.'");');
			break;	
		}	
	}	

	return $resultado;
}

function funcValidarHorarioMateriaCurso($horaI,$horaF,$dia,$idMateria,$idGrupo,$idCiclo,$id)
{
	global $con;
	$conHorasSemana="SELECT horasSemana FROM 4013_materia WHERE idMateria=".$idMateria;
	$noHoras=$con->obtenerValor($conHorasSemana);
	$sedeGrupo="SELECT sede FROM 4048_grupos WHERE idGrupo='".$idGrupo."'";
	$sedeG=$con->obtenerValor($sedeGrupo);
	if($sedeG=="")
		$sedeG="-1";
	if(($noHoras=="")||($noHoras==0))
	{
		return "3|";  // Materia no tiene configurado no horas x semana
	}
	
	$conUnidadMedida="SELECT IntHrMedida FROM _315_tablaDinamica WHERE codigoInstitucion='".$sedeG."'"; //WHERE cmbCiclo=".$idCiclo;
	$consulta="SELECT dracionHora FROM _472_tablaDinamica WHERE idReferencia=".$idInstanciaPlanEstudio;
	$unidadM=$con->obtenerValor($conUnidadMedida);
	if($unidadM=="")
		$unidadM=60;
	
	$tiempoMat=((strtotime('0:00:00'))+(strtotime($horaF)))-(strtotime($horaI));
	$nuevosMin=(date('H',$tiempoMat)*60)+(date('i',$tiempoMat));
	if($id==-1)
	{
		$conHorMat="SELECT dia,horaInicio,horaFin FROM 4065_materiaVSGrupo WHERE  idMateria=".$idMateria." AND idGrupo=".$idGrupo." and ciclo=".$idCiclo;

	}
	else
	{
		$conHorMat="SELECT dia,horaInicio,horaFin FROM 4065_materiaVSGrupo WHERE  idMateria=".$idMateria." AND idGrupo=".$idGrupo." and ciclo=".$idCiclo." and idMateriaVSGrupo not in(select idMateriaVSGrupo from 4065_materiaVSGrupo where idMateriaVSGrupo=".$id." )";
	}
	$resHorMat=$con->obtenerFilas($conHorMat);
	$numeroFilas=$con->filasAfectadas;
	if($numeroFilas>0)			
	
	{
		$sumatoriaMinutos=0;
		while($hMat=mysql_fetch_row($resHorMat))			
		{
			$tiempoMat=((strtotime('0:00:00'))+(strtotime($hMat[2])))-(strtotime($hMat[1]));
			$sumatoriaMinutos+=(date('H',$tiempoMat)*60)+(date('i',$tiempoMat));
			
			if($hMat[0]==$dia)
			{
				if(colisionaTiempo($horaI,$horaF,$hMat[1],$hMat[2]))
				{
					$dia=obtenerNombreDia($dia);
					return "2|".$dia."|".$hMat[1]."|".$hMat[2]."";  //Colisiona la materia con otro horario de la misma materia

				}
			}
		}
		$minutosReales=$sumatoriaMinutos+$nuevosMin;
	}

	$validar=$minutosReales/$unidadM;
	if($validar>$noHoras)
	{

		return "4|".$validar."|".$noHoras;  //No horas asignadas no afecte a no horas permitidas
	}
	else
	{
		$conProfesor="SELECT idUsuario FROM 4047_participacionesMateria WHERE idMateria=".$idMateria." AND idGrupo=".$idGrupo." AND participacionP=1"; // AND  esperaContrato=0";
		$idProfesor=$con->obtenerValor($conProfesor);
		$dispValidar;
		if($idProfesor!="")
		{
			$validarDisponibilidadDoc=validarModificacionHorarioDisponibleDocente($dia,$horaI,$horaF,$idCiclo,$idProfesor);
			$dispValidar=explode("|",$validarDisponibilidadDoc);
			if($dispValidar[0]!=1)
			{
				if($dispValidar[0]==2)
					return "8|"; //el profesor no cuenta con disponibilidad de horario
				else
					return "7|".$dispValidar[1]; //tiene un profesor y la materia tiene conflictos de horario
			}
		}
			
		$validarNuevoHorarioMat=validarModificacionHorarioProf($idProfesor,$horaI,$horaF,$dia,$idCiclo);
		$dValid=explode("|",$validarNuevoHorarioMat);
		if($dValid[0]==1)
		{
			$validarAlumnosGrupo=validarModificacionHorarioAlumnos($idMateria,$idGrupo,$idCiclo,$dia,$horaI,$horaF);
			$vValGpo=explode("|",$validarAlumnosGrupo);
			if($vValGpo[0]==1)
				return "1|";
			else
				return "6|".$vValGpo[1];
		}
		else
		{
			return "5|".$dValid[1]; //tiene un profesor y la materia tiene conflictos de horario
		}
			
	}
	
}


//Eliinar en funciones progAcademico
function obtenerNombreDiaExtendido($noDia)
{
	  $dia="";
	  switch($noDia)
	  {
		case "1":
			$dia="Lunes";
		break;
		case "2":
			$dia="Martes";
		break;
		case "3":
			$dia="Miercoles";
		break;
		case "4":
			$dia="Jueves";
		break;
		case "5":
		   $dia="Viernes";
		break;
		case "6":
			$dia="Sabado";
		break;
		case "0":
		   $dia="Domingo";
	  }
	return $dia  ;
}

function validarModificacionHorarioDisponibleDocenteExtendido($dia,$horaI,$horaF,$idCiclo,$idUsuario,$idPeriodo,$idInstancia)
{
	global $con; 
	
	
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

	if($noHorarios==0)
	{
		return "2|";
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
		}
		
			
		if(!$encontrado)
		{
			$obj=obtenerNombreDiaExtendido($dia)."&nbsp;De&nbsp;".date('H:i',strtotime($horaI))."&nbsp;A&nbsp;".date('H:i',strtotime($horaF));
			if($cadenaColision=="")	  
				$cadenaColision=$obj;
			else
				$cadenaColision.=$obj;
		}
		if($encontrado)
		{
			return "1|";
		}
		else
		{
			return "3|".$cadenaColision;
		}
	}
}

function validarModificacionHorarioProfExtendido($idUsuario,$horaI,$horaF,$dia,$idCiclo,$idMateria,$idGrupo,$formato=1,$fAplicacion=NULL,$objComp=NULL)
{
	global $con;
	$arrFechasMateria=array();
	if($objComp==NULL)
		$arrFechasMateria=obtenerFechaVigenciaMateria($idGrupo);
	else
	{
		if(isset($objComp->esCambioFechaInicio))
		{
			$arrFechasMateria[0]=$objComp->fechaInicio;
			$arrFechasMateria[1]=$objComp->fechaFin;
			
		}
	}
	
	$colision=false;
	$tablaColisiones=0;
	if($formato==1)
		$tablaColisiones="<table><tr><td width=\"300\" align=\"center\"><pan class=\"corpo8_bold\">Materia</td><td width=\"120\" align=\"center\"><pan class=\"corpo8_bold\">Grupo</span></td><td width=\"150\" align=\"center\"><pan class=\"corpo8_bold\">Horario problema</span></td></tr>";
	else
		$tablaColisiones="<table><tr><td width='300' align='center'><pan class='corpo8_bold'>Materia</td><td width='120' align='center'><pan class='corpo8_bold'>Grupo</span></td><td width='150' align='center'><pan class='corpo8_bold'>Horario problema</span></td></tr>";		
	if($arrFechasMateria[0]=="")
	{
		return "2|";	
	}
	
	$comp="";
	if($fAplicacion!=NULL)
		$comp=" and '".$fAplicacion."'>=fechaInicio and '".$fAplicacion."'<=fechaFin";
	$consulta="select idUsuario,a.idGrupo from 4519_asignacionProfesorGrupo a,4520_grupos g where  a.idGrupo<>".$idGrupo." and participacionPrincipal=1 and a.situacion=1 and g.idGrupos=a.idGrupo and g.situacion=1 and idUsuario=".$idUsuario;


	$filas=$con->obtenerFilas($consulta);
	$numeroFilas=$con->filasAfectadas;
	if($numeroFilas==0)
	{
		return "1|";
	}
	else
	{
		$mensajeDeColision="";
		while($fila=mysql_fetch_row($filas))	
		{
			  $horaMatProf="SELECT dia,horaInicio,horaFin FROM 4522_horarioGrupo where idGrupo=".$fila[1]." and fechaFin>=fechaInicio and dia=".$dia." ".$comp." for update";
			
			  $respuesta=$con->obtenerFilas($horaMatProf);				 
			  $numFilas=$con->filasAfectadas;
			  while($filaMatP=mysql_fetch_row($respuesta))
			  {
				  if(colisionaTiempo($filaMatP[1],$filaMatP[2],$horaI,$horaF))
				  {
					  $arrFechasMateriaComp=obtenerFechaVigenciaMateria($fila[1]);
					  if($arrFechasMateriaComp[0]!="")
					  {
						  if(colisionaTiempo($arrFechasMateria[0],$arrFechasMateria[1],$arrFechasMateriaComp[0],$arrFechasMateriaComp[1]))
						  {
							  $colision=true;
							  $consulta="SELECT nombreGrupo,nombreMateria,o.unidad,a.cveMateria FROM 4520_grupos g,4502_Materias m,817_organigrama o,4512_aliasClavesMateria a   
							  			WHERE m.idMateria=g.idMateria AND idGrupos=".$fila[1]." AND o.codigoUnidad=g.plantel AND a.idMateria=m.idMateria AND a.sede=g.plantel";
							  
							  $filaMat=$con->obtenerPrimeraFila($consulta);
							  
							  $nombre=$filaMat[1];
							  $diaProblema=obtenerNombreDiaExtendido($filaMatP[0]);
							  $grupo=$filaMat[0];
							  if($formato==1)
							  {
								  $tablaColisiones.="<tr height=\"21\"><td align=\"left\"><span class=\"letraExt\">[".$filaMat[3]."] ".$nombre."<br><b>Plantel:</b> ".$filaMat[2]."</span></td><td align=\"left\"><span class=\"letraExt\">".$grupo."</span></td><td align=\"left\"><span class=\"letraExt\">".$diaProblema." ".
													date("H:i",strtotime($filaMatP[1]))." - ".date("H:i",strtotime($filaMatP[2]))."</span></td></tr>" ;
							  }
							  else
							  {
								  $tablaColisiones.="<tr height='21'><td align='left'><span class='letraExt'>[".$filaMat[3]."] ".$nombre."<br><b>Plantel:</b> ".$filaMat[2]."</span></td><td align='left'><span class='letraExt'>".$grupo."</span></td><td align='left'><span class='letraExt'>".$diaProblema." ".
													date("H:i",strtotime($filaMatP[1]))." - ".date("H:i",strtotime($filaMatP[2]))."</span></td></tr>" ;
							  }
						  }
					  }
				  }
				  
			  }
		}
		$tablaColisiones.="</table>";
		if(!$colision)
		{
			return "1|";
		}
		return "3|".$tablaColisiones;
		
	   
	}
}

function obtenerFechaVigenciaMateria($idGrupo)
{
	global $con;
	$conFechasGrupo="SELECT fechaInicio,fechaFin FROM 4520_grupos WHERE idGrupos=".$idGrupo;
	$fechasGrupo=$con->obtenerPrimeraFila($conFechasGrupo);
	$fechaIniGrupoExiste;
	$fechaFinGrupoExiste;
	$fechaIniGrupoExiste=$fechasGrupo[0];
	$fechaFinGrupoExiste=$fechasGrupo[1];
	$arrFechas=array();
	$arrFechas[0]=$fechaIniGrupoExiste;
	$arrFechas[1]=$fechaFinGrupoExiste;
	return $arrFechas;
	
}

function validarModificacionHorarioAlumnosExtendido($idMateria,$idGrupo,$idCiclo,$dia,$hIni,$hFin,$formato=1,$fAplicacion=NULL)
{
	global $con;
	$colision=false;
	$tablaColisiones="";
	if($formato==1)
	  	$tablaColisiones="<table><tr><td width=\"200\" align=\"center\"><pan class=\"corpo8_bold\">Alumno</td><td width=\"200\" align=\"center\"><pan class=\"corpo8_bold\">Materia/Grupo problema</span></td><td width=\"150\" align=\"center\"><pan class=\"corpo8_bold\">Horario problema</span></td></tr>";
	else
		$tablaColisiones="<table><tr><td width='200' align='center'><pan class='corpo8_bold'>Alumno</td><td width='200' align='center'><pan class='corpo8_bold'>Materia/Grupo problema</span></td><td width='150' align='center'><pan class='corpo8_bold'>Horario problema</span></td></tr>";		  
	$consulta="SELECT idUsuario FROM 4517_alumnosVsMateriaGrupo WHERE  idGrupo=".$idGrupo." AND situacion=1";
	
	$comp="";
	if($fAplicacion!=NULL)
	{
		$comp=" and '".$fAplicacion."'>=fechaInicio and '".$fAplicacion."'<=fechaFin";
	}
	$res=$con->obtenerFilas($consulta);
	$noFilas=$con->filasAfectadas;
	$arrFechasMateria=obtenerFechaVigenciaMateria($idGrupo);
	if($noFilas==0)
	{
		return "1|";
	}
	else
	{
		$mensajeColision="";
		while($fila=mysql_fetch_row($res))
		{
			$conMatAlum="SELECT idGrupo FROM 4517_alumnosVsMateriaGrupo WHERE idGrupo<>".$idGrupo." AND situacion=1 and idUsuario=".$fila[0];
		   
			$resAlum=$con->obtenerFilas($conMatAlum);
			while($filaMat=mysql_fetch_row($resAlum))
			{
				$consulta="SELECT dia,horaInicio,horaFin FROM 4522_horarioGrupo WHERE idGrupo=".$filaMat[0]." and dia=".$dia." ".$comp." for update";
				
				$resMatAlum=$con->obtenerFilas($consulta);
				while($filaAlum=mysql_fetch_row($resMatAlum))
				{
					if(colisionaTiempo($filaAlum[1],$filaAlum[2],$hIni,$hFin))
					{
						
						  $arrFechasMateriaComp=obtenerFechaVigenciaMateria($filaMat[0]);
						  if($arrFechasMateriaComp[0]!="")
						  {
							  if(colisionaTiempo($arrFechasMateria[0],$arrFechasMateria[1],$arrFechasMateriaComp[0],$arrFechasMateriaComp[1]))
							  {
								  $nombreAlumno=obtenerNombreUsuario($fila[0]);
								  $colision=true;
								  $consulta="SELECT nombreGrupo,nombreMateria,o.unidad,a.cveMateria  FROM 4520_grupos g,4502_Materias m,817_organigrama o,4512_aliasClavesMateria a  
											  WHERE m.idMateria=g.idMateria AND idGrupos=".$filaMat[0]." AND o.codigoUnidad=g.plantel AND a.idMateria=m.idMateria AND a.sede=g.plantel";
								  
								  $filaM=$con->obtenerPrimeraFila($consulta);
								  $nombre=$filaM[1];
								  $diaProblema=obtenerNombreDiaExtendido($filaAlum[0]);
								  $grupo=$filaM[0];
								  if($formato==1)
								  {
									  $tablaColisiones.="<tr height=\"21\"><td align=\"left\"><span class=\"letraExt\">".$nombreAlumno."</span></td><td align=\"left\"><span class=\"letraExt\">[".$filaM[3]."] ".$nombre." (Grupo: ".$grupo.")<br><b>Plantel:</b> ".$filaM[2]."</span></td><td align=\"left\"><span class=\"letraExt\">".$diaProblema." ".
													  date("H:i",strtotime($filaAlum[1]))." - ".date("H:i",strtotime($filaAlum[2]))."</span></td></tr>" ;
								  }
								  else
								  {
									  $tablaColisiones.="<tr height='21'><td align='left'><span class='letraExt'>".$nombreAlumno."</span></td><td align='left'><span class='letraExt'>[".$filaM[3]."] ".$nombre." (Grupo: ".$grupo.")<br><b>Plantel:</b> ".$filaM[2]."</span></td><td align='left'><span class='letraExt'>".$diaProblema." ".
													  date("H:i",strtotime($filaAlum[1]))." - ".date("H:i",strtotime($filaAlum[2]))."</span></td></tr>" ;
								  }
							  }
						  }
					}
				}
			}
		}
		$tablaColisiones.="</table>";
		if(!$colision)
		{
			return "1|";
		}
		else
		{
			
			return "2|".$tablaColisiones;
		}
	}
}


function funcValidarHorarioMateriaCursoExtendido($horaI,$horaF,$dia,$idGrupo,$idCiclo,$id,$idMateria,$tipoFormato=1,$fAplicacion=NULL,$esCambioHorario=false,$objComp=NULL)
{
	global $con;
	$arrResultado=array();
	$obj=array();
	$consulta="SELECT idInstanciaPlanEstudio,idMateria FROM 4520_grupos WHERE idGrupos=".$idGrupo;
	$idInstancia=$con->obtenerValor($consulta);
	$conHorasSemana="SELECT noHorasSemana FROM 4512_aliasClavesMateria WHERE idInstanciaPlanEstudio=".$idInstancia." AND idMateria=".$idMateria;
	$noHoras=$con->obtenerValor($conHorasSemana);
	$sedeGrupo="SELECT Plantel,idPeriodo,idMateria FROM 4520_grupos WHERE idGrupos='".$idGrupo."'";
	$fGrupo=$con->obtenerPrimeraFila($sedeGrupo);
	$idPeriodo=$fGrupo[1];
	$idMateria=$fGrupo[2];
	$sedeG=$fGrupo[0];
	if($sedeG=="")
		$sedeG="-1";
	$consulta="SELECT dracionHora FROM _472_tablaDinamica WHERE idReferencia=".$idInstancia;
	$unidadM=$con->obtenerValor($consulta);
	if($unidadM=="")
		$unidadM=60;
	
	$tiempoMat=((strtotime('0:00:00'))+(strtotime($horaF)))-(strtotime($horaI));
	$nuevosMin=(date('H',$tiempoMat)*60)+(date('i',$tiempoMat));
	$comp="";
	if($fAplicacion!=NULL)
	{
		$comp=" and '".$fAplicacion."'>=fechaInicio and '".$fAplicacion."'<=fechaFin";
	}
	$conHorMat="SELECT dia,horaInicio,horaFin FROM 4522_horarioGrupo WHERE  idGrupo=".$idGrupo." and idHorarioGrupo not in(".$id.") ".$comp." for update";
	$resHorMat=$con->obtenerFilas($conHorMat);
	$numeroFilas=$con->filasAfectadas;
	$minutosReales=0;
	$sumatoriaMinutos=0;
	if($numeroFilas>0)			
	{
		while($hMat=mysql_fetch_row($resHorMat))			
		{
			$tiempoMat=((strtotime('0:00:00'))+(strtotime($hMat[2])))-(strtotime($hMat[1]));
			$sumatoriaMinutos+=(date('H',$tiempoMat)*60)+(date('i',$tiempoMat));
			
			if(($hMat[0]==$dia)&&(!$esCambioHorario))
			{
				
				if(colisionaTiempo($horaI,$horaF,$hMat[1],$hMat[2]))
				{
					$obj[0]="2";
					$obj[1]="1";
					$obj[2]="Est&aacute; intentando asignar un bloque de horario donde ya existe uno asociado a la materia";
					$obj[3]="";
					array_push($arrResultado,$obj);
				}
			}
		}
		
	}
	$minutosReales=$sumatoriaMinutos+$nuevosMin;
	$validar=$minutosReales/$unidadM;
	
	if(($idMateria>0)&&($validar>$noHoras)&&(!$esCambioHorario))
	{
		$obj[0]="4";
		$obj[1]="1";
		$obj[2]="La suma de horas asignadas a la materia (<b>".$validar."</b>) sobre pasa el n&uacute;mero de horas permitidas por semana (<b>".$noHoras."</b>)";
		$obj[3]="";
		array_push($arrResultado,$obj);
		
	}
	
	
	
	
	$conProfesor="SELECT idUsuario FROM 4519_asignacionProfesorGrupo where idGrupo=".$idGrupo." AND participacionPrincipal=1 and situacion=1"; // AND  esperaContrato=0";
	$idProfesor=$con->obtenerValor($conProfesor);
	$dispValidar;
	if($idProfesor!="")
	{
		$validarDisponibilidadDoc=validarModificacionHorarioDisponibleDocenteExtendido($dia,$horaI,$horaF,$idCiclo,$idProfesor,$idPeriodo,$idInstancia,$fAplicacion);
		$dispValidar=explode("|",$validarDisponibilidadDoc);
		if($dispValidar[0]!=1)
		{
			if($dispValidar[0]==2)
			{
				$obj[0]="8";
				$obj[1]="0";
				$obj[2]="El profesor no cuenta con un registro de disponibilidad de horario para este ciclo";
				$obj[3]="";
				array_push($arrResultado,$obj);
				
			}
			else
			{
				$obj[0]="7";
				$obj[1]="0";
				$obj[2]="El horario que desea asignara a la materia no concuerda con la disponibilidad de horario que el profesor estableci&oacute;n para este ciclo";
				$obj[3]="";
				array_push($arrResultado,$obj);
				
			}
		}
		
		$validarNuevoHorarioMat=validarModificacionHorarioProfExtendido($idProfesor,$horaI,$horaF,$dia,$idCiclo,$idMateria,$idGrupo,$tipoFormato,$fAplicacion,$objComp);
		$dValid=explode("|",$validarNuevoHorarioMat);
		switch($dValid[0])
		{
			case 2:
				$obj[0]="9";
				$obj[1]="0";
				$obj[2]="La materia a&uacute;n no tiene configurado fechas de inicio y t&eacute;rmino";
				$obj[3]="";
				array_push($arrResultado,$obj);
			break;
			case 3:
				$obj[0]="10";
				$obj[1]="0";
				$obj[2]="El horario presenta problemas con otras materias en las cuales el profesor es titular";
				$obj[3]=$dValid[1];
				array_push($arrResultado,$obj);
			break;
			
		}
		
	}
		
	$validarAlumnosGrupo=validarModificacionHorarioAlumnosExtendido($idMateria,$idGrupo,$idCiclo,$dia,$horaI,$horaF,$tipoFormato,$fAplicacion);
	$vValGpo=explode("|",$validarAlumnosGrupo);
	if($vValGpo[0]!=1)
	{
		$obj[0]="11";
		$obj[1]="0";
		$obj[2]="El horario ocasiona que algunos alumnos inscritos en la materia tengan problemas de horario";
		$obj[3]=$vValGpo[1];
		array_push($arrResultado,$obj);
	}
	$objCasos="";
	$resultado=1;
	if(sizeof($arrResultado)>0)	
	{
		$resultado=-1;
		$permiteContinuar=1;
		$arrCasos="";
		$cCaso="";
		foreach($arrResultado as $objRes)
		{
			if($objRes[1]==1)
				$permiteContinuar=0;
			if($tipoFormato==1)
			{
				$cCaso="['".$objRes[0]."','".$objRes[1]."','".$objRes[2]."','".$objRes[3]."']";	
				
			}
			else
			{
				$cCaso='{"noError":"'.$objRes[0].'","permiteContinuar":"'.$objRes[1].'","msgError":"'.$objRes[2].'","compl":"'.$objRes[3].'"}';
			}
			if($arrCasos=="")
				$arrCasos=$cCaso;
			else
				$arrCasos.=",".$cCaso;
		}
		$arrCasos="[".$arrCasos."]";
		$objCasos='{"permiteContinuar":"'.$permiteContinuar.'","arrCasos":'.$arrCasos.'}';
	}
	
	return $resultado."|".$objCasos;
}

function inscribirAlumnoGrado($idAlumno,$idPrograma,$ciclo,$grado)
{
	global $con;
	return true;	
}

function generarEnlaceEtiqueta($idEnlace,$idElemento,$arrValoresParametros)
{
	global $con;
	$consulta="select * from 9041_parametrosEnlaces where idEnlace=".$idEnlace;
	$resEnlace=$con->obtenerFilas($consulta);
	$parametros="";
	while($filaEnlace=mysql_fetch_row($resEnlace))
	{
		$valor="";
		switch($filaEnlace[3])
		{
			case "1";
				$valor=$filaEnlace[4];
			break;
			case "3":
				$consulta="select valorSesion,valorReemplazo from 8003_valoresSesion where idValorSesion=".$valor;
				$filaSesion=$con->obtenerPrimeraFila($consulta);
				$valor="'".$_SESSION[$filaSesion[0]]."'";
			break;
			case "4":
				switch($valor)
				{
					case 8:
						$valor=date("Y-m-d");
					break;
					case 9:
						$valor=date("H:i");
					break;	
				}
			break;
			case "5":
				$valor=$filaEnlace[4];
				switch($valor)
				{
					case 1:
						$valor=$arrValoresParametros["idFormulario"];
					break;
					case 2:
						$valor=$arrValoresParametros["idRegistro"];
					break;
					case 3:
						$valor=$arrValoresParametros["idReferencia"];
					break;
					case 4:
						$valor=$arrValoresParametros["idProceso"];
					break;
					case 5:
						$valor=$arrValoresParametros["idProcesoP"];
					break;	
				}
			break;
		}
		$obj="['".$filaEnlace[2]."','".$valor."']";
		if($parametros=="")
			$parametros=$obj;
		else
			$parametros.=",".$obj;
			
	}
	$consulta="SELECT * FROM 9040_listadoEnlaces WHERE idEnlace=".$idEnlace;
	$filaEnlace=$con->obtenerPrimeraFila($consulta);
	$HRef="<a title='".$filaEnlace[1]."' alt='".$filaEnlace[1]."' id='link_".$idElemento."' href='javascript:verEnlaceFormularioLink(\"".bE($filaEnlace[4])."\",\"".bE($filaEnlace[2])."\",\"".bE('['.$parametros.']')."\")'>";
	return $HRef;		
}


function obtenerCodigosRutas($ciclo=NULL)

{

	global $con;
	if($ciclo!=NULL)
	{
		$consulta="			SELECT DISTINCT p.ruta,
	
							CONCAT(
	
								(SELECT codigoGrupoFuncional FROM _497_tablaDinamica WHERE  id__497_tablaDinamica=p.grupoFuncional),' ',
	
								(SELECT codigoFuncion FROM _498_tablaDinamica WHERE  id__498_tablaDinamica=p.funcion),' ',
	
								(SELECT codigoSubFuncion FROM _500_tablaDinamica WHERE  id__500_tablaDinamica=p.subFuncion),' ',
	
								(SELECT codigoPrograma FROM _502_tablaDinamica WHERE  id__502_tablaDinamica=p.programaGasto),' ',
	
								(SELECT codigoActividadInst FROM _501_tablaDinamica WHERE  id__501_tablaDinamica=p.actividadInstitucional),' ',
	
								(SELECT codigoProgPresupuestal FROM _503_tablaDinamica WHERE id__503_tablaDinamica=p.partidaPresupuestal)
	
							) AS programaInstitucional FROM 9117_estructuraPAT p WHERE  codigoInstitucion='".$_SESSION["codigoInstitucion"]."' and ciclo=".$ciclo;
	}
	else
	{
		$consulta="			SELECT DISTINCT p.ruta,

						CONCAT(

							(SELECT codigoGrupoFuncional FROM _497_tablaDinamica WHERE  id__497_tablaDinamica=p.grupoFuncional),' ',

							(SELECT codigoFuncion FROM _498_tablaDinamica WHERE  id__498_tablaDinamica=p.funcion),' ',

							(SELECT codigoSubFuncion FROM _500_tablaDinamica WHERE  id__500_tablaDinamica=p.subFuncion),' ',

							(SELECT codigoPrograma FROM _502_tablaDinamica WHERE  id__502_tablaDinamica=p.programaGasto),' ',

							(SELECT codigoActividadInst FROM _501_tablaDinamica WHERE  id__501_tablaDinamica=p.actividadInstitucional),' ',

							(SELECT codigoProgPresupuestal FROM _503_tablaDinamica WHERE id__503_tablaDinamica=p.partidaPresupuestal)

						) AS programaInstitucional FROM 9117_estructuraPAT p WHERE  codigoInstitucion='".$_SESSION["codigoInstitucion"]."'";
	}
	$arrRutas=$con->obtenerFilasArregloAsocPHP($consulta);

	return $arrRutas;						

}

function obtenerNombreActor($actor)
{
	global $con;
	$consulta="SELECT actor,tipoActor FROM 944_actoresProcesoEtapa WHERE idActorProcesoEtapa=".$actor;

	$filaActor=$con->obtenerPrimeraFila($consulta);
	if($filaActor[1]=="1")
		return obtenerTituloRol($filaActor[0]);
	else
	{
		$consulta=" SELECT c.nombreComite FROM 944_actoresProcesoEtapa pe,234_proyectosVSComitesVSEtapas ce, 2006_comites c WHERE ce.idProyectoVSComiteVSEtapa=pe.actor AND c.idComite=ce.idComite
					 AND pe.idActorProcesoEtapa=".$actor;
		return $con->obtenerValor($consulta);	
	}
}

function rF($cad)
{
	$res=$cad;//cleanFromWord($cad);
	/*$res=str_replace('<p class="MsoNormal"><span style="font-size: 14pt;"> </span></p>',"",$res);
	$res=str_replace('<p class="MsoNormal"><span style="font-size: 13pt;"> </span></p>',"",$res);
	$res=str_replace('<p class="MsoNormal"><span style="font-size: 12pt;"> </span></p>',"",$res);
	$res=str_replace('<p class="MsoNormal"><span style="font-size: 11pt;"> </span></p>',"",$res);
	$res=str_replace('<p class="MsoNormal"><span style="font-size: 10pt;"> </span></p>',"",$res);
	$res=str_replace('<p class="MsoNormal" style="margin-bottom: 0.0001pt;"><span style="font-size: 14pt;"> <o:p></o:p></span></p>',"",$res);
	$res=str_replace('<p class="MsoNormal" style="margin-bottom: 0.0001pt;"><span style="font-size: 15pt;"> <o:p></o:p></span></p>',"",$res);
	$res=str_replace('<p class="MsoNormal" style="margin-bottom: 0.0001pt;"><span style="font-size: 12pt;"> <o:p></o:p></span></p>',"",$res);
	$res=str_replace('<p class="MsoNormal" style="margin-bottom: 0.0001pt;"><span style="font-size: 11pt;"> <o:p></o:p></span></p>',"",$res);
	$res=str_replace('<p class="MsoNormal" style="margin-bottom: 0.0001pt;"><span style="font-size: 10pt;"> <o:p></o:p></span></p>',"",$res);*/
	$res=str_replace("\r","",$res);
	$res=str_replace("\n","",$res);
	
	
	/*$res=str_replace("class","",$res);
	$res=str_replace("style","",$res);
	$res=str_replace("color","",$res);
	$res=str_replace("font","",$res);
	$res=str_replace("fuente","",$res);
	$res=str_replace("line-height","",$res);
	$res=str_replace("=\"MsoNormal\"","",$res);*/
	$res=str_replace("<p> </p>","",$res);
	while(strpos($res,"  ")!==false)
	{
		$res=str_replace("  "," ",$res);
	}
	$res=str_replace("<br /> <br />","<br /><br />",$res);
	
	while(strpos($res,"<br /><br />")!==false)
	{
		$res=str_replace("<br /><br />","<br />",$res);
	}
	return $res;
}

function validarModificacionHorarioAulaExtendido($idAula,$horaI,$horaF,$dia,$idCiclo,$idMateria,$idGrupo,$idRegistro=-1,$formato=1,$fAplicacion=NULL,$objComp=NULL)
{
	global $con;
	$arrFechasMateria=array();
	if($objComp==NULL)
		$arrFechasMateria=obtenerFechaVigenciaMateria($idGrupo);
	else
	{
		if(isset($objComp->esCambioFechaInicio))
		{
			$arrFechasMateria[0]=$objComp->fechaInicio;
			$arrFechasMateria[1]=$objComp->fechaFin;
			
		}
	}

	$colision=false;
	$tablaColisiones="";
	if($formato==1)
		$tablaColisiones="<table><tr><td width=\"300\" align=\"center\"><pan class=\"corpo8_bold\">Materia</td><td width=\"120\" align=\"center\"><pan class=\"corpo8_bold\">Grupo</span></td><td width=\"150\" align=\"center\"><pan class=\"corpo8_bold\">Horario problema</span></td></tr>";
	else
		$tablaColisiones="<table><tr><td width='300' align='center'><pan class='corpo8_bold'>Materia</td><td width='120' align='center'><pan class='corpo8_bold'>Grupo</span></td><td width='150' align='center'><pan class='corpo8_bold'>Horario problema</span></td></tr>";
	if($arrFechasMateria[0]=="")
	{
		return "2|";	
	}
	$comp="";
	if($fAplicacion==NULL)
	{
		$fechaActual=date("Y-m-d");
		$consulta="SELECT MIN(fechaInicio) FROM 4522_horarioGrupo WHERE idGrupo=".$idGrupo;
		$fechaInicioH=$con->obtenerValor($consulta);
		if($fechaInicioH!="")
		{
			$fechaInicioH=strtotime($fechaInicioH);
			if($fechaInicioH<=strtotime($fechaActual))
				  $comp=" and '".$fechaActual."'>=h.fechaInicio and '".$fechaActual."'<=h.fechaFin";
			  else
				  $comp=" and h.fechaInicio='".date("Y-m-d",$fechaInicioH)."' and h.fechaInicio<=h.fechaFin";
		  }
		  
		
		
	}
	else
		$comp=" and '".$fAplicacion."'>=h.fechaInicio and '".$fAplicacion."'<=h.fechaFin";
		
	$consulta="SELECT DISTINCT h.idGrupo FROM 4522_horarioGrupo h, 4520_grupos g WHERE g.idGrupos=h.idGrupo and g.situacion=1 and idAula=".$idAula." and dia=".$dia.$comp;

	$filas=$con->obtenerFilas($consulta);
	$numeroFilas=$con->filasAfectadas;
	if($numeroFilas==0)
	{
		return "1|";
	}
	else
	{
		$mensajeDeColision="";
		while($fila=mysql_fetch_row($filas))	
		{
			  
			  $horaMatProf="SELECT dia,horaInicio,horaFin,fechaInicio,fechaFin FROM 4522_horarioGrupo h where idHorarioGrupo not in (".$idRegistro.") and idGrupo=".$fila[0]." and dia=".$dia." ".$comp." for update";

			  $respuesta=$con->obtenerFilas($horaMatProf);				 
			  $numFilas=$con->filasAfectadas;
			  
			  while($filaMatP=mysql_fetch_row($respuesta))
			  {
				  if(colisionaTiempo($filaMatP[1],$filaMatP[2],$horaI,$horaF))
				  {
					  //$arrFechasMateriaComp=obtenerFechaVigenciaMateria($fila[0]);
					  $arrFechasMateriaComp[0]=$filaMatP[3];
					  $arrFechasMateriaComp[1]=$filaMatP[4];
					  if($arrFechasMateriaComp[0]!="")
					  {
						  if(colisionaTiempo($arrFechasMateria[0],$arrFechasMateria[1],$arrFechasMateriaComp[0],$arrFechasMateriaComp[1]))
						  {
							  $colision=true;
							  $consulta="SELECT nombreGrupo,nombreMateria,o.unidad,a.cveMateria FROM 4520_grupos g,4502_Materias m,817_organigrama o,4512_aliasClavesMateria a  
							  			WHERE m.idMateria=g.idMateria AND idGrupos=".$fila[0]." AND o.codigoUnidad=g.plantel AND a.idMateria=m.idMateria AND a.sede=g.plantel";
							  $filaMat=$con->obtenerPrimeraFila($consulta);
							  
							  $nombre=$filaMat[1];
							  $diaProblema=obtenerNombreDiaExtendido($filaMatP[0]);
							  $grupo=$filaMat[0];
							  if($formato==1)
							  {
							  	$tablaColisiones.="<tr height=\"21\"><td align=\"left\"><span class=\"letraExt\">[".$filaMat[3]."] ".$nombre."<br><b>Plantel</b>: ".$filaMat[2]."</span></td><td align=\"left\"><span class=\"letraExt\">".$grupo."</span></td><td align=\"left\"><span class=\"letraExt\">".$diaProblema." ".
												date("H:i",strtotime($filaMatP[1]))." - ".date("H:i",strtotime($filaMatP[2]))."</span></td></tr>" ;
							  }
							  else
							  {
								  $tablaColisiones.="<tr height='21'><td align='left'><span class='letraExt'>[".$filaMat[3]."] ".$nombre."<br><b>Plantel</b>: ".$filaMat[2]."</span></td><td align='left'><span class='letraExt'>".$grupo."</span></td><td align='left'><span class='letraExt'>".$diaProblema." ".
												date("H:i",strtotime($filaMatP[1]))." - ".date("H:i",strtotime($filaMatP[2]))."</span></td></tr>" ;
							  }
						  }
					  }
				  }
				  
			  }
		}
		$tablaColisiones.="</table>";
		if(!$colision)
		{
			return "1|";
		}
		return "3|".$tablaColisiones;
		
	   
	}
}

function validarHorarioAlumnosExtendido($idUsuario,$idGrupoDestino)
{
	  global $con;
	  $colision=false;
	  $tablaColisiones="<table><tr><td width=\"200\" align=\"center\"><pan class=\"corpo8_bold\">Materia/Grupo problema</span></td><td width=\"150\" align=\"center\"><pan class=\"corpo8_bold\">Horario problema</span></td></tr>";
	  $noFilas=$con->filasAfectadas;
	  $arrFechasMateria=obtenerFechaVigenciaMateria($idGrupoDestino);
	  if($noFilas==0)
	  {
		  return "";
	  }
	  else
	  {
		  $mensajeColision="";
		  
		  $consulta="SELECT dia,horaInicio,horaFin FROM 4522_horarioGrupo WHERE idGrupo=".$idGrupoDestino." order by dia";
		  $res=$con->obtenerFilas($consulta);
		  while($fila=mysql_fetch_row($res))
		  {
			  $conMatAlum="SELECT idGrupo FROM 4517_alumnosVsMateriaGrupo WHERE idGrupo<>".$idGrupoDestino." AND situacion=1 and idUsuario=".$idUsuario;
			  $resAlum=$con->obtenerFilas($conMatAlum);
			  while($filaMat=mysql_fetch_row($resAlum))
			  {
				  $consulta="SELECT dia,horaInicio,horaFin FROM 4522_horarioGrupo WHERE idGrupo=".$filaMat[0]." and dia=".$fila[0];
				  
				  $resMatAlum=$con->obtenerFilas($consulta);
				  while($filaAlum=mysql_fetch_row($resMatAlum))
				  {
					  if(colisionaTiempo($filaAlum[1],$filaAlum[2],$fila[1],$fila[2]))
					  {
						  
							$arrFechasMateriaComp=obtenerFechaVigenciaMateria($filaMat[0]);
							if($arrFechasMateriaComp[0]!="")
							{
								if(colisionaTiempo($arrFechasMateria[0],$arrFechasMateria[1],$arrFechasMateriaComp[0],$arrFechasMateriaComp[1]))
								{
									$nombreAlumno=obtenerNombreUsuario($fila[0]);
									$colision=true;
									$consulta="SELECT nombreGrupo,nombreMateria,o.unidad,a.cveMateria  FROM 4520_grupos g,4502_Materias m,817_organigrama o,4512_aliasClavesMateria a  
												WHERE m.idMateria=g.idMateria AND idGrupos=".$filaMat[0]." AND o.codigoUnidad=g.plantel AND a.idMateria=m.idMateria AND a.sede=g.plantel";
									
							  		$filaM=$con->obtenerPrimeraFila($consulta);
									$nombre=$filaM[1];
									$diaProblema=obtenerNombreDiaExtendido($filaAlum[0]);
									$grupo=$filaM[0];
									$tablaColisiones.="<tr height=\"21\"><td align=\"left\"><span class=\"letraExt\">[".$filaM[3]."] ".$nombre." (Grupo: ".$grupo.")<br><b>Plantel:</b> ".$filaM[2]."</span></td><td align=\"left\"><span class=\"letraExt\">".$diaProblema." ".
													date("H:i",strtotime($filaAlum[1]))." - ".date("H:i",strtotime($filaAlum[2]))."</span></td></tr>" ;
								}
							}
					  }
				  }
			  }
		  }
		  $tablaColisiones.="</table>";
		  if(!$colision)
		  {
			  return "";
		  }
		  else
		  {
			  return $tablaColisiones;
		  }
	  }
}

function obtenerSituacionPlanPeriodo($idInstancia,$idCiclo,$idPeriodo,$idGradoEstructura=-1)
{
	global $con;
	
	$consulta="SELECT sede FROM 4513_instanciaPlanEstudio WHERE idInstanciaPlanEstudio=".$idInstancia;
	$plantel=$con->obtenerValor($consulta);
	
	$consulta="SELECT situacion FROM 4547_situacionInstanciaPlan WHERE idCiclo=".$idCiclo." AND idPeriodo=".$idPeriodo." AND plantel='".$plantel."'  
				ORDER BY idSituacionPlanEstudio DESC";

	$situacion=$con->obtenerValor($consulta);
	if($situacion=="")
	{
		if($idGradoEstructura!=-1)
		{
			$consulta="SELECT dictamen,s.situacion FROM 4615_gradosSolicitudAutorizacionEstructura g,4614_cabeceraSolicitudAutorizacionEstructura s 
						WHERE g.idGradoEstructura=".$idGradoEstructura." AND s.idRegistro=g.idSolicitud ORDER BY g.idRegistro DESC";					
			$fSituacion=$con->obtenerPrimeraFila($consulta);
			
			if(!$fSituacion)
				$situacion=0;		
			else
			{
				if($fSituacion[1]==1)
				{
					$situacion=1;
				}
				else
				{
					switch($fSituacion[0])
					{
						case 1:
							$situacion=3;
						break;
						case 2:
							$situacion=0;
						break;
					}
				}
			}
		}
		else
			$situacion=0;
	}
	return $situacion;
}

function obtenerFechaFinCurso($fechaInicio,$idMateria,$idInstancia,$validarFechaFin=false)
{
	global $con;
	$consulta="SELECT horasSemana,horaMateriaTotal FROM 4502_Materias WHERE idMateria=".$idMateria;

	$filaMat=$con->obtenerPrimeraFila($consulta);			
	$hTotal=$filaMat[1];
	$hSemana=$filaMat[0];
	$consulta="SELECT noHorasSemana FROM 4512_aliasClavesMateria WHERE idInstanciaPlanEstudio=".$idInstancia." AND idMateria=".$idMateria;

	$noHoras=$con->obtenerValor($consulta);
	if($noHoras!="")
		$hSemana=$noHoras;
	if($noHoras==0)
		$numSemanas=0;
	else
		$numSemanas=ceil($hTotal/$noHoras);
	$fechaFin=date("Y-m-d",strtotime("+".$numSemanas." week",strtotime($fechaInicio)));
	$fechaFin="'".date("Y-m-d",strtotime("-1 day",strtotime($fechaFin)))."'";
	
	return $fechaFin;
}

function generarFolioAME($idGrupo)
{
	global $con;
	$consulta="SELECT Plantel,idCiclo,idPeriodo FROM 4520_grupos WHERE idGrupos=".$idGrupo;
	$fDatos=$con->obtenerPrimeraFila($consulta);
	if($con->ct())
	{
		$consulta="SELECT codigoDepto FROM 817_organigrama WHERE codigoUnidad='".$fDatos[0]."'";
		$cveDepto=$con->obtenerValor($consulta);
		$consulta="SELECT folioActual FROM 4550_foliosAME WHERE ciclo=".$fDatos[1]." AND idPeriodo=".$fDatos[2]." AND plantel='".$fDatos[0]."' for update";
		$fActual=$con->obtenerValor($consulta);
		if($fActual=="")
		{
			$consulta="INSERT INTO 4550_foliosAME(plantel,ciclo,idPeriodo,folioActual) VALUES('".$fDatos[0]."',".$fDatos[1].",".$fDatos[2].",2)"; 
			$fActual=1;
		}
		else
		{
			$consulta="update 4550_foliosAME set folioActual=folioActual+1 where plantel='".$fDatos[0]."' and ciclo=".$fDatos[1]." and idPeriodo=".$fDatos[2]; 
		}
		$con->ejecutarConsulta($consulta);
		$con->fT();
		return $cveDepto."-".str_pad($fActual,5,"0",STR_PAD_LEFT);
	}
}

function calcularTabuladorPlanPagos($objParametro)
{
	global $con;
	$consulta="select idCostoConcepto from 6011_costoConcepto where plantel='".$objParametro->plantel."' and idInstanciaPlanEstudio=".$objParametro->idInstanciaPlanEstudio." and idCiclo=".$objParametro->idCiclo." 
				and idPeriodo=".$objParametro->idPeriodo." and idConcepto=".$objParametro->idConcepto." and tipoConcepto=".$objParametro->tipoConcepto." and idElemento=".$objParametro->idElemento." and tipoElemento=".$objParametro->tipoElemento;
	$idRegistro=$con->obtenerValor($consulta);
	$x=0;
	$query[$x]="begin";
	$x++;
	$query[$x]="delete from 6015_tabuladorPlanPagosConcepto where idCostoConcepto=".$idRegistro;
	$x++;
	$consulta="SELECT * FROM _673_tablaDinamica ";
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		$objRes=calcularPlanPagos($objParametro->valor,$fila[0],$objParametro);
		
		foreach($objRes as $o)
		{
			$query[$x]="INSERT INTO 6015_tabuladorPlanPagosConcepto(idCostoConcepto,idPlanPago,noPago,pagoIndividual,
						montoDescuento1,pagoDescuento1,montoDescuento2,pagoDescuento2,montoCargo1,pagoCargo1,montoCargo2,pagoCargo2,decimalAcumuladoPago,decimalAcumuladoDescuento1,decimalAcumuladoDescuento2,decimalAcumuladoCargo1,decimalAcumuladoCargo2)
						VALUES(".$idRegistro.",".$fila[0].",".$o["noPago"].",".$o["pagoIndividual"].",".$o["montoDescuento1"].",".$o["pagoDescuento1"].
						",".$o["montoDescuento2"].",".$o["pagoDescuento2"].",".$o["montoCargo1"].",".$o["pagoCargo1"].",".$o["montoCargo2"].",".$o["pagoCargo2"].",".$o["decimalMontoPago"].",".$o["decimalMontoDescuento1"].",".$o["decimalMontoDescuento2"]."
						,".$o["decimalMontoCargo1"].",".$o["decimalMontoCargo2"].")";
			$x++;
		}
	}
	$query[$x]="commit";
	$x++;
	return $con->ejecutarBloque($query);
			
}

function calcularPlanPagos($monto,$idPlan,$objParametro)
{
	global $con;
	$decimalesIndividual=0;
	$decimalesPago=0;
	$acumularDecimales=true;
	
	$montoDecimalAcumulado=0;
	$montoDecimalAcumulado1=0;
	$montoDecimalAcumulado2=0;
	$montoDecimalAcumuladoCargo1=0;
	$montoDecimalAcumuladoCargo2=0;
	$ultimoPago=false;
	
	$consulta="SELECT * FROM _673_tablaDinamica WHERE id__673_tablaDinamica=".$idPlan;
	$fPlan=$con->obtenerPrimeraFila($consulta);
	$decimalesIndividual=$fPlan[31];
	$decimalesPago=$fPlan[32];
	if($fPlan[33]==0)
		$acumularDecimales=false;
	
	$totalPagos=$fPlan[12];
	$arrPagos=array();
	$pagoIndividual=normalizarNumero(number_format($monto/$totalPagos,$decimalesIndividual));
	$acumPago=0;
	$ct=1;
	$consulta="SELECT * FROM _673_gridPagosPlan WHERE idReferencia=".$fPlan[0]." order by noPago";
	$res=$con->obtenerFilas($consulta);
	$nPagos=$con->filasAfectadas;
	
	while($fila=mysql_fetch_row($res))
	{
		$obj=array();

		$montoPago=normalizarNumero(number_format($pagoIndividual*$fila[4],$decimalesPago));

		if($acumularDecimales)
		{
			$montoPago=parteEntera($montoPago,false);
			$montoDecimalAcumulado+="0.".parteDecimal($montoPago);
			$obj["decimalMontoPago"]="0.".parteDecimal($montoPago);
		}
		if($ct==$nPagos)
		{
			$montoPago=($monto-$acumPago)+$montoDecimalAcumulado;

			$ultimoPago=true;
		}
		if(($acumPago+$montoPago)>$monto)
		{
			$montoPago=$monto-$acumPago;
			
		}
		
		$acumPago+=$montoPago;
		$obj["noPago"]=$fila[5];
		$obj["pagoIndividual"]=$montoPago;
		
		//Descuento 1
		$obj["montoDescuento1"]=0;
		$obj["pagoDescuento1"]=$montoPago;
		$obj["decimalMontoDescuento1"]=0;
		$tAccion=$fPlan[13];
		$mAplicacion=$montoPago;
		$vAplicacion=$fPlan[14];
		if($tAccion==3)
			$vAplicacion=$fPlan[15];
		$resObj=calcularValoresAplicacion($tAccion,$mAplicacion,$vAplicacion,$objParametro,$decimalesPago,-1);
		
		$pago=$resObj["pagoDescuento"];
		if($acumularDecimales)
		{
			$montoDecimalAcumulado1+="0.".parteDecimal($pago);
			$obj["decimalMontoDescuento1"]="0.".parteDecimal($pago);
			$pago=parteEntera($pago,false);
		}
		$obj["pagoDescuento1"]=$pago;
		if($ultimoPago)
		{
			$obj["pagoDescuento1"]+=$montoDecimalAcumulado1;
			
			
		}
		$obj["montoDescuento1"]=$mAplicacion-$obj["pagoDescuento1"];//$resObj["montoDescuento"];

		//Descuento 2
		$obj["montoDescuento2"]=0;
		$obj["pagoDescuento2"]=$montoPago;
		$obj["decimalMontoDescuento2"]=0;
		$tAccion=$fPlan[17];
		$mAplicacion=$montoPago;
		if($fPlan[21]==2)
			$mAplicacion=$obj["pagoDescuento1"];
		
		$vAplicacion=$fPlan[18];
		if($tAccion==3)
			$vAplicacion=$fPlan[19];
		$resObj=calcularValoresAplicacion($tAccion,$mAplicacion,$vAplicacion,$objParametro,$decimalesPago,-1);
		
		$pago=$resObj["pagoDescuento"];
		if($acumularDecimales)
		{
			
			$montoDecimalAcumulado2+="0.".parteDecimal($pago);
			$obj["decimalMontoDescuento2"]="0.".parteDecimal($pago);
			$pago=parteEntera($pago,false);
		}
		
		$obj["pagoDescuento2"]=$pago;
		if($ultimoPago)
		{
			$obj["pagoDescuento2"]+=$montoDecimalAcumulado2;
			
		}
		$obj["montoDescuento2"]=$mAplicacion-$obj["pagoDescuento2"];//$resObj["montoDescuento"];
		//Cargo 1
		$obj["montoCargo1"]=0;
		$obj["pagoCargo1"]=$montoPago;
		$obj["decimalMontoCargo1"]=0;
		$tAccion=$fPlan[22];
		$mAplicacion=$montoPago;
		$vAplicacion=$fPlan[23];
		if($tAccion==3)
			$vAplicacion=$fPlan[24];
		$resObj=calcularValoresAplicacion($tAccion,$mAplicacion,$vAplicacion,$objParametro,$decimalesPago,1);
		$pago=$resObj["pagoDescuento"];
		if($acumularDecimales)
		{
			
			$montoDecimalAcumuladoCargo1+="0.".parteDecimal($pago);
			$obj["decimalMontoCargo1"]="0.".parteDecimal($pago);
			$pago=parteEntera($pago,false);
		}
		$obj["pagoCargo1"]=$pago;
		if($ultimoPago)
		{
			$obj["pagoCargo1"]+=$montoDecimalAcumuladoCargo1;
			
		}
			
		$obj["montoCargo1"]=$obj["pagoCargo1"]-$mAplicacion;//$resObj["montoDescuento"];
		//Cargo 2
		$obj["montoCargo2"]=0;
		$obj["pagoCargo2"]=$montoPago;
		$obj["decimalMontoCargo2"]=0;
		$tAccion=$fPlan[26];
		$mAplicacion=$montoPago;
		$vAplicacion=$fPlan[27];
		if($tAccion==3)
			$vAplicacion=$fPlan[28];
		if($fPlan[29]==2)
			$mAplicacion=$obj["pagoCargo1"];	
			
		$resObj=calcularValoresAplicacion($tAccion,$mAplicacion,$vAplicacion,$objParametro,$decimalesPago,1);
		$pago=$resObj["pagoDescuento"];
		if($acumularDecimales)
		{
			
			$montoDecimalAcumuladoCargo2+="0.".parteDecimal($pago);
			$obj["decimalMontoCargo2"]="0.".parteDecimal($pago);
			$pago=parteEntera($pago,false);
		}
		$obj["pagoCargo2"]=$pago;
		if($ultimoPago)
		{
			$obj["pagoCargo2"]+=$montoDecimalAcumuladoCargo2;
			
		}
		$obj["montoCargo2"]=$obj["pagoCargo2"]-$mAplicacion;//$resObj["montoDescuento"];
		array_push($arrPagos,$obj);
		$ct++;
	}
	return $arrPagos;

}


function calcularValoresAplicacion($tAplicacion,$montoPago,$vDescuento,$objParametro,$decimales,$accion)
{
	$obj=array();
	switch($tAplicacion)
	{
		
		case 0:
			$obj["montoDescuento"]=0;
			$obj["pagoDescuento"]=$montoPago;
		break;
		case 1: //porcentaje

			$descuento=number_format($montoPago*($vDescuento/100),$decimales);
			$descuento=str_replace(",","",$descuento);
			$obj["montoDescuento"]=$descuento;
			$obj["pagoDescuento"]=$montoPago+($descuento*$accion);
		break;
		case 2: //valor absoluto

			$descuento=number_format($vDescuento,$decimales);
			$descuento=str_replace(",","",$descuento);
			$obj["montoDescuento"]=$descuento;
			$obj["pagoDescuento"]=$montoPago+($descuento*$accion);
		break;
		case 3: //Funcion sistema
			$fDescuento=$vDescuento;
			$cadObj='{}';
			$obj=json_decode($cadObj);
			$obj->param1=$objParametro;
			$cache=NULL;
			$descuento=resolverExpresionCalculoPHP($fDescuento,$obj,$cache);	
			$descuento=number_format($descuento,$decimales);
			$descuento=str_replace(",","",$descuento);
			$obj["montoDescuento"]=$descuento;
			$obj["pagoDescuento"]=$montoPago+($descuento*$accion);
			
		break;
		default:
			$descuento=str_replace(",","",$descuento);
			$obj["montoDescuento"]=0;
			$obj["pagoDescuento"]=$montoPago;
		break;
	}
	return $obj;
}

function obtenerNuevoActorProceso($actor,$idFormulario,$idRegistro,$idEtapa="",$nRegistro="0",$idPerfil=-1)
{
	global $con;
	$idEstado=$idEtapa;
	if($idEtapa=="")
	{
		$consulta="select idEstado from _".$idFormulario."_tablaDinamica where id__".$idFormulario."_tablaDinamica=".$idRegistro;
		$idEstado=$con->obtenerValor($consulta);
	}
	
	if($nRegistro==0)
		$consulta="SELECT * FROM 944_actoresProcesoEtapa WHERE idActorProcesoEtapa=".$actor;
	else
		$consulta="SELECT '' as id,actor,'1' as tipo,'' as tmp,idProceso FROM 949_actoresVSAccionesProceso WHERE idActorVSAccionesProceso=".$actor;
	$fActor=$con->obtenerPrimeraFila($consulta);
	$consulta="SELECT idActorProcesoEtapa FROM 944_actoresProcesoEtapa WHERE idProceso=".$fActor[4]." AND 
				numEtapa=".$idEstado." AND actor='".$fActor[1]."' AND tipoActor=".$fActor[2]." and idPerfil=".$idPerfil;

	
	$idActor=$con->obtenerValor($consulta);
	if($idActor=="")
		$idActor=0;
	return $idActor;
}

function obtenerClaveProgramatica($idDetalle)
{
	global $con;
	$consulta="SELECT idPerfilEstructura FROM 9117_definicionEstructuraProgramatica d,9117_detalleEstructuraProgramatica e WHERE d.idDefinicion=e.idDefinicion AND e.idDetalle=".$idDetalle;
	$idPerfil=$con->obtenerValor($consulta);
	if($idPerfil=="")
		$idPerfil=-1;
	$consulta="SELECT nombrePerfil,configuracion FROM 9117_perfilesEstructuraProgramatica WHERE idPerfilEstructura=".$idPerfil;
	$fPerfil=$con->obtenerPrimeraFila($consulta);
	$lblPerfil=$fPerfil[0];
	$configuracion=$fPerfil[1];
	$clave="";
	if($configuracion!="")
	{
		$objConf=json_decode($configuracion);
		if(sizeof($objConf->arrConfiguracion)>0)
		{
			
			foreach($objConf->arrConfiguracion as $o)
			{
				$query="(select ".$o->campoCodigo."  from ".$o->tablaOrigen." where ".$o->campoId."=@orden_".$o->orden.") as orden_".$o->orden;
				$arrQuery[$o->orden]=$query;
				
				
			}
			$consulta="SELECT definicion,idDetalle FROM 9117_detalleEstructuraProgramatica WHERE idDetalle=".$idDetalle;
			$fAux=$con->obtenerPrimeraFila($consulta);
			$cadConsulta="";
			$arrElementos=explode("_",$fAux[0]);
			foreach($objConf->arrConfiguracion as $o)
			{
				if($cadConsulta=="")
					$cadConsulta=str_replace("@orden_1",$arrElementos[($o->orden-1)],$arrQuery[$o->orden]);
				else
					$cadConsulta.=",".str_replace("@orden_1",$arrElementos[($o->orden-1)],$arrQuery[$o->orden]);
			}
			$cadConsulta="select ".$cadConsulta;
			
			$oReg=$con->obtenerPrimeraFila($cadConsulta);
			foreach($oReg as $c)
			{
				if($clave=="")
					$clave=$c;
				else
					$clave.=" ".$c;
			}
			
		}
	}
	return $clave;
}



function obtenerProgramasInstitucionalesVigentes($idCiclo,$formato) //1 arreglo php,2 arreglo java
{
	global $con;
	$resultado="";
	$consulta="SELECT idDefinicion FROM 9117_definicionEstructuraProgramatica WHERE ciclo=".$idCiclo;

	$idDefinicion=$con->obtenerValor($consulta);

	if($idDefinicion=="")
		$idDefinicion=-1;
	$arrProgramas=array();
	$consulta="SELECT definicion,idDetalle FROM 9117_detalleEstructuraProgramatica WHERE idDefinicion=".$idDefinicion;

	$resDef=$con->obtenerFilas($consulta);
	while($fAux=mysql_fetch_row($resDef))
	{
		$clave=obtenerClaveProgramatica($fAux[1]);
		$query="SELECT idEstructuraVSPrograma,p.cvePrograma,p.tituloPrograma FROM 9117_estructurasVSPrograma e, 517_programas p 
							WHERE p.idPrograma=e.idProgramaInstitucional and  idPartidaPresupuestal=".$fAux[1]." order by p.cvePrograma,p.tituloPrograma";
		
		$resProgramas=$con->obtenerFilas($query);
		while($fPrograma=mysql_fetch_row($resProgramas))
		{
			$arrProgramas["[ ".$clave." ".$fPrograma[1]." ] ".$fPrograma[2]]=$fPrograma[0];
		}
	}
	$cadRegistros="";
	ksort($arrProgramas);
	if(sizeof($arrProgramas)>0)
	{
		if($formato==2)
		{
			foreach($arrProgramas as $programa=>$id)
			{
				$o="['".$id."','".$programa."']";
				if($cadRegistros=="")
					$cadRegistros=$o;
				else
					$cadRegistros.=",".$o;
			}
			$resultado="[".$cadRegistros."]";
		}
		else
		{
			$resultado=array();
			foreach($arrProgramas as $programa=>$id)
			{
				$resultado[$id]=$programa;
			}
			
		}
		
	}
	else
	{
		if($formato==2)
			return "[]";
		else
			return array();
	}
	
	return $resultado;
}

function generarConsultaIntervalos($fechaInicio,$fechaFin,$nombreCampoInicio,$nombreCampoFechaFin,$considerarLimites=true)
{
	$cadena="";
	if($considerarLimites)
		$cadena="(('".$fechaFin."'>=".$nombreCampoInicio." and '".$fechaFin."'<=".$nombreCampoFechaFin.") or('".$fechaInicio."'>=".$nombreCampoInicio." and '".$fechaInicio."'<=".$nombreCampoFechaFin.") or ('".$fechaInicio."'<=".$nombreCampoInicio." and '".$fechaFin."'>=".$nombreCampoFechaFin."))";
	else
		$cadena="(('".$fechaFin."'>".$nombreCampoInicio." and '".$fechaFin."'<".$nombreCampoFechaFin.") or('".$fechaInicio."'>".$nombreCampoInicio." and '".$fechaInicio."'<".$nombreCampoFechaFin.") or ('".$fechaInicio."'<=".$nombreCampoInicio." and '".$fechaFin."'>=".$nombreCampoFechaFin."))";
	return $cadena;
}

function registrarDocumentoServidor($idDocumento,$nombreDocumento)
{
	global $con;
	global $directorioInstalacion;
	global $guardarArchivosBD;
	$binario_nombre_temporal=$directorioInstalacion.'/archivosTemporales/'.$idDocumento;
	$binario_nombre=$nombreDocumento;
	$binarioPeso=filesize($binario_nombre_temporal);
	$binario_tipo="application/octet-stream";
	
	if(!file_exists($binario_nombre_temporal))
		return -1;
	
	$sha512=strtoupper(hash_file("sha512" ,$binario_nombre_temporal,false));
		
	if($guardarArchivosBD)
	{
		$binario_contenido = addslashes(fread(fopen($binario_nombre_temporal, "rb"),$binarioPeso));
		
		$consulta_insertar = "insert into 908_archivos(documento,fechaCreacion,responsable,nomArchivoOriginal,tipoArchivo,tamano,enBD,sha512)
								values('".$binario_contenido."','".date('Y-m-d H:i:s')."',".$_SESSION["idUsr"].",
								'".$binario_nombre."','".$binario_tipo."','".$binarioPeso."',1,'".$sha512."')";
	
		if(!$con->ejecutarConsulta($consulta_insertar))
		{
			return -1;
			
		}
		
		$idArchivo=$con->obtenerUltimoID();
		unlink($binario_nombre_temporal);
		
	}
	else
	{
		
		$consulta_insertar = "insert into 908_archivos(documento,fechaCreacion,responsable,nomArchivoOriginal,tipoArchivo,tamano,enBD,sha512)
								values(NULL,'".date('Y-m-d H:i:s')."',".$_SESSION["idUsr"].",
								'".$binario_nombre."','".$binario_tipo."','".$binarioPeso."',0,'".$sha512."')";

		if(!$con->ejecutarConsulta($consulta_insertar))
		{
			return -1;
			
		}
		$idArchivo=$con->obtenerUltimoID();
		
		copy($binario_nombre_temporal,$directorioInstalacion."/documentosUsr/archivo_".$idArchivo);
		unlink($binario_nombre_temporal);

	}
	return $idArchivo;
}

function removerDocumentoServidor($idDocumento)
{
	global $con;
	global $directorioInstalacion;
	$binarioArchivo=$directorioInstalacion."/documentosUsr/archivo_".$idDocumento;
	
	$consulta="DELETE FROM 908_archivos WHERE idArchivo=".$idDocumento;
	if($con->ejecutarConsulta($consulta))
	{
		if(file_exists($binarioArchivo))
			unlink($binarioArchivo);
		return true;
	}
	return false;
	
}

function cleanFromWord( $textoword ) 
{
    $textoword = ereg_replace( '<(/)?(font|span|del|ins)[^>]*>', "", $textoword );
    $textoword = ereg_replace( '<([^>]*)(class|lang|style|size|face)=("[^"]*"|\'[^\']*\'|[^>]+)([^>]*)>', "<\1>", $textoword );
    $textoword = ereg_replace( '<([^>]*)(class|lang|style|size|face)=("[^"]*"|\'[^\']*\'|[^>]+)([^>]*)>', "<\1>", $textoword );

    return( $textoword );
}

function cc($cadena)
{
	return gzdeflate($cadena);
}

function dc($cadena)
{
	return gzinflate($cadena);
}

function obtenerNombreComite($idComite)
{
	global $con;
	$consulta="SELECT nombreComite FROM 2006_comites WHERE idComite=".$idComite;	
	$nombreComite=$con->obtenerValor($consulta);
	return $nombreComite;
}


function obtenerIdPerfilEscenario($idProceso,$tipoActor,$actor,$activo=false)
{
	global $con;
	$consulta="SELECT idPerfil FROM 206_perfilesEscenarios WHERE idProceso=".$idProceso." and tipoActor=".$tipoActor." AND actor='".$actor."'";	

	if($activo)
		$consulta.=" and situacion=1";
	$idPerfil=$con->obtenerValor($consulta);
	if($idPerfil=="")
		$idPerfil=-1;
	return $idPerfil;
}

function generarDomicilioFiscalCliente($idCliente)
{
	global $con;
	$consulta="SELECT * FROM 6927_empresas WHERE idEmpresa=".$idCliente;
	$fEmpresa=$con->obtenerPrimeraFila($consulta);
	$domicilio="Calle:";
	if($fEmpresa[8]!="")
		$domicilio.=" ".$fEmpresa[8];
	else
		$domicilio.=" N/E";
	$domicilio.=" No.";	
	if($fEmpresa[9]!="")
		$domicilio.=" ".$fEmpresa[9];
	else
		$domicilio.=" N/E";
	
	if($fEmpresa[19]!="")
		$domicilio.=" No. Int. ".$fEmpresa[19];
	
	$domicilio.=" Colonia:";	
	if($fEmpresa[10]!="")
		$domicilio.="  ".$fEmpresa[10].".";	
	else
		$domicilio.=" N/E.";
	$municipio="";
	if($fEmpresa[14]!="")
	{
		$consulta="SELECT municipio FROM 821_municipios WHERE cveMunicipio='".$fEmpresa[14]."'";	
		$municipio=$con->obtenerValor($consulta);
		$domicilio.=" ".$municipio;
	}
	if($fEmpresa[13]!="")
	{
		$consulta="SELECT estado FROM 820_estados WHERE cveEstado='".$fEmpresa[13]."'";	
		$estado=$con->obtenerValor($consulta);
		if($municipio!="")
			$domicilio.=", ".$estado.".";
		else
			$domicilio.=" ".$estado.".";
			
	}
	return $domicilio;
}


function registrarDocumentoServidorRepositorio($idDocumento,$nombreDocumento,$idCategoria=6,$descripcion="")
{
	global $con;
	global $directorioInstalacion;
	global $guardarArchivosBD;
	$binario_nombre_temporal=$directorioInstalacion.'/archivosTemporales/'.$idDocumento;
	$binario_nombre=$nombreDocumento;
	
	$arrBinarioNombre=explode(".",$binario_nombre);
	
	$nombre="";
	for($x=0;$x<=sizeof($arrBinarioNombre)-2;$x++)
	{
		if($nombre=="")
			$nombre=$arrBinarioNombre[$x];
		else
			$nombre.="_".$arrBinarioNombre[$x];
	}
	$nombre.=".".$arrBinarioNombre[sizeof($arrBinarioNombre)-1];
	
	$binario_nombre=$nombre;	
	$binario_nombre=str_replace(" ","_",$binario_nombre);

	$binarioPeso=filesize($binario_nombre_temporal);
	$binario_tipo="application/octet-stream";	
	if(!file_exists($binario_nombre_temporal))
		return -1;
		
	$sha512=strtoupper(hash_file ( "sha512" , $binario_nombre_temporal,false ));	
		
	if($guardarArchivosBD)
	{
		$binario_contenido = addslashes(fread(fopen($binario_nombre_temporal, "rb"),$binarioPeso));
		$binario_pkcs7="";
		if(file_exists($binario_nombre_temporal.".pkcs7"))
			$binario_pkcs7=addslashes(fread(fopen($binario_nombre_temporal.".pkcs7", "rb"),filesize($binario_nombre_temporal.".pkcs7")));
		
		$consulta_insertar = "insert into 908_archivos(documento,fechaCreacion,responsable,nomArchivoOriginal,tipoArchivo,
							tamano,enBD,tipoDocumento,categoriaDocumentos,descripcion,documentoPKCS7,sha512)
								values('".$binario_contenido."','".date('Y-m-d H:i:s')."',".$_SESSION["idUsr"].",
								'".cv($binario_nombre)."','".$binario_tipo."','".$binarioPeso."',1,2,".$idCategoria.
								",'".cv($descripcion)."','".$binario_pkcs7."','".$sha512."')";

		if(!$con->ejecutarConsulta($consulta_insertar))
		{
			return -1;
			
		}
		$idArchivo=$con->obtenerUltimoID();
		unlink($binario_nombre_temporal);
		
	}
	else
	{
		$consulta_insertar = "insert into 908_archivos(documento,fechaCreacion,responsable,nomArchivoOriginal,tipoArchivo,tamano,enBD,tipoDocumento,categoriaDocumentos,sha512)
								values(NULL,'".date('Y-m-d H:i:s')."',".$_SESSION["idUsr"].",
								'".cv($binario_nombre)."','".$binario_tipo."','".$binarioPeso."',0,2,".$idCategoria.",'".$sha512."')";

		if(!$con->ejecutarConsulta($consulta_insertar))
		{
			return -1;
		}
		$idArchivo=$con->obtenerUltimoID();
		
		copy($binario_nombre_temporal,$directorioInstalacion."/repositorioDocumentos/documento_".$idArchivo);
		unlink($binario_nombre_temporal);
		
		if(file_exists($binario_nombre_temporal.".pkcs7"))
		{
			copy($binario_nombre_temporal.".pkcs7",$directorioInstalacion."/repositorioDocumentos/documento_".$idArchivo.".pkcs7");
			unlink($binario_nombre_temporal.".pkcs7");
		}

	}
	return $idArchivo;
}

function marcarTareaAtendida($iA,$iTablero,$iUsuarioAtendio=-1,$afectarTareasPadres=true,$afectarTareasDelegadas=true)
{
	if($iUsuarioAtendio==-1)
		$iUsuarioAtendio=$_SESSION["idUsr"];
	$iTareaRaiz=$iA;
	if($afectarTareasPadres)
		$iTareaRaiz=obtenerTareaRaiz($iTablero,$iA);	
	return setTareaAtendida($iTareaRaiz,$iTablero,$iUsuarioAtendio,$afectarTareasDelegadas);	
	
}

function obtenerTareaRaiz($iTablero,$iActividad)
{
	global $con;
	$nombreTablero="9060_tableroControl_".$iTablero;
	$consulta="SELECT idNotificacionBase FROM ".$nombreTablero." WHERE idRegistro=".$iActividad;
	$idNotificacionBase=$con->obtenerValor($consulta);
	if($idNotificacionBase!="")
		return obtenerTareaRaiz($iTablero,$idNotificacionBase);
	return $iActividad;
}


function setTareaAtendida($iA,$iTablero,$iUsuarioAtendio,$afectarTareasDelegadas=true)
{
	global $con;
	$nombreTablero="9060_tableroControl_".$iTablero;
	$x=0;
	$query[$x]="begin";
	$x++;
	$query[$x]="UPDATE ".$nombreTablero." SET idEstado=2,usuarioAtendio=".$iUsuarioAtendio.
				",fechaAtencion='".date("Y-m-d H:i:s")."' where idRegistro=".$iA;
	$x++;
	$query[$x]="UPDATE 3000_documentosAsignadosAtencion SET situacionActual=1,fechaAtencion='".date("Y-m-d H:i:s")."' WHERE idTareaAsociada=".$iA." AND idTableroTarea=".$iTablero;
	$x++;
	$query[$x]="commit";
	$x++;
	if($con->ejecutarBloque($query))
	{
		if($afectarTareasDelegadas)
		{
			$consulta="SELECT idRegistro FROM ".$nombreTablero." WHERE idNotificacionBase=".$iA;
			$res=$con->obtenerFilas($consulta);
			while($fila=mysql_fetch_row($res))
			{
				setTareaAtendida($fila[0],$iTablero,$iUsuarioAtendio);
			}
		}
		return true;
	}
}

function registrarBitacoraEjecucionActividadTemporal($tActividad)
{
	global $con;
	$consulta="INSERT INTO 9075_bitacoraEjecucionActividadTemporal(fechaInicioEjecucion,tipoActividad,resultado) 
			VALUES('".date("Y-m-d H:i:s")."',".$tActividad.",0)";
	if($con->ejecutarConsulta($consulta))
	{
		return $con->obtenerUltimoID();
	}
}


function actualizarRegistroBitacoraEjecucionActividadTemporal($idRegistro,$situacion,$comentariosAdicionales)
{
	global $con;
	$consulta="update 9075_bitacoraEjecucionActividadTemporal set resultado=".$situacion.
				",comentariosAdicionales='".cv($comentariosAdicionales)."',fechaTerminoEjecucion='".
				date("Y-m-d H:i:s")."' where idRegistro=".$idRegistro;
	return $con->ejecutarConsulta($consulta);
}
?>