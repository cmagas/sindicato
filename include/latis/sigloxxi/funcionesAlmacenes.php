<?php
include_once("latis/cAlmacen.php");

function aplicarBajaProducto($idRegistro)
{
	global $con;
	$query="SELECT cmbProducto,cantidadBaja FROM _989_tablaDinamica WHERE  id__989_tablaDinamica=".$idRegistro;
	$fBaja=$con->obtenerPrimeraFila($query);
	
	$x=0;
	$consulta[$x]="begin";
	$x++;
	$query="SELECT idAlmacen FROM 6901_catalogoProductos WHERE idProducto=".$fBaja[0];
	$idAlmacen=$con->obtenerValor($query);
	
	$c=new cAlmacen($idAlmacen);
	
	$objAsiento='{
					"tipoMovimiento":"26",
					"cantidadOperacion":"",
					"idProducto":"",
					"tipoReferencia":"1",
					"datoReferencia1":"989",
					"datoReferencia2":"'.$idRegistro.'",
					"complementario":"'.$fBaja[0].'"
				}';
				
	$arrMovimientos=array();
	$oProducto=json_decode($objAsiento);
	$oProducto->cantidadOperacion=$fBaja[1];
	$oProducto->idProducto=$fBaja[0];
	array_push($arrMovimientos,$oProducto);
	$c->asentarArregloMovimientos($arrMovimientos,$consulta,$x,true);
	$consulta[$x]="commit";
	$x++;
	return $con->ejecutarBloque($consulta);
}

function cancelarPedido($idRegistro)
{
	global $con;
	$query="SELECT idPedido FROM _990_tablaDinamica WHERE id__990_tablaDinamica='".$idRegistro."'";
	$idPedido=$con->obtenerValor($query);
	
	$x=0;
	$consulta[$x]="begin";
	$x++;
	$consulta[$x]="update 6930_pedidos set situacion='2' where idPedido='".$idPedido."'";
	$x++;
	
	$query="SELECT idAlmacen FROM 6930_pedidos WHERE idPedido=".$idPedido;
	$idAlmacen=$con->obtenerValor($query);
	
	$c=new cAlmacen($idAlmacen);
	
	$objAsiento='{
					"tipoMovimiento":"25",
					"cantidadOperacion":"",
					"idProducto":"",
					"tipoReferencia":"1",
					"datoReferencia1":"990",
					"datoReferencia2":"'.$idRegistro.'",
					"complementario":"'.$idPedido.'",
					"dimensiones":null
				}';
				
	$arrMovimientos=array();
	$query="SELECT idProducto,cantidad,llave FROM 6931_productosPedido WHERE idPedido=".$idPedido;
	$res=$con->obtenerFilas($query);
	while($fila=mysql_fetch_row($res))
	{
		$oProducto=json_decode($objAsiento);
		$oProducto->cantidadOperacion=$fila[1];
		$oProducto->idProducto=$fila[0];
		$oProducto->dimensiones=convertirLlaveDimensiones($fila[2]);
		array_push($arrMovimientos,$oProducto);
		
	}
	$c->asentarArregloMovimientos($arrMovimientos,$consulta,$x,true);
	$consulta[$x]="commit";
	$x++;
	return $con->ejecutarBloque($consulta);
	
	
	
	
}


function pagoPedidoVentaTiendaVirtual($idMovimiento)
{
	global $con;
	$x=0;
	$query[$x]="begin";
	$x++;
	$consulta="SELECT idConcepto FROM 6011_movimientosPago WHERE idMovimiento=".$idMovimiento;
	$idPedido=$con->obtenerValor($consulta);
	
	$query[$x]="UPDATE 6934_pedidosTienda SET situacion=4 WHERE idPedidoTienda=".$idPedido;
	$x++;
	
	$consulta="SELECT idProducto,cantidad FROM 6935_productosPedidoTienda WHERE idPedido=".$idPedido;
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		$consulta="SELECT idAlmacen FROM 6901_catalogoProductos WHERE idProducto=".$fila[0];
		$idAlmacen=$con->obtenerValor($consulta);
		$c=new cAlmacen($idAlmacen);
				
		$objAsiento='{
						"tipoMovimiento":"29",
						"cantidadOperacion":"",
						"idProducto":"",
						"tipoReferencia":"4",
						"datoReferencia1":"'.$idPedido.'"
					}';
					
		$arrMovimientos=array();
		
		$oProducto=json_decode($objAsiento);
		$oProducto->cantidadOperacion=$fila[1];
		$oProducto->idProducto=$fila[0];
		array_push($arrMovimientos,$oProducto);
			
		$c->asentarArregloMovimientos($arrMovimientos,$query,$x,true);
		
	}
	$query[$x]="commit";
	$x++;
	return $con->ejecutarBloque($query);
}

?>