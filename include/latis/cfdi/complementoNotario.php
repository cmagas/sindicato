<?php 
include_once("latis/conexionBD.php"); 

function generarComplementoNotario($obj)
{
	global $con;
	
	if($obj["datosComplementoNotario"]["incluirComplemento"]==0)
		return "";
	
	$cadCompNotario="<notariospublicos:NotariosPublicos xmlns:notariospublicos=\"http://www.sat.gob.mx/notariospublicos\" Version=\"1.0\">";
		$cadCompNotario.="<notariospublicos:DescInmuebles>";
			foreach($obj["datosComplementoNotario"]["datosInmueble"] as $d)
			{
				$TipoInmueble=formatearValorElementoCFDI("TipoInmueble",$d["idTipoInmueble"]);	
				$Calle=formatearValorElementoCFDI("Calle",$d["calle"]);	
				$NoExterior=formatearValorElementoCFDI("NoExterior",$d["noExterior"]);	
				$NoInterior=formatearValorElementoCFDI("NoInterior",$d["noInterior"]);	
				$Colonia=formatearValorElementoCFDI("Colonia",$d["colonia"]);	
				$Localidad=formatearValorElementoCFDI("Localidad",$d["localidad"]);	
				$Referencia=formatearValorElementoCFDI("Referencia",$d["referencia"]);	
				$Municipio=formatearValorElementoCFDI("Municipio",$d["municipio"]);	
				$Estado=formatearValorElementoCFDI("Estado",$d["estado"]);	
				$Pais=formatearValorElementoCFDI("Pais",$d["pais"]);	
				$CodigoPostal=formatearValorElementoCFDI("CodigoPostal",$d["cp"]);	
				$atributos=$TipoInmueble." ".$Calle." ".$NoExterior." ".$NoInterior." ".$Colonia." ".$Localidad." ".$Referencia." ".$Municipio." ".$Estado." ".$Pais." ".$CodigoPostal;
				$cadCompNotario.="<notariospublicos:DescInmueble ".$atributos." ></notariospublicos:DescInmueble>";
			}
		$cadCompNotario.="</notariospublicos:DescInmuebles>";
			

		$NumInstrumentoNotarial=formatearValorElementoCFDI("NumInstrumentoNotarial",$obj["datosComplementoNotario"]["datosOperacion"]["noInstrumentoNotarial"]);	
		$FechaInstNotarial=formatearValorElementoCFDI("FechaInstNotarial",date("Y-m-d",strtotime($obj["datosComplementoNotario"]["datosOperacion"]["fechaInstrumentoNotarial"])));	
		$MontoOperacion=formatearValorElementoCFDI("MontoOperacion",formatearValorMonetarioCFDI($obj["datosComplementoNotario"]["datosOperacion"]["total"]));	
		$Subtotal=formatearValorElementoCFDI("Subtotal",formatearValorMonetarioCFDI($obj["datosComplementoNotario"]["datosOperacion"]["subtotal"]));	
		$IVA=formatearValorElementoCFDI("IVA",formatearValorMonetarioCFDI($obj["datosComplementoNotario"]["datosOperacion"]["iva"]));			

		$atributos=$NumInstrumentoNotarial." ".$FechaInstNotarial." ".$MontoOperacion." ".$Subtotal." ".$IVA;
				
		$cadCompNotario.="<notariospublicos:DatosOperacion ".$atributos."></notariospublicos:DatosOperacion>";
		
		
		$CURP=formatearValorElementoCFDI("CURP",$obj["datosComplementoNotario"]["datosNotaria"]["curpNotario"]);	
		$NumNotaria=formatearValorElementoCFDI("NumNotaria",$obj["datosComplementoNotario"]["datosNotaria"]["noNotaria"]);	
		$EntidadFederativa=formatearValorElementoCFDI("EntidadFederativa",$obj["datosComplementoNotario"]["datosNotaria"]["entidadFederativa"]);	
		$Adscripcion=formatearValorElementoCFDI("Adscripcion",$obj["datosComplementoNotario"]["datosNotaria"]["adscripcion"]);	
		
		$atributos=$CURP." ".$NumNotaria." ".$EntidadFederativa." ".$Adscripcion;
		
		$cadCompNotario.="<notariospublicos:DatosNotario ".$atributos."></notariospublicos:DatosNotario>";
		
		$CoproSocConyugalE=($obj["datosComplementoNotario"]["tipoEnajenante"]==2)?'Si':'No';

		$cadCompNotario.="<notariospublicos:DatosEnajenante CoproSocConyugalE=\"".$CoproSocConyugalE."\">";
		
		if($obj["datosComplementoNotario"]["tipoEnajenante"]==2)
		{
			$cadCompNotario.="<notariospublicos:DatosEnajenantesCopSC>";
			
			foreach($obj["datosComplementoNotario"]["datosEnajenante"] as $d)
			{
				
				$Nombre=formatearValorElementoCFDI("Nombre",$d["nombre"]);	
				$ApellidoPaterno=formatearValorElementoCFDI("ApellidoPaterno",$d["apPaterno"]);	
				$ApellidoMaterno=formatearValorElementoCFDI("ApellidoMaterno",$d["apMaterno"]);	
				$RFC=formatearValorElementoCFDI("RFC",$d["rfc"]);	
				$CURP=formatearValorElementoCFDI("CURP",$d["curp"]);	
				$Porcentaje=formatearValorElementoCFDI("Porcentaje",str_replace(",","",number_format($d["porcentaje"],2)));	
							
				$atributos=$Nombre." ".$ApellidoPaterno." ".$ApellidoMaterno." ".$RFC." ".$CURP." ".$Porcentaje;
							
				$cadCompNotario.="<notariospublicos:DatosEnajenanteCopSC ".$atributos."></notariospublicos:DatosEnajenanteCopSC>";
			}
			
			$cadCompNotario.="</notariospublicos:DatosEnajenantesCopSC>";
		}
		else
		{
			
			$Nombre=formatearValorElementoCFDI("Nombre",$obj["datosComplementoNotario"]["datosEnajenante"][0]["nombre"]);	
			$ApellidoPaterno=formatearValorElementoCFDI("ApellidoPaterno",$obj["datosComplementoNotario"]["datosEnajenante"][0]["apPaterno"]);	
			$ApellidoMaterno=formatearValorElementoCFDI("ApellidoMaterno",$obj["datosComplementoNotario"]["datosEnajenante"][0]["apMaterno"]);	
			$RFC=formatearValorElementoCFDI("RFC",$obj["datosComplementoNotario"]["datosEnajenante"][0]["rfc"]);	
			$CURP=formatearValorElementoCFDI("CURP",$obj["datosComplementoNotario"]["datosEnajenante"][0]["curp"]);	
						
			$atributos=$Nombre." ".$ApellidoPaterno." ".$ApellidoMaterno." ".$RFC." ".$CURP;
						
			$cadCompNotario.="<notariospublicos:DatosUnEnajenante ".$atributos."></notariospublicos:DatosUnEnajenante>";

		}
		
		
		$cadCompNotario.="</notariospublicos:DatosEnajenante>";
		
	
		$CoproSocConyugalE=($obj["datosComplementoNotario"]["tipoAdquiriente"]==2)?'Si':'No';
	
		$cadCompNotario.="<notariospublicos:DatosAdquiriente CoproSocConyugalE=\"".$CoproSocConyugalE."\">";
		
		if($obj["datosComplementoNotario"]["tipoAdquiriente"]==2)
		{
			$cadCompNotario.="<notariospublicos:DatosAdquirientesCopSC>";
			
			foreach($obj["datosComplementoNotario"]["datosAdquiriente"] as $d)
			{
				
				$Nombre=formatearValorElementoCFDI("Nombre",$d["nombre"]);	
				$ApellidoPaterno=formatearValorElementoCFDI("ApellidoPaterno",$d["apPaterno"]);	
				$ApellidoMaterno=formatearValorElementoCFDI("ApellidoMaterno",$d["apMaterno"]);	
				$RFC=formatearValorElementoCFDI("RFC",$d["rfc"]);	
				$CURP=formatearValorElementoCFDI("CURP",$d["curp"]);	
				$Porcentaje=formatearValorElementoCFDI("Porcentaje",str_replace(",","",number_format($d["porcentaje"],2)));	
							
				$atributos=$Nombre." ".$ApellidoPaterno." ".$ApellidoMaterno." ".$RFC." ".$CURP." ".$Porcentaje;
							
				$cadCompNotario.="<notariospublicos:DatosAdquirienteCopSC ".$atributos."></notariospublicos:DatosAdquirienteCopSC>";
			}
			
			$cadCompNotario.="</notariospublicos:DatosAdquirientesCopSC>";
		}
		else
		{
			
			$Nombre=formatearValorElementoCFDI("Nombre",$obj["datosComplementoNotario"]["datosAdquiriente"][0]["nombre"]);	
			$ApellidoPaterno=formatearValorElementoCFDI("ApellidoPaterno",$obj["datosComplementoNotario"]["datosAdquiriente"][0]["apPaterno"]);	
			$ApellidoMaterno=formatearValorElementoCFDI("ApellidoMaterno",$obj["datosComplementoNotario"]["datosAdquiriente"][0]["apMaterno"]);	
			$RFC=formatearValorElementoCFDI("RFC",$obj["datosComplementoNotario"]["datosAdquiriente"][0]["rfc"]);	
			$CURP=formatearValorElementoCFDI("CURP",$obj["datosComplementoNotario"]["datosAdquiriente"][0]["curp"]);	
						
			$atributos=$Nombre." ".$ApellidoPaterno." ".$ApellidoMaterno." ".$RFC." ".$CURP;
						
			$cadCompNotario.="<notariospublicos:DatosUnAdquiriente ".$atributos."></notariospublicos:DatosUnAdquiriente>";

		}
		
		
		$cadCompNotario.="</notariospublicos:DatosAdquiriente>";
			
		
		
	$cadCompNotario.="</notariospublicos:NotariosPublicos>";
	return $cadCompNotario;
}


