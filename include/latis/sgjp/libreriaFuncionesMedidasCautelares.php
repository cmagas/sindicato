<?php 
include_once("latis/conexionBD.php");
include_once("latis/utiles.php");

function enviarNotificacionSobreseimiento($idRegistrBitacora,$tipoMateria)//$tipoMateria 2=Adolescentes, 1 Adultos; 
{
	global $con;
	global $urlWSSobreseimientoUsmeca;
	global $nombreFuncionSobreseimientoUsmeca;
	
	$consulta="SELECT * FROM 3000_bitacoraFormatos WHERE idRegistro=".$idRegistrBitacora;
	$fBitacora=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$consulta="SELECT idPerfilEvaluacion,idRegistro,tipoFormato,idDocumento,fechaFirma,responsableFirma FROM 3000_formatosRegistrados WHERE idRegistroFormato=".$fBitacora["idRegistroFormato"];
	$fDocumento=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$consulta="SELECT Nom,Paterno,Materno,Nombre FROM 802_identifica WHERE idUsuario=".$fDocumento["responsableFirma"];
	$filaFirmante=$con->obtenerPrimeraFilaAsoc($consulta);
	
	
	$consulta="SELECT * FROM 7035_informacionDocumentos WHERE idRegistro=".$fDocumento["idRegistro"];
	$fDocumentoComp=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$consulta="SELECT carpetaInvestigacion,u.id__17_tablaDinamica as idUGA,u.tipoMateria,u.claveUnidad,u.nombreUnidad,idActividad FROM 7006_carpetasAdministrativas c,_17_tablaDinamica u WHERE
				c.carpetaAdministrativa='".$fDocumentoComp["carpetaAdministrativa"]."' AND u.claveUnidad=c.unidadGestion";

	$fCarpeta=$con->obtenerPrimeraFilaAsoc($consulta);
	
	
	$idArea=$tipoMateria==2?3:4;
	
	$datosParametros=json_decode(bD($fDocumentoComp["datosParametros"]));
	$carpetaJudicialAnterior="";
	$arrCarpetasAntecesoras=obtenerCarpetasAntecesoras($fDocumentoComp["carpetaAdministrativa"]);

	$listaCarpetasAntecesoras="";
	foreach($arrCarpetasAntecesoras as $c)
	{
		if($fDocumentoComp["carpetaAdministrativa"]!=$c)
		{
			$carpetaJudicialAnterior=$c;
			break;
		}
	}
	
		
	$arrDelitos="";
	$consulta="SELECT d.denominacionDelito FROM _61_tablaDinamica d WHERE d.idActividad=".$fCarpeta["idActividad"];
	$rDelitos=$con->obtenerFilas($consulta);
	while($fDelito =mysql_fetch_row($rDelitos))
	{
			$arrDelitos.='<delitos>
						  <idDelito>'.$fDelito[0].'</idDelito>
					   </delitos>';
	}
	
	
	$victimas="";
	$consulta="SELECT upper(CONCAT(IF(nombre IS NULL,'',nombre),' ',IF(apellidoPaterno IS NULL,'',apellidoPaterno),' ',IF(apellidoMaterno IS NULL,'',apellidoMaterno))) 
				FROM _47_tablaDinamica p,7005_relacionFigurasJuridicasSolicitud r WHERE r.idParticipante=p.id__47_tablaDinamica
				AND r.idActividad=".$fCarpeta["idActividad"]." AND r.idFiguraJuridica=2 ORDER BY nombre,nombre,apellidoMaterno";
	
	$res=$con->obtenerFilas($consulta);
	while($filaImputado=mysql_fetch_row($res))
	{
		$nombre=$filaImputado[0];
		if($datosParametros->mostrarVictimasComo!=1)
			$nombre=inicialesNombre($filaImputado[0]);
		
		if($victimas=="")
			$victimas=$nombre;
		else
			$victimas.=", ".$nombre;
	}
	
	
	$consulta="SELECT nombreUnidad,claveFolioCarpetas,tipoMateria,id__17_tablaDinamica,idReferencia FROM _17_tablaDinamica WHERE  claveUnidad='".$fCarpeta["claveUnidad"]."'";
	$filaUGA=$con->obtenerPrimeraFila($consulta);
	
	$consulta="SELECT nombreInmueble FROM _1_tablaDinamica WHERE id__1_tablaDinamica=".$filaUGA[4];
	$nombreSede=$con->obtenerValor($consulta);
	
	$noUGA=$filaUGA[1]*1;
	if($filaUGA[2]==2)
		$noUGA="JA";
		
		
	$numOficio="UGJ".$noUGA."/".str_pad($datosParametros->noOficioAsignar,4,"0",STR_PAD_LEFT)."/".date("Y");	
	$arrImputados="";
	$consulta="SELECT upper(CONCAT(IF(nombre IS NULL,'',nombre),' ',IF(apellidoPaterno IS NULL,'',apellidoPaterno),' ',IF(apellidoMaterno IS NULL,'',apellidoMaterno))),
				id__47_tablaDinamica,genero,edad,fechaNacimiento,nombre,apellidoPaterno,apellidoMaterno FROM _47_tablaDinamica p where 
				id__47_tablaDinamica in(".$datosParametros->imputados.") ORDER BY nombre,nombre,apellidoMaterno";
	$rImputado=$con->obtenerFilas($consulta);
	while($fImputado=mysql_fetch_row($rImputado))
	{
		
		
		$fechaTraslado="";
		$fechaVinculacion="";
		$horaTraslado="";
		$vinculaciónProceso=1;
		
		$oImputado='<imputados>
					   '.$arrDelitos.'
					   <edad>'.$fImputado[3].'</edad>
					   <fechaNacimiento>'.($fImputado[4]==""?"":date("d-m-Y",strtotime($fImputado[4]))).'</fechaNacimiento>
					   <fechaTraslado>'.cv($fechaTraslado).'</fechaTraslado>
					   <fechaVinculacion>'.cv($fechaVinculacion).'</fechaVinculacion>
					   <horaTraslado>'.cv($horaTraslado).'</horaTraslado>
					   <nombre><![CDATA['.cv($fImputado[5]).']]></nombre>
					   <nombreVictima><![CDATA['.cv($victimas).']]></nombreVictima>
					   <primerApellido><![CDATA['.cv($fImputado[6]).']]></primerApellido>
					   <segundoApellido><![CDATA['.cv($fImputado[7]).']]></segundoApellido>
					   <sexo><![CDATA['.$fImputado[2].']]></sexo>
					   <vinculacionProceso>'.cv($vinculaciónProceso).'</vinculacionProceso>
					</imputados>';
		
		$arrImputados.=$oImputado;
			
	}
	
	
	
	
	$consulta="SELECT nomArchivoOriginal FROM 908_archivos WHERE idArchivo=".$fDocumento["idDocumento"];
	$nombreDocumento=$con->obtenerValor($consulta);
	$documento='<documento>
				   <data>'.obtenerCuerpoDocumentoB64($fDocumento["idDocumento"]).'</data>
				   <nombreDocumento><![CDATA['.cv($nombreDocumento).']]></nombreDocumento>
				</documento>';
	
	
	
	$consulta="SELECT * FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$datosParametros->audienciaSobreseimiento;
	$fAudiencia=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$consulta="SELECT upper(tA.tipoAudiencia) FROM 7000_eventosAudiencia e,_4_tablaDinamica tA WHERE e.idRegistroEvento=".$datosParametros->audienciaSobreseimiento.
				" AND e.tipoAudiencia=tA.id__4_tablaDinamica";
	$tipoAudiencia=$con->obtenerValor($consulta);
	
	$fechaAudiencia=date("d-m-Y",strtotime($fAudiencia["fechaEvento"]));

	$fechaFirma=date("d-m-Y",strtotime($fDocumento["fechaFirma"]));
	$consulta="SELECT UPPER(u.nombre) FROM 7001_eventoAudienciaJuez ej,800_usuarios u WHERE ej.idRegistroEvento=".
				$datosParametros->audienciaSobreseimiento." AND ej.idJuez=u.idUsuario";
	$juezControl=$con->obtenerValor($consulta);
	
	

	
	$fechaFirma=date("d-m-Y",strtotime($fDocumento["fechaFirma"]));
	
	
	$nombrePuesto="";
	
	
	$consulta="SELECT puestoOrganozacional FROM _421_tablaDinamica WHERE usuarioAsignado=".($fDocumento["responsableFirma"]==""?-1:$fDocumento["responsableFirma"]);
	$puestoOrganozacional=$con->obtenerValor($consulta);
	if($puestoOrganozacional=="")
		$puestoOrganozacional=-1;
	$consulta="SELECT nombrePuesto FROM _416_tablaDinamica WHERE id__416_tablaDinamica=".$puestoOrganozacional;
	$nombrePuesto=$con->obtenerValor($consulta);
	
	
	
	
	$solicitante='<solicitante>
					   <cargo><![CDATA['.cv($nombrePuesto).']]></cargo>
					   <nombre><![CDATA['.cv($filaFirmante["Nom"]).']]></nombre>
					   <nombreRemitente><![CDATA['.cv($filaFirmante["Nombre"]).']]></nombreRemitente>
					   <numeroUGJ><![CDATA['.$filaUGA[3].']]></numeroUGJ>
					   <primerApellido><![CDATA['.cv($filaFirmante["Paterno"]).']]></primerApellido>
					   <sedeUGJ><![CDATA['.cv($nombreSede).']]></sedeUGJ>
					   <segundoApellido><![CDATA['.cv($filaFirmante["Materno"]).']]></segundoApellido>
					   <subdirectorUGJ><![CDATA['.cv($filaFirmante["Nombre"]).']]></subdirectorUGJ>
				   </solicitante>';
	
	
	
	$cadJSON='<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:end="http://endpoint.solicitudespgjcdmx.apsi.com/">
   <soap:Header/>
   <soap:Body>
      <end:SolicitudSobreseimientoService>
         <arg0>
            <asunto><![CDATA[Se informa sobreseimiento]]></asunto>
            <carpetaInvestigacion><![CDATA['.$fCarpeta["carpetaInvestigacion"].']]></carpetaInvestigacion>
            <carpetaJudicial><![CDATA['.$fDocumentoComp["carpetaAdministrativa"].']]></carpetaJudicial>
            <carpetaJudicialAnterior><![CDATA['.$carpetaJudicialAnterior.']]></carpetaJudicialAnterior>
            <contenidoDeclaracion><![CDATA[Se informa sobreseimiento]]></contenidoDeclaracion>
            '.$documento.'
            <fechaAudiencia>'.$fechaAudiencia.'</fechaAudiencia>
            <fechaRedaccion>'.$fechaFirma.'</fechaRedaccion>
            <idArea>'.$idArea.'</idArea>
            <idTipoSolicitud>1</idTipoSolicitud>
            '.$arrImputados.'
            <juezControl><![CDATA['.$juezControl.']]></juezControl>
			<nombreUnidad><![CDATA['.$filaUGA[0].']]></nombreUnidad>
            <numOficioSolicitud><![CDATA[]]></numOficioSolicitud>
			<tipoAudiencia><![CDATA['.$tipoAudiencia.']]></tipoAudiencia>
            <numOficio><![CDATA['.$numOficio.']]></numOficio>'.$solicitante.'
         </arg0>
      </end:SolicitudSobreseimientoService>
   </soap:Body>
</soap:Envelope>';
echo $cadJSON;
$service_url = 'http://10.17.5.29:8080/solicitud-evaluacion/SolicitudService';
    $curl = curl_init($service_url);
    $curl_post_data = $cadJSON;
	
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array(                                                                          
													'Content-Type: application/soap+xml; charset=utf-8',                                                                                
													'Content-Length: ' . strlen($curl_post_data))                                                                       
												);                                                                                                                   
                                
	$curl_response = curl_exec($curl);
	varDUmp($curl_response);
	return;


}

