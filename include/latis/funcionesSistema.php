<?php include_once("latis/funcionesNeotrai.php");

function publicarConvocatoriaCicloEscolar($idFormulario,$idRegistro,$idReporte)
{
  //global $urlSitio;
  global $con;
  $tabla="_".$idFormulario."_tablaDinamica";
  $idTabla="id__".$idFormulario."_tablaDinamica";
  $posCodigo=$con->existeCampo("ciclo",$tabla);
  $consulta="SELECT fechaIniPublicacion,fechaFinPublicacion FROM 9042_formularioVSFechasConvocatoria 
  				WHERE idFormulario=".$idFormulario." AND idReferencia=".$idRegistro;
  $filasFechas=$con->obtenerPrimeraFila($consulta);
  $fechaIni="NULL";
  $fechaFin="NULL";
  if($filasFechas)
  {
	  $fechaIni="'".$filasFechas[0]."'";
	  $fechaFin="'".$filasFechas[1]."'";
		  
  }		
  $idProceso=obtenerIdProcesoFormulario($idFormulario);
  $consulta="SELECT vincularProcesoRegistroEnLinea FROM 9044_datosConvocatoria WHERE idFormulario=".$idFormulario." AND idReferencia=".$idRegistro;
  $idProcesoRegistro=$con->obtenerValor($consulta);
  if($idProcesoRegistro=="")
  {
	  $consulta="select valoresDefault FROM 4001_procesos WHERE idProceso=".$idProceso;
	  $cadObj=$con->obtenerValor($consulta);
	  $idProcesoConv="-1";
	  if($cadObj!="")
	  {
		  $obj=json_decode($cadObj);
		  if(isset($obj->idProcesoReg))
			  $idProcesoRegistro=$obj->idProcesoReg;
		  else
			  $idProcesoRegistro="-1";
	  }
	  else
		  $idProcesoRegistro="-1";
  }
  
 if($posCodigo)
 {
	$consulCiclo="select ciclo from ".$tabla." where ".$idTabla."='".$idRegistro."'";
	$idCicloEscolar=$con->obtenerValor($consulCiclo);
	
	  $consulta="INSERT INTO 9118_convocatoriasPublicadas(idFormulario,idRegistro,idReporte,fechaActivacion,fechaIniPublica,fechaFinPublica,
  			status,idProcesoRegistro,ciclo) VALUES(".$idFormulario.",".$idRegistro.",".$idReporte.",'".date('Y-m-d')."',".$fechaIni.",
 			 ".$fechaFin.",1,".$idProcesoRegistro.",".$idCicloEscolar.")";
  		return $con->ejecutarConsulta($consulta);		
 }
 else
 {
	  $consulta="INSERT INTO 9118_convocatoriasPublicadas(idFormulario,idRegistro,idReporte,fechaActivacion,fechaIniPublica,fechaFinPublica,
  			status,idProcesoRegistro) VALUES(".$idFormulario.",".$idRegistro.",".$idReporte.",'".date('Y-m-d')."',".$fechaIni.",
 			 ".$fechaFin.",1,".$idProcesoRegistro.")";
  		return $con->ejecutarConsulta($consulta);		
 }
}

function publicarConvocatoria($idFormulario,$idRegistro,$idReporte)
{
  //global $urlSitio;
  global $con;
  $consulta="SELECT fechaIniPublicacion,fechaFinPublicacion FROM 9042_formularioVSFechasConvocatoria 
  				WHERE idFormulario=".$idFormulario." AND idReferencia=".$idRegistro;
  $filasFechas=$con->obtenerPrimeraFila($consulta);
  $fechaIni="NULL";
  $fechaFin="NULL";
  if($filasFechas)
  {
	  $fechaIni="'".$filasFechas[0]."'";
	  $fechaFin="'".$filasFechas[1]."'";
		  
  }		
  $idProceso=obtenerIdProcesoFormulario($idFormulario);
  $consulta="SELECT vincularProcesoRegistroEnLinea FROM 9044_datosConvocatoria WHERE idFormulario=".$idFormulario." AND idReferencia=".$idRegistro;
  $idProcesoRegistro=$con->obtenerValor($consulta);
  if($idProcesoRegistro=="")
  {
	  $consulta="select valoresDefault FROM 4001_procesos WHERE idProceso=".$idProceso;
	  $cadObj=$con->obtenerValor($consulta);
	  $idProcesoConv="-1";
	  if($cadObj!="")
	  {
		  $obj=json_decode($cadObj);
		  if(isset($obj->idProcesoReg))
			  $idProcesoRegistro=$obj->idProcesoReg;
		  else
			  $idProcesoRegistro="-1";
	  }
	  else
		  $idProcesoRegistro="-1";
  }
  
  $consulta="INSERT INTO 9118_convocatoriasPublicadas(idFormulario,idRegistro,idReporte,fechaActivacion,fechaIniPublica,fechaFinPublica,
  			status,idProcesoRegistro) VALUES(".$idFormulario.",".$idRegistro.",".$idReporte.",'".date('Y-m-d')."',".$fechaIni.",
 			 ".$fechaFin.",1,".$idProcesoRegistro.")";
  return $con->ejecutarConsulta($consulta);		
}

function desHabilitarConvocatoria($idFormulario,$idRegistro)
{
	global $con;
	$consulta="update 9118_convocatoriasPublicadas set status=0 where idFormulario=".$idFormulario." and idRegistro=".$idRegistro;
	return $con->ejecutarConsulta($consulta);		
}

function publicarLicitacion($idFormulario,$idRegistro,$idReporte)
{
	//global $urlSitio;
	global $con;
	$consulta="SELECT fechaIniPublicacion,fechaFinPublicacion FROM 9042_formularioVSFechasConvocatoria 
				WHERE idFormulario=".$idFormulario." AND idReferencia=".$idRegistro;
	$filasFechas=$con->obtenerPrimeraFila($consulta);
	$fechaIni="NULL";
	$fechaFin="NULL";
	if($filasFechas)
	{
		$fechaIni="'".$filasFechas[0]."'";
		$fechaFin="'".$filasFechas[1]."'";
	}		
	$consulta="SELECT vincularProcesoRegistroEnLinea FROM 9044_datosConvocatoria 
				WHERE idFormulario=".$idFormulario." AND idReferencia=".$idRegistro;
	$idProcesoRegistro=$con->obtenerValor($consulta);
	if($idProcesoRegistro=="")
		$idProcesoRegistro="-1";
	
	$consulta="INSERT INTO 118_convocatoriasPublicadas(idFormulario,idRegistro,idReporte,fechaActivacion,fechaIniPublica,
				fechaFinPublica,status,idProcesoRegistro,tipoConvocatoria)		
				VALUES(".$idFormulario.",".$idRegistro.",".$idReporte.",'".date('Y-m-d')."',".$fechaIni.",".$fechaFin.",1,
				".$idProcesoRegistro.",1)";
	return $con->ejecutarConsulta($consulta);		
}
	
function obtenerTabuladorPuesto($cveConcepto,$puesto)
{
	global $con;
	$consulta="SELECT valor.importe FROM _535_tablaDinamica AS puesto,_535_conceptos AS valor 
				WHERE puesto.cmbPuesto2='".$puesto."' AND puesto.codigo=valor.idReferencia AND valor.clave='".$cveConcepto."'";
	$resp= $con->obtenerValor($consulta);
	if($resp=="")
	{
		return "0";
	}
	else
	{
		return $resp;
	}
}
	
function costoOperacionPuesto($cvePuesto)
{
	global $con;
	$suma=0;
	$sumatoria=0;
	$Consulta="SELECT sueldoMinimo FROM 819_puestosOrganigrama WHERE cvePuesto='".$cvePuesto."'";
	$Resp=$con->obtenerValor($Consulta);
	$SueldoBase=$Resp;
	$SueldoBaseDiario=$Resp/30;
	function ObtenerNumeroPersonas($cveDepto)
	{
		global $con;
		$consulta="SELECT COUNT(idUsuario) FROM 801_adscripcion WHERE codigoUnidad='".$cveDepto."'";
		$Resp=$con->obtenerValor($consulta);
		$cero=0;
		if($Resp)
		{
			return $Resp;
		}
		else
		{
			return $cero;
		}
}
	
function bajaProducto($Registro,$Usuario)
{
	global $con;
	$Valor=0;
	$consulta2="SELECT tabla.cmbProducto,tabla.responsable,tabla.fechaCreacion,tabla.txtObservaciones,depto.depto,depto.programa,depto.importe 
				FROM _595_tablaDinamica AS tabla,_595_dtgMovimiento AS depto WHERE id__595_tablaDinamica='".$Registro."'
				AND 	depto.idReferencia=tabla.id__595_tablaDinamica";
	$resp2=$con->obtenerFilas($consulta2);
	
	$consulta="SELECT noMovimiento FROM 903_variablesSistema";
	$resp=$con->obtenerValor($consulta);
	$Valor=$resp + 1;
	$consulta1="UPDATE 903_variablesSistema SET noMovimiento='".$Valor."'";
	$con->ejecutarConsulta($consulta1);

		while ($row= mysql_fetch_row($resp2))
		{
			$idProducto=$row[0];
			$idResponsable=$row[1];
			$fechaBaja=$row[2];
			$Observa=$row[3];
			$depto=$row[4];
			$idprograma=$row[5];
			$cantidad=$row[6];
			
			$consulta3="INSERT INTO 9302_existenciaAlmacen(idProducto,codigoUnidad,idPrograma,cantidad,fechaMovimiento,operacion,
			responsable,tipoMovimiento,noMovimiento,complementario1)
			VALUES('".$idProducto."','".$depto."','".$idprograma."','".$cantidad."','".$fechaBaja."','2','".$idResponsable."','2',
			'".$Valor."','".$Observa."')";
			$con->ejecutarConsulta($consulta3);
		}
}
	
