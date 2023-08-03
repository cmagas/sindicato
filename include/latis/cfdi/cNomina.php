<?php include_once("latis/cfdi/cComprobante.php");

class cNominaCFDI extends cComprobante
{

	var $idAsientoNomina;
	var $idComprobante;
	var $sello;
	var $formaPago;
	var $noCertificado;
	var $certificado;
	var $version;
	var $serie;
	var $folio;
	var $fechaActual;
	var $tipoDeComprobante;
	var $metodoDePago;
	var $lugarExpedicion;
	var $moneda;
	var $descuento;
	var $subtotal;
	var $motivoDescuento;
	var $tipoCambio;
	var $total;
	var $emisor;
	var $receptor;
	var $datosNomina;
	var $versionNomina;
	var $XML;
	var $condicionesDePago;
	var $numCtaPago;
	var $folioFiscalOrig;
	var $serieFolioFiscalOrig;
	var $fechaFolioFiscalOrig;
	var $montoFolioFiscalOrig;
	var $tipoNomina;
	var $idCertificado;
	var $idSerie;
	var $idClienteFactura;
	var $impuestos;
	var $objComprobante;

	function cNominaCFDI($version)
	{
		$this->version=$version;
		$this->fechaActual=date("Y-m-d\TH:i:s");
		$this->tipoDeComprobante="egreso";
		$this->moneda="PESOS";
		$this->XML="";
	}

	function setObjNomina($obj)
	{
		$this->objComprobante=$obj;
		$this->idAsientoNomina=$obj["idAsientoNomina"];
		$this->versionNomina=$obj["versionNomina"];
		$this->sello=$obj["sello"];
		$this->formaPago=$obj["formaPago"];
		$this->noCertificado=$obj["noCertificado"];
		$this->certificado=$obj["certificado"];
		$this->serie=$obj["serie"];
		$this->folio=$obj["folio"];
		$this->metodoDePago=$obj["metodoDePago"];
		$this->lugarExpedicion=$obj["lugarExpedicion"];
		$this->descuento=$obj["descuento"];
		$this->subtotal=$obj["subtotal"];
		$this->motivoDescuento=$obj["motivoDescuento"];
		$this->tipoCambio=$obj["tipoCambio"];
		$this->total=$obj["total"];	
		$this->emisor=$obj["datosEmisor"];	
		$this->receptor=$obj["datosReceptor"];	
		$this->datosNomina=$obj["datosNomina"];
		$this->condicionesDePago=$obj["condicionesDePago"];
		$this->numCtaPago=$obj["numCtaPago"];  
		$this->folioFiscalOrig=$obj["folioFiscalOrig"];
		$this->serieFolioFiscalOrig=$obj["serieFolioFiscalOrig"];
		$this->fechaFolioFiscalOrig=$obj["fechaFolioFiscalOrig"];
		$this->montoFolioFiscalOrig=$obj["montoFolioFiscalOrig"];
		$this->idCertificado=$obj["idCertificado"];
		$this->idSerie=$obj["idSerie"];
		$this->idClienteFactura=$obj["idClienteFactura"];
		$this->impuestos=array();
		if(isset($obj["impuestos"]))
			$this->impuestos=$obj["impuestos"];
		$this->tipoNomina="O";
		if(isset($obj["tipoNomina"]))	
			$this->tipoNomina=$obj["tipoNomina"];
	}

	function generarEstructuraLlenadoXML()
	{
		$obj["sello"]="";//* Obligatorio
		$obj["formaPago"]=""; //* Obligatorio
		$obj["noCertificado"]=""; //* Obligatorio
		$obj["certificado"]=""; //* Obligatorio
		$obj["serie"]=""; 
		$obj["folio"]=""; 
		$obj["metodoDePago"]=""; //* Obligatorio
		$obj["lugarExpedicion"]=""; //* Obligatorio
		$obj["descuento"]="";
		$obj["subtotal"]=""; //* Obligatorio
		$obj["motivoDescuento"]=""; 
		$obj["tipoCambio"]=""; //* Obligatorio
		$obj["total"]=""; //* Obligatorio
		$obj["version"]="";
		$obj["fechaComprobante"]="";
		$obj["condicionesDePago"]="";
		$obj["tipoDeComprobante"]="";
		$obj["numCtaPago"]="";  
		$obj["folioFiscalOrig"]="";
		$obj["serieFolioFiscalOrig"]="";
		$obj["fechaFolioFiscalOrig"]="";
		$obj["montoFolioFiscalOrig"]="";
		$obj["idCertificado"]=""; //* Obligatorio
		$obj["idSerie"]=""; //* Obligatorio
		$obj["idClienteFactura"]=""; //* Obligatorio
		$datosEmisor["rfc"]="";  //* Obligatorio
		$datosEmisor["razonSocial"]="";
		$datosEmisor["regimenFiscal"]=""; //* Obligatorio
		$datosEmisor["domicilio"]["calle"]=""; //* Obligatorio
		$datosEmisor["domicilio"]["noExterior"]="";
		$datosEmisor["domicilio"]["noInterior"]="";
		$datosEmisor["domicilio"]["colonia"]="";
		$datosEmisor["domicilio"]["localidad"]="";
		$datosEmisor["domicilio"]["municipio"]=""; //* Obligatorio
		$datosEmisor["domicilio"]["estado"]=""; //* Obligatorio
		$datosEmisor["domicilio"]["pais"]=""; //* Obligatorio
		$datosEmisor["domicilio"]["codigoPostal"]=""; //* Obligatorio
		$obj["datosEmisor"]=$datosEmisor;
		$datosReceptor["rfc"]=""; //* Obligatorio
		$datosReceptor["razonSocial"]="";
		$datosReceptor["domicilio"]["calle"]="";
		$datosReceptor["domicilio"]["noExterior"]="";
		$datosReceptor["domicilio"]["noInterior"]="";
		$datosReceptor["domicilio"]["colonia"]="";
		$datosReceptor["domicilio"]["localidad"]="";
		$datosReceptor["domicilio"]["municipio"]="";
		$datosReceptor["domicilio"]["estado"]="";
		$datosReceptor["domicilio"]["pais"]=""; //* Obligatorio
		$datosReceptor["domicilio"]["codigoPostal"]="";
		$obj["datosReceptor"]=$datosReceptor;	
		$datosNomina["curp"]=""; //* Obligatorio
		$datosNomina["tipoRegimen"]=""; //* Obligatorio
		$datosNomina["fechaPago"]=""; //* Obligatorio
		$datosNomina["numEmpleado"]=""; //* Obligatorio
		$datosNomina["fechaInicioPago"]=""; //* Obligatorio
		$datosNomina["fechaFinPago"]=""; //* Obligatorio
		$datosNomina["numDiasPagados"]=""; //* Obligatorio
		$datosNomina["periodicidadPago"]=""; //* Obligatorio
		$datosNomina["registroPatronal"]="";
		$datosNomina["numSS"]="";
		$datosNomina["departamento"]="";
		$datosNomina["clabe"]="";
		$datosNomina["banco"]="";
		$datosNomina["fechaInicioRelLaboral"]="";
		$datosNomina["puesto"]="";
		$datosNomina["tipoContrato"]="";
		$datosNomina["tipoJornada"]="";
		$datosNomina["riezgoPuesto"]="";
		$datosNomina["antiguedad"]="";
		$datosNomina["salarioBaseCotApor"]="";
		$datosNomina["sdi"]="";
		$datosNomina["arrPercepciones"]=array();
		$datosNomina["arrDeducciones"]=array();
		$datosNomina["arrIncapacidades"]=array();
		$datosNomina["arrHorasExtra"]=array();
		$obj["datosNomina"]=$datosNomina;
		$obj["impuestos"]=array();
		$obj["impuestos"]["retenciones"]=array();
		$obj["impuestos"]["traslados"]=array();
		return $obj;

	}

	function generarXML()
	{
		switch($this->versionNomina)
		{
			case '1.1':
				return $this->generarXMLV1_1();
			break;
			case '1.2':	
				if($this->version==3.3)
					return $this->generarXMLV1_2_CFDI3_3();
				else
					return $this->generarXMLV1_2();
			break;
		}
		return "";
	}


