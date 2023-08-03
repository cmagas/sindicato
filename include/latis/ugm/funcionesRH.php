<?php
	function tieneSeguroSocial($idUsuario)
	{
		global $con;
		$consulta="SELECT IMSS FROM 802_identifica WHERE idUsuario=".$idUsuario;
		$imss=$con->obtenerValor($consulta);
		if(trim($imss)!="")
			return true;
		return false;
	}
	
	
	function esAdministrativo($idUsuario)
	{
		global $con;
		$consulta="SELECT COUNT(*) FROM 979_horariosLaborUsuario WHERE idUsuario=".$idUsuario;
		$nReg=$con->obtenerValor($consulta);
		if($nReg>0)
			return true;
		return false;
	}
	
	function tieneInfonavit($idUsuario)
	{
		global $con;
		$consulta="SELECT COUNT(*) FROM _460_tablaDinamica WHERE cmbUsuario=".$idUsuario." AND cmbConceptos=1";
		$nReg=$con->obtenerValor($consulta);
		if($nReg>0)
			return true;
		return false;
	}
	
	function obtenerTipoContratacion($idUsuario)
	{
		global $con;
		$consulta="SELECT tipoContratacion FROM 801_adscripcion WHERE idUsuario=	".$idUsuario;
		$tContratacion=$con->obtenerValor($consulta);
		switch($tContratacion)
		{
			case 10:
				return 2;
			break;
			case 14:
				return 1;
			break;	
			default:
				return 3;
			break;
		}
	}
	
	function obtenerTipoIngreso($idUsuario, $plantel,$fecha)
	{
		global $con;
		$consulta="SELECT COUNT(*) FROM 4553_contratosProfesores WHERE idProfesor=".$idUsuario." AND plantel='".$plantel."'";
		$nReg=$con->obtenerValor($consulta);
		if($nReg==0)
		{
			return 1;
		}
		$consulta="SELECT COUNT(*) FROM 4553_contratosProfesores WHERE idProfesor=".$idUsuario." AND plantel='".$plantel."' and '".$fecha."'>=fechaInicioContrato and '".$fecha."'<=fechaFinContrato";
		$nReg=$con->obtenerValor($consulta);
		if($nReg==0)
		{
			return 2;
		}
		return 3;
		
	}
?>