function entradaProducto($idRegistro)
{
	global $con;
	$idRegistro="1";
	$tipoEntrada="1";
	$fechaRecibo="2011-03-30";
	$consulta="SELECT txtCantidad,cmbProducto,ciclo FROM _604_tablaDinamica WHERE id__604_tablaDinamica='".$idRegistro."'";
	$Resp=$con->obtenerFilas($consulta);
	  while ($row= mysql_fetch_row($Resp))
	  {
		  $Cantidad=$row[0];
		  $idproducto=$row[1];
		  $Ciclo=$row[2];
		  $consulta1="INSERT INTO 9136_AlmacenFarmacotecnia (idArticulo,cantidad,tipoMovimiento,tipoEntrada,fechaRecibo,ciclo)
		   VALUES ('".$idproducto."','".$Cantidad."','1','".$tipoEntrada."','".$fechaRecibo."','".$Ciclo."')";
		  $con->ejecutarConsulta($consulta1);
	  }
}
  
  $Consulta1="SELECT sum(importe.importe) FROM _535_tablaDinamica AS puesto,_535_conceptos AS importe 
  				WHERE puesto.cmbPuesto2='".$cvePuesto."' AND puesto.codigo=importe.idReferencia";
  $Resp1=$con->obtenerValor($Consulta1);
  $TabuladorPuesto=$Resp1;
  $TabuladorDiario=$Resp1/30;
  $sumaPercepciones=$SueldoBaseDiario + $TabuladorDiario;
  return $sumaPercepciones;
}

function ObtenerNumeroPersonas($cveDepto)
{
	global $con;
	$consulta="SELECT COUNT(idUsuario) FROM 801_adscripcion WHERE codigoUnidad='".$cveDepto."'";
	$Resp=$con->obtenerValor($consulta);
	$cero=0;
	if($Resp)
	{
		return $Resp;
	}
	else
	{
		return $cero;
	}
}

function antiguedad($fechaActual,$FechaIngreso)//Nutricion Funcion de nomina
{
	global $con;
	if($fechaActual>$FechaIngreso)
	{
		$Antiguedad= obtenerDiferenciaAnios($fechaActual,$FechaIngreso);
		$consulta="SELECT txtFactor FROM _656_tablaDinamica WHERE txtDE=(SELECT MAX(txtDE) FROM _656_tablaDinamica WHERE txtde<'".$Antiguedad."')";

		$Resp=$con->obtenerValor($consulta);
		if($Resp=="")
		{
			return "0";
		}
		return $Resp;
	}
	else
		return "0";
}

function diasTrascurridos()//Nutricion funcion para nomina
{
	$fechaActual=date("Y-m-d");
	$anio=date("Y");
	$fechaInicial="".$anio."-01-01";
	$calculo=obtenerDiferenciaDias($fechaInicial,$fechaActual);
	return $calculo;
}



function obtenerSalidaAnticipadas($Periodo,$ciclo,$IdUsuario)//Nutricion calculo de nomina
{
	global $con;
	global $idNomina;
	$horas=0;
	$hora1=0;
	if(strlen($Periodo)>"1")
	{
		$Periodo2=$Periodo;
	}
	else
	{
		$Periodo2="0".$Periodo;
	}
	$consulta="SELECT fechaInicioIncidencias,fechaFinIncidencias FROM 672_nominasEjecutadas WHERE idNomina='".$idNomina."'";
	$resp=$con->obtenerFilas($consulta);
	while ($row= mysql_fetch_row($resp))
	{
		$fechaIncial=$row[0];
		$fechaFin=$row[1];
		
		$consulta2="SELECT tAnticipado FROM 9105_controlAsistencia WHERE idUsuario='".$IdUsuario."' AND fecha 
						BETWEEN '".$fechaIncial."' AND '".$fechaFin."' and aut_TAnticipado='0'";
		$Tiempo=$con->obtenerFilas($consulta2 );
		$totalMinutos=strtotime("00:00:00");
		$horas=0;
		$minutos=0;
		$segundos=0;
			while ($row= mysql_fetch_row($Tiempo))
			{
				$dato=$row[0];
				$hora1=split(":",$dato);
				$horas=$horas+$hora1[0];
				$minutos=$minutos+$hora1[1];
				$segundos=$segundos+$hora1[2];
			}
			$minutos2=($horas*60)+$minutos;
			return $minutos2;
	}
}



function obtenerFechaNomina($periodo)//Nutricion calculo de nomina
{
	global $con;
	if(strlen($periodo)>"1")
	{
		$Periodo2=$periodo;
	}
	else
	{
		$Periodo2="0".$periodo;
	}

	$consulta="select fechaFinIncidencia FROM 656_calendarioNomina WHERE noQuincena='".$Periodo2."'";
	$resp=$con->obtenerPrimeraFila($consulta);
	if($resp)
	{
		$fecha=$resp[0];
		return $fecha;
	}
	return "0";
}

function ingresosNetos($ingreso)//Nutricion calculo de nomina
{
	global $con;
	$consulta="SELECT ingreso.limIngInf,ingreso.limIngSup,ingreso.limInfe,ingreso.limSup,ingreso.porcentaje,ingreso.cuotaFija 
	FROM _667_dtgIngreso AS ingreso,_667_tablaDinamica AS tipo WHERE ingreso.idReferencia=tipo.id__667_tablaDinamica AND tipo.txtTipo='10' 
	AND '".$ingreso."' BETWEEN ingreso.limIngInf AND ingreso.limIngSup";
	$valor=$con->obtenerPrimeraFila($consulta);
	if($valor)
	{
		$ingesoInferior=$valor[0];
		$ingresoSuperior=$valor[1];
		$limiteInferior=$valor[2];
		$limiteSuperior=$valor[3];
		$porcentaje=$valor[4];
		$cuotaFija=$valor[5];
		$ingreso=(($limiteInferior*$porcentaje)-($cuotaFija-$porcentaje))/(1-$porcentaje);
		return $ingreso;
	}
	return "0";
}

function impuestoQuinc($baseGrav,$ciclo,$dato)//Nutricion calculo de nomina
{
	//$dato= 'limite', 'porcentaje', 'cuota'
	global $con;
	echo $baseGrav;
	$valor=0;
	$consulta="SELECT impuesto.limInferior,impuesto.porcentaje,impuesto.cuotaFija FROM _668_dtgImpuesto AS impuesto,_668_tablaDinamica AS tipo
	 			WHERE impuesto.idReferencia=tipo.id__668_tablaDinamica AND tipo.txtTipo='1' AND '".$baseGrav."' 
				 BETWEEN impuesto.limInferior AND impuesto.limSuperior AND tipo.cmbCiclo='".$ciclo."' AND radStatus='1'";
	$filas=$con->obtenerPrimeraFila($consulta);
	if($filas)
	{
		$limInferior=$filas[0];
		$porcentaje=$filas[1];
		$cuota=$filas[2];
		
		switch($dato)
	  	{
			case($dato=='limite'):
		  	$valor=$limInferior;
		  	break;
			case($dato=='porcentaje'):
			$valor=$porcentaje;
			break;
			case($dato=='cuota'):
			$valor=$cuota;
			break;
			default:
			$valor=0;
			break;
		}
		return $valor;
	}
	else
	{
		return "0";
	}
}

function EmpleadoReclutado($idRegistro)//nutricion
{
	global $con;
	$consulta="SELECT candidato.idReferencia,candidato.cmbCandito,candidato.fechaCreacion,tipo.cmbTipoContratacion,tipo.horasContratacion 
				FROM _686_tablaDinamica AS candidato,_687_tablaDinamica AS tipo WHERE candidato.idReferencia=tipo.idReferencia 
				AND candidato.id__686_tablaDinamica='".$idRegistro."'";
	$filas=$con->obtenerPrimeraFila($consulta);
	if($filas)
	{
		$referencia=$filas[0];
		$idUsuario=$filas[1];
		$fechaIngreso=$filas[2];
		$tipoContratacion=$filas[3];
		$horasContratacion=$filas[4];
	}
	$consulCiclo="SELECT ciclo FROM 550_cicloFiscal WHERE STATUS='1'";
	$ciclo=$con->obtenerValor($consulCiclo);

	
	$consulta10="SELECT unidad.codUnidad,unidad.idPuesto,puesto.cvePuesto,puesto.sueldoMinimo,unidad.idUnidadVSPuesto 
				FROM 653_unidadesOrgVSPuestos AS unidad,667_puestosVacantes AS vacante,819_puestosOrganigrama AS puesto 
				WHERE unidad.idUnidadVSPuesto=vacante.idUnidadOrgVSPuesto AND vacante.idRegistroPerfil='".$referencia."' 
				AND unidad.idPuesto=puesto.idPuesto";
	$filas10=$con->obtenerPrimeraFila($consulta10);
	if($filas10)
	{
		$departamento=$filas10[0];
		$idPuesto=$filas10[1];
		$puesto=$filas10[2];
		$sueldoPuesto=$filas10[3];
		$idUnidadPuesto=$filas10[4];
	}
	
	$consulta12="UPDATE 653_unidadesOrgVSPuestos SET situacion='1' WHERE idUnidadVSPuesto='".$idUnidadPuesto."'";
	$con->ejecutarConsulta($consulta12);
	
	$consulta13="UPDATE 667_puestosVacantes SET STATUS='0' WHERE idUnidadOrgVSPuesto='".$idUnidadPuesto."'";
	$con->ejecutarConsulta($consulta13);

	
	$consulta1="SELECT cmbEstado,txtCalle,txtColonia,txtCp,cmbGenero,cmbPais,cmbCiudad,cmbLocalidad,cmbEstados,txtCiudadNac,txtCurp,txtNumero
				 FROM _760_tablaDinamica WHERE responsable='".$idUsuario."'";
	$filas1=$con->obtenerPrimeraFila($consulta1);
	if($filas1)
	{
		$estadoNac=$filas1[0];
		$calle=$filas1[1];
		$colonia=$filas1[2];
		$cp=$filas1[3];
		$genero=$filas1[4];
		$Pais=$filas1[5];
		$ciudadActual=$filas1[6];
		$localidad=$filas1[7];
		$estado=$filas1[8];
		$ciudadNac=$filas1[9];
		$Curp=$filas1[10];
		$numero=$filas1[11];
		$consulta1="UPDATE 807_usuariosVSRoles SET idRol='17',codigoRol='17_0' WHERE idUsuario='".$idUsuario."' AND idRol='56'";
		$con->ejecutarConsulta($consulta1);
		
		$consulta2="UPDATE 801_adscripcion SET Institucion='0001',cod_Puesto='".$puesto."',
				codigoUnidad='".$departamento."',fechaIngresoInstitucion='".$fechaIngreso."',
				horasTrabajador='".$horasContratacion."',tipoContratacion='".$tipoContratacion."' WHERE idUsuario='".$idUsuario."'";
		$con->ejecutarConsulta($consulta2);
		
	 	$consulta3="INSERT INTO 801_fumpEmpleado(idUsuario,tipoOperacion,fechaAplicacion,pQuincenaPago,pCicloPago,puesto,departamento,
		salario,fechaOperacion,tipoPuesto,zona,activo,idPuesto,tipoContratacion,horasTrabajador) 
		VALUES('".$idUsuario."','1','".$fechaIngreso."','01','".$ciclo."','".$puesto."','".$departamento."','".$sueldoPuesto."',
		'".$fechaIngreso."','1','1','1','".$idPuesto."','".$tipoContratacion."','".$horasContratacion."')";
	 	$con->ejecutarConsulta($consulta3);
		
		$consulta4="UPDATE 802_identifica SET ciudadNacimiento='".$ciudadNac."',estadoNacimiento='".$estadoNac."',
					STATUS='1',genero='".$genero."',curp='".$Curp."' WHERE idUsuario='".$idUsuario."'";
	  	$con->ejecutarConsulta($consulta4);
		
		$consulta6="UPDATE 803_direcciones SET Calle='".$calle."',Numero='".$numero."',Colonia='".$colonia."',Ciudad='".$ciudadNac."',
					CP='".$cp."',Estado='".$estado."',Pais='".$Pais."' WHERE idusuario='".$idUsuario."' AND tipo='0'";
		$con->ejecutarConsulta($consulta6);

		$consulta7="INSERT INTO 804_telefonos(idUsuario)VALUES('".$idUsuario."')";
		$con->ejecutarConsulta($consulta7);
	}
}

