<?php
	class cFDIInvoice extends cCFDI
{
	var $usuario;
	var $password;
	function cFDIInvoice()
	{
		$this->usuario="WEB01010";
		$this->password="demoprue";
	}
	
	function timbrarCFDI($objParametros)
	{
		
		$url = "https://invoiceone.mx/TimbreCFDI/TimbreCFDI.asmx?wsdl";
		$client = new SoapClient($url);
		$parametros=array();
		$parametros["cabecera"]="";
		$parametros["username"]=$this->usuario;
		$parametros["password"]= $this->password;		
		$parametros["xml"]=bE($objParametros["xml"]);
		
		$arrParametros[0]=$parametros;

								
		$response = $client->__soapCall("ObtenerCFDIPrueba", $arrParametros);
		varDump($response);
	}
	
	function cancelarCFDI($objParametros)
	{
		
		$url = "http://demo-facturacion.finkok.com/servicios/soap/cancel.wsdl";
		$client = new SoapClient($url);
		$parametros();
		$parametros["username"]=$this->usuario;
		$parametros["password"]= $this->password;	
		$parametros["uuids"]=$objParametros["arrFoliosUUID"];
		$parametros["taxpayer_id"]=$objParametros["RFC"];
		$parametros["cer"]=$objParametros["archivoKey"];
		$parametros["key"]=$objParametros["archivoKey"];	
		
		$arrParametros[0]=$parametros;
		
		$response = $client->__soapCall("cancel", $arrParametros);
	}
	
	function timbrarComprobante($idComprobante)
	{
		global $con;
		global $baseDir;
		$consulta="SELECT idCertificado FROM 703_relacionFoliosCFDI WHERE idFolio=".$idComprobante;
		$idCertificado=$con->obtenerValor($consulta);
		
		$consulta="SELECT * FROM 687_certificadosSelloDigital WHERE idCertificado=".$idCertificado;
		$fCertificado=$con->obtenerPrimeraFila($consulta);
		$idEmpresa=$fCertificado[7];
		$cuerpoArchivo="";
		$archivoXML=$baseDir."/facturacionElectronica/".$idEmpresa."/".$idComprobante.".xml";
		
		$fp=fopen($archivoXML,"r");
		while ( ($linea = fgets($fp)) !== false) 
		{
			
			$cuerpoArchivo.=$linea;
		}
		fclose($fp);
		$oParam["xml"]=$cuerpoArchivo;
		$this->timbrarCFDI($oParam);
		
	}
	
}
?>