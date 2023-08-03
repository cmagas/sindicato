<?php
	function enteroHaciaAbajo($valor)
	{
		return floor($valor);
	}
	
	function enteroHaciaArriba($valor)
	{
		return ceil($valor);
	}
	
	function obtenerParteEntera($valor)
	{
		if(strpos($valor,".")!==false)
		{
			$arrValor=explode(".",$valor);
			if($arrValor[0]=="")
				return 0;
			return $arrValor[0];
		}
		return $valor;

	}
?>