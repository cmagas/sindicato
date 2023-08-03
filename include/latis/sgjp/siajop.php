<?php 
include_once("latis/conexionBD.php");
include_once("latis/utiles.php");
include_once("latis/nusoap/nusoap.php");

function obtenerInformacionCatalogo($idCatalogo)
{
	global $con;
	
	$xmlRespuesta="<?xml version=\"1.0\" encoding=\"utf-8\"?><siajop><catalog_type>".$idCatalogo."</catalog_type><records>";
	
	switch($idCatalogo)
	{
		case 1: //Audiencias
			$consulta="SELECT id__4_tablaDinamica,tipoAudiencia FROM _4_tablaDinamica";
			$res=$con->obtenerFilas($consulta);
			while($fila=mysql_fetch_row($res))
			{
				$oXML='<record id="'.$fila[0].'">'.cv($fila[1]).'</record>';
				$xmlRespuesta.=$oXML;	
			}
		
		break;
		case 2: //Jueces
			$consulta="SELECT usuarioJuez,clave,u.Nombre FROM _26_tablaDinamica j,800_usuarios u WHERE u.idUsuario=j.usuarioJuez";
			$res=$con->obtenerFilas($consulta);
			while($fila=mysql_fetch_row($res))
			{
				$oXML='<record id="'.$fila[0].'"  judgeCode="'.$fila[1].'" status="1">'.cv($fila[2]).'</record>';
				$xmlRespuesta.=$oXML;	
			}
		
		break;
		case 3: //UGJ
			$consulta="SELECT id__17_tablaDinamica,claveUnidad,nombreUnidad FROM _17_tablaDinamica WHERE cmbCategoria=1";
			$res=$con->obtenerFilas($consulta);
			while($fila=mysql_fetch_row($res))
			{
				$oXML='<record id="'.$fila[0].'" unitCode="'.$fila[1].'">'.cv($fila[2]).'</record>';
				$xmlRespuesta.=$oXML;	
			}
		
		break;
		case 4: //Salas
			$consulta="SELECT id__15_tablaDinamica,nombreSala FROM _15_tablaDinamica WHERE idReferencia 
					IN(SELECT idReferencia FROM _17_tablaDinamica WHERE cmbCategoria=1)";
			$res=$con->obtenerFilas($consulta);
			while($fila=mysql_fetch_row($res))
			{
				$oXML='<record id="'.$fila[0].'">'.cv($fila[1]).'</record>';
				$xmlRespuesta.=$oXML;	
			}
		
		break;
		case 5: //Tipos de participantes
			$consulta="SELECT id__5_tablaDinamica,nombreTipo FROM _5_tablaDinamica";
			$res=$con->obtenerFilas($consulta);
			while($fila=mysql_fetch_row($res))
			{
				$oXML='<record id="'.$fila[0].'">'.cv($fila[1]).'</record>';
				$xmlRespuesta.=$oXML;	
			}
		
		break;
		case 6: //Motivos de pausa
		
			$oXML='<record id="1">Receso</record><record id="2">Cambio de sala</record><record id="3">Fallas técnicas</record><record id="4">Otros</record>';
			$xmlRespuesta.=$oXML;	
		
			/*$consulta="SELECT id__5_tablaDinamica,nombreTipo FROM _5_tablaDinamica";
			$res=$con->obtenerFilas($consulta);
			while($fila=mysql_fetch_row($res))
			{
				$oXML='<record id="'.$fila[0].'">'.cv($fila[1]).'</record>';
				$xmlRespuesta.=$oXML;	
			}*/
		
		break;
		case 7: //Telepresncias
		
			$oXML='<record id="1">Reclusorio Norte</record><record id="2">Reclusorio Sur</record><record id="3">Reclusorio Oriente</record><record id="4">Santa Martha</record>';
			$xmlRespuesta.=$oXML;	
		
			/*$consulta="SELECT id__5_tablaDinamica,nombreTipo FROM _5_tablaDinamica";
			$res=$con->obtenerFilas($consulta);
			while($fila=mysql_fetch_row($res))
			{
				$oXML='<record id="'.$fila[0].'">'.cv($fila[1]).'</record>';
				$xmlRespuesta.=$oXML;	
			}*/
		
		break;
	}
	$xmlRespuesta.="</records></siajop>";
	return $xmlRespuesta;
	
}

function reportarAudienciaSiajop($idEvento,$formato=1)
{
	global $con;
	$consulta="SELECT * FROM 7000_eventosAudiencia WHERE  idRegistroEvento=".$idEvento;
	$fRegistro=$con->obtenerPrimeraFilaAsoc($consulta);
	

	$consulta="SELECT carpetaAdministrativa,idCarpetaAdministrativa FROM 7007_contenidosCarpetaAdministrativa WHERE tipoContenido=3 AND idRegistroContenidoReferencia=".$idEvento;
	$fDatosCarpetaJudicial=$con->obtenerPrimeraFila($consulta);
	$carpetaAdministrativa=$fDatosCarpetaJudicial[0];
	if($carpetaAdministrativa=="")
	{
		return true;
	}
	
	$consulta="SELECT idActividad FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$carpetaAdministrativa."'";
	
	if(($fDatosCarpetaJudicial[1]!=-1)&&($fDatosCarpetaJudicial[1]!=""))
		$consulta.=" and idCarpeta=".$fDatosCarpetaJudicial[1];
		
	$idActividad=$con->obtenerValor($consulta);
	
	
	$consulta="SELECT idJuez FROM 7001_eventoAudienciaJuez WHERE idRegistroEvento=".$idEvento;
	$res=$con->obtenerFilas($consulta);
	
	$arrJueces="";
	while($fJuez=mysql_fetch_row($res))
	{
		
		$o='<id_judge>'.$fJuez[0].'</id_judge>';
		$arrJueces.=$o;
	}

    $arrParticipantes="";    
	$consulta="SELECT (CONCAT(IF(apellidoPaterno IS NULL,'',apellidoPaterno),' ',IF(apellidoMaterno IS NULL,'',apellidoMaterno),
' ',IF(nombre IS NULL,'',nombre))) AS participante,r.idFiguraJuridica FROM 7005_relacionFigurasJuridicasSolicitud r,_47_tablaDinamica p 
				WHERE r.idFiguraJuridica IN(2,4,1) AND p.id__47_tablaDinamica=r.idParticipante AND r.idActividad=".$idActividad;
		//echo $consulta;
	$rParticipantes=$con->obtenerFilas($consulta);		
	while($fParticipante=mysql_fetch_row($rParticipantes))
	{

		$o='<participant participantType="'.$fParticipante[1].'" protectedWitness="0">'.cv(str_replace('"','',$fParticipante[0])).'</participant>';
		$arrParticipantes.=$o;
	}
	
	


	$xml="";
    if($formato==1)
	{
		$xml='<?xml version="1.0" encoding="utf-8" ?>
				<siajop>
					<audience id="'.$idEvento.'" status="1">
						<id_audience_type>'.$fRegistro["tipoAudiencia"].'</id_audience_type>
						<expedient>'.$carpetaAdministrativa.'</expedient>
						<id_management_unit>'.$fRegistro["idCentroGestion"].'</id_management_unit>
						<id_room>'.$fRegistro["idSala"].'</id_room>
						<recording_date>'.date("d/m/Y",strtotime($fRegistro["horaInicioEvento"])).'</recording_date>
						<recording_time>'.date("H:i:s",strtotime($fRegistro["horaInicioEvento"])).'</recording_time>
						<judges> 
							   '.$arrJueces.'
						</judges>
						<participants>						
							'.$arrParticipantes.'
						</participants>
					</audience>
				</siajop>';
	}
	else
	{
		$xml='<audience id="'.$idEvento.'" status="1">
				  <id_audience_type>'.$fRegistro["tipoAudiencia"].'</id_audience_type>
				  <expedient>'.$carpetaAdministrativa.'</expedient>
				  <id_management_unit>'.$fRegistro["idCentroGestion"].'</id_management_unit>
				  <id_room>'.$fRegistro["idSala"].'</id_room>
				  <recording_date>'.date("d/m/Y",strtotime($fRegistro["horaInicioEvento"])).'</recording_date>
				  <recording_time>'.date("H:i:s",strtotime($fRegistro["horaInicioEvento"])).'</recording_time>
				  <judges> 
						 '.$arrJueces.'
				  </judges>
				  <participants>						
					  '.$arrParticipantes.'
				  </participants>
			  </audience>';
	}
	if(($fRegistro["situacion"]==3)||($fRegistro["situacion"]==6))
		$xml=str_replace('status="1"','status="0"',$xml);
	
	return $xml;
}

