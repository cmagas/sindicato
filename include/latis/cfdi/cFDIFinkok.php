<?php include_once("latis/cfdi/cCFDI.php");
	 include_once("latis/cfdi/cFactura.php");

class cFDIFinkok extends cCFDI
{
	var $usuario;
	var $password;
	var $urlTimbrado;
	var $urlCancelacion;
	var $urlUtilerias;
	var $arrSituacionEmpresas;
	var $enviarMail;
	var $esPrueba;
	
	function cFDIFinkok()
	{
		$arrSituacionEmpresas=array();
		$this->password="Grup0latis_17";
	}
	
	function  getPassword()
	{
		return $this->password;
	}
	
	function timbrarCFDI($objParametros)
	{
		
		$url = $this->urlTimbrado;
		$client = new SoapClient($url);
		$parametros=array();
		$parametros["xml"]=($objParametros["xml"]);
		$parametros["username"]=$this->usuario;
		$parametros["password"]= $this->password;		
		
		$arrParametros[0]=$parametros;

								
		$response = $client->__soapCall("stamp", $arrParametros);
		return $response;
	}
	
	function cancelarCFDI($objParametros)
	{
		$url = $this->urlCancelacion;
		$client = new SoapClient($url);
		$parametros=array();
		$parametros["username"]=$this->usuario;
		$parametros["password"]= $this->password;	
		$parametros["UUIDS"]["uuids"]=$objParametros["arrFoliosUUID"];
		$parametros["taxpayer_id"]=$objParametros["RFC"];
		$parametros["cer"]=$objParametros["archivoCer"];
		$parametros["key"]=$objParametros["archivoKey"];	

		$arrParametros[0]=$parametros;

		$response = $client->__soapCall("cancel", $arrParametros);
		return $response;
	}	
	
	function timbrarComprobante($idComprobante)
	{
		global $con;
		global $baseDir;
		$this->enviarMail=false;
		
		$consulta="SELECT idCertificado,tipoUso FROM 703_relacionFoliosCFDI WHERE idFolio=".$idComprobante;
		$fComprobante=$con->obtenerPrimeraFila($consulta);
		$idCertificado=$fComprobante[0];
		$tipoUso=$fComprobante[1];
		
		$consulta="SELECT * FROM 687_certificadosSelloDigital WHERE idCertificado=".$idCertificado;
		$fCertificado=$con->obtenerPrimeraFila($consulta);
		$idEmpresa=$fCertificado[7];
		
		$this->prepararAmbienteTimbrado($idEmpresa,$tipoUso);
		
		$cuerpoArchivo="";
		$archivoXML=$baseDir."/facturacionElectronica/".$idEmpresa."/".$idComprobante.".xml";
		
		$fp=fopen($archivoXML,"r");
		while ( ($linea = fgets($fp)) !== false) 
		{
			
			$cuerpoArchivo.=$linea;
		}
		fclose($fp);

		$oParam["xml"]=$cuerpoArchivo;
		$response=$this->timbrarCFDI($oParam);
		
		if(isset($response->stampResult->UUID))
		{
			$arrFecha=explode("T",$response->stampResult->Fecha);
			if($response->stampResult->xml!="")
			{
				$fp=fopen($archivoXML,"w");
				fwrite($fp,$response->stampResult->xml);
				fclose($fp);
			}
			else
			{
				$c=new cFacturaCFDI();
				$objComprobante=$c->cargarComprobanteXMLObjeto($idComprobante);
				$xmlTimbrado=$cuerpoArchivo;
				$seccionTimbre='<tfd:TimbreFiscalDigital xmlns:tfd="http://www.sat.gob.mx/TimbreFiscalDigital" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sat.gob.mx/TimbreFiscalDigital '.
								'http://www.sat.gob.mx/TimbreFiscalDigital/TimbreFiscalDigital.xsd"'.
								' version="1.1" UUID="'.$response->stampResult->UUID.'" FechaTimbrado="'.$response->stampResult->Fecha.'" selloCFD="'.$objComprobante["sello"].'" noCertificadoSAT="'.$response->stampResult->NoCertificadoSAT.'" selloSAT="'.
								$response->stampResult->SatSeal.'" />';
				$arrXML=explode('</cfdi:Complemento>',$cuerpoArchivo);
				$xmlTimbrado=$arrXML[0].$seccionTimbre."</cfdi:Complemento>".$arrXML[0];
				$fp=fopen($archivoXML,"w");
				fwrite($fp,$xmlTimbrado);
				fclose($fp);
			}

			
			$consulta="UPDATE 703_relacionFoliosCFDI SET esPrueba=".$this->esPrueba.",situacion=2,fechaTimbrado='".$arrFecha[0]."',horaTimbrado='".$arrFecha[1]."',`uuid`='".$response->stampResult->UUID."',selloSAT='".$response->stampResult->SatSeal.
						"',noCertificadoSAT='".$response->stampResult->NoCertificadoSAT."' WHERE idFolio=".$idComprobante;
						
			if( $con->ejecutarConsulta($consulta))
			{
				if($this->enviarMail)
					return $this->enviarComprobanteEmail($idComprobante);	
				return true;
			}
			

		}
		else
		{
			$comentarios="";
			if(gettype($response->stampResult->Incidencias->Incidencia)=='object')
			{
				$i=$response->stampResult->Incidencias->Incidencia;
				$comentarios="Error: ".$i->CodigoError.", ".$i->MensajeIncidencia;
			}
			else
			{
				foreach($response->stampResult->Incidencias->Incidencia as $i)
				{
					if($comentarios=="")
						$comentarios="Error: ".$i->CodigoError.", ".$i->MensajeIncidencia;
					else
						$comentarios.=", Error: ".$i->CodigoError.", ".$i->MensajeIncidencia;
				}
			}
			$consulta="UPDATE 703_relacionFoliosCFDI SET comentarios='".cv($comentarios)."',situacion=5 WHERE idFolio=".$idComprobante;
			$con->ejecutarConsulta($consulta);
			return false;
		}
		//if()
		
	}
	
