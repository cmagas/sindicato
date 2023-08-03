<?php

include_once("latis/conexionBD.php");
	include_once("latis/utiles.php");
 	
	function existeDisponibilidadHabitacion($habitacion,$fechaInicio,$fechaFin,$iRegistro)
 	{
	 	global $con;
		
		$arrConflictos=array();
		$fInicio=$fechaInicio." 15:00";
		$fFin=$fechaFin." 14:59:59";
		
		$consulta="SELECT t.id__670_tablaDinamica AS idRegistro,'670' AS iFormulario,t.fechaIngreso,t.fechaSalida,'2' AS inicioMantenimiento,
					'1' AS finMantenimiento,t.codigo  FROM _670_asignacionHabitaciones a,_670_tablaDinamica t WHERE t.id__670_tablaDinamica=a.iReferencia 
					AND a.idHabitacion='".$habitacion."' AND t.idEstado=2 and ".generarConsultaIntervalos($fechaInicio,$fechaFin,"t.fechaIngreso",
					"t.fechaSalida ",true)." AND t.id__670_tablaDinamica<>".$iRegistro;	
		$consulta.=" union
					SELECT t.id__660_tablaDinamica AS idRegistro,'660' AS iFormulario,fechaInicio AS fechaIngreso,fechaFinalizacion AS fechaSalida,
					t.inicioMantenimiento,t.finMantenimiento,t.codigo FROM _660_habitacionesInvolucradas a,_660_tablaDinamica t 
					WHERE t.id__660_tablaDinamica=a.idReferencia AND a.habitacion='".$habitacion."' AND t.idEstado=2 
					AND ".generarConsultaIntervalos($fechaInicio,$fechaFin,"fechaInicio","fechaFinalizacion ",true);
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$fechaIngresoSolicitud=$fila[2];
			$fechaSalidaSolicitud=$fila[3];
			
			if($fila[1]==670)
			{
				$fechaIngresoSolicitud.=" 15:00:00";
				$fechaSalidaSolicitud.=" 14:59:59";
			}
			else
			{
				if($fila[4]==1)
					$fechaIngresoSolicitud.=" 00:00:01";
				else
					$fechaIngresoSolicitud.=" 15:00:00";
				
				if($fila[5]==1)
					$fechaSalidaSolicitud.=" 14:59:59";
				else
					$fechaSalidaSolicitud.=" 23:59:59";
			}
			
			/*if($habitacion==2)
			{
				varDUmp($fechaIngresoSolicitud." ".$fechaIngresoSolicitud);
			}*/
			
			if(colisionaTiempo($fechaIngresoSolicitud,$fechaSalidaSolicitud,$fInicio,$fFin,true))
			{
				array_push($arrConflictos,$fila);
			}
		}
		return $arrConflictos;
 	}
	
	
	//function generarFolioProcesos($idFormulario,$idRegistro)
