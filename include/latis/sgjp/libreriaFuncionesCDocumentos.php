<?php 
include_once("latis/conexionBD.php");
include_once("latis/utiles.php");


function obtenerTitularRolCDocumento($rolDestinatario,$idRegistroFormato,$tipoDocumento,$iFormulario,$iRegistro)
{
	global $con;
	
	$arrResultado=array();
	
	$cAdministrativa=obtenerCarpetaAdministrativaProceso($iFormulario,$iRegistro);
	
	
	$consulta="SELECT unidadGestion FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$cAdministrativa."'";
	$unidadGestion=$con->obtenerValor($consulta);
	
	$consulta="SELECT u.idUsuario FROM 807_usuariosVSRoles uR,801_adscripcion a,800_usuarios u WHERE uR.codigoRol='".$rolDestinatario."'
			and uR.idUsuario=u.idUsuario AND u.idUsuario=a.idUsuario  AND a.Institucion='".$unidadGestion.
			"' AND u.cuentaActiva=1 and u.idUsuario not in(1) ORDER BY u.Nombre";

	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		$o=json_decode('{"idUsuario":"'.$fila[0].'"}');
		array_push($arrResultado,$o);
	}
			
	return $arrResultado;
}

function obtenerJuezDestinatarioCDocumento($rolDestinatario,$idRegistroFormato,$tipoDocumento,$iFormulario,$iRegistro)
{
	global $con;
	$fDocumentoComp=NULL;
	$fechactual=date("Y-m-d");
	$idUsuarioDestinatario=-1;
	$fDocumento[0]=$iFormulario;
	$fDocumento[1]=$iRegistro;
	if($fDocumento[0]<0)
	{
		if($fDocumento[0]==-2)
		{
			$consulta="SELECT * FROM 7035_informacionDocumentos WHERE idRegistro=".$fDocumento[1];
			$fDocumentoComp=$con->obtenerPrimeraFilaAsoc($consulta);
			if($fDocumentoComp["datosParametros"]!="")
			{
				$oComp=json_decode(bD($fDocumentoComp["datosParametros"]));
				if(isset($oComp->usuarioDestinatario))
				{
					$idUsuarioDestinatario=$oComp->usuarioDestinatario;
				}
			}
		}
	}
	else
	{
		$fDocumentoComp["idFormulario"]=$fDocumento[0];
		$fDocumentoComp["idReferencia"]=$fDocumento[1];
	}
	
	$cAdministrativa=obtenerCarpetaAdministrativaProceso($fDocumento[0],$fDocumento[1]);

	$consulta="SELECT unidadGestion,tipoCarpetaAdministrativa FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$cAdministrativa."'";
	$fCarpeta=$con->obtenerPrimeraFila($consulta);
	$unidadGestion=$fCarpeta[0];
	
	$consulta="SELECT id__17_tablaDinamica FROM _17_tablaDinamica WHERE claveUnidad='".$unidadGestion."'";
	$idUnidad=$con->obtenerValor($consulta);
	
	$arrJueces=array();
	if($idUsuarioDestinatario==-1)
	{
		if($fCarpeta[1]==6)
		{
			$idUsuarioDestinatario=obtenerJuezAtencionCarpetaUnica($cAdministrativa,$fechactual);
			if($idUsuarioDestinatario!=-1)
				array_push($arrJueces,$idUsuarioDestinatario);
		}
		else
		{
		
			if($fDocumentoComp)
			{
				$idProcesoPadre=obtenerIdProcesoFormulario($fDocumentoComp["idFormulario"]);
		
				if(($idProcesoPadre!=-1)&&($idProcesoPadre!=""))
				{
					$consulta="SELECT idUsuarioDestinatario FROM _578_tablaDinamica WHERE idProcesoPadre=".$idProcesoPadre.
							" AND idReferencia=".$fDocumentoComp["idReferencia"]." order by id__578_tablaDinamica desc";
		
					$idUsuarioDestinatario=$con->obtenerValor($consulta);
					if($idUsuarioDestinatario!="")
					{
						array_push($arrJueces,$idUsuarioDestinatario);
					}
				}
			}
		}
	}
	else
	{
		array_push($arrJueces,$idUsuarioDestinatario);
	}
	
	
	
	if(sizeof($arrJueces)==0)
	{
		$consulta="SELECT id__17_tablaDinamica FROM _17_tablaDinamica WHERE claveUnidad='".$unidadGestion."'";
		$idUGJ=$con->obtenerValor($consulta);
		$consulta="SELECT j.usuarioJuez FROM _26_tablaDinamica j,_292_tablaDinamica jt WHERE jt.idEstado=1 and jt.nombreJueces=j.usuarioJuez
					and j.idReferencia=".$idUnidad." and '".$fechactual."'>=fechaInicial and '".$fechactual."'<=fechaFinal and
					jt.idEstado=1";
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			array_push($arrJueces,$fila[0]);
			
		}
		
		
		
		if($fDocumentoComp["idFormulario"]==460)
		{
			$query="SELECT * FROM _460_tablaDinamica WHERE id__460_tablaDinamica=".$fDocumentoComp["idReferencia"];	
			$fAmparoPromocion=$con->obtenerPrimeraFilaAsoc($query);
			
			if($fAmparoPromocion["resolverAmparoTransitorio"]==1)
			{
				$query="SELECT * FROM _536_tablaDinamica WHERE idReferencia=".$fDocumentoComp["idReferencia"];
				$fResolucionAmparo=	$con->obtenerPrimeraFilaAsoc($query);
				if($fResolucionAmparo["existeJuezConoce"]==1)
				{
					$query="SELECT rj.usuarioJuez FROM _26_tablaDinamica rj WHERE 
							rj.id__26_tablaDinamica=".$fResolucionAmparo["juezConoce"]." and
						rj.usuarioJuez<>-1 AND rj.usuarioJuez IS NOT NULL";
					$rJuezAmparo=$con->obtenerFilas($query);
					if($con->filasAfectadas>0)
					{
						$arrJuecesAux=array();
						while($filaJuez=mysql_fetch_row($rJuezAmparo))
						{
							if(esJuezDisponibleIncidencia($filaJuez[0],$fechactual))
								array_push($arrJuecesAux,$filaJuez[0]);
							
						}
						
						if(sizeof($arrJuecesAux)>0)
						{
							$arrJueces=$arrJuecesAux;
						}
					}
				}
				
				
			}
			else
			{
				$query="SELECT rj.usuarioJuez FROM _460_juezReferido j,_26_tablaDinamica rj WHERE 
						idPadre=".$fDocumentoComp["idReferencia"]." AND rj.id__26_tablaDinamica=j.idOpcion AND 
						rj.usuarioJuez<>-1 AND rj.usuarioJuez IS NOT NULL";
				$rJuezAmparo=$con->obtenerFilas($query);
				if($con->filasAfectadas>0)
				{
					$arrJuecesAux=array();
					while($filaJuez=mysql_fetch_row($rJuezAmparo))
					{
						if(esJuezDisponibleIncidencia($filaJuez[0],$fechactual))
								array_push($arrJuecesAux,$filaJuez[0]);
						
					}
					if(sizeof($arrJuecesAux)>0)
					{
						$arrJueces=$arrJuecesAux;
					}
				}
			
				
			}
			
		}
	}
	
	$arrResultado=array();
	foreach($arrJueces as $idJuez)
	{
		$consulta="SELECT clave,(SELECT Nombre FROM 800_usuarios WHERE idUsuario=j.usuarioJuez) AS juez FROM _26_tablaDinamica j WHERE usuarioJuez=".$idJuez;
		$fila=$con->obtenerPrimeraFila($consulta);
		$o=json_decode('{"idUsuario":"'.$idJuez.'","etiqueta":"['.$fila[0].'] '.cv($fila[1]).'"}');
		array_push($arrResultado,$o);
	}
			
	return $arrResultado;
	
}

