<?php
include_once("latis/funcionesNeotrai.php");
include_once("latis/crearCURP.php");

function obtenerMatriculaInterna2($plantel,$idPlanEstudio,$idCiclo)
{
	global $con;
	$anio=date('y');
	$consultaCampus="SELECT codigoDepto FROM 817_organigrama WHERE codigoUnidad='".$plantel."'";
	$campus=$con->obtenerValor($consultaCampus);
	$areaEstudio="SELECT areaEspecialidad FROM 4500_planEstudio WHERE idPlanEstudio='".$idPlanEstudio."'";
	$area=$con->obtenerValor($areaEstudio);
	
	$consulNumero="SELECT folioActual FROM 4572_folioMatricula WHERE codigoUnidad='".$plantel."' AND idCiclo='".$idCiclo."'";
	$consecutivo=$con->obtenerValor($consulNumero);
		$x=0;
		$consulta[$x]="begin";
		$x++;
	
	if($consecutivo=="")
	{
		$numero='1';
		$consulta[$x]="INSERT INTO 4572_folioMatricula(codigoUnidad,idCiclo,folioActual)VALUES('".$plantel."','".$idCiclo."','".$numero."')";
		$x++;
	}
	else
	{
		$numero=$consecutivo+1;
		$consulta[$x]="update 4572_folioMatricula set folioActual='".$numero."' where codigoUnidad='".$plantel."' and idCiclo='".$idCiclo."'";
		$x++;
	}
	$longitud=strlen($numero);
	switch($longitud)
	{
		case 1:
			$numConsecutivo='000'.$numero;
		break;
		case 2:
			$numConsecutivo='00'.$numero;
		break;
		case 3:
			$numConsecutivo='0'.$numero;
		break;
		default:
			$numConsecutivo=$numero;
	}
		$consulta[$x]="commit";
		$x++;
		$con->ejecutarBloque($consulta);
	
	//año-Campus-area-numero sConsecutivo
	$registro=$anio.$campus.$area.$numConsecutivo;
	return $registro;
}

function validarFormulario678($nombre,$paterno,$materno,$genero,$edoNac,$fechaNac,$curp,$llaveCurp,$correo,$idRegistro,$nacionalidad)
{
	global $con;
	$idUsuario=-1;
	$fechaA=date("Y-m-d");
	$anioA=date("Y");
	$msj="";
	$plantel=$_SESSION["codigoInstitucion"];	
	$fechaNacimiento=cambiaraFechaMysql($fechaNac);

	if($nacionalidad==1)
	{
		$curp=$curp.$llaveCurp;
		$diaNac=date("d",strtotime($fechaNacimiento));
		$mesNac=date("m",strtotime($fechaNacimiento));
		$anioNac=date("Y",strtotime($fechaNacimiento));
		$limiteAnio=$anioA-10;
		if($anioNac>=$limiteAnio)
		{
			$msj="Error de registro en la fecha de nacimiento<br>";
			echo $msj;
			return;
		}
		if($idRegistro!=-1)
		{
			$consultaUsuario="SELECT idReferencia FROM _678_tablaDinamica WHERE id__678_tablaDinamica='".$idRegistro."'";
			$refe=$con->obtenerValor($consultaUsuario);
			$consulUsuario="SELECT idUsuario FROM 4573_solicitudesInscripcion WHERE idSolicitudInscripcion='".$refe."'";
			$idUsuario=$con->obtenerValor($consulUsuario);
			if(!$idUsuario)
				$idUsuario=-1;
		}
		//Se valida CURP escritura
		if($curp!="")
		{
			$validarExtructura=validarCurp($curp);
			if($validarExtructura!=1)
			{
				$msj="Error en la estructura de la CURP<br>";
				echo $msj;
				return;
			}
		}
		//Se valida curp que no exista
			//$existeCURP="";
			$existeCURP=validarExisteCurp($curp,$plantel,$idUsuario);
			if($existeCURP==1)
			{
				$msj="CURP ya existente en esta Institución<br>";
				echo $msj;
				return;
			}
		
		//Se valida el correo
		$existeCorreo=validarCorreoInscripcion($correo,$plantel,$idUsuario);
			if($existeCorreo==1)
			{
				$msj="El correo electronico ya existente en esta Institución<br>";
				echo $msj;
				return;
			}
	}
	echo "1|";
}

