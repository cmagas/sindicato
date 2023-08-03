<?php 
include_once("latis/conexionBD.php"); 
include_once("latis/funcionesEnvioMensajes.php"); 
include_once("latis/class.smtp.php"); 
include_once("latis/fpdf/fpdf.php"); 

$modoDebug=false;

ini_set("memory_limit","6000M");
set_time_limit(999000);
$minutosDezplazamiento=0;
$arrColumnasSemana=array();
$arrHorasDia=array();

$arrConfiguracionHorario=array();

$consulta="SELECT * FROM _642_gridConfiguracionFuente ORDER BY minutosMax asc";
$res=$con->obtenerFilas($consulta);
while($fila=mysql_fetch_assoc($res))
{
	if(!isset($arrConfiguracionHorario[$fila["idReferencia"]]))
		$arrConfiguracionHorario[$fila["idReferencia"]]=array();
	$arrConfiguracionHorario[$fila["idReferencia"]][$fila["minutosMax"]*1]=$fila;
}



function generarProgramacionSemanalV2($idSemana,$formato="PDF",$descargarDocumento=true,$iMaster=-1,$ocultarClaveTransmision=0,$version=-1,$idFeed=-1,$informeRating=0)
{
	date_default_timezone_set('America/Bogota');
	global $con;
	global $arrMesLetra;
	global $arrDiasSemana;
	global $baseDir;
	global $arrColumnasSemana;
	global $minutosDezplazamiento;
	global $arrHorasDia;
	global $modoDebug;
	$versionChar="";
	$consulta="SELECT etiqueta,noSemana,fechaInicio,fechaFin FROM 1050_semanasAnio WHERE idSemana=".$idSemana;
	$fSemana=$con->obtenerPrimeraFila($consulta);

	$arrLibros=array();
	$consulta="SELECT id__593_tablaDinamica,clave,nombre FROM _593_tablaDinamica WHERE situacion=1";
	if($iMaster!=-1)
		$consulta.=" and id__593_tablaDinamica in(".$iMaster.")";
	$resMaster=$con->obtenerFilas($consulta);
	while($filaMaster=mysql_fetch_row($resMaster))
	{
		
		$consulta="SELECT id__593_gTransmision,feedTransmision FROM _593_gTransmision WHERE idReferencia=".$filaMaster[0];
		if($idFeed!=-1)
			$consulta.=" AND id__593_gTransmision IN(".$idFeed.")";

		$resFeed=$con->obtenerFilas($consulta);
		while($filaFeed=mysql_fetch_row($resFeed))
		{
			
			$idMaster=$filaMaster[0];
			$nFilaInicial=6;	
			$libro=new cExcel($baseDir."/modulosEspeciales_FOX/plantilla/plantillaHorario.xlsx",true,"Excel2007");			
			for($nFilaRegistro=6;$nFilaRegistro<=725;$nFilaRegistro++)
			{
				$libro->setAltoFila($nFilaRegistro,2.2);
			}
			
			$libro->cambiarTituloHoja(0,$filaMaster[1]."_".str_replace(" ","_",$filaFeed[1]));
			$arrFechasSemana=array();
			
			
			//$libro->setValor("A2",utf8_encode($fSemana[0]));
			//$libro->setValor("A3",utf8_encode($filaMaster[2]));
			
			$fechaInicio=strtotime($fSemana[2]);
			$fechaFin=strtotime($fSemana[3]);
			$columna="B";
			$columaIniComp="I";		
			
			$consulta="SELECT id__594_tablaDinamica,horaInicial FROM _594_tablaDinamica";
			$fConfiguracion=$con->obtenerPrimeraFila($consulta);
			
			$horaBase=strtotime("1984-05-10 00:00:00");
			$horaInicio=strtotime("1984-05-10 ".$fConfiguracion[1]);
			$minutosDezplazamiento=0;
			if($horaBase<>$horaInicio)
			{
			  $minutosDezplazamiento=obtenerDiferenciaMinutos(date("Y-m-d H:i:s",$horaBase),date("Y-m-d H:i:s",$horaInicio));
			}
			
			for($h=0;$h<24;$h++)
			{
				$arrHorasDia[$h]=strtotime("+".$minutosDezplazamiento." minutes",strtotime("+ ".$h." hours",$horaBase));
			}
			
			
			if($informeRating==0)
				$versionChar=dibujarEventosV2($minutosDezplazamiento,$idSemana,$idMaster,$libro,$arrFechasSemana,$ocultarClaveTransmision,$version,$filaFeed[0]);
			else
				$versionChar=dibujarEventosRating($minutosDezplazamiento,$idSemana,$idMaster,$libro,$arrFechasSemana,$ocultarClaveTransmision,$version,$filaFeed[0]);
			
			$nombreLibro=generarNombreArchivoTemporal();
			$arrLibros[$idMaster."_".$filaFeed[0]]=$baseDir."/archivosTemporales/".$nombreLibro.".xlsx";
	
			if(!$modoDebug)
				$libro->generarArchivoServidor("Excel2007",$arrLibros[$idMaster."_".$filaFeed[0]]);
			else
			{
				$libro->generarArchivoServidor("HTML",$arrLibros[$idMaster."_".$filaFeed[0]]);
				return;
			}
		}

	}
	
	
	
	$libro2;
	$numLibro=1;
	foreach($arrLibros as $ruta)
	{
		if($numLibro==1)
		{
			$libro=new cExcel($ruta,true,"Excel2007");
		}
		else
		{
			$libro2=new cExcel($ruta,true,"Excel2007");
			$libro->libroExcel->addExternalSheet($libro2->obtenerHojaActiva());
		}
		$numLibro++;
		
	}
	
	
	$consulta="SELECT id__593_tablaDinamica,clave,nombre FROM _593_tablaDinamica WHERE id__593_tablaDinamica in(".$iMaster.")";
	$fMaster=$con->obtenerPrimeraFila($consulta);
	$consulta="SELECT leyenda FROM _593_gTransmision WHERE id__593_gTransmision IN(".$idFeed.")";
	$nombreFeed=$con->obtenerValor($consulta);
	$tituloInforme=$fMaster[2]." ".$nombreFeed." Semana ".$fSemana[1];
	$libro->cambiarHojaActiva(0);
	if($informeRating==1)
	{
		$tituloInforme.="Cod".$versionChar."CAMBIOS";

	}
	else
	{
		if($ocultarClaveTransmision==0)
		{
			$tituloInforme.="Cod".$versionChar;
		}
		else
		{
			$tituloInforme.=$versionChar;
		}
	}
	
	date_default_timezone_set('America/Mexico_City');
	//$nombreLibro=$tituloInforme."_".$fSemana[1]."(".date("d-m-Y",strtotime($fSemana[2]))."_".date("d-m-Y",strtotime($fSemana[3])).")".($version==-1?"":("_version_".$version)).".xlsx";
	$nombreLibro=($tituloInforme).".xlsx";
	if($descargarDocumento)
	{
		
		$libro->generarArchivo($formato,$nombreLibro);
		foreach($arrLibros as $ruta)
		{
			unlink($ruta);
		}
	}
	else
	{
		$nLibro=generarNombreArchivoTemporal();
		$objDatosDocumento=array();
		if($formato=="PDF")
		{
			$objDatosDocumento[0]=$baseDir."/archivosTemporales/".$nLibro.".pdf";
			$objDatosDocumento[1]=str_replace(".xlsx",".pdf",$nombreLibro);
		}
		else
		{
			$objDatosDocumento[0]=$baseDir."/archivosTemporales/".$nLibro.".xlsx";
			$objDatosDocumento[1]=$nombreLibro;
		}
		$libro->generarArchivoServidor($formato,$objDatosDocumento[0]);
		
		$objDatosDocumento[2]=filesize($objDatosDocumento[0]);
		
		foreach($arrLibros as $ruta)
		{
			unlink($ruta);
		}
		return $objDatosDocumento;
	}
	
}

function obtenerRangoHora($nHora)
{
	$nFilaInicial=6;
	$inicio=$nFilaInicial+($nHora*30);
	$final=$inicio+29;
	$arrRangos=array();
	$arrRangos[0]=$inicio;
	$arrRangos[1]=$final;
	return $arrRangos;
	
}