	function generarXMLV1_1()
	{
		global $con;
		$condicionesDePago=$this->formatearValorElemento("condicionesDePago",$this->condicionesDePago);
		$numCtaPago=$this->formatearValorElemento("NumCtaPago",$this->numCtaPago);
		$folioFiscalOrig=$this->formatearValorElemento("FolioFiscalOrig",$this->folioFiscalOrig);
		$serieFolioFiscalOrig=$this->formatearValorElemento("SerieFolioFiscalOrig",$this->serieFolioFiscalOrig);
		$fechaFolioFiscalOrig=$this->formatearValorElemento("FechaFolioFiscalOrig",$this->fechaFolioFiscalOrig);

		if($this->montoFolioFiscalOrig!="")

			$this->montoFolioFiscalOrig=$this->formatearValorMonetarioV2($this->montoFolioFiscalOrig);

		

		$montoFolioFiscalOrig=$this->formatearValorElemento("MontoFolioFiscalOrig",$this->montoFolioFiscalOrig);

		

		$atributosOpcionalesCFDI=$condicionesDePago.$numCtaPago.$folioFiscalOrig.$serieFolioFiscalOrig.$fechaFolioFiscalOrig.$montoFolioFiscalOrig;

		

		$noExterior=$this->formatearValorElemento("noExterior",$this->emisor["domicilio"]["noExterior"]);

		$noInterior=$this->formatearValorElemento("noInterior",$this->emisor["domicilio"]["noInterior"]);

		$colonia=$this->formatearValorElemento("colonia",$this->emisor["domicilio"]["colonia"]);

		$localidad=$this->formatearValorElemento("localidad",$this->emisor["domicilio"]["localidad"]);

			

		

		$calleRec=$this->formatearValorElemento("calle",$this->receptor["domicilio"]["calle"]);	

		$noExteriorRec=$this->formatearValorElemento("noExterior",$this->receptor["domicilio"]["noExterior"]);	

		$noInteriorRec=$this->formatearValorElemento("noInterior",$this->receptor["domicilio"]["noInterior"]);	

		$coloniaRec=$this->formatearValorElemento("colonia",$this->receptor["domicilio"]["colonia"]);	

		$localidadRec=$this->formatearValorElemento("localidad",$this->receptor["domicilio"]["localidad"]);	

		$municipioRec=$this->formatearValorElemento("municipio",$this->receptor["domicilio"]["municipio"]);	

		$estadoRec=$this->formatearValorElemento("estado",$this->receptor["domicilio"]["estado"]);	

		$codigoPostalRec=$this->formatearValorElemento("codigoPostal",$this->receptor["domicilio"]["codigoPostal"]);	

		

		

		$domicilio=$calleRec.$noExteriorRec.$noInteriorRec.$coloniaRec.$localidadRec.$municipioRec.$estadoRec.$codigoPostalRec;

		

		$registroPatronal=$this->formatearValorElemento("RegistroPatronal",$this->datosNomina["registroPatronal"]);

		$numSS="";//$this->formatearValorElemento("NumSeguridadSocial",$this->datosNomina["numSS"]);

		$departamento=substr($this->formatearValorElemento("Departamento",$this->datosNomina["departamento"]),0,100);

		$clabe=$this->formatearValorElemento("CLABE",$this->datosNomina["clabe"]);

		$banco=$this->formatearValorElemento("Banco",$this->datosNomina["banco"]);

		$fechaInicioRelLaboral="";//$this->formatearValorElemento("FechaInicioRelLaboral",$this->datosNomina["fechaInicioRelLaboral"]);

		$antiguedad="";//$this->formatearValorElemento("Antiguedad",$this->datosNomina["antiguedad"]);

		$puesto=$this->formatearValorElemento("Puesto",$this->datosNomina["puesto"]);

		$tipoContrato=$this->formatearValorElemento("TipoContrato",$this->datosNomina["tipoContrato"]);

		$tipoJornada=$this->formatearValorElemento("TipoJornada",$this->datosNomina["tipoJornada"]);

		

		if($this->datosNomina["salarioBaseCotApor"]!="")

			$$this->datosNomina["salarioBaseCotApor"]=$this->formatearValorMonetarioV2($this->datosNomina["salarioBaseCotApor"]);

		

		$salarioBaseCotApor=$this->formatearValorElemento("SalarioBaseCotApor",$this->datosNomina["salarioBaseCotApor"]);

		

		

		

		$riezgoPuesto="";//$this->formatearValorElemento("RiesgoPuesto",$this->datosNomina["riezgoPuesto"]);

	

		if($this->datosNomina["sdi"]!="")

			$this->datosNomina["sdi"]=$this->formatearValorMonetarioV2($this->datosNomina["sdi"]);

		



			

		$sdi="";//$this->formatearValorElemento("SalarioDiarioIntegrado",$this->datosNomina["sdi"]);

		

		$atributosOpcionales=$registroPatronal.$numSS.$departamento.$clabe.$banco.$fechaInicioRelLaboral.$antiguedad.$puesto.$tipoContrato.$tipoJornada.$salarioBaseCotApor.$riezgoPuesto.$sdi;

		

		$this->XML=	'<cfdi:Comprobante xmlns:nomina="http://www.sat.gob.mx/nomina" xmlns:cfdi="http://www.sat.gob.mx/cfd/3" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '.

					'xsi:schemaLocation="http://www.sat.gob.mx/cfd/3 http://www.sat.gob.mx/sitio_internet/cfd/3/cfdv32.xsd http://www.sat.gob.mx/nomina http://www.sat.gob.mx/sitio_internet/cfd/nomina/nomina11.xsd" '.

					'version="'.$this->version.'" serie="'.$this->serie.'" folio="'.$this->folio.'" fecha="'.$this->fechaActual.'" sello="@selloDigital" '. 

					'formaDePago="'.rC($this->formaPago).'" noCertificado="'.$this->noCertificado.'" certificado="'.$this->certificado.'" '.

					'subTotal="'.$this->formatearValorMonetarioV2($this->subtotal).'" descuento="'.$this->formatearValorMonetarioV2($this->descuento).'" motivoDescuento="'.rC($this->motivoDescuento).'" TipoCambio="'.$this->tipoCambio.'" Moneda="'.$this->moneda.'" total="'.$this->formatearValorMonetarioV2($this->total).

					'" tipoDeComprobante="'.$this->tipoDeComprobante.'" metodoDePago="'.rC($this->metodoDePago).'" LugarExpedicion="'.rC($this->lugarExpedicion).'" '.$atributosOpcionalesCFDI.

					'>'.

					

						

						'<cfdi:Emisor rfc="'.$this->emisor["rfc"].'" nombre="'.rC($this->emisor["razonSocial"]).'">'.

							'<cfdi:DomicilioFiscal calle="'.rC($this->emisor["domicilio"]["calle"]).'" '.$noExterior.' '.$noInterior.' '.$colonia.' '.$localidad.' '.

							'codigoPostal="'.$this->emisor["domicilio"]["codigoPostal"].'" estado="'.$this->emisor["domicilio"]["estado"].'" municipio="'.rC($this->emisor["domicilio"]["municipio"]).

							'" pais="'.$this->emisor["domicilio"]["pais"].'" />';

						foreach($this->emisor["regimenFiscal"] as $r)

						{

							$consulta="select descripcion FROM 7102_regimenFiscal WHERE cve_regimen='".$r."'";

							$regimen=$con->obtenerValor($consulta);

							$this->XML.='<cfdi:RegimenFiscal Regimen="'.rC($regimen).'"/>';

						}

						

						$this->XML.=

						'</cfdi:Emisor>'.

						'<cfdi:Receptor nombre="'.rC($this->receptor["razonSocial"]).'" rfc="'.$this->receptor["rfc"].'">'.

							'<cfdi:Domicilio '.$domicilio.' pais="'.$this->receptor["domicilio"]["pais"].'"/>'.

						'</cfdi:Receptor>'.

						'<cfdi:Conceptos>'.

							'<cfdi:Concepto cantidad="'.$this->formatearValorMonetarioV2(1).'" descripcion="Pago de nómina" valorUnitario="'.$this->formatearValorMonetarioV2($this->subtotal).'" importe="'.$this->formatearValorMonetarioV2($this->subtotal).'"  unidad="SERVICIO"/>'.

						'</cfdi:Conceptos>';

						

						

						$this->XML.='<cfdi:Impuestos totalImpuestosRetenidos="@totalRetenido" totalImpuestosTrasladados="@totalTraslado">';

						$totalRetenciones=0;

						$totalTraslado=0;

						

						if(isset($this->impuestos["retenciones"])&&(sizeof($this->impuestos["retenciones"])>0))

						{

							$this->XML.='<cfdi:Retenciones>';

							

							foreach($this->impuestos["retenciones"] as $r)

							{

								$totalRetenciones+=$r["importe"];

								$this->XML.='<cfdi:Retencion impuesto="'.$r["impuesto"].'" importe="'.$this->formatearValorMonetarioV2($r["importe"]).'"/>';

							}

							$this->XML.='</cfdi:Retenciones>';

						}

						

									

						if(isset($this->impuestos["traslados"])&&(sizeof($this->impuestos["traslados"])>0))

						{	

							$this->XML.='<cfdi:Traslados>';

							

							

							foreach($this->impuestos["traslados"] as $t)

							{

								$totalTraslado+=$t["importe"];

								$this->XML.='<cfdi:Traslado impuesto="'.$t["impuesto"].'" tasa="'.$this->formatearValorMonetarioV2($t["tasa"]).'" importe="'.$this->formatearValorMonetarioV2($t["importe"]).'"/>';

							}

							$this->XML.='</cfdi:Traslados>';

							

						}

						$this->XML=str_replace("@totalRetenido",$this->formatearValorMonetarioV2($totalRetenciones),$this->XML);			

						$this->XML=str_replace("@totalTraslado",$this->formatearValorMonetarioV2($totalTraslado),$this->XML);			

						$this->XML.='</cfdi:Impuestos>';						

						$this->XML.=

						'<cfdi:Complemento>'.

							'<nomina:Nomina Version="'.$this->versionNomina.'"  NumEmpleado="'.$this->datosNomina["numEmpleado"].'" CURP="'.$this->datosNomina["curp"].'" '.

							'TipoRegimen="'.$this->datosNomina["tipoRegimen"].'"  FechaPago="'.$this->datosNomina["fechaPago"].'" '.$atributosOpcionales.

							'FechaInicialPago="'.$this->datosNomina["fechaInicioPago"].'" FechaFinalPago="'.$this->datosNomina["fechaFinPago"].'" NumDiasPagados="'.$this->datosNomina["numDiasPagados"].'" '.

							'PeriodicidadPago="'.$this->datosNomina["periodicidadPago"].'">';

							

								$pTotalGravado=0;

								$pTotalExento=0;

								if(sizeof($this->datosNomina["arrPercepciones"])>0)

								{

									$this->XML.='<nomina:Percepciones TotalGravado="@pTotalGravado" TotalExento="@pTotalExento">';

									foreach($this->datosNomina["arrPercepciones"] as $p)

									{

										$pTotalExento+=$p["importeExento"];

										$pTotalGravado+=$p["importeGravado"];

										

										$p["importeGravado"]=$this->formatearValorMonetarioV2($p["importeGravado"]);

										$p["importeExento"]=$this->formatearValorMonetarioV2($p["importeExento"]);

										$this->XML.='<nomina:Percepcion TipoPercepcion="'.$p["tipoPercepcion"].'" Clave="'.$p["clave"].'" Concepto="'.$p["descripcion"].'" ImporteGravado="'.$p["importeGravado"].'" ImporteExento="'.$p["importeExento"].'"/>';

									}

									$this->XML.='</nomina:Percepciones>';

								}

								$this->XML=str_replace("@pTotalGravado",$this->formatearValorMonetarioV2($pTotalGravado),$this->XML);

								$this->XML=str_replace("@pTotalExento",$this->formatearValorMonetarioV2($pTotalExento),$this->XML);

								$dTotalGravado=0;

								$dTotalExento=0;

								if(sizeof($this->datosNomina["arrDeducciones"])>0)

								{

									$this->XML.='<nomina:Deducciones TotalGravado="@dTotalGravado" TotalExento="@dTotalExento">';

									foreach($this->datosNomina["arrDeducciones"] as $d)

									{

										//if($d["tipoDeduccion"]!="002")

										{

											$dTotalExento+=$d["importeExento"];

											$dTotalGravado+=$d["importeGravado"];

										}

										

										

										$d["importeGravado"]=$this->formatearValorMonetarioV2($d["importeGravado"]);

										$d["importeExento"]=$this->formatearValorMonetarioV2($d["importeExento"]);

										

										$this->XML.='<nomina:Deduccion TipoDeduccion="'.$d["tipoDeduccion"].'" Clave="'.$d["clave"].'" Concepto="'.$d["descripcion"].'" ImporteGravado="'.$d["importeGravado"].'" ImporteExento="'.$d["importeExento"].'"/>';

									}

									$this->XML.='</nomina:Deducciones>';

								}

								$this->XML=str_replace("@dTotalGravado",$this->formatearValorMonetarioV2($dTotalGravado),$this->XML);

								$this->XML=str_replace("@dTotalExento",$this->formatearValorMonetarioV2($dTotalExento),$this->XML);

								

								if((isset($this->datosNomina["arrIncapacidades"]))&&(sizeof($this->datosNomina["arrIncapacidades"])>0))

								{

									$this->XML.='<nomina:Incapacidades>';

									foreach($this->datosNomina["arrIncapacidades"] as $i)

									{

										$i["descuentoIncapacidad"]=$this->formatearValorMonetarioV2($i["descuentoIncapacidad"]);

										$this->XML.='<nomina:Incapacidad DiasIncapacidad="'.$i["diasIncapacidad"].'" TipoIncapacidad="'.$i["tipoIncapacidad"].'" Descuento="'.$i["descuentoIncapacidad"].'"/>';

									}

									$this->XML.='</nomina:Incapacidades>';

									

								

								}

								

								if((isset($this->datosNomina["arrHorasExtra"]))&&(sizeof($this->datosNomina["arrHorasExtra"])>0))

								{

									$this->XML.='<nomina:HorasExtras>';

									foreach($this->datosNomina["arrHorasExtra"] as $h)

									{

										$h["importePagado"]=$this->formatearValorMonetarioV2($h["importePagado"]);

										$this->XML.='<nomina:HorasExtra Dias="'.$h["diasHorasExtras"].'" TipoHoras="'.$h["tipoPagoHoras"].'" HorasExtra="'.$h["totalHorasExtra"].'" ImportePagado="'.$h["importePagado"].'"/>';

									}

									$this->XML.='</nomina:HorasExtras>';

								}

								

									

		$this->XML.=		'</nomina:Nomina>'.

						'</cfdi:Complemento>'.

					'</cfdi:Comprobante>';

		$this->XML=str_replace("&","&amp;",$this->XML);					

		return $this->XML;	

	}

	 

	function generarXMLV1_2()

