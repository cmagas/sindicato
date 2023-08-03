<?php

	include_once("utiles.php");

//crearNombreCompletoProductos(1);

function enviarPromocionesSommeil($idRegistro)
{
	//global $con;
//	$destinatario="";
//	$asunto="";
//	$cuerpo="";
//	$X=0;
//	
//	//$idRegistro=4;
//	//$tipoCorreo=2;
//	
//	$emisor="reservaciones@sommeil.mx";
//	$consulta="SELECT * FROM _1149_tablaDinamica WHERE id__1149_tablaDinamica='".$idRegistro."'";
//	$resp=$con->obtenerPrimeraFila($consulta);
//	
//	$tipoCorreo=$resp[12];
//	$asunto=$resp[10];
//	$cuerpo=$resp[11];
//	
//	if($tipoCorreo==1)
//	{
//		echo "1";
//		$consultaCorreos="SELECT correo FROM _1081_emailContacto WHERE situacion=0";
//		$resCorreo=$con->obtenerFilas($consultaCorreos);
//		
//	  	while ($fila= mysql_fetch_row($resCorreo))
//	  	{
//		  $destinatario=$fila[0];
//		  	enviarMail($destinatario,$asunto,$cuerpo,$emisor);
//			$X++;
//	  	}
//		echo "Eventos finalizado 2".$X;
//	}
//	else
//	{
//		
//		$emailAdmon=array();
//		$emailAdmon[0]="carlosmario07@hotmail.com";
//		//$emailAdmon[1]="lemsamanta@gmail.com";
//		$emailAdmon[1]="reservacionessommeil@gmail.com";
//		
//		foreach($emailAdmon as $email)
//		{
//			$destinatario=$email;
//	 		enviarMail($destinatario,$asunto,$cuerpo,$emisor);
//			
//		}
//		echo "Evento finalizado";
//	}
}

function crearNuevoGasto($idRegistro)
{
	//global $con;
//	
//	$fechaHoy=date("Y-m-d");
//	
//	$consulta2="SELECT * FROM _1164_tablaDinamica WHERE idReferencia='".$idRegistro."'";
//	$valor=$con->obtenerPrimeraFila($consulta2);
//
//	$tipoPago=$valor[14];
//	
//	if($tipoPago==2)
//	{
//		$consulta1="SELECT * FROM _1163_tablaDinamica WHERE id__1163_tablaDinamica='".$idRegistro."'";
//		$res1=$con->obtenerPrimeraFila($consulta1);
//		
//		$responsable=$res1[3];
//		$idEstado=2;
//		$fechaNuevoPago=$valor[15];
//		$centroCosto=$res1[11];
//		$importeNuevoPago=$valor[16];
//		$observaciones=$valor[12];
//		$formaPago=$res1[14];
//		$concepto=$res1[15];
//		$cajaChica=$valor[17];
//		$descDetallada=$res1[19];
//		$cuentaNum=$res1[20];
//		
//		$x=0;
//		$consulta[$x]="begin";
//		$x++;
//	
//		$consulta[$x]="INSERT INTO _1163_tablaDinamica(idReferencia,fechaCreacion,responsable,idEstado,codigoInstitucion,fechaPago,
//		centroCosto,importePago,observaciones,formaPago,concepto,cajaChica,idReferenciaGastoAnt,descripcionDetallada,numCuenta)VALUES('-1',
//		'".$fechaHoy."','".$responsable."','".$idEstado."','0001','".$fechaNuevoPago."','".$centroCosto."','".$importeNuevoPago."',
//		'".$observaciones."','".$formaPago."',		'".$concepto."','".$cajaChica."','".$idRegistro."','".$descDetallada."','".$cuentaNum."')";
//		$x++;
//		$consulta[$x]="set @idRegistro:=(select last_insert_id())";
//		$x++;
//		$consulta[$x]="UPDATE _1163_tablaDinamica SET codigo=@idRegistro WHERE id__1163_tablaDinamica=@idRegistro";
//		$x++;
//		$consulta[$x]="commit";
//		$x++;
//		$res=0;
//		
//		if($con->ejecutarBloque($consulta))
//			$res=1;
//	}
}