function dibujarEventosV2($minutosDezplazamiento,$idSemana,$iMaster,$libro,$arrFechasSemana,$ocultarClaveTransmision=0,$version=-1,$idFeed)
{
	global $con;
	global $baseDir;
	global $arrColumnasSemana;
	global $minutosDezplazamiento;
	global $arrDiasSemana;
	global $arrMesLetra;
	global $arrHorasDia;
	
	
	$consulta="SELECT leyenda FROM _593_gTransmision WHERE id__593_gTransmision=".$idFeed;
	$lblFeed=$con->obtenerValor($consulta);
	$letraTitulo=12;
	$letraHorario=12;	
	$letraEvento=11;
	$letraEvento2=9.5;
	if($ocultarClaveTransmision==1)
		$letraEvento=11;
	$margenDerecho=3.6;
	$constanteMargen=0.3937;
	
	$arrOcupacionColumnas=array();
	$consulta="SELECT id__593_tablaDinamica,clave,nombre FROM _593_tablaDinamica WHERE id__593_tablaDinamica=".$iMaster;
	$filaMaster=$con->obtenerPrimeraFila($consulta);
	$consulta="SELECT COUNT(*) FROM _593_gTransmision WHERE id__593_gTransmision=".$idFeed;
	$totalCanales=$con->obtenerValor($consulta);

	$consulta="SELECT etiqueta,noSemana,fechaInicio,fechaFin FROM 1050_semanasAnio WHERE idSemana=".$idSemana;
	$fSemana=$con->obtenerPrimeraFila($consulta);
	$arrProgramacionFecha=array();
	$fIAux=strtotime($fSemana[2]);
	$fFAux=strtotime($fSemana[3]);
	
	while($fIAux<=$fFAux)
	{
		$arrProgramacionFecha[date("Y-m-d",$fIAux)]=array();
		$fIAux=strtotime("+1 days",$fIAux);
	}
	
	$consulta="SELECT fechaAplicacion,tipoHorarioAplica FROM _643_tablaDinamica WHERE fechaAplicacion>='".$fSemana[2]."' AND fechaAplicacion<='".$fSemana[3]."'";
	$fHorarioVerano=$con->obtenerPrimeraFila($consulta);
	
	$fechaAplicacionHorario=$fHorarioVerano[0];
		
	$aplicaCambioHorario=$fechaAplicacionHorario?true:false;

	$esHorarioVerano=$aplicaCambioHorario && ($fHorarioVerano[1]==1);
	$esHorarioInvierno=$aplicaCambioHorario && ($fHorarioVerano[1]==2);
	$horaReferenciaCambioHorario="";
	$horaFinalReferenciaCambio="";
	$horaLimiteInicioDomingo="";
	$horaLimiteInicioNormal="";
	$arrEventos="";	
	$arrColores=array();
	$consulta="SELECT id__594_tablaDinamica,horaInicial FROM _594_tablaDinamica";
	$fConfiguracion=$con->obtenerPrimeraFila($consulta);
	
	$start=$fSemana[2]." ".$fConfiguracion[1];
	$end=date("Y-m-d H:i:s",strtotime("+".$minutosDezplazamiento." minutes",strtotime($fSemana[3]." 23:59:59")));
	
	$consulta="SELECT * FROM _594_gConfiguracionColores WHERE idReferencia=".$fConfiguracion[0];
	$resColores=$con->obtenerFilas($consulta);
	while($fColor=mysql_fetch_row($resColores))
	{
		$arrColores[$fColor[2]]="#".$fColor[4];
	}
	
	$listaCanales="";
	$arrBloquesProgramacion=array();
	$totalCanalesMaster=0;
	$canalBaseMaster=-1;
	$consulta="SELECT id__593_gTransmision,confBase FROM _593_gTransmision WHERE id__593_gTransmision=".$idFeed." order by confBase desc,feedTransmision";
	$res=$con->obtenerFilas($consulta);
	while($fCanal=mysql_fetch_row($res))
	{
		$arrBloquesProgramacion[$fCanal[0]]=array();
		$totalCanalesMaster++;
		if($listaCanales=="")
			$listaCanales=$fCanal[0];
		else
			$listaCanales.=",".$fCanal[0];
		if($fCanal[1]==1)
			$canalBaseMaster=$fCanal[0];
	}
	
	$arrProgramasMulticanal=array();
	$arrRejillaGrid=array();
	
	
	$columnaMapeoInicial="A";
	$totalColumnasHorario=0;
	$consulta="SELECT id__593_gUsosHorarios,leyenda,horasDiferencia FROM _593_gUsosHorarios WHERE idReferencia=".$iMaster." AND posicionLeyenda=1
				union
	select 0,'MEX',0";
	$res=$con->obtenerFilas($consulta);
	
	while($fila=mysql_fetch_row($res))
	{
		$libro->setValor($columnaMapeoInicial."5",$fila[1]);
		$libro->setTamano($columnaMapeoInicial."5",$letraTitulo);
		
		for($h=0;$h<24;$h++)
		{
			$rango=obtenerRangoHora($h);
			$rangoAux=$rango[0]+15;
			
			if($fila[2]>=0)
				$hora=strtotime("+".$fila[2]." hours",$arrHorasDia[$h]);
			else
				$hora=strtotime("".$fila[2]." hours",$arrHorasDia[$h]);
			$libro->setValor($columnaMapeoInicial.$rango[0],date("H:i",$hora));
			$libro->setTamano($columnaMapeoInicial.$rango[0],$letraHorario);

			$libro->setValor($columnaMapeoInicial.$rangoAux,date("H:i",strtotime("+30 minutes",$hora)));
			$libro->setTamano($columnaMapeoInicial.$rangoAux,$letraHorario);
			if($fila[1]=='MEX')
			{
				$libro->setNegritas($columnaMapeoInicial."6:".$columnaMapeoInicial."725");
			}
			
		}
		
		$columnaMapeoInicial=obtenerSiguienteColumna($columnaMapeoInicial);
		$totalColumnasHorario++;
		
	}
	$libro->hojaActiva->removeColumn($columnaMapeoInicial,(9-$con->filasAfectadas));
	
	
	$columnaInicial=$columnaMapeoInicial;
	$arrProgramasMulticanalAux=array();
	foreach($arrProgramacionFecha as $fecha=>$resto)
	{
		if(!isset($arrProgramasMulticanal[$fecha]))
		{
			
			if(!isset($arrProgramasMulticanalAux[$fecha."_".$listaCanales]))
				$arrProgramasMulticanalAux[$fecha."_".$listaCanales]=array();
			$arrProgramasMulticanalAux[$fecha."_".$listaCanales]["columna"]=$columnaInicial;
			$arrProgramasMulticanalAux[$fecha."_".$listaCanales]["canales"]=$listaCanales;
				
			$columnaInicial=obtenerSiguienteColumna($columnaInicial);
		}
		else
		{
			$oMulticanal=$arrProgramasMulticanal[$fecha];
			$arrConjuntoCanal=array();
			for($x=0;$x<sizeof($oMulticanal);$x++)
			{
				$oBase=explode(",",$oMulticanal[$x][1]);
				$existeConjunto=false;
				foreach($oBase as $canalBase)
				{
					for($x2=($x+1);$x2<sizeof($oMulticanal);$x2++)
					{
						$oReferencia=explode(",",$oMulticanal[$x2][1]);
						foreach($oReferencia as $canalReferencia)
						{
							if($canalBase==$canalReferencia)
							{
								$existeConjunto=true;
								break;
							}
						}
						
						if($existeConjunto)
						{
							break;
						}
						
					}
					if($existeConjunto)
					{
						break;
					}
				}
				
				if($existeConjunto)
				{
					foreach($oBase as $canalBase)
					{
						$arrConjuntoCanal[$canalBase]=1;
					}
				}
				else
				{
					$arrConjuntoCanal[$oMulticanal[$x][1]]=1;
				}
				
			}
			
			foreach($arrConjuntoCanal as $canales=>$resto)
			{
				if(!isset($arrProgramasMulticanalAux[$fecha."_".$canales]))
					$arrProgramasMulticanalAux[$fecha."_".$canales]=array();
				$arrProgramasMulticanalAux[$fecha."_".$canales]["columna"]=$columnaInicial;
				$arrProgramasMulticanalAux[$fecha."_".$canales]["canales"]=$canales;
				$columnaInicial=obtenerSiguienteColumna($columnaInicial);
			}
			
		}
		
	}
	
	//$diferenciaColumnasHorario=($totalAnchoColumnasHorario-(8.33*$totalColumnasHorario))/7;
	
	//$compAjuste=4*$totalColumnasHorario
	
	$columnaInicialEncabezado=$columnaMapeoInicial;

	$libro->hojaActiva->insertNewColumnBefore($columnaMapeoInicial,sizeof($arrProgramasMulticanalAux));
	$columna="";
	
	$columnaDomingo="";
	$columnaJueves="";
	foreach($arrProgramasMulticanalAux as $fecha=>$resto)
	{
		$arrFecha=explode("_",$fecha);
		$dteFecha=strtotime($arrFecha[0]);
		
		if(date("w",$dteFecha)==4)
		{
			
			$columnaJueves=$resto["columna"];
		}
		else
			if(date("w",$dteFecha)==0)
			{
				$arrFechaAux=explode("_",$fecha);
				$columnaDomingo=$resto["columna"];
				$horaReferenciaCambioHorario=strtotime($arrFechaAux[0]." 02:00:00");
				$horaFinalReferenciaCambio=strtotime("+1 hours",$horaReferenciaCambioHorario);
				$horaLimiteInicioDomingo=strtotime("+1 hours",strtotime(date("Y-m-d",$horaReferenciaCambioHorario)." ".$fConfiguracion[1]));
				$horaLimiteInicioNormal=strtotime("-1 hours",$horaLimiteInicioDomingo);
			}
		
		//$leyenda=utf8_encode($arrDiasSemana[date("w",$dteFecha)])."\r";
		//$leyenda.=date("d",$dteFecha)." de ".$arrMesLetra[(date("m",$dteFecha)*1)-1]." de ".date("Y",$dteFecha);
		
		$leyenda=utf8_encode(substr($arrDiasSemana[date("w",$dteFecha)],0,3))." ".date("d/m/Y",$dteFecha) ;
		$consulta="SELECT feedTransmision FROM _593_gTransmision WHERE id__593_gTransmision in(".$resto["canales"].") order by feedTransmision";
		$listaCanales=$con->obtenerListaValores($consulta);
		//$leyenda.="\r(".$listaCanales.")\r";
		
		$tCanales=explode(",",$resto["canales"]);
		$tCanales=sizeof($tCanales);
		if($totalCanales!=$tCanales)
		{
			$consulta="SELECT distinct leyenda FROM _593_gTransmision WHERE id__593_gTransmision in(".$resto["canales"].") order by feedTransmision";
			$listaCanales=$con->obtenerListaValores($consulta);
			$leyendaCanal=str_replace(",","\r",$listaCanales);
			$libro->setValor($resto["columna"]."4",$leyendaCanal);
			
			
			
			$arrCanalesMaster=explode(",",$resto["canales"]);
			$encontradoBase=false;
			foreach($arrCanalesMaster as $iCanal)
			{
				if($iCanal==$canalBaseMaster)
				{
					$encontradoBase=true;
					break;
				}
			}
			if(!$encontradoBase)
			{
				
				$libro->setColorFondo($resto["columna"]."6:".$resto["columna"]."725","9BC1E6");
				
			}
			
			
			
		}
		$libro->setValor($resto["columna"]."5",$leyenda);
		$libro->setTamano($resto["columna"]."5",$letraTitulo);
		$libro->setAnchoColumna($resto["columna"],30);
		$libro->unsetNegritas($resto["columna"]."6:".$resto["columna"]."725");
		$columna=$resto["columna"];
	}
	$columna=obtenerSiguienteColumna($columna);
	$libro->hojaActiva->removeColumn($columna,1);


	$consulta="select 0,'MEX',0
			union
			SELECT id__593_gUsosHorarios,leyenda,horasDiferencia FROM _593_gUsosHorarios WHERE idReferencia=".$iMaster." AND posicionLeyenda=2";
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		$libro->setValor($columna."5",$fila[1]);
		$libro->setTamano($columna."5",$letraTitulo);
		
		for($h=0;$h<24;$h++)
		{
			$rango=obtenerRangoHora($h);
			$rangoAux=$rango[0]+15;
			
			if($fila[2]>=0)
				$hora=strtotime("+".$fila[2]." hours",$arrHorasDia[$h]);
			else
				$hora=strtotime("".$fila[2]." hours",$arrHorasDia[$h]);
			$libro->setValor($columna.$rango[0],date("H:i",$hora));
			$libro->setTamano($columna.$rango[0],$letraHorario);
			$libro->setValor($columna.$rangoAux,date("H:i",strtotime("+30 minutes",$hora)));
			$libro->setTamano($columna.$rangoAux,$letraHorario);
			if($fila[1]=='MEX')
			{
				$libro->setNegritas($columna."6:".$columna."725");
			}
			
		}
		$columna=obtenerSiguienteColumna($columna);
		$totalColumnasHorario++;
		
	}
	
	
	$columnaFinal=obtenerAnteriorColumna($columna);
	
	$consulta="SELECT versionLiberacion FROM 5001_situacionProgramacionSemanal WHERE idSemana=".$idSemana." AND idMaster=".$iMaster;
	$versionLiberacion=$con->obtenerValor($consulta);
	
	$versionPrint=$version;
	if($versionPrint==-1)
	{
		$consulta="SELECT VERSION FROM 5001_situacionProgramacionSemanal WHERE idSemana=".$idSemana." AND idMaster=".$iMaster;
		$versionPrint=$con->obtenerValor($consulta);
	}
	$lblVersion="";
	$chrVersion="";
	if($versionLiberacion=="")
	{
		$chrVersion=": V. ".$versionPrint;
		$lblVersion=$versionPrint;
	}
	else
	{
		$chrVersion=chr(65+($versionPrint-$versionLiberacion));
		$lblVersion=$chrVersion;
	}
	
	
	$fIAux=strtotime($fSemana[2]);
	$fFAux=strtotime($fSemana[3]);
	
	$libro->setHAlineacion("A1:".$columnaFinal."4","C");
	
	$libro->setValor("B727","CWeek No.: ".$fSemana[1].$chrVersion);
	
	$columnaDate=$libro->obtenerDesplazamientoColumna($columnaFinal,-3+($aplicaCambioHorario?1:0));
	$libro->setValor($columnaDate."727","Date Range: ".(date("d/m/Y",$fIAux)." - ".date("d/m/Y",$fFAux)));
	$libro->setNegritas("B727:".$columnaFinal."727");
	$libro->setTamano("B727:".$columnaFinal."727",$letraTitulo);
	$libro->setValor("A3","Time Zone: México");
	
	
	$libro->setNegritas("A3:".$columnaFinal."3");
	$libro->setTamano("A3:".$columnaFinal."4",$letraTitulo);
	$libro->setHAlineacion("A1:".$columnaFinal."4","I");	
	$columnaDate=$libro->obtenerDesplazamientoColumna($columnaFinal,-3+($aplicaCambioHorario?1:0));
	
	
	
	$libro->setValor($columnaDate."3","Date Range: ".(date("d/m/Y",$fIAux)." - ".date("d/m/Y",$fFAux)));
	$objImg=$libro->crearObjetoImagen($baseDir."/modulosEspeciales_FOX/plantilla/images/".$filaMaster[1].".png");
	$objImg->setHeight(100);
	$objImg->setResizeProportional(true);
	
	$columnaLogo=$columnaJueves;
	

	$noColumna=$libro->columnaToNumero($columnaFinal);
	$totalColumnas=ceil($noColumna/2);
	$libro->setValor($columnaLogo."2",$lblFeed);
	$libro->setHAlineacion($columnaLogo."2:".$columnaLogo."2","C");
	$libro->insertarImagenHoja(0,$objImg,$columnaLogo."1");
	
	
	$libro->setValor($columnaLogo."3","WEEKLY PROGRAM SCHEDULE  - ".$filaMaster[1]);
	//$libro->insertarImagenHoja(0,$objImg2,$columnaLogo."728");
	$libro->obtenerHojaActiva()->getPageSetup()->setPrintArea('A1:'.$columnaFinal.'727');
	$libro->obtenerHojaActiva()->getPageMargins()->setLeft(0.3);	
	$libro->obtenerHojaActiva()->getPageMargins()->setRight(0.3);
	$libro->obtenerHojaActiva()->getPageMargins()->setTop(0.3);
	$libro->obtenerHojaActiva()->getPageMargins()->setBottom(0.3);
	
	//$libro->obtenerHojaActiva()->getPageMargins()->setLeft(($margenDerecho-(($totalColumnasHorario*1.2)/2))*$constanteMargen);	
	//$libro->obtenerHojaActiva()->getPageMargins()->setRight(($margenDerecho-(($totalColumnasHorario*1.2)/2))*$constanteMargen);	
	
	$consulta="SELECT VERSION FROM 5001_situacionProgramacionSemanal WHERE idSemana=".$idSemana." AND idMaster=".$iMaster;
	$versionActual=$con->obtenerValor($consulta);
	if($version!=-1)
		$versionActual=$version;
	
	$versionComparacion=$versionActual-1;
	if($versionComparacion<1)
		$versionComparacion=1;
	
	
	
	if($versionLiberacion==$versionActual)
	{
		$versionComparacion=$versionActual;
	}

	foreach($arrProgramasMulticanalAux as $fechaDia=>$resto)
	{
		
		$arrFechaCanal=explode("_",$fechaDia);
		
		$fechaDia=$arrFechaCanal[0];
		
		$startDia=$fechaDia." ".$fConfiguracion[1];
		
		
		
		$rangoInicioDia=strtotime($startDia);
		$rangoFinDia=strtotime("+23 hours 59 minutes",$rangoInicioDia);
		
		
		
		$aplicaAjusteHorario=$horaReferenciaCambioHorario>=$rangoInicioDia && $horaReferenciaCambioHorario<=$rangoFinDia;
		$startDiaDte=strtotime(-$minutosDezplazamiento." minutes",strtotime($startDia));
		$endDia=date("Y-m-d H:i:s",strtotime("+".$minutosDezplazamiento." minutes",strtotime($fechaDia." 23:59:59")));
		
		
		
		if(($aplicaAjusteHorario)&&($esHorarioVerano))
		{

			$endDia=date("Y-m-d H:i:s",strtotime("+".($minutosDezplazamiento+60)." minutes",strtotime($fechaDia." 23:59:59")));
		}
		
		
		$consulta="SELECT distinct p.* FROM 5000_programacionEventos p,5000_programacionEventosFeedTransmition c WHERE 
				((horaInicio>='".$startDia."' AND  horaInicio<='".$endDia."') 
							OR (horaFin>='".$startDia."' AND horaFin<'".$endDia."'))	
					 
					and idMaster=".$iMaster." and c.idProgramacion=p.idRegistro and c.idFeedTransmition in(".$resto["canales"].
					")  order by horaInicio";
		
		if($version!=-1)
		{
			$consulta="SELECT distinct p.* FROM 5001_programacionEventosRespaldoVersion p,5001_programacionEventosFeedTransmitionRespaldoVersion c WHERE 
					((horaInicio>='".$startDia."' AND  horaInicio<='".$endDia."') 
							OR (horaFin>='".$startDia."' AND horaFin<'".$endDia."')	)
					and idMaster=".$iMaster." and p.version=".$version." and c.idProgramacion=p.idRegistroEvento and c.version=".$version.
					" and c.idFeedTransmition in(".$resto["canales"].") order by horaInicio";

		}
		
		
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_assoc($res))
		{
			$filaV=NULL;
			if($versionComparacion==$versionActual)
			{
				$filaV=$fila;
			}
			else
			{
				
				$consulta="SELECT distinct p.* FROM 5001_programacionEventosRespaldoVersion p,5001_programacionEventosFeedTransmitionRespaldoVersion c WHERE 
						p.idRegistroEvento=".$fila["idRegistro"]." AND p.version=".$versionComparacion." and c.idProgramacion=p.idRegistroEvento and c.version=".$versionComparacion.
					" and c.idFeedTransmition in(".$resto["canales"].")  order by horaInicio";

				$filaV=$con->obtenerPrimeraFilaAsoc($consulta);
			}
			
			$horaInicioPrograma=strtotime($fila["horaInicio"]);
			$horaFinPrograma=strtotime($fila["horaFin"]);
			
			
			
			if(($esHorarioVerano)&&($aplicaAjusteHorario))
			{
				
				if(($horaInicioPrograma>=$horaReferenciaCambioHorario)&&($horaInicioPrograma<$horaLimiteInicioDomingo))
				{
					if($horaInicioPrograma<$horaFinalReferenciaCambio)
					{
						$horaInicioPrograma=$horaFinalReferenciaCambio;
					}
					else
					{
						$horaInicioPrograma=strtotime("-1 hours",$horaInicioPrograma);	
					}
					
				}
				
				if(($horaFinPrograma>$horaReferenciaCambioHorario)&&($horaFinPrograma<=$horaLimiteInicioDomingo))
				{
					
					if($horaFinPrograma<$horaFinalReferenciaCambio)
					{
						$horaFinPrograma=$horaReferenciaCambioHorario;
					}
					else
						$horaFinPrograma=strtotime("-1 hours",$horaFinPrograma);	
				}
				
				
				
				
				if($horaInicioPrograma>=$horaFinPrograma)
				{
					continue;
				}
				
				
			}
			
			$lblNombrePrograma="";
			$fechaInicio=strtotime("-".$minutosDezplazamiento." minutes",$horaInicioPrograma);
			$fechaFin=strtotime("-".$minutosDezplazamiento." minutes",$horaFinPrograma);		
			
			
			if($fechaFin==strtotime(date("Y-m-d",$fechaFin)." 00:00"))
			{
				$fechaFin=strtotime("-1 minute",$fechaFin);
			}
			
			
			if($fechaFin<$startDiaDte)
			{
				continue;
			}
			
			
			
			$consulta="SELECT abreviatura FROM _586_tablaDinamica WHERE id__586_tablaDinamica=".$fila["tipoEmision"];
			$tipoEmision=$con->obtenerValor($consulta);
			$objRichText = new PHPExcel_RichText();
			$objRichText->createText("");
			
			$fechaColumna=date("Y-m-d",$fechaInicio);
			
			$columnaDibujo=$resto["columna"];
			$columnaDibujoFinal=$columnaDibujo;
			
			$arrRango=obtenerRangoHora(date("H",$fechaInicio));
			$rango1=$arrRango[0];
			$rango1+=floor(date("i",$fechaInicio)*0.5);
			$arrRango=obtenerRangoHora(date("H",$fechaFin));
			$rango2=$arrRango[0]-1;
			$rango2+=floor(date("i",$fechaFin)*0.5);
			
			
			$lblNombreProgramaBase="";
			$lblNombreProgramaComparacion="";
			
			$consulta="SELECT nombrePrograma,clavePrograma,proy.tipoTransmision,pr.colorIdentificacion FROM _589_tablaDinamica pr, _588_tablaDinamica proy 
					WHERE id__589_tablaDinamica=".$fila["idPrograma"]." and proy.id__588_tablaDinamica=pr.proyectos";
			$fPrograma=$con->obtenerPrimeraFila($consulta);
			$lblNombrePrograma=$fPrograma[0];

			if($filaV)
			{
				$consulta="SELECT nombrePrograma,clavePrograma,proy.tipoTransmision,pr.colorIdentificacion FROM _589_tablaDinamica pr, _588_tablaDinamica proy 
					WHERE id__589_tablaDinamica=".$filaV["idPrograma"]." and proy.id__588_tablaDinamica=pr.proyectos";
				$fProgramaV=$con->obtenerPrimeraFila($consulta);
				$lblNombreProgramaComparacion=$fProgramaV[0];
				
				if($filaV["detalleEvento"]!="")
				{
					$lblNombreProgramaComparacion.="\n".str_replace("<br />","\n",$filaV["detalleEvento"]);
				}
				
				if($filaV["porConfirmar"]==1)
				{
					$lblNombreProgramaComparacion.="\n--Evento por confirmar--";
				}
				
			}


			if($fila["detalleEvento"]!="")
			{
				$lblNombrePrograma.="\n".str_replace("<br />","\n",$fila["detalleEvento"]);
			}
			
			if($fila["porConfirmar"]==1)
			{
				$lblNombrePrograma.="\n--Evento por confirmar--";
			}
			$tipoEvento=0;
			
			if($fila["porConfirmar"]==1)
			{
				$tipoEvento=3;
			}
			else
				if($fila["cveTransmision"]=="")
				{
					$tipoEvento=2;
				}
				else
					$tipoEvento=1;
					
					
			$mostrarTipoEmision=true;		
			
			if($fila["tipoEmision"]==9)
				$mostrarTipoEmision=false;
					
			$lblNombreProgramaBase=$lblNombrePrograma;	
			
			
			$diferenciaTiempo=obtenerDiferenciaMinutos($fila["horaInicio"],$fila["horaFin"]);
			$fConfiguracionTam=obtenerConfiguracionInformeAgenda($diferenciaTiempo,$ocultarClaveTransmision==0?1:2);
			
		
			$lblNombreProgramaBase.=" (".$tipoEmision.") [".$fila["cveTransmision"]."] ".$fila["horaInicio"]."-".$fila["horaFin"];
			if($filaV)
			{
				$consulta="SELECT abreviatura FROM _586_tablaDinamica WHERE id__586_tablaDinamica=".$filaV["tipoEmision"];
				$tipoEmisionV=$con->obtenerValor($consulta);
				$lblNombreProgramaComparacion.=" (".$tipoEmisionV.") [".$filaV["cveTransmision"]."] ".$filaV["horaInicio"]."-".$filaV["horaFin"];
			
			}
			
			if($ocultarClaveTransmision==0)	
			{
				
				
				
				if($mostrarTipoEmision)
				{
					$objAux=$objRichText->createTextRun($lblNombrePrograma."\n");
					$objAux->getFont()->setBold($fConfiguracionTam["programaNegritas"]==1?true:false);
					$objAux->getFont()->setSize($fConfiguracionTam["tamPrograma"]);
					
					$objAux=$objRichText->createTextRun("(");
					$objAux->getFont()->setBold($fConfiguracionTam["tipoTransmisionNegritas"]==1?true:false);
					$objAux->getFont()->setSize($fConfiguracionTam["tamTipoTransmision"]);
					
					$objAux=$objRichText->createTextRun($tipoEmision);
					$objAux->getFont()->setBold($fConfiguracionTam["tipoTransmisionNegritas"]==1?true:false);
					$objAux->getFont()->setSize($fConfiguracionTam["tamTipoTransmision"]);
					
					$objAux=$objRichText->createTextRun(")");
					$objAux->getFont()->setBold($fConfiguracionTam["tipoTransmisionNegritas"]==1?true:false);
					$objAux->getFont()->setSize($fConfiguracionTam["tamTipoTransmision"]);
					
					$objAux=$objRichText->createTextRun(" [");
					$objAux->getFont()->setBold($fConfiguracionTam["claveTrasmisionNegritas"]==1?true:false);
					$objAux->getFont()->setSize($fConfiguracionTam["tamTipoTransmision"]);
					if(trim($fila["cveTransmision"])!="")
					{
						$objAux=$objRichText->createTextRun($fila["cveTransmision"]);
						$objAux->getFont()->setBold($fConfiguracionTam["claveTrasmisionNegritas"]==1?true:false);
						$objAux->getFont()->setSize($fConfiguracionTam["tamClaveTransmision"]);
					}
					$objAux=$objRichText->createTextRun("]");
					$objAux->getFont()->setBold($fConfiguracionTam["claveTrasmisionNegritas"]==1?true:false);
					$objAux->getFont()->setSize($fConfiguracionTam["tamTipoTransmision"]);
					

					
				}
				else
				{
					$objAux=$objRichText->createTextRun($lblNombrePrograma);
					$objAux->getFont()->setBold($fConfiguracionTam["programaNegritas"]==1?true:false);
					$objAux->getFont()->setSize($fConfiguracionTam["tamPrograma"]);
					
				}
				
				
				
			}
			else
			{
				
				if($mostrarTipoEmision)
				{
					$objAux=$objRichText->createTextRun($lblNombrePrograma."\n");
					$objAux->getFont()->setBold($fConfiguracionTam["programaNegritas"]==1?true:false);
					$objAux->getFont()->setSize($fConfiguracionTam["tamPrograma"]);
					
					$objAux=$objRichText->createTextRun("(");
					$objAux->getFont()->setBold($fConfiguracionTam["tipoTransmisionNegritas"]==1?true:false);
					$objAux->getFont()->setSize($fConfiguracionTam["tamTipoTransmision"]);
					
					$objAux=$objRichText->createTextRun($tipoEmision);
					$objAux->getFont()->setBold($fConfiguracionTam["tipoTransmisionNegritas"]==1?true:false);
					$objAux->getFont()->setSize($fConfiguracionTam["tamTipoTransmision"]);
					
					$objAux=$objRichText->createTextRun(")");
					$objAux->getFont()->setBold($fConfiguracionTam["tipoTransmisionNegritas"]==1?true:false);
					$objAux->getFont()->setSize($fConfiguracionTam["tamTipoTransmision"]);
					
				}
				else
				{
					$objAux=$objRichText->createTextRun($lblNombrePrograma);
					$objAux->getFont()->setBold($fConfiguracionTam["programaNegritas"]==1?true:false);
					$objAux->getFont()->setSize($fConfiguracionTam["tamPrograma"]);
				}
			}
			
			
			if(date("Y-m-d",$fechaInicio)==date("Y-m-d",$fechaFin))
			{

				$libro->combinarCelda($columnaDibujo.$rango1,$columnaDibujo.$rango2);
				$libro->setValor($columnaDibujo.$rango1,$objRichText);
				if($lblNombreProgramaBase!=$lblNombreProgramaComparacion)
				{
					$libro->setColorFondo($columnaDibujo.$rango1,"ECDD08");
				}
				$libro->setBorde($columnaDibujo.$rango1,"DE","000000");//str_replace("#","",$arrColores[$tipoEvento])
				$libro->setHAlineacion($columnaDibujo.$rango1.":".$columnaDibujo.$rango1,"C");
				$libro->setVAlineacion($columnaDibujo.$rango1.":".$columnaDibujo.$rango1,"S");
				$libro->hojaActiva->getStyle($columnaDibujo.$rango1)->getAlignment()->setWrapText(true);
			}
			else
			{
				

				$rango1="";
				$rango2="";
				
				if($startDiaDte<$fechaInicio)
				{
					$arrRango=obtenerRangoHora(date("H",$fechaInicio));
					$rango1=$arrRango[0];
					$rango1+=floor(date("i",$fechaInicio)*0.5);
					$fechaFinAux=strtotime(date("Y-m-d",$fechaInicio)." 23:59");
		
					$arrRango=obtenerRangoHora(date("H",$fechaFinAux));
					
					$rango2=$arrRango[0]-1;
					$rango2+=floor(date("i",$fechaFinAux)*0.5)+1;
				}
				else
				{
					
					$rango1=6;
					
					$arrRango=obtenerRangoHora(date("H",$fechaFin));
					
					$rango2=$arrRango[0]-1;
					$rango2+=floor(date("i",$fechaFin)*0.5);

					
				}
				
				
				$libro->combinarCelda($columnaDibujo.$rango1,$columnaDibujo.$rango2);
				$libro->setValor($columnaDibujo.$rango1,$objRichText);
				if($lblNombreProgramaBase!=$lblNombreProgramaComparacion)
					$libro->setColorFondo($columnaDibujo.$rango1,"ECDD08");
				$libro->setBorde($columnaDibujo.$rango1,"DE","000000");
				$libro->setHAlineacion($columnaDibujo.$rango1.":".$columnaDibujo.$rango1,"C");
				$libro->setVAlineacion($columnaDibujo.$rango1.":".$columnaDibujo.$rango1,"S");
				$libro->hojaActiva->getStyle($columnaDibujo.$rango1)->getAlignment()->setWrapText(true);
				
				
				
				
				
				
			}
			
		}
	}
	
	
	
	///----
	if($aplicaCambioHorario)
	{
		$libro->hojaActiva->insertNewColumnBefore($columnaDomingo,1);
		$columnaAnteriorDomingo=$columnaDomingo;
		$columnaDomingo=obtenerSiguienteColumna($columnaAnteriorDomingo);
		
		$hora=strtotime($fConfiguracion[1]);
		
		$nFilaBase="";

		for($h=0;$h<24;$h++)
		{
			$rango=obtenerRangoHora($h);
			$rangoAux=$rango[0]+14;
			
			if($esHorarioVerano)
			{
				
				if(date("H:i:s",$hora)==date("H:i:s",$horaReferenciaCambioHorario))
				{
					$hora=strtotime("+1 hours",$hora);
					$nFilaBase=$rango[0];
				}
				
			}
			else
			{
				if(date("H:i:s",$hora)==date("H:i:s",$horaReferenciaCambioHorario))
					$nFilaBase=$rango[0];
			}
			$libro->combinarCelda($columnaAnteriorDomingo.$rango[0],$columnaAnteriorDomingo.$rangoAux);	
			$libro->setValor($columnaAnteriorDomingo.$rango[0],date("H:i",$hora));
			$libro->setTamano($columnaAnteriorDomingo.$rango[0],$letraHorario);
			
			
			
			
			$rango[0]=$rangoAux+1;
			$rangoAux=$rango[0]+14;
			$libro->combinarCelda($columnaAnteriorDomingo.$rango[0],$columnaAnteriorDomingo.$rangoAux);	
			$libro->setValor($columnaAnteriorDomingo.$rango[0],date("H:i",strtotime("+30 minutes",$hora)));
			$libro->setTamano($columnaAnteriorDomingo.$rango[0],$letraHorario);
			
			
			
			$hora=strtotime("+1 hours",$hora);
			
		}

		$libro->setColorFondo($columnaAnteriorDomingo.$nFilaBase.":".$columnaAnteriorDomingo."725","FF0000");
		
		$libro->setNegritas($columnaAnteriorDomingo."6:".$columnaAnteriorDomingo."725");
		$libro->setValor($columnaAnteriorDomingo."5","MEX");
		$columnaFinal=obtenerSiguienteColumna($columnaFinal);
		$libro->obtenerHojaActiva()->getPageSetup()->setPrintArea('A1:'.$columnaFinal.'727');
		
		
		
		
	}
	//--

	return $lblVersion;
}

