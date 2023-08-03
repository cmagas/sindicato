<?php	
function calcularNominaUsuarioIndividualV2($objAux)
{
	global $con;
//	
	$habilitarCache=true;	
	$idPerfilImportacion=0;
	$idNomina=$objAux->idNomina;
	$fPerfilImportacion=null;
	$fPerfil=null;
	$idQuincenaAplicacion=0;
	$arrCalculosDef=array();
	$arrAcumuladoresGlobales=array();
	$fechaCorteNomina="";
	$clasificacionPuesto="";
	$salarioMinimoGeneral=0;
	$cacheCalculos=array();
	//
	
	
	if(!isset($_SESSION["nominasEjecutadas"]))
	{
		$_SESSION["nominasEjecutadas"]=array();
		
	}
	
	
	if(!isset($_SESSION["nominasEjecutadas"][$objAux->idNomina]))
	{
		$_SESSION["nominasEjecutadas"][$objAux->idNomina]=array();
		
		$consulta="SELECT * FROM 672_nominasEjecutadas WHERE idNomina=".$objAux->idNomina;
		
		$filaNomina=$con->obtenerPrimeraFilaAsoc($consulta);
		$_SESSION["nominasEjecutadas"][$objAux->idNomina]["cacheCalculos"]=array();
		$_SESSION["nominasEjecutadas"][$objAux->idNomina]["filaNomina"]=$filaNomina;
		$_SESSION["nominasEjecutadas"][$objAux->idNomina]["idPerfilImportacion"]="0";
		$_SESSION["nominasEjecutadas"][$objAux->idNomina]["fPerfilImportacion"]="";
		if(($filaNomina["idPerfilImportacion"]!="")&&($filaNomina["idPerfilImportacion"]!="0"))
		{
			$consulta="SELECT idPerfilImportacion,columnaEmpleado,considerarSoloEmpleadosImportados FROM 662_perfilesImportacionNomina 
						WHERE idPerfilImportacionNomina=".$filaNomina["idPerfilImportacion"];			
			$fPerfilImportacion=$con->obtenerPrimeraFila($consulta);
			$idPerfilImportacion=$fPerfilImportacion[0];
			$_SESSION["nominasEjecutadas"][$objAux->idNomina]["idPerfilImportacion"]=$idPerfilImportacion;
			$_SESSION["nominasEjecutadas"][$objAux->idNomina]["fPerfilImportacion"]=$fPerfilImportacion;
		}
		
		$consulta="SELECT precisionDecimales,criterioPrecision,idFuncionRecalculo,idFuncionEliminacion,idPeriodicidad 
				FROM 662_perfilesNomina WHERE idPerfilesNomina=".$filaNomina["idPerfil"];
		$fPerfil=$con->obtenerPrimeraFila($consulta);
		$_SESSION["nominasEjecutadas"][$objAux->idNomina]["fPerfil"]=$fPerfil;
		
		
		$consulta="SELECT id__642_gElementosPeriodicidad FROM _642_gElementosPeriodicidad WHERE idReferencia=".$fPerfil[4]." AND noOrdinal=".$filaNomina["quincenaAplicacion"];
		$idQuincenaAplicacion=$con->obtenerValor($consulta);
		$_SESSION["nominasEjecutadas"][$objAux->idNomina]["idQuincenaAplicacion"]=$idQuincenaAplicacion;
		
		
		
		$consulta="select idCalculo,co.nombreConsulta,etiquetaConcepto,cveCalculo,c.idConsulta
				from 662_calculosNomina c,991_consultasSql co where idPerfil=".$filaNomina["idPerfil"]." and co.idConsulta=c.idConsulta order by orden";
	
	
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$arrCalculosDef[$fila[0]]=array();
			$arrCalculosDef[$fila[0]]["etiquetaCalculo"]="[".($fila[3]==""?$fila[0]:$fila[3])."] ".($fila[2]==""?$fila[1]:$fila[2]);
			$arrCalculosDef[$fila[0]]["arrCategorias"]=array();
			$consulta="SELECT idClasificador FROM 668_clasificacionCalculosNomina WHERE  idCalculo=".$fila[0];
			$rClasificador=$con->obtenerFilas($consulta);
			while($fClasificador=mysql_fetch_row($rClasificador))
			{
				array_push($arrCalculosDef[$fila[0]]["arrCategorias"],$fClasificador[0]);
			}
			
			
			$arrCalculosDef[$fila[0]]["columnaImportacion"]="";
			if($idPerfilImportacion!=0)
			{
				$consulta="SELECT idColumnaAsociada FROM 662_configuracionPerfilImportacion WHERE idPerfilImportacion=".$idPerfilImportacion." AND idCalculoNomina=".$fila[0];	
				$iColumna=$con->obtenerValor($consulta);
				if($iColumna!="")
				{
					$arrCalculosDef[$fila[0]]["columnaImportacion"]=$arrCeldasExcel[$iColumna];
				}
			}
			
			$consulta="SELECT * FROM 662_calculosNomina WHERE idCalculo=".$fila[0];
			$filaCalculo=$con->obtenerPrimeraFilaAsoc($consulta);
			$arrCalculosDef[$fila[0]]["filaCalculo"]=$filaCalculo;
			$arrCalculosDef[$fila[0]]["parametrosCalculo"]=array();
			
			$consulta="select idParametro,parametro from 993_parametrosConsulta where idConsulta=".$fila[4];
			$resParam=$con->obtenerFilas($consulta);
			while($fParametro=mysql_fetch_assoc($resParam))
			{
				array_push($arrCalculosDef[$fila[0]]["parametrosCalculo"],$fParametro);
			}
			
			$arrCalculosDef[$fila[0]]["acumuladoresCalculo"]=array();
			$consulta="select idAcumulador,operacion from 666_acumuladoresCalculo where idCalculo=".$fila[0];
			$rAcumuladores=$con->obtenerFilas($consulta);
			while($fAcumuladores=mysql_fetch_assoc($rAcumuladores))
			{
				array_push($arrCalculosDef[$fila[0]]["acumuladoresCalculo"],$fAcumuladores);
			}	
			
			$arrCalculosDef[$fila[0]]["filtrosAplicacion"]=array();
			$consulta="SELECT tipoElemento,idElemento FROM 669_filtroAplicacionCalculosNomina WHERE idCalculo=".$fila[0];
			$rFiltrosAplicacion=$con->obtenerFilas($consulta);
			while($fFiltroAplicacion=mysql_fetch_assoc($rFiltrosAplicacion))
			{
				$arrCalculosDef[$fila[0]]["filtrosAplicacion"][$fFiltroAplicacion["tipoElemento"]."_".$fFiltroAplicacion["idElemento"]]=1;
			}
			
			$arrCalculosDef[$fila[0]]["quincenaAplicacion"]=array();
			$consulta="SELECT cicloAplicacion,quincenaAplicacion FROM 670_quincenasAplicacionCalculosNomina WHERE idCalculo=".$fila[0];
			$rQuincenaAplicacion=$con->obtenerFilas($consulta);
			while($fQuincenaAplicacion=mysql_fetch_assoc($rQuincenaAplicacion))
			{
				$llaveQuincena="";
				
				if($fQuincenaAplicacion["cicloAplicacion"]=="")
				{
					$llaveQuincena=$fQuincenaAplicacion["quincenaAplicacion"];
				}
				else
				{
					$llaveQuincena=$fQuincenaAplicacion["cicloAplicacion"]."_".$fQuincenaAplicacion["quincenaAplicacion"];
				}
				
				$arrCalculosDef[$fila[0]]["quincenaAplicacion"][$llaveQuincena]=1;
			}
			
		}
		
		
		
		
		$_SESSION["nominasEjecutadas"][$objAux->idNomina]["arrCalculosDef"]=$arrCalculosDef;		
		
		$consulta="SELECT idAcumuladorNomina FROM 665_acumuladoresNomina WHERE nivelAcumulador=0 AND idPerfil=".$filaNomina["idPerfil"]."";
	
		$resAcumuladores=$con->obtenerFilas($consulta);
		$arrAcumuldoresGlobales=array();
		while($filaAcum=mysql_fetch_row($resAcumuladores))
		{
			$arrAcumuldoresGlobales[$filaAcum[0]]=0;
		}
		$_SESSION["nominasEjecutadas"][$objAux->idNomina]["arrAcumuldoresGlobales"]=$arrAcumuldoresGlobales;
		
		
		$fechaCorteNomina=obtenerFechaNominaAplicacion($idNomina);
		$_SESSION["nominasEjecutadas"][$objAux->idNomina]["fechaCorteNomina"]=$fechaCorteNomina;
		$_SESSION["nominasEjecutadas"][$objAux->idNomina]["clasificacionPuesto"]=array();
		$_SESSION["nominasEjecutadas"][$objAux->idNomina]["salarioMinimoGeneral"]=array();
	}
	else
	{
		$filaNomina=$_SESSION["nominasEjecutadas"][$objAux->idNomina]["filaNomina"];
		$idPerfilImportacion=$_SESSION["nominasEjecutadas"][$objAux->idNomina]["idPerfilImportacion"];
		$fPerfilImportacion=$_SESSION["nominasEjecutadas"][$objAux->idNomina]["fPerfilImportacion"];
		$fPerfil=$_SESSION["nominasEjecutadas"][$objAux->idNomina]["fPerfil"];
		$idQuincenaAplicacion=$_SESSION["nominasEjecutadas"][$objAux->idNomina]["idQuincenaAplicacion"];
		$arrCalculosDef=$_SESSION["nominasEjecutadas"][$objAux->idNomina]["arrCalculosDef"];
		$arrAcumuldoresGlobales=$_SESSION["nominasEjecutadas"][$objAux->idNomina]["arrAcumuldoresGlobales"];
		$fechaCorteNomina=$_SESSION["nominasEjecutadas"][$objAux->idNomina]["fechaCorteNomina"];
		
		
		
	}
	$idPerfil=$filaNomina["idPerfil"];
	$idUnidadAgrupadora=$filaNomina["idUnidadAgrupadora"];
	$accionPrecision=$fPerfil[1];
	$precision=$fPerfil[0];
	$idPeriodicidad=$fPerfil[4];
	

	$consulta="SELECT *,
			(SELECT idZonaGeograficaSMG FROM 817_organigrama WHERE codigoUnidad=a.Institucion) as idZonaSMG,
			i.Genero as genero
			 FROM 801_adscripcion a,802_identifica i WHERE a.idUsuario=".$objAux->idUsuario." and i.idUsuario=a.idUsuario";
	$fAdscripcion=$con->obtenerPrimeraFilaAsoc($consulta);
	
	////
	
	
	$cadObj='	{
					"idUsuario":"",
					"idPerfil":"'.$idPerfil.'",
					"fechaContratacion":"",
					"sueldoBase":"",
					"arrCalculosIndividuales":[],
					"arrCalculosGlobales":[],
					"ciclo":"'.$filaNomina["ciclo"].'",
					"quincena":"'.$filaNomina["quincenaAplicacion"].'",
					"idQuincena":"'.$idQuincenaAplicacion.'",
					"nFaltas":"",
					"nRetardos":"",
					"departamento":"",
					"totalDeducciones":"",
					"totalPercepciones":"",
					"sueldoNeto":"",
					"puesto":"",
					"fechaBaja":"",
					"fechaBasificacion":"",
					"fechaBaseNomina":"",
					"horasTrabajador":"0",
					"institucion":"",
					"tipoContratacion":"",
					"fechaIniIncidencia":"'.$filaNomina["fechaInicioIncidencias"].'",
					"fechaFinIncidencia":"'.$filaNomina["fechaFinIncidencias"].'",
					"idZona":"",
					"situacion":"",
					"tipoPago":"",
					"fechaCorteNomina":"",
					"idNomina":"'.$objAux->idNomina.'",
					"idPerfil":"'.$idPerfil.'",
					"idPeriodicidad":"'.$idPeriodicidad.'",
					"idEntidadAgrupacion":"0",
					"idUnidadAgrupadora":"0",
					"idIdentificadorComplementario":"0",
					"etiquetaIdentificadorComp":"",
					"calcularIdNomina":"",
					"objImportacion":"",
					"acumuladoBaseGravablePercepcion":"0",
					"acumuladoBaseGravableDeduccion":"0",
					"idAsientoNomina":"-1",
					"SMG":"",
					"genero":"",
					"clasificacionPuesto":""
					
				}';
	$cadObjGlobal='	{
						"arrCalculos":[],
						"totalDeducciones":"0",
						"totalPercepciones":"0",
						"nPlazas":"0"
					}';
	$objUsuario=json_decode($cadObj);
	$objGlobal=json_decode($cadObjGlobal);
	
	
	if(!isset($_SESSION["nominasEjecutadas"][$objAux->idNomina]["salarioMinimoGeneral"][$fAdscripcion["idZonaSMG"]]))
	{
		$salarioMinimoGeneral=obtenerSalarioMinimo($fAdscripcion["idZonaSMG"],$fechaCorteNomina);
	}
	else
	{
		$salarioMinimoGeneral=$_SESSION["nominasEjecutadas"][$objAux->idNomina]["salarioMinimoGeneral"][$fAdscripcion["idZonaSMG"]];
	}
	
	
	$objUsuario->fechaCorteNomina=$fechaCorteNomina;
	$objUsuario->SMG=$salarioMinimoGeneral;
	$objUsuario->arrCalculosGlobales=array();
	$objUsuario->objImportacion=NULL;
	$objUsuario->objConfiguracion=NULL;
	$objUsuario->fechaBaseNomina=$fechaCorteNomina;
	
	$objUsuario->idUsuario=$objAux->idUsuario;
	$objUsuario->idZona=$objAux->idZona;
	$objUsuario->puesto=$objAux->idPuesto;
	$objUsuario->tipoContratacion=$objAux->tipoContratacion;
	
	$objUsuario->fechaBaja="";
	$objUsuario->fechaBasificacion="";

	$objUsuario->institucion=$fAdscripcion["Institucion"];
	$objUsuario->fechaContratacion=$fAdscripcion["fechaIngresoInstitucion"];
	$objUsuario->sueldoBase=0;
	$objUsuario->nFaltas=0;
	$objUsuario->nRetardos=0;
	$objUsuario->totalDeducciones=0;
	$objUsuario->totalPercepciones=0;
	$objUsuario->sueldoNeto=0;
	$objUsuario->departamento="";
	$objUsuario->situacion=1;
	
	$objUsuario->tipoPago=$fAdscripcion["tipoPago"];
	if($objUsuario->tipoPago=="")
		$objUsuario->tipoPago=-1;
		
	$objUsuario->idEntidadAgrupacion=0;
	$objUsuario->idUnidadAgrupadora=0;
	
	$objUsuario->genero=$fAdscripcion["genero"]==1?"M":"H";
	
	if(!isset($_SESSION["nominasEjecutadas"][$objAux->idNomina]["clasificacionPuesto"][$objUsuario->puesto]))
	{
		$consulta="SELECT clasificacionPuesto FROM _632_tablaDinamica WHERE id__632_tablaDinamica=".$objUsuario->puesto;
		$clasificacionPuesto=$con->obtenerValor($consulta);
		$_SESSION["nominasEjecutadas"][$objAux->idNomina]["clasificacionPuesto"][$objUsuario->puesto]=$clasificacionPuesto;
	}
	else
		$clasificacionPuesto=$_SESSION["nominasEjecutadas"][$objAux->idNomina]["clasificacionPuesto"][$objUsuario->puesto];
	
	
	
	
	$objUsuario->clasificacionPuesto=$clasificacionPuesto;
	
	$consulta="SELECT arregloInformacion FROM 672_registrosArchivosImportadosNomina WHERE idNomina=".$objUsuario->idNomina." AND idEmpleado=".$objAux->idUsuario;
	$arregloInformacion=$con->obtenerValor($consulta);
	if($arregloInformacion!="")
	{
		$objUsuario->objImportacion=unserialize(bD($arregloInformacion));	
	}
	
	
	
	$cacheCalculos=NULL;
	if($habilitarCache)
		$cacheCalculos=array();

	
	
	foreach($arrAcumuldoresGlobales as $acumG=>$valor)
	{
		  $arrAcumuldoresGlobales[$acumG]=0;	
	}
	//$_SESSION["nominasEjecutadas"][$objAux->idNomina]["cacheCalculos"]
	if(limpiarAsientoNomina($idNomina,$objAux->idUsuario))
	{
		realizarCalculosGlobalesV2($objUsuario,$arrCalculosDef,$arrAcumuldoresGlobales,
									$cacheCalculos,
									$idPerfil,$precision,$accionPrecision);
		$idAsientoNomina=escribirAsientoNomina($objUsuario,$idNomina);
		
		if($idAsientoNomina)
		{
			$objUsuario->idAsientoNomina=$idAsientoNomina;
			return $objUsuario;
		}
	}
	return NULL;
	

	
}


