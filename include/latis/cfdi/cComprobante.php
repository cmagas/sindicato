<?php include_once("latis/nusoap/nusoap.php");

class cComprobante

{

	function cComprobante()

	{

		

	}

	

	function obtenerValorAtributoObj($obj,$nombreAtributo)

	{

		$valor="";

		$res=false;

		eval('$res=isset($obj->'.$nombreAtributo.');');

		if($res)

		{

			eval('$valor=$obj->'.$nombreAtributo.";");

		}

		return $valor;

	}

	

	function obtenerValorAtributoArregloAsoc($obj,$nombreAtributo)

	{



		$valor="";

		if(isset($obj[$nombreAtributo]))

			$valor=(String)$obj[$nombreAtributo];

		return $valor;

	}

	

	function formatearValorElemento($nombreAtributo,$valor)

	{

		$atributo="";

		if(trim($valor)!="")

			$atributo=' '.$nombreAtributo.'="'.str_replace('"',"",$valor).'" ';

		return $atributo;

	}

	

	function formatearValorMonetario($valor)

	{

		$valor=trim($valor);

		if($valor=="")

			$valor=0;

		$resultado=	str_replace(",","",number_format($valor,6));

		return $resultado;

	}

	

	function formatearValorMonetarioV2($valor)

	{

		$valor=trim($valor);

		if($valor=="")

			$valor=0;

		$resultado=	str_replace(",","",number_format($valor,2));

		return $resultado;

	}

	

	function registrarXML($tipoUso,$idReferencia)

