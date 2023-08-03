<?php
	function agregarElementoArreglo(&$arreglo,$elemento)
	{
		if(gettype($arreglo)!="array")
			$arreglo=array();
		array_push($arreglo,$elemento);
	}
	
	function obtenerElementoArreglo($arreglo,$posicion)
	{
		if(isset($arreglo[$posicion]))
			return $arreglo[$posicion];
		return null;
	}
	
	function obtenerNumeroElementos($arreglo)
	{
		return sizeof($arreglo);	
	}
	
	function crearArreglo()
	{
		return array();
	}
	
	function descomponerCadena($separador,$cadena)
	{
		return explode($separador,$cadena);
	}
	
?>