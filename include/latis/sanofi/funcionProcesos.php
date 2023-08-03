<?php
	include("latis/conexionBD.php");
	include_once("latis/conectorMail/cSendMail.php");
	
	function seleccionarRutaContinuacion($idFormulario,$idRegistro)
	{
		global $con;
		$consulta="SELECT resultadoPrueba,idReferencia,tipoServicioGratuito,gratuidadServicios FROM _1039_tablaDinamica WHERE id__1039_tablaDinamica=".$idRegistro;	
		$fResultado=$con->obtenerPrimeraFila($consulta);
		$resultado=$fResultado[0];
		$idReferencia=$fResultado[1];
		$etapa="";
		switch($resultado)
		{
			case 1:
			
				if($fResultado[3]==1)
				{
					switch($fResultado[2])
					{
						case 3:
							$etapa="2";
						break;
						default:
							$etapa="2.7";
						break;	
					}
				}
				else
					$etapa="2";
				
			break;	
			case 2:
				$etapa="1.5";
			break;
		}
		
		return cambiarEtapaFormulario(1022,$idReferencia,$etapa);
		
	}
	
	
	function seleccionarAsistenciaConsulta($idFormulario,$idRegistro)
	{
		global $con;
		$consulta="SELECT * FROM _1028_tablaDinamica WHERE id__1028_tablaDinamica=".$idRegistro;	
		$fRegistro=$con->obtenerPrimeraFila($consulta);
		$etapa="";
		if($fRegistro[16]==0)
			$etapa="2.1";
		else
			if($fRegistro[10]==0)
			{
				if($fRegistro[14]==0)
					$etapa="2.2";
				else
					$etapa="2.5";
			}
			else
			{
				$etapa=3;
			}

		
		return cambiarEtapaFormulario(1022,$fRegistro[1],$etapa,$fRegistro[12],-1,$idFormulario,$idRegistro);
		
	}
	
	function seleccionarAsistenciaEntregaResultadoAPE($idFormulario,$idRegistro)
	{
		global $con;
		$consulta="SELECT * FROM _1030_tablaDinamica WHERE id__1030_tablaDinamica=".$idRegistro;	
		$fRegistro=$con->obtenerPrimeraFila($consulta);
		$etapa="";
		if($fRegistro[17]==0)
			$etapa="3.1";
		else
			if($fRegistro[10]==0)
			{
				$etapa="3.5";
			}
			else
			{
				if($fRegistro[12]==0)
				{
					$etapa="3.2";
				}
				else
				{
					if($fRegistro[14]==0)
					{
						$etapa="3.6";
					}
					else
					{
						$etapa="4";
					}
				}
			}

		
		return cambiarEtapaFormulario(1022,$fRegistro[1],$etapa,$fRegistro[16],-1,$idFormulario,$idRegistro);
		
	}
	
	function seleccionarAsistenciaEstudioBiopsia($idFormulario,$idRegistro)
	{
		global $con;
		$consulta="SELECT * FROM _1032_tablaDinamica WHERE id__1032_tablaDinamica=".$idRegistro;	
		$fRegistro=$con->obtenerPrimeraFila($consulta);
		$etapa="";
		if($fRegistro[10]==0)
			$etapa="4.1";
		else
			if($fRegistro[12]==1)
			{
				$etapa="5";
			}
			else
			{
				if($fRegistro[14]==0)
				{
					$etapa="4.2";
				}
				else
				{
					$etapa="4.5";
					
				}
			}

		
		return cambiarEtapaFormulario(1022,$fRegistro[1],$etapa,$fRegistro[16],-1,$idFormulario,$idRegistro);
		
	}
	
	function seleccionarResultadoEstudioBiopsia($idFormulario,$idRegistro)
	{
		global $con;
		$consulta="SELECT * FROM _1033_tablaDinamica WHERE id__1033_tablaDinamica=".$idRegistro;	
		$fRegistro=$con->obtenerPrimeraFila($consulta);
		$etapa="6";
		if($fRegistro[11]==0)
		{
			$etapa="5.1";
		}
		
		return cambiarEtapaFormulario(1022,$fRegistro[1],$etapa,$fRegistro[12],-1,$idFormulario,$idRegistro);
		
	}
	
	
	function registroAccionRealizado($idFormulario,$idRegistro)
	{
		global $con;
		$consulta="SELECT fechaCreacion,siguienteAccion,unidadProxContacto,intervaloProxContacto FROM _1035_tablaDinamica WHERE id__1035_tablaDinamica=".$idRegistro;	
		$fRegistro=$con->obtenerPrimeraFila($consulta);
		
		if($fRegistro[1]==1)
		{
			$intervalo="";
			switch($fRegistro[3])
			{
				case 1:
					$intervalo="minutes";
				break;
				case 2:
					$intervalo="hours";
				break;
				case 3:
					$intervalo="days";
				break;
			}
			$proxAccion=date("Y-m-d H:i",strtotime("+ ".$fRegistro[2]." ".$intervalo,strtotime($fRegistro[0])));	
			
			$consulta="UPDATE _1035_tablaDinamica SET ctrlProximoContacto='".$proxAccion."' WHERE id__1035_tablaDinamica=".$idRegistro;
			return $con->ejecutarConsulta($consulta);
			
		}
		return true;
	}
	
	function registroReagendaCita($idFormulario,$idRegistro)
	{
		global $con;
		$etapa="";
		
		
		
		$consulta="SELECT * FROM _1037_tablaDinamica WHERE id__1037_tablaDinamica=".$idRegistro;	
		$fRegistro=$con->obtenerPrimeraFila($consulta);
		$consulta="SELECT idEstado FROM _1022_tablaDinamica WHERE id__1022_tablaDinamica=".$fRegistro[1];	
		$idEstado=$con->obtenerValor($consulta);
		
		switch($idEstado)
		{
			case "2.10":
				$etapa="2";
			break	;
			case "3.10":
				$etapa="3";
			break	;
			case "4.10":
				$etapa="4";
			break	;
			case "4.20":
				$etapa="4";
			break	;
			case "5.10":
				$etapa="5";
			break	;
		}
		return cambiarEtapaFormulario(1022,$fRegistro[1],$etapa,$fRegistro[11],-1,$idFormulario,$idRegistro);
	}
	
	function verificarAsignacionSupervisor($idRegistro)
	{
		global $con;
		$consulta="SELECT idReferencia FROM 3020_seguimientoPaciente WHERE idCuestionarioSeguimiento=".$idRegistro;
		$idPaciente=$con->obtenerValor($consulta);
		$consulta="SELECT COUNT(*) FROM 3021_responsableSeguimientoPaciente WHERE idPaciente=".$idPaciente." AND fechaBaja IS NULL";
		$nReg=$con->obtenerValor($consulta);
		if($nReg==0)
		{
			$consulta="INSERT INTO 3021_responsableSeguimientoPaciente(idResponsableSeguimiento,fechaAsignacion,idPaciente) values(".$_SESSION["idUsr"].",'".date("Y-m-d H:i:s")."',".$idPaciente.")";
			return $con->ejecutarConsulta($consulta);	
		}
		return true;
	}
	
	
	function intepretarEstudioTratamiento($tipoEstudio,$idFormulario,$idRegistro)
	{
		global $con;
		$descripcion="";
		switch($tipoEstudio)
		{
			case 1://Antígeno Prostático Específico (APE)
				$consulta="SELECT nivelAPE FROM _1042_tablaDinamica WHERE id__1042_tablaDinamica=".$idRegistro;
				$resultado=$con->obtenerValor($consulta);
				$descripcion="Nivel APE: ".number_format($resultado,2)." ng/ml";
			break;
			case 2://Tacto Rectal (TR)
				$consulta="SELECT resultadoPrueba FROM _1043_tablaDinamica WHERE id__1043_tablaDinamica=".$idRegistro;
				$resultado=$con->obtenerValor($consulta);
				
				$consulta="SELECT contenido FROM 902_opcionesFormulario WHERE valor=".$resultado." AND idGrupoElemento=9038";
				$resultado=$con->obtenerValor($consulta);
				
				$descripcion="Resultado: ".$resultado."";
			break;
			case 3://Biopsia
				$consulta="SELECT sumaGleason,muestrasBiopsia FROM _1044_tablaDinamica WHERE id__1044_tablaDinamica=".$idRegistro;
				$fResultado=$con->obtenerPrimeraFila($consulta);
				$descripcion="Suma Gleas&oacute;n: ".number_format($fResultado[0],2).", No. muestras: ".number_format($fResultado[1],0);
			
			break;
		}
		return "'".$descripcion."'";
	}
	
	function obtenerEstudiosTratamientosPaciente($idPaciente,$idEstudioTratamiento)
	{
		global $con;	
		$arrEstudios=array();
		
		switch($idEstudioTratamiento)
		{
			case 1:
				$consulta="select * from ((SELECT nivelAPE,'1' as prioridad,id__1030_tablaDinamica as idRegistro,densidadAPE FROM _1030_tablaDinamica WHERE idReferencia=".$idPaciente." AND asitioCita=1)
						UNION
						(SELECT nivelAPE,'2' as prioridad,id__1042_tablaDinamica as idRegistro,densidadAPE FROM _1042_tablaDinamica WHERE idReferencia=".$idPaciente." )) as tmp order by prioridad asc,idRegistro asc";

				$res=$con->obtenerFilas($consulta);
				while($fila=mysql_fetch_row($res))
				{
					$o= array();
					$o["nivelAPE"]=$fila[0];
					$o["densidadAPE"]=$fila[3];
					array_push($arrEstudios,$o);
				}
			break;
			case 2:
				$consulta="SELECT resultadoPrueba FROM _1043_tablaDinamica WHERE idReferencia=".$idPaciente." ORDER BY id__1043_tablaDinamica ASC";
				$res=$con->obtenerFilas($consulta);
				while($fila=mysql_fetch_row($res))
				{
					$o= array();
					$o["resultado"]=$fila[0];
					array_push($arrEstudios,$o);
				}
			break;
			case 3:
				$consulta="select * from ((SELECT sumaGleason,muestrasBiopsia,'1033' AS idFormulario,id__1033_tablaDinamica,'1' as prioridad,id__1033_tablaDinamica as idRegistro FROM _1033_tablaDinamica WHERE idReferencia=".$idPaciente." AND asistioCita=1)
							UNION
							(SELECT sumaGleason,muestrasBiopsia,'1044' AS idFormulario,id__1044_tablaDinamica,'2' as prioridad,id__1044_tablaDinamica as idRegistro FROM _1044_tablaDinamica WHERE idReferencia=".$idPaciente." )) as tmp order by prioridad asc,idRegistro asc";
				$res=$con->obtenerFilas($consulta);
				while($fila=mysql_fetch_row($res))
				{
					$o= array();
					$o["sumaGleason"]=$fila[0];
					$o["muestrasBiopsia"]=$fila[1];
					$o["muestras"]=array();
					switch($fila[2])
					{
						case 1033:
							$consulta="SELECT noMuestra,resultado,porcentajeCancer FROM _1033_gridMuestrasBiosia WHERE idReferencia=".$fila[3]." ORDER BY noMuestra";

						break;
						case 1044:
							$consulta="SELECT noMuestras,resutado,porcentajeCancer FROM _1044_gridMuestrasBiopsia WHERE idReferencia=".$fila[3]." ORDER BY noMuestras";
						
						break;	
					}
					
					$resMuestras=$con->obtenerFilas($consulta);
					while($filaMuestras=mysql_fetch_row($resMuestras))
					{
						$oMuestra=array();
						$oMuestra["noMuestras"]=$filaMuestras[0];
						$oMuestra["resultado"]=$filaMuestras[1];
						$oMuestra["porcentaje"]=$filaMuestras[2];
						array_push($o["muestras"],$oMuestra);
					}
					array_push($arrEstudios,$o);
				}
			break;	
		}
		
		return $arrEstudios;
	}
	
	
	function registrarDiagnosticoPaciente($obj)
	{
		global $con;	
		$x=0;
		$query[$x]="begin";
		$x++;
		
		$query[$x]="INSERT INTO 3023_evaluacionPaciente(riezgoPaciente,llaveDiagnostico,fechaDiagnostico,idPaciente) VALUES(".$obj["riesgoPaciente"].",'','".date("Y-m-d H:i:s")."',".$obj["idPaciente"].")";
		$x++;
		
		$query[$x]="set @idDiagnostico:=(select last_insert_id())";
		$x++;
		if(sizeof($obj["estudios"])>0)
		{
			foreach($obj["estudios"] as $e)
			{
				$query[$x]="INSERT INTO 3024_estudiosTratamientosRecomendadosDiagnostico(tipo,idEstudioDiagnostico,idDiagnostico) VALUES(".$e["tipo"].",".$e["idEstudioDiagnostico"].",@idDiagnostico)";
				$x++;
			}
		}
		
		if(sizeof($obj["recomendaciones"])>0)
		{
			foreach($obj["recomendaciones"] as $r)
			{
				$query[$x]="INSERT INTO 3025_recomendacionesDiagnosticoPaciente(recomendacion,idDiagnostico) VALUES('".cv($r["recomendaciones"])."',@idDiagnostico)";
				$x++;
			}
		}
		
		
		
		$query[$x]="commit";
		$x++;
		return $con->ejecutarBloque($query);
		
	}
	
	function registrarSeguimientoEmail($idRegistro,$listMail)
	{
		global $con;
		if($listMail=="")
			$listMail=-1;
		$situacion=0;
		$consulta="SELECT contactoPaciente FROM 3020_seguimientoPaciente WHERE idCuestionarioSeguimiento=".$idRegistro;
		$fRegistro=$con->obtenerPrimeraFila($consulta);
		if($fRegistro[0]==1)
			$situacion=2;
		else
			$situacion=3;
		$x=0;
		$query[$x]="begin";
		$x++;
		$query[$x]="UPDATE 3026_mensajesContactoPaciente SET situacion=".$situacion." WHERE idReferenciaContacto=".$idRegistro;
		$x++;
		$query[$x]="UPDATE 3027_eMailRecibidos SET idReferencia=(select idMensaje from 3026_mensajesContactoPaciente where idReferenciaContacto=".$idRegistro."),tipoReferencia=1,situacion2=1 WHERE idEmail IN (".$listMail.")";
		$x++;
		$query[$x]="commit";
		$x++;
		
		return $con->ejecutarBloque($query);
		
	}
	
	function verificarMailPacientes()
	{
		global $con;
		
		
		$rutaTmp=$baseDir."/archivosTemporales/";
		$mail="contacto@unossegundos.org";
		$passwd="campania1segundos";
		$url="{mail.unossegundos.org:143/novalidate-cert}";
		
		$c=new cSendMail($url,$mail,$passwd);

		if($c->conectarServidor())
		{
			$c->prepararEstructuraBuzonLatis();
			
			$arrDirectoriosDisponibles=$c->obtenerDirectorioBuzon();
			foreach($arrDirectoriosDisponibles as $d)
			{
				if(($d->name!=$url."INBOX.ProcesadosLatis")&&($d->name!=$url."INBOX.ProcesadosLatis.CorreosVarios"))
				{
					$arrMail=$c->obtenerCorreosBandeja($d->name);	
					foreach($arrMail as $m)
					{
						if($c->registrarMailPlataforma($m)!==false)
						{
							
							$c->moverCorreoBuzon($m,"INBOX.ProcesadosLatis");	
						}
					}
				}		
			}
			
			$c->cerrarConexionServidor();		
		}
		
		return true;
	}
	
	function obtenerEstadificacionCancerProstata()
	{
		global $con;	
		$arrRegistros=array();
		$consulta="SELECT claveElemento,nombreElemento FROM 1018_catalogoVarios WHERE tipoElemento=29";
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$o["id"]=$fila[0];
			$o["valor"]=$fila[1];
			array_push($arrRegistros,$o);
				
		}
		return $arrRegistros;
	}
	
	function obtenerValores0_1CancerProstata()
	{
		global $con;	
		$arrRegistros=array();
		$consulta="SELECT claveElemento,nombreElemento FROM 1018_catalogoVarios WHERE tipoElemento=34";
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$o["id"]=$fila[0];
			$o["valor"]=$fila[1];
			array_push($arrRegistros,$o);
				
		}
		return $arrRegistros;
	}
	
	function obtenerSumaGleason($idPaciente)
	{
		global $con;	
		$gleason="";
		$arrBiopsia=obtenerEstudiosTratamientosPaciente($idPaciente,3);
		
		if(sizeof($arrBiopsia)>0)
		{
			$ultimaBiopsia=$arrBiopsia[sizeof($arrBiopsia)-1];	
			$gleason=$ultimaBiopsia["sumaGleason"];
		}
		if($gleason=="")
			$gleason=0;
		return $gleason;
	}
	
	function obtenerNivelAPE($idPaciente)
	{
		global $con;
		$nivelAPE="";	
		$arrAPE=obtenerEstudiosTratamientosPaciente($idPaciente,1);
		
		if(sizeof($arrAPE)>0)
		{
			$nivelAPE=$arrAPE[sizeof($arrAPE)-1]	["nivelAPE"];
			
		}
		
		if($nivelAPE=="")
			$nivelAPE=0;
		return $nivelAPE;
		
		
	}
		
	function obtenerDensidadAPE($idPaciente)
	{
		global $con;	
		$densidadAPE="";	
		$arrAPE=obtenerEstudiosTratamientosPaciente($idPaciente,1);
		
		if(sizeof($arrAPE)>0)
		{
			$densidadAPE=$arrAPE[sizeof($arrAPE)-1]["densidadAPE"];
			
		}
		
		if($densidadAPE=="")
			$densidadAPE=0;
		
		return $densidadAPE;
	}
	
	function validacionBiopsias($idPaciente)
	{
		$arrBiopsia=obtenerEstudiosTratamientosPaciente($idPaciente,3);
		$cumpleCriterioBiopsia=0;	
		if(sizeof($arrBiopsia)>0)
		{
			$ultimaBiopsia=$arrBiopsia[sizeof($arrBiopsia)-1];
			
			$nBiopsias=0;
			foreach($ultimaBiopsia as $m)
			{
				if(($m["resultado"]==1)	&&($m["porcentaje"]<50))
				{
					$nBiopsias++;	
				}
			}
			if($nBiopsias<3)
				$cumpleCriterioBiopsia=1;	
		}
		
		return $cumpleCriterioBiopsia;
		
	}
	
	
	function obtenerNumeroMuestrasDiagnosticoUltimaBiopsia($idPaciente)
	{
		$numMuestras=0;
		
		$arrBiopsia=obtenerEstudiosTratamientosPaciente($idPaciente,3);
		if(sizeof($arrBiopsia)>0)
		{
			$ultimaBiopsia=$arrBiopsia[sizeof($arrBiopsia)-1];	
			$numMuestras=sizeof($ultimaBiopsia["muestras"]);
			
		}	
		return $numMuestras;
	}
	
	function actualizarValoresEstudio($idFormulario,$idRegistro)
	{
		global $con;
		$x=0;
		$query[$x]="begin";
		$x++;
		
		$consulta="select * from _".$idFormulario."_tablaDinamica where id__".$idFormulario."_tablaDinamica=".$idRegistro;
		$fRegistro=$con->obtenerPrimeraFila($consulta);
		switch($idFormulario)
		{
			case 1042: //APE
				$query[$x]="UPDATE 3031_parametrosPaciente SET valor=".$fRegistro[10]." WHERE idPaciente=".$fRegistro[1]." AND idParametro IN (1,2,3)";
				$x++;
				$query[$x]="UPDATE 3031_parametrosPaciente SET valor=".$fRegistro[13]." WHERE idPaciente=".$fRegistro[1]." AND idParametro IN (4)";
				$x++;
				
			break;
			case 1044: //Biopsia
				$query[$x]="UPDATE 3031_parametrosPaciente SET valor=".$fRegistro[11]." WHERE idPaciente=".$fRegistro[1]." AND idParametro IN (2,9,15)";
				$x++;
			
			break;
			
		}
		
		$query[$x]="commit";
		$x++;
		return $con->ejecutarBloque($query);
	}
	
	//CancerMama
	function obtenerEstadificacionCancerMama()
	{
		global $con;	
		$arrRegistros=array();
		$consulta="SELECT claveElemento,nombreElemento FROM 1018_catalogoVarios WHERE tipoElemento=35 order by nombreElemento";
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$o["id"]=$fila[0];
			$o["valor"]=$fila[1];
			array_push($arrRegistros,$o);
				
		}
		return $arrRegistros;
	}
	
	function obtenerValoresGangliosLinfaticosCancerMama()
	{
		global $con;	
		$arrRegistros=array();
		$consulta="SELECT claveElemento,nombreElemento FROM 1018_catalogoVarios WHERE tipoElemento=36 order by nombreElemento";
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$o["id"]=$fila[0];
			$o["valor"]=$fila[1];
			array_push($arrRegistros,$o);
				
		}
		return $arrRegistros;
	}
	
	
	function obtenerValoresMetastasisCancerMama()
	{
		global $con;	
		$arrRegistros=array();
		$consulta="SELECT claveElemento,nombreElemento FROM 1018_catalogoVarios WHERE tipoElemento=37 order by nombreElemento";
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$o["id"]=$fila[0];
			$o["valor"]=$fila[1];
			array_push($arrRegistros,$o);
				
		}
		return $arrRegistros;
	}
	
	
?>