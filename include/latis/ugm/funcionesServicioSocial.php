<?php
	function ocultarPagoServicioSocialV2($idProceso,$idFormulario,$idRegistro)
	{
		global $con;
		$consulta="select cmbTipoSolicitud,idEstado from _759_tablaDinamica where id__759_tablaDinamica=".$idRegistro;
		$fRegistro=$con->obtenerPrimeraFila($consulta);

		$tipoSolicitud=$fRegistro[0];
		if(($tipoSolicitud==1)||($fRegistro[1]!=2))
			return true;
		return false;
		
	}
	
	function ocultarPagoPracticasProfesionalesV2($idProceso,$idFormulario,$idRegistro)
	{
		global $con;
		$consulta="select cmbTipoSolicitud,idEstado from _759_tablaDinamica where id__759_tablaDinamica=".$idRegistro;
		$fRegistro=$con->ObtenerPrimeraFila($consulta);
		$tipoSolicitud=$fRegistro[0];
		if(($tipoSolicitud==2)||($fRegistro[1]!=2))
			return true;	
		return false;
	}
	
	function ocultarGeneracionCartaDescargarPresentacion($idProceso,$idFormulario,$idRegistro,$actor)
	{
		global $con;
		
		if($actor!=513)
			return true;
		return false;
	}
?>