function guardarGanador($idRegistro)//Alta de Catedratico en el Sistema UGM
{
	global $con;
	$idMateria=0;
	$idGrupo=0;
	$participacion=0;

	$consulta="SELECT codigoInstitucion,cmbCiclo,cmbMateria,cmbPrograma,cmbGrupo FROM _290_tablaDinamica 
				WHERE id__290_tablaDinamica='".$idRegistro."'";
	$resp=$con->obtenerPrimeraFila($consulta);
	$Institucion=$resp[0];
	$Ciclo=$resp[1];
	$idMateria=$resp[2];
	$idPrograma=$resp[3];
	$idGrupo=$resp[4];
	
	$consulta10="SELECT cmbCandi,id__338_tablaDinamica FROM _338_tablaDinamica WHERE idReferencia='".$idRegistro."'";
	$resp10=$con->obtenerPrimeraFila($consulta10);
	
	$idUsuario=$resp10[0];
	$id=$resp10[1];

	$consulta4="SELECT cmbEstado,cmbGenero,txtCurp,cmbCiudadNac,cmbEdiCivil,txtTelefono,cmbPais,txtCalle,txtColonia,txtNumero,txtCP,cmbPaisNaci,
				txtEdoNaci,txtCdNaci,txtEdo,txtCiudad,txtLocalidad,cmbEdiDirec,cmbCiudad,cmbLocalidad,txtCedulaProfesional
				FROM _230_tablaDinamica where responsable='".$idUsuario."'";
	$resp1=$con->obtenerPrimeraFila($consulta4);
	$estadoNacMex="";
	$cdNacimientoMex="";
	$edoNaciExt="";
	$cdNacimientoExt="";
	$estadoNacMex="";
	$genero="";
	$curp="";
	$cdNacimientoMex="";
	$edoCivil="";
	$telefonoActual="";
	$paisActual="";
	$calleActual="";
	$coloniaActual="";
	$numeroActual="";
	$cpActual="";
	$paisNac="";
	$edoNaciExt="";
	$cdNacimientoExt="";
	$edoActExt="";
	$cdActualExt="";
	$localidadActExt="";
	$edoActualMex="";
	$cdActualMex="";
	$localidadActMex="";
	$cedulaP="";

	if($resp1)
	{
		$estadoNacMex=$resp1[0];
		$genero=$resp1[1];
		$curp=$resp1[2];
		$cdNacimientoMex=$resp1[3];	
		$edoCivil=$resp1[4];
		$telefonoActual=$resp1[5];
		$paisActual=$resp1[6];
		$calleActual=$resp1[7];
		$coloniaActual=$resp1[8];
		$numeroActual=$resp1[9];
		$cpActual=$resp1[10];
		$paisNac=$resp1[11];
		$edoNaciExt=$resp1[12];
		$cdNacimientoExt=$resp1[13];
		$edoActExt=$resp1[14];
		$cdActualExt=$resp1[15];
		$localidadActExt=$resp1[16];
		$edoActualMex=$resp1[17];
		$cdActualMex=$resp1[18];
		$localidadActMex=$resp1[19];
		$cedulaP=$resp1[20];
	}
	
	if($paisActual==146)
	{
		$paisActual2="México";
		$EstadoA=$edoActualMex;
		$CiudadA=$cdActualMex;
		$LocalidadA=$localidadActMex;
	}
	else
	{
		$EstadoA=$edoActExt;
		$CiudadA=$cdActualExt;
		$LocalidadA=$localidadActExt;
		$bPais="SELECT nombre FROM 238_paises WHERE idPais='".$paisActual."'";
		$paisActual2=$con->obtenerValor($bPais);
	}
	if($paisNac==146)
	{
		$paisNac2="México";
		$bestado="SELECT estado FROM 820_estados WHERE cveEstado='".$estadoNacMex."'";
		$EstadoNac=$con->obtenerValor($bestado);
		$bCiudad="SELECT municipio FROM 821_municipios WHERE cveMunicipio='".$cdNacimientoMex."'";
		$CiudadNac=$con->obtenerValor($bCiudad);
	}
	else
	{
		$EstadoNac=$edoNaciExt;
		$CiudadNac=$cdNacimientoExt;
		//$bPaisN="SELECT nombre FROM 238_paises WHERE idPais='".$paisNac."'";
		//$paisNac2=$con->obtenerValor($bPaisN);
	}
	
	$consulta2="UPDATE 807_usuariosVSRoles SET idRol='5',codigoRol='5_0' WHERE idUsuario='".$idUsuario."' AND idRol='38'";
	$con->ejecutarConsulta($consulta2);
	
	$consultaMapa="SELECT idMapaCurricular FROM 4241_nuevosMapas WHERE ciclo='".$Ciclo."' AND idprograma='".$idPrograma."' 
					AND sede='".$Institucion."'";
	$cMapa=$con->obtenerValor($consultaMapa);
	
				
	$consulta3="SELECT idParticipacionPrincipal FROM 4029_mapaCurricular WHERE idMapaCurricular='".$cMapa."'";
	$participacion=$con->obtenerValor($consulta3);

	$consulta1="INSERT INTO 4047_participacionesMateria(idUsuario,idMateria,idGrupo,idParticipacion,participacionP,estado,esperaContrato)
				 VALUES('".$idUsuario."','".$idMateria."','".$idGrupo."','".$participacion."','1','1','1')";
	$con->ejecutarConsulta($consulta1);

	$consulta5="UPDATE 801_adscripcion SET institucion='".$Institucion."',status='1' WHERE idUsuario='".$idUsuario."'";
	$con->ejecutarConsulta($consulta5);
	
	$consulta6="INSERT INTO 801_fumpEmpleado(idUsuario,tipoOperacion,activo) VALUES('".$idUsuario."','1','1')";
	$con->ejecutarConsulta($consulta6);

	$consulta7="UPDATE 802_identifica SET ciudadNacimiento='".$CiudadNac."',estadoNacimiento='".$EstadoNac."',
				paisNacimiento='".$paisNac."',STATUS='1',Genero='".$genero."',CURP='".$curp."',cedulaProf='".$cedulaP."'
				WHERE idUsuario='".$idUsuario."'";
	$con->ejecutarConsulta($consulta7);
	
	$consulta8="UPDATE 803_direcciones SET Calle='".$calleActual."',Numero='".$numeroActual."',Colonia='".$coloniaActual."'
				,Ciudad='".$CiudadA."',CP='".$cpActual."',Estado='".$EstadoA."',Pais='".$paisActual."' 
				WHERE idUsuario='".$idUsuario."' AND tipo='0'";
	$con->ejecutarConsulta($consulta8);
	
	$consulta9="INSERT INTO 804_telefonos(Lada,Numero,Extension,Tipo,Tipo2,idUsuario) 
				VALUES('1','$telefonoActual',' ','0','0','".$idUsuario."')";
	$con->ejecutarConsulta($consulta9);
	
	$consultaMat="UPDATE 4233_solicitudConvMateria SET situacion='0' WHERE idRegistro='".$idRegistro."'";
	$con->ejecutarConsulta($consultaMat);
}

