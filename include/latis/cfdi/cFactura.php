<?php include_once("latis/cfdi/cComprobante.php");

$consulta="SELECT archivoIncludePHP FROM 6956_complementosComprobantes";
$res=$con->obtenerFilas($consulta);
while($fila=mysql_fetch_row($res))
{
	include_once($fila[0]);
}

class cFacturaCFDI extends cComprobante
{
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

	
	var $XML;
	
	var $condicionesDePago;
	var $numCtaPago;
	var $folioFiscalOrig;
	var $serieFolioFiscalOrig;
	var $fechaFolioFiscalOrig;
	var $montoFolioFiscalOrig;
	var $impuestos;
	var $arrConceptos;
	
	var $idCertificado;
	var $idSerie;

	var $idClienteFactura;
	var $objComprobante;
	
	
	function cFacturaCFDI($version="3.2")
	{
		$this->version=$version;

		$this->fechaActual=date("Y-m-d\TH:i:s");
		$this->tipoDeComprobante="ingreso";
		$this->moneda="PESOS";
		$this->XML="";
		$this->idCertificado=-1;
		$this->idComprobante=-1;
		
	}
	
	function setObjFactura($obj)
	{
		
		$this->objComprobante=$obj;
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
		
		$this->arrConceptos=$obj["conceptos"];
		$this->condicionesDePago=$obj["condicionesDePago"];
		$this->numCtaPago=$obj["numCtaPago"];  
		$this->folioFiscalOrig=$obj["folioFiscalOrig"];
		$this->serieFolioFiscalOrig=$obj["serieFolioFiscalOrig"];
		$this->fechaFolioFiscalOrig=$obj["fechaFolioFiscalOrig"];
		$this->montoFolioFiscalOrig=$obj["montoFolioFiscalOrig"];
		$this->impuestos=array();
		if(isset($obj["impuestos"]))
			$this->impuestos=$obj["impuestos"];
		
		if(isset($obj["moneda"]))
			$this->moneda=$obj["moneda"];
		if(isset($obj["tipoDeComprobante"]))
			$this->tipoDeComprobante=$obj["tipoDeComprobante"];
		if(isset($obj["fechaComprobante"])&&($obj["fechaComprobante"]!=""))
			$this->fechaActual=$obj["fechaComprobante"];
		if(isset($obj["version"])&&($obj["version"]!=""))
			$this->version=$obj["version"];
			
		$this->idCertificado=$obj["idCertificado"];
		$this->idSerie=$obj["idSerie"];
		
		$this->idClienteFactura=$obj["idClienteFactura"];
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
		$obj["moneda"]="";
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
		
		$obj["conceptos"]=array();

		/*
		$oConcepto=array();  
		$oConcepto["cantidad"]=""; //*
		$oConcepto["unidad"]=""; //*
		$oConcepto["noIdentificacion"]="";
		$oConcepto["descripcion"]="";  //*
		$oConcepto["valorUnitario"]="";  //*
		$oConcepto["importe"]=""; //*
		*/
		
		$obj["impuestos"]=array();
		$obj["impuestos"]["retenciones"]=array();
		$obj["impuestos"]["traslados"]=array();
		/*
		$oImpuestoRetencion=array();
		$oImpuestoRetencion["impuesto"]="";//*  ISR | IVA
		$oImpuestoRetencion["importe"]="";//*
		
		
		$oImpuestoTraslado=array();
		$oImpuestoTraslado["impuesto"]="";//*  IEPS | IVA
		$oImpuestoTraslado["tasa"]="";//*
		$oImpuestoTraslado["importe"]="";//*
		
		*/
		
		
		return $obj;
	}
	
	function generarXML()
	{
		switch($this->version)
		{
			case '3.2':
				return $this->generarXMLV3_2();
			break;
			case '3.3':	
				return $this->generarXMLV3_3();
			break;
			
		}
		return "";	
	}
	