function notificarEventoAudienciaSIAJOP($idEvento,$direccionIP)
{
	global $con;
	
	$idRegistroBitacora=registrarBitacoraNotificacionSIAJOP($idEvento);
	$response="";
	$idWS=0;
	try
	{
		$consulta="SELECT * FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$idEvento;
		$fDatosEvento=$con->obtenerPrimeraFilaAsoc($consulta);
		
		
		@reportarAudienciaPGJ($fDatosEvento["idFormulario"],$fDatosEvento["idRegistroSolicitud"]);
		
		$idWS=$fDatosEvento["idEdificio"]*10;
		$xml=reportarAudienciaSiajop($idEvento);
		
		
		
		$urlWebServices="http://".$direccionIP.":8080/ServiceSIAJOP.asmx";
		$client = new nusoap_client($urlWebServices."?wsdl","wsdl");
		$parametros=array();
		$parametros["xml"]=$xml;
		
		$response = $client->call("SynchronizationAudience", $parametros);
		if($response=="")
		{
			@actualizarBitacoraNotificacionSIAJOP($idRegistroBitacora,0,"No se pudo establecer conexion con el servidor",0,bE(""),$idWS);
			return;
		}
		$response=$response["SynchronizationAudienceResult"];
		
		$cXML=simplexml_load_string($response);	
		$resultado=(string)$cXML->resultado[0];
		@actualizarBitacoraNotificacionSIAJOP($idRegistroBitacora,$resultado,(string)$cXML->datosComplementarios[0],0,bE($response),$idWS);
		@notificarEventoAudienciaSIAJOPCabina($idEvento);
		
	}
	catch(Exception $e)
	{
		
		@actualizarBitacoraNotificacionSIAJOP($idRegistroBitacora,0,$e->getMessage(),1,bE($response),$idWS);
		@notificarEventoAudienciaSIAJOPCabina($idEvento);
		
		
		
	}		
		
}

function notificarCancelacionEventoAudienciaSIAJOP($idEvento,$direccionIP)
{
	global $con;
	$idRegistroBitacora=registrarBitacoraNotificacionSIAJOP($idEvento);
	$response="";
	$idWS=100;
	try
	{
		$consulta="SELECT * FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$idEvento;
		$fDatosEvento=$con->obtenerPrimeraFilaAsoc($consulta);
		$idWS=$fDatosEvento["idEdificio"]*10;
		
		$xml=reportarAudienciaSiajop($idEvento);
		
		$xml=str_replace('status="1"','status="0"',$xml);
		$urlWebServices="http://".$direccionIP.":8080/ServiceSIAJOP.asmx";
		$client = new nusoap_client($urlWebServices."?wsdl","wsdl");
		$parametros=array();
		$parametros["xml"]=$xml;
		
		$response = $client->call("SynchronizationAudience", $parametros);
		

		$response=$response["SynchronizationAudienceResult"];
		
		$cXML=simplexml_load_string($response);	
		$resultado=(string)$cXML->resultado[0];
		
		actualizarBitacoraNotificacionSIAJOP($idRegistroBitacora,$resultado,(string)$cXML->datosComplementarios[0],0,bE($response),$idWS);
		@notificarCancelacionEventoAudienciaSIAJOPCabina($idEvento);
		
	}
	catch(Exception $e)
	{
		
		actualizarBitacoraNotificacionSIAJOP($idRegistroBitacora,0,$e->getMessage(),1,bE($response),$idWS);
		@notificarCancelacionEventoAudienciaSIAJOPCabina($idEvento);
		
		
		
	}		
		
}

function notificarActualizacionCatalogoSIAJOP($idCatalogo,$direccionIP)
{
	global $con;
	$idWS=1000;
	$urlWebServices="";
	$xml=obtenerInformacionCatalogo($idCatalogo);
	
	$idRegistroBitacora=registrarBitacoraNotificacionOperadores(bE($xml),1,1,$idWS);
	$response="";
	
	try
	{
		
		
		
		$urlWebServices="http://".$direccionIP.":8080/ServiceSIAJOP.asmx";
		$client = new nusoap_client($urlWebServices."?wsdl","wsdl");
		$parametros=array();
		$parametros["xml"]=$xml;
		
		$response = $client->call("SynchronizationCatalogs", $parametros);
		
		$response=($response["SynchronizationCatalogsResult"]);
		$cXML=simplexml_load_string($response);	
		$resultado=(string)$cXML->resultado[0];

		actualizarBitacoraNotificacionOperadores($idRegistroBitacora,$resultado,(string)$cXML->datosComplementarios[0],bE($response));
		
	}
	catch(Exception $e)
	{
		actualizarBitacoraNotificacionOperadores($idRegistroBitacora,0,$e->getMessage(),bE($response));
		
		
		
	}		
		
}

