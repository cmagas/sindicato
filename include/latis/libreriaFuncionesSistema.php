<?php
	function reemplazarCadena($cadenaBuscada,$cadenaReemplazo,$cadenaFuente,$utilizarComillas)
	{
		if($utilizarComillas==1)
			return str_replace($cadenaBuscada,'"'.$cadenaReemplazo.'"',$cadenaFuente);	
		else
			return str_replace($cadenaBuscada,$cadenaReemplazo,$cadenaFuente);	
	}
	
	function obtenerParteFecha($fecha,$formato)
	{
		return date($formato,strtotime($fecha));
	}
?>