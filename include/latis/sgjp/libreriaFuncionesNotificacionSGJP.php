<?php 
include_once("latis/conexionBD.php");
include_once("latis/utiles.php");

function determinarDirectorDestinatario($idFormulario,$idRegistro,$idActorProceso)
{
	global $con;
	$rolDestinatario="";
	$consulta="SELECT actor FROM 944_actoresProcesoEtapa WHERE idActorProcesoEtapa=".$idActorProceso;
	$rol=$con->obtenerValor($consulta);
	switch($rol)
	{
		case "200_0":
		case "201_0":
			$rolDestinatario="199_0";
		break;
		case "204_0":
		case "205_0":
			$rolDestinatario="202_0";
		break;
	}
	
	$consulta="SELECT codigoInstitucion FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$unidadGestion=$con->obtenerValor($consulta);
	
	$arrDestinatario=array();
	$consulta="SELECT ad.idUsuario FROM 801_adscripcion ad,807_usuariosVSRoles r WHERE r.idUsuario=ad.idUsuario AND 
				r.codigoRol='".$rolDestinatario."' AND ad.Institucion='".$unidadGestion."'";

	
	$tituloRolActor=obtenerTituloRol($rolDestinatario);
	
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		
		$nombreUsuario=obtenerNombreUsuario($fila[0])." (".$tituloRolActor.")";
		$o='{"idUsuarioDestinatario":"'.$fila[0].'","nombreUsuarioDestinatario":"'.$nombreUsuario.'","actorDestinatario":"'.$rolDestinatario.'"}';
		$o=json_decode($o);
		array_push($arrDestinatario,$o);
	}
	$nombreUsuario=obtenerNombreUsuario(1)." (".$tituloRolActor.")";
	$o='{"idUsuarioDestinatario":"1","nombreUsuarioDestinatario":"'.$nombreUsuario.'","actorDestinatario":"'.$rolDestinatario.'"}';
	$o=json_decode($o);
	array_push($arrDestinatario,$o);

	return $arrDestinatario;
}

?>