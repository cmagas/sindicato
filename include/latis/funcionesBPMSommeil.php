<?php

	include_once("utiles.php");
	
	function guardarProductoAlmacenSommeil($idRegistro)
	{
		global $con;
		$precioVenta=0;
		$piezas=0;
		
		$consultar="SELECT * FROM _732_tablaDinamica WHERE id__732_tablaDinamica='".$idRegistro."'";
		$res=$con->obtenerPrimeraFila($consultar);
		
		$idProducto=$res[0];
		
		$unidadCompra=$res[13];
		$idProvedor=$res[14];
		$ventaDirecta=$res[17];
		$precioVenta=$res[18];
		$piezas=$res[19];
		
		
		$consulAlmacen="SELECT almacen FROM _732_gridAlmacen WHERE idReferencia='".$idRegistro."'";
		$resAlmacen=$con->obtenerFilas($consulAlmacen);
		
		//$num=mysql_num_rows($resAlmacen);
		
		$x=0;
		$consulta[$x]="begin";
		$x++;

		$consulta[$x]="DELETE FROM 732_productoAlmacen WHERE idProducto='".$idProducto."'";
		$x++;
		
		
		while($fila=mysql_fetch_row($resAlmacen))
		{
			$idAlmacen=$fila[0];
			$nombreAlmacen=obtenerNombreAlmacenSommeil($idAlmacen);
			$nombreProducto=strtoupper($res[11]);
			
			if(mysql_num_rows($resAlmacen)>1)
			{
				$nombreProducto=$nombreProducto." [".$nombreAlmacen."]";
			}
			
			$consulta[$x]="INSERT INTO 732_productoAlmacen(idProducto,nombreProducto,idAlmacen,idProveedor,ventaDirecta,precioVenta,piezas,
						unidadCompra,idEstado)values('".$idProducto."','".$nombreProducto."','".$idAlmacen."','".$idProvedor."','".$ventaDirecta."',
						'".$precioVenta."','".$piezas."','".$unidadCompra."','2')";
			$x++;
		}
		
		$consulta[$x]="commit";
		$con->ejecutarBloque($consulta);				
	}
	
	function obtenerNombreAlmacenSommeil($idAlmacen)
	{
		global $con;
		$consulta="SELECT nombreAlmacen FROM _682_tablaDinamica WHERE id__682_tablaDinamica='".$idAlmacen."'";
		$nom=$con->obtenerValor($consulta);
		
		return strtoupper($nom);
	}
	
	function cambiarEstadoProductoActivos($idRegistro)
	{
		global $con;
		$consulta="UPDATE 732_productoAlmacen SET idEstado='3' WHERE idProducto='".$idRegistro."'";
		$con->ejecutarConsulta($consulta);
	}

	function crearNotaComandaVentaCaja($idRegistro)
	{
		global $con;
		$fechaActual=date("Y-m-d");
		$tipoNota="Nota de Consumo";
		$tablaRef="711";
		$total=0;
		$nombreCliente="";
		
		$consultar="SELECT tipoCliente,nombreClienteDatosComanda,idHabitacion FROM _711_tablaDinamica WHERE id__711_tablaDinamica='".$idRegistro."'";
		$res=$con->obtenerPrimeraFila($consultar);
		
		$tipoCliente=$res[0]; //1=ClienteForaneo, 2=Habitacion
		$idHabitacion=$res[2];
		
		if($tipoCliente==2)
		{
			$nombreCliente=$res[1];
			$idCliente=-1;
		}
		else
		{
			//$datos=obtenerNombreClienteHabitacion($idHabitacion,$fechaActual);
			
			//varDump($datos);
		}
		
		$consultaDatos="SELECT SUM(total) FROM _711_productoDatosComanda WHERE idReferencia='".$idRegistro."'";
		$total=$con->obtenerValor($consultaDatos);
		
		$x=0;
		$consulta[$x]="begin";
		$x++;
		
		$consulta[$x]="DELETE FROM 6942_adeudos WHERE tablaRef='".$tablaRef."' AND idReferencia='".$idRegistro."'";
		$x++;
		
		$consulta[$x]="INSERT INTO 6942_adeudos(tipoAdeudo,idReferencia,fechaCreacion,total,tipoCliente,idCliente,nombreCliente,situacion,tablaRef)VALUES('1',
						'".$idRegistro."','".$fechaActual."','".$total."','".$tipoCliente."','".$idCliente."','".$nombreCliente."','2','".$tablaRef."')";
		$x++;
		
		$consulta[$x]="commit";
		$con->ejecutarBloque($consulta);				
	}
	
	function obtenerNombreClienteHabitacion($idHabitacion,$fecha)
	{
		global $con;
		
		$consulta="SELECT cliente FROM _670_tablaDinamica t, _670_asignacionHabitaciones a WHERE t.id__670_tablaDinamica=a.iReferencia
					AND a.idHabitacion='".$idHabitacion."' AND '".$fecha."' BETWEEN t.fechaIngreso AND t.fechaSalida";
		$res=$con->obtenerValor($consulta);
		
		$nomCliente="SELECT nombre,apPaterno,apMaterno FROM _655_tablaDinamica WHERE id__655_tablaDinamica='".$res."'";
		$nom=$con->obtenerPrimeraFila($nomCliente);
		
		$nombre=strtoupper($nom[0]." ".$nom[1]." ".$nom[2]);
		$obj["$idCliente"]=$res;
		$obj["nombre"]=$nombre;
		return $obj;
	}
	
	function registrarProductosComandaInventario($idRegistro,$idTabla)
	{
		global $con;
		$fechaActual=date("Y-m-d");
		$tipoOperacion=-1;
		$idComanda=$idTabla;
		
		$x=0;
		$consulta[$x]="begin";
		$x++;
		
		switch($idTabla)
		{
			case 711:
						$consultar="SELECT * FROM _711_productoDatosComanda WHERE idReferencia='".$idRegistro."'";
						$res=$con->obtenerFilas($consultar);
						
						
						while($fila=mysql_fetch_row($res))
						{
							$idRegistro=$fila[0];
							$nombreProducto=$fila[2];
							$cantidad=$fila[3];
							$precio=$fila[4];
							$total=$fila[5];
							$idProd=$fila[7];
							$tablaRef=$fila[8];
							$idAlmacen=$fila[9];
							
							if($tablaRef==732)
							{
								$consulProducto="SELECT idProducto FROM 732_productoAlmacen WHERE idRegistro='".$idProd."'";
								$idProducto=$con->obtenerValor($consulProducto);
							}
							else
							{
								$idProducto=$idProd;
							}
							
							$consulta[$x]="INSERT INTO 609_movimientoInventario(tipoMovimiento,fechaMovimiento,tipoOperacion,tipoProducto,
										idProducto,producto,cantidad,costoU,importe,idRegRef,idTablaRef,idAlmacen)VALUES('1','".$fechaActual."','".$tipoOperacion."',
										'1','".$idProducto."','".$nombreProducto."','".$cantidad."','".$precio."','".$total."','".$idRegistro."','".$idComanda."',
										'".$idAlmacen."')";
							$x++;
						}					
			break;
			case 740:
						$consultar="SELECT * FROM _740_gridServicios WHERE idReferencia='".$idRegistro."'";
						$res=$con->obtenerFilas($consultar);
						
						while($fila=mysql_fetch_row($res))
						{
							$idRegistro=$fila[0];
							$nombreProducto=$fila[3];
							$cantidad=$fila[4];
							$precio=$fila[6];
							$total=$fila[7];
							$idProducto=$fila[2];
							$tablaRef=$fila[8];
							$idAlmacen='';
							
							$consulta[$x]="INSERT INTO 609_movimientoInventario(tipoMovimiento,fechaMovimiento,tipoOperacion,tipoProducto,
										idProducto,producto,cantidad,costoU,importe,idRegRef,idTablaRef,idAlmacen)VALUES('1','".$fechaActual."','".$tipoOperacion."',
										'1','".$idProducto."','".$nombreProducto."','".$cantidad."','".$precio."','".$total."','".$idRegistro."','".$idComanda."',
										'".$idAlmacen."')";
							$x++;
						}					
					
			break;
		}

		$consulta[$x]="commit";
		$con->ejecutarBloque($consulta);				
	}
	
	function crearNotaComandaServicioVentaCaja($idRegistro)
	{
		global $con;
		$fechaActual=date("Y-m-d");
		$tipoNota="Nota de Servicio";
		$tablaRef="740";
		$total=0;
		$nombreCliente="";
		
		$consultar="SELECT tipoCliente,nombreClienteDato FROM _740_tablaDinamica WHERE id__740_tablaDinamica='".$idRegistro."'";
		$res=$con->obtenerPrimeraFila($consultar);
		
		$tipoCliente=$res[0]; //1=ClienteForaneo, 2=Habitacion
		$idHabitacion=$res[1];
		
		if($tipoCliente==2)
		{
			$nombreCliente=$res[1];
			$idCliente=-1;
		}
		else
		{
			//$datos=obtenerNombreClienteHabitacion($idHabitacion,$fechaActual);
			
			//varDump($datos);
		}
		
		$consultaDatos="SELECT SUM(total) FROM _740_gridServicios WHERE idReferencia='".$idRegistro."'";
		$total=$con->obtenerValor($consultaDatos);
		
		$x=0;
		$consulta[$x]="begin";
		$x++;
		
		$consulta[$x]="DELETE FROM 6942_adeudos WHERE tablaRef='".$tablaRef."' AND idReferencia='".$idRegistro."'";
		$x++;
		
		$consulta[$x]="INSERT INTO 6942_adeudos(tipoAdeudo,idReferencia,fechaCreacion,total,tipoCliente,idCliente,nombreCliente,situacion,tablaRef)VALUES('1',
						'".$idRegistro."','".$fechaActual."','".$total."','".$tipoCliente."','".$idCliente."','".$nombreCliente."','2','".$tablaRef."')";
		$x++;
		
		$consulta[$x]="commit";
		$con->ejecutarBloque($consulta);				
	}
	
	function calcularTotalGasto($idRegistro)
	{
		global $con;
		$total=0;
		
		$consulta="SELECT SUM(importeGastos) FROM _748_gastosImporte WHERE idReferencia='".$idRegistro."'";
		$total=$con->obtenerValor($consulta);
		
		$conActualizar="UPDATE _748_tablaDinamica SET totalGasto='".$total."' WHERE id__748_tablaDinamica='".$idRegistro."'";
		$con->ejecutarConsulta($conActualizar);
	}
	
	function guardarAdeudoReservacionBPM($idRegistro,$tipoOperacion) //Tipo operacion: 1=Guardar, 2=cancelar
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
					$abono=obtenerAbonosReservacionesBPM($idRegistro);
					
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
					$importeAdeudo=$montoTotal;
					$nombreCliente=obtenerNombreClienteRegistrado($idCliente);
					$consularServicios="SELECT servicio,t.nombreServicio,precioPreferente,cantidad,total FROM _670_servicios s,_653_tablaDinamica t 
										WHERE s.servicio=t.id__653_tablaDinamica AND s.idReferencia='".$idRegistro."' AND t.tipoServicio IN(2,4)";
					$resServicio=$con->obtenerFilas($consularServicios);
					
					$consulta[$x]="DELETE FROM 6942_adeudos WHERE tablaRef='".$formulario."' AND idReferencia='".$idRegistro."'";
					$x++;
					
					$consulta[$x]="DELETE FROM 1224_controlServicios WHERE idReferencia='".$idRegistro."' AND idTablaRef='".$formulario."'";
					$x++;
					
					while($fila=mysql_fetch_row($resServicio))
					{
						$nombreServicio=$fila[1];
						$cantidadS=$fila[3];
						$totalS=$fila[4];
						
						$consulta[$x]="INSERT INTO 1224_controlServicios(fechaRegistro,idReferencia,idTablaRef,nombreServicio,cantidad,totalVenta,
									nombreCliente,situacion)VALUES('".$fechaC."','".$idRegistro."','670','".$nombreServicio."','".$cantidadS."',
									'".$totalS."','".$nombreCliente."','1')";
						$x++;
					}
					
					$consulta[$x]="INSERT INTO 6942_adeudos(tipoAdeudo,idReferencia,fechaCreacion,subtotal,iva,total,tipoCliente,idCliente,nombreCliente,situacion,
									fechaVencimiento,naturalezaAdeudo,tablaRef,fechaInicioRes,fechaFinRes)VALUES('1','".$idRegistro."','".$fechaC."','0','0','".$importeAdeudo."','5',
									'".$idCliente."','".$nombreCliente."','2','".$fechaC."','1','".$formulario."','".$fechaIngreso."','".$fechaSalida."')";
					$x++;
					
					$consulta[$x]="UPDATE _670_tablaDinamica SET importeTotalReserva='".$importeAdeudo."' WHERE id__670_tablaDinamica='".$idRegistro."'";
					$x++;		
			break;
			case 2:
					$consulta[$x]="UPDATE 6942_adeudos SET situacion='4' WHERE tablaRef='".$formulario."' and idReferencia='".$idRegistro."'";
					$x++;
					
					$consulta[$x]="UPDATE 1224_controlServicios SET situacion='4' WHERE idReferencia='".$idRegistro."' AND idTablaRef='".$formulario."'";
					$x++;
			break;
		}
		
		$consulta[$x]="commit";
		$con->ejecutarBloque($consulta);
	}
	
	function obtenerAbonosReservacionesBPM($idRegistro)
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
	
	function obtenerNombreClienteRegistrado($idCliente)
	{
		global $con;
		$nombre="";
		
		$consulta="SELECT CONCAT(nombre,' ',apPaterno,' ',apMaterno) AS nombre FROM _655_tablaDinamica WHERE id__655_tablaDinamica='".$idCliente."'";
		$res=$con->obtenerValor($consulta);
		
		$nombre=strtoupper($res);
		
		return $nombre;	
	}
	
	function obtenerNombreServicioReserva($id)
	{
		global $con;
		$consulta="SELECT nombreServicio FROM _653_tablaDinamica WHERE id__653_tablaDinamica='".$id."'";
		$res=$cn->obtenerValor($consulta);
		
		return $res;
	}
	
	

?>