function reportarAudienciaSiajopV2($idEvento,$formato=1)
{
	global $con;
	global $tipoMateria;
	$consulta="SELECT * FROM 7000_eventosAudiencia WHERE  idRegistroEvento=".$idEvento;
	$fRegistro=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$esVirtual=false;
	
	$consulta="SELECT carpetaAdministrativa,idCarpetaAdministrativa FROM 7007_contenidosCarpetaAdministrativa WHERE 
				tipoContenido=3 AND idRegistroContenidoReferencia=".$idEvento;
	$fDatosCarpeta=$con->obtenerPrimeraFila($consulta);
	
	$carpetaAdministrativa=$fDatosCarpeta[0];
	if($carpetaAdministrativa=="")
	{
		
		return true;
	}
	
	$consulta="SELECT idActividad FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$carpetaAdministrativa."'";

	if($tipoMateria!="P")
	{
		$consulta.=" and idCarpeta=".$fDatosCarpeta[1];
	}

	$idActividad=$con->obtenerValor($consulta);
	
	
	$consulta="SELECT idJuez FROM 7001_eventoAudienciaJuez WHERE idRegistroEvento=".$idEvento;
	$res=$con->obtenerFilas($consulta);
	
	$arrJueces="";
	while($fJuez=mysql_fetch_row($res))
	{
		
		$o='<id_judge>'.$fJuez[0].'</id_judge>';
		$arrJueces.=$o;
	}

    $arrParticipantes="";    
	$consulta="SELECT (CONCAT(IF(apellidoPaterno IS NULL,'',apellidoPaterno),' ',IF(apellidoMaterno IS NULL,'',apellidoMaterno),
' ',IF(nombre IS NULL,'',nombre))) AS participante,r.idFiguraJuridica FROM 7005_relacionFigurasJuridicasSolicitud r,_47_tablaDinamica p 
				WHERE r.idFiguraJuridica IN(2,4,1) AND p.id__47_tablaDinamica=r.idParticipante AND r.idActividad=".($idActividad==""?-1:$idActividad);

	$rParticipantes=$con->obtenerFilas($consulta);		
	while($fParticipante=mysql_fetch_row($rParticipantes))
	{

		$o='<participant participantType="'.$fParticipante[1].'" protectedWitness="0">'.cv($fParticipante[0]).'</participant>';
		$arrParticipantes.=$o;
	}
	
	
	
	$arrTelepresencias=array();
	/*array_push($arrTelepresencias,2);
	array_push($arrTelepresencias,3);	*/
	
	$aTelepresencia="";
	foreach($arrTelepresencias as $t)
	{
		$oTelepresencia='<id_telepresence>'.$t.'</id_telepresence>';
		$aTelepresencia.=$oTelepresencia;
		
	}
	
	$arrDiligencias=array();
	/*array_push($arrDiligencias,"Diligencia XXX");*/
	
	$aDiligencias="";
	foreach($arrDiligencias as $d)
	{
		$oDiligencia='<address>'.$d.'</address>';
		$aDiligencias.=$oDiligencia;
		
	}
	
	$aParticipantesVirtuales="";
	$arrParticipantesVirtuales=array();
	if($esVirtual)
	{
		/*$participante=json_decode('{"tipo":"juez","telefono":"55461214","email":"juez9@gmail.com","nombre":"2402"}');
		array_push($arrParticipantesVirtuales,$participante);
		$participante=json_decode('{"tipo":"mp","telefono":"55171922","email":"mmmmm@gmail.com","nombre":"MMMM"}');
		array_push($arrParticipantesVirtuales,$participante);
		
		$participante=json_decode('{"tipo":"diso","telefono":"55115048","email":"diso2@gmail.com","nombre":"ZZZ"}');
		array_push($arrParticipantesVirtuales,$participante);*/
	}
	foreach($arrParticipantesVirtuales as $p)
	{
		$oParticipante='<participant type="'.$p->tipo.'"  phone="'.$p->telefono.'" email="'.$p->email.'">'.$p->nombre.'</participant>';
		$aParticipantesVirtuales.=$oParticipante;
		
	}
	
	
		
	$xml="";
    if($formato==1)
	{
		if(!$esVirtual)
		{
			$xml='<?xml version="1.0" encoding="utf-8" ?>
					<siajop>
						<audience id="'.$idEvento.'" status="1" isVirtual="0">
							<id_audience_type>'.$fRegistro["tipoAudiencia"].'</id_audience_type>
							<expedient>'.$carpetaAdministrativa.'</expedient>
							<id_management_unit>'.$fRegistro["idCentroGestion"].'</id_management_unit>
							<id_room>'.$fRegistro["idSala"].'</id_room>
							<recording_date>'.date("d/m/Y",strtotime($fRegistro["horaInicioEvento"])).'</recording_date>
							<recording_time>'.date("H:i:s",strtotime($fRegistro["horaInicioEvento"])).'</recording_time>
							<judges> 
								   '.$arrJueces.'
							</judges>
							<participants>						
								'.$arrParticipantes.'
							</participants>
							<telepresence>'.$aTelepresencia.'</telepresence>
							<diligence>'.$aDiligencias.'</diligence>
						</audience>
					</siajop>';
		}
		else
		{
			$xml='<?xml version="1.0" encoding="utf-8" ?>
					<siajop>
						<audience id="'.$idEvento.'" status="1" isVirtual="1">
							<id_audience_type>'.$fRegistro["tipoAudiencia"].'</id_audience_type>
							<expedient>'.$carpetaAdministrativa.'</expedient>
							<id_management_unit>'.$fRegistro["idCentroGestion"].'</id_management_unit>
							<id_room></id_room>
							<recording_date>'.date("d/m/Y",strtotime($fRegistro["horaInicioEvento"])).'</recording_date>
							<recording_time>'.date("H:i:s",strtotime($fRegistro["horaInicioEvento"])).'</recording_time>
							<judges></judges>
							<participants></participants>
							<telepresence></telepresence>
							<diligence></diligence>
							<virtual_participants>'.$aParticipantesVirtuales.'</virtual_participants>
						</audience>
					</siajop>';
		}
	}
	else
	{
		if(!$esVirtual)
		{
			$xml='		<audience id="'.$idEvento.'" status="1" isVirtual="0">
								<id_audience_type>'.$fRegistro["tipoAudiencia"].'</id_audience_type>
								<expedient>'.$carpetaAdministrativa.'</expedient>
								<id_management_unit>'.$fRegistro["idCentroGestion"].'</id_management_unit>
								<id_room>'.$fRegistro["idSala"].'</id_room>
								<recording_date>'.date("d/m/Y",strtotime($fRegistro["horaInicioEvento"])).'</recording_date>
								<recording_time>'.date("H:i:s",strtotime($fRegistro["horaInicioEvento"])).'</recording_time>
								<judges> 
									   '.$arrJueces.'
								</judges>
								<participants>						
									'.$arrParticipantes.'
								</participants>
								<telepresence>'.$aTelepresencia.'</telepresence>
								<diligence>'.$aDiligencias.'</diligence>
						</audience>';
		}
		else
		{
			$xml='		<audience id="'.$idEvento.'" status="1" isVirtual="1">
							<id_audience_type>'.$fRegistro["tipoAudiencia"].'</id_audience_type>
							<expedient>'.$carpetaAdministrativa.'</expedient>
							<id_management_unit>'.$fRegistro["idCentroGestion"].'</id_management_unit>
							<id_room></id_room>
							<recording_date>'.date("d/m/Y",strtotime($fRegistro["horaInicioEvento"])).'</recording_date>
							<recording_time>'.date("H:i:s",strtotime($fRegistro["horaInicioEvento"])).'</recording_time>
							<judges></judges>
							<participants></participants>
							<telepresence></telepresence>
							<diligence></diligence>
							<virtual_participants>'.$aParticipantesVirtuales.'</virtual_participants>
						</audience>';
		}
	}
	if(($fRegistro["situacion"]==3)||($fRegistro["situacion"]==6))
		$xml=str_replace('status="1"','status="0"',$xml);
	
	return $xml;
}