function registrarIngresosSommeil($idRegistro)
{
	//global $con;
//	
//	$consultar="SELECT * FROM _1166_tablaDinamica WHERE id__1166_tablaDinamica='".$idRegistro."'";
//	$res=$con->obtenerPrimeraFila($consultar);
//	
//	$tipoMovimiento=$res[15];
//	$fechaMov=$res[2];
//	$responsable=$res[3];
//	$codigoI='0001';
//	$importe=$res[14];
//	$fechaCobro=$res[11];
//	
//	$x=0;
//	$consulta[$x]="begin";
//	$x++;
//	
//	if($tipoMovimiento==1)//1=Venta, 2=Programado
//	{
//		$consulta[$x]="INSERT INTO _1167_tablaDinamica(idReferencia,fechaCreacion,responsable,codigoInstitucion,importeCobrado,
//		fechaCobro)VALUES('".$idRegistro."','".$fechaMov."','".$responsable."','".$codigoI."','".$importe."','".$fechaCobro."')";
//		$x++;
//		
//		$consulta[$x]="set @idRegistroN:=(select last_insert_id())";
//		$x++;
//		
//		$consulta[$x]="UPDATE _1167_tablaDinamica SET codigo=@idRegistroN WHERE idReferencia='".$idRegistro."'";
//		$x++;
//		$consulta[$x]="commit";
//		$x++;
//		
//		if($con->ejecutarBloque($consulta))
//		{
//			cambiarEtapaFormulario1166($idRegistro,"3");		
//		}
//		else
//		{
//			cambiarEtapaFormulario1166($idRegistro,"1");		
//		}
//	}
//	else
//	{
//		cambiarEtapaFormulario1166($idRegistro,"2");		
//	}
}

function cambiarEtapaFormulario1166($idRegistro,$etapa)
{
	//global $con;
//	
//	$consulta="UPDATE _1166_tablaDinamica SET idEstado='".$etapa."' WHERE id__1166_tablaDinamica='".$idRegistro."'";
//	$con->ejecutarConsulta($consulta);	
}

function asignarIVARegistro($idFormulario,$idRegistro)
{
	//global $con;
//	$consulta="SELECT id__1209_tablaDinamica,nombreImpuesto FROM _1209_tablaDinamica ";
//	$arrImpuestos=$con->obtenerFilasArreglo($consulta);
//	$valor=$con->obtenerValor($consulta);
//	echo "window.parent.asignarIVACombo(".$idRegistro.",".$arrImpuestos.");window.parent.cerrarVentanaFancy();return;";
}

function guardarAdeudoReservacion($idRegistro,$tipoOperacion) //Tipo operacion: 1=Guardar, 2=cancelar
{
	global $con;
	$formulario='670';
	$fechaC=date("Y-m-d");
	$abono=0;
	$etapa=3; //Sin anticipo
	$importeVenta=0;
	
	$x=0;
	$consulta[$x]="begin";
	$x++;
	
	switch($tipoOperacion)
	{
		case 1:
				$abono=obtenerAbonosReservasSommeil($idRegistro);
				
				if($abono>0)
				{
					$etapa=2;					
				}
				
				$consultar="SELECT * FROM _670_tablaDinamica WHERE id__670_tablaDinamica='".$idRegistro."'";
				$resp=$con->obtenerPrimeraFila($consultar);
	
				$idCliente=$resp[12];
				$montoTotal=$resp[17];
				$montoDescuento=$resp[18];
				$montoSubtotal=$resp[19];
				$fechaIngreso=$resp[10];
				$fechaSalida=$resp[11];
				//$importeAdeudo=$montoTotal-$abono;
				
				$consulta[$x]="DELETE FROM 6942_adeudos WHERE tablaRef='".$formulario."' AND idReferencia='".$idRegistro."'";
				$x++;
				
				$consulta[$x]="INSERT INTO 6942_adeudos(tipoAdeudo,idReferencia,fechaCreacion,subtotal,iva,total,tipoCliente,idCliente,situacion,
								fechaVencimiento,naturalezaAdeudo,tablaRef,fechaInicioRes,fechaFinRes)VALUES('1','".$idRegistro."','".$fechaC."','0','0','".$montoTotal."','5',
								'".$idCliente."','2','".$fechaC."','1','".$formulario."','".$fechaIngreso."','".$fechaSalida."')";
				$x++;
				$consulta[$x]="UPDATE _670_tablaDinamica SET idEstado='".$etapa."' WHERE id__670_tablaDinamica='".$idRegistro."'";
				$x++;		
		break;
		case 2:
				$consulta[$x]="UPDATE 6942_adeudos SET situacion='4' WHERE idReferencia='".$idRegistro."'";
				$x++;
		break;
	}
	
	$consulta[$x]="commit";
	$con->ejecutarBloque($consulta);
}

