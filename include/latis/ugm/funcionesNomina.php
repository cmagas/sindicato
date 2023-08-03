<?php
	include_once("latis/conexionBD.php");
	include_once("latis/cfdi/cNomina.php");
	include_once("latis/cfdi/funciones.php");

	
	
	
	
	function generarXMLNominaPrimaria($idRegistro)
	{
		global $con;
		
		
		$consulta="select * from _1012_gridNomina where id__1012_gridNomina=".$idRegistro;
		$fRegistroNomina=$con->obtenerPrimeraFila($consulta);
		
		
		$consulta="select * from _1012_tablaDinamica where id__1012_tablaDinamica=".$fRegistroNomina[1];
		
		$fNomina=$con->obtenerPrimeraFila($consulta);
		
		$consulta="SELECT contenido FROM 902_opcionesFormulario WHERE idGrupoElemento=8594 AND valor=".$fNomina[16];
		
		$periodicidad=$con->obtenerValor($consulta);
		$codigoUnidad=$fNomina[17];
		
		$idUsuario=$fRegistroNomina[2];
		
		$consulta="SELECT codigoUnidad,idCuentaDeposito,tipoContratacion,idTipoJornada,idTipoContrato,registroPatronal,cod_Puesto FROM 801_adscripcion WHERE idUsuario=".$idUsuario;

		$fDatosContratacion=$con->obtenerPrimeraFila($consulta);
		if($fDatosContratacion[1]=="")
			$fDatosContratacion[1]=-1;
		if($fDatosContratacion[2]=="")
			$fDatosContratacion[2]=-1;
		if($fDatosContratacion[6]=="")
			$fDatosContratacion[6]=-1;
		
		$tJornada="";
		$tContrato="";
		$tRegimen="";
		
		$consulta="SELECT r.clave FROM 690_tiposContratacionEmpresa t,683_regimenContratacionSAT r WHERE idTipoContratacion=".$fDatosContratacion[2]." AND r.idRegimen=t.idRegimenContratacion";

		$tRegimen=$con->obtenerValor($consulta);
		
		if(($fDatosContratacion[3]!="")&&($fDatosContratacion[3]!="0"))
		{
			$consulta="SELECT tipoJornada FROM 689_tipoJornadas WHERE idTipoJornada=".$fDatosContratacion[3];
			$tJornada=$con->obtenerValor($consulta);
		
		}
		
		if(($fDatosContratacion[4]!="")&&($fDatosContratacion[4]!="0"))
		{
			$consulta="SELECT tipoContrato FROM 686_tiposContrato WHERE idTipoContrato=".$fDatosContratacion[4];
			$tContrato=$con->obtenerValor($consulta);
		}
		
		if($fDatosContratacion[6]=="")
			$fDatosContratacion[6]=-1;
		$consulta="SELECT puesto,r.clave FROM 819_puestosOrganigrama p,684_riesgoPuestoSAT r WHERE idPuesto=".$fDatosContratacion[6]." AND r.idRiesgo=p.nivelRiesgo";
		$fDatosPuesto=$con->obtenerPrimeraFila($consulta);	
		if(!$fDatosPuesto)
		{
			$fDatosPuesto[0]="";
			$fDatosPuesto[1]="";
		}
		$consulta="SELECT b.claveSAT,cu.clabe,cu.cuenta FROM 823_cuentasUsuario cu,6000_bancos b  
						WHERE b.idBanco=cu.idBanco AND cu.idCuentaUsuario=".$fDatosContratacion[1];
			
		$fDatosBancarios=$con->obtenerPrimeraFila($consulta);	
		$consulta="select unidad from 817_organigrama where codigoUnidad='".$fNomina[17]."'";
		$departamento=$con->obtenerValor($consulta);	
		

		
		$consulta="SELECT idCertificado,idSerie,idEmpresa,idRegistroPatronal FROM 717_relacionInstitucionSerieNomina WHERE codigoUnidad='".$codigoUnidad."'";
		
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


		$consulta="SELECT UPPER(municipio) FROM 821_municipios WHERE cveMunicipio='".$fEmpresa[14]."'";
		$municipioEmisor=$con->obtenerValor($consulta);
		$consulta="SELECT UPPER(estado) FROM 820_estados WHERE cveEstado='".$fEmpresa[13]."'";
		$estadoEmisor=$con->obtenerValor($consulta);

		$consulta="SELECT noCertificado,claveArchivoKey,certificadoDigital FROM 687_certificadosSelloDigital WHERE idCertificado=".$dCertificado[0];
		$fCertificado=$con->obtenerPrimeraFila($consulta);
		$consulta="SELECT serie FROM 688_seriesCertificados WHERE idSerieCertificado=".$dCertificado[1];
		$fSerie=$con->obtenerPrimeraFila($consulta);
		$nom=new cNominaCFDI();
		$objNomina=$nom->generarEstructuraLlenadoXML();
		
		$noFolio=obtenerFolioCertificado($dCertificado[1]);
		
		
		$descuento=0;//Pendiente
		$retenciones=0;//Pendiente
		
		$objNomina["idCertificado"]=$dCertificado[0];
		$objNomina["idSerie"]=$dCertificado[1];
		$objNomina["idClienteFactura"]=$idUsuario;
		$objNomina["sello"]="";//* Obligatorio
		$datosNomina["numDiasPagados"]=$fNomina[15]; //* Obligatorio

		$objNomina["noCertificado"]=$fCertificado[0]; //* Obligatorio
		$objNomina["certificado"]=$fCertificado[2]; //* Obligatorio
		$objNomina["serie"]=$fSerie[0]; 
		$objNomina["folio"]=$noFolio; 
		$objNomina["motivoDescuento"]="DEDUCCIONES NÓMINA"; 
		$objNomina["lugarExpedicion"]=$estadoEmisor; //* Obligatorio
		$objNomina["tipoCambio"]="1"; //* Obligatorio
		$consulta="SELECT formaPago FROM 710_metodoPagoNomina WHERE idFormaPago=".$fNomina[18]	;
		$metodoDePago=$con->obtenerValor($consulta);
		$objNomina["metodoDePago"]=$metodoDePago; //* Obligatorio
		$objNomina["formaPago"]="PAGO EN UNA SOLA EXHIBICIÓN"; //* Obligatorio
		
		
		
		$objNomina["numCtaPago"]="NO APLICA";
		
		
		$objNomina["datosEmisor"]=obtenerSeccionDatosEmisor($dCertificado[2]);
		$consulta="SELECT Nom,Paterno,Materno,RFC,CURP,IMSS FROM 802_identifica WHERE idUsuario=".$idUsuario;
		$fReceptor=$con->obtenerPrimeraFila($consulta);

		$consulta="SELECT Calle,Numero,Colonia,
					IF(d.Pais=146,(SELECT upper(municipio) FROM 821_municipios WHERE cveMunicipio=d.Ciudad) ,upper(Ciudad)) AS municipio,
					IF(Pais=146,(SELECT upper(estado) FROM 820_estados WHERE cveEstado=d.Estado),upper(Estado)) AS estado,
					(SELECT nombre FROM 238_paises WHERE idPais=d.Pais) AS pais,CP FROM 803_direcciones d WHERE idUsuario=".$idUsuario." AND Tipo=0";		
		$fDirReceptor=$con->obtenerPrimeraFila($consulta);
		
		$objDatos["rfc"]=$fRegistroNomina[4];
			
			
		$objDatos["razonSocial"]=$fReceptor[0]." ".$fReceptor[1]." ".$fReceptor[2];

		$objDatos["domicilio"]["calle"]=trim($fDirReceptor[0]);
		$objDatos["domicilio"]["noExterior"]=trim($fDirReceptor[1]);
		$objDatos["domicilio"]["noInterior"]="";
		$objDatos["domicilio"]["colonia"]=trim($fDirReceptor[2]);
		$objDatos["domicilio"]["localidad"]="";
		$objDatos["domicilio"]["municipio"]=trim($fDirReceptor[3]);
		$objDatos["domicilio"]["estado"]=trim($fDirReceptor[4]);
		$objDatos["domicilio"]["pais"]=trim($fDirReceptor[5]);
		$objDatos["domicilio"]["codigoPostal"]=trim($fDirReceptor[6]);
		
		$objNomina["datosReceptor"]=$objDatos;
		
		
		$datosNomina["curp"]=$fRegistroNomina[3];	
		
		
		$datosNomina["fechaPago"]=$fNomina[13]; //* Obligatorio
		$datosNomina["fechaInicioPago"]=$fNomina[11]; //* Obligatorio
		$datosNomina["fechaFinPago"]=$fNomina[12]; //* Obligatorio
		$datosNomina["periodicidadPago"]=$periodicidad; //* Obligatorio
		
		$datosNomina["numEmpleado"]=str_pad($idUsuario,15,"0",STR_PAD_LEFT); //* Obligatorio
		$datosNomina["numSS"]=$fRegistroNomina[5];
		$datosNomina["clabe"]=$fDatosBancarios[1];
		$datosNomina["banco"]=$fDatosBancarios[0];
		$datosNomina["departamento"]=$departamento;
		$datosNomina["registroPatronal"]=$registroPatronal;
		$datosNomina["puesto"]=$fDatosPuesto[0];
		$datosNomina["riezgoPuesto"]=$fDatosPuesto[1];
		$datosNomina["tipoContrato"]=$tContrato;
		$datosNomina["tipoJornada"]=$tJornada;
		$datosNomina["tipoRegimen"]=$tRegimen; //* Obligatorio
		
		
		
		$arrPercepciones=array();
		$arrDeducciones=array();
		
		
		
		
			
		$objConceptoNomina=array();
		
		//Percepciones
		$categoria=1;
		
		
		$consulta="SELECT clave FROM 681_tiposPercepcionSAT where idTipoPercepcion=".$categoria;
		$tConcepto=$con->obtenerValor($consulta);
		
		if($tConcepto!="")
			$tConcepto=str_pad($tConcepto,3,"0",STR_PAD_LEFT);
			
		$objConceptoNomina["clave"]="000000000000305";	
		$objConceptoNomina["descripcion"]="[305] Sueldos Horas";
		$objConceptoNomina["tipoPercepcion"]=$tConcepto;//tipoDeduccion   //* Obligatorio
		$objConceptoNomina["importeGravado"]=$fRegistroNomina[7];	//* Obligatorio
		$objConceptoNomina["importeExento"]=0;	//* Obligatorio
		
		
		array_push($arrPercepciones,$objConceptoNomina);
		
		//Deducciones
		$categoria=2;
		$objConceptoNomina=array();
		$consulta="SELECT clave FROM 682_tiposDeduccionSAT where idTipoDeduccion=".$categoria;
		$tConcepto=$con->obtenerValor($consulta);
		if($tConcepto!="")
			$tConcepto=str_pad($tConcepto,3,"0",STR_PAD_LEFT);
		$objConceptoNomina["clave"]="000000000000310";	
		$objConceptoNomina["descripcion"]="[310] I.S.R.";
		$objConceptoNomina["tipoDeduccion"]=$tConcepto;//tipoDeduccion   //* Obligatorio
		$objConceptoNomina["importeGravado"]=$fRegistroNomina[8];	//* Obligatorio
		$objConceptoNomina["importeExento"]=0;	//* Obligatorio
		$retenciones=$fRegistroNomina[8];
		if(($objConceptoNomina["importeGravado"]+$objConceptoNomina["importeExento"])>0)		
			array_push($arrDeducciones,$objConceptoNomina);
		
		$oImpuestoRetencion=array();
		$oImpuestoRetencion["impuesto"]="ISR";//*  ISR | IVA
		$oImpuestoRetencion["importe"]=$fRegistroNomina[8];
		
		array_push($objNomina["impuestos"]["retenciones"],$oImpuestoRetencion);
		
		
		$categoria=1;
		$objConceptoNomina=array();
		$consulta="SELECT clave FROM 682_tiposDeduccionSAT where idTipoDeduccion=".$categoria;
		$tConcepto=$con->obtenerValor($consulta);
		if($tConcepto!="")
			$tConcepto=str_pad($tConcepto,3,"0",STR_PAD_LEFT);
		$objConceptoNomina["clave"]="000000000000318";	
		$objConceptoNomina["descripcion"]="[318] I.M.S.S";
		$objConceptoNomina["tipoDeduccion"]=$tConcepto;//tipoDeduccion   //* Obligatorio
		$objConceptoNomina["importeGravado"]=$fRegistroNomina[9];	//* Obligatorio
		$objConceptoNomina["importeExento"]=0;	//* Obligatorio
		
		$descuento+=$fRegistroNomina[9];
		
		if(($objConceptoNomina["importeGravado"]+$objConceptoNomina["importeExento"])>0)						
			array_push($arrDeducciones,$objConceptoNomina);
		
		$categoria=5;
		$objConceptoNomina=array();
		$consulta="SELECT clave FROM 682_tiposDeduccionSAT where idTipoDeduccion=".$categoria;
		$tConcepto=$con->obtenerValor($consulta);
		if($tConcepto!="")
			$tConcepto=str_pad($tConcepto,3,"0",STR_PAD_LEFT);
		$objConceptoNomina["clave"]="000000000000327";	
		$objConceptoNomina["descripcion"]="[327] INFONAVIT";
		$objConceptoNomina["tipoDeduccion"]=$tConcepto;//tipoDeduccion   //* Obligatorio
		$objConceptoNomina["importeGravado"]=$fRegistroNomina[10];	//* Obligatorio
		$objConceptoNomina["importeExento"]=0;	//* Obligatorio
		$descuento+=$fRegistroNomina[10];
		if(($objConceptoNomina["importeGravado"]+$objConceptoNomina["importeExento"])>0)						
			array_push($arrDeducciones,$objConceptoNomina);
		
		
		
		
		$datosNomina["arrPercepciones"]=$arrPercepciones;
		$datosNomina["arrDeducciones"]=$arrDeducciones;
		
		$datosNomina["fechaInicioRelLaboral"]="";
		$datosNomina["antiguedad"]="";
		$datosNomina["salarioBaseCotApor"]="";
		$datosNomina["sdi"]=$fRegistroNomina[11];
		
		$objNomina["datosNomina"]=$datosNomina;
		
		
		$objNomina["subtotal"]=$fRegistroNomina[7]; //* Obligatorio
		$objNomina["descuento"]=$descuento;

		$objNomina["total"]=$fRegistroNomina[12]; //* Obligatorio
		
		return ($objNomina);
	}
	
	function descomponerArchivoNomina($idFormulario,$idRegistro)
	{
		global $con;
		global $baseDir;
		$estructura["idReferencia"]="";
		$estructura["numEmpleado"]="";
		$estructura["curp"]="";
		$estructura["rfc"]="";
		$estructura["numSeguridadSocial"]="";
		$estructura["nombre"]="";
		$estructura["sueldo"]="";
		$estructura["isr"]="";
		$estructura["imss"]="";
		$estructura["infonavit"]="";
		$estructura["sdi"]="";
		$estructura["sueldoNeto"]="";
		$nombreTabla="_1012_gridNomina";
		$consulta="delete from _1012_gridNomina where idReferencia=".$idRegistro;
		if($con->ejecutarConsulta($consulta))
		{
			$consulta="select archivoNomina from _".$idFormulario."_tablaDinamica where id__".$idFormulario."_tablaDinamica=".$idRegistro;
			$nombreArchivo=$con->obtenerValor($consulta);
			
			
			$f=fopen($baseDir."/documentosUsr/archivo_".$nombreArchivo,"r");	

			while ( ($linea = fgets($f)) !== false) 
			{
				if(trim($linea)!="")
				{
					$linea=$idRegistro.",".trim($linea);
					$arrDatosNomina=explode(",",$linea);
					array_push($arrDatosNomina,($arrDatosNomina[6]-$arrDatosNomina[7]-$arrDatosNomina[8]-$arrDatosNomina[9]));
					escribirArregloEstructura($nombreTabla,$estructura,$arrDatosNomina);
				}
			}
			fclose($f);
		}
	}
	
	function escribirArregloEstructura($tabla,$estructura,$arreglo)
	{
		global $con;
		$listaCampos="";
		$listaValores="";
		$ct=0;
		foreach($estructura as $campo=>$resto)	
		{
			if($listaCampos=="")
				$listaCampos=$campo;
			else
				$listaCampos.=",".$campo;
				
			if($listaValores=="")
				$listaValores="'".removerComillasLimite(trim($arreglo[$ct]))."'";
			else
				$listaValores.=",'".removerComillasLimite(trim($arreglo[$ct]))."'";
					
			$ct++;
		}

		$consulta="insert into ".$tabla."(".$listaCampos.") values(".$listaValores.")";

		return $con->ejecutarConsulta($consulta);
	}
	
	

	
	//generarXMLNominaPrimaria(32);
	//generarXMLNomina(722,853,0,0)
?>