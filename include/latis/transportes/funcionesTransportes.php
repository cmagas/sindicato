<?php
	function generarRolArranqueDia($fecha)
	{
		global $con;
		$arrRutasHorarios=array();
		$fechaBase=strtotime($fecha);
		
		$diaBase=date("w",$fechaBase);
		
		$diaFechaInicio=$diaBase;
		if($diaFechaInicio==0)
			$diaFechaInicio=7;
			
		$diferencia=$diaFechaInicio-1;
		
		$fechaSemanaInicio=strtotime("-".$diferencia." days",$fechaBase);
		$fechaInicio=$fechaSemanaInicio;
		$fechaFin=strtotime("+6 days",$fechaInicio);
		
		$fechaInicioAux=$fechaBase;
		$arrFechaConsiderar=array();
		$arrFechas="";
		for($x=1;$x<=7;$x++)
		{
			$dia=$x;
			if($dia==7)
			{
				$dia=0;
				$arrFechas="'".date("d/m/Y",$fechaInicioAux)."'".$arrFechas;
			}
			else
				$arrFechas.=",'".date("d/m/Y",$fechaInicioAux)."'";
				
			$arrFechaConsiderar[$dia]=date("Y-m-d",$fechaInicioAux);
			$fechaInicioAux=strtotime("+1 days",$fechaInicioAux);
		}
		
		
		$numReg=0;
		$arrRegistros="";
		
		/*$consulta="SELECT idRuta,nombreRuta FROM 3100_rutasTransportes";
		$consulta.=" ORDER BY nombreRuta";
		$res=$con->obtenerFilas($consulta);
		$arrRutas=array();
		$arrCacheHorarios=array();
		$matrizHorario=array();
		$matrizHorarioRol=array();
		
		$arrHorarioRutas=array();
		
		while($fila=mysql_fetch_row($res))
		{
			$llaveRuta=$fila[1]."_".$fila[0];
			$arrRutas[$fila[1]."_".$fila[0]]=array();
			$matrizHorario[$fila[1]."_".$fila[0]]["ruta"]=$fila[1];
			$matrizHorario[$fila[1]."_".$fila[0]]["horario"]=array();	
			
			generarRolHorarioRutaDia($fila[0],$arrFechaConsiderar[$diaBase]);
			$consulta="SELECT DISTINCT p.horario,p.idFilaHorario FROM 3105_horarioEjecucionRuta h,3103_horariosPerfilRuta p,3104_marcadoresHorarioPerfilRuta m WHERE 
						idRuta=".$fila[0]." AND fecha>='".date("Y-m-d",$fechaInicio).
						"' AND fecha<='".date("Y-m-d",$fechaFin)."' and p.idHorarioPerfil=h.idHorario and m.idHorario=p.idHorarioPerfil and m.idMarcador=2";
	
			
			$resHorario=$con->obtenerFilas($consulta);
			while($fHorario=mysql_fetch_row($resHorario))		
			{
				
				$dia=$diaBase;
				$llave=$fHorario[0]."_".$fHorario[1];
				if(!isset($matrizHorarioRol[$llave]))	
				{
					$matrizHorarioRol[$llave]=array();
				}
				if(!isset($matrizHorarioRol[$llave][$llaveRuta]))	
				{
					$matrizHorarioRol[$llave][$llaveRuta]=array();
				}	
					
				$consulta="SELECT (SELECT idUnidadAsignada FROM 3106_asignacionHorarioRuta WHERE idHorarioAsignacion=idHorarioEjecucion) as idUnidadAsignada,idHorarioEjecucion FROM 3105_horarioEjecucionRuta h,3103_horariosPerfilRuta p,3104_marcadoresHorarioPerfilRuta m WHERE 
							idRuta=".$fila[0]." AND fecha='".$fecha."' and p.horario='".$fHorario[0]."' and p.idFilaHorario=".$fHorario[1]." and p.idHorarioPerfil=h.idHorario and m.idHorario=p.idHorarioPerfil and m.idMarcador in(2,4)";	
				
				$filaHorario=$con->obtenerPrimeraFila($consulta);
				$nReg=$con->filasAfectadas;
				if($nReg>0)
				{
					
					$matrizHorarioRol[$llave][$llaveRuta]["idRuta"]=$fila[0];
					$matrizHorarioRol[$llave][$llaveRuta]["ruta"]=$fila[1];
					$matrizHorarioRol[$llave][$llaveRuta]["idHorarioEjecucion"]=$filaHorario[1];
					$numEconomico="";
					if(isset($arrUnidades[$filaHorario[0]]))
						$numEconomico=$arrUnidades[$filaHorario[0]];
					$matrizHorarioRol[$llave][$llaveRuta]["dias"][$dia]=$numEconomico."|".$filaHorario[0]."|".$filaHorario[1];
				}
				else
					$horario[$dia]="";
				
			}
		}
		
		foreach($matrizHorarioRol as $horario=>$aRuta)
		{
			foreach($aRuta as $ruta=>$resto)	
			{
				if(sizeof($resto)>0)
				{
					$arrHorarioRutas[$horario."_".$ruta]["hora"]=$horario;
					$arrHorarioRutas[$horario."_".$ruta]["ruta"]=$ruta;           
					$arrHorarioRutas[$horario."_".$ruta]["idHorarioEjecucion"]=$resto["idHorarioEjecucion"];           
				}
			}
		}
		*/
		
		
		
		$consulta="SELECT r.nombreRuta,p.horario,p.idPerfilHorarioRuta,p.idFilaHorario,r.idRuta,h.idHorarioEjecucion, 
					(SELECT GROUP_CONCAT(idMarcador) FROM 3104_marcadoresHorarioPerfilRuta WHERE idHorario=h.idHorario) as marcadores
					FROM 3105_horarioEjecucionRuta h,3103_horariosPerfilRuta p,3100_rutasTransportes r ,
					3104_marcadoresHorarioPerfilRuta m WHERE  
					h.fecha='".$fecha."' AND p.idHorarioPerfil=h.idHorario AND
					r.idRuta=h.idRuta AND  m.idHorario=h.idHorario AND m.idMarcador IN (2,4) ORDER BY horario,nombreRuta";	

		$res=$con->obtenerFilas($consulta);
		while($fRuta=mysql_fetch_row($res))		
		{
			$llave=$fRuta[1]."_".$fRuta[0]."_".$fRuta[2]."_".$fRuta[3]."_".$fRuta[4];

			
			$arrRutasHorarios[$llave]=array();
			$arrRutasHorarios[$llave]["idRuta"]=$fRuta[4];
			$arrRutasHorarios[$llave]["ruta"]=$fRuta[0];
			$arrRutasHorarios[$llave]["hora"]=$fRuta[1];
			$arrRutasHorarios[$llave]["idPerfil"]=$fRuta[2];
			$arrRutasHorarios[$llave]["idFila"]=$fRuta[3];
			$arrRutasHorarios[$llave]["marcadores"]=explode(",",$fRuta[6]);
			$arrRutasHorarios[$llave]["idHorarioEjecucion"]=$fRuta[5];
			
		}
		
		return $arrRutasHorarios;
		
		
	}
	
	function generarAsignacionTransporte($arrRuta,$fecha,$unidadInicial)
	{
		global $con;
		
		$x=0;
		$query[$x]="begin";
		$x++;
		
		$arrUnidadesGuardias=obtenerUnidadesGuardias($fecha);
		$arrUnidades=obtenerUnidadesAsignablesTransporte($fecha);
		
		$posUnidad=0;
		foreach($arrUnidades as $u)
		{
			if($u["idUnidad"]==$unidadInicial)
			{
				break;
			}	
			$posUnidad++;
		}
		$lAsignacion="";
		$orden=1;
		
		
		
		foreach($arrRuta as $r)
		{
			$idUnidad=$arrUnidades[$posUnidad]["idUnidad"];
			if($lAsignacion=="")
				$lAsignacion=$r["idHorarioEjecucion"];
			else
				$lAsignacion.=",".$r["idHorarioEjecucion"];
			
			$consulta="SELECT idAsignacion FROM 3106_asignacionHorarioRuta WHERE idHorarioAsignacion=".$r["idHorarioEjecucion"];
			$idAsignacion=$con->obtenerValor($consulta);
			if($idAsignacion!="")
			{
				$query[$x]="UPDATE 3106_asignacionHorarioRuta SET idUnidadAsignada=".$idUnidad.",idChoferAsignado=NULL,orden=".$orden." WHERE idAsignacion=".$idAsignacion;	
				$x++;
			}
			else
			{
				$query[$x]="INSERT INTO 3106_asignacionHorarioRuta(idHorarioAsignacion,idUnidadAsignada,fecha,horario,orden)
						VALUES(".$r["idHorarioEjecucion"].",".$idUnidad.",'".$fecha."','".$r["hora"]."',".$orden.")";
				$x++;

			}
			$orden++;
			$posUnidad++;
			if($posUnidad==sizeof($arrUnidades))
				$posUnidad=0;
			
		}
		$query[$x]="delete from 3106_asignacionHorarioRuta where fecha='".$fecha."' and idHorarioAsignacion not in (".$lAsignacion.")";
		$x++;
		$query[$x]="commit";
		$x++;

		return $con->ejecutarBloque($query);

		
	}
	
	function obtenerUnidadesAsignablesTransporte($fecha="")
	{
		global $con;
		$arrUnidades=array();
		$consulta="SELECT id__1012_tablaDinamica,numEconomico FROM _1012_tablaDinamica ORDER BY numEconomico";
		$res=$con->obtenerFilas($consulta);	
		while($fila=mysql_fetch_row($res))
		{
			$obj["idUnidad"]=$fila[0];
			$obj["numEconomico"]=$fila[1];
			array_push($arrUnidades,$obj);
		}
		return $arrUnidades;
	}
	
	function obtenerUnidadesGuardias($fecha)
	{
		global $con;	
	}
	
	function registrarTarjetas($idRegistro)
	{
		global $con;
		
		$x=0;
		$query[$x]="begin";
		$x++;
		$consulta="SELECT * FROM _1039_gridDetalleCompra WHERE idReferencia=".$idRegistro;
		$res=$con->obtenerFilas($consulta);	
		while($fila=mysql_fetch_row($res))
		{
			for($folio=$fila[3];$folio<=$fila[4];$folio++)
			{
				$query[$x]="INSERT INTO 1101_tarjetasInventario(idTarjetaCompra,folio,situacion,color)
							VALUES(".$idRegistro.",".$folio.",0,".$fila[2].")";
				$x++;
			}
		}
		$query[$x]="commit";
		$x++;
		eB($query);
	}
	
	function registrarBoletos($idRegistro)
	{
		global $con;
		
		$x=0;
		$query[$x]="begin";
		$x++;
		$consulta="SELECT noBoletosTalon FROM _1033_tablaDinamica";
		$noBoletos=$con->obtenerValor($consulta);
		
		if($noBoletos=="")
			$noBoletos=100;
		
		$consulta="SELECT * FROM _1040_gridDetalleCompra WHERE idReferencia=".$idRegistro;
		$res=$con->obtenerFilas($consulta);	
		while($fila=mysql_fetch_row($res))
		{
			$consulta="SELECT denominacion,color FROM _1030_tablaDinamica WHERE id__1030_tablaDinamica=".$fila[2];
			$fTipoBoleto=$con->obtenerPrimeraFila($consulta);
			
			$color=$fTipoBoleto[1];
			$monto=$fTipoBoleto[0];
			$folioInicial="";
			$folioFinal="";
			
			$fInicial=$fila[3];
			$fFinal=$fila[4];
			$folioInicial=$fInicial;
			$salir=false;
			while($fInicial<$fFinal)
			{
				$folioFinal=$fInicial+$noBoletos-1;
				
				if($folioFinal>$fFinal)
					$folioFinal=$fFinal;
				$query[$x]="INSERT INTO 1102_talonesBoletos(tipoBoleto,folioInicial,folioFinal,monto,folioActual,color,boletosExistencia,folioFinalActual,idFormulario,idReferencia)
						VALUES(".$fila[0].",".$fInicial.",".$folioFinal.",".$monto.",".$fInicial.",".$color.",".$noBoletos.",".$folioFinal.",1040,".$idRegistro.")";
				$x++;
				$fInicial=$folioFinal+1;
			}
			
			
			
		}
		$query[$x]="commit";
		$x++;
		
		eB($query);
	}
	
	function obtenerTotalFoliosBaja($idTalon,$folioInicial="",$folioFinal="")
	{
		global $con;
	
		if($folioInicial=="")
		{
			$consulta="SELECT folioInicial,folioFinal FROM 1102_talonesBoletos WHERE idTalon=".$idTalon;
			$fFolio=$con->obtenerPrimeraFila($consulta);
			$folioInicial=$fFolio[0];
			$folioFinal=$fFolio[1];
		}
		
		$arrFoliosCandidatos=array();
		
		for($pos=$folioInicial;$pos<=$folioFinal;$pos++)
		{
			$arrFoliosCandidatos[$pos]=0;
		}
		
		
		$comp=generarConsultaIntervalos($folioInicial,$folioFinal,"folioInicial","folioFinal",true);
		
		$arrIntervalos=array();
		$query="SELECT folioInicial,folioFinal FROM 1106_bajasBoletosTalon WHERE idTalon=".$idTalon." and ".$comp." order by folioInicial";

		$rRegistros=$con->obtenerFilas($query);
		while($fRegistro=mysql_fetch_row($rRegistros))
		{
			array_push($arrIntervalos,$fRegistro);
		}
		
		$arrIntervalosBaja=array();
		foreach($arrIntervalos as $i)
		{
			for($pos=$i[0];$pos<=$i[1];$pos++)
			{
				if(isset($arrFoliosCandidatos[$pos]))
					$arrFoliosCandidatos[$pos]=1;
			}	
		}
		
		
		$foliosBaja=0;
		foreach($arrFoliosCandidatos as $folio=>$valor)
		{
			if($valor==1)
				$foliosBaja++;
		}
		
		
		return $foliosBaja;	
	}
	
	function obtenerUltimoBoletoVendido($idTalon,$idChofer)
	{
		global $con;
		$consulta="SELECT folioInicial,folioFinal,folioActual FROM 1105_asignacionBoletosChofer WHERE folioTalon=".$idTalon." AND idChofer=".$idChofer." ORDER BY idAsignacion DESC";
		$fAsignacion=$con->obtenerPrimeraFila($consulta);
		
		if($fAsignacion)
		{
			if($fAsignacion[0]==$fAsignacion[2])
				return -1;
			else
			{
				$ultimoFolio=-1;
				if($fAsignacion[2]!=-1)
				{
					for($x=($fAsignacion[2]-1);$x>=$fAsignacion[0];$x--)
					{
						if(obtenerTotalFoliosBaja($idTalon,$x,$x)==0)	
						{
							$ultimoFolio=$x;
							break;
						}
					}
				}
				else
				{
					for($x=($fAsignacion[1]);$x>=$fAsignacion[0];$x--)
					{
						if(obtenerTotalFoliosBaja($idTalon,$x,$x)==0)	
						{
							$ultimoFolio=$x;
							break;
						}
					}		
				}
				return $ultimoFolio;	
			}
			
		}
		else
			return -1;
	}
	
	function obtenerProximoFolioActual($idTalon,$folioBase)
	{
		global $con;	
		$arrFoliosCandidatos=array();
		$consulta="SELECT folioActual,folioFinalActual FROM 1102_talonesBoletos WHERE idTalon=".$idTalon;
		$fTalon=$con->obtenerPrimeraFila($consulta);
		if(($folioBase>=$fTalon[0])&&($folioBase<=$fTalon[1]))
		{
			for($pos=$folioBase;$pos<=$fTalon[1];$pos++)
			{
				$arrFoliosCandidatos[$pos]=0;
			}		
			
			$comp=generarConsultaIntervalos($folioBase,$fTalon[1],"folioInicial","folioFinal",true);
			$arrIntervalos=array();
			$query="SELECT folioInicial,folioFinal FROM 1106_bajasBoletosTalon WHERE idTalon=".$idTalon." and ".$comp." order by folioInicial";

			$rRegistros=$con->obtenerFilas($query);
			while($i=mysql_fetch_row($rRegistros))
			{
				for($pos=$i[0];$pos<=$i[1];$pos++)
				{
					if(isset($arrFoliosCandidatos[$pos]))
						$arrFoliosCandidatos[$pos]=1;
				}	
			}
			
			foreach($arrFoliosCandidatos as $folio=>$valor)			
			{
				if($valor==0)	
					return $folio;
			}
			
		}
		
		return -1;
	}
		
	function obtenerProximoFolioTerminoActual($idTalon)
	{
		global $con;	
		$arrFoliosCandidatos=array();
		$consulta="SELECT folioActual,folioFinalActual FROM 1102_talonesBoletos WHERE idTalon=".$idTalon;
		$fTalon=$con->obtenerPrimeraFila($consulta);
		
		for($pos=$fTalon[1];$pos>=$fTalon[0];$pos--)
		{
			$arrFoliosCandidatos[$pos]=0;
		}		
		
		$comp=generarConsultaIntervalos($fTalon[0],$fTalon[1],"folioInicial","folioFinal",true);
		$arrIntervalos=array();
		$query="SELECT folioInicial,folioFinal FROM 1106_bajasBoletosTalon WHERE idTalon=".$idTalon." and ".$comp." order by folioInicial";
		$rRegistros=$con->obtenerFilas($query);
		while($i=mysql_fetch_row($rRegistros))
		{
			for($pos=$i[0];$pos<=$i[1];$pos++)
			{
				if(isset($arrFoliosCandidatos[$pos]))
					$arrFoliosCandidatos[$pos]=1;
			}	
		}

		
	
		foreach($arrFoliosCandidatos as $folio=>$valor)			
		{
			if($valor==0)	
				return $folio;
		}
			
		
		
		return -1;
		
			
	}	
	
	function cambiarSituacionAsignacionRuta($idAsignacion,$etapa,$comentarios="",$ref1="",$ref2="")
	{
		global $con;
		$consulta="SELECT situacion FROM 3106_asignacionHorarioRuta WHERE idAsignacion=".$idAsignacion;
		$situacion=$con->obtenerValor($consulta);
		
		
		$x=0;
		$query[$x]="begin";
		$x++;
		$query[$x]="INSERT INTO 3108_historialAsignacionRuta(idAsignacion,fechaAccion,responsableCambio,etapaAnterior,etapaActual,comentarios,referencia1,referencia2)
				VALUES(".$idAsignacion.",'".date("Y-m-d H:i:s")."',".$_SESSION["idUsr"].",".$situacion.",".$etapa.",'".cv($comentarios)."','".$ref1."','".$ref2."')";

		
		$x++;
		$query[$x]="UPDATE 3106_asignacionHorarioRuta SET situacion=".$etapa." WHERE idAsignacion=".$idAsignacion;
		$x++;
		$query[$x]="commit";
		$x++;	
		return $con->ejecutarBloque($query);
	}
	
	function registrarPuntosControlRuta($idAsignacion)
	{
		global $con;
		$consulta="SELECT ar.idRuta,h.horario,a.fecha FROM 3106_asignacionHorarioRuta a,3105_horarioEjecucionRuta ar,3103_horariosPerfilRuta h WHERE idAsignacion=".$idAsignacion." AND ar.idHorarioEjecucion=a.idHorarioAsignacion
					AND h.idHorarioPerfil=ar.idHorario";
		$fHorario=$con->obtenerPrimeraFila($consulta);
		$consulta="SELECT * FROM 3101_puntosRecorridoRuta WHERE idRuta=".$fHorario[0]."  ORDER BY orden";
		$res=$con->obtenerFilas($consulta);
		$horaBase=strtotime($fHorario[2]." ".$fHorario[1]);
		$x=0;
		$query[$x]="begin";
		$x++;
		$query[$x]="delete from 3109_puntosControlRutaEjecucion where idAsignacion=".$idAsignacion;
		$x++;
		while($fila=mysql_fetch_row($res))
		{
			if($fila[7]=="")	
				$fila[7]=0;
			if(($fila[4]=="2")||($fila[4]=="6"))
			{
				$horaPunto=strtotime("+".$fila[7]." minutes",$horaBase);
				
				$horaLlegada="'".date("H:i:s",$horaPunto)."'";
				$fecha=date("Y-m-d",$horaPunto);
				if($fila[7]==0)
					$horaLlegada="NULL";
				$query[$x]="INSERT INTO 3109_puntosControlRutaEjecucion(idAsignacion,idPuntoControl,horaLlegadaProgramada,fecha,situacion,idPuntoOrigen,idPuntoRecorrido)
							VALUES(".$idAsignacion.",".$fila[2].",".$horaLlegada.",'".$fecha."',1,".$fila[1].",".$fila[0].")";
				$x++;
			}
		}
		$query[$x]="commit";
		$x++;
		return $con->ejecutarBloque($query);
		
	}
	
	function asignarSiguienteRutaDisponible($fecha,$idUnidad,$idChofer)
	{
		global $con;
		$consulta="SELECT h.idHorarioEjecucion,p.horario FROM 3105_horarioEjecucionRuta h,3103_horariosPerfilRuta p WHERE fecha='".$fecha."' 
						AND p.idHorarioPerfil=h.idHorario AND h.idHorarioEjecucion  NOT IN
						(
							SELECT idHorarioAsignacion FROM 3106_asignacionHorarioRuta WHERE fecha='".$fecha."'
						)
						ORDER BY p.horario"	;
		$fHorario=$con->obtenerPrimeraFila($consulta);
		$resultado=NULL;
		if($fHorario)
		{
			$consulta="select count(*) from 3106_asignacionHorarioRuta where fecha='".$fecha."'";
			$nReg=$con->obtenerValor($consulta);
			$nReg++;
			$consulta="INSERT INTO 3106_asignacionHorarioRuta(idHorarioAsignacion,idUnidadAsignada,idChoferAsignado,fecha,horario,orden,situacion)
						values(".$fHorario[0].",".$idUnidad.",".$idChofer.",'".$fecha."','".$fHorario[1]."',".$nReg.",2)";
			$con->ejecutarConsulta($consulta);
			$idAsignacion=$con->obtenerUltimoID();	
			$resultado["idAsignacion"]=$idAsignacion;
			$resultado["idHorarioEjecucion"]=$fHorario[0];
			
			$consulta="SELECT p.horario,r.nombreRuta  FROM 3105_horarioEjecucionRuta h,3103_horariosPerfilRuta p,3100_rutasTransportes r 
						WHERE h.idHorarioEjecucion=".$resultado["idHorarioEjecucion"]." AND  p.idHorarioPerfil=h.idHorario AND r.idRuta=h.idRuta";
			$fRuta=$con->obtenerPrimeraFila($consulta);
			$resultado["rutaAsignada"]='['.$fRuta[0].'] '.cv($fRuta[1]);
		}
		return $resultado;
		
	}	
	
	function choferPresentaAdeudos($idChofer)
	{
		global $con;
		$resultado["resultado"]=0;
		$consulta="SELECT SUM(saldoActual) FROM 3113_adeudosChofer WHERE idChofer=".$idChofer." AND  situacion=1 AND tipoConceptoAdeudo NOT IN (2)";
		$saldo=$con->obtenerValor($consulta);	
		if($saldo=="")
			$saldo=0;
		if($saldo>0)
		{
			$resultado["resultado"]=1;
			$resultado["mensaje"]="El chofer presenta un adeudo de $ ".number_format($saldo,2);
			
		}
		return $resultado;
	}	
	
	function obtenerIdUnidadNumEconomico($noEconomico)
	{
		global $con;	
		
		$consulta="SELECT id__1012_tablaDinamica FROM _1012_tablaDinamica WHERE numEconomico=".$noEconomico." AND idEstado=2";
		$idUnidad=$con->obtenerValor($consulta);
		if($idUnidad=="")
			$idUnidad=-1;
		return $idUnidad;
	}
	
	function obtenerIdChofer($idUnidad,$fecha,$horario="")
	{
		global $con;
		$consulta="SELECT idChofer FROM 3119_unidadesChoferesFecha WHERE idUnidad=".$idUnidad." and fecha='".$fecha."'";
		if($horario!="")	
			$consulta.=" AND horaVigencia>='".$horario."'";
		else
			$consulta.=" order by idUnidadChofer desc";
		$idChofer=$con->obtenerValor($consulta);
		if($idChofer=="")
			$idChofer=-1;
		return $idChofer;
	}	
	
	function obtenerComentariosAccionOperacion($idAccionOperacion,$noEconomico,$fecha,$idHorarioEjecucion,$idPuntoControl,$idChofer=-1)
	{
		global $con;	
		$cTmp='{"fecha":"","idPuntoControl":"","idUnidad":"","idChofer":"","idHorarioEjecucion":"","horario":""}';
		$objTmp=json_decode($cTmp);
		$objTmp->fecha=$fecha;
		$objTmp->idUnidad=$noEconomico;
		if($noEconomico>0)
			$objTmp->idUnidad=obtenerIdUnidadNumEconomico($noEconomico);
		else
			$objTmp->idUnidad=$noEconomico*-1;
			

			
		$objTmp->idHorarioEjecucion=$idHorarioEjecucion;
		$consulta="SELECT horario,idPuntoPaseLista FROM 3105_horarioEjecucionRuta h,3103_horariosPerfilRuta p WHERE idHorarioEjecucion=".$idHorarioEjecucion." AND p.idHorarioPerfil=h.idHorario";
		$fHorarioEjecucion=$con->obtenerPrimeraFila($consulta);
		$horario=$fHorarioEjecucion[0];
		$objTmp->horario=$horario;
		$objTmp->idPuntoControl=explode(",",$idPuntoControl);
		if($idChofer==-1)
			$idChofer=obtenerIdChofer($objTmp->idUnidad,$objTmp->fecha,$horario);
		if($idChofer=="")
			$idChofer=-1;
		

		$objTmp->idChofer=$idChofer;
		if($objTmp->idChofer=="")
			$objTmp->idChofer=-1;
		$cache=NULL;
		$arrComentarios=array();
		
		$consulta="SELECT funcionEvaluacion,accion,tipoEvaluacion FROM _1050_gridFuncionesEvaluacion WHERE idReferencia=".$idAccionOperacion;
		$resFunciones=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($resFunciones))
		{
			
			$oResp=resolverExpresionCalculoPHP($fila[0],$objTmp,$cache);	
			
			if($oResp["resultado"]==1)
			{
				$resp=array();	
				
				$resp["tipoValidacion"]="Validación de chofer";
				$resp["idEntidadValidacion"]=$objTmp->idChofer;
				if($fila[2]==2)
				{
					$resp["tipoValidacion"]="Validación Unidad";
					$resp["idEntidadValidacion"]=$objTmp->idUnidad;
				}
				$resp["tValidacion"]=$fila[2];
				
				
				$resp["mensaje"]=$oResp["mensaje"];
				$resp["accion"]=$fila[1];
				
				$resp["icono"]="";
				if(isset($oResp["icono"]))
					$resp["icono"]=$oResp["icono"];
				array_push($arrComentarios,$resp);
			}
			
		}
		
		
		$arrReg="";
		$numReg=0;
		if(sizeof($arrComentarios)>0)
		{
			foreach($arrComentarios as $c)	
			{
				$o='{"entidadValidacion":"'.$resp["tValidacion"].'","idEntidadValidacion":"'.$resp["idEntidadValidacion"].'","comentario":"'.cv($c["mensaje"]).'","tipoComentario":"'.$c["tipoValidacion"].'","icono":"'.$c["icono"].'","accion":"'.$c["accion"].'"}';	
				if($arrReg=="")
					$arrReg=$o;
				else
					$arrReg.=",".$o;
				$numReg++;
			}
		}
		
		
		return $arrReg;
	}
	
	function obtenerNombreRutaAsignacion($idAsignacion)
	{
		global $con;
		$consulta="SELECT * FROM 3106_asignacionHorarioRuta WHERE idAsignacion=".$idAsignacion;
		
		$fConsulta=$con->obtenerPrimeraFila($consulta);
		$ruta="[No identificada]";
		$nomRuta="";
		if($fConsulta)
		{
			$ruta='['.date("d/m/Y H:i",strtotime($fConsulta[7]." ".$fConsulta[8])).']';
			$consulta="SELECT r.nombreRuta FROM 3105_horarioEjecucionRuta h,3100_rutasTransportes r WHERE idHorarioEjecucion=".$fConsulta[1]." AND r.idRuta=h.idRuta";
			$nomRuta=$con->obtenerValor($consulta);
		}
		
		$ruta.=" ".$nomRuta;
		$oRuta["fecha"]=$fConsulta[7];
		$oRuta["horario"]=$fConsulta[8];
		$oRuta["nombreRuta"]=$ruta;
		return $oRuta;
	}
	
	function asignacionPresentaMarca($idAsignacion,$marca)
	{
		global $con;	
		$consulta="SELECT COUNT(*) FROM 3106_asignacionHorarioRuta a,3105_horarioEjecucionRuta e,3104_marcadoresHorarioPerfilRuta m WHERE idAsignacion=".$idAsignacion." AND e.idHorarioEjecucion=a.idHorarioAsignacion
				AND m.idHorario=e.idHorario AND m.idMarcador=".$marca;
		$nReg=$con->obtenerValor($consulta);
		if($nReg==0)
			return false;
		return true;
	}
	
	function obtenerNumEconomicoIdUnidad($idUnidad)
	{
		global $con;	
		
		$consulta="SELECT numEconomico FROM _1012_tablaDinamica WHERE  id__1012_tablaDinamica=".$idUnidad."";
		$idUnidad=$con->obtenerValor($consulta);
		if($idUnidad=="")
			$idUnidad=-1;
		return $idUnidad;
	}	
	
	function obtenerNombreChofer($idChofer)
	{
		global $con;
		$consulta="SELECT CONCAT(aPaterno,' ',aMaterno,' ',nombre) FROM _1013_tablaDinamica WHERE id__1013_tablaDinamica=".$idChofer;
		$nombre=$con->obtenerValor($consulta);
		return $nombre;
	}
	
	function registrarAbonoAdeudo($idAdeudo,$montoAbono,$tipoAbono,$descripcion="",$comentarios="")
	{
		global $con;
		
		$x=0;
		$consulta[$x]="begin";
		$x++;
		$consulta[$x]="INSERT INTO 3114_abonosChofer(idAdeudoChofer,fechaAbono,montoAbono,responsableRegistro,comentariosAdicionales,tipoAbono,descripcion)
					VALUES(".$idAdeudo.",'".date("Y-m-d H:i:s")."',".$montoAbono.",".$_SESSION["idUsr"].",'".cv($comentarios)."',".$tipoAbono.",'".cv($descripcion)."')";
		
		$x++;
		$consulta[$x]="set @saldoActual:=((select montoAdeudo from 3113_adeudosChofer where idAdeudo=".$idAdeudo.")-(select sum(montoAbono) from 3114_abonosChofer where idAdeudoChofer=".$idAdeudo."))";
		$x++;
		
		$consulta[$x]="set @saldoActual:=if(@saldoActual<=0.001,0,@saldoActual)";
		$x++;
		
		$consulta[$x]="set @situacion:=if(@saldoActual<=0.001,2,1)";
		$x++;
		
		$consulta[$x]="UPDATE 3113_adeudosChofer SET saldoActual=@saldoActual,situacion=@situacion WHERE idAdeudo=".$idAdeudo;
		$x++;
		
		$consulta[$x]="commit";
		$x++;
		
		return $con->ejecutarBloque($consulta);
	}
	
	function registrarAdeudoChofer($idChofer,$montoAdeudo,$tipoAdeudo,$descripcion,$referencia1="",$referencia2="")
	{
		global $con;
		$consulta="INSERT INTO 3113_adeudosChofer(idChofer,fechaAdeudo,montoAdeudo,tipoConceptoAdeudo,referencia1,referencia2,descripcion,situacion,saldoActual)
				VALUES(".$idChofer.",'".date("Y-m-d H:i:s")."',".$montoAdeudo.",".$tipoAdeudo.",'".$referencia1."','".$referencia2."','".cv($descripcion)."',1,".$montoAdeudo.")";
		return $con->ejecutarConsulta($consulta);
			
	}
	
	function registrarArmadoTarjeta(&$query,&$x,$oTarjeta,$idAsignacion)
	{
		global $con;
		
		$consulta="SELECT idChofer,situacion FROM 1108_asignacionTarjetaChofer WHERE idRegistro=".$oTarjeta->idTarjeta;
		$fTarjetaActual=$con->obtenerPrimeraFila($consulta);
		
		$idChofer=$fTarjetaActual[0];
		$situacion=$fTarjetaActual[1];
		
		if($situacion!=2)
		{
			$consulta="SELECT idRegistro FROM 1108_asignacionTarjetaChofer WHERE idChofer=".$idChofer." AND situacion=2 AND idRegistro<>".$oTarjeta->idTarjeta;
			$idTarjetaAnt=$con->obtenerValor($consulta);
			if($idTarjetaAnt!="")
			{
				prepararTarjetaLiquidacion($idTarjetaAnt,$query,$x);
			}
		}
		$query[$x]="UPDATE 3106_asignacionHorarioRuta SET idTarjeta=".$oTarjeta->idTarjeta." WHERE idAsignacion=".$idAsignacion;
		$x++;		
		
		if($situacion!=2)
		{
			$query[$x]	="UPDATE 1108_asignacionTarjetaChofer SET situacion=2 WHERE idRegistro=".$oTarjeta->idTarjeta;
			$x++;
			$query[$x]="set @idAsignacion:=".$oTarjeta->idTarjeta;
			$x++;
			$query[$x]="delete from  1102_detalleTarjetaAsignada where idAsignacion=@idAsignacion";
			$x++;
			
			foreach($oTarjeta->detalleTarjeta as $d)
			{
				$query[$x]="INSERT INTO 1102_detalleTarjetaAsignada(idTalon,costoUnitario,folioInicial,folioFinal,foliosBaja,total,montoTotal,idAsignacion,ultimoFolioVendido)
							VALUES(".$d->folioTalon.",".$d->costoUnitario.",".$d->folioInicial.",".$d->folioFinal.",".$d->foliosBaja.",".$d->total.",".$d->montoTalon.",@idAsignacion,-1)";
				$x++;
				
			}
		}
	}
	
	function prepararTarjetaLiquidacion($idTarjeta,&$query,&$x)
	{
		global $con;	
		
		$consulta="SELECT idChofer FROM 1108_asignacionTarjetaChofer WHERE idRegistro=".$idTarjeta;
		$idChofer=$con->obtenerValor($consulta);
		
		$query[$x]	="UPDATE 1108_asignacionTarjetaChofer SET situacion=5 WHERE idRegistro=".$idTarjeta;
		$x++;
		
		$consulta="SELECT idDetalle,idTalon,folioInicial FROM 1102_detalleTarjetaAsignada WHERE idAsignacion=".$idTarjeta;
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$ultimoFolio=obtenerUltimoBoletoVendido($fila[1],$idChofer);
			
			$query[$x]="UPDATE 1102_detalleTarjetaAsignada SET ultimoFolioVendido=".$ultimoFolio." WHERE idDetalle=".$fila[0];
			$x++;
			
			
		}
		
		
	}
	
	function obtenerUltimoBoletoVendidoAsignacion($idAsignacion)
	{
		global $con;
		$consulta="SELECT folioInicial,folioFinal,folioActual FROM 1105_asignacionBoletosChofer WHERE idAsignacion=".$idAsignacion;
		$fAsignacion=$con->obtenerPrimeraFila($consulta);
		
		if($fAsignacion)
		{
			if($fAsignacion[0]==$fAsignacion[2])
				return -1;
			else
			{
				$ultimoFolio=-1;
				
				for($x=($fAsignacion[2]-1);$x>=$fAsignacion[0];$x--)
				{
					if(obtenerTotalFoliosBaja($idTalon,$x,$x)==0)	
					{
						$ultimoFolio=$x;
						break;
					}
				}
				
				return $ultimoFolio;	
			}
			
		}
		else
			return -1;
	}
	
	function registrarAutorizacionSolicitudCambio($idSolicitud)
	{
		global $con;
		$consulta="SELECT * FROM 3121_solicitudesCambioChofer WHERE idRegistro=".$idSolicitud;
		$fRegistro=$con->obtenerPrimeraFila($consulta);
		$consulta="SELECT * FROM 3106_asignacionHorarioRuta WHERE idAsignacion=".$fRegistro[4];
		$fAsignacion=$con->obtenerPrimeraFila($consulta);
		
		$arrEstadosRegistroTarejtaOriginal[3]=1;
		$arrEstadosRegistroTarejtaOriginal[4]=1;
		$arrEstadosRegistroTarejtaOriginal[12]=1;

		
		$x=0;
		$query[$x]="begin";
		$x++;
		$query[$x]="UPDATE 3121_solicitudesCambioChofer SET situacion=2,fechaAutorizacion='".date("Y-m-d H:i:s")."',idResponsableAutorizacion=".$_SESSION["idUsr"]." WHERE idRegistro=".$idSolicitud;
		$x++;
		$query[$x]="UPDATE 1108_asignacionTarjetaChofer SET situacion=5 WHERE idChofer=".$fRegistro[3]." AND situacion=2 AND idRegistro<>".$fRegistro[6];
		$x++;
		$query[$x]="UPDATE 1108_asignacionTarjetaChofer SET situacion=2 WHERE idRegistro=".$fRegistro[6];
		$x++;
		$query[$x]="UPDATE 3106_asignacionHorarioRuta SET idChoferAsignado=".$fRegistro[3].",idTarjeta=".$fRegistro[6]." WHERE idAsignacion=".$fRegistro[4];
		$x++;
		
		if(isset($arrEstadosRegistro[($fAsignacion[11]*1)]))
		{
			$query[$x]="INSERT INTO 3122_tarjetasAsignadasRecorridosInconclusos(idAsignacionTarjeta,idAsignacionRuta,idSolicitudCambio) values(".$fAsignacion[10].",".$fAsignacion[0].",".$idSolicitud.")";
			$x++;
		}
		
		$query[$x]="commit";
		$x++;
		if($con->ejecutarBloque($query))
		{
			$consulta="SELECT sancionAplica FROM _1049_gridSanciones WHERE idReferencia=".$fRegistro[5];
			$lSanciones=$con->obtenerListaValores($consulta);
			if($lSanciones=="")
				$lSanciones=-1;
			$cache=NULL;
			$cTmp='{"tipoElemento":"","idElemento":"","idMotivoCambio":"","idRegistro":""}';
			$objTmp=json_decode($cTmp);	
			$objTmp->idMotivoCambio=$fRegistro[5];
			$objTmp->idRegistro=$idSolicitud;
				
			$consulta="SELECT funcionAccion,aplicableA FROM _1025_tablaDinamica WHERE id__1025_tablaDinamica IN (".$lSanciones.")";
			$rFunciones=$con->obtenerFilas($consulta);
			while($fFunciones=mysql_fetch_row($rFunciones))
			{
				$objTmp->tipoElemento=$fFunciones[1];
				switch($objTmp->tipoElemento)
				{
					case 1: //CHofer
						$objTmp->idElemento=$fRegistro[2];
					break;
					case 2: //Unidad
						$objTmp->idElemento=$fRegistro[1];
					break;	
				}
				if(($objTmp->idElemento!="")&&($objTmp->idElemento!="-1"))
					$oResp=resolverExpresionCalculoPHP($fFunciones[0],$objTmp,$cache);		
			}
			
			
			return true;
		}
		return false;
	}
	
	function obtenerRegistrosBoletoPuntosControlTarjeta($idTalon,$idAsignacionTarjeta)
	{
		global $con;	
		$arrRegistros=array();
		$consulta="SELECT p.idPuntoControlEjecucion FROM 3106_asignacionHorarioRuta a,3109_puntosControlRutaEjecucion p WHERE idTarjeta=".$idAsignacionTarjeta." AND a.idAsignacion=p.idAsignacion";
		$lPuntosControl=$con->obtenerListaValores($consulta);
		if($lPuntosControl=="")
			$lPuntosControl=-1;
		
		$consulta="SELECT * FROM 3110_tarjetaRegistroPuntoControl WHERE idPuntoControlRuta IN (".$lPuntosControl.") and folioTalon=".$idTalon."  order by idRegistro";
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			array_push($arrRegistros,$fila)	;
		}
		return $arrRegistros;
		
	}
	
?>