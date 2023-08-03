<?php
	function getNombreDepto($depto)
	{
		global $con;
		$consulta="select unidad from 817_organigrama where codigoUnidad='".$depto."'";
		return $con->obtenerValor($consulta);
	}
	
	function getPrograma($idPrograma)
	{
		global $con;
		$consulta="SELECT tituloPrograma FROM 517_programas WHERE idPrograma='".$idPrograma."'";
		return $con->obtenerValor($consulta);
	}
	
	function getCentroCosto($cc)
	{
		global $con;
		$consulta="SELECT tituloCentroC FROM 506_centrosCosto WHERE idCentroCosto=".$cc;
		return $con->obtenerValor($consulta);
	}
?>