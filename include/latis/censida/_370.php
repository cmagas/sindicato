<?php
	include_once("latis/conexionBD.php"); 
	
	function rendererComentarios($idFormulario,$idReferencia,$actor)
	{
		global $con;
		$idRegistro=$idReferencia;
		$arrRegistros="";
		$nReg=0;
		$nNuevos=0;
		$x=0;
		
		
		$consulta="select codigo from _370_tablaDinamica WHERE id__370_tablaDinamica=".$idRegistro;
		$folio=$con->obtenerValor($consulta);
		$consulta="SELECT distinct comentario,dictamen,fechaHoraDictamen AS fechaComentario,actorResponsableDictamen AS actorComentario,
				idFormularioDictamen AS idFormulario,idRegistroDictamen AS idRegistro,visualizado,idComentario FROM 2002_comentariosRegistro WHERE idFormulario=".$idFormulario."  AND idRegistro=".$idRegistro;
		$res=$con->obtenerFilas($consulta);		
		while($fila=mysql_fetch_row($res))
		{
			$nReg++;
			
			if($fila[6]==0)
			{
				$nNuevos++;
			}
			
		}
		
		$consulta="SELECT id__401_tablaDinamica,txtDescripcion FROM _401_tablaDinamica WHERE idReferencia=".$idRegistro;
		$resReg=$con->obtenerFilas($consulta);
		while($fReg=mysql_fetch_row($resReg))
		{
			$consulta="SELECT distinct comentario,dictamen,fechaHoraDictamen AS fechaComentario,actorResponsableDictamen AS actorComentario,
					idFormularioDictamen AS idFormulario,idRegistroDictamen AS idRegistro,visualizado,idComentario FROM 2002_comentariosRegistro WHERE idFormulario=401  AND idRegistro=".$fReg[0];
			$res=$con->obtenerFilas($consulta);		
			while($fila=mysql_fetch_row($res))
			{
				$nReg++;
				
				if($fila[6]==0)
				{
					
					$nNuevos++;
				}
				
			}
		}
		
		$consulta="SELECT id__395_tablaDinamica,descripcion FROM _395_tablaDinamica WHERE idReferencia=".$idRegistro;
		$resReg=$con->obtenerFilas($consulta);
		while($fReg=mysql_fetch_row($resReg))
		{
			$consulta="SELECT distinct comentario,dictamen,fechaHoraDictamen AS fechaComentario,actorResponsableDictamen AS actorComentario,
					idFormularioDictamen AS idFormulario,idRegistroDictamen AS idRegistro,visualizado,idComentario FROM 2002_comentariosRegistro WHERE idFormulario=395  AND idRegistro=".$fReg[0];
			$res=$con->obtenerFilas($consulta);		
			while($fila=mysql_fetch_row($res))
			{
				$nReg++;
				if($fila[6]==0)
				{
					$nNuevos++;
				}
				
			}
		}
		
		$consulta="SELECT id__395_tablaDinamica,descripcion FROM _395_tablaDinamica WHERE idReferencia=".$idRegistro;
		$resReg=$con->obtenerFilas($consulta);
		while($fReg=mysql_fetch_row($resReg))
		{
			$consulta="SELECT distinct comentario,dictamen,fechaHoraDictamen AS fechaComentario,actorResponsableDictamen AS actorComentario,
					idFormularioDictamen AS idFormulario,idRegistroDictamen AS idRegistro,visualizado,idComentario FROM 2002_comentariosRegistro WHERE idFormulario=395  AND idRegistro=".$fReg[0];
			$res=$con->obtenerFilas($consulta);		
			while($fila=mysql_fetch_row($res))
			{
				$nReg++;
				if($fila[6]==0)
				{
					$nNuevos++;
				}
				
			}
		}
		
		
		
		$consulta="SELECT i.idInforme,i.noInforme FROM 3001_evaluacionesInformeTecnico e,3000_informesTecnicos i WHERE 
					e.idInforme=i.idInforme AND i.idFormulario=370 AND i.idReferencia=".$idRegistro;
		
		
		$resReg=$con->obtenerFilas($consulta);
		while($fReg=mysql_fetch_row($resReg))
		{
			$consulta="SELECT fechaEvaluacion AS fechaComentario,u.Nombre AS responsable,resultadoEvaluacion AS dictamen,comentarios AS comentario,e.idEvaluacion,visualizado FROM 3001_evaluacionesInformeTecnico e,800_usuarios u WHERE e.idInforme=".$fReg[0]." 
					AND u.idUsuario=e.idResponsableEval ORDER BY fechaEvaluacion DESC";
			$res=$con->obtenerFilas($consulta);		
			while($fila=mysql_fetch_row($res))
			{
				$nReg++;
				if($fila[5]==0)
				{
					$nNuevos++;
				}
				
			}
		}

		$folio="N/E";
		$consulta="SELECT idFactura,visualizado,tipoComprobante,fechaEvaluacion,razonSocial,folioComprobante,comprobante FROM 101_comprobantesPresupuestales c,595_proveedores p,106_tipoComprobante t 
				WHERE idFormulario=370 AND idReferencia=".$idRegistro." AND c.situacion<>0 and p.idProveedor=c.idProveedor and t.idTipoComprobante=c.tipoComprobante";
		$resReg=$con->obtenerFilas($consulta);
		while($fReg=mysql_fetch_row($resReg))
		{
			$nReg++;
			if($fReg[1]==0)
			{
				$nNuevos++;
				
			}
			
		}
		
		$lblComentarios="";
		if($nReg==1)
			$lblComentarios="1 comentario";
		else
			$lblComentarios=$nReg." comentarios";
		
		$lblNuevos="";
		if($nNuevos==1)
			$lblNuevos="<span style='color:#F00'><b><span id='lblNuevos'>1</span></span></b> nuevo";
		else
			$lblNuevos="<span  style='color:#F00'><b><span id='lblNuevos'>".$nNuevos."</span></b></span> nuevos";
		return $lblComentarios." (".$lblNuevos.")";
	}
?>