/*
function realizarCalculosGlobalesV2Aux(&$obj,$arrCalculosDef,&$arrAcumuladores,&$cacheCalculos,$idPerfil=1,$precision=2,$accionPrecision=2)
{
	global $con;
	global $estadisticasCalculo;
	
	$modoDebugger=false;
	$calculoInd=array();
	$consulta="select * from 662_calculosNomina where idUsuarioAplica is null and idPerfil=".$idPerfil." order by orden";
	$resCalculos=$con->obtenerFilas($consulta);
	if($modoDebugger)
	{
		echo '<br><br><span class="letraRojaSubrayada8">Calculando n&oacute;mina de:</span> <span>'.$obj->idUsuario.'</span><br><br>';	
	}
	
	$pruebaArreglo=array();
	$referencia=NULL;
	while($filaCalculo=mysql_fetch_row($resCalculos))
	{
		
		$afectacionNomina=$filaCalculo[2];
		$arrParametros=array();
		$idCalculo=$filaCalculo[0];
		$considerar=true;
		switch($afectacionNomina)
		{
			case 1: // Permanente
			break;
			case 2: //No afectar
				if($filaCalculo[3]=="")
					$considerar=false;
				else
				{
					if(($filaCalculo[3]>=$obj->ciclo)&&($filaCalculo[4]>=$obj->quincena)&&($filaCalculo[5]<$filaCalculo[10]))
						$considerar=true;
					else
						$considerar=false;
				}
			break;
			case 3: //aplicar a quincenas
				if(($filaCalculo[3]>=$obj->ciclo)&&($filaCalculo[4]>=$obj->quincena)&&($filaCalculo[5]<$filaCalculo[10]))
					$considerar=true;
				else
					$considerar=false;
			
			break;
		}
		
		$idTabulacion=$obj->puesto;
		$tipoPuesto=$obj->tipoContratacion;
		if(isset($arrCalculosDef[$idCalculo]))
		{
			$consulta="select count(*) from 660_afectacionesDeducPercepciones where idDeduccionPercepcion=".$idCalculo;	
			$nReg=$con->obtenerValor($consulta);
			if($nReg==0)
			{
				$consulta="select count(*) from 9115_calculosVSPuestos where idCalculo=".$idCalculo;	
				$nReg=$con->obtenerValor($consulta);
			}
			if($nReg>0)	
			{
				$consulta="select idTipoPuestoAfecta from 660_afectacionesDeducPercepciones where idDeduccionPercepcion=".$idCalculo." and afectacion=".$tipoPuesto;
				$idTipoPuestoAfecta=$con->obtenerValor($consulta);
				if($idTipoPuestoAfecta=="")
				{
					$consulta="SELECT idPuesto FROM `9115_calculosVSPuestos` WHERE idCalculo=".$idCalculo." AND cvePuesto=".$obj->puesto."";
					$idTipoPuestoAfecta=$con->obtenerValor($consulta);
					if($idTipoPuestoAfecta=="")
						$considerar=false;
				}
			}
		}
		else
			$considerar=false;
		if($considerar)
		{
			
			$obj->arrCalculosGlobales[$idCalculo]["arrCategorias"]=$arrCalculosDef[$idCalculo]["arrCategorias"];
			$obj->arrCalculosGlobales[$idCalculo]["idConsulta"]=$filaCalculo[1];
			$obj->arrCalculosGlobales[$idCalculo]["calculado"]=1;
			$obj->arrCalculosGlobales[$idCalculo]["usoCache"]=0;
			$obj->arrCalculosGlobales[$idCalculo]["horaInicio"]=microtime();
			$obj->arrCalculosGlobales[$idCalculo]["termino"]=0;
			$obj->arrCalculosGlobales[$idCalculo]["orden"]=$filaCalculo[9];
			$obj->arrCalculosGlobales[$idCalculo]["tipoCalculo"]=$filaCalculo[8];//2 Percepcion 1 Deduccion
			$consulta="select idParametro,parametro from 993_parametrosConsulta where idConsulta=".$filaCalculo[1];
			if($modoDebugger)
				echo $consulta;
			$resParam=$con->obtenerFilas($consulta);
			$cadParametros='"objDatosUsr":"","marcaParametro":""';	
			$marcaParam="";
			while($filaParam=mysql_fetch_row($resParam))
			{
				$consulta="select valor,tipoValor from 663_valoresCalculos where idCalculo=".$filaCalculo[0]." and idParametro=".$filaParam[0];
				if($modoDebugger)
					echo $consulta;
				$filaValor=$con->obtenerPrimeraFila($consulta);
				$valor=$filaValor[0];
				switch($filaValor[1])
				{
					case 2:
						if(isset($obj->arrCalculosGlobales[$valor]))
							$valor=$obj->arrCalculosGlobales[$valor]["valorCalculado"];
						else
							$valor=0;
					break;
					case 21:
						if(isset($arrAcumuladores[$valor]))
							$valor=$arrAcumuladores[$valor];
						else
							$valor=0;
					break;
				}
				if($cadParametros=="")
					$cadParametros='"'.$filaParam[1].'":"'.$valor.'"';
				else
					$cadParametros.=',"'.$filaParam[1].'":"'.$valor.'"';
				
				if($marcaParam=="")
				{
					$marcaParam=str_replace("'","",str_replace('$',"_",str_replace(".","_",$valor)));
					
				}
				else
					$marcaParam.="_".str_replace("'","",str_replace('$',"_",str_replace(".","_",$valor)));
			}
			
			$cObjCalculoAsociado=NULL;
			$idConsultaAsociada="";
			$cveCalculoAsociado="";
			
			
			
			if(($filaCalculo[18]!="")&&($filaCalculo[18]!="0"))
			{
				
				foreach($obj->arrCalculosGlobales as $idCalculoArr=>$resto)
				{
					if($idCalculoArr==$filaCalculo[18])
					{
						
						$idConsultaAsociada=$resto["idConsulta"];
						$cveCalculoAsociado=$resto["cveConcepto"];
						
						$importeTotal=$resto["importeGravado"]+$resto["importeExcento"];
						
						$cCalculoAsociado='{"idCalculo":"'.$idCalculoArr.'","idConsulta":"'.$idConsultaAsociada.'","cveConcepto":"'.$cveCalculoAsociado.
											'","importeTotal":"'.$importeTotal.'","importeGravado":"'.$resto["importeGravado"].'","importeExcento":"'.
											$resto["importeExcento"].'"}';
						
						$cObjCalculoAsociado=json_decode($cCalculoAsociado);
					}
				}
				
				if(!$cObjCalculoAsociado)
				{
					
					$cObjCalculoAsociado=json_decode('{"idCalculo":"'.$filaCalculo[18].'","idConsulta":"-1000","cveConcepto":"","importeTotal":"0","importeGravado":"0","importeExcento":"0"}');
					
				}
				
				
				
			}
			
			if($cObjCalculoAsociado)
			{
				$cadParametros.=',"objCalculoAsociado":""';
			}
			
			$cadParametros='{'.$cadParametros.'}';
			
			$objParametros=json_decode($cadParametros);
			$objParametros->objDatosUsr=$obj;
			if($cObjCalculoAsociado)
			{
				$objParametros->objCalculoAsociado=$cObjCalculoAsociado;
				
			}
			
			$objParametros->marcaParametro=$marcaParam;
			$obj->arrCalculosGlobales[$idCalculo]["marcaParametro"]=$objParametros->marcaParametro;
			
			$valCalculado=0;

			$normalizarValor=true;
			$etiquetaCalculoAux="";
			$etiquetaCalculoComp="";
			if((gettype($arrCalculosDef[$idCalculo])=='array')&&(isset($obj->objImportacion))&&(sizeof($obj->objImportacion)>0))
			{
				
				if(isset($obj->objImportacion[$arrCalculosDef[$idCalculo]["columnaImportacion"]]))
				{
					$valCalculado=trim($obj->objImportacion[$arrCalculosDef[$idCalculo]["columnaImportacion"]]);
					
					$normalizarValor=false;
				}
				if($valCalculado=="")
					$valCalculado=0;
					
			}
			else
			{
				$resultadoCalculo=0;
				if	( 
						($filaCalculo[18]=="")  ||
						(($filaCalculo[18]!="") && ( ($filaCalculo[21]==1) ||  ($objParametros->objCalculoAsociado->importeTotal>0)  ) )
						
						
					)
				
				{
					$resultadoCalculo=resolverExpresionCalculoPHP($filaCalculo[1],$objParametros,($filaCalculo[20]==1?$cacheCalculos:NULL));
				}
				else
					$obj->arrCalculosGlobales[$idCalculo]["calculado"]=0;
				
				
					
				
				if(gettype($resultadoCalculo)=='array')
				{
					$valCalculado=$resultadoCalculo["valorCalculado"];
					if(isset($resultadoCalculo["etiquetaCalculo"]))
					{
						$etiquetaCalculoAux=$resultadoCalculo["etiquetaCalculo"];
					}
					
					if(isset($resultadoCalculo["etiquetaCalculoComp"]))
					{
						$etiquetaCalculoComp=$resultadoCalculo["etiquetaCalculoComp"];
					}
					
					if(isset($resultadoCalculo["cache"]))
					{
						$obj->arrCalculosGlobales[$idCalculo]["usoCache"]=1;
					}
					
				}
				else
					$valCalculado=str_replace("'","",$resultadoCalculo);
			}
			$obj->arrCalculosGlobales[$idCalculo]["termino"]=microtime();	
			$obj->arrCalculosGlobales[$idCalculo]["tiempoEjecucion"]=$obj->arrCalculosGlobales[$idCalculo]["termino"]-$obj->arrCalculosGlobales[$idCalculo]["horaInicio"];
			
			if($valCalculado=="")
				$valCalculado=0;
			if((is_numeric($valCalculado))&&($normalizarValor))	
			{
				switch($accionPrecision)
				{
					case 1:
						$valCalculado=truncarValor($valCalculado,$precision);
					break;
					case 2:
						$valCalculado=str_replace(",","",number_format($valCalculado,$precision));
					break;
				}
			}
			
			if($valCalculado=="''")
				$valCalculado=0;
			$consulta="select idAcumulador,operacion from 666_acumuladoresCalculo where idCalculo=".$idCalculo;
			if($modoDebugger)
				echo $consulta;
			$resAcum=$con->obtenerFilas($consulta);
			
			while($filaAcumulador=mysql_fetch_row($resAcum))
			{
				if(isset($arrAcumuladores[$filaAcumulador[0]]))	
				{
					
					switch($filaAcumulador[1])	
					{
						case '+':
							$arrAcumuladores[$filaAcumulador[0]]+=$valCalculado;
						break;
						case '-':
							$arrAcumuladores[$filaAcumulador[0]]-=$valCalculado;
						break;
						case '*':
							$arrAcumuladores[$filaAcumulador[0]]*=$valCalculado;
						break;
						case '/':
							if($valor!=0)
								$arrAcumuladores[$filaAcumulador[0]]/=$valCalculado;
							else
								$arrAcumuladores[$filaAcumulador[0]]=0;
						break;
						case '=':
							$arrAcumuladores[$filaAcumulador[0]]=$valCalculado;
						break;	
					}
				}
			}

			if(gettype($arrCalculosDef[$idCalculo])=='array')
				$obj->arrCalculosGlobales[$idCalculo]["nombreCalculo"]=$arrCalculosDef[$idCalculo]["etiquetaCalculo"].$etiquetaCalculoComp;
			else
				$obj->arrCalculosGlobales[$idCalculo]["nombreCalculo"]=$arrCalculosDef[$idCalculo].$etiquetaCalculoComp;
				
			if($etiquetaCalculoAux!="")
			{
				$obj->arrCalculosGlobales[$idCalculo]["nombreCalculo"]="[".($filaCalculo[17]==""?$filaCalculo[0]:$filaCalculo[17])."] ".$etiquetaCalculoAux;
			}
				
				
			$obj->arrCalculosGlobales[$idCalculo]["valorCalculado"]=$valCalculado;
			$obj->arrCalculosGlobales[$idCalculo]["cveConcepto"]=$filaCalculo[17];
			$obj->arrCalculosGlobales[$idCalculo]["idCategoriaSAT"]=$filaCalculo[12];
			$obj->arrCalculosGlobales[$idCalculo]["idCalculoAsociado"]=$filaCalculo[18];
			
			
			
			$obj->arrCalculosGlobales[$idCalculo]["idConsultaAsociada"]="";
			$obj->arrCalculosGlobales[$idCalculo]["cveConseptoAsociado"]="";
			
			
			if(($filaCalculo[18]!="")&&($filaCalculo[18]!="-1"))
			{
				$consulta="SELECT idConsulta,cveCalculo FROM 662_calculosNomina WHERE idCalculo=".$filaCalculo[18];
				$fCalculoAsociado=$con->obtenerPrimeraFilaAsoc($consulta);
				$obj->arrCalculosGlobales[$idCalculo]["idConsultaAsociada"]=$idConsultaAsociada;
				$obj->arrCalculosGlobales[$idCalculo]["cveConseptoAsociado"]=$cveCalculoAsociado;
			}
			
			if($obj->arrCalculosGlobales[$idCalculo]["tipoCalculo"]==1) //Deduccion
			{
				$obj->totalDeducciones+=$valCalculado;
				$obj->arrCalculosGlobales[$idCalculo]["importeGravado"]=0;
				$obj->arrCalculosGlobales[$idCalculo]["importeExcento"]=$valCalculado;
			}
			else
			{
				if($obj->arrCalculosGlobales[$idCalculo]["tipoCalculo"]==2)
				{
					$obj->totalPercepciones+=$valCalculado;
					$obj->arrCalculosGlobales[$idCalculo]["importeGravado"]=$valCalculado;
					$obj->arrCalculosGlobales[$idCalculo]["importeExcento"]=0;
				}
				else
				{
					$obj->arrCalculosGlobales[$idCalculo]["importeGravado"]=0;
					$obj->arrCalculosGlobales[$idCalculo]["importeExcento"]=$valCalculado;
				}
			}
			
			if(($filaCalculo[13]!="")&&($filaCalculo[13]!="-1"))
			{
				if($valCalculado=="")
					$valCalculado=0;
				$cObjFuncion='{"importe":"'.$valCalculado.'","cveConcepto":"'.$filaCalculo[17].'","idConcepto":"'.$idCalculo.
								'","objDatosUsr":""}';
				$oFuncion=json_decode($cObjFuncion);
				$oFuncion->objDatosUsr=$obj;
				$cache=NULL;
				
				
				$arrGravamen=resolverExpresionCalculoPHP($filaCalculo[13],$oFuncion,$cache);
		
				$obj->arrCalculosGlobales[$idCalculo]["importeGravado"]=$arrGravamen["importeGravado"];
				$obj->arrCalculosGlobales[$idCalculo]["importeExcento"]=$arrGravamen["importeExento"];
				
				
			}
			
			if($obj->arrCalculosGlobales[$idCalculo]["tipoCalculo"]==2)
				$obj->acumuladoBaseGravablePercepcion+=$obj->arrCalculosGlobales[$idCalculo]["importeGravado"];
			else
				if($obj->arrCalculosGlobales[$idCalculo]["tipoCalculo"]==1)
					$obj->acumuladoBaseGravableDeduccion+$obj->arrCalculosGlobales[$idCalculo]["importeGravado"];
			
			$referencia=&$obj->arrCalculosGlobales[$idCalculo];
			$referencia["distriCuentas"]=array();
			$arrCuentas=array();
			$consulta="select codCuentaAfectacion,codCuentaAfectacionSimple,porcentaje,tipoAfectacion,idEstructura,idBeneficiario,tipoBeneficiario,idTipoPresupuesto
						from 661_afectacionesCuentasDeducPercepciones where idDeduccionPercepcion=".$idCalculo;
			
			if($modoDebugger)
				echo $consulta;
			$resAfectCuentas=$con->obtenerFilas($consulta);
			
			$objCuenta=array();
			while($filaAfectCuentas=mysql_fetch_row($resAfectCuentas))
			{
				$objCuenta["codCuentaAfectacion"]=$filaAfectCuentas[0];
				$objCuenta["codCuentaAfectacionSimple"]=$filaAfectCuentas[1];
				$objCuenta["porcentaje"]=$filaAfectCuentas[2];
				$objCuenta["tipoAfectacion"]=$filaAfectCuentas[3];
				$objCuenta["idEstructura"]=$filaAfectCuentas[4];
				$objCuenta["idBeneficiario"]=$filaAfectCuentas[5];
				$objCuenta["tipoBeneficiario"]=$filaAfectCuentas[6];
				$objCuenta["valorAsignado"]=$valCalculado*($objCuenta["porcentaje"]/100);
				$consulta="select codigo from 508_tiposPresupuesto where idTipoPresupuesto=".$filaAfectCuentas[7];
				if($modoDebugger)
					echo $consulta;
				$codigoPre=$con->obtenerValor($consulta);				
				$objCuenta["tipoPresupuesto"]=$codigoPre;
				$objCuenta["idTipoPresupuesto"]=$filaAfectCuentas[7];
				if($filaAfectCuentas[5]!='')
				{
					if($filaAfectCuentas[5]=="0")
						$consulta="select cu.cuenta,cu.idBanco from 801_adscripcion a,823_cuentasUsuario cu where cu.idCuentaUsuario=a.idCuentaDeposito and a.idUsuario=".$obj->idUsuario;
					else
					{
						if($filaAfectCuentas[6]==1)
							$consulta="select txtCuenta,cmbbanco from _217_tablaDinamica where id__217_tablaDinamica=".$objCuenta["idBeneficiario"];
						else
							$consulta="select txtCuenta,cmbbanco from _216_tablaDinamica where id__216_tablaDinamica=".$objCuenta["idBeneficiario"];
						
					}
					if($modoDebugger)
						echo $consulta;
					$filaCuenta=$con->obtenerPrimeraFila($consulta);
					$objCuenta["cuentaBancaria"]=$filaCuenta[0];
					$objCuenta["idBanco"]=$filaCuenta[1];
				}
				else
					$objCuenta["cuentaBancaria"]="";
				
				
				array_push($arrCuentas,$objCuenta);
			}
			$referencia["distriCuentas"]=$arrCuentas;
		}
	}
	
	$obj->sueldoNeto=$obj->totalPercepciones-$obj->totalDeducciones;
	switch($accionPrecision)
	  {
		  case 1:
			  $obj->sueldoNeto=truncarValor($obj->sueldoNeto,$precision);
		  break;
		  case 2:
			  $obj->sueldoNeto=str_replace(",","",number_format($obj->sueldoNeto,$precision));
		  break;
	  }

}*/