	function generarXMLV3_2()
	{
		global $con;	
		$condicionesDePago=$this->formatearValorElemento("condicionesDePago",$this->condicionesDePago);
		$numCtaPago=$this->formatearValorElemento("NumCtaPago",$this->numCtaPago);
		$folioFiscalOrig=$this->formatearValorElemento("FolioFiscalOrig",$this->folioFiscalOrig);
		$serieFolioFiscalOrig=$this->formatearValorElemento("SerieFolioFiscalOrig",$this->serieFolioFiscalOrig);
		$fechaFolioFiscalOrig=$this->formatearValorElemento("FechaFolioFiscalOrig",$this->fechaFolioFiscalOrig);
		$montoFolioFiscalOrig=$this->formatearValorElemento("MontoFolioFiscalOrig",$this->montoFolioFiscalOrig);
		$motivoDescuento=$this->formatearValorElemento("motivoDescuento",$this->motivoDescuento);
		$atributosOpcionalesCFDI=$condicionesDePago.$numCtaPago.$folioFiscalOrig.$serieFolioFiscalOrig.$fechaFolioFiscalOrig.$montoFolioFiscalOrig.$motivoDescuento;
		
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
		
		//$consulta="SELECT clave FROM 710_metodoPagoComprobante WHERE formaPago='".$this->metodoDePago."'";
		$metodoPago=$this->metodoDePago;//$con->obtenerValor($consulta);
		
		$this->XML=	'<cfdi:Comprobante xmlns:notariospublicos="http://www.sat.gob.mx/notariospublicos" xmlns:nomina="http://www.sat.gob.mx/nomina" xmlns:cfdi="http://www.sat.gob.mx/cfd/3" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '.
					'xsi:schemaLocation="http://www.sat.gob.mx/cfd/3 http://www.sat.gob.mx/sitio_internet/cfd/3/cfdv32.xsd http://www.sat.gob.mx/nomina http://www.sat.gob.mx/sitio_internet/cfd/nomina/nomina11.xsd" '.
					'version="'.$this->version.'" serie="'.$this->serie.'" folio="'.$this->folio.'" fecha="'.$this->fechaActual.'" sello="@selloDigital" '. 
					'formaDePago="'.$this->formaPago.'" noCertificado="'.$this->noCertificado.'" certificado="'.$this->certificado.'" '.
					'subTotal="'.$this->formatearValorMonetario($this->subtotal).'" descuento="'.$this->formatearValorMonetario($this->descuento).'"  TipoCambio="'.$this->tipoCambio.'" Moneda="'.$this->moneda.'" total="'.$this->formatearValorMonetario($this->total).
					'" tipoDeComprobante="'.$this->tipoDeComprobante.'" metodoDePago="'.$metodoPago.'" LugarExpedicion="'.$this->lugarExpedicion.'" '.$atributosOpcionalesCFDI.
					'>'.
						'<cfdi:Emisor rfc="'.$this->emisor["rfc"].'" nombre="'.$this->emisor["razonSocial"].'">'.
							'<cfdi:DomicilioFiscal calle="'.$this->emisor["domicilio"]["calle"].'" '.$noExterior.' '.$noInterior.' '.$colonia.' '.$localidad.' '.
							'codigoPostal="'.$this->emisor["domicilio"]["codigoPostal"].'" estado="'.$this->emisor["domicilio"]["estado"].'" municipio="'.$this->emisor["domicilio"]["municipio"].
							'" pais="'.$this->emisor["domicilio"]["pais"].'" />';
						foreach($this->emisor["regimenFiscal"] as $r)
						{
							$this->XML.='<cfdi:RegimenFiscal Regimen="'.$r.'"/>';
						}
			$this->XML.=
						'</cfdi:Emisor>'.
						'<cfdi:Receptor nombre="'.$this->receptor["razonSocial"].'" rfc="'.$this->receptor["rfc"].'">'.
							'<cfdi:Domicilio '.$domicilio.' pais="'.$this->receptor["domicilio"]["pais"].'"/>'.
						'</cfdi:Receptor>'.
						'<cfdi:Conceptos>';
						
						foreach($this->arrConceptos as $c)
						{
							$noIdentificacion=$this->formatearValorElemento("noIdentificacion",$c["noIdentificacion"]);
							$this->XML.='<cfdi:Concepto cantidad="'.$this->formatearValorMonetario($c["cantidad"]).'" descripcion="'.$c["descripcion"].'" valorUnitario="'.$this->formatearValorMonetario($c["valorUnitario"]).'" importe="'.$this->formatearValorMonetario($c["importe"]).
										'"  unidad="'.$c["unidad"].'" '.$noIdentificacion.'/>';
						}
							
							
						$this->XML.='</cfdi:Conceptos>';
						
						
						
						
						$this->XML.='<cfdi:Impuestos totalImpuestosRetenidos="@totalRetenido" totalImpuestosTrasladados="@totalTraslado">';
						$totalRetenciones=0;
						$totalTraslado=0;
						
						if(isset($this->impuestos["retenciones"])&&(sizeof($this->impuestos["retenciones"])>0))
						{
							$this->XML.='<cfdi:Retenciones>';
							
							foreach($this->impuestos["retenciones"] as $r)
							{
								$totalRetenciones+=$r["importe"];
								$this->XML.='<cfdi:Retencion impuesto="'.$r["impuesto"].'" importe="'.$this->formatearValorMonetario($r["importe"]).'"/>';
							}
							$this->XML.='</cfdi:Retenciones>';
						}
						
									
						if(isset($this->impuestos["traslados"])&&(sizeof($this->impuestos["traslados"])>0))
						{	
							$this->XML.='<cfdi:Traslados>';
							
							
							foreach($this->impuestos["traslados"] as $t)
							{
								$totalTraslado+=$t["importe"];
								$this->XML.='<cfdi:Traslado impuesto="'.$t["impuesto"].'" tasa="'.$this->formatearValorMonetario($t["tasa"]).'" importe="'.$this->formatearValorMonetario($t["importe"]).'"/>';
							}
							$this->XML.='</cfdi:Traslados>';
							
						}
						$this->XML=str_replace("@totalRetenido",$this->formatearValorMonetario($totalRetenciones),$this->XML);			
						$this->XML=str_replace("@totalTraslado",$this->formatearValorMonetario($totalTraslado),$this->XML);			
						$this->XML.='</cfdi:Impuestos>';
						
						
						if(isset($this->objComprobante["complemento"])&&$this->objComprobante["complemento"]!=0)
						{
							$complemento="";
							
							
							$consulta="SELECT funcionGeneradoraComplemento FROM 6956_complementosComprobantes where idRegistro=".$this->objComprobante["complemento"];
							$funcionGeneradoraComplemento=$con->obtenerValor($consulta);
							
							eval('$complemento='.$funcionGeneradoraComplemento.'($this->objComprobante);');
							if($complemento!="")
							{
								$this->XML.='<cfdi:Complemento>';
									$this->XML.=$complemento;	
								$this->XML.='</cfdi:Complemento>';
							}
							
							
						}
						
					$this->XML.='</cfdi:Comprobante>';
					$this->XML=str_replace("&","&amp;",$this->XML);
		return $this->XML;	
	}
	
