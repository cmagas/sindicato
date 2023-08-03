<?php

	function registrarUnidadOrganigrama($idFormulario,$idRegistro)
	{
		global $con;
		
		$x=0;
		$query[$x]="begin";
		$x++;
		
		$consulta="";
		switch($idFormulario)
		{
			case 1:
				$consulta="SELECT unidadPadre,nombreInmueble as nombreUnidad,claveUnidad as cveUnidad,cveInmueble as cveRegistro,tipoUnidad FROM _1_tablaDinamica WHERE id__1_tablaDinamica=".$idRegistro;
			break;
			case 17:
				$consulta="SELECT unidadPadre,nombreUnidad as nombreUnidad,claveUnidad as cveUnidad,claveRegistro as cveRegistro,tipoUnidad FROM _17_tablaDinamica WHERE id__17_tablaDinamica=".$idRegistro;
			break;	
		}
		
		$fila=$con->obtenerPrimeraFilaAsoc($consulta);
		if(($fila["cveUnidad"]=="")||($fila["cveUnidad"]=="N/E"))
		{
			$consulta="SELECT MAX(codigoIndividual) FROM 817_organigrama WHERE unidadPadre='".$fila["unidadPadre"]."'";
			$maxCodigo=$con->obtenerValor($consulta);
			if(($maxCodigo=="")||($maxCodigo=="0"))
				$maxCodigo=1;
			else
				$maxCodigo++;
			
			$codigoIndividual=str_pad($maxCodigo,4,"0",STR_PAD_LEFT);
			$codigo=$fila["unidadPadre"].$codigoIndividual;
			$fila["cveUnidad"]=$codigo;
			$query[$x]="INSERT INTO 817_organigrama(unidad,codigoFuncional,codigoUnidad,descripcion,institucion,codCentroCosto,unidadPadre,codigoIndividual,
						codigoDepto,claveDepartamental,codigoInstitucion,fechaCreacion,responsableCreacion,STATUS,instColaboradora)
						VALUES('".cv($fila["nombreUnidad"])."','".$codigo."','".$codigo."','',".$fila["tipoUnidad"].",'','".$fila["unidadPadre"].
						"','".$codigoIndividual."','','".cv($fila["cveRegistro"])."','".$_SESSION["codigoInstitucion"]."','".date("Y-m-d H:i:s").
						"',".$_SESSION["idUsr"].",1,1)";
			$x++;
			
			$query[$x]="select @idOrganigrama:=(select last_insert_id())";
			$x++;
			
			$query[$x]="UPDATE _".$idFormulario."_tablaDinamica SET claveUnidad='".$fila["cveUnidad"]."' WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
			$x++;
			
		}
		else
		{
			
			$query[$x]="UPDATE 817_organigrama SET unidad='".cv($fila["nombreUnidad"])."',claveDepartamental='".cv($fila["cveRegistro"]).
						"' WHERE codigoUnidad='".$fila["cveUnidad"]."'";
			$x++;
			$query[$x]="set @idOrganigrama:=(select idOrganigrama FROM 817_organigrama WHERE codigoUnidad='".$fila["cveUnidad"]."')";
			$x++;
			
		}
		
		
		$query[$x]="commit";
		$x++;
		
		
		if($con->ejecutarBloque($query))
		{
			
			$consulta="select @idOrganigrama";
			$idOrganigrama=$con->obtenerValor($consulta);
			echo "window.parent.parent.invocarEjecucionFuncionIframe('frameContenido','recargarOrganigrama','\'".$fila["cveUnidad"]."\',\'".$idOrganigrama."\'');";
			return true;
		}
		
	}
	
	
	function asignarDespachoAsunto($idFormulario,$idRegistro)
	{
		global $con;
		
		
		
		$_SESSION["funcionCargaProceso"]="obtenerAcuseRadicacion()";
		$_SESSION["funcionCargaUnicaProceso"]=1;
		$_SESSION["funcionRetrasoCargaProceso"]=1000;
	

		$consulta="SELECT * FROM _632_tablaDinamica WHERE id__632_tablaDinamica=".$idRegistro;
		$fRegistro=$con->obtenerPrimeraFilaAsoc($consulta);
		
		$etapaContinuacion="2.".$fRegistro["tipoProceso"];
		
		if(($fRegistro["carpetaAdministrativa"]!="N/E")&&($fRegistro["carpetaAdministrativa"]!=""))
		{
			cambiarEtapaFormulario($idFormulario,$idRegistro,$etapaContinuacion,"",-1,"NULL","NULL",1206);
			return true;
		}
		
		$consulta="SELECT id__642_tablaDinamica FROM _642_tablaDinamica gR,_642_chkTiposProceso tP WHERE tP.idPadre=gR.id__642_tablaDinamica
					AND tP.idOpcion=".$fRegistro["tipoProceso"]." and idEstado=2";

		$listaGrupos=$con->obtenerListaValores($consulta);
		
		if($listaGrupos=="")
			$listaGrupos=-1;
			
		$consulta="SELECT * FROM _643_tablaDinamica WHERE idReferencia in(".$listaGrupos.") 
					and jurisdiccion=".$fRegistro["jurisdiccion"]." AND especialidad=".$fRegistro["especialidad"].
					" AND cmbTema=".$fRegistro["temaProceso"]." order by id__643_tablaDinamica asc";
					

		$filaGrupo=$con->obtenerPrimeraFilaAsoc($consulta);
		if(!$filaGrupo)
		{
			$consulta="SELECT * FROM _643_tablaDinamica WHERE idReferencia in(".$listaGrupos.") 
					and jurisdiccion=".$fRegistro["jurisdiccion"]." AND especialidad=".$fRegistro["especialidad"].
					"   order by id__643_tablaDinamica asc";
					
					
			$filaGrupo=$con->obtenerPrimeraFilaAsoc($consulta);
			if(!$filaGrupo)
			{
				$consulta="SELECT * FROM _643_tablaDinamica WHERE idReferencia in(".$listaGrupos.") 
						and jurisdiccion=".$fRegistro["jurisdiccion"]."  order by id__643_tablaDinamica asc";
				$filaGrupo=$con->obtenerPrimeraFilaAsoc($consulta);
				if(!$filaGrupo)
				{
					$consulta="SELECT * FROM _643_tablaDinamica WHERE idReferencia in(".$listaGrupos.") 
								 order by id__643_tablaDinamica asc";
					$filaGrupo=$con->obtenerPrimeraFilaAsoc($consulta);
				}
			}
		}
		
		$consulta="SELECT despacho FROM _644_tablaDinamica d,817_organigrama o WHERE d.idReferencia=".$filaGrupo["idReferencia"]." and 
					o.codigoUnidad=d.despacho order by claveDepartamental";
		$universoDespachos=$con->obtenerListaValores($consulta);
		
		$arrConfiguracion["tipoAsignacion"]="";
		$arrConfiguracion["serieRonda"]="GrupoReparto_".$filaGrupo["idReferencia"];
		$arrConfiguracion["universoAsignacion"]=$universoDespachos;
		$arrConfiguracion["idObjetoReferencia"]=-1;
		$arrConfiguracion["pagarDeudasAsignacion"]=false;
		$arrConfiguracion["considerarDeudasMismaRonda"]=false;
		$arrConfiguracion["limitePagoRonda"]=0;
		$arrConfiguracion["escribirAsignacion"]=true;
		$arrConfiguracion["idFormulario"]=$idFormulario;
		$arrConfiguracion["idRegistro"]=$idRegistro;
		$resultado= obtenerSiguienteAsignacionObjeto($arrConfiguracion,true);
		
		$cveDespacho=$resultado["idUnidad"];
		
		$consulta="SELECT id__17_tablaDinamica FROM _17_tablaDinamica WHERE claveUnidad='".$cveDespacho."'";
		$idUnidadGestion=$con->obtenerValor($consulta);
		if($idUnidadGestion=="")
			$idUnidadGestion=-1;
			
		$anio=date("Y");
		
		$consulta="SELECT carpetaAdministrativa,idActividad,tipoProceso FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
		$fDatosCarpeta=$con->obtenerPrimeraFila($consulta);
	
		
		if(($fDatosCarpeta[0]!="")&&($fDatosCarpeta[0]!="N/E"))
			return true;
		$arrCodigoUnico=obtenerSiguienteCodigoUnicoProceso($cveDespacho,$anio,$fDatosCarpeta[2],$idFormulario,$idRegistro);	
		$carpetaAdministrativa=$arrCodigoUnico[0];
		
		
		$idActividad=$fDatosCarpeta[1];
		
		$consulta=array();
		$x=0;
		$consulta[$x]="begin";
		$x++;
		$consulta[$x]="INSERT INTO 7006_carpetasAdministrativas(carpetaAdministrativa,fechaCreacion,responsableCreacion,idFormulario,
						idRegistro,unidadGestion,etapaProcesalActual,idActividad,carpetaAdministrativaBase,
						tipoCarpetaAdministrativa,unidadGestionOriginal) 
						VALUES('".$carpetaAdministrativa."','".date("Y-m-d H:i:s")."',".$_SESSION["idUsr"].",".$idFormulario.",'".$idRegistro."','".
						$cveDespacho."',0,".$idActividad.",'',".$fDatosCarpeta[2].",'".$cveDespacho."')";
		$x++;
		$consulta[$x]="update _".$idFormulario."_tablaDinamica set carpetaAdministrativa='".$carpetaAdministrativa.
					"',codigoInstitucion='".$cveDespacho."',despachoAsignado='".$cveDespacho.
					"' where id__".$idFormulario."_tablaDinamica=".$idRegistro;
		$x++;
	
		$consulta[$x]="commit";
		$x++;
	
		if($con->ejecutarBloque($consulta))
		{
	
			$query="SELECT documentoAdjunto,idDocumento FROM _632_documentacionRequerida WHERE idReferencia=".$idRegistro;
	
			$rDocumentos=$con->obtenerFilas($query);
			while($fDocumento=mysql_fetch_row($rDocumentos))
			{
				convertirDocumentoUsuarioDocumentoResultadoProceso($fDocumento[0],$idFormulario,$idRegistro,"",$fDocumento[1]);
			}
			
			
			cambiarEtapaFormulario($idFormulario,$idRegistro,$etapaContinuacion,"",-1,"NULL","NULL",1206);
			return true;
	
		}
		return false;
		
	}
	
	
	function obtenerSiguienteCodigoUnicoProceso($idUnidadGestion,$anio,$tipoCarpeta,$idFormulario,$idRegistro)
	{
		global $con;
		$tratarComoRadicacion=true;
		
		
		$consulta="SELECT * FROM _632_tablaDinamica WHERE id__632_tablaDinamica=".$idRegistro;
		$fDatosRadicacion=$con->obtenerPrimeraFilaAsoc($consulta);
		
		$generarCodigoUnico=true;
		if(!$tratarComoRadicacion)
		{
			switch($fDatosRadicacion["tipoProceso"])
			{
				case "1"://Radicación Inicial
					$generarCodigoUnico=true;
				break;
				case "2"://Radicación Segunda Instancia
				case "3"://Registro de Casación
				case "4"://Revisión de Expediente
						$generarCodigoUnico=false;
				break;
			}
			
		}
		
		
		if($generarCodigoUnico)
		{
			$query="select folioActual FROM 7004_seriesUnidadesGestion WHERE idUnidadGestion='".$idUnidadGestion.
						"' AND anio=".$anio." and tipoDelito='".$tipoCarpeta."'";
			$folioActual=$con->obtenerValor($query);
			
			if($folioActual=="")
			{
				$folioActual=1;
				$agregarSecuencia=true;	
			}
			else
			{
				$folioActual++;
			}
						
			
			
			//-------
			$folioCorreccion=$folioActual-10;
			if($folioCorreccion<1)
				$folioCorreccion=1;
			$query="SELECT claveRegistro,claveUnidad FROM _17_tablaDinamica WHERE claveUnidad='".$idUnidadGestion."'";
			$fRegistroUnidad=$con->obtenerPrimeraFila($query);
			$cveUnidadGestion=$fRegistroUnidad[0];
			$cvAdscripcion=$fRegistroUnidad[1];
			$formatoCarpeta= $fRegistroUnidad[0]."-".$anio."[folioCarpeta]";
			$formatoCarpeta."01";
			
			
			
			$carpetaAdministrativa=str_replace("[folioCarpeta]",str_pad($folioCorreccion,5,"0",STR_PAD_LEFT),$formatoCarpeta);
			while(existeCarpetaAdministrativa($carpetaAdministrativa,""))
			{
				$folioCorreccion++;	
				$carpetaAdministrativa=str_replace("[folioCarpeta]",str_pad($folioCorreccion,5,"0",STR_PAD_LEFT),$formatoCarpeta);
			}
			
			
			if($folioCorreccion<$folioActual)
			{
				registrarRecuperacionCodigoUnico($idUnidadGestion,$carpetaAdministrativa,'');
			}
			$folioActual=$folioCorreccion;
			///
			if($agregarSecuencia)
			{
				$query="INSERT INTO 7004_seriesUnidadesGestion(idUnidadGestion,anio,folioActual,tipoDelito) VALUES('".$idUnidadGestion.
						"',".$anio.",".$folioActual.",'".$tipoCarpeta."')";
			}
			else
			{
				$query="update 7004_seriesUnidadesGestion set folioActual=".$folioActual." where idUnidadGestion='".$idUnidadGestion.
						"' and anio=".$anio." and tipoDelito='".$tipoCarpeta."'";
			}	
			
			
			if($con->ejecutarConsulta($query))
			{
				$arrResultado[0]=$carpetaAdministrativa;
				$arrResultado[1]=$folioActual;
				return $arrResultado;
			}
		}
		else
		{
			$procesoJudicialOrigen=substr($fDatosRadicacion["procesoJudicialOrigen"],0,strlen($fDatosRadicacion["procesoJudicialOrigen"])-2);
			$consulta="SELECT carpetaAdministrativa FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa LIKE 
						'".$procesoJudicialOrigen."%' ORDER BY carpetaAdministrativa DESC";
			$ultimoProceso=$con->obtenerValor($consulta);
			$maxValor=substr($ultimoProceso,strlen($fDatosRadicacion["procesoJudicialOrigen"])-2,2);
			
			$folioCorreccion=($maxValor*1)+1;
			
			$folioCorreccion=$folioActual-5;
			if($folioCorreccion<1)
				$folioCorreccion=1;
			
			$carpetaAdministrativa=$procesoJudicialOrigen.str_pad($folioCorreccion,2,"0",STR_PAD_LEFT);
			
			
			while(existeCarpetaAdministrativa($carpetaAdministrativa,""))
			{
				$carpetaAdministrativa=$procesoJudicialOrigen.str_pad($folioCorreccion,2,"0",STR_PAD_LEFT);
				$folioCorreccion++;	
				
			}
			
			$folioActual=$folioCorreccion;
					
			$arrResultado[0]=$carpetaAdministrativa;
			$arrResultado[1]=$folioActual;
			return $arrResultado;
		}
		
		
		
	}
	
	
	function registrarRecuperacionCodigoUnico($idUnidad,$folio,$tipoCarpeta)
	{
		global $con;
		$consulta="INSERT INTO 7048_registroRecuperacionFolio(idUnidadGestion,folioRecuperado,fechaRecuperacion,tipoCarpeta) 
				VALUES('".$idUnidad."','".$folio."','".date("Y-m-d H:i:s")."','".$tipoCarpeta."')";
		return $con->ejecutarConsulta($consulta);
	}
?>