function generarComplementoNotarioJSON(&$obj,$objRef)
{
	global $con;

	$obj["datosComplementoNotario"]=array();
	$obj["datosComplementoNotario"]["incluirComplemento"]=$objRef->datosComplemento->incluirComplemento;
	$obj["datosComplementoNotario"]["datosNotaria"]=array();
	$obj["datosComplementoNotario"]["datosNotaria"]["noNotaria"]=$objRef->datosComplemento->datosNotaria->noNotaria;
	$obj["datosComplementoNotario"]["datosNotaria"]["curpNotario"]=$objRef->datosComplemento->datosNotaria->curpNotario;
	$obj["datosComplementoNotario"]["datosNotaria"]["entidadFederativa"]=$objRef->datosComplemento->datosNotaria->entidadFederativa;
	$obj["datosComplementoNotario"]["datosNotaria"]["adscripcion"]=$objRef->datosComplemento->datosNotaria->adscripcion;
	$obj["datosComplementoNotario"]["datosOperacion"]=array();
	$obj["datosComplementoNotario"]["datosOperacion"]["noInstrumentoNotarial"]=$objRef->datosComplemento->datosOperacion->noInstrumentoNotarial;
	$obj["datosComplementoNotario"]["datosOperacion"]["fechaInstrumentoNotarial"]=$objRef->datosComplemento->datosOperacion->fechaInstrumentoNotarial;
	$obj["datosComplementoNotario"]["datosOperacion"]["subtotal"]=$objRef->datosComplemento->datosOperacion->subtotal;
	$obj["datosComplementoNotario"]["datosOperacion"]["iva"]=$objRef->datosComplemento->datosOperacion->iva;
	$obj["datosComplementoNotario"]["datosOperacion"]["total"]=$objRef->datosComplemento->datosOperacion->total;
	$obj["datosComplementoNotario"]["datosInmueble"]=array();
	
	foreach($objRef->datosComplemento->datosInmueble as $d)
	{
		$oAux=array();
		$oAux["idTipoInmueble"]=$d->idTipoInmueble;
		$oAux["calle"]=$d->calle;
		$oAux["noExterior"]=$d->noExterior;
		$oAux["noInterior"]=$d->noInterior;
		$oAux["colonia"]=$d->colonia;
		$oAux["localidad"]=$d->localidad;
		$oAux["referencia"]=$d->referencia;
		$oAux["municipio"]=$d->municipio;
		$oAux["estado"]=$d->estado;
		$oAux["pais"]=$d->pais;
		$oAux["cp"]=$d->cp;
		array_push($obj["datosComplementoNotario"]["datosInmueble"],$oAux);
		
		
	}
	
	$obj["datosComplementoNotario"]["tipoEnajenante"]=$objRef->datosComplemento->tipoPropietario;
	$obj["datosComplementoNotario"]["datosEnajenante"]=array();
	
	foreach($objRef->datosComplemento->datosEnajenante as $d)
	{
		$oAux=array();
		$oAux["apPaterno"]=$d->apPaterno;
		$oAux["apMaterno"]=$d->apMaterno;
		$oAux["nombre"]=$d->nombre;
		$oAux["rfc"]=$d->rfc;
		$oAux["curp"]=$d->curp;
		$oAux["tipoPersona"]=$d->tipoPersona;
		$oAux["porcentaje"]=$d->porcentaje;
		array_push($obj["datosComplementoNotario"]["datosEnajenante"],$oAux);
		
		
	}
	
	$obj["datosComplementoNotario"]["tipoAdquiriente"]=$objRef->datosComplemento->tipoAdquiriente;
	$obj["datosComplementoNotario"]["datosAdquiriente"]=array();
	
	foreach($objRef->datosComplemento->datosAdquiriente as $d)
	{
		$oAux=array();
		$oAux["apPaterno"]=$d->apPaterno;
		$oAux["apMaterno"]=$d->apMaterno;
		$oAux["nombre"]=$d->nombre;
		$oAux["rfc"]=$d->rfc;
		$oAux["curp"]=$d->curp;
		$oAux["tipoPersona"]=$d->tipoPersona;
		$oAux["porcentaje"]=$d->porcentaje;
		array_push($obj["datosComplementoNotario"]["datosAdquiriente"],$oAux);
		
		
	}
	
	
}