function inscripcionEnLinea($idRegistro)//Alta de alumnos al Sistema UGM
{
	global $con;
	$fecha=date("Y-m-d");
	$consultaX="SELECT uno.responsable,uno.cmbPlantel,uno.cmbCarrera,uno.cmbModalidad,uno.cmbTurno,dos.cmbEstadoNac,dos.cmbGenero,
				dos.cmbCurp,dos.txtCalle,dos.txtNumero,dos.cmbEdoDirec,dos.cmbPais,dos.txtCP,dos.cmbEdoCivil,dos.txtTelefono,
				dos.cmbCiudadNac,dos.cmbCiudad,dos.cmbLocalidad,dos.txtEstadoIntAct,dos.txtCiudad,dos.txtLocalidad,dos.cmbPaisNac,
				dos.txtEdoInter,dos.txtCdIntern FROM _309_tablaDinamica AS uno,_299_tablaDinamica AS dos 
				WHERE dos.idReferencia=uno.id__309_tablaDinamica AND uno.id__309_tablaDinamica='".$idRegistro."'";
	$Registros=$con->obtenerFilas($consultaX);
	  while ($row= mysql_fetch_row($Registros))
	  {
		  $idUsuario=$row[0];
		  $Plantel=$row[1];
		  $idPlanEstudio=$row[2];
		  $idModalidad=$row[3];
		  $idTurno=$row[4];
		  $estadoNac=$row[5];
		  $genero=$row[6];
		  $curp=$row[7];
		  $calle=$row[8];
		  $numero=$row[9];
		  $edoDireccionAct=$row[10];
		  $pais=$row[11];
		  $cp=$row[12];
		  $edoCivil=$row[13];
		  $telefono=$row[14];
		  $ciudadNac=$row[15];
		  $ciudadActual=$row[16];
		  $localidadActual=$row[17];
		  $EdoInternActual=$row[18];
		  $ciudadInternAct=$row[19];
		  $localidadIntActual=$row[20];
		  $paisNacimiento=$row[21];
		  $EdoInternaNac=$row[22];
		  $ciudadInternNac=$row[23];
		  $query="SELECT idInstanciaPlanEstudio FROM 4513_instanciaPlanEstudio WHERE idPlanEstudio=".$idPlanEstudio." AND sede='".$Plantel."' 
		  			AND idModalidad='".$idModalidad."' AND idTurno=".$idTurno;
		$idInstanciaPlan=$con->obtenerValor($query);
	  if($paisNacimiento==146) //Si el Pais de Nacimiento es México
	  {
		  $EdoNacimiento=$estadoNac;
		  $CiudadNacimiento=$ciudadNac;
	  }
	  else
	  {
		  $EdoNacimiento=$EdoInternaNac;
		  $CiudadNacimiento=$ciudadInternNac;
	  }
	  if($pais==146) // Si el País del Domicilio actual es México
	  {
		  $edoDomActual=$edoDireccionAct;
		  $ciudadDomActual=$ciudadActual;
		  $localidadDomAtual=$localidadActual;
	  }
	  else
	  {
		  $edoDomActual=$EdoInternActual;
		  $ciudadDomActual=$ciudadInternAct;
		  $localidadDomAtual=$localidadIntActual;
	  }
	  $consultaPadre="SELECT txtApPaterno,txtApMaterno,txtNombrePa,txtOcupacionPar,txtDomicilioTutor,cmbParentesco,txtTelOficina,txtTelCasa,
						txtCorreoElec FROM _321_tablaDinamica WHERE responsable='".$idUsuario."'";
		$filaPadre=$con->obtenerPrimeraFila($consultaPadre);
			$paternoTutor=$filaPadre[0];
			$maternoTutor=$filaPadre[1];
		 	$nombreTutor=$filaPadre[2];
			$ocupacionTutor=$filaPadre[3];
			$domicilioTutor=$filaPadre[4];
			$parentescoTutor=$filaPadre[5];
		 	$telOficina=$filaPadre[6];
		 	$telCasa=$filaPadre[7];
			$emailPadre=$filaPadre[8];
		 	$nombreComTutor=$nombreTutor." ".$paternoTutor." ".$maternoTutor;
		
		$consultaEs="SELECT idEsquemaGrupo FROM 4513_instanciaPlanEstudio WHERE idInstanciaPlanEstudio=".$idInstanciaPlan;
		$idEsquema=$con->obtenerValor($consultaEs);

		$consultaCiclo="";
		$Ciclo='1';
		
		
		$consultaGrado="SELECT idGrado FROM 4501_Grado WHERE idPlanEstudio='".$idPlanEstudio."' ORDER BY ordenGrado LIMIT 0,1";
		$grado=$con->obtenerPrimeraFila($consultaGrado);
		$idGrado=$grado[0];
		$x=0;
		$consulta[$x]="begin";
		$x++;

//	  Parientes
	  	$consulta[$x]="INSERT INTO 800_usuarios(STATUS,Nombre,paso,idIdioma,cuentaActiva,cambiarDatosUsr)VALUES('1',
					'".$nombreComTutor."','1','1','1','0')";

		$x++;
		$consulta[$x]="set @idTutor=(select last_insert_id())";
		$x++;
		$consulta[$x]="INSERT INTO 801_adscripcion(Institucion,STATUS,Tipo,idUsuario,Actualizado,codigoUnidad)
					VALUES('".$Plantel."','1','0',@idTutor,'0','".$Plantel."')";
		$x++;
		$consulta[$x]="INSERT INTO 802_identifica(Nom,Paterno,Materno,Nombre,STATUS,idUsuario)
					VALUES('".$nombreTutor."','".$paternoTutor."','".$maternoTutor."','".$nombreComTutor."','1',@idTutor)";

		$x++;
		$consulta[$x]="INSERT INTO 803_direcciones(Tipo,idUsuario) VALUES('0',@idTutor)";
		$x++;
		$consulta[$x]="INSERT INTO 803_direcciones(Tipo,idUsuario) VALUES('1',@idTutor)";
		$x++;
		$consulta[$x]="INSERT INTO 804_telefonos(Lada,Numero,Extension,Tipo,Tipo2,idUsuario) 
					VALUES('1','".$telCasa."',' ','0','0',@idTutor)";
		$x++;

		$consulta[$x]="INSERT INTO 805_mails (Mail,Tipo,Notificacion,idUsuario) VALUES('".$emailPadre."','0','1',@idTutor)";
		$x++;
		$consulta[$x]="INSERT INTO 806_fotos(idUsuario)VALUES(@idTutor)";
		$x++;
		$consulta[$x]="INSERT INTO 4518_alumnosParientes(idAlumno,idUsuario,IdParentezco) VALUES(".$idUsuario.",@idTutor,
					".$parentescoTutor.")";
		$x++;
		$consulta[$x]="INSERT INTO 807_usuariosVSRoles(idUsuario,idRol,idExtensionRol,codigoRol)VALUES(@idTutor,'53','0','53_0')";
		$x++;
	  //Alumno
		$consulta[$x]="UPDATE 807_usuariosVSRoles SET idRol='7',codigoRol='7_0' WHERE idUsuario='".$idUsuario."' AND idRol='36'";
	  	$x++;
		$consulta[$x]="UPDATE 802_identifica SET ciudadNacimiento='".$CiudadNacimiento."',estadoNacimiento='".$EdoNacimiento."',
					paisNacimiento='".$paisNacimiento."',STATUS='1',Genero='".$genero."',CURP='".$curp."' where idUsuario='".$idUsuario."' ";
		$x++;
		$consulta[$x]="UPDATE 803_direcciones SET Calle='".$calle."',Numero='".$numero."',Colonia='".$localidadDomAtual."'
					,Ciudad='".$ciudadDomActual."',CP='".$cp."',Estado='".$edoDomActual."',Pais='".$pais."' 
					WHERE idusuario='".$idUsuario."' AND tipo='0'";
		$x++;
		$consulta[$x]="INSERT INTO 804_telefonos(Lada,Numero,Extension,Tipo,Tipo2,idUsuario) 
					VALUES('1','".$telefono."',' ','0','0','".$idUsuario."')";
		$x++;
		
		if($idEsquema==1)
		{
			$consulta[$x]="INSERT INTO 4529_alumnos(ciclo,idInstanciaPlanEstudio,idGrado,idUsuario,estado)VALUES('".$Ciclo."','".$idInstanciaPlan."'
							,'".$idGrado."','".$idUsuario."','1')";
			$x++;
		}

		$query="SELECT codigoUnidad FROM 4505_estructuraCurricular WHERE idUnidad=".$idGrado." AND idPlanEstudio=".$idPlanEstudio." 
				AND tipoUnidad=3";
		$codigoUnidad=$con->obtenerValor($query);
		
		
		inscribirAlumnoMateriaObligatoria($codigoUnidad,$idPlanEstudio,$idUsuario,$consulta,$x,$Ciclo,$Plantel,$idInstanciaPlan,$idGrado);
		$consulta[$x]="commit";
		$x++;
		$con->ejecutarBloque($consulta);
	  }

}

function asignarSede($idRegistro)
{
	global $con;
	$consulta="SELECT responsable,cmbPlantel FROM _309_tablaDinamica WHERE id__309_tablaDinamica='".$idRegistro."'";
	$Registros=$con->obtenerFilas($consulta);
	  while ($row= mysql_fetch_row($Registros))
	  {
		  $idUsuario=$row[0];
		  $sede=$row[1];
		  $consulta1="UPDATE _309_tablaDinamica SET codigoInstitucion='".$sede."' WHERE id__309_tablaDinamica='".$idRegistro."'";
		  $con->ejecutarConsulta($consulta1);
		  $consulta3="UPDATE 801_adscripcion SET Institucion='".$sede."' WHERE idUsuario='".$idUsuario."'";
		$con->ejecutarConsulta($consulta3);
	  }
}

function ponerSedeConvocatoria($idRegistro)
{
	global $con;
	$consulta="SELECT uno.codigoInstitucion FROM _277_tablaDinamica AS uno,_298_tablaDinamica AS dos 
				WHERE uno.id__277_tablaDinamica=dos.cmbConvocatoria AND dos.id__298_tablaDinamica='".$idRegistro."'";
	$codigo=$con->obtenerValor($consulta);
	
	$consulta1="UPDATE _298_tablaDinamica SET codigoInstitucion='".$codigo."' WHERE id__298_tablaDinamica='".$idRegistro."'";
	$con->ejecutarConsulta($consulta1);
}