	{

		global $con;		

		global $baseDir;

		

		$consulta="INSERT INTO 703_relacionFoliosCFDI(idCertificado,idSerie,folio,tipoUso,idReferencia,fechaCreacion,situacion,idResponsableCreacion,idClienteFactura)

				VALUES(".$this->idCertificado.",".$this->idSerie.",".$this->folio.",".$tipoUso.",".$idReferencia.
				",'".date("Y-m-d H:i:s")."',1,".$_SESSION["idUsr"].",".$this->idClienteFactura.")";



		if($con->ejecutarConsulta($consulta))

		{

			$idFactura=$con->obtenerUltimoID();	

			$consulta="SELECT idReferencia FROM 687_certificadosSelloDigital WHERE idCertificado=".$this->idCertificado;



			$idEmpresa=$con->obtenerValor($consulta);

			$dirEmpresa=$baseDir."/facturacionElectronica/".$idEmpresa;



			if(!file_exists($dirEmpresa))

			{

				if(!mkdir($dirEmpresa))

					return false;

			}

			$fp = fopen($dirEmpresa."/".$idFactura.".xml", 'w');

			if(!$fp)

				return false;

			if(!fwrite($fp,$this->XML))

				return false;

			fclose($fp);

			$this->idComprobante=$idFactura;

			

			$consulta="select * from 704_tiposUsoCFDI where idTipoUso=".$tipoUso;



			$fUso=$con->obtenerPrimeraFila($consulta);

			if($fUso[3]!="")

			{

				$consulta="update ".$fUso[2]." set ".$fUso[3]."='".$idFactura."' where ".$fUso[4]."=".$idReferencia;	



				$con->ejecutarConsulta($consulta);

			}



			return $idFactura;

		}

	}

	

	function actualizarXMLComprobante($idComprobante)

	{

		global $con;

		global $baseDir;

		$consulta="SELECT idCertificado FROM 703_relacionFoliosCFDI WHERE idFolio=".$idComprobante;

		$idCertificado=$con->obtenerValor($consulta);

		

		$consulta="SELECT idReferencia FROM 687_certificadosSelloDigital WHERE idCertificado=".$idCertificado;

		$idEmpresa=$con->obtenerValor($consulta);

		$dirEmpresa=$baseDir."/facturacionElectronica/".$idEmpresa;

		if(!file_exists($dirEmpresa))

		{

			if(!mkdir($dirEmpresa))

				return false;

		}

		$fp = fopen($dirEmpresa."/".$idComprobante.".xml", 'w');

		if(!$fp)

			return false;

		if(!fwrite($fp,$this->XML))

			return false;

		fclose($fp);

		

		return true;		

		

	}

	

	function generarCadenaOriginal($iComprobante="",$filaCertificado=null)

	{

		global $con;	

		global $baseDir;



		$cadenaOriginal="";

		$idCertificado=$this->idCertificado;

		$idComprobante=$this->idComprobante;

		$idEmpresa=0;

		$archivoTransformacion=$baseDir."/include/latis/cfdi/xslt/cadenaOriginal_3_2.xslt";

		if($this->version==3.3)

			$archivoTransformacion=$baseDir."/include/latis/cfdi/xslt/cadenaOriginal_3_3.xslt";



		if($iComprobante!="")	

		{

			$idComprobante=$iComprobante;

			$consulta="SELECT idCertificado FROM 703_relacionFoliosCFDI WHERE idFolio=".$idComprobante;

			$idCertificado=$con->obtenerValor($consulta);

		}

		

		if(!$filaCertificado)

		{

			$consulta="SELECT * FROM 687_certificadosSelloDigital WHERE idCertificado=".$idCertificado;

			$fCertificado=$con->obtenerPrimeraFila($consulta);

			

		}

		else

			$fCertificado=$filaCertificado;

			

		

		$idEmpresa=$fCertificado[7];



		$nombreArchivo=date("YmdHis_").rand(1,1000);

		$archivoXML=$baseDir."/facturacionElectronica/".$idEmpresa."/".$idComprobante.".xml";
		$contenidoXML=leerContenidoArchivo($archivoXML);
		$client = new nusoap_client("http://grupolatis.net/webServices/wsNomina.php?wsdl","wsdl");

		$parametros=array();
		$parametros["archivoXML"]=bE($contenidoXML);
		$parametros["nombreArchivo"]=$idComprobante.".xml";
		$cadenaOriginal = bD($client->call("generarCadenaOriginalXML", $parametros));
		
		

//		$archivoDestino=$baseDir."/archivosTemporales/".$nombreArchivo.".txt";

		
		/*$comando="xsltproc ".$archivoTransformacion." ".$archivoXML." > ".$archivoDestino;
		
		

		if(file_exists($archivoXML))
		{
			$cadenaOriginal=shell_exec($comando);
			if(file_exists($archivoDestino))

			{

				$cadenaOriginal="";

				$fp=fopen($archivoDestino,"r");

				while ( ($linea = fgets($fp)) !== false) 

				{

					$cadenaOriginal.=$linea;

				}

				fclose($fp);

				unlink($archivoDestino);

			}

		}*/

		

		return $cadenaOriginal;

	}

	

	function generarSelloDigital($iComprobante="")

	{

		global $con;

		global $baseDir;

		$idCertificado=$this->idCertificado;

		$idComprobante=$this->idComprobante;

		

		if($iComprobante!="")	

		{

			$idComprobante=$iComprobante;

			

			$consulta="SELECT idCertificado FROM 703_relacionFoliosCFDI WHERE idFolio=".$idComprobante;

			$idCertificado=$con->obtenerValor($consulta);

		}

		

		

		$consulta="SELECT * FROM 687_certificadosSelloDigital WHERE idCertificado=".$idCertificado;



		$fCertificado=$con->obtenerPrimeraFila($consulta);



		$nIntentos=0;

		$cadenaOriginal="";

		while(trim($cadenaOriginal)=="")

		{



			$cadenaOriginal=$this->generarCadenaOriginal($idComprobante,$fCertificado);
			
			

			if($cadenaOriginal=="")

			{

				$nIntentos++;

				

				if($nIntentos==10)

					return false;	



				

			}

		

		}

		

		$archivoCadena=$baseDir."/archivosTemporales/cO".$idComprobante.".txt";
		//$archivoSello=$baseDir."/archivosTemporales/sello_".$idComprobante.".txt";

		$fp=fopen($archivoCadena,"w");



		if(!fwrite($fp,$cadenaOriginal))

		{

			

			return false;

		}

		fclose($fp);

		

		$idEmpresa=$fCertificado[7];



		$archivoPEM=$baseDir."/tesoreria/certificados/".$idCertificado.".pem";

		

		$sello="";

		$nIntentos=0;

		while(trim($sello)=="")

		{

		

			$comando="openssl dgst -sha1 -sign ".$archivoPEM." ".$archivoCadena." | openssl enc -base64 -A";

			if($this->version==3.3)

				$comando="openssl dgst -sha256 -sign ".$archivoPEM." ".$archivoCadena." | openssl enc -base64 -A";

			$sello=shell_exec($comando);
			
			$nIntentos++;
			if($nIntentos==10)
			{
				$sello=$nIntentos;
			}

		}

		

		$cuerpoArchivo="";

		$archivoXML=$baseDir."/facturacionElectronica/".$idEmpresa."/".$idComprobante.".xml";

		

		$fp=fopen($archivoXML,"r");

		while ( ($linea = fgets($fp)) !== false) 

		{

			

			$cuerpoArchivo.=$linea;

		}

		fclose($fp);

		

		$cuerpoArchivo=str_replace("@selloDigital",$sello,$cuerpoArchivo);
		$fp=fopen($archivoXML,"w");

		if(!fwrite($fp,$cuerpoArchivo))

			return false;

		fclose($fp);

		unlink($archivoCadena);

		return true;

		

	}	

	

	function convertirXMLCadenaToObj($XML,$oComp=NULL)

	{



	}

	

	function cargarComprobanteXML($idComprobante)

	{

		global $baseDir;

		global $con;

		$consulta="SELECT idCertificado FROM 703_relacionFoliosCFDI WHERE idFolio=".$idComprobante;



		$idCertificado=$con->obtenerValor($consulta);

		$consulta="SELECT * FROM 687_certificadosSelloDigital WHERE idCertificado=".$idCertificado;

		$fCertificado=$con->obtenerPrimeraFila($consulta);

		$idEmpresa=$fCertificado[7];

		$archivoXML=$baseDir."/facturacionElectronica/".$idEmpresa."/".$idComprobante.".xml";

		$fp=fopen($archivoXML,"r");

		$cuerpoArchivo="";

		while ( ($linea = fgets($fp)) !== false) 

		{

			

			$cuerpoArchivo.=$linea;

		}

		fclose($fp);

		return $cuerpoArchivo;



	}

	

	function cargarComprobanteXMLObjeto($idComprobante)

	{

		global $con;

		$XML=$this->cargarComprobanteXML($idComprobante);

		

		$consulta="SELECT idCertificado,idSerie,idClienteFactura FROM 703_relacionFoliosCFDI WHERE idFolio=".$idComprobante;

		

		$fComprobante=$con->obtenerPrimeraFila($consulta);

		

		$oCertificado=array();

		$oCertificado["idCertificado"]=$fComprobante[0]; //* Obligatorio

		$oCertificado["idSerie"]=$fComprobante[1]; //* Obligatorio

		$oCertificado["idClienteFactura"]=$fComprobante[2]; //* Obligatorio

		

		return $this->convertirXMLCadenaToObj($XML,$oCertificado);

	}

		

	function estructuraXMLCFDIValida($XML)

	{

		global $baseDir;

		

		

		$XML=trim(str_replace("<cfdi:ComplementoConcepto/>","",$XML));

		$XML=str_replace("<cfdi:ComplementoConcepto />","",$XML);

		

		

		

		

//		varDump($XML);

		

		$XML=str_replace("\xEF\xBB\xBF","",$XML); 

		

		$tagIgnora="<cfdi:ComplementoConcepto";//"<cfdi:Addenda>"

		$tagInoraCierre="</cfdi:ComplementoConcepto>";

		$pos=strpos($XML,$tagIgnora);

		if($pos!==false)

		{

			$posFinal=strpos($XML,$tagInoraCierre);

			if($posFinal!==false)

			{

				$subcadena=substr($XML,0,$pos);

				$subcadena.=substr($XML,$posFinal+strlen($tagInoraCierre));

				$XML=$subcadena;

			}

			

		}

		

		

		$tagIgnora="<cfdi:Complemento";//"<cfdi:Addenda>"

		$tagInoraCierre="</cfdi:Complemento>";

		libxml_use_internal_errors(true);

		$xml = new DOMDocument(); 

		

		

		$pos=strpos($XML,$tagIgnora);

		if($pos!==false)

		{

			$posFinal=strpos($XML,$tagInoraCierre);

			$subcadena=substr($XML,0,$pos);

			$subcadena.=substr($XML,$posFinal+strlen($tagInoraCierre));

			$XML=$subcadena;

		}

		

		//varDump($XML);

		

		$tagIgnora="<cfdi:Addenda";//"<cfdi:Addenda>"

		$tagInoraCierre="</cfdi:Addenda>";

		$pos=strpos($XML,$tagIgnora);

		if($pos!==false)

		{

			

			$posFinal=strpos($XML,$tagInoraCierre);

			if($posFinal!==false)

			{

				$subcadena=substr($XML,0,$pos);

				$subcadena.=substr($XML,$posFinal+strlen($tagInoraCierre));

				$XML=$subcadena;

			}

			

		}

		

		

		

		

		//var_dump($XML);



		@$xml->loadXML($XML); 

		$resVal=(@$xml->schemaValidate($baseDir."/include/latis/cfdi/xsd/cfdv32.xsd"));

		$errors = libxml_get_errors();

		/*if(sizeof($errors)>0)

			varDump($errors);*/

		return $resVal;

	}

	

	function selloXMLCFDICorrecto($XML)

	{

		global $baseDir;

		$resultado=false;

		

		

		$rutaArchivo1="";

		$rutaArchivo1_5="";

		$rutaArchivo1_6="";

		$rutaArchivo2="";

		$rutaArchivo3="";

		$rutaArchivo4="";

		$rutaArchivo5="";



		

		$obj=$this->convertirXMLCadenaToObj($XML);



		$sello=trim($obj["sello"]);

		if((strpos($obj["certificado"],"\r")!==false)||(strpos($obj["certificado"],"\n")!==false))

			return true;

		$cadCertificado=trim($obj["certificado"]);



		

		$cuerpoArchivo="-----BEGIN CERTIFICATE-----\n";

		$posIni=0;

		for($posIni=0;$posIni<strlen($cadCertificado);$posIni+=64)

		{

			$cuerpoArchivo.=substr($cadCertificado,$posIni,64)."\n";

		}		

		$cuerpoArchivo.="-----END CERTIFICATE-----\n";

		

		

		$archivoTemp1=generarNombreArchivoTemporal();

		$rutaArchivo1=$baseDir."/archivosTemporales/".$archivoTemp1.".pem";

		if(escribirContenidoArchivo($rutaArchivo1,$cuerpoArchivo))

		{

			

			$cuerpoArchivo="";

			$posIni=0;

			for($posIni=0;$posIni<strlen($sello);$posIni+=64)

			{

				$cuerpoArchivo.=substr($sello,$posIni,64)."\n";

			}		

			

			$archivoTemp1_5=generarNombreArchivoTemporal();

			$rutaArchivo1_5=$baseDir."/archivosTemporales/".$archivoTemp1_5.".sello";

			$archivoTemp1_6=generarNombreArchivoTemporal();

			$rutaArchivo1_6=$baseDir."/archivosTemporales/".$archivoTemp1_6.".xml";

			if(escribirContenidoArchivo($rutaArchivo1_5,$cuerpoArchivo)&&escribirContenidoArchivo($rutaArchivo1_6,$XML))

			{

				$archivoTemp2=generarNombreArchivoTemporal();

				$rutaArchivo2=$baseDir."/archivosTemporales/".$archivoTemp2.".sbin";

				

				$comando="openssl enc -base64 -d -in ".$rutaArchivo1_5." >".$rutaArchivo2;

				shell_exec($comando);

				if(file_exists($rutaArchivo2))

				{

					  $archivoTemp3=generarNombreArchivoTemporal();

					  $rutaArchivo3=$baseDir."/archivosTemporales/".$archivoTemp3.".cad";

					  

					  $archivoTransformacion=$baseDir."/include/latis/cfdi/xslt/cadenaOriginal_3_2.xslt";

					  $nIntentos=0;

					  $cadenaOriginal="";

					  while($cadenaOriginal=="")

					  {

					  

						  $comando="xsltproc ".$archivoTransformacion." ".$rutaArchivo1_6." > ".$rutaArchivo3;

						  shell_exec($comando);

						  $cadenaOriginal=trim(leerContenidoArchivo($rutaArchivo3));

						  $nIntentos++;

						  if($nIntentos==10)

						  {

							  $resultado=false;

							  break;

						  }

					  }

					  

					  if($cadenaOriginal!="")

					  {

						  $archivoTemp4=generarNombreArchivoTemporal();

						  $rutaArchivo4=$baseDir."/archivosTemporales/".$archivoTemp4.".key";

						  

						  $comando="openssl x509 -in ".$rutaArchivo1." -pubkey -noout >".$rutaArchivo4;

						  shell_exec($comando);

						  if(file_exists($rutaArchivo4))

						  {

							  $archivoTemp5=generarNombreArchivoTemporal();

							  $rutaArchivo5=$baseDir."/archivosTemporales/".$archivoTemp5.".res";

							  $comando="openssl dgst -sha1 -verify ".$rutaArchivo4." -signature ".$rutaArchivo2." ".$rutaArchivo3." >".$rutaArchivo5;

							  shell_exec($comando);

							  $resultado=trim(leerContenidoArchivo($rutaArchivo5));

							  if($resultado=="Verified OK")

									$resultado=true;

							  else

							  		$resultado=false;

						  }

						  else

							  $resultado=false;

					  }

				}

				else

					$resultado=false;

			}

			else

				$resultado=false;

		}

		else

			$resultado=false;

			

		eliminarArchivo($rutaArchivo1);

		eliminarArchivo($rutaArchivo1_5);

		eliminarArchivo($rutaArchivo1_6);

		eliminarArchivo($rutaArchivo2);

		eliminarArchivo($rutaArchivo3);

		eliminarArchivo($rutaArchivo4);

		eliminarArchivo($rutaArchivo5);

		

		

		return $resultado;

		

	}

	

	function validarXMLSATWS($XML)

	{

		$obj=$this->convertirXMLCadenaToObj($XML);

		$re=$obj["datosEmisor"]["rfc"];

		$rr=$obj["datosReceptor"]["rfc"];

		$arrTotal=explode(".",$obj["total"]);

		

		$arrTotal[0]=str_pad($arrTotal[0],10,"0",STR_PAD_LEFT);

		

		$tt=$arrTotal[0];

		if(!isset($arrTotal[1]))

			$arrTotal[1]="0";

		$arrTotal[1]=str_pad($arrTotal[1],6,"0",STR_PAD_RIGHT);

		$tt=$arrTotal[0].".".$arrTotal[1];

		$id=$obj["folioUUID"];	

		$cadena="?re=".$re."&rr=".$rr."&tt=".$tt."&id=".trim($id);

		

		

		$url="https://consultaqr.facturaelectronica.sat.gob.mx/ConsultaCFDIService.svc?wsdl";

		$client = new SoapClient($url);

		$param = array	(

        					'expresionImpresa'=>$cadena

     					)	;

     

		$response = $client->Consulta($param);

		switch($response->ConsultaResult->Estado)

		{

			case "Vigente":

				return 1;

			break;

			case "No Encontrado":

				return 0;

			break;

			case "Cancelado":

				return 2;

			break;

		}

		

	}

	

	function obtenerImpuestosRetencionesObj($objComp)

	{

		global $con;

		$arrImpuestosRetenciones=array();

		

		

		$obj=array();

		$obj["tipoConcepto"]="";

		$obj["idConcepto"]="5";

		$obj["tasa"]="";

		$obj["montoConcepto"]=$objComp["subtotal"];

		array_push($arrImpuestosRetenciones,$obj);

		

		if($objComp["descuento"]=="")

			$objComp["descuento"]=0;

		$obj=array();

		$obj["tipoConcepto"]="";

		$obj["idConcepto"]="6";

		$obj["tasa"]="";

		$obj["montoConcepto"]=$objComp["descuento"];

		array_push($arrImpuestosRetenciones,$obj);



		$obj=array();

		$obj["tipoConcepto"]="";

		$obj["idConcepto"]="7";

		$obj["tasa"]="";

		$obj["montoConcepto"]=$objComp["subtotal"]-$objComp["descuento"];

		array_push($arrImpuestosRetenciones,$obj);



		

		$arrRetenciones=array();

		$arrTraslados=array();

		

		if(isset($objComp["impuestos"]))

		{

			if(sizeof($objComp["impuestos"]["traslados"])>0)

			{

				foreach($objComp["impuestos"]["traslados"] as $t)	

				{

					if(!isset($arrTraslados[strtoupper($t["impuesto"])]))

						$arrTraslados[strtoupper($t["impuesto"])]=0;

					$arrTraslados[strtoupper($t["impuesto"])]+=$t["importe"];

				}

			}

			

			if(sizeof($objComp["impuestos"]["retenciones"])>0)

			{

				foreach($objComp["impuestos"]["retenciones"] as $t)	

				{

					if(!isset($arrRetenciones[strtoupper($t["impuesto"])]))

						$arrRetenciones[strtoupper($t["impuesto"])]=0;

					$arrRetenciones[strtoupper($t["impuesto"])]+=$t["importe"];

				}

			}

			

		}

		

		if(isset($arrTraslados["IVA"]))

		{

			$obj=array();

			$obj["tipoConcepto"]="";

			$obj["idConcepto"]="1";

			$obj["tasa"]="";

			$obj["montoConcepto"]=$arrTraslados["IVA"];

			array_push($arrImpuestosRetenciones,$obj);

		}



		if(isset($arrRetenciones["ISR"]))

		{

			$obj=array();

			$obj["tipoConcepto"]="";

			$obj["idConcepto"]="3";

			$obj["tasa"]="";

			$obj["montoConcepto"]=$arrRetenciones["ISR"];

			array_push($arrImpuestosRetenciones,$obj);

		}

		

		if(isset($arrRetenciones["IVA"]))

		{

			$obj=array();

			$obj["tipoConcepto"]="";

			$obj["idConcepto"]="4";

			$obj["tasa"]="";

			$obj["montoConcepto"]=$arrRetenciones["IVA"];

			array_push($arrImpuestosRetenciones,$obj);

		}



		$obj=array();

		$obj["tipoConcepto"]="";

		$obj["idConcepto"]="12";

		$obj["tasa"]="";

		$obj["montoConcepto"]=$objComp["total"];

		array_push($arrImpuestosRetenciones,$obj);

		

		foreach($arrImpuestosRetenciones as $pos=>$i)

		{

			$consulta="SELECT tipoConcepto FROM 711_catalogoImpuestosRetenciones WHERE idConcepto=".$i["idConcepto"];

			$arrImpuestosRetenciones[$pos]["tipoConcepto"]=$con->obtenerValor($consulta);

		}

		

		

		return $arrImpuestosRetenciones;

	}

	

	

}

?>