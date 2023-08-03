<?php
	include_once("latis/conexionBD.php");
	
	
	
	function asignacionLiderRevisor($idRegistro)
	{
		global $con;		
		$consulta="SELECT * FROM (
					SELECT idUsuario,(SELECT COUNT(*) FROM 1011_asignacionRevisoresProyectos a WHERE a.idUsuario=r.idUsuario AND a.idFormulario=541 AND  tipoRevisor=1) AS nRegistros 
					FROM 807_usuariosVSRoles r WHERE  r.idRol=116) AS tmp ORDER BY nRegistros ASC";
		$fDatos=$con->obtenerPrimeraFila($consulta);
		
		$consulta="INSERT INTO 1011_asignacionRevisoresProyectos(idUsuario,idProyecto,tipoRevisor,idFormulario,situacion,fechaAsignacion)
				VALUES(".$fDatos[0].",".$idRegistro.",1,541,1,'".date("Y-m-d H:i:s")."')";
		return $con->ejecutarConsulta($consulta);
		
		
	}
	
	function mostrarSeccionAsignacionRevisor($idActor)
	{
		global $con;
		$consulta="SELECT actor FROM 944_actoresProcesoEtapa WHERE idActorProcesoEtapa=".$idActor;
		$rol=$con->obtenerValor($consulta);
		if($rol=="116_0")
			return 1;
		return 0;
	}
	
	function mostrarSeccionCuestionarioEvaluacion($idActor)
	{
		global $con;
		$consulta="SELECT actor FROM 944_actoresProcesoEtapa WHERE idActorProcesoEtapa=".$idActor;
		$rol=$con->obtenerValor($consulta);
		if(($rol=="117_0")||($rol=="116_0")||($rol=="118_0"))
			return 1;
		return 0;
	}
	
	function permitirEdicionCuestionarioEvaluacion($idActor,$idRegistro)
	{
		global $con;
		$consulta="SELECT actor FROM 944_actoresProcesoEtapa WHERE idActorProcesoEtapa=".$idActor;
		$rol=$con->obtenerValor($consulta);
		if(($rol=="117_0")||($rol=="116_0")||($rol=="118_0"))
		{
			$consulta="SELECT count(*) FROM _544_tablaDinamica WHERE idReferencia=".$idRegistro." AND responsable=".$_SESSION["idUsr"];
			$nReg=$con->obtenerValor($consulta);
			if($nReg==0)
				return 1;
			return 0;
		}
		return 0;
	}
	
	function registrarDatosCuentaRevisor($idFormulario,$idRegistro)
	{
		global $con;
		$consulta="SELECT idUsuario,nombreRevisor,emailContacto,emailContacto FROM _545_tablaDinamica WHERE id__545_tablaDinamica=".$idRegistro;
		$fDatosRevisor=$con->obtenerPrimeraFila($consulta);
		$idUsr=$fDatosRevisor[0];
		
		if($idUsr=="")
		{
			$idUsr=crearBaseUsuario("","",$fDatosRevisor[1],$fDatosRevisor[2],"","","117");
		}
		$arrParam["mail"]=$fDatosRevisor[2];
		$arrParam["nombreRevisor"]=$fDatosRevisor[1];
		
		$consulta="SELECT Login, PASSWORD FROM 800_usuarios WHERE idUsuario=".$idUsr;
		$fDatosUsr=$con->obtenerPrimeraFila($consulta);
		
		$arrParam["usr"]=$fDatosUsr[0];
		$arrParam["passwd"]=$fDatosUsr[1];
		
		@enviarMensajeEnvio(12,$arrParam);
		if($fDatosRevisor[3]!="")
		{
			$arrParam["mail"]=$fDatosRevisor[3];
			
		}
		@enviarMensajeEnvio(12,$arrParam);
		
	}
	
	function registrarEvaluacionRevisor($idFormulario,$idRegistro)
	{
		global $con;
		$consulta="SELECT idReferencia,fechaCreacion,responsable FROM _544_tablaDinamica WHERE id__544_tablaDinamica=".$idRegistro;
		$fRegistro=$con->obtenerPrimeraFila($consulta);
		
		$consulta="UPDATE 1011_asignacionRevisoresProyectos SET situacion=2,fechaEvaluacion='".$fRegistro[1]."',idReferencia=".$idRegistro.",idCuestionarioEval=".$idFormulario.",
				tipoCuestionario=0 WHERE idUsuario=".$fRegistro[2]." AND idProyecto=".$fRegistro[0]." AND idFormulario=541";
				
		if($con->ejecutarConsulta($consulta))			
		{
			if(!existeRol("'116_0'"))
				echo "window.parent.parent.cerrarVentanaFancy();return;";
			return true;
		}
		return false;
				
		
	}
	
?>