<?php
	function obtenerPrimerDiaDelMes($op1=">=",$noFiltro)
	{
		if($noFiltro==2)
			return "";
		if($op1=="")
			$op1=">=";
		$arrValores[0]=$op1;
		$arrValores[1]=date("Y-m-01");
		return $arrValores;
	}
?>