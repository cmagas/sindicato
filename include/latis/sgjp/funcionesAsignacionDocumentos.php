<?php 
include_once("latis/conexionBD.php");
include_once("latis/utiles.php");


function asignacionDocumentoNotificacion($idFormulario,$idRegistro,$actor)
{
	global $con;
	$consulta="SELECT idFiguraJuridica FROM _72_tablaDinamica WHERE id__72_tablaDinamica='".$idRegistro."'";
	$res=$con->obtenerValor($consulta);
	switch($res)
	{
		case 1: //denunciante
				$idDocumento='22';
		break;
		case 2: //victima
				$idDocumento='80';
		break;
		case 3: //Asesor
				$idDocumento='80';
		break;
		case 4: //Imputado
				$idDocumento='22';
		break;
		case 5: //Defensor
				$idDocumento='80';
		break;
		case 6: //Representante
				$idDocumento='';
		break;
		case 7: // Testigo
				$idDocumento='22';
		break;
	}
	return $idDocumento;
}

function funcionLlenadoCitatorioImputadoVictima($idDocumento,$idReferencia,$idRegistro,$idFormulario)
{
	global $con;
	global $arrMesLetra;
	$arrValores=array();
	$diahoy=date("d");
	$mesHoy=$arrMesLetra[(date("m")*1)-1];
	$anioHoy=date("Y");
	
	$consulta="SELECT d.idReferencia,nombreNotificar,d.carpetaAdministrativa,d.idFiguraJuridica,g.idEvento,d.codigo FROM _72_tablaDinamica d,
				_67_tablaDinamica g WHERE d.idReferencia=g.id__67_tablaDinamica AND id__72_tablaDinamica='".$idRegistro."'";
	$res=$con->obtenerPrimeraFila($consulta);
	$carpetaA=$res[2];
	$nombreNotificar=$res[1];
	$idEvento=$res[4];
	$oficio=$res[5];
	
	switch($res[3])
	{
		case 1: //denunciante
				$idDocumento='1';
				$nombreImputado=strtoupper($nombreNotificar);
		break;
		case 2: //victima
				$idDocumento='2';
				$nombreVictima=strtoupper($nombreNotificar);
				
		break;
		case 3: //Asesor
				$idDocumento='2';
				$nombreVictima=strtoupper($nombreNotificar);
				
		break;
		case 4: //Imputado
				$idDocumento='1';
				$nombreImputado=strtoupper($nombreNotificar);
				
		break;
		case 5: //Defensor
				$idDocumento='2';
				$nombreVictima=strtoupper($nombreNotificar);
				
		break;
		case 6: //Representante
				$idDocumento='';
				$nombreRepresentante=strtoupper($nombreNotificar);
		break;
		case 7: // Testigo
				$idDocumento='1';
				$nombreImputado=strtoupper($nombreNotificar);
		break;
	}
	
	
	$datosAudiencia=obtenerDatosEventoAudiencia($idEvento);
	//varDump($datosAudiencia);
	$sala=$datosAudiencia->sala;
	$tipoAudiencia=$datosAudiencia->tipoAudiencia;
	//$fechaEvento=cambiarFormatoFecha($datosAudiencia->fechaEvento);
	$fechaEvento=$datosAudiencia->fechaEvento;
	
	$horaInicio=date("H:i",strtotime($datosAudiencia->horaInicio));
	$horafin=date("H:i",strtotime($datosAudiencia->horaFin));
	$edificio=$datosAudiencia->edificio;
	$unidadGestion=$datosAudiencia->unidadGestion;
	foreach($datosAudiencia->jueces as $juez=>$datosJ )
	{
		$nombreJuez=$datosJ->nombreJuez;
	}
	
	$delito=obtenerDatosDelito($carpetaA);
	$figuraJuridica=obtenerFigurasJuridicasCarpeta($carpetaA);
	//varDump($figuraJuridica);
	$nombreDenunciante="";
	$nombreVitimaF="";
	$nombreAsesorF="";
	$nombreImputadoF="";
	$nombreDefensorF="";
	$nombreRepresentanteF="";
	$nombreTestigoF="";
	foreach($figuraJuridica as $tipo=>$persona)
	{
		$idTipoFigura=$tipo;
		$nombreFigura=obtenerNombreFigura($idTipoFigura);
		foreach($persona as $figura)
		{
			if($idTipoFigura==1)
			{
				if($nombreDenunciante=="")
				{
					$nombreDenunciante=$figura["nombre"];
				}
				else
				{
					$nombreDenunciante.=$nombreDenunciante." ,".$figura["nombre"];
				}
			}
			if($idTipoFigura==2)
			{
				if($nombreVitimaF=="")
				{
					$nombreVitimaF=$figura["nombre"];
				}
				else
				{
					$nombreVitimaF.=$nombreVitimaF." ,".$figura["nombre"];
				}
			}
			if($idTipoFigura==3)
			{
				if($nombreAsesorF=="")
				{
					$nombreAsesorF=$figura["nombre"];
				}
				else
				{
					$nombreAsesorF.=$nombreAsesorF." ,".$figura["nombre"];
				}
			}
			if($idTipoFigura==4)
			{
				if($nombreImputadoF=="")
				{
					$nombreImputadoF=$figura["nombre"];
				}
				else
				{
					$nombreImputadoF.=$nombreImputadoF." ,".$figura["nombre"];
				}
			}
			if($idTipoFigura==5)
			{
				if($nombreDefensorF=="")
				{
					$nombreDefensorF=$figura["nombre"];
				}
				else
				{
					$nombreDefensorF.=$nombreDefensorF." ,".$figura["nombre"];
				}
			}
			if($idTipoFigura==6)
			{
				if($nombreRepresentanteF=="")
				{
					$nombreRepresentanteF=$figura["nombre"];
				}
				else
				{
					$nombreRepresentanteF.=$nombreRepresentanteF." ,".$figura["nombre"];
				}
			}
			if($idTipoFigura==7)
			{
				if($nombreTestigoF=="")
				{
					$nombreTestigoF=$figura["nombre"];
				}
				else
				{
					$nombreTestigoF.=$nombreTestigoF." ,".$figura["nombre"];
				}
			}
		}
	}
		
	if($idDocumento==1)//imputado
	{
		$arrValores["noCarpeta"]=$carpetaA;
		$arrValores["noOficio"]=$oficio;
		$arrValores["Imputado"]=strtoupper($nombreImputado);
		$arrValores["delito"]=$delito;
		$arrValores["victima"]=$nombreVitimaF;
		$arrValores["noSala"]=$sala;
		$arrValores["letraSala"]="";
		$arrValores["domicilioSala"]="";
		$arrValores["horaAudiencia"]=$horaInicio;
		$arrValores["diaAudiencia"]=date("d",strtotime($fechaEvento));
		$arrValores["mesAudiencia"]=$arrMesLetra[(date("m",strtotime($fechaEvento))*1)-1];
		$arrValores["anioAudiencia"]=date("Y",strtotime($fechaEvento));
		$arrValores["leyendaAnioAudiencia"]=convertirNumeroLetra($arrValores["anioAudiencia"],false,false);
		$arrValores["tipoAudiencia"]=$tipoAudiencia;
		$arrValores["noJuezAudiencia"]="";
		$arrValores["nombreJuez"]=$nombreJuez;
		$arrValores["noGestionJudicial"]=$unidadGestion;
		$arrValores["domicilioUMC"]="ubicada en calle Niños Héroes número 132, primer piso, colonia Doctores, delegación Cuauhtémoc";
		$arrValores["diaInicio"]=$diahoy;
		$arrValores["mesInicio"]=$mesHoy;
		$arrValores["anioInicio"]=$anioHoy;
		$arrValores["noJuezTramite"]="JUEZ PRIMERO";
		$arrValores["nombreJuezTramite"]="";
	}
	else //victima
	{
		$nombreImputado=obtenerFigurasJuridicasCarpeta($idEvento,'4');
		$arrValores["noCarpeta"]=$carpetaA;
		$arrValores["noOficio"]=$oficio;
		$arrValores["victima"]=$nombreVictima;
		$arrValores["imputado"]=$nombreImputadoF;
		$arrValores["delito"]=$delito;
		$arrValores["noSala"]=$sala;
		$arrValores["letraSala"]="";
		$arrValores["domicilioSala"]="";
		$arrValores["horaAudiencia"]=$horaInicio;
		$arrValores["diaAudiencia"]=date("d",strtotime($fechaEvento));
		$arrValores["mesAudiencia"]=$arrMesLetra[(date("m",strtotime($fechaEvento))*1)-1];
		$arrValores["anioAudiencia"]=date("Y",strtotime($fechaEvento));
		$arrValores["leyendaAnioAudiencia"]=convertirNumeroLetra($arrValores["anioAudiencia"],false,false);
		$arrValores["tipoAudiencia"]=$tipoAudiencia;
		$arrValores["noGestionJudicial"]=$unidadGestion;
		$arrValores["diaInicio"]=$diahoy;
		$arrValores["mesInicio"]=$mesHoy;
		$arrValores["anioInicio"]=$anioHoy;
		$arrValores["noJuezTramite"]="JUEZ PRIMERO";
		$arrValores["nombreJuezTramite"]="";
	}
	
	return $arrValores;
}

function obtenerDatosDelito($carpeta)
{
	global $con;
	$consulta="SELECT idActividad FROM _46_tablaDinamica WHERE carpetaAdministrativa='".$carpeta."'";
	$res=$con->obtenerValor($consulta);
	
	$consultaDelito="SELECT d.denominacionDelito FROM _61_tablaDinamica t,_35_denominacionDelito d 
					WHERE t.denominacionDelito=d.id__35_denominacionDelito AND idActividad='".$res."'";
	$delito=strtoupper($con->obtenerValor($consultaDelito));
	
	return $delito;
}

function obtenerFigurasJuridicasCarpeta($carpeta)
{
	global $con;	
	
	$consultaActivida="SELECT id__46_tablaDinamica,idActividad FROM _46_tablaDinamica WHERE carpetaAdministrativa='".$carpeta."'";
	$resActidad=$con->obtenerPrimeraFila($consultaActivida);
	
	$consultaNombre="SELECT id__47_tablaDinamica,apellidoPaterno,apellidoMaterno,nombre FROM _47_tablaDinamica WHERE idActividad='".$resActidad[1]."'";
	$res=$con->obtenerFilas($consultaNombre);
	$arrFiguras=array();
	while($fila=mysql_fetch_row($res))
	{
		$nombre=strtoupper($fila[3])." ".strtoupper($fila[1])." ".strtoupper($fila[2]);
		$consultaParticipaciones="SELECT idOpcion,t.nombreTipo FROM _47_chParticipacionJuridica f,_5_tablaDinamica t 
									WHERE f.idOpcion=t.id__5_tablaDinamica AND idPadre='".$fila[0]."' ORDER BY idOpcion";
		$resP=$con->obtenerFilas($consultaParticipaciones);
		while($row=mysql_fetch_row($resP))
		{
			if(!isset($arrFiguras[$row[0]]))
				$arrFiguras[$row[0]]=array();
				$obj["nombre"]=$nombre;
				array_push($arrFiguras[$row[0]],$obj);	
		}
	}
	return $arrFiguras;
}