function dibujarEventosRating($minutosDezplazamiento,$idSemana,$iMaster,$libro,$arrFechasSemana,$ocultarClaveTransmision=0,$version=-1,$idFeed)
{
	global $con;
	global $baseDir;
	global $arrColumnasSemana;
	global $minutosDezplazamiento;
	global $arrDiasSemana;
	global $arrMesLetra;
	global $arrHorasDia;	
	
	$consulta="SELECT leyenda FROM _593_gTransmision WHERE id__593_gTransmision=".$idFeed;
	$lblFeed=$con->obtenerValor($consulta);
	$letraTitulo=12;
	$letraHorario=12;	
	$letraEvento=11;
	$letraEvento2=9.5;
	$letraEvento3=6;
	
	if($ocultarClaveTransmision==1)
		$letraEvento=11;
	$margenDerecho=3.6;
	$constanteMargen=0.3937;
	
	$arrOcupacionColumnas=array();
	$consulta="SELECT id__593_tablaDinamica,clave,nombre FROM _593_tablaDinamica WHERE id__593_tablaDinamica=".$iMaster;
	$filaMaster=$con->obtenerPrimeraFila($consulta);
	$consulta="SELECT COUNT(*) FROM _593_gTransmision WHERE id__593_gTransmision=".$idFeed;
	$totalCanales=$con->obtenerValor($consulta);

	$consulta="SELECT etiqueta,noSemana,fechaInicio,fechaFin FROM 1050_semanasAnio WHERE idSemana=".$idSemana;
	$fSemana=$con->obtenerPrimeraFila($consulta);
	$arrProgramacionFecha=array();
	$fIAux=strtotime($fSemana[2]);
	$fFAux=strtotime($fSemana[3]);
	
	
	$consulta="SELECT fechaAplicacion,tipoHorarioAplica FROM _643_tablaDinamica WHERE fechaAplicacion>='".$fSemana[2]."' AND fechaAplicacion<='".$fSemana[3]."'";
	$fHorarioVerano=$con->obtenerPrimeraFila($consulta);
	
	$fechaAplicacionHorario=$fHorarioVerano[0];
		
	$aplicaCambioHorario=$fechaAplicacionHorario?true:false;
		
	
	while($fIAux<=$fFAux)
	{
		$arrProgramacionFecha[date("Y-m-d",$fIAux)]=array();
		$fIAux=strtotime("+1 days",$fIAux);
	}
	
	
	$consulta="SELECT fechaAplicacion,tipoHorarioAplica FROM _643_tablaDinamica WHERE fechaAplicacion>='".$fSemana[2]."' AND fechaAplicacion<='".$fSemana[3]."'";
	$fHorarioVerano=$con->obtenerPrimeraFila($consulta);
	
	$fechaAplicacionHorario=$fHorarioVerano[0];
		
	$aplicaCambioHorario=$fechaAplicacionHorario?true:false;

	$esHorarioVerano=$aplicaCambioHorario && ($fHorarioVerano[1]==1);
	$esHorarioInvierno=$aplicaCambioHorario && ($fHorarioVerano[1]==2);
	$horaReferenciaCambioHorario="";
	$horaFinalReferenciaCambio="";
	$horaLimiteInicioDomingo="";
	$horaLimiteInicioNormal="";
	
	$arrEventos="";	
	$arrColores=array();
	$consulta="SELECT id__594_tablaDinamica,horaInicial FROM _594_tablaDinamica";
	$fConfiguracion=$con->obtenerPrimeraFila($consulta);
	
	$start=$fSemana[2]." ".$fConfiguracion[1];
	$end=date("Y-m-d H:i:s",strtotime("+".$minutosDezplazamiento." minutes",strtotime($fSemana[3]." 23:59:59")));
	
	$consulta="SELECT * FROM _594_gConfiguracionColores WHERE idReferencia=".$fConfiguracion[0];
	$resColores=$con->obtenerFilas($consulta);
	while($fColor=mysql_fetch_row($resColores))
	{
		$arrColores[$fColor[2]]="#".$fColor[4];
	}
	
	$listaCanales="";
	$arrBloquesProgramacion=array();
	$totalCanalesMaster=0;
	$canalBaseMaster=-1;
	$consulta="SELECT id__593_gTransmision,confBase FROM _593_gTransmision WHERE id__593_gTransmision=".$idFeed." order by confBase desc,feedTransmision";
	$res=$con->obtenerFilas($consulta);
	while($fCanal=mysql_fetch_row($res))
	{
		$arrBloquesProgramacion[$fCanal[0]]=array();
		$totalCanalesMaster++;
		if($listaCanales=="")
			$listaCanales=$fCanal[0];
		else
			$listaCanales.=",".$fCanal[0];
		if($fCanal[1]==1)
			$canalBaseMaster=$fCanal[0];
	}
	
	$arrProgramasMulticanal=array();
	$arrRejillaGrid=array();
	
	
	$columnaMapeoInicial="A";
	$totalColumnasHorario=0;
	$consulta="SELECT id__593_gUsosHorarios,leyenda,horasDiferencia FROM _593_gUsosHorarios WHERE idReferencia=".$iMaster." AND posicionLeyenda=1
				union
	select 0,'MEX',0";
	$res=$con->obtenerFilas($consulta);
	
	while($fila=mysql_fetch_row($res))
	{
		$libro->setValor($columnaMapeoInicial."5",$fila[1]);
		$libro->setTamano($columnaMapeoInicial."5",$letraTitulo);
		
		for($h=0;$h<24;$h++)
		{
			$rango=obtenerRangoHora($h);
			$rangoAux=$rango[0]+15;
			
			if($fila[2]>=0)
				$hora=strtotime("+".$fila[2]." hours",$arrHorasDia[$h]);
			else
				$hora=strtotime("".$fila[2]." hours",$arrHorasDia[$h]);
			$libro->setValor($columnaMapeoInicial.$rango[0],date("H:i",$hora));
			$libro->setTamano($columnaMapeoInicial.$rango[0],$letraHorario);

			$libro->setValor($columnaMapeoInicial.$rangoAux,date("H:i",strtotime("+30 minutes",$hora)));
			$libro->setTamano($columnaMapeoInicial.$rangoAux,$letraHorario);
			if($fila[1]=='MEX')
			{
				$libro->setNegritas($columnaMapeoInicial."6:".$columnaMapeoInicial."725");
			}
			
		}
		
		$columnaMapeoInicial=obtenerSiguienteColumna($columnaMapeoInicial);
		$totalColumnasHorario++;
		
	}
	$libro->hojaActiva->removeColumn($columnaMapeoInicial,(9-$con->filasAfectadas));
	
	
	$columnaInicial=$columnaMapeoInicial;
	$arrProgramasMulticanalAux=array();
	foreach($arrProgramacionFecha as $fecha=>$resto)
	{
		if(!isset($arrProgramasMulticanal[$fecha]))
		{
			
			if(!isset($arrProgramasMulticanalAux[$fecha."_".$listaCanales]))
				$arrProgramasMulticanalAux[$fecha."_".$listaCanales]=array();
			$arrProgramasMulticanalAux[$fecha."_".$listaCanales]["columna"]=$columnaInicial;
			$arrProgramasMulticanalAux[$fecha."_".$listaCanales]["canales"]=$listaCanales;
				
			$columnaInicial=obtenerSiguienteColumna($columnaInicial);
		}
		else
		{
			$oMulticanal=$arrProgramasMulticanal[$fecha];
			$arrConjuntoCanal=array();
			for($x=0;$x<sizeof($oMulticanal);$x++)
			{
				$oBase=explode(",",$oMulticanal[$x][1]);
				$existeConjunto=false;
				foreach($oBase as $canalBase)
				{
					for($x2=($x+1);$x2<sizeof($oMulticanal);$x2++)
					{
						$oReferencia=explode(",",$oMulticanal[$x2][1]);
						foreach($oReferencia as $canalReferencia)
						{
							if($canalBase==$canalReferencia)
							{
								$existeConjunto=true;
								break;
							}
						}
						
						if($existeConjunto)
						{
							break;
						}
						
					}
					if($existeConjunto)
					{
						break;
					}
				}
				
				if($existeConjunto)
				{
					foreach($oBase as $canalBase)
					{
						$arrConjuntoCanal[$canalBase]=1;
					}
				}
				else
				{
					$arrConjuntoCanal[$oMulticanal[$x][1]]=1;
				}
				
			}
			
			foreach($arrConjuntoCanal as $canales=>$resto)
			{
				if(!isset($arrProgramasMulticanalAux[$fecha."_".$canales]))
					$arrProgramasMulticanalAux[$fecha."_".$canales]=array();
				$arrProgramasMulticanalAux[$fecha."_".$canales]["columna"]=$columnaInicial;
				$arrProgramasMulticanalAux[$fecha."_".$canales]["canales"]=$canales;
				$columnaInicial=obtenerSiguienteColumna($columnaInicial);
			}
			
		}
		
	}
	
	//$diferenciaColumnasHorario=($totalAnchoColumnasHorario-(8.33*$totalColumnasHorario))/7;
	
	//$compAjuste=4*$totalColumnasHorario
	
	$columnaInicialEncabezado=$columnaMapeoInicial;

	$libro->hojaActiva->insertNewColumnBefore($columnaMapeoInicial,sizeof($arrProgramasMulticanalAux));
	$columna="";
	
	$columnaJueves="";
	$columnaDomingo="";
	foreach($arrProgramasMulticanalAux as $fecha=>$resto)
	{
		$arrFecha=explode("_",$fecha);
		$dteFecha=strtotime($arrFecha[0]);
		
		if(date("w",$dteFecha)==4)
		{
			
			$columnaJueves=$resto["columna"];
		}
		else
			if(date("w",$dteFecha)==0)
			{
				$arrFechaAux=explode("_",$fecha);
				$columnaDomingo=$resto["columna"];
				$horaReferenciaCambioHorario=strtotime($arrFechaAux[0]." 02:00:00");
				$horaFinalReferenciaCambio=strtotime("+1 hours",$horaReferenciaCambioHorario);
				$horaLimiteInicioDomingo=strtotime("+1 hours",strtotime(date("Y-m-d",$horaReferenciaCambioHorario)." ".$fConfiguracion[1]));
				$horaLimiteInicioNormal=strtotime("-1 hours",$horaLimiteInicioDomingo);
			}
		//$leyenda=utf8_encode($arrDiasSemana[date("w",$dteFecha)])."\r";
		//$leyenda.=date("d",$dteFecha)." de ".$arrMesLetra[(date("m",$dteFecha)*1)-1]." de ".date("Y",$dteFecha);
		
		$leyenda=utf8_encode(substr($arrDiasSemana[date("w",$dteFecha)],0,3))." ".date("d/m/Y",$dteFecha) ;
		$consulta="SELECT feedTransmision FROM _593_gTransmision WHERE id__593_gTransmision in(".$resto["canales"].") order by feedTransmision";
		$listaCanales=$con->obtenerListaValores($consulta);
		//$leyenda.="\r(".$listaCanales.")\r";
		
		$tCanales=explode(",",$resto["canales"]);
		$tCanales=sizeof($tCanales);
		if($totalCanales!=$tCanales)
		{
			$consulta="SELECT distinct leyenda FROM _593_gTransmision WHERE id__593_gTransmision in(".$resto["canales"].") order by feedTransmision";
			$listaCanales=$con->obtenerListaValores($consulta);
			$leyendaCanal=str_replace(",","\r",$listaCanales);
			$libro->setValor($resto["columna"]."4",$leyendaCanal);
			
			
			
			$arrCanalesMaster=explode(",",$resto["canales"]);
			$encontradoBase=false;
			foreach($arrCanalesMaster as $iCanal)
			{
				if($iCanal==$canalBaseMaster)
				{
					$encontradoBase=true;
					break;
				}
			}
			if(!$encontradoBase)
			{
				
				$libro->setColorFondo($resto["columna"]."6:".$resto["columna"]."725","9BC1E6");
				
			}
			
			
			
		}
		$libro->setValor($resto["columna"]."5",$leyenda);
		$libro->setTamano($resto["columna"]."5",$letraTitulo);
		$libro->setAnchoColumna($resto["columna"],30);
		$libro->unsetNegritas($resto["columna"]."6:".$resto["columna"]."725");
		$columna=$resto["columna"];
	}
	$columna=obtenerSiguienteColumna($columna);
	$libro->hojaActiva->removeColumn($columna,1);


	$consulta="select 0,'MEX',0
			union
			SELECT id__593_gUsosHorarios,leyenda,horasDiferencia FROM _593_gUsosHorarios WHERE idReferencia=".$iMaster." AND posicionLeyenda=2";
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		$libro->setValor($columna."5",$fila[1]);
		$libro->setTamano($columna."5",$letraTitulo);
		
		for($h=0;$h<24;$h++)
		{
			$rango=obtenerRangoHora($h);
			$rangoAux=$rango[0]+15;
			
			if($fila[2]>=0)
				$hora=strtotime("+".$fila[2]." hours",$arrHorasDia[$h]);
			else
				$hora=strtotime("".$fila[2]." hours",$arrHorasDia[$h]);
			$libro->setValor($columna.$rango[0],date("H:i",$hora));
			$libro->setTamano($columna.$rango[0],$letraHorario);
			$libro->setValor($columna.$rangoAux,date("H:i",strtotime("+30 minutes",$hora)));
			$libro->setTamano($columna.$rangoAux,$letraHorario);
			if($fila[1]=='MEX')
			{
				$libro->setNegritas($columna."6:".$columna."725");
			}
			
		}
		$columna=obtenerSiguienteColumna($columna);
		$totalColumnasHorario++;
		
	}
	
	
	$columnaFinal=obtenerAnteriorColumna($columna);
	
	$consulta="SELECT versionLiberacion FROM 5001_situacionProgramacionSemanal WHERE idSemana=".$idSemana." AND idMaster=".$iMaster;
	$versionLiberacion=$con->obtenerValor($consulta);
	
	$versionPrint=$version;
	if($versionPrint==-1)
	{
		$consulta="SELECT VERSION FROM 5001_situacionProgramacionSemanal WHERE idSemana=".$idSemana." AND idMaster=".$iMaster;
		$versionPrint=$con->obtenerValor($consulta);
	}
	
	$lblVersion="";
	$chrVersion="";
	if($versionLiberacion=="")
	{
		$chrVersion=": V. ".$versionPrint;
		$lblVersion=$versionPrint;
	}
	else
	{
		$chrVersion=chr(65+($versionPrint-$versionLiberacion));
		$lblVersion=$chrVersion;
	}
	
	
	$fIAux=strtotime($fSemana[2]);
	$fFAux=strtotime($fSemana[3]);
	
	$libro->setHAlineacion("A1:".$columnaFinal."4","C");
	
	$libro->setValor("B727","CWeek No.: ".$fSemana[1].$chrVersion." (Ratings)");
	
	$columnaDate=$libro->obtenerDesplazamientoColumna($columnaFinal,$columnaFinal,-3+($aplicaCambioHorario?1:0));
	$libro->setValor($columnaDate."727","Date Range: ".(date("d/m/Y",$fIAux)." - ".date("d/m/Y",$fFAux)));
	$libro->setNegritas("B727:".$columnaFinal."727");
	$libro->setTamano("B727:".$columnaFinal."727",$letraTitulo);
	$libro->setValor("A3","Time Zone: México");
	
	
	$libro->setNegritas("A3:".$columnaFinal."3");
	$libro->setTamano("A3:".$columnaFinal."4",$letraTitulo);
	$libro->setHAlineacion("A1:".$columnaFinal."4","I");	
	$columnaDate=$libro->obtenerDesplazamientoColumna($columnaFinal,$columnaFinal,-3+($aplicaCambioHorario?1:0));
	$libro->setValor($columnaDate."3","Date Range: ".(date("d/m/Y",$fIAux)." - ".date("d/m/Y",$fFAux)));
	$objImg=$libro->crearObjetoImagen($baseDir."/modulosEspeciales_FOX/plantilla/images/".$filaMaster[1].".png");
	$objImg->setHeight(100);
	$objImg->setResizeProportional(true);
	
	$columnaLogo=$columnaJueves;
	

	$noColumna=$libro->columnaToNumero($columnaFinal);
	$totalColumnas=ceil($noColumna/2);
	$libro->setValor($columnaLogo."2",$lblFeed);
	$libro->setHAlineacion($columnaLogo."2:".$columnaLogo."2","C");
	$libro->insertarImagenHoja(0,$objImg,$columnaLogo."1");
	
	
	$libro->setValor($columnaLogo."3","WEEKLY PROGRAM SCHEDULE  - ".$filaMaster[1]);
	//$libro->insertarImagenHoja(0,$objImg2,$columnaLogo."728");
	$libro->obtenerHojaActiva()->getPageSetup()->setPrintArea('A1:'.$columnaFinal.'727');
	$libro->obtenerHojaActiva()->getPageMargins()->setLeft(0.3);	
	$libro->obtenerHojaActiva()->getPageMargins()->setRight(0.3);
	$libro->obtenerHojaActiva()->getPageMargins()->setTop(0.3);
	$libro->obtenerHojaActiva()->getPageMargins()->setBottom(0.3);
	
	//$libro->obtenerHojaActiva()->getPageMargins()->setLeft(($margenDerecho-(($totalColumnasHorario*1.2)/2))*$constanteMargen);	
	//$libro->obtenerHojaActiva()->getPageMargins()->setRight(($margenDerecho-(($totalColumnasHorario*1.2)/2))*$constanteMargen);	
	
	$consulta="SELECT VERSION FROM 5001_situacionProgramacionSemanal WHERE idSemana=".$idSemana." AND idMaster=".$iMaster;
	$versionActual=$con->obtenerValor($consulta);
	if($version!=-1)
		$versionActual=$version;
	
	$versionComparacion=$versionActual-1;
	if($versionComparacion<1)
		$versionComparacion=1;
	
	
	if($versionLiberacion==$versionActual)
	{
		$versionComparacion=$versionActual;
	}
	 
	 
	
	foreach($arrProgramasMulticanalAux as $fechaDia=>$resto)
	{
		$arrFechaCanal=explode("_",$fechaDia);
		
		$fechaDia=$arrFechaCanal[0];
		
		$startDia=$fechaDia." ".$fConfiguracion[1];
		
		$rangoInicioDia=strtotime($startDia);
		$rangoFinDia=strtotime("+23 hours 59 minutes",$rangoInicioDia);
		$aplicaAjusteHorario=$horaReferenciaCambioHorario>=$rangoInicioDia && $horaReferenciaCambioHorario<=$rangoFinDia;
		
		
		$startDiaDte=strtotime(-$minutosDezplazamiento." minutes",strtotime($startDia));
		$endDia=date("Y-m-d H:i:s",strtotime("+".$minutosDezplazamiento." minutes",strtotime($fechaDia." 23:59:59")));
		
		if(($aplicaAjusteHorario)&&($esHorarioVerano))
		{

			$endDia=date("Y-m-d H:i:s",strtotime("+".($minutosDezplazamiento+60)." minutes",strtotime($fechaDia." 23:59:59")));
		}
		
		$consulta="SELECT distinct p.* FROM 5000_programacionEventosRating p,5000_programacionEventosFeedTransmitionRating c WHERE 
				((horaInicio>='".$startDia."' AND  horaInicio<='".$endDia."') 
							OR (horaFin>='".$startDia."' AND horaFin<'".$endDia."'))	
					 
					and idMaster=".$iMaster." and c.idProgramacion=p.idRegistro and c.idFeedTransmition in(".$resto["canales"].
					") and p.idRegistro and p.eliminado=0  order by horaInicio";
					
		
		
		
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_assoc($res))
		{
			
			$filaV=NULL;
			if($versionComparacion==$versionActual)
			{
				$filaV=$fila;
			}
			else
			{
				
				$consulta="SELECT distinct p.* FROM 5001_programacionEventosRespaldoVersion p,5001_programacionEventosFeedTransmitionRespaldoVersion c WHERE 
						p.idRegistroEvento=".$fila["idRegistro"]." AND p.version=".$versionComparacion." and c.idProgramacion=p.idRegistroEvento and c.version=".$versionComparacion.
					" and c.idFeedTransmition in(".$resto["canales"].") order by horaInicio";

				$filaV=$con->obtenerPrimeraFilaAsoc($consulta);
					
				
			}
			

			$diferenciaMinutos=0;
			
			$leyendaDiferencia="";
			$consulta="SELECT horaInicio,horaFin FROM 5000_programacionEventos WHERE idRegistro=".($fila["idProgramacionEventoBase"]==""?-1:$fila["idProgramacionEventoBase"]);
			$fRegistroBase=$con->obtenerPrimeraFilaAsoc($consulta);
			if($fRegistroBase)
			{
				if(($fRegistroBase["horaInicio"]!=$fila["horaInicio"])||($fRegistroBase["horaFin"]!=$fila["horaFin"]))
				{
					
					$diferenciaMinutos=obtenerDiferenciaMinutos($fila["horaInicio"],$fila["horaFin"]);
					
					$horas=parteEntera($diferenciaMinutos/60);
					$minutos=$diferenciaMinutos-($horas*60);
					$leyendaDiferencia="\nDURÓ ";
					if($horas>0)
						$leyendaDiferencia.=$horas."HRS ";
					if($minutos>0)
						$leyendaDiferencia.=$minutos."MIN";
					
				}
				
			}
			else
			{
				$diferenciaMinutos=obtenerDiferenciaMinutos($fila["horaInicio"],$fila["horaFin"]);
					
				$horas=parteEntera($diferenciaMinutos/60);
				$minutos=$diferenciaMinutos-($horas*60);
				$leyendaDiferencia="\nDURÓ ";
				if($horas>0)
					$leyendaDiferencia.=$horas."HRS ";
				if($minutos>0)
					$leyendaDiferencia.=$minutos."MIN";
				
			}
			
			$horaInicioPrograma=strtotime($fila["horaInicio"]);
			$horaFinPrograma=strtotime($fila["horaFin"]);
			
			
			if(($esHorarioVerano)&&($aplicaAjusteHorario))
			{
				
				if(($horaInicioPrograma>=$horaReferenciaCambioHorario)&&($horaInicioPrograma<$horaLimiteInicioDomingo))
				{
					if($horaInicioPrograma<$horaFinalReferenciaCambio)
					{
						$horaInicioPrograma=$horaFinalReferenciaCambio;
					}
					else
					{

						$horaInicioPrograma=strtotime("-1 hours",$horaInicioPrograma);	
					}
					
				}
				
				if(($horaFinPrograma>$horaReferenciaCambioHorario)&&($horaFinPrograma<=$horaLimiteInicioDomingo))
				{
					
					if($horaFinPrograma<$horaFinalReferenciaCambio)
					{
						$horaFinPrograma=$horaReferenciaCambioHorario;
					}
					else
						$horaFinPrograma=strtotime("-1 hours",$horaFinPrograma);	
				}
				
				
				
				
				if($horaInicioPrograma>=$horaFinPrograma)
				{
					continue;
				}
				
				
			}
			
			
			$lblNombrePrograma="";
			$fechaInicio=strtotime("-".$minutosDezplazamiento." minutes",$horaInicioPrograma);
			$fechaFin=strtotime("-".$minutosDezplazamiento." minutes",$horaFinPrograma);		
			
			
			
			if($fechaFin==strtotime(date("Y-m-d",$fechaFin)." 00:00"))
			{
				$fechaFin=strtotime("-1 minute",$fechaFin);
			}
			
			if($fechaFin<$startDiaDte)
			{
				
				continue;
			}
			
			$consulta="SELECT abreviatura FROM _586_tablaDinamica WHERE id__586_tablaDinamica=".$fila["tipoEmision"];
			$tipoEmision=$con->obtenerValor($consulta);
			$objRichText = new PHPExcel_RichText();
			
			
			$fechaColumna=date("Y-m-d",$fechaInicio);
			
			$columnaDibujo=$resto["columna"];
			$columnaDibujoFinal=$columnaDibujo;
			
			$arrRango=obtenerRangoHora(date("H",$fechaInicio));
			$rango1=$arrRango[0];
			$rango1+=floor(date("i",$fechaInicio)*0.5);
			$arrRango=obtenerRangoHora(date("H",$fechaFin));
			$rango2=$arrRango[0]-1;
			$rango2+=floor(date("i",$fechaFin)*0.5);
			
			
			$lblNombreProgramaBase="";
			$lblNombreProgramaComparacion="";
			
			$consulta="SELECT nombrePrograma,clavePrograma,proy.tipoTransmision,pr.colorIdentificacion FROM _589_tablaDinamica pr, _588_tablaDinamica proy 
					WHERE id__589_tablaDinamica=".$fila["idPrograma"]." and proy.id__588_tablaDinamica=pr.proyectos";
			$fPrograma=$con->obtenerPrimeraFila($consulta);
			$lblNombrePrograma=$fPrograma[0];


			if($filaV)
			{
				$consulta="SELECT nombrePrograma,clavePrograma,proy.tipoTransmision,pr.colorIdentificacion FROM _589_tablaDinamica pr, _588_tablaDinamica proy 
					WHERE id__589_tablaDinamica=".$filaV["idPrograma"]." and proy.id__588_tablaDinamica=pr.proyectos";
				$fProgramaV=$con->obtenerPrimeraFila($consulta);
				$lblNombreProgramaComparacion=$fProgramaV[0];
				
				if($filaV["detalleEvento"]!="")
				{
					$lblNombreProgramaComparacion.="\n".str_replace("<br />","\n",$filaV["detalleEvento"]);
				}
				
				if($filaV["porConfirmar"]==1)
				{
					$lblNombreProgramaComparacion.="\n--Evento por confirmar--";
				}
				
			}


			if($fila["detalleEvento"]!="")
			{
				$lblNombrePrograma.="\n".str_replace("<br />","\n",$fila["detalleEvento"]);
			}
			
			if($fila["porConfirmar"]==1)
			{
				$lblNombrePrograma.="\n--Evento por confirmar--";
			}
			$tipoEvento=0;
			
			if($fila["porConfirmar"]==1)
			{
				$tipoEvento=3;
			}
			else
				if($fila["cveTransmision"]=="")
				{
					$tipoEvento=2;
				}
				else
					$tipoEvento=1;
					
					
			$mostrarTipoEmision=true;		
			
			if($fila["tipoEmision"]==9)
				$mostrarTipoEmision=false;		
			
			$diferenciaTiempo=obtenerDiferenciaMinutos($fila["horaInicio"],$fila["horaFin"]);
			$fConfiguracionTam=obtenerConfiguracionInformeAgenda($diferenciaTiempo,3);
					
			$lblNombreProgramaBase=$lblNombrePrograma;					
			if($ocultarClaveTransmision==0)	
			{
				$lblNombreProgramaBase.=" (".$tipoEmision.") [".$fila["cveTransmision"]."] ".$fila["horaInicio"]."-".$fila["horaFin"];
				if($filaV)
				{
					$consulta="SELECT abreviatura FROM _586_tablaDinamica WHERE id__586_tablaDinamica=".$filaV["tipoEmision"];
					$tipoEmisionV=$con->obtenerValor($consulta);
					$lblNombreProgramaComparacion.=" (".$tipoEmisionV.") [".$filaV["cveTransmision"]."] ".$filaV["horaInicio"]."-".$filaV["horaFin"];
				
				}
				//$objRichText->createText("(".$fila["idRegistro"].")".$lblNombrePrograma."\n(");
				
				if($mostrarTipoEmision)
				{
					$objAux=$objRichText->createTextRun($lblNombrePrograma."\n");
					$objAux->getFont()->setBold($fConfiguracionTam["programaNegritas"]==1?true:false);
					$objAux->getFont()->setSize($fConfiguracionTam["tamPrograma"]);
					
					$objAux=$objRichText->createTextRun("(");
					$objAux->getFont()->setBold($fConfiguracionTam["tipoTransmisionNegritas"]==1?true:false);
					$objAux->getFont()->setSize($fConfiguracionTam["tamTipoTransmision"]);
					
					$objAux=$objRichText->createTextRun($tipoEmision);
					$objAux->getFont()->setBold($fConfiguracionTam["tipoTransmisionNegritas"]==1?true:false);
					$objAux->getFont()->setSize($fConfiguracionTam["tamTipoTransmision"]);
					
					
					$objAux=$objRichText->createTextRun(")");
					$objAux->getFont()->setBold($fConfiguracionTam["tipoTransmisionNegritas"]==1?true:false);
					$objAux->getFont()->setSize($fConfiguracionTam["tamTipoTransmision"]);
					
					
					$objAux=$objRichText->createTextRun(" [");
					$objAux->getFont()->setBold($fConfiguracionTam["claveTrasmisionNegritas"]==1?true:false);
					$objAux->getFont()->setSize($fConfiguracionTam["tamTipoTransmision"]);
					
					if(trim($fila["cveTransmision"])!="")
					{
						$objAux=$objRichText->createTextRun($fila["cveTransmision"]);
						$objAux->getFont()->setBold($fConfiguracionTam["claveTrasmisionNegritas"]==1?true:false);
						$objAux->getFont()->setSize($fConfiguracionTam["tamClaveTransmision"]);
					}
					$objAux=$objRichText->createTextRun("]");
					$objAux->getFont()->setBold($fConfiguracionTam["claveTrasmisionNegritas"]==1?true:false);
					$objAux->getFont()->setSize($fConfiguracionTam["tamTipoTransmision"]);
					
					
				}
				else
				{
					$objAux=$objRichText->createTextRun($lblNombrePrograma);
					$objAux->getFont()->setBold($fConfiguracionTam["programaNegritas"]==1?true:false);
					$objAux->getFont()->setSize($fConfiguracionTam["tamPrograma"]);
					

				}
				
				if($leyendaDiferencia)
				{
					$objAux=$objRichText->createTextRun($leyendaDiferencia);
					$objAux->getFont()->setBold(true);
					$phpColor = new PHPExcel_Style_Color();
					$phpColor->setRGB('FF0000');  
					$objAux->getFont()->setColor($phpColor);
				}
				
				
			}
			else
			{

				if($mostrarTipoEmision)
				{
					$objAux=$objRichText->createTextRun($lblNombrePrograma."\n");
					$objAux->getFont()->setBold($fConfiguracionTam["programaNegritas"]==1?true:false);
					$objAux->getFont()->setSize($fConfiguracionTam["tamPrograma"]);
					
					$objAux=$objRichText->createTextRun("(");
					$objAux->getFont()->setBold($fConfiguracionTam["tipoTransmisionNegritas"]==1?true:false);
					$objAux->getFont()->setSize($fConfiguracionTam["tamTipoTransmision"]);
					
					$objAux=$objRichText->createTextRun($tipoEmision);
					$objAux->getFont()->setBold($fConfiguracionTam["tipoTransmisionNegritas"]==1?true:false);
					$objAux->getFont()->setSize($fConfiguracionTam["tamTipoTransmision"]);
					
					$objAux=$objRichText->createTextRun(")");
					$objAux->getFont()->setBold($fConfiguracionTam["corcheteParentesisNegris"]==1?true:false);
					$objAux->getFont()->setSize($fConfiguracionTam["tamTipoTransmision"]);
					
				}
				else
				{
					$objAux=$objRichText->createTextRun($lblNombrePrograma);
					$objAux->getFont()->setBold($fConfiguracionTam["programaNegritas"]==1?true:false);
					$objAux->getFont()->setSize($fConfiguracionTam["tamPrograma"]);
				}
			}

			
			if(date("Y-m-d",$fechaInicio)==date("Y-m-d",$fechaFin))
			{
				
				$libro->combinarCelda($columnaDibujo.$rango1,$columnaDibujo.$rango2);
				$libro->setValor($columnaDibujo.$rango1,$objRichText);
				//echo "A".date("Y-m-d H:i",$fechaInicio)."-".date("Y-m-d H:i",$fechaFin)." (".$fila["idRegistro"].")=".$columnaDibujo.$rango1.":".$columnaDibujo.$rango2."<br>";
				if($leyendaDiferencia!="")
				{
					
					$libro->setColorFondo($columnaDibujo.$rango1,"ECDD08");
				}
				$libro->setBorde($columnaDibujo.$rango1,"DE","000000");//str_replace("#","",$arrColores[$tipoEvento])
				$tamanoLetra=$letraEvento;
				if($diferenciaTiempo<22)
				{
					if($diferenciaTiempo>12)
						$tamanoLetra=$letraEvento2;
					else
						$tamanoLetra=$letraEvento2;
				}
				$libro->setHAlineacion($columnaDibujo.$rango1.":".$columnaDibujo.$rango1,"C");
				$libro->setVAlineacion($columnaDibujo.$rango1.":".$columnaDibujo.$rango1,"S");
				$libro->hojaActiva->getStyle($columnaDibujo.$rango1)->getAlignment()->setWrapText(true);
			}
			else
			{
				$rango1="";
				$rango2="";
				if($startDiaDte<$fechaInicio)
				{
				
					$arrRango=obtenerRangoHora(date("H",$fechaInicio));
					$rango1=$arrRango[0];
					$rango1+=floor(date("i",$fechaInicio)*0.5);
					$fechaFinAux=strtotime(date("Y-m-d",$fechaInicio)." 23:59");
		
					$arrRango=obtenerRangoHora(date("H",$fechaFinAux));
					
					$rango2=$arrRango[0]-1;
					$rango2+=floor(date("i",$fechaFinAux)*0.5)+1;
				}
				else
				{
					
					
					$rango1=6;
					
					$arrRango=obtenerRangoHora(date("H",$fechaFin));
					
					$rango2=$arrRango[0]-1;
					$rango2+=floor(date("i",$fechaFin)*0.5);
					
					//echo "A".date("Y-m-d H:i",$fechaInicio)."-".date("Y-m-d H:i",$fechaFin)." (".$fila["idRegistro"].")=".$columnaDibujo.$rango1.":".$columnaDibujo.$rango2."<br>";
				
					//echo $rango1."-".$
				}
				
				
				$libro->combinarCelda($columnaDibujo.$rango1,$columnaDibujo.$rango2);
				$libro->setValor($columnaDibujo.$rango1,$objRichText);
				if($leyendaDiferencia!="")
					$libro->setColorFondo($columnaDibujo.$rango1,"ECDD08");
				$libro->setBorde($columnaDibujo.$rango1,"DE","000000");
				
				$libro->setHAlineacion($columnaDibujo.$rango1.":".$columnaDibujo.$rango1,"C");
				$libro->setVAlineacion($columnaDibujo.$rango1.":".$columnaDibujo.$rango1,"S");
				$libro->hojaActiva->getStyle($columnaDibujo.$rango1)->getAlignment()->setWrapText(true);
				
			}
			
		}
		
		
	}
	
	if($aplicaCambioHorario)
	{
		$libro->hojaActiva->insertNewColumnBefore($columnaDomingo,1);
		$columnaAnteriorDomingo=$columnaDomingo;
		$columnaDomingo=obtenerSiguienteColumna($columnaAnteriorDomingo);
		
		$hora=strtotime($fConfiguracion[1]);
		
		$nFilaBase="";

		for($h=0;$h<24;$h++)
		{
			$rango=obtenerRangoHora($h);
			$rangoAux=$rango[0]+14;
			
			if($esHorarioVerano)
			{
				
				if(date("H:i:s",$hora)==date("H:i:s",$horaReferenciaCambioHorario))
				{
					$hora=strtotime("+1 hours",$hora);
					$nFilaBase=$rango[0];
				}
				
			}
			else
			{
				if(date("H:i:s",$hora)==date("H:i:s",$horaReferenciaCambioHorario))
					$nFilaBase=$rango[0];
			}
			$libro->combinarCelda($columnaAnteriorDomingo.$rango[0],$columnaAnteriorDomingo.$rangoAux);	
			$libro->setValor($columnaAnteriorDomingo.$rango[0],date("H:i",$hora));
			$libro->setTamano($columnaAnteriorDomingo.$rango[0],$letraHorario);
			
			
			
			
			$rango[0]=$rangoAux+1;
			$rangoAux=$rango[0]+14;
			$libro->combinarCelda($columnaAnteriorDomingo.$rango[0],$columnaAnteriorDomingo.$rangoAux);	
			$libro->setValor($columnaAnteriorDomingo.$rango[0],date("H:i",strtotime("+30 minutes",$hora)));
			$libro->setTamano($columnaAnteriorDomingo.$rango[0],$letraHorario);
			
			
			
			$hora=strtotime("+1 hours",$hora);
			
		}

		$libro->setColorFondo($columnaAnteriorDomingo.$nFilaBase.":".$columnaAnteriorDomingo."725","FF0000");
		
		$libro->setNegritas($columnaAnteriorDomingo."6:".$columnaAnteriorDomingo."725");
		$libro->setValor($columnaAnteriorDomingo."5","MEX");
		$columnaFinal=obtenerSiguienteColumna($columnaFinal);
		$libro->obtenerHojaActiva()->getPageSetup()->setPrintArea('A1:'.$columnaFinal.'727');
		
		
		
		
	}
	
	return $lblVersion;
}

