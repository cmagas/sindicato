<?php

function puedeInscribirseASiguienteGrado($param1)
{
	$alumnoRegular=1;	

	foreach($param1["arrMaterias"] as $o)
	{
		if($o->situacion!=1)
		{
			$alumnoRegular=0;
			break;
		}
	}
	return $alumnoRegular;
}

function puedeInscribirseASiguienteGradoCondicionado($param1)
{
	$materiasReprobadas=0;	
	foreach($param1["arrMaterias"] as $o)
	{
		if($o->situacion!=1)
		{
			$materiasReprobadas++;

		}
	}
	
	if(($materiasReprobadas>0)&&($materiasReprobadas<=$param1["fConfiguracion"]["limiteMaximoPromocionCondicionada"]))
		return 1;
	return 0;

}

function puedeInscribirseAlMismoGrado($param1)
{
	$materiasReprobadas=0;	
	foreach($param1["arrMaterias"] as $o)
	{
		if($o->situacion!=1)
		{
			$materiasReprobadas++;

		}
	}
	if($materiasReprobadas>$param1["fConfiguracion"]["limiteMaximoPromocionCondicionada"])
		return 1;
	return 0;

}

function alumnoExcedeNumeroMaximoRepeticionesGrado($param1)
{
	global $con;
	$reInscribeGrado=puedeInscribirseAlMismoGrado($param1);
	if($reInscribeGrado==1)
	{
		$consulta="SELECT idGrado,noInscripcion FROM 4529_alumnos WHERE idUsuario=".$param1["idUsuario"]." AND idInstanciaPlanEstudio=".$param1["idInstanciaPlan"]." ORDER BY idAlumnoTabla desc";
		$fInscripcion=$con->obtenerPrimeraFila($consulta);
		if($fInscripcion[1]>$param1["fConfiguracion"]["limiteMaximoInscripcionMismoGrado"])
			return 1;
	}
	return 0;
}

function gradoInscripcionNoAperturado($param1)
{
	global $con;
	$idGrado=0;
	$consulta="SELECT idGrado,noInscripcion FROM 4529_alumnos WHERE idUsuario=".$param1["idUsuario"]." AND idInstanciaPlanEstudio=".$param1["idInstanciaPlan"]." ORDER BY idAlumnoTabla desc";
	$fInscripcion=$con->obtenerPrimeraFila($consulta);
	
	$inscribeSiguienteGrado=puedeInscribirseASiguienteGrado($param1);
	if($inscribeSiguienteGrado==0)
	{
		$inscribeSiguienteGrado=puedeInscribirseASiguienteGradoCondicionado($param1);
	}
	else
		$idGrado=$fInscripcion[0];
	
	if($inscribeSiguienteGrado!=0)
	{
		$idGrado=$fInscripcion[0];
		$consulta="SELECT idGradoSiguiente FROM 4501_Grado WHERE idGrado=".$idGrado;
		
		$idGrado=$con->obtenerValor($consulta);
		if($idGrado=="")
			$idGrado=-1;
	}
	else
	{
		$inscribeSiguienteGrado=puedeInscribirseAlMismoGrado($param1);
		if($inscribeSiguienteGrado!=0)
		{
			$idGrado=$fInscripcion[0];
		}
		else
			return 1;
	}

	$consulta="SELECT count(*) FROM 4546_estructuraPeriodo WHERE idGrado=".$idGrado." AND idCiclo=".$param1["idCiclo"]." AND  idPeriodo=".$param1["idPeriodo"]."  AND idInstanciaPlanEstudio=".$param1["idInstanciaPlan"];

	$nReg=$con->obtenerValor($consulta);
	
	
	
	if($nReg>0)
	{
		return 0;
	}
	
	$consulta="SELECT leyendaGrado FROM 4501_Grado WHERE idGrado=".$idGrado;
	$lGrado=$con->obtenerValor($consulta);
	$objResultado["valor"]=1;
	
	$objResultado["complementario"]="Se esperaba la apertura del grado: ".$lGrado;
	return $objResultado;

}

function gradoInscribeAlumno($idUsuario,$idInstancia)
{
	$idGrado=-1;
	$consulta="SELECT idGrado,noInscripcion FROM 4529_alumnos WHERE idUsuario=".$idUsuario." AND idInstanciaPlanEstudio=".$idInstancia." ORDER BY idAlumnoTabla desc";
	$fInscripcion=$con->obtenerPrimeraFila($consulta);
	
	$inscribeSiguienteGrado=puedeInscribirseASiguienteGrado($param1);
	if($inscribeSiguienteGrado==0)
	{
		$inscribeSiguienteGrado=puedeInscribirseASiguienteGradoCondicionado($param1);
	}
	else
		$idGrado=$fInscripcion[0];
	
	if($inscribeSiguienteGrado!=0)
	{
		$idGrado=$fInscripcion[0];
		$consulta="SELECT idGradoSiguiente FROM 4501_Grado WHERE idGrado=".$idGrado;
		
		$idGrado=$con->obtenerValor($consulta);
		if($idGrado=="")
			$idGrado=-1;
	}
	else
	{
		$inscribeSiguienteGrado=puedeInscribirseAlMismoGrado($param1);
		if($inscribeSiguienteGrado!=0)
		{
			$idGrado=$fInscripcion[0];
		}
		
	}
	return $idGrado;
}

?>