function enviarNotificacionImposicionMedida($idRegistrBitacora,$tipoMateria,$tipoInforme)//$tipoMateria 2=Adolescentes, 1 Adultos; $tipoInforme 1=Medida Cautelar 2= SCP
{
	global $con;
	global $nombreFuncionInformeMedidaSCPUsmeca;
	global $urlWSInformeMedidaSCPUsmeca;
	
	$consulta="SELECT * FROM 3000_bitacoraFormatos WHERE idRegistro=".$idRegistrBitacora;
	$fBitacora=$con->obtenerPrimeraFilaAsoc($consulta);

	$consulta="SELECT idPerfilEvaluacion,idRegistro,tipoFormato,idDocumento,fechaFirma,responsableFirma FROM 3000_formatosRegistrados WHERE idRegistroFormato=".$fBitacora["idRegistroFormato"];
	$fDocumento=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$consulta="SELECT Nom,Paterno,Materno,Nombre FROM 802_identifica WHERE idUsuario=".$fDocumento["responsableFirma"];
	$filaFirmante=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$consulta="SELECT * FROM 7035_informacionDocumentos WHERE idRegistro=".$fDocumento["idRegistro"];

	$fDocumentoComp=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$consulta="SELECT carpetaInvestigacion,u.id__17_tablaDinamica as idUGA,u.tipoMateria,u.claveUnidad,u.nombreUnidad,idActividad FROM 7006_carpetasAdministrativas c,_17_tablaDinamica u WHERE
				c.carpetaAdministrativa='".$fDocumentoComp["carpetaAdministrativa"]."' AND u.claveUnidad=c.unidadGestion";

	$fCarpeta=$con->obtenerPrimeraFilaAsoc($consulta);
	

	$idArea=$tipoMateria==2?3:4;
	
	$datosParametros=json_decode(bD($fDocumentoComp["datosParametros"]));
	
	
	$carpetaJudicialAnterior="";
	$arrCarpetasAntecesoras=obtenerCarpetasAntecesoras($fDocumentoComp["carpetaAdministrativa"]);

	$listaCarpetasAntecesoras="";
	foreach($arrCarpetasAntecesoras as $c)
	{
		if($fDocumentoComp["carpetaAdministrativa"]!=$c)
		{
			$carpetaJudicialAnterior=$c;
			break;
		}
	}
	
	$arrDelitos="";
	$consulta="SELECT d.denominacionDelito FROM _61_tablaDinamica d WHERE d.idActividad=".$fCarpeta["idActividad"];
	$rDelitos=$con->obtenerFilas($consulta);
	while($fDelito =mysql_fetch_row($rDelitos))
	{
			$arrDelitos.='<delitos>
						  <idDelito>'.$fDelito[0].'</idDelito>
					   </delitos>';
	}
	
	
	$victimas="";
	$consulta="SELECT upper(CONCAT(IF(nombre IS NULL,'',nombre),' ',IF(apellidoPaterno IS NULL,'',apellidoPaterno),' ',IF(apellidoMaterno IS NULL,'',apellidoMaterno))) 
				FROM _47_tablaDinamica p,7005_relacionFigurasJuridicasSolicitud r WHERE r.idParticipante=p.id__47_tablaDinamica
				AND r.idActividad=".$fCarpeta["idActividad"]." AND r.idFiguraJuridica=2 ORDER BY nombre,nombre,apellidoMaterno";
	
	$res=$con->obtenerFilas($consulta);
	while($filaImputado=mysql_fetch_row($res))
	{
		$nombre=$filaImputado[0];
		
		if((isset($datosParametros->mostrarVictimasComo)) &&($datosParametros->mostrarVictimasComo!=1))
			$nombre=inicialesNombre($filaImputado[0]);
		
		if($victimas=="")
			$victimas=$nombre;
		else
			$victimas.=", ".$nombre;
	}
	
	
	$consulta="SELECT nombreUnidad,claveFolioCarpetas,tipoMateria,id__17_tablaDinamica,idReferencia FROM _17_tablaDinamica WHERE  claveUnidad='".$fCarpeta["claveUnidad"]."'";
	$filaUGA=$con->obtenerPrimeraFila($consulta);
	
	$consulta="SELECT nombreInmueble FROM _1_tablaDinamica WHERE id__1_tablaDinamica=".$filaUGA[4];
	$nombreSede=$con->obtenerValor($consulta);
	$noUGA=$filaUGA[1]*1;
	if($filaUGA[2]==2)
		$noUGA="JA";
		

	$numOficio="UGJ".$noUGA."/".str_pad($datosParametros->noOficioAsignar,4,"0",STR_PAD_LEFT)."/".date("Y");	
	
	
	$arrImputados="";
	
	//$imputado=$con->obtenerListaValores($consulta);
	
	
	
	
	$nombreSubdirector="";
	$nombreJuez="";
	$arrFracciones="";
	$fechaAudiencia="";
	$contenidoDeclaracion="";
	
	if($tipoMateria==1)
	{
		switch($tipoInforme)
		{
			case 1:
				$contenidoDeclaracion="previstas en las fracciones I, VII y VIII del numeral 155 del Código Nacional de Procedimientos Penales";
				$asunto="Se informa imposición de medida cautelar";
	
				$consulta=" SELECT 
				 IF((SELECT upper(nombre) FROM 800_usuarios WHERE idUsuario=usuarioAsignado) IS NULL,'(NO ASIGNADO)',
				 (SELECT upper(nombre) FROM 800_usuarios WHERE idUsuario=usuarioAsignado) ) AS nombre,
				 (SELECT upper(nombrePuesto) FROM _416_tablaDinamica WHERE id__416_tablaDinamica=puestoOrganozacional)  AS  puesto
				FROM _421_tablaDinamica WHERE id__421_tablaDinamica=".$datosParametros->firmante;
				$filaPuesto=$con->obtenerPrimeraFila($consulta);
				$nombreSubdirector=$filaPuesto[0];
				$consulta="SELECT UPPER(u.nombre) FROM 7001_eventoAudienciaJuez ej,800_usuarios u WHERE ej.idRegistroEvento=".
				$datosParametros->audienciaMedidas." AND ej.idJuez=u.idUsuario";
				$nombreJuez=$con->obtenerValor($consulta);
	
	
				$consulta="SELECT DATE_FORMAT(fechaEvento,'%d-%m-%Y') FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$datosParametros->audienciaMedidas;
				$fechaAudiencia=$con->obtenerValor($consulta);
				foreach($datosParametros->medidadCautelares as $m)
				{
					$fraccion="";
					$detalle="";
					if($m->idMedidaCautelar!=0)
					{
						$consulta="SELECT cveSistemaUSMECA FROM _587_tablaDinamica WHERE id__587_tablaDinamica =".$m->idMedidaCautelar;
						$fLeyenda=$con->obtenerPrimeraFila($consulta);
						$fraccion=$fLeyenda[0];
						$detalle=$m->detalles;
					}
					else
					{
						$fraccion=76;
						$detalle=$m->detalles;
						
					}
					
					
					
						
					$arrFracciones.='<fracciones>
									  <especificacion><![CDATA['.$detalle.']]></especificacion>
									  <idFraccion>'.$fraccion.'</idFraccion>
								   </fracciones>';
				}

				
			break;
			case 2:
				$contenidoDeclaracion="__";
				
				$asunto="Se concede suspensión condicional de proceso";
				foreach($datosParametros->suspencionesCondicional as $m)
				{
					$fraccion="";
					$detalle="";
					if($m->idCondicion!=0)
					{
						$consulta="SELECT cveSistemaUSMECA,leyenda FROM _587_tablaDinamica WHERE id__587_tablaDinamica =".$m->idCondicion;
						$fLeyenda=$con->obtenerPrimeraFila($consulta);
						if($m->idCondicion!=15)
						{
							$fraccion=$fLeyenda[0];
							$detalle=$m->detalles;
						}
						else
						{
							$fraccion=76;
							$detalle=$fLeyenda[1].". ".$m->detalles;
						}
					}
					else
					{
						
						$fraccion=76;
						$detalle=$m->detalles;
					}
					
					$arrFracciones.='<fracciones>
									  <especificacion><![CDATA['.$detalle.']]></especificacion>
									  <idFraccion>'.$fraccion.'</idFraccion>
								   </fracciones>';
				}
				
				$consulta="SELECT UPPER(u.nombre) FROM 7001_eventoAudienciaJuez ej,800_usuarios u WHERE ej.idRegistroEvento=".
				$datosParametros->audienciaConcede." AND ej.idJuez=u.idUsuario";
				$nombreJuez=$con->obtenerValor($consulta);
				
				$consulta="SELECT DATE_FORMAT(fechaEvento,'%d-%m-%Y') FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$datosParametros->audienciaConcede;
				$fechaAudiencia=$con->obtenerValor($consulta);

				$nombreSubdirector=obtenerNombreUsuario($datosParametros->usuarioDestinatario);
	
			break;
		}
	}
	else
	{
		switch($tipoInforme)
		{
			case 1:

				$contenidoDeclaracion="con fundamento en el numeral 119 de la Ley Nacional del Sistema Integral de Justicia Penal para Adolescentes";
				$asunto="Se informa imposición de medida cautelar";
				
				$fraccion="";
				$detalle="";
				foreach($datosParametros->medidadCautelares as $m)
				{
					
					if($m->idMedidaCautelar!=0)
					{
						$consulta="SELECT cveSistemaUSMECA FROM _587_tablaDinamica WHERE id__587_tablaDinamica =".$m->idMedidaCautelar;
						$fLeyenda=$con->obtenerPrimeraFila($consulta);
						
						$fraccion=$fLeyenda[0];
						$detalle=$m->detalles;
					}
					else
					{
						$fraccion=76;
						$detalle=$m->detalles;

					}
						
					$arrFracciones.='<fracciones>
									  <especificacion><![CDATA['.$detalle.']]></especificacion>
									  <idFraccion>'.$fraccion.'</idFraccion>
								   </fracciones>';	
						
				}
				
				
				
				$consulta="SELECT idPadre FROM _420_unidadGestion WHERE idOpcion=".$fCarpeta["idUGA"];
				$idPerfilOrganigrama=$con->obtenerValor($consulta);
				if($idPerfilOrganigrama=="")
					$idPerfilOrganigrama=-1;
				
				$nombreJuez=obtenerNombreUsuario($datosParametros->usuarioOrdeno);
				
				$consulta="SELECT usuarioAsignado FROM _421_tablaDinamica WHERE idReferencia=".$idPerfilOrganigrama." and puestoOrganozacional=15";
				$directorUnidad=$con->obtenerValor($consulta);
				$nombreSubdirector=obtenerNombreUsuario($directorUnidad);
				
				$consulta="SELECT DATE_FORMAT(fechaEvento,'%d-%m-%Y') FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$datosParametros->audienciaConcede;
				$fechaAudiencia=$con->obtenerValor($consulta);
				
			break;
			case 2:
				$contenidoDeclaracion="de conformidad con los artículos 177 y 178 del Código Nacional de Procedimientos Penales";
				
				$asunto="Se concede suspensión condicional de proceso";
				foreach($datosParametros->suspencionesCondicional as $m)
				{
					$fraccion="";
					$detalle="";
					if($m->idCondicion!=0)
					{
						$consulta="SELECT cveSistemaUSMECA,leyenda FROM _587_tablaDinamica WHERE id__587_tablaDinamica =".$m->idCondicion;
						$fLeyenda=$con->obtenerPrimeraFila($consulta);
						if($m->idCondicion!=15)
						{
							$fraccion=$fLeyenda[0];
							$detalle=$m->detalles;
						}
						else
						{
							$fraccion=76;
							$detalle=$fLeyenda[1].". ".$m->detalles;
						}
					}
					else
					{
						
						$fraccion=76;
						$detalle=$m->detalles;
					}
					
					$arrFracciones.='<fracciones>
									  <especificacion><![CDATA['.$detalle.']]></especificacion>
									  <idFraccion>'.$fraccion.'</idFraccion>
								   </fracciones>';
				}
				
				$consulta="SELECT UPPER(u.nombre) FROM 7001_eventoAudienciaJuez ej,800_usuarios u WHERE ej.idRegistroEvento=".
				$datosParametros->audienciaConcede." AND ej.idJuez=u.idUsuario";
				$nombreJuez=$con->obtenerValor($consulta);
				
				$consulta="SELECT DATE_FORMAT(fechaEvento,'%d-%m-%Y') FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$datosParametros->audienciaConcede;
				$fechaAudiencia=$con->obtenerValor($consulta);

				$nombreSubdirector=obtenerNombreUsuario($datosParametros->usuarioDestinatario);
	
			break;
		}
	}
	$fechaFirma=date("d-m-Y",strtotime($fDocumento["fechaFirma"]));
	$consulta="SELECT upper(CONCAT(IF(nombre IS NULL,'',nombre),' ',IF(apellidoPaterno IS NULL,'',apellidoPaterno),' ',IF(apellidoMaterno IS NULL,'',apellidoMaterno))),
				id__47_tablaDinamica,genero,edad,fechaNacimiento,nombre,apellidoPaterno,apellidoMaterno FROM _47_tablaDinamica p where 
				id__47_tablaDinamica in(".$datosParametros->imputados.") ORDER BY nombre,nombre,apellidoMaterno";
	$rImputado=$con->obtenerFilas($consulta);
	while($fImputado=mysql_fetch_row($rImputado))
	{
		
		
		$fechaTraslado="";
		$fechaVinculacion="";
		$horaTraslado="";
		$vinculaciónProceso=1;
		
		$oImputado='<imputados>
					   '.$arrDelitos.'
					   <edad>'.$fImputado[3].'</edad>
					   <fechaNacimiento>'.($fImputado[4]==""?"":date("d-m-Y",strtotime($fImputado[4]))).'</fechaNacimiento>
					   <fechaTraslado>'.cv($fechaTraslado).'</fechaTraslado>
					   <fechaVinculacion>'.cv($fechaVinculacion).'</fechaVinculacion>
					   '.$arrFracciones.'
					   <horaTraslado>'.cv($horaTraslado).'</horaTraslado>
					   <nombre><![CDATA['.cv($fImputado[5]).']]></nombre>
					   <nombreVictima><![CDATA['.cv($victimas).']]></nombreVictima>
					   <primerApellido><![CDATA['.cv($fImputado[6]).']]></primerApellido>
					   <segundoApellido><![CDATA['.cv($fImputado[7]).']]></segundoApellido>
					   <sexo><![CDATA['.$fImputado[2].']]></sexo>
					   <vinculacionProceso>'.cv($vinculaciónProceso).'></vinculacionProceso>
					</imputados>';
		
		$arrImputados.=$oImputado;
			
	}
	
	
	
	
	$consulta="SELECT nomArchivoOriginal FROM 908_archivos WHERE idArchivo=".$fDocumento["idDocumento"];
	$nombreDocumento=$con->obtenerValor($consulta);
	$documento='<documento>
				   <data>'.obtenerCuerpoDocumentoB64($fDocumento["idDocumento"]).'</data>
				   <nombreDocumento><![CDATA['.cv($nombreDocumento).']]></nombreDocumento>
				</documento>';
	
	$nombrePuesto="";
	
	
	$consulta="SELECT puestoOrganozacional FROM _421_tablaDinamica WHERE usuarioAsignado=".($fDocumento["responsableFirma"]==""?-1:$fDocumento["responsableFirma"]);
	$puestoOrganozacional=$con->obtenerValor($consulta);
	if($puestoOrganozacional=="")
		$puestoOrganozacional=-1;
	$consulta="SELECT nombrePuesto FROM _416_tablaDinamica WHERE id__416_tablaDinamica=".$puestoOrganozacional;
	$nombrePuesto=$con->obtenerValor($consulta);
	
	
	
	
	$solicitante='<solicitante>
					   <cargo><![CDATA['.cv($nombrePuesto).']]></cargo>
					   <nombre><![CDATA['.cv($filaFirmante["Nom"]).']]></nombre>
					   <nombreRemitente><![CDATA['.cv($filaFirmante["Nombre"]).']]></nombreRemitente>
					   <numeroUGJ><![CDATA['.$filaUGA[3].']]></numeroUGJ>
					   <primerApellido><![CDATA['.cv($filaFirmante["Paterno"]).']]></primerApellido>
					   <sedeUGJ><![CDATA['.cv($nombreSede).']]></sedeUGJ>
					   <segundoApellido><![CDATA['.cv($filaFirmante["Materno"]).']]></segundoApellido>
					   <subdirectorUGJ><![CDATA['.cv($filaFirmante["Nombre"]).']]></subdirectorUGJ>
				   </solicitante>';
	
	
	
	$cadJSON='<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:end="http://endpoint.solicitudespgjcdmx.apsi.com/">
   <soap:Header/>
   <soap:Body>
      <end:SolicitudImposicionService>
         <arg0>
            <asunto><![CDATA['.$asunto.']]></asunto>
            <carpetaInvestigacion><![CDATA['.$fCarpeta["carpetaInvestigacion"].']]></carpetaInvestigacion>
            <carpetaJudicial><![CDATA['.$fDocumentoComp["carpetaAdministrativa"].']]></carpetaJudicial>
            <carpetaJudicialAnterior><![CDATA['.$carpetaJudicialAnterior.']]></carpetaJudicialAnterior>
            <contenidoDeclaracion><![CDATA['.$contenidoDeclaracion.']]></contenidoDeclaracion>
            '.$documento.'
            <fechaAudiencia>'.$fechaAudiencia.'</fechaAudiencia>
            <fechaDocumento>'.$fechaFirma.'</fechaDocumento>
            <idArea>'.$idArea.'</idArea>
            <idTipoSolicitud>'.$tipoInforme.'</idTipoSolicitud>
            '.$arrImputados.'
            <nombreJuez><![CDATA['.$nombreJuez.']]></nombreJuez>
            <numOficio><![CDATA['.$numOficio.']]></numOficio>'.$solicitante.'
         </arg0>
      </end:SolicitudImposicionService>
   </soap:Body>
</soap:Envelope>';
	echo $cadJSON;
	return;
	$service_url = 'http://10.17.5.29:8080/solicitud-evaluacion/SolicitudService';
    $curl = curl_init($service_url);
    $curl_post_data = $cadJSON;
	
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array(                                                                          
													'Content-Type: application/soap+xml; charset=utf-8',                                                                                
													'Content-Length: ' . strlen($curl_post_data))                                                                       
												);                                                                                                                   
                                
	$curl_response = curl_exec($curl);
	varDUmp($curl_response);
	return;
	
	
	
}