function notificarEventoAudienciaSIAJOPV2($idEvento,$direccionIP)
{
	global $con;

	$idRegistroBitacora=registrarBitacoraNotificacionSIAJOP($idEvento);
	
	$response="";
	$idWS=0;
	try
	{
		$consulta="SELECT * FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$idEvento;
		$fDatosEvento=$con->obtenerPrimeraFilaAsoc($consulta);

		//@reportarAudienciaPGJ($fDatosEvento["idFormulario"],$fDatosEvento["idRegistroSolicitud"]);
		$idWS=$fDatosEvento["idEdificio"]*10;
		
		$xml=reportarAudienciaSiajopV2($idEvento);
		//echo $xml;
		$urlWebServices="";
		$urlWebServices="http://".$direccionIP.":8080/ServiceSIAJOP.asmx";
		//echo $urlWebServices;

		$client = new nusoap_client($urlWebServices."?wsdl","wsdl");
		$parametros=array();
		
		
		
		
		
		$parametros["xml"]=$xml;
		/*if($_SESSION["idUsr"]==1)
			varDump($xml);*/
		$response = $client->call("SynchronizationAudience", $parametros);
//		varDUmp($response);
		if($response=="")
		{
			@actualizarBitacoraNotificacionSIAJOP($idRegistroBitacora,0,"No se pudo establecer conexion con el servidor",0,bE(""),$idWS);
			return;
		}
		$response=$response["SynchronizationAudienceResult"];
		
		$cXML=simplexml_load_string($response);	

		$resultado=(string)$cXML->resultado[0];
		@actualizarBitacoraNotificacionSIAJOP($idRegistroBitacora,$resultado,(string)$cXML->datosComplementarios[0],0,bE($response),$idWS);
		@notificarEventoAudienciaSIAJOPCabina($idEvento);
		
	}
	catch(Exception $e)
	{
		
		@actualizarBitacoraNotificacionSIAJOP($idRegistroBitacora,0,$e->getMessage(),1,bE($response),$idWS);
		@notificarEventoAudienciaSIAJOPCabina($idEvento);
		
		
		
	}		
		
}

function notificarCancelacionEventoAudienciaSIAJOPV2($idEvento,$direccionIP)
{
	global $con;
	$idRegistroBitacora=registrarBitacoraNotificacionSIAJOP($idEvento);
	$response="";
	$idWS=100;
	try
	{
		$consulta="SELECT * FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$idEvento;
		$fDatosEvento=$con->obtenerPrimeraFilaAsoc($consulta);
		$idWS=$fDatosEvento["idEdificio"]*10;
		
		$xml=reportarAudienciaSiajopV2($idEvento);
		
		$xml=str_replace('status="1"','status="0"',$xml);
		$urlWebServices="";
		
		$urlWebServices="http://".$direccionIP.":8080/ServiceSIAJOP.asmx";

		$client = new nusoap_client($urlWebServices."?wsdl","wsdl");
		$parametros=array();
		$parametros["xml"]=$xml;
		
		$response = $client->call("SynchronizationAudience", $parametros);
		

		$response=$response["SynchronizationAudienceResult"];
		
		$cXML=simplexml_load_string($response);	
		$resultado=(string)$cXML->resultado[0];
		actualizarBitacoraNotificacionSIAJOP($idRegistroBitacora,$resultado,(string)$cXML->datosComplementarios[0],0,bE($response),$idWS);
		@notificarCancelacionEventoAudienciaSIAJOPCabina($idEvento);
		
	}
	catch(Exception $e)
	{
		
		actualizarBitacoraNotificacionSIAJOP($idRegistroBitacora,0,$e->getMessage(),1,bE($response),$idWS);
		@notificarCancelacionEventoAudienciaSIAJOPCabina($idEvento);
		
		
		
	}		
		
}