function obtenerNombreFiguraL($idFigura)
{
	global $con;
	$nombre="";
	$consulta="SELECT nombreTipo FROM _5_tablaDinamica WHERE id__5_tablaDinamica='".$idFigura."'";
	$nombre=strtoupper($con->obtenerValor($consulta));
	return $nombre;
}
function funcionLlenadoDocumentoTraslado($idDocumento,$idReferencia,$idRegistro,$idFormulario)
{
	global $con;
	global $arrMesLetra;
	$arrValores=array();
	$diahoy=date("d");
	$mesHoy=$arrMesLetra[(date("m")*1)-1];
	$anioHoy=date("Y");
	$fechaHoy="a ".$diahoy." de ".$mesHoy." del ".$anioHoy;
	
	$consuDatos="SELECT iRegistro FROM _123_tablaDinamica WHERE id__123_tablaDinamica='".$idRegistro."'";
	$idRegistroA=$con->obtenerValor($consuDatos);

	$consulta="SELECT codigo,idEvento,carpetaAdministrativa,idUsuario,idReclusorio FROM _84_tablaDinamica WHERE id__84_tablaDinamica='".$idRegistroA."'";
	$res=$con->obtenerPrimeraFila($consulta);
	$folioSolicitud=$res[0];
	$idEvento=$res[1];
	$carpetaA=$res[2];
	$idImputado=$res[3];
	$idReclusorio=$res[4];

	$datosImplicados=obtenerFigurasJuridicasCarpeta($carpetaA);
	$nombreImputado=obtenerNombreImputadoID($idImputado);
	$delito=obtenerDatosDelito($carpetaA);
	$nombreReclusorio=obtenerNombreReclusorio($idReclusorio);	
	
	
	$datosAudiencia=obtenerDatosEventoAudiencia($idEvento);
	//varDump($datosAudiencia);
	
	$sala=$datosAudiencia->sala;
	$tipoAudiencia=$datosAudiencia->tipoAudiencia;
	$fechaEvento=$datosAudiencia->fechaEvento;
	
	$horaInicio=date("H:i",strtotime($datosAudiencia->horaInicio));
	$horafin=date("H:i",strtotime($datosAudiencia->horaFin));
	$edificio=$datosAudiencia->edificio;
	$unidadGestion=$datosAudiencia->unidadGestion;
	foreach($datosAudiencia->jueces as $juez=>$datosJ )
	{
		$idJuez=$datosJ->idJuez;
		$nombreJuez=$datosJ->nombreJuez;
	}
	
	$consultarNumJuez="SELECT clave FROM _26_tablaDinamica WHERE usuarioJuez='".$idJuez."'";
	$noJuez=$con->obtenerValor($consultarNumJuez);
	
	$arrValores["fechaHoy"]=$fechaHoy;
	
	$arrValores["diaInicio"]=$diahoy;
	$arrValores["mesInicio"]=$mesHoy;
	$arrValores["anioInicio"]=$anioHoy;
	$arrValores["noCarpetaAdministrativa"]=$carpetaA;
	$arrValores["noOficio"]=$folioSolicitud;
	$arrValores["imputado"]=$nombreImputado;
	$arrValores["delito"]=$delito;
	$arrValores["diaAudiencia"]=date("d",strtotime($fechaEvento));
	$arrValores["mesAudiencia"]=$arrMesLetra[(date("m",strtotime($fechaEvento))*1)-1];
	$arrValores["anioAudiencia"]=date("Y",strtotime($fechaEvento));
	$arrValores["noSala"]=$sala;
	$arrValores["horaAudiencia"]=$horaInicio;
	$arrValores["reclusorioPreventivo"]=$nombreReclusorio;
	$arrValores["nombreJuezTramite"]=$nombreJuez;
	$arrValores["noJuezTramite"]=$noJuez;
	
	return $arrValores;
}

function funcionLlenadoProyectoAcuerdoTraslado($idDocumento,$idReferencia,$idRegistro,$idFormulario)
{
	global $con;
	global $arrMesLetra;
	$arrValores=array();
	$diahoy=date("d");
	$mesHoy=$arrMesLetra[(date("m")*1)-1];
	$anioHoy=date("Y");
	
	$fecha=$diahoy." de ".$mesHoy." de ".$anioHoy;
	
	$consulta="SELECT codigo,idEvento,carpetaAdministrativa,cmbImputado,ubicacionImputado,cmbReclusorio,otroReclusorio 
				FROM _84_tablaDinamica WHERE id__84_tablaDinamica='".$idRegistro."'";
	$res=$con->obtenerPrimeraFila($consulta);
	$folioSolicitud=$res[0];
	$carpetaA=$res[2];
	
	$arrValores["fecha"]=$fecha;
	$arrValores["numCarpeta"]=$carpetaA;
	$arrValores["numOficio"]=$folioSolicitud;
	
	return $arrValores;
}

function llenadoAcuerdoControlValores($idDocumento,$idReferencia,$idRegistro,$idFormulario)
{
	global $con;
	global $arrMesLetra;
	$arrValores=array();
	$diahoy=date("d");
	$mesHoy=$arrMesLetra[(date("m")*1)-1];
	$anioHoy=date("Y");
	
	$fecha=$diahoy." de ".$mesHoy." de ".$anioHoy;
	
//	echo "documento ".$idDocumento." referencia ".$idReferencia." registro ".$idRegistro." formulario ".$idFormulario."<br>";
//	return;
	
	$consulta="";
	
	$arrValores["fecha"]=$fecha;
	//$arrValores["noCarpetaJudicial"]=$carpetaA;
	//$arrValores["noOficio"]=$folioSolicitud;
	
	return $arrValores;
}

function llenadoAcuerdoGestionPromocion($idDocumento,$idReferencia,$idRegistro,$idFormulario)
{
	global $con;
	global $arrMesLetra;
	$arrValores=array();
	$diahoy=date("d");
	$mesHoy=$arrMesLetra[(date("m")*1)-1];
	$anioHoy=date("Y");
	
	$fecha=$diahoy." de ".$mesHoy." de ".$anioHoy;
	
	$consuDatos="SELECT iRegistro FROM _123_tablaDinamica WHERE id__123_tablaDinamica='".$idRegistro."'";
	$idRegistroA=$con->obtenerValor($consuDatos);
	
	$consulta="SELECT codigo,tipoPromociones,numeroPromocion,tipoAudiencia,carpetaAdministrativa,fechaRecepcion,horaRecepcion,figurasJuridicas,
				imputado FROM _96_tablaDinamica WHERE id__96_tablaDinamica='".$idRegistroA."'";
	$res=$con->obtenerPrimeraFila($consulta);
	$carpetaA=$res[4];
	$folioSolicitud=$res[0];
	
	$arrValores["fechaHoy"]=$fecha;
	$arrValores["carpetaA"]=$carpetaA;
	$arrValores["folioOficio"]=$folioSolicitud;
	
	return $arrValores;
}

function asignacionDocumentoResultadoAudiencia($idFormulario,$idRegistro,$actor)
{
	global $con;
	$consulta="SELECT idFormatoImpresion FROM _101_tablaDinamica WHERE id__101_tablaDinamica='".$idRegistro."'";
	$idDocumento=$con->obtenerValor($consulta);
	return $idDocumento;
}

function LlenadoCitacionDefensorPublico($idDocumento,$idReferencia,$idRegistro,$idFormulario)
{
	global $con;
	global $arrMesLetra;
	$arrValores=array();
	$diahoy=date("d");
	$mesHoy=$arrMesLetra[(date("m")*1)-1];
	$anioHoy=date("Y");
	$fechaHoy="a ".$diahoy." de ".$mesHoy." del ".$anioHoy;

	$consulta="SELECT codigo,idEvento,carpetaAdministrativa FROM _80_tablaDinamica WHERE id__80_tablaDinamica='".$idRegistro."'";
	$res=$con->obtenerPrimeraFila($consulta);
	$folioSolicitud=$res[0];
	$idEvento=$res[1];
	$carpetaA=$res[2];
	$consulImpu="SELECT idOpcion FROM _80_impuados WHERE idPadre='".$idRegistro."'";
	$resI=$con->obtenerValor($consulImpu);
	
	$idImputado=$resI;
	$nombreImputado=obtenerNombreImputadoID($idImputado);
	$delito=obtenerDatosDelito($carpetaA);
	$datosAudiencia=obtenerDatosEventoAudiencia($idEvento);
	//varDump($datosAudiencia);
	
	$sala=$datosAudiencia->sala;
	$tipoAudiencia=$datosAudiencia->tipoAudiencia;
	$fechaEvento=$datosAudiencia->fechaEvento;
	
	$horaInicio=date("H:i",strtotime($datosAudiencia->horaInicio));
	$horafin=date("H:i",strtotime($datosAudiencia->horaFin));
	$edificio=$datosAudiencia->edificio;
	$unidadGestion=$datosAudiencia->unidadGestion;
	
	$figuraJuridica=obtenerFigurasJuridicasCarpeta($carpetaA);
	
	foreach($datosAudiencia->jueces as $juez=>$datosJ )
	{
		$nombreJuez=$datosJ->nombreJuez;
	}
	
	$nombreDenunciante="";
	$nombreVitimaF="";
	$nombreAsesorF="";
	$nombreImputadoF="";
	$nombreDefensorF="";
	$nombreRepresentanteF="";
	$nombreTestigoF="";
	foreach($figuraJuridica as $tipo=>$persona)
	{
		$idTipoFigura=$tipo;
		$nombreFigura=obtenerNombreFiguraL($idTipoFigura);
		foreach($persona as $figura)
		{
			if($idTipoFigura==1)
			{
				if($nombreDenunciante=="")
				{
					$nombreDenunciante=$figura["nombre"];
				}
				else
				{
					$nombreDenunciante.=$nombreDenunciante." ,".$figura["nombre"];
				}
			}
			if($idTipoFigura==2)
			{
				if($nombreVitimaF=="")
				{
					$nombreVitimaF=$figura["nombre"];
				}
				else
				{
					$nombreVitimaF.=$nombreVitimaF." ,".$figura["nombre"];
				}
			}
			if($idTipoFigura==3)
			{
				if($nombreAsesorF=="")
				{
					$nombreAsesorF=$figura["nombre"];
				}
				else
				{
					$nombreAsesorF.=$nombreAsesorF." ,".$figura["nombre"];
				}
			}
			if($idTipoFigura==4)
			{
				if($nombreImputadoF=="")
				{
					$nombreImputadoF=$figura["nombre"];
				}
				else
				{
					$nombreImputadoF.=$nombreImputadoF." ,".$figura["nombre"];
				}
			}
			if($idTipoFigura==5)
			{
				if($nombreDefensorF=="")
				{
					$nombreDefensorF=$figura["nombre"];
				}
				else
				{
					$nombreDefensorF.=$nombreDefensorF." ,".$figura["nombre"];
				}
			}
			if($idTipoFigura==6)
			{
				if($nombreRepresentanteF=="")
				{
					$nombreRepresentanteF=$figura["nombre"];
				}
				else
				{
					$nombreRepresentanteF.=$nombreRepresentanteF." ,".$figura["nombre"];
				}
			}
			if($idTipoFigura==7)
			{
				if($nombreTestigoF=="")
				{
					$nombreTestigoF=$figura["nombre"];
				}
				else
				{
					$nombreTestigoF.=$nombreTestigoF." ,".$figura["nombre"];
				}
			}
		}
	}	
	
	
	$arrValores["noCarpeta"]=$carpetaA;
	$arrValores["noOficio"]=$folioSolicitud;
	$arrValores["imputado"]=$nombreImputado;
	$arrValores["delito"]=$delito;
	$arrValores["victima"]=$nombreVitimaF;
	$arrValores["noSala"]=$sala;
	$arrValores["horaAudiencia"]=$horaInicio;
	$arrValores["diaAudiencia"]=date("d",strtotime($fechaEvento));
	$arrValores["mesAudiencia"]=date("m",strtotime($fechaEvento));
	$arrValores["anioAudiencia"]=date("Y",strtotime($fechaEvento));
	$arrValores["leyendaAnioAudiencia"]=convertirNumeroLetra($arrValores["anioAudiencia"],false,false);
	$arrValores["tipoAudiencia"]=$tipoAudiencia;
	$arrValores["noJuezAudiencia"]="";
	$arrValores["nombreJuezAudiencia"]=$nombreJuez;
	$arrValores["noGestionJudicial"]=$unidadGestion;
	$arrValores["fechaHoy"]=$fechaHoy;
	
	return $arrValores;
}

