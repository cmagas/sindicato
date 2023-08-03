<?php include_once("latis/funcionesNeotrai.php");

function obtenerMatriculaInterna($plantel,$idInstanciaPlan,$idCiclo,$idRegistro) //1349917980
{
	global $con;
	$consultaCiclo="SELECT nombreCiclo FROM 4526_ciclosEscolares WHERE idCiclo='".$idCiclo."'";
	$resAnio=$con->obtenerValor($consultaCiclo);
	$anio=substr($resAnio,2,2);

	$consultaCampus="SELECT codigoDepto FROM 817_organigrama WHERE codigoUnidad='".$plantel."'";
	$campus=$con->obtenerValor($consultaCampus);
	
	$areaEstudio="SELECT AreaEspecialidad FROM 4500_planEstudio p,4513_instanciaPlanEstudio i WHERE p.idPlanEstudio=i.idPlanEstudio 
					AND i.idInstanciaPlanEstudio='".$idInstanciaPlan."'";
	$area=$con->obtenerValor($areaEstudio);
	
	$consulNumero="SELECT folioActual FROM 4572_folioMatricula WHERE codigoUnidad='".$plantel."' AND idCiclo='".$idCiclo."'";
	$consecutivo=$con->obtenerValor($consulNumero);
	if($consecutivo=="")
	{
		$numero='1';
	}
	else
	{
		$numero=$consecutivo+1;
	}
	
	$longitud=strlen($numero);
	switch($longitud)
	{
		case 1:
			$numConsecutivo='000'.$numero;
		break;
		case 2:
			$numConsecutivo='00'.$numero;
		break;
		case 3:
			$numConsecutivo='0'.$numero;
		break;
		default:
			$numConsecutivo=$numero;
	}
	
	// caso campus de un digito = año-Campus-area-numero sConsecutivo
	// Caso campus de 2 digitos = año-campus-numero cnsecutivo
	if($campus>9)
	{
		$matricula=$anio.$campus.$numConsecutivo;
	}
	else
	{
		$matricula=$anio.$campus.$area.$numConsecutivo;
	}
	
		$x=0;
		$consulta[$x]="begin";
		$x++;
	
	if($consecutivo=="")
	{
		$consulta[$x]="INSERT INTO 4572_folioMatricula(codigoUnidad,idCiclo,folioActual)VALUES('".$plantel."','".$idCiclo."','".$numero."')";
		$x++;
		$consulta[$x]="UPDATE _678_tablaDinamica SET txtMatricula='".$matricula."' WHERE id__678_tablaDinamica='".$idRegistro."'";
		$x++;
	}
	else
	{
		$consulta[$x]="update 4572_folioMatricula set folioActual='".$numero."' where codigoUnidad='".$plantel."' and idCiclo='".$idCiclo."'";
		$x++;
		$consulta[$x]="UPDATE _678_tablaDinamica SET txtMatricula='".$matricula."' WHERE id__678_tablaDinamica='".$idRegistro."''";
		$x++;
	}
		$consulta[$x]="commit";
		$x++;
		$con->ejecutarBloque($consulta);
	
	return $matricula;
}