function generarReporteProgramacionSemanal($idSemana,$formato="PDF",$descargarDocumento=true,$tiposEmision="3,4",$idMaster="",$version=-1)
{
	global $con;
	global $baseDir;
	global $arrDiasSemana;
	$colorEventosConfirmar="b3e327";
	
	$arrCamposCambio=array();
	$arrCamposCambio["horaInicio"]="A";
	$arrCamposCambio["horaFin"]="A";
	$arrCamposCambio["idPrograma"]="B";
	$arrCamposCambio["tipoEmision"]="C";
	$arrCamposCambio["paisOrigen"]="D";
	$arrCamposCambio["comentariosAdicionales"]="E";
	
	$consulta="SELECT fechaInicio,fechaFin,noSemana FROM 1050_semanasAnio WHERE idSemana='".$idSemana."'";
	$fechasPeriodo=$con->obtenerPrimeraFila($consulta);
	
	$fechaIPeriodo=strtotime($fechasPeriodo[0]);
	$fechaFPeriodo=strtotime($fechasPeriodo[1]);
		
	$numSemana=obtenerNumeroSemanaFox($idSemana);
	$nombreSemana="SEMANA ".$numSemana;
	
	$periodo=obtenerPeriodoSemana($idSemana);
	
	
	$consulta="SELECT id__593_tablaDinamica,clave,nombre FROM _593_tablaDinamica WHERE situacion=1";
	if($idMaster!="")
		$consulta.=" and id__593_tablaDinamica=".$idMaster;
	
	$res=$con->obtenerFilas($consulta);
	
	$arrLibros=array();
	
	$libro=null;
	$n=0;
	while($filaMaster=mysql_fetch_row($res))
	{
		$idMasterPrincipal=$filaMaster[0];
		$libro=new cExcel($baseDir."/reportes/fox/repFox.xlsx",true,"Excel2007");
		

		$fechaIPeriodo=strtotime($fechasPeriodo[0]);
		$fechaFPeriodo=strtotime("+1 days",strtotime($fechasPeriodo[1]));
		
		$clave=obtenerNombreMaster($filaMaster[0]);	
		
		
		$libro->cambiarTituloHoja(0,$clave);
		$libro->setAnchoColumna("A",'20');
		$libro->setAnchoColumna("B",'40');
		$libro->setAnchoColumna("C",'16');
		$libro->setAnchoColumna("D",'26');
		$libro->setAnchoColumna("E",'45');
		

		$objImg=$libro->crearObjetoImagen($baseDir."/modulosEspeciales_FOX/plantilla/images/".$filaMaster[1].".png");
		$objImg->setHeight(100);
		$libro->setNegritas("B1:E4");
		$libro->setFuente("B1:E4","Trebuchet MS");
		$libro->combinarCelda("B1","E1");
		$libro->setColorFondo("A1:E1","060100","S");
		$libro->setHAlineacion("B1:E1","C");
		$libro->insertarImagenHoja($n,$objImg,"A1");
		$libro->setColorFuente("B1:E1","FFFFFF");
		$libro->setTamano("B1:E1",16);
		$libro->setValor("B1","Programación México para ".ucwords(mb_strtolower($filaMaster[2])));
		
		$libro->combinarCelda("A2","E2");
		$libro->setHAlineacion("A2:E2","C");
		$libro->setTamano("A2:E2",14);
		$libro->setValor("A2",$nombreSemana);

		$libro->combinarCelda("A3","E3");
		$libro->setHAlineacion("A3:E3","C");
		$libro->setTamano("A3:E3",14);
		$libro->setValor("A3",$periodo);
		
		$libro->combinarCelda("A4","E4");
		$libro->setHAlineacion("A4:E4","C");
		$libro->setTamano("A4:E4",14);
		$libro->setValor("A4",$filaMaster[2]);
		
		
		$linea=6;
		$datosSem=obtenerDatosSemana($idSemana,$idMasterPrincipal,$tiposEmision,$version);
		
		if(!empty($datosSem))
		{
			
			foreach($datosSem as $master=>$datos)
			{
				$idMaster=$master;
				$linea=6;
				
				
				$clave=obtenerNombreMaster($idMaster);
					
				while($fechaIPeriodo<=$fechaFPeriodo)
				{
					
					$fecha=date("Y-m-d",$fechaIPeriodo);
					
					$dteFecha=strtotime($fecha);
					$nombreDia=utf8_encode($arrDiasSemana[date("w",$dteFecha)])." ".date("d",$dteFecha);
					
					$libro->setNegritas("A".$linea.":E".$linea);
					$libro->setColorFondo("A".$linea.":E".$linea,"060100","S");
					$libro->setColorFuente("A".$linea.":E".$linea,"FFFFFF");
					
					$libro->setFuente("A".$linea.":E".$linea,"Trebuchet MS");
					$libro->setTamano("A".$linea.":E".$linea,12);
					$libro->setValor("A".$linea,$nombreDia);
					$linea++;
					$libro->setNegritas("A".$linea.":E".$linea);
					$libro->setFuente("A".$linea.":E".$linea,"Trebuchet MS");
					$libro->setTamano("A".$linea.":E".$linea,10);
					$libro->setColorFondo("A".$linea.":E".$linea,"F9FF33","S");
					$libro->setHAlineacion("A".$linea.":E".$linea,"C");
					$libro->setValor("A".$linea,"Hora");
					$libro->setValor("B".$linea,"Programa");
					$libro->setValor("C".$linea,"Aire");
					$libro->setValor("D".$linea,"Producción");
					$libro->setValor("E".$linea,"Comentarios");
					$linea++;
					
					if(isset($datos[date("Y-m-d",$fechaIPeriodo)]))
					{
						
						$info=$datos[date("Y-m-d",$fechaIPeriodo)];
					
						foreach($info as $prog)
						{
							
							$idRegistro=$prog["idRegistro"];
							$horaInicial=$prog["horaInicial"];
							$horaFinal=$prog["horaFinal"];
							$idPrograma=$prog["idPrograma"];
							$nombrePrograma=$prog["nombrePrograma"];
							$paisOrigen=$prog["paisOrigen"];
							$comentario=$prog["comentarios"];
							$tipoEmision=$prog["tipoEmision"];
							
							$horario=$horaInicial." / ".$horaFinal;
							$libro->setTamano("A".$linea.":E".$linea,9);							
							$libro->setValor("A".$linea,$horario);
							$nombrePrograma=str_replace("<br />","\n",$nombrePrograma);
							$libro->setValor("B".$linea,$nombrePrograma);
							$libro->obtenerHojaActiva()->getStyle("B".$linea)->getAlignment()->setWrapText(true);
							$libro->obtenerHojaActiva()->getStyle("E".$linea)->getAlignment()->setWrapText(true);							
							
							$totalLineas=ceil(strlen($nombrePrograma)/30)-1;
							$arrEnterComentario=explode("\n",$nombrePrograma);
							$totalLineas+=sizeof($arrEnterComentario)-1;
							$libro->setValor("C".$linea,$tipoEmision);
							$libro->setHAlineacion("C".$linea.":D".$linea,"C");
							
							$libro->setValor("D".$linea,$paisOrigen);
							$comentario=str_replace("<br />","\n",urldecode(urldecode(urldecode($comentario))));
							$libro->setValor("E".$linea,$comentario);
							
							$totalLineasComentarios=ceil(strlen($comentario)/35)-1;

							
							$arrEnterComentario=explode("\n",$comentario);
							$totalLineasComentarios+=sizeof($arrEnterComentario);
							
							$totalLineas=$totalLineas>$totalLineasComentarios?$totalLineas:$totalLineasComentarios;
							
							$libro->setNegritas("A".$linea.":E".$linea);
							$libro->setFuente("A".$linea.":E".$linea,"Trebuchet MS");
							$libro->setAltoFila($linea,12+(12*$totalLineas));
							
							$libro->setVAlineacion("A".$linea.":E".$linea,"S");
							
							if($prog["porConfirmar"]==1)
							{
								$libro->setColorFondo("A".$linea.":E".$linea,$colorEventosConfirmar,"S");
							}
							
							$cambiarLetraComentario=true;
							if(sizeof($prog["colorFondo"])>0)
							{
								foreach($prog["colorFondo"] as $campos=>$valor)
								{
									if($campos=="comentariosAdicionales")
										$cambiarLetraComentario=false;
									$libro->setColorFondo($arrCamposCambio[$campos].$linea.":".$arrCamposCambio[$campos].$linea,"FF0000","S");
								}
							}
							if($cambiarLetraComentario)
								$libro->setColorLetra("E".$linea.":E".$linea,"FF0000","S");
							$libro->setCursivas("E".$linea.":E".$linea);							
							$libro->setColorLetra("A".$linea.":D".$linea,$prog["colorLetra"],"S");
							$linea++;
						}
						$linea++;
					}
					else
					{
						$linea++;
					}
					
					
					
					$fechaIPeriodo=strtotime("+1 days",$fechaIPeriodo);
				}//Fin While FechaIPeriodo
				
			}// fin de Foreach	
			
		}
		$libro->setColorFondo("A".$linea,"FF0000","S");
		$libro->setValor("B".$linea,"MODIFICACIONES");
		$linea++;
		
		$libro->setColorFondo("A".$linea,$colorEventosConfirmar,"S");
		$libro->setValor("B".$linea,"A CONFIRMAR");
		$linea++;
		$libro->obtenerHojaActiva()->getPageSetup()->setPrintArea('A1:E'.$linea);			
		$nombreLibro=generarNombreArchivoTemporal();
		$arrLibros[$idMasterPrincipal]=$baseDir."/archivosTemporales/".$nombreLibro.".xlsx";
		
		$libro->generarArchivoServidor("Excel2007",$arrLibros[$idMasterPrincipal]);

	}
	
	$libro2;
	$numLibro=1;
	foreach($arrLibros as $ruta)
	{
		if($numLibro==1)
		{
			$libro=new cExcel($ruta,true,"Excel2007");
		}
		else
		{
			$libro2=new cExcel($ruta,true,"Excel2007");
			$libro->libroExcel->addExternalSheet($libro2->obtenerHojaActiva());
		}
		$numLibro++;
		
	}
	
	$libro->cambiarHojaActiva(0);
	
	
	$consulta="SELECT id__593_tablaDinamica,clave,nombre FROM _593_tablaDinamica WHERE id__593_tablaDinamica in(".$idMaster.")";
	$fMaster=$con->obtenerPrimeraFila($consulta);
	$tituloInforme="Programación ".$fMaster[1]." Semana ".$fechasPeriodo[2];
	
	$nombreLibro=($tituloInforme).".xlsx";
	if($descargarDocumento)
	{
		
		$libro->generarArchivo($formato,$nombreLibro);
		foreach($arrLibros as $ruta)
		{
			unlink($ruta);
		}
	}
	else
	{
		$nLibro=generarNombreArchivoTemporal();
		$objDatosDocumento=array();
		if($formato=="PDF")
			$objDatosDocumento[0]=$baseDir."/archivosTemporales/".$nLibro.".pdf";
		else	
			$objDatosDocumento[0]=$baseDir."/archivosTemporales/".$nLibro.".xlsx";
			
		$libro->generarArchivoServidor($formato,$objDatosDocumento[0]);
		
		if($formato=="PDF")
			$objDatosDocumento[1]=str_replace(".xlsx",".pdf",$nombreLibro);
		else
			$objDatosDocumento[1]=$nombreLibro;
		$objDatosDocumento[2]=filesize($objDatosDocumento[0]);
		
		foreach($arrLibros as $ruta)
		{
			unlink($ruta);
		}
		return $objDatosDocumento;
	}
	
}

