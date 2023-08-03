<?php 
include_once("latis/conexionBD.php");
include_once("latis/utiles.php");
include_once("latis/sgjp/funcionesInterconexionSGJ.php");
include_once("latis/funcionesReunionesVirtuales.php");


function registrarEventoAudiencia($oEvento,$idFormulario,$idRegistro,$idReferencia,$tipoAudiencia,$etapaProcesal,$situacion,$oDatosParametros)
{
	
	global $con;
	
	$consulta="SELECT fechaCreacion FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$fechaSolicitud=$con->obtenerValor($consulta);
	
	$x=0;
	$query[$x]="begin";
	$x++;	
	
	if(($oEvento["idRegistroEvento"]!="")&&($oEvento["idRegistroEvento"]!="-1"))
	{
		$query[$x]="update 7000_eventosAudiencia set 
				fechaEvento=".(($oEvento["fecha"]!="")?"'".$oEvento["fecha"]."'":"NULL").
				",horaInicioEvento=".(($oEvento["horaInicio"]!="")?"'".$oEvento["horaInicio"]."'":"NULL").",
				horaFinEvento=".(($oEvento["horaFin"]!="")?"'".$oEvento["horaFin"]."'":"NULL").",
				fechaAsignacion='".date("Y-m-d H:i:s")."' ,idSala=".(($oEvento["idSala"]!="")?$oEvento["idSala"]:"NULL").",
				fechaLimiteAtencion=".($oDatosParametros["fechaMaximaAudiencia"]==NULL?"NULL":"'".$oDatosParametros["fechaMaximaAudiencia"]."'").",
				fechaSolicitud='".$fechaSolicitud."'
				where idRegistroEvento=".$oEvento["idRegistroEvento"];
		$x++;
		$query[$x]="set @idRegistro:=".$oEvento["idRegistroEvento"];
		$x++;
		
	}
	else
	{
		$query[$x]="insert into 7000_eventosAudiencia(fechaEvento,horaInicioEvento,horaFinEvento,situacion,fechaAsignacion,idEdificio,idCentroGestion,
					idSala,idFormulario,idRegistroSolicitud,idReferencia,idEtapaProcesal,tipoAudiencia,fechaLimiteAtencion,fechaSolicitud)
					values(".(($oEvento["fecha"]!="")?"'".$oEvento["fecha"]."'":"NULL").",".(($oEvento["horaInicio"]!="")?"'".$oEvento["horaInicio"]."'":"NULL").",".
					(($oEvento["horaFin"]!="")?"'".$oEvento["horaFin"]."'":"NULL").",".$situacion.",'".date("Y-m-d H:i:s")."',".$oEvento["idEdificio"].",".$oEvento["idUnidadGestion"].
					",".(($oEvento["idSala"]!="")?$oEvento["idSala"]:"NULL").",".$idFormulario.",".$idRegistro.",".$idReferencia.",".$etapaProcesal.",".$tipoAudiencia.",".
					($oDatosParametros["fechaMaximaAudiencia"]==NULL?"NULL":"'".$oDatosParametros["fechaMaximaAudiencia"]."'").",'".$fechaSolicitud."')";
		$x++;	
		$query[$x]="set @idRegistro:=(select last_insert_id())";
		$x++;	
	}
	
	if(gettype($oEvento["jueces"])!="string")
	{
		foreach($oEvento["jueces"] as $j)
		{
			$serieRonda="NULL";
			$noRonda="NULL";
			$idUGARonda="NULL";
			if(isset($j["noRonda"]))
			{
				$noRonda=$j["noRonda"]==""?"NULL":$j["noRonda"];
				$serieRonda=$j["tipoRonda"]==""?"NULL":"'".$j["tipoRonda"]."'";
				$idUGARonda=$oEvento["idUnidadGestion"];
				if($j["idUsuario"]!=-1)
				{
					$arrParametros["idFormulario"]=$idFormulario;
					$arrParametros["idRegistro"]=$idRegistro;
					$arrParametros["fechaEvento"]=$oEvento["fecha"];
					$arrParametros["idJuez"]=$j["idUsuario"];
					$arrParametros["tipoRonda"]=str_replace("'","",$serieRonda);
					$arrParametros["noRonda"]=$noRonda;
					$arrParametros["situacion"]=1;
					$arrParametros["idUnidadGestion"]=$oEvento["idUnidadGestion"];
					registrarAsignacionJuez($arrParametros);
				}
			}
			$consulta="SELECT clave FROM _26_tablaDinamica WHERE usuarioJuez=".$j["idUsuario"];
			$noJuez=$con->obtenerValor($consulta);

			$query[$x]="INSERT INTO 7001_eventoAudienciaJuez(idRegistroEvento,idJuez,tipoJuez,titulo,noJuez,serieRonda,noRonda,idUGARonda) 
			VALUES(@idRegistro,".$j["idUsuario"].",".$j["tipoJuez"].",'".cv($j["titulo"])."','".$noJuez."',".$serieRonda.",".$noRonda.",".$idUGARonda.")";
			$x++;
		}
	}
	$query[$x]="commit";
	$x++;
		
	if($con->ejecutarBloque($query))
	{
		
		$consulta="select @idRegistro";
		$idEventoAgenda=$con->obtenerValor($consulta);
		if($oDatosParametros["notificarMAJO"])
		{
			@enviarNotificacionMAJO($idEventoAgenda);
		}
		@registrarAudienciaCarpetaAdministrativa($idFormulario,$idRegistro,$idEventoAgenda);
		$consulta="UPDATE 7001_asignacionesJuezAudiencia SET idEventoAudiencia=".$idEventoAgenda.
				" WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro;
		@$con->ejecutarConsulta($consulta);
		
		return $idEventoAgenda;
	}
	else
		return -100;
	
}




function obtenerEdificioNumeroHoras($tipoAudiencia,$fechaInicial,$fechaFinal,$listaEdificios,$ignEdificio)
{
	global $con;
	$arrEdificio=array();	
	$universoTiempo=10080; //minutos a la semana
	$arrEdificiosPosibles=explode(",",$listaEdificios);	
	
	$arrEdificiosIgnorar=explode(",",$ignEdificio);
	
	foreach($arrEdificiosPosibles as $e)	
	{
		if(existeValor($arrEdificiosIgnorar,$e))
			continue;
		$arrEdificio[$e]["totalTiempo"]=0;
		$arrEdificio[$e]["porcentajeOcupacion"]=0;
		$totalSalas=0;			
		$consulta="SELECT id__15_tablaDinamica FROM _15_tablaDinamica WHERE idReferencia=".$e;		
		$rSalas=$con->obtenerFilas($consulta);
		while($fSala=mysql_fetch_row($rSalas))		
		{
			$resultado=obtenerTotalTiempoAsignado($fSala[0],$fechaInicial,$fechaFinal);
			$arrEdificio[$e]["totalTiempo"]+=$resultado[0];
			$arrEdificio[$e]["porcentajeOcupacion"]+=($resultado[0]/$universoTiempo)*100;
			$totalSalas++;
		}
		
		$arrEdificio[$e]["porcentajeOcupacion"]=($arrEdificio[$e]["porcentajeOcupacion"]/($totalSalas*100))*100;
	}
	
	$cargaMenor=-1;
	foreach($arrEdificio as $e=>$resto)
	{
		if($cargaMenor==-1)		
			$cargaMenor=$resto["porcentajeOcupacion"];
		if($resto["porcentajeOcupacion"]<$cargaMenor)
			$cargaMenor=$resto["porcentajeOcupacion"];
	}
	
	$arrFinal=array();
	foreach($arrEdificio as $e=>$resto)
	{
		if($resto["porcentajeOcupacion"]==$cargaMenor)	
			array_push($arrFinal,$e);
	}
	
	$posFinal=0;
	if(sizeof($arrFinal)>1)
	{
		$posFinal=rand(0,sizeof($arrFinal)-1);
		
	}
	
	if(isset($arrFinal[$posFinal]))
		return $arrFinal[$posFinal];
	return -1;
	
}

function obtenerEdificioNumeroEventos($tipoAudiencia,$fechaInicial,$fechaFinal,$listaEdificios,$ignEdificio)
{
	global $con;
	$arrEdificio=array();	
	$arrEdificiosPosibles=explode(",",$listaEdificios);	
	
	$arrEdificiosIgnorar=explode(",",$ignEdificio);
	
	foreach($arrEdificiosPosibles as $e)	
	{
		if(existeValor($arrEdificiosIgnorar,$e))
			continue;
		$arrEdificio[$e]["totalEventos"]=0;		
		$totalSalas=0;			
		$consulta="SELECT id__15_tablaDinamica FROM _15_tablaDinamica WHERE idReferencia=".$e;		
		$rSalas=$con->obtenerFilas($consulta);
		while($fSala=mysql_fetch_row($rSalas))		
		{
			$resultado=obtenerTotalTiempoAsignado($fSala[0],$fechaInicial,$fechaFinal);
			$arrEdificio[$e]["totalEventos"]+=$resultado[2];
			
		}
	}
	
	$cargaMenor=-1;
	foreach($arrEdificio as $e=>$resto)
	{
		if($cargaMenor==-1)		
			$cargaMenor=$resto["totalEventos"];
		if($resto["totalEventos"]<$cargaMenor)
			$cargaMenor=$resto["totalEventos"];
	}
	
	$arrFinal=array();
	foreach($arrEdificio as $e=>$resto)
	{
		if($resto["totalEventos"]==$cargaMenor)	
			array_push($arrFinal,$e);
	}
	
	$posFinal=0;
	if(sizeof($arrFinal)>1)
	{
		$posFinal=rand(0,sizeof($arrFinal)-1);
		
	}
	
	if(isset($arrFinal[$posFinal]))
		return $arrFinal[$posFinal];
	return -1;
}

function obtenerUnidadGestionNumeroHoras($tAudiencia,$fechaInicial,$fechaFinal,$listaUnidades,$ignUnidadGestion)
{
	global $con;
	$arrUnidadesGestion=array();
	
	$universoTiempo=10080; //minutos a la semana	
	$arrUnidadesIgnorar=explode(",",$ignUnidadGestion);	
	$arrUnidadesPosibles=explode(",",$listaUnidades);	
	foreach($arrUnidadesPosibles as $u)	
	{
		if(existeValor($arrUnidadesIgnorar,$u))
			continue;
			
		$arrUnidadesGestion[$u]["totalTiempo"]=0;
		$arrUnidadesGestion[$u]["porcentajeOcupacion"]=0;
		$consulta="select salasVinculadas from _55_tablaDinamica where idReferencia=".$u;
		$rSalas=$con->obtenerFilas($consulta);
		
		while($fSala=mysql_fetch_row($rSalas))		
		{
			$resultado=obtenerTotalTiempoAsignado($fSala[0],$fechaInicial,$fechaFinal,$u);
			$arrUnidadesGestion[$u]["totalTiempo"]+=$resultado[0];
			$arrUnidadesGestion[$u]["porcentajeOcupacion"]+=($resultado[0]/$universoTiempo)*100;
		}
		
		if(($con->filasAfectadas!=0)&&($con->filasAfectadas!=""))
			$arrUnidadesGestion[$u]["porcentajeOcupacion"]=($arrUnidadesGestion[$u]["porcentajeOcupacion"]/($con->filasAfectadas*100))*100;
		else
			$arrUnidadesGestion[$u]["porcentajeOcupacion"]=0;
	}
	
	$cargaMenor=-1;
	foreach($arrUnidadesGestion as $u=>$resto)
	{
		if($cargaMenor==-1)		
			$cargaMenor=$resto["porcentajeOcupacion"];
		if($resto["porcentajeOcupacion"]<$cargaMenor)
			$cargaMenor=$resto["porcentajeOcupacion"];
	}
	
	
	
	$arrFinal=array();
	foreach($arrUnidadesGestion as $u=>$resto)
	{
		if($resto["porcentajeOcupacion"]==$cargaMenor)	
			array_push($arrFinal,$u);
	}
	
	$posFinal=0;
	if(sizeof($arrFinal)>1)
	{
		$posFinal=rand(0,sizeof($arrFinal)-1);
		
	}

	if(isset($arrFinal[$posFinal]))
		return $arrFinal[$posFinal];
	return -1;
	
}

function obtenerUnidadGestionNumeroEventos($tAudiencia,$fechaInicial,$fechaFinal,$listaUnidades,$ignUnidadGestion,$oDatosParametros)
{
	//Version 2;
	
	global $con;


	$fechaSolitud=date("Y-m-d",strtotime($oDatosParametros["fechaSolicitud"]));
	$considerarGuardias=false;
	$arrUnidadesGestion=array();	
		
	$arrUnidadesPosibles=explode(",",$listaUnidades);
	$arrUnidadesIgnorar=explode(",",$ignUnidadGestion);		
	foreach($arrUnidadesPosibles as $u)	
	{
		$arrUnidadesGestion[$u]["totalEventos"]=0;
		
		$totalOrdinario=0;
		$totalGuardias=0;
		/*$consulta="SELECT SUM(numeroMetrica) FROM 7000_eventosAudiencia e,_4_tablaDinamica s WHERE idCentroGestion=".$u." AND 
					s.id__4_tablaDinamica=e.tipoAudiencia AND fechaEvento>='".$fechaInicial."'";*/

		$consulta="SELECT e.horaInicioEvento,a.numeroMetrica,e.idEdificio,e.idFormulario,e.idRegistroSolicitud,e.fechaSolicitud FROM 7000_eventosAudiencia e, 
					_4_tablaDinamica a WHERE a.id__4_tablaDinamica=e.tipoAudiencia AND e.fechaSolicitud>='".$fechaInicial." 00:00:00' 
					 and idCentroGestion=".$u." and e.situacion  
					 in (SELECT idSituacion FROM 7011_situacionEventosAudiencia WHERE considerarDiponibilidad=1)";//and e.fechaEvento<='".$fechaFinal."'	
			
		$rEventos=$con->obtenerFilas($consulta);
		while($fEvento=mysql_fetch_row($rEventos))
		{
			
			
			$tipoHorario=0;
			
			if($fEvento[2]=4)
				$tipoHorario=determinarHorarioB($fEvento[5]);
			else
				$tipoHorario=determinarHorarioA($fEvento[5]);
			
			if($fEvento[1]=="")
				$fEvento[1]=0;
				
			if($tipoHorario!=2)	
				$totalOrdinario+=$fEvento[1];
			else
				$totalGuardias+=$fEvento[1];

		}

		$resultado=$totalOrdinario;
		if($considerarGuardias)
			$resultado+=$totalGuardias;
		$arrUnidadesGestion[$u]["totalEventos"]=$resultado;		
		
		
		$totalOrdinario=0;

		$consulta="SELECT e.horaInicioEvento,a.numeroMetrica,e.idEdificio,e.idFormulario,e.idRegistroSolicitud,e.fechaSolicitud FROM 7000_eventosAudiencia e, 
					_4_tablaDinamica a WHERE a.id__4_tablaDinamica=e.tipoAudiencia AND e.fechaSolicitud>='".$fechaSolitud." 00:00:00' AND e.fechaSolicitud<='".$fechaSolitud." 23:59:59' 
					 and idCentroGestion=".$u." and e.situacion  
					 in (SELECT idSituacion FROM 7011_situacionEventosAudiencia WHERE considerarDiponibilidad=1)";//and e.fechaEvento<='".$fechaFinal."'	
			
		$rEventos=$con->obtenerFilas($consulta);
		while($fEvento=mysql_fetch_row($rEventos))
		{
			$tipoHorario=0;
			$totalOrdinario+=$fEvento[1];
			
		}
		
		$arrUnidadesGestion[$u]["totalEventosDia"]=$totalOrdinario;		
			
	}

/*	varDump($arrUnidadesGestion);*/
	
	$cargaMenorDia=-1;
	
	foreach($arrUnidadesGestion as $u=>$resto)
	{
		if($cargaMenorDia==-1)		
			$cargaMenorDia=$resto["totalEventosDia"];
		if($resto["totalEventosDia"]<$cargaMenorDia)
			$cargaMenorDia=$resto["totalEventosDia"];
	}
	
	
	
	$arrAuxiliar=array();
	foreach($arrUnidadesGestion as $u=>$resto)
	{
		if($resto["totalEventosDia"]==$cargaMenorDia)
		{
			$arrAuxiliar[$u]=$resto;
		}
	}
	
	$arrUnidadesGestion=$arrAuxiliar;
	


	$cargaMenor=-1;
	foreach($arrUnidadesGestion as $u=>$resto)
	{
		if($cargaMenor==-1)		
			$cargaMenor=$resto["totalEventos"];
		if($resto["totalEventos"]<$cargaMenor)
			$cargaMenor=$resto["totalEventos"];
	}
	
	$arrFinal=array();
	foreach($arrUnidadesGestion as $u=>$resto)
	{
		if($resto["totalEventos"]==$cargaMenor)	
			array_push($arrFinal,$u);
	}
	
	$posFinal=0;
	if(sizeof($arrFinal)>1)
	{
		$posFinal=rand(0,sizeof($arrFinal)-1);
		
	}
	
	if(isset($arrFinal[$posFinal]))
		return $arrFinal[$posFinal];
	return -1;
	
}

function obtenerUnidadGestionNumeroAsignaciones($tAudiencia,$fechaInicial,$fechaFinal,$listaUnidades,$ignUnidadGestion,$oDatosParametros)
{
	//Version 2;
	
	global $con;
	
	
	$consulta="SELECT  tipoAtencion FROM _4_tablaDinamica WHERE id__4_tablaDinamica=".$tAudiencia;
	$tAtencion=$con->obtenerValor($consulta);

	if($tAtencion=="")
		$tAtencion=2;
	
	
	$consulta="SELECT id__4_tablaDinamica FROM _4_tablaDinamica WHERE tipoAtencion=".$tAtencion;
	$lAudiencias=$con->obtenerListaValores($consulta);
	if($lAudiencias=="")
		$lAudiencias=-1;


	$diasAtras=5;
	$diferencia=10;
	
	$fechaActual=date("Y-m-d");	
	$fechaLimite=date("Y-m-d",strtotime("-".$diasAtras." days",strtotime($fechaActual)));
	
	$arrUnidadesGestion=array();	
		
	$arrUnidadesPosibles=explode(",",$listaUnidades);
	
	foreach($arrUnidadesPosibles as $u)	
	{
		$consulta="SELECT claveUnidad FROM _17_tablaDinamica WHERE id__17_tablaDinamica=".$u;
		$ugj=$con->obtenerValor($consulta);
		/*$consulta="SELECT COUNT( DISTINCT  t.carpetaAdministrativa) FROM _46_tablaDinamica t,7006_carpetasAdministrativas c 
					WHERE t.fechaCreacion>='".$fechaLimite."' AND t.fechaCreacion<='".$fechaActual." 23:59:59' AND t.idEstado>1.4 
					AND t.carpetaAdministrativa =c.carpetaAdministrativa AND c.unidadGestion='".$ugj."'";
					
		
		$arrUnidadesGestion[$u]["totalEventosDia"]=$con->obtenerValor($consulta);	*/
		$arrUnidadesGestion[$u]["totalEventosDia"]=1;
		
		$consulta="SELECT t.fechaCreacion FROM _46_tablaDinamica t,7006_carpetasAdministrativas c 
					WHERE t.idEstado>1.4 and t.tipoAudiencia in(".$lAudiencias.") 
					AND t.carpetaAdministrativa =c.carpetaAdministrativa AND c.unidadGestion='".$ugj.
					"'   order by t.fechaCreacion desc limit 0,1";
		
		$arrUnidadesGestion[$u]["ultimaAsignacion"]=$con->obtenerValor($consulta);
			
			
	}
	
	$cargaMenorDia=-1;
	
	foreach($arrUnidadesGestion as $u=>$resto)
	{
		if($cargaMenorDia==-1)		
			$cargaMenorDia=$resto["totalEventosDia"];
		if($resto["totalEventosDia"]<$cargaMenorDia)
			$cargaMenorDia=$resto["totalEventosDia"];
	}
	
	
	$arrUnidadesGestionAux=array();
	$arrAuxiliar=array();
	foreach($arrUnidadesGestion as $u=>$resto)
	{
		if(($resto["totalEventosDia"]-$cargaMenorDia)<=$diferencia)
		{
			$arrUnidadesGestionAux[$u]=$resto;
		}
	}
	
	$arrUnidadesGestion=$arrUnidadesGestionAux;
	/*if($_SESSION["idUsr"]==1)
	{
		varDump($arrUnidadesGestion);
	}*/
	//varDump($arrUnidadesGestion);
	$idUnidad=NULL;
	$fechaReferencia=NULL;
	foreach($arrUnidadesGestion as $u=>$resto)
	{
		if($idUnidad==NULL)	
		{
			$idUnidad=$u;
			$fechaReferencia=strtotime($resto["ultimaAsignacion"]);
		}
		
		if($fechaReferencia>strtotime($resto["ultimaAsignacion"]))
		{
			$idUnidad=$u;
			$fechaReferencia=strtotime($resto["ultimaAsignacion"]);
		}
		
		
	}
	

	if($idUnidad==NULL)
		return -1;
	else
		return $idUnidad;
	
}
/*function obtenerUnidadGestionNumeroEventos($tAudiencia,$fechaInicial,$fechaFinal,$listaUnidades,$ignUnidadGestion)
{
	global $con;
	$arrUnidadesGestion=array();		
		
	$arrUnidadesPosibles=explode(",",$listaUnidades);
	$arrUnidadesIgnorar=explode(",",$ignUnidadGestion);		
	foreach($arrUnidadesPosibles as $u)	
	{
		$arrUnidadesGestion[$u]["totalEventos"]=0;

		$consulta="select salasVinculadas from _55_tablaDinamica where idReferencia=".$u;
		$rSalas=$con->obtenerFilas($consulta);
		while($fSala=mysql_fetch_row ($rSalas))		
		{
			
			$resultado=obtenerTotalTiempoAsignado($fSala[0],$fechaInicial,$fechaFinal,$u);
			$arrUnidadesGestion[$u]["totalEventos"]+=$resultado[2];
			
		}
	}
	
	
	$cargaMenor=-1;
	foreach($arrUnidadesGestion as $u=>$resto)
	{
		if($cargaMenor==-1)		
			$cargaMenor=$resto["totalEventos"];
		if($resto["totalEventos"]<$cargaMenor)
			$cargaMenor=$resto["totalEventos"];
	}
	

	
	$arrFinal=array();
	foreach($arrUnidadesGestion as $u=>$resto)
	{
		if($resto["totalEventos"]==$cargaMenor)	
			array_push($arrFinal,$u);
	}
	
	$posFinal=0;
	if(sizeof($arrFinal)>1)
	{
		$posFinal=rand(0,sizeof($arrFinal)-1);
		
	}
	
	if(isset($arrFinal[$posFinal]))
		return $arrFinal[$posFinal];
	return -1;
	
}
*/

