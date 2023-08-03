<?php
	include_once("latis/libreriasFunciones/planPagos.php");
	
	function generarPagosReferenciados($idPlantel,$idUsuario,$tabulador,$arrDimensionesPagoReferenciado,$idFuncion="",$tipoFuncion='NULL')
	{
		global $con;
		$objDatos["idUsuario"]=$idUsuario;
		$objDatos["plantel"]=$idPlantel;

		foreach($tabulador as $pago)
		{
			$objDatos["idConcepto"]=$pago["idConcepto"];
			$objDatos["datosPago"]=$pago;
			$datosComp="";
			if(isset($pago["informacionCalculo"]))
				$datosComp=$pago["informacionCalculo"];
			$fechaVencimiento="NULL";
			if(isset($pago["fechaVencimiento"])&&($pago["fechaVencimiento"]!=""))
				$fechaVencimiento="'".$pago["fechaVencimiento"]."'";			
			generarReferenciaPago($objDatos,$pago["arrFechasPago"],$arrDimensionesPagoReferenciado,$idFuncion,$tipoFuncion,$datosComp,$fechaVencimiento);
		}
	}


	function generarReferenciaPago($objDatos,$arrFechasPago,$arrDimensionesReferencia,$idFuncion="",$tipoFuncion="NULL",$datosComp="",$fechaVencimiento="NULL",$getIdMovimiento=false)
	{
		global $con;
		
		$consulta="SELECT nombreEstructura,idDimension,idFuncionInterpretacion FROM 563_dimensiones";
		$arrDimensiones=$con->obtenerFilasArregloAsocPHP($consulta,true);	
		$x=0;
		$query[$x]="begin";
		$x++;		
		
		$query[$x]="select @idReferencia:=idReferenciaSiguiente FROM 903_variablesSistema FOR update";
		$x++;
		$query[$x]="update 903_variablesSistema set idReferenciaSiguiente=(if((idReferenciaSiguiente+1)>99,1,(idReferenciaSiguiente+1)))";
		$x++;
		$query[$x]="set @referencia:=(concat('".date("ymdHms")."',LPAD(@idReferencia,2,'0')))";
		$x++;
		$idUsuario="-1";
		if(isset($objDatos["idUsuario"])&&($objDatos["idUsuario"]!=""))
			$idUsuario=$objDatos["idUsuario"];
		
		$plantel="";
		if(isset($objDatos["plantel"])&&($objDatos["plantel"]!=""))
			$plantel=$objDatos["plantel"];
		
		
		$idConcepto=-1;
		if(isset($objDatos["idConcepto"])&&($objDatos["idConcepto"]!=""))
			$idConcepto=$objDatos["idConcepto"];
		$descripcionAdeudo="";
		if(isset($objDatos["datosPago"]))
		{
			$descripcionAdeudo=$objDatos["datosPago"]["etiquetaPago"];
		}
		$query[$x]="INSERT INTO 6011_movimientosPago(idReferencia,idUsuario,plantel,fechaGeneracionFolio,idConcepto,idFuncionEjecucion,tipoFuncion,datosComplementarios,fechaVencimiento,descripcionAdeudo) 
				VALUES(@referencia,".$idUsuario.",'".$plantel."','".date("Y-m-d H:i:s")."',".$idConcepto.",'".$idFuncion."',".$tipoFuncion.",'".mysql_escape_string($datosComp)."',".$fechaVencimiento.",'".cv($descripcionAdeudo)."')";
		$x++;
		$query[$x]="set @idRegistro:=(select last_insert_id())";
		$x++;
		
		if(sizeof($arrFechasPago)>0)
		{
			foreach($arrFechasPago as $oPago)
			{
				$fechaInicio=$oPago["fechaInicio"];
				if($fechaInicio=="")
					$fechaInicio="NULL";
				else
					$fechaInicio="'".$fechaInicio."'";
				
				$fechaFin=$oPago["fechaFin"];
				if($fechaFin=="")
					$fechaFin="NULL";
				else
					$fechaFin="'".$fechaFin."'";
				$query[$x]="INSERT INTO 6012_asientosPago(idReferenciaMovimiento,monto,fechaInicio,fechaFin,pagado)
							VALUES(@idRegistro,".$oPago["monto"].",".$fechaInicio.",".$fechaFin.",0)";
				$x++;
			}
		}
		if(sizeof($arrDimensionesReferencia)>0)
		{

			foreach ($arrDimensionesReferencia as $objDimension) 
			{
				$nombre=$objDimension["nombre"];
				$valor=$objDimension["valor"];
				if(isset($arrDimensiones[$nombre]))
				{
					$idDimension=$arrDimensiones[$nombre][0];
					$funcionInterpretacion=$arrDimensiones[$nombre][1];
					$valorInterpretacion="";
					if($funcionInterpretacion!="")
					{
						$cadObj='{"valor":""}';
						$objParam=json_decode($cadObj);
						$objParam->valor=$valor;
						
						$cacheConsulta=NULL;
						$valorInterpretacion=resolverExpresionCalculoPHP($funcionInterpretacion,$objParam,$cacheConsulta);	
					}
					
					$query[$x]="INSERT INTO 6012_detalleAsientoPago(idAsientoPago,idDimension,valorCampo,valorInterpretacion)  VALUES(@idRegistro,".$idDimension.",'".$valor."','".$valorInterpretacion."')";
					$x++;
					
				}
				
			}
		}
		$query[$x]="commit";
		$x++;
		if($con->ejecutarBloque($query))
		{
			if(!$getIdMovimiento)
				$consulta="select @referencia";
			else
				$consulta="select @idRegistro";
			$referencia=$con->obtenerValor($consulta);
			return $referencia;	
		}
	}
	
	function generarTabuladorCostoServicio($idServicio,$idCiclo,$idPeriodo,$arrDimensiones,$objParametros)  //0Plantel; 1=grado;3 idPrograma educativo;
	{
		global $con;
		$oResultado["montoConcepto"]="";
		$oResultado["fechaVencimiento"]="";
		$oResultado["idPerfilCosteo"]="";
		$oResultado["idCostoConcepto"]="";
		$oResultado["planesPago"]=array();
		$fila=NULL;
		$oParam=array();
		$oParam["idServicio"]=$idServicio;
		$oParam["idCiclo"]=$idCiclo;
		$oParam["idPeriodo"]=$idPeriodo;
		if(sizeof($arrDimensiones)>0)
		{
			foreach($arrDimensiones as $llave=>$valor)
			{
				if(!isset($oParam[$llave]))
					$oParam[$llave]=$valor;
			}
		}
		
		if(sizeof($objParametros)>0)
		{
			foreach($objParametros as $p=>$v)
			{
				$oParam[$p]=$v;
			}
		}
		
		if(sizeof($arrDimensiones)>0)
		{
			foreach($arrDimensiones as $d=>$v)
			{
				$oParam[$d]=$v;
			}
		}
		
		$numDimensiones=sizeof($arrDimensiones);
		if(!isset($objParametros["filaCosto"]))
		{
			while(!$fila)
			{
				$consulta="SELECT valor,fechaVencimiento,idCostoConcepto,idPerfilCosteo FROM 6011_costoConcepto WHERE idConcepto=".$idServicio." AND idCiclo=".$idCiclo." AND idPeriodo=".$idPeriodo;
				
				if($numDimensiones>0)
				{
					$nCiclo=0;
					foreach($arrDimensiones as $d=>$v)
					{
						$consulta.=" and ".$d."='".$v."'";
						$nCiclo++;
						if($nCiclo==$numDimensiones)
							break;
					}
				}
				else
					if($numDimensiones<0)
					break;
				
				$fila=$con->obtenerPrimeraFila($consulta);
				$numDimensiones--;
				
			}
		}
		else
			$fila=$objParametros["filaCosto"];
		if($fila)
		{
			
			$oResultado["montoConcepto"]=$fila[0];
			if(isset($objParametros["costoBase"]))
				$oResultado["montoConcepto"]=$objParametros["costoBase"];
			$oResultado["fechaVencimiento"]=$fila[1];
			$oResultado["idPerfilCosteo"]=$fila[3];
			$oResultado["idCostoConcepto"]=$fila[2];
			$consulta="SELECT DISTINCT idPlanPago FROM 564_conceptosVSPlanPago WHERE idConcepto=".$idServicio;
			$resPlanPago=$con->obtenerFilas($consulta);
			while($filaPlan=mysql_fetch_row($resPlanPago))
			{
				$oPlan=array();
				$consulta="SELECT nombrePlanPago FROM 6020_planesPago WHERE idPlanPagos=".$filaPlan[0];
				$nombrePlanPago=$con->obtenerValor($consulta);
				$oPlan["idPlanPago"]=$filaPlan[0];
				$oPlan["nombrePlanPago"]=$nombrePlanPago;
				
				$oPlan["desglocePago"]=calcularPlanPagosMonto($oResultado["montoConcepto"],$filaPlan[0],$oParam,true,$idServicio,$fila[2]);
				
				array_push($oResultado["planesPago"],$oPlan);
			}
			return $oResultado;
		}
		return NULL;
		
	}
	
	function obtenerMontoPagoReferencia($referencia)
	{
		global $con;
		$consulta="SELECT idMovimiento FROM 6011_movimientosPago WHERE idReferencia='".$referencia."'";
		$idReferencia=$con->obtenerValor($consulta);
		$fechaActual=date("Y-m-d");
		$consulta="SELECT monto FROM 6012_asientosPago WHERE idReferenciaMovimiento=".$idReferencia." AND  fechaInicio<='".$fechaActual."' AND (fechaFin>='".$fechaActual."' OR fechaFin is null or fechaFin ='')";
		$monto=$con->obtenerValor($consulta);
		if($monto=="")
			$monto=0;
		return $monto;
	}
	
	function obtenerProductoServicioCaja($referencia)
	{
		global $con;
		$aProducto=array();
		$consulta="SELECT c.cveConcepto,c.nombreConcepto,c.idConcepto,m.idMovimiento,m.idUsuario FROM 561_conceptosIngreso c,6011_movimientosPago m WHERE m.idReferencia='".$referencia."' and pagado=0 AND c.idConcepto=m.idConcepto";
	
		$fDatos=$con->obtenerPrimeraFila($consulta);
		if($fDatos)
		{
			$regProducto=array();
			$regProducto["cveProducto"]=$referencia;
			$regProducto["concepto"]="[".$fDatos[0]."] ".$fDatos[1];
			$regProducto["cantidad"]=1;
			$regProducto["tipoConcepto"]=1;
			$regProducto["idProducto"]=$fDatos[2];
			$regProducto["tipoMovimiento"]=19;
			$regProducto["costoUnitario"]=obtenerMontoPagoReferencia($referencia);
			$regProducto["costoUnitarioNeto"]=obtenerMontoPagoReferencia($referencia);
			$regProducto["iva"]=0;
			$regProducto["total"]=$regProducto["costoUnitario"];
			$regProducto["subtotal"]=$regProducto["costoUnitario"];
			$regProducto["dimensiones"]=array();
			$regProducto["imagen"]="";
			$regProducto["detalle"]="";
			array_push($aProducto,$regProducto);
			
		}
		if(sizeof($aProducto)>0)
		{
			$consulta="SELECT SUM(montoParcialidad) FROM 6012_parcialidadesPago WHERE idMovimiento=".$fDatos[3];
			$totalParcial=$con->obtenerValor($consulta);
			if($totalParcial=="")
				$totalParcial=0;
			
			$regProducto=array();
			$regProducto["metaData"]='[{"tCliente":"2","idCliente":"'.$fDatos[4].'","nombreCliente":"'.cv(obtenerNombreUsuario($fDatos[4])).'","abono":"'.$totalParcial.'"}]';
			array_push($aProducto,$regProducto);
		}
		return $aProducto;
	}	
	
	function generarFechasPagosServicio($idConcepto,$tabuladorPagos,$idPlanPagosAplica=-1,$ignorarFechasVencidas=true)
	{
		global $con;
		$arrTabulador=array();
		$tabulador=$tabuladorPagos;

		if(sizeof($tabulador["planesPago"])>0)
		{
			foreach($tabulador["planesPago"] as $pp)
			{
				$registrarPagos=false;
				if(($idPlanPagosAplica==-1)||($pp["idPlanPago"]==$idPlanPagosAplica))
					$registrarPagos=true;
				if($registrarPagos)
				{
					
					
					foreach($pp["desglocePago"] as $d)
					{
						
						$iConcepto=$idConcepto;
						if($d["idConceptoNoPago"]!=-1)
							$iConcepto=$d["idConceptoNoPago"];
						$oTabulador["idConcepto"]=$iConcepto;
						$oTabulador["etiquetaPago"]=$d["etiquetaPago"];
						$oTabulador["fechaVencimiento"]=$d["fechaVencimiento"];
						
						/*//if($idConcepto==14)
						{
							$consulta="SELECT valor FROM 6016_valoresReferenciaCosteoServicios WHERE idCostoConcepto =".$idConcepto." AND idPlanPago=".$pp["idPlanPago"]." AND noPago=".$d["noPago"]." AND noColumna=0";
							echo $consulta."<br>";
							$fechaVencimiento=$con->obtenerValor($consulta);
						}
						$oTabulador["fechaVencimiento"]=$fechaVencimiento;*/
						$oTabulador["arrFechasPago"]=array();
						$oTabulador["informacionCalculo"]=gzdeflate(serialize($d));
						$oDetalle=array();
						
						if(sizeof($d["arrDescuestosCargos"])==0)
						{
							
							$oDetalle["monto"]=$d["pagoIndividual"];
							$oDetalle["fechaInicio"]=date("Y-m-d");
							$oDetalle["fechaFin"]="";
							
							array_push($oTabulador["arrFechasPago"],$oDetalle);
							
						}
						else
						{
							
							$arrFechasDescuentos=array();
							
							foreach($d["arrDescuestosCargos"] as $op)
							{
								$fLlave=$op["fechaFin"];
								
								if($fLlave=="")
								{
									if($op["fechaInicio"]!="")
										$fLlave=$op["fechaInicio"];
									else
										$fLlave="_";
								}
								if(!isset($arrFechasDescuentos[$fLlave]))
									$arrFechasDescuentos[$fLlave]=array();
								array_push($arrFechasDescuentos[$fLlave],$op);
								
							}
							
								
							if(!isset($arrFechasDescuentos["_"]))
							{
								$oBase["fechaInicio"]="";
								$oBase["fechaFin"]="";
								$oBase["idConcepto"]=0;
								$oBase["etiqueta"]="";
								$oBase["opMonto"]=$d["pagoIndividual"];
								$oBase["cantidadMonto"]=0;
								$oBase["tipoOperacion"]=0;
								$oBase["valoresReferencia"]=array();
								$arrFechasDescuentos["_"][0]=$oBase;
							}
							ksort($arrFechasDescuentos);
							
							
							if((sizeof($arrFechasDescuentos)==1)&&(isset($arrFechasDescuentos["_"])))
							{
								
								$oDetalle["monto"]=$d["montoFinal"];
								$oDetalle["fechaInicio"]=date("Y-m-d");
								$oDetalle["fechaFin"]="";
								array_push($oTabulador["arrFechasPago"],$oDetalle);
								
							}
							else
							{
								
								$fechaInicioAux=date("Y-m-d");
								$fechaFin="";
								$existeFechaPosterior=false;
								$existeFechaAnterior=false;
								$existeDespues=false;
								$arrTempFechas=array();
								
								foreach($arrFechasDescuentos as $f=>$resto)
								{
									
									if($f!="_")
									{
										$montoFinal=0;
										$fechaInicio="";
										$fechaFin="";
										$tFecha=0;
										
										foreach($resto as $r)
										{
											$montoFinal=$r["opMonto"];
											$fechaInicio=$r["fechaInicio"];
											$fechaFin=$r["fechaFin"];
										}
										if($fechaFin!="")	
										{
											$tFecha=0;//Antes de
											$existeFechaAnterior=true;
										}
										else	
										{
											$tFecha=1;//Despues de
											$existeFechaPosterior=true;
											$existeDespues=true;
										}
										if((!$existeDespues)||($tFecha==1))
										{
											$obFecha["monto"]=$montoFinal;
											$obFecha["fechaInicio"]=$fechaInicio;
											$obFecha["fechaFin"]=$fechaFin;
											$obFecha["tipoFecha"]=$tFecha;
											array_push($arrTempFechas,$obFecha);
										}
										
									}
								}
								
								$nPos=0;
								$maxFechas=sizeof($arrTempFechas);
								$anularRubro=false;
								for($nPos=0;$nPos<$maxFechas;$nPos++)
								{
									if($nPos==0)
									{
										if($arrTempFechas[$nPos]["tipoFecha"]==0)
											$arrTempFechas[$nPos]["fechaInicio"]=date("Y-m-d");
										
									}
									else
									{
										if($arrTempFechas[$nPos]["tipoFecha"]==0)//antes de 
										{
											$eAnterior=$arrTempFechas[$nPos-1];
											if($eAnterior["tipoFecha"]==0)
											{
												$arrTempFechas[$nPos]["fechaInicio"]=date("Y-m-d",strtotime("+1 days",strtotime($arrTempFechas[$nPos-1]["fechaFin"])));
											}
											
										}
										else
										{
											$eAnterior=$arrTempFechas[$nPos-1];
											if($eAnterior["tipoFecha"]==1)
											{
												$arrTempFechas[$nPos-1]["fechaFin"]=date("Y-m-d",strtotime("-1 days",strtotime($arrTempFechas[$nPos]["fechaInicio"])));
											}
											
												
										}
									}
								}
								
								$aPagosFinal=array();
								if(!$existeFechaAnterior)
								{
									$oPago=array();
									$oPago["fechaInicio"]=date("Y-m-d");
									$oPago["fechaFin"]=date("Y-m-d",strtotime("-1 days",strtotime($arrTempFechas[0]["fechaInicio"])));
									$oPago["monto"]=$arrFechasDescuentos["_"][0]["opMonto"];
									array_push($aPagosFinal,$oPago);
								}
								
								for($nPos=0;$nPos<$maxFechas;$nPos++)
								{
									if($nPos>0)
									{
										$eAnterior=$arrTempFechas[$nPos-1];
										$fFinAnteriorVirtual=date("Y-m-d",strtotime("-1 days",strtotime($arrTempFechas[$nPos]["fechaInicio"])));
										$fFinAnteriorReal=$eAnterior["fechaFin"];
										if($fFinAnteriorReal!=$fFinAnteriorVirtual)
										{
											$oPago=array();
											$oPago["fechaInicio"]=date("Y-m-d",strtotime("+1 days",strtotime($fFinAnteriorReal)));
											$oPago["fechaFin"]=$fFinAnteriorVirtual;
											$oPago["monto"]=$arrFechasDescuentos["_"][0]["opMonto"];
											array_push($aPagosFinal,$oPago);
										}
										
									}
									$oPago=array();
									$oPago["fechaInicio"]=$arrTempFechas[$nPos]["fechaInicio"];
									$oPago["fechaFin"]=$arrTempFechas[$nPos]["fechaFin"];
									$oPago["monto"]=$arrTempFechas[$nPos]["monto"];
									array_push($aPagosFinal,$oPago);
								}
								
								
								if(!$existeFechaPosterior)
								{
									$oPago=array();
									$oPago["fechaInicio"]=date("Y-m-d",strtotime("+1 days",strtotime($arrTempFechas[sizeof($arrTempFechas)-1]["fechaFin"])));
									$oPago["fechaFin"]="";
									$oPago["monto"]=$arrFechasDescuentos["_"][0]["opMonto"];
									array_push($aPagosFinal,$oPago);
								}
								
								foreach($aPagosFinal as $p)
								{
									$oDetalle=array();
									$oDetalle["monto"]=$p["monto"];
									$oDetalle["fechaInicio"]=$p["fechaInicio"];
									$oDetalle["fechaFin"]=$p["fechaFin"];

									
									$agregar=false;
									if(!$ignorarFechasVencidas||(strtotime($oDetalle["fechaInicio"])<strtotime($oDetalle["fechaFin"]))||($oDetalle["fechaFin"]==""))
										$agregar=true;
									
									
									
									if($agregar)
										array_push($oTabulador["arrFechasPago"],$oDetalle);
								}
								
										
							}
							
						}
						array_push($arrTabulador,$oTabulador);
						
						
						
					}
				}
			}
		}
		else
		{
			$oTabulador["idConcepto"]=$idConcepto;
			$consulta="SELECT nombreConcepto FROM 561_conceptosIngreso WHERE idConcepto=".$idConcepto;
			$etiqueta=$con->obtenerValor($consulta);
			$oTabulador["etiquetaPago"]=$etiqueta;
			$oTabulador["fechaVencimiento"]=$tabulador["fechaVencimiento"];
			$oTabulador["arrFechasPago"]=array();
			$consulta="SELECT idRecuperacionCosteo FROM 6022_perfilesCosteo WHERE idPerfilCosteo=".$tabulador["idPerfilCosteo"];
			$idRecuperacionCosteo=$con->obtenerValor($consulta);
			if(($idRecuperacionCosteo=="")||($idRecuperacionCosteo==-1))
			{
				$oDetalle=array();
				$oDetalle["monto"]=$tabulador["montoConcepto"];
				$oDetalle["fechaInicio"]=date("Y-m-d");
				$oDetalle["fechaFin"]="";
				array_push($oTabulador["arrFechasPago"],$oDetalle);
			}
			else
			{
				$cadObj='{"idCostoConcepto":"'.$tabuladorPagos["idCostoConcepto"].'"}';
				$objParam=json_decode($cadObj);
				
				
				$cacheConsulta=NULL;
				$arrFechasPago=resolverExpresionCalculoPHP($idRecuperacionCosteo,$objParam,$cacheConsulta);	
				$oTabulador["arrFechasPago"]=$arrFechasPago;
			}
			array_push($arrTabulador,$oTabulador);
		}

							
		return $arrTabulador;
		
	}
	
	function obtenerIdAsientoPagoReferenciado($dimensiones,$idConcepto=-1)
	{
			global $con;
			$saldo=0;
			$listAsientos="";
			$arrIdAsientos=array();
			$arrDimensiones=array();
			$consulta="SELECT nombreEstructura,idDimension,idFuncionInterpretacion FROM 563_dimensiones";
			$arrDimensiones=$con->obtenerFilasArregloAsocPHP($consulta,true);	
			$tDimensiones=sizeof($dimensiones);

			foreach($dimensiones as $d=>$valor)
			{
				$idDimension=-1;
				if(isset($arrDimensiones[$d]))
					$idDimension=$arrDimensiones[$d][0];

				if($idDimension!=-1)
				{
					$consulta="";
					
					if($idConcepto==-1)
					{
						$consulta="select idAsientoPago FROM 6012_detalleAsientoPago WHERE idDimension=".$idDimension." AND valorCampo='".$valor."'";
						
					}
					else
					{
						$consulta="select d.idAsientoPago FROM 6012_detalleAsientoPago d,6011_movimientosPago  c WHERE 
							c.idConcepto=".$idConcepto." and c.idMovimiento=d.idAsientoPago and
							  idDimension=".$idDimension." AND valorCampo='".$valor."'";
						
						
						
					}
					
					
					$res=$con->obtenerFilas($consulta);
					while($f=mysql_fetch_row($res))
					{
						if(!isset($arrIdAsientos[$f[0]]))
							$arrIdAsientos[$f[0]]=1;
						else
							$arrIdAsientos[$f[0]]++;
					}
				}
			}
			
			if(sizeof($arrIdAsientos)>0)
			{
				foreach($arrIdAsientos as $idAsiento=>$nElementos)
				{
					if($tDimensiones==$nElementos)
					{
						if($listAsientos=="")
							$listAsientos=$idAsiento;
						else
							$listAsientos.=",".$idAsiento;
					}
				}
			}
			
			if($listAsientos=="")
				$listAsientos=-1;
			
			return $listAsientos;
		
		
	}
	
	function obtenerFechaPagoCosteoEstandarTabuladorDescuento($idCostoConcepto)
	{
		global $con;
		$arrFechas=array();	
		$ultimaFecha=strtotime("-1 days",strtotime(date("Y-m-d")));
		$consulta="SELECT valorReferencia4,valorReferencia3 FROM 6016_valoresReferenciaComplementariosCosteoServicios WHERE  idCostoConcepto=".$idCostoConcepto;
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$oFecha["monto"]=$fila[0];
			$oFecha["fechaInicio"]=date("Y-m-d");
			$oFecha["fechaFin"]=$fila[1];
			$ultimaFecha=strtotime($fila[1]);
			array_push($arrFechas,$oFecha);
		}
		
		for($x=1;$x<sizeof($arrFechas);$x++)
		{
			$arrFechas[$x]["fechaInicio"]=date("Y-m-d",strtotime("+1 days",strtotime($arrFechas[$x-1]["fechaFin"])));

		}
		$consulta="SELECT valor FROM 6011_costoConcepto WHERE idCostoConcepto=".$idCostoConcepto;
		$fila=$con->obtenerPrimeraFila($consulta);
		if($fila[0]=="")
			$fila[0]="0";
		$oFecha["monto"]=$fila[0];
		$oFecha["fechaInicio"]=date("Y-m-d",strtotime("+1 days",$ultimaFecha));
		$oFecha["fechaFin"]="";
		array_push($arrFechas,$oFecha);
		return $arrFechas;
	}
?>