function validarDatos678Inscripcion($nombre,$paterno,$materno,$genero,$edoNac,$fechaNac,$curp,$correo,$idRegistro)
{
	global $con;
	$res="1|";
	
	$rCURP=validarCURPInscripcion($curp);
	if($rCURP=='1')
	{
		$res="CURP incorrecta<br>";
		echo $res;
	}
	
	if($idRegistro==-1)
	{
		
		$rCorreo=validarCorreoInscripcion($correo,-1);
		if($rCorreo=='1')
		{
			$res="Email ya existente<br>";
			echo $res;
		}
		
		$exCurp=validarExisteCurp($curp,-1);
		if($exCurp=='1')
		{
			$res="CURP ya existente<br>";
			echo $res;
		}
		
		return $res;
	}
	else
	{
		$consulta="SELECT idUsuario FROM _678_tablaDinamica WHERE id__678_tablaDinamica='".$idRegistro."'";
		$idUsuario=$con->obtenerValor($consulta);

		$rCorreo=validarCorreoInscripcion($correo,$idUsuario);
		if($rCorreo=='1')
		{
			$res="Email ya existente<br>";
			echo $res;
		}
		
		$exCurp=validarExisteCurp($curp,$codigoUnidad);
		if($exCurp=='1')
		{
			$res="CURP ya existente<br>";
			echo $res;
		}
		
		return $res;
	}
}
function validarCURPInscripcion($curp,$fechaNac,$nombre,$paterno,$materno)
{
	$valor=0;
	 $validarExtructura=validarCurp($curp);
	
	 if($validarExtructura!=1)
	 {
		 return 1;
	 }
	return $valor;	
}

function validarCorreoInscripcion($correo,$plantel,$idUsuario)
{
	global $con;
	$valor=0;
	$consul="";
	if($idUsuario!=-1)
	{
		$consul=" AND a.idUsuario<>'".$idUsuario."'";
	}
	$consulta="SELECT idMail FROM 805_mails m,801_adscripcion a WHERE m.idUsuario=a.idUsuario AND a.Institucion='".$plantel."' 
				AND m.Mail='".$correo."'$consul";
	$resp=$con->obtenerValor($consulta);
	if($resp)
	{
		$valor='1';
	}
	return $valor;
}

function validarExisteCurp($curp,$plantel,$idUsuario)
{
	global $con;
	$valor=0;
	$consul="";
	
	if($idUsuario!=-1)
		$consul=" AND i.idUsuario<>'".$idUsuario."'";
	
	$consulta="SELECT idIdentifica FROM 802_identifica i,801_adscripcion a WHERE i.idUsuario=a.idUsuario AND a.Institucion='".$plantel."' 
				AND i.CURP='".$curp."'$consul ";
	$resp=$con->obtenerValor($consulta);
	if($resp)
	{
		$valor='1';
	}
	return $valor;
}

function generarCurp($nombre,$paterno,$materno,$genero,$edoNac,$fechaNac)
{
	global $con;
	$fechaA=date("Y-m-d");
	$anioA=date("Y");
	$msj="";
	$Ape1=trim(strtoupper($paterno));	
	$Ape2=trim(strtoupper($materno));
	$nombre=trim(strtoupper($nombre));
	$fechaNac=cambiaraFechaMysql($fechaNac);
	$fechaNacimiento=$fechaNac;
	$diaNac=date("d",strtotime($fechaNacimiento));
	$mesNac=date("m",strtotime($fechaNacimiento));
	$anioNac=date("Y",strtotime($fechaNacimiento));
	$anioCurp=substr($anioNac,2,2);		
	//echo $fechaNac."<br>";
	
	$vocales=array("A","E","I","O","U");
    $consonantes =array("B","C","D","F","G","H","I","J","K","L","M","N","Ñ","P","Q","R","S","T","V","W","X","Y","Z");
	switch($genero)
	{
		case 0:
				$sexo='H';
		break;
		case 1:
				$sexo='M';
		break;
	}
	//echo "Genero ".$genero;
	$consultarEntidad="SELECT cveCurp FROM 820_estados WHERE cveEstado='".$edoNac."'";
	$if=$con->obtenerValor($consultarEntidad);

//	$Curp=new B_CURP();//creamos el objeto de la curp
//	$Curp->SetDatos($nombre, $Ape1, $Ape2 , $sexo, $diaNac, $mesNac,$anioCurp, $if);//se meten datos para que calcule una posible curp
//	//echo $Curp->CURP;
//
//	$nuevaCurp=$Curp->CURP;
//	if($nuevaCurp!="")
//	{
//		return $nuevaCurp;
//	}
	
	//Apellido paterno
	    $curp=substr($Ape1,0,1);//primera letra

        for($i=1;$i<strlen($Ape1);$i++)
		{
            if(array_search(substr($Ape1,$i,1),$vocales)!==false)
			{
                $curp.=substr($Ape1,$i,1);//primera vocal que no sea la primera
                break;
            }
		}
		
        $curp.=substr($Ape2,0,1).substr($nombre,0,1).substr($anioNac,2,2).$mesNac.$diaNac.$sexo.$if;
        for($i=1;$i<strlen($Ape1);$i++)
		{
            if(array_search(substr($Ape1,$i,1),$consonantes)!==false)
			{
                $curp.=substr($Ape1,$i,1);//primera vocal que no sea la primera
                break;
            }
		}
        for($i=1;$i<strlen($Ape2);$i++)
		{
            if(array_search(substr($Ape2,$i,1),$consonantes)!==false)
			{
                $curp.=substr($Ape2,$i,1);//primera vocal que no sea la primera
                break;
            }
		}
        for($i=1;$i<strlen($nombre);$i++)
		{
            if(array_search(substr($nombre,$i,1),$consonantes)!==false)
			{
                $curp.=substr($nombre,$i,1);//primera vocal que no sea la primera
                break;
            }
		}
	return $curp;
}

