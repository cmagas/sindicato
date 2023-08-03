<?php
	function obtenerPersupuestoSolicitadoProyectos($idFormulario,$idRegistro)
	{
		global $con;
		$consulta="select sum(total) fROM 100_calculosGrid where idFormulario=".$idFormulario." and idReferencia=".$idRegistro;
		$total=$con->obtenerValor($consulta);
		if($total=="")
			$total=0;
		return $total;
	}
	function obtenerPersupuestoAutorizadoProyectos($idFormulario,$idRegistro)
	{
		global $con;
		$consulta="select sum(montoAutorizado) fROM 100_calculosGrid where idFormulario=".$idFormulario." and idReferencia=".$idRegistro;
		$total=$con->obtenerValor($consulta);
		if($total=="")
			$total=0;
		return $total;
	}
?>