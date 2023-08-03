<?php
	
	function obtenerAudienciasProgramadasSede($idSala,$start,$end,$idEvento)
	{
		global $con;
		$arrAudiencias=array();
		$consulta="SELECT * FROM 000_instanciasSistema where situacion=1";
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			
			$client = new nusoap_client("http://".$fila[2]."/webServices/wsSGJ.php?wsdl","wsdl");
 
			$parametros=array();
			$parametros["idSala"]=$idSala;
			$parametros["start"]=$start;
			$parametros["end"]=$end;
			$parametros["idEvento"]=$idEvento;
			
			$response = $client->call("obtenerEventosSala", $parametros);

			$arrEventos=json_decode($response);

			foreach($arrEventos as $fila)
				array_push($arrAudiencias,$fila);
		}
		
		return $arrAudiencias;
		
		
	}
?>