<?php
	include_once("latis/conexionBD.php");
	include_once("latis/cfdi/cNomina.php");
	include_once("latis/cfdi/funciones.php");
	include_once("latis/cfdi/cFDIFinkok.php");


	function generarXMLNominaGeneral($idRegistro,$folio="")
	{
		global $con;
		$consulta="select * from 699_empleadosEjecucionNominaV2 where idEmpleadoNomina=".$idRegistro;

		$fRegistroNomina=$con->obtenerPrimeraFila($consulta);
		
		$consulta="select * from 698_nominasV2 where idNomina=".$fRegistroNomina[1];
		
		$fNomina=$con->obtenerPrimeraFila($consulta);
		
		$consulta="select * from 693_empleadosNominaV2 where idEmpleado=".$fRegistroNomina[2];

		$fDatosEmpleado=$con->obtenerPrimeraFila($consulta);

		$periodicidad=$fDatosEmpleado[27];
		
		
		$idRegPatronal=$fDatosEmpleado[18];
		if($idRegPatronal=="")
			$idRegPatronal=-1;
		$consulta="SELECT registroPatronal FROM 6927_empresaRegistroPatronal WHERE idRegistro=".$idRegPatronal;
		$registroPatronal=$con->obtenerValor($consulta);
		
		
		$tJornada="";
		$tContrato="";
		$tRegimen="";
		
		$consulta="SELECT r.clave FROM 683_regimenContratacionSAT r WHERE  r.idRegimen=".$fDatosEmpleado[24];

		$tRegimen=$con->obtenerValor($consulta);
		
		if(($fDatosEmpleado[26]!="")&&($fDatosEmpleado[26]!="0"))
		{
			$consulta="SELECT tipoJornada FROM 689_tipoJornadas WHERE idTipoJornada=".$fDatosEmpleado[26];
			$tJornada=$con->obtenerValor($consulta);
		
		}
		
		if(($fDatosEmpleado[25]!="")&&($fDatosEmpleado[25]!="0"))
		{
			$consulta="SELECT tipoContrato FROM 686_tiposContrato WHERE idTipoContrato=".$fDatosEmpleado[25];
			$tContrato=$con->obtenerValor($consulta);
		}
		
		
		$consulta="SELECT puesto,r.clave FROM 692_puestosNominaV2 p,684_riesgoPuestoSAT r WHERE idPuesto=".$fDatosEmpleado[21]." AND r.idRiesgo=p.idRiesgoPuesto";
		$fDatosPuesto=$con->obtenerPrimeraFila($consulta);	
		if(!$fDatosPuesto)
		{
			$fDatosPuesto[0]="";
			$fDatosPuesto[1]="";
		}
		
			
		$consulta="SELECT nombreDepartamento FROM 691_departamentosNominaV2 WHERE idDepartamento=".$fDatosEmpleado[22];
		$departamento=$con->obtenerValor($consulta);	
		
		
		$consulta="SELECT * FROM 6927_empresas WHERE idEmpresa=".$fDatosEmpleado[20];

		$fEmpresa=$con->obtenerPrimeraFila($consulta);


		$consulta="SELECT UPPER(municipio) FROM 821_municipios WHERE cveMunicipio='".$fEmpresa[14]."'";
		$municipioEmisor=$con->obtenerValor($consulta);
		$consulta="SELECT UPPER(estado) FROM 820_estados WHERE cveEstado='".$fEmpresa[13]."'";
		$estadoEmisor=$con->obtenerValor($consulta);

		$consulta="SELECT noCertificado,claveArchivoKey,certificadoDigital FROM 687_certificadosSelloDigital WHERE idCertificado=".$fNomina[10];
		$fCertificado=$con->obtenerPrimeraFila($consulta);
		$consulta="SELECT serie FROM 688_seriesCertificados WHERE idSerieCertificado=".$fNomina[11];
		$fSerie=$con->obtenerPrimeraFila($consulta);
		$nom=new cNominaCFDI();
		$objNomina=$nom->generarEstructuraLlenadoXML();
		
		$noFolio=$folio;
		if($noFolio=="")
			$noFolio=obtenerFolioCertificado($fNomina[11]);
		
		
		$consulta="SELECT UPPER(cveSAT) FROM 600_formasPago WHERE idFormaPago=".$fDatosEmpleado[28];
		$formaPago=$con->obtenerValor($consulta);

		
		$descuento=0;//Pendiente
		$retenciones=0;//Pendiente
		
		
		$objNomina["sello"]="";//* Obligatorio
		
		$objNomina["idCertificado"]=$fNomina[10];
		$objNomina["idSerie"]=$fNomina[11];
		$objNomina["idClienteFactura"]=$fRegistroNomina[2];

		
		$datosNomina["numDiasPagados"]=$fNomina[4]; //* Obligatorio

		$objNomina["noCertificado"]=$fCertificado[0]; //* Obligatorio
		$objNomina["certificado"]=$fCertificado[2]; //* Obligatorio
		$objNomina["serie"]=$fSerie[0]; 
		$objNomina["folio"]=$noFolio; 
		$objNomina["motivoDescuento"]="DEDUCCIONES NÓMINA"; 
		$objNomina["lugarExpedicion"]="ORIZABA, ".$estadoEmisor; //* Obligatorio
		$objNomina["tipoCambio"]="1"; //* Obligatorio
		
		$objNomina["metodoDePago"]=$formaPago; //* Obligatorio
		
		$objNomina["formaPago"]="PAGO EN UNA SOLA EXHIBICIÓN"; //* Obligatorio
		$objNomina["numCtaPago"]="NO APLICA";
		
		
		$objNomina["datosEmisor"]=obtenerSeccionDatosEmisor($fDatosEmpleado[20]);
		
		
		$fReceptor[0]=$fDatosEmpleado[8]; //Nombre
		$fReceptor[1]=$fDatosEmpleado[6];  //Ap. paterno
		$fReceptor[2]=$fDatosEmpleado[7];	//Ap. materno
		$fReceptor[3]=$fDatosEmpleado[2].$fDatosEmpleado[3].$fDatosEmpleado[4]; //RFC
		$fReceptor[4]=$fDatosEmpleado[5]; //CURP
		$fReceptor[5]=$fDatosEmpleado[19]; //IMSS
		
		
		
		$fDirReceptor[0]=$fDatosEmpleado[13];  //cale
		$fDirReceptor[1]=$fDatosEmpleado[14];	//No. exterior
		$fDirReceptor[2]=$fDatosEmpleado[16];  //Clornia
		
		$consulta="SELECT upper(municipio) FROM 821_municipios WHERE cveMunicipio='".$fDatosEmpleado[11]."'";
		$fDirReceptor[3]=$con->obtenerValor($consulta);  //municipio
		$consulta="SELECT upper(estado) FROM 820_estados WHERE cveEstado='".$fDatosEmpleado[10]."'";
		$fDirReceptor[4]=$con->obtenerValor($consulta);   //estado
		
		
		$fDirReceptor[5]="MÉXICO";   //pars
		$fDirReceptor[6]=$fDatosEmpleado[17];   //cp
		$fDirReceptor[7]=$fDatosEmpleado[15];   //noInterios
		$fDirReceptor[8]=$fDatosEmpleado[12];  //localidad
		
		
		
		$objDatos["rfc"]=normalizarRFC($fReceptor[3]);
		$objDatos["razonSocial"]=$fReceptor[0]." ".$fReceptor[1]." ".$fReceptor[2];

		$objDatos["domicilio"]["calle"]=trim($fDirReceptor[0]);
		$objDatos["domicilio"]["noExterior"]=trim($fDirReceptor[1]);
		$objDatos["domicilio"]["noInterior"]=$fDirReceptor[7];
		$objDatos["domicilio"]["colonia"]=trim($fDirReceptor[2]);
		$objDatos["domicilio"]["localidad"]=$fDirReceptor[8];
		$objDatos["domicilio"]["municipio"]=trim($fDirReceptor[3]);
		$objDatos["domicilio"]["estado"]=trim($fDirReceptor[4]);
		$objDatos["domicilio"]["pais"]=trim($fDirReceptor[5]);
		$objDatos["domicilio"]["codigoPostal"]=trim($fDirReceptor[6]);
		
		$objNomina["datosReceptor"]=$objDatos;
		
		$datosNomina["curp"]=$fReceptor[4]; //* Obligatorio
		
		
		$datosNomina["fechaPago"]=$fNomina[3]; //* Obligatorio
		$datosNomina["fechaInicioPago"]=$fNomina[1]; //* Obligatorio
		$datosNomina["fechaFinPago"]=$fNomina[2]; //* Obligatorio
		$datosNomina["periodicidadPago"]=$periodicidad; //* Obligatorio
		
		
		$cveBanco="";
		if($fDatosEmpleado[29]!="")
		{
			$consulta="SELECT claveSAT FROM 6000_bancos WHERE idBanco=".$fDatosEmpleado[29];
			$cveBanco=$con->obtenerValor($consulta);
		}
		
		$numEmpleado=$fDatosEmpleado[0];
		if($fDatosEmpleado[1]!="")
			$numEmpleado=$fDatosEmpleado[1];
		$datosNomina["numEmpleado"]=str_pad($numEmpleado,15,"0",STR_PAD_LEFT); //* Obligatorio
		$datosNomina["numSS"]=normalizarSoloNumeros($fDatosEmpleado[19]);
		$datosNomina["clabe"]=$fDatosEmpleado[31];
		$datosNomina["banco"]=$cveBanco;
		$datosNomina["departamento"]=$departamento;
		
		$datosNomina["registroPatronal"]=$registroPatronal;
		
		$datosNomina["puesto"]=$fDatosPuesto[0];
		$datosNomina["riezgoPuesto"]=$fDatosPuesto[1];
		$datosNomina["tipoContrato"]="";//$tContrato;
		$datosNomina["tipoJornada"]="";//$tJornada;
		$datosNomina["tipoRegimen"]=$tRegimen; //* Obligatorio
		$datosNomina["fechaInicioRelLaboral"]=$fDatosEmpleado[23];
		
		
		$datosNomina["antiguedad"]="";
		$datosNomina["salarioBaseCotApor"]="";
		$datosNomina["sdi"]=$fDatosEmpleado[32];
		
		
		
		$arrPercepciones=array();
		$arrDeducciones=array();
		
		
		
		
			
		$objConceptoNomina=array();
		
		//Percepciones
		
		$totalISR=0;
		$consulta="SELECT * FROM 700_percepcionesDeduccionesV2 WHERE idEmpleadoNomina=".$idRegistro;
		$res=$con->obtenerFilas($consulta);
		while($fConceptos=mysql_fetch_row($res))
		{
			$objConceptoNomina=array();
			if($fConceptos[2]==2)
			{
				$categoria=$fConceptos[3];
				
				
				$consulta="SELECT clave FROM 681_tiposPercepcionSAT where idTipoPercepcion=".$categoria;
				$tConcepto=$con->obtenerValor($consulta);
				
				if($tConcepto!="")
					$tConcepto=str_pad($tConcepto,3,"0",STR_PAD_LEFT);
					
				$objConceptoNomina["clave"]=str_pad($fConceptos[4],14,"0",STR_PAD_LEFT);	
				$objConceptoNomina["descripcion"]=$fConceptos[5];
				$objConceptoNomina["tipoPercepcion"]=$tConcepto;//tipoDeduccion   //* Obligatorio
				$objConceptoNomina["importeGravado"]=$fConceptos[6];	//* Obligatorio
				$objConceptoNomina["importeExento"]=$fConceptos[7];	//* Obligatorio
				if(($objConceptoNomina["importeGravado"]+$objConceptoNomina["importeExento"])>0)		
					array_push($arrPercepciones,$objConceptoNomina);
				
				
			}
			else
			{
			//Deducciones
			
				$categoria=$fConceptos[3];
				$consulta="SELECT clave FROM 682_tiposDeduccionSAT where idTipoDeduccion=".$categoria;
				$tConcepto=$con->obtenerValor($consulta);
				if($tConcepto!="")
					$tConcepto=str_pad($tConcepto,3,"0",STR_PAD_LEFT);
				$objConceptoNomina["clave"]=str_pad($fConceptos[4],14,"0",STR_PAD_LEFT);	
				$objConceptoNomina["descripcion"]=$fConceptos[5];
				$objConceptoNomina["tipoDeduccion"]=$tConcepto;//tipoDeduccion   //* Obligatorio
				$objConceptoNomina["importeGravado"]=$fConceptos[6];	//* Obligatorio
				$objConceptoNomina["importeExento"]=$fConceptos[7];	//* Obligatorio
				
				
				if($categoria!=2)
					$descuento+=($objConceptoNomina["importeGravado"]+$objConceptoNomina["importeExento"]);
				else
					$totalISR=($objConceptoNomina["importeGravado"]+$objConceptoNomina["importeExento"]);
				if(($objConceptoNomina["importeGravado"]+$objConceptoNomina["importeExento"])>0)		
					array_push($arrDeducciones,$objConceptoNomina);
			}
		
		
		}
		
		$datosNomina["arrPercepciones"]=$arrPercepciones;
		$datosNomina["arrDeducciones"]=$arrDeducciones;
		
		
		$arrIncapacidades=array();
		$consulta="SELECT * FROM 701_incapacidadesNominaV2 WHERE idEmpleadoNomina=".$idRegistro;
		$res=$con->obtenerFilas($consulta);
		while($fConceptos=mysql_fetch_row($res))
		{
			$objIncapacidad=array();
			$objIncapacidad["diasIncapacidad"]=$fConceptos[1]; //* Obligatorio
			$objIncapacidad["tipoIncapacidad"]=$fConceptos[2]; //* Obligatorio
			$objIncapacidad["descuentoIncapacidad"]=$fConceptos[3]; //* Obligatorio
			array_push($arrIncapacidades,$objIncapacidad);
		}
		$datosNomina["arrIncapacidades"]=$arrIncapacidades;
		$arrHorasExtra=array();
		$consulta="SELECT * FROM 702_horasExtraNominaV2 WHERE idEmpleadoNomina=".$idRegistro;
		$res=$con->obtenerFilas($consulta);
		while($fConceptos=mysql_fetch_row($res))
		{
			$objHorasExtra=array();
			$objHorasExtra["diasHorasExtras"]=$fConceptos[1]; //* Obligatorio 
			
			$tHoras="";
			switch($fConceptos[2])
			{
				case 2:
					$tHoras="Dobles";
				break;
				case 3:
					$tHoras="Triples";
				break;
			}
			
			$objHorasExtra["tipoPagoHoras"]=$tHoras; //* Obligatorio
			$objHorasExtra["totalHorasExtra"]=$fConceptos[3]; //* Obligatorio
			$objHorasExtra["importePagado"]=$fConceptos[4]; //* Obligatorio
			array_push($arrHorasExtra,$objHorasExtra);
		}
		$datosNomina["arrHorasExtra"]=$arrHorasExtra;
		if(($fRegistroNomina[9]!="")&&($fRegistroNomina[9]>0))
			$datosNomina["sdi"]=$fRegistroNomina[9];
		
		
		
		
		
		$objNomina["datosNomina"]=$datosNomina;
		
		
		$retenciones=$totalISR;
		$objNomina["subtotal"]=$fRegistroNomina[3]; //* Obligatorio
		$objNomina["descuento"]=$descuento;
		$objNomina["total"]=$objNomina["subtotal"]-$descuento-$retenciones; //* Obligatorio
		
		

		$objNomina["impuestos"]=array();
		$objNomina["impuestos"]["retenciones"]=array();
		$objNomina["impuestos"]["traslados"]=array();
		$oImpuestoRetencion=array();
		$oImpuestoRetencion["impuesto"]="ISR";//*  ISR | IVA
		$oImpuestoRetencion["importe"]=$totalISR;//*
		array_push($objNomina["impuestos"]["retenciones"],$oImpuestoRetencion);
		return ($objNomina);
	}
	
	
	function generarListadoXMLNominaGeneral($idNomina)
	{
		global $con;
		$consulta="SELECT idEmpleadoNomina,idComprobante FROM 699_empleadosEjecucionNominaV2 WHERE idNomina=".$idNomina;
		$res=$con->obtenerFilas($consulta);
		while($f=mysql_fetch_row($res))
		{
			$c=new cNominaCFDI();
			$idRegistro=$f[0];
			$folio="";
			if($f[1]!="")
			{
				$query="SELECT folio FROM 703_relacionFoliosCFDI WHERE idFolio=".$f[1];
				$folio=$con->obtenerValor($query);
				
			}

			$oNomina=generarXMLNominaGeneral($idRegistro,$folio);
			
			$c->setObjNomina($oNomina);
			$XML=$c->generarXML();
			if($f[1]=="")
			{
				$idFactura=$c->registrarXML(2,$idRegistro);
				if(!$c->generarSelloDigital())
					return false;
			}
			else
			{
				$c->actualizarXMLComprobante($f[1]);
				if(!$c->generarSelloDigital($f[1]))
					return false;
				
			}
			
	
		}
		
		return true;
	}
	
	function timbrarXMLNominaGeneral($idNomina)
	{
		global $con;
		$cTimbrador=new cFDIFinkok();
		$consulta="SELECT idEmpleadoNomina,idComprobante FROM 699_empleadosEjecucionNominaV2 WHERE idNomina=".$idNomina;
		$res=$con->obtenerFilas($consulta);
		while($f=mysql_fetch_row($res))
		{
			
			$cTimbrador->timbrarComprobante($f[1]);

	
		}
		return true;
	}
	
	function generarXMLModuloNomina($idRegistro)
	{
		global $con;
		$obtenerNombreCalculoActualizado=true;
		$consulta="SELECT * FROM 671_asientosCalculosNomina WHERE idAsientoNomina=".$idRegistro;

		$fAsientoNomina=$con->obtenerPrimeraFila($consulta);
		$plantelEmpleado=$fAsientoNomina[1];
		
		$idComprobante=$fAsientoNomina[24];

		
		$arrEtiquetas=array();
		$consulta="SELECT idEtiquetaAgrupadora,clave,etiqueta,idCategoria,idCategoriaSAT FROM 718_etiquetasAgrupadoras WHERE idPerfilNomina=".$fAsientoNomina[11];

		$resEtiquetas=$con->obtenerFilas($consulta);
		while($filaEtiquetas=mysql_fetch_row($resEtiquetas))
		{

			$arrEtiquetas[$filaEtiquetas[0]]["clave"]=$filaEtiquetas[1];
			$arrEtiquetas[$filaEtiquetas[0]]["descripcion"]=$filaEtiquetas[2];
			$arrEtiquetas[$filaEtiquetas[0]]["idCategoria"]=$filaEtiquetas[3];
			$arrEtiquetas[$filaEtiquetas[0]]["idCategoriaSAT"]=$filaEtiquetas[4];
			$arrEtiquetas[$filaEtiquetas[0]]["importeGravado"]=0;
			$arrEtiquetas[$filaEtiquetas[0]]["importeExento"]=0;
			$arrEtiquetas[$filaEtiquetas[0]]["conceptos"]=array();
			
		}
		
		$idUsuario=$fAsientoNomina[4];
		$idNomina=$fAsientoNomina[12];

		$consulta="select * from 672_nominasEjecutadas where idNomina=".$idNomina;

		$fNomina=$con->obtenerPrimeraFila($consulta);
		$nom=new cNominaCFDI(3.3);

		$objNomina=$nom->generarEstructuraLlenadoXML();
		
		$objNomina["versionNomina"]=$fNomina[30]; //Version 1.2
		$objNomina["tipoNomina"]=$fNomina[31];	//Version 1.2
		$objNomina["idAsientoNomina"]=$idRegistro;
		$objNomina["cfdiRelacionados"]=array();
		$objNomina["relacionCFDI"]="";
		
		if($idComprobante!="")
		{
			$XMLComprobante=$nom->cargarComprobanteXML($idComprobante);
			$oComprobante=$nom->convertirXMLCadenaToObj($XMLComprobante);

			if($oComprobante["folioUUID"]!="")
			{
				$objNomina["relacionCFDI"]="04";
				
				$folioUUID=$oComprobante["folioUUID"];
				array_push($objNomina["cfdiRelacionados"],$folioUUID);
				
				
			}
			else
			{
				if(sizeof($oComprobante["cfdiRelacionados"])>0)
				{
					$objNomina["relacionCFDI"]=$oComprobante["relacionCFDI"];
					foreach($oComprobante["cfdiRelacionados"] as $u)
					{
						array_push($objNomina["cfdiRelacionados"],$u);
					}
				}
			}
			
			
			
		}
		
		
		$consulta="SELECT tipoNomina,clave_periodicidad FROM 677_tipoPagoNomina t,662_perfilesNomina p WHERE p.idPerfilesNomina=".$fNomina[1]." AND t.idTipoNomina=p.idPeriodicidad";
		$fPeriodicidad=$con->obtenerPrimeraFila($consulta);
		$periodicidad=$fPeriodicidad[0];
		if($objNomina["versionNomina"]=="1.2")//Version 1.2
		{
			$periodicidad=$fPeriodicidad[1];
		}
		
		$codigoUnidad=$fNomina[18];
		$registroPatronal="";
		switch($fNomina[10])
		{
			case 2:
				$consulta="SELECT idDepartamento,clabe ,regimenContratacion as regimen,tipoJornada,tipoContrato as idTipoContrato,idPuesto,idBanco,formaPago,noRegistroPatronal FROM 693_empleadosNominaV2 WHERE idEmpleado=".$idUsuario;
			break;
			default: 
				$consulta="SELECT codigoUnidad,idCuentaDeposito,tipoContratacion,idTipoJornada,idTipoContrato,cod_Puesto FROM 801_adscripcion WHERE idUsuario=".$idUsuario;
			break;
		}



		$fDatosContratacion=$con->obtenerPrimeraFila($consulta);
		if($fDatosContratacion[1]=="")
			$fDatosContratacion[1]=-1;
		if($fDatosContratacion[2]=="")
			$fDatosContratacion[2]=-1;

		
		$tJornada="";
		$tContrato="";
		$tRegimen="";
		switch($fNomina[10])
		{
			case 2:
				$consulta="SELECT r.clave FROM 683_regimenContratacionSAT r WHERE r.idRegimen=".$fDatosContratacion[2];
			break;
			default:
				$consulta="SELECT r.clave FROM 690_tiposContratacionEmpresa t,683_regimenContratacionSAT r WHERE idTipoContratacion=".$fDatosContratacion[2]." AND r.idRegimen=t.idRegimenContratacion";
			break;
		}
		$tRegimen=$con->obtenerValor($consulta);
		
		if(($fDatosContratacion[3]!="")&&($fDatosContratacion[3]!="0"))
		{
			$consulta="SELECT tipoJornada FROM 689_tipoJornadas WHERE idTipoJornada=".$fDatosContratacion[3];
			$tJornada=$con->obtenerValor($consulta);
		}
		
		if(($fDatosContratacion[4]!="")&&($fDatosContratacion[4]!="0"))
		{
			$consulta="SELECT tipoContrato,cve_tipoContrato FROM 686_tiposContrato WHERE idTipoContrato=".$fDatosContratacion[4];

			$fContrato=$con->obtenerPrimeraFila($consulta);
			$tContrato=$fContrato[0];
			if($objNomina["versionNomina"]=="1.2")
				$tContrato=$fContrato[1];
		}
		
		if($fDatosContratacion[5]=="")
			$fDatosContratacion[5]=-1;
		
		switch($fNomina[10])
		{
			case 2:
				$consulta="SELECT puesto,r.clave FROM 692_puestosNominaV2 p,684_riesgoPuestoSAT r WHERE idPuesto=".$fDatosContratacion[5]." AND r.idRiesgo=p.idRiesgoPuesto";
			break;
			default:
				$consulta="SELECT puesto,r.clave FROM 819_puestosOrganigrama p,684_riesgoPuestoSAT r WHERE idPuesto=".$fDatosContratacion[5]." AND r.idRiesgo=p.nivelRiesgo";
			break;
		}

		$fDatosPuesto=$con->obtenerPrimeraFila($consulta);	
		if(!$fDatosPuesto)
		{
			$fDatosPuesto[0]="";
			$fDatosPuesto[1]="";
		}

		
		switch($fNomina[10])
		{
			case 2:
				$consulta="SELECT claveSAT FROM 6000_bancos WHERE idBanco=".$fDatosContratacion[6];
				$claveBancoSAT=$con->obtenerValor($consulta);
				if($fDatosContratacion[1]==-1)
					$fDatosContratacion[1]="";
				$consulta="SELECT '".$claveBancoSAT."' as claveSAT,'".$fDatosContratacion[1]."' as clabe,'' as cuenta ";
			break;
			default:
				$consulta="SELECT b.claveSAT,cu.clabe,cu.cuenta FROM 823_cuentasUsuario cu,6000_bancos b  
							WHERE b.idBanco=cu.idBanco AND cu.idCuentaUsuario=".$fDatosContratacion[1];
			break;
		}

		$fDatosBancarios=$con->obtenerPrimeraFila($consulta);

		
		switch($fNomina[10])
		{
			case 2:
				$consulta="SELECT nombreDepartamento FROM 691_departamentosNominaV2 WHERE idDepartamento='".$fDatosContratacion[0]."'";
			break;
			default:
				$consulta="select unidad from 817_organigrama where codigoUnidad='".$fDatosContratacion[0]."'";
			break;
		}
		
		
		$departamento=$con->obtenerValor($consulta);	
		

		$fDatosNomina=$fAsientoNomina;
		
		$objDatosNomina=unserialize($fDatosNomina[8]);
		
		
		switch($fNomina[10])
		{
			case 2:
				$consulta="SELECT idCertificado,idSerie,idEmpresa,'".$fDatosContratacion[8]."' as idRegistroPatronal  FROM 715_relacionEmpresaSerieNomina where idEmpresa='".$codigoUnidad."'";
			break;
			default:
				$consulta="SELECT idCertificado,idSerie,idEmpresa,idRegistroPatronal FROM 717_relacionInstitucionSerieNomina WHERE codigoUnidad='".$codigoUnidad."'";

			break;
		}
		
		$dCertificado=$con->obtenerPrimeraFila($consulta);
		
		if(!$dCertificado)
		{
			$dCertificado[0]=-1;
			$dCertificado[1]=-1;
			$dCertificado[2]=$codigoUnidad;
			$dCertificado[3]=-1;
		}
		
		
		if($dCertificado[3]=="")
			$dCertificado[3]=-1;
		$consulta="SELECT registroPatronal FROM 6927_empresaRegistroPatronal WHERE idRegistro=".$dCertificado[3];
		$registroPatronal=$con->obtenerValor($consulta);
		
		
		$consulta="SELECT * FROM 6927_empresas WHERE idEmpresa=".$dCertificado[2];
		$fEmpresa=$con->obtenerPrimeraFila($consulta);
		
		
		
		$noFolio=obtenerFolioCertificado($dCertificado[1]);
		

		$consulta="SELECT UPPER(municipio) FROM 821_municipios WHERE cveMunicipio='".$fEmpresa[14]."'";
		$municipioEmisor=$con->obtenerValor($consulta);
		$consulta="SELECT UPPER(estado) FROM 820_estados WHERE cveEstado='".$fEmpresa[13]."'";
		$estadoEmisor=$con->obtenerValor($consulta);

		$consulta="SELECT noCertificado,claveArchivoKey,certificadoDigital FROM 687_certificadosSelloDigital WHERE idCertificado=".$dCertificado[0];
		$fCertificado=$con->obtenerPrimeraFila($consulta);
		$consulta="SELECT serie FROM 688_seriesCertificados WHERE idSerieCertificado=".$dCertificado[1];
		$fSerie=$con->obtenerPrimeraFila($consulta);
		
		
		
		$descuento=0;//Pendiente
		$retenciones=0;//Pendiente
		
		$objNomina["sello"]="";//* Obligatorio
		
		$metodoDePago="DEPÓSITO EN CUENTA";
		
		$metodoDePago="01";
		
		if(($fAsientoNomina[15]=="")||($fAsientoNomina[15]=="-1"))
		{
			switch($fNomina[10])
			{
				case 2:
					
					$consulta="SELECT UPPER(cveSAT) FROM 710_metodoPagoComprobante WHERE idFormaPago=".$fDatosContratacion[7];
					$metodoDePago=$con->obtenerValor($consulta);
				break;
				default:
					if($fNomina[27]!="")
					{
						$consulta="SELECT cveSAT FROM 710_metodoPagoNomina WHERE idFormaPago=".$fNomina[27]	;
						$metodoDePago=$con->obtenerValor($consulta);
						
					}
					
				break;
			}
		}
		else
		{
			$consulta="SELECT cveSAT FROM 710_metodoPagoNomina WHERE idFormaPago=".$fAsientoNomina[15]	;
			$metodoDePago=$con->obtenerValor($consulta);
			
		}
		
		$objNomina["metodoDePago"]=$metodoDePago; //* Obligatorio
		
		$objNomina["idCertificado"]=$dCertificado[0];
		$objNomina["idSerie"]=$dCertificado[1];
		$objNomina["idClienteFactura"]=$idUsuario;
		
		$objNomina["noCertificado"]=$fCertificado[0]; //* Obligatorio
		$objNomina["certificado"]=$fCertificado[2]; //* Obligatorio
		$objNomina["serie"]=$fSerie[0]; 
		$objNomina["folio"]=$noFolio; 
		$objNomina["motivoDescuento"]="DEDUCCIONES NÓMINA"; 
		$objNomina["lugarExpedicion"]="ORIZABA, ".$estadoEmisor; //* Obligatorio
		if($objNomina["versionNomina"]=="1.2")
		{
			$objNomina["lugarExpedicion"]=94324;	//Version 1.2
		}
		$objNomina["tipoCambio"]="1"; //* Obligatorio
		
		$objNomina["formaPago"]="En una sola exhibición"; //* Obligatorio
		$objNomina["numCtaPago"]="NO APLICA";
		
		
		$objNomina["datosEmisor"]=obtenerSeccionDatosEmisor($dCertificado[2]);
		
		switch($fNomina[10])
		{
			case 2:
				$consulta="SELECT nombre,apPaterno,apMaterno,CONCAT(rfc1,rfc2,rfc3) AS rfc,curp,noSeguroSocial,numEmpleado,fechaIniRelLab FROM 693_empleadosNominaV2 WHERE idEmpleado=".$idUsuario;
			break;
			default:
				$consulta="SELECT Nom,Paterno,Materno,RFC,CURP,IMSS,idUsuario,(select fechaIngresoInstitucion from 801_adscripcion where idUsuario=i.idUsuario) as fechaIniRelLab FROM 802_identifica i WHERE idUsuario=".$idUsuario;
			break;
		}
		
		
		$fReceptor=$con->obtenerPrimeraFila($consulta);
		
		
		switch($fNomina[10])
		{
			case 2:
				$consulta="select calle,noExterior,colonia,(SELECT UPPER(municipio) FROM 821_municipios WHERE cveMunicipio=e.municipio) AS municipio,
						(SELECT UPPER(estado) FROM 820_estados WHERE cveEstado=e.estado) AS estado,'MÉXICO' AS pais,cp,noInterior,localidad FROM 693_empleadosNominaV2 e where idEmpleado=".$idUsuario;
			break;
			default:
				$consulta="SELECT Calle,Numero,Colonia,
					IF(d.Pais=146,(SELECT upper(municipio) FROM 821_municipios WHERE cveMunicipio=d.Ciudad) ,upper(Ciudad)) AS municipio,
					IF(Pais=146,(SELECT upper(estado) FROM 820_estados WHERE cveEstado=d.Estado),upper(Estado)) AS estado,
					(SELECT nombre FROM 238_paises WHERE idPais=d.Pais) AS pais,CP,'' as interior,'' as localidad FROM 803_direcciones d WHERE idUsuario=".$idUsuario." AND Tipo=0";		
			break;
		}
		$fDirReceptor=$con->obtenerPrimeraFila($consulta);
		
		
		$objDatos["rfc"]=normalizarRFC($fReceptor[3]);
		$objDatos["razonSocial"]=$fReceptor[0]." ".$fReceptor[1]." ".$fReceptor[2];

		$objDatos["domicilio"]["calle"]=trim($fDirReceptor[0]);
		$objDatos["domicilio"]["noExterior"]=trim($fDirReceptor[1]);
		$objDatos["domicilio"]["noInterior"]=$fDirReceptor[7];
		$objDatos["domicilio"]["colonia"]=trim($fDirReceptor[2]);
		$objDatos["domicilio"]["localidad"]=$fDirReceptor[8];
		$objDatos["domicilio"]["municipio"]=trim($fDirReceptor[3]);
		$objDatos["domicilio"]["estado"]=trim($fDirReceptor[4]);
		$objDatos["domicilio"]["pais"]=trim($fDirReceptor[5]);
		$objDatos["domicilio"]["codigoPostal"]=trim($fDirReceptor[6]);
		
		$objNomina["datosReceptor"]=$objDatos;
		
		$datosNomina["curp"]=$fReceptor[4]; //* Obligatorio
		
		
		$fechaPago=$fAsientoNomina[20];
		if($fechaPago=="")
		{
			$fechaPago=$fNomina[5];
			if($fechaPago=="")
				$fechaPago=$fNomina[4];
		}
		else
		{
			$fechaPago=date("Y-m-d",strtotime($fechaPago));
		}
		
		
		$datosNomina["fechaPago"]=$fechaPago; //* Obligatorio
		$datosNomina["fechaInicioPago"]=$fNomina[2]; //* Obligatorio
		$datosNomina["fechaFinPago"]=$fNomina[3]; //* Obligatorio
		$datosNomina["periodicidadPago"]=$periodicidad; //* Obligatorio
		$datosNomina["numDiasPagados"]=obtenerDiferenciaDias($datosNomina["fechaInicioPago"],$datosNomina["fechaFinPago"])+1; //* Obligatorio
		
		$numEmpleado=$idUsuario;
		switch($fNomina[10])
		{
			case 2:
				if($fReceptor[6]!="")
					$numEmpleado=$fReceptor[6];
			break;
			
		}
		
		$datosNomina["numEmpleado"]=str_pad($numEmpleado,15,"0",STR_PAD_LEFT); //* Obligatorio
		if($objNomina["versionNomina"]=="1.2")
			$datosNomina["numEmpleado"]=$numEmpleado;
		$datosNomina["numSS"]=normalizarSoloNumeros($fReceptor[5]);
		$datosNomina["clabe"]=$fDatosBancarios[1];
		$datosNomina["banco"]=$fDatosBancarios[0];
		$datosNomina["departamento"]=$departamento;
		
		$datosNomina["registroPatronal"]=$registroPatronal;
		$datosNomina["puesto"]=$fDatosPuesto[0];
		$consulta="SELECT e.cveSAT FROM 247_instituciones i,817_organigrama o,820_estados e 
					WHERE o.codigoUnidad='".$plantelEmpleado."' AND o.idOrganigrama=i.idOrganigrama AND e.cveEstado=i.estado";

		$datosNomina["entidadFederativa"]=$con->obtenerValor($consulta);
		
		$datosNomina["Sindicalizado"]="No";
		$datosNomina["riezgoPuesto"]=$fDatosPuesto[1];
		$datosNomina["tipoContrato"]=$tContrato;
		$datosNomina["tipoJornada"]="";//$tJornada;
		$datosNomina["tipoRegimen"]=$tRegimen; //* Obligatorio
		
		$arrPercepciones=array();
		$arrDeducciones=array();
		
		$sdi="";
		
		
		
		foreach($objDatosNomina->arrCalculosGlobales as $idCalculo=>$resto)
		{
			
			$consulta="SELECT categoriaCalculo,idEtiquetaAgrupadora,idEtiquetaAgrupadora FROM 662_calculosNomina WHERE idCalculo=".$idCalculo;
			$fCalculoNomina=$con->obtenerPrimeraFila($consulta);

			$idEtiquetaAgrupadora=$fCalculoNomina[1];
			
			if(($idEtiquetaAgrupadora!="")&&(isset($arrEtiquetas[$idEtiquetaAgrupadora])))
			{
				
				if(isset($resto["importeGravado"]))
				{
					$arrEtiquetas[$idEtiquetaAgrupadora]["importeGravado"]+=$resto["importeGravado"];
					$arrEtiquetas[$idEtiquetaAgrupadora]["importeExento"]+=$resto["importeExento"];
				}
				else
				{
					if($resto["tipoCalculo"]==1)//Deduccion
					{
						$arrEtiquetas[$idEtiquetaAgrupadora]["importeGravado"]+=$resto["valorCalculado"];
						$arrEtiquetas[$idEtiquetaAgrupadora]["importeExento"]+=0;	
					}
					else
					{
						$arrEtiquetas[$idEtiquetaAgrupadora]["importeGravado"]+=$resto["valorCalculado"];
						$arrEtiquetas[$idEtiquetaAgrupadora]["importeExento"]+=0;	
					}
				}
				array_push($arrEtiquetas[$idEtiquetaAgrupadora]["conceptos"],$objDatosNomina->arrCalculosGlobales[$idCalculo]);
				continue;	
			}
			
			$categoria=$fCalculoNomina[0];
			if($categoria=="")
				$categoria=-1;
				
			$objConceptoNomina=array();
			
			$objConceptoNomina["clave"]=$idCalculo;//str_pad($idCalculo,15,"0",STR_PAD_LEFT);	//* Obligatorio
			$objConceptoNomina["descripcion"]=$resto["nombreCalculo"];	//* Obligatorio
			
			
			if($obtenerNombreCalculoActualizado)
			{
				$consulta="SELECT etiquetaConcepto FROM 662_calculosNomina WHERE idCalculo=".$idCalculo;
				$etiquetaConcepto=$con->obtenerValor($consulta);
				if($etiquetaConcepto=="")
				{
					$consulta="SELECT nombreConsulta FROM 991_consultasSql WHERE idConsulta=".$resto["idConsulta"];
					$objConceptoNomina["descripcion"]=$con->obtenerValor($consulta);//"[".$idCalculo."] ".$con->obtenerValor($consulta);
				}
				else
				{
					$objConceptoNomina["descripcion"]=$etiquetaConcepto;
				}
			}
			
			if($resto["tipoCalculo"]==1)  //Deduccion
			{
				
				$consulta="SELECT clave FROM 682_tiposDeduccionSAT where idTipoDeduccion=".$categoria;
				$tConcepto=$con->obtenerValor($consulta);
				if($tConcepto!="")
					$tConcepto=str_pad($tConcepto,3,"0",STR_PAD_LEFT);
	
				$objConceptoNomina["tipoDeduccion"]=$tConcepto;//tipoDeduccion   //* Obligatorio
				$objConceptoNomina["importeGravado"]=$resto["valorCalculado"];	//* Obligatorio
				$objConceptoNomina["importeExento"]=0;	//* Obligatorio
				
				if(isset($resto["importeGravado"]))
				{
					$objConceptoNomina["importeGravado"]=$resto["importeGravado"];	//* Obligatorio
					$objConceptoNomina["importeExento"]=$resto["importeExento"];	//* Obligatorio
				}
				
				
				if($categoria!=2)
				{
					$descuento+=($objConceptoNomina["importeGravado"]+$objConceptoNomina["importeExento"]);
				}
				else
				{
					$retenciones+=($objConceptoNomina["importeGravado"]+$objConceptoNomina["importeExento"]);
				}
				
				
				if($resto["valorCalculado"]>0)
					array_push($arrDeducciones,$objConceptoNomina);
			}
			else
				if($resto["tipoCalculo"]==2)
				{
					
					$consulta="SELECT clave FROM 681_tiposPercepcionSAT where idTipoPercepcion=".$categoria;
					
					$tConcepto=$con->obtenerValor($consulta);
					if($tConcepto!="")
						$tConcepto=str_pad($tConcepto,3,"0",STR_PAD_LEFT);
					$objConceptoNomina["tipoPercepcion"]=$tConcepto;//tipoDeduccion   //* Obligatorio
					$objConceptoNomina["importeGravado"]=$resto["valorCalculado"];	//* Obligatorio
					$objConceptoNomina["importeExento"]=0;	//* Obligatorio
					
					if(isset($resto["importeGravado"]))
					{
						$objConceptoNomina["importeGravado"]=$resto["importeGravado"];	//* Obligatorio
						$objConceptoNomina["importeExento"]=$resto["importeExento"];	//* Obligatorio
					}
					if($resto["valorCalculado"]>0)
						array_push($arrPercepciones,$objConceptoNomina);
				}
				else
				{
					switch($resto["idCategoriaSAT"])
					{
						case 1:
						break;
						case 2:	//Salario Diario Integrado
							$sdi=$resto["valorCalculado"];
						break;
						case 3:	//Días trabajados
							$datosNomina["numDiasPagados"]=$resto["valorCalculado"];	
						break;
						case 4:	//RFC
							$objNomina["datosReceptor"]["rfc"]=$resto["valorCalculado"];	
						break;
						case 5:	//CURP
							$datosNomina["curp"]=$resto["valorCalculado"];	
						break;
						case 6:	//No. Seguro Social
							$datosNomina["numSS"]=$resto["valorCalculado"];	
						break;
						case 7:	//Nombre del Receptor
							$objNomina["datosReceptor"]["razonSocial"]=$resto["valorCalculado"];	
						break;
						case 8:
							$datosNomina["puesto"]=$resto["valorCalculado"];	
						break;
						case 9:
							$datosNomina["riezgoPuesto"]=$resto["valorCalculado"];	
						break;
						case 10:
							$datosNomina["tipoRegimen"]=$resto["valorCalculado"];	
						break;
						case 11:
							$datosNomina["departamento"]=$resto["valorCalculado"];	
						break;

							
					}
					
					
						
				}
		}
		
		
		if(sizeof($arrEtiquetas)>0)
		{
			foreach($arrEtiquetas as $idEtiquetaAgrupadora=>$oEtiquetaAgrupadora)
			{
					
				$objConceptoNomina=array();
				
				$objConceptoNomina["clave"]=$oEtiquetaAgrupadora["clave"];//str_pad($oEtiquetaAgrupadora["clave"],15,"0",STR_PAD_LEFT);	//* Obligatorio
				$objConceptoNomina["descripcion"]=$oEtiquetaAgrupadora["descripcion"];//"[".$oEtiquetaAgrupadora["clave"]."] ".$oEtiquetaAgrupadora["descripcion"];	//* Obligatorio
				
				
				
				if($oEtiquetaAgrupadora["idCategoria"]==1)  //Deduccion
				{
					$consulta="SELECT clave FROM 682_tiposDeduccionSAT where idTipoDeduccion=".$oEtiquetaAgrupadora["idCategoriaSAT"];
					$tConcepto=$con->obtenerValor($consulta);
					
					$tConcepto=str_pad($tConcepto,3,"0",STR_PAD_LEFT);
					$objConceptoNomina["tipoDeduccion"]=$tConcepto;//tipoDeduccion   //* Obligatorio
					$objConceptoNomina["importeGravado"]=$oEtiquetaAgrupadora["importeGravado"];	//* Obligatorio
					$objConceptoNomina["importeExento"]=$oEtiquetaAgrupadora["importeExento"];	//* Obligatorio
					$descuento+=$oEtiquetaAgrupadora["importeGravado"]+$oEtiquetaAgrupadora["importeExento"];
					if(($oEtiquetaAgrupadora["importeGravado"]+$oEtiquetaAgrupadora["importeExento"])>0)
						array_push($arrDeducciones,$objConceptoNomina);
				}
				else
					if($oEtiquetaAgrupadora["idCategoria"]==2)
					{
						$consulta="SELECT clave FROM 681_tiposPercepcionSAT where idTipoPercepcion=".$oEtiquetaAgrupadora["idCategoriaSAT"];
						$tConcepto=$con->obtenerValor($consulta);
						
						$tConcepto=str_pad($tConcepto,3,"0",STR_PAD_LEFT);
						$objConceptoNomina["tipoPercepcion"]=$tConcepto;//tipoDeduccion   //* Obligatorio
						$objConceptoNomina["importeGravado"]=$oEtiquetaAgrupadora["importeGravado"];	//* Obligatorio
						$objConceptoNomina["importeExento"]=$oEtiquetaAgrupadora["importeExento"];	//* Obligatorio
						
						if(($oEtiquetaAgrupadora["importeGravado"]+$oEtiquetaAgrupadora["importeExento"])>0)
							array_push($arrPercepciones,$objConceptoNomina);
						
						
						
					}
					
			}
		}
		
		
		
		$datosNomina["arrPercepciones"]=$arrPercepciones;
		$datosNomina["arrDeducciones"]=$arrDeducciones;
		if($fReceptor[7]=="0000-00-00")
			$fReceptor[7]="";
		$datosNomina["fechaInicioRelLaboral"]=$fReceptor[7];
		$datosNomina["antiguedad"]="";
		$datosNomina["salarioBaseCotApor"]="";
		
		$datosNomina["sdi"]=$sdi;
		
		$objNomina["datosNomina"]=$datosNomina;
		
		
		$objNomina["subtotal"]=$fDatosNomina[6]; //* Obligatorio
		$objNomina["descuento"]=$descuento;
		$objNomina["total"]=$objNomina["subtotal"]-$descuento-$retenciones; //* Obligatorio
		
		$objNomina["impuestos"]=array();
		$objNomina["impuestos"]["retenciones"]=array();
		$objNomina["impuestos"]["traslados"]=array();
		$oImpuestoRetencion=array();
		$oImpuestoRetencion["impuesto"]="ISR";//*  ISR | IVA
		$oImpuestoRetencion["importe"]=$retenciones;//*
		array_push($objNomina["impuestos"]["retenciones"],$oImpuestoRetencion);
		
		return ($objNomina);
	}
	
	function timbrarModuloNomina($idNomina)
	{
		global $con;
		$cT=new cFDIFinkok();
		$consulta="SELECT idAsientoNomina FROM 671_asientosCalculosNomina WHERE idNomina=".$idNomina." and idComprobante is null";
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$c=new cNominaCFDI();
			$oNomina=generarXMLModuloNomina($fila[0]);
			$c->setObjNomina($oNomina);
			$XML=$c->generarXML();
			$idFactura=$c->registrarXML(1,$fila[0]);
			$c->generarSelloDigital();
			$cT->timbrarComprobante($idFactura);
			
				
		}
		return true;
	}
	
	function cancelarTimbradoNomina($idNomina)
	{
		global $con;
		$cT=new cFDIFinkok();
		
		$consulta="SELECT comentarios FROM 676_historialNomina WHERE idNomina=".$idNomina." ORDER BY idHistorialNomina DESC";
		$motivo=$con->obtenerValor($consulta);
		
		$consulta="SELECT idComprobante FROM 671_asientosCalculosNomina WHERE idNomina=".$idNomina." and idComprobante is not null";

		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$cT->cancelarComprobante($fila[0],$motivo);
		}
		return true;
	}
	
?>