function realizarCalculosGlobalesV2(&$obj,$arrCalculosDef,&$arrAcumuladores,&$cacheCalculos,$idPerfil=1,$precision=2,$accionPrecision=2)
{
	global $con;
	global $estadisticasCalculo;
	
	$modoDebugger=false;
	$calculoInd=array();
	
	if($modoDebugger)
	{
		echo '<br><br><span class="letraRojaSubrayada8">Calculando n&oacute;mina de:</span> <span>'.$obj->idUsuario.'</span><br><br>';	
	}
	
	$pruebaArreglo=array();
	$referencia=NULL;
	
	foreach($arrCalculosDef as $idCalculo=>$resto)
	{
		
		$filaCalculo=$resto["filaCalculo"];
		$afectacionNomina=$filaCalculo["afectacionNomina"];
		$arrParametros=array();

		$considerar=true;
		switch($afectacionNomina)
		{
			case 1: // Permanente
			break;
			case 2: //No afectar
				$considerar=false;
			break;
			case 3: //aplicar a quincenas
				if(!isset($resto["quincenaAplicacion"][$obj->quincena]))
				{
					if(!isset($resto["quincenaAplicacion"][$obj->ciclo."_".$obj->quincena]))
					{
						$considerar=false;
					}
					
				}
			
			break;
		}
		
		$idTabulacion=$obj->puesto;
		$tipoPuesto=$obj->tipoContratacion;
		
		if(count($resto["filtrosAplicacion"])>0)
		{
			if(!isset($resto["filtrosAplicacion"]["1_".$obj->tipoContratacion])) //Tipo Contratacion
			{
				if(!isset($resto["filtrosAplicacion"]["2_".$obj->clasificacionPuesto])) //Clasificación del Puesto
				{
					if(!isset($resto["filtrosAplicacion"]["3_".$obj->puesto])) //Puesto
					{
						$considerar=false;
					}
				}
			}
		}

		if($considerar)
		{
			
			$obj->arrCalculosGlobales[$idCalculo]["arrCategorias"]=$resto["arrCategorias"];
			$obj->arrCalculosGlobales[$idCalculo]["idConsulta"]=$filaCalculo["idConsulta"];
			$obj->arrCalculosGlobales[$idCalculo]["calculado"]=1;
			$obj->arrCalculosGlobales[$idCalculo]["usoCache"]=0;
			$obj->arrCalculosGlobales[$idCalculo]["horaInicio"]=microtime();
			$obj->arrCalculosGlobales[$idCalculo]["termino"]=0;
			$obj->arrCalculosGlobales[$idCalculo]["orden"]=$filaCalculo["orden"];
			$obj->arrCalculosGlobales[$idCalculo]["tipoCalculo"]=$filaCalculo["tipoCalculo"];//2 Percepcion 1 Deduccion
			
			$cadParametros='"objDatosUsr":"","marcaParametro":""';	
			$marcaParam="";
			
			foreach($resto["parametrosCalculo"] as $filaParam)
			{
				$consulta="select valor,tipoValor from 663_valoresCalculos where idCalculo=".$filaCalculo["idCalculo"]." and idParametro=".$filaParam["idParametro"];
				if($modoDebugger)
					echo $consulta;
				$filaValor=$con->obtenerPrimeraFila($consulta);
				$valor=$filaValor[0];
				switch($filaValor[1])
				{
					case 2:
						if(isset($obj->arrCalculosGlobales[$valor]))
							$valor=$obj->arrCalculosGlobales[$valor]["valorCalculado"];
						else
							$valor=0;
					break;
					case 21:
						if(isset($arrAcumuladores[$valor]))
							$valor=$arrAcumuladores[$valor];
						else
							$valor=0;
					break;
				}
				if($cadParametros=="")
					$cadParametros='"'.$filaParam["parametro"].'":"'.$valor.'"';
				else
					$cadParametros.=',"'.$filaParam["parametro"].'":"'.$valor.'"';
				
				if($marcaParam=="")
				{
					$marcaParam=str_replace("'","",str_replace('$',"_",str_replace(".","_",$valor)));
					
				}
				else
					$marcaParam.="_".str_replace("'","",str_replace('$',"_",str_replace(".","_",$valor)));
			}
			
			$cObjCalculoAsociado=NULL;
			$idConsultaAsociada="";
			$cveCalculoAsociado="";
			
			
			
			if(($filaCalculo["idCalculoAsociado"]!="")&&($filaCalculo["idCalculoAsociado"]!="0"))
			{
				
				foreach($obj->arrCalculosGlobales as $idCalculoArr=>$restoAux)
				{
					if($idCalculoArr==$filaCalculo["idCalculoAsociado"])
					{
						
						$idConsultaAsociada=$restoAux["idConsulta"];
						$cveCalculoAsociado=$restoAux["cveConcepto"];
						
						$importeTotal=$restoAux["importeGravado"]+$restoAux["importeExcento"];
						
						$cCalculoAsociado='{"idCalculo":"'.$idCalculoArr.'","idConsulta":"'.$idConsultaAsociada.'","cveConcepto":"'.$cveCalculoAsociado.
											'","importeTotal":"'.$importeTotal.'","importeGravado":"'.$restoAux["importeGravado"].'","importeExcento":"'.
											$restoAux["importeExcento"].'"}';
						
						$cObjCalculoAsociado=json_decode($cCalculoAsociado);
					}
				}
				
				if(!$cObjCalculoAsociado)
				{
					
					$cObjCalculoAsociado=json_decode('{"idCalculo":"'.$filaCalculo["idCalculoAsociado"].
					'","idConsulta":"-1000","cveConcepto":"","importeTotal":"0","importeGravado":"0","importeExcento":"0"}');
					
				}
				
				
				
			}
			
			if($cObjCalculoAsociado)
			{
				$cadParametros.=',"objCalculoAsociado":""';
			}
			
			$cadParametros='{'.$cadParametros.'}';
			
			$objParametros=json_decode($cadParametros);
			$objParametros->objDatosUsr=$obj;
			if($cObjCalculoAsociado)
			{
				$objParametros->objCalculoAsociado=$cObjCalculoAsociado;
				
			}
			
			$objParametros->marcaParametro=$marcaParam;
			$obj->arrCalculosGlobales[$idCalculo]["marcaParametro"]=$objParametros->marcaParametro;
			
			$valCalculado=0;

			$normalizarValor=true;
			$etiquetaCalculoAux="";
			$etiquetaCalculoComp="";
			if((gettype($arrCalculosDef[$idCalculo])=='array')&&(isset($obj->objImportacion))&&(sizeof($obj->objImportacion)>0))
			{
				
				if(isset($obj->objImportacion[$arrCalculosDef[$idCalculo]["columnaImportacion"]]))
				{
					$valCalculado=trim($obj->objImportacion[$arrCalculosDef[$idCalculo]["columnaImportacion"]]);
					
					$normalizarValor=false;
				}
				if($valCalculado=="")
					$valCalculado=0;
					
			}
			else
			{
				$resultadoCalculo=0;
				if	( 
						($filaCalculo["idCalculoAsociado"]=="")  ||
						(($filaCalculo["idCalculoAsociado"]!="") && ( ($filaCalculo["calcularSiCalculoAsociadoIgualCero"]==1) ||  
						($objParametros->objCalculoAsociado->importeTotal>0)  ) )
					)
				
				{
					//$resultadoCalculo=resolverExpresionCalculoPHP($filaCalculo["idConsulta"],$objParametros,($filaCalculo["incluirEnCache"]==1?$cacheCalculos:NULL));
				}
				else
					$obj->arrCalculosGlobales[$idCalculo]["calculado"]=0;
				
				
					
				
				if(gettype($resultadoCalculo)=='array')
				{
					$valCalculado=$resultadoCalculo["valorCalculado"];
					if(isset($resultadoCalculo["etiquetaCalculo"]))
					{
						$etiquetaCalculoAux=$resultadoCalculo["etiquetaCalculo"];
					}
					
					if(isset($resultadoCalculo["etiquetaCalculoComp"]))
					{
						$etiquetaCalculoComp=$resultadoCalculo["etiquetaCalculoComp"];
					}
					
					if(isset($resultadoCalculo["cache"]))
					{
						$obj->arrCalculosGlobales[$idCalculo]["usoCache"]=1;
					}
					
				}
				else
					$valCalculado=str_replace("'","",$resultadoCalculo);
			}
			$obj->arrCalculosGlobales[$idCalculo]["termino"]=microtime();	
			$obj->arrCalculosGlobales[$idCalculo]["tiempoEjecucion"]=$obj->arrCalculosGlobales[$idCalculo]["termino"]-$obj->arrCalculosGlobales[$idCalculo]["horaInicio"];
			
			if($valCalculado=="")
				$valCalculado=0;
			if((is_numeric($valCalculado))&&($normalizarValor))	
			{
				switch($accionPrecision)
				{
					case 1:
						$valCalculado=truncarValor($valCalculado,$precision);
					break;
					case 2:
						$valCalculado=str_replace(",","",number_format($valCalculado,$precision));
					break;
				}
			}
			
			if($valCalculado=="''")
				$valCalculado=0;
			
			foreach($resto["acumuladoresCalculo"] as $filaAcumulador)
			{
				if(isset($arrAcumuladores[$filaAcumulador["idAcumulador"]]))	
				{
					
					switch($filaAcumulador["operacion"])	
					{
						case '+':
							$arrAcumuladores[$filaAcumulador["idAcumulador"]]+=$valCalculado;
						break;
						case '-':
							$arrAcumuladores[$filaAcumulador["idAcumulador"]]-=$valCalculado;
						break;
						case '*':
							$arrAcumuladores[$filaAcumulador["idAcumulador"]]*=$valCalculado;
						break;
						case '/':
							if($valor!=0)
								$arrAcumuladores[$filaAcumulador["idAcumulador"]]/=$valCalculado;
							else
								$arrAcumuladores[$filaAcumulador["idAcumulador"]]=0;
						break;
						case '=':
							$arrAcumuladores[$filaAcumulador["idAcumulador"]]=$valCalculado;
						break;	
					}
				}
			}

			if(gettype($arrCalculosDef[$idCalculo])=='array')
				$obj->arrCalculosGlobales[$idCalculo]["nombreCalculo"]=$arrCalculosDef[$idCalculo]["etiquetaCalculo"].$etiquetaCalculoComp;
			else
				$obj->arrCalculosGlobales[$idCalculo]["nombreCalculo"]=$arrCalculosDef[$idCalculo].$etiquetaCalculoComp;
				
			if($etiquetaCalculoAux!="")
			{
				$obj->arrCalculosGlobales[$idCalculo]["nombreCalculo"]="[".($filaCalculo["cveCalculo"]==""?$filaCalculo["idCalculo"]:$filaCalculo["cveCalculo"])."] ".$etiquetaCalculoAux;
			}
				
				
			$obj->arrCalculosGlobales[$idCalculo]["valorCalculado"]=$valCalculado;
			$obj->arrCalculosGlobales[$idCalculo]["cveConcepto"]=$filaCalculo["cveCalculo"];
			$obj->arrCalculosGlobales[$idCalculo]["idCategoriaSAT"]=$filaCalculo["categoriaCalculo"];
			$obj->arrCalculosGlobales[$idCalculo]["idCalculoAsociado"]=$filaCalculo["idCalculoAsociado"];
			
			
			
			$obj->arrCalculosGlobales[$idCalculo]["idConsultaAsociada"]="";
			$obj->arrCalculosGlobales[$idCalculo]["cveConseptoAsociado"]="";
			
			
			if(($filaCalculo["idCalculoAsociado"]!="")&&($filaCalculo["idCalculoAsociado"]!="-1"))
			{
				$consulta="SELECT idConsulta,cveCalculo FROM 662_calculosNomina WHERE idCalculo=".$filaCalculo["idCalculoAsociado"];
				$fCalculoAsociado=$con->obtenerPrimeraFilaAsoc($consulta);
				$obj->arrCalculosGlobales[$idCalculo]["idConsultaAsociada"]=$idConsultaAsociada;
				$obj->arrCalculosGlobales[$idCalculo]["cveConseptoAsociado"]=$cveCalculoAsociado;
			}
			
			if($obj->arrCalculosGlobales[$idCalculo]["tipoCalculo"]==1) //Deduccion
			{
				$obj->totalDeducciones+=$valCalculado;
				$obj->arrCalculosGlobales[$idCalculo]["importeGravado"]=0;
				$obj->arrCalculosGlobales[$idCalculo]["importeExcento"]=$valCalculado;
			}
			else
			{
				if($obj->arrCalculosGlobales[$idCalculo]["tipoCalculo"]==2)
				{
					$obj->totalPercepciones+=$valCalculado;
					$obj->arrCalculosGlobales[$idCalculo]["importeGravado"]=$valCalculado;
					$obj->arrCalculosGlobales[$idCalculo]["importeExcento"]=0;
				}
				else
				{
					$obj->arrCalculosGlobales[$idCalculo]["importeGravado"]=0;
					$obj->arrCalculosGlobales[$idCalculo]["importeExcento"]=$valCalculado;
				}
			}
			
			if(($filaCalculo["idFuncionGravamen"]!="")&&($filaCalculo["idFuncionGravamen"]!="-1"))
			{
				if($valCalculado=="")
					$valCalculado=0;
				$cObjFuncion='{"importe":"'.$valCalculado.'","cveConcepto":"'.$filaCalculo["cveCalculo"].'","idConcepto":"'.$idCalculo.
								'","objDatosUsr":""}';
				$oFuncion=json_decode($cObjFuncion);
				$oFuncion->objDatosUsr=$obj;
				$cache=NULL;
				
				
				$arrGravamen=resolverExpresionCalculoPHP($filaCalculo["idFuncionGravamen"],$oFuncion,$cache);
		
				$obj->arrCalculosGlobales[$idCalculo]["importeGravado"]=$arrGravamen["importeGravado"];
				$obj->arrCalculosGlobales[$idCalculo]["importeExcento"]=$arrGravamen["importeExento"];
				
				
			}
			
			if($obj->arrCalculosGlobales[$idCalculo]["tipoCalculo"]==2)
				$obj->acumuladoBaseGravablePercepcion+=$obj->arrCalculosGlobales[$idCalculo]["importeGravado"];
			else
				if($obj->arrCalculosGlobales[$idCalculo]["tipoCalculo"]==1)
					$obj->acumuladoBaseGravableDeduccion+$obj->arrCalculosGlobales[$idCalculo]["importeGravado"];
			
			$referencia=&$obj->arrCalculosGlobales[$idCalculo];
			/*$referencia["distriCuentas"]=array();
			$arrCuentas=array();
			$consulta="select codCuentaAfectacion,codCuentaAfectacionSimple,porcentaje,tipoAfectacion,idEstructura,idBeneficiario,tipoBeneficiario,idTipoPresupuesto
						from 661_afectacionesCuentasDeducPercepciones where idDeduccionPercepcion=".$idCalculo;
			
			if($modoDebugger)
				echo $consulta;
			$resAfectCuentas=$con->obtenerFilas($consulta);
			
			$objCuenta=array();
			while($filaAfectCuentas=mysql_fetch_row($resAfectCuentas))
			{
				$objCuenta["codCuentaAfectacion"]=$filaAfectCuentas[0];
				$objCuenta["codCuentaAfectacionSimple"]=$filaAfectCuentas[1];
				$objCuenta["porcentaje"]=$filaAfectCuentas[2];
				$objCuenta["tipoAfectacion"]=$filaAfectCuentas[3];
				$objCuenta["idEstructura"]=$filaAfectCuentas[4];
				$objCuenta["idBeneficiario"]=$filaAfectCuentas[5];
				$objCuenta["tipoBeneficiario"]=$filaAfectCuentas[6];
				$objCuenta["valorAsignado"]=$valCalculado*($objCuenta["porcentaje"]/100);
				$consulta="select codigo from 508_tiposPresupuesto where idTipoPresupuesto=".$filaAfectCuentas[7];
				if($modoDebugger)
					echo $consulta;
				$codigoPre=$con->obtenerValor($consulta);				
				$objCuenta["tipoPresupuesto"]=$codigoPre;
				$objCuenta["idTipoPresupuesto"]=$filaAfectCuentas[7];
				if($filaAfectCuentas[5]!='')
				{
					if($filaAfectCuentas[5]=="0")
						$consulta="select cu.cuenta,cu.idBanco from 801_adscripcion a,823_cuentasUsuario cu where cu.idCuentaUsuario=a.idCuentaDeposito and a.idUsuario=".$obj->idUsuario;
					else
					{
						if($filaAfectCuentas[6]==1)
							$consulta="select txtCuenta,cmbbanco from _217_tablaDinamica where id__217_tablaDinamica=".$objCuenta["idBeneficiario"];
						else
							$consulta="select txtCuenta,cmbbanco from _216_tablaDinamica where id__216_tablaDinamica=".$objCuenta["idBeneficiario"];
						
					}
					if($modoDebugger)
						echo $consulta;
					$filaCuenta=$con->obtenerPrimeraFila($consulta);
					$objCuenta["cuentaBancaria"]=$filaCuenta[0];
					$objCuenta["idBanco"]=$filaCuenta[1];
				}
				else
					$objCuenta["cuentaBancaria"]="";
				
				
				array_push($arrCuentas,$objCuenta);
			}*/
			//$referencia["distriCuentas"]=$arrCuentas;
		}
	}
	
	$obj->sueldoNeto=$obj->totalPercepciones-$obj->totalDeducciones;
	switch($accionPrecision)
	  {
		  case 1:
			  $obj->sueldoNeto=truncarValor($obj->sueldoNeto,$precision);
		  break;
		  case 2:
			  $obj->sueldoNeto=str_replace(",","",number_format($obj->sueldoNeto,$precision));
		  break;
	  }

}

