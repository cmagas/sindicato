<?php

	function permiteVisualizarCotizacionServicio($actor)
	{
		global $con;
		$consulta="SELECT actor FROM 944_actoresProcesoEtapa WHERE idActorProcesoEtapa=".$actor;
		$rol=$con->obtenerValor($consulta);
		$arrRolesVisualizanCotizacion["182_0"]=1;
		$arrRolesVisualizanCotizacion["183_0"]=1;
		
		if(isset($arrRolesVisualizanCotizacion[$rol]))
		{
			return 1;
		}
	
		
		return 0;
	}
	
	
	function permiteVisualizarCotizacionViaticos($actor)
	{
		global $con;
		$consulta="SELECT actor FROM 944_actoresProcesoEtapa WHERE idActorProcesoEtapa=".$actor;
		$rol=$con->obtenerValor($consulta);
		$arrRolesVisualizanCotizacion["182_0"]=1;
		$arrRolesVisualizanCotizacion["181_0"]=1;
		
		if(isset($arrRolesVisualizanCotizacion[$rol]))
		{
			return 1;
		}
	
		
		return 0;
	}
?>