function obtenerSiguienteEntidad($tipoEntidad,$idReferenciaAux=-1,$idReferenciaAux2=-1,$idReferenciaAux3=-1)
{
	global $con;	
	$idResultado=-1;
	switch($tipoEntidad)
	{
		case 1: //UnidadGestion
			
			$listaUnidadesGestion=$idReferenciaAux2;
			
			$consulta="select idEntidad from 7002_controlSecuencialEntidades where tipoEntidad=".$tipoEntidad." and idEntidad in (".$listaUnidadesGestion.") order by idRegistroSecuencia desc";
			$idEntidad=$con->obtenerValor($consulta);
			if($idEntidad=="")
				$idEntidad=0;
			$consulta="SELECT id__17_tablaDinamica FROM  _17_tablaDinamica where id__17_tablaDinamica>".$idEntidad." and id__17_tablaDinamica  in (".$listaUnidadesGestion.") and id__17_tablaDinamica not in (".$idReferenciaAux3.") order by id__17_tablaDinamica";
			$idResultado=$con->obtenerValor($consulta);	
			if($idResultado=="")
			{
				$consulta="SELECT id__17_tablaDinamica FROM  _17_tablaDinamica where id__17_tablaDinamica<".$idEntidad." and id__17_tablaDinamica  in (".$listaUnidadesGestion.") and id__17_tablaDinamica not in (".$idReferenciaAux3.") order by id__17_tablaDinamica";
				$idResultado=$con->obtenerValor($consulta);	
			}
			if(($idResultado=="")&&($idEntidad!="0"))
			{
				
				$idResultado=$idEntidad;	
			}
						
				
		break;
		case 2: //Sala
			
			$listaSalas=$idReferenciaAux;
			
			$consulta="select idEntidad from 7002_controlSecuencialEntidades where tipoEntidad=".$tipoEntidad." and idEntidad in (".$listaSalas.") order by idRegistroSecuencia desc";
			$idEntidad=$con->obtenerValor($consulta);
			if($idEntidad=="")
				$idEntidad=0;
			$consulta="SELECT id__15_tablaDinamica FROM  _15_tablaDinamica where id__15_tablaDinamica >".$idEntidad." and id__15_tablaDinamica in(".$listaSalas.") and id__15_tablaDinamica not in (".$idReferenciaAux3.") order by id__15_tablaDinamica";
			$idResultado=$con->obtenerValor($consulta);	
			if($idResultado=="")
			{
				$consulta="SELECT id__15_tablaDinamica FROM  _15_tablaDinamica where id__15_tablaDinamica <".$idEntidad." and id__15_tablaDinamica in(".$listaSalas.") and id__15_tablaDinamica not in (".$idReferenciaAux3.")order by id__15_tablaDinamica";
				$idResultado=$con->obtenerValor($consulta);	
			}
			if(($idResultado=="")&&($idEntidad!="0"))
			{
				
				$idResultado=$idEntidad;	
			}
			
		break;
		case 4:
			
			$listaEdificios=$idReferenciaAux2;			
			$consulta="select idEntidad from 7002_controlSecuencialEntidades where tipoEntidad=".$tipoEntidad." and idEntidad in (".$listaEdificios.") order by idRegistroSecuencia desc";
			$idEntidad=$con->obtenerValor($consulta);
			if($idEntidad=="")
				$idEntidad=0;
				
			$consulta="SELECT id__1_tablaDinamica FROM  _1_tablaDinamica where id__1_tablaDinamica >".$idEntidad." and id__1_tablaDinamica in(".$listaEdificios.") and id__1_tablaDinamica not in(".$idReferenciaAux3.") order by id__1_tablaDinamica";
			$idResultado=$con->obtenerValor($consulta);	
			if($idResultado=="")
			{
				$consulta="SELECT id__1_tablaDinamica FROM  _1_tablaDinamica where id__1_tablaDinamica <".$idEntidad." and id__1_tablaDinamica in(".$listaEdificios.") and id__1_tablaDinamica not in(".$idReferenciaAux3.") order by id__1_tablaDinamica";
				$idResultado=$con->obtenerValor($consulta);	
			}
			if(($idResultado=="")&&($idEntidad!="0"))
			{
				
				$idResultado=$idEntidad;	
			}
		break;
		
	}
	if($idResultado!="")
	{
		$consulta="insert into 7002_controlSecuencialEntidades(tipoEntidad,idEntidad) values(".$tipoEntidad.",".$idResultado.")";
		if($con->ejecutarConsulta($consulta))
			return $idResultado;
	}
	return -1;
}

function obtenerSalaAsignacionNumeroHoras($listaSalas,$tipoAudiencia,$fechaInicial,$fechaFinal,$listadoSalasIgnorar)
{
	global $con;
	$arrSalas=array();
	
	$universoTiempo=10080; //minutos a la semana	
	
	$arrSalasIgnorar=explode(",",$listadoSalasIgnorar);
	
	$arrSalasPosibles=explode(",",$listaSalas);

	foreach($arrSalasPosibles as $s)	
	{
		if(existeValor($arrSalasIgnorar,$s))
			continue;
		$resultado=obtenerTotalTiempoAsignado($s,$fechaInicial,$fechaFinal);
		$arrSalas[$s]["totalTiempo"]=$resultado[0];
		$arrSalas[$s]["porcentajeOcupacion"]=($resultado[0]/$universoTiempo)*100;
		
	}
		
	$cargaMenor=-1;
	foreach($arrSalas as $s=>$resto)
	{
		if($cargaMenor==-1)		
			$cargaMenor=$resto["porcentajeOcupacion"];
		if($resto["porcentajeOcupacion"]<$cargaMenor)
			$cargaMenor=$resto["porcentajeOcupacion"];
	}
	
	$arrFinal=array();
	foreach($arrSalas as $s=>$resto)
	{
		if($resto["porcentajeOcupacion"]==$cargaMenor)	
			array_push($arrFinal,$s);
	}
	
	$posFinal=0;
	if(sizeof($arrFinal)>1)
	{
		$posFinal=rand(0,sizeof($arrFinal)-1);
		
	}

	if(isset($arrFinal[$posFinal]))
		return $arrFinal[$posFinal];
	return -1;
	
}

function obtenerSalaAsignacionNumeroEventos($listaSalas,$tipoAudiencia,$fechaInicial,$fechaFinal,$listadoSalasIgnorar)
{
	global $con;
	$arrSalas=array();
	
		
	$arrSalasIgnorar=explode(",",$listadoSalasIgnorar);
	$arrSalasPosibles=explode(",",$listaSalas);
	foreach($arrSalasPosibles as $s)	
	{
		if(existeValor($arrSalasIgnorar,$s))
			continue;
		$resultado=obtenerTotalTiempoAsignado($s,$fechaInicial,$fechaFinal);
		$arrSalas[$s]["totalEventos"]=$resultado[2];
	}
	
	$cargaMenor=-1;
	foreach($arrSalas as $s=>$resto)
	{
		if($cargaMenor==-1)		
			$cargaMenor=$resto["totalEventos"];
		if($resto["totalEventos"]<$cargaMenor)
			$cargaMenor=$resto["totalEventos"];
	}
	
	$arrFinal=array();
	foreach($arrSalas as $s=>$resto)
	{
		if($resto["totalEventos"]==$cargaMenor)	
			array_push($arrFinal,$s);
	}
	
	$posFinal=0;
	if(sizeof($arrFinal)>1)
	{
		$posFinal=rand(0,sizeof($arrFinal)-1);
		
	}
	
	if(isset($arrFinal[$posFinal]))
		return $arrFinal[$posFinal];
	return -1;
}

function obtenerTotalTiempoAsignado($idSala,$fechaInicial,$fechaFinal,$uG="")
{
	global $con;
	$arrDia=array();
	$totalTiempo=0;
	$consulta="SELECT fechaEvento,if(a.horaInicioReal is null,a.horaInicioEvento,a.horaInicioReal),
				if(a.horaTerminoReal is null,a.horaFinEvento,a.horaTerminoReal) FROM 7000_eventosAudiencia a WHERE 
				idSala=".$idSala." AND horaInicioEvento>='".$fechaInicial."' and horaInicioEvento<='".$fechaFinal." 23:59:59'
				and situacion in (SELECT idSituacion FROM 7011_situacionEventosAudiencia WHERE considerarDiponibilidad=1)";

	if($uG!="")
		$consulta.=" and idCentroGestion=".$uG;

	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		
		$diferencia=obtenerDiferenciaMinutos($fila[1],$fila[2]);
		$totalTiempo+=$diferencia;
		if(!isset($arrDia[$fila[0]]))
			$arrDia[$fila[0]]=0;
		$arrDia[$fila[0]]+=$diferencia;
	}
	
	$resultado[0]=$totalTiempo;
	$resultado[1]=$arrDia;
	$resultado[2]=$con->filasAfectadas;
	return $resultado;
}


function noCarpetasAsignadas($listaJueces)
{
	global $con;
	
	/*$arrJueces=array();
	$consulta="SELECT clave,usuarioJuez FROM _26_tablaDinamica WHERE usuarioJuez IN (".$listaJueces.") ORDER BY clave";
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		$arrJueces[$fila[0]]["idUsuario"]=$fila[1];
		$arrJueces[$fila[0]]["totalCarpetas"]=$fila[2];
		
		
		
	}*/
	
	
	
	
	
}

function obtenerJuezAsignacionNumeroHoras($listaJueces,$horaInicial,$horaFinal,$fechaInicial,$fechaFinal,$maximoCriterio)
{
	global $con;	
	$minutosMaximos=$maximoCriterio*60;
	$tiempoSesion=obtenerDiferenciaMinutos($horaInicial,$horaFinal);
	
	$arrJueces=array();
		
	$arrJuecesPosibles=explode(",",$listaJueces);
	
	foreach($arrJuecesPosibles as $j)	
	{
		$resultado=obtenerTotalTiempoJuez($j,$fechaInicial,$fechaFinal);
		if(($maximoCriterio==0)||(($maximoCriterio>0) &&($minutosMaximos>=($resultado[0]+$tiempoSesion))))
			$arrJueces[$j]["totalTiempo"]=$resultado[0];
	}
	
	$cargaMenor=-1;
	foreach($arrJueces as $j=>$resto)
	{
		if($cargaMenor==-1)		
			$cargaMenor=$resto["totalTiempo"];
		if($resto["totalTiempo"]<$cargaMenor)
			$cargaMenor=$resto["totalTiempo"];
	}
	
	$arrFinal=array();
	foreach($arrJueces as $j=>$resto)
	{
		if($resto["totalTiempo"]==$cargaMenor)	
			array_push($arrFinal,$j);
	}
	
	$posFinal=0;
	if(sizeof($arrFinal)>1)
	{
		$posFinal=rand(0,sizeof($arrFinal)-1);
		
	}
	
	if(isset($arrFinal[$posFinal]))
		return $arrFinal[$posFinal];
	return -1;
}


/*function obtenerJuezAsignacionNumeroEventos($listaJueces,$horaInicial,$horaFinal,$fechaInicial,$fechaFinal,$maximoCriterio)
{
	global $con;	
	
	$arrJueces=array();
		
	$arrJuecesPosibles=explode(",",$listaJueces);

	foreach($arrJuecesPosibles as $j)	
	{
		$resultado=sizeof(obtenerEventosJuez($j,$fechaInicial,$fechaFinal));
		
		if(($maximoCriterio==0)||(($maximoCriterio>0)&&($resultado<$maximoCriterio)))
			$arrJueces[$j]["totalEventos"]=$resultado;
	}
	


	$cargaMenor=-1;
	foreach($arrJueces as $j=>$resto)
	{
		if($cargaMenor==-1)		
			$cargaMenor=$resto["totalEventos"];
		if($resto["totalEventos"]<$cargaMenor)
			$cargaMenor=$resto["totalEventos"];
	}
	
	$arrFinal=array();
	foreach($arrJueces as $j=>$resto)
	{
		if($resto["totalEventos"]==$cargaMenor)	
			array_push($arrFinal,$j);
	}
	
	
	
	/*$arrAux=array();	
	foreach($arrFinal as $j)
	{
		
		if(existeDisponibilidadJuez($j,date("Y-m-d",strtotime($horaInicial)),$horaInicial,$horaFinal))
		{
			array_push($arrAux,$j);
		}
		
	}
	$arrFinal=$arrAux;

	
	$posFinal=0;
	if(sizeof($arrFinal)>1)
	{
		$posFinal=rand(0,sizeof($arrFinal)-1);
		
	}
	if(isset($arrFinal[$posFinal]))
		return $arrFinal[$posFinal];
	return -1;
}
*/

function obtenerJuezAsignacionNumeroEventos($listaJueces,$horaInicial,$horaFinal,$fechaInicial,$fechaFinal,$objParametros,$considerarGuardias=false,$seleccionAleatoria=true)
{
	global $con;	
	
	
	$lblEventos="totalEventos";
	
	$arrJueces=array();
		
	$arrJuecesPosibles=explode(",",$listaJueces);
	$fechaSolicitud=date("Y-m-d",strtotime($objParametros->fechaSolicitud));
	foreach($arrJuecesPosibles as $j)	
	{
	
		$resultado=obtenerMetricaEventosJuez($j,$fechaInicial,$fechaFinal,true);
		
		if(($objParametros->horasMaximaAsignablesJuez==0)||(($objParametros->horasMaximaAsignablesJuez>0)&&($resultado<$objParametros->horasMaximaAsignablesJuez)))
		{
			$arrJueces[$j]["totalEventos"]=$resultado;
			$arrJueces[$j]["nombreJuez"]=obtenerNombreUsuario($j);
			$arrJueces[$j]["totalEventosDia"]=obtenerMetricaEventosJuez($j,date("Y-m-d",strtotime("-2 days",strtotime($fechaSolicitud))),$fechaSolicitud,$considerarGuardias);
			
		}
	}
	
	

	$cargaMenorDia=-1;
	if($objParametros->metodoBalanceoEventosJuez==1)
	{
		foreach($arrJueces as $j=>$resto)
		{
			if($cargaMenorDia==-1)		
				$cargaMenorDia=$resto["totalEventosDia"];
			if($resto["totalEventosDia"]<$cargaMenorDia)
				$cargaMenorDia=$resto["totalEventosDia"];
		}
		
		
		
		
		$arrAuxiliar=array();
		foreach($arrJueces as $j=>$resto)
		{
			if($resto["totalEventosDia"]==$cargaMenorDia)
			{
				$arrAuxiliar[$j]=$resto;
			}
		}
		
		$arrJueces=$arrAuxiliar;
	}

	
	
	$cargaMenor=-1;
	foreach($arrJueces as $j=>$resto)
	{
		if($cargaMenor==-1)		
			$cargaMenor=$resto[$lblEventos];
		if($resto[$lblEventos]<$cargaMenor)
			$cargaMenor=$resto[$lblEventos];
	}
	
	
	
	
	$arrFinal=array();
	foreach($arrJueces as $j=>$resto)
	{
		if($resto[$lblEventos]==$cargaMenor)	
			array_push($arrFinal,$j);
	}
	
	
	
	/*$arrAux=array();	
	foreach($arrFinal as $j)
	{
		
		if(existeDisponibilidadJuez($j,date("Y-m-d",strtotime($horaInicial)),$horaInicial,$horaFinal))
		{
			array_push($arrAux,$j);
		}
		
	}
	$arrFinal=$arrAux;*/

	
	$posFinal=0;
	if($seleccionAleatoria)
	{
		if(sizeof($arrFinal)>1)
		{
			
			$posFinal=rand(0,sizeof($arrFinal)-1);
			
		}
	}
	if(isset($arrFinal[$posFinal]))
		return $arrFinal[$posFinal];
	return -1;
}

function obtenerJuezAsignacionNumeroEventosV2($listaJueces,$horaInicial,$horaFinal,$fechaInicial,$fechaFinal,$objParametros,$considerarGuardias=false,$seleccionAleatoria=true)
{
	global $con;	
	
	
	$lblEventos="totalEventosDiaAjustado";
	
	$arrJueces=array();
		
	$arrJuecesPosibles=explode(",",$listaJueces);
	$fechaSolicitud=date("Y-m-d",strtotime($objParametros->fechaSolicitud));
	$diasAtras=4;
	$fechaLimiteInicio=date("Y-m-d",strtotime("-".$diasAtras." days",strtotime($fechaSolicitud)));
	foreach($arrJuecesPosibles as $j)	
	{
	
		$resultado=obtenerMetricaEventosJuez($j,$fechaInicial,$fechaFinal,true);
		
		if(($objParametros->horasMaximaAsignablesJuez==0)||(($objParametros->horasMaximaAsignablesJuez>0)&&($resultado<$objParametros->horasMaximaAsignablesJuez)))
		{
			$arrJueces[$j]["totalEventosCambios"]=obtenerMetricaEventosCambioJuez($j,$fechaLimiteInicio,$fechaSolicitud,$considerarGuardias);
			$arrJueces[$j]["totalEventosAsignacion"]=obtenerMetricaEventosCambioAsignacionJuez($j,$fechaLimiteInicio,$fechaSolicitud,$considerarGuardias);
			$arrJueces[$j]["totalEventos"]=$resultado;
			$arrJueces[$j]["nombreJuez"]=obtenerNombreUsuario($j);
			$arrJueces[$j]["totalEventosDia"]=obtenerMetricaEventosJuez($j,$fechaLimiteInicio,$fechaSolicitud,$considerarGuardias);
			$diferenciaEventos=$arrJueces[$j]["totalEventosCambios"]-$arrJueces[$j]["totalEventosAsignacion"];
			if($diferenciaEventos<0)
				$diferenciaEventos=0;
			$arrJueces[$j]["totalEventosDiaAjustado"]=$arrJueces[$j]["totalEventosDia"]+$diferenciaEventos;
			//$arrJueces[$j]["totalEventosDia"]+=$arrJueces[$j]["totalEventosCambios"];
			
			$consulta="SELECT fechaAsignacion FROM 7000_eventosAudiencia e,7001_eventoAudienciaJuez j,_4_tablaDinamica t WHERE j.idJuez=".$j." 
						AND j.idRegistroEvento=e.idRegistroEvento AND e.situacion  IN
						(
						SELECT idSituacion FROM 7011_situacionEventosAudiencia WHERE considerarDiponibilidad=1
						) and t.id__4_tablaDinamica=e.tipoAudiencia and t.numeroMetrica>0 ORDER BY fechaAsignacion DESC LIMIT 0,1";
			
			$arrJueces[$j]["ultimaAsignacion"]=$con->obtenerValor($consulta);
			
		}
	}
	
	$idJuez=NULL;
	$fechaReferencia=NULL;
	foreach($arrJueces as $j=>$resto)
	{
		if($idJuez==NULL)	
		{
			$idJuez=$j;
			$fechaReferencia=strtotime($resto["ultimaAsignacion"]);
		}
		
		if($fechaReferencia>strtotime($resto["ultimaAsignacion"]))
		{
			$idJuez=$j;
			$fechaReferencia=strtotime($resto["ultimaAsignacion"]);
		}
		
		
	}
	
	
	
	return $idJuez;
	/*$cargaMenorDia=-1;
	if($objParametros->metodoBalanceoEventosJuez==1)
	{
		foreach($arrJueces as $j=>$resto)
		{
			if($cargaMenorDia==-1)		
				$cargaMenorDia=$resto["totalEventosDiaAjustado"];
			if($resto["totalEventosDiaAjustado"]<$cargaMenorDia)
				$cargaMenorDia=$resto["totalEventosDiaAjustado"];
		}
		
		
		
		
		$arrAuxiliar=array();
		foreach($arrJueces as $j=>$resto)
		{
			if($resto["totalEventosDiaAjustado"]==$cargaMenorDia)
			{
				$arrAuxiliar[$j]=$resto;
			}
		}
		
		$arrJueces=$arrAuxiliar;
	}

	
	
	$cargaMenor=-1;
	foreach($arrJueces as $j=>$resto)
	{
		if($cargaMenor==-1)		
			$cargaMenor=$resto[$lblEventos];
		if($resto[$lblEventos]<$cargaMenor)
			$cargaMenor=$resto[$lblEventos];
	}
	
	

	$arrFinal=array();
	foreach($arrJueces as $j=>$resto)
	{
		if($resto[$lblEventos]==$cargaMenor)	
			array_push($arrFinal,$j);
	}*/
	
	
	
	/*$arrAux=array();	
	foreach($arrFinal as $j)
	{
		
		if(existeDisponibilidadJuez($j,date("Y-m-d",strtotime($horaInicial)),$horaInicial,$horaFinal))
		{
			array_push($arrAux,$j);
		}
		
	}
	$arrFinal=$arrAux;*/

	
	$posFinal=0;
	if($seleccionAleatoria)
	{
		if(sizeof($arrFinal)>1)
		{
			
			$posFinal=rand(0,sizeof($arrFinal)-1);
			
		}
	}
	if(isset($arrFinal[$posFinal]))
		return $arrFinal[$posFinal];
	return -1;
}