function bajaDocente($idRegistro)//baja de Docente y cancelacion de contrato del sistema UGM
{
	global $con;
	$consulta="SELECT codigoInstitucion,Fecha,cmbDocente FROM _325_tablaDinamica WHERE id__325_tablaDinamica='".$idRegistro."'";
	$Profe=$con->obtenerPrimeraFila($consulta);
	$institucion=$Profe[0];
	$fechaBaja=$Profe[1];
	$idProfeBaja=$Profe[2];
	
	$consulta1="SELECT idOpcion FROM _325_materiasDocente WHERE idpadre='".$idRegistro."'";
	$Registros=$con->obtenerFilas($consulta1);
	while ($row= mysql_fetch_row($Registros))
	{
		$cadena=$row[0];
		$cadenaS=explode("_",$cadena);
		$materiaBaja=$cadenaS[0];
		$grupoBaja=$cadenaS[1];
		
			$consulta2="UPDATE 4047_participacionesMateria SET estado='2' WHERE idUsuario='".$idProfeBaja."' AND idMateria='".$materiaBaja."' 
						AND idGrupo='".$grupoBaja."'";
			$con->ejecutarConsulta($consulta2);
	
			$consulta3="SELECT contrato.id__273_tablaDinamica FROM _273_tablaDinamica AS contrato,_274_gridAsignaturas AS materia 
						WHERE materia.idReferencia=contrato.id__273_tablaDinamica AND materia.asignatura='".$cadena."'";
			$program=$con->obtenerValor($consulta3);

			$consulta4="UPDATE _273_tablaDinamica SET idEstado='5' WHERE id__273_tablaDinamica='".$program."'";
			$con->ejecutarConsulta($consulta4);
	}
	$consulta5="UPDATE 4047_participacionesMateria SET esperaContrato='1' WHERE idUsuario='".$idProfeBaja."' AND estado='1'";
	$con->ejecutarConsulta($consulta5);
}

function liberarEsperaContrato($idRegistro)//Cambia status de esperaContrato UGM
{
	global $con;
	$consulta="SELECT codigoInstitucion,cmbCatedratico FROM _273_tablaDinamica WHERE id__273_tablaDinamica='".$idRegistro."'";
	$Profe=$con->obtenerPrimeraFila($consulta);
	$institucion=$Profe[0];
	$idUsuario=$Profe[1];
	
	$consulta1="SELECT asignatura FROM _274_gridAsignaturas WHERE idReferencia='".$idRegistro."'";
	$Registros=$con->obtenerFilas($consulta1);
	while ($row= mysql_fetch_row($Registros))
	{
		$cadena=$row[0];
		$cadenaS=explode("_",$cadena);
		$materia=$cadenaS[0];
		$grupo=$cadenaS[1];
		
		$consulta2="UPDATE 4047_participacionesMateria SET esperaContrato='0' WHERE idUsuario='".$idUsuario."' 
					AND idMateria='".$materia."' AND idGrupo='".$grupo."'";
		$con->ejecutarConsulta($consulta2);
	}
}

function esperaContrato($idFormulario,$idRegistro)//nutricion
{
	global $con;
	$consulta="SELECT id__454_tablaDinamica,codigoInstitucion FROM _454_tablaDinamica WHERE id__454_tablaDinamica='".$idRegistro."'";
	$Registros=$con->obtenerPrimeraFila($consulta);
	$idReferencia=$Registros[0];
	$Institucion=$Registros[1];

	$consulta1="SELECT cmbCandito FROM _686_tablaDinamica WHERE idReferencia='".$idReferencia."'";
	$Registros1=$con->obtenerValor($consulta1);
	$idUsuario=$Registros1;
	
	$consulta2="SELECT p.codUnidad,p.idPuesto,v.idUnidadOrgVSPuesto FROM 653_unidadesOrgVSPuestos AS p,667_puestosVacantes AS v 
				WHERE p.idUnidadVSPuesto=v.idUnidadOrgVSPuesto AND v.idRegistroPerfil='".$idReferencia."'";
	$puesto=$con->obtenerPrimeraFila($consulta2);
	$CodUnidad=$puesto[0];
	$idPuesto=$puesto[1];
	$idUnidadPuesto=$puesto[2];
	
	$consulta3="INSERT INTO 9137_esperaContrato(idUsuario,codigoUnidad,cod_puesto,Institucion,idReferencia,idUnidadOrgVSPuesto,	
				estado,esperaContrato) 	VALUES('".$idUsuario."','".$CodUnidad."','".$idPuesto."','".$Institucion."',
					'".$idReferencia."','".$idUnidadPuesto."','1','1')";
	$con->ejecutarConsulta($consulta3);
}

function altaEmpleadoNutricion($idFormulario,$idRegistro)
{
	global $con;
	$consulta="SELECT idReferencia,codigoInstitucion,cmbTipoContratacion,horasContratacion,dteFEchaInicio,dteFechaFinal 
				FROM _687_tablaDinamica WHERE id__687_tablaDinamica='".$idRegistro."'";
	$datos=$con->obtenerPrimeraFila($consulta);
	$idReferencia=$datos[0];
	$Institucion=$datos[1];
	$tipoContratacion=$datos[2];
	$horaContratacion=$datos[3];
	$fechaInicioContrato=$datos[4];
	$fechaFinContrato=$datos[5];
	$primerPeriodo=periodo($fechaInicioContrato);
	
	$consulta2="SELECT idUsuario,codigoUnidad,cod_puesto,idUnidadOrgVSPuesto FROM 9137_esperaContrato 
				WHERE idReferencia='".$idReferencia."'";
	$datos2=$con->obtenerPrimeraFila($consulta2);
	$idUsuario=$datos2[0];
	$codigoUnidad=$datos2[1];
	$codPuesto=$datos2[2];
	$idUnidadOrgVSPuesto=$datos2[3];
	
	$consulta3="UPDATE _454_tablaDinamica SET idEstado='4.20' WHERE id__454_tablaDinamica='".$idReferencia."'";
	$con->ejecutarConsulta($consulta3);
	
	$consulta4="UPDATE 653_unidadesOrgVSPuestos SET situacion='1' WHERE idUnidadVSPuesto='".$idUnidadOrgVSPuesto."'";
	$con->ejecutarConsulta($consulta4);

	$consulta5="UPDATE 667_puestosVacantes SET STATUS='0' WHERE idRegistroPerfil='".$idReferencia."'";
	$con->ejecutarConsulta($consulta5);	

	$consulta6="UPDATE 9137_esperaContrato SET esperaContrato='0' WHERE idReferencia='".$idReferencia."'";
	$con->ejecutarConsulta($consulta6);	

	$consultaPuesto="SELECT cvePuesto,sueldoMinimo,horasPuesto FROM 819_puestosOrganigrama WHERE idPuesto='".$codPuesto."'";
	$cve_Puesto=$con->obtenerPrimeraFila($consultaPuesto);
	$cvePuesto=$cve_Puesto[0];
	$importeSueldo=$cve_Puesto[1];
	$horaPuesto=$cve_Puesto[2];

	$consulta7="UPDATE 801_adscripcion SET Institucion='".$Institucion."',cod_Puesto='".$cvePuesto."',codigoUnidad='".$codigoUnidad."',
	fechaIngresoInstitucion='".$fechaInicioContrato."',horasTrabajador='".$horaContratacion."',tipoContratacion='".$tipoContratacion."' 
	where idUsuario='".$idUsuario."'";
	$con->ejecutarConsulta($consulta7);
	
	$consulta8="SELECT ciclo FROM 550_cicloFiscal WHERE STATUS='1'";
	$ciclo1=$con->obtenerValor($consulta8);
	$cicloA=$ciclo1;
	
	$consulta9="INSERT INTO 801_fumpEmpleado(idUsuario,tipoOperacion,fechaAplicacion,pQuincenaPago,pCicloPago,puesto,departamento,salario,
	fechaOperacion,	tipoPuesto,zona,idTabulacion,activo,idPuesto,tipoContratacion,horasTrabajador,horasCategoria) VALUES('".$idUsuario."','1',
	'".$fechaInicioContrato."','".$primerPeriodo."','".$cicloA."','".$cvePuesto."','".$codigoUnidad."','".$importeSueldo."',
	'".$fechaInicioContrato."','1','1','".$idUnidadOrgVSPuesto."','1','".$codPuesto."','".$tipoContratacion."','".$horaContratacion."',
	'".$horaPuesto."')";
	$con->ejecutarConsulta($consulta9);

	$consulta10="SELECT cmbEstadoNacMex,txtCalle,txtCp,cmbGenero,cmbPaisActual,cmbCiudadActualMe,cmbLocalidadActMex,
	cmbEstadosActualeMex,txtCurp,txtNumero,cmbPaisNacimiento,cmbCiudadNacMexi,txtEstadoNacInt,txtCiudadNacInt,txtCiudadActInt,txtEstadoActInt 
	FROM _760_tablaDinamica WHERE responsable='".$idUsuario."'";
	$datosG=$con->obtenerPrimeraFila($consulta10);
	$estadoNacMex=$datosG[0];
	$calleActual=$datosG[1];
	$cp=$datosG[2];
	$genero=$datosG[3];
	$PaisActual=$datosG[4];
	$ciudadActualMex=$datosG[5];
	$colonia=$datosG[6];
	$estadoActualMex=$datosG[7];
	$curp=$datosG[8];
	$numCasa=$datosG[9];
	$paisNacimiento=$datosG[10];
	$ciudadNacMex=$datosG[11];
	$estadoNacInt=$datosG[12];
	$ciudadNacInt=$datosG[13];
	$ciudadActInt=$datosG[14];
	$estadoActInt=$datosG[15];

	if($paisNacimiento==146)
	{
		$edoNac=$estadoNacMex;
		$cdNac=$ciudadNacMex;
	}
	else
	{
		$edoNac=$estadoNacInt;
		$cdNac=$ciudadNacInt;
	}
	if($PaisActual==146)
	{
		$edoActual=$estadoActualMex;
		$cdActual=$ciudadActualMex;
	}
	else
	{
		$edoActual=$estadoActInt;
		$cdActual=$ciudadActInt;
	}
	
	$colonia1="SELECT * FROM 822_localidades WHERE cveLocalidad='".$colonia."'";
	$colonia2=$con->obtenerValor($colonia1);

	
	$consulta11="UPDATE 802_identifica SET ciudadNacimiento='".$cdNac."',estadoNacimiento='".$edoNac."',paisNacimiento='".$paisNacimiento."',
				STATUS='1',Genero='".$genero."',CURP='".$curp."' WHERE idUsuario='".$idUsuario."'";
	$con->ejecutarConsulta($consulta11);
	
	$consulta12="UPDATE 803_direcciones SET Calle='".$calleActual."',Numero='".$numCasa."',Colonia='".$colonia2."',
				Ciudad='".$cdActual."',CP='".$cp."',Estado='".$edoActual."',Pais='".$PaisActual."' where idUsuario='".$idUsuario."' and Tipo='0'";	
	$con->ejecutarConsulta($consulta12);

	$consulta13="INSERT INTO 804_telefonos(Tipo,Tipo2,idUsuario)VALUES('0','0','".$idUsuario."')";
	$con->ejecutarConsulta($consulta13);
	
	$consulta15="UPDATE 807_usuariosVSRoles SET idRol='17',codigoRol='17_0' WHERE idUsuario='".$idUsuario."' AND idRol='56'";
	$con->ejecutarConsulta($consulta15);
}


