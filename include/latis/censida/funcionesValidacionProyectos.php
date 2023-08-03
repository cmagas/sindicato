<?php

	function validarNuevoRegistroProyectoInicio($idConfiguracion,$idCategoria,$idSubcategoria,$idTema)
	{
		global $con;
				return "\"[]\"";
		$consulta="SELECT fechaInicioRegistro,horaInicioRegistro,fechaFinRegistro,horaFinRegistro,limiteProyectosOSC,
					maximoProyectosOSC,criterioLimiteProyectosOSC,limiteRegistrosProyectos,criterioRegistroProyectos,
					maximoProyectos FROM _412_tablaDinamica WHERE id__412_tablaDinamica=".$idConfiguracion;
		$fConvocatoria=$con->obtenerPrimeraFila($consulta);
		
		$arrErrores=array();
		if(!cumpleActualizacionDatosOrganizacion())
		{
			$obj= array();
			$obj[0]="Proyecto General";
			$obj[1]="Primero debe actualizar los datos de su organización";
			array_push($arrErrores,$obj);
		}
		
		if(!cumpleNoAdeudosOSC($idConfiguracion,$idCategoria,$idSubcategoria,$idTema))
		{
			$obj= array();
			$obj[0]="Proyecto General";
			$obj[1]="Su organización cuenta con adeudo en apoyos asignados en ciclos anteriores, debe contactarse con el responsable en CENSIDA";
			array_push($arrErrores,$obj);
		}
		
		/*if(!cumpleCategoriasCompatibles($idConfiguracion,$idCategoria,$idSubcategoria,$idTema))
		{
			$obj= array();
			$obj[0]="Proyecto General";
			if($idCategoria==1)
			{
				$consulta="SELECT group_concat(codigo) FROM _410_tablaDinamica WHERE codigoInstitucion='".$_SESSION["codigoInstitucion"]."' and idCategoria in (2,3)";
				$lProyectos=$con->obtenerValor($consulta);
				$obj[1]="Ya ha registrado almenos un proyecto en la categorias 2 o 3 (Folio del proyecto: ".$lProyectos.")";
			}
			else
			{
				$consulta="SELECT group_concat(codigo) FROM _410_tablaDinamica WHERE codigoInstitucion='".$_SESSION["codigoInstitucion"]."' and idCategoria in (1)";
				$lProyectos=$con->obtenerValor($consulta);
				$obj[1]="Ya ha registrado almenos un proyecto en la categorias 1 (Folio del proyecto: ".$lProyectos.")";
			}
			array_push($arrErrores,$obj);
		}*/
		
		if(!cumpleConvocatoriaAbierta($idConfiguracion,$idCategoria,$idSubcategoria,$idTema))
		{
			$obj= array();
			$obj[0]="Proyecto General";
			$lblPeriodo="Del ".date("d/m/Y",strtotime($fConvocatoria[0]))." a las ".date("H:i",strtotime($fConvocatoria[1]))." hrs. al ".date("d/m/Y",strtotime($fConvocatoria[2]))." a las ".date("H:i",strtotime($fConvocatoria[3]))." hrs.";
			$obj[1]="El periodo de registro de proyectos (".$lblPeriodo.") ha finalizado";
			array_push($arrErrores,$obj);
		}
		
		if(!cumpleNumeroProyectosPermitidosProceso($idConfiguracion,$idCategoria,$idSubcategoria,$idTema))
		{
			$lblProyectos=$fConvocatoria[9]." proyectos";
			if($fConvocatoria[8]==1)
				$lblProyectos.=" registrados";
			else
				$lblProyectos.=" sometidos";
			$obj= array();
			$obj[0]="Proyecto General";
			$obj[1]="Se ha llegado al número de proyectos permitidos por la convocatoria (".$lblProyectos.")";
			array_push($arrErrores,$obj);
		}
		
		if(!cumpleNumeroProyectosPermitidosOSC($idConfiguracion,$idCategoria,$idSubcategoria,$idTema))
		{
			$lblProyectos=$fConvocatoria[5]." proyectos";
			if($fConvocatoria[6]==1)
				$lblProyectos.=" registrados";
			else
				$lblProyectos.=" sometidos";
			$obj= array();
			$obj[0]="Proyecto General";
			$obj[1]="Su organización ha cubierto el de número de proyectos permitidos por la convocatoria (".$lblProyectos.")";
			array_push($arrErrores,$obj);
		}
		
		if($idSubcategoria==17)
		{
			if(!cumpleNoFinanciadoAnteriormente())
			{
				$obj= array();
				$obj[0]="Proyecto General";
				$obj[1]="Su organización ha recibido ha recibido apoyo por CENSIDA en ciclos anteriores, si ésto no es correcto deberá reportalo a CENSIDA para su corrección";
				array_push($arrErrores,$obj);
			}
		}
		$cadError="";
		
		if(sizeof($arrErrores)>0)
		{
			foreach($arrErrores as $e)
			{
				$o="['".$e[0]."','".$e[1]."']";
				if($cadError=="")
					$cadError=$o;
				else
					$cadError.=",".$o;
			}
		}
		
		return "\"[".$cadError."]\"";
	}
	
	function validarNuevoRegistroProyectoVistaDTD($idConfiguracion,$idCategoria,$idSubcategoria,$idTema)
	{
		global $con;
		
		$consulta="SELECT fechaInicioRegistro,horaInicioRegistro,fechaFinRegistro,horaFinRegistro,limiteProyectosOSC,
					maximoProyectosOSC,criterioLimiteProyectosOSC,limiteRegistrosProyectos,criterioRegistroProyectos,
					maximoProyectos FROM _412_tablaDinamica WHERE id__412_tablaDinamica=".$idConfiguracion;
		$fConvocatoria=$con->obtenerPrimeraFila($consulta);
		
		$arrErrores=array();
		if(!cumpleActualizacionDatosOrganizacion())
		{
			$obj= array();
			$obj[0]="Proyecto General";
			$obj[1]="Primero debe actualizar los datos de su organización";
			array_push($arrErrores,$obj);
		}
		
		if(!cumpleNoAdeudosOSC($idConfiguracion,$idCategoria,$idSubcategoria,$idTema))
		{
			$obj= array();
			$obj[0]="Proyecto General";
			$obj[1]="Su organización cuenta con adeudo en apoyos asignados en ciclos anteriores, debe contactarse con el responsable en CENSIDA";
			array_push($arrErrores,$obj);
		}
		
		/*if(!cumpleCategoriasCompatibles($idConfiguracion,$idCategoria,$idSubcategoria,$idTema))
		{
			$obj= array();
			$obj[0]="Proyecto General";
			if($idCategoria==1)
			{
				$consulta="SELECT group_concat(codigo) FROM _410_tablaDinamica WHERE codigoInstitucion='".$_SESSION["codigoInstitucion"]."' and idCategoria in (2,3)";
				$lProyectos=$con->obtenerValor($consulta);
				$obj[1]="Ya ha registrado almenos un proyecto en la categorias 2 o 3 (Folio del proyecto: ".$lProyectos.")";
			}
			else
			{
				$consulta="SELECT group_concat(codigo) FROM _410_tablaDinamica WHERE codigoInstitucion='".$_SESSION["codigoInstitucion"]."' and idCategoria in (1)";
				$lProyectos=$con->obtenerValor($consulta);
				$obj[1]="Ya ha registrado almenos un proyecto en la categorias 1 (Folio del proyecto: ".$lProyectos.")";
			}
			array_push($arrErrores,$obj);
		}*/
		
		if(!cumpleConvocatoriaAbierta($idConfiguracion,$idCategoria,$idSubcategoria,$idTema))
		{
			$obj= array();
			$obj[0]="Proyecto General";
			$lblPeriodo="Del ".date("d/m/Y",strtotime($fConvocatoria[0]))." a las ".date("H:i",strtotime($fConvocatoria[1]))." hrs. al ".date("d/m/Y",strtotime($fConvocatoria[2]))." a las ".date("H:i",strtotime($fConvocatoria[3]))." hrs.";
			$obj[1]="El periodo de registro de proyectos (".$lblPeriodo.") ha finalizado";
			array_push($arrErrores,$obj);
		}
		
		if(!cumpleNumeroProyectosPermitidosProceso($idConfiguracion,$idCategoria,$idSubcategoria,$idTema))
		{
			$lblProyectos=$fConvocatoria[9]." proyectos";
			if($fConvocatoria[8]==1)
				$lblProyectos.=" registrados";
			else
				$lblProyectos.=" sometidos";
			$obj= array();
			$obj[0]="Proyecto General";
			$obj[1]="Se ha llegado al número de proyectos permitidos por la convocatoria (".$lblProyectos.")";
			array_push($arrErrores,$obj);
		}
		
		if(!cumpleNumeroProyectosPermitidosOSC($idConfiguracion,$idCategoria,$idSubcategoria,$idTema))
		{
			$lblProyectos=$fConvocatoria[5]." proyectos";
			if($fConvocatoria[6]==1)
				$lblProyectos.=" registrados";
			else
				$lblProyectos.=" sometidos";
			$obj= array();
			$obj[0]="Proyecto General";
			$obj[1]="Su organización ha cubierto el de número de proyectos permitidos por la convocatoria (".$lblProyectos.")";
			array_push($arrErrores,$obj);
		}
		
		if($idSubcategoria==17)
		{
			if(!cumpleNoFinanciadoAnteriormente())
			{
				$obj= array();
				$obj[0]="Proyecto General";
				$obj[1]="Su organización ha recibido ha recibido apoyo por CENSIDA en ciclos anteriores, si ésto no es correcto deberá reportalo a CENSIDA para su corrección";
				array_push($arrErrores,$obj);
			}
		}
		$cadError="";
		
		if(sizeof($arrErrores)>0)
		{
			foreach($arrErrores as $e)
			{
				$o="['".$e[0]."','".$e[1]."']";
				if($cadError=="")
					$cadError=$o;
				else
					$cadError.=",".$o;
			}
		}
		
		return "[".$cadError."]";
	}
		
	function validarSometimientoProyecto($idFormulario,$idRegistro)
	{
		global $con;
		$consulta="SELECT idReferencia FROM _".$idFormulario."_tablaDinamica WHERE  id__".$idFormulario."_tablaDinamica=".$idRegistro;
		$idConfiguracion=$con->obtenerValor($consulta);
		$consulta="SELECT fechaInicioRegistro,horaInicioRegistro,fechaFinRegistro,horaFinRegistro,limiteProyectosOSC,
					maximoProyectosOSC,criterioLimiteProyectosOSC,limiteRegistrosProyectos,criterioRegistroProyectos,
					maximoProyectos FROM _412_tablaDinamica WHERE id__412_tablaDinamica=".$idConfiguracion;
		$fConvocatoria=$con->obtenerPrimeraFila($consulta);
		$consulta="SELECT campoCategoria,campoSubcategoria,campoTema FROM _428_tablaDinamica WHERE idReferencia=".$idConfiguracion;
		$fCamposConf=$con->obtenerPrimeraFila($consulta);
		
		$consulta="SELECT ".$fCamposConf[0].",".$fCamposConf[1].",".$fCamposConf[2]." FROM _".$idFormulario."_tablaDinamica WHERE  id__".$idFormulario."_tablaDinamica=".$idRegistro;
		$fRegistro=$con->obtenerPrimeraFila($consulta);
		
		$arrErrores=array();
		$idCategoria=$fRegistro[0];
		$idSubcategoria=$fRegistro[1];
		$idTema=$fRegistro[2];		
		/*if(!cumpleCategoriasCompatibles($idConfiguracion,$idCategoria,$idSubcategoria,$idTema))
		{
			$obj= array();
			$obj[0]="Proyecto General";
			if($idCategoria==1)
			{
				$consulta="SELECT group_concat(codigo) FROM _410_tablaDinamica WHERE codigoInstitucion='".$_SESSION["codigoInstitucion"]."' and idCategoria in (2,3)";
				$lProyectos=$con->obtenerValor($consulta);
				$obj[1]="Ya ha registrado almenos un proyecto en la categorias 2 o 3 (Folio del proyecto: ".$lProyectos.")";
			}
			else
			{
				$consulta="SELECT group_concat(codigo) FROM _410_tablaDinamica WHERE codigoInstitucion='".$_SESSION["codigoInstitucion"]."' and idCategoria in (1)";
				$lProyectos=$con->obtenerValor($consulta);
				$obj[1]="Ya ha registrado almenos un proyecto en la categorias 1 (Folio del proyecto: ".$lProyectos.")";
			}
			array_push($arrErrores,$obj);
		}*/
		
		if(!cumpleConvocatoriaAbierta($idConfiguracion,$idCategoria,$idSubcategoria,$idTema))
		{
			$obj= array();
			$obj[0]="Proyecto General";
			$lblPeriodo="Del ".date("d/m/Y",strtotime($fConvocatoria[0]))." a las ".date("H:i",strtotime($fConvocatoria[1]))." hrs. al ".date("d/m/Y",strtotime($fConvocatoria[2]))." a las ".date("H:i",strtotime($fConvocatoria[3]))." hrs.";
			$obj[1]="El periodo de registro de proyectos (".$lblPeriodo.") ha finalizado";
			array_push($arrErrores,$obj);
		}
		
		if(!cumpleNumeroProyectosSometidosProceso($idConfiguracion,$idCategoria,$idSubcategoria,$idTema))
		{
			$lblProyectos=$fConvocatoria[9]." proyectos";
			
			$obj= array();
			$obj[0]="Proyecto General";
			$obj[1]="Se ha llegado al número de proyectos sometidos permitidos por la convocatoria (".$lblProyectos.")";
			array_push($arrErrores,$obj);
		}
		
		if(!cumpleNumeroProyectosSometidosOSC($idConfiguracion,$idCategoria,$idSubcategoria,$idTema))
		{
			$lblProyectos=$fConvocatoria[5]." proyectos";
			
			$obj= array();
			$obj[0]="Proyecto General";
			$obj[1]="Su organización ha cubierto el de número de proyectos sometidos permitidos por la convocatoria (".$lblProyectos.")";
			array_push($arrErrores,$obj);
		}
		
		if(!cumpleObjetivosVSMetas($idFormulario,$idRegistro))
		{
			$obj= array();
			$obj[0]="Definición de Metas";
			$obj[1]="";
			$consulta="SELECT objetivoEspecifico1,objetivoEspecifico2,objetivoEspecifico3 FROM _421_tablaDinamica WHERE idReferencia=".$idRegistro;
			$fObjetivos=$con->obtenerPrimeraFila($consulta);
			if($fObjetivos)
			{
				if($fObjetivos[0]!="")
				{
					$consulta="SELECT COUNT(*) FROM 108_metasProyectos WHERE idFormulario=".$idFormulario." AND idReferencia=".$idRegistro." AND noObjetivo=1";
					$nReg=$con->obtenerValor($consulta);
					if($nReg==0)
					{
						$obj= array();
						$obj[0]="Definición de Metas";
						$obj[1]="El objetivo específico 1 no tiene asociado metas";
						array_push($arrErrores,$obj);
					}
				}
				if($fObjetivos[1]!="")
				{
					$consulta="SELECT COUNT(*) FROM 108_metasProyectos WHERE idFormulario=".$idFormulario." AND idReferencia=".$idRegistro." AND noObjetivo=2";
					$nReg=$con->obtenerValor($consulta);
					if($nReg==0)
					{
						$obj= array();
						$obj[0]="Definición de Metas";
						$obj[1]="El objetivo específico 2 no tiene asociado metas";
						array_push($arrErrores,$obj);
					}
				}
				if($fObjetivos[2]!="")
				{
					$consulta="SELECT COUNT(*) FROM 108_metasProyectos WHERE idFormulario=".$idFormulario." AND idReferencia=".$idRegistro." AND noObjetivo=3";
					$nReg=$con->obtenerValor($consulta);
					if($nReg==0)
					{
						$obj= array();
						$obj[0]="Definición de Metas";
						$obj[1]="El objetivo específico 3 no tiene asociado metas";
						array_push($arrErrores,$obj);
					}
				}
			}
			
			
		}


		if(!cumpleMetasVSActividades($idFormulario,$idRegistro))
		{
			
			$consulta="SELECT idMeta,meta FROM 108_metasProyectos m WHERE idFormulario=".$idFormulario." AND idReferencia=".$idRegistro."
					AND idMeta NOT IN (SELECT descripcion FROM 965_actividadesUsuario WHERE idFormulario=".$idFormulario." AND idReferencia=".$idRegistro.") order by meta";
			$res=$con->obtenerFilas($consulta);
			while($fMeta=mysql_fetch_row($res))
			{
				$obj= array();
				$consulta="";
				$obj[0]="Cronograma de actividades";
				$obj[1]="La meta <i>".cv($fMeta[1])."</i> no se encuentra asociado con alguna actividad para su cumplimiento";
				array_push($arrErrores,$obj);
			}
			
			
			
		}	
		
		if(!cumpleIndicadoresSActividades($idFormulario,$idRegistro))
		{
			
			$consulta="SELECT idIndicador FROM 109_indicadoresProyectos  WHERE idFormulario=".$idFormulario." AND idReferencia=".$idRegistro."
					AND idIndicador NOT IN (SELECT distinct idIndicador FROM 965_actividadesUsuario a,968_actividadesIndicador ai WHERE a.idFormulario=".$idFormulario." AND a.idReferencia=".$idRegistro." and ai.idActividad=a.idActividadPrograma)";
			$res=$con->obtenerFilas($consulta);
			while($fIndicador=mysql_fetch_row($res))
			{
				$consulta="SELECT nombreIndicador FROM _375_tablaDinamica WHERE id__375_tablaDinamica=".$fIndicador[0];
				$lblIndicador=$con->obtenerValor($consulta);
				$obj= array();
				$consulta="";
				$obj[0]="Cronograma de actividades";
				$obj[1]="El indicador <i>".cv($lblIndicador)."</i> no se encuentra asociado con alguna actividad para su cumplimiento";
				array_push($arrErrores,$obj);
			}
		}		
		
		$cadError="";
		if(sizeof($arrErrores)>0)
		{
			foreach($arrErrores as $e)
			{
				$o="['".$e[0]."','".$e[1]."']";
				if($cadError=="")
					$cadError=$o;
				else
					$cadError.=",".$o;
			}
		}
		
		return "[".$cadError."]";
	}

	function cumpleNumeroProyectosSometidosOSC($idConfiguracion,$idCategoria,$idSubcategoria,$idTema)
	{
		global $con;
		$consulta="SELECT limiteProyectosOSC,maximoProyectosOSC,criterioLimiteProyectosOSC,procesoAsociado FROM _412_tablaDinamica WHERE id__412_tablaDinamica=".$idConfiguracion;
		$fConfiguracion=$con->obtenerPrimeraFila($consulta);
		if($fConfiguracion[0]==0)
			return true;
		$idFormulario=obtenerFormularioBase($fConfiguracion[3]);
		if($fConfiguracion[2]==1)//Registrados
		{
			return true;
		}
		else		//Sometidos
		{
			$consulta="SELECT COUNT(*) FROM _".$idFormulario."_tablaDinamica WHERE codigoInstitucion='".$_SESSION["codigoInstitucion"]."' and idEstado<>1";
		}
		$nReg=$con->obtenerValor($consulta);
		if($nReg<$fConfiguracion[1])
			return true;
		return false;
	}
	
	function cumpleNumeroProyectosSometidosProceso($idConfiguracion,$idCategoria,$idSubcategoria,$idTema)
	{
		global $con;
		$consulta="SELECT limiteRegistrosProyectos,maximoProyectos,criterioRegistroProyectos,procesoAsociado FROM _412_tablaDinamica WHERE id__412_tablaDinamica=".$idConfiguracion;
		$fConfiguracion=$con->obtenerPrimeraFila($consulta);
		if($fConfiguracion[0]==0)
			return true;
		$idFormulario=obtenerFormularioBase($fConfiguracion[3]);
		if($fConfiguracion[2]==1)//Registrados
		{
			return true;
		}
		else		//Sometidos
		{
			$consulta="SELECT COUNT(*) FROM _".$idFormulario."_tablaDinamica WHERE  idEstado<>1";
		}
		$nReg=$con->obtenerValor($consulta);
		if($nReg<$fConfiguracion[1])
			return true;
		return false;
	}

	function cumpleNumeroProyectosPermitidosOSC($idConfiguracion,$idCategoria,$idSubcategoria,$idTema)
	{
		global $con;
		$consulta="SELECT limiteProyectosOSC,maximoProyectosOSC,criterioLimiteProyectosOSC,procesoAsociado FROM _412_tablaDinamica WHERE id__412_tablaDinamica=".$idConfiguracion;
		$fConfiguracion=$con->obtenerPrimeraFila($consulta);
		if($fConfiguracion[0]==0)
			return true;
		$idFormulario=obtenerFormularioBase($fConfiguracion[3]);
		if($fConfiguracion[2]==1)//Registrados
		{
			$consulta="SELECT COUNT(*) FROM _".$idFormulario."_tablaDinamica WHERE codigoInstitucion='".$_SESSION["codigoInstitucion"]."'";
		}
		else		//Sometidos
		{
			$consulta="SELECT COUNT(*) FROM _".$idFormulario."_tablaDinamica WHERE codigoInstitucion='".$_SESSION["codigoInstitucion"]."' and idEstado<>1";
		}
		$nReg=$con->obtenerValor($consulta);
		if($nReg<$fConfiguracion[1])
			return true;
		return false;
	}
	
	function cumpleNumeroProyectosPermitidosProceso($idConfiguracion,$idCategoria,$idSubcategoria,$idTema)
	{
		global $con;
		$consulta="SELECT limiteRegistrosProyectos,maximoProyectos,criterioRegistroProyectos,procesoAsociado FROM _412_tablaDinamica WHERE id__412_tablaDinamica=".$idConfiguracion;
		$fConfiguracion=$con->obtenerPrimeraFila($consulta);
		if($fConfiguracion[0]==0)
			return true;
		$idFormulario=obtenerFormularioBase($fConfiguracion[3]);
		if($fConfiguracion[2]==1)//Registrados
		{
			$consulta="SELECT COUNT(*) FROM _".$idFormulario."_tablaDinamica";
		}
		else		//Sometidos
		{
			$consulta="SELECT COUNT(*) FROM _".$idFormulario."_tablaDinamica WHERE  idEstado<>1";
		}
		$nReg=$con->obtenerValor($consulta);
		if($nReg<$fConfiguracion[1])
			return true;
		return false;
	}
	
	function cumpleConvocatoriaAbierta($idConfiguracion,$idCategoria,$idSubcategoria,$idTema)
	{
		global $con;
		$consulta="SELECT fechaInicioRegistro,horaInicioRegistro,fechaFinRegistro,horaFinRegistro FROM _412_tablaDinamica WHERE id__412_tablaDinamica=".$idConfiguracion;
		$fConfiguracion=$con->obtenerPrimeraFila($consulta);
		$fechaActual=(obtenerFechaActual());
		$horaActual=(obtenerHoraActual());
		$fInicioConv=strtotime($fConfiguracion[0]." ".$fConfiguracion[1]);
		$fFinConv=strtotime($fConfiguracion[2]." ".$fConfiguracion[3]);
		$fActual=strtotime($fechaActual." ".$horaActual);
		if(($fActual>=$fInicioConv)&&($fActual<=$fFinConv))
		{
			return true;
		}
		
		return false;
	}
	
	function cumpleNoFinanciadoAnteriormente()
	{
		global $con;
		$consulta="SELECT count(*) FROM _370_tablaDinamica t WHERE codigoInstitucion='".$_SESSION["codigoInstitucion"]."' and marcaAutorizado=1";
		$nReg=$con->obtenerValor($consulta);
		if($nReg>0)
			return false;
		$consulta="SELECT count(*) FROM _293_tablaDinamica t WHERE codigoInstitucion='".$_SESSION["codigoInstitucion"]."' and idEstado IN (12,13,14,15)";
		$nReg=$con->obtenerValor($consulta);
		if($nReg>0)
			return false;
		return true;
	}
	
	function cumpleCategoriasCompatibles($idConfiguracion,$idCategoria,$idSubcategoria,$idTema)
	{
		global $con;
		$consulta="SELECT limiteRegistrosProyectos,maximoProyectos,criterioRegistroProyectos,procesoAsociado FROM _412_tablaDinamica WHERE id__412_tablaDinamica=".$idConfiguracion;
		$fConfiguracion=$con->obtenerPrimeraFila($consulta);
		$idFormulario=obtenerFormularioBase($fConfiguracion[3]);
		$consulta="";
		
		if($idCategoria==1)
		{
			$consulta="SELECT COUNT(*) FROM _".$idFormulario."_tablaDinamica WHERE codigoInstitucion='".$_SESSION["codigoInstitucion"]."' and idCategoria in (2,3)";
		}
		else
		{
			$consulta="SELECT COUNT(*) FROM _".$idFormulario."_tablaDinamica WHERE codigoInstitucion='".$_SESSION["codigoInstitucion"]."' and idCategoria in (1)";
		}
		$nReg=$con->obtenerValor($consulta);
		if($nReg>0)
			return false;
		return true;
	}
	
	function cumpleNoAdeudosOSC($idConfiguracion,$idCategoria,$idSubcategoria,$idTema)
	{
		global $con;
		return true;
		$adeudo2012=0;
		$consulta="SELECT id__370_tablaDinamica FROM _370_tablaDinamica WHERE codigoInstitucion='".$_SESSION["codigoInstitucion"]."' AND marcaAutorizado=1";
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$consulta="SELECT montoAutorizado,(SELECT SUM(montoComprobacion) FROM 102_conceptosComprobacion WHERE idConcepto=c.idGridVSCalculo)AS montoComprobado 
			FROM 100_calculosGrid c WHERE idFormulario=370 AND idReferencia=".$fila[0]." AND montoAutorizado>0 and eliminado=0";
			$resConceptos=$con->obtenerFilas($consulta);
			while($filaCon=mysql_fetch_row($resConceptos))
			{
				$diferencia=$filaCon[0]-$filaCon[1];
				if($diferencia<0)
					$diferencia=0;
				$adeudo2012+=$diferencia;
			}
		}
		$adeudo2011=0;
		$consulta="SELECT id__293_tablaDinamica FROM _293_tablaDinamica WHERE codigoInstitucion='".$_SESSION["codigoInstitucion"]."' and idEstado IN (12,13,14,15)";
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$consulta="SELECT montoAutorizado,(SELECT SUM(txtImporte) FROM _351_tablaDinamica WHERE cmbConcepto=c.idGridVSCalculo and idEstado=3)AS montoComprobado 
			FROM 100_calculosGrid c WHERE idFormulario=293 AND idReferencia=".$fila[0]." AND montoAutorizado>0 and eliminado=0";
			$resConceptos=$con->obtenerFilas($consulta);
			while($filaCon=mysql_fetch_row($resConceptos))
			{
				$diferencia=$filaCon[0]-$filaCon[1];
				/*if($diferencia<0)
					$diferencia=0;*/
				$adeudo2011+=$diferencia;
			}
			$consulta="SELECT SUM(importe) FROM _361_tablaDinamica WHERE idReferencia=".$fila[0];
			$sumTmp=$con->obtenerValor($consulta);
			if($sumTmp=="")
				$sumTmp=0;
			$adeudo2011-=$sumTmp;
				
		}
		
		if($adeudo2011<0)
			$adeudo2011=0;
		
		
		$adeudo=$adeudo2012+$adeudo2011;
		
		
		if($adeudo==0)
			return true;
		return false;
	}
	
	function cumpleActualizacionDatosOrganizacion()
	{
		global $con;
		$consulta="select idEstado from _367_tablaDinamica where codigoInstitucion='".$_SESSION["codigoInstitucion"]."'";
		$idEstado=$con->obtenerValor($consulta);
		if($idEstado==1)
			return false;
		return true;
	}
	
	function cumpleObjetivosVSMetas($idFormulario,$idRegistro)
	{
		global $con;
		$consulta="SELECT objetivoEspecifico1,objetivoEspecifico2,objetivoEspecifico3 FROM _421_tablaDinamica WHERE idReferencia=".$idRegistro;
		$fObjetivos=$con->obtenerPrimeraFila($consulta);
		if($fObjetivos)
		{
			if($fObjetivos[0]!="")
			{
				$consulta="SELECT COUNT(*) FROM 108_metasProyectos WHERE idFormulario=".$idFormulario." AND idReferencia=".$idRegistro." AND noObjetivo=1";
				$nReg=$con->obtenerValor($consulta);
				if($nReg==0)
					return false;
			}
			if($fObjetivos[1]!="")
			{
				$consulta="SELECT COUNT(*) FROM 108_metasProyectos WHERE idFormulario=".$idFormulario." AND idReferencia=".$idRegistro." AND noObjetivo=2";
				$nReg=$con->obtenerValor($consulta);
				if($nReg==0)
					return false;
			}
			if($fObjetivos[2]!="")
			{
				$consulta="SELECT COUNT(*) FROM 108_metasProyectos WHERE idFormulario=".$idFormulario." AND idReferencia=".$idRegistro." AND noObjetivo=3";
				$nReg=$con->obtenerValor($consulta);
				if($nReg==0)
					return false;
			}
		}
		
			return false;
		return true;
	}
	
	function cumpleMetasVSActividades($idFormulario,$idRegistro)
	{
		global $con;
		$consulta="SELECT idMeta FROM 108_metasProyectos m WHERE idFormulario=".$idFormulario." AND idReferencia=".$idRegistro."
					AND idMeta NOT IN (SELECT descripcion FROM 965_actividadesUsuario WHERE idFormulario=".$idFormulario." AND idReferencia=".$idRegistro.")";
		$con->obtenerFilas($consulta);
		if($con->filasAfectadas>0)
			return false;
		return true;
	}
	
	function cumpleIndicadoresSActividades($idFormulario,$idRegistro)
	{
		global $con;
		$consulta="SELECT idIndicador FROM 109_indicadoresProyectos  WHERE idFormulario=".$idFormulario." AND idReferencia=".$idRegistro."
					AND idIndicador NOT IN (SELECT distinct idIndicador FROM 965_actividadesUsuario a,968_actividadesIndicador ai WHERE a.idFormulario=".$idFormulario." AND a.idReferencia=".$idRegistro." and ai.idActividad=a.idActividadPrograma)";
		$con->obtenerFilas($consulta);
		if($con->filasAfectadas>0)
			return false;
		return true;
	}
	
	function validarEdicionPresupuestoProyectos($idFormulario,$idReferencia)
	{
		global $con;
		$arrValor=array();
		array_push($arrValor,57);
		array_push($arrValor,124);
		array_push($arrValor,221);
		array_push($arrValor,147);
		array_push($arrValor,310);
		array_push($arrValor,368);
		array_push($arrValor,330);
		array_push($arrValor,184);
		array_push($arrValor,149);
		array_push($arrValor,271);
		array_push($arrValor,327);
		array_push($arrValor,69);
		array_push($arrValor,214);
		array_push($arrValor,343);
		$consulta="SELECT idEstado FROM _410_tablaDinamica WHERE id__410_tablaDinamica=".$idReferencia;
		$idEstado=$con->obtenerValor($consulta);
		$valorFinal=0;
		if($idEstado==8)
		{
			$valorFinal=1;

			switch($idFormulario)
			{
				
				case 421://271,327,69
					if(existeValor($arrValor,$idReferencia))
					
						$valorFinal=1;
					else
						$valorFinal=0;
				break;
				case 422: //marco conceptual
					if(existeValor($arrValor,$idReferencia))
						$valorFinal=1;
					else
						$valorFinal=0;
				break;
				case 426://Docuentos anexos
					/*if($idReferencia==184)
						$valorFinal=1;
					else*/
						$valorFinal=0;
				break;
				case 430://Metas
					
					if(existeValor($arrValor,$idReferencia))
						$valorFinal=1;
					else
						$valorFinal=0;
				break;
				case 432://Indicadores
					
					if(existeValor($arrValor,$idReferencia))
						$valorFinal=1;
					else
						$valorFinal=0;
				break;
				case 442://Bitácora de cambios
					
					if(existeValor($arrValor,$idReferencia))
						$valorFinal=1;
					else
						$valorFinal=0;
				break;
				case 433://Poblacion blanco
					
					/*if(($idReferencia==343)||($idReferencia==108)||($idReferencia==95))
						$valorFinal=1;
					else*/
						$valorFinal=0;
				break;
			}
		}
		/*if($idEstado==15)
		{
			switch($idFormulario)
			{
				case 426:
			
				  if($idReferencia==27)s
					  $valorFinal=1;
				  else
					  $valorFinal=0;
				break;
			}
		}*/

		return $valorFinal;
	}
	
	
	function validarSometimientoRegistroReunionNacionalOSC($idFormulario,$idRegistro)
	{
		global $con;
		$arrErrores=array();
		$consulta="select entidadFederativa from _437_tablaDinamica WHERE id__437_tablaDinamica=".$idRegistro;
		$entidad=$con->obtenerValor($consulta);
		$numMaximo=0;
		if(($entidad=='0009000000')||($entidad=='0015000000'))
			$numMaximo=50;
		else
			$numMaximo=3;
			
		$consulta="select count(*) from _437_tablaDinamica WHERE entidadFederativa='".$entidad."' and idEstado=2 and id__437_tablaDinamica<>".$idRegistro;	
		$numRegistrados=$con->obtenerValor($consulta);
		if($numRegistrados>=$numMaximo)	
		{
			$obj= array();
			$consulta="SELECT estado FROM 820_estados WHERE cveEstado='".$entidad."'";
			$nEstado=$con->obtenerValor($consulta);
			$obj[0]="Formato de registro";
			$obj[1]="Se ha cubierto el número máximo permitido de registros(<b>".$numMaximo."</b>) para su Entidad Federativa (<b>".$nEstado."</b>)";
			array_push($arrErrores,$obj);	
		}
		$cadError="";
		if(sizeof($arrErrores)>0)
		{
			foreach($arrErrores as $e)
			{
				$o="['".$e[0]."','".$e[1]."']";
				if($cadError=="")
					$cadError=$o;
				else
					$cadError.=",".$o;
			}
		}
		
		return "[".$cadError."]";	
	}
	
	function ocultarBitacoraCambios($idProceso,$idFormulario,$idRegistro)
	{
		global $con;
		$idFormulario=410;

		$consulta="SELECT idEstado FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
		$idEstado=$con->obtenerValor($consulta);
		
		if(($idEstado!=8)&&($idEstado!=10))
			return true;
		return false;
	}
	
	
	function cumpleLimiteProyectosPermitidosConvocatoria2014()
	{
		global $con;
		$res=array();	
		$consulta="SELECT COUNT(*) FROM _448_tablaDinamica";
		$nReg=$con->obtenerValor($consulta);
		
		if($nReg>=400)
		{
			$res["situacion"]="0";
			$res["comentarios"]="Se ha alcanzado el n&uacute;mero de proyectos permitidos por la convocatoria(<b>400</b> proyectos)";
		}
		else
		{
			$res["situacion"]="1";
			$res["comentarios"]="";
		}
		
		return $res;
	}
	
	function cumpleConvocatoria2014Abierta()
	{
		global $con;
		$res=array();	
		
		
		if(strtotime(date("Y-m-d H:i:s"))>strtotime("2014-03-10 10:00:00"))
		{
			$res["situacion"]="0";
			$res["comentarios"]="El periodo de registro de proyectos de la convocatoria ha finalizado";
		}
		else
		{
			$res["situacion"]="1";
			$res["comentarios"]="";
		}
		
		return $res;
	}
	
	
	function cumpleLimiteProyectosPermitidos2014($organizacion)
	{
		global $con;
		$res=array();	
		$consulta="SELECT COUNT(*) FROM _448_tablaDinamica WHERE codigoInstitucion='".$organizacion."'";
		$nReg=$con->obtenerValor($consulta);
		$consulta="SELECT maximoProyectosOSC FROM _412_tablaDinamica WHERE id__412_tablaDinamica=2";
		$nMaxProyectos=$con->obtenerValor($consulta);
		if($nReg>=$nMaxProyectos)
		{
			$res["situacion"]="0";
			$res["comentarios"]="Ha completado el n&uacute;mero de proyectos permitidos (<b>".$nMaxProyectos."</b> proyectos)";
		}
		else
		{
			$res["situacion"]="1";
			$res["comentarios"]="Ha registrado <b>".$nReg."</b> proyectos de <b>".$nMaxProyectos."</b> permitidos";
		}
		
		return $res;
	}
	
	function cumpleNoAdeudos2014($organizacion)
	{
		global $con;
		$porcentaje=2;	
		$montoTotal=0;
		$adeudo2013=0;
		
		$comentarios="";
		
		$consulta="SELECT id__410_tablaDinamica FROM _410_tablaDinamica WHERE codigoInstitucion='".$organizacion."' AND marcaAutorizado=1";
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$consulta="SELECT montoAutorizado,(SELECT SUM(montoComprobacion) FROM 102_conceptosComprobacion WHERE idConcepto=c.idGridVSCalculo and situacion=1)AS montoComprobado 
			FROM 100_calculosGrid c WHERE idFormulario=410 AND idReferencia=".$fila[0]." AND montoAutorizado>0 and eliminado=0";
			$resConceptos=$con->obtenerFilas($consulta);
			while($filaCon=mysql_fetch_row($resConceptos))
			{
				$diferencia=$filaCon[0]-$filaCon[1];
				if($diferencia<0)
					$diferencia=0;
				$adeudo2013+=$diferencia;
				$montoTotal+=$filaCon[0];
			}
		}
		
		$montoRegistro=$montoTotal*($porcentaje/100);
		if($adeudo2013>$montoRegistro)
		{
			$comentarios.="Presenta un adeudo de $ ".number_format($adeudo2013,2)." que excede el ".number_format($porcentaje,2)."% de adeudo permitido sobre el monto total de los proyectos asignados a su OSC/IA/CI en el ciclo 2013 ($ ".number_format($montoTotal,2).")<br><br>";	
		}
		
		$montoTotal=0;
		$adeudo2012=0;
		$consulta="SELECT id__370_tablaDinamica FROM _370_tablaDinamica WHERE codigoInstitucion='".$organizacion."' AND marcaAutorizado=1";
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$consulta="SELECT montoAutorizado,(SELECT SUM(montoComprobacion) FROM 102_conceptosComprobacion WHERE idConcepto=c.idGridVSCalculo and situacion=1)AS montoComprobado 
			FROM 100_calculosGrid c WHERE idFormulario=370 AND idReferencia=".$fila[0]." AND montoAutorizado>0 and eliminado=0";
			$resConceptos=$con->obtenerFilas($consulta);
			while($filaCon=mysql_fetch_row($resConceptos))
			{
				$diferencia=$filaCon[0]-$filaCon[1];
				if($diferencia<0)
					$diferencia=0;
				$adeudo2012+=$diferencia;
				$montoTotal+=$filaCon[0];
			}
		}
		
		
		$montoRegistro=$montoTotal*($porcentaje/100);
		if($adeudo2012>$montoRegistro)
		{
			$comentarios.="Presenta un adeudo de $ ".number_format($adeudo2012,2)." que excede el ".number_format($porcentaje,2)."% de adeudo permitido sobre el monto total de los proyectos asignados a su OSC/IA/CI en el ciclo 2012 ($ ".number_format($montoTotal,2).")<br><br>";	
		}
		
		$montoTotal=0;
		$adeudo2011=0;
		$consulta="SELECT id__293_tablaDinamica FROM _293_tablaDinamica WHERE codigoInstitucion='".$organizacion."' and idEstado IN (12,13,14,15)";
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$montoTotalProy=0;
			$consulta="SELECT montoAutorizado FROM 100_calculosGrid c WHERE idFormulario=293 AND idReferencia=".$fila[0]." AND montoAutorizado>0 and eliminado=0";
			$resConceptos=$con->obtenerFilas($consulta);
			while($filaCon=mysql_fetch_row($resConceptos))
			{
				$montoTotalProy+=$filaCon[0];
				$montoTotal+=$filaCon[0];
			}
			$consulta="SELECT SUM(txtImporte) FROM _351_tablaDinamica WHERE idReferencia=".$fila[0]." AND idEstado=3"; 
			$montoComprobado=$con->obtenerValor($consulta);
			
			$diferencia=$montoTotalProy-$montoComprobado;
			if($diferencia<0)
				$diferencia=0;
				
			$adeudo2011+=$diferencia;
			$consulta="SELECT SUM(importe) FROM _361_tablaDinamica WHERE idReferencia=".$fila[0];
			$sumTmp=$con->obtenerValor($consulta);
			if($sumTmp=="")
				$sumTmp=0;
			$adeudo2011-=$sumTmp;
				
		}
		if($adeudo2011<0)
			$adeudo2011=0;
		
		
		
		$montoRegistro=$montoTotal*($porcentaje/100);
		if($adeudo2011>$montoRegistro)
		{
			$comentarios.="Presenta un adeudo de $ ".number_format($adeudo2011,2)." que excede el ".number_format($porcentaje,2)."% de adeudo permitido sobre el monto total de los proyectos asignados a su OSC/IA/CI en el ciclo 2011 ($ ".number_format($montoTotal,2).")<br><br>";	
		}
		
		$consulta="SELECT COUNT(*) FROM 1027_organizacionDeudoras2014 WHERE osc='".$organizacion."'";
		$nRes=$con->obtenerValor($consulta);
		if($nRes>0)
			$comentarios="";
		$res=array();
		if($comentarios!="")
		{
			$res["situacion"]="0";
			$res["comentarios"]="Su organizaci&oacute;n presenta adeudos en ciclos anteriores: <br><br>".$comentarios;
			
		}
		else
		{
			$res["situacion"]="1";
			$res["comentarios"]="";	
		}
		return $res;
		
	}
	
	function cumpleActualizacionPadron2014($organizacion)
	{
		global $con;	
		$consulta="SELECT idEstado FROM _367_tablaDinamica WHERE codigoInstitucion='".$organizacion."'";
		$idEstado=$con->obtenerValor($consulta);
		$res=array();
		if(($idEstado==1)||($idEstado==""))
		{
			$res["situacion"]="0";
			$res["comentarios"]="Los datos de actualizaci&oacute;n de su organizaci&oacute;n a&uacute;n no han sido enviados a validaci&oacute;n";
		}
		else
		{
			$res["situacion"]="1";
		}
		return $res;
		
	}
	
	function validarProyecto2014($idFormulario,$idRegistro)
	{
		global $con;	
		
		$consulta="SELECT idCategoria FROM _448_tablaDinamica WHERE id__448_tablaDinamica=".$idRegistro;
		$idReferencia=$con->obtenerValor($consulta);
		
		if(($idReferencia==4)||($idReferencia==5)||($idReferencia==8))
		{
			
			$consulta="SELECT indicador FROM _414_indicadoresCategoria gi WHERE gi.idReferencia=".$idReferencia;
		
			$listIndicadoresRef=$con->obtenerListaValores($consulta);
			
			
			$consulta="SELECT COUNT(*) FROM 109_indicadoresProyectos WHERE idFormulario=".$idFormulario." AND idReferencia=".$idRegistro." AND (numerador>0 OR denominador>0)
					and idIndicador in (".$listIndicadoresRef.")";	
			$nReg=$con->obtenerValor($consulta);
			if($nReg<2)
			{
				return "<br>Al menos debe considerar dos indicadores clasificados como \"De la categor&iacute;a\" dentro de su proyecto (Los indicadores con valor 0 no son considerados dentro del proyecto)";	
			}
			
		}
		
		$cadena="";
		$consulta="SELECT idIndicador FROM 109_indicadoresProyectos WHERE  idFormulario=".$idFormulario." AND idReferencia=".$idRegistro;
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$consulta="SELECT COUNT(*) FROM 968_actividadesIndicador WHERE idIndicador=".$fila[0]	;
			$nReg=$con->obtenerValor($consulta);
			if($nReg==0)
			{
				$consulta="SELECT nombreIndicador FROM 1026_catalogoIndicadores2014 WHERE idIndicador=".$fila[0];
				$nIndicador=$con->obtenerValor($consulta);
				$cadena.="<br>".$nIndicador;
					
			}
		}
			
		if($cadena!="")
		{
			return "<br>Se ha detectado que los siguientes indicadores no cuentan con algunas actividad asociada que permitan garantizar su cumplimiento: <br>".$cadena;	
		}
		
		$consulta="	SELECT * FROM (
					SELECT idActividadPrograma,(SELECT COUNT(*) FROM 968_planeacionActividadesMeses WHERE idActividad=a.idActividadPrograma) AS nReg
					FROM 965_actividadesUsuario a WHERE idFormulario=448 AND idReferencia=".$idRegistro.") AS tmp WHERE nReg=0";
		$con->obtenerFilas($consulta);
		if($con->filasAfectadas>0)
		{
			return "<br>Se ha detectado que en algunas actividades no se ha marcado los meses en los cuales ser&aacute;n ejecutados";	
		}
		
		
		$comp="<br><span style='color:#F00'><b>*</b></span> ";
		$resultado="";
		
		
		
		$consulta="SELECT id__367_tablaDinamica FROM _367_tablaDinamica WHERE codigoInstitucion='".$_SESSION["codigoInstitucion"]."'";
		
		$idOrganizacion=$con->obtenerValor($consulta);
		
		$consulta="SELECT tipoOrganizacion,cuentaCluni,CLUNI FROM _367_tablaDinamica WHERE id__367_tablaDinamica=".$idOrganizacion;
		$fOrganizacion=$con->obtenerPrimeraFila($consulta);
		if($fOrganizacion[0]==1)
		{
			if(trim($fOrganizacion[2])=="")
			{
				$resultado.=$comp."Proceso Actualización de padrón, Sección: Datos de la Organización.- No ha ingresado el número de CLUNI de su organización";
			}
			$consulta="SELECT COUNT(*) FROM _407_documentosRequeridosOSC gd,_407_tablaDinamica d WHERE gd.idReferencia=d.id__407_tablaDinamica AND d.idReferencia=".$idOrganizacion." AND gd.tituloDocumento=6";
			$nReg=$con->obtenerValor($consulta);
			if($nReg==0)
			{
				$resultado.=$comp."Documentos.- Debe ingresar el CLUNI (Escaneado) de su organización";
			}
			
			
			$consulta="SELECT COUNT(*) FROM _407_documentosRequeridosOSC gd,_407_tablaDinamica d WHERE gd.idReferencia=d.id__407_tablaDinamica AND d.idReferencia=".$idOrganizacion." AND gd.tituloDocumento=7";
			$nReg=$con->obtenerValor($consulta);
			if($nReg==0)
			{
				$resultado.=$comp."Documentos.- Debe ingresar el Comprobante de entrega del Informe anual (INDESOL) de su organización";
			}
			
			
		}
		
		
		$consulta="SELECT COUNT(*) FROM _407_documentosRequeridosOSC gd,_407_tablaDinamica d WHERE gd.idReferencia=d.id__407_tablaDinamica AND d.idReferencia=".$idOrganizacion." AND gd.tituloDocumento=1";
		$nReg=$con->obtenerValor($consulta);
		if($nReg==0)
		{
			$resultado.=$comp."Documentos.- Debe ingresar el Acta Constitutiva de su organización";
		}
		
		$consulta="SELECT COUNT(*) FROM _407_documentosRequeridosOSC gd,_407_tablaDinamica d WHERE gd.idReferencia=d.id__407_tablaDinamica AND d.idReferencia=".$idOrganizacion." AND gd.tituloDocumento=2";
		$nReg=$con->obtenerValor($consulta);
		if($nReg==0)
		{
			$resultado.=$comp."Documentos.- Debe ingresar el Comprobante de Domicilio de su organización";
		}
		
		$consulta="SELECT COUNT(*) FROM _407_documentosRequeridosOSC gd,_407_tablaDinamica d WHERE gd.idReferencia=d.id__407_tablaDinamica AND d.idReferencia=".$idOrganizacion." AND gd.tituloDocumento=5";
		$nReg=$con->obtenerValor($consulta);
		if($nReg==0)
		{
			$resultado.=$comp."Documentos.- Debe ingresar la Cédula Fiscal (RFC) de su organización";
		}
		
		$consulta="SELECT COUNT(*) FROM _407_documentosRequeridosOSC gd,_407_tablaDinamica d WHERE gd.idReferencia=d.id__407_tablaDinamica AND d.idReferencia=".$idOrganizacion." AND gd.tituloDocumento=8";
		$nReg=$con->obtenerValor($consulta);
		if($nReg==0)
		{
			$resultado.=$comp."Documentos.- Debe ingresar el Documento vigente expedido por el SAT en el que se emite la opinión de cumplimiento de obligaciones fiscales";
		}
		