function obtenerJuezAsignacionNumeroEventosV3($listaJueces,$horaInicial,$horaFinal,$fechaInicial,$fechaFinal,$objParametros,$considerarGuardias=false,$seleccionAleatoria=true)
{
	global $con;	
	
	//return 2402;
	$seleccionAleatoria=true;

	$lblEventos="totalEventosDiaTodas";
	
	$arrJueces=array();
	$fechaAudiencia=date("Y-m-d",strtotime($horaInicial))	;

	$arrJuecesPosibles=explode(",",$listaJueces);
	$fechaSolicitud=date("Y-m-d",strtotime($objParametros->fechaSolicitud));
	$diasAtras=4;
	$fechaLimiteInicio=date("Y-m-d",strtotime("-".$diasAtras." days",strtotime($fechaSolicitud)));
	foreach($arrJuecesPosibles as $j)	
	{
		if($j==-1)
			continue;
		$resultado=obtenerMetricaEventosJuez($j,$fechaInicial,$fechaFinal,true);
		
		if(($objParametros->horasMaximaAsignablesJuez==0)||(($objParametros->horasMaximaAsignablesJuez>0)&&($resultado<$objParametros->horasMaximaAsignablesJuez)))
		{
			$arrJueces[$j]["totalEventosCambios"]=obtenerMetricaEventosCambioJuez($j,$fechaLimiteInicio,$fechaSolicitud,$considerarGuardias);
			$arrJueces[$j]["totalEventosAsignacion"]=obtenerMetricaEventosCambioAsignacionJuez($j,$fechaLimiteInicio,$fechaSolicitud,$considerarGuardias);
			$arrJueces[$j]["totalEventos"]=$resultado;
			$arrJueces[$j]["nombreJuez"]=obtenerNombreUsuario($j);
			$arrJueces[$j]["totalEventosDia"]=obtenerMetricaEventosJuez($j,$fechaLimiteInicio,$fechaSolicitud,$considerarGuardias);
			
			$diferenciaEventos=$arrJueces[$j]["totalEventosCambios"]-$arrJueces[$j]["totalEventosAsignacion"];
			if($diferenciaEventos<0)
				$diferenciaEventos=0;
			$arrJueces[$j]["totalEventosDiaAjustado"]=$arrJueces[$j]["totalEventosDia"]+$diferenciaEventos;
			$arrJueces[$j]["totalEventosDiaActual"]=obtenerMetricaEventosJuez($j,$fechaAudiencia,$fechaAudiencia,true);
			$arrJueces[$j]["totalEventosDiaTodas"]=obtenerTotalEventosJuezAudiencia($j,$fechaAudiencia,$fechaAudiencia,true);
			
			
			$consulta="SELECT fechaAsignacion FROM 7000_eventosAudiencia e,7001_eventoAudienciaJuez j,_4_tablaDinamica t WHERE j.idJuez=".$j." 
						AND j.idRegistroEvento=e.idRegistroEvento AND e.situacion  IN
						(
						SELECT idSituacion FROM 7011_situacionEventosAudiencia WHERE considerarDiponibilidad=1
						) and t.id__4_tablaDinamica=e.tipoAudiencia and t.numeroMetrica>0 ORDER BY fechaAsignacion DESC LIMIT 0,1";
			
			$arrJueces[$j]["ultimaAsignacion"]=$con->obtenerValor($consulta);
			
		}
	}
	
	
	
	$idJuez=NULL;
	$fechaReferencia=NULL;

	foreach($arrJueces as $j=>$resto)
	{
		if($idJuez==NULL)	
		{
			$idJuez=$j;
			$fechaReferencia=strtotime($resto["ultimaAsignacion"]);
		}
		
		if($fechaReferencia>strtotime($resto["ultimaAsignacion"]))
		{
			$idJuez=$j;
			$fechaReferencia=strtotime($resto["ultimaAsignacion"]);
		}
		
		
	}
	
	
	$cargaMenorDia=-1;
	if($objParametros->metodoBalanceoEventosJuez==1)
	{
		foreach($arrJueces as $j=>$resto)
		{
			if($cargaMenorDia==-1)		
				$cargaMenorDia=$resto["totalEventosDiaTodas"];
			if($resto["totalEventosDiaTodas"]<$cargaMenorDia)
				$cargaMenorDia=$resto["totalEventosDiaTodas"];
		}
		
		
		
		
		$arrAuxiliar=array();
		foreach($arrJueces as $j=>$resto)
		{
			if($resto["totalEventosDiaTodas"]==$cargaMenorDia)
			{
				$arrAuxiliar[$j]=$resto;
			}
		}
		
		$arrJueces=$arrAuxiliar;
	}
	


	$arrAux=array();	
	foreach($arrJueces as $j=>$resto)
	{
			
		if(existeDisponibilidadJuez($j,date("Y-m-d",strtotime($horaInicial)),$horaInicial,$horaFinal))
		{
			$arrAux[$j]=$resto;
			
		}
			
	}
	$arrJueces=$arrAux;

	//varDump($horaInicial);

	$cargaMenor=-1;
	foreach($arrJueces as $j=>$resto)
	{
		if($cargaMenor==-1)		
			$cargaMenor=$resto[$lblEventos];
		if($resto[$lblEventos]<$cargaMenor)
			$cargaMenor=$resto[$lblEventos];
	}
	
	
	
	$arrFinal=array();
	foreach($arrJueces as $j=>$resto)
	{
		if($resto[$lblEventos]==$cargaMenor)	
			array_push($arrFinal,$j);
	}
	
	
	

	
	$posFinal=0;
	if($seleccionAleatoria)
	{
		if(sizeof($arrFinal)>1)
		{
			
			$posFinal=rand(0,sizeof($arrFinal)-1);
			
		}
	}
	

	if(isset($arrFinal[$posFinal]))
		return $arrFinal[$posFinal];
	return -1;
}




function obtenerMetricaEventosJuez($idJuez,$fInicial,$fFinal,$considerarGuardia=false)
{
	global $con;
	
	$arrEventos=array();
	$consulta="SELECT a.horaInicioEvento,ta.numeroMetrica,a.idEdificio,a.fechaSolicitud FROM 7000_eventosAudiencia a,7001_eventoAudienciaJuez j,_4_tablaDinamica ta 
				where j.idJuez=".$idJuez." and j.idRegistroEvento=a.idRegistroEvento
				and fechaSolicitud>='".$fInicial." 00:00:00' and fechaSolicitud<='".$fFinal." 23:59:59'  and a.situacion in(SELECT idSituacion FROM 7011_situacionEventosAudiencia 
				WHERE considerarDiponibilidad=1) and ta.id__4_tablaDinamica=a.tipoAudiencia and ta.numeroMetrica>0";

	
	$valorMetrica=0;
	$totalOrdinario=0;
	$totalGuardias=0;
	$rEventos=$con->obtenerFilas($consulta);
	while($fEvento=mysql_fetch_row($rEventos))
	{
		$tipoHorario=0;
		
		if($fEvento[2]==4)
			$tipoHorario=determinarHorarioB($fEvento[3]);
		else
			$tipoHorario=determinarHorarioA($fEvento[3]);
		
		if($fEvento[1]=="")
			$fEvento[1]=0;
			
		if($tipoHorario!=2)	
			$totalOrdinario+=$fEvento[1];
		else
			$totalGuardias+=$fEvento[1];

	}
	$valorMetrica=$totalOrdinario;
	if($considerarGuardia)
		$valorMetrica+=$totalGuardias;
	return $valorMetrica;
	
}


function obtenerTotalEventosJuezAudiencia($idJuez,$fInicial,$fFinal,$considerarGuardia=false)
{
	global $con;
	
	$arrEventos=array();
	$consulta="SELECT a.horaInicioEvento,'1' as numeroMetrica,a.idEdificio,a.fechaSolicitud FROM 7000_eventosAudiencia a,7001_eventoAudienciaJuez j,_4_tablaDinamica ta 
				where j.idJuez=".$idJuez." and j.idRegistroEvento=a.idRegistroEvento
				and horaInicioEvento>='".$fInicial." 00:00:00' and horaInicioEvento<='".$fFinal." 23:59:59'  
				and a.situacion in(SELECT idSituacion FROM 7011_situacionEventosAudiencia WHERE considerarDiponibilidad=1) 
				and ta.id__4_tablaDinamica=a.tipoAudiencia";

	
	$valorMetrica=0;
	$totalOrdinario=0;
	$totalGuardias=0;
	$rEventos=$con->obtenerFilas($consulta);
	while($fEvento=mysql_fetch_row($rEventos))
	{
		$tipoHorario=0;
		
		if($fEvento[2]==4)
			$tipoHorario=determinarHorarioB($fEvento[3]);
		else
			$tipoHorario=determinarHorarioA($fEvento[3]);
		
		if($fEvento[1]=="")
			$fEvento[1]=0;
			
		if($tipoHorario!=2)	
			$totalOrdinario+=$fEvento[1];
		else
			$totalGuardias+=$fEvento[1];

	}
	
	$valorMetrica=$totalOrdinario;
	if($considerarGuardia)
		$valorMetrica+=$totalGuardias;
	return $valorMetrica;
	
}


function obtenerMetricaEventosCambioJuez($idJuez,$fInicial,$fFinal,$considerarGuardia=false)
{
	global $con;
	
	$arrEventos=array();
	$consulta="SELECT a.horaInicioEvento,ta.numeroMetrica,a.idEdificio,a.fechaSolicitud FROM 7000_eventosAudiencia a,3005_bitacoraCambiosJuez j,_4_tablaDinamica ta 
				where j.idJuezOriginal=".$idJuez." and j.idEventoAudiencia=a.idRegistroEvento
				and fechaSolicitud>='".$fInicial." 00:00:00' and fechaSolicitud<='".$fFinal." 23:59:59'  and a.situacion in(SELECT idSituacion FROM 7011_situacionEventosAudiencia 
				WHERE considerarDiponibilidad=1) and ta.id__4_tablaDinamica=a.tipoAudiencia and ta.numeroMetrica>0";

	
	$valorMetrica=0;
	$totalOrdinario=0;
	$totalGuardias=0;
	$rEventos=$con->obtenerFilas($consulta);
	while($fEvento=mysql_fetch_row($rEventos))
	{
		$tipoHorario=0;
		
		if($fEvento[2]==4)
			$tipoHorario=determinarHorarioB($fEvento[3]);
		else
			$tipoHorario=determinarHorarioA($fEvento[3]);
		
		if($fEvento[1]=="")
			$fEvento[1]=0;
			
		if($tipoHorario!=2)	
			$totalOrdinario+=$fEvento[1];
		else
			$totalGuardias+=$fEvento[1];

	}
	$valorMetrica=$totalOrdinario;
	if($considerarGuardia)
		$valorMetrica+=$totalGuardias;
	return $valorMetrica;
	
}

function obtenerMetricaEventosCambioAsignacionJuez($idJuez,$fInicial,$fFinal,$considerarGuardia=false)
{
	global $con;
	
	$arrEventos=array();
	$consulta="SELECT a.horaInicioEvento,ta.numeroMetrica,a.idEdificio,a.fechaSolicitud FROM 7000_eventosAudiencia a,3005_bitacoraCambiosJuez j,_4_tablaDinamica ta 
				where j.idJuezCambio=".$idJuez." and j.idEventoAudiencia=a.idRegistroEvento
				and fechaSolicitud>='".$fInicial." 00:00:00' and fechaSolicitud<='".$fFinal." 23:59:59'  and a.situacion in(SELECT idSituacion FROM 7011_situacionEventosAudiencia 
				WHERE considerarDiponibilidad=1) and ta.id__4_tablaDinamica=a.tipoAudiencia and ta.numeroMetrica>0";

	
	$valorMetrica=0;
	$totalOrdinario=0;
	$totalGuardias=0;
	$rEventos=$con->obtenerFilas($consulta);
	while($fEvento=mysql_fetch_row($rEventos))
	{
		$tipoHorario=0;
		
		if($fEvento[2]==4)
			$tipoHorario=determinarHorarioB($fEvento[3]);
		else
			$tipoHorario=determinarHorarioA($fEvento[3]);
		
		if($fEvento[1]=="")
			$fEvento[1]=0;
			
		if($tipoHorario!=2)	
			$totalOrdinario+=$fEvento[1];
		else
			$totalGuardias+=$fEvento[1];

	}
	$valorMetrica=$totalOrdinario;
	if($considerarGuardia)
		$valorMetrica+=$totalGuardias;
	return $valorMetrica;
	
}

function existeDisponibilidadJuez($idJuez,$fecha,$horaInicial,$horaFinal,$idEventoIngnorar=-1,$fechaRegistro="",$ignorarJuezTramite=false)
{
	global $con;

	$resultado=verificarDisponibilidadJuez($idJuez,$fecha,$horaInicial,$horaFinal,$idEventoIngnorar);

	if(sizeof($resultado)>0)
		return false;
		

	if($fechaRegistro!="")	
	{

		$consulta="SELECT count(*) FROM _13_tablaDinamica WHERE '".$fechaRegistro."'>=fechaInicio AND '".
						$fechaRegistro."'<=fechaFinalizacion and usuarioJuez =".$idJuez;
		$nReg=$con->obtenerValor($consulta);
		
		if($nReg==0)
		{

			if(!$ignorarJuezTramite)
				return !esJuezTramite($idJuez,$fechaRegistro);
			return true;
		}
		
	}

	return true;
}

function existeDisponibilidadHorarioJuez($idJuez,$fecha,$horaInicial,$horaFinal,$idEventoIngnorar=-1,$fechaRegistro="")
{
	global $con;
	$resultado=verificarDisponibilidadJuez($idJuez,$fecha,$horaInicial,$horaFinal,$idEventoIngnorar,false);
	
	if(sizeof($resultado)>0)
		return false;
		
	return true;
}

function verificarDisponibilidadJuez($idJuez,$fechaAudiencia,$horaInicioAudiencia,$horaFinAudiencia,$idEvento,$considerarIncidencias=true)
{
	global $con;
	$arrEventos=array();	
	
	if($considerarIncidencias)
	{
		if(!esJuezDisponibleIncidencia($idJuez,$fechaAudiencia))
		{
			array_push($arrEventos,1);
			return $arrEventos;
		}
	}
	$qAux=generarConsultaIntervalos($horaInicioAudiencia,$horaFinAudiencia,"if(a.horaInicioReal is null,a.horaInicioEvento,a.horaInicioReal)",
			"if(a.horaTerminoReal is null,a.horaFinEvento,a.horaTerminoReal)",false,true);
	$consulta="SELECT a.idRegistroEvento, a.fechaEvento, a.horaInicioEvento, a.horaFinEvento, idSala, situacion, fechaAsignacion
			 FROM 7000_eventosAudiencia a, 7001_eventoAudienciaJuez j WHERE   j.idJuez = ".$idJuez." and a.idRegistroEvento = j.idRegistroEvento
			and a.fechaEvento='".$fechaAudiencia."'  and ".$qAux." and a.idRegistroEvento<>".$idEvento." and a.situacion in 
			(SELECT idSituacion FROM 7011_situacionEventosAudiencia WHERE considerarDiponibilidad=1)";

	$res=$con->obtenerFilas($consulta);
	while($fEvento=mysql_fetch_row($res))
	{
		array_push($arrEventos,$fEvento)	;
	}
	return $arrEventos;
}

function esJuezTramite($idJuez,$fecha)
{
	global $con;
	$consulta="SELECT COUNT(*) FROM _292_tablaDinamica WHERE idEstado=1 and nombreJueces=".$idJuez." AND '".$fecha."'>=fechaInicial AND '".$fecha."'<=fechaFinal";
	$nRegistros=$con->obtenerValor($consulta);
	if($nRegistros==0)
		return false;
	return true;
}

function esJuezDisponibleIncidencia($idJuez,$fecha)
{
	global $con;

	
	$consulta="SELECT COUNT(*) FROM _20_tablaDinamica WHERE idEstado=1 and usuarioJuez=".$idJuez." AND '".$fecha."'>=fechaInicial AND '".
			$fecha."'<=fechaFinal";

	if($con->existeCampo("tipoIntervalo","_20_tablaDinamica"))
	{
		$consulta.=" and tipoIntervalo=1";
	}
	
	$nRegistros=$con->obtenerValor($consulta);
	if($nRegistros==0)
		return true;
	return false;
}

function obtenerTotalTiempoJuez($idJuez,$fechaInicial,$fechaFinal)
{
	global $con;
	$arrDia=array();
	$totalTiempo=0;
	$resEventos=obtenerEventosJuez($idJuez,$fechaInicial,$fechaFinal);
	foreach($resEventos as $fila)
	{
		$diferencia=(0+strtotime($fila[3])-strtotime($fila[2]))/60;
		$totalTiempo+=$diferencia;
		if(!isset($arrDia[$fila[1]]))
			$arrDia[$fila[1]]=0;
		$arrDia[$fila[1]]+=$diferencia;
	}
	
	$resultado[0]=$totalTiempo;
	$resultado[1]=$arrDia;
	$resultado[2]=sizeof($resEventos);
	return $resultado;
}

function obtenerEventosJuez($idJuez,$fechaInicio,$fechaFin)
{
	global $con;

	$arrEventos=array();
	$consulta="SELECT * FROM 7000_eventosAudiencia a,7001_eventoAudienciaJuez j where j.idJuez=".$idJuez." and j.idRegistroEvento=a.idRegistroEvento
				and fechaEvento>='".$fechaInicio."' and fechaEvento<='".$fechaFin."' and situacion in(SELECT idSituacion FROM 7011_situacionEventosAudiencia WHERE considerarDiponibilidad=1)";
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row ($res))
	{
		array_push($arrEventos,$fila);	
	}
	return $arrEventos;
}

