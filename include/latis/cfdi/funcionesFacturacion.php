<?php
	include_once("latis/conexionBD.php");
	include_once("latis/cfdi/cFactura.php");
	include_once("latis/cfdi/funciones.php");

	function generarXMLVentaCajaGeneral($idRegistro,$iEmpresaFactura,$iEmpresa=-1,$iCertificado=-1,$iSerie=-1,$generarFolio=false)
	{
		global $con;
		
		$consulta="SELECT desglosarIVAIndividualPG,desglosarIVAGlobalPG FROM 719_configuracionComprobantes";
		$fIVA=$con->obtenerPrimeraFila($consulta);
		
		
		
		$idEmpresa=$iEmpresa;
		$idCertificado=$iCertificado;
		$idSerie=$iSerie;
		
		$consulta="SELECT * FROM 6008_ventasCaja WHERE idVenta=".$idRegistro;
		$fVenta=$con->obtenerPrimeraFila($consulta);
		
		if($iEmpresa==-1)
		{
			$consulta="SELECT idClienteFactura,idCertificado,idSerie FROM 703_relacionFoliosCFDI WHERE idFolio=".$iEmpresa;
			$fRelacion=$con->obtenerPrimeraFila($consulta);
			$idEmpresa=$fRelacion[0];
			$idCertificado=$fRelacion[1];
			$idSerie=$fRelacion[2];
		}
				
		$consulta="SELECT * FROM 6927_empresas WHERE idEmpresa=".$idEmpresa;
		$fEmpresa=$con->obtenerPrimeraFila($consulta);
		$consulta="SELECT UPPER(municipio) FROM 821_municipios WHERE cveMunicipio='".$fEmpresa[14]."'";
		$municipioEmisor=$con->obtenerValor($consulta);
		$consulta="SELECT UPPER(estado) FROM 820_estados WHERE cveEstado='".$fEmpresa[13]."'";
		$estadoEmisor=$con->obtenerValor($consulta);

		$consulta="SELECT noCertificado,claveArchivoKey,certificadoDigital FROM 687_certificadosSelloDigital WHERE idCertificado=".$idCertificado;
		$fCertificado=$con->obtenerPrimeraFila($consulta);
		$consulta="SELECT serie FROM 688_seriesCertificados WHERE idSerieCertificado=".$idSerie;
		$fSerie=$con->obtenerPrimeraFila($consulta);
		
		
		
		$nom=new cFacturaCFDI();
		$objFactura=$nom->generarEstructuraLlenadoXML();
		
		$noFolio="NO ASIGNADO";
		if($generarFolio)
			$noFolio=obtenerFolioCertificado($idSerie);
		
		
		
		$consulta="SELECT UPPER(leyendaCFDI) FROM 600_formasPago WHERE idFormaPago=".$fVenta[8];
		$formaPago=$con->obtenerValor($consulta);

		
		$descuento=0;
		$retenciones=0;//Pendiente
		$subtotal=0;
		$totalTraslados=0;
		
		$objFactura["sello"]="";//* Obligatorio
		$objFactura["moneda"]="PESOS";

		$objFactura["idCertificado"]=$idCertificado;
		$objFactura["idSerie"]=$idSerie;
		$objFactura["idClienteFactura"]=$iEmpresaFactura;


		$objFactura["noCertificado"]=$fCertificado[0]; //* Obligatorio
		$objFactura["certificado"]=$fCertificado[2]; //* Obligatorio
		$objFactura["serie"]=$fSerie[0]; 
		$objFactura["folio"]=$noFolio; 
		
		$lugarExpedicion=$municipioEmisor;
		if($lugarExpedicion!="")
			$lugarExpedicion.=", ".$estadoEmisor;
		
		$objFactura["lugarExpedicion"]=$lugarExpedicion; //* Obligatorio
		$objFactura["tipoCambio"]="1"; //* Obligatorio
		$objFactura["tipoDeComprobante"]="ingreso";
		
		$objFactura["metodoDePago"]=$formaPago; //* Obligatorio
		$objFactura["numCtaPago"]="NO APLICA";
		if($fVenta[8]==2)
		{
			$oDatosCompra=json_decode($fVenta[9]);	
			$objFactura["numCtaPago"]=$oDatosCompra->digitosTarjeta;
		}
		
		$objFactura["formaPago"]="PAGO EN UNA SOLA EXHIBICIÓN"; //* Obligatorio	
				
		$objFactura["datosEmisor"]=obtenerSeccionDatosEmisor($idEmpresa);
		
		$objFactura["datosReceptor"]=obtenerSeccionDatosEmisor($iEmpresaFactura);
		
		$desglosarIVA=true;
		
		if(($fIVA[0]==0)&&($objFactura["datosReceptor"]["rfc"]=="XAXX010101000"))
		{
			$desglosarIVA=false;
		}
		
		
		$arrConceptos=array();
		
		$arrIVAS=array();
		
		$subtotalDevolucion=0;
		$ivaTotalDevolucion=0;
		$consulta="SELECT * FROM 6009_productosVentaCaja WHERE idVenta=".$idRegistro;

		$resProductos=$con->obtenerFilas($consulta);
		while($fProductoVenta=mysql_fetch_row($resProductos))
		{
			$subDevolucion=0;
			$ivaDevolucion=0;
			$consulta="SELECT cantidad FROM 6950_productosDevolucion WHERE idProductoVenta=".$fProductoVenta[0];
			$cantidadDevolucion=$con->obtenerValor($consulta);
			if($cantidadDevolucion!="")
			{
				$sTotalUnitario=str_replace(",","",number_format($fProductoVenta[6]/$fProductoVenta[4],2));
				$iTotalUnitario=str_replace(",","",number_format($fProductoVenta[7]/$fProductoVenta[4],2));
				$totalProducto=$fProductoVenta[8];
				$porcIVAProducto=$fProductoVenta[13];
				$ivaDevolucion=$iTotalUnitario*$cantidadDevolucion;
				$subDevolucion=$sTotalUnitario*$cantidadDevolucion;
				if(!$desglosarIVA)
				{
					$subDevolucion=	($sTotalUnitario+$iTotalUnitario)*$cantidadDevolucion;
				}
				
				
				
			}
			
			
			if(!isset($arrIVAS[$fProductoVenta[13]]))
				$arrIVAS[$fProductoVenta[13]]=0;
			$arrIVAS[$fProductoVenta[13]]+=($fProductoVenta[7]-$ivaDevolucion);
			if($arrIVAS[$fProductoVenta[13]]<0.001)
				$arrIVAS[$fProductoVenta[13]]=0;
			if($fProductoVenta[16]=="")
				$fProductoVenta[16]=0;
			$descuento+=($fProductoVenta[16]);
			$consulta="SELECT * FROM 6901_catalogoProductos WHERE idProducto=".$fProductoVenta[2];
			
			$fProd=$con->obtenerPrimeraFila($consulta);
			if($fProd[8]=="")
				$fProd[8]=-1;
				
			$oConcepto=array();  
			$oConcepto["cantidad"]=$fProductoVenta[4]; //*
			$oConcepto["valorUnitario"]=$fProductoVenta[5];  //*
			$oConcepto["importe"]=$oConcepto["valorUnitario"]*$oConcepto["cantidad"]; //*
			if(!$desglosarIVA)
			{
				$oConcepto["valorUnitario"]=$fProductoVenta[5]+($fProductoVenta[7]/$oConcepto["cantidad"]);
				$oConcepto["importe"]=($fProductoVenta[5]*$oConcepto["cantidad"]+$fProductoVenta[7]); //*
			}
			
			$consulta="SELECT unidadMedida FROM 6923_unidadesMedida WHERE idUnidadMedida=".$fProd[8];
			$oConcepto["unidad"]=$con->obtenerValor($consulta);  //*
			
			$consulta="SELECT descripcionProducto FROM 6932_descripcionProducto WHERE idProducto=".$fProductoVenta[2]." AND llave='".$fProductoVenta[12]."'";
			$oConcepto["descripcion"]=str_replace("</b>","",str_replace("<b>","",strip_tags($con->obtenerValor($consulta))));  //*
			
			array_push($arrConceptos,$oConcepto);
			$difSubtotal=$oConcepto["importe"]-$subDevolucion;
			$subtotal+=$difSubtotal;
		}


		$idReferenciaAdeudo=$idRegistro;
		$tipoAdeudo=1;
		if(($fVenta[10]!="")&&($fVenta[10]!="-1"))
		{
			$idReferenciaAdeudo=$fVenta[10];
			$tipoAdeudo=2;
		}
		
		$subtotalFacturado=0;
		$ivaTotalFacturado=0;
		$consulta="SELECT idAdeudo FROM 6942_adeudos WHERE tipoAdeudo=".$tipoAdeudo." AND idReferencia=".$idReferenciaAdeudo;
		$idAdeudo=$con->obtenerValor($consulta);
		
		
		if($idAdeudo!="")
		{
			$consulta="SELECT subtotal,iva FROM 6936_controlPagos WHERE idAdeudo=".$idAdeudo." AND idComprobante IS NOT NULL";
			$rAbonos=$con->obtenerFilas($consulta);	
			while($fAbonos=mysql_fetch_row($rAbonos))
			{
				$subtotalFacturado+=$fAbonos[0];
				$ivaTotalFacturado+=$fAbonos[1];	
			}
			
			$arrIVAS["16.00"]-=$ivaTotalFacturado;
			
		}
		


		$objFactura["conceptos"]=$arrConceptos;
		
		
		$objFactura["impuestos"]=array();
		$objFactura["impuestos"]["retenciones"]=array();
		$objFactura["impuestos"]["traslados"]=array();
		
		if($desglosarIVA)
		{
			foreach($arrIVAS as $tasa=>$monto)
			{
				$oImpuestoTraslado=array();
				$oImpuestoTraslado["impuesto"]="IVA";//*  IEPS | IVA
				$oImpuestoTraslado["tasa"]=$tasa;//*
				$oImpuestoTraslado["importe"]=$monto;//*
				$totalTraslados+=$monto;
				array_push($objFactura["impuestos"]["traslados"],$oImpuestoTraslado);
			}
		}
		
		$objFactura["subtotal"]=$subtotal; //* Obligatorio
		$objFactura["descuento"]=$descuento;
		$objFactura["motivoDescuento"]=""; 
		if($descuento>0)
			$objFactura["motivoDescuento"]="Descuento aplicable a productos: $ ".number_format($descuento,2)."";
		if($subtotalDevolucion>0)
		{
			$objFactura["descuento"]+=$subtotalDevolucion;
			if($objFactura["motivoDescuento"]!="")
				$objFactura["motivoDescuento"].=", Devolución de producto $ ".number_format($subtotalDevolucion,2);
			else
				$objFactura["motivoDescuento"].="Devolución de producto $ ".number_format($subtotalDevolucion,2);
		}
		
		if($subtotalFacturado>0)
		{
			$objFactura["descuento"]+=$subtotalFacturado;
			if($objFactura["motivoDescuento"]!="")
				$objFactura["motivoDescuento"].=", Monto facturado previamente $ ".number_format($subtotalFacturado,2);
			else
				$objFactura["motivoDescuento"].="Monto facturado previamente $ ".number_format($subtotalFacturado,2);
		}
		
		
		
		$objFactura["total"]= $subtotal-$objFactura["descuento"]+$totalTraslados-$retenciones; //* Obligatorio
		if($objFactura["total"]<0.001)	
			$objFactura["total"]=0;
		
		return $objFactura;
	}
		
	function generarXMLVentaCajaGeneralAbono($idRegistro,$iEmpresaFactura,$iEmpresa=-1,$iCertificado=-1,$iSerie=-1,$generarFolio=false)
	{
		global $con;
		$idEmpresa=$iEmpresa;
		$idCertificado=$iCertificado;
		$idSerie=$iSerie;
		
		$consulta="SELECT * FROM 6936_controlPagos WHERE idControlPago=".$idRegistro;
		$fAbono=$con->obtenerPrimeraFila($consulta);
		
		
		$consulta="SELECT * FROM 6942_adeudos WHERE idAdeudo=".$fAbono[3];
		$fAdeudo=$con->obtenerPrimeraFila($consulta);
		
		$lblTipoVenta="";
		$folio="";
		switch($fAdeudo[1])
		{
			case 1: //Venta
				$lblTipoVenta="Venta";
				$consulta="SELECT folioVenta FROM 6008_ventasCaja WHERE idVenta=".$fAdeudo[2];
				$folio=$con->obtenerValor($consulta);
			break;	
			case 2://pedido
				$lblTipoVenta="Pedido";
				$consulta="SELECT folioPedido FROM 6934_pedidosTienda WHERE idPedidoTienda=".$fAdeudo[2];
				$folio=$con->obtenerValor($consulta);
			break;	
		}
		
		
		if($iEmpresa==-1)
		{
			$consulta="SELECT idClienteFactura,idCertificado,idSerie FROM 703_relacionFoliosCFDI WHERE idFolio=".$iEmpresa;
			$fRelacion=$con->obtenerPrimeraFila($consulta);
			$idEmpresa=$fRelacion[0];
			$idCertificado=$fRelacion[1];
			$idSerie=$fRelacion[2];
		}
		
		
		$consulta="SELECT * FROM 6927_empresas WHERE idEmpresa=".$idEmpresa;

		$fEmpresa=$con->obtenerPrimeraFila($consulta);


		$consulta="SELECT UPPER(municipio) FROM 821_municipios WHERE cveMunicipio='".$fEmpresa[14]."'";
		$municipioEmisor=$con->obtenerValor($consulta);
		$consulta="SELECT UPPER(estado) FROM 820_estados WHERE cveEstado='".$fEmpresa[13]."'";
		$estadoEmisor=$con->obtenerValor($consulta);

		$consulta="SELECT noCertificado,claveArchivoKey,certificadoDigital FROM 687_certificadosSelloDigital WHERE idCertificado=".$idCertificado;
		$fCertificado=$con->obtenerPrimeraFila($consulta);
		$consulta="SELECT serie FROM 688_seriesCertificados WHERE idSerieCertificado=".$idSerie;
		$fSerie=$con->obtenerPrimeraFila($consulta);
		
		
		
		$nom=new cFacturaCFDI();
		$objFactura=$nom->generarEstructuraLlenadoXML();
		
		$noFolio="NO ASIGNADO";
		if($generarFolio)
			$noFolio=obtenerFolioCertificado($idSerie);
		
		
		
		$consulta="SELECT UPPER(leyendaCFDI) FROM 600_formasPago WHERE idFormaPago=".$fAbono[4];
		$formaPago=$con->obtenerValor($consulta);

		
		$descuento=0;
		$retenciones=0;//Pendiente
		$subtotal=0;
		$totalTraslados=0;
		
		$objFactura["sello"]="";//* Obligatorio
		$objFactura["moneda"]="PESOS";

		$objFactura["idCertificado"]=$idCertificado;
		$objFactura["idSerie"]=$idSerie;
		$objFactura["idClienteFactura"]=$iEmpresaFactura;


		$objFactura["noCertificado"]=$fCertificado[0]; //* Obligatorio
		$objFactura["certificado"]=$fCertificado[2]; //* Obligatorio
		$objFactura["serie"]=$fSerie[0]; 
		$objFactura["folio"]=$noFolio; 
		
		$lugarExpedicion=$municipioEmisor;
		if($lugarExpedicion!="")
			$lugarExpedicion.=", ".$estadoEmisor;
		
		$objFactura["lugarExpedicion"]=$lugarExpedicion; //* Obligatorio
		$objFactura["tipoCambio"]="1"; //* Obligatorio
		$objFactura["tipoDeComprobante"]="ingreso";
		
		$objFactura["metodoDePago"]=$formaPago; //* Obligatorio
		$objFactura["numCtaPago"]="NO APLICA";
		if($fAbono[4]==2)
		{
			if($fAbono[5]!="")
			{
				$oDatosCompra=json_decode($fAbono[5]);	
				$objFactura["numCtaPago"]=$oDatosCompra->digitosTarjeta;
			}
		}
		
		$objFactura["formaPago"]="PAGO EN UNA SOLA EXHIBICIÓN"; //* Obligatorio	
				
		$objFactura["datosEmisor"]=obtenerSeccionDatosEmisor($idEmpresa);
		
		$objFactura["datosReceptor"]=obtenerSeccionDatosEmisor($iEmpresaFactura);
		
		$arrConceptos=array();
		
		$arrIVAS=array();
		
		$subtotalDevolucion=0;
		$ivaTotalDevolucion=0;
		
		
		
		$arrIVAS[16]=$fAbono[10];
		
		$oConcepto=array();  
		$oConcepto["cantidad"]=1; //*
		$oConcepto["unidad"]="No Aplica";  //*
		$oConcepto["noIdentificacion"]="";
		
		$oConcepto["descripcion"]="Abono de uniformes/libros (".$lblTipoVenta.": ".$folio.")";//*
		$oConcepto["valorUnitario"]= $fAbono[9]; //*
		$oConcepto["importe"]=$oConcepto["valorUnitario"]*$oConcepto["cantidad"]; //*
		
		$subtotal+=$oConcepto["importe"];
		array_push($arrConceptos,$oConcepto);
		
		/*$consulta="SELECT * FROM 6009_productosVentaCaja WHERE idVenta=".$idRegistro;
		$resProductos=$con->obtenerFilas($consulta);
		while($fProductoVenta=mysql_fetch_row($resProductos))
		{
			$subDevolucion=0;
			$ivaDevolucion=0;
			$consulta="SELECT cantidad FROM 6950_productosDevolucion WHERE idProductoVenta=".$fProductoVenta[0];
			$cantidadDevolucion=$con->obtenerValor($consulta);
			if($cantidadDevolucion!="")
			{
				$sTotalUnitario=str_replace(",","",number_format($fProductoVenta[6]/$fProductoVenta[4],2));
				$iTotalUnitario=str_replace(",","",number_format($fProductoVenta[7]/$fProductoVenta[4],2));
				$totalProducto=$fProductoVenta[8];
				$porcIVAProducto=$fProductoVenta[13];
				$ivaDevolucion=$iTotalUnitario*$cantidadDevolucion;
				$subDevolucion=$sTotalUnitario*$cantidadDevolucion;
				
				
				
				
			}
			
			
			if(!isset($arrIVAS[$fProductoVenta[13]]))
				$arrIVAS[$fProductoVenta[13]]=0;
			$arrIVAS[$fProductoVenta[13]]+=($fProductoVenta[7]-$ivaDevolucion);
			if($arrIVAS[$fProductoVenta[13]]<0.001)
				$arrIVAS[$fProductoVenta[13]]=0;
			if($fProductoVenta[16]=="")
				$fProductoVenta[16]=0;
			$descuento+=($fProductoVenta[16]);
			$consulta="SELECT * FROM 6901_catalogoProductos WHERE idProducto=".$fProductoVenta[2];
			$fProd=$con->obtenerPrimeraFila($consulta);
			if($fProd[8]=="")
				$fProd[8]=-1;
				
			$oConcepto=array();  
			$oConcepto["cantidad"]=$fProductoVenta[4]; //*
//			$consulta="SELECT unidadMedida FROM 6923_unidadesMedida WHERE idUnidadMedida=".$fProd[8];
			$oConcepto["unidad"]=$fProd[8];  //*
			$oConcepto["noIdentificacion"]="";
			$consulta="SELECT descripcionProducto FROM 6932_descripcionProducto WHERE idProducto=".$fProductoVenta[2]." AND llave='".$fProductoVenta[12]."'";
			$oConcepto["descripcion"]=str_replace("</b>","",str_replace("<b>","",strip_tags($con->obtenerValor($consulta))));  //*
			$oConcepto["valorUnitario"]=$fProductoVenta[5];  //*
			$oConcepto["importe"]=$oConcepto["valorUnitario"]*$oConcepto["cantidad"]; //*
			
			$subtotal+=$oConcepto["importe"];
			array_push($arrConceptos,$oConcepto);
			$subtotalDevolucion+=$subDevolucion;
			$ivaTotalDevolucion+=$ivaDevolucion;
			
			
			
			
			
			
		}*/


		$objFactura["conceptos"]=$arrConceptos;
		
		
		$objFactura["impuestos"]=array();
		$objFactura["impuestos"]["retenciones"]=array();
		$objFactura["impuestos"]["traslados"]=array();
		
		
		foreach($arrIVAS as $tasa=>$monto)
		{
			$oImpuestoTraslado=array();
			$oImpuestoTraslado["impuesto"]="IVA";//*  IEPS | IVA
			$oImpuestoTraslado["tasa"]=$tasa;//*
			$oImpuestoTraslado["importe"]=$monto;//*
			$totalTraslados+=$monto;
			array_push($objFactura["impuestos"]["traslados"],$oImpuestoTraslado);
		}
		
		
		$objFactura["subtotal"]=$subtotal; //* Obligatorio
		$objFactura["descuento"]=$descuento;
		$objFactura["motivoDescuento"]=""; 
		if($descuento>0)
			$objFactura["motivoDescuento"]="Descuento aplicable a productos: $ ".number_format($descuento,2)."";
		if($subtotalDevolucion>0)
		{
			$objFactura["descuento"]+=$subtotalDevolucion;
			$objFactura["motivoDescuento"].=", Devolución de producto $ ".number_format($subtotalDevolucion,2);
		}
		
		
		$objFactura["total"]= $subtotal-$objFactura["descuento"]+$totalTraslados-$retenciones; //* Obligatorio
		if($objFactura["total"]<0.001)	
			$objFactura["total"]=0;
		
		return $objFactura;
	}
	
	function generarXMLVentaPublicoGeneral($iEmpresaFactura,$iEmpresa=-1,$iCertificado=-1,$iSerie=-1,$generarFolio=false,$subtotal=0,$iva=0,$leyendaConcepto="")
	{
		global $con;
		
		
		
		
		$idEmpresa=$iEmpresa;
		$idCertificado=$iCertificado;
		$idSerie=$iSerie;
		
		if($iEmpresa==-1)
		{
			$consulta="SELECT idClienteFactura,idCertificado,idSerie FROM 703_relacionFoliosCFDI WHERE idFolio=".$iEmpresa;
			$fRelacion=$con->obtenerPrimeraFila($consulta);
			$idEmpresa=$fRelacion[0];
			$idCertificado=$fRelacion[1];
			$idSerie=$fRelacion[2];
		}
		
		
		$consulta="SELECT * FROM 6927_empresas WHERE idEmpresa=".$idEmpresa;

		$fEmpresa=$con->obtenerPrimeraFila($consulta);


		$consulta="SELECT UPPER(municipio) FROM 821_municipios WHERE cveMunicipio='".$fEmpresa[14]."'";
		$municipioEmisor=$con->obtenerValor($consulta);
		$consulta="SELECT UPPER(estado) FROM 820_estados WHERE cveEstado='".$fEmpresa[13]."'";
		$estadoEmisor=$con->obtenerValor($consulta);

		$consulta="SELECT noCertificado,claveArchivoKey,certificadoDigital FROM 687_certificadosSelloDigital WHERE idCertificado=".$idCertificado;
		$fCertificado=$con->obtenerPrimeraFila($consulta);
		$consulta="SELECT serie FROM 688_seriesCertificados WHERE idSerieCertificado=".$idSerie;
		$fSerie=$con->obtenerPrimeraFila($consulta);

		$nom=new cFacturaCFDI();
		$objFactura=$nom->generarEstructuraLlenadoXML();
		
		$noFolio="NO ASIGNADO";
		if($generarFolio)
			$noFolio=obtenerFolioCertificado($idSerie);
		$formaPago="NO APLICA";
		$descuento=0;
		$retenciones=0;//Pendiente
		$totalTraslados=0;
		$objFactura["sello"]="";//* Obligatorio
		$objFactura["moneda"]="PESOS";
		$objFactura["idCertificado"]=$idCertificado;
		$objFactura["idSerie"]=$idSerie;
		$objFactura["idClienteFactura"]=$iEmpresaFactura;

		$lugarExpedicion=$municipioEmisor;
		if($lugarExpedicion!="")
			$lugarExpedicion.=", ".$estadoEmisor;
		$objFactura["noCertificado"]=$fCertificado[0]; //* Obligatorio
		$objFactura["certificado"]=$fCertificado[2]; //* Obligatorio
		$objFactura["serie"]=$fSerie[0]; 
		$objFactura["folio"]=$noFolio; 
		$objFactura["lugarExpedicion"]=$lugarExpedicion; //* Obligatorio
		$objFactura["tipoCambio"]="1"; //* Obligatorio
		$objFactura["tipoDeComprobante"]="ingreso";
		
		$objFactura["metodoDePago"]=$formaPago; //* Obligatorio
		$objFactura["numCtaPago"]="NO APLICA";
				
		$objFactura["formaPago"]="PAGO EN UNA SOLA EXHIBICIÓN"; //* Obligatorio	
				
		$objFactura["datosEmisor"]=obtenerSeccionDatosEmisor($idEmpresa);
		
		$objFactura["datosReceptor"]=obtenerSeccionDatosEmisor($iEmpresaFactura);
		
		
		$consulta="SELECT desglosarIVAIndividualPG,desglosarIVAGlobalPG FROM 719_configuracionComprobantes";
		$fIVA=$con->obtenerPrimeraFila($consulta);
		
		
		$desglosarIVA=true;
		
		if(($fIVA[1]==0)&&($objFactura["datosReceptor"]["rfc"]=="XAXX010101000"))
		{
			$desglosarIVA=false;
		}
		
		$arrConceptos=array();
		
		$arrIVAS=array();
		
		$arrIVAS[16]=$iva;
		
		$oConcepto=array();  
		$oConcepto["cantidad"]=1; //*
		$oConcepto["unidad"]="No Aplica";  //*
		$oConcepto["noIdentificacion"]="";
		
		$oConcepto["descripcion"]=$leyendaConcepto;//*
		$oConcepto["valorUnitario"]= $subtotal; //*
		if(!$desglosarIVA)
		{
			$oConcepto["valorUnitario"]= $subtotal+$iva; //*
		}
		$oConcepto["importe"]=$oConcepto["valorUnitario"]*$oConcepto["cantidad"]; //*
		
		
		array_push($arrConceptos,$oConcepto);
		$objFactura["conceptos"]=$arrConceptos;
				
		$objFactura["impuestos"]=array();
		$objFactura["impuestos"]["retenciones"]=array();
		$objFactura["impuestos"]["traslados"]=array();
		
		if($desglosarIVA)
		{
			foreach($arrIVAS as $tasa=>$monto)
			{
				$oImpuestoTraslado=array();
				$oImpuestoTraslado["impuesto"]="IVA";//*  IEPS | IVA
				$oImpuestoTraslado["tasa"]=$tasa;//*
				$oImpuestoTraslado["importe"]=$monto;//*
				$totalTraslados+=$monto;
				array_push($objFactura["impuestos"]["traslados"],$oImpuestoTraslado);
			}
		}
		
		$objFactura["subtotal"]=$subtotal; //* Obligatorio
		if(!$desglosarIVA)
		{
			$objFactura["subtotal"]=$subtotal+$iva;
		}
		
		
		$objFactura["descuento"]=$descuento;
		$objFactura["motivoDescuento"]=""; 
		if($descuento>0)
			$objFactura["motivoDescuento"]="Descuento aplicable a productos: $ ".number_format($descuento,2)."";
				
		$objFactura["total"]= $objFactura["subtotal"]-$objFactura["descuento"]+$totalTraslados-$retenciones; //* Obligatorio
		if(!$desglosarIVA)
		{
			
		}
		
		
		if($objFactura["total"]<0.001)	
			$objFactura["total"]=0;
		
		return $objFactura;
	}
	
	
	
	function generarObjetoComprobanteJSON($o,$generarFolio=false)
	{
		global $con;
		
		
		
		$obj["sello"]="";
		
		$obj["usoCFDI"]=$o->usoCFDI;
		$obj["formaPago"]=$o->formaPago; //* Obligatorio
		$obj["fechaComprobante"]=$o->fechaComprobante."T00:00:00";
		$consulta="SELECT noCertificado,certificadoDigital FROM 687_certificadosSelloDigital WHERE idCertificado=".$o->idCertificado;
		$fCertificado=$con->obtenerPrimeraFila($consulta);
		$obj["noCertificado"]=$fCertificado[0]; //* Obligatorio
		$obj["certificado"]=$fCertificado[1]; //* Obligatorio
		
		$consulta="SELECT serie FROM 688_seriesCertificados WHERE idSerieCertificado=".$o->idSerie;
		$obj["serie"]=$con->obtenerValor($consulta); 
		$obj["folio"]="NO ASIGNADO"; 
		if($generarFolio)
			$obj["folio"]=obtenerFolioCertificado($o->idSerie);
			
		
		$obj["metodoDePago"]=$o->metodoPago; //* Obligatorio
		$obj["lugarExpedicion"]=$o->lugarExpedicion; //* Obligatorio
		
		
		$obj["descuento"]="0";
		$descuentoEncontrado=false;
		foreach($o->arrTotales as $t)
		{
			if($t->idConcepto==6)
			{
				$obj["descuento"]=$t->montoConcepto;
				$descuentoEncontrado=true;
				break;	
			}	
		}
		
		$obj["subtotal"]="0"; //* Obligatorio
		foreach($o->arrTotales as $t)
		{
			if($t->idConcepto==5)
			{
				$obj["subtotal"]=$t->montoConcepto;
				break;	
			}	
		}
		
		$obj["motivoDescuento"]=$o->motivoDescuento; 
		
		$totalIVA=0;
		$tasaIVA="16";
		
		foreach($o->arrTotales as $t)
		{
			if($t->tipoConcepto==3)
			{
				$obj["descuento"]+=$t->montoConcepto;
				
				if($obj["motivoDescuento"]=="")
					$obj["motivoDescuento"]=$t->etiqueta;
				else
					$obj["motivoDescuento"].=", ".$t->etiqueta;
			}
			
			if($t->idConcepto==1)
			{
				$totalIVA=$t->montoConcepto;
				if(($t->tasaConcepto!="")&&($t->tasaConcepto!="0")&&($t->tasaConcepto!=NULL)&&($t->tasaConcepto!="NULL"))	
					$tasaIVA=$t->tasaConcepto;
			}
			
		}
		
		
		$obj["moneda"]=$o->moneda;
		$consulta="SELECT naturalezaComprobante FROM 705_tiposComprobantes WHERE idTipoComprobante=".$o->tipoComprobante;
		$obj["tipoDeComprobante"]=$con->obtenerValor($consulta);
		$obj["tipoCambio"]=$o->tipoCambio; //* Obligatorio
		
		$obj["condicionesDePago"]=$o->condicionesPago;
		$obj["numCtaPago"]=$o->noCuenta;  
		$obj["folioFiscalOrig"]="";
		$obj["serieFolioFiscalOrig"]="";
		$obj["fechaFolioFiscalOrig"]="";
		$obj["montoFolioFiscalOrig"]="";
	
		$obj["datosEmisor"]=obtenerSeccionDatosEmisor($o->idEmpresa);
		
		$obj["datosReceptor"]=obtenerSeccionDatosEmisor($o->idCliente);
		
		
		$obj["conceptos"]=array();
		$arrIVA=array();
		foreach($o->arrConceptos as $c)
		{
			if(!isset($arrIVA[$c->tasaIVA]))
				$arrIVA[$c->tasaIVA]=0;
			$arrIVA[$c->tasaIVA]+=$c->iva;
			$oConcepto=array();  
			$oConcepto["cantidad"]=$c->cantidad; //*
			$oConcepto["claveProdServ"]=$c->claveProdServ;
			$oConcepto["claveUnidad"]=$c->claveUnidad; //*
			$oConcepto["unidad"]=$c->unidad; //*
			$oConcepto["noIdentificacion"]="";
			$oConcepto["descripcion"]=$c->descripcion;  //*
			$oConcepto["valorUnitario"]=$c->costoUnitario;  //*
			$oConcepto["descuentoTotal"]=$c->descuentoTotal;  //*
			$oConcepto["importe"]=$oConcepto["cantidad"]*$oConcepto["valorUnitario"]; //*
			$oConcepto["iva"]=$c->iva;  //*
			$oConcepto["tasaIVA"]=$c->tasaIVA/100;  //*
			
			
			
			array_push($obj["conceptos"],$oConcepto);
		}
		
		$obj["impuestos"]=array();
		$obj["impuestos"]["retenciones"]=array();
		$obj["impuestos"]["traslados"]=array();
		
		$totalImpuestos=0;
		$totalRetenciones=0;
		
		
		if((sizeof($arrIVA)==1)&&(isset($arrIVA[0]))&&($totalIVA!=0))
		{
			$arrIVA=array();
			$arrIVA[$tasaIVA]+=$totalIVA;	
		}
		
		foreach($arrIVA as $tasa=>$monto)
		{
			
			$oImpuestoTraslado=array();
			//$consulta="SELECT claveSAT FROM 711_catalogoImpuestosRetenciones WHERE idConcepto=".$t->idConcepto;
			$oImpuestoTraslado["impuesto"]="002";//*  IEPS | IVA
			$oImpuestoTraslado["tasa"]=$tasa/100;//*
			$oImpuestoTraslado["importe"]=$monto;//*
			$totalImpuestos+=$monto;
			array_push($obj["impuestos"]["traslados"],$oImpuestoTraslado);	
		}
		
		foreach($o->arrTotales as $t)
		{
			if($t->tipoConcepto==2)
			{
				$oImpuestoRetencion=array();
				$consulta="SELECT claveSAT FROM 711_catalogoImpuestosRetenciones WHERE idConcepto=".$t->idConcepto;
				$oImpuestoRetencion["impuesto"]=$con->obtenerValor($consulta);//*  ISR | IVA
				$oImpuestoRetencion["importe"]=$t->montoConcepto;//*
				$totalRetenciones+=$t->montoConcepto;
				array_push($obj["impuestos"]["retenciones"],$oImpuestoRetencion);
			}
			else
			{
				if($t->tipoConcepto==1)
				{
					$oImpuestoTraslado=array();
					$consulta="SELECT claveSAT FROM 711_catalogoImpuestosRetenciones WHERE idConcepto=".$t->idConcepto;
					$oImpuestoTraslado["impuesto"]=$con->obtenerValor($consulta);//*  IEPS | IVA
					$oImpuestoTraslado["tasa"]=$t->tasaConcepto/100;//*
					$oImpuestoTraslado["importe"]=$t->montoConcepto;//*
					$totalImpuestos+=$t->montoConcepto;
					array_push($obj["impuestos"]["traslados"],$oImpuestoTraslado);	
				}
				
			}
			
			
		}
		
		//$obj["total"]=$obj["subtotal"]-$obj["descuento"]+$totalImpuestos-$totalRetenciones; //* Obligatorio
		
		
		
		foreach($o->arrTotales as $t)
		{
			if($t->idConcepto==12)
			{
				$obj["total"]=$t->montoConcepto;
				break;	
			}	
		}
		
		$obj["idCertificado"]=$o->idCertificado;
		$obj["idSerie"]=$o->idSerie;
		$obj["idClienteFactura"]=$o->idCliente;
		
		
		$obj["complemento"]=$o->complemento;
		if($obj["complemento"]!=0)
		{
			$consulta="SELECT funcionGeneradoraComplemento FROM 6956_complementosComprobantes WHERE idRegistro=".$o->complemento;
			$funcion=$con->obtenerValor($consulta);
			eval ($funcion.'JSON($obj,$o);');
			
		}
			
		return $obj;
	}
	
	function obtenerValoresVenta($idVenta)
	{
		global $con;
		$resultado["subtotal"]=0;
		$resultado["iva"]=0;
		$consulta="SELECT * FROM 6008_ventasCaja WHERE idVenta=".$idVenta;
		$fVenta=$con->obtenerPrimeraFila($consulta);
		
		
		$arrConceptos=array();
		$arrIVAS=array();
		$subtotalDevolucion=0;
		$ivaTotalDevolucion=0;
		$descuento=0;
		$consulta="SELECT * FROM 6009_productosVentaCaja WHERE idVenta=".$idVenta;
		$resProductos=$con->obtenerFilas($consulta);
		while($fProductoVenta=mysql_fetch_row($resProductos))
		{
			$subDevolucion=0;
			$ivaDevolucion=0;
			$consulta="SELECT cantidad FROM 6950_productosDevolucion WHERE idProductoVenta=".$fProductoVenta[0];
			$cantidadDevolucion=$con->obtenerValor($consulta);
			if($cantidadDevolucion!="")
			{
				$sTotalUnitario=str_replace(",","",number_format($fProductoVenta[6]/$fProductoVenta[4],2));
				$iTotalUnitario=str_replace(",","",number_format($fProductoVenta[7]/$fProductoVenta[4],2));
				$totalProducto=$fProductoVenta[8];
				$porcIVAProducto=$fProductoVenta[13];
				$ivaDevolucion=$iTotalUnitario*$cantidadDevolucion;
				$subDevolucion=$sTotalUnitario*$cantidadDevolucion;
			}			
			
			if(!isset($arrIVAS[$fProductoVenta[13]]))
				$arrIVAS[$fProductoVenta[13]]=0;
			$arrIVAS[$fProductoVenta[13]]+=($fProductoVenta[7]);
			if($arrIVAS[$fProductoVenta[13]]<0.001)
				$arrIVAS[$fProductoVenta[13]]=0;
			if($fProductoVenta[16]=="")
				$fProductoVenta[16]=0;
			$descuento+=($fProductoVenta[16]);
			$consulta="SELECT * FROM 6901_catalogoProductos WHERE idProducto=".$fProductoVenta[2];
			$fProd=$con->obtenerPrimeraFila($consulta);
			if($fProd[8]=="")
				$fProd[8]=-1;
				
			$oConcepto=array();  
			$oConcepto["cantidad"]=$fProductoVenta[4]; //*
			
			$oConcepto["valorUnitario"]=$fProductoVenta[5];  //*
			$oConcepto["importe"]=$oConcepto["valorUnitario"]*$oConcepto["cantidad"]; //*
			
			$subtotal+=$oConcepto["importe"];
			
			$subtotalDevolucion+=$subDevolucion;
			$ivaTotalDevolucion+=$ivaDevolucion;
			
		}
		
		

		$idReferenciaAdeudo=$idVenta;
		$tipoAdeudo=1;
		if(($fVenta[10]!="")&&($fVenta[10]!="-1"))
		{
			$idReferenciaAdeudo=$fVenta[10];
			$tipoAdeudo=2;
		}
		
		$subtotalFacturado=0;
		$ivaTotalFacturado=0;
		$consulta="SELECT idAdeudo FROM 6942_adeudos WHERE tipoAdeudo=".$tipoAdeudo." AND idReferencia=".$idReferenciaAdeudo;
		$idAdeudo=$con->obtenerValor($consulta);
		
		
		if($idAdeudo!="")
		{
			$consulta="SELECT subtotal,iva FROM 6936_controlPagos WHERE idAdeudo=".$idAdeudo." AND idComprobante IS NOT NULL";
			$rAbonos=$con->obtenerFilas($consulta);	
			while($fAbonos=mysql_fetch_row($rAbonos))
			{
				$subtotalFacturado+=$fAbonos[0];
				$ivaTotalFacturado+=$fAbonos[1];	
			}
			
			
			
		}
		
		foreach($arrIVAS as $porcentaje=>$monto)
		{
			$resultado["iva"]+=$monto;
		}	
		
		
		$resultado["iva"]-=($ivaTotalFacturado+$ivaTotalDevolucion);
		$resultado["subtotal"]=$subtotal-($subtotalDevolucion+$subtotalFacturado+$descuento);
		return $resultado;	
	}
	//generarXMLVentaCajaGeneral(190,11,11,14,4);
	//generarXMLNominaPrimaria(32);
	//generarXMLNomina(722,853,0,0)
?>