function obtenerJuecesUnidadGestionCDocumento($rolDestinatario,$idRegistroFormato,$tipoDocumento,$iFormulario,$iRegistro)
{
	global $con;
	global $con;
	
	$arrResultado=array();
	
	$fDocumento[0]=$iFormulario;
	$fDocumento[1]=$iRegistro;
	$cAdministrativa=obtenerCarpetaAdministrativaProceso($fDocumento[0],$fDocumento[1]);
	
	$consulta="SELECT unidadGestion,tipoCarpetaAdministrativa FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$cAdministrativa."'";
	$fCarpeta=$con->obtenerPrimeraFila($consulta);
	$unidadGestion=$fCarpeta[0];
	
	$tipoJuez=0;
	switch($fCarpeta[1])
	{
		case 1:
			$tipoJuez=1;
		break;
		case 5:
			$tipoJuez=2;
		break;
		case 6:
			$tipoJuez=3;
		break;
		default:
			$tipoJuez="1,2,3";
		break;
	}
	
	$consulta="SELECT id__17_tablaDinamica FROM _17_tablaDinamica WHERE claveUnidad='".$unidadGestion."'";
	$idUnidad=$con->obtenerValor($consulta);
	
	$consulta="SELECT clave,usuarioJuez,(SELECT nombre FROM 800_usuarios WHERE idUsuario=u.idUsuario) AS nombreJuez 
				FROM _26_tablaDinamica j,800_usuarios u,_26_tipoJuez tJ WHERE idReferencia=".$idUnidad."
				AND u.idUsuario=j.usuarioJuez AND usuarioJuez<>-1 AND tJ.idPadre=j.id__26_tablaDinamica
				AND tJ.idOpcion in(".$tipoJuez.") and j.clave<>'000' ORDER BY tj.idOpcion,j.clave";

	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		$o=json_decode('{"idUsuario":"'.$fila[1].'","etiqueta":"['.$fila[0].'] '.cv($fila[2]).'"}');
		array_push($arrResultado,$o);
	}
			
	return $arrResultado;
}
?>