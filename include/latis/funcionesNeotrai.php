<?php	 
	include_once("latis/funcionesSistemaNomina.php");
	include_once("latis/numeroToLetra.php");
	include_once("latis/ugm/funcionesRH.php");
	
	function esGrupoCerrado($idInstancia)
	{
		global $con;
		$consulta="SELECT idEsquemaGrupo FROM 4513_instanciaPlanEstudio WHERE idInstanciaPlanEstudio=".$idInstancia;
		$idEsquema=$con->obtenerValor($consulta);
		
		if($idEsquema==1)
			return false;
		return true;
	}
	
	function crearGruposAsociados($idInstancia,$idGrupo,$codigoPadre=null)
	{
		global $con;
		$consulta="select idPlanEstudio,sede from 4513_instanciaPlanEstudio WHERE idInstanciaPlanEstudio=".$idInstancia;
		$filaIns=$con->obtenerPrimeraFila($consulta);
		$idPlanEstudio=$filaIns[0];
		$plantel=$filaIns[1];
		$consulta="select * from 4540_gruposMaestros where idGrupoPadre=".$idGrupo;
		$filaGpo=$con->obtenerPrimeraFila($consulta);
		$x=0;
		$query[$x]="begin";
		$x++;
		$query[$x]="set @idGrupoAgrupador:=0";
		$x++;
		$consulta="";
		if($codigoPadre==null)
			$consulta="select codigoUnidad FROM 4505_estructuraCurricular WHERE (codigoPadre='' OR codigoPadre IS NULL) AND idPlanEstudio=".$idPlanEstudio;
		else
			$consulta="select codigoUnidad FROM 4505_estructuraCurricular WHERE codigoUnidad='".$codigoPadre."' and idPlanEstudio=".$idPlanEstudio;
		$resGrados=$con->obtenerFilas($consulta);
		while($filaGrado=mysql_fetch_row($resGrados))
		{
			crearGrupoMateriaObligatoria($filaGrado[0],$idPlanEstudio,$query,$x,$filaGpo[6],$idInstancia,$filaGpo,$plantel);	
		}
		$query[$x]="commit";
		$x++;
		return $con->ejecutarBloque($query);
	}
	
	function crearGrupoMateriaObligatoria($codigoUnidadPadre,$idPlanEstudio,&$consulta,&$x,$Ciclo,$idInstancia,$filaGpo,$plantel)
	{
		global $con;
		$query="SELECT idUnidad,codigoUnidad,tipoUnidad,idEstructuraCurricular FROM 4505_estructuraCurricular where tipoUnidad IN (1,2) AND naturalezaMateria=1
				AND idPlanEstudio=".$idPlanEstudio." AND codigoPadre='".$codigoUnidadPadre."'";
		$res=$con->obtenerFilas($query);
		$fechaInicio="NULL";
		$fechaFin="NULL";
		
		$idCiclo=$filaGpo[6];
		$idPeriodo=$filaGpo[7];
		$idInstanciaPlan=$filaGpo[3];
		if($idCiclo=="")
			$idCiclo=-1;
		if($idPeriodo=="")
			$idPeriodo=-1;
		
		$query="SELECT fechaInicial,fechaFinal FROM 4544_fechasPeriodo WHERE idCiclo=".$idCiclo." and idPeriodo=".$idPeriodo." and idInstanciaPlanEstudio='".$idInstancia."'";
		$fFechas=$con->obtenerPrimeraFila($query);
		if(($fFechas)&&($fFechas[0]!=""))
		{
			$fechaInicio="'".$fFechas[0]."'";
			

		}
		$unidadAgrupadora=false;
		while($fila=mysql_fetch_row($res))
		{
			$query="SELECT * FROM 4552_intercambiosMateria WHERE idElementoOrigen=".$fila[3]." AND idCiclo=".$idCiclo." AND idPeriodo=".$idPeriodo;
			$fMateria=$con->obtenerPrimeraFila($query);
			if(!$fMateria)
			{
				if($fila[2]==1)
				{
					if($fechaInicio!="NULL")
					{
						$query="SELECT horasSemana,horaMateriaTotal FROM 4502_Materias WHERE idMateria=".$fila[0];
						$filaMat=$con->obtenerPrimeraFila($query);			
						$hTotal=$filaMat[1];
						$hSemana=$filaMat[0];
						$query="SELECT noHorasSemana FROM 4512_aliasClavesMateria WHERE idInstanciaPlanEstudio=".$idInstancia." AND idMateria=".$fila[0];
						$noHoras=$con->obtenerValor($query);
						if($noHoras!="")
							$hSemana=$noHoras;
						if($noHoras==0)
							$numSemanas=0;
						else
							$numSemanas=ceil($hTotal/$noHoras);
						$fechaFin=date("Y-m-d",strtotime("+".($numSemanas)." week",strtotime($fFechas[0])));
						if($fFechas[1]!="")
						{
							if(strtotime($fechaFin)>strtotime($fFechas[1]))
							{
								$fechaFin="'".$fFechas[1]."'";
							}
							else
								$fechaFin="'".date("Y-m-d",strtotime("-1 day",strtotime($fechaFin)))."'";
						}
						else
							$fechaFin="'".date("Y-m-d",strtotime("-1 day",strtotime($fechaFin)))."'";
					}
					$nGrupo=generarNombreGrupo($filaGpo[6],$fila[0],$filaGpo[3],$filaGpo[0]);
					
					$queryGpoMaestro="SELECT idGradoCiclo FROM 4540_gruposMaestros WHERE idGrupoPadre=".$filaGpo[0];
					$idGradoCiclo=$con->obtenerValor($queryGpoMaestro);
					
					$consulta[$x]="insert into 4520_grupos(idPlanEstudio,Plantel,idMateria,nombreGrupo,cupoMinimo,cupoMaximo,situacion,idCiclo,idInstanciaPlanEstudio,idGrupoPadre,fechaInicio,fechaFin,idPeriodo,idGrupoAgrupador,idGradoCiclo)
									VALUES(".$idPlanEstudio.",'".$plantel."',".$fila[0].",'".$nGrupo."',".$filaGpo[4].",".$filaGpo[5].",".$filaGpo[2].",".$filaGpo[6].
									",".$filaGpo[3].",".$filaGpo[0].",".$fechaInicio.",".$fechaFin.",".$idPeriodo.",@idGrupoAgrupador,".$idGradoCiclo.")";
							
					$x++;
				}
				else
				{
					if($fila[2]==2)
					{
						$query="SELECT nombreUnidad,tipoUnidad FROM 4508_unidadesContenedora WHERE idUnidadContenedora=".$fila[0];
						$fContenedor=$con->obtenerPrimeraFila($query);
						if($fContenedor[1]==1)
						{
							if($fFechas[1]!="")	
								$fechaFin="'".$fFechas[1]."'";
							else
								$fechaFin="NULL";
								
							$queryGpoMaestro="SELECT idGradoCiclo FROM 4540_gruposMaestros WHERE idGrupoPadre=".$filaGpo[0];
							$idGradoCiclo=$con->obtenerValor($queryGpoMaestro);	
								
							$nGrupo=generarNombreGrupo($filaGpo[6],"-".$fila[0],$filaGpo[3],$filaGpo[0]);
							$consulta[$x]="insert into 4520_grupos(idPlanEstudio,Plantel,idMateria,nombreGrupo,cupoMinimo,cupoMaximo,situacion,idCiclo,idInstanciaPlanEstudio,idGrupoPadre,fechaInicio,fechaFin,idPeriodo,idGrupoAgrupador,idGradoCiclo)
									VALUES(".$idPlanEstudio.",'".$plantel."',-".$fila[0].",'".$nGrupo."',".$filaGpo[4].",".$filaGpo[5].",".$filaGpo[2].",".$filaGpo[6].
									",".$filaGpo[3].",".$filaGpo[0].",".$fechaInicio.",".$fechaFin.",".$idPeriodo.",@idGrupoAgrupador,".$idGradoCiclo.")";
							
							$x++;
							$consulta[$x]="set @idGrupoAgrupador:=(select last_insert_id())";
							$x++;
							$unidadAgrupadora=true;

						}
					}
				}
				crearGrupoMateriaObligatoria($fila[1],$idPlanEstudio,$consulta,$x,$Ciclo,$idInstancia,$filaGpo,$plantel);
				if($unidadAgrupadora)
				{
					$consulta[$x]="set @fechaFin:=(SELECT fechaFin FROM 4520_grupos WHERE idGrupos=@idGrupoAgrupador)";
					$x++;
					$consulta[$x]="UPDATE 4520_grupos SET situacion=5,fechaFin=@fechaFin WHERE idGrupoAgrupador=@idGrupoAgrupador";
					$x++;
					$consulta[$x]="set @idGrupoAgrupador:=0";
					$x++;
					$unidadAgrupadora=false;
				}
			}
			else
			{
				$query="SELECT codigoUnidad,idUnidad FROM 4505_estructuraCurricular WHERE idEstructuraCurricular=".$fMateria[2];
				$fDestino=$con->obtenerPrimeraFila($query);
				$idGrupoAgrupador=0;
				crearGrupoMateriaUnica($idInstancia,$fDestino[1],$filaGpo[0],$fila[0],$fMateria[0],$idGrupoAgrupador);
			}
		}
	}
	
	function crearGrupoMateriaUnica($idInstanciaPlan,$idMateria,$idGrupoPadre,$idMateriaReemplazo="NULL",$idRegistroReemplazo="NULL",$idGrupoAgrupador=0)
	{
		global $con;
		$consulta="SELECT * FROM 4540_gruposMaestros WHERE idGrupoPadre=".$idGrupoPadre;
		$filaGpo=$con->obtenerPrimeraFila($consulta);
		$idCiclo=$filaGpo[6];
		$idPeriodo=$filaGpo[7];
		$consulta="SELECT sede,idPlanEstudio FROM 4513_instanciaPlanEstudio WHERE idInstanciaPlanEstudio=".$idInstanciaPlan;
		$fPlanEstudio=$con->obtenerPrimeraFila($consulta);
		$idPlanEstudio=$fPlanEstudio[1];
		$plantel=$fPlanEstudio[0];
		$fechaInicio="NULL";
		$fechaFin="NULL";
		$query="SELECT fechaInicial,fechaFinal FROM 4544_fechasPeriodo WHERE idCiclo=".$idCiclo." and idPeriodo=".$idPeriodo." and idInstanciaPlanEstudio='".$idInstanciaPlan."'";

		$fFechas=$con->obtenerPrimeraFila($query);
		if(($fFechas)&&($fFechas[0]))
		{
			$fechaInicio="'".$fFechas[0]."'";
		}
		
		if($fechaInicio!="NULL")
		{
			$query="SELECT horasSemana,horaMateriaTotal FROM 4502_Materias WHERE idMateria=".$idMateria;
			$filaMat=$con->obtenerPrimeraFila($query);			
			$hTotal=$filaMat[1];
			$hSemana=$filaMat[0];
			$query="SELECT noHorasSemana FROM 4512_aliasClavesMateria WHERE idInstanciaPlanEstudio=".$idInstanciaPlan." AND idMateria=".$idMateria;
			$noHoras=$con->obtenerValor($query);
			if($noHoras!="")
				$hSemana=$noHoras;
			if($noHoras==0)
				$numSemanas=0;
			else
				$numSemanas=ceil($hTotal/$noHoras);
			$fechaFin=date("Y-m-d",strtotime("+".($numSemanas)." week",strtotime($fFechas[0])));
			
			if($fFechas[1]!="")
			{
				if(strtotime($fechaFin)>strtotime($fFechas[1]))
				{
					$fechaFin="'".$fFechas[1]."'";
				}
				else
					$fechaFin="'".date("Y-m-d",strtotime("-1 day",strtotime($fechaFin)))."'";
			}
			else
				$fechaFin="'".date("Y-m-d",strtotime("-1 day",strtotime($fechaFin)))."'";
			
		}
		$nGrupo=generarNombreGrupo($idCiclo,$idMateria,$idInstanciaPlan,$idGrupoPadre);
		$queryGpoMaestro="SELECT idGradoCiclo FROM 4540_gruposMaestros WHERE idGrupoPadre=".$filaGpo[0];
		$idGradoCiclo=$con->obtenerValor($queryGpoMaestro);	
		$query="insert into 4520_grupos(idPlanEstudio,Plantel,idMateria,nombreGrupo,cupoMinimo,cupoMaximo,situacion,idCiclo,idInstanciaPlanEstudio,
						idGrupoPadre,fechaInicio,fechaFin,idPeriodo,idMateriaReemplazo,idRegistroReemplazo,idGrupoAgrupador,idGradoCiclo)
						VALUES(".$idPlanEstudio.",'".$plantel."',".$idMateria.",'".$nGrupo."',".$filaGpo[4].",".$filaGpo[5].",".$filaGpo[2].",".$filaGpo[6].
						",".$filaGpo[3].",".$filaGpo[0].",".$fechaInicio.",".$fechaFin.",".$idPeriodo.",".$idMateriaReemplazo.",".$idRegistroReemplazo.",".$idGrupoAgrupador.",".$idGradoCiclo.")";
						
						
		$con->ejecutarConsulta($query);
		
	}
	
	function actualizarGruposMateriaObligatoria($idGrupo)
	{
		global $con;
		$consulta="select * from 4540_gruposMaestros where idGrupoPadre=".$idGrupo;
		$filaGpo=$con->obtenerPrimeraFila($consulta);
		$consulta="select * from 4520_grupos where idGrupoPadre=".$idGrupo;
		$res=$con->obtenerFilas($consulta);
		$x=0;
		$query[$x]="begin";
		$x++;
		while($fila=mysql_fetch_row($res))
		{
			$nGrupo='["'.$filaGpo[1].'"]';
			$nombreGrupo=$fila[4];
			if(strpos($nombreGrupo,"[")!==false)
			{
				$datos=explode("[",$nombreGrupo);
				$nombreGrupo=$datos[0]."[".$filaGpo[1]."]";
				$datos=explode("]",$datos[1]);
				if(sizeof($datos)>1)	
					$nombreGrupo.=$datos[1];
			}
			else
				$nombreGrupo=$nombreGrupo." [".$filaGpo[1]."]";
			$query[$x]="update 4520_grupos set nombreGrupo='".$nombreGrupo."', cupoMinimo=".$filaGpo[4].",cupoMaximo=".$filaGpo[5].",situacion=".$filaGpo[2]." where idGrupos=".$fila[0];	
			$x++;
		}
		$query[$x]="commit";
		$x++;
		return $con->ejecutarBloque($query);
	}
	
	function eliminarGruposMateriaObligatoria($idGrupo)
	{
		global $con;
		$consulta="delete from  4520_grupos where idGrupoPadre=".$idGrupo;
		return $con->ejecutarConsulta($consulta);
	}
	
	function inscribirAlumnoGradoGrupo($idAlumno,$idGrado,$Ciclo,$idGrupo)
	{
		global $con;
		$consulta="SELECT idInstanciaPlanEstudio FROM 4540_gruposMaestros WHERE idGrupoPadre=".$idGrupo;
		$idInstanciaPlan=$con->obtenerValor($consulta);
		$consulta="SELECT codigoUnidad,idPlanEstudio FROM 4505_estructuraCurricular WHERE tipoUnidad=3 AND idUnidad=".$idGrado;
		$fila=$con->obtenerPrimeraFila($consulta);
		$codigoUnidad=$fila[0];
		$idPlanEstudio=$fila[1];
		$consulta="INSERT INTO 4529_alumnos(ciclo,idInstanciaPlanEstudio,idGrado,idUsuario,estado,idGrupo)VALUES('".$Ciclo."','".$idInstanciaPlan."'
								,'".$idGrado."','".$idAlumno."','1',".$idGrupo.")";
		if($con->ejecutarConsulta($consulta))
			return asociarAlumnoGrupoMateria($codigoUnidad,$idAlumno,$idGrupo,$idPlanEstudio);
		
	}
	
	function asociarAlumnoGrupoMateria($codigoPadre,$idAlumno,$idGrupo,$idPlanEstudio)
	{
		global $con;
		$consulta="SELECT idCiclo,idPeriodo FROM 4540_gruposMaestros WHERE idGrupoPadre=".$idGrupo;
		$fGpo=$con->obtenerPrimeraFila($consulta);
		$idCiclo=$fGpo[0];
		$idPeriodo=$fGpo[1];
		$consulta="select idGrupos FROM 4520_grupos WHERE  idGrupoPadre=".$idGrupo;
		$con->obtenerFilas($consulta);
		if($con->filasAfectadas==0)
		{
			$consulta="SELECT idInstanciaPlanEstudio FROM 4540_gruposMaestros WHERE idGrupoPadre=".$idGrupo;
			$idInstancia=$con->obtenerValor($consulta);
			crearGruposAsociados($idInstancia,$idGrupo,$codigoPadre);
		}
		$consulta="select * from 4505_estructuraCurricular where idPlanEstudio=".$idPlanEstudio." and codigoPadre='".$codigoPadre."' and tipoUnidad in (1,2) and naturalezaMateria=1";
		$resMat=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($resMat))
		{
			$consulta="SELECT * FROM 4552_intercambiosMateria WHERE idElementoOrigen=".$fila[0]." AND idCiclo=".$idCiclo." AND idPeriodo=".$idPeriodo;
			$fCambio=$con->obtenerPrimeraFila($consulta);

			if(!$fCambio)
				$consulta="select idGrupos FROM 4520_grupos WHERE idMateria=".$fila[2]." AND idGrupoPadre=".$idGrupo;
			else
				$consulta="select idGrupos FROM 4520_grupos WHERE idRegistroReemplazo=".$fCambio[0]." AND idGrupoPadre=".$idGrupo;
			$idGpo=$con->obtenerValor($consulta);
			if($idGpo!="")
			{
				$consulta="INSERT INTO 4517_alumnosVsMateriaGrupo(idUsuario,situacion,idGrupo,idMateria,idPlanEstudio) VALUES
							(".$idAlumno.",1,".$idGpo.",".$fila[2].",".$idPlanEstudio.")";
				if(!$con->ejecutarConsulta($consulta))
					return false;
				asociarAlumnoGrupoMateria($fila[5],$idAlumno,$idGrupo,$idPlanEstudio);
			}
		}
		return true;
	}
	
	function inscribirAlumnoMateriaObligatoria($codigoUnidadPadre,$idPlanEstudio,$idAlumno,&$consulta,&$x,$Ciclo,$Plantel,$idInstancia,$idGrado,$idPeriodo=1) //Validar sin valor periodo default
	{
		global $con;
		
		$query="SELECT idEsquemaGrupo FROM 4513_instanciaPlanEstudio WHERE idInstanciaPlanEstudio=".$idInstancia;
		$idEsquema=$con->obtenerValor($query);
		if($idEsquema==1)
		{
		
			$query="SELECT idUnidad,codigoUnidad,tipoUnidad,idEstructuraCurricular FROM 4505_estructuraCurricular where tipoUnidad IN (1,2) AND naturalezaMateria=1
					AND idPlanEstudio=".$idPlanEstudio." AND codigoPadre='".$codigoUnidadPadre."'";
					
			$res=$con->obtenerFilas($query);
			while($fila=mysql_fetch_row($res))
			{
				$idMateriaReemplazo="NULL";
				$idRegistroReemplazo="NULL";
				$consulta="SELECT * FROM 4552_intercambiosMateria WHERE idElementoOrigen=".$fila[3]." AND idCiclo=".$Ciclo." AND idPeriodo=".$idPeriodo;
				$fCambio=$con->obtenerPrimeraFila($consulta);
				$NumGrupo="";
				if(!$fCambio)
					$NumGrupo=creacionGrupo($fila[0],$Ciclo,$idInstancia,$Plantel,$idPlanEstudio,$idEsquema,$idPeriodo,$idMateriaReemplazo,$idRegistroReemplazo);
				else
				{
					$consulta="SELECT idUnidad FROM 4505_estructuraCurricular WHERE idEstructuraCurricular=".$fCambio[1];
					$idMateriaReemplazo=$con->obtenerValor($consulta);
					$consulta="SELECT idUnidad FROM 4505_estructuraCurricular WHERE idEstructuraCurricular=".$fCambio[2];
					$idMateriaD=$con->obtenerValor($consulta);
					$idRegistroReemplazo=$fCambio[0];
					$NumGrupo=creacionGrupo($idMateriaD,$Ciclo,$idInstancia,$Plantel,$idPlanEstudio,$idEsquema,$idPeriodo,$idMateriaReemplazo,$idRegistroReemplazo);
				}
				if($NumGrupo!="-1")
				{
					$consulta[$x]="INSERT into 4517_alumnosVsMateriaGrupo(idUsuario,situacion,idGrupo,idMateria,idPlanEstudio) 
								VALUES('".$idAlumno."','1','".$NumGrupo."','".$fila[0]."','".$idInstancia."')";
					$x++;
				}
				else
				{
					$consulta[$x]="INSERT INTO 4528_AlumnoEsperaGrupo(idAlumno,idMateria,idCiclo,idPlantel,idPlanEstudio) VALUES('".$idAlumno."'
									,'".$fila[0]."','".$Ciclo."','".$Plantel."','".$idInstancia."')";
					$x++;
				}
				if(!$fCambio)
					inscribirAlumnoMateriaObligatoria($fila[1],$idPlanEstudio,$idAlumno,$consulta,$x,$Ciclo,$Plantel,$idInstancia,$idGrado,$idPeriodo);
			}
		}
		else
		{
			$idGrupo=creacionGrupo($idGrado,$Ciclo,$idInstancia,$Plantel,$idPlanEstudio,$idEsquema,$idPeriodo);
			inscribirAlumnoGradoGrupo($idAlumno,$idGrado,$Ciclo,$idGrupo);
			
		}
		
	}
	
	function creacionGrupo($idMateria,$ciclo,$idInstancia,$Plantel,$idPlanEstudio,$idEsquema,$idPeriodo=1,$idMateriaReemplazo="NULL",$idRegistroReemplazo="NULL") //Validar sin valor periodo default
	{
		global $con;
		$VGrupo=0;
		$codigoGrado="";
		if($idEsquema==1)
		{
			$consultaGrupo="SELECT * FROM 4520_grupos WHERE idInstanciaPlanEstudio='".$idInstancia."' and idMateria=".$idMateria." and idCiclo=".$ciclo." AND situacion=1";

			$resGrupos=$con->obtenerFilas($consultaGrupo);
			while($fila=mysql_fetch_row($resGrupos))
			{
				$cupoMax=$fila[6];
				$consulta="select count(*) from 4517_alumnosVsMateriaGrupo WHERE idGrupo=".$fila[0]." AND situacion=1";
				$nInscritos=$con->obtenerValor($consulta);
				if($nInscritos<$cupoMax)
				{
					return $fila[0];
				}
			}
		}
		else
		{
			$consulta="SELECT codigoUnidad FROM 4505_estructuraCurricular WHERE idPlanEstudio=".$idPlanEstudio." AND idUnidad=".$idMateria." AND tipoUnidad=3";
			$codigoGrado=$con->obtenerValor($consulta);
			$consultaGrupo="SELECT * FROM 4540_gruposMaestros WHERE 
						idInstanciaPlanEstudio='".$idInstancia."' and idCiclo='".$ciclo."' AND idPeriodo=".$idPeriodo." and codigoGrado=".$codigoGrado." and situacion=1";
			$resGrupos=$con->obtenerFilas($consultaGrupo);
			while($fila=mysql_fetch_row($resGrupos))
			{
				$cupoMax=$fila[5];
				$consulta="select count(*) from 4529_alumnos WHERE idGrupo=".$fila[0]." AND estado=1";
				$nInscritos=$con->obtenerValor($consulta);
				if($nInscritos<$cupoMax)
				{
					return $fila[0];
				}
			}
		}
		$consulta="SELECT * FROM 4521_configuracionGrupo WHERE idInstanciaPlanEstudio=".$idInstancia;
		$fila=$con->obtenerPrimeraFila($consulta);
		if(!$fila)
		{
			$consulta="SELECT * FROM 4521_configuracionGrupo WHERE idInstanciaPlanEstudio is null and idPlanEstudio=".$idPlanEstudio;
			$fila=$con->obtenerPrimeraFila($consulta);
		}
		if(!$fila)
		{
			$prefijo="Grupo";
			$incremento="1";
			$tipoSufijo="1";
			$valorInicial="A";
			$cupoMinimo="5";
			$cupoMaximo="15";
		}
		else
		{
			$prefijo=$fila[3];
			$incremento=$fila[6];
			$tipoSufijo=$fila[4];
			$valorInicial=$fila[5];
			$cupoMinimo=$fila[7];
			$cupoMaximo=$fila[8];
		}
		
		$consulta="SELECT * FROM 4523_conteoActualGrupo WHERE  idInstanciaPlanEstudio=".$idInstancia." AND ciclo=".$ciclo." and idPeriodo=".$idPeriodo;
		$fila=$con->obtenerPrimeraFila($consulta);
		if($fila)
		{
			$valorActual=$fila[3];
			if($tipoSufijo==0)
				$valorActual+=$incremento;
			else
			{
				$valorActual=ord($valorActual)+$incremento;
				$valorActual=chr($valorActual);
			}
			$nombreGrupo=$prefijo." ".$valorActual;
			if($idEsquema==1)
				$consulta="update 4523_conteoActualGrupo set valorActual='".$valorActual."' where idMateria=".$idMateria." and  idInstanciaPlanEstudio=".$idInstancia." AND ciclo=".$ciclo." and idPeriodo=".$idPeriodo;
			else
				$consulta="update 4523_conteoActualGrupo set valorActual='".$valorActual."' where  idInstanciaPlanEstudio=".$idInstancia." AND ciclo=".$ciclo." and idPeriodo=".$idPeriodo;
			if(!$con->ejecutarConsulta($consulta))
				return "-1";
			
		}
		else
		{
			$nombreGrupo=$prefijo." ".$valorInicial;
			if($idEsquema==1)
				$consulta="INSERT INTO 4523_conteoActualGrupo(idInstanciaPlanEstudio,valorActual,ciclo,idMateria,idPeriodo) VALUES(".$idInstancia.",'".$valorInicial."',".$ciclo.",".$idMateria.",".$idPeriodo.")";
			else
				$consulta="INSERT INTO 4523_conteoActualGrupo(idInstanciaPlanEstudio,valorActual,ciclo,idPeriodo) VALUES(".$idInstancia.",'".$valorInicial."',".$ciclo.",".$idPeriodo.")";
			if(!$con->ejecutarConsulta($consulta))
				return "-1";
		}
		$codigoGrado="";
		$consulta="SELECT codigoUnidad FROM 4505_estructuraCurricular WHERE idPlanEstudio=".$idPlanEstudio." AND idUnidad=".$idMateria." AND tipoUnidad=3";
		$codigoGrado=$con->obtenerValor($consulta);
		if($idEsquema==1)
		{
			
			$consulta="SELECT idEstructuraPeriodo FROM 4546_estructuraPeriodo WHERE  grado='".$codigoGrado."' AND idCiclo=".$ciclo." AND idPeriodo=".$idPeriodo." AND idInstanciaPlanEstudio=".$idInstancia;
			$idGradoCiclo=$con->obtenerValor($consulta);	
			$consulta="INSERT INTO 4520_grupos(idPlanEstudio,Plantel,idMateria,nombreGrupo,cupoMinimo,cupoMaximo,situacion,idCiclo,idInstanciaPlanEstudio,idPeriodo,idMateriaReemplazo,idRegistroReemplazo,idGradoCiclo) values
						(".$idPlanEstudio.",'".$Plantel."',".$idMateria.",'".$nombreGrupo."',".$cupoMinimo.",".$cupoMaximo.",1,".$ciclo.",".$idInstancia.",".$idPeriodo.",".$idMateriaReemplazo.",".$idRegistroReemplazo.",".$idGradoCiclo.")";
		}
		else
		{
			$consulta="SELECT idEstructuraPeriodo FROM 4546_estructuraPeriodo WHERE  grado='".$codigoGrado."' AND idCiclo=".$ciclo." AND idPeriodo=".$idPeriodo." AND idInstanciaPlanEstudio=".$idInstancia;
			$idGradoCiclo=$con->obtenerValor($consulta);
			
			$consulta="INSERT INTO 4540_gruposMaestros(nombreGrupo,cupoMinimo,cupoMaximo,situacion,idCiclo,idInstanciaPlanEstudio,idPeriodo,codigoGrado,idGradoCiclo) values
						('".$nombreGrupo."',".$cupoMinimo.",".$cupoMaximo.",1,".$ciclo.",".$idInstancia.",".$idPeriodo.",'".$codigoGrado."',".$idGradoCiclo.")";
		}
		if($con->ejecutarConsulta($consulta))
		{
			$idGrupo=$con->obtenerUltimoID();
			if($idEsquema==2)
				crearGruposAsociados($idInstancia,$idGrupo,$codigoGrado);
			return $idGrupo;
		}
		else
			return -1;
		
	}

	function inscribirAlumnoPlanRigido($idUsuario,$idGrado,$ciclo,$idInstancia,$idPeriodo,$matricula="",$idPlanPagos=-1,$accionMateriaProbada=0) // 0 No inscribe, 1 Inscribe
	{
		global $con;

		$consulta="SELECT id__721_tablaDinamica FROM _721_tablaDinamica WHERE cmbCalificacionfinal=1";
		$listEvaluacionesFinales=$con->obtenerListaValores($consulta);
		$consulta="SELECT COUNT(*) FROM 4529_alumnos WHERE idUsuario=".$idUsuario." AND idInstanciaPlanEstudio=".$idInstancia." AND idGrado=".$idGrado." AND idCiclo=".$ciclo." AND idPeriodo=".$idPeriodo;
		$noInscripcion=$con->obtenerValor($consulta);
		if($noInscripcion>0)
			return true;
		$consulta="select sede from 4513_instanciaPlanEstudio WHERE idInstanciaPlanEstudio=".$idInstancia;
		$plantel=$con->obtenerValor($consulta);
		$idGrupoPadre=creacionGrupoInscripcion($idGrado,$ciclo,$idInstancia,$idPeriodo);
		$x=0;
		$query[$x]="begin";
		$x++;
		$consulta="select count(*) from 807_usuariosVSRoles WHERE idUsuario=".$idUsuario." AND idRol=7";
		$nReg=$con->obtenerValor($consulta);
		if($nReg==0)
		{
			$query[$x]="INSERT INTO 807_usuariosVSRoles(idUsuario,idRol,idExtensionRol,codigoRol)
						VALUES(".$idUsuario.",7,0,'7_0')";
			$x++;
		}
		$query[$x]="delete from 807_usuariosVSRoles where idUsuario=".$idUsuario." and idRol=79";
						
		$x++;
		$consulta="SELECT COUNT(*) FROM 4529_alumnos WHERE idUsuario=".$idUsuario." AND idInstanciaPlanEstudio=".$idInstancia." AND idGrado=".$idGrado." 
					AND idCiclo<>".$ciclo." AND idPeriodo<>".$idPeriodo;
		$noInscripcion=$con->obtenerValor($consulta);
		$noInscripcion++;
		$query[$x]="INSERT INTO 4529_alumnos(idCiclo,idPeriodo,idInstanciaPlanEstudio,idGrado,idGrupo,idUsuario,plantel,estado,matricula,noInscripcion)
					VALUES(".$ciclo.",".$idPeriodo.",".$idInstancia.",".$idGrado.",".$idGrupoPadre.",".$idUsuario.",'".$plantel."',1,'".$matricula."',".$noInscripcion.")";
		$x++;
		$consulta="SELECT idGrupos,idMateria,idPlanEstudio FROM 4520_grupos WHERE idGrupoPadre=".$idGrupoPadre;
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))			
		{
			$inscribirAlumno=true;
			if($accionMateriaProbada==0)
			{
				$consulta="SELECT COUNT(*) FROM 4569_calificacionesEvaluacionAlumnoPerfilMateria WHERE idAlumno=".$idUsuario." AND idMateria=".$fila[1]." 
						AND tipoEvaluacion IN (".$listEvaluacionesFinales.") AND aprobado=1";
				$numEval=$con->obtenerValor($consulta);
				$inscribirAlumno=($numEval==0);
			}
			
			if($inscribirAlumno)
			{
				$idGrupoOrigen="NULL";
				$idGrupo=$fila[0];
				
				
				$consulta="SELECT idGrupo FROM 4539_gruposCompartidos WHERE idGrupoReemplaza=".$idGrupo;
				$iGpo=$con->obtenerValor($consulta);
				if($iGpo!="")
				{
					$idGrupoOrigen=$idGrupo;
					$idGrupo=$iGpo;
				}
				$query[$x]="INSERT INTO 4517_alumnosVsMateriaGrupo(idUsuario,situacion,idGrupo,idMateria,idPlanEstudio,idGrupoOrigen,idInstanciaPlan,idCiclo,idPeriodo)
							VALUES(".$idUsuario.",1,".$idGrupo.",".$fila[1].",".$fila[2].",".$idGrupoOrigen.",".$idInstancia.",".$ciclo.",".$idPeriodo.")";
				$x++;
			}
		}
		$consulta="SELECT idAlumnoSituacion FROM 4537_situacionActualAlumno WHERE idAlumno=".$idUsuario." AND idInstanciaPlanEstudio=".$idInstancia;

		$idAlumnoSituacion=$con->obtenerValor($consulta);
		if($idAlumnoSituacion=="")
		{
			$query[$x]="INSERT INTO 4537_situacionActualAlumno(idAlumno,idInstanciaPlanEstudio,matricula,situacionAlumno,idPlanPagos)
						VALUES(".$idUsuario.",".$idInstancia.",'".$matricula."',1,".$idPlanPagos.")";
			$x++;
		}
		else
		{
			if($matricula=="")
				$query[$x]="UPDATE 4537_situacionActualAlumno set situacionAlumno=1 , idPlanPagos=".$idPlanPagos." WHERE idAlumnoSituacion=".$idAlumnoSituacion;
			else
				$query[$x]="UPDATE 4537_situacionActualAlumno set situacionAlumno=1, idPlanPagos=".$idPlanPagos.",matricula='".$matricula."' WHERE idAlumnoSituacion=".$idAlumnoSituacion;
			$x++;
		}


	
		$query[$x]="commit";
		$x++;
		return $con->ejecutarBloque($query);
		
		
	}

	function creacionGrupoInscripcion($idGrado,$ciclo,$idInstancia,$idPeriodo) //Validar sin valor periodo default
	{
		global $con;
		$VGrupo=0;
		$codigoGrado="";
		$consulta="select idPlanEstudio from 4513_instanciaPlanEstudio WHERE idInstanciaPlanEstudio=".$idInstancia;
		$idPlanEstudio=$con->obtenerValor($consulta);
		$consulta="SELECT codigoUnidad FROM 4505_estructuraCurricular WHERE idPlanEstudio=".$idPlanEstudio." AND idUnidad=".$idGrado." AND tipoUnidad=3";

		$codigoGrado=$con->obtenerValor($consulta);
		$consultaGrupo="SELECT * FROM 4540_gruposMaestros WHERE 
					idInstanciaPlanEstudio='".$idInstancia."' and idCiclo='".$ciclo."' AND idPeriodo=".$idPeriodo." and codigoGrado=".$codigoGrado." and situacion=1";
		
		$resGrupos=$con->obtenerFilas($consultaGrupo);
		while($fila=mysql_fetch_row($resGrupos))
		{
			$cupoMax=$fila[5];
			$consulta="select count(*) from 4529_alumnos WHERE idGrupo=".$fila[0]." AND estado=1";
			$nInscritos=$con->obtenerValor($consulta);
			if($nInscritos<$cupoMax)
			{
				return $fila[0];
			}
		}

		$consulta="SELECT * FROM 4521_configuracionGrupo WHERE idInstanciaPlanEstudio=".$idInstancia;
		$fila=$con->obtenerPrimeraFila($consulta);
		if(!$fila)
		{
			
			$consulta="SELECT * FROM 4521_configuracionGrupo WHERE idInstanciaPlanEstudio is null and idPlanEstudio=".$idPlanEstudio;
			$fila=$con->obtenerPrimeraFila($consulta);
		}
		if(!$fila)
		{
			$prefijo="Grupo";
			$incremento="1";
			$tipoSufijo="1";
			$valorInicial="A";
			$cupoMinimo="5";
			$cupoMaximo="15";
		}
		else
		{
			$prefijo=$fila[3];
			$incremento=$fila[6];
			$tipoSufijo=$fila[4];
			$valorInicial=$fila[5];
			$cupoMinimo=$fila[7];
			$cupoMaximo=$fila[8];
		}
		
		$consulta="SELECT * FROM 4523_conteoActualGrupo WHERE  idInstanciaPlanEstudio=".$idInstancia." AND ciclo=".$ciclo." and idPeriodo=".$idPeriodo;
		$fila=$con->obtenerPrimeraFila($consulta);
		if($fila)
		{
			$valorActual=$fila[3];
			if($tipoSufijo==0)
				$valorActual+=$incremento;
			else
			{
				$valorActual=ord($valorActual)+$incremento;
				$valorActual=chr($valorActual);
			}
			$nombreGrupo=$prefijo." ".$valorActual;
			$consulta="update 4523_conteoActualGrupo set valorActual='".$valorActual."' where  idInstanciaPlanEstudio=".$idInstancia." AND ciclo=".$ciclo." and idPeriodo=".$idPeriodo;
			if(!$con->ejecutarConsulta($consulta))
				return "-1";
			
		}
		else
		{
			$nombreGrupo=$prefijo." ".$valorInicial;
			$consulta="INSERT INTO 4523_conteoActualGrupo(idInstanciaPlanEstudio,valorActual,ciclo,idPeriodo) VALUES(".$idInstancia.",'".$valorInicial."',".$ciclo.",".$idPeriodo.")";
			if(!$con->ejecutarConsulta($consulta))
				return "-1";
		}

		$consulta="SELECT idEstructuraPeriodo FROM 4546_estructuraPeriodo WHERE  grado='".$codigoGrado."' AND idCiclo=".$ciclo." AND idPeriodo=".$idPeriodo." AND idInstanciaPlanEstudio=".$idInstancia;
		$idGradoCiclo=$con->obtenerValor($consulta);
		$consulta="INSERT INTO 4540_gruposMaestros(nombreGrupo,cupoMinimo,cupoMaximo,situacion,idCiclo,idInstanciaPlanEstudio,idPeriodo,codigoGrado,idGradoCiclo) values
						('".$nombreGrupo."',".$cupoMinimo.",".$cupoMaximo.",1,".$ciclo.",".$idInstancia.",".$idPeriodo.",'".$codigoGrado."',".$idGradoCiclo.")";
		if($con->ejecutarConsulta($consulta))
		{
			$idGrupo=$con->obtenerUltimoID();
			crearGruposAsociados($idInstancia,$idGrupo,$codigoGrado);
			return $idGrupo;
		}
		else
			return -1;
		
	}

	function generarNombreGrupo($idCiclo,$idMateria,$idInstanciaPlanEstudio,$idGrupoPadre=-1)
	{
		global $con;
		$consulta="SELECT nombreGrupo FROM 4520_grupos WHERE idCiclo=".$idCiclo." AND idMateria=".$idMateria." AND idInstanciaPlanEstudio=".$idInstanciaPlanEstudio;
		$res=$con->obtenerFilas($consulta);
		$maxNum=0;
		while($fila=mysql_fetch_row($res))
		{
			if(strpos($fila[0],"-")!==false)
			{
				$arrDatos=explode('-',$fila[0]);
				$ultimoValor=$arrDatos[sizeof($arrDatos)-1];
				$arrDatos=explode("[",$ultimoValor);

				if(is_numeric(trim($arrDatos[0])))
				{
					if($arrDatos[0]>$maxNum)
						$maxNum=trim($arrDatos[0]);
				}
			}
		}
		$maxNum++;
		$filaMateria=array();
		if($idMateria>0)
		{
			$consulta="SELECT cveMateria FROM 4512_aliasClavesMateria WHERE idInstanciaPlanEstudio=".$idInstanciaPlanEstudio." AND idMateria=".$idMateria;
			$filaMateria=$con->obtenerPrimeraFila($consulta);
			if(!$filaMateria)
			{
				$consulta="SELECT sede FROM 4513_instanciaPlanEstudio WHERE idInstanciaPlanEstudio=".$idInstanciaPlanEstudio;
				$plantel=$con->obtenerValor($consulta);
				$consulta="SELECT idMateria,cveMateria,horasSemana FROM 4502_Materias m WHERE idMateria=".$idMateria;
				$fDatosMat=$con->obtenerPrimeraFila($consulta);
				
				$consulta="INSERT INTO 4512_aliasClavesMateria(idInstanciaPlanEstudio,idMateria,sede,cveMateria,noHorasSemana,cambioNoHoras)
							VALUES(".$idInstanciaPlanEstudio.",".$idMateria.",'".$plantel."','".$fDatosMat[1]."',".$fDatosMat[2].",0)";
				if(!$con->ejecutarConsulta($consulta))			
					return;
				
				$filaMateria[0]=$fDatosMat[1];
			}
		}
		else
		{
			$consulta="select nombreUnidad from 4508_unidadesContenedora WHERE idUnidadContenedora=".$idMateria*-1;
			$filaMateria=$con->obtenerPrimeraFila($consulta);
		}
		
		$clave=$filaMateria[0];
		$nombreGrupo=$clave."-".$maxNum;
		if($idGrupoPadre!=-1)
		{
			$consulta="SELECT nombreGrupo FROM 4540_gruposMaestros WHERE idGrupoPadre=".$idGrupoPadre;
			$nGrupoPadre=$con->obtenerValor($consulta);
			$nombreGrupo.=" [".$nGrupoPadre."]";
		}
		return $nombreGrupo;
	}	
	
	function obtenerAgendaGrupoCompatible($idGrupo)
	{
		global $con;
		
		$arrFechas[0]="2011-06-05";
		$arrFechas[1]="2011-06-06";
		$arrFechas[2]="2011-06-07";
		$arrFechas[3]="2011-06-08";
		$arrFechas[4]="2011-06-09";
		$arrFechas[5]="2011-06-10";
		$arrFechas[6]="2011-06-11";
		
		$ct=0;
		$todoDia="0";
		$arrEvento="";
		$arrGrupos=array();
		$consulta="select idMateria from 4520_grupos where idGrupos=".$idGrupo;
		$idMateria=$con->obtenerValor($consulta);
		
		$consulta="";
		if($idMateria>0)
		{
			$consulta="SELECT idGrupoPadre,codigoPadre,e.idPlanEstudio,g.idInstanciaPlanEstudio,e.nivel,g.fechaInicio,g.fechaFin FROM  4520_grupos g,
						4505_estructuraCurricular e WHERE e.tipoUnidad=1 AND e.idUnidad=g.idMateria AND idGrupos=".$idGrupo;
		}
		else
		{
			$consulta="SELECT idGrupoPadre,codigoPadre,e.idPlanEstudio,g.idInstanciaPlanEstudio,e.nivel,g.fechaInicio,g.fechaFin FROM  4520_grupos g,
						4505_estructuraCurricular e WHERE e.tipoUnidad=2 AND e.idUnidad=(g.idMateria*-1) AND idGrupos=".$idGrupo;
		}
		

		$fGrupo=$con->obtenerPrimeraFila($consulta);
		if($fGrupo[1]!="")
		{
			if($fGrupo[0]=="")
				$fGrupo[0]=-1;
			$padre=$fGrupo[1];
			if($fGrupo[4]!="2")
			{
				$padre=substr($padre,0,3);
			}
			$listMaterias=obtenerMateriasObligatorias($fGrupo[2],$padre);
			$consulta="select idGrupos from 4520_grupos WHERE idMateria IN (".$listMaterias.") AND idInstanciaPlanEstudio=".$fGrupo[3]."  
					 and ((fechaInicio<='".$fGrupo[5]."' and fechaFin>='".$fGrupo[6]."') or 
					 (fechaInicio>='".$fGrupo[5]."' and fechaInicio<='".$fGrupo[6]."') or 
					 (fechaFin>='".$fGrupo[5]."' and fechaFin<='".$fGrupo[6]."')) and  idGrupos<>".$idGrupo." and idGrupoPadre=".$fGrupo[0]." and situacion in(1,2)";

			$resGrupos=$con->obtenerFilas($consulta);
			$arrGpoComp=array();
			while($fila=mysql_fetch_row($resGrupos))
			{
				$consulta="SELECT idGrupo FROM 4539_gruposCompartidos WHERE idGrupoReemplaza=".$fila[0];
				$fGpo=$con->obtenerPrimeraFila($consulta);
				if(!$fGpo)
					array_push($arrGrupos,$fila[0]);
				else
				{
					$consulta="select idGrupos from 4520_grupos WHERE idGrupos=".$fGpo[0]."
					 and ((fechaInicio<='".$fGrupo[5]."' and fechaFin>='".$fGrupo[6]."') or 
					 (fechaInicio>='".$fGrupo[5]."' and fechaInicio<='".$fGrupo[6]."') or 
					 (fechaFin>='".$fGrupo[5]."' and fechaFin<='".$fGrupo[6]."')) ";
					 

					 
					 $fGpoVig=$con->obtenerPrimeraFila($consulta);
					 if($fGpoVig)
					 {
						 array_push($arrGrupos,$fGpo[0]);
						 $arrGpoComp[$fGpo[0]]="1";
					 }
				}
				
			}
		}
		

		
		$ctColor=9;
		foreach($arrGrupos as $grupo)
		{

			$consulta="SELECT nombreGrupo,o.unidad,g.idInstanciaPlanEstudio,idMateria FROM 4520_grupos g,817_organigrama o WHERE idGrupos=".$grupo." and o.codigoUnidad=g.Plantel";
			$fGrupo=$con->obtenerPrimeraFila($consulta);
			$nCurso="";
			$tCal=4;
			
			if($fGrupo[3]>0)
			{
				$consulta="select nombreMateria from 4502_Materias WHERE idMateria=".$fGrupo[3];
				$nCurso=$con->obtenerValor($consulta);
				$tCal=4;
			}
			else
			{
				$consulta="select nombreUnidad from 4508_unidadesContenedora WHERE idUnidadContenedora=".abs($fGrupo[3]);
				$nCurso=$con->obtenerValor($consulta);
				$tCal=6;
			}
			
			if(isset($arrGpoComp[$grupo]))
				$tCal=5;
			
			$consulta="select concat(i.nombrePlanEstudios,' (Turno: ',t.turno,', Modalidad: ',m.nombre) as nombrePlanEstudios from 
					4513_instanciaPlanEstudio i,4516_turnos t,4514_tipoModalidad m WHERE t.idTurno=i.idTurno and m.idModalidad=i.idModalidad 
					and i.idInstanciaPlanEstudio=".$fGrupo[2];
					
					
			$lblPlan=$con->obtenerValor($consulta);
			$fechaActual=date("Y-m-d");
			
			
			
			$consulta="SELECT horaInicio,horaFin,dia,idAula FROM  4522_horarioGrupo WHERE idGrupo=".$grupo." and '".$fechaActual."'>=fechaInicio 
						and '".$fechaActual."'<=fechaFin ORDER BY dia";
			$lClase=cv($nCurso)." (Grupo: ".(cv($fGrupo[0]))."<br>Lugar: @lugar<br>[".cv($fGrupo[1])."]<br>Plan de estudios: ".cv($lblPlan).")";
			$res=$con->obtenerFilas($consulta);
			if($con->filasAfectadas==0)
			{
				$consulta="SELECT min(fechaInicio) FROM  4522_horarioGrupo WHERE idGrupo=".$grupo;
				$minFecha=$con->obtenerValor($consulta);
				$consulta="SELECT horaInicio,horaFin,dia,idAula FROM  4522_horarioGrupo WHERE idGrupo=".$grupo." and fechaInicio='".$minFecha."' ORDER BY dia";
				$res=$con->obtenerFilas($consulta);
			}
			while($fila=mysql_fetch_row($res))
			{
				
				$consulta="SELECT nombreArea FROM 9309_ubicacionesFisicas WHERE idAreaFisica=".$fila[3];
				$nAula=$con->obtenerValor($consulta);
				if($nAula=="")
					$nAula="No especificado";
				$lblClase=str_replace("@lugar",$nAula,$lClase);
				$obj='{
						  "id": "clase_'.$ct.'",
						  "cid": "'.$tCal.'",
						  "title": "Curso: '.$lblClase.'",
						  "start": "'.$arrFechas[$fila[2]].' '.$fila[0].'",
						  "end": "'.$arrFechas[$fila[2]].' '.$fila[1].'",
						  "ad": "'.$todoDia.'",
						  "rO":"1",
						  "notes":"-1"
					  }';
				if($arrEvento=="")
					$arrEvento=$obj;
				else
					$arrEvento.=",".$obj;
				$ct++;
				
				
			}
			$ctColor++;
			if($ctColor>13)
				$ctColor=9;
		}
			
			
		

		return $arrEvento;
	}
	
	function esDiaInhabilEscolar($plantel,$fecha)
	{
		global $con;
		$consulta="SELECT idFechaCalendario,afectaPago FROM 4525_fechaCalendarioDiaHabil WHERE '".$fecha."' BETWEEN FechaInicio AND fechaFin 
					and (plantel is null or plantel ='' or plantel='".$plantel."')";

		$fila=$con->obtenerPrimeraFila($consulta);
		if($fila)
		{
			return true;
			/*switch($fila[1])
			{
				case 0:
						return true;
				break;
				case 1:
						return false;
				break;
			}*/
		}
		else
		{
			return false;
		}
	}
	
	function ajustarFechaFinalCurso($idGrupo,$validarFechaFinal=true)
	{
		global $con;
		$x=0;
		$arrDias=array();
		$fechaFin="";
		
		$query="delete FROM 4522_horarioGrupo WHERE idGrupo=".$idGrupo." AND fechaInicio>fechaFin";
		$con->ejecutarConsulta($query);
		$query="SELECT fechaInicio,fechaFin FROM 4522_horarioGrupo WHERE idGrupo=".$idGrupo." AND horarioCompleto=0";
		$fGpo=$con->obtenerPrimeraFila($query);
		if($fGpo)
		{
			$query="UPDATE 4522_horarioGrupo SET fechaFin='".$fGpo[1]."' WHERE fechaInicio<=fechaFin AND idGrupo=".$idGrupo." AND fechaFin='".date("Y-m-d",strtotime("-1 days,",strtotime($fGpo[0])))."' and horarioCompleto=1";
			$con->ejecutarConsulta($query);
			$query="delete FROM 4522_horarioGrupo WHERE idGrupo=".$idGrupo." AND horarioCompleto=0";
			$con->ejecutarConsulta($query);
		}
		$query="SELECT idMateria,idInstanciaPlanEstudio,fechaInicio,fechaFin,Plantel,idCiclo,idPeriodo,idGrupoAgrupador,noBloqueAsociado,idGradoCiclo FROM 4520_grupos where idGrupos=".$idGrupo;
		$fGrupos=$con->obtenerPrimeraFila($query);
		$esAgrupador=false;
		if(($fGrupos[0]<0)||($fGrupos[7]!=0))
			$esAgrupador=true;
		$idInstanciaPlanEstudio=$fGrupos[1];
		$consulta="SELECT i.idPlanEstudio,idEsquemaGrupo,i.nombrePlanEstudios,p.descripcion,i.idInstanciaPlanEstudio,i.sede,tipoEsquemaAsignacionFechasGrupo,numMaxBloquesFechas 
				FROM 4513_instanciaPlanEstudio i,4500_planEstudio p WHERE idInstanciaPlanEstudio=".$idInstanciaPlanEstudio." and p.idPlanEstudio=i.idPlanEstudio";
		$filaInstancia=$con->obtenerPrimeraFila($consulta);
		$tEsquemaFecha=$filaInstancia[6];
		$query="SELECT fechaInicial,fechaFinal FROM 4544_fechasPeriodo WHERE idCiclo=".$fGrupos[5]." AND idPeriodo=".$fGrupos[6]." AND idInstanciaPlanEstudio='".$fGrupos[1]."'";
		$fFechas=$con->obtenerPrimeraFila($query);	
		if($tEsquemaFecha==2)
		{
			if($fGrupos[8]=="")
				$fGrupos[8]=0;
			$consulta="SELECT fechaInicio,fechaFin FROM 4571_fechasBloquePeriodo WHERE idInstancia=".$fGrupos[1]." AND noBloque=".$fGrupos[8]." AND idGrado=".$fGrupos[9].
					" AND idCiclo=".$fGrupos[5]." AND idPeriodo=".$fGrupos[6];
			$filaFecha=$con->obtenerPrimeraFila($consulta);			
			$fI=$filaFecha[0];
			if($fI!="")
				$fFechas[0]=$fI;
			
			if($filaFecha[1]=="")	
			{	
				$consulta="SELECT fechaInicio FROM 4571_fechasBloquePeriodo WHERE idInstancia=".$fGrupos[1]." AND noBloque=".($fGrupos[8]+1)." AND idGrado=".$fGrupos[9].
						" AND idCiclo=".$fGrupos[5]." AND idPeriodo=".$fGrupos[6];
				$fF=$con->obtenerValor($consulta);
				if($fF!="")
				{
					$fF=strtotime("-1 days",strtotime($fF));
					$fFechas[1]=$fF;
				}
			}
			else
			{
				$fFechas[1]=$filaFecha[1];
			}
		}
		$fechaInicial=$fFechas[0];	
		
		$arrDatosMateriaHoras=obtenerDatosMateriaHorasGrupo($idGrupo);
		$filaMat[0]=$arrDatosMateriaHoras["horasSemana"];
		$filaMat[1]=$arrDatosMateriaHoras["horaMateriaTotal"];
		
		if(($filaMat[0]==0)&&($filaMat[1]==0))
		{
			return true;
		}
		
		$totalHoras=$filaMat[1];
		$hSemana=$filaMat[0];
		$horasAsignadas=obtenerHorasAsignadasGrupo($idGrupo);

		if(($horasAsignadas>=$hSemana)&&(!$esAgrupador))
		{
			$noBloque=0;
			if(($tEsquemaFecha==2)&&($validarFechaFinal))
				$noBloque=$fGrupos[8];
			
			$fechaFin=obtenerFechaFinCursoHorario($idGrupo,"",null,0,$noBloque);
			
			$fechaFin=str_replace("'","",$fechaFin);
			$query="update 4520_grupos set fechaFin='".$fechaFin."' where idGrupos=".$idGrupo;
			
			if($con->ejecutarConsulta($query))
			{
				$pos=0;
				$consultaAux[$pos]="begin";
				$pos++;
				
				$query="select MAX(fechaBaja) FROM 4519_asignacionProfesorGrupo WHERE idGrupo=".$idGrupo." and fechaAsignacion<=fechaBaja";

				$maxFecha=$con->obtenerValor($query);
				if($maxFecha!="")
				{
					$consultaAux[$pos]="UPDATE 4519_asignacionProfesorGrupo SET fechaBaja='".$fechaFin."' WHERE idGrupo=".$idGrupo." AND fechaBaja='".$maxFecha."' and fechaAsignacion<=fechaBaja";
					$pos++;	
				}
				
				$query="select MAX(fechaFin) FROM 4522_horarioGrupo WHERE idGrupo=".$idGrupo." and fechaInicio<=fechaFin";
				$maxFecha=$con->obtenerValor($query);
				if($maxFecha!="")
				{
					$consultaAux[$pos]="UPDATE 4522_horarioGrupo SET fechaFin='".$fechaFin."' WHERE idGrupo=".$idGrupo." AND fechaFin='".$maxFecha."' and fechaInicio<=fechaFin";
					$pos++;	
				}
				
				$consultaAux[$pos]="commit";
				$pos++;
				if($con->ejecutarBloque($consultaAux))
				{
					$aHorario=obtenerSesionesVirtualesCursoHorario($idGrupo);
					if(sizeof($aHorario)>0)
					{
						$uSesion=$aHorario[sizeof($aHorario)-1];
						
						$fSI=$uSesion["fechaInicio"];
						$fSF=$uSesion["fechaFin"];
						
						for($nPosS=0;$nPosS<sizeof($aHorario);$nPosS++)
						{
							if(($aHorario[$nPosS]["fechaInicio"]==$fSI)&&($aHorario[$nPosS]["fechaFin"]==$fSF))
							{
								$aHorario[$nPosS]["fechaFin"]=$fechaFin;
							}
						}
						
						$uSesion["fechaFin"]=$fechaFin;
						
						if($uSesion["horarioCompleto"]==0)
						{
							
							if(sizeof($aHorario)==1)	
							{
								$pos=0;
								$consultaAux=array();
								$consultaAux[$pos]="begin";
								$pos++;
								
								$consultaAux[$pos]="delete from 4522_horarioGrupo where idGrupo=".$idGrupo;
								$pos++;
								
								$consultaAux[$pos]="INSERT INTO 4522_horarioGrupo(idGrupo,dia,horaInicio,horaFin,idAula,fechaInicio,fechaFin)VALUES(".
													$idGrupo.",".$uSesion["dia"].",'".$uSesion["horaInicio"]."','".$uSesion["horaFin"]."',".$uSesion["idAula"].
													",'".$uSesion["fechaInicio"]."','".$uSesion["fechaFin"]."')";
								$pos++;
								
								
								$consultaAux[$pos]="commit";
								$pos++;
								$con->ejecutarBloque($consultaAux);
								
							}
							else
							{
								$pos=0;
								$consultaAux=array();
								$consultaAux[$pos]="begin";
								$pos++;
								
								$consultaAux[$pos]="update 4522_horarioGrupo SET fechaFin='".date("Y-m-d",strtotime("-1 days",strtotime($uSesion["fechaFin"])))."' where idGrupo=".
													$idGrupo." and fechaInicio<=fechaFin and fechaFin='".$uSesion["fechaFin"]."'";
								$pos++;
								
								$query="select * from 4522_horarioGrupo where idGrupo=".$idGrupo." and dia=".$uSesion["dia"]." and fechaFin='".$uSesion["fechaFin"]."' and horaInicio<'".
										$uSesion["horaInicio"]."' order by horaInicio";
								$rHorarioAux=$con->obtenerFilas($query);
								while($fHorarioAux=mysql_fetch_row($rHorarioAux))
								{
									$consultaAux[$pos]="INSERT INTO 4522_horarioGrupo(idGrupo,dia,horaInicio,horaFin,idAula,fechaInicio,fechaFin,horarioCompleto)VALUES(".
													$idGrupo.",".$uSesion["dia"].",'".$fHorarioAux[3]."','".$fHorarioAux[4]."',".$fHorarioAux[5].
													",'".$uSesion["fechaFin"]."','".$uSesion["fechaFin"]."',0)";
									$pos++;	
								}
								
								if($uSesion["horaInicio"]!=$uSesion["horaFin"])
								{
									$consultaAux[$pos]="INSERT INTO 4522_horarioGrupo(idGrupo,dia,horaInicio,horaFin,idAula,fechaInicio,fechaFin,horarioCompleto)VALUES(".
														$idGrupo.",".$uSesion["dia"].",'".$uSesion["horaInicio"]."','".$uSesion["horaFin"]."',".$uSesion["idAula"].
														",'".$uSesion["fechaFin"]."','".$uSesion["fechaFin"]."',0)";
									$pos++;
								}
								
								$consultaAux[$pos]="commit";
								$pos++;
								$con->ejecutarBloque($consultaAux);	
							}
						}
						
					}
					$consulta=array();
					$x=0;
					
					if(ajustarSesiones($idGrupo,strtotime($fGrupos[2]),NULL,$consulta,$x,true,true))
					{
						$query="SELECT MIN(fechaSesion) FROM `4530_sesiones` WHERE idGrupo=".$idGrupo;
						$fechaInicial=$con->obtenerValor($query);
						
						$ctAux=0;
						$queryAux[$ctAux]="begin";
						$ctAux++;
						if($fechaInicial!="")
						{
							$queryAux[$ctAux]="UPDATE 4520_grupos SET fechaInicio='".$fechaInicial."' WHERE idGrupos=".$idGrupo;
							$ctAux++;
							$query="select MIN(fechaAsignacion) FROM 4519_asignacionProfesorGrupo WHERE idGrupo=".$idGrupo." and fechaAsignacion<fechaBaja";
							$minFecha=$con->obtenerValor($query);
							if($minFecha!="")
							{
								$queryAux[$ctAux]="UPDATE 4519_asignacionProfesorGrupo SET fechaAsignacion='".$fechaInicial."' WHERE idGrupo=".$idGrupo." AND fechaAsignacion='".$minFecha."' and fechaAsignacion<fechaBaja";
								$ctAux++;	
							}
							
							$query="select MIN(fechaInicio) FROM 4522_horarioGrupo WHERE idGrupo=".$idGrupo." and fechaInicio<fechaFin";
							$minFecha=$con->obtenerValor($query);
							if($minFecha!="")
							{
								$queryAux[$ctAux]="UPDATE 4522_horarioGrupo SET fechaInicio='".$fechaInicial."' WHERE idGrupo=".$idGrupo." AND fechaInicio='".$minFecha."' and fechaInicio<fechaFin";
								$ctAux++;	
							}
							
						}
						
						
						$queryAux[$ctAux]="commit";
						$ctAux++;
						
						return $con->ejecutarBloque($queryAux);
					}
					
					return true;
				}
				
			}
		}
		else
		{
			
			$fIni=$fGrupos[2];
			if($fIni!="")
				$fechaInicial=$fIni;
			$fechaFinal=$fGrupos[3];
			if(!$esAgrupador)
				$fechaFinal=obtenerFechaFinCursoGrupo($fechaInicial,$idGrupo,true);
			
			$fechaFinal=str_replace("'","",$fechaFinal);
			$query="UPDATE 4520_grupos SET fechaInicio='".$fechaInicial."',fechaFin='".$fechaFinal."' WHERE idGrupos=".$idGrupo;
			
			$ultimaFechaSesion=$fechaFinal;
			if($con->ejecutarConsulta($query))
			{
				
				$pos=0;
				$consultaAux[$pos]="begin";
				$pos++;
				
				$query="select MAX(fechaBaja) FROM 4519_asignacionProfesorGrupo WHERE idGrupo=".$idGrupo." and fechaAsignacion<fechaBaja";
				$maxFecha=$con->obtenerValor($query);
				if($maxFecha!="")
				{
					$consultaAux[$pos]="UPDATE 4519_asignacionProfesorGrupo SET fechaBaja='".$ultimaFechaSesion."' WHERE idGrupo=".$idGrupo." AND fechaBaja='".$maxFecha."' and fechaAsignacion<fechaBaja";
					$pos++;	
				}
				
				$query="select MAX(fechaFin) FROM 4522_horarioGrupo WHERE idGrupo=".$idGrupo." and fechaInicio<fechaFin";
				$maxFecha=$con->obtenerValor($query);
				if($maxFecha!="")
				{
					$consultaAux[$pos]="UPDATE 4522_horarioGrupo SET fechaFin='".$ultimaFechaSesion."' WHERE idGrupo=".$idGrupo." AND fechaFin='".$maxFecha."' and fechaInicio<fechaFin";
					$pos++;	
				}
				
				
				$query="select MIN(fechaAsignacion) FROM 4519_asignacionProfesorGrupo WHERE idGrupo=".$idGrupo." and fechaAsignacion<fechaBaja";
				$minFecha=$con->obtenerValor($query);
				if($minFecha!="")
				{
					$consultaAux[$pos]="UPDATE 4519_asignacionProfesorGrupo SET fechaAsignacion='".$fechaInicial."' WHERE idGrupo=".$idGrupo." AND fechaAsignacion='".$minFecha."' and fechaAsignacion<fechaBaja";

					$pos++;	
				}
				
				$query="select MIN(fechaInicio) FROM 4522_horarioGrupo WHERE idGrupo=".$idGrupo." and fechaInicio<fechaFin";
				$minFecha=$con->obtenerValor($query);
				if($minFecha!="")
				{
					$consultaAux[$pos]="UPDATE 4522_horarioGrupo SET fechaInicio='".$fechaInicial."' WHERE idGrupo=".$idGrupo." AND fechaInicio='".$minFecha."' and fechaInicio<fechaFin";
					$pos++;	
				}
				
				
				$consultaAux[$pos]="commit";
				$pos++;
				if($con->ejecutarBloque($consultaAux))
				{
					if($esAgrupador)	
					{
						$consulta="select idGrupos from 4520_grupos where idGrupoAgrupador=".$idGrupo;
						$res=$con->obtenerFilas($consulta);
						while($fila=mysql_fetch_row($res))
						{
							$query="UPDATE 4520_grupos SET fechaInicio='".$fechaInicial."',fechaFin='".$fechaFinal."' WHERE idGrupos=".$fila[0];
							if($con->ejecutarConsulta($query))
							{
								ajustarFechaFinalCurso($fila[0]);
							}
						}
					}
					return true;
				}
			}
		}
	}	
	
	function validarCambioHorario($horario,$idGrupo,$idMateria,$idRegistro,$ciclo,$fechaAplicacion=NULL,$objComp=NULL)
	{
		global $con;
		global $arrFechasHorario;
		$arrDiasSemana[0]="Domingo";
		$arrDiasSemana[1]="Lunes";
		$arrDiasSemana[2]="Martes";
		$arrDiasSemana[3]="Mi&eacute;rcoles";
		$arrDiasSemana[4]="Jueves";
		$arrDiasSemana[5]="Viernes";
		$arrDiasSemana[6]="S&aacute;bado";
		$cadValidacion="";
		$dHorario=explode(" ",$horario->hInicio);
		$horario->hInicio=$dHorario[0];
		$dHorario=explode(" ",$horario->hFin);
		$horario->hFin=$dHorario[0];
		$fechaInicio=strtotime($arrFechasHorario[$horario->dia]." ".$horario->hInicio);
		$fechaFin=strtotime($arrFechasHorario[$horario->dia]." ".$horario->hFin);
		$comp="";
		if($fechaAplicacion!=NULL)
		{
			$comp=" and '".$fechaAplicacion."'>=fechaInicio and '".$fechaAplicacion."'<=fechaFin";
		}
		$idDiaSemana=$horario->dia;
		
		$consulta="SELECT idGrupoPadre,fechaInicio,fechaFin FROM 4520_grupos WHERE idGrupos=".$idGrupo;
		$fGrupo=$con->obtenerPrimeraFila($consulta);
		
		if($objComp!=NULL)
		{
			if(isset($objComp->esCambioFechaInicio))
			{
				$fGrupo[1]=$objComp->fechaInicio;
				$fGrupo[2]=$objComp->fechaFin;
				
			}
		}
		
		$idGrupoPadre=$fGrupo[0];
		
		$resValidacion=funcValidarHorarioMateriaCursoExtendido(date("H:i",$fechaInicio),date("H:i",$fechaFin),$idDiaSemana,$idGrupo,$ciclo,$idRegistro,$idMateria,2,$fechaAplicacion,true,$objComp);

		$aValidacion=explode("|",$resValidacion);
		if($aValidacion[0]==-1)
			$aValidacion[0]=2;
		if($horario->idAula!=-1)
		{
			
			$res=validarModificacionHorarioAulaExtendido($horario->idAula,date("H:i",$fechaInicio),date("H:i",$fechaFin),$idDiaSemana,$ciclo,$idMateria,$idGrupo,$idRegistro,2,$fechaAplicacion,$objComp);
			$resAula=explode("|",$res);
			if($resAula[0]==3)
			{
				$arrCasos='{"noError":"12","permiteContinuar":"1","msgError":"El aula asignada ya se encuentra ocupada por otras materias en el horario indicado","compl":"'.($resAula[1]).'"}';
				//$arrCasos="['12','1','El aula asignada ya se encuentra ocupada por otras materias en el horario indicado','".$resAula[1]."']";
				if($aValidacion[1]!="")
				{
					$cadValidacion=$aValidacion[1];
					$cadValidacion=substr($cadValidacion,0,strlen($cadValidacion)-2);
					$cadValidacion.=",".$arrCasos.']}';
					$aValidacion[1]=$cadValidacion;
				}
				else
				{
					$aValidacion[0]=2;
					$aValidacion[1]='{"permiteContinuar":"0","arrCasos":['.$arrCasos.']}';
				}
			}
		}
		
		if($idGrupoPadre!="")
		{
			$consulta="SELECT idGrupos,situacion FROM 4520_grupos WHERE idGrupoPadre=".$idGrupoPadre." AND idGrupos<>".$idGrupo." 
						and ((fechaInicio<='".$fGrupo[1]."' and fechaFin>='".$fGrupo[2]."') or 
							 (fechaInicio>='".$fGrupo[1]."' and fechaInicio<='".$fGrupo[2]."') or 
							 (fechaFin>='".$fGrupo[1]."' and fechaFin<='".$fGrupo[2]."'))";

			$listGrupos="";
			
			$resGrupos=$con->obtenerFilas($consulta);
			while($filaGrupoTmp=mysql_fetch_row($resGrupos))
			{

				switch($filaGrupoTmp[1])
				{
					case 1:
						if($listGrupos=="")
							$listGrupos=$filaGrupoTmp[0];
						else
							$listGrupos.=",".$filaGrupoTmp[0];
					break;
					case 2:
						$consulta="SELECT * FROM 4539_gruposCompartidos WHERE idGrupoReemplaza=".$filaGrupoTmp[0];
						$idGrupoComp=$con->obtenerValor($consulta);
						if($idGrupoComp!="")
						{
							if($listGrupos=="")
								$listGrupos=$idGrupoComp;
							else
								$listGrupos.=",".$idGrupoComp;
						}
					break;
					
				}
			}
			
			$arrCasos="";
			if($listGrupos!="")
			{
				$consulta="SELECT idGrupo,horaInicio,horaFin FROM 4522_horarioGrupo WHERE idGrupo IN (".$listGrupos.") AND dia=".$idDiaSemana." ".$comp." for update";
				$resHoras=$con->obtenerFilas($consulta);
				while($fHorario=mysql_fetch_row($resHoras))
				{
					
					if(colisionaTiempo(date("Y-m-d H:i",$fechaInicio),date("Y-m-d H:i",$fechaFin),$arrFechasHorario[$idDiaSemana]." ".$fHorario[1],$arrFechasHorario[$idDiaSemana]." ".$fHorario[2]))
					{
						$diaSemanaLetra=$arrDiasSemana[$idDiaSemana];
						$nMateria=obtenerNombreGrupoCompleto($fHorario[0]);	
						if($arrCasos=="")
							$arrCasos='{"noError":"13","permiteContinuar":"0","msgError":"El grupo presenta problemas de horario con la materia <b>'.$nMateria.'</b> el d&iacute;a '.$diaSemanaLetra.' de '.date("H:i",strtotime($fHorario[1])).' a '.date("H:i",strtotime($fHorario[2])).'","compl":""}';
						else
							$arrCasos.=',{"noError":"13","permiteContinuar":"0","msgError":"El grupo presenta problemas de horario con la materia <b>'.$nMateria.'</b> el d&iacute;a '.$diaSemanaLetra.' de '.date("H:i",strtotime($fHorario[1])).' a '.date("H:i",strtotime($fHorario[2])).'","compl":""}';

					}
				}
				if($arrCasos!="")
				{
					if($aValidacion[1]!="")
					{
						$cadValidacion=$aValidacion[1];
						$cadValidacion=substr($cadValidacion,0,strlen($cadValidacion)-2);
						$cadValidacion.=",".$arrCasos.']}';
						$aValidacion[1]=$cadValidacion;
					}
					else
					{
						$aValidacion[0]=2;
						$aValidacion[1]='{"permiteContinuar":"0","arrCasos":['.$arrCasos.']}';
						
					}
				}
				
				
			}
			
		}

		return $aValidacion[0]."|".$aValidacion[1];
	}
	
	function obtenerNombreGrupoCompleto($idGrupo)
	{
		global $con;
		$consulta="SELECT m.nombreMateria,nombreGrupo,idInstanciaPlanEstudio FROM 4520_grupos g,4502_Materias m WHERE idGrupos=".$idGrupo." AND m.idMateria=g.idMateria";
		$fGrupo=$con->obtenerPrimeraFila($consulta);
		return $fGrupo[0].", Grupo: ".$fGrupo[1]." (".obtenerNombreInstanciaPlan($fGrupo[2]).")";
		
	}
	
	function cumpleRequisitos($idMateriaEval,$idMateriaReemplazo,$idGrado,$idPlanEstudios,$idCiclo,$idPeriodo,$idInstanciaPlan)
	{
		global $con;
		
		$consulta="SELECT idEstructuraCurricular FROM 4505_estructuraCurricular WHERE idPlanEstudio=".$idPlanEstudios." AND idUnidad=".$idMateriaEval." AND tipoUnidad=1";
		$idElemento=$con->obtenerValor($consulta);
		
		$consulta="SELECT idMateriaIntercambio FROM 4552_intercambiosMateria WHERE idElementoOrigen=".$idElemento." OR idElementoCambio=".$idElemento." AND idInstanciaPlan=".$idInstanciaPlan."
					AND idCiclo=".$idCiclo." AND idPeriodo=".$idPeriodo;
		$fIntercambio=$con->obtenerPrimeraFila($consulta);
		if($fIntercambio)					
			return false;
		
		$consulta="SELECT idMateriaRequisito,tipoRequisito FROM 4511_seriacionMateria WHERE idMateria=".$idMateriaEval." AND tipoRequisito IN (1,3)";
		$resRequisitos=$con->obtenerFilas($consulta);
		$cumpleRequisito=true;
		$arrMateriasGradosAnt=array();
		$consulta="select ordenGrado from 4501_Grado where idGrado=".$idGrado;
		$ordenGrado=$con->obtenerValor($consulta);
		$consulta="SELECT idGrado FROM 4501_Grado WHERE idPlanEstudio=".$idPlanEstudios." AND ordenGrado<".$ordenGrado;
		$resG=$con->obtenerFilas($consulta);
		while($filaGrado=mysql_fetch_row($resG))
		{
			$consulta="SELECT codigoUnidad FROM 4505_estructuraCurricular WHERE idPlanEstudio=".$idPlanEstudios." AND idUnidad=".$filaGrado[0]." AND tipoUnidad=3";
			$codGrado=$con->obtenerValor($consulta);
			$consulta="select idUnidad from 4505_estructuraCurricular where idPlanEstudio=".$idPlanEstudios." AND codigoUnidad like '".$codGrado."%' and tipoUnidad=1";
			$resMatGrado=$con->obtenerFilas($consulta);
			while($fMatGrado=mysql_fetch_row($resMatGrado))
			{
				$arrMateriasGradosAnt[$fMatGrado[0]]=1;
			}
		}
		$arrMateriasGrado=array();
		$consulta="SELECT codigoUnidad FROM 4505_estructuraCurricular WHERE idPlanEstudio=".$idPlanEstudios." AND idUnidad=".$idGrado." AND tipoUnidad=3";
		$codGrado=$con->obtenerValor($consulta);
		$consulta="select idUnidad from 4505_estructuraCurricular where idPlanEstudio=".$idPlanEstudios." AND codigoUnidad like '".$codGrado."%' and idUnidad<>".$idMateriaReemplazo." and tipoUnidad=1";
		$resMatGrado=$con->obtenerFilas($consulta);
		while($fMatGrado=mysql_fetch_row($resMatGrado))
		{
			$arrMateriasGrado[$fMatGrado[0]]=1;
		}
		
		while($filaRequisito=mysql_fetch_row($resRequisitos))
		{
			if(!isset($arrMateriasGradosAnt[$filaRequisito[0]]))
			{
				if($filaRequisito[1]==3)
				{
					if(!isset($arrMateriasGrado[$filaRequisito[0]]))
					{
						$cumpleRequisito=false;
						break;
					}
				}
				else
				{
					$cumpleRequisito=false;
					break;
				}
			}
		}
		return $cumpleRequisito;
	}
	
	function obtenerNombreInstanciaPlan($idInstanciaPlanEstudio)
	{
		global $con;
		if($idInstanciaPlanEstudio=="")
			$idInstanciaPlanEstudio=-1;
		$consulta="SELECT CONCAT(nombrePlanEstudios,' Modalidad: ',m.nombre,' Turno: ',t.turno) FROM 4513_instanciaPlanEstudio i,4514_tipoModalidad m,4516_turnos t  WHERE idInstanciaPlanEstudio=".$idInstanciaPlanEstudio."
                   AND m.idModalidad=i.idModalidad AND t.idTurno=i.idTurno";

		return $con->obtenerValor($consulta);
	}
	
	function validarHorarioAsistenciaProfesor($hora,$idUsuario,$fechaActual,$plantel)
	{
		global $con;
		$toleranciaAntesHoraInicio=10;//--
		$toleranciaDespuesHoraInicio=10;
		$toleranciaAntesHoraFin=10;//--
		$toleranciaFalta=10;
		$toleranciaIgnoraSalida=0;
		$toleranciaRegistroSalida=10;
		$horaCero=0;
		$consulta="SELECT * FROM _484_tablaDinamica";
		$fConf=$con->obtenerPrimeraFila($consulta);
		if($fConf)
		{
			$toleranciaAntesHoraInicio=$fConf[10];
			$toleranciaDespuesHoraInicio=$fConf[19];
			$toleranciaFalta=$fConf[11];
			$toleranciaAntesHoraFin=$fConf[12];
			$toleranciaIgnoraSalida=$fConf[20];
			$toleranciaRegistroSalida=$fConf[21];
		}
		//$toleranciaDespuesHoraFin=10;
		//$plantel=$_SESSION["codigoInstitucion"];
		$checarSalida=true;
		$fechaActual=strtotime($fechaActual);
		$horaActual=strtotime($hora);
		
		//$numIp=obtenerIP();
		//$consulta="SELECT id__453_tablaDinamica FROM _453_tablaDinamica 
		//			WHERE codigoInstitucion='".$plantel."' and  ipTerminal='".$numIp."'";
		//$fInstitucion=$con->obtenerPrimeraFila($consulta);
		$ignorarEvento=false;
		$fInstitucion=true;
		if($fInstitucion)
		{
			if($idUsuario=="")
			{
				$objResp='{"resultado":"-1"}';//Usuario no existe
			}
			else
			{
				$consulta="select concat(Paterno,' ',Materno,' ',Nom) as nombre from 802_identifica where idUsuario=".$idUsuario;
				$filaI=$con->obtenerPrimeraFila($consulta);
				$d=date("w",$fechaActual);
				$filaSalida=false;
				if($checarSalida)
				{
					$consulta="SELECT idAsistencia,hora_entrada,hora_salida,idGrupo,horaInicioBloque FROM 9105_controlAsistencia WHERE fecha='".date("Y-m-d",$fechaActual)."' 
								AND idUsuario=".$idUsuario." AND tipo=0";
					$filaSalida=$con->obtenerPrimeraFila($consulta);
					if($filaSalida)
					{
						$horaEntrada=strtotime($filaSalida[1]);
						$diferencia=($horaCero+$horaActual)-$horaEntrada;	
						$nSegundos=(date("H",$diferencia)*3600)+(date("i",$diferencia)*60)+date("s",$diferencia);
						if($nSegundos>$toleranciaIgnoraSalida)
						{
							$consulta="SELECT horaFin FROM 4522_horarioGrupo WHERE idGrupo=".$filaSalida[3]." AND dia=".$d." and horaInicio='".$filaSalida[4]."'";
							$filaH=$con->obtenerPrimeraFila($consulta);
							$hSalida=strtotime($filaH[0]);
							$hSalida=strtotime("+".$toleranciaRegistroSalida." minutes",$hSalida);
							if($horaActual>$hSalida)
							{
								$consulta="update 9105_controlAsistencia set tipo=1 where idAsistencia=".$filaSalida[0];
								if($con->ejecutarConsulta($consulta))
									$filaSalida=false;
								else
									return;
							}
						}
						else
							$ignorarEvento=true;
					}
				}
				if(!$ignorarEvento)
				{
					if($filaSalida)
					{
						$consulta="SELECT horaFin FROM 4522_horarioGrupo WHERE idGrupo=".$filaSalida[3]." AND dia=".$d." and horaInicio='".$filaSalida[4]."'";
						$filaH=$con->obtenerPrimeraFila($consulta);
						$hSalida=strtotime($filaH[0]);
						$hSalida=strtotime("-".$toleranciaAntesHoraFin." minutes",$hSalida);
						$consulta="SELECT nombreGrupo,nombreMateria,idInstanciaPlanEstudio FROM 4520_grupos g,4502_Materias m WHERE idGrupos=".$filaSalida[3]." and m.idMateria=g.idMateria";
						$fGpo=$con->obtenerPrimeraFila($consulta); 
						$diferencia=$horaCero+$hSalida-$horaActual;
						$resultado=4;// Salida anticipada
						$tAnticipado=date("H:i:s",$diferencia);
						$tExtra="00:00:00";
						$fPlan=true;
						if($fPlan)
						{
							if($horaActual>=$hSalida)
							{
								$resultado=5;//Salida
								$tAnticipado="00:00:00";
								$tExtra=date("H:i:s",$diferencia);
							}
							$tAsistencia=1;
							if($resultado==4)
							{
								$consulta="SELECT cmbSancionSalidaAnticipada FROM _484_tablaDinamica";
								$tAsistencia=$con->obtenerValor($consulta);
							}
							
							$consulta="update 9105_controlAsistencia set tAnticipado='".$tAnticipado."',tExtra='".$tExtra."',hora_salida='".date("H:i:s",$horaActual)."',tipo=1,tipoAsistencia=".$tAsistencia." where idAsistencia=".$filaSalida[0];
							if($con->ejecutarConsulta($consulta))
							{
								$objResp='{"resultado":"'.$resultado.'","nombre":"'.cv($filaI[0]).'","idFoto":"'.($idUsuario).'","idUsuario":"'.$idUsuario.'","nomMateria":"'.cv($fGpo[1]." (Grupo: ".$fGpo[0].")").'","tRetardo":"'.date("H:i:s",$diferencia).'"}';
							}
							else
								return;
						}
						else
						{
							$consulta="SELECT nombrePlanEstudios FROM 4513_instanciaPlanEstudio WHERE idInstanciaPlanEstudio=".$fPlan[0];
							$nPlanEstudios=$con->obtenerValor($consulta);
							$consulta="INSERT INTO 9105_controlAsistencia(idUsuario,fecha,hora_entrada,tipo,tRetardo,esRetardo,idGrupo,tipoAsistencia) 
										values(".$idUsuario.",'".date("Y-m-d",$fechaActual)."','".date("H:i:s",$horaActual)."',9,0,0,NULL,9)";
							if($con->ejecutarConsulta($consulta))
							{
								$objResp='{"resultado":"9","planEstudios":"'.cv($nPlanEstudios).'"}'; //Terminal no autoizada para registrar asistencia d emateria plan estudio
							}
							else
								return;
						}
					}
					else
					{
									
						$consulta="SELECT idGrupos FROM 4520_grupos g,4519_asignacionProfesorGrupo a WHERE a.idUsuario=".$idUsuario." AND a.situacion=1 and a.participacionPrincipal=1 AND a.idGrupo=g.idGrupos  AND Plantel='".$plantel."' 
									AND g.fechaInicio<='".date("Y-m-d",$fechaActual)."' AND g.fechaFin>='".date("Y-m-d",$fechaActual)."' ";
						$res=$con->obtenerFilas($consulta);

						$arrGrupos=array();
						while($fila=mysql_fetch_row($res))
						{
							array_push($arrGrupos,$fila[0]);
						}
						
						$consulta="SELECT idGrupos,idFormularioAccion,idRegistroAccion FROM 4520_grupos g,4519_asignacionProfesorGrupo a WHERE a.idUsuario=".$idUsuario." AND a.situacion=1 and a.participacionPrincipal=0 AND a.idGrupo=g.idGrupos  AND Plantel='".$plantel."' 
									AND g.fechaInicio<='".date("Y-m-d",$fechaActual)."' AND g.fechaFin>='".date("Y-m-d",$fechaActual)."' ";
						
						$res=$con->obtenerFilas($consulta);
						while($fila=mysql_fetch_row($res))
						{
							if($fila[1]!="")
							{	
								$consulta="select fechaBaja,fechaRegreso,motivoBaja from id_".$fila[1]."_tablaDinamica where id__".$fila[1]."_tablaDinamica=".$fila[2];
								$fSuplencia=$con->obtenerPrimeraFila($consulta);
								if($fSuplencia)
								{
									if($fSuplencia[2]==5) //Suplencia
									{
										$fInicioSup=strtotime($fSuplencia[0]);
										$fFinSup=strtotime($fSuplencia[1]);
										if(($fechaActual>=$fInicioSup)&&($fechaActual<=$fFinSup))
										{
											if(!existeValor($arrGrupos,$fila[0]))
												array_push($arrGrupos,$fila[0]);
										}
									}
								}
							}
							
						}
	
						$nGrupos=sizeof($arrGrupos);					
						$ct=0;
						$encontrado=false;
						while(($ct<$nGrupos)&&(!$encontrado))
						{
							$fila[0]=$arrGrupos[$ct];
							$horaEntrada=strtotime("+".$toleranciaAntesHoraInicio." minutes",$horaActual);
							$consulta="SELECT horaInicio,horaFin FROM 4522_horarioGrupo WHERE idGrupo=".$fila[0]." AND dia=".$d." and '".date("H:i:s",$horaEntrada)."'>=horaInicio and '".date("H:i:s",$horaEntrada)."'<=horaFin";
							$filaH=$con->obtenerPrimeraFila($consulta);
	
							if($filaH)
							{
	
								$hInicio=strtotime("+".$toleranciaDespuesHoraInicio." minutes",strtotime($filaH[0]));
								$diferencia="";
								if($horaActual>$hInicio)
									$diferencia=$horaCero+$horaActual-$hInicio;
								else
								{
									$diferencia=$horaCero+$hInicio-$horaActual;
									$diferencia=$horaCero;
								}
								$consulta="SELECT nombreGrupo,nombreMateria,idInstanciaPlanEstudio FROM 4520_grupos g,4502_Materias m WHERE idGrupos=".$fila[0]." and m.idMateria=g.idMateria";
								$fGpo=$con->obtenerPrimeraFila($consulta);
								$fPlan=true;
								if($fPlan)
								{
									if($horaActual<=$hInicio) //entrada
									{
										$consulta="INSERT INTO 9105_controlAsistencia(idUsuario,fecha,hora_entrada,tipo,tRetardo,esRetardo,idGrupo,tipoAsistencia,horaInicioBloque) 
													values(".$idUsuario.",'".date("Y-m-d",$fechaActual)."','".date("H:i:s",$horaActual)."',0,'".date("H:i:s",$diferencia)."',0,".$fila[0].",1,'".$filaH[0]."')";
										if($con->ejecutarConsulta($consulta))
											$objResp='{"resultado":"1","nombre":"'.cv($filaI[0]).'","idFoto":"'.($idUsuario).'","idUsuario":"'.$idUsuario.'","nomMateria":"'.cv($fGpo[1]." (Grupo: ".$fGpo[0].")").'","tRetardo":"'.date("H:i:s",$diferencia).'"}';
										else
											return;
									}
									else
									{
										if($diferencia>strtotime("00:".$toleranciaFalta.":00"))
										{
											$consulta="INSERT INTO 9105_controlAsistencia(idUsuario,fecha,hora_entrada,tipo,tRetardo,esRetardo,idGrupo,tipoAsistencia,horaInicioBloque) 
														values(".$idUsuario.",'".date("Y-m-d",$fechaActual)."','".date("H:i:s",$horaActual)."',3,'".date("H:i:s",$diferencia)."',1,".$fila[0].",3,'".$filaH[0]."')";
											if($con->ejecutarConsulta($consulta))
												$objResp='{"resultado":"7"}';// Tiempo limite excedido para asistencia
											else
												return;
											
										}
										else 
										{
											//Entrada con retardo
											$consulta="INSERT INTO 9105_controlAsistencia(idUsuario,fecha,hora_entrada,tipo,tRetardo,esRetardo,idGrupo,tipoAsistencia,horaInicioBloque) 
														values(".$idUsuario.",'".date("Y-m-d",$fechaActual)."','".date("H:i:s",$horaActual)."',0,'".date("H:i:s",$diferencia)."',1,".$fila[0].",2,'".$filaH[0]."')";
											if($con->ejecutarConsulta($consulta))
												$objResp='{"resultado":"3","nombre":"'.cv($filaI[0]).'","idFoto":"'.($idUsuario).'","idUsuario":"'.$idUsuario.'","nomMateria":"'.cv($fGpo[1]." (Grupo: ".$fGpo[0].")").'","tRetardo":"'.date("H:i:s",$diferencia).'"}';
											else
												return;
										}
									}
								}
								else
								{
									$consulta="SELECT nombrePlanEstudios FROM 4513_instanciaPlanEstudio WHERE idInstanciaPlanEstudio=".$fPlan[0];
									$nPlanEstudios=$con->obtenerValor($consulta);
									$consulta="INSERT INTO 9105_controlAsistencia(idUsuario,fecha,hora_entrada,tipo,tRetardo,esRetardo,idGrupo,tipoAsistencia) 
										values(".$idUsuario.",'".date("Y-m-d",$fechaActual)."','".date("H:i:s",$horaActual)."',9,0,0,NULL,9)";
									if($con->ejecutarConsulta($consulta))
										$objResp='{"resultado":"9","planEstudios":"'.cv($nPlanEstudios).'"}';//Terminal no autoizada para registrar asistencia d emateria plan estudio
									else
										return;
								}
								$encontrado=true;
							}
							$ct++;
						}
						if(!$encontrado)
						{
							$consulta="INSERT INTO 9105_controlAsistencia(idUsuario,fecha,hora_entrada,tipo,tRetardo,esRetardo,idGrupo,tipoAsistencia) 
										values(".$idUsuario.",'".date("Y-m-d",$fechaActual)."','".date("H:i:s",$horaActual)."',6,0,0,NULL,6)";
							if($con->ejecutarConsulta($consulta))
								$objResp='{"resultado":"6"}'; // No materia para asistencia
							else
								return;
						}
					}
				}
			}
		}
		else
		{
			$consulta="INSERT INTO 9105_controlAsistencia(idUsuario,fecha,hora_entrada,tipo,tRetardo,esRetardo,idGrupo,tipoAsistencia) 
									values(".$idUsuario.",'".date("Y-m-d",$fechaActual)."','".date("H:i:s",$horaActual)."',6,0,0,NULL,8)";
			if($con->ejecutarConsulta($consulta))
				$objResp='{"resultado":"8"}';  //Terminal no autorizada para regsitrar asistencia
			else
				return;
		}
		$consulta="INSERT INTO 9105_eventosControlAsistencia(fechaEvento,horaEvento,idUsuario,plantel) VALUES('".date("Y-m-d",$fechaActual)."','".date("H:i:s",$horaActual)."',".$idUsuario.",'".$plantel."')";
		if($con->ejecutarConsulta($consulta))
			return "1|".$objResp;
	}
	
	function ajustarFechasMateria($idMateria)
	{
		global $con;
		$consulta="SELECT idPlanEstudio,horasSemana,cveMateria FROM 4502_Materias WHERE idMateria=".$idMateria;
		$fMateria=$con->obtenerPrimeraFila($consulta);
		$idPlanEstudio=$fMateria[0];
		$noHoras=$fMateria[1];
		$consulta="SELECT `horasSemanaMateriasInd` FROM 4500_planEstudio WHERE `idPlanEstudio`=".$idPlanEstudio;
		$horasInd=$con->obtenerValor($consulta);

		if($horasInd=="0")
		{
			$x=0;
			$query[$x]="begin";
			$x++;
			$query[$x]="UPDATE 4512_aliasClavesMateria SET cambioNoHoras=(noHorasSemana-".$noHoras."),cveMateria='".$fMateria[2]."',noHorasSemana=".$noHoras." WHERE idMateria=".$idMateria;
			$x++;
			$query[$x]="commit";
			$x++;
			if($con->ejecutarBloque($query))
				echo "document.getElementById('frmEnvio').submit();";
		}
	}
	
	function recalcularFechasPlantel($plantel,$fechaInicio,$fechaFin)
	{
		global $con;
		$consulta="SELECT idGrupos FROM 4520_grupos WHERE Plantel='".$plantel."' AND (('".$fechaInicio."'<=fechaInicio AND '".$fechaFin."'>=fechaInicio) OR ('".$fechaInicio."'>=fechaInicio AND '".$fechaInicio."'<=fechaFin) 
					OR('".$fechaInicio."'<=fechaInicio AND '".$fechaFin."'>=fechaFin))";
		$res=$con->obtenerFilas($consulta);		
		while($fila=mysql_fetch_row($res))
		{
			if(!ajustarFechaFinalCurso($fila[0]))
				return false;
		}
		return true;
		
	}
	
	function esPeriodoBase($idPeriodo)
	{
		if(($idPeriodo==7)||($idPeriodo==9))
			return true;
		return false;
	}
	
	function obtenerPeriodoBase()
	{
		return "7,9";
	}
	
	function obtenerIdElementoEstructuraUnidadAgrupadora($idPlanEstudio,$codigoEstructura)
	{
		global $con;

		$nivel=1;

		$nCaracteres=(strlen($codigoEstructura)-(3*$nivel));
		$codigoEstructuraAux=substr($codigoEstructura,0,$nCaracteres);
		$consulta="SELECT idUnidad,tipoUnidad FROM 4505_estructuraCurricular WHERE idPlanEstudio=".$idPlanEstudio." AND codigoUnidad='".$codigoEstructuraAux."'";
		$fElemento=$con->obtenerPrimeraFila($consulta);
		
		$encontrado=false;
		if($fElemento[1]==2)
		{
			$consulta="SELECT tipoUnidad FROM 4508_unidadesContenedora WHERE idUnidadContenedora=".$fElemento[0];
			$tUnidad=$con->obtenerValor($consulta);
			if($tUnidad==1)
				$encontrado=true;
		}
		
		
		$nAux=0;
		while((!$encontrado)&&($fElemento[1]!=3))
		{
			$nCaracteres=(strlen($codigoEstructura)-(3*$nivel));
			$codigoEstructuraAux=substr($codigoEstructura,0,$nCaracteres);
			$consulta="SELECT idUnidad,tipoUnidad FROM 4505_estructuraCurricular WHERE idPlanEstudio=".$idPlanEstudio." AND codigoUnidad='".$codigoEstructuraAux."'";

			$fElemento=$con->obtenerPrimeraFila($consulta);
			
			if($fElemento[1]==2)
			{
				$consulta="SELECT tipoUnidad FROM 4508_unidadesContenedora WHERE idUnidadContenedora=".$fElemento[0];

				$tUnidad=$con->obtenerValor($consulta);

				if($tUnidad==1)
					$encontrado=true;
			}
			
			$nivel++;
			
			
		}
		
		if($encontrado)
		{
			return $fElemento[0]; 
		}
		return -1;
	}
	
	function obtenerHorasAsignadasGrupo($idGrupo)
	{
		global $con;
		$consulta="SELECT idInstanciaPlanEstudio FROM 4520_grupos WHERE idGrupos=".$idGrupo;
		$idInstanciaPlan=$con->obtenerValor($consulta);
		
		$duracionHora=obtenenerDuracionHoraGrupo($idGrupo);
		if($duracionHora=="")
			$duracionHora=60;
		$query="SELECT dia,horaInicio,horaFin FROM 4522_horarioGrupo WHERE idGrupo=".$idGrupo."  and fechaInicio<=fechaFin order by dia";
		$res=$con->obtenerFilas($query);
		$arrDias=array();
		while($fHorario=mysql_fetch_row($res))
		{
			if(!isset($arrDias[$fHorario[0]]))
				$arrDias[$fHorario[0]]=0;
			$hInicio=strtotime($fHorario[1]);
			$hFin=strtotime($fHorario[2]);
			$resta=strtotime("00:00:00")+$hFin-$hInicio;

			$minutos=(date("H",$resta)*60)+date("i",$resta);
			$arrDias[$fHorario[0]]+=$minutos;
				
		}
		$totalhorasSemana=0;
		if(sizeof($arrDias)>0)
		{
			foreach($arrDias as $d=>$resto)
			{
				$arrDias[$d]=$resto/$duracionHora;
				$totalhorasSemana+=$arrDias[$d];
			}	
		}
		return $totalhorasSemana;
	}
	
	function obtenerMateriasGrupoAgrupador($idGrupo)
	{
		global $con;
		$cadMateriasAgrupa="";
		$consulta="SELECT m.nombreMateria from 4520_grupos g,4502_Materias m WHERE idGrupoAgrupador=".$idGrupo." AND m.idMateria=g.idMateria order by nombreMateria";
		$resMateria=$con->obtenerFilas($consulta);
		while($fMateriaAg=mysql_fetch_row($resMateria))
		{
			if($cadMateriasAgrupa=="")
				$cadMateriasAgrupa=$fMateriaAg[0];
			else
				$cadMateriasAgrupa.=", ".$fMateriaAg[0];
			
		}
		return $cadMateriasAgrupa;
	}
	
	function obtenerNombreCurso($idGrupo,$completo=false)
	{
		global $con;
		$consulta="SELECT idMateria FROM 4520_grupos WHERE idGrupos=".$idGrupo;
		$idMateria=$con->obtenerValor($consulta);
		$nMateria="";
		$consulta="";
		if($idMateria>0)
		{
			$consulta="select nombreMateria FROM 4502_Materias m WHERE m.idMateria=".$idMateria;
		}
		else
		{
			$consulta="select nombreUnidad from 4508_unidadesContenedora WHERE idUnidadContenedora=".abs($idMateria);
		}
		$nMateria=$con->obtenerValor($consulta);
		if($completo)
			$nMateria.=" (".obtenerMateriasGrupoAgrupador($idGrupo).")";
		return $nMateria;	
	}	
	
	function obtenerNombreCursoCompleto($idGrupo)
	{
		global $con;
		$consulta="SELECT idMateria,idInstanciaPlanEstudio FROM 4520_grupos WHERE idGrupos=".$idGrupo;
		$fGrupo=$con->obtenerPrimeraFila($consulta);
		$idMateria=$fGrupo[0];
		$idInstancia=$fGrupo[1];
		$nMateria="";
		$consulta="";
		if($idMateria>0)
		{
			$consulta="select nombreMateria FROM 4502_Materias m WHERE m.idMateria=".$idMateria;
		}
		else
		{
			$consulta="select nombreUnidad from 4508_unidadesContenedora WHERE idUnidadContenedora=".abs($idMateria);
		}
		$nMateria=$con->obtenerValor($consulta);
		
		return $nMateria."(".obtenerNombreInstanciaPlan($idInstancia).")";	
	}
	
	function procesarEventosBiometricos($horaMarca="",$validarPlanteles=false)
	{
		global $con;
		$arrPlantelesBiometrico=array();
		$arrRecesos=obtenerArregloRecesos();
		$consulta="SELECT id__494_tablaDinamica,cmbPlanteles FROM _494_tablaDinamica ";
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$arrPlantelesBiometrico[$fila[1]]=array();
			$arrPlantelesBiometrico[$fila[1]][$fila[1]]=1;
			$consulta="SELECT plantelCompartido FROM _494_gridSedeCompartida WHERE idReferencia=".$fila[0];
			$resC=$con->obtenerFilas($consulta);
			while($fPlantel=mysql_fetch_row($resC))
			{
				$arrPlantelesBiometrico[$fila[1]][$fPlantel[0]]=1;
			}
		}
		
		$arrTerminalesEventos=array();
		$consulta="SELECT DISTINCT noTerminal,plantel FROM 9105_eventosRecibidos WHERE marcaSincronizacion='".$horaMarca."'";
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$consulta="select nombreTerminal from _494_gridBiometricos where idTerminal=".$fila[0];
			$descripcion=$con->obtenerValor($consulta);
			$obj["plantel"]=$fila[1];
			$obj["noTerminal"]=$fila[0];
			$obj["descripcion"]=$descripcion;
			$obj["horaMarca"]=$horaMarca;
			$arrTerminalesEventos[$fila[0]]=$obj;
		}

		$cadPlantelesProcesar="";
		$consulta="SELECT  id__494_tablaDinamica,cmbPlanteles FROM _494_tablaDinamica";
		$res=$con->obtenerFilas($consulta);
		while($f=mysql_fetch_row($res))
		{
			$considerarPlantel=true;
			$consulta="SELECT idTerminal FROM _494_gridBiometricos WHERE idReferencia=".$f[0]." and activo=1";
			
			$resTerminal=$con->obtenerFilas($consulta);
			if($con->filasAfectadas>0)
			{
				while($fTerminal=mysql_fetch_row($resTerminal))
				{
					
					if(!isset($arrTerminalesEventos[$fTerminal[0]]))
					{
						$considerarPlantel=false;
						break;
					}
				}
			}
			else
				$considerarPlantel=false;
			if($considerarPlantel)
			{
				if($cadPlantelesProcesar=="")
					$cadPlantelesProcesar="'".$f[1]."'";
				else
					$cadPlantelesProcesar.=",'".$f[1]."'";
			}
		}

	
		$arrTipoIncidencias=array();
		$consulta="SELECT tipoEvento,tipoincidencia FROM _484_gridTiposIncidencias ORDER BY tipoEvento";

		$res=$con->obtenerFilas($consulta);
		while($f=mysql_fetch_row($res))
		{
			$arrTipoIncidencias["".$f[0]]=$f[1];
		}

		$comp="";
		if($validarPlanteles)
		{
			if($cadPlantelesProcesar=="")
				$cadPlantelesProcesar="-1";
			$comp=" and plantel in (".$cadPlantelesProcesar.")";
		}
		$consulta="SELECT cmbUnirBloquesContiguos FROM _484_tablaDinamica";
		$esquemaProcesamientoEventos=$con->obtenerValor($consulta);
		$consulta="SELECT DISTINCT idUsuario FROM  9105_eventosRecibidos where 1=1 ".$comp."   order by idUsuario"; //  and idUsuario=14339
		
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			if($esquemaProcesamientoEventos==0) //Asistencia por clase
			{
				if(!procesarEventosBiometricosUsuario($fila[0],$arrTipoIncidencias,$arrPlantelesBiometrico,$horaMarca,$arrRecesos,$comp))
					return false;
			}
			else//Asistencia por bloque de clase
			{
				if(!procesarEventosBiometricosUsuarioBloque($fila[0],$arrTipoIncidencias,$arrPlantelesBiometrico,$horaMarca,$arrRecesos,$comp,$validarPlanteles))
					return false;
			}
		}
		$x=0;

		$query[$x]="begin";
		$x++;
		
		$consulta="select count(*) from 9105_sincronizacionSistema";
		$nCount=$con->obtenerValor($consulta);
		if($nCount>0)
			$query[$x]="update 9105_sincronizacionSistema set ultimaSincronizacion='".$horaMarca."'";
		else
			$query[$x]="INSERT INTO 9105_sincronizacionSistema(ultimaSincronizacion) VALUES('".$horaMarca."')";

		$x++;
		if(sizeof($arrTerminalesEventos)>0)
		{
			foreach($arrTerminalesEventos as $t)
			{
				$consulta="select count(*) from 9105_sincronizacionTerminales where 
							plantel='".$t["plantel"]."' and noTerminal='".$t["noTerminal"]."'";
				
				$nCount=$con->obtenerValor($consulta);

				if($nCount>0)
				{
					$query[$x]="update 9105_sincronizacionTerminales set fechaSincronizacion='".$horaMarca."' where 
								plantel='".$t["plantel"]."' and noTerminal='".$t["noTerminal"]."'";
				}
				else
				{
					$query[$x]="INSERT INTO 9105_sincronizacionTerminales(plantel,noTerminal,descripcion,fechaSincronizacion) 
								VALUES('".$t["plantel"]."','".$t["noTerminal"]."','".$t["descripcion"]."','".$horaMarca."')";
					
				}

				$x++;
				
			}
		}

		if($cadPlantelesProcesar=="")
			$cadPlantelesProcesar="-1";
		
		if($cadPlantelesProcesar!="-1")
		{
			$arrPlantelesSincronizacion=explode(",",$cadPlantelesProcesar);

			foreach($arrPlantelesSincronizacion as $p)
			{
				$consulta="select count(*) from 9105_sincronizacionPlanteles where plantel=".$p."";

				$nCount=$con->obtenerValor($consulta);
				if($nCount==0)
					$query[$x]="INSERT INTO 9105_sincronizacionPlanteles(plantel,marcaSincronizacion) VALUES(".$p.",'".$horaMarca."')";
				else
					$query[$x]="update 9105_sincronizacionPlanteles set marcaSincronizacion='".$horaMarca."' where plantel=".$p."";
				$x++;	
				$fecha=date("Y-m-d",strtotime("-1 days",strtotime($horaMarca)));
				
				$consulta="select count(*) from 9105_eventosControlAsistencia where fechaEvento='".date("Y-m-d",strtotime($horaMarca))."' and plantel=".$p."";

				$nEventos=$con->obtenerValor($consulta);
				if($nEventos>0)
				{
					$query[$x]="UPDATE 9105_controlAsistencia SET tipo=1 WHERE tipo=0 AND idGrupo IN (SELECT idGrupos FROM 4520_grupos WHERE Plantel=".$p." AND situacion=1) AND fecha<='".$fecha."'";
					$x++;
				}
			}
		}
		$query[$x]="commit";
		$x++;
		
		return $con->ejecutarBloque($query);
	}
	
	function obtenerArregloRecesos()
	{
		global $con;
		$arrRecesos=array();
		$consulta="SELECT id__476_tablaDinamica,cmbPlantel FROM _476_tablaDinamica";
		$resPlantel=$con->obtenerFilas($consulta);
		while($fReceso=mysql_fetch_row($resPlantel))
		{
			$arrRecesos[$fReceso[0]]=array();
			$arrRecesos[$fReceso[0]]["plantel"]=$fReceso[1];
			$arrRecesos[$fReceso[0]]["instanciasPlan"]=array();
			$consulta="SELECT idInstanciaPlanEstudio FROM _476_gridPlanesEstudio WHERE idReferencia=".$fReceso[0];
			$resIns=$con->obtenerFilas($consulta);
			while($filaIns=mysql_fetch_row($resIns))
			{
				array_push($arrRecesos[$fReceso[0]]["instanciasPlan"],$filaIns[0]);
			}
			$consulta="SELECT horaInicio,horaFin FROM _476_gridRecesos where idReferencia=".$fReceso[0];
			$resAux=$con->obtenerFilas($consulta);
			while($fAux=mysql_fetch_row($resAux))
			{
				$arrRecesos[$fReceso[0]]["horario"][$fAux[0]]=$fAux[1];
			}
		}
		return $arrRecesos;
	}
	
	function procesarEventosBiometricosUsuario($idUsuario,$arrTipoIncidencias,$arrPlantelesBiometrico,$horaMarca,$arrRecesos,$compEvt,$validarPlantel=true)
	{
		global $con;
		$consulta="SELECT * FROM _484_tablaDinamica";
		$fConf=$con->obtenerPrimeraFila($consulta);
		$checarSalida=true;
		$ignorarEvento=false;
		$fInstitucion=true;
		$toleranciaAntesHoraInicio=10;//--
		$toleranciaDespuesHoraInicio=10;
		$toleranciaAntesHoraFin=10;//--
		$toleranciaFalta=10;
		$toleranciaIgnoraSalida=0;
		$toleranciaRegistroSalida=10;
		$horaCero=0;//strtotime("00:00:00");
		$tipoDefinicion=2; 
		$posSalida=0;
		$resultado2=0;
		$noHorasBloqueExcepcion=2;
		$tRetardoBloqueExcepcion=80;
		$tActicipoBloqueExcepcion=80;
		$toleranciaRegistroSalidaClaseNoContinua=20;
		if($fConf)
		{
			$toleranciaAntesHoraInicio=$fConf[10];
			$toleranciaDespuesHoraInicio=$fConf[19];
			$toleranciaFalta=$fConf[11];
			$toleranciaAntesHoraFin=$fConf[12];
			$toleranciaIgnoraSalida=$fConf[20];
			$toleranciaRegistroSalida=$fConf[21];
			$toleranciaRegistroSalidaClaseNoContinua=$fConf[22];
		}

		$consulta="SELECT idEvento,idUsuario,fechaEvento,horaEvento,noTerminal,plantel,'','',DATE_FORMAT(horaEvento,'%H:%i:%s'),marcaSincronizacion 
					FROM  9105_eventosRecibidos where idUsuario=".$idUsuario." ".$compEvt." order by fechaEvento,horaEvento  asc";// and fechaEvento='2012-05-04' 
		
		$horaEventoPlantel=array();
		$res=$con->obtenerFilas($consulta);
		$arrEventos=array();
		while($fila=mysql_fetch_row($res))
		{
			if(!isset($arrEventos[$fila[2]]))
				$arrEventos[$fila[2]]=array();
			$fila[3]=strtotime($fila[3]);
			$horaEventoPlantel[$fila[5]]=$fila[3];
			array_push($arrEventos[$fila[2]],$fila);
		}
		
		$arrGrupos=array();
		
		foreach($arrEventos as $fecha=>$arrFilas)
		{
			$x=0;
			$query=array();
			$query[$x]="begin";
			$x++;
			$consulta="SELECT * FROM 9105_controlAsistencia WHERE idUsuario=".$idUsuario." AND fecha='".$fecha."'";

			$resAsistenciaCerrada=$con->obtenerFilas($consulta);
			$arrGruposIgnora=array();
			while($fAsistencia=mysql_fetch_row($resAsistenciaCerrada))
			{
				$obj[0]=$fAsistencia[12];
				$obj[1]=$fAsistencia[14];
				$obj[2]=$fAsistencia[5];
				array_push($arrGruposIgnora,$obj);
			}
			
			$arrComisiones=array();
			$consulta="SELECT id__489_tablaDinamica,tipoComision FROM _489_tablaDinamica WHERE  '".$fecha."'>=dteFechaInicial AND '".$fecha."'<=dteFechaFinal AND cmbDocentes=".$idUsuario." and idEstado=2";
			$res=$con->obtenerFilas($consulta);
			while($fila=mysql_fetch_row($res))
			{
				$consulta="SELECT idRegistroModuloSesionesClase FROM 4560_registroModuloSesionesClase WHERE  idFormulario=489 AND idReferencia=".$fila[0];
				$idReferenciaComision=$con->obtenerValor($consulta);
				$consulta="SELECT idGrupo,horaInicioBloque,horaFinBloque FROM 4561_sesionesClaseModulo WHERE idReferencia=".$idReferenciaComision;
				$resCon=$con->obtenerFilas($consulta);
				while($filaComision=mysql_fetch_row($resCon))
				{
					$arrComisiones[$filaComision[0]."_".$filaComision[1]."_".$filaComision[2]]=1;
				}
				
				
			}
						
			$fechaActual=strtotime($fecha);
			$arrGrupos=array();			
			
			$consulta="SELECT idGrupos,g.Plantel FROM 4520_grupos g,4519_asignacionProfesorGrupo a WHERE a.idUsuario=".$idUsuario." AND  a.participacionPrincipal=1 AND a.idGrupo=g.idGrupos  
						AND a.fechaAsignacion<='".date("Y-m-d",$fechaActual)."' AND a.fechaBaja>='".date("Y-m-d",$fechaActual)."' ";
			
			$res=$con->obtenerFilas($consulta);
			while($fila=mysql_fetch_row($res))
			{
				
						
				if(!esDiaInhabilEscolar($fila[1],$fecha))
				{
					$o[0]=$fila[0];
					$o[1]=$fila[1];
					array_push($arrGrupos,$o);
				}
			}

			
			$consulta="SELECT idGrupos,idFormularioAccion,idRegistroAccion,g.Plantel FROM 4520_grupos g,4519_asignacionProfesorGrupo a 
						WHERE a.idUsuario=".$idUsuario." AND a.participacionPrincipal=0 AND a.idGrupo=g.idGrupos AND 
						a.fechaAsignacion<='".date("Y-m-d",$fechaActual)."' AND a.fechaBaja>='".date("Y-m-d",$fechaActual)."'";
			$res=$con->obtenerFilas($consulta);
			while($fila=mysql_fetch_row($res))
			{
				if((!existeValor($arrGrupos,$fila[0]))&&(!esDiaInhabilEscolar($fila[3],$fecha)))
				{
					$o[0]=$fila[0];
					$o[1]=$fila[3];
					array_push($arrGrupos,$o);
				}
			}

			$listGrupos="";
			$arrGruposPlantel=array();
			foreach($arrGrupos as $iGrupo)
			{
				if($listGrupos=="")
					$listGrupos=$iGrupo[0];
				else
					$listGrupos.=",".$iGrupo[0];
				$arrGruposPlantel[$iGrupo[0]]=$iGrupo[1];
			}

			if($listGrupos=="")
				$listGrupos=0;
			
			$dia=date("w",$fechaActual);
			$consulta="(SELECT idHorarioGrupo,idGrupo,dia,horaInicio,horaFin,idAula  FROM 4522_horarioGrupo WHERE idGrupo IN (".$listGrupos.") AND dia=".$dia." and '".$fecha."'>=fechaInicio and '".$fecha."'<=fechaFin) union
					(SELECT (idSesionReposicion*-1),idGrupo,0,horaInicio,horaFin,idAula FROM 4563_sesionesReposicion s,4562_registroReposicionSesion r 
					WHERE fechaReposicion='".$fecha."' AND idGrupo IN (".$listGrupos.") and r.idRegistroReposicion=s.idRegistroReposicion and r.idUsuario=".$idUsuario.") ORDER BY horaInicio";

			$resH=$con->obtenerFilas($consulta);
			$arrHorario=array();
			while($filaH=mysql_fetch_row($resH))
			{
				if($filaH[0]<0)
				{
					$consulta="select Plantel FROM 4520_grupos WHERE idGrupos=".$filaH[1];
					$p=$con->obtenerValor($consulta);
					$arrGruposPlantel[$filaH[1]]=$p;
				}
				
				
				$filaH[6]=0;
				$filaH[7]=$arrGruposPlantel[$filaH[1]];
				$filaH[3]=($filaH[3]);
				$filaH[4]=($filaH[4]);
				$filaH[8]=strtotime("-".$toleranciaAntesHoraInicio." minutes",strtotime($filaH[3]));
				$filaH[9]=strtotime("+".$toleranciaRegistroSalida." minutes",strtotime($filaH[4]));
				$uEvento=obtenerUltimoEvento($arrFilas,$arrGruposPlantel[$filaH[1]]);
				$horaUltimoEvento=$uEvento[3];
				if($filaH[8]<=$horaUltimoEvento)
					array_push($arrHorario,$filaH);
			}
			
			$nArrHorario=array();
			$nAux=0;
			$arrHorarioAux=array();
			for($nHorario=0;$nHorario<sizeof($arrHorario);$nHorario++)
			{
				$hActual=$arrHorario[$nHorario];

				$hActual[12]=0;
				if(($nHorario+1)<sizeof($arrHorario))
				{
					$hSiguiente=$arrHorario[$nHorario+1];
					if($hActual[4]!=$hSiguiente[3])
						$hActual[12]=1;
					
				}
				else
					$hActual[12]=1;
				if($hActual[12]==1)
				{
					$hActual[9]=strtotime("+".$toleranciaRegistroSalidaClaseNoContinua." minutes",strtotime($hActual[4]));
				}
				
				array_push($arrHorarioAux,$hActual);
			}
			
			
			$nArrHorario=array();
			for($nHorario=0;$nHorario<sizeof($arrHorarioAux);$nHorario++)
			{
				$h=$arrHorarioAux[$nHorario];

				$pos=obtenerHoraFinBloque($arrHorarioAux,$h,($nHorario+1),$arrRecesos,$h[7]);
				
				if($pos!=-1)
				{
					
					$nHorario=$pos;
					$h[4]=$arrHorarioAux[$pos][4];
					$h[9]=$arrHorarioAux[$pos][9];
				}
				$h[10]=date("H:i:s",$h[8]);
				$h[11]=date("H:i:s",$h[9]);
							
				array_push($nArrHorario,$h);	
				
			}
			$arrHorarioAux=$nArrHorario;
			$arrHorario=array();
			
			if(sizeof($arrHorarioAux)>0)
			{
				foreach($arrHorarioAux as $filaH)
				{
					$encontradoH=false;
					if(sizeof($arrGruposIgnora)>0)
					{
						foreach($arrGruposIgnora as $datosHorario)
						{
							if(($datosHorario[0]==$filaH[1])&&($datosHorario[1]==$filaH[3]))
							{
								if($datosHorario[2]==1)
									$encontradoH=true;
								else
									$filaH[6]=1;
							}
						}
						
					}
	
					if(!$encontradoH)
					{
						foreach($arrComisiones as $com=>$resto)
						{
							$dComision=explode("_",$com);
							if(($dComision[0]==$filaH[1])&&($dComision[1]==$filaH[3]))
							{
								$encontradoH=true;
							}
						}
					}

					
					if(!$encontradoH)
					{
						array_push($arrHorario,$filaH);
					}
				}
			}	
			//$arrHorario=$nArrHorario;  //Obtención de clases
			$arrEv=array();
			$nEventos=sizeof($arrFilas);
			$hEncontrado=null;
			$posActualEv=0;
			
			if((sizeof($arrHorario)>0)&&($nEventos>0))  //Busqueda eventos inconclusos
			{
				for($nPos=0;$nPos<sizeof($arrHorario);$nPos++)
				{
						
					if($arrHorario[$nPos][6]==1)
					{

						$consulta="select idAsistencia,esRetardo,tRetardo,idUsuario,fecha,hora_entrada,hora_salida FROM 9105_controlAsistencia WHERE idUsuario=".$idUsuario." AND fecha='".$fecha."' AND tipo=0 
									AND idGrupo=".$arrHorario[$nPos][1]." AND horaInicioBloque='".$arrHorario[$nPos][3]."'";

						$fAsistencia=$con->obtenerPrimeraFila($consulta);
						$idRegistro=$fAsistencia[0];
						
						$consulta="SELECT * FROM 9105_eventosControlAsistencia WHERE idReferencia=".$idRegistro;
						$resEventos=$con->obtenerFilas($consulta);
						$lEventos="";
						if($con->filasAfectadas==0)
						{
							$consulta="SELECT * FROM 9105_eventosControlAsistencia WHERE idUsuario=".$fAsistencia[3]." AND fechaEvento='".$fAsistencia[4]."' 
											AND ((horaEvento='".$fAsistencia[5]."') or (horaEvento='".$fAsistencia[6]."')) ORDER BY idEvento";
							$resEventos=$con->obtenerFilas($consulta);
							$query[$x]="delete from 9105_eventosControlAsistencia where idUsuario=".$fAsistencia[3]." AND fechaEvento='".$fAsistencia[4]."' AND 
										((horaEvento='".$fAsistencia[5]."') or (horaEvento='".$fAsistencia[6]."'))";
							$x++;
						}
						while($fEvento=mysql_fetch_row($resEventos))
						{
							$ev= array();
							$ev[0]=$fEvento[0];
							$ev[1]=$fEvento[3];
							$ev[2]=$fEvento[1];
							$ev[3]=strtotime($fEvento[2]);
							$ev[4]=$fEvento[6];
							$ev[5]=$fEvento[4];
							$ev[6]="";
							$ev[7]="";
							$ev[8]=$fEvento[2];
							$ev[9]=$fEvento[8];
							$pEv=0;
		
							
							$arrFilaAuxiliar=array();
							$encFila=false;
	
							for($pEv=0;$pEv<$nEventos;$pEv++)
							{
								$evC=$arrFilas[$pEv];
		
								if(($ev[3]<=$evC[3])&&(!$encFila))
								{
									array_push($arrFilaAuxiliar,$ev);
									$encFila=true;
								}
								array_push($arrFilaAuxiliar,$evC);
							}
							$arrFilas=$arrFilaAuxiliar;
							$nEventos=sizeof($arrFilas);
							if($lEventos=="")
								$lEventos=$fEvento[0];
							else
								$lEventos.=",".$fEvento[0];
						}
						if($lEventos=="")
							$lEventos=-1;
						$arrHorario[$nPos][6]=0;
						$query[$x]="delete from 9105_controlAsistencia where idAsistencia=".$idRegistro;
						$x++;
						$query[$x]="delete from 9105_eventosControlAsistencia where idEvento in (".$lEventos.")";
						$x++;

						
					}
				}
			}

			$nEventos=sizeof($arrFilas);
			for($pos=$posActualEv;$pos<$nEventos;$pos++)
			{
				$enc=false;
				$ev=$arrFilas[$pos];
				$posHorario=0;
				foreach($arrHorario as $horario)	
				{
					if(($ev[3]>=$horario[8])&&($ev[3]<=$horario[9]))
					{
						switch($tipoDefinicion)
						{
							case 1:
								$enc=true;
								if($validarPlantel)
								{
									if(!isset($arrPlantelesBiometrico[$ev[5]][$horario[7]]))
									{
										$enc=false;
									}
								}
								
							break;
							case 2:
								
								$evS=obtenerEventoSiguiente($arrFilas,$pos);
								$horaS=obtenerSiguienteSesion($arrHorario,$posHorario);
								if($horaS!=null)
								{
									
									if(estaEnLimiteChecaEntrada($horaS,$toleranciaDespuesHoraInicio,$ev))
									{
										if($evS==null)
											$enc=false;
										else
										{
											if(estaEnLimiteChecaEntrada($horaS,$toleranciaDespuesHoraInicio,$evS))
											{
												
												$enc=true;
											}
											else
												$enc=false;
										}
									}
									else
										$enc=true;
								}
								else
									$enc=true;
								if($enc)
								{
									if($validarPlantel)
									{
										
										if(!isset($arrPlantelesBiometrico[$ev[5]][$horario[7]]))
										{
											
											$enc=false;
										}
									}
								}
								
								
							break;
						}
						if($enc)
						{
//							echo date("H:i",$ev[3])."<br>";
							$hEncontrado=$horario;
							break;
						}
					}
					$posHorario++;
				}

				$arrEv=array();
				if($enc)
				{
					
					$tRetardo=$horaCero;
					$tAnticipado=$horaCero;
					$tExtra=$horaCero;
					$esRetardo=0;
					$resEntrada=0;
					$tInicio=strtotime("+".$toleranciaDespuesHoraInicio." minutes",strtotime($hEncontrado[3]));
					$tLimiteRetardo=strtotime("+".$toleranciaFalta." minutes",$tInicio);
					if(($ev[3]>=$horario[8])&&($ev[3]<=$tInicio))
					{
						
						$resultado=1; //Asistencia
						$resEntrada=1;
						$ev[6]=$resultado;
						$ev[7]='@idRegistro';
						array_push($arrEv,$ev);
					}
					else
					{
						$tRetardo=$horaCero+$ev[3]-$tInicio;
						if(($ev[3]>$tInicio)&&($ev[3]<=$tLimiteRetardo))
						{
							$resultado=2; //Asistencia con retardo
							$resEntrada=2;
							$ev[6]=$resultado;
							$ev[7]='@idRegistro';
							array_push($arrEv,$ev);
							$esRetardo=1;
						}
						else
						{
							$resultado=3;
							$resEntrada=3;
							$ev[6]=$resultado;
							$ev[7]='@idRegistro'; //Falta, por entrada fuera de los límites permitidos
							array_push($arrEv,$ev);
							$esRetardo=1;
							
						}
					}
					

					$posSalida=obtenerSalida($arrFilas,$arrHorario,($pos+1),$posHorario,$toleranciaAntesHoraFin,($toleranciaDespuesHoraInicio+$toleranciaFalta),$validarPlantel,$tipoDefinicion,$arrPlantelesBiometrico);
					if($posSalida==-1) ////Entrada sin salida
					{
						$horasBloque=obtenerNumeroHorasBloque($hEncontrado[1],$hEncontrado[3],$hEncontrado[4],$hEncontrado[7],$arrRecesos);
						//if($horasBloque<$noHorasBloqueExcepcion)
						{						
							$resEvento="4";
							$query[$x]="INSERT INTO 9105_controlAsistencia(idUsuario,fecha,hora_entrada,hora_salida,tipo,tRetardo,esRetardo,tAnticipado,tExtra,idGrupo,tipoEvento,horaInicioBloque,valorEvento,horaFinBloque)
																VALUES(".$idUsuario.",'".$fecha."','".date("H:i:s",$ev[3])."',NULL,0,'".convertirSegundosTiempo($tRetardo)."',".$esRetardo.",'".convertirSegundosTiempo($horaCero)."','".
																convertirSegundosTiempo($horaCero)."',".$hEncontrado[1].",".$resEvento.",'".date("H:i:s",strtotime($hEncontrado[3]))."',".$arrTipoIncidencias["".$resEvento].",'".date("H:i:s",strtotime($hEncontrado[4]))."')";
							$x++;
						}
						
						$query[$x]="set @idRegistro:=(select last_insert_id())";
						$x++;
						registrarAccionesEvento($arrEv,$query,$x,$horaMarca);	
						array_splice($arrHorario,$posHorario,1);	
					}
					else
					{
						if(($posSalida==$pos)||($posSalida==-2))
						{
							$resEvento=0;
							$ultimoEv=obtenerUltimoEvento($arrFilas,$horario[7]);
							if($ultimoEv[3]>$horario[9]) 
							{
								if($resultado!=3)//Omisión de salida
								{
									$resEvento=4;
										
								}
								else 
								{
									$minhora_salida=strtotime("-".$toleranciaAntesHoraFin." minutes",strtotime($hEncontrado[4]));
									$horaEntrada=strtotime("+".($toleranciaDespuesHoraInicio+$toleranciaFalta)." minutes",strtotime($hEncontrado[3]));
									$diferenciaConEntrada=0;
									if($ev[3]>$horaEntrada)
										$diferenciaConEntrada=$ev[3]-$horaEntrada;
									else
										$diferenciaConEntrada=$horaEntrada-$ev[3];
										
									$diferenciaConSalida=0;
									if($ev[3]>$minhora_salida)	
										$diferenciaConSalida=$ev[3]-$minhora_salida;
									else
										$diferenciaConSalida=$minhora_salida-$ev[3];
									if($diferenciaConEntrada<$diferenciaConSalida)//Entrada fuera de los limites permitidos / Omisión de salida
									{
										$resEvento=5;
									}
									else	//Salida con omisión de entrada
									{
										$resultado=0;
										if(($ev[3]>=$minhora_salida)&&($ev[3]<=$hEncontrado[9]))
										{
											$resultado=5;
											$resEvento=6;
										}
										else
										{
											$resEvento=7;
											$resultado=4;	
										}
										$arrEv[sizeof($arrEv)-1][6]=$resultado;
										
									}
								}
								
								if(($resEvento!=6)&&($resEvento!=7))
								{
									$horasBloque=obtenerNumeroHorasBloque($hEncontrado[1],$hEncontrado[3],$hEncontrado[4],$hEncontrado[7],$arrRecesos);
									//if($horasBloque<$noHorasBloqueExcepcion)
									{
										$query[$x]="INSERT INTO 9105_controlAsistencia(idUsuario,fecha,hora_entrada,hora_salida,tipo,tRetardo,esRetardo,tAnticipado,tExtra,idGrupo,tipoEvento,horaInicioBloque,valorEvento,horaFinBloque)
																VALUES(".$idUsuario.",'".$fecha."','".date("H:i:s",$ev[3])."',NULL,1,'".convertirSegundosTiempo($tRetardo)."',".$esRetardo.",'".convertirSegundosTiempo($horaCero)."','".convertirSegundosTiempo($horaCero)."',".$hEncontrado[1].
																",".$resEvento.",'".date("H:i:s",strtotime($hEncontrado[3]))."',".$arrTipoIncidencias["".$resEvento].",'".date("H:i:s",strtotime($hEncontrado[4]))."')";
										$x++;
									}
									
								}
								else  //Salida con omision de entrada
								{
									$tAnticipado=0;
									$horasBloque=obtenerNumeroHorasBloque($hEncontrado[1],$hEncontrado[3],$hEncontrado[4],$hEncontrado[7],$arrRecesos);
									//if($horasBloque<$noHorasBloqueExcepcion)
									{
										$query[$x]="INSERT INTO 9105_controlAsistencia(idUsuario,fecha,hora_entrada,hora_salida,tipo,tRetardo,esRetardo,tAnticipado,tExtra,idGrupo,tipoEvento,horaInicioBloque,valorEvento,horaFinBloque)
																VALUES(".$idUsuario.",'".$fecha."',NULL,'".date("H:i:s",$ev[3])."',1,'".convertirSegundosTiempo($horaCero)."',0,'".convertirSegundosTiempo($tAnticipado)."','".convertirSegundosTiempo($tExtra).
																"',".$hEncontrado[1].",".$resEvento.",'".date("H:i:s",strtotime($hEncontrado[3]))."',".$arrTipoIncidencias["".$resEvento].",'".date("H:i:s",strtotime($hEncontrado[4]))."')";
										$x++;
									}
									
								}
								$query[$x]="set @idRegistro:=(select last_insert_id())";
								$x++;
								registrarAccionesEvento($arrEv,$query,$x,$horaMarca);
								array_splice($arrHorario,$posHorario,1);
							}
							else//Entrada sin salida aperturada
							{
								$resEvento="4";
								$horasBloque=obtenerNumeroHorasBloque($hEncontrado[1],$hEncontrado[3],$hEncontrado[4],$hEncontrado[7],$arrRecesos);
								//if($horasBloque<$noHorasBloqueExcepcion)
								{
									$query[$x]="INSERT INTO 9105_controlAsistencia(idUsuario,fecha,hora_entrada,hora_salida,tipo,tRetardo,esRetardo,tAnticipado,tExtra,idGrupo,tipoEvento,horaInicioBloque,valorEvento,horaFinBloque)
												VALUES(".$idUsuario.",'".$fecha."','".date("H:i:s",$ev[3])."',NULL,0,'".convertirSegundosTiempo($tRetardo)."',".$esRetardo.",'".convertirSegundosTiempo($horaCero)."','".convertirSegundosTiempo($horaCero)."',".
												$hEncontrado[1].",".$resEvento.",'".date("H:i:s",strtotime($hEncontrado[3]))."',".$arrTipoIncidencias["".$resEvento].",'".date("H:i:s",strtotime($hEncontrado[4]))."')";
									$x++;
								
								}
								
								$query[$x]="set @idRegistro:=(select last_insert_id())";
								$x++;
								registrarAccionesEvento($arrEv,$query,$x,$horaMarca);
								array_splice($arrHorario,$posHorario,1);
							}
						}
						else
						{
							
							$vInicio=($pos+1);
							for($aux=$vInicio;$aux<=$posSalida;$aux++)
							{
								$resultado=10;
								$evS=$arrFilas[$aux];
								$idReferencia="@idRegistro";
								if($aux==$posSalida)
								{
									$resEvento=0;
									$eventoCerrado=1;
									$tolAntesChecado=strtotime("-".$toleranciaAntesHoraFin." minutes",strtotime($hEncontrado[4]));
									if(($evS[3]>=$tolAntesChecado)&&($evS[3]<=$hEncontrado[9]))  //Salida correcta
									{
										$resultado=5; //Salida
										switch($resEntrada)
										{
											case 1:
												$resEvento="1";
											break;
											case 2:
												$resEvento="9";
											break;
											case "3":

												$minhora_salida=strtotime("-".$toleranciaAntesHoraFin." minutes",strtotime($hEncontrado[4]));
												$horaEntrada=strtotime("+".($toleranciaDespuesHoraInicio+$toleranciaFalta)." minutes",strtotime($hEncontrado[3]));
												
												$diferenciaConEntrada=0;
												if($ev[3]>$horaEntrada)
												{
													$diferenciaConEntrada=$ev[3]-$horaEntrada;
												}
												else
													$diferenciaConEntrada=$horaEntrada-$ev[3];

												$diferenciaConSalida=0;
												if($ev[3]>$minhora_salida)
												{
													$diferenciaConSalida=($horaCero+$ev[3])-$minhora_salida;
												}
												else
												{
													$diferenciaConSalida=($horaCero+$minhora_salida)-($horaCero+$ev[3]);
												}
												
												if($diferenciaConEntrada>$diferenciaConSalida)  //Salida con omision de entrada
												{
													$resEvento=6;
													$resEntrada=6;
													cambiarResultadoEvento($ev[0],10,$arrEv);
												}
												else
												{
													$resEvento=14;
												}
											
											break;
										}
										

									}
									else
									{
										
										if($posSalida==sizeof($arrFilas)-1)
										{
											$eventoCerrado=0;
										}
										
										if($evS[3]<$tolAntesChecado)
										{
											$resultado=4;
											switch($resEntrada)
											{
												case 1:
												
													
													$tAnticipado=$horaCero+$tolAntesChecado-$evS[3]; 
													$diferenciaEv=$evS[3]-$ev[3];
													
													if($diferenciaEv>$toleranciaIgnoraSalida)//Entrada con Salida anticipada
													{
														$resEvento=2;
													}
													else
													{
														$resultado=10;  //Evento ignorado, //Entrada con omisión de salida
														$resEvento=4;	
													}
												break;
												case 2:
													$tAnticipado=$horaCero+$tolAntesChecado-$evS[3]; 
													$diferenciaEv=$evS[3]-$ev[3];
													
													if($diferenciaEv>$toleranciaIgnoraSalida)//Registro de entrada con retardo y salida anticipada
													{
														$resEvento=10;
													}
													else
													{
														$resultado=10;  //Evento ignorado, //Entrada con omisión de salida
														$resEvento=11;	
													}
												
												break;
												case 3:
													$tAnticipado=$horaCero+$tolAntesChecado-$evS[3]; 
													$diferenciaEv=$evS[3]-$ev[3];
													
													if($diferenciaEv>$toleranciaIgnoraSalida)
													{
														$resEvento=8;
													}
													else
													{
														$resultado=10;  //Evento ignorado, //Entrada con omisión de salida
														$resEvento=5;	
													}
												
												break;
											}
											
										}
										else
										{
											$resultado=11;
											
											
											switch($resEntrada)
											{
												case 1:
													$resEvento=3;
												break;
												case 2:
													$resEvento=12;	
												
												break;
												case 3:
													$resEvento=13;
												
												break;
											}
											
											
											
											$tExtra=$horaCero+$evS[3]-$hEncontrado[9];  //Entrada con con Salida fuera de los limites permitidos
											
											
										}
									}
									
									
									switch($resEvento)
									{
										case 6:
											$horasBloque=obtenerNumeroHorasBloque($hEncontrado[1],$hEncontrado[3],$hEncontrado[4],$hEncontrado[7],$arrRecesos);
											$minutosAnticipados=convertirHoraMinutos($tAnticipado);
											//if(($horasBloque<$noHorasBloqueExcepcion)||($minutosAnticipados<90))
											{
												$query[$x]="INSERT INTO 9105_controlAsistencia(idUsuario,fecha,hora_entrada,hora_salida,tipo,tRetardo,esRetardo,tAnticipado,tExtra,idGrupo,tipoEvento,horaInicioBloque,valorEvento,horaFinBloque)
																VALUES(".$idUsuario.",'".$fecha."',NULL,'".date("H:i:s",$evS[3])."',".$eventoCerrado.",'".convertirSegundosTiempo($horaCero)."',0,'".convertirSegundosTiempo($tAnticipado)."','".convertirSegundosTiempo($tExtra).
																"',".$hEncontrado[1].",".$resEvento.",'".date("H:i:s",strtotime($hEncontrado[3]))."',".$arrTipoIncidencias["".$resEvento].",'".date("H:i:s",strtotime($hEncontrado[4]))."')";
												$x++;
											}
											/*else
											{
												
												
												
											}*/
										break;
										case 4:
											$horasBloque=obtenerNumeroHorasBloque($hEncontrado[1],$hEncontrado[3],$hEncontrado[4],$hEncontrado[7],$arrRecesos);
											//if($horasBloque<$noHorasBloqueExcepcion)
											{
												
												$query[$x]="INSERT INTO 9105_controlAsistencia(idUsuario,fecha,hora_entrada,hora_salida,tipo,tRetardo,esRetardo,tAnticipado,tExtra,idGrupo,tipoEvento,horaInicioBloque,valorEvento,horaFinBloque)
																VALUES(".$idUsuario.",'".$fecha."','".date("H:i:s",$ev[3])."',NULL,".$eventoCerrado.",'".convertirSegundosTiempo($tRetardo)."',".$esRetardo.",'".convertirSegundosTiempo($horaCero)."','".convertirSegundosTiempo($horaCero).
																"',".$hEncontrado[1].",".$resEvento.",'".date("H:i:s",strtotime($hEncontrado[3]))."',".$arrTipoIncidencias["".$resEvento].",'".date("H:i:s",strtotime($hEncontrado[4]))."')";
												$x++;
											}
											/*else
											{
											}*/
										break;
										default:
											$minutosAnticipados=convertirHoraMinutos($tAnticipado);
											
											$minutosRetardo=convertirHoraMinutos($tRetardo);
											
											$horasBloque=obtenerNumeroHorasBloque($hEncontrado[1],$hEncontrado[3],$hEncontrado[4],$hEncontrado[7],$arrRecesos);
											
											
											//echo $hEncontrado[1]." ".$horasBloque."<br>";

											if(($horasBloque<=$noHorasBloqueExcepcion)||($minutosRetardo==0)&&($minutosAnticipados==0))
											{
												
												$query[$x]="INSERT INTO 9105_controlAsistencia(idUsuario,fecha,hora_entrada,hora_salida,tipo,tRetardo,esRetardo,tAnticipado,tExtra,idGrupo,tipoEvento,horaInicioBloque,valorEvento,horaFinBloque)
																VALUES(".$idUsuario.",'".$fecha."','".date("H:i:s",$ev[3])."','".date("H:i:s",$evS[3])."',".$eventoCerrado.",'".convertirSegundosTiempo($tRetardo)."',".$esRetardo.",'".convertirSegundosTiempo($tAnticipado)."','".
																convertirSegundosTiempo($tExtra)."',".$hEncontrado[1].",".$resEvento.",'".date("H:i:s",strtotime($hEncontrado[3]))."',".$arrTipoIncidencias["".$resEvento].",'".date("H:i:s",strtotime($hEncontrado[4]))."')";
												$x++;
											}
											else
											{
												
												$horaGrupo=obtenenerDuracionHoraGrupo($hEncontrado[1]);
												if(($minutosRetardo>$tRetardoBloqueExcepcion)||($minutosAnticipados>$tActicipoBloqueExcepcion))
												{

													$query[$x]="INSERT INTO 9105_controlAsistencia(idUsuario,fecha,hora_entrada,hora_salida,tipo,tRetardo,esRetardo,tAnticipado,tExtra,idGrupo,tipoEvento,horaInicioBloque,valorEvento,horaFinBloque)
																	VALUES(".$idUsuario.",'".$fecha."','".date("H:i:s",$ev[3])."','".date("H:i:s",$evS[3])."',".$eventoCerrado.",'".convertirSegundosTiempo($tRetardo)."',".$esRetardo.",'".convertirSegundosTiempo($tAnticipado)."','".
																	convertirSegundosTiempo($tExtra)."',".$hEncontrado[1].",".$resEvento.",'".date("H:i:s",strtotime($hEncontrado[3]))."',".$arrTipoIncidencias["".$resEvento].",'".date("H:i:s",strtotime($hEncontrado[4]))."')";
													$x++;
												}
												else
												{
													$hEntradaPago=strtotime($hEncontrado[3]);
													$hSalidaPago=strtotime($hEncontrado[4]);
													$hEntrada=$ev[3];
													$hSalida=$evS[3];
													if($minutosRetardo>0)
													{

														$hSalidaFaltaRetardo=strtotime("+".$horaGrupo." minutes",$hEntradaPago);

														$query[$x]="INSERT INTO 9105_controlAsistencia(idUsuario,fecha,hora_entrada,hora_salida,tipo,tRetardo,esRetardo,tAnticipado,tExtra,idGrupo,tipoEvento,horaInicioBloque,valorEvento,horaFinBloque)
																	VALUES(".$idUsuario.",'".$fecha."','".date("H:i:s",$ev[3])."','".date("H:i:s",$hSalidaFaltaRetardo)."',1,'".convertirSegundosTiempo($horaCero)."',0,'".convertirSegundosTiempo($horaCero)."','".
																	convertirSegundosTiempo($horaCero)."',".$hEncontrado[1].",16,'".date("H:i:s",strtotime($hEncontrado[3]))."',3,'".date("H:i:s",$hSalidaFaltaRetardo)."')";
																	

														$x++;
														$hEntradaPago=$hSalidaFaltaRetardo;
														if(isset($arrRecesos[$hEncontrado[7]]))
														{
															if(isset($arrRecesos[$hEncontrado[7]][date("H:i:s",$hEntradaPago)]))
																$hEntradaPago=strtotime($arrRecesos[$hEncontrado[7]][date("H:i:s",$hEntradaPago)]);
														}

														$hEntrada=$hEntradaPago;
													}
													if($minutosAnticipados>0)
													{
													
														$hEntradaFaltaRetardo=strtotime("-".$horaGrupo." minutes",$hSalidaPago);
														$query[$x]="INSERT INTO 9105_controlAsistencia(idUsuario,fecha,hora_entrada,hora_salida,tipo,tRetardo,esRetardo,tAnticipado,tExtra,idGrupo,tipoEvento,horaInicioBloque,valorEvento,horaFinBloque)
																	VALUES(".$idUsuario.",'".$fecha."','".date("H:i:s",$hEntradaFaltaRetardo)."','".date("H:i:s",$evS[3])."',1,'".convertirSegundosTiempo($horaCero)."',0,'".convertirSegundosTiempo($horaCero)."','".
																	convertirSegundosTiempo($horaCero)."',".$hEncontrado[1].",17,'".date("H:i:s",$hEntradaFaltaRetardo)."',3,'".date("H:i:s",strtotime($hEncontrado[4]))."')";
																	
														$x++;
														$hSalidaPago=$hEntradaFaltaRetardo;
														if(isset($arrRecesos[$hEncontrado[7]]))
														{
															foreach($arrRecesos[$hEncontrado[7]] as $horaInicio=>$horaFin)
															{
																if($horaFin==date("H:i:s",$hSalidaPago))
																{
																	$hSalidaPago=strtotime($horaInicio);
																	break;
																}
															}
														}
														
														
														
														$hSalida=$hSalidaPago;
													}
													$resEvento=18;
													$arrTipoIncidencias["".$resEvento]=1;
													$query[$x]="INSERT INTO 9105_controlAsistencia(idUsuario,fecha,hora_entrada,hora_salida,tipo,tRetardo,esRetardo,tAnticipado,tExtra,idGrupo,tipoEvento,horaInicioBloque,valorEvento,horaFinBloque)
																	VALUES(".$idUsuario.",'".$fecha."','".date("H:i:s",$hEntrada)."','".date("H:i:s",$hSalida)."',".$eventoCerrado.",'".convertirSegundosTiempo($tRetardo)."',".$esRetardo.",'".convertirSegundosTiempo($tAnticipado)."','".
																	convertirSegundosTiempo($tExtra)."',".$hEncontrado[1].",".$resEvento.",'".date("H:i:s",$hEntradaPago)."',".$arrTipoIncidencias["".$resEvento].",'".date("H:i:s",$hSalidaPago)."')";
													$x++;
													
												}
												
											}
										break;
									}
									
									
									$query[$x]="set @idRegistro:=(select last_insert_id())";
									$x++;
									$idReferencia="@idRegistro";
									array_splice($arrHorario,$posHorario,1);	
									
								}
								$evS[6]=$resultado;
								$evS[7]=$idReferencia;
								array_push($arrEv,$evS);
							}

							registrarAccionesEvento($arrEv,$query,$x,$horaMarca);
							if($posSalida>0)
								$pos=($posSalida);
							
						}
					}
				}
				else
				{

					$resultado=6; //No se encontró grupo
					$ev[6]=$resultado;
					$ev[7]="-1";
					array_push($arrEv,$ev);
					registrarAccionesEvento($arrEv,$query,$x,$horaMarca);
				}
				
			}
			
			foreach($arrHorario as $horario)
			{
				$ultimoEv=obtenerUltimoEvento($arrFilas,$horario[7]);
				if($ultimoEv[3]>$horario[9]) 
				{
					$consulta="select * from 4559_controlDeFalta where idGrupo=".$horario[1]." and fechaFalta='".$fecha."' and horaInicial='".$horario[3]."'";
					$fFalta=$con->obtenerPrimeraFila($consulta);
					if(!$fFalta)
					{
						$query[$x]="INSERT INTO 4559_controlDeFalta(idGrupo,idUsuario,horaInicial,horaFinal,justificado,fechaFalta)
									VALUES(".$horario[1].",".$idUsuario.",'".$horario[3]."','".$horario[4]."',0,'".$fecha."')";
						$x++;
					}
				}
			}
			$query[$x]="delete from 9105_eventosRecibidos where idUsuario=".$idUsuario." and fechaEvento='".$fecha."'  ".$compEvt;
			$x++;
			$query[$x]="commit";
			$x++;

			if(!$con->ejecutarBloque($query))
				return false;
		}
		return true;
	}	
	
	function obtenerUltimoEvento($arrEventos,$plantel)
	{
			
		$nEventos=sizeof($arrEventos);
		$ultimoEvento=null;
		for($x=0;$x<$nEventos;$x++)
		{
			if($arrEventos[$x][5]==$plantel)
				$ultimoEvento=$arrEventos[$x];
		}
		return $ultimoEvento;
	}
		
	function obtenerNumeroHorasBloque($idGrupo,$horaInicio,$horaFin,$plantel,$arrRecesos,$formato=1,$debug=false) //1=Horas 2=Minutos
	{
		global $con;
		$consulta="SELECT idInstanciaPlanEstudio FROM 4520_grupos WHERE idGrupos=".$idGrupo;
		$idInstanciaPlanEstudio=$con->obtenerValor($consulta);
		$minutos=(strtotime($horaFin)-strtotime($horaInicio))/60;
		$nRegSesion=sizeof($arrRecesos);
		$minutosDescuento=0;
		$arrRecesosConsiderados=array();
		if(sizeof($arrRecesos)>0)
		{

			foreach($arrRecesos as $fila)
			{
				
				
				if(($fila["plantel"]==$plantel)&&(existeValor($fila["instanciasPlan"],$idInstanciaPlanEstudio)))
				{
					
					foreach($fila["horario"] as $hInicio=>$hFin)
					{
						
						if(colisionaTiempo($horaInicio,$horaFin,$hInicio,$hFin))
						{
							if(!isset($arrRecesosConsiderados[$hInicio]))	
							{
								$minutosDescuento+=(strtotime($hFin)-strtotime($hInicio))/60;
								$arrRecesosConsiderados[$hInicio]=1;
							}
						}
					}
					$minutos-=$minutosDescuento;
				}
			}
		}
		
		$duracionHora=1;
		if($formato==1)
		{
			
			
	
			$duracionHora=obtenenerDuracionHoraGrupo($idGrupo);
			if($duracionHora=="")
				$duracionHora=60;
		}
		return $minutos/$duracionHora;
	}
	
	function obtenenerDuracionHoraGrupo($idGrupo)
	{
		global $con;
		$consulta="SELECT idInstanciaPlanEstudio FROM 4520_grupos WHERE idGrupos=".$idGrupo;
		$idInstanciaPlanEstudio=$con->obtenerValor($consulta);
		$filaConf=obtenerConfiguracionPlanEstudio(472,"",$idInstanciaPlanEstudio);
		$duracionHora=60;
		if($filaConf)
			$duracionHora=$filaConf[10];
			
		return $duracionHora;
	}	
	
	function convertirHoraMinutos($hora)
	{
		$minutos=(date("H",$hora)*60)+date("i",$hora);
		if($minutos==0)
		{
			if((date("s",$hora)*1)>0)
				$minutos++;
		}
		return $minutos;
	}
	
	function vH($hora)
	{
		return date("H:i",$hora);
	}	
	
	function vHA($arr)
	{
		foreach($arr as $e)
		{
			echo date("H:i:s",$e[3])."<br>";
		}
	}
	
	function cambiarResultadoEvento($idEvento,$nValorEvento,&$arrEventos)
	{
		for($x=0;$x<sizeof($arrEventos);$x++)
		{
			$ev=$arrEventos[$x];
			
			if($ev[0]==$idEvento)
			{
				$arrEventos[$x][6]=$nValorEvento;

				$return;
			}
				
		}
	}
	
	function obtenerSalida($listaEventos,$arrClases,$evInicial,$posClaseBusqueda,$nToleranciaAntesSalida,$nToleranciaEntrada,$validarPlantel,$tipoDefinicion,$arrPlantelesBiometrico)//1 detectar maximo control de asistencia; 2 Preferencia cierre eventos completos
	{
		//
			
		$nListaEventos=sizeof($listaEventos);
		if($nListaEventos<=$evInicial)
			return -1;
		$evActual=$listaEventos[$evInicial];
		$claseB=$arrClases[$posClaseBusqueda];
		$hFinalClase=$claseB[9];
		$hToleranciaAntesSalida=strtotime("-".$nToleranciaAntesSalida." minutes",strtotime($claseB[4]));
		$posEnc=-2;
		$x=0;
		$existeEvento=false;
		for($x=$evInicial;$x<$nListaEventos;$x++)
		{	
			$existeEvento=true;
			$ev=$listaEventos[$x];
			$finalizar=false;
			if($validarPlantel)
			{
				if(!isset($arrPlantelesBiometrico[$ev[5]][$claseB[7]]))
				{
					$finalizar=true;
				}
			}
			if(($ev[3]>=$hToleranciaAntesSalida)&&($ev[3]<=$claseB[9])&&(!$finalizar))
			{
				//echo date("H:i",$ev[3]).">=". date("H:i",$hToleranciaAntesSalida)." and ". date("H:i",$ev[3])."<=". date("H:i",$claseB[9])."<br>";
				
				$posEnc=$x;
				if($validarPlantel)
				{
					if(!isset($arrPlantelesBiometrico[$ev[5]][$claseB[7]]))
						$posEnc=-2;
				}
			}
			else
			{
				
				if(($ev[3]>$claseB[9])||($finalizar))
				{
					$x--;
					if($x<$evInicial)
						return -2;
					$ev=$listaEventos[$x];
					
					$posEnc=$x;
					
					if(($x+1)<sizeof($listaEventos))
					{
						
						if(($posClaseBusqueda+1)< sizeof($arrClases))
						{
							$claseSig=$arrClases[$posClaseBusqueda+1];
							$toleranciaEntradaCS=strtotime("+".$nToleranciaEntrada." minutes",strtotime($claseSig[3]));
							/*if($posEnc==-2)
								return 0;*/
							$evtSig=$listaEventos[($posEnc+1)];
							
							
							
							if(($evtSig[3]>=$claseSig[8])&&($evtSig[3]<=$toleranciaEntradaCS))	
							{

								return $x;
							}
							else
							{
								
								switch($tipoDefinicion)
								{
									case 1:
									
										if(($ev[3]>=$claseSig[8])&&($ev[3]<=$toleranciaEntradaCS))	
											return $x-1;
										else
											return $x;
									break;
									case 2:
										if(($ev[3]>=$claseSig[8])&&($ev[3]<=$toleranciaEntradaCS))	
										{
											
											if(($x-1)>=$evInicial)
											{

												$evtAnt=$listaEventos[($x-1)];

												if(estaEnLimiteChecaSalida($claseB,$nToleranciaAntesSalida,$evtAnt))
													return $x-1;
												else
													return $x;
											}
											else
											{
												
												$evAux=$listaEventos[$evInicial-1];
												
												
												
												if(!estaEnLimiteChecaEntrada($claseB,$nToleranciaEntrada,$evAux,false))
												{
													
													return -2;
												}
												/*$minhora_salida=strtotime("-".$nToleranciaAntesSalida." minutes",strtotime($claseB[4]));
												$horaEntrada=strtotime("+".($nToleranciaEntrada)." minutes",strtotime($claseB[3]));
												$diferenciaConEntrada=0;
												$evAux=$listaEventos[$evInicial-1];
												
												if($evAux[3]>$horaEntrada)
													$diferenciaConEntrada=$evAux[3]-$horaEntrada;
												else
													$diferenciaConEntrada=$horaEntrada-$evAux[3];
													
												$diferenciaConSalida=0;
												if($evAux[3]>$minhora_salida)	
													$diferenciaConSalida=$evAux[3]-$minhora_salida;
												else
													$diferenciaConSalida=$minhora_salida-$evAux[3];
												if($diferenciaConEntrada<$diferenciaConSalida)
												{
														
												}*/
												
												
												//return -2;
												return $x; //orden jorge
											}
										}
										else
											return $x;
										
										
										
									break;
								}
							}
						}
						else
							return $x;
					}
					else
					{
						return $x;
					}
				}
			}
		}

		if($posEnc>0)
		{
			$evSalida=$listaEventos[$posEnc];
			
			if(($posClaseBusqueda+1)< sizeof($arrClases))
			{
				$claseSig=$arrClases[$posClaseBusqueda+1];
				$toleranciaEntradaCS=strtotime("+".$nToleranciaEntrada." minutes",strtotime($claseSig[3]));
				if(($evSalida[3]>=$claseSig[8])&&($evSalida[3]<=$toleranciaEntradaCS))
				{
					if($posEnc==$evInicial)
					{
						return $posEnc;
					}
					else
					{
						$evAnt=$listaEventos[$posEnc-1];
						if(($evAnt[3]>=$hToleranciaAntesSalida)&&($evAnt[3]<=$claseB[9]))
						{
							return $posEnc-1;
						}
						else
						{
							if(($posEnc+1)<sizeof($listaEventos))
							{
								$evtSig=$listaEventos[($posEnc+1)];
								if(($evtSig[3]>=$claseSig[8])&&($evtSig[3]<=$toleranciaEntradaCS))	
								{
									return $posEnc;
								}
								else
								{
									return $posEnc-1;
								}
							}
							else
							{
								return $posEnc;
							}
						}
						
					}
					
				}
				else
				{
					return $posEnc;
				}
			}
			else
			{
				return $posEnc;				
			}
		}
		else
		{
			
			if($existeEvento)
			{
				$posEnc=($x-1);
				$ev=$listaEventos[$posEnc];
				if($validarPlantel)
				{
					if(!isset($arrPlantelesBiometrico[$ev[5]][$claseB[7]]))
						$posEnc=-2;
				}
			}
		}
		return $posEnc;
		
	}
		
	function registrarAccionesEvento($arrEventos,&$arrQuery,&$pos,$horaMarca)
	{

		foreach($arrEventos as $ev)
		{
			if($ev[6]=="")
			{
				if(!isset($ev[10]))	
					$ev[6]=10;
				else
					$ev[6]=12;
			}
			
			if($ev[7]=="")
				$ev[7]=-1;
			$arrQuery[$pos]="INSERT INTO 9105_eventosControlAsistencia (fechaEvento,horaEvento,idUsuario,accionEvento,noTerminal,plantel,idReferencia,marcaSincronizacion,marcaProcesamiento) 
							VALUES('".$ev[2]."','".date("H:i:s",$ev[3])."',".$ev[1].",".$ev[6].",".$ev[4].",'".$ev[5]."',".$ev[7].",'".$ev[9]."','".$horaMarca."')";
			$pos++;
		}
	}		
	
	function verificarCierreEventos($fechaInicio,$fechaFin,$plantel)
	{
		global $con;

		$arrPlantelesBiometrico=array();
		$arrRecesos=obtenerArregloRecesos();
		$consulta="SELECT id__494_tablaDinamica,cmbPlanteles FROM _494_tablaDinamica ";
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$arrPlantelesBiometrico[$fila[1]]=array();
			$arrPlantelesBiometrico[$fila[1]][$fila[1]]=1;
			$consulta="SELECT plantelCompartido FROM _494_gridSedeCompartida WHERE idReferencia=".$fila[0];
			$resC=$con->obtenerFilas($consulta);
			while($fPlantel=mysql_fetch_row($resC))
			{
				$arrPlantelesBiometrico[$fila[1]][$fPlantel[0]]=1;
			}
		}
		
		$plantelBase="";
		$listPlanteles="";
		foreach($arrPlantelesBiometrico as $plantelIndice=>$resto)
		{
			$plantelBase=$plantelIndice;
			if(isset($resto[$plantel]))
				break;
		}
		
		if($plantelBase!="")
		{
			foreach($arrPlantelesBiometrico[$plantelBase] as $pAux=>$resto)
			{
				if($listPlanteles=="")
					$listPlanteles="'".$pAux."'";
				else
					$listPlanteles.=",'".$pAux."'";
			}
		}
		
		$arrRecesos=array();
		$arrRecesos=obtenerArregloRecesos();
		$fInicio=strtotime($fechaInicio);
		$fFin=strtotime($fechaFin);
		$x=0;
		$query[$x]="begin";
		$x++;
		
		while($fInicio<=$fFin)
		{
			$fechaActual=date("Y-m-d",$fInicio);
			
			if(!esDiaInhabilEscolar($plantel,$fechaActual))
			{
				$arrGrupos=array();
				$dia=date("w",$fInicio);
				$consulta="select idGrupos from 4520_grupos g where Plantel in (".$listPlanteles.") AND '".$fechaActual."'>=g.fechaInicio AND '".$fechaActual."'<=g.fechaFin";
				
				$listGrupos=$con->obtenerListaValores($consulta);
				if($listGrupos=="")
					$listGrupos=-1;
				$consulta="(SELECT idHorarioGrupo,idGrupo,dia,horaInicio,horaFin,idAula  FROM 4522_horarioGrupo WHERE idGrupo IN (".$listGrupos.") AND dia=".$dia." and '".$fechaActual."'>=fechaInicio and '".$fechaActual."'<=fechaFin) union
					(SELECT (idSesionReposicion*-1),idGrupo,0,horaInicio,horaFin,idAula FROM 4563_sesionesReposicion 
					WHERE fechaReposicion='".$fechaActual."' AND idGrupo IN (".$listGrupos.")) ORDER BY idGrupo,horaInicio";
				
				
				$res=$con->obtenerFilas($consulta);
				$arrHorario=array();
				while($fila=mysql_fetch_row($res))
				{
					array_push($arrHorario,$fila);
					
				}
				
				$nArrHorario=array();
				$nAux=0;
	
				for($nHorario=0;$nHorario<sizeof($arrHorario);$nHorario++)
				{
					$h=$arrHorario[$nHorario];
					$pos=obtenerHoraFinBloque($arrHorario,$h,($nHorario+1),$arrRecesos,$plantel);
					if($pos!=-1)
					{
						$nHorario=$pos;
						$h[4]=$arrHorario[$pos][4];

					}
					array_push($nArrHorario,$h);	
					
				}

				$arrHorario=$nArrHorario;

				foreach($arrHorario as $resto)
				{
					$consulta="select valorEvento,idAsistencia from 9105_controlAsistencia WHERE idGrupo=".$resto[1]." AND fecha='".$fechaActual."' AND horaInicioBloque='".$resto[3]."'";
					$fDatos=$con->obtenerPrimeraFila($consulta);
					$nReg=$fDatos[0];
					if($nReg!="")
					{
						if($nReg!=1)
						{
							

							$consulta="SELECT idUsuario FROM 4519_asignacionProfesorGrupo WHERE idGrupo=".$resto[1]." AND participacionPrincipal=0 AND '".$fechaActual."'>=fechaAsignacion AND '".$fechaActual."'<=fechaBaja";	
							$idUsuario=$con->obtenerValor($consulta);
							if($idUsuario=="")
							{
								$consulta="SELECT idUsuario FROM 4519_asignacionProfesorGrupo WHERE idGrupo=".$resto[1]." AND participacionPrincipal=1 AND '".$fechaActual."'>=fechaAsignacion and '".$fechaActual."'<=fechaBaja and fechaBaja is not null";	
								$idUsuario=$con->obtenerValor($consulta);
								if($idUsuario=="")
								{
									$consulta="SELECT idUsuario FROM 4519_asignacionProfesorGrupo WHERE idGrupo=".$resto[1]." AND participacionPrincipal=1 AND '".$fechaActual."'>=fechaAsignacion and fechaBaja is null";	
									$idUsuario=$con->obtenerValor($consulta);
									if($idUsuario=="")
									{
										$idUsuario=-1;
									}
									
								}
								
							}
							
							if($idUsuario<>-1)
							{
								$justificado=0;
								$estadoFalta=0;
								$consulta="SELECT id__489_tablaDinamica,tipoComision FROM _489_tablaDinamica WHERE  '".$fechaActual."'>=dteFechaInicial AND '".$fechaActual."'<=dteFechaFinal AND cmbDocentes=".$idUsuario." and idEstado=2";
								$res=$con->obtenerFilas($consulta);
								$tComision="0";
								
								$esComision=false;
								while($fila=mysql_fetch_row($res))
								{
									$consulta="SELECT idRegistroModuloSesionesClase FROM 4560_registroModuloSesionesClase WHERE  idFormulario=489 AND idReferencia=".$fila[0];
									$idReferenciaComision=$con->obtenerValor($consulta);
									$consulta="SELECT idGrupo,horaInicioBloque,horaFinBloque FROM 4561_sesionesClaseModulo WHERE idReferencia=".$idReferenciaComision." and idGrupo=".$resto[1]." and horaInicioBloque='".$resto[3]."'";
									$filaComision=$con->obtenerPrimeraFila($consulta);
									if($filaComision)
									{
										$tComision=$filaComision[1];
										$esComision=true;
										break;
									}
								}
							
								if(!$esComision)
								{
									$consulta="select * from 4559_controlDeFalta where idGrupo=".$resto[1]." and fechaFalta='".$fechaActual."' and horaInicial='".$resto[3]."'";
									$fFalta=$con->obtenerPrimeraFila($consulta);
									if(!$fFalta)
									{
										$query[$x]="INSERT INTO 4559_controlDeFalta(idGrupo,idUsuario,fechaFalta,horaInicial,horaFinal,justificado,estadoFalta,idRegistroControlAsistencia) VALUES(".$resto[1].",".$idUsuario.",'".$fechaActual."','".$resto[3]."','".$resto[4]."',0,0,".$fDatos[1].")";
										$x++;
									}
								}
								else
								{
									registrarComision($resto[1],$fechaActual,$resto[3],$resto[4],$idUsuario,$plantel,$tComision);
								}
							}
						}
					}
					else
					{
						$consulta="SELECT idUsuario FROM 4519_asignacionProfesorGrupo WHERE idGrupo=".$resto[1]." AND participacionPrincipal=0 AND '".$fechaActual."'>=fechaAsignacion AND '".$fechaActual."'<=fechaBaja";	
	
						$idUsuario=$con->obtenerValor($consulta);
						if($idUsuario=="")
						{
							$consulta="SELECT idUsuario FROM 4519_asignacionProfesorGrupo WHERE idGrupo=".$resto[1]." AND participacionPrincipal=1 AND '".$fechaActual."'>=fechaAsignacion and '".$fechaActual."'<=fechaBaja and fechaBaja is not null";	
							$idUsuario=$con->obtenerValor($consulta);
							

							if($idUsuario=="")
							{
								$consulta="SELECT idUsuario FROM 4519_asignacionProfesorGrupo WHERE idGrupo=".$resto[1]." AND participacionPrincipal=1 AND '".$fechaActual."'>=fechaAsignacion and fechaBaja is null";	
								$idUsuario=$con->obtenerValor($consulta);
								if($idUsuario=="")
								{
									$idUsuario=-1;
								}
								
							}
							
						}
						
						if($idUsuario<>-1)
						{
							$justificado=0;
							$estadoFalta=0;
							$consulta="SELECT id__489_tablaDinamica,tipoComision FROM _489_tablaDinamica WHERE  '".$fechaActual."'>=dteFechaInicial AND '".$fechaActual."'<=dteFechaFinal AND cmbDocentes=".$idUsuario." and idEstado=2";
							$res=$con->obtenerFilas($consulta);
							
							$tComision="0";
							
							$esComision=false;
							while($fila=mysql_fetch_row($res))
							{
								$consulta="SELECT idRegistroModuloSesionesClase FROM 4560_registroModuloSesionesClase WHERE  idFormulario=489 AND idReferencia=".$fila[0];
								$idReferenciaComision=$con->obtenerValor($consulta);
								
								$consulta="SELECT idGrupo,horaInicioBloque,horaFinBloque FROM 4561_sesionesClaseModulo WHERE idReferencia=".$idReferenciaComision." and idGrupo=".$resto[1]." and horaInicioBloque='".$resto[3]."'";
								$filaComision=$con->obtenerPrimeraFila($consulta);
								
								if($filaComision)
								{
									$tComision=$filaComision[1];
									$esComision=true;
									break;
								}
							}
							if(!$esComision)
							{
								
								$consulta="select * from 4559_controlDeFalta where idGrupo=".$resto[1]." and fechaFalta='".$fechaActual."' and horaInicial='".$resto[3]."'";
								$fFalta=$con->obtenerPrimeraFila($consulta);
								if(!$fFalta)
								{
									$query[$x]="INSERT INTO 4559_controlDeFalta(idGrupo,idUsuario,fechaFalta,horaInicial,horaFinal,justificado,estadoFalta) VALUES(".$resto[1].",".$idUsuario.",'".$fechaActual."','".$resto[3]."','".$resto[4]."',0,0)";
									$x++;
								}
							}
							else
							{
								
								registrarComision($resto[1],$fechaActual,$resto[3],$resto[4],$idUsuario,$plantel,$tComision);
							}
						}
					}
				}
			}
			else
			{
				verificarDiasHabiles($fechaActual,$fechaActual,$plantel);
			}
			$fInicio=strtotime("+1 days",$fInicio);
		}
		
		
		
		$query[$x]="commit";
		$x++;
		return $con->ejecutarBloque($query);
	}
	
	function verificarDiasHabiles($fechaInicio,$fechaFin,$plantel)
	{
		global $con;
		$arrRecesos=array();
		$arrRecesos=obtenerArregloRecesos();
		$fInicio=strtotime($fechaInicio);
		$fFin=strtotime($fechaFin);
		$x=0;
		$query[$x]="begin";
		$x++;
		
		while($fInicio<=$fFin)
		{
			$fechaActual=date("Y-m-d",$fInicio);
			
			if(esDiaInhabilEscolar($plantel,$fechaActual))
			{
				$arrGrupos=array();
				$dia=date("w",$fInicio);
				$consulta="select idGrupos from 4520_grupos g where Plantel='".$plantel."' AND '".$fechaActual."'>=g.fechaInicio AND '".$fechaActual."'<=g.fechaFin";
				$listGrupos=$con->obtenerListaValores($consulta);
				if($listGrupos=="")
					$listGrupos=-1;
					
				$query[$x]="DELETE FROM 4559_controlDeFalta WHERE idGrupo IN (".$listGrupos.") AND fechaFalta='".$fechaActual."'";
				$x++;
				$query[$x]="DELETE FROM 9105_controlAsistencia WHERE idGrupo IN (".$listGrupos.") AND fecha='".$fechaActual."'";
				$x++;
				$consulta="(SELECT idHorarioGrupo,idGrupo,dia,horaInicio,horaFin,idAula  FROM 4522_horarioGrupo WHERE idGrupo IN (".$listGrupos.") AND dia=".$dia." and '".$fechaActual."'>=fechaInicio and '".$fechaActual."'<=fechaFin) union
					(SELECT (idSesionReposicion*-1),idGrupo,0,horaInicio,horaFin,idAula FROM 4563_sesionesReposicion 
					WHERE fechaReposicion='".$fechaActual."' AND idGrupo IN (".$listGrupos.")) ORDER BY idGrupo,horaInicio";
				$res=$con->obtenerFilas($consulta);
				$arrHorario=array();
				while($fila=mysql_fetch_row($res))
				{
					array_push($arrHorario,$fila);
					
				}
				
				$nArrHorario=array();
				$nAux=0;
	
				for($nHorario=0;$nHorario<sizeof($arrHorario);$nHorario++)
				{
					$h=$arrHorario[$nHorario];
					$pos=obtenerHoraFinBloque($arrHorario,$h,($nHorario+1),$arrRecesos,$plantel);
					if($pos!=-1)
					{
						$nHorario=$pos;
						$h[4]=$arrHorario[$pos][4];

					}
					array_push($nArrHorario,$h);	
					
				}
				$arrHorario=$nArrHorario;
				foreach($arrHorario as $resto)
				{
					$consulta="SELECT idUsuario FROM 4519_asignacionProfesorGrupo WHERE idGrupo=".$resto[1]." AND participacionPrincipal=0 AND '".$fechaActual."'>=fechaAsignacion AND '".$fechaActual."'<=fechaBaja";	
					$idUsuario=$con->obtenerValor($consulta);
					if($idUsuario=="")
					{
						$consulta="SELECT idUsuario FROM 4519_asignacionProfesorGrupo WHERE idGrupo=".$resto[1]." AND participacionPrincipal=1 AND '".$fechaActual."'>=fechaAsignacion and '".$fechaActual."'<=fechaBaja and fechaBaja is not null";	
						$idUsuario=$con->obtenerValor($consulta);
						
	
						if($idUsuario=="")
						{
							$consulta="SELECT idUsuario FROM 4519_asignacionProfesorGrupo WHERE idGrupo=".$resto[1]." AND participacionPrincipal=1 AND '".$fechaActual."'>=fechaAsignacion and fechaBaja is null";	
							$idUsuario=$con->obtenerValor($consulta);
							if($idUsuario=="")
							{
								$idUsuario=-1;
							}
						}
					}
					if($idUsuario<>-1)
					{
						$consulta="select * from 9105_controlAsistenciaDiaFestivo where idGrupo=".$resto[1]." and fecha='".$fechaActual."' and horaInicioBloque='".$resto[3]."'";
						$fReg=$con->obtenerPrimeraFila($consulta);
						if(!$fReg)
						{
							$query[$x]="INSERT INTO 9105_controlAsistenciaDiaFestivo(idUsuario,fecha,hora_entrada,hora_salida,tipo,tRetardo,esRetardo,tAnticipado,
										aut_TAnticipado,tExtra,aut_tExtra,idGrupo,tipoEvento,horaInicioBloque,valorEvento,horaFinBloque) VALUES
										(".$idUsuario.",'".$fechaActual."','00:00:00','00:00:00',1,'00:00:00',0,'00:00:00',0,'00:00:00',0,".$resto[1].",19,'".$resto[3]."',1,'".$resto[4]."')";
							$x++;
						}
						
					}
					
				}
			}
			$fInicio=strtotime("+1 days",$fInicio);
		}
		$query[$x]="commit";
		$x++;
		return $con->ejecutarBloque($query);
	}
	
	function registrarComision($idGrupo,$fechaComision,$horaInicial,$horaFinal,$idUsuario,$plantel,$tipoComision,$idRegistroComision)
	{
		global $con;
		$formaReposicion='2';
		$tComision='Comisión sin grupo';
		if($tipoComision=='2')
		{
			$formaReposicion='1';
			$tComision='Comisión con grupo';
		}
		$ConsultaFalta="SELECT idFalta,estadoFalta FROM 4559_controlDeFalta WHERE idGrupo='".$idGrupo."' and fechaFalta='".$fechaComision."' AND horaInicial='".$horaInicial."' AND idUsuario='".$idUsuario."'";
		
		$faltaN=$con->obtenerPrimeraFila($ConsultaFalta);
		
		$x=0;
		$consulta[$x]="begin";
		$x++;
		if($faltaN)
		{
			if($faltaN[1]!=2)
			{
				$consulta[$x]="UPDATE 4559_controlDeFalta SET estadoFalta='2' WHERE idFalta='".$faltaN[0]."'";
				$x++;
				$consulta[$x]="INSERT INTO _481_tablaDinamica(fechaCreacion,responsable,idEstado,codigoUnidad,codigoInstitucion,txtJustificacion,
								cmbDocentes,cmbFalta,cmbFormaReposicion,cmbTipoJutificacion,idSolicitudComision)VALUES('".date("Y-m-d")."','1','5','".$plantel."',
								'".$plantel."','".$tComision."','".$idUsuario."','".$faltaN[0]."','".$formaReposicion."','3',".$idRegistroComision.")";
				$x++;
				$consulta[$x]="set @idJustificacion=(select last_insert_id())";
				$x++;
				$consulta[$x]="UPDATE _481_tablaDinamica SET codigo=@idJustificacion WHERE id__481_tablaDinamica=@idJustificacion";
				$x++;
				$consulta[$x]="UPDATE 4559_controlDeFalta SET idFormulario=481,idRegistroJustificacion=@idJustificacion WHERE idFalta='".$faltaN[0]."'";
				$x++;
			}
		}
		else
		{
			
			$consulta[$x]="INSERT INTO 4559_controlDeFalta(idGrupo,idUsuario,fechaFalta,horaInicial,horaFinal,justificado,idFormulario,estadoFalta)VALUES('".$idGrupo."',
			'".$idUsuario."','".$fechaComision."','".$horaInicial."','".$horaFinal."','0',481,'2')";
			$x++;
			$consulta[$x]="set @idFalta=(select last_insert_id())";
			$x++;
			$consulta[$x]="INSERT INTO _481_tablaDinamica(fechaCreacion,responsable,idEstado,codigoUnidad,codigoInstitucion,txtJustificacion,
							cmbDocentes,cmbFalta,cmbFormaReposicion,cmbTipoJutificacion,idSolicitudComision)VALUES('".date("Y-m-d")."','1','5','".$plantel."',
							'".$plantel."','".$tComision."','".$idUsuario."',@idFalta,'".$formaReposicion."','3',".$idRegistroComision.")";
			$x++;
			$consulta[$x]="set @idJustificacion=(select last_insert_id())";
			$x++;
			$consulta[$x]="UPDATE _481_tablaDinamica SET codigo=@idJustificacion WHERE id__481_tablaDinamica=@idJustificacion";
			$x++;
			$consulta[$x]="UPDATE 4559_controlDeFalta SET idRegistroJustificacion=@idJustificacion WHERE idFalta=@idFalta";
			$x++;
			
			
		}
		$consulta[$x]="delete from 9105_controlAsistencia WHERE idGrupo=".$idGrupo." AND fecha='".$fechaComision."' AND horaInicioBloque='".$horaInicial."'";
		$x++;
		$consulta[$x]="commit";
		$x++;
		$con->ejecutarBloque($consulta);
	}
	
	function ejecutarCierreEventos($fechaInicio,$fechaFin)
	{
		global $con;
		$consulta="SELECT codigoUnidad FROM 817_organigrama WHERE institucion=1 and instColaboradora=0";//and codigoUnidad='00010004'
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			if(!verificarCierreEventos($fechaInicio,$fechaFin,$fila[0]))
				return false;
		}
		return true;
		
	}
	
	function ajustarSesiones($idGrupo,$fechaAplicacion,$arrDias,&$consulta,&$x,$ejecutar=false,$reiniciarSesiones=false)
	{
		global $con;
		if(gettype($fechaAplicacion)=="string")
			$fechaAplicacion=strtotime($fechaAplicacion);
		
		
		
		
		
		$ultimoHorario="";
		$fechaUltimaSesion="";
		$generarSesionPorDia=true;
		$query="SELECT fechaFin,Plantel FROM 4520_grupos WHERE idGrupos=".$idGrupo;
		$fGpo=$con->obtenerPrimeraFila($query);
		$fechaFin=strtotime($fGpo[0]);
		$plantel=$fGpo[1];
		$arrDiasSesion=array();
		if($ejecutar)
		{
			$x=0;
			$consulta[$x]="begin";
			$x++;
		}
		if($arrDias==NULL)
		{
			$query="SELECT dia,horaInicio,horaFin,fechaInicio,fechaFin FROM 4522_horarioGrupo WHERE idGrupo= ".$idGrupo." and fechaInicio<=fechaFin order by fechaInicio,horaInicio";
			$res=$con->obtenerFilas($query);
			while($f=mysql_fetch_row($res))
			{
				$hInicio=strtotime($f[1]);
				$hFin=strtotime($f[2]);
				if(!isset($arrDiasSesion[$f[0]]))
				{
					$arrDiasSesion[$f[0]]=array();
					$obj[0]=date("H:i:s",$hInicio)." - ".date("H:i:s",$hFin);
					$obj[1]=strtotime($f[3]);
					$obj[2]=strtotime($f[4]);
					array_push($arrDiasSesion[$f[0]],$obj);
				}
				else
				{
					if($generarSesionPorDia)
					{
						$pos=0;
						for($pos=0;$pos<sizeof($arrDiasSesion[$f[0]]);$pos++)
						{
							//echo $arrDiasSesion[$f[0]][$pos][1]."==".strtotime($f[3])." && ".$arrDiasSesion[$f[0]][$pos][2]."==".strtotime($f[4])."<br>";
							if(($arrDiasSesion[$f[0]][$pos][1]==strtotime($f[3]))&&($arrDiasSesion[$f[0]][$pos][2]==strtotime($f[4])))
							{
								break;
							}
						}
						if($pos<sizeof($arrDiasSesion[$f[0]]))
							$arrDiasSesion[$f[0]][$pos][0].=", ".date("H:i:s",$hInicio)." - ".date("H:i:s",$hFin);
						else
						{
							$obj[0]=date("H:i:s",$hInicio)." - ".date("H:i:s",$hFin);
							$obj[1]=strtotime($f[3]);
							$obj[2]=strtotime($f[4]);
							array_push($arrDiasSesion[$f[0]],$obj);
						}
					}
					else
					{
						$obj[0]=date("H:i:s",$hInicio)." - ".date("H:i:s",$hFin);
						$obj[1]=strtotime($f[3]);
						$obj[2]=strtotime($f[4]);
						array_push($arrDiasSesion[$f[0]],$obj);
					}
					
				}
			}
		}
		else
		{
			$arrDiasSesion=$arrDias;
		}

		if(!$reiniciarSesiones)
		{
			$query="SELECT noSesion,fechaSesion FROM 4530_sesiones WHERE idGrupo=".$idGrupo." AND fechaSesion<'".date("Y-m-d",$fechaAplicacion)."' 
					and tipoSesion<>15 ORDER BY fechaSesion DESC LIMIT 0,1";
		}
		else
		{
			$query="SELECT noSesion,fechaSesion FROM 4530_sesiones WHERE idGrupo=".$idGrupo."  ORDER BY fechaSesion asc LIMIT 0,1";
		}
		
		$filaSesion=$con->obtenerPrimeraFila($query);
		$noSesion=1;
		$fechaSesion=$fechaAplicacion;
		if(($filaSesion)&&(!$reiniciarSesiones))
		{
			$noSesion=($filaSesion[0]+1);
			$fechaSesion=$filaSesion[1];
		}
		
		$ingresado=false;
		
		
		
	
		while($fechaAplicacion<=$fechaFin)
		{
			
			if((isset($arrDiasSesion[date("w",$fechaAplicacion)]))&&(!esDiaInhabilEscolar($plantel,date("Y-m-d",$fechaAplicacion))))
			{
				$arrHorario=$arrDiasSesion[date("w",$fechaAplicacion)];
				foreach($arrHorario as $h)	
				{
					if(($fechaAplicacion>=$h[1])&&($fechaAplicacion<=$h[2]))
					{
						$query="select idSesion from 4530_sesiones  WHERE idGrupo=".$idGrupo." AND noSesion=".$noSesion;
						$idSesion=$con->obtenerValor($query);
						$fechaUltimaSesion=date("Y-m-d",$fechaAplicacion);
						$ultimoHorario=$h[0];
						if($idSesion!="")
						{
							$consulta[$x]="UPDATE 4530_sesiones SET fechaSesion='".date("Y-m-d",$fechaAplicacion)."',horario='".$h[0]."' WHERE idSesion=".$idSesion;
							$x++;
						}
						else
						{
							$consulta[$x]="INSERT INTO 4530_sesiones(noSesion,fechaSesion,horario,tipoSesion,situacionAsistencia,idAlumno,idGrupo,comentarios) 
									values(".$noSesion.",'".date("Y-m-d",$fechaAplicacion)."','".trim($h[0])."',9,0,NULL,".$idGrupo.",'')";
							$x++;
						}
						$noSesion++;
						$ingresado=true;
					}
					
				}
				
			}
			$fechaAplicacion=strtotime("+1 days",$fechaAplicacion);
			
		}
		
		$noSesion--;
		$consulta[$x]="delete from 4530_sesiones where idGrupo=".$idGrupo." and  ((fechaSesion>'".$fechaUltimaSesion."' or noSesion>".$noSesion.") and tipoSesion<>15) 
						and noSesion not in(SELECT noSesion FROM 4536_temasVSSesion WHERE idGrupo=".$idGrupo.")";
		$x++;
		$consulta[$x]="UPDATE 4530_sesiones SET fechaSesion='".date("Y-m-d",strtotime($fechaUltimaSesion))."',
					horario='".$ultimoHorario."' 
					WHERE idGrupo=".$idGrupo." AND fechaSesion>'".$fechaUltimaSesion."'";
		$x++;
		if($ejecutar)
		{
			$consulta[$x]="commit";
			$x++;
			return $con->ejecutarBloque($consulta);
		}
	}
	
	function obtenerSesionesSuplencia($idUsuario,$fechaInicio,$fechaFin,$plantel="")
	{
		global $con;
		$comp="";
		if($plantel!="")
		{
			$comp="  and g.Plantel='".$plantel."'";
		}
		$arrSesiones=array();
		$consulta="SELECT idGrupo FROM 4519_asignacionProfesorGrupo a,4520_grupos g WHERE idUsuario=".$idUsuario." AND a.situacion=1 AND idParticipacion=45 AND g.idGrupos=a.idGrupo and 
					((fechaAsignacion <='".$fechaInicio."' AND fechaBaja>='".$fechaInicio."') or (fechaAsignacion>='".$fechaInicio."' and fechaAsignacion<='".$fechaInicio."'))".$comp;

		$resGrupos=$con->obtenerFilas($consulta);
		while($f=mysql_fetch_row($resGrupos))
		{
			$consulta="SELECT fechaSesion,horario FROM 4530_sesiones WHERE idGrupo =".$f[0]." and fechaSesion>='".$fechaInicio."' and fechaSesion<='".$fechaFin."' ORDER BY fechaSesion,horario";
			$resFilas=$con->obtenerFilas($consulta);
			$arrHorarioProf=array();
			$arrHorarioProf=array();
			while($fila=mysql_fetch_row($resFilas))
			{
				$dia=$fila[0];
				$dSesion=explode(",",$fila[1]);
				foreach($dSesion as $s)
				{
					$dHorario=explode("-",trim($s));
					if(!isset($arrSesiones[$dia]))
						$arrSesiones[$dia]=array();
					$obj[0]=trim($dHorario[0]);
					$obj[1]=trim($dHorario[1]);
					$obj[2]=trim($f[0]);
					array_push($arrSesiones[$dia],$obj);
				}
			}
			return $arrSesiones;
		}
	}
	///
	function obtenerEventoSiguiente($arrEventos,$pos)
	{
		if(($pos+1)<sizeof($arrEventos))
		{
			return $arrEventos[($pos+1)];
		}
		return null;
	}
	
	function obtenerSiguienteSesion($arrSesiones,$pos)
	{
		if(($pos+1)<sizeof($arrSesiones))
		{
			return $arrSesiones[($pos+1)];
		}
		return null;
	}
	
	function estaEnLimiteChecaEntrada($horario,$toleranciaAsistencia,$evt,$modoDebug=false)
	{
		$horaInicio=$horario[8];	
		$horaFin=strtotime("+".$toleranciaAsistencia." minutes",strtotime($horario[3]));
		if($modoDebug)
			echo vH($evt[3]).">=".date("H:i",$horaInicio)."&&".vH($evt[3])."<=".date("H:i",$horaFin)."";
		if(($evt[3]>=$horaInicio)&&($evt[3]<=$horaFin))
		{
			if($modoDebug)
				echo "true<br>";
			return true;
		}
		else
		{
			if($modoDebug)
				echo "false<br>";
			return false;
		}
	}
	
	function estaEnLimiteRetardo($horario,$toleranciaAsistencia,$toleranciaRetardo,$evt)
	{
		$horaInicio=$horario[8];	
		$horaFin=strtotime("+".($toleranciaAsistencia+$toleranciaRetardo)." minutes",strtotime($horario[3]));
		if(($evt[3]>=$horaInicio)&&($evt[3]<=$horaFin))
			return true;
		else
			return false;
	}
	
	function estaEnLimiteChecaSalida($horario,$toleranciaAntesSalida,$evt)
	{
		$horaInicio=strtotime("-".$toleranciaAntesSalida." minutes",strtotime($horario[4]));
		$horaFin=$horario[9];	
		if(($evt[3]>=$horaInicio)&&($evt[3]<=$horaFin))
			return true;
		else
			return false;
	}
	
	function obtenerHoraFinBloque($arrHorario,$horarioBase,$pos,$arrRecesos,$plantel,$validarBloqueGrupo=true,$validarInstanciaReceso=true)
	{
		global $con;
		$posFin=-1;
		
		if($pos<sizeof($arrHorario))
		{
			for($x=$pos;$x<sizeof($arrHorario);$x++)
			{
				$horaSesion=$arrHorario[$x];
				
				
					
				$consulta="select idInstanciaPlanEstudio from 4520_grupos where idGrupos=".$arrHorario[$x][1];
				$idInstancia=$con->obtenerValor($consulta);
				if(sizeof($arrRecesos)>0)
				{
					
					foreach($arrRecesos as $r)
					{
						
						if($r["plantel"]==$plantel)
						{
							if(existeValor($r["instanciasPlan"],$idInstancia)||(!$validarInstanciaReceso))
							{
								
								if(isset($r["horario"][$horarioBase[4]]))
								{
									if($horarioBase[4]!=$horaSesion[3])
										$horarioBase[4]=$r["horario"][$horarioBase[4]];
									
									break;
								}
							}
						}
					}
				}
				
				if((($horaSesion[1]!=$horarioBase[1])&&($validarBloqueGrupo))||($horarioBase[4]!=$horaSesion[3]))
				{
					
					if($pos==$x)
					{
						
						$posFin=-1;
						break;
					}
					else
					{
						return $posFin;
					}
				}
				else
				{
					$horarioBase[4]=$horaSesion[4];
					$posFin=$x;
				}
			}
		}
		return $posFin;
	}
	
		//	
	function justificarAsistenciaProfesores($fechaInicio,$fechaFin,$plantel)
	{
		global $con;
		$arrRecesos=array();
		$arrRecesos=obtenerArregloRecesos();
		$fInicio=strtotime($fechaInicio);
		$fFin=strtotime($fechaFin);
		$x=0;
		$query[$x]="begin";
		$x++;
		
		while($fInicio<=$fFin)
		{
			$fechaActual=date("Y-m-d",$fInicio);
			
			if(!esDiaInhabilEscolar($plantel,$fechaActual))
			{
				$arrGrupos=array();
				$dia=date("w",$fInicio);
				$consulta="select idGrupos from 4520_grupos g where Plantel='".$plantel."' AND '".$fechaActual."'>=g.fechaInicio AND '".$fechaActual."'<=g.fechaFin";
				$listGrupos=$con->obtenerListaValores($consulta);
				if($listGrupos=="")
					$listGrupos=-1;
					
				$consulta="(SELECT idHorarioGrupo,idGrupo,dia,horaInicio,horaFin,idAula  FROM 4522_horarioGrupo WHERE idGrupo IN (".$listGrupos.") AND dia=".$dia." and '".$fechaActual."'>=fechaInicio and '".$fechaActual."'<=fechaFin) union
					(SELECT (idSesionReposicion*-1),idGrupo,0,horaInicio,horaFin,idAula FROM 4563_sesionesReposicion 
					WHERE fechaReposicion='".$fechaActual."' AND idGrupo IN (".$listGrupos.")) ORDER BY horaInicio";
				
				$query[$x]="delete FROM 4559_controlDeFalta WHERE idGrupo NOT IN (SELECT idGrupos FROM 4520_grupos)";
				$x++;
				
				$res=$con->obtenerFilas($consulta);
				$arrHorario=array();
				while($fila=mysql_fetch_row($res))
				{
					array_push($arrHorario,$fila);
					
				}
				
				$nArrHorario=array();
				$nAux=0;
	
				for($nHorario=0;$nHorario<sizeof($arrHorario);$nHorario++)
				{
					$h=$arrHorario[$nHorario];
					$pos=obtenerHoraFinBloque($arrHorario,$h,($nHorario+1),$arrRecesos,$plantel);
					
					if($pos!=-1)
					{
						
						$nHorario=$pos;
						$h[4]=$arrHorario[$pos][4];

						
					}
					array_push($nArrHorario,$h);	
					
				}
				$arrHorario=$nArrHorario;
				foreach($arrHorario as $resto)
				{
					$query[$x]="delete from 4559_controlDeFalta where idGrupo=".$resto[1]." and fechaFalta='".$fechaActual."'";
					$x++;
					$query[$x]="delete from 9105_controlAsistencia where idGrupo=".$resto[1]." and fecha='".$fechaActual."'";
					$x++;
				}
				foreach($arrHorario as $resto)
				{
					
					/*$consulta="select * from 9105_controlAsistencia WHERE idGrupo=".$resto[1]." AND fecha='".$fechaActual."' AND horaInicioBloque='".$resto[3]."'";
					$fReg=$con->obtenerPrimeraFila($consulta);
					if(!$fReg)*/
					{

						$consulta="SELECT idUsuario FROM 4519_asignacionProfesorGrupo WHERE idGrupo=".$resto[1]." AND participacionPrincipal=0 
									AND '".$fechaActual."'>=fechaAsignacion AND '".$fechaActual."'<=fechaBaja";	
						$idUsuario=$con->obtenerValor($consulta);
						if($idUsuario=="")
						{
							$consulta="SELECT idUsuario FROM 4519_asignacionProfesorGrupo WHERE idGrupo=".$resto[1]." AND participacionPrincipal=1 
										AND '".$fechaActual."'>=fechaAsignacion and '".$fechaActual."'<=fechaBaja and fechaBaja is not null";	
							$idUsuario=$con->obtenerValor($consulta);
							if($idUsuario=="")
							{
								$consulta="SELECT idUsuario FROM 4519_asignacionProfesorGrupo WHERE idGrupo=".$resto[1]." AND participacionPrincipal=1 
										AND '".$fechaActual."'>=fechaAsignacion and fechaBaja is null";	
								$idUsuario=$con->obtenerValor($consulta);
								if($idUsuario=="")
								{
									$idUsuario=-1;
								}
							}
						}

						if($idUsuario<>-1)
						{
							if(ingresarAsistenciaExtendido($idUsuario,$resto[1],$fechaActual,$resto[3],$resto[4]))
							{
								$query[$x]="INSERT INTO 9105_controlAsistencia(idUsuario,fecha,hora_entrada,hora_salida,tipo,tRetardo,esRetardo,tAnticipado,
											tExtra,idGrupo,tipoEvento,horaInicioBloque,valorEvento,horaFinBloque)
											VALUES(".$idUsuario.",'".$fechaActual."','".$resto[3]."','".$resto[4]."',1,'00:00:00',0,
											'00:00:00','00:00:00',".$resto[1].",15,'".$resto[3]."',1,'".$resto[4]."')";
								$x++;
							}
						}
						
					}
					/*else
					{
						
						$query[$x]="delete from 9105_controlAsistencia where idAsistencia=".$fReg[0];
						$x++;
						$query[$x]="delete from 4559_controlDeFalta where idRegistroControlAsistencia=".$fReg[0];
						$x++;
						$query[$x]="INSERT INTO 9105_controlAsistencia(idUsuario,fecha,hora_entrada,hora_salida,tipo,tRetardo,esRetardo,tAnticipado,
											tExtra,idGrupo,tipoEvento,horaInicioBloque,valorEvento,horaFinBloque)
											VALUES(".$fReg[1].",'".$fechaActual."','".$resto[3]."','".$resto[4]."',1,'00:00:00',0,
											'00:00:00','00:00:00',".$resto[1].",15,'".$resto[3]."',1,'".$resto[4]."')";
						$x++;
						
						
						
					}*/
				}
				
				
			}
			$fInicio=strtotime("+1 days",$fInicio);
		}
		$query[$x]="commit";
		$x++;
		return $con->ejecutarBloque($query);
		
	}
	
	function ingresarAsistenciaExtendido($idUsuario,$idGrupo,$fecha,$horaInicio,$horaFin)
	{
		global $con;
		$consulta="SELECT idFalta FROM 4559_controlDeFalta WHERE idGrupo=".$idGrupo." AND fechaFalta='".$fecha."' and idUsuario=".$idUsuario.
					" and horaInicial='".$horaInicio."' and horaFinal='".$horaFin."' and estadoFalta in (1,2)";

		$idFalta=$con->obtenerValor($consulta);
		if($idFalta=="")
			return true;
		return false;
	}
	
	function eliminarEventosRepetidos($comp="1=1")
	{
		global $con;
		$x=0;
		$query[$x]="begin";
		$x++;
		$arrEventos=array();
		$consulta="SELECT * FROM 9105_eventosRecibidos where ".$comp." ORDER BY fechaEvento,horaEvento";
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$llave=$fila[1]."_".$fila[2]."_".$fila[3]."_".$fila[4]."_".$fila[5];
			if(!isset($arrEventos[$llave]))
			{
				$arrEventos[$llave]=1;
			}
			else
			{
				$query[$x]="delete from 9105_eventosRecibidos where idEvento=".$fila[0];
				$x++;
			}
		}
		$query[$x]="commit";
		$x++;
		return $con->ejecutarBloque($query);
		
	}
	
	function revisarFaltasJustificadas()
	{
		global $con;
		$consulta="SELECT * FROM _481_tablaDinamica WHERE  cmbFalta NOT IN  
					(SELECT idFalta FROM 4559_controlDeFalta)";
		$res=$con->obtenerFilas($consulta);
		echo "Total=".$con->filasAfectadas."<br><br>";
		while($fila=mysql_fetch_row($res))
		{
			$consulta="SELECT * FROM 4559_controlDeFalta_Historial WHERE idFalta=".$fila[12];
			$filaFalta=$con->obtenerPrimeraFila($consulta);
			if(!$filaFalta)
				continue;
			$consulta="SELECT * FROM 9105_controlAsistencia WHERE fecha='".$filaFalta[3]."' AND idGrupo = ".$filaFalta[1]." and horainicioBloque='".$filaFalta[4]."'";
			$f=$con->obtenerPrimeraFila($consulta);
			if($f[15]!=1)
			{
				if($f)
				{
					echo "Registro justificante:".$fila[0]."<br>";
					varDump($f);
				}
				else
				{
					echo "<font color='#FF0000'>Registro justificante:".$fila[0]." sin coincidencia</b><br>";
					varDump($filaFalta);
				}
			}
		}

	}
	
	function reasignarJustificacionesFalta()
	{
		global $con;
		$x=0;
		$query[$x]="begin";
		$x++;
		$listFaltas="-1";
		$consulta="SELECT cmbFalta FROM _481_tablaDinamica where cmbFalta in (".$listFaltas.")";
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			if($fila[0]=="")
				continue;
			$consulta="SELECT idGrupo,idUsuario,fechaFalta,horaInicial,idFormulario,idRegistroJustificacion,estadoFalta FROM 4559_controlDeFalta_Historial 
						WHERE idFalta=".$fila[0];
			$filaFalta=$con->obtenerPrimeraFila($consulta);
			if($filaFalta)
			{
				$consulta="SELECT * FROM 4559_controlDeFalta WHERE idGrupo=".$filaFalta[0]." AND idUsuario=".$filaFalta[1]." AND fechaFalta='".$filaFalta[2]."' 
						AND horaInicial='".$filaFalta[3]."'";

				$fFalta=$con->obtenerPrimeraFila($consulta);		
				if($fFalta)
				{
					if($filaFalta[4]=="")
						$filaFalta[4]="NULL";
					if($filaFalta[5]=="")
						$filaFalta[5]="NULL";
						
					$query[$x]="update 4559_controlDeFalta set idFormulario=".$filaFalta[4].",idRegistroJustificacion=".$filaFalta[5].",
								estadoFalta=".$filaFalta[6]." where idFalta=".$fFalta[0];
					$x++;	
					$query[$x]="update _481_tablaDinamica set cmbFalta=".$fFalta[0]." where cmbFalta=".$fila[0];
					$x++;	
				}
				else
				{
					$query[$x]="update _481_tablaDinamica set idEstado=0 where cmbFalta=".$fila[0];
					$x++;
				}
			}
			else
			{
				$query[$x]="update _481_tablaDinamica set idEstado=(idEstado*-1) where cmbFalta=".$fila[0];
				$x++;
			}
		}

		$consulta="SELECT idFalta FROM 4562_registroReposicionSesion where idFalta in (".$listFaltas.")";
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$consulta="SELECT idGrupo,idUsuario,fechaFalta,horaInicial,idFormulario,idRegistroJustificacion,estadoFalta FROM 4559_controlDeFalta_Historial 
						WHERE idFalta=".$fila[0];
			$filaFalta=$con->obtenerPrimeraFila($consulta);
			if($filaFalta)
			{
				$consulta="SELECT * FROM 4559_controlDeFalta WHERE idGrupo=".$filaFalta[0]." AND idUsuario=".$filaFalta[1]." AND fechaFalta='".$filaFalta[2]."' 
						AND horaInicial='".$filaFalta[3]."'";
				$fFalta=$con->obtenerPrimeraFila($consulta);		
				if($fFalta)
				{
					$query[$x]="update 4559_controlDeFalta set idFormulario=".$filaFalta[4].",idRegistroJustificacion=".$filaFalta[5].",
								estadoFalta=".$filaFalta[6]." where idFalta=".$fFalta[0];
					$x++;	
					$query[$x]="update 4562_registroReposicionSesion set idFalta=".$fFalta[0]." where idFalta=".$fila[0];
					$x++;	
				}
				else
				{
					$query[$x]="update 4562_registroReposicionSesion set idFalta=(idFalta*-1) where idFalta=".$fila[0];
					$x++;
				}
			}
			else
			{
				$query[$x]="update 4562_registroReposicionSesion set idFalta=(idFalta*-1) where idFalta=".$fila[0];
				$x++;
			}
		}
		$query[$x]="commit";
		$x++;
		$con->ejecutarBloque($query);
	}
	
	function obtenerNoBloquesEvaluacion($parametro,$tipoParam=1) //Grupo
	{
		global $con;
		$consulta="";
		if($tipoParam==1)
			$consulta="SELECT noParciales FROM 4520_grupos g,4502_Materias m WHERE m.idMateria=g.idMateria and g.idGrupos=".$parametro;
		else
			$consulta="SELECT noParciales FROM 4502_Materias m WHERE m.idMateria=".$parametro;
		$nParciales=$con->obtenerValor($consulta);
		return $nParciales;
	}
		
	function obtenerFechaFinCursoHorario($idGrupo,$fInicioCurso="",$arrHorario=null,$hAcumuladas=0,$noBloqueAsociado=0)
	{
		global $con;
		$ultimoHorario="";
		$fechaUltimaSesion="";
		$generarSesionPorDia=false;

		$query="SELECT max(fechaFin) FROM 4522_horarioGrupo WHERE idGrupo= ".$idGrupo." order by horaInicio";
		$maxFechaGrupo=$con->obtenerValor($query);

		$query="SELECT fechaInicio,Plantel,idMateria,idInstanciaPlanEstudio,idCiclo,idPeriodo,idGradoCiclo FROM 4520_grupos WHERE idGrupos=".$idGrupo;
		$fGpo=$con->obtenerPrimeraFila($query);
		$fechaAplicacion=strtotime($fGpo[0]);
		if($fInicioCurso!="")
		{
			$fechaAplicacion=strtotime($fInicioCurso);
		}
		$plantel=$fGpo[1];
		$arrDiasSesion=array();
		$query="SELECT fechaInicial,fechaFinal FROM 4544_fechasPeriodo WHERE idCiclo=".$fGpo[4]." AND idPeriodo=".$fGpo[5]." AND idInstanciaPlanEstudio='".$fGpo[3]."'";

		$fFechas=$con->obtenerPrimeraFila($query);	

		$fechaFinPeriodo=strtotime($fFechas[1]);
		if($noBloqueAsociado!=0)
		{
			$query="SELECT fechaFin FROM 4571_fechasBloquePeriodo WHERE idGrado=".$fGpo[6]." AND idCiclo=".$fGpo[4]." AND idPeriodo=".$fGpo[5]." AND idInstancia=".$fGpo[3]." AND noBloque=".$noBloqueAsociado;
			$fFinBloqueActual=$con->obtenerValor($query);	
			
			if($fFinBloqueActual=="")
			{
				$query="SELECT fechaInicio FROM 4571_fechasBloquePeriodo WHERE idGrado=".$fGpo[6]." AND idCiclo=".$fGpo[4]." AND idPeriodo=".$fGpo[5]." AND idInstancia=".$fGpo[3]." AND noBloque=".($noBloqueAsociado+1);
				
				$fInicioBloque=$con->obtenerValor($query);	
				if($fInicioBloque!="")
				{
					$fechaFinPeriodo=strtotime("-1 day",strtotime($fInicioBloque));
				}
			}
			else
				$fechaFinPeriodo=$fFinBloqueActual;
		}


		$arrDatosMateriaHoras=obtenerDatosMateriaHorasGrupo($idGrupo);
		$filaMat[0]	=$arrDatosMateriaHoras["horasSemana"];
		$filaMat[1]	=$arrDatosMateriaHoras["horaMateriaTotal"];
		
		$totalHoras=$filaMat[1];
		$duracionHora=obtenenerDuracionHoraGrupo($idGrupo);
					
		if($arrHorario==null)	
		{
			$query="SELECT dia,horaInicio,horaFin,fechaInicio,fechaFin FROM 4522_horarioGrupo WHERE idGrupo= ".$idGrupo." order by horaInicio";
			$res=$con->obtenerFilas($query);
			while($f=mysql_fetch_row($res))
			{
				$hInicio=strtotime($f[1]);
				$hFin=strtotime($f[2]);
				if(!isset($arrDiasSesion[$f[0]]))
				{
					$arrDiasSesion[$f[0]]=array();
				}
				if($f[4]==$maxFechaGrupo)
					$f[4]=date("Y-m-d",$fechaFinPeriodo);
				$obj[0]=date("H:i:s",$hInicio)." - ".date("H:i:s",$hFin);
				$obj[1]=strtotime($f[3]);
				$obj[2]=strtotime($f[4]);
				$obj[3]=(($hFin-$hInicio)/60)/$duracionHora;
				$obj[4]=$f[3];
				$obj[5]=$f[4];
				array_push($arrDiasSesion[$f[0]],$obj);
			}
		}
		else
			$arrDiasSesion=$arrHorario;	
		
		if(sizeof($arrDiasSesion)==0)
		{
			$fechaFinal=obtenerFechaFinCurso($fGpo[0],$fGpo[2],$fGpo[3]);
			return str_replace("'","",$fechaFinal);
		}
		
		$horasAcum=0+$hAcumuladas;

		$finalizar=false;
		$fechaUltimaSesion=strtotime($fGpo[0]);

		while(($horasAcum<$totalHoras)	&&(!$finalizar))
		{
			if((isset($arrDiasSesion[date("w",$fechaAplicacion)]))&&(!esDiaInhabilEscolar($plantel,date("Y-m-d",$fechaAplicacion))))
			{

				$arrHorario=$arrDiasSesion[date("w",$fechaAplicacion)];
				foreach($arrHorario as $h)	
				{
					if(($fechaAplicacion>=$h[1])&&($fechaAplicacion<=$h[2]))
					{
						
						$horasAcum+=$h[3];
						$fechaUltimaSesion=$fechaAplicacion;
					}
				}
			}
			$fechaAplicacion=strtotime("+1 days",$fechaAplicacion);

			if($fechaAplicacion>$fechaFinPeriodo)
			{
				$finalizar=true;
			}
		}
		
		return date("Y-m-d",$fechaUltimaSesion);
		
	}
	
	
	
	//-----Funciones de diagnostico
	
	function validarFechasTerminoGrupos($fechaInicioTermino,$ciclo,$idPeriodo,$corregirFecha=false)
	{
		global $con;
		$consulta="SELECT idGrupos,fechaFin,plantel,idInstanciaPlanEstudio FROM 4520_grupos WHERE situacion=1 AND idCiclo=".$ciclo." AND idPeriodo in (".$idPeriodo.") AND '".$fechaInicioTermino."'<=fechaFin";
		$res=$con->obtenerFilas($consulta);
		$ct=1;
		while($fila=mysql_fetch_row($res))
		{
			$fFinOriginal=$fila[1];
			$consulta="SELECT count(*) FROM 4522_horarioGrupo WHERE idGrupo=".$fila[0];
			$nHorario=$con->obtenerValor($consulta);
			$consulta="SELECT idUsuario FROM 4519_asignacionProfesorGrupo WHERE idGrupo=".$fila[0]." AND participacionPrincipal=1 AND (idUsuario<>0 AND idUsuario<>-1) and situacion=1";
			$nAsignacion=$con->obtenerValor($consulta);
			if(($nHorario>0)&&($nAsignacion!=""))
			{
				$fFinReal=obtenerFechaFinCursoHorario($fila[0]);
				if($fFinOriginal!=$fFinReal)
				{
					$consulta="select unidad from 817_organigrama where codigoUnidad='".$fila[2]."'";
					$plantel=$con->obtenerValor($consulta);
					$consulta="SELECT nombrePlanEstudios FROM 4513_instanciaPlanEstudio WHERE idInstanciaPlanEstudio=".$fila[3];
					$planEstudio=$con->obtenerValor($consulta);
					$consulta="select nombre from 800_usuarios where idUsuario=".$nAsignacion;
					$nProfesor=$con->obtenerValor($consulta);
					echo $ct.".- Plantel: ".str_replace(" ","_",$plantel)." Plan_Estudios: ".str_replace(" ","_",$planEstudio)." Grupo: ".$fila[0]." Profesor: ".str_replace(" ","_",$nProfesor)." Fecha_original :".$fFinOriginal." Fecha_correcta: ".$fFinReal."<br>";
					$ct++;
					if($corregirFecha)
					{
						ajustarFechaFinalCurso($fila[0]);
					}
				}
			}
		}
	}
	
	function validarProfesoresAsignadosGrupos($fecha="")
	{
		global $con;
		$consulta="";
		if($fecha=="")
			$consulta="SELECT DISTINCT idGrupo FROM 4519_asignacionProfesorGrupo";
		else
			$consulta="SELECT DISTINCT idGrupo FROM 4519_asignacionProfesorGrupo a,4520_grupos g where g.idGrupos=a.idGrupo and g.fechaFin>='".$fecha."'";
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$consulta="select fechaFin,fechaInicio FROM 4520_grupos WHERE idGrupos=".$fila[0];
			$fGrupo=$con->obtenerPrimeraFila($consulta);
			
			$consulta="select idUsuario,fechaAsignacion,fechaBaja,situacion FROM 4519_asignacionProfesorGrupo WHERE idGrupo=".$fila[0]." and participacionPrincipal=1 and fechaAsignacion<=fechaBaja order by fechaAsignacion ";
			$resProf=$con->obtenerFilas($consulta);
			if($con->filasAfectadas>0)
			{
				if($con->filasAfectadas==1)
				{
					$fProf=mysql_fetch_row($resProf);
					if(($fProf[2]!=$fGrupo[0])&&($fProf[3]==1))
					{
						echo $fProf[2]."!=".$fGrupo[0]."--<br>";
						echo 'IdGrupo='.$fila[0]."<br>Situacion=El profesor asignado no coincide con la fecha final de la materia<br>Fecha Grupo=".$fGrupo[1]." - ".$fGrupo[0]."<br><br>";
					}
				}
				else
				{
					$arrFechas=array();
					$maxFecha="";
					while($fProf=mysql_fetch_row($resProf))
					{
						$fInicio=($fProf[1]);
						$fFin=($fProf[2]);
						if($maxFecha=="")
							$maxFecha=$fFin;
						else
						{
							if(strtotime($maxFecha)<strtotime($fFin))
								$maxFecha=$fFin;
								
						}
						$conflicto=false;
						if(sizeof($arrFechas)>0)
						{
							foreach($arrFechas as $f)
							{
								if(colisionaTiempo($f[0],$f[1],$fInicio,$fFin,true))
								{
									
									echo 'IdGrupo='.$fila[0]."<br>Situacion=Conflicto en asignación de fechas<br>Fecha Grupo=".$fGrupo[1]." - ".$fGrupo[0]."<br><br>";
									$conflicto=true;
									break;
								}
							}
							
						}
						if($conflicto)
							break;
						$obj[0]=$fInicio;
						$obj[1]=$fFin;
						array_push($arrFechas,$obj);
						
					}
					
					
					if($maxFecha!=$fGrupo[0])
					{
						echo  $maxFecha."!=".$fGrupo[0]."<br>";
						echo 'IdGrupo='.$fila[0]."<br>Situacion=El profesor asignado no coincide con la fecha final de la materia<br>Fecha Grupo=".$fGrupo[1]." - ".$fGrupo[0]."<br><br>";
					}
				}
			
			}
		}
		
	}
		
	function validarHorariosAsignadosGrupos()
	{
		global $con;
		$consulta="SELECT DISTINCT idGrupo FROM 4522_horarioGrupo";
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$consulta="select fechaFin,fechaInicio FROM 4520_grupos WHERE idGrupos=".$fila[0];
			$fGrupo=$con->obtenerPrimeraFila($consulta);
			$consulta="select distinct fechaInicio,fechaFin FROM 4522_horarioGrupo WHERE idGrupo=".$fila[0]." and fechaInicio<=fechaFin order by fechaInicio";
			$resProf=$con->obtenerFilas($consulta);
			$arrFechas=array();
			$maxFecha="";
			while($fProf=mysql_fetch_row($resProf))
			{
				if($maxFecha=="")
					$maxFecha=$fProf[1];
				else
				{
					if(strtotime($maxFecha)<strtotime($fProf[1]))
						$maxFecha=$fProf[1];
				}
				
				$fInicio=($fProf[0]);
				$fFin=($fProf[1]);
				$conflicto=false;
				if(sizeof($arrFechas)>0)
				{
					foreach($arrFechas as $f)
					{
						if(colisionaTiempo($f[0],$f[1],$fInicio,$fFin,true))
						{
							echo 'IdGrupo='.$fila[0]."<br>Situacion=Conflicto en asignación de fechas<br>Fecha Grupo=".$fGrupo[1]." - ".$fGrupo[0]."<br><br>";
							$conflicto=true;
							break;
						}
					}
					
				}
				if($conflicto)
					break;
				$obj[0]=$fInicio;
				$obj[1]=$fFin;
				array_push($arrFechas,$obj);
				
			}
			if($maxFecha<>$fGrupo[0])
			{
				echo 'IdGrupo='.$fila[0]."<br>Situacion=Fecha de horario no coincide con fecha final de materia<br>Fecha Grupo=".$fGrupo[1]." - ".$fGrupo[0]."<br><br>";
			}
			
			
		}
		
	}
	
	function generarSesionesGrupos()
	{
		global $con;
		$consulta="SELECT DISTINCT idGrupo FROM 4519_asignacionProfesorGrupo WHERE idGrupo NOT IN(SELECT DISTINCT idGrupo FROM 4530_sesionesAux) ORDER BY idGrupo";//  
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$consulta="select * from 4520_grupos WHERE idGrupos=".$fila[0];

			$fGrupo=$con->obtenerPrimeraFila($consulta);
			$query=array();
			$x=0;
			ajustarSesiones($fila[0],strtotime($fGrupo[7]),NULL,$query,$x,true);
			$consulta="insert into 4530_sesionesAux(idGrupo) values(".$fila[0].")";
			$con->ejecutarConsulta($consulta);
		}
	}
		
	function verificarBajasDocentes($corregir=false)
	{
		global $con;
		$consulta="SELECT datosSolicitud,g.idGrupo,idAsignacion,version FROM 4548_solicitudesMovimientoGrupo s,4548_gruposSolicitudesMovimiento g WHERE tipoSolicitud=2 AND situacion=3 and g.idSolicitud=s.idSolicitudMovimiento";
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$cadObj=($fila[0]);
			$obj=json_decode($cadObj);
			$fBaja="";
			if($fila[3]==1)
			{
				$consulta="select fechaBaja from _447_tablaDinamica where id__447_tablaDinamica=".$obj->idRegistro;
				$fBaja=$con->obtenerValor($consulta);
			}
			else
				$fBaja=$obj->fechaBaja;
			$consulta="SELECT fechaBaja,idUsuario FROM 4519_asignacionProfesorGrupo WHERE idAsignacionProfesorGrupo=".$fila[2];
			$fDatos=$con->obtenerPrimeraFila($consulta);
			if(!$fDatos)
				continue;
			if($fBaja!=$fDatos[0])
			{
				echo "Grupo ".$fila[1]." Usuario:".$fDatos[1]." Fecha baja:".$fBaja." Fecha baja actual:".$fDatos[0]."<br>";
				if($corregir)
				{
					$x=0;
					$query[$x]="begin";
					$x++;
					$query[$x]="update 4519_asignacionProfesorGrupo set situacion=0,fechaBaja='".$fBaja."' WHERE idAsignacionProfesorGrupo=".$fila[2];
					$x++;
					$query[$x]="delete from 4559_controlDeFalta where fechaFalta>'".$fBaja."' and idUsuario=".$fDatos[1]." and idGrupo=".$fila[1];
					$x++;
					$query[$x]="commit";
					$x++;
					eB($query);
					
				}
			}
			
		}
	}
	
	function verificarDuplicidadHorasAsistencia($fecha)
	{
		global $con;
		$consulta="SELECT DISTINCT fecha,idGrupo,idUsuario,horaInicioBloque FROM 9105_controlAsistencia WHERE valorEvento=3 AND fecha='2012-07-10'";
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$consulta="select count(*) from 9105_controlAsistencia where fecha='".$fila[0]."' and idGrupo=".$fila[1]." and idUsuario=".$fila[2]." and horaInicioBloque='".$fila[3]."'";
			$nReg=$con->obtenerValor($consulta);
			if($nReg>1)
			{
				echo "select * from 9105_controlAsistencia where fecha='".$fila[0]."' and idGrupo=".$fila[1]." and idUsuario=".$fila[2]." and horaInicioBloque='".$fila[3]."'<br>";
			}
		}
	}
	
	//--
	function reprocesarEventoUsuario($idUsuario,$fechaEvento,$plantel,$validarEventosPlantel=true)
	{
		global $con;
		$arrTipoIncidencias=array();
		$query="SELECT cmbUnirBloquesContiguos FROM _484_tablaDinamica";
		$esquemaProcesamientoEventos=$con->obtenerValor($query);
		$query="SELECT tipoEvento,tipoincidencia FROM _484_gridTiposIncidencias ORDER BY tipoEvento";
		$res=$con->obtenerFilas($query);
		while($f=mysql_fetch_row($res))
		{
			$arrTipoIncidencias["".$f[0]]=$f[1];
		}
		
		$arrPlantelesBiometrico=array();
		$arrRecesos=obtenerArregloRecesos();
		$query="SELECT id__494_tablaDinamica,cmbPlanteles FROM _494_tablaDinamica ";
		$res=$con->obtenerFilas($query);
		while($fila=mysql_fetch_row($res))
		{
			$arrPlantelesBiometrico[$fila[1]]=array();
			$arrPlantelesBiometrico[$fila[1]][$fila[1]]=1;
			$query="SELECT plantelCompartido FROM _494_gridSedeCompartida WHERE idReferencia=".$fila[0];
			$resC=$con->obtenerFilas($query);
			while($fPlantel=mysql_fetch_row($resC))
			{
				$arrPlantelesBiometrico[$fila[1]][$fPlantel[0]]=1;
			}
		}
		
		$plantelBase="";
		$listPlanteles="";
		foreach($arrPlantelesBiometrico as $plantelIndice=>$resto)
		{
			$plantelBase=$plantelIndice;
			if(isset($resto[$plantel]))
				break;
		}
		if($plantelBase!="")
		{
			foreach($arrPlantelesBiometrico[$plantelBase] as $pAux=>$resto)
			{
				if($listPlanteles=="")
					$listPlanteles="'".$pAux."'";
				else
					$listPlanteles.=",'".$pAux."'";
			}
		}
		
		$x=0;
		$consulta[$x]="INSERT INTO 9105_eventosRecibidos(idUsuario,fechaEvento,horaEvento,noTerminal,plantel,marcaSincronizacion)
					SELECT idUsuario,fechaEvento,horaEvento,noTerminal,plantel,marcaSincronizacion FROM 9105_eventosControlAsistencia WHERE fechaEvento='".$fechaEvento."' AND idUsuario=".$idUsuario;
					
		if($validarEventosPlantel)			
			$consulta[$x].=" and plantel in (".$listPlanteles.")";
		
		$x++;
		$consulta[$x]="delete FROM 9105_eventosControlAsistencia WHERE fechaEvento='".$fechaEvento."' AND idUsuario=".$idUsuario;
		if($validarEventosPlantel)			
			$consulta[$x].=" and plantel in (".$listPlanteles.")";
		$x++;
		
		$query="SELECT g.idGrupos FROM 4520_grupos g,4519_asignacionProfesorGrupo a WHERE a.idUsuario=".$idUsuario." AND '".$fechaEvento."'>=a.fechaAsignacion AND  '".$fechaEvento."'<=a.fechaBaja AND
				a.idGrupo=g.idGrupos AND g.Plantel in (".$listPlanteles.")";
		$listGrupos=$con->obtenerListaValores($query);
		if($listGrupos=="")
			$listGrupos=-1;
		$arrGrupos=explode(",",$listGrupos);
		$listFaltasEliminar="";
		$arrFaltasEliminar=array();
		$query="SELECT idFalta,f.idGrupo FROM 4559_controlDeFalta f,4520_grupos g WHERE f.fechaFalta='".$fechaEvento."' AND f.idUsuario=".$idUsuario." AND g.idGrupos=f.idGrupo AND g.Plantel in (".$listPlanteles.")";
		$res=$con->obtenerFilas($query);
		while($fila=mysql_fetch_row($res))
		{
			if(existeValor($arrGrupos,$fila[1]))
			{
				if($listFaltasEliminar=="")
					$listFaltasEliminar=$fila[0];
				else
					$listFaltasEliminar.=",".$fila[0];
				array_push($arrFaltasEliminar,$fila[0]);
			}
		}
		
		$consulta[$x]="DELETE FROM 9105_controlAsistencia  WHERE fecha='".$fechaEvento."' and idUsuario=".$idUsuario." and idGrupo in (".$listGrupos.")";
		$x++;
		
		$consulta[$x]="commit";
		$x++;
		if($con->ejecutarBloque($consulta))
		{
			
			
			$comp="idUsuario=".$idUsuario." and fechaEvento='".$fechaEvento."'";
			if($validarEventosPlantel)
				$comp.=" and plantel in (".$listPlanteles.")";
			if(!eliminarEventosRepetidos($comp))
				return;
			$comp=" and fechaEvento='".$fechaEvento."'";
			if($validarEventosPlantel)
				$comp.=" and plantel in (".$listPlanteles.")";
			$resProcesamiento=null;

			if($esquemaProcesamientoEventos==0)
				$resProcesamiento=procesarEventosBiometricosUsuario($idUsuario,$arrTipoIncidencias,$arrPlantelesBiometrico,date("Y-m-d H:i"),$arrRecesos,$comp,$validarEventosPlantel);
			else
				$resProcesamiento=procesarEventosBiometricosUsuarioBloque($idUsuario,$arrTipoIncidencias,$arrPlantelesBiometrico,date("Y-m-d H:i"),$arrRecesos,$comp,$validarEventosPlantel,true);
			
			if($resProcesamiento)
			{
				if(sizeof($arrFaltasEliminar)>0)
				{
					$consulta=array();
					$x=0;
					$consulta[$x]="begin";
					$x++;
					foreach($arrFaltasEliminar as $idFalta)
					{
						$query="SELECT COUNT(*) FROM 4559_controlDeFalta WHERE idFalta=".$idFalta;
						$nFalta=$con->obtenerValor($query);
						if($nFalta==0)
						{
							$query="SELECT idRegistroReposicion FROM 4562_registroReposicionSesion WHERE idFalta=".$idFalta;
							$listRegistroFalta=$con->obtenerListaValores($query);
							if($listRegistroFalta!="")
							{
								$query="SELECT * FROM 4563_sesionesReposicion WHERE listRegistroFalta in (".$listRegistroFalta.")";
								$res=$con->obtenerFilas($query);
								while($fila=mysql_fetch_row($res))
								{
									$consulta[$x]="DELETE FROM 4530_sesiones WHERE idGrupo=".$fila[7]." AND fechaSesion='".$fila[2]."' and horario='".$fila[3]." - ".$fila[4]."' AND tipoSesion=15";
									$x++;
								}
								$consulta[$x]="delete FROM 4562_registroReposicionSesion WHERE idFalta = ".$idFalta;
								$x++;
								$consulta[$x]="delete FROM 4563_sesionesReposicion WHERE idRegistroReposicion in (".$listRegistroFalta.")";
								$x++;
							}	
						}
					}
					$consulta[$x]="commit";
					$x++;
					return $con->ejecutarBloque($consulta);
				}
				
				return true;
			}
			return true;
		}
		return false;
	}
	
	function reprocesarEventosUsuarioPeriodo($idUsuario,$plantel,$fechaInicio,$fechaFin,$validarEventosPlantel=true)
	{
		global $con;
		$consulta="SELECT cmbUnirBloquesContiguos FROM _484_tablaDinamica";
		$esquemaProcesamientoEventos=$con->obtenerValor($consulta);
		$fI=strtotime($fechaInicio);
		$fF=strtotime($fechaFin);
		while($fI<=$fF)
		{
			$resProcesamiento=reprocesarEventoUsuario($idUsuario,date("Y-m-d",$fI),$plantel,$validarEventosPlantel);
			if(!$resProcesamiento)
				return false;
			$fI=strtotime("+1 days",$fI);
			
		}

		$query="UPDATE 9105_controlAsistencia SET tipo=1 WHERE idAsistencia in 
					(select idAsistencia from 9105_controlAsistencia c,4520_grupos g where fecha>='".$fechaInicio."' and fecha<='".$fechaFin."' and idUsuario=".$idUsuario." and g.idGrupos=c.idGrupo and g.Plantel='".$plantel."')";
		return $con->ejecutarConsulta($query);
		
	}
	
	function reprocesarEventosPlantelPeriodo($plantel,$fechaInicio,$fechaFin,$validarEventosPlantel=true)
	{
		global $con;
		
		$arrPlantelesBiometrico=array();
		$arrRecesos=obtenerArregloRecesos();
		$consulta="SELECT id__494_tablaDinamica,cmbPlanteles FROM _494_tablaDinamica ";
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$arrPlantelesBiometrico[$fila[1]]=array();
			$arrPlantelesBiometrico[$fila[1]][$fila[1]]=1;
			$consulta="SELECT plantelCompartido FROM _494_gridSedeCompartida WHERE idReferencia=".$fila[0];
			$resC=$con->obtenerFilas($consulta);
			while($fPlantel=mysql_fetch_row($resC))
			{
				$arrPlantelesBiometrico[$fila[1]][$fPlantel[0]]=1;
			}
		}
		
		$plantelBase="";
		$listPlanteles="";
		foreach($arrPlantelesBiometrico as $plantelIndice=>$resto)
		{
			$plantelBase=$plantelIndice;
			if(isset($resto[$plantel]))
				break;
		}
		
		if($plantelBase!="")
		{
			foreach($arrPlantelesBiometrico[$plantelBase] as $pAux=>$resto)
			{
				if($listPlanteles=="")
					$listPlanteles="'".$pAux."'";
				else
					$listPlanteles.=",'".$pAux."'";
			}
		}
		
		
		$fechaEvento=strtotime($fechaInicio);
		$fechaFinEvento=strtotime($fechaFin);
		while($fechaEvento<=$fechaFinEvento)
		{
			$consulta="";
			if($validarEventosPlantel)
			{
				$consulta="select distinct * from ((SELECT idUsuario FROM 9105_eventosControlAsistencia WHERE fechaEvento='".date("Y-m-d",$fechaEvento)."' 
						AND plantel in(".$listPlanteles.") and idUsuario<>0)
						union
						(SELECT idUsuario FROM 9105_eventosRecibidos WHERE fechaEvento='".date("Y-m-d",$fechaEvento)."' 
						AND plantel in(".$listPlanteles.") and idUsuario<>0)) as tmp order by idUsuario";
			}
			else
			{
				$consulta="SELECT distinct a.idUsuario FROM 4520_grupos g,4519_asignacionProfesorGrupo a WHERE g.Plantel in(".$listPlanteles.") and  a.idGrupo=g.idGrupos  
						AND a.fechaAsignacion<='".date("Y-m-d",$fechaEvento)."' AND a.fechaBaja>='".date("Y-m-d",$fechaEvento)."' ";
			}
			
			$resUsr=$con->obtenerFilas($consulta);
			while($fUsr=mysql_fetch_row($resUsr))
			{

				//if($fUsr[0]==76218)
				{	
					//echo $fUsr[0]."<br>";
					if(!reprocesarEventoUsuario($fUsr[0],date("Y-m-d",$fechaEvento),$plantel,$validarEventosPlantel))
						return false;
				}
				
			}
			$fechaEvento=strtotime("+1 days",$fechaEvento);
		}
		$x=0;
		$query[$x]="begin";
		$x++;
		
		
		$consulta="SELECT distinct g.idGrupos,a.fechaBaja,a.idUsuario FROM 4520_grupos g,4519_asignacionProfesorGrupo a WHERE 
				(
					(g.fechaInicio<='".$fechaInicio."' AND  g.fechaFin>='".$fechaInicio."') OR 
					(g.fechaInicio>='".$fechaInicio."' and g.fechaFin<='".$fechaFin."')
					OR (g.fechaInicio<='".$fechaFin."' AND  g.fechaFin>='".$fechaFin."')
				) AND
				a.idGrupo=g.idGrupos AND g.Plantel in(".$listPlanteles.") and a.fechaAsignacion<=a.fechaBaja";
		$resGpos=$con->obtenerFilas($consulta);
		while($fGpo=mysql_fetch_row($resGpos))
		{
			$query[$x]="DELETE FROM 4559_controlDeFalta  WHERE idGrupo=".$fGpo[0]." and fechaFalta>'".$fGpo[1]."' and idUsuario=".$fGpo[2];
			$x++;
		}
		
		$query[$x]="commit";
		$x++;
		
		if($con->ejecutarBloque($query))
		{
			
			return verificarCierreEventos($fechaInicio,$fechaFin,$plantel);
		}
	}
		//
	function obtenerOrigenGrupoAlumno($idGrupo)
	{
		global $con;
		$consulta="SELECT nombreGrupo,idInstanciaPlanEstudio FROM 4520_grupos WHERE idGrupos=".$idGrupo;
		$fGrupo=$con->obtenerPrimeraFila($consulta);
		return cv($fGrupo[0]).", ".cv(obtenerNombreInstanciaPlan($fGrupo[1]));
		
	}	
	
	function obtenerIdProgramaEducativoInstancia($idInstancia)
	{
		global $con;
		$consulta="SELECT idProgramaEducativo FROM 4500_planEstudio p,4513_instanciaPlanEstudio i WHERE i.idPlanEstudio=p.idPlanEstudio AND i.idInstanciaPlanEstudio=".$idInstancia;
		$idPrograma=$con->obtenerValor($consulta);
		return $idPrograma;
	}
	
	//Calificaciones alumno
	
	function recalcularCalificacionesGrupo($idGrupo,$idCriterio,$bloque,$tipoEvaluacion,$noEvaluacion,$idAlumno=-1)//1 Cambio de limite máximo,2 Valor calificacion,3 Porcentaje
	{
		global $con;
		
		$consulta="SELECT idPlanEstudio,idInstanciaPlanEstudio,idMateria FROM 4520_grupos WHERE idGrupos=".$idGrupo;
		$fGrupo=$con->obtenerPrimeraFila($consulta);
		$idPlanEstudio=$fGrupo[0];
		$idInstanciaPlanEstudio=$fGrupo[1];
		$idReferenciaExamenes=obtenerPerfilExamenesAplica($idPlanEstudio,$idInstanciaPlanEstudio);
		$idMateria=$fGrupo[2];
		$idPerfil=-1;
		$consulta="SELECT idPerfil,calificacionMinimaAprobatoria FROM 4592_configuracionPerfilEvaluacion WHERE  idInstanciaPlanEstudio in (".$idInstanciaPlanEstudio.",-1) AND idMateria=".$idMateria." and
				idGrupo in (".$idGrupo.",-1) AND tipoExamen=".$tipoEvaluacion." AND noExamen=".$noEvaluacion." order by idGrupo desc, idInstanciaPlanEstudio DESC";
		$fPerfil=$con->obtenerPrimeraFila($consulta);
		if(!$fPerfil)
		{
			$calificacion=obtenerValorConfiguracionEvaluacion($idReferenciaExamenes,$tipoEvaluacion,13);//Calificacion
			$idPerfil=obtenerPerfilCriterioEvaluacionGrupo($idGrupo,$tipoEvaluacion);//Perfil evaluacion
			$idPerfilEvaluacionMateria="-1";
			
		}
		else
		{
			$idPerfil=$fPerfil[0];
			$calificacion=$fPerfil[1];
			$idPerfilEvaluacionMateria=$fPerfil[2];
		}
		
		
		
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
		
		$consulta="SELECT valMax FROM 4571_valoresMaximosCriterioPerfilMateria WHERE idGrupo in (".$idGrupo.",-1) AND noBloque=".$bloque." AND idCriterio='".$idCriterio.
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

		
		$cache=NULL;
		$arrUsrFinal=array();
		if($idAlumno==-1)
		{
			$idFuncionListado=obtenerValorConfiguracionEvaluacion($idReferenciaExamenes,$tipoEvaluacion,6);//FUncion listado alumnos
		
			if($idFuncionListado!="0")
			{
				$cTmp='{"idGrupo":"'.$idGrupo.'","tipoEvaluacion":"'.$tipoEvaluacion.'","noEvaluacion":"'.$noEvaluacion.'"}';
				$objTmp=json_decode($cTmp);
				
				
				$arrUsr=resolverExpresionCalculoPHP($idFuncionListado,$objTmp,$cache);
				
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
			$consulta="select * from 4568_calificacionesCriteriosAlumnoPerfilMateria WHERE idAlumno=".$fila[0]." AND idGrupo=".$idGrupo." AND idCriterio=".$idCriterio.
							" and bloque=".$bloque." and tipoEvaluacion=".$tipoEvaluacion." and noEvaluacion=".$noEvaluacion;	
			$fCal=$con->obtenerPrimeraFila($consulta);

			if($fCal)
			{						
				$calificacion=$fCal[5];
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
			if($pCriterio=="")
				$pCriterio=0;
			if($idCalificacion!=-1)
			{
				$consulta="UPDATE 4568_calificacionesCriteriosAlumnoPerfilMateria SET valor=".$calificacion.",porcentajeObtenido=".$calificacionFinal.",porcentajeValor=".$pCriterio.",totalConsiderar='".$limiteMax."' WHERE idCalificacion=".$idCalificacion;
			}
			else
			{
				$consulta="insert into 4568_calificacionesCriteriosAlumnoPerfilMateria (idAlumno,idGrupo,idCriterio,bloque,valor,porcentajeObtenido,porcentajeValor,totalConsiderar,tipoEvaluacion,noEvaluacion) 
							values(".$fila[0].",".$idGrupo.",'".$idCriterio."',".$bloque.",".$calificacion.",".$calificacionFinal.",".$pCriterio.",'".$limiteMax."',".$tipoEvaluacion.",".$noEvaluacion.")";
			}
			
//
			if(!(($con->ejecutarConsulta($consulta))&&(recalcularCalificacionCriterioPadre($idGrupo,$idCriterio,$bloque,$fila[0],$tipoEvaluacion,$noEvaluacion))&&(recalcularCalificacionBloque($idGrupo,$bloque,$fila[0],$tipoEvaluacion,$noEvaluacion))))
			{
				return false;
			}
		}
		return true;
	}
	
	function recalcularCalificacionCriterioPadre($idGrupo,$idCriterio,$bloque,$idAlumno,$tipoEvaluacion,$noEvaluacion)
	{
		global $con;
		if(strlen($idCriterio)==14)
			return true;
			
		$query="SELECT idPlanEstudio,idInstanciaPlanEstudio,idMateria FROM 4520_grupos WHERE idGrupos=".$idGrupo;
		$fGrupo=$con->obtenerPrimeraFila($query);
		
		
		
		$idPlanEstudio=$fGrupo[0];
		$idInstanciaPlanEstudio=$fGrupo[1];
		$idMateria=$fGrupo[2];
		
		/*$consulta="SELECT idPerfil,calificacionMinimaAprobatoria FROM 4592_configuracionPerfilEvaluacion WHERE idPlanEstudio=".$idPlanEstudio." AND idInstanciaPlanEstudio=".$idInstanciaPlanEstudio." AND idMateria=".$idMateria." and
				idGrupo in (".$idGrupo.",-1) AND tipoExamen=".$tipoEvaluacion." AND noExamen=".$noEvaluacion." order by idGrupo desc";
		$fPerfil=$con->obtenerPrimeraFila($consulta);
		$idPerfil=$fPerfil[0];
		if($idPerfil=="")
			$idPerfil=-1;	*/
			
		$idPerfil=obtenerPerfilCriterioEvaluacionGrupo($idGrupo,$tipoEvaluacion);//Perfil evaluacion
			
		$codigoPadre=substr($idCriterio,0,strlen($idCriterio)-7);
		
		$consulta="SELECT idCriterio FROM 4564_criteriosEvaluacionPerfilMateria WHERE codigoUnidad='".$codigoPadre."'";
		
		
		$idCriterioEval=$con->obtenerValor($consulta);
		
		$consulta="SELECT idTipoEvaluacion,funcionEvaluacion,funcionSistemaValMax,formaValorMaximoCriterio,valorMaximo,idEscalaCalificacion,idEvaluacion,precisionDecimales,accionPrecision FROM 4010_evaluaciones e
					 WHERE e.idEvaluacion= ".$idCriterioEval;
		
		$fCriterio=$con->obtenerPrimeraFila($consulta);
				
		$consulta="SELECT porcentajeObtenido,idCriterio FROM 4568_calificacionesCriteriosAlumnoPerfilMateria WHERE idAlumno=".$idAlumno." AND idGrupo=".$idGrupo." AND idCriterio LIKE '".$codigoPadre."%' AND bloque=".
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

		
		$consulta="select * from 4568_calificacionesCriteriosAlumnoPerfilMateria WHERE idAlumno=".$idAlumno." AND idGrupo=".$idGrupo." AND idCriterio=".$codigoPadre." and bloque=".$bloque.
					" and  tipoEvaluacion=".$tipoEvaluacion." and noEvaluacion=".$noEvaluacion;	
		$fCal=$con->obtenerPrimeraFila($consulta);
		$idCalificacion=-1;
		if($fCal)
			$idCalificacion=$fCal[0];
		
		
		if($idCalificacion!=-1)
		{
			$consulta="UPDATE 4568_calificacionesCriteriosAlumnoPerfilMateria SET valor=0,porcentajeObtenido=".$calificacionFinal.",porcentajeValor=".$pCriterio.
				",totalConsiderar=".$pCriterio." WHERE idCalificacion=".$idCalificacion;
		}
		else
		{
			$consulta="insert into 4568_calificacionesCriteriosAlumnoPerfilMateria (idAlumno,idGrupo,idCriterio,bloque,valor,porcentajeObtenido,porcentajeValor,totalConsiderar,tipoEvaluacion,noEvaluacion) 
						values(".$idAlumno.",".$idGrupo.",'".$codigoPadre."',".$bloque.",0,".$calificacionFinal.",".$pCriterio.",".$pCriterio.",".$tipoEvaluacion.",".$noEvaluacion.")";

		}
		
		if($con->ejecutarConsulta($consulta))
		{
			
			return recalcularCalificacionCriterioPadre($idGrupo,$codigoPadre,$bloque,$idAlumno,$tipoEvaluacion,$noEvaluacion);

		}
	}
	
	function recalcularCalificacionBloque($idGrupo,$bloque,$idAlumno,$tipoEvaluacion,$noEvaluacion)
	{
		global $con;
		
		$query="SELECT idPlanEstudio,idInstanciaPlanEstudio,idMateria FROM 4520_grupos WHERE idGrupos=".$idGrupo;
		$fGrupo=$con->obtenerPrimeraFila($query);
		
		
		
		$idPlanEstudio=$fGrupo[0];
		$idInstanciaPlanEstudio=$fGrupo[1];
		$idMateria=$fGrupo[2];
		
		/*$consulta="SELECT idPerfil,calificacionMinimaAprobatoria FROM 4592_configuracionPerfilEvaluacion WHERE idPlanEstudio=".$idPlanEstudio." AND idInstanciaPlanEstudio=".$idInstanciaPlanEstudio." AND idMateria=".$idMateria." and
				idGrupo in (".$idGrupo.",-1) AND tipoExamen=".$tipoEvaluacion." AND noExamen=".$noEvaluacion." order by idGrupo desc";
		$fPerfil=$con->obtenerPrimeraFila($consulta);
		$idPerfil=$fPerfil[0];
		if($idPerfil=="")
			$idPerfil=-1;*/
		
		$idPerfil=obtenerPerfilCriterioEvaluacionGrupo($idGrupo,$tipoEvaluacion);//Perfil evaluacion
		
		
		$query="SELECT calificacionMinimaAprobatoria FROM 4592_configuracionPerfilEvaluacion WHERE idPlanEstudio=".$fGrupo[0]." AND idMateria=".$fGrupo[2]." AND idInstanciaPlanEstudio IN (".$fGrupo[1].",-1) AND idGrupo IN (".$idGrupo.",-1)
				AND tipoExamen=".$tipoEvaluacion." AND noExamen=".$noEvaluacion." order by idGrupo desc,idInstanciaPlanEstudio desc";
		$calMinima=$con->obtenerValor($query);
		
		
		$consulta="SELECT idCriterio,porcentajeObtenido FROM 4568_calificacionesCriteriosAlumnoPerfilMateria WHERE idAlumno=".$idAlumno." AND idGrupo=".$idGrupo.
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
		$consulta="SELECT idCalificacionBloque FROM 4569_calificacionesEvaluacionAlumnoPerfilMateria WHERE idAlumno=".$idAlumno." AND idGrupo=".$idGrupo." AND bloque=".$bloque.
				" and tipoEvaluacion=".$tipoEvaluacion." and noEvaluacion=".$noEvaluacion;	

		$idCalificacionBloque=$con->obtenerValor($consulta);
		if($idCalificacionBloque=="")
			$consulta="INSERT INTO 4569_calificacionesEvaluacionAlumnoPerfilMateria(idAlumno,idGrupo,bloque,valor,tipoEvaluacion,noEvaluacion,aprobado,idMateria) 
						VALUES(".$idAlumno.",".$idGrupo.",".$bloque.",".$total.",".$tipoEvaluacion.",".$noEvaluacion.",".$aprobado.",".$idMateria.")";
		else
			$consulta="UPDATE 4569_calificacionesEvaluacionAlumnoPerfilMateria SET idMateria=".$idMateria.",aprobado=".$aprobado.", idAlumno=".$idAlumno.",idGrupo=".$idGrupo.",bloque=".$bloque.",valor=".$total." WHERE idCalificacionBloque=".$idCalificacionBloque;
			
		return $con->ejecutarConsulta($consulta);
	}
	
	function recalcularCalificacionFinalMateria($idGrupo,$idAlumno)
	{
		global $con;
		$query="select count(*) from 4569_calificacionesBloqueAlumno where idAlumno=".$idAlumno." and idGrupo=".$idGrupo;
	
		$nBloques=$con->obtenerValor($query);
		if($nBloques==5)
		{
			$aprobado=0;
			
			$query="select avg(valor) from 4569_calificacionesBloqueAlumno where idAlumno=".$idAlumno." and idGrupo=".$idGrupo;

			$calificacion=$con->obtenerValor($query);
			if($calificacion>=6)
				$aprobado=1;
			$query="INSERT INTO 4570_calificacionesAlumnoMateria(idAlumno,idGrupo,valor,aprobado) 
							VALUES(".$idAlumno.",".$idGrupo.",".$calificacion.",".$aprobado.")";
			return $con->ejecutarConsulta($query);
		}
		return true;	
	}
	
	
	//Contratos
	function reconstruirContrato($idContrato)
	{
		global $con;
		global $arrMesLetra;
		$rector="ING. LUIS REYES LARIOS";
		$consulta="SELECT * FROM 4553_contratosProfesores WHERE idContratoProfesor=".$idContrato;

		$fContrato=$con->obtenerPrimeraFila($consulta);
		$arrMaterias=array();
		$fInicio="";
		$fTermino="";
		$fLetraIni="";
		$fLetraFin="";
		$costoHora="";
		$idNivel="";
		$consulta="SELECT m.idMateria,g.idGrupos,m.nombreMateria,m.horasSemana,a.idAsignacionProfesorGrupo,a.fechaAsignacion,a.fechaBaja,
					pe.nivelPlanEstudio,i.idModalidad,g.idInstanciaPlanEstudio,g.nombreGrupo 
					FROM 4520_grupos g,4519_asignacionProfesorGrupo a,4502_Materias m,4513_instanciaPlanEstudio i,4500_planEstudio pe 
					WHERE a.idGrupo=g.idGrupos AND m.idMateria=g.idMateria and
					pe.idPlanEstudio=i.idPlanEstudio and i.idInstanciaPlanEstudio=g.idInstanciaPlanEstudio and a.idContrato=".$idContrato." 
					and ((a.fechaAsignacion<=a.fechaBaja)or((a.fechaAsignacion>a.fechaBaja) and (ignoraContrato=1)))
					order by nombreMateria";
		$resMaterias=$con->obtenerFilas($consulta);
		while($fMateria=mysql_fetch_row($resMaterias))
		{
			$costoHora=obtenerCostoProfesor($fContrato[1],$fContrato[12],$fMateria[9],$fMateria[1]);
			$datosMateria=obtenerDatosMateriaHorasGrupo($fMateria[1]);
			
			
			$objMat='{"idMateria":"'.$fMateria[0].'","idGrupo":"'.$fMateria[1].'","nombreMateria":"'.$fMateria[2].'","horasMateria":"'.$datosMateria["horasSemana"].'",
					"fechaInicio":"'.$fMateria[5].'","fechaFin":"'.$fMateria[6].'","costoHora":"'.$costoHora.'","grupo":"'.$fMateria[10].'"}';
			$oMat=json_decode($objMat);
			array_push($arrMaterias,$oMat);
			
			if($fInicio=="")
				$fInicio=$fMateria[5];
			else
				if(strtotime($fInicio)>strtotime($fMateria[5]))
					$fInicio=$fMateria[5];
			
			if($fTermino=="")
				$fTermino=$fMateria[6];
			else
				if(strtotime($fTermino)<strtotime($fMateria[6]))
					$fTermino=$fMateria[6];
				
			$dia=date("d",strtotime($fInicio));
			$mes=$arrMesLetra[(date("m",strtotime($fInicio))*1)-1];
			$ano=date("Y",strtotime($fInicio));
			$fLetraIni=$dia." de ".$mes." de ".$ano;
			$dia=date("d",strtotime($fTermino));
			$mes=$arrMesLetra[(date("m",strtotime($fTermino))*1)-1];
			$ano=date("Y",strtotime($fTermino));
			$fLetraFin=$dia." de ".$mes." de ".$ano;
			$idNivel=$fMateria[7];
			
		}
		if($idNivel=="")
			$idNivel=-1;
		$cadObj='{"idProfesor":"","profesor":"","rector":"","costoHora":"","modalidad":"","plantel":"","fechaInicioContrato":"","fInicioC":"",
					"fechaFinContrato":"","fFinC":"","fechaContrato":"","numeroContrato":"","idCiclo":"","idPeriodo":"","nombreArchivo":"","arrMaterias":""}';
		$obj=json_decode($cadObj);
		
		$consulta="SELECT CONCAT(Paterno,'_',Materno,'_',Nom) FROM 802_identifica WHERE idUsuario=".$fContrato[1];
		$nombreProfesor=(str_replace(" ","_",$con->obtenerValor($consulta)));
		$consulta="SELECT CONCAT(Nom,'_',Paterno,' ',Materno) FROM 802_identifica WHERE idUsuario=".$fContrato[1];
		$nombreProfesorContrato=$con->obtenerValor($consulta);
		$consulta="SELECT txtNivelEstudio FROM _401_tablaDinamica WHERE id__401_tablaDinamica=".$idNivel;
		$nNivel=$con->obtenerValor($consulta);
		$nNivel=str_replace(" ","_",$nNivel);
		$nomModalidad="";
		if($fContrato[9]=="2")
			$nomModalidad="Escolarizado";
		else
			$nomModalidad="NoEscolarizado";
		$nombreArchivo=$fContrato[6]."_".$nombreProfesor."_".$nNivel."_".$nomModalidad.".doc";
		
		$fFirmaContrato=$fInicio;
		if($fContrato[13]!="")
		{
			$oContrato=unserialize($fContrato[13]);

			$fFirmaContrato=$oContrato->fechaContrato;
			if((strtotime($fFirmaContrato)>strtotime($fInicio))||(date("Y",strtotime($fFirmaContrato))=='1969'))
			{
				$fFirmaContrato=$fInicio;
			}
		}

		$fFirmaContrato=obtenerDiaHabilAnterior($fFirmaContrato,$fContrato[12]);
		$obj->idProfesor=$fContrato[1];
		$obj->profesor=$nombreProfesorContrato;
		$obj->rector=$rector;
		$obj->costoHora=$costoHora;
		$obj->modalidad=$fContrato[9];
		$obj->plantel=$fContrato[12];
		$obj->fechaInicioContrato=$fLetraIni;
		$obj->fInicioC=$fInicio;
		$obj->fechaFinContrato=$fLetraFin;
		$obj->fFinC=$fTermino;
		$obj->fechaContrato=$fFirmaContrato;
		$obj->numeroContrato=$fContrato[6];
		$obj->idCiclo=$fContrato[10];
		$obj->idPeriodo=$fContrato[11];
		$obj->nombreArchivo=$nombreArchivo;
		$obj->arrMaterias=$arrMaterias;
		return $obj;
	}
	
	function normalizarDiferenciaContratos($objDatosBase,$objDatosNuevo)
	{
		global $con;
		$mat=null;
		$ignorarActivo=false;
		for($ct=0;$ct<sizeof($objDatosNuevo->arrMaterias);$ct++)
		{
			$mat=$objDatosNuevo->arrMaterias[$ct];
			if(strtotime($mat->fechaInicio)>strtotime($mat->fechaFin))				
			{
				$ignorarActivo=true;
				foreach($objDatosBase->arrMaterias as $m)
				{
					if(($m->idGrupo==$mat->idGrupo))
					{
						$objDatosNuevo->arrMaterias[$ct]->fechaInicio=$m->fechaInicio;
						$objDatosNuevo->arrMaterias[$ct]->fechaFin=$m->fechaFin;
						$objDatosNuevo->arrMaterias[$ct]->costoHora=$m->costoHora;
						$objDatosNuevo->arrMaterias[$ct]->horasMateria=$m->horasMateria;
						
						break;
					}	
				}	
			}
		}
		if($ignorarActivo&&(sizeof($objDatosNuevo->arrMaterias)==1))
		{
			$objDatosNuevo=$objDatosBase;
			
		}
		return $objDatosNuevo;
	}
	
	
	function crearContrato($obj,$guardarContrato=true)	
	{	
		global $con;
		global $arrMesLetra;
		global $query;
		global $x;
		
		$fechaCreacion=date("Y-m-d");
		$cadDocumento='<html xmlns:v="urn:schemas-microsoft-com:vml"
						xmlns:o="urn:schemas-microsoft-com:office:office"
						xmlns:w="urn:schemas-microsoft-com:office:word"
						xmlns:m="http://schemas.microsoft.com/office/2004/12/omml"
						xmlns="http://www.w3.org/TR/REC-html40">
						
						<head>
						<meta http-equiv=Content-Type content="text/html; charset=utf-8">
						<meta name=ProgId content=Word.Document>
						<meta name=Generator content="Microsoft Word 14">
						<meta name=Originator content="Microsoft Word 14">
						
						<title>Contrato Privado de Honorarios Asimilables A Sueldos por la Prestación
						de Servicios Profesionales Independientes por Obra Dete</title>
						<!--[if gte mso 9]><xml>
						 <o:DocumentProperties>
						  <o:Author>SISTEMAS</o:Author>
						  <o:LastAuthor>Marco</o:LastAuthor>
						  <o:Revision>2</o:Revision>
						  <o:TotalTime>56</o:TotalTime>
						  <o:Created>2012-01-10T23:23:00Z</o:Created>
						  <o:LastSaved>2012-01-10T23:23:00Z</o:LastSaved>
						  <o:Pages>2</o:Pages>
						  <o:Words>1424</o:Words>
						  <o:Characters>7833</o:Characters>
						  <o:Company>U.G.M.</o:Company>
						  <o:Lines>65</o:Lines>
						  <o:Paragraphs>18</o:Paragraphs>
						  <o:CharactersWithSpaces>9239</o:CharactersWithSpaces>
						  <o:Version>14.00</o:Version>
						 </o:DocumentProperties>
						 <o:OfficeDocumentSettings>
						  <o:TargetScreenSize>800x600</o:TargetScreenSize>
						 </o:OfficeDocumentSettings>
						</xml><![endif]-->
						
						<!--[if gte mso 9]><xml>
						 <w:WordDocument>
						  <w:GrammarState>Clean</w:GrammarState>
						  <w:TrackMoves>false</w:TrackMoves>
						  <w:TrackFormatting/>
						  <w:HyphenationZone>21</w:HyphenationZone>
						  <w:ValidateAgainstSchemas/>
						  <w:SaveIfXMLInvalid>false</w:SaveIfXMLInvalid>
						  <w:IgnoreMixedContent>false</w:IgnoreMixedContent>
						  <w:AlwaysShowPlaceholderText>false</w:AlwaysShowPlaceholderText>
						  <w:DoNotPromoteQF/>
						  <w:LidThemeOther>ES-MX</w:LidThemeOther>
						  <w:LidThemeAsian>X-NONE</w:LidThemeAsian>
						  <w:LidThemeComplexScript>X-NONE</w:LidThemeComplexScript>
						  <w:Compatibility>
						   <w:BreakWrappedTables/>
						   <w:SnapToGridInCell/>
						   <w:WrapTextWithPunct/>
						   <w:UseAsianBreakRules/>
						   <w:UseWord2002TableStyleRules/>
						   <w:DontUseIndentAsNumberingTabStop/>
						   <w:FELineBreak11/>
						   <w:WW11IndentRules/>
						   <w:DontAutofitConstrainedTables/>
						   <w:AutofitLikeWW11/>
						   <w:HangulWidthLikeWW11/>
						   <w:UseNormalStyleForList/>
						   <w:DontVertAlignCellWithSp/>
						   <w:DontBreakConstrainedForcedTables/>
						   <w:DontVertAlignInTxbx/>
						   <w:Word11KerningPairs/>
						   <w:CachedColBalance/>
						  </w:Compatibility>
						  <w:BrowserLevel>MicrosoftInternetExplorer4</w:BrowserLevel>
						  <m:mathPr>
						   <m:mathFont m:val="Cambria Math"/>
						   <m:brkBin m:val="before"/>
						   <m:brkBinSub m:val="&#45;-"/>
						   <m:smallFrac m:val="off"/>
						   <m:dispDef/>
						   <m:lMargin m:val="0"/>
						   <m:rMargin m:val="0"/>
						   <m:defJc m:val="centerGroup"/>
						   <m:wrapIndent m:val="1440"/>
						   <m:intLim m:val="subSup"/>
						   <m:naryLim m:val="undOvr"/>
						  </m:mathPr></w:WordDocument>
						</xml><![endif]--><!--[if gte mso 9]><xml>
						 <w:LatentStyles DefLockedState="false" DefUnhideWhenUsed="true"
						  DefSemiHidden="true" DefQFormat="false" DefPriority="99"
						  LatentStyleCount="267">
						  <w:LsdException Locked="false" Priority="0" SemiHidden="false"
						   UnhideWhenUsed="false" QFormat="true" Name="Normal"/>
						  <w:LsdException Locked="false" Priority="9" SemiHidden="false"
						   UnhideWhenUsed="false" QFormat="true" Name="heading 1"/>
						  <w:LsdException Locked="false" Priority="9" QFormat="true" Name="heading 2"/>
						  <w:LsdException Locked="false" Priority="9" QFormat="true" Name="heading 3"/>
						  <w:LsdException Locked="false" Priority="9" QFormat="true" Name="heading 4"/>
						  <w:LsdException Locked="false" Priority="9" QFormat="true" Name="heading 5"/>
						  <w:LsdException Locked="false" Priority="9" QFormat="true" Name="heading 6"/>
						  <w:LsdException Locked="false" Priority="9" QFormat="true" Name="heading 7"/>
						  <w:LsdException Locked="false" Priority="9" QFormat="true" Name="heading 8"/>
						  <w:LsdException Locked="false" Priority="9" QFormat="true" Name="heading 9"/>
						  <w:LsdException Locked="false" Priority="39" Name="toc 1"/>
						  <w:LsdException Locked="false" Priority="39" Name="toc 2"/>
						  <w:LsdException Locked="false" Priority="39" Name="toc 3"/>
						  <w:LsdException Locked="false" Priority="39" Name="toc 4"/>
						  <w:LsdException Locked="false" Priority="39" Name="toc 5"/>
						  <w:LsdException Locked="false" Priority="39" Name="toc 6"/>
						  <w:LsdException Locked="false" Priority="39" Name="toc 7"/>
						  <w:LsdException Locked="false" Priority="39" Name="toc 8"/>
						  <w:LsdException Locked="false" Priority="39" Name="toc 9"/>
						  <w:LsdException Locked="false" Priority="35" QFormat="true" Name="caption"/>
						  <w:LsdException Locked="false" Priority="10" SemiHidden="false"
						   UnhideWhenUsed="false" QFormat="true" Name="Title"/>
						  <w:LsdException Locked="false" Priority="0" Name="Default Paragraph Font"/>
						  <w:LsdException Locked="false" Priority="11" SemiHidden="false"
						   UnhideWhenUsed="false" QFormat="true" Name="Subtitle"/>
						  <w:LsdException Locked="false" Priority="22" SemiHidden="false"
						   UnhideWhenUsed="false" QFormat="true" Name="Strong"/>
						  <w:LsdException Locked="false" Priority="20" SemiHidden="false"
						   UnhideWhenUsed="false" QFormat="true" Name="Emphasis"/>
						  <w:LsdException Locked="false" Priority="0" Name="No List"/>
						  <w:LsdException Locked="false" Priority="59" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Table Grid"/>
						  <w:LsdException Locked="false" UnhideWhenUsed="false" Name="Placeholder Text"/>
						  <w:LsdException Locked="false" Priority="1" SemiHidden="false"
						   UnhideWhenUsed="false" QFormat="true" Name="No Spacing"/>
						  <w:LsdException Locked="false" Priority="60" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Light Shading"/>
						  <w:LsdException Locked="false" Priority="61" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Light List"/>
						  <w:LsdException Locked="false" Priority="62" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Light Grid"/>
						  <w:LsdException Locked="false" Priority="63" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Medium Shading 1"/>
						  <w:LsdException Locked="false" Priority="64" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Medium Shading 2"/>
						  <w:LsdException Locked="false" Priority="65" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Medium List 1"/>
						  <w:LsdException Locked="false" Priority="66" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Medium List 2"/>
						  <w:LsdException Locked="false" Priority="67" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Medium Grid 1"/>
						  <w:LsdException Locked="false" Priority="68" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Medium Grid 2"/>
						  <w:LsdException Locked="false" Priority="69" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Medium Grid 3"/>
						  <w:LsdException Locked="false" Priority="70" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Dark List"/>
						  <w:LsdException Locked="false" Priority="71" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Colorful Shading"/>
						  <w:LsdException Locked="false" Priority="72" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Colorful List"/>
						  <w:LsdException Locked="false" Priority="73" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Colorful Grid"/>
						  <w:LsdException Locked="false" Priority="60" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Light Shading Accent 1"/>
						  <w:LsdException Locked="false" Priority="61" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Light List Accent 1"/>
						  <w:LsdException Locked="false" Priority="62" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Light Grid Accent 1"/>
						  <w:LsdException Locked="false" Priority="63" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Medium Shading 1 Accent 1"/>
						  <w:LsdException Locked="false" Priority="64" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Medium Shading 2 Accent 1"/>
						  <w:LsdException Locked="false" Priority="65" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Medium List 1 Accent 1"/>
						  <w:LsdException Locked="false" UnhideWhenUsed="false" Name="Revision"/>
						  <w:LsdException Locked="false" Priority="34" SemiHidden="false"
						   UnhideWhenUsed="false" QFormat="true" Name="List Paragraph"/>
						  <w:LsdException Locked="false" Priority="29" SemiHidden="false"
						   UnhideWhenUsed="false" QFormat="true" Name="Quote"/>
						  <w:LsdException Locked="false" Priority="30" SemiHidden="false"
						   UnhideWhenUsed="false" QFormat="true" Name="Intense Quote"/>
						  <w:LsdException Locked="false" Priority="66" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Medium List 2 Accent 1"/>
						  <w:LsdException Locked="false" Priority="67" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Medium Grid 1 Accent 1"/>
						  <w:LsdException Locked="false" Priority="68" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Medium Grid 2 Accent 1"/>
						  <w:LsdException Locked="false" Priority="69" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Medium Grid 3 Accent 1"/>
						  <w:LsdException Locked="false" Priority="70" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Dark List Accent 1"/>
						  <w:LsdException Locked="false" Priority="71" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Colorful Shading Accent 1"/>
						  <w:LsdException Locked="false" Priority="72" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Colorful List Accent 1"/>
						  <w:LsdException Locked="false" Priority="73" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Colorful Grid Accent 1"/>
						  <w:LsdException Locked="false" Priority="60" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Light Shading Accent 2"/>
						  <w:LsdException Locked="false" Priority="61" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Light List Accent 2"/>
						  <w:LsdException Locked="false" Priority="62" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Light Grid Accent 2"/>
						  <w:LsdException Locked="false" Priority="63" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Medium Shading 1 Accent 2"/>
						  <w:LsdException Locked="false" Priority="64" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Medium Shading 2 Accent 2"/>
						  <w:LsdException Locked="false" Priority="65" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Medium List 1 Accent 2"/>
						  <w:LsdException Locked="false" Priority="66" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Medium List 2 Accent 2"/>
						  <w:LsdException Locked="false" Priority="67" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Medium Grid 1 Accent 2"/>
						  <w:LsdException Locked="false" Priority="68" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Medium Grid 2 Accent 2"/>
						  <w:LsdException Locked="false" Priority="69" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Medium Grid 3 Accent 2"/>
						  <w:LsdException Locked="false" Priority="70" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Dark List Accent 2"/>
						  <w:LsdException Locked="false" Priority="71" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Colorful Shading Accent 2"/>
						  <w:LsdException Locked="false" Priority="72" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Colorful List Accent 2"/>
						  <w:LsdException Locked="false" Priority="73" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Colorful Grid Accent 2"/>
						  <w:LsdException Locked="false" Priority="60" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Light Shading Accent 3"/>
						  <w:LsdException Locked="false" Priority="61" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Light List Accent 3"/>
						  <w:LsdException Locked="false" Priority="62" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Light Grid Accent 3"/>
						  <w:LsdException Locked="false" Priority="63" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Medium Shading 1 Accent 3"/>
						  <w:LsdException Locked="false" Priority="64" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Medium Shading 2 Accent 3"/>
						  <w:LsdException Locked="false" Priority="65" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Medium List 1 Accent 3"/>
						  <w:LsdException Locked="false" Priority="66" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Medium List 2 Accent 3"/>
						  <w:LsdException Locked="false" Priority="67" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Medium Grid 1 Accent 3"/>
						  <w:LsdException Locked="false" Priority="68" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Medium Grid 2 Accent 3"/>
						  <w:LsdException Locked="false" Priority="69" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Medium Grid 3 Accent 3"/>
						  <w:LsdException Locked="false" Priority="70" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Dark List Accent 3"/>
						  <w:LsdException Locked="false" Priority="71" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Colorful Shading Accent 3"/>
						  <w:LsdException Locked="false" Priority="72" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Colorful List Accent 3"/>
						  <w:LsdException Locked="false" Priority="73" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Colorful Grid Accent 3"/>
						  <w:LsdException Locked="false" Priority="60" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Light Shading Accent 4"/>
						  <w:LsdException Locked="false" Priority="61" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Light List Accent 4"/>
						  <w:LsdException Locked="false" Priority="62" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Light Grid Accent 4"/>
						  <w:LsdException Locked="false" Priority="63" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Medium Shading 1 Accent 4"/>
						  <w:LsdException Locked="false" Priority="64" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Medium Shading 2 Accent 4"/>
						  <w:LsdException Locked="false" Priority="65" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Medium List 1 Accent 4"/>
						  <w:LsdException Locked="false" Priority="66" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Medium List 2 Accent 4"/>
						  <w:LsdException Locked="false" Priority="67" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Medium Grid 1 Accent 4"/>
						  <w:LsdException Locked="false" Priority="68" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Medium Grid 2 Accent 4"/>
						  <w:LsdException Locked="false" Priority="69" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Medium Grid 3 Accent 4"/>
						  <w:LsdException Locked="false" Priority="70" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Dark List Accent 4"/>
						  <w:LsdException Locked="false" Priority="71" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Colorful Shading Accent 4"/>
						  <w:LsdException Locked="false" Priority="72" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Colorful List Accent 4"/>
						  <w:LsdException Locked="false" Priority="73" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Colorful Grid Accent 4"/>
						  <w:LsdException Locked="false" Priority="60" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Light Shading Accent 5"/>
						  <w:LsdException Locked="false" Priority="61" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Light List Accent 5"/>
						  <w:LsdException Locked="false" Priority="62" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Light Grid Accent 5"/>
						  <w:LsdException Locked="false" Priority="63" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Medium Shading 1 Accent 5"/>
						  <w:LsdException Locked="false" Priority="64" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Medium Shading 2 Accent 5"/>
						  <w:LsdException Locked="false" Priority="65" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Medium List 1 Accent 5"/>
						  <w:LsdException Locked="false" Priority="66" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Medium List 2 Accent 5"/>
						  <w:LsdException Locked="false" Priority="67" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Medium Grid 1 Accent 5"/>
						  <w:LsdException Locked="false" Priority="68" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Medium Grid 2 Accent 5"/>
						  <w:LsdException Locked="false" Priority="69" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Medium Grid 3 Accent 5"/>
						  <w:LsdException Locked="false" Priority="70" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Dark List Accent 5"/>
						  <w:LsdException Locked="false" Priority="71" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Colorful Shading Accent 5"/>
						  <w:LsdException Locked="false" Priority="72" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Colorful List Accent 5"/>
						  <w:LsdException Locked="false" Priority="73" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Colorful Grid Accent 5"/>
						  <w:LsdException Locked="false" Priority="60" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Light Shading Accent 6"/>
						  <w:LsdException Locked="false" Priority="61" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Light List Accent 6"/>
						  <w:LsdException Locked="false" Priority="62" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Light Grid Accent 6"/>
						  <w:LsdException Locked="false" Priority="63" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Medium Shading 1 Accent 6"/>
						  <w:LsdException Locked="false" Priority="64" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Medium Shading 2 Accent 6"/>
						  <w:LsdException Locked="false" Priority="65" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Medium List 1 Accent 6"/>
						  <w:LsdException Locked="false" Priority="66" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Medium List 2 Accent 6"/>
						  <w:LsdException Locked="false" Priority="67" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Medium Grid 1 Accent 6"/>
						  <w:LsdException Locked="false" Priority="68" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Medium Grid 2 Accent 6"/>
						  <w:LsdException Locked="false" Priority="69" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Medium Grid 3 Accent 6"/>
						  <w:LsdException Locked="false" Priority="70" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Dark List Accent 6"/>
						  <w:LsdException Locked="false" Priority="71" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Colorful Shading Accent 6"/>
						  <w:LsdException Locked="false" Priority="72" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Colorful List Accent 6"/>
						  <w:LsdException Locked="false" Priority="73" SemiHidden="false"
						   UnhideWhenUsed="false" Name="Colorful Grid Accent 6"/>
						  <w:LsdException Locked="false" Priority="19" SemiHidden="false"
						   UnhideWhenUsed="false" QFormat="true" Name="Subtle Emphasis"/>
						  <w:LsdException Locked="false" Priority="21" SemiHidden="false"
						   UnhideWhenUsed="false" QFormat="true" Name="Intense Emphasis"/>
						  <w:LsdException Locked="false" Priority="31" SemiHidden="false"
						   UnhideWhenUsed="false" QFormat="true" Name="Subtle Reference"/>
						  <w:LsdException Locked="false" Priority="32" SemiHidden="false"
						   UnhideWhenUsed="false" QFormat="true" Name="Intense Reference"/>
						  <w:LsdException Locked="false" Priority="33" SemiHidden="false"
						   UnhideWhenUsed="false" QFormat="true" Name="Book Title"/>
						  <w:LsdException Locked="false" Priority="37" Name="Bibliography"/>
						  <w:LsdException Locked="false" Priority="39" QFormat="true" Name="TOC Heading"/>
						 </w:LatentStyles>
						</xml><![endif]-->
						<style>
						<!--
						 /* Style Definitions */
						
						 
						 p.MsoNormal, li.MsoNormal, div.MsoNormal
							{mso-style-unhide:no;
							mso-style-qformat:yes;
							mso-style-parent:"";
							margin:0cm;
							margin-bottom:.0001pt;
							mso-pagination:widow-orphan;
							font-size:10.0pt !important;
							font-family:"Times New Roman","serif";
							mso-fareast-font-family:"Times New Roman";
							mso-ansi-language:ES;
							mso-fareast-language:ES;}
						p.MsoBodyTextIndent, li.MsoBodyTextIndent, div.MsoBodyTextIndent
							{mso-style-unhide:no;
							margin:0cm;
							margin-bottom:.0001pt;
							mso-pagination:widow-orphan;
							font-size:6.5pt;
							font-family:"Times New Roman","serif";
							mso-fareast-font-family:"Times New Roman";
							letter-spacing:-.4pt;font-size:10pt;;
							mso-ansi-language:ES;
							mso-fareast-language:ES;}
						span.GramE
							{mso-style-name:"";
							mso-gram-e:yes;}
						@page WordSection1
							{size:612.0pt 792.0pt;
							margin:70.85pt 3.0cm 70.85pt 3.0cm;
							mso-header-margin:35.45pt;
							mso-footer-margin:35.45pt;
							mso-paper-source:0;}
						div.WordSection1
							{page:WordSection1;}
						span.GramE1 {mso-style-name:"";
							mso-gram-e:yes;}
						span.GramE2 {mso-style-name:"";
							mso-gram-e:yes;}
						-->
						</style>
						<!--[if gte mso 10]>
						<style>
						 /* Style Definitions */
						 table.MsoNormalTable
							{mso-style-name:"Tabla normal";
							mso-tstyle-rowband-size:0;
							mso-tstyle-colband-size:0;
							mso-style-noshow:yes;
							mso-style-unhide:no;
							mso-style-parent:"";
							mso-padding-alt:0cm 5.4pt 0cm 5.4pt;
							mso-para-margin:0cm;
							mso-para-margin-bottom:.0001pt;
							mso-pagination:widow-orphan;
							font-size:10.0pt;
							font-family:"Times New Roman","serif";}
						table.MsoTableGrid
							{mso-style-name:"Tabla con cuadrícula";
							mso-tstyle-rowband-size:0;
							mso-tstyle-colband-size:0;
							mso-style-unhide:no;
							border:solid windowtext 1.0pt;
							mso-border-alt:solid windowtext .5pt;
							mso-padding-alt:0cm 5.4pt 0cm 5.4pt;
							mso-border-insideh:.5pt solid windowtext;
							mso-border-insidev:.5pt solid windowtext;
							mso-para-margin:0cm;
							mso-para-margin-bottom:.0001pt;
							mso-pagination:widow-orphan;
							font-size:10.0pt;
							font-family:"Times New Roman","serif";}
						 span
						 {
							 font-size: 10pt !important;
						 }
						 
							 p.MsoNormal1, li.MsoNormal1, div.MsoNormal1
								{margin:0cm;
								margin-bottom:.0001pt;
								font-size:12.0pt;
								font-family:"Times New Roman","serif";}
							h1.docente
								{margin:0cm;
								margin-bottom:.0001pt;
								text-align:center;
								page-break-after:avoid;
								font-size:12.0pt;
								font-family:"Times New Roman","serif";}
							p.MsoBodyText1, li.MsoBodyText1, div.MsoBodyText1
								{margin:0cm;
								margin-bottom:.0001pt;
								text-align:justify;
								font-size:12.0pt;
								font-family:"Times New Roman","serif";}
							@page WordSection11
								{size:612.1pt 1008.15pt;
								margin:70.9pt 3.0cm 70.9pt 3.0cm;}
							div.WordSection11
								{page:WordSection11;}
						 
						</style>
						<![endif]--><!--[if gte mso 9]><xml>
						 <o:shapedefaults v:ext="edit" spidmax="1026"/>
						</xml><![endif]--><!--[if gte mso 9]><xml>
						 <o:shapelayout v:ext="edit">
						  <o:idmap v:ext="edit" data="1"/>
						 </o:shapelayout></xml><![endif]-->
						</head>';
		$idUsuario=$obj->idProfesor;
		$idProfesor=$idUsuario;
		$profesor=($obj->profesor);
		$rector=$obj->rector;
		$modalidad=$obj->modalidad;		
		$fechaInicioContrato=$obj->fechaInicioContrato;
		$fInicioContrato=$obj->fInicioC;
		$fechaFinContrato=$obj->fechaFinContrato;
		$fFinC=$obj->fFinC;
		$fechaContrato=$obj->fechaContrato;
		$numeroContrato=$obj->numeroContrato;
		$sede=$obj->plantel;
		$plantel=$obj->plantel;
		$costoHora=$obj->costoHora;
		$idProfesor=$obj->idProfesor;
		$idCiclo=$obj->idCiclo;
		$idPeriodo=$obj->idPeriodo;
		
		$consulta="SELECT cedulaProf,RFC FROM 802_identifica WHERE idUsuario=".$idProfesor;
		$filaCed=$con->obtenerPrimeraFila($consulta);
		$cedulaProfesional=$filaCed[0];
		if($cedulaProfesional=="")
			$cedulaProfesional="______________";
		$cedulaFiscal=$filaCed[1];
		if($cedulaFiscal=="")
			$cedulaFiscal="__________";

		$consulta="SELECT GROUP_CONCAT(CONCAT('(',Lada,') ',Numero)) AS telefono FROM 804_telefonos WHERE idUsuario=".$idProfesor;
		
		$telefono=$con->obtenerValor($consulta);
		if($telefono=="")
			$telefono="_____________";
		//domicilio fiscal
	
		$consulta="select * from 803_direcciones where idUsuario=".$idProfesor." and Tipo=0";
		//echo $consulta;
		$filaDm=$con->obtenerPrimeraFila($consulta);
		$domicilio=$filaDm[2];
		if($domicilio=="")
			$domicilio="_________________";
		$colonia=$filaDm[4];
		if($colonia=="")
			$colonia="_________________";
		$cp=$filaDm[6];
		if($cp=="")
			$cp="_____";
		$consulta="SELECT municipio FROM 821_municipios WHERE cveMunicipio='".$filaDm[5]."'";
		$delegacion=$con->obtenerValor($consulta);
		if($delegacion=="")
			$delegacion=$filaDm[10];
		if($delegacion=="")
			$delegacion="____________";
			
		$consulta="SELECT estado FROM 820_estados WHERE cveEstado='".$filaDm[7]."'";
		$estado=$con->obtenerValor($consulta);
		if($estado=="")
			$estado=$filaDm[7];
		if($estado=="")
			$estado="____________________";
		$domicioFiscal=($domicilio).", Col. ".($colonia).", Ciudad. ".($delegacion).", C.P. ".$cp.", ".($estado);
		//Materias
		$consulta="SELECT municipio,estado FROM 247_instituciones i,817_organigrama o WHERE i.idOrganigrama=o.idOrganigrama AND o.codigoUnidad='".$obj->plantel."'";
		$ciudadO=$con->obtenerPrimeraFila($consulta);
		$consulta="SELECT municipio FROM 821_municipios WHERE cveMunicipio='".$ciudadO[0]."'";
		$ciudad=$con->obtenerValor($consulta);
		if($ciudad=="")
			$ciudad=$ciudadO;
		if($ciudad=="")
			$ciudad="________________";
			
		$consulta="SELECT estado FROM 820_estados WHERE cveEstado='".$ciudadO[1]."'";
		$estado=$con->obtenerValor($consulta);
		if($estado=="")
			$estado=$estado;
		if($estado=="")
			$estado="________________";
			
		$sedeContrato=$ciudad.", ".$estado;
		if($sede=='00010011')
		{
			$nombreSede="INSTITUTO PLUVIOSILLA S.C.";
			$rfc="IPL850521K34";
		}
		else
		{
			$nombreSede="UGMSUR S.C.";
			$rfc="UGM0707139H5";
		}
	
    	$cadDocumento.="
			<body lang=ES-MX style='tab-interval:35.4pt'>
			<div class=WordSection1>
			
			<p class=MsoBodyTextIndent style='text-align:justify'><span lang=ES
			style='font-size:10.0pt'>Contrato Privado de <b style='mso-bidi-font-weight:
			normal'>Honorarios Asimilables A Sueldos </b>por la Prestación de Servicios
			Profesionales Independientes por Obra Determinada de carácter<span
			style='mso-spacerun:yes'> </span><b style='mso-bidi-font-weight:normal'>DOCENTE<span
			style='mso-spacerun:yes'> </span></b>que celebran<span
			style='mso-spacerun:yes'> </span>por<span style='mso-spacerun:yes'> 
			</span>una parte<span style='mso-spacerun:yes'> </span>el <b style='mso-bidi-font-weight:
			normal'>\"".$nombreSede."\", </b>representada en este acto por el C. ".strtoupper( ($rector))." quien<span style='mso-spacerun:yes'>  </span>para<span
			style='mso-spacerun:yes'> </span>efectos<span style='mso-spacerun:yes'> 
			</span>del<span style='mso-spacerun:yes'> </span>presente<span
			style='mso-spacerun:yes'> </span>se<span style='mso-spacerun:yes'> </span>le
			denominará<span style='mso-spacerun:yes'> </span>\"LA UNIVERSIDAD\"<span
			style='mso-spacerun:yes'> </span>y<span style='mso-spacerun:yes'>
			</span>por<span style='mso-spacerun:yes'> </span>la<span
			style='mso-spacerun:yes'> </span>otra<span style='mso-spacerun:yes'>
			</span>él<span style='mso-spacerun:yes'> </span>(la)<span
			style='mso-spacerun:yes'> </span>C. ".( ($profesor))."<span style='mso-spacerun:yes'>
			</span>quien<span style='mso-spacerun:yes'> </span>para<span
			style='mso-spacerun:yes'> </span>efectos<span style='mso-spacerun:yes'>
			</span>del<span style='mso-spacerun:yes'> </span>presente<span
			style='mso-spacerun:yes'> </span>se<span style='mso-spacerun:yes'>
			</span>le<span style='mso-spacerun:yes'> </span>denominará \"EL<span
			style='mso-spacerun:yes'> </span>CATEDRÁTICO\"., mismo que se somete al<span
			style='mso-spacerun:yes'> </span>tenor de las siguientes declaraciones y
			cláusulas.
			<o:p></o:p></span></p>
			
			<p class=MsoNormal style='text-align:justify;'><span lang=ES style='letter-spacing:
			-.4pt;font-size:10pt;'><o:p>&nbsp;</o:p></span></p>
			<p class=MsoNormal style='text-align:justify;'><span lang=ES style='letter-spacing:
			-.4pt;font-size:10pt;'><o:p>&nbsp;</o:p></span></p>
			
			<p class=\"MsoNormal\" align=center style='text-align:center'><b><span lang=ES
			style='letter-spacing:-.4pt;font-size:10pt;'>D E C L A R A C I<span style='mso-spacerun:yes'> 
			</span>O N E S<o:p></o:p></span></b></p>
			<p class=MsoNormal style='text-align:justify;'><span lang=ES style='letter-spacing:
			-.4pt;font-size:10pt;'><o:p>&nbsp;</o:p></span></p>
			
			<p class=MsoNormal style='text-align:justify'><b><span lang=ES
			style='letter-spacing:-.4pt;font-size:10pt;'>PRIMERA.-</span></b><span lang=ES
			style='letter-spacing:-.4pt;font-size:10pt;'> La<span style='mso-spacerun:yes'> 
			</span>Universidad declara que se dedica exclusivamente al ramo de la Educación
			y que es una Sociedad Civil, sin fines de lucro.<o:p></o:p></span></p>
						<p class=MsoNormal style='text-align:justify;'><span lang=ES style='letter-spacing:
			-.4pt;font-size:10pt;'><o:p>&nbsp;</o:p></span></p>
			
			<p class=MsoNormal style='text-align:justify'><b><span lang=ES
			style='letter-spacing:-.4pt;font-size:10pt;'>SEGUNDA.-</span></b><span lang=ES
			style='letter-spacing:-.4pt;font-size:10pt;'> La Universidad<span style='mso-spacerun:yes'> 
			</span>declara estar<span style='mso-spacerun:yes'>  </span>debidamente
			registrada ante<span style='mso-spacerun:yes'>  </span>la Secretaria<span
			style='mso-spacerun:yes'>  </span>de Hacienda Y Crédito Publico con<span
			style='mso-spacerun:yes'>  </span>el<span style='mso-spacerun:yes'> 
			</span>Numero <b style='mso-bidi-font-weight:normal'>".$rfc."</b>.<o:p></o:p></span></p>
			<p class=MsoNormal style='text-align:justify;'><span lang=ES style='letter-spacing:
			-.4pt;font-size:10pt;'><o:p>&nbsp;</o:p></span></p>	";
			
			if($modalidad==1)
			{
    			$cadDocumento.="
				<p class=MsoNormal style='text-align:justify'><b><span lang=ES
				style='letter-spacing:-.4pt;font-size:10pt;'>TERCERA.-</span></b><span lang=ES
				style='letter-spacing:-.4pt;font-size:10pt;'> El Catedrático declara estar registrado ante la
				Secretaría de Hacienda y Crédito Público con la actividad de Honorarios Asimilables a Sueldos y<span style='mso-spacerun:yes'>
				</span>con domicilio fiscal en ".strtoupper( ($domicioFiscal))." con
				cédula profesional No. ".strtoupper( ($cedulaProfesional))."</span><!--[if supportFields]><span lang=ES
				style='letter-spacing:-.4pt;font-size:10pt;'><span style='mso-element:field-begin'></span>
				MERGEFIELD N_CEDULA </span><![endif]--><!--[if supportFields]><span lang=ES
				style='letter-spacing:-.4pt;font-size:10pt;'><span style='mso-element:field-end'></span></span><![endif]--><span
				lang=ES style='letter-spacing:-.4pt;font-size:10pt;'><span style='mso-spacerun:yes'> </span><span
				class=GramE>y</span> con teléfono No. ".strtoupper( ($telefono))."
				<o:p></o:p></span></p>
				<p class=MsoNormal style='text-align:justify;'><span lang=ES style='letter-spacing:
				-.4pt;font-size:10pt;'><o:p>&nbsp;</o:p></span></p>";
			}
			else
			{
				$cadDocumento.="
				<p class=MsoNormal style='text-align:justify'><b><span lang=ES
				style='letter-spacing:-.4pt;font-size:10pt;'>TERCERA.-</span></b><span lang=ES
				style='letter-spacing:-.4pt;font-size:10pt;'> El Catedrático declara estar registrado ante la
				Secretaría de Hacienda y Crédito Público con el <span class=GramE>número </span>".strtoupper( ($cedulaFiscal))."
				con la actividad de Honorarios Asimilables a Sueldos<span
				style='mso-spacerun:yes'>  </span>(se anexa formato R-1 de alta ante Hacienda) y<span style='mso-spacerun:yes'>  </span>con domicilio fiscal en ".strtoupper( ($domicioFiscal))." con
				cédula profesional No. ".strtoupper( ($cedulaProfesional))."</span><!--[if supportFields]><span lang=ES
				style='letter-spacing:-.4pt;font-size:10pt;'><span style='mso-element:field-begin'></span>
				MERGEFIELD N_CEDULA </span><![endif]--><!--[if supportFields]><span lang=ES
				style='letter-spacing:-.4pt;font-size:10pt;'><span style='mso-element:field-end'></span></span><![endif]--><span
				lang=ES style='letter-spacing:-.4pt;font-size:10pt;'><span style='mso-spacerun:yes'> </span><span
				class=GramE>y</span> con teléfono No. ".strtoupper( ($telefono))."
				<o:p></o:p></span></p>
				<p class=MsoNormal style='text-align:justify;'><span lang=ES style='letter-spacing:
				-.4pt;font-size:10pt;'><o:p>&nbsp;</o:p></span></p>";
			}
			$cadDocumento.="
			<p class=MsoNormal style='text-align:justify'><b><span lang=ES
			style='letter-spacing:-.4pt;font-size:10pt;'>CUARTA.-</span></b><span lang=ES style='letter-spacing:
			-.4pt;font-size:10pt;'> El Catedrático declara tener la formación académica y los conocimientos
			<span class=GramE>técnico pedagógicos adecuado</span> para realizar la
			actividad especificada<span style='mso-spacerun:yes'>  </span>en este contrato.<o:p></o:p></span></p>
			<p class=MsoNormal style='text-align:justify;'><span lang=ES style='letter-spacing:
			-.4pt;font-size:10pt;'><o:p>&nbsp;</o:p></span></p>
			
			<p class=MsoNormal style='text-align:justify'><b><span lang=ES
			style='letter-spacing:-.4pt;font-size:10pt;'>QUINTA.- </span></b><span lang=ES
			style='letter-spacing:-.4pt;font-size:10pt;'>El Catedrático manifiesta que conoce las normas y
			reglamentos internos que rigen a la Universidad.<o:p></o:p></span></p>
			<p class=MsoNormal style='text-align:justify;'><span lang=ES style='letter-spacing:
			-.4pt;font-size:10pt;'><o:p>&nbsp;</o:p></span></p>
			
			<p class=MsoNormal style='text-align:justify'><span lang=ES style='letter-spacing:
			-.4pt;font-size:10pt;'>S<b>EXTA.- </b>Ambas partes se reconocen mutuamente la personalidad con
			la que se ostentan en el presente contrato.<b><o:p></o:p></b></span></p>
			<p class=MsoNormal style='text-align:justify;'><span lang=ES style='letter-spacing:
			-.4pt;font-size:10pt;'><o:p>&nbsp;</o:p></span></p>
			
			<p class=MsoNormal style='text-align:justify'><b><span lang=ES
			style='letter-spacing:-.4pt;font-size:10pt;'><o:p>&nbsp;</o:p></span></b></p>
			
			<p class=MsoNormal align=center style='text-align:center'><b><span lang=ES
			style='letter-spacing:-.4pt;font-size:10pt;'>C L A U S U L A S <o:p></o:p></span></b></p>
			<p class=MsoNormal style='text-align:justify'><b><span lang=ES
			style='letter-spacing:-.4pt;font-size:10pt;'><o:p>&nbsp;</o:p></span></b></p>
			
			<p class=MsoNormal style='text-align:justify'><b><span lang=ES
			style='letter-spacing:-.4pt;font-size:10pt;'>PRIMERA.-<span style='mso-spacerun:yes'>  </span></span></b><span
			lang=ES style='letter-spacing:-.4pt;font-size:10pt;'>Es voluntad del<span
			style='mso-spacerun:yes'>  </span>Catedrático aceptar<span
			style='mso-spacerun:yes'>  </span>prestar sus Servicios Profesionales
			Independientes por Obra determinada de carácter docente a la Universidad, por
			lo cual se compromete a apegarse estrictamente<span style='mso-spacerun:yes'> 
			</span>a las leyes<span style='mso-spacerun:yes'>  </span>y reglamentos
			internos bajo los que se rige<span style='mso-spacerun:yes'>  </span>".$nombreSede."
			<o:p></o:p></span></p>
			<p class=MsoNormal style='text-align:justify'><b><span lang=ES
			style='letter-spacing:-.4pt;font-size:10pt;'><o:p>&nbsp;</o:p></span></b></p>
			
			<p class=MsoNormal style='text-align:justify'><b><span lang=ES
			style='letter-spacing:-.4pt;font-size:10pt;'>SEGUNDA.-<span style='mso-spacerun:yes'>  </span></span></b><span
			lang=ES style='letter-spacing:-.4pt;font-size:10pt;'>El Catedrático se obliga a elaborar y
			aplicar los distintos instrumentos de evaluación, así como documentos e
			informes que le solicite la Universidad; y así mismo, brindar las asesorías
			para Titulación que le sean asignadas.<o:p></o:p></span></p>
			<p class=MsoNormal style='text-align:justify'><b><span lang=ES
			style='letter-spacing:-.4pt;font-size:10pt;'><o:p>&nbsp;</o:p></span></b></p>
			
			<p class=MsoNormal style='text-align:justify'><b><span lang=ES
			style='letter-spacing:-.4pt;font-size:10pt;'>TERCERA.-</span></b><span lang=ES
			style='letter-spacing:-.4pt;font-size:10pt;'> El Catedrático se obliga a impartir<span
			style='mso-spacerun:yes'>  </span>de manera semanal en la ciudad <span
			class=GramE>de<span style='mso-spacerun:yes'>  </span></span>".strtoupper( ($sedeContrato))." las siguientes
			asignaturas:<span style='color:#FF9900'> <o:p></o:p></span></span></p>
			<p class=MsoNormal style='text-align:justify'><b><span lang=ES
			style='letter-spacing:-.4pt;font-size:10pt;'><o:p>&nbsp;</o:p></span></b></p>
			
			
			<table border=1 cellspacing=0 cellpadding=0>
				<tr>
				  <td valign=top align='center' ><span lang=ES style='letter-spacing:-.4pt;font-size:10pt;'><b>Materia</b></span></td>
				  <td valign=top align='center' ><span lang=ES style='letter-spacing:-.4pt;font-size:10pt;'><b>Grupo</b></span></td>
				  <td valign=top align='center'><span lang=ES style='letter-spacing:-.4pt;font-size:10pt;'><b>Horas por impartir</b></span></td>
				  <td valign=top align='center'><span lang=ES style='letter-spacing:-.4pt;font-size:10pt;'><b>Fecha Termino de Materia</b></span></td>
				</tr>";
				$montoNivel=number_format($costoHora,2);
				$letraMontoNivel=convertirNumeroLetra($montoNivel);
			
			$datosContrato=serialize($obj);

			if($guardarContrato)	
			{
				$query[$x]="INSERT INTO 4553_contratosProfesores(idProfesor,fechaCreacion,fechaInicioContrato,fechaFinContrato,folioContrato,
									modalidadPlanEstudio,idCiclo,idPeriodo,plantel,datosContrato,nombreArchivo)VALUES('".$idProfesor."','".$fechaCreacion."','".$fInicioContrato."','".$fFinC."',
									'".$numeroContrato."','".$modalidad."','".$idCiclo."','".$idPeriodo."','".$plantel."','".$datosContrato."','".cv($obj->nombreArchivo)."')";
				$x++;
					
				$query[$x]="set @idContrato:=(select last_insert_id())";
				$x++;
			}
			$fContratoTmp=strtotime($fechaContrato);
			$diaContrato=date("d",$fContratoTmp);
			$mesContrato=date("m",$fContratoTmp);
			$lMesContrato=$arrMesLetra[$mesContrato-1];
			$anioContrato=date("Y",$fContratoTmp);
			$fechaContratoFirmaDia="";
			if($diaContrato==1)
			{
				$fechaContratoFirmaDia=" al día ".$diaContrato." del mes de ".$lMesContrato." del año ".$anioContrato;
			}
			else
			{
				$fechaContratoFirmaDia=" a los ".$diaContrato." días del mes de ".$lMesContrato." del año ".$anioContrato;
			}
			
			
			foreach($obj->arrMaterias as $m)
			{
			   $fMat[0]=$m->idMateria;
			   $fMat[1]=$m->idGrupo;
			   $fMat[2]=$m->nombreMateria;
			   $fMat[3]=$m->horasMateria;
			   $fMat[4]=$m->fechaInicio;
			   $fMat[5]=$m->fechaFin;
			   $fMat[6]=$m->costoHora;
			   $fMat[7]=$m->grupo;
			   if($guardarContrato)
			   {
			  		$query[$x]="UPDATE  4519_asignacionProfesorGrupo SET esperaContrato='0',idContrato=@idContrato where idUsuario='".$idProfesor."' 
							  AND idGrupo='".$fMat[1]."' AND situacion<>'4'";	
			  		$x++;
			   }
			  $cadDocumento.="
							  <tr>
								<td width=479 valign=top align='left'><span style='letter-spacing:-.4pt;font-size:10pt;'>".($fMat[2])."</span></td>
								<td width=479 valign=top align='left'><span style='letter-spacing:-.4pt;font-size:10pt;'>".($fMat[7])."</span></td>
								<td width=245 valign=top align='center'><span style='letter-spacing:-.4pt;font-size:10pt;'>".number_format($fMat[3])."</span></td>
								<td width=245 valign=top align='center'><span style='letter-spacing:-.4pt;font-size:10pt;'>".cambiarFormatoFecha($fMat[5],"-","-",true)."</span></td>
							  </tr>";
			}
				 
			$cadDocumento.="
			</table>
			<p class=MsoNormal style='text-align:justify'><b><span lang=ES
			style='letter-spacing:-.4pt;font-size:10pt;'><o:p>&nbsp;</o:p></span></b></p>
			
			<p class=MsoNormal style='text-align:justify'><b><span lang=ES
			style='letter-spacing:-.4pt;font-size:10pt;'>CUARTA.- </span></b><span lang=ES
			style='letter-spacing:-.4pt;font-size:10pt;'>La Universidad<span style='mso-spacerun:yes'> 
			</span>se obliga al pagar al Catedrático por concepto de Servicios
			Profesionales de carácter docente, es decir, por <b style='mso-bidi-font-weight:
			normal'>Impartir Su Cátedra Frente A Grupo </b>la cantidad de <b>$ </b></span><!--[if supportFields]><b><span
			lang=ES style='letter-spacing:-.4pt;font-size:10pt;'><span style='mso-element:field-begin'></span><span
			style='mso-spacerun:yes'> </span>MERGEFIELD COSTO <span style='mso-element:
			field-separator'></span></span></b><![endif]--><b><span style='letter-spacing:-.4pt;font-size:10pt;'>".strtoupper(($montoNivel))."</span><span lang=ES
			style='letter-spacing:-.4pt;font-size:10pt;'> ( </span><span style=\"letter-spacing:-.4pt;font-size:10pt;\">".strtoupper(($letraMontoNivel))."</span><span lang=ES
			style='letter-spacing:-.4pt;font-size:10pt;'><span style='mso-no-proof:yes'>
			00/100 M.N)</span></span></b><!--[if supportFields]><b><span lang=ES
			style='letter-spacing:-.4pt;font-size:10pt;'><span style='mso-element:field-end'></span></span></b><![endif]--><b><span
			lang=ES style='letter-spacing:-.4pt;font-size:10pt;'><span style='mso-spacerun:yes'>   </span></span></b><span
			lang=ES style='letter-spacing:-.4pt;font-size:10pt;'>por <b style='mso-bidi-font-weight:normal'><u>Hora
			De Cátedra Impartida</u></b>.<o:p></o:p></span></p>
			<p class=MsoNormal style='text-align:justify'><b><span lang=ES
			style='letter-spacing:-.4pt;font-size:10pt;'><o:p>&nbsp;</o:p></span></b></p>
			
			
			<p class=MsoNormal style='text-align:justify'><b><span lang=ES
			style='letter-spacing:-.4pt;font-size:10pt;'>QUINTA.-<span style='mso-spacerun:yes'>  </span></span></b><span
			lang=ES style='letter-spacing:-.4pt;font-size:10pt;'>El Catedrático se obliga a elaborar,
			aplicar y evaluar los exámenes parciales de acuerdo al calendario oficial de la
			Universidad.<o:p></o:p></span></p>
			<p class=MsoNormal style='text-align:justify'><b><span lang=ES
			style='letter-spacing:-.4pt;font-size:10pt;'><o:p>&nbsp;</o:p></span></b></p>
			
			<p class=MsoNormal style='text-align:justify'><b><span lang=ES
			style='letter-spacing:-.4pt;font-size:10pt;'>SEXTA.-</span></b><span lang=ES style='letter-spacing:
			-.4pt;font-size:10pt;'>Ambas partes acuerdan que el presente Contrato Privado de Honorarios
			Asimilables a Sueldos por la prestación de servicios profesionales
			independientes por obra determinada de carácter docente es por tiempo <b
			style='mso-bidi-font-weight:normal'>DETERMINADO UNICAMENTE POR EL PERIODO </b><span class=GramE>del <b>".$fechaInicioContrato."</b> al <b>".$fechaFinContrato."</b>
			<o:p></o:p></span></p>
			<p class=MsoNormal style='text-align:justify'><b><span lang=ES
			style='letter-spacing:-.4pt;font-size:10pt;'><o:p>&nbsp;</o:p></span></b></p>
			
			<p class=MsoNormal style='text-align:justify'><b style='mso-bidi-font-weight:
			normal'><span lang=ES style='letter-spacing:-.4pt;font-size:10pt;'>SEPTIMA</span></b><span
			lang=ES style='letter-spacing:-.4pt;font-size:10pt;'>.- La Universidad no se obliga a contratar los Servicios Profesionales Independientes por Obra Determinada
			de carácter Docente del Catedrático una vez terminado el periodo especificado en la Clausula Sexta, toda vez que dicha vigencia ha Fenecido para todos los
			efectos legales a que haya lugar, quedando la Universidad en libertad de contratar a cualquier otro profesionista independiente que estime conveniente.
			<o:p></o:p></b></span></p>
			<p class=MsoNormal style='text-align:justify'><b><span lang=ES
			style='letter-spacing:-.4pt;font-size:10pt;'><o:p>&nbsp;</o:p></span></b></p>
			
			<p class=MsoNormal style='text-align:justify'><b><span lang=ES
			style='letter-spacing:-.4pt;font-size:10pt;'>OCTAVA.- </span></b><span lang=ES
			style='letter-spacing:-.4pt;font-size:10pt;;mso-bidi-font-weight:bold'>La Universidad se obliga a pagar, únicamente por la Actividad Docente desarrollada,
			 es decir por cátedra impartida frente al Grupo, pago que se hará de manera quincenal, únicamente durante la vigencia del presente contrato, mencionada en
			 la Clausula Sexta del presente contrato, para lo cual la Universidad pagará al Catedrático por la actividad mencionada, por lo que una vez concluido el
			 trabajo a conformidad de la Universidad, esta liquidará la cantidad que se deba al Catedrático, previa comprobación de horas impartidas, quedando asi,
			  finiquitado el presente contrato.
			<o:p></o:p></span></p>
			<p class=MsoNormal style='text-align:justify'><b><span lang=ES
			style='letter-spacing:-.4pt;font-size:10pt;'><o:p>&nbsp;</o:p></span></b></p>
			
			<p class=MsoNormal style='text-align:justify'><b><span lang=ES
			style='letter-spacing:-.4pt;font-size:10pt;'>NOVENA.- </span></b><span lang=ES
			style='letter-spacing:-.4pt;font-size:10pt;;mso-bidi-font-weight:bold'>La Universidad podrá rescindir al Catedrático cuando este cobre por su cuenta cursos
			de capacitación, actualización, aprendizaje y otros, impartidos por el Catedrático a alumnos de la Universidad o cuando registren y comprueben violaciones
			a los Reglamentos y Estatutos Generales Internos que rigen a la Universidad, así como también sus normas.
			<o:p></o:p></span></p>
			<p class=MsoNormal style='text-align:justify'><b><span lang=ES
			style='letter-spacing:-.4pt;font-size:10pt;'><o:p>&nbsp;</o:p></span></b></p>
			
			<p class=MsoNormal style='text-align:justify'><b><span lang=ES
			style='letter-spacing:-.4pt;font-size:10pt;'>DECIMA.- </span></b><span lang=ES
			style='letter-spacing:-.4pt;font-size:10pt;;mso-bidi-font-weight:bold'>El Catedrático se obliga a cubrir e impartir puntualmente todas las clases que la
			Universidad le asigne a fin de mantener el nivel académico de los alumnos y la cobertura adecuada de los programas que la Universidad establezca; la
			Falta Injustificada a más de tres clases durante la vigencia del presente contrato por materia impartida, será causa suficiente de rescisión de contrato,
			 sin responsabilidad para la Universidad.
			<o:p></o:p></span></p>
			<p class=MsoNormal style='text-align:justify'><b><span lang=ES
			style='letter-spacing:-.4pt;font-size:10pt;'><o:p>&nbsp;</o:p></span></b></p>
			
			<p class=MsoNormal style='text-align:justify'><b><span lang=ES
			style='letter-spacing:-.4pt;font-size:10pt;'>DECIMA PRIMERA</span></b><span lang=ES
			style='letter-spacing:-.4pt;font-size:10pt;;mso-bidi-font-weight:bold'>.- En caso de que el Catedrático faltare a impartir alguna de sus clases, se obliga a
			reponer con posterioridad dicha clase, sin goce de sueldo.
			<o:p></o:p></span></span></p>
			<p class=MsoNormal style='text-align:justify'><b><span lang=ES
			style='letter-spacing:-.4pt;font-size:10pt;'><o:p>&nbsp;</o:p></span></b></p>
			
			<p class=MsoNormal style='text-align:justify'><b><span lang=ES
			style='letter-spacing:-.4pt;font-size:10pt;'>DECIMA SEGUNDA</span></b><span lang=ES
			style='letter-spacing:-.4pt;font-size:10pt;;mso-bidi-font-weight:bold'>- Si el Catedrático decide voluntariamente dar por terminado con anticipación el presente
			contrato, deberá dar aviso a la Universidad 8 días antes a su Renuncia, para efectos de tener el plazo citado, para poder suplir al Catedrático y no afectar
			el nivel académico de los alumnos, en la inteligencia de que de no dar aviso alguno, no le serán pagadas las clases impartidas en los últimos 15 días, sin
			ninguna responsabilidad para la Universidad.
			<b><o:p></o:p></b></span></span></p>
			<p class=MsoNormal style='text-align:justify'><b><span lang=ES
			style='letter-spacing:-.4pt;font-size:10pt;'><o:p>&nbsp;</o:p></span></b></p>
			
			<p class=MsoNormal style='text-align:justify'><b><span lang=ES
			style='letter-spacing:-.4pt;font-size:10pt;'>DECIMA TERCERA.- </span></b><span lang=ES
			style='letter-spacing:-.4pt;font-size:10pt;'><span style='mso-spacerun:yes'> </span> La falsedad en los documentos que se mencionan en las cláusulas anteriores
			serán motivo de rescisión del presente contrato sin responsabilidad para la Universidad.
			<o:p></o:p></span></p>
			<p class=MsoNormal style='text-align:justify'><b><span lang=ES
			style='letter-spacing:-.4pt;font-size:10pt;'><o:p>&nbsp;</o:p></span></b></p>
			
			<p class=MsoNormal style='text-align:justify'><b><span lang=ES
			style='letter-spacing:-.4pt;font-size:10pt;'>DECIMA CUARTA.- </span></b><span lang=ES
			style='letter-spacing:-.4pt;font-size:10pt;'><span style='mso-spacerun:yes'> </span> Ambas partes acuerdan que para efectos del pago al Catedrático por las Horas
			de cátedra impartidas frente a grupo, la Universidad le hará estos pagos, a través de tarjeta de débito estrictamente a nombre de Catedrático, previo recibo que 
			este firme de conformidad a la Universidad.
			<o:p></o:p></span></p>
			<p class=MsoNormal style='text-align:justify'><b><span lang=ES
			style='letter-spacing:-.4pt;font-size:10pt;'><o:p>&nbsp;</o:p></span></b></p>
			
			<p class=MsoNormal style='text-align:justify'><b><span lang=ES
			style='letter-spacing:-.4pt;font-size:10pt;'>DECIMA QUINTA.- </span></b><span lang=ES
			style='letter-spacing:-.4pt;font-size:10pt;'><span style='mso-spacerun:yes'>  </span> El Catedrático autoriza a la Universidad para que esta efectúe las retenciones
			y enteros de impuestos sobre la renta, de conformidad con lo estipulado en los artículos 113 de la ley de Impuestos sobre la renta, anotando en el comprobante las
			cantidades netas recibidas por el Catedrático.
			<o:p></o:p></span></p>
			<p class=MsoNormal style='text-align:justify'><b><span lang=ES
			style='letter-spacing:-.4pt;font-size:10pt;'><o:p>&nbsp;</o:p></span></b></p>
			
			<p class=MsoNormal style='text-align:justify'><b><span lang=ES
			style='letter-spacing:-.4pt;font-size:10pt;'>DÉCIMA SEXTA.- </span></b><span lang=ES
			style='letter-spacing:-.4pt;font-size:10pt;'> Son causas de terminación y/o rescisión del presente contrato sin responsabilidad para la Universidad, 
			las siguientes:
			<o:p></o:p></span></p>
			<p class=MsoNormal style='text-align:justify'><b><span lang=ES
			style='letter-spacing:-.4pt;font-size:10pt;'><o:p>&nbsp;</o:p></span></b></p>
			
			<p class=MsoNormal style='text-align:justify'><span lang=ES style='letter-spacing:
			-.4pt;font-size:10pt;'><span style='mso-spacerun:yes'> </span>A).- Que el Catedrático incumpla con cualquiera de las clausulas estipuladas en el presente
			contrato.
			<o:p></o:p></span></p>
			<p class=MsoNormal style='text-align:justify'><b><span lang=ES
			style='letter-spacing:-.4pt;font-size:10pt;'><o:p>&nbsp;</o:p></span></b></p>

			<p class=MsoNormal style='text-align:justify'><span lang=ES style='letter-spacing:
			-.4pt;font-size:10pt;'><span style='mso-spacerun:yes'> </span>B).- La terminación de la vigencia del Contrato.
			<o:p></o:p></span></p>
			<p class=MsoNormal style='text-align:justify'><b><span lang=ES
			style='letter-spacing:-.4pt;font-size:10pt;'><o:p>&nbsp;</o:p></span></b></p>

			<p class=MsoNormal style='text-align:justify'><span lang=ES style='letter-spacing:
			-.4pt;font-size:10pt;'><span style='mso-spacerun:yes'> </span>C).- Por común acuerdo de las partes.
			<o:p></o:p></span></p>
			<p class=MsoNormal style='text-align:justify'><b><span lang=ES
			style='letter-spacing:-.4pt;font-size:10pt;'><o:p>&nbsp;</o:p></span></b></p>

			<p class=MsoNormal style='text-align:justify'><span lang=ES style='letter-spacing:
			-.4pt;font-size:10pt;'><span style='mso-spacerun:yes'> </span>D).- Porque el Catedrático no se apegue estrictamente a los Reglamentos que rigen a
			 la Universidad.
			<o:p></o:p></span></p>
			<p class=MsoNormal style='text-align:justify'><b><span lang=ES
			style='letter-spacing:-.4pt;font-size:10pt;'><o:p>&nbsp;</o:p></span></b></p>
			
			<p class=MsoNormal style='text-align:justify'><b style='mso-bidi-font-weight:
			normal'><span lang=ES style='letter-spacing:-.4pt;font-size:10pt;'>DECIMO SEPTIMA</span></b><span
			lang=ES style='letter-spacing:-.4pt;font-size:10pt;'>.- Para cualquier controversia que pudiera surgir con relación a la interpretación o 
			incumplimiento del presente contrato las partes convienen Someterse a la jurisdicción de los tribunales Civiles de la Ciudad de Orizaba
			del Estado de Veracruz, renunciando a cualquier otra jurisdicción que pudiera corresponderles por razón de sus domicilios.
			<o:p></o:p></span></p>
			
			<p class=MsoNormal style='text-align:justify'><b><span lang=ES
			style='letter-spacing:-.4pt;font-size:10pt;'><span style='mso-spacerun:yes'> </span>Una vez que
			fuera leído el presente contrato por las partes y sabedoras de su contenido y
			alcance legal</span></b><span lang=ES style='letter-spacing:-.4pt;font-size:10pt;'> <b
			style='mso-bidi-font-weight:normal'>y toda vez que no existe dolo, error, ni
			mala fe, ambas partes lo firman de entera conformidad en la Ciudad de Orizaba,
			Ver. ".$fechaContratoFirmaDia."</b><b
			style='mso-bidi-font-weight:normal'>.</b>
			<o:p></o:p></span></p>
			
			<p class=MsoNormal style='text-align:justify'><span lang=ES style='letter-spacing:
			-.4pt;font-size:10pt;'><o:p>&nbsp;</o:p></span></p>
			<p class=MsoNormal style='text-align:justify'><span lang=ES style='letter-spacing:
			-.4pt;font-size:10pt;'><o:p>&nbsp;</o:p></span></p>
			<p class=MsoNormal style='text-align:justify'><span lang=ES style='letter-spacing:
			-.4pt;font-size:10pt;'><o:p>&nbsp;</o:p></span></p>
			<p class=MsoNormal style='text-align:justify'><span lang=ES style='letter-spacing:
			-.4pt;font-size:10pt;'><o:p>&nbsp;</o:p></span></p>
			<p class=MsoNormal style='text-align:justify'><span lang=ES style='letter-spacing:
			-.4pt;font-size:10pt;'><o:p>&nbsp;</o:p></span></p>
			
			<table class=MsoTableGrid border=1 cellspacing=0 cellpadding=0
			 style='border-collapse:collapse;border:none;mso-border-alt:solid windowtext .5pt;
			 mso-yfti-tbllook:480;mso-padding-alt:0cm 5.4pt 0cm 5.4pt'>
			 <tr style='mso-yfti-irow:0;mso-yfti-firstrow:yes;mso-yfti-lastrow:yes'>
			  <td width=299 valign=top style='width:224.5pt;border:none;padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'><b
			  style='mso-bidi-font-weight:normal'><span lang=ES style='letter-spacing:-.4pt;font-size:10pt;'>LA
			  UNIVERSIDAD<o:p></o:p></span></b></p>
			  </td>
			  <td width=299 valign=top style='width:224.5pt;border:none;padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'><b
			  style='mso-bidi-font-weight:normal'><span lang=ES style='letter-spacing:-.4pt;font-size:10pt;'>EL
			  CATEDRÁTICO<o:p></o:p></span></b></p>
			  </td>
			 </tr>
			</table>
			
			<p class=MsoNormal style='text-align:justify'><span lang=ES style='letter-spacing:
			-.4pt;font-size:10pt;'><o:p>&nbsp;</o:p></span></p>
			<p class=MsoNormal style='text-align:justify'><span lang=ES style='letter-spacing:
			-.4pt;font-size:10pt;'><o:p>&nbsp;</o:p></span></p>
			<p class=MsoNormal style='text-align:justify'><span lang=ES style='letter-spacing:
			-.4pt;font-size:10pt;'><o:p>&nbsp;</o:p></span></p>
			
			<table class=MsoTableGrid border=1 cellspacing=0 cellpadding=0
			 style='border-collapse:collapse;border:none;mso-border-alt:solid windowtext .5pt;
			 mso-yfti-tbllook:480;mso-padding-alt:0cm 5.4pt 0cm 5.4pt'>
			 <tr style='mso-yfti-irow:0;mso-yfti-firstrow:yes;mso-yfti-lastrow:yes'>
			  <td width=299 valign=top style='width:224.5pt;border:none;padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'><b
			  style='mso-bidi-font-weight:normal'><span lang=ES style='letter-spacing:-.4pt;font-size:10pt;'>".strtoupper(($rector))."
				<o:p></o:p></span></b></p>
			  </td>
			  <td width=299 valign=top style='width:224.5pt;border:none;padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'><span lang=ES
			  style='letter-spacing:-.4pt;font-size:10pt;'><o:p>&nbsp;</o:p></span><span style=\"letter-spacing:-.4pt;font-size:10pt;\">".strtoupper(($profesor))."</span></p>
			  </td>
			 </tr>
			</table>
			
			<p class=MsoNormal><span lang=ES><span style='mso-spacerun:yes'> </span></span></p>
			
			<p class=MsoNormal><span lang=ES><o:p>&nbsp;</o:p></span></p>
			
			<p class=MsoNormal><span lang=ES><o:p>&nbsp;</o:p></span><span class=\"Section1\">
			<br clear=all style='mso-special-character:line-break;page-break-before:always'>";	
				
		$cadDocumento.="
			<body lang=ES-MX>

<div class=WordSection11>

<p class=MsoNormal1 align=right style='text-align:right'><b>".strtoupper($sedeContrato)." A".strtoupper($fechaFinContrato)."
</b></p>

<p class=MsoNormal1><b>&nbsp;</b></p>

<p class=MsoNormal1><b>&nbsp;</b></p>

<p class=MsoNormal1><b>&nbsp;</b></p>

<p class=MsoNormal1><b>&nbsp;</b></p>

<p class=MsoNormal1><b>".$nombreSede."</b></p>

<p class=MsoNormal1><b>P R E S E N T E:</b></p>

<p class=MsoNormal1><b>&nbsp;</b></p>

<p class=MsoNormal1>&nbsp;</p>

<p class=MsoNormal1>&nbsp;</p>

<p class=MsoNormal1 style='text-align:justify'>&nbsp;</p>

<p class=MsoNormal1 style='text-align:justify'>&nbsp;</p>

<p class=MsoNormal1 style='text-align:justify'>&nbsp;</p>

<p class=MsoNormal1 style='text-align:justify'>&nbsp;</p>

<p class=MsoNormal1 style='text-align:justify'>&nbsp;</p>

<p class=MsoNormal1 style='text-align:justify'>&nbsp;</p>

<p class=MsoNormal1 style='text-align:justify'>&nbsp;</p>

<p class=MsoNormal1 style='text-align:justify'>&nbsp;</p>

<p class=MsoNormal1 style='text-align:justify'>&nbsp;</p>

<p class=MsoBodyText1 style='line-height:150%'>POR MEDIO DE LA PRESENTE,
COMUNICO A USTEDES QUE A PARTIR DE ESTA FECHA POR ASI CONVENIR A MIS INTERESES,
DE MANERA <b>VOLUNTARIA E IRREVOCABLE</b>, DOY POR TERMINADA LA RELACION LABORAL
O CONTRATO DE TRABAJO QUE NOS UNIO; POR LO QUE NO ME RESERVO NINGUNA ACCION O
DERECHO ALGUNO QUE EJERCITAR EN SU CONTRA, CON POSTERIORIDAD, NI DE QUIEN A SUS
INTERESES LEGALES REPRESENTE.</p>

<p class=MsoNormal1 style='text-align:justify'>&nbsp;</p>

<p class=MsoNormal1 style='text-align:justify'>&nbsp;</p>

<p class=MsoNormal1 style='text-align:justify;line-height:150%'>ASI MISMO
MANIFIESTO QUE DURANTE EL TIEMPO QUE PRESTE MIS SERVICIOS PARA USTEDES, SIEMPRE
ME CUBRIERON OPORTUNAMENTE LAS PRESTACIONES A QUE POR LEY TENIA DERECHO, NO
SUFRIENDO RIESGO O ACCIDENTE DE TRABAJO ALGUNO DURANTE EL TIEMPO QUE DURO LA
RELACION LABORAL, RECIBIENDO SIEMPRE UN TRATO DIGNO Y PROFESIONAL.</p>

<p class=MsoNormal1 style='text-align:justify'>&nbsp;</p>

<p class=MsoNormal1 style='text-align:justify'>&nbsp;</p>

<p class=MsoNormal1><b>&nbsp;</b></p>

<p class=MsoNormal1><b>&nbsp;</b></p>

<p class=MsoNormal1 align=center style='text-align:center'><b>&nbsp;</b></p>

<p class=MsoNormal1 align=center style='text-align:center'><b>ATENTAMENTE</b></p>

<p class=MsoNormal1 style='text-align:justify'><b>&nbsp;</b></p>

<p class=MsoNormal1 style='text-align:justify'><b>&nbsp;</b></p>

<p class=MsoNormal1 style='text-align:justify'><b>&nbsp;</b></p>

<p class=MsoNormal1 style='text-align:justify'><b>&nbsp;</b></p>

<p class=MsoNormal1 align=center style='text-align:center'><b>_________________________________</b></p>

<h1 class=docente>".$profesor."</h1>

<p class=MsoNormal1><span lang=ES>&nbsp;</span></p>

</div>

</body>";
			

		return $cadDocumento;
	}
	
	function obtenerDiaHabilAnterior($fecha,$plantel)
	{
		global $con;
		$fin=false;
		$ct=0;
		$fechaInicio=strtotime($fecha);
		$fecha2=date("N",$fechaInicio);
		if(!esDiaInhabilEscolar($plantel,$fecha)&&($fecha2!=6)&&($fecha2!=7))
		{
			return $fecha;
		}

		while(!$fin)
		{
			switch($fecha2)
			{
				case 1:
						$fechaAnterior=strtotime("-3 days",$fechaInicio);
				break;
				case 7:
						$fechaAnterior=strtotime("-2 days",$fechaInicio);
				break;
				default:
						$fechaAnterior=strtotime("-1 days",$fechaInicio);
			}
			

			$fechaInicio=$fechaAnterior;
			$fecha2=date("N",$fechaInicio);
			
			if(!esDiaInhabilEscolar($plantel,date("Y-m-d",$fechaInicio))&&($fecha2!=6)&&($fecha2!=7))
			{
				$fin=true;
				return date("Y-m-d",$fechaInicio);
			}
		}
	}
	
	//revalidacion/equivalencia
	
	function esCalificacionAprobatoria($idMateria,$calificacion,$idInstanciaPlan)
	{
		global $con;
		
		$filaConf=obtenerConfiguracionPlanEstudio(472,"",$idInstanciaPlan);
		
		
		
		$minCalifacionAprobatoria=6;
		if($filaConf)
		{
			$minCalifacionAprobatoria=$filaConf[17];
			if($minCalifacionAprobatoria=="")
				$minCalifacionAprobatoria=6;
		}
		if($calificacion>=$minCalifacionAprobatoria)
			return 1;
		else
			return 0;
	}
	
	function obtenerMateriasPreRequisitosEquivalenciaIncumple($idMateria,$idSolicitudRevaliacion)
	{
		global $con;
		$arrPreRequisitos=obtenerMateriasPrerrequisito($idMateria);
		$arrMateriasInCumple=array();
		if(sizeof($arrPreRequisitos)>0)
		{
			foreach($arrPreRequisitos as $idMateria=>$resto)
			{
				$consulta="SELECT calificacion FROM 4575_calificacionesSolicitudesRevalidacion WHERE idSolicitudRevaliacion=".$idSolicitudRevaliacion." AND idMateriaDestino=".$idMateria;
				$calificacion=$con->obtenerValor($consulta);
				if($calificacion=="")
				{
					$arrMateriasInCumple[$idMateria]=1;
				}
				else
				{
					if(esCalificacionAprobatoria($idMateria,$calificacion)==0)
					{
						$arrMateriasInCumple[$idMateria]=1;
					}
				}
			}
		}
		//varDump($arrMateriasInCumple);
		return $arrMateriasInCumple;
	}
	
	function obtenerMateriasPrerrequisito($idMateria)
	{
		global $con;
		$arrPreRequisitos=array();
		$consulta="SELECT idMateriaRequisito,tipoRequisito FROM 4511_seriacionMateria WHERE idMateria=".$idMateria." AND tipoRequisito IN (1,3)";
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$arrPreRequisitos[$fila[0]]=$fila[1];
		}
		
		return $arrPreRequisitos;
	}
	
	function obtenerMateriasDependenciaInicio($idMateria)
	{
		global $con;
		$arrMateriasDependencia=array();
		obtenerMateriasDependencia($idMateria,$arrMateriasDependencia);
		return $arrMateriasDependencia;
	}
	
	function obtenerMateriasDependencia($idMateria,&$arrMateriasDependencia)
	{
		global $con;
		$consulta="SELECT idMateria FROM 4511_seriacionMateria WHERE idMateriaRequisito=".$idMateria." and tipoRequisito IN (1,3)";
		$res=$con->obtenerFilas($consulta);
		while($f=mysql_fetch_row($res))
		{
			if(!isset($arrMateriasDependencia[$f[0]]))
			{
				$arrMateriasDependencia[$f[0]]=1;
				obtenerMateriasDependencia($f[0],$arrMateriasDependencia);
			}
		}
		return $arrMateriasDependencia;
	}
	
	function evaluarMateriasSolicitudEquivalencia($idSolicitud)
	{
		global $con;

		$x=0;
		$consulta[$x]="begin";
		$x++;
		$query="SELECT idMateriaDestino FROM 4575_calificacionesSolicitudesRevalidacion WHERE idSolicitudRevaliacion=".$idSolicitud." AND  situacion=1";
		$res=$con->obtenerfilas($query);
		while($fila=mysql_fetch_row($res))
		{
			$arrMateriasIncumple=obtenerMateriasPreRequisitosEquivalenciaIncumple($fila[0],$idSolicitud);
			if(sizeof($arrMateriasIncumple)>0)
			{
				
				$listMaterias="";
				foreach($arrMateriasIncumple as $m=>$resto)
				{
					if($listMaterias=="")
						$listMaterias=$m;
					else
						$listMaterias.=",".$m;
				}
				$consulta[$x]="UPDATE 4575_calificacionesSolicitudesRevalidacion SET situacion=3, datosComplementarios='".$listMaterias."' WHERE idSolicitudRevaliacion=".$idSolicitud." AND idMateriaDestino=".$fila[0];
				$x++;
				$arrMateriasDependencia=obtenerMateriasDependenciaInicio($fila[0]);
				
				if(sizeof($arrMateriasDependencia)>0)
				{
					
					foreach($arrMateriasDependencia as $m=>$resto)
					{
						actualizarInclumplimientoMateriasDependientes($fila[0],$m,$idSolicitud,$consulta,$x);
					}
				}
			}
			else
			{
				$consulta[$x]="UPDATE 4575_calificacionesSolicitudesRevalidacion SET situacion=1, datosComplementarios='' WHERE idSolicitudRevaliacion=".$idSolicitud." AND idMateriaDestino=".$fila[0];
				$x++;	
			}
		}
		$consulta[$x]="commit";
		$x++;
		return $con->ejecutarBloque($consulta);
	}
	
	function actualizarInclumplimientoMateriasDependientes($idMateriaBase,$idMateria,$idSolicitud,&$consulta,&$x)
	{
		global $con;
		$query="SELECT idRevalidacion,datosComplementarios FROM 4575_calificacionesSolicitudesRevalidacion WHERE idSolicitudRevaliacion=".$idSolicitud." AND idMateriaDestino=".$idMateria;
		$fDep=$con->obtenerPrimeraFila($query);
		if($fDep)
		{
			$arrMaterias=explode(",",$fDep[1]);
			if(!existeValor($arrMaterias,$idMateriaBase))
				array_push($arrMaterias,$idMateriaBase);
			$listMaterias="";
			foreach($arrMaterias as $m)
			{
				if($listMaterias=="")
					$listMaterias=$m;
				else
					$listMaterias.=",".$m;
			}
			
			$consulta[$x]="UPDATE 4575_calificacionesSolicitudesRevalidacion SET situacion=3, datosComplementarios='".$listMaterias."' WHERE idSolicitudRevaliacion=".$idSolicitud." AND idRevalidacion=".$fDep[0];
			$x++;
			$arrMateriasDependencia=obtenerMateriasDependenciaInicio($idMateria);
			if(sizeof($arrMateriasDependencia)>0)
			{
				foreach($arrMateriasDependencia as $m=>$resto)
				{
					actualizarInclumplimientoMateriasDependientes($idMateriaBase,$m,$idSolicitud,$consulta,$x);
				}
			}
		}
	}
	
	function obtenerProfesorTitular($idGrupo,$fechaBase="",$obtenerIdAsignacion=false)
	{
		global $con;
		$consulta="SELECT nombreGrupo,idCiclo,idPeriodo,fechaInicio,fechaFin,idInstanciaPlanEstudio FROM 4520_grupos WHERE idGrupos=".$idGrupo;
								
		$filaGrupo=$con->obtenerPrimeraFila($consulta);
		$minFecha=$filaGrupo[3];
		$maxFecha=$filaGrupo[4];
		$idProfesorTitular="";
		$fechaActual=date("Y-m-d");
		if($fechaBase!="")
			$fechaActual=$fechaBase;
		if(strtotime($fechaActual)>strtotime($maxFecha))
		{

			$consulta="SELECT max(fechaBaja) FROM 4519_asignacionProfesorGrupo WHERE idGrupo=".$idGrupo." AND fechaAsignacion<=fechaBaja";
			$maxFechaAsignacion=$con->obtenerValor($consulta);
			if(!$obtenerIdAsignacion)
				$consulta="select idUsuario FROM 4519_asignacionProfesorGrupo WHERE idGrupo=".$idGrupo." AND fechaBaja='".$maxFechaAsignacion."' AND fechaAsignacion<=fechaBaja";
			else
				$consulta="select idAsignacionProfesorGrupo FROM 4519_asignacionProfesorGrupo WHERE idGrupo=".$idGrupo." AND fechaBaja='".$maxFechaAsignacion."' AND fechaAsignacion<=fechaBaja";
			$idProfesorTitular=$con->obtenerValor($consulta);
		}
		else
		{
			if(strtotime($minFecha)>strtotime($fechaActual))
			{
			
				$minFecha=$fechaActual;
				$consulta="SELECT MIN(fechaAsignacion) FROM 4519_asignacionProfesorGrupo WHERE idGrupo=".$idGrupo." AND fechaAsignacion<=fechaBaja";
				$minFechaAsignacion=$con->obtenerValor($consulta);
				if(!$obtenerIdAsignacion)
					$consulta="select idUsuario FROM 4519_asignacionProfesorGrupo WHERE idGrupo=".$idGrupo." AND fechaAsignacion='".$minFechaAsignacion."' AND fechaAsignacion<=fechaBaja";
				else
					$consulta="select idAsignacionProfesorGrupo FROM 4519_asignacionProfesorGrupo WHERE idGrupo=".$idGrupo." AND fechaAsignacion='".$minFechaAsignacion."' AND fechaAsignacion<=fechaBaja";
				$idProfesorTitular=$con->obtenerValor($consulta);
			}
			else
			{
				if(!$obtenerIdAsignacion)
					$consulta="select idUsuario FROM 4519_asignacionProfesorGrupo WHERE idGrupo=".$idGrupo." AND '".$fechaActual."'>=fechaAsignacion and '".$fechaActual."'<=fechaBaja  AND fechaAsignacion<=fechaBaja";
				else
					$consulta="select idAsignacionProfesorGrupo FROM 4519_asignacionProfesorGrupo WHERE idGrupo=".$idGrupo." AND '".$fechaActual."'>=fechaAsignacion and '".$fechaActual."'<=fechaBaja  AND fechaAsignacion<=fechaBaja";
				$idProfesorTitular=$con->obtenerValor($consulta);
			}
		
		}
		if($idProfesorTitular=="")
			$idProfesorTitular=-1;
		return $idProfesorTitular;
	}	
		
	//Validacion horarios
	function generarResolucionArregloErrores($arreglo,$nivel=1)
	{
		global $con;
		$cadErrores="";
		$permiteContinuar=1;
		$arrLeyendas=array();
		$arrErrores=array();
		$consulta="SELECT idErroresValidacion,descripcion,permiteContinuar,permiteContinuarAME,permiteContinuarEnvioValidacion,permiteContinuarAutorizacion FROM 4586_erroresValidacion ORDER BY descripcion";
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$arrErrores[$fila[0]]["leyenda"]=$fila[1];
			$arrErrores[$fila[0]]["permiteContinuar"]=$fila[2];
			$arrErrores[$fila[0]]["permiteContinuarAME"]=$fila[3];
			$arrErrores[$fila[0]]["permiteContinuarValidacion"]=$fila[4];
			$arrErrores[$fila[0]]["permiteContinuarAutorizacion"]=$fila[5];
			
		}
		if(sizeof($arreglo)>0)
		{
			foreach($arreglo as $o)
			{
				
				
				$pContinuar=0;
				$leyenda="No definido";
				$comp=$o->compl;
				if(isset($arrErrores[$o->noError]))
				{
					switch($nivel)
					{
						case 1:
							$pContinuar=$arrErrores[$o->noError]["permiteContinuar"];
						break;
						case 2:
							$pContinuar=$arrErrores[$o->noError]["permiteContinuarAME"];
						break;
						case 3:
							$pContinuar=$arrErrores[$o->noError]["permiteContinuarValidacion"];
						break;
						case 4:
							$pContinuar=$arrErrores[$o->noError]["permiteContinuarAutorizacion"];
						break;
					}
					if(isset($o->leyenda))
						$leyenda=$o->leyenda;
					else
						$leyenda=$arrErrores[$o->noError]["leyenda"];
					
				}
				if(!existeValor($arrLeyendas,$o->noError."__".$comp))
				{
					$obj='{"permiteContinuar":"'.$pContinuar.'","leyenda":"'.cv($leyenda).'","noError":"'.$o->noError.'","complementario":"'.cv($comp).'"}';
					if($cadErrores=="")
						$cadErrores=$obj;
					else
						$cadErrores.=",".$obj;
					if($pContinuar==0)
						$permiteContinuar=0;
					array_push($arrLeyendas,$arrLeyendas,$o->noError."__".$comp);
				}
			}
		}
		return '{"permiteContinuar":"'.$permiteContinuar.'","arrErrores":['.$cadErrores.']}';
		
	}
	
	function cumpleTotalHorasSemanaCurso($idGrupo)
	{
		global $con;
			
		$arrDatosMateria=obtenerDatosMateriaHorasGrupo($idGrupo);	
			
		$horasAsignadas=obtenerHorasAsignadasGrupo($idGrupo);
		if(($horasAsignadas>=$arrDatosMateria["horasSemana"]))
			return true;
		return false;
		
	}	
	
	function obtenerDiferenciaHoraMinutos($horaInicial,$horaFinal)
	{
		$hFinal=strtotime($horaFinal);
		$hInicial=strtotime($horaInicial);
		$diferencia=($hFinal)-$hInicial;
		return ($diferencia/60);
	}
	
	function obtenerPerfilExamenGrupo($idGrupo,$tipoExamen)
	{
		global $con;
		$consulta="SELECT idPlanEstudio,idInstanciaPlanEstudio FROM 4520_grupos WHERE idGrupos=".$idGrupo;
		$fGrupo=$con->obtenerPrimeraFila($consulta);
		$idInstancia=$fGrupo[0];
		$idPlanEstudio=$fGrupo[1];
		$idReferenciaExamenes=obtenerPerfilExamenesAplica($idPlanEstudio,$idInstancia);

		$consulta="SELECT t.* FROM _398_gridTiposExamen g,_721_tablaDinamica t WHERE g.idReferencia=".$idReferenciaExamenes." 
				AND t.id__721_tablaDinamica=g.tipoExamen and t.id__721_tablaDinamica=".$tipoExamen;

		$fRegistro=$con->obtenerPrimeraFila($consulta);
		return $fRegistro;
	}
	
	function removerPorcentajesCriterioEvaluacionPerfil($codigoPadre,$idPerfil)
	{
		global $con;
		$x=0;	
		$consulta[$x]="begin";
		$x++;
		$query="SELECT codigoUnidad FROM 4564_criteriosEvaluacionPerfilMateria WHERE codigoPadre='".$codigoPadre."' AND idPerfil=".$idPerfil;
		$res=$con->obtenerFilas($query);
		while($fila=mysql_fetch_row($res))
		{
			$consulta[$x]="DELETE FROM 4565_porcentajeCriterioEvaluacionPerfil WHERE idCriterioEvaluacionMateria='".$fila[0]."' AND idPerfil=".$idPerfil;
			$x++;
		}
		$consulta[$x]="commit";
		$x++;
		return $con->ejecutarBloque($consulta);
	}
	
	function obtenerPerfilExamenesAplica($iPlanEstudio,$idInstanciaPlanEstudio,$buscarSiguiente=true)
	{
		global $con;
		/*$idPlanEstudio="";
		if($iPlanEstudio!="")
			$idPlanEstudio=$iPlanEstudio;
		else
		{
			$consulta="SELECT idPlanEstudio FROM 4513_instanciaPlanEstudio WHERE idInstanciaPlanEstudio=".$idInstanciaPlanEstudio;

			$idPlanEstudio=$con->obtenerValor($consulta);
		}
		if($buscarSiguiente)
			$consulta="SELECT id__398_tablaDinamica FROM _398_tablaDinamica WHERE idPlanEstudio=".$idPlanEstudio." AND idInstanciaPlanEstudio IN (".$idInstanciaPlanEstudio.",-1) ORDER BY idInstanciaPlanEstudio DESC LIMIT 0,1";
		else
			$consulta="SELECT id__398_tablaDinamica FROM _398_tablaDinamica WHERE idPlanEstudio=".$idPlanEstudio." AND idInstanciaPlanEstudio IN (".$idInstanciaPlanEstudio.") ORDER BY idInstanciaPlanEstudio DESC LIMIT 0,1";

		$idReferenciaExamenes=$con->obtenerValor($consulta);
		if($idReferenciaExamenes=="")
			$idReferenciaExamenes=-1;*/
			
		$consulta="SELECT idPerfilEvaluacion FROM 4513_instanciaPlanEstudio WHERE idInstanciaPlanEstudio=".$idInstanciaPlanEstudio;	
		$idReferenciaExamenes=$con->obtenerValor($consulta);	
		return $idReferenciaExamenes;
	}
	
	function obtenerListadoEstandarAlumnosGrupo($idGrupo,$tipoExamen,$noExamen)
	{
		global $con;
		$consulta="SELECT idUsuario FROM 4517_alumnosVsMateriaGrupo WHERE idGrupo=".$idGrupo." AND situacion=1";

		$arrAlumnos=array();
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			
			
			
			
			$obj["idUsuario"]=$fila[0];
			array_push($arrAlumnos,$obj);
			
		}
		return $arrAlumnos;		
	}	
	
	function obtenerNombreGrupoMateria($idGrupo)
	{
		global $con;
		$consulta="SELECT CONCAT('(',nombreGrupo,') ',m.nombreMateria) FROM 4520_grupos g,4502_Materias m WHERE idGrupos=".$idGrupo."
					AND m.idMateria=g.idMateria";
		$lblGrupo=$con->obtenerValor($consulta);
		return $lblGrupo;
	}
	
	function obtenerConfiguracionPlanEstudio($idFormulario,$idPlanEstudio,$idInstanciaPlanEstudio,$arrAsociativo=false)
	{
		global $con;
		$fila=null;
		if(($idPlanEstudio=="")||($idPlanEstudio=="-1"))
		{
			$consulta="SELECT idPlanEstudio FROM 4513_instanciaPlanEstudio WHERE idInstanciaPlanEstudio=".$idInstanciaPlanEstudio;
			$idPlanEstudio=$con->obtenerValor($consulta);
		}
		$consulta="SELECT * FROM _".$idFormulario."_tablaDinamica WHERE idPlanEstudio=".$idPlanEstudio." AND idInstanciaPlanEstudio IN (".$idInstanciaPlanEstudio.",-1) ORDER BY idInstanciaPlanEstudio DESC LIMIT 0,1";
		if($arrAsociativo)
			$fila=$con->obtenerPrimeraFilaAsoc($consulta);
		else
			$fila=$con->obtenerPrimeraFila($consulta);	
		return $fila;
	}
	
	
	function procesarEventosBiometricosUsuarioBloque($idUsuario,$arrTipoIncidencias,$arrPlantelesBiometrico,$horaMarca,$arrRecesos,$compEvt,$validarPlantel=true,$esReprocesamiento=false)
	{
		global $con;

		$consulta="SELECT * FROM _484_tablaDinamica";
		$fConf=$con->obtenerPrimeraFila($consulta);
		$checarSalida=true;
		$ignorarEvento=false;
		$fInstitucion=true;
		$toleranciaAntesHoraInicio=10;//--
		$toleranciaDespuesHoraInicio=10;
		$toleranciaAntesHoraFin=10;//--
		$toleranciaFalta=10; //Tolerancia para retardo
		$toleranciaIgnoraSalida=0;
		$toleranciaRegistroSalida=10;
		$horaCero=0;//strtotime("0000-00-00 00:00:00");
		$tipoDefinicion=2; 
		$posSalida=0;
		$resultado2=0;

		$noHorasBloqueExcepcion=2;
		$tRetardoBloqueExcepcion=80;
		$tActicipoBloqueExcepcion=80;
		$toleranciaRegistroSalidaClaseNoContinua=20;
		$noMinutosSancionExcepcion=90;
		
		if($fConf)
		{
			$toleranciaAntesHoraInicio=$fConf[10];
			$toleranciaDespuesHoraInicio=$fConf[19];
			$toleranciaFalta=$fConf[11];
			$toleranciaAntesHoraFin=$fConf[12];
			$toleranciaIgnoraSalida=$fConf[20];
			$toleranciaRegistroSalida=$fConf[21];
			$toleranciaRegistroSalidaClaseNoContinua=$fConf[22];
		}
		
		$consulta="SELECT idEvento,idUsuario,fechaEvento,horaEvento,noTerminal,plantel,'','',DATE_FORMAT(horaEvento,'%H:%i:%s'),marcaSincronizacion 
					FROM  9105_eventosRecibidos where idUsuario=".$idUsuario." ".$compEvt." order by fechaEvento,horaEvento  asc";// and fechaEvento='2012-05-04' 
		
		$horaEventoPlantel=array();
		$res=$con->obtenerFilas($consulta);
		$arrEventos=array();
		$arrEventos[date("Y-m-d")]=array();
		while($fila=mysql_fetch_row($res))
		{
			if(!isset($arrEventos[$fila[2]]))
				$arrEventos[$fila[2]]=array();
			$fila[3]=strtotime($fila[3]);
			$horaEventoPlantel[$fila[5]]=$fila[3];
			array_push($arrEventos[$fila[2]],$fila);
		}
		
		$arrGrupos=array();		

		foreach($arrEventos as $fecha=>$arrFilas)
		{
			if(sizeof($arrFilas)==0)
			{
				continue;
			}
			$x=0;
			$existenBloquesPendientes=false;
			$ultimoEventoUtilizado=null;
			
			$query=array();
			$query[$x]="begin";
			$x++;
			$consulta="SELECT * FROM 9105_controlAsistencia WHERE idUsuario=".$idUsuario." AND fecha='".$fecha."'";

			$resAsistenciaCerrada=$con->obtenerFilas($consulta);
			$arrGruposIgnora=array();
			while($fAsistencia=mysql_fetch_row($resAsistenciaCerrada))
			{
				$obj[0]=$fAsistencia[12];
				$obj[1]=$fAsistencia[14];
				$obj[2]=$fAsistencia[5];
				array_push($arrGruposIgnora,$obj);
			}
			
			$arrComisiones=array();
			$consulta="SELECT id__489_tablaDinamica,tipoComision FROM _489_tablaDinamica WHERE  '".$fecha."'>=dteFechaInicial AND '".$fecha."'<=dteFechaFinal AND cmbDocentes=".$idUsuario." and idEstado=2";
			$res=$con->obtenerFilas($consulta);
			while($fila=mysql_fetch_row($res))
			{
				$consulta="SELECT idRegistroModuloSesionesClase FROM 4560_registroModuloSesionesClase WHERE  idFormulario=489 AND idReferencia=".$fila[0];
				$idReferenciaComision=$con->obtenerValor($consulta);
				$consulta="SELECT idGrupo,horaInicioBloque,horaFinBloque FROM 4561_sesionesClaseModulo WHERE idReferencia=".$idReferenciaComision;
				$resCon=$con->obtenerFilas($consulta);
				while($filaComision=mysql_fetch_row($resCon))
				{
					$arrComisiones[$filaComision[0]."_".$filaComision[1]."_".$filaComision[2]]=1;
				}
				
				
			}
						
			$fechaActual=strtotime($fecha);
			$arrGrupos=array();			

			$compEvt2="";
			if(strpos($compEvt,"plantel")!==false)
			{
				$aPlantel=explode("plantel",$compEvt);
				$compEvt2=" and plantel".$aPlantel[1];
			}
			$consulta="SELECT idGrupos,g.Plantel FROM 4520_grupos g,4519_asignacionProfesorGrupo a WHERE a.idUsuario=".$idUsuario." AND  a.participacionPrincipal=1 AND a.idGrupo=g.idGrupos  
						AND a.fechaAsignacion<='".date("Y-m-d",$fechaActual)."' AND a.fechaBaja>='".date("Y-m-d",$fechaActual)."' ".$compEvt2;
			$res=$con->obtenerFilas($consulta);
			while($fila=mysql_fetch_row($res))
			{
				if(!esDiaInhabilEscolar($fila[1],$fecha))
				{
					$consulta="SELECT count(*) from 4519_asignacionProfesorGrupo a 
							WHERE a.idGrupo=".$fila[0]." AND a.participacionPrincipal=0  AND 
							a.fechaAsignacion<='".date("Y-m-d",$fechaActual)."' AND a.fechaBaja>='".date("Y-m-d",$fechaActual)."'";
					$nSuplencias=$con->obtenerValor($consulta);
					if($nSuplencias==0)
					{
						$o[0]=$fila[0];
						$o[1]=$fila[1];
						array_push($arrGrupos,$o);
					}
				}
			}

			$consulta="SELECT idGrupos,idFormularioAccion,idRegistroAccion,g.Plantel FROM 4520_grupos g,4519_asignacionProfesorGrupo a 
						WHERE a.idUsuario=".$idUsuario." AND a.participacionPrincipal=0 AND a.idGrupo=g.idGrupos AND 
						a.fechaAsignacion<='".date("Y-m-d",$fechaActual)."' AND a.fechaBaja>='".date("Y-m-d",$fechaActual)."' ".$compEvt2;
			$res=$con->obtenerFilas($consulta);
			while($fila=mysql_fetch_row($res))
			{
				if((!existeValor($arrGrupos,$fila[0]))&&(!esDiaInhabilEscolar($fila[3],$fecha)))
				{
					$o[0]=$fila[0];
					$o[1]=$fila[3];
					array_push($arrGrupos,$o);
				}
			}

			$listGrupos="";
			$arrGruposPlantel=array();
			foreach($arrGrupos as $iGrupo)
			{
				if($listGrupos=="")
					$listGrupos=$iGrupo[0];
				else
					$listGrupos.=",".$iGrupo[0];
				$arrGruposPlantel[$iGrupo[0]]=$iGrupo[1];
			}

			if($listGrupos=="")
				$listGrupos=0;
			
			$dia=date("w",$fechaActual);
			$consulta="(SELECT idHorarioGrupo,idGrupo,dia,horaInicio,horaFin,idAula  FROM 4522_horarioGrupo WHERE idGrupo IN (".$listGrupos.") AND dia=".$dia." and '".$fecha."'>=fechaInicio and '".$fecha."'<=fechaFin) union
					(SELECT (idSesionReposicion*-1),idGrupo,0,horaInicio,horaFin,idAula FROM 4563_sesionesReposicion s,4562_registroReposicionSesion r 
					WHERE fechaReposicion='".$fecha."' AND idGrupo IN (".$listGrupos.") and r.idRegistroReposicion=s.idRegistroReposicion and r.idUsuario=".$idUsuario.") ORDER BY horaInicio";

			$resH=$con->obtenerFilas($consulta);
			$arrHorario=array();
			while($filaH=mysql_fetch_row($resH))
			{
				if($filaH[0]<0)
				{
					$consulta="select Plantel FROM 4520_grupos WHERE idGrupos=".$filaH[1];
					$p=$con->obtenerValor($consulta);
					$arrGruposPlantel[$filaH[1]]=$p;
				}
				
				
				$filaH[6]=0;
				$filaH[7]=$arrGruposPlantel[$filaH[1]];
				$filaH[8]=strtotime("-".$toleranciaAntesHoraInicio." minutes",strtotime($filaH[3]));
				$filaH[9]=strtotime("+".$toleranciaRegistroSalida." minutes",strtotime($filaH[4]));
				$filaH[10]=date("H:i:s",$filaH[8]);
				$filaH[11]=date("H:i:s",$filaH[9]);
				
				array_push($arrHorario,$filaH);
			}
			
			$arrHorarioAux=$arrHorario;
			$arrHorario=array();

			if(sizeof($arrHorarioAux)>0) 
			{
				foreach($arrHorarioAux as $filaH)
				{
					$encontradoH=false;
					if(sizeof($arrGruposIgnora)>0)
					{
						foreach($arrGruposIgnora as $datosHorario)
						{
							if(($datosHorario[0]==$filaH[1])&&($datosHorario[1]==$filaH[3]))  //Si grupo y horario inicial coinciden
							{
								if($datosHorario[2]==1) //Clase cerrada
									$encontradoH=true;
								else
									$filaH[6]=1;  //Clase inconclusa
							}
						}
						
					}
	
					if(!$encontradoH)
					{
						foreach($arrComisiones as $com=>$resto)
						{
							$dComision=explode("_",$com);
							if(($dComision[0]==$filaH[1])&&($dComision[1]==$filaH[3]))  //Si grupo y horario inicial coinciden
							{
								$encontradoH=true;
							}
						}
					}

					
					if(!$encontradoH)
					{
						array_push($arrHorario,$filaH);
					}
				}
			}	
			
			$nAux=0;
			$arrHorarioAux=array();
			for($nHorario=0;$nHorario<sizeof($arrHorario);$nHorario++)
			{
				$hActual=$arrHorario[$nHorario];

				$hActual[12]=0;
				if(($nHorario+1)<sizeof($arrHorario))
				{
					$hSiguiente=$arrHorario[$nHorario+1];
					if($hActual[4]!=$hSiguiente[3])
						$hActual[12]=1;
					
				}
				else
					$hActual[12]=1;
				if($hActual[12]==1)//Verificar
				{
					$hActual[9]=strtotime("+".$toleranciaRegistroSalidaClaseNoContinua." minutes",strtotime($hActual[4]));
					$hActual[11]=date("H:i:s",$hActual[9]);
					
				}
				
				array_push($arrHorarioAux,$hActual);
			}
			
			$nArrHorario=array();
			
			for($nHorario=0;$nHorario<sizeof($arrHorarioAux);$nHorario++)
			{
				$h=$arrHorarioAux[$nHorario];

				$pos=obtenerHoraFinBloque($arrHorarioAux,$h,($nHorario+1),$arrRecesos,$h[7],false,false);
				$h[20]=array();
				if($pos!=-1)
				{
					for($numTmp=$nHorario;$numTmp<=$pos;$numTmp++)
					{
						array_push($h[20],$arrHorarioAux[$numTmp]);
					}
					$nHorario=$pos;
					$h[4]=$arrHorarioAux[$pos][4];
					$h[9]=$arrHorarioAux[$pos][9];
				}
				else
					array_push($h[20],$arrHorarioAux[$nHorario]);
				
				$h[1]=0;	
				$h[10]=date("H:i:s",$h[8]);
				$h[11]=date("H:i:s",$h[9]);
				
				$agregarBloque=true;
				$uEvento=obtenerUltimoEvento($arrFilas,$h[7]);
				
				$horaUltimoEvento=$horaCero+strtotime($uEvento[8]);


				//$limiteChecadaSalida=$horaCero+strtotime("-".$toleranciaRegistroSalida." minutes",strtotime($h[4]));
				$limiteChecadaSalida=strtotime($h[11]);
				if(!$esReprocesamiento)
				{
					$dteMarcaSincronizacion=$horaCero+strtotime($horaMarca);
					
					if($uEvento[2]==date("Y-m-d",$dteMarcaSincronizacion))
					{
						
						$horaSincronizacion=$horaCero+strtotime(date("H:i:s",$dteMarcaSincronizacion));
						if($horaSincronizacion<$limiteChecadaSalida)
							$agregarBloque=false;
					}
					else
					{	
						
						if($horaUltimoEvento<$limiteChecadaSalida)
							$agregarBloque=false;
					}
				}
				
				
				if(!$agregarBloque)
				{
					$fActual=strtotime($fecha);
					foreach($arrEventos as $fechaTmp => $resto)
					{
						$fAuxiliar=strtotime($fechaTmp);
						if($fAuxiliar>$fActual)
						{
							
							$agregarBloque=true;
							break;
						}
					}
				}
				if($agregarBloque)
					$h["45"]=1;
				else
				{
					$h["45"]=0;
					$existenBloquesPendientes=true;
				}
				array_push($nArrHorario,$h);
				/*if($agregarBloque)
					array_push($nArrHorario,$h);
				else
					$existenBloquesPendientes=true;*/
				
			}
			
			
			
			$arrHorario=$nArrHorario;
			$arrEv=array();
			$nEventos=sizeof($arrFilas);
			$hEncontrado=null;
			$posActualEv=0;
			$nEventos=sizeof($arrFilas);
			$posHorario=0;
			$posActualEv=0;
			
			foreach($arrHorario as $bHorario)
			{

				if($bHorario[45]==0)
					break;
				$arrHorario[$posHorario][30]=array();
				
				
				$posEventoInicial=$posActualEv;
				for($pos=$posActualEv;$pos<$nEventos;$pos++)
				{
					$ev=$arrFilas[$pos];
					$agregar=false;
					$enLimiteBloqueHorario=estaEnlimiteBloqueHorario($bHorario,$ev);
					if(($enLimiteBloqueHorario)&&(cumpleEventoPermitidoPlantel($validarPlantel,$bHorario,$ev,$arrPlantelesBiometrico)))
					{

						$evS=obtenerEventoSiguiente($arrFilas,$pos);
						$bHorarioS=obtenerSiguienteSesion($arrHorario,$posHorario);
						$validarEventoAnt=false;
						if(!$bHorarioS)
						{
							$agregar=true;
						}
						else
						{

							if((!estaEnlimiteBloqueHorario($bHorarioS,$ev))||(!cumpleEventoPermitidoPlantel($validarPlantel,$bHorarioS,$ev,$arrPlantelesBiometrico)))
							{
								$agregar=true;
							}
							else
							{
								
								if($evS)
								{
									
									if((estaEnLimiteChecaEntrada($bHorarioS,$toleranciaDespuesHoraInicio,$evS))&&(cumpleEventoPermitidoPlantel($validarPlantel,$bHorarioS,$evS,$arrPlantelesBiometrico)))
										$agregar=true;
									else
									{
										
										$validarEventoAnt=true;
									}
								}
								else
								 {
									 
									$validarEventoAnt=true;
								 }
								 
								
								 
								if($validarEventoAnt)
								{
									$evA=null;
									
									if(($pos-1)>=$posEventoInicial)
									{
										
										$evA=$arrFilas[$pos-1];
										
										
									}
									
									
									if($evA)
									{
										if((!estaEnLimiteChecaSalida($bHorario,$toleranciaAntesHoraFin,$evA))||(!cumpleEventoPermitidoPlantel($validarPlantel,$bHorario,$evA,$arrPlantelesBiometrico)))
										{
											//if(isset($bHorario[30])&&(sizeof($bHorario[30])>0))
											if(isset($arrHorario[$posHorario][30])&&(sizeof($arrHorario[$posHorario][30])>0))
												$agregar=true;
											
										}
										
										
									}
									else
									{
										
										if(isset($arrHorario[$posHorario][30])&&(sizeof($arrHorario[$posHorario][30])>0))
											$agregar=true;	
									}
									
								}
							}
						}
						
						
						
					}
					
					
					
					
					if($agregar)
					{
						$arrFilas[$pos][10]=1;
						array_push($arrHorario[$posHorario][30],$ev);
						
						$ultimoEventoUtilizado=$ev;
						$posActualEv=($pos+1);
						
					}
					else
					{
						$tEvento=$horaCero+strtotime($ev[8]);
						$hFinBloque=$horaCero+$bHorario[9];
						if(($tEvento>$hFinBloque)||$enLimiteBloqueHorario)
						{
						
							$posActualEv=$pos;
							break;
						}
						

					}
						
				}
				$posHorario++;
				
			}
			
			$posHorario=0;
			
			
			
			foreach($arrHorario as $bHorario)
			{
				if($bHorario[45]==0)
					break;
				$esCasoIdeal=true;
				
				switch(sizeof($bHorario[30])) //Caso ideal
				{
					case 0:
						$numGrupos=sizeof($bHorario[20]);
						for($ct=0;$ct<$numGrupos;$ct++)
						{
							$arrHorario[$posHorario][20][$ct][40]=0;//Resultado
						}
					break;
					case 2:
						
						if(estaEnLimiteChecaEntrada($bHorario,$toleranciaDespuesHoraInicio,$bHorario[30][0]))
						{
							if(estaEnLimiteChecaSalida($bHorario,$toleranciaAntesHoraFin,$bHorario[30][1]))
							{
								$numGrupos=sizeof($bHorario[20]);
								for($ct=0;$ct<$numGrupos;$ct++)
								{
									
									$arrHorario[$posHorario][20][$ct][40]=1;//Resultado
								}
								$arrHorario[$posHorario][20][0][41]=$bHorario[30][0];
								$arrHorario[$posHorario][20][sizeof($bHorario[20])-1][42]=$bHorario[30][1];
								
							}
							else
								$esCasoIdeal=false;	
						}
						else
							$esCasoIdeal=false;
					break;
					default:
						$esCasoIdeal=false;
				}

				if(!$esCasoIdeal)
				{
					
					$posClase=0;
					$posActualEvAux=0;
					$evA=null;

					foreach($bHorario[20] as $bClase)
					{
						
						$arrHorario[$posHorario][20][$posClase][30]=array();
						$nEventosAux=sizeof($arrHorario[$posHorario][30]);
						$arrFilasAux=$arrHorario[$posHorario][30];
						$posInicioOriginal=$posActualEvAux;
						for($posAux=$posActualEvAux;$posAux<$nEventosAux;$posAux++)
						{
							$ev=$arrHorario[$posHorario][30][$posAux];
							$agregar=false;
							/*varDump($ev);
							varDump($bClase);
							echo "<br><br>";*/
							
							if(estaEnlimiteBloqueHorario($bClase,$ev))
							{
								
								$evS=obtenerEventoSiguiente($arrFilasAux,$posAux);
								$bHorarioS=obtenerSiguienteSesion($bHorario[20],$posClase);
							
								$validarEventoAnt=false;
								if(!$bHorarioS)
								{
									$agregar=true;
								}
								else
								{
									
									if(!estaEnlimiteBloqueHorario($bHorarioS,$ev))
									{
										$agregar=true;
									}
									else
									{
										if($evS)
										{
											if(estaEnLimiteChecaEntrada($bHorarioS,$toleranciaDespuesHoraInicio,$evS))
												$agregar=true;
											else
												$validarEventoAnt=true;
										}
										else
											$validarEventoAnt=true;
										
										/*varDump($ev);
										var_dump($validarEventoAnt);*/
										
										if($validarEventoAnt)
										{
											$evA=null;
											if(($posAux-1)>=$posInicioOriginal)
											{
												$evA=$arrFilasAux[$posAux-1];
											}
											
											
											if($evA)
											{
												if(!estaEnLimiteChecaSalida($bClase,$toleranciaAntesHoraFin,$evA))
												{
													$existeApertura=false;
													for($nTmp=0;$nTmp<$posClase;$nTmp++)
													{
														
														switch(sizeof($arrHorario[$posHorario][20][$nTmp][30]))
														{
															case "0":
																
															break;
															case "1":
																
																$bClase=$arrHorario[$posHorario][20][$nTmp];
																$horaEvento=$horaCero+strtotime($bClase[30][0][8]);
																$hEntrada=$horaCero+strtotime("+".$toleranciaDespuesHoraInicio." minutes",strtotime($bClase[3]));
																$hSalida=$horaCero+strtotime("-".$toleranciaAntesHoraFin." minutes",strtotime($bClase[4]));
																$diferenciaEntrada=abs($hEntrada-$horaEvento);
																$diferenciaSalida=abs($hSalida-$horaEvento);
																$tipoEvento=0;
																if($diferenciaEntrada<$diferenciaSalida) //Entrada
																{
																	$existeApertura=true;
																}
																else								//Salida
																	$existeApertura=false;
															break;
															default:
																$existeApertura=false;
															break;
														}
													}
													
													if((isset($arrHorario[$posHorario][20][$posClase][30])&&(sizeof($arrHorario[$posHorario][20][$posClase][30])>0))||($existeApertura))
													{
														
														$agregar=true;
													}
												}
											}
											
										}
									}
								}
								
								
							}
							if($agregar)
							{
								$agregarFinal=true;
								

								if((estaEnLimiteChecaEntrada($bClase,$toleranciaDespuesHoraInicio,$ev))&&(sizeof($arrHorario[$posHorario][20][$posClase][30])>0))
								{
									$agregarFinal=false;
								}
								
								
								if($agregarFinal)
									array_push($arrHorario[$posHorario][20][$posClase][30],$ev);
								$evA=$ev;
								$posActualEvAux=($posAux+1);
							}
							else
								break;
						}
						$posClase++;
					}
					
					$posClase=0;
					$existeApertura=false;
					
					$arrCacheGrupos=array();
					foreach($bHorario[20] as $bClase)///Falta
					{
						$bClase=$arrHorario[$posHorario][20][$posClase];
						
						switch(sizeof($bClase[30]))
						{
							case 0:
								$arrHorario[$posHorario][20][$posClase][40]=0;
								array_push($arrCacheGrupos,$posClase);
							break;
							case 1:
								
								$horaEvento=$horaCero+strtotime($bClase[30][0][8]);
								$hEntrada=$horaCero+strtotime("+".$toleranciaDespuesHoraInicio." minutes",strtotime($bClase[3]));
								$hSalida=$horaCero+strtotime("-".$toleranciaAntesHoraFin." minutes",strtotime($bClase[4]));
								$diferenciaEntrada=$horaCero+abs($hEntrada-$horaEvento);
								$diferenciaSalida=$horaCero+($hSalida-$horaEvento);
								$tipoEvento=0;
								
								$cumpleCondicionesEntrada=true;
								if($existeApertura)
								{
									if(sizeof($bClase[30])==1)
									{
										$cumpleCondicionesEntrada=false;
									}
								}
								$diferenciaEntrada=$horaCero;
								
								if(($diferenciaEntrada<$diferenciaSalida)&&($cumpleCondicionesEntrada)) //Entrada
								{
									
									if(estaEnLimiteChecaEntrada($bClase,$toleranciaDespuesHoraInicio,$bClase[30][0]))
										$tipoEvento=1;  //Entrada
									else
									{
										
										if(estaEnLimiteRetardo($bClase,$toleranciaDespuesHoraInicio,$toleranciaFalta,$bClase[30][0]))
										{
											
											$tipoEvento=2; //Entrada con retardo
										}
										else
										{
												
											$tipoEvento=3; //Entrada fuera de los limites permitidos
										}
									}
								}
								else								//Salida
								{
									if(estaEnLimiteChecaSalida($bClase,$toleranciaAntesHoraFin,$bClase[30][0]))
										$tipoEvento=5;//Salida
									else
										$tipoEvento=4; //Salida anticipada
								}
								
								
									
								switch($tipoEvento)
								{
									case 1://Entradas
									case 2:
									case 3:
										
										foreach($arrCacheGrupos as $pClase)
										{
											$resAsistencia=0;
											
											switch($arrHorario[$posHorario][20][$pClase][40])
											{
												case "1"://Registro de entrada con omisión de salida
													$resAsistencia=4;
												break;
												case "2"://Registro de entrada con retardo y omisión de salida
													$resAsistencia=11;
												break;
												case "3"://Registro de entrada fuera de los limites permitidos con omisión de salida
													$resAsistencia=5;
												break;
												default:
													$resAsistencia=$arrHorario[$posHorario][20][$pClase][40];
													if($resAsistencia=="")
														$resAsistencia=0;
												break;
											}
											$arrHorario[$posHorario][20][$pClase][40]=$resAsistencia;
										}
										$arrCacheGrupos=array();
										
										
										$arrHorario[$posHorario][20][$posClase][41]=$bClase[30][0];
										if($posClase==sizeof($arrHorario[$posHorario][20])-1)
										{
											
											switch($tipoEvento)
											{
												case "1"://Registro de entrada con omisión de salida
													$tipoEvento=4;
												break;
												case "2"://Registro de entrada con retardo y omisión de salida
													$tipoEvento=11;
												break;
												case "3"://Registro de entrada fuera de los limites permitidos con omisión de salida
													$tipoEvento=5;
												break;
												
												
											}
										}
										
										$arrHorario[$posHorario][20][$posClase][40]=$tipoEvento;
										
										array_push($arrCacheGrupos,$posClase);
										$existeApertura=true;
										
									break;
									case 4: //Salidas
									case 5:
										
										if($existeApertura)
										{
											foreach($arrCacheGrupos as $pClase)
											{
												$resAsistencia=1;
												
												switch($arrHorario[$posHorario][20][$pClase][40])
												{
													case "1"://Registro de entrada 
														/*switch($tipoEvento)
														{
															case 4: //Salida anticipada
																$resAsistencia=2;
															break;
															case 5:  //salida
																$resAsistencia=1;
															break;
														}*/
														$resAsistencia=1;
													break;
													case "2"://Registro de entrada con retardo 
														/*switch($tipoEvento)
														{
															case 4:
																$resAsistencia=10;
															break;
															case 5:
																$resAsistencia=9;
															break;
														}*/
														$resAsistencia=9;
													break;
													case "3"://Registro de entrada fuera de los limites permitidos 
														/*switch($tipoEvento)
														{
															case 4:
																$resAsistencia=8;
															break;
															case 5:
																$resAsistencia=14;
															break;
														}*/
														$resAsistencia=14;
													break;
													default:
														$resAsistencia=$arrHorario[$posHorario][20][$pClase][40];
														if($resAsistencia=="")
															$resAsistencia=1;
													break;
												}
												$arrHorario[$posHorario][20][$pClase][40]=$resAsistencia;
											}
											switch($tipoEvento)
											{
												case 4: //Salida anticipada
													$arrHorario[$posHorario][20][$posClase][40]=2;
												break;
												case 5:  //salida
													$arrHorario[$posHorario][20][$posClase][40]=1;
												break;
											}
											$arrHorario[$posHorario][20][$posClase][42]=$bClase[30][0];
											$arrCacheGrupos=array();
											$existeApertura=false;
										}
										else
										{
											switch($tipoEvento)
											{
												case 4: //Salida anticipada
													$arrHorario[$posHorario][20][$posClase][40]=7;
												break;
												case 5:  //salida
													$arrHorario[$posHorario][20][$posClase][40]=6;
												break;
											}
											$arrHorario[$posHorario][20][$posClase][42]=$bClase[30][0];
											foreach($arrCacheGrupos as $pClase)
											{
												$resAsistencia=0;
												
												$arrHorario[$posHorario][20][$pClase][40]=$resAsistencia;
											}
											$arrCacheGrupos=array();
											$existeApertura=false;
										}
									break;
								}
							
							break;
							default:
								$horaEvento=$horaCero+strtotime($bClase[30][0][8]);
								$tipoEvento=0;
								$procesarEntrada=true;
								if(estaEnLimiteChecaEntrada($bClase,$toleranciaDespuesHoraInicio,$bClase[30][0]))
									$tipoEvento=1;  //Entrada
								else
								{
									if(estaEnLimiteRetardo($bClase,$toleranciaDespuesHoraInicio,$toleranciaFalta,$bClase[30][0]))
										$tipoEvento=2; //Entrada con retardo
									else
									{
										$tipoEvento=3; //Entrada fuera de los limites permitidos
										
										if(estaEnLimiteChecaSalida($bClase,$toleranciaAntesHoraFin,$bClase[30][0]))
										{
											$procesarEntrada=false;
										}
										
										
									}
								}
								if($procesarEntrada)
								{
									foreach($arrCacheGrupos as $pClase)
									{
										$resAsistencia=0;
										switch($arrHorario[$posHorario][20][$pClase][40])
										{
											case "1"://Registro de entrada con omisión de salida
												$resAsistencia=4;
											break;
											case "2"://Registro de entrada con retardo y omisión de salida
												$resAsistencia=11;
											break;
											case "3"://Registro de entrada fuera de los limites permitidos con omisión de salida
												$resAsistencia=5;
											break;
											default:
												$resAsistencia=$arrHorario[$posHorario][20][$pClase][40];
												if($resAsistencia=="")
													$resAsistencia=0;
											break;
										}
										$arrHorario[$posHorario][20][$pClase][40]=$resAsistencia;
									}
									$arrCacheGrupos=array();
									
									$existeApertura=false;
									$uEvento=$bClase[30][sizeof($bClase[30])-1];
									$tipoEventoSalida=0;
									if(estaEnLimiteChecaSalida($bClase,$toleranciaAntesHoraFin,$uEvento))
											$tipoEventoSalida=5;//Salida
										else
											$tipoEventoSalida=4; //Salida anticipada
									
									
									switch($tipoEvento)
									{
										case 1://Entradas
											
											switch($tipoEventoSalida)
											{
												case 4: //Salida anticipada
													$tipoEvento=2;
												break;
												case 5: //Salida
													$tipoEvento=1;
												break;
											}
										break;
										case 2:
											
											switch($tipoEventoSalida)
											{
												case 4: //Salida anticipada
													$tipoEvento=10;
												break;
												case 5: //Salida
													$tipoEvento=9;
												break;
											}
										break;
										case 3:
											
											switch($tipoEventoSalida)
											{
												case 4: //Salida anticipada
													$tipoEvento=8;
												break;
												case 5: //Salida
													$tipoEvento=14;
												break;
											}
										break;
										
										
									}
									$arrHorario[$posHorario][20][$posClase][41]=$bClase[30][0];
									$arrHorario[$posHorario][20][$posClase][42]=$uEvento;
									$arrHorario[$posHorario][20][$posClase][40]=$tipoEvento;
								}
								else
								{
									$horaEvento=$horaCero+strtotime($bClase[30][sizeof($bClase[30])-1][8]);
									$tipoEvento=5;
									
									if($existeApertura)
									{
										foreach($arrCacheGrupos as $pClase)
										{
											$resAsistencia=1;
											
											switch($arrHorario[$posHorario][20][$pClase][40])
											{
												case "1"://Registro de entrada 
													$resAsistencia=1;
												break;
												case "2"://Registro de entrada con retardo 
													$resAsistencia=9;
												break;
												case "3"://Registro de entrada fuera de los limites permitidos 
													$resAsistencia=14;
												break;
												default:
													$resAsistencia=$arrHorario[$posHorario][20][$pClase][40];
													if($resAsistencia=="")
														$resAsistencia=1;
												break;
												
											}
											$arrHorario[$posHorario][20][$pClase][40]=$resAsistencia;
										}
										$arrHorario[$posHorario][20][$posClase][40]=1;
										$arrHorario[$posHorario][20][$posClase][42]=$bClase[30][sizeof($bClase[30])-1];
										$arrCacheGrupos=array();
										$existeApertura=false;
									}
									else
									{
										$arrHorario[$posHorario][20][$posClase][40]=6;
										$arrHorario[$posHorario][20][$posClase][42]=$bClase[30][sizeof($bClase[30])-1];
										foreach($arrCacheGrupos as $pClase)
										{
											$resAsistencia=0;
											
											$arrHorario[$posHorario][20][$pClase][40]=$resAsistencia;
										}
										$arrCacheGrupos=array();
										$existeApertura=false;
									}	
									
								}
							
							break;
						}
						$posClase++;
					}
					
					if(sizeof($arrCacheGrupos)>0)
					{
						foreach($arrCacheGrupos as $pClase)
						{
							$resAsistencia=0;
							
							switch($arrHorario[$posHorario][20][$pClase][40])
							{
								case "1"://Registro de entrada con omisión de salida
									$resAsistencia=4;
								break;
								case "2"://Registro de entrada con retardo y omisión de salida
									$resAsistencia=11;
								break;
								case "3"://Registro de entrada fuera de los limites permitidos con omisión de salida
									$resAsistencia=5;
								break;
								default:
									$resAsistencia=$arrHorario[$posHorario][20][$pClase][40];
									if($resAsistencia=="")
										$resAsistencia=0;
								break;
							}
							$arrHorario[$posHorario][20][$pClase][40]=$resAsistencia;
						}
						$arrCacheGrupos=array();
					}
				}
				
				$posHorario++;
			}			
			
			
			
			
			foreach($arrHorario as $bHorario)
			{
				if($bHorario[45]==0)
					break;
				foreach($bHorario[20] as $bClase)
				{
					$tipoEvento=$bClase[40];
					$idGrupo=$bClase[1];
					$horaInicioBloque=$bClase[3];
					$horaFinBloque=$bClase[4];
					
					switch($tipoEvento)
					{
						case 0:
							$consulta="select * from 4559_controlDeFalta where idGrupo=".$idGrupo." and fechaFalta='".$fecha."' and horaInicial='".$horaInicioBloque."'";
							$fFalta=$con->obtenerPrimeraFila($consulta);
							if(!$fFalta)
							{
								$query[$x]="INSERT INTO 4559_controlDeFalta(idGrupo,idUsuario,horaInicial,horaFinal,justificado,fechaFalta)
											VALUES(".$idGrupo.",".$idUsuario.",'".$horaInicioBloque."','".$horaFinBloque."',0,'".$fecha."')";
								$x++;
							}
						break;
						default:
							$eventoCerrado=1;	
							
							$horaEntrada=$bClase[3];
							if(isset($bClase[41])&&($bClase[41]))
								$horaEntrada=$bClase[41][8];
							$horaSalida=$bClase[4];
							if(isset($bClase[42])&&($bClase[42]))
								$horaSalida=$bClase[42][8];
							
							$valorEvento=$arrTipoIncidencias["".$tipoEvento];
							$tRetardo=null;
							$esRetardo=null;
							$hInicio=strtotime("+".$toleranciaDespuesHoraInicio." minutes",strtotime($horaInicioBloque));
							$horaActual=strtotime($horaEntrada);
							$diferencia="";
							if($horaActual>$hInicio)
							{
								$tRetardo=$horaCero+$horaActual-$hInicio;
								$esRetardo=1;
							}
							else
							{
								$tRetardo=$horaCero;
								$esRetardo=0;
							}
							
							$hSalida=strtotime("-".$toleranciaAntesHoraFin." minutes",strtotime($horaFinBloque));
							$horaActual=strtotime($horaSalida);
							$tAnticipado=$horaCero;
							if($hSalida>$horaActual)
								$tAnticipado=$horaCero+$hSalida-$horaActual;
							
							$tExtra=$horaCero;	
							$hSalida=strtotime($horaFinBloque);
							if($horaActual>$hSalida)
							{
								$tExtra=$horaCero+$horaActual-$hSalida;
							}
							$horaEntrada="'".$horaEntrada."'";
							$horaSalida="'".$horaSalida."'";
							if($valorEvento==3)
							{
								/*
								4 	Registro de entrada con omisión de salida
								11	Registro de entrada con retardo y omisión de salida
								5 	Registro de entrada fuera de los limites permitidos con omisión de salida
								7 	Registro de salida anticipada con omisión de entrada
								6 	Registro de salida con omisión de entrada*/

								switch($tipoEvento)
								{
									
									case 4:
										$horaSalida="NULL";
									break;
									case 5:
										$horaSalida="NULL";
									break;
									case 6:
										$horaEntrada="NULL";
									break;
									case 7:
										$horaEntrada="NULL";
									break;
									case 11:
										$horaSalida="NULL";
									break;
									
								}
								
									
							}
							$horasBloque=obtenerNumeroHorasBloque($bClase[1],$bClase[3],$bClase[4],$bClase[7],$arrRecesos);
							$esBloqueExcepcion=false;
							if($horasBloque>$noHorasBloqueExcepcion)
							{
									
								switch($tipoEvento)
								{
									case 2://Registro de entrada con salida anticipada
										if(estaEnLimiteChecaSalida($bClase,$tActicipoBloqueExcepcion,$bClase[42]))
										{
											$esBloqueExcepcion=true;
										}
									break;
									
									
									case 8://Registro de entrada fuera de los límites permitidos con salida anticipada
									case 10://Registro de entrada con retardo y salida anticipada
										if((estaEnLimiteRetardo($bClase,$tRetardoBloqueExcepcion,$toleranciaFalta,$bClase[41]))&&(estaEnLimiteChecaSalida($bClase,$tActicipoBloqueExcepcion,$bClase[42])))
										{
											$esBloqueExcepcion=true;
										}
									break;
									case 9://Registro de entrada con retardo
									case 14://Registro de entrada fuera de los límites permitidos con salida correcta
										if(estaEnLimiteRetardo($bClase,$tRetardoBloqueExcepcion,$toleranciaFalta,$bClase[41]))
										{
											$esBloqueExcepcion=true;
										}
									break;
									
										
									
								}
								
							}
							
							if(!$esBloqueExcepcion)
							{
								$query[$x]="INSERT INTO 9105_controlAsistencia(idUsuario,fecha,hora_entrada,hora_salida,tipo,tRetardo,esRetardo,tAnticipado,tExtra,idGrupo,
											tipoEvento,horaInicioBloque,valorEvento,horaFinBloque)
											VALUES(".$idUsuario.",'".$fecha."',".$horaEntrada.",".$horaSalida.",".$eventoCerrado.",'".convertirSegundosTiempo($tRetardo)."',".$esRetardo.",'".convertirSegundosTiempo($tAnticipado)."','".convertirSegundosTiempo($tExtra)."',".$idGrupo.
											",".$tipoEvento.",'".$horaInicioBloque."',".$valorEvento.",'".$horaFinBloque."')";
							
								$x++;
							}
							else
							{
								$horaGrupo=obtenenerDuracionHoraGrupo($bClase[1]);
								$hEntrada="";
								if(isset($bClase[41][3]))
									$hEntrada=$bClase[41][3];
								$hSalida=$bClase[42][3];
								$eventoCerrado=1;
								
								switch($tipoEvento)
								{
									case 2://Registro de entrada con salida anticipada
										$hEntrada=strtotime($bClase[3]);
										$hEntradaPago=strtotime($bClase[3]);
										$hSalidaFaltaRetardo=strtotime("-".$horaGrupo." minutes",strtotime($bClase[4]));


										$query[$x]="INSERT INTO 9105_controlAsistencia(idUsuario,fecha,hora_entrada,hora_salida,tipo,tRetardo,esRetardo,tAnticipado,tExtra,idGrupo,tipoEvento,horaInicioBloque,valorEvento,horaFinBloque)
													VALUES(".$idUsuario.",'".$fecha."','".date("H:i:s",$hSalidaFaltaRetardo)."','".date("H:i:s",$hSalida)."',1,'".convertirSegundosTiempo($tRetardo)."',".$esRetardo.",'".convertirSegundosTiempo($tAnticipado)."','".
													convertirSegundosTiempo($horaCero)."',".$bClase[1].",17,'".date("H:i:s",$hSalidaFaltaRetardo)."',3,'".date("H:i:s",strtotime($bClase[4]))."')";
										
										$x++;
										
										
										
										$tRetardo=$horaCero;
										$esRetardo=0;
										$tAnticipado=$horaCero;
										$tExtra=$horaCero;
										$resEvento=18;
										$hSalida=$hSalidaFaltaRetardo;
										$hSalidaPago=$hSalida;
										
										
									break;
									case 8://Registro de entrada fuera de los límites permitidos con salida anticipada
									case 10://Registro de entrada con retardo y salida anticipada
										
										if(estaEnLimiteRetardo($bClase,$tRetardoBloqueExcepcion,$toleranciaFalta,$bClase[41]))
										{
											$hEntradaPago=strtotime($bClase[3]);
											$hSalidaFaltaRetardo=strtotime("+".$horaGrupo." minutes",$hEntradaPago);

											$query[$x]="INSERT INTO 9105_controlAsistencia(idUsuario,fecha,hora_entrada,hora_salida,tipo,tRetardo,esRetardo,tAnticipado,tExtra,idGrupo,tipoEvento,horaInicioBloque,valorEvento,horaFinBloque)
														VALUES(".$idUsuario.",'".$fecha."',".$horaEntrada.",'".date("H:i:s",$hSalidaFaltaRetardo)."',1,'".convertirSegundosTiempo($tRetardo)."',".$esRetardo.",'".convertirSegundosTiempo($horaCero)."','".
														convertirSegundosTiempo($horaCero)."',".$bClase[1].",16,'".date("H:i:s",strtotime($bClase[3]))."',3,'".date("H:i:s",$hSalidaFaltaRetardo)."')";
											$x++;
											$hEntradaPago=$hSalidaFaltaRetardo;
											if(isset($arrRecesos[$bClase[7]]))
											{
												if(isset($arrRecesos[$bClase[7]][date("H:i:s",$hEntradaPago)]))
													$hEntradaPago=strtotime($arrRecesos[$bClase[7]][date("H:i:s",$hEntradaPago)]);
											}

											$hEntrada=$hEntradaPago;
											$tRetardo=$horaCero;
											$esRetardo=0;
											
											$tExtra=$horaCero;
											$resEvento=18;
											$hSalidaPago=strtotime($bClase[4]);
										}
										if(estaEnLimiteChecaSalida($bClase,$tActicipoBloqueExcepcion,$bClase[42]))
										{

											
											$hSalidaFaltaRetardo=strtotime("-".$horaGrupo." minutes",strtotime($bClase[4]));

											$query[$x]="INSERT INTO 9105_controlAsistencia(idUsuario,fecha,hora_entrada,hora_salida,tipo,tRetardo,esRetardo,tAnticipado,tExtra,idGrupo,tipoEvento,horaInicioBloque,valorEvento,horaFinBloque)
														VALUES(".$idUsuario.",'".$fecha."','".date("H:i:s",$hSalidaFaltaRetardo)."','".date("H:i:s",$hSalida)."',1,'".convertirSegundosTiempo($tRetardo)."',".$esRetardo.",'".convertirSegundosTiempo($tAnticipado)."','".
														convertirSegundosTiempo($horaCero)."',".$bClase[1].",17,'".date("H:i:s",$hSalidaFaltaRetardo)."',3,'".date("H:i:s",strtotime($bClase[4]))."')";
											$x++;
										
										
										
											$tRetardo=$horaCero;
											$esRetardo=0;
											$tAnticipado=$horaCero;
											$tExtra=$horaCero;
											$resEvento=18;
											$hSalida=$hSalidaFaltaRetardo;
											$hSalidaPago=$hSalida;
										}
										

									break;
									case 9://Registro de entrada con retardo
									case 14://Registro de entrada fuera de los límites permitidos con salida correcta
										$hEntradaPago=strtotime($bClase[3]);
										$hSalidaFaltaRetardo=strtotime("+".$horaGrupo." minutes",$hEntradaPago);

										$query[$x]="INSERT INTO 9105_controlAsistencia(idUsuario,fecha,hora_entrada,hora_salida,tipo,tRetardo,esRetardo,tAnticipado,tExtra,idGrupo,tipoEvento,horaInicioBloque,valorEvento,horaFinBloque)
													VALUES(".$idUsuario.",'".$fecha."',".$horaEntrada.",'".date("H:i:s",$hSalidaFaltaRetardo)."',1,'".convertirSegundosTiempo($tRetardo)."',".$esRetardo.",'".convertirSegundosTiempo($horaCero)."','".
													convertirSegundosTiempo($horaCero)."',".$bClase[1].",16,'".date("H:i:s",strtotime($bClase[3]))."',3,'".date("H:i:s",$hSalidaFaltaRetardo)."')";
										$x++;
										$hEntradaPago=$hSalidaFaltaRetardo;
										if(isset($arrRecesos[$bClase[7]]))
										{
											if(isset($arrRecesos[$bClase[7]][date("H:i:s",$hEntradaPago)]))
												$hEntradaPago=strtotime($arrRecesos[$bClase[7]][date("H:i:s",$hEntradaPago)]);
										}

										$hEntrada=$hEntradaPago;
										$tRetardo=$horaCero;
										$esRetardo=0;
										$tAnticipado=$horaCero;
										$tExtra=$horaCero;
										$resEvento=18;
										$hSalidaPago=strtotime($bClase[4]);
									break;
								}
								$resEvento=18;
								$arrTipoIncidencias["".$resEvento]=1;
								
								$query[$x]="INSERT INTO 9105_controlAsistencia(idUsuario,fecha,hora_entrada,hora_salida,tipo,tRetardo,esRetardo,tAnticipado,tExtra,idGrupo,tipoEvento,horaInicioBloque,valorEvento,horaFinBloque)
												VALUES(".$idUsuario.",'".$fecha."','".date("H:i:s",$hEntrada)."','".date("H:i:s",$hSalida)."',".$eventoCerrado.",'".convertirSegundosTiempo($tRetardo)."',".$esRetardo.",'".convertirSegundosTiempo($tAnticipado)."','".
												convertirSegundosTiempo($tExtra)."',".$bClase[1].",".$resEvento.",'".date("H:i:s",$hEntradaPago)."',".$arrTipoIncidencias["".$resEvento].",'".date("H:i:s",$hSalidaPago)."')";
								$x++;
							}
							

							
							
						break;
						
					}
					
				}
			}
			
			
			
			$listEventos="";			
			
			if(!$existenBloquesPendientes)
			{
				registrarAccionesEvento($arrFilas,$query,$x,$horaMarca);
				foreach($arrFilas as $h)
				{
					if($listEventos=="")
						$listEventos=$h[0];
					else
						$listEventos.=",".$h[0];
				}
			}
			else
			{
				$arrEventosGuardar=array();
				$posUltimo=-1;
				$numEventos=sizeof($arrFilas);
				for($ct=0;$ct<$numEventos;$ct++)
				{
					if(isset($arrFilas[$ct][10])&&($arrFilas[$ct][10]==1))
					{
						$posUltimo=$ct;
					}
				}
				
				for($ct=0;$ct<=$posUltimo;$ct++)
				{
					array_push($arrEventosGuardar,$arrFilas[$ct]);
					if($listEventos=="")
						$listEventos=$arrFilas[$ct][0];
					else
						$listEventos.=",".$arrFilas[$ct][0];
				}
				registrarAccionesEvento($arrEventosGuardar,$query,$x,$horaMarca);
				
			}
			if($listEventos=="")
				$listEventos=-1;
				
			$query[$x]="delete from 9105_eventosRecibidos where idEvento in(".$listEventos.") ";
			$x++;
			$query[$x]="commit";
			$x++;
			//varDump($query);
			//return;
			if(!$con->ejecutarBloque($query))
				return false;
		}
		return true;
	}
	
	function cumpleEventoPermitidoPlantel($validarPlantel,$bHorario,$ev,$arrPlantelesBiometrico)
	{
		return true;
		if(!$validarPlantel)
			return true;
		if(!isset($arrPlantelesBiometrico[$ev[5]][$bHorario[7]]))
		{
			return false;
		}
		return true;
	}
	
	function estaEnlimiteBloqueHorario($bloque,$ev)
	{
		if(($ev[3]>=$bloque[8])&&($ev[3]<=$bloque[9]))
		{
			return true;
		}
		return false;
	}
	
	function obtenerFechaMinimaOperacionAMES($fechaBase,$tOperacion,$plantel)
	{
		global $con;
		$fechaFinal=null;
		$fBase=strtotime($fechaBase);
		$consulta="SELECT * FROM 4604_configuracionModulosAcademicos";
		$fOperacion=$con->obtenerPrimeraFila($consulta);
		if(!$fOperacion)
		{
			$fOperacion[1]=0;
			$fOperacion[2]=0;
			$fOperacion[3]=0;	
			$fOperacion[4]=1;	
		}
		$fechaFinal=strtotime("-".$fOperacion[$tOperacion]." days",$fBase);
		if($fOperacion[4]==1)
		{
			$fechaFinal=date("Y-m-d",$fechaFinal);
			
			$fechaFinal=obtenerDiaHabilAnterior($fechaFinal,$plantel);	
			
			$fechaFinal=strtotime($fechaFinal);
		}
		
			
			
		return date("Y-m-d",$fechaFinal);
	}
	
	function obtenerCodigoCriterioEvaluacion($idGrupo,$idCriterio,$tipoEvaluacion,$noEvaluacion)
	{
		global $con;	
		$consulta="SELECT idPlanEstudio,idInstanciaPlanEstudio,idMateria FROM 4520_grupos WHERE idGrupos=".$idGrupo;
		$fGrupo=$con->obtenerPrimeraFila($consulta);
		$idPlanEstudio=$fGrupo[0];
		$idInstanciaPlanEstudio=$fGrupo[1];
		$idMateria=$fGrupo[2];
		$consulta="SELECT idPerfil,calificacionMinimaAprobatoria FROM 4592_configuracionPerfilEvaluacion WHERE idPlanEstudio=".$idPlanEstudio." AND idInstanciaPlanEstudio in(".$idInstanciaPlanEstudio.",-1) AND idMateria=".$idMateria." and
				idGrupo in (".$idGrupo.",-1) AND tipoExamen=".$tipoEvaluacion." AND noExamen=".$noEvaluacion." order by idGrupo desc,idInstanciaPlanEstudio desc";

		$fPerfil=$con->obtenerPrimeraFila($consulta);
		$idPerfil=$fPerfil[0];
		if($idPerfil=="")
			$idPerfil=-1;
		$consulta="SELECT codigoUnidad FROM 4564_criteriosEvaluacionPerfilMateria WHERE idPerfil=".$idPerfil." AND idCriterio=".$idCriterio;
		$codigoUnidad=$con->obtenerValor($consulta);
		return $codigoUnidad;
		
	} 
	
	function obtenerCalificacionMinimaAprobatoria($idGrupo,$tipoEvaluacion,$noEvaluacion)
	{
		global $con;	
		$consulta="SELECT idPlanEstudio,idInstanciaPlanEstudio,idMateria FROM 4520_grupos WHERE idGrupos=".$idGrupo;
		$fGrupo=$con->obtenerPrimeraFila($consulta);
		$idPlanEstudio=$fGrupo[0];
		$idInstanciaPlanEstudio=$fGrupo[1];
		$idMateria=$fGrupo[2];
		$consulta="SELECT idPerfil,calificacionMinimaAprobatoria FROM 4592_configuracionPerfilEvaluacion WHERE idPlanEstudio=".$idPlanEstudio." AND idInstanciaPlanEstudio in(".$idInstanciaPlanEstudio.",-1) AND idMateria=".$idMateria." and
				idGrupo in (".$idGrupo.",-1) AND tipoExamen=".$tipoEvaluacion." AND noExamen=".$noEvaluacion." order by idGrupo desc,idInstanciaPlanEstudio desc";

		$fPerfil=$con->obtenerPrimeraFila($consulta);
		
		return $fPerfil[1];
		
	} 
	
	function asentarCalificacionCriterio($idGrupo,$codigoCriterio,$tipoEvaluacion,$noEvaluacion,$idUsuario,$calificacion)
	{
		global $con;	
		
		$consulta="select idCalificacion from 4568_calificacionesCriteriosAlumnoPerfilMateria WHERE idAlumno=".$idUsuario." AND idGrupo=".$idGrupo." 
				AND idCriterio='".$codigoCriterio."' AND tipoEvaluacion=".$tipoEvaluacion." AND noEvaluacion=".$noEvaluacion;
		$idCalificacion=$con->obtenerValor($consulta);
		
		if($idCalificacion=="")
		{
			$consulta="INSERT INTO 4568_calificacionesCriteriosAlumnoPerfilMateria(idAlumno,idGrupo,idCriterio,bloque,valor,tipoEvaluacion,noEvaluacion)
						VALUES(".$idUsuario.",".$idGrupo.",'".$codigoCriterio."',0,".$calificacion.",".$tipoEvaluacion.",".$noEvaluacion.")";
		}
		else
		{
			$consulta="UPDATE 4568_calificacionesCriteriosAlumnoPerfilMateria SET valor=".$calificacion." WHERE idCalificacion=".$idCalificacion;
		}
		
		if($con->ejecutarConsulta($consulta))
		{
			return recalcularCalificacionesGrupo($idGrupo,$codigoCriterio,0,$tipoEvaluacion,$noEvaluacion,$idUsuario);
		}
		
	}
	
	function cerrarRegistroEvaluacion($idGrupo,$tipoEvaluacion,$noEvaluacion)
	{
		global $con;	
		$consulta="SELECT idSituacionAplicacionEvaluacion FROM 4593_situacionEvaluacionCurso WHERE idGrupo=".$idGrupo." AND tipoExamen=".$tipoEvaluacion." AND noExamen=".$noEvaluacion;
		$idSituacion=$con->obtenerValor($consulta);
		if($idSituacion=="")
		{
			$consulta="INSERT INTO 4593_situacionEvaluacionCurso(idGrupo,tipoExamen,noExamen,situacion,idResponsable,fechaRegistro)
						VALUES(".$idGrupo.",".$tipoEvaluacion.",".$noEvaluacion.",2,".$_SESSION["idUsr"].",'".date("Y-m-d H:i:s")."')";
					
		}
		else
			$consulta="update 4593_situacionEvaluacionCurso set situacion=2 where idSituacionAplicacionEvaluacion=".$idSituacion;
		return $con->ejecutarConsulta($consulta);
	}
	
	
	function obtenerListadoAlumnosEvaluacionDemandaGrupo($idGrupo,$tipoExamen,$noExamen)
	{
		global $con;
		$consulta="SELECT idUsuarioRegistro FROM _736_tablaDinamica WHERE idGrupo=".$idGrupo." AND idTipoExamen=".$tipoExamen." AND noEvaluacion=".$noExamen;

		$arrAlumnos=array();
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$obj["idUsuario"]=$fila[0];
			array_push($arrAlumnos,$obj);
			
		}
		return $arrAlumnos;		
	}
	
	function permiteCapturaEvaluacionDemandaGrupo($idGrupo,$tipoExamen,$noExamen,$objUsr)
	{
		
		global $con;
		$oResultado["registraCalificacion"]=1;
		$consulta="SELECT idEstado FROM _736_tablaDinamica WHERE idUsuarioRegistro=".$objUsr["idUsuario"]." and idGrupo=".$idGrupo." AND idTipoExamen=".$tipoExamen." AND noEvaluacion=".$noExamen;
		$idEstado=$con->obtenerValor($consulta);
		if($idEstado!=4)
		{
			$oResultado["registraCalificacion"]=0;
			$oResultado["comentarios"]="El alumno NO ha cubierto el pago correspondiente a la evaluación";
			$oResultado["totalEvaluacion"]=-5;
		}
		
		return $oResultado;
		
	}
	
	
	function obtenerMateriasIncumplePrerrequisitos($idAlumno,$idMateria)
	{
		global $con;
		
		$arrMateriaIncumple=array();
		$consulta="SELECT id__721_tablaDinamica FROM _721_tablaDinamica WHERE cmbCalificacionfinal=1";
		$listEvaluacionesFinales=$con->obtenerListaValores($consulta);
		if($listEvaluacionesFinales=="")
			$listEvaluacionesFinales=-1;
		$arrMateriasPrerrequisito=obtenerMateriasPrerrequisito($idMateria);
		if(sizeof($arrMateriasPrerrequisito)>0)
		{
			foreach($arrMateriasPrerrequisito as $m=>$resto)	
			{
				
				if(!esMateriaAcreditada($idAlumno,$m))
				{
					array_push($arrMateriaIncumple,$m);	
					
				}
					
			}
		}
		return $arrMateriaIncumple;
	}
	
	function esMateriaAcreditada($idAlumno,$idMateria)
	{
		global $con;
		$arrMateriaIncumple=array();
		$consulta="SELECT id__721_tablaDinamica FROM _721_tablaDinamica WHERE cmbCalificacionfinal=1";
		$listEvaluacionesFinales=$con->obtenerListaValores($consulta);
		if($listEvaluacionesFinales=="")
			$listEvaluacionesFinales=-1;
		$consulta="SELECT count(*) FROM 4569_calificacionesEvaluacionAlumnoPerfilMateria WHERE idAlumno=".$idAlumno." AND tipoEvaluacion IN(".$listEvaluacionesFinales.") AND idMateria=".$idMateria." and aprobado=1";
		$nReg=$con->obtenerValor($consulta);
		if($nReg==0)
			return false;
		return true;
	}
	
	function inscribirAlumnoOfertaAcademica($idRegistro,$idAlumno,$idInstanciaPlan,$idPlanPagos,$idCiclo,$idPeriodo,$idGradoInscribe,$plantel,$noInscripcion)
	{
		global $con;
		$idFormulario=910;
		$consulta="SELECT p.idProgramaEducativo,i.sede,p.idPlanEstudio FROM 4513_instanciaPlanEstudio i,4500_planEstudio p 
					WHERE p.idPlanEstudio=i.idPlanEstudio AND i.idInstanciaPlanEstudio=".$idInstanciaPlan;
		$fInstancia=$con->obtenerPrimeraFila($consulta);
		
		$plantel=$fInstancia[1];
		$arrFechasPago=array();
		$idProgramaEducativo=$fInstancia[0];
		$idPlanEstudio=$fInstancia[2];
		
		$consulta="SELECT idOfertaAcademica FROM 4608_ofertaCargasAcademicas WHERE idFormulario=".$idFormulario." AND idReferencia=".$idRegistro." AND cargaSeleccionada=1";
		$idOfertaAcademica=$con->obtenerValor($consulta);
		
		$xAux=0;
		$queryAux[$xAux]="begin";
		$xAux++;
		
		$consulta="SELECT * FROM 4537_situacionActualAlumno WHERE idAlumno=".$idAlumno." AND idInstanciaPlanEstudio=".$idInstanciaPlan;
		$idAlumnoSituacion=$con->obtenerValor($consulta);
		if($idAlumnoSituacion=="")
		{
			$queryAux[$xAux]="INSERT INTO 4537_situacionActualAlumno(idAlumno,idInstanciaPlanEstudio,situacionAlumno,idPlanPagos)
							VALUES(".$idAlumno.",".$idInstanciaPlan.",1,".$idPlanPagos.")";
			$xAux++;		
		}
		else
		{
			$queryAux[$xAux]="UPDATE 4537_situacionActualAlumno SET situacionAlumno=1 ,idPlanPagos=".$idPlanPagos." WHERE idAlumnoSituacion=".$idAlumnoSituacion;
			$xAux++;
		}
			
		$queryAux[$xAux]="INSERT INTO 4529_alumnos(idCiclo,idPeriodo,idInstanciaPlanEstudio,idGrado,idGrupo,idUsuario,plantel,estado,noInscripcion)
						VALUES(".$idCiclo.",".$idPeriodo.",".$idInstanciaPlan.",".$idGradoInscribe.",NULL,".$idAlumno.",'".$plantel."',1,".$noInscripcion.")";
		$xAux++;
		$consulta="SELECT idMateria,idGrupo FROM 4609_elementosOfertaAcademica WHERE idOfertaAcademica=".$idOfertaAcademica;
		$resGpo=$con->obtenerFilas($consulta);
		while($fGpo=mysql_fetch_row($resGpo))
		{
			$queryAux[$xAux]="INSERT INTO 4517_alumnosVsMateriaGrupo(idUsuario,situacion,idGrupo,idMateria,idPlanEstudio,idInstanciaPlan,idCiclo,idPeriodo)
							VALUES(".$idAlumno.",1,".$fGpo[1].",".$fGpo[0].",".$idPlanEstudio.",".$idInstanciaPlan.",".$idCiclo.",".$idPeriodo.");";	
			$xAux++;
		}
		
		$consulta="SELECT idCiclo,idPeriodo,idGrado FROM 4529_alumnos WHERE idUsuario=".$idAlumno." AND idInstanciaPlanEstudio=".$idInstanciaPlan.
					" ORDER BY idAlumnoTabla desc";
		$fCiclo=$con->obtenerPrimeraFila($consulta);
		$arrMateriasCurso=obtenerMateriasCurso($idAlumno,$fCiclo[2],$idInstanciaPlan,$fCiclo[0],$fCiclo[1]);

		foreach($arrMateriasCurso as $materia)
		{
			
			if(!esMateriaAcreditada($idAlumno,$materia))
			{
				
				$queryAux[$xAux]="INSERT INTO 4607_materiasAdeudoAlumno(idAlumno,idMateria,idCiclo,idPeriodo,situacion,idInstanciaPlan)
									VALUES(".$idAlumno.",".$materia.",".$fCiclo[0].",".$fCiclo[1].",1,".$idInstanciaPlan.")"	;
				$xAux++;
			}
		}
		$queryAux[$xAux]="commit";
		$xAux++;
		return $con->ejecutarBloque($queryAux);	
	}
	
	function obtenerMateriasCurso($idAlumno,$idGrado,$idInstanciaPlan,$idCiclo,$idPeriodo)
	{
		global $con;
		$arrMaterias=array();
		$consulta="SELECT idMateria FROM 4517_alumnosVsMateriaGrupo WHERE idUsuario=".$idAlumno." and idCiclo=".$idCiclo." AND idPeriodo=".$idPeriodo;

		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			array_push($arrMaterias,$fila[0]);	
		}
		return $arrMaterias;
	}
	
	function esCalificacionFinal($tipoEvaluacion)
	{
		global $con;
		$consulta="SELECT cmbCalificacionfinal FROM _721_tablaDinamica WHERE id__721_tablaDinamica=".$tipoEvaluacion;	
		$calFinal=$con->obtenerValor($consulta);
		if($calFinal=="1")
			return true;
		return false;
	}
	
	function esSistemaEscolarizado($tipoSistema)
	{
		global $con;
		switch($tipoSistema)
		{
			case 6:
				return true;
			break;
			default:
				return false;
			break;
		}
	}
	
	function obtenerNivelEducativoGrupo($idGrupo)
	{
		global $con;
		$consulta="SELECT idPlanEstudio FROM 4520_grupos WHERE idGrupos=".$idGrupo;
		$idPlanEstudio=$con->obtenerValor($consulta);	
		$consulta="SELECT nivelPlanEstudio FROM 4500_planEstudio WHERE idPlanEstudio=".$idPlanEstudio;
		$idNivel=$con->obtenerValor($consulta);
		return $idNivel;
	}
	
	function obtenerModalidadGrupo($idGrupo)
	{
		global $con;
		$consulta="SELECT idInstanciaPlanEstudio FROM 4520_grupos WHERE idGrupos=".$idGrupo;
		$idInstanciaPlanEstudio=$con->obtenerValor($consulta);	
		$consulta="SELECT idModalidad FROM 4513_instanciaPlanEstudio WHERE idInstanciaPlanEstudio=".$idInstanciaPlanEstudio;
		$idNivel=$con->obtenerValor($consulta);
		return $idNivel;
	}
	
	function esGrupoEscolarizado($idGrupo)
	{
		$iModalidad=obtenerModalidadGrupo($idGrupo);	
		return esSistemaEscolarizado($iModalidad);
	}
	
	
	function obtenerHorasSemanaGrupo($idGrupo,$fechaCorte="")
	{
		global $con;
		$horasSemana=0;
		if($fechaCorte=="")
		{
			$consulta="SELECT idInstanciaPlanEstudio,idMateria FROM 4520_grupos WHERE idGrupos=".$idGrupo;
			$fGrupo=$con->obtenerPrimeraFila($consulta);
			$consulta="SELECT noHorasSemana FROM 4512_aliasClavesMateria WHERE idInstanciaPlanEstudio=".$fGrupo[0]." AND idMateria=".$fGrupo[1];
			$horasSemana=$con->obtenerValor($consulta);	
			if($horasSemana=="")
			{
				$consulta="SELECT horasSemana FROM 4502_Materias WHERE idMateria=".$fGrupo[1];
				$horasSemana=$con->obtenerValor($consulta);	
					
			}
			
		}
		else
		{
			$consulta="SELECT horaInicio,horaFin FROM 4522_horarioGrupo WHERE idGrupo=".$idGrupo." AND fechaInicio<='".$fechaCorte."' AND fechaFin>='".$fechaCorte."' AND fechaInicio<=fechaFin";		
			$res=$con->obtenerFilas($consulta);
			while($fila=mysql_fetch_row($res))
			{

				$horasSemana+=obtenerDiferenciaHoraMinutos($fila[0],$fila[1]);
			}
			$duracionHora=obtenenerDuracionHoraGrupo($idGrupo);
			$horasSemana/=$duracionHora;
		}
		return $horasSemana;	
	}

	function obtenerNoBloqueGrupo($idGrupo)
	{
		global $con;
		$consulta="SELECT noBloqueAsociado FROM 4520_grupos WHERE idGrupos=".$idGrupo;
		$nBloque=$con->obtenerValor($consulta);	
		return $nBloque;
	}
	
	function generarReporteAME($idSolicitudAme,$modoDebug=false)
	{
		global $con;
		global $baseDir;
		global $arrDiasSemana;
	
		$libro=new cExcel("../modulosEspeciales_UGM/plantillas/plantillaAMEV3.xlsx",true,"Excel2007");	
		
		$consulta="SELECT * FROM 4549_cabeceraSolicitudAME WHERE idSolicitudAME=".$idSolicitudAme;
		$fCabecera=$con->obtenerPrimeraFila($consulta);
		
		
		$fechaAME=date("Y-m-d",strtotime($fCabecera[4]));
	
		
		$consulta="select upper(unidad) from 817_organigrama where codigoUnidad='".$fCabecera[12]."'";
	
		$plantel=$con->obtenerValor($consulta);
		
		$consulta="SELECT idInstanciaPlanEstudio FROM 4513_instanciaPlanEstudio WHERE sede='".$fCabecera[12]."' AND idModalidad IN (4,7)";
		$listPlanesAbiertos=$con->obtenerListaValores($consulta);
		if($listPlanesAbiertos=="")
			$listPlanesAbiertos=-1;
		$arrPlanesAbiertos=explode(",",$listPlanesAbiertos);
		$libro->setValor("A3",$fCabecera[1]);
		$libro->setValor("B3",$plantel);
		$libro->setValor("B15",date("d/m/Y",strtotime($fCabecera[4])));
		if($fCabecera[5]!="")
			$libro->setValor("G15",date("d/m/Y",strtotime($fCabecera[5])));
		if($fCabecera[7]!="")
			$libro->setValor("M15",date("d/m/Y",strtotime($fCabecera[7])));
			
		$arrResponsables=obtenerResponsablesAMES($idSolicitudAme);
			
		$subdireccionAcademica=$arrResponsables["subDirAcademica"];	
		
		$coordAdministrativa =$arrResponsables["coorAdmon"];	

		$director=$arrResponsables["director"];
		
		$direccionAcademina=$arrResponsables["direccionAcade"];
			
		$coordAcademica=$arrResponsables["coordAcademica"];
		
		$libro->setValor("A12",$subdireccionAcademica);	
		$libro->setValor("F12",$coordAdministrativa);	
		$libro->setValor("L12",$director);	
		$libro->setValor("C21",$direccionAcademina);	
		$libro->setValor("J21",$coordAcademica);	
		
			
		$arrOperaciones=array();
		$arrOperaciones["altas"]=array();
		$arrOperaciones["bajas"]=array();
		$arrOperaciones["cambios"]=array();
		

		$consulta="SELECT * FROM 4548_solicitudesMovimientoGrupo WHERE idSolicitudAME=".$idSolicitudAme;
		
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$obj=json_decode($fila[3]);
			$objAux=array();
			$objAux["obj"]=$obj;
			switch($fila[2])
			{
				//Altas
				case "1":
				case "3":
					$consulta="select idGrupo from 4548_gruposSolicitudesMovimiento WHERE idSolicitud=".$fila[0];
					$idGrupo=$con->obtenerValor($consulta);
					$objAux["idAsignacion"]=$fila[4];
					if($objAux["idAsignacion"]=="")
						$objAux["idAsignacion"]=0;
					$objAux["idGrupo"]=$idGrupo;
					$objAux["accion"]=$fila[2];
					$objAux["motivo"]=$obj->motivo;
					array_push($arrOperaciones["altas"],$objAux);
		
				break;	
				//Bajas
				case "2":
				case "5":
					$consulta="select idGrupo from 4548_gruposSolicitudesMovimiento WHERE idSolicitud=".$fila[0];
					$idGrupo=$con->obtenerValor($consulta);
					$objAux["idAsignacion"]=$fila[4];
					$objAux["idGrupo"]=$idGrupo;
					$objAux["accion"]=$fila[2];
					$objAux["motivo"]=$fila[6];
					$consulta="SELECT idUsuario,fechaAsignacion FROM 4519_asignacionProfesorGrupo WHERE idAsignacionProfesorGrupo=".$fila[4];
					$fAsignacion=$con->obtenerPrimeraFila($consulta);
					$idProfesor=$fAsignacion[0];
					
					
					$objAux["obj"]=array();
					$cadOAux='{"fechaAsignacion":"","fechaBaja":"","idProfesor":""}';
					$oAux=json_decode($cadOAux);
					$oAux->fechaBaja=$obj->fechaBaja;
					$oAux->idProfesor=$idProfesor;
					$oAux->fechaAsignacion=$fAsignacion[1];
					$objAux["obj"]=$oAux;
					array_push($arrOperaciones["bajas"],$objAux);
					
				break;
				//Cambios
				case "4":
					if(sizeof($obj->horarioCambio)==0)
						continue;
					$consulta="select idGrupo from 4548_gruposSolicitudesMovimiento WHERE idSolicitud=".$fila[0];
					$idGrupo=$con->obtenerValor($consulta);
					$fechaAux=date("Y-m-d",strtotime($obj->fechaAplicacion));//date("Y-m-d",strtotime("-1 days",strtotime($obj->fechaAplicacion)));
					
					$arrAsignaciones=obtenerFechasAsignacionGrupoV2($idGrupo,$idSolicitudAme,true,true,$fechaAux,$fechaAux,1);
					
					$arrSuplencias=obtenerFechasAsignacionGrupoV2($idGrupo,$idSolicitudAme,true,true,$fechaAux,$fechaAux,3);
					if(sizeof($arrSuplencias)>0)
						$arrAsignaciones=$arrSuplencias;
					
					if(sizeof($arrAsignaciones)>0)
					{
						foreach($arrAsignaciones as $asignacion)
						{
							$idGrupo=$idGrupo;
							if($asignacion[6]!=$obj->fechaAplicacion)
							{
								$uFecha=date("Y-m-d",strtotime("-1 days",strtotime($obj->fechaAplicacion)));
								$arrHorarioAux=obtenerFechasHorarioGrupoV2($idGrupo,$idSolicitudAme,true,true,$asignacion[6],$uFecha);
								if(sizeof($arrHorarioAux)>0)
								{
									$ultimaFechaTitular="";
									$bufferFechas=array();
									foreach($arrHorarioAux as $hAux)
									{
										if(!isset($bufferFechas[$hAux[6]."_".$hAux[7]]))
										{
											$arrAsignacionesTmp=obtenerFechasAsignacionGrupoV2($idGrupo,$idSolicitudAme,true,true,$hAux[6],$hAux[7],0,true);
											$bufferFechas[$hAux[6]."_".$hAux[7]]=1;
											if(sizeof($arrAsignacionesTmp)>0)
											{
												foreach($arrAsignacionesTmp as $aTmp)	
												{
													if($aTmp[5]==$asignacion[5])
													{
														$ultimaFechaTitular=$hAux[6];
														break;
													}
												}
											}
										}
									}
									
									$arrHorarioAux=obtenerFechasHorarioGrupoV2($idGrupo,$idSolicitudAme,true,true,$ultimaFechaTitular,$ultimaFechaTitular);
									if(sizeof($obj->horarioCambio)==sizeof($arrHorarioAux))
									{
										$numIguales=0;
										foreach($obj->horarioCambio as $hC)
										{
											foreach($arrHorarioAux as $hA)
											{
												if((strtotime($hC->horaInicial)==strtotime($hA[3]))&&(strtotime($hC->horaFinal)==strtotime($hA[4]))&&($hC->dia==$hA[2]))
												{
													$numIguales++;
													break;	
												}
											}
										}
										
										
										if($numIguales==sizeof($obj->horarioCambio))
										{
											continue;
										}
										
									}
									
									$objAux=array();
									$objAux["idAsignacion"]=$asignacion[0];
									/*$consulta="SELECT idUsuario,fechaAsignacion FROM 4519_asignacionProfesorGrupo WHERE idAsignacionProfesorGrupo=".$asignacion[0];
									$fAsignacion=$con->obtenerPrimeraFila($consulta);*/
									$idProfesor=$asignacion[5];
									$objAux["idGrupo"]=$idGrupo;
									$objAux["accion"]=2;
									$objAux["virtual"]=1;
									$objAux["obj"]=array();
									$objAux["motivo"]="[Cambio de horario] ".$obj->motivo;
									$cadOAux='{"fechaAsignacion":"","fechaBaja":"","idProfesor":""}';
									$oAux=json_decode($cadOAux);
									$oAux->fechaBaja=date("Y-m-d",strtotime("-1 days",$fechaAux));
									$oAux->idProfesor=$idProfesor;
									$oAux->fechaAsignacion=$asignacion[6];
									$objAux["obj"]=$oAux;
									array_push($arrOperaciones["bajas"],$objAux);
								}
							
							}
							$accion=1;
							if($asignacion[8]==45)
								$accion=3;
							$idGrupo=$idGrupo;
							$objAux=array();
							$objAux["idAsignacion"]=$asignacion[0];
							$objAux["idGrupo"]=$idGrupo;
							$objAux["accion"]=$accion;
							$objAux["motivo"]="[Cambio de horario] ".$obj->motivo;
							$objAux["virtual"]=1;
							$objAux["obj"]=array();
							
							$cadOAux='{"fechaAplicacion":"","fechaTermino":"","motivo":"","idProfesor":""}';
							$oAux=json_decode($cadOAux);
							$oAux->fechaAplicacion=$obj->fechaAplicacion;
							$oAux->fechaTermino=$asignacion[7];
							$oAux->motivo=$obj->motivo;
							$oAux->idProfesor=$asignacion[5];
							
							$objAux["obj"]=$oAux;
							array_push($arrOperaciones["altas"],$objAux);
							
						}
					}
					/*$arrAsignaciones=obtenerFechasAsignacionGrupoV2($idGrupo,$idSolicitudAme,true,true,$obj->fechaAplicacion,$obj->fechaTermino,0);
					if(sizeof($arrAsignaciones)>0)
					{
						foreach($arrAsignaciones as $asignacion)
						{
							
							$idGrupo=$idGrupo;
							$objAux=array();
							$objAux["idAsignacion"]=$asignacion[0];
							$objAux["idGrupo"]=$idGrupo;
							$objAux["accion"]=1;
							$objAux["virtual"]=1;
							$objAux["obj"]=array();
							
							$cadOAux='{"fechaAplicacion":"","fechaTermino":"","motivo":"","idProfesor":""}';
							$oAux=json_decode($cadOAux);
							$oAux->fechaAplicacion=$obj->fechaAplicacion;
							$oAux->fechaTermino=$asignacion[7];
							$oAux->motivo="";
							$oAux->idProfesor=$asignacion[5];
							
							$objAux["obj"]=$oAux;
							array_push($arrOperaciones["altas"],$objAux);
						}
					}*/
				case "6":
					/*$consulta="select idGrupo from 4548_gruposSolicitudesMovimiento WHERE idSolicitud=".$fila[0];
					$idGrupo=$con->obtenerValor($consulta);
					
					if($fila[13]!="")
					{
						$objDesHacer=json_decode($fila[13]);
						if(isset($objDesHacer->arrAsignaciones))
						{
							foreach($objDesHacer->arrAsignaciones as $a)
							{
								if(strtotime($a->fechaAsignacion)<strtotime($a->fechaBaja))
								{
									$objAux=array();
	
									$consulta="SELECT idUsuario,fechaAsignacion FROM 4519_asignacionProfesorGrupo WHERE idAsignacionProfesorGrupo=".$a->idAsignacion;
									$fAsignacion=$con->obtenerPrimeraFila($consulta);
									$idProfesor=$fAsignacion[0];
									$objAux["idAsignacion"]=$a->idAsignacion;
									$objAux["idGrupo"]=$idGrupo;
									$objAux["accion"]=2;
									$objAux["virtual"]=1;
									$objAux["obj"]=array();
									$cadOAux='{"fechaAsignacion":"","fechaBaja":"","idProfesor":"'.$idProfesor.'"}';
									
									$oAux=json_decode($cadOAux);
									$oAux->fechaBaja=strtotime("-1 days",strtotime($a->fechaAsignacion));
									$oAux->fechaAsignacion=$fAsignacion[1];
									$objAux["obj"]=$oAux;
									array_push($arrOperaciones["bajas"],$objAux);
								}
							}
						}
					}
					$consulta="select idGrupo from 4548_gruposSolicitudesMovimiento WHERE idSolicitud=".$fila[0];
					$idGrupo=$con->obtenerValor($consulta);
					$objAux["idGrupo"]=$idGrupo;
					$objAux["accion"]=$fila[2];
					array_push($arrOperaciones["cambios"],$objAux);*/
					
				break;
				case "7":
					foreach($obj->arrCambios as $o)
					{
						$idGrupo=$o->idGrupo;
						if(($o->idProfesorO!="")&&($o->idProfesorO!="0"))
						{
							$consulta="SELECT fechaAsignacion FROM 4519_asignacionProfesorGrupo WHERE idUsuario=".$o->idProfesorO." AND idGrupo IN (".$idGrupo.")";
							
							$fAsignacion=$con->obtenerValor($consulta);
							$objAux=array();
							$objAux["idAsignacion"]=0;
							$objAux["idGrupo"]=$idGrupo;
							$objAux["accion"]=2;
							$objAux["virtual"]=1;
							$objAux["motivo"]="[Intercambio de curso] ".$obj->motivo;
							$objAux["obj"]=array();
							$cadOAux='{"fechaAsignacion":"","fechaBaja":"","idProfesor":""}';
							$oAux=json_decode($cadOAux);
							$oAux->fechaBaja=date("Y-m-d",strtotime("-1 days",strtotime($fAsignacion)));
							$oAux->idProfesor=$o->idProfesorO;
							$oAux->fechaAsignacion=$fAsignacion;
							$objAux["obj"]=$oAux;
							$objAux["horario"]=array();
							$objAux["noBloque"]=$o->noBloqueO;

							foreach($o->arrHorarioO as $h)
							{
								$rHorario=array();
								$rHorario[0]=0;
								$rHorario[1]=$o->idGrupo;
								$rHorario[2]=$h->dia;
								$rHorario[3]=$h->hInicio;
								$rHorario[4]=$h->hFin;
								$rHorario[5]=$h->idAula;
								$rHorario[6]=$h->fInicio;
								$rHorario[7]=$h->fFIn;
								array_push($objAux["horario"],$rHorario);
							}
							
							
							array_push($arrOperaciones["bajas"],$objAux);
						}
						if(($o->idProfesorC!="")&&($o->idProfesorC!="0"))
						{
							$objAux=array();
							$objAux["idAsignacion"]=0;
							$objAux["idGrupo"]=$idGrupo;
							$objAux["accion"]=1;
							$objAux["motivo"]="[Intercambio de curso] ".$obj->motivo;
							$objAux["virtual"]=1;
							$objAux["obj"]=array();
							
							$cadOAux='{"fechaAplicacion":"","fechaTermino":"","motivo":"","idProfesor":""}';
							$oAux=json_decode($cadOAux);
							$oAux->fechaAplicacion=$o->fechaInicioC;
							$oAux->fechaTermino=$o->fechaFinC;
							$oAux->motivo="";
							$oAux->idProfesor=$o->idProfesorC;
							$objAux["noBloque"]=$o->noBloqueC;
							$objAux["horario"]=array();
							foreach($o->arrHorarioC as $h)
							{
								$rHorario=array();
								$rHorario[0]=0;
								$rHorario[1]=$o->idGrupo;
								$rHorario[2]=$h->dia;
								$rHorario[3]=$h->hInicio;
								$rHorario[4]=$h->hFin;
								$rHorario[5]=$h->idAula;
								$rHorario[6]=$h->fInicio;
								$rHorario[7]=$h->fFIn;
								array_push($objAux["horario"],$rHorario);
							}
							
							$objAux["obj"]=$oAux;
							array_push($arrOperaciones["altas"],$objAux);
						}
						//array_push($arrOperaciones["cambios"],$objAux);
					}
				break;
			}	
		}				
	
		$arrProfesores=array();
		if(sizeof($arrOperaciones["bajas"])>0)
		{
			foreach($arrOperaciones["bajas"] as $o)
			{
				
				$fAsignacion=$con->obtenerPrimeraFila($consulta);
				$idProfesor=$o["obj"]->idProfesor;
				if(!isset($arrProfesores[$idProfesor]))
				{
					$arrProfesores[$idProfesor]=array();
					$arrProfesores[$idProfesor]["bajas"]=array();
					
				}
				array_push($arrProfesores[$idProfesor]["bajas"],$o);
				
			}
		}
		
		
		if(sizeof($arrOperaciones["altas"])>0)
		{
			foreach($arrOperaciones["altas"] as $o)
			{
				
				if($o["idAsignacion"]!=0)
			
					$consulta="SELECT g.idInstanciaPlanEstudio,a.idUsuario  FROM 4519_asignacionProfesorGrupo a,4520_grupos g WHERE idAsignacionProfesorGrupo=".$o["idAsignacion"]." AND g.idGrupos=a.idGrupo";
				else
				{
					$consulta="select idInstanciaPlanEstudio,".$o["obj"]->idProfesor." as idUsuario from 4520_grupos where idGrupos=".$o["idGrupo"];
					
				
				}
				
				
				
				
				$fAsignacion=$con->obtenerPrimeraFila($consulta);
				$idProfesor=$fAsignacion[1];
				if(!isset($arrProfesores[$idProfesor]))
					$arrProfesores[$idProfesor]=array();
				if(!isset($arrProfesores[$idProfesor]["altas"]))
				{
					
					$arrProfesores[$idProfesor]["altas"]=array();
	
				}
				array_push($arrProfesores[$idProfesor]["altas"],$o);
				
			}
		}
		
		
	//---------------------
	
		foreach($arrProfesores as $idProfesor=>$resto)
		{
			$arrFechas=array();
			if((isset($resto["bajas"]))&&(sizeof($resto["bajas"])>0))	
			{
				$pos=0;
				foreach($resto["bajas"] as $gpo)
				{
					if(isset($gpo["virtual"]))
					{
						for($ct=0;$ct<sizeof($resto["bajas"]);$ct++)
						{
							if($pos!=$ct)
							{
								$gpoAux=$arrProfesores[$idProfesor]["bajas"][$ct];
								if(($gpo["idGrupo"]==$gpoAux["idGrupo"])&&($gpo["accion"]==$gpoAux["accion"])&&($gpo["obj"]->idProfesor==$gpoAux["obj"]->idProfesor))
								{
									array_splice($arrProfesores[$idProfesor]["bajas"],$pos,1);
									$pos--;
									break;
								}
								
							}	
						}
					}
					$pos++;
					
					
					
				}
			}
			if((isset($resto["altas"]))&&(sizeof($resto["altas"])>0))	
			{
				$pos=0;
				foreach($resto["altas"] as $gpo)
				{
					if(isset($gpo["virtual"]))
					{
						for($ct=0;$ct<sizeof($resto["altas"]);$ct++)
						{
							if($pos!=$ct)
							{
								$gpoAux=$arrProfesores[$idProfesor]["altas"][$ct];
								if(($gpo["idGrupo"]==$gpoAux["idGrupo"])&&($gpo["accion"]==$gpoAux["accion"])&&($gpo["obj"]->idProfesor==$gpoAux["obj"]->idProfesor))
								{
									array_splice($arrProfesores[$idProfesor]["altas"],$pos,1);
									$pos--;
									break;
								}
								
							}	
						}
					}
					$pos++;
				}
			}
		}
				
		
		/*foreach($arrProfesores as $idProfesor=>$resto)
		{
			$arrFechas=array();
			if((isset($resto["bajas"]))&&(sizeof($resto["bajas"])>0))	
			{
				
				foreach($resto["bajas"] as $gpo)
				{
	
					if($gpo["accion"]==1)
					{
						array_push($arrFechas,$gpo["obj"]->fechaBaja);
					}
					else
					{
						array_push($arrFechas,$gpo["obj"]->fechaBaja);
					}
					
				}
			}
			
		}
		$arrFinal=array();*/
		
		$cargaEscolarizado=0;
		$arrCargaNE=array();
		$nFinal=6;		
		
		foreach($arrProfesores as $idProfesor=>$resto)
		{
			$cargaEscolarizado=0;
			$arrCargaNE=array();
		
			$arrGruposProfesorTmp=array();
			
			if(isset($resto["bajas"]))
			{
				if(sizeof($resto["bajas"])>0)
				{
					foreach($resto["bajas"] as $b)
					{
						$arrGruposProfesorTmp[$b["idGrupo"]]=1;
					}
				}
			}
			
			if(isset($resto["altas"]))
			{
				if(sizeof($resto["altas"])>0)
				{
					foreach($resto["altas"] as $a)
					{
						$arrGruposProfesorTmp[$b["idGrupo"]]=1;
					}
				}
			}
			
			$listaGruposAux="";
			foreach($arrGruposProfesorTmp as $idGrupoTmp=>$restoFinal)
			{
				if($listaGruposAux=="")
					$listaGruposAux=$idGrupoTmp;
				else
					$listaGruposAux.=",".$idGrupoTmp;
			}
			
			$minFechaPeriodo="";
			$maxFechaPeriodo="";
			
			if($listaGruposAux=="")
				$listaGruposAux=-1;
			$consulta="SELECT DISTINCT idCiclo,idPeriodo FROM 4520_grupos WHERE idGrupos IN (".$listaGruposAux.")";
			$rPeriodos=$con->obtenerFilas($consulta);
			while($fPeriodo=mysql_fetch_row($rPeriodos))
			{
				$consulta="SELECT fechaInicial,fechaFinal FROM 4544_fechasPeriodo WHERE idCiclo=".$fPeriodo[0]." AND idPeriodo=".$fPeriodo[1]." 
							AND Plantel='".$fCabecera[12]."'";		
				$fPeriodo=$con->obtenerPrimeraFila($consulta);
				if($fPeriodo)
				{
					if($minFechaPeriodo=="")
					{
						$minFechaPeriodo=$fPeriodo[0];
						$maxFechaPeriodo=$fPeriodo[1];
					}
					else
					{
						if(strtotime($minFechaPeriodo)>strtotime($fPeriodo[0]))	
						{
							$minFechaPeriodo=$fPeriodo[0];
						}
						
						if(strtotime($maxFechaPeriodo)<strtotime($fPeriodo[1]))	
						{
							$maxFechaPeriodo=$fPeriodo[1];
						}
						
					}	
				}
				
			}

			$compPeriodos="";
			
			//$consulta="SELECT idCiclo,idPerido FROM 4544_fechasPeriodo WHERE Plantel='' "
			
			
			$arrGruposConsiderados=array();
			/*$consulta="SELECT * FROM 4519_asignacionProfesorGrupo WHERE idUsuario=".$idProfesor." 
						AND fechaAsignacion<='".$fechaAME."' AND fechaBaja>='".$fechaAME."' AND fechaAsignacion<=fechaBaja and a.situacion<>4";*/
			
			$consulta="SELECT a.* FROM 4519_asignacionProfesorGrupo a,4520_grupos g WHERE idUsuario=".$idProfesor." and g.idGrupos=a.idGrupo and g.Plantel='".$fCabecera[12]."'
						 AND fechaAsignacion<=fechaBaja and a.situacion<>4 AND fechaBaja>='".$fechaAME."' ".$compPeriodos;						
			
			$res=$con->obtenerFilas($consulta);	
			while($fila=mysql_fetch_row($res))
			{
				$idGrupo=$fila[1];
				$arrGruposConsiderados[$idGrupo]=1;
				$hSemanas=obtenerHorasSemanaGrupo($idGrupo);
				if(esGrupoEscolarizado($idGrupo))
				{
					$cargaEscolarizado+=$hSemanas;
				}
				else
				{
					$noBloque=obtenerNoBloqueGrupo($idGrupo);
					if(!isset($arrCargaNE[$noBloque]))	
						$arrCargaNE[$noBloque]=0;
					$arrCargaNE[$noBloque]+=$hSemanas;
				}
			}
			
			$arrCargaNEAnt=array();
			foreach($arrCargaNE as $id=>$valor)
			{
				$arrCargaNEAnt[$id]=$valor;	
			}
	
			
			$nProfesor=obtenerNombreUsuarioPaterno($idProfesor);
			$existeBajaProfesor=false;
			
			if((isset($resto["bajas"]))&&(sizeof($resto["bajas"])>0))	
				$existeBajaProfesor=true;
			
			$existeAltaProfesor=false;
			if((isset($resto["altas"]))&&(sizeof($resto["altas"])>0))	
				$existeAltaProfesor=true;
				
				
			$numFilaBase=$nFinal;
			$arrBajaE=array();
			$arrBajaA=array();
			
			//variables varias
				
			$seguroSocial=tieneSeguroSocial($idProfesor);
			$administrativo=esAdministrativo($idProfesor);
			$infonavit=tieneInfonavit($idProfesor);
			$tipoHonorario=obtenerTipoContratacion($idProfesor); //1= HP; 2 HAS; 3 N/A

			

			if($existeBajaProfesor)
			{
				$nFinal=$libro->clonarRangoCeldas("A",1,"Q",22,"A",$nFinal,1,0,true,false);
				
				$libro->setValor("B".$numFilaBase,$nProfesor);
				
				$libro->setValor("B".($numFilaBase+6),$cargaEscolarizado);
				
				//Variables varias
				$rBaja=$resto["bajas"][0];
				
				$motivoBaja="";
				$arrMotivosBajas=array();
				foreach($resto["bajas"] as $oBaja)
				{
					if(!isset($arrMotivosBajas[$oBaja["motivo"]]))
						$arrMotivosBajas[$oBaja["motivo"]]=array();
					array_push($arrMotivosBajas[$oBaja["motivo"]],$oBaja["idGrupo"]);
				}
				$altoFilaMotivo=25;
				if(sizeof($arrMotivosBajas)==1)
				{
					foreach($arrMotivosBajas as $m=>$restoAux)
					{
						$motivoBaja=$m;
						break;
					}
				}
				else
				{
					foreach($arrMotivosBajas as $m=>$restoAux)
					{
						$lblMotivo="";
						if(sizeof($restoAux)==1)
						{
							$consulta="SELECT nombreGrupo FROM 4520_grupos g WHERE g.idGrupos=".$restoAux[0];	
							$nGrupo=$con->obtenerValor($consulta);
							$lblMotivo="Baja del grupo ".$nGrupo." por el motivo: ".$m;
						}
						else
						{
							$lGrupos="";
							foreach($restoAux as $idGrupo)
							{
								$consulta="SELECT nombreGrupo FROM 4520_grupos g WHERE g.idGrupos=".$restoAux[0];	
								$nGrupo=$con->obtenerValor($consulta);	
								if($lGrupos=="")
									$lGrupos=$nGrupo;
								else
									$lGrupos.=", ".$nGrupo;
							}
							$lblMotivo="Baja de los grupos ".$lGrupos." por el motivo: ".$m;
						}	
						
						
						if($motivoBaja=="")
							$motivoBaja=$lblMotivo;
						else
						{
							$altoFilaMotivo+=25;
							$motivoBaja.="\n\r".$lblMotivo;
						}
						
					}	
				}
				
				$fechaBaja=date("d/m/Y",strtotime($rBaja["obj"]->fechaBaja));
				
				$libro->setValor("M".$numFilaBase,$fechaBaja);
				
				
				$noIMSS="";
				$consulta="SELECT IMSS FROM 802_identifica WHERE idUsuario=".$idProfesor;

				$noIMSS=$con->obtenerValor($consulta);
				
				
				$libro->setValor("A".($numFilaBase+19),$motivoBaja);
				$libro->setAltoFila("A".($numFilaBase+19),$altoFilaMotivo);
				$libro->setValor("B".($numFilaBase+2),$noIMSS);
				
				
				if($seguroSocial)
					$libro->setValor("G".($numFilaBase+2),"X");
				else
					$libro->setValor("I".($numFilaBase+2),"X");
					
				if($administrativo)
					$libro->setValor("G".($numFilaBase+3),"X");
				else
					$libro->setValor("I".($numFilaBase+3),"X");
				
				if($infonavit)
					$libro->setValor("G".($numFilaBase+4),"X");
				else
					$libro->setValor("I".($numFilaBase+4),"X");
				
				switch($tipoHonorario)
				{
					case 1:
						$libro->setValor("M".($numFilaBase+3),"X");
					break;
					case 2:
						$libro->setValor("N".($numFilaBase+3),"X");
					break;
					case 3:
						$libro->setValor("O".($numFilaBase+3),"X");
					break;
				}


				$cargaEscolarizadoAct=$cargaEscolarizado;
				foreach($resto["bajas"] as $oBaja)
				{
					$consulta="SELECT nombreGrupo,m.nombreMateria,g.fechaInicio FROM 4520_grupos g,4502_Materias m WHERE g.idGrupos=".$oBaja["idGrupo"]." AND g.idMateria=m.idMateria";
					$fGrupo=$con->obtenerPrimeraFila($consulta);
					$oGpo=array();
					$oGpo["A"]=$fGrupo[0];
					$oGpo["B"]=$fGrupo[1];
					$fechaMovimiento=$oBaja["obj"]->fechaBaja;
					$arrHorario=array();
					
					if(!isset($oBaja["horario"]))
					{
						if(strtotime($oBaja["obj"]->fechaAsignacion)>strtotime($oBaja["obj"]->fechaBaja))
							$fechaMovimiento=$oBaja["obj"]->fechaAsignacion;
						$arrHorario=obtenerFechasHorarioGrupoV2($oBaja["idGrupo"],$idSolicitudAme,true,true,$fechaMovimiento,$fechaMovimiento);
						if(sizeof($arrHorario)==0)
						{
							$arrHorario=obtenerFechasHorarioGrupoV2($oBaja["idGrupo"],$idSolicitudAme,true,true,$fGrupo[2],$fGrupo[2]);
						}
						
						
						
					}
					else
						$arrHorario=$oBaja["horario"];
					if(esGrupoEscolarizado($oBaja["idGrupo"]))
					{
						$oGpo["O"]=0;
						$totalHoras=0;
						foreach($arrHorario as $h)
						{
							
							$colAux="I";
							$lHorario=date("H:i",strtotime($h[3]))."-".date("H:i",strtotime($h[4]));
							$duracionMinutos=obtenerDiferenciaHoraMinutos($h[3],$h[4]);
							$duracionHora=obtenenerDuracionHoraGrupo($oBaja["idGrupo"]);
							$oGpo["O"]+=$duracionMinutos/$duracionHora;
							$totalHoras+=($duracionMinutos/$duracionHora);
							for($nAux=0;$nAux<($h[2]-1);$nAux++)	
							{
								$colAux=$libro->obtenerSiguienteColumna($colAux);
							}
							
							if(!isset($oGpo[$colAux]))
								$oGpo[$colAux]=$lHorario;
							else
								$oGpo[$colAux].=", ".$lHorario;
						}
						$cargaEscolarizadoAct-=$oGpo["O"];
						
						if(!isset($arrGruposConsiderados[$oBaja["idGrupo"]]))
						{
							$cargaEscolarizadoAct+=$totalHoras;
							$cargaEscolarizado+=$totalHoras;
							$arrGruposConsiderados[$oBaja["idGrupo"]]=1;
						}
						
						array_push($arrBajaE,$oGpo);
							
					}
					else
					{
						
						$nBloqueGpo=0;
						if(!isset($oBaja["nBloqueGpo"]))
							$nBloqueGpo=obtenerNoBloqueGrupo($oBaja["idGrupo"]);
						else
							$nBloqueGpo=$oBaja["nBloqueGpo"];

						$consulta="select count(*) from 4530_sesiones WHERE idGrupo=".$oBaja["idGrupo"]." AND tipoSesion<>15";
						$oGpo["I"]=$con->obtenerValor($consulta);
						$oGpo["K"]=$nBloqueGpo;
						$oGpo["N"]="";
						$turno=0;
						$totalHoras=0;
						foreach($arrHorario as $h)
						{
							if($turno==0)
							{
								$consulta="SELECT idReferencia FROM _474_gridHoraTurno WHERE horaInicio>='".$h[3]."' AND horaFin<='".$h[3]."'";
								$turno=$con->obtenerValor($consulta);
								
							}
							
							$diaLetra=utf8_encode($arrDiasSemana[$h[2]]);
							$lHorario=$diaLetra." ".date("H:i",strtotime($h[3]))."-".date("H:i",strtotime($h[4]));
							
							
							if($oGpo["N"]=="")
								$oGpo["N"]=$lHorario;
							else
								$oGpo["N"].=", ".$lHorario;
							$duracionMinutos=obtenerDiferenciaHoraMinutos($h[3],$h[4]);
							$duracionHora=obtenenerDuracionHoraGrupo($oBaja["idGrupo"]);
							if(!isset($arrCargaNEAnt[$nBloqueGpo]))
								$arrCargaNEAnt[$nBloqueGpo]=0;
							$arrCargaNEAnt[$nBloqueGpo]-=($duracionMinutos/$duracionHora);
							$totalHoras+=($duracionMinutos/$duracionHora);
						}
						
						
						if(!isset($arrGruposConsiderados[$oBaja["idGrupo"]]))
						{
							if(!isset($arrCargaNEAnt[$nBloqueGpo]))
								$arrCargaNEAnt[$nBloqueGpo]=0;
							$arrCargaNEAnt[$nBloqueGpo]+=$totalHoras;
							if(!isset($arrCargaNE[$nBloqueGpo]))
								$arrCargaNE[$nBloqueGpo]=0;
							$arrCargaNE[$nBloqueGpo]+=$totalHoras;
							$arrGruposConsiderados[$oBaja["idGrupo"]]=1;
						}
						
						if($turno==1)
							$oGpo["M"]="Matutino";
						else
							$oGpo["M"]="Vespertino";
							
						
						array_push($arrBajaA,$oGpo);	
						
					}
				}
				
				$nFilaListado=$numFilaBase+13;
				$numDesplazamiento=0;
				for($nFilas=1;$nFilas<sizeof($arrBajaE);$nFilas++)
				{
					$libro->insertarFila($nFilaListado);
					$numDesplazamiento++;
					
					
				}
				
				$nFilaListado--;
				foreach($arrBajaE as $f)
				{
					$libro->combinarCelda("B".$nFilaListado,"H".$nFilaListado);
					foreach($f as $columna=>$valor)	
					{
						$libro->setValor($columna.$nFilaListado,$valor);
					}
					$nFilaListado++;
				}
								
				$nFinal+=$numDesplazamiento;
				$nFilaListado=$numFilaBase+17+$numDesplazamiento;
				$numDesplazamientoA=0;
				for($nFilas=1;$nFilas<sizeof($arrBajaA);$nFilas++)
				{
					$libro->insertarFila($nFilaListado);
					$numDesplazamientoA++;
					
					
				}
				
				$nFilaListado--;
				foreach($arrBajaA as $f)
				{
					foreach($f as $columna=>$valor)	
					{
						
						$libro->setValor($columna.$nFilaListado,$valor);
					}
					$nFilaListado++;
				}
				
				$nFinal+=$numDesplazamientoA;
				
				
				
				
				$libro->setValor("B".($numFilaBase+7),$cargaEscolarizadoAct);
				
				$col="E";
				$colAnt="E";
				for($nBloque=1;$nBloque<=4;$nBloque++)
				{
					$cargaBloque=0;
					if(isset($arrCargaNE[$nBloque]))
						$cargaBloque=$arrCargaNE[$nBloque];
						
					$libro->setValor($col.($numFilaBase+6),$cargaBloque);
					$col=$libro->obtenerSiguienteColumna($col);
					$col=$libro->obtenerSiguienteColumna($col);
					$col=$libro->obtenerSiguienteColumna($col);
					
					$cargaBloqueAnt=0;
					if(isset($arrCargaNEAnt[$nBloque]))
						$cargaBloqueAnt=$arrCargaNEAnt[$nBloque];
					$libro->setValor($colAnt.($numFilaBase+7),$cargaBloqueAnt);
					$colAnt=$libro->obtenerSiguienteColumna($colAnt);
					$colAnt=$libro->obtenerSiguienteColumna($colAnt);
					$colAnt=$libro->obtenerSiguienteColumna($colAnt);
					
				}
				
				
				
			}
			//----------------------------
			
			if($existeAltaProfesor)
			{
				$numFilaBase=$nFinal;
	
				$nFinal=$libro->clonarRangoCeldas("A",1,"Q",28,"A",$nFinal,2,0,true,false);
				
				if($existeBajaProfesor)
				{
					$arrCargaNE=array();
					foreach($arrCargaNEAnt as $id=>$valor)
					{
						$arrCargaNE[$id]=$valor;	
					}
					
					$cargaEscolarizado=$cargaEscolarizadoAct;
				}
				
				$arrCargaNEAnt=array();
				foreach($arrCargaNE as $id=>$valor)
				{
					$arrCargaNEAnt[$id]=$valor;	
				}
				
				$libro->setValor("B".$numFilaBase,$nProfesor);
				$libro->setValor("B".($numFilaBase+10),$cargaEscolarizado);
				
				//variables varias
				$oProf=$resto["altas"][0];
				
				$consulta="select Plantel,idInstanciaPlanEstudio from 4520_grupos WHERE idGrupos=".$oProf["idGrupo"];
				
				$fGpo=$con->obtenerPrimeraFila($consulta);
				$costoHora=obtenerCostoProfesor($oProf["obj"]->idProfesor,$fGpo[0],$fGpo[1],$oProf["idGrupo"]);
				$perfil="";
				
				$consulta="SELECT n.txtnivel,a.txtArea,e.especialidad FROM _262_tablaDinamica f,_246_tablaDinamica n ,
						_369_tablaDinamica a,_369_dtgEspecialidad e WHERE f.responsable=".$oProf["obj"]->idProfesor." AND f.idEstado=3 AND n.id__246_tablaDinamica=f.cmbNivelEstudio AND 
						a.id__369_tablaDinamica=f.cmbAreaEstudio AND e.id__369_dtgEspecialidad=f.cmbEspecialidad ORDER BY n.intPrioridad desc";
				$fNivel=$con->obtenerPrimeraFila($consulta);
				$perfil=$fNivel[0]." (".$fNivel[1].", ".$fNivel[2].")";
				
				$sustituye="";
				
				
				$costoHora="$ ".number_format($costoHora,2);
				$motivoAlta="";
				
				$arrSuplencias=array();
				$arrAsignaciones=array();
				foreach($resto["altas"] as $oAlta)
				{
					
					if($oAlta["accion"]==3)
					{
						$arrAsignacionesTitulares=obtenerFechasAsignacionGrupoV2($oAlta["idGrupo"],$idSolicitudAme,true,true,$oAlta["obj"]->fechaAplicacion,$oAlta["obj"]->fechaTermino,1,false);
						if(sizeof($arrAsignacionesTitulares)>0)
						{
							$arrSuplencias[$arrAsignacionesTitulares[0][5]]["nombreProfesor"]=obtenerNombreUsuarioPaterno($arrAsignacionesTitulares[0][5]);
							if(!isset($arrSuplencias[$arrAsignacionesTitulares[0][5]]["grupos"]))
								$arrSuplencias[$arrAsignacionesTitulares[0][5]]["grupos"]=array();
							array_push($arrSuplencias[$arrAsignacionesTitulares[0][5]]["grupos"],$oAlta);
						}
					}
					else
					{
						array_push($arrAsignaciones,$oAlta);
					}
				}
				$altoFilaMotivo=25;
				if((sizeof($arrAsignaciones)>0)||(sizeof($arrSuplencias)>1))
				{
					$sustituye="";
					
					foreach($arrSuplencias as $idProfesor=>$oProfesor)
					{
						$lblMotivo="";
						
						
						foreach($oProfesor["grupos"] as $oGrupo)
						{

							$consulta="SELECT nombreGrupo FROM 4520_grupos g WHERE g.idGrupos=".$oGrupo["idGrupo"];	
							$nGrupo=$con->obtenerValor($consulta);
							$lblMotivo="Grupo: ".$nGrupo." suple al profesor: ".$oProfesor["nombreProfesor"]."  por el motivo:".$oGrupo["motivo"];	
						}
						
						if($motivoAlta=="")
							$motivoAlta=$lblMotivo;
						else	
						{
							$altoFilaMotivo+=25;
							$motivoAlta.="\n\r".$lblMotivo;
						}
					}
					
					

					if(($motivoAlta!="")||(sizeof($arrAsignaciones)>1))
					{
						foreach($arrAsignaciones as $oProfesor)
						{
							$lblMotivo="";
							

							$consulta="SELECT nombreGrupo FROM 4520_grupos g WHERE g.idGrupos=".$oProfesor["idGrupo"];	
							
							$nGrupo=$con->obtenerValor($consulta);
							$lblMotivo="Grupo: ".$nGrupo." alta del profesor por el motivo:".$oProfesor["motivo"];	
							
							
							if($motivoAlta=="")
								$motivoAlta=$lblMotivo;
							else	
							{
								$altoFilaMotivo+=25;
								$motivoAlta.="\n\r".$lblMotivo;
							}
						}
					}
					else
					{
						foreach($arrAsignaciones as $oProfesor)
						{
							$lblMotivo="";
							
							$motivoAlta=$oProfesor["motivo"];
						}
					}
				}
				else
				{
					foreach($arrSuplencias as $suplencia)
					{
						$sustituye=$suplencia["nombreProfesor"];
						if(sizeof($suplencia["grupos"])==1)
							$motivoAlta=$suplencia["grupos"][0]["motivo"];
					}
				}
				
				$tIngreso=obtenerTipoIngreso($oProf["obj"]->idProfesor,$fCabecera[12],$fechaAME);
				
				if($seguroSocial)
					$libro->setValor("M".($numFilaBase+2),"X");
				else
					$libro->setValor("O".($numFilaBase+2),"X");
					
				if($administrativo)
					$libro->setValor("M".($numFilaBase+3),"X");
				else
					$libro->setValor("O".($numFilaBase+3),"X");
				
				if($infonavit)
					$libro->setValor("M".($numFilaBase+4),"X");
				else
					$libro->setValor("O".($numFilaBase+4),"X");
				
				
				switch($tipoHonorario)
				{
					case 1:
						$libro->setValor("H".($numFilaBase+7),"X");
					break;
					case 2:
						$libro->setValor("I".($numFilaBase+7),"X");
					break;
					case 3:
						$libro->setValor("J".($numFilaBase+7),"X");
					break;
				}
				
				switch($tIngreso)
				{
					case 1:
						$libro->setValor("J".($numFilaBase),"X");
					break;
					case 2:
						$libro->setValor("L".($numFilaBase),"X");
					break;
					case 3:
						$libro->setValor("N".($numFilaBase),"X");
					break;
				}
				
				$libro->setValor("B".($numFilaBase+2),$perfil);
				$libro->setValor("B".($numFilaBase+4),$sustituye);
				$libro->setValor("B".($numFilaBase+6),$costoHora);
				$libro->setValor("A".($numFilaBase+23),$motivoAlta);
				$libro->setAltoFila("A".($numFilaBase+23),$altoFilaMotivo);
				$cargaEscolarizadoAct=$cargaEscolarizado;
				$arrAltaE=array();
				$arrAltaA=array();
				
				//-----
				
				
				foreach($resto["altas"] as $oAlta)
				{
					
					
					$consulta="SELECT nombreGrupo,m.nombreMateria FROM 4520_grupos g,4502_Materias m WHERE g.idGrupos=".$oAlta["idGrupo"]." AND g.idMateria=m.idMateria";
					$fGrupo=$con->obtenerPrimeraFila($consulta);
					$oGpo=array();
					$oGpo["A"]=$fGrupo[0];
					$oGpo["B"]=$fGrupo[1];
					$oGpo["P"]=date("d/m/Y",strtotime($oAlta["obj"]->fechaAplicacion));
					$oGpo["Q"]=date("d/m/Y",strtotime($oAlta["obj"]->fechaTermino));
					$arrHorario=array();
					
	
					$fechaMovimiento=$oAlta["obj"]->fechaAplicacion;
					$arrHorario=array();
					if(!isset($oAlta["horario"]))
					{
						$arrHorario=obtenerFechasHorarioGrupoV2($oAlta["idGrupo"],$idSolicitudAme,true,true,$fechaMovimiento,$fechaMovimiento);
						if(sizeof($arrHorario)==0)
						{
							$arrHorario=obtenerFechasHorarioGrupoV2($oAlta["idGrupo"],$idSolicitudAme,true,true,$fechaMovimiento,$oAlta["obj"]->fechaTermino);
							if(sizeof($arrHorario)>0)
							{

								$fechaMovimiento=$arrHorario[0][6];

								$arrHorario=obtenerFechasHorarioGrupoV2($oAlta["idGrupo"],$idSolicitudAme,true,true,$fechaMovimiento,$fechaMovimiento);
							}
							
						}
					}
					else
						$arrHorario=$oAlta["horario"];
					if(esGrupoEscolarizado($oAlta["idGrupo"]))
					{
						$oGpo["O"]=0;
						
						foreach($arrHorario as $h)
						{
							$colAux="I";
							$lHorario=date("H:i",strtotime($h[3]))."-".date("H:i",strtotime($h[4]));
							$duracionMinutos=obtenerDiferenciaHoraMinutos($h[3],$h[4]);
							$duracionHora=obtenenerDuracionHoraGrupo($oAlta["idGrupo"]);
							$oGpo["O"]+=$duracionMinutos/$duracionHora;
							
							for($nAux=0;$nAux<($h[2]-1);$nAux++)	
							{
								$colAux=$libro->obtenerSiguienteColumna($colAux);
							}
							
							if(!isset($oGpo[$colAux]))
								$oGpo[$colAux]=$lHorario;
							else
								$oGpo[$colAux].=", ".$lHorario;
						}
						$cargaEscolarizadoAct+=$oGpo["O"];
						array_push($arrAltaE,$oGpo);
							
					}
					else
					{
						$nBloqueGpo=0;
						if(!isset($oAlta["nBloqueGpo"]))
							$nBloqueGpo=obtenerNoBloqueGrupo($oAlta["idGrupo"]);
						else
							$nBloqueGpo=$oAlta["nBloqueGpo"];
						$consulta="select count(*) from 4530_sesiones WHERE idGrupo=".$oAlta["idGrupo"]." AND tipoSesion<>15";
						$oGpo["I"]=$con->obtenerValor($consulta);
						$oGpo["K"]=$nBloqueGpo;
						$oGpo["N"]="";
						$turno=0;
						foreach($arrHorario as $h)
						{
							
							if($turno==0)
							{
								$consulta="SELECT idReferencia FROM _474_gridHoraTurno WHERE horaInicio>='".$h[3]."' AND horaFin<='".$h[3]."'";
								$turno=$con->obtenerValor($consulta);
								
							}
							
	
							$diaLetra=utf8_encode($arrDiasSemana[$h[2]]);
							$lHorario=$diaLetra." ".date("H:i",strtotime($h[3]))."-".date("H:i",strtotime($h[4]));

							if($oGpo["N"]=="")
								$oGpo["N"]=$lHorario;
							else
								$oGpo["N"].=", ".$lHorario;
							$duracionMinutos=obtenerDiferenciaHoraMinutos($h[3],$h[4]);
							$duracionHora=obtenenerDuracionHoraGrupo($oAlta["idGrupo"]);
							if(!isset($arrCargaNEAnt[$nBloqueGpo]))
								$arrCargaNEAnt[$nBloqueGpo]=0;
							$arrCargaNEAnt[$nBloqueGpo]+=($duracionMinutos/$duracionHora);
						}
						if($turno==1)
							$oGpo["M"]="Matutino";
						else
							$oGpo["M"]="Vespertino";
							
						
						array_push($arrAltaA,$oGpo);	
						
					}
				}
				
				$nFilaListado=$numFilaBase+17;
				$numDesplazamiento=0;
				for($nFilas=1;$nFilas<sizeof($arrAltaE);$nFilas++)
				{
					$libro->insertarFila($nFilaListado);
					$numDesplazamiento++;
					
					
				}
				
				$nFilaListado--;
				foreach($arrAltaE as $f)
				{
					foreach($f as $columna=>$valor)	
					{
						$libro->combinarCelda("B".$nFilaListado,"H".$nFilaListado);
						$libro->setValor($columna.$nFilaListado,$valor);
					}
					$nFilaListado++;
				}
				
				
				$nFinal+=$numDesplazamiento;
				$nFilaListado=$numFilaBase+21+$numDesplazamiento;
				$numDesplazamientoA=0;
				for($nFilas=1;$nFilas<sizeof($arrAltaA);$nFilas++)
				{
					$libro->insertarFila($nFilaListado);
					$numDesplazamientoA++;
					
					
				}
				
				$nFilaListado--;
				foreach($arrAltaA as $f)
				{
					foreach($f as $columna=>$valor)	
					{
						$libro->setValor($columna.$nFilaListado,$valor);
					}
					$nFilaListado++;
				}
				
				$nFinal+=$numDesplazamientoA;
				
				//---
				
				
				
				$libro->setValor("B".($numFilaBase+11),$cargaEscolarizadoAct);
				$col="E";
				$colAnt="E";
				for($nBloque=1;$nBloque<=4;$nBloque++)
				{
					$cargaBloque=0;
					if(isset($arrCargaNE[$nBloque]))
						$cargaBloque=$arrCargaNE[$nBloque];
						
					$libro->setValor($col.($numFilaBase+10),$cargaBloque);
					$col=$libro->obtenerSiguienteColumna($col);
					$col=$libro->obtenerSiguienteColumna($col);
					$col=$libro->obtenerSiguienteColumna($col);
					
					$cargaBloqueAnt=0;
					if(isset($arrCargaNEAnt[$nBloque]))
						$cargaBloqueAnt=$arrCargaNEAnt[$nBloque];
					$libro->setValor($colAnt.($numFilaBase+11),$cargaBloqueAnt);
					$colAnt=$libro->obtenerSiguienteColumna($colAnt);
					$colAnt=$libro->obtenerSiguienteColumna($colAnt);
					$colAnt=$libro->obtenerSiguienteColumna($colAnt);
					
				}
				
				
			}
			
		}
		
		$libro->removerHoja(1);
		$libro->removerHoja(1);
		$pieFinal=$nFinal+1;
		$consulta="UPDATE 4549_cabeceraSolicitudAME SET formatoPieFinal=".$pieFinal." WHERE idSolicitudAME=".$idSolicitudAme;
		$con->ejecutarConsulta($consulta);
		$rutaExcel="../modulosEspeciales_UGM/respaldoAMES/AME_".$idSolicitudAme.".xlsx";
		//$libro->generarArchivo("HTML","../modulosEspeciales_UGM/respaldoAMES/AME_".$idSolicitudAme.".xlsx");
		if(!$modoDebug)
		{
			$libro->generarArchivoServidor("Excel2007",$rutaExcel);
		}
		else
		{
			$libro->generarArchivo("PDF",$rutaExcel);
		}
			
		
		/*header("Content-length: ".filesize($rutaExcel)); 
		header("Content-Disposition: attachment; filename=pruebaExcel.xlsx");
		readfile($rutaExcel);	*/
			
		return true;
	}
	
	function actualizarFechaVoBoAME($idAME,$fecha)
	{
		global $con;	
		global $baseDir;

		$rutaArchivo=$baseDir."/modulosEspeciales_UGM/respaldoAMES/AME_".$idAME.".xlsx";
		$rutaTemp=$baseDir."/archivosTemporales/AME_".$idAME.".xlsx";
		if(file_exists($rutaArchivo))
		{
		
			$libro=new cExcel($rutaArchivo,true,"Excel2007");	
			$consulta="SELECT formatoPieFinal FROM 4549_cabeceraSolicitudAME WHERE idSolicitudAME=".$idAME;
			$numFilaBase=$con->obtenerValor($consulta);
			if($numFilaBase!="")
			{
				$libro->setValor("M".($numFilaBase+6),"");
				$libro->setValor("M".($numFilaBase+7),$fecha);

				$libro->generarArchivoServidor("Excel2007",$rutaTemp);
				if(copy($rutaTemp,$rutaArchivo))
					unlink($rutaTemp);
			}
		}
	}
	
	
	function obtenerResponsablesAMES($idAME)
	{
		global $con;
		
		$arrResponsables=array();
		$arrGrupos=array();
		$plantel="";
		
		$consulta="SELECT idSolicitudMovimiento FROM 4548_solicitudesMovimientoGrupo WHERE idSolicitudAME=".$idAME;
		$listSolicitudes=$con->obtenerListaValores($consulta);
		if($listSolicitudes=="")
			$listSolicitudes=-1;
		$consulta="SELECT DISTINCT idGrupo FROM 4548_gruposSolicitudesMovimiento WHERE idSolicitud IN (".$listSolicitudes.")";
		$res=$con->obtenerFilas($consulta);	
		while($fila=mysql_fetch_row($res))
		{
			$consulta="SELECT Plantel,i.idModalidad,p.nivelPlanEstudio FROM 4520_grupos g,4513_instanciaPlanEstudio i, 4500_planEstudio p
					WHERE idGrupos=".$fila[0]." AND g.idInstanciaPlanEstudio=i.idInstanciaPlanEstudio AND p.idPlanEstudio=i.idPlanEstudio";
			$fGrupo=$con->obtenerPrimeraFila($consulta);
			
			$plantel=$fGrupo[0];
			if(!isset($arrGrupos[$fGrupo[2]]))
				$arrGrupos[$fGrupo[2]]=array();
				
			if(!existeValor($arrGrupos[$fGrupo[2]],$fGrupo[1]))
				array_push($arrGrupos[$fGrupo[2]],$fGrupo[1]);
		}
		
		
		$iNivel=0;
		foreach($arrGrupos as $idNivel=>$idModalidad)
		{
			if($iNivel==0)
				$iNivel=$idNivel;
				
			if(sizeof($arrGrupos[$iNivel])<sizeof($arrGrupos[$idNivel]))	
				$iNivel=$idNivel;
			
		}
		
		$idRegistro="";
		
		foreach($arrGrupos[$iNivel] as $idModalidad)
		{
			$consulta="SELECT id__1040_tablaDinamica FROM _1040_tablaDinamica WHERE plantel='".$plantel."' AND nivelEstudio=".$iNivel." AND modalidad=".$idModalidad;	
			$idRegistro=$con->obtenerValor($consulta);
			if($idRegistro!="")
				break;
		}
		
		if($idRegistro=="")
		{
			foreach($arrGrupos as $idNivel=>$resto)
			{
				$consulta="SELECT id__1040_tablaDinamica FROM _1040_tablaDinamica WHERE plantel='".$plantel."' AND nivelEstudio=".$idNivel." AND modalidad=-1";	
				$idRegistro=$con->obtenerValor($consulta);
				if($idRegistro!="")
					break;
			}
		}
		
		if($idRegistro=="")
		{
				
			$consulta="SELECT id__1040_tablaDinamica FROM _1040_tablaDinamica WHERE plantel='".$plantel."' AND nivelEstudio=-1 AND modalidad=-1";	
			$idRegistro=$con->obtenerValor($consulta);
			if($idRegistro=="")
				$idRegistro=-1;
			
		}
		
		
		$consulta="SELECT subDirAcademica,coorAdmon,director,direccionAcade,coordAcademica FROM _1040_responsables WHERE idReferencia=".$idRegistro;
		$fDatos=$con->obtenerPrimeraFila($consulta);
		$arrResponsables["subDirAcademica"]=$fDatos[0];
		$arrResponsables["coorAdmon"]=$fDatos[1];
		$arrResponsables["director"]=$fDatos[2];
		$arrResponsables["direccionAcade"]=$fDatos[3];
		$arrResponsables["coordAcademica"]=$fDatos[4];
		return $arrResponsables;
		
	}
	
	function registrarCambioSituacionCabeceraAME($idSolicitud,$estadoCambio)
	{
		global $con;
		$consulta="SELECT situacion FROM  4549_cabeceraSolicitudAME WHERE idSolicitudAME=".$idSolicitud;
		$idEstado=$con->obtenerValor($consulta);
		$consulta="INSERT INTO 4613_bitacoraAMECabeceraSistema(idSolicitudAME,idEstadoOriginal,idEstadoCambio,fechaCambio,horaCambio,idUsuario)
					VALUES(".$idSolicitud.",".$idEstado.",".$estadoCambio.",'".date("Y-m-d")."','".date("H:i:s")."',".$_SESSION["idUsr"].")";
		$con->ejecutarConsulta($consulta);
		
	}
	
	function situacionJustificacionComision($idRegistro)
	{
		global $con;
		$situacion=0;
		$lblComision="";
		$consulta="SELECT idGrupo,horaInicioBloque,horaFinBloque,fechaSesion,noSesion FROM 4561_sesionesClaseModulo WHERE idSesionesClaseComision=".$idRegistro;	
		$fRegistro=$con->obtenerPrimeraFila($consulta);
		
		
		$consulta="SELECT pagado,(SELECT folioNomina FROM 672_nominasEjecutadas WHERE idNomina=c.idNomina),c.estadoFalta,c.idNomina,c.idFalta  FROM 4559_controlDeFalta c WHERE idGrupo=".$fRegistro[0]." 
								AND fechaFalta='".$fRegistro[3]."' AND horaInicial='".$fRegistro[1]."'";	
						
		$fFalta=$con->obtenerPrimeraFila($consulta);	
		
		
			
		if($fFalta)
		{
			if($fFalta[0]==1)
			{
				$situacion=2;
				$lblComision="(<span style='color:#FF0000'>Falta considerada en la nómina: ".$fFalta[1].", la cual se encuentra cerrada</span>) ";
			}
			else
			{
				if($fFalta[0]==2)
				{
					$situacion=5;
					$lblComision=$fFalta[3];
				}
				else
					if($fFalta[2]==1)
					{
						$situacion=3;
						$lblComision="(<span style='color:#FF0000'>Falta justificada</span>) ";
					}
			}
		}
		else
		{
			$consulta="SELECT e.etapa,e.folioNomina,e.idNomina FROM 4556_costoHoraDocentes c,672_nominasEjecutadas e WHERE idGrupo=".$fRegistro[0]." AND fechasesion='".$fRegistro[3]."' AND horaInicioBloque='".trim($fRegistro[1]).
					"' AND e.idNomina=c.idNomina";
			$fNomina=$con->obtenerPrimeraFila($consulta);

			if(($fNomina)&&(($fNomina[0]!=1)&&($fNomina[0]!=200)))	
			{
				$situacion=4;
				$lblComision="(<span style='color:#FF0000'>Sesión considerada en la nómina: ".$fNomina[1].", la cual se encuentra cerrada</span>) ";
			}
			else
			{
				if($fNomina)
				{
					$situacion=6;
					$lblComision=$fNomina[2];
				}	
			}
		}
		
		return $situacion."|".$lblComision;
	}
	
	
	function obtenerDatosMateriaHorasGrupo($idGrupo)
	{
		global $con;
		$consulta="SELECT idPlanEstudio,idMateria,idCiclo,idPeriodo,idInstanciaPlanEstudio FROM 4520_grupos WHERE idGrupos=".$idGrupo;
		$fDatosGrupos=$con->obtenerPrimeraFila($consulta);
	
		$consulta="SELECT noHorasSemana,totalHorasMateria FROM 4502_configuracionesAvanzadasMateria WHERE idInstanciaPlanEstudio=".$fDatosGrupos[4].
				" AND idMateria=".$fDatosGrupos[1]." AND idCiclo=".$fDatosGrupos[2]." AND idPeriodo=".$fDatosGrupos[3]." and situacion=1 ORDER BY idRegistro";

		$fDatosMateria=$con->obtenerPrimeraFila($consulta);
		if(!$fDatosMateria)
		{
			$consulta="SELECT noHorasSemana,totalHorasMateria FROM 4502_configuracionesAvanzadasMateria WHERE idInstanciaPlanEstudio=".$fDatosGrupos[4].
				" AND idMateria=".$fDatosGrupos[1]." AND idCiclo=".$fDatosGrupos[2]."  AND idPeriodo=-1 and situacion=1 ORDER BY idRegistro";
		
			$fDatosMateria=$con->obtenerPrimeraFila($consulta);
			if(!$fDatosMateria)
			{
				$consulta="SELECT noHorasSemana,totalHorasMateria FROM 4502_configuracionesAvanzadasMateria WHERE idInstanciaPlanEstudio=".$fDatosGrupos[4].
					" AND idMateria=".$fDatosGrupos[1]." AND idCiclo=-1  AND idPeriodo=".$fDatosGrupos[3]." and situacion=1 ORDER BY idRegistro";
		
				$fDatosMateria=$con->obtenerPrimeraFila($consulta);
				if(!$fDatosMateria)
				{
					$consulta="SELECT noHorasSemana,totalHorasMateria FROM 4502_configuracionesAvanzadasMateria WHERE idInstanciaPlanEstudio=".$fDatosGrupos[4].
							" AND idMateria=".$fDatosGrupos[1]." AND idCiclo=-1 AND idPeriodo=-1 and situacion=1  ORDER BY idRegistro";
			
					$fDatosMateria=$con->obtenerPrimeraFila($consulta);
					if(!$fDatosMateria)
					{
						$consulta="SELECT horasSemana,horaMateriaTotal FROM 4502_Materias  WHERE idMateria=".$fDatosGrupos[1];
						$fDatosMateria=$con->obtenerPrimeraFila($consulta);	
					}
				}
			}
		}
				
		
		$arrDatos=array();
		$arrDatos["horasSemana"]=$fDatosMateria[0];
		$arrDatos["horaMateriaTotal"]=$fDatosMateria[1];
		return $arrDatos ;
	}
	
	function obtenerFechaFinCursoGrupo($fechaInicio,$idGrupo,$validarFechaFin=false)
	{
		global $con;


		$arrDatosMateriaHoras=obtenerDatosMateriaHorasGrupo($idGrupo);
		$hSemana	=$arrDatosMateriaHoras["horasSemana"];
		$hTotal	=$arrDatosMateriaHoras["horaMateriaTotal"];

		if($hSemana=="")
			$numSemanas=0;
		else
			$numSemanas=ceil($hTotal/$hSemana);
			
			
		$fechaFin=date("Y-m-d",strtotime("+".$numSemanas." week",strtotime($fechaInicio)));
		$fechaFin="'".date("Y-m-d",strtotime("-1 day",strtotime($fechaFin)))."'";



		if($validarFechaFin)
		{
			$consulta="SELECT idCiclo,idPeriodo,idInstanciaPlanEstudio,idGradoCiclo,noBloqueAsociado FROM 4520_grupos WHERE idGrupos=".$idGrupo;
			$fDatosGpo=$con->obtenerPrimeraFila($consulta);

			$consulta="SELECT fechaInicial,fechaFinal FROM 4544_fechasPeriodo WHERE idCiclo=".$fDatosGpo[0]." AND idPeriodo=".$fDatosGpo[1]." AND  idInstanciaPlanEstudio=".$fDatosGpo[2];
			$fFechasP=$con->obtenerPrimeraFila($consulta);
			$consulta="SELECT idEsquemaGrupo FROM 4513_instanciaPlanEstudio WHERE idInstanciaPlanEstudio=".$fDatosGpo[2];
			$tEsquemaFecha=$con->obtenerValor($consulta);
			
			if($tEsquemaFecha==2)
			{
				$consulta="SELECT fechaInicio FROM 4571_fechasBloquePeriodo WHERE idInstancia=".$fDatosGpo[2]." AND noBloque=".$fDatosGpo[4]." AND idGrado=".$fDatosGpo[3].
					" AND idCiclo=".$fDatosGpo[0]." AND idPeriodo=".$fDatosGpo[1];
				$fI=$con->obtenerValor($consulta);
				$fFechasP[0]=$fI;
				$consulta="SELECT fechaInicio FROM 4571_fechasBloquePeriodo WHERE idInstancia=".$fDatosGpo[2]." AND noBloque=".($fDatosGpo[4]+1)." AND idGrado=".$fDatosGpo[3].
						" AND idCiclo=".$fDatosGpo[0]." AND idPeriodo=".$fDatosGpo[1];
				$fF=$con->obtenerValor($consulta);
				if($fF!="")
				{
					$fF=date("Y-m-d",strtotime("-1 days",strtotime($fF)));
					$fFechasP[1]=$fF;
				}
			}
			
			
			if(strtotime(str_replace("'","",$fechaFin))>strtotime($fFechasP[1]))
			{
				$fechaFin="'".$fFechasP[1]."'";
			}
			
		}
		
		
		return $fechaFin;
	}

	function obtenerSesionesVirtualesCursoHorario($idGrupo,$fechaMaximaParam="")
	{
		global $con;
		$ultimoHorario="";
		$fechaUltimaSesion="";
		$generarSesionPorDia=false;

		$query="SELECT max(fechaFin) FROM 4522_horarioGrupo WHERE idGrupo= ".$idGrupo." order by horaInicio";
		$maxFechaGrupo=$con->obtenerValor($query);
		
		$query="SELECT fechaInicio,Plantel,idMateria,idInstanciaPlanEstudio,idCiclo,idPeriodo,idGradoCiclo,noBloqueAsociado,idInstanciaPlanEstudio FROM 4520_grupos WHERE idGrupos=".$idGrupo;
		$fGpo=$con->obtenerPrimeraFila($query);
		$fechaAplicacion=strtotime($fGpo[0]);
		$noBloqueAsociado=$fGpo[7];
		$plantel=$fGpo[1];
		$idInstanciaPlanEstudio=$fGpo[8];
		
		$query="SELECT ajustarUltimaSesionClases FROM 4513_instanciaPlanEstudio WHERE idInstanciaPlanEstudio=".$idInstanciaPlanEstudio;
		$ajustarUltimaSesionClases=$con->obtenerValor($query);

		
		$arrDiasSesion=array();
		$query="SELECT fechaInicial,fechaFinal FROM 4544_fechasPeriodo WHERE idCiclo=".$fGpo[4]." AND idPeriodo=".$fGpo[5]." AND idInstanciaPlanEstudio='".$fGpo[3]."'";
		$fFechas=$con->obtenerPrimeraFila($query);	

		$fechaFinPeriodo=strtotime($fFechas[1]);
		if($noBloqueAsociado!=0)
		{
			
			$query="SELECT fechaFin FROM 4571_fechasBloquePeriodo WHERE idGrado=".$fGpo[6]." AND idCiclo=".$fGpo[4]." AND idPeriodo=".$fGpo[5]." AND idInstancia=".$fGpo[3].
					" AND noBloque=".$noBloqueAsociado;
			$fFinBloqueAux=$con->obtenerValor($query);
			if($fFinBloqueAux=="")
			{
				$query="SELECT fechaInicio FROM 4571_fechasBloquePeriodo WHERE idGrado=".$fGpo[6]." AND idCiclo=".$fGpo[4]." AND idPeriodo=".$fGpo[5]." AND idInstancia=".$fGpo[3].
						" AND noBloque=".($noBloqueAsociado+1);
				
				$fInicioBloque=$con->obtenerValor($query);	
				if($fInicioBloque!="")
				{
					$fechaFinPeriodo=strtotime("-1 day",strtotime($fInicioBloque));
				}
			}
			else
			{
				$fechaFinPeriodo=$fFinBloqueAux;
			}
			
		}
		
		if($fechaMaximaParam!="")
		{
			if($fechaFinPeriodo>strtotime($fechaMaximaParam))
				$fechaFinPeriodo=strtotime($fechaMaximaParam);
		}

		$arrDatosMateriaHoras=obtenerDatosMateriaHorasGrupo($idGrupo);
		$filaMat[0]	=$arrDatosMateriaHoras["horasSemana"];
		$filaMat[1]	=$arrDatosMateriaHoras["horaMateriaTotal"];
		
		$totalHoras=$filaMat[1];
		$duracionHora=obtenenerDuracionHoraGrupo($idGrupo);
		
		$query="SELECT dia,horaInicio,horaFin,fechaInicio,fechaFin,idAula FROM 4522_horarioGrupo WHERE idGrupo= ".$idGrupo." and fechaInicio<=fechaFin order by horaInicio";
		$res=$con->obtenerFilas($query);
		while($f=mysql_fetch_row($res))
		{
			$hInicio=strtotime($f[1]);
			$hFin=strtotime($f[2]);
			if(!isset($arrDiasSesion[$f[0]]))
			{
				$arrDiasSesion[$f[0]]=array();
			}
			if($f[4]==$maxFechaGrupo)
				$f[4]=date("Y-m-d",$fechaFinPeriodo);
			$obj[0]=date("H:i:s",$hInicio)." - ".date("H:i:s",$hFin);
			$obj[1]=strtotime($f[3]);
			$obj[2]=strtotime($f[4]);
			$obj[3]=obtenerDiferenciaHoraMinutos($f[1],$f[2])/$duracionHora;
			$obj[4]=$f[3];
			$obj[5]=$f[4];
			$obj[6]=$f[1];
			$obj[7]=$f[2];
			$obj[8]=$f[5];
			array_push($arrDiasSesion[$f[0]],$obj);
		}
		
		$arrFechasHorario=array();
		
		if(sizeof($arrDiasSesion)==0)
		{
			$fechaFinal=obtenerFechaFinCurso($fGpo[0],$fGpo[2],$fGpo[3]);
			return str_replace("'","",$fechaFinal);
		}
		
		$finalizar=false;
		$fechaUltimaSesion=strtotime($fGpo[0]);
		$horasAcum=0;

		while(($horasAcum<$totalHoras)	&&(!$finalizar))
		{
			if((isset($arrDiasSesion[date("w",$fechaAplicacion)]))&&(!esDiaInhabilEscolar($plantel,date("Y-m-d",$fechaAplicacion))))
			{
				$arrHorario=$arrDiasSesion[date("w",$fechaAplicacion)];
				foreach($arrHorario as $h)	
				{
					if(($fechaAplicacion>=$h[1])&&($fechaAplicacion<=$h[2]))
					{
						$oFecha["fecha"]=date("Y-m-d",$fechaAplicacion);
						$oFecha["fechaInicio"]=$h[4];
						$oFecha["fechaFin"]=$h[5];
						$oFecha["horaInicio"]=$h[6];
						$oFecha["horaFin"]=$h[7];
						$oFecha["dia"]=date("w",$fechaAplicacion);
						$oFecha["idAula"]=$h[8];
						$oFecha["horarioCompleto"]=1;

						$hSesion=obtenerDiferenciaHoraMinutos($h[6],$h[7])/$duracionHora;
						if((($horasAcum+$hSesion)<=$totalHoras)||($ajustarUltimaSesionClases==0))
						{
							$horasAcum+=$hSesion;
						}
						else
						{
							$diferenciaHoras=$totalHoras-$horasAcum;
							$leyenda="+".$diferenciaHoras." hours";
							$oFecha["horaFin"]=date("H:i:s",strtotime($leyenda,strtotime($oFecha["horaInicio"])));

							$horasAcum+=$diferenciaHoras;
							$oFecha["horarioCompleto"]=0;
						}
						
						if($oFecha["horaInicio"]!=$oFecha["horaFin"])
							array_push($arrFechasHorario,$oFecha);
						
					}
				}
			}
			$fechaAplicacion=strtotime("+1 days",$fechaAplicacion);

			if($fechaAplicacion>$fechaFinPeriodo)
			{
				$finalizar=true;
			}
		}
		
		return $arrFechasHorario;
		
	}
	
	function ajustarFechaFinalCursoAME($idGrupo,$validarFechaFinal=true)
	{
		global $con;
		$x=0;
		$arrDias=array();
		$fechaFin="";
		
		$query="delete FROM 4522_horarioGrupo WHERE idGrupo=".$idGrupo." AND fechaInicio>fechaFin";
		$con->ejecutarConsulta($query);
		$query="SELECT fechaInicio,fechaFin FROM 4522_horarioGrupo WHERE idGrupo=".$idGrupo." AND horarioCompleto=0";
		$fGpo=$con->obtenerPrimeraFila($query);
		if($fGpo)
		{
			$query="UPDATE 4522_horarioGrupo SET fechaFin='".$fGpo[1]."' WHERE fechaInicio<=fechaFin AND idGrupo=".$idGrupo." AND fechaFin='".date("Y-m-d",strtotime("-1 days",strtotime($fGpo[0])))."' and horarioCompleto=1";
			$con->ejecutarConsulta($query);
			$query="delete FROM 4522_horarioGrupo WHERE idGrupo=".$idGrupo." AND horarioCompleto=0";
			$con->ejecutarConsulta($query);
		}
		
		
		
		
		
		$query="SELECT idMateria,idInstanciaPlanEstudio,fechaInicio,fechaFin,Plantel,idCiclo,idPeriodo,idGrupoAgrupador,noBloqueAsociado,idGradoCiclo FROM 4520_grupos where idGrupos=".$idGrupo;
		$fGrupos=$con->obtenerPrimeraFila($query);
		$esAgrupador=false;
		
		$idInstanciaPlanEstudio=$fGrupos[1];
		
		$query="SELECT ajustarUltimaSesionClases FROM 4513_instanciaPlanEstudio WHERE idInstanciaPlanEstudio=".$idInstanciaPlanEstudio;
		$ajustarUltimaSesionClases=$con->obtenerValor($query);
		
		if(($fGrupos[0]<0)||($fGrupos[7]!=0))
			$esAgrupador=true;
		$idInstanciaPlanEstudio=$fGrupos[1];
		$consulta="SELECT i.idPlanEstudio,idEsquemaGrupo,i.nombrePlanEstudios,p.descripcion,i.idInstanciaPlanEstudio,i.sede,tipoEsquemaAsignacionFechasGrupo,numMaxBloquesFechas 
				FROM 4513_instanciaPlanEstudio i,4500_planEstudio p WHERE idInstanciaPlanEstudio=".$idInstanciaPlanEstudio." and p.idPlanEstudio=i.idPlanEstudio";
		$filaInstancia=$con->obtenerPrimeraFila($consulta);
		$tEsquemaFecha=$filaInstancia[6];
		
		
		$arrDatosMateriaHoras=obtenerDatosMateriaHorasGrupo($idGrupo);
		$filaMat[0]=$arrDatosMateriaHoras["horasSemana"];
		$filaMat[1]=$arrDatosMateriaHoras["horaMateriaTotal"];
		
		if(($filaMat[0]==0)&&($filaMat[1]==0))
		{
			return true;
		}
		$totalHoras=$filaMat[1];
		$hSemana=$filaMat[0];
		
		$noBloque=0;
		if(($tEsquemaFecha==2)&&($validarFechaFinal))
			$noBloque=$fGrupos[8];
		
		
		$query="select fechaFin from 4520_grupos where idGrupos=".$idGrupo;
		$fechaFin=$con->obtenerValor($query);
		
		$pos=0;
		$consultaAux[$pos]="begin";
		$pos++;
		
		
		
		$consultaAux[$pos]="commit";
		$pos++;
		if($con->ejecutarBloque($consultaAux))
		{
			$aHorario=obtenerSesionesVirtualesCursoHorario($idGrupo,$fechaFin);
			if(sizeof($aHorario)>0)
			{
				$uSesion=$aHorario[sizeof($aHorario)-1];
				
				$fSI=$uSesion["fechaInicio"];
				$fSF=$uSesion["fechaFin"];
				
				for($nPosS=0;$nPosS<sizeof($aHorario);$nPosS++)
				{
					if(($aHorario[$nPosS]["fechaInicio"]==$fSI)&&($aHorario[$nPosS]["fechaFin"]==$fSF))
					{
						$aHorario[$nPosS]["fechaFin"]=$fechaFin;
					}
				}
				
				$uSesion["fechaFin"]=$fechaFin;
				
				if($uSesion["horarioCompleto"]==0)
				{
					
					if(sizeof($aHorario)==1)	
					{
						$pos=0;
						$consultaAux=array();
						$consultaAux[$pos]="begin";
						$pos++;
						
						$consultaAux[$pos]="delete from 4522_horarioGrupo where idGrupo=".$idGrupo;
						$pos++;
						
						$consultaAux[$pos]="INSERT INTO 4522_horarioGrupo(idGrupo,dia,horaInicio,horaFin,idAula,fechaInicio,fechaFin)VALUES(".
											$idGrupo.",".$uSesion["dia"].",'".$uSesion["horaInicio"]."','".$uSesion["horaFin"]."',".$uSesion["idAula"].
											",'".$uSesion["fechaInicio"]."','".$uSesion["fechaFin"]."')";
						$pos++;
						
						
						$consultaAux[$pos]="commit";
						$pos++;
						$con->ejecutarBloque($consultaAux);
						
					}
					else
					{
						$pos=0;
						$consultaAux=array();
						$consultaAux[$pos]="begin";
						$pos++;
						
						$consultaAux[$pos]="update 4522_horarioGrupo SET fechaFin='".date("Y-m-d",strtotime("-1 days",strtotime($uSesion["fechaFin"])))."' where idGrupo=".$idGrupo." and fechaInicio<=fechaFin and fechaFin='".$uSesion["fechaFin"]."'";
						$pos++;
						
						$query="select * from 4522_horarioGrupo where idGrupo=".$idGrupo." and dia=".$uSesion["dia"]." and fechaFin='".$uSesion["fechaFin"]."' and horaInicio<'".$uSesion["horaInicio"]."' order by horaInicio";
						$rHorarioAux=$con->obtenerFilas($query);
						while($fHorarioAux=mysql_fetch_row($rHorarioAux))
						{
							$consultaAux[$pos]="INSERT INTO 4522_horarioGrupo(idGrupo,dia,horaInicio,horaFin,idAula,fechaInicio,fechaFin,horarioCompleto)VALUES(".
											$idGrupo.",".$uSesion["dia"].",'".$fHorarioAux[3]."','".$fHorarioAux[4]."',".$fHorarioAux[5].
											",'".$uSesion["fechaFin"]."','".$uSesion["fechaFin"]."',0)";
							$pos++;	
						}
						
						if($uSesion["horaInicio"]!=$uSesion["horaFin"])
						{
							$consultaAux[$pos]="INSERT INTO 4522_horarioGrupo(idGrupo,dia,horaInicio,horaFin,idAula,fechaInicio,fechaFin,horarioCompleto)VALUES(".
												$idGrupo.",".$uSesion["dia"].",'".$uSesion["horaInicio"]."','".$uSesion["horaFin"]."',".$uSesion["idAula"].
												",'".$uSesion["fechaFin"]."','".$uSesion["fechaFin"]."',0)";
							$pos++;
						}
						
						$consultaAux[$pos]="commit";
						$pos++;
						$con->ejecutarBloque($consultaAux);	
					}
				}
				
			}
			return true;
		}
		
	}
	
	
	function obtenerGradosAperturaCiclo($idCiclo,$idPeriodo,$idInstancia)
	{
		global $con;
		$arrGrados=array();
		$arrGradosFinal=array();
		$arrPeriodos=explode(",",$idPeriodo);
		foreach($arrPeriodos as $p)
		{
			$consulta="SELECT idGrado FROM 4546_estructuraPeriodo WHERE idCiclo=".$idCiclo." AND idPeriodo=".$p." AND idInstanciaPlanEstudio=".$idInstancia." AND situacion=1";


			$res=$con->obtenerFilas($consulta);
			if($con->filasAfectadas>0)
			{
				while($fila=mysql_fetch_row($res))
				{
					$arrGrados[$fila[0]]=1;
				}
			}
			else
			{
				$consulta="SELECT COUNT(*) FROM 4500_gradosInstanciaPlanModificados WHERE idCiclo=".$idCiclo." AND idPeriodo=".$p." AND idInstancia=".$idInstancia;
				$nGrados=$con->obtenerValor($consulta);
				if($nGrados==0)
				{
					$consulta="SELECT idGrado FROM 4500_aperturaGradosPeriodo WHERE idPeriodo=".$p." AND idInstanciaPlanEstudio=".$idInstancia." AND valor=1";			
					$res=$con->obtenerFilas($consulta);
					while($fila=mysql_fetch_row($res))
					{
						$arrGrados[$fila[0]]=1;
					}
				}
				
			}
			
			
		}
		
		
		
		foreach($arrGrados as $g=>$resto)
		{
			$consulta="SELECT leyendaGrado FROM 4501_Grado WHERE idGrado=".$g;
			$arrGradosFinal[$g]=$con->obtenerValor($consulta);
			
			
		}
		
		return $arrGradosFinal;
		
	}
	
	
	function obtenerIdPlanEstudioInstancia($idInstancia)
	{
		global $con;
		$consulta="SELECT idPlanEstudio FROM 4513_instanciaPlanEstudio WHERE idInstanciaPlanEstudio=".$idInstancia;
		$idPlanEstudio=$con->obtenerValor($consulta);
	}
	
	function obtenerGradoMateria($idMateria,$idPlanEstudios)
	{
		global $con;
		$consulta="SELECT codigoPadre FROM 4505_estructuraCurricular WHERE idPlanEstudio=".$idPlanEstudios." AND idUnidad=".$idMateria." AND tipoUnidad=1";

		$codigoPadre=$con->obtenerValor($consulta);
		
		$idGrado=obtenerGradoPadre($codigoPadre,$idPlanEstudios);
		
		return $idGrado;
		
		
		
			
	}
	
	function obtenerGradoPadre($codigoUnidad,$idPlanEstudios)
	{
		global $con;
		if($codigoUnidad=="")
			return -1;
			
		$consulta="SELECT * FROM 4505_estructuraCurricular WHERE idPlanEstudio=".$idPlanEstudios." AND codigoUnidad='".$codigoUnidad."'";
		$fRegistro=$con->obtenerPrimeraFila($consulta);
		
		if($fRegistro[3]==3)	
			return $fRegistro[2];
		return obtenerGradoPadre($fRegistro[4],$idPlanEstudios);
		
	}
	
	function obtenerFechaFinCursoMateria($fechaInicio,$idMateria,$idInstancia,$idCiclo,$idPeriodo,$noBloque,$validarFechaFin=false)
	{
		global $con;

		$arrDatosMateriaHoras=obtenerDatosMateriaHoras($idMateria,$idInstancia,$idCiclo,$idPeriodo);//($idMateria,$idInstancia,$idCiclo=-1,$idPeriodo=-1)
		$hSemana	=$arrDatosMateriaHoras["horasSemana"];
		$hTotal	=$arrDatosMateriaHoras["horaMateriaTotal"];

		if($hSemana=="")
			$numSemanas=0;
		else
			$numSemanas=ceil($hTotal/$hSemana);
			
			
		$fechaFin=date("Y-m-d",strtotime("+".$numSemanas." week",strtotime($fechaInicio)));
		$fechaFin="'".date("Y-m-d",strtotime("-1 day",strtotime($fechaFin)))."'";



		if($validarFechaFin)
		{
			$consulta="SELECT ".$idCiclo.",".$idPeriodo.",".$idInstancia.",".$idGradoPadre.",".$noBloque;
			$fDatosGpo=$con->obtenerPrimeraFila($consulta);

			$consulta="SELECT fechaInicial,fechaFinal FROM 4544_fechasPeriodo WHERE idCiclo=".$fDatosGpo[0]." AND idPeriodo=".$fDatosGpo[1]." AND  idInstanciaPlanEstudio=".$fDatosGpo[2];
			$fFechasP=$con->obtenerPrimeraFila($consulta);
			$consulta="SELECT idEsquemaGrupo FROM 4513_instanciaPlanEstudio WHERE idInstanciaPlanEstudio=".$fDatosGpo[2];
			$tEsquemaFecha=$con->obtenerValor($consulta);
			
			if($tEsquemaFecha==2)
			{
				$consulta="SELECT min(fechaInicio) FROM 4571_fechasBloquePeriodo WHERE idInstancia=".$fDatosGpo[2]." AND noBloque=".$fDatosGpo[4]." AND idGrado in(".$fDatosGpo[3].
					") AND idCiclo=".$fDatosGpo[0]." AND idPeriodo=".$fDatosGpo[1];
				$filaFecha=$con->obtenerValor($consulta);			
				
				$fI=$filaFecha;
				if($fI!="")
					$fFechasP[0]=$fI;
					
				$consulta="SELECT max(fechaFin) FROM 4571_fechasBloquePeriodo WHERE idInstancia=".$fDatosGpo[2]." AND noBloque=".$fDatosGpo[4]." AND idGrado in(".$fDatosGpo[3].
					") AND idCiclo=".$fDatosGpo[0]." AND idPeriodo=".$fDatosGpo[1];
				$filaFecha=$con->obtenerValor($consulta);			
					
				if($filaFecha=="")	
				{
					$consulta="SELECT min(fechaInicio) FROM 4571_fechasBloquePeriodo WHERE idInstancia=".$fDatosGpo[2]." AND noBloque=".($fDatosGpo[4]+1)." AND idGrado= in(".$fDatosGpo[3].
							") AND idCiclo=".$fDatosGpo[0]." AND idPeriodo=".$fDatosGpo[1];
					$fF=$con->obtenerValor($consulta);
					if($fF!="")
					{
						$fF=date("Y-m-d",strtotime("-1 days",strtotime($fF)));
						$fFechasP[1]=$fF;
					}
				}
				else
				{
					$fFechas[1]=$filaFecha;
				}
			}
			
			
			if(strtotime(str_replace("'","",$fechaFin))>strtotime($fFechasP[1]))
			{
				$fechaFin="'".$fFechasP[1]."'";
			}
			
		}
		
		
		return $fechaFin;
	}
	
	function obtenerDatosMateriaHoras($idMateria,$idInstancia,$idCiclo=-1,$idPeriodo=-1)
	{
		global $con;
		
		$consulta="SELECT idPlanEstudio FROM 4513_instanciaPlanEstudio WHERE idInstanciaPlanEstudio=".$idInstancia;
		$idPlanEstudio=$con->obtenerValor($consulta);
		$consulta="SELECT ".$idPlanEstudio.",".$idMateria.",".$idCiclo.",".$idPeriodo.",".$idInstancia;
		$fDatosGrupos=$con->obtenerPrimeraFila($consulta);
	
		$consulta="SELECT noHorasSemana,totalHorasMateria FROM 4502_configuracionesAvanzadasMateria WHERE idInstanciaPlanEstudio=".$fDatosGrupos[4].
				" AND idMateria=".$fDatosGrupos[1]." AND idCiclo=".$fDatosGrupos[2]." AND idPeriodo=".$fDatosGrupos[3]." and situacion=1 ORDER BY idRegistro";

		$fDatosMateria=$con->obtenerPrimeraFila($consulta);
		if(!$fDatosMateria)
		{
			$consulta="SELECT noHorasSemana,totalHorasMateria FROM 4502_configuracionesAvanzadasMateria WHERE idInstanciaPlanEstudio=".$fDatosGrupos[4].
				" AND idMateria=".$fDatosGrupos[1]." AND idCiclo=".$fDatosGrupos[2]."  AND idPeriodo=-1 and situacion=1 ORDER BY idRegistro";
		
			$fDatosMateria=$con->obtenerPrimeraFila($consulta);
			if(!$fDatosMateria)
			{
				$consulta="SELECT noHorasSemana,totalHorasMateria FROM 4502_configuracionesAvanzadasMateria WHERE idInstanciaPlanEstudio=".$fDatosGrupos[4].
					" AND idMateria=".$fDatosGrupos[1]." AND idCiclo=-1  AND idPeriodo=".$fDatosGrupos[3]." and situacion=1 ORDER BY idRegistro";
		
				$fDatosMateria=$con->obtenerPrimeraFila($consulta);
				if(!$fDatosMateria)
				{
					$consulta="SELECT noHorasSemana,totalHorasMateria FROM 4502_configuracionesAvanzadasMateria WHERE idInstanciaPlanEstudio=".$fDatosGrupos[4].
							" AND idMateria=".$fDatosGrupos[1]." AND idCiclo=-1 AND idPeriodo=-1 and situacion=1  ORDER BY idRegistro";
			
					$fDatosMateria=$con->obtenerPrimeraFila($consulta);
					if(!$fDatosMateria)
					{
						$consulta="SELECT horasSemana,horaMateriaTotal FROM 4502_Materias  WHERE idMateria=".$fDatosGrupos[1];
						$fDatosMateria=$con->obtenerPrimeraFila($consulta);	
					}
				}
			}
		}
				
		
		$arrDatos=array();
		$arrDatos["horasSemana"]=$fDatosMateria[0];
		$arrDatos["horaMateriaTotal"]=$fDatosMateria[1];
		return $arrDatos ;
	}
	
	function obtenerValorConfiguracionEvaluacion($idReferenciaExamenes,$tipoExamen,$tipoValor)
	{
		global $con;	
		$valorConfiguracion="";
		$consulta="SELECT valor FROM 4626_configuracionesPerfilEvaluacion WHERE idPerfil=".$idReferenciaExamenes." AND tipoExamen=".$tipoExamen." AND idConfiguracion=".$tipoValor;
		
		$fRegistroValor=$con->obtenerPrimeraFila($consulta);
		if(!$fRegistroValor)
		{
			
			$consulta="SELECT * FROM 4624_configuracionesDefaultPerfilReglasEvaluacion WHERE idRegistro=".$tipoValor;
			$fila=$con->obtenerPrimeraFila($consulta);
			
			$valorConfiguracion=$fila[2]	  ;
		}
		else
			$valorConfiguracion=$fRegistroValor[0];
		
		

		return $valorConfiguracion;
		
		
		
	}
	
	
	function clonarPerfilCriterioEvaluacion($idPerfil,$idGrupo,$noExamen)
	{
		global $con;
		$consulta="SELECT * FROM 4591_perfilesEvaluacionMateria WHERE idPerfil=".$idPerfil;
		$fPerfil=$con->obtenerPrimeraFila($consulta);
		$consulta="INSERT INTO 4591_perfilesEvaluacionMateria(situacion,tipoPonderacion,clavePerfil,permiteAjuste,permiteAgregarCriterios,idGrupo,noExamen)
					VALUES(1,".$fPerfil[4].",'".cv($fPerfil[5])."',1,".$fPerfil[7].",".$idGrupo.",".$noExamen.")";
		if($con->ejecutarConsulta($consulta))
		{
			$idPerfilClon=$con->obtenerUltimoID();
			
			$query=array();
			$x=0;
			$query[$x]="begin";
			$x++;
			$codigoPadre=str_pad($idPerfil,7,"0",STR_PAD_LEFT);
			$codigoPadreClon=str_pad($idPerfilClon,7,"0",STR_PAD_LEFT);
			clonarCriterioHijoPerfil($idPerfil,$idPerfilClon,$codigoPadre,$codigoPadreClon,$query,$x);
			$query[$x]="commit";
			$x++;
			
			if($con->ejecutarBloque($query))
				return $idPerfilClon;
		}
		
		return 0;
	}
	
	function clonarCriterioHijoPerfil($idPerfil,$idPerfilClon,$codigoPadre,$codigoPadreClon,&$query,&$x)
	{
		global $con;
		$nCriterio=1;
		$consulta="SELECT * FROM 4564_criteriosEvaluacionPerfilMateria WHERE idPerfil=".$idPerfil." AND codigoPadre='".$codigoPadre."' 
					ORDER BY idCriteriosEvaluacionPerfilMateria ";

		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$codigoUnidad=$codigoPadreClon.str_pad($nCriterio,7,"0",STR_PAD_LEFT);
			if($fila[6]=="")
				$fila[6]="NULL";
			
			if($fila[7]=="")
				$fila[7]="NULL";
				
			$query[$x]="INSERT INTO 4564_criteriosEvaluacionPerfilMateria(idPerfil,codigoUnidad,codigoPadre,idCriterio,tipoPonderacion,idFuncionInicializacion,idFuncionScriptGuardar,obligatorio)
						VALUES(".$idPerfilClon.",'".$codigoUnidad."','".$codigoPadreClon."',".$fila[4].",".$fila[5].",".$fila[6].",".$fila[7].",".$fila[8].")";
			$x++;
			$query[$x]="INSERT INTO 4565_porcentajeCriterioEvaluacionPerfil(idCriterioEvaluacionMateria,bloque,porcentaje,idPerfil,porcentajeMaximo,porcentajeMinimo)
						SELECT '".$codigoUnidad."' AS idCriterioEvaluacionMateria,bloque,porcentaje,'".$idPerfilClon."' as idPerfil,porcentajeMaximo,porcentajeMinimo FROM 4565_porcentajeCriterioEvaluacionPerfil 
						WHERE idCriterioEvaluacionMateria='".$fila[2]."'";
			$x++;
			$nCriterio++;
			clonarCriterioHijoPerfil($idPerfil,$idPerfilClon,$fila[2],$codigoUnidad,$query,$x);
			
		}
	}
	
	function destruirPerfilCriterioEvaluacion($idPerfil)
	{
		global $con;
		$x=0;
		$query[$x]="begin";
		$x++;
		
		
		$query[$x]="DELETE FROM 4591_perfilesEvaluacionMateria WHERE idPerfil=".$idPerfil;
		$x++;
		$query[$x]="DELETE FROM 4564_criteriosEvaluacionPerfilMateria WHERE idPerfil=".$idPerfil;
		$x++;
		$query[$x]="DELETE FROM 4565_porcentajeCriterioEvaluacionPerfil WHERE idPerfil=".$idPerfil;
		$x++;
		
		$query[$x]="commit";
		$x++;
		
		return $con->ejecutarBloque($query);
		
	}
	
	
	function obtenerPerfilCriterioEvaluacionGrupo($idGrupo,$noExamen)
	{
		global $con;
		$consulta="SELECT idInstanciaPlanEstudio,idCiclo,idPeriodo FROM 4520_grupos WHERE idGrupos=".$idGrupo;
		$fGrupo=$con->obtenerPrimeraFila($consulta);
		$idInstancia=$fGrupo[0];
		
		
		$consulta="SELECT idPerfilEvaluacion FROM 4513_instanciaPlanEstudio WHERE idInstanciaPlanEstudio=".$idInstancia;
		$idPerfilEvaluacion=$con->obtenerValor($consulta);
		
		if($idPerfilEvaluacion=="")
			$idPerfilEvaluacion=-1;
		
		$consulta="SELECT valor FROM 4626_configuracionesPerfilEvaluacion WHERE idPerfil=".$idPerfilEvaluacion." AND tipoExamen=".$noExamen." AND idConfiguracion=12";
		$idPerfilBase=$con->obtenerValor($consulta);
		if($idPerfilBase=="")
			$idPerfilBase=-1;
		
		$consulta="SELECT permiteAjuste FROM 4591_perfilesEvaluacionMateria WHERE idPerfil=".$idPerfilBase;
		$permiteAjuste=$con->obtenerValor($consulta);
		$idPerfil="-1";
		$arrGrupos="";
		if($permiteAjuste==1)
		{
			$consulta="SELECT idPerfil FROM 4591_perfilesEvaluacionMateria WHERE idGrupo=".$idGrupo." and noExamen=".$noExamen;
			
			$idPerfil=$con->obtenerValor($consulta);
			if($idPerfil=="")
				$idPerfil=-1;
		}
		else
			$idPerfil=$idPerfilBase;
		
		return $idPerfil;
	}
	
	function validarConfiguracionCriterioEvaluacion($idPerfil)
	{
		global $con;
		$arrComentarios=array();
		$arrComentariosCriterio=array();
		$folioPadre=str_pad($idPerfil,7,"0",STR_PAD_LEFT);
		
		$consulta="SELECT tipoPonderacion,permiteAjuste,permiteAgregarCriterios,idGrupo FROM 4591_perfilesEvaluacionMateria WHERE idPerfil=".$idPerfil;
		$fPerfil=$con->obtenerPrimeraFila($consulta);
		
		
		if(($fPerfil[3]==0)&&(($fPerfil[1]==1)||($fPerfil[2]==1)))
		{
			
			$totalPorcentaje=100;
			$consulta="SELECT idCriterio,tipoPonderacion,codigoUnidad,
						(SELECT porcentajeMinimo FROM 4565_porcentajeCriterioEvaluacionPerfil WHERE idPerfil=c.idPerfil AND idCriterioEvaluacionMateria=c.codigoUnidad) AS porcentajeMinimo ,
						(SELECT porcentajeMaximo FROM 4565_porcentajeCriterioEvaluacionPerfil WHERE idPerfil=c.idPerfil AND idCriterioEvaluacionMateria=c.codigoUnidad) AS porcentajeMaximo ,
						(SELECT titulo FROM 4010_evaluaciones WHERE idEvaluacion=c.idCriterio) as criterioEvaluacion
						FROM 4564_criteriosEvaluacionPerfilMateria c WHERE idPerfil=".$idPerfil." AND codigoPadre='".$folioPadre."'";
						
			$rCriterios=$con->obtenerFilas($consulta);
			while($fCriterio=mysql_fetch_row($rCriterios))
			{

				if($fCriterio[1]==1)
				{
					if($fCriterio[3]=="")
						$fCriterio[3]=0;
					
					if($fCriterio[4]=="")
						$fCriterio[4]=0;	
						
					if(($fCriterio[3]>0)&&($fCriterio[3]>$fCriterio[4]))
					{
						array_push($arrComentarios,"El porcentaje m&iacute;nimo (".removerCerosDerecha(number_format($fCriterio[3],2))." %) del criterio <b>".$fCriterio[5].
					  						"</b> no puede ser mayor al porcentaje m&aacute;ximo (".removerCerosDerecha(number_format($fCriterio[4],2))." %) ");
					}
					
				}
				
				$arrComentariosAux=array();
				validarCriterioEvaluacionHijoValoresMinimosMaximos($idPerfil,$fCriterio[2],$arrComentariosAux);
				foreach($arrComentariosAux as $c)
				{
					array_push($arrComentariosCriterio,$c);
				}
				
			}
			
		}
		else
		{
			$consulta="SELECT idCriterio,tipoPonderacion,codigoUnidad,(SELECT porcentaje FROM 4565_porcentajeCriterioEvaluacionPerfil WHERE idPerfil=c.idPerfil AND idCriterioEvaluacionMateria=c.codigoUnidad) 
						AS porcentaje ,(SELECT titulo FROM 4010_evaluaciones WHERE idEvaluacion=c.idCriterio) as criterioEvaluacion
						FROM 4564_criteriosEvaluacionPerfilMateria c WHERE idPerfil=".$idPerfil." AND codigoPadre='".$folioPadre."'";
						
			$rCriterios=$con->obtenerFilas($consulta);
			if(($con->filasAfectadas==0)&&(($fPerfil[2]==0)||($fPerfil[3]!=0)))
			{
				array_push($arrComentarios,"Almenos debe agregar un criterio de evaluaci&oacute;n");
			}
			else
			{
				$totalPorcentaje=0;
				if($fPerfil[0]==2)
					$totalPorcentaje=100;
					
				while($fCriterio=mysql_fetch_row($rCriterios))
				{
					if($fPerfil[0]!=2)
					{
						if($fCriterio[3]=="")
							$fCriterio[3]=0;
						$totalPorcentaje+=$fCriterio[3];
					}
					$arrComentariosAux=array();
					validarCriterioEvaluacionHijo($idPerfil,$fCriterio[2],$arrComentariosAux);
					foreach($arrComentariosAux as $c)
					{
						array_push($arrComentariosCriterio,$c);
					}
					
				}
				
				
				
			}
		}
		
		if($totalPorcentaje<100)
		{
			array_push($arrComentarios,"La suma de los porcentajes de los criterios de evaluaci&oacute;n no es igual al 100% (Suma actual: ".removerCerosDerecha($totalPorcentaje)."%)");
		}
		foreach($arrComentariosCriterio as $c)
			array_push($arrComentarios,$c);
		
		
		return $arrComentarios;
				
	}
	
	function validarCriterioEvaluacionHijo($idPerfil,$codigoUnidad,&$arrComentarios)
	{
		global $con;
		$arrComentariosCriterio=array();
		
		/*$consulta="SELECT tipoPonderacion,permiteAjuste,permiteAgregarCriterios,idGrupo FROM 4591_perfilesEvaluacionMateria WHERE idPerfil=".$idPerfil;
		$fPerfil=$con->obtenerPrimeraFila($consulta);*/
		
		$consulta="SELECT tipoPonderacion,(SELECT titulo FROM 4010_evaluaciones WHERE idEvaluacion=c.idCriterio) as criterioEvaluacion FROM 4564_criteriosEvaluacionPerfilMateria c
					WHERE idPerfil=".$idPerfil." AND codigoUnidad='".$codigoUnidad."'";
		$fCriterioBase=$con->obtenerPrimeraFila($consulta);
		
		$consulta="SELECT idCriterio,tipoPonderacion,codigoUnidad,(SELECT porcentaje FROM 4565_porcentajeCriterioEvaluacionPerfil WHERE idPerfil=c.idPerfil AND idCriterioEvaluacionMateria=c.codigoUnidad) 
						AS porcentaje ,(SELECT titulo FROM 4010_evaluaciones WHERE idEvaluacion=c.idCriterio) as criterioEvaluacion
						FROM 4564_criteriosEvaluacionPerfilMateria c WHERE idPerfil=".$idPerfil." AND codigoPadre='".$codigoUnidad."'";
						
		$rCriterios=$con->obtenerFilas($consulta);
		if($con->filasAfectadas==0)
		{
			$totalPorcentaje=100;
		}
		else
		{
			$totalPorcentaje=0;
			if($fCriterioBase[0]==2)
				$totalPorcentaje=100;
				
			while($fCriterio=mysql_fetch_row($rCriterios))
			{
				if($fCriterioBase[0]!=2)
				{
					if($fCriterio[3]=="")
						$fCriterio[3]=0;
					$totalPorcentaje+=$fCriterio[3];
				}
				$arrComentariosAux=array();
				validarCriterioEvaluacionHijo($idPerfil,$fCriterio[2],$arrComentariosAux);
				foreach($arrComentariosAux as $c)
				{
					array_push($arrComentariosCriterio,$c);
				}
			}
		}
		if($totalPorcentaje<100)
		{
			array_push($arrComentarios,"La suma de los porcentajes de los criterios de evaluaci&oacute;n pertenecientes al criterio <b>".$fCriterioBase[1]."</b> no es igual al 100% (Suma actual: ".removerCerosDerecha($totalPorcentaje)."%)");
		}
		foreach($arrComentariosCriterio as $c)
			array_push($arrComentarios , $c);
		
	}
	
	
	function validarCriterioEvaluacionHijoValoresMinimosMaximos($idPerfil,$codigoUnidad,&$arrComentarios)
	{
		global $con;
		$arrComentariosCriterio=array();
		
		
		$consulta="SELECT idCriterio,tipoPonderacion,codigoUnidad,
						(SELECT porcentajeMinimo FROM 4565_porcentajeCriterioEvaluacionPerfil WHERE idPerfil=c.idPerfil AND idCriterioEvaluacionMateria=c.codigoUnidad) AS porcentajeMinimo ,
						(SELECT porcentajeMaximo FROM 4565_porcentajeCriterioEvaluacionPerfil WHERE idPerfil=c.idPerfil AND idCriterioEvaluacionMateria=c.codigoUnidad) AS porcentajeMaximo ,
						(SELECT titulo FROM 4010_evaluaciones WHERE idEvaluacion=c.idCriterio) as criterioEvaluacion
						FROM 4564_criteriosEvaluacionPerfilMateria c WHERE idPerfil=".$idPerfil." AND codigoPadre='".$codigoUnidad."'";
		
		
		$rCriterios=$con->obtenerFilas($consulta);				
		
				
		  while($fCriterio=mysql_fetch_row($rCriterios))
		  {
			  if($fCriterio[1]==1)
			  {
				  if($fCriterio[3]=="")
						$fCriterio[3]=0;
					
					if($fCriterio[4]=="")
						$fCriterio[4]=0;
						
				  if(($fCriterio[3]>0)&&($fCriterio[3]>$fCriterio[4]))
				  {
					  array_push($arrComentarios,"El porcentaje m&iacute;nimo (".removerCerosDerecha(number_format($fCriterio[3],2))." %) del criterio <b>".$fCriterio[5].
					  						"</b> no puede ser mayor al porcentaje m&aacute;ximo (".removerCerosDerecha(number_format($fCriterio[4],2))." %) ");
				  }
				  
			  }
			  
			  $arrComentariosAux=array();
			  validarCriterioEvaluacionHijoValoresMinimosMaximos($idPerfil,$fCriterio[2],$arrComentariosAux);
			  foreach($arrComentariosAux as $c)
			  {
				  array_push($arrComentariosCriterio,$c);
			  }
		  }
		
		
		foreach($arrComentariosCriterio as $c)
			array_push($arrComentarios , $c);
		
	}
	
	
?>