	function cancelarComprobante($idComprobante,$motivo)
	{
		global $con;
		global $baseDir;
		$consulta="SELECT idCertificado,uuid FROM 703_relacionFoliosCFDI WHERE idFolio=".$idComprobante;
		$fComprobante=$con->obtenerPrimeraFila($consulta);
		
		$objParametros["arrFoliosUUID"]=array();
		array_push($objParametros["arrFoliosUUID"],$fComprobante[1]);
		
		
		$consulta="SELECT idEmpresa,rfc1,rfc2,rfc3 FROM 6927_empresas e,687_certificadosSelloDigital c WHERE e.idEmpresa=c.idReferencia AND c.idCertificado=".$fComprobante[0];
		$fEmpresa=$con->obtenerPrimeraFila($consulta);
		$idEmpresa=$fEmpresa[0];
		$objParametros["RFC"]=$fEmpresa[1].$fEmpresa[2].$fEmpresa[3];
		
		$rutaKey=$baseDir."/tesoreria/certificados/".$fComprobante[0].".finkok.key";
		$rutaCer=$baseDir."/tesoreria/certificados/".$fComprobante[0].".cer.pem";
		$fp= fopen($rutaKey, "r");
		$archivoKey = fread($fp,filesize($rutaKey));

		fclose($fp);
		
		$fp= fopen($rutaCer, "r");
		$archivoCer = fread($fp,filesize($rutaCer));
		fclose($fp);

		
		$objParametros["archivoKey"]=$archivoKey;
		$objParametros["archivoCer"]=$archivoCer;
		
		$this->prepararAmbienteTimbrado($idEmpresa);
		
		$resultado=$this->cancelarCFDI($objParametros);
		
		if(($resultado->cancelResult->Folios->Folio->EstatusUUID==201)||($resultado->cancelResult->Folios->Folio->EstatusUUID==202))
		{
			$consulta="update 703_relacionFoliosCFDI set situacion=3,motivoCancelacion='".cv($motivo)."',fechaCancelacion='".date("Y-m-d H:i:s")."',idResponsableCancelacion=".$_SESSION["idUsr"].
						",acuseCancelacion='".cv($resultado->cancelResult->Acuse)."',fechaAcuse='".$resultado->cancelResult->Fecha."' where idFolio=".$idComprobante;
			return $con->ejecutarConsulta($consulta)	;

		}
		else
		{
			
		}
		
		
	}
	