function obtenerCostoPaqueteSommeil($idRegistro)
{
	//global $con;
//	$valor=0;
//	$consulta="SELECT importeTotal FROM _1250_tablaDinamica WHERE id__1250_tablaDinamica='".$idRegistro."'";
//	if($resp=$con->obtenerValor($consulta))
//	{
//		return $resp;
//	}
//	else
//	{
//		return $valor;
//	}
}

function crearNombreCompletoProductos($idRegistro)
{
	//global $con;
//	
//	$x=0;
//	$consulta[$x]="begin";
//	$x++;
//	
//	
//	$consultar="SELECT * FROM _1220_tablaDinamica WHERE id__1220_tablaDinamica='".$idRegistro."'";
//	$res=$con->obtenerPrimeraFila($consultar);
//	
//	$nombreProducto=$res[10];
//	$idMarcaProducto=$res[22];
//	$idPresentacion=$res[24];
//	$capacidad=$res[25];
//	$idUnidadDetalle=$res[26];
//	
//	$consultaMarca="SELECT nombreMarca FROM _1249_tablaDinamica WHERE id__1249_tablaDinamica='".$idMarcaProducto."'";
//	$marca=$con->obtenerValor($consultaMarca);
//	
//	$consultaPresentacion="SELECT nombrePresentacion FROM _1254_tablaDinamica WHERE id__1254_tablaDinamica='".$idPresentacion."'";
//	$presentacion=$con->obtenerValor($consultaPresentacion);
//	
//	$consultaUnidad="SELECT  abreviatura FROM _1253_tablaDinamica WHERE id__1253_tablaDinamica='".$idUnidadDetalle."'";
//	$unidad=$con->obtenerValor($consultaUnidad);
//	
//	$nombreLargo=strtoupper($nombreProducto." [".$marca."] ".$presentacion." ".$capacidad." ".$unidad);
//	
//	$consulta[$x]="DELETE FROM 1220_descripcionProducto WHERE idProducto='".$idRegistro."'";
//	$x++;
//	$consulta[$x]="INSERT INTO 1220_descripcionProducto(idProducto,descripcion) VALUE('".$idRegistro."','".$nombreLargo."')";
//	$x++;
//	
//	$consulta[$x]="commit";
//	$x++;
//	$con->ejecutarBloque($consulta);
}

function recalcularImportePaquetes($idRegistro)
{
	global $con;
	$valor=0;
	
	$consultar="SELECT SUM(montoTotal) FROM _679_contenidoPaquete WHERE idReferencia='".$idRegistro."'";
	$res=$con->obtenerValor($consultar);
	if($res)
	{
		$valor=$res;
	}

	$consulta="UPDATE _679_tablaDinamica SET sumaTotal='".$valor."' WHERE id__679_tablaDinamica='".$idRegistro."'";
	$con->ejecutarconsulta($consulta);
}

function obtenerAbonosReservasSommeil($idRegistro)
{
	global $con;
	$valor=0;
	
	$consulta="SELECT SUM(montoPago) FROM _680_tablaDinamica WHERE iFormulario='670' AND iRegistro='".$idRegistro."'";
	$res=$con->obtenerValor($consulta);
	if($res)
	{
		$valor=$res;
	}
	
	return $valor;
}

?>
