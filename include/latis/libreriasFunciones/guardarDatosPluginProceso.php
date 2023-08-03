<?php include_once("latis/conexionBD.php");

	function asociarPeriodosProceso($idRegistro,$periodo,$idFormulario)
	{
		global $con;
		
		$arrPeriodos=explode(",",$periodo);
		$x=0;
		$consulta[$x]="begin";
		$x++;
		$consulta[$x]="delete from 3014_pluginPeriodos where idFormulario=".$idFormulario." and idReferencia=".$idRegistro;
		$x++;
		foreach($arrPeriodos as $p)
		{
			$consulta[$x]="INSERT INTO 3014_pluginPeriodos(idFormulario,idReferencia,idPeriodo) VALUES(".$idFormulario.",".$idRegistro.",".$p.")";
			$x++;
		}
		
		$consulta[$x]="commit";
		$x++;
		return $con->ejecutarBloque($consulta);
		
		
		
	}
?>