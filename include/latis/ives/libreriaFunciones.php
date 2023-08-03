<?php 
	include_once("latis/conexionBD.php"); 
	
	function gruposVisiblesProfesor_ives_neocommunity_org($idUsuario,$idCiclo,$idPeriodo)
	{
		global $con;
		
		$listGrupos=-1;
		
		$consulta="SELECT liberado FROM 3010_contratosLiberados WHERE idUsuario=".$idUsuario." AND idCiclo=".$idCiclo." AND idPeriodo=".$idPeriodo;
		$liberado=$con->obtenerValor($consulta);
		$liberado=1;
		if($liberado==1)
		{
			$consulta="SELECT idGrupos FROM 4520_grupos g,4519_asignacionProfesorGrupo a WHERE g.idCiclo=".$idCiclo." AND g.idPeriodo=".$idPeriodo." and a.idUsuario=".$idUsuario."
					AND a.idGrupo=g.idGrupos AND fechaAsignacion<fechaBaja AND a.esperaContrato=0";
				
			$consulta="SELECT idGrupos FROM 4520_grupos g,4519_asignacionProfesorGrupo a WHERE g.idCiclo=".$idCiclo." AND g.idPeriodo=".$idPeriodo." and a.idUsuario=".$idUsuario."
					AND a.idGrupo=g.idGrupos AND fechaAsignacion<fechaBaja";	
			$listGrupos=$con->obtenerListaValores($consulta);
			if($listGrupos=="")
				$listGrupos=-1;
		}
		$cadAux=" and g.idGrupos in (".$listGrupos.")";
		
		return $cadAux;
	}
	
	function generarReferenciaDigitoVerificador($referencia)
	{
		$digito=2;
		$total=0;
		$tam=strlen($referencia)-1;
		for($x=$tam;$x>=0;$x--)
		{
			$resultado=$referencia[$x]*$digito;
			if($resultado>=10)
			{
				$resultado=$resultado." ";
				$resultado=$resultado[0]+$resultado[1];
			}
			$total+=$resultado;	
			
			if($digito==2)
				$digito=1;
			else
				$digito=2;
		}
		
		$resto=$total%10;
		if($resto==0)
			$digito=0;
		else
			$digito=10-$resto;
		return $referencia.$digito;
	}
	
	function generarMatriculaIVES($idFormulario,$idReferencia)
	{
		global $con;
		$consulta="SELECT idCiclo,idPeriodo,idInstanciaPlan,txtMatricula FROM _678_tablaDinamica WHERE id__678_tablaDinamica=".$idReferencia;
		$fDatos=$con->obtenerPrimeraFila($consulta);
		
		if($fDatos[3]!="")
			return true;
		
		$consulta="SELECT nombreCiclo FROM 4526_ciclosEscolares WHERE idCiclo=".$fDatos[0];
		$ciclo=$con->obtenerValor($consulta);
		
		$matricula=$ciclo;
		
		$consulta="SELECT prioridad FROM _464_gridPeriodos WHERE id__464_gridPeriodos=".$fDatos[1];
		$periodo=$con->obtenerValor($consulta);
		
		$matricula.=$periodo;
		
		$consulta="SELECT p.idProgramaEducativo,p.idPlanEstudio,p.folioPlan  FROM 4513_instanciaPlanEstudio i,4500_planEstudio p WHERE i.idInstanciaPlanEstudio=".$fDatos[2]." AND p.idPlanEstudio=i.idPlanEstudio";
		$fDatosPlan=$con->obtenerPrimeraFila($consulta);
		
		$consulta="SELECT cveProgramaEducativo FROM 4500_programasEducativos WHERE idProgramaEducativo=".$fDatosPlan[0];
		$nivel=$con->obtenerValor($consulta);
		
		$matricula.=$nivel;
		
		$matricula.=$fDatosPlan[2];
		
		$matricula.=$periodo;
		
		$consulta="SELECT idProgramaEducativo FROM 4500_programasEducativos WHERE cveProgramaEducativo='".$nivel."'";

		$lProgramas=$con->obtenerListaValores($consulta);
		
		$consulta="SELECT idPlanEstudio FROM 4500_planEstudio WHERE idProgramaEducativo IN(".$lProgramas.")";
		$lPlanes=$con->obtenerListaValores($consulta);
		
		$consulta="SELECT idInstanciaPlanEstudio FROM  4513_instanciaPlanEstudio WHERE idPlanEstudio IN(".$lPlanes.")";
		$lInstancias=$con->obtenerListaValores($consulta);
		
		$consulta="SELECT MAX(consecutivo) FROM _678_tablaDinamica WHERE idCiclo=".$fDatos[0]." AND idPeriodo=".$fDatos[1]." AND idInstanciaPlan IN(".$lInstancias.")";
		$maxConsecutivo=$con->obtenerValor($consulta);
		if($maxConsecutivo=="")
			$maxConsecutivo=0;
		
		$maxConsecutivo++;
		
		$matricula.=str_pad($maxConsecutivo,"3","0",STR_PAD_LEFT);
		
		$consulta="UPDATE _678_tablaDinamica SET txtMatricula='".$matricula."',consecutivo=".$maxConsecutivo." WHERE id__678_tablaDinamica=".$idReferencia;
		return $con->ejecutarConsulta($consulta);
	}
	
	function generarPagoReferenciadoInscripcionIVES($idRegistro)
	{
		global $con;

		$idConcepto=6;
				
		$consulta="SELECT idReferencia,codigoInstitucion,idGradoInscribe,idInstanciaPlan,idCiclo,idPeriodo,fechaCreacion,idUsuario FROM _678_tablaDinamica WHERE id__678_tablaDinamica=".$idRegistro;
		
		$fSolicitud=$con->obtenerPrimeraFila($consulta);

		$idReferencia=$fSolicitud[0];
		$plantel=$fSolicitud[1];
		$idInstancia=$fSolicitud[3];
		$idCiclo=$fSolicitud[4];
		$idPeriodo=$fSolicitud[5];
		
/*		$consulta="SELECT * FROM 4573_solicitudesInscripcion WHERE idSolicitudInscripcion=".$idReferencia;
		$fRegistro=$con->obtenerPrimeraFila($consulta);
		$objInscripcion=json_decode($fRegistro[3]);
*/		$objDatos=array();
		$arrFechasPago=array();
		
		$consulta="SELECT p.idProgramaEducativo FROM 4513_instanciaPlanEstudio i,4500_planEstudio p WHERE p.idPlanEstudio=i.idPlanEstudio AND i.idInstanciaPlanEstudio=".$idInstancia;
		$idProgramaEducativo=$con->obtenerValor($consulta);
		
			
		$idGrado=$fSolicitud[2];
		
		
		$arrDimensiones=array();
		$arrDimensionesCosto["plantel"]=$plantel;
		$arrDimensionesCosto["idProgramaEducativo"]=$idProgramaEducativo;
		$arrDimensionesCosto["idInstanciaPlanEstudio"]=$idInstancia;
		$arrDimensionesCosto["grado"]=$idGrado;
		
		$oParametroCosto=array();
		$oParametroCosto["idUsuario"]=$fRegistro[2];
		$idPlanPagosAplica=-1;//Si -1 Aplica todos los planes
		
		

		$tabulador=generarTabuladorCostoServicio($idConcepto,$idCiclo,$idPeriodo,$arrDimensionesCosto,$oParametroCosto); 
		
		$consulta="SELECT idDescuento,tipoDescuento FROM 3015_descuentoAplicablePromocion WHERE idFormulario=678 AND 
				idReferencia=".$idRegistro." AND descuentoAplicable=1";
		$fDescuento=$con->obtenerPrimeraFila($consulta);
		
		$montoDescuento=0;
		$lblDescuento="";
		
		if($fDescuento)
		{
			$consulta="SELECT p.idProgramaEducativo,p.idPlanEstudio FROM 4513_instanciaPlanEstudio i,4500_planEstudio p 
			WHERE p.idPlanEstudio=i.idPlanEstudio AND idInstanciaPlanEstudio=".$idInstancia;
			$fDatos=$con->obtenerPrimeraFila($consulta);

			$consulta="SELECT idEstructuraCurricular FROM 4505_estructuraCurricular WHERE idPlanEstudio=".$fDatos[1]." AND  
						idUnidad=".$idGrado." AND tipoUnidad=3"	;
			$idGradoEstructura=$con->obtenerValor($consulta);
			
			
			$idFormularioProm=1058;//promocion
			if($fDescuento[1]==2)
				$idFormularioProm=1057; //beca
			
			$consulta="SELECT idPerfilDescuento FROM 3012_gradosPlanesEstudioDescuento WHERE idFormulario=".$idFormularioProm." AND idReferencia=".$fDescuento[0].
						" AND idInstanciaPlan=".$idInstancia." AND idGrado=".$idGradoEstructura." AND idConcepto=6";

			$idPerfil=$con->obtenerValor($consulta);
	
			if($idPerfil!="")
			{
			
				$consulta="SELECT objComp FROM 3013_perfilesDescuento WHERE idRegistro=".$idPerfil;
				$objComp=$con->obtenerValor($consulta);
				
				
				$descuento=0;
				
				
		
				$fechaCreacion=$fSolicitud[6];
				$fechaActual=strtotime(date("Y-m-d",strtotime($fechaCreacion)));
		
		
				$arrDescuentos=array();
				$arrDatos=explode(":",$objComp);
				
				$arrAux=str_replace("}","",$arrDatos[1]);
				$arrAux=str_replace("[","",$arrAux);
				$arrAux=str_replace("]","",$arrAux);
				
				$arrAux=explode(",",$arrAux);
				
				
				$cadAux="";
				$pos=0;
				$oAux='';
				foreach($arrAux as $a)
				{
					if($pos==0)
					{
						$oAux='{"p":"'.str_replace("'","",$a).'",';
						$pos=1;
					}
					else
					{
						$oAux.='"f":"'.str_replace("'","",$a).'"}';
						$pos=0;
						
					}
					if($pos==0)
					{
						if($cadAux=="")
						
							$cadAux=$oAux;
						else
							$cadAux.=",".$oAux;
					}
				}
				
				$cadAux='{"arrDescuentos":['.$cadAux.']}';
				$oAux=json_decode($cadAux);
		
	
				foreach($oAux->arrDescuentos as $d)
				{
		
					if($d->f!="")	
					{
						if($fechaActual<=strtotime($d->f))
						{
							$descuento=$d->p;
							break;
						}
					}
					else
					{
						$descuento=$d->p;
						break;
					}
				}
				
				if($descuento>0)
				{
					$montoDescuento=($tabulador["montoConcepto"]*($descuento/100));
					
					$tabulador["montoConcepto"]-=$montoDescuento;
					
				}
			}
		}
			

		$arrDimensionesPagoReferenciado=array();
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IdInstanciaPlan",$idInstancia);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDCicloEscolar",$idCiclo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDPeriodo",$idPeriodo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDFormulario",678);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDRegistro",$idRegistro);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"Plantel",$plantel);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDProgramaEducativo",$idProgramaEducativo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDGrado",$idGrado);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDUsuario",$fSolicitud[7]);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDConcepto",$idConcepto);

		$arrTabulador=generarFechasPagosServicio($idConcepto,$tabulador);
		foreach($arrTabulador as $id=>$t);
		{
			foreach($t["arrFechasPago"] as $iF=>$f)
			{
				$arrTabulador[$id]["arrFechasPago"][$iF]["monto"]-=($arrTabulador[$id]["arrFechasPago"][$iF]["monto"]*($descuento/100));
			}
		}
		
		
		
		$seguro=generarTabuladorCostoServicio(94,$idCiclo,$idPeriodo,$arrDimensionesCosto,$oParametroCosto); 
		$arrTabSeguro=generarFechasPagosServicio(94,$seguro);
		$credencial=generarTabuladorCostoServicio(95,$idCiclo,$idPeriodo,$arrDimensionesCosto,$oParametroCosto); 
		$arrTabCredencial=generarFechasPagosServicio(95,$credencial);

		/*if(sizeof($arrTabulador)>0)
		{
			$arrTabulador[0][""]
		}*/
		
		
		if(sizeof($arrTabSeguro)>0)
		{
			$arrTabulador[0]["etiquetaPago"].=", ".$arrTabSeguro[0]["etiquetaPago"];
			$arrTabulador[0]["arrFechasPago"][0]["monto"]+=$arrTabSeguro[0]["arrFechasPago"][0]["monto"];
		}
		
		if(sizeof($arrTabCredencial)>0)
		{
			$arrTabulador[0]["etiquetaPago"].=", ".$arrTabCredencial[0]["etiquetaPago"];
			$arrTabulador[0]["arrFechasPago"][0]["monto"]+=$arrTabCredencial[0]["arrFechasPago"][0]["monto"];
		}
		
		
		generarPagosReferenciadosInscripcionIVES($plantel,$fSolicitud[7],$arrTabulador,$arrDimensionesPagoReferenciado,"pagoInscripcionRealizado",0);
		return true;
	}
	
	function generarPagosReferenciadosInscripcionIVES($idPlantel,$idUsuario,$tabulador,$arrDimensionesPagoReferenciado,$idFuncion="",$tipoFuncion='NULL')
	{
		global $con;
		$objDatos["idUsuario"]=$idUsuario;
		$objDatos["plantel"]=$idPlantel;

		foreach($tabulador as $pago)
		{
			$objDatos["idConcepto"]=$pago["idConcepto"];
			$objDatos["datosPago"]=$pago;
			$datosComp="";
			if(isset($pago["informacionCalculo"]))
				$datosComp=$pago["informacionCalculo"];
			$fechaVencimiento="NULL";
			if(isset($pago["fechaVencimiento"])&&($pago["fechaVencimiento"]!=""))
				$fechaVencimiento="'".$pago["fechaVencimiento"]."'";
				
		
							
			generarReferenciaPagoIVES($objDatos,$pago["arrFechasPago"],$arrDimensionesPagoReferenciado,$idFuncion,$tipoFuncion,$datosComp,$fechaVencimiento);
		}
	}

	function generarReferenciaPagoIVES($objDatos,$arrFechasPago,$arrDimensionesReferencia,$idFuncion="",$tipoFuncion="NULL",$datosComp="",$fechaVencimiento="NULL",$getIdMovimiento=false)
	{
		global $con;
		

		
		$consulta="SELECT nombreEstructura,idDimension,idFuncionInterpretacion FROM 563_dimensiones";
		$arrDimensiones=$con->obtenerFilasArregloAsocPHP($consulta,true);	
		$x=0;
		$query[$x]="begin";
		$x++;		
		
		$eF=obtenerValorDiccionaioArregloIVES($arrDimensionesReferencia,"nombre","IDFormulario");
		$eR=obtenerValorDiccionaioArregloIVES($arrDimensionesReferencia,"nombre","IDRegistro");
		$eC=obtenerValorDiccionaioArregloIVES($arrDimensionesReferencia,"nombre","IDConcepto");
		
		$idReferencia=generarReferenciaBancariaInscripcion($eF["valor"],$eR["valor"],$eC["valor"]);
		
		$query[$x]="set @referencia:='".$idReferencia."'";
		$x++;
		$idUsuario="-1";
		if(isset($objDatos["idUsuario"])&&($objDatos["idUsuario"]!=""))
			$idUsuario=$objDatos["idUsuario"];
		
		$plantel="";
		if(isset($objDatos["plantel"])&&($objDatos["plantel"]!=""))
			$plantel=$objDatos["plantel"];
		
		
		$idConcepto=-1;
		if(isset($objDatos["idConcepto"])&&($objDatos["idConcepto"]!=""))
			$idConcepto=$objDatos["idConcepto"];
		$descripcionAdeudo="";
		if(isset($objDatos["datosPago"]))
		{
			$descripcionAdeudo=$objDatos["datosPago"]["etiquetaPago"];
		}
		$query[$x]="INSERT INTO 6011_movimientosPago(idReferencia,idUsuario,plantel,fechaGeneracionFolio,idConcepto,idFuncionEjecucion,tipoFuncion,datosComplementarios,fechaVencimiento,descripcionAdeudo) 
				VALUES(@referencia,".$idUsuario.",'".$plantel."','".date("Y-m-d H:i:s")."',".$idConcepto.",'".$idFuncion."',".$tipoFuncion.",'".mysql_escape_string($datosComp)."',".$fechaVencimiento.",'".cv($descripcionAdeudo)."')";
		$x++;
		$query[$x]="set @idRegistro:=(select last_insert_id())";
		$x++;
		
		if(sizeof($arrFechasPago)>0)
		{
			foreach($arrFechasPago as $oPago)
			{
				$fechaInicio=$oPago["fechaInicio"];
				if($fechaInicio=="")
					$fechaInicio="NULL";
				else
					$fechaInicio="'".$fechaInicio."'";
				
				$fechaFin=$oPago["fechaFin"];
				if($fechaFin=="")
					$fechaFin="NULL";
				else
					$fechaFin="'".$fechaFin."'";
				$query[$x]="INSERT INTO 6012_asientosPago(idReferenciaMovimiento,monto,fechaInicio,fechaFin,pagado)
							VALUES(@idRegistro,".$oPago["monto"].",".$fechaInicio.",".$fechaFin.",0)";
				$x++;
			}
		}
		if(sizeof($arrDimensionesReferencia)>0)
		{

			foreach ($arrDimensionesReferencia as $objDimension) 
			{
				$nombre=$objDimension["nombre"];
				$valor=$objDimension["valor"];
				if(isset($arrDimensiones[$nombre]))
				{
					$idDimension=$arrDimensiones[$nombre][0];
					$funcionInterpretacion=$arrDimensiones[$nombre][1];
					$valorInterpretacion="";
					if($funcionInterpretacion!="")
					{
						$cadObj='{"valor":""}';
						$objParam=json_decode($cadObj);
						$objParam->valor=$valor;
						
						$cacheConsulta=NULL;
						$valorInterpretacion=resolverExpresionCalculoPHP($funcionInterpretacion,$objParam,$cacheConsulta);	
					}
					
					$query[$x]="INSERT INTO 6012_detalleAsientoPago(idAsientoPago,idDimension,valorCampo,valorInterpretacion)  VALUES(@idRegistro,".$idDimension.",'".$valor."','".$valorInterpretacion."')";
					$x++;
					
				}
				
			}
		}
		$query[$x]="commit";
		$x++;
		if($con->ejecutarBloque($query))
		{
			if(!$getIdMovimiento)
				$consulta="select @referencia";
			else
				$consulta="select @idRegistro";
			$referencia=$con->obtenerValor($consulta);
			return $referencia;	
		}
	}
	
	function generarReferenciaBancariaInscripcion($idFormulario,$idReferencia,$idConcepto)
	{
		global $con;
		
		$consulta="SELECT txtMatricula FROM _678_tablaDinamica WHERE id__678_tablaDinamica=".$idReferencia;
		$matricula=$con->obtenerValor($consulta);
		$anio=date("y");
		$mes=date("m");
		$consulta="SELECT cveConcepto FROM 561_conceptosIngreso WHERE idConcepto=".$idConcepto;
		$arancel=$con->obtenerValor($consulta);		
		
		$arancel=str_pad($arancel,3,"0",STR_PAD_LEFT);
		$matricula.=$anio.$mes.$arancel;

		$referencia=generarReferenciaDigitoVerificador($matricula);
		
		return $referencia;
		
	}
	
	function obtenerValorDiccionaioArregloIVES($dicionario,$campo,$valor)
	{
		foreach($dicionario as $e)
		{
			if($e[$campo]==$valor)
				return $e;
				
		}
		return NULL;
	}
	
	function esAlumnoNuevoIngresoLicenciaturaPosgrado($idInstanciaPlan,$idGrado)// Alumno que s einscribe a primer grado de cualquier plan de posgrado o licenciatura
	{
		global $con;
		$consulta="SELECT p.idProgramaEducativo,p.idPlanEstudio FROM 4513_instanciaPlanEstudio i,4500_planEstudio p WHERE p.idPlanEstudio=i.idPlanEstudio AND idInstanciaPlanEstudio=".$idInstanciaPlan;

		$fDatos=$con->obtenerPrimeraFila($consulta);
		$idProgramaEducativo=$fDatos[0];
		$idPlanEstudio=$fDatos[1];
		
		$arrProgramas=explode(",","82,84,85,86,87,88");

		if(existeValor($arrProgramas,$idProgramaEducativo))
		{
			$consulta="SELECT idGrado FROM 4505_estructuraCurricular e,4501_Grado g WHERE e.idPlanEstudio=".$idPlanEstudio." AND tipoUnidad=3 AND g.idGrado=e.idUnidad AND g.ordenGrado=1";
			$idPrimerGrado=$con->obtenerValor($consulta);

			if($idPrimerGrado==$idGrado)
				return 1;
			return 0;
		}
		return 0;
		
	}
	
	function esPromocionActivaCalendarioFechas($idRegistroInscripcion,$idPromocion,$tipoPromocion,$idInstanciaPlan,$idGrado)
	{
		global $con;
		
		$idFormulario=1058;//promocion
		if($tipoPromocion==2)
			$idFormulario=1057; //beca
		
		
		$descuento=0;
		
		$consulta="SELECT p.idProgramaEducativo,p.idPlanEstudio FROM 4513_instanciaPlanEstudio i,4500_planEstudio p WHERE p.idPlanEstudio=i.idPlanEstudio AND idInstanciaPlanEstudio=".$idInstanciaPlan;
		$fDatos=$con->obtenerPrimeraFila($consulta);

		$consulta="SELECT idEstructuraCurricular FROM 4505_estructuraCurricular WHERE idPlanEstudio=".$fDatos[1]." AND  idUnidad=".$idGrado." AND tipoUnidad=3"	;
		$idGradoEstructura=$con->obtenerValor($consulta);
		
		$consulta="SELECT idPerfilDescuento FROM 3012_gradosPlanesEstudioDescuento WHERE idFormulario=".$idFormulario." AND idReferencia=".$idPromocion.
					" AND idInstanciaPlan=".$idInstanciaPlan." AND idGrado=".$idGradoEstructura." AND idConcepto in(6,14)";
					
		
		
		$res=$con->obtenerFilas($consulta);
		while($fPerfiles=mysql_fetch_row($res))
		{
		
			$idPerfil=$fPerfiles[0];
	
			
			$consulta="SELECT objComp FROM 3013_perfilesDescuento WHERE idRegistro=".$idPerfil;
			$objComp=$con->obtenerValor($consulta);
			
				
			$consulta="SELECT fechaCreacion FROM _678_tablaDinamica WHERE id__678_tablaDinamica=".$idRegistroInscripcion;
	
			$fechaCreacion=$con->obtenerValor($consulta);
			$fechaActual=strtotime(date("Y-m-d",strtotime($fechaCreacion)));
	
	
			$arrDescuentos=array();
			$arrDatos=explode(":",$objComp);
			
			$arrAux=str_replace("}","",$arrDatos[1]);
			$arrAux=str_replace("[","",$arrAux);
			$arrAux=str_replace("]","",$arrAux);
			
			$arrAux=explode(",",$arrAux);
			
			
			$cadAux="";
			$pos=0;
			$oAux='';
			foreach($arrAux as $a)
			{
				if($pos==0)
				{
					$oAux='{"p":"'.str_replace("'","",$a).'",';
					$pos=1;
				}
				else
				{
					$oAux.='"f":"'.str_replace("'","",$a).'"}';
					$pos=0;
					
				}
				if($pos==0)
				{
					if($cadAux=="")
					
						$cadAux=$oAux;
					else
						$cadAux.=",".$oAux;
				}
			}
			
			$cadAux='{"arrDescuentos":['.$cadAux.']}';
			$oAux=json_decode($cadAux);
	
	
			foreach($oAux->arrDescuentos as $d)
			{
	
				if($d->f!="")	
				{
					if($fechaActual<=strtotime($d->f))
						$descuento=$d->p;
				}
				else
				{
					$descuento=$d->p;
				}
			}
			if($descuento>0)
				break;
		}
		if($descuento!=0)
			return 1;
		return 0;
	}
	
	function esAlumnoCanalizadoInstitucionConvenio($idRegistroInscripcion)
	{
		global $con;
		$consulta="SELECT conConvenio FROM _678_tablaDinamica WHERE id__678_tablaDinamica=".$idRegistroInscripcion;
		$conConvenio=$con->obtenerValor($consulta);
		return ($conConvenio==1)?"1":"0";
	}
		
	function registrarCobroPagoReferenciadoIVES($idVenta)
	{
		global $con;
		$consulta="SELECT claveProducto,total FROM 6009_productosVentaCaja WHERE idVenta=".$idVenta;

		$fVenta=$con->obtenerPrimeraFila($consulta);
		afectarPagoReferenciadoIVES($idVenta,$fVenta[0],date("Y-m-d"),$fVenta[1],$_SESSION["idUsr"],6);
	}	
	
	function afectarPagoReferenciadoIVES($idVenta,$referencia,$fechaPago,$importe,$idResponsable,$idBanco)
	{
		global $con;
		$fechaProceso=date("Y-m-d");
		
		
		$consulta="SELECT datosCompra FROM 6008_ventasCaja WHERE idVenta=".$idVenta;
		$datosCompra=$con->obtenerValor($consulta);
		$oCompra=json_decode($datosCompra);
		
		$consulta="select idFuncionEjecucion,tipoFuncion,idMovimiento,pagoParcial from 	6011_movimientosPago where idReferencia='".$referencia."'";

		$filaReferencia=$con->obtenerPrimeraFila($consulta);
		
		
		$pagado=0;
		$consulta="SELECT idAsientosPago,monto FROM 6012_asientosPago WHERE idReferenciaMovimiento=".$filaReferencia[2]." AND pagado=1";
		$fRegReferencia=$con->obtenerPrimeraFila($consulta);
		if(!$fRegReferencia)
		{
			$consulta="SELECT idAsientosPago,monto FROM 6012_asientosPago WHERE idReferenciaMovimiento=".$filaReferencia[2]." AND fechaInicio<='".$fechaProceso."' ORDER BY fechaInicio DESC"	;
			$fRegReferencia=$con->obtenerPrimeraFila($consulta);
		}
		else
			$pagado=1;
		
		
		
		$montoTotalPago=$fRegReferencia[1];
		
		$montoTotalAbono=$oCompra->cantidadAPagar;
		
		$consulta="SELECT SUM(montoParcialidad) FROM 6012_parcialidadesPago WHERE idMovimiento=".$filaReferencia[2];
		$totalParcial=$con->obtenerValor($consulta);
		if($totalParcial=="")
			$totalParcial=0;
			
		$montoTotalAbono+=$totalParcial;
		
		$diferencia=$montoTotalPago-$montoTotalAbono;
		if($diferencia<0)
			$diferencia=0;
			
		
		
		$x=0;
		$query[$x]="begin";
		$x++;
		
		if($diferencia==0)
		{
			$query[$x]="UPDATE 6011_movimientosPago SET pagado='1',fechaPago='".$fechaPago."',idPerfilPago='".$idBanco."',fechaRegistroPago='".$fechaProceso."',
						montoPagado='".$montoTotalAbono."',idResponsableAsiento='".$idResponsable."' WHERE idMovimiento=".$filaReferencia[2];
			
			$x++;
			if($filaReferencia[3]==1)
			{
				$query[$x]="INSERT INTO 6012_parcialidadesPago(idMovimiento,montoParcialidad,idVenta) VALUES(".$filaReferencia[2].",".$oCompra->cantidadAPagar.",".$idVenta.")";
				$x++;
			}
		}
		else
		{
			$query[$x]="UPDATE 6011_movimientosPago SET pagoParcial=1,idResponsableAsiento='".$idResponsable."' WHERE idMovimiento=".$filaReferencia[2];
			$x++;	
			$query[$x]="INSERT INTO 6012_parcialidadesPago(idMovimiento,montoParcialidad,idVenta) VALUES(".$filaReferencia[2].",".$oCompra->cantidadAPagar.",".$idVenta.")";
			$x++;
			
			
		}
		
		if($pagado==0)
		{
			$query[$x]="UPDATE 6012_asientosPago SET  pagado=1 WHERE idAsientosPago=".$fRegReferencia[0];
			$x++;
		}
		
		$query[$x]="commit";
		$x++;
		
		if($con->ejecutarBloque($query))
		{

			if($filaReferencia[0]!="")
			{
				if($filaReferencia[1]>=0)
				{
					if($filaReferencia[1]==1)
					{
						$cadObj='{"idMovimiento":"'.$filaReferencia[2].'"}';
						$obj=json_decode($cadObj);
						$cache=NULL;
						resolverExpresionCalculoPHP($filaReferencia[0],$obj,$cache);
					}
					else
					{
						
						eval($filaReferencia[0]."(".$filaReferencia[2].");");
					}
					
					
					$consulta="update 6011_movimientosPago set tipoFuncion=-1 where idReferencia='".$referencia."'";
					$con->ejecutarConsulta($consulta);
					
				}
				else
				{
					if($filaReferencia[1]==-1)
						eval($filaReferencia[0]."(".$filaReferencia[2].");");
				}
			}
		}
		
	}
	
	function seleccionarDictamenEquivalencia($idFormulario,$idReferencia)
	{
		global $con;
		$consulta="SELECT planEstudioDestino FROM _1066_tablaDinamica WHERE id__1066_tablaDinamica=".$idReferencia;
		$planEstudio=$con->obtenerValor($consulta);
		
		$consulta="SELECT idPlanEstudio FROM 4513_instanciaPlanEstudio WHERE idInstanciaPlanEstudio=".$planEstudio;
		$idPlanEstudio=$con->obtenerValor($consulta);
		
		$consulta="SELECT idProgramaEducativo FROM 4500_planEstudio WHERE idPlanEstudio=".$idPlanEstudio;
		$idProgramaEducativo=$con->obtenerValor($consulta);
		
		$idEstado=2;
		if(($idProgramaEducativo==89)&&($idProgramaEducativo==90))
			$idEstado=2.1;

		$consulta="UPDATE _1066_tablaDinamica SET idEstado=".$idEstado." WHERE id__1066_tablaDinamica=".$idReferencia;
		return $con->ejecutarConsulta($consulta);		
	}
	
	function visualizarResolucionEquivalenciaInterna($idReferencia)
	{
		global $con;
		$consulta="SELECT planEstudioDestino,idEstado FROM _1066_tablaDinamica WHERE id__1066_tablaDinamica=".$idReferencia;
		$fPlanEstudio=$con->obtenerPrimeraFila($consulta);
		$planEstudio=$fPlanEstudio[0];
		$idEstado=$fPlanEstudio[1];
		
		$consulta="SELECT idPlanEstudio FROM 4513_instanciaPlanEstudio WHERE idInstanciaPlanEstudio=".$planEstudio;
		$idPlanEstudio=$con->obtenerValor($consulta);
		
		$consulta="SELECT idProgramaEducativo FROM 4500_planEstudio WHERE idPlanEstudio=".$idPlanEstudio;
		$idProgramaEducativo=$con->obtenerValor($consulta);
		
		
		if(($idEstado>=2)&&(($idProgramaEducativo!=89)&&($idProgramaEducativo!=90)))
		{
			return 1;
		}
		
		return 0;
	}
	
	function seleccionRutaPreinscripcion($idFormulario,$idReferencia)
	{
		global $con;
		$idEstado=2.5;
		$consulta="SELECT conConvenio FROM _678_tablaDinamica WHERE id__678_tablaDinamica=".$idReferencia;
		$conConvenio=$con->obtenerValor($consulta);
		if($conConvenio==1)
		{
			$idEstado=4;
			$consulta="delete from 3015_descuentoAplicablePromocion where idFormulario=".$idFormulario." and idReferencia=".$idReferencia;
			$con->ejecutarConsulta($consulta);
			$consulta="INSERT INTO 3015_descuentoAplicablePromocion(idFormulario,idReferencia,idDescuento,tipoDescuento,descuentoAplicable)
					values(".$idFormulario.",".$idReferencia.",1,2,1)";
			$con->ejecutarConsulta($consulta);
			
		}
		return cambiarEtapaFormulario($idFormulario,$idReferencia,$idEstado);
		
		
	}
	
	function visualizarSeleccionDescuentoAplicable($idReferencia)
	{
		global $con;
		$consulta="SELECT conConvenio,idEstado FROM _678_tablaDinamica WHERE id__678_tablaDinamica=".$idReferencia;
		$fRegistro=$con->obtenerPrimeraFila($consulta);
		$conConvenio=$fRegistro[0];
		if(($fRegistro[1]>2)&&($conConvenio==0))
		{
			return 1;
		}
		
		return 0;
	}
	
	function asociarHorasMateriasPerfil($idPerfil,$cadHoras)
	{
		global $con;

		$oHoras=json_decode(bD($cadHoras));
		
		$x=0;
		
		$consulta[$x]="begin";
		$x++;
		$consulta[$x]="delete from 4627_horariosPerfilMateria where idPerfil=".$idPerfil;
		$x++;
		foreach($oHoras->arrFechas as $f)
		{
			$consulta[$x]="INSERT INTO 4627_horariosPerfilMateria(idPerfil,horaInicial,horaFinal,idCategoriaMateria,dia) VALUES(".$idPerfil.",'".$f->horaInicial."','".$f->horaFinal."',".$f->tipoMateria.",".$f->dia.")";
			$x++;
		}
		$consulta[$x]="commit";
		$x++;
		return $con->ejecutarBloque($consulta);
		
	}
?>