	function prepararAmbienteTimbrado($idEmpresa,$tipoUso="")
	{
		global $con;
		$situacionEmpresa=0;
		
		if(isset($this->arrSituacionEmpresas[$idEmpresa]))
		{
			$situacionEmpresa=$this->arrSituacionEmpresas[$idEmpresa];
		}
		else
		{
			$consulta="SELECT situacion FROM 715_situacionEmpresasTimbrado WHERE idEmpresa=".$idEmpresa;
			$situacionEmpresa=$con->obtenerValor($consulta);
			$this->arrSituacionEmpresas[$idEmpresa]=$situacionEmpresa;	
		}
		$ambientePruebas=!($situacionEmpresa==1);
		
		
		
		if(!$ambientePruebas)
		{
			$this->esPrueba=0;
			$this->enviarMail=true;
			
			if($tipoUso!="")
			{
				$consulta="SELECT esNomina FROM 704_tiposUsoCFDI WHERE idTipoUso=".$tipoUso;
				$esNomina=$con->obtenerValor($consulta);	
				if($esNomina==1)
				{
					$consulta="SELECT enviarMailNominaReceptor FROM 6927_empresas WHERE idEmpresa=".$idEmpresa;
				}
				else
				{
					$consulta="SELECT enviarMailComprobanteReceptor FROM 6927_empresas WHERE idEmpresa=".$idEmpresa;
				}
				$resMail=$con->obtenerValor($consulta);
				$this->enviarMail=($resMail=="1");
			}			
			
			$this->usuario="ti@grupolatis.net";
			$this->password="Grup0latis_17";
			$this->urlTimbrado="https://facturacion.finkok.com/servicios/soap/stamp.wsdl";
			$this->urlCancelacion="https://facturacion.finkok.com/servicios/soap/cancel.wsdl";	
			$this->urlUtilerias="https://facturacion.finkok.com/servicios/soap/utilities.wsdl";	
		}
		else
		{
			$this->esPrueba=1;
			$this->enviarMail=false;
			$this->usuario="marco.magana@grupolatis.net";
			$this->password="Grup0latis_17";
			$this->urlTimbrado="http://demo-facturacion.finkok.com/servicios/soap/stamp.wsdl";
			$this->urlCancelacion="http://demo-facturacion.finkok.com/servicios/soap/cancel.wsdl";
			$this->urlUtilerias="http://demo-facturacion.finkok.com/servicios/soap/utilities.wsdl";
	
		}
	}	
	
	function prepararAmbienteUtilerias()
	{
		global $con;
		
		$this->esPrueba=0;
		$this->enviarMail=false;
		$this->usuario="ti@grupolatis.net";
		$this->password="Grup0latis_17";
		$this->urlTimbrado="https://facturacion.finkok.com/servicios/soap/stamp.wsdl";
		$this->urlCancelacion="https://facturacion.finkok.com/servicios/soap/cancel.wsdl";	
		$this->urlUtilerias="https://facturacion.finkok.com/servicios/soap/utilities.wsdl";	
		
		
	}		
	
	function obtenerInformacionCFDIXML($xml)
	{
		if((!$this->urlTimbrado)||($this->urlTimbrado==""))
			$this->prepararAmbienteUtilerias();
		$url = $this->urlTimbrado;
		$client = new SoapClient($url);
		$parametros=array();
		$parametros["xml"]=$xml;
		$parametros["username"]=$this->usuario;
		$parametros["password"]= $this->password;		
		$arrParametros[0]=$parametros;
		$response = $client->__soapCall("Stamped", $arrParametros);
		return $response;
	}
	
	function obtenerInformacionCFDIUUID($uuid,$rfcEmisor)
	{
		if((!$this->urlTimbrado)||($this->urlTimbrado==""))
			$this->prepararAmbienteUtilerias();
			
		$url = $this->urlUtilerias;
		$client = new SoapClient($url);
		$parametros=array();
		$parametros["username"]=$this->usuario;
		$parametros["password"]= $this->password;		
		$parametros["uuid"]=$uuid;
		$parametros["taxpayer_id"]=$rfcEmisor;
		$arrParametros[0]=$parametros;
		$response = $client->__soapCall("get_xml", $arrParametros);
		return $response;
	}
	
}
?>