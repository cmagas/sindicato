<?php
	function obtenerValorBD(&$objRes)
	{
		if($objRes[0]==NULL)
			return NULL;
		if(gettype($objRes[0])=="resource")
		{	
			$fila=$objRes[1]->obtenerSiguienteFila($objRes[0]);
			if($fila)
				return $fila[0];
		}
		else
			return $objRes[0];
		return NULL;
	}
	
	function obtenerSiguienteRegistroBD(&$objRes)
	{
		if($objRes[0]==NULL)
			return NULL;
		return $objRes[1]->obtenerSiguienteFila($objRes[0]);
	}
	
	function obtenerSiguienteRegistroEstructuraBD(&$objRes)
	{
		global $arrQueries;
		if($objRes[0]==NULL)
			return NULL;
		return $objRes[1]->obtenerSiguienteFilaAsoc($objRes[0]);
	}
	
	function obtenerNumeroRegistrosBD($objRes)
	{
		if($objRes[0]==NULL)
			return 0;
		return $objRes[1]->obtenerNumeroRegistros($objRes[0]);
	}
	
	function inicializarRecursoBD(&$objRes)
	{
		if($objRes[0]==NULL)
			return;
		$objRes[1]->inicializarRecurso($objRes[0]);
	}
?>