//La Viga
function reportarAudienciaSiajopLaViga($idEvento,$formato=1)
{
	global $con;
	global $tipoMateria;
	$consulta="SELECT * FROM 7000_eventosAudiencia WHERE  idRegistroEvento=".$idEvento;
	$fRegistro=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$esVirtual=false;
	
	$consulta="SELECT carpetaAdministrativa,idCarpetaAdministrativa FROM 7007_contenidosCarpetaAdministrativa WHERE 
				tipoContenido=3 AND idRegistroContenidoReferencia=".$idEvento;
	$fDatosCarpeta=$con->obtenerPrimeraFila($consulta);
	
	$carpetaAdministrativa=$fDatosCarpeta[0];
	if($carpetaAdministrativa=="")
	{
		
		return true;
	}
	
	$consulta="SELECT * FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$carpetaAdministrativa."'";

	if($tipoMateria!="P")
	{
		$consulta.=" and idCarpeta=".$fDatosCarpeta[1];
	}
	//echo $consulta;
	$fInfoCarpeta=$con->obtenerPrimeraFilaAsoc($consulta);

	$idActividad=$fInfoCarpeta["idActividad"];
	
	$secretario="";
	$tipoJuicio="";
	
	$consulta="SELECT * FROM _478_tablaDinamica WHERE id__478_tablaDinamica=".$fInfoCarpeta["idRegistro"];
	$fInfoRegistroExp=$con->obtenerPrimeraFilaAsoc($consulta);
	
	if($fInfoRegistroExp)
	{
		$secretario=obtenerNombreUsuario($fInfoRegistroExp["secretario"]==""?-1:$fInfoRegistroExp["secretario"]);
		$consulta="SELECT tipoJuicio FROM _477_tablaDinamica WHERE id__477_tablaDinamica=".$fInfoRegistroExp["tipoJuicio"];
		$tipoJuicio=$con->obtenerValor($consulta);
	}
	$consulta="SELECT idJuez FROM 7001_eventoAudienciaJuez WHERE idRegistroEvento=".$idEvento;
	$res=$con->obtenerFilas($consulta);
	
	$arrJueces="";
	while($fJuez=mysql_fetch_row($res))
	{
		
		$o='<id_judge>'.$fJuez[0].'</id_judge>';
		$arrJueces.=$o;
	}

    $arrParticipantes="";    
	$consulta="SELECT group_concat(CONCAT(IF(nombre IS NULL,'',nombre),' ',IF(apellidoPaterno IS NULL,'',apellidoPaterno),' ',IF(apellidoMaterno IS NULL,'',apellidoMaterno))) AS participante,r.idFiguraJuridica FROM 7005_relacionFigurasJuridicasSolicitud r,_47_tablaDinamica p 
				WHERE r.idFiguraJuridica IN(2) AND p.id__47_tablaDinamica=r.idParticipante AND r.idActividad=".$idActividad;

	$rParticipantes=$con->obtenerFilas($consulta);		
	while($fParticipante=mysql_fetch_row($rParticipantes))
	{

		$o='<participant participantType="'.$fParticipante[1].'" protectedWitness="0">'.cv($fParticipante[0]).'</participant>';
		$arrParticipantes.=$o;
	}
	
	$consulta="SELECT group_concat(CONCAT(IF(nombre IS NULL,'',nombre),' ',IF(apellidoPaterno IS NULL,'',apellidoPaterno),' ',IF(apellidoMaterno IS NULL,'',apellidoMaterno))) AS participante,r.idFiguraJuridica FROM 7005_relacionFigurasJuridicasSolicitud r,_47_tablaDinamica p 
				WHERE r.idFiguraJuridica IN(4) AND p.id__47_tablaDinamica=r.idParticipante AND r.idActividad=".$idActividad;

	$rParticipantes=$con->obtenerFilas($consulta);		
	while($fParticipante=mysql_fetch_row($rParticipantes))
	{

		$o='<participant participantType="'.$fParticipante[1].'" protectedWitness="0">'.cv($fParticipante[0]).'</participant>';
		$arrParticipantes.=$o;
	}
	
	
	$arrTelepresencias=array();
	/*array_push($arrTelepresencias,2);
	array_push($arrTelepresencias,3);	*/
	
	$aTelepresencia="";
	foreach($arrTelepresencias as $t)
	{
		$oTelepresencia='<id_telepresence>'.$t.'</id_telepresence>';
		$aTelepresencia.=$oTelepresencia;
		
	}
	
	$arrDiligencias=array();
	/*array_push($arrDiligencias,"Diligencia XXX");*/
	
	$aDiligencias="";
	foreach($arrDiligencias as $d)
	{
		$oDiligencia='<address>'.$d.'</address>';
		$aDiligencias.=$oDiligencia;
		
	}
	
	$aParticipantesVirtuales="";
	$arrParticipantesVirtuales=array();
	if($esVirtual)
	{
		/*$participante=json_decode('{"tipo":"juez","telefono":"55461214","email":"juez9@gmail.com","nombre":"2402"}');
		array_push($arrParticipantesVirtuales,$participante);
		$participante=json_decode('{"tipo":"mp","telefono":"55171922","email":"mmmmm@gmail.com","nombre":"MMMM"}');
		array_push($arrParticipantesVirtuales,$participante);
		
		$participante=json_decode('{"tipo":"diso","telefono":"55115048","email":"diso2@gmail.com","nombre":"ZZZ"}');
		array_push($arrParticipantesVirtuales,$participante);*/
	}
	foreach($arrParticipantesVirtuales as $p)
	{
		$oParticipante='<participant type="'.$p->tipo.'"  phone="'.$p->telefono.'" email="'.$p->email.'">'.$p->nombre.'</participant>';
		$aParticipantesVirtuales.=$oParticipante;
		
	}
	
	
		
	$xml="";
    if($formato==1)
	{
		if(!$esVirtual)
		{
			$xml='<?xml version="1.0" encoding="utf-8" ?>
					<siajop>
						<audience id="'.$idEvento.'" status="1" isVirtual="0">
							<id_audience_type>'.$fRegistro["tipoAudiencia"].'</id_audience_type>
							<expedient>'.$carpetaAdministrativa.'</expedient>
							<id_management_unit>'.$fRegistro["idCentroGestion"].'</id_management_unit>
							<id_room>'.$fRegistro["idSala"].'</id_room>
							<recording_date>'.date("d/m/Y",strtotime($fRegistro["horaInicioEvento"])).'</recording_date>
							<recording_time>'.date("H:i:s",strtotime($fRegistro["horaInicioEvento"])).'</recording_time>
							<secretario>'.$secretario.'</secretario>
							<tipoJuicio>'.$tipoJuicio.'</tipoJuicio>
							<judges> 
								   '.$arrJueces.'
							</judges>
							<participants>						
								'.$arrParticipantes.'
							</participants>
							<telepresence>'.$aTelepresencia.'</telepresence>
							<diligence>'.$aDiligencias.'</diligence>
						</audience>
					</siajop>';
		}
		else
		{
			$xml='<?xml version="1.0" encoding="utf-8" ?>
					<siajop>
						<audience id="'.$idEvento.'" status="1" isVirtual="1">
							<id_audience_type>'.$fRegistro["tipoAudiencia"].'</id_audience_type>
							<expedient>'.$carpetaAdministrativa.'</expedient>
							<id_management_unit>'.$fRegistro["idCentroGestion"].'</id_management_unit>
							<id_room></id_room>
							<recording_date>'.date("d/m/Y",strtotime($fRegistro["horaInicioEvento"])).'</recording_date>
							<recording_time>'.date("H:i:s",strtotime($fRegistro["horaInicioEvento"])).'</recording_time>
							<judges></judges>
							<secretario></secretario>
							<tipoJuicio></tipoJuicio>
							<participants></participants>
							<telepresence></telepresence>
							<diligence></diligence>
							<virtual_participants>'.$aParticipantesVirtuales.'</virtual_participants>
						</audience>
					</siajop>';
		}
	}
	else
	{
		if(!$esVirtual)
		{
			$xml='		<audience id="'.$idEvento.'" status="1" isVirtual="0">
								<id_audience_type>'.$fRegistro["tipoAudiencia"].'</id_audience_type>
								<expedient>'.$carpetaAdministrativa.'</expedient>
								<id_management_unit>'.$fRegistro["idCentroGestion"].'</id_management_unit>
								<id_room>'.$fRegistro["idSala"].'</id_room>
								<recording_date>'.date("d/m/Y",strtotime($fRegistro["horaInicioEvento"])).'</recording_date>
								<recording_time>'.date("H:i:s",strtotime($fRegistro["horaInicioEvento"])).'</recording_time>
								<secretario>'.$secretario.'</secretario>
								<tipoJuicio>'.$tipoJuicio.'</tipoJuicio>
								<judges> 
									   '.$arrJueces.'
								</judges>
								<participants>						
									'.$arrParticipantes.'
								</participants>
								<telepresence>'.$aTelepresencia.'</telepresence>
								<diligence>'.$aDiligencias.'</diligence>
						</audience>';
		}
		else
		{
			$xml='		<audience id="'.$idEvento.'" status="1" isVirtual="1">
							<id_audience_type>'.$fRegistro["tipoAudiencia"].'</id_audience_type>
							<expedient>'.$carpetaAdministrativa.'</expedient>
							<id_management_unit>'.$fRegistro["idCentroGestion"].'</id_management_unit>
							<id_room></id_room>
							<recording_date>'.date("d/m/Y",strtotime($fRegistro["horaInicioEvento"])).'</recording_date>
							<recording_time>'.date("H:i:s",strtotime($fRegistro["horaInicioEvento"])).'</recording_time>
							<judges></judges>
							<secretario></secretario>
							<tipoJuicio></tipoJuicio>
							<participants></participants>
							<telepresence></telepresence>
							<diligence></diligence>
							<virtual_participants>'.$aParticipantesVirtuales.'</virtual_participants>
						</audience>';
		}
	}
	if(($fRegistro["situacion"]==3)||($fRegistro["situacion"]==6))
		$xml=str_replace('status="1"','status="0"',$xml);
	
	return $xml;
}