function obtenerFechaEventoAudiencia($objDatos)
{
	global $con;
	$fechaActual=date("Y-m-d");
	$fecha=date("Y-m-d",strtotime($objDatos->fechaInicialAgenda));
	return;	
	$dia=date("N",strtotime($fecha));
	$consulta="SELECT horaInicial,horaFinal FROM _17_horario WHERE idReferencia=".$objDatos->idUnidadGestion." and  dia=".$dia;


	if($objDatos->esSolicitudUgente)
	{
		if($fecha!=$fechaActual)
			$consulta="SELECT '00:00:00', '23:59:59'";
		else
			$consulta="SELECT '".date("H:i:s",strtotime($objDatos->fechaInicialAgenda))."', '23:59:59'";
	}

	$fHorario=$con->obtenerPrimeraFila($consulta);
	
	
	
	if(!$fHorario)
		return -1;
		
	$arrHorarios=array();
	
	$regHorario["hInicial"]=$fecha." ".date("H:i:s",strtotime($fHorario[0]));
	$regHorario["hFinal"]=$fecha." ".date("H:i:s",strtotime($fHorario[1]));
	$regHorario["tiempoMinutos"]=floor(obtenerDiferenciaMinutos($regHorario["hInicial"],$regHorario["hFinal"]));
	
	array_push($arrHorarios,$regHorario);	
		
	$consulta="SELECT horaInicioEvento,horaFinEvento FROM 7000_eventosAudiencia WHERE idSala=".$objDatos->idSala." AND fechaEvento='".$fecha."' ORDER BY horaInicioEvento";
	
	$resEvento=$con->obtenerFilas($consulta);			
	while($fEvento=mysql_fetch_row($resEvento))
	{
		$hInicioA=date("Y-m-d H:i:s",strtotime($fEvento[0]));
		$hFinA=date("Y-m-d H:i:s",strtotime($fEvento[1]));
		$arrAux=array();		
		for($pos=0;$pos<sizeof($arrHorarios);$pos++)
		{
			$horario=$arrHorarios[$pos];
			
			if(colisionaTiempo($hInicioA,$hFinA,$horario["hInicial"],$horario["hFinal"],true))
			{
				if(strtotime($hInicioA)<=strtotime($horario["hInicial"]))
				{
					
					if(strtotime($hFinA)<strtotime($horario["hFinal"]))
					{
						
						$nHorario=array();
						$nHorario["hInicial"]=date("Y-m-d H:i:s",strtotime("+0 minute",strtotime($hFinA)));
						$nHorario["hFinal"]=date("Y-m-d H:i:s",strtotime($horario["hFinal"]));
						$nHorario["tiempoMinutos"]=floor(obtenerDiferenciaMinutos($nHorario["hInicial"],$nHorario["hFinal"]));
						array_push($arrAux,$nHorario);
						
					}
						
				}
				else
				{
					$nHorario=array();
					$nHorario["hInicial"]=date("Y-m-d H:i:s",strtotime($horario["hInicial"]));
					$nHorario["hFinal"]=date("Y-m-d H:i:s",strtotime("-0 minute",strtotime($hInicioA)));
					$nHorario["tiempoMinutos"]=floor(obtenerDiferenciaMinutos($nHorario["hInicial"],$nHorario["hFinal"]));
					array_push($arrAux,$nHorario);
					
					if(strtotime($hFinA)<strtotime($horario["hFinal"]))
					{
						$nHorario=array();
						$nHorario["hInicial"]=date("Y-m-d H:i:s",strtotime("+0 minute",strtotime($hFinA)));
						$nHorario["hFinal"]=date("Y-m-d H:i:s",strtotime($horario["hFinal"]));
						$nHorario["tiempoMinutos"]=floor(obtenerDiferenciaMinutos($nHorario["hInicial"],$nHorario["hFinal"]));
						array_push($arrAux,$nHorario);
					}
					
					
				}
			}
			else
			{
				array_push($arrAux,$horario);	
			}
		}
		
		$arrHorarios=$arrAux;
		
	}
	
	$horaMinimaDia="";
	
	if($fecha==$fechaActual)
	{
		$horaMinimaDia=strtotime(date("H:i:s",strtotime($objDatos->fechaInicialAgenda)));
	}
	
	$arrHorariosBloquear=$objDatos->arrHorarioIgn;
	
	if(sizeof($arrHorariosBloquear)>0)
	{
		
		foreach($arrHorariosBloquear as $fEvento)	
		{
			$hInicioA=date("Y-m-d H:i:s",strtotime($fEvento["horaInicial"]));
			$hFinA=date("Y-m-d H:i:s",strtotime($fEvento["horaFinal"]));
			$arrAux=array();		
			for($pos=0;$pos<sizeof($arrHorarios);$pos++)
			{
				$horario=$arrHorarios[$pos];
				if(colisionaTiempo($hInicioA,$hFinA,$horario["hInicial"],$horario["hFinal"],true))
				{
					if(strtotime($hInicioA)<=strtotime($horario["hInicial"]))
					{
						
						if(strtotime($hFinA)<strtotime($horario["hFinal"]))
						{
							
							$nHorario=array();
							$nHorario["hInicial"]=date("Y-m-d H:i:s",strtotime("+0 minute",strtotime($hFinA)));
							$nHorario["hFinal"]=date("Y-m-d H:i:s",strtotime($horario["hFinal"]));
							$nHorario["tiempoMinutos"]=floor(obtenerDiferenciaMinutos($nHorario["hInicial"],$nHorario["hFinal"]));
							array_push($arrAux,$nHorario);
							
						}
							
					}
					else
					{
						$nHorario=array();
						$nHorario["hInicial"]=date("Y-m-d H:i:s",strtotime($horario["hInicial"]));
						$nHorario["hFinal"]=date("Y-m-d H:i:s",strtotime("-0 minute",strtotime($hInicioA)));
						$nHorario["tiempoMinutos"]=floor(obtenerDiferenciaMinutos($nHorario["hInicial"],$nHorario["hFinal"]));
						array_push($arrAux,$nHorario);
						
						if(strtotime($hFinA)<strtotime($horario["hFinal"]))
						{
							$nHorario=array();
							$nHorario["hInicial"]=date("Y-m-d H:i:s",strtotime("+0 minute",strtotime($hFinA)));
							$nHorario["hFinal"]=date("Y-m-d H:i:s",strtotime($horario["hFinal"]));
							$nHorario["tiempoMinutos"]=floor(obtenerDiferenciaMinutos($nHorario["hInicial"],$nHorario["hFinal"]));
							array_push($arrAux,$nHorario);
						}
						
						
					}
				}
				else
				{
					array_push($arrAux,$horario);	
				}
			}
			$arrHorarios=$arrAux;
		}
	}
	
	$agendaEvento=array();
	$agendaEvento["fechaEvento"]=$fecha;
	
	$horaInicial="";
	$horaFinal="";
	
	foreach($arrHorarios as $h)
	{
		$incrementoMinutos=0;
		if($h["hInicial"]!=date("Y-m-d H:i:s",strtotime($fecha." ".$fHorario[0])))
		{
			$incrementoMinutos=$objDatos->intervaloTiempoEvento;
		}
		
		
		if(($h["tiempoMinutos"]-$incrementoMinutos)>=$objDatos->duracionAudiencia)
		{
			$horaInicial=strtotime("+".$incrementoMinutos." minutes",strtotime($h["hInicial"]));
			
			
			if(($horaMinimaDia=="")||($horaInicial>=$horaMinimaDia))
				break;
			else
				$horaInicial="";
		}
		else
		{
			if($h["hFinal"]==date("Y-m-d H:i:s",strtotime($fecha." ".$fHorario[1])))
			{
				$horaInicial=strtotime("+".$incrementoMinutos." minutes",strtotime($h["hInicial"]));
				if(($horaMinimaDia=="")||($horaInicial>=$horaMinimaDia))
					break;	
				else
					$horaInicial="";
			}
		}
	}
	
	if($horaInicial=="")
		return -1;
	
	if($objDatos->fechaMaxima!=NULL)
	{
		if($horaInicial>strtotime($objDatos->fechaMaxima))	
		{
			return -1;	
		}
	}

	$horaFinal=strtotime("+".$objDatos->duracionAudiencia." minutes",$horaInicial);

	if($objDatos->permitirExcederHoraFinal==0)
	{
		if($horaFinal>strtotime(date("Y-m-d",$horaInicial)." ".$fHorario[1]))
		{
			return -1;
		}
	}
	else
	{
		if($horaInicial>strtotime(date("Y-m-d",$horaInicial)." ".$fHorario[1]))
		{
			return -1;
		}
	}
	$agendaEvento["horaInicial"]=date("Y-m-d H:i:s",$horaInicial);
	$agendaEvento["horaFinal"]=date("Y-m-d H:i:s",$horaFinal);	
	
	return $agendaEvento;	
	
}


function obtenerDatosEventoAudiencia($idEvento)
{
	global $con;
	$consulta="SELECT * FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$idEvento;
	$fDatosEvento=$con->obtenerPrimeraFilaAsoc($consulta);
	
	if($fDatosEvento["idEdificio"]=="")
		$fDatosEvento["idEdificio"]=-1;
	
	$consulta="SELECT nombreInmueble FROM _1_tablaDinamica WHERE id__1_tablaDinamica=".$fDatosEvento["idEdificio"];
	$nombreInmueble=$con->obtenerValor($consulta);
		
	if($fDatosEvento["idCentroGestion"]=="")
		$fDatosEvento["idCentroGestion"]=-1;	
		
	$consulta="SELECT nombreUnidad FROM _17_tablaDinamica WHERE id__17_tablaDinamica=".$fDatosEvento["idCentroGestion"];
	$nombreUnidadGestion=$con->obtenerValor($consulta);
	
	if($fDatosEvento["idSala"]=="")
		$fDatosEvento["idSala"]=-1;
	$consulta="SELECT nombreSala FROM _15_tablaDinamica WHERE id__15_tablaDinamica=".$fDatosEvento["idSala"];
	$nombreSala=$con->obtenerValor($consulta);
	
	$arrJueces="";
	$consulta="SELECT idRegistroEventoJuez,idJuez,tipoJuez,titulo FROM 7001_eventoAudienciaJuez WHERE idRegistroEvento=".$idEvento;
	$resJueces=$con->obtenerFilas($consulta);
	while($fJueces=mysql_fetch_row($resJueces))
	{
		$consulta="SELECT u.Nombre FROM 800_usuarios u WHERE u.idUsuario=".$fJueces[1];

		$nombreJuez=$con->obtenerValor($consulta);
		
		$oJueces='{"idRegistroEventoJuez":"'.$fJueces[0].'","idJuez":"'.$fJueces[1].'","tipoJuez":"'.$fJueces[2].'","titulo":"'.cv($fJueces[3]).'","nombreJuez":"'.cv($nombreJuez).'"}';
		if($arrJueces=="")
			$arrJueces=$oJueces;
		else
			$arrJueces.=",".$oJueces;
	}
	
	$consulta="SELECT tipoAudiencia FROM _4_tablaDinamica WHERE id__4_tablaDinamica=".$fDatosEvento["tipoAudiencia"];
	$tipoAudiencia=$con->obtenerValor($consulta);	
	
	
	$consulta="SELECT carpetaAdministrativa FROM 7007_contenidosCarpetaAdministrativa WHERE tipoContenido=3 
			AND idRegistroContenidoReferencia=".$idEvento;
	$carpetaAdministrativa=$con->obtenerValor($consulta);
	$cadObj='{"carpetaAdministrativa":"'.$carpetaAdministrativa.'","idTipoAudiencia":"'.$fDatosEvento["tipoAudiencia"].'","tipoAudiencia":"'.$tipoAudiencia.'","fechaEvento":"'.$fDatosEvento["fechaEvento"].'","horaInicio":"'.
			$fDatosEvento["horaInicioEvento"].'","horaFin":"'.$fDatosEvento["horaFinEvento"].
			'","horaInicioReal":"'.$fDatosEvento["horaInicioReal"].'","horaFinReal":"'.$fDatosEvento["horaTerminoReal"].'","urlMultimedia":"'.$fDatosEvento["urlMultimedia"].
			'","idEdificio":"'.$fDatosEvento["idEdificio"].'","edificio":"'.cv($nombreInmueble).'","idUnidadGestion":"'.$fDatosEvento["idCentroGestion"].
			'","unidadGestion":"'.cv($nombreUnidadGestion).'","idSala":"'.$fDatosEvento["idSala"].'","sala":"'.cv($nombreSala).'","jueces":['.$arrJueces.']}';
	
	
	$objEvento=json_decode($cadObj);
	return $objEvento;
}



function generarFolioCarpetaAdministrativa($idFormulario,$idRegistro,$idUnidadGestion)
{
	global $con;
	
	$anio=date("Y");
	
	$tipoDelito="";
	$query="SELECT tipoAudiencia,idActividad,folioCarpetaInvestigacion,carpetaRemitida,ctrlSolicitud,idSolicitud,materiaDestino,corregidoAlgoritmo FROM _".$idFormulario.
			"_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	
	$fRegistroSolicitud=$con->obtenerPrimeraFila($query);
	if($fRegistroSolicitud[3]=="N/E")
		$fRegistroSolicitud[3]="";
	$tAudiencia=$fRegistroSolicitud[0];
	$idActividad=$fRegistroSolicitud[1];
	$carpetaInvestigacion=$fRegistroSolicitud[2];
	$query="SELECT claveFolioCarpetas,claveUnidad FROM _17_tablaDinamica WHERE id__17_tablaDinamica=".$idUnidadGestion;
	$fRegistroUnidad=$con->obtenerPrimeraFila($query);
	$cvAdscripcion=$fRegistroUnidad[1];
	$query="SELECT carpetaAdministrativa FROM 7006_carpetasAdministrativas WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro;
	$carpetaAdministrativa=$con->obtenerValor($query);
	$agregarCarpeta=true;
	
	
	if(($tAudiencia!=91)&&($fRegistroSolicitud["7"]!=488))
	{
		if($carpetaAdministrativa=="")
		{
			$query="SELECT carpetaAdministrativa FROM 3011_solicitudRecibidasPGJ s,_46_tablaDinamica fs WHERE s.ctrlSolicitud='".$fRegistroSolicitud[4]."' AND s.idSolicitud='".$fRegistroSolicitud[5].
						"' and s.idFormulario=46 AND id__46_tablaDinamica=s.idRegistro and  fs.carpetaAdministrativa is not null";
						
						
			$carpetaAdministrativa=$con->obtenerValor($query);				
		}
		
	
		if($carpetaAdministrativa=="")
		{
			
			
			$esMateriaAdolescentes=$fRegistroSolicitud[6]==2;
				
			
			$llaveCarpeta=generarLlaveCarpetaInvestigacion($carpetaInvestigacion);
			$tipoAudiencia=$tAudiencia;
			switch($tipoAudiencia)
			{
				case 9:
				case 56:
				case 32:
				case 97:
				case 69:
				case 35:
					if($llaveCarpeta!="")
					{
						$query="SELECT carpetaAdministrativa FROM 7006_carpetasAdministrativas WHERE llaveCarpetaInvestigacion='".
							cv($llaveCarpeta)."' and tipoCarpetaAdministrativa=1 and unidadGestion ='012' order by fechaCreacion DESC";
	
						$carpetaAdministrativa=$con->obtenerValor($query);
						if($carpetaAdministrativa=="")
						{
							$llaveCarpeta="";		
						}
					}
					
				break;
			}
						
			if(($llaveCarpeta!="")&&($carpetaAdministrativa==""))
			{
				if(!$esMateriaAdolescentes)
				{
					
					$query="SELECT c.carpetaAdministrativa FROM 7006_carpetasAdministrativas c,_46_tablaDinamica s WHERE 
								s.carpetaAdministrativa=c.carpetaAdministrativa and llaveCarpetaInvestigacion='".
								cv($llaveCarpeta)."' and s.tipoAudiencia in (1,102,114,136,26,91,52) and tipoCarpetaAdministrativa=1 
								and unidadGestion not in ('012','301','302') order by c.fechaCreacion DESC";
				}
				else
				{
					$query="SELECT c.carpetaAdministrativa FROM 7006_carpetasAdministrativas c,_46_tablaDinamica s WHERE 
								s.carpetaAdministrativa=c.carpetaAdministrativa and llaveCarpetaInvestigacion='".
								cv($llaveCarpeta)."' and s.tipoAudiencia in (1,102,114,136,26,91,52) and tipoCarpetaAdministrativa=1 
								and unidadGestion in ('301') order by c.fechaCreacion DESC";
				}
				$carpetaAdministrativa=$con->obtenerValor($query);
				
			}
			
			
						
		}
	}
	if($carpetaAdministrativa=="")
	{
		$carpetaAdministrativa=obtenerSiguienteCarpetaAdministrativa($idUnidadGestion,$anio,1,$idFormulario,$idRegistro);
	}
	else
		$agregarCarpeta=false;
	$x=0;
	$consulta=array();
	$consulta[$x]="begin";
	$x++;
	if($agregarCarpeta)
	{
		$consulta[$x]="INSERT INTO 7006_carpetasAdministrativas(carpetaAdministrativa,fechaCreacion,responsableCreacion,idFormulario,idRegistro,
						unidadGestion,etapaProcesalActual,idActividad,tipoCarpetaAdministrativa,carpetaInvestigacion,llaveCarpetaInvestigacion,
						unidadGestionOriginal,carpetaAdministrativaBase) 
						VALUES('".$carpetaAdministrativa."','".date("Y-m-d H:i:s")."',".$_SESSION["idUsr"].",".$idFormulario.",'".$idRegistro."','".
						$cvAdscripcion."',1,".$idActividad.",1,(SELECT UPPER('".$carpetaInvestigacion."')),'".
						cv(generarLlaveCarpetaInvestigacion($carpetaInvestigacion))."','".$cvAdscripcion."',".
						($fRegistroSolicitud[3]==""?"NULL":"'".$fRegistroSolicitud[3]."'").")";
		$x++;
	}
	$consulta[$x]="update _".$idFormulario."_tablaDinamica set carpetaAdministrativa='".$carpetaAdministrativa."' where id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$x++;
	
	if(isset($_SESSION["actualizaReferenciaSerie"])&& $_SESSION["actualizaReferenciaSerie"]==1)
	{
		$consulta[$x]="update 7004_referenciasSeries set fechaReferencia='".date("Y-m-d H:i:s")."' where idRegistro=1";
		$x++;
		$_SESSION["actualizaReferenciaSerie"]=0;
	}

	$consulta[$x]="commit";
	$x++;

	if($con->ejecutarBloque($consulta))
	{
		$query="SELECT idDocumento FROM 9074_documentosRegistrosProceso WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro;
		$rDocumentos=$con->obtenerFilas($query);
		while($fDocumento=mysql_fetch_row($rDocumentos))
		{
			registrarDocumentoCarpetaAdministrativa($carpetaAdministrativa,$fDocumento[0],$idFormulario,$idRegistro);	
		}

		$query="SELECT idRegistroEvento FROM 7000_eventosAudiencia WHERE idFormulario=".$idFormulario." AND idRegistroSolicitud=".$idRegistro;
		$rAudiencias=$con->obtenerFilas($query);
		while($fAudiencia=mysql_fetch_row($rAudiencias))
		{
			registrarAudienciaCarpetaAdministrativa($idFormulario,$idRegistro,$fAudiencia[0]);
		}
		
	}
	
	return false;
	
}

function obtenerReferenciaFechaSolicitud($idFormulario,$idRegistro)
{
	global $con;
	
	$fecha=date("Y-m-d H:i:s");
	switch($idFormulario)
	{
		case 46:
			$consulta="SELECT fechaCreacion FROM _46_tablaDinamica WHERE id__46_tablaDinamica=".$idRegistro;
			$fecha=$con->obtenerValor($consulta);
		break;
		default:
		break;
		
	}
	return "'".$fecha."'";
}

function obtenerReferenciaFechaMaxima($idFormulario,$idRegistro)
{
	global $con;
	return "''";
}

function esSolicitudUrgente($idFormulario,$idRegistro)
{
	global $con;
	
	$solicitudUrgente=0;
	switch($idFormulario)
	{
		case 46:
			$consulta="SELECT tipoProgramacionAudiencia FROM _46_tablaDinamica WHERE id__46_tablaDinamica=".$idRegistro;
			$tipoProgramacionAudiencia=$con->obtenerValor($consulta);
			if($tipoProgramacionAudiencia==2)
				$solicitudUrgente=1;
		break;
		default:
		
	}
	
	return $solicitudUrgente;
}

function esDiaHabil($fecha)
{
	global $con;
	$fechaTime=strtotime($fecha);
	$dia=date("w",$fechaTime)*1;
	if(($dia>=1)&&($dia<=5))
	{
		$consulta="SELECT COUNT(*) FROM 7022_diasNOHabiles WHERE '".date("Y-m-d",$fechaTime)."'>=fechaInicio AND 
					'".date("Y-m-d",$fechaTime)."'<=fechaTermino AND situacion=1";
		$nReg=$con->obtenerValor($consulta);
		if($nReg>0)
			return 0;
		return 1;
	}
	return 0;
	
	
}

