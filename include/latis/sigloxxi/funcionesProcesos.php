<?php
	include_once("latis/cAlmacen.php");
	
	function cancelarPedido($idPedido,$idFormulario,$idRegistro)
	{
		global $con;
		$x=0;
		$consulta[$x]="begin";
		$x++;
		$query="SELECT idAlmacen FROM 6930_pedidos WHERE idPedido=".$idPedido;
		$idAlmacen=$con->obtenerValor($query);
		
		$c=new cAlmacen($idAlmacen);
		
		$objAsiento='{
						"tipoMovimiento":"25",
						"cantidadOperacion":"",
						"idProducto":"",
						"tipoReferencia":"1",
						"datoReferencia1":"'.$idFormulario.'",
						"datoReferencia2":"'.$idRegistro.'",
						"complementario":"'.$idPedido.'"
					}';
					
		$arrMovimientos=array();
		$query="SELECT idProducto,cantidad FROM 6931_productosPedido WHERE idPedido=".$idPedido;
		$res=$con->obtenerFilas($query);
		while($fila=mysql_fetch_row($res))
		{
			$oProducto=json_decode($objAsiento);
			$oProducto->cantidadOperacion=$fila[1];
			$oProducto->idProducto=$fila[0];
			array_push($arrMovimientos,$oProducto);
			
		}
		$c->asentarArregloMovimientos($arrMovimientos,$consulta,$x,true);
		$consulta[$x]="commit";
		$x++;
		return $con->ejecutarBloque($consulta);
	}
	
	function registraBajaProducto($idRegistro)
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
	
?>