	{

		global $con;

		$this->moneda="MXN";

		$condicionesDePago=$this->formatearValorElemento("condicionesDePago",$this->condicionesDePago);

		$numCtaPago="";//$this->formatearValorElemento("NumCtaPago",$this->numCtaPago);

		$folioFiscalOrig=$this->formatearValorElemento("FolioFiscalOrig",$this->folioFiscalOrig);

		$serieFolioFiscalOrig=$this->formatearValorElemento("SerieFolioFiscalOrig",$this->serieFolioFiscalOrig);

		$fechaFolioFiscalOrig=$this->formatearValorElemento("FechaFolioFiscalOrig",$this->fechaFolioFiscalOrig);

		

		

		if($this->montoFolioFiscalOrig!="")

			$this->montoFolioFiscalOrig=$this->formatearValorMonetarioV2($this->montoFolioFiscalOrig);

		

		$montoFolioFiscalOrig=$this->formatearValorElemento("MontoFolioFiscalOrig",$this->montoFolioFiscalOrig);

		

		$atributosOpcionalesCFDI=$condicionesDePago.$numCtaPago.$folioFiscalOrig.$serieFolioFiscalOrig.$fechaFolioFiscalOrig.$montoFolioFiscalOrig;

		

		$noExterior=$this->formatearValorElemento("noExterior",$this->emisor["domicilio"]["noExterior"]);

		$noInterior=$this->formatearValorElemento("noInterior",$this->emisor["domicilio"]["noInterior"]);

		$colonia=$this->formatearValorElemento("colonia",$this->emisor["domicilio"]["colonia"]);

		$localidad=$this->formatearValorElemento("localidad",$this->emisor["domicilio"]["localidad"]);

			

		

		$calleRec=$this->formatearValorElemento("calle",$this->receptor["domicilio"]["calle"]);	

		$noExteriorRec=$this->formatearValorElemento("noExterior",$this->receptor["domicilio"]["noExterior"]);	

		$noInteriorRec=$this->formatearValorElemento("noInterior",$this->receptor["domicilio"]["noInterior"]);	

		$coloniaRec=$this->formatearValorElemento("colonia",$this->receptor["domicilio"]["colonia"]);	

		$localidadRec=$this->formatearValorElemento("localidad",$this->receptor["domicilio"]["localidad"]);	

		$municipioRec=$this->formatearValorElemento("municipio",$this->receptor["domicilio"]["municipio"]);	

		$estadoRec=$this->formatearValorElemento("estado",$this->receptor["domicilio"]["estado"]);	

		$codigoPostalRec=$this->formatearValorElemento("codigoPostal",$this->receptor["domicilio"]["codigoPostal"]);	

		

		

		$domicilio=$calleRec.$noExteriorRec.$noInteriorRec.$coloniaRec.$localidadRec.$municipioRec.$estadoRec.$codigoPostalRec;

		

		$registroPatronal=$this->formatearValorElemento("RegistroPatronal",$this->datosNomina["registroPatronal"]);

		$numSS=$this->formatearValorElemento("NumSeguridadSocial",$this->datosNomina["numSS"]);

		//$departamento=substr($this->formatearValorElemento("Departamento",$this->datosNomina["departamento"]),0,100);

		$clabe=$this->formatearValorElemento("CLABE",$this->datosNomina["clabe"]);

		$banco=$this->formatearValorElemento("Banco",$this->datosNomina["banco"]);

		$fechaInicioRelLaboral=$this->formatearValorElemento("FechaInicioRelLaboral",$this->datosNomina["fechaInicioRelLaboral"]);

		$antiguedad=$this->formatearValorElemento("Antiguedad",$this->datosNomina["antiguedad"]);

		//$puesto=$this->formatearValorElemento("Puesto",$this->datosNomina["puesto"]);

		$tipoContrato=$this->formatearValorElemento("TipoContrato",$this->datosNomina["tipoContrato"]);

		$tipoJornada=$this->formatearValorElemento("TipoJornada",$this->datosNomina["tipoJornada"]);

		$sindicalizado=$this->formatearValorElemento("Sindicalizado",$this->datosNomina["Sindicalizado"]);

		if($this->datosNomina["salarioBaseCotApor"]!="")

			$this->datosNomina["salarioBaseCotApor"]=$this->formatearValorMonetarioV2($this->datosNomina["salarioBaseCotApor"]);

		

		$salarioBaseCotApor=$this->formatearValorElemento("SalarioBaseCotApor",$this->datosNomina["salarioBaseCotApor"]);

		

		

		

		$riezgoPuesto=$this->formatearValorElemento("RiesgoPuesto",$this->datosNomina["riezgoPuesto"]);

	

		if($this->datosNomina["sdi"]!="")

			$this->datosNomina["sdi"]=$this->formatearValorMonetarioV2($this->datosNomina["sdi"]);

		



			

		$sdi=$this->formatearValorElemento("SalarioDiarioIntegrado",$this->datosNomina["sdi"]);

		

		$atributosOpcionales=$registroPatronal.$numSS.$departamento.$clabe.$banco.$fechaInicioRelLaboral.$antiguedad.$puesto.$tipoContrato.$tipoJornada.$salarioBaseCotApor.$riezgoPuesto.$sdi;

		

		$this->XML=	'<cfdi:Comprobante xmlns:nomina12="http://www.sat.gob.mx/nomina12" xmlns:cfdi="http://www.sat.gob.mx/cfd/3" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '.

					'xsi:schemaLocation="http://www.sat.gob.mx/cfd/3 http://www.sat.gob.mx/sitio_internet/cfd/3/cfdv32.xsd http://www.sat.gob.mx/nomina http://www.sat.gob.mx/sitio_internet/cfd/nomina/nomina11.xsd" '.

					'version="3.2" serie="'.$this->serie.'" folio="'.$this->folio.'" fecha="'.$this->fechaActual.'" sello="@selloDigital" '. 

					'formaDePago="'.rC($this->formaPago).'" noCertificado="'.$this->noCertificado.'" certificado="'.$this->certificado.'" '.

					'subTotal="@totalPercepciones" descuento="@totalDeducciones"   Moneda="'.$this->moneda.'" total="@totalComprobante"'.

					' tipoDeComprobante="'.$this->tipoDeComprobante.'" metodoDePago="NA" LugarExpedicion="'.rC($this->lugarExpedicion).'" '.$atributosOpcionalesCFDI.

					'>'.

					

						

						'<cfdi:Emisor rfc="'.$this->emisor["rfc"].'" nombre="'.rC($this->emisor["razonSocial"]).'">';

							/*'<cfdi:DomicilioFiscal calle="'.rC($this->emisor["domicilio"]["calle"]).'" '.$noExterior.' '.$noInterior.' '.$colonia.' '.$localidad.' '.

							'codigoPostal="'.$this->emisor["domicilio"]["codigoPostal"].'" estado="'.$this->emisor["domicilio"]["estado"].'" municipio="'.rC($this->emisor["domicilio"]["municipio"]).

							'" pais="'.$this->emisor["domicilio"]["pais"].'" />';*/

						foreach($this->emisor["regimenFiscal"] as $r)

						{

							$this->XML.='<cfdi:RegimenFiscal Regimen="'.rC($r).'"/>';

							break;

						}

						

						$this->XML.=

						'</cfdi:Emisor>'.

						'<cfdi:Receptor nombre="'.rC($this->receptor["razonSocial"]).'" rfc="'.$this->receptor["rfc"].'">'.

							//'<cfdi:Domicilio '.$domicilio.' pais="'.$this->receptor["domicilio"]["pais"].'"/>'.

						'</cfdi:Receptor>'.

						'<cfdi:Conceptos>'.

							'<cfdi:Concepto cantidad="1" descripcion="Pago de nómina" valorUnitario="@totalPercepciones" importe="@totalPercepciones"  unidad="ACT"/>'.

						'</cfdi:Conceptos>';

						

						

						//$this->XML.='<cfdi:Impuestos totalImpuestosRetenidos="@totalRetenido" totalImpuestosTrasladados="@totalTraslado">';

						$this->XML.='<cfdi:Impuestos>';

						$totalRetenciones=0;

						$totalTraslado=0;

						

						/*if(isset($this->impuestos["retenciones"])&&(sizeof($this->impuestos["retenciones"])>0))

						{

							$this->XML.='<cfdi:Retenciones>';

							

							foreach($this->impuestos["retenciones"] as $r)

							{

								$totalRetenciones+=$r["importe"];

								$this->XML.='<cfdi:Retencion impuesto="'.$r["impuesto"].'" importe="'.$this->formatearValorMonetarioV2($r["importe"]).'"/>';

							}

							$this->XML.='</cfdi:Retenciones>';

						}

						

									

						if(isset($this->impuestos["traslados"])&&(sizeof($this->impuestos["traslados"])>0))

						{	

							$this->XML.='<cfdi:Traslados>';

							

							

							foreach($this->impuestos["traslados"] as $t)

							{

								$totalTraslado+=$t["importe"];

								$this->XML.='<cfdi:Traslado impuesto="'.$t["impuesto"].'" tasa="'.$this->formatearValorMonetarioV2($t["tasa"]).'" importe="'.$this->formatearValorMonetarioV2($t["importe"]).'"/>';

							}

							$this->XML.='</cfdi:Traslados>';

							

						}

						$this->XML=str_replace("@totalRetenido",$this->formatearValorMonetarioV2($totalRetenciones),$this->XML);			

						$this->XML=str_replace("@totalTraslado",$this->formatearValorMonetarioV2($totalTraslado),$this->XML);	*/		

						$this->XML.='</cfdi:Impuestos>';						

						$this->XML.=

						'<cfdi:Complemento>'.

							'<nomina12:Nomina Version="'.$this->versionNomina.'" TipoNomina="'.$this->tipoNomina.'"   '.

							' FechaPago="'.$this->datosNomina["fechaPago"].'" FechaInicialPago="'.$this->datosNomina["fechaInicioPago"].

							'" FechaFinalPago="'.$this->datosNomina["fechaFinPago"].'" NumDiasPagados="'.$this->datosNomina["numDiasPagados"].'" '.

							' TotalPercepciones="@totalPercepciones" TotalDeducciones="@totalDeducciones" >';

							

								$this->XML.='<nomina12:Emisor>';

								$this->XML.='</nomina12:Emisor>';

								$this->XML.='<nomina12:Receptor Curp="'.$this->datosNomina["curp"].'" '.$numSS.' TipoContrato="'.$this->datosNomina["tipoContrato"].'" '.$sindicalizado.

											' '.$tipoJornada.' TipoRegimen="'.$this->datosNomina["tipoRegimen"].'" NumEmpleado="'.$this->datosNomina["numEmpleado"].'" '.$puesto.' '.

											$departamento.' PeriodicidadPago="'.$this->datosNomina["periodicidadPago"].'" ClaveEntFed="'.$this->datosNomina["entidadFederativa"].'">';

								$this->XML.='</nomina12:Receptor>';

							

							

								$pTotalGravado=0;

								$pTotalExento=0;

								$totalSueldo=0;

								$totalSeparacionIndemnizacion=0;

								$totalJubilacion=0;

								

								if(sizeof($this->datosNomina["arrPercepciones"])>0)

								{

									$this->XML.='<nomina12:Percepciones TotalGravado="@pTotalGravado" TotalExento="@pTotalExento" TotalSueldos="@totalSueldos"'.

											' TotalSeparacionIndemnizacion="@totalSeparacionIndemnizacion" TotalJubilacionPensionRetiro="@totalJubilacionRetiro">';

									foreach($this->datosNomina["arrPercepciones"] as $p)

									{

										$pTotalExento+=$p["importeExento"];

										$pTotalGravado+=$p["importeGravado"];

										

										switch($p["tipoPercepcion"])

										{

											case '022':

											case '023':

											case '056':

												$totalSeparacionIndemnizacion+=$p["importeExento"]+$p["importeGravado"];

											break;

											case '039':

											case '044':

												$totalJubilacion+=$p["importeExento"]+$p["importeGravado"];

											break;

											default:

												$totalSueldo+=$p["importeExento"]+$p["importeGravado"];

											break;

											

										}

										

										$p["importeGravado"]=$this->formatearValorMonetarioV2($p["importeGravado"]);

										$p["importeExento"]=$this->formatearValorMonetarioV2($p["importeExento"]);

										$this->XML.='<nomina12:Percepcion TipoPercepcion="'.$p["tipoPercepcion"].'" Clave="'.$p["clave"].'" Concepto="'.$p["descripcion"].'" ImporteGravado="'.$p["importeGravado"].'" ImporteExento="'.$p["importeExento"].'"/>';

									}

									$this->XML.='</nomina12:Percepciones>';

								}

								$this->XML=str_replace("@pTotalGravado",$this->formatearValorMonetarioV2($pTotalGravado),$this->XML);

								$this->XML=str_replace("@pTotalExento",$this->formatearValorMonetarioV2($pTotalExento),$this->XML);

								$this->XML=str_replace("@totalSueldos",$this->formatearValorMonetarioV2($totalSueldo),$this->XML);

								$this->XML=str_replace("@totalSeparacionIndemnizacion",$this->formatearValorMonetarioV2($totalSeparacionIndemnizacion),$this->XML);

								$this->XML=str_replace("@totalJubilacionRetiro",$this->formatearValorMonetarioV2($totalJubilacion),$this->XML);

								$this->XML=str_replace("@totalPercepciones",$this->formatearValorMonetarioV2($totalSueldo+$totalJubilacion+$totalSeparacionIndemnizacion),$this->XML);

								$totalOtrasDeducciones=0;

								$totalImpuestosRetenidos=0;

								if(sizeof($this->datosNomina["arrDeducciones"])>0)

								{

									$this->XML.='<nomina12:Deducciones TotalOtrasDeducciones="@totalOtrasDeducciones" TotalImpuestosRetenidos="@totalImpuestosRetenidos">';

									foreach($this->datosNomina["arrDeducciones"] as $d)

									{

										if($d["tipoDeduccion"]!="002")

											$totalOtrasDeducciones+=$d["importeExento"]+$d["importeGravado"];

										else

											$totalImpuestosRetenidos+=$d["importeExento"]+$d["importeGravado"];

										

										$this->XML.='<nomina12:Deduccion TipoDeduccion="'.$d["tipoDeduccion"].'" Clave="'.$d["clave"].'" Concepto="'.$d["descripcion"].'" Importe="'.$this->formatearValorMonetarioV2($d["importeGravado"]+$d["importeExento"]).'"/>';

									}

									$this->XML.='</nomina12:Deducciones>';

								}

								$this->XML=str_replace("@totalOtrasDeducciones",$this->formatearValorMonetarioV2($totalOtrasDeducciones),$this->XML);

								$this->XML=str_replace("@totalImpuestosRetenidos",$this->formatearValorMonetarioV2($totalImpuestosRetenidos),$this->XML);

								

								$tPercepciones=$totalSueldo+$totalJubilacion+$totalSeparacionIndemnizacion;

								$tDeducciones=$totalOtrasDeducciones+$totalImpuestosRetenidos;

								if(sizeof($this->datosNomina["arrPercepciones"])>0)

									$this->XML=str_replace("@totalPercepciones",$this->formatearValorMonetarioV2($tPercepciones),$this->XML);

								else

								{

									$this->XML=str_replace('TotalPercepciones="@totalPercepciones"',"",$this->XML);

									$this->XML=str_replace("@totalPercepciones",$this->formatearValorMonetarioV2($tPercepciones),$this->XML);

								}

								

								if(sizeof($this->datosNomina["arrDeducciones"])>0)	

									$this->XML=str_replace("@totalDeducciones",$this->formatearValorMonetarioV2($tDeducciones),$this->XML);

								else

								{

									$this->XML=str_replace('TotalDeducciones="@totalDeducciones"',"",$this->XML);

									$this->XML=str_replace("@totalDeducciones",$this->formatearValorMonetarioV2($tDeducciones),$this->XML);

								}

								

								$this->XML=str_replace("@totalComprobante",$this->formatearValorMonetarioV2($tPercepciones-$tDeducciones),$this->XML);

								

								

								if((isset($this->datosNomina["arrIncapacidades"]))&&(sizeof($this->datosNomina["arrIncapacidades"])>0))

								{

									$this->XML.='<nomina12:Incapacidades>';

									foreach($this->datosNomina["arrIncapacidades"] as $i)

									{

										$i["descuentoIncapacidad"]=$this->formatearValorMonetarioV2($i["descuentoIncapacidad"]);

										$this->XML.='<nomina12:Incapacidad DiasIncapacidad="'.$i["diasIncapacidad"].'" TipoIncapacidad="'.$i["tipoIncapacidad"].'" ImporteMonetario="'.$i["descuentoIncapacidad"].'"/>';

									}

									$this->XML.='</nomina12:Incapacidades>';

									

								

								}

								

								if((isset($this->datosNomina["arrHorasExtra"]))&&(sizeof($this->datosNomina["arrHorasExtra"])>0))

								{

									$this->XML.='<nomina:HorasExtras>';

									foreach($this->datosNomina["arrHorasExtra"] as $h)

									{

										$h["importePagado"]=$this->formatearValorMonetarioV2($h["importePagado"]);

										$this->XML.='<nomina:HorasExtra Dias="'.$h["diasHorasExtras"].'" TipoHoras="'.$h["tipoPagoHoras"].'" HorasExtra="'.$h["totalHorasExtra"].'" ImportePagado="'.$h["importePagado"].'"/>';

									}

									$this->XML.='</nomina:HorasExtras>';

								}

								

									

		$this->XML.=		'</nomina12:Nomina>'.

						'</cfdi:Complemento>'.

					'</cfdi:Comprobante>';

		$this->XML=str_replace("&","&amp;",$this->XML);					

		return $this->XML;	

	}

	