function obtenerNombreImputadoID($idUsuario)
{
	global $con;
	$consulta="SELECT apellidoPaterno,apellidoMaterno,nombre FROM _47_tablaDinamica WHERE id__47_tablaDinamica='".$idUsuario."'";
	$res=$con->obtenerPrimeraFila($consulta);
	$nombre=$res[2]." ".$res[0]." ".$res[1];
	return $nombre;
}

function llenadoOficioMedidaCautelar($idDocumento,$idReferencia,$idRegistro,$idFormulario)
{
	global $con;
	
	global $arrMesLetra;
	$arrValores=array();
	$diahoy=date("d");
	$mesHoy=$arrMesLetra[(date("m")*1)-1];
	$anioHoy=date("Y");
	$fechaHoy="a ".$diahoy." de ".$mesHoy." del ".$anioHoy;

	$consulta="SELECT idUsuario,idEvento,carpetaAdministrativa1,codigo FROM _111_tablaDinamica WHERE id__111_tablaDinamica='".$idRegistro."'";
	$res=$con->obtenerPrimeraFila($consulta);
	
	$idImputado=$res[0];
	$idEvento=$res[1];
	$carpetaA=$res[2];
	$folio=$res[3];
	
	$consulMedidas="SELECT medidaCautelar,t.tipoMedidaCautelar,vigencia, monto FROM _111_medidasCautelares m,_110_tablaDinamica t 
					WHERE m.medidaCautelar=t.id__110_tablaDinamica AND m.idReferencia='".$idRegistro."'";
	$resMedidas=$con->obtenerFilas($consulMedidas);
	$medida="";
	while($fila=mysql_fetch_row($resMedidas))
	{
		$tipoMedida=$fila[1];
		if($medida=="")
		{
			$medida=$tipoMedida;
		}
		else
		{
			$medida.="<br>".$tipoMedida;
		}
		
	}
	
	$nombreImputado=obtenerNombreImputadoID($idImputado);
	
	
	$delito=obtenerDatosDelito($carpetaA);
	$datosAudiencia=obtenerDatosEventoAudiencia($idEvento);
	//varDump($datosAudiencia);
	
	$sala=$datosAudiencia->sala;
	$tipoAudiencia=$datosAudiencia->tipoAudiencia;
	$fechaEvento=$datosAudiencia->fechaEvento;
	
	$horaInicio=date("H:i",strtotime($datosAudiencia->horaInicio));
	$horafin=date("H:i",strtotime($datosAudiencia->horaFin));
	$edificio=$datosAudiencia->edificio;
	$unidadGestion=$datosAudiencia->unidadGestion;
	foreach($datosAudiencia->jueces as $juez=>$datosJ )
	{
		$nombreJuez=$datosJ->nombreJuez;
	}
	
	$figuraJuridica=obtenerFigurasJuridicasCarpeta($carpetaA);
	$nombreDenunciante="";
	$nombreVitimaF="";
	$nombreAsesorF="";
	$nombreImputadoF="";
	$nombreDefensorF="";
	$nombreRepresentanteF="";
	$nombreTestigoF="";
	
	foreach($figuraJuridica as $tipo=>$persona)
	{
		$idTipoFigura=$tipo;
		$nombreFigura=obtenerNombreFiguraL($idTipoFigura);
		foreach($persona as $figura)
		{
			if($idTipoFigura==1)
			{
				if($nombreDenunciante=="")
				{
					$nombreDenunciante=$figura["nombre"];
				}
				else
				{
					$nombreDenunciante.=$nombreDenunciante." ,".$figura["nombre"];
				}
			}
			if($idTipoFigura==2)
			{
				if($nombreVitimaF=="")
				{
					$nombreVitimaF=$figura["nombre"];
				}
				else
				{
					$nombreVitimaF.=$nombreVitimaF." ,".$figura["nombre"];
				}
			}
			if($idTipoFigura==3)
			{
				if($nombreAsesorF=="")
				{
					$nombreAsesorF=$figura["nombre"];
				}
				else
				{
					$nombreAsesorF.=$nombreAsesorF." ,".$figura["nombre"];
				}
			}
			if($idTipoFigura==4)
			{
				if($nombreImputadoF=="")
				{
					$nombreImputadoF=$figura["nombre"];
				}
				else
				{
					$nombreImputadoF.=$nombreImputadoF." ,".$figura["nombre"];
				}
			}
			if($idTipoFigura==5)
			{
				if($nombreDefensorF=="")
				{
					$nombreDefensorF=$figura["nombre"];
				}
				else
				{
					$nombreDefensorF.=$nombreDefensorF." ,".$figura["nombre"];
				}
			}
			if($idTipoFigura==6)
			{
				if($nombreRepresentanteF=="")
				{
					$nombreRepresentanteF=$figura["nombre"];
				}
				else
				{
					$nombreRepresentanteF.=$nombreRepresentanteF." ,".$figura["nombre"];
				}
			}
			if($idTipoFigura==7)
			{
				if($nombreTestigoF=="")
				{
					$nombreTestigoF=$figura["nombre"];
				}
				else
				{
					$nombreTestigoF.=$nombreTestigoF." ,".$figura["nombre"];
				}
			}
		}
	}	
	
	
	$arrValores["fechaHoy"]=$fechaHoy;
	$arrValores["noCarpeta"]=$carpetaA;
	$arrValores["noOficio"]=$folio;
	$arrValores["imputado"]=$nombreImputado;
	$arrValores["delito"]=$delito;
	//$arrValores["noJuezAudiencia"]=$delito;
	$arrValores["nombreJuezAudiencia"]=$nombreJuez;
	$arrValores["victima"]=$nombreVitimaF;
	$arrValores["tipoMedidaCautelar"]=$medida;
	
	return $arrValores;
}

function llenadoSolicitudRevisionMedica($idDocumento,$idReferencia,$idRegistro,$idFormulario)
{
	global $con;
	global $arrMesLetra;
	$arrValores=array();
	$diahoy=date("d");
	$mesHoy=$arrMesLetra[(date("m")*1)-1];
	$anioHoy=date("Y");
	$fechaHoy="a ".$diahoy." de ".$mesHoy." del ".$anioHoy;
	
	$consulta="SELECT idEvento,carpetaAdministrativa,lugarTraslado,idUsuario,codigo FROM _113_tablaDinamica WHERE id__113_tablaDinamica='".$idRegistro."'";
	$res=$con->obtenerPrimeraFila($consulta);
	
	$folio=$res[4];
	$idEVento=$res[0];
	$carpetaA=$res[1];
	$lugarTraslado=$res[2];
	$idUsuario=$res[3];
	
	$nombreImputado=obtenerNombreImputadoID($idUsuario);
	
	$arrValores["fechaHoy"]=$fechaHoy;
	$arrValores["noCarpeta"]=$carpetaA;
	$arrValores["noOficio"]=$folio;
	$arrValores["imputado"]=$nombreImputado;
	$arrValores["lugarTraslado"]=$lugarTraslado;
	
	//$arrValores["noJuezAudiencia"]=$delito;
	//$arrValores["nombreJuezAudiencia"]=$nombreJuez;
	//$arrValores["victima"]=$nombreVitimaF;
	//$arrValores["tipoMedidaCautelar"]=$medida;
	
	return $arrValores;
}

function llenadoSolicitudPoliciaProcesal($idDocumento,$idReferencia,$idRegistro,$idFormulario)
{
	global $con;
	global $arrMesLetra;
	$arrValores=array();
	$diahoy=date("d");
	$mesHoy=$arrMesLetra[(date("m")*1)-1];
	$anioHoy=date("Y");
	$fechaHoy="a ".$diahoy." de ".$mesHoy." del ".$anioHoy;
	$idReclusorioDestino=-1;
	if($idFormulario==115)
	{
		$consulta="SELECT idEvento,carpetaAdministrativa,idUsuario,codigo FROM _115_tablaDinamica WHERE id__115_tablaDinamica='".$idRegistro."'";
		$res=$con->obtenerPrimeraFila($consulta);
		
		$folio=$res[3];
		$idEvento=$res[0];
		$carpetaA=$res[1];
		$idUsuario=$res[2];
	}
	else
	{
		$consulta="SELECT idEvento,carpetaAdministrativa,cmbImputado,codigo,reclusorioDestino FROM _84_tablaDinamica 
					WHERE id__84_tablaDinamica='".$idRegistro."'";
		$res=$con->obtenerPrimeraFila($consulta);
		
		$folio=$res[3];
		$idEvento=$res[0];
		$carpetaA=$res[1];
		$idUsuario=$res[2];
		$idReclusorioDestino=$res[4];
		
	}
	
	$nombreReclusorio=obtenerNombreReclusorio($idReclusorioDestino);
	$nombreImputado=obtenerNombreImputadoID($idUsuario);
	$delito=obtenerDatosDelito($carpetaA);
	$datosAudiencia=obtenerDatosEventoAudiencia($idEvento);
	//varDump($datosAudiencia);
	
	$sala=$datosAudiencia->sala;
	$tipoAudiencia=$datosAudiencia->tipoAudiencia;
	$fechaEvento=$datosAudiencia->fechaEvento;
	
	$horaInicio=date("H:i",strtotime($datosAudiencia->horaInicio));
	$horaI=date("H",strtotime($datosAudiencia->horaInicio));
	$minutoI=date("i",strtotime($datosAudiencia->horaInicio));
	
	$horafin=date("H:i",strtotime($datosAudiencia->horaFin));
	$edificio=$datosAudiencia->edificio;
	$unidadGestion=$datosAudiencia->unidadGestion;
	foreach($datosAudiencia->jueces as $juez=>$datosJ )
	{
		$idJuez=$datosJ->idJuez;
		$codigoJuez="SELECT clave FROM _26_tablaDinamica WHERE usuarioJuez='".$idJuez."'";
		$claveJuez=$con->obtenerValor($codigoJuez);
		$nombreJuez=$datosJ->nombreJuez;
	}
	
	
	$arrValores["fechaHoy"]=$fechaHoy;
	$arrValores["noCarpeta"]=$carpetaA;
	$arrValores["noOficio"]=$folio;
	$arrValores["imputado"]=$nombreImputado;
	$arrValores["delito"]=$delito;
	$arrValores["noJuez"]=$claveJuez;
	$arrValores["nombreJuez"]=$nombreJuez;
	$arrValores["noSala"]=$sala;

	$arrValores["horaAudiencia"]=$horaI;
	$arrValores["minutosAudiencia"]=$minutoI;
	$arrValores["reclusorioPreventivo"]=$nombreReclusorio;
	
	//$arrValores["tipoMedidaCautelar"]=$medida;
	
	return $arrValores;
}

function obtenerNombreReclusorio($idReclusorio)
{
	global $con;
	$consulta="SELECT nombre FROM _2_tablaDinamica WHERE id__2_tablaDinamica='".$idReclusorio."'";
	$res=$con->obtenerValor($consulta);
	return $res;
}

function cambiarEtapaPromocion($idRegistro,$idFormulario)
{

	global $con;
	global $servidorPruebas;
	/*$consulta="SELECT tipoPromociones,carpetaAdministrativaReferida,relacionPromocion,carpetaAdministrativa,fechaRecepcion,horaRecepcion FROM _96_tablaDinamica WHERE id__96_tablaDinamica='".$idRegistro."'";
	$fPromocion=$con->obtenerPrimeraFila($consulta);

	switch($fPromocion[0])
	{
		case 1: //Promocion de Trámite
				$etapa='2';
		break;
		case 2: //Promoción de solicitud de programación de audiencia
				$etapa='3';
		break;
		
	}*/
	$consulta="SELECT tipoPromociones,carpetaAdministrativaReferida,relacionPromocion,carpetaAdministrativa,fechaRecepcion,
	horaRecepcion FROM _96_tablaDinamica WHERE id__96_tablaDinamica='".$idRegistro."'";
	$fPromocion=$con->obtenerPrimeraFila($consulta);

	$actualizar="UPDATE _96_tablaDinamica SET fechaHoraRecepcionPromocion='".$fPromocion[4]." ".$fPromocion[5].
				"' WHERE id__96_tablaDinamica='".$idRegistro."'";

	$con->ejecutarConsulta($actualizar);
	
	
	/*if($servidorPruebas)
		$etapa="2.1";
	cambiarEtapaFormulario($idFormulario,$idRegistro,$etapa,"",-1,"NULL","NULL",293);*/
}