function generarDocumentoAgendaProgramacionSemanalMail($idSemana,$idMaster,$formato="PDF")
{
	$nombreArchivo=generarProgramacionSemanal($idSemana,$formato,false,$idMaster);
	return $nombreArchivo;
}

function generarDocumentoReporteProgramacionSemanalMail($idSemana,$idMaster,$formato="PDF")
{
	global $con;
	$consulta="SELECT VERSION-1 FROM 5001_situacionProgramacionSemanal WHERE idSemana=".$idSemana." AND idMaster=".$idMaster;
	$versionActual=$con->obtenerValor($consulta);
	
	$nombreArchivo=generarReporteProgramacionSemanal($idSemana,$formato,false,"3,4",$idMaster,$versionActual);
	return $nombreArchivo;
}

function dispararLiberacionProgramacionSemanal($idSemana,$idMaster)
{
	global $con;
	global $baseDir;
	
	$arrDocumentosEliminar=array();
	$consulta="SELECT etiqueta,noSemana,fechaInicio,fechaFin FROM 1050_semanasAnio WHERE idSemana=".$idSemana;
	
	$fSemana=$con->obtenerprimeraFila($consulta);
	$leyendaSemana=$fSemana[0];
	$consulta="SELECT mensajeEnvio FROM _608_tablaDinamica WHERE idReferencia=1";
	$mensajeEnvio=$con->obtenerValor($consulta);
	
	$arrAchivos=array();
	$consulta="SELECT documentoAdjunto FROM _608_gDocumentosAdjuntos WHERE idReferencia=1";
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		if(file_exists($baseDir."/documentosUsr/archivo_".$fila[0]))
		{
			$consulta="SELECT nomArchivoOriginal FROM 908_archivos WHERE idArchivo=".$fila[0];
			$oDocumento=array();
			$oDocumento[0]=$baseDir."/documentosUsr/archivo_".$fila[0];
			$oDocumento[1]=$con->obtenerValor($consulta);
			
			array_push($arrAchivos,$oDocumento);
		}
	}
	
	$consulta="SELECT funcionGeneradoraDocumento FROM _608_gFuncionesGeneradorasDocumentos WHERE idReferencia=1";
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		$consulta="SELECT nombreFuncionPHP FROM 9033_funcionesSistema WHERE idFuncion=".$fila[0];
		$nombreFuncionPHP=$con->obtenerValor($consulta);
		$documento=array();
		
		eval('$documento='.$nombreFuncionPHP.'('.$idSemana.','.$idMaster.');');
		array_push($arrAchivos,$documento);
		array_push($arrDocumentosEliminar,$documento[0]);
	}
	
	$consulta="SELECT nombre FROM _593_tablaDinamica WHERE id__593_tablaDinamica=".$idMaster;
	$leyendaMaster=$con->obtenerValor($consulta);
	
	$consulta="  SELECT idDestinatario FROM _608_gridDestinatarios WHERE idReferencia=1";
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		$arrParam=array();
		$arrParam["nombreDestinatario"]=obtenerNombreUsuario($fila[0]);
		$consulta="SELECT Mail FROM 805_mails WHERE idUsuario=".$fila[0];
		$arrParam["leyendaSemana"]=$leyendaSemana;
		$arrParam["master"]=$leyendaMaster;
		
		$listaCorreos=$con->obtenerListaValores($consulta);
		$arrCorreos=explode(",",$listaCorreos);
		foreach($arrCorreos as $c)
		{
			$arrParam["correoDestino"]=$c;		
			
			enviarMensajeEnvioDisparador($mensajeEnvio,$arrParam,$arrAchivos);
		}
		
	}
	foreach($arrDocumentosEliminar as $e)
	{
		unlink($e);
	}
}

function enviarMensajeEnvioDisparador($idMensaje,$arrParam,$arrAchivos)
{
	global $con;
	global $urlRepositorioDocumentos;
	
		
	$lParametros="";
	$consulta="SELECT parametro FROM 2012_parametrosMensajeEnvio WHERE idMensaje=".$idMensaje." ORDER BY orden";

	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		$valor="";
		if(isset($arrParam[$fila[0]]))
			$valor=$arrParam[$fila[0]];
		if($lParametros=="")
			$lParametros='"'.$fila[0].'":"'.cv($valor).'"';
		else
			$lParametros.=',"'.$fila[0].'":"'.cv($valor).'"';
	}
	
	$cadObj='{"p17":{'.$lParametros.'}}';
	
	$paramObj=json_decode($cadObj);

	$arrQueries=resolverQueries($idMensaje,10,$paramObj,true);
	
	$consulta="select * from 2011_mensajesEnvio WHERE idMensajeEnvio=".$idMensaje;
	$fMensaje=$con->obtenerPrimeraFila($consulta);
	$asunto=$fMensaje[6];
	$objValoresCuerpo=json_decode('{"registros":'.$fMensaje[7].'}');
	
	$consulta="SELECT cuerpoMensaje FROM 2013_cuerposMensajes WHERE idMensaje=".$idMensaje;
	$cuerpo=$con->obtenerValor($consulta);
	$cuerpo=str_replace("<strong>","<b>",$cuerpo);
	$cuerpo=str_replace("</strong>","</b>",$cuerpo);
	
	if(sizeof($objValoresCuerpo->registros)>0)
	{
		foreach($objValoresCuerpo->registros as $r)
		{
			$cadParametro=$r->lblVariable."||".$r->tVariable."||".$r->valor1."||".$r->valor2."||".$r->renderer;
			$valor=resolverParametroMensaje($cadParametro,$arrParam,$arrQueries);
			$cuerpo=str_replace($r->lblVariable,$valor,$cuerpo);
		}
	}
	
	
	enviarMail($arrParam["correoDestino"],$fMensaje[6],$cuerpo,"proyectos@grupolatis.net","",$arrAchivos,null);
	
	
	
	
}