	function generarXMLV1_2_CFDI3_3()

	{

		global $con;

		

		$this->moneda="MXN";

		$condicionesDePago=$this->formatearValorElemento("CondicionesDePago",$this->condicionesDePago);

		

		$consulta="SELECT *FROM 671_asientosCalculosNomina WHERE idAsientoNomina=".$this->idAsientoNomina;

		$fDatosAsiento=$con->obtenerPrimeraFilaAsoc($consulta);

		

		$consulta="SELECT i.* FROM 817_organigrama o,247_instituciones i WHERE codigoUnidad='".$fDatosAsiento["codDepartamento"]."' 

				AND i.idOrganigrama=o.idOrganigrama";

		

		$fDatosInstitucion=$con->obtenerPrimeraFilaAsoc($consulta);

		

		$atributosOpcionalesCFDI=$condicionesDePago;

		

		$noExterior=$this->formatearValorElemento("noExterior",$this->emisor["domicilio"]["noExterior"]);

		$noInterior=$this->formatearValorElemento("noInterior",$this->emisor["domicilio"]["noInterior"]);

		$colonia=$this->formatearValorElemento("colonia",$this->emisor["domicilio"]["colonia"]);

		$localidad=$this->formatearValorElemento("localidad",$this->emisor["domicilio"]["localidad"]);

			

		

		$calleRec=$this->formatearValorElemento("calle",$this->receptor["domicilio"]["calle"]);	

		$noExteriorRec=$this->formatearValorElemento("noExterior",$this->receptor["domicilio"]["noExterior"]);	

		$noInteriorRec=$this->formatearValorElemento("noInterior",$this->receptor["domicilio"]["noInterior"]);	

		$coloniaRec=$this->formatearValorElemento("colonia",$this->receptor["domicilio"]["colonia"]);	

		$localidadRec=$this->formatearValorElemento("localidad",$this->receptor["domicilio"]["localidad"]);	

		$municipioRec=$this->formatearValorElemento("municipio",$this->receptor["domicilio"]["municipio"]);	

		$estadoRec=$this->formatearValorElemento("estado",$this->receptor["domicilio"]["estado"]);	

		$codigoPostalRec=$this->formatearValorElemento("codigoPostal",$this->receptor["domicilio"]["codigoPostal"]);	

		

		

		$domicilio=$calleRec.$noExteriorRec.$noInteriorRec.$coloniaRec.$localidadRec.$municipioRec.$estadoRec.$codigoPostalRec;

		

		$registroPatronal=$this->formatearValorElemento("RegistroPatronal",$this->datosNomina["registroPatronal"]);

		$numSS="";//$this->formatearValorElemento("NumSeguridadSocial",$this->datosNomina["numSS"]);
		$departamento="";
		//$departamento=substr($this->formatearValorElemento("Departamento",$this->datosNomina["departamento"]),0,100);

		$clabe=$this->formatearValorElemento("CLABE",$this->datosNomina["clabe"]);

		$banco=$this->formatearValorElemento("Banco",$this->datosNomina["banco"]);

		$fechaInicioRelLaboral=$this->formatearValorElemento("FechaInicioRelLaboral",$this->datosNomina["fechaInicioRelLaboral"]);

		$antiguedad=$this->formatearValorElemento("Antiguedad",$this->datosNomina["antiguedad"]);
		$puesto="";
		//$puesto=$this->formatearValorElemento("Puesto",$this->datosNomina["puesto"]);

		$tipoContrato=$this->formatearValorElemento("TipoContrato",$this->datosNomina["tipoContrato"]);

		$tipoJornada=$this->formatearValorElemento("TipoJornada",$this->datosNomina["tipoJornada"]);

		$sindicalizado=$this->formatearValorElemento("Sindicalizado",$this->datosNomina["Sindicalizado"]);

		if($this->datosNomina["salarioBaseCotApor"]!="")

			$this->datosNomina["salarioBaseCotApor"]=$this->formatearValorMonetarioV2($this->datosNomina["salarioBaseCotApor"]);

		

		$salarioBaseCotApor=$this->formatearValorElemento("SalarioBaseCotApor",$this->datosNomina["salarioBaseCotApor"]);

		

		

		

		$riezgoPuesto=$this->formatearValorElemento("RiesgoPuesto",$this->datosNomina["riezgoPuesto"]);

	

		if($this->datosNomina["sdi"]!="")

			$this->datosNomina["sdi"]=$this->formatearValorMonetarioV2($this->datosNomina["sdi"]);

		



			

		$sdi=$this->formatearValorElemento("SalarioDiarioIntegrado",$this->datosNomina["sdi"]);

		

		$atributosOpcionales=$registroPatronal.$numSS.$departamento.$clabe.$banco.$fechaInicioRelLaboral.$antiguedad.$puesto.$tipoContrato.$tipoJornada.$salarioBaseCotApor.$riezgoPuesto.$sdi;

		

		$regimen="";

		foreach($this->emisor["regimenFiscal"] as $r)

		{

			$regimen=rC($r);

			break;

		};

		$this->XML=	'<cfdi:Comprobante xmlns:nomina12="http://www.sat.gob.mx/nomina12" xmlns:cfdi="http://www.sat.gob.mx/cfd/3" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '.

					'xsi:schemaLocation="http://www.sat.gob.mx/cfd/3 http://www.sat.gob.mx/sitio_internet/cfd/3/cfdv33.xsd http://www.sat.gob.mx/nomina12 http://www.sat.gob.mx/sitio_internet/cfd/nomina/nomina12.xsd" '.

					'Version="3.3" Serie="'.$this->serie.'" Folio="'.$this->folio.'" Fecha="'.$this->fechaActual.'" Sello="@selloDigital" '. 

					'FormaPago="99" NoCertificado="'.$this->noCertificado.'" Certificado="'.$this->certificado.'" '.

					'SubTotal="@totalPercepciones" Descuento="@totalDeducciones"   Moneda="'.$this->moneda.'" Total="@totalComprobante"'.

					' TipoDeComprobante="N" MetodoPago="PUE" LugarExpedicion="'.$fDatosInstitucion["cp"].'" '.$atributosOpcionalesCFDI.

					'>';

		if(sizeof($this->objComprobante["cfdiRelacionados"])>0 )

		{					

			$this->XML.='<cfdi:CfdiRelacionados TipoRelacion="'.$this->objComprobante["relacionCFDI"].'">'	;	

			

			foreach($this->objComprobante["cfdiRelacionados"]  as $c)

			{

				

				$this->XML.='<cfdi:CfdiRelacionado UUID="'.$c.'" />'	;	

			}

			

			$this->XML.='</cfdi:CfdiRelacionados>'	;			

		}

		$this->XML.='<cfdi:Emisor Rfc="'.$this->emisor["rfc"].'" Nombre="'.rC($this->emisor["razonSocial"]).'" RegimenFiscal="'.$regimen.'">';

		

		

							/*'<cfdi:DomicilioFiscal calle="'.rC($this->emisor["domicilio"]["calle"]).'" '.$noExterior.' '.$noInterior.' '.$colonia.' '.$localidad.' '.

							'codigoPostal="'.$this->emisor["domicilio"]["codigoPostal"].'" estado="'.$this->emisor["domicilio"]["estado"].'" municipio="'.rC($this->emisor["domicilio"]["municipio"]).

							'" pais="'.$this->emisor["domicilio"]["pais"].'" />';*/

						

						

						$this->XML.=

						'</cfdi:Emisor>'.

						'<cfdi:Receptor Nombre="'.rC($this->receptor["razonSocial"]).'" Rfc="'.$this->receptor["rfc"].'" UsoCFDI="P01">'.

							//'<cfdi:Domicilio '.$domicilio.' pais="'.$this->receptor["domicilio"]["pais"].'"/>'.

						'</cfdi:Receptor>'.

						'<cfdi:Conceptos>'.

							'<cfdi:Concepto ClaveProdServ="84111505" Cantidad="1" Descripcion="Pago de nómina" ValorUnitario="@totalPercepciones" Importe="@totalPercepciones"  ClaveUnidad="ACT" Descuento="@totalDeducciones"/>'.

						'</cfdi:Conceptos>';

		

						

						//$this->XML.='<cfdi:Impuestos totalImpuestosRetenidos="@totalRetenido" totalImpuestosTrasladados="@totalTraslado">';

						/*$this->XML.='<cfdi:Impuestos>';

						$totalRetenciones=0;

						$totalTraslado=0;

						

						if(isset($this->impuestos["retenciones"])&&(sizeof($this->impuestos["retenciones"])>0))

						{

							$this->XML.='<cfdi:Retenciones>';

							

							foreach($this->impuestos["retenciones"] as $r)

							{

								$totalRetenciones+=$r["importe"];

								$this->XML.='<cfdi:Retencion impuesto="'.$r["impuesto"].'" importe="'.$this->formatearValorMonetarioV2($r["importe"]).'"/>';

							}

							$this->XML.='</cfdi:Retenciones>';

						}

						

									

						if(isset($this->impuestos["traslados"])&&(sizeof($this->impuestos["traslados"])>0))

						{	

							$this->XML.='<cfdi:Traslados>';

							

							

							foreach($this->impuestos["traslados"] as $t)

							{

								$totalTraslado+=$t["importe"];

								$this->XML.='<cfdi:Traslado impuesto="'.$t["impuesto"].'" tasa="'.$this->formatearValorMonetarioV2($t["tasa"]).'" importe="'.$this->formatearValorMonetarioV2($t["importe"]).'"/>';

							}

							$this->XML.='</cfdi:Traslados>';

							

						}

						$this->XML=str_replace("@totalRetenido",$this->formatearValorMonetarioV2($totalRetenciones),$this->XML);			

						$this->XML=str_replace("@totalTraslado",$this->formatearValorMonetarioV2($totalTraslado),$this->XML);		

						$this->XML.='</cfdi:Impuestos>';						*/	

						$this->XML.=

						'<cfdi:Complemento>'.

							'<nomina12:Nomina Version="'.$this->versionNomina.'" TipoNomina="'.$this->tipoNomina.'"   '.

							' FechaPago="'.$this->datosNomina["fechaPago"].'" FechaInicialPago="'.$this->datosNomina["fechaInicioPago"].

							'" FechaFinalPago="'.$this->datosNomina["fechaFinPago"].'" NumDiasPagados="'.$this->datosNomina["numDiasPagados"].'" '.

							' TotalPercepciones="@totalPercepciones" TotalDeducciones="@totalDeducciones" >';

							

								$this->XML.='<nomina12:Emisor>';

								$this->XML.='</nomina12:Emisor>';

								$this->XML.='<nomina12:Receptor Curp="'.$this->datosNomina["curp"].'" '.$numSS.' TipoContrato="'.$this->datosNomina["tipoContrato"].'" '.$sindicalizado.

											' '.$tipoJornada.' TipoRegimen="'.$this->datosNomina["tipoRegimen"].'" NumEmpleado="'.$this->datosNomina["numEmpleado"].'" '.$puesto.' '.

											$departamento.' PeriodicidadPago="'.$this->datosNomina["periodicidadPago"].'" ClaveEntFed="'.$this->datosNomina["entidadFederativa"].'">';

								$this->XML.='</nomina12:Receptor>';

							

							

								$pTotalGravado=0;

								$pTotalExento=0;

								$totalSueldo=0;

								$totalSeparacionIndemnizacion=0;

								$totalJubilacion=0;

								

								if(sizeof($this->datosNomina["arrPercepciones"])>0)

								{

									$this->XML.='<nomina12:Percepciones TotalGravado="@pTotalGravado" TotalExento="@pTotalExento" TotalSueldos="@totalSueldos"'.

											' TotalSeparacionIndemnizacion="@totalSeparacionIndemnizacion" TotalJubilacionPensionRetiro="@totalJubilacionRetiro">';

									foreach($this->datosNomina["arrPercepciones"] as $p)

									{

										$pTotalExento+=$p["importeExento"];

										$pTotalGravado+=$p["importeGravado"];

										

										switch($p["tipoPercepcion"])

										{

											case '022':

											case '023':

											case '056':

												$totalSeparacionIndemnizacion+=$p["importeExento"]+$p["importeGravado"];

											break;

											case '039':

											case '044':

												$totalJubilacion+=$p["importeExento"]+$p["importeGravado"];

											break;

											default:

												$totalSueldo+=$p["importeExento"]+$p["importeGravado"];

											break;

											

										}

										

										$p["importeGravado"]=$this->formatearValorMonetarioV2($p["importeGravado"]);

										$p["importeExento"]=$this->formatearValorMonetarioV2($p["importeExento"]);

										$this->XML.='<nomina12:Percepcion TipoPercepcion="'.$p["tipoPercepcion"].'" Clave="'.$p["clave"].'" Concepto="'.$p["descripcion"].'" ImporteGravado="'.$p["importeGravado"].'" ImporteExento="'.$p["importeExento"].'"/>';

									}

									$this->XML.='</nomina12:Percepciones>';

								}

								$this->XML=str_replace("@pTotalGravado",$this->formatearValorMonetarioV2($pTotalGravado),$this->XML);

								$this->XML=str_replace("@pTotalExento",$this->formatearValorMonetarioV2($pTotalExento),$this->XML);

								$this->XML=str_replace("@totalSueldos",$this->formatearValorMonetarioV2($totalSueldo),$this->XML);

								$this->XML=str_replace("@totalSeparacionIndemnizacion",$this->formatearValorMonetarioV2($totalSeparacionIndemnizacion),$this->XML);

								$this->XML=str_replace("@totalJubilacionRetiro",$this->formatearValorMonetarioV2($totalJubilacion),$this->XML);

								$this->XML=str_replace("@totalPercepciones",$this->formatearValorMonetarioV2($totalSueldo+$totalJubilacion+$totalSeparacionIndemnizacion),$this->XML);

								

								

								

								$totalOtrasDeducciones=0;

								$totalImpuestosRetenidos=0;

								if(sizeof($this->datosNomina["arrDeducciones"])>0)

								{

									$this->XML.='<nomina12:Deducciones TotalOtrasDeducciones="@totalOtrasDeducciones" TotalImpuestosRetenidos="@totalImpuestosRetenidos">';

									foreach($this->datosNomina["arrDeducciones"] as $d)

									{

										if($d["tipoDeduccion"]!="002")

											$totalOtrasDeducciones+=$d["importeExento"]+$d["importeGravado"];

										else

											$totalImpuestosRetenidos+=$d["importeExento"]+$d["importeGravado"];

										

										$this->XML.='<nomina12:Deduccion TipoDeduccion="'.$d["tipoDeduccion"].'" Clave="'.$d["clave"].'" Concepto="'.$d["descripcion"].'" Importe="'.$this->formatearValorMonetarioV2($d["importeGravado"]+$d["importeExento"]).'"/>';

									}

									$this->XML.='</nomina12:Deducciones>';

								}

								$this->XML=str_replace("@totalOtrasDeducciones",$this->formatearValorMonetarioV2($totalOtrasDeducciones),$this->XML);

								$this->XML=str_replace("@totalImpuestosRetenidos",$this->formatearValorMonetarioV2($totalImpuestosRetenidos),$this->XML);

								

								$tPercepciones=$totalSueldo+$totalJubilacion+$totalSeparacionIndemnizacion;

								$tDeducciones=$totalOtrasDeducciones+$totalImpuestosRetenidos;

								if(sizeof($this->datosNomina["arrPercepciones"])>0)

									$this->XML=str_replace("@totalPercepciones",$this->formatearValorMonetarioV2($tPercepciones),$this->XML);

								else

								{

									$this->XML=str_replace('TotalPercepciones="@totalPercepciones"',"",$this->XML);

									$this->XML=str_replace("@totalPercepciones",$this->formatearValorMonetarioV2($tPercepciones),$this->XML);

								}

								

								if(sizeof($this->datosNomina["arrDeducciones"])>0)	

								{

									$this->XML=str_replace("@totalDeducciones",$this->formatearValorMonetarioV2($tDeducciones),$this->XML);

								}

								else

								{

									$this->XML=str_replace('TotalDeducciones="@totalDeducciones"',"",$this->XML);

									$this->XML=str_replace("@totalDeducciones",$this->formatearValorMonetarioV2($tDeducciones),$this->XML);

								}

								

								$this->XML=str_replace("@totalComprobante",$this->formatearValorMonetarioV2($tPercepciones-$tDeducciones),$this->XML);

								

								

								if((isset($this->datosNomina["arrIncapacidades"]))&&(sizeof($this->datosNomina["arrIncapacidades"])>0))

								{

									$this->XML.='<nomina12:Incapacidades>';

									foreach($this->datosNomina["arrIncapacidades"] as $i)

									{

										$i["descuentoIncapacidad"]=$this->formatearValorMonetarioV2($i["descuentoIncapacidad"]);

										$this->XML.='<nomina12:Incapacidad DiasIncapacidad="'.$i["diasIncapacidad"].'" TipoIncapacidad="'.$i["tipoIncapacidad"].'" ImporteMonetario="'.$i["descuentoIncapacidad"].'"/>';

									}

									$this->XML.='</nomina12:Incapacidades>';

									

								

								}

								

								if((isset($this->datosNomina["arrHorasExtra"]))&&(sizeof($this->datosNomina["arrHorasExtra"])>0))

								{

									$this->XML.='<nomina:HorasExtras>';

									foreach($this->datosNomina["arrHorasExtra"] as $h)

									{

										$h["importePagado"]=$this->formatearValorMonetarioV2($h["importePagado"]);

										$this->XML.='<nomina:HorasExtra Dias="'.$h["diasHorasExtras"].'" TipoHoras="'.$h["tipoPagoHoras"].'" HorasExtra="'.$h["totalHorasExtra"].'" ImportePagado="'.$h["importePagado"].'"/>';

									}

									$this->XML.='</nomina:HorasExtras>';

								}

								

									

		$this->XML.=		'</nomina12:Nomina>'.

						'</cfdi:Complemento>'.

					'</cfdi:Comprobante>';

		$this->XML=str_replace("&","&amp;",$this->XML);					

		return $this->XML;	

	}

	

