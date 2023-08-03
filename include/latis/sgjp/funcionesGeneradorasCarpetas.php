<?php include_once("latis/conexionBD.php");

	//Carpetas Judiciales Control	
	function generarCarpetaJudicialEstandar($idUnidadGestion,$anio,$tipoCarpeta,$folioActual)
	{
		global $con;	
		$arrResultado=array();
		$agregarSecuencia=false;
		
		if($folioActual==0)
		{
			$query="select folioActual FROM 7004_seriesUnidadesGestion WHERE idUnidadGestion=".$idUnidadGestion.
						" AND anio=".$anio." and tipoDelito=''";
			$folioActual=$con->obtenerValor($query);
			
			if($folioActual=="")
			{
				$folioActual=1;
				$agregarSecuencia=true;	
			}
			else
			{
				$folioActual++;
			}
					
		}
		
		//-------
		$folioCorreccion=$folioActual-10;
		if($folioCorreccion<1)
			$folioCorreccion=1;
		$query="SELECT claveFolioCarpetas,claveUnidad FROM _17_tablaDinamica WHERE id__17_tablaDinamica=".$idUnidadGestion;
		$fRegistroUnidad=$con->obtenerPrimeraFila($query);
		$cveUnidadGestion=$fRegistroUnidad[0];
		$cvAdscripcion=$fRegistroUnidad[1];
		$formatoCarpeta= str_pad($cveUnidadGestion,3,"0",STR_PAD_LEFT)."/[folioCarpeta]/".$anio;
		if($idUnidadGestion==16)
			$formatoCarpeta.="-O";
		
		
		$carpetaAdministrativa=str_replace("[folioCarpeta]",str_pad($folioCorreccion,4,"0",STR_PAD_LEFT),$formatoCarpeta);
		while(existeCarpetaAdministrativa($carpetaAdministrativa,""))
		{
			$folioCorreccion++;	
			$carpetaAdministrativa=str_replace("[folioCarpeta]",str_pad($folioCorreccion,4,"0",STR_PAD_LEFT),$formatoCarpeta);
		}
		
		
		if($folioCorreccion<$folioActual)
		{
			registrarRecuperacionFolio($idUnidadGestion,$folioCorreccion,'');
		}
		$folioActual=$folioCorreccion;
		///
		if($agregarSecuencia)
		{
			$query="INSERT INTO 7004_seriesUnidadesGestion(idUnidadGestion,anio,folioActual,tipoDelito) VALUES(".$idUnidadGestion.
					",".$anio.",".$folioActual.",'')";
		}
		else
		{
			$query="update 7004_seriesUnidadesGestion set folioActual=".$folioActual." where idUnidadGestion=".$idUnidadGestion.
					" and anio=".$anio." and tipoDelito=''";
		}	
		
		
		if($con->ejecutarConsulta($query))
		{
			$arrResultado[0]=$carpetaAdministrativa;
			$arrResultado[1]=$folioActual;
			return $arrResultado;
		}
		
		
	}
	
	/*Requiere referencia a proceso*/
	function generarCarpetaJudicialUGA12($idUnidadGestion,$anio,$tipoCarpeta,$folioActual,$idFormulario,$idRegistro)
	{
		global $con;
		$query="";	
		if($idFormulario!=538)
		{
			$query="SELECT tipoAudiencia,idActividad,folioCarpetaInvestigacion FROM _".$idFormulario."_tablaDinamica 
				WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
		}
		else
		{
			$query="SELECT '' as tipoAudiencia,idActividad,'' as folioCarpetaInvestigacion FROM _".$idFormulario."_tablaDinamica 
				WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
		}
		$fRegistroSolicitud=$con->obtenerPrimeraFila($query);
		$tAudiencia=$fRegistroSolicitud[0];
			
		$agregarSecuencia=false;
		if($folioActual==0)
		{
			$query="select folioActual FROM 7004_seriesUnidadesGestion WHERE idUnidadGestion=".$idUnidadGestion.
						" AND anio=".$anio." and tipoDelito=''";
			$folioActual=$con->obtenerValor($query);
			if($folioActual=="")
			{
				$folioActual=1;
				$agregarSecuencia=true;	
			}
			else
			{
				$folioActual++;
			}
					
		}
		
		//-------
		$folioCorreccion=$folioActual-10;
		if($folioCorreccion<1)
			$folioCorreccion=1;
		
		$query="SELECT claveFolioCarpetas,claveUnidad FROM _17_tablaDinamica WHERE id__17_tablaDinamica=".$idUnidadGestion;
		$fRegistroUnidad=$con->obtenerPrimeraFila($query);
		$cveUnidadGestion=$fRegistroUnidad[0];
		$cvAdscripcion=$fRegistroUnidad[1];
		$formatoCarpeta= str_pad($cveUnidadGestion,3,"0",STR_PAD_LEFT)."/[folioCarpeta]/".$anio;
		switch($tAudiencia)
		{
			case 1:
				$formatoCarpeta.="-SD";
			break;
			case 35:
			case 69:
			case 11:
				$formatoCarpeta.="-AI";
			break;
			case 9:
				$formatoCarpeta.="-OC";
			break;
			case 79:
			case 97:
				$formatoCarpeta.="-OA";
			break;
			case 56:
				$formatoCarpeta.="-TM";
			break;
		}
		
		$carpetaAdministrativa=str_replace("[folioCarpeta]",str_pad($folioCorreccion,4,"0",STR_PAD_LEFT),$formatoCarpeta);
		while(existeCarpetaAdministrativa($carpetaAdministrativa,""))
		{
			$folioCorreccion++;	
			$carpetaAdministrativa=str_replace("[folioCarpeta]",str_pad($folioCorreccion,4,"0",STR_PAD_LEFT),$formatoCarpeta);
		}
		
		
		if($folioCorreccion<$folioActual)
		{
			registrarRecuperacionFolio($idUnidadGestion,$folioCorreccion,'');
		}
		$folioActual=$folioCorreccion;
		///
		
		if($agregarSecuencia)
		{
			$query="INSERT INTO 7004_seriesUnidadesGestion(idUnidadGestion,anio,folioActual,tipoDelito) VALUES(".$idUnidadGestion.
					",".$anio.",".$folioActual.",'')";
		}
		else
		{
			$query="update 7004_seriesUnidadesGestion set folioActual=".$folioActual." where idUnidadGestion=".$idUnidadGestion.
					" and anio=".$anio." and tipoDelito=''";
		}	
		
		if($con->ejecutarConsulta($query))
		{
			
			$arrResultado[0]=$carpetaAdministrativa;
			$arrResultado[1]=$folioActual;
			return $arrResultado;
			
		}
		
		
	}
	
	//Carpetas Exhorto
	function generarCarpetaExhortoEstandar($idUnidadGestion,$anio,$tipoCarpeta,$folioActual)
	{
		global $con;	
		$tipoDelito="EX";			
		$agregarSecuencia=false;
		if($folioActual==0)
		{
			$query="select folioActual FROM 7004_seriesUnidadesGestion WHERE idUnidadGestion=".$idUnidadGestion.
						" AND anio=".$anio." and tipoDelito='".$tipoDelito."'";
			$folioActual=$con->obtenerValor($query);
			if($folioActual=="")
			{
				$folioActual=1;
				$agregarSecuencia=true;	
			}
			else
			{
				$folioActual++;
			}
					
		}
		
		//-------
		$folioCorreccion=$folioActual-10;
		if($folioCorreccion<1)
			$folioCorreccion=1;
		
		$query="SELECT claveFolioCarpetas,claveUnidad,siglasClave FROM _17_tablaDinamica WHERE id__17_tablaDinamica=".$idUnidadGestion;
		$fDatosUnidad=$con->obtenerPrimeraFila($query);
		$cveUnidadGestion=$fDatosUnidad[0];
		$formatoCarpeta= str_pad($cveUnidadGestion,3,"0",STR_PAD_LEFT)."/[folioCarpeta]/".$anio."-EX";
		
		
		$carpetaAdministrativa=str_replace("[folioCarpeta]",str_pad($folioCorreccion,4,"0",STR_PAD_LEFT),$formatoCarpeta);
		while(existeCarpetaAdministrativa($carpetaAdministrativa,""))
		{
			$folioCorreccion++;	
			$carpetaAdministrativa=str_replace("[folioCarpeta]",str_pad($folioCorreccion,4,"0",STR_PAD_LEFT),$formatoCarpeta);
		}
		
		
		if($folioCorreccion<$folioActual)
		{
			registrarRecuperacionFolio($idUnidadGestion,$folioCorreccion,'EX');
		}
		$folioActual=$folioCorreccion;
		///
		
		if($agregarSecuencia)
		{
			$query="INSERT INTO 7004_seriesUnidadesGestion(idUnidadGestion,anio,folioActual,tipoDelito) VALUES(".$idUnidadGestion.
					",".$anio.",".$folioActual.",'".$tipoDelito."')";
		}
		else
		{
			$query="update 7004_seriesUnidadesGestion set folioActual=".$folioActual." where idUnidadGestion=".$idUnidadGestion.
					" and anio=".$anio." and tipoDelito='".$tipoDelito."'";
		}	
		
		if($con->ejecutarConsulta($query))
		{
			
			$arrResultado[0]=$carpetaAdministrativa;
			$arrResultado[1]=$folioActual;
			return $arrResultado;
			
		}
		
		
	}
	
	function generarCarpetaExhortoEjecucion($idUnidadGestion,$anio,$tipoCarpeta,$folioActual)
	{
		global $con;	
		$tipoDelito="EX";			
		$agregarSecuencia=false;
		if($folioActual==0)
		{
			$query="select folioActual FROM 7004_seriesUnidadesGestion WHERE idUnidadGestion=".$idUnidadGestion.
						" AND anio=".$anio." and tipoDelito='".$tipoDelito."'";
			$folioActual=$con->obtenerValor($query);
			if($folioActual=="")
			{
				$folioActual=1;
				$agregarSecuencia=true;	
			}
			else
			{
				$folioActual++;
			}
					
		}
		
		$folioCorreccion=$folioActual-10;
		if($folioCorreccion<1)
			$folioCorreccion=1;
		$query="SELECT claveFolioCarpetas,claveUnidad,siglasClave FROM _17_tablaDinamica WHERE id__17_tablaDinamica=".$idUnidadGestion;
		$fDatosUnidad=$con->obtenerPrimeraFila($query);
		$cveUnidadGestion=$fDatosUnidad[0];
		$formatoCarpeta= "EJEC-".$fDatosUnidad[2]."-EXH/[folioCarpeta]/".$anio;
		
		$carpetaAdministrativa=str_replace("[folioCarpeta]",str_pad($folioCorreccion,4,"0",STR_PAD_LEFT),$formatoCarpeta);
		while(existeCarpetaAdministrativa($carpetaAdministrativa,""))
		{
			$folioCorreccion++;	
			$carpetaAdministrativa=str_replace("[folioCarpeta]",str_pad($folioCorreccion,4,"0",STR_PAD_LEFT),$formatoCarpeta);
		}
		
		
		if($folioCorreccion<$folioActual)
		{
			registrarRecuperacionFolio($idUnidadGestion,$folioCorreccion,'EX');
		}
		$folioActual=$folioCorreccion;
		
		if($agregarSecuencia)
		{
			$query="INSERT INTO 7004_seriesUnidadesGestion(idUnidadGestion,anio,folioActual,tipoDelito) VALUES(".$idUnidadGestion.
					",".$anio.",".$folioActual.",'".$tipoDelito."')";
		}
		else
		{
			$query="update 7004_seriesUnidadesGestion set folioActual=".$folioActual." where idUnidadGestion=".$idUnidadGestion.
					" and anio=".$anio." and tipoDelito='".$tipoDelito."'";
		}	
		
		if($con->ejecutarConsulta($query))
		{
			$arrResultado[0]=$carpetaAdministrativa;
			$arrResultado[1]=$folioActual;
			return $arrResultado;
			
		}
		
		
	}
	

	/*Requiere referencia a proceso*/
	//Carpetas de Amparo
	function generarCarpetaAmparoEstandar($idUnidadGestion,$anio,$tipoCarpeta,$folioActual,$idRegistro)
	{
		global $con;	
		
		
		$carpetaInvestigacion="";
		$consulta="SELECT * FROM _346_tablaDinamica WHERE id__346_tablaDinamica=".$idRegistro;
		$fRegistro=$con->obtenerPrimeraFilaAsoc($consulta);
		
		$tipoDelito="AT";	
		$codigoInstitucion=$fRegistro["codigoInstitucion"];
		if($fRegistro["categoriaAmparo"]==1)
		{
			$consulta="SELECT unidadGestion,carpetaInvestigacion FROM 7006_carpetasAdministrativas WHERE   carpetaAdministrativa='".$fRegistro["carpetaAdministrativa"]."'";
			$fCarpetaBase=$con->obtenerPrimeraFila($consulta);
			$codigoInstitucion=$fCarpetaBase[0];
			$carpetaInvestigacion=$fCarpetaBase[1];
			$tipoDelito="AC";
		}
		
		
			
		$agregarSecuencia=false;
		if($folioActual==0)
		{
			$query="select folioActual FROM 7004_seriesUnidadesGestion WHERE idUnidadGestion=".$idUnidadGestion.
						" AND anio=".$anio." and tipoDelito='".$tipoDelito."'";
			$folioActual=$con->obtenerValor($query);
			if($folioActual=="")
			{
				$folioActual=1;
				$agregarSecuencia=true;	
			}
			else
			{
				$folioActual++;
			}
					
		}
		
		$folioCorreccion=$folioActual-10;
		if($folioCorreccion<1)
			$folioCorreccion=1;
		$query="SELECT claveUnidad,claveFolioCarpetas,siglasClave FROM _17_tablaDinamica WHERE id__17_tablaDinamica=".$idUnidadGestion;

		$fDatosUnidadBase=$con->obtenerPrimeraFila($query);
		$cveUnidadGestion=$fDatosUnidadBase[0];
		
		$formatoCarpeta= $fDatosUnidadBase[1]."-".$tipoDelito."/[folioCarpeta]/".$anio;
		
		
		$carpetaAdministrativa=str_replace("[folioCarpeta]",str_pad($folioCorreccion,4,"0",STR_PAD_LEFT),$formatoCarpeta);
		while(existeCarpetaAdministrativa($carpetaAdministrativa,""))
		{
			$folioCorreccion++;	
			$carpetaAdministrativa=str_replace("[folioCarpeta]",str_pad($folioCorreccion,4,"0",STR_PAD_LEFT),$formatoCarpeta);
		}
		
		
		if($folioCorreccion<$folioActual)
		{
			registrarRecuperacionFolio($idUnidadGestion,$folioCorreccion,$tipoDelito);
		}
		$folioActual=$folioCorreccion;
		
		
		if($agregarSecuencia)
		{
			$query="INSERT INTO 7004_seriesUnidadesGestion(idUnidadGestion,anio,folioActual,tipoDelito) VALUES(".$idUnidadGestion.
					",".$anio.",".$folioActual.",'".$tipoDelito."')";
		}
		else
		{
			$query="update 7004_seriesUnidadesGestion set folioActual=".$folioActual." where idUnidadGestion=".$idUnidadGestion.
					" and anio=".$anio." and tipoDelito='".$tipoDelito."'";
		}	
		
		if($con->ejecutarConsulta($query))
		{
			
			$arrResultado[0]=$carpetaAdministrativa;
			$arrResultado[1]=$folioActual;
			return $arrResultado;
			
		}
		
		
	}
	
	/*Requiere referencia a proceso*/
	function generarCarpetaAmparoEjecucion($idUnidadGestion,$anio,$tipoCarpeta,$folioActual,$idRegistro)
	{
		global $con;	
		
		
		$carpetaInvestigacion="";
		$consulta="SELECT * FROM _346_tablaDinamica WHERE id__346_tablaDinamica=".$idRegistro;
		$fRegistro=$con->obtenerPrimeraFilaAsoc($consulta);
		
		
		$tipoDelito="AT";	
		$codigoInstitucion=$fRegistro["codigoInstitucion"];
		if($fRegistro["categoriaAmparo"]==1)
		{
			$consulta="SELECT unidadGestion,carpetaInvestigacion FROM 7006_carpetasAdministrativas WHERE   carpetaAdministrativa='".$fRegistro["carpetaAdministrativa"]."'";
			$fCarpetaBase=$con->obtenerPrimeraFila($consulta);
			$codigoInstitucion=$fCarpetaBase[0];
			$carpetaInvestigacion=$fCarpetaBase[1];
			$tipoDelito="AC";
		}
		
		
			
		$agregarSecuencia=false;
		if($folioActual==0)
		{
			$query="select folioActual FROM 7004_seriesUnidadesGestion WHERE idUnidadGestion=".$idUnidadGestion.
						" AND anio=".$anio." and tipoDelito='".$tipoDelito."'";
			$folioActual=$con->obtenerValor($query);
			if($folioActual=="")
			{
				$folioActual=1;
				$agregarSecuencia=true;	
			}
			else
			{
				$folioActual++;
			}
					
		}
		
		$folioCorreccion=$folioActual-10;
		if($folioCorreccion<1)
			$folioCorreccion=1;
		$query="SELECT claveUnidad,claveFolioCarpetas,siglasClave FROM _17_tablaDinamica WHERE id__17_tablaDinamica=".$idUnidadGestion;

		$fDatosUnidad=$con->obtenerPrimeraFila($query);
		$cveUnidadGestion=$fDatosUnidad[0];			
		$formatoCarpeta= "EJEC-".$fDatosUnidad[2]."-".$tipoDelito."/[folioCarpeta]/".$anio;
		
		
		$carpetaAdministrativa=str_replace("[folioCarpeta]",str_pad($folioCorreccion,4,"0",STR_PAD_LEFT),$formatoCarpeta);
		while(existeCarpetaAdministrativa($carpetaAdministrativa,""))
		{
			$folioCorreccion++;	
			$carpetaAdministrativa=str_replace("[folioCarpeta]",str_pad($folioCorreccion,4,"0",STR_PAD_LEFT),$formatoCarpeta);
		}
		
		
		if($folioCorreccion<$folioActual)
		{
			registrarRecuperacionFolio($idUnidadGestion,$folioCorreccion,$tipoDelito);
		}
		$folioActual=$folioCorreccion;
		
		if($agregarSecuencia)
		{
			$query="INSERT INTO 7004_seriesUnidadesGestion(idUnidadGestion,anio,folioActual,tipoDelito) VALUES(".$idUnidadGestion.
					",".$anio.",".$folioActual.",'".$tipoDelito."')";
		}
		else
		{
			$query="update 7004_seriesUnidadesGestion set folioActual=".$folioActual." where idUnidadGestion=".$idUnidadGestion.
					" and anio=".$anio." and tipoDelito='".$tipoDelito."'";
		}	
		
		if($con->ejecutarConsulta($query))
		{
			
			$arrResultado[0]=$carpetaAdministrativa;
			$arrResultado[1]=$folioActual;
			return $arrResultado;
			
		}
		
		
	}
	
	//Carpetas de Apelacion
	function generarCarpetaApelacionEstandar($idUnidadGestion,$anio,$tipoCarpeta,$folioActual)
	{
		global $con;	
		
		$tipoDelito="APEL";	
		
			
		$agregarSecuencia=false;
		if($folioActual==0)
		{
			$query="select folioActual FROM 7004_seriesUnidadesGestion WHERE idUnidadGestion=".$idUnidadGestion.
						" AND anio=".$anio." and tipoDelito='".$tipoDelito."'";
			$folioActual=$con->obtenerValor($query);
			if($folioActual=="")
			{
				$folioActual=1;
				$agregarSecuencia=true;	
			}
			else
			{
				$folioActual++;
			}
					
		}
		
		$folioCorreccion=$folioActual-10;
		if($folioCorreccion<1)
			$folioCorreccion=1;
		$query="SELECT claveUnidad,claveFolioCarpetas,siglasClave FROM _17_tablaDinamica WHERE id__17_tablaDinamica=".$idUnidadGestion;

		$fDatosUnidadBase=$con->obtenerPrimeraFila($query);
		$cveUnidadGestion=$fDatosUnidadBase[0];
		
		$formatoCarpeta= $fDatosUnidadBase[1]."-".$tipoDelito."/[folioCarpeta]/".$anio;
		
		
		$carpetaAdministrativa=str_replace("[folioCarpeta]",str_pad($folioCorreccion,4,"0",STR_PAD_LEFT),$formatoCarpeta);
		while(existeCarpetaAdministrativa($carpetaAdministrativa,""))
		{
			$folioCorreccion++;	
			$carpetaAdministrativa=str_replace("[folioCarpeta]",str_pad($folioCorreccion,4,"0",STR_PAD_LEFT),$formatoCarpeta);
		}
		
		
		if($folioCorreccion<$folioActual)
		{
			registrarRecuperacionFolio($idUnidadGestion,$folioCorreccion,$tipoDelito);
		}
		$folioActual=$folioCorreccion;
		
		if($agregarSecuencia)
		{
			$query="INSERT INTO 7004_seriesUnidadesGestion(idUnidadGestion,anio,folioActual,tipoDelito) VALUES(".$idUnidadGestion.
					",".$anio.",".$folioActual.",'".$tipoDelito."')";
		}
		else
		{
			$query="update 7004_seriesUnidadesGestion set folioActual=".$folioActual." where idUnidadGestion=".$idUnidadGestion.
					" and anio=".$anio." and tipoDelito='".$tipoDelito."'";
		}	
		
		if($con->ejecutarConsulta($query))
		{
			
			$arrResultado[0]=$carpetaAdministrativa;
			$arrResultado[1]=$folioActual;
			return $arrResultado;
			
		}
		
		
	}
	
	function generarCarpetaApelacionEjecucion($idUnidadGestion,$anio,$tipoCarpeta,$folioActual)
	{
		global $con;	
		
		$tipoDelito="APEL";	
		
			
		$agregarSecuencia=false;
		if($folioActual==0)
		{
			$query="select folioActual FROM 7004_seriesUnidadesGestion WHERE idUnidadGestion=".$idUnidadGestion.
						" AND anio=".$anio." and tipoDelito='".$tipoDelito."'";
			$folioActual=$con->obtenerValor($query);
			if($folioActual=="")
			{
				$folioActual=1;
				$agregarSecuencia=true;	
			}
			else
			{
				$folioActual++;
			}
					
		}
		
		$folioCorreccion=$folioActual-10;
		if($folioCorreccion<1)
			$folioCorreccion=1;
		$query="SELECT claveUnidad,claveFolioCarpetas,siglasClave FROM _17_tablaDinamica WHERE id__17_tablaDinamica=".$idUnidadGestion;

		$fDatosUnidadBase=$con->obtenerPrimeraFila($query);
		$cveUnidadGestion=$fDatosUnidadBase[0];
		
		$formatoCarpeta= "EJEC-".$fDatosUnidadBase[2]."-".$tipoDelito."/[folioCarpeta]/".$anio;
		
		
		$carpetaAdministrativa=str_replace("[folioCarpeta]",str_pad($folioCorreccion,4,"0",STR_PAD_LEFT),$formatoCarpeta);
		while(existeCarpetaAdministrativa($carpetaAdministrativa,""))
		{
			$folioCorreccion++;	
			$carpetaAdministrativa=str_replace("[folioCarpeta]",str_pad($folioCorreccion,4,"0",STR_PAD_LEFT),$formatoCarpeta);
		}
		
		
		if($folioCorreccion<$folioActual)
		{
			registrarRecuperacionFolio($idUnidadGestion,$folioCorreccion,$tipoDelito);
		}
		$folioActual=$folioCorreccion;
		
		if($agregarSecuencia)
		{
			$query="INSERT INTO 7004_seriesUnidadesGestion(idUnidadGestion,anio,folioActual,tipoDelito) VALUES(".$idUnidadGestion.
					",".$anio.",".$folioActual.",'".$tipoDelito."')";
		}
		else
		{
			$query="update 7004_seriesUnidadesGestion set folioActual=".$folioActual." where idUnidadGestion=".$idUnidadGestion.
					" and anio=".$anio." and tipoDelito='".$tipoDelito."'";
		}	
		
		if($con->ejecutarConsulta($query))
		{
			
			$arrResultado[0]=$carpetaAdministrativa;
			$arrResultado[1]=$folioActual;
			return $arrResultado;
			
		}
		
		
	}
		
	//Carpetas Tribunal de enjuiciamiento
	function generarCarpetaTribunalEnjuiciamientoEstandar($idUnidadGestion,$anio,$tipoCarpeta,$folioActual)
	{
		global $con;	
		
		$tipoDelito="TE";	
		
			
		$agregarSecuencia=false;
		if($folioActual==0)
		{
			$query="select folioActual FROM 7004_seriesUnidadesGestion WHERE idUnidadGestion=".$idUnidadGestion.
						" AND anio=".$anio." and tipoDelito='".$tipoDelito."'";
			$folioActual=$con->obtenerValor($query);
			if($folioActual=="")
			{
				$folioActual=1;
				$agregarSecuencia=true;	
			}
			else
			{
				$folioActual++;
			}
					
		}
		
		$folioCorreccion=$folioActual-10;
		if($folioCorreccion<1)
			$folioCorreccion=1;
		$query="SELECT claveUnidad,claveFolioCarpetas FROM _17_tablaDinamica WHERE id__17_tablaDinamica=".$idUnidadGestion;
		$fDatosUnidadTribunalE=$con->obtenerPrimeraFila($query);
		$cveUnidadGestion=$fDatosUnidadTribunalE[0];
		
		$formatoCarpeta= "TE".$fDatosUnidadTribunalE[1]."/[folioCarpeta]/".$anio;
		
		
		$carpetaAdministrativa=str_replace("[folioCarpeta]",str_pad($folioCorreccion,4,"0",STR_PAD_LEFT),$formatoCarpeta);
		while(existeCarpetaAdministrativa($carpetaAdministrativa,""))
		{
			$folioCorreccion++;	
			$carpetaAdministrativa=str_replace("[folioCarpeta]",str_pad($folioCorreccion,4,"0",STR_PAD_LEFT),$formatoCarpeta);
		}
		
		
		if($folioCorreccion<$folioActual)
		{
			registrarRecuperacionFolio($idUnidadGestion,$folioCorreccion,$tipoDelito);
		}
		$folioActual=$folioCorreccion;
		
		if($agregarSecuencia)
		{
			$query="INSERT INTO 7004_seriesUnidadesGestion(idUnidadGestion,anio,folioActual,tipoDelito) VALUES(".$idUnidadGestion.
					",".$anio.",".$folioActual.",'".$tipoDelito."')";
		}
		else
		{
			$query="update 7004_seriesUnidadesGestion set folioActual=".$folioActual." where idUnidadGestion=".$idUnidadGestion.
					" and anio=".$anio." and tipoDelito='".$tipoDelito."'";
		}	
		
		if($con->ejecutarConsulta($query))
		{
			$query="SELECT claveUnidad,claveFolioCarpetas FROM _17_tablaDinamica WHERE id__17_tablaDinamica=".$idUnidadGestion;
			$fDatosUnidadTribunalE=$con->obtenerPrimeraFila($query);
			$cveUnidadGestion=$fDatosUnidadTribunalE[0];
			
			$carpetaAdministrativa= "TE".$fDatosUnidadTribunalE[1]."/".str_pad($folioActual,4,"0",STR_PAD_LEFT)."/".$anio;
			
			$arrResultado[0]=$carpetaAdministrativa;
			$arrResultado[1]=$folioActual;
			return $arrResultado;
			
			
		}
		
		
	}
	
	function generarCarpetaTribunalEnjuiciamientoUGA5($idUnidadGestion,$anio,$tipoCarpeta,$folioActual)
	{
		global $con;	
		
		$tipoDelito="TE";	
		
			
		$agregarSecuencia=false;
		if($folioActual==0)
		{
			$query="select folioActual FROM 7004_seriesUnidadesGestion WHERE idUnidadGestion=".$idUnidadGestion.
						" AND anio=".$anio." and tipoDelito='".$tipoDelito."'";
			$folioActual=$con->obtenerValor($query);
			if($folioActual=="")
			{
				$folioActual=1;
				$agregarSecuencia=true;	
			}
			else
			{
				$folioActual++;
			}
					
		}
		
		$folioCorreccion=$folioActual-10;
		if($folioCorreccion<1)
			$folioCorreccion=1;
		$query="SELECT claveUnidad,claveFolioCarpetas FROM _17_tablaDinamica WHERE id__17_tablaDinamica=".$idUnidadGestion;
		$fDatosUnidadTribunalE=$con->obtenerPrimeraFila($query);
		$cveUnidadGestion=$fDatosUnidadTribunalE[0];
		
		$formatoCarpeta= "TE/[folioCarpeta]/".$anio;
		$carpetaAdministrativa=str_replace("[folioCarpeta]",str_pad($folioCorreccion,4,"0",STR_PAD_LEFT),$formatoCarpeta);
		while(existeCarpetaAdministrativa($carpetaAdministrativa,""))
		{
			$folioCorreccion++;	
			$carpetaAdministrativa=str_replace("[folioCarpeta]",str_pad($folioCorreccion,4,"0",STR_PAD_LEFT),$formatoCarpeta);
		}
		
		
		if($folioCorreccion<$folioActual)
		{
			registrarRecuperacionFolio($idUnidadGestion,$folioCorreccion,$tipoDelito);
		}
		$folioActual=$folioCorreccion;
		
		if($agregarSecuencia)
		{
			$query="INSERT INTO 7004_seriesUnidadesGestion(idUnidadGestion,anio,folioActual,tipoDelito) VALUES(".$idUnidadGestion.
					",".$anio.",".$folioActual.",'".$tipoDelito."')";
		}
		else
		{
			$query="update 7004_seriesUnidadesGestion set folioActual=".$folioActual." where idUnidadGestion=".$idUnidadGestion.
					" and anio=".$anio." and tipoDelito='".$tipoDelito."'";
		}	
		
		if($con->ejecutarConsulta($query))
		{
			$arrResultado[0]=$carpetaAdministrativa;
			$arrResultado[1]=$folioActual;
			return $arrResultado;
			
			
		}
		
		
	}
	
	function generarCarpetaTribunalEnjuiciamientoAdolescentes($idUnidadGestion,$anio,$tipoCarpeta,$folioActual)
	{
		global $con;	
		
		$tipoDelito="TE";	
		
			
		$agregarSecuencia=false;
		if($folioActual==0)
		{
			$query="select folioActual FROM 7004_seriesUnidadesGestion WHERE idUnidadGestion=".$idUnidadGestion.
						" AND anio=".$anio." and tipoDelito='".$tipoDelito."'";
			$folioActual=$con->obtenerValor($query);
			if($folioActual=="")
			{
				$folioActual=1;
				$agregarSecuencia=true;	
			}
			else
			{
				$folioActual++;
			}
					
		}
		
		$folioCorreccion=$folioActual-10;
		if($folioCorreccion<1)
			$folioCorreccion=1;
		$query="SELECT claveUnidad,claveFolioCarpetas FROM _17_tablaDinamica WHERE id__17_tablaDinamica=".$idUnidadGestion;
		$fDatosUnidadTribunalE=$con->obtenerPrimeraFila($query);
		$cveUnidadGestion=$fDatosUnidadTribunalE[0];
		
		$formatoCarpeta= "TEJA/[folioCarpeta]/".$anio;
		
		
		$carpetaAdministrativa=str_replace("[folioCarpeta]",str_pad($folioCorreccion,4,"0",STR_PAD_LEFT),$formatoCarpeta);
		while(existeCarpetaAdministrativa($carpetaAdministrativa,""))
		{
			$folioCorreccion++;	
			$carpetaAdministrativa=str_replace("[folioCarpeta]",str_pad($folioCorreccion,4,"0",STR_PAD_LEFT),$formatoCarpeta);
		}
		
		
		if($folioCorreccion<$folioActual)
		{
			registrarRecuperacionFolio($idUnidadGestion,$folioCorreccion,$tipoDelito);
		}
		$folioActual=$folioCorreccion;
		
		if($agregarSecuencia)
		{
			$query="INSERT INTO 7004_seriesUnidadesGestion(idUnidadGestion,anio,folioActual,tipoDelito) VALUES(".$idUnidadGestion.
					",".$anio.",".$folioActual.",'".$tipoDelito."')";
		}
		else
		{
			$query="update 7004_seriesUnidadesGestion set folioActual=".$folioActual." where idUnidadGestion=".$idUnidadGestion.
					" and anio=".$anio." and tipoDelito='".$tipoDelito."'";
		}	
		
		if($con->ejecutarConsulta($query))
		{
			
			
			$arrResultado[0]=$carpetaAdministrativa;
			$arrResultado[1]=$folioActual;
			return $arrResultado;
			
			
		}
		
		
	}
	
	//Carpetas Ejecucion
	function generarCarpetaEjecucionEstandar($idUnidadGestion,$anio,$tipoCarpeta,$folioActual,$idFormulario)
	{
		global $con;	
		
			
		$tipoDelito="EJEC";	
		
			
		$agregarSecuencia=false;
		if($folioActual==0)
		{
			$query="select folioActual FROM 7004_seriesUnidadesGestion WHERE idUnidadGestion=".$idUnidadGestion.
						" AND anio=".$anio." and tipoDelito='".$tipoDelito."'";
			$folioActual=$con->obtenerValor($query);
			if($folioActual=="")
			{
				$folioActual=1;
				$agregarSecuencia=true;	
			}
			else
			{
				$folioActual++;
			}
					
		}
		
		$folioCorreccion=$folioActual-10;
		if($folioCorreccion<1)
			$folioCorreccion=1;
		$query="SELECT claveUnidad,siglasClave FROM _17_tablaDinamica WHERE id__17_tablaDinamica=".$idUnidadGestion;
		$fDatosUnidad=$con->obtenerPrimeraFila($query);
		$cveUnidadGestion=$fDatosUnidad[0];
		
		$formatoCarpeta= "EJEC-".$fDatosUnidad[1]."/[folioCarpeta]/".$anio;
		
		
		$carpetaAdministrativa=str_replace("[folioCarpeta]",str_pad($folioCorreccion,4,"0",STR_PAD_LEFT),$formatoCarpeta);
		while(existeCarpetaAdministrativa($carpetaAdministrativa,""))
		{
			$folioCorreccion++;	
			$carpetaAdministrativa=str_replace("[folioCarpeta]",str_pad($folioCorreccion,4,"0",STR_PAD_LEFT),$formatoCarpeta);
		}
		
		
		if($folioCorreccion<$folioActual)
		{
			registrarRecuperacionFolio($idUnidadGestion,$folioCorreccion,$tipoDelito);
		}
		$folioActual=$folioCorreccion;
		
		
		if($agregarSecuencia)
		{
			$query="INSERT INTO 7004_seriesUnidadesGestion(idUnidadGestion,anio,folioActual,tipoDelito) VALUES(".$idUnidadGestion.
					",".$anio.",".$folioActual.",'".$tipoDelito."')";
		}
		else
		{
			$query="update 7004_seriesUnidadesGestion set folioActual=".$folioActual." where idUnidadGestion=".$idUnidadGestion.
					" and anio=".$anio." and tipoDelito='".$tipoDelito."'";
		}	
		
		if($con->ejecutarConsulta($query))
		{
			
			
			$arrResultado[0]=$carpetaAdministrativa;
			$arrResultado[1]=$folioActual;
			return $arrResultado;
			
			
		}
		
		
	}	
	
	function generarCarpetaExpedienteJuzgado($idUnidadGestion,$anio,$tipoCarpeta,$folioActual)
	{
		global $con;	
		
		$consulta="SELECT claveUnidad FROM _17_tablaDinamica WHERE id__17_tablaDinamica=".$idUnidadGestion;
		$claveUnidad=$con->obtenerValor($query);
		$arrResultado=array();
		$agregarSecuencia=false;
		
		if($folioActual==0)
		{
			$query="select folioActual FROM 7004_seriesUnidadesGestion WHERE idUnidadGestion=".$idUnidadGestion.
						" AND anio=".$anio." and tipoDelito=''";
			$folioActual=$con->obtenerValor($query);
			
			if($folioActual=="")
			{
				$folioActual=1;
				$agregarSecuencia=true;	
			}
			else
			{
				$folioActual++;
			}
					
		}
		
		$folioCorreccion=$folioActual-10;
		if($folioCorreccion<1)
			$folioCorreccion=1;
		$formatoCarpeta= "[folioCarpeta]/".$anio;
		
		
		$carpetaAdministrativa=str_replace("[folioCarpeta]",str_pad($folioCorreccion,4,"0",STR_PAD_LEFT),$formatoCarpeta);
		while(existeCarpetaAdministrativa($carpetaAdministrativa,$claveUnidad))
		{
			$folioCorreccion++;	
			$carpetaAdministrativa=str_replace("[folioCarpeta]",str_pad($folioCorreccion,4,"0",STR_PAD_LEFT),$formatoCarpeta);
		}
		
		
		if($folioCorreccion<$folioActual)
		{
			registrarRecuperacionFolio($idUnidadGestion,$folioCorreccion,'');
		}
		$folioActual=$folioCorreccion;
		
		if($agregarSecuencia)
		{
			$query="INSERT INTO 7004_seriesUnidadesGestion(idUnidadGestion,anio,folioActual,tipoDelito) VALUES(".$idUnidadGestion.
					",".$anio.",".$folioActual.",'')";
		}
		else
		{
			$query="update 7004_seriesUnidadesGestion set folioActual=".$folioActual." where idUnidadGestion=".$idUnidadGestion.
					" and anio=".$anio." and tipoDelito=''";
		}	
		
		
		if($con->ejecutarConsulta($query))
		{
			
			$arrResultado[0]=$carpetaAdministrativa;
			$arrResultado[1]=$folioActual;
			return $arrResultado;
		}
		
		
	}
	
	function generarCarpetaEjecucionLeyNacional($idUnidadGestion,$anio,$tipoCarpeta,$folioActual)
	{
		global $con;			
		$tipoDelito="LN";			
			
		$agregarSecuencia=false;
		if($folioActual==0)
		{
			$query="select folioActual FROM 7004_seriesUnidadesGestion WHERE idUnidadGestion=".$idUnidadGestion.
						" AND anio=".$anio." and tipoDelito='".$tipoDelito."'";
			$folioActual=$con->obtenerValor($query);
			if($folioActual=="")
			{
				$folioActual=1;
				$agregarSecuencia=true;	
			}
			else
			{
				$folioActual++;
			}
					
		}
		
		$folioCorreccion=$folioActual-10;
		if($folioCorreccion<1)
			$folioCorreccion=1;
		$query="SELECT claveUnidad,siglasClave FROM _17_tablaDinamica WHERE id__17_tablaDinamica=".$idUnidadGestion;
		$fDatosUnidad=$con->obtenerPrimeraFila($query);
		$cveUnidadGestion=$fDatosUnidad[0];
		
		$formatoCarpeta= "EJEC-".$fDatosUnidad[1]."/[folioCarpeta]/".$anio."-LN";
		
		
		$carpetaAdministrativa=str_replace("[folioCarpeta]",str_pad($folioCorreccion,4,"0",STR_PAD_LEFT),$formatoCarpeta);
		while(existeCarpetaAdministrativa($carpetaAdministrativa,""))
		{
			$folioCorreccion++;	
			$carpetaAdministrativa=str_replace("[folioCarpeta]",str_pad($folioCorreccion,4,"0",STR_PAD_LEFT),$formatoCarpeta);
		}
		
		
		if($folioCorreccion<$folioActual)
		{
			registrarRecuperacionFolio($idUnidadGestion,$folioCorreccion,$tipoDelito);
		}
		$folioActual=$folioCorreccion;
		
		if($agregarSecuencia)
		{
			$query="INSERT INTO 7004_seriesUnidadesGestion(idUnidadGestion,anio,folioActual,tipoDelito) VALUES(".$idUnidadGestion.
					",".$anio.",".$folioActual.",'".$tipoDelito."')";
		}
		else
		{
			$query="update 7004_seriesUnidadesGestion set folioActual=".$folioActual." where idUnidadGestion=".$idUnidadGestion.
					" and anio=".$anio." and tipoDelito='".$tipoDelito."'";
		}	
		
		if($con->ejecutarConsulta($query))
		{
			
			
			$arrResultado[0]=$carpetaAdministrativa;
			$arrResultado[1]=$folioActual;

			return $arrResultado;
			
			
		}
		
		
	}	
	
	function generarCarpetaJudicialAlzada($idUnidadGestion,$anio,$tipoCarpeta,$folioActual,$idFormulario,$idRegistro)
	{
		global $con;			
		$tipoDelito="TOCA";			
		
		$cache=NULL;
		$cadObj='{"idUnidadGestion":"'.$idUnidadGestion.'","anio":"'.$anio.'","tipoCarpeta":"'.$tipoCarpeta.
				'","idFormulario":"'.$idFormulario.'","idRegistro":"'.$idRegistro.'"}';		
		$obj=json_decode($cadObj);		
		
		$consulta="SELECT claveUnidad,claveFolioCarpetas,siglasClave,prioridad FROM _17_tablaDinamica WHERE id__17_tablaDinamica=".$idUnidadGestion;
		$fUnidadGestion=$con->obtenerPrimeraFila($consulta);
		$arrParametrosFolio=array();
		$arrParametrosFolio["claveUnidad"]=$fUnidadGestion[0];
		$arrParametrosFolio["claveFolioCarpetas"]=$fUnidadGestion[1];
		$arrParametrosFolio["siglasClave"]=$fUnidadGestion[2];
		$arrParametrosFolio["ordinal"]=$fUnidadGestion[3];
		$arrParametrosFolio["anio"]=$anio;
		
		$fConfiguracion=NULL;
		
		$consulta="SELECT formatoCarpetaAlzada,tamanoFolio,funcionAplicacion,funcionAsignacionFolio 
					FROM _512_tablaDinamica WHERE codigoInstitucion='".$fUnidadGestion[0]."' ORDER BY id__512_tablaDinamica DESC";	
		$resConf=$con->obtenerFilas($consulta);
		while($fConfiguracion=mysql_fetch_row($resConf))
		{
			if(($fConfiguracion[2]!="")&&($fConfiguracion[2]!=-1))
			{
				$aplicaFolio=removerComillasLimite(resolverExpresionCalculoPHP($fConfiguracion[2],$obj,$cache));
				if($aplicaFolio==1)
					break;
				
			}
			else
			{
				break;
			}

		}
		
		if(!$fConfiguracion)
			return NULL;	
			
		$agregarSecuencia=false;
		if($folioActual==0)
		{
			if(($fConfiguracion[3]=="")||($fConfiguracion[3]=="-1"))
			{
				$query="select folioActual FROM 7004_seriesUnidadesGestion WHERE idUnidadGestion=".$idUnidadGestion.
							" AND anio=".$anio." and tipoDelito='".$tipoDelito."'";
				$folioActual=$con->obtenerValor($query);
				if($folioActual=="")
				{
					$folioActual=1;
					$agregarSecuencia=true;	
				}
				else
				{
					$folioActual++;
				}
			}
			else
			{
				$arrFolio=resolverExpresionCalculoPHP($fConfiguracion[3],$obj,$cache);
				$folioActual=$arrFolio["folioActual"];
				$tipoDelito=$arrFolio["tipoDelito"];
				$agregarSecuencia=$arrFolio["agregarSecuencia"];
			}
					
		}
		
		
		if($agregarSecuencia)
		{
			$query="INSERT INTO 7004_seriesUnidadesGestion(idUnidadGestion,anio,folioActual,tipoDelito) VALUES(".$idUnidadGestion.
					",".$anio.",".$folioActual.",'".$tipoDelito."')";
		}
		else
		{
			$query="update 7004_seriesUnidadesGestion set folioActual=".$folioActual." where idUnidadGestion=".$idUnidadGestion.
					" and anio=".$anio." and tipoDelito='".$tipoDelito."'";
		}	
		
		$tamanoFolio=$fConfiguracion[1];
		
		if($con->ejecutarConsulta($query))
		{
			
			$arrParametrosFolio["folio"]=str_pad($folioActual,$tamanoFolio,"0",STR_PAD_LEFT);			
			
			$carpetaAdministrativa= $fConfiguracion[0];
			foreach($arrParametrosFolio as $param=>$valor)
			{
				$carpetaAdministrativa=str_replace('{'.$param.'}',$valor,$carpetaAdministrativa);
			}
			
			$arrResultado[0]=$carpetaAdministrativa;
			$arrResultado[1]=$folioActual;

			return $arrResultado;
			
			
		}
		
		
	}	
	
	
	function generarCarpetaAlzadaCivil($idUnidadGestion,$anio,$tipoCarpeta,$folioActual,$idFormulario,$idRegistro)
	{
		global $con;			
		$tipoDelito="TOCA";			
		
		$cache=NULL;
		$cadObj='{"idUnidadGestion":"'.$idUnidadGestion.'","anio":"'.$anio.'","tipoCarpeta":"'.$tipoCarpeta.
				'","idFormulario":"'.$idFormulario.'","idRegistro":"'.$idRegistro.'"}';		
		$obj=json_decode($cadObj);		
		
		$consulta="SELECT claveUnidad,claveFolioCarpetas,siglasClave,prioridad FROM _17_tablaDinamica WHERE id__17_tablaDinamica=".$idUnidadGestion;
		$fUnidadGestion=$con->obtenerPrimeraFila($consulta);
		$arrParametrosFolio=array();
		$arrParametrosFolio["claveUnidad"]=$fUnidadGestion[0];
		$arrParametrosFolio["claveFolioCarpetas"]=$fUnidadGestion[1];
		$arrParametrosFolio["siglasClave"]=$fUnidadGestion[2];
		$arrParametrosFolio["ordinal"]=$fUnidadGestion[3];
		$arrParametrosFolio["anio"]=$anio;
		
		$fConfiguracion=NULL;
		
		$consulta="SELECT formatoCarpetaAlzada,tamanoFolio,funcionAplicacion,funcionAsignacionFolio 
					FROM _528_tablaDinamica WHERE codigoInstitucion='".$fUnidadGestion[0]."' ORDER BY id__528_tablaDinamica DESC";	

		$resConf=$con->obtenerFilas($consulta);
		while($fConfiguracion=mysql_fetch_row($resConf))
		{
			if(($fConfiguracion[2]!="")&&($fConfiguracion[2]!=-1))
			{
				$aplicaFolio=removerComillasLimite(resolverExpresionCalculoPHP($fConfiguracion[2],$obj,$cache));
				if($aplicaFolio==1)
					break;
				
			}
			else
			{
				break;
			}

		}
		
		if(!$fConfiguracion)
			return NULL;	
			
		$agregarSecuencia=false;
		if($folioActual==0)
		{
			if(($fConfiguracion[3]=="")||($fConfiguracion[3]=="-1"))
			{
				$query="select folioActual FROM 7004_seriesUnidadesGestion WHERE idUnidadGestion=".$idUnidadGestion.
							" AND anio=".$anio." and tipoDelito='".$tipoDelito."'";
				$folioActual=$con->obtenerValor($query);
				if($folioActual=="")
				{
					$folioActual=1;
					$agregarSecuencia=true;	
				}
				else
				{
					$folioActual++;
				}
			}
			else
			{
				$arrFolio=resolverExpresionCalculoPHP($fConfiguracion[3],$obj,$cache);
				$folioActual=$arrFolio["folioActual"];
				$tipoDelito=$arrFolio["tipoDelito"];
				$agregarSecuencia=$arrFolio["agregarSecuencia"];
			}
					
		}
		
		
		if($agregarSecuencia)
		{
			$query="INSERT INTO 7004_seriesUnidadesGestion(idUnidadGestion,anio,folioActual,tipoDelito) VALUES(".$idUnidadGestion.
					",".$anio.",".$folioActual.",'".$tipoDelito."')";
		}
		else
		{
			$query="update 7004_seriesUnidadesGestion set folioActual=".$folioActual." where idUnidadGestion=".$idUnidadGestion.
					" and anio=".$anio." and tipoDelito='".$tipoDelito."'";
		}	
		
		$tamanoFolio=$fConfiguracion[1];
		
		if($con->ejecutarConsulta($query))
		{
			
			$arrParametrosFolio["folio"]=str_pad($folioActual,$tamanoFolio,"0",STR_PAD_LEFT);			
			
			$carpetaAdministrativa= $fConfiguracion[0];
			foreach($arrParametrosFolio as $param=>$valor)
			{
				$carpetaAdministrativa=str_replace('{'.$param.'}',$valor,$carpetaAdministrativa);
			}
			
			$arrResultado[0]=$carpetaAdministrativa;
			$arrResultado[1]=$folioActual;

			return $arrResultado;
			
			
		}
		
		
	}	
	
	
	///
	function generarCarpetaLeyMujeresLibreViolencia($idUnidadGestion,$anio,$tipoCarpeta,$folioActual)
	{
		global $con;	
		$arrResultado=array();
		$agregarSecuencia=false;
		$tipoDelito="LMLV";
		if($folioActual==0)
		{
			$query="select folioActual FROM 7004_seriesUnidadesGestion WHERE idUnidadGestion=".$idUnidadGestion.
						" AND anio=".$anio." and tipoDelito='".$tipoDelito."'";
			$folioActual=$con->obtenerValor($query);
			
			if($folioActual=="")
			{
				$folioActual=1;
				$agregarSecuencia=true;	
			}
			else
			{
				$folioActual++;
			}
					
		}
		
		//-------
		$folioCorreccion=$folioActual-10;
		if($folioCorreccion<1)
			$folioCorreccion=1;
		$query="SELECT claveFolioCarpetas,claveUnidad FROM _17_tablaDinamica WHERE id__17_tablaDinamica=".$idUnidadGestion;
		$fRegistroUnidad=$con->obtenerPrimeraFila($query);
		$cveUnidadGestion=$fRegistroUnidad[0];
		$cvAdscripcion=$fRegistroUnidad[1];
		$formatoCarpeta= str_pad($cveUnidadGestion,3,"0",STR_PAD_LEFT)."/[folioCarpeta]/".$anio."-LLV";
		
		
		
		$carpetaAdministrativa=str_replace("[folioCarpeta]",str_pad($folioCorreccion,4,"0",STR_PAD_LEFT),$formatoCarpeta);
		while(existeCarpetaAdministrativa($carpetaAdministrativa,""))
		{
			$folioCorreccion++;	
			$carpetaAdministrativa=str_replace("[folioCarpeta]",str_pad($folioCorreccion,4,"0",STR_PAD_LEFT),$formatoCarpeta);
		}
		
		
		if($folioCorreccion<$folioActual)
		{
			registrarRecuperacionFolio($idUnidadGestion,$folioCorreccion,'');
		}
		$folioActual=$folioCorreccion;
		///
		if($agregarSecuencia)
		{
			$query="INSERT INTO 7004_seriesUnidadesGestion(idUnidadGestion,anio,folioActual,tipoDelito) VALUES(".$idUnidadGestion.
					",".$anio.",".$folioActual.",'".$tipoDelito."')";
		}
		else
		{
			$query="update 7004_seriesUnidadesGestion set folioActual=".$folioActual." where idUnidadGestion=".$idUnidadGestion.
					" and anio=".$anio." and tipoDelito='".$tipoDelito."'";
		}	
		
		
		if($con->ejecutarConsulta($query))
		{
			$arrResultado[0]=$carpetaAdministrativa;
			$arrResultado[1]=$folioActual;
			return $arrResultado;
		}
		
		
	}
	

?>