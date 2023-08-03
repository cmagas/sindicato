<?php
class cCFDI 
{
	function cCFDI()
	{
		
	}
	
	function timbrarCFDI($objParametros)
	{
	}
	
	function cancelarCFDI($objParametros)
	{
	}
	
	function timbrarComprobante($idComprobante)
	{
	}
	
	function obtenerInformacionCFDIXML($xml)
	{
		
	}
	
	function obtenerInformacionCFDIUUID($uuid,$rfcEmisor)
	{
		
	}
	
	
	
	function visualizarXML($xml)
	{
		echo str_replace(">","]",str_replace("<","[",$xml));
	}
	
	function enviarComprobanteEmail($idComprobante,$reenvio=false)
	{
		global $con;
		global $baseDir;
		global $urlSitio;
		$arrArhivos=array();
		
		$arrParametros=array();
		$arrParametros["apPaterno"]=1;
		$arrParametros["apMaterno"]=1;
		$arrParametros["nombreContacto"]=1;
		$arrParametros["nombreCompleto"]=1;
		$arrParametros["empresaEmisora"]=1;

		$arrCC=array();
		$arrDestinatarios=array();
		$consulta="SELECT idCertificado,idClienteFactura,tipoUso,idSerie,folio,idReferencia FROM 703_relacionFoliosCFDI WHERE idFolio=".$idComprobante;

		$fDatos=$con->obtenerPrimeraFila($consulta);
		$idDestinatario=$fDatos[1];
		$consulta="SELECT idReferencia FROM 687_certificadosSelloDigital WHERE idCertificado=".$fDatos[0];
		$idEmpresaEmisora=$con->obtenerValor($consulta);	
		$consulta="SELECT serie FROM 688_seriesCertificados WHERE idSerieCertificado=".$fDatos[3];
		$serie=$con->obtenerValor($consulta);
		$nArchivo=$serie."_".$fDatos[4];
		$archivoXML=$baseDir."/facturacionElectronica/".$idEmpresaEmisora."/".$idComprobante.".xml";
		
		$archivoPDF="../archivosTemporales/".$idComprobante.".pdf";
		
		$arrArhivos[0][0]=$archivoXML;
		$arrArhivos[0][1]="Comprobante_".$nArchivo.".xml";
		
		$arrArhivos[1][0]=$archivoPDF;
		$arrArhivos[1][1]="Comprobante_".$nArchivo.".pdf";
		
		$consulta="SELECT IF(e.tipoEmpresa=1,CONCAT(razonSocial,' ',apPaterno,' ',apMaterno),razonSocial) AS nombreEmpresa,asuntoMensajeComprobante,cuerpoMailComprobante,asuntoMensajeNomina,cuerpoMailNomina,mailRemitenteComprobante,mailRemitenteNomina
					FROM 6927_empresas e WHERE e.idEmpresa=".$idEmpresaEmisora;

		$fDatosEmpresa=$con->obtenerPrimeraFila($consulta);
		$nombreEmpresa=$fDatosEmpresa[0];
		$tituloMail="";
		$cuerpoMail="";
		$mailRemitente="";
		if(!$reenvio)
		{
			$consulta="SELECT idContacto FROM 6929_contactoEmpresa WHERE idEmpresa=".$idEmpresaEmisora." AND destinatarioFactura=1";
			$res=$con->obtenerFilas($consulta);
			while($fila=mysql_fetch_row($res))
			{
				$consulta="SELECT mail FROM 6929_emailContacto WHERE idContacto=".$fila[0]." and trim(mail)<>''";
				$rMail=$con->obtenerFilas($consulta);	
				while($fMail=mysql_fetch_row($rMail))
				{
					$oM=array();
					$oM[0]=$fMail[0];
					$oM[1]="";
					array_push($arrCC,$oM);
				}
			}
		}
		
		$urlComprobante=$urlSitio;

		switch($fDatos[2])
		{
			case 1:
				
				$idUsuario=$idDestinatario;
				$consulta="SELECT Paterno,Materno,Nom FROM 802_identifica WHERE idUsuario=".$idUsuario;
				$fUsuario=$con->obtenerPrimeraFila($consulta);
				$obj=array();
				$obj["apPaterno"]=$fUsuario[0];
				$obj["apMaterno"]=$fUsuario[1];
				$obj["nombre"]=$fUsuario[2];
				$obj["nombreContacto"]=$fUsuario[2]." ".$fUsuario[0]." ".$fUsuario[1];
				$obj["empresaEmisora"]=$nombreEmpresa;
				$obj["mails"]=array();
				$consulta="SELECT Mail FROM 805_mails WHERE idUsuario=".$idUsuario." AND TRIM(Mail)<>''";
				$resM=$con->obtenerFilas($consulta);
				while($filaM=mysql_fetch_row($resM))
				{
					array_push($obj["mails"],$filaM[0])	;
				}
				array_push($arrDestinatarios,$obj);
				
				
				$tituloMail=$fDatosEmpresa[3];
				$cuerpoMail=$fDatosEmpresa[4];
				$mailRemitente=$fDatosEmpresa[6];
				$urlComprobante.="/formatosFacturasElectronicas/cfdiNomina_1.php";
			break;
			case 2:
				$idUsuario=$idDestinatario;
				
				$consulta="SELECT apPaterno,apMaterno,nombre FROM 693_empleadosNominaV2 WHERE idEmpleado=".$idUsuario;
				
				$fUsuario=$con->obtenerPrimeraFila($consulta);
				
				$obj=array();
				$obj["apPaterno"]=$fUsuario[0];
				$obj["apMaterno"]=$fUsuario[1];
				$obj["nombreContacto"]=$fUsuario[2];
				$obj["nombreCompleto"]=$fUsuario[2]." ".$fUsuario[0]." ".$fUsuario[1];
				$obj["mails"]=array();
				$obj["empresaEmisora"]=$nombreEmpresa;
				$consulta="SELECT mail FROM 694_emailContactoEmpleadoV2 WHERE idEmpleado=".$idUsuario." AND TRIM(mail)<>''";
				$resM=$con->obtenerFilas($consulta);
				while($filaM=mysql_fetch_row($resM))
				{
					array_push($obj["mails"],$filaM[0])	;
				}
				array_push($arrDestinatarios,$obj);
				$tituloMail=$fDatosEmpresa[3];
				$cuerpoMail=$fDatosEmpresa[4];
				$mailRemitente=$fDatosEmpresa[6];
				$urlComprobante.="/formatosFacturasElectronicas/cfdiNomina_1.php";
			break;	
			case 3:
				$idUsuario=$idDestinatario;
				$consulta="SELECT Paterno,Materno,Nom FROM 802_identifica WHERE idUsuario=".$idUsuario;
				$fUsuario=$con->obtenerPrimeraFila($consulta);
				$obj=array();
				$obj["apPaterno"]=$fUsuario[0];
				$obj["apMaterno"]=$fUsuario[1];
				$obj["nombreContacto"]=$fUsuario[2];
				$obj["nombreCompleto"]=$fUsuario[2]." ".$fUsuario[0]." ".$fUsuario[1];
				$obj["mails"]=array();
				$obj["empresaEmisora"]=$nombreEmpresa;
				$consulta="SELECT Mail FROM 805_mails WHERE idUsuario=".$idUsuario." AND TRIM(Mail)<>''";
				$resM=$con->obtenerFilas($consulta);
				while($filaM=mysql_fetch_row($resM))
				{
					array_push($obj["mails"],$filaM[0])	;
				}
				array_push($arrDestinatarios,$obj);
				$tituloMail=$fDatosEmpresa[3];
				$cuerpoMail=$fDatosEmpresa[4];
				$mailRemitente=$fDatosEmpresa[6];
				$urlComprobante.="/formatosFacturasElectronicas/cfdiNomina_1.php";
			break;	
			case 4:
			case 5:
				$consulta="SELECT idContacto,apPaterno,apMaterno, nombreContacto FROM 6929_contactoEmpresa WHERE idEmpresa=".$idDestinatario." AND destinatarioFactura=1";

				$res=$con->obtenerFilas($consulta);
				while($fila=mysql_fetch_row($res))
				{
					$obj=array();
					$obj["apPaterno"]=$fila[1];
					$obj["apMaterno"]=$fila[2];
					$obj["nombreContacto"]=$fila[3];
					$obj["nombreCompleto"]=$fila[3]." ".$fila[1]." ".$fila[2];
					$obj["mails"]=array();
					$obj["empresaEmisora"]=$nombreEmpresa;
					$consulta="SELECT mail FROM 6929_emailContacto WHERE idContacto=".$fila[0]." and trim(mail)<>''";

					$rMail=$con->obtenerFilas($consulta);	
					while($fMail=mysql_fetch_row($rMail))
					{
						array_push($obj["mails"],$fMail[0])	;
						
					}

					array_push($arrDestinatarios,$obj);
				}
				$tituloMail=$fDatosEmpresa[1];
				$cuerpoMail=$fDatosEmpresa[2];
				$mailRemitente=$fDatosEmpresa[5];
				
				
				
				$urlComprobante.="/formatosFacturasElectronicas/cfdi_1.php";
				
				$moduloComprobante="";
				
				$complemento=0;
				
				if($con->existeCampo("complemento","706_comprobanteFactura"))
				{
					$consulta="SELECT complemento FROM 706_comprobanteFactura WHERE idComprobanteFactura=".$fDatos[5];
					$complemento=$con->obtenerValor($consulta);
					if($complemento=="")
						$complemento=0;
	
				}
				
				if($complemento==1)
				{
					$consulta="SELECT plantillaCFDI FROM _1026_tablaDinamica WHERE empresa=".$idEmpresaEmisora;
					$moduloComprobante=$con->obtenerValor($consulta);
				}
				if($moduloComprobante=="")
				{
					$consulta="SELECT moduloComprobante FROM 6956_complementosComprobantes WHERE idRegistro=".$complemento;
					$moduloComprobante=$con->obtenerValor($consulta);
					
				}
				
				if($moduloComprobante!="")
				{
					$urlComprobante=$urlSitio."/".str_replace("../","",$moduloComprobante);
				}
								
				
				
				
			break;	
		}

		$urlComprobante.="?almacenarPDF=1&idComprobante=".$idComprobante;

		$comando="wget \"".$urlComprobante."\"";

		$resultado=shell_exec($comando);


		if($mailRemitente=="")
			$mailRemitente="noReply@noreply.com";

		if(sizeof($arrDestinatarios)>0)
		{
			foreach($arrDestinatarios as $d)
			{
				
				foreach($arrParametros as $params=>$resto)
				{
					
					$cuerpoMail=str_replace("@".$params,$d[$params],$cuerpoMail);
				}
				
				if(sizeof($d["mails"])>0)
				{
					$arrCopia=array();
					$mail=$d["mails"][0];
					if(sizeof($d["mails"])>1)
					{
						for($x=1;$x<sizeof($d["mails"]);$x++)
						{
							$obj=array();
							$obj[0]=$d["mails"][$x];
							$obj[1]="";
							array_push($arrCopia,$obj);
						}
					}
					
					enviarMail($mail,$tituloMail,$cuerpoMail,$mailRemitente,"",$arrArhivos,$arrCopia,$arrCC);	
						
						
				}
				
			}
		}
		unlink($archivoPDF);
		
		return true;
	}
}
?>