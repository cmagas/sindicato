<?php
function funcionLlenadoOficioMedidasCautelaresV2($idRegistro)
{
	global $con;
	global $arrMesLetra;
	global $leyendaTribunal;
	$arrValores=array();
	$consulta="SELECT datosParametros,carpetaAdministrativa,tipoDocumento FROM 7035_informacionDocumentos WHERE idRegistro=".$idRegistro;
	$fila=$con->obtenerPrimeraFila($consulta);
	$datosParametros=json_decode(bD($fila[0]));
	
	$arrCarpetasAntecesoras=obtenerCarpetasAntecesoras($datosParametros->carpetaJudicial);
	$listaCarpetasAntecesoras="";
	foreach($arrCarpetasAntecesoras as $c)
	{
		if($listaCarpetasAntecesoras=="")
		{
			$listaCarpetasAntecesoras="'".$c."'";
		}
		else
			$listaCarpetasAntecesoras.=",'".$c."'";
	}
	
	$consulta="SELECT carpetaInvestigacion,idActividad,carpetaAdministrativaBase,unidadGestion FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$datosParametros->carpetaJudicial."'";
	$filaCarpeta=$con->obtenerPrimeraFila($consulta);
	
	$consulta="SELECT GROUP_CONCAT(upper(dl.denominacionDelito)) FROM _61_tablaDinamica d,_35_denominacionDelito dl WHERE d.idActividad=".$filaCarpeta[1]." AND
					dl.id__35_denominacionDelito=d.denominacionDelito";
	$numDelitos=$con->filasAfectadas;	
	$delitos=$con->obtenerValor($consulta);
	
	
	$consulta="SELECT nombreUnidad,claveFolioCarpetas,tipoMateria FROM _17_tablaDinamica WHERE  claveUnidad='".$filaCarpeta[3]."'";
	$filaUGA=$con->obtenerPrimeraFila($consulta);
	
	$noUGA=$filaUGA[1]*1;
	if($filaUGA[2]==2)
		$noUGA="JA";
	
	$numVictimas=0;
	$victimas="";
	$consulta="SELECT upper(CONCAT(IF(nombre IS NULL,'',nombre),' ',IF(apellidoPaterno IS NULL,'',apellidoPaterno),' ',IF(apellidoMaterno IS NULL,'',apellidoMaterno))) 
				FROM _47_tablaDinamica p,7005_relacionFigurasJuridicasSolicitud r WHERE r.idParticipante=p.id__47_tablaDinamica
				AND r.idActividad=".$filaCarpeta[1]." AND r.idFiguraJuridica=2 ORDER BY nombre,nombre,apellidoMaterno";
	
	$res=$con->obtenerFilas($consulta);
	while($filaImputado=mysql_fetch_row($res))
	{
		$nombre=$filaImputado[0];
		if($datosParametros->mostrarVictimasComo!=1)
			$nombre=inicialesNombre($filaImputado[0]);
		
		if($victimas=="")
			$victimas=trim($nombre);
		else
			$victimas.=", ".trim($nombre);
		$numVictimas++;
	}

	$consulta="SELECT upper(CONCAT(IF(nombre IS NULL,'',nombre),' ',IF(apellidoPaterno IS NULL,'',apellidoPaterno),' ',IF(apellidoMaterno IS NULL,'',apellidoMaterno))) 
				FROM _47_tablaDinamica p where id__47_tablaDinamica in(".(isset($datosParametros->imputados)?$datosParametros->imputados:-1).
				") ORDER BY nombre,nombre,apellidoMaterno";
	
	
	$arrValores["fecha"]=convertirFechaLetra(date("Y-m-d"),false,false);
	$arrValores["carpetaJudicial"]=$datosParametros->carpetaJudicial;
	$arrValores["carpetaJudicialAnterior"]=$filaCarpeta[2]==""?"Sin carpeta anterior":$filaCarpeta[2];
	$arrValores["carpetaInvestigacion"]=$filaCarpeta[0];
	$arrValores["imputado"]=$con->obtenerListaValores($consulta);
	
	$arrValores["fechaVinculacion"]=date("d/m/Y",strtotime($datosParametros->fechaVinculacion));
	$arrValores["folioOficio"]="UGJ".$noUGA."/".str_pad($datosParametros->noOficioAsignar,4,"0",STR_PAD_LEFT)."/".date("Y");
	
	$arrValores["delito"]=$delitos;
	$arrValores["victimaCabecera"]=$victimas==""?"________________":$victimas;
	$arrValores["victima"]=$arrValores["victimaCabecera"];
	$arrValores["noUGA"]=convertirNumeroLetra($noUGA,false,false);
	if($arrValores["noUGA"]=="UN")
		$arrValores["noUGA"]="UNO";
	$arrValores["nombreResponsable"]=obtenerNombreUsuario($_SESSION["idUsr"]);	
	
	if($datosParametros->firmante!=0)
	{
		$consulta=" SELECT 
					 IF((SELECT upper(nombre) FROM 800_usuarios WHERE idUsuario=usuarioAsignado) IS NULL,'(NO ASIGNADO)',
					 (SELECT upper(nombre) FROM 800_usuarios WHERE idUsuario=usuarioAsignado) ) AS nombre,
					 (SELECT upper(nombrePuesto) FROM _416_tablaDinamica WHERE id__416_tablaDinamica=puestoOrganozacional)  AS  puesto
					FROM _421_tablaDinamica WHERE id__421_tablaDinamica=".$datosParametros->firmante;
		
		$filaPuesto=$con->obtenerPrimeraFila($consulta);
		$arrValores["puestoFirmante"]=$filaPuesto[1];
		$arrValores["nombreFirmante"]=$filaPuesto[0];
	}
	else
	{
		$arrValores["puestoFirmante"]=$datosParametros->puestoFirmante;
		$arrValores["nombreFirmante"]=$arrValores["nombreResponsable"];
	}
	
	
	$arrValores["leyendaTribunal"]=str_replace("<br>", " ",$leyendaTribunal);
	$arrValores["leyendaVictimaCabecera"]=($datosParametros->mostrarVictimasComo!=1)?"Víctima u Ofendido de Iniciales":"Víctima u Ofendido";
	switch($fila[2])
	{
		case 601:
		case 602:
		case 620:
		case 637:
			$consulta="SELECT horaInicioEvento,idSala,tipoAudiencia FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$datosParametros->audienciaCelebrar;
			$fAudiencia=$con->obtenerPrimeraFila($consulta);
			
			$consulta="SELECT idJuez FROM 7001_eventoAudienciaJuez WHERE idRegistroEvento=".$datosParametros->audienciaCelebrar;
			$idJuez=$con->obtenerValor($consulta);
			$consulta="SELECT Genero FROM 802_identifica WHERE idUsuario=".$idJuez;
			
			$generoJuez=$con->obtenerValor($consulta);
			$consulta="SELECT nombreSala FROM _15_tablaDinamica WHERE id__15_tablaDinamica=".$fAudiencia[1];
			$nombreSala=$con->obtenerValor($consulta);
			
			$consulta="SELECT tipoAudiencia FROM _4_tablaDinamica WHERE id__4_tablaDinamica=".$fAudiencia[2];
			$tipoAudiencia=$con->obtenerValor($consulta);
			
			$arrValores["tipoAudiencia"]=trim(str_replace("Audiencia de ","",$tipoAudiencia));
			$arrValores["nombreImputado"]=$arrValores["imputado"];
			$arrValores["fechaAudiencia"]=convertirFechaLetra($fAudiencia[0],false,false);
			$arrValores["horaAudiencia"]=date("H:i",strtotime($fAudiencia[0]));
			$arrValores["salaAudiencia"]=trim(str_replace("Sala","",$nombreSala))*1;
			
			$arrValores["arrLeyendaJuez"]=$generoJuez==0?"el Juez":"la Jueza"; 
			$arrValores["nombreUnidad"]="Unidad de Gestión Judicial en Materia de Justicia para Adolescentes";
			
		break;
		case 603:
		case 621:
			$consulta="SELECT horaInicioEvento,idSala,tipoAudiencia FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$datosParametros->audienciaCelebrar;
			$fAudiencia=$con->obtenerPrimeraFila($consulta);
			
			$consulta="SELECT idJuez FROM 7001_eventoAudienciaJuez WHERE idRegistroEvento=".$datosParametros->audienciaCelebrar;
			$idJuez=$con->obtenerValor($consulta);
			$consulta="SELECT Genero FROM 802_identifica WHERE idUsuario=".$idJuez;
			
			$generoJuez=$con->obtenerValor($consulta);
			$arrValores["juez"]=obtenerNombreUsuario($idJuez,true);
			$arrValores["nombreImputado"]=$arrValores["imputado"];
			$arrValores["fechaAudiencia"]=convertirFechaLetra($fAudiencia[0],false,false);
			$arrValores["arrLeyendaJuez"]=$generoJuez==0?"Juez":"Jueza"; 
			$arrValores["lblDelitos"]= $numDelitos>1?"de los hechos que la ley establece como delitos":"del hecho que la ley establece como delito";
			$arrValores["lblDelitos2"]= $numDelitos>1?"cometidos":"cometido";
			$arrValores["lblVictimas"]= $numVictimas>1?"en agravio de las víctimas":"en agravio de la víctima";
			
			
			$fracciones="";
			$lblLeyendas="";
			$lblLeyenda="";
			$pFraccion=1;
			foreach($datosParametros->medidadCautelares as $m)
			{
				
				
				
				
				if($m->idMedidaCautelar!=0)
				{
					$consulta="SELECT fraccion,leyenda FROM _587_tablaDinamica WHERE id__587_tablaDinamica =".$m->idMedidaCautelar." ORDER BY id__587_tablaDinamica";
					$fLeyenda=$con->obtenerPrimeraFila($consulta);
					$lblFraccion=$fLeyenda[0];
					$lblLeyenda=$pFraccion.".- ".$fLeyenda[1];
				}
				else
				{
					
					$lblFraccion="otro";
					$lblLeyenda=$pFraccion.".- Otro";
					
				}
				if($m->detalles!="")
				{
					if($lblLeyenda=="")
						$lblLeyenda=$m->detalles;
					else
						$lblLeyenda.=": ".$m->detalles;
				}
				
				
				if($lblLeyendas=="")
					$lblLeyendas="<p>".$lblLeyenda.".</p>";
				else
					$lblLeyendas.="<p>".$lblLeyenda.".</p>";
				
				if($fracciones=="")
					  $fracciones=$lblFraccion;
				 else
				 {
					 if($pFraccion<sizeof($datosParametros->medidadCautelares))
						$fracciones.=", ".$lblFraccion;
					else
						$fracciones.=" y ".$lblFraccion;
				 
				 
				 
				 }
					
				
				
				$pFraccion++;
			}
			$pFraccion--;
			$plazoPresentacion="";
			if($datosParametros->tipoPeriodoLimite==2)
			{
				$plazoPresentacion=" el día ".convertirFechaLetra($datosParametros->fechaLimite,false,false);
			}
			else
			{
				
				$plazoPresentacion=" en ".$datosParametros->periodoLimite;
				switch($datosParametros->plazoPeriodoLimite)
				{
					case 1:
						$plazoPresentacion.=($datosParametros->periodoLimite==1?" hora":" horas");
					break;
					case 2:
						$plazoPresentacion.=($datosParametros->periodoLimite==1?" dia":" dias");
					break;
					case 3:
						$plazoPresentacion.=($datosParametros->periodoLimite==1?" mes":" meses");
					break;
					case 4:
						$plazoPresentacion.=($datosParametros->periodoLimite==1?" año":" años");
					break;
				}
			}
			$arrValores["horasTermino"]=$datosParametros->terminoConstitucional;
			$arrValores["fracciones"]=$fracciones;
			$arrValores["plazoPresentacion"]=$plazoPresentacion;
			$arrValores["medidasCautelares"]=$lblLeyendas;
			$arrValores["lblFracciones"]=$pFraccion==1?"fracción ".$arrValores["fracciones"]." del Código Nacional de Procedimientos Penales, consistente":"fracciones ".$arrValores["fracciones"]." del Código Nacional de Procedimientos Penales, consistentes";
			$arrValores["lblMedidasImpuestas"]=$pFraccion==1?" la medida cautelar impuesta":" las medidas cautelares impuestas";
			$arrValores["nombreUnidad"]="Unidad de Gestión Judicial en Materia de Justicia para Adolescentes";
		break;
		case 604:
		case 622:
			$consulta="SELECT horaInicioEvento,idSala,tipoAudiencia FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$datosParametros->audienciaCelebrar;
			$fAudiencia=$con->obtenerPrimeraFila($consulta);
			
			$consulta="SELECT idJuez FROM 7001_eventoAudienciaJuez WHERE idRegistroEvento=".$datosParametros->audienciaCelebrar;
			$idJuez=$con->obtenerValor($consulta);
			$consulta="SELECT Genero FROM 802_identifica WHERE idUsuario=".$idJuez;
			
			$generoJuez=$con->obtenerValor($consulta);
			$arrValores["juez"]=obtenerNombreUsuario($idJuez,true);
			$arrValores["nombreImputado"]=$arrValores["imputado"];
			$arrValores["fechaAudiencia"]=convertirFechaLetra($fAudiencia[0],false,false);
			$arrValores["arrLeyendaJuez"]=$generoJuez==0?"Juez":"Jueza"; 
			$arrValores["lblDelitos"]= $numDelitos>1?"los hechos que la ley establece como delitos":"el hecho que la ley establece como delito";
			$arrValores["lblDelitos2"]= $numDelitos>1?"cometidos":"cometido";
			$arrValores["lblVictimas"]= $numVictimas>1?"en agravio de las víctimas":"en agravio de la víctima";
			
			
			
			$arrValores["prefijoJuez"]=$generoJuez==0?"el":"la"; 
			$arrValores["nombreUnidad"]="Unidad de Gestión Judicial en Materia de Justicia para Adolescentes";
		break;
		case 605:
		
			$consulta="SELECT horaInicioEvento,idSala,tipoAudiencia FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$datosParametros->audienciaCelebrar;
			$fAudiencia=$con->obtenerPrimeraFila($consulta);
			
			$consulta="SELECT idJuez FROM 7001_eventoAudienciaJuez WHERE idRegistroEvento=".$datosParametros->audienciaCelebrar;
			$idJuez=$con->obtenerValor($consulta);
			$consulta="SELECT Genero FROM 802_identifica WHERE idUsuario=".$idJuez;
			
			$generoJuez=$con->obtenerValor($consulta);
			$arrValores["juez"]=obtenerNombreUsuario($idJuez,true);
			$arrValores["nombreImputado"]=$arrValores["imputado"];
			$arrValores["fechaAudiencia"]=convertirFechaLetra($fAudiencia[0],false,false);
			$arrValores["arrLeyendaJuez"]=$generoJuez==0?"Juez":"Jueza"; 
			$arrValores["lblDelitos"]= $numDelitos>1?"los hechos que la ley establece como delitos":"el hecho que la ley establece como delito";
			$arrValores["lblDelitos2"]= $numDelitos>1?"cometidos":"cometido";
			$arrValores["lblVictimas"]= $numVictimas>1?"en agravio de las víctimas":"en agravio de la víctima";
			
			
			$fracciones="";
			$lblLeyendas="";
			$lblLeyenda="";
			$pFraccion=1;
			foreach($datosParametros->medidadCautelares as $m)
			{
				
				
				
				
				if($m->idMedidaCautelar!=0)
				{
					$consulta="SELECT fraccion,leyenda FROM _587_tablaDinamica WHERE id__587_tablaDinamica =".$m->idMedidaCautelar." ORDER BY id__587_tablaDinamica";
					$fLeyenda=$con->obtenerPrimeraFila($consulta);
					$lblFraccion=$fLeyenda[0];
					$lblLeyenda=$pFraccion.".- ".$fLeyenda[1];
				}
				else
				{
					
					$lblFraccion="otro";
					$lblLeyenda=$pFraccion.".- Otro";
					
				}
				if($m->detalles!="")
				{
					if($lblLeyenda=="")
						$lblLeyenda=$m->detalles;
					else
						$lblLeyenda.=": ".$m->detalles;
				}
				
				
				if($lblLeyendas=="")
					$lblLeyendas="<p>".$lblLeyenda.".</p>";
				else
					$lblLeyendas.="<p>".$lblLeyenda.".</p>";
				
				if($fracciones=="")
					  $fracciones=$lblFraccion;
				 else
				 {
					 if($pFraccion<sizeof($datosParametros->medidadCautelares))
						$fracciones.=", ".$lblFraccion;
					else
						$fracciones.=" y ".$lblFraccion;
				 
				 
				 
				 }
					
				
				
				$pFraccion++;
			}
			$pFraccion--;
			$plazoPresentacion="";
			if($datosParametros->tipoPeriodoLimite==2)
			{
				$plazoPresentacion=" el día ".convertirFechaLetra($datosParametros->fechaLimite,false,false);
			}
			else
			{
				
				$plazoPresentacion=" en ".$datosParametros->periodoLimite;
				switch($datosParametros->plazoPeriodoLimite)
				{
					case 1:
						$plazoPresentacion.=($datosParametros->periodoLimite==1?" hora":" horas");
					break;
					case 2:
						$plazoPresentacion.=($datosParametros->periodoLimite==1?" dia":" dias");
					break;
					case 3:
						$plazoPresentacion.=($datosParametros->periodoLimite==1?" mes":" meses");
					break;
					case 4:
						$plazoPresentacion.=($datosParametros->periodoLimite==1?" año":" años");
					break;
				}
			}
			
			$arrValores["fracciones"]=$fracciones;
			$arrValores["plazoPresentacion"]=$plazoPresentacion;
			$arrValores["medidasCautelares"]=$lblLeyendas;
			$arrValores["lblFracciones"]=" ".$arrValores["fracciones"];
			$arrValores["lblMedidasImpuestas"]=$pFraccion==1?" la medida cautelar impuesta":" las medidas cautelares impuestas";
			$arrValores["lblFraccion2"]=$pFraccion==1?" la medida cautelar prevista en la fracción":" las medidas cautelares previstas en las fracciones";
			$arrValores["prefijoJuez"]=$generoJuez==0?"el":"la"; 
			$arrValores["nombreUnidad"]="Unidad de Gestión Judicial en Materia de Justicia para Adolescentes";
		break;
		case 623:
			$consulta="SELECT horaInicioEvento,idSala,tipoAudiencia FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$datosParametros->audienciaCelebrar;
			$fAudiencia=$con->obtenerPrimeraFila($consulta);
			
			$consulta="SELECT idJuez FROM 7001_eventoAudienciaJuez WHERE idRegistroEvento=".$datosParametros->audienciaCelebrar;
			$idJuez=$con->obtenerValor($consulta);
			$consulta="SELECT Genero FROM 802_identifica WHERE idUsuario=".$idJuez;
			
			$generoJuez=$con->obtenerValor($consulta);
			$arrValores["juez"]=obtenerNombreUsuario($idJuez,true);
			$arrValores["nombreImputado"]=$arrValores["imputado"];
			$arrValores["fechaAudiencia"]=convertirFechaLetra($fAudiencia[0],false,false);
			$arrValores["arrLeyendaJuez"]=$generoJuez==0?"Juez":"Jueza"; 
			$arrValores["lblDelitos"]= $numDelitos>1?"los hechos que la ley establece como delitos":"el hecho que la ley establece como delito";
			$arrValores["lblDelitos2"]= $numDelitos>1?"cometidos":"cometido";
			$arrValores["lblVictimas"]= $numVictimas>1?"en agravio de las víctimas":"en agravio de la víctima";
			
			
			$fracciones="";
			$lblLeyendas="";
			$lblLeyenda="";
			$pFraccion=1;
			foreach($datosParametros->medidadCautelares as $m)
			{
				
				
				
				
				if($m->idMedidaCautelar!=0)
				{
					$consulta="SELECT fraccion,leyenda FROM _587_tablaDinamica WHERE id__587_tablaDinamica =".$m->idMedidaCautelar." ORDER BY id__587_tablaDinamica";
					$fLeyenda=$con->obtenerPrimeraFila($consulta);
					$lblFraccion=$fLeyenda[0];
					$lblLeyenda=$pFraccion.".- ".$fLeyenda[1];
				}
				else
				{
					
					$lblFraccion="otro";
					$lblLeyenda=$pFraccion.".- Otro";
					
				}
				if($m->detalles!="")
				{
					if($lblLeyenda=="")
						$lblLeyenda=$m->detalles;
					else
						$lblLeyenda.=": ".$m->detalles;
				}
				
				
				if($lblLeyendas=="")
					$lblLeyendas="<p>".$lblLeyenda.".</p>";
				else
					$lblLeyendas.="<p>".$lblLeyenda.".</p>";
				
				if($fracciones=="")
					  $fracciones=$lblFraccion;
				 else
				 {
					 if($pFraccion<sizeof($datosParametros->medidadCautelares))
						$fracciones.=", ".$lblFraccion;
					else
						$fracciones.=" y ".$lblFraccion;
				 
				 
				 
				 }
					
				
				
				$pFraccion++;
			}
			$pFraccion--;
			$plazoPresentacion="";
			if($datosParametros->tipoPeriodoLimite==2)
			{
				$plazoPresentacion=" el día ".convertirFechaLetra($datosParametros->fechaLimite,false,false);
			}
			else
			{
				
				$plazoPresentacion=" en ".$datosParametros->periodoLimite;
				switch($datosParametros->plazoPeriodoLimite)
				{
					case 1:
						$plazoPresentacion.=($datosParametros->periodoLimite==1?" hora":" horas");
					break;
					case 2:
						$plazoPresentacion.=($datosParametros->periodoLimite==1?" dia":" dias");
					break;
					case 3:
						$plazoPresentacion.=($datosParametros->periodoLimite==1?" mes":" meses");
					break;
					case 4:
						$plazoPresentacion.=($datosParametros->periodoLimite==1?" año":" años");
					break;
				}
			}
			
			$arrValores["fracciones"]=($pFraccion==1?"fracción":"fracciones")." ".$fracciones;
			$arrValores["plazoPresentacion"]=$plazoPresentacion;
			$arrValores["medidasCautelares"]=$lblLeyendas;
			$arrValores["lblFracciones"]=" ".$arrValores["fracciones"];
			$arrValores["lblMedidasImpuestas"]=$pFraccion==1?" la medida cautelar impuesta":" las medidas cautelares impuestas";
			$arrValores["lblFraccion2"]=$pFraccion==1?" la medida cautelar establecida":" las medidas cautelares establecidas";
			$arrValores["prefijoJuez"]=$generoJuez==0?"el":"la"; 
			$arrValores["nombreUnidad"]="Unidad de Gestión Judicial en Materia de Justicia para Adolescentes";
		break;
		case 606:
		case 624:
			$consulta="SELECT horaInicioEvento,idSala,tipoAudiencia FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$datosParametros->audienciaCelebrar;
			$fAudiencia=$con->obtenerPrimeraFila($consulta);
			
			$consulta="SELECT idJuez FROM 7001_eventoAudienciaJuez WHERE idRegistroEvento=".$datosParametros->audienciaCelebrar;
			$idJuez=$con->obtenerValor($consulta);
			$consulta="SELECT Genero FROM 802_identifica WHERE idUsuario=".$idJuez;
			
			$generoJuez=$con->obtenerValor($consulta);
			$arrValores["juez"]=obtenerNombreUsuario($idJuez,true);
			$arrValores["nombreImputado"]=$arrValores["imputado"];
			$arrValores["fechaAudiencia"]=convertirFechaLetra($fAudiencia[0],false,false);
			$arrValores["arrLeyendaJuez"]=$generoJuez==0?"Juez":"Jueza"; 
			$arrValores["lblDelitos"]= $numDelitos>1?"los hechos que la ley establece como delitos":"el hecho que la ley establece como delito";
			$arrValores["lblDelitos2"]= $numDelitos>1?"cometidos":"cometido";
			$arrValores["lblVictimas"]= $numVictimas>1?"en agravio de las víctimas":"en agravio de la víctima";
			
			
			$fracciones="";
			$lblLeyendas="";
			$lblLeyenda="";
			$pFraccion=1;
			foreach($datosParametros->medidadCautelares as $m)
			{
				
				
				
				
				if($m->idMedidaCautelar!=0)
				{
					$consulta="SELECT fraccion,leyenda FROM _587_tablaDinamica WHERE id__587_tablaDinamica =".$m->idMedidaCautelar." ORDER BY id__587_tablaDinamica";
					$fLeyenda=$con->obtenerPrimeraFila($consulta);
					$lblFraccion=$fLeyenda[0];
					$lblLeyenda=$pFraccion.".- ".$fLeyenda[1];
				}
				else
				{
					
					$lblFraccion="otro";
					$lblLeyenda=$pFraccion.".- Otro";
					
				}
				if($m->detalles!="")
				{
					if($lblLeyenda=="")
						$lblLeyenda=$m->detalles;
					else
						$lblLeyenda.=": ".$m->detalles;
				}
				
				
				if($lblLeyendas=="")
					$lblLeyendas="<p>".$lblLeyenda.".</p>";
				else
					$lblLeyendas.="<p>".$lblLeyenda.".</p>";
				
				if($fracciones=="")
					  $fracciones=$lblFraccion;
				 else
				 {
					 if($pFraccion<sizeof($datosParametros->medidadCautelares))
						$fracciones.=", ".$lblFraccion;
					else
						$fracciones.=" y ".$lblFraccion;
				 
				 
				 
				 }
					
				
				
				$pFraccion++;
			}
			$pFraccion--;
			$plazoPresentacion="";
			if($datosParametros->tipoPeriodoLimite==2)
			{
				$plazoPresentacion=" el día ".convertirFechaLetra($datosParametros->fechaLimite,false,false);
			}
			else
			{
				
				$plazoPresentacion=" en ".$datosParametros->periodoLimite;
				switch($datosParametros->plazoPeriodoLimite)
				{
					case 1:
						$plazoPresentacion.=($datosParametros->periodoLimite==1?" hora":" horas");
					break;
					case 2:
						$plazoPresentacion.=($datosParametros->periodoLimite==1?" dia":" dias");
					break;
					case 3:
						$plazoPresentacion.=($datosParametros->periodoLimite==1?" mes":" meses");
					break;
					case 4:
						$plazoPresentacion.=($datosParametros->periodoLimite==1?" año":" años");
					break;
				}
			}
			
			$arrValores["fracciones"]=$fracciones;
			$arrValores["plazoPresentacion"]=$plazoPresentacion;
			$arrValores["medidasCautelares"]=$lblLeyendas;
			$arrValores["lblFracciones"]=" ".$arrValores["fracciones"];
			$arrValores["lblMedidasImpuestas"]=$pFraccion==1?" la medida cautelar establecida":" las medidas cautelares establecidas";
			$arrValores["lblFraccion2"]=$pFraccion==1?"fracción":"fracciones";
			$arrValores["lblConsistente"]=$pFraccion==1?"consistente":"consistentes";
			$arrValores["prefijoJuez"]=$generoJuez==0?"el":"la"; 
			$arrValores["nombreUnidad"]="Unidad de Gestión Judicial en Materia de Justicia para Adolescentes";
		break;
		case 607:
		case 625:
			$consulta="SELECT horaInicioEvento,idSala,tipoAudiencia FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$datosParametros->audienciaCelebrar;
			$fAudiencia=$con->obtenerPrimeraFila($consulta);
			
			$consulta="SELECT idJuez FROM 7001_eventoAudienciaJuez WHERE idRegistroEvento=".$datosParametros->audienciaCelebrar;
			$idJuez=$con->obtenerValor($consulta);
			$consulta="SELECT Genero FROM 802_identifica WHERE idUsuario=".$idJuez;
			
			$generoJuez=$con->obtenerValor($consulta);
			$arrValores["juez"]=obtenerNombreUsuario($idJuez,true);
			$arrValores["nombreImputado"]=$arrValores["imputado"];
			$arrValores["fechaAudiencia"]=convertirFechaLetra($fAudiencia[0],false,false);
			$arrValores["arrLeyendaJuez"]=$generoJuez==0?"Juez":"Jueza"; 
			$arrValores["lblDelitos"]= $numDelitos>1?"los hechos que la ley establece como delitos":"el hecho que la ley establece como delito";
			$arrValores["lblDelitos2"]= $numDelitos>1?"cometidos":"cometido";
			$arrValores["lblVictimas"]= $numVictimas>1?"en agravio de las víctimas":"en agravio de la víctima";
			
			
			$fracciones="";
			$lblLeyendas="";
			$lblLeyenda="";
			$pFraccion=1;
			foreach($datosParametros->condicionesSupencion as $c)
			{
				
				
				
				
				if($c->idCondicion!=0)
				{
					$consulta="SELECT fraccion,leyenda FROM _587_tablaDinamica WHERE id__587_tablaDinamica =".$c->idCondicion." ORDER BY id__587_tablaDinamica";
					$fLeyenda=$con->obtenerPrimeraFila($consulta);
					$lblFraccion=$fLeyenda[0];
					$lblLeyenda=$pFraccion.".- ".$fLeyenda[1];
				}
				else
				{
					
					$lblFraccion="otro";
					$lblLeyenda=$pFraccion.".- Otro";
					
				}
				if($c->detalles!="")
				{
					if($lblLeyenda=="")
						$lblLeyenda=$c->detalles;
					else
						$lblLeyenda.=": ".$c->detalles;
				}
				
				
				if($lblLeyendas=="")
					$lblLeyendas="<p>".$lblLeyenda.".</p>";
				else
					$lblLeyendas.="<p>".$lblLeyenda.".</p>";
				
				if($fracciones=="")
					  $fracciones=$lblFraccion;
				 else
				 {
					 if($pFraccion<sizeof($datosParametros->condicionesSupencion))
						$fracciones.=", ".$lblFraccion;
					else
						$fracciones.=" y ".$lblFraccion;
				 
				 
				 
				 }
					
				
				
				$pFraccion++;
			}
			$pFraccion--;
			$plazoPresentacion="";
			if($datosParametros->tipoPeriodoLimite==2)
			{
				$plazoPresentacion=" el día ".convertirFechaLetra($datosParametros->fechaLimite,false,false);
			}
			else
			{
				
				$plazoPresentacion=" en ".$datosParametros->periodoLimite;
				switch($datosParametros->plazoPeriodoLimite)
				{
					case 1:
						$plazoPresentacion.=($datosParametros->periodoLimite==1?" hora":" horas");
					break;
					case 2:
						$plazoPresentacion.=($datosParametros->periodoLimite==1?" dia":" dias");
					break;
					case 3:
						$plazoPresentacion.=($datosParametros->periodoLimite==1?" mes":" meses");
					break;
					case 4:
						$plazoPresentacion.=($datosParametros->periodoLimite==1?" año":" años");
					break;
				}
			}
			
			$arrValores["fracciones"]=$fracciones;
			$arrValores["plazoPresentacion"]=$plazoPresentacion;
			$arrValores["condicionesSuspencion"]=$lblLeyendas;
			$arrValores["lblFracciones"]=" ".$arrValores["fracciones"];
			$arrValores["lblCondicionesImpuestas"]=$pFraccion==1?" la condición impuesta":" las condiciones impuestas";
			$arrValores["lblFraccion2"]=$pFraccion==1?"fracción":"fracciones";
			$arrValores["lblConsistente"]=$pFraccion==1?"consistente":"consistentes";
			$arrValores["prefijoJuez"]=$generoJuez==0?"el":"la";
			$arrValores["planReparacion"]=$datosParametros->planReparacion;
			$arrValores["duracionPeriodo"]=$datosParametros->periodoSuspension;
			switch($datosParametros->tiempoSuspension)
			{
				case 1:
					$arrValores["duracionPeriodo"].=$datosParametros->periodoSuspension==1?" hora":" horas";
				break;
				case 2:
					$arrValores["duracionPeriodo"].=$datosParametros->periodoSuspension==1?" dia":" dias";
				break;
				case 3:
					$arrValores["duracionPeriodo"].=$datosParametros->periodoSuspension==1?" mes":" meses";
				break;
				case 4:
					$arrValores["duracionPeriodo"].=$datosParametros->periodoSuspension==1?" año":" años";
				break;
				
			}
			
			$arrValores["nombreUnidad"]="Unidad de Gestión Judicial en Materia de Justicia para Adolescentes";
			 
		break;
		case 608:
		case 626:
			$consulta="SELECT horaInicioEvento,idSala,tipoAudiencia FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$datosParametros->audienciaCelebrar;
			$fAudiencia=$con->obtenerPrimeraFila($consulta);
			$consulta="SELECT tipoAudiencia FROM _4_tablaDinamica WHERE id__4_tablaDinamica=".$fAudiencia[2];
			$tipoAudiencia=$con->obtenerValor($consulta);
			
			$arrValores["tipoAudiencia"]=trim(str_replace("Audiencia de ","",$tipoAudiencia));
			$consulta="SELECT idJuez FROM 7001_eventoAudienciaJuez WHERE idRegistroEvento=".$datosParametros->audienciaCelebrar;
			$idJuez=$con->obtenerValor($consulta);
			$consulta="SELECT Genero FROM 802_identifica WHERE idUsuario=".$idJuez;
			
			$generoJuez=$con->obtenerValor($consulta);
			$arrValores["juez"]=obtenerNombreUsuario($idJuez,true);
			$arrValores["nombreImputado"]=$arrValores["imputado"];
			$arrValores["fechaAudiencia"]=convertirFechaLetra($fAudiencia[0],false,false);
			$arrValores["arrLeyendaJuez"]=$generoJuez==0?"Juez":"Jueza"; 
			$consulta="SELECT unidad FROM 817_organigrama WHERE codigoUnidad='".$datosParametros->centroReclusion."'";
			$arrValores["reclusorio"]=$con->obtenerValor($consulta);
			$arrValores["prefijoJuez"]=$generoJuez==0?"el":"la";
			$arrValores["nombreUnidad"]="Unidad de Gestión Judicial en Materia de Justicia para Adolescentes";
		break;
		case 609:
			$consulta="SELECT horaInicioEvento,idSala,tipoAudiencia FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$datosParametros->audienciaCelebrar;
			$fAudiencia=$con->obtenerPrimeraFila($consulta);
			
			$consulta="SELECT idJuez FROM 7001_eventoAudienciaJuez WHERE idRegistroEvento=".$datosParametros->audienciaCelebrar;
			$idJuez=$con->obtenerValor($consulta);
			$consulta="SELECT Genero FROM 802_identifica WHERE idUsuario=".$idJuez;
			
			$generoJuez=$con->obtenerValor($consulta);
			$consulta="SELECT tipoAudiencia FROM _4_tablaDinamica WHERE id__4_tablaDinamica=".$fAudiencia[2];
			$tipoAudiencia=$con->obtenerValor($consulta);
			
			$arrValores["tipoAudiencia"]=trim(str_replace("Audiencia de ","",$tipoAudiencia));
			$arrValores["juez"]=obtenerNombreUsuario($idJuez,true);
			$arrValores["nombreImputado"]=$arrValores["imputado"];
			$arrValores["fechaAudiencia"]=convertirFechaLetra($fAudiencia[0],false,false);
			$arrValores["arrLeyendaJuez"]=$generoJuez==0?"Juez":"Jueza"; 
			$arrValores["lblDelitos"]= $numDelitos>1?"los hechos que la ley establece como delitos":"el hecho que la ley establece como delito";
			$arrValores["lblDelitos2"]= $numDelitos>1?"cometidos":"cometido";
			$arrValores["lblVictimas"]= $numVictimas>1?"en agravio de las víctimas":"en agravio de la víctima";
			
			$plazoPresentacion="";
			if($datosParametros->tipoPeriodoLimite==2)
			{
				$plazoPresentacion=" el día ".convertirFechaLetra($datosParametros->fechaLimite,false,false);
			}
			else
			{
				
				$plazoPresentacion=" en ".$datosParametros->periodoLimite;
				switch($datosParametros->plazoPeriodoLimite)
				{
					case 1:
						$plazoPresentacion.=($datosParametros->periodoLimite==1?" hora":" horas");
					break;
					case 2:
						$plazoPresentacion.=($datosParametros->periodoLimite==1?" dia":" dias");
					break;
					case 3:
						$plazoPresentacion.=($datosParametros->periodoLimite==1?" mes":" meses");
					break;
					case 4:
						$plazoPresentacion.=($datosParametros->periodoLimite==1?" año":" años");
					break;
				}
			}
			
			
			$arrValores["plazoPresentacion"]=$plazoPresentacion;
		break;
		
		case 619:
			
			$idJuez=$datosParametros->juez;
			$consulta="SELECT Genero FROM 802_identifica WHERE idUsuario=".$idJuez;
			$generoJuez=$con->obtenerValor($consulta);
			$arrValores["nombreImputado"]=$arrValores["imputado"];
			$arrValores["juez"]=obtenerNombreUsuario($idJuez,true);
			$arrValores["arrLeyendaJuez"]=$generoJuez==0?"Juez":"Jueza"; 
			$arrValores["prefijoJuez"]=$generoJuez==0?"el":"la";  
			$arrValores["fechaAuto"]=convertirFechaLetra($datosParametros->fechaAuto,false,false);
			$arrValores["lblDelitos"]= $numDelitos>1?"los hechos que la ley establece como delitos":"el hecho que la ley establece como delito";
			$arrValores["lblDelitos2"]= $numDelitos>1?"cometidos":"cometido";
			$arrValores["lblVictimas"]= $numVictimas>1?"en agravio de las víctimas":"en agravio de la víctima";
			
			$consulta="SELECT idActividad,carpetaAdministrativaBase FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$datosParametros->carpetaJudicial."'";
			$fDatosCarpeta=$con->obtenerPrimeraFila($consulta);
			
			$arrValores["carpetaAnterior"]=$fDatosCarpeta[1]==""?"______________":$fDatosCarpeta[1];
			$consulta="SELECT upper(CONCAT(IF(nombre IS NULL,'',nombre),' ',IF(apellidoPaterno IS NULL,'',apellidoPaterno),' ',IF(apellidoMaterno IS NULL,'',apellidoMaterno))) 
						FROM _47_tablaDinamica p where id__47_tablaDinamica in(".$fDatosCarpeta[0].
						") ORDER BY nombre,nombre,apellidoMaterno";
	
			$arrValores["imputado"]=$con->obtenerListaValores($consulta);
			$nImputados=$con->filasAfectadas;
			$arrValores["nombreImputado"]=$arrValores["imputado"];
			$arrValores["lblImputadas"]=$nImputados>1?"las personas imputadas ":"la persona imputada ";
			$arrValores["lblImputadas2"]=$nImputados>1?"las personas imputadas mencionadas":"la persona imputada mencionada";
			
			
			$arrFracciones=array();
			
			$aFracciones=explode(",",$datosParametros->listaMedidasActuales);
			
			foreach($aFracciones as $f)
			{
				$fraccion="OTRA";
				if($f!=0)
				{
					$consulta="SELECT fraccion FROM _587_tablaDinamica WHERE id__587_tablaDinamica =".$f;
					$fraccion=$con->obtenerValor($consulta);
				}
				array_push($arrFracciones,$fraccion);	
			}
			
			$arrValores["fraccionesActuales"]="";
			for($x=0;$x<sizeof($arrFracciones)-1;$x++)
			{
				if($arrValores["fraccionesActuales"]=="")
				{
					$arrValores["fraccionesActuales"]=$arrFracciones[$x];
				}
				else
				{
					$arrValores["fraccionesActuales"].=", ".$arrFracciones[$x];
				}
			}
			
			if($arrValores["fraccionesActuales"]=="")
			{
				$arrValores["fraccionesActuales"]=$arrFracciones[sizeof($arrFracciones)-1];
			}
			else
				$arrValores["fraccionesActuales"].=" y ".$arrFracciones[sizeof($arrFracciones)-1];
			
			
			$arrFraccionesCondiciones=array();
			
			$aFraccionesCondiciones=explode(",",$datosParametros->listaCondicionesActuales);
			
			foreach($aFraccionesCondiciones as $f)
			{
				$fraccion="OTRA";
				if($f!=0)
				{
					$consulta="SELECT fraccion FROM _587_tablaDinamica WHERE id__587_tablaDinamica =".$f;
					$fraccion=$con->obtenerValor($consulta);
				}
				array_push($arrFraccionesCondiciones,$fraccion);	
			}
			
			$arrValores["fraccionesCondicionesActuales"]="";
			for($x=0;$x<sizeof($arrFraccionesCondiciones)-1;$x++)
			{
				if($arrValores["fraccionesCondicionesActuales"]=="")
				{
					$arrValores["fraccionesCondicionesActuales"]=$arrFraccionesCondiciones[$x];
				}
				else
				{
					$arrValores["fraccionesCondicionesActuales"].=", ".$arrFraccionesCondiciones[$x];
				}
			}
			
			if($arrValores["fraccionesCondicionesActuales"]=="")
			{
				$arrValores["fraccionesCondicionesActuales"]=$arrFraccionesCondiciones[sizeof($arrFraccionesCondiciones)-1];
			}
			else
				$arrValores["fraccionesCondicionesActuales"].=" y ".$arrFraccionesCondiciones[sizeof($arrFraccionesCondiciones)-1];
				
				
				
			$leyendaMedidasCautelares="";
			$leyendaCondicionesSuspencion="";
			
			
			if(sizeof($arrFracciones)>0)
			{
				if(sizeof($arrFracciones)==1)
				{
					$leyendaMedidasCautelares="<b>LA MEDIDA CAUTELAR</b> prevista en la fracción ".$arrValores["fraccionesActuales"].
											" del artículo 155";
				}
				else
				{
					$leyendaMedidasCautelares="<b>LAS MEDIDAS CAUTELARES</b> previstas en la fracciones ".$arrValores["fraccionesActuales"].
											" del artículo 155";
				}
				
				if(sizeof($arrFraccionesCondiciones)==0)
					$leyendaMedidasCautelares.=" del Código Nacional de Procedimientos Penales";
			}
			
			if(sizeof($arrFraccionesCondiciones)>0)
			{
				if(sizeof($arrFraccionesCondiciones)==1)
				{
					$leyendaCondicionesSuspencion="<b>LA MEDIDA DE SUSPENSIÓN CONDICIONAL DEL PROCESO</b> prevista en la fracción ".$arrValores["fraccionesCondicionesActuales"].
											" del artículo 195 del Código Nacional de Procedimientos Penales";
				}
				else
				{
					$leyendaCondicionesSuspencion="<b>LAS MEDIDAS DE SUSPENSIÓN CONDICIONAL DEL PROCESO</b> previstas en las fracciones ".$arrValores["fraccionesCondicionesActuales"].
											" del artículo 195 del Código Nacional de Procedimientos Penales";
				}
			}
			
			
			$arrValores["lblMedidasCondiciones"]="<b>POR LO QUE QUEDA SUBSISTENTE</b>";
			if((sizeof($arrFracciones)+sizeof($arrFracciones))>1)
			{
				$arrValores["lblMedidasCondiciones"]="<b>POR LO QUE QUEDAN SUBSISTENTES</b>";
			}
			
			$arrValores["lblMedidasCondiciones2"]="";
			if(sizeof($arrFracciones)>0)
			{
				$arrValores["lblMedidasCondiciones2"]=$leyendaMedidasCautelares;
			}
			
			if(sizeof($arrFraccionesCondiciones)>0)
			{
				if($arrValores["lblMedidasCondiciones2"]=="")
					$arrValores["lblMedidasCondiciones2"]=$leyendaCondicionesSuspencion;
				else
					$arrValores["lblMedidasCondiciones2"].=" así como ".$leyendaCondicionesSuspencion;
				
				$arrValores["lblMedidasCondiciones2"].=" por el tiempo establecido de ".$datosParametros->periodoDuracion;
				
				switch($datosParametros->tiempoPeriodoDuracion)
				{
					case 1:
						$arrValores["lblMedidasCondiciones2"].=($datosParametros->periodoDuracion==1?" hora":" horas");
					break;
					case 2:
						$arrValores["lblMedidasCondiciones2"].=($datosParametros->periodoDuracion==1?" dia":" dias");
					break;
					case 3:
						$arrValores["lblMedidasCondiciones2"].=($datosParametros->periodoDuracion==1?" mes":" meses");
					break;
					case 4:
						$arrValores["lblMedidasCondiciones2"].=($datosParametros->periodoDuracion==1?" año":" años");
					break;
				}
			
			}
			$arrValores["oficioConocimiento"]=$datosParametros->oficioConocimiento;
			$arrValores["nombreUnidad"]="Unidad de Gestión Judicial en Materia de Justicia para Adolescentes";
			
			
			
			
		break;
		case 628:
		
			$idJuez=$datosParametros->juez;
			$consulta="SELECT Genero FROM 802_identifica WHERE idUsuario=".$idJuez;
			$generoJuez=$con->obtenerValor($consulta);
			$arrValores["nombreImputado"]=$arrValores["imputado"];
			$arrValores["juez"]=obtenerNombreUsuario($idJuez,true);
			$arrValores["arrLeyendaJuez"]=$generoJuez==0?"Juez":"Jueza"; 
			$arrValores["prefijoJuez"]=$generoJuez==0?"el":"la";  
			$arrValores["fechaAuto"]=convertirFechaLetra($datosParametros->fechaAuto,false,false);
			$arrValores["lblDelitos"]= $numDelitos>1?"los hechos que la ley establece como delitos":"el hecho que la ley establece como delito";
			$arrValores["lblDelitos2"]= $numDelitos>1?"cometidos":"cometido";
			$arrValores["lblVictimas"]= $numVictimas>1?"en agravio de las víctimas":"en agravio de la víctima";
			
			$consulta="SELECT idActividad,carpetaAdministrativaBase FROM 7006_carpetasAdministrativas WHERE carpetaAdministrativa='".$datosParametros->carpetaJudicial."'";
			$fDatosCarpeta=$con->obtenerPrimeraFila($consulta);
			
			$arrValores["carpetaAnterior"]=$fDatosCarpeta[1]==""?"______________":$fDatosCarpeta[1];
			$consulta="SELECT upper(CONCAT(IF(nombre IS NULL,'',nombre),' ',IF(apellidoPaterno IS NULL,'',apellidoPaterno),' ',IF(apellidoMaterno IS NULL,'',apellidoMaterno))) 
						FROM _47_tablaDinamica p where id__47_tablaDinamica in(".$fDatosCarpeta[0].
						") ORDER BY nombre,nombre,apellidoMaterno";
	
			$arrValores["imputado"]=$con->obtenerListaValores($consulta);
			$nImputados=$con->filasAfectadas;
			$arrValores["nombreImputado"]=$arrValores["imputado"];
			$arrValores["lblImputadas"]=$nImputados>1?"los menores ":"el menor ";
			$arrValores["lblImputadas2"]=$nImputados>1?"los menores mencionados":"el menor mencionado";
			
			
			$arrFracciones=array();
			
			$aFracciones=explode(",",$datosParametros->listaMedidasActuales);
			
			foreach($aFracciones as $f)
			{
				$fraccion="OTRA";
				if($f!=0)
				{
					$consulta="SELECT fraccion FROM _587_tablaDinamica WHERE id__587_tablaDinamica =".$f;
					$fraccion=$con->obtenerValor($consulta);
				}
				array_push($arrFracciones,$fraccion);	
			}
			
			$arrValores["fraccionesActuales"]="";
			for($x=0;$x<sizeof($arrFracciones)-1;$x++)
			{
				if($arrValores["fraccionesActuales"]=="")
				{
					$arrValores["fraccionesActuales"]=$arrFracciones[$x];
				}
				else
				{
					$arrValores["fraccionesActuales"].=", ".$arrFracciones[$x];
				}
			}
			
			if($arrValores["fraccionesActuales"]=="")
			{
				$arrValores["fraccionesActuales"]=$arrFracciones[sizeof($arrFracciones)-1];
			}
			else
				$arrValores["fraccionesActuales"].=" y ".$arrFracciones[sizeof($arrFracciones)-1];
			
			
			$arrFraccionesCondiciones=array();
			
			$aFraccionesCondiciones=explode(",",$datosParametros->listaCondicionesActuales);
			
			foreach($aFraccionesCondiciones as $f)
			{
				$fraccion="OTRA";
				if($f!=0)
				{
					$consulta="SELECT fraccion FROM _587_tablaDinamica WHERE id__587_tablaDinamica =".$f;
					$fraccion=$con->obtenerValor($consulta);
				}
				array_push($arrFraccionesCondiciones,$fraccion);	
			}
			
			$arrValores["fraccionesCondicionesActuales"]="";
			for($x=0;$x<sizeof($arrFraccionesCondiciones)-1;$x++)
			{
				if($arrValores["fraccionesCondicionesActuales"]=="")
				{
					$arrValores["fraccionesCondicionesActuales"]=$arrFraccionesCondiciones[$x];
				}
				else
				{
					$arrValores["fraccionesCondicionesActuales"].=", ".$arrFraccionesCondiciones[$x];
				}
			}
			
			if($arrValores["fraccionesCondicionesActuales"]=="")
			{
				$arrValores["fraccionesCondicionesActuales"]=$arrFraccionesCondiciones[sizeof($arrFraccionesCondiciones)-1];
			}
			else
				$arrValores["fraccionesCondicionesActuales"].=" y ".$arrFraccionesCondiciones[sizeof($arrFraccionesCondiciones)-1];
				
				
				
			$leyendaMedidasCautelares="";
			$leyendaCondicionesSuspencion="";
			
			
			if(sizeof($arrFracciones)>0)
			{
				if(sizeof($arrFracciones)==1)
				{
					$leyendaMedidasCautelares="<b>LA MEDIDA CAUTELAR</b> prevista en la fracción ".$arrValores["fraccionesActuales"].
											" del artículo 119";
				}
				else
				{
					$leyendaMedidasCautelares="<b>LAS MEDIDAS CAUTELARES</b> previstas en la fracciones ".$arrValores["fraccionesActuales"].
											" del artículo 119";
				}
				
				if(sizeof($arrFraccionesCondiciones)==0)
					$leyendaMedidasCautelares.=" de la Ley Nacional del Sistema Integral de Justicia Penal para Adolescentes";
			}
			
			if(sizeof($arrFraccionesCondiciones)>0)
			{
				if(sizeof($arrFraccionesCondiciones)==1)
				{
					$leyendaCondicionesSuspencion="<b>LA MEDIDA DE SUSPENSIÓN CONDICIONAL DEL PROCESO</b> prevista en la fracción ".$arrValores["fraccionesCondicionesActuales"].
											" del artículo 102 de la Ley Nacional del Sistema Integral de Justicia Penal para Adolescentes";
				}
				else
				{
					$leyendaCondicionesSuspencion="<b>LAS MEDIDAS DE SUSPENSIÓN CONDICIONAL DEL PROCESO</b> previstas en las fracciones ".$arrValores["fraccionesCondicionesActuales"].
											" del artículo 102 de la Ley Nacional del Sistema Integral de Justicia Penal para Adolescentes";
				}
			}
			
			
			$arrValores["lblMedidasCondiciones"]="<b>POR LO QUE QUEDA SUBSISTENTE</b>";
			if((sizeof($arrFracciones)+sizeof($arrFracciones))>1)
			{
				$arrValores["lblMedidasCondiciones"]="<b>POR LO QUE QUEDAN SUBSISTENTES</b>";
			}
			
			$arrValores["lblMedidasCondiciones2"]="";
			if(sizeof($arrFracciones)>0)
			{
				$arrValores["lblMedidasCondiciones2"]=$leyendaMedidasCautelares;
			}
			
			if(sizeof($arrFraccionesCondiciones)>0)
			{
				if($arrValores["lblMedidasCondiciones2"]=="")
					$arrValores["lblMedidasCondiciones2"]=$leyendaCondicionesSuspencion;
				else
					$arrValores["lblMedidasCondiciones2"].=" así como ".$leyendaCondicionesSuspencion;
				
				$arrValores["lblMedidasCondiciones2"].=" por el tiempo establecido de ".$datosParametros->periodoDuracion;
				
				switch($datosParametros->tiempoPeriodoDuracion)
				{
					case 1:
						$arrValores["lblMedidasCondiciones2"].=($datosParametros->periodoDuracion==1?" hora":" horas");
					break;
					case 2:
						$arrValores["lblMedidasCondiciones2"].=($datosParametros->periodoDuracion==1?" dia":" dias");
					break;
					case 3:
						$arrValores["lblMedidasCondiciones2"].=($datosParametros->periodoDuracion==1?" mes":" meses");
					break;
					case 4:
						$arrValores["lblMedidasCondiciones2"].=($datosParametros->periodoDuracion==1?" año":" años");
					break;
				}
			
			}
			$arrValores["oficioConocimiento"]=$datosParametros->oficioConocimiento;
			$arrValores["nombreUnidad"]="Unidad de Gestión Judicial en Materia de Justicia para Adolescentes";
			
			
			
			
		break;
		case 610:
			$consulta="SELECT horaInicioEvento,idSala,tipoAudiencia FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$datosParametros->audienciaCelebrar;
			$fAudiencia=$con->obtenerPrimeraFila($consulta);
			$consulta="SELECT tipoAudiencia FROM _4_tablaDinamica WHERE id__4_tablaDinamica=".$fAudiencia[2];
			$tipoAudiencia=$con->obtenerValor($consulta);
			
			$arrValores["tipoAudiencia"]=trim(str_replace("Audiencia de ","",$tipoAudiencia));
			$consulta="SELECT idJuez FROM 7001_eventoAudienciaJuez WHERE idRegistroEvento=".$datosParametros->audienciaCelebrar;
			$idJuez=$con->obtenerValor($consulta);
			$consulta="SELECT Genero FROM 802_identifica WHERE idUsuario=".$idJuez;
			
			$generoJuez=$con->obtenerValor($consulta);
			$arrValores["juez"]=obtenerNombreUsuario($idJuez,true);
			$arrValores["nombreImputado"]=$arrValores["imputado"];
			$arrValores["fechaAudiencia"]=convertirFechaLetra($fAudiencia[0],false,false);
			$arrValores["arrLeyendaJuez"]=$generoJuez==0?"Juez":"Jueza"; 
			
			$arrValores["prefijoJuez"]=$generoJuez==0?"el":"la";
			$arrValores["lblDelitos"]= $numDelitos>1?"los hechos que la ley establece como delitos":"el hecho que la ley establece como delito";
			$arrValores["lblDelitos2"]= $numDelitos>1?"cometidos":"cometido";
			$arrValores["lblVictimas"]= $numVictimas>1?"en agravio de las víctimas":"en agravio de la víctima";
			
			$consulta="SELECT carpetaAdministrativa,unidadGestion FROM 7006_carpetasAdministrativas c,7005_relacionFigurasJuridicasSolicitud r 
						WHERE carpetaAdministrativaBase='".$datosParametros->carpetaJudicial."' AND tipoCarpetaAdministrativa=5 AND r.idActividad=c.idActividad
						AND r.idParticipante in(".$datosParametros->imputados.")";
			
			$fCarpetaTE=$con->obtenerPrimeraFila($consulta);
			$unidadDestino="________________";
			$carpetaTE="________________";
			if($fCarpetaTE)
			{
				$consulta="SELECT nombreUnidad,claveFolioCarpetas,tipoMateria FROM _17_tablaDinamica WHERE  claveUnidad='".$fCarpetaTE[1]."'";
				$filaUGADestino=$con->obtenerPrimeraFila($consulta);
				
				$unidadDestino=$filaUGADestino[1]*1;
				
				$unidadDestino=convertirNumeroLetra($unidadDestino,false,false);
				if($unidadDestino=="UN")
					$unidadDestino="UNO";
		
				$carpetaTE=$fCarpetaTE[0];
			}
			$arrValores["unidadDestino"]=$unidadDestino;
			$arrValores["carpetaEnjuiciamiento"]=$carpetaTE;
			$arrValores["articulo"]=$datosParametros->articulo;
			
			
		break;
		case 629:
			$consulta="SELECT horaInicioEvento,idSala,tipoAudiencia FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$datosParametros->audienciaCelebrar;
			$fAudiencia=$con->obtenerPrimeraFila($consulta);
			$consulta="SELECT tipoAudiencia FROM _4_tablaDinamica WHERE id__4_tablaDinamica=".$fAudiencia[2];
			$tipoAudiencia=$con->obtenerValor($consulta);
			
			$arrValores["tipoAudiencia"]=trim(str_replace("Audiencia de ","",$tipoAudiencia));
			$consulta="SELECT idJuez FROM 7001_eventoAudienciaJuez WHERE idRegistroEvento=".$datosParametros->audienciaCelebrar;
			$idJuez=$con->obtenerValor($consulta);
			$consulta="SELECT Genero FROM 802_identifica WHERE idUsuario=".$idJuez;
			
			$generoJuez=$con->obtenerValor($consulta);
			$arrValores["juez"]=obtenerNombreUsuario($idJuez,true);
			$arrValores["nombreImputado"]=$arrValores["imputado"];
			$arrValores["fechaAudiencia"]=convertirFechaLetra($fAudiencia[0],false,false);
			$arrValores["arrLeyendaJuez"]=$generoJuez==0?"Juez":"Jueza"; 
			
			$arrValores["prefijoJuez"]=$generoJuez==0?"el":"la";
			$arrValores["lblDelitos"]= $numDelitos>1?"los hechos que la ley establece como delitos":"el hecho que la ley establece como delito";
			$arrValores["lblDelitos2"]= $numDelitos>1?"cometidos":"cometido";
			$arrValores["lblVictimas"]= $numVictimas>1?"en agravio de las víctimas":"en agravio de la víctima";
			
			$consulta="SELECT carpetaAdministrativa,unidadGestion FROM 7006_carpetasAdministrativas c,7005_relacionFigurasJuridicasSolicitud r 
						WHERE carpetaAdministrativaBase='".$datosParametros->carpetaJudicial."' AND tipoCarpetaAdministrativa=5 AND r.idActividad=c.idActividad
						AND r.idParticipante in(".$datosParametros->imputados.")";
			
			$fCarpetaTE=$con->obtenerPrimeraFila($consulta);
			$unidadDestino="________________";
			$carpetaTE="________________";
			if($fCarpetaTE)
			{
				$consulta="SELECT nombreUnidad,claveFolioCarpetas,tipoMateria FROM _17_tablaDinamica WHERE  claveUnidad='".$fCarpetaTE[1]."'";
				$filaUGADestino=$con->obtenerPrimeraFila($consulta);
				
				$unidadDestino=$filaUGADestino[1]*1;
				
				$unidadDestino=convertirNumeroLetra($unidadDestino,false,false);
				if($unidadDestino=="UN")
					$unidadDestino="UNO";
		
				$carpetaTE=$fCarpetaTE[0];
			}
			$arrValores["unidadDestino"]=$unidadDestino;
			$arrValores["carpetaEnjuiciamiento"]=$carpetaTE;
			$arrValores["articulo"]=$datosParametros->articulo;
			
			
		break;
		
		case 612:
		case 630:
			$consulta="SELECT horaInicioEvento,idSala,tipoAudiencia FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$datosParametros->audienciaCelebrar;
			$fAudiencia=$con->obtenerPrimeraFila($consulta);
			
			$consulta="SELECT idJuez FROM 7001_eventoAudienciaJuez WHERE idRegistroEvento=".$datosParametros->audienciaCelebrar;
			$idJuez=$con->obtenerValor($consulta);
			$consulta="SELECT Genero FROM 802_identifica WHERE idUsuario=".$idJuez;
			
			$generoJuez=$con->obtenerValor($consulta);
			$consulta="SELECT nombreSala FROM _15_tablaDinamica WHERE id__15_tablaDinamica=".$fAudiencia[1];
			$nombreSala=$con->obtenerValor($consulta);
			
			$consulta="SELECT tipoAudiencia FROM _4_tablaDinamica WHERE id__4_tablaDinamica=".$fAudiencia[2];
			$tipoAudiencia=$con->obtenerValor($consulta);
			
			$arrValores["tipoAudiencia"]=trim(str_replace("Audiencia de ","",$tipoAudiencia));
			$arrValores["nombreImputado"]=$arrValores["imputado"];
			$arrValores["fechaAudiencia"]=convertirFechaLetra($fAudiencia[0],false,false);
			$arrValores["horaAudiencia"]=date("H:i",strtotime($fAudiencia[0]));
			$arrValores["salaAudiencia"]=trim(str_replace("Sala","",$nombreSala))*1;
			
			$arrValores["arrLeyendaJuez"]=$generoJuez==0?"el Juez":"la Jueza"; 
			
			$consulta="SELECT idJuez FROM 7001_eventoAudienciaJuez WHERE idRegistroEvento=".$datosParametros->audienciaCelebrar;
			$idJuez=$con->obtenerValor($consulta);
			$consulta="SELECT Genero FROM 802_identifica WHERE idUsuario=".$idJuez;
			
			$generoJuez=$con->obtenerValor($consulta);
			$arrValores["juez"]=obtenerNombreUsuario($idJuez,true);
			$arrValores["nombreImputado"]=$arrValores["imputado"];
			$arrValores["fechaAudiencia"]=convertirFechaLetra($fAudiencia[0],false,false);
			$arrValores["arrLeyendaJuez"]=$generoJuez==0?"Juez":"Jueza"; 
			
			$arrValores["prefijoJuez"]=$generoJuez==0?"el":"la";
			$arrValores["lblDelitos"]= $numDelitos>1?"los hechos que la ley establece como delitos":"el hecho que la ley establece como delito";
			$arrValores["lblDelitos2"]= $numDelitos>1?"cometidos":"cometido";
			$arrValores["nombreUnidad"]="Unidad de Gestión Judicial en Materia de Justicia para Adolescentes";
			 
		break;
		case 613:
		case 631:
			$consulta="SELECT horaInicioEvento,idSala,tipoAudiencia FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$datosParametros->audienciaCelebrar;
			$fAudiencia=$con->obtenerPrimeraFila($consulta);
			
			$consulta="SELECT horaInicioEvento,idSala,tipoAudiencia FROM 7000_eventosAudiencia WHERE idRegistroEvento=".
					($datosParametros->audienciaMediasCautelares==""?-1:$datosParametros->audienciaMediasCautelares);
			$fAudienciaMedidas=$con->obtenerPrimeraFila($consulta);
			
			$consulta="SELECT idJuez FROM 7001_eventoAudienciaJuez WHERE idRegistroEvento=".$datosParametros->audienciaCelebrar;
			$idJuez=$con->obtenerValor($consulta);
			$consulta="SELECT Genero FROM 802_identifica WHERE idUsuario=".$idJuez;
			
			$generoJuez=$con->obtenerValor($consulta);
			$consulta="SELECT nombreSala FROM _15_tablaDinamica WHERE id__15_tablaDinamica=".$fAudiencia[1];
			$nombreSala=$con->obtenerValor($consulta);
			
			$consulta="SELECT tipoAudiencia FROM _4_tablaDinamica WHERE id__4_tablaDinamica=".$fAudiencia[2];
			$tipoAudiencia=$con->obtenerValor($consulta);
			
			$arrValores["tipoAudiencia"]=trim(str_replace("Audiencia de ","",$tipoAudiencia));
			$arrValores["nombreImputado"]=$arrValores["imputado"];
			$arrValores["fechaAudiencia"]=convertirFechaLetra($fAudiencia[0],false,false);
			$arrValores["horaAudiencia"]=date("H:i",strtotime($fAudiencia[0]));
			$arrValores["salaAudiencia"]=trim(str_replace("Sala","",$nombreSala))*1;
			
			$arrValores["arrLeyendaJuez"]=$generoJuez==0?"el Juez":"la Jueza"; 
			
			$consulta="SELECT idJuez FROM 7001_eventoAudienciaJuez WHERE idRegistroEvento=".$datosParametros->audienciaCelebrar;
			$idJuez=$con->obtenerValor($consulta);
			$consulta="SELECT Genero FROM 802_identifica WHERE idUsuario=".$idJuez;
			
			$generoJuez=$con->obtenerValor($consulta);
			$arrValores["juez"]=obtenerNombreUsuario($idJuez,true);
			$arrValores["nombreImputado"]=$arrValores["imputado"];
			$arrValores["fechaAudiencia"]=convertirFechaLetra($fAudiencia[0],false,false);
			$arrValores["arrLeyendaJuez"]=$generoJuez==0?"Juez":"Jueza"; 
			
			$arrValores["prefijoJuez"]=$generoJuez==0?"el":"la";
			$arrValores["lblDelitos"]= $numDelitos>1?"los hechos que la ley establece como delitos":"el hecho que la ley establece como delito";
			$arrValores["lblDelitos2"]= $numDelitos>1?"cometidos":"cometido";
			$arrValores["tipoCumplimiento"]= $datosParametros->tipoAcuerdo==1?"Inmediato":"Diferido";
			$arrValores["fechaAudienciaMedidas"]=convertirFechaLetra($fAudienciaMedidas[0],false,false);
			$arrValores["nombreUnidad"]="Unidad de Gestión Judicial en Materia de Justicia para Adolescentes";
		break;
		case 614:
		case 632:
			$consulta="SELECT horaInicioEvento,idSala,tipoAudiencia FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$datosParametros->audienciaCelebrar;
			$fAudiencia=$con->obtenerPrimeraFila($consulta);
			
			$consulta="SELECT idJuez FROM 7001_eventoAudienciaJuez WHERE idRegistroEvento=".$datosParametros->audienciaCelebrar;
			$idJuez=$con->obtenerValor($consulta);
			$consulta="SELECT Genero FROM 802_identifica WHERE idUsuario=".$idJuez;
			
			$generoJuez=$con->obtenerValor($consulta);
			$consulta="SELECT nombreSala FROM _15_tablaDinamica WHERE id__15_tablaDinamica=".$fAudiencia[1];
			$nombreSala=$con->obtenerValor($consulta);
			
			$consulta="SELECT tipoAudiencia FROM _4_tablaDinamica WHERE id__4_tablaDinamica=".$fAudiencia[2];
			$tipoAudiencia=$con->obtenerValor($consulta);
			
			$arrValores["tipoAudiencia"]=trim(str_replace("Audiencia de ","",$tipoAudiencia));
			$arrValores["nombreImputado"]=$arrValores["imputado"];
			$arrValores["fechaAudiencia"]=convertirFechaLetra($fAudiencia[0],false,false);
			$arrValores["horaAudiencia"]=date("H:i",strtotime($fAudiencia[0]));
			$arrValores["salaAudiencia"]=trim(str_replace("Sala","",$nombreSala))*1;
			$arrValores["juez"]=obtenerNombreUsuario($idJuez,true);
			$arrValores["arrLeyendaJuez"]=$generoJuez==0?"Juez":"Jueza"; 
			$arrValores["prefijoJuez"]=$generoJuez==0?"el":"la";  
			
			$arrFracciones=array();
			
			$aFracciones=explode(",",$datosParametros->listaMedidasActuales);
			
			foreach($aFracciones as $f)
			{
				$fraccion="OTRA";
				if($f!=0)
				{
					$consulta="SELECT fraccion FROM _587_tablaDinamica WHERE id__587_tablaDinamica =".$f;
					$fraccion=$con->obtenerValor($consulta);
				}
				array_push($arrFracciones,$fraccion);	
			}
			
			$arrValores["fraccionesActuales"]="";
			for($x=0;$x<sizeof($arrFracciones)-1;$x++)
			{
				if($arrValores["fraccionesActuales"]=="")
				{
					$arrValores["fraccionesActuales"]=$arrFracciones[$x];
				}
				else
				{
					$arrValores["fraccionesActuales"].=", ".$arrFracciones[$x];
				}
			}
			
			if($arrValores["fraccionesActuales"]=="")
			{
				$arrValores["fraccionesActuales"]=$arrFracciones[sizeof($arrFracciones)-1];
			}
			else
				$arrValores["fraccionesActuales"].=" y ".$arrFracciones[sizeof($arrFracciones)-1];
			$arrValores["lblFraccionesLeyenda"]=sizeof($arrFracciones)==1?"la medida cautelar prevista en la fracción":"las medidas cautelares previstas en las fracciones";
			$arrValores["lblFraccionesLeyenda2"]=sizeof($arrFracciones)==1?"la misma es <b>MODIFICADA</b>":"las mismas fueron <b>MODIFICADAS</b>";
			$arrValores["nombreUnidad"]="Unidad de Gestión Judicial en Materia de Justicia para Adolescentes";
		break;
		case 615:
		case 633:
			$consulta="SELECT horaInicioEvento,idSala,tipoAudiencia FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$datosParametros->audienciaCelebrar;
			$fAudiencia=$con->obtenerPrimeraFila($consulta);
			
		
			
			$consulta="SELECT idJuez FROM 7001_eventoAudienciaJuez WHERE idRegistroEvento=".$datosParametros->audienciaCelebrar;
			$idJuez=$con->obtenerValor($consulta);
			$consulta="SELECT Genero FROM 802_identifica WHERE idUsuario=".$idJuez;
			
			$generoJuez=$con->obtenerValor($consulta);
			$consulta="SELECT nombreSala FROM _15_tablaDinamica WHERE id__15_tablaDinamica=".$fAudiencia[1];
			$nombreSala=$con->obtenerValor($consulta);
			
			$consulta="SELECT tipoAudiencia FROM _4_tablaDinamica WHERE id__4_tablaDinamica=".$fAudiencia[2];
			$tipoAudiencia=$con->obtenerValor($consulta);
			
			$arrValores["tipoAudiencia"]=trim(str_replace("Audiencia de ","",$tipoAudiencia));
			$arrValores["nombreImputado"]=$arrValores["imputado"];
			$arrValores["fechaAudiencia"]=convertirFechaLetra($fAudiencia[0],false,false);
			$arrValores["horaAudiencia"]=date("H:i",strtotime($fAudiencia[0]));
			$arrValores["salaAudiencia"]=trim(str_replace("Sala","",$nombreSala))*1;
			
			$arrValores["arrLeyendaJuez"]=$generoJuez==0?"el Juez":"la Jueza"; 
			
			$consulta="SELECT idJuez FROM 7001_eventoAudienciaJuez WHERE idRegistroEvento=".$datosParametros->audienciaCelebrar;
			$idJuez=$con->obtenerValor($consulta);
			$consulta="SELECT Genero FROM 802_identifica WHERE idUsuario=".$idJuez;
			
			$generoJuez=$con->obtenerValor($consulta);
			$arrValores["juez"]=obtenerNombreUsuario($idJuez,true);
			$arrValores["nombreImputado"]=$arrValores["imputado"];
			$arrValores["fechaAudiencia"]=convertirFechaLetra($fAudiencia[0],false,false);
			$arrValores["arrLeyendaJuez"]=$generoJuez==0?"Juez":"Jueza"; 
			
			$arrValores["prefijoJuez"]=$generoJuez==0?"el":"la";
			$arrValores["lblDelitos"]= $numDelitos>1?"los hechos que la ley establece como delitos":"el hecho que la ley establece como delito";
			$arrValores["lblDelitos2"]= $numDelitos>1?"cometidos":"cometido";
			$arrValores["nombreUnidad"]="Unidad de Gestión Judicial en Materia de Justicia para Adolescentes";
			 
		break;
		case 616:
		case 634:
			$consulta="SELECT horaInicioEvento,idSala,tipoAudiencia FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$datosParametros->audienciaCelebrar;
			$fAudiencia=$con->obtenerPrimeraFila($consulta);
			
			$consulta="SELECT idJuez FROM 7001_eventoAudienciaJuez WHERE idRegistroEvento=".$datosParametros->audienciaCelebrar;
			$idJuez=$con->obtenerValor($consulta);
			$consulta="SELECT Genero FROM 802_identifica WHERE idUsuario=".$idJuez;
			
			$generoJuez=$con->obtenerValor($consulta);
			
			$consulta="SELECT tipoAudiencia FROM _4_tablaDinamica WHERE id__4_tablaDinamica=".$fAudiencia[2];
			$tipoAudiencia=$con->obtenerValor($consulta);
			$arrValores["tipoAudiencia"]= trim(str_replace("Audiencia de ","",$tipoAudiencia));
			$arrValores["juez"]=obtenerNombreUsuario($idJuez,true);
			$arrValores["nombreImputado"]=$arrValores["imputado"];
			$arrValores["fechaAudiencia"]=convertirFechaLetra($fAudiencia[0],false,false);
			$arrValores["arrLeyendaJuez"]=$generoJuez==0?"Juez":"Jueza"; 
			$arrValores["lblDelitos"]= $numDelitos>1?"los hechos que la ley establece como delitos":"el hecho que la ley establece como delito";
			$arrValores["lblDelitos2"]= $numDelitos>1?"cometidos":"cometido";
			$arrValores["lblVictimas"]= $numVictimas>1?"en agravio de las víctimas":"en agravio de la víctima";
			
			
			$fracciones="";
			$lblLeyendas="";
			$lblLeyenda="";
			$pFraccion=1;
			foreach($datosParametros->medidadCautelares as $m)
			{
				
				
				
				
				if($m->idMedidaCautelar!=0)
				{
					$consulta="SELECT fraccion,leyenda FROM _587_tablaDinamica WHERE id__587_tablaDinamica =".$m->idMedidaCautelar." ORDER BY id__587_tablaDinamica";
					$fLeyenda=$con->obtenerPrimeraFila($consulta);
					$lblFraccion=$fLeyenda[0];
					$lblLeyenda=$pFraccion.".- ".$fLeyenda[1];
				}
				else
				{
					
					$lblFraccion="otro";
					$lblLeyenda=$pFraccion.".- Otro";
					
				}
				if($m->detalles!="")
				{
					if($lblLeyenda=="")
						$lblLeyenda=$m->detalles;
					else
						$lblLeyenda.=": ".$m->detalles;
				}
				
				
				if($lblLeyendas=="")
					$lblLeyendas="<p>".$lblLeyenda.".</p>";
				else
					$lblLeyendas.="<p>".$lblLeyenda.".</p>";
				
				if($fracciones=="")
					  $fracciones=$lblFraccion;
				 else
				 {
					 if($pFraccion<sizeof($datosParametros->medidadCautelares))
						$fracciones.=", ".$lblFraccion;
					else
						$fracciones.=" y ".$lblFraccion;
				 
				 
				 
				 }
					
				
				
				$pFraccion++;
			}
			$pFraccion--;
			$plazoPresentacion="";
			if($datosParametros->tipoPeriodoLimite==2)
			{
				$plazoPresentacion=" el día ".convertirFechaLetra($datosParametros->fechaLimite,false,false);
			}
			else
			{
				
				$plazoPresentacion=" en ".$datosParametros->periodoLimite;
				switch($datosParametros->plazoPeriodoLimite)
				{
					case 1:
						$plazoPresentacion.=($datosParametros->periodoLimite==1?" hora":" horas");
					break;
					case 2:
						$plazoPresentacion.=($datosParametros->periodoLimite==1?" dia":" dias");
					break;
					case 3:
						$plazoPresentacion.=($datosParametros->periodoLimite==1?" mes":" meses");
					break;
					case 4:
						$plazoPresentacion.=($datosParametros->periodoLimite==1?" año":" años");
					break;
				}
			}
			
			$arrValores["fracciones"]=$fracciones;
			$arrValores["plazoPresentacion"]=$plazoPresentacion;
			$arrValores["medidasCautelares"]=$lblLeyendas;
			$arrValores["lblFracciones"]=" ".$arrValores["fracciones"];
			$arrValores["lblMedidasImpuestas"]=$pFraccion==1?" la medida cautelar establecida":" las medidas cautelares establecidas";
			$arrValores["lblFraccion2"]=$pFraccion==1?"fracción":"fracciones";
			$arrValores["lblConsistente"]=$pFraccion==1?"consistente":"consistentes";
			$arrValores["prefijoJuez"]=$generoJuez==0?"el":"la"; 
			
			
			$arrFracciones=array();
			
			$aFracciones=explode(",",$datosParametros->listaMedidasActuales);
			
			foreach($aFracciones as $f)
			{
				$fraccion="OTRA";
				if($f!=0)
				{
					$consulta="SELECT fraccion FROM _587_tablaDinamica WHERE id__587_tablaDinamica =".$f;
					$fraccion=$con->obtenerValor($consulta);
				}
				array_push($arrFracciones,$fraccion);	
			}
			
			$arrValores["fraccionesActuales"]="";
			for($x=0;$x<sizeof($arrFracciones)-1;$x++)
			{
				if($arrValores["fraccionesActuales"]=="")
				{
					$arrValores["fraccionesActuales"]=$arrFracciones[$x];
				}
				else
				{
					$arrValores["fraccionesActuales"].=", ".$arrFracciones[$x];
				}
			}
			
			if($arrValores["fraccionesActuales"]=="")
			{
				$arrValores["fraccionesActuales"]=$arrFracciones[sizeof($arrFracciones)-1];
			}
			else
				$arrValores["fraccionesActuales"].=" y ".$arrFracciones[sizeof($arrFracciones)-1];
			$arrValores["lblFraccionesLeyenda"]=sizeof($arrFracciones)==1?"la MEDIDA CAUTELAR establecida en la fracción":"las MEDIDAS CAUTELARES establecidas en las fracciones";
			$arrValores["lblFraccionesLeyenda2"]=sizeof($arrFracciones)==1?"la misma es <b>MODIFICADA</b>":"las mismas fueron <b>MODIFICADAS</b>";
			$arrValores["nombreUnidad"]="Unidad de Gestión Judicial en Materia de Justicia para Adolescentes";
		break;
		case 617:
		case 635:
			$consulta="SELECT horaInicioEvento,idSala,tipoAudiencia FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$datosParametros->audienciaCelebrar;
			$fAudiencia=$con->obtenerPrimeraFila($consulta);
			
		
			
			$consulta="SELECT idJuez FROM 7001_eventoAudienciaJuez WHERE idRegistroEvento=".$datosParametros->audienciaCelebrar;
			$idJuez=$con->obtenerValor($consulta);
			$consulta="SELECT Genero FROM 802_identifica WHERE idUsuario=".$idJuez;
			
			$generoJuez=$con->obtenerValor($consulta);
			$consulta="SELECT nombreSala FROM _15_tablaDinamica WHERE id__15_tablaDinamica=".$fAudiencia[1];
			$nombreSala=$con->obtenerValor($consulta);
			
			$consulta="SELECT tipoAudiencia FROM _4_tablaDinamica WHERE id__4_tablaDinamica=".$fAudiencia[2];
			$tipoAudiencia=$con->obtenerValor($consulta);
			
			$arrValores["tipoAudiencia"]=trim(str_replace("Audiencia de ","",$tipoAudiencia));
			$arrValores["nombreImputado"]=$arrValores["imputado"];
			$arrValores["fechaAudiencia"]=convertirFechaLetra($fAudiencia[0],false,false);
			$arrValores["horaAudiencia"]=date("H:i",strtotime($fAudiencia[0]));
			$arrValores["salaAudiencia"]=trim(str_replace("Sala","",$nombreSala))*1;
			
			$arrValores["arrLeyendaJuez"]=$generoJuez==0?"el Juez":"la Jueza"; 
			
			$consulta="SELECT idJuez FROM 7001_eventoAudienciaJuez WHERE idRegistroEvento=".$datosParametros->audienciaCelebrar;
			$idJuez=$con->obtenerValor($consulta);
			$consulta="SELECT Genero FROM 802_identifica WHERE idUsuario=".$idJuez;
			
			$generoJuez=$con->obtenerValor($consulta);
			$arrValores["juez"]=obtenerNombreUsuario($idJuez,true);
			$arrValores["nombreImputado"]=$arrValores["imputado"];
			$arrValores["fechaAudiencia"]=convertirFechaLetra($fAudiencia[0],false,false);
			$arrValores["arrLeyendaJuez"]=$generoJuez==0?"Juez":"Jueza"; 
			$arrValores["fechaAutoEjecutoria"]=convertirFechaLetra($datosParametros->fechaAuto,false,false);
			$arrValores["textoMedidas"]=$datosParametros->textoMedidas;
			$arrValores["prefijoJuez"]=$generoJuez==0?"el":"la";
			$arrValores["lblDelitos"]= $numDelitos>1?"los hechos que la ley establece como delitos":"el hecho que la ley establece como delito";
			$arrValores["lblDelitos2"]= $numDelitos>1?"cometidos":"cometido";
			$arrValores["lblVictimas"]= $numVictimas>1?"en agravio de las víctimas":"en agravio de la víctima";
			$arrValores["nombreUnidad"]="Unidad de Gestión Judicial en Materia de Justicia para Adolescentes";
			 
		break;
		case 618:
		case 636:
			$consulta="SELECT horaInicioEvento,idSala,tipoAudiencia FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$datosParametros->audienciaCelebrar;
			$fAudiencia=$con->obtenerPrimeraFila($consulta);
			
		
			
			$consulta="SELECT idJuez FROM 7001_eventoAudienciaJuez WHERE idRegistroEvento=".$datosParametros->audienciaCelebrar;
			$idJuez=$con->obtenerValor($consulta);
			$consulta="SELECT Genero FROM 802_identifica WHERE idUsuario=".$idJuez;
			
			$generoJuez=$con->obtenerValor($consulta);
			$consulta="SELECT nombreSala FROM _15_tablaDinamica WHERE id__15_tablaDinamica=".$fAudiencia[1];
			$nombreSala=$con->obtenerValor($consulta);
			
			$consulta="SELECT tipoAudiencia FROM _4_tablaDinamica WHERE id__4_tablaDinamica=".$fAudiencia[2];
			$tipoAudiencia=$con->obtenerValor($consulta);
			
			$arrValores["tipoAudiencia"]=trim(str_replace("Audiencia de ","",$tipoAudiencia));
			$arrValores["nombreImputado"]=$arrValores["imputado"];
			$arrValores["fechaAudiencia"]=convertirFechaLetra($fAudiencia[0],false,false);
			$arrValores["horaAudiencia"]=date("H:i",strtotime($fAudiencia[0]));
			$arrValores["salaAudiencia"]=trim(str_replace("Sala","",$nombreSala))*1;
			
			$arrValores["arrLeyendaJuez"]=$generoJuez==0?"el Juez":"la Jueza"; 
			
			$consulta="SELECT idJuez FROM 7001_eventoAudienciaJuez WHERE idRegistroEvento=".$datosParametros->audienciaCelebrar;
			$idJuez=$con->obtenerValor($consulta);
			$consulta="SELECT Genero FROM 802_identifica WHERE idUsuario=".$idJuez;
			
			$generoJuez=$con->obtenerValor($consulta);
			$arrValores["juez"]=obtenerNombreUsuario($idJuez,true);
			$arrValores["nombreImputado"]=$arrValores["imputado"];
			$arrValores["fechaAudiencia"]=convertirFechaLetra($fAudiencia[0],false,false);
			$arrValores["arrLeyendaJuez"]=$generoJuez==0?"Juez":"Jueza"; 
			$arrValores["fechaAuto"]=convertirFechaLetra($datosParametros->fechaAuto,false,false);
			$arrValores["numeral"]=$datosParametros->numeralPlazo;
			$arrValores["prefijoJuez"]=$generoJuez==0?"el":"la";
			$arrValores["lblDelitos"]= $numDelitos>1?"los hechos que la ley establece como delitos":"el hecho que la ley establece como delito";
			$arrValores["lblDelitos2"]= $numDelitos>1?"cometidos":"cometido";
			$arrValores["lblVictimas"]= $numVictimas>1?"en agravio de las víctimas":"en agravio de la víctima";
			$arrValores["nombreUnidad"]="Unidad de Gestión Judicial en Materia de Justicia para Adolescentes";
			 
		break;
		case 638:
			$consulta="SELECT horaInicioEvento,idSala,tipoAudiencia FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$datosParametros->audienciaCelebrar;
			$fAudiencia=$con->obtenerPrimeraFila($consulta);
			
			$consulta="SELECT idJuez FROM 7001_eventoAudienciaJuez WHERE idRegistroEvento=".$datosParametros->audienciaCelebrar;
			$idJuez=$con->obtenerValor($consulta);
			$consulta="SELECT Genero FROM 802_identifica WHERE idUsuario=".$idJuez;
			
			$generoJuez=$con->obtenerValor($consulta);
			
			$consulta="SELECT nombreSala FROM _15_tablaDinamica WHERE id__15_tablaDinamica=".$fAudiencia[1];
			$nombreSala=$con->obtenerValor($consulta);
			
			$consulta="SELECT tipoAudiencia FROM _4_tablaDinamica WHERE id__4_tablaDinamica=".$fAudiencia[2];
			$tipoAudiencia=$con->obtenerValor($consulta);
			
			$arrValores["tipoAudiencia"]=trim(str_replace("Audiencia de ","",$tipoAudiencia));
			$arrValores["nombreImputado"]=$arrValores["imputado"];
			$arrValores["fechaAudiencia"]=convertirFechaLetra($fAudiencia[0],false,false);
			$arrValores["horaAudiencia"]=date("H:i",strtotime($fAudiencia[0]));
			$arrValores["salaAudiencia"]=trim(str_replace("Sala","",$nombreSala))*1;
			
			$arrValores["prefijoJuez"]=$generoJuez==0?"el Juez":"la Jueza"; 
			$arrValores["juez"]=obtenerNombreUsuario($idJuez);
			
			$arrValores["comunidad"]=$datosParametros->comunidadInternamiento;
			$arrValores["correoElectronico"]=$datosParametros->emailEvaluacion;
			$arrValores["fechaEntrega"]=convertirFechaLetra($datosParametros->fechaEvaluacion,false,false);
			
			$arrValores["arrLeyendaJuez"]=$generoJuez==0?"el Juez":"la Jueza"; 
			$arrValores["nombreUnidad"]="Unidad de Gestión Judicial en Materia de Justicia para Adolescentes";
			
		break;
		case 639:
			$consulta="SELECT horaInicioEvento,idSala,tipoAudiencia FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$datosParametros->audienciaCelebrar;
			$fAudiencia=$con->obtenerPrimeraFila($consulta);
			
			$consulta="SELECT idJuez FROM 7001_eventoAudienciaJuez WHERE idRegistroEvento=".$datosParametros->audienciaCelebrar;
			$idJuez=$con->obtenerValor($consulta);
			$consulta="SELECT Genero FROM 802_identifica WHERE idUsuario=".$idJuez;
			
			$generoJuez=$con->obtenerValor($consulta);
			
			$consulta="SELECT nombreSala FROM _15_tablaDinamica WHERE id__15_tablaDinamica=".$fAudiencia[1];
			$nombreSala=$con->obtenerValor($consulta);
			
			$consulta="SELECT tipoAudiencia FROM _4_tablaDinamica WHERE id__4_tablaDinamica=".$fAudiencia[2];
			$tipoAudiencia=$con->obtenerValor($consulta);
			
			$arrValores["tipoAudiencia"]=trim(str_replace("Audiencia de ","",$tipoAudiencia));
			$arrValores["nombreImputado"]=$arrValores["imputado"];
			$arrValores["fechaAudiencia"]=convertirFechaLetra($fAudiencia[0],false,false);
			$arrValores["horaAudiencia"]=date("H:i",strtotime($fAudiencia[0]));
			$arrValores["salaAudiencia"]=trim(str_replace("Sala","",$nombreSala))*1;
			
			$arrValores["prefijoJuez"]=$generoJuez==0?"el Juez":"la Jueza"; 
			$arrValores["juez"]=obtenerNombreUsuario($idJuez);
			
			$arrValores["nombreClinica"]=$datosParametros->clinicaValoracion;
			$arrValores["tipoTratamiento"]=$datosParametros->cmbTipoTratamiento==1?"residencial":"ambulatoria";

			
			$arrValores["arrLeyendaJuez"]=$generoJuez==0?"el Juez":"la Jueza"; 
			$arrValores["nombreUnidad"]="Unidad de Gestión Judicial en Materia de Justicia para Adolescentes";
			
		break;
		case 640:
			
			
			$consulta="SELECT Genero FROM 802_identifica WHERE idUsuario=".$datosParametros->juezOrdena;
			
			$generoJuez=$con->obtenerValor($consulta);
			
			
			
			$arrValores["nombreImputado"]=$arrValores["imputado"];
			$arrValores["fechaCondiciones"]=convertirFechaLetra($datosParametros->fechaCondiciones,false,false);
			
			
			$arrValores["prefijoJuez"]=$generoJuez==0?"el Juez":"la Jueza"; 
			$arrValores["juez"]=obtenerNombreUsuario($datosParametros->juezOrdena);
			
			$arrValores["arrLeyendaJuez"]=$generoJuez==0?"el Juez":"la Jueza"; 
			$arrValores["nombreUnidad"]="Unidad de Gestión Judicial en Materia de Justicia para Adolescentes";
			
		break;
	}
	
	
	return $arrValores;
}

?>