function notificarEventoAudienciaSIAJOPLaViga($idEvento,$direccionIP)
{
	global $con;

	$idRegistroBitacora=registrarBitacoraNotificacionSIAJOP($idEvento);
	
	$response="";
	$idWS=0;
	try
	{
		$consulta="SELECT * FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$idEvento;
		$fDatosEvento=$con->obtenerPrimeraFilaAsoc($consulta);

		$idWS=$fDatosEvento["idEdificio"]*10;
		
		$xml=reportarAudienciaSiajopLaViga($idEvento);

		
		$urlWebServices="http://".$direccionIP.":2050/xml_sgj_web.asp";

		$client = new nusoap_client($urlWebServices."?wsdl","wsdl");
		$parametros=array();
		
		$curl = curl_init($urlWebServices);
		$curl_post_data = $xml;

		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(                                                                          
														//'Content-Type: application/soap+xml; charset=utf-8',                                                                                
														'Content-Length: ' . strlen($curl_post_data))                                                                       
													);                                                                                                                   
									
		$curl_response = curl_exec($curl);

		if($curl_response=="")
		{
			@actualizarBitacoraNotificacionSIAJOP($idRegistroBitacora,0,"No se pudo establecer conexion con el servidor",0,bE(""),$idWS);
			return;
		}
		
		
		$cXML=simplexml_load_string($curl_response);	
		$resultado=(string)$cXML->resultado[0];
		@actualizarBitacoraNotificacionSIAJOP($idRegistroBitacora,$resultado,(string)$cXML->datosComplementarios[0],0,bE($response),$idWS);
		
		
	}
	catch(Exception $e)
	{
		
		@actualizarBitacoraNotificacionSIAJOP($idRegistroBitacora,0,$e->getMessage(),1,bE($response),$idWS);
		
		
		
	}		
		
}

function notificarCancelacionEventoAudienciaSIAJOPLaViga($idEvento,$direccionIP)
{
	global $con;
	$idRegistroBitacora=registrarBitacoraNotificacionSIAJOP($idEvento);
	$response="";
	$idWS=100;
	try
	{
		$consulta="SELECT * FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$idEvento;
		$fDatosEvento=$con->obtenerPrimeraFilaAsoc($consulta);
		$idWS=$fDatosEvento["idEdificio"]*10;
		
		$xml=reportarAudienciaSiajopLaViga($idEvento);
		$xml=str_replace('status="1"','status="0"',$xml);
		$urlWebServices="http://".$direccionIP.":2050/xml_sgj_web.asp";
		
		$client = new nusoap_client($urlWebServices."?wsdl","wsdl");
		$parametros=array();
		
		$curl = curl_init($urlWebServices);
		$curl_post_data = $xml;

		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(                                                                          
														//'Content-Type: application/soap+xml; charset=utf-8',                                                                                
														'Content-Length: ' . strlen($curl_post_data))                                                                       
													);                                                                                                                   
									
		$curl_response = curl_exec($curl);
		
		if($curl_response=="")
		{
			@actualizarBitacoraNotificacionSIAJOP($idRegistroBitacora,0,"No se pudo establecer conexion con el servidor",0,bE(""),$idWS);
			return;
		}
		
		
		$cXML=simplexml_load_string($curl_response);	
		$resultado=(string)$cXML->resultado[0];
		@actualizarBitacoraNotificacionSIAJOP($idRegistroBitacora,$resultado,(string)$cXML->datosComplementarios[0],0,bE($response),$idWS);
		
		
	}
	catch(Exception $e)
	{
		
		actualizarBitacoraNotificacionSIAJOP($idRegistroBitacora,0,$e->getMessage(),1,bE($response),$idWS);
		
		
		
	}		
		
}