function obtenerDatosSemana($idSemana,$idMaster,$tiposEmision,$version=-1)
{
	global $con;
	global $minutosDezplazamiento;
	
	$consulta="SELECT id__594_tablaDinamica,horaInicial FROM _594_tablaDinamica";
	$fConfiguracion=$con->obtenerPrimeraFila($consulta);
	
	$horaBase=strtotime("1984-05-10 00:00:00");
	$horaInicio=strtotime("1984-05-10 ".$fConfiguracion[1]);
	$minutosDezplazamiento=0;
	if($horaBase<>$horaInicio)
	{
	  $minutosDezplazamiento=obtenerDiferenciaMinutos(date("Y-m-d H:i:s",$horaBase),date("Y-m-d H:i:s",$horaInicio));
	}
	
	
	$consulta="SELECT etiqueta,noSemana,fechaInicio,fechaFin FROM 1050_semanasAnio WHERE idSemana=".$idSemana;
	$fSemana=$con->obtenerPrimeraFila($consulta);

	
	$fechaInicio=strtotime("+".$minutosDezplazamiento." minutes",strtotime($fSemana[2]));
	$fechaFin=strtotime("+7 days",$fechaInicio);
	
	
	
	
	$fInicio=date("Y-m-d H:i:s",$fechaInicio);
	$fechaFin=date("Y-m-d H:i:s",$fechaFin);
	
	/*if($_SESSION["idUsr"]==1)
	{
		echo $fInicio."-".$fechaFin."<br>";
	}*/
	
	$qAux=generarConsultaIntervalos($fInicio,$fechaFin,"horaInicio","horaFin",false);
	
	$consulta="SELECT * FROM 5000_programacionEventos WHERE idMaster=".$idMaster." and tipoEmision in(".$tiposEmision.") and ".$qAux." ORDER BY idMaster,horaInicio";
	
	if($version!=-1)
	{
		$consulta="SELECT * FROM 5001_programacionEventosRespaldoVersion WHERE idMaster=".$idMaster.
				" and tipoEmision in(".$tiposEmision.") and ".$qAux."  and version=".$version." ORDER BY idMaster,horaInicio";
		
	}
	
	$res=$con->obtenerFilas($consulta);
	
	$consulta="SELECT versionLiberacion FROM 5001_situacionProgramacionSemanal WHERE idSemana=".$idSemana." AND idMaster=".$idMaster;
	$versionLiberacion=$con->obtenerValor($consulta);
	
	$consulta="SELECT VERSION FROM 5001_situacionProgramacionSemanal WHERE idSemana=".$idSemana." AND idMaster=".$idMaster;
	$versionActual=$con->obtenerValor($consulta);
	if($version!=-1)
		$versionActual=$version;
	
	
	/*$consulta="SELECT VERSION-1 FROM 5001_bitacoraProgramacionSemanal WHERE idSemana= ".$idSemana."
			AND idMaster=".$idMaster." AND situacionActual=3 and VERSION<".$versionActual."  ORDER BY fechaCambio DESC";
	$versionComparacion=$con->obtenerValor($consulta);*/
	
	$versionComparacion=$versionActual-1;
	if($versionComparacion<1)
		$versionComparacion=1;
	
	if($versionLiberacion==$versionActual)
	{
		$versionComparacion=$versionActual;
	}
		
	
	$arrDatos1=Array();
	while($fila=mysql_fetch_assoc($res))
	{
		if($version!=-1)
		{
			$fila["idRegistro"]=$fila["idRegistroEvento"];
		}
		$idRegistro=$fila["idRegistro"];
		$horaIni=$fila["horaInicio"];
		$horaFin=$fila["horaFin"];
		$idMaster=$fila["idMaster"];
		$idPrograma=$fila["idPrograma"];
		$cveTransmision=$fila["cveTransmision"];
		$detalleEvento=$fila["detalleEvento"];
		$idTipoEmision=$fila["tipoEmision"];
		$porConfirmar=$fila["porConfirmar"];
		$eventoEspecial=$fila["eventoEspecial"];
		$comentarios=$fila["comentariosAdicionales"];
		$diaLetra=obtenerDiaLetraFecha($horaIni);
		$fechaInicial=date("Y-m-d",strtotime($horaIni));
		$horaInicial=obtenerHora($horaIni);
		$horaFinal=obtenerHora($horaFin);
		$datosPrograma=obtenerNombrePrograma($idPrograma);
		$clavePrograma=$datosPrograma["clave"];
		$nombrePrograma=$datosPrograma["nombrePro"];
		$paisOrigen=$datosPrograma["paisOrigen"];
		$nombreTipoEmision=obtenerTipoEmision($idTipoEmision);
		$llaveVersion="";
		
		$arrCamposBase=array();
		$arrCamposBase["horaInicio"]=$fila["horaInicio"];
		$arrCamposBase["horaFin"]=$fila["horaFin"];
		$arrCamposBase["idPrograma"]=$fila["idPrograma"];
		$arrCamposBase["tipoEmision"]=$fila["tipoEmision"];
		$arrCamposBase["paisOrigen"]=$datosPrograma["paisOrigen"];
		$arrCamposBase["comentariosAdicionales"]=$fila["comentariosAdicionales"];
		
		$arrCamposRespaldo=NULL;
		
		if($versionComparacion>=1)
		{
			$consulta="SELECT * FROM 5001_programacionEventosRespaldoVersion WHERE idRegistroEvento=".$fila["idRegistro"]." AND VERSION=".$versionComparacion;
			$filaV=$con->obtenerPrimeraFilaAsoc($consulta);
			if($filaV)
			{
				$datosProgramaVersion=obtenerNombrePrograma($filaV["idPrograma"]);
				$arrCamposRespaldo=array();
				$arrCamposRespaldo["horaInicio"]=$filaV["horaInicio"];
				$arrCamposRespaldo["horaFin"]=$filaV["horaFin"];
				$arrCamposRespaldo["idPrograma"]=$filaV["idPrograma"];
				$arrCamposRespaldo["tipoEmision"]=$filaV["tipoEmision"];
				$arrCamposRespaldo["paisOrigen"]=$datosPrograma["paisOrigen"];
				$arrCamposRespaldo["comentariosAdicionales"]=$filaV["comentariosAdicionales"];
			}
		}
		
		
		if(!isset($arrDatos1[$idMaster][$fechaInicial]))
			$arrDatos1[$idMaster][$fechaInicial]=array();

			$obj["idRegistro"]=$idRegistro;
			$obj["fechaIni"]=$fechaInicial;
			$obj["horaIni"]=$horaIni;
			$obj["horaInicial"]=$horaInicial;
			$obj["horaFin"]=$horaFin;
			$obj["horaFinal"]=$horaFinal;
			$obj["idPrograma"]=$idPrograma;
			$obj["nombrePrograma"]=$nombrePrograma;
			if($detalleEvento!="")
				$obj["nombrePrograma"].=" ".$detalleEvento;
			
			$obj["paisOrigen"]=$paisOrigen;
			$obj["comentarios"]=$comentarios;
			$obj["tipoEmision"]=$nombreTipoEmision;
			$obj["diaLetra"]=$diaLetra;
			$obj["colorFondo"]=array();
			$obj["colorLetra"]="000000";
			$obj["porConfirmar"]=$porConfirmar;
			
			if($versionActual!=$versionComparacion)
			{
				foreach($arrCamposBase as $campo=>$valor)
				{
					if($arrCamposRespaldo==NULL)
						$obj["colorFondo"][$campo]=1;
					else
					{
						if($valor!=$arrCamposRespaldo[$campo])
						{
							$obj["colorFondo"][$campo]=1;
						}
					}
				}
			}
			array_push($arrDatos1[$idMaster][$fechaInicial],$obj);
		
	}
	
	return $arrDatos1;
	
}

function fechaTexto($fecha)
{
	//Fecha letra con año
	$fecha = substr($fecha, 0, 10);
  	$numeroDia = date('d', strtotime($fecha));
	$dia = date('l', strtotime($fecha));
	$mes = date('F', strtotime($fecha));
	$anio = date('Y', strtotime($fecha));
	$dias_ES = array("Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado", "Domingo");
	$dias_EN = array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday");
	$nombredia = str_replace($dias_EN, $dias_ES, $dia);
	$meses_ES = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
	$meses_EN = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
	$nombreMes = str_replace($meses_EN, $meses_ES, $mes);
	
	//return $nombredia." ".$numeroDia." de ".$nombreMes." de ".$anio;
	return $nombredia." ".$numeroDia." de ".$nombreMes;
}

function obtenerDiaLetraFecha($fecha)
{
	global $arrDiasSemana;
	$dteFecha=strtotime($fecha);
	
	
	$fecha = utf8_encode($arrDiasSemana[date("w",$dteFecha)])." ".date("d",$dteFecha);
	
	return $fecha;	
}

function obtenerNumeroSemanaFox($idSemana)
{
	global $con;
	
	$consulta="SELECT noSemana FROM 1050_semanasAnio WHERE idSemana='".$idSemana."'";
	$numSemana=$con->obtenerValor($consulta);
	
	return $numSemana;
}

function obtenerNombreMaster($idMaster)
{
	global $con;
	
	$consulta="SELECT clave FROM _593_tablaDinamica WHERE id__593_tablaDinamica='".$idMaster."'";
	$clave=$con->obtenerValor($consulta);
	return $clave;
}

function obtenerPeriodoSemana($idSemana)
{
	global $con;
	
	$consulta="SELECT fechaInicio,fechaFin FROM 1050_semanasAnio WHERE idSemana='".$idSemana."'";
	$fechas=$con->obtenerPrimeraFila($consulta);
	
	$fechaI=$fechas[0];
	$fechaF=$fechas[1];
	
	$fI=fechaTexto($fechaI);
	$fF=fechaTexto($fechaF);
	
	$nombreFecha=$fI." - ".$fF;
	return $nombreFecha;
	
}

function obtenerHora($fecha)
{
	$fecha = substr($fecha, 10, 18);
		$hora=date("H:i",strtotime($fecha));
		return $hora;
}

function obtenerNombrePrograma($idPrograma)
{
	global $con;
	
	$obj=array();
	$consulta="SELECT clavePrograma,nombrePrograma,pa.paisOrigen FROM _589_tablaDinamica p,_584_tablaDinamica pa 
				WHERE p.paisOrigen=pa.id__584_tablaDinamica AND id__589_tablaDinamica='".$idPrograma."'";
	$res=$con->obtenerPrimeraFila($consulta);
	
	$obj["clave"]=$res[0];
	$obj["nombrePro"]=strtoupper($res[1]);
	$obj["paisOrigen"]=mb_strtoupper($res[2],'UTF-8');
	
	return $obj;
				
}

function obtenerTipoEmision($idTipoEmision)
{
	global $con;
	
	$consulta="SELECT tipoEmision FROM _586_tablaDinamica WHERE id__586_tablaDinamica='".$idTipoEmision."'";
	$emision=$con->obtenerValor($consulta);
	
	return mb_strtoupper($emision,'UTF-8');
	
}

