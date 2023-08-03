<?php 
	include ("latis/funcionesContabilidad.php");
	class cContabilidad
	{
		var $cicloFiscal;
		var $tblAsientoLibroDiario;
		var $tblDetallesAsientoLibroDiario;
		var $arrDimensiones;
		var $arrDimensionesVigentes;
		var $arrDimensionesVigentesPresupuesto;
		var $arrDescripcionConceptos;
		var $validadaBaseContable;
		var $arrCuentas;
		function cContabilidad($cFiscal="")
		{
			global $con;
			$this->validadaBaseContable=false;
			if($cFiscal=="")
			{
				$consulta="SELECT idCicloFiscal FROM 550_cicloFiscal WHERE STATUS=1";
				$this->cicloFiscal=$con->obtenerValor($consulta);
			}
			else
				$this->cicloFiscal=$cFiscal;
			$this->tblAsientoLibroDiario="595_asientosLibroDiarioC_".$this->cicloFiscal;
			$this->tblDetallesAsientoLibroDiario="596_detallesAsientoLibroC_".$this->cicloFiscal;
			
			
			$consulta="SELECT nombreEstructura,idDimension,idFuncionInterpretacion FROM 563_dimensiones";
			$this->arrDimensiones=$con->obtenerFilasArregloAsocPHP($consulta,true);	
			$consulta="SELECT idDimension,'' AS contenido FROM 592_dimensionesVigentesLibroDiario WHERE cicloFiscal=".$this->cicloFiscal;
			$this->arrDimensionesVigentes=$con->obtenerFilasArregloAsocPHP($consulta);	
			
			$consulta="SELECT idDimension,'' AS contenido FROM 572_dimensionesVigentesAsientoPresupuestal";
			$this->arrDimensionesVigentesPresupuesto=$con->obtenerFilasArregloAsocPHP($consulta);	
			$this->arrDescripcionConceptos=array();
			$query="SELECT idConcepto,descripcionRegistroMovimiento FROM 598_perfilesMovimientos";
			$res=$con->obtenerFilas($query);
			while($f=mysql_fetch_row($res))
			{
				$this->arrDescripcionConceptos[$f[0]]=$f[1];
			}
			$this->arrCuentas=array();
			$query="SELECT codigoCta,CONCAT('[',codigoUnidadCta,'] ',tituloCta) FROM 510_cuentas";
			$res=$con->obtenerFilas($query);
			while($f=mysql_fetch_row($res))
			{
				$this->arrCuentas["".$f[0]]=$f[1];
			}
		}
		
		function existeBaseContabilidad()
		{
			global $con;
			return $con->existeTabla("595_asientosLibroDiarioC_".$this->cicloFiscal);
		}
		
		function crearBaseContabilidad()
		{
			global $con;
			$x=0;
			$consulta[$x]="begin";
			$x++;
			$consulta[$x]="CREATE TABLE 595_asientosLibroDiarioC_".$this->cicloFiscal." LIKE 595_asientosLibroDiarioBase";
			$x++;
			$consulta[$x]="CREATE TABLE 596_detallesAsientoLibroC_".$this->cicloFiscal." LIKE 596_detallesAsientoLibroBase";
			$x++;
			$consulta[$x]="commit";
			$x++;
			return $con->ejecutarBloque($consulta);
		}
		
		function asentarMovimiento($obj)
		{
			global $con;
			$idLibro=-1;
			
			$query="SELECT tipoOrigenLibro,idLibro,descripcionRegistroMovimiento FROM 598_perfilesMovimientos WHERE idConcepto=".$obj->tipoMovimiento;
			$fMovimiento=$con->obtenerPrimeraFila($query);
			if($fMovimiento[0]==0)
				$idLibro=$fMovimiento[1];
			else
			{
				$cadObj='{"vAmbiente":"","tipoMovimiento":"'.$obj->tipoMovimiento.'"}';
				$objParam=json_decode($cadObj);
				$objParam->vAmbiente=$obj;
				$cacheConsulta=NULL;
				$idLibro=resolverExpresionCalculoPHP($fMovimiento[1],$objParam,$cacheConsulta);

			}			
			
			if(!$this->existeBaseContabilidad())
				$this->crearBaseContabilidad();
				
			if(!isset($obj->folio)||($obj->folio==""))
				$folio=$this->obtenerFolio($obj);
			else
				$folio=$obj->folio;
			$x=0;
			
			$consulta[$x]="begin";
			$x++;
			$arrAsientos=array();
			if(!isset($obj->arrAsientos))
			{
			
				$query="SELECT cuentaAfectacion,'' AS etiqueta,porcentaje,tipoAfectacion,idFuncionAplicacion,idAfectacion from 599_afectacionContableMovimiento WHERE idPerfilMovimiento=".$obj->tipoMovimiento;
				$resAfectacion=$con->obtenerFilas($query);
				while($filaAfectacion=mysql_fetch_row($resAfectacion))
				{
					$considerarMovimiento=true;
					if(($filaAfectacion[4]!="")&&($filaAfectacion[4]!="-1"))
					{
						$cadObj='{"vAmbiente":"","idAfectacion":"'.$filaAfectacion[5].'"}';
						$objParam=json_decode($cadObj);
						$objParam->vAmbiente=$obj;
						$cacheConsulta=NULL;
						$resAplicacion=resolverExpresionCalculoPHP($fFolio[1],$objParam,$cacheConsulta);
						if($resAplicacion!='1')
							$considerarMovimiento=false;
					}
					
					if($considerarMovimiento)
					{
						$objDatos=array();
						$objDatos[0]=$filaAfectacion[0];
						$objDatos[1]=$fMovimiento[2];
						$objDatos[2]=($filaAfectacion[2]/100);
						$objDatos[3]=$filaAfectacion[3];
						$objDatos[4]=($filaAfectacion[2]/100)*$obj->montoOperacion;
						array_push($arrAsientos,$objDatos);
					}
				}
			}
			else
				$arrAsientos=$obj->arrAsientos;
			if(sizeof($arrAsientos)>0)
			{
				foreach($arrAsientos as $filaAfectacion)
				{
					$noMovimiento=$this->obtenerNoMovimiento();
					$montoDebe=0;
					$montoHaber=0;
					if($filaAfectacion[3]==0)
						$montoDebe=$filaAfectacion[4];
					else
						$montoHaber=$filaAfectacion[4];
					
					$cuenta=$filaAfectacion[0];
					$query="SELECT CONCAT('[',codigoUnidadCta,'] ',tituloCta) FROM 510_cuentas WHERE codigoCta='".$filaAfectacion[0]."' and ciclo=".$this->cicloFiscal;
					$lblCuenta=$con->obtenerValor($query);
					
						
					$consulta[$x]=" insert into ".$this->tblAsientoLibroDiario."(idLibroDiario,noMovimiento,fechaMovimiento,codigoCuenta,montoDebeOperacion,montoHaberOperacion,tipoOperacion,idResponsableMovimiento,
								descripcionMovimiento,folioMovimiento,tipoMovimiento,cuenta) values(".$idLibro.",".$noMovimiento.",'".date("Y-m-d H:i:s")."','".$lblCuenta."',".$montoDebe.",".$montoHaber.",".$filaAfectacion[3].",
								".$_SESSION["idUsr"].",'".$filaAfectacion[1]."','".$folio."',".$obj->tipoMovimiento.",'".$cuenta."')";
					$x++;
					if(isset($obj->dimensiones)&(sizeof($obj->dimensiones)>0))
					{
						$consulta[$x]="set @idAsiento=(select last_insert_id())";
						$x++;
						foreach ($obj->dimensiones as $nombre=>$valor) 
						{
							if(isset($this->arrDimensiones[$nombre]))
							{
								$idDimension=$this->arrDimensiones[$nombre][0];
								$funcionInterpretacion=$this->arrDimensiones[$nombre][1];
								$valorInterpretacion="";
								if($funcionInterpretacion!="")
								{
									$cadObj='{"valor":""}';
									$objParam=json_decode($cadObj);
									$objParam->valor=$valor;
									$cacheConsulta=NULL;
									$valorInterpretacion=resolverExpresionCalculoPHP($funcionInterpretacion,$objParam,$cacheConsulta);	
								}
								
								$consulta[$x]="INSERT INTO ".$this->tblDetallesAsientoLibroDiario." (idAsiento,idDimension,valorCampo,valorInterpretacion) VALUES(@idAsiento,".$idDimension.",'".$valor."','".$valorInterpretacion."')";
								$x++;
								if(!isset($this->arrDimensionesVigentes[$idDimension]))
								{
									$consulta[$x]="INSERT INTO 592_dimensionesVigentesLibroDiario(cicloFiscal,idDimension) VALUES(".$this->cicloFiscal.",".$idDimension.")";
									$x++;
									$this->arrDimensionesVigentes[$idDimension]="";
								}
							}
							
						}
					}
				}
			
			}
			$arrAsientosPresupuestales=array();
			
			if(!isset($obj->arrAsientosPresupuestales))
			{
			
				$query="SELECT tiempoPresupuestal,'' AS etiqueta,porcentajeAfectacion,tipoAfectacion,idFuncionAplicacion,idAfectacion from 599_afectacionPresupuestalMovimiento WHERE idPerfilMovimiento=".$obj->tipoMovimiento;
				$resAfectacion=$con->obtenerFilas($query);
				while($filaAfectacion=mysql_fetch_row($resAfectacion))
				{
					$considerarMovimiento=true;
					if(($filaAfectacion[4]!="")&&($filaAfectacion[4]!="-1"))
					{
						$cadObj='{"vAmbiente":"","idAfectacion":"'.$filaAfectacion[5].'"}';
						$objParam=json_decode($cadObj);
						$objParam->vAmbiente=$obj;
						$cacheConsulta=NULL;
						$resAplicacion=resolverExpresionCalculoPHP($fFolio[1],$objParam,$cacheConsulta);
						if($resAplicacion!='1')
							$considerarMovimiento=false;
					}
					
					if($considerarMovimiento)
					{
						$objDatos=array();
						$objDatos[0]=$filaAfectacion[0];
						$objDatos[1]=$fMovimiento[2];
						$objDatos[2]=$filaAfectacion[2]/100;
						$objDatos[3]=$filaAfectacion[3];
						$objDatos[4]=($filaAfectacion[2]/100)*$obj->montoOperacion;
						array_push($arrAsientosPresupuestales,$objDatos);
					}
				}
			}
			else
				$arrAsientosPresupuestales=$obj->arrAsientosPresupuestales;
			
			if(sizeof($arrAsientosPresupuestales)>0)
			{
				foreach($arrAsientosPresupuestales as $filaAfectacion)
				{
					
					$montoOperacion=$filaAfectacion[4];
					
					$consulta[$x]="INSERT INTO 570_asientosPresupuestales(fechaMovimiento,idTiempoPresupuestal,montoOperacion,tipoOperacion,idResponsableMovimiento,descripcionMovimiento,folioMovimiento,tipoMovimiento) 
									VALUES('".date("Y-m-d H:i:s")."',".$filaAfectacion[0].",".$filaAfectacion[4].",".$filaAfectacion[3].",".$_SESSION["idUsr"].",'".cv($filaAfectacion[1])."','".$folio."',".$obj->tipoMovimiento.")";
					$x++;

					if(isset($obj->dimensiones)&(sizeof($obj->dimensiones)>0))
					{
						$consulta[$x]="set @idAsiento=(select last_insert_id())";
						$x++;
						foreach ($obj->dimensiones as $nombre=>$valor) 
						{
							
							if(isset($this->arrDimensiones[$nombre]))
							{
								$idDimension=$this->arrDimensiones[$nombre][0];
								$funcionInterpretacion=$this->arrDimensiones[$nombre][1];
								$valorInterpretacion="";
								if($funcionInterpretacion!="")
								{
									$cadObj='{"valor":""}';
									$objParam=json_decode($cadObj);
									$objParam->valor=$valor;
									
									$cacheConsulta=NULL;
									$valorInterpretacion=resolverExpresionCalculoPHP($funcionInterpretacion,$objParam,$cacheConsulta);	
								}
								
								$consulta[$x]="INSERT INTO 571_detallesAsientoPresupuestal (idAsiento,idDimension,valorCampo,valorInterpretacion) VALUES(@idAsiento,".$idDimension.",'".$valor."','".$valorInterpretacion."')";
								$x++;
								if(!isset($arrDimensionesVigentesPresupuesto[$idDimension]))
								{
									$consulta[$x]="INSERT INTO 572_dimensionesVigentesAsientoPresupuestal(idDimension) VALUES(".$idDimension.")";
									$x++;
									$arrDimensionesVigentesPresupuesto[$idDimension]="";
								}
							}
							
						}
					}
				}
			}
			$consulta[$x]="commit";
			$x++;
			return $con->ejecutarBloque($consulta);
		}
				
		function asentarArregloMovimientos($arrObj,&$consulta=null,&$ct=null,$folioUnico=false)
		{
			global $con;

			$x=0;
			if($consulta==null)
			{
				
				$consulta=array();
				$consulta[$x]="begin";
				$x++;
			}
			else
			{
				
				$x=$ct;
			}
			if(!$this->validadaBaseContable)
			{
				if(!$this->existeBaseContabilidad())
					$this->crearBaseContabilidad();
				$this->validadaBaseContable=true;
			}
			
			$folioGlobal="";
			
			
			$query="SELECT noMovimientoActual FROM 593_conteoNoMovimientoLibroDiario WHERE cicloFiscal=".$this->cicloFiscal." AND codigoInstitucion='".$_SESSION["codigoInstitucion"]."'";
			$fila=$con->obtenerPrimeraFila($query);
			if(!$fila)
			{
				$consulta[$x]="INSERT INTO 593_conteoNoMovimientoLibroDiario(noMovimientoActual,cicloFiscal,codigoInstitucion) VALUES(1,".$this->cicloFiscal.",'".$_SESSION["codigoInstitucion"]."')";
				$x++;
			}
			$consulta[$x]="set @noMovimiento:=(select  noMovimientoActual FROM 593_conteoNoMovimientoLibroDiario WHERE cicloFiscal=".$this->cicloFiscal." AND codigoInstitucion='".$_SESSION["codigoInstitucion"]."' for update)";
			$x++;

			$cacheLibro=array();

			foreach($arrObj as $obj)
			{	
				$idLibro="";
				if(!isset($cacheLibro[$obj->tipoMovimiento]))
				{
					$query="SELECT tipoOrigenLibro,idLibro,descripcionRegistroMovimiento FROM 598_perfilesMovimientos WHERE idConcepto=".$obj->tipoMovimiento;
					$fMovimiento=$con->obtenerPrimeraFila($query);
					if($fMovimiento[0]==0)
						$idLibro=$fMovimiento[1];
					else
					{
						$cadObj='{"vAmbiente":"","tipoMovimiento":"'.$obj->tipoMovimiento.'"}';
						$objParam=json_decode($cadObj);
						$objParam->vAmbiente=$obj;
						$cacheConsulta=NULL;
						$idLibro=resolverExpresionCalculoPHP($fMovimiento[1],$objParam,$cacheConsulta);
					}
					$cacheLibro[$obj->tipoMovimiento]=$idLibro;
				}
				else
				{
					$idLibro=$cacheLibro[$obj->tipoMovimiento];
				}
				
				if(!$folioUnico)
				{
					if(!isset($obj->folio)||($obj->folio==""))
						$folio=$this->obtenerFolio($obj);
					else
						$folio=$obj->folio;
				}
				else
				{
					if($folioGlobal=="")
						$folioGlobal=$this->obtenerFolio($obj);
					$folio=$folioGlobal;
				}
				
							
							
				$arrAsientos=array();
				if(!isset($obj->arrAsientos))
				{
				
					$query="SELECT cuentaAfectacion,'' AS etiqueta,porcentaje,tipoAfectacion,idFuncionAplicacion,idAfectacion from 599_afectacionContableMovimiento WHERE idPerfilMovimiento=".$obj->tipoMovimiento;
					$resAfectacion=$con->obtenerFilas($query);
					while($filaAfectacion=mysql_fetch_row($resAfectacion))
					{
						$considerarMovimiento=true;
						if(($filaAfectacion[4]!="")&&($filaAfectacion[4]!="-1"))
						{
							$cadObj='{"vAmbiente":"","idAfectacion":"'.$filaAfectacion[5].'"}';
							$objParam=json_decode($cadObj);
							$objParam->vAmbiente=$obj;
							$cacheConsulta=NULL;
							$resAplicacion=resolverExpresionCalculoPHP($fFolio[1],$objParam,$cacheConsulta);
							if($resAplicacion!='1')
								$considerarMovimiento=false;
						}
						
						if($considerarMovimiento)
						{
							$objDatos=array();
							$objDatos[0]=$filaAfectacion[0];
							$objDatos[1]=$fMovimiento[2];
							$objDatos[2]=$filaAfectacion[2];
							$objDatos[3]=$filaAfectacion[3];
							$objDatos[4]=($filaAfectacion[2]/100)*$obj->montoOperacion;
							array_push($arrAsientos,$objDatos);
						}
					}
				}
				else
					$arrAsientos=$obj->arrAsientos;
				
				foreach($arrAsientos as $filaAfectacion)
				{
					$noMovimiento=$this->obtenerNoMovimiento();
					$montoDebe=0;
					$montoHaber=0;
					if($filaAfectacion[3]==0)
						$montoDebe=$filaAfectacion[4];
					else
						$montoHaber=$filaAfectacion[4];
					
					$cuenta=$filaAfectacion[0];
					$query="SELECT CONCAT('[',codigoUnidadCta,'] ',tituloCta) FROM 510_cuentas WHERE codigoCta='".$filaAfectacion[0]."' and ciclo=".$this->cicloFiscal;
					$lblCuenta=$con->obtenerValor($query);
					
						
					$consulta[$x]=" insert into ".$this->tblAsientoLibroDiario."(idLibroDiario,noMovimiento,fechaMovimiento,codigoCuenta,montoDebeOperacion,montoHaberOperacion,tipoOperacion,idResponsableMovimiento,
								descripcionMovimiento,folioMovimiento,tipoMovimiento,cuenta) values(".$idLibro.",".$noMovimiento.",'".date("Y-m-d H:i:s")."','".$lblCuenta."',".$montoDebe.",".$montoHaber.",".$filaAfectacion[3].",
								".$_SESSION["idUsr"].",'".$filaAfectacion[1]."','".$folio."',".$obj->tipoMovimiento.",'".$cuenta."')";
					$x++;
					if(isset($obj->dimensiones)&(sizeof($obj->dimensiones)>0))
					{
						$consulta[$x]="set @idAsiento=(select last_insert_id())";
						$x++;
						foreach ($obj->dimensiones as $nombre=>$valor) 
						{
							if(isset($this->arrDimensiones[$nombre]))
							{
								$idDimension=$this->arrDimensiones[$nombre][0];
								$funcionInterpretacion=$this->arrDimensiones[$nombre][1];
								$valorInterpretacion="";
								if($funcionInterpretacion!="")
								{
									$cadObj='{"valor":""}';
									$objParam=json_decode($cadObj);
									$objParam->valor=$valor;
									
									$cacheConsulta=NULL;
									$valorInterpretacion=resolverExpresionCalculoPHP($funcionInterpretacion,$objParam,$cacheConsulta);	
								}
								
								$consulta[$x]="INSERT INTO ".$this->tblDetallesAsientoLibroDiario." (idAsiento,idDimension,valorCampo,valorInterpretacion) VALUES(@idAsiento,".$idDimension.",'".$valor."','".$valorInterpretacion."')";
								$x++;
								if(!isset($arrDimensionesVigentes[$idDimension]))
								{
									$consulta[$x]="INSERT INTO 592_dimensionesVigentesLibroDiario(cicloFiscal,idDimension) VALUES(".$this->cicloFiscal.",".$idDimension.")";
									$x++;
									$arrDimensionesVigentes[$idDimension]="";
								}
							}
							
						}
					}
				}
				
				
				$arrAsientosPresupuestales=array();
				if(!isset($obj->arrAsientosPresupuestales))
				{
				
					$query="SELECT tiempoPresupuestal,'' AS etiqueta,porcentajeAfectacion,tipoAfectacion,idFuncionAplicacion,idAfectacion from 599_afectacionPresupuestalMovimiento WHERE idPerfilMovimiento=".$obj->tipoMovimiento;
					$resAfectacion=$con->obtenerFilas($query);
					while($filaAfectacion=mysql_fetch_row($resAfectacion))
					{
						$considerarMovimiento=true;
						if(($filaAfectacion[4]!="")&&($filaAfectacion[4]!="-1"))
						{
							$cadObj='{"vAmbiente":"","idAfectacion":"'.$filaAfectacion[5].'"}';
							$objParam=json_decode($cadObj);
							$objParam->vAmbiente=$obj;
							$cacheConsulta=NULL;
							$resAplicacion=resolverExpresionCalculoPHP($fFolio[1],$objParam,$cacheConsulta);
							if($resAplicacion!='1')
								$considerarMovimiento=false;
						}
						
						if($considerarMovimiento)
						{
							$objDatos=array();
							$objDatos[0]=$filaAfectacion[0];
							$objDatos[1]=$fMovimiento[2];
							$objDatos[2]=$filaAfectacion[2];
							$objDatos[3]=$filaAfectacion[3];
							$objDatos[4]=($filaAfectacion[2]/100)*$obj->montoOperacion;
							array_push($arrAsientosPresupuestales,$objDatos);
						}
					}
				}
				else
					$arrAsientosPresupuestales=$obj->arrAsientosPresupuestales;
				
			
				$arrPerfilesPresupuestales=array();
				if(sizeof($arrAsientosPresupuestales)>0)
				{
					foreach($arrAsientosPresupuestales as $filaAfectacion)
					{
						
						if(!isset($arrPerfilesPresupuestales[$obj->tipoMovimiento]))
						{
							$query="SELECT idPerfilPresupuestario FROM 598_perfilesMovimientos WHERE idConcepto=".$obj->tipoMovimiento;
							$arrPerfilesPresupuestales[$obj->tipoMovimiento]=$con->obtenerValor($query);
						}
						$idPerfilPresupuestal=$arrPerfilesPresupuestales[$obj->tipoMovimiento];
						
						if($idPerfilPresupuestal=="")
							$idPerfilPresupuestal=-1;
						
						
						$montoOperacion=$filaAfectacion[4];
						$idFormulario="NULL";
						$idRegistro="NULL";
						if(isset($obj->idFormulario))
							$idFormulario=$obj->idFormulario;
						if(isset($obj->idRegistro))
							$idRegistro=$obj->idRegistro;
						$consulta[$x]="INSERT INTO 570_asientosPresupuestales(fechaMovimiento,idTiempoPresupuestal,montoOperacion,tipoOperacion,idResponsableMovimiento,descripcionMovimiento,folioMovimiento,tipoMovimiento,idPerfilPresupuestal,idFormulario,idRegistro) 
										VALUES('".date("Y-m-d H:i:s")."',".$filaAfectacion[0].",".$filaAfectacion[4].",".$filaAfectacion[3].",".$_SESSION["idUsr"].",'".cv($filaAfectacion[1])."','".$folio."',".$obj->tipoMovimiento.",".$idPerfilPresupuestal.",".$idFormulario.",".$idRegistro.")";
						$x++;
						$consulta[$x]="set @idAsiento=(select last_insert_id())";
						$x++;
						/*$consulta[$x]="update 570_asientosPresupuestales set noMovimiento=@idAsiento where idAsientoPresupuestal=@idAsiento";
						$x++;*/
						if(isset($obj->dimensiones)&(sizeof($obj->dimensiones)>0))
						{
							
							foreach ($obj->dimensiones as $nombre=>$valor) 
							{
								if(isset($this->arrDimensiones[$nombre]))
								{
									$idDimension=$this->arrDimensiones[$nombre][0];
									$funcionInterpretacion=$this->arrDimensiones[$nombre][1];
									$valorInterpretacion="";
									if($funcionInterpretacion!="")
									{
										$cadObj='{"valor":""}';
										$objParam=json_decode($cadObj);
										$objParam->valor=$valor;
										
										$cacheConsulta=NULL;
										$valorInterpretacion=cv(removerComillasLimite(resolverExpresionCalculoPHP($funcionInterpretacion,$objParam,$cacheConsulta)));	
									}
									
									$consulta[$x]="INSERT INTO 571_detallesAsientoPresupuestal (idAsiento,idDimension,valorCampo,valorInterpretacion) VALUES(@idAsiento,".$idDimension.",'".$valor."','".$valorInterpretacion."')";
									$x++;
									if(!isset($this->arrDimensionesVigentesPresupuesto[$idDimension]))
									{
										$consulta[$x]="INSERT INTO 572_dimensionesVigentesAsientoPresupuestal(idDimension) VALUES(".$idDimension.")";
										$x++;
										$this->arrDimensionesVigentesPresupuesto[$idDimension]="";
									}
								}
								
							}
						}
					}
				}
				
				$consulta[$x]="set @noMovimiento:=@noMovimiento+1";
				$x++;
			}
				
			$consulta[$x]="update 593_conteoNoMovimientoLibroDiario set noMovimientoActual=@noMovimiento where cicloFiscal=".$this->cicloFiscal." AND codigoInstitucion='".$_SESSION["codigoInstitucion"]."'";
			$x++;

			if($ct!=null)
			{

				$ct=$x;
			}
			else
			{
				$consulta[$x]="commit";
				$x++;

				return $con->ejecutarBloque($consulta);
			}
			return true;
		}
		
		function obtenerFolio($obj)
		{
			global $con;
			$x=0;
			$consulta="begin";
			if($con->ejecutarConsulta($consulta))
			{
				$query="SELECT tipoFolio,idFuncionSistema FROM 598_perfilesMovimientos WHERE idConcepto=".$obj->tipoMovimiento;
				$fFolio=$con->obtenerPrimeraFila($query);	
				if($fFolio[0]==0)
				{
					$consulta="SELECT prefijo,separador,longitud,incremento,fActual,idFolio FROM 597_foliosMovimientos WHERE idPerfilMovimiento=".$obj->tipoMovimiento." AND situacion=1 for update";
					$fila=$con->obtenerPrimeraFila($consulta);
					if($fila)
					{
						$consulta="update 597_foliosMovimientos set fActual=fActual+1 where idFolio=".$fila[5];
						if($con->ejecutarConsulta($consulta))
						{
							$folio=$fila[0].$fila[1].str_pad($fila[4],$fila[2],"0",STR_PAD_LEFT);
							$consulta="commit";
							if($con->ejecutarConsulta($consulta))
								return $folio;
		
						}
					}
				}
				else
				{
					$cadObj='{"vAmbiente":""}';
					$objParam=json_decode($cadObj);
					$objParam->param1=$obj;
					$cacheConsulta=NULL;
					return resolverExpresionCalculoPHP($fFolio[1],$objParam,$cacheConsulta);	
				}
			}
			return "";
		}
		
		function obtenerNoMovimiento()
		{
			global $con;
			$x=0;
			$consulta="begin";
			if($con->ejecutarConsulta($consulta))
			{
				$consulta="SELECT noMovimientoActual FROM 593_conteoNoMovimientoLibroDiario WHERE cicloFiscal=".$this->cicloFiscal." AND codigoInstitucion='".$_SESSION["codigoInstitucion"]."' for update";
				$fila=$con->obtenerPrimeraFila($consulta);
				if($fila)
				{
					$consulta="update 593_conteoNoMovimientoLibroDiario set noMovimientoActual=noMovimientoActual+1 where cicloFiscal=".$this->cicloFiscal." AND codigoInstitucion='".$_SESSION["codigoInstitucion"]."'";
					if($con->ejecutarConsulta($consulta))
						return $fila[0];
				}
				else
				{
					$consulta="INSERT INTO 593_conteoNoMovimientoLibroDiario(noMovimientoActual,cicloFiscal,codigoInstitucion) VALUES(2,".$this->cicloFiscal.",'".$_SESSION["codigoInstitucion"]."')";
					if($con->ejecutarConsulta($consulta))
						return 1;
				}
			}
			return "0";
		}
		
		function generarLibroDelDia()
		{
			global $con;
			$fecha=date("d/m/Y");
			$consulta="SELECT idLibro FROM 590_librosDiarios WHERE tituloLibro='Libro diario (".$fecha.")'";
			
			$idLibro=$con->obtenerValor($consulta);
			if($idLibro!="")
				return $idLibro;
			$consulta="insert into 590_librosDiarios(tituloLibro,descripcion) values('Libro diario (".$fecha.")','Libro diario (".$fecha.")')";
			
			if($con->ejecutarConsulta($consulta))
			{
				$idLibro=$con->obtenerUltimoID();
				$consulta="INSERT INTO 594_rolesVSLibros(idLibro,rol,permisos) values(".$idLibro.",'1_0','CRM')";
				if($con->ejecutarConsulta($consulta))
					return $idLibro;
			}
		}
		
		function obtenerSaldoPresupuesto($tPresupuestal,$dimensiones,$idPerfilPresupuestal=-1)
		{
			global $con;
			$saldo=0;
			$listAsientos="";
			$arrIdAsientos=array();
			$tDimensiones=sizeof($dimensiones);
			foreach($dimensiones as $d=>$valor)
			{
				$idDimension=-1;
				if(isset($this->arrDimensiones[$d]))
					$idDimension=$this->arrDimensiones[$d][0];
				if($idDimension!=-1)
				{
					$consulta="";
					if($idPerfilPresupuestal==-1)
						$consulta="select idAsiento FROM 571_detallesAsientoPresupuestal WHERE idDimension=".$idDimension." AND valorCampo='".$valor."'";
					else
						$consulta="select idAsiento FROM 571_detallesAsientoPresupuestal d,570_asientosPresupuestales  c WHERE c.idPerfilPresupuestal=".$idPerfilPresupuestal." and c.idAsientoPresupuestal=d.idAsiento and
								  idDimension=".$idDimension." AND valorCampo='".$valor."'";
					
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
			$consulta="SELECT SUM(montoOperacion * tipoOperacion) FROM 570_asientosPresupuestales WHERE idAsientoPresupuestal IN (".$listAsientos.") AND idTiempoPresupuestal=".$tPresupuestal;
			$saldo=$con->obtenerValor($consulta);
			if($saldo=="")
				$saldo=0;
			return $saldo;
		}
				
		function obtenerTotalPresupuestoDimensiones($dimensiones,$idPerfilPresupuestal=-1)
		{
			global $con;
			$consulta="";
			if($idPerfilPresupuestal==-1)
				$consulta="SELECT idTiempoPresupuestal FROM 524_tiemposPresupuestales WHERE situacion=1";
			else
				$consulta="SELECT idTiempoPresupuestal FROM 524_tiemposPresupuestales WHERE idPerfilPresupuestal=".$idPerfilPresupuestal." and situacion=1";
			$res=$con->obtenerFilas($consulta);
			$totalPresupuesto=0;
			while($f=mysql_fetch_row($res))
			{
				$totalPresupuesto+=obtenerSaldoPresupuesto($f[0],$dimensiones,$idPerfilPresupuestal);
			}
			return $totalPresupuesto;
		}
		
		function obtenerSituacionPresupuestoDimensiones($dimensiones,$idPerfilPresupuestal=-1)
		{
			global $con;
			$arrTiemposPresupuestales=array();
			$consulta="";
			if($idPerfilPresupuestal==-1)
				$consulta="SELECT idTiempoPresupuestal FROM 524_tiemposPresupuestales WHERE situacion=1";
			else
				$consulta="SELECT idTiempoPresupuestal FROM 524_tiemposPresupuestales WHERE idPerfilPresupuestal=".$idPerfilPresupuestal." and situacion=1";
			$res=$con->obtenerFilas($consulta);
			$totalPresupuesto=0;
			while($f=mysql_fetch_row($res))
			{
				$arrTiemposPresupuestales[$f[1]]["tiempoPresupuestal"]=$f[1];
				$arrTiemposPresupuestales[$f[1]]["saldo"]=obtenerSaldoPresupuesto($f[0],$dimensiones);
			}
			return $arrTiemposPresupuestales;
		}
		
		function existeSuficienciaPresupuestal($tPresupuestal,$dimensiones,$monto,$idPerfilPresupuestal=-1)
		{
			$saldo=$this->obtenerSaldoPresupuesto($tPresupuestal,$dimensiones,$idPerfilPresupuestal);
			if($saldo>$monto)
				return 0;
			else
				return $monto-$saldo;
		}
		
		function obtenerIdAsientoPresupuestal($dimensiones,$idPerfilPresupuestal=-1,$tPresupuestal=-1)
		{
			global $con;
			$saldo=0;
			$listAsientos="";
			$arrIdAsientos=array();
			$tDimensiones=sizeof($dimensiones);
			foreach($dimensiones as $d=>$valor)
			{
				$idDimension=-1;
				if(isset($this->arrDimensiones[$d]))
					$idDimension=$this->arrDimensiones[$d][0];
				if($idDimension!=-1)
				{
					$consulta="";
					if($idPerfilPresupuestal==-1)
					{
						if($tPresupuestal==-1)
							$consulta="select idAsiento FROM 571_detallesAsientoPresupuestal WHERE idDimension=".$idDimension." AND valorCampo='".$valor."'";
						else
						{
							$consulta="select idAsiento FROM 571_detallesAsientoPresupuestal d,570_asientosPresupuestales  c WHERE c.idTiempoPresupuestal=".$tPresupuestal." and c.idAsientoPresupuestal=d.idAsiento and
								  idDimension=".$idDimension." AND valorCampo='".$valor."'";
						}
					}
					else
					{
						$consulta="select idAsiento FROM 571_detallesAsientoPresupuestal d,570_asientosPresupuestales  c WHERE c.idPerfilPresupuestal=".$idPerfilPresupuestal." and c.idAsientoPresupuestal=d.idAsiento and
								  idDimension=".$idDimension." AND valorCampo='".$valor."'";
						if($tPresupuestal!=-1)
							$consulta.=" and c.idTiempoPresupuestal=".$tPresupuestal;
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
		
	}
	
	
	
	
	function obtenerFolioSiguiente($tipoMovimiento,$obj)
	{
		global $con;
		$x=0;
		$consulta="begin";
		if($con->ejecutarConsulta($consulta))
		{
			$query="SELECT tipoFolio,idFuncionSistema FROM 598_perfilesMovimientos WHERE idConcepto=".$tipoMovimiento;
			$fFolio=$con->obtenerPrimeraFila($query);	
			if($fFolio[0]==0)
			{
				$consulta="SELECT prefijo,separador,longitud,incremento,fActual,idFolio FROM 597_foliosMovimientos WHERE idPerfilMovimiento=".$tipoMovimiento." AND situacion=1 for update";
				$fila=$con->obtenerPrimeraFila($consulta);
				if($fila)
				{
					$consulta="update 597_foliosMovimientos set fActual=fActual+1 where idFolio=".$fila[5];
					if($con->ejecutarConsulta($consulta))
					{
						$folio=$fila[0].$fila[1].str_pad($fila[4],$fila[2],"0",STR_PAD_LEFT);
						$consulta="commit";
						if($con->ejecutarConsulta($consulta))
							return $folio;
	
					}
				}
			}
			else
			{
				$cadObj='{"vAmbiente":""}';
				$objParam=json_decode($cadObj);
				$objParam->param1=$obj;
				$cacheConsulta=NULL;
				return resolverExpresionCalculoPHP($fFolio[1],$objParam,$cacheConsulta);	
			}
		}
		return "";
	}
	
	
?>