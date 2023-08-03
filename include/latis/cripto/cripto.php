<?php
	class cCripto
	{
		var $llave;
		
		function cAlmacen($llaveCodificacion)
		{
			$consulta="";
			$this->llave=$llaveCodificacion;
		}
		
		function codificarCadena($cadena)
		{
			$cadena=cc(stringEncode(bE($cadena),$this->llave));
			return $cadena;
		}
		
		function decodificarCadena($cadena)
		{
			$cadena=bD(stringDecode(dc($cadena),$this->llave));
			return $cadena;
		}
	}
?>