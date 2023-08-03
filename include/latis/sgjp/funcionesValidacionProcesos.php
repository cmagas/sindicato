<?php  
include_once("latis/conexionBD.php");
include_once("latis/utiles.php");


function validarTranscripcion($idFormulario,$idRegistro)
{
	global $con;
	$cadRes="";
	$consulta="SELECT COUNT(*) FROM 7035_informacionDocumentos WHERE idFormulario=".$idFormulario.
				" AND idReferencia=".$idRegistro." AND idFormularioProceso=482";
	
	$nReg=$con->obtenerValor($consulta);
	if($nReg==0)
	{
		$cadRes="['Transcripci&oacute;n','Debe ingresar la transcripci&oacute;n a enviar']";
	}
	
	
	return "[".$cadRes."]";	
}



function validarResolucionJuez($idFormulario,$idRegistro)
{
	global $con;
	$cadRes="";
	$consulta="SELECT COUNT(*) FROM 7035_informacionDocumentos WHERE idFormulario=".$idFormulario.
				" AND idReferencia=".$idRegistro." AND idFormularioProceso=484";
	
	$nReg=$con->obtenerValor($consulta);
	if($nReg==0)
	{
		$cadRes="['Resoluci&oacute;n','Debe ingresar la resoluci&oacute;n a enviar']";
	}
	
	
	return "[".$cadRes."]";	
}
?>