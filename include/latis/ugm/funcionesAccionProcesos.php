<?php include_once("latis/ugm/funcionesNomina.php");


	//Funciones del poceso Solicitud de cambio de calificaciones
	function clonarEvaluacionesGrupo($idFormulario,$idRegistro)
	{
		global $con;
		$query="SELECT idGrupo,noEvaluacion,tipoEvaluacion FROM _885_tablaDinamica WHERE id__885_tablaDinamica=".$idRegistro;
		$fRegistro=$con->obtenerPrimeraFila($query);
		
		$x=0;
		$consulta[$x]="begin";
		$x++;
		$consulta[$x]="INSERT INTO 4594_calificacionesCriteriosAlumnoCambioEvaluacion(idAlumno,idGrupo,idCriterio,bloque,valor,porcentajeObtenido,porcentajeValor,totalConsiderar,tipoEvaluacion,noEvaluacion,
						idFormulario,idReferencia,valorCambio,porcentajeObtenidoCambio,porcentajeValorCambio,totalConsiderarCambio)
						SELECT idAlumno,idGrupo,idCriterio,bloque,valor,porcentajeObtenido,porcentajeValor,totalConsiderar,tipoEvaluacion,noEvaluacion,'".$idFormulario."','".$idRegistro."',valor,porcentajeObtenido,porcentajeValor,totalConsiderar
						FROM 4568_calificacionesCriteriosAlumnoPerfilMateria WHERE idGrupo=".$fRegistro[0]." AND tipoEvaluacion=".$fRegistro[2]." AND noEvaluacion=".$fRegistro[1];

		$x++;
		$consulta[$x]="INSERT INTO  4595_calificacionesEvaluacionAlumnoCambioEvaluacion(idAlumno,idGrupo,bloque,valor,tipoEvaluacion,noEvaluacion,aprobado,idFormulario,idReferencia,valorCambio,aprobadoCambio)
						SELECT idAlumno,idGrupo,bloque,valor,tipoEvaluacion,noEvaluacion,aprobado,'".$idFormulario."','".$idRegistro."',valor,aprobado FROM 4569_calificacionesEvaluacionAlumnoPerfilMateria WHERE 
						idGrupo=".$fRegistro[0]." AND tipoEvaluacion=".$fRegistro[2]." AND noEvaluacion=".$fRegistro[1];
		$x++;
		
		$consulta[$x]="INSERT INTO 4596_valoresMaximosCriterioCambioEvaluacion(idCriterio,idGrupo,noBloque,valMax,idAlumno,tipoEvaluacion,noEvaluacion,idFormulario,idReferencia,valMaxCambio)
					SELECT idCriterio,idGrupo,noBloque,valMax,idAlumno,tipoEvaluacion,noEvaluacion,'".$idFormulario."','".$idRegistro."',valMax FROM 4571_valoresMaximosCriterioPerfilMateria WHERE 
					idGrupo=".$fRegistro[0]." AND tipoEvaluacion=".$fRegistro[2]." AND noEvaluacion=".$fRegistro[1];
		$x++;
		
		$consulta[$x]="commit";
		$x++;
			
		return $con->ejecutarBloque($consulta);
	}
	
	function recalcularCalificacionesGrupoCambioEvaluacion($idGrupo,$idCriterio,$bloque,$tipoEvaluacion,$noEvaluacion,$idFormulario,$idRegistro,$idAlumno=-1)//1 Cambio de limite máximo,2 Valor calificacion,3 Porcentaje
	{
		global $con;
		$consulta="SELECT idPlanEstudio,idInstanciaPlanEstudio,idMateria FROM 4520_grupos WHERE idGrupos=".$idGrupo;
		$fGrupo=$con->obtenerPrimeraFila($consulta);
		$idPlanEstudio=$fGrupo[0];
		$idInstanciaPlanEstudio=$fGrupo[1];
		$idMateria=$fGrupo[2];
		
		$consulta="SELECT idPerfil,calificacionMinimaAprobatoria FROM 4592_configuracionPerfilEvaluacion WHERE idPlanEstudio=".$idPlanEstudio." AND idInstanciaPlanEstudio=".$idInstanciaPlanEstudio." AND idMateria=".$idMateria." and
				idGrupo in (".$idGrupo.",-1) AND tipoExamen=".$tipoEvaluacion." AND noExamen=".$noEvaluacion." order by idGrupo desc";
		$fPerfil=$con->obtenerPrimeraFila($consulta);
		$idPerfil=$fPerfil[0];
		if($idPerfil=="")
			$idPerfil=-1;
		
		
		$consulta="SELECT idCriterio FROM 4564_criteriosEvaluacionPerfilMateria WHERE  codigoUnidad='".$idCriterio."'";
		$idCriterioEval=$con->obtenerValor($consulta);
		
		$consulta="SELECT idTipoEvaluacion,funcionEvaluacion,funcionSistemaValMax,formaValorMaximoCriterio,valorMaximo,idEscalaCalificacion,idEvaluacion,precisionDecimales,accionPrecision FROM 4010_evaluaciones e
					 WHERE e.idEvaluacion= ".$idCriterioEval;
		$fCriterio=$con->obtenerPrimeraFila($consulta);
		
		$pCriterio="NULL";
		$tPonderacion="";
		if(strlen($idCriterio)==14)
		{
			$consulta="SELECT tipoPonderacion FROM 4591_perfilesEvaluacionMateria WHERE idPerfil=".$idPerfil;
			$tPonderacion=$con->obtenerValor($consulta);
			if($tPonderacion==1)
			{
				$consulta="SELECT porcentaje FROM 4565_porcentajeCriterioEvaluacionPerfil WHERE idCriterioEvaluacionMateria='".$idCriterio."' AND idPerfil=".$idPerfil." and  bloque=0";
				$pCriterio=$con->obtenerValor($consulta);
			}
		}
		else
		{
			$codigoPadre=substr($idCriterio,0,strlen($idCriterio)-7);
			$consulta="SELECT tipoPonderacion FROM 4564_criteriosEvaluacionPerfilMateria WHERE idPerfil=".$idPerfil." AND codigoUnidad='".$codigoPadre."'";
			$tPonderacion=$con->obtenerValor($consulta);
			if($tPonderacion==1)
			{
				$consulta="SELECT porcentaje FROM 4565_porcentajeCriterioEvaluacionPerfil WHERE idCriterioEvaluacionMateria='".$idCriterio."' AND idPerfil=".$idPerfil." and  bloque=0";
				$pCriterio=$con->obtenerValor($consulta);
			}

			
		}
		
		//$consulta="SELECT porcentaje FROM  4565_porcentajeCriterioEvaluacionPerfil WHERE idCriterioEvaluacionMateria='".$idCriterio."'  AND  bloque=".$bloque;
		//$pCriterio=$con->obtenerValor($consulta);
		
		$consulta="SELECT valMaxCambio FROM 4596_valoresMaximosCriterioCambioEvaluacion WHERE idFormulario=".$idFormulario." and idReferencia=".$idRegistro." and  idGrupo=".$idGrupo." AND noBloque=".$bloque." AND idCriterio='".$idCriterio.
				"' AND(idAlumno=".$idAlumno." OR idAlumno IS NULL) and tipoEvaluacion=".$tipoEvaluacion." and noEvaluacion=".$noEvaluacion;  
		$limiteMax=$con->obtenerValor($consulta);
		$calcularLimiteMaximo=false;
		if($limiteMax=="")
		{
			switch($fCriterio[3])
			{
				case 1:
					$consulta="SELECT max(valorMaximo) FROM 4033_elementosEscala WHERE idEscalaCalificacion=".$fCriterio[5];
					$limiteMax=$con->obtenerValor($consulta);
				break;
				case 2:
					$limiteMax=$fCriterio[4];
				break;
				case 3:
					$calcularLimiteMaximo=true;
				break;
				
			}
		}
		if($limiteMax=="")
			$limiteMax=1;

		$fConfiguracion=obtenerPerfilExamenGrupo($idGrupo,$tipoEvaluacion);
		$cache=NULL;
		$arrUsrFinal=array();
		if($idAlumno==-1)
		{
			if($fConfiguracion[18]!="")
			{
				$cTmp='{"idGrupo":"'.$idGrupo.'","tipoEvaluacion":"'.$tipoEvaluacion.'","noEvaluacion":"'.$noEvaluacion.'"}';
				$objTmp=json_decode($cTmp);
				
				
				$arrUsr=resolverExpresionCalculoPHP($fConfiguracion[18],$objTmp,$cache);
				
				if(sizeof($arrUsr)>0)
				{
					foreach($arrUsr as $u)
					{
						$arrUsrFinal[$u["idUsuario"]]["registraCalificacion"]=$u["registraCalificacion"];
						$arrUsrFinal[$u["idUsuario"]]["comentarios"]=$u["comentarios"];
						
					}
				}
			}
			
		}
		else
		{
			$arrUsrFinal[$idAlumno]["registraCalificacion"]=1;
			$arrUsrFinal[$idAlumno]["comentarios"]="";
			
		}
			
		$calificacion=0;

		
		foreach($arrUsrFinal as $idUsr=>$resto)
		{
			$fila[0]=$idUsr;
			$idCalificacion=-1;
			if($calcularLimiteMaximo)
			{
				$cadObj='{"idCriterio":"'.$idCriterio.'","idGrupo":"'.$idGrupo.'","idUsuario":"'.$fila[0].'","bloque":"'.$bloque.'","tipoEvaluacion":"'.$tipoEvaluacion.'","noEvaluacion":"'.$noEvaluacion.'"}';
				$obj=json_decode($cadObj);
				
				$limiteMax=resolverExpresionCalculoPHP($fCriterio[2],$obj,$cache);
			}
			$calcular=true;
			$consulta="select * from 4594_calificacionesCriteriosAlumnoCambioEvaluacion WHERE idFormulario=".$idFormulario." and idReferencia=".$idRegistro." and idAlumno=".$fila[0]." AND idGrupo=".$idGrupo." AND idCriterio=".$idCriterio.
							" and bloque=".$bloque." and tipoEvaluacion=".$tipoEvaluacion." and noEvaluacion=".$noEvaluacion;	
			$fCal=$con->obtenerPrimeraFila($consulta);
			if($fCal)
			{						
	
				$calificacion=$fCal[13];
				$idCalificacion=$fCal[0];
			}
			else
				$calificacion=0;
			switch($fCriterio[0])
			{
				
				case 2:
					$cadObj='{"idCriterio":"'.$idCriterio.'","idGrupo":"'.$idGrupo.'","idUsuario":"'.$fila[0].'","bloque":"'.$bloque.'","tipoEvaluacion":"'.$tipoEvaluacion.'","noEvaluacion":"'.$noEvaluacion.'"}';
					$obj=json_decode($cadObj);
					
					$calificacion=resolverExpresionCalculoPHP($fCriterio[1],$obj,$cache);
					if( !is_numeric($calificacion))
						$calificacion=0;
				break;
			}

			if($calificacion<=$limiteMax)
			{
				if($limiteMax==0)					
					$calificacionFinal=0;
				else
				{
					if($tPonderacion==1)
						$calificacionFinal=($calificacion/$limiteMax)*$pCriterio;					
					else
						$calificacionFinal=($calificacion/$limiteMax)*100;
					
				}
			}
			else
			{

				if($tPonderacion==1)
					$calificacionFinal=$pCriterio;
				else
					$calificacionFinal=100;
			}
			if($fCriterio[8]==1)//
				$calificacionFinal=truncarValor($calificacionFinal,$fCriterio[7]);
			else
				$calificacionFinal=str_replace(",","",number_format($calificacionFinal,$fCriterio[7]));

			if($idCalificacion!=-1)
			{
				$consulta="UPDATE 4594_calificacionesCriteriosAlumnoCambioEvaluacion SET valorCambio=".$calificacion.",porcentajeObtenidoCambio=".$calificacionFinal.",porcentajeValorCambio=".$pCriterio.",totalConsiderarCambio='".$limiteMax."' 
							WHERE idCalificacion=".$idCalificacion;
			}
			else
			{
				$consulta="insert into 4594_calificacionesCriteriosAlumnoCambioEvaluacion (idAlumno,idGrupo,idCriterio,bloque,valor,porcentajeObtenido,porcentajeValor,totalConsiderar,tipoEvaluacion,noEvaluacion,idFormulario,
							idReferencia,valorCambio,porcentajeObtenidoCambio,porcentajeValorCambio,totalConsiderarCambio) 
							values(".$fila[0].",".$idGrupo.",'".$idCriterio."',".$bloque.",0,0,".$pCriterio.",'".$limiteMax."',".$tipoEvaluacion.",".$noEvaluacion.",".$idFormulario.",".$idRegistro.",
							".$calificacion.",".$calificacionFinal.",".$pCriterio.",'".$limiteMax."')";
			
			}

			if(!(($con->ejecutarConsulta($consulta))&&(recalcularCalificacionCriterioPadreCambioEvaluacion($idGrupo,$idCriterio,$bloque,$fila[0],$tipoEvaluacion,$noEvaluacion,$idFormulario,$idRegistro))&&(recalcularCalificacionBloqueCambioEvaluacion($idGrupo,$bloque,$fila[0],$tipoEvaluacion,$noEvaluacion,$idFormulario,$idRegistro))))
			{
				return false;
			}
		}
		return true;
	}
	
	function recalcularCalificacionCriterioPadreCambioEvaluacion($idGrupo,$idCriterio,$bloque,$idAlumno,$tipoEvaluacion,$noEvaluacion,$idFormulario,$idRegistro)
	{
		global $con;
		if(strlen($idCriterio)==14)
			return true;
			
		$query="SELECT idPlanEstudio,idInstanciaPlanEstudio,idMateria FROM 4520_grupos WHERE idGrupos=".$idGrupo;
		$fGrupo=$con->obtenerPrimeraFila($query);
		
		
		
		$idPlanEstudio=$fGrupo[0];
		$idInstanciaPlanEstudio=$fGrupo[1];
		$idMateria=$fGrupo[2];
		
		$consulta="SELECT idPerfil,calificacionMinimaAprobatoria FROM 4592_configuracionPerfilEvaluacion WHERE idPlanEstudio=".$idPlanEstudio." AND idInstanciaPlanEstudio=".$idInstanciaPlanEstudio." AND idMateria=".$idMateria." and
				idGrupo in (".$idGrupo.",-1) AND tipoExamen=".$tipoEvaluacion." AND noExamen=".$noEvaluacion." order by idGrupo desc";
		$fPerfil=$con->obtenerPrimeraFila($consulta);
		$idPerfil=$fPerfil[0];
		if($idPerfil=="")
			$idPerfil=-1;	
			
		$codigoPadre=substr($idCriterio,0,strlen($idCriterio)-7);
		
		$consulta="SELECT idCriterio FROM 4564_criteriosEvaluacionPerfilMateria WHERE codigoUnidad='".$codigoPadre."'";
		
		
		$idCriterioEval=$con->obtenerValor($consulta);
		
		$consulta="SELECT idTipoEvaluacion,funcionEvaluacion,funcionSistemaValMax,formaValorMaximoCriterio,valorMaximo,idEscalaCalificacion,idEvaluacion,precisionDecimales,accionPrecision FROM 4010_evaluaciones e
					 WHERE e.idEvaluacion= ".$idCriterioEval;
		
		$fCriterio=$con->obtenerPrimeraFila($consulta);
				
		$consulta="SELECT porcentajeObtenidoCambio,idCriterio FROM 4594_calificacionesCriteriosAlumnoCambioEvaluacion WHERE idFormulario=".$idFormulario." and idReferencia=".$idRegistro." and idAlumno=".$idAlumno." AND idGrupo=".$idGrupo." AND idCriterio LIKE '".$codigoPadre."%' AND bloque=".
				$bloque." and  tipoEvaluacion=".$tipoEvaluacion." and noEvaluacion=".$noEvaluacion;
		
		$res=$con->obtenerFilas($consulta);
		$total=0;
		$numHijos=0;
		while($fila=mysql_fetch_row($res))
		{
			if(strlen($fila[1])==(strlen($codigoPadre)+7))
			{
				$total+=$fila[0];
				$numHijos++;
			}
		}
		
		
		$pCriterio="NULL";
		
		$consulta="SELECT tipoPonderacion FROM 4564_criteriosEvaluacionPerfilMateria WHERE idPerfil=".$idPerfil." AND codigoUnidad='".$codigoPadre."'";
		$tPonderacion=$con->obtenerValor($consulta);
		if($tPonderacion==1)
		{
			
			$calificacionFinal=$total;
		}
		else
		{
			$calificacionFinal=($total/$numHijos);
		}
		$tPonderacionPadre="";
		if(strlen($codigoPadre)==14)
		{
			$consulta="SELECT tipoPonderacion FROM 4591_perfilesEvaluacionMateria WHERE idPerfil=".$idPerfil;
			$tPonderacionPadre=$con->obtenerValor($consulta);
		}
		else
		{
			$codigoPadre2=substr($codigoPadre,0,strlen($codigoPadre)-7);
			$consulta="SELECT tipoPonderacion FROM 4564_criteriosEvaluacionPerfilMateria WHERE idPerfil=".$idPerfil." AND codigoUnidad='".$codigoPadre2."'";
			$tPonderacionPadre=$con->obtenerValor($consulta);
		}
		
		if($tPonderacionPadre==1)
		{
			$consulta="SELECT porcentaje FROM 4565_porcentajeCriterioEvaluacionPerfil WHERE idCriterioEvaluacionMateria='".$codigoPadre."' AND idPerfil=".$idPerfil." and  bloque=".$bloque;
			$pCriterio=$con->obtenerValor($consulta);
			
			$calificacionFinal/=100;
			$calificacionFinal=$calificacionFinal*$pCriterio;
		}
		
		
		
		
		
		
		if($fCriterio[8]==1)//
			$calificacionFinal=truncarValor($calificacionFinal,$fCriterio[7]);
		else
			$calificacionFinal=str_replace(",","",number_format($calificacionFinal,$fCriterio[7]));

		
		$consulta="select * from 4594_calificacionesCriteriosAlumnoCambioEvaluacion WHERE idFormulario=".$idFormulario." and idReferencia=".$idRegistro." and idAlumno=".$idAlumno." AND idGrupo=".$idGrupo." AND idCriterio=".$codigoPadre." and bloque=".$bloque.
					" and  tipoEvaluacion=".$tipoEvaluacion." and noEvaluacion=".$noEvaluacion;	
		$fCal=$con->obtenerPrimeraFila($consulta);
		$idCalificacion=-1;
		if($fCal)
			$idCalificacion=$fCal[0];
		
		
		if($idCalificacion!=-1)
		{
			$consulta="UPDATE 4594_calificacionesCriteriosAlumnoCambioEvaluacion SET valorCambio=0,porcentajeObtenidoCambio=".$calificacionFinal.",porcentajeValorCambio=".$pCriterio.",totalConsiderarCambio=".$pCriterio." WHERE idCalificacion=".$idCalificacion;
		}
		else
		{
			$consulta="insert into 4594_calificacionesCriteriosAlumnoCambioEvaluacion (idAlumno,idGrupo,idCriterio,bloque,valor,porcentajeObtenido,porcentajeValor,totalConsiderar,tipoEvaluacion,noEvaluacion,idFormulario,
							idReferencia,valorCambio,porcentajeObtenidoCambio,porcentajeValorCambio,totalConsiderarCambio) 
						values(".$idAlumno.",".$idGrupo.",'".$codigoPadre."',".$bloque.",0,0,".$pCriterio.",".$pCriterio.",".$tipoEvaluacion.",".$noEvaluacion.",".$idFormulario.",".$idRegistro.",".$cal."
							,".$calificacion.",".$calificacionFinal.",".$pCriterio.",".$pCriterio.")";

		}
		
		if($con->ejecutarConsulta($consulta))
		{
			
			return recalcularCalificacionCriterioPadreCambioEvaluacion($idGrupo,$codigoPadre,$bloque,$idAlumno,$tipoEvaluacion,$noEvaluacion,$idFormulario,$idRegistro);

		}
	}
	
	function recalcularCalificacionBloqueCambioEvaluacion($idGrupo,$bloque,$idAlumno,$tipoEvaluacion,$noEvaluacion,$idFormulario,$idRegistro)
	{
		global $con;
		
		$query="SELECT idPlanEstudio,idInstanciaPlanEstudio,idMateria FROM 4520_grupos WHERE idGrupos=".$idGrupo;
		$fGrupo=$con->obtenerPrimeraFila($query);
		
		
		
		$idPlanEstudio=$fGrupo[0];
		$idInstanciaPlanEstudio=$fGrupo[1];
		$idMateria=$fGrupo[2];
		
		$consulta="SELECT idPerfil,calificacionMinimaAprobatoria FROM 4592_configuracionPerfilEvaluacion WHERE idPlanEstudio=".$idPlanEstudio." AND idInstanciaPlanEstudio=".$idInstanciaPlanEstudio." AND idMateria=".$idMateria." and
				idGrupo in (".$idGrupo.",-1) AND tipoExamen=".$tipoEvaluacion." AND noExamen=".$noEvaluacion." order by idGrupo desc";
		$fPerfil=$con->obtenerPrimeraFila($consulta);
		$idPerfil=$fPerfil[0];
		if($idPerfil=="")
			$idPerfil=-1;
		
		
		
		
		$query="SELECT calificacionMinimaAprobatoria FROM 4592_configuracionPerfilEvaluacion WHERE idPlanEstudio=".$fGrupo[0]." AND idMateria=".$fGrupo[2]." AND idInstanciaPlanEstudio IN (".$fGrupo[1].",-1) AND idGrupo IN (".$idGrupo.",-1)
				AND tipoExamen=".$tipoEvaluacion." AND noExamen=".$noEvaluacion." order by idGrupo desc,idInstanciaPlanEstudio desc";
		$calMinima=$con->obtenerValor($query);
		
		
		$consulta="SELECT idCriterio,porcentajeObtenidoCambio FROM 4594_calificacionesCriteriosAlumnoCambioEvaluacion WHERE idFormulario=".$idFormulario." and idReferencia=".$idRegistro." and idAlumno=".$idAlumno." AND idGrupo=".$idGrupo.
				"  AND bloque=".$bloque." and tipoEvaluacion=".$tipoEvaluacion." and noEvaluacion=".$noEvaluacion;	

		$res=$con->obtenerFilas($consulta);
		$total=0;
		$numHijos=0;
		while($fila=mysql_fetch_row($res))
		{
			if(strlen($fila[0])==14)
			{
				$total+=$fila[1];
				$numHijos++;
			}
		}
		
		$consulta="SELECT tipoPonderacion FROM 4591_perfilesEvaluacionMateria WHERE idPerfil=".$idPerfil;
		$tPonderacion=$con->obtenerValor($consulta);
		if($tPonderacion==2)
		{
			$total=$total/$numHijos;
		}
		
		$aprobado=0;
		if($calMinima<=($total/10))
			$aprobado=1;
		$consulta="SELECT idCalificacionBloque FROM 4595_calificacionesEvaluacionAlumnoCambioEvaluacion WHERE idFormulario=".$idFormulario." and idReferencia=".$idRegistro." and  idAlumno=".$idAlumno." AND idGrupo=".$idGrupo." AND bloque=".$bloque.
				" and tipoEvaluacion=".$tipoEvaluacion." and noEvaluacion=".$noEvaluacion;	

		$idCalificacionBloque=$con->obtenerValor($consulta);
		if($idCalificacionBloque=="")
			$consulta="INSERT INTO 4595_calificacionesEvaluacionAlumnoCambioEvaluacion(idAlumno,idGrupo,bloque,valor,tipoEvaluacion,noEvaluacion,aprobado,idFormulario,idReferencia,valorCambio,aprobadoCambio) 
						VALUES(".$idAlumno.",".$idGrupo.",".$bloque.",0,".$tipoEvaluacion.",".$noEvaluacion.",0,".$idFormulario.",".$idRegistro.",".$total.",".$aprobado.")";
		else
			$consulta="UPDATE 4595_calificacionesEvaluacionAlumnoCambioEvaluacion SET aprobadoCambio=".$aprobado.",valorCambio=".$total." WHERE idCalificacionBloque=".$idCalificacionBloque;
			
		return $con->ejecutarConsulta($consulta);
	}
	
	
	function asentarCambioEvaluacionGrupo($idFormulario,$idRegistro)
	{
		global $con;
		
		
		
		$query="SELECT idGrupo,noEvaluacion,tipoEvaluacion FROM _885_tablaDinamica WHERE id__885_tablaDinamica=".$idRegistro;
		$fRegistro=$con->obtenerPrimeraFila($query);
		
		
		$query="SELECT idAlumno FROM 4595_calificacionesEvaluacionAlumnoCambioEvaluacion WHERE 
						idFormulario=".$idFormulario." and idReferencia=".$idRegistro;
		$listAlumnos=$con->obtenerListaValores($query);				
		if($listAlumnos=="")
			$listAlumnos=-1;
		
		$x=0;
		$consulta[$x]="begin";
		$x++;
		$consulta[$x]="delete from 4568_calificacionesCriteriosAlumnoPerfilMateria where idGrupo=".$fRegistro[0]." and tipoEvaluacion=".$fRegistro[2]." and noEvaluacion=".$fRegistro[1]." and idAlumno in (".$listAlumnos.")";
		$x++;
		$consulta[$x]="INSERT INTO 4568_calificacionesCriteriosAlumnoPerfilMateria(idAlumno,idGrupo,idCriterio,bloque,valor,porcentajeObtenido,porcentajeValor,totalConsiderar,tipoEvaluacion,noEvaluacion)
						SELECT idAlumno,idGrupo,idCriterio,bloque,valorCambio,porcentajeObtenidoCambio,porcentajeValorCambio,totalConsiderarCambio,tipoEvaluacion,noEvaluacion
						FROM 4594_calificacionesCriteriosAlumnoCambioEvaluacion WHERE idFormulario=".$idFormulario." and idReferencia=".$idRegistro;

		$x++;
		$consulta[$x]="delete from 4569_calificacionesEvaluacionAlumnoPerfilMateria where idGrupo=".$fRegistro[0]." and tipoEvaluacion=".$fRegistro[2]." and noEvaluacion=".$fRegistro[1]." and idAlumno in (".$listAlumnos.")";
		$x++;
		
		$consulta[$x]="INSERT INTO  4569_calificacionesEvaluacionAlumnoPerfilMateria(idAlumno,idGrupo,bloque,valor,tipoEvaluacion,noEvaluacion,aprobado,idMateria)
						SELECT idAlumno,idGrupo,bloque,valorCambio,tipoEvaluacion,noEvaluacion,aprobadoCambio,idMateria FROM 4595_calificacionesEvaluacionAlumnoCambioEvaluacion WHERE 
						idFormulario=".$idFormulario." and idReferencia=".$idRegistro;
		$x++;
		
		$consulta[$x]="delete from 4571_valoresMaximosCriterioPerfilMateria where idGrupo=".$fRegistro[0]." and tipoEvaluacion=".$fRegistro[2]." and noEvaluacion=".$fRegistro[1];
		$x++;
		$consulta[$x]="INSERT INTO 4571_valoresMaximosCriterioPerfilMateria(idCriterio,idGrupo,noBloque,valMax,idAlumno,tipoEvaluacion,noEvaluacion)
					SELECT idCriterio,idGrupo,noBloque,valMaxCambio,idAlumno,tipoEvaluacion,noEvaluacion FROM 4596_valoresMaximosCriterioCambioEvaluacion WHERE 
					idFormulario=".$idFormulario." and idReferencia=".$idRegistro;
		$x++;
		
		
		if(esCalificacionFinal($fRegistro[2]))
		{
			$query="SELECT idAlumno,idGrupo,bloque,valorCambio,tipoEvaluacion,noEvaluacion,aprobadoCambio,idMateria FROM 4595_calificacionesEvaluacionAlumnoCambioEvaluacion WHERE 
						idFormulario=".$idFormulario." and idReferencia=".$idRegistro;
			$rAlumnos=$con->obtenerFilas($query);
			while($fAlumno=mysql_fetch_row($rAlumnos))
			{
				
				$query="SELECT idInstanciaPlan,idMateria FROM 4517_alumnosVsMateriaGrupo WHERE idUsuario=".$fAlumno[0]." and idGrupo=".$fRegistro[0];
				$fInscripcion=$con->obtenerPrimeraFila($query);
				
				$query="SELECT idAdeudoMateria FROM 4607_materiasAdeudoAlumno WHERE idAlumno=".$fAlumno[0]." AND idMateria=".$fInscripcion[1]." and idInstanciaPlan=".$fInscripcion[0];
				
				$idAdeudoMateria=$con->obtenerValor($query);
				if($idAdeudoMateria!="")
				{
					if($fAlumno[6]==1)
					{
						$consulta[$x]="UPDATE 4607_materiasAdeudoAlumno SET situacion=0 WHERE idAdeudoMateria=".$idAdeudoMateria;
						$x++;
					}	
					else
					{
						$consulta[$x]="UPDATE 4607_materiasAdeudoAlumno SET situacion=1 WHERE idAdeudoMateria=".$idAdeudoMateria;
						$x++;	
					}
				}
			}
		}
		
		$consulta[$x]="commit";
		$x++;
			
		return $con->ejecutarBloque($consulta);
	}
	
	function autorizarCambioFechaPago($idFormulario,$idRegistro)
	{
		global $con;
		$query="SELECT idReferencia,dteFechaExtension FROM _900_tablaDinamica WHERE id__900_tablaDinamica=".$idRegistro;
		$fRegistro=$con->obtenerPrimeraFila($query);
		$query="SELECT idRegistro FROM 964_registroDictamenes WHERE idFormulario=906 AND idReferencia=".$idRegistro." AND tipoDictamen='F' ORDER BY idDictamenes DESC ";
		$idRegistroDictamen=$con->obtenerValor($query);
		$query="SELECT pagoAplicar,cargoIncumplimiento,tipoCargo,valorCargo FROM _906_tablaDinamica WHERE id__906_tablaDinamica=".$idRegistroDictamen;
		$fDictamen=$con->obtenerPrimeraFila($query);

		$query="SELECT monto FROM 6012_asientosPago WHERE idAsientosPago=".$fDictamen[0];
		
		$monto=$con->obtenerValor($query);
		
		$query="select *   FROM 6012_asientosPago WHERE idReferenciaMovimiento=".$fRegistro[0];
		$arrFechas=$con->obtenerFilasJSON($query);
		$query="select fechaVencimiento from 6011_movimientosPago WHERE idMovimiento=".$fRegistro[0];
		$fVencimientoO=$con->obtenerValor($query);
		
		$objOriginal='{"fechaVencimiento":"'.$fVencimientoO.'","arrFechas:'.$arrFechas.'}';
		
		$x=0;
		$consulta[$x]="begin";
		$x++;
		$consulta[$x]="update _906_tablaDinamica set montosOriginales='".($objOriginal)."' where id__906_tablaDinamica=".$idRegistroDictamen;
		$x++;
		$consulta[$x]="update 6011_movimientosPago SET fechaVencimiento='".$fRegistro[1]."' WHERE idMovimiento=".$fRegistro[0];
		$x++;
		$consulta[$x]="delete   FROM 6012_asientosPago WHERE idReferenciaMovimiento=".$fRegistro[0];
		$x++;
		$consulta[$x]="INSERT INTO 6012_asientosPago(idReferenciaMovimiento,monto,fechaInicio,fechaFin) VALUES(".$fRegistro[0].",".$monto.",'".date("Y-m-d")."','".$fRegistro[1]."')";
		$x++;
		if($fDictamen[1]=="1")
		{
			$valor=$fDictamen[3];
			switch($fDictamen[2])	
			{
				case 1: //porcentaje
					$monto+=($monto* ($valor/100));
				break;
				case 2: //monto
					$monto+=$valor;
				break;
			}
			$consulta[$x]="INSERT INTO 6012_asientosPago(idReferenciaMovimiento,monto,fechaInicio,fechaFin) VALUES(".$fRegistro[0].",".$monto.",'".date("Y-m-d",strtotime("+1 days",strtotime($fRegistro[1])))."',NULL)";
			$x++;
		}
		
		$consulta[$x]="commit";
		$x++;
		
		return $con->ejecutarBloque($consulta);
	}
	
	
	function ocultarSeleccionPlanPagos($idProceso,$idFormulario,$idRegistro)
	{
		global $con;
		$consulta="SELECT idEstado FROM _910_tablaDinamica WHERE id__910_tablaDinamica=".$idRegistro;
		$idEstado=$con->obtenerValor($consulta);
		if($idEstado==4)	
			return false;
		return true;
		
	}
	
	function definirEtapaBajaAlumno($idFormulario,$idRegistro)
	{
		global $con;
		$idEtapa=0;
		$consulta="SELECT cmbTipoBaja FROM _715_tablaDinamica WHERE id__715_tablaDinamica=".$idRegistro;
		$tBaja=$con->obtenerValor($consulta);
		switch($tBaja)
		{
			case 2://baja definitiva
				$idEtapa=6;
			break;
			case 3://baja temporal
				$idEtapa=3;
			break;
		}
		cambiarEtapaFormulario($idFormulario,$idRegistro,$idEtapa);
	}
	
	function registrarBajaAlumno($idFormulario,$idRegistro)
	{
		global $con;
		$x=0;
		$query[$x]="begin";
		$x++;
		$consulta="SELECT cmbTipoBaja,cmbAlumnos FROM _715_tablaDinamica WHERE id__715_tablaDinamica=".$idRegistro;
		$fBaja=$con->obtenerPrimeraFila($consulta);
		$consulta="SELECT idOpcion FROM _715_chkInstanciaPlan WHERE idPadre=".$idRegistro;
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$consulta="SELECT idAlumnoTabla FROM 4529_alumnos WHERE idUsuario=".$fBaja[1]." AND idInstanciaPlanEstudio=".$fila[0]." ORDER BY idAlumnoTabla DESC LIMIT 0,1";
			$idAlumnoTabla=$con->obtenerValor($consulta);
			$query[$x]="UPDATE 4529_alumnos SET estado=3, tipoBaja=".$fBaja[0].",idFormularioBaja=".$idFormulario.",idRegistroBaja=".$idRegistro." WHERE idAlumnoTabla=".$idAlumnoTabla;
			$x++;
			$query[$x]="UPDATE  4537_situacionActualAlumno SET situacionAlumno=3 WHERE idAlumno=".$fBaja[1]." AND idInstanciaPlanEstudio=".$fila[0];
			$x++;
		}
		$consulta="SELECT idDocumento FROM 4598_evaluacionDocumentosInscripcion WHERE idFormulario=".$idFormulario." AND idReferencia=".$idRegistro." AND situacionDocumento=5";
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$query[$x]="UPDATE 825_documentosUsr SET situacion=4,idFormularioRef=".$idFormulario.",idRegistroRef=".$idRegistro." WHERE idUsuario=".$fBaja[1]." AND idDocumento=".$fila[0];
			$x++;
		}
		$query[$x]="commit";
		$x++;
		return $con->ejecutarBloque($query);
	}
	
	function verificarDictamen($idFormulario,$idRegistro)
	{
		global $con;
		$consulta="SELECT dictamenFinal,fechaEntrega,idReferencia FROM _929_tablaDinamica WHERE id__929_tablaDinamica=".$idRegistro;
		$fDictamen=$con->obtenerPrimeraFila($consulta);
		if($fDictamen[0]==2)
		{
			$consulta="UPDATE _925_tablaDinamica SET fechaLimite='".$fDictamen[1]."' WHERE id__925_tablaDinamica=".$fDictamen[2];
			return $con->ejecutarConsulta($consulta);
		}
		return true;
	}
	
	function registrarBajaAlumnoCondDocumentos($idFormulario,$idRegistro)
	{
		global $con;
		$consulta="SELECT cmbAlumno,instanciaPlanEstudio,codigoInstitucion FROM _925_tablaDinamica WHERE id__925_tablaDinamica=".$idRegistro;
		$fCond=$con->obtenerPrimeraFila($consulta);
		$x=0;
		$query[$x]="begin";
		$x++;
		$query[$x]="INSERT INTO _715_tablaDinamica(idReferencia,fechaCreacion,responsable,idEstado,codigoInstitucion,cmbAlumnos,cmbTipoBaja,motivoBaja)
					VALUES(".$idRegistro.",'".date("Y-m-d H:i:s")."',".$fCond[0].",2,'".$fCond[2]."',".$fCond[0].",2,7)";
		$x++;
		$query[$x]="set @idRegistro:=(select last_insert_id())";
		$x++;
		$query[$x]="INSERT INTO _715_chkInstanciaPlan(idPadre,idOpcion) VALUES(@idRegistro,".$fCond[1].")";
		$x++;
		$query[$x]="UPDATE _715_tablaDinamica SET codigo=@idRegistro WHERE id__715_tablaDinamica=@idRegistro";
		$x++;
		$query[$x]="commit";
		$x++;
		return $con->ejecutarBloque($query);


	}
	
	function registrarCumplimientoCondDocumentos($idFormulario,$idRegistro)
	{
		global $con;
		$consulta="SELECT cmbAlumno,instanciaPlanEstudio,codigoInstitucion FROM _925_tablaDinamica WHERE id__925_tablaDinamica=".$idRegistro;
		$fCond=$con->obtenerPrimeraFila($consulta);
		$x=0;
		$query[$x]="begin";
		$x++;
		$consulta="SELECT idDocumento,documentoDigital FROM _931_tablaDinamica t,_931_reporteDocumento r 
					WHERE r.idReferencia=t.id__931_tablaDinamica AND t.idReferencia=".$idRegistro." AND situacionDocumento=1";
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			if($fila[1]=="")
				$fila[1]="NULL";
			$consulta="SELECT idDocumentoUsr FROM 825_documentosUsr WHERE idUsuario=".$fCond[0]." AND idDocumento=".$fila[0];
			$idDocumentoUsr=$con->obtenerValor($consulta);
			if($idDocumentoUsr=="")
			{
				$query[$x]="INSERT INTO 825_documentosUsr(idUsuario,idDocumento,valorDocumento,idFormulario,idRegistro,fechaCreacion,situacion)
							VALUES(".$fCond[0].",".$fila[0].",".$fila[1].",".$idFormulario.",".$idRegistro.",'".date("Y-m-d")."',1)";
			}
			else
			{
				$query[$x]="UPDATE 825_documentosUsr SET valorDocumento=".$fila[1].",idFormulario=".$idFormulario.",idRegistro=".$idRegistro.",
							fechaCreacion='".date("Y-m-d")."',situacion=1 WHERE idDocumentoUsr=".$idDocumentoUsr;
			}
			$x++;
		}
		$query[$x]="commit";
		$x++;
		return $con->ejecutarBloque($query);
	}
	
	
	function generarPagoReferenciadoInscripcionMateria($idRegistro)
	{
		global $con;

		$idConcepto=32;
				
		$consulta="SELECT instanciaPlanEstudio,cmbAlumno,idCiclo,idPeriodo FROM _932_tablaDinamica WHERE id__932_tablaDinamica=".$idRegistro;
		$fBase=$con->obtenerPrimeraFila($consulta);
		$idInstanciaPlan=$fBase[0];
		$idCiclo=$fBase[2];
		$idPeriodo=$fBase[3];
		$consulta="SELECT p.idProgramaEducativo,i.sede FROM 4513_instanciaPlanEstudio i,4500_planEstudio p WHERE p.idPlanEstudio=i.idPlanEstudio AND i.idInstanciaPlanEstudio=".$idInstanciaPlan;
		$fInstancia=$con->obtenerPrimeraFila($consulta);
		
		$plantel=$fInstancia[1];
		
		$arrFechasPago=array();
		$idProgramaEducativo=$fInstancia[0];
		
		$idGradoInscribe=-1;
		
		$arrDimensiones=array();
		$arrDimensionesCosto["plantel"]=$plantel;
		$arrDimensionesCosto["idProgramaEducativo"]=$idProgramaEducativo;
		$arrDimensionesCosto["idInstanciaPlanEstudio"]=$idInstanciaPlan;
		$arrDimensionesCosto["grado"]=$idGradoInscribe;
		
		$oParametroCosto=array();
		$oParametroCosto["idUsuario"]=$fBase[1];
		$idPlanPagosAplica=-1;//Si -1 Aplica todos los planes
		$tabulador=generarTabuladorCostoServicio($idConcepto,$idCiclo,$idPeriodo,$arrDimensionesCosto,$oParametroCosto); 
		
		$arrDimensionesPagoReferenciado=array();
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IdInstanciaPlan",$idInstanciaPlan);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDCicloEscolar",$idCiclo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDPeriodo",$idPeriodo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDFormulario",932);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDRegistro",$idRegistro);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"Plantel",$plantel);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDProgramaEducativo",$idProgramaEducativo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDGrado",$idGradoInscribe);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDUsuario",$fBase[1]);

		$arrTabulador=generarFechasPagosServicio($idConcepto,$tabulador);
		generarPagosReferenciados($plantel,$fBase[1],$arrTabulador,$arrDimensionesPagoReferenciado,"pagoInscripcionMateriaRealizado",0);
		return true;
	}
	
	function pagoInscripcionMateriaRealizado($idMovimiento)
	{
		global $con;
		$consulta="SELECT valorCampo FROM 6012_detalleAsientoPago WHERE idAsientoPago=".$idMovimiento." AND idDimension=11";
		$idFormulario=$con->obtenerValor($consulta);
		$consulta="SELECT valorCampo FROM 6012_detalleAsientoPago WHERE idAsientoPago=".$idMovimiento." AND idDimension=12";
		$idRegistro=$con->obtenerValor($consulta);
		cambiarEtapaFormulario($idFormulario,$idRegistro,4);
	}
	
	
	function registrarInscripcionAlumnoMateria($idFormulario,$idRegistro)
	{
		global $con;
		$consulta="SELECT instanciaPlanEstudio,cmbAlumno,idCiclo,idPeriodo FROM _932_tablaDinamica WHERE id__932_tablaDinamica=".$idRegistro;
		$fBase=$con->obtenerPrimeraFila($consulta);
		$consulta="SELECT idGrupoInscribe FROM 4600_solicitudesInscripcionCurso WHERE idFormulario=".$idFormulario." AND idReferencia=".$idRegistro;
		$idGrupo=$con->obtenerValor($consulta);
		$consulta="SELECT idPlanEstudio,idMateria FROM 4520_grupos WHERE idGrupos=".$idGrupo;
		$fGrupos=$con->obtenerPrimeraFila($consulta);
		$consulta="INSERT INTO 4517_alumnosVsMateriaGrupo(idUsuario,situacion,idGrupo,idMateria,idPlanEstudio,fechaRegistro,condicionado,idCicloCondicionado,idPeriodoCondicionado,idInstanciaPlanEstudioCondicionado)
				VALUES(".$fBase[1].",1,".$idGrupo.",".$fGrupos[1].",".$fGrupos[0].",'".date("Y-m-d")."',1,".$fBase[2].",".$fBase[3].",".$fBase[0].")";
		return $con->ejecutarConsulta($consulta);


	}
	
	function registrarEnvioPlanPagosAutorizacion($idFormulario,$idRegistro)
	{
		global	$con;
	}
	
	function registrarCambioPlanPagos($idFormulario,$idRegistro)
	{
		global $con;
	/*	$consulta="SELECT idInstanciaPlan,idUsuarioRegistro FROM _933_tablaDinamica WHERE id__933_tablaDinamica=".$idRegistro;
		$fBase=$con->obtenerPrimeraFila($consulta);
		$cosulta="SELECT i.* FROM 564_conceptosVSCategorias c,561_conceptosIngreso i WHERE idCategoria=10 AND i.idConcepto=c.idConcepto";
		$listConceptos=$con->obtenerListaValores($cosulta);
		if($listConceptos=="")
			$listConceptos=-1;
			*/
		
		
		
		return generarPagoReferenciadoColegiaturaCambioPlan($idRegistro);
	}
	
	function generarPagoReferenciadoColegiaturaCambioPlan($idRegistro)
	{
		global $con;
		$idConcepto=14;
		$idFormulario=933;
		$consulta="SELECT idInstanciaPlan,idUsuarioRegistro FROM _933_tablaDinamica WHERE id__933_tablaDinamica=".$idRegistro;
		$fBase=$con->obtenerPrimeraFila($consulta);
		$idInstanciaPlan=$fBase[0];
		$consulta="SELECT idCiclo,idPeriodo,idGrado FROM 4529_alumnos WHERE idUsuario=".$fBase[1]." AND idInstanciaPlanEstudio=".$idInstanciaPlan." ORDER BY idAlumnoTabla desc";
		$fAlumno=$con->obtenerPrimeraFila($consulta);
		$idCiclo=$fAlumno[0];
		$idPeriodo=$fAlumno[1];
		$idGrado=$fAlumno[2];
		$consulta="SELECT p.idProgramaEducativo,i.sede FROM 4513_instanciaPlanEstudio i,4500_planEstudio p WHERE 
					p.idPlanEstudio=i.idPlanEstudio AND i.idInstanciaPlanEstudio=".$idInstanciaPlan;
		$fInstancia=$con->obtenerPrimeraFila($consulta);
		
		$plantel=$fInstancia[1];
		
		
		$idProgramaEducativo=$fInstancia[0];

		$arrDimensiones=array();
		$arrDimensionesCosto["plantel"]=$plantel;
		$arrDimensionesCosto["idProgramaEducativo"]=$idProgramaEducativo;
		$arrDimensionesCosto["idInstanciaPlanEstudio"]=$idInstanciaPlan;
		$arrDimensionesCosto["grado"]=$idGrado;
		
		$oParametroCosto=array();
		$oParametroCosto["idUsuario"]=$fBase[1];
		$idPlanPagosAplica=-1;//Si -1 Aplica todos los planes
		$tabulador=generarTabuladorCostoServicio($idConcepto,$idCiclo,$idPeriodo,$arrDimensionesCosto,$oParametroCosto); 
		
		$arrDimensionesPagoReferenciado=array();
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IdInstanciaPlan",$idInstanciaPlan);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDCicloEscolar",$idCiclo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDPeriodo",$idPeriodo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDGrado",$idGrado);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDUsuario",$fBase[1]);
		
		//echo $idInstanciaPlan."_".$idCiclo."_".$idPeriodo."_".$idGrado."_".$fBase[1]."<br>";
		$arrDimensionesConsulta["IdInstanciaPlan"]=$idInstanciaPlan;
		$arrDimensionesConsulta["IDCicloEscolar"]=$idCiclo;
		$arrDimensionesConsulta["IDPeriodo"]=$idPeriodo;
		$arrDimensionesConsulta["IDGrado"]=$idGrado;
		$arrDimensionesConsulta["IDUsuario"]=$fBase[1];
		
		
		
		$listReferencias=obtenerIdAsientoPagoReferenciado($arrDimensionesConsulta);
		
		
		if($listReferencias=="")
			$listReferencias=-1;
			
		$consulta="SELECT DISTINCT idConceptoAsociado FROM 564_conceptosVSPlanPago WHERE idConcepto=".$idConcepto;
		$listConceptos=	$con->obtenerListaValores($consulta);
		if($listConceptos=="")
			$listConceptos=-1;

		$consulta="UPDATE 6011_movimientosPago SET situacion=0 WHERE idMovimiento IN(".$listReferencias.") AND idConcepto IN(".$listConceptos.")";
		
		if($con->ejecutarConsulta($consulta))
		{
			
			agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDFormulario",$idFormulario);
			agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDRegistro",$idRegistro);
			agregarDimensionArreglo($arrDimensionesPagoReferenciado,"Plantel",$plantel);
			agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDProgramaEducativo",$idProgramaEducativo);
	
			$consulta="SELECT idPlanPago FROM 4581_planPagoAlumnoInscripcion WHERE idFormulario=".$idFormulario." AND idReferencia=".$idRegistro;
			$idPlanPagos=$con->obtenerValor($consulta);
			$arrTabulador=generarFechasPagosServicio($idConcepto,$tabulador,$idPlanPagos);
			
			generarPagosReferenciados($plantel,$fBase[1],$arrTabulador,$arrDimensionesPagoReferenciado,"",0);
			
			
			$consulta="UPDATE 4537_situacionActualAlumno SET idPlanPagos=".$idPlanPagos." WHERE idAlumno=".$fBase[1]." AND idInstanciaPlanEstudio=".$idInstanciaPlan;
			return $con->ejecutarConsulta($consulta);
			

		}
		return false;
	}	
		
	//rEINSCRIPCION
		
	function generarPagoReferenciadoReInscripcion($idRegistro)
	{
		global $con;

		$idConcepto=6;
				
		$consulta="SELECT idInstanciaPlan,idUsuarioRegistro,idCiclo,idPeriodo,tipoInscripcion FROM _910_tablaDinamica WHERE id__910_tablaDinamica=".$idRegistro;
		$fBase=$con->obtenerPrimeraFila($consulta);
		$idInstanciaPlan=$fBase[0];
		$idCiclo=$fBase[2];
		$idPeriodo=$fBase[3];
		$consulta="SELECT p.idProgramaEducativo,i.sede FROM 4513_instanciaPlanEstudio i,4500_planEstudio p WHERE p.idPlanEstudio=i.idPlanEstudio AND i.idInstanciaPlanEstudio=".$idInstanciaPlan;
		$fInstancia=$con->obtenerPrimeraFila($consulta);
		
		$plantel=$fInstancia[1];
		
		$arrFechasPago=array();
		$idProgramaEducativo=$fInstancia[0];
		
		$consulta="SELECT idGrado FROM 4529_alumnos WHERE idUsuario=".$fBase[1]." AND idInstanciaPlanEstudio=".$idInstanciaPlan." ORDER BY idAlumnoTabla DESC LIMIT 0,1";
		$idGradoActual=$con->obtenerValor($consulta);
		$consulta="SELECT idGradoSiguiente FROM 4501_Grado WHERE idGrado=".$idGradoActual;
		$idGrado=$con->obtenerValor($consulta);
		$idGradoInscribe="";
		
		if(($fBase[4]==1)||($fBase[4]==3))
			$idGradoInscribe=$idGrado;
		else
			$idGradoInscribe=$idGradoActual;
		
		$arrDimensiones=array();
		$arrDimensionesCosto["plantel"]=$plantel;
		$arrDimensionesCosto["idProgramaEducativo"]=$idProgramaEducativo;
		$arrDimensionesCosto["idInstanciaPlanEstudio"]=$idInstanciaPlan;
		$arrDimensionesCosto["grado"]=$idGradoInscribe;
		
		$oParametroCosto=array();
		$oParametroCosto["idUsuario"]=$fBase[1];
		$idPlanPagosAplica=-1;//Si -1 Aplica todos los planes
		$tabulador=generarTabuladorCostoServicio($idConcepto,$idCiclo,$idPeriodo,$arrDimensionesCosto,$oParametroCosto); 
		
		$arrDimensionesPagoReferenciado=array();
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IdInstanciaPlan",$idInstanciaPlan);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDCicloEscolar",$idCiclo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDPeriodo",$idPeriodo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDFormulario",910);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDRegistro",$idRegistro);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"Plantel",$plantel);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDProgramaEducativo",$idProgramaEducativo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDGrado",$idGradoInscribe);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDUsuario",$fBase[1]);

		$arrTabulador=generarFechasPagosServicio($idConcepto,$tabulador);
		generarPagosReferenciados($plantel,$fBase[1],$arrTabulador,$arrDimensionesPagoReferenciado,"pagoReInscripcionRealizado",0);
		return true;
	}

	function pagoReInscripcionRealizado($idMovimiento)
	{
		global $con;
		$consulta="SELECT valorCampo FROM 6012_detalleAsientoPago WHERE idAsientoPago=".$idMovimiento." AND idDimension=11";
		$idFormulario=$con->obtenerValor($consulta);
		$consulta="SELECT valorCampo FROM 6012_detalleAsientoPago WHERE idAsientoPago=".$idMovimiento." AND idDimension=12";
		$idRegistro=$con->obtenerValor($consulta);
		
		/*
		$consulta="SELECT idInstanciaPlan,idUsuarioRegistro FROM _910_tablaDinamica WHERE id__910_tablaDinamica=".$idRegistro;
		$fIncripcion=$con->obtenerPrimeraFila($consulta);
		$idInstancia=$fIncripcion[0];
		$idUsuario=$fIncripcion[1];
		$filaConf=obtenerConfiguracionPlanEstudio(392,"",$idInstancia);
		$consulta="SELECT documentos,d.txtDocumento,requerido,funcionAplicacion FROM _392_docVSplanesEstudio t,_391_tablaDinamica d 
						WHERE t.idReferencia=".$filaConf[0]." AND d.id__391_tablaDinamica=t.documentos ORDER BY txtDocumento";
		
		$res=$con->obtenerFilas($consulta);
		$numReg=0;
		while($fila=mysql_fetch_row($res))
		{
			$mostrarDocumento=false;
			$documentoRequerido=true;
			if($fila[2]==0)
			{
				$consulta="SELECT COUNT(*) FROM 825_documentosUsr WHERE idUsuario=".$idUsuario." AND idDocumento=".$fila[0];
				$nReg=$con->obtenerValor($consulta);
				if($nReg>0)
					$documentoRequerido=false;
			}
			
			if($documentoRequerido)
			{
				if($fila[3]=="")
				{
					$mostrarDocumento=true;
				}
				else
				{
					$arrParam["idFormulario"]=$idFormulario;
					$arrParam["idRegistro"]=$idRegistro;
					$arrParam["idUsuario"]=$idUsuario;
					$arrParam["idInstancia"]=$idInstancia;
					$arrParam["idDocumento"]=$fila[0];
					$cache=NULL;
					$cadObjParam='{"param1":null}';
					$objParam1=json_decode($cadObjParam);
					$objParam1->param1=$arrParam;
					$resultado=resolverExpresionCalculoPHP($fila[3],$objParam1,$cache);
					$resultado=removerComillasLimite($resultado);
					$mostrarDocumento=($resultado==1);
					
				}
			}
			
			if($mostrarDocumento)
			{
			
				
				$numReg++;
			}		
		}
		$numEtapa=0;
		
		if($numReg>0)
			$numEtapa=3;
		else*/
		$numEtapa=5;
			
		cambiarEtapaFormulario($idFormulario,$idRegistro,$numEtapa);
	}

	function finalizarReInscripcion($idRegistro)
	{
		global $con;
		
		$x=0;
		$query[$x]="begin";
		$x++;
		$consulta="SELECT idInstanciaPlan,idUsuarioRegistro,idCiclo,idPeriodo,tipoInscripcion,codigoInstitucion,codigoUnidad 
					FROM _910_tablaDinamica WHERE id__910_tablaDinamica=".$idRegistro;
		$fBase=$con->obtenerPrimeraFila($consulta);
		$idInstanciaPlan=$fBase[0];
		$idUsuario=$fBase[1];
		$idCiclo=$fBase[2];
		$idPeriodo=$fBase[3];
		$tipoInscripcion=$fBase[4];
		$codigoInstitucion=$fBase[5];
		$codigoUnidad=$fBase[6];
		
		$consulta="SELECT p.idProgramaEducativo,i.sede,p.idPlanEstudio FROM 4513_instanciaPlanEstudio i,4500_planEstudio p 
					WHERE p.idPlanEstudio=i.idPlanEstudio AND i.idInstanciaPlanEstudio=".$idInstanciaPlan;
		$fInstancia=$con->obtenerPrimeraFila($consulta);
		
		$plantel=$fInstancia[1];
		
		$arrFechasPago=array();
		$idProgramaEducativo=$fInstancia[0];
		$idPlanEstudio=$fInstancia[2];
		
		$consulta="SELECT idGrado FROM 4529_alumnos WHERE idUsuario=".$fBase[1]." AND idInstanciaPlanEstudio=".$idInstanciaPlan.
					" ORDER BY idAlumnoTabla DESC LIMIT 0,1";
		$idGradoActual=$con->obtenerValor($consulta);
		$consulta="SELECT idGradoSiguiente FROM 4501_Grado WHERE idGrado=".$idGradoActual;
		$idGrado=$con->obtenerValor($consulta);
		$idGradoInscribe="";
		$accionMateriaCursada=0;
		$noInscripcion=1;
		switch($tipoInscripcion)
		{
			case "1"://Inscripción al siguiente grado
				$idGradoInscribe=$idGrado;
			break;
			case "2"://Inscripción a mismo grado (Alumno repetidor)
				$idGradoInscribe=$idGradoActual;
				$noInscripcion++;
				$filaConf=obtenerConfiguracionPlanEstudio(472,$fInstancia[2],$idInstanciaPlan);
				if($filaConf[16]==1)
					$accionMateriaCursada=1;
			break;
			case "3"://Inscripción al siguiente grado condicionado
				$idGradoInscribe=$idGrado;
				$consulta="SELECT id__721_tablaDinamica FROM _721_tablaDinamica WHERE cmbCalificacionfinal=1";
				$listEvaluacionesFinales=$con->obtenerListaValores($consulta);
				$consulta="SELECT idCiclo,idPeriodo FROM 4529_alumnos WHERE idUsuario=".$idUsuario." AND idInstanciaPlanEstudio=".$idInstanciaPlan." AND idGrado=".$idGradoActual."  ORDER BY idAlumnoTabla DESC LIMIT 0,1";
				
				$fPeriodo=$con->obtenerPrimeraFila($consulta);
				$consulta="SELECT DISTINCT idGrupo,g.idMateria FROM 4517_alumnosVsMateriaGrupo a,4520_grupos g WHERE a.idUsuario=".$idUsuario." AND  g.idGrupos=a.idGrupo AND
						((g.idCiclo=".$fPeriodo[0]." AND g.idPeriodo=".$fPeriodo[1]." AND idInstanciaPlanEstudio=".$idInstanciaPlan.") OR 
						(a.idCicloCondicionado=".$fPeriodo[0]." AND a.idPeriodoCondicionado=".$fPeriodo[1]." AND a.idInstanciaPlanEstudioCondicionado=".$idInstanciaPlan."))";
						
				$resGrupos=$con->obtenerFilas($consulta);
				while($fGpo=mysql_fetch_row($resGrupos))
				{
					$consulta="SELECT aprobado FROM 4569_calificacionesEvaluacionAlumnoPerfilMateria WHERE idAlumno=".$idUsuario." AND idGrupo=".$fGpo[0]." and tipoEvaluacion in (".$listEvaluacionesFinales.") ORDER BY idCalificacionBloque desc";
					$aprobado=$con->obtenerValor($consulta);
					if($aprobado!=1)
					{
						
						$query[$x]="INSERT INTO _932_tablaDinamica(idReferencia,fechaCreacion,responsable,idEstado,codigoInstitucion,codigoUnidad,codigo,cmbAlumno,instanciaPlanEstudio,materia,idCiclo,idPeriodo)
								values(".$idRegistro.",'".date("Y-m-d H:i:s")."',".$idUsuario.",2,'".$codigoInstitucion."','".$codigoUnidad."','@folio',".$idUsuario.",".$idInstanciaPlan.",".$fGpo[1].",".$idCiclo.",".$idPeriodo.")";
						$x++;
					}
				}
				
			break;
		}
		
		$arrDocumentos=array();
		$consulta="SELECT * FROM 4598_evaluacionDocumentosInscripcion WHERE idFormulario=910 AND idReferencia=".$idRegistro." AND situacionDocumento=2";
		$resDoc=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($resDoc))
		{
			if(!isset($arrDocumentos[$fila[6]]))
			{
				$arrDocumentos[$fila[6]]=array();
			}
			$oDoc[0]=$fila[3];
			$oDoc[1]=$fila[7];
			array_push($arrDocumentos[$fila[6]],$oDoc);
		}
		if(sizeof($arrDocumentos)>0)
		{
			foreach($arrDocumentos as $fecha=>$resto)
			{
				
				$query[$x]="INSERT INTO _925_tablaDinamica(idReferencia,fechaCreacion,responsable,idEstado,codigoInstitucion,codigoUnidad,cmbAlumno,instanciaPlanEstudio,fechaLimite,codigo)
							VALUES(".$idRegistro.",'".date("Y-m-d H:i:s")."',".$idUsuario.",2,'".$codigoInstitucion."','".$codigoUnidad."',".$idUsuario.",".$idInstanciaPlan.",'".$fecha."','@folio')";
				$x++;
				$query[$x]="set @idRegistro:=(select last_insert_id())";
				$x++;
				$query[$x]="INSERT INTO _928_tablaDinamica(idReferencia,fechaCreacion,responsable,idEstado,codigoInstitucion,codigoUnidad)
						VALUES(@idRegistro,'".date("Y-m-d H:i:s")."',".$idUsuario.",1,'".$codigoInstitucion."','".$codigoUnidad."')";
				$x++;
				$query[$x]="set @idRegistroDoc:=(select last_insert_id())";
				$x++;
				foreach($resto as $oDoc)
				{
					$query[$x]="INSERT INTO _928_gridDocumentosCondicionados(idReferencia,documento,comentarios)
								VALUES(@idRegistroDoc,".$oDoc[0].",'".cv($oDoc[1])."')";
					$x++;
				}
			}
		}
		$query[$x]="commit";
		$x++;	
		$consulta="SELECT idPlanPago FROM 4581_planPagoAlumnoInscripcion WHERE idFormulario=910 AND idReferencia=".$idRegistro;
		$idPlanPagos=$con->obtenerValor($consulta);
		
		if(generarPagoReferenciadoColegiaturaReincripcion($idRegistro))
		{
			$alumnoInscrito=false;
			switch($tipoInscripcion)
			{
				case 1:
					$alumnoInscrito=inscribirAlumnoPlanRigido($fBase[1],$idGradoInscribe,$idCiclo,$idInstanciaPlan,$idPeriodo,"",$idPlanPagos,$accionMateriaCursada);
				break;
				case 2:
				case 3:
					$alumnoInscrito=inscribirAlumnoOfertaAcademica($idRegistro,$fBase[1],$idInstanciaPlan,$idPlanPagos,$idCiclo,$idPeriodo,$idGradoInscribe,$plantel,$noInscripcion);	
				break;
			}
			if($alumnoInscrito)
			{
				if($con->ejecutarBloque($query))
				{
					
					$consulta="select id__925_tablaDinamica from _925_tablaDinamica where codigo='@folio'";
					$res=$con->obtenerFilas($consulta);
					while($fila=mysql_fetch_row($res))
					{
						asignarFolioRegistro(925,$fila[0]);
					}
					$consulta="select id__932_tablaDinamica from _932_tablaDinamica where codigo='@folio'";
					$res=$con->obtenerFilas($consulta);
					while($fila=mysql_fetch_row($res))
					{
						asignarFolioRegistro(932,$fila[0]);
					}
					$consulta="SELECT idGrupo,idMateria FROM 4517_alumnosVsMateriaGrupo WHERE idUsuario=".$idUsuario." AND idCiclo=".$idCiclo." AND idPeriodo=".$idPeriodo;
					$resGrupo=$con->obtenerFilas($consulta);
					while($fGrupo=mysql_fetch_row($resGrupo))
					{
						
						$consulta="SELECT id__932_tablaDinamica FROM _932_tablaDinamica WHERE idEstado=2 AND cmbAlumno=".$idUsuario." AND materia=".$fGrupo[1];
						$idRegistro=$con->obtenerValor($consulta);
						if($idRegistro!="")
						{
							$consulta="INSERT INTO 4600_solicitudesInscripcionCurso(idFormulario,idReferencia,idGrupoInscribe) VALUES(932,".$idRegistro.",".$fGrupo[0].")";
							$con->ejecutarConsulta($consulta);
							$consulta="UPDATE _932_tablaDinamica SET idEstado=4 WHERE id__932_tablaDinamica=".$idRegistro;
							$con->ejecutarConsulta($consulta);
							
							
						}
					}
					
					
					
					
					return true;
				}
				
			}
		}
		return false;
		
	}
	
	function generarPagoReferenciadoColegiaturaReincripcion($idRegistro)
	{
		global $con;
		$idConcepto=14;
		$idFormulario=910;
		
		$consulta="SELECT idInstanciaPlan,idUsuarioRegistro,idCiclo,idPeriodo,tipoInscripcion FROM _910_tablaDinamica WHERE id__910_tablaDinamica=".$idRegistro;
		$fBase=$con->obtenerPrimeraFila($consulta);
		
		$idUsuario=$fBase[1];
		$idInstanciaPlan=$fBase[0];
		$idCiclo=$fBase[2];
		$idPeriodo=$fBase[3];
		
		
		$consulta="SELECT p.idProgramaEducativo,i.sede,p.idPlanEstudio FROM 4513_instanciaPlanEstudio i,4500_planEstudio p WHERE p.idPlanEstudio=i.idPlanEstudio AND i.idInstanciaPlanEstudio=".$idInstanciaPlan;
		$fInstancia=$con->obtenerPrimeraFila($consulta);
		
		$plantel=$fInstancia[1];
		
		$arrFechasPago=array();
		$idProgramaEducativo=$fInstancia[0];
		
		
		$arrFechasPago=array();

		$consulta="SELECT idGrado FROM 4529_alumnos WHERE idUsuario=".$fBase[1]." AND idInstanciaPlanEstudio=".$idInstanciaPlan." ORDER BY idAlumnoTabla DESC LIMIT 0,1";
		$idGradoActual=$con->obtenerValor($consulta);
		if($idGradoActual=="")
			$idGradoActual=-1;
		$consulta="SELECT idGradoSiguiente FROM 4501_Grado WHERE idGrado=".$idGradoActual;
		$idGrado=$con->obtenerValor($consulta);
		$idGradoInscribe="";
		
		if(($fBase[4]==1)||($fBase[4]==3))
			$idGradoInscribe=$idGrado;
		else
			$idGradoInscribe=$idGradoActual;


		$arrDimensiones=array();
		$arrDimensionesCosto["plantel"]=$plantel;
		$arrDimensionesCosto["idProgramaEducativo"]=$idProgramaEducativo;
		$arrDimensionesCosto["idInstanciaPlanEstudio"]=$idInstanciaPlan;
		$arrDimensionesCosto["grado"]=$idGradoInscribe;
		
		$oParametroCosto=array();
		$oParametroCosto["idUsuario"]=$idUsuario;
		$consulta="SELECT costoCarga FROM 4608_ofertaCargasAcademicas WHERE idFormulario=".$idFormulario." AND idReferencia=".$idRegistro." AND cargaSeleccionada=1";
		$costoCarga=$con->obtenerValor($consulta);
		if($costoCarga!="")
		{
			$tabulador=generarTabuladorCostoServicio($idConcepto,$idCiclo,$idPeriodo,$arrDimensionesCosto,$oParametroCosto); 
			$costoColegiatura=$tabulador["montoConcepto"];
			switch($costoCarga)
			{
				case 1:
					$costoBase=$costoColegiatura;
				break;
				case 2:
					$costoBase=$costoColegiatura*0.5;
				break;
				case 3:
					$costoBase=$costoColegiatura*1.5;
				break;	
			}
			$oParametroCosto["costoBase"]=$costoBase;
		}
		
		
		$idPlanPagosAplica=-1;//Si -1 Aplica todos los planes
		$tabulador=generarTabuladorCostoServicio($idConcepto,$idCiclo,$idPeriodo,$arrDimensionesCosto,$oParametroCosto); 
		
		$arrDimensionesPagoReferenciado=array();
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IdInstanciaPlan",$idInstanciaPlan);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDCicloEscolar",$idCiclo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDPeriodo",$idPeriodo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDFormulario",$idFormulario);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDRegistro",$idRegistro);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"Plantel",$plantel);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDProgramaEducativo",$idProgramaEducativo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDGrado",$idGrado);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDUsuario",$idUsuario);
		$consulta="SELECT idPlanPago FROM 4581_planPagoAlumnoInscripcion WHERE idFormulario=".$idFormulario." AND idReferencia=".$idRegistro;
		$idPlanPagos=$con->obtenerValor($consulta);
		
		
		$arrTabulador=generarFechasPagosServicio($idConcepto,$tabulador,$idPlanPagos);
		
		generarPagosReferenciados($plantel,$idUsuario,$arrTabulador,$arrDimensionesPagoReferenciado,"",0);
		return true;
	}	
	
	
	
	function verificarSituacionInscripcionValidacionDocumento($idFormulario,$idRegistro)
	{
		global $con;
		$consulta="SELECT dictamenEvaluacion FROM 4599_dictamenEvaluacionDocumentosInscripcion WHERE idFormulario=".$idFormulario." AND idReferencia=".$idRegistro;
		$dictamenEvaluacion=$con->obtenerValor($consulta);
		$numEtapa=0;
		switch($dictamenEvaluacion)
		{
			case 1:
				if($idFormulario==910)
					$numEtapa=7;
				else
					$numEtapa=5;
			break;
			case 2:
			case 3:
				if($idFormulario==910)
					$numEtapa=4;
				else
					$numEtapa=4;
			break;
		}
		cambiarEtapaFormulario($idFormulario,$idRegistro,$numEtapa);
	}
	
	
	function mostrarValidacionDocumentosReinscripcion($idRegistro)
	{
		global $con;	
		$idFormulario=910;

		$consulta="SELECT COUNT(*) FROM 4599_dictamenEvaluacionDocumentosInscripcion WHERE idFormulario=".$idFormulario." AND idReferencia=".$idRegistro;
		$nRegDocumento=$con->obtenerValor($consulta);
		if($nRegDocumento==0)
		{
			$numReg=obtenerNumDocumentosFaltantesReinscripcion($idRegistro);	
			if($numReg>0)
				return 1;
			return 0;

						
		}
		else
			return 1;
	}
	
	
	function obtenerNumDocumentosFaltantesReinscripcion($idRegistro)
	{
		global $con;
		$consulta="SELECT idInstanciaPlan,idUsuarioRegistro FROM _910_tablaDinamica WHERE id__910_tablaDinamica=".$idRegistro;
		$fIncripcion=$con->obtenerPrimeraFila($consulta);
		$idInstancia=$fIncripcion[0];
		$idUsuario=$fIncripcion[1];
		$filaConf=obtenerConfiguracionPlanEstudio(392,"",$idInstancia);
		$consulta="SELECT documentos,d.txtDocumento,requerido,funcionAplicacion FROM _392_docVSplanesEstudio t,_391_tablaDinamica d 
						WHERE t.idReferencia=".$filaConf[0]." AND d.id__391_tablaDinamica=t.documentos ORDER BY txtDocumento";
			
		  $res=$con->obtenerFilas($consulta);
		  $numReg=0;
		  while($fila=mysql_fetch_row($res))
		  {
			  $mostrarDocumento=false;
			  $documentoRequerido=true;
			  if($fila[2]==0)
			  {
				  $consulta="SELECT COUNT(*) FROM 825_documentosUsr WHERE idUsuario=".$idUsuario." AND idDocumento=".$fila[0];
				  $nReg=$con->obtenerValor($consulta);
				  if($nReg>0)
					  $documentoRequerido=false;
			  }
			  
			  if($documentoRequerido)
			  {
				  if($fila[3]=="")
				  {
					  $mostrarDocumento=true;
				  }
				  else
				  {
					  $arrParam["idFormulario"]=$idFormulario;
					  $arrParam["idRegistro"]=$idRegistro;
					  $arrParam["idUsuario"]=$idUsuario;
					  $arrParam["idInstancia"]=$idInstancia;
					  $arrParam["idDocumento"]=$fila[0];
					  $cache=NULL;
					  $cadObjParam='{"param1":null}';
					  $objParam1=json_decode($cadObjParam);
					  $objParam1->param1=$arrParam;
					  $resultado=resolverExpresionCalculoPHP($fila[3],$objParam1,$cache);
					  $resultado=removerComillasLimite($resultado);
					  $mostrarDocumento=($resultado==1);
					  
				  }
			  }
			  
			  if($mostrarDocumento)
			  {
				  $numReg++;
			  }
		  }
		  return $numReg;
			
	}
	
	function esReinscripcionCondicionada($idRegistro)
	{
		global $con;
		$consulta="SELECT tipoInscripcion FROM _910_tablaDinamica WHERE id__910_tablaDinamica=".$idRegistro;
		$tInscripcion=$con->obtenerValor($consulta);
		if($tInscripcion==3)
			return 1;
		return 0;
		
	}
	
	function mostrarSeleccionFormaPagoReinscripcion($idRegistro)
	{
		global $con;
		$consulta="SELECT idEstado FROM _910_tablaDinamica WHERE id__910_tablaDinamica=".$idRegistro;
		$idEstado=$con->obtenerValor($consulta);
		if((esReinscripcionCondicionada($idRegistro)==0)||($idEstado==4)||($idEstado==1.6))
		{
			return 1;	
		}
		
		return 0;
		
	}
	
	function seleccionEtapaAlumnoReInscripcion($idRegistro)
	{
		global $con;
		$idEtapa=2;
		if(esReinscripcionCondicionada($idRegistro)==0)
		{
			$numDocumentos=obtenerNumDocumentosFaltantesReinscripcion($idRegistro);
			if($numDocumentos>0)
				$idEtapa=3;
		}
		else
		{
			$idEtapa=1.5;
		}
		cambiarEtapaFormulario(910,$idRegistro,$idEtapa);
	}
	
	
	function mostrarEnviarOfertarCarga($idFormulario,$idRegistro,$actor,$etiqueta)
	{
		global $con;
		
		$consulta="SELECT idEstado FROM _910_tablaDinamica WHERE id__910_tablaDinamica=".$idRegistro;
		$idEstado=$con->obtenerValor($consulta);
		if($idEstado==1.5)
		{
			$consulta="SELECT COUNT(*) FROM 4608_ofertaCargasAcademicas WHERE idFormulario=".$idFormulario." AND idReferencia=".$idRegistro;
			$nReg=$con->obtenerValor($consulta);		
			if($nReg>0)
				return $etiqueta;
		}
		return "";
	}
	
	function mostrarEnviarPagoReinscripcion($idFormulario,$idRegistro,$actor,$etiqueta)
	{
		global $con;
		
		$consulta="SELECT idEstado FROM _910_tablaDinamica WHERE id__910_tablaDinamica=".$idRegistro;
		$idEstado=$con->obtenerValor($consulta);
		
		$consulta="SELECT dictamenEvaluacion FROM 4599_dictamenEvaluacionDocumentosInscripcion WHERE idFormulario=".$idFormulario." AND idReferencia=".$idRegistro." order by idEvaluacionDocumentos desc";

		$dictamenEvaluacion=$con->obtenerValor($consulta);
		if($dictamenEvaluacion!="1")
		{
			$consulta="SELECT COUNT(*) FROM 4610_cargaAcademicaSeleccionada WHERE idFormulario=".$idFormulario." AND idReferencia=".$idRegistro;
			$nReg=$con->obtenerValor($consulta);		
			if($nReg>0)
			{
				if($idEstado!=1.6)
					return $etiqueta;
				else
				{
					$consulta="SELECT COUNT(*) FROM 4581_planPagoAlumnoInscripcion WHERE idFormulario=".$idFormulario." AND idReferencia=".$idRegistro;	
					$nReg=$con->obtenerValor($consulta);		
					if($nReg>0)
						return $etiqueta;
					return "";
						
				}
			}
		}
		return "";	
	}
	
	function mostrarEnviarFinalReinscripcion($idFormulario,$idRegistro,$actor,$etiqueta)
	{
		
		global $con;
		$consulta="SELECT dictamenEvaluacion FROM 4599_dictamenEvaluacionDocumentosInscripcion WHERE idFormulario=".$idFormulario." AND idReferencia=".$idRegistro." order by idEvaluacionDocumentos desc";
		$dictamenEvaluacion=$con->obtenerValor($consulta);

		if($dictamenEvaluacion==1)
			return $etiqueta;
		return "";	
	}
	
	//Insrcipcion
	function generarPagoReferenciadoRevalidacion($idRegistro)
	{
		global $con;

		$idConcepto=17;
		$consulta="SELECT idReferencia,codigoInstitucion,solicitaRevalidacion FROM _678_tablaDinamica WHERE id__678_tablaDinamica=".$idRegistro;

		$fSolicitud=$con->obtenerPrimeraFila($consulta);
		$idReferencia=$fSolicitud[0];
		$plantel=$fSolicitud[1];
		
		$consulta="SELECT * FROM 4573_solicitudesInscripcion WHERE idSolicitudInscripcion=".$idReferencia;
		$fRegistro=$con->obtenerPrimeraFila($consulta);
		
		$objInscripcion=json_decode($fRegistro[3]);
		$objDatos=array();
		$arrFechasPago=array();
		$idUsuario=$fRegistro[2];
		$consulta="SELECT p.idProgramaEducativo FROM 4513_instanciaPlanEstudio i,4500_planEstudio p WHERE p.idPlanEstudio=i.idPlanEstudio AND i.idInstanciaPlanEstudio=".$objInscripcion->idInstanciaPlan;
		$idProgramaEducativo=$con->obtenerValor($consulta);
		
		$consulta="SELECT g.idGrado FROM 4505_estructuraCurricular e,4500_planEstudio p,4513_instanciaPlanEstudio i,4501_Grado g WHERE i.idInstanciaPlanEstudio=".$objInscripcion->idInstanciaPlan." AND  
					i.idPlanEstudio=p.idPlanEstudio AND e.idPlanEstudio=p.idPlanEstudio AND tipoUnidad=3 AND g.idGrado=e.idUnidad ORDER BY g.ordenGrado LIMIT 0,1";
		$idGrado=$con->obtenerValor($consulta);
		
		/*if($fSolicitud[2]=="0")
		{
			$consulta="SELECT g.idGrado FROM 4505_estructuraCurricular e,4500_planEstudio p,4513_instanciaPlanEstudio i,4501_Grado g WHERE i.idInstanciaPlanEstudio=".$objInscripcion->idInstanciaPlan." AND  
					i.idPlanEstudio=p.idPlanEstudio AND e.idPlanEstudio=p.idPlanEstudio AND tipoUnidad=3 AND g.idGrado=e.idUnidad ORDER BY g.ordenGrado LIMIT 0,1";
			$idGrado=$con->obtenerValor($consulta);
		}
		else
		{
			$consulta="SELECT id__685_tablaDinamica FROM _685_tablaDinamica WHERE idReferencia=".$idRegistro;
			$idRefAux=$con->obtenerValor($consulta);
			$consulta="SELECT gradoInscribe FROM 4574_solicitudesRevalidacion WHERE idReferencia=".$idRefAux;
			$idGrado=$con->obtenerValor($consulta);
		}*/
		
		//Caculo costo
		$arrDimensiones=array();
		$arrDimensionesCosto["plantel"]=$plantel;
		$arrDimensionesCosto["idProgramaEducativo"]=$idProgramaEducativo;
		$arrDimensionesCosto["idInstanciaPlanEstudio"]=$objInscripcion->idInstanciaPlan;
		$arrDimensionesCosto["grado"]=$idGrado;
		
		$oParametroCosto=array();
		$oParametroCosto["idUsuario"]=$idUsuario;
		$idPlanPagosAplica=-1;//Si -1 Aplica todos los planes
		$tabulador=generarTabuladorCostoServicio($idConcepto,$objInscripcion->idCiclo,$objInscripcion->idPeriodo,$arrDimensionesCosto,$oParametroCosto); 
		
		$arrDimensionesPagoReferenciado=array();
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IdInstanciaPlan",$objInscripcion->idInstanciaPlan);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDCicloEscolar",$objInscripcion->idCiclo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDPeriodo",$objInscripcion->idPeriodo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDFormulario",678);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDRegistro",$idRegistro);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"Plantel",$plantel);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDProgramaEducativo",$idProgramaEducativo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDGrado",$idGrado);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDUsuario",$idUsuario);

		$arrTabulador=generarFechasPagosServicio($idConcepto,$tabulador);
		
		return generarPagosReferenciados($plantel,$idUsuario,$arrTabulador,$arrDimensionesPagoReferenciado,"pagoRevalidacionRealizado",0);
		
		//--Termina
		return false;
	}
		
	function generarSolicitudRevalidacion($idRegistro)
	{
		global $con;
		$consulta="SELECT idReferencia,codigoInstitucion FROM _678_tablaDinamica WHERE id__678_tablaDinamica=".$idRegistro;
		$fSolicitud=$con->obtenerPrimeraFila($consulta);
		$idReferencia=$fSolicitud[0];
		$plantel=$fSolicitud[1];
		$consulta="SELECT * FROM 4573_solicitudesInscripcion WHERE idSolicitudInscripcion=".$idReferencia;
		$fRegistro=$con->obtenerPrimeraFila($consulta);
		$idUsuario=$fRegistro[2];
		$objInscripcion=json_decode($fRegistro[3]);
		$consulta="SELECT nombreEscuela,planEstudios FROM _708_tablaDinamica WHERE idReferencia=".$idRegistro;
		$fDatosOrigen=$con->obtenerPrimeraFila($consulta);
		$idOrigen=$fDatosOrigen[0];
		$idPlanEstudioOrigen=$fDatosOrigen[1];
		$consulta="SELECT idReferencia FROM (
					SELECT idReferencia,COUNT(*) AS nRegistros FROM 6011_movimientosPago m,6012_detalleAsientoPago d WHERE idUsuario=".$idUsuario." AND idConcepto=17 AND d.idAsientoPago=m.idMovimiento 
					AND ((idDimension=11 AND valorCampo=678)||(idDimension=12 AND valorCampo=".$idRegistro."))) AS tm WHERE nRegistros=2";
					
		$referencia=$con->obtenerValor($consulta);
		$idPrograma=obtenerIdProgramaEducativoInstancia($objInscripcion->idInstanciaPlan);
		$query="INSERT INTO _685_tablaDinamica(idReferencia,fechaCreacion,responsable,idEstado,codigoInstitucion,codigo,origenSolicitante,programaEducativoDestino,
				plantel,planEstudioEscuelaOrigen,planEstudioPlantelOrigen,escuelaOrigen,instanciaPlanEstudioDestino,usuarioSolicitud,cmbCicloEscolar,periodo)
				VALUES(".$idRegistro.",'".date("Y-m-d")."',".$_SESSION["idUsr"].",2,'".$plantel."','".$referencia."',2,'".$idPrograma."','','".$idPlanEstudioOrigen."','','".$idOrigen."','".$objInscripcion->idInstanciaPlan."','".
				$idUsuario."','".$objInscripcion->idCiclo."','".$objInscripcion->idPeriodo."')";

		return $con->ejecutarConsulta($query);
		
	}

	function mostrarSolicitudRevalidacion($idProceso,$idFormulario,$idRegistro)
	{
		global $con;
		$consulta="SELECT idEstado,solicitaRevalidacion FROM _678_tablaDinamica WHERE id__678_tablaDinamica=".$idRegistro;
		$fRegistro=$con->obtenerPrimeraFila($consulta);
		if(($fRegistro[0]>=1.3)&&($fRegistro[1]==1))
			return false;
		return true;
	}
	
	function generarPagoReferenciadoInscripcion($idRegistro)
	{
		global $con;

		$idConcepto=6;
				
		$consulta="SELECT idReferencia,codigoInstitucion,solicitaRevalidacion FROM _678_tablaDinamica WHERE id__678_tablaDinamica=".$idRegistro;

		$fSolicitud=$con->obtenerPrimeraFila($consulta);
		$idReferencia=$fSolicitud[0];
		$plantel=$fSolicitud[1];
		
		$consulta="SELECT * FROM 4573_solicitudesInscripcion WHERE idSolicitudInscripcion=".$idReferencia;
		$fRegistro=$con->obtenerPrimeraFila($consulta);
		$objInscripcion=json_decode($fRegistro[3]);
		$objDatos=array();
		$arrFechasPago=array();
		
		$consulta="SELECT p.idProgramaEducativo FROM 4513_instanciaPlanEstudio i,4500_planEstudio p WHERE p.idPlanEstudio=i.idPlanEstudio AND i.idInstanciaPlanEstudio=".$objInscripcion->idInstanciaPlan;
		$idProgramaEducativo=$con->obtenerValor($consulta);
		
		
		
		if($fSolicitud[2]=="0")
		{
			$consulta="SELECT g.idGrado FROM 4505_estructuraCurricular e,4500_planEstudio p,4513_instanciaPlanEstudio i,4501_Grado g WHERE i.idInstanciaPlanEstudio=".$objInscripcion->idInstanciaPlan." AND  
					i.idPlanEstudio=p.idPlanEstudio AND e.idPlanEstudio=p.idPlanEstudio AND tipoUnidad=3 AND g.idGrado=e.idUnidad ORDER BY g.ordenGrado LIMIT 0,1";
			$idGrado=$con->obtenerValor($consulta);
		}
		else
		{
			$consulta="SELECT id__685_tablaDinamica FROM _685_tablaDinamica WHERE idReferencia=".$idRegistro;
			$idRefAux=$con->obtenerValor($consulta);
			$consulta="SELECT gradoInscribe FROM 4574_solicitudesRevalidacion WHERE idReferencia=".$idRefAux;
			$idGrado=$con->obtenerValor($consulta);
		}

		$arrDimensiones=array();
		$arrDimensionesCosto["plantel"]=$plantel;
		$arrDimensionesCosto["idProgramaEducativo"]=$idProgramaEducativo;
		$arrDimensionesCosto["idInstanciaPlanEstudio"]=$objInscripcion->idInstanciaPlan;
		$arrDimensionesCosto["grado"]=$idGrado;
		
		$oParametroCosto=array();
		$oParametroCosto["idUsuario"]=$fRegistro[2];
		$idPlanPagosAplica=-1;//Si -1 Aplica todos los planes
		$tabulador=generarTabuladorCostoServicio($idConcepto,$objInscripcion->idCiclo,$objInscripcion->idPeriodo,$arrDimensionesCosto,$oParametroCosto); 
		
		$arrDimensionesPagoReferenciado=array();
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IdInstanciaPlan",$objInscripcion->idInstanciaPlan);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDCicloEscolar",$objInscripcion->idCiclo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDPeriodo",$objInscripcion->idPeriodo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDFormulario",678);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDRegistro",$idRegistro);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"Plantel",$plantel);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDProgramaEducativo",$idProgramaEducativo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDGrado",$idGrado);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDUsuario",$idRegistro);

		$arrTabulador=generarFechasPagosServicio($idConcepto,$tabulador);
		generarPagosReferenciados($plantel,$fRegistro[2],$arrTabulador,$arrDimensionesPagoReferenciado,"pagoInscripcionRealizado",0);
		return true;
	}
	
	function generarPagoReferenciadoColegiatura($idRegistro)
	{
		global $con;
		$idConcepto=14;
		
		$consulta="SELECT idReferencia,codigoInstitucion,'0',idGradoInscribe FROM _678_tablaDinamica WHERE id__678_tablaDinamica=".$idRegistro;

		$fSolicitud=$con->obtenerPrimeraFila($consulta);
		$idReferencia=$fSolicitud[0];
		$plantel=$fSolicitud[1];
		
		$consulta="SELECT * FROM 4573_solicitudesInscripcion WHERE idSolicitudInscripcion=".$idReferencia;
		$fRegistro=$con->obtenerPrimeraFila($consulta);
		$objInscripcion=json_decode($fRegistro[3]);
		$objDatos=array();
		$arrFechasPago=array();
		$idUsuario=$fRegistro[2];
		$consulta="SELECT p.idProgramaEducativo FROM 4513_instanciaPlanEstudio i,4500_planEstudio p WHERE p.idPlanEstudio=i.idPlanEstudio AND i.idInstanciaPlanEstudio=".$objInscripcion->idInstanciaPlan;
		$idProgramaEducativo=$con->obtenerValor($consulta);
		
		
		
		if($fSolicitud[2]=="0")
		{
			
			$idGrado=$fSolicitud[3];
		}
		else
		{
			$consulta="SELECT id__685_tablaDinamica FROM _685_tablaDinamica WHERE idReferencia=".$idRegistro;
			$idRefAux=$con->obtenerValor($consulta);
			$consulta="SELECT gradoInscribe FROM 4574_solicitudesRevalidacion WHERE idReferencia=".$idRefAux;
			$idGrado=$con->obtenerValor($consulta);
			
		}

		$arrDimensiones=array();
		$arrDimensionesCosto["plantel"]=$plantel;
		$arrDimensionesCosto["idProgramaEducativo"]=$idProgramaEducativo;
		$arrDimensionesCosto["idInstanciaPlanEstudio"]=$objInscripcion->idInstanciaPlan;
		$arrDimensionesCosto["grado"]=$idGrado;
		
		$oParametroCosto=array();
		$oParametroCosto["idUsuario"]=$idUsuario;
		$idPlanPagosAplica=-1;//Si -1 Aplica todos los planes
		$tabulador=generarTabuladorCostoServicio($idConcepto,$objInscripcion->idCiclo,$objInscripcion->idPeriodo,$arrDimensionesCosto,$oParametroCosto); 
		
		$arrDimensionesPagoReferenciado=array();
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IdInstanciaPlan",$objInscripcion->idInstanciaPlan);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDCicloEscolar",$objInscripcion->idCiclo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDPeriodo",$objInscripcion->idPeriodo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDFormulario",678);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDRegistro",$idRegistro);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"Plantel",$plantel);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDProgramaEducativo",$idProgramaEducativo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDGrado",$idGrado);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDUsuario",$idUsuario);
		$consulta="SELECT idPlanPago FROM 4581_planPagoAlumnoInscripcion WHERE idFormulario=678 AND idReferencia=".$idRegistro;
		$idPlanPagos=$con->obtenerValor($consulta);
		$arrTabulador=generarFechasPagosServicio($idConcepto,$tabulador,$idPlanPagos);
		
		generarPagosReferenciados($plantel,$idUsuario,$arrTabulador,$arrDimensionesPagoReferenciado,"",0);
		return true;
	}	
	
	function finalizarInscripcion($idRegistro)
	{
		global $con;
		$consulta="SELECT idReferencia,codigoInstitucion,codigo,'0',codigoUnidad,idGradoInscribe FROM _678_tablaDinamica WHERE id__678_tablaDinamica=".$idRegistro;
	
		$fSolicitud=$con->obtenerPrimeraFila($consulta);
		$idReferencia=$fSolicitud[0];
		$plantel=$fSolicitud[1];
		$codigoInstitucion=$fSolicitud[1];
		$codigoUnidad=$fSolicitud[4];
		
		$consulta="SELECT * FROM 4573_solicitudesInscripcion WHERE idSolicitudInscripcion=".$idReferencia;
		$fRegistro=$con->obtenerPrimeraFila($consulta);
		$idUsuario=$fRegistro[2];
		$objInscripcion=json_decode($fRegistro[3]);
		$idInstanciaPlan=$objInscripcion->idInstanciaPlan;
		if($fSolicitud[3]=="0")
		{
			
			$idGrado=$fSolicitud[5];
		}
		else
		{
			$consulta="SELECT id__685_tablaDinamica FROM _685_tablaDinamica WHERE idReferencia=".$idRegistro;
			$idRefAux=$con->obtenerValor($consulta);
			$consulta="SELECT gradoInscribe,id_4574_solicitudesRevalidacion FROM 4574_solicitudesRevalidacion WHERE idReferencia=".$idRefAux;
			$fRevalidacion=$con->obtenerPrimeraFila($consulta);
			$idGrado=$fRevalidacion[0];
			$filaConf=obtenerConfiguracionPlanEstudio(398,"",$objInscripcion->idInstanciaPlan);
			$consulta="SELECT tipoExamen,noExamen FROM _398_gridTiposExamen WHERE idReferencia=".$filaConf[0]." AND asentarMateriaRevalidacion=1";
			$fExamen=$con->obtenerPrimeraFila($consulta);
			$x=0;
			
			
			
			$query[$x]="begin";
			$x++;
			$consulta="SELECT idMateriaDestino,calificacion FROM 4575_calificacionesSolicitudesRevalidacion WHERE idSolicitudRevaliacion=".$fRevalidacion[1]." AND situacion=1";
			$resMatRev=$con->obtenerFilas($consulta);
			while($fMatRev=mysql_fetch_row($resMatRev))
			{
				$query[$x]="INSERT INTO 4569_calificacionesEvaluacionAlumnoPerfilMateria(idAlumno,idGrupo,bloque,valor,tipoEvaluacion,noEvaluacion,aprobado,idMateria) 
							VALUES(".$idUsuario.",0,0,".$fMatRev[1].",".$fExamen[0].",".$fExamen[1].",1,".$fMatRev[0].")";
				$x++;
			}
			
			$query[$x]="commit";
			$x++;
			
			/*if($con->ejecutarBloque($query))
				return false;*/
			
			
		}

		$query=array();
		$x=0;
		$query[$x]="begin";
		$x++;
		$arrDocumentos=array();
		$consulta="SELECT * FROM 4598_evaluacionDocumentosInscripcion WHERE idFormulario=678 AND idReferencia=".$idRegistro." AND situacionDocumento=2";
		$resDoc=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($resDoc))
		{
			if(!isset($arrDocumentos[$fila[6]]))
			{
				$arrDocumentos[$fila[6]]=array();
			}
			$oDoc[0]=$fila[3];
			$oDoc[1]=$fila[7];
			array_push($arrDocumentos[$fila[6]],$oDoc);
		}
		if(sizeof($arrDocumentos)>0)
		{
			foreach($arrDocumentos as $fecha=>$resto)
			{
				
				$query[$x]="INSERT INTO _925_tablaDinamica(idReferencia,fechaCreacion,responsable,idEstado,codigoInstitucion,codigoUnidad,cmbAlumno,instanciaPlanEstudio,fechaLimite,codigo)
							VALUES(".$idRegistro.",'".date("Y-m-d H:i:s")."',".$idUsuario.",2,'".$codigoInstitucion."','".$codigoUnidad."',".$idUsuario.",".$idInstanciaPlan.",'".$fecha."','@folio')";
				$x++;
				$query[$x]="set @idRegistro:=(select last_insert_id())";
				$x++;
				$query[$x]="INSERT INTO _928_tablaDinamica(idReferencia,fechaCreacion,responsable,idEstado,codigoInstitucion,codigoUnidad)
						VALUES(@idRegistro,'".date("Y-m-d H:i:s")."',".$idUsuario.",1,'".$codigoInstitucion."','".$codigoUnidad."')";
				$x++;
				$query[$x]="set @idRegistroDoc:=(select last_insert_id())";
				$x++;
				foreach($resto as $oDoc)
				{
					$query[$x]="INSERT INTO _928_gridDocumentosCondicionados(idReferencia,documento,comentarios)
								VALUES(@idRegistroDoc,".$oDoc[0].",'".cv($oDoc[1])."')";
					$x++;
				}
			}
		}
		$query[$x]="commit";
		$x++;
		$consulta="SELECT idPlanPago FROM 4581_planPagoAlumnoInscripcion WHERE idFormulario=678 AND idReferencia=".$idRegistro;
		$idPlanPagos=$con->obtenerValor($consulta);
		if(generarPagoReferenciadoColegiatura($idRegistro))
		{
				
			if( inscribirAlumnoPlanRigido($idUsuario,$idGrado,$objInscripcion->idCiclo,$objInscripcion->idInstanciaPlan,$objInscripcion->idPeriodo,$fSolicitud[2],$idPlanPagos))
			{
				if($con->ejecutarBloque($query))
				{
					
					$consulta="select id__925_tablaDinamica from _925_tablaDinamica where codigo='@folio'";
					$res=$con->obtenerFilas($consulta);
					while($fila=mysql_fetch_row($res))
					{
						asignarFolioRegistro(925,$fila[0]);
					}
					
					return true;
				}
			}
		}
		return false;
		
	}
	
	//Revalidacion
	function dictamenRevalidacionRealizado($idFormulario,$idRegistro)
	{
		global $con;
		$consulta="SELECT idReferencia FROM _685_tablaDinamica WHERE id__685_tablaDinamica=".$idRegistro;
		$idInscripcion=$con->obtenerValor($consulta);
		if($idInscripcion!="-1")
		{
			cambiarEtapaFormulario(678,$idInscripcion,3.1);
		}
	}
	
	
	function menusOtrosServicios($idMenu,$param1)
	{
		global $con;
		
		$consulta="SELECT textoMenu,clase FROM 808_titulosMenu WHERE idMenu=".$idMenu;
		
		$fMenu=$con->obtenerPrimeraFila($consulta);
		
		$tbl="<table width='250'>
			<tr>
				<td >
					<ul id='menu_1'>
						<li  ><a href='#'><span class='".$fMenu[1]."' style='display:inline-block; '>".$fMenu[0]."</span></a>
							<ul>";
							
		$consulta="SELECT i.idConcepto,nombreConcepto FROM 564_conceptosVSCategorias c,561_conceptosIngreso i WHERE idCategoria=20 AND i.idConcepto=c.idConcepto ORDER BY nombreConcepto";
		$resOpciones=$con->obtenerFilas($consulta);
		while($fOpciones=mysql_fetch_row($resOpciones))					
		{
			$opcion="<li >
						<table>
						<tr height='21'>
							<td width='20'>
								<img src='../media/verBullet.php?id=679' width='16' height='16'> 
							</td>
							<td>
								<a onclick=javascript:window.parent.abrirFormularioProceso('".bE(923)."','LTE=','".bE(186)."','@param','".bE("[['idSolicitud','".$fOpciones[0]."'],['actorInicio','']]")."') style='cursor:pointer; cursor: hand;'>
								<span class='negrita'>Solicitud de ".$fOpciones[1]."</span>
								</a>
							</td>
						</tr>
						</table>
					</li>";	
			$tbl.=$opcion;											
		}
		$tbl.=	"			</ul>
						</li>
					</ul>
				</td>
			</tr>
			</table>";

		return $tbl;
					
	}
	
	function rendererRegistroProceso($idFormulario,$idRegistro)
	{
		global $con;
		$consulta="select * from _".$idFormulario."_tablaDinamica where id__".$idFormulario."_tablaDinamica=".$idRegistro;
		$fR=$con->obtenerPrimeraFilaAsoc($consulta);
		$etiqueta="";
		switch($idFormulario)
		{
			
			case 715://	Solicitud de baja de alumno
				$consulta="SELECT txtNombreBaja FROM _714_tablaDinamica WHERE id__714_tablaDinamica=".$fR["cmbTipoBaja"];
				$tipoBaja=$con->obtenerValor($consulta);
				$etiqueta="Solicitud de Baja (".$tipoBaja.")";
			break;
			case 736:	//Solicitud de examen
				$consulta="select nombreGrupo,nombreMateria,idInstanciaPlanEstudio from 4520_grupos g,4502_Materias m where idGrupos=".$fR["idGrupo"]." and m.idMateria=g.idMateria";
				$fDatos=$con->obtenerPrimeraFila($consulta);
				$filaConf=obtenerConfiguracionPlanEstudio(398,"",$fDatos[2]);
				$consulta="SELECT etiquetaExamen FROM  _398_gridTiposExamen WHERE idReferencia=".$filaConf[0]." AND tipoExamen=".$fR["idTipoExamen"];
				$examen=$con->obtenerValor($consulta);
				$etiqueta="Solicitud de exámen: ".$examen." (Materia: ".$fDatos[1].", Grupo: ".$fDatos[0].")";
			
			break;
			case 759:	//Registro de solicitud
				$consulta="SELECT tipoSolicitud FROM 4597_tipoSolicitudTitulo WHERE idTipoSolicitud=".$fR["idSolicitud"];
				$etiqueta="Solicitud de: ".$con->obtenerValor($consulta);
			break;
			case 769:	//Solicitud de Titulación
				$etiqueta="Solicitud de titulación";
			break;
			case 845:	//Solicitud de Titulo/Cédula Prof.
				$consulta="SELECT tipoSolicitud FROM 4597_tipoSolicitudTitulo WHERE idTipoSolicitud=".$fR["idSolicitud"];
				$etiqueta="Solicitud de: ".$con->obtenerValor($consulta);
			break;
			case 900:	//Solicitud de extensión de pago
				$consulta="SELECT c.nombreConcepto FROM 6011_movimientosPago m,561_conceptosIngreso c WHERE idMovimiento=".$fR["idReferencia"]." AND c.idConcepto=m.idConcepto";
				$nomConcepto=$con->obtenerValor($consulta);
				$etiqueta="Solicitud de extensión de pago del concepto: ".$nomConcepto;
			break;
			case 910:	//Solicitud de reinscripción
				$consulta="SELECT nombreCiclo FROM 4526_ciclosEscolares where idCiclo=".$fR["idCiclo"];
				$ciclo=$con->obtenerValor($consulta);
				$consulta="SELECT nombrePeriodo FROM _464_gridPeriodos WHERE id__464_gridPeriodos=".$fR["idPeriodo"];
				$periodo=$con->obtenerValor($consulta);
				$etiqueta="Solicitud de Reincripción (Ciclo: ".$ciclo.", Periodo: ".$periodo.",Plan de Estudios: ".obtenerNombreInstanciaPlan($fR["idInstanciaPlan"]).")";
			break;
			case 923:	//Solicitud de trámite
				$consulta="SELECT c.nombreConcepto FROM 561_conceptosIngreso c WHERE c.idConcepto=".$fR["idSolicitud"];
				$nomConcepto=$con->obtenerValor($consulta);
				$etiqueta="Solicitud de: ".$nomConcepto;
			break;
			case 925:	//Solicitud de condicionamiento
				$etiqueta="Condicionamiento de documento, Fecha de vencimiento: ".date("d/m/Y",strtotime($fR["fechaLimite"]));
			break;
			case 932:	//Solicitud de condicionamiento
				$consulta="select nombreMateria from 4502_Materias where idMateria=".$fR["materia"];
				$nombreMateria=$con->obtenerValor($consulta);
				$consulta="SELECT nombreCiclo FROM 4526_ciclosEscolares where idCiclo=".$fR["idCiclo"];
				$ciclo=$con->obtenerValor($consulta);
				$consulta="SELECT nombrePeriodo FROM _464_gridPeriodos WHERE id__464_gridPeriodos=".$fR["idPeriodo"];
				$periodo=$con->obtenerValor($consulta);
				$etiqueta="Condicionamiento de materia: ".$nombreMateria." (Ciclo: ".$ciclo.", Periodo: ".$periodo.",Plan de Estudios: ".obtenerNombreInstanciaPlan($fR["instanciaPlanEstudio"]).")";
			break;
		}
		return $etiqueta;
	}
		
	//Recursos humanos
		
	function generarFichaResumenArticulo($idRegistro,$tiempoEjecucion=false)
	{
		global $con;
		global $urlSitio;
		$claseResumen="Letragris";
		$claseArticulo="Letranegra";
		$claseTitulo="letraVino14N";
		$claseLeerMas="vino11SubrayadadoNegritas";
		
		$aArticulo="";
		$cArticulo="";
		if($tiempoEjecucion)
		{
			$aArticulo='<a href="javascript:abrirArticulo(\''.bE($idRegistro).'\')">';
			$cArticulo='</a>';
		}
		/*$consulta="SELECT idUsuario FROM 246_autoresVSProyecto WHERE idFormulario=752 AND idReferencia=".$idRegistro." AND responsable=1";
		$idAutor=$con->obtenerValor($consulta);
		if($idAutor=="")
			$idAutor=-1;
		$nombreAutor=obtenerNombreUsuario($idAutor);
		<tr>
						<td align="right"><br><span style="font-size:11px; color:#777; font-weight: normal" class="letraExt"><b>Por:</b> '.$nombreAutor.'</span></td>
					</tr>
		*/
		
		$consulta="SELECT * FROM _957_tablaDinamica WHERE id__957_tablaDinamica =".$idRegistro;
		$fRegistro=$con->obtenerPrimeraFila($consulta);
		$urlDocumento="";
		if($fRegistro[14]!="")
			$urlDocumento='<table><tr height="5"><td></td></tr><tr><td><a href="../paginasFunciones/obtenerArchivos.php?id='.bE($fRegistro[14]).'"><img src="../imagenesDocumentos/16/file_extension_pdf.png"></a></td><td><span style="color:#006">&nbsp;<a href="../paginasFunciones/obtenerArchivos.php?id='.bE($fRegistro[14]).'">Descargar artículo</a></span></td></tr></table>';
		
		$tblResumen='<table width="100%">
					<tr>
						<td>'.$aArticulo.'<span class="'.$claseTitulo.'">'.$fRegistro[10].'</span>'.$cArticulo.'</td>
					</tr>
					
					<tr height="20">
							<td></td>
						</tr>
					<tr>
						<td valign="top">
							<p><img style="MARGIN-RIGHT: 10px" src="'.$urlSitio.'/paginasFunciones/obtenerArchivos.php?id='.bE($fRegistro[11]).'" height="100" alt="" width="100" align="left" /><div class="'.$claseResumen.
							'" style="text-align:justify">'.$fRegistro[12].'</div></p>
						</td>
					</tr>
					<tr height="10">
						<td></td>
					</tr>
					<tr>
						<td align="right">'.$aArticulo.'<span class="'.$claseLeerMas.'">Leer mas...</span>'.$cArticulo.'</td>
					</tr>
					</table>';
		
		return $tblResumen;
	}
	
	function generarFichaArticulo($idRegistro)
	{
		global $con;
		global $urlSitio;
		$claseResumen="Letragris";
		$claseArticulo="Letranegra";
		$claseTitulo="letraVino14N";
		$claseLeerMas="vino11SubrayadadoNegritas";
		
		
		$consulta="SELECT * FROM _957_tablaDinamica WHERE id__957_tablaDinamica =".$idRegistro;
		$fRegistro=$con->obtenerPrimeraFila($consulta);
		$cadCita="";
		$consulta="SELECT cita FROM citaxnumero WHERE idFormulario=957 AND idReferencia=".$idRegistro." AND cita <>''";
		$resCita=$con->obtenerFilas($consulta);
		while($fCita=mysql_fetch_row($resCita))
		{
			if($cadCita=="")
				$cadCita='<span style="font-size:11px; color:#777; font-weight: normal" class="letraExt">'.$fCita[0].'</span>';
			else
				$cadCita.='<span style="font-size:11px; color:#777; font-weight: normal" class="letraExt">'.$fCita[0].'</span><br><br>';
		}
		$urlDocumento="";
		if($fRegistro[14]!="")
			$urlDocumento='<table><tr height="5"><td></td></tr><tr><td><a href="../paginasFunciones/obtenerArchivos.php?id='.bE($fRegistro[14]).
			'"><img src="../imagenesDocumentos/16/file_extension_pdf.png"></a></td><td><span style="color:#006">&nbsp;<a href="../paginasFunciones/obtenerArchivos.php?id='.bE($fRegistro[14]).'">Descargar artículo</a></span></td></tr></table>';
		
		
		$tblArticulo='<table width="100%">
					<tr>
						<td><span class="'.$claseTitulo.'">'.$fRegistro[10].'</span><br></td>
					</tr>
					
					<tr height="20">
							<td></td>
						</tr>
					<tr>
						<td valign="top">
							<p><img style="MARGIN-RIGHT: 10px" src="'.$urlSitio.'/paginasFunciones/obtenerArchivos.php?id='.bE($fRegistro[11]).'" height="100" alt="" width="100" align="left" /><div class="'.$claseArticulo.
							'" style="text-align:justify">'.$fRegistro[13].'</div></p>
						</td>
					</tr>
					<tr height="10">
						<td></td>
					</tr>
					<tr height="1">
						<td style="background-color: #CCC"></td>
					</tr>
					<tr height="10">
						<td></td>
					</tr>
					<tr>
						<td valign="top">
							'.$cadCita.'
						</td>
					</tr>
					</table>';
		return $tblArticulo;
	}
	
	function generarLinkBoletin($idBoletin)//
	{
		$cadLink=bE("100584.".$idBoletin.".latis");
		return $cadLink;
	}
	
	function rendererGeneradorLink($valor)//
	{
		
		global $urlSitio;
		$cad=$urlSitio."/modulosEspeciales_UGM/portalBoletin.php?ref=".generarLinkBoletin($valor);
		
		$url='<a href="'.$cad.'">'.$cad.'</a>';

		return "'".$url."'";
		
	}
	
	function rendererGeneradorVacante($valor)//
	{
		return "'".generarFichaResumenVacanteEmail($valor,"",false)."'";
	}
	
	function generarFichaResumenVacante($idVacante,$noRegistro,$vistaPortal=true)//
	{
		global $con;
		global $urlSitio;
		$claseResumen="letraExt";
		$claseArticulo="Letranegra";
		$claseTitulo="letraVino14N";
		$claseLeerMas="vino11SubrayadadoNegritas";
		
		$aArticulo="";
		$cArticulo="";
		if($vistaPortal)
		{
			$aArticulo='<a href="javascript:window.parent.abrirDetalleVacante(\''.bE($idVacante).'\')">';
			$cArticulo='</a>';
		}
		else
		{
			$aArticulo='<a href="'.$urlSitio.'/modulosEspeciales_UGM/portalBolsaTrabajo.php?v='.generarLinkBoletin($idVacante).'">';
			$cArticulo='</a>';
		}
		$consulta="SELECT fechaCambio FROM 941_bitacoraEtapasFormularios WHERE idFormulario=952 AND idRegistro=".$idVacante." ORDER BY idRegistroEstado";
		$fPublicacion=strtotime($con->obtenerValor($consulta));
		$consulta="SELECT * FROM _952_tablaDinamica WHERE id__952_tablaDinamica =".$idVacante;
		$fRegistro=$con->obtenerPrimeraFila($consulta);
		$consulta="select unidad from 817_organigrama where codigoUnidad='".$fRegistro[8]."'";
		$nombrePlantel=$con->obtenerValor($consulta);
		$consulta="SELECT nombreCategoria FROM _953_tablaDinamica WHERE id__953_tablaDinamica=".$fRegistro[25];
		$nombreCategoria=$con->obtenerValor($consulta);
		if($fRegistro[27]=="")
			$fRegistro[27]=-1;
		$consulta="SELECT txtNombreRazonSocial FROM _752_tablaDinamica WHERE id__752_tablaDinamica=".$fRegistro[27];
		$nombreEmpresa=$con->obtenerValor($consulta);
		if($noRegistro!="")
			$noRegistro.=".- ";
		$tblResumen='<table width="100%">
					<tr>
						<td width="10" valign="top"><span class="'.$claseTitulo.'">'.$noRegistro.'</span></td><td width="10"></td><td valign="top">'.$aArticulo.'<span class="'.$claseTitulo.'">'.$fRegistro[10].'</span>'.$cArticulo.'</td>
					</tr>
					<tr height="21">
						<td colspan="2"></td><td align="right"><span style="font-size:11px; color:#000; font-weight: normal" class="letraExt"><b>Publicado el día:</b> '.date("d/m/Y",$fPublicacion).'</span><br></td>
					</tr>
					<tr height="21">
						<td colspan="2"></td><td align="left"><span style="font-size:11px;" class="letraRojaSubrayada8"><b>Empresa solicitante:</b></span><span style="font-size:11px;" class="corpo8Bold"><b> '.$nombreEmpresa.'</b></span></td>
					</tr>
					<tr height="21">
						<td colspan="2"></td><td align="left"><span style="font-size:11px;" class="letraRojaSubrayada8"><b>Categoría:</b></span><span style="font-size:11px;" class="corpo8Bold"><b> '.$nombreCategoria.'</b></span></td>
					</tr>
					<tr height="21">
						<td colspan="2"></td><td align="left"><span style="font-size:11px;" class="letraRojaSubrayada8"><b>Publicado por plantel:</b></span><span style="font-size:11px;" class="corpo8Bold"><b> '.$nombrePlantel.'</b></span></td>
					</tr>
					
					<tr height="20">
							<td colspan="2"></td><td></td>
						</tr>
					<tr>
						<td colspan="2"></td>
						<td valign="top">
							<div class="'.$claseResumen.'" style="text-align:justify">'.$fRegistro[11].'</div></p>
						</td>
					</tr>
					<tr height="10">
						<td colspan="3"></td>
					</tr>
					<tr>
						<td colspan="2"></td>
						<td align="right">'.$aArticulo.'<span class="'.$claseLeerMas.'">Detalles...</span>'.$cArticulo.'</td>
					</tr>
					</table>';
		
		return $tblResumen;
	}
	
	function generarFichaResumenVacanteEmail($idVacante,$noRegistro,$vistaPortal=true)//
	{
		global $con;
		global $urlSitio;
		
		$aArticulo="";
		$cArticulo="";
		if($vistaPortal)
		{
			$aArticulo='<a href="javascript:abrirDetalleVacante(\''.bE($idVacante).'\')">';
			$cArticulo='</a>';
		}
		else
		{
			$aArticulo='<a href="'.$urlSitio.'/modulosEspeciales_UGM/portalBolsaTrabajo.php?v='.generarLinkBoletin($idVacante).'">';
			$cArticulo='</a>';
		}
		$consulta="SELECT fechaCambio FROM 941_bitacoraEtapasFormularios WHERE idFormulario=952 AND idRegistro=".$idVacante." ORDER BY idRegistroEstado";
		$fPublicacion=strtotime($con->obtenerValor($consulta));
		$consulta="SELECT * FROM _952_tablaDinamica WHERE id__952_tablaDinamica =".$idVacante;
		$fRegistro=$con->obtenerPrimeraFila($consulta);
		$consulta="select unidad from 817_organigrama where codigoUnidad='".$fRegistro[8]."'";
		$nombrePlantel=$con->obtenerValor($consulta);
		$consulta="SELECT nombreCategoria FROM _953_tablaDinamica WHERE id__953_tablaDinamica=".$fRegistro[25];
		$nombreCategoria=$con->obtenerValor($consulta);
		if($fRegistro[27]=="")
			$fRegistro[27]=-1;
		$consulta="SELECT txtNombreRazonSocial FROM _752_tablaDinamica WHERE id__752_tablaDinamica=".$fRegistro[27];
		$nombreEmpresa=$con->obtenerValor($consulta);
		$tblResumen='<table width="100%">
					<tr height="1"><td colspan="2"></td><td align="left"><table width="400"><tbody><tr style="height:1px !important;"><td style="width:100%;background-color:#CCC"></td></tr></tbody></table></td></tr>
					<tr>
						<td width="10" valign="top"><span ></span></td><td width="10"></td><td valign="top">'.$aArticulo.'<span  style="color:#B0281A; font-size:14px;font-weight: bold !important;text-decoration: underline !important;">'.$fRegistro[10].'</span>'.$cArticulo.'</td>
					</tr>
					<tr height="21">
						<td colspan="2"></td><td align="right"><span style="font-size:11px; color:#000; font-weight: normal"><b>Publicado el día:</b> '.date("d/m/Y",$fPublicacion).'</span><br></td>
					</tr>
					<tr height="21">
						<td colspan="2"></td><td align="left"><span style="font-size:11px;" class="letraRojaSubrayada8"><b>Empresa solicitante:</b></span><span style="font-size:11px;" class="corpo8Bold"><b> '.$nombreEmpresa.'</b></span></td>
					</tr>
					<tr height="21">
						<td colspan="2"></td><td align="left"><span style="color:#B0281A; font-size:11px;font-weight: bold !important;text-decoration: underline !important;" ><b>Categoría:</b></span><span style="font-size:11px;" ><b> '.$nombreCategoria.'</b></span></td>
					</tr>
					<tr height="21">
						<td colspan="2"></td><td align="left"><span style="color:#B0281A; font-size:11px;font-weight: bold !important;text-decoration: underline !important;" ><b>Plantel solicitante:</b></span><span style="font-size:11px;" ><b> '.$nombrePlantel.'</b></span></td>
					</tr>
					
					<tr height="20">
							<td colspan="2"></td><td></td>
						</tr>
					<tr>
						<td colspan="2"></td>
						<td valign="top">
							<div style="text-align:justify;font-size:11px">'.$fRegistro[11].'</div></p>
						</td>
					</tr>
					<tr height="10">
						<td colspan="3"></td>
					</tr>
					<tr>
						<td colspan="2"></td>
						<td align="right">'.$aArticulo.'<span style="color:#B0281A; font-size:10px;font-weight: bold !important;text-decoration: underline !important;">Detalles...</span>'.$cArticulo.'</td>
					</tr>
					<tr height="1"><td colspan="2"></td><td align="left"><table width="400"><tbody><tr style="height:1px !important;"><td style="width:100%;background-color:#CCC"></td></tr></tbody></table></td></tr>
					</table>';
		
		return $tblResumen;
	}
	
	function notificarVacanteBolsaTrabajo($idRegistro)//
	{
		global $con;
		
		$arrMailsMiembros=array();
		$arrMailsPublico=array();
	
		$consulta="SELECT * FROM _952_ambitoPublicacion WHERE idPadre=".$idRegistro." AND idOpcion=1"; //Public general
		$fPublico=$con->obtenerPrimeraFila($consulta);


		$consulta="SELECT * FROM _952_ambitoPublicacion WHERE idPadre=".$idRegistro." AND idOpcion=2"; //MIembro
		$fMiebros=$con->obtenerPrimeraFila($consulta);
		if($fMiebros)
		{
			$consulta="SELECT idUsuario FROM _956_tablaDinamica where tipoUsuario=1 AND notificacionActiva=1";
			$resUsr=$con->obtenerFilas($consulta);
			while($fUsr=mysql_fetch_row($resUsr))
			{
				$idUsuario=$fUsr[0];
				$consulta="SELECT Mail FROM 805_mails WHERE idUsuario=".$idUsuario." and trim(Mail)<>''";
				$resMail=$con->obtenerFilas($consulta);
				while($fMail=mysql_fetch_row($resMail))
				{
					if(!isset($arrMailsMiembros[$fMail[0]]))
						$arrMailsMiembros[$fMail[0]]=$idUsuario;
				}
			}
		}
		
		
		if($fPublico)
		{
			$consulta="SELECT email,id__956_tablaDinamica FROM _956_tablaDinamica where tipoUsuario=0 AND notificacionActiva=1";
			$resUsr=$con->obtenerFilas($consulta);
			while($fUsr=mysql_fetch_row($resUsr))
			{
				if(!isset($arrMailsPublico[$fUsr[0]]))
					$arrMailsPublico[$fUsr[0]]=$fUsr[1];
			}
		}
		
		foreach($arrMailsMiembros as $mail=>$idUsuario)
		{
			if($mail!="")
			{
				$consulta="SELECT Paterno,Materno,Nom FROM 802_identifica WHERE idUsuario=".$idUsuario;
				$fIdentifica=$con->obtenerPrimeraFila($consulta);
				$arrParam["idVacante"]=$idRegistro;
				$arrParam["apPaterno"]=$fIdentifica[0];
				$arrParam["apMaterno"]=$fIdentifica[1];
				$arrParam["nombre"]=$fIdentifica[2];
				$arrParam["email"]=$mail;
				enviarMensajeEnvio(15,$arrParam);
			}
		}
		foreach($arrMailsPublico as $mail=>$idUsuario)
		{
			if($mail!="")
			{
				$consulta="SELECT apPaterno,apMaterno,nombre FROM _956_tablaDinamica WHERE id__956_tablaDinamica=".$idUsuario;
				
				$arrParam["idVacante"]=$idRegistro;
				$fIdentifica=$con->obtenerPrimeraFila($consulta);
				$arrParam["idVacante"]=$idRegistro;
				$arrParam["apPaterno"]=$fIdentifica[0];
				$arrParam["apMaterno"]=$fIdentifica[1];
				$arrParam["nombre"]=$fIdentifica[2];
				$arrParam["email"]=$mail;
				enviarMensajeEnvio(15,$arrParam);
			}
		}
			
	}
	
	
	//Boletin
	function prepararPublicacionBoletin($idFormulario,$idRegistro)
	{
		global $con;
		$consulta="UPDATE _957_tablaDinamica SET idEstado=3,publicadoEn=".$idRegistro." WHERE id__957_tablaDinamica IN (
					SELECT idArticulo FROM 3005_articulosBoletin WHERE idFormulario=".$idFormulario." AND idReferencia=".$idRegistro.")";
		verificarBoletinesPublicados();
		return $con->ejecutarConsulta($consulta);
	}
	
	function verificarBoletinesPublicados()
	{
		global $con;
		
		$arrMailsMiembros=array();
		$arrMailsPublico=array();
		$consulta="SELECT id__960_tablaDinamica FROM _960_tablaDinamica WHERE idEstado=2 AND fechaInicio='".date("Y-m-d")."'";
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$consulta="SELECT * FROM _960_ambitoAplicacion WHERE idPadre=".$fila[0]." AND idOpcion=1"; //Public general
			$fPublico=$con->obtenerPrimeraFila($consulta);


			$consulta="SELECT * FROM _960_ambitoAplicacion WHERE idPadre=".$fila[0]." AND idOpcion=2"; //MIembro
			$fMiebros=$con->obtenerPrimeraFila($consulta);
			if($fMiebros)
			{
				$consulta="SELECT idUsuario FROM _1032_tablaDinamica where tipoUsuario=1 AND notificacionActiva=1";
				$resUsr=$con->obtenerFilas($consulta);
				while($fUsr=mysql_fetch_row($resUsr))
				{
					$idUsuario=$fUsr[0];
					$consulta="SELECT Mail FROM 805_mails WHERE idUsuario=".$idUsuario." and trim(Mail)<>''";
					$resMail=$con->obtenerFilas($consulta);
					while($fMail=mysql_fetch_row($resMail))
					{
						if(!isset($arrMailsMiembros[$fMail[0]]))
							$arrMailsMiembros[$fMail[0]]=$idUsuario;
					}
				}
			}
			
			
			if($fPublico)
			{
				$consulta="SELECT email,id__1032_tablaDinamica FROM _1032_tablaDinamica where tipoUsuario=0 AND notificacionActiva=1";
				$resUsr=$con->obtenerFilas($consulta);
				while($fUsr=mysql_fetch_row($resUsr))
				{
					if(!isset($arrMailsPublico[$fUsr[0]]))
						$arrMailsPublico[$fUsr[0]]=$fUsr[1];
				}
			}
			
			foreach($arrMailsMiembros as $mail=>$idUsuario)
			{
				if($mail!="")
				{
					$arrParam["idBoletin"]=$fila[0];
					$arrParam["idUsuario"]=$idUsuario;
					$arrParam["email"]=$mail;
					enviarMensajeEnvio(16,$arrParam);
				}
			}
			
			
			foreach($arrMailsPublico as $mail=>$idUsuario)
			{
				if($mail!="")
				{
					$arrParam["idBoletin"]=$fila[0];
					$arrParam["idUsuario"]=$idUsuario;
					$arrParam["email"]=$mail;
					enviarMensajeEnvio(18,$arrParam);
				}
			}
			
			
		}
	}
	
	//Beca
	
	function convocatoriaRectoriaPublicada($idFomulario,$idRegistro)
	{
		global $con;
		$x=0;
		$query[$x]="begin";
		$x++;
		
		
		$consulta="SELECT tituloConvocatoria,fInicioPublicacion,fFinPublicacion,fLimiteDictmen FROM _963_tablaDinamica WHERE id__963_tablaDinamica=".$idRegistro;
		$fConvocatoriaRec=$con->obtenerPrimeraFila($consulta);
		$consulta="SELECT plantel,matriculaPosible FROM _963_gridPlanteles WHERE idReferencia=".$idRegistro;
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$query[$x]="INSERT INTO _964_tablaDinamica(idReferencia,fechaCreacion,idEstado,codigoInstitucion,tituloConvocatoria,fInicioPublicacion,fFinPublicacion,limiteBeneficiario,codigo)
					VALUES(".$idRegistro.",'".date("Y-m-d H:i:s")."',1,'".$fila[0]."','".cv($fConvocatoriaRec[0])."','".cv($fConvocatoriaRec[1])."','".cv($fConvocatoriaRec[2])."',".$fila[1].",'@folio')";
			$x++;
		}
		$query[$x]="commit";
		$x++;
		if($con->ejecutarBloque($query))
		{
			$consulta="select id__964_tablaDinamica from _964_tablaDinamica where codigo='@folio'";
			$res=$con->obtenerFilas($consulta);
			while($fila=mysql_fetch_row($res))
			{
				asignarFolioRegistro(964,$fila[0]);
			}
		}
		return false;
	}
	
	function generarPagoReferenciadoEstudioSocioEconomico($idRegistro)
	{
		global $con;

		$idConcepto=8;
		$idFormulario=946;		
		$consulta="SELECT idUsuarioRegistro,idInstanciaPlan,idCiclo,idPeriodo FROM _946_tablaDinamica WHERE id__946_tablaDinamica=".$idRegistro;
		$fBase=$con->obtenerPrimeraFila($consulta);
		$idInstanciaPlan=$fBase[1];
		$idCiclo=$fBase[2];
		$idPeriodo=$fBase[3];
		$consulta="SELECT p.idProgramaEducativo,i.sede FROM 4513_instanciaPlanEstudio i,4500_planEstudio p WHERE p.idPlanEstudio=i.idPlanEstudio AND i.idInstanciaPlanEstudio=".$idInstanciaPlan;
		$fInstancia=$con->obtenerPrimeraFila($consulta);
		
		$plantel=$fInstancia[1];
		
		$arrFechasPago=array();
		$idProgramaEducativo=$fInstancia[0];
		
		$idGradoInscribe=-1;
		
		$arrDimensiones=array();
		$arrDimensionesCosto["plantel"]=$plantel;
		$arrDimensionesCosto["idProgramaEducativo"]=$idProgramaEducativo;
		$arrDimensionesCosto["idInstanciaPlanEstudio"]=$idInstanciaPlan;
		$arrDimensionesCosto["grado"]=-1;
		
		$oParametroCosto=array();
		$oParametroCosto["idUsuario"]=$fBase[0];
		$idPlanPagosAplica=-1;//Si -1 Aplica todos los planes
		$tabulador=generarTabuladorCostoServicio($idConcepto,$idCiclo,$idPeriodo,$arrDimensionesCosto,$oParametroCosto); 
		
		$arrDimensionesPagoReferenciado=array();
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IdInstanciaPlan",$idInstanciaPlan);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDCicloEscolar",$idCiclo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDPeriodo",$idPeriodo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDFormulario",$idFormulario);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDRegistro",$idRegistro);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"Plantel",$plantel);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDProgramaEducativo",$idProgramaEducativo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDGrado",$idGradoInscribe);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDUsuario",$fBase[0]);

		$arrTabulador=generarFechasPagosServicio($idConcepto,$tabulador);
		generarPagosReferenciados($plantel,$fBase[0],$arrTabulador,$arrDimensionesPagoReferenciado,"pagoEstudioSocioEconomicoRealizado",0);
		return true;
	}
	
	function pagoEstudioSocioEconomicoRealizado($idMovimiento)
	{
		global $con;
		$consulta="SELECT valorCampo FROM 6012_detalleAsientoPago WHERE idAsientoPago=".$idMovimiento." AND idDimension=11";

		$idFormulario=$con->obtenerValor($consulta);
		$consulta="SELECT valorCampo FROM 6012_detalleAsientoPago WHERE idAsientoPago=".$idMovimiento." AND idDimension=12";
		$idRegistro=$con->obtenerValor($consulta);
		
		cambiarEtapaFormulario($idFormulario,$idRegistro,4);
	}
	
	function verificarSituacionSolicitudBeca($idFormulario,$idRegistro)
	{
		global $con;
		$consulta="SELECT radTipoBeca FROM _946_tablaDinamica WHERE id__946_tablaDinamica=".$idRegistro;
		$tipoBeca=$con->obtenerValor($consulta);
		$consulta="SELECT requiereEstudioS FROM _962_tablaDinamica WHERE id__962_tablaDinamica=".$tipoBeca;
		$requiere=$con->obtenerValor($consulta);
		$numEtapa="";
		if($requiere==1)
			$numEtapa=3;
		else
			$numEtapa=5;
		cambiarEtapaFormulario($idFormulario,$idRegistro,$numEtapa);
		
	}
	
	function notificarResultadosBeca($idFormulario,$idRegistro)
	{
		global $con;
		$consulta="SELECT id__964_tablaDinamica FROM _964_tablaDinamica WHERE idReferencia=".$idRegistro;
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$consulta="SELECT id__946_tablaDinamica FROM _946_tablaDinamica WHERE idReferencia=".$fila[0]." AND idEstado=10";
			$resSol=$con->obtenerFilas($consulta);
			while($fSol=mysql_fetch_row($resSol))
			{
					$consulta="SELECT porcentajeBecaAsignar FROM _969_tablaDinamica WHERE idReferencia=".$fSol[0];
					$porcentaje=$con->obtenerValor($consulta);
					$consulta="UPDATE _946_tablaDinamica SET porcentajeAsignado=".$porcentaje." WHERE id__946_tablaDinamica=".$fSol[0];
					if($con->ejecutarConsulta($consulta))
						cambiarEtapaFormulario("946",$fSol[0],12);
					else
						return false;
			}
			cambiarEtapaFormulario("964",$fila[0],4);
		}

	}
	
	function registrarAsignacionBeca($idFormulario,$idRegistro)
	{
		global $con;
		$consulta="SELECT idUsuarioRegistro,idInstanciaPlan,radTipoBeca,porcentajeAsignado,idPeriodo,idCiclo FROM _946_tablaDinamica WHERE id__946_tablaDinamica=".$idRegistro;
		$fRegistro=$con->obtenerPrimeraFila($consulta);
		$consulta="SELECT sede FROM 4513_instanciaPlanEstudio WHERE idInstanciaPlanEstudio=".$fRegistro[1];
		$plantel=$con->obtenerValor($consulta);
		$consulta="SELECT horasServicio,departamento,responsableBecario FROM _977_tablaDinamica WHERE idReferencia=".$idRegistro;
		$fDictamen=$con->obtenerPrimeraFila($consulta);
		$x=0;
		$query[$x]="begin";
		$x++;
		$query[$x]="INSERT INTO 4603_becasAlumno(idUsuario,idCiclo,idPeriodo,situacion,porcentajeBeca,idInstanciaPlanEstudio,idTipoBeca)
					VALUES(".$fRegistro[0].",".$fRegistro[5].",".$fRegistro[4].",1,".$fRegistro[3].",".$fRegistro[1].",".$fRegistro[2].")";
					
		$x++;
		$query[$x]="set @idRegistro:=(select last_insert_id())";
		$x++;
		$query[$x]="INSERT INTO _978_tablaDinamica(idReferencia,fechaCreacion,responsable,idEstado,codigoInstitucion,codigo,
					alumno,idInstanciaPlanEstudio,horasServicio,responsableBecario,departamento,idCiclo,idPeriodo) VALUES
				(@idRegistro,'".date("Y-m-d H:i:s")."',".$fDictamen[2].",2,'".$plantel."','@folio',".$fRegistro[0].",".$fRegistro[1].",".$fDictamen[0].",".$fDictamen[2].",'".$fDictamen[1]."',".$fRegistro[5].",".$fRegistro[4].")";
		$x++;
		
		$porcentaje=$fRegistro[3]/100;
		$consulta="SELECT idMovimiento FROM _969_gridConceptosAfectacion g,_969_tablaDinamica t WHERE t.idReferencia=".$idRegistro." AND g.idReferencia=t.id__969_tablaDinamica AND g.afecta=1";
		$rMovimiento=$con->obtenerFilas($consulta);
		while($fMov=mysql_fetch_row($rMovimiento))
		{
			$query[$x]="UPDATE 6012_asientosPago SET monto=(monto*".$porcentaje.") WHERE idReferenciaMovimiento=".$fMov[0];
			$x++;	
		}
		
		$query[$x]="commit";
		$x++;
		if($con->ejecutarBloque($query))
		{
			registrarRolUsuario($fDictamen[2],"109_0");
			$consulta="select id__978_tablaDinamica from _978_tablaDinamica where codigo='@folio'";
			$res=$con->obtenerFilas($consulta);
			while($fila=mysql_fetch_row($res))
			{
				asignarFolioRegistro(978,$fila[0]);
			}
			return true;	
		}

	}
		
	function registrarModificacionSituacionBeca($idFormulario,$idRegistro,$numEtapa)
	{
		global $con;
		$consulta="SELECT idReferencia FROM _978_tablaDinamica WHERE id__978_tablaDinamica=".$idRegistro;
		$idReferencia=$con->obtenerValor($consulta);
		$situacion=0;
		switch($numEtapa)
		{
			case "4":
				$situacion=2; //Cancelada
			break;
			case "3":
				$situacion=3; //Liberada
			break;
		}
		$consulta="UPDATE 4603_becasAlumno SET situacion=".$situacion." WHERE idBeca=".$idReferencia;
		return $con->ejecutarConsulta($consulta);
	}
	
	function menuConvocatorias($idMenu,$param1)
	{
		global $con;
		
		$consulta="SELECT textoMenu,clase FROM 808_titulosMenu WHERE idMenu=".$idMenu;
		
		$fMenu=$con->obtenerPrimeraFila($consulta);
		
		$tbl="<table width='450'>
			<tr>
				<td >
					<ul id='menu_1'>
						<li  ><a href='#'><span class='".$fMenu[1]."' style='display:inline-block; '>".$fMenu[0]."</span></a>
							<ul>";
		$numReg=0;		
		$consulta="SELECT idCiclo,idPeriodo,plantel FROM 4529_alumnos WHERE idUsuario=".$param1["idUsuarioRegistro"]." AND idInstanciaPlanEstudio=".$param1["idInstanciaPlan"]." ORDER BY idAlumnoTabla desc";
		$fAlumno=$con->obtenerPrimeraFila($consulta);			
		$consulta="SELECT id__964_tablaDinamica,tituloConvocatoria FROM _964_tablaDinamica WHERE idEstado=2 and codigoInstitucion='".$fAlumno[2]."' and '".date("Y-m-d")."'>=fInicioPublicacion AND '".date("Y-m-d")."'<=fFinPublicacion";

		$resOpciones=$con->obtenerFilas($consulta);
		while($fOpciones=mysql_fetch_row($resOpciones))					
		{
			$opcion="";
			$consulta="SELECT id__946_tablaDinamica,idEstado FROM _946_tablaDinamica WHERE idReferencia=".$fOpciones[0]." AND idUsuarioRegistro=".$param1["idUsuarioRegistro"]." AND idInstanciaPlan=".$param1["idInstanciaPlan"];
			$fSolicitud=$con->obtenerPrimeraFila($consulta);
			if(!$fSolicitud)
			{
				$opcion="<li >
							<table>
							<tr height='21'>
								<td width='20'>
									<img src='../media/verBullet.php?id=679' width='16' height='16'> 
								</td>
								<td>
									<a onclick=javascript:window.parent.abrirFormularioProceso('".bE(946)."','LTE=','".bE(203)."','@param','".bE("[['idReferencia','".$fOpciones[0]."'],['actorInicio',''],['idCiclo','".$fAlumno[0]."'],['idPeriodo','".$fAlumno[1]."']]")."') style='cursor:pointer; cursor: hand;'>
									<span class='negrita'>(Becas) ".$fOpciones[1]."</span>
									</a>
								</td>
							</tr>
							</table>
						</li>";	
			}
			else
			{
				$idProceso=obtenerIdProcesoFormulario(946);
				$actor=obtenerActorProcesoIdRol($idProceso,"7_0",$fSolicitud[1]);
				if(($actor=="")||($actor==-1))
					$actor=0;
					
				$opcion="<li >
							<table>
							<tr height='21'>
								<td width='20'>
									<img src='../media/verBullet.php?id=679' width='16' height='16'> 
								</td>
								<td>
									<a onclick=javascript:window.parent.abrirFormularioProceso('".bE(946)."','".bE($fSolicitud[0])."','".bE($actor)."','@param') style='cursor:pointer; cursor: hand;'>
									<span class='negrita'>(Becas) ".$fOpciones[1]."</span>
									</a>
								</td>
							</tr>
							</table>
						</li>";	
			}
			$tbl.=$opcion;	
			$numReg++;										
		}
		$tbl.=	"			</ul>
						</li>
					</ul>
				</td>
			</tr>
			</table>";
		if($numReg==0)
			$tbl="";
		return $tbl;
					
	}	
	
	function generarPagoReferenciadoExamen($idRegistro)
	{
		global $con;
		$consulta="SELECT idTipoExamen,idGrupo,idUsuarioRegistro,noEvaluacion FROM _736_tablaDinamica WHERE id__736_tablaDinamica=".$idRegistro;
		$fBase=$con->obtenerPrimeraFila($consulta);



		$idUsuario=$fBase[2];
		$consulta="SELECT conceptoIngresoVinculado FROM _721_tablaDinamica WHERE id__721_tablaDinamica=".$fBase[0];
		
		$idConcepto=$con->obtenerValor($consulta);

		$consulta="SELECT fechaAplicacion FROM 4580_calendarioExamenesGrupo WHERE idGrupo=".$fBase[1]." AND tipoExamen=".$fBase[0]." AND noExamen=".$fBase[3];

		$fechaVencimiento=$con->obtenerValor($consulta);
		
		$idFormulario=736;	
		$consulta="SELECT Plantel,idInstanciaPlanEstudio,idCiclo,idPeriodo FROM 4520_grupos WHERE idGrupos=".$fBase[1];
		$fGrupo=$con->obtenerPrimeraFila($consulta);
		
		
		$idInstanciaPlan=$fGrupo[1];
		$idCiclo=$fGrupo[2];
		$idPeriodo=$fGrupo[3];
		$consulta="SELECT p.idProgramaEducativo,i.sede FROM 4513_instanciaPlanEstudio i,4500_planEstudio p WHERE p.idPlanEstudio=i.idPlanEstudio AND i.idInstanciaPlanEstudio=".$idInstanciaPlan;
		$fInstancia=$con->obtenerPrimeraFila($consulta);
		
		$plantel=$fInstancia[1];
		
		$arrFechasPago=array();
		$idProgramaEducativo=$fInstancia[0];
		
		$idGradoInscribe=-1;
		
		$arrDimensiones=array();
		$arrDimensionesCosto["plantel"]=$plantel;
		$arrDimensionesCosto["idProgramaEducativo"]=$idProgramaEducativo;
		$arrDimensionesCosto["idInstanciaPlanEstudio"]=$idInstanciaPlan;
		$arrDimensionesCosto["grado"]=-1;
		
		$oParametroCosto=array();
		$oParametroCosto["idUsuario"]=$idUsuario;
		$idPlanPagosAplica=-1;//Si -1 Aplica todos los planes
		$tabulador=generarTabuladorCostoServicio($idConcepto,$idCiclo,$idPeriodo,$arrDimensionesCosto,$oParametroCosto); 
		$tabulador["fechaVencimiento"]=$fechaVencimiento;
		
		$arrDimensionesPagoReferenciado=array();
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IdInstanciaPlan",$idInstanciaPlan);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDCicloEscolar",$idCiclo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDPeriodo",$idPeriodo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDFormulario",$idFormulario);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDRegistro",$idRegistro);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"Plantel",$plantel);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDProgramaEducativo",$idProgramaEducativo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDGrado",$idGradoInscribe);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDUsuario",$idUsuario);

		$arrTabulador=generarFechasPagosServicio($idConcepto,$tabulador);
		
		generarPagosReferenciados($plantel,$idUsuario,$arrTabulador,$arrDimensionesPagoReferenciado,"pagoExamenRealizado",0);
		$referencia=obtenerReferenciaPagoFormulario($idFormulario,$idRegistro,$idConcepto,$idUsuario);
		echo "
					window.parent.mostrarPagoReferencia(".$referencia.");
				";
		
		return true;
	}
	
	function pagoExamenRealizado($idMovimiento)
	{
		global $con;
		$consulta="SELECT valorCampo FROM 6012_detalleAsientoPago WHERE idAsientoPago=".$idMovimiento." AND idDimension=11";

		$idFormulario=$con->obtenerValor($consulta);
		$consulta="SELECT valorCampo FROM 6012_detalleAsientoPago WHERE idAsientoPago=".$idMovimiento." AND idDimension=12";
		$idRegistro=$con->obtenerValor($consulta);
		
		cambiarEtapaFormulario($idFormulario,$idRegistro,4);
	}
	
	function accionSeguimientoRegistrado($idFormulario,$idRegistro)
	{
		global $con;
		$consulta="SELECT resultadAccion,fechaCita FROM _1010_tablaDinamica WHERE id__1010_tablaDinamica=".$idRegistro;
		$fResultado=$con->obtenerPrimeraFila($consulta);
		$consulta="SELECT accionDesencadena,solicitaDatosAgenda FROM _1008_gridResultadosAccion WHERE id__1008_gridResultadosAccion=".$fResultado[0];
		$fDes=$con->obtenerPrimeraFila($consulta);
		if($fDes[0]!="")
		{
			$fAccion=date("Y-m-d");
			if(($fDes[1]=="1")&&($fResultado[1]!=""))
			{
				$fAccion=$fResultado[1];	
			}
			$situacion=1;
			if($fDes[0]==5)
				$situacion=2;
			$consulta="INSERT INTO _1010_tablaDinamica(idReferencia,fechaCreacion,responsable,idEstado,codigoUnidad,codigoInstitucion,fechaAccion,accionSeguimiento)
						SELECT idReferencia,fechaCreacion,responsable,".$situacion.",codigoUnidad,codigoInstitucion,'".$fAccion."' AS fechaAccion,".$fDes[0]." AS accionSeguimiento FROM _1010_tablaDinamica WHERE id__1010_tablaDinamica=".$idRegistro;
			$con->ejecutarConsulta($consulta);
		}
		echo "window.parent.regresar1Pagina(false);";
		cambiarEtapaFormulario($idFormulario,$idRegistro,2);
		
		return true;
		
	}
	
	function evaluarReinscripcion($idFormulario,$idRegistro)
	{
		global $con;
		$consulta="SELECT tipoInscripcion  FROM _910_tablaDinamica WHERE id__910_tablaDinamica=".$idRegistro;
		$tInscripcion=$con->obtenerValor($consulta);
		switch($tInscripcion)
		{
			case 1:
				cambiarEtapaFormulario($idFormulario,$idRegistro,"2");
			break;
			case 2:
			case 3:
				cambiarEtapaFormulario($idFormulario,$idRegistro,"1.5");		
			break;	
		}
			
	}
	
	function registrarReestructuracion($idFormulario,$idRegistro)
	{
		global $con;
		$idConcepto=93;
		$consulta="SELECT cmbAlumno,cmbInstanciaPlan FROM _1006_tablaDinamica WHERE id__1006_tablaDinamica=".$idRegistro;
		$fConvenio=$con->obtenerPrimeraFila($consulta);
		$idUsuario=$fConvenio[0];
		$consulta="SELECT i.sede,p.idProgramaEducativo FROM 4513_instanciaPlanEstudio i,4500_planEstudio p WHERE idInstanciaPlanEstudio=".$fConvenio[1]." AND p.idPlanEstudio=i.idPlanEstudio";
		$fInstancia=$con->obtenerPrimeraFila($consulta);

		$idProgramaEducativo=$fInstancia[1];
		$plantel=$fInstancia[0];
		$x=0;
		$query[$x]="begin";
		$x++;
		
		$consulta="SELECT * FROM 3006_datosReestructura WHERE idFormulario=".$idFormulario." AND idReferencia=".$idRegistro;

		$idReestructura=$con->obtenerValor($consulta);

		$consulta="SELECT idConcepto FROM 3007_conceptosConsiderarReestructura WHERE idReestructura=".$idReestructura;
		$resCon=$con->obtenerFilas($consulta);
		while($fConsulta=mysql_fetch_row($resCon))
		{
			$query[$x]="UPDATE 6011_movimientosPago SET situacion=0 WHERE idMovimiento=".$fConsulta[0];	
			$x++;
		}
		$arrTabulador=array();	
		$arrDimensionesPagoReferenciado=array();
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IdInstanciaPlan",$fConvenio[1]);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDFormulario",$idFormulario);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDRegistro",$idRegistro);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"Plantel",$plantel);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDProgramaEducativo",$idProgramaEducativo);
		agregarDimensionArreglo($arrDimensionesPagoReferenciado,"IDUsuario",$idUsuario);	
			
		
		$consulta="SELECT * FROM 3008_pagosReestructura WHERE idReestructura=".$idReestructura;
		$resCon=$con->obtenerFilas($consulta);
		while($fConsulta=mysql_fetch_row($resCon))
		{
			$obj["idConcepto"]=$idConcepto;
			$obj["etiquetaPago"]=$fConsulta[1];
			$obj["fechaVencimiento"]=$fConsulta[3];
			$obj["arrFechasPago"]=array();
			$fechaInicio=date("Y-m-d");
			if($fConsulta[5]!="")
			{
				$oPago=array();
				$oPago["monto"]=$fConsulta[4];
				$oPago["fechaInicio"]=date("Y-m-d");
				$oPago["fechaFin"]=$fConsulta[5];	
				array_push($obj["arrFechasPago"],$oPago);
				$fechaInicio=date("Y-m-d",strtotime("+1 days",strtotime($fConsulta[5])));
			}
			
			$oPago=array();
			$oPago["monto"]=$fConsulta[2];
			$oPago["fechaInicio"]=$fechaInicio;
			$oPago["fechaFin"]="";	
			array_push($obj["arrFechasPago"],$oPago);
			array_push($arrTabulador,$obj);
		}	

		generarPagosReferenciados($plantel,$idUsuario,$arrTabulador,$arrDimensionesPagoReferenciado,"",0);
		$query[$x]="commit";
		$x++;	
		return $con->ejecutarBloque($query);
	}
	
	function generarCFDINomina($idRegistro) 
	{
		global $con;
		$consulta="SELECT id__1012_gridNomina FROM _1012_gridNomina WHERE idReferencia=".$idRegistro;
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$oNomina=generarXMLNominaPrimaria($fila[0]);	
			
			
			$c=new cNominaCFDI();
			$c->setObjNomina($oNomina);
			$XML=$c->generarXML();
			$idFactura=$c->registrarXML(3,$fila[0]);
			$c->generarSelloDigital();
		
			$XML=$c->cargarComprobanteXML($idFactura);

			$resultado=$c->validarXMLNomina($XML);
			if($resultado["errores"])
			{
				$consulta="UPDATE 703_relacionFoliosCFDI SET situacion=5,comentarios='".cv($resultado["arrErrores"])."' WHERE idFolio=".$idFactura;
				$con->ejecutarConsulta($consulta);
			}
		}
		
		return true;
			
	}
	
	function bloquearUsuariosNomina($idNomina)
	{
		global $con;
		$x=0;
		$consulta[$x]="begin";
		$x++;
		
		$query="SELECT DISTINCT idUsuario FROM 671_asientosCalculosNomina a,703_relacionFoliosCFDI r WHERE r.idFolio=a.idComprobante  AND r.situacion=2 and a.idNomina=".$idNomina;
		$res=$con->obtenerFilas($query);
		while($fila=mysql_fetch_row($res))
		{
			$consulta[$x]="UPDATE 802_identifica SET bloqueadoNomina=1 WHERE idUsuario=".$fila[0];
			$x++;
		}
		$consulta[$x]="commit";
		$x++;
		return $con->ejecutarBloque($consulta);
		
	}
	
	function cancelarJustificacionFalta($idRegistro)
	{
		global $con;
		$consulta="SELECT cmbFalta FROM _481_tablaDinamica WHERE id__481_tablaDinamica=".$idRegistro;	
		$idFalta=$con->obtenerValor($consulta);
		
		$consulta="SELECT pagado,idNomina,idUsuario,fechaFalta,horaInicial,horaFinal,idGrupo FROM 4559_controlDeFalta WHERE idFalta=".$idFalta;
		$fFalta=$con->obtenerPrimeraFila($consulta);
		
		
		if($fFalta[0]==2)
		{
			$consulta="SELECT nombreGrupo FROM 4520_grupos WHERE idGrupos=".$fFalta[6];
			$nombreGrupo=$con->obtenerValor($consulta);
			$notificacion="se ha cancelado la justificación de la falta del profesor: ".cv(obtenerNombreUsuario($fFalta[2]))." en el grupo: ".cv($nombreGrupo)." de la sesión del día: ".
						date("d/m/Y",strtotime($fFalta[3]))." de las ".date("H:i",strtotime($fFalta[4]))." a las ".date("H:i",strtotime($fFalta[5]));
			registrarNotificacionNomina($fFalta[1],$notificacion);	
		}
		
		$consulta="UPDATE 4559_controlDeFalta SET estadoFalta=0,idRegistroJustificacion=NULL WHERE idFalta=".$idFalta;
		return $con->ejecutarConsulta($consulta);
		
		
		
	}
	
	function cancelarComisionAutorizada($idRegistro)
	{
		global $con;
		$arrFechasProfesor=array();
		$x=0;
		$query[$x]="begin";
		$x++;
		
		$idUsuario=-1;
		$consulta="SELECT codigo,cmbDocentes,codigoInstitucion FROM _489_tablaDinamica WHERE id__489_tablaDinamica=".$idRegistro;
		$fSolicitudComision=$con->obtenerPrimeraFila($consulta);
		$folioComision=$fSolicitudComision[0];
		$idUsuario=$fSolicitudComision[1];
		$plantel=$fSolicitudComision[2];
		
		$consulta="SELECT idRegistroModuloSesionesClase FROM 4560_registroModuloSesionesClase WHERE idFormulario=489 AND idReferencia=".$idRegistro;
		$idReferencia=$con->obtenerValor($consulta);
		if($idReferencia=="")
			$idReferencia=-1;
		
		$situacion=10;
		$consulta="select * from 4561_sesionesClaseModulo WHERE idReferencia=".$idReferencia." AND aplicado=1" ;
		
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$consulta="SELECT f.idFalta,f.pagado,f.idNomina,f.idUsuario,t.id__481_tablaDinamica FROM _481_tablaDinamica t,4559_controlDeFalta f WHERE t.idSolicitudComision=".$idRegistro." AND f.idFalta=t.cmbFalta
						AND f.idGrupo=".$fila[1]." AND f.fechaFalta='".$fila[4]."' AND f.horaInicial='".$fila[2]."'";
			$fFalta=$con->obtenerPrimeraFila($consulta);
			if($fFalta)
			{
				switch($fFalta[0])								
				{
					case 0:
						$query[$x]="DELETE FROM 4559_controlDeFalta WHERE idFalta=".$fFalta[0];
						$x++;
						
					break;
					case 1:
						$situacion=11;
					break;
					case 2:
						$query[$x]="DELETE FROM 4559_controlDeFalta WHERE idFalta=".$fFalta[0];
						$x++;
						$notificacion="Se han reprocedado los eventos del profesor: ".obtenerNombreUsuario($fFalta[3])." debido a la cancelación de la comisión con folio: ".$folioComision;
						registrarNotificacionNomina($fFalta[2],$notificacion);
					break;	
				}
				
				
				$query[$x]="UPDATE _481_tablaDinamica SET idEstado=6 WHERE id__481_tablaDinamica=".$fila[4];
				$x++;
				
				$arrFechasProfesor[$fila[4]]=1;
				
			}
			$query[$x]="UPDATE 4561_sesionesClaseModulo SET aplicado=".$situacion." WHERE idSesionesClaseComision=".$fila[0];
			$x++;
		}
		$query[$x]="commit";
		$x++;
		if($con->ejecutarBloque($query))
		{
			foreach($arrFechasProfesor as $fecha=>$resto)
			{
				if(strtotime($fecha)<=strtotime(date("Y-m-d")))
					reprocesarEventoUsuario($idUsuario,$fecha,$plantel,true);		
			}
			return true;	
		}
	}
	
	function aplicarHorarVirtual($idFormulario,$idRegistro)
	{
		global $con;
		$consulta="SELECT profesor,grupo FROM _1032_tablaDinamica WHERE id__1032_tablaDinamica=".$idRegistro;
		$fRegistro=$con->obtenerPrimeraFila($consulta);
		
		
		$consulta="SELECT * FROM 3009_sesionesVirtuales WHERE idFormulario=".$idFormulario." AND idReferencia=".$idRegistro;
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			
		}
		
		return true;
		
		
	}
?>