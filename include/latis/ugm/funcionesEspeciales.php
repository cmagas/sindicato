<?php	include_once("latis/libreriasFunciones/cPagoReferenciado.php");
	include_once("latis/funcionesBancos.php");

	function obtenerDocumentosRequeridos($idRef)
	{
		global $con;

		$arrDocumentos=array();
		
		$consulta="SELECT idReferencia FROM _678_tablaDinamica WHERE id__678_tablaDinamica=".$idRef;
		$idReferencia=$con->obtenerValor($consulta);
		
		$consulta="SELECT datosInscripcion FROM 4573_solicitudesInscripcion WHERE idSolicitudInscripcion=".$idReferencia;
		$datos=$con->obtenerValor($consulta);
		$objDatos=json_decode($datos);
		
		$consulta="SELECT idPlanEstudio FROM 4513_instanciaPlanEstudio WHERE idInstanciaPlanEstudio=".$objDatos->idInstanciaPlan;
		$idPlanEstudio=$con->obtenerValor($consulta);
		
		$consulta="SELECT documentos,funcionAplicacion FROM _392_tablaDinamica t, _392_docVSplanesEstudio d WHERE t.idReferencia=".$idPlanEstudio." AND d.idReferencia=t.id__392_tablaDinamica AND d.requerido=1";
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$obj=array();
			$obj["idDocumento"]=$fila[0];
			array_push($arrDocumentos,$obj);
		}

		return $arrDocumentos;
		
	}
	
	function asociarDocumentosUsuarioInscripcion($idRegistro)
	{
		global $con;
		$consulta="SELECT idReferencia FROM _678_tablaDinamica WHERE id__678_tablaDinamica=".$idRegistro;
		$idReferencia=$con->obtenerValor($consulta);
		
		$consulta="SELECT idUsuario FROM 4573_solicitudesInscripcion WHERE idSolicitudInscripcion=".$idReferencia;
		$idUsuario=$con->obtenerValor($consulta);
		$x=0;
		$query[$x]="begin";
		$x++;
		
		$consulta="SELECT documento,adjuntarArchivo FROM _681_GridDocumentosDigitales d,_681_tablaDinamica t WHERE d.idReferencia=t.id__681_tablaDinamica AND t.idReferencia=".$idRegistro." AND adjuntarArchivo IS NOT null";
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			if(!guardarDocumentoUsr($idUsuario,1,$fila[0],$fila[1]))
				return false;
			
		}
		$query[$x]="commit";
		$x++;
		return $con->ejecutarBloque($query);
		
	}
		
	
	function mostrarSeleccionPago($idProceso,$idFormulario,$idRegistro)
	{
		global $con;
		$consulta="SELECT idEstado,solicitaRevalidacion FROM _678_tablaDinamica WHERE id__678_tablaDinamica=".$idRegistro;
		$fRegistro=$con->obtenerPrimeraFila($consulta);
		if($fRegistro[0]=="6")
			return false;
		return true;
	}
	
	function pagoInscripcionRealizado($idMovimiento)
	{
		global $con;
		$consulta="SELECT valorCampo FROM 6012_detalleAsientoPago WHERE idAsientoPago=".$idMovimiento." AND idDimension=11";
		$idFormulario=$con->obtenerValor($consulta);
		$consulta="SELECT valorCampo FROM 6012_detalleAsientoPago WHERE idAsientoPago=".$idMovimiento." AND idDimension=12";
		$idRegistro=$con->obtenerValor($consulta);
		cambiarEtapaFormulario($idFormulario,$idRegistro,6);
	}
	
	function pagoRevalidacionRealizado($idMovimiento)
	{
		global $con;
		$consulta="SELECT valorCampo FROM 6012_detalleAsientoPago WHERE idAsientoPago=".$idMovimiento." AND idDimension=11";
		$idFormulario=$con->obtenerValor($consulta);
		$consulta="SELECT valorCampo FROM 6012_detalleAsientoPago WHERE idAsientoPago=".$idMovimiento." AND idDimension=12";
		$idRegistro=$con->obtenerValor($consulta);
		cambiarEtapaFormulario($idFormulario,$idRegistro,3);
	}
	
	function registrarCobroPagoReferenciado($idVenta)
	{
		global $con;
		$consulta="SELECT claveProducto,total FROM 6009_productosVentaCaja WHERE idVenta=".$idVenta;
		$fVenta=$con->obtenerPrimeraFila($consulta);
		afectarPagoReferenciado($fVenta[0],date("Y-m-d"),$fVenta[1],$_SESSION["idUsr"],6);
	}	
	
	
	
	
	
	
	function obtenerGradoPeriodoInstancia($idInstancia,$idUsuario)
	{
		global $con;
		$cicloPeriodo=-1;
		$consultaCiclo="SELECT idCiclo,idPeriodo,idGrado FROM 4529_alumnos WHERE idInstanciaPlanEstudio='".$idInstancia."' AND idUsuario='".$idUsuario."' 
						AND estado='1'";
		$idCiclo=$con->obtenerPrimeraFila($consultaCiclo);
		if($idCiclo!="")
		{
			$cicloPeriodo=$idCiclo[0]."_".$idCiclo[1]."_".$idCiclo[2];
		}
		else
		{
			$maximoCiclo="SELECT MAX(idCiclo) FROM 4529_alumnos WHERE idInstanciaPlanEstudio='".$idInstancia."' AND idUsuario='".$idUsuario."' ";
			$idCiclo=$con->obtenerValor($maximoCiclo);

			$maximoPeriodo="SELECT MAX(idPeriodo) FROM 4529_alumnos WHERE idInstanciaPlanEstudio='".$idInstancia."' AND idUsuario='".$idUsuario."' 
							AND idCiclo='".$idCiclo."'";
			$idPeriodo=$con->obtenerValor($maximoPeriodo);

			$grado="SELECT idGrado FROM 4529_alumnos WHERE idInstanciaPlanEstudio='".$idInstancia."' AND idUsuario='".$idUsuario."' 
							AND idCiclo='".$idCiclo."' AND idPeriodo='".$idPeriodo."'";
			$idGrado=$con->obtenerValor($grado);
			
			$cicloPeriodo=$idCiclo."_".$idPeriodo."_".$idGrado;
		}
		return $cicloPeriodo;
	}
	
	function obtenerProgramaEducativoInstancia($idInstancia)
	{
		global $con;
		$consultaP="SELECT p.idProgramaEducativo FROM 4513_instanciaPlanEstudio i,4500_planEstudio p WHERE p.idPlanEstudio=i.idPlanEstudio 
					AND i.idInstanciaPlanEstudio='".$idInstancia."'";
		$idProgramaEducativo=$con->obtenerValor($consultaP);
		return $idProgramaEducativo;
	}
	
	function generarPagoReferenciadoTitulacionPaq1($idRegistro)//paquete 1
	{
		global $con;
		//$idConcepto=16;
		$consulta="SELECT fechaCreacion,responsable,idEstado,codigoInstitucion,idInstanciaPlan,optTipoTitulacion FROM _769_tablaDinamica 
					WHERE id__769_tablaDinamica='".$idRegistro."'";
		$resp=$con->obtenerPrimeraFila($consulta);
		if($resp==3)
		{
			$idConcepto=70;
		}
		else
		{
			$idConcepto=92;
		}
		
		$plantel=$resp[3];
		$idInstancia=$resp[4];
		$idUsuario=$resp[1];
		$idProgramaEducativo=obtenerProgramaEducativoInstancia($idInstancia);
		$idGradoPeriodoCiclo=obtenerGradoPeriodoInstancia($idInstancia,$idUsuario);
			
		$datos=explode("_",$idGradoPeriodoCiclo);
		$idCiclo=$datos[0];
		$idPeriodo=$datos[1];
		$idGrado=$datos[2];
		
		$arrDimensiones=array();
		$arrDimensionesCosto["plantel"]=$plantel;
		$arrDimensionesCosto["idProgramaEducativo"]=$idProgramaEducativo;
		$arrDimensionesCosto["idInstanciaPlanEstudio"]=$idInstancia;
		$arrDimensionesCosto["grado"]=$idGrado;
		
		$oParametroCosto=array();
		$oParametroCosto["idUsuario"]=$idUsuario;
		$idPlanPagosAplica=-1;//Si -1 Aplica todos los planes
		$tabulador=generarTabuladorCostoServicio($idConcepto,$idCiclo,$idPeriodo,$arrDimensionesCosto,$oParametroCosto); 
		$arrTabulador=generarFechasPagosServicio($idConcepto,$tabulador);
		
		$arrDimensionesPagoReferenciado=array();
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IdInstanciaPlan",$idInstancia);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDCicloEscolar",$idCiclo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDPeriodo",$idPeriodo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDFormulario",769);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDRegistro",$idRegistro);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"Plantel",$plantel);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDProgramaEducativo",$idProgramaEducativo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDGrado",$idGrado);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDUsuario",$idUsuario);
		
		generarPagosReferenciados($plantel,$idUsuario,$arrTabulador,$arrDimensionesPagoReferenciado,"pagoTituloRealizado",0);
		return true;
	}
	
	function pagoTituloRealizado($idMovimiento)
	{
		global $con;
		$consulta="SELECT valorCampo FROM 6012_detalleAsientoPago WHERE idAsientoPago=".$idMovimiento." AND idDimension=11";
		$idFormulario=$con->obtenerValor($consulta);
		$consulta="SELECT valorCampo FROM 6012_detalleAsientoPago WHERE idAsientoPago=".$idMovimiento." AND idDimension=12";
		$idRegistro=$con->obtenerValor($consulta);
		$consultaTipo="SELECT optTipoTitulacion FROM _769_tablaDinamica WHERE id__769_tablaDinamica='".$idRegistro."'";
		$resp=$con->obtenerValor($consultaTipo);
		if($resp=='3')
		{
			cambiarEtapaFormulario($idFormulario,$idRegistro,3);
		}
		else
		{
			cambiarEtapaFormulario($idFormulario,$idRegistro,9);
		}
	}
	
	function generarPagoReferenciadoAsesoriaTit($idRegistro)
	{
		global $con;
		$idConcepto=26;
		$consulta="SELECT fechaCreacion,responsable,idEstado,codigoInstitucion,idInstanciaPlan FROM _769_tablaDinamica 
					WHERE id__769_tablaDinamica='".$idRegistro."'";
		$resp=$con->obtenerPrimeraFila($consulta);
		
		$plantel=$resp[3];
		$idInstancia=$resp[4];
		$idUsuario=$resp[1];
		$idProgramaEducativo=obtenerProgramaEducativoInstancia($idInstancia);
		$idGradoPeriodoCiclo=obtenerGradoPeriodoInstancia($idInstancia,$idUsuario);
			
		$datos=explode("_",$idGradoPeriodoCiclo);
		$idCiclo=$datos[0];
		$idPeriodo=$datos[1];
		$idGrado=$datos[2];
		
		$arrDimensiones=array();
		$arrDimensionesCosto["plantel"]=$plantel;
		$arrDimensionesCosto["idProgramaEducativo"]=$idProgramaEducativo;
		$arrDimensionesCosto["idInstanciaPlanEstudio"]=$idInstancia;
		$arrDimensionesCosto["grado"]=$idGrado;
		
		$oParametroCosto=array();
		$oParametroCosto["idUsuario"]=$idUsuario;
		$idPlanPagosAplica=-1;//Si -1 Aplica todos los planes
		$tabulador=generarTabuladorCostoServicio($idConcepto,$idCiclo,$idPeriodo,$arrDimensionesCosto,$oParametroCosto); 
		$arrTabulador=generarFechasPagosServicio($idConcepto,$tabulador);
		
		$arrDimensionesPagoReferenciado=array();
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IdInstanciaPlan",$idInstancia);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDCicloEscolar",$idCiclo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDPeriodo",$idPeriodo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDFormulario",769);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDRegistro",$idRegistro);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"Plantel",$plantel);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDProgramaEducativo",$idProgramaEducativo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDGrado",$idGrado);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDUsuario",$idUsuario);
		
		generarPagosReferenciados($plantel,$idUsuario,$arrTabulador,$arrDimensionesPagoReferenciado,"pagoAsesoriaRealizado",0);
		return true;
	}
	
	function pagoAsesoriaRealizado($idMovimiento)
	{
		global $con;
		$consulta="SELECT valorCampo FROM 6012_detalleAsientoPago WHERE idAsientoPago=".$idMovimiento." AND idDimension=11";
		$idFormulario=$con->obtenerValor($consulta);
		$consulta="SELECT valorCampo FROM 6012_detalleAsientoPago WHERE idAsientoPago=".$idMovimiento." AND idDimension=12";
		$idRegistro=$con->obtenerValor($consulta);
		cambiarEtapaFormulario($idFormulario,$idRegistro,4);
	}

	function generarPagoReferenciadoPaqueteTit($idRegistro)
	{
		global $con;
		$idConcepto=26;
		$consulta="SELECT fechaCreacion,responsable,idEstado,codigoInstitucion,idInstanciaPlan FROM _769_tablaDinamica 
					WHERE id__769_tablaDinamica='".$idRegistro."'";
		$resp=$con->obtenerPrimeraFila($consulta);
		
		$plantel=$resp[3];
		$idInstancia=$resp[4];
		$idUsuario=$resp[1];
		$idProgramaEducativo=obtenerProgramaEducativoInstancia($idInstancia);
		$idGradoPeriodoCiclo=obtenerGradoPeriodoInstancia($idInstancia,$idUsuario);
			
		$datos=explode("_",$idGradoPeriodoCiclo);
		$idCiclo=$datos[0];
		$idPeriodo=$datos[1];
		$idGrado=$datos[2];
		
		$arrDimensiones=array();
		$arrDimensionesCosto["plantel"]=$plantel;
		$arrDimensionesCosto["idProgramaEducativo"]=$idProgramaEducativo;
		$arrDimensionesCosto["idInstanciaPlanEstudio"]=$idInstancia;
		$arrDimensionesCosto["grado"]=$idGrado;
		
		$oParametroCosto=array();
		$oParametroCosto["idUsuario"]=$idUsuario;
		$idPlanPagosAplica=-1;//Si -1 Aplica todos los planes
		$tabulador=generarTabuladorCostoServicio($idConcepto,$idCiclo,$idPeriodo,$arrDimensionesCosto,$oParametroCosto); 
		$arrTabulador=generarFechasPagosServicio($idConcepto,$tabulador);
		
		$arrDimensionesPagoReferenciado=array();
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IdInstanciaPlan",$idInstancia);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDCicloEscolar",$idCiclo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDPeriodo",$idPeriodo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDFormulario",769);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDRegistro",$idRegistro);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"Plantel",$plantel);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDProgramaEducativo",$idProgramaEducativo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDGrado",$idGrado);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDUsuario",$idUsuario);
		
		//varDump($arrDimensionesPagoReferenciado)."<br>";
		generarPagosReferenciados($plantel,$idUsuario,$arrTabulador,$arrDimensionesPagoReferenciado,"pagoPaqueteTitRealizado",0);
		return true;
	}
	
	function pagoPaqueteTitRealizado($idMovimiento)
	{
		global $con;
		$consulta="SELECT valorCampo FROM 6012_detalleAsientoPago WHERE idAsientoPago=".$idMovimiento." AND idDimension=11";
		$idFormulario=$con->obtenerValor($consulta);
		$consulta="SELECT valorCampo FROM 6012_detalleAsientoPago WHERE idAsientoPago=".$idMovimiento." AND idDimension=12";
		$idRegistro=$con->obtenerValor($consulta);
		cambiarEtapaFormulario($idFormulario,$idRegistro,9);
	}

	function generarPagoReferenciadoPaquete2($idRegistro)
	{
		global $con;
		$idConcepto=28;
		$consulta="SELECT fechaCreacion,responsable,idEstado,codigoInstitucion,idInstanciaPlan FROM _769_tablaDinamica 
					WHERE id__769_tablaDinamica='".$idRegistro."'";
		$resp=$con->obtenerPrimeraFila($consulta);
		
		$plantel=$resp[3];
		$idInstancia=$resp[4];
		$idUsuario=$resp[1];
		$idProgramaEducativo=obtenerProgramaEducativoInstancia($idInstancia);
		$idGradoPeriodoCiclo=obtenerGradoPeriodoInstancia($idInstancia,$idUsuario);
			
		$datos=explode("_",$idGradoPeriodoCiclo);
		$idCiclo=$datos[0];
		$idPeriodo=$datos[1];
		$idGrado=$datos[2];
		
		$arrDimensiones=array();
		$arrDimensionesCosto["plantel"]=$plantel;
		$arrDimensionesCosto["idProgramaEducativo"]=$idProgramaEducativo;
		$arrDimensionesCosto["idInstanciaPlanEstudio"]=$idInstancia;
		$arrDimensionesCosto["grado"]=$idGrado;
		
		$oParametroCosto=array();
		$oParametroCosto["idUsuario"]=$idUsuario;
		$idPlanPagosAplica=-1;//Si -1 Aplica todos los planes
		$tabulador=generarTabuladorCostoServicio($idConcepto,$idCiclo,$idPeriodo,$arrDimensionesCosto,$oParametroCosto); 
		$arrTabulador=generarFechasPagosServicio($idConcepto,$tabulador);
		
		$arrDimensionesPagoReferenciado=array();
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IdInstanciaPlan",$idInstancia);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDCicloEscolar",$idCiclo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDPeriodo",$idPeriodo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDFormulario",845);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDRegistro",$idRegistro);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"Plantel",$plantel);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDProgramaEducativo",$idProgramaEducativo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDGrado",$idGrado);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDUsuario",$idUsuario);
		
		//varDump($arrDimensionesPagoReferenciado)."<br>";
		generarPagosReferenciados($plantel,$idUsuario,$arrTabulador,$arrDimensionesPagoReferenciado,"pagoPaquete2Realizado",0);
		return true;
	}
	
	function pagoPaquete2Realizado($idMovimiento)
	{
		global $con;
		$consulta="SELECT valorCampo FROM 6012_detalleAsientoPago WHERE idAsientoPago=".$idMovimiento." AND idDimension=11";
		$idFormulario=$con->obtenerValor($consulta);
		$consulta="SELECT valorCampo FROM 6012_detalleAsientoPago WHERE idAsientoPago=".$idMovimiento." AND idDimension=12";
		$idRegistro=$con->obtenerValor($consulta);
		cambiarEtapaFormulario($idFormulario,$idRegistro,5);
	}

	function generarPagoReferenciadoCedulaProf($idRegistro)
	{
		global $con;
		$idConcepto=27;
		$consulta="SELECT fechaCreacion,responsable,idEstado,codigoInstitucion,idInstanciaplan FROM _845_tablaDinamica
					WHERE id__845_tablaDinamica='".$idRegistro."'";
		$resp=$con->obtenerPrimeraFila($consulta);
		
		$plantel=$resp[3];
		$idInstancia=$resp[4];
		$idUsuario=$resp[1];
		$idProgramaEducativo=obtenerProgramaEducativoInstancia($idInstancia);
		$idGradoPeriodoCiclo=obtenerGradoPeriodoInstancia($idInstancia,$idUsuario);
			
		$datos=explode("_",$idGradoPeriodoCiclo);
		$idCiclo=$datos[0];
		$idPeriodo=$datos[1];
		$idGrado=$datos[2];
		
		$arrDimensiones=array();
		$arrDimensionesCosto["plantel"]=$plantel;
		$arrDimensionesCosto["idProgramaEducativo"]=$idProgramaEducativo;
		$arrDimensionesCosto["idInstanciaPlanEstudio"]=$idInstancia;
		$arrDimensionesCosto["grado"]=$idGrado;
		
		$oParametroCosto=array();
		$oParametroCosto["idUsuario"]=$idUsuario;
		$idPlanPagosAplica=-1;//Si -1 Aplica todos los planes
		$tabulador=generarTabuladorCostoServicio($idConcepto,$idCiclo,$idPeriodo,$arrDimensionesCosto,$oParametroCosto); 
		$arrTabulador=generarFechasPagosServicio($idConcepto,$tabulador);
		
		$arrDimensionesPagoReferenciado=array();
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IdInstanciaPlan",$idInstancia);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDCicloEscolar",$idCiclo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDPeriodo",$idPeriodo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDFormulario",845);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDRegistro",$idRegistro);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"Plantel",$plantel);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDProgramaEducativo",$idProgramaEducativo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDGrado",$idGrado);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDUsuario",$idUsuario);
		
		//varDump($arrDimensionesPagoReferenciado)."<br>";
		generarPagosReferenciados($plantel,$idUsuario,$arrTabulador,$arrDimensionesPagoReferenciado,"pagoCedulaProfRealizado",0);
		return true;
	}
	
	function pagoCedulaProfRealizado($idMovimiento)
	{
		global $con;
		$consulta="SELECT valorCampo FROM 6012_detalleAsientoPago WHERE idAsientoPago=".$idMovimiento." AND idDimension=11";
		$idFormulario=$con->obtenerValor($consulta);
		$consulta="SELECT valorCampo FROM 6012_detalleAsientoPago WHERE idAsientoPago=".$idMovimiento." AND idDimension=12";
		$idRegistro=$con->obtenerValor($consulta);
		cambiarEtapaFormulario($idFormulario,$idRegistro,5);
	}

	function generarPagoReferenciadoTituloCedula($idRegistro)
	{
		global $con;
		$idConcepto=30;
		$consulta="SELECT fechaCreacion,responsable,idEstado,codigoInstitucion,idInstanciaplan FROM _845_tablaDinamica
					WHERE id__845_tablaDinamica='".$idRegistro."'";
		$resp=$con->obtenerPrimeraFila($consulta);
		
		$plantel=$resp[3];
		$idInstancia=$resp[4];
		$idUsuario=$resp[1];
		$idProgramaEducativo=obtenerProgramaEducativoInstancia($idInstancia);
		$idGradoPeriodoCiclo=obtenerGradoPeriodoInstancia($idInstancia,$idUsuario);
			
		$datos=explode("_",$idGradoPeriodoCiclo);
		$idCiclo=$datos[0];
		$idPeriodo=$datos[1];
		$idGrado=$datos[2];
		
		$arrDimensiones=array();
		$arrDimensionesCosto["plantel"]=$plantel;
		$arrDimensionesCosto["idProgramaEducativo"]=$idProgramaEducativo;
		$arrDimensionesCosto["idInstanciaPlanEstudio"]=$idInstancia;
		$arrDimensionesCosto["grado"]=$idGrado;
		
		$oParametroCosto=array();
		$oParametroCosto["idUsuario"]=$idUsuario;
		$idPlanPagosAplica=-1;//Si -1 Aplica todos los planes
		$tabulador=generarTabuladorCostoServicio($idConcepto,$idCiclo,$idPeriodo,$arrDimensionesCosto,$oParametroCosto); 
		$arrTabulador=generarFechasPagosServicio($idConcepto,$tabulador);
		
		$arrDimensionesPagoReferenciado=array();
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IdInstanciaPlan",$idInstancia);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDCicloEscolar",$idCiclo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDPeriodo",$idPeriodo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDFormulario",845);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDRegistro",$idRegistro);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"Plantel",$plantel);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDProgramaEducativo",$idProgramaEducativo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDGrado",$idGrado);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDUsuario",$idUsuario);
		
		//varDump($arrDimensionesPagoReferenciado)."<br>";
		generarPagosReferenciados($plantel,$idUsuario,$arrTabulador,$arrDimensionesPagoReferenciado,"pagoTituloCedulaRealizado",0);
		return true;
	}
	
	function pagoTituloCedulaRealizado($idMovimiento)
	{
		global $con;
		$consulta="SELECT valorCampo FROM 6012_detalleAsientoPago WHERE idAsientoPago=".$idMovimiento." AND idDimension=11";
		$idFormulario=$con->obtenerValor($consulta);
		$consulta="SELECT valorCampo FROM 6012_detalleAsientoPago WHERE idAsientoPago=".$idMovimiento." AND idDimension=12";
		$idRegistro=$con->obtenerValor($consulta);
		cambiarEtapaFormulario($idFormulario,$idRegistro,5);
	}

	function generarPagoReferenciadoServicioSocial($idRegistro)
	{
		global $con;
		$idConcepto=15;
		$consulta="SELECT fechaCreacion,responsable,idEstado,codigoInstitucion,idInstanciaPlan FROM _759_tablaDinamica
					WHERE id__759_tablaDinamica='".$idRegistro."'";
		$resp=$con->obtenerPrimeraFila($consulta);
		
		$plantel=$resp[3];
		$idInstancia=$resp[4];
		$idUsuario=$resp[1];
		$idProgramaEducativo=obtenerProgramaEducativoInstancia($idInstancia);
		$idGradoPeriodoCiclo=obtenerGradoPeriodoInstancia($idInstancia,$idUsuario);
			
		$datos=explode("_",$idGradoPeriodoCiclo);
		$idCiclo=$datos[0];
		$idPeriodo=$datos[1];
		$idGrado=$datos[2];
		
		$arrDimensiones=array();
		$arrDimensionesCosto["plantel"]=$plantel;
		$arrDimensionesCosto["idProgramaEducativo"]=$idProgramaEducativo;
		$arrDimensionesCosto["idInstanciaPlanEstudio"]=$idInstancia;
		$arrDimensionesCosto["grado"]=$idGrado;
		
		$oParametroCosto=array();
		$oParametroCosto["idUsuario"]=$idUsuario;
		$idPlanPagosAplica=-1;//Si -1 Aplica todos los planes
		$tabulador=generarTabuladorCostoServicio($idConcepto,$idCiclo,$idPeriodo,$arrDimensionesCosto,$oParametroCosto); 
		$arrTabulador=generarFechasPagosServicio($idConcepto,$tabulador);
		
		$arrDimensionesPagoReferenciado=array();
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IdInstanciaPlan",$idInstancia);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDCicloEscolar",$idCiclo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDPeriodo",$idPeriodo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDFormulario",759);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDRegistro",$idRegistro);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"Plantel",$plantel);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDProgramaEducativo",$idProgramaEducativo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDGrado",$idGrado);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDUsuario",$idUsuario);
		
				//varDump($arrTabulador);

		//varDump($arrDimensionesPagoReferenciado)."<br>";
		generarPagosReferenciados($plantel,$idUsuario,$arrTabulador,$arrDimensionesPagoReferenciado,"pagoServicioSocialRealizado",0);
		return true;
	}
	
	function pagoServicioSocialRealizado($idMovimiento)
	{
		global $con;
		$consulta="SELECT valorCampo FROM 6012_detalleAsientoPago WHERE idAsientoPago=".$idMovimiento." AND idDimension=11";
		$idFormulario=$con->obtenerValor($consulta);
		$consulta="SELECT valorCampo FROM 6012_detalleAsientoPago WHERE idAsientoPago=".$idMovimiento." AND idDimension=12";
		$idRegistro=$con->obtenerValor($consulta);
		cambiarEtapaFormulario($idFormulario,$idRegistro,3);
	}



	function generarPagoReferenciadoPracticaProf($idRegistro)
	{
		global $con;
		$idConcepto=24;
		$consulta="SELECT fechaCreacion,responsable,idEstado,codigoInstitucion,idInstanciaPlan FROM _759_tablaDinamica
					WHERE id__759_tablaDinamica='".$idRegistro."'";
		$resp=$con->obtenerPrimeraFila($consulta);
		
		$plantel=$resp[3];
		$idInstancia=$resp[4];
		$idUsuario=$resp[1];
		$idProgramaEducativo=obtenerProgramaEducativoInstancia($idInstancia);
		$idGradoPeriodoCiclo=obtenerGradoPeriodoInstancia($idInstancia,$idUsuario);
			
		$datos=explode("_",$idGradoPeriodoCiclo);
		$idCiclo=$datos[0];
		$idPeriodo=$datos[1];
		$idGrado=$datos[2];
		
		$arrDimensiones=array();
		$arrDimensionesCosto["plantel"]=$plantel;
		$arrDimensionesCosto["idProgramaEducativo"]=$idProgramaEducativo;
		$arrDimensionesCosto["idInstanciaPlanEstudio"]=$idInstancia;
		$arrDimensionesCosto["grado"]=$idGrado;
		
		$oParametroCosto=array();
		$oParametroCosto["idUsuario"]=$idUsuario;
		$idPlanPagosAplica=-1;//Si -1 Aplica todos los planes
		$tabulador=generarTabuladorCostoServicio($idConcepto,$idCiclo,$idPeriodo,$arrDimensionesCosto,$oParametroCosto); 
		$arrTabulador=generarFechasPagosServicio($idConcepto,$tabulador);
		
		$arrDimensionesPagoReferenciado=array();
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IdInstanciaPlan",$idInstancia);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDCicloEscolar",$idCiclo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDPeriodo",$idPeriodo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDFormulario",759);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDRegistro",$idRegistro);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"Plantel",$plantel);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDProgramaEducativo",$idProgramaEducativo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDGrado",$idGrado);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDUsuario",$idUsuario);
		
		//varDump($arrDimensionesPagoReferenciado)."<br>";
		generarPagosReferenciados($plantel,$idUsuario,$arrTabulador,$arrDimensionesPagoReferenciado,"pagoPracticaProfRealizado",0);
		return true;
	}
	
	function pagoPracticaProfRealizado($idMovimiento)
	{
		global $con;
		$consulta="SELECT valorCampo FROM 6012_detalleAsientoPago WHERE idAsientoPago=".$idMovimiento." AND idDimension=11";
		$idFormulario=$con->obtenerValor($consulta);
		$consulta="SELECT valorCampo FROM 6012_detalleAsientoPago WHERE idAsientoPago=".$idMovimiento." AND idDimension=12";
		$idRegistro=$con->obtenerValor($consulta);
		cambiarEtapaFormulario($idFormulario,$idRegistro,3);
	}

	function generarPagoReferenciadoTramite($idRegistro)
	{
		global $con;
		$consulta="SELECT fechaCreacion,responsable,idEstado,codigoInstitucion,idInstanciaPlan,idSolicitud FROM _923_tablaDinamica
					WHERE id__923_tablaDinamica='".$idRegistro."'";
		$resp=$con->obtenerPrimeraFila($consulta);
		
		$idConcepto=$resp[5];
		$plantel=$resp[3];
		$idInstancia=$resp[4];
		$idUsuario=$resp[1];
		$idProgramaEducativo=obtenerProgramaEducativoInstancia($idInstancia);
		$idGradoPeriodoCiclo=obtenerGradoPeriodoInstancia($idInstancia,$idUsuario);
			
		$datos=explode("_",$idGradoPeriodoCiclo);
		$idCiclo=$datos[0];
		$idPeriodo=$datos[1];
		$idGrado=$datos[2];
		
		$arrDimensiones=array();
		$arrDimensionesCosto["plantel"]=$plantel;
		$arrDimensionesCosto["idProgramaEducativo"]=$idProgramaEducativo;
		$arrDimensionesCosto["idInstanciaPlanEstudio"]=$idInstancia;
		$arrDimensionesCosto["grado"]=$idGrado;
		
		$oParametroCosto=array();
		$oParametroCosto["idUsuario"]=$idUsuario;
		$idPlanPagosAplica=-1;//Si -1 Aplica todos los planes
		$tabulador=generarTabuladorCostoServicio($idConcepto,$idCiclo,$idPeriodo,$arrDimensionesCosto,$oParametroCosto); 
		$arrTabulador=generarFechasPagosServicio($idConcepto,$tabulador);
		
		$arrDimensionesPagoReferenciado=array();
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IdInstanciaPlan",$idInstancia);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDCicloEscolar",$idCiclo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDPeriodo",$idPeriodo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDFormulario",923);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDRegistro",$idRegistro);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"Plantel",$plantel);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDProgramaEducativo",$idProgramaEducativo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDGrado",$idGrado);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDUsuario",$idUsuario);
		
		//varDump($arrDimensionesPagoReferenciado)."<br>";
		generarPagosReferenciados($plantel,$idUsuario,$arrTabulador,$arrDimensionesPagoReferenciado,"pagoTramiteRealizado",0);
		return true;
	}
	
	function pagoTramiteRealizado($idMovimiento)
	{
		global $con;
		$consulta="SELECT valorCampo FROM 6012_detalleAsientoPago WHERE idAsientoPago=".$idMovimiento." AND idDimension=11";
		$idFormulario=$con->obtenerValor($consulta);
		$consulta="SELECT valorCampo FROM 6012_detalleAsientoPago WHERE idAsientoPago=".$idMovimiento." AND idDimension=12";
		$idRegistro=$con->obtenerValor($consulta);
		cambiarEtapaFormulario($idFormulario,$idRegistro,5);
	}

	function generarPagoReferenciadoAdeudoVarios($idRegistro)
	{
		global $con;
		$query="SELECT fechaCreacion,responsable,idEstado,codigoInstitucion,cmbAlumno,cmbInstanciaPlan,montoAdeudo,txtDescripcion 
					FROM _936_tablaDinamica WHERE id__936_tablaDinamica='".$idRegistro."'";
		$resp=$con->obtenerPrimeraFila($query);
		
		$idConcepto=31;
		$plantel=$resp[3];
		$idUsuario=$resp[4];
		$montoAdeudo=$resp[6];
		$descripcionAdeudo=$resp[7];
		$idFuncion="pagoAdeudoVariosRealizado";
		$fechaInicio=date("Y-m-d");
		$x=0;
		$consulta[$x]="begin";
		$x++;		
		$consulta[$x]="select @idReferencia:=idReferenciaSiguiente FROM 903_variablesSistema FOR update";
		$x++;
		$consulta[$x]="update 903_variablesSistema set idReferenciaSiguiente=(if((idReferenciaSiguiente+1)>99,1,(idReferenciaSiguiente+1)))";
		$x++;
		$consulta[$x]="set @referencia:=(concat('".date("ymdHms")."',LPAD(@idReferencia,2,'0')))";
		$x++;
		$consulta[$x]="INSERT INTO 6011_movimientosPago(idReferencia,idUsuario,plantel,fechaGeneracionFolio,idConcepto,idFuncionEjecucion,tipoFuncion,
					descripcionAdeudo) VALUES(@referencia,".$idUsuario.",'".$plantel."','".date("Y-m-d H:i:s")."',
					".$idConcepto.",'".$idFuncion."','0','".$descripcionAdeudo."')";
		$x++;
		$consulta[$x]="set @idRegistro:=(select last_insert_id())";
		$x++;
		$consulta[$x]="INSERT INTO 6012_asientosPago(idReferenciaMovimiento,monto,fechaInicio,pagado)VALUES(@idRegistro,'".$montoAdeudo."',
						'".$fechaInicio."',0)";
		$x++;
		$consulta[$x]="commit";
		$x++;
		$consulta[$x]="INSERT INTO 6012_detalleAsientoPago(idAsientoPago,idDimension,valorCampo)VALUES(@idRegistro,'11','936')";
		$x++;
		$consulta[$x]="INSERT INTO 6012_detalleAsientoPago(idAsientoPago,idDimension,valorCampo)VALUES(@idRegistro,'12','".$idRegistro."')";
		$x++;
		$consulta[$x]="UPDATE _936_tablaDinamica SET referenciaPago=@referencia WHERE id__936_tablaDinamica='".$idRegistro."'";
		$x++;
		
		if(!$con->ejecutarBloque($consulta))
		{
			echo "Operación no realizada";
		}
	}
	
	function pagoAdeudoVariosRealizado($idMovimiento)
	{
		global $con;
		$consulta="SELECT valorCampo FROM 6012_detalleAsientoPago WHERE idAsientoPago=".$idMovimiento." AND idDimension=11";
		$idFormulario=$con->obtenerValor($consulta);
		$consulta="SELECT valorCampo FROM 6012_detalleAsientoPago WHERE idAsientoPago=".$idMovimiento." AND idDimension=12";
		$idRegistro=$con->obtenerValor($consulta);
		cambiarEtapaFormulario($idFormulario,$idRegistro,3);
	}
	
	
	function obtenerMontoDescuentoProntoPago($param1)
	{
		global $con;

		$resultado=0;
		$consulta="SELECT sede FROM 4513_instanciaPlanEstudio WHERE idInstanciaPlanEstudio=".$param1["idInstanciaPlanEstudio"];
		$plantel=$con->obtenerValor($consulta);
		$filaConf=obtenerConfiguracionPlanEstudio(986,"",$param1["idInstanciaPlanEstudio"]);
		$fOperacion=null;
		
		if($filaConf)
		{
			$consulta="select valorReferencia,tipoValorReferencia FROM _986_gridPlanesPago WHERE idReferencia=".$filaConf[0];
			$fOperacion=$con->obtenerPrimeraFila($consulta);
			
		}
		
		if(!$fOperacion)
		{
			$consulta="SELECT valorReferencia,tipoValorReferencia FROM 6023_planesPagosConceptoPlanteles
						WHERE idPlanPago=".$param1["idPlanPagos"]." AND idConcepto=".$param1["idConceptoServicio"]." AND plantel='".$plantel."' 
						AND aplicaPlantel=1";
						
			
			$fOperacion=$con->obtenerPrimeraFila($consulta);
		}
		
		if($fOperacion)
		{
			if(($fOperacion[0]!="")	&&($fOperacion[0]!="0"))
			{
				
				switch($fOperacion[1])
				{
					case 1:
						$resultado= $param1["montoBase"]*($fOperacion[0]/100);
					break;
					case 2:
						$resultado=$fOperacion[0];
					break;	
				}
			}
		}
		$idCostoConcepto=$param1["idCostoConcepto"];
		/*$consulta="SELECT idCostoConcepto FROM 6011_costoConcepto WHERE plantel='".$param1["plantel"]."' AND idProgramaEducativo=".$param1["idProgramaEducativo"]." AND 
					idInstanciaPlanEstudio=".$param1["idInstanciaPlanEstudio"]." and grado=".$param1["grado"]." and idConcepto=".$param1["idServicio"]." AND idCiclo=".$param1["idCiclo"]." AND idPeriodo=".$param1["idPeriodo"];

		$idCostoConcepto=$con->obtenerValor($consulta);
		if($idCostoConcepto=="")
			$idCostoConcepto=-1;*/
			
		$consulta="SELECT valor FROM 6016_valoresReferenciaCosteoServicios WHERE idCostoConcepto =".$idCostoConcepto." AND idPlanPago=".$param1["idPlanPagos"]." AND noPago=".$param1["noPago"]." AND noColumna=0";

		$fechaVencimiento=$con->obtenerValor($consulta);
		$resObj=array();
		
		
		$resObj["montoDescuento"]=$resultado;
		$resObj["fechaInicio"]=date("Y-m-d");
		$resObj["fechaFin"]=$fechaVencimiento;
		return $resObj;
		
	}
	
	
	
	function permiteCapturaCalificacionEstandar($idGrupo,$tipoExamen,$noExamen,$objUsr)
	{
		global $con;
		$consulta="SELECT idInstanciaPlanEstudio ,idPlanEstudio,idMateria from 4520_grupos WHERE idGrupos=".$idGrupo;
		$fGrupo=$con->obtenerPrimeraFila($consulta);
		
		$idInstanciaPlanEstudio=$fGrupo[0];
		$idPlanEstudio=$fGrupo[1];
		$idMateria=$fGrupo[2];
		$consulta="SELECT idPerfil,calificacionMinimaAprobatoria FROM 4592_configuracionPerfilEvaluacion WHERE idPlanEstudio=".$idPlanEstudio." AND idInstanciaPlanEstudio in(".$idInstanciaPlanEstudio.",-1) AND idMateria=".$idMateria." and
				idGrupo in (".$idGrupo.",-1) AND tipoExamen=".$tipoExamen." AND noExamen=".$noExamen." order by idGrupo desc,idInstanciaPlanEstudio desc";

		$fPerfil=$con->obtenerPrimeraFila($consulta);
		$idPerfil=$fPerfil[0];
		if($idPerfil=="")
			$idPerfil=-1;
		
		
		$fConfTipoE=obtenerConfiguracionPlanEstudio(398,"",$idInstanciaPlanEstudio);

		if($fConfTipoE)
		{
			$consulta="SELECT asistencia FROM _398_gridTiposExamen WHERE idReferencia=".$fConfTipoE[0]." AND tipoExamen=".$tipoExamen." AND noExamen=".$noExamen;

			$porcentajeAsistencia=$con->obtenerValor($consulta);
			if($porcentajeAsistencia=="")
				$porcentajeAsistencia=100;
		
		}

			
		
		$tamPerfil=7;
		$codPerfil=str_pad($idPerfil,$tamPerfil,"0",STR_PAD_LEFT);
		$porcObtenido=100;
		$consulta="SELECT codigoUnidad FROM 4564_criteriosEvaluacionPerfilMateria WHERE codigoUnidad LIKE '".$codPerfil."%' AND idCriterio=16";
		$codCriterio=$con->obtenerValor($consulta);
		if($codCriterio!="")
		{
			$tConsiderar=$objUsr["tConsidera_".$codCriterio];
			$tObtenido=$objUsr["c_".$codCriterio];
			if($tConsiderar>0)
			{
				$porcObtenido=($tObtenido/$tConsiderar)*100;
			}
			
			
			
		}
		
		
		
		$oResultado["registraCalificacion"]=1;
		
		/*if($porcObtenido<$porcentajeAsistencia)
		{
			$oResultado["registraCalificacion"]=0;
			$oResultado["comentarios"]="El alumno NO cumple con el porcentaje mínimo(".removerCerosDerecha($porcentajeAsistencia)."%) requerido de asistencia. Porcentaje obtenido: ".removerCerosDerecha($porcObtenido)."%";
			$oResultado["totalEvaluacion"]=-3;
		}*/
		
		return $oResultado;
		
	}
	
	function generarPagoReferenciadoPaq1($idRegistro)
	{
		global $con;
		$idConcepto=25;
		$consulta="SELECT fechaCreacion,responsable,idEstado,codigoInstitucion,idInstanciaPlan FROM _769_tablaDinamica 
					WHERE id__769_tablaDinamica='".$idRegistro."'";
		$resp=$con->obtenerPrimeraFila($consulta);
		
		$plantel=$resp[3];
		$idInstancia=$resp[4];
		$idUsuario=$resp[1];
		$idProgramaEducativo=obtenerProgramaEducativoInstancia($idInstancia);
		$idGradoPeriodoCiclo=obtenerGradoPeriodoInstancia($idInstancia,$idUsuario);
			
		$datos=explode("_",$idGradoPeriodoCiclo);
		$idCiclo=$datos[0];
		$idPeriodo=$datos[1];
		$idGrado=$datos[2];
		
		$arrDimensiones=array();
		$arrDimensionesCosto["plantel"]=$plantel;
		$arrDimensionesCosto["idProgramaEducativo"]=$idProgramaEducativo;
		$arrDimensionesCosto["idInstanciaPlanEstudio"]=$idInstancia;
		$arrDimensionesCosto["grado"]=$idGrado;
		
		$oParametroCosto=array();
		$oParametroCosto["idUsuario"]=$idUsuario;
		$idPlanPagosAplica=-1;//Si -1 Aplica todos los planes
		$tabulador=generarTabuladorCostoServicio($idConcepto,$idCiclo,$idPeriodo,$arrDimensionesCosto,$oParametroCosto); 
		$arrTabulador=generarFechasPagosServicio($idConcepto,$tabulador);
		
		$arrDimensionesPagoReferenciado=array();
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IdInstanciaPlan",$idInstancia);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDCicloEscolar",$idCiclo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDPeriodo",$idPeriodo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDFormulario",769);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDRegistro",$idRegistro);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"Plantel",$plantel);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDProgramaEducativo",$idProgramaEducativo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDGrado",$idGrado);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDUsuario",$idUsuario);
		
		generarPagosReferenciados($plantel,$idUsuario,$arrTabulador,$arrDimensionesPagoReferenciado,"pagoAsesoriaRealizado",0);
		return true;
	}
	
	function generarPagoReferenciadoDiplomadoTit($idRegistro)
	{
		global $con;
		$idConcepto=16; // CURSO O DIPLOMADO DE TITULACION
		$consulta="SELECT fechaCreacion,responsable,idEstado,codigoInstitucion,idInstanciaPlan FROM _769_tablaDinamica 
					WHERE id__769_tablaDinamica='".$idRegistro."'";
		$resp=$con->obtenerPrimeraFila($consulta);
		
		$plantel=$resp[3];
		$idInstancia=$resp[4];
		$idUsuario=$resp[1];
		$idProgramaEducativo=obtenerProgramaEducativoInstancia($idInstancia);
		$idGradoPeriodoCiclo=obtenerGradoPeriodoInstancia($idInstancia,$idUsuario);
			
		$datos=explode("_",$idGradoPeriodoCiclo);
		$idCiclo=$datos[0];
		$idPeriodo=$datos[1];
		$idGrado=$datos[2];
		
		$arrDimensiones=array();
		$arrDimensionesCosto["plantel"]=$plantel;
		$arrDimensionesCosto["idProgramaEducativo"]=$idProgramaEducativo;
		$arrDimensionesCosto["idInstanciaPlanEstudio"]=$idInstancia;
		$arrDimensionesCosto["grado"]=$idGrado;
		
		$oParametroCosto=array();
		$oParametroCosto["idUsuario"]=$idUsuario;
		$idPlanPagosAplica=-1;//Si -1 Aplica todos los planes
		$tabulador=generarTabuladorCostoServicio($idConcepto,$idCiclo,$idPeriodo,$arrDimensionesCosto,$oParametroCosto); 
		$arrTabulador=generarFechasPagosServicio($idConcepto,$tabulador);
		
		$arrDimensionesPagoReferenciado=array();
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IdInstanciaPlan",$idInstancia);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDCicloEscolar",$idCiclo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDPeriodo",$idPeriodo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDFormulario",769);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDRegistro",$idRegistro);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"Plantel",$plantel);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDProgramaEducativo",$idProgramaEducativo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDGrado",$idGrado);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDUsuario",$idUsuario);
		
		generarPagosReferenciados($plantel,$idUsuario,$arrTabulador,$arrDimensionesPagoReferenciado,"pagoDiplomadoTitRealizado",0);
		return true;
	}
	
	function pagoDiplomadoTitRealizado($idMovimiento)
	{
		global $con;
		$consulta="SELECT valorCampo FROM 6012_detalleAsientoPago WHERE idAsientoPago=".$idMovimiento." AND idDimension=11";
		$idFormulario=$con->obtenerValor($consulta);
		$consulta="SELECT valorCampo FROM 6012_detalleAsientoPago WHERE idAsientoPago=".$idMovimiento." AND idDimension=12";
		$idRegistro=$con->obtenerValor($consulta);
		cambiarEtapaFormulario($idFormulario,$idRegistro,5);
	}
	
	function obtenerCiclosFiscalesAnio()
	{
		global $con;
		$arrCiclos=array();
		$consulta="SELECT ciclo,ciclo FROM 550_cicloFiscal ORDER BY ciclo";
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$o["valor"]=$fila[0];
			$o["etiqueta"]=$fila[1];
			array_push($arrCiclos,$o);
			
		}
		return $arrCiclos;	
	}
	
	function obtenerCiclosFiscalActivoAnio()
	{
		global $con;
		$arrCiclos=array();
		$consulta="SELECT ciclo FROM 550_cicloFiscal WHERE STATUS=1";
		$ciclo=$con->obtenerValor($consulta);
		return $ciclo;	
	}
?>