function periodo($fecha)
{
	global $con;
	$fecha2=explode("-",$fecha);
	$anio=$fecha2[0];
	$mes=$fecha2[1];
	$dia=$fecha2[2];
	
	switch($mes)
	{
		case '01':
			if($dia<15)
			{
				$periodo='01';
				
			}
			else
			{
				$periodo='02';
			}
			break;
		case '02':
			if($dia<15)
			{
				$periodo='03';
				
			}
			else
			{
				$periodo='04';
			}
			break;
			
		case '03':
			if($dia<15)
			{
				$periodo='05';
				
			}
			else
			{
				$periodo='06';
			}
			break;
		case '04':
			if($dia<15)
			{
				$periodo='07';
				
			}
			else
			{
				$periodo='08';
			}
			break;
		case '05':
			if($dia<15)
			{
				$periodo='09';
				
			}
			else
			{
				$periodo='10';
			}
			break;
		case '06':
			if($dia<15)
			{
				$periodo='11';
				
			}
			else
			{
				$periodo='12';
			}
			break;
		case '07':
					if($dia<15)
			{
				$periodo='13';
				
			}
			else
			{
				$periodo='14';
			}
			break;
		case '08':
					if($dia<15)
			{
				$periodo='15';
				
			}
			else
			{
				$periodo='16';
			}
			break;
		case '09':
					if($dia<15)
			{
				$periodo='17';
				
			}
			else
			{
				$periodo='18';
			}
			break;
		case '10':
			if($dia<15)
			{
				$periodo='19';
				
			}
			else
			{
				$periodo='20';
			}
			break;
		case '11':
			if($dia<15)
			{
				$periodo='21';
				
			}
			else
			{
				$periodo='22';
			}
			break;
		case '12':
					if($dia<15)
			{
				$periodo='23';
				
			}
			else
			{
				$periodo='24';
			}
			break;
	}
	return $periodo;
	
}

function validarVacaciones($tipo,$fechaIni,$fechaFin)
{
	global $con;
	$res="1|";
	$idUsuario=$_SESSION["idUsr"];
	$fechaInicio=cambiaraFechaMysql($fechaIni);
	$fechaFin=cambiaraFechaMysql($fechaFin);
	$diasSolicitados=(obtenerDiferenciaDias($fechaInicio,$fechaFin))+1;//dias solicitados
	$descomponer=explode('-',$fechaInicio);
	$anio=$descomponer[0];
	$mes=$descomponer[1];
	$dia=$descomponer[2];
	$consulta="SELECT p.fechaInicio,p.fechaFinal FROM _455_periodos AS p,_455_tablaDinamica AS ciclo 
				WHERE ciclo.cicloFiscal='".$anio."' AND p.nombrePeriodo='".$tipo."' ";//dias de vacaciones autorizados
	$datos=$con->obtenerPrimeraFila($consulta);
	$fechaIniAut=$datos[0];
	$fechaFinAut=$datos[1];
	$diasVacaAutorizados=(obtenerDiferenciaDias($fechaIniAut,$fechaFinAut))+1;
	
	if($diasSolicitados>$diasVacaAutorizados)
	{
		$res="Los dias solicitados son Mayor al Autorizado";
		return $res;
	}
	
	$consulta1="SELECT * FROM 9106_Justificaciones WHERE tipo='".$tipo."' AND estado='1' AND idUsuario='".$idUsuario."'
				AND EXTRACT(YEAR FROM fecha_Inicial)='".$anio."'";//dias tomados
	$Registros=$con->obtenerFilas($consulta1);
	$numero=$con->filasAfectadas;
	if($numero>0)
	{
		$diasTomados=0;
		while ($row= mysql_fetch_row($Registros))
		{
			$fechaIniTomado=$row[1];
			$fechaFinTomado=$row[2];
			$dif=(obtenerDiferenciaDias($fechaIniTomado,$fechaFinTomado))+1;
			$diasTomados=$diasTomados+$dif;
		}
		$resta=$diasVacaAutorizados-$diasTomados;
		if($diasSolicitados>$resta)
		{
			$res="Ya tiene tomados ".$diasTomados." dia te restan ".$resta." dia del Periodo";
			return $res;
		}
		else
		{
			return $res;
		}
	}
	else
	{
		return $res;
	}
}
function obtenerNivelEstudio($idUsuario,$ciclo)
{
	global $con;
			$consulta5="SELECT MAX(estudio.cmbNivelEstudio),costo.idHrCatedra FROM _269_Tabuladorsueldos AS costo,_262_tablaDinamica AS estudio 
						WHERE estudio.cmbNivelEstudio=costo.id__269_Tabuladorsueldos AND estudio.responsable='".$idUsuario."' 
						GROUP BY estudio.responsable";
		$filaX=$con->obtenerPrimeraFila($consulta5);
		if($filaX)
	  		{
		  		$costoUsuario=$filaX[1];
				
	  		}
	  	else
	  		{
		  		//$costoUsuario=0;
				$consulLic="SELECT s.idHrCatedra FROM _269_Tabuladorsueldos AS s,_269_tablaDinamica AS c 
							WHERE s.idReferencia=c.id__269_tablaDinamica AND s.idNivelacademico='1' AND c.cmbCiclo='".$ciclo."'";
				$ValorLic=$con->obtenerValor($consulLic);
				$costoUsuario=$ValorLic;
	  		}
			return $costoUsuario;
}
	
function obtenerValorFactorEmpleado($cveFactor)
{
	global $arrFactores;
	if(isset($arrFactores[$cveFactor]))
	{
		if($arrFactores[$arrFactores][1]==0)
			return $arrFactores[$arrFactores][0];
		return $arrFactores[$arrFactores][0]/100;	
	}
	return 0;
}

function obtenerValorFactor($factor,$puesto,$depto)
{
	global $arrFactoresRiesgo;
	$llave=$factor."_".$depto."_".$puesto;
	if(isset($arrFactores[$llave]))
	{
		if($arrFactoresRiesgo[$llave][1]==0)
			return $arrFactoresRiesgo[$llave][0];
		return $arrFactoresRiesgo[$llave][0]/100;	
	}
	return 0;
}

function baseFactorRiesgo($puesto)
{
	$baseRiesgo= obtenerValorFactor("C110",$puesto,-1);
	$baseRiesgo+=obtenerValorFactorEmpleado("C112");
	$baseRiesgo+=obtenerValorFactor("C114",$puesto,-1);
	$baseRiesgo+=obtenerValorFactorEmpleado("C118");
	$baseRiesgo+=obtenerValorFactorEmpleado("C119");
	return $baseRiesgo;
}

function validarFechasProtocolo($Fechainiciopro,$fechatermino)
{
	if($Fechainiciopro=="")
		return "La fecha de inicio es obligatoria";
	if($fechatermino=="")
		return "La fecha de término es obligatoria";
	$fI=strtotime(cambiaraFechaMysql($Fechainiciopro));
	$fF=strtotime(cambiaraFechaMysql($fechatermino));
	if($fF<$fI)
		return "La fecha de inicio no puede ser menor que la fecha de término";
	return "1|";
}

function validarFechas($fechaIni,$fechaFin)
{
	global $con;
	$res="1|";
	$fechaInicio=cambiaraFechaMysql($fechaIni);
	$fechaFin=cambiaraFechaMysql($fechaFin);
	if($fechaFin<$fechaInicio)
	{
		$res="La fecha de inicio no puede ser menor que la fecha de término";
		return $res;
	}
	else
	{
		return $res;
	}
}


function obtenerProfesoresCompatiblePerfilMateria($idGrupo)
{
	global $con;
	$consultaMateria="SELECT idMateria FROM 4520_grupos WHERE idGrupos='".$idGrupo."'";
	$idMateria=$con->obtenerValor($consultaMateria);
	$listCandidatos="";
	$consulta="SELECT DISTINCT(responsable) FROM _262_tablaDinamica WHERE cmbEspecialidad IN(SELECT idEspecialidad 
					FROM 4502_perfilMateria WHERE idMateria='".$idMateria."') and idEstado='3' ORDER BY responsable";
	
	$res=$con->obtenerFilas($consulta);	
	$numero=count($res);
	while($fila=mysql_fetch_row($res))
	{
				if($listCandidatos=="")
					$listCandidatos=$fila[0];
				else
					$listCandidatos.=",".$fila[0];
	}
	if($listCandidatos=="")
			$listCandidatos=-1;
	return "'".$listCandidatos."'";
}



function obtenerTokenLongitudCadena($cadena,$tamano)
{
	$cadAux="";
	$arrTokens=array();
	$ct=0;
	for($x=0;$x<strlen($cadena);$x++)
	{
		$cadAux.=$cadena[$x];
		$ct++;
		if($ct==$tamano)
		{
			$ct=0;
			array_push($arrTokens,$cadAux);
			$cadAux="";
		}
	}
	return $arrTokens;
}


