<?php 
include_once("latis/conexionBD.php");
include_once("latis/utiles.php");


function aplicarActivacionCarpeta($idFormulario,$idRegistro)
{
	global $con;

	$consulta="SELECT carpetaAdministrativa,comentariosAdicionales FROM _594_tablaDinamica WHERE id__594_tablaDinamica=".$idRegistro;
	$fCarpeta=$con->obtenerPrimeraFila($consulta);
	$carpetaAdministrativa=$fCarpeta[0];
	
	$listaCarpetas=obtenerCarpetasVinculadas($fCarpeta[0],"1,5,6");
	
	$consulta="SELECT idActividad FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$carpetaAdministrativa."'";
	$idActividad=$con->obtenerValor($consulta);
	
	$consulta="SELECT carpetaAdministrativa FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa IN(".$listaCarpetas.
				") and carpetaAdministrativa<>'".$fCarpeta[0]."'";
				
	$listaCarpetas=$con->obtenerValor($consulta);
	if($listaCarpetas=="")
		$listaCarpetas=-1;
	
	$arrCarpetasJudiciales=explode(",",$listaCarpetas);

	$consulta="SELECT idOpcion FROM _594_chmImputados WHERE idPadre=".$idRegistro;
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		//varDUmp($fila);
		registrarCambioSituacionImputado($fCarpeta[0],-1,$fila[0],23,"",$fCarpeta[1]);
		foreach($arrCarpetasJudiciales as $iCarpeta)
		{
			if($iCarpeta!=-1)
			{
				$iCarpeta=str_replace("'","",$iCarpeta);
				registrarCambioSituacionImputado($iCarpeta,-1,$fila[0],24,"","Apertura de carpeta ".$carpetaAdministrativa.". Comentario: ".$fCarpeta[1]);
				determinarSituacionCarpeta($iCarpeta,-1);
			}
		}
	}
	determinarSituacionCarpeta($fCarpeta[0],-1);
}


function aplicarCambioAdscripcionCarpeta($idFormulario,$idRegistro)
{
	global $con;
	$consulta="SELECT carpetaAdministrativa,unidadDestino FROM _595_tablaDinamica WHERE id__595_tablaDinamica=".$idRegistro;
	$fCarpeta=$con->obtenerPrimeraFila($consulta);
	
	$consulta="UPDATE 7006_carpetasAdministrativas SET unidadGestion='".$fCarpeta[1]."' WHERE carpetaAdministrativa='".$fCarpeta[0]."'";
	return $con->ejecutarConsulta($consulta);
}

?>