function enviarNotificacionMAJO($idEvento)
{
	global $servidorPruebas;
	global $con;
	global $pruebasPGJ;
	global $tipoMateria;
	global $pruebasPGJ;
	
	$consulta="SELECT idEdificio,idCentroGestion,idSala FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$idEvento;

	$fDatosEvento=$con->obtenerPrimeraFila($consulta);
	
	
	$consulta="SELECT perfilSala FROM _15_tablaDinamica WHERE id__15_tablaDinamica=".$fDatosEvento[2];
	$perfilSala=$con->obtenerValor($consulta);
	
	if($tipoMateria=="P")
	{
		
		if(($servidorPruebas)|| ($pruebasPGJ))
		{
			$consulta="SELECT idFormulario,idRegistroSolicitud FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$idEvento;
	
			$fDatosEvento=$con->obtenerPrimeraFila($consulta);
			//@notificarEventoAudienciaSIAJOPCabina($idEvento);
			@reportarAudienciaPGJ($fDatosEvento[0],$fDatosEvento[1]);
			return true;
		}
	}
	
	if($tipoMateria=="PT")
	{
		@enviarNotificacionMailConfirmacion($idEvento);
		return true;
	}
	

	if(($fDatosEvento[0]==0)||($perfilSala==3)||($perfilSala==4))
		return true;
	
	$consulta="SELECT ip1,ip2,ip3,ip4,funcionNotificacion FROM _55_tablaDinamica WHERE salasVinculadas=".$fDatosEvento[2]." AND idReferencia=".$fDatosEvento[1];
	$fDatosConexion=$con->obtenerPrimeraFila($consulta);
	
	if((!$fDatosConexion)||(($fDatosConexion[0]=="")||($fDatosConexion[1]=="")||($fDatosConexion[2]=="")||($fDatosConexion[3]=="")||($fDatosConexion[4]=="")))
	{
		$consulta="SELECT ip1,ip2,ip3,ip4,funcionNotificacion FROM _16_tablaDinamica WHERE idReferencia=".$fDatosEvento[0]	;
		$fDatosConexion=$con->obtenerPrimeraFila($consulta);
	}
	
	if(!$fDatosConexion)
		return true;
	$dirIP=$fDatosConexion[0].".".$fDatosConexion[1].".".$fDatosConexion[2].".".$fDatosConexion[3];
	$cache=NULL;
	$cObj='{"direccionIP":"'.$dirIP.'","idEvento":"'.$idEvento.'"}';
	$objFuncion=json_decode($cObj);
//varDump($$fDatosConexion[4]);
	$resultado=@removerComillasLimite(resolverExpresionCalculoPHP($fDatosConexion[4],$objFuncion,$cache));
	
	if($tipoMateria=="P")
		@notificarSeguimientoMediaticoAudiencia($idEvento);
	
	
}

function formatearEventoAudienciaRenderer($idEventoAudiencia)
{
	global $con;
	
	$datosEventos=obtenerDatosEventoAudiencia($idEventoAudiencia);
	
	
	$fechaEvento=utf8_encode(convertirFechaLetra($datosEventos->fechaEvento,true));
	$duracionEstimada=obtenerDiferenciaMinutos($datosEventos->horaInicio,$datosEventos->horaFin)." minutos";
	
	$lblHorario="";
	
	$fechaHoraInicio=strtotime($datosEventos->horaInicio);
	$fechaHoraFin=strtotime($datosEventos->horaFin);
	$comp='';
	if(date("Y-m-d",$fechaHoraInicio)!=date("Y-m-d",$fechaHoraFin))
	{
		$comp=' del '.utf8_encode(convertirFechaLetra(date("Y-m-d",$fechaHoraInicio),true));
	}
	
	$lblJueces='';            
            
	foreach($datosEventos->jueces as $j)
	{
		$lblJueces.=$j->nombreJuez.' ('.$j->titulo.')<br>';
	}
	
	$lblHorario='De las '.date("h:i",$fechaHoraInicio).' hrs.'.$comp.' a las '.date("h:i",$fechaHoraFin).' hrs. del '.utf8_encode(convertirFechaLetra(date("Y-m-d",$fechaHoraFin),true));
	
	$tabla='	<table width="800px">';
	$tabla.='	<tr height="23"><td align="left" colspan="4" ><br><span class="SeparadorSeccion" style="width:800px">Datos de la audiencia</span><br></td></tr>';
	$tabla.='	<tr height="23"><td align="left"><span class="TSJDF_Etiqueta">Fecha de la audiencia:</span></td><td colspan="3" align="left"><span class="TSJDF_Control">'.$fechaEvento.'</span></td></tr>';
	$tabla.='	<tr height="23"><td align="left"><span class="TSJDF_Etiqueta">Tipo de audiencia:</span></td><td colspan="3" align="left"><span class="TSJDF_Control">'.$datosEventos->tipoAudiencia.'</span></td></tr>';
	$tabla.='	<tr height="23"><td align="left"><span class="TSJDF_Etiqueta">Duraci&oacute;n estimada:</span></td><td colspan="3" align="left"><span class="TSJDF_Control">'.$duracionEstimada.'</span></td></tr>';
	$tabla.='	<tr height="23"><td align="left"><span class="TSJDF_Etiqueta">Horario:</span></td><td colspan="3" align="left"><span class="TSJDF_Control">'.$lblHorario.'</span></td></tr>';
	$tabla.='	<tr height="23"><td align="left"><span class="TSJDF_Etiqueta">Sala asignada:</span></td><td colspan="3" align="left"><span class="TSJDF_Control">'.$datosEventos->sala.'</span></td></tr>';
	$tabla.='	<tr height="23"><td align="left"><span class="TSJDF_Etiqueta">Centro de Gesti&oacute;n:</span></td><td colspan="3" align="left"><span class="TSJDF_Control">'.$datosEventos->unidadGestion.'</span></td></tr>';
	$tabla.='	<tr height="23"><td align="left"><span class="TSJDF_Etiqueta">Edificio sede:</span></td><td colspan="3" align="left"><span class="TSJDF_Control">'.$datosEventos->edificio.'</span></td></tr>';
	$tabla.='	<tr height="23"><td align="left" style="vertical-align:top; padding-top:4px"><span class="TSJDF_Etiqueta">'.((sizeof($datosEventos->jueces)==1)?'Juez asignado:':'Jueces asignados:').'</span></td><td colspan="3" align="left"><span class="TSJDF_Control">'.$lblJueces.'</span></td></tr>';
	$tabla.='	<tr height="23"><td align="left" width="200"></td><td width="200"></td><td width="200"></td><td width="200" align="left"><span class="TSJDF_Control"></span></td></tr>';
	$tabla.='	</table>';


	
	return '"'.bE($tabla).'"';
	
}

function obtenerCarpetaAdministrativaProceso($idFormulario,$idRegistro)
{
	global $con;	

	$carpetaAdministrativa="";
	$nombreTablaBase="_".$idFormulario."_tablaDinamica";
	$campoLlave="id_".$nombreTablaBase;
	if($idFormulario==0)
	{
		return "";
	}
	else
	{
		if($idFormulario<0)
		{
			$consulta="SELECT nombreTabla,campoLlave FROM 900_formulariosVirtuales WHERE idFormulario=".abs($idFormulario);

			$fFormularioVirtual=$con->obtenerPrimeraFila($consulta);

			$nombreTablaBase=$fFormularioVirtual[0];
			$campoLlave=$fFormularioVirtual[1];
			if($nombreTablaBase=="")
				return "";
		}
	}
	
	
	switch($idFormulario)
	{
		case 320:
		case 316:
		case 428:
		case 385:
		case 491:
			$consulta="select carpetaAdministrativa from ".$nombreTablaBase." where ".$campoLlave."=".$idRegistro;
			$carpetaAdministrativa=$con->obtenerValor($consulta);
		break;
		
		
		
		default:
			if($con->existeCampo("carpetaJudicialDeclinaCompetencia",$nombreTablaBase))
			{
				$consulta="select carpetaJudicialDeclinaCompetencia from ".$nombreTablaBase." where ".$campoLlave."=".$idRegistro;
				$carpetaAdministrativa=$con->obtenerValor($consulta);
			}
			else
				if($con->existeCampo("carpetaAmparo",$nombreTablaBase))
				{
					$consulta="select carpetaAmparo from ".$nombreTablaBase." where ".$campoLlave."=".$idRegistro;
					$carpetaAdministrativa=$con->obtenerValor($consulta);
				}
				else
					if($con->existeCampo("carpetaApelacion",$nombreTablaBase))
					{
						$consulta="select carpetaApelacion from ".$nombreTablaBase." where ".$campoLlave."=".$idRegistro;
						$carpetaAdministrativa=$con->obtenerValor($consulta);
					}
					else
						if($con->existeCampo("carpetaEjecucion",$nombreTablaBase))
						{
							$consulta="select carpetaEjecucion from ".$nombreTablaBase." where ".$campoLlave."=".$idRegistro;
							$carpetaAdministrativa=$con->obtenerValor($consulta);
						}
						else
							if($con->existeCampo("carpetaExhorto",$nombreTablaBase))
							{
								$consulta="select carpetaExhorto from ".$nombreTablaBase." where ".$campoLlave."=".$idRegistro;
								$carpetaAdministrativa=$con->obtenerValor($consulta);
							}
							else
								if($con->existeCampo("expediente",$nombreTablaBase))
								{
									$consulta="select expediente from ".$nombreTablaBase." where ".$campoLlave."=".$idRegistro;
									$carpetaAdministrativa=$con->obtenerValor($consulta);
								}
								else
									if($con->existeCampo("carpetaAdministrativa",$nombreTablaBase))
									{
										$consulta="select carpetaAdministrativa from ".$nombreTablaBase." where ".$campoLlave."=".$idRegistro;
		
										$carpetaAdministrativa=$con->obtenerValor($consulta);
									}
									else
										if($con->existeCampo("idCarpetaAdministrativa",$nombreTablaBase))
										{
											$consulta="select idCarpetaAdministrativa from ".$nombreTablaBase." where ".$campoLlave."=".$idRegistro;
											$idCarpetaAdministrativa=$con->obtenerValor($consulta);
											if($idCarpetaAdministrativa=="")
												$idCarpetaAdministrativa=-1;
											$consulta="SELECT carpetaAdministrativa FROM 7006_carpetasAdministrativas WHERE idCarpeta=".$idCarpetaAdministrativa;
											
											$carpetaAdministrativa=$con->obtenerValor($consulta);
										
										}

										else
											if($con->existeCampo("idCarpeta",$nombreTablaBase))
											{
												$consulta="select idCarpeta from ".$nombreTablaBase." where ".$campoLlave."=".$idRegistro;
												$idCarpetaAdministrativa=$con->obtenerValor($consulta);
												if($idCarpetaAdministrativa=="")
													$idCarpetaAdministrativa=-1;
												$consulta="SELECT carpetaAdministrativa FROM 7006_carpetasAdministrativas WHERE idCarpeta=".$idCarpetaAdministrativa;
												$carpetaAdministrativa=$con->obtenerValor($consulta);
											
											}
											else
												if($con->existeCampo("carpetaAsignada",$nombreTablaBase))
												{
													$consulta="select carpetaAsignada from ".$nombreTablaBase." where ".$campoLlave."=".$idRegistro;
													$carpetaAdministrativa=$con->obtenerValor($consulta);
												
												}

		break;
	}
	
	
	
	
	if($carpetaAdministrativa=="")
	{
		$consulta="SELECT carpetaAdministrativa FROM 7007_contenidosCarpetaAdministrativa WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro;
		$carpetaAdministrativa=$con->obtenerValor($consulta);
	}
	
	return $carpetaAdministrativa;
}

function obtenerIdUnidadGestionProceso($idFormulario,$idRegistro)
{
	global $con;
	$carpetaAdministrativa=obtenerCarpetaAdministrativaProceso($idFormulario,$idRegistro);
	
	$consulta="SELECT unidadGestion FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$carpetaAdministrativa."'";
	$unidadGestion=$con->obtenerValor($consulta);
	$consulta="SELECT id__17_tablaDinamica FROM _17_tablaDinamica WHERE claveUnidad='".$unidadGestion."'";
	$idUnidadGestion=$con->obtenerValor($consulta);
	return $idUnidadGestion;
	
	
}

function obtenerEtapaProcesalCarpetaAdministrativa($carpetaAdminsitrativa,$idCarpetaAdministrativa=-1)
{
	global $con;
	$consulta="SELECT etapaProcesalActual FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$carpetaAdminsitrativa."'";
	if($idCarpetaAdministrativa!=-1)
		$consulta.=" and idCarpeta=".$idCarpetaAdministrativa;
	return $con->obtenerValor($consulta);
	
	
}

function obtenerTitularAuxiliarAsignadoSolicitudInicial($idFormulario,$idRegistro,$actorDestinatario)
{
	global $con;
	
	$arrDestinatario=array();
	$consulta="SELECT auxiliarSala FROM _105_tablaDinamica WHERE idReferencia=".$idRegistro;
	$idUsuarioDestinatario=$con->obtenerValor($consulta);
	if($idUsuarioDestinatario=="")
		$idUsuarioDestinatario=-1;
	$rolActor=obtenerTituloRol($actorDestinatario);
	
	$nombreUsuario=obtenerNombreUsuario($idUsuarioDestinatario)." (".$rolActor.")";
	$o='{"idUsuarioDestinatario":"'.$idUsuarioDestinatario.'","nombreUsuarioDestinatario":"'.$nombreUsuario.'"}';
	$o=json_decode($o);
	array_push($arrDestinatario,$o);
		
	$nombreUsuario=obtenerNombreUsuario(1)." (".$rolActor.")";
	$o='{"idUsuarioDestinatario":"1","nombreUsuarioDestinatario":"'.$nombreUsuario.'"}';
	$o=json_decode($o);
	array_push($arrDestinatario,$o);
	
	return $arrDestinatario;
}

function obtenerTitularPuesto($idFormulario,$idRegistro,$actorDestinatario)
{
	global $con;
	global $tipoMateria;
	$carpetaAdministrativa="";
	$nombreTablaBase="_".$idFormulario."_tablaDinamica";
	
	$continuar=true;
	$unidadGestion="";

	if($tipoMateria=="P")
	{
		if($idFormulario==96)
		{
			$consulta="SELECT unidadGestion,idReferencia FROM _360_tablaDinamica WHERE idReferencia=".$idRegistro;
			$fDatosRemision=$con->obtenerPrimeraFila($consulta);
			if($fDatosRemision)
			{
				$unidadGestion=$fDatosRemision[0];
				$continuar=false;
			}
		}
		
		
		if($continuar)
		{
			$carpetaAdministrativa=obtenerCarpetaAdministrativaProceso($idFormulario,$idRegistro);
			
			
			if($carpetaAdministrativa!="")
			{
				$consulta="SELECT unidadGestion FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$carpetaAdministrativa."'";
				$unidadGestion=$con->obtenerValor($consulta);
			}
			else
			{
				$consulta="SELECT codigoInstitucion FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
				$unidadGestion=$con->obtenerValor($consulta);
			}
		}
	}
	else
	{
		$consulta="SELECT codigoInstitucion FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
		$unidadGestion=$con->obtenerValor($consulta);
	}

	$rolActor=obtenerTituloRol($actorDestinatario);

	$arrDestinatario=array();
	$consulta="SELECT ad.idUsuario FROM 801_adscripcion ad,807_usuariosVSRoles r WHERE r.idUsuario=ad.idUsuario AND 
				r.codigoRol='".$actorDestinatario."' AND ad.Institucion='".$unidadGestion."'";

	
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		
		$nombreUsuario=obtenerNombreUsuario($fila[0])." (".$rolActor.")";
		$o='{"idUsuarioDestinatario":"'.$fila[0].'","nombreUsuarioDestinatario":"'.$nombreUsuario.'"}';
		$o=json_decode($o);
		array_push($arrDestinatario,$o);
	}
	$nombreUsuario=obtenerNombreUsuario(1)." (".$rolActor.")";
	$o='{"idUsuarioDestinatario":"1","nombreUsuarioDestinatario":"'.$nombreUsuario.'"}';
	$o=json_decode($o);
	array_push($arrDestinatario,$o);

	return $arrDestinatario;
}

function obtenerJuezControlSolicitudInicial($idFormulario,$idRegistro,$actorDestinatario)
{
	global $con;
	
	/*$carpetaAdministrativa="";
	$nombreTablaBase="_".$idFormulario."_tablaDinamica";
	
	if($con->existeCampo("carpetaAdministrativa",$nombreTablaBase))
	{
		$consulta="select carpetaAdministrativa from ".$nombreTablaBase." where id_".$nombreTablaBase."=".$idRegistro;
		$carpetaAdministrativa=$con->obtenerValor($consulta);
	}
	
	if($carpetaAdministrativa=="")
	{
		$consulta="SELECT carpetaAdministrativa FROM 7007_contenidosCarpetaAdministrativa WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro;
		$carpetaAdministrativa=$con->obtenerValor($consulta);
	}
	
	$consulta="SELECT unidadGestion FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$carpetaAdministrativa."'";
	$unidadGestion=$con->obtenerValor($consulta);
	
	$rolActor=obtenerTituloRol($actorDestinatario);
	
	$arrDestinatario=array();
	$consulta="SELECT ad.idUsuario FROM 801_adscripcion ad,807_usuariosVSRoles r WHERE r.idUsuario=ad.idUsuario AND 
				r.codigoRol='".$actorDestinatario."' AND ad.codigoUnidad='".$unidadGestion."'";
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		
		$nombreUsuario=obtenerNombreUsuario($fila[0])." (".$rolActor.")";
		$o='{"idUsuarioDestinatario":"'.$fila[0].'","nombreUsuarioDestinatario":"'.$nombreUsuario.'"}';
		$o=json_decode($o);
		array_push($arrDestinatario,$o);
	}*/
	$arrDestinatario=array();
	$rolActor=obtenerTituloRol($actorDestinatario);
	$nombreUsuario=obtenerNombreUsuario(1)." (".$rolActor.")";
	$o='{"idUsuarioDestinatario":"1","nombreUsuarioDestinatario":"'.$nombreUsuario.'"}';
	$o=json_decode($o);
	array_push($arrDestinatario,$o);
	
	return $arrDestinatario;
}

function obtenerUsuarioResponsableProceso($idFormulario,$idRegistro,$actorDestinatario)
{
	global $con;
	
	$consulta="SELECT responsable FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	
	$idUsuario=$con->obtenerValor($consulta);
	$arrDestinatario=array();
	
	$rolActor=obtenerTituloRol($actorDestinatario);	
	$nombreUsuario=obtenerNombreUsuario($idUsuario)." (".$rolActor.")";
	$o='{"idUsuarioDestinatario":"'.$idUsuario.'","nombreUsuarioDestinatario":"'.$nombreUsuario.'"}';
	$o=json_decode($o);
	array_push($arrDestinatario,$o);
		
	$rolActor=obtenerTituloRol($actorDestinatario);
	$nombreUsuario=obtenerNombreUsuario(1)." (".$rolActor.")";
	$o='{"idUsuarioDestinatario":"1","nombreUsuarioDestinatario":"'.$nombreUsuario.'"}';
	$o=json_decode($o);
	array_push($arrDestinatario,$o);
	
	return $arrDestinatario;
}

function obtenerJuezControlAudiencia($idFormulario,$idRegistro,$actorDestinatario)
{
	global $con;
	$consulta="SELECT idEvento FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$idEvento=$con->obtenerValor($consulta);
	
	$consulta="SELECT idJuez,titulo FROM 7001_eventoAudienciaJuez WHERE idRegistroEvento=".$idEvento;
	$rJueces=$con->obtenerFilas($consulta);
	while($fJuez=mysql_fetch_row($rJueces))
	{
		$idUsuario=$fJuez[0];
		$arrDestinatario=array();

		$rolActor=$fJuez[1];	
		$nombreUsuario=obtenerNombreUsuario($idUsuario)." (".$rolActor.")";
		$o='{"idUsuarioDestinatario":"'.$idUsuario.'","nombreUsuarioDestinatario":"'.$nombreUsuario.'"}';
		$o=json_decode($o);
		array_push($arrDestinatario,$o);

		
		$nombreUsuario=obtenerNombreUsuario(1)." (".$rolActor.")";
		$o='{"idUsuarioDestinatario":"1","nombreUsuarioDestinatario":"'.$nombreUsuario.'"}';
		$o=json_decode($o);
		array_push($arrDestinatario,$o);
	}
	return $arrDestinatario;
	
}

function obtenerJuezControlAudienciaSolicitud($idFormulario,$idRegistro,$actorDestinatario)
{
	global $con;
	$consulta="SELECT idRegistroEvento FROM 7000_eventosAudiencia WHERE idFormulario=".$idFormulario." AND idRegistroSolicitud=".$idRegistro;
	$idEvento=$con->obtenerValor($consulta);
	
	$consulta="SELECT idJuez FROM 7001_eventoAudienciaJuez WHERE idRegistroEvento=".$idEvento;
	


	$arrDestinatario=array();
	
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		$rolActor=obtenerTituloRol($actorDestinatario);	
		$nombreUsuario=obtenerNombreUsuario($fila[0])." (".$rolActor.")";
		$o='{"idUsuarioDestinatario":"'.$fila[0].'","nombreUsuarioDestinatario":"'.$nombreUsuario.'"}';
		$o=json_decode($o);
		array_push($arrDestinatario,$o);
		
	}
	
	
	$rolActor=obtenerTituloRol($actorDestinatario);
	$nombreUsuario=obtenerNombreUsuario(1)." (".$rolActor.")";
	$o='{"idUsuarioDestinatario":"1","nombreUsuarioDestinatario":"'.$nombreUsuario.'"}';
	$o=json_decode($o);
	array_push($arrDestinatario,$o);
	
	return $arrDestinatario;
	
}

