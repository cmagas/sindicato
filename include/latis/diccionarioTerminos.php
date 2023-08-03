<?php include_once("latis/conexionBD.php"); 
	$consultaTmp="SELECT * FROM 1008_diccionarioTerminos";
	$res=$con->obtenerFilas($consultaTmp);
	$dic=array();
	while($fila=mysql_fetch_row($res))
	{
		$dic[$fila[1]]["s"]["et"]=$fila[2];
		$dic[$fila[1]]["s"]["el"]=$fila[3];
		$dic[$fila[1]]["s"]["un"]=$fila[4];
		$dic[$fila[1]]["s"]["este"]=$fila[5];
		$dic[$fila[1]]["s"]["sel"]=$fila[11];
		$dic[$fila[1]]["p"]["et"]=$fila[6];
		$dic[$fila[1]]["p"]["el"]=$fila[7];
		$dic[$fila[1]]["p"]["un"]=$fila[8];
		$dic[$fila[1]]["p"]["este"]=$fila[9];
		$dic[$fila[1]]["p"]["sel"]=$fila[12];
		
	}
	function reemplazarCadenaDiccionario($cadena,$rutas)
	{
		global $dic;
		if($rutas=="")
			return $cadena;
		$cadenaFinal=$cadena;
		$arrRutas=explode(",",$rutas);
		foreach($arrRutas as $ruta)
		{
			$arrElem=explode("_",$ruta);
			$cadenaFinal=str_replace("@".$arrElem[0],$dic[$arrElem[0]][$arrElem[1]][$arrElem[2]],$cadenaFinal);
		}
		return $cadenaFinal;
	}
?>