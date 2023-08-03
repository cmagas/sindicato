<?php

include_once("latis/cfdi/cComprobante.php");
include_once("latis/cfdi/cFactura.php");

class cSendMail
{
	var $servidor;
	var $usuario;
	var $password;
	var $conexion;
	var $arrBuzonesEstructuraLatis;
	
	function cSendMail($s,$u,$p,$portPOP3=110,$aBuzones=NULL)
	{
		global $baseDir;
		$cComp=new cFacturaCFDI();
		$this->servidor=$s;
		$this->usuario=$u;
		$this->password=$p;
		
		
		$this->arrBuzonesEstructuraLatis[0]=$this->servidor."INBOX.ProcesadosLatis";
		$this->arrBuzonesEstructuraLatis[1]=$this->servidor."INBOX.ProcesadosLatis.CorreosVarios";
		if($aBuzones!=NULL)
		{
			$this->arrBuzonesEstructuraLatis=array();
			foreach($aBuzones as $b)
			{
				array_push($this->arrBuzonesEstructuraLatis,$this->servidor.$b);
			}
		}
	}
		
	function conectarServidor()
	{
		$this->conexion=imap_open($this->servidor,$this->usuario,$this->password);
		if($this->conexion)
			return true;
		else
			return false;
	}
	
	function obtenerDirectorioBuzon()
	{
		$directorios=imap_getmailboxes($this->conexion,$this->servidor,"*");	
		return $directorios;
	}
	
	function cerrarConexionServidor()
	{
		imap_close($this->conexion,CL_EXPUNGE);
	}
	
	function obtenerArchivosAdjuntos($estructura,$uidMail)
	{
		$adjuntos=array();
		
		if(isset($estructura->parts)&&(count($estructura->parts)))
		{

			for($i = 0; $i < count ( $estructura->parts ); $i ++) 
			{
				$adjuntos[$i] = array ();
											
				$adjuntos[$i]["is_attachment"]=false;
				$adjuntos[$i]["filename"]="";
				
				$adjuntos[$i]["attachment"]="";
											
				if($estructura->parts[$i]->ifdparameters) 
				{
					foreach($estructura->parts[$i]->dparameters as $object ) 
					{
						if (strtolower($object->attribute) == 'filename') 
						{
							$adjuntos[$i]['is_attachment'] = true;
							$adjuntos[$i]['filename'] = $object->value;
						}
					}
				}
				
				if($estructura->parts[$i]->ifparameters) 
				{
					foreach($estructura->parts[$i]->parameters as $object ) 
					{
						if(strtolower($object->attribute ) == 'name') 
						{
							$adjuntos[$i]['is_attachment'] = true;
							$adjuntos[$i]['filename'] = $object->value;
						}
					}
				}
				
				if($adjuntos[$i]['is_attachment']) 
				{
					$adjuntos[$i]['attachment'] = imap_fetchbody ($this->conexion, $uidMail, ($i+1),FT_UID);
					if($estructura->parts[$i]->encoding == 3) 
					{ 
						$adjuntos[$i]['attachment'] = base64_decode($adjuntos[$i]['attachment']);
					} 
					else
						if ($estructura->parts [$i]->encoding == 4) 
						{ 
							$adjuntos[$i]['attachment'] = quoted_printable_decode($adjuntos[$i]['attachment']);
						}
				}
        	}
		}
		$arrAdjuntosFinal=array();
		foreach($adjuntos as $a)
		{
			if($a["is_attachment"])	
			{
				array_push($arrAdjuntosFinal,$a);
			}
		}
		
		return $arrAdjuntosFinal;
	}	
	
	function esEmailMultiparte($estructura) 
	{
	  	if($estructura->type == 1) 
		{
			return true; ## Mensaje multiparte
		}
	 	else
		{
			return false; ## Mensaje No Multiparte
		}
	}
	
	function prepararEstructuraBuzonLatis()
	{
		$arrDirectorios=$this->obtenerDirectorioBuzon();
		
		foreach($this->arrBuzonesEstructuraLatis as $pos=>$d)
		{
			
			$enc=false;
			foreach($arrDirectorios as $dBuzon)
			{
				if($dBuzon->name==$d)	
					$enc=true;
			}
			if(!$enc)	
			{
				if(imap_createmailbox($this->conexion,$d))
					imap_subscribe($this->conexion,$d);
						
			}
		}
		
		return true;
	}
	
	function obtenerCorreosBandeja($bandeja)
	{
		imap_reopen($this->conexion,$bandeja);
		$emails = imap_search($this->conexion,'ALL',SE_UID);
		imap_reopen($this->conexion,$this->servidor);
		return $emails;
	}
	
	
	function obtenerCabeceraMail($iMail)
	{
		$cabecera = imap_fetch_overview($this->conexion,$iMail,FT_UID);	
		return $cabecera[0];
	}
	