function enviarNotificacionSolicitudInforme($idRegistrBitacora,$tipoMateria)//$tipoMateria 2=Adolescentes, 1 Adultos; 
{
	global $con;
	global $urlWSSobreseimientoUsmeca;
	global $nombreFuncionSobreseimientoUsmeca;
	
	$consulta="SELECT * FROM 3000_bitacoraFormatos WHERE idRegistro=".$idRegistrBitacora;
	$fBitacora=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$consulta="SELECT idPerfilEvaluacion,idRegistro,tipoFormato,idDocumento,fechaFirma,responsableFirma FROM 3000_formatosRegistrados WHERE idRegistroFormato=".$fBitacora["idRegistroFormato"];
	$fDocumento=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$consulta="SELECT Nom,Paterno,Materno,Nombre FROM 802_identifica WHERE idUsuario=".$fDocumento["responsableFirma"];
	$filaFirmante=$con->obtenerPrimeraFilaAsoc($consulta);
	
	
	$consulta="SELECT * FROM 7035_informacionDocumentos WHERE idRegistro=".$fDocumento["idRegistro"];
	$fDocumentoComp=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$consulta="SELECT carpetaInvestigacion,u.id__17_tablaDinamica as idUGA,u.tipoMateria,u.claveUnidad,u.nombreUnidad,idActividad FROM 7006_carpetasAdministrativas c,_17_tablaDinamica u WHERE
				c.carpetaAdministrativa='".$fDocumentoComp["carpetaAdministrativa"]."' AND u.claveUnidad=c.unidadGestion";

	$fCarpeta=$con->obtenerPrimeraFilaAsoc($consulta);
	
	
	$idArea=$tipoMateria==2?3:4;
	
	$datosParametros=json_decode(bD($fDocumentoComp["datosParametros"]));
	$carpetaJudicialAnterior="";
	$arrCarpetasAntecesoras=obtenerCarpetasAntecesoras($fDocumentoComp["carpetaAdministrativa"]);

	$listaCarpetasAntecesoras="";
	foreach($arrCarpetasAntecesoras as $c)
	{
		if($fDocumentoComp["carpetaAdministrativa"]!=$c)
		{
			$carpetaJudicialAnterior=$c;
			break;
		}
	}
	
		
	$arrDelitos="";
	$consulta="SELECT d.denominacionDelito FROM _61_tablaDinamica d WHERE d.idActividad=".$fCarpeta["idActividad"];
	$rDelitos=$con->obtenerFilas($consulta);
	while($fDelito =mysql_fetch_row($rDelitos))
	{
			$arrDelitos.='<delitos>
						  <idDelito>'.$fDelito[0].'</idDelito>
					   </delitos>';
	}
	
	
	$victimas="";
	$consulta="SELECT upper(CONCAT(IF(nombre IS NULL,'',nombre),' ',IF(apellidoPaterno IS NULL,'',apellidoPaterno),' ',IF(apellidoMaterno IS NULL,'',apellidoMaterno))) 
				FROM _47_tablaDinamica p,7005_relacionFigurasJuridicasSolicitud r WHERE r.idParticipante=p.id__47_tablaDinamica
				AND r.idActividad=".$fCarpeta["idActividad"]." AND r.idFiguraJuridica=2 ORDER BY nombre,nombre,apellidoMaterno";
	
	$res=$con->obtenerFilas($consulta);
	while($filaImputado=mysql_fetch_row($res))
	{
		$nombre=$filaImputado[0];
		if($datosParametros->mostrarVictimasComo!=1)
			$nombre=inicialesNombre($filaImputado[0]);
		
		if($victimas=="")
			$victimas=$nombre;
		else
			$victimas.=", ".$nombre;
	}
	
	
	$consulta="SELECT nombreUnidad,claveFolioCarpetas,tipoMateria,id__17_tablaDinamica,idReferencia FROM _17_tablaDinamica WHERE  claveUnidad='".$fCarpeta["claveUnidad"]."'";
	$filaUGA=$con->obtenerPrimeraFila($consulta);
	
	$consulta="SELECT nombreInmueble FROM _1_tablaDinamica WHERE id__1_tablaDinamica=".$filaUGA[4];
	$nombreSede=$con->obtenerValor($consulta);
	
	$noUGA=$filaUGA[1]*1;
	if($filaUGA[2]==2)
		$noUGA="JA";
		
		
	$numOficio="UGJ".$noUGA."/".str_pad($datosParametros->noOficioAsignar,4,"0",STR_PAD_LEFT)."/".date("Y");	
	$arrImputados="";
	$consulta="SELECT upper(CONCAT(IF(nombre IS NULL,'',nombre),' ',IF(apellidoPaterno IS NULL,'',apellidoPaterno),' ',IF(apellidoMaterno IS NULL,'',apellidoMaterno))),
				id__47_tablaDinamica,genero,edad,fechaNacimiento,nombre,apellidoPaterno,apellidoMaterno FROM _47_tablaDinamica p where 
				id__47_tablaDinamica in(".$datosParametros->imputados.") ORDER BY nombre,nombre,apellidoMaterno";
	$rImputado=$con->obtenerFilas($consulta);
	while($fImputado=mysql_fetch_row($rImputado))
	{
		
		
		$fechaTraslado="";
		$fechaVinculacion="";
		$horaTraslado="";
		$vinculaciónProceso=1;
		
		$oImputado='<imputados>
					   '.$arrDelitos.'
					   <edad>'.$fImputado[3].'</edad>
					   <fechaNacimiento>'.($fImputado[4]==""?"":date("d-m-Y",strtotime($fImputado[4]))).'</fechaNacimiento>
					   <fechaTraslado>'.cv($fechaTraslado).'</fechaTraslado>
					   <fechaVinculacion>'.cv($fechaVinculacion).'</fechaVinculacion>
					   <horaTraslado>'.cv($horaTraslado).'</horaTraslado>
					   <nombre><![CDATA['.cv($fImputado[5]).']]></nombre>
					   <nombreVictima><![CDATA['.cv($victimas).']]></nombreVictima>
					   <primerApellido><![CDATA['.cv($fImputado[6]).']]></primerApellido>
					   <segundoApellido><![CDATA['.cv($fImputado[7]).']]></segundoApellido>
					   <sexo><![CDATA['.$fImputado[2].']]></sexo>
					   <vinculacionProceso>'.cv($vinculaciónProceso).'</vinculacionProceso>
					</imputados>';
		
		$arrImputados.=$oImputado;
			
	}
	
	
	
	
	$consulta="SELECT nomArchivoOriginal FROM 908_archivos WHERE idArchivo=".$fDocumento["idDocumento"];
	$nombreDocumento=$con->obtenerValor($consulta);
	$documento='<documento>
				   <data>'.obtenerCuerpoDocumentoB64($fDocumento["idDocumento"]).'</data>
				   <nombreDocumento><![CDATA['.cv($nombreDocumento).']]></nombreDocumento>
				</documento>';
	
	
	
	$consulta="SELECT * FROM 7000_eventosAudiencia WHERE idRegistroEvento=263833";
	$fAudiencia=$con->obtenerPrimeraFilaAsoc($consulta);

	$consulta="SELECT upper(tA.tipoAudiencia) FROM 7000_eventosAudiencia e,_4_tablaDinamica tA WHERE e.idRegistroEvento=268315 AND e.tipoAudiencia=tA.id__4_tablaDinamica";
	$tipoAudiencia=$con->obtenerValor($consulta);
	
	$fechaAudiencia=date("d-m-Y",strtotime($fAudiencia["fechaEvento"]));
	$horaAudiencia=date("H:i:s",strtotime($fAudiencia["horaInicioEvento"]));
	$fechaFirma=date("d-m-Y",strtotime($fDocumento["fechaFirma"]));
	$consulta="SELECT UPPER(u.nombre) FROM 7001_eventoAudienciaJuez ej,800_usuarios u WHERE ej.idRegistroEvento=268315 AND ej.idJuez=u.idUsuario";
	$juezControl=$con->obtenerValor($consulta);
	
	

	
	$fechaFirma=date("d-m-Y",strtotime($fDocumento["fechaFirma"]));
	
	
	$nombrePuesto="";
	
	
	$consulta="SELECT puestoOrganozacional FROM _421_tablaDinamica WHERE usuarioAsignado=".($fDocumento["responsableFirma"]==""?-1:$fDocumento["responsableFirma"]);
	$puestoOrganozacional=$con->obtenerValor($consulta);
	if($puestoOrganozacional=="")
		$puestoOrganozacional=-1;
	$consulta="SELECT nombrePuesto FROM _416_tablaDinamica WHERE id__416_tablaDinamica=".$puestoOrganozacional;
	$nombrePuesto=$con->obtenerValor($consulta);
	
	
	
	
	$solicitante='<solicitante>
					   <cargo><![CDATA['.cv($nombrePuesto).']]></cargo>
					   <nombre><![CDATA['.cv($filaFirmante["Nom"]).']]></nombre>
					   <nombreRemitente><![CDATA['.cv($filaFirmante["Nombre"]).']]></nombreRemitente>
					   <numeroUGJ><![CDATA['.$filaUGA[3].']]></numeroUGJ>
					   <primerApellido><![CDATA['.cv($filaFirmante["Paterno"]).']]></primerApellido>
					   <sedeUGJ><![CDATA['.cv($nombreSede).']]></sedeUGJ>
					   <segundoApellido><![CDATA['.cv($filaFirmante["Materno"]).']]></segundoApellido>
					   <subdirectorUGJ><![CDATA['.cv($filaFirmante["Nombre"]).']]></subdirectorUGJ>
				   </solicitante>';
	
	
	
	$cadJSON='<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:end="http://endpoint.solicitudespgjcdmx.apsi.com/">
   <soap:Header/>
   <soap:Body>
      <end:SolicitudAntecedentesService>
         <arg0>
            <asunto><![CDATA[Se informa sobreseimiento]]></asunto>
            <carpetaInvestigacion><![CDATA['.$fCarpeta["carpetaInvestigacion"].']]></carpetaInvestigacion>
            <carpetaJudicial><![CDATA['.$fDocumentoComp["carpetaAdministrativa"].']]></carpetaJudicial>
            <carpetaJudicialAnterior><![CDATA['.$carpetaJudicialAnterior.']]></carpetaJudicialAnterior>
            '.$documento.'
            <fechaAudiencia>'.$fechaAudiencia.'</fechaAudiencia>
			<horaAudiencia>'.$horaAudiencia.'</horaAudiencia>
            <idArea>'.$idArea.'</idArea>
            <idTipoSolicitud>1</idTipoSolicitud>
            '.$arrImputados.'
            <numOficio><![CDATA['.$numOficio.']]></numOficio>'.$solicitante.'
         </arg0>
      </end:SolicitudAntecedentesService>
   </soap:Body>
</soap:Envelope>';
echo $cadJSON;
return;
$service_url = 'http://10.17.5.29:8080/solicitud-evaluacion/SolicitudService';
    $curl = curl_init($service_url);
    $curl_post_data = $cadJSON;
	
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array(                                                                          
													'Content-Type: application/soap+xml; charset=utf-8',                                                                                
													'Content-Length: ' . strlen($curl_post_data))                                                                       
												);                                                                                                                   
                                
	$curl_response = curl_exec($curl);
	varDUmp($curl_response);
	return;


}

?>