/*		$consulta="SELECT COUNT(*) FROM _407_documentosRequeridosOSC gd,_407_tablaDinamica d WHERE gd.idReferencia=d.id__407_tablaDinamica AND d.idReferencia=".$idOrganizacion." AND gd.tituloDocumento=9";
		$nReg=$con->obtenerValor($consulta);
		if($nReg==0)
		{
			$resultado.=$comp."Documentos.- Debe ingresar la Carta de no existencia de conflicto de interés";
		}
		
		if(strtotime(date("Y-m-d H:i:s"))>strtotime("2014-03-10 10:00:00"))
		{
			$resultado.=$comp."La convocatoria a finalizado";	
		}*/
		
		
		
		return $resultado;
	}
	
	function cumpleCLUNI($organizacion)
	{
		global $con;
		$res=array();
		$res["situacion"]="1";	
		$consulta="SELECT tipoOrganizacion,cuentaCluni,id__367_tablaDinamica FROM _367_tablaDinamica WHERE codigoInstitucion='".$organizacion."'";
		$fDatosCluni=$con->obtenerPrimeraFila($consulta);
		if($fDatosCluni)
		{
			if($fDatosCluni[0]!=1)
			{
				$res["situacion"]="1";	
				return $res;
			}
			else
			{
				if($fDatosCluni[0]==1)
				{
					
					$consulta="SELECT COUNT(*) FROM _407_documentosRequeridosOSC gd,_407_tablaDinamica d WHERE gd.idReferencia=d.id__407_tablaDinamica AND d.idReferencia=".$fDatosCluni[2]." AND gd.tituloDocumento=6";
					$nReg=$con->obtenerValor($consulta);
					if($nReg==0)
					{
						
						
						$res["situacion"]="0";	
						$res["comentarios"]="La organizaci&oacute;n NO cuenta con el CLUNI (Escaneado)";
						return $res;
						
					}
					
					
					$consulta="SELECT COUNT(*) FROM _407_documentosRequeridosOSC gd,_407_tablaDinamica d WHERE gd.idReferencia=d.id__407_tablaDinamica AND d.idReferencia=".$fDatosCluni[2]." AND gd.tituloDocumento=7";
					$nReg=$con->obtenerValor($consulta);
					if($nReg==0)
					{
						
						
						
						$res["situacion"]="0";	
						$res["comentarios"]="La organizaci&oacute;n NO cuenta con el Comprobante de entrega del Informe anual (INDESOL)";
						return $res;
					}
					
					
				}
				else
				{
					$res["situacion"]="0";	
					$res["comentarios"]="La organizaci&oacute;n NO cuenta con CLUNI (No se permite CLUNI en trámite)";
					return $res;
				}
			}
		}
		else
		{
			$res["situacion"]="0";	
			$res["comentarios"]="La organizaci&oacute;n NO cuenta con CLUNI (No se permite CLUNI en trámite)";
		}
		return $res;
			
	}	
	
	function cumpleConvocatoriaAbiertaConfiguracion($idConfiguracion)
	{
		global $con;
		$res=array();	
		
		
		if(existeRol("'93_0'"))
		{
			$res["situacion"]="1";
			$res["comentarios"]="";	
			return $res;
		}
		
		
		$consulta="SELECT fechaInicioRegistro,horaInicioRegistro,fechaFinRegistro,horaFinRegistro FROM _412_tablaDinamica WHERE id__412_tablaDinamica=".$idConfiguracion;

		$fConfiguracion=$con->obtenerPrimeraFila($consulta);
		
		$fechaApertura=strtotime($fConfiguracion[0]." ".$fConfiguracion[1]);
		$fechaCierre=strtotime($fConfiguracion[2]." ".$fConfiguracion[3]);
		$fechaActual=strtotime(date("Y-m-d H:i:s"));
		
		if($fechaActual<$fechaApertura)
		{
			$res["situacion"]="0";
			$res["comentarios"]="El periodo de registro de proyectos de la convocatoria aún NO comienza (Fecha de inicio: ".date("d/m/Y H:i",$fechaApertura)." hrs.)";
		}
		else
		{
			if($fechaActual>=$fechaCierre)
			{
				$res["situacion"]="0";
				$res["comentarios"]="El periodo de registro de proyectos de la convocatoria ha finalizado (Fecha de cierre: ".date("d/m/Y H:i",$fechaCierre)." hrs.)";
			}
			else
			{
				$res["situacion"]="1";
				$res["comentarios"]="";
			}
		}
		
		
		
		return $res;
	}
	
	function cumplePrimerRegistroEnCategoria($idCategoria)
	{
		global $con;
		$res=array();	
		$consulta="SELECT codigo FROM _464_tablaDinamica WHERE codigoInstitucion='".$_SESSION["codigoInstitucion"]."' AND idCategoria=".$idCategoria;
		$folio=$con->obtenerValor($consulta);
		if($folio=="")
		{
			$res["situacion"]="1";
			$res["comentarios"]="";	
		}
		else
		{
			$res["situacion"]="0";
			$res["comentarios"]="La organización ya ha registrado un proyecto en esta categoría (Folio del proyecto: ".$folio.")";	
		}
		return $res;
		
	}
	
	function validarProyecto2a2014($idFormulario,$idRegistro)
	{
		global $con;	
		
		$consulta="SELECT idCategoria FROM _448_tablaDinamica WHERE id__448_tablaDinamica=".$idRegistro;
		$idReferencia=$con->obtenerValor($consulta);
		
		/*if(($idReferencia==4)||($idReferencia==5)||($idReferencia==8))
		{
			
			$consulta="SELECT indicador FROM _414_indicadoresCategoria gi WHERE gi.idReferencia=".$idReferencia;
		
			$listIndicadoresRef=$con->obtenerListaValores($consulta);
			
			
			$consulta="SELECT COUNT(*) FROM 109_indicadoresProyectos WHERE idFormulario=".$idFormulario." AND idReferencia=".$idRegistro." AND (numerador>0 OR denominador>0)
					and idIndicador in (".$listIndicadoresRef.")";	
			$nReg=$con->obtenerValor($consulta);
			if($nReg<2)
			{
				return "<br>Al menos debe considerar dos indicadores clasificados como \"De la categor&iacute;a\" dentro de su proyecto (Los indicadores con valor 0 no son considerados dentro del proyecto)";	
			}
			
		}*/
		
		/*$cadena="";
		$consulta="SELECT idIndicador FROM 109_indicadoresProyectos WHERE  idFormulario=".$idFormulario." AND idReferencia=".$idRegistro;
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$consulta="SELECT COUNT(*) FROM 968_actividadesIndicador WHERE idIndicador=".$fila[0]	;
			$nReg=$con->obtenerValor($consulta);
			if($nReg==0)
			{
				$consulta="SELECT nombreIndicador FROM 1026_catalogoIndicadores2014 WHERE idIndicador=".$fila[0];
				$nIndicador=$con->obtenerValor($consulta);
				$cadena.="<br>".$nIndicador;
					
			}
		}
			
		if($cadena!="")
		{
			return "<br>Se ha detectado que los siguientes indicadores no cuentan con algunas actividad asociada que permitan garantizar su cumplimiento: <br>".$cadena;	
		}*/
		
		/*$consulta="	SELECT * FROM (
					SELECT idActividadPrograma,(SELECT COUNT(*) FROM 968_planeacionActividadesMeses WHERE idActividad=a.idActividadPrograma) AS nReg
					FROM 965_actividadesUsuario a WHERE idFormulario=448 AND idReferencia=".$idRegistro.") AS tmp WHERE nReg=0";
		$con->obtenerFilas($consulta);
		if($con->filasAfectadas>0)
		{
			return "<br>Se ha detectado que en algunas actividades no se ha marcado los meses en los cuales ser&aacute;n ejecutados";	
		}*/
		
		
		$comp="<br><span style='color:#F00'><b>*</b></span> ";
		$resultado="";
		
		
		
		$consulta="SELECT id__367_tablaDinamica FROM _367_tablaDinamica WHERE codigoInstitucion='".$_SESSION["codigoInstitucion"]."'";
		
		$idOrganizacion=$con->obtenerValor($consulta);
		
		$consulta="SELECT tipoOrganizacion,cuentaCluni,CLUNI FROM _367_tablaDinamica WHERE id__367_tablaDinamica=".$idOrganizacion;
		$fOrganizacion=$con->obtenerPrimeraFila($consulta);
		if($fOrganizacion[0]==1)
		{
			if(trim($fOrganizacion[2])=="")
			{
				$resultado.=$comp."Proceso Actualziación de padrón, Sección: Datos de la Organización.- No ha ingresado el número de CLUNI de su organización";
			}
			$consulta="SELECT COUNT(*) FROM _407_documentosRequeridosOSC gd,_407_tablaDinamica d WHERE gd.idReferencia=d.id__407_tablaDinamica AND d.idReferencia=".$idOrganizacion." AND gd.tituloDocumento=6";
			$nReg=$con->obtenerValor($consulta);
			if($nReg==0)
			{
				$resultado.=$comp."Documentos.- Debe ingresar el CLUNI (Escaneado) de su organización";
			}
			
			
			$consulta="SELECT COUNT(*) FROM _407_documentosRequeridosOSC gd,_407_tablaDinamica d WHERE gd.idReferencia=d.id__407_tablaDinamica AND d.idReferencia=".$idOrganizacion." AND gd.tituloDocumento=7";
			$nReg=$con->obtenerValor($consulta);
			if($nReg==0)
			{
				$resultado.=$comp."Documentos.- Debe ingresar el Comprobante de entrega del Informe anual (INDESOL) de su organización";
			}
			
			
		}
		
		
		$consulta="SELECT COUNT(*) FROM _407_documentosRequeridosOSC gd,_407_tablaDinamica d WHERE gd.idReferencia=d.id__407_tablaDinamica AND d.idReferencia=".$idOrganizacion." AND gd.tituloDocumento=1";
		$nReg=$con->obtenerValor($consulta);
		if($nReg==0)
		{
			$resultado.=$comp."Documentos.- Debe ingresar el Acta Constitutiva de su organización";
		}
		
		$consulta="SELECT COUNT(*) FROM _407_documentosRequeridosOSC gd,_407_tablaDinamica d WHERE gd.idReferencia=d.id__407_tablaDinamica AND d.idReferencia=".$idOrganizacion." AND gd.tituloDocumento=2";
		$nReg=$con->obtenerValor($consulta);
		if($nReg==0)
		{
			$resultado.=$comp."Documentos.- Debe ingresar el Comprobante de Domicilio de su organización";
		}
		
		$consulta="SELECT COUNT(*) FROM _407_documentosRequeridosOSC gd,_407_tablaDinamica d WHERE gd.idReferencia=d.id__407_tablaDinamica AND d.idReferencia=".$idOrganizacion." AND gd.tituloDocumento=5";
		$nReg=$con->obtenerValor($consulta);
		if($nReg==0)
		{
			$resultado.=$comp."Documentos.- Debe ingresar la Cédula Fiscal (RFC) de su organización";
		}
		
		$consulta="SELECT COUNT(*) FROM _407_documentosRequeridosOSC gd,_407_tablaDinamica d WHERE gd.idReferencia=d.id__407_tablaDinamica AND d.idReferencia=".$idOrganizacion." AND gd.tituloDocumento=8";
		$nReg=$con->obtenerValor($consulta);
		if($nReg==0)
		{
			$resultado.=$comp."Documentos.- Debe ingresar el Documento vigente expedido por el SAT en el que se emite la opinión de cumplimiento de obligaciones fiscales";
		}
		
		/*$consulta="SELECT COUNT(*) FROM _407_documentosRequeridosOSC gd,_407_tablaDinamica d WHERE gd.idReferencia=d.id__407_tablaDinamica AND d.idReferencia=".$idOrganizacion." AND gd.tituloDocumento=9";
		$nReg=$con->obtenerValor($consulta);
		if($nReg==0)
		{
			$resultado.=$comp."Documentos.- Debe ingresar la Carta de no existencia de conflicto de interés";
		}*/
		
		
		$res=cumpleConvocatoriaAbiertaConfiguracion(3);
		
		if($res["situacion"]=="0")
		{
			$resultado.=$comp.$res["comentarios"];	
		}
		
		
		$consulta="SELECT idObjetivoGeneral FROM 112_objetivoGeneral WHERE idFormulario=".$idFormulario." AND idReferencia=".$idRegistro;
		$idObjetivoGeneral=$con->obtenerValor($consulta);
		if($idObjetivoGeneral=="")
		{
			$resultado.=$comp."Objetivos, Metas, Actividades.- Debe ingresar el objetivo general del proyecto";
		}
		
		$consulta="SELECT * FROM 113_objetivosEspecificos WHERE idObjetivoGeneral=".$idObjetivoGeneral;
		$res=$con->obtenerFilas($consulta);
		
		if($con->filasAfectadas==0)
		{
			$resultado.=$comp."Objetivos, Metas, Actividades.- Almenos debe ingresar un objetivo específico";
		}
		else
		{
			$nObjetivoEspecifico=1;
			while($fila=mysql_fetch_row($res))
			{
				$consulta="SELECT * FROM 114_metasProyectos2014 WHERE idObjetivoEspecifico=".$fila[0];
				$resMetas=$con->obtenerFilas($consulta);
				if($con->filasAfectadas==0)
				{
					$resultado.=$comp."Objetivos, Metas, Actividades.- Almenos debe ingresar una meta para el objetivo específico: ".$fila[2];
				}
				else
				{
					while($fMeta=mysql_fetch_row($resMetas))
					{
						$consulta="SELECT * FROM 965_actividadesUsuario WHERE idMeta=".$fMeta[0];
						$resActividades=$con->obtenerFilas($consulta);
						if($con->filasAfectadas==0)
						{
							$resultado.=$comp."Objetivos, Metas, Actividades.- Almenos debe ingresar una actividad para la meta: ".$fMeta[2];
						}
					}
				}
				
				
				$nObjetivoEspecifico++;
			}
		}
		
		
		return $resultado;
	}
	
	function esProyectoFinanciado($idFormulario,$idRegistro)
	{
		global $con;
		$consulta="SELECT marcaAutorizado FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;	
		$marca=$con->obtenerValor($consulta);
		return $marca;
	}
	
	function actualizacionDatosOSCCompletada($idUsuario)
	{
		global $con;
		$consulta="SELECT Institucion FROM 801_adscripcion WHERE idUsuario=".$idUsuario;
		$osc=$con->obtenerValor($consulta);
		$consulta="SELECT idEstado,id__367_tablaDinamica FROM _367_tablaDinamica WHERE codigoInstitucion='".$osc."'";
		$fDatos=$con->obtenerPrimeraFila($consulta);
		$idEstado=$fDatos[0];
		$idRegistro=$fDatos[1];
		
		$arrResultado=array();
		$arrResultado[0]=0;
		$arrResultado[1]="";
		$arrResultado[2]="";
		
		if($idEstado==2)
		{
			$arrResultado[0]=1;
			$arrResultado[2]=bE('window.parent.abrirVentanaFancy({url:"../modeloPerfiles/vistaDTDv2.php",ancho:"100%",alto:"100%",title:"Actualizar datos de la organizaci&oacute;n",params:[["idRegistro","'.$idRegistro.'"],["idFormulario","367"],["actor","'.bE(0).'"],["dComp","YXV0bw"]]})');
		}
		else
			if($idEstado==1)
			{
				$arrResultado[1]="Debe finalizar el proceso de actualizaci&oacute;n de datos de su organizaci&oacute;n";
				$arrResultado[2]=bE('window.parent.abrirVentanaFancy({url:"../modeloPerfiles/vistaDTDv2.php",ancho:"100%",alto:"100%",title:"Actualizar datos de la organizaci&oacute;n",params:[["idRegistro","'.$idRegistro.'"],["idFormulario","367"],["actor","'.bE(159).'"],["dComp","YXV0bw"]]})');
				
			}
			else
			{
				$arrResultado[2]=bE('window.parent.abrirVentanaFancy({url:"../modeloPerfiles/vistaDTDv2.php",ancho:"100%",alto:"100%",title:"Actualizar datos de la organizaci&oacute;n",params:[["idRegistro","-1"],["idFormulario","367"],["actor","MzU="],["dComp","YWdyZWdhcg=="]]})');
			}
		return $arrResultado;
		
	}
	
	function actualizacionDatosPerfilOSCCompletado($idUsuario)
	{
		global $con;
		$consulta="SELECT Institucion FROM 801_adscripcion WHERE idUsuario=".$idUsuario;
		$osc=$con->obtenerValor($consulta);
		$consulta="SELECT idEstado,id__485_tablaDinamica FROM _485_tablaDinamica WHERE codigoInstitucion='".$osc."'";
		$fDatos=$con->obtenerPrimeraFila($consulta);
		$idEstado=$fDatos[0];
		$idRegistro=$fDatos[1];
		
		$arrResultado=array();
		$arrResultado[0]=0;
		$arrResultado[1]="";
		$arrResultado[2]="";
		
		if($idEstado==2)
		{
			$arrResultado[0]=1;
			$arrResultado[2]=bE('window.parent.abrirVentanaFancy({url:"../modeloPerfiles/vistaDTDv2.php",ancho:"100%",alto:"100%",title:"Actualizar datos de la organizaci&oacute;n",params:[["idRegistro","'.$idRegistro.'"],["idFormulario","485"],["actor","'.bE(0).'"],["dComp","YXV0bw"]]})');
		}
		else
			if($idEstado==1)
			{
				$arrResultado[1]="Debe finalizar el proceso de registro de perfil de su organizaci&oacute;n";
				$arrResultado[2]=bE('window.parent.abrirVentanaFancy({url:"../modeloPerfiles/vistaDTDv2.php",ancho:"100%",alto:"100%",title:"Actualizar datos de la organizaci&oacute;n",params:[["idRegistro","'.$idRegistro.'"],["idFormulario","485"],["actor","'.bE(211).'"],["dComp","YXV0bw"]]})');
			}
			else
			{
				$arrResultado[1]="Debe registrar los datos del perfil de su organizaci&oacute;n";
				$arrResultado[2]=bE('window.parent.abrirVentanaFancy({url:"../modeloPerfiles/vistaDTDv2.php",ancho:"100%",alto:"100%",title:"Actualizar datos de la organizaci&oacute;n",params:[["idRegistro","-1"],["idFormulario","485"],["actor","NzY"],["dComp","YWdyZWdhcg"]]})');
			}
		return $arrResultado;
	}
	
	function cumpleLimiteRegistroProyectosOsc($idUsuario,$idConvocatoria)
	{
		global $con;
		$consulta="SELECT Institucion FROM 801_adscripcion WHERE idUsuario=".$idUsuario;
		$osc=$con->obtenerValor($consulta);
		
		$consulta="SELECT maximoProyectosOSC,criterioLimiteProyectosOSC,procesoAsociado FROM `_412_tablaDinamica` WHERE id__412_tablaDinamica=".$idConvocatoria;
		$fConfConvocatoria=$con->obtenerPrimeraFila($consulta);
		
		$idFormularioBase=obtenerFormularioBase($fConfConvocatoria[2]);
		
		$consulta="SELECT COUNT(*) FROM  _".$idFormularioBase."_tablaDinamica WHERE codigoInstitucion='".$osc."'";
		
		if($fConfConvocatoria[1]==2)
		{
			$consulta.=" and idEstado=2";
		}
		
		$nRegistros=$con->obtenerValor($consulta);
		
		$arrResultado=array();
		$arrResultado[0]=0;
		$arrResultado[1]="";
		$arrResultado[2]="";
		
		
		$arrResultado[1]="Ha registrado <b>".$nRegistros."</b> proyectos de <b>".$fConfConvocatoria[0]."</b> permitidos";
		
		if($nRegistros<$fConfConvocatoria[0])
		{
			$arrResultado[0]=1;
			
		}
		else
		{
			$arrResultado[0]=0;
		}
		
		return $arrResultado;
		
		
	}
	
	function cumpleLimiteRegistroProyectosConvocatoria($idConvocatoria)
	{
		global $con;
		
		
		$consulta="SELECT maximoProyectos,criterioRegistroProyectos,procesoAsociado FROM `_412_tablaDinamica` WHERE id__412_tablaDinamica=".$idConvocatoria;
		$fConfConvocatoria=$con->obtenerPrimeraFila($consulta);
		
		$idFormularioBase=obtenerFormularioBase($fConfConvocatoria[2]);
		
		$consulta="SELECT COUNT(*) FROM  _".$idFormularioBase."_tablaDinamica";
		
		if($fConfConvocatoria[1]==2)
		{
			$consulta.=" where idEstado=2";
		}
		
		$nRegistros=$con->obtenerValor($consulta);
		
		$arrResultado=array();
		$arrResultado[0]=0;
		$arrResultado[1]="";
		$arrResultado[2]="";
		
		
		if($nRegistros<$fConfConvocatoria[0])
		{
			$arrResultado[0]=1;
			
		}
		else
		{
			$arrResultado[0]=0;
			$arrResultado[1]="Se ha llegado al l&imite de registro de proyetos permitidos por la convocatoria (<b>".$fConfConvocatoria[0]."</b> proyectos)";
		}
		
		return $arrResultado;
		
		
	}

	function esConvocatoriaAbiertaConfiguracion($idConvocatoria)
	{
		global $con;
		$arrResultado=array();
		$arrResultado[0]=0;
		$arrResultado[1]="";
		$arrResultado[2]="";
		$consulta="SELECT fechaInicioRegistro,horaInicioRegistro,fechaFinRegistro,horaFinRegistro FROM _412_tablaDinamica WHERE id__412_tablaDinamica=".$idConvocatoria;
		$fConfiguracion=$con->obtenerPrimeraFila($consulta);
		$fechaActual=(obtenerFechaActual());
		$horaActual=(obtenerHoraActual());
		$fInicioConv=strtotime($fConfiguracion[0]." ".$fConfiguracion[1]);
		$fFinConv=strtotime($fConfiguracion[2]." ".$fConfiguracion[3]);
		$fActual=strtotime($fechaActual." ".$horaActual);
		if(($fActual>=$fInicioConv)&&($fActual<=$fFinConv))
		{
			$arrResultado[0]=1;
		}
		else
		{
			$arrResultado[0]=0;
			$arrResultado[1]="La convocatoria ha finalizado";
		}
		
		return 	$arrResultado;
	}

	function validarProyectoConfiguracionPerfil($idFormulario,$idRegistro)
	{
		global $con;	
		
		$consulta="SELECT idReferencia,responsable FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
		$fDatosRegistros=$con->obtenerPrimeraFila($consulta);
		$idConvocatoria=$fDatosRegistros[0];
		
		
		$consulta="SELECT g.id__465_gridFunciones as idRequisito,g.descripcion as requisito,g.descripcionDetalle as descripcion,g.funcion,g.funcionAccion FROM 
				_465_tablaDinamica t,_465_gridFunciones g WHERE g.idReferencia=t.id__465_tablaDinamica AND t.idReferencia=".$idConvocatoria." and g.aplicableAEnvioValidacion=1 ORDER BY id__465_gridFunciones";

		$numReg=0;
		$cache=NULL;	
		$res=$con->obtenerFilas($consulta);
		$resultado="";
		while($fila=mysql_fetch_row($res))
		{
			
			$cadObj='{"idFormulario":"'.$idFormulario.'","idRegistro":"'.$idRegistro.'","idUsuario":"'.$fDatosRegistros[1].'","idRequisito":"'.$fila[0].'","idConvocatoria":"'.$idConvocatoria.'"}';
			$objReg=json_decode($cadObj);
			
			$arrResultado=resolverExpresionCalculoPHP($fila[3],$objReg,$cache);
			
			if($arrResultado[0]==0)
			{
				$o="['".(isset($arrResultado[3])?cv($arrResultado[3]):'General')."','".cv($arrResultado[1])."']";	
				if($resultado=="")
					$resultado=$o;
				else
					$resultado.=",".$o;
			}
			
		}
		
		
		
		return "[".$resultado."]";
	}

	function validarNuevoRegistroProyectosCensidaVistaDTD($idReferencia,$idFormulario,$idRegistro)
	{
		
		global $con;	
		if($idRegistro!=-1)
			return "[]";
			
		
		$idConvocatoria=$idReferencia;
		
		
		$consulta="SELECT g.id__465_gridFunciones as idRequisito,g.descripcion as requisito,g.descripcionDetalle as descripcion,g.funcion,g.funcionAccion FROM 
				_465_tablaDinamica t,_465_gridFunciones g WHERE g.idReferencia=t.id__465_tablaDinamica AND t.idReferencia=".$idConvocatoria." and g.aplicableARegistro=1 and g.obligatorio ORDER BY id__465_gridFunciones";

		$numReg=0;
		$cache=NULL;	
		$res=$con->obtenerFilas($consulta);
		$resultado="";
		while($fila=mysql_fetch_row($res))
		{
			
			$cadObj='{"idUsuario":"'.$_SESSION["idUsr"].'","idRequisito":"'.$fila[0].'","idConvocatoria":"'.$idConvocatoria.'"}';
			$objReg=json_decode($cadObj);
			$arrResultado=resolverExpresionCalculoPHP($fila[3],$objReg,$cache);
			
			if($arrResultado[0]==0)
			{
				$o="['".(isset($arrResultado[3])?cv($arrResultado[3]):'General')."','".cv($arrResultado[1])."']";	
				if($resultado=="")
					$resultado=$o;
				else
					$resultado.=",".$o;
			}
			
		}
		
		
		
		return "[".$resultado."]";
	}
	
	function verificarObjetivosEspecificosPerfil($idFormulario,$idRegistro)
	{
		global $con;
		$consulta="SELECT idObjetivoGeneral FROM 112_objetivoGeneral WHERE idFormulario=".$idFormulario." AND idReferencia=".$idRegistro;
		$oGeneral=$con->obtenerValor($consulta);
		$consulta="SELECT count(*) FROM 113_objetivosEspecificos WHERE idObjetivoGeneral=".$oGeneral;
		$nReg=$con->obtenerValor($consulta);
		
		$arrResultado=array();
		$arrResultado[0]=0;
		$arrResultado[1]="";
		$arrResultado[2]="";
		
		if($nReg<3)
		{
			$arrResultado[1]="Debe registrar almenos 3 objetivos espec&iacute;ficos asociados al objetivo general (Definición de objetivos, metas y cronograma de actividades)";
		}
		else
			$arrResultado[0]=1;
			
		return $arrResultado;
	}
	
	function funcionRecomendacion1($idUsuario,$idCategoria,$idConvocatoria)
	{
		return 1;
	}
	
	function funcionRecomendacion2($idUsuario,$idCategoria,$idConvocatoria)
	{
		return 1;
	}
	
	function validacionRegistroPerfil($idFormulario,$idRegistro)
	{
		global $con;
		$cadRes="";
		$consulta="SELECT cExperienciaEjecucionProyectos,DProfesionalizacion,EMetodolodias,perteneceRed FROM _485_tablaDinamica WHERE id__485_tablaDinamica=".$idRegistro;
		$fRegistro=$con->obtenerPrimeraFila($consulta);
		
		if($fRegistro[0]==1)
		{
			$consulta="SELECT COUNT(*) FROM _485_gridExperienciaEjecucion WHERE idReferencia=".$idRegistro;
			$nReg=$con->obtenerValor($consulta);
			if($nReg==0)
			{
				$o="['Registro de perfil','Debe ingresar la expericenica que su organizaci&oacute;n tiene en la ejecuci&oacute;n de proyectos']";
				$cadRes=$o;
			}
		}
		
		if($fRegistro[1]==1)
		{
			$consulta="SELECT COUNT(*) FROM _485_gProfesionalizacionOSC WHERE idReferencia=".$idRegistro;
			$nReg=$con->obtenerValor($consulta);
			if($nReg==0)
			{
				$o="['Registro de perfil','Debe ingresar la documentaci&oacute;n que avale la profesionalizaci&oacute;n de su organizaci&oacute;n y/o actores sociales']";
				if($cadRes=="")
					$cadRes=$o;	
				else
					$cadRes.=",".$o;
			}
		}
		
		if($fRegistro[2]==1)
		{
			$consulta="SELECT COUNT(*) FROM _485_gMetodologiaPublicacion WHERE idReferencia=".$idRegistro;
			$nReg=$con->obtenerValor($consulta);
			if($nReg==0)
			{
				$o="['Registro de perfil','Debe ingresar las metodolog&iacute;as o publicaciones que su organizaci&oacute;n ha desarrollado derivadas de las acciones que realiza']";
				if($cadRes=="")
					$cadRes=$o;	
				else
					$cadRes.=",".$o;
			}	
		}
		
		if($fRegistro[3]==1)
		{
			$consulta="SELECT COUNT(*) FROM _485_gridRedes WHERE idReferencia=".$idRegistro;
			$nReg=$con->obtenerValor($consulta);
			if($nReg==0)
			{
				$o="['Registro de perfil','Debe ingresar las redes o colectivos a las cuales pertenece su organizaci&oacute;n']";
				if($cadRes=="")
					$cadRes=$o;	
				else
					$cadRes.=",".$o;
			}
		}
		
		
		return "[".$cadRes."]";	
	}
	
	function existeCartaProtesta($idFormulario,$idRegistro)
	{
		global $con;
		$consulta="SELECT cartaProtesta FROM _498_tablaDinamica WHERE id__498_tablaDinamica=".$idRegistro;
		$carta=$con->obtenerValor($consulta);
		
		$arrResultado=array();
		$arrResultado[0]=0;
		$arrResultado[1]="";
		$arrResultado[2]="";
		
		if($carta=='')
		{
			$arrResultado[1]="Debe ingresar la carta bajo protesta de decir verdad (Secci&oacute;n: I. Pertinencia e impacto social)";
		}
		else
			$arrResultado[0]=1;
			
	
		return $arrResultado;
		
	}
	
	function esProyectoLiberado($idFormulario,$idRegistro)
	{
		global $con;
		$consulta="SELECT proyLiberado FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
		$proyLiberado=$con->obtenerValor($consulta);
		return $proyLiberado;
	}
	
	function existeCartaProtesta2da2015($idFormulario,$idRegistro)
	{
		global $con;
		$consulta="SELECT cartaProtesta FROM _525_tablaDinamica WHERE idReferencia=".$idRegistro;
		$carta=$con->obtenerValor($consulta);
		
		$arrResultado=array();
		$arrResultado[0]=0;
		$arrResultado[1]="";
		$arrResultado[2]="";
		
		if($carta=='')
		{
			$arrResultado[1]="Debe ingresar la carta bajo protesta de decir verdad (Secci&oacute;n: Documentos anexos)";
		}
		else
			$arrResultado[0]=1;
			
	
		return $arrResultado;
		
	}
	
?>