function obtenerTitularDefensoriaOficio($idFormulario,$idRegistro,$actorDestinatario)
{
	global $con;
	$arrDestinatario=array();
	/*
	
	$rolActor=obtenerTituloRol($actorDestinatario);	
	$nombreUsuario=obtenerNombreUsuario($idUsuario)." (".$rolActor.")";
	$o='{"idUsuarioDestinatario":"'.$idUsuario.'","nombreUsuarioDestinatario":"'.$nombreUsuario.'"}';
	$o=json_decode($o);
	array_push($arrDestinatario,$o);*/
		
	$rolActor=obtenerTituloRol($actorDestinatario);
	$nombreUsuario=obtenerNombreUsuario(1)." (".$rolActor.")";
	$o='{"idUsuarioDestinatario":"1","nombreUsuarioDestinatario":"'.$nombreUsuario.'"}';
	$o=json_decode($o);
	array_push($arrDestinatario,$o);
	
	return $arrDestinatario;
	
}

//Funcviones filtro juez

function obtenerJuecesUnidadesGestion($idFormulario,$idRegistro,$fecha,$horaInicio,$horaFin,$idUnidadGestion)
{
	global $con;
	$arrJueces=array();
	$unidadGestion=$idUnidadGestion;
	

	
	
	$consulta="SELECT usuarioJuez FROM _26_tablaDinamica WHERE idReferencia=".$unidadGestion;
	$listaJueces=$con->obtenerListaValores($consulta);

	if($listaJueces=="")
		$listaJueces=-1;
		
	
	
	return "'".$listaJueces."'";
}

function obtenerJuecesDiferenteUnidadesGestion($idFormulario,$idRegistro)
{
	global $con;
	
	$unidadGestion=obtenerIdUnidadGestionProceso($idFormulario,$idRegistro);
	$consulta="SELECT usuarioJuez FROM _26_tablaDinamica WHERE idReferencia<>".$unidadGestion;
	$lista=$con->obtenerListaValores($consulta);
	return "'".$lista."'";
}

function obtenerJuezDesconoceCausa($idFormulario,$idRegistro)
{
	global $con;
	$carpetaAdministrativa=obtenerCarpetaAdministrativaProceso($idFormulario,$idRegistro);
	$consulta="SELECT idRegistroContenidoReferencia FROM 7007_contenidosCarpetaAdministrativa WHERE carpetaAdministrativa='".$carpetaAdministrativa."' 
				AND tipoContenido=3";	
	$listaAudiencias=$con->obtenerListaValores($consulta);
	if($listaAudiencias=="")
		$listaAudiencias=-1;
	$consulta="SELECT idJuez FROM 7001_eventoAudienciaJuez WHERE idRegistroEvento IN(".$listaAudiencias.")";
	$listaJueces=$con->obtenerListaValores($consulta);
	if($listaJueces=="")
		$listaJueces=-1;
		
	$consulta="SELECT usuarioJuez FROM _26_tablaDinamica WHERE usuarioJuez not in (".$listaJueces.")";
	$lista=$con->obtenerListaValores($consulta);
	return "'".$lista."'";
	
	
}

function obtenerJuezCausaActual($idFormulario,$idRegistro)
{
	global $con;
	$carpetaAdministrativa=obtenerCarpetaAdministrativaProceso($idFormulario,$idRegistro);
	$consulta="SELECT idRegistroContenidoReferencia FROM 7007_contenidosCarpetaAdministrativa WHERE carpetaAdministrativa='".$carpetaAdministrativa."' 
				AND tipoContenido=3 order by idRegistroContenidoReferencia desc";
				
	
	$listaAudiencias=$con->obtenerValor($consulta);
	if($listaAudiencias=="")
		$listaAudiencias=-1;
	$consulta="SELECT idJuez FROM 7001_eventoAudienciaJuez WHERE idRegistroEvento IN(".$listaAudiencias.")";
	
	$listaJueces=$con->obtenerListaValores($consulta);
	if($listaJueces=="")
		$listaJueces=-1;
		
	
	return "'".$listaJueces."'";
	
	
}

function noEsAudicenciaJuicioOral($idFormulario,$idRegistro)
{
	global $con;
	$consulta="SELECT * FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$fRegistro=$con->obtenerPrimeraFilaAsoc($consulta);	
	if($fRegistro["tipoAudiencia"]!=28)
	{
		return 1;
	}
	return 0;
	
}

function esAudicenciaJuicioOral($idFormulario,$idRegistro)
{
	global $con;
	$consulta="SELECT * FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$fRegistro=$con->obtenerPrimeraFilaAsoc($consulta);
	if($fRegistro["tipoAudiencia"]==28)
	{
		return 1;
	}
	return 0;
}

function esAudienciaCambioEtapaProcesal($idFormulario,$idRegistro)
{
	global $con;
	$consulta="SELECT * FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$fRegistro=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$consulta="SELECT categoriaAudiencia FROM _4_tablaDinamica WHERE id__4_tablaDinamica=".$fRegistro["tipoAudiencia"];
	$categoriaAudiencia=$con->obtenerValor($consulta);	
	
	switch($categoriaAudiencia)	
	{
		case 1:
		case 3:
			return 1;
		break;
		case 2:
			if($idFormulario==46)
				return 1;
			return 0;
		break;
	}
	return 0;
}

function esAudienciaMismaEtapaProcesal($idFormulario,$idRegistro)
{
	global $con;
	$consulta="SELECT * FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	$fRegistro=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$consulta="SELECT categoriaAudiencia FROM _4_tablaDinamica WHERE id__4_tablaDinamica=".$fRegistro["tipoAudiencia"];
	$categoriaAudiencia=$con->obtenerValor($consulta);	
	
	switch($categoriaAudiencia)	
	{
		case 1:
		case 3:
			return 0;
		break;
		case 2:
			if($idFormulario==46)
				return 0;
			return 1;
		break;
	}
	return 1;
}

function controladorMAJO_V1($direccionIP,$idEvento)
{
	global $con;
	
	$consulta="SELECT idEdificio,idFormulario,idRegistroSolicitud FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$idEvento;
	$fEvento=$con->obtenerPrimeraFila($consulta);
	$edificio=$fEvento[0];
	
	$comando='C:\\Apache24\\php55\\php.exe -f C:\\Apache24\\htdocs\\modulosEspeciales_SGJP\\majoWS.php '.$idEvento." ".$edificio;
	
	
	$salida="";
	$valorRetorno="";
	$salida=exec($comando,$salida,$valorRetorno);
	@notificarEventoAudienciaSIAJOPCabina($idEvento);  
	@reportarAudienciaPGJ($fEvento[1],$fEvento[2]);
	return true;
}



function generarFechaAudienciaSolicitudAmparo($oDatosParametros)
{
	global $con;
	$nInteraciones=0;	
	
	$idFormulario=$oDatosParametros["idFormulario"];
	$idRegistro=$oDatosParametros["idRegistro"];
	$idReferencia=$oDatosParametros["idReferencia"];
	$tipoAudiencia=$oDatosParametros["tipoAudiencia"];
	
	$oDatosAudiencia=array();
	$oDatosAudiencia["idRegistroEvento"]=$oDatosParametros["oDatosAudiencia"]["idRegistroEvento"];
	$oDatosAudiencia["idEdificio"]="";
	$oDatosAudiencia["listaEdificiosIgnorar"]=-1;
	$oDatosAudiencia["idUnidadGestion"]="";
	$oDatosAudiencia["listaUnidadesGestionIgnorar"]=-1;
	$oDatosAudiencia["idSala"]="";
	$oDatosAudiencia["listaSalasIgnorar"]=-1;
	$oDatosAudiencia["fecha"]="";
	$oDatosAudiencia["horaInicio"]="";
	$oDatosAudiencia["horaFin"]="";
	$oDatosAudiencia["jueces"]="";
	
	$notificarMAJO=$oDatosParametros["notificarMAJO"];
	$idRegistroConfiguracionAgenda=-1;
	
	$listaSalas="";
	$arrSalas=array();

	$cadObj='{';
	foreach($oDatosParametros as $campo=>$valor)
	{
		if((gettype($valor)!='array')&&(gettype($valor)!='object'))
		{
			if($cadObj=='{')
				$cadObj.='"'.$campo.'":"'.$valor.'"';
			else
				$cadObj.=',"'.$campo.'":"'.$valor.'"';
		}
	}
	$cadObj.='}';
	
	$cache=NULL;
	
	$objFuncion=json_decode($cadObj);
	
	if(isset($oDatosParametros["oDatosAudiencia"])&&($oDatosParametros["oDatosAudiencia"]["idSala"]!="")&&($oDatosParametros["oDatosAudiencia"]["idSala"]!="-1"))
	{
		$listaSalas=$oDatosParametros["oDatosAudiencia"]["idSala"];
	}
	else
	{
		$consulta="SELECT id__15_tablaDinamica FROM _15_tablaDinamica";
	
		if(isset($oDatosParametros["oDatosAudiencia"])&&($oDatosParametros["oDatosAudiencia"]["idEdificio"]!="")&&($oDatosParametros["oDatosAudiencia"]["idEdificio"]!="-1"))
		{
			$consulta.=" where idReferencia=".$oDatosParametros["oDatosAudiencia"]["idEdificio"];
		}
		
		$resSalas=$con->obtenerFilas($consulta);
		while($fSalas=mysql_fetch_row($resSalas))
		{
			$arrSalas[$fSalas[0]]=1;
		}
		
		
		foreach($arrSalas as $idSala=>$resto)
		{
			if($listaSalas=="")
				$listaSalas=$idSala;
			else
				$listaSalas.=",".$idSala;
		}	
	
	}
	
	$listaEdificios="";
	
	if(isset($oDatosParametros["oDatosAudiencia"])&&($oDatosParametros["oDatosAudiencia"]["idEdificio"]!="")&&($oDatosParametros["oDatosAudiencia"]["idEdificio"]!="-1"))
	{
		$listaEdificios=$oDatosParametros["oDatosAudiencia"]["idEdificio"];
	}
	else
	{
		$consulta="select distinct e.id__1_tablaDinamica from _15_tablaDinamica s,_1_tablaDinamica e where  
					id__15_tablaDinamica in (".$listaSalas.") and e.id__1_tablaDinamica=s.idReferencia and e.idEstado=2";
		
		$listaEdificios=$con->obtenerListaValores($consulta);
		if($listaEdificios=="")
		{
			return -10;
		}
	
		$arrEdificios=explode(",",$listaEdificios);
		
		
		$listaEdificios="";
		if(sizeof($arrEdificios)>0)
			$listaEdificios=implode(",",$arrEdificios);
	
		if($listaEdificios=="")
		{
			return -10;  //No existen edificios disponibles
		}
	}
	$fechaBasePeriodo=$oDatosParametros["fechaBasePeriodo"]." 00:00:01";

	$fechaInicialPeriodo=date("Y-m-d",strtotime("- 0 days",strtotime($oDatosParametros["fechaBasePeriodo"])))." 00:00:00";	
	$fechaFinalPeriodo=date("Y-m-d ",strtotime("+ 15 days",strtotime($fechaInicialPeriodo)))." 23:59:59";		
	
	//$fechaInicialPeriodo=date("2016-06-16 00:00:01",strtotime($oDatosParametros["fechaBasePeriodo"]));	
	$fechaInicialPeriodo="2016-06-16";
	$fechaFinalPeriodo=date("Y-12-31 23:59:59",strtotime($oDatosParametros["fechaBasePeriodo"]));	
	
	$fechaMaximaPermitida=$oDatosParametros["fechaMaximaAudiencia"];
	if($fechaMaximaPermitida!=NULL)
	{
		$fechaMaximaPermitida=strtotime($oDatosParametros["fechaMaximaAudiencia"]);
	}
	
	
	$edificioAsignado=false;
	$determinacionFechaSiguiente=false;
	$diasIncremento=3;
	

	$nCiclos=0;
	
	while(!$edificioAsignado)	
	{
		if(isset($oDatosParametros["oDatosAudiencia"])&&($oDatosParametros["oDatosAudiencia"]["idEdificio"]!="")&&($oDatosParametros["oDatosAudiencia"]["idEdificio"]!="-1"))
		{
			$oDatosAudiencia["idEdificio"]=$oDatosParametros["oDatosAudiencia"]["idEdificio"];
			if($oDatosAudiencia["listaEdificiosIgnorar"]!="-1")
			{
				$arrEdificiosIgnorar=explode(",",$oDatosAudiencia["listaEdificiosIgnorar"]);
				if(existeValor($arrEdificiosIgnorar,$oDatosAudiencia["idEdificio"]))
				{
					$oDatosAudiencia["idEdificio"]=-1;
				}
			}
			
			
			
		}
		else
		{
			switch($oDatosParametros["criterioBalanceoEdificio"])//Asignacion Unidad gestion
			{
				case "1"://No. horas asignadas
					$oDatosAudiencia["idEdificio"]=obtenerEdificioNumeroHoras($tipoAudiencia,$fechaInicialPeriodo,$fechaFinalPeriodo,$listaEdificios,$oDatosAudiencia["listaEdificiosIgnorar"]);
				break;
				case "2"://No. eventos asignados
					$oDatosAudiencia["idEdificio"]=obtenerEdificioNumeroEventos($tipoAudiencia,$fechaInicialPeriodo,$fechaFinalPeriodo,$listaEdificios,$oDatosAudiencia["listaEdificiosIgnorar"]);
				break;
				case "3"://Secuencial				
					$oDatosAudiencia["idEdificio"]=obtenerSiguienteEntidad(4,$tipoAudiencia,$listaEdificios,$ignEdificio);
				break;
				
			}
					
		}
			
				
		if($oDatosAudiencia["idEdificio"]==-1)
		{
			break;
		}
		$oDatosAudiencia["listaEdificiosIgnorar"].=",".$oDatosAudiencia["idEdificio"];	
		
		$listaUnidades=-1;			
		
		if(isset($oDatosParametros["oDatosAudiencia"])&&($oDatosParametros["oDatosAudiencia"]["idUnidadGestion"]!="")&&($oDatosParametros["oDatosAudiencia"]["idUnidadGestion"]!="-1"))
		{
			$listaUnidades=$oDatosParametros["oDatosAudiencia"]["idUnidadGestion"];
		}
		else
		{
			$consulta="SELECT DISTINCT idReferencia FROM _55_tablaDinamica WHERE salasVinculadas IN(".$listaSalas.")";
			$listaUnidades=$con->obtenerListaValores($consulta);
			if($listaUnidades=="")
			{
				$listaUnidades=-1;
			}
			
			$consulta="SELECT id__17_tablaDinamica FROM _17_tablaDinamica WHERE idReferencia=".$oDatosAudiencia["idEdificio"]." and id__17_tablaDinamica in(".$listaUnidades.") and idEstado=2";
			$listaUnidades=$con->obtenerListaValores($consulta);
					
			$arrUnidades=explode(",",$listaUnidades);
			
			$consulta="SELECT funcionModificadora FROM _27_gridFuncionesSeleccionUnidadGestion WHERE idReferencia=".$idRegistroConfiguracionAgenda;
			$resFuncion=$con->obtenerFilas($consulta);
			while($fFuncion=mysql_fetch_row($resFuncion))
			{
				
				$arrAux=array();
				
				$listaUnidadesFuncion=removerComillasLimite(resolverExpresionCalculoPHP($fFuncion[0],$objFuncion,$cache));
				$arrUnidadesFuncion=explode(",",$listaUnidadesFuncion);
				foreach($arrUnidades as $idUnidad)
				{
					if(existeValor($arrUnidadesFuncion,$idUnidad))
					{
						array_push($arrAux,$idUnidad);
					}
					
				}
				$arrUnidades=$arrAux;
			}
			$listaUnidades="";
			if(sizeof($arrUnidades)>0)
				$listaUnidades=implode(",",$arrUnidades);
			if($listaUnidades=="")
			{
				continue;
			}
		}		
		
		$unidadControlAsignada=false;
		
		while(!$unidadControlAsignada)
		{
		
			if(isset($oDatosParametros["oDatosAudiencia"])&&($oDatosParametros["oDatosAudiencia"]["idUnidadGestion"]!="")&&($oDatosParametros["oDatosAudiencia"]["idUnidadGestion"]!="-1"))
			{
				$oDatosAudiencia["idUnidadGestion"]=$oDatosParametros["oDatosAudiencia"]["idUnidadGestion"];
				if($oDatosAudiencia["listaUnidadesGestionIgnorar"]!="-1")
				{
					$arrUnidadesGestionIgnorar=explode(",",$oDatosAudiencia["listaUnidadesGestionIgnorar"]);
					if(existeValor($arrUnidadesGestionIgnorar,$oDatosAudiencia["idUnidadGestion"]))
					{
						return -20;
					}
				}
			}
			else
			{
				switch($oDatosParametros["criterioBalanceoUnidadGestion"])//Asignacion Unidad gestion
				{
					case "1"://No. horas asignadas
						$oDatosAudiencia["idUnidadGestion"]=obtenerUnidadGestionNumeroHoras($tipoAudiencia,$fechaInicialPeriodo,$fechaFinalPeriodo,$listaUnidades,$oDatosAudiencia["listaUnidadesGestionIgnorar"]);
					break;
					case "2"://No. eventos asignados
						$oDatosAudiencia["idUnidadGestion"]=obtenerUnidadGestionNumeroEventos($tipoAudiencia,$fechaInicialPeriodo,$fechaFinalPeriodo,$listaUnidades,$oDatosAudiencia["listaUnidadesGestionIgnorar"]);
					break;
					case "3"://Secuencial
						$oDatosAudiencia["idUnidadGestion"]=obtenerSiguienteEntidad(1,$tipoAudiencia,$listaUnidades,$oDatosAudiencia["listaUnidadesGestionIgnorar"]);
					break;
					
				}
			}
			
			
			
			$oDatosAudiencia["listaUnidadesGestionIgnorar"].=",".$oDatosAudiencia["idUnidadGestion"];
			
			if($oDatosAudiencia["idUnidadGestion"]!=-1)
			{
				
				if($oDatosParametros["nivelAsignacion"]>=2)
				{
					
					$fechaInicialBusqueda=strtotime($oDatosParametros["fechaMinimaAudiencia"]);
					if(isset($oDatosParametros["oDatosAudiencia"])&&($oDatosParametros["oDatosAudiencia"]["idSala"]!="")&&($oDatosParametros["oDatosAudiencia"]["idSala"]!="-1"))
					{
						$listaSalasUnidadGestion=$oDatosParametros["oDatosAudiencia"]["idSala"];
					}
					else
					{
						$consulta="SELECT salasVinculadas FROM _55_tablaDinamica WHERE idReferencia IN(".$oDatosAudiencia["idUnidadGestion"].") AND salasVinculadas in (".$listaSalas.")";
						
						
						$listaSalasUnidadGestion=$con->obtenerListaValores($consulta);
						if($listaSalasUnidadGestion=="")
							continue;
					}
						
						
						
						
					$salaAsignada=false;
					while(!$salaAsignada)
					{
						
						$oDatosAudiencia["jueces"]=array();						
						if(isset($oDatosParametros["oDatosAudiencia"])&&($oDatosParametros["oDatosAudiencia"]["idSala"]!="")&&($oDatosParametros["oDatosAudiencia"]["idSala"]!="-1"))
						{
							$oDatosAudiencia["idSala"]=$oDatosParametros["oDatosAudiencia"]["idSala"];
							if($oDatosAudiencia["listaSalasIgnorar"]!="-1")
							{
								$arrSalasIgnorar=explode(",",$oDatosAudiencia["listaSalasIgnorar"]);
								if(existeValor($arrSalasIgnorar,$oDatosAudiencia["idSala"]))
								{
									$fechaInicialBusqueda=strtotime("+".$diasIncremento." days",strtotime(date("Y-m-d",$fechaInicialBusqueda)));	
									if(($fechaMaximaPermitida!=NULL) &&($fechaInicialBusqueda>$fechaMaximaPermitida))
									{
										return -30;
									}
									else
									{
										$oDatosAudiencia["listaSalasIgnorar"]=-1;
										continue;
									}
								}
							}
						}
						else
						{
							
							switch($oDatosParametros["criterioBalanceoSala"])//Asignacion Sala
							{
								case "1":
									$oDatosAudiencia["idSala"]=obtenerSalaAsignacionNumeroHoras($listaSalasUnidadGestion,$tipoAudiencia,$fechaInicialPeriodo,$fechaFinalPeriodo,$oDatosAudiencia["listaSalasIgnorar"]);
								break;
								case "2":
									$oDatosAudiencia["idSala"]=obtenerSalaAsignacionNumeroEventos($listaSalasUnidadGestion,$tipoAudiencia,$fechaInicialPeriodo,$fechaFinalPeriodo,$oDatosAudiencia["listaSalasIgnorar"]);			
								break;
								case "3":
									$oDatosAudiencia["idSala"]=obtenerSiguienteEntidad(2,$listaSalasUnidadGestion,$tipoAudiencia,$oDatosAudiencia["listaSalasIgnorar"]);
								break;		
							}
						}
						
						
						$nInteraciones++;
						
						$oDatosAudiencia["listaSalasIgnorar"].=",".$oDatosAudiencia["idSala"];
						if($oDatosAudiencia["idSala"]==-1)
						{
							
							$fechaInicialBusqueda=strtotime("+".$diasIncremento." days",strtotime(date("Y-m-d",$fechaInicialBusqueda)));
								
							if(($fechaMaximaPermitida!=NULL) &&($fechaInicialBusqueda>$fechaMaximaPermitida))
							{
								$salaAsignada=true;
							}
							else
							{
								$oDatosAudiencia["listaSalasIgnorar"]=-1;
								continue;
							}
						}
						else
						{
							
							if($oDatosParametros["nivelAsignacion"]>=3)
							{
								$consulta="SELECT perfilSala FROM _15_tablaDinamica WHERE id__15_tablaDinamica=".$oDatosAudiencia["idSala"];
								$idPerfilSala=$con->obtenerValor($consulta);
								$consulta="SELECT * FROM _8_tablaDinamica WHERE id__8_tablaDinamica=".$idPerfilSala;
								$fDatosPerfil=$con->obtenerPrimeraFilaAsoc($consulta);
								$intervaloTiempoEvento=0;
								$permitirExcederHoraFinal=0;
								if($fDatosPerfil)
								{
									$intervaloTiempoEvento=$fDatosPerfil["intervaloTiempo"];
									$permitirExcederHoraFinal=$oDatosParametros["esSolicitudUgente"]?1:$fDatosPerfil["permiteExceder"];
								}
								if($intervaloTiempoEvento=="")
									$intervaloTiempoEvento=0;
									
								$fechaAsignada=false;
								
								$diasBusqueda=0;
								$obtenerSiguienteFecha=true;
								$arrHorarioIgn=array();
								while(!$fechaAsignada)
								{
									$agendaEvento=-1;
									$considerarFechaAudiencia=true;
									$obtenerSiguienteFecha=true;
									if($oDatosParametros["considerarDiaHabil"])
									{
										$cadObj='{"fecha":"'.date("Y-m-d",$fechaInicialBusqueda).'"}';
										$arrAux=array();
										$objFuncion=json_decode($cadObj);
										$esDiaHabil=removerComillasLimite(resolverExpresionCalculoPHP($oDatosParametros["funcionDiaHabil"],$objFuncion,$cache));
										$considerarFechaAudiencia=($esDiaHabil==1);
										
									}
									
									
									if($considerarFechaAudiencia)
									{
										$arrHorarioIgn=array();
										$horaAsignada=false;
										while(!$horaAsignada)
										{
											$cadObjParametrosEvento='{"fechaSolicitud":"'.$oDatosParametros["fechaSolicitud"].'","fechaMinimaAudiencia":"'.$oDatosParametros["fechaMinimaAudiencia"].
																	'","idSala":"'.$oDatosAudiencia["idSala"].'","fechaInicialAgenda":"'.date("Y-m-d H:i:s",$fechaInicialBusqueda).'","duracionAudiencia":"'.$oDatosParametros["duracionAudiencia"]
																	.'","intervaloTiempoEvento":"'.$intervaloTiempoEvento.'","fechaMaxima":"","permitirExcederHoraFinal":"'.$permitirExcederHoraFinal.
																	'","arrHorarioIgn":[],"idUnidadGestion":"'.$oDatosAudiencia["idUnidadGestion"].'","esSolicitudUgente":"'.($oDatosParametros["esSolicitudUgente"]?1:0).'"}';
															
											$oParametrosEvento=json_decode($cadObjParametrosEvento);
											$oParametrosEvento->arrHorarioIgn=$arrHorarioIgn;		
											
											
											
											if($fechaMaximaPermitida!=NULL)
												$oParametrosEvento->fechaMaxima=date("Y-m-d H:i:s",$fechaMaximaPermitida);	
											else
												$oParametrosEvento->fechaMaxima=NULL;
											///$agendaEvento=obtenerFechaEventoAudienciaV3($oParametrosEvento);eliminado
											return;
											if($agendaEvento!=-1)
											{												
												$oHorario=array();
												$oHorario["horaInicial"]=$agendaEvento["horaInicial"];
												$oHorario["horaFinal"]=$agendaEvento["horaFinal"];
												
												array_push($arrHorarioIgn,$oHorario);
												
												$oDatosAudiencia["fecha"]=$agendaEvento["fechaEvento"];
												$oDatosAudiencia["horaInicio"]=$agendaEvento["horaInicial"];
												$oDatosAudiencia["horaFin"]=$agendaEvento["horaFinal"];
												
												$juecesAsignados=true;
												$listaJuecesIgn=-1;
												/*for($x=0;$x<sizeof($oDatosParametros["juecesRequeridos"]);$x++)
												{
													
													$oDatosParametros["juecesRequeridos"][$x]["idUsuario"]=asignarJuezAudiencia($oDatosAudiencia,$oDatosParametros,$oDatosParametros["juecesRequeridos"][$x]["tipoJuez"],$listaJuecesIgn,$fechaInicialPeriodo,$fechaFinalPeriodo);
													
													
													if($oDatosParametros["juecesRequeridos"][$x]["idUsuario"]==-1)
													{
														$juecesAsignados=false;
														break;
													}
													else
													{
														$listaJuecesIgn.=",".$oDatosParametros["juecesRequeridos"][$x]["idUsuario"];
													}
												}*/
											
												if($juecesAsignados)
												{
													foreach($oDatosParametros["juecesRequeridos"] as $j)									
													{
														array_push($oDatosAudiencia["jueces"],$j);
													}
													$obtenerSiguienteFecha=false;
													$horaAsignada=true;
													$fechaAsignada=true;
													$salaAsignada=true;
													$unidadControlAsignada=true;
													$edificioAsignado=true;
												}
											}
											else
												$horaAsignada=true;
											
										}
									}
									
									
									
									/*date("Y-m-d",$fechaInicialBusqueda)."<br>";
									$nCiclos++;
									if($nCiclos>30)
									{
										return;
									}*/
									
									

								
									if($obtenerSiguienteFecha)
									{
											

										$arrHorarioIgn=array();
										$diasBusqueda++;
										
										if($diasBusqueda>$diasIncremento)
										{
											$fechaInicialBusqueda=strtotime("-".($diasBusqueda-1)." days",strtotime(date("Y-m-d",$fechaInicialBusqueda)));
											$fechaAsignada=true;
										}
										else
										{
											
											$fechaInicialBusqueda=strtotime("+1 days",strtotime(date("Y-m-d",$fechaInicialBusqueda)));	
											
											
											if(($fechaMaximaPermitida!=NULL) &&($fechaInicialBusqueda>$fechaMaximaPermitida))
											{
												$fechaAsignada=true;
												$salaAsignada=true;
											}
										}
									}
								}
							}
							else
							{
								$salaAsignada=true;
								$unidadControlAsignada=true;
								$edificioAsignado=true;
							}
						}
					}
				}
				else
				{
					$unidadControlAsignada=true;
					$edificioAsignado=true;
				}
			}
			else
				$unidadControlAsignada=true;
		}
	}	
	
	
	if($oDatosAudiencia["idEdificio"]==-1)
		return -10;
	
	if($oDatosAudiencia["idUnidadGestion"]==-1)
		return -20;	
	
	if($oDatosAudiencia["idSala"]==-1)
		return -30;		
	
	if($oDatosAudiencia["fecha"]==-1)
		return -40;	
		
	if(sizeof($oDatosAudiencia["jueces"])==0)
		return -50;	
		
	return $oDatosAudiencia;	
}