	function obtenerCuerpoMail($iMail,$escaparEnter=false)
	{
		$mensaje = "";
		$header = imap_header($this->conexion, $iMail);
		$structure = imap_fetchstructure($this->conexion, $iMail,FT_UID);
		
		if ($this->esEmailMultiparte($structure))
		{

			$mensaje = imap_fetchbody($this->conexion,$iMail,"1",FT_UID); ## GET THE BODY OF MULTI-PART MESSAGE
			if(!$mensaje) 
			{
				$mensaje = '';
			}
		}
		else
		{

			$mensaje = imap_body($this->conexion, $iMail,FT_UID);
			if(!$mensaje) 
			{
				$mensaje = '';
			}
		}
		
		$mensaje= (quoted_printable_decode($mensaje));
		if($escaparEnter)
		{
			$mensaje=str_replace("\n","<br>",$mensaje);
			$mensaje=str_replace("\r\r","\r",$mensaje);
			$mensaje=str_replace("\r","<br>",$mensaje);		
	
		}
		return $mensaje;
	}
	
	function obtenerAdjuntosMail($iMail)
	{
		$estructura=imap_fetchstructure($this->conexion,$iMail, FT_UID);
		$arrAdjuntos=$this->obtenerArchivosAdjuntos($estructura,$iMail);	
		return  $arrAdjuntos;
	}
	
	function moverCorreoBuzon($iMail,$buzon)
	{
		imap_mail_move($this->conexion,$iMail,$buzon,FT_UID);	
	}
	
	function obtenerRemitente($iMail)
	{
		$cabecera=$this->obtenerCabeceraMail($iMail);

		$arrMail=explode("<",$cabecera->from);
		if(sizeof($arrMail)>1)
		{
			
			$mail=trim(str_replace(">","",$arrMail[1]));	
			$arrMail[1]=$mail;
		}
		else
		{
			$arrMail[1]=$arrMail[0];	
		}
		
		return $arrMail;
	}
	
	function obtenerDestinatario($iMail)
	{
		$cabecera=$this->obtenerCabeceraMail($iMail);
		
		$arrMail=explode("<",$cabecera->to);
		if(sizeof($arrMail)>1)
		{
			
			$mail=trim(str_replace(">","",$arrMail[1]));	
			$arrMail[1]=$mail;
		}
		else
		{
			$arrMail[1]=$arrMail[0];	
		}
		
		return $arrMail;
	}
	
	function obtenerFechaMail($iMail,$formato="Y-m-d H:i:s")
	{
		$cabecera=$this->obtenerCabeceraMail($iMail);
		$fecha=strtotime($cabecera->date);
		$fechaMail=date($formato,$fecha);	
		return $fechaMail;
	}
	
	function obtenerAsuntoMail($iMail)
	{
		$cabecera=$this->obtenerCabeceraMail($iMail);
		$asunto=$cabecera->subject;
		return utf8_encode($asunto);
	}
	
	function registrarMailPlataforma($iMail,$idReferencia="NULL",$tipoReferencia="NULL")
	{
		global $con;
		global $baseDir;
		$rutaTmp=$baseDir."/archivosTemporales/";
		$x=0;
		$query[$x]="begin";
		$x++;
		$fechaMail=$this->obtenerFechaMail($iMail);
		$remitente=$this->obtenerRemitente($iMail);
		$destinatario=$this->obtenerDestinatario($iMail);
		$asunto=$this->obtenerAsuntoMail($iMail);
		$cuerpo=$this->obtenerCuerpoMail($iMail,true);
		$arrAdjuntos=$this->obtenerAdjuntosMail($iMail);
		
		$query[$x]="INSERT INTO 3027_eMailRecibidos(asunto,cuerpo,remitente,destinatario,fechaRegistro,situacion,fechaMail,idReferencia,tipoReferencia,situacion2) 
					VALUES('".cv($asunto)."','".cv($cuerpo)."','".$remitente[1]."','".$destinatario[1]."','".date("Y-m-d H:i:s")."',0,'".$fechaMail."',".$idReferencia.",".$tipoReferencia.",0)";

		$x++;
		
		$query[$x]="set @idMail:=(select last_insert_id())";
		$x++;
		foreach($arrAdjuntos as $a)
		{
			$documento=generarNombreArchivoTemporal(1);
			escribirContenidoArchivo($rutaTmp.$documento,$a["attachment"]);
			$idAdjunto=registrarDocumentoServidor($documento,$a["filename"]);
			$query[$x]="INSERT INTO 3028_eMailAdjuntos(idMail,idArchivo) VALUES(@idMail,".$idAdjunto.")";
			$x++;
		}	

		$query[$x]="commit";
		$x++;
		if($con->ejecutarBloque($query))
		{
			$consulta="select @idMail";
			$idMail=$con->obtenerValor($consulta);
			return $idMail;	
		}
		return false;
	}
}

?>