function generarDocumentoCotizacion($idFormulario,$idRegistro,$formato="PDF")
{
	global $con;
	global $baseDir;
	$libro=new cExcel($baseDir."/modulosEspeciales_FOX/plantilla/plantillaViaticos.xlsx",true,"Excel2007");	
	$consulta="SELECT * FROM _597_tablaDinamica WHERE id__597_tablaDinamica=".$idRegistro;
	$fSolicitudViaticos=$con->obtenerPrimeraFilaAsoc($consulta);
	$idOrdenServicio=$fSolicitudViaticos["idReferencia"];
	
	$lblAsignacionViaticos="";
	switch($fSolicitudViaticos["asignacionViaticos"])
	{
		case 0:
			$lblAsignacionViaticos="NO se asignan viáticos";
		break;
		case 1:
			$lblAsignacionViaticos="Con viáticos (MXN)";
		break;
		case 2:
			$lblAsignacionViaticos="Con viáticos (USD)";
		break;
	}
	
	$consulta="SELECT idProgramacion FROM _602_tablaDinamica WHERE id__602_tablaDinamica=".$idOrdenServicio;
	$idProgramacion=$con->obtenerValor($consulta);
	
	$consulta="SELECT detalleEvento,horaInicio,comentariosAdicionales,idPrograma FROM 5000_programacionEventos WHERE idRegistro=".$idProgramacion;
	$fProgramacion=$con->obtenerPrimeraFilaAsoc($consulta);
	
	
	$consulta="SELECT clavePrograma,nombrePrograma FROM _589_tablaDinamica WHERE id__589_tablaDinamica=".$fProgramacion["idPrograma"];
	$fPrograma=$con->obtenerPrimeraFila($consulta);
	
	$libro->setValor("B6","[".$fPrograma[0]."] ".$fPrograma[1]);
	$libro->setValor("B7",date("d/m/Y",strtotime($fProgramacion["horaInicio"])));
	$libro->setValor("B8",date("H:i",strtotime($fProgramacion["horaInicio"])));
	$libro->setValor("B9",str_replace("<br />","\n",($fProgramacion["detalleEvento"]==""?"(Sin detalles)":$fProgramacion["detalleEvento"])));
	$libro->setValor("B10",obtenerNombreUsuario($fSolicitudViaticos["responsable"]));
	$libro->setValor("B11",str_replace("<br />","\n",($fProgramacion["comentariosAdicionales"]==""?"(Sin Comentarios)":$fProgramacion["comentariosAdicionales"])));
	$libro->setValor("B12",$lblAsignacionViaticos);
	
	$totalViaticosEquipo=0;
	$numFila=16;
	$consulta="SELECT nombreEquipo,numDias,(SELECT COUNT(*) FROM _597_personalEquipoViaticos WHERE idEquipo=e.idRegistro) AS totalPersonas ,
				(SELECT GROUP_CONCAT(u.Nombre) FROM _597_personalEquipoViaticos pV,800_usuarios u WHERE idEquipo=e.idRegistro
				AND u.idUsuario=pV.idPersonal ORDER BY u.Nombre) AS listadoPersonas 
				FROM _597_equiposViaticos e WHERE idFormulario=".$idFormulario." AND idReferencia=".$idRegistro;
	$res=$con->obtenerFilas($consulta);
	$libro->insertarFila($numFila,$con->filasAfectadas);
	
	while($fila=mysql_fetch_row($res))
	{
		$libro->setValor("A".$numFila,$fila[0]);
		$libro->setValor("B".$numFila,$fila[3]);
		
		if($fSolicitudViaticos["asignacionViaticos"]==0)
		{
			$viaticosPersona="N/A";
			$viaticosEquipo="N/A";
			$libro->setValor("F".$numFila,"N/A");
			$libro->setValor("G".$numFila,"N/A");	
		}
		else
		{
			$viaticosPersona=($fSolicitudViaticos["asignacionViaticos"]==1?$fSolicitudViaticos["costoBaseViaticosPesos"]:$fSolicitudViaticos["costoBaseViaticosDolar"])*$fila[1];
			$viaticosEquipo=$viaticosPersona*$fila[2];
			$totalViaticosEquipo+=$viaticosEquipo;
			$libro->setValor("F".$numFila,"$ ".number_format($viaticosPersona,2).($fSolicitudViaticos["asignacionViaticos"]==1?" MXN":" USD"));
			$libro->setValor("G".$numFila,"$ ".number_format($viaticosEquipo,2).($fSolicitudViaticos["asignacionViaticos"]==1?" MXN":" USD"));	
		}
		$libro->combinarCelda("B".$numFila,"E".$numFila);
		$libro->setHAlineacion("B".$numFila,"I");
			
		$numFila++;
		
	}
	
	if($fSolicitudViaticos["asignacionViaticos"]==0)
		$libro->setValor("G".$numFila,"N/A");
	else
		$libro->setValor("G".$numFila,"$ ".number_format($totalViaticosEquipo,2).($fSolicitudViaticos["asignacionViaticos"]==1?" MXN":" USD"));
	
	
	$totalP=0;
	$totalD=0;
	$numFila+=6;
	$consulta="SELECT id__621_tablaDinamica,nombreConcepto,totalP,totalD FROM _597_cotizacionConceptos cc,_621_tablaDinamica c 
				WHERE cc.idFormulario=".$idFormulario." AND cc.idReferencia=".$idRegistro." AND c.id__621_tablaDinamica=cc.idConcepto AND (totalD+totalP)>0 ORDER BY prioridad";
	$res=$con->obtenerFilas($consulta);
	$libro->insertarFila($numFila,$con->filasAfectadas);
	
	while($fila=mysql_fetch_row($res))
	{
		$totalP+=$fila[2];
		$totalD+=$fila[3];
		$libro->setValor("A".$numFila,$fila[1]);
		$libro->combinarCelda("A".$numFila,"C".$numFila);
		$libro->setHAlineacion("A".$numFila,"I");
		$libro->setValor("D".$numFila,$fila[2]);
		$libro->setValor("E".$numFila,$fila[3]);
		$libro->setFormatoCelda("D".$numFila.":E".$numFila,PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
		$numFila++;
	}
	$libro->setValor("D".$numFila,$totalP);
	$libro->setValor("E".$numFila,$totalD);
	$libro->setFormatoCelda("D".$numFila.":E".$numFila,PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
	$libro->removerFila(15,1);
	$libro->removerFila($numFila-6,1);
	$nLibro=generarNombreArchivoTemporal();
	$objDatosDocumento=array();
	$objDatosDocumento[0]=$baseDir."/archivosTemporales/".$nLibro.".pdf";

	$libro->generarArchivoServidor($formato,$objDatosDocumento[0]);
	$objDatosDocumento[1]=$baseDir."/archivosTemporales/".$nLibro;
	
	
	return $objDatosDocumento;
}

function generarDocumentoCotizacionRenta($idFormulario,$idRegistro,$formato="PDF")
{
	global $con;
	global $baseDir;
	$libro=new cExcel($baseDir."/modulosEspeciales_FOX/plantilla/plantillaRentaServicio.xlsx",true,"Excel2007");	
	$consulta="SELECT * FROM _623_tablaDinamica WHERE id__623_tablaDinamica=".$idRegistro;
	$fSolicitud=$con->obtenerPrimeraFilaAsoc($consulta);
	$idOrdenServicio=$fSolicitud["idReferencia"];
	
	$consulta="SELECT idProgramacion FROM _602_tablaDinamica WHERE id__602_tablaDinamica=".$idOrdenServicio;
	$idProgramacion=$con->obtenerValor($consulta);
	
	$consulta="SELECT detalleEvento,horaInicio,comentariosAdicionales,idPrograma FROM 5000_programacionEventos WHERE idRegistro=".$idProgramacion;
	$fProgramacion=$con->obtenerPrimeraFilaAsoc($consulta);
	
	
	$consulta="SELECT clavePrograma,nombrePrograma FROM _589_tablaDinamica WHERE id__589_tablaDinamica=".$fProgramacion["idPrograma"];
	$fPrograma=$con->obtenerPrimeraFila($consulta);
	
	$libro->setValor("B6","[".$fPrograma[0]."] ".$fPrograma[1]);
	$libro->setValor("B7",date("d/m/Y",strtotime($fProgramacion["horaInicio"])));
	$libro->setValor("B8",date("H:i",strtotime($fProgramacion["horaInicio"])));
	$libro->setValor("B9",str_replace("<br />","\n",($fProgramacion["detalleEvento"]==""?"(Sin detalles)":$fProgramacion["detalleEvento"])));
	$libro->setValor("B10",str_replace("<br />","\n",($fProgramacion["comentariosAdicionales"]==""?"(Sin Comentarios)":$fProgramacion["comentariosAdicionales"])));
	
	$consulta="SELECT catRecurso FROM _585_tablaDinamica WHERE id__585_tablaDinamica=".$fSolicitud["conceptoContratacion"];
	$servicioContratacion=$con->obtenerValor($consulta);
	$libro->setValor("B12",$servicioContratacion);
	$libro->setValor("B13",obtenerNombreUsuario($fSolicitud["responsable"]));
	$libro->setValor("B14","Del ".date("d/m/Y",strtotime($fSolicitud["fechaInicio"]))." al ".date("d/m/Y",strtotime($fSolicitud["fechaFin"])));
	$libro->setValor("B15",$fSolicitud["detalleServicio"]);
	
	$consulta="SELECT cotizaciones,comentariosAdicionales FROM _626_tablaDinamica WHERE idReferencia=".$idRegistro;
	$fPropuestaSelFinal=$con->obtenerPrimeraFila($consulta);
	
	$numFila=21;
	$consulta="SELECT pr.proveedor,descripcionServicio,costoServicio,monedaCotizacion,comentariosAdicionales 
				FROM _624_tablaDinamica c,625_vistaProveedores pr WHERE c.idReferencia=".$idRegistro." AND pr.id__625_tablaDinamica=c.proveedor";

	$resCotizaciones=$con->obtenerFilas($consulta);
	$libro->insertarFila($numFila,$con->filasAfectadas-1);
	$numFila=20;
	while($fila=mysql_fetch_row($resCotizaciones))
	{
		$libro->setValor("A".$numFila,$fila[0]);
		$libro->setValor("D".$numFila,$fila[2]);
		$libro->setValor("E".$numFila,"MXN");//$fila[3]==1?"MXN":"USD"
		$libro->setValor("F".$numFila,str_replace("<br />","\r",$fila[4]));
		$libro->combinarCelda("A".$numFila,"C".$numFila);
		$libro->setHAlineacion("A".$numFila,"I");
		
		$libro->combinarCelda("F".$numFila,"G".$numFila);
		$libro->setHAlineacion("F".$numFila,"I");
		$libro->setFormatoCelda("D".$numFila,PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
		
		$numFila++;
		
	}
	
	$numFila+=4;
	
	
	
	$consulta="	SELECT pr.proveedor,descripcionServicio,costoServicio,monedaCotizacion,comentariosAdicionales 
				FROM _624_tablaDinamica c,625_vistaProveedores pr WHERE c.id__624_tablaDinamica=".$fPropuestaSelFinal[0].
				" AND pr.id__625_tablaDinamica=c.proveedor";
	$fPropuestaSel=$con->obtenerPrimeraFilaAsoc($consulta);

	$libro->setValor("A".$numFila,$fPropuestaSel["proveedor"]);
	$libro->setValor("D".$numFila,$fPropuestaSel["costoServicio"]);
	$libro->setValor("E".$numFila,"MXN");//$fPropuestaSel["monedaCotizacion"]==1?"MXN":"USD"
	$libro->setValor("F".$numFila,str_replace("<br />","\r",$fPropuestaSelFinal[1]));

	
	$libro->setFormatoCelda("D".$numFila,PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
	//$libro->removerFila($numFila-6-$con->filasAfectadas,1);
	
	$nLibro=generarNombreArchivoTemporal();
	$objDatosDocumento=array();
	$objDatosDocumento[0]=$baseDir."/archivosTemporales/".$nLibro.".pdf";
	$libro->obtenerHojaActiva()->getPageSetup()->setPrintArea('A1:G:'.$numFila);
	$libro->generarArchivoServidor($formato,$objDatosDocumento[0]);
	$objDatosDocumento[1]=$baseDir."/archivosTemporales/".$nLibro;
	
	return $objDatosDocumento;
	
	//$libro->obtenerHojaActiva()->getPageSetup()->setPrintArea('A1:G:'.$numFila);
	//$libro->generarArchivoServidor("PDF",$baseDir."/archivosTemporales/prueba.xlsx");
}

function generarDocumentoAdjuntoCotizacionRenta($idFormulario,$idRegistro)
{
	global $con;
	$arrDocumento=generarDocumentoCotizacionRenta($idFormulario,$idRegistro);

	$datosArchivo=explode("/",$arrDocumento[0]);
	$idDocumento=registrarDocumentoServidorRepositorio($datosArchivo[sizeof($datosArchivo)-1],"cotizacionServicio.pdf",1,"");
	
	if($idDocumento!=-1)
	{
		if(registrarDocumentoReferenciaProceso($idFormulario,$idRegistro,$idDocumento))
		{
			
			return true;
		}
	}
}

function generarDocumentoAdjuntoCotizacionViatico($idFormulario,$idRegistro)
{
	global $con;
	$arrDocumento=generarDocumentoCotizacion($idFormulario,$idRegistro);

	$datosArchivo=explode("/",$arrDocumento[0]);
	$idDocumento=registrarDocumentoServidorRepositorio($datosArchivo[sizeof($datosArchivo)-1],"cotizacionViaticos.pdf",1,"");
	
	if($idDocumento!=-1)
	{
		if(registrarDocumentoReferenciaProceso($idFormulario,$idRegistro,$idDocumento))
		{
			
			return true;
		}
	}
}

function obtenerConfiguracionInformeAgenda($duracion,$tipoInforme)
{
	global $arrConfiguracionHorario;
	$totalConf=sizeof($arrConfiguracionHorario);
	$configuracionHorario=$arrConfiguracionHorario[$tipoInforme];
	foreach( $configuracionHorario as $tiempo=>$resto)
	{
		if($tiempo>=$duracion)
			return $resto;	
	}

	
}


function generarLlamadoTalentos($idSemana,$formato="PDF")
{
	global $con;
	global $arrMesLetra;
	global $arrDiasSemana;
	global $baseDir ;
	
	$consulta="SELECT * FROM 1050_semanasAnio WHERE idSemana=".$idSemana;
	$fSemana=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$consulta="SELECT VERSION FROM 5005_versionesReportes WHERE tipoReporte=2 AND idSemana=".$idSemana." ORDER BY fechaRegistro DESC";
	
	$version=$con->obtenerValor($consulta);
	if($version=="")
		$version=1;
		
	$arrFechasProduccion=array();
	
	$fInicio=strtotime($fSemana["fechaInicio"]);
	$fFin=strtotime($fSemana["fechaFin"]);
	while($fInicio<=$fFin)
	{
		$arrFechasProduccion[date("Y-m-d",$fInicio)]=array();
		$consulta="SELECT id__581_tablaDinamica,nombreFabrica FROM _581_tablaDinamica ORDER BY nombreFabrica";
		$rFabricas=$con->obtenerFilas($consulta);
		while($fFabrica=mysql_fetch_row($rFabricas))
		{
			$arrFechasProduccion[date("Y-m-d",$fInicio)][$fFabrica[1]]=array();
		}
		$fInicio=strtotime("+1 days",$fInicio);
	}
	
	$maxLlamado=0;
	
	$arrLlamados=array();
	
	$consulta="SELECT oT.*,fP.fechaProduccion FROM _628_tablaDinamica fP,_602_tablaDinamica oT 
				WHERE fechaProduccion>='".$fSemana["fechaInicio"]."' AND fechaProduccion<='".$fSemana["fechaFin"]."'
				AND oT.id__602_tablaDinamica=fP.idReferencia AND folioBase IS NOT NULL order by fechaProduccion";
	
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_assoc($res))
	{
		$totalLlamados=0;
		
			
		$consulta="SELECT * FROM _602_recursosOrdenServicios WHERE idFormulario=602 and idReferencia=".$fila["id__602_tablaDinamica"].
					" AND tipoRecurso=2 AND idRecurso=19";	
		$resTalestos=$con->obtenerFilas($consulta);
		if($con->filasAfectadas>0)
		{
			if(!isset($arrLlamados[$fila["id__602_tablaDinamica"]]))
				$arrLlamados[$fila["id__602_tablaDinamica"]]=array();
		}
		else
		{
			continue;
		}
		
		while($fTalento=mysql_fetch_assoc($resTalestos))
		{
			array_push($arrLlamados[$fila["id__602_tablaDinamica"]],$fTalento);
			$totalLlamados++;
		}
		
		if($maxLlamado<$totalLlamados)
			$maxLlamado=$totalLlamados;
		
		
		
		$consulta="SELECT fabrica FROM _589_tablaDinamica WHERE id__589_tablaDinamica=".$fila["nombrePrograma"];
		$idFabrica=$con->obtenerValor($consulta);
		$consulta="SELECT nombreFabrica FROM _581_tablaDinamica WHERE id__581_tablaDinamica=".$idFabrica;
		$lblFabrica=$con->obtenerValor($consulta);
		
		if(!isset($arrFechasProduccion[$fila["fechaProduccion"]][$lblFabrica][$fila["id__602_tablaDinamica"]]))
			$arrFechasProduccion[$fila["fechaProduccion"]][$lblFabrica][$fila["id__602_tablaDinamica"]]=array();
			
		array_push($arrFechasProduccion[$fila["fechaProduccion"]][$lblFabrica][$fila["id__602_tablaDinamica"]],$arrLlamados[$fila["id__602_tablaDinamica"]]);
			
	}
	
	$arrValoresTalentosVacios="";
	$arrCamposTalentos="";
	$arrColumnasLlamado="";
	for($t=1;$t<=$maxLlamado;$t++)
	{
		$arrColumnasLlamado.=",{header:\"Talento ".$t."\",width:190,sortable:true,dataIndex:\"talento_".$t."\",renderer:formatearTalento}";			
		$arrCamposTalentos.=",{name:\"talento_".$t."\"},{name:\"aplicaTalento_".$t."\"}";
		$arrValoresTalentosVacios.=",\"talento_".$t."\":\"\",\"aplicaTalento_".$t."\":\"0\"";
	}
	
	
	$numReg=0;
	$arrRegistros='';
	
	
	$fabrica="";
	$fechaProduccionActual="";
	
	
	foreach($arrFechasProduccion as $fechaProduccion=>$resto)
	{
		if($fechaProduccion!=$fechaProduccionActual)
		{
			$dteFechaEvento=strtotime($fechaProduccion);
			$lblPrograma=utf8_encode($arrDiasSemana[(date("w",$dteFechaEvento)*1)]." ".date("d",$dteFechaEvento).
						" de ".$arrMesLetra[(date("m",$dteFechaEvento)*1)-1]);
			$fechaProduccionActual=$fechaProduccion;
			$o='{"idFormulario":"-1","idRegistro":"-1","idServicio":"-1","programa":"'.$lblPrograma.'","fecha":"'.$fechaProduccion.'",'.
				'"horaIn":"","horaOut":"","master":"","editable":"0","procesoProduccion":"","comentariosAdicionales":"'.
				'","tipoFila":"1","colorFondo":"002060","situacionOT":"","comentariosAdicionalesLlamado":"","totalTalentos":"0"'.$arrValoresTalentosVacios.'}';
			if($arrRegistros=="")
				$arrRegistros=$o;
			else
				$arrRegistros.=",".$o;
			$numReg++;
			$fabrica="";
		}
		

		foreach($resto as $fabricas=>$arrProgramas)
		{
			if(sizeof($arrProgramas)>0)
			{
				$lblPrograma=$fabricas;
				$o='{"idFormulario":"-1","idRegistro":"-1","idServicio":"-1","programa":"'.$lblPrograma.'","fecha":"'.$fechaProduccion.'",'.
				'"horaIn":"","horaOut":"","master":"","editable":"0","colorFondo":"FF0000","tipoFila":"2","procesoProduccion":"",'.
				'"comentariosAdicionales":"","situacionOT":"","comentariosAdicionalesLlamado":"","totalTalentos":"0"'.$arrValoresTalentosVacios.'}';
				if($arrRegistros=="")
					$arrRegistros=$o;
				else
					$arrRegistros.=",".$o;
				$numReg++;
			}
			else
				continue;

			foreach($arrProgramas as $idPrograma=>$arrOrdenes)
			{

				$consulta="SELECT oT.*,cP.nombrePrograma as lblNombrePrograma FROM _602_tablaDinamica oT,_589_tablaDinamica cP WHERE id__602_tablaDinamica=".$idPrograma." AND cP.id__589_tablaDinamica=oT.nombrePrograma";
				$filaOT=$con->obtenerPrimeraFilaAsoc($consulta);
				
				
				$arrValoresTalentos="";
				
				for($t=1;$t<=$maxLlamado;$t++)
				{
					$valor="";
					
					if(isset($arrOrdenes[0][$t-1]))
					{

						$valor=$arrOrdenes[0][$t-1]["idAsignacion"];
					}
					
					$arrValoresTalentos.=",\"talento_".$t."\":\"".$valor."\",\"aplicaTalento_".$t."\":\"".($valor!=""?1:0)."\"";
				}
				
				$consulta="SELECT * FROM _628_tablaDinamica WHERE idReferencia=".$idPrograma;
				$fProduccion=$con->obtenerPrimeraFilaAsoc($consulta);
				
				$consulta="SELECT * FROM _602_llamadosTalentos WHERE idOrdenTrabajo=".$idPrograma;
				$fRegistroLlamadoTalento=$con->obtenerPrimeraFilaAsoc($consulta);
				
				$lblPrograma=$filaOT["lblNombrePrograma"];
				$o='{"idFormulario":"602","idRegistro":"'.$idPrograma.'","idServicio":"'.$idPrograma.'","programa":"'.cv($lblPrograma).'","fecha":"'.$fechaProduccion.'",'.
				'"horaIn":"'.$fProduccion["fechaProduccion"].' '.$fProduccion["horaProduccion"].'","horaOut":"'.$fProduccion["horaEstimadaTermino"].
				'","procesoProduccion":"'.$fProduccion["procesoProduccion"].'","comentariosAdicionales":"'.cv($fProduccion["comentariosAdicionales"]).
				'","master":"'.$filaOT["idMaster"].'","editable":"1","tipoFila":"3","situacionOT":"'.$filaOT["idEstado"].
				'","colorFondo":"FF0000","comentariosAdicionalesLlamado":"'.cv($fRegistroLlamadoTalento["comentariosAdicionales"]).'","totalTalentos":"'.
				$totalLlamados.'"'.$arrValoresTalentos.'}';
				
				if($arrRegistros=="")
					$arrRegistros=$o;
				else
					$arrRegistros.=",".$o;
				$numReg++;

			}
		}
	}
	
	$ruta=$baseDir."/reportes/plantillas/plantillaLlamadosTalento.xlsx";
	$libro=new cExcel($ruta,true,"Excel2007");
	
	$objRichText = new PHPExcel_RichText();
	$objRichText->createText("");
	
	$lblSemana=str_replace("Semana","",$fSemana["etiqueta"]);
	$objAux=$objRichText->createTextRun("Semana: ");
	$phpColor = new PHPExcel_Style_Color();
	$phpColor->setRGB('000000');  
	$objAux->getFont()->setColor($phpColor);
	$objAux->getFont()->setBold(true);
	$objAux->getFont()->setSize(16);
	
	$objAux=$objRichText->createTextRun($lblSemana);
	$phpColor = new PHPExcel_Style_Color();
	$phpColor->setRGB('000000');  
	$objAux->getFont()->setColor($phpColor);
	$objAux->getFont()->setBold(false);
	$objAux->getFont()->setSize(16);
	
	$libro->setValor("A3",$objRichText);

	$numFila=5;
	$cadObj='{"registros":['.$arrRegistros.']}';
	$oRegistro=json_decode($cadObj);
	$listaTalentos="";
	foreach($oRegistro->registros as $r)
	{
		$listaTalentos="";
		switch($r->tipoFila)
		{
			case 1:
				$libro->combinarCelda("A".$numFila,"E".$numFila);
				$libro->setColorFondo("A".$numFila,"002060");
				$libro->setColorFuente("A".$numFila,"FFFFFF");
				$libro->setNegritas("A".$numFila);
				$libro->setValor("A".$numFila,$r->programa);
				$libro->setHAlineacion("A".$numFila,"C");
				$libro->setTamano("A".$numFila,14);
				$libro->setBorde("A".$numFila.":E".$numFila,"DE");
			break;
			case 2:
				$libro->combinarCelda("A".$numFila,"E".$numFila);
				$libro->setColorFondo("A".$numFila,"FF0000");
				$libro->setColorFuente("A".$numFila,"FFFFFF");
				$libro->setNegritas("A".$numFila);
				$libro->setValor("A".$numFila,$r->programa);
				$libro->setHAlineacion("A".$numFila,"I");
				$libro->setTamano("A".$numFila,12);
				$libro->setBorde("A".$numFila.":E".$numFila,"DE");
			break;
			case 3:
				$altoTotal=0;
				$objRichText = new PHPExcel_RichText();
				$objRichText->createText("");
				$consulta="SELECT * FROM _628_tablaDinamica WHERE idReferencia=".$r->idRegistro;
				$fProduccion=$con->obtenerPrimeraFilaAsoc($consulta);
				
				$consulta="SELECT * FROM _602_llamadosTalentos WHERE idOrdenTrabajo=".$r->idRegistro;
				$fRegistroLlamadoTalento=$con->obtenerPrimeraFilaAsoc($consulta);
				$comentarios=$fRegistroLlamadoTalento["comentariosAdicionales"];
				$comentarios=str_replace("<br />","\n",$comentarios);
				$hInicio=strtotime($fProduccion["horaProduccion"]);
				$hFin=strtotime($fProduccion["horaEstimadaTermino"]);
				$lblHorario=date("H:i",$hInicio)." A ".date("H:i",$hFin);
				if($fProduccion["procesoProduccion"]==2)
				{
					$lblHorario.="\nGRABADO";
					
				}
				else
				{
					$lblHorario.="\nVIVO";
				}
				$altoTotal=1;	
				$idTalento="";	
				$listaTalento="";
				
				$arrTalentos=array();
				
				for($nTalento=1;$nTalento<=$maxLlamado;$nTalento++)
				{
					
					eval('$idTalento=$r->talento_'.$nTalento.";");
					
					
					if(($idTalento!="")&&($idTalento!="-1"))
					{
						if($listaTalentos=="")
							$listaTalentos=$idTalento;
						else
							$listaTalentos.=",".$idTalento;
						array_push($arrTalentos,obtenerNombreUsuario($idTalento));
						
					}
				}
				
				
				
				
				$lblTalento="";
				$totalTalentos=sizeof($arrTalentos);
				for($nTalento=1;$nTalento<=$totalTalentos;$nTalento++)
				{
					if($nTalento>1)
					{
						$texto=", ";
						if($nTalento==$totalTalentos)
							$texto=" y ";
						$objAux=$objRichText->createTextRun($texto);
						$phpColor = new PHPExcel_Style_Color();
						$phpColor->setRGB('000000');  
						$objAux->getFont()->setColor($phpColor);
						$lblTalento.=$texto;
					}
					$objAux=$objRichText->createTextRun(trim($arrTalentos[$nTalento-1]));
					$phpColor = new PHPExcel_Style_Color();
					$phpColor->setRGB('000000');  
					$objAux->getFont()->setColor($phpColor);
					$lblTalento.=trim($arrTalentos[$nTalento-1]);
				
				}
				$master="";
				$consulta="SELECT nombre FROM _593_tablaDinamica WHERE id__593_tablaDinamica=".$r->master;
				$master=$con->obtenerValor($consulta);
				$libro->setValor("A".$numFila,formatearCampoMultilinea($r->programa));				
				
				$totalLineasPrograma=calcularNumeroLineas($r->programa,39);
				
				$libro->setValor("B".$numFila,$lblHorario);
				
				$libro->setHAlineacion("B".$numFila,"C");
				$libro->setValor("C".$numFila,$master);
				
				$libro->setValor("D".$numFila,$objRichText);				
				
				$totalLineasTalentos=calcularNumeroLineas($lblTalento,65);
				
				$libro->setValor("E".$numFila,formatearCampoMultilinea($comentarios));				
								
				$totalLineasComentarios=calcularNumeroLineas($comentarios,65);
				
				if($altoTotal<$totalLineasPrograma)
					$altoTotal=$totalLineasPrograma;
				if($totalLineasTalentos>$altoTotal)
					$altoTotal=$totalLineasTalentos;
					
				if($totalLineasComentarios>$altoTotal)
					$altoTotal=$totalLineasComentarios;
					
				$libro->setAltoFila($numFila,12+(12*$altoTotal));	
				
				
				if($version>1)
				{
					$versionReferencia=$version-1;
					$arrDatosFila["A"]=formatearCampoMultilinea($r->programa);
					$arrDatosFila["B"]=$lblHorario;
					$arrDatosFila["C"]=$master;
					
					
					$arrDatosFila["D"]=$listaTalentos;
					$arrDatosFila["E"]=formatearCampoMultilinea($comentarios);
					$arrDatosFila["iO"]=$r->idRegistro;
					
					
					
					$arrColorElemento=array();
					
					
					$consulta="select llave FROM 5007_llavesRegistrosReportes WHERE tipoReporte=2 AND idReferencia1=".$r->idRegistro."  AND VERSION=".$versionReferencia;
					$llaveReferencia=$con->obtenerValor($consulta);
					$arrLlave=explode("_",$llaveReferencia);
					
					foreach($arrDatosFila as $col=>$valor)
					{
						
						$pos=ord($col)-65;
						
						
						
						if(!isset($arrLlave[$pos]) ||(bE($valor)!=$arrLlave[$pos]))
						{
							
							$arrColorElemento[$col]="0000FF";
							
							if($col=="D")
							{
								$objRichText = new PHPExcel_RichText();
								$objRichText->createText("");
								$arrTalentosRegistro=explode(",",$arrDatosFila["D"]);
								$aTalentosRespaldo=explode(",",bD($arrLlave[$pos]));
								
								
								$totalTalentos=sizeof($arrTalentosRegistro);
								for($nTalento=1;$nTalento<=$totalTalentos;$nTalento++)
								{
									$colorTalento="000000";
									if($nTalento>1)
									{
										$texto=", ";
										if($nTalento==$totalTalentos)
											$texto=" y ";
										$objAux=$objRichText->createTextRun($texto);
										$phpColor = new PHPExcel_Style_Color();
										$phpColor->setRGB('000000');  
										$objAux->getFont()->setColor($phpColor);
										$lblTalento.=$texto;
									}
									$objAux=$objRichText->createTextRun(trim(obtenerNombreUsuario($arrTalentosRegistro[$nTalento-1])));
									$phpColor = new PHPExcel_Style_Color();
									
									if(!existeValor($aTalentosRespaldo,$arrTalentosRegistro[$nTalento-1]))
										$colorTalento="0000FF";
									
									$phpColor->setRGB($colorTalento);  
									$objAux->getFont()->setColor($phpColor);
									
								
								}
								$libro->setValor("D".$numFila,$objRichText);	
								
							}
							
							
						}
						else
						{
							$arrColorElemento[$col]="000000";
						}
					}
					
					foreach($arrColorElemento as $col=>$color)
					{
						$libro->setColorLetra($col.$numFila,$color);
					}
				}
				
				$libro->setBorde("A".$numFila,"DE");
				$libro->setBorde("B".$numFila,"DE");
				$libro->setBorde("C".$numFila,"DE");
				$libro->setBorde("D".$numFila,"DE");
				$libro->setBorde("E".$numFila,"DE");
							
				
				
			break;
		}
		$numFila++;
	}
	$libro->obtenerHojaActiva()->getPageSetup()->setPrintArea('A1:E'.($numFila-1));
	$libro->generarArchivo($formato,"llamadoTalentos.xlsx");
}


function generarOrdenesServiciosProduccion($idSemana,$formato="PDF",$filtros)
{
	global $baseDir ;
	global $con;
	global $arrMesLetra;
	global $arrDiasSemana;

	$objFiltros=NULL;
	$cadFiltros=bD($filtros);
	if($cadFiltros!="")
	{
		$objFiltros=json_decode("[".$cadFiltros."]");
		
	}
	
	$listaAire=-1;
	$listaMaster=-1;
	$filtroProductor=false;
	$arrProductores=array();
	$listaPrograma="";
	$filtroEstudio=false;
	$arrEstudios=array();
	if($objFiltros!=NULL)
	{
		
		$nFiltros=count($objFiltros);
		
		for($x=0;$x<$nFiltros;$x++)
		{
			switch($objFiltros[$x]->campo)
			{
				case "estudio":
				
					$aEstudios=explode(",",$objFiltros[$x]->valor);
					foreach($aEstudios as $e)
					{
						$arrEstudios[$e]=1;
					}
					$filtroEstudio=true;
				break;
				case "aire":
					$listaAire=$objFiltros[$x]->valor;
				break;
				case "region":
					$listaMaster=$objFiltros[$x]->valor;
				break;
				case "productor":
					
					$filtroProductor=true;
					$aProductores=explode(",",$objFiltros[$x]->valor);
					foreach($aProductores as $p)
					{
						$arrProductores[$p]=1;
					}
				break;
				case "programa":
					$listaPrograma=$objFiltros[$x]->valor;
				break;
				
			}
		}
		
	}
	
	
	$numFila=4;

	$ruta=$baseDir."/reportes/plantillas/plantillaOrdenesServiciosProduccion.xlsx";
	$libro=new cExcel($ruta,true,"Excel2007");
	
	$consulta="SELECT * FROM 1050_semanasAnio WHERE idSemana=".$idSemana;
	$fSemana=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$fInicio=strtotime($fSemana["fechaInicio"]);
	$fFin=strtotime($fSemana["fechaFin"]);
	
	$consulta="SELECT VERSION FROM 5005_versionesReportes WHERE tipoReporte=1 AND idSemana=".$idSemana." ORDER BY fechaRegistro DESC";
	
	$version=$con->obtenerValor($consulta);
	if($version=="")
		$version=1;
	
	$lblSemana="Del ".date("d",$fInicio);
	
	if(date("m",$fInicio)!=date("m",$fFin))
	{
		$lblSemana." de ".$arrMesLetra[(date("m",$fInicio)*1)-1];
	}
	
	$lblSemana.=" al ".date("d",$fFin)." de ".$arrMesLetra[(date("m",$fFin)*1)-1]." de ".date("Y",$fFin);
	
	$libro->setValor("H2","Semana ".$fSemana["noSemana"]);
	$libro->setValor("H3",$lblSemana);
	
	while($fInicio<=$fFin)
	{
		$arrFechasProduccion[date("Y-m-d",$fInicio)]=array();
		
		
		$consulta="SELECT id__591_tablaDinamica,nombreRecurso FROM _591_tablaDinamica WHERE categoriaRecuso=10
					UNION
					SELECT '0','Sin Estudio Asignado' AS nombreRecurso
					 ORDER BY nombreRecurso";
		$res=$con->obtenerFilas($consulta);
		while($filaEstudio=mysql_fetch_row($res))
		{
			$arrFechasProduccion[date("Y-m-d",$fInicio)][$filaEstudio[0]]=array();
			if(!$filtroEstudio)
			{
				$arrEstudios[$filaEstudio[0]]=1;
			}
		}
		
		$fInicio=strtotime("+1 days",$fInicio);
	}
	
	$consulta="SELECT oT.*,fP.fechaProduccion FROM _628_tablaDinamica fP,_602_tablaDinamica oT 
					WHERE fechaProduccion>='".$fSemana["fechaInicio"]."' AND fechaProduccion<='".$fSemana["fechaFin"]."'
					AND oT.id__602_tablaDinamica=fP.idReferencia AND folioBase IS NULL ".($listaAire!=-1?" and procesoProduccion in(".$listaAire.
					")":"")." ".($listaMaster!=-1?" and idMaster in(".$listaMaster.
					")":"")." order by fechaProduccion";
		
		
	if($listaPrograma!="")
	{
		$consulta="SELECT oT.*,fP.fechaProduccion FROM _628_tablaDinamica fP,_602_tablaDinamica oT ,
				_589_tablaDinamica cP  
				WHERE fechaProduccion>='".$fSemana["fechaInicio"]."' AND fechaProduccion<='".$fSemana["fechaFin"]."' and
				 cP.id__589_tablaDinamica=oT.nombrePrograma AND oT.id__602_tablaDinamica=fP.idReferencia AND folioBase IS NULL ".($listaAire!=-1?" and procesoProduccion in(".$listaAire.
				")":"")." ".($listaMaster!=-1?" and idMaster in(".$listaMaster.
				")":"")." and cP.nombrePrograma like '%".$listaPrograma."%' order by fechaProduccion";
	}
	
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_assoc($res))
	{
		$consulta="SELECT idAsignacion FROM _602_recursosOrdenServicios WHERE idFormulario=602 AND idReferencia=".$fila["id__602_tablaDinamica"].
				" AND tipoRecurso=1 AND idRecurso=10";
		$idEstudio=$con->obtenerValor($consulta);
		if(($idEstudio=="")||($idEstudio==-1))
			$idEstudio=0;
			
		if(isset($arrEstudios[$idEstudio]))
			array_push($arrFechasProduccion[$fila["fechaProduccion"]][$idEstudio],$fila);
			
	}
	
	
	$numReg=0;
	$arrRegistros='';
	
	$arrTitulos=array();
	$arrTitulos["A"]="Estudio";
	$arrTitulos["B"]="Hora In";
	$arrTitulos["C"]="Hora Out";
	$arrTitulos["D"]="Programa";
	$arrTitulos["E"]="Aire";
	$arrTitulos["F"]="Región";
	$arrTitulos["G"]="Productor";
	$arrTitulos["H"]="Servicios";
	$fabrica="";
	$fechaProduccionActual="";
	$numFila=4;
	
	foreach($arrFechasProduccion as $fechaProduccion=>$resto)
	{
		if($fechaProduccion!=$fechaProduccionActual)
		{
			$dteFechaEvento=strtotime($fechaProduccion);
			$lblPrograma=utf8_encode($arrDiasSemana[(date("w",$dteFechaEvento)*1)]." ".date("d",$dteFechaEvento));
			$fechaProduccionActual=$fechaProduccion;
			
			$libro->combinarCelda("A".$numFila,"H".$numFila);
			$libro->setColorFondo("A".$numFila,"D6DCE4");
			$libro->setColorFuente("A".$numFila,"000000");
//			$libro->setNegritas("A".$numFila);
			$libro->setValor("A".$numFila,$lblPrograma);
			$libro->setHAlineacion("A".$numFila,"C");
			$libro->setTamano("A".$numFila,11);


			$numFila++;
			$libro->combinarCelda("A".$numFila,"H".$numFila);
			$libro->setColorFondo("A".$numFila,"2F75B5");
			$numFila++;
			foreach($arrTitulos as $columna=>$valor)
			{
				$libro->setColorFuente($columna.$numFila.":".$columna.$numFila,"1F4E78");
				$libro->setValor($columna.$numFila,$valor);
				$libro->setHAlineacion($columna.$numFila,"C");
				$libro->setTamano($columna.$numFila,11);
			}
			$numFila++;
			
			
		}
		

		foreach($resto as $idEstudio=>$arrProgramas)
		{
			if(sizeof($arrProgramas)==0)
			{
				continue;
			}
			foreach($arrProgramas  as $p)
			{	
				$consulta="SELECT cP.nombrePrograma as lblNombrePrograma FROM _589_tablaDinamica cP WHERE  cP.id__589_tablaDinamica=".$p["nombrePrograma"];
				$filaOT=$con->obtenerPrimeraFilaAsoc($consulta);
			
				$consulta="SELECT * FROM _628_tablaDinamica WHERE idReferencia=".$p["id__602_tablaDinamica"];
				$fProduccion=$con->obtenerPrimeraFilaAsoc($consulta);
			
				$lblPrograma=$filaOT["lblNombrePrograma"];
				
				$consulta="SELECT idRegistro FROM _602_recursosOrdenServicios WHERE idFormulario=602 AND idReferencia=".$p["id__602_tablaDinamica"].
						" AND tipoRecurso=1 AND idRecurso=10";
				$idRecursoEstudio=$con->obtenerValor($consulta);
				
				$consulta="SELECT idAsignacion,idRegistro FROM _602_recursosOrdenServicios WHERE idFormulario=602 AND idReferencia=".$p["id__602_tablaDinamica"].
						" AND tipoRecurso=2 AND idRecurso=23";
				$fRegProductor=$con->obtenerPrimeraFila($consulta);
				$idProductor=$fRegProductor[0];
				if($idProductor=="")
					$idProductor=-1;
				
				if( !$filtroProductor || isset($arrProductores[$idProductor]))
				{
					
				
					$idRecursoProductor=$fRegProductor[1];
					if($idRecursoProductor=="")
						$idRecursoProductor=-1;
					
					$consulta="SELECT comentariosAdicionales FROM _602_serviciosCoordinacion WHERE idOrdenTrabajo=".$p["id__602_tablaDinamica"];
					$servicios=$con->obtenerValor($consulta);
					
					$consulta="select nombreRecurso from _591_tablaDinamica where id__591_tablaDinamica=".$idEstudio;
					$nombreEstudio=$con->obtenerValor($consulta);
					if($nombreEstudio=="")
						$nombreEstudio="";
					$consulta="SELECT nombre FROM _593_tablaDinamica WHERE id__593_tablaDinamica=".$p["idMaster"];
					$lblMaster=$con->obtenerValor($consulta);
					$libro->setValor("A".$numFila,$nombreEstudio==""?"Sin Estudio Asignado":$nombreEstudio);
					$libro->setHAlineacion("A".$numFila,"C");
					$libro->setValor("B".$numFila,date("H:i",strtotime($fProduccion["horaProduccion"])));
	
					$altoTotal=0;
	
					$libro->setHAlineacion("B".$numFila,"C");
					
					
					$lblFechaTermino=date("H:i",strtotime($fProduccion["horaEstimadaTermino"]));
					
					$libro->setValor("C".$numFila,$lblFechaTermino);
					$libro->setHAlineacion("C".$numFila,"C");
					
					
					if(date("Y-m-d",strtotime($fProduccion["horaEstimadaTermino"]))!=$fechaProduccion)
					{
						$altoTotal++;
						$lblFechaTermino=date("H:i",strtotime($fProduccion["horaEstimadaTermino"]))."\n(".date("d/m/Y",strtotime($fProduccion["horaEstimadaTermino"])).")";
						$libro->setValor("C".$numFila,$lblFechaTermino);
						$libro->hojaActiva->getStyle("C".$numFila)->getAlignment()->setWrapText(true);
						
					}
					$totalLineasPrograma=calcularNumeroLineas($lblPrograma,53);
					
					if($altoTotal<$totalLineasPrograma)
						$altoTotal=$totalLineasPrograma;
					
					$libro->setValor("D".$numFila,formatearCampoMultilinea($lblPrograma));
					$libro->setValor("E".$numFila,$fProduccion["procesoProduccion"]==1?"Vivo":"Grabado");
					$libro->setHAlineacion("E".$numFila,"C");
					$libro->setValor("F".$numFila,$lblMaster);
					$libro->setHAlineacion("F".$numFila,"C");
					$lblNombreProductor=obtenerNombreUsuario($idProductor);
					
					$totalLineasProductor=calcularNumeroLineas($lblNombreProductor,28);
	
					$libro->setValor("G".$numFila,$lblNombreProductor);
					$libro->hojaActiva->getStyle("G".$numFila)->getAlignment()->setWrapText(true);
					
					if($altoTotal<$totalLineasProductor)
						$altoTotal=$totalLineasProductor;
					
					$totalLineasServicio=calcularNumeroLineas($servicios,28);
					
					
					$libro->setValor("H".$numFila,formatearCampoMultilinea($servicios));
					
					if($altoTotal<$totalLineasServicio)
						$altoTotal=$totalLineasServicio;
					
					$libro->setAltoFila($numFila,16+(16*$altoTotal));	
					$libro->hojaActiva->getStyle("H".$numFila)->getAlignment()->setWrapText(true);
					
					if($version>1)
					{
						$versionReferencia=$version-1;
						$arrDatosFila["A"]=$nombreEstudio;
						$arrDatosFila["B"]=date("H:i",strtotime($fProduccion["horaProduccion"]));
						$arrDatosFila["C"]=$lblFechaTermino;
						
						
						$arrDatosFila["D"]=formatearCampoMultilinea($lblPrograma);
						$arrDatosFila["E"]=$fProduccion["procesoProduccion"]==1?"Vivo":"Grabado";
						$arrDatosFila["F"]=$lblMaster;
						$lblNombreProductor=obtenerNombreUsuario($idProductor);
						$arrDatosFila["G"]=$lblNombreProductor;
						$arrDatosFila["H"]=formatearCampoMultilinea($servicios);
						$arrDatosFila["IO"]=$p["id__602_tablaDinamica"];
						
						
						
						$arrColorElemento=array();
						
						
						$consulta="select llave FROM 5007_llavesRegistrosReportes WHERE tipoReporte=1 AND idReferencia1=".$p["id__602_tablaDinamica"]."  AND VERSION=".$versionReferencia;
						$llaveReferencia=$con->obtenerValor($consulta);
						$arrLlave=explode("_",$llaveReferencia);
						
						foreach($arrDatosFila as $col=>$valor)
						{
							
							$pos=ord($col)-65;
							
							if((isset($arrLlave[$pos]))&&(bD($arrLlave[$pos])=="@NULL@"))
							{
								$arrLlave[$pos]="";
							}
							
							if(!isset($arrLlave[$pos]) ||(bE($valor)!=$arrLlave[$pos]))
							{
								
								$arrColorElemento[$col]="FF0000";
							}
							else
							{
								$arrColorElemento[$col]="000000";
							}
						}
						
						foreach($arrColorElemento as $col=>$color)
						{
							$libro->setColorLetra($col.$numFila,$color);
						}
					}
					$numFila++;
				}
			}

		}
	}

	$libro->obtenerHojaActiva()->getPageSetup()->setPrintArea('A1:H'.($numFila-1));
	$libro->generarArchivo($formato,"servicioOrdenesCoordinacion.xlsx");

}

function calcularNumeroLineas($texto,$longMax)
{
	$texto=str_replace("<br />","\n",$texto);
	$texto=str_replace("\r","\n",$texto);
	$totalLineas=ceil(strlen($texto)/$longMax)-1;
	$arrEnterComentario=explode("\n",$texto);
	$totalLineas+=sizeof($arrEnterComentario)-1;
	
	return $totalLineas;
}

function formatearCampoMultilinea($texto)
{
	$texto=str_replace("<br />","\n",$texto);
	$texto=str_replace("\r","\n",$texto);
	return $texto;
}

?>