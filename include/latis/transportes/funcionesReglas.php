<?php
	function licenciaVencidaChofer($idChofer)
	{
		global $con;
		$fecha=date("Y-m-d");
		$resultado["resultado"]=0;
		if(($idChofer!=-1)&&($idChofer!=""))
		{
			$consulta="SELECT id__1018_tablaDinamica FROM _1018_tablaDinamica WHERE '".$fecha."' BETWEEN fechaEmision AND fechaVigencia
						AND idReferencia='".$idChofer."'";
			$res=$con->obtenerValor($consulta);
			if($res=="")
			{
				$resultado["resultado"]=1;
				$resultado["mensaje"]="El chofer presenta Licencia vencida";
			}
		}
		return $resultado;
	}

	function vencimientoSeguroUnidad($idUnidad)
	{
		global $con;
		$fecha=date("Y-m-d");
		$resultado["resultado"]=0;
		return $resultado;
		
		if(($idUnidad=="")||($idUnidad==-1))
			return $resultado;	
		$consulta="SELECT id__1038_tablaDinamica FROM _1038_tablaDinamica WHERE '".$fecha."' BETWEEN fechaInicioVigencia 
					AND fechaFinalVigencia AND idReferencia='".$idUnidad."'";
		$res=$con->obtenerValor($consulta);
		if($res=="")
		{
			$resultado["resultado"]=1;
			$resultado["mensaje"]="La Unidad: ".obtenerNumEconomicoIdUnidad($idUnidad)." presenta Seguro vencido";
		}
		
		return $resultado;
	}
	
	function choferCastigado($idChofer)
	{
		global $con;
		$fecha=date("Y-m-d");
		$resultado["resultado"]=0;
		$consulta="SELECT idEstado FROM _1013_tablaDinamica WHERE id__1013_tablaDinamica='".$idChofer."' and idEstado='3'";
		$res=$con->obtenerValor($consulta);
		if($res!="")
		{
			$resultado["resultado"]=1;
			$resultado["mensaje"]="El Chofer se encuentra en periodo de Castigo";
		}
		return $resultado;
	}
	
	function vehiculoSinRevision($idUnidad)
	{
		global $con;
	}
	
	function inexistenciaNumEconomico($idUnidad)
	{
		global $con;	
		$resultado["resultado"]=0;
		$consulta="SELECT numEconomico FROM _1012_tablaDinamica WHERE  id__1012_tablaDinamica=".$idUnidad." and idEstado=2";
		$idUnidad=$con->obtenerValor($consulta);
		if($idUnidad=="")
		{
			$resultado["resultado"]=1;
			$resultado["mensaje"]="El número económico: ".$idUnidad." ingresado NO existe o no está activo";
		}
		return $resultado;
	}
	
	function situacionBajaChofer($idChofer)
	{
		global $con;
		$resultado["resultado"]=0;
		
		$consulta="SELECT idEstado FROM _1013_tablaDinamica WHERE id__1013_tablaDinamica='".$idChofer."' and idEstado='4'";
		$res=$con->obtenerValor($consulta);
		if($res!="")
		{
			$resultado["resultado"]=1;
			$resultado["mensaje"]="El Chofer se encuentra dado de Baja";
		}
		return $resultado;
	}
	function permitirSalidaUnidad($idUnidad)
	{
		global $con;
		$fecha=date("Y-m-d");
		$resultado["resultado"]=0;
		$consulta="SELECT idAsignacion FROM 3106_asignacionHorarioRuta WHERE fecha='".$fecha."' AND situacion='3'
					and idUnidadAsignada='".$idUnidad."'";
		$res=$con->obtenerValor($consulta);
		if($res!="")
		{
			$resultado["resultado"]=1;
			$resultado["mensaje"]="La Unidad se encuentra en Recorrido";
		}
		return $resultado;
	}

?>