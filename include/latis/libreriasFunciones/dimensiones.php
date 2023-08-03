<?php
	function obtenerTallaProducto($param1)
	{
		global $con;
		$arrFilas=array();
		$consulta="SELECT id__987_tablaDinamica,talla,descripcion FROM _987_tablaDinamica ORDER BY talla";
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$o=array();
			$o["id"]=$fila[0];
			$o["etiqueta"]=$fila[1];
			$o["descripcion"]=$fila[2];
			array_push($arrFilas,$o);
		}
		return $arrFilas;
	}
	
	function obtenerDimensionUnica($param1)
	{
		global $con;
		$arrFilas=array();
		$o=array();
		$o["id"]=0;
		$o["etiqueta"]='&Uacute;nica';
		$o["descripcion"]="";
		array_push($arrFilas,$o);
		return $arrFilas;
	}

	function obtenerColorProducto($param1)
	{
		global $con;
		$arrFilas=array();
		$consulta="SELECT id__985_tablaDinamica,nombreColor,descripcion FROM _985_tablaDinamica ORDER BY nombreColor";
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$o=array();
			$o["id"]=$fila[0];
			$o["etiqueta"]=$fila[1];
			$o["descripcion"]=$fila[2];
			array_push($arrFilas,$o);
		}
		return $arrFilas;
	}
	
?>