function insertarDocumentoExhorto($idRegistro)//recepcion exhorto
{
	global $con;
	$fechaA=date("Y-m-d");
	
	$borrar="DELETE FROM _123_tablaDinamica WHERE iFormulario='92' AND iRegistro='".$idRegistro."'";
	$con->ejecutarConsulta($borrar);
	
	$consulDatos="SELECT fechaCreacion,responsable,codigoInstitucion FROM _92_tablaDinamica WHERE id__92_tablaDinamica='".$idRegistro."'";
	$res=$con->obtenerPrimeraFila($consulDatos);
	$fechaCreacion=$res[0];
	$idResponsable=$res[1];
	$idEstado=1;
	$codigoUnidad=$res[2];
	
	$x=0;
	$consulta[$x]="begin";
	$x++;
	
	$consulta[$x]="INSERT INTO _123_tablaDinamica(fechaCreacion,responsable,idEstado,codigoInstitucion,idPersona,tipoFigura,tipoDocumento,
				iFormulario,iRegistro) VALUE('".$fechaA."','".$idResponsable."','".$idEstado."','".$codigoUnidad."','0','0','143','92','".$idRegistro."')";
	$x++;
	$consulta[$x]="INSERT INTO _123_tablaDinamica(fechaCreacion,responsable,idEstado,codigoInstitucion,idPersona,tipoFigura,tipoDocumento,
				iFormulario,iRegistro) VALUE('".$fechaA."','".$idResponsable."','".$idEstado."','".$codigoUnidad."','0','0','144','92','".$idRegistro."')";
	$x++;
	$consulta[$x]="commit";
	$con->ejecutarBloque($consulta);
}

function llenarFormatoEmisionExhorto($idDocumento,$idReferencia,$idRegistro,$idFormulario)
{
	global $con;
	global $arrMesLetra;
	$arrValores=array();
	$diahoy=date("d");
	$mesHoy=$arrMesLetra[(date("m")*1)-1];
	$anioHoy=date("Y");
	$fechaHoy="a ".$diahoy." de ".$mesHoy." del ".$anioHoy;
	
	$consuDatos="SELECT iRegistro FROM _123_tablaDinamica WHERE id__123_tablaDinamica='".$idRegistro."'";
	$idRegistroA=$con->obtenerValor($consuDatos);
	
	
	$consulta="SELECT codigo,carpetaAdministrativa,idUsuario,idEvento FROM _191_tablaDinamica WHERE id__191_tablaDinamica='".$idRegistroA."'";
	$res=$con->obtenerPrimeraFila($consulta);
	
	$folio=$res[0];
	$carpetaA=$res[1];
	$idImputado=$res[2];
	$idEvento=$res[3];
	
	
	$nombreImputado=obtenerNombreImputadoID($idImputado);
	$delito=obtenerDatosDelito($carpetaA);

	$datosAudiencia=obtenerDatosEventoAudiencia($idEvento);
	//varDump($datosAudiencia);
	
	$sala=$datosAudiencia->sala;
	$tipoAudiencia=$datosAudiencia->tipoAudiencia;
	$fechaEvento=$datosAudiencia->fechaEvento;
	
	$horaInicio=date("H:i",strtotime($datosAudiencia->horaInicio));
	$horaI=date("H",strtotime($datosAudiencia->horaInicio));
	$minutoI=date("i",strtotime($datosAudiencia->horaInicio));
	
	$horafin=date("H:i",strtotime($datosAudiencia->horaFin));
	$edificio=$datosAudiencia->edificio;
	$unidadGestion=$datosAudiencia->unidadGestion;
	foreach($datosAudiencia->jueces as $juez=>$datosJ )
	{
		$idJuez=$datosJ->idJuez;
		$codigoJuez="SELECT clave FROM _26_tablaDinamica WHERE usuarioJuez='".$idJuez."'";
		$claveJuez=$con->obtenerValor($codigoJuez);
		$nombreJuez=$datosJ->nombreJuez;
	}
	
	$arrValores["carpeta"]=$carpetaA;
//	$arrValores["delito"]=$delito;
//	$arrValores["imputado"]=$nombreImputado;
//	$arrValores["victima"]=$nombreImputado;
	
	return $arrValores;
}

function llenarFormatoRecepcionExhorto($idDocumento,$idReferencia,$idRegistro,$idFormulario)
{
	global $con;
	global $arrMesLetra;
	$arrValores=array();
	$diahoy=date("d");
	$mesHoy=$arrMesLetra[(date("m")*1)-1];
	$anioHoy=date("Y");
	$fechaHoy="a ".$diahoy." de ".$mesHoy." del ".$anioHoy;
	
	$consuDatos="SELECT iRegistro FROM _123_tablaDinamica WHERE id__123_tablaDinamica='".$idRegistro."'";
	$idRegistroA=$con->obtenerValor($consuDatos);
	
	
	//$consulta="SELECT codigo,carpetaAdministrativa,idUsuario,idEvento FROM _92_tablaDinamica WHERE id__92_tablaDinamica='".$idRegistroA."'";
//	//echo $consulta."<br>";
//	$res=$con->obtenerPrimeraFila($consulta);
//	
//	$folio=$res[0];
//	$carpetaA=$res[1];
//	$idImputado=$res[2];
//	$idEvento=$res[3];
//	
//	
//	$nombreImputado=obtenerNombreImputadoID($idImputado);
//	$delito=obtenerDatosDelito($carpetaA);
//
//	$datosAudiencia=obtenerDatosEventoAudiencia($idEvento);
//	//varDump($datosAudiencia);
//	
//	$sala=$datosAudiencia->sala;
//	$tipoAudiencia=$datosAudiencia->tipoAudiencia;
//	$fechaEvento=$datosAudiencia->fechaEvento;
//	
//	$horaInicio=date("H:i",strtotime($datosAudiencia->horaInicio));
//	$horaI=date("H",strtotime($datosAudiencia->horaInicio));
//	$minutoI=date("i",strtotime($datosAudiencia->horaInicio));
//	
//	$horafin=date("H:i",strtotime($datosAudiencia->horaFin));
//	$edificio=$datosAudiencia->edificio;
//	$unidadGestion=$datosAudiencia->unidadGestion;
//	foreach($datosAudiencia->jueces as $juez=>$datosJ )
//	{
//		$idJuez=$datosJ->idJuez;
//		$codigoJuez="SELECT clave FROM _26_tablaDinamica WHERE usuarioJuez='".$idJuez."'";
//		$claveJuez=$con->obtenerValor($codigoJuez);
//		$nombreJuez=$datosJ->nombreJuez;
//	}
//	
//	$arrValores["carpeta"]=$carpetaA;
//	$arrValores["delito"]=$delito;
//	$arrValores["imputado"]=$nombreImputado;
//	$arrValores["victima"]=$nombreImputado;
	
	//return $arrValores;
}

function llenadoFormatoEjecucionSentencia($idDocumento,$idReferencia,$idRegistro,$idFormulario)
{
	global $con;
	global $arrMesLetra;
	$arrValores=array();
	$diahoy=date("d");
	$mesHoy=$arrMesLetra[(date("m")*1)-1];
	$anioHoy=date("Y");
	$fechaHoy="a ".$diahoy." de ".$mesHoy." del ".$anioHoy;
	
	$consuDatos="SELECT iRegistro FROM _123_tablaDinamica WHERE id__123_tablaDinamica='".$idRegistro."'";
	$idRegistroA=$con->obtenerValor($consuDatos);
	
	$consulta="SELECT codigo,carpetaAdministrativa,cmbImputados,idEvento FROM _197_tablaDinamica WHERE id__197_tablaDinamica='".$idRegistroA."'";
	$res=$con->obtenerPrimeraFila($consulta);
	
	$folio=$res[0];
	$carpetaA=$res[1];
	$idImputado=$res[2];
	$idEvento=$res[3];
	
	$nombreImputado=obtenerNombreImputadoID($idImputado);
	$delito=obtenerDatosDelito($carpetaA);
	
	$figuraJuridica=obtenerFigurasJuridicasCarpeta($carpetaA);	
	
	$datosAudiencia=obtenerDatosEventoAudiencia($idEvento);
	//varDump($datosAudiencia);
	
	$sala=$datosAudiencia->sala;
	$tipoAudiencia=$datosAudiencia->tipoAudiencia;
	$fechaEvento=$datosAudiencia->fechaEvento;
	
	$horaInicio=date("H:i",strtotime($datosAudiencia->horaInicio));
	$horaI=date("H",strtotime($datosAudiencia->horaInicio));
	$minutoI=date("i",strtotime($datosAudiencia->horaInicio));
	
	$horafin=date("H:i",strtotime($datosAudiencia->horaFin));
	$edificio=$datosAudiencia->edificio;
	$unidadGestion=$datosAudiencia->unidadGestion;
	foreach($datosAudiencia->jueces as $juez=>$datosJ )
	{
		$idJuez=$datosJ->idJuez;
		$codigoJuez="SELECT clave FROM _26_tablaDinamica WHERE usuarioJuez='".$idJuez."'";
		$claveJuez=$con->obtenerValor($codigoJuez);
		$nombreJuez=$datosJ->nombreJuez;
	}
	
	$nombreDenunciante="";
	$nombreVitimaF="";
	$nombreAsesorF="";
	$nombreImputadoF="";
	$nombreDefensorF="";
	$nombreRepresentanteF="";
	$nombreTestigoF="";
	foreach($figuraJuridica as $tipo=>$persona)
	{
		$idTipoFigura=$tipo;
		$nombreFigura=obtenerNombreFiguraL($idTipoFigura);
		foreach($persona as $figura)
		{
			if($idTipoFigura==1)
			{
				if($nombreDenunciante=="")
				{
					$nombreDenunciante=$figura["nombre"];
				}
				else
				{
					$nombreDenunciante.=$nombreDenunciante." ,".$figura["nombre"];
				}
			}
			if($idTipoFigura==2)
			{
				if($nombreVitimaF=="")
				{
					$nombreVitimaF=$figura["nombre"];
				}
				else
				{
					$nombreVitimaF.=$nombreVitimaF." ,".$figura["nombre"];
				}
			}
			if($idTipoFigura==3)
			{
				if($nombreAsesorF=="")
				{
					$nombreAsesorF=$figura["nombre"];
				}
				else
				{
					$nombreAsesorF.=$nombreAsesorF." ,".$figura["nombre"];
				}
			}
			if($idTipoFigura==4)
			{
				if($nombreImputadoF=="")
				{
					$nombreImputadoF=$figura["nombre"];
				}
				else
				{
					$nombreImputadoF.=$nombreImputadoF." ,".$figura["nombre"];
				}
			}
			if($idTipoFigura==5)
			{
				if($nombreDefensorF=="")
				{
					$nombreDefensorF=$figura["nombre"];
				}
				else
				{
					$nombreDefensorF.=$nombreDefensorF." ,".$figura["nombre"];
				}
			}
			if($idTipoFigura==6)
			{
				if($nombreRepresentanteF=="")
				{
					$nombreRepresentanteF=$figura["nombre"];
				}
				else
				{
					$nombreRepresentanteF.=$nombreRepresentanteF." ,".$figura["nombre"];
				}
			}
			if($idTipoFigura==7)
			{
				if($nombreTestigoF=="")
				{
					$nombreTestigoF=$figura["nombre"];
				}
				else
				{
					$nombreTestigoF.=$nombreTestigoF." ,".$figura["nombre"];
				}
			}
		}
	}	
	
	$arrValores["fechaHoy"]=$fechaHoy;
	$arrValores["carpeta"]=$carpetaA;
	$arrValores["delito"]=$delito;
	$arrValores["imputado"]=$nombreImputado;
	$arrValores["noOficio"]=$folio;
	$arrValores["victima"]=$nombreVitimaF;
	$arrValores["noJuezControl"]=$claveJuez;
	$arrValores["nombreJuezControl"]=$nombreJuez;
	$arrValores["noGestionJudicial"]=$unidadGestion;
	$arrValores["diaAudiencia"]=date("d",strtotime($fechaEvento));
	$arrValores["mesAudiencia"]=$arrMesLetra[(date("m",strtotime($fechaEvento))*1)-1];
	$arrValores["anioAudiencia"]=date("Y",strtotime($fechaEvento));
	
	return $arrValores;
}

