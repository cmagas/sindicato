<?php
	class cBaseConectorDatos
	{
		var $filasAfectadas;
		var $conexion;	
		var $bdActual;
		
		function obtenerTablasSistemaJSON($filtro)
		{
			return '{"numReg":"","registros":[]}';	
		}
		
		function obtenerCamposTabla($nTabla,$nLargo=false,$mostrarCamposCheck=false,$mostrarCamposControl=true,$formato=0)
		{
			return "[]";
		}
		
		function obtenerListaValoresCampoTabla($nCampo,$nTabla)
		{
			return "[]";
		}
		
		function obtenerValoresTabla($nTabla,$campoId,$campoEtiqueta)
		{
			return "[]";
		}
		
		function obtenerCampoLlave($nTabla)
		{
			return "";
		}
		
		function existeCampo($campo,$nomTabla)
		{
			return false;
		}
		
		function existeTabla($nomTabla)
		{
			return false;
		}
		
		function esSistemaLatis()
		{
			return false;
		}
		
		
		function obtenerValor($consulta)
		{
			return "";
		}
		
		function obtenerFilas($consulta)
		{
			return NULL;
		}
		
		function obtenerFilasArreglo($consulta)
		{
			return "[]";
		}
		
		function obtenerSiguienteFila(&$res)
		{
			return NULL;
		}
		
		function obtenerSiguienteFilaAsoc(&$res)
		{
			return NULL;
		}
		
		function inicializarRecurso(&$res)
		{
		}
		
		function obtenerNumeroRegistros($res)
		{
			return 0;
		}
		
		function obtenerListaValores($consulta,$caracter="")
		{
			return "";
		}
		
		function obtenerPrimeraFila($consulta)
		{
			return NULL;
		}
		
		
		function obtenerPrimeraFilaAsoc($consulta)
		{
			return NULL;
		}
		
		function ejecutarConsulta($consulta)
		{
			return true;
		}
		
		function obtenerFilasObjConector($consulta)
		{
			return NULL;
		}
	}
?>