	function convertirXMLCadenaToObj($XLM,$oComp=NULL)

	{

		$obj=$this->generarEstructuraLlenadoXML();

		$XLM=str_replace("&","&amp;",$XLM);

		

		$factura = new SimpleXMLElement($XLM);

		

		$ns = $factura->getNamespaces(true);

		if(!isset($ns["tfd"]))

			$ns["tfd"]="";

		$factura->registerXPathNamespace('tfd', $ns['tfd']);

		$datosComprobante=$factura->xpath('//cfdi:Comprobante')[0];

		if($oComp)

		{

			$obj["idCertificado"]=$oComp["idCertificado"];

			$obj["idSerie"]=$oComp["idSerie"];

			$obj["idClienteFactura"]=$oComp["idClienteFactura"];

		}

		

		if(strpos($XLM,'Version="3.3"')===false)

		{

		

			$obj["sello"]=$this->obtenerValorAtributoArregloAsoc($datosComprobante,"sello");//* Obligatorio

			$obj["formaPago"]=$this->obtenerValorAtributoArregloAsoc($datosComprobante,"formaDePago"); //* Obligatorio

			$obj["noCertificado"]=$this->obtenerValorAtributoArregloAsoc($datosComprobante,"noCertificado"); //* Obligatorio

			$obj["certificado"]=$this->obtenerValorAtributoArregloAsoc($datosComprobante,"certificado"); //* Obligatorio

			$obj["serie"]=$this->obtenerValorAtributoArregloAsoc($datosComprobante,"serie"); 

			$obj["folio"]=$this->obtenerValorAtributoArregloAsoc($datosComprobante,"folio"); 

			$obj["metodoDePago"]=$this->obtenerValorAtributoArregloAsoc($datosComprobante,"metodoDePago"); //* Obligatorio

			$obj["lugarExpedicion"]=$this->obtenerValorAtributoArregloAsoc($datosComprobante,"LugarExpedicion"); //* Obligatorio

			$obj["descuento"]=$this->obtenerValorAtributoArregloAsoc($datosComprobante,"descuento");

			$obj["subtotal"]=$this->obtenerValorAtributoArregloAsoc($datosComprobante,"subTotal"); //* Obligatorio

			$obj["motivoDescuento"]=$this->obtenerValorAtributoArregloAsoc($datosComprobante,"motivoDescuento"); 

			$obj["tipoCambio"]=$this->obtenerValorAtributoArregloAsoc($datosComprobante,"TipoCambio"); //* Obligatorio

			$obj["total"]=$this->obtenerValorAtributoArregloAsoc($datosComprobante,"total"); //* Obligatorio

			$obj["version"]=$this->obtenerValorAtributoArregloAsoc($datosComprobante,"version");

			$obj["fechaComprobante"]=$this->obtenerValorAtributoArregloAsoc($datosComprobante,"fecha");

			$obj["condicionesDePago"]=$this->obtenerValorAtributoArregloAsoc($datosComprobante,"condicionesDePago");

			$obj["tipoDeComprobante"]=$this->obtenerValorAtributoArregloAsoc($datosComprobante,"tipoDeComprobante");

			$obj["moneda"]=$this->obtenerValorAtributoArregloAsoc($datosComprobante,"Moneda");

			$obj["numCtaPago"]=$this->obtenerValorAtributoArregloAsoc($datosComprobante,"NumCtaPago");  

			$obj["folioFiscalOrig"]=$this->obtenerValorAtributoArregloAsoc($datosComprobante,"FolioFiscalOrig");

			$obj["serieFolioFiscalOrig"]=$this->obtenerValorAtributoArregloAsoc($datosComprobante,"SerieFolioFiscalOrig");

			$obj["fechaFolioFiscalOrig"]=$this->obtenerValorAtributoArregloAsoc($datosComprobante,"FechaFolioFiscalOrig");

			$obj["montoFolioFiscalOrig"]=$this->obtenerValorAtributoArregloAsoc($datosComprobante,"MontoFolioFiscalOrig");

			$obj["cfdiRelacionados"]=array();

			$obj["relacDI"]="";

			

			$dEmisor=$factura->xpath('//cfdi:Comprobante//cfdi:Emisor')[0];

			$datosEmisor["rfc"]=$this->obtenerValorAtributoArregloAsoc($dEmisor,"rfc");  //* Obligatorio

			$datosEmisor["razonSocial"]=$this->obtenerValorAtributoArregloAsoc($dEmisor,"nombre");

			$datosEmisor["regimenFiscal"]=array(); //* Obligatorio

			

			$dRegimen=$factura->xpath('//cfdi:Comprobante//cfdi:Emisor//cfdi:RegimenFiscal');

			foreach($dRegimen as $r)

			{

				$regimen=$this->obtenerValorAtributoArregloAsoc($r,"Regimen"); 

				array_push($datosEmisor["regimenFiscal"],$regimen);

			}

			

			$domicilioEmisor=$factura->xpath('//cfdi:Comprobante//cfdi:Emisor//cfdi:DomicilioFiscal')[0];

			

			$datosEmisor["domicilio"]["calle"]=$this->obtenerValorAtributoArregloAsoc($domicilioEmisor,"calle"); //* Obligatorio

			$datosEmisor["domicilio"]["noExterior"]=$this->obtenerValorAtributoArregloAsoc($domicilioEmisor,"noExterior");

			$datosEmisor["domicilio"]["noInterior"]=$this->obtenerValorAtributoArregloAsoc($domicilioEmisor,"noInterior");

			$datosEmisor["domicilio"]["colonia"]=$this->obtenerValorAtributoArregloAsoc($domicilioEmisor,"colonia");

			$datosEmisor["domicilio"]["localidad"]=$this->obtenerValorAtributoArregloAsoc($domicilioEmisor,"localidad");

			$datosEmisor["domicilio"]["municipio"]=$this->obtenerValorAtributoArregloAsoc($domicilioEmisor,"municipio"); //* Obligatorio

			$datosEmisor["domicilio"]["estado"]=$this->obtenerValorAtributoArregloAsoc($domicilioEmisor,"estado"); //* Obligatorio

			$datosEmisor["domicilio"]["pais"]=$this->obtenerValorAtributoArregloAsoc($domicilioEmisor,"pais"); //* Obligatorio

			$datosEmisor["domicilio"]["codigoPostal"]=$this->obtenerValorAtributoArregloAsoc($domicilioEmisor,"codigoPostal"); // Obligatorio

			

			$obj["datosEmisor"]=$datosEmisor;

			

			$dReceptor=$factura->xpath('//cfdi:Comprobante//cfdi:Receptor')[0];

			$domicilioReceptor=$factura->xpath('//cfdi:Comprobante//cfdi:Receptor//cfdi:Domicilio')[0];

			

			$datosReceptor["rfc"]=$this->obtenerValorAtributoArregloAsoc($dReceptor,"rfc"); //* Obligatorio

			$datosReceptor["razonSocial"]=$this->obtenerValorAtributoArregloAsoc($dReceptor,"nombre");

			$datosReceptor["domicilio"]["calle"]=$this->obtenerValorAtributoArregloAsoc($domicilioReceptor,"calle"); 

			$datosReceptor["domicilio"]["noExterior"]=$this->obtenerValorAtributoArregloAsoc($domicilioReceptor,"noExterior");

			$datosReceptor["domicilio"]["noInterior"]=$this->obtenerValorAtributoArregloAsoc($domicilioReceptor,"noInterior");

			$datosReceptor["domicilio"]["colonia"]=$this->obtenerValorAtributoArregloAsoc($domicilioReceptor,"colonia");

			$datosReceptor["domicilio"]["localidad"]=$this->obtenerValorAtributoArregloAsoc($domicilioReceptor,"localidad");

			$datosReceptor["domicilio"]["municipio"]=$this->obtenerValorAtributoArregloAsoc($domicilioReceptor,"municipio"); 

			$datosReceptor["domicilio"]["estado"]=$this->obtenerValorAtributoArregloAsoc($domicilioReceptor,"estado"); 

			$datosReceptor["domicilio"]["pais"]=$this->obtenerValorAtributoArregloAsoc($domicilioReceptor,"pais"); //* Obligatorio

			$datosReceptor["domicilio"]["codigoPostal"]=$this->obtenerValorAtributoArregloAsoc($domicilioReceptor,"codigoPostal"); 

			$obj["datosReceptor"]=$datosReceptor;	

			$arrConceptos=$factura->xpath('//cfdi:Comprobante//cfdi:Concepto');

		

			

			

			$obj["conceptos"]=array();

			foreach($arrConceptos as $c)

			{

				$oConcepto=array();  

				$oConcepto["cantidad"]=$this->obtenerValorAtributoArregloAsoc($c,"cantidad"); //*

				$oConcepto["unidad"]=$this->obtenerValorAtributoArregloAsoc($c,"unidad"); //*

				$oConcepto["claveUnidad"]=$this->obtenerValorAtributoArregloAsoc($c,"unidad"); //*

				$oConcepto["noIdentificacion"]=$this->obtenerValorAtributoArregloAsoc($c,"noIdentificacion");

				$oConcepto["descripcion"]=$this->obtenerValorAtributoArregloAsoc($c,"descripcion");  //*

				$oConcepto["valorUnitario"]=$this->obtenerValorAtributoArregloAsoc($c,"valorUnitario");  //*

				$oConcepto["importe"]=$this->obtenerValorAtributoArregloAsoc($c,"importe"); //*

				$oConcepto["descuentoTotal"]=$this->obtenerValorAtributoArregloAsoc($c,"descuento"); //*

				$oConcepto["claveProdServ"]="";

				$oConcepto["iva"]="";

				$oConcepto["tasaIVA"]="";				

				

				

				array_push($obj["conceptos"],$oConcepto);

			}

			

			

			$obj["impuestos"]=array();

			$obj["impuestos"]["retenciones"]=array();

			$obj["impuestos"]["traslados"]=array();

			

			$arrImpuestosTraslados=$factura->xpath('//cfdi:Comprobante//cfdi:Impuestos//cfdi:Traslados//cfdi:Traslado');

			foreach($arrImpuestosTraslados as $i)

			{

				$oImpuestoTraslado=array();

				$oImpuestoTraslado["impuesto"]=$this->obtenerValorAtributoArregloAsoc($i,"impuesto");//*  IEPS | IVA

				$oImpuestoTraslado["tasa"]=$this->obtenerValorAtributoArregloAsoc($i,"tasa");//*

				$oImpuestoTraslado["importe"]=$this->obtenerValorAtributoArregloAsoc($i,"importe");//*

				array_push($obj["impuestos"]["traslados"],$oImpuestoTraslado);

			}

			$arrImpuestosRetenciones=$factura->xpath('//cfdi:Comprobante//cfdi:Impuestos//cfdi:Retenciones//cfdi:Retencion');

			

			foreach($arrImpuestosRetenciones as $r)

			{

				$oImpuestoRetencion=array();

				$oImpuestoRetencion["impuesto"]=$this->obtenerValorAtributoArregloAsoc($r,"impuesto");//*  ISR | IVA

				$oImpuestoRetencion["importe"]=$this->obtenerValorAtributoArregloAsoc($r,"importe");//*

				array_push($obj["impuestos"]["retenciones"],$oImpuestoRetencion);

			}

			

			

			

			

			

			$obj["versionCertificadoSAT"]="";

			$obj["folioUUID"]="";

			$obj["fechaCertificacionSAT"]="";

			$obj["noCertificadoSAT"]="";

			$obj["selloCFD"]="";

			$obj["SelloDigitalSAT"]="";

			$obj["cadenaOriginalComplententoSAT"]="||".$obj["versionCertificadoSAT"]."|".$obj["folioUUID"]."|".$obj["fechaCertificacionSAT"]."|".$obj["selloCFD"]."|".$obj["noCertificadoSAT"]."|";

			$datosTimbreFiscal=$factura->xpath('//cfdi:Comprobante//cfdi:Complemento//tfd:TimbreFiscalDigital');

			if(sizeof($datosTimbreFiscal)>0)

			{	

				$datosTimbreFiscal=$datosTimbreFiscal[0];

				$obj["versionCertificadoSAT"]=$this->obtenerValorAtributoArregloAsoc($datosTimbreFiscal,"version");

				$obj["folioUUID"]=$this->obtenerValorAtributoArregloAsoc($datosTimbreFiscal,"UUID");

				$obj["fechaCertificacionSAT"]=$this->obtenerValorAtributoArregloAsoc($datosTimbreFiscal,"FechaTimbrado");

				$obj["noCertificadoSAT"]=$this->obtenerValorAtributoArregloAsoc($datosTimbreFiscal,"noCertificadoSAT");

				$obj["selloCFD"]=$this->obtenerValorAtributoArregloAsoc($datosTimbreFiscal,"selloCFD");

				$obj["SelloDigitalSAT"]=$this->obtenerValorAtributoArregloAsoc($datosTimbreFiscal,"selloSAT");

				$obj["cadenaOriginalComplententoSAT"]="||".$obj["versionCertificadoSAT"]."|".$obj["folioUUID"]."|".$obj["fechaCertificacionSAT"]."|".$obj["selloCFD"]."|".$obj["noCertificadoSAT"]."|";

				

			}

		}

		else

		{

			$obj["sello"]=$this->obtenerValorAtributoArregloAsoc($datosComprobante,"Sello");//* Obligatorio

			$obj["formaPago"]=$this->obtenerValorAtributoArregloAsoc($datosComprobante,"FormaPago"); //* Obligatorio

			$obj["noCertificado"]=$this->obtenerValorAtributoArregloAsoc($datosComprobante,"NoCertificado"); //* Obligatorio

			$obj["certificado"]=$this->obtenerValorAtributoArregloAsoc($datosComprobante,"Certificado"); //* Obligatorio

			$obj["serie"]=$this->obtenerValorAtributoArregloAsoc($datosComprobante,"Serie"); 

			$obj["folio"]=$this->obtenerValorAtributoArregloAsoc($datosComprobante,"Folio"); 

			$obj["metodoDePago"]=$this->obtenerValorAtributoArregloAsoc($datosComprobante,"MetodoPago"); //* Obligatorio

			$obj["lugarExpedicion"]=$this->obtenerValorAtributoArregloAsoc($datosComprobante,"LugarExpedicion"); //* Obligatorio

			$obj["descuento"]=$this->obtenerValorAtributoArregloAsoc($datosComprobante,"Descuento");

			$obj["subtotal"]=$this->obtenerValorAtributoArregloAsoc($datosComprobante,"SubTotal"); //* Obligatorio

			

			$obj["tipoCambio"]=$this->obtenerValorAtributoArregloAsoc($datosComprobante,"TipoCambio"); //* Obligatorio

			$obj["total"]=$this->obtenerValorAtributoArregloAsoc($datosComprobante,"Total"); //* Obligatorio

			$obj["version"]=$this->obtenerValorAtributoArregloAsoc($datosComprobante,"Version");

			$obj["fechaComprobante"]=$this->obtenerValorAtributoArregloAsoc($datosComprobante,"Fecha");

			$obj["condicionesDePago"]=$this->obtenerValorAtributoArregloAsoc($datosComprobante,"CondicionesDePago");

			$obj["tipoDeComprobante"]=$this->obtenerValorAtributoArregloAsoc($datosComprobante,"TipoDeComprobante");

			$obj["moneda"]=$this->obtenerValorAtributoArregloAsoc($datosComprobante,"Moneda");

			

			$dRelacionados=$factura->xpath('//cfdi:Comprobante//cfdi:CfdiRelacionados');

			

			$obj["cfdiRelacionados"]=array();

			$obj["relacionCFDI"]="";

			if(sizeof($dRelacionados)>0)

			{

				$dRelacionados=$dRelacionados[0];

				

				$obj["relacionCFDI"]=$this->obtenerValorAtributoArregloAsoc($dRelacionados,"TipoRelacion");

				$aRelaciondos=	$dRelacionados->xpath('//cfdi:CfdiRelacionado');

				foreach($aRelaciondos as $r)

				{

					array_push($obj["cfdiRelacionados"],$this->obtenerValorAtributoArregloAsoc($r,"UUID")); 

				}

				

			}

			

			$dEmisor=$factura->xpath('//cfdi:Comprobante//cfdi:Emisor')[0];

			$datosEmisor["rfc"]=$this->obtenerValorAtributoArregloAsoc($dEmisor,"Rfc");  //* Obligatorio

			$datosEmisor["razonSocial"]=$this->obtenerValorAtributoArregloAsoc($dEmisor,"Nombre");

			$datosEmisor["regimenFiscal"]=array(); //* Obligatorio

			array_push($datosEmisor["regimenFiscal"],$this->obtenerValorAtributoArregloAsoc($dEmisor,"RegimenFiscal"));

			

			

			/*$domicilioEmisor=$factura->xpath('//cfdi:Comprobante//cfdi:Emisor//cfdi:DomicilioFiscal')[0];

			

			$datosEmisor["domicilio"]["calle"]=$this->obtenerValorAtributoArregloAsoc($domicilioEmisor,"calle"); //* Obligatorio

			$datosEmisor["domicilio"]["noExterior"]=$this->obtenerValorAtributoArregloAsoc($domicilioEmisor,"noExterior");

			$datosEmisor["domicilio"]["noInterior"]=$this->obtenerValorAtributoArregloAsoc($domicilioEmisor,"noInterior");

			$datosEmisor["domicilio"]["colonia"]=$this->obtenerValorAtributoArregloAsoc($domicilioEmisor,"colonia");

			$datosEmisor["domicilio"]["localidad"]=$this->obtenerValorAtributoArregloAsoc($domicilioEmisor,"localidad");

			$datosEmisor["domicilio"]["municipio"]=$this->obtenerValorAtributoArregloAsoc($domicilioEmisor,"municipio"); //* Obligatorio

			$datosEmisor["domicilio"]["estado"]=$this->obtenerValorAtributoArregloAsoc($domicilioEmisor,"estado"); //* Obligatorio

			$datosEmisor["domicilio"]["pais"]=$this->obtenerValorAtributoArregloAsoc($domicilioEmisor,"pais"); //* Obligatorio

			$datosEmisor["domicilio"]["codigoPostal"]=$this->obtenerValorAtributoArregloAsoc($domicilioEmisor,"codigoPostal"); // Obligatorio*/

			

			$obj["datosEmisor"]=$datosEmisor;

			

			$dReceptor=$factura->xpath('//cfdi:Comprobante//cfdi:Receptor')[0];

			//$domicilioReceptor=$factura->xpath('//cfdi:Comprobante//cfdi:Receptor//cfdi:Domicilio')[0];

			

			$datosReceptor["rfc"]=$this->obtenerValorAtributoArregloAsoc($dReceptor,"Rfc"); //* Obligatorio

			$datosReceptor["razonSocial"]=$this->obtenerValorAtributoArregloAsoc($dReceptor,"Nombre");

			$obj["usoCFDI"]=$this->obtenerValorAtributoArregloAsoc($dReceptor,"UsoCFDI");

			/*$datosReceptor["domicilio"]["calle"]=$this->obtenerValorAtributoArregloAsoc($domicilioReceptor,"calle"); 

			$datosReceptor["domicilio"]["noExterior"]=$this->obtenerValorAtributoArregloAsoc($domicilioReceptor,"noExterior");

			$datosReceptor["domicilio"]["noInterior"]=$this->obtenerValorAtributoArregloAsoc($domicilioReceptor,"noInterior");

			$datosReceptor["domicilio"]["colonia"]=$this->obtenerValorAtributoArregloAsoc($domicilioReceptor,"colonia");

			$datosReceptor["domicilio"]["localidad"]=$this->obtenerValorAtributoArregloAsoc($domicilioReceptor,"localidad");

			$datosReceptor["domicilio"]["municipio"]=$this->obtenerValorAtributoArregloAsoc($domicilioReceptor,"municipio"); 

			$datosReceptor["domicilio"]["estado"]=$this->obtenerValorAtributoArregloAsoc($domicilioReceptor,"estado"); 

			$datosReceptor["domicilio"]["pais"]=$this->obtenerValorAtributoArregloAsoc($domicilioReceptor,"pais"); //* Obligatorio

			$datosReceptor["domicilio"]["codigoPostal"]=$this->obtenerValorAtributoArregloAsoc($domicilioReceptor,"codigoPostal");*/ 

			$obj["datosReceptor"]=$datosReceptor;	

			$arrConceptos=$factura->xpath('//cfdi:Comprobante//cfdi:Concepto');

		

			

			

			$obj["conceptos"]=array();

			foreach($arrConceptos as $c)

			{

				$oConcepto=array();  

				$oConcepto["descuentoTotal"]=$this->obtenerValorAtributoArregloAsoc($c,"Descuento"); //*

				$oConcepto["claveUnidad"]=$this->obtenerValorAtributoArregloAsoc($c,"ClaveUnidad"); //*

				$oConcepto["unidad"]=$this->obtenerValorAtributoArregloAsoc($c,"Unidad"); //*

				$oConcepto["importe"]=$this->obtenerValorAtributoArregloAsoc($c,"Importe"); //*

				$oConcepto["valorUnitario"]=$this->obtenerValorAtributoArregloAsoc($c,"ValorUnitario");  //*

				$oConcepto["descripcion"]=$this->obtenerValorAtributoArregloAsoc($c,"Descripcion");  //*

				$oConcepto["cantidad"]=$this->obtenerValorAtributoArregloAsoc($c,"Cantidad"); //*

				$oConcepto["claveProdServ"]=$this->obtenerValorAtributoArregloAsoc($c,"ClaveProdServ"); //*				

				$oConcepto["noIdentificacion"]=$this->obtenerValorAtributoArregloAsoc($c,"NoIdentificacion");

				$oConcepto["iva"]="";

				$oConcepto["tasaIVA"]="";

				$trasladoIVA=$c->xpath('//cfdi:Impuestos//cfdi:Traslados//cfdi:Traslado');		

				if(sizeof($trasladoIVA)>0)

				{

					$trasladoIVA=$trasladoIVA[0];

					$oConcepto["iva"]=$this->obtenerValorAtributoArregloAsoc($trasladoIVA,"Importe"); 

					$oConcepto["tasaIVA"]=$this->obtenerValorAtributoArregloAsoc($trasladoIVA,"TasaOCuota"); 

				}

				

				array_push($obj["conceptos"],$oConcepto);

			}

			

			

			$obj["impuestos"]=array();

			$obj["impuestos"]["retenciones"]=array();

			$obj["impuestos"]["traslados"]=array();

			

			$arrImpuestosTraslados=$factura->xpath('//cfdi:Comprobante//cfdi:Impuestos//cfdi:Traslados//cfdi:Traslado');

			foreach($arrImpuestosTraslados as $i)

			{

				if($this->obtenerValorAtributoArregloAsoc($i,"Base")=="")

				{

					$oImpuestoTraslado=array();

					$oImpuestoTraslado["impuesto"]=$this->obtenerValorAtributoArregloAsoc($i,"Impuesto");//*  IEPS | IVA

					$oImpuestoTraslado["tasa"]=$this->obtenerValorAtributoArregloAsoc($i,"TasaOCuota");//*

					$oImpuestoTraslado["importe"]=$this->obtenerValorAtributoArregloAsoc($i,"Importe");//*

					array_push($obj["impuestos"]["traslados"],$oImpuestoTraslado);

				}

			}

			$arrImpuestosRetenciones=$factura->xpath('//cfdi:Comprobante//cfdi:Impuestos//cfdi:Retenciones//cfdi:Retencion');

			

			foreach($arrImpuestosRetenciones as $r)

			{

				if($this->obtenerValorAtributoArregloAsoc($r,"Base")=="")

				{

					$oImpuestoRetencion=array();

					$oImpuestoRetencion["impuesto"]=$this->obtenerValorAtributoArregloAsoc($r,"Impuesto");//*  ISR | IVA

					$oImpuestoRetencion["importe"]=$this->obtenerValorAtributoArregloAsoc($r,"Importe");//*

					array_push($obj["impuestos"]["retenciones"],$oImpuestoRetencion);

				}

			}

			

			

			//varDump(sizeof($factura->xpath('//cfdi:Comprobante//cfdi:Complemento//nomina12:Nomina')));

			

			

			

			

			$obj["versionCertificadoSAT"]="";

			$obj["folioUUID"]="";

			$obj["fechaCertificacionSAT"]="";

			$obj["noCertificadoSAT"]="";

			$obj["selloCFD"]="";

			$obj["SelloDigitalSAT"]="";

			$obj["cadenaOriginalComplententoSAT"]="||".$obj["versionCertificadoSAT"]."|".$obj["folioUUID"]."|".$obj["fechaCertificacionSAT"]."|".$obj["selloCFD"]."|".$obj["noCertificadoSAT"]."|";

			$datosTimbreFiscal=$factura->xpath('//cfdi:Comprobante//cfdi:Complemento//tfd:TimbreFiscalDigital');

			if(sizeof($datosTimbreFiscal)>0)

			{	

				$datosTimbreFiscal=$datosTimbreFiscal[0];

				$obj["versionCertificadoSAT"]=$this->obtenerValorAtributoArregloAsoc($datosTimbreFiscal,"Version");

				$obj["folioUUID"]=$this->obtenerValorAtributoArregloAsoc($datosTimbreFiscal,"UUID");

				$obj["fechaCertificacionSAT"]=$this->obtenerValorAtributoArregloAsoc($datosTimbreFiscal,"FechaTimbrado");

				$obj["noCertificadoSAT"]=$this->obtenerValorAtributoArregloAsoc($datosTimbreFiscal,"NoCertificadoSAT");

				$obj["selloCFD"]=$this->obtenerValorAtributoArregloAsoc($datosTimbreFiscal,"SelloCFD");

				$obj["SelloDigitalSAT"]=$this->obtenerValorAtributoArregloAsoc($datosTimbreFiscal,"SelloSAT");

				$obj["cadenaOriginalComplententoSAT"]="||".$obj["versionCertificadoSAT"]."|".$obj["folioUUID"]."|".$obj["fechaCertificacionSAT"]."|".$obj["selloCFD"]."|".$obj["noCertificadoSAT"]."|";

				

			}

		}

		

		

		/*$datosNominaXML=$factura->xpath('//cfdi:Comprobante//cfdi:Complemento//nomina:Nomina')[0];
		$datosNomina["version"]=$this->obtenerValorAtributoArregloAsoc($datosNominaXML,"Version");

		if($datosNomina["version"]=="")

		{

			$datosNominaXML=$factura->xpath('//cfdi:Comprobante//cfdi:Complemento//nomina12:Nomina')[0];

			$datosNomina["version"]=$this->obtenerValorAtributoArregloAsoc($datosNominaXML,"Version");		

		}*/

		$datosNominaXML=$factura->xpath('//cfdi:Comprobante//cfdi:Complemento//nomina12:Nomina')[0];

		$datosNomina["version"]=$this->obtenerValorAtributoArregloAsoc($datosNominaXML,"Version");
		

		$datosNomina["curp"]=$this->obtenerValorAtributoArregloAsoc($datosNominaXML,"Curp"); //* Obligatorio

		$datosNomina["tipoRegimen"]=$this->obtenerValorAtributoArregloAsoc($datosNominaXML,"TipoRegimen"); //* Obligatorio

		$datosNomina["fechaPago"]=$this->obtenerValorAtributoArregloAsoc($datosNominaXML,"FechaPago"); //* Obligatorio

		$datosNomina["numEmpleado"]=$this->obtenerValorAtributoArregloAsoc($datosNominaXML,"NumEmpleado"); //* Obligatorio

		$datosNomina["fechaInicioPago"]=$this->obtenerValorAtributoArregloAsoc($datosNominaXML,"FechaInicialPago"); //* Obligatorio

		$datosNomina["fechaFinPago"]=$this->obtenerValorAtributoArregloAsoc($datosNominaXML,"FechaFinalPago"); //* Obligatorio

		$datosNomina["numDiasPagados"]=$this->obtenerValorAtributoArregloAsoc($datosNominaXML,"NumDiasPagados"); //* Obligatorio

		$datosNomina["periodicidadPago"]=$this->obtenerValorAtributoArregloAsoc($datosNominaXML,"PeriodicidadPago"); //* Obligatorio

		$datosNomina["registroPatronal"]=$this->obtenerValorAtributoArregloAsoc($datosNominaXML,"RegistroPatronal");

		$datosNomina["numSS"]=$this->obtenerValorAtributoArregloAsoc($datosNominaXML,"NumSeguridadSocial");

		$datosNomina["departamento"]=$this->obtenerValorAtributoArregloAsoc($datosNominaXML,"Departamento");

		$datosNomina["clabe"]=$this->obtenerValorAtributoArregloAsoc($datosNominaXML,"CLABE");

		$datosNomina["banco"]=$this->obtenerValorAtributoArregloAsoc($datosNominaXML,"Banco");

		$datosNomina["fechaInicioRelLaboral"]=$this->obtenerValorAtributoArregloAsoc($datosNominaXML,"FechaInicioRelLaboral");

		$datosNomina["puesto"]=$this->obtenerValorAtributoArregloAsoc($datosNominaXML,"Puesto");

		$datosNomina["tipoContrato"]=$this->obtenerValorAtributoArregloAsoc($datosNominaXML,"TipoContrato");

		$datosNomina["tipoJornada"]=$this->obtenerValorAtributoArregloAsoc($datosNominaXML,"TipoJornada");

		$datosNomina["riezgoPuesto"]=$this->obtenerValorAtributoArregloAsoc($datosNominaXML,"RiesgoPuesto");

		$datosNomina["antiguedad"]=$this->obtenerValorAtributoArregloAsoc($datosNominaXML,"Antiguedad");

		$datosNomina["salarioBaseCotApor"]=$this->obtenerValorAtributoArregloAsoc($datosNominaXML,"SalarioBaseCotApor");

		$datosNomina["sdi"]=$this->obtenerValorAtributoArregloAsoc($datosNominaXML,"SalarioDiarioIntegrado");

		

		if($datosNomina["version"]=="1.2")

		{

			$datosNominaXML=$factura->xpath('//cfdi:Comprobante//cfdi:Complemento//nomina12:Nomina//nomina12:Receptor')[0];

			$datosNomina["curp"]=$this->obtenerValorAtributoArregloAsoc($datosNominaXML,"Curp"); //* Obligatorio

			$datosNomina["tipoRegimen"]=$this->obtenerValorAtributoArregloAsoc($datosNominaXML,"TipoRegimen"); //* Obligatorio

			$datosNomina["numEmpleado"]=$this->obtenerValorAtributoArregloAsoc($datosNominaXML,"NumEmpleado"); //* Obligatorio

			$datosNomina["periodicidadPago"]=$this->obtenerValorAtributoArregloAsoc($datosNominaXML,"PeriodicidadPago"); //* Obligatorio

			//$datosNomina["registroPatronal"]=$this->obtenerValorAtributoArregloAsoc($datosNominaXML,"RegistroPatronal");

			$datosNomina["numSS"]=$this->obtenerValorAtributoArregloAsoc($datosNominaXML,"NumSeguridadSocial");

			//$datosNomina["departamento"]=$this->obtenerValorAtributoArregloAsoc($datosNominaXML,"Departamento");

			$datosNomina["puesto"]=$this->obtenerValorAtributoArregloAsoc($datosNominaXML,"Puesto");

			$datosNomina["tipoContrato"]=$this->obtenerValorAtributoArregloAsoc($datosNominaXML,"TipoContrato");

			//$datosNomina["tipoJornada"]=$this->obtenerValorAtributoArregloAsoc($datosNominaXML,"TipoJornada");

			//$datosNomina["sdi"]=$this->obtenerValorAtributoArregloAsoc($datosNominaXML,"SalarioDiarioIntegrado");

		}

		

		

		$datosNomina["arrPercepciones"]=array();

		
		$datosPercepciones=NULL;
		if($datosNomina["version"]=="1.2")

			$datosPercepciones=$factura->xpath('//cfdi:Comprobante//cfdi:Complemento//nomina12:Nomina//nomina12:Percepciones//nomina12:Percepcion');
		else	
			$datosPercepciones=$factura->xpath('//cfdi:Comprobante//cfdi:Complemento//nomina:Nomina//nomina:Percepciones//nomina:Percepcion');



		

		foreach($datosPercepciones as $p)

		{

			$objConceptoNomina=array();

			$objConceptoNomina["tipoPercepcion"]=$this->obtenerValorAtributoArregloAsoc($p,"TipoPercepcion");//tipoDeduccion   //* Obligatorio

			$objConceptoNomina["clave"]=$this->obtenerValorAtributoArregloAsoc($p,"Clave");	//* Obligatorio

			$objConceptoNomina["descripcion"]=$this->obtenerValorAtributoArregloAsoc($p,"Concepto");	//* Obligatorio

			$objConceptoNomina["importeGravado"]=$this->obtenerValorAtributoArregloAsoc($p,"ImporteGravado");	//* Obligatorio

			$objConceptoNomina["importeExento"]=$this->obtenerValorAtributoArregloAsoc($p,"ImporteExento");	//* Obligatorio

			array_push($datosNomina["arrPercepciones"],$objConceptoNomina);

		}

		

		$datosNomina["arrDeducciones"]=array();

		

		

		$datosPercepciones=NULL;
		if($datosNomina["version"]=="1.2")
			$datosPercepciones=$factura->xpath('//cfdi:Comprobante//cfdi:Complemento//nomina12:Nomina//nomina12:Deducciones//nomina12:Deduccion');
		else
			$datosPercepciones=$factura->xpath('//cfdi:Comprobante//cfdi:Complemento//nomina:Nomina//nomina:Deducciones//nomina:Deduccion');
		
		

		

		foreach($datosPercepciones as $p)

		{

			$objConceptoNomina=array();

			$objConceptoNomina["tipoDeduccion"]=$this->obtenerValorAtributoArregloAsoc($p,"TipoDeduccion");//tipoDeduccion   //* Obligatorio

			$objConceptoNomina["clave"]=$this->obtenerValorAtributoArregloAsoc($p,"Clave");	//* Obligatorio

			$objConceptoNomina["descripcion"]=$this->obtenerValorAtributoArregloAsoc($p,"Concepto");	//* Obligatorio

			$objConceptoNomina["importeGravado"]=$this->obtenerValorAtributoArregloAsoc($p,"ImporteGravado");	//* Obligatorio

			$objConceptoNomina["importeExento"]=$this->obtenerValorAtributoArregloAsoc($p,"ImporteExento");	//* Obligatorio

			if($datosNomina["version"]=="1.2")

				$objConceptoNomina["importeExento"]=$this->obtenerValorAtributoArregloAsoc($p,"Importe");	//* Obligatorio

			array_push($datosNomina["arrDeducciones"],$objConceptoNomina);

		}

		

		$datosNomina["arrIncapacidades"]=array();

		

		

		$datos=NULL;

		if($datosNomina["version"]=="1.2")

			$datos=$factura->xpath('//cfdi:Comprobante//cfdi:Complemento//nomina12:Nomina//nomina12:Incapacidades//nomina12:Incapacidad');
		else
			$datos=$factura->xpath('//cfdi:Comprobante//cfdi:Complemento//nomina12:Nomina//nomina12:Incapacidades//nomina12:Incapacidad');
	
		foreach($datos as $p)

		{

			$objIncapacidad=array();

			$objIncapacidad["diasIncapacidad"]=$this->obtenerValorAtributoArregloAsoc($p,"DiasIncapacidad"); //* Obligatorio

			$objIncapacidad["tipoIncapacidad"]=$this->obtenerValorAtributoArregloAsoc($p,"TipoIncapacidad"); //* Obligatorio

			$objIncapacidad["descuentoIncapacidad"]=$this->obtenerValorAtributoArregloAsoc($p,"Descuento"); //* Obligatorio

		

			array_push($datosNomina["arrIncapacidades"],$objIncapacidad);

		}

		

		$datosNomina["arrHorasExtra"]=array();

		$datos=NULL;
		if($datosNomina["version"]=="1.2")
			$datos=$factura->xpath('//cfdi:Comprobante//cfdi:Complemento//nomina12:Nomina//nomina12:HorasExtras//nomina12:HorasExtra');
		else
			$datos=$factura->xpath('//cfdi:Comprobante//cfdi:Complemento//nomina:Nomina//nomina:HorasExtras//nomina:HorasExtra');

		foreach($datos as $p)

		{

			$objHorasExtra["diasHorasExtras"]=$this->obtenerValorAtributoArregloAsoc($p,"Dias"); //* Obligatorio 

			$objHorasExtra["tipoPagoHoras"]=$this->obtenerValorAtributoArregloAsoc($p,"TipoHoras"); //* Obligatorio

			$objHorasExtra["totalHorasExtra"]=$this->obtenerValorAtributoArregloAsoc($p,"HorasExtra"); //* Obligatorio

			$objHorasExtra["importePagado"]=$this->obtenerValorAtributoArregloAsoc($p,"ImportePagado"); //* Obligatorio



			array_push($datosNomina["arrHorasExtra"],$objHorasExtra);

		}

		$obj["datosNomina"]=$datosNomina;

		

		

		return $obj;

	}

		