function cambiarEtapaProcesoDepositoJudicialFianza($idRegistro,$idFormulario)
{
	global $con;
	$consulta="SELECT fianzaVigente FROM _212_tablaDinamica WHERE idReferencia='".$idRegistro."'"; 
	$res=$con->obtenerValor($consulta);
	switch($res)
	{
		case 0://No esta vigente
				$etapa=12;
		break;
		case 1://si esta vigente
				$etapa=14;
		break;
	}
	cambiarEtapaFormulario($idFormulario,$idRegistro,$etapa);
}

function cambiarEtapaProcesoDepositoJudicial($idRegistro,$idFormulario)
{
	global $con;
	$consulta="SELECT tipoGarantia FROM _121_tablaDinamica WHERE idReferencia='".$idRegistro."'"; 
	$res=$con->obtenerValor($consulta);
	switch($res)
	{
		case 1://Billete
				$etapa=3;
				insertarDocumentoBilletes120($idRegistro);
		break;
		case 2://Fianzas
				$etapa=9;
		break;
	}

	cambiarEtapaFormulario($idFormulario,$idRegistro,$etapa);
}

function llenadoFormatoAcuerdoValores201($idDocumento,$idReferencia,$idRegistro,$idFormulario)
{
	global $con;
	global $con;
	global $arrMesLetra;
	$arrValores=array();
	$diahoy=date("d");
	$mesHoy=$arrMesLetra[(date("m")*1)-1];
	$anioHoy=date("Y");
	$fechaHoy="a ".$diahoy." de ".$mesHoy." del ".$anioHoy;

	$consulta="SELECT codigo,carpetaAdministrativa,idUsuario,idEvento FROM _120_tablaDinamica WHERE id__120_tablaDinamica='".$idRegistro."'";	
	$res=$con->obtenerPrimeraFila($consulta);
	
	$folio=$res[0];
	$carpetaA=$res[1];
	$idImputado=$res[2];
	$idEvento=$res[3];


	$arrValores["fechaHoy"]=$fechaHoy;
	$arrValores["carpeta"]=$carpetaA;
	$arrValores["noOficio"]=$folio;

	return $arrValores;
}

function insertarDocumentoIncompetencia($idRegistro)
{
	global $con;
	global $con;
	$fechaA=date("Y-m-d");
	
	$borrar="DELETE FROM _123_tablaDinamica WHERE iFormulario='163' AND iRegistro='".$idRegistro."'";
	$con->ejecutarConsulta($borrar);

	$consulDatos="SELECT fechaCreacion,responsable,codigoInstitucion FROM _163_tablaDinamica WHERE id__163_tablaDinamica='".$idRegistro."'";
	$res=$con->obtenerPrimeraFila($consulDatos);
	$fechaCreacion=$res[0];
	$idResponsable=$res[1];
	$idEstado=1;
	$codigoUnidad=$res[2];
	
	$x=0;
	$consulta[$x]="begin";
	$x++;
	
	$consulta[$x]="INSERT INTO _123_tablaDinamica(fechaCreacion,responsable,idEstado,codigoInstitucion,idPersona,tipoFigura,tipoDocumento,
				iFormulario,iRegistro) VALUE('".$fechaA."','".$idResponsable."','".$idEstado."','".$codigoUnidad."','0','0','40','163','".$idRegistro."')";
	$x++;
	$consulta[$x]="INSERT INTO _123_tablaDinamica(fechaCreacion,responsable,idEstado,codigoInstitucion,idPersona,tipoFigura,tipoDocumento,
				iFormulario,iRegistro) VALUE('".$fechaA."','".$idResponsable."','".$idEstado."','".$codigoUnidad."','0','0','42','163','".$idRegistro."')";
	$x++;
	$consulta[$x]="commit";
	$con->ejecutarBloque($consulta);
}

function llenarFormatoConstanciaIncompetencia163($idDocumento,$idReferencia,$idRegistro,$idFormulario)
{
	global $con;
	global $arrMesLetra;
	$arrValores=array();
	$diahoy=date("d");
	$mesHoy=$arrMesLetra[(date("m")*1)-1];
	$anioHoy=date("Y");
	$fechaHoy="a ".$diahoy." de ".$mesHoy." del ".$anioHoy;

	$consuDatos="SELECT iRegistro FROM _123_tablaDinamica WHERE id__123_tablaDinamica='".$idRegistro."'";
	$idRegistroA=$con->obtenerValor($consuDatos);
	
	$consulta="SELECT codigoInstitucion,codigo,jueces,carpetaAdministrativa,idEvento FROM _163_tablaDinamica WHERE id__163_tablaDinamica='".$idRegistroA."'";
	$res=$con->obtenerPrimeraFila($consulta);
	
	$folio=$res[1];
	$carpetaA=$res[3];
//	$idImputado=$res[2];
//	$idEvento=$res[3];
	$delito=obtenerDatosDelito($carpetaA);
	
	$figuraJuridica=obtenerFigurasJuridicasCarpeta($carpetaA);
	
		

	$nombreDenunciante="";
	$nombreVitimaF="";
	$nombreAsesorF="";
	$nombreImputadoF="";
	$nombreDefensorF="";
	$nombreRepresentanteF="";
	$nombreTestigoF="";
	foreach($figuraJuridica as $tipo=>$persona)
	{
		$idTipoFigura=$tipo;
		$nombreFigura=obtenerNombreFiguraL($idTipoFigura);
		foreach($persona as $figura)
		{
			if($idTipoFigura==1)
			{
				if($nombreDenunciante=="")
				{
					$nombreDenunciante=$figura["nombre"];
				}
				else
				{
					$nombreDenunciante.=$nombreDenunciante." ,".$figura["nombre"];
				}
			}
			if($idTipoFigura==2)
			{
				if($nombreVitimaF=="")
				{
					$nombreVitimaF=$figura["nombre"];
				}
				else
				{
					$nombreVitimaF.=$nombreVitimaF." ,".$figura["nombre"];
				}
			}
			if($idTipoFigura==3)
			{
				if($nombreAsesorF=="")
				{
					$nombreAsesorF=$figura["nombre"];
				}
				else
				{
					$nombreAsesorF.=$nombreAsesorF." ,".$figura["nombre"];
				}
			}
			if($idTipoFigura==4)
			{
				if($nombreImputadoF=="")
				{
					$nombreImputadoF=$figura["nombre"];
				}
				else
				{
					$nombreImputadoF.=$nombreImputadoF." ,".$figura["nombre"];
				}
			}
			if($idTipoFigura==5)
			{
				if($nombreDefensorF=="")
				{
					$nombreDefensorF=$figura["nombre"];
				}
				else
				{
					$nombreDefensorF.=$nombreDefensorF." ,".$figura["nombre"];
				}
			}
			if($idTipoFigura==6)
			{
				if($nombreRepresentanteF=="")
				{
					$nombreRepresentanteF=$figura["nombre"];
				}
				else
				{
					$nombreRepresentanteF.=$nombreRepresentanteF." ,".$figura["nombre"];
				}
			}
			if($idTipoFigura==7)
			{
				if($nombreTestigoF=="")
				{
					$nombreTestigoF=$figura["nombre"];
				}
				else
				{
					$nombreTestigoF.=$nombreTestigoF." ,".$figura["nombre"];
				}
			}
		}
	}

	$arrValores["fechaHoy"]=$fechaHoy;
	$arrValores["carpeta"]=$carpetaA;
	$arrValores["noOficio"]=$folio;
	$arrValores["delito"]=$delito;
	$arrValores["imputado"]=$nombreImputadoF;
	$arrValores["victima"]=$nombreVitimaF;

	return $arrValores;	
}

function llenarFormatoAutoIncompetencia163($idDocumento,$idReferencia,$idRegistro,$idFormulario)
{
	global $con;
	global $arrMesLetra;
	$arrValores=array();
	$diahoy=date("d");
	$mesHoy=$arrMesLetra[(date("m")*1)-1];
	$anioHoy=date("Y");
	$fechaHoy="a ".$diahoy." de ".$mesHoy." del ".$anioHoy;

	$consuDatos="SELECT iRegistro FROM _123_tablaDinamica WHERE id__123_tablaDinamica='".$idRegistro."'";
	$idRegistroA=$con->obtenerValor($consuDatos);
	
	$consulta="SELECT codigoInstitucion,codigo,jueces,carpetaAdministrativa,idEvento FROM _163_tablaDinamica WHERE id__163_tablaDinamica='".$idRegistroA."'";
	$res=$con->obtenerPrimeraFila($consulta);
	
	$folio=$res[1];
	$carpetaA=$res[3];
//	$idImputado=$res[2];
	$idEvento=$res[4];
	$delito=obtenerDatosDelito($carpetaA);
	
	$figuraJuridica=obtenerFigurasJuridicasCarpeta($carpetaA);	
	$datosAudiencia=obtenerDatosEventoAudiencia($idEvento);
	//varDump($datosAudiencia);
	
	$sala=$datosAudiencia->sala;
	$tipoAudiencia=$datosAudiencia->tipoAudiencia;
	$fechaEvento=$datosAudiencia->fechaEvento;
	
	$horaInicio=date("H:i",strtotime($datosAudiencia->horaInicio));
	$horaI=date("H",strtotime($datosAudiencia->horaInicio));
	$minutoI=date("i",strtotime($datosAudiencia->horaInicio));
	
	$horafin=date("H:i",strtotime($datosAudiencia->horaFin));
	$edificio=$datosAudiencia->edificio;
	$unidadGestion=$datosAudiencia->unidadGestion;
	foreach($datosAudiencia->jueces as $juez=>$datosJ )
	{
		$idJuez=$datosJ->idJuez;
		$codigoJuez="SELECT clave FROM _26_tablaDinamica WHERE usuarioJuez='".$idJuez."'";
		$claveJuez=$con->obtenerValor($codigoJuez);
		$nombreJuez=$datosJ->nombreJuez;
	}	


	$nombreDenunciante="";
	$nombreVitimaF="";
	$nombreAsesorF="";
	$nombreImputadoF="";
	$nombreDefensorF="";
	$nombreRepresentanteF="";
	$nombreTestigoF="";
	foreach($figuraJuridica as $tipo=>$persona)
	{
		$idTipoFigura=$tipo;
		$nombreFigura=obtenerNombreFiguraL($idTipoFigura);
		foreach($persona as $figura)
		{
			if($idTipoFigura==1)
			{
				if($nombreDenunciante=="")
				{
					$nombreDenunciante=$figura["nombre"];
				}
				else
				{
					$nombreDenunciante.=$nombreDenunciante." ,".$figura["nombre"];
				}
			}
			if($idTipoFigura==2)
			{
				if($nombreVitimaF=="")
				{
					$nombreVitimaF=$figura["nombre"];
				}
				else
				{
					$nombreVitimaF.=$nombreVitimaF." ,".$figura["nombre"];
				}
			}
			if($idTipoFigura==3)
			{
				if($nombreAsesorF=="")
				{
					$nombreAsesorF=$figura["nombre"];
				}
				else
				{
					$nombreAsesorF.=$nombreAsesorF." ,".$figura["nombre"];
				}
			}
			if($idTipoFigura==4)
			{
				if($nombreImputadoF=="")
				{
					$nombreImputadoF=$figura["nombre"];
				}
				else
				{
					$nombreImputadoF.=$nombreImputadoF." ,".$figura["nombre"];
				}
			}
			if($idTipoFigura==5)
			{
				if($nombreDefensorF=="")
				{
					$nombreDefensorF=$figura["nombre"];
				}
				else
				{
					$nombreDefensorF.=$nombreDefensorF." ,".$figura["nombre"];
				}
			}
			if($idTipoFigura==6)
			{
				if($nombreRepresentanteF=="")
				{
					$nombreRepresentanteF=$figura["nombre"];
				}
				else
				{
					$nombreRepresentanteF.=$nombreRepresentanteF." ,".$figura["nombre"];
				}
			}
			if($idTipoFigura==7)
			{
				if($nombreTestigoF=="")
				{
					$nombreTestigoF=$figura["nombre"];
				}
				else
				{
					$nombreTestigoF.=$nombreTestigoF." ,".$figura["nombre"];
				}
			}
		}
	}

	$arrValores["fechaHoy"]=$fechaHoy;
	$arrValores["carpeta"]=$carpetaA;
	$arrValores["noOficio"]=$folio;
	$arrValores["delito"]=$delito;
	$arrValores["imputado"]=$nombreImputadoF;
	$arrValores["victima"]=$nombreVitimaF;
	$arrValores["noGestionJudicial"]=$unidadGestion;
	$arrValores["diaAudiencia"]=date("d",strtotime($fechaEvento));
	$arrValores["mesAudiencia"]=$arrMesLetra[(date("m",strtotime($fechaEvento))*1)-1];
	$arrValores["anioAudiencia"]=date("Y",strtotime($fechaEvento));
	$arrValores["noSala"]=$sala;
	$arrValores["horaAudiencia"]=$horaInicio;
	$arrValores["noJuezAudiencia"]=$claveJuez;
	$arrValores["nombreJuezAudiencia"]=$nombreJuez;

	return $arrValores;	
}