function obtenerTitularPuestoEjecucion($idFormulario,$idRegistro,$actorDestinatario)
{
	global $con;
	
	$consulta="SELECT carpetaEjecucion FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
	
	$carpetaAdministrativa=$con->obtenerValor($consulta);//obtenerCarpetaAdministrativaProceso($idFormulario,$idRegistro);

	$consulta="SELECT unidadGestion FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$carpetaAdministrativa."'";
	$unidadGestion=$con->obtenerValor($consulta);
	$rolActor=obtenerTituloRol($actorDestinatario);
	
	$arrDestinatario=array();
	$consulta="SELECT ad.idUsuario FROM 801_adscripcion ad,807_usuariosVSRoles r WHERE r.idUsuario=ad.idUsuario AND 
				r.codigoRol='".$actorDestinatario."' AND ad.Institucion='".$unidadGestion."'";
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		
		$nombreUsuario=obtenerNombreUsuario($fila[0])." (".$rolActor.")";
		$o='{"idUsuarioDestinatario":"'.$fila[0].'","nombreUsuarioDestinatario":"'.$nombreUsuario.'"}';
		$o=json_decode($o);
		array_push($arrDestinatario,$o);
	}
	$nombreUsuario=obtenerNombreUsuario(1)." (".$rolActor.")";
	$o='{"idUsuarioDestinatario":"1","nombreUsuarioDestinatario":"'.$nombreUsuario.'"}';
	$o=json_decode($o);
	array_push($arrDestinatario,$o);
	return $arrDestinatario;
}

function obtenerJuezTitularDocumentoAmparo($idFormulario,$idRegistro)
{
	global $con;
	$arrDestinatario=array();
	$rolActor=obtenerTituloRol("108_0");	
	
	$consulta="SELECT idJuez FROM _363_tablaDinamica WHERE id__363_tablaDinamica=".$idRegistro;
	$idJuez=$con->obtenerValor($consulta);
	
	$nombreUsuario=obtenerNombreUsuario($idJuez)." (".$rolActor.")";
	$o='{"idUsuarioDestinatario":"'.$idJuez.'","nombreUsuarioDestinatario":"'.$nombreUsuario.'"}';
	$o=json_decode($o);
	array_push($arrDestinatario,$o);
	
	$nombreUsuario=obtenerNombreUsuario(1)." (".$rolActor.")";
	$o='{"idUsuarioDestinatario":"1","nombreUsuarioDestinatario":"'.$nombreUsuario.'"}';
	$o=json_decode($o);
	array_push($arrDestinatario,$o);
	return $arrDestinatario;
}


function existeDisponibilidadSala($idEvento,$idSala,$fechaAudiencia,$horaInicio,$horaFin)
{
	global $con;	
	global $tipoMateria;
	$arrEventos=array();
	$qAux=generarConsultaIntervalos($horaInicio,$horaFin,"if(a.horaInicioReal is null,a.horaInicioEvento,a.horaInicioReal)",
			"if(a.horaTerminoReal is null,a.horaFinEvento,a.horaTerminoReal)",false,true);
	
	
	$consulta="SELECT a.idRegistroEvento, a.fechaEvento, a.horaInicioEvento, a.horaFinEvento, idSala, situacion, fechaAsignacion
			 FROM 7000_eventosAudiencia a WHERE a.fechaEvento='".$fechaAudiencia."'   and ".$qAux." and idSala=".$idSala."
			 and a.idRegistroEvento<>".$idEvento." and a.situacion in 
			(SELECT idSituacion FROM 7011_situacionEventosAudiencia WHERE considerarDiponibilidad=1)";
	
	
	
	if($idSala==156)
	{
		$consulta="SELECT a.idRegistroEvento, a.fechaEvento, a.horaInicioEvento, a.horaFinEvento, idSala, situacion, fechaAsignacion
			 FROM 7000_eventosAudiencia a WHERE a.fechaEvento='".$fechaAudiencia."'   and ".$qAux." and idSala in(156,3021)
			 and a.idRegistroEvento<>".$idEvento." and a.situacion in 
			(SELECT idSituacion FROM 7011_situacionEventosAudiencia WHERE considerarDiponibilidad=1)";
	}

	$res=$con->obtenerFilas($consulta);
	while($fEvento=mysql_fetch_row($res))
	{
		array_push($arrEventos,$fEvento);
	}
	
	$consulta="SELECT idCentroGestion FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$idEvento;
	$idUnidadGestion=$con->obtenerValor($consulta);
	if($idUnidadGestion=="")
		$idUnidadGestion=-1;
	$consulta="SELECT idPadre FROM _25_chkUnidadesAplica WHERE idOpcion=".$idUnidadGestion;	
	$listaIncidencias=$con->obtenerValor($consulta);
	if($listaIncidencias=="")
		$listaIncidencias=-1;
	
	$consulta="SELECT fechaInicial,horaInicial,fechaFinal,horaFinal,id__25_tablaDinamica,tipoPeriodo FROM _25_tablaDinamica t,_25_Salas s 
				WHERE s.idReferencia=t.id__25_tablaDinamica AND '".$fechaAudiencia."'>=t.fechaInicial AND '".$fechaAudiencia.
				"'<=t.fechaFinal AND s.nombreSala=".$idSala." and idEstado=2 and aplicaTodasUnidades=1
				union
				SELECT fechaInicial,horaInicial,fechaFinal,horaFinal,id__25_tablaDinamica,tipoPeriodo FROM _25_tablaDinamica t,_25_Salas s 
				WHERE s.idReferencia=t.id__25_tablaDinamica AND '".$fechaAudiencia."'>=t.fechaInicial AND '".$fechaAudiencia.
				"'<=t.fechaFinal AND s.nombreSala=".$idSala." and idEstado=2 and aplicaTodasUnidades=0 and id__25_tablaDinamica in(".
				$listaIncidencias.")";
				
	$res=$con->obtenerFilas($consulta);
	while($fIncidencia=mysql_fetch_row($res))
	{
		
		$horaInicial="00:00:00";
		$horaFinal="23:59:59";
		if($fIncidencia[1]=="")
			$fIncidencia[1]=$horaInicial;
		
		if($fIncidencia[3]=="")
			$fIncidencia[3]=$horaFinal;
			
		
		
		if($fIncidencia[5]==2)
		{
			
			if($fIncidencia[0]==$fechaAudiencia)
			{
				$horaInicial=$fIncidencia[1];
			}
			
			
			if($fIncidencia[2]==$fechaAudiencia)
			{
				$horaFinal=$fIncidencia[3];
			}
			
		}
		else
		{
			$horaInicial=$fIncidencia[1];
			$horaFinal=$fIncidencia[3];
		}
		
		$fechaInicio=$fechaAudiencia." ".$horaInicial;
		$fechaFinal=$fechaAudiencia." ".$horaFinal;
		
		
		if(colisionaTiempo($horaInicio,$horaFin,$fechaInicio,$fechaFinal,false))
			array_push($arrEventos,$fIncidencia);
	}		
	
	if(($tipoMateria=="C")&&($idSala==70)) //Eliminar Sala
	{
		if(date("w",strtotime($fechaAudiencia))==4)
		{
			$fRegIncidencia[0]=$fechaAudiencia." 00:00";
			$fRegIncidencia[1]=$fechaAudiencia." 23:59";
			
			array_push($arrEventos,$fRegIncidencia);
		}
	}

	$arrEventosSala=obtenerAudienciasProgramadasSede($idSala,$fechaAudiencia,$fechaAudiencia,$idEvento);
	
	foreach($arrEventosSala as $f)
	{
		if(colisionaTiempo($f[0],$f[1],$horaInicio,$horaFin,false))
		{
			array_push($arrEventos,$f);
		}
	}
	

	return (sizeof($arrEventos)>0)?false:true;
	
	
}


function obtenerTitularPuestoTribunalEnjuciamiento($idFormulario,$idRegistro,$actorDestinatario)
{
	global $con;
	
	$consulta="SELECT carpetaTribunalEnjuiciamiento FROM _320_tablaDinamica WHERE id__320_tablaDinamica=".$idRegistro;
	$carpetaTribunal=$con->obtenerValor($consulta);

	$consulta="SELECT unidadGestion FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$carpetaTribunal."'";

	$unidadGestion=$con->obtenerValor($consulta);
	$rolActor=obtenerTituloRol($actorDestinatario);
	
	$arrDestinatario=array();
	$consulta="SELECT ad.idUsuario FROM 801_adscripcion ad,807_usuariosVSRoles r WHERE r.idUsuario=ad.idUsuario AND 
				r.codigoRol='".$actorDestinatario."' AND ad.Institucion='".$unidadGestion."'";
	

	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		
		$nombreUsuario=obtenerNombreUsuario($fila[0])." (".$rolActor.")";
		$o='{"idUsuarioDestinatario":"'.$fila[0].'","nombreUsuarioDestinatario":"'.$nombreUsuario.'"}';
		$o=json_decode($o);
		array_push($arrDestinatario,$o);
	}
	$nombreUsuario=obtenerNombreUsuario(1)." (".$rolActor.")";
	$o='{"idUsuarioDestinatario":"1","nombreUsuarioDestinatario":"'.$nombreUsuario.'"}';
	$o=json_decode($o);
	array_push($arrDestinatario,$o);
	return $arrDestinatario;
}

function obtenerTitularPuestoCarpeta($carpetaJudicial,$idCarpetaJudial,$actorDestinatario)
{
	global $con;
	global $tipoMateria;
	$carpetaAdministrativa="";
	$unidadGestion="";

	$consulta="SELECT unidadGestion FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$carpetaJudicial."' AND idCarpeta=".$idCarpetaJudial;
	$unidadGestion=$con->obtenerValor($consulta);
	
	$rolActor=obtenerTituloRol($actorDestinatario);
	
	$arrDestinatario=array();
	$consulta="SELECT ad.idUsuario FROM 801_adscripcion ad,807_usuariosVSRoles r WHERE r.idUsuario=ad.idUsuario AND 
				r.codigoRol='".$actorDestinatario."' AND ad.Institucion='".$unidadGestion."'";
	
	
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		
		$nombreUsuario=obtenerNombreUsuario($fila[0])." (".$rolActor.")";
		$o='{"idUsuarioDestinatario":"'.$fila[0].'","nombreUsuarioDestinatario":"'.$nombreUsuario.'"}';
		$o=json_decode($o);
		array_push($arrDestinatario,$o);
	}
	$nombreUsuario=obtenerNombreUsuario(1)." (".$rolActor.")";
	$o='{"idUsuarioDestinatario":"1","nombreUsuarioDestinatario":"'.$nombreUsuario.'"}';
	$o=json_decode($o);
	array_push($arrDestinatario,$o);
	
	return $arrDestinatario;
}

function obtenerTitularPuestoCarpetaDestino($idFormulario,$idRegistro,$actorDestinatario)
{
	global $con;
	global $tipoMateria;
	
	$arrCamposDestino=array();
	$arrCamposDestino["carpetaAsignada"]=1;
	
	$carpetaAdministrativa="";
	$nombreTablaBase="_".$idFormulario."_tablaDinamica";
	
	$continuar=true;
	$unidadGestion="";

	if($tipoMateria=="P")
	{
		$campo="";
		foreach($arrCamposDestino as $nCampo=>$resto)
		{
			if($con->existeCampo($nCampo,$nombreTablaBase))
			{
				$campo=$nCampo;
				break;
			}
		}
		if($campo!="")
		{
			$consulta="SELECT ".$campo." FROM ".$nombreTablaBase." WHERE id_".$nombreTablaBase."=".$idRegistro;
			$carpetaAdministrativa=$con->obtenerValor($consulta);
		}
		
		
		if($carpetaAdministrativa!="")
		{
			$consulta="SELECT unidadGestion FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$carpetaAdministrativa."'";
			$unidadGestion=$con->obtenerValor($consulta);
		}
		else
		{
			$consulta="SELECT codigoInstitucion FROM ".$nombreTablaBase." WHERE id_".$nombreTablaBase."=".$idRegistro;
			$unidadGestion=$con->obtenerValor($consulta);
		}
		
	}
	else
	{
		$consulta="SELECT codigoInstitucion FROM ".$nombreTablaBase." WHERE id_".$nombreTablaBase."=".$idRegistro;
		$unidadGestion=$con->obtenerValor($consulta);
	}

	$rolActor=obtenerTituloRol($actorDestinatario);
	
	$arrDestinatario=array();
	$consulta="SELECT ad.idUsuario FROM 801_adscripcion ad,807_usuariosVSRoles r WHERE r.idUsuario=ad.idUsuario AND 
				r.codigoRol='".$actorDestinatario."' AND ad.Institucion='".$unidadGestion."'";

	
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		
		$nombreUsuario=obtenerNombreUsuario($fila[0])." (".$rolActor.")";
		$o='{"idUsuarioDestinatario":"'.$fila[0].'","nombreUsuarioDestinatario":"'.$nombreUsuario.'"}';
		$o=json_decode($o);
		array_push($arrDestinatario,$o);
	}
	$nombreUsuario=obtenerNombreUsuario(1)." (".$rolActor.")";
	$o='{"idUsuarioDestinatario":"1","nombreUsuarioDestinatario":"'.$nombreUsuario.'"}';
	$o=json_decode($o);
	array_push($arrDestinatario,$o);
	
	return $arrDestinatario;
}