	function generarXMLV3_3()
	{
		global $con;	
		/*$condicionesDePago=$this->formatearValorElemento("condicionesDePago",$this->condicionesDePago);
		$numCtaPago=$this->formatearValorElemento("NumCtaPago",$this->numCtaPago);
		$folioFiscalOrig=$this->formatearValorElemento("FolioFiscalOrig",$this->folioFiscalOrig);
		$serieFolioFiscalOrig=$this->formatearValorElemento("SerieFolioFiscalOrig",$this->serieFolioFiscalOrig);
		$fechaFolioFiscalOrig=$this->formatearValorElemento("FechaFolioFiscalOrig",$this->fechaFolioFiscalOrig);
		$montoFolioFiscalOrig=$this->formatearValorElemento("MontoFolioFiscalOrig",$this->montoFolioFiscalOrig);
		$motivoDescuento=$this->formatearValorElemento("motivoDescuento",$this->motivoDescuento);
		$atributosOpcionalesCFDI=$condicionesDePago.$numCtaPago.$folioFiscalOrig.$serieFolioFiscalOrig.$fechaFolioFiscalOrig.$montoFolioFiscalOrig.$motivoDescuento;*/
		
		$atributosOpcionalesCFDI="";
		
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
		
		
		$metodoPago=$this->metodoDePago;//$con->obtenerValor($consulta);
		
		$regimenFiscal="";
		foreach($this->emisor["regimenFiscal"] as $r)
		{
			$regimenFiscal=$r;
			break;
		}
		
		$this->XML=	'<cfdi:Comprobante xmlns:notariospublicos="http://www.sat.gob.mx/notariospublicos" xmlns:nomina="http://www.sat.gob.mx/nomina" xmlns:cfdi="http://www.sat.gob.mx/cfd/3" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '.
					'xsi:schemaLocation="http://www.sat.gob.mx/cfd/3 http://www.sat.gob.mx/sitio_internet/cfd/3/cfdv33.xsd http://www.sat.gob.mx/nomina http://www.sat.gob.mx/sitio_internet/cfd/nomina/nomina12.xsd" '.
					'Version="3.3" Serie="'.$this->serie.'" Folio="'.$this->folio.'" Fecha="'.$this->fechaActual.'" Sello="@selloDigital" '. 
					'FormaPago="'.$this->formaPago.'" NoCertificado="'.$this->noCertificado.'" Certificado="'.$this->certificado.'" '.
					'SubTotal="'.$this->formatearValorMonetarioV2($this->subtotal).'" Descuento="'.$this->formatearValorMonetarioV2($this->descuento).'"   Moneda="'.$this->moneda.'" Total="'.$this->formatearValorMonetarioV2($this->total).
					'" TipoDeComprobante="'.$this->tipoDeComprobante.'" MetodoPago="'.$metodoPago.'" LugarExpedicion="'.$this->lugarExpedicion.'" '.$atributosOpcionalesCFDI.
					'>'.
						'<cfdi:Emisor Rfc="'.$this->emisor["rfc"].'" Nombre="'.$this->emisor["razonSocial"].'" RegimenFiscal="'.$regimenFiscal.'"></cfdi:Emisor>'.
						'<cfdi:Receptor Nombre="'.$this->receptor["razonSocial"].'" Rfc="'.$this->receptor["rfc"].'" UsoCFDI="'.$this->objComprobante["usoCFDI"].'"></cfdi:Receptor>'.
						'<cfdi:Conceptos>';
						
						foreach($this->arrConceptos as $c)
						{
							$noIdentificacion=$this->formatearValorElemento("noIdentificacion",$c["noIdentificacion"]);
							$this->XML.='<cfdi:Concepto ClaveProdServ="'.$c["claveProdServ"].'" Cantidad="'.$this->formatearValorMonetarioV2($c["cantidad"]).
										'" Descripcion="'.$c["descripcion"].'" ValorUnitario="'.$this->formatearValorMonetarioV2($c["valorUnitario"]).
										'" Importe="'.$this->formatearValorMonetarioV2($c["importe"]).
										'" Descuento="'.$this->formatearValorMonetarioV2($c["descuentoTotal"]).'" Unidad="'.$c["unidad"].'" ClaveUnidad="'.$c["claveUnidad"].'" '.$noIdentificacion.'>';
										
							if(($c["tasaIVA"]!="")&&($c["tasaIVA"]>0))
							{
								$this->XML.='<cfdi:Impuestos><cfdi:Traslados><cfdi:Traslado Base="'.$this->formatearValorMonetarioV2($c["importe"]).'" Impuesto="002" TipoFactor="Tasa" TasaOCuota="'.
											$this->formatearValorMonetarioV2($c["tasaIVA"]).'" Importe="'.$this->formatearValorMonetarioV2($c["iva"]).'"/></cfdi:Traslados></cfdi:Impuestos>';				
							}
							$this->XML.='</cfdi:Concepto>';					
						}
							
							
						$this->XML.='</cfdi:Conceptos>';
						
						$xmlImpuestos="";
						if((isset($this->impuestos["retenciones"])&&(sizeof($this->impuestos["retenciones"])>0))||
							(isset($this->impuestos["traslados"])&&(sizeof($this->impuestos["traslados"])>0)))
						{
						
						
							$xmlImpuestos.='<cfdi:Impuestos TotalImpuestosRetenidos="@totalRetenido" TotalImpuestosTrasladados="@totalTraslado">';
							$totalRetenciones=0;
							$totalTraslado=0;
							
							if(isset($this->impuestos["retenciones"])&&(sizeof($this->impuestos["retenciones"])>0))
							{
								$xmlImpuestos.='<cfdi:Retenciones>';
								
								foreach($this->impuestos["retenciones"] as $r)
								{
									$totalRetenciones+=$r["importe"];
									$xmlImpuestos.='<cfdi:Retencion Impuesto="'.$r["impuesto"].'" Importe="'.$this->formatearValorMonetarioV2($r["importe"]).'"/>';
								}
								$xmlImpuestos.='</cfdi:Retenciones>';
							}
							
										
							if(isset($this->impuestos["traslados"])&&(sizeof($this->impuestos["traslados"])>0))
							{	
								$xmlImpuestos.='<cfdi:Traslados>';
								
								
								foreach($this->impuestos["traslados"] as $t)
								{
									$totalTraslado+=$t["importe"];
									$xmlImpuestos.='<cfdi:Traslado Impuesto="'.$t["impuesto"].'" TipoFactor="Tasa" TasaOCuota="'.$this->formatearValorMonetarioV2($t["tasa"]).'" Importe="'.$this->formatearValorMonetarioV2($t["importe"]).'"/>';
								}
								$xmlImpuestos.='</cfdi:Traslados>';
								
							}
							
							$xmlImpuestos=str_replace("@totalRetenido",$this->formatearValorMonetarioV2($totalRetenciones),$xmlImpuestos);			
							$xmlImpuestos=str_replace("@totalTraslado",$this->formatearValorMonetarioV2($totalTraslado),$xmlImpuestos);			
							$xmlImpuestos.='</cfdi:Impuestos>';
						
						}
						if(($totalRetenciones+$totalTraslado)<=0)
							$xmlImpuestos="";
						
						$this->XML.=$xmlImpuestos;
						
						if(isset($this->objComprobante["complemento"])&&$this->objComprobante["complemento"]!=0)
						{
							$complemento="";
							
							
							$consulta="SELECT funcionGeneradoraComplemento FROM 6956_complementosComprobantes where idRegistro=".$this->objComprobante["complemento"];
							$funcionGeneradoraComplemento=$con->obtenerValor($consulta);
							
							eval('$complemento='.$funcionGeneradoraComplemento.'($this->objComprobante);');
							if($complemento!="")
							{
								$this->XML.='<cfdi:Complemento>';
									$this->XML.=$complemento;	
								$this->XML.='</cfdi:Complemento>';
							}
							
							
						}
						
					$this->XML.='</cfdi:Comprobante>';
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
		$datosComprobante=$factura->xpath('//cfdi:Comprobante');
		$datosComprobante=$datosComprobante[0];

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
			
			
			$dEmisor=$factura->xpath('//cfdi:Comprobante//cfdi:Emisor');
			$dEmisor=$dEmisor[0];
			$datosEmisor["rfc"]=$this->obtenerValorAtributoArregloAsoc($dEmisor,"rfc");  //* Obligatorio
			$datosEmisor["razonSocial"]=$this->obtenerValorAtributoArregloAsoc($dEmisor,"nombre");
			$datosEmisor["regimenFiscal"]=array(); //* Obligatorio
			
			$dRegimen=$factura->xpath('//cfdi:Comprobante//cfdi:Emisor//cfdi:RegimenFiscal');
			foreach($dRegimen as $r)
			{
				$regimen=$this->obtenerValorAtributoArregloAsoc($r,"Regimen"); 
				array_push($datosEmisor["regimenFiscal"],$regimen);
			}
			
			$domicilioEmisor=$factura->xpath('//cfdi:Comprobante//cfdi:Emisor//cfdi:DomicilioFiscal');
			if(isset($domicilioEmisor[0]))
			{
				$domicilioEmisor=$domicilioEmisor[0];
				
				$datosEmisor["domicilio"]["calle"]=$this->obtenerValorAtributoArregloAsoc($domicilioEmisor,"calle"); //* Obligatorio
				$datosEmisor["domicilio"]["noExterior"]=$this->obtenerValorAtributoArregloAsoc($domicilioEmisor,"noExterior");
				$datosEmisor["domicilio"]["noInterior"]=$this->obtenerValorAtributoArregloAsoc($domicilioEmisor,"noInterior");
				$datosEmisor["domicilio"]["colonia"]=$this->obtenerValorAtributoArregloAsoc($domicilioEmisor,"colonia");
				$datosEmisor["domicilio"]["localidad"]=$this->obtenerValorAtributoArregloAsoc($domicilioEmisor,"localidad");
				$datosEmisor["domicilio"]["municipio"]=$this->obtenerValorAtributoArregloAsoc($domicilioEmisor,"municipio"); //* Obligatorio
				$datosEmisor["domicilio"]["estado"]=$this->obtenerValorAtributoArregloAsoc($domicilioEmisor,"estado"); //* Obligatorio
				$datosEmisor["domicilio"]["pais"]=$this->obtenerValorAtributoArregloAsoc($domicilioEmisor,"pais"); //* Obligatorio
				$datosEmisor["domicilio"]["codigoPostal"]=$this->obtenerValorAtributoArregloAsoc($domicilioEmisor,"codigoPostal"); // Obligatorio
			}
			else
			{
				$datosEmisor["domicilio"]["calle"]="N/E"; //* Obligatorio
				$datosEmisor["domicilio"]["noExterior"]="";
				$datosEmisor["domicilio"]["noInterior"]="";
				$datosEmisor["domicilio"]["colonia"]="N/E";
				$datosEmisor["domicilio"]["localidad"]="N/E";
				$datosEmisor["domicilio"]["municipio"]="N/E"; //* Obligatorio
				$datosEmisor["domicilio"]["estado"]="N/E"; //* Obligatorio
				$datosEmisor["domicilio"]["pais"]="N/E"; //* Obligatorio
				$datosEmisor["domicilio"]["codigoPostal"]="N/E"; // Obligatorio
			}
			$obj["datosEmisor"]=$datosEmisor;
			
			$dReceptor=$factura->xpath('//cfdi:Comprobante//cfdi:Receptor');
			$dReceptor=$dReceptor[0];
			
			$domicilioReceptor=$factura->xpath('//cfdi:Comprobante//cfdi:Receptor//cfdi:Domicilio');
			$datosReceptor["rfc"]=$this->obtenerValorAtributoArregloAsoc($dReceptor,"rfc"); //* Obligatorio
			$datosReceptor["razonSocial"]=$this->obtenerValorAtributoArregloAsoc($dReceptor,"nombre");
			if(isset($domicilioReceptor[0]))
			{
				$domicilioReceptor=$domicilioReceptor[0];
		
				
				$datosReceptor["domicilio"]["calle"]=$this->obtenerValorAtributoArregloAsoc($domicilioReceptor,"calle"); 
				$datosReceptor["domicilio"]["noExterior"]=$this->obtenerValorAtributoArregloAsoc($domicilioReceptor,"noExterior");
				$datosReceptor["domicilio"]["noInterior"]=$this->obtenerValorAtributoArregloAsoc($domicilioReceptor,"noInterior");
				$datosReceptor["domicilio"]["colonia"]=$this->obtenerValorAtributoArregloAsoc($domicilioReceptor,"colonia");
				$datosReceptor["domicilio"]["localidad"]=$this->obtenerValorAtributoArregloAsoc($domicilioReceptor,"localidad");
				$datosReceptor["domicilio"]["municipio"]=$this->obtenerValorAtributoArregloAsoc($domicilioReceptor,"municipio"); 
				$datosReceptor["domicilio"]["estado"]=$this->obtenerValorAtributoArregloAsoc($domicilioReceptor,"estado"); 
				$datosReceptor["domicilio"]["pais"]=$this->obtenerValorAtributoArregloAsoc($domicilioReceptor,"pais"); //* Obligatorio
				$datosReceptor["domicilio"]["codigoPostal"]=$this->obtenerValorAtributoArregloAsoc($domicilioReceptor,"codigoPostal"); 
			}
			else
			{
				
				$datosReceptor["domicilio"]["calle"]="N/E"; 
				$datosReceptor["domicilio"]["noExterior"]="";
				$datosReceptor["domicilio"]["noInterior"]="";
				$datosReceptor["domicilio"]["colonia"]="N/E";
				$datosReceptor["domicilio"]["localidad"]="N/E";
				$datosReceptor["domicilio"]["municipio"]="N/E"; 
				$datosReceptor["domicilio"]["estado"]="N/E"; 
				$datosReceptor["domicilio"]["pais"]="N/E"; //* Obligatorio
				$datosReceptor["domicilio"]["codigoPostal"]="N/E"; 
			}
			$obj["datosReceptor"]=$datosReceptor;	
			
			
			$arrConceptos=$factura->xpath('//cfdi:Comprobante//cfdi:Concepto');
		
			
			
			$obj["conceptos"]=array();
			foreach($arrConceptos as $c)
			{
				$oConcepto=array();  
				$oConcepto["cantidad"]=$this->obtenerValorAtributoArregloAsoc($c,"cantidad"); //*
				$oConcepto["unidad"]=$this->obtenerValorAtributoArregloAsoc($c,"unidad"); //*
				$oConcepto["noIdentificacion"]=$this->obtenerValorAtributoArregloAsoc($c,"noIdentificacion");
				$oConcepto["descripcion"]=$this->obtenerValorAtributoArregloAsoc($c,"descripcion");  //*
				$oConcepto["valorUnitario"]=$this->obtenerValorAtributoArregloAsoc($c,"valorUnitario");  //*
				$oConcepto["importe"]=$this->obtenerValorAtributoArregloAsoc($c,"importe"); //*
				
				array_push($obj["conceptos"],$oConcepto);
			}
			
			
			$obj["impuestos"]=array();
			$obj["impuestos"]["retenciones"]=array();
			$obj["impuestos"]["traslados"]=array();
			$obj["impuestos"]["totalRetenciones"]=0;
			$obj["impuestos"]["totalTraslados"]=0;
			
			
			$arrNodoImpuestos=$factura->xpath('//cfdi:Comprobante//cfdi:Impuestos');
			if(sizeof($arrNodoImpuestos)>0)
			{
				$obj["impuestos"]["totalRetenciones"]=$this->obtenerValorAtributoArregloAsoc($arrNodoImpuestos[0],"totalImpuestosRetenidos");
				if($obj["impuestos"]["totalRetenciones"]=="")
					$obj["impuestos"]["totalRetenciones"]=0;
				$obj["impuestos"]["totalTraslados"]=$this->obtenerValorAtributoArregloAsoc($arrNodoImpuestos[0],"totalImpuestosTrasladados");
				if($obj["impuestos"]["totalTraslados"]=="")
					$obj["impuestos"]["totalTraslados"]=0;
				
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
			$obj["motivoDescuento"]=$this->obtenerValorAtributoArregloAsoc($datosComprobante,"MotivoDescuento"); 
			$obj["tipoCambio"]=$this->obtenerValorAtributoArregloAsoc($datosComprobante,"TipoCambio"); //* Obligatorio
			$obj["total"]=$this->obtenerValorAtributoArregloAsoc($datosComprobante,"Total"); //* Obligatorio
			$obj["version"]=$this->obtenerValorAtributoArregloAsoc($datosComprobante,"Version");
			$obj["fechaComprobante"]=$this->obtenerValorAtributoArregloAsoc($datosComprobante,"Fecha");
			$obj["condicionesDePago"]=$this->obtenerValorAtributoArregloAsoc($datosComprobante,"CondicionesDePago");
			$obj["tipoDeComprobante"]=$this->obtenerValorAtributoArregloAsoc($datosComprobante,"TipoDeComprobante");
			$obj["moneda"]=$this->obtenerValorAtributoArregloAsoc($datosComprobante,"Moneda");
			$obj["numCtaPago"]=$this->obtenerValorAtributoArregloAsoc($datosComprobante,"NumCtaPago");  
			$obj["folioFiscalOrig"]=$this->obtenerValorAtributoArregloAsoc($datosComprobante,"FolioFiscalOrig");
			$obj["serieFolioFiscalOrig"]=$this->obtenerValorAtributoArregloAsoc($datosComprobante,"SerieFolioFiscalOrig");
			$obj["fechaFolioFiscalOrig"]=$this->obtenerValorAtributoArregloAsoc($datosComprobante,"FechaFolioFiscalOrig");
			$obj["montoFolioFiscalOrig"]=$this->obtenerValorAtributoArregloAsoc($datosComprobante,"MontoFolioFiscalOrig");
			
			$dEmisor=$factura->xpath('//cfdi:Comprobante//cfdi:Emisor');
			$dEmisor=$dEmisor[0];
			$datosEmisor["rfc"]=$this->obtenerValorAtributoArregloAsoc($dEmisor,"Rfc");  //* Obligatorio
			$datosEmisor["razonSocial"]=$this->obtenerValorAtributoArregloAsoc($dEmisor,"Nombre");
			$datosEmisor["regimenFiscal"]=array(); //* Obligatorio
			array_push($datosEmisor["regimenFiscal"],$this->obtenerValorAtributoArregloAsoc($dEmisor,"RegimenFiscal"));
			
			
			$domicilioEmisor=$factura->xpath('//cfdi:Comprobante//cfdi:Emisor//cfdi:DomicilioFiscal');
			if(isset($domicilioEmisor[0]))
			{
				$domicilioEmisor=$domicilioEmisor[0];
				
				$datosEmisor["domicilio"]["calle"]=$this->obtenerValorAtributoArregloAsoc($domicilioEmisor,"calle"); //* Obligatorio
				$datosEmisor["domicilio"]["noExterior"]=$this->obtenerValorAtributoArregloAsoc($domicilioEmisor,"noExterior");
				$datosEmisor["domicilio"]["noInterior"]=$this->obtenerValorAtributoArregloAsoc($domicilioEmisor,"noInterior");
				$datosEmisor["domicilio"]["colonia"]=$this->obtenerValorAtributoArregloAsoc($domicilioEmisor,"colonia");
				$datosEmisor["domicilio"]["localidad"]=$this->obtenerValorAtributoArregloAsoc($domicilioEmisor,"localidad");
				$datosEmisor["domicilio"]["municipio"]=$this->obtenerValorAtributoArregloAsoc($domicilioEmisor,"municipio"); //* Obligatorio
				$datosEmisor["domicilio"]["estado"]=$this->obtenerValorAtributoArregloAsoc($domicilioEmisor,"estado"); //* Obligatorio
				$datosEmisor["domicilio"]["pais"]=$this->obtenerValorAtributoArregloAsoc($domicilioEmisor,"pais"); //* Obligatorio
				$datosEmisor["domicilio"]["codigoPostal"]=$this->obtenerValorAtributoArregloAsoc($domicilioEmisor,"codigoPostal"); // Obligatorio
			}
			else
			{
				$datosEmisor["domicilio"]["calle"]="N/E"; //* Obligatorio
				$datosEmisor["domicilio"]["noExterior"]="";
				$datosEmisor["domicilio"]["noInterior"]="";
				$datosEmisor["domicilio"]["colonia"]="N/E";
				$datosEmisor["domicilio"]["localidad"]="N/E";
				$datosEmisor["domicilio"]["municipio"]="N/E"; //* Obligatorio
				$datosEmisor["domicilio"]["estado"]="N/E"; //* Obligatorio
				$datosEmisor["domicilio"]["pais"]="N/E"; //* Obligatorio
				$datosEmisor["domicilio"]["codigoPostal"]="N/E"; // Obligatorio
			}
			$obj["datosEmisor"]=$datosEmisor;
			
			$dReceptor=$factura->xpath('//cfdi:Comprobante//cfdi:Receptor');
			$dReceptor=$dReceptor[0];
			$obj["usoCFDI"]=$this->obtenerValorAtributoArregloAsoc($dReceptor,"UsoCFDI");
			$domicilioReceptor=$factura->xpath('//cfdi:Comprobante//cfdi:Receptor//cfdi:Domicilio');
			$datosReceptor["rfc"]=$this->obtenerValorAtributoArregloAsoc($dReceptor,"Rfc"); //* Obligatorio
			$datosReceptor["razonSocial"]=$this->obtenerValorAtributoArregloAsoc($dReceptor,"Nombre");
			
			if(isset($domicilioReceptor[0]))
			{
				$domicilioReceptor=$domicilioReceptor[0];
		
				
				$datosReceptor["domicilio"]["calle"]=$this->obtenerValorAtributoArregloAsoc($domicilioReceptor,"calle"); 
				$datosReceptor["domicilio"]["noExterior"]=$this->obtenerValorAtributoArregloAsoc($domicilioReceptor,"noExterior");
				$datosReceptor["domicilio"]["noInterior"]=$this->obtenerValorAtributoArregloAsoc($domicilioReceptor,"noInterior");
				$datosReceptor["domicilio"]["colonia"]=$this->obtenerValorAtributoArregloAsoc($domicilioReceptor,"colonia");
				$datosReceptor["domicilio"]["localidad"]=$this->obtenerValorAtributoArregloAsoc($domicilioReceptor,"localidad");
				$datosReceptor["domicilio"]["municipio"]=$this->obtenerValorAtributoArregloAsoc($domicilioReceptor,"municipio"); 
				$datosReceptor["domicilio"]["estado"]=$this->obtenerValorAtributoArregloAsoc($domicilioReceptor,"estado"); 
				$datosReceptor["domicilio"]["pais"]=$this->obtenerValorAtributoArregloAsoc($domicilioReceptor,"pais"); //* Obligatorio
				$datosReceptor["domicilio"]["codigoPostal"]=$this->obtenerValorAtributoArregloAsoc($domicilioReceptor,"codigoPostal"); 
			}
			else
			{
				
				$datosReceptor["domicilio"]["calle"]="N/E"; 
				$datosReceptor["domicilio"]["noExterior"]="";
				$datosReceptor["domicilio"]["noInterior"]="";
				$datosReceptor["domicilio"]["colonia"]="N/E";
				$datosReceptor["domicilio"]["localidad"]="N/E";
				$datosReceptor["domicilio"]["municipio"]="N/E"; 
				$datosReceptor["domicilio"]["estado"]="N/E"; 
				$datosReceptor["domicilio"]["pais"]="N/E"; //* Obligatorio
				$datosReceptor["domicilio"]["codigoPostal"]="N/E"; 
			}
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
				$oConcepto["noIdentificacion"]=$this->obtenerValorAtributoArregloAsoc($c,"noIdentificacion");
				$oConcepto["iva"]="";
				$oConcepto["tasaIVA"]="";
				$trasladoIVA=$c->xpath('//cfdi:Impuestos//cfdi:Traslados//cfdi:Traslado');		
				if(sizeof($trasladoIVA)>0)
				{
					$trasladoIVA=$trasladoIVA[0];
					$oConcepto["iva"]=$this->obtenerValorAtributoArregloAsoc($trasladoIVA,"Importe"); 
					$oConcepto["tasaIVA"]=$this->obtenerValorAtributoArregloAsoc($trasladoIVA,"TasaOCuota"); 
				}
//				$arrNodoImpuestosConcepto=$c->xpath('//cfdi:Comprobante//cfdi:Conceptos//cfdi:Impuestos//cfdi:Traslados//cfdi:Traslado');
				
				
				
				
				
				array_push($obj["conceptos"],$oConcepto);
			}
			
			
			$obj["impuestos"]=array();
			$obj["impuestos"]["retenciones"]=array();
			$obj["impuestos"]["traslados"]=array();
			$obj["impuestos"]["totalRetenciones"]=0;
			$obj["impuestos"]["totalTraslados"]=0;
			
			
			$arrNodoImpuestos=$factura->xpath('//cfdi:Comprobante//cfdi:Impuestos');
			if(sizeof($arrImpuestosTraslados)>0)
			{
				$obj["impuestos"]["totalRetenciones"]=$this->obtenerValorAtributoArregloAsoc($arrNodoImpuestos[0],"TotalImpuestosRetenidos");
				if($obj["impuestos"]["totalRetenciones"]=="")
					$obj["impuestos"]["totalRetenciones"]=0;
				$obj["impuestos"]["totalTraslados"]=$this->obtenerValorAtributoArregloAsoc($arrNodoImpuestos[0],"TotalImpuestosTrasladados");
				if($obj["impuestos"]["totalTraslados"]=="")
					$obj["impuestos"]["totalTraslados"]=0;
				
				
				
				$arrImpuestosTraslados=$arrNodoImpuestos[0]->xpath('//cfdi:Traslados//cfdi:Traslado');
			
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
				$arrImpuestosRetenciones=$arrNodoImpuestos[0]->xpath('//cfdi:Retenciones//cfdi:Retencion');
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
				$obj["noCertificadoSAT"]=$this->obtenerValorAtributoArregloAsoc($datosTimbreFiscal,"NoCertificadoSAT");
				$obj["selloCFD"]=$this->obtenerValorAtributoArregloAsoc($datosTimbreFiscal,"SelloCFD");
				$obj["SelloDigitalSAT"]=$this->obtenerValorAtributoArregloAsoc($datosTimbreFiscal,"SelloSAT");
				$obj["cadenaOriginalComplententoSAT"]="||".$obj["versionCertificadoSAT"]."|".$obj["folioUUID"]."|".$obj["fechaCertificacionSAT"]."|".$obj["selloCFD"]."|".$obj["noCertificadoSAT"]."|";
				
			}
		}
		