	function validarXMLNomina($XML)

	{

		

		$factura = new SimpleXMLElement($XML);

		$ns = $factura->getNamespaces(true);

		if(!isset($ns["tfd"]))

			$ns["tfd"]="";

		$factura->registerXPathNamespace('tfd', $ns['tfd']);

		

		$arrValoresObligatorios=array();

		/*$this->agregarValorValidacion($arrValoresObligatorios,"//cfdi:Comprobante","sello","Sello");

		$this->agregarValorValidacion($arrValoresObligatorios,"//cfdi:Comprobante","formaDePago","Forma de pago");

		$this->agregarValorValidacion($arrValoresObligatorios,"//cfdi:Comprobante","noCertificado","No. de certificado");

		$this->agregarValorValidacion($arrValoresObligatorios,"//cfdi:Comprobante","certificado","Certificado");

		$this->agregarValorValidacion($arrValoresObligatorios,"//cfdi:Comprobante","metodoDePago","Método de pago");

		$this->agregarValorValidacion($arrValoresObligatorios,"//cfdi:Comprobante","LugarExpedicion","Lugar de expedición");

		$this->agregarValorValidacion($arrValoresObligatorios,"//cfdi:Comprobante","subTotal", "Subtotal");

		$this->agregarValorValidacion($arrValoresObligatorios,"//cfdi:Comprobante","TipoCambio","Tipo de cambio");

		$this->agregarValorValidacion($arrValoresObligatorios,"//cfdi:Comprobante","total","Total");

		$this->agregarValorValidacion($arrValoresObligatorios,"//cfdi:Comprobante//cfdi:Emisor","rfc","RFC del Emisor",12,13);

		$this->agregarValorValidacion($arrValoresObligatorios,"//cfdi:Comprobante//cfdi:Emisor//cfdi:DomicilioFiscal","calle","Calle del domicilio del Emisor");

		$this->agregarValorValidacion($arrValoresObligatorios,"//cfdi:Comprobante//cfdi:Emisor//cfdi:DomicilioFiscal","municipio","Municipio del domicilio del Emisor");

		$this->agregarValorValidacion($arrValoresObligatorios,"//cfdi:Comprobante//cfdi:Emisor//cfdi:DomicilioFiscal","estado","Estado del domicilio del Emisor");

		$this->agregarValorValidacion($arrValoresObligatorios,"//cfdi:Comprobante//cfdi:Emisor//cfdi:DomicilioFiscal","pais","País del domicilio del Emisor");

		$this->agregarValorValidacion($arrValoresObligatorios,"//cfdi:Comprobante//cfdi:Emisor//cfdi:DomicilioFiscal","codigoPostal","C.P. del domicilio del Emisor",5);

		$this->agregarValorValidacion($arrValoresObligatorios,"//cfdi:Comprobante//cfdi:Receptor","rfc","RFC del Receptor",12,13);

		$this->agregarValorValidacion($arrValoresObligatorios,"//cfdi:Comprobante//cfdi:Receptor//cfdi:Domicilio","pais","País del domicilio del Receptor");

		$this->agregarValorValidacion($arrValoresObligatorios,"//cfdi:Comprobante//cfdi:Complemento//nomina:Nomina","CURP","CURP del Empleado",18);

		$this->agregarValorValidacion($arrValoresObligatorios,"//cfdi:Comprobante//cfdi:Complemento//nomina:Nomina","TipoRegimen","Tipo de régimen de contratación del Empleado");

		$this->agregarValorValidacion($arrValoresObligatorios,"//cfdi:Comprobante//cfdi:Complemento//nomina:Nomina","FechaPago","Fecha de pago del Empleado");

		$this->agregarValorValidacion($arrValoresObligatorios,"//cfdi:Comprobante//cfdi:Complemento//nomina:Nomina","NumEmpleado","No. de empleado del Empleado");

		$this->agregarValorValidacion($arrValoresObligatorios,"//cfdi:Comprobante//cfdi:Complemento//nomina:Nomina","FechaInicialPago","Fecha inicial de pago del Empleado");

		$this->agregarValorValidacion($arrValoresObligatorios,"//cfdi:Comprobante//cfdi:Complemento//nomina:Nomina","FechaFinalPago","Fecha final de pago del Empleado");

		$this->agregarValorValidacion($arrValoresObligatorios,"//cfdi:Comprobante//cfdi:Complemento//nomina:Nomina","NumDiasPagados","Num. días pagados del Empleado");

		$this->agregarValorValidacion($arrValoresObligatorios,"//cfdi:Comprobante//cfdi:Complemento//nomina:Nomina","PeriodicidadPago","Periodicidad de pago del Empleado");

		*/

		$arrErrores=array();		



		foreach($arrValoresObligatorios as $o)

		{

			$nodoXML=$factura->xpath($o["ruta"]);

			$nodoXML=$nodoXML[0];

			$valor=$this->obtenerValorAtributoArregloAsoc($nodoXML,$o["campo"]);

			if(trim($valor)=="")

			{

				array_push($arrErrores,"El campo ".$o["leyenda"]." no puede ser vacío");

			}

			else

			{

				if($o["longMin"]!="")	

				{

					$lValor=strlen(trim($valor));

					if(($lValor<$o["longMin"])||($lValor>$o["longMax"]))

					{

						$longitud=$o["longMax"]." caracteres";

						if($o["longMin"]!=$o["longMax"])

						{

							$longitud=$o["longMin"]." a ".$longitud;

						}

						array_push($arrErrores,"La longitud del campo ".$o["leyenda"]." es de ".$longitud);

					}

						

				}

			}

		}

		

	

		$oResultado["errores"]=false;

		$oResultado["arrErrores"]="";

		if(sizeof($arrErrores)>0)

		{

			$oResultado["errores"]=true;

			$err="Se presentan los siguientes errores: ";

			$lblCampos="";

			foreach($arrErrores as $e)

			{

				if($lblCampos=="")

					$lblCampos=$e;

				else

					$lblCampos.=", ".$e;

			}

			$oResultado["arrErrores"]=$err.$lblCampos;

		}

		

		return $oResultado;

		

		

		

		

			

	}

		

	function agregarValorValidacion(&$arreglo,$ruta,$campo,$leyenda,$longMin="",$longMax="")

	{

		$obj=array();

		$obj["ruta"]=$ruta;

		$obj["campo"]=$campo;

		$obj["leyenda"]=$leyenda;

		$obj["longMin"]=$longMin;

		if(($longMax=="")&&($longMin!=""))

			$longMax=$longMin;

			

		$obj["longMax"]=$longMax;

		array_push($arreglo,$obj);

	}

	

}

?>