function esJuezDisponibleIncidenciaV2($idJuez,$fecha,$horaInicio,$horaFin)
{
	global $con;

	
	$consulta="SELECT COUNT(*) FROM _20_tablaDinamica WHERE idEstado=1 and usuarioJuez=".$idJuez." AND '".$fecha."'>=fechaInicial AND '".
			$fecha."'<=fechaFinal  and tipoIntervalo=1";
	
	$nRegistros=$con->obtenerValor($consulta);
	
	$consulta="SELECT * FROM _20_tablaDinamica WHERE idEstado=1 and usuarioJuez=".$idJuez." AND '".$fecha."'>=fechaInicial AND '".
			$fecha."'<=fechaFinal  and tipoIntervalo=2";
	
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_assoc($res))
	{
		
		if(colisionaTiempo($fecha." ".$horaInicio,$fecha." ".$horaFin,$fila["fechaInicial"]." ".$fila["hInicio"],$fila["fechaFinal"]." ".$fila["hFin"],false))
			$nRegistros++;
	}
	return $nRegistros==0;
}


function existeDisponibilidadRecurso($idEvento,$fechaAudiencia,$tipoRecurso,$idRecurso,$horaInicio,$horaTermino,$idRegistroRecurso)
{
	global $con;
	$arrEventos=array();
	$qAux=generarConsultaIntervalos($horaInicio,$horaTermino,"if(horaInicioReal is null,horaInicio,horaInicioReal)",
			"if(horaFinReal is null,horaFin,horaFinReal)",false,true);
	$consulta="SELECT idRegistro
			 FROM 7001_recursosAdicionalesAudiencia WHERE   tipoRecurso=".$tipoRecurso." AND idRecurso=".$idRecurso.
			 " AND situacionRecurso<>3  and ".$qAux." and idRegistro<>".$idRegistroRecurso.
			 " and situacionRecurso in (SELECT idSituacion FROM 7011_situacionEventosAudiencia WHERE considerarDiponibilidad=1)";

	$res=$con->obtenerFilas($consulta);
	while($fEvento=mysql_fetch_row($res))
	{
		array_push($arrEventos,$fEvento)	;
	}
	
	$consulta="SELECT idCentroGestion FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$idEvento;
	$idUnidadGestion=$con->obtenerValor($consulta);
	if($idUnidadGestion=="")
		$idUnidadGestion=-1;
	$consulta="SELECT idPadre FROM _25_chkUnidadesAplica WHERE idOpcion=".$idUnidadGestion;	
	$listaIncidencias=$con->obtenerValor($consulta);
	if($listaIncidencias=="")
		$listaIncidencias=-1;
	
	$consulta="SELECT fechaInicial,horaInicial,fechaFinal,horaFinal,id__25_tablaDinamica,tipoPeriodo FROM _25_tablaDinamica t,_25_Salas s 
				WHERE s.idReferencia=t.id__25_tablaDinamica AND '".$fechaAudiencia."'>=t.fechaInicial AND '".$fechaAudiencia.
				"'<=t.fechaFinal AND s.nombreSala=-".$idRecurso." and idEstado=2 and aplicaTodasUnidades=1
				union
				SELECT fechaInicial,horaInicial,fechaFinal,horaFinal,id__25_tablaDinamica,tipoPeriodo FROM _25_tablaDinamica t,_25_Salas s 
				WHERE s.idReferencia=t.id__25_tablaDinamica AND '".$fechaAudiencia."'>=t.fechaInicial AND '".$fechaAudiencia.
				"'<=t.fechaFinal AND s.nombreSala=-".$idRecurso." and idEstado=2 and aplicaTodasUnidades=0 and id__25_tablaDinamica in(".
				$listaIncidencias.")";
				
	$res=$con->obtenerFilas($consulta);
	while($fIncidencia=mysql_fetch_row($res))
	{
		
		$horaInicial="00:00:00";
		$horaFinal="23:59:59";
		if($fIncidencia[1]=="")
			$fIncidencia[1]=$horaInicial;
		
		if($fIncidencia[3]=="")
			$fIncidencia[3]=$horaFinal;
			
		
		
		if($fIncidencia[5]==2)
		{
			
			if($fIncidencia[0]==$fechaAudiencia)
			{
				$horaInicial=$fIncidencia[1];
			}
			
			
			if($fIncidencia[2]==$fechaAudiencia)
			{
				$horaFinal=$fIncidencia[3];
			}
			
		}
		else
		{
			$horaInicial=$fIncidencia[1];
			$horaFinal=$fIncidencia[3];
		}
		
		$fechaInicio=$fechaAudiencia." ".$horaInicial;
		$fechaFinal=$fechaAudiencia." ".$horaFinal;
		
		
		if(colisionaTiempo($horaInicio,$horaTermino,$fechaInicio,$fechaFinal,false))
			array_push($arrEventos,$fIncidencia);
	}	
	
	return count($arrEventos)==0;

	
}


function registrarTerminacionRecursosEventos($idRegistroEvento,$horaInicial,$horaFinal)
{
	global $con;
	
	$x=0;
	$query[$x]="begin";
	$x++;
	$consulta="SELECT * FROM 7001_recursosAdicionalesAudiencia WHERE idRegistroEvento=".$idRegistroEvento." AND situacionRecurso IN
				(SELECT idSituacion FROM 7011_situacionEventosAudiencia WHERE considerarDiponibilidad=1)";
		
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_assoc($res))
	{
		$hInicial=$fila["horaInicio"];
		if(strtotime($horaInicial)>strtotime($hInicial))
		{
			$hInicial=$horaInicial;
		}
		
		
		$hFinal=$fila["horaFin"];
		if(strtotime($horaFinal)<strtotime($hFinal))
		{
			$hFinal=$horaFinal;
		}
		
		$query[$x]="UPDATE 7001_recursosAdicionalesAudiencia SET situacionRecurso=2,horaInicioReal='".$hInicial."',horaFinReal='".$hFinal.
		 			"' WHERE idRegistro=".$fila["idRegistro"];
		
		
		$x++;
		
		
		
		$query[$x]="INSERT INTO 7001_bitacoraCambiosRecursosAdicionales(idRegistroRecurso,fechaCambio,idUsuarioResponsable,comentariosAdicionales,
					situacionAnterior,idRecursoAnterior,horaInicioAnterior,horaFinAnterior,comentariosAdicionalesAnterior)
					VALUES(".$fila["idRegistro"].",'".date("Y-m-d H:i:s")."',".$_SESSION["idUsr"].",'Terminación de audiencia',".$fila["situacionRecurso"].
					",".$fila["idRecurso"].",'".$fila["horaInicio"]."','".$fila["horaFin"]."','".cv($fila["comentariosAdicionales"])."')";
		$x++;	
		
		
	}
	
	$query[$x]="commit";
	$x++;

	return $con->ejecutarBloque($query);
					
}

function registrarCancelacionRecursosEventos($idRegistroEvento,$comentariosAdicionales)
{
	global $con;
	
	$x=0;
	$query[$x]="begin";
	$x++;
	$consulta="SELECT * FROM 7001_recursosAdicionalesAudiencia WHERE idRegistroEvento=".$idRegistroEvento." AND situacionRecurso IN
				(SELECT idSituacion FROM 7011_situacionEventosAudiencia WHERE considerarDiponibilidad=1)";
		
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_assoc($res))
	{
		$query[$x]="UPDATE 7001_recursosAdicionalesAudiencia SET situacionRecurso=3 WHERE idRegistro=".$fila["idRegistro"];
		$x++;
		
		$query[$x]="INSERT INTO 7001_bitacoraCambiosRecursosAdicionales(idRegistroRecurso,fechaCambio,idUsuarioResponsable,comentariosAdicionales,
					situacionAnterior,idRecursoAnterior,horaInicioAnterior,horaFinAnterior,comentariosAdicionalesAnterior)
					VALUES(".$fila["idRegistro"].",'".date("Y-m-d H:i:s")."',".$_SESSION["idUsr"].",'".cv($comentariosAdicionales)."',".$fila["situacionRecurso"].
					",".$fila["idRecurso"].",'".$fila["horaInicio"]."','".$fila["horaFin"]."','".cv($fila["comentariosAdicionales"])."')";
		
		
		$x++;
	}
	
	$query[$x]="commit";
	$x++;

	if( $con->ejecutarBloque($query))
	{
		
		return true;
	}
					
}

function registrarReunionVirtualLatisMeeting($idEvento,$objConfComplementaria)
{
	global $con;


	$consulta="SELECT * FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$idEvento;
	$fRegistroEvento=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$consulta="SELECT tipoAudiencia FROM _4_tablaDinamica WHERE id__4_tablaDinamica=".$fRegistroEvento["tipoAudiencia"];
	$tipoAudiencia=$con->obtenerValor($consulta);
	
	$consulta="SELECT carpetaAdministrativa FROM 7007_contenidosCarpetaAdministrativa WHERE tipoContenido=3 AND idRegistroContenidoReferencia=".$idEvento;
	$carpetaAdministrativa=$con->obtenerValor($consulta);
	
	$consulta="SELECT idActividad,carpetaInvestigacion FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$carpetaAdministrativa."'";
	$fCarpetaInvestigacion=$con->obtenerPrimeraFila($consulta);
	$idActividad=$fCarpetaInvestigacion[0];

	
	$arrParticipantes="";
	$arrRegistros="";
	$numReg=0;
	
	$totalParticipantes=0;
	
	foreach($objConfComplementaria->participantes as $p)
	{
	
		$nombreParticipante="";
		if($p->idParticipante>0)
		{
			$cadDomicilio=obtenerUltimoDomicilioFiguraJuridica($p->idParticipante);
			$mails="";
			$oDomicilio=json_decode($cadDomicilio);
			foreach($oDomicilio->correos as $m)
			{
				if($mails=="")
					$mails=$m->mail;
				else
					$mails.=",".$m->mail;
			}
			
			$arrTelefono="";

			foreach($oDomicilio->telefonos as $t)
			{
				if($t->tipoTelefono==2)
				{
					if($arrTelefono=="")
						$arrTelefono=$t->lada."-".$t->numero;
					else
						$arrTelefono.=",".$t->lada."-".$t->numero;
				}
			}
			
			$oParticipante='{"idParticipante":"-1","nombreParticipante":"'.cv($p->nombreParticipante).
						'","tipoParticipacion":"2","email":"'.$mails.
						'","noParticipantes":"1","tipoParticipante":"2","perfilParticipante":"'.
						$p->perfilParticipante.'","idAuxiliar":"'.$p->idParticipante.
						'","telefono":"'.$arrTelefono.'"}';
		
			if($arrParticipantes=="")
				$arrParticipantes=$oParticipante;
			else
				$arrParticipantes.=",".$oParticipante;
			
			$totalParticipantes++;
		}
		else
		{
			switch($p->idParticipante)
			{
				case -10:
					
					$consulta="SELECT * FROM 7001_eventoAudienciaJuez WHERE idRegistroEvento=".$idEvento;
					$resJueces=$con->obtenerFilas($consulta);
					while($filaJuez=mysql_fetch_assoc($resJueces))
					{
						$consulta="SELECT Mail FROM 805_mails WHERE idUsuario=".$filaJuez["idJuez"];
						$mails=$con->obtenerListaValores($consulta);
						
						$consulta="SELECT CONCAT(Lada,'-',Numero) AS numero FROM 804_telefonos WHERE idUsuario=".$filaJuez["idJuez"]." and Tipo2=2";
						$arrTelefono=$con->obtenerListaValores($consulta);

						
						$oParticipante='{"idParticipante":"-1","nombreParticipante":"'.(obtenerNombreUsuario($filaJuez["idJuez"])).
										'","tipoParticipacion":"1","email":"'.$mails.'","noParticipantes":"1",'.
										'"tipoParticipante":"2","perfilParticipante":"'.$p->perfilParticipante.
										'","idAuxiliar":"-10","telefono":"'.$arrTelefono.'"}';
						if($arrParticipantes=="")
							$arrParticipantes=$oParticipante;
						else
							$arrParticipantes.=",".$oParticipante;
						$totalParticipantes++;
					}
					
				
				
					
				break;
				case -20:
					
					if($objConfComplementaria->auxiliarSala!=-1)
					{
						$consulta="SELECT Mail FROM 805_mails WHERE idUsuario=".$objConfComplementaria->auxiliarSala;
						$mails=$con->obtenerListaValores($consulta);
						
						$consulta="SELECT CONCAT(Lada,'-',Numero) AS numero FROM 804_telefonos WHERE idUsuario=".$objConfComplementaria->auxiliarSala." and Tipo2=2";
						$arrTelefono=$con->obtenerListaValores($consulta);

						
						$oParticipante='{"idParticipante":"-1","nombreParticipante":"'.(obtenerNombreUsuario($objConfComplementaria->auxiliarSala)).
										'","tipoParticipacion":"1","email":"'.$mails.'","noParticipantes":"1",'.
										'"tipoParticipante":"2","perfilParticipante":"'.$p->perfilParticipante.
										'","idAuxiliar":"-20","telefono":"'.$arrTelefono.'"}';
						if($arrParticipantes=="")
							$arrParticipantes=$oParticipante;
						else
							$arrParticipantes.=",".$oParticipante;
						$totalParticipantes++;
						
					}
				break;
			}
			
		}
		
		
	}
	
	
	$duracionEstimada=obtenerDiferenciaMinutos($fRegistroEvento["horaInicioEvento"],$fRegistroEvento["horaFinEvento"]);
	$tituloReunion=$tipoAudiencia.", Carpeta Judicial: ".$carpetaAdministrativa;
	$confSesion='{"permiteGrabacion":"1","grabarAlIniciar":"0","permiteDetenerIniciarGrabacion":"1","webCamSoloModerador":"0","silencioAlIniciar":"1",'.
				'"permitirDesSileciarParticipantes":"1","deshabilitarCamaraParticipantes":"0","deshabilitarMicrofonoParticipantes":"0","deshabilitarChatPrivado":"0"'.
				',"deshabilitarChatPublico":"0","deshabilitarNotas":"0","iniciarAlIngresarModerador":"0"}';
	$cadObj='{"tituloReunion":"'.cv($tituloReunion).'","fechaReunion":"'.$fRegistroEvento["horaInicioEvento"].'","duracionEstimada":"'.$duracionEstimada.
			'","totalParticipantes":"'.$totalParticipantes.'","idReunion":"'.
			($fRegistroEvento["idReunionVirtual"]==0?-1:$fRegistroEvento["idReunionVirtual"]).'","confSesion":'.$confSesion.',"participantes":['.$arrParticipantes.']}';
	
	
   	
	$x=0;
	$query[$x]="begin";
	$x++;
	$obj=json_decode($cadObj);
	
	$confSesion=$obj->confSesion;
	if($obj->idReunion==-1)
	{
		$encontrado=true;
		$reunionID=rand(1000,9999)."-".date("Hi",strtotime($obj->fechaReunion))."-".date("ym",strtotime($obj->fechaReunion))."-".rand(1000,9999);
		while($encontrado)
		{
			$consulta="SELECT COUNT(*) FROM 7050_reunionesVirtualesProgramadas WHERE reunionID='".$reunionID."'";
			$numRegistros=$con->obtenerValor($consulta);
			$encontrado=$numRegistros>0;
		}
		
		
		$query[$x]="INSERT INTO 7050_reunionesVirtualesProgramadas(fechaRegistro,idResponsableRegistro,situacionActual,nombreReunion,fechaProgramada,
					duracion,passwdModerador,passParticipante,reunionID,maxParticipantes,permiteGrabacion,grabarAlIniciar,permiteDetenerIniciarGrabacion,
					webCamSoloModerador,silencioAlIniciar,permitirDesSileciarParticipantes,deshabilitarCamaraParticipantes,deshabilitarMicrofonoParticipantes,
					deshabilitarChatPrivado,deshabilitarChatPublico,deshabilitarNotas,iniciarAlIngresarModerador,mesaEvidencia) values 
					('".date("Y-m-d H:i:s")."',".$_SESSION["idUsr"].",0,'".cv($obj->tituloReunion)."','".$obj->fechaReunion."',".$obj->duracionEstimada.
					",'".generarPasswordAleatorio()."','".generarPasswordAleatorio()."','".$reunionID."',".$obj->totalParticipantes.",".$confSesion->permiteGrabacion.
					",".$confSesion->grabarAlIniciar.",".$confSesion->permiteDetenerIniciarGrabacion.",".$confSesion->webCamSoloModerador.
					",".$confSesion->silencioAlIniciar.",".$confSesion->permitirDesSileciarParticipantes.",".$confSesion->deshabilitarCamaraParticipantes.
					",".$confSesion->deshabilitarMicrofonoParticipantes.",".$confSesion->deshabilitarChatPrivado.",".$confSesion->deshabilitarChatPublico.
					",".$confSesion->deshabilitarNotas.",".$confSesion->iniciarAlIngresarModerador.",".$objConfComplementaria->requiereMesaEvidencias.")";
		$x++;
		$query[$x]="set @idReunion:=(select last_insert_id())";
		$x++;
		
	}
	else
	{
		$query[$x]="set @idReunion:=".$obj->idReunion;
		$x++;
		
		$query[$x]="update 7050_reunionesVirtualesProgramadas set nombreReunion='".cv($obj->tituloReunion)."',fechaProgramada='".$obj->fechaReunion.
					"',duracion=".$obj->duracionEstimada.",maxParticipantes=".$obj->totalParticipantes.",permiteGrabacion=".$confSesion->permiteGrabacion.
					",grabarAlIniciar=".$confSesion->grabarAlIniciar.",permiteDetenerIniciarGrabacion=".$confSesion->permiteDetenerIniciarGrabacion.
					",webCamSoloModerador=".$confSesion->webCamSoloModerador.",silencioAlIniciar=".$confSesion->silencioAlIniciar.
					",permitirDesSileciarParticipantes=".$confSesion->permitirDesSileciarParticipantes.",deshabilitarCamaraParticipantes=".
					$confSesion->deshabilitarCamaraParticipantes.",deshabilitarMicrofonoParticipantes=".$confSesion->deshabilitarMicrofonoParticipantes.",
					deshabilitarChatPrivado=".$confSesion->deshabilitarChatPrivado.",deshabilitarChatPublico=".$confSesion->deshabilitarChatPublico.
					",deshabilitarNotas=".$confSesion->deshabilitarNotas.",iniciarAlIngresarModerador=".$confSesion->iniciarAlIngresarModerador.
					",mesaEvidencia=".$objConfComplementaria->requiereMesaEvidencias." where idRegistro=@idReunion";
					
		$x++;
		
		$query[$x]="DELETE FROM 7051_participantesReunionesVirtuales WHERE idReunion=@idReunion";
		$x++;
	}
	
	
	foreach($obj->participantes as $p)
	{
		$nombreParticipante="";
		switch($p->tipoParticipante)
		{
			case 1:
				$nombreParticipante=$p->idParticipante;
			break;
			case 2:
			case 3:
				$nombreParticipante=$p->nombreParticipante;
			break;
		}
		
		$passwdReunion=generarPasswordAleatorio();
		$query[$x]="INSERT INTO 7051_participantesReunionesVirtuales(idReunion,tipoParticipante,nombreParticipante".
					",rolParticipante,eMail,noParticipantes,passwdReunion,perfilParticipacion,idAuxiliar,telefono) ".
					"VALUES(@idReunion,".$p->tipoParticipante.",'".cv($nombreParticipante)."',".$p->tipoParticipacion.
					",'".$p->email."',".$p->noParticipantes.",'".$passwdReunion."',".$p->perfilParticipante.",".
					$p->idAuxiliar.",'".$p->telefono."')";
		$x++;
	}
	
	$idAuxiliarSala=(($objConfComplementaria->auxiliarSala=="") || ($objConfComplementaria->auxiliarSala==-1))?"NULL":$objConfComplementaria->auxiliarSala;
	$query[$x]="UPDATE 7000_eventosAudiencia SET idReunionVirtual=@idReunion,idAuxiliarSala=".$idAuxiliarSala." WHERE idRegistroEvento=".$idEvento;
	$x++;
	
	$query[$x]="UPDATE 7050_reunionesVirtualesProgramadas SET situacionActual=1 WHERE idRegistro=@idReunion";
	$x++;	
		
		
	$query[$x]="commit";
	$x++;

	if($con->ejecutarBloque($query))
	{

		$consulta="select @idReunion";
		$idReunion=$con->obtenerValor($consulta);

		enviarInvitacionesReunion($idReunion);
		return true;
	}
	
	
}


?>