function insertarDocumentoRecursoApelacion164($idRegistro)
{
	global $con;
	$fechaA=date("Y-m-d");
	
	$borrar="DELETE FROM _123_tablaDinamica WHERE iFormulario='164' AND iRegistro='".$idRegistro."'";
	$con->ejecutarConsulta($borrar);

	$consulDatos="SELECT fechaCreacion,responsable,codigoInstitucion,carpetaAdministrativa FROM _164_tablaDinamica 
				WHERE id__164_tablaDinamica='".$idRegistro."'";
	$res=$con->obtenerPrimeraFila($consulDatos);
	$fechaCreacion=$res[0];
	$idResponsable=$res[1];
	$idEstado=1.1;
	$codigoUnidad=$res[2];
	$carperaAdministrativa=$res[3];
	
	$x=0;
	$consulta[$x]="begin";
	$x++;
	
	$consulta[$x]="INSERT INTO _123_tablaDinamica(fechaCreacion,responsable,idEstado,codigoInstitucion,idPersona,tipoFigura,tipoDocumento,
				iFormulario,iRegistro,carpetaAdministrativa) VALUE('".$fechaA."','".$idResponsable."','".$idEstado."',
				'".$codigoUnidad."','0','0','195','164','".$idRegistro."','".$carperaAdministrativa."')";
	$x++;
	$consulta[$x]="INSERT INTO _123_tablaDinamica(fechaCreacion,responsable,idEstado,codigoInstitucion,idPersona,tipoFigura,tipoDocumento,
				iFormulario,iRegistro,carpetaAdministrativa) VALUE('".$fechaA."','".$idResponsable."','".$idEstado."',
				'".$codigoUnidad."','0','0','146','164','".$idRegistro."','".$carperaAdministrativa."')";
	$x++;
	$consulta[$x]="commit";
	$con->ejecutarBloque($consulta);
}

function llenarDocumentoAcuerdoRecursoApelacion($idDocumento,$idReferencia,$idRegistro,$idFormulario)
{
	global $con;
	global $arrMesLetra;
	$arrValores=array();
	$diahoy=date("d");
	$mesHoy=$arrMesLetra[(date("m")*1)-1];
	$anioHoy=date("Y");
	$fechaHoy="a ".$diahoy." de ".$mesHoy." del ".$anioHoy;

	$consuDatos="SELECT iRegistro FROM _123_tablaDinamica WHERE id__123_tablaDinamica='".$idRegistro."'";
	$idRegistroA=$con->obtenerValor($consuDatos);
	
	$consulta="SELECT codigo,carpetaAdministrativa,imputados,figurasJuridica FROM _164_tablaDinamica WHERE id__164_tablaDinamica='".$idRegistroA."'";
	$res=$con->obtenerPrimeraFila($consulta);
	
	$folio=$res[0];
	$carpetaA=$res[1];

	$arrValores["carpeta"]=$carpetaA;
	$arrValores["noOficio"]=$folio;

	return $arrValores;
}

function llenarDocumentoConstanciaRecursoApelacion($idDocumento,$idReferencia,$idRegistro,$idFormulario)
{
	global $con;
	global $arrMesLetra;
	$arrValores=array();
	$diahoy=date("d");
	$mesHoy=$arrMesLetra[(date("m")*1)-1];
	$anioHoy=date("Y");
	$fechaHoy="a ".$diahoy." de ".$mesHoy." del ".$anioHoy;

	$consuDatos="SELECT iRegistro FROM _123_tablaDinamica WHERE id__123_tablaDinamica='".$idRegistro."'";
	$idRegistroA=$con->obtenerValor($consuDatos);
	
	$consulta="SELECT codigo,carpetaAdministrativa,imputados,figurasJuridica FROM _164_tablaDinamica WHERE id__164_tablaDinamica='".$idRegistroA."'";
	$res=$con->obtenerPrimeraFila($consulta);
	
	$folio=$res[0];
	$carpetaA=$res[1];
//	$idImputado=$res[2];
	//$idEvento=$res[4];
	//$delito=obtenerDatosDelito($carpetaA);

	$arrValores["noCarpetaAdministrativa"]=$carpetaA;
	$arrValores["noOficio"]=$folio;
	$arrValores["fechaHoy"]=$fechaHoy;
	
	return $arrValores;
}

function insertarDocumentoSeguimientoAmparoIndirecto172($idRegistro)
{
	global $con;
	$fechaA=date("Y-m-d");
	
	$borrar="DELETE FROM _123_tablaDinamica WHERE iFormulario='172' AND iRegistro='".$idRegistro."'";
	$con->ejecutarConsulta($borrar);

	$consulDatos="SELECT fechaCreacion,responsable,codigoInstitucion,carpetaAdministrativa FROM _172_tablaDinamica 
				WHERE id__172_tablaDinamica='".$idRegistro."'";
	$res=$con->obtenerPrimeraFila($consulDatos);
	$fechaCreacion=$res[0];
	$idResponsable=$res[1];
	$idEstado=1;
	$codigoUnidad=$res[2];
	$carperaAdministrativa=$res[3];
	
	$x=0;
	$consulta[$x]="begin";
	$x++;
	
	$consulta[$x]="INSERT INTO _123_tablaDinamica(fechaCreacion,responsable,idEstado,codigoInstitucion,idPersona,tipoFigura,tipoDocumento,
				iFormulario,iRegistro,carpetaAdministrativa) VALUE('".$fechaA."','".$idResponsable."','".$idEstado."',
				'".$codigoUnidad."','0','0','141','172','".$idRegistro."','".$carperaAdministrativa."')";
	$x++;
	$consulta[$x]="INSERT INTO _123_tablaDinamica(fechaCreacion,responsable,idEstado,codigoInstitucion,idPersona,tipoFigura,tipoDocumento,
				iFormulario,iRegistro,carpetaAdministrativa) VALUE('".$fechaA."','".$idResponsable."','".$idEstado."',
				'".$codigoUnidad."','0','0','147','172','".$idRegistro."','".$carperaAdministrativa."')";
	$x++;
	$consulta[$x]="INSERT INTO _123_tablaDinamica(fechaCreacion,responsable,idEstado,codigoInstitucion,idPersona,tipoFigura,tipoDocumento,
				iFormulario,iRegistro,carpetaAdministrativa) VALUE('".$fechaA."','".$idResponsable."','".$idEstado."',
				'".$codigoUnidad."','0','0','148','172','".$idRegistro."','".$carperaAdministrativa."')";
	$x++;
	
	$consulta[$x]="commit";
	$con->ejecutarBloque($consulta);
}

function llenarDocumentoSeguimientoAutoAmparoIndirecto($idDocumento,$idReferencia,$idRegistro,$idFormulario)
{
	global $con;
	global $arrMesLetra;
	$arrValores=array();
	$diahoy=date("d");
	$mesHoy=$arrMesLetra[(date("m")*1)-1];
	$anioHoy=date("Y");
	$fechaHoy="a ".$diahoy." de ".$mesHoy." del ".$anioHoy;

	$consuDatos="SELECT iRegistro FROM _123_tablaDinamica WHERE id__123_tablaDinamica='".$idRegistro."'";
	$idRegistroA=$con->obtenerValor($consuDatos);
	
	$consulta="SELECT codigo,carpetaAdministrativa FROM _172_tablaDinamica WHERE id__172_tablaDinamica='".$idRegistroA."'";
	$res=$con->obtenerPrimeraFila($consulta);
	
	$folio=$res[0];
	$carpetaA=$res[1];

	$arrValores["carpeta"]=$carpetaA;
	$arrValores["noOficio"]=$folio;
	$arrValores["fechaHoy"]=$fechaHoy;
	
	return $arrValores;
}

function llenarDocumentoInformeJustificadoAmparoIndirecto($idDocumento,$idReferencia,$idRegistro,$idFormulario)
{
	global $con;
	global $arrMesLetra;
	$arrValores=array();
	$diahoy=date("d");
	$mesHoy=$arrMesLetra[(date("m")*1)-1];
	$anioHoy=date("Y");
	$fechaHoy="a ".$diahoy." de ".$mesHoy." del ".$anioHoy;

	$consuDatos="SELECT iRegistro FROM _123_tablaDinamica WHERE id__123_tablaDinamica='".$idRegistro."'";
	$idRegistroA=$con->obtenerValor($consuDatos);
	
	$consulta="SELECT codigo,carpetaAdministrativa FROM _172_tablaDinamica WHERE id__172_tablaDinamica='".$idRegistroA."'";
	$res=$con->obtenerPrimeraFila($consulta);
	
	$folio=$res[0];
	$carpetaA=$res[1];

	$arrValores["carpeta"]=$carpetaA;
	$arrValores["noOficio"]=$folio;
	$arrValores["fechaHoy"]=$fechaHoy;
	
	return $arrValores;
}

function llenarDocumentoInformePrevioAmparoIndirecto($idDocumento,$idReferencia,$idRegistro,$idFormulario)
{
	global $con;
	global $arrMesLetra;
	$arrValores=array();
	$diahoy=date("d");
	$mesHoy=$arrMesLetra[(date("m")*1)-1];
	$anioHoy=date("Y");
	$fechaHoy="a ".$diahoy." de ".$mesHoy." del ".$anioHoy;

	$consuDatos="SELECT iRegistro FROM _123_tablaDinamica WHERE id__123_tablaDinamica='".$idRegistro."'";
	$idRegistroA=$con->obtenerValor($consuDatos);
	
	$consulta="SELECT codigo,carpetaAdministrativa FROM _172_tablaDinamica WHERE id__172_tablaDinamica='".$idRegistroA."'";
	$res=$con->obtenerPrimeraFila($consulta);
	
	$folio=$res[0];
	$carpetaA=$res[1];

	$arrValores["carpeta"]=$carpetaA;
	$arrValores["noOficio"]=$folio;
	$arrValores["fechaHoy"]=$fechaHoy;
	
	return $arrValores;
}