function guardarDatosLayout($idFormulario,$idRegistro)
{
	global $con;
	$fechaReporte=date("d-m-Y");
	$fechaOperacion=date("Y-m-d H:i:s");
	
	$consultarRegistro="SELECT cmbBanco,datRutaArchivo,b.nombreBanco,responsable,codigoInstitucion FROM _700_tablaDinamica t,6000_bancos b WHERE t.cmbBanco=b.idBanco 
						AND id__700_tablaDinamica='".$idRegistro."'";				
	$datosR=$con->obtenerPrimeraFila($consultarRegistro);
	$idBanco=$datosR[0];
	$ruta1=$datosR[1];
	$nombreBanco=$datosR[2];
	$ruta="../documentosUsr/archivo_".$ruta1;
	$archivo=fopen($ruta,"r") or die("No se puedo abrir el archivo");
			
		switch($idBanco)
		{
			case 2://banamex
					$x=0;
					$consulta[$x]="begin";
			 		$x++;
					$consulta[$x]="INSERT INTO _701_tablaDinamica(idReferencia,fechaCreacion,responsable,codigoInstitucion,txtBanco)VALUES('".$idRegistro."'
									,'".$fechaOperacion."','".$datosR[3]."','".$datosR[4]."','".$nombreBanco."')";
					$x++;
					$consulta[$x]="set @idRegistro=(select last_insert_id())";
					$x++;
					
					while(!feof($archivo))
					{
						$linea=fgets($archivo);
						$indice=substr($linea,0,1);
						if($indice==1)
						{
							$fechaM=substr($linea,1,6);
							$fechaPago=componerFecha($fechaM,'2');
							$importeS=substr($linea,7,15);
							$importe=afectarImporte($importeS,$idBanco);
							$refeS=substr($linea,47,44);
							$concepto=obtenerConcepto($refeS,$idBanco);
							$referencia=obtenerReferencia($refeS,$idBanco);
							$leyendaReferencia=validarReferencia($referencia);
							list($validarRef,$observa)=explode("_",$leyendaReferencia);
							$consulta[$x]="INSERT INTO _701_gridDetalle(idReferencia,referencia,concepto,fechaRegistro,importePago,procesado,observaciones)VALUES(@idRegistro,'".$referencia."',
											'".$concepto."','".$fechaPago."','".$importe."','".$validarRef."','".$observa."')";
							$x++;
						}
					}
					$consulta[$x]="commit";
					$x++;
					$resp=$con->ejecutarBloque($consulta);
					if($resp!=1)
					{
						echo "Proceso no realizado";	
					}
			break;
			case 3://serfin
					$x=0;
					$consulta[$x]="begin";
					$x++;
					$consulta[$x]="INSERT INTO _701_tablaDinamica(idReferencia,fechaCreacion,responsable,codigoInstitucion,txtBanco)VALUES('".$idRegistro."'
									,'".$fechaOperacion."','".$datosR[3]."','".$datosR[4]."','".$nombreBanco."')";
					$x++;
					$consulta[$x]="set @idRegistro=(select last_insert_id())";
					$x++;
			
					while(!feof($archivo))
					{
						$linea=fgets($archivo);
						$indice=substr($linea,0,1);
						if($indice>=5)
						{
							$fechaM=substr($linea,16,8);
							$fechaPago=componerFecha($fechaM,'4');
							$importeS=substr($linea,73,14);
							$importe=afectarImporte($importeS,$idBanco);
							$referencia=substr($linea,112,22);
							$leyendaReferencia=validarReferencia($referencia);
							list($validarRef,$observa)=explode("_",$leyendaReferencia);
							$concepto=obtenerConcepto($referencia,$idBanco);
							$consulta[$x]="INSERT INTO _701_gridDetalle(idReferencia,referencia,concepto,fechaRegistro,importePago,procesado,observaciones)VALUES(@idRegistro,'".$referencia."',
											'".$concepto."','".$fechaPago."','".$importe."','".$validarRef."','".$observa."')";
							$x++;	
						}
					}
					$consulta[$x]="commit";
					$x++;
					$resp=$con->ejecutarBloque($consulta);
					if($resp!=1)
					{
						echo "Proceso no realizado";	
					}
			break;
			case 4://hsbc
					$x=0;
					$consulta[$x]="begin";
					$x++;
					$consulta[$x]="INSERT INTO _701_tablaDinamica(idReferencia,fechaCreacion,responsable,codigoInstitucion,txtBanco)VALUES('".$idRegistro."'
									,'".$fechaOperacion."','".$datosR[3]."','".$datosR[4]."','".$nombreBanco."')";
					$x++;
					$consulta[$x]="set @idRegistro=(select last_insert_id())";
					$x++;
					while(!feof($archivo))
					{
						$linea=fgets($archivo);
						if(trim($linea)!="")
						{
							$fechaM=substr($linea,18,10);
							$fechaPago=componerFecha($fechaM,'3');
							$importeS=substr($linea,28,13);
							$importe=afectarImporte($importeS,$idBanco);
							$referencia=substr($linea,41,22);
							$leyendaReferencia=validarReferencia($referencia);
							list($validarRef,$observa)=explode("_",$leyendaReferencia);
							$concepto=obtenerConcepto($referencia,$idBanco);
							$consulta[$x]="INSERT INTO _701_gridDetalle(idReferencia,referencia,concepto,fechaRegistro,importePago,procesado,observaciones)VALUES(@idRegistro,'".$referencia."',
											'".$concepto."','".$fechaPago."','".$importe."','".$validarRef."','".$observa."')";
							$x++;	
						}
					}
					$consulta[$x]="commit";
					$x++;
					$resp=$con->ejecutarBloque($consulta);
					if($resp!=1)
					{
						echo "Proceso no realizado";	
					}
			break;
			case 5://OXXO
					$x=0;
					$consulta[$x]="begin";
					$x++;
					$consulta[$x]="INSERT INTO _701_tablaDinamica(idReferencia,fechaCreacion,responsable,codigoInstitucion,txtBanco)VALUES('".$idRegistro."'
									,'".$fechaOperacion."','".$datosR[3]."','".$datosR[4]."','".$nombreBanco."')";
					$x++;
					$consulta[$x]="set @idRegistro=(select last_insert_id())";
					$x++;
					
					while(!feof($archivo))
					{
						$linea=fgets($archivo);
						$datos=explode(",",$linea);
						$tipoMov=$datos[0];
						if($tipoMov==1)
						{
							$fechaM=$datos[2];
							$fechaMov=componerFecha($fechaM,'1');//Fecha tipo 20121112
							$horaMov=$datos[3].":00";
							$fechaPago=$fechaMov." ".$horaMov;
							$cadenaReferencia=$datos[4];
							$referencia=obtenerReferencia($cadenaReferencia,$idBanco);
							$leyendaReferencia=validarReferencia($referencia);
							list($validarRef,$observa)=explode("_",$leyendaReferencia);
							$concepto=obtenerConcepto($referencia,$idBanco);
							$pago=$datos[6];
							$pago1=$pago*1;
							$consulta[$x]="INSERT INTO _701_gridDetalle(idReferencia,referencia,concepto,fechaRegistro,importePago,procesado,observaciones)VALUES(@idRegistro,'".$referencia."',
											'".$concepto."','".$fechaPago."','".$pago1."','".$validarRef."','".$observa."')";
							$x++;				
						}
					}
					$consulta[$x]="commit";
					$x++;
					$resp=$con->ejecutarBloque($consulta);
					if($resp!=1)
					{
						echo "Proceso no realizado";	
					}
			break;
		}
	fclose($archivo);
}

	function componerFecha($fecha,$tipo)
	{
		switch($tipo)
		{
			case 1://20121011
					$anio=substr($fecha,0,4);
					$mes=substr($fecha,4,2);
					$dia=substr($fecha,6,2);
					$fechaD=$anio."-".$mes."-".$dia;
			break;
			case 2://121116
					$anio=substr($fecha,0,2);
					$mes=substr($fecha,2,2);
					$dia=substr($fecha,4,2);
					$fechaD="20".$anio."-".$mes."-".$dia;
			break;
			case 3://06/11/2012
					$fechaD=cambiaraFechaMysql($fecha);
			break;
			case 4://09112012
					$anio=substr($fecha,4,4);
					$mes=substr($fecha,2,2);
					$dia=substr($fecha,0,2);
					$fechaD=$anio."-".$mes."-".$dia;
			break;
		}
		return $fechaD;
	}
	
	function obtenerReferencia($referencia,$idBanco)
	{
		switch($idBanco)
		{
			case 2:
					$matricula=substr($referencia,0,8);
					$concepto=substr($referencia,30,5);
					$periodo=substr($referencia,35,4);
					$grupo=substr($referencia,39,5);
					$refeFinal=$matricula.$concepto.$periodo.$grupo;
					return $refeFinal;
			break;
			case 5:
					$ref=substr($referencia,3,14);
			break;
		}
		return $ref;
	}
	
	function afectarImporte($importe,$idBanco)//125030 a 1250.30
	{
		switch($idBanco)
		{
			case 2:
					$entero=substr($importe,0,13);
					$decimal=substr($importe,13,2);
					$valor=($entero*1).".".$decimal;
			break;
			case 3:
					$entero=substr($importe,0,12);
					$decimal=substr($importe,12,2);
					$valor=($entero*1).".".$decimal;
			break;
			case 4:
					$entero=substr($importe,0,11);
					$decimal=substr($importe,11,2);
					$valor=($entero*1).".".$decimal;
			break;
		}
		return $valor;
	}
	
	function obtenerConcepto($referencia,$idBanco)
	{
		global $con;
		switch($idBanco)
		{
			case 2:
					$concepto=(substr($referencia,30,5)*1);
					$nConcepto="SELECT nombreConcepto FROM 561_conceptosIngreso WHERE (cveConcepto*1)='".$concepto."'";
					$res=$con->obtenerValor($nConcepto);
					return $res;
			break;
			case 3:
					$concepto=(substr($referencia,8,5)*1);
					$nConcepto="SELECT nombreConcepto FROM 561_conceptosIngreso WHERE (cveConcepto*1)='".$concepto."'";
					$res=$con->obtenerValor($nConcepto);
					return $res;
			break;
			case 4:
					$concepto=(substr($referencia,8,5)*1);
					$nConcepto="SELECT nombreConcepto FROM 561_conceptosIngreso WHERE (cveConcepto*1)='".$concepto."'";
					$res=$con->obtenerValor($nConcepto);
					return $res;
			break;
			case 5:
					$concepto=(substr($referencia,8,3)*1);
					$nConcepto="SELECT nombreConcepto FROM 561_conceptosIngreso WHERE (cveConcepto*1)='".$concepto."'";
					$res=$con->obtenerValor($nConcepto);
					return $res;
			break;
			
		}
	}
	
	function limpiarDatosRegistro($idFormulario,$idRegistro)
	{
		global $con;
		$obtenerRef="SELECT id__701_tablaDinamica FROM _701_tablaDinamica WHERE idReferencia='".$idRegistro."'";
		$res=$con->obtenerValor($obtenerRef);
		$x=0;
		$consulta[$x]="begin";
		$x++;
		$consulta[$x]="DELETE FROM _700_tablaDinamica WHERE id__700_tablaDinamica='".$idRegistro."'";
		$x++;
		$consulta[$x]="DELETE FROM _701_tablaDinamica WHERE idReferencia='".$idRegistro."'";
		$x++;
		$consulta[$x]="DELETE FROM _701_gridDetalle WHERE idReferencia='".$res."'";
		$x++;
		$consulta[$x]="commit";
		$x++;
		$resp=$con->ejecutarBloque($consulta);
	}
	
	function validarReferencia($referencia)
	{
		global $con;
		$res="0_REFERENCIA NO ENCONTRADA";
		$consulta="SELECT idMovimiento,pagado FROM 6011_movimientosPago WHERE idReferencia='".$referencia."'";
		$resul=$con->obtenerPrimeraFila($consulta);
		if($resul)
		{
			if($resul[1]=='1')
			{
				$res="0_REFERENCIA AFECTADA";
			}
			else
			{
				$res="1_REFERENCIA EN ESPERA DE PAGO";
			}
		}
		return $res;
	}

	function afectarReferenciaBanco($idRegistro)
	{
		global $con;
		$consulta="SELECT id__701_gridDetalle,referencia,fechaRegistro,importePago,t.responsable,o.cmbBanco FROM _701_gridDetalle g,
					_701_tablaDinamica t,_700_tablaDinamica o WHERE g.idReferencia=t.id__701_tablaDinamica AND t.idReferencia=o.id__700_tablaDinamica 
					AND t.idReferencia='".$idRegistro."' AND procesado='1'";
		$res_consulta=$con->obtenerFilas($consulta);
		while ( $filas = mysql_fetch_row ( $res_consulta) )
		{
			afectarPagoReferenciado($filas[1],$filas[2],$filas[3],$filas[4],$filas[5]);
		}
	}

	function afectarPagoReferenciado($referencia,$fechaPago,$importe,$idResponsable,$idBanco)
	{
		global $con;
		$fechaProceso=date("Y-m-d");
		$consulta="UPDATE 6011_movimientosPago SET pagado='1',fechaPago='".$fechaPago."',idPerfilPago='".$idBanco."',fechaRegistroPago='".$fechaProceso."',
					montoPagado='".$importe."',idResponsableAsiento='".$idResponsable."' WHERE idReferencia='".$referencia."' AND pagado='0'";
		if($con->ejecutarConsulta($consulta))
		{
			$consulta="select idFuncionEjecucion,tipoFuncion,idMovimiento from 	6011_movimientosPago where idReferencia=".$referencia;
			$fila=$con->obtenerPrimeraFila($consulta);
			if($fila[0]!="")
			{
				if($fila[1]==1)
				{
					$cadObj='{"idMovimiento":"'.$fila[2].'"}';
					$obj=json_decode($cadObj);
					$cache=NULL;
					resolverExpresionCalculoPHP($fila[0],$obj,$cache);
				}
				else
				{
					eval($fila[0]."(".$fila[2].");");
				}
			}
		}
		
	}

?>