function generarComplementoNotarioQueryGuardar($obj,&$consulta,&$x)
{
	global $con;
	$consulta[$x]="INSERT INTO 724_datosComplementoNotario(idComprobante,fechaInstrumentoNotarial,numeroInstrumentoNotarial,montoOperacion,subtotal,iva,incluirComplemento,
				tipoEnajenante,tipoAdquiriente)
				VALUES(@idComprobante,'".$obj->datosComplemento->datosOperacion->fechaInstrumentoNotarial."',".$obj->datosComplemento->datosOperacion->noInstrumentoNotarial.
				",".$obj->datosComplemento->datosOperacion->total.",".$obj->datosComplemento->datosOperacion->subtotal.",".$obj->datosComplemento->datosOperacion->iva.
				",".$obj->datosComplemento->incluirComplemento.",".$obj->datosComplemento->tipoPropietario.",".$obj->datosComplemento->tipoAdquiriente.")";
	$x++;			
	
	foreach($obj->datosComplemento->datosInmueble as $i)
	{
		$consulta[$x]="INSERT INTO 725_inmueblesComplementoNotario(idComprobante,tipoInmueble,calle,noExterior,noInterior,
						colonia,localidad,referencia,municipio,estado,pais,cp)
						VALUES(@idComprobante,'".$i->idTipoInmueble."','".cv($i->calle)."','".cv($i->noExterior)."','".cv($i->noInterior)."','".cv($i->colonia).
						"','".cv($i->localidad)."','".cv($i->referencia)."','".cv($i->municipio)."','".cv($i->estado)."','".$i->pais."',".$i->cp.")";
		$x++;					
	}
	
	
	foreach($obj->datosComplemento->datosEnajenante as $i)
	{
		$consulta[$x]="INSERT INTO 726_involucradosComplementoNotario(idComprobante,apellidoPaterno,apellidoMaterno,
						nombre,rfc,curp,porcentaje,tipoInvolucrado,tipoPersona)
						VALUES(@idComprobante,'".cv($i->apPaterno)."','".cv($i->apMaterno)."','".cv($i->nombre)."','".cv($i->rfc)."','".cv($i->curp).
						"',".(($i->porcentaje=="")?0:$i->porcentaje).",1,".$i->tipoPersona.")";
		$x++;					
	}
	
	
	foreach($obj->datosComplemento->datosAdquiriente as $i)
	{
		$consulta[$x]="INSERT INTO 726_involucradosComplementoNotario(idComprobante,apellidoPaterno,apellidoMaterno,
						nombre,rfc,curp,porcentaje,tipoInvolucrado,tipoPersona)
						VALUES(@idComprobante,'".cv($i->apPaterno)."','".cv($i->apMaterno)."','".cv($i->nombre)."','".cv($i->rfc)."','".cv($i->curp).
						"',".(($i->porcentaje=="")?0:$i->porcentaje).",2,".$i->tipoPersona.")";
		$x++;					
	}
}
?>