function cambiarEtapaProcesarTipoAmparo($idRegistro,$idFormulario)
{
	global $con;
	$consulta="SELECT tipoAmparo FROM _172_tablaDinamica WHERE id__172_tablaDinamica='".$idRegistro."'";
	$res=$con->obtenerValor($consulta);
	switch($res)
	{
		case 2://Indirecto
				$etapa='2';
		break;
		case 3://directo
				$etapa='11';
		break;
	}
	
	cambiarEtapaFormulario($idFormulario,$idRegistro,$etapa);
}


function insertarDocumentoSeguimientoAmparoDirecto172($idRegistro)
{
	global $con;
	$fechaA=date("Y-m-d");
	
	$borrar="DELETE FROM _123_tablaDinamica WHERE iFormulario='172' AND iRegistro='".$idRegistro."'";
	$con->ejecutarConsulta($borrar);

	$consulDatos="SELECT fechaCreacion,responsable,codigoInstitucion,carpetaAdministrativa FROM _172_tablaDinamica 
				WHERE id__172_tablaDinamica='".$idRegistro."'";
	$res=$con->obtenerPrimeraFila($consulDatos);
	$fechaCreacion=$res[0];
	$idResponsable=$res[1];
	$idEstado=1;
	$codigoUnidad=$res[2];
	$carperaAdministrativa=$res[3];
	
	$x=0;
	$consulta[$x]="begin";
	$x++;
	
	$consulta[$x]="INSERT INTO _123_tablaDinamica(fechaCreacion,responsable,idEstado,codigoInstitucion,idPersona,tipoFigura,tipoDocumento,
				iFormulario,iRegistro,carpetaAdministrativa) VALUE('".$fechaA."','".$idResponsable."','".$idEstado."',
				'".$codigoUnidad."','0','0','159','172','".$idRegistro."','".$carperaAdministrativa."')";
	$x++;
	$consulta[$x]="INSERT INTO _123_tablaDinamica(fechaCreacion,responsable,idEstado,codigoInstitucion,idPersona,tipoFigura,tipoDocumento,
				iFormulario,iRegistro,carpetaAdministrativa) VALUE('".$fechaA."','".$idResponsable."','".$idEstado."',
				'".$codigoUnidad."','0','0','161','172','".$idRegistro."','".$carperaAdministrativa."')";
	$x++;
	
	$consulta[$x]="commit";
	$con->ejecutarBloque($consulta);
}

function llenarDocumentoAutoAmparoDirecto($idDocumento,$idReferencia,$idRegistro,$idFormulario)
{
	global $con;
	global $arrMesLetra;
	$arrValores=array();
	$diahoy=date("d");
	$mesHoy=$arrMesLetra[(date("m")*1)-1];
	$anioHoy=date("Y");
	$fechaHoy="a ".$diahoy." de ".$mesHoy." del ".$anioHoy;

	$consuDatos="SELECT iRegistro FROM _123_tablaDinamica WHERE id__123_tablaDinamica='".$idRegistro."'";
	$idRegistroA=$con->obtenerValor($consuDatos);
	
	$consulta="SELECT codigo,carpetaAdministrativa FROM _172_tablaDinamica WHERE id__172_tablaDinamica='".$idRegistroA."'";
	$res=$con->obtenerPrimeraFila($consulta);
	
	$folio=$res[0];
	$carpetaA=$res[1];

	$arrValores["carpeta"]=$carpetaA;
	$arrValores["noOficio"]=$folio;
	$arrValores["fechaHoy"]=$fechaHoy;
	
	return $arrValores;
}

function llenarDocumentoOficioAmparoDirecto($idDocumento,$idReferencia,$idRegistro,$idFormulario)
{
	global $con;
	global $arrMesLetra;
	$arrValores=array();
	$diahoy=date("d");
	$mesHoy=$arrMesLetra[(date("m")*1)-1];
	$anioHoy=date("Y");
	$fechaHoy="a ".$diahoy." de ".$mesHoy." del ".$anioHoy;

	$consuDatos="SELECT iRegistro FROM _123_tablaDinamica WHERE id__123_tablaDinamica='".$idRegistro."'";
	$idRegistroA=$con->obtenerValor($consuDatos);
	
	$consulta="SELECT codigo,carpetaAdministrativa FROM _172_tablaDinamica WHERE id__172_tablaDinamica='".$idRegistroA."'";
	$res=$con->obtenerPrimeraFila($consulta);
	
	$folio=$res[0];
	$carpetaA=$res[1];

	$arrValores["carpeta"]=$carpetaA;
	$arrValores["noOficio"]=$folio;
	$arrValores["fechaHoy"]=$fechaHoy;
	
	return $arrValores;
}

function insertarDocumentoTraslado84($idRegistro)
{
	global $con;
	$fechaA=date("Y-m-d");
	
	$borrar="DELETE FROM _123_tablaDinamica WHERE iFormulario='84' AND iRegistro='".$idRegistro."'";
	$con->ejecutarConsulta($borrar);
	
	$consulDatos="SELECT fechaCreacion,responsable,codigoInstitucion,carpetaAdministrativa,idUsuario FROM _84_tablaDinamica 
				WHERE id__84_tablaDinamica='".$idRegistro."'";
	$res=$con->obtenerPrimeraFila($consulDatos);
	$fechaCreacion=$res[0];
	$idResponsable=$res[1];
	$idEstado=1;
	$codigoUnidad=$res[2];
	$carpetaA=$res[3];
	$idUsuario=$res[4];
	$figura='4';
	
	$x=0;
	$consulta[$x]="begin";
	$x++;
	
	$consulta[$x]="INSERT INTO _123_tablaDinamica(fechaCreacion,responsable,idEstado,codigoInstitucion,idPersona,tipoFigura,tipoDocumento,
				iFormulario,iRegistro,carpetaAdministrativa) VALUE('".$fechaA."','".$idResponsable."','".$idEstado."','".$codigoUnidad."',
				'".$idUsuario."','4','45','84','".$idRegistro."','".$carpetaA."')";
	$x++;
	$consulta[$x]="commit";
	$con->ejecutarBloque($consulta);
}

function insertarDocumentoEmisionExhorto191($idFormulario,$idRegistro)//Emision exhorto
{
	global $con;
	$fechaA=date("Y-m-d");
	
	$borrar="DELETE FROM _123_tablaDinamica WHERE iFormulario='191' AND iRegistro='".$idRegistro."'";
	$con->ejecutarConsulta($borrar);
	
	$consulDatos="SELECT fechaCreacion,responsable,codigoInstitucion,carpetaAdministrativa FROM _191_tablaDinamica 
				WHERE id__191_tablaDinamica='".$idRegistro."'";
	$res=$con->obtenerPrimeraFila($consulDatos);
	$fechaCreacion=$res[0];
	$idResponsable=$res[1];
	$idEstado=1;
	$codigoUnidad=$res[2];
	$carpetaA=$res[3];
	//$idUsuario=$res[4];
	//$figura='4';
	
	$x=0;
	$consulta[$x]="begin";
	$x++;
	
	$consulta[$x]="INSERT INTO _123_tablaDinamica(fechaCreacion,responsable,idEstado,codigoInstitucion,idPersona,tipoFigura,tipoDocumento,
				iFormulario,iRegistro,carpetaAdministrativa) VALUE('".$fechaA."','".$idResponsable."','".$idEstado."','".$codigoUnidad."',
				'0','0','140','191','".$idRegistro."','".$carpetaA."')";
	$x++;
	$consulta[$x]="commit";
	$con->ejecutarBloque($consulta);
}

function insertarDocumentoEmisionExhortoEjecucion241($idRegistro)
{
	global $con;
	$fechaA=date("Y-m-d");
	
	$borrar="DELETE FROM _123_tablaDinamica WHERE iFormulario='241' AND iRegistro='".$idRegistro."'";
	$con->ejecutarConsulta($borrar);
	
	$consulDatos="SELECT fechaCreacion,responsable,codigoInstitucion,carpetaAdministrativa FROM _241_tablaDinamica 
				WHERE id__241_tablaDinamica='".$idRegistro."'";
	$res=$con->obtenerPrimeraFila($consulDatos);
	$fechaCreacion=$res[0];
	$idResponsable=$res[1];
	$idEstado=1;
	$codigoUnidad=$res[2];
	$carpetaA=$res[3];
	//$idUsuario=$res[4];
	//$figura='4';
	
	$x=0;
	$consulta[$x]="begin";
	$x++;
	
	$consulta[$x]="INSERT INTO _123_tablaDinamica(fechaCreacion,responsable,idEstado,codigoInstitucion,idPersona,tipoFigura,tipoDocumento,
				iFormulario,iRegistro,carpetaAdministrativa) VALUE('".$fechaA."','".$idResponsable."','".$idEstado."','".$codigoUnidad."',
				'0','0','193','241','".$idRegistro."','".$carpetaA."')";
	$x++;
	$consulta[$x]="INSERT INTO _123_tablaDinamica(fechaCreacion,responsable,idEstado,codigoInstitucion,idPersona,tipoFigura,tipoDocumento,
				iFormulario,iRegistro,carpetaAdministrativa) VALUE('".$fechaA."','".$idResponsable."','".$idEstado."','".$codigoUnidad."',
				'0','0','194','241','".$idRegistro."','".$carpetaA."')";
	$x++;
	
	$consulta[$x]="commit";
	$con->ejecutarBloque($consulta);
}

function insertarDocumentoEmisionDocumento101($idRegistro)
{
	global $con;
	$fechaA=date("Y-m-d");
	
	$borrar="DELETE FROM _123_tablaDinamica WHERE iFormulario='101' AND iRegistro='".$idRegistro."'";
	$con->ejecutarConsulta($borrar);
	
	$consulDatos="SELECT fechaCreacion,responsable,codigoInstitucion FROM _101_tablaDinamica 
				WHERE id__101_tablaDinamica='".$idRegistro."'";
	$res=$con->obtenerPrimeraFila($consulDatos);
	$fechaCreacion=$res[0];
	$idResponsable=$res[1];
	$idEstado=1;
	$codigoUnidad=$res[2];
	//$carpetaA=$res[3];
	//$idUsuario=$res[4];
	//$figura='4';
	
	$x=0;
	$consulta[$x]="begin";
	$x++;
	
	$consulta[$x]="INSERT INTO _123_tablaDinamica(fechaCreacion,responsable,idEstado,codigoInstitucion,idPersona,tipoFigura,tipoDocumento,
				iFormulario,iRegistro) VALUE('".$fechaA."','".$idResponsable."','".$idEstado."','".$codigoUnidad."',
				'0','0','196','101','".$idRegistro."')";
	$x++;
	
	$consulta[$x]="commit";
	$con->ejecutarBloque($consulta);
}

