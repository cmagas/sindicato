<?php 
include_once("latis/conexionBD.php");
include_once("latis/utiles.php");
include_once("latis/numeroToLetra.php");

function funcionLlenadoRespuestaAdultoMedidaCautelar($idRegistro)
{
	global $con;
	global $arrMesLetra;
	global $leyendaTribunal;
	$arrValores=array();
	$consulta="SELECT datosParametros,carpetaAdministrativa,idFormulario,idReferencia FROM 7035_informacionDocumentos WHERE idRegistro=".$idRegistro;
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
		
	$delitos=$con->obtenerValor($consulta);
	
	$consulta="SELECT upper(CONCAT(IF(nombre IS NULL,'',nombre),' ',IF(apellidoPaterno IS NULL,'',apellidoPaterno),' ',IF(apellidoMaterno IS NULL,'',apellidoMaterno))) 
				FROM _47_tablaDinamica p where id__47_tablaDinamica in(".$datosParametros->imputados.
				") ORDER BY nombre,nombre,apellidoMaterno";
	
	
	$arrValores["fechaActual"]=convertirFechaLetra(date("Y-m-d"),false,false);
	$arrValores["carpetaJudicial"]=$datosParametros->carpetaJudicial;
	$arrValores["carpetaInvestigacion"]=$filaCarpeta[0];
	$arrValores["imputado"]=$con->obtenerListaValores($consulta);
	$arrValores["nombreImputado"]=$con->obtenerListaValores($consulta);
	$arrValores["lblCuenta"]=$con->filasAfectadas==1?"cuenta":"cuentan";
	$arrValores["delito"]=$delitos;
	$arrValores["oficioOriginal"]=$datosParametros->oficioResponde==""?"_______":$datosParametros->oficioResponde;
	$arrValores["fechaOficio"]=$datosParametros->fechaOficioResponde=="_______"?"":date("d/m/Y",strtotime($datosParametros->fechaOficioResponde));
	$arrValores["sinNumero"]=$arrValores["oficioOriginal"]==""?"sin ":"";
	$arrValores["vinculacionProceso"]=$datosParametros->fechaVinculacionProceso!="_______"?date("d/m/Y",strtotime($datosParametros->fechaVinculacionProceso)):"";
	$arrValores["noOficio"]=$datosParametros->noOficioAsignar==""?("____/".date("Y")):(str_pad($datosParametros->noOficioAsignar,4,"0",STR_PAD_LEFT)."/".date("Y"));

	
	$arrValores["leyendaTribunal"]=str_replace("<br>", " ",$leyendaTribunal);	
	$arrValores["nombreDestinatario"]=mb_strtoupper($datosParametros->nombreDestinatario==""?"_______":$datosParametros->nombreDestinatario);
	$arrValores["puestoDestinatario"]=mb_strtoupper($datosParametros->puestoDestinatario==""?"_______":$datosParametros->puestoDestinatario);
	$arrValores["lblInforme"]="";
	$arrValores["nombreElaboro"]=mb_strtoupper(obtenerNombreUsuario($_SESSION["idUsr"]));
	
	$consulta="SELECT TRIM(CONCAT(IF(Prefijo IS NULL,'',Prefijo),' ',i.Nombre)) FROM 802_identifica i,800_usuarios u WHERE i.idUsuario IN(
				SELECT idUsuario FROM 807_usuariosVSRoles WHERE idRol=188) AND u.idUsuario=i.idUsuario AND 
				u.cuentaActiva=1 ORDER BY u.Nombre";
	$nombreJefeUnidadDepartamental=$con->obtenerListaValores($consulta);
	$arrValores["nombreJefeUnidadDepartamental"]=($nombreJefeUnidadDepartamental);
	
	
	if(($datosParametros->existeMedidaCautelar==0)&&($datosParametros->existeSuspencionCP==0))
	{
		$arrValores["lblInforme"]='- Tras una búsqueda en los archivos de esta Dirección Ejecutiva, no se encontró registro en el que se aprecie resolución, donde se haya decretado alguna medida cautelar o suspensión condicional del proceso.';
	}
	else
	{
		$fracciones="";
		foreach($datosParametros->medidasCautelares as $m)
		{
			$carpetaJudicial=$m->carpetaAdministrativa;
			for($pFraccion=0;$pFraccion<sizeof($m->fracciones);$pFraccion++)
			{
				$f=$m->fracciones[$pFraccion];
				$lblFraccion="";
				if($f->idRegistro!=0)
				{
					$consulta="SELECT fraccion FROM _587_tablaDinamica WHERE id__587_tablaDinamica =".$f->idRegistro." ORDER BY id__587_tablaDinamica";
					$lblFraccion=$con->obtenerValor($consulta);
				}
				else
				{
					
					$lblFraccion="otro";
					
				}
				if($f->detallesAdicionales!="")
				{
					if($lblFraccion=="")
						$lblFraccion=$f->detallesAdicionales;
					else
						$lblFraccion.=": ".$f->detallesAdicionales;
				}
				if($fracciones=="")
					  $fracciones=$lblFraccion;
				 else
				 {
					 if($pFraccion<sizeof($m->fracciones)-1)
						$fracciones.=", ".$lblFraccion;
					else
				 		$fracciones.=" y ".$lblFraccion;
				 
				 
				 
				 }
				
			}
			
			$arrValores["lblInforme"].='- Tras una búsqueda en los archivos de esta Dirección Ejecutiva, a nombre de '.$arrValores["nombreImputado"].
									' se encontró el registro relacionado con la carpeta judicial número '.$carpetaJudicial.
									', donde se impuso '.($con->filasAfectadas==1?'<b>medida cautelar</b> fracción '.$fracciones:'las <b>medidas cautelares</b> fracción: '.
									$fracciones).' del artículo 155 del Código Nacional de Procedimientos Penales.<br><br>';
			
		}
		
		$fracciones="";
		foreach($datosParametros->suspencionesCondicional as $s)
		{
			$carpetaJudicial=$s->carpetaAdministrativa;
			for($pFraccion=0;$pFraccion<sizeof($s->fracciones);$pFraccion++)
			{
				$f=$s->fracciones[$pFraccion];
				$lblFraccion="";
				if($f->idRegistro!=0)
				{
					$consulta="SELECT fraccion FROM _587_tablaDinamica WHERE id__587_tablaDinamica =".$f->idRegistro." ORDER BY id__587_tablaDinamica";
					$lblFraccion=$con->obtenerValor($consulta);
				}
				else
				{
					$lblFraccion="otro";
					
				}
				if($f->detallesAdicionales!="")
				{
					if($lblFraccion=="")
						$lblFraccion=$f->detallesAdicionales;
					else
						$lblFraccion.=": ".$f->detallesAdicionales;
				}
				
				if($fracciones=="")
					  $fracciones=$lblFraccion;
				 else
				 {
					 if($pFraccion<sizeof($s->fracciones)-1)
						$fracciones.=", ".$lblFraccion;
					else
				 		$fracciones.=" y ".$lblFraccion;
				 }
				
			}
			
			$arrValores["lblInforme"].='- Tras una búsqueda en los archivos de esta Dirección Ejecutiva, a nombre de '.$arrValores["nombreImputado"].
									' se encontró el registro relacionado con la carpeta judicial número '.$carpetaJudicial.
									', donde se decretó la <b>suspensión condicional del proceso</b>'.($con->filasAfectadas==1?' fracción '.$fracciones:' fracciones: '.
									$fracciones).' del artículo 195 del Código Nacional de Procedimientos Penales.<br><br>';
			
		}
		
		
		
	}
	
	
	
	return $arrValores;
}