function guardarMatricula($idRegistro)
{
	global $con;
	$consulta1="SELECT fechaCreacion,responsable,cmbPlantel,cmbCarrera,cmbModalidad,cmbTurno FROM _309_tablaDinamica 
				WHERE id__309_tablaDinamica='".$idRegistro."'";
	$datos=$con->obtenerPrimeraFila($consulta1);
	
	$consultaMatricula="SELECT txtMatricula FROM _459_tablaDinamica WHERE idReferencia='".$idRegistro."'";
	$matricula=$con->obtenerValor($consultaMatricula);
	
	$x=0;
	$consulta[$x]="begin";
	$x++;
	$consulta[$x]="INSERT INTO 4542_matriculaAlumno(idUsuario,matricula,fechaRegistro,idInstanciaPlan,idModalidad,idTurno,plantel)VALUES('".$datos[1]."'
					,'".$matricula."','".$datos[0]."','".$datos[3]."','".$datos[4]."','".$datos[5]."','".$datos[2]."')";
	$x++;					
	
	$consulta[$x]="UPDATE _309_tablaDinamica SET idEstado='6' WHERE id__309_tablaDinamica='".$idRegistro."'";
	$x++;
	
	$consulta[$x]="commit";
	$x++;
	$con->ejecutarBloque($consulta);
	echo "proceso terminado";
}

function guardarPromedioAdmon($idRegistro,$idFormulario)
{
	global $con;
	$consultaSede="SELECT DISTINCT(plantel) FROM _435_tablaDinamica WHERE idReferencia='".$idRegistro."'";
	$resSede=$con->obtenerFilas($consultaSede);
	while ($row= mysql_fetch_row($resSede))
	{
		$consultaDatos="SELECT id__435_tablaDinamica FROM _435_tablaDinamica WHERE plantel='".$row[0]."' AND idReferencia='".$idRegistro."' 
		 		 		AND idEstado='2'";
		$resDato=$con->obtenerFilas($consultaDatos);
		while ($fila= mysql_fetch_row($resDato))
		{
			$consultaUsuario="SELECT g.nombreDocente,g.puntos FROM _437_dtgAcademica AS g,_437_tablaDinamica AS t 
								WHERE g.idReferencia=t.id__437_tablaDinamica AND t.idReferencia='".$fila[0]."'";
			$evaAcademica=$con->obtenerPrimeraFila($consultaUsuario);
			$consultaServicios="SELECT g.puntos FROM _438_dtgServiciosEscolares AS g,_438_tablaDinamica AS t 
								WHERE g.idReferencia=t.id__438_tablaDinamica AND t.idReferencia='".$fila[0]."'";			
			$evaServicios=$con->obtenerPrimeraFila($consultaServicios);
			$consultaFinanzas="SELECT g.puntos FROM _439_dtgEvaluacionFinanzas AS g,_439_tablaDinamica AS t 
								WHERE g.idReferencia=t.id__439_tablaDinamica AND t.idReferencia='".$fila[0]."'";
			$evaFinanzas=$con->obtenerPrimeraFila($consultaFinanzas);
			$idUsuario=$evaAcademica[0];
			$puntosAcademica=$evaAcademica[1];
			$puntosServicios=$evaServicios[0];
			$puntosFinanzas=$evaFinanzas[0];
			$promedio=((($puntosAcademica+$puntosServicios)*2+$puntosFinanzas)*100)/110;
			
			$actualizar="INSERT INTO 4543_EvaluacionesAcademico(tipoEvaluacion,idConvocatoria,idReferencia,idUsuario,promedio,idFormulario)VALUES('1',
						'".$idRegistro."','".$fila[0]."','".$idUsuario."','".$promedio."','".$idFormulario."')";	
			$con->ejecutarConsulta($actualizar);
		}
	}
}

function guardarPromedioEvaluacionAlumnos($idRegistro,$idFormulario)
{
	global $con;
	$consultaSede="SELECT DISTINCT(Plantel) FROM 4520_grupos AS g,_233_tablaDinamica AS t WHERE g.idGrupos=t.idReferencia 
					AND t.idConvocatoria='".$idRegistro."'";
	$resSede=$con->obtenerFilas($consultaSede);
	while ($row= mysql_fetch_row($resSede))
	{
		$consultaProfe="SELECT SumaValores,idProfesor FROM _233_tablaDinamica AS t,4520_grupos AS g WHERE t.idReferencia=g.idGrupos 
						AND g.Plantel='".$row[0]."' AND t.idConvocatoria='".$idRegistro."'";
		$resDocente=$con->obtenerFilas($consultaProfe);
		while ($fila= mysql_fetch_row($resDocente))
		{
			$actualizar="INSERT INTO 4543_EvaluacionesAcademico(tipoEvaluacion,idConvocatoria,idUsuario,promedio,idFormulario)VALUES('2',
						'".$idRegistro."','".$fila[1]."','".$fila[0]."','".$idFormulario."')";	
			$con->ejecutarConsulta($actualizar);

		}
	}
}

function obtenerDocentesAprobados($idGrupo)
{
	global $con;
	$listCandidatos="";
	$consultaSede="SELECT Plantel FROM 4520_grupos WHERE idGrupos='".$idGrupo."'";
	$plantel=$con->obtenerValor($consultaSede);
	
	$consutaValorAprobatorio="SELECT txtpromedio FROM _315_tablaDinamica";
	$valor=$con->obtenerValor($consutaValorAprobatorio);
	
	

	$consultaConvo="SELECT MAX(cmbNumEvaluacion),id__440_tablaDinamica FROM _440_tablaDinamica 
					WHERE ciclo =(SELECT MAX(ciclo) FROM _440_tablaDinamica WHERE codigoInstitucion='".$plantel."' ) 
					AND codigoInstitucion='".$plantel."' GROUP BY ciclo ";
	$Convoca=$con->obtenerPrimeraFila($consultaConvo);
	$idConvocatoria=$Convoca[1];
	
	$consultaConvo1="SELECT MAX(cmbEvaluacion),id__443_tablaDinamica FROM _443_tablaDinamica 
						WHERE ciclo =(SELECT MAX(ciclo) FROM _443_tablaDinamica WHERE codigoInstitucion='".$plantel."') 
						AND codigoInstitucion='".$plantel."' GROUP BY ciclo";
	$Convoca1=$con->obtenerPrimeraFila($consultaConvo1);
	$idConvocatoria1=$Convoca1[1];
	
	$consultaUsuario="SELECT DISTINCT(idUsuario) FROM 4543_EvaluacionesAcademico WHERE idConvocatoria IN ('".$idConvocatoria."','".$idConvocatoria1."')";
	$registros=$con->obtenerFilas($consultaUsuario);
	while ($row= mysql_fetch_row($registros))
	{
		$consultaAdmon="SELECT AVG(promedio) FROM 4543_EvaluacionesAcademico WHERE idUsuario='".$row[0]."' AND idConvocatoria='".$idConvocatoria."' 
					AND idFormulario='440' group by idUsuario";
		$repu=$con->obtenerValor($consultaAdmon);
		if($repu!="")
		{
			$usuarioProm=$repu;
		}
		else
		{
			$usuarioProm=0;
		}
		
		$consultaAlumnos="SELECT AVG(promedio) FROM 4543_EvaluacionesAcademico WHERE idUsuario='".$row[0]."' AND idConvocatoria='".$idConvocatoria1."' 
					AND idFormulario='443' group by idUsuario";
		$rep1=$con->obtenerValor($consultaAlumnos);
		if($rep1!="")
		{
			$usuarioPromAlum=$rep1;
		}
		else
		{
			$usuarioPromAlum=0;
		}
		$promedioGeneral=(($usuarioProm+$usuarioPromAlum)/2)/100;
		if($promedioGeneral>=$valor)
		{
			if($listCandidatos=="")
				$listCandidatos=$fProfesor[0];
			else
				$listCandidatos.=",".$fProfesor[0];
		}
	}
	
	if($listCandidatos=="")
		$listCandidatos=-1;
	return "'".$listCandidatos."'";
}

function insertarFechaAutorizada($idRegistro)
{
	global $con;
	$consulta="SELECT codigoInstitucion,fechaInicio,fechaFin,txtDescripcion FROM _465_tablaDinamica WHERE id__465_tablaDinamica='".$idRegistro."'";
	$periodo=$con->obtenerPrimeraFila($consulta);
	
	$fecha=explode('-',$periodo[1]);
	$anio=$fecha[0];
	$consultaCiclo="SELECT idCiclo FROM 4525_fechaCalendarioDiaHabil WHERE YEAR(fechaInicio)='".$anio."'";
	$idCiclo=$con->obtenerValor($consultaCiclo);
	
	$fechaAutoinsert ="INSERT INTO 4525_fechaCalendarioDiaHabil(idCiclo,fechaInicio,fechaFin,plantel,descripcion) VALUES('".$idCiclo."',
						'".$periodo[1]."','".$periodo[2]."','".$periodo[0]."','".$periodo[3]."')";
	
	$con->ejecutarConsulta($fechaAutoinsert);
}

function validarFechasNoHabil($fechaIni,$fechaFin,$idOrganigrama)
{
	global $con;
	$res="1|";
	$fechaHoy=date('Y-m-d');
	$fechaInicio=cambiaraFechaMysql($fechaIni);
	$fechaFin=cambiaraFechaMysql($fechaFin);
	if($fechaFin<$fechaInicio)
	{
		$res="La fecha de inicio no puede ser menor que la fecha de termino";
		return $res;
	}
	
	if($fechaInicio<$fechaHoy)
	{
		$res="La fecha no esta actualizada";
		return $res;
	}

	$obtenerUnidad="SELECT codigoUnidad FROM 817_organigrama WHERE idOrganigrama='".$idOrganigrama."'";
	$unidad=$con->obtenerValor($obtenerUnidad);
	
	$consultaDia="SELECT idFechaCalendario,plantel FROM 4525_fechaCalendarioDiaHabil WHERE '".$fechaInicio."' BETWEEN FechaInicio AND fechaFin";
	$diaExistente=$con->obtenerPrimeraFila($consultaDia);
	
	
	if($diaExistente)
	{
		if($diaExistente[1]==""||$diaExistente[1]==$unidad)
		$res="La fecha ya existe en el calendario del sistema";
		return $res;
	}
	else
	{
		return $res;
	}
}

