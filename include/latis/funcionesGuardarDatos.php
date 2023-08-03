<?php	
	function llenarControlFrmDestino($idNuevoReg,$nCtrlDestino,$idElemento)
	{
		global $con;	
		$consulta="select * from 904_configuracionElemFormulario where idElemFormulario=".$idElemento;
		$filaConf=$con->obtenerPrimeraFila($consulta);
		$tabla=$filaConf[2];
		$campoID=$filaConf[4];
		$campoProy=$filaConf[3];
		$autocompletar=$filaConf[9];
		$campoTablaID=obtenerCampoIDTabla($tabla);
		$consulta="select ".$campoID.",".$campoProy." from ".$tabla." where ".$campoTablaID."=".$idNuevoReg;
		$filaOpt=$con->obtenerPrimeraFila($consulta);
		echo "window.parent.insertarOpcion('".$nCtrlDestino."','".$idNuevoReg."','".$filaOpt[1]."',".$autocompletar.");window.parent.tb_remove();return;";
	}
	
	function generarCadenasCamposGrid($objCampos,&$consulta,&$x)
	{
		global $con;
		global $guardarArchivosBD;
		global $directorioInstalacion;
		
		foreach($objCampos as $obj)
		{
			$idElemento=$obj->idElemento;
			$query="select * from 904_configuracionElemFormulario where idElemFormulario=".$idElemento;
			$filaConf=$con->obtenerPrimeraFila($query);
			$nTabla=$filaConf[4];
			$registros=$obj->objGrid;
			$listRegistros="";
			$campoID=obtenerCampoIDTabla($nTabla);
			$posTmp=$x;
			$x++;
			foreach($registros as $registro)
			{
				$reflectionClase = new ReflectionObject($registro);
				$idRegistro=$registro->idRegistro;
				$listValores="";
				$binario_tipo="application/octet-stream";
				if($idRegistro=="-1")
				{
					$listCampos="idReferencia";
					$listValores="idRegPadre";
					
					foreach ($reflectionClase->getProperties() as $property => $value) 
					{
						$nombre=$value->getName();
						$valor="'".$value->getValue($registro)."'";
						if($valor=="''")
							$valor="NULL";

						if(strpos($valor,"|")!==false)
						{
							$arrDatos=explode("|",str_replace("'","",$valor));
							$binario_nombre_temporal=$directorioInstalacion.'/archivosTemporales/'.$arrDatos[1];
							$binario_nombre=$arrDatos[0];
							$binarioPeso=filesize($binario_nombre_temporal);
							$sha512=strtoupper(hash_file ( "sha512" , $binario_nombre_temporal,false ));
							if($guardarArchivosBD)
							{
								$binario_contenido = addslashes(fread(fopen($binario_nombre_temporal, "rb"),$binarioPeso));
								
								$consulta_insertar = "insert into 908_archivos(documento,fechaCreacion,responsable,nomArchivoOriginal,tipoArchivo,tamano,enBD,sha512)
														values('".$binario_contenido."','".date('Y-m-d H:i:s')."',".$_SESSION["idUsr"].",
														'".$binario_nombre."','".$binario_tipo."','".$binarioPeso."',1,'".$sha512."')";
								if(!$con->ejecutarConsulta($consulta_insertar))
								{
									return;
									$reenviarGlobal=false;
								}
								
								$idArchivo=$con->obtenerUltimoID();
								unlink($binario_nombre_temporal);
								$valor=$idArchivo;
							}
							else
							{
								
								$consulta_insertar = "insert into 908_archivos(documento,fechaCreacion,responsable,nomArchivoOriginal,tipoArchivo,tamano,enBD,sha512)
														values(NULL,'".date('Y-m-d H:i:s')."',".$_SESSION["idUsr"].",
														'".$binario_nombre."','".$binario_tipo."','".$binarioPeso."',0,'".$sha512."')";
								if(!$con->ejecutarConsulta($consulta_insertar))
								{
									return;
									$reenviarGlobal=false;
								}
								$idArchivo=$con->obtenerUltimoID();
								
								copy($binario_nombre_temporal,$directorioInstalacion."/documentosUsr/archivo_".$idArchivo);
								unlink($binario_nombre_temporal);
								$valor=$idArchivo;
							}
						}
							
						if(($nombre!="idElemento")&&($nombre!="idRegistro")&&($nombre!="idReferencia"))
						{
							$listCampos.=",".$nombre;
							$listValores.=",".$valor;
						}
						
					}
					$consulta[$x]="insert into ".$nTabla."(".$listCampos.") values(".$listValores.")";
					$x++;
				}
				else
				{
					$consultaFila="select * from ".$nTabla." where id_".$nTabla."=".$idRegistro;
					$fRegistro=$con->obtenerPrimeraFilaAsoc($consultaFila);
					
					foreach ($reflectionClase->getProperties() as $property => $value) 
					{
						$nombre=$value->getName();
						$valor="'".$value->getValue($registro)."'";
						if($valor=="''")
							$valor="NULL";
						if(strpos($valor,"|")!==false)
						{
							$arrDatos=explode("|",str_replace("'","",$valor));
							if(strpos($arrDatos[1],"_")!==false)
							{

								$vOriginal=$fRegistro[$nombre];
								$binario_nombre_temporal=$directorioInstalacion.'/archivosTemporales/'.$arrDatos[1];
								$binario_nombre=$arrDatos[0];
								$binarioPeso=filesize($binario_nombre_temporal);
								$sha512=strtoupper(hash_file ( "sha512" , $binario_nombre_temporal,false ));
								if($guardarArchivosBD)
								{
									$binario_contenido = addslashes(fread(fopen($binario_nombre_temporal, "rb"),$binarioPeso));
									$idArchivo=$vOriginal;
									if($vOriginal=="")
									{
										$consulta_insertar = "insert into 908_archivos(documento,fechaCreacion,responsable,nomArchivoOriginal,tipoArchivo,tamano,enBD,sha512)
																values('".$binario_contenido."','".date('Y-m-d H:i:s')."',".$_SESSION["idUsr"].",
																'".$binario_nombre."','".$binario_tipo."','".$binarioPeso."',1,'".$sha512."')";
										if(!$con->ejecutarConsulta($consulta_insertar))
										{
											return;
											$reenviarGlobal=false;
										}
										$idArchivo=$con->obtenerUltimoID();
										
									}
									else
									{
										$consulta_insertar = "update 908_archivos set documento='".$binario_contenido."',fechaModif='".date('Y-m-d H:i:s')."',respModif=".$_SESSION["idUsr"].",nomArchivoOriginal='".$binario_nombre."',
															tipoArchivo='".$binario_tipo."',tamano='".$binarioPeso."',enBD=1,sha512='".$sha512."' where idArchivo=".$vOriginal;
										if(!$con->ejecutarConsulta($consulta_insertar))
										{
											return;
											$reenviarGlobal=false;
										}
										if(file_exists($directorioInstalacion.'/documentosUsr/archivo_'.$vOriginal))
											unlink($directorioInstalacion.'/documentosUsr/archivo_'.$vOriginal);
									}
									unlink($binario_nombre_temporal);
									$valor=$idArchivo;
								}
								else
								{
									$idArchivo=$vOriginal;
									if($vOriginal=="")
									{
										$consulta_insertar = "insert into 908_archivos(documento,fechaCreacion,responsable,nomArchivoOriginal,tipoArchivo,tamano,enBD,sha512)
																values(NULL,'".date('Y-m-d H:i:s')."',".$_SESSION["idUsr"].",
																'".$binario_nombre."','".$binario_tipo."','".$binarioPeso."',0,'".$sha512."')";
										if(!$con->ejecutarConsulta($consulta_insertar))
										{
											return;
											$reenviarGlobal=false;
										}
										$idArchivo=$con->obtenerUltimoID();
									}
									else
									{
										if(file_exists($directorioInstalacion.'/documentosUsr/archivo_'.$vOriginal))
											unlink($directorioInstalacion.'/documentosUsr/archivo_'.$vOriginal);
										
										$consulta_insertar = "update 908_archivos set documento=NULL,fechaModif='".date('Y-m-d H:i:s')."',respModif=".$_SESSION["idUsr"].",nomArchivoOriginal='".$binario_nombre."',
															tipoArchivo='".$binario_tipo."',tamano='".$binarioPeso."',enBD=0,sha512='".$sha512."' where idArchivo=".$vOriginal;
										if(!$con->ejecutarConsulta($consulta_insertar))
										{
											return;
											$reenviarGlobal=false;
										}
									}
									if(copy($binario_nombre_temporal,$directorioInstalacion."/documentosUsr/archivo_".$idArchivo))
										unlink($binario_nombre_temporal);
									$valor=$idArchivo;
								}
							}
							else
								$valor=$arrDatos[1];
						}	
						
							
						if(($nombre!="idElemento")&&($nombre!="idRegistro")&&($nombre!="idReferencia"))
						{
							if($listValores=="")
								$listValores=$nombre."=".$valor;
							else
								$listValores.=",".$nombre."=".$valor;
						}
					}
					
					$consulta[$x]="update ".$nTabla." set ".$listValores." where ".$campoID."=".$idRegistro;

					$x++;
					if($listRegistros=="")
						$listRegistros=$idRegistro;
					else
						$listRegistros.=",".$idRegistro;
						
				}
			}
			if($listRegistros=="")
				$listRegistros="-1";
			$consulta[$posTmp]="delete from ".$nTabla." where ".$campoID."  not in (".$listRegistros.") and idReferencia=idRegPadre";
			
		}
		//$x--;
		
	}
	
	function vincularVacantePerfil($idVacante,$idRegistro,$query)
	{
		global $con;
		$consulta="update 667_puestosVacantes set idRegistroPerfil=".$idRegistro." where idVacante=".$idVacante;
		if($con->ejecutarConsulta($consulta))	
		{
			$consulta=bD($query)	;
			$arrFilas=$con->obtenerFilasArreglo($consulta);
			echo "window.parent.opener.recargarGridVacantes(".$arrFilas.");";
		}
	}
	
	function vicularGradoAnterior($idGrado)
	{
		global $con;
		$consulta="select idGradoAnterior from 4501_Grado where idGrado=".$idGrado;
		$idGradoAnterior=$con->obtenerValor($consulta);
		$x=0;
		$query[$x]="begin";
		$x++;
		$query[$x]="update 4501_Grado set idGradoSiguiente=-1 where idGradoSiguiente=".$idGrado;
		$x++;
		$query[$x]="update 4501_Grado set idGradoSiguiente=".$idGrado." where idGrado=".$idGradoAnterior;
		$x++;
		$query[$x]="commit";
		$x++;		
		return $con->ejecutarBloque($query);
	}
	
	function verificarRespuestasNOContesto($idFormulario,$idRegistro)
	{
		global $con;
		$consulta="SELECT COUNT(*) FROM _363_asignaturasEncuesta05 WHERE idPadre=".$idRegistro." AND idOpcion=13";
		$nReg=$con->obtenerValor($consulta);
		if($nReg>0)
		{
			$consulta="delete FROM _363_asignaturasEncuesta05 WHERE idPadre=".$idRegistro." AND idOpcion<>13";
			$con->ejecutarConsulta($consulta);
		}
		
		$consulta="SELECT COUNT(*) FROM _363_informoComponentesTaller WHERE idPadre=".$idRegistro." AND idOpcion=5";
		$nReg=$con->obtenerValor($consulta);
		if($nReg>0)
		{
			$consulta="delete FROM _363_informoComponentesTaller WHERE idPadre=".$idRegistro." AND idOpcion<>5";
			$con->ejecutarConsulta($consulta);
		}
		
		$consulta="SELECT COUNT(*) FROM _363_paraQueUtilizaMateriales WHERE idPadre=".$idRegistro." AND idOpcion=9";
		$nReg=$con->obtenerValor($consulta);
		if($nReg>0)
		{
			$consulta="delete FROM _363_paraQueUtilizaMateriales WHERE idPadre=".$idRegistro." AND idOpcion<>9";
			return $con->ejecutarConsulta($consulta);
		}
		return true;
	}
	
	function asociarRolesConexion($id,$cadObjArr)
	{
		global $con;
		$x=0;
		$consulta[$x]="begin";
		$x++;
		$consulta[$x]="DELETE FROM 252_rolesVSConexiones WHERE idConexion=".$id;
		$x++;
		
		$objArr=json_decode(bD($cadObjArr));
		foreach($objArr->arrElementos as $e)
		{
			$consulta[$x]="INSERT INTO 252_rolesVSConexiones(idRol,idConexion)  VALUES('".$e->idRol."',".$id.")";

			$x++;
		}
		$consulta[$x]="commit";
		$x++;
		return $con->ejecutarBloque($consulta);
	}
	
	function asociarElementosPeriodicidad($idRegistro,$cadObj)
	{
		global $con;

		$obj=json_decode(bD($cadObj));

		$x=0;
		$consulta[$x]="begin";
		$x++;
		$consulta[$x]="DELETE FROM 678_elementosTipoPagoNomina WHERE idTipoPago=".$idRegistro;
		$x++;
		foreach($obj->arrElementos as $e)
		{
			if($e->diaInicio=="")
				$e->diaInicio="NULL";
			if($e->mesInicio=="")
				$e->mesInicio="NULL";
			$consulta[$x]="INSERT INTO 678_elementosTipoPagoNomina(nombreElemento,noOrdinal,diaInicio,mesInicio,idTipoPago)
						VALUES('".$e->tituloElemento."',".$e->cveElemento.",".$e->diaInicio.",".$e->mesInicio.",".$idRegistro.")";
			$x++;						
		}
		
		$consulta[$x]="commit";
		$x++;
		return $con->ejecutarBloque($consulta);
	}
	
	function asociarEntidadConceptosBusqueda($idRegistro,$cadObj)
	{
		global $con;

		$obj=json_decode(bD($cadObj));

		$x=0;
		$consulta[$x]="begin";
		$x++;
		$consulta[$x]="DELETE FROM 680_funcionesBusqueda WHERE idEntidad=".$idRegistro;
		$x++;
		foreach($obj->arrConceptos as $e)
		{
			
			$consulta[$x]="INSERT INTO 680_funcionesBusqueda(idFuncionBusqueda,idEntidad)
						VALUES(".$e->idCategoria.",".$idRegistro.")";
			$x++;						
		}
		
		$consulta[$x]="commit";
		$x++;
		return $con->ejecutarBloque($consulta);
	}
	
	function asociarRolesCategoriasOrganigrama($idRegistro,$cadObj)
	{
		global $con;

		$obj=json_decode(bD($cadObj));

		$x=0;
		$consulta[$x]="begin";
		$x++;
		$consulta[$x]="DELETE FROM 817_rolesVSCategoriasOrganigrama WHERE idCategoria=".$idRegistro;
		$x++;
		foreach($obj->arrRoles as $e)
		{
			
			$consulta[$x]="INSERT INTO 817_rolesVSCategoriasOrganigrama(idCategoria,rol) VALUES(".$idRegistro.",'".$e->idRol."')";
			$x++;						
		}
		
		$consulta[$x]="commit";
		$x++;
		return $con->ejecutarBloque($consulta);
	}
	
	function asociarCursoFecha($idFormulario,$idRegistro)
	{
		global $con;
		
		$consulta="UPDATE _337_tablaDinamica SET cursos=idReferencia WHERE id__337_tablaDinamica=".$idRegistro;
		return $con->ejecutarConsulta($consulta);
	}
	
	function asociarPagosPlanPagos($idRegistro,$cadObj)
	{
		global $con;

		$obj=json_decode(bD($cadObj));

		$x=0;
		$consulta[$x]="begin";
		$x++;
		$consulta[$x]="DELETE FROM 6021_desglocePlanPagos WHERE idPlanPagos=".$idRegistro;
		$x++;
		foreach($obj->arrPagos as $e)
		{
			
			$consulta[$x]="INSERT INTO 6021_desglocePlanPagos(etiquetaPago,noPagosAgrupa,noPago,idPlanPagos)
						VALUES('".$e->etiquetaPago."',".$e->noPagosAgrupa.",".$e->noPago.",".$idRegistro.")";
			
			$x++;						
		}
		
		$consulta[$x]="commit";
		$x++;
		return $con->ejecutarBloque($consulta);
	}
	
	function asociarTiemposPerfilPresupuestal($idRegistro,$cadObj)
	{
		global $con;
		$reasignarTSuficiencia=false;
		$query="SELECT tiempoSuficiencia FROM 524_perfilesPresupuestales WHERE idPerfilPresupuestal=".$idRegistro;
		$tSuficiencia=$con->obtenerValor($query);
		if($tSuficiencia<0)
		{
			$tSuficiencia*=-1;
			$tSuficiencia-=10;
			$reasignarTSuficiencia=true;
		}
		$obj=json_decode(bD($cadObj));
		
		$x=0;
		$consulta[$x]="begin";
		$x++;
		$consulta[$x]="DELETE FROM 524_tiemposPresupuestales WHERE idPerfilPresupuestal=".$idRegistro;
		$x++;
		foreach($obj->arrTiemposPresupuestal as $t)
		{
			if($t->idTiempoPresupuestal!=-1)
			{
				$consulta[$x]="INSERT INTO 524_tiemposPresupuestales(idTiempoPresupuestal,nombreTiempo,idPerfilPresupuestal,situacion,orden)
							VALUES(".$t->idTiempoPresupuestal.",'".cv($t->tiempoPresupuestal)."',".$idRegistro.",1,".$t->orden.")";
				
				$x++;	
			}
			else
			{
				$consulta[$x]="INSERT INTO 524_tiemposPresupuestales(nombreTiempo,idPerfilPresupuestal,situacion,orden)
							VALUES('".cv($t->tiempoPresupuestal)."',".$idRegistro.",1,".$t->orden.")";
				
				$x++;
			}
		}
		
		if($reasignarTSuficiencia)
		{
			$consulta[$x]="update 524_perfilesPresupuestales p set tiempoSuficiencia=(SELECT idTiempoPresupuestal FROM 524_tiemposPresupuestales WHERE 
						idPerfilPresupuestal=p.idPerfilPresupuestal ORDER BY idTiempoPresupuestal LIMIT 0,1 ) where idPerfilPresupuestal=".$idRegistro;
			$x++;
		}
		
		$consulta[$x]="DELETE FROM 524_perfilPresupuestalVSDimensiones WHERE idPerfilPresupuestal=".$idRegistro;
		$x++;
		foreach($obj->arrDimensiones as $t)
		{
			$consulta[$x]="INSERT INTO 524_perfilPresupuestalVSDimensiones(idPerfilPresupuestal,idDimension,orden) VALUES(".$idRegistro.",".$t->idDimension.",".$t->orden.")";
			$x++;
		}
		
		$consulta[$x]="commit";
		$x++;
		return $con->ejecutarBloque($consulta);
		
	}
	
	function asociarSeccionesProducto($idRegistro,$cadObj,$accion)
	{
		global $con;
		$obj=json_decode(bD($cadObj));
		$x=0;
		$consulta[$x]="begin";
		$x++;
		$consulta[$x]="DELETE FROM 6922_seccionesCategoriasProducto WHERE idCategoriaProducto=".$idRegistro;
		$x++;
		foreach($obj->arrSecciones as $o)
		{
			$consulta[$x]="INSERT INTO 6922_seccionesCategoriasProducto(idCategoriaProducto,idFormulario,orden) VALUES(".$idRegistro.",".$o->idFormulario.",".$o->orden.")";
			$x++;
		}
		
		
		$consulta[$x]="DELETE FROM 6908_dimensionesCategoriasProducto WHERE idCategoria=".$idRegistro;
		$x++;
		foreach($obj->arrDimensiones as $o)
		{
			$consulta[$x]="INSERT INTO 6908_dimensionesCategoriasProducto(idCategoria,idDimension,orden,etiqueta,galeriaImagen) VALUES(".$idRegistro.",".$o->idDimension.",".$o->orden.",'".cv($o->etiqueta)."',".$o->permiteGaleria.")";
			$x++;
		}
		
		if($accion==1)
		{
			$query="select llave from 6906_categoriasProducto  where idCategoriaProducto=".$idRegistro;	
			$llave=$con->obtenerValor($query);
			if($llave=="")
			{
				$llave=$idRegistro;
			}
			else
				$llave.=".".$idRegistro;
			$consulta[$x]="update 6906_categoriasProducto set llave='".$llave."' where idCategoriaProducto=".$idRegistro;	
				
			$x++;
		}
		
		$consulta[$x]="commit";
		$x++;
		return $con->ejecutarBloque($consulta);
	}
	
	function asociarLocalidadesZona($idRegistro,$cadObj)
	{
		global $con;
		$obj=json_decode(bD($cadObj));
		$x=0;
		$consulta[$x]="begin";
		$x++;
		$consulta[$x]="DELETE FROM 827_localidadesVSRegiones WHERE idRegion=".$idRegistro;
		$x++;
		foreach($obj->arrLocalidades as $o)
		{
			$consulta[$x]="INSERT INTO 827_localidadesVSRegiones(idRegion,cveLocalidad) VALUES(".$idRegistro.",'".$o->cveLocalidad."')";
			$x++;
		}
		$consulta[$x]="commit";
		$x++;
		return $con->ejecutarBloque($consulta);
		
	}
	
	function asociarFuncionesCaja($idPerfil,$cadObj)
	{
		global $con;
		$obj=json_decode(bD($cadObj));
		$x=0;
		$query[$x]="DELETE FROM 6023_funcionesCaja WHERE idPerfilCaja=".$idPerfil;
		$x++;
		if(sizeof($obj->arrFuncionBusqueda)>0)
		{
			foreach($obj->arrFuncionBusqueda as $f)
			{
				$query[$x]="INSERT INTO 6023_funcionesCaja(idPerfilCaja,etiqueta,idFuncionOperacion,teclaAcceso,tipoFuncion,valorComplementario1,valorComplementario2,vDefault)
							VALUES(".$idPerfil.",'".cv($f->etiqueta)."',".$f->idFuncionOperacion.",".$f->teclaAcceso.",1,'".$f->valorComplementario1."','".$f->valorComplementario2."',".$f->default.")";
				$x++;
			}
		}
		
		if(sizeof($obj->arrFuncionCierre)>0)
		{
			foreach($obj->arrFuncionCierre as $f)
			{
				$query[$x]="INSERT INTO 6023_funcionesCaja(idPerfilCaja,etiqueta,idFuncionOperacion,teclaAcceso,tipoFuncion,valorComplementario1,valorComplementario2)
							VALUES(".$idPerfil.",'".cv($f->etiqueta)."',".$f->idFuncionOperacion.",".$f->teclaAcceso.",2,".$f->valorComplementario1.",".$f->ordenAplicacion.")";
				$x++;
			}
		}
		
		$query[$x]="DELETE FROM 6006_tiposClientePerfilCaja WHERE idPerfilCaja=".$idPerfil;
		$x++;
		if(sizeof($obj->arrTiposClientes)>0)
		{
			foreach($obj->arrTiposClientes as $t)
			{
				$arrFormaPago=explode(",",$t->formasPago);
				foreach($arrFormaPago as $f)
				{
					$query[$x]="INSERT INTO 6006_tiposClientePerfilCaja(idPerfilCaja,idTipoCliente,formaPago)
								VALUES(".$idPerfil.",".$t->idTipoCliente.",".$f.")";
					$x++;
				}
			}
		}

		
		return $con->ejecutarBloque($query);
			
	}
	
	function asociarPuntosRuta($idRuta,$cadObj)
	{
		global $con;
		$obj=json_decode(bD($cadObj));
		$x=0;
		$query[$x]="begin";
		$x++;
		$query[$x]="DELETE FROM 3101_puntosRecorridoRuta WHERE idRuta=".$idRuta;
		$x++;
		
		if(sizeof($obj->arrPuntos)>0)
		{
			foreach($obj->arrPuntos as $p)
			{
				if($p->tiempoRecorrido=="")
					$p->tiempoRecorrido=0;
				if($p->distancia=="")
					$p->distancia=0;
				$query[$x]="INSERT INTO 3101_puntosRecorridoRuta(puntoOrigen,puntoDestino,distancia,tipoPunto,idRuta,orden,tiempoRecorrido) 
							values(".$p->puntoOrigen.",".$p->puntoDestino.",".$p->distancia.",".$p->tipoPunto.",".$idRuta.",".$p->orden.",".$p->tiempoRecorrido.")";
				$x++;
			}
		}
		
		$query[$x]="commit";
		$x++;
		return $con->ejecutarBloque($query);
			
	}
	
	
	function asociarContactoEmpresa($idEmpresa,$cadObj)
	{
		global $con;
		$consulta="SELECT idContacto FROM 6929_contactoEmpresa WHERE idEmpresa=".$idEmpresa;
		$listContactos=$con->obtenerListaValores($consulta);
		if($listContactos=="")
			$listContactos=-1;

		$obj=json_decode(bD($cadObj));
		
		
		$x=0;
		$query[$x]="begin";
		$x++;
		
		$consulta="SELECT COUNT(*) FROM 715_situacionEmpresasTimbrado WHERE idEmpresa=".$idEmpresa;
		$nReg=$con->obtenerValor($consulta);
		if($nReg==0)
		{
			$query[$x]="INSERT INTO 715_situacionEmpresasTimbrado(idEmpresa,situacion) VALUES(".$idEmpresa.",0)";
			$x++;
		}

		
		$query[$x]="DELETE FROM 6927_categoriaEmpresa WHERE idEmpresa=".$idEmpresa;
		$x++;
		$query[$x]="DELETE FROM 6929_contactoEmpresa WHERE idEmpresa=".$idEmpresa;
		$x++;
		$query[$x]="DELETE FROM 6929_emailContacto WHERE idContacto in (".$listContactos.")";
		$x++;
		$query[$x]="DELETE FROM 6929_telefonoContacto WHERE idContacto in (".$listContactos.")";
		$x++;
		
		foreach($obj->arrCategoria as $c)
		{
			$query[$x]="INSERT INTO 6927_categoriaEmpresa(idEmpresa,idCategoria) VALUES(".$idEmpresa.",".$c->idCategoria.")";
			$x++;
		}
		
		if(sizeof($obj->arrContactos)>0)
		{
			foreach($obj->arrContactos as $c)
			{

				$cObj=bD($c->objContacto);

				$cObj=str_replace("\'","'",$cObj);
				$o=json_decode($cObj);

				$query[$x]="INSERT INTO 6929_contactoEmpresa(nombreContacto,departamento,puesto,apPaterno,apMaterno,idEmpresa,idContactoAux,destinatarioFactura)
							VALUES('".cv(str_replace('\"','"',urldecode($o->nombre)))."','".cv(str_replace('\"','"',urldecode($o->departamento)))."','".cv(str_replace('\"','"',urldecode($o->puesto))).
							"','".cv(str_replace('\"','"',urldecode($o->apPaterno)))."','".cv(urldecode(str_replace('\"','"',$o->apMaterno)))."',".$idEmpresa.",'".$o->idContacto."',0)";
				$x++;
				$query[$x]="set @idContacto=(select last_insert_id())";
				$x++;
				foreach($o->arrMail as $mail)
				{

					$query[$x]="INSERT INTO 6929_emailContacto(idContacto,mail) VALUES(@idContacto,'".$mail->email."')";
					$x++;

				}
				
				foreach($o->telefono as $oTel)
				{
					$query[$x]="INSERT INTO 6929_telefonoContacto(idContacto,tipo,lada,telefono,extension)
								VALUES(@idContacto,".$oTel->tipo.",'".$oTel->lada."','".$oTel->telefono."','".$oTel->extension."')";
					$x++;

				}
			}
		}
		if(sizeof($obj->arrDestinatarios)>0)
		{
			foreach($obj->arrDestinatarios as $d)
			{
				$query[$x]	="UPDATE 6929_contactoEmpresa SET destinatarioFactura=1 WHERE idEmpresa=".$idEmpresa." AND idContactoAux='".$d->idContacto."'";
				$x++;
			}
		}
		
		$listRegistroPatronal="";
		
		
		foreach($obj->arrRegistroPatronal as $d)
		{
			if($d->idRegistroPatronal!=-1)
			{
				if($listRegistroPatronal=="")
					$listRegistroPatronal=$d->idRegistroPatronal;
				else
					$listRegistroPatronal.=",".$d->idRegistroPatronal;
			}
		}
		
		
		if($listRegistroPatronal=="")
			$listRegistroPatronal=-1;
		
		
		
		
		$query[$x]="delete from  6927_empresaRegistroPatronal where idEmpresa=".$idEmpresa." and idRegistro not in (".$listRegistroPatronal.")";
		$x++;
		
		if(sizeof($obj->arrRegistroPatronal)>0)
		{
			foreach($obj->arrRegistroPatronal as $d)
			{
				if($d->idRegistroPatronal==-1)
				{
					$query[$x]="INSERT INTO 6927_empresaRegistroPatronal(idEmpresa,registroPatronal) VALUES(".$idEmpresa.",'".$d->registroPatronal."')";
					$x++;
					
				}
				else
				{
					$query[$x]="update 6927_empresaRegistroPatronal set registroPatronal='".$d->registroPatronal."' where idRegistro=".$d->idRegistroPatronal;
					$x++;
					
				}
				
			}
		}
		
		
		
		$query[$x]="DELETE FROM 6927_datosBuzonRecepcionComprobantes WHERE idEmpresa=".$idEmpresa;
		$x++;
		
		if($obj->habilitarBuzonRecepcion==1)
		{
			$query[$x]="INSERT INTO 6927_datosBuzonRecepcionComprobantes(urlServidor,puertoConexion,utilizarSSL,usuario,contrasena,directoriosBusqueda,idEmpresa,moverCorreosProcesados)
						VALUES('".cv($obj->configuracionBuzonRecepcion->urlServidorEmail)."',".cv($obj->configuracionBuzonRecepcion->puertoConexion).",".cv($obj->configuracionBuzonRecepcion->utilizarSSL).
						",'".cv($obj->configuracionBuzonRecepcion->emailConexion)."','".cv($obj->configuracionBuzonRecepcion->passwdConexion)."','".
						cv($obj->configuracionBuzonRecepcion->directoriosBusqueda)."',".$idEmpresa.",'".cv($obj->configuracionBuzonRecepcion->moverCorreosProcesados)."')";

			$x++;
		}
		
		$query[$x]="commit";
		$x++;
		
		return $con->ejecutarBloque($query);
	}
	
	function asociarDatosEmpleado($idEmpleado,$cadObj)
	{
		global $con;
		
		$obj=json_decode(bD($cadObj));
		$x=0;
		$query[$x]="begin";
		$x++;
		
		$query[$x]="DELETE FROM 694_emailContactoEmpleadoV2 WHERE idEmpleado=".$idEmpleado;
		$x++;
		$query[$x]="DELETE FROM 695_telefonoContactoEmpleadoV2 WHERE idEmpleado=".$idEmpleado;
		$x++;
		$query[$x]="DELETE FROM 696_percepcionesEmpleadoV2 WHERE idEmpleado=".$idEmpleado;
		$x++;
		$query[$x]="DELETE FROM 697_deduccionesEmpleadoV2 WHERE idEmpleado=".$idEmpleado;
		$x++;
		$query[$x]="DELETE FROM 714_empleadosConceptoBase WHERE idEmpleado=".$idEmpleado;
		$x++;
		
		
		if(sizeof($obj->arrMail)>0)
		{
			
			
			foreach($obj->arrMail as $mail)
			{
	
				$query[$x]="INSERT INTO 694_emailContactoEmpleadoV2(idEmpleado,mail) VALUES(".$idEmpleado.",'".$mail->mail."')";
				$x++;
	
			}
		}
		
		if(sizeof($obj->arrTelefonos)>0)
		{

			foreach($obj->arrTelefonos as $oTel)
			{
				$query[$x]="INSERT INTO 695_telefonoContactoEmpleadoV2(idEmpleado,tipo,lada,telefono,extension)
							VALUES(".$idEmpleado.",".$oTel->tipo.",'".$oTel->lada."','".$oTel->telefono."','".$oTel->extension."')";
				$x++;
	
			}
			
		}
		
		if(sizeof($obj->arrPercepciones)>0)
		{

			foreach($obj->arrPercepciones as $c)
			{
				$query[$x]="INSERT INTO 696_percepcionesEmpleadoV2(tipoPercepcion,clave,descripcion,importeGravado,importeExento,idEmpleado) VALUES(".
							$c->tipoPercepcion.",'".$c->clave."','".cv($c->descripcion)."',".$c->importeGravado.",".$c->importeExento.",".$idEmpleado.")";
				$x++;
	
			}
			
		}
		
		if(sizeof($obj->arrDeducciones)>0)
		{

			foreach($obj->arrDeducciones as $c)
			{
				$query[$x]="INSERT INTO 697_deduccionesEmpleadoV2(tipoDeduccion,clave,descripcion,importeGravado,importeExento,idEmpleado) VALUES(".
							$c->tipoDeduccion.",'".$c->clave."','".cv($c->descripcion)."',".$c->importeGravado.",".$c->importeExento.",".$idEmpleado.")";
				$x++;
	
			}
			
		}
		
		if(sizeof($obj->arrConceptosBase)>0)
		{

			foreach($obj->arrConceptosBase as $c)
			{
				$query[$x]="INSERT INTO 714_empleadosConceptoBase(idEmpleado,idConcepto,valor) VALUES(".$idEmpleado.",".$c->idConcepto.",".$c->valor.")";
				$x++;
	
			}
			
		}
		
		$query[$x]="commit";
		$x++;
		
		return $con->ejecutarBloque($query);
	}
	
	
	function asociarAplicacionFormaPago($idRegistro,$tiposUso)
	{
		global $con;
		$x=0;
		$query[$x]="begin";
		$x++;
		$query[$x]="DELETE FROM 601_aplicacionesFormaPago WHERE idFormaPago=".$idRegistro;
		$x++;
		$arrTiposUso=explode(",",bD($tiposUso));
		if(sizeof($arrTiposUso)>0)
		{
			foreach($arrTiposUso as $tipoUso)	
			{
				$query[$x]="INSERT INTO 601_aplicacionesFormaPago(idFormaPago,idTipoUso) VALUES(".$idRegistro.",".$tipoUso.")";
				$x++;
			}
			
		}
		$query[$x]="commit";
		$x++;
		
		return $con->ejecutarBloque($query);
	}
	
	function asociarLineasAccionPoblacion($idRegistro,$cadObj)
	{
		global $con;
		$cObj=bD($cadObj);
		$obj=json_decode($cObj);
	
		$x=0;		
		$consulta[$x]="begin";
		$x++;
		$consulta[$x]="DELETE FROM _485_lineaAccionEstado WHERE idRegistroLineaAccion IN (SELECT idRegistro FROM _485_lineasAccionPoblacion WHERE idPerfil=".$idRegistro.")";
		$x++;
		$consulta[$x]="DELETE FROM _485_lineasAccionPoblacion WHERE idPerfil=".$idRegistro;
		$x++;
		foreach($obj->registros as $r)
		{
			$consulta[$x]="INSERT INTO _485_lineasAccionPoblacion(idPerfil,idPoblacion,estrategia,linea,sublinea,financiador,monto,anio) 
						VALUES(".$idRegistro.",".$r->idPoblacion.",".$r->estrategia.",".$r->linea.",".$r->sublinea.",'".cv($r->financiador)."',".$r->monto.",".$r->anio.")";
			$x++;
			
			
			$consulta[$x]="set @idRegistro:=(select last_insert_id())";
			$x++;
			
			$arrEstados=explode(",",$r->estado);
			foreach($arrEstados as $e)
			{
				$consulta[$x]="INSERT INTO _485_lineaAccionEstado(idRegistroLineaAccion,estado) VALUES(@idRegistro,'".$e."')";
				$x++;
			}
			
		}
		$consulta[$x]="commit";
		$x++;
		
		return $con->ejecutarBloque($consulta);
		
	}
	
	function asociarRolesTableroControl($idRegistro,$cadRoles)
	{
		global $con;
		$cadRoles=bD($cadRoles);
		$oRoles=json_decode($cadRoles);
		
		
		$x=0;
		$query[$x]="begin";
		$x++;
		$query[$x]="DELETE FROM 9064_rolesTableroControl WHERE idTableroControl=".$idRegistro;
		$x++;
		
		foreach($oRoles as $rol)
		{
			$query[$x]="INSERT INTO 9064_rolesTableroControl(idTableroControl,rol)VALUES(".$idRegistro.",'".$rol->idRol."')";
			$x++;
			
		}
		
		$nombreTabla="9060_tableroControl_".$idRegistro;
		if(!$con->existeTabla($nombreTabla))
		{
			$consulta="SELECT GROUP_CONCAT(CONCAT(nombreCampo,' ',definicion)) FROM 9070_camposDefaultCreacionTablas WHERE idTipoTablas=1 ORDER BY orden";
			$definicionCampos=$con->obtenerValor($consulta);
			
			$query[$x]="create table ".$nombreTabla."(".$definicionCampos.",	PRIMARY KEY(idRegistro)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci";
												
			$x++;
			$query[$x]="CREATE INDEX idxUsuarioDestinatarioFechaLimite ON ".$nombreTabla."(idUsuarioDestinatario,idEstado,fechaLimiteAtencion)";
			$x++;
			$query[$x]="CREATE INDEX idxUsuarioDestinatarioFechaAsignacion ON ".$nombreTabla."(idUsuarioDestinatario,idEstado,fechaAsignacion)";
			$x++;
			$query[$x]="CREATE INDEX idxNotificacionRepetida ON ".$nombreTabla."(idUsuarioDestinatario,idNotificacion,iFormulario,iRegistro,usuarioDestinatario);";
			$x++;
			$query[$x]="CREATE INDEX idxLlaveTarea ON ".$nombreTabla."(llaveTarea,idUsuarioDestinatario);";
			$x++;
		}
		
		$query[$x]="commit";
		$x++;
		
		return $con->ejecutarBloque($query);
	}
	
	function cerrarSesionFormulario()
	{
		unset($_SESSION["idUsr"]);
		echo "location.href='../principalCensida/registroActualizado.php';return;";
	}
	
	function registrarDatosAsignacionHabitaciones($idRegistro,$cadObj)
	{
		global $con;
		$cObj=bD($cadObj);
		$obj=json_decode($cObj);
		$x=0;
		$query[$x]="begin";
		$x++;
		$query[$x]="DELETE FROM _670_asignacionHabitaciones WHERE iFormulario=".$obj->idFormulario." AND iReferencia=".$idRegistro;
		$x++;
		foreach($obj->arrHabitaciones as $h)
		{
			$query[$x]="INSERT INTO _670_asignacionHabitaciones(iFormulario,iReferencia,idHabitacion,costoPorNoche,precioPreferente,comentariosAdicionales,adultos,ninos)
						VALUES(".$obj->idFormulario.",".$idRegistro.",".$h->idHabitacion.",".$h->costoPorNoche.",".$h->precioPreferente.
						",'".cv($h->comentariosAdicionales)."',".$h->totalAdultos.",".$h->totalNinos.")";
			$x++;
		}
		$query[$x]="commit";
		$x++;
		
		return $con->ejecutarBloque($query);
		
		
	}
	
	function asignarClienteRegistro($idRegistro)
	{
		global $con;
		$consulta="SELECT nombre,apPaterno,apMaterno FROM _655_tablaDinamica WHERE id__655_tablaDinamica=".$idRegistro;
		$fRegistro=$con->obtenerPrimeraFila($consulta);
		$nombre=$fRegistro[0]." ".$fRegistro[1]." ".$fRegistro[2];
		echo 'window.parent.parent.asignarClienteComboH('.$idRegistro.',\''.cv($nombre).'\');return;';
		return true;
	}
?>