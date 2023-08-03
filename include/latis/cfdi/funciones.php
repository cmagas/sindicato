<?php  include_once("latis/cfdi/cFDIFinkok.php");


	$numHorasJusteCertificado=5;
	
	function esCertificadoSelloDigital($archivo)
	{
		$resultado=shell_exec("openssl x509 -inform DER -in ".$archivo." -subject -noout");
		if(strpos($resultado,"/OU=")!==false)
			return true;
		return false;	
	}
	
	
	function esContrasenaCorrecta($archivo,$password)
	{
		$consulta="openssl pkcs8 -inform DER  -in ".$archivo." -passin pass:".$password." -out /tmp/tmp.pem";

		$resultado=shell_exec("openssl pkcs8 -inform DER  -in ".$archivo." -passin pass:".$password." -out /tmp/tmp.pem");

		if(filesize("/tmp/tmp.pem")>0)
		{
			return true;
		}
		else
		{
			unlink("/tmp/tmp.pem");
			return false;	
		}
	}
	
	function obtenerCertificadoDigitalB64($archivo)
	{
		$certificado=shell_exec("openssl x509 -inform DER -in ".$archivo);
		$certificado=str_replace("\r","",$certificado);
		$certificado=str_replace("\n","",$certificado);
		$certificado=str_replace("-----BEGIN CERTIFICATE-----","",$certificado);
		$certificado=str_replace("-----END CERTIFICATE-----","",$certificado);
		return trim($certificado);
	}
	
	function obtenerFechaInicioVigencia($archivo)
	{
		global $numHorasJusteCertificado;		
		$certificado=shell_exec("openssl x509 -inform DER -in ".$archivo." -startdate -noout");
		$certificado=str_replace("\r","",$certificado);
		$certificado=str_replace("\n","",$certificado);
		$arrFecha=explode("notBefore=",$certificado);
		$fecha=strtotime("+".$numHorasJusteCertificado." hours",strtotime($arrFecha[1]));
		return date("Y-m-d H:i:s",$fecha);
	}
	
	function obtenerFechaFinVigencia($archivo)
	{
		global $numHorasJusteCertificado;	
		
		$certificado=shell_exec("openssl x509 -inform DER -in ".$archivo." -enddate -noout");
		$certificado=str_replace("\r","",$certificado);
		$certificado=str_replace("\n","",$certificado);
		$arrFecha=explode("notAfter=",$certificado);
		$fecha=strtotime("+".$numHorasJusteCertificado." hours",strtotime($arrFecha[1]));
		return date("Y-m-d H:i:s",$fecha);
	}
	
	function obtenerNumCertificado($archivo)
	{
		$certificado=shell_exec("openssl x509 -inform DER -in ".$archivo." -serial -noout");
		$certificado=str_replace("\r","",$certificado);
		$certificado=str_replace("\n","",$certificado);
		$arrElementos=explode("serial=",$certificado);
		
		$resultado="";
		for($x=1;$x<strlen($arrElementos[1]);$x+=2)
		{
			$resultado.=$arrElementos[1][$x];
		}
		return $resultado;
	}
	
	function convertirKeyToPem($archivo,$password)
	{
		$arrArchivo=explode(".",$archivo);
		$resultado=shell_exec("openssl pkcs8 -inform DER  -in ".$archivo." -passin pass:".$password." -out ".$arrArchivo[0].".pem");
		return true;
	}
	
	function convertirCerToPem($archivo)
	{
		$arrArchivo=explode(".",$archivo);
		$resultado=shell_exec("openssl x509 -inform DER -outform PEM -in ".$archivo."   -out ".$arrArchivo[0].".cer.pem");
		return true;
	}
	
	function convertirKeyToPemFinkok($archivo)
	{
		$c=new cFDIFinkok();
		$arrArchivo=explode(".",$archivo);
		$passFinkok=$c->getPassword();
		$resultado=shell_exec('openssl rsa -in '.$archivo.' -des3 -out '.$arrArchivo[0].'.finkok.key -passout pass:"'.$passFinkok.'"');
		return true;
	}
	
	
	function verificarCompatibilidadArchivosCerKey($archivoCer,$archivoKey)
	{
		$comando1="openssl x509 -inform DER -in ".$archivoCer." -noout -modulus";
		$resultado1=shell_exec($comando1);
		
		$comando2="openssl rsa -in ".$archivoKey." -noout -modulus";
		$resultado2=shell_exec($comando2);
		
		
		
		if($resultado1!=$resultado2)
			return false;
		return true;
		
	}
	
	


	
	
	function existeCertificado($noCertificado)
	{
		global $con;
		$consulta="SELECT COUNT(*) FROM 687_certificadosSelloDigital WHERE noCertificado='".$noCertificado."'";
		$nReg=$con->obtenerValor($consulta);
		if($nReg>0)
			return true;
		return false;
	}
	
	function obtenerFolioCertificado($idSerie)
	{
		global $con;
		$x=0;
		$consulta[$x]="begin";
		$x++;
		$consulta[$x]="set @folio=(SELECT folioActual FROM 688_seriesCertificados WHERE idSerieCertificado=".$idSerie." FOR UPDATE)";
		$x++;
		$consulta[$x]="update 688_seriesCertificados set folioActual=folioActual+1  WHERE idSerieCertificado=".$idSerie."";
		$x++;
		$consulta[$x]="commit";
		$x++;
		if($con->ejecutarBloque($consulta))
		{
			$query="select @folio";
			$folio=$con->obtenerValor($query);	
			return $folio;
		}
		return "";
	}
	
	function obtenerSeccionDatosEmisor($idEmpresa)
	{
		global $con;
		$consulta="SELECT * FROM 6927_empresas WHERE idEmpresa=".$idEmpresa;
		$fEmpresa=$con->obtenerPrimeraFila($consulta);	
		
		
		$razonSocial="";
		if($fEmpresa[1]==1)
			$razonSocial=$fEmpresa[2]." ".$fEmpresa[3]." ".$fEmpresa[4];
		else
			$razonSocial=$fEmpresa[2];
			
		$consulta="SELECT UPPER(municipio) FROM 821_municipios WHERE cveMunicipio='".$fEmpresa[14]."'";
		$municipioEmisor=$con->obtenerValor($consulta);
		$consulta="SELECT UPPER(estado) FROM 820_estados WHERE cveEstado='".$fEmpresa[13]."'";
		$estadoEmisor=$con->obtenerValor($consulta);	
		
		$objDatos["rfc"]=$fEmpresa[5].$fEmpresa[6].$fEmpresa[7];
		$objDatos["razonSocial"]=$razonSocial;
		$objDatos["regimenFiscal"]=explode("|",$fEmpresa[21]);
		$objDatos["regPatronal"]=$fEmpresa[22];
		$objDatos["domicilio"]["calle"]=$fEmpresa[8];
		$objDatos["domicilio"]["noExterior"]=$fEmpresa[9];
		$objDatos["domicilio"]["noInterior"]=$fEmpresa[19];
		$objDatos["domicilio"]["colonia"]=$fEmpresa[10];
		$objDatos["domicilio"]["localidad"]=$fEmpresa[17];
		$objDatos["domicilio"]["municipio"]=$municipioEmisor;
		$objDatos["domicilio"]["estado"]=$estadoEmisor;
		$objDatos["domicilio"]["pais"]="MÉXICO";
		$objDatos["domicilio"]["codigoPostal"]=$fEmpresa[11];
		
		return $objDatos;
		
	}
	
	function normalizarRFC($rfc)
	{
		$rfcFinal="";
		for($x=0;$x<strlen($rfc);$x++)	
		{
			if(is_numeric($rfc[$x])||(ctype_alpha($rfc[$x])))
				$rfcFinal.=$rfc[$x];	
		}
		return $rfcFinal;
	}
	
	function normalizarSoloNumeros($valor)
	{
		$cadFinal="";
		for($x=0;$x<strlen($valor);$x++)	
		{
			if(is_numeric($valor[$x]))
				$cadFinal.=$valor[$x];	
		}
		return $cadFinal;
	}
	


	//Funciones validación Fiel

	function esCertificadoFiel($archivo)
	{
		$resultado=shell_exec("openssl x509 -inform DER -in ".$archivo." -subject -noout");
		if(strpos($resultado,"/OU=")!==false)
			return false;
		return true;	
	}

	function obtenerRFCCertificado($archivo)
	{
		$resultado=shell_exec("openssl x509 -inform DER -in ".$archivo." -subject -noout");
		$pos=strpos($resultado,"UniqueIdentifier=");
		$resultado=substr($resultado,($pos+strlen("UniqueIdentifier=")));
		if($pos!==false)
		{
			$pos2=strpos($resultado,"/");
		
			$RFC=trim(substr($resultado,0,$pos2));
			
			return $RFC;
		}
		return "";		
	}
	
	function esCertificadoEmpresa($idEmpresa,$archivo)
	{
		global $con;
		
		$consulta="SELECT CONCAT(rfc1,rfc2,rfc3) FROM 6927_empresas WHERE idEmpresa=".$idEmpresa;
		$rfc1=$con->obtenerValor($consulta);
		$rfc2=obtenerRFCCertificado($archivo);
		if($rfc1==$rfc2)
			return true;	
		return false;
		
	}
?>