//Cabinas
function notificarEventoAudienciaSIAJOPCabina($idEvento,$direccionIP="")
{
	global $con;
	global $arrSIAJOPCabina;
	global $servidorPruebas;
	
	/*if($servidorPruebas)
	{
		return true;
	}*/
	
	$consulta="SELECT * FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$idEvento;
	$fDatosEvento=$con->obtenerPrimeraFilaAsoc($consulta);

	if(($direccionIP=="") &&(isset($arrSIAJOPCabina[$fDatosEvento["idCentroGestion"]])))
	{
		$direccionIP=$arrSIAJOPCabina[$fDatosEvento["idCentroGestion"]];
	}
		
		
	$consulta="SELECT COUNT(*) FROM 7001_recursosAdicionalesAudiencia WHERE idRegistroEvento=".$idEvento.
				" and tipoRecurso=1  AND situacionRecurso IN(SELECT idSituacion FROM 7011_situacionEventosAudiencia WHERE considerarDiponibilidad=1)";	
	$numAdicionales=$con->obtenerValor($consulta);
	
	if(($numAdicionales==0)||($direccionIP==""))
		return true;	
	$idRegistroBitacora=registrarBitacoraNotificacionSIAJOP($idEvento);
	
	$response="";
	$idWS=1000;
	try
	{
		

		$consulta="UPDATE 3009_bitacoraVideoGrabacion SET servicioWeb=".$idWS." WHERE idRegistro=".$idRegistroBitacora;
		$con->ejecutarConsulta($consulta);
		
		
		
		
		$xml=reportarAudienciaSiajopCabina($idEvento);
		
		$urlWebServices="";
		$urlWebServices="http://".$direccionIP.":8080/ServiceSIAJOP.asmx";


		$client = new nusoap_client($urlWebServices."?wsdl","wsdl");
		$parametros=array();
		
		$parametros["xml"]=$xml;
		
		$response = $client->call("SynchronizationAudience", $parametros);
		if($response=="")
		{
			@actualizarBitacoraNotificacionSIAJOP($idRegistroBitacora,0,"No se pudo establecer conexion con el servidor",0,bE(""),$idWS);
			return;
		}
		$response=$response["SynchronizationAudienceResult"];
		
		$cXML=simplexml_load_string($response);	

		$resultado=(string)$cXML->resultado[0];
		@actualizarBitacoraNotificacionSIAJOP($idRegistroBitacora,$resultado,(string)$cXML->datosComplementarios[0],0,bE($response),$idWS);
		
		
	}
	catch(Exception $e)
	{
		
		@actualizarBitacoraNotificacionSIAJOP($idRegistroBitacora,0,$e->getMessage(),1,bE($response),$idWS);
		
		
		
	}		
		
}

function reportarAudienciaSiajopCabina($idEvento,$formato=1)
{
	global $con;
	global $tipoMateria;
	$consulta="SELECT * FROM 7000_eventosAudiencia WHERE  idRegistroEvento=".$idEvento;
	$fRegistro=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$esVirtual=false;
	
	$consulta="SELECT carpetaAdministrativa,idCarpetaAdministrativa FROM 7007_contenidosCarpetaAdministrativa WHERE 
				tipoContenido=3 AND idRegistroContenidoReferencia=".$idEvento;
	$fDatosCarpeta=$con->obtenerPrimeraFila($consulta);
	
	$carpetaAdministrativa=$fDatosCarpeta[0];
	if($carpetaAdministrativa=="")
	{
		
		return true;
	}
	
	$consulta="SELECT idActividad FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$carpetaAdministrativa."'";

	if($tipoMateria!="P")
	{
		$consulta.=" and idCarpeta=".$fDatosCarpeta[1];
	}
	//echo $consulta;
	$idActividad=$con->obtenerValor($consulta);
	
	
	$consulta="SELECT idJuez FROM 7001_eventoAudienciaJuez WHERE idRegistroEvento=".$idEvento;
	$res=$con->obtenerFilas($consulta);
	
	$arrJueces="";
	while($fJuez=mysql_fetch_row($res))
	{
		
		$o='<id_judge>'.$fJuez[0].'</id_judge>';
		$arrJueces.=$o;
	}

    $arrParticipantes="";    
	$consulta="SELECT (CONCAT(IF(apellidoPaterno IS NULL,'',apellidoPaterno),' ',IF(apellidoMaterno IS NULL,'',apellidoMaterno),
' ',IF(nombre IS NULL,'',nombre))) AS participante,r.idFiguraJuridica FROM 7005_relacionFigurasJuridicasSolicitud r,_47_tablaDinamica p 
				WHERE r.idFiguraJuridica IN(2,4,1) AND p.id__47_tablaDinamica=r.idParticipante AND r.idActividad=".$idActividad;
		//echo $consulta;
	$rParticipantes=$con->obtenerFilas($consulta);		
	while($fParticipante=mysql_fetch_row($rParticipantes))
	{

		$o='<participant participantType="'.$fParticipante[1].'" protectedWitness="0">'.cv($fParticipante[0]).'</participant>';
		$arrParticipantes.=$o;
	}
	
	
	$consulta="SELECT * FROM 7001_recursosAdicionalesAudiencia WHERE idRegistroEvento=".$idEvento." AND situacionRecurso=1 AND tipoRecurso=1";
	$rParticipantes=$con->obtenerFilas($consulta);		
	while($fParticipante=mysql_fetch_assoc($rParticipantes))
	{
		$consulta="SELECT nombreRecurso FROM _628_tablaDinamica WHERE id__628_tablaDinamica=".$fParticipante["idRecurso"];
		$nombreRecurso=$con->obtenerValor($consulta);
		
		$o='<participant participantType="20" protectedWitness="0">'.cv($nombreRecurso).'</participant>';
		$arrParticipantes.=$o;
	}
	$arrTelepresencias=array();
	/*array_push($arrTelepresencias,2);
	array_push($arrTelepresencias,3);	*/
	
	$aTelepresencia="";
	foreach($arrTelepresencias as $t)
	{
		$oTelepresencia='<id_telepresence>'.$t.'</id_telepresence>';
		$aTelepresencia.=$oTelepresencia;
		
	}
	
	$arrDiligencias=array();
	/*array_push($arrDiligencias,"Diligencia XXX");*/
	
	$aDiligencias="";
	foreach($arrDiligencias as $d)
	{
		$oDiligencia='<address>'.$d.'</address>';
		$aDiligencias.=$oDiligencia;
		
	}
	
	$aParticipantesVirtuales="";
	$arrParticipantesVirtuales=array();
	if($esVirtual)
	{
		/*$participante=json_decode('{"tipo":"juez","telefono":"55461214","email":"juez9@gmail.com","nombre":"2402"}');
		array_push($arrParticipantesVirtuales,$participante);
		$participante=json_decode('{"tipo":"mp","telefono":"55171922","email":"mmmmm@gmail.com","nombre":"MMMM"}');
		array_push($arrParticipantesVirtuales,$participante);
		
		$participante=json_decode('{"tipo":"diso","telefono":"55115048","email":"diso2@gmail.com","nombre":"ZZZ"}');
		array_push($arrParticipantesVirtuales,$participante);*/
	}
	foreach($arrParticipantesVirtuales as $p)
	{
		$oParticipante='<participant type="'.$p->tipo.'"  phone="'.$p->telefono.'" email="'.$p->email.'">'.$p->nombre.'</participant>';
		$aParticipantesVirtuales.=$oParticipante;
		
	}
	
	
		
	$xml="";
    if($formato==1)
	{
		if(!$esVirtual)
		{
			$xml='<?xml version="1.0" encoding="utf-8" ?>
					<siajop>
						<audience id="'.$idEvento.'" status="1" isVirtual="0">
							<id_audience_type>'.$fRegistro["tipoAudiencia"].'</id_audience_type>
							<expedient>'.$carpetaAdministrativa.'</expedient>
							<id_management_unit>'.$fRegistro["idCentroGestion"].'</id_management_unit>
							<id_room>'.$fRegistro["idSala"].'</id_room>
							<recording_date>'.date("d/m/Y",strtotime($fRegistro["horaInicioEvento"])).'</recording_date>
							<recording_time>'.date("H:i:s",strtotime($fRegistro["horaInicioEvento"])).'</recording_time>
							<judges> 
								   '.$arrJueces.'
							</judges>
							<participants>						
								'.$arrParticipantes.'
							</participants>
							<telepresence>'.$aTelepresencia.'</telepresence>
							<diligence>'.$aDiligencias.'</diligence>
						</audience>
					</siajop>';
		}
		else
		{
			$xml='<?xml version="1.0" encoding="utf-8" ?>
					<siajop>
						<audience id="'.$idEvento.'" status="1" isVirtual="1">
							<id_audience_type>'.$fRegistro["tipoAudiencia"].'</id_audience_type>
							<expedient>'.$carpetaAdministrativa.'</expedient>
							<id_management_unit>'.$fRegistro["idCentroGestion"].'</id_management_unit>
							<id_room></id_room>
							<recording_date>'.date("d/m/Y",strtotime($fRegistro["horaInicioEvento"])).'</recording_date>
							<recording_time>'.date("H:i:s",strtotime($fRegistro["horaInicioEvento"])).'</recording_time>
							<judges></judges>
							<participants></participants>
							<telepresence></telepresence>
							<diligence></diligence>
							<virtual_participants>'.$aParticipantesVirtuales.'</virtual_participants>
						</audience>
					</siajop>';
		}
	}
	else
	{
		if(!$esVirtual)
		{
			$xml='		<audience id="'.$idEvento.'" status="1" isVirtual="0">
								<id_audience_type>'.$fRegistro["tipoAudiencia"].'</id_audience_type>
								<expedient>'.$carpetaAdministrativa.'</expedient>
								<id_management_unit>'.$fRegistro["idCentroGestion"].'</id_management_unit>
								<id_room>'.$fRegistro["idSala"].'</id_room>
								<recording_date>'.date("d/m/Y",strtotime($fRegistro["horaInicioEvento"])).'</recording_date>
								<recording_time>'.date("H:i:s",strtotime($fRegistro["horaInicioEvento"])).'</recording_time>
								<judges> 
									   '.$arrJueces.'
								</judges>
								<participants>						
									'.$arrParticipantes.'
								</participants>
								<telepresence>'.$aTelepresencia.'</telepresence>
								<diligence>'.$aDiligencias.'</diligence>
						</audience>';
		}
		else
		{
			$xml='		<audience id="'.$idEvento.'" status="1" isVirtual="1">
							<id_audience_type>'.$fRegistro["tipoAudiencia"].'</id_audience_type>
							<expedient>'.$carpetaAdministrativa.'</expedient>
							<id_management_unit>'.$fRegistro["idCentroGestion"].'</id_management_unit>
							<id_room></id_room>
							<recording_date>'.date("d/m/Y",strtotime($fRegistro["horaInicioEvento"])).'</recording_date>
							<recording_time>'.date("H:i:s",strtotime($fRegistro["horaInicioEvento"])).'</recording_time>
							<judges></judges>
							<participants></participants>
							<telepresence></telepresence>
							<diligence></diligence>
							<virtual_participants>'.$aParticipantesVirtuales.'</virtual_participants>
						</audience>';
		}
	}
	if(($fRegistro["situacion"]==3)||($fRegistro["situacion"]==6))
		$xml=str_replace('status="1"','status="0"',$xml);
	
	return $xml;
}