function funcionLlenadoRespuestaAdolescentesMedidaCautelar($idRegistro)
{
	global $con;
	global $arrMesLetra;
	global $leyendaTribunal;
	
	$arrValores=array();
	$consulta="SELECT datosParametros,carpetaAdministrativa,idFormulario,idReferencia FROM 7035_informacionDocumentos WHERE idRegistro=".$idRegistro;
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
		
	$delitos=$con->obtenerValor($consulta);
	
	$consulta="SELECT upper(CONCAT(IF(nombre IS NULL,'',nombre),' ',IF(apellidoPaterno IS NULL,'',apellidoPaterno),' ',IF(apellidoMaterno IS NULL,'',apellidoMaterno))) 
				FROM _47_tablaDinamica p where id__47_tablaDinamica in(".$datosParametros->imputados.
				") ORDER BY nombre,nombre,apellidoMaterno";
	
	
	$arrValores["fechaActual"]=convertirFechaLetra(date("Y-m-d"),false,false);
	$arrValores["carpetaJudicial"]=$datosParametros->carpetaJudicial;
	$arrValores["carpetaInvestigacion"]=$filaCarpeta[0];
	$arrValores["imputado"]=$con->obtenerListaValores($consulta);
	$arrValores["nombreImputado"]=$con->obtenerListaValores($consulta);
	$arrValores["lblCuenta"]=$con->filasAfectadas==1?"cuenta":"cuentan";
	$arrValores["delito"]=$delitos;
	$arrValores["oficioOriginal"]=$datosParametros->oficioResponde==""?"_______":$datosParametros->oficioResponde;
	$arrValores["fechaOficio"]=$datosParametros->fechaOficioResponde=="_______"?"":date("d/m/Y",strtotime($datosParametros->fechaOficioResponde));
	$arrValores["sinNumero"]=$arrValores["oficioOriginal"]==""?"sin ":"";
	$arrValores["vinculacionProceso"]=$datosParametros->fechaVinculacionProceso!="_______"?date("d/m/Y",strtotime($datosParametros->fechaVinculacionProceso)):"";
	$arrValores["noOficio"]=$datosParametros->noOficioAsignar==""?("____/".date("Y")):(str_pad($datosParametros->noOficioAsignar,4,"0",STR_PAD_LEFT)."/".date("Y"));
	$arrValores["leyendaTribunal"]=str_replace("<br>", " ",$leyendaTribunal);	
	$arrValores["nombreDestinatario"]=mb_strtoupper($datosParametros->nombreDestinatario==""?"_______":$datosParametros->nombreDestinatario);
	$arrValores["puestoDestinatario"]=mb_strtoupper($datosParametros->puestoDestinatario==""?"_______":$datosParametros->puestoDestinatario);
	$arrValores["puestoDestinatario"]=str_replace("\n","<br>",$arrValores["puestoDestinatario"]);
	$arrValores["lblInforme"]="";
	$arrValores["nombreElaboro"]=mb_strtoupper(obtenerNombreUsuario($_SESSION["idUsr"]));
	
	if(($datosParametros->existeMedidaCautelar==0)&&($datosParametros->existeSuspencionCP==0))
	{
		$arrValores["lblInforme"]='- Tras una búsqueda en los archivos de esta Dirección Ejecutiva, no se encontró registro en el que se aprecie resolución, donde se haya decretado alguna medida cautelar o suspensión condicional del proceso.';
	}
	else
	{
		$fracciones="";
		foreach($datosParametros->medidasCautelares as $m)
		{
			$carpetaJudicial=$m->carpetaAdministrativa;
			for($pFraccion=0;$pFraccion<sizeof($m->fracciones);$pFraccion++)
			{
				$f=$m->fracciones[$pFraccion];
				$lblFraccion="";
				if($f->idRegistro!=0)
				{
					$consulta="SELECT fraccion FROM _587_tablaDinamica WHERE id__587_tablaDinamica =".$f->idRegistro." ORDER BY id__587_tablaDinamica";
					$lblFraccion=$con->obtenerValor($consulta);
				}
				else
				{
					/*if($f->detallesAdicionales=="")
					{
						$lblFraccion="otro";
					}*/
					$lblFraccion="otro";
				}
				if($f->detallesAdicionales!="")
				{
					if($lblFraccion=="")
						$lblFraccion=$f->detallesAdicionales;
					else
						$lblFraccion.=": ".$f->detallesAdicionales;
				}
				if($fracciones=="")
					  $fracciones=$lblFraccion;
				 else
				 {
					 if($pFraccion<sizeof($m->fracciones)-1)
						$fracciones.=", ".$lblFraccion;
					else
				 		$fracciones.=" y ".$lblFraccion;
				 
				 
				 
				 }
				
			}
			$arrValores["lblInforme"].='- Tras una búsqueda en los archivos de esta Dirección Ejecutiva, a nombre de '.$arrValores["nombreImputado"].
									' se encontró el registro relacionado con la carpeta judicial número '.$carpetaJudicial.
									', donde se impuso '.($con->filasAfectadas==1?'<b>medida cautelar</b> fracción '.$fracciones:'las <b>medidas cautelares</b> fracción: '.
									$fracciones).' del artículo 119 de la Ley Nacional del Sistema Integral de Justicia Penal para Adolescentes.<br><br>';
			
		}
		$fracciones="";

		foreach($datosParametros->suspencionesCondicional as $s)
		{
			$carpetaJudicial=$s->carpetaAdministrativa;
			for($pFraccion=0;$pFraccion<sizeof($s->fracciones);$pFraccion++)
			{
				$f=$s->fracciones[$pFraccion];
				$lblFraccion="";
				if($f->idRegistro!=0)
				{
					$consulta="SELECT fraccion FROM _587_tablaDinamica WHERE id__587_tablaDinamica =".$f->idRegistro." ORDER BY id__587_tablaDinamica";
					$lblFraccion=$con->obtenerValor($consulta);
				}
				else
				{
					
						$lblFraccion="otro";
					
				}
				
				if($f->detallesAdicionales!="")
				{
					if($lblFraccion=="")
						$lblFraccion=$f->detallesAdicionales;
					else
						$lblFraccion.=": ".$f->detallesAdicionales;
				}
				
				if($fracciones=="")
					  $fracciones=$lblFraccion;
				 else
				 {
					 if($pFraccion<sizeof($s->fracciones)-1)
						$fracciones.=", ".$lblFraccion;
					else
				 		$fracciones.=" y ".$lblFraccion;
				 
				 
				 
				 }
				
			}
			$arrValores["lblInforme"].='- Tras una búsqueda en los archivos de esta Dirección Ejecutiva, a nombre de '.$arrValores["nombreImputado"].
									' se encontró el registro relacionado con la carpeta judicial número '.$carpetaJudicial.
									', donde se decretó la <b>suspensión condicional del proceso</b>'.($con->filasAfectadas==1?' fracción '.$fracciones:' fracciones: '.
									$fracciones).' del artículo 102 de la Ley Nacional del Sistema Integral de Justicia Penal para Adolescentes.<br><br>';
			
		}
		
		
		
		
		
		
	}
	
	
	
	return $arrValores;
}

function funcionLlenadoRespuestaSupervisionAdultoMedidaCautelar($idRegistro)
{
	global $con;
	global $arrMesLetra;
	global $leyendaTribunal;
	
	$arrValores=array();

	$consulta="SELECT datosParametros,carpetaAdministrativa,idFormulario,idReferencia FROM 7035_informacionDocumentos WHERE idRegistro=".$idRegistro;
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
		
	$delitos=$con->obtenerValor($consulta);
	
	$consulta="SELECT upper(CONCAT(IF(nombre IS NULL,'',nombre),' ',IF(apellidoPaterno IS NULL,'',apellidoPaterno),' ',IF(apellidoMaterno IS NULL,'',apellidoMaterno))) 
				FROM _47_tablaDinamica p where id__47_tablaDinamica in(".$datosParametros->imputados.
				") ORDER BY nombre,nombre,apellidoMaterno";
	
	
	$arrValores["fechaActual"]=convertirFechaLetra(date("Y-m-d"),false,false);
	$arrValores["carpetaJudicial"]=$datosParametros->carpetaJudicial;
	$arrValores["carpetaInvestigacion"]=$filaCarpeta[0];
	$arrValores["imputado"]=$con->obtenerListaValores($consulta);
	$arrValores["nombreImputado"]=$con->obtenerListaValores($consulta);
	$arrValores["lblCuenta"]=$con->filasAfectadas==1?"cuenta":"cuentan";
	$arrValores["delito"]=$delitos;
	$arrValores["oficioOriginal"]=$datosParametros->oficioResponde==""?"_______":$datosParametros->oficioResponde;
	$arrValores["fechaOficio"]=$datosParametros->fechaOficioResponde=="_______"?"":date("d/m/Y",strtotime($datosParametros->fechaOficioResponde));
	$arrValores["sinNumero"]=$arrValores["oficioOriginal"]==""?"sin ":"";
	$arrValores["vinculacionProceso"]=$datosParametros->fechaVinculacionProceso!="_______"?date("d/m/Y",strtotime($datosParametros->fechaVinculacionProceso)):"";
	$arrValores["noOficio"]=$datosParametros->noOficioAsignar==""?("____/".date("Y")):(str_pad($datosParametros->noOficioAsignar,4,"0",STR_PAD_LEFT)."/".date("Y"));

	$arrValores["leyendaTribunal"]=str_replace("<br>", " ",$leyendaTribunal);	
	$arrValores["nombreDestinatario"]=mb_strtoupper($datosParametros->nombreDestinatario==""?"_______":$datosParametros->nombreDestinatario);
	$arrValores["puestoDestinatario"]=mb_strtoupper($datosParametros->puestoDestinatario==""?"_______":$datosParametros->puestoDestinatario);
	$arrValores["lblInforme"]="";
	$arrValores["nombreElaboro"]=mb_strtoupper(obtenerNombreUsuario($_SESSION["idUsr"]));
	$arrValores["Iniciales"]=inicialesNombre($arrValores["nombreElaboro"]);
	$consulta="SELECT TRIM(CONCAT(IF(Prefijo IS NULL,'',Prefijo),' ',i.Nombre)) FROM 802_identifica i,800_usuarios u WHERE i.idUsuario IN(
				SELECT idUsuario FROM 807_usuariosVSRoles WHERE idRol=188) AND u.idUsuario=i.idUsuario AND 
				u.cuentaActiva=1 ORDER BY u.Nombre";
	$nombreJefeUnidadDepartamental=$con->obtenerListaValores($consulta);
	$arrValores["nombreJefeUnidadDepartamental"]=($nombreJefeUnidadDepartamental);
	
	if($datosParametros->tipoInforme==1)//MC
	{
		$arrValores["numerales"]=155;
		$arrValores["tipoSancion"]="medidas cautelares";		
	}
	else//SC
	{
		$arrValores["numerales"]=195;	
		$arrValores["tipoSancion"]="condiciones de suspensión condicional de proceso";		
	}
	
	
	$arrValores["fracciones"]="";
	$totalFracciones=sizeof($datosParametros->listaSeguimiento);
	$nFraccion=1;

	foreach($datosParametros->listaSeguimiento as $oSeguimiento)
	{
		if($oSeguimiento->idRegistro!=0)
		{
			if($arrValores["fracciones"]=="")
				$arrValores["fracciones"]=$oSeguimiento->fracciones;
			else
				$arrValores["fracciones"].=", ".$oSeguimiento->fracciones;
		}
		$consulta="SELECT leyenda FROM _587_tablaDinamica WHERE id__587_tablaDinamica=".$oSeguimiento->idRegistro;
		$descripcionFraccion=$con->obtenerValor($consulta);
		$leyenda="";
		
		if($oSeguimiento->idRegistro!=0)
		{
		
			if($nFraccion==1)
			{
				$leyenda="<strong>Con respecto a la fracción ".$oSeguimiento->fracciones."</strong> consistente en: ".$descripcionFraccion."; informo que ".$oSeguimiento->resultadoSeguimiento."<br>";
				if($totalFracciones>1)
					$leyenda.="<br>";
			}
			else
			{
				if($nFraccion==$totalFracciones)
				{
					$leyenda="<strong>Por último, relativo a la fracción ".$oSeguimiento->fracciones."</strong> consistente en: ".$descripcionFraccion."; informo que ".$oSeguimiento->resultadoSeguimiento."<br>";
				}
				else
				{
					if($nFraccion==2)
					{
						$leyenda="<strong>Asimismo, con relación a la fracción ".$oSeguimiento->fracciones."</strong> consistente en: ".$descripcionFraccion."; informo que ".$oSeguimiento->resultadoSeguimiento."<br><br>";
					}
					else
					{
						$leyenda="<strong>En relación a la fracción ".$oSeguimiento->fracciones."</strong> consistente en: ".$descripcionFraccion."; informo que ".$oSeguimiento->resultadoSeguimiento."<br><br>";
					}
				}
			}

		}
		else
		{
		
			if($nFraccion==1)
			{
				$leyenda="<strong>Con respecto a otro</strong>; informo que ".$oSeguimiento->resultadoSeguimiento."<br>";
				if($totalFracciones>1)
					$leyenda.="<br>";
			}
			else
			{
				if($nFraccion==$totalFracciones)
				{
					$leyenda="<strong>Por último, relativo a otro</strong>; informo que ".$oSeguimiento->resultadoSeguimiento."<br>";
				}
				else
				{
					if($nFraccion==2)
					{
						$leyenda="<strong>Asimismo, con relación a otro</strong>; informo que ".$oSeguimiento->resultadoSeguimiento."<br><br>";
					}
					else
					{
						$leyenda="<strong>En relación a otro</strong>; informo que ".$oSeguimiento->resultadoSeguimiento."<br><br>";
					}
				}
			}
			
		}
		
		
		
		if($arrValores["lblInforme"]=="")			
			$arrValores["lblInforme"]=$leyenda;
		else
			$arrValores["lblInforme"].=$leyenda;
		$nFraccion++;
			
	}
	
	if($totalFracciones==1)
	{
		$arrValores["fracciones"]=" fracción ".$arrValores["fracciones"];
	}
	else
	{
		$arrValores["fracciones"]=" fracciones ".$arrValores["fracciones"];
	}
	
	return $arrValores;
}

function funcionLlenadoRespuestaSupervisionAdolescenteMedidaCautelar($idRegistro)
{
	global $con;
	global $arrMesLetra;
	global $leyendaTribunal;
	$arrValores=array();
	$consulta="SELECT datosParametros,carpetaAdministrativa,idFormulario,idReferencia FROM 7035_informacionDocumentos WHERE idRegistro=".$idRegistro;
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
		
	$delitos=$con->obtenerValor($consulta);
	
	$consulta="SELECT upper(CONCAT(IF(nombre IS NULL,'',nombre),' ',IF(apellidoPaterno IS NULL,'',apellidoPaterno),' ',IF(apellidoMaterno IS NULL,'',apellidoMaterno))) 
				FROM _47_tablaDinamica p where id__47_tablaDinamica in(".$datosParametros->imputados.
				") ORDER BY nombre,nombre,apellidoMaterno";
	
	$consultaAux="SELECT fechaEvento FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$datosParametros->audienciaImposicion;
	$fechaAudiencia=$con->obtenerValor($consultaAux);
	
	$arrValores["fechaAudiencia"]=convertirFechaLetra($fechaAudiencia,false,false);
	$arrValores["fechaActual"]=mb_strtoupper(convertirFechaLetra(date("Y-m-d"),false,false));
	$arrValores["carpetaJudicial"]=$datosParametros->carpetaJudicial;
	$arrValores["carpetaInvestigacion"]=$filaCarpeta[0];
	$arrValores["imputado"]=$con->obtenerListaValores($consulta);
	$arrValores["nombreImputado"]=$con->obtenerListaValores($consulta);
	$arrValores["lblCuenta"]=$con->filasAfectadas==1?"cuenta":"cuentan";
	$arrValores["delito"]=$delitos;
	$arrValores["oficioOriginal"]=$datosParametros->oficioResponde==""?"_______":$datosParametros->oficioResponde;
	$arrValores["fechaOficio"]=$datosParametros->fechaOficioResponde=="_______"?"":date("d/m/Y",strtotime($datosParametros->fechaOficioResponde));
	$arrValores["sinNumero"]=$arrValores["oficioOriginal"]==""?"sin ":"";
	$arrValores["vinculacionProceso"]=$datosParametros->fechaVinculacionProceso!="_______"?date("d/m/Y",strtotime($datosParametros->fechaVinculacionProceso)):"";
	$arrValores["noOficio"]=$datosParametros->noOficioAsignar==""?("____/".date("Y")):(str_pad($datosParametros->noOficioAsignar,4,"0",STR_PAD_LEFT)."/".date("Y"));

	$arrValores["leyendaTribunal"]=str_replace("<br>", " ",$leyendaTribunal);	
	$arrValores["nombreDestinatario"]=mb_strtoupper($datosParametros->nombreDestinatario==""?"_______":$datosParametros->nombreDestinatario);
	$arrValores["puestoDestinatario"]=mb_strtoupper($datosParametros->puestoDestinatario==""?"_______":$datosParametros->puestoDestinatario);
	$arrValores["lblInforme"]="";
	$arrValores["nombreElaboro"]=mb_strtoupper(obtenerNombreUsuario($_SESSION["idUsr"]));
	$arrValores["Iniciales"]=inicialesNombre($arrValores["nombreElaboro"]);
	$consulta="SELECT TRIM(CONCAT(IF(Prefijo IS NULL,'',Prefijo),' ',i.Nombre)) FROM 802_identifica i,800_usuarios u WHERE i.idUsuario IN(
				SELECT idUsuario FROM 807_usuariosVSRoles WHERE idRol=188) AND u.idUsuario=i.idUsuario AND 
				u.cuentaActiva=1 ORDER BY u.Nombre";
	$nombreJefeUnidadDepartamental=$con->obtenerListaValores($consulta);
	$arrValores["nombreJefeUnidadDepartamental"]=($nombreJefeUnidadDepartamental);
	
	if($datosParametros->tipoInforme==1)//MC
	{
		//$arrValores["numerales"]=155;
		$arrValores["tipoSancion"]="medidas cautelares";		
	}
	else//SC
	{
		//$arrValores["numerales"]=195;	
		$arrValores["tipoSancion"]="condiciones de suspensión condicional de proceso";		
	}
	
	
	$arrValores["fracciones"]="";
	$totalFracciones=sizeof($datosParametros->listaSeguimiento);
	$nFraccion=1;
	foreach($datosParametros->listaSeguimiento as $oSeguimiento)
	{
		if($arrValores["fracciones"]=="")
			$arrValores["fracciones"]=$oSeguimiento->fracciones;
		else
			$arrValores["fracciones"].=", ".$oSeguimiento->fracciones;
		
		$consulta="SELECT 	leyenda FROM _587_tablaDinamica WHERE id__587_tablaDinamica=".$oSeguimiento->idRegistro;
		$descripcionFraccion=$con->obtenerValor($consulta);
		$leyenda="";
		if($nFraccion==1)
		{
			if($oSeguimiento->idRegistro!=0)
			{
				$leyenda="<strong>Referente a la fracción ".$oSeguimiento->fracciones."</strong> consistente en: ".$descripcionFraccion."; informo que ".$oSeguimiento->resultadoSeguimiento."<br>";
				
			}
			else
			{
				$leyenda="<strong>Referente a otro</strong>; informo que ".$oSeguimiento->resultadoSeguimiento."<br>";
				
			}
			if($totalFracciones>1)
				$leyenda.="<br>";
		}
		else
		{
			if($oSeguimiento->idRegistro!=0)
			{
				if($nFraccion==$totalFracciones)
				{
					$leyenda="<strong>Por último, relativo a la fracción ".$oSeguimiento->fracciones."</strong> consistente en: ".$descripcionFraccion."; informo que ".$oSeguimiento->resultadoSeguimiento."<br>";
				}
				else
				{
					if($nFraccion==2)
					{
						$leyenda="<strong>Respecto a la fracción ".$oSeguimiento->fracciones."</strong> consistente en: ".$descripcionFraccion."; informo que ".$oSeguimiento->resultadoSeguimiento."<br><br>";
					}
					else
					{
						$leyenda="<strong>En relación a la fracción ".$oSeguimiento->fracciones."</strong> consistente en: ".$descripcionFraccion."; informo que ".$oSeguimiento->resultadoSeguimiento."<br><br>";
					}
				}
			}
			else
			{
				if($nFraccion==$totalFracciones)
				{
					$leyenda="<strong>Por último, relativo a otro;</strong> informo que ".$oSeguimiento->resultadoSeguimiento."<br>";
				}
				else
				{
					if($nFraccion==2)
					{
						$leyenda="<strong>Respecto a otro;</strong> informo que ".$oSeguimiento->resultadoSeguimiento."<br><br>";
					}
					else
					{
						$leyenda="<strong>En relación a otro;</strong> informo que ".$oSeguimiento->resultadoSeguimiento."<br><br>";
					}
				}
			}
			
		}
		
		
		
		
		if($arrValores["lblInforme"]=="")			
			$arrValores["lblInforme"]=$leyenda;
		else
			$arrValores["lblInforme"].=$leyenda;
		$nFraccion++;
			
	}
	
	if($totalFracciones==1)
	{
		$arrValores["fracciones"]=" fracción ".$arrValores["fracciones"];
	}
	else
	{
		$arrValores["fracciones"]=" fracciones ".$arrValores["fracciones"];
	}
	
	return $arrValores;
}

function funcionLlenadoSuspensionCondicionalProcesoAdolescente($idRegistro)
{
	global $con;
	global $arrMesLetra;
	global $leyendaTribunal;
	$arrValores=array();
	$consulta="SELECT datosParametros,carpetaAdministrativa,idFormulario,idReferencia FROM 7035_informacionDocumentos WHERE idRegistro=".$idRegistro;
	$fila=$con->obtenerPrimeraFila($consulta);
	$datosParametros=json_decode(bD($fila[0]));
	
	$consulta="SELECT carpetaInvestigacion,idActividad,carpetaAdministrativaBase,unidadGestion FROM 7006_carpetasAdministrativas 
				WHERE carpetaAdministrativa='".$datosParametros->carpetaJudicial."'";
	$filaCarpeta=$con->obtenerPrimeraFila($consulta);
	
	$consulta="SELECT GROUP_CONCAT(upper(dl.denominacionDelito)) FROM _61_tablaDinamica d,_35_denominacionDelito dl WHERE d.idActividad=".$filaCarpeta[1]." AND
					dl.id__35_denominacionDelito=d.denominacionDelito";
		
	$delitos=$con->obtenerValor($consulta);
	
	$consulta="SELECT upper(CONCAT(IF(nombre IS NULL,'',nombre),' ',IF(apellidoPaterno IS NULL,'',apellidoPaterno),' ',IF(apellidoMaterno IS NULL,'',apellidoMaterno))) 
				FROM _47_tablaDinamica p where id__47_tablaDinamica in(".$datosParametros->imputados.
				") ORDER BY nombre,nombre,apellidoMaterno";
	
	
	$arrValores["fecha"]=convertirFechaLetra(date("Y-m-d"),false,false);
	$arrValores["carpetaJudicial"]=$datosParametros->carpetaJudicial;
	$arrValores["carpetaInvestigacion"]=$filaCarpeta[0];
	$arrValores["imputado"]=$con->obtenerListaValores($consulta);
	
	
	$consulta="SELECT nombreUnidad,claveFolioCarpetas,tipoMateria FROM _17_tablaDinamica WHERE  claveUnidad='".$filaCarpeta[3]."'";
	$filaUGA=$con->obtenerPrimeraFila($consulta);
	
	$noUGA=$filaUGA[1]*1;
	if($filaUGA[2]==2)
		$noUGA="JA";
	
	$victimas="";
	$consulta="SELECT upper(CONCAT(IF(nombre IS NULL,'',nombre),' ',IF(apellidoPaterno IS NULL,'',apellidoPaterno),' ',IF(apellidoMaterno IS NULL,'',apellidoMaterno))) 
				FROM _47_tablaDinamica p,7005_relacionFigurasJuridicasSolicitud r WHERE r.idParticipante=p.id__47_tablaDinamica
				AND r.idActividad=".$filaCarpeta[1]." AND r.idFiguraJuridica=2 ORDER BY nombre,nombre,apellidoMaterno";
	
	$res=$con->obtenerFilas($consulta);
	while($filaImputado=mysql_fetch_row($res))
	{
		$nombre=$filaImputado[0];
/*		if($datosParametros->mostrarVictimasComo!=1)
			$nombre=inicialesNombre($filaImputado[0]);*/
		
		if($victimas=="")
			$victimas=$nombre;
		else
			$victimas.=", ".$nombre;
	}
	
	$arrValores["folioOficio"]="UGJ".$noUGA."/".str_pad($datosParametros->noOficioAsignar,4,"0",STR_PAD_LEFT)."/".date("Y");
	
	$arrValores["victimaCabecera"]=$victimas==""?"________________":$victimas;
	$arrValores["delito"]=$delitos;

	$arrValores["periodoSuspension"]=$datosParametros->periodo;
	$arrValores["fechaFenecimiento"]=convertirFechaLetra(date("Y-m-d",strtotime($datosParametros->fechaFenecimiento)),false,false);
	$arrValores["condiciones"]="";
	$arrValores["plazoIncumplimiento"]=$datosParametros->plazoDias;
	$arrValores["nombreFirmante"]=obtenerNombreUsuario($datosParametros->usuarioDestinatario);
	$arrValores["leyendaTribunal"]=$leyendaTribunal;
	$arrValores["limitePresentacion"]="";
	if($datosParametros->tipoPeriodoLimite==1)
	{
		$arrPeriodoLimite[1]="horas";
		$arrPeriodoLimite[2]="dias";
		$arrPeriodoLimite[3]="meses";
		$arrPeriodoLimite[4]="a&ntilde;os";
		$arrValores["limitePresentacion"]="en ".$datosParametros->periodoLimite." ".$arrPeriodoLimite[$datosParametros->plazoPeriodoLimite];
	}
	else
	{
		$arrValores["limitePresentacion"]="el ".convertirFechaLetra($datosParametros->fechaLimite);
	}
	$numReg=1;
	foreach($datosParametros->suspencionesCondicional as $m)
	{
		if($m->idCondicion!=0)
		{
			$consulta="SELECT fraccion,leyenda FROM _587_tablaDinamica WHERE id__587_tablaDinamica =".$m->idCondicion;
			$fLeyenda=$con->obtenerPrimeraFila($consulta);
			$arrValores["condiciones"].="<p>".$numReg.". ".$fLeyenda[1]."".($m->detalles==""?".":": ".$m->detalles)."</p>";
		}
		else
		{
			$arrValores["condiciones"].="<p>".$numReg.". ".$m->detalles."</p>";
		}
		
		$numReg++;
	}
		
	return $arrValores;
}

function funcionLlenadoMedidaCautelarProcesoAdolescente($idRegistro)
{
	global $con;
	global $arrMesLetra;
	global $leyendaTribunal;
	$arrValores=array();
	$consulta="SELECT datosParametros,carpetaAdministrativa,idFormulario,idReferencia FROM 7035_informacionDocumentos WHERE idRegistro=".$idRegistro;
	$fila=$con->obtenerPrimeraFila($consulta);
	$datosParametros=json_decode(bD($fila[0]));
	
	$consulta="SELECT carpetaInvestigacion,idActividad,carpetaAdministrativaBase,unidadGestion FROM 7006_carpetasAdministrativas 
				WHERE carpetaAdministrativa='".$datosParametros->carpetaJudicial."'";
	$filaCarpeta=$con->obtenerPrimeraFila($consulta);
	
	$consulta="SELECT GROUP_CONCAT(upper(dl.denominacionDelito)) FROM _61_tablaDinamica d,_35_denominacionDelito dl WHERE d.idActividad=".$filaCarpeta[1]." AND
					dl.id__35_denominacionDelito=d.denominacionDelito";
		
	$delitos=$con->obtenerValor($consulta);
	
	$consulta="SELECT upper(CONCAT(IF(nombre IS NULL,'',nombre),' ',IF(apellidoPaterno IS NULL,'',apellidoPaterno),' ',IF(apellidoMaterno IS NULL,'',apellidoMaterno))) 
				FROM _47_tablaDinamica p where id__47_tablaDinamica in(".$datosParametros->imputados.
				") ORDER BY nombre,nombre,apellidoMaterno";
	
	
	$arrValores["fecha"]=convertirFechaLetra(date("Y-m-d"),false,false);
	$arrValores["carpetaJudicial"]=$datosParametros->carpetaJudicial;
	$arrValores["carpetaInvestigacion"]=$filaCarpeta[0];
	$arrValores["imputado"]=$con->obtenerListaValores($consulta);
	
	
	$consulta="SELECT nombreUnidad,claveFolioCarpetas,tipoMateria,id__17_tablaDinamica FROM _17_tablaDinamica WHERE  claveUnidad='".$filaCarpeta[3]."'";
	$filaUGA=$con->obtenerPrimeraFila($consulta);
	
	$noUGA=$filaUGA[1]*1;
	if($filaUGA[2]==2)
		$noUGA="JA";
	
	
	
	$iUG=$filaUGA[3];
	
	$consulta="SELECT idPadre FROM _420_unidadGestion WHERE idOpcion=".$iUG;
	$idPerfilOrganigrama=$con->obtenerValor($consulta);
	if($idPerfilOrganigrama=="")
		$idPerfilOrganigrama=-1;
	
	$victimas="";
	$consulta="SELECT upper(CONCAT(IF(nombre IS NULL,'',nombre),' ',IF(apellidoPaterno IS NULL,'',apellidoPaterno),' ',IF(apellidoMaterno IS NULL,'',apellidoMaterno))) 
				FROM _47_tablaDinamica p,7005_relacionFigurasJuridicasSolicitud r WHERE r.idParticipante=p.id__47_tablaDinamica
				AND r.idActividad=".$filaCarpeta[1]." AND r.idFiguraJuridica=2 ORDER BY nombre,nombre,apellidoMaterno";
	
	$res=$con->obtenerFilas($consulta);
	while($filaImputado=mysql_fetch_row($res))
	{
		$nombre=$filaImputado[0];
/*		if($datosParametros->mostrarVictimasComo!=1)
			$nombre=inicialesNombre($filaImputado[0]);*/
		
		if($victimas=="")
			$victimas=$nombre;
		else
			$victimas.=", ".$nombre;
	}
	
	$arrValores["folioOficio"]="UGJ".$noUGA."/".str_pad($datosParametros->noOficioAsignar,4,"0",STR_PAD_LEFT)."/".date("Y");
	
	$arrValores["victimaCabecera"]=$victimas==""?"________________":$victimas;
	$arrValores["delito"]=$delitos;
	
	$consulta="SELECT usuarioAsignado FROM _421_tablaDinamica WHERE idReferencia=".$idPerfilOrganigrama." and puestoOrganozacional=15";
	$directorUnidad=$con->obtenerValor($consulta);
	
	$arrValores["medidasCautelares"]="";
	$arrValores["nombreFirmante"]=obtenerNombreUsuario($directorUnidad);
	$arrValores["juez"]=obtenerNombreUsuario($datosParametros->usuarioOrdeno);
	$arrValores["leyendaTribunal"]=$leyendaTribunal;
	
	$numReg=97;
	foreach($datosParametros->medidadCautelares as $m)
	{
		if($m->idMedidaCautelar!=0)
		{
			$consulta="SELECT leyenda FROM _587_tablaDinamica WHERE id__587_tablaDinamica =".$m->idMedidaCautelar;
			$fLeyenda=$con->obtenerPrimeraFila($consulta);
			$arrValores["medidasCautelares"].="<p>".chr ($numReg).") ".$fLeyenda[0]."".($m->detalles==""?".":": ".$m->detalles)."</p>";
		}
		else
		{
			$arrValores["medidasCautelares"].="<p>".chr ($numReg).") ".$m->detalles."</p>";
		}
		
		$numReg++;
	}
		
	return $arrValores;
}

function funcionLlenadoAudienciaInicialAdolescente($idRegistro)
{
	global $con;
	global $arrMesLetra;
	global $leyendaTribunal;
	
	$arrValores=array();
	$consulta="SELECT datosParametros,carpetaAdministrativa,idFormulario,idReferencia FROM 7035_informacionDocumentos WHERE idRegistro=".$idRegistro;
	$fila=$con->obtenerPrimeraFila($consulta);
	$datosParametros=json_decode(bD($fila[0]));

	$consulta="SELECT carpetaInvestigacion,idActividad,carpetaAdministrativaBase,unidadGestion FROM 7006_carpetasAdministrativas 
				WHERE carpetaAdministrativa='".$datosParametros->carpetaJudicial."'";
	$filaCarpeta=$con->obtenerPrimeraFila($consulta);
	
	$consulta="SELECT GROUP_CONCAT(upper(dl.denominacionDelito)) FROM _61_tablaDinamica d,_35_denominacionDelito dl WHERE d.idActividad=".$filaCarpeta[1]." AND
					dl.id__35_denominacionDelito=d.denominacionDelito";
		
	$delitos=$con->obtenerValor($consulta);
	
	$consulta="SELECT upper(CONCAT(IF(nombre IS NULL,'',nombre),' ',IF(apellidoPaterno IS NULL,'',apellidoPaterno),' ',IF(apellidoMaterno IS NULL,'',apellidoMaterno))) 
				FROM _47_tablaDinamica p where id__47_tablaDinamica in(".$datosParametros->imputados.
				") ORDER BY nombre,nombre,apellidoMaterno";
	
	
	$arrValores["fecha"]=convertirFechaLetra(date("Y-m-d"),false,false);
	$arrValores["carpetaJudicial"]=$datosParametros->carpetaJudicial;
	$arrValores["carpetaInvestigacion"]=$filaCarpeta[0];
	$arrValores["adolescente"]=$con->obtenerListaValores($consulta);
	
	
	$consulta="SELECT nombreUnidad,claveFolioCarpetas,tipoMateria FROM _17_tablaDinamica WHERE  claveUnidad='".$filaCarpeta[3]."'";
	$filaUGA=$con->obtenerPrimeraFila($consulta);
	
	$noUGA=$filaUGA[1]*1;
	if($filaUGA[2]==2)
		$noUGA="JA";
	
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
			$victimas=$nombre;
		else
			$victimas.=", ".$nombre;
	}
	
	$arrValores["folioOficio"]="UGJ".$noUGA."/".str_pad($datosParametros->noOficioAsignar,4,"0",STR_PAD_LEFT)."/".date("Y");
	
	$arrValores["victima"]=$victimas==""?"________________":$victimas;
	$arrValores["delito"]=$delitos;
	
	$consulta="SELECT usuarioAsignado FROM _421_tablaDinamica WHERE id__421_tablaDinamica=".$datosParametros->usuarioDestinatario;
	$firmante=$con->obtenerValor($consulta);
	
	$arrValores["nombreFirmante"]=obtenerNombreUsuario($firmante);
	$arrValores["leyendaTribunal"]=$leyendaTribunal;
	
	$consulta="SELECT * FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$datosParametros->audienciaCelebrar;
	$fEvento=$con->obtenerPrimeraFilaAsoc($consulta);
	
	$arrValores["horaAudiencia"]=date("H:i",strtotime($fEvento["horaInicioEvento"]))." hrs.";
	$leyendaAnio=date("Y",strtotime($fEvento["fechaEvento"]));
	
	$leyendaAnio=convertirNumeroLetra($leyendaAnio,false,false);
	
	
	$arrValores["fechaAudiencia"]=convertirFechaLetra(date("Y-m-d",strtotime($fEvento["fechaEvento"])),false,false)." ".$leyendaAnio;
	
	//$consulta="select nombreSala from _15_tablaDinamica where id__15_tablaDinamica=".$fEvento["idSala"];
	
	//$arrValores["salaAudiencia"]=str_replace("Sala ","",$con->obtenerValor($consulta));
		
	return $arrValores;
}

function funcionLlenadoSolicitudPeritoGenetica($idRegistro)
{
	global $con;
	global $arrMesLetra;
	global $leyendaTribunal;
	
	$arrValores=array();
	$consulta="SELECT datosParametros,carpetaAdministrativa,idFormulario,idReferencia FROM 7035_informacionDocumentos WHERE idRegistro=".$idRegistro;
	$fila=$con->obtenerPrimeraFila($consulta);
	$datosParametros=json_decode(bD($fila[0]));
	
	$consulta="SELECT carpetaInvestigacion,idActividad,carpetaAdministrativaBase,unidadGestion FROM 7006_carpetasAdministrativas 
				WHERE carpetaAdministrativa='".$datosParametros->carpetaJudicial."'";
	$filaCarpeta=$con->obtenerPrimeraFila($consulta);
	
	
	$consulta="SELECT id__17_tablaDinamica FROM _17_tablaDinamica WHERE claveUnidad='".$filaCarpeta[3]."'";
	$iUG=$con->obtenerValor($consulta);
	
	$consulta="SELECT idPadre FROM _420_unidadGestion WHERE idOpcion=".$iUG;
	$idPerfilOrganigrama=$con->obtenerValor($consulta);
	if($idPerfilOrganigrama=="")
		$idPerfilOrganigrama=-1;
	
	
	$consulta="SELECT GROUP_CONCAT(upper(dl.denominacionDelito)) FROM _61_tablaDinamica d,_35_denominacionDelito dl WHERE d.idActividad=".$filaCarpeta[1]." AND
					dl.id__35_denominacionDelito=d.denominacionDelito";
		
	$delitos=$con->obtenerValor($consulta);
	
	$consulta="SELECT upper(CONCAT(IF(nombre IS NULL,'',nombre),' ',IF(apellidoPaterno IS NULL,'',apellidoPaterno),' ',IF(apellidoMaterno IS NULL,'',apellidoMaterno))) 
				FROM _47_tablaDinamica p where id__47_tablaDinamica in(".$datosParametros->imputados.
				") ORDER BY nombre,nombre,apellidoMaterno";
	
	
	$arrValores["fecha"]=convertirFechaLetra(date("Y-m-d"),false,false);
	$arrValores["carpetaJudicial"]=$datosParametros->carpetaJudicial;
	$arrValores["carpetaInvestigacion"]=$filaCarpeta[0];
	$arrValores["imputado"]=$con->obtenerListaValores($consulta);
	
	
	$consulta="SELECT nombreUnidad,claveFolioCarpetas,tipoMateria FROM _17_tablaDinamica WHERE  claveUnidad='".$filaCarpeta[3]."'";
	$filaUGA=$con->obtenerPrimeraFila($consulta);
	
	$noUGA=$filaUGA[1]*1;
	if($filaUGA[2]==2)
		$noUGA="JA";
	
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
			$victimas=$nombre;
		else
			$victimas.=", ".$nombre;
	}
	
	$arrValores["folioOficio"]="UGJ".$noUGA."/".str_pad($datosParametros->noOficioAsignar,4,"0",STR_PAD_LEFT)."/".date("Y");
	
	$arrValores["victima"]=$victimas==""?"________________":$victimas;
	$arrValores["delito"]=$delitos;
	
	$consulta="SELECT usuarioAsignado FROM _421_tablaDinamica WHERE idReferencia=".$idPerfilOrganigrama." and puestoOrganozacional=15";
	$firmante=$con->obtenerValor($consulta);
	
	$arrValores["nombreFirmante"]=obtenerNombreUsuario($datosParametros->usuarioDestinatario);
	$arrValores["leyendaTribunal"]=$leyendaTribunal;
	
	/*$consulta="SELECT tipoAudiencia,fechaEvento FROM 7000_eventosAudiencia WHERE idRegistroEvento=".$datosParametros->audienciaOrdena;
	$fDatosAudiencia=$con->obtenerPrimeraFila($consulta);
	
	$consulta="SELECT tipoAudiencia FROM  _4_tablaDinamica WHERE id__4_tablaDinamica=".$fDatosAudiencia[0];
	$tAudiencia=$con->obtenerValor($consulta);*/
	
	$consulta="SELECT nombreElemento,datosComplementarios FROM 1018_catalogoVarios WHERE tipoElemento=36 AND claveElemento=".$datosParametros->parentezco;
	$fComplementario=$con->obtenerPrimeraFila($consulta);
	
	//$datoAudiencia=trim($tAudiencia)." celebrada el d&iacute;a ".convertirFechaLetra($fDatosAudiencia[1],false,false);;
	//$arrValores["datoAudiencia"]=$datoAudiencia;
	$arrValores["nombrePariente"]=($fComplementario[1]=="1"?"el señor ":"la señora ").$datosParametros->personaComparacion;
	
	
	$arrValores["parentezco"]=mb_strtolower($fComplementario[0]);
	
	$arrValores["parrafoInterprete"]="";
	if($datosParametros->hablaLengua==1)
	{
		/*$consulta="SELECT lengua FROM _379_tablaDinamica where id__379_tablaDinamica=".$datosParametros->lengua;
		$lengua=$con->obtenerValor($consulta);*/
		$arrValores["parrafoInterprete"]="<p>No se omite informar, que dicho adolescente habla el idioma ".$datosParametros->lengua.", ".
										"por lo que será asistido por el interprete o traductor.";
	}
	return $arrValores;
}

function funcionLlenadoSolicitudInformeRiezgoProcesal($idRegistro)
{
	global $con;
	global $arrMesLetra;
	global $leyendaTribunal;
	
	$arrValores=array();
	$consulta="SELECT datosParametros,carpetaAdministrativa,idFormulario,idReferencia FROM 7035_informacionDocumentos WHERE idRegistro=".$idRegistro;
	$fila=$con->obtenerPrimeraFila($consulta);
	$datosParametros=json_decode(bD($fila[0]));
	
	$consulta="SELECT carpetaInvestigacion,idActividad,carpetaAdministrativaBase,unidadGestion FROM 7006_carpetasAdministrativas 
				WHERE carpetaAdministrativa='".$datosParametros->carpetaJudicial."'";
	$filaCarpeta=$con->obtenerPrimeraFila($consulta);
	
	
	$consulta="SELECT id__17_tablaDinamica FROM _17_tablaDinamica WHERE claveUnidad='".$filaCarpeta[3]."'";
	$iUG=$con->obtenerValor($consulta);
	
	$consulta="SELECT idPadre FROM _420_unidadGestion WHERE idOpcion=".$iUG;
	$idPerfilOrganigrama=$con->obtenerValor($consulta);
	if($idPerfilOrganigrama=="")
		$idPerfilOrganigrama=-1;
	
	
	$consulta="SELECT GROUP_CONCAT(upper(dl.denominacionDelito)) FROM _61_tablaDinamica d,_35_denominacionDelito dl WHERE d.idActividad=".$filaCarpeta[1]." AND
					dl.id__35_denominacionDelito=d.denominacionDelito";
		
	$delitos=$con->obtenerValor($consulta);
	
	$consulta="SELECT upper(CONCAT(IF(nombre IS NULL,'',nombre),' ',IF(apellidoPaterno IS NULL,'',apellidoPaterno),' ',IF(apellidoMaterno IS NULL,'',apellidoMaterno))) 
				FROM _47_tablaDinamica p where id__47_tablaDinamica in(".$datosParametros->imputados.
				") ORDER BY nombre,nombre,apellidoMaterno";
	
	
	$arrValores["fecha"]=convertirFechaLetra(date("Y-m-d"),false,false);
	$arrValores["carpetaJudicial"]=$datosParametros->carpetaJudicial;
	$arrValores["carpetaInvestigacion"]=$filaCarpeta[0];
	$arrValores["imputado"]=$con->obtenerListaValores($consulta);
	
	
	$consulta="SELECT nombreUnidad,claveFolioCarpetas,tipoMateria FROM _17_tablaDinamica WHERE  claveUnidad='".$filaCarpeta[3]."'";
	$filaUGA=$con->obtenerPrimeraFila($consulta);
	
	$noUGA=$filaUGA[1]*1;
	if($filaUGA[2]==2)
		$noUGA="JA";
	
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
			$victimas=$nombre;
		else
			$victimas.=", ".$nombre;
	}
	
	$arrValores["folioOficio"]="UGJ".$noUGA."/".str_pad($datosParametros->noOficioAsignar,4,"0",STR_PAD_LEFT)."/".date("Y");
	
	$arrValores["victima"]=$victimas==""?"________________":$victimas;
	$arrValores["delito"]=$delitos;
	
	$consulta="SELECT usuarioAsignado FROM _421_tablaDinamica WHERE idReferencia=".$idPerfilOrganigrama." and puestoOrganozacional=15";
	$firmante=$con->obtenerValor($consulta);
	
	$arrValores["nombreFirmante"]=obtenerNombreUsuario($firmante);
	$arrValores["leyendaTribunal"]=$leyendaTribunal;
	$arrValores["fechaLimite"]=convertirFechaLetra(date("Y-m-d",strtotime($datosParametros->fechaLimite)),false,false);
	
	$arrValores["nombreJuez"]=obtenerNombreUsuario($datosParametros->juezOrdena);
		
	return $arrValores;
}

function funcionLlenadoEdadGenetica($idRegistro)
{
	global $con;
	global $arrMesLetra;
	global $leyendaTribunal;
	
	$arrValores=array();
	$consulta="SELECT datosParametros,carpetaAdministrativa,idFormulario,idReferencia FROM 7035_informacionDocumentos WHERE idRegistro=".$idRegistro;
	$fila=$con->obtenerPrimeraFila($consulta);
	$datosParametros=json_decode(bD($fila[0]));
	
	$consulta="SELECT carpetaInvestigacion,idActividad,carpetaAdministrativaBase,unidadGestion FROM 7006_carpetasAdministrativas 
				WHERE carpetaAdministrativa='".$datosParametros->carpetaJudicial."'";
	$filaCarpeta=$con->obtenerPrimeraFila($consulta);
	
	
	$consulta="SELECT id__17_tablaDinamica FROM _17_tablaDinamica WHERE claveUnidad='".$filaCarpeta[3]."'";
	$iUG=$con->obtenerValor($consulta);
	
	$consulta="SELECT idPadre FROM _420_unidadGestion WHERE idOpcion=".$iUG;
	$idPerfilOrganigrama=$con->obtenerValor($consulta);
	if($idPerfilOrganigrama=="")
		$idPerfilOrganigrama=-1;
	
	
	$consulta="SELECT GROUP_CONCAT(upper(dl.denominacionDelito)) FROM _61_tablaDinamica d,_35_denominacionDelito dl WHERE d.idActividad=".$filaCarpeta[1]." AND
					dl.id__35_denominacionDelito=d.denominacionDelito";
		
	$delitos=$con->obtenerValor($consulta);
	
	$consulta="SELECT upper(CONCAT(IF(nombre IS NULL,'',nombre),' ',IF(apellidoPaterno IS NULL,'',apellidoPaterno),' ',IF(apellidoMaterno IS NULL,'',apellidoMaterno))) 
				FROM _47_tablaDinamica p where id__47_tablaDinamica in(".$datosParametros->imputados.
				") ORDER BY nombre,nombre,apellidoMaterno";
	
	
	$arrValores["fecha"]=convertirFechaLetra(date("Y-m-d"),false,false);
	$arrValores["carpetaJudicial"]=$datosParametros->carpetaJudicial;
	$arrValores["carpetaInvestigacion"]=$filaCarpeta[0];
	$arrValores["imputado"]=$con->obtenerListaValores($consulta);
	
	
	$consulta="SELECT nombreUnidad,claveFolioCarpetas,tipoMateria FROM _17_tablaDinamica WHERE  claveUnidad='".$filaCarpeta[3]."'";
	$filaUGA=$con->obtenerPrimeraFila($consulta);
	
	$noUGA=$filaUGA[1]*1;
	if($filaUGA[2]==2)
		$noUGA="JA";
	
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
			$victimas=$nombre;
		else
			$victimas.=", ".$nombre;
	}
	
	$arrValores["folioOficio"]="UGJ".$noUGA."/".str_pad($datosParametros->noOficioAsignar,4,"0",STR_PAD_LEFT)."/".date("Y");
	
	$arrValores["victima"]=$victimas==""?"________________":$victimas;
	$arrValores["delito"]=$delitos;
	
	$consulta="SELECT usuarioAsignado FROM _421_tablaDinamica WHERE idReferencia=".$idPerfilOrganigrama." and puestoOrganozacional=15";
	$firmante=$con->obtenerValor($consulta);
	
	$arrValores["nombreFirmante"]=obtenerNombreUsuario($datosParametros->usuarioDestinatario);
	$arrValores["leyendaTribunal"]=$leyendaTribunal;
	
	
	$arrValores["representante"]=$datosParametros->representanteImputado;
	
	
	
	$arrValores["parrafoInterprete"]=".";
	if($datosParametros->hablaLengua==1)
	{
		$arrValores["parrafoInterprete"]=", así como habla el idioma ".mb_strtolower($datosParametros->lengua).
										", por lo que será asistido por un interprete durante el desarrollo del estudio.";
		
	}
	return $arrValores;
}

function funcionLlenadoSuspensionCondicionalProcesoAdultos($idRegistro)
{
	global $con;
	global $arrMesLetra;
	global $leyendaTribunal;
	$arrValores=array();
	$consulta="SELECT datosParametros,carpetaAdministrativa,idFormulario,idReferencia FROM 7035_informacionDocumentos WHERE idRegistro=".$idRegistro;
	$fila=$con->obtenerPrimeraFila($consulta);
	$datosParametros=json_decode(bD($fila[0]));
	
	$consulta="SELECT carpetaInvestigacion,idActividad,carpetaAdministrativaBase,unidadGestion FROM 7006_carpetasAdministrativas 
				WHERE carpetaAdministrativa='".$datosParametros->carpetaJudicial."'";
	$filaCarpeta=$con->obtenerPrimeraFila($consulta);
	
	$consulta="SELECT GROUP_CONCAT(upper(dl.denominacionDelito)) FROM _61_tablaDinamica d,_35_denominacionDelito dl WHERE d.idActividad=".$filaCarpeta[1]." AND
					dl.id__35_denominacionDelito=d.denominacionDelito";
		
	$delitos=$con->obtenerValor($consulta);
	
	$consulta="SELECT upper(CONCAT(IF(nombre IS NULL,'',nombre),' ',IF(apellidoPaterno IS NULL,'',apellidoPaterno),' ',IF(apellidoMaterno IS NULL,'',apellidoMaterno))) 
				FROM _47_tablaDinamica p where id__47_tablaDinamica in(".$datosParametros->imputados.
				") ORDER BY nombre,nombre,apellidoMaterno";
	
	
	$arrValores["fecha"]=convertirFechaLetra(date("Y-m-d"),false,false);
	$arrValores["carpetaJudicial"]=$datosParametros->carpetaJudicial;
	$arrValores["carpetaInvestigacion"]=$filaCarpeta[0];
	$arrValores["imputado"]=$con->obtenerListaValores($consulta);
	
	
	$consulta="SELECT nombreUnidad,claveFolioCarpetas,tipoMateria FROM _17_tablaDinamica WHERE  claveUnidad='".$filaCarpeta[3]."'";
	$filaUGA=$con->obtenerPrimeraFila($consulta);
	
	$noUGA=$filaUGA[1]*1;
	if($filaUGA[2]==2)
		$noUGA="JA";
	
	$victimas="";
	$consulta="SELECT upper(CONCAT(IF(nombre IS NULL,'',nombre),' ',IF(apellidoPaterno IS NULL,'',apellidoPaterno),' ',IF(apellidoMaterno IS NULL,'',apellidoMaterno))) 
				FROM _47_tablaDinamica p,7005_relacionFigurasJuridicasSolicitud r WHERE r.idParticipante=p.id__47_tablaDinamica
				AND r.idActividad=".$filaCarpeta[1]." AND r.idFiguraJuridica=2 ORDER BY nombre,nombre,apellidoMaterno";
	
	$res=$con->obtenerFilas($consulta);
	while($filaImputado=mysql_fetch_row($res))
	{
		$nombre=$filaImputado[0];
/*		if($datosParametros->mostrarVictimasComo!=1)
			$nombre=inicialesNombre($filaImputado[0]);*/
		
		if($victimas=="")
			$victimas=$nombre;
		else
			$victimas.=", ".$nombre;
	}
	
	$arrValores["folioOficio"]="UGJ".$noUGA."/".str_pad($datosParametros->noOficioAsignar,4,"0",STR_PAD_LEFT)."/".date("Y");
	
	$arrValores["victimaCabecera"]=$victimas==""?"________________":$victimas;
	$arrValores["delito"]=$delitos;

	$arrValores["periodoSuspension"]=$datosParametros->periodo;
	$arrValores["fechaFenecimiento"]=convertirFechaLetra(date("Y-m-d",strtotime($datosParametros->fechaFenecimiento)),false,false);
	$arrValores["condiciones"]="";
	$arrValores["plazoIncumplimiento"]=$datosParametros->plazoDias;
	$arrValores["nombreFirmante"]=obtenerNombreUsuario($datosParametros->usuarioDestinatario);
	$arrValores["leyendaTribunal"]=$leyendaTribunal;
	$arrValores["limitePresentacion"]="";
	if($datosParametros->tipoPeriodoLimite==1)
	{
		$arrPeriodoLimite[1]="horas";
		$arrPeriodoLimite[2]="dias";
		$arrPeriodoLimite[3]="meses";
		$arrPeriodoLimite[4]="a&ntilde;os";
		$arrValores["limitePresentacion"]="en ".$datosParametros->periodoLimite." ".$arrPeriodoLimite[$datosParametros->plazoPeriodoLimite];
	}
	else
	{
		$arrValores["limitePresentacion"]="el ".convertirFechaLetra($datosParametros->fechaLimite);
	}
	$numReg=1;
	foreach($datosParametros->suspencionesCondicional as $m)
	{
		if($m->idCondicion!=0)
		{
			$consulta="SELECT fraccion,leyenda FROM _587_tablaDinamica WHERE id__587_tablaDinamica =".$m->idCondicion;
			$fLeyenda=$con->obtenerPrimeraFila($consulta);
			$arrValores["condiciones"].="<p>".$numReg.". ".$fLeyenda[1]."".($m->detalles==""?".":": ".$m->detalles)."</p>";
		}
		else
		{
			$arrValores["condiciones"].="<p>".$numReg.". ".$m->detalles."</p>";
		}
		
		$numReg++;
	}
		
	return $arrValores;
}
?>