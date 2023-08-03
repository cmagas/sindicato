<?php include_once("latis/cConexion.php");

	class cConexionMySQL extends cConexion
	{
		function cConexionMySQL($host,$puerto,$login,$passwd,$bd)
		{
			if($puerto=="")
				$puerto=3306;
			$this->conexion=@mysql_connect($host.":".$puerto,$login,$passwd,true);
			if($this->conexion)
			{
				mysql_select_db($bd,$this->conexion);
				mysql_query("SET NAMES UTF8");
				$this->filasAfectadas=0;
				$this->bdActual=$bd;
			}
		}
		
		
		
		
	}
?>