function obtenerMesLetra($mes)
{
	global $con;
	switch($mes)
	{
		case 1:
		return "ENERO";
		break;
		case 2:
		return "FEBRERO";
		break;
		case 3:
		return "MARZO";
		break;
		case 4:
		return "ABRIL";
		break;
		case 5:
		return "MAYO";
		break;
		case 6:
		return "JUNIO";
		break;
		case 7:
		return "JULIO";
		break;
		case 8:
		return "AGOSTO";
		break;
		case 9:
		return "SEPTIEMBRE";
		break;
		case 10:
		return "OCTUBRE";
		break;
		case 11:
		return "NOVIEMBRE";
		break;
		case 12:
		return "DICIEMBRE";
		break;
	}
}

function validarFechaCupo($fechaIni,$fechaFin,$cupoMin,$cupoMax)
{
	global $con;
	global $con;
	$res="1|";
	$fechaInicio=cambiaraFechaMysql($fechaIni);
	$fechaFin=cambiaraFechaMysql($fechaFin);
	if($fechaFin<$fechaInicio)
	{
		$res="La fecha de inicio no puede ser menor que la fecha de término";
		return $res;
	}
	else
	{
		if($cupoMax<$cupoMin)
		{
			$res="el cupo maximo no puede ser menor al cupo minimo";
			return $res;
		}
		else
		{
			return $res;
		}
	}
}

function insertarCursoExtraordinarios($idRegistro)
{
	global $con;
	$consulta="SELECT codigoInstitucion,dteFechaIni,dteFechaFin,txtCupoMin,txtCupoMax,txtClaveCurso,cmbCiclo,cmbPeriodos,cmbMateria,
				cmbPlan FROM _470_tablaDinamica where id__470_tablaDinamica='".$idRegistro."'";
	$registro=$con->obtenerPrimeraFila($consulta);
	
	$consulta1="SELECT idInstanciaPlanEstudio FROM 4513_instanciaPlanEstudio WHERE sede='".$registro[0]."' AND idPlanEstudio='".$registro[9]."' 
					AND situacion='1'";
	$idInstancia=$con->obtenerValor($consulta1);
	
	$insertaDatos="INSERT INTO 4520_grupos(idPlanEstudio,Plantel,idMateria,nombreGrupo,cupoMinimo,cupoMaximo,fechaInicio,fechaFin,situacion,
					idCiclo,idInstanciaPlanEstudio,idPeriodo)VALUES('".$registro[9]."','".$registro[0]."','".$registro[8]."','".$registro[5]."',
					'".$registro[3]."','".$registro[4]."','".$registro[1]."','".$registro[2]."','1','".$registro[6]."','".$idInstancia."','".$registro[7]."')";
					
	$con->ejecutarConsulta($insertaDatos);
}

function profesoresPlantel($idGrupo)
{
	global $con;
	$listCandidatos="2";

	$consulta="SELECT plantel FROM 4520_grupos WHERE  idGrupos='".$idGrupo."' ";	
	$codigoUnidad=$con->obtenerValor($consulta);
	
	$usuario="SELECT DISTINCT(a.idUsuario) FROM 801_adscripcion AS a,807_usuariosVSRoles AS r WHERE a.idUsuario=r.idUsuario 
				AND r.idRol='5' AND a.Institucion='".$codigoUnidad."' order by a.idUsuario";
	$res=$con->obtenerFilas($usuario);	
	
	$usuarioSede="SELECT a.cmbDocente FROM _473_tablaDinamica AS a,_473_gridSedes AS s WHERE s.idReferencia=a.id__473_tablaDinamica 
					AND s.plantel='".$codigoUnidad."'";
	$res2=$con->obtenerFilas($usuarioSede);	
	
		while($fila=mysql_fetch_row($res))
		{
					if($listCandidatos=="")
						$listCandidatos=$fila[0];
					else
						$listCandidatos.=",".$fila[0];
		}
		while($fila2=mysql_fetch_row($res2))
		{
					if($listCandidatos=="")
						$listCandidatos=$fila2[0];
					else
						$listCandidatos.=",".$fila2[0];
		}
		if($listCandidatos=="")
				$listCandidatos=-1;
		return "'".$listCandidatos."'";			
}

function justificacionFalta($idRegistro)
{
	global $con;
	$consulta="SELECT responsable,cmbTipoIncidencia,dteFechaIncidencia,horaInicial,horaFin,cmbPlanteles,radPuesto FROM _481_tablaDinamica 
				WHERE id__481_tablaDinamica='".$idRegistro."' ";
	$justificacion=$con->obtenerPrimeraFila($consulta);
	
	$guardarJustificacion="INSERT INTO 9106_Justificaciones(fecha_Inicial,fecha_Final,idUsuario,tipo,horaInicial,horaFinal,plantel,puesto
							,idFormulario,referencia) VALUES('".$justificacion[2]."','".$justificacion[2]."','".$justificacion[0]."'
							,'".$justificacion[1]."','".$justificacion[3]."','".$justificacion[4]."','".$justificacion[5]."','".$justificacion[6]."'
							,'481','".$idRegistro."')";
	$con->ejecutarConsulta($guardarJustificacion);
	
}

function validarHorasJustificada($idFalta,$tipoReposicion,$horasJ)//UGM
{
	global $con;
	$horasT=0;
	$rep="1|";
	if($tipoReposicion=='3')
	{
		$consulta="SELECT horaInicial,horaFinal FROM 4559_controlDeFalta WHERE idFalta='".$idFalta."'";
		$falta=$con->obtenerPrimeraFila($consulta);
		if($falta)
		{
			$horaI=strtotime($falta[0]);
			$horaF=strtotime($falta[1]);
			$diferencia=strtotime("00:00:00")+strtotime($falta[1])-strtotime($falta[0]);
			$horasT=((date("H",$diferencia))+date("i",$diferencia));
		}
		if($horasT<$horasJ)
		{
			$rep="La hora de Reposición es mayor a la hora justificada";
			return $rep;
		}
		return $rep;
	}
	return $rep;
}

function actualizarSituacionFalta($idRegistro,$situacion,$idFormulario)//UGM
{
	//$situcion=1=Autorizada,2=Rechazada
	global $con;
	$consulta="SELECT cmbFalta FROM _481_tablaDinamica WHERE id__481_tablaDinamica='".$idRegistro."'";
	$idFalta=$con->obtenerValor($consulta);
	
	
	
	
	$actualizar="UPDATE 4559_controlDeFalta SET estadoFalta='".$situacion."',idFormulario='".$idFormulario."',
				idRegistrojustificacion='".$idRegistro."' WHERE idFalta='".$idFalta."'";
	if($con->ejecutarConsulta($actualizar))
	{
		if($situacion==1)
		{
			$consulta="SELECT pagado,idNomina,idUsuario,fechaFalta,horaInicial,horaFinal,idGrupo FROM 4559_controlDeFalta WHERE idFalta=".$idFalta;
			$fFalta=$con->obtenerPrimeraFila($consulta);
			
			
			if($fFalta[0]==2)
			{
				$consulta="SELECT nombreGrupo FROM 4520_grupos WHERE idGrupos=".$fFalta[6];
				$nombreGrupo=$con->obtenerValor($consulta);
				$notificacion="se ha justificado la falta del profesor: ".cv(obtenerNombreUsuario($fFalta[2]))." en el grupo: ".cv($nombreGrupo)." de la sesión del día: ".
							date("d/m/Y",strtotime($fFalta[3]))." de las ".date("H:i",strtotime($fFalta[4]))." a las ".date("H:i",strtotime($fFalta[5]));
				registrarNotificacionNomina($fFalta[1],$notificacion);	
			}
		}
	}
}

function validarFechasComision($fechaIni,$fechaFin,$valor)
{
	global $con;
	$res="1|";
	$fechaInicio=cambiaraFechaMysql($fechaIni);
	$fechaFin=cambiaraFechaMysql($fechaFin);
	if($fechaFin<$fechaInicio)
	{
		$res="La fecha de inicio no puede ser menor que la fecha de término";
		return $res;
	}
	else
	{
		$consulta="SELECT id__489_tablaDinamica FROM _489_tablaDinamica WHERE txtFolioAutorizacion='".$valor."'";
		$numero=$con->obtenerValor($consulta);
		if($numero)
		{
			$res="El Folio ".$valor." ya existe ";
			return $res;
		}
		return $res;
	}
}

function validarDatosFormularioIncripcion($correo,$correo2)
{
	global $con;
	$res="1|";
	if(strtoupper($correo)!=strtoupper($correo2))
	{
		$res="Los correos electronicos son diferentes";
		return $res;
	}
	else
	{
		$consulta="SELECT idUsuario FROM 805_mails WHERE Mail='".$correo."'";
		$existe=$con->obtenerValor($consulta);
		if($existe>0)
		{
			$res="Correo ".$correo." ya existente en nuestra Base de datos";
			return $res;
		}
		else
		{
			return $res;
		}
	}
}

function validarDisponibilidadH($idUsuario,$idCiclo,$idPeriodo)
{
	global $con;
	$res="1|";
	
	$consulta="SELECT COUNT(*) FROM _390_tablaDinamica WHERE responsable='".$idUsuario."' AND idCiclo='".$idCiclo."' AND idPeriodo='".$idPeriodo."' AND idEstado=2";
	$resFecha=$con->obtenerValor($consulta);
	if($resFecha>=1)
	{
		$res="Ya ha ingresado su disponibilidad para este periodo";
		return $res;
	}
	return $res;
}


?>