function notificarCancelacionEventoAudienciaSIAJOPCabina($idEvento,$direccionIP="")
{
	global $con;
	global $arrSIAJOPCabina;
	
	global $servidorPruebas;
	
	/*if($servidorPruebas)
	{
		return true;
	}*/
	
	$consulta="SELECT * FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$idEvento;
	$fDatosEvento=$con->obtenerPrimeraFilaAsoc($consulta);
	
	if(($direccionIP=="") &&(isset($arrSIAJOPCabina[$fDatosEvento["idCentroGestion"]])))
	{
		$direccionIP=$arrSIAJOPCabina[$fDatosEvento["idCentroGestion"]];
	}
	
	$consulta="SELECT COUNT(*) FROM 7001_recursosAdicionalesAudiencia WHERE idRegistroEvento=".$idEvento.
				" and tipoRecurso=1  AND situacionRecurso IN(SELECT idSituacion FROM 7011_situacionEventosAudiencia WHERE considerarDiponibilidad=1)";	
	$numAdicionales=$con->obtenerValor($consulta);
	
	if(($numAdicionales==0)||($direccionIP==""))
		return true;
	
	
	
	$idRegistroBitacora=registrarBitacoraNotificacionSIAJOP($idEvento);
	$response="";
	$idWS=1000;
	try
	{
		
		
		
		$xml=reportarAudienciaSiajopCabina($idEvento);
		
		$xml=str_replace('status="1"','status="0"',$xml);
		$urlWebServices="";
		
		$urlWebServices="http://".$direccionIP.":8080/ServiceSIAJOP.asmx";

		$client = new nusoap_client($urlWebServices."?wsdl","wsdl");
		$parametros=array();
		$parametros["xml"]=$xml;
		
		$response = $client->call("SynchronizationAudience", $parametros);
		

		$response=$response["SynchronizationAudienceResult"];
		
		$cXML=simplexml_load_string($response);	
		$resultado=(string)$cXML->resultado[0];
		actualizarBitacoraNotificacionSIAJOP($idRegistroBitacora,$resultado,(string)$cXML->datosComplementarios[0],0,bE($response),$idWS);
		
		
	}
	catch(Exception $e)
	{
		
		actualizarBitacoraNotificacionSIAJOP($idRegistroBitacora,0,$e->getMessage(),1,bE($response),$idWS);
		
		
		
	}		
		
}



?>