function obtenerFechaNominaAplicacion($idNomina)
{
	global $con;
	$consulta="SELECT quincenaAplicacion,ciclo,idPerfil FROM 672_nominasEjecutadas WHERE idNomina=".$idNomina;
	$fNomina=$con->obtenerPrimeraFilaAsoc($consulta);
	$consulta="SELECT idPeriodicidad FROM 662_perfilesNomina WHERE idPerfilesNomina=".$fNomina["idPerfil"];
	$fPerfilNomina=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$consulta="SELECT noOrdinal,nombreElemento,mes,diaInicio FROM _642_gElementosPeriodicidad WHERE idReferencia=".
				$fPerfilNomina["idPeriodicidad"]." and noOrdinal=".$fNomina["quincenaAplicacion"];
	$fPeriodicidad=$con->obtenerPrimeraFilaAsoc($consulta);
	
	
	$consulta="SELECT * FROM _642_gElementosPeriodicidad WHERE idReferencia=".$fPerfilNomina["idPeriodicidad"]." AND noOrdinal>".$fPeriodicidad["noOrdinal"]." ORDER BY noOrdinal";

	$fPeriodo=$con->obtenerPrimeraFilaAsoc($consulta);
	if(!$fPeriodo)
	{
		$consulta="SELECT * FROM _642_gElementosPeriodicidad WHERE idReferencia=".$fPerfilNomina["idPeriodicidad"]." ORDER BY noOrdinal";
		$fPeriodo=$con->obtenerPrimeraFilaAsoc($consulta);
		$fNomina["ciclo"]++;
	
	}
	
	$fechaConsiderar=$fNomina["ciclo"]."-".str_pad($fPeriodo["mes"],2,"0",STR_PAD_LEFT)."-".str_pad($fPeriodo["diaInicio"],2,"0",STR_PAD_LEFT);
	$fechaConsiderar=date("Y-m-d",strtotime("-1 days",strtotime($fechaConsiderar)));
	return $fechaConsiderar;
}