		return $obj;
	}	
	
	function validarXML($XML)
	{
		
		$factura = new SimpleXMLElement($XML);
		$ns = $factura->getNamespaces(true);
		if(!isset($ns["tfd"]))
			$ns["tfd"]="";
		$factura->registerXPathNamespace('tfd', $ns['tfd']);
		
		$arrValoresObligatorios=array();
		$this->agregarValorValidacion($arrValoresObligatorios,"//cfdi:Comprobante","Sello","Sello");
		$this->agregarValorValidacion($arrValoresObligatorios,"//cfdi:Comprobante","FormaPago","Forma de pago");
		$this->agregarValorValidacion($arrValoresObligatorios,"//cfdi:Comprobante","NoCertificado","No. de certificado");
		$this->agregarValorValidacion($arrValoresObligatorios,"//cfdi:Comprobante","Certificado","Certificado");
		$this->agregarValorValidacion($arrValoresObligatorios,"//cfdi:Comprobante","MetodoPago","Método de pago");
		$this->agregarValorValidacion($arrValoresObligatorios,"//cfdi:Comprobante","LugarExpedicion","Lugar de expedición");
		$this->agregarValorValidacion($arrValoresObligatorios,"//cfdi:Comprobante","SubTotal", "Subtotal");
/*		$this->agregarValorValidacion($arrValoresObligatorios,"//cfdi:Comprobante","TipoCambio","Tipo de cambio");*/
		$this->agregarValorValidacion($arrValoresObligatorios,"//cfdi:Comprobante","Total","Total");
		$this->agregarValorValidacion($arrValoresObligatorios,"//cfdi:Comprobante//cfdi:Emisor","Rfc","RFC del Emisor",12,13);
		/*$this->agregarValorValidacion($arrValoresObligatorios,"//cfdi:Comprobante//cfdi:Emisor//cfdi:DomicilioFiscal","calle","Calle del domicilio del Emisor");
		$this->agregarValorValidacion($arrValoresObligatorios,"//cfdi:Comprobante//cfdi:Emisor//cfdi:DomicilioFiscal","municipio","Municipio del domicilio del Emisor");
		$this->agregarValorValidacion($arrValoresObligatorios,"//cfdi:Comprobante//cfdi:Emisor//cfdi:DomicilioFiscal","estado","Estado del domicilio del Emisor");
		$this->agregarValorValidacion($arrValoresObligatorios,"//cfdi:Comprobante//cfdi:Emisor//cfdi:DomicilioFiscal","pais","País del domicilio del Emisor");
		$this->agregarValorValidacion($arrValoresObligatorios,"//cfdi:Comprobante//cfdi:Emisor//cfdi:DomicilioFiscal","codigoPostal","C.P. del domicilio del Emisor",5);*/
		$this->agregarValorValidacion($arrValoresObligatorios,"//cfdi:Comprobante//cfdi:Receptor","Rfc","RFC del Receptor",12,13);
		/*$this->agregarValorValidacion($arrValoresObligatorios,"//cfdi:Comprobante//cfdi:Receptor//cfdi:Domicilio","pais","País del domicilio del Receptor");*/
		
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