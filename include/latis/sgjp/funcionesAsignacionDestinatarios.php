<?php 
include_once("latis/conexionBD.php");
include_once("latis/utiles.php");


function obtenerResponsablesTareas($idFormulario,$idRegistro,$numEtapa,$actorDestinatario)
{
	global $con;
	$arrDestinatarios=array();	
	
	$rolActor=obtenerTituloRol($actorDestinatario);
				
	$consulta="SELECT responsableTarea FROM 3020_responsablesTareas WHERE iFormulario=".$idFormulario.
			" AND iRegistro=".$idRegistro." AND etapa=".$numEtapa;	
			
	$resDestinatarios=$con->obtenerFilas($consulta);
	while($fDestinatario=mysql_fetch_row($resDestinatarios))	
	{
		
		$nombreUsuario=obtenerNombreUsuario($fDestinatario[0])." (".$rolActor.")";
		$o='{"idUsuarioDestinatario":"'.$fDestinatario[0].'","nombreUsuarioDestinatario":"'.$nombreUsuario.'"}';
		$oDestinatario=json_decode($o);
		
		array_push($arrDestinatarios,$oDestinatario);
	}
	
	return $arrDestinatarios;
}

function obtenerAuxiliarJudicialJuezTranscripcion($idFormulario,$idRegistro,$actorDestinatario)
{
	global $con;
	$rolActor=obtenerTituloRol($actorDestinatario);	
	$arrDestinatario=array();
	$consulta="SELECT idEventoAudiencia FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$idEvento=$con->obtenerValor($consulta);
	
	$consulta="SELECT idJuez FROM 7001_eventoAudienciaJuez WHERE idRegistroEvento=".$idEvento." limit 0,1";
	$idJuez=$con->obtenerValor($consulta);
	
	$consulta="SELECT idOpcion FROM _445_auxiliarJudicial a,_445_tablaDinamica j WHERE a.idPadre=j.id__445_tablaDinamica AND
				j.juez= ".$idJuez;
	
	$rAuxiliares=$con->obtenerFilas($consulta);
	while($fAuxiliares=mysql_fetch_row($rAuxiliares))
	{
		
		
		
		$nombreUsuario=obtenerNombreUsuario($fAuxiliares[0])." (".$rolActor.")";
		$o='{"idUsuarioDestinatario":"'.$fAuxiliares[0].'","nombreUsuarioDestinatario":"'.$nombreUsuario.'"}';
		$o=json_decode($o);
		array_push($arrDestinatario,$o);

		
		
	}
	
	$nombreUsuario=obtenerNombreUsuario(1)." (".$rolActor.")";
	$o='{"idUsuarioDestinatario":"1","nombreUsuarioDestinatario":"'.$nombreUsuario.'"}';
	$o=json_decode($o);
	array_push($arrDestinatario,$o);
	
	return $arrDestinatario;
	
	
}

function obtenerJuezAudienciaTranscripcion($idFormulario,$idRegistro,$actorDestinatario)
{
	global $con;
	$arrDestinatario=array();
	$consulta="SELECT idEventoAudiencia FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$idEvento=$con->obtenerValor($consulta);
	$consulta="SELECT idJuez,titulo FROM 7001_eventoAudienciaJuez WHERE idRegistroEvento=".$idEvento." limit 0,1";
	$rJueces=$con->obtenerFilas($consulta);
	while($fJuez=mysql_fetch_row($rJueces))
	{
		$idUsuario=$fJuez[0];
		

		$rolActor=$fJuez[1];	
		$nombreUsuario=obtenerNombreUsuario($idUsuario)." (".$rolActor.")";
		$o='{"idUsuarioDestinatario":"'.$idUsuario.'","nombreUsuarioDestinatario":"'.$nombreUsuario.'"}';
		$o=json_decode($o);
		array_push($arrDestinatario,$o);

		
		$nombreUsuario=obtenerNombreUsuario(1)." (".$rolActor.")";
		$o='{"idUsuarioDestinatario":"1","nombreUsuarioDestinatario":"'.$nombreUsuario.'"}';
		$o=json_decode($o);
		array_push($arrDestinatario,$o);
	}
	return $arrDestinatario;
	
}


function generarTareaAuxiliarDelegado($idFormulario,$idRegistro,$actorDestinatario)
{
	global $con;
	$arrDestinatario=array();
	
	$consulta="SELECT auxiliarAsignado FROM _619_tablaDinamica WHERE idReferencia=".$idRegistro;
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		$idUsuario=$fila[0];
		

		$rolActor=obtenerTituloRol($actorDestinatario);	
		$nombreUsuario=obtenerNombreUsuario($idUsuario)." (".$rolActor.")";
		$o='{"idUsuarioDestinatario":"'.$idUsuario.'","nombreUsuarioDestinatario":"'.$nombreUsuario.'"}';
		$o=json_decode($o);
		array_push($arrDestinatario,$o);

		
		$nombreUsuario=obtenerNombreUsuario(1)." (".$rolActor.")";
		$o='{"idUsuarioDestinatario":"1","nombreUsuarioDestinatario":"'.$nombreUsuario.'"}';
		$o=json_decode($o);
		array_push($arrDestinatario,$o);
	}
	return $arrDestinatario;
	
}
?>