function escribirAsientoNomina($objUsuario,$idNomina)
{
	global $con;
	
	$objSerial=serialize($objUsuario);
	$x=0;
	$consulta[$x]="begin";
	$x++;
	$consulta[$x]="INSERT INTO 671_asientosCalculosNomina(cvePuesto,codDepartamento,tipoContratacion,idZona,idUsuario,
				totalDeducciones,totalPercepciones,sueldoNeto,objDetalle,idCiclo,quincenaAplicacion,idPerfil,idNomina,
				horasTrabajador,sueldoCompactado,tipoPago,situacion,institucion,pagado,responsablePago,fechaPago,
				idUnidadAgrupadora,identificador,descriptorIdentificador) values ";
	$consulta[$x].="('".$objUsuario->puesto."','".$objUsuario->departamento."',".$objUsuario->tipoContratacion.",".$objUsuario->idZona.",".$objUsuario->idUsuario.
				",".$objUsuario->totalDeducciones.",".$objUsuario->totalPercepciones.",".$objUsuario->sueldoNeto.",'".cv($objSerial)."',".$objUsuario->ciclo.
				",".$objUsuario->quincena.",".$objUsuario->idPerfil.",".$idNomina.
				",".$objUsuario->horasTrabajador.",".($objUsuario->sueldoBase/2).",".$objUsuario->tipoPago.",".$objUsuario->situacion.
				",'".$objUsuario->institucion."',0,NULL,NULL,".$objUsuario->idUnidadAgrupadora.",".$objUsuario->idIdentificadorComplementario.
				",'".cv($objUsuario->etiquetaIdentificadorComp)."')";
	$x++;	
	$consulta[$x]="set @idAsiento:=(select last_insert_id())";
	$x++;

	foreach($objUsuario->arrCalculosGlobales as $iC=>$resto)
	{

		$consulta[$x]="INSERT INTO 671_resultadosCalculosAsientoNomina(idAsientoNomina,idAlineacionCalculo,idConsulta,orden,
						tipoCalculo,nombreCalculo,valorCalculado,cveConcepto,idCategoriaSAT,idCalculoAsociado,idConsultaAsociada,
						cveConseptoAsociado,importeGravado,importeExcento)
						VALUES(@idAsiento,".$iC.",".$resto["idConsulta"].",".$resto["orden"].
						",".$resto["tipoCalculo"].",'".cv($resto["nombreCalculo"])."',".$resto["valorCalculado"].",'".cv($resto["cveConcepto"]).
						"',".($resto["idCategoriaSAT"]==""?0:$resto["idCategoriaSAT"]).",".($resto["idCalculoAsociado"]==""?0:$resto["idCalculoAsociado"]).
						",".($resto["idConsultaAsociada"]==""?0:$resto["idConsultaAsociada"]).",'".cv($resto["cveConseptoAsociado"]).
						"',".$resto["importeGravado"].",".$resto["importeExcento"].")";
		$x++;
		$consulta[$x]="set @idResultadoCalculo:=(select last_insert_id())";
		$x++;
		foreach($resto["arrCategorias"] as $cat)
		{
			$consulta[$x]="INSERT INTO 671_categoriasCalculosAientosNomina(idResultadoCalculo,idCategoria) VALUES(@idResultadoCalculo,".$cat.")";
			$x++;
		}
	}
	$consulta[$x]="commit";
	$x++;


	if($con->ejecutarBloque($consulta))
	{
		$query="select @idAsiento";
		$idAsiento=$con->obtenerValor($query);
		return $idAsiento;
	}
	
	return false;
	

}


function limpiarAsientoNomina($idNomina,$idUsuario)
{
	global $con;
	$x=0;
	$consulta[$x]="begin";
	$x++;
	$consulta[$x]="DELETE FROM 671_asientosCalculosNomina WHERE idUsuario=".$idUsuario." AND idNomina=".$idNomina;
	$x++;
	$consulta[$x]="commit";
	$x++;
	return $con->ejecutarBloque($consulta);
}



function obtenerSalarioMinimo($idZona,$fechaAplicacion)
{
	global $con;
	$valor=0;
	
	$consulta="SELECT importe FROM _650_tablaDinamica WHERE fechaAplicacion<='".$fechaAplicacion."' AND zonaEconomica='".$idZona."' ORDER BY fechaAplicacion DESC";
	$valor=$con->obtenerValor($consulta);
	
	return $valor;
}



?>