<?php
	include_once("latis/conexionBD.php");
	$consultaConectores="SELECT archivoInclude FROM 250_conectoresSistema";
	$resConector=$con->obtenerFilas($consultaConectores);
	
	while($filaConector=mysql_fetch_row($resConector))
	{
		include_once($filaConector[0]); 	
	}
	
	function generarInstanciaConector($idConexion)
	{
		global $con;
		$consulta="SELECT * FROM 251_conexionesSistema WHERE idConexion=".$idConexion;
		$fConexion=$con->obtenerPrimeraFila($consulta);
		
		$consulta="SELECT nombreClase FROM 250_conectoresSistema WHERE idTipoConector=".$fConexion[7];		
		$nClase=$con->obtenerValor($consulta);
		$conAux=NULL;
		eval('$conAux=new '.$nClase.'("'.$fConexion[3].'","'.$fConexion[8].'","'.$fConexion[4].'","'.$fConexion[5].'","'.$fConexion[6].'");');
		return $conAux;
	}
	
	function resolverQueries($id,$tipoDataSet,$paramDatos,$pNombreCampoCompleto=false,$vRegistros=false,$ejecutarConsultas=false,$conBD=NULL)
	{
		global $con;
		
		

		if($id=="")
			$id=-1;
		$conAux="";
		$conexionBD=$con;
		if($conBD!=NULL)
			$conexionBD=$conBD;
		
		$nIntentosResolucion=20;
		$nCiclos=0;
		$arrConexiones=array();
		$arrConexiones[0]=$conexionBD;
		$modoDebugger=false;
		if(isset($_SESSION["debuggerQueries"])&&($_SESSION["debuggerQueries"]==1))
			$modoDebugger=true;
	
		$arrOrden[0]="ASC";
		$arrOrden[1]="DESC";
		$arrCamposFrmTablas=array();
		$consulta="SELECT campoUsr,campoMysql FROM 9017_camposControlFormulario";
		$resCampo=$conexionBD->obtenerFilas($consulta);
		while($filaCampo=mysql_fetch_row($resCampo))
		{
			$arrCamposFrmTablas[$filaCampo[0]]=$filaCampo[1];
		}
	
		$consulta="select nodosFiltro,camposProy,operacion,nTabla,parametros,idDataSet,tipoAlmacen,nombreDataSet,orden,complementario,idConexion,obtenerValoresDistintos,numRegistros from 
				9014_almacenesDatos where idReporte=".$id." and tipoDataSet=".$tipoDataSet." and tipoAlmacen<>2 order by idDataSet asc";

		$resConAux=$conexionBD->obtenerFilas($consulta);
		$arrValorParamReporte=array();
		$arrResultConsulta=array();
		$arrValorParamAlmacen=array();
		$finalizarConsulta=false;
		
		while(!$finalizarConsulta)
		{

			$nEsperaResolver=0;
	
			while($filaCon=mysql_fetch_row($resConAux))
			{
				
				$numRegistros=$filaCon[12];
				if(!isset($arrConexiones[$filaCon[10]]))
				{
					$arrConexiones[$filaCon[10]]=generarInstanciaConector($filaCon[10]);
					if(!$arrConexiones[$filaCon[10]]->conexion)
						continue;
				}
				if((!isset($arrResultConsulta[$filaCon[5]]))||(!isset($arrResultConsulta[$filaCon[5]]["tipoAlmacen"])))
				{
					
					$arrResultConsulta[$filaCon[5]]["camposFormularioReferencia"]=array();
					$esperaResolver=false;
					$arrResp=validarConsultaAlmacen($filaCon[5],$conexionBD);
					if(sizeof($arrResp)>0)
					{
					?>
						<table>
							<tr>
								<td align="center">
									<span class="corpo8_bold">
									No se ha podido construir el módulo debido al siguiente problema:
									</span>
								</td>
							</tr>
							<tr>
								<td align="left"><br /><br />
								<span class="copyrigthSinPadding">
			
								<?php
									foreach($arrResp as $problema)
									{
										echo "<br><img src='../images/bullet_green.png'>".$problema." <b>".$filaCon[7]."</b><br>";
									}
								?>
								</span>
								<br /><br /><br />
								<span class="letraRojaSubrayada8">
								Se recomienda reportar el problema al administrador del sistema para su corrección.
								</span>
								</td>
							</tr>
						</table>
					<?php	
						return false;	
					}
					
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
							if($filaCon[10]==0)
							{
								$consulta="select idFormulario from 900_formularios where nombreTabla='".$nTabla."'";
								$idFormulario=$conexionBD->obtenerValor($consulta);
								$consulta="SELECT tipoElemento,idGrupoElemento,nombreCampo FROM 901_elementosFormulario WHERE idFormulario=".$idFormulario;
				
								$resElementoFormulario=$conexionBD->obtenerFilas($consulta);
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
								$arrElementosTabla[$filaCon[10]."@".$nTabla]=$arrElementos;
							}
							else
							{
								$conAux=$arrConexiones[$filaCon[10]];
								if($conAux->esSistemaLatis())
								{
									$consulta="select idFormulario from 900_formularios where nombreTabla='".$nTabla."'";
									$idFormulario=$conAux->obtenerValor($consulta);
									$consulta="SELECT tipoElemento,idGrupoElemento,nombreCampo FROM 901_elementosFormulario WHERE idFormulario=".$idFormulario;
					
									$resElementoFormulario=$conAux->obtenerFilas($consulta);
									$arrElementos=array();
									while($filaElemento=$conAux->obtenerSiguienteFila($resElementoFormulario))
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
									$arrElementosTabla[$filaCon[10]."@".$nTabla]=$arrElementos;
								}
							}
						}
					}	
					
					$arrParamControl="";
					$arrParamPendientes=array();
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
							
							$filaParam=$conexionBD->obtenerPrimeraFila($consulta);
							
							
							if(!$filaParam)
								$arrValorParamAlmacen[substr($param,1)]="-100584";
							else
							{
								$valor="";
								switch($filaParam[1])
								{
									case "1":  //Constante
										$valor=$filaParam[0];
									break;
									case "2":  //Parametro reporte
										$consulta="select parametro from 9015_parametrosReporte where idParametro=".$filaParam[0];
										$parametroRep=$conexionBD->obtenerValor($consulta);
										eval('$valor=$paramDatos->p2->p'.$parametroRep.';');
									break;
									case "3":  //Valor de sesion
										$consulta="select valorSesion,valorReemplazo from 8003_valoresSesion where idValorSesion=".$filaParam[0];
										
										$filaSesion=$conexionBD->obtenerPrimeraFila($consulta);
										if(($filaParam[0]==1)||($filaParam[0]==4))
											$valor=$_SESSION[$filaSesion[0]];
										else
											$valor=$_SESSION[$filaSesion[0]];
										if($valor=="")
											$valor="-1";
									break;
									case "4":  //Valor de sistema
										$consulta="select valorSesion,valorReemplazo from 8003_valoresSesion where idValorSesion=".$filaParam[0];
										$filaSesion=$conexionBD->obtenerPrimeraFila($consulta);
										$valorSistema="";
										switch($filaParam[0])
										{
											case "8":
												$valorSistema=date("Y-m-d");
											break;
											case "9":
												$valorSistema=date("H:is");
											break;
											case "10":
												$valorSistema=date("Y-m-d H:i:s");
											break;
										}
										$valor=$valorSistema;
									break;
									case "7":  //Consulta auxiliar
										if(($filaParam[0]<$filaCon[5])||(isset($arrResultConsulta[$filaParam[0]])))
										{
											if(isset($arrResultConsulta[$filaParam[0]]))
											{
												if($arrResultConsulta[$filaParam[0]]["ejecutado"]==1)
													$valor=$arrResultConsulta[$filaParam[0]]["resultado"];	
												else
													$valor='-100584';
											}
											else
												$valor='-100584';
										}
										else
										{
											$esperaResolver=true;
											$nEsperaResolver++;
										}
									break;
									case "8":	//valor de almacen padre
										$valor=$filaParam[0]."|@|".$filaParam[2];  
									break;
									case "9":	//Valor de pàgina
										eval('$valor=$paramDatos->p9->p'.$filaParam[0].';');
									break;
									case "11": //Consulta de almacen
										$clave=$filaParam[0];
										if(($filaParam[0]<$filaCon[5])||(isset($arrResultConsulta[$filaParam[0]])))
										{
											$arrDatos=explode($clave,"|");
											if(isset($arrResultConsulta[$filaParam[0]]))
											{
												if($arrResultConsulta[$filaParam[0]]["ejecutado"]==1)
												{
													$resultado=$arrResultConsulta[$arrDatos[0]]["resultado"];
													
													$conAux=$arrResultConsulta[$arrDatos[0]]["conector"];
													$conAux->inicializarRecurso($resultado);
													$filaRes=$conAux->obtenerSiguienteFilaAsoc($resultado);
													if(!$filaRes)
														$valor="100584";
													else
													{
														$valor=$filaRes[$arrDatos[1]];
													}
												}
												else
													$valor='-100584';
											}
											else
												$valor='';
										}
										else
										{
											$esperaResolver=true;
											$nEsperaResolver++;
										}
									break;
									case "16": //Valores de proceso
										
										eval('
												if(isset($paramDatos->p16->p'.$filaParam[0].'))
													$valor=$paramDatos->p16->p'.$filaParam[0].';
												else
													$valor=$paramDatos->p16->'.$filaParam[0].';
											');
										
									break;
									case "17": //Parametros varios
										
										eval('$valor=$paramDatos->p17->'.$filaParam[0].';');
									break;
									case "18": //Parametros varios
										
										eval('$valor=isset($paramDatos->'.$filaParam[0].')?$paramDatos->'.$filaParam[0].':-1;');
									break;
									case "23": //Parametros control
										$valor='@'.$filaParam[0];
										if($arrParamControl=='')
											$arrParamControl=$filaParam[0];
										else
											$arrParamControl.=",".$filaParam[0];
											
									break;
									case "24": //Parametros control V2
										$valor="@Control_".$filaParam[0];
									break;
									
								}
								$arrValorParamAlmacen[substr($param,1)]="".$valor."";
							}
						}
					}
					
					
					
					$queryOp="";
					$compAux="";
					$campoDistinto="";
					
					if($filaCon[9]!="")
					{
						$obj=json_decode($filaCon[9]);
						if($obj->distinto==1)
						{
							if($obj->campoDistinto!="")
								$campoDistinto=$obj->campoDistinto;
							$compAux="distinct ";
						}
					}
					
					if($filaCon[11]==1)
						$compAux="distinct ";
					
	
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
								$nCampo=generarCampoFormularioThot($campo,$arrCamposFrmTablas,$arrElementosTabla,$arrConexiones[$filaCon[10]]);
								
								if($pNombreCampoCompleto)
								{
									$arrCampoTmp=explode(" as ",$nCampo);
									if(sizeof($arrCampoTmp)==1)
										$nCampo=$nCampo." as ".str_replace(".","_",$nCampo);
									else
										$nCampo=$arrCampoTmp[0]." as ".str_replace(".","_",$arrCampoTmp[1]);
								}
								if($listCamposProy=="")	
									$listCamposProy=$nCampo;
								else
									$listCamposProy.=",".$nCampo;	
							}
							$queryOp="select ".$compAux.$listCamposProy;
							
							
							
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
							if($campoDistinto!="")
								$camposProy="distinct (".$campoDistinto.")";
							$queryOp="select count(".$camposProy.") as nRegistros";
						break;
						case 7:
							$queryOp="select sqrt(".$camposProy.")";
						break;		
					}
					
					$queryOp.=" from ".$listTabla;
					$objNodos=json_decode($nodosFiltro);
					$ejecutado=1;
					
					$condWhere="";
					if($modoDebugger)
					{
						echo "<span class='letraRojaSubrayada8'>".$filaCon[7]."</span><br><br><span class='letraAzulSubrayada7'>Consulta: ".$filaCon[5]." Nodos WHERE</br></span>";
						varDump($objNodos);
					}
	
					
				
	
					if(sizeof($objNodos)>0)
					{
						foreach($objNodos as $nodo)
						{
							/*if($filaCon[5]==734)
							{
								varDump($arrValorParamAlmacen);
								echo "<br>";
								varDump($nodo);
							}*/
							$codMysql=$nodo->tokenMysql;
							$tokenTipo=$nodo->tokenTipo;
							$dToken=explode("|",$tokenTipo);
							if(($dToken[0]==17)&&($tipoDataSet==1))
								$dToken[0]=2;
							switch($dToken[0])
							{
								case 0:
								case 1:
								break;
								case 2:  //Parametro de reporte
									if(!$vRegistros)
									{
										$consulta="select parametro from 9015_parametrosReporte where idParametro=".$dToken[1];

										$param=$conexionBD->obtenerValor($consulta);
										eval('	if(isset($paramDatos->p2->p'.$param.'))
													$valParam=$paramDatos->p2->p'.$param.';
												else
													$valParam=$paramDatos->p2->'.$param.';
											');
										if(strpos($codMysql,' like ')===false)
											$codMysql=str_replace("@","'".$valParam."'",$codMysql);
										else
											$codMysql=str_replace("@",$valParam,$codMysql);
									}
									else
										$codMysql="1=1";

								break;
								case 3:	//Valor de sesion
								
															
									$consulta="select valorSesion,valorReemplazo from 8003_valoresSesion where idValorSesion=".$dToken[1];
									
									$filaSesion=$conexionBD->obtenerPrimeraFila($consulta);
									if(!$vRegistros)
									{
										
										$valor=$_SESSION[$filaSesion[0]];
										
										
										$iFormulario=-1;
										$iRegistro=-1;
										

										if(isset($paramDatos->paramAmbiente->idFormulario)||isset($paramDatos->idFormulario)||isset($paramDatos->p16))
										{
											
											if(isset($paramDatos->paramAmbiente->idFormulario))
												$iFormulario=$paramDatos->paramAmbiente->idFormulario;
											else
												if(isset($paramDatos->idFormulario))
												{
													$iFormulario=$paramDatos->idFormulario;
												}
												else
													$iFormulario=$paramDatos->p16->p1;
											
											
										}
										
										
										
										if(isset($paramDatos->paramAmbiente->idRegistro)||isset($paramDatos->idRegistro)||isset($paramDatos->p16))
										{
											
											if(isset($paramDatos->paramAmbiente->idRegistro))
												$iRegistro=$paramDatos->paramAmbiente->idRegistro;
											else
												if(isset($paramDatos->idRegistro))
												{
													$iRegistro=$paramDatos->idRegistro;
												}
												else
													$iRegistro=$paramDatos->p16->p3;
											
											
										}
										
										
										if(($iFormulario!=-1)&&($iRegistro!=-1))
										{
											$consulta="SELECT responsable AS idUsr,codigoUnidad,codigoInstitucion FROM _".$iFormulario.
													"_tablaDinamica WHERE id__".$iFormulario."_tablaDinamica=".$iRegistro;
											
											$fRegistroTemp=$con->obtenerPrimeraFilaAsoc($consulta);
											
											if(isset($fRegistroTemp[$filaSesion[0]]))
											{
												$valor=$fRegistroTemp[$filaSesion[0]];
											}
											
										}
										
										if($valor=="")
												$valor="-1";
										if(strpos($codMysql,' like ')===false)
											$codMysql=str_replace('@'.$filaSesion[1],"'".$valor."'",$codMysql);
										else
											$codMysql=str_replace('@'.$filaSesion[1],$valor,$codMysql);
									}
									else
									{
										if($filaCon[10]==0)
										{
											switch($dToken[1])
											{
												case 1://ID de usuario autenticado
												case 2://Departamento de usuario autenticado
												case 3://Institución de usuario autenticado
													$valor="";
													if($dToken[1]==1)
														$valor="responsable";
													else
														if($dToken[1]==2)
															$valor="codigoUnidad";
														else
															$valor="codigoInstitucion";
													if(strpos($codMysql,' like ')===false)
													{
														$codMysql=str_replace('@'.$filaSesion[1],$valor,$codMysql);
														$codMysql=str_replace("'".$valor."'",$valor,$codMysql);
													}
													else
													{
														$arrTokenTmp=explode(" like ",$codMysql);
														$tokenFinal=$arrTokenTmp[1];
														$arrTokens=explode('@'.$filaSesion[1],$codMysql);
														$codMysql=$arrTokenTmp[0]." like concat(";
														if($arrTokens[0]=="'%")
															$codMysql.="'%',".$valor;
														else
															$codMysql.=$valor;
															
														if($arrTokens[1]=="%'")	
															$codMysql.=",'%'";
														$codMysql.=")";
													}
												break;
												default:
													$codMysql="1=1";
												break;
											}
										}
										else
											$codMysql="1=1";
									}
								break;
								case 4:	//Valor de sistema
									$consulta="select valorSesion,valorReemplazo from 8003_valoresSesion where idValorSesion=".$dToken[1];
									$filaSesion=$conexionBD->obtenerPrimeraFila($consulta);
									if(!$vRegistros)
									{
										
										$valorSistema="";
										switch($dToken[1])
										{
											case "8":
												$valorSistema="'".date("Y-m-d")."'";
											break;
											case "9":
												$valorSistema="'".date("H:i:s")."'";
											break;
											case "10":
												$valorSistema="'".date("Y-m-d H:i:s")."'";
											break;
										}
										if(strpos($codMysql,' like ')===false)
											$codMysql=str_replace('@'.$filaSesion[1],$valorSistema,$codMysql);
										else
											$codMysql=str_replace('@'.$filaSesion[1],str_replace("'","",$valorSistema),$codMysql);
									}
									else
									{
										if($filaCon[10]==0)
										{
											switch($dToken[1])
											{
												case 8://Fecha del sistema
													$valor='DATE_FORMAT(fechaCreacion,"%Y-%m-%d")';
													
													if(strpos($codMysql,' like ')===false)
													{
														$codMysql=str_replace('@'.$filaSesion[1],$valor,$codMysql);
														$codMysql=str_replace("'".$valor."'",$valor,$codMysql);
														
													}
													else
													{
														$arrTokenTmp=explode(" like ",$codMysql);
														$tokenFinal=$arrTokenTmp[1];
														$arrTokens=explode('@'.$filaSesion[1],$codMysql);
														$codMysql=$arrTokenTmp[0]." like concat(";
														if($arrTokens[0]=="'%")
															$codMysql.="'%',".$valor;
														else
															$codMysql.=$valor;
															
														if($arrTokens[1]=="%'")	
															$codMysql.=",'%'";
														$codMysql.=")";
													}
												break;
												default:
													$codMysql="1=1";
												break;
											}
										}
										else
											$codMysql="1=1";
									}
								break;
								case 5:
								case 6:	//Valor de parametro
									$nParam=substr($dToken[1],1);
									
									if(!$vRegistros)
									{
										
										if(strpos($arrValorParamAlmacen[$nParam],"|@|")===false)
										{
												
											if($arrValorParamAlmacen[$nParam]!='-100584')
											{
												if(strpos($arrValorParamAlmacen[$nParam],"@Control")!==false)
												{
													
													
													$arrControl=explode("_",$arrValorParamAlmacen[$nParam]);
													$queryTmp="SELECT nombreCampo FROM 901_elementosFormulario WHERE idGrupoElemento=".$arrControl[1];
													$conAux=$arrConexiones[$filaCon[10]];
													
													$nControl=$con->obtenerValor($queryTmp);
													
													$ejecutado=0;
													if($nControl!="")
													{
														if($arrParamControl=="")
															$arrParamControl=$nControl;
														else
															$arrParamControl.=",".$nControl;
													}
												}
												/*if($filaCon[5]==734)	
												{
													varDUmp($codMysql);

												}*/
												if(strpos($codMysql," like ")!==false)
												{
													$codMysql=str_replace('@'.$nParam,$arrValorParamAlmacen[$nParam],$codMysql);
													
												}
												else
												{
													$codMysql=str_replace('@'.$nParam,"'".$arrValorParamAlmacen[$nParam]."'",$codMysql);
												}
												
											}
											else
											{
												if(strpos($codMysql," like ")!==false)
													$codMysql=str_replace('@'.$nParam,"".'@'.$nParam."",$codMysql);
												else
													$codMysql=str_replace('@'.$nParam,"'".'@'.$nParam."'",$codMysql);
												
												$ejecutado=0;
											}
										}
										else
										{
											$arrDatos=explode("|@|",$arrValorParamAlmacen[$nParam]);	
											$campoRef=$arrDatos[0];
											$ejecutado=0;
											if(!existeValor($arrParamPendientes,$campoRef."|".$arrDatos[1]))
												array_push($arrParamPendientes,$campoRef."|".$arrDatos[1]);
										}
									}
									else
									{
										if($filaCon[10]==0)
										{
											if(strpos($arrValorParamAlmacen[$nParam],"|@|")===false)
											{
												if($arrValorParamAlmacen[$nParam]!='-100584')
												{
													$reemplazarComillas=false;
													$valor=$arrValorParamAlmacen[$nParam];
													if(strpos($arrValorParamAlmacen[$nParam],"@Control")!==false)
													{
														
														$arrControl=explode("_",$arrValorParamAlmacen[$nParam]);
														$queryTmp="SELECT concat('_',idFormulario,'_tablaDinamica.',nombreCampo) FROM 901_elementosFormulario WHERE idGrupoElemento=".$arrControl[1];
														$conAux=$arrConexiones[$filaCon[10]];
														$reemplazarComillas=true;
														$valor=$con->obtenerValor($queryTmp);	
													}
													
													if(strpos($codMysql,' like ')===false)
													{
														$codMysql=str_replace('@'.$nParam,$valor,$codMysql);
														if($reemplazarComillas)
															$codMysql=str_replace("'".$valor."'",$valor,$codMysql);
													}
													else
													{
														$arrTokenTmp=explode(" like ",$codMysql);
														$tokenFinal=$arrTokenTmp[1];
														$arrTokens=explode('@'.$nParam,$codMysql);
														$codMysql=$arrTokenTmp[0]." like concat(";
														if($arrTokens[0]=="'%")
															$codMysql.="'%',".$valor;
														else
															$codMysql.=$valor;
															
														if($arrTokens[1]=="%'")	
															$codMysql.=",'%'";
														$codMysql.=")";
													}
												}
												else
												{
													$codMysql=str_replace('@'.$nParam,"'".'@'.$nParam."'",$codMysql);
													$ejecutado=0;
												}
											}
											else
												$codMysql="1=1";
										}
										else
											$codMysql="1=1";
									}
								break;
								case 7:	//Valor de consulta auxilizar
									if(!$vRegistros)
									{	
										
										$valorQuery="";
										if(($dToken[1]<$filaCon[5])	||(isset($arrResultConsulta[$dToken[1]])))
										{
											if(isset($arrResultConsulta[$dToken[1]]))
											{
												if($arrResultConsulta[$dToken[1]]["ejecutado"]==1)
													$valorQuery=$arrResultConsulta[$dToken[1]]["resultado"];
												else
												{
													$valorQuery="-100584";
													$ejecutado=0;
												}
												$codMysql=str_replace("@","'".$valorQuery."'",$codMysql);
											}
											else
											{
												$esperaResolver=true;
												$nEsperaResolver++;
											}
											
											
										}
										else
										{
											$esperaResolver=true;
											$nEsperaResolver++;
											
										}
										
										
										
										
									}
									else
										$codMysql="1=1";
								break;
								case 9:  //Valor de pagina
									if(!$vRegistros)
									{
										eval('
												if(isset($paramDatos->p9->p'.$dToken[1].'))
													$valor=$paramDatos->p9->p'.$dToken[1].';
												else
													$valor=$paramDatos->p9->'.$dToken[1].';
											');
											if(strpos($codMysql,' like ')===false)
												$codMysql=str_replace("@","'".$valor."'",$codMysql);
											else
												$codMysql=str_replace("@",$valor,$codMysql);
									}
									else
										$codMysql="1=1";
								break;
								case 11: //Valor de almacen
									if(!$vRegistros)
									{
										$valor="";
										
										if(($dToken[1]<$filaCon[5])	||(isset($arrResultConsulta[$dToken[1]])))
										{
											if(isset($arrResultConsulta[$dToken[1]]))
											{
												if($arrResultConsulta[$dToken[1]]["ejecutado"]==1)
												{
													$resultado=$arrResultConsulta[$dToken[1]]["resultado"];
													$conAux=$arrResultConsulta[$dToken[1]]["conector"];
													
													$conAux->inicializarRecurso($resultado);
													$filaRes=$conAux->obtenerSiguienteFilaAsoc($resultado);
													
													if(!$filaRes)
														$valor="-100584";
													else
													{
														$cNormalizado=str_replace(".","_",$dToken[2]);
														$valor=$filaRes[$cNormalizado];
													}
												}
												else
												{
													$valor="-100584";
													$ejecutado=0;
												}
												$codMysql=str_replace("@","'".$valor."'",$codMysql);	
											}
											else
											{
												$esperaResolver=true;
												$nEsperaResolver++;
											}
											
										}
										else
										{
											$esperaResolver=true;
											$nEsperaResolver++;
										}
									}
									else
										$codMysql="1=1";
								break;
								case 16:  //Valor de proceso
									if((!$vRegistros)||($dToken[1]==1)||($dToken[1]==2))
									{
										$valor="";
										eval('if(isset($paramDatos->p16->p'.$dToken[1].')){$valor=$paramDatos->p16->p'.$dToken[1].';}');
										if(strpos($codMysql,' like ')===false)
											$codMysql=str_replace("@","'".$valor."'",$codMysql);
										else
											$codMysql=str_replace("@",$valor,$codMysql);
									}
									else
									{
										if($filaCon[10]==0)
										{
											switch($dToken[1])
											{
												case 3://ID Registro
												case 5://ID Referencia
												case 6://ID Responsable
													$valor="";
													$nombreTablaBase="_".$paramDatos->p16->p1."_tablaDinamica";
													if($dToken[1]==3)
														$valor="id_".$nombreTablaBase;
													else
														if($dToken[1]==5)
															$valor=$nombreTablaBase.".idReferencia";
														else
															$valor=$nombreTablaBase.".responsable";
													if(strpos($codMysql,' like ')===false)
													{
														$codMysql=str_replace('@',$valor,$codMysql);
														$codMysql=str_replace("'".$valor."'",$valor,$codMysql);
													}
													else
													{
														$arrTokenTmp=explode(" like ",$codMysql);
														$tokenFinal=$arrTokenTmp[1];
														$arrTokens=explode('@',$codMysql);
														$codMysql=$arrTokenTmp[0]." like concat(";
														if($arrTokens[0]=="'%")
															$codMysql.="'%',".$valor;
														else
															$codMysql.=$valor;
															
														if($arrTokens[1]=="%'")	
															$codMysql.=",'%'";
														$codMysql.=")";
													}
												break;
												default:
													$codMysql="1=1";
												break;
											}
										}
										else
											$codMysql="1=1";
									}
								break;
								case 17:  //Valor vario
									if(!$vRegistros)
									{	
										$reemplazar=false;
										$valor="";
										if(isset($paramDatos->p17))
										{
											eval ('
												
													if(isset($paramDatos->p17->p'.$dToken[1].')) 
														$valor=$paramDatos->p17->p'.$dToken[1].';
													else
														$valor=$paramDatos->p17->'.$dToken[1].';
													
												');
										}
												
										$reemplazar=true;
										if($reemplazar)
										{
											if(strpos($codMysql,' like ')===false)
												$codMysql=str_replace("@","'".$valor."'",$codMysql);
											else
												$codMysql=str_replace("@",$valor,$codMysql);
										}
									}
									else
										$codMysql="1=1";
								break;
								
								case "18": //Parametros varios
										if(!$vRegistros)
										{	
											
											$valor="";
											
											eval('$valor=isset($paramDatos->'.$dToken[1].')?$paramDatos->'.$dToken[1].':-1;');
											
											
													
											$reemplazar=true;
											if($reemplazar)
											{
												if(strpos($codMysql,' like ')===false)
													$codMysql=str_replace("@","'".$valor."'",$codMysql);
												else
													$codMysql=str_replace("@",$valor,$codMysql);
											}
										}
										else
											$codMysql="1=1";
										
								break;
								
								case 20:
									$consulta="SELECT nombreCampo FROM 901_elementosFormulario WHERE idGrupoElemento=".$dToken[1];
									$nCampoFormulario=$con->obtenerValor($consulta);
									if($nCampo!="")
									{
										if(!$vRegistros)
										{		

											if($paramDatos->p16->p3==-1)
											{
												$resVal=false;
												
												eval('$resVal=isset($paramDatos->paramAmbiente->'.$nCampoFormulario.');');
												if($resVal)
													eval('$valor=$paramDatos->paramAmbiente->'.$nCampoFormulario.';');
												else
													$valor=-1;
											}
											else
											{
												$consulta="select ".$nCampoFormulario." from _".$paramDatos->p16->p1."_tablaDinamica where id__".$paramDatos->p16->p1."_tablaDinamica=".$paramDatos->p16->p3;
												$valor=$con->obtenerValor($consulta);
											}
											$codMysql=str_replace("@","'".$valor."'",$codMysql);
										}
										else
										{
											$valor=$nCampoFormulario;
											array_push($arrResultConsulta[$filaCon[5]]["camposFormularioReferencia"],$nCampoFormulario);
											$codMysql=str_replace("@","'[_".$valor."_]'",$codMysql);
										}
										
									}
									else
										$codMysql="1=-1";
								break;
								case 23:
									if(!$vRegistros)
									{
										$valor='@'.$filaParam[0];
										if($arrParamControl=='')
											$arrParamControl=$filaParam[0];
										else
											$arrParamControl.=",".$filaParam[0];
									}
									else
										$codMysql="1=1";
								break;
								case 31:
									$cadObj='{"param1":""}';
									$objParamCalculo=json_decode($cadObj);
									if(isset($paramDatos->parametrosAmbiente))
										$objParamCalculo->param1=$paramDatos->parametrosAmbiente;
									$arrCache=NULL;
									$valor=resolverExpresionCalculoPHP($dToken[1],$objParamCalculo,$arrCache);
									if($valor=="''")
										$valor=-1;
									$codMysql=str_replace("@","".$valor."",$codMysql);
								break;
								
								
							}
							$condWhere.=" ".str_replace("'%'","%'",$codMysql);
						}
						
						$condWhere=normalizarCondicionWhere($condWhere,$arrCamposFrmTablas);
						$queryOp.=' where '.$condWhere;
						
					}
					
					$queryOp=str_replace("()","(-1)",$queryOp);
					$queryOp=str_replace("''","'",$queryOp);
					
					if(($filaCon[8]!="")&&(!$vRegistros))
					{
						
						$objOrden=json_decode($filaCon[8]);
						$cadOrden="";
						foreach($objOrden as $orden)	
						{
							$campo=$orden->campo;
							$arrCampo=explode(".",$orden->campo);
							if(isset($arrCamposFrmTablas[$arrCampo[1]]))
								$campo=$arrCampo[0].".".$arrCamposFrmTablas[$arrCampo[1]];
							$objOrden=$campo." ".$arrOrden[$orden->orden];
							if($cadOrden=='')
								$cadOrden=$objOrden;
							else
								$cadOrden.=",".$objOrden;
						}
						if($cadOrden!="")
							$queryOp.=" order by ".$cadOrden;
						
					}
					
					if(($numRegistros!="")&&($numRegistros!=0))
						$queryOp.=" limit 0,".$numRegistros;
					
					/*if($filaCon[5]==734)
						echo $queryOp;*/
					
					if(!$esperaResolver)
					{
						
						$arrResultConsulta[$filaCon[5]]["tipoAlmacen"]=$filaCon[6];
						$arrResultConsulta[$filaCon[5]]["query"]=$queryOp;
						$arrResultConsulta[$filaCon[5]]["idConexion"]=$filaCon[10];
						$arrResultConsulta[$filaCon[5]]["conector"]=$arrConexiones[$filaCon[10]];
						$arrResultConsulta[$filaCon[5]]["filasAfectadas"]=0;
						if(($ejecutado==1)&&((!$vRegistros)||($ejecutarConsultas)))
						{
							if($modoDebugger)
							{
								echo "<br><span class='letraAzulSubrayada7'>Consulta construida:</br></span>";
								echo "<span class='copyrigthSinPadding'>".$queryOp."</span><br><br><br><br>";
							}
							$conAux=$arrConexiones[$filaCon[10]];
							if($filaCon[6]==1)	
							{	
								$resQuery=$conAux->obtenerListaValores($queryOp,"'");	
								
								if($resQuery=="")
									$resQuery="-1";//-100584
								$arrResultConsulta[$filaCon[5]]["resultado"]=$resQuery;
							}
							else
								$arrResultConsulta[$filaCon[5]]["resultado"]=$conAux->obtenerFilas($queryOp);
							$arrResultConsulta[$filaCon[5]]["filasAfectadas"]=$conAux->filasAfectadas;
						}
						
						$arrResultConsulta[$filaCon[5]]["ejecutado"]=$ejecutado;
						$arrResultConsulta[$filaCon[5]]["paramPendientes"]=$arrParamPendientes;
						$arrResultConsulta[$filaCon[5]]["arrParamControl"]=$arrParamControl;
						$arrResultConsulta[$filaCon[5]]["nomConsulta"]=$filaCon[7];
					}
				}
			}
			
			
			
			
			if($nEsperaResolver==0)
				$finalizarConsulta=true;
			else
			{
				mysql_data_seek($resConAux,0);
				$nCiclos++;
				if($nCiclos>$nIntentosResolucion)
					$finalizarConsulta=true;
			}
	
		}
		$arrResultConsulta[-1000]=$arrConexiones;

		return $arrResultConsulta;
	}
	
	function validarConsultaAlmacen($idAlmacen,$con)
	{

		$consulta="select * from 9014_almacenesDatos where idDataSet=".$idAlmacen;
		$filaAlmacen=$con->obtenerPrimeraFila($consulta);
		$nTabla=$filaAlmacen[9];
		$cadObjNodos='{"nodos":'.$filaAlmacen[7].'}';
		$objTemp=json_decode($cadObjNodos);
		$obj=$objTemp->nodos;
		$arrTablas=explode(",",$nTabla);
		$arrDiagnostico=array();
		foreach($obj as $nodo)
		{
			if($nodo->tokenTipo!='0|0')
			{
				$tokenMysql=$nodo->tokenMysql;
				$arrTokens=explode(" ",$tokenMysql);
				$nTokens=sizeof($arrTokens);		
				for($x=0;$x<$nTokens;$x++)
				{
					$tok=$arrTokens[$x];
					if(strpos($tok,"_")!==false)
					{
						$arrTok=explode(".",$tok);
						if(sizeof($arrTok)>1)
						{
							$nTablaInv=$arrTok[0];
							if(!existeValor($arrTablas,$nTablaInv))
							{
								$datosTablaInv=explode("_",$nTablaInv);
								$nTablaRef="";
								if(strpos($nTablaInv,"tablaDinamica")!==false)
									$nTablaRef=$datosTablaInv[2];
								else
									$nTablaRef=$datosTablaInv[1];
								$cad="La part&iacute;cula <b><I>".$nodo->tokenUsuario."</I></b> hace referencia a la tabla ".$nTablaRef." la cual fu&eacute; removida como tabla referenciada por el almac&eacute;n";
								array_push($arrDiagnostico,$cad);
								
								
							}	
							
						}
					}		
				}
			}
		}
		return $arrDiagnostico;
	}
	
	function normalizarCondicionWhere($condicion,$arrCamposControlTablas)
	{
		$arrCond=explode(" ",$condicion);
		$cadenaFinal="";
		foreach($arrCond as $token)
		{
			if(strpos($token,"tablaDinamica")===false)
				$tokenFinal=$token;
			else
			{
				$arrDatos=explode(".",$token);

				if(isset($arrDatos[1]))
				{
					if(isset($arrCamposControlTablas[$arrDatos[1]]))
						$tokenFinal=$arrDatos[0].".".$arrCamposControlTablas[$arrDatos[1]];
					else
						$tokenFinal=$token;
				}
				else
					$tokenFinal=$token;
			}
			$cadenaFinal.=" ".$tokenFinal;
		}
		return $cadenaFinal;
	}
	
	function resolverQueriesThot($objParametros)
	{
		global $con;
		$idReporte=$objParametros->r;
		$consulta="SELECT parametro FROM 9015_parametrosReporte WHERE idReporte=".$idReporte;
		$resParam=$con->obtenerFilas($consulta);
		$arrValorParamReporte="";
		$paramAmbiente='"idReporte":"'.$idReporte.'"';
		while($filaParam=mysql_fetch_row($resParam))
		{
			$vParam="";
			$exp='if(isset($objParametros->'.$filaParam[0].'))
					$vParam=$objParametros->'.$filaParam[0].';';
			eval($exp);
			
			$paramAmbiente.=',"'.$filaParam[0].'":"'.$vParam.'"';

			
			if($arrValorParamReporte=="")
				$arrValorParamReporte='"p'.$filaParam[0].'":"'.$vParam.'"';
			else
				$arrValorParamReporte.=',"p'.$filaParam[0].'":"'.$vParam.'"';
				
		}
		$cadObj='{"p2":{'.$arrValorParamReporte.'},"parametrosAmbiente":{'.$paramAmbiente.'}}';
		$paramObj=json_decode($cadObj);
		$ojParametrosReporte=$paramObj->p2;
		$parametrosAmbiente=$paramObj->parametrosAmbiente;
		$arrResultConsulta=resolverQueries($idReporte,1,$paramObj,true);
		
		$consulta="select nodosFiltro,camposProy,operacion,nTabla,parametros,idDataSet,tipoAlmacen,nombreDataSet,orden,complementario,idConexion,obtenerValoresDistintos,numRegistros from 
				9014_almacenesDatos where idReporte=".$idReporte." and tipoDataSet=1 and tipoAlmacen=2 order by idDataSet asc";

		$resConAux=$con->obtenerFilas($consulta);	
		while($fila=mysql_fetch_row($resConAux))
		{
			$idAlmacen=$fila[5];
			$arrResultConsulta[$fila[5]]["tipoAlmacen"]=$fila[6];
			$arrResultConsulta[$fila[5]]["idConexion"]="0";
			$arrResultConsulta[$fila[5]]["resultado"]=array();
			
			$consulta="SELECT nombreCategoria,idCategoriaAlmacenGrafico,color,tipoCategorias,comp1,valor FROM 9014_categoriasAlmacenesGraficos WHERE idAlmacen=".$idAlmacen." order by idCategoriaAlmacenGrafico";
			$resCat=$con->obtenerFilas($consulta);
			$arrCategorias=array();
			while($fCat=mysql_fetch_row($resCat))
			{
				$tipoOrigenCategorias=$fCat[3];
				
				switch($tipoOrigenCategorias)
				{
					case 0:
					case 1:
						$obj=array();
						$obj[0]=$fCat[5];
						$obj[1]=$fCat[0];
						$obj[2]=normalizarValorRGB($fCat[2]);
						$obj[3]=array();
						$obj[4]=$fCat[1];
						$arrValores=explode(",",$obj[0]);
						
						foreach($arrValores as $v)
						{
							$v=substr($v,1);
							$v=substr($v,0,strlen($v)-1);
							if($v!="")
								array_push($obj[3],$v);
						}
						array_push($arrCategorias,$obj);
					break;
					case 2:
						$cadObj='{"idReporte":"'.$idReporte.'","params":'.json_encode($ojParametrosReporte).'}';
						$objParam=json_decode($cadObj);
						$cache=NULL;
						$arrValores=resolverExpresionCalculoPHP($fCat[4],$objParam,$cache);
						foreach($arrValores as $v)
						{
							$obj=array();
							$obj[0]=$v["id"];
							$obj[1]=$v["etiqueta"];
							$obj[2]=normalizarValorRGB($v["color"]);
							$obj[3]=array();
							$obj[4]=$fCat[1];
							$arrValores=explode(",",$obj[0]);
							foreach($arrValores as $v)
							{
								if(strpos($v,"'")===0)
								{
									$v=substr($v,1);
									$v=substr(0,strlen($v)-2);
								}
								if($v!="")
									array_push($obj[3],$v);
							}
							array_push($arrCategorias,$obj);
						}
					break;
				}
			}
							
					
			$consulta="SELECT titulo,idSerieAlmacenGrafico,color,tipoSerie,comp1,valor FROM 9014_seriesAlmacenesGraficos WHERE idAlmacen=".$idAlmacen." order by idSerieAlmacenGrafico";
			$resSerie=$con->obtenerFilas($consulta);
			
			$arrSeries=array();
			$arrMatrizValoresAux=array();
			while($fSerie=mysql_fetch_row($resSerie))
			{
				$tipoOrigenSeries=$fSerie[3];
				
				switch($tipoOrigenSeries)
				{
					case 0:
					case 1:
						$obj=array();
						$obj[0]=$fSerie[5];
						$obj[1]=$fSerie[0];
						$obj[2]=normalizarValorRGB($fSerie[2]);
						$obj[3]=array();
						$obj[4]=$fSerie[1];
						$arrValores=explode(",",$obj[0]);
						
						foreach($arrValores as $v)
						{
							$v=substr($v,1);
							$v=substr($v,0,strlen($v)-1);
							if($v!="")
								array_push($obj[3],$v);
						}
						array_push($arrSeries,$obj);
					break;
					case 2:
						$cadObj='{"idReporte":"'.$idReporte.'","params":'.json_encode($ojParametrosReporte).'}';
						$objParam=json_decode($cadObj);
						$arrValores=resolverExpresionCalculoPHP($fSerie[4],$objParam);
						foreach($arrValores as $v)
						{
							$obj=array();
							$obj[0]=$v["id"];
							$obj[1]=$v["etiqueta"];
							$obj[2]=normalizarValorRGB($v["color"]);
							$obj[3]=array();
							$obj[4]=$fSerie[1];
							$arrValores=explode(",",$obj[0]);
							foreach($arrValores as $v)
							{
								if(strpos($v,"'")===0)
								{
									$v=substr($v,1);
									$v=substr(0,strlen($v)-2);
								}
								if($v!="")
									array_push($obj[3],$v);
							}
							array_push($arrSeries,$obj);
						}
					break;
				}
			}
					
			$generaSerie=false;
			if(sizeof($arrSeries)==0)
			{
				if($tipoOrigenCategorias==1)
				{
					$generaSerie=true;
					
					$objSerie=array();
					$objSerie[0]="";
					$objSerie[1]="Serie 1";
					$objSerie[2]="";
					$objSerie[3]=array();
					$objSerie[4]="";
					
					array_push($arrSeries,$objSerie);
					
					$consulta="select complementario from 9014_almacenesDatos where idDataSet=".$idAlmacen;
					
					$complementario=$con->obtenerValor($consulta);	

					$objComp=json_decode($complementario);
					$idConexion=$objComp->idConexion;
					
					

					$consulta="select ".$objComp->campo." from ".$objComp->tablaOrigen;
					$listRegistros="";
					if(isset($objComp->condicionFiltro)&&($objComp->condicionFiltro!=""))
					{
						$objFiltro=json_decode($objComp->condicionFiltro);
						switch($objFiltro->tipoValor)
						{
							case 1:
								$listRegistros=$objFiltro->valor;
							break;
							case 7:  //Consulta auxiliar
								if(isset($arrResultConsulta[$objFiltro->idAlmacen]))
								{
									if($arrResultConsulta[$objFiltro->idAlmacen]["ejecutado"]==1)
										$listRegistros=str_replace("'","",$arrResultConsulta[$objFiltro->idAlmacen]["resultado"]);
								}
							break;
							case 11:  //Almacén de datos
								if(isset($arrResultConsulta[$objFiltro->idAlmacen]))
								{
									if($arrResultConsulta[$objFiltro->idAlmacen]["ejecutado"]==1)
									{
										$conAux=$arrResultConsulta[$objFiltro->idAlmacen]["conector"];
										$resTmp=$arrResultConsulta[$objFiltro->idAlmacen]["resultado"];
										$conAux->inicializarRecurso($resTmp);
										$fDatos=$conAux->obtenerSiguienteFilaAsoc($resTmp);
										$cNomalizado=str_replace(".","_",$objFiltro->campoMysql);
										if(isset($fDatos[$cNomalizado]))
											$listRegistros=$fDatos[$cNomalizado];
									}
								}
							break;
							case 22:  //Invocacion de funcion
								$objParametrosValorSerie=array();
								$reflectionClase = new ReflectionObject($ojParametrosReporte);
								foreach ($reflectionClase->getProperties() as $property => $value) 
								{
									$objParametrosValorSerie[$value->getName()]=$value->getValue($ojParametrosReporte);	
								}
								$cadObj='{"idReporte":"'.$idReporte.'","params":'.json_encode($objParametrosValorSerie).'}';
								$objParam=json_decode($cadObj);
								$cache=NULL;
								$valorTmp=resolverExpresionCalculoPHP($objFiltro->funcion,$objParam,$cache);
								$listRegistros=str_replace("'","",$valorTmp);
							break;
						}
					}
					$conAux=$con;
					if($idConexion!=0)
						$conAux=generarInstanciaConector($idConexion);
					if(!$conAux->conexion)	
						continue;
					if($listRegistros!="")
					{
						$campoLlave=$conAux->obtenerCampoLlave($objComp->tablaOrigen);
						$consulta.=" where ".$campoLlave." in (".$listRegistros.")";
					}
					$resRegistros=NULL;
					if($conAux->existeCampo($objComp->campo,$objComp->tablaOrigen))
					{
						$resRegistros=$conAux->obtenerFilas($consulta);
					}
					else
					{
						if((strpos($objComp->tablaOrigen,"tablaDinamica")!==false)&&($conAux->esSistemaLatis()))
						{
							$queryAux="SELECT tipoElemento FROM 901_elementosFormulario WHERE nombreCampo='".$objComp->campo."'";
							$tipoCampo=$conAux->obtenerValor($queryAux);
							switch($tipoCampo)
							{
								case 17:
								case 18:
								case 19:
									$arrTablaDinamica=explode("_",$objComp->tablaOrigen);
									$nTablaDinamica="_".$arrTablaDinamica[1]."_".$objComp->campo;
									if($conAux->existeTabla($nTablaDinamica))
									{
										$consulta="select idOpcion FROM ".$nTablaDinamica;
										if($listRegistros!="")
											$consulta.=" WHERE idPadre IN(".$listRegistros.")";
										$resRegistros=$conAux->obtenerFilas($consulta);
									}
								break;
							}
							
						}
					}
					
					
					while($fCont=$conAux->obtenerSiguienteFila($resRegistros))
					{
						foreach($arrCategorias as $c)
						{
							if(existeValor($c[3],$fCont[0]))
							{
								if(isset($arrMatrizValoresAux[$c[4]]))
									$arrMatrizValoresAux[$c[4]]++;
								else
									$arrMatrizValoresAux[$c[4]]=1;
								break;
							}
						}
					}
					
					foreach($arrCategorias as $c)
					{
						if(!isset($arrMatrizValoresAux[$c[4]]))
						{
							$arrMatrizValoresAux[$c[4]]=0;
						}
					}
					
					
				}

			}
			
			
			
			
			$valor=0;
			$arrMatrizValores=array();
			$posSerie=0;
			$posCategoria=0;
			$arrTotalSerie=array();
			$arrTotalCategoria=array();
			foreach($arrSeries as $s)
			{
				$total=0;
				$posCategoria=0;
				foreach($arrCategorias as $c)
				{
					$valor=0;
					$fValor=array();
					$fValor[2]="";
					if(!$generaSerie)
					{
						$etiqueta="";
						$consulta="SELECT tipoValor,valor,color,etiqueta FROM 9014_valoresAlmacenGrafico WHERE idAlmacen=".$idAlmacen." AND idCategoria=".$c[4]." AND idSerie=".$s[4];
						$fValor=$con->obtenerPrimeraFila($consulta);
						
						if($fValor)
						{
							if($fValor[3]!="")
								$etiqueta=$fValor[3];
							else
								$etiqueta=$c[1];	
							switch($fValor[0])
							{
								case 1:  //Valor constante
									$valor=$fValor[1];
								break;
								case 7:  //Consulta auxiliar
									$arrValor=explode("|",$fValor[1]);
									if(isset($arrResultConsulta[$arrValor[0]]))
									{
										if($arrResultConsulta[$arrValor[0]]["ejecutado"]==1)
										{
											$valor=str_replace("'","",$arrResultConsulta[$arrValor[0]]["resultado"]);
											
											if(!is_numeric($valor))
												$valor=0;
										}
										else
										{
											$consulta=$arrResultConsulta[$arrValor[0]]["query"];
											
			
											if(sizeof($c[3])>0)
												$consulta=str_replace("'@idCategoria'",$c[0],$consulta);
											if(sizeof($s[3])>0)	
												$consulta=str_replace("'@idSerie'",$s[0],$consulta);
											
			
											if(strpos($consulta,"'@")===false)
											{
												$conAux=$arrResultConsulta[$arrValor[0]]["conector"];
												$valor=$conAux->obtenerValor($consulta);
												if(!is_numeric($valor))
													$valor=0;
												
											}
											else
												$valor=0;
											
										}
									}
									
									
								break;
								case 11:  //Almacén de datos
									$valor=0;
									$arrValor=explode("|");
									if(isset($arrResultConsulta[$arrValor[0]]))
									{
										if($arrResultConsulta[$arrValor[0]]["ejecutado"]==1)
										{
											$resTmp=$arrResultConsulta[$arrValor[0]]["resultado"];
											
											$conAux=$arrResultConsulta[$arrValor[0]]["conector"];
											$resTmp=$arrResultConsulta[$arrValor[0]]["resultado"];
											$conAux->inicializarRecurso($resTmp);
											$fDatos=$conAux->obtenerSiguienteFilaAsoc($resTmp);
											$cNomalizado=str_replace(".","_",$arrValor[1]);
											if(isset($fDatos[$cNomalizado]))
												$valor=$fDatos[$cNomalizado];
											if(!is_numeric($valor))
												$valor=0;
												
											
											
										}
										else
										{
											$consulta=$arrResultConsulta[$arrValor[0]]["query"];
											if(sizeof($c[3])>0)
												$consulta=str_replace("'@idCategoria'",$c[0],$consulta);
											if(sizeof($s[3])>0)	
												$consulta=str_replace("'@idSerie'",$s[0],$consulta);
			
											if(strpos($consulta,"'@")===false)
											{
												$conAux=$arrResultConsulta[$arrValor[0]]["conector"];
												$resTmp=$conAux->obtenerFilas($consulta);
												$fDatos=$conAux->obtenerSiguienteFilaAsoc($resTmp);
												$cNomalizado=str_replace(".","_",$arrValor[1]);
												if(isset($fDatos[$arrValor[1]]))
													$valor=$fDatos[$arrValor[1]];
												if(!is_numeric($valor))
													$valor=0;
											}
											else
												$valor=0;
											
										}
									}
									
								break;
								case 22:  //Invocacion de funcion
									$objParametrosValorSerie=array();
									$objParametrosValorSerie["idSerie"]=$s[4];
									$objParametrosValorSerie["valorSerie"]=$s[0];
									$objParametrosValorSerie["idCategoria"]=$c[4];
									$objParametrosValorSerie["valorCategoria"]=$c[0];
									
									$reflectionClase = new ReflectionObject($ojParametrosReporte);
									foreach ($reflectionClase->getProperties() as $property => $value) 
									{
										$objParametrosValorSerie[$value->getName()]=$value->getValue($ojParametrosReporte);	
									}
									$cadObj='{"idReporte":"'.$idReporte.'","params":'.json_encode($objParametrosValorSerie).'}';
									$objParam=json_decode($cadObj);
									$cache=NULL;
									$valorTmp=resolverExpresionCalculoPHP($fValor[1],$objParam,$cache);
									if(gettype($valorTmp)=="array")
									{
										$fValor[2]=$valorTmp["color"];
										$valor=$valorTmp["valor"];
										if(isset($valorTmp["etiqueta"]))
											$etiqueta=$valorTmp["etiqueta"];
									}
									else
										$valor=$valorTmp;
									$valor=str_replace("'","",$valor);
									if(!is_numeric($valor))
										$valor=0;
								break;
							}
						}
					}
					else
					{
						$etiqueta=$c[1];	
						$valor=$arrMatrizValoresAux[$c[4]];
					}
					$total+=$valor;
					if(!isset($arrTotalCategoria[$c[4]."_".$c[1]]))
						$arrTotalCategoria[$c[4]."_".$c[1]]=$valor;
					else
						$arrTotalCategoria[$c[4]."_".$c[1]]+=$valor;	
						
					$arrMatrizValores[$c[4]."_".$c[1]][$s[4]."_".$s[1]]=$valor;
					$posCategoria++;
				}
				$arrTotalSerie[$s[4]."_".$s[1]]=$total;
				/*if($idCategoriaGrafico==1)
					break;*/
				$posSerie++;
			}
			$arrResultConsulta[$fila[5]]["resultado"]=	$arrMatrizValores;
			$arrResultConsulta[$fila[5]]["totalCategoria"]=$arrTotalCategoria;
			$arrResultConsulta[$fila[5]]["totalSerie"]=$arrTotalSerie;
			$arrResultConsulta[$fila[5]]["nomConsulta"]=$fila[7];
			
		}
		return $arrResultConsulta;
	}
	
	function obtenerAlmacenGraficoPorcentajeSerie($datosAlmacen,$decimalPrecision)
	{
		$dAlmacen=$datosAlmacen["resultado"];
		$arrCategoria=$datosAlmacen["totalCategoria"];
		$arrSerie=$datosAlmacen["totalSerie"];
		$nCategorias=sizeof($arrCategoria);
		$nSeries=sizeof($arrSerie);
		$totalAcumuladoSerie=0;
		$arrFinal=array();
		$posSerie=0;
		$posCategoria=0;
		foreach($arrSerie as $s => $resto)
		{
			$arrSer=explode("_",$s);
			$ser=$arrSer[0];
			$totalAcumuladoSerie=0;
			$posCategoria=0;
			foreach($arrCategoria as $c =>$resCategoria)
			{
				$arrCat=explode("_",$c);
				$cat=$arrCat[0];
				if($posCategoria==($nCategorias-1))
				{
					$valor=$dAlmacen[$c][$s];
					if(($totalAcumuladoSerie==0)&&($valor==0))
						$valor=0;
					else
					{
						$valor=(100-$totalAcumuladoSerie);
						$valor=str_replace(",","",number_format($valor,$decimalPrecision));
					}
				}
				else
				{
					if($resto!=0)
					{
						$valor=$dAlmacen[$c][$s];
						$valor=($valor/$resto)*100;
						$valor=str_replace(",","",number_format($valor,$decimalPrecision));
						if(($totalAcumuladoSerie+$valor)>100)
						{
							$valor=($totalAcumuladoSerie+$valor)-100;
							$valor=str_replace(",","",number_format($valor,$decimalPrecision));
						}
							
					}
					else
						$valor=0;
				}
				$posCategoria++;
				$arrFinal[$cat][$ser]=$valor;
				$totalAcumuladoSerie+=$valor;
			}
			
			$posSerie++;
		}
		return $arrFinal;
	}
		
	function obtenerAlmacenGraficoPorcentajeCategoria($datosAlmacen,$decimalPrecision)
	{
		$dAlmacen=$datosAlmacen["resultado"];
		$arrCategoria=$datosAlmacen["totalCategoria"];
		$arrSerie=$datosAlmacen["totalSerie"];
		$nCategorias=sizeof($arrCategoria);
		$nSeries=sizeof($arrSerie);
		$totalAcumuladoSerie=0;
		$arrFinal=array();
		$posSerie=0;
		$posCategoria=0;
		foreach($arrCategoria as $c => $resto)
		{
			$arrCat=explode("_",$c);
			$cat=$arrCat[0];
			$totalAcumuladoCategoria=0;
			$posSerie=0;
			foreach($arrSerie as $s=>$resCategoria)
			{
				$arrSer=explode("_",$s);
				$ser=$arrSer[0];
				if($posSerie==($nSeries-1))
				{
					$valor=$dAlmacen[$c][$s];
					if(($totalAcumuladoCategoria==0)&&($valor==0))
						$valor=0;
					else
					{
						$valor=(100-$totalAcumuladoCategoria);
						$valor=str_replace(",","",number_format($valor,$decimalPrecision));
					}
				}
				else
				{
					if($resto!=0)
					{
						$valor=$dAlmacen[$c][$s];
						$valor=($valor/$resto)*100;
						$valor=str_replace(",","",number_format($valor,$decimalPrecision));
						if(($totalAcumuladoCategoria+$valor)>100)
						{
							$valor=($totalAcumuladoCategoria+$valor)-100;
							$valor=str_replace(",","",number_format($valor,$decimalPrecision));
						}
							
					}
					else
						$valor=0;
				}
				$posSerie++;
				$arrFinal[$cat][$ser]=$valor;
				$totalAcumuladoCategoria+=$valor;
			}
			
			
		}
		return $arrFinal;
	}
	
	function resolverConsulta($id,$paramDatos,$pNombreCampoCompleto=false,$vRegistros=false,$ejecutarConsultas=false,$conBD=NULL)
	{
		global $con;

		$conAux="";
		$conexionBD=$con;
		if($conBD!=NULL)
			$conexionBD=$conBD;
		
		$nIntentosResolucion=20;
		$nCiclos=0;
		$arrConexiones=array();
		$arrConexiones[0]=$conexionBD;
		$modoDebugger=false;
		if(isset($_SESSION["debuggerQueries"])&&($_SESSION["debuggerQueries"]==1))
			$modoDebugger=true;
	
		$arrOrden[0]="ASC";
		$arrOrden[1]="DESC";
		$arrCamposFrmTablas=array();
		$consulta="SELECT campoUsr,campoMysql FROM 9017_camposControlFormulario";
		$resCampo=$conexionBD->obtenerFilas($consulta);
		while($filaCampo=mysql_fetch_row($resCampo))
		{
			$arrCamposFrmTablas[$filaCampo[0]]=$filaCampo[1];
		}
	
		$consulta="select nodosFiltro,camposProy,operacion,nTabla,parametros,idDataSet,tipoAlmacen,nombreDataSet,orden,complementario,idConexion,obtenerValoresDistintos,numRegistros from 
				9014_almacenesDatos where idDataSet=".$id;
		$resConAux=$conexionBD->obtenerFilas($consulta);
		$arrValorParamReporte=array();
		$arrResultConsulta=array();
		$arrValorParamAlmacen=array();
		$finalizarConsulta=false;
		
		while(!$finalizarConsulta)
		{

			$nEsperaResolver=0;
	
			while($filaCon=mysql_fetch_row($resConAux))
			{
				$numRegistros=$filaCon[12];
				if(!isset($arrConexiones[$filaCon[10]]))
				{
					$arrConexiones[$filaCon[10]]=generarInstanciaConector($filaCon[10]);
					if(!$arrConexiones[$filaCon[10]]->conexion)
						continue;
				}
				if(!isset($arrResultConsulta[$filaCon[5]]))
				{
					$esperaResolver=false;
					$arrResp=validarConsultaAlmacen($filaCon[5],$conexionBD);
					if(sizeof($arrResp)>0)
					{
					?>
						<table>
							<tr>
								<td align="center">
									<span class="corpo8_bold">
									No se ha podido construir el módulo debido al siguiente problema:
									</span>
								</td>
							</tr>
							<tr>
								<td align="left"><br /><br />
								<span class="copyrigthSinPadding">
			
								<?php
									foreach($arrResp as $problema)
									{
										echo "<br><img src='../images/bullet_green.png'>".$problema." <b>".$filaCon[7]."</b><br>";
									}
								?>
								</span>
								<br /><br /><br />
								<span class="letraRojaSubrayada8">
								Se recomienda reportar el problema al administrador del sistema para su corrección.
								</span>
								</td>
							</tr>
						</table>
					<?php	
						return false;	
					}
					
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
							if($filaCon[10]==0)
							{
								$consulta="select idFormulario from 900_formularios where nombreTabla='".$nTabla."'";
								$idFormulario=$conexionBD->obtenerValor($consulta);
								$consulta="SELECT tipoElemento,idGrupoElemento,nombreCampo FROM 901_elementosFormulario WHERE idFormulario=".$idFormulario;
				
								$resElementoFormulario=$conexionBD->obtenerFilas($consulta);
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
								$arrElementosTabla[$filaCon[10]."@".$nTabla]=$arrElementos;
							}
							else
							{
								$conAux=$arrConexiones[$filaCon[10]];
								if($conAux->esSistemaLatis())
								{
									$consulta="select idFormulario from 900_formularios where nombreTabla='".$nTabla."'";
									$idFormulario=$conAux->obtenerValor($consulta);
									$consulta="SELECT tipoElemento,idGrupoElemento,nombreCampo FROM 901_elementosFormulario WHERE idFormulario=".$idFormulario;
					
									$resElementoFormulario=$conAux->obtenerFilas($consulta);
									$arrElementos=array();
									while($filaElemento=$conAux->obtenerSiguienteFila($resElementoFormulario))
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
									$arrElementosTabla[$filaCon[10]."@".$nTabla]=$arrElementos;
								}
							}
						}
					}	
					
					$arrParamControl="";
					$arrParamPendientes=array();
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

							$filaParam=$conexionBD->obtenerPrimeraFila($consulta);
							if(!$filaParam)
								$arrValorParamAlmacen[substr($param,1)]="-100584";
							else
							{
								$valor="";
								switch($filaParam[1])
								{
									case "1":  //Constante
										$valor=$filaParam[0];
									break;
									case "2":  //Parametro reporte
										$consulta="select parametro from 9015_parametrosReporte where idParametro=".$filaParam[0];
										$parametroRep=$conexionBD->obtenerValor($consulta);
										eval('$valor=$paramDatos->p2->p'.$parametroRep.';');
									break;
									case "3":  //Valor de sesion
										$consulta="select valorSesion,valorReemplazo from 8003_valoresSesion where idValorSesion=".$filaParam[0];
										$filaSesion=$conexionBD->obtenerPrimeraFila($consulta);
										if(($filaParam[0]==1)||($filaParam[0]==4))
											$valor=$_SESSION[$filaSesion[0]];
										else
											$valor=$_SESSION[$filaSesion[0]];
										if($valor=="")
											$valor="-1";
									break;
									case "4":  //Valor de sistema
										$consulta="select valorSesion,valorReemplazo from 8003_valoresSesion where idValorSesion=".$filaParam[0];
										$filaSesion=$conexionBD->obtenerPrimeraFila($consulta);
										$valorSistema="";
										switch($filaParam[0])
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
									case "7":  //Consulta auxiliar
										if(($filaParam[0]<$filaCon[5])||(isset($arrResultConsulta[$filaParam[0]])))
										{
											if(isset($arrResultConsulta[$filaParam[0]]))
											{
												if($arrResultConsulta[$filaParam[0]]["ejecutado"]==1)
													$valor=$arrResultConsulta[$filaParam[0]]["resultado"];	
												else
													$valor='-100584';
											}
											else
												$valor='-100584';
										}
										else
										{
											$esperaResolver=true;
											$nEsperaResolver++;
										}
									break;
									case "8":	//valor de almacen padre
										$valor=$filaParam[0]."|@|".$filaParam[2];  
									break;
									case "9":	//Valor de pàgina
										eval('$valor=$paramDatos->p9->p'.$filaParam[0].';');
									break;
									case "11": //Consulta de almacen
										$clave=$filaParam[0];
										if(($filaParam[0]<$filaCon[5])||(isset($arrResultConsulta[$filaParam[0]])))
										{
											$arrDatos=explode($clave,"|");
											if(isset($arrResultConsulta[$filaParam[0]]))
											{
												if($arrResultConsulta[$filaParam[0]]["ejecutado"]==1)
												{
													$resultado=$arrResultConsulta[$arrDatos[0]]["resultado"];
													
													$conAux=$arrResultConsulta[$arrDatos[0]]["conector"];
													$conAux->inicializarRecurso($resultado);
													$filaRes=$conAux->obtenerSiguienteFilaAsoc($resultado);
													if(!$filaRes)
														$valor="100584";
													else
													{
														$valor=$filaRes[$arrDatos[1]];
													}
												}
												else
													$valor='-100584';
											}
											else
												$valor='';
										}
										else
										{
											$esperaResolver=true;
											$nEsperaResolver++;
										}
									break;
									case "16": //Valores de proceso
										
										eval('
												if(isset($paramDatos->p16->p'.$filaParam[0].'))
													$valor=$paramDatos->p16->p'.$filaParam[0].';
												else
													$valor=$paramDatos->p16->'.$filaParam[0].';
											');
										
									break;
									case "17": //Parametros varios
										
										eval('$valor=$paramDatos->p17->'.$filaParam[0].';');
									break;
									case "23": //Parametros control
										$valor='@'.$filaParam[0];
										if($arrParamControl=='')
											$arrParamControl=$filaParam[0];
										else
											$arrParamControl.=",".$filaParam[0];
											
									break;
									case "24": //Parametros control V2
										$valor="@Control_".$filaParam[0];
									break;
									
								}
								$arrValorParamAlmacen[substr($param,1)]="".$valor."";
							}
						}
					}
					
					$queryOp="";
					$compAux="";
					$campoDistinto="";
					
					if($filaCon[9]!="")
					{
						$obj=json_decode($filaCon[9]);
						if($obj->distinto==1)
						{
							if($obj->campoDistinto!="")
								$campoDistinto=$obj->campoDistinto;
							$compAux="distinct ";
						}
					}
					
					if($filaCon[11]==1)
						$compAux="distinct ";
					
	
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
								$nCampo=generarCampoFormularioThot($campo,$arrCamposFrmTablas,$arrElementosTabla,$arrConexiones[$filaCon[10]]);
								
								if($pNombreCampoCompleto)
								{
									$arrCampoTmp=explode(" as ",$nCampo);
									if(sizeof($arrCampoTmp)==1)
										$nCampo=$nCampo." as ".str_replace(".","_",$nCampo);
									else
										$nCampo=$arrCampoTmp[0]." as ".str_replace(".","_",$arrCampoTmp[1]);
								}
								if($listCamposProy=="")	
									$listCamposProy=$nCampo;
								else
									$listCamposProy.=",".$nCampo;	
							}
							$queryOp="select ".$compAux.$listCamposProy;
							
							
							
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
							if($campoDistinto!="")
								$camposProy="distinct (".$campoDistinto.")";
							$queryOp="select count(".$camposProy.") as nRegistros";
						break;
						case 7:
							$queryOp="select sqrt(".$camposProy.")";
						break;		
					}
					
					$queryOp.=" from ".$listTabla;
					$objNodos=json_decode($nodosFiltro);
					$ejecutado=1;
					
					$condWhere="";
					if($modoDebugger)
					{
						echo "<span class='letraRojaSubrayada8'>".$filaCon[7]."</span><br><br><span class='letraAzulSubrayada7'>Consulta: ".$filaCon[5]." Nodos WHERE</br></span>";
						varDump($objNodos);
					}
	
					if(sizeof($objNodos)>0)
					{
						foreach($objNodos as $nodo)
						{

							$codMysql=$nodo->tokenMysql;
							$tokenTipo=$nodo->tokenTipo;
							$dToken=explode("|",$tokenTipo);
							if(($dToken[0]==17)&&($tipoDataSet==1))
								$dToken[0]=2;
							switch($dToken[0])
							{
								case 0:
								case 1:
								break;
								case 2:  //Parametro de reporte
									if(!$vRegistros)
									{
										$consulta="select parametro from 9015_parametrosReporte where idParametro=".$dToken[1];

										$param=$conexionBD->obtenerValor($consulta);
										eval('	if(isset($paramDatos->p2->p'.$param.'))
													$valParam=$paramDatos->p2->p'.$param.';
												else
													$valParam=$paramDatos->p2->'.$param.';
											');
										if(strpos($codMysql,' like ')===false)
											$codMysql=str_replace("@","'".$valParam."'",$codMysql);
										else
											$codMysql=str_replace("@",$valParam,$codMysql);
									}
									else
										$codMysql="1=1";

								break;
								case 3:	//Valor de sesion
									$consulta="select valorSesion,valorReemplazo from 8003_valoresSesion where idValorSesion=".$dToken[1];
									$filaSesion=$conexionBD->obtenerPrimeraFila($consulta);
									if(!$vRegistros)
									{
										
										$valor=$_SESSION[$filaSesion[0]];
										if($valor=="")
												$valor="-1";
										if(strpos($codMysql,' like ')===false)
											$codMysql=str_replace('@'.$filaSesion[1],"'".$valor."'",$codMysql);
										else
											$codMysql=str_replace('@'.$filaSesion[1],$valor,$codMysql);
									}
									else
									{
										if($filaCon[10]==0)
										{
											switch($dToken[1])
											{
												case 1://ID de usuario autenticado
												case 2://Departamento de usuario autenticado
												case 3://Institución de usuario autenticado
													$valor="";
													if($dToken[1]==1)
														$valor="responsable";
													else
														if($dToken[1]==2)
															$valor="codigoUnidad";
														else
															$valor="codigoInstitucion";
													if(strpos($codMysql,' like ')===false)
													{
														$codMysql=str_replace('@'.$filaSesion[1],$valor,$codMysql);
														$codMysql=str_replace("'".$valor."'",$valor,$codMysql);
													}
													else
													{
														$arrTokenTmp=explode(" like ",$codMysql);
														$tokenFinal=$arrTokenTmp[1];
														$arrTokens=explode('@'.$filaSesion[1],$codMysql);
														$codMysql=$arrTokenTmp[0]." like concat(";
														if($arrTokens[0]=="'%")
															$codMysql.="'%',".$valor;
														else
															$codMysql.=$valor;
															
														if($arrTokens[1]=="%'")	
															$codMysql.=",'%'";
														$codMysql.=")";
													}
												break;
												default:
													$codMysql="1=1";
												break;
											}
										}
										else
											$codMysql="1=1";
									}
								break;
								case 4:	//Valor de sistema
									$consulta="select valorSesion,valorReemplazo from 8003_valoresSesion where idValorSesion=".$dToken[1];
									$filaSesion=$conexionBD->obtenerPrimeraFila($consulta);
									if(!$vRegistros)
									{
										
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
										if(strpos($codMysql,' like ')===false)
											$codMysql=str_replace('@'.$filaSesion[1],$valorSistema,$codMysql);
										else
											$codMysql=str_replace('@'.$filaSesion[1],str_replace("'","",$valorSistema),$codMysql);
									}
									else
									{
										if($filaCon[10]==0)
										{
											switch($dToken[1])
											{
												case 8://Fecha del sistema
													$valor='DATE_FORMAT(fechaCreacion,"%Y-%m-%d")';
													
													if(strpos($codMysql,' like ')===false)
													{
														$codMysql=str_replace('@'.$filaSesion[1],$valor,$codMysql);
														$codMysql=str_replace("'".$valor."'",$valor,$codMysql);
														
													}
													else
													{
														$arrTokenTmp=explode(" like ",$codMysql);
														$tokenFinal=$arrTokenTmp[1];
														$arrTokens=explode('@'.$filaSesion[1],$codMysql);
														$codMysql=$arrTokenTmp[0]." like concat(";
														if($arrTokens[0]=="'%")
															$codMysql.="'%',".$valor;
														else
															$codMysql.=$valor;
															
														if($arrTokens[1]=="%'")	
															$codMysql.=",'%'";
														$codMysql.=")";
													}
												break;
												default:
													$codMysql="1=1";
												break;
											}
										}
										else
											$codMysql="1=1";
									}
								break;
								case 5:
								case 6:	//Valor de parametro
									$nParam=substr($dToken[1],1);
									
									if(!$vRegistros)
									{
										
										
										if(strpos($arrValorParamAlmacen[$nParam],"|@|")===false)
										{
												
											if($arrValorParamAlmacen[$nParam]!='-100584')
											{
												if(strpos($arrValorParamAlmacen[$nParam],"@Control")!==false)
												{
													
													$arrControl=explode("_",$arrValorParamAlmacen[$nParam]);
													$queryTmp="SELECT nombreCampo FROM 901_elementosFormulario WHERE idGrupoElemento=".$arrControl[1];
													$conAux=$arrConexiones[$filaCon[10]];
													
													$nControl=$con->obtenerValor($queryTmp);
													$ejecutado=0;
													if($nControl!="")
													{
														if($arrParamControl=="")
															$arrParamControl=$nControl;
														else
															$arrParamControl.=",".$nControl;
													}
												}
												
												if(strpos($codMysql," like ")!==false)
													$codMysql=str_replace('@'.$nParam,$arrValorParamAlmacen[$nParam],$codMysql);
												else
													$codMysql=str_replace('@'.$nParam,"'".$arrValorParamAlmacen[$nParam]."'",$codMysql);
											}
											else
											{
												if(strpos($codMysql," like ")!==false)
													$codMysql=str_replace('@'.$nParam,"".'@'.$nParam."",$codMysql);
												else
													$codMysql=str_replace('@'.$nParam,"'".'@'.$nParam."'",$codMysql);
												
												$ejecutado=0;
											}
										}
										else
										{
											$arrDatos=explode("|@|",$arrValorParamAlmacen[$nParam]);	
											$campoRef=$arrDatos[0];
											$ejecutado=0;
											if(!existeValor($arrParamPendientes,$campoRef."|".$arrDatos[1]))
												array_push($arrParamPendientes,$campoRef."|".$arrDatos[1]);
										}
									}
									else
									{
										if($filaCon[10]==0)
										{
											if(strpos($arrValorParamAlmacen[$nParam],"|@|")===false)
											{
												if($arrValorParamAlmacen[$nParam]!='-100584')
												{
													$reemplazarComillas=false;
													$valor=$arrValorParamAlmacen[$nParam];
													if(strpos($arrValorParamAlmacen[$nParam],"@Control")!==false)
													{
														
														$arrControl=explode("_",$arrValorParamAlmacen[$nParam]);
														$queryTmp="SELECT nombreCampo FROM 901_elementosFormulario WHERE idGrupoElemento=".$arrControl[1];
														$conAux=$arrConexiones[$filaCon[10]];
														$reemplazarComillas=true;
														$valor=$con->obtenerValor($queryTmp);	
													}
													
													if(strpos($codMysql,' like ')===false)
													{
														$codMysql=str_replace('@'.$nParam,$valor,$codMysql);
														if($reemplazarComillas)
															$codMysql=str_replace("'".$valor."'",$valor,$codMysql);
													}
													else
													{
														$arrTokenTmp=explode(" like ",$codMysql);
														$tokenFinal=$arrTokenTmp[1];
														$arrTokens=explode('@'.$nParam,$codMysql);
														$codMysql=$arrTokenTmp[0]." like concat(";
														if($arrTokens[0]=="'%")
															$codMysql.="'%',".$valor;
														else
															$codMysql.=$valor;
															
														if($arrTokens[1]=="%'")	
															$codMysql.=",'%'";
														$codMysql.=")";
													}
												}
												else
												{
													$codMysql=str_replace('@'.$nParam,"'".'@'.$nParam."'",$codMysql);
													$ejecutado=0;
												}
											}
											else
												$codMysql="1=1";
										}
										else
											$codMysql="1=1";
									}
								break;
								case 7:	//Valor de consulta auxilizar
									if(!$vRegistros)
									{
										$valorQuery="";
										if(($dToken[1]<$filaCon[5])	||(isset($arrResultConsulta[$dToken[1]])))
										{
											if(isset($arrResultConsulta[$dToken[1]]))
											{
												if($arrResultConsulta[$dToken[1]]["ejecutado"]==1)
													$valorQuery=$arrResultConsulta[$dToken[1]]["resultado"];
												else
												{
													$valorQuery="-100584";
													$ejecutado=0;
												}
												$codMysql=str_replace("@","".$valorQuery."",$codMysql);
											}
											else
											{
												$esperaResolver=true;
												$nEsperaResolver++;
											}
											
											
										}
										else
										{
											$esperaResolver=true;
											$nEsperaResolver++;
										}
									}
									else
										$codMysql="1=1";
								break;
								case 9:  //Valor de pagina
									if(!$vRegistros)
									{
										eval('
												if(isset($paramDatos->p9->p'.$dToken[1].'))
													$valor=$paramDatos->p9->p'.$dToken[1].';
												else
													$valor=$paramDatos->p9->'.$dToken[1].';
											');
											if(strpos($codMysql,' like ')===false)
												$codMysql=str_replace("@","'".$valor."'",$codMysql);
											else
												$codMysql=str_replace("@",$valor,$codMysql);
									}
									else
										$codMysql="1=1";
								break;
								case 11: //Valor de almacen
									if(!$vRegistros)
									{
										$valor="";
										
										if(($dToken[1]<$filaCon[5])	||(isset($arrResultConsulta[$dToken[1]])))
										{
											if(isset($arrResultConsulta[$dToken[1]]))
											{
												if($arrResultConsulta[$dToken[1]]["ejecutado"]==1)
												{
													$resultado=$arrResultConsulta[$dToken[1]]["resultado"];
													$conAux=$arrResultConsulta[$dToken[1]]["conector"];
													
													$conAux->inicializarRecurso($resultado);
													$filaRes=$conAux->obtenerSiguienteFilaAsoc($resultado);
													
													if(!$filaRes)
														$valor="-100584";
													else
													{
														$cNormalizado=str_replace(".","_",$dToken[2]);
														$valor=$filaRes[$cNormalizado];
													}
												}
												else
												{
													$valor="-100584";
													$ejecutado=0;
												}
												$codMysql=str_replace("@","'".$valor."'",$codMysql);	
											}
											else
											{
												$esperaResolver=true;
												$nEsperaResolver++;
											}
											
										}
										else
										{
											$esperaResolver=true;
											$nEsperaResolver++;
										}
									}
									else
										$codMysql="1=1";
								break;
								case 16:  //Valor de proceso
									if((!$vRegistros)||($dToken[1]==1)||($dToken[1]==2))
									{
										$valor="";
										eval('if(isset($paramDatos->p16->p'.$dToken[1].')){$valor=$paramDatos->p16->p'.$dToken[1].';}');
										if(strpos($codMysql,' like ')===false)
											$codMysql=str_replace("@","'".$valor."'",$codMysql);
										else
											$codMysql=str_replace("@",$valor,$codMysql);
									}
									else
									{
										if($filaCon[10]==0)
										{
											switch($dToken[1])
											{
												case 3://ID Registro
												case 5://ID Referencia
												case 6://ID Responsable
													$valor="";
													$nombreTablaBase="_".$paramDatos->p16->p1."_tablaDinamica";
													if($dToken[1]==3)
														$valor="id_".$nombreTablaBase;
													else
														if($dToken[1]==5)
															$valor=$nombreTablaBase.".idReferencia";
														else
															$valor=$nombreTablaBase.".responsable";
													if(strpos($codMysql,' like ')===false)
													{
														$codMysql=str_replace('@',$valor,$codMysql);
														$codMysql=str_replace("'".$valor."'",$valor,$codMysql);
													}
													else
													{
														$arrTokenTmp=explode(" like ",$codMysql);
														$tokenFinal=$arrTokenTmp[1];
														$arrTokens=explode('@',$codMysql);
														$codMysql=$arrTokenTmp[0]." like concat(";
														if($arrTokens[0]=="'%")
															$codMysql.="'%',".$valor;
														else
															$codMysql.=$valor;
															
														if($arrTokens[1]=="%'")	
															$codMysql.=",'%'";
														$codMysql.=")";
													}
												break;
												default:
													$codMysql="1=1";
												break;
											}
										}
										else
											$codMysql="1=1";
									}
								break;
								case 17:  //Valor vario
									if(!$vRegistros)
									{	
										$reemplazar=false;
										$valor="";
										if(isset($paramDatos->p17))
										{
											eval ('
												
													if(isset($paramDatos->p17->p'.$dToken[1].')) 
														$valor=$paramDatos->p17->p'.$dToken[1].';
													else
														$valor=$paramDatos->p17->'.$dToken[1].';
													
												');
										}
												
										$reemplazar=true;
										if($reemplazar)
										{
											if(strpos($codMysql,' like ')===false)
												$codMysql=str_replace("@","'".$valor."'",$codMysql);
											else
												$codMysql=str_replace("@",$valor,$codMysql);
										}
									}
									else
										$codMysql="1=1";
								break;
								case 20:
									$consulta="SELECT nombreCampo FROM 901_elementosFormulario WHERE idGrupoElemento=".$dToken[1];
									$nCampoFormulario=$con->obtenerValor($consulta);
									if($nCampo!="")
									{
										if(!$vRegistros)
										{
											if($paramDatos->p16->p3==-1)
											{
												$resVal=false;
												eval('$resVal=isset($paramDatos->paramAmbiente->'.$nCampoFormulario.');');
												if($resVal)
													eval('$valor=$paramDatos->paramAmbiente->'.$nCampoFormulario.';');
												else
													$valor=-1;
											}
											else
											{
												$consulta="select ".$nCampoFormulario." from _".$paramDatos->p16->p1."_tablaDinamica where id__".$paramDatos->p16->p1."_tablaDinamica=".$paramDatos->p16->p3;
												$valor=$con->obtenerValor($consulta);
											}
										}
										else
										{
											$valor=$nCampoFormulario;
										}
										$codMysql=str_replace("@",$valor,$codMysql);
									}
									else
										$codMysql="1=-1";
								break;
								case 23:
									if(!$vRegistros)
									{
										$valor='@'.$filaParam[0];
										if($arrParamControl=='')
											$arrParamControl=$filaParam[0];
										else
											$arrParamControl.=",".$filaParam[0];
									}
									else
										$codMysql="1=1";
								break;
								case 31:
									$cadObj='{"param1":""}';
									$objParamCalculo=json_decode($cadObj);
									if(isset($paramDatos->parametrosAmbiente))
										$objParamCalculo->param1=$paramDatos->parametrosAmbiente;
									$arrCache=NULL;
									$valor=resolverExpresionCalculoPHP($dToken[1],$objParamCalculo,$arrCache);
									if($valor=="''")
										$valor=-1;
									$codMysql=str_replace("@","".$valor."",$codMysql);
								break;
								
								
							}
							$condWhere.=" ".str_replace("'%'","%'",$codMysql);
						}
						
						$condWhere=normalizarCondicionWhere($condWhere,$arrCamposFrmTablas);
						$queryOp.=' where '.$condWhere;
						
					}
					
					$queryOp=str_replace("()","(-1)",$queryOp);
					$queryOp=str_replace("''","'",$queryOp);
					
					if(($filaCon[8]!="")&&(!$vRegistros))
					{
						
						$objOrden=json_decode($filaCon[8]);
						$cadOrden="";
						foreach($objOrden as $orden)	
						{
							$campo=$orden->campo;
							$arrCampo=explode(".",$orden->campo);
							if(isset($arrCamposFrmTablas[$arrCampo[1]]))
								$campo=$arrCampo[0].".".$arrCamposFrmTablas[$arrCampo[1]];
							$objOrden=$campo." ".$arrOrden[$orden->orden];
							if($cadOrden=='')
								$cadOrden=$objOrden;
							else
								$cadOrden.=",".$objOrden;
						}
						if($cadOrden!="")
							$queryOp.=" order by ".$cadOrden;
						
					}
					
					if(($numRegistros!="")&&($numRegistros!=0))
						$queryOp.=" limit 0,".$numRegistros;
					
					if(!$esperaResolver)
					{
						$arrResultConsulta[$filaCon[5]]["tipoAlmacen"]=$filaCon[6];
						$arrResultConsulta[$filaCon[5]]["query"]=$queryOp;
						$arrResultConsulta[$filaCon[5]]["idConexion"]=$filaCon[10];
						$arrResultConsulta[$filaCon[5]]["conector"]=$arrConexiones[$filaCon[10]];
						$arrResultConsulta[$filaCon[5]]["filasAfectadas"]=0;
						if(($ejecutado==1)&&((!$vRegistros)||($ejecutarConsultas)))
						{
							if($modoDebugger)
							{
								echo "<br><span class='letraAzulSubrayada7'>Consulta construida:</br></span>";
								echo "<span class='copyrigthSinPadding'>".$queryOp."</span><br><br><br><br>";
							}
							$conAux=$arrConexiones[$filaCon[10]];
							if($filaCon[6]==1)	
							{	
								$resQuery=$conAux->obtenerListaValores($queryOp,"'");	
								
								if($resQuery=="")
									$resQuery="-1";//-100584
								$arrResultConsulta[$filaCon[5]]["resultado"]=$resQuery;
							}
							else
								$arrResultConsulta[$filaCon[5]]["resultado"]=$conAux->obtenerFilas($queryOp);
							$arrResultConsulta[$filaCon[5]]["filasAfectadas"]=$conAux->filasAfectadas;
						}
						
						$arrResultConsulta[$filaCon[5]]["ejecutado"]=$ejecutado;
						$arrResultConsulta[$filaCon[5]]["paramPendientes"]=$arrParamPendientes;
						$arrResultConsulta[$filaCon[5]]["arrParamControl"]=$arrParamControl;
						$arrResultConsulta[$filaCon[5]]["nomConsulta"]=$filaCon[7];
					}
				}
			}
			if($nEsperaResolver==0)
				$finalizarConsulta=true;
			else
			{
				mysql_data_seek($resConAux,0);
				$nCiclos++;
				if($nCiclos>$nIntentosResolucion)
					$finalizarConsulta=true;
			}
	
		}
		
		$arrResultConsulta[-1000]=$arrConexiones;

		return $arrResultConsulta[$id];
	}
	
	function normalizarNombreCampo($campo)
	{
		return str_replace(".","_",$campo);
	}
?>