function actualizarMatriculaInterna($idRegistro)
{
	global $con;
	$consultar="SELECT idReferencia,codigoInstitucion FROM _678_tablaDinamica WHERE id__678_tablaDinamica='".$idRegistro."'";
	$resul=$con->obtenerPrimeraFila($consultar);
	
	$queryU="SELECT idUsuario,idCiclo,idPeriodo,idInstancia FROM 4573_solicitudesInscripcion WHERE idSolicitudInscripcion=".$resul[0];
	$resp=$con->obtenerPrimeraFila($queryU);
	
	$idUsuario=$resp[0];
	$plantel=$resul[1];
	$planEstudio=$resp[3];
	$idCiclo=$resp[1];
	$idPeriodo=$resp[2];
	
	$matriculaInterna=obtenerMatriculaInterna2($plantel,$planEstudio,$idCiclo);
	$x=0;
	$consulta[$x]="begin";
	$x++;
	$consulta[$x]="UPDATE 4537_situacionActualAlumno  SET matricula='".$matriculaInterna."' WHERE idAlumno='".$idUsuario."' 
				AND idInstanciaPlanEstudio='".$planEstudio."' AND situacionAlumno='1'";
	$x++;
	$consulta[$x]="UPDATE 4529_alumnos SET matricula='".$matriculaInterna."' WHERE idUsuario='".$idUsuario."' AND idInstanciaPlanEstudio='".$planEstudio."' 
		AND idCiclo='".$idCiclo."' AND idPeriodo='".$idPeriodo."' AND estado='1'";
	$x++;
	$consulta[$x]="UPDATE 800_usuarios SET cuentaActiva='1' WHERE idUsuario='".$idUsuario."'";
	$x++;
	$consulta[$x]="commit";
	$x++;
	$con->ejecutarBloque($consulta);
}

function finalizarPreInscripcion($idRegistro)
{
	global $con;
	$consultar="SELECT idReferencia,codigoInstitucion FROM _678_tablaDinamica WHERE id__678_tablaDinamica='".$idRegistro."'";
	$resul=$con->obtenerPrimeraFila($consultar);
	
	$queryU="SELECT idUsuario,idCiclo,idPeriodo,idInstancia FROM 4573_solicitudesInscripcion WHERE idSolicitudInscripcion=".$resul[0];
	$resp=$con->obtenerPrimeraFila($queryU);
	$idUsuario=$resp[0];
	$planEstudio=$resp[3];
	
	$consulMat="SELECT matriculaOficial FROM _982_tablaDinamica WHERE idReferencia='".$idRegistro."'";
	$matricula=$con->obtenerValor($consulMat);
	if($matricula!="")
	{
		$actualizar="UPDATE 4537_situacionActualAlumno SET matriculaOficial='".$matricula."' WHERE idAlumno='".$idUsuario."' 
					AND idInstanciaPlanEstudio='".$planEstudio."' AND situacionAlumno='1'";
		$con->ejecutarConsulta($actualizar);
	}
}

?>