//	{
//		global $con;
//		
//		$anio=date("Y");
//		
//		$query="begin";
//		if($con->ejecutarConsulta($query))
//		{
//			$query="select folioActual FROM 7003_administradorFoliosProcesos WHERE idFormulario=".$idFormulario." AND anio=".$anio." for update";
//			$folioActual=$con->obtenerValor($query);
//			if($folioActual=="")
//			{
//				$folioActual=1;
//				
//				$query="INSERT INTO 7003_administradorFoliosProcesos(idFormulario,anio,folioActual) VALUES(".$idFormulario.",".$anio.",".$folioActual.")";
//				
//			}
//			else
//			{
//				$folioActual++;
//				$query="update 7003_administradorFoliosProcesos set folioActual=".$folioActual." where idFormulario=".$idFormulario." and anio=".$anio;
//			}
//				
//			if($con->ejecutarConsulta($query))
//			{
//				$query="commit";
//				$con->ejecutarConsulta($query);
//				
//				
//				
//				return str_pad($folioActual,5,"0",STR_PAD_LEFT)."/".$anio;
//				
//			}
//				
//			
//		}
//		
//		return 0;
//		
//	}
	
	function validarDisponibilidadBloqueHabitaciones($idFormulario,$idRegistro)
	{
		global $con;
		$cadRes="";
		$consulta="SELECT * FROM _1157_tablaDinamica WHERE id__1157_tablaDinamica=".$idRegistro;
		$fRegistro=$con->obtenerPrimeraFilaAsoc($consulta);

		$fechaInicio=$fRegistro["fechaInicio"];
		$tInicio=$fRegistro["inicioMantenimiento"];
		if($tInicio==1)
			$fechaInicio.=" 00:00:01";
		else
			$fechaInicio.=" 15:00:00";
		
		
		$fechaFin=$fRegistro["fechaFinal"];
		$tFin=$fRegistro["finMantenimiento"];
		
		if($tFin==1)
			$fechaFin.=" 14:59:59";
		else
			$fechaFin.=" 23:59:59";
		
		$fInicio=($fechaInicio);
		$fFin=($fechaFin);
		
		$consulta="SELECT habitacion FROM _1157_gHabitaciones WHERE idReferencia=".$idRegistro;
		$rAsignacion=$con->obtenerFilas($consulta);
		while($fAsignacion=mysql_fetch_row($rAsignacion))
		{
			$consulta="SELECT t.id__1160_tablaDinamica,t.fechaIngreso,t.fechaSalida,t.codigo
					FROM _1160_asignacionHabitaciones a,_1160_tablaDinamica t WHERE t.id__1160_tablaDinamica=a.iReferencia
					AND a.idHabitacion=".$fAsignacion[0].
					" AND t.idEstado=2 and ".generarConsultaIntervalos($fechaInicio,$fechaFin,"t.fechaIngreso","t.fechaSalida ",true);					
			
			$res=$con->obtenerFilas($consulta);
			while($fila=mysql_fetch_assoc($res))
			{
				
				$fechaIngresoSolicitud=($fila["fechaIngreso"]." 15:00");
				$fechaSalidaSolicitud=($fila["fechaSalida"]." 14:59:59");
				
				if(colisionaTiempo($fechaIngresoSolicitud,$fechaSalidaSolicitud,$fInicio,$fFin,true))
				{

					$consulta="SELECT nombreHabitacion FROM _1103_tablaDinamica WHERE id__1103_tablaDinamica=".$fAsignacion[0];
					$nombreHabitacion=$con->obtenerValor($consulta);
					$o="['Registro de incidencia','No se puede deshabilitar la habitaci&oacute;n <b>".$nombreHabitacion."</b> ya que se encuentra asignada a la reservaci&oacute;n: <b>".$fila[6]."</b>']";
					if($cadRes=="")
						$cadRes=$o;
					else
						$cadRes.=",".$o;
				}
				
				
				
			}
		}
		
		
		
		
		return "[".$cadRes."]";	
	}
	
	function validarDisponibilidadHabitacionesReserva($idFormulario,$idRegistro)
	{
		global $con;
		$cadRes="";
		$consulta="SELECT * FROM _1160_tablaDinamica WHERE id__1160_tablaDinamica=".$idRegistro;
		$fRegistro=$con->obtenerPrimeraFilaAsoc($consulta);

		$consulta="SELECT idHabitacion FROM _1160_asignacionHabitaciones WHERE iFormulario=1160 AND iReferencia=".$fRegistro["id__1160_tablaDinamica"];
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$arrResultado=existeDisponibilidadHabitacion($fila[0],$fRegistro["fechaIngreso"],$fRegistro["fechaSalida"],$idRegistro);
			foreach($arrResultado as $r)
			{
				$consulta="SELECT nombreHabitacion FROM _1103_tablaDinamica WHERE id__1103_tablaDinamica=".$fila[0];
				$nombreHabitacion=$con->obtenerValor($consulta);
				$o="";
				if($r[1]==1160)
					$o="['Registro de hospedaje','No se puede reservar la habitaci&oacute;n <b>".$nombreHabitacion."</b> debido a que se encuentra asignada a la reservaci&oacute;n: <b>".$r[6]."</b>']";
				else					
					$o="['Registro de hospedaje','No se puede reservar la habitaci&oacute;n <b>".$nombreHabitacion."</b> debido a que se encuentra marcada como NO disponible, folio de incidencia: <b>".$r[6]."</b>']";
				if($cadRes=="")
					$cadRes=$o;
				else
					$cadRes.=",".$o;
			}
			
			
		}
		
		
		
		return "[".$cadRes."]";	
	}






?>