function insertarDocumentoRecepcionExhortoEjecucion255($idRegistro)
{
	global $con;
	$fechaA=date("Y-m-d");
	$idFormulario=255;
	
	$borrar="DELETE FROM _123_tablaDinamica WHERE iFormulario=".$idFormulario." AND iRegistro='".$idRegistro."'";
	$con->ejecutarConsulta($borrar);
	
	$consulDatos="SELECT fechaCreacion,responsable,codigoInstitucion FROM _".$idFormulario."_tablaDinamica 
					WHERE id__".$idFormulario."_tablaDinamica='".$idRegistro."'";
	$res=$con->obtenerPrimeraFila($consulDatos);
	$fechaCreacion=$res[0];
	$idResponsable=$res[1];
	$idEstado=1;
	$codigoUnidad=$res[2];
	
	$x=0;
	$consulta[$x]="begin";
	$x++;
	
	$consulta[$x]="INSERT INTO _123_tablaDinamica(fechaCreacion,responsable,idEstado,codigoInstitucion,idPersona,tipoFigura,tipoDocumento,
				iFormulario,iRegistro) VALUE('".$fechaA."','".$idResponsable."','".$idEstado."','".$codigoUnidad."','0','0','198',".$idFormulario.",
				'".$idRegistro."')";
	$x++;
	$consulta[$x]="INSERT INTO _123_tablaDinamica(fechaCreacion,responsable,idEstado,codigoInstitucion,idPersona,tipoFigura,tipoDocumento,
				iFormulario,iRegistro) VALUE('".$fechaA."','".$idResponsable."','".$idEstado."','".$codigoUnidad."','0','0','199',".$idFormulario.",
				'".$idRegistro."')";
	$x++;
	$consulta[$x]="commit";
	$con->ejecutarBloque($consulta);
}

function insertarDocumentoTrasladoImputadoEjecucion263($idRegistro)
{
	global $con;
	$fechaA=date("Y-m-d");
	$idFormulario=263;
	
	$borrar="DELETE FROM _123_tablaDinamica WHERE iFormulario=".$idFormulario." AND iRegistro='".$idRegistro."'";
	$con->ejecutarConsulta($borrar);
	
	$consulDatos="SELECT fechaCreacion,responsable,codigoInstitucion FROM _".$idFormulario."_tablaDinamica 
					WHERE id__".$idFormulario."_tablaDinamica='".$idRegistro."'";
	$res=$con->obtenerPrimeraFila($consulDatos);
	$fechaCreacion=$res[0];
	$idResponsable=$res[1];
	$idEstado=1;
	$codigoUnidad=$res[2];
	
	$x=0;
	$consulta[$x]="begin";
	$x++;
	
	$consulta[$x]="INSERT INTO _123_tablaDinamica(fechaCreacion,responsable,idEstado,codigoInstitucion,idPersona,tipoFigura,tipoDocumento,
				iFormulario,iRegistro) VALUE('".$fechaA."','".$idResponsable."','".$idEstado."','".$codigoUnidad."','0','0','198',".$idFormulario.",
				'".$idRegistro."')";
	$x++;
	$consulta[$x]="INSERT INTO _123_tablaDinamica(fechaCreacion,responsable,idEstado,codigoInstitucion,idPersona,tipoFigura,tipoDocumento,
				iFormulario,iRegistro) VALUE('".$fechaA."','".$idResponsable."','".$idEstado."','".$codigoUnidad."','0','0','199',".$idFormulario.",
				'".$idRegistro."')";
	$x++;
	$consulta[$x]="commit";
	$con->ejecutarBloque($consulta);
}

function insertarDocumentoAudienciaPorAcuerdo46($idRegistro)
{
	global $con;
	$fechaA=date("Y-m-d");
	
	$borrar="DELETE FROM _123_tablaDinamica WHERE iFormulario='46' AND iRegistro='".$idRegistro."'";
	$con->ejecutarConsulta($borrar);

	$consulDatos="SELECT fechaCreacion,responsable,codigoInstitucion,carpetaAdministrativa,idActividad FROM _46_tablaDinamica 
					WHERE id__46_tablaDinamica='".$idRegistro."'";
	$res=$con->obtenerPrimeraFila($consulDatos);
	$fechaCreacion=$res[0];
	$idResponsable=$res[1];
	$idEstado=1.1;
	$codigoUnidad=$res[2];
	$carperaAdministrativa=$res[3];
	
	$x=0;
	$consulta[$x]="begin";
	$x++;
	
	$consulta[$x]="INSERT INTO _123_tablaDinamica(fechaCreacion,responsable,idEstado,codigoInstitucion,idPersona,tipoFigura,tipoDocumento,
				iFormulario,iRegistro,carpetaAdministrativa) VALUE('".$fechaA."','".$idResponsable."','".$idEstado."',
				'".$codigoUnidad."','0','0','124','46','".$idRegistro."','".$carperaAdministrativa."')";
	$x++;
	$consulta[$x]="commit";
	$con->ejecutarBloque($consulta);
}

function insertarDocumentoBilletes120($idRegistro)
{
	global $con;
	$fechaA=date("Y-m-d");
	$idFormulario=120;
	
	$borrar="DELETE FROM _123_tablaDinamica WHERE iFormulario=".$idFormulario." AND iRegistro='".$idRegistro."'";
	$con->ejecutarConsulta($borrar);
	
	$consulDatos="SELECT fechaCreacion,responsable,codigoInstitucion,carpetaAdministrativa FROM _".$idFormulario."_tablaDinamica 
					WHERE id__".$idFormulario."_tablaDinamica='".$idRegistro."'";
	$res=$con->obtenerPrimeraFila($consulDatos);
	$fechaCreacion=$res[0];
	$idResponsable=$res[1];
	$idEstado=1;
	$codigoUnidad=$res[2];
	$carpetaA=$res[3];
	
	$x=0;
	$consulta[$x]="begin";
	$x++;
	
	$consulta[$x]="INSERT INTO _123_tablaDinamica(fechaCreacion,responsable,idEstado,codigoInstitucion,idPersona,tipoFigura,tipoDocumento,
				iFormulario,iRegistro,carpetaAdministrativa) VALUE('".$fechaA."','".$idResponsable."','".$idEstado."','".$codigoUnidad."','0','0',
				'209',".$idFormulario.",'".$idRegistro."','".$carpetaA."')";
	$x++;
	$consulta[$x]="INSERT INTO _123_tablaDinamica(fechaCreacion,responsable,idEstado,codigoInstitucion,idPersona,tipoFigura,tipoDocumento,
				iFormulario,iRegistro,carpetaAdministrativa) VALUE('".$fechaA."','".$idResponsable."','".$idEstado."','".$codigoUnidad."','0','0',
				'208',".$idFormulario.",'".$idRegistro."','".$carpetaA."')";
	$x++;
	$consulta[$x]="INSERT INTO _123_tablaDinamica(fechaCreacion,responsable,idEstado,codigoInstitucion,idPersona,tipoFigura,tipoDocumento,
				iFormulario,iRegistro,carpetaAdministrativa) VALUE('".$fechaA."','".$idResponsable."','".$idEstado."','".$codigoUnidad."','0','0',
				'210',".$idFormulario.",'".$idRegistro."','".$carpetaA."')";
	$x++;
	
	$consulta[$x]="commit";
	$con->ejecutarBloque($consulta);
}

function llenarDocumentoOficioBillete($idDocumento,$idReferencia,$idRegistro,$idFormulario)
{
	global $con;
	global $arrMesLetra;
	$idDocumento=208;
	$arrValores=array();
	$diahoy=date("d");
	$mesHoy=$arrMesLetra[(date("m")*1)-1];
	$anioHoy=date("Y");
	$fechaHoy="a ".$diahoy." de ".$mesHoy." del ".$anioHoy;

	$consuDatos="SELECT iRegistro FROM _123_tablaDinamica WHERE id__123_tablaDinamica='".$idRegistro."'";
	$idRegistroA=$con->obtenerValor($consuDatos);
	
	$consulta="SELECT a.codigo,a.fechaCreacion,a.codigoInstitucion,montoGarantia, detallesAdicionales,idUsuario,carpetaAdministrativa,
				nombreBeneficiario,fechaExpedicion,numeroBillete,fechaDevolucion,fechaEntrega,horaEntrega,imputado,nombreInstitucion 
				FROM _120_tablaDinamica a,_121_tablaDinamica b WHERE b.idReferencia=a.id__120_tablaDinamica 
				AND id__120_tablaDinamica='".$idRegistroA."'";
	$res=$con->obtenerPrimeraFila($consulta);
	
	$folio=$res[0];
	$fechaCreacion=$res[1];
	$codigoUnidad=$res[2];
	$montoGarantia="$ ".number_format($res[3],2);
	$imputado=$res[13];
	$carpetaA=$res[6];
	$nombreBeneficiado=$res[7];
	$fechaExpedicion=$res[8];
	$numBillete=$res[9];
	$fechaDevolucion=$res[10];
	$fechaEntrega=$res[11];
	$horaEntrega=$res[12];
	$Banco=$res[14];
	
	$delito=obtenerDatosDelito($carpetaA);
	//echo "carpeta ".$carpetaA." delito ".$delito."<br>";
	$nombreImputado="";
	if($imputado!=-1)
	{
		$nombreImputado=obtenerNombreImputadoID($imputado);
	}

	$arrValores["fechaHoy"]=$fechaHoy;
	$arrValores["carpetaJudicial"]=$carpetaA;
	$arrValores["delito"]=$delito;
	$arrValores["imputado"]=$nombreImputado;
	$arrValores["numOficio"]=$folio;
	$arrValores["folioBillete"]=$numBillete;
	$arrValores["importe"]=$montoGarantia;
	$arrValores["Banco"]=$Banco;
	$arrValores["numUnidad"]=$codigoUnidad;
	
	return $arrValores;
}

function llenarDocumentoConstanciaBillete($idDocumento,$idReferencia,$idRegistro,$idFormulario)
{
	global $con;
	global $arrMesLetra;
	$idDocumento=209;
	$arrValores=array();
	$diahoy=date("d");
	$mesHoy=$arrMesLetra[(date("m")*1)-1];
	$anioHoy=date("Y");
	$fechaHoy="a ".$diahoy." de ".$mesHoy." del ".$anioHoy;
	

	$consuDatos="SELECT iRegistro FROM _123_tablaDinamica WHERE id__123_tablaDinamica='".$idRegistro."'";
	$idRegistroA=$con->obtenerValor($consuDatos);
	
	$consulta="SELECT a.codigo,a.fechaCreacion,a.codigoInstitucion,montoGarantia, detallesAdicionales,idUsuario,carpetaAdministrativa,
				nombreBeneficiario,fechaExpedicion,numeroBillete,fechaDevolucion,fechaEntrega,horaEntrega,imputado,nombreInstitucion 
				FROM _120_tablaDinamica a,_121_tablaDinamica b WHERE b.idReferencia=a.id__120_tablaDinamica 
				AND id__120_tablaDinamica='".$idRegistroA."'";
	$res=$con->obtenerPrimeraFila($consulta);
	
	$folio=$res[0];
	$fechaCreacion=$res[1];
	$codigoUnidad=$res[2];
	$montoGarantia="$ ".number_format($res[3],2);
	$imputado=$res[13];
	$carpetaA=$res[6];
	$nombreBeneficiado=$res[7];
	$fechaExpedicion=$res[8];
	$numBillete=$res[9];
	$fechaDevolucion=$res[10];
	$fechaEntrega=$res[11];
	$horaEntrega=$res[12];
	$Banco=$res[14];
	
	$delito=obtenerDatosDelito($carpetaA);
	//echo "carpeta ".$carpetaA." delito ".$delito."<br>";
	$nombreImputado="";
	if($imputado!=-1)
	{
		$nombreImputado=obtenerNombreImputadoID($imputado);
	}

	$arrValores["horaInicio"]=date("H:i:s");
	$arrValores["diaInicio"]=date("d");
	$arrValores["mesInicio"]=$arrMesLetra[(date("m")*1)-1];
	$arrValores["anioInicio"]=date("Y");
	$arrValores["leyendaInicio"]=convertirNumeroLetra($arrValores["anioInicio"],false,false);

	$arrValores["fechaHoy"]=$fechaHoy;
	$arrValores["carpetaJudicial"]=$carpetaA;
	$arrValores["delito"]=$delito;
	$arrValores["imputado"]=$nombreImputado;
	$arrValores["numOficio"]=$folio;
	$arrValores["folioBillete"]=$numBillete;
	$arrValores["importe"]=$montoGarantia;
	$arrValores["Banco"]=$Banco;
	$arrValores["numUnidad"]=$codigoUnidad;
	
	return $arrValores;	
}

?>