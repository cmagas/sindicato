<?php	include_once("latis/libreriaFuncionesSistema.php");
		include_once("latis/funcionesFormularios.php");
		include_once ("latis/cExcel.php");
		include_once("latis/cContabilidad.php");
		include_once("latis/funcionesValidacionGrupos.php");

	function validarMontoCensida($idFormulario,$idCategoria,$monto,$idRegistro)
	{
		global $con;	
		$monto=normalizarNumero($monto);
		$consulta="SELECT montoMinimo,montoMaximo FROM _312_montosCategoria WHERE categoria=".$idCategoria;	
		$fila=$con->obtenerPrimeraFila($consulta);
		$res="1|";
		if(($idCategoria!=3)&&($idCategoria!=7)&&($idRegistro=="-1"))
		{
			$consulta="SELECT COUNT(idEstado) FROM _293_tablaDinamica WHERE categorias=".$idCategoria." AND codigoInstitucion='".$_SESSION["codigoInstitucion"]."'";
			$nProyectos=$con->obtenerValor($consulta);
			if($nProyectos>4)
			{
				$res="Ha excedido el n&uacute;mero m&aacute;ximo(4) de proyectos que puede registrar bajo esta catego&iacute;a|categorias";
				return $res;
			}
		}
		
		
		if($fila)
		{
			if($idCategoria!=7)
			{
				if($monto=="")
				{
					$res="El monto ingresado no es v&aacute;lido";				
				}
			}
			if($monto<$fila[0])
			{
				$res="El monto ingresado es <b>menor</b> que el monto m&iacute;nimo permitido de acuerdo a la categor&iacute;a ($ ".number_format($fila[0],2,".",",").")|txtPresupuestoCensida";
			}
			if($monto>$fila[1])
			{
				$res="El monto ingresado es <b>mayor</b> que el monto m&aacute;ximo permitido de acuerdo a la categor&iacute;a ($ ".number_format($fila[1],2,".",",").")|txtPresupuestoCensida";
			}
		}
		return $res;
	}
	
	function validarSolicitudLicencia($idFormulario,$fInicio,$fFin,$tipoSolicitud)
	{
		$res="1|";
		$fechaInicio=cambiaraFechaMysql($fInicio);
		$fechaFin=cambiaraFechaMysql($fFin);
		
		$diferenciaDias=obtenerDiferenciaDias($fechaInicio,$fechaFin);
		
		
		
		if($tipoSolicitud==2)
		{
			if($diferenciaDias>2)
			{
				$res="S&oacute;lo puede seleccionar como m&aacute;ximo dos d&iacute;as econ&oacute;micos por mes";
			}
			else
			{
				$resultado=autorizarDiaEconomico($fechaInicio,$fechaFin,$_SESSION["idUsr"]);
				if($resultado==0)	
				{
					$res="No puede solicitar un d&iacute;a econ&oacute;mico en un fecha previa o posterior a un d&iacute;n festivo";
				}
			}
		}
		return $res;	
	}
	
	//
	
	function usuarioNoparticipante($idFormulario,$idRegistro,$idUsuario)
	{
		global $con;
		$consulta="SELECT idUsuario FROM 246_autoresVSProyecto WHERE idFormulario=".$idFormulario." AND idReferencia=".$idRegistro." and idUsuario=".$idUsuario;	
		$fila=$con->obtenerPrimeraFila($consulta);
		if($fila)
			return true;
		else
			return false;
	}
	
	function existeSolicitudNoAutor($idFormulario,$idRegistro,$idUsuario)
	{
		global $con;
		$consulta="SELECT idUsuario FROM 9202_accionesSolicitudUsuario WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro." and idUsuario=".$idUsuario." and accion=-1 and estado=1";	
		$fila=$con->obtenerPrimeraFila($consulta);
		if($fila)
			return true;
		else
			return false;
	}
	
	function usuarioNoparticipanteNoSolicitudNoautor($idFormulario,$idRegistro,$idUsuario)
	{
		global $con;
		$consulta="SELECT idUsuario FROM 246_autoresVSProyecto WHERE idFormulario=".$idFormulario." AND idReferencia=".$idRegistro." and idUsuario=".$idUsuario;	
		$fila=$con->obtenerPrimeraFila($consulta);
		if($fila)
		{
			return !existeSolicitudNoAutor($idFormulario,$idRegistro,$idUsuario);
		}
		else
			return false;
	}
	
	function permiteSometerProduccion2011($idFormulario,$idRegistro,$idUsuario)
	{
		global $con;
		$consulta="SELECT idUsuario FROM 9200_usuariosInscritosConvocatoria WHERE idConvocatoria=1000 AND idUsuario=".$idUsuario;
		$fila=$con->obtenerPrimeraFila($consulta);
		if($fila)
		{
			if(!existeSolicitudNoAutor($idFormulario,$idRegistro,$idUsuario))
			{
				$consulta="SELECT idUsuario FROM 246_autoresVSProyecto WHERE idFormulario=".$idFormulario." AND idReferencia=".$idRegistro." and idUsuario=".$idUsuario;	
				$fila=$con->obtenerPrimeraFila($consulta);
				if($fila)
				{
					$consulta="SELECT idUsuario FROM 9201_productosUsuarioConvocatoria WHERE idUsuario=".$idUsuario." AND idFormulario=".$idFormulario." AND idConvocatoria=1000 and idRegistro=".$idRegistro;
					$fila=$con->obtenerPrimeraFila($consulta);
					if($fila)
					{
						return false;
					}
					return true;
				}
				return false;
			}
			return false;
		}
		return false;
	}
	
	function articuloInscritoConvocatoria($idFormulario,$idRegistro,$idUsuario)
	{
		global $con;
		$consulta="SELECT idUsuario FROM 9201_productosUsuarioConvocatoria WHERE idUsuario=".$idUsuario." AND idFormulario=".$idFormulario." AND idConvocatoria=1000 and idRegistro=".$idRegistro;
		$fila=$con->obtenerPrimeraFila($consulta);
		if($fila)
		{
			return true;
		}
		return false;
	}
	
	function existeSolicitudParticipacion($idFormulario,$idRegistro,$idUsuario)
	{
		global $con;
		$consulta="SELECT idRegistro FROM 9202_accionesSolicitudUsuario WHERE idFormulario=".$idFormulario." and idRegistro=".$idRegistro." and idUsuario=".$idUsuario." AND estado=1 AND accion=5";	
		$fila=$con->obtenerPrimeraFila($consulta);
		if($fila)
			return true;
		else
			return false;
	}
	
	function asignarProyectosRevisorV2()
	{
		global $con;
		$consulta="SELECT id__293_tablaDinamica,categorias,totalRevisores FROM (
					SELECT id__293_tablaDinamica,t.codigo,categorias AS Folio,t.tituloProyecto,categorias,
					(SELECT COUNT(idUsuario) FROM 1011_asignacionUsuariosProyectos WHERE tipoRevisor=1 AND idProyecto=t.id__293_tablaDinamica) 
					+
					(SELECT COUNT(idUsuario) FROM 1011_asignacionUsuariosProyectos WHERE tipoRevisor=2 AND idProyecto=t.id__293_tablaDinamica) 
					AS totalRevisores
					FROM _293_tablaDinamica t WHERE t.idEstado = 2.81) AS t WHERE totalRevisores<3 AND categorias=8 ORDER BY categorias";
					
					
		/*$consulta="SELECT id__293_tablaDinamica,categorias,totalRevisores FROM (
					SELECT id__293_tablaDinamica,t.codigo,categorias AS Folio,t.tituloProyecto,categorias,
					(SELECT COUNT(idUsuario) FROM 1011_asignacionUsuariosProyectos WHERE tipoRevisor=1 AND idProyecto=t.id__293_tablaDinamica) 
					+
					(SELECT COUNT(idUsuario) FROM 1011_asignacionUsuariosProyectos WHERE tipoRevisor=2 AND idProyecto=t.id__293_tablaDinamica) 
					AS totalRevisores
					FROM _293_tablaDinamica t WHERE t.idEstado IN (2.1,2.2,2.3,2.4,2.5,2.6,2.7)) AS t WHERE totalRevisores<3 AND categorias<>7 ORDER BY categorias";					
		*/			
		$resProy=$con->obtenerFilas($consulta);
		$x=0;
		$consulta="begin";
		$con->ejecutarConsulta($consulta);
		$actor=array();
		$actor[1]=118;
		$actor[2]=104;
		$actor[3]=106;
		$actor[4]=108;
		$actor[5]=110;
		$actor[6]=112;
		$actor[8]=143;
		$n=1;
		$nRevisionesRestantes=0;
		while($fila=mysql_fetch_row($resProy))					
		{
			$idProyecto=$fila[0];
			$categoria=$fila[1];
			$totalRevisores=3-$fila[2];
			$nRevisionesRestantes+=$totalRevisores;
			$consulta="select min(numProyAsignados) from 1010_revisionRevisores where tipo_Revisor=1 AND num_Max>numProyAsignados  and idCategoria=".$fila[1];
			$minValor=$con->obtenerValor($consulta);
			if($minValor=="")
				$minValor=0;
			$consulta="SELECT idUsuario FROM 1011_asignacionUsuariosProyectos WHERE idProyecto=".$fila[0];
			$listUsr=$con->obtenerListaValores($consulta);
			if($listUsr=="")
				$listUsr="-1";
			$consulta="SELECT idUsuario,idRegistro,tipo_Revisor FROM 1010_revisionRevisores WHERE tipo_Revisor=1 AND num_Max>numProyAsignados and idUsuario not in (".$listUsr.") 
			and numProyAsignados=".$minValor." AND idCategoria=".$fila[1]." LIMIT 0,".$totalRevisores;
			$n++;
			$resRevisor=$con->obtenerFilas($consulta);
			$listRegistros="";
			$x=0;
			$query=array();
			if($con->filasAfectadas>0)
			{
				while($filaRevisor=mysql_fetch_row($resRevisor))
				{
					if($listRegistros=="")
						$listRegistros=$filaRevisor[1];
					else
						$listRegistros.=",".$filaRevisor[1];
					
					$query[$x]="INSERT INTO 1011_asignacionUsuariosProyectos(idUsuario,idCategoria,idProyecto,tipoRevisor) VALUES(".$filaRevisor[0].",".$fila[1].",".$fila[0].",".$filaRevisor[2].")";
					$x++;	
					$query[$x]="INSERT INTO 955_revisoresProceso(idFormulario,idReferencia,idUsuarioRevisor,idProceso,idFormDictamen,idActorProcesoEtapa,estado,fechaAsignacion,fechaDictamen,versionRegistro,presentador)
								VALUES(293,".$fila[0].",".$filaRevisor[0].",34,-1,".$actor[$fila[1]].",1,'".date("Y-m-d")."',NULL,0,0)";
					$x++;
				}
				if($listRegistros!="")
				{
					$query[$x]="update 1010_revisionRevisores set numProyAsignados=numProyAsignados+1 where idRegistro in (".$listRegistros.")";
					$x++;
				}
				$con->ejecutarBloque($query);
			}
			else
			{
				echo "No hay disponibles revisores para categoria ".$fila[1]."<br>";	
			}
			
		}
		echo $nRevisionesRestantes;
		$consulta="commit";
		return $con->ejecutarConsulta($consulta);
	}
	
	function asignarProyectosRevisor()
	{
		global $con;
//			$consulta="SELECT id__293_tablaDinamica,categorias FROM _293_tablaDinamica WHERE idEstado in (2.1,2.2,2.3,2.4,2.5,2.6) and categorias<>7 ORDER BY categorias";	
		$consulta="SELECT id__293_tablaDinamica,categorias FROM _293_tablaDinamica WHERE idEstado =2.81 and categorias=8 ORDER BY categorias";	
		$res=$con->obtenerFilas($consulta);
		$x=0;
		$consulta="begin";
		$con->ejecutarConsulta($consulta);
		$actor=array();
		$actor[1]=118;
		$actor[2]=104;
		$actor[3]=106;
		$actor[4]=108;
		$actor[5]=110;
		$actor[6]=112;
		$actor[8]=143;
		while($fila=mysql_fetch_row($res))
		{
			$idProyecto=$fila[0];
			$categoria=$fila[1];	
			$consulta="select min(numProyAsignados) from 1010_revisionRevisores where tipo_Revisor=1 AND num_Max>numProyAsignados  and idCategoria=".$fila[1];
			$minValor=$con->obtenerValor($consulta);
			if($minValor=="")
				$minValor=0;
			$consulta="select min(numProyAsignados) from 1010_revisionRevisores where tipo_Revisor=2 AND num_Max>numProyAsignados  and idCategoria=".$fila[1];
			$minValor2=$con->obtenerValor($consulta);
			if($minValor2=="")
				$minValor2=0;
			$consulta="SELECT idUsuario FROM 1011_asignacionUsuariosProyectos WHERE idProyecto=".$fila[0];
			$listUsr=$con->obtenerListaValores($consulta);
			if($listUsr=="")
				$listUsr="-1";
			
			$consulta="(SELECT idUsuario,idRegistro,tipo_Revisor FROM 1010_revisionRevisores WHERE tipo_Revisor=1 AND num_Max>numProyAsignados and idUsuario not in (".$listUsr.") and numProyAsignados=".$minValor." AND idCategoria=".$fila[1]." LIMIT 0,1) union
						(SELECT idUsuario,idRegistro,tipo_Revisor FROM 1010_revisionRevisores WHERE tipo_Revisor=2 AND num_Max>numProyAsignados and idUsuario not in (".$listUsr.") and numProyAsignados=".$minValor2." AND idCategoria=".$fila[1]." LIMIT 0,2)";
			$resRevisor=$con->obtenerFilas($consulta);						
			$listRegistros="";
			/*if($con->filasAfectadas<>3)
			{
				echo "No hay suficientes revisores para categoria ".$fila[1];
				return false;
			}*/
			$x=0;
			$query=array();
			while($filaRevisor=mysql_fetch_row($resRevisor))
			{
				if($listRegistros=="")
					$listRegistros=$filaRevisor[1];
				else
					$listRegistros.=",".$filaRevisor[1];
				
				$query[$x]="INSERT INTO 1011_asignacionUsuariosProyectos(idUsuario,idCategoria,idProyecto,tipoRevisor) VALUES(".$filaRevisor[0].",".$fila[1].",".$fila[0].",".$filaRevisor[2].")";
				$x++;	
				$query[$x]="INSERT INTO 955_revisoresProceso(idFormulario,idReferencia,idUsuarioRevisor,idProceso,idFormDictamen,idActorProcesoEtapa,estado,fechaAsignacion,fechaDictamen,versionRegistro,presentador)
							VALUES(293,".$fila[0].",".$filaRevisor[0].",34,-1,".$actor[$fila[1]].",1,'".date("Y-m-d")."',NULL,0,0)";
				$x++;
				
				
			}
			
			if($listRegistros!="")
			{
				$query[$x]="update 1010_revisionRevisores set numProyAsignados=numProyAsignados+1 where idRegistro in (".$listRegistros.")";
				$x++;
			}
			$con->ejecutarBloque($query);
			
		}
		$consulta="commit";
		return $con->ejecutarConsulta($consulta);
		
		//DELETE FROM 1011_asignacionUsuariosProyectos;
		//DELETE FROM 955_revisoresProceso;
		//UPDATE 1010_revisionRevisores SET numProyAsignados=0;
		
	}
	
	function asignarProyectosRevisorV3()
	{
		global $con;
		$consulta="SELECT * FROM 955_revisoresProceso WHERE estado=3;";	
		$resRev=$con->obtenerFilas($consulta);
		$x=0;
		$arrUsr=explode(",","3,285,294,295,296,299,302,421,274,455,402,456");
		$nArrUsr=sizeof($arrUsr);
		$query[$x]="begin";
		$x++;
		$nPosUsr=0;
		$idUsuario="-1";
		while($fila=mysql_fetch_row($resRev))
		{
			$idProyecto=$fila[2];
			$asignado=false;
			while(!$asignado)
			{
				$idUsuario=$arrUsr[$nPosUsr];
				$consulta="select idRevisorProceso from 955_revisoresProceso where idUsuarioRevisor=".$idUsuario." and idReferencia=".$idProyecto;
				$filaProy=$con->obtenerPrimeraFila($consulta);
				if(!$filaProy)
					$asignado=true;
				$nPosUsr++;
				if($nPosUsr>=$nArrUsr)
					$nPosUsr=0;
			}
			$query[$x]="insert into 955_revisoresProceso (idFormulario,idReferencia,idUsuarioRevisor,idProceso,
							idFormDictamen,idActorProcesoEtapa,estado,fechaAsignacion,versionRegistro,presentador)
							VALUES(".$fila[1].",".$fila[2].",".$idUsuario.",".$fila[4].",-1,".$fila[6].",1,'2011-03-19',0,0)";
			
			$x++;							
								
		}
		$query[$x]="commit";
		$x++;
		return $con->ejecutarBloque($query);
	}
	
	function enviarCorreosRevisores()
	{
		global $con;
		$arrUsrProblemas=array();
		$arrUsrOk=array();
		$arrAchivos=array();
		/*$arrAchivos[0][0]="../recursos/GuiaDeEvaluacion.pdf";
		$arrAchivos[0][1]="Guia de evaluacion.pdf";*/
		
	$cuerpoMail="
		<img src=\"http://censida.grupolatis.net/images/bannerCensida.jpg\" width=\"800\" height=\"164\">
<h1>Centro Nacional para La Prevención y el<br>
Control Del VIH/SIDA</h1>
		<b>Estimada/o _nombreRevisor </b><i>(Integrante del Equipo Asesor de Revisión de Proyectos (EARP))<i><br><br>
Antes que nada quiero agradecer su participación en la evaluación de proyectos registrados por diversas organizaciones que acudieron al llamado de la '<b>CONVOCATORIA PÚBLICA PARA EL FORTALECIMIENTO DE LA RESPUESTA EN ACCIONES DE PREVENCIÓN FOCALIZADA DEL VIH IMPLEMENTADAS POR LA 
SOCIEDAD CIVIL 2012'</b>, el motivo de este correo electrónico es para informarle que le han sido asignados nuevos proyecto para evaluar. Para lo cuál deberá ingresar al SMAP (Sistema de Monitoreo de Actividades de Prevención) a través de la siguiente dirección<br>
de Internet <a href=\"http://censida.grupolatis.net\">http://censida.grupolatis.net</a> y registrar los datos solicitados, Usted ya cuenta con una clave de acceso:<br><br>
<b>Usuario:</b> _usuario<br>
<b>Contraseña:</b> _contrasena<br><br>
Agradeciendo nuevamente su participación y apoyo y sin nada más que agregar por el momento, le envio un cordial saludo.<br><br>

<br><br>";

$cuerpoMail="
		<table style='background-color:#B60C0C' border='0' cellspacing='1' width='100%' align='center'>    <tbody>        <tr>            <td width='30%' style='text-align: center; '><img alt='' src='http://ugm.grupolatis.net/images/logodelfin_1.png' /></td>            <td width='70%'></td>        </tr>    </tbody></table><br><br>
		<b>Estimada/o _nombreRevisor: </b><br><br>
		Por medio de la presente reciba un cordial saludo, el motivo de la presente es para informarle que a partir del día 6 al 10 de Junio del presente podrá<br>
		accesar al sistema LATIS para ingresar su disponibilidad de horario para el semestre Agosto´12-Enero´13.<br><br><br>
		Le recuerdo sus datos de acceso a la plataforma:<br><br>
		<b>Usuario:</b> _usuario<br>
		<b>Contraseña:</b> _contrasena<br><br>
 		Sin otro en particular agradezco de antemano su apoyo para respetar los tiempos señalados para este proceso, quedo de usted.<br><br>
 
<b>A T E N T A M E N T E.-</b><br>
Dirección Académica.<br><br>
";			
/*		$cuerpoMail="
<img src=\"http://censida.grupolatis.net/images/bannerCensida.jpg\" width=\"800\" height=\"164\">
<h1>Centro Nacional para La Prevención y el<br>
Control Del VIH/SIDA</h1>


<br><b>Estimada/o _nombreRevisor:</b><br><br>
Por medio de la presente, le comunicamos que próximamente lanzaremos la Convocatoria, la cual requiere un proceso de<br>
evaluación parcial y objetivo, que tome en cuenta la calidad técnica en materia de prevención de VIH de las propuestas.<br>
Para ello requerimos personas comprometidas y con la capacitad de realizar este trabajo.<br> 
Por dichas razones, nos ponemos en contacto con usted puesto que desearíamos volver a contar con su apoyo para evaluar<br>
los proyectos que envían las OSC e IA.<br><br>
En caso de aceptar, le solicitamos nos proporcione los siguientes datos:<br><br>
-	Nombre y Apellido Completo<br>
-	Institución y área donde labora<br>
-	Cargo<br>
-	Correo electrónico<br>
-	Tel: (Oficina,  Casa y Celular)<br>
-	Entidad Federativa donde reside<br>
-	Breve experiencia curricular (5 renglones)<br><br>
Para lo cuál deberá ingresar al SMAP (Sistema de Monitoreo de Actividades de Prevención) a través de la siguiente dirección<br>
de Internet <a href=\"http://censida.grupolatis.net\">http://censida.grupolatis.net</a> y registrar los datos solicitados, Usted ya cuenta con una clave de acceso:<br><br>
<b>Usuario:</b> _usuario<br>
<b>Contraseña:</b> _contrasena<br><br>
Para cualquier duda o sugerencia, le agradeceremos nos las haga llegar por este medio al siguiente correo electrónico:<br>
<b>censida@grupolatis.net</b><br><br>
<img src=\"http://censida.grupolatis.net/images/firmaCensida.png\" width=\"400\" height=\"148\"><br><br>
";	
*/	
		//$consulta="SELECT u.idUsuario,u.Login,u.Nombre,u.Password FROM  807_usuariosVSRoles r,800_usuarios u WHERE u.idUsuario=r.idUsuario AND codigoRol='10_0'";
		//$consulta="SELECT distinct u.idUsuario,u.Login,u.Nombre,u.Password FROM  807_usuariosVSRoles r,800_usuarios u WHERE u.idUsuario=r.idUsuario AND u.idUsuario in (296)";
		
		//$consulta="SELECT DISTINCT u.idUsuario,u.Login,u.Nombre,u.Password FROM 1010_revisionRevisores r,800_usuarios u WHERE u.idUsuario=r.idUsuario and tipo_Revisor=1 ";
		$consulta="SELECT distinct u.idUsuario,u.Login,u.Nombre,u.Password FROM  800_usuarios u WHERE u.idUsuario in 
					(SELECT DISTINCT idUsuario FROM 807_usuariosVSRoles WHERE idRol=5)";
		//$consulta="SELECT distinct u.idUsuario,u.Login,u.Nombre,u.Password FROM  800_usuarios u WHERE u.idUsuario in (1344)";
		
		$res=$con->obtenerFilas($consulta);	
		while($fila=mysql_fetch_row($res))
		{
			$idUsuario=$fila[0];
			$nombre=$fila[2];
			$login=$fila[1];
			$password=$fila[3];
			$consulta="SELECT distinct Mail FROM 805_mails WHERE idUsuario=".$fila[0]." and Mail<>'' and Mail is not null limit 0,1";	
			$mail=$con->obtenerValor($consulta);

			$nCuerpo=str_replace("_nombreRevisor",$nombre,$cuerpoMail);
			$nCuerpo=str_replace("_usuario",$login,$nCuerpo);
			$nCuerpo=str_replace("_contrasena",$password,$nCuerpo);
			$obj=array();
			$obj[0]=$idUsuario;
			$obj[1]=$nombre;
			//$mail="novant1730@hotmail.com";
			$obj[2]=$mail;
			
			if($mail!="")
			{
				if(!enviarMail($mail,"Registro de disponibilidad de horario",$nCuerpo,"proyectos@grupolatis.net","UGM Rectoria Sur",$arrAchivos))			
				{
					
					array_push($arrUsrProblemas,$obj);
				}
				else
				{
					array_push($arrUsrOk,$obj);	
				}
			}
		}
		echo "Usuarios Mal: ".sizeof($arrUsrProblemas)."<br>";
		varDump($arrUsrProblemas);
		echo "Usuarios OK: ".sizeof($arrUsrOk)." <br>";
		varDump($arrUsrOk);

	}//342,61
	
	function enviarCorreosAvisoOSC()
	{
		global $con;
		$arrAchivos=array();
		$arrAchivos[0][0]="../recursos/PComunicativos.pdf";
		$arrAchivos[0][1]="Guia producto comunicativos.pdf";
		
		$arrUsrProblemas=array();
		$arrUsrOk=array();
		
		$cuerpoMail="
		<img src=\"http://censida.grupolatis.net/images/bannerCensida.jpg\" width=\"800\" height=\"164\">
<h1>Centro Nacional para La Prevención y el<br>
Control Del VIH/SIDA</h1><br>
		Por este medio, atentamente les es enviada la guía para someter a revisión los productos comunicativos para difusión, cabe mencionar que aplica solamente a los proyectos que definieron que distribuirían este tipo de productos.<br><br>

<b>Atentamente</b><br><br>

CENSIDA

<br>
<br>
";


		
	
		$consulta="SELECT i.email,o.unidad FROM 817_organigrama o,247_instituciones i  WHERE codigoUnidad IN 
(SELECT codigoInstitucion FROM _370_tablaDinamica WHERE marcaAutorizado=1) AND i.idOrganigrama=o.idOrganigrama
ORDER BY unidad limit 0,1";
		$res=$con->obtenerFilas($consulta);	
		while($fila=mysql_fetch_row($res))
		{
			$idUsuario=$fila[1];
			$nombre=$fila[1];
			$mail=$fila[0];
			$obj=array();
			$obj[0]=$idUsuario;
			$obj[1]=$nombre;
			//$mail="sa.barronlimon@gmail.com";
			$obj[2]=$mail;
			if(!enviarMail($mail,"Guía para registro productos comunicativos en SMAP",$cuerpoMail,"proyectos@grupolatis.net","CENSIDA",$arrAchivos))			
			{
				
				array_push($arrUsrProblemas,$obj);
			}
			else
			{
				array_push($arrUsrOk,$obj);	
			}
		}
		echo "Usuarios Mal: ".sizeof($arrUsrProblemas)."<br>";
		varDump($arrUsrProblemas);
		echo "Usuarios OK: ".sizeof($arrUsrOk)." <br>";
		varDump($arrUsrOk);

	}//342,61
	
	
	
	function enviarCorreosAvisoOSCV2Masiva()
	{
		global $con;
		$prueba=false;
		$arrAchivos=array();

		/*$arrAchivos[0][0]="../recursos/GuiaDocumentosRequeridos2014.pdf";
		$arrAchivos[0][1]="GuiaDocumentosRequeridos2014.pdf";
		$arrAchivos[1][0]="../recursos/Manual2014.pdf";
		$arrAchivos[1][1]="Manual2014.pdf";
		$arrAchivos[2][0]="../recursos/catalogoIndicadores2014.pdf";
		$arrAchivos[2][1]="CatalogoIndicadores2014.pdf";*/
		
		
		
		$arrUsrProblemas=array();
		$arrUsrOk=array();
		
			
	
	$cuerpoMail="
		<table width=\"800\"><tr><td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/logoSalud.gif\"></td>".
		"<td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/censidaLogo.gif\"></td></tr></table>
<br><br><h1>Centro Nacional para La Prevención y el<br>
Control Del VIH/SIDA</h1><br>
<br><br>

Estimadas Organizaciones de la Sociedad Civil e Instituciones Académicas:<br><br>

Por este medio se les informa que se ha ampliado el plazo (por 2 horas.) para el registro y sometimiento de proyectos estableciéndose como hora límite las 15:00 hrs. del día de hoy (5 de marzo de 2013), una vez cumplido dicho horario no podrán registrar ni someter a evaluación ningún proyecto, se les recuerda que sólo participarán en la \"CONVOCATORIA PÚBLICA PARA LA IMPLEMENTACION DE ESTRATEGIAS DE PREVENCIÓN COMBINADA para el fortalecimiento de la respuesta ante el VIH y el SIDA 2013\" aquellos proyectos que hayan sido enviados a evaluación
<br><br><br>

<img src=\"http://censida.grupolatis.net/images/firmaCensida.png\" width=\"400\" height=\"148\"><br><br>		
";		

	$cuerpoMail="<table width=\"800\"><tr><td width=\'250\' align=\"center\"></td>".
			"<td width=\'250\' align=\"center\"></td></tr></table>
				<br><br>
				A todas las OSC / IA que utilizan el SMAP, a través de la presente se les informa que los servidores de la plataforma Web sufrieron un ataque de tipo DENIED OF SERVICE (DOS)<br> 
				por sus siglas en inglés (Denegación de servicio), el cuál consiste en el envío masivo de peticiones al servidor (miles de peticiones por segundo), saturando el servicio del mismo y alentando<br>
				su funcionamiento; el ataque comenzó a partir de las 12:05 horas, lo anterior se informó a las autoridades de CENSIDA quienes tomaron la decisión de ampliar el cierre de registro y<br>
				sometimiento de proyectos hasta las 15:00 horas del día de hoy.
				<br><br><br>
				
				
				";	


	$cuerpoMail="
		<table width=\"800\"><tr><td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/logoSalud.gif\"></td>".
		"<td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/censidaLogo.gif\"></td></tr></table>
<br><br><h1>Centro Nacional para La Prevención y el<br>
Control Del VIH/SIDA</h1><br>
<br><br>
<b>Comunicado</b><br><br> 
<span style=\"font-size:12px\">

El Centro Nacional para la Prevención y Control del VIH/sida (CENSIDA) se congratula en dar la bienvenida a las 47 nuevas organizaciones que se registraron para participar en la Convocatoria 
Pública para la implementación de estrategias de prevención combinada para el Fortalecimiento de la respuesta ante el VIH y el Sida 2013.<br><br>
Consideramos que el aumento de la participación ciudadana mediante la generación de nuevas Organizaciones de la Sociedad Civil (OSCs) permitirá dar nuevos bríos a la respuesta  contra el VIH/sida e 
ITS y la posibilidad de alcanzar las nuevas metas, que la epidemia contra la cual luchamos día a día, plantea.<br>
</span>
";	
$cuerpoMail="
		<table width=\"800\"><tr><td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/logoSalud.gif\"></td>".
		"<td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/censidaLogo.gif\"></td></tr></table>
<br><br><h1>Centro Nacional para La Prevención y el<br>
Control Del VIH/SIDA</h1><br>
<br><br>

<span style=\"font-size:12px\">
Estimadas Organizaciones de la Sociedad Civil e Instituciones Académicas:<br><br>
Con relación a la invitación a participar en la reunión nacional con representantes de las organizaciones de la sociedad civil los días 29 y 30 de abril del año en curso, en el área metropolitana del Estado de México, me permito informarles lo siguiente:<br><br>

El registro se realizará vía correo electrónico <b>direcciondeprevencion@gmail.com</b>,  a partir de las <b>17 horas del 11 de abril y hasta las 13 horas del 15 de abril o al registrarse el representante número tres por entidad federativa</b> y en el caso del <b>Distrito Federal y área metropolitana hasta el representante número 50.</b><br><br>
Vía correo electrónico se le enviará la confirmación correspondiente, así como el folio de su registro.<br><br>
Se tomará en cuenta la hora y fecha de recepción del registro.<br><br>
Para cualquier duda y/o aclaración se podrán poner en contacto con Miguel Ángel Domínguez Camargo en el teléfono 01 (55) 9150 6020 en un horario de 10:00 a 18 hrs.<br><br>
Se anexa formato de registro.<br><br>
 
<b>No omito informarles que solo se podrá registrar una persona por organización y que el registro en línea a través del SMAP queda sin efecto.</b>


<br><br><br>
<br>
</span>
";	

	$cuerpoMail="
		<table width=\"800\"><tr><td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/logoSalud.gif\"></td>".
		"<td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/censidaLogo.gif\"></td></tr></table>
<br><br><h1>Centro Nacional para La Prevención y el<br>
Control Del VIH/SIDA</h1><br>
<br><br>

<span style=\"font-size:12px\">
Estimadas OSC e IA:<br><br>
Por este medio se les informa que ya se encuentran disponibles en el SMAP (<a href=\"http://censida.grupolatis.net\">http://censida.grupolatis.net</a>) los siguientes materiales para su descarga:<br><br>
Uso del condón (Para HG)<br>
Uso del condón (Para HSH)<br>
Uso del condón (Para HTS)<br>
Uso del condón (Para MTS)<br>
Uso del condón (Para PB)<br>
Uso del condón (Para MTS)<br>
Uso del condón (Para Transgénero)<br>
Derechos Humanos (Para HG)<br>
Derechos Humanos (Para HSH)<br>
Derechos Humanos (Para PB)<br>
Derechos Humanos (Trabajo Sexual)<br>
Infecciones de Transmisión Sexual (Para HG)<br>
Infecciones de Transmisión Sexual (Para HSH)<br>
Infecciones de Transmisión Sexual (Para HTS)<br>
Infecciones de Transmisión Sexual (Para PB)<br>
Prueba de detección del VIH (Para HG)<br>
Prueba de detección del VIH (Para HSH)<br>
Prueba de detección del VIH (Para HTS)<br>
Prueba de detección del VIH (Para PB)<br>
Prueba de detección del VIH (Transgénero)<br>
Salud Anal (Para HG)<br>
Vida plena, Vivir con VIH<br>
Virus de Inmunodeficiencia Humana<br>
Trabajo Sexual y violencia<br><br>

Cabe mencionar que éstos se encuentran publicados en el menú Materiales de la página principal del sitio.<br><br>

Por otra parte se les extendiende una invitación para que sigan la reunión del programa especial de VIH sida e ITS PEVSIT a través de los siguiente medios:<br><br>

<b>Pagina WEB:</b>  <a href=\"http://www.pesida.net\">http://www.pesida.net</a><br>

<b>Twitter:</b> @pesidamex<br>

<b>Facebook:</b>  <a href=\"http://www.facebook.com/pesidamex\">http://www.facebook.com/pesidamex</a>


<br><br><br>
<br>
</span>
";	

	$cuerpoMail="
		<table width=\"800\"><tr><td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/logoSalud.gif\"></td>".
		"<td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/censidaLogo.gif\"></td></tr></table>
<br><br><h1>Centro Nacional para La Prevención y el<br>
Control Del VIH/SIDA</h1><br>
<br><br>

<span style=\"font-size:12px\">
Estimadas OSC e IA:<br><br>
Por este medio se les informa que ya se encuentran disponibles en el SMAP  el Manual de Identidad de Telsida el
cual identificará al servicio del mismo, para descargarlo, deberá seleccionar la opción \"Manual de Identidad TELSIDA\" del menú
 \"Guías y manuales\" en la página principal del <a href=\"http://censida.grupolatis.net\">SMAP</a>.
<br><br><br>
<br>
</span>
";	
	$cuerpoMail="
		<table width=\"800\"><tr><td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/logoSalud.gif\"></td>".
		"<td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/censidaLogo.gif\"></td></tr></table>
<br><br><h1>Centro Nacional para La Prevención y el<br>
Control Del VIH/SIDA</h1><br>
<br><br>

<span style=\"font-size:12px\">
<b>COMUNICADO A LAS ORGANIZACIONES DE LA SOCIEDAD CIVIL</b><br><br>


Estimadas todas y todos.<br><br>

Adjunto el lema que impulsaremos para la conmemoración del día mundial de la respuesta ante el VIH y el sida. Les suplico lo distribuyan y den a conocer con sus respectivos equipos de trabajo.<br><br>

Este lema estará vigente hasta noviembre de 2014.<br><br>
Cabe mencionar que las imagenes adjuntas también pueden ser descargadas desde el SMAP(<a href=\"http://censida.grupolatis.net\">http://censida.grupolatis.net</a>) en el menu \"Día de la Respuesta ante el VIH\" en la página principal.<br><br>

Muchas gracias.
<br><br><br>
<br>
</span>
";	


	$html='
			<p>Fecha: 7 de noviembre&nbsp;</p>

<p>Hora: 9:00 hrs PST.</p>

<p>Info</p>

<p>&nbsp;</p>

<table border="1" cellpadding="0" cellspacing="0" style="width:600px">
	<tbody>
		<tr>
			<td>
			<table border="0" cellpadding="0" cellspacing="0" style="width:6.25in">
				<tbody>
					<tr>
						<td>
						<table border="0" cellpadding="0" cellspacing="0" style="width:600px">
							<tbody>
								<tr>
									<td>
									<p><strong>La seguridad digital para defensores de los DDHH y proveedores de servicios a HSH: como proteger a si mismo y a sus clientes en los contextos hostiles</strong></p>

									<p><strong>MSMGF</strong></p>

									<p><strong>SERIE SEMINARIOS WEB</strong></p>

									<p>Cuando: Friday, 7 Noviembre de 2013</p>

									<p><a href="http://msmgf.us1.list-manage.com/track/click?u=1efcb45b2d3a4abde06876054&amp;id=84a3287a90&amp;e=9d44aaf6b2" target="_blank">Hora: 9:00 am PST</a></p>

									<p>Donde: web&nbsp;</p>

									<p>&nbsp;</p>

									<p>El MSMGF se ha asociado con el Programa de Derechos Humanos de Benetech para producir un seminario sobre seguridad inform&aacute;tica para los defensores de los DDHH y los proveedores de servicios que trabajan en contextos hostiles. El seminario ofrecer&aacute; una visi&oacute;n general de los diversos riesgos digitales que enfrentan los defensores de los DDHH as&iacute; como proveedores de servicios a HSH, as&iacute; como las herramientas y los m&eacute;todos gratuitos que se pueden utilizar para proteger sus datos y &ndash;por extensi&oacute;n&mdash;a s&iacute; mismo y a su comunidad.</p>

									<p>&nbsp;</p>

									<p>La presentaci&oacute;n cubrir&aacute; los siguientes:</p>

									<ul>
										<li>Conceptos importantes de seguridad digital</li>
										<li>Principios para evaluar la seguridad de sus datos y comunicaciones digitales</li>
										<li>Explicaciones de lo que es la encriptaci&oacute;n y c&oacute;mo te puede ayudar&nbsp;</li>
										<li>Demostraci&oacute;n de varias herramientas tanto de confianza como gratuitas para mejorar la seguridad digital</li>
										<li>Recomendaciones para proteger la unidad de disco duro, correos electr&oacute;nicos y otra actividad en el Internet, mensajes de texto y llamadas telef&oacute;nicas</li>
									</ul>

									<p>El seminario proveer&aacute; la oportunidad para que la audiencia participe via web chat y tendra una duraci&oacute;n de 45 minutos seguido por un periodo de preguntas y respuestas.</p>

									<p>&nbsp;</p>

									<p>Para ver la presentaci&oacute;n visual es necesario una conexi&oacute;n a internet. Para escuchar el audio, es necesario que se conecte a trav&eacute;s del tel&eacute;fono.</p>

									<p>&nbsp;</p>

									<p>N&uacute;meros Internacionales: si ninguno de los n&uacute;meros proporcionados en el listado incluido &nbsp;enel registro son accesibles para usted, por favor, introduzca su n&uacute;mero de tel&eacute;fono durante el proceso de registro a continuaci&oacute;n. Usted recibir&aacute; una llamada telef&oacute;nica poco antes del inicio de la presentaci&oacute;n.<br />
									<br />
									Usted debe de registrarce para poder participar de este seminario. Para registrarce, favor connectarse a la siguiente pagina web:&nbsp;<a href="http://msmgf.us1.list-manage.com/track/click?u=1efcb45b2d3a4abde06876054&amp;id=264e65747c&amp;e=9d44aaf6b2" target="_blank">https://cc.readytalk.com/cc/s/registrations/new?cid=hw02xv1gefef</a>.</p>

									<p>&nbsp;</p>

									<p>Participaci&oacute;n en el seminario es gratuita, de tener alguna pregunta envie correo electronico a :<a href="mailto:calicea@msmgf.org" target="_blank">calicea@msmgf.org</a>.</p>
									</td>
								</tr>
							</tbody>
						</table>
						</td>
					</tr>
				</tbody>
			</table>
			</td>
		</tr>
		<tr>
			<td>
			<table border="0" cellpadding="0" cellspacing="0" style="width:6.25in">
				<tbody>
					<tr>
						<td>
						<table border="0" cellpadding="0" cellspacing="0" style="width:580px">
							<tbody>
								<tr>
									<td colspan="2" style="background-color:rgb(250, 250, 250)">
									<p><a href="http://us1.forward-to-friend2.com/forward?u=1efcb45b2d3a4abde06876054&amp;id=746b2d3e1f&amp;e=9d44aaf6b2" target="_blank">forward to a friend</a>&nbsp;</p>
									</td>
								</tr>
								<tr>
									<td style="width:262.5pt">
									<p><em>Copyright &copy; 2013 The Global Forum on MSM &amp; HIV, All rights reserved.</em>&nbsp;<br />
									This is you.&nbsp;<br />
									<strong>Our mailing address is:</strong></p>

									<p>The Global Forum on MSM &amp; HIV</p>

									<p>436 14th Street</p>

									<p>Suite 1500</p>

									<p>Oakland,&nbsp;CA&nbsp;94612</p>

									<p><br />
									<a href="http://msmgf.us1.list-manage.com/vcard?u=1efcb45b2d3a4abde06876054&amp;id=cb38be213c" target="_blank">Add us to your address book</a></p>
									</td>
									<td style="width:142.5pt">&nbsp;</td>
								</tr>
							</tbody>
						</table>
						</td>
					</tr>
				</tbody>
			</table>
			</td>
		</tr>
	</tbody>
</table>

			';


	$cuerpoMail="
		<table width=\"800\"><tr><td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/logoSalud.gif\"></td>".
		"<td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/censidaLogo.gif\"></td></tr></table>
<br><br><h1>Centro Nacional para La Prevención y el<br>
Control Del VIH/SIDA</h1><br>
<br><br>

<span style=\"font-size:12px\">
<b>Estimadas OSC e IA</b><br><br>
Por este medio se envía la invitación de la MSMGF sobre las series de seminarios \"La seguridad digital para defensores de los DDHH y proveedores de servicios a HSH: como proteger a si mismo y a sus clientes en los contextos hostiles\" 
<br><br>
".$html."
<br><br><br>
<br>
</span>
";	


$cuerpoMail="
		<table width=\"800\"><tr><td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/logoSalud.gif\"></td>".
		"<td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/censidaLogo.gif\"></td></tr></table>
<br><br><h1>Centro Nacional para La Prevención y el<br>
Control Del VIH/SIDA</h1><br>
<br><br>

<span style=\"font-size:12px\">
Estimadas OSC e IA:<br><br>
Por este medio se les informa que ya se encuentran disponibles en el SMAP (<a href=\"http://censida.grupolatis.net\">http://censida.grupolatis.net</a>) los siguientes materiales para su descarga:<br><br>

Infecciones de Transmisión Sexual (Náhuatl)<br>
Prueba de detección del VIH (Náhuatl)<br>
Reducción del daño<br>
Uso del condón (Náhuatl)<br>
Vida plena, Vivir con VIH (Náhuatl)<br>
VIH y Comorbilidad<br>
VIH y Embarazo<br>
Virus de Inmunodeficiencia Humana (Náhuatl)<br><br><br>

Cabe mencionar que éstos se encuentran publicados en el menú Materiales de la página principal del sitio.<br><br>

<br><br><br>
<br>
</span>
";	

$cuerpoMail="
<span style=\"font-size:12px\">
En 1989 tuve oportunidad de conocer a AVE de México; desde entonces pude iniciar una constante cadena de aprendizajes y de oportunidades de colaborar en la respuesta ante el VIH, el sida así como en temas de sexualidad humana.<br><br>
He vivido, durante veinticinco años, muy diversos ciclos tanto a nivel local, regional como internacionalmente.<br><br>
En 2009 fui invitado para fungir como Director de Prevención y Participación Social del Censida; pude convertirme en servidor público de carrera y me desempeñé en dicho cargo hasta el pasado 31 de diciembre. <br><br>
Este ciclo, que recién concluye, me dio la oportunidad de conocer otra faceta de la respuesta ante la epidemia; desde mi experiencia y las posibilidades que ofrece el servicio público, aporté mi mejor esfuerzo para cumplir con los retos y encargos que el área requirió; por ello quiero agradecer a todas las instituciones, organizaciones de la sociedad civil y personas  a quienes tuve oportunidad de servir, a los programas estatales, a mis compañeras y compañeros de trabajo, de las distintas áreas del Censida, al personal de base, y muy en particular al equipo de la Dirección de Prevención y de Participación Social, por los enormes aprendizajes que obtuve y la oportunidad de conocer personas, sin duda extraordinarias.<br><br>
Reitero, a todas y todos, mi profundo agradecimiento y espero, al iniciar un nuevo ciclo, tener nuevas y enriquecedoras oportunidades de aportar para transformar,  en los objetivos que nos son comunes.<br><br><br><br>
Reciban mis felicitaciones para éste 2014.<br><br><br>
Un abrazo,<br><br><br>
C.D. Carlos García de León Moreno
<br><br><br>
<br>
</span>
";	

$cuerpoMail='<p>Hasta el 22 de enero est&aacute; abierta la convocatoria para que organizaciones y redes de la sociedad civil, institutos de investigaci&oacute;n, autoridades gubernamentales y equipos de pa&iacute;s de la ONU apliquen al Fondo Fiduciario de las Naciones Unidas en Apoyo de las Acciones para Eliminar la Violencia contra la Mujer y presenten propuestas para ser financiadas por el Fondo.</p>

<p>&nbsp;</p>

<p>El&nbsp;<strong>Fondo Fiduciario de las Naciones Unidas en Apoyo de las Acciones para Eliminar la Violencia contra la Mujer</strong>&nbsp;(&ldquo;El Fondo Fiduciario de la ONU&rdquo;)&nbsp;es un mecanismo global l&iacute;der de concesi&oacute;n de subsidios dedicado exclusivamente a enfrentar la violencia contra las mujeres y las ni&ntilde;as en todas sus formas. El Fondo presta apoyo a iniciativas efectivas que demuestran que la violencia contra las mujeres y las ni&ntilde;as puede ser abordada y reducida de manera sistem&aacute;tica y, con persistencia, eliminada.</p>

<p>&nbsp;</p>

<p>Actualmente acepta aplicaciones dentro de su ciclo n&uacute;mero 18 de selecci&oacute;n de proyectos de organizaciones y redes de la sociedad civil, institutos de investigaci&oacute;n, autoridades gubernamentales y equipos de pa&iacute;s de la ONU (en colaboraci&oacute;n con gobiernos y organizaciones de la sociedad civil).</p>

<p>&nbsp;</p>

<p>Los postulantes est&aacute;n invitados a entregar propuestas de proyecto de dos o tres a&ntilde;os, de un presupuesto m&iacute;nimo de US$50,000 hasta un presupuesto de US$1,000.000. Las propuestas deber&aacute;n ser presentadas en l&iacute;nea en forma de una breve Nota Conceptual.</p>

<p>&nbsp;</p>

<p><strong>&iquest;Cu&aacute;ndo y d&oacute;nde presentarse?</strong></p>

<p>&nbsp;</p>

<p>El plazo para la presentaci&oacute;n de la Nota Conceptual&nbsp;<strong>finalizar&aacute; el 22 de enero de 2014 a las 23.59 (hora de Nueva York</strong>). No se tendr&aacute;n en&nbsp; consideraci&oacute;n&nbsp; las Notas Conceptuales recibidas despu&eacute;s de este plazo. Todas&nbsp; las solicitudes&nbsp; deber&aacute;n&nbsp; presentarse&nbsp; en&nbsp; l&iacute;nea.&nbsp; El&nbsp; Fondo&nbsp; Fiduciario&nbsp; de&nbsp; la&nbsp; ONU&nbsp; no&nbsp; aceptar&aacute; solicitudes presentadas a trav&eacute;s de correo electr&oacute;nico, correo ordinario o fax.&nbsp;</p>

<p>&nbsp;</p>

<p>La solicitud en l&iacute;nea est&aacute; disponible desde el 25 de noviembre de 2013 hasta el 22 de enero de 2014 en:&nbsp;<a href="http://grants.unwomen.org/" target="_blank">http://grants.unwomen.org</a></p>

<p>&nbsp;</p>

<p>En caso de tener problemas con la solicitud en l&iacute;nea, p&oacute;ngase en contacto con la Secretar&iacute;a del Fondo Fiduciario de la ONU (Nueva York, Estados Unidos) en el correo electr&oacute;nico&nbsp;<a href="http://unwomen.org/" target="_blank">untf-evaw@unwomen.org</a></p>

<p>Los postulantes ser&aacute;n informados de toda novedad en el proceso de solicitud v&iacute;a correo electr&oacute;nico.</p>

<p>&nbsp;</p>

<p>&nbsp;&nbsp;</p>

<p>Gracias por su inter&eacute;s y difusi&oacute;n.</p>

<p>Saludos,</p>

<p>&nbsp;</p>

<p>&nbsp;</p>

<p>&nbsp;</p>

<p>&nbsp;</p>

<table border="0" cellpadding="0" style="width:453.4pt">
	<tbody>
		<tr>
			<td>
			
			</td>
			<td>
			<p><strong>Mariana Winocur</strong></p>

			<p>Responsable de Comunicaci&oacute;n</p>

			<p><a href="http://www.unwomen.org/es/" target="_blank">ONU Mujeres</a></p>

			<p>M&eacute;xico<br />
			Montes Urales 440, Colonia Lomas de Chapultepec<br />
			C.P. 11000</p>

			<p>Tel: 4000-9845</p>

			<p>E-mail:&nbsp;<a href="mailto:nancy.almaraz@unwomen.org" target="_blank">mariana.winocur@unwomen.org</a></p>
			</td>
		</tr>
	</tbody>
</table>

<p><br />
&nbsp;</p>';


	$cuerpoMail="
					<table width=\"800\"><tr><td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/logoSalud.gif\"></td>
								<td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/censidaLogo.gif\"></td></tr></table>
								<br><br><h1>Centro Nacional para La Prevención y el<br>
								Control Del VIH/SIDA</h1><br>
								<br><br>	
								<span style='font-size:12px'>
								<br><b>Estimada OSC/IA:</b><br><br>	
									Por este medio se envía la \"Convocatoria pública dirigida a organizaciones de sociedad civil e instituciones académicas con experiencia y trabajo
comprobable en VIH, sida e infecciones de transmisión sexual para la implementación de estrategias de prevención que
fortalezcan la respuesta nacional ante el VIH y el sida 2014.\" para su difusión<br>
								</span>
								<br><br>
					";

	
	$cuerpoMail="
					<table width=\"800\"><tr><td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/logoSalud.gif\"></td>
								<td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/censidaLogo.gif\"></td></tr></table>
								<br><br><h1>Centro Nacional para La Prevención y el<br>
								Control Del VIH/SIDA</h1><br>
								<br><br>	
								<span style='font-size:12px'>
								<br><b>Estimadas OSC:</b><br><br>	
									Por este medio se envía información actualizada referente a la convocatoria para \"La actualización de información de país para el Reporte de progreso en la respuesta global al SIDA 2014 \" para su difusión<br>
								</span>
								<br><br>
					";

	$cuerpoMail="
					<table width=\"800\"><tr><td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/logoSalud.gif\"></td>
								<td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/censidaLogo.gif\"></td></tr></table>
								<br><br><h1>Centro Nacional para La Prevención y el<br>
								Control Del VIH/SIDA</h1><br>
								<br><br>	
								<span style='font-size:12px'>
								<br><b>Estimadas OSC/IA:</b><br><br>	
									Por este medio se envian los siguientes documentos referentes a la <b>Convocatoria pública dirigida a organizaciones de sociedad civil e instituciones académicas con experiencia y trabajo
comprobable en VIH, sida e infecciones de transmisión sexual para la implementación de estrategias de prevención que
fortalezcan la respuesta nacional ante el VIH y el sida 2014</b>:<br><br>
									- Catálogo de indicadores<br>
									- Guía de documentos requeridos para registro de proyectos<br>
									- Manual de registro de proyectos (Actualización)
									<br><br>
									Es recomendable su lectura y difusión, dichos documentos se encuentran publicados también en la sección de Guías y manuales en el portal del SMAP<br>
								</span>
								<br><br>
					";

		$cuerpoMail="
		<table width=\"800\"><tr><td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/logoSalud.gif\"></td>
								<td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/censidaLogo.gif\"></td></tr></table>
								<br><br><h1>Centro Nacional para La Prevención y el<br>
								Control Del VIH/SIDA</h1><br>
								<br><br>	

Estimadas Organizaciones de la Sociedad Civil e Instituciones Académicas:<br><br>

Por este medio se les recuerda que el plazo para el registro y envío de proyectos a participación pertenecientes a la <b>\"Convocatoria pública dirigida a organizaciones de sociedad civil e instituciones académicas con experiencia y trabajo
comprobable en VIH, sida e infecciones de transmisión sexual para la implementación de estrategias de prevención que
fortalezcan la respuesta nacional ante el VIH y el sida 2014\"</b> finaliza el día 07 de marzo de 2014 a las 15:00 hrs.<br><br><br>";		
		
		
		$cuerpoMail="
		<table width=\"800\"><tr><td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/logoSalud.gif\"></td>
								<td width=\'250\' align=\"center\"><img width=\"75%\" height=\"75%\"  src=\"http://censida.grupolatis.net/images/censida/FIRMA_ELECTRONICAaaa.png\"></td></tr></table>
								<br><br><h1>Centro Nacional para La Prevención y el<br>
								Control Del VIH/SIDA</h1><br>
								<br><br>	

Estimadas Organizaciones de la Sociedad Civil e Instituciones Académicas:<br><br>
Por este medio se les notifica que NO existe Prórrroga en la convocatoria 2014, lo anterior como respuesta a un correo envíado previamente donde se notificaba dicho evento, se les pide de la manera más atenta ignoren el anterior correo.
<br><br><br>";		
		
		
		$arrCopiaOculta=NULL;
		$comp="";
		$arrDestinatarios=array();
		if(!$prueba)
		{
			/*$arrCopiaOculta[0][0]="carlosnic@gmail.com";
			$arrCopiaOculta[0][1]="";
			$arrCopiaOculta[2][0]="marco.lasso@grupolatis.net";
			$arrCopiaOculta[2][1]="";
			$arrCopiaOculta[2][0]="palomaruizgomez@gmail.com";
			$arrCopiaOculta[2][1]="";
			$arrCopiaOculta[3][0]="proyectos@grupolatis.net";
			$arrCopiaOculta[3][1]="";
			foreach($arrCopiaOculta as $o)
			{
				$obj=array();
				$obj[0]=1;
				$obj[1]="";
				$obj[2]=$o[0];
				array_push($arrDestinatarios,$obj);
			}*/
		}
		else
		{
			/*$arrCopiaOculta[0][0]="marco.lasso@grupolatis.net";
			$arrCopiaOculta[0][1]="";
			
			foreach($arrCopiaOculta as $o)
			{
				$obj=array();
				
				$obj[0]=1;
				$obj[1]="";
				$obj[2]=$o[0];
				array_push($arrDestinatarios,$obj);
			}*/
			$comp=" limit 0,1";
		}
		
		/*$nCuerpo=str_replace("_nombreRevisor",$nombre,$cuerpoMail);
		$nCuerpo=str_replace("_usuario",$login,$nCuerpo);
		$nCuerpo=str_replace("_contrasena",$password,$nCuerpo);*/
		//$consulta="SELECT i.email,o.unidad FROM 817_organigrama o,247_instituciones i  WHERE codigoUnidad IN 
//(SELECT codigoInstitucion FROM _370_tablaDinamica WHERE idEstado>1) AND i.idOrganigrama=o.idOrganigrama
//ORDER BY unidad".$comp;
		//$consulta="SELECT i.email,o.unidad,o.idOrganigrama,o.codigoUnidad FROM 817_organigrama o,247_instituciones i WHERE i.idOrganigrama=o.idOrganigrama AND email <>'' AND email IS NOT null ".$comp;
		$consulta="SELECT Mail,m.idUsuario FROM 805_mails m,800_usuarios u WHERE u.idUsuario=m.idUsuario and u.cuentaActiva=1 and m.idUsuario IN (
					SELECT idUsuario FROM 807_usuariosVSRoles WHERE idRol IN (37,62)) ".$comp;
		$res=$con->obtenerFilas($consulta);	
		while($fila=mysql_fetch_row($res))
		{
			$mail=trim($fila[0]);
			$obj=array();
			if($prueba)
				$mail="novant1730@hotmail.com";
			$obj[0]=$fila[1];
			$obj[1]="";
			$obj[2]=$mail;
			$obj[3]=obtenerNombreUsuario($fila[1]);
			array_push($arrDestinatarios,$obj);
			
		}
		
		foreach($arrDestinatarios as $d)
		{
			$consulta="SELECT Login,PASSWORD FROM 800_usuarios WHERE idUsuario=".$d[0];
			$fUsr=$con->obtenerPrimeraFila($consulta);
			$cuerpoMailFinal=str_replace("_usuario",$fUsr[0],$cuerpoMail);
			$cuerpoMailFinal=str_replace("_contrasena",$fUsr[1],$cuerpoMailFinal);
			if(!enviarMail($d[2],"CENSIDA - ERROR PRORROGA CONVOCATORIA 2014",$cuerpoMailFinal,"soporteSMAP@grupolatis.net","",$arrAchivos,NULL))			
			{
				
				array_push($arrUsrProblemas,$d);
			}
			else
			{
				array_push($arrUsrOk,$d);	
			}
		}
		//echo enviarMailMultiplesDestinatarios($arrDestinatarios,"Aviso CENSIDA",$cuerpoMail,"proyectos@grupolatis.net","Carlos Garcia de Leon",$arrAchivos,$arrCopiaOculta);
		echo "Usuarios Mal: ".sizeof($arrUsrProblemas)."<br>";
		varDump($arrUsrProblemas);
		echo "Usuarios OK: ".sizeof($arrUsrOk)." <br>";
		varDump($arrUsrOk);

	}//342,61
	
	function enviarCorreosProyectosGanadores()
	{
		global $con;
		$arrUsrProblemas=array();
		$arrUsrOk=array();
		$arrAchivos=array();
		/*$arrAchivos[0][0]="../recursos/CartaOSC.doc";
		$arrAchivos[0][1]="CartaOSC.doc";
		$arrAchivos[1][0]="../recursos/GuiaOSC.pdf";
		$arrAchivos[1][1]="GuiaOSC.pdf";*/
		$cuerpoMail="Estimadas y Estimados colegas de organizaciones de la sociedad civil con trabajo en VIH:<br><br>
Les envío un saludo y el mejor deseo de felicidad, bienestar y salud en estas épocas de fin de año.<br>
Espero que 2011 les haya sido fructífero y satisfactorio en el ámbito profesional y personal, así como para las comunidades para las que trabajamos.<br><br>

Aprovecho la oportunidad para desearles el mayor de los éxitos en el año entrante y asegurándoles que en CENSIDA haremos lo posible para intensificar las acciones de colaboración con las organizaciones de la sociedad civil con trabajo en el tema, en beneficio de las personas y comunidades para las que trabajamos, objetivo común entre nosotros.<br><br>  

Hemos tenido ya reuniones con el subsecretario, Dr. Pablo Kuri, tan pronto regresé de la participación de la junta de coordinación del ONUSIDA, y ahora les confirmo que haremos lo que esté a nuestro alcance para mejorar la respuesta nacional al VIH en nuestro país, respuesta de la que la sociedad civil es parte fundamental.<br><br>

Les deseo un exitoso 2012 y que obtengan la felicidad y bienestar que tanto merecen ustedes y sus seres queridos.<br><br>

Sinceramente,<bR><bR>

Jose Antonio Izazola <br><br>


Centro Nacional para la Prevención y el Control del VIH/SIDA<br>
CENSIDA
";
		
		//$consulta="SELECT u.idUsuario,u.Login,u.Nombre,u.Password FROM  807_usuariosVSRoles r,800_usuarios u WHERE u.idUsuario=r.idUsuario AND codigoRol='10_0'";
		//$consulta="SELECT distinct u.idUsuario,u.Login,u.Nombre,u.Password FROM  807_usuariosVSRoles r,800_usuarios u WHERE u.idUsuario=r.idUsuario AND u.idUsuario in (142,144)";
		$consulta="SELECT distinct u.idUsuario,u.Login,u.Nombre,u.Password FROM  800_usuarios u WHERE  u.idUsuario in (SELECT DISTINCT responsable FROM _293_tablaDinamica WHERE id__293_tablaDinamica IN
					(SELECT idReferencia FROM 1012_dictamenesProyectos WHERE tipoDictamen=0 AND dictamen=1 AND idReferencia<>220))";
		//$consulta="SELECT distinct u.idUsuario,u.Login,u.Nombre,u.Password FROM  800_usuarios u WHERE  u.idUsuario in (240)";					
		$res=$con->obtenerFilas($consulta);	
		//$fila=mysql_fetch_row($res);
		while($fila=mysql_fetch_row($res))
		{
			
			$idUsuario=$fila[0];
			$nombre=$fila[2];
			$login=$fila[1];
			$password=$fila[3];
			$consulta="SELECT distinct Mail FROM 805_mails WHERE idUsuario=".$fila[0];	
			$mail=$con->obtenerValor($consulta);
			$mail="novant1730@hotmail.com";
			//$nCuerpo=str_replace("_nombreRevisor",$nombre,$cuerpoMail);
			//$nCuerpo=str_replace("_usuario",$login,$nCuerpo);
			//$nCuerpo=str_replace("_contrasena",$password,$nCuerpo);
			$obj=array();
			$obj[0]=$idUsuario;
			$obj[1]=$nombre;
			$obj[2]=$mail;
			if(!enviarMail($mail,"CENSIDA",$nCuerpo,"proyectos@grupolatis.net","",NULL))			
			{
				
				array_push($arrUsrProblemas,$obj);
			}
			else
			{
				array_push($arrUsrOk,$obj);	
			}
		}
		echo "Usuarios Mal: ".sizeof($arrUsrProblemas)."<br>";
		varDump($arrUsrProblemas);
		echo "Usuarios OK: ".sizeof($arrUsrOk)." <br>";
		varDump($arrUsrOk);

	}

	function definirResultadoLicitacion($idProceso,$idFormulario,$idRegistro)	
	{
		global $con;
		$idFormularioBase=obtenerFormularioBase($idProceso);	
		$consulta="select idEstado from _".$idFormularioBase."_tablaDinamica where id__".$idFormularioBase."_tablaDinamica=".$idRegistro;

		$nEtapa=$con->obtenerValor($consulta);
		switch($idFormularioBase)
		{
			case 699:
			case 589:
				if($nEtapa>=3)
					return false;
			break;
			case 356:
				if($nEtapa>=2)
					return false;
				
			break;
		}
		
		return true;
		
		
	}
	
	function ingresarProveedoresPropuestas($idProceso,$idFormulario,$idRegistro)	
	{
		global $con;
		$idFormularioBase=obtenerFormularioBase($idProceso);	
		$consulta="select idEstado from _".$idFormularioBase."_tablaDinamica where id__".$idFormularioBase."_tablaDinamica=".$idRegistro;
		$nEtapa=$con->obtenerValor($consulta);
		switch($idFormularioBase)
		{
			case 699:
				if($nEtapa>=2)
					return false;
			break;
		}
		
		return true;
		
		
	}
	
	function enviarMailDocumentosFaltantes()
	{
		global $con;
		$cadInst="0010,0012,0013,0014,0021,0034,0034,0040,0048,0048,0054,0062,0064,0066,0069,0069,0069,0070,0072,0072,0073,0079,0088,0089,0091,0091,0113,0114,0118,0125,0128,0131,0131,0131,0132,0132,0138,0146,0173,0179,0179,0194,0194,0215,0273,0283,0307,0314,0318,0341,0347,0350,0351,0368,0369,0372,0372,0378,0404,0408,0408,0408,0409";
		$arrInstituciones=explode(",",$cadInst);
		$arrUsrProblemas=array();
		$arrUsrOk=array();
		foreach($arrInstituciones as $institucion)
		{
		
			$query="SELECT idUsuario FROM 801_adscripcion WHERE Institucion='".$institucion."'";
			$listUsr=$con->obtenerListaValores($query);
			if($listUsr=="")
				$listUsr="-1";
			$query="SELECT distinct u.idUsuario,u.Login,u.Nombre,u.Password FROM  800_usuarios u WHERE  u.idUsuario in (".$listUsr.")";
			$res=$con->obtenerFilas($query);	
			while($fila=mysql_fetch_row($res))
			{
				$queryDocumentos="SELECT actaConstitutiva,comprobanteDomicilio,credencialElector,certificacionBancaria,edoCuenta,cedulaFiscal,escritura FROM _329_tablaDinamica WHERE codigoInstitucion='".$institucion."'";
				$filaDoc=$con->obtenerPrimeraFila($queryDocumentos);
				$documentos="";
				if($filaDoc[0]=="")
					$documentos.="Acta Constitutiva la OSC o IA<br>";
				if($filaDoc[1]=="")
					$documentos.="Comprobante de domicilio<br>";
				if($filaDoc[2]=="")
					$documentos.="Credencial de elector<br>";
				if($filaDoc[3]=="")
					$documentos.="Centificación bancaria<br>";
				if($filaDoc[4]=="")
					$documentos.="Estado de cuenta<br>";
				if($filaDoc[5]=="")
					$documentos.="Escritura de protocolización del representante<br>";
				if($filaDoc[6]=="")
					$documentos.="Comprobante de domicilio<br>";
				$cuerpoMail="Estimado _nombreRevisor: <br><br>Como resultado de la validación de los documentos de la organización que amablemente nos proporcionó, le informo que tuvimos problemas para visualizar los siguientes documentos: <br><br>_documento<br><br>
							Le suplico los ingrese nuevamente al portal (censida.grupolatis.net) con el fin de tener completo su expediente y poder llevar a cabo correctamente el trámite de su proyecto. <b>Nota:</b> De ser posible le pido que el formato de los archivos sea
							en pdf, si esto complica la realización de el reenvio de los datos, ignore este comentario.
				
							<br>
							<br>
							Esperando pueda llevar a cabo la actividad solicitada en la brevedad posible, le envio un cordial saludo, le agradezco  su comprensión y le deseo un buen día.<br><br>
							
							<b>Atentamente</b><br>
							Dr. Carlos García de León Moreno<br>
							Director de Prevención y Participación Social<br>
							CENSIDA";
							
				$idUsuario=$fila[0];
				$nombre=$fila[2];
				$login=$fila[1];
				$password=$fila[3];
				
				$query="SELECT distinct Mail FROM 805_mails WHERE idUsuario=".$fila[0];	
				$mail=$con->obtenerValor($query);
				//$mail="novant1730@hotmail.com";
				$nCuerpo=str_replace("_nombreRevisor",$nombre,$cuerpoMail);
				$nCuerpo=str_replace("_documento",$documentos,$nCuerpo);
				$obj=array();
				$obj[0]=$idUsuario;
				$obj[1]=$nombre;
				$obj[2]=$mail;
				
				if(!enviarMail($mail,"Convocatoria CENSIDA 2011, Validación de documento de proyectos",$nCuerpo,"proyectos@grupolatis.net","Dr. Carlos García de León",NULL))
				{
					array_push($arrUsrProblemas,$obj);
				}
				else
				{
					array_push($arrUsrOk,$obj);	
				}
				
			}
		}
		echo "Usuarios Mal: ".sizeof($arrUsrProblemas)."<br>";
		varDump($arrUsrProblemas);
		echo "Usuarios OK: ".sizeof($arrUsrOk)." <br>";
		varDump($arrUsrOk);
	}
	
	function esPoblacionVulnerable($idProceso,$idFormulario,$idRegistro)
	{
		global $con;
		$consulta="select cmbPacienteVulnerableSiNo from _256_tablaDinamica where idReferencia=".$idRegistro;
		$res=$con->obtenerValor($consulta);
		if($res==2)
			return false;
		else
			return true;
		
	}
	
	function asignarCandidatoPuesto($idRegistro)
	{
		global $con;
		$consulta="select * from _687_tablaDinamica where idReferencia=".$idRegistro;
		$fila=$con->obtenerPrimeraFila($consulta);
		$consulta="select * from _686_tablaDinamica where idReferencia=".$idRegistro;
		$filaCand=$con->obtenerPrimeraFila($consulta);
		$consulta="select ciclo from 550_cicloFiscal WHERE STATUS=1";
		$ciclo=$con->obtenerValor($consulta);
		$consulta="SELECT noQuincena FROM 656_calendarioNomina WHERE ciclo=".$ciclo." AND situacion=1 LIMIT 0,1";
		$quincena=$con->obtenerValor($consulta);
		$consulta="SELECT idUnidadOrgVSPuesto FROM 667_puestosVacantes WHERE idRegistroPerfil=".$idRegistro;
		$idTabulacion=$con->obtenerValor($consulta);
		
		$query="SELECT codUnidad,cvePuesto,p.idPuesto,p.zona,p.tipoPuesto,sueldoMinimo FROM 653_unidadesOrgVSPuestos up,819_puestosOrganigrama p WHERE p.idPuesto=up.idPuesto and up.idUnidadVSPuesto=".$idTabulacion;
		$filaPuesto=$con->obtenerPrimeraFila($query);
		$x=0;
		$consulta[$x]="begin";
		$x++;
		$consulta[$x]="insert into 801_fumpEmpleado(idUsuario,tipoOperacion,fechaAplicacion,pQuincenaPago,pCicloPago,salario,fechaOperacion,respOperacion,idPuesto,activo,puesto,idTabulacion,departamento,tipoPuesto,zona,tipoContratacion,horasTrabajador) values
						(".$filaCand[10].",1,'".$date("Y-m-d")."','".$quincena."',".$ciclo.",".$filaPuesto[5].",'".date('Y-m-d')."',".$_SESSION["idUsr"].",".$filaPuesto[2].",1,'".$filaPuesto[1]."',".$idTabulacion.
						",'".$filaPuesto[0]."',".$filaPuesto[3].",".$filaPuesto[4].",".$fila[10].",".$fila[11].")";
		$x++;
		$consulta[$x]="update 653_unidadesOrgVSPuestos set situacion=1 where idUnidadVSPuesto=".$idTabulacion;
		$x++;
		$consulta[$x]="update 801_adscripcion set cod_Puesto='".$filaPuesto[1]."',codigoUnidad='".$filaPuesto[0]."',tipoContratacion='".$fila[10]."',horasTrabajador=".$fila[11]." where idUsuario=".$filaCand[10];
		$x++;
		$consulta[$x]="update 667_puestosVacantes set status=2 where idUnidadOrgVSPuesto=".$idTabulacion;
		$x++;
		$consulta[$x]="commit";
		$x++;
		return $con->ejecutarBloque($consulta);
	}
	
	function asociarProveedorCompra($idFormulario,$idRegistro)
	{
		global $con;
		$consulta="select cmbProveedorInvitado from _700_tablaDinamica where idReferencia=".$idRegistro;
		$resProveedores=$con->obtenerFilas($consulta);
		$x=0;
		$query[$x]="begin";
		$x++;	
		while($fProveedor=mysql_fetch_row($resProveedores))
		{
			$query[$x]="insert into 530_licitacionVSProveedor(idFormulario,idReferencia,idProveedor) values (".$idFormulario.",".$idRegistro.",".$fProveedor[0].")";
			$x++;	
		}
		$query[$x]="commit";
		$x++;	
		return  $con->ejecutarBloque($query);
	}
	
	function registrarPartidaProveedor($idFormulario,$idRegistro)
	{
		global $con;
		$query="select * from 531_licitacionVSProveedorElegido where idFormulario=".$idFormulario." and idReferencia=".$idRegistro;
		$res=$con->obtenerFilas($query);
		$x=0;
		$consulta[$x]="begin";
		$x++;
		while($fila=mysql_fetch_row($res))
		{
			$consulta[$x]="insert 532_partidasPedido(idPartida,tipoOrigen,situacion,idProveedor,complementario,complementario2) 
							values(".$fila[0].",0,1,".$fila[1].",'".$fila[6]."','".$fila[7]."')";
			$x++;
		}
		$consulta[$x]="commit";
		$x++;	
		return $con->ejecutarBloque($consulta);
	}
	
	function guardarMovimiento($idRegistro,$idFormulario,$cadObj)
	{
		global $con;
		$obj=json_decode(bD($cadObj));
		$movimiento=generarFolioMovimiento($obj->tipoMovimiento);
		$consulta="insert into 561_movimientosCaja(tipoMovimiento,movimiento,fechaMovimiento,horaMovimiento,tipoPago,monto,tipoRecurso,situacion,idFormulario,idRegistro)
					values(".$obj->tipoMovimiento.",'".$movimiento."','".date('Y-m-d')."','".date("H:i")."',".$obj->tipoPago.",".$obj->monto.",".$obj->recurso.",".$obj->situacion.",
					".$idFormulario.",".$idRegistro.")";
		if($con->ejecutarConsulta($consulta))
		{
			$idConsulta=$con->obtenerUltimoID();
			echo "alert(window.parent.url);window.parent.asignarFolioMovimiento('".$movimiento."',".$idConsulta.");return;";
		}
	}
	
	function realizarAsientoMovimiento($idMovimiento,$numEtapa,$idFormulario,$idRegistro,$ciclo,$programa,$departamento,$partida,$monto,$tipoPresupuesto,$tipoPago,$mes="")
	{
		global $con;
		$tPresupuesto=$tipoPresupuesto;
		if($tPresupuesto=="")
			$tPresupuesto="NULL";
		if($tipoPago=="")
			$tipoPago="NULL";
		
		$consulta="SELECT txtNomMovimiento FROM _649_tablaDinamica WHERE id__649_tablaDinamica=".$idMovimiento;
		$nomMovimiento=$con->obtenerValor($consulta);
		$x=0;
		$consulta="begin";
		if($con->ejecutarConsulta($consulta))
		{
			//Afectaciones presupuestales
			$consulta="select folioMovimiento,noMovimientoActual from 900_movimientosRegistroFormularios where idFormulario=".$idFormulario." and idRegistro=".$idRegistro;
			$fila=$con->obtenerPrimeraFila($consulta);
			$folioMovimiento="";
			$nMovimiento="";
			if($fila)
			{
				$folioMovimiento=$fila[0];
				$nMovimiento=$fila[0]+1;
				$consulta="update 900_movimientosRegistroFormularios set noMovimientoActual=".$nMovimiento." where idFormulario=".$idFormulario." and idRegistro=".$idRegistro;
				if(!$con->ejecutarConsulta($consulta))
					return false;
			}
			else
			{
				$folioMovimiento=generarFolioMovimiento($idMovimiento,false);
				$nMovimiento=1;
				$consulta="insert into 900_movimientosRegistroFormularios(idFormulario,idRegistro,folioMovimiento,noMovimientoActual) values(".$idFormulario.",".$idRegistro.",'".$folioMovimiento."',1)";
				if(!$con->ejecutarConsulta($consulta))
					return false;
			}
			$consulta="SELECT * FROM _650_gridMovimientosPresupuestales WHERE cast(etapa as decimal)=".$numEtapa." AND idReferencia=".$idMovimiento." AND  tipoPago=".$tipoPago." AND tipoPresupuesto=".$tPresupuesto." order by tipoAfectacion";
			$res=$con->obtenerFilas($consulta);		
			if($con->filasAfectadas==0)
			{
				$consulta="SELECT * FROM _650_gridMovimientosPresupuestales WHERE cast(etapa as decimal)=".$numEtapa." AND idReferencia=".$idMovimiento." AND  tipoPago=".$tipoPago." order by tipoAfectacion";
				$res=$con->obtenerFilas($consulta);		
				if($con->filasAfectadas==0)
				{
					$consulta="SELECT * FROM _650_gridMovimientosPresupuestales WHERE cast(etapa as decimal)=".$numEtapa." AND idReferencia=".$idMovimiento." AND  tipoPresupuesto=".$tPresupuesto." order by tipoAfectacion";
					$res=$con->obtenerFilas($consulta);		
				}			
			}
			if($con->filasAfectadas>0)
			{
				while($fila=mysql_fetch_row($res))
				{
					$tiempoPresupuestal=$fila[3];
					$porcentajeAfectacion=$fila[4]/100;
					$tipoAfectacion=$fila[5];	
					$consulta="SELECT codigoControlPadre FROM 507_objetosGasto WHERE clave='".$partida."'";
					$codPadre=$con->obteenrValor($consulta);
					$consulta="SELECT clave FROM 507_objetosGasto WHERE codigoControl='".$codPadre."'";
					$capitulo=$con->obtenerValor($consulta);
					
					if($mes=="")
						$mes=date("m");
					$query[$x]="INSERT INTO 528_asientosCuentasPresupuestales(ciclo,programa,departamento,capitulo,partida,mes,monto,fechaOperacion,responsableOperacion,
								tiempoPresupuestal,operacion,idReferencia,idFormulario,tipoPago,fuenteFinanciamiento,descripcionMovimiento,folioMovimiento,noMovimiento)
								VALUES(".$ciclo.",".$programa.",'".$departamento."',".$capitulo.",".$partida.",".$mes.",".($monto*$porcentajeAfectacion).",'".date("Y-m-d")."',".$_SESSION["idUsr"].
								",".$tiempoPresupuestal.",".$tipoAfectacion.",".$idRegistro.",".$idFormulario.",".$tipoPago.",".$tipoPresupuesto.",'".$nomMovimiento."','".$folioMovimiento."',".$nMovimiento.")";
					$x++;								
				}
			}
			//afectaciones contables
			$consulta="SELECT * FROM _652_gridAfectacionContable WHERE cast(etapa as decimal)=".$numEtapa." AND idReferencia=".$idMovimiento." AND  tipoPago=".$tipoPago." AND tipoPresupuesto=".$tPresupuesto." order by tipoAfectacion";
			$res=$con->obtenerFilas($consulta);		
			if($con->filasAfectadas==0)
			{
				$consulta="SELECT * FROM _652_gridAfectacionContable WHERE cast(etapa as decimal)=".$numEtapa." AND idReferencia=".$idMovimiento." AND  tipoPago=".$tipoPago." order by tipoAfectacion";
				$res=$con->obtenerFilas($consulta);		
				if($con->filasAfectadas==0)
				{
					$consulta="SELECT * FROM _652_gridAfectacionContable WHERE cast(etapa as decimal)=".$numEtapa." AND idReferencia=".$idMovimiento." AND  tipoPresupuesto=".$tPresupuesto." order by tipoAfectacion";
					$res=$con->obtenerFilas($consulta);		
				}			
			}
			if($con->filasAfectadas>0)
			{
				while($fila=mysql_fetch_row($res))
				{
					$porcentajeAfectacion=$fila[4]/100;
					$cuenta=$fila[3];
					$consulta="SELECT codigoControlPadre FROM 507_objetosGasto WHERE clave='".$partida."'";
					$codPadre=$con->obteenrValor($consulta);
					$consulta="SELECT clave FROM 507_objetosGasto WHERE codigoControl='".$codPadre."'";
					$capitulo=$con->obtenerValor($consulta);
					if($mes=="")
						$mes=date("m");
						
					$montoDebe=0;	
					$montoHaber=0;
					if($tipoAfectacion==1)
						$montoDebe=$monto*$porcentajeAfectacion;
					else
						$montoHaber=$monto*$porcentajeAfectacion;
					$query[$x]="INSERT INTO 514_asientos(fechaMovimiento,idUsuario,idProceso,objetoGasto,tipoPresupuesto,cuentaMascara,cuentaSimple,montoDebe,
								montoHaber,noDocumento,concepto,idLibro,programa,idFormulario,noMovimiento) VALUES('".date("Y-m-d")."',".$_SESSION["idUsr"].",".$partida.",".
								$tipoPresupuesto.",'".$cuenta."','".$cuenta."',".$montoDebe.",".$montoHaber.",'".$folioMovimiento."','".$nomMovimiento."',".$idLibro
								.",".$programa.",".$idFormulario.",".$nMovimiento.")";		
					$x++;
				}
			}
			
			$query[$x]="commit";
			$x++;
			return $con->ejecutarBloque($query);
		}
		else
			return false;
	}
	
	function obtenerSaldoPresupuestal($programa,$depto,$partida,$ciclo,$mes,$fuenteFinanciamiento,$tiempoPresupuestal)
	{
		global $con;	
		$consulta="SELECT monto,tiempoPresupuestal,operacion FROM 528_asientosCuentasPresupuestales WHERE fuenteFinanciamiento=".$fuenteFinanciamiento." and programa=".$programa." AND departamento='".$depto."' AND partida=".$partida." AND ciclo=".$ciclo." and mes=".($iMonto-1);
		$resAsientos=$con->obtenerFilas($consulta);
		$arrAsientos=array();
		while($fila=mysql_fetch_row($resAsientos))
		{
			$monto=$fila[0]*$fila[2];
			if(!isset($arrAsientos[$fila[1]]))	
				$arrAsientos[$fila[1]]=$monto;
			else
				$arrAsientos[$fila[1]]+=$monto;
		}
		return $arrAsientos[$tiempoPresupuestal];		
	}
	
	function verificarSuficienciaPresupuestal($programa,$depto,$partida,$ciclo,$mes,$tiempoPresupuestal,$fuenteFinanciamiento,$monto)
	{
		$saldo=obtenerSaldoPresupuestal($programa,$depto,$partida,$ciclo,$mes,$fuenteFinanciamiento,$tiempoPresupuestal);
		if($saldo<$monto)
			return false;
		else
			return true;
	}
	
	function cancelarMovimiento($folioMovimiento,$nMovimientoInicial,$nMovimientoFinal)
	{
		global $con;
		$x=0;
		$consulta="select * from 528_asientosCuentasPresupuestales WHERE folioMovimiento='".$folioMovimiento."' AND noMovimiento>=".$nMovimientoInicial." and noMovimiento<=".$nMovimientoFinal;
		$resAsientos=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($resAsientos))
		{
			$query[$x]="INSERT INTO 528_asientosCuentasPresupuestales(ciclo,programa,departamento,capitulo,partida,mes,monto,
						fechaOperacion,responsableOperacion,tiempoPresupuestal,operacion,idReferencia,idProceso,complementario,origenMovimiento,
						idFormulario,tipoPago,fuenteFinanciamiento,descripcionMoviento,folioMovimiento,noMovimiento)
						VALUES(".$fila[1].",".$fila[2].",'".$fila[3]."',".$fila[4].",".$fila[5].",".$fila[6].",".$fila[7].",'".$fila[8]."',".$_SESSION["idUsr"].",".$fila[10]
						.",".($fila[11]*-1).",".$fila[12].",".$fila[13].",'".$fila[14]."',".$fila[15].",".$fila[16].",".$fila[17].",".$fila[18].",'".$fila[19]."','".
						$fila[20]."',".$fila[21].")";
			$x++;
		}
		$consulta="select * from 514_asientos WHERE noDocumento='".$folioMovimiento."' AND noMovimiento>=".$nMovimientoInicial." and noMovimiento<=".$nMovimientoFinal;
		$resAsientos=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($resAsientos))
		{
			$query[$x]="INSERT INTO fechaMovimiento,idUsuario,centroCosto,idProyecto,objetoGasto,
						tipoPresupuesto,cuentaMascara,cuentaSimple,montoDebe,montoHaber,idTipoDocumento,noDocumento,idLibro,
						concepto,estado,idFormulario,noMovimiento
						VALUES('".$fila[0]."',".$fila[1].",".$_SESSION["idUsr"].",'".$fila[2]."',".$fila[3].",".$fila[4].",'".$fila[5]."','".$fila[6]."',".$fila[7].",".
						$fila[9].",".$fila[8].",".$fila[10].",'".$fila[11]."',".$fila[12].",'".$fila[13]."',".$fila[14].",".$fila[15].",".$fila[16].")";
			$x++;
		}
		
	}
	
	function statusContrato($idRegistro,$statusAsociados)
	{
		global $con;
		$consulta="SELECT asignatura FROM _274_gridAsignaturas WHERE idReferencia=".$idRegistro;
		$list=$con->obtenerListaValores($consulta);
		if($list=="")
			$list="-1";
		$consulta="UPDATE 4047_participacionesMateria SET esperaContrato=".$statusAsociados." WHERE idParticipante IN (".$list.")";
		return $con->ejecutarConsulta($consulta);
		
	}
	
	function crearCargaAcademicaDocente($idProceso,$idFormulario,$actor)
	{
		global $con;
		$consulta="select anio from 4015_ciclos where status=1";
		$ciclo=$con->obtenerValor($consulta);
		
		$consulta="INSERT INTO _239_tablaDinamica(fechaCreacion,responsable,idEstado,codigoUnidad,codigoInstitucion,ciclo)
				VALUES('".date('Y-m-d')."',".$_SESSION["idUsr"].",1,'".$_SESSION["codigoUnidad"]."','".$_SESSION["codigoInstitucion"]."',".$ciclo.")";
		if($con->ejecutarConsulta($consulta))
		{
			$idRegistro=$con->obtenerUltimoID();
			$consulta="update _239_tablaDinamica set codigo=".$idRegistro." where id__239_tablaDinamica=".$idRegistro;
			if($con->ejecutarConsulta($consulta))
			{
				$consulta="select actor from 949_actoresVSAccionesProceso where idActorVSAccionesProceso=".$actor;
				$rolActor=$con->obtenerValor($consulta);
				$consulta="select idActorProcesoEtapa from 944_actoresProcesoEtapa where idProceso=".$idProceso." and numEtapa=1 and actor='".$rolActor."' and tipoActor=1";
				$actorEtapa1=$con->obtenerValor($consulta);
				if($actorEtapa1=="")
					$actorEtapa1="0";
				echo "1|".$idRegistro."|".$actorEtapa1;
			}
		}
	}
	
	function enviarCandidatosComite($idFormulario,$idRegistro)
	{
		global $con;
		
		$query="SELECT cmbcandidatos,examen FROM _294_tablaDinamica WHERE idReferencia=".$idRegistro;
		$res=$con->obtenerFilas($query);
		$x=0;
		$consulta[$x]="begin";
		$x++;
		$query="select cmbMateria,cmbPrograma from _290_tablaDinamica where id__290_tablaDinamica=".$idRegistro;
		$filaMat=$con->obtenerPrimeraFila($query);
		$idMateria=$filaMat[0];
		$idPrograma=$filaMat[1];
		while($fila=mysql_fetch_row($res))
		{
			$consulta[$x]="insert into _332_tablaDinamica(idReferencia,fechaCreacion,responsable,idEstado,codigoInstitucion,codigoUnidad,cmbUsuarios,fechaexa,cmbMateria,cmbPrograma)
							VALUES(".$idRegistro.",'".date("Y-m-d")."',".$_SESSION["idUsr"].",2,'".$_SESSION["codigoInstitucion"]."','".$_SESSION["codigoUnidad"]."',".$fila[0].",'".$fila[1]."',".$idMateria.",".$idPrograma.")";	
			$x++;
			$consulta[$x]="update _332_tablaDinamica set codigo=id__332_tablaDinamica where id__332_tablaDinamica=(select LAST_INSERT_ID())";
			$x++;
		}
		$consulta[$x]="commit";
		$x++;
		return $con->ejecutarBloque($consulta);
	}
	
	function verificarCandidatosMateria($idFormulario,$idRegistro)
	{
		global $con;
		$consulta="select idReferencia from _".$idFormulario."_tablaDinamica where id__".$idFormulario."_tablaDinamica=".$idRegistro;
		$idReferencia=$con->obtenerValor($consulta);
		$consulta="select idEstado from _".$idFormulario."_tablaDinamica where idEstado=2 and id__".$idFormulario."_tablaDinamica<>".$idRegistro." and idReferencia=".$idReferencia;
		$res=$con->obtenerFilas($consulta);

		if($con->filasAfectadas==0)
		{
			
			return cambiarEtapaFormulario("290",$idReferencia,4);
		}
		return true;	
	}
	
	function validarFechaInicioEvaluacion($idFormulario,$idRegistro)
	{
		global $con;
		$consulta="select * from _".$idFormulario."_tablaDinamica where fechaexa<='".date("Y-m-d")."' and id__".$idFormulario."_tablaDinamica=".$idRegistro;
		$fila=$con->obtenerPrimeraFila($consulta);
		if($fila)
			return true;
		return false;
	}
	
	
	function autorEsInvestigador($idProceso,$idFormulario,$idRegistro)
	{
		global $con;
		$idFormulario=obtenerFormularioBase($idProceso);
		$consulta="select responsable from _".$idFormulario."_tablaDinamica where id__".$idFormulario."_tablaDinamica=".$idRegistro;

		$idResp=$con->obtenerValor($consulta);
		if($idResp=="")
			$idResp="-1";
		$consulta="SELECT * FROM 807_usuariosVSRoles WHERE codigoRol='15_0' AND idUsuario=".$idResp;
		$fila=$con->obtenerPrimeraFila($consulta);
		if($fila)
			return false;
		return true;
	}
	
	function eventoEsBeca($idProceso,$idFormulario,$idRegistro)
	{
		global $con;
		$idFormulario=obtenerFormularioBase($idProceso);
		$consulta="select cmbEventos from _".$idFormulario."_tablaDinamica where id__".$idFormulario."_tablaDinamica=".$idRegistro;

		$idResp=$con->obtenerValor($consulta);
		if($idResp=="2")
			return false;
		return true;
	}
	
	function generarPeriodos($idFormulario,$idRegistro)
	{
		global $con;
		global $arrMesLetra;
		$consulta="select txtNoMeses from _".$idFormulario."_tablaDinamica where id__".$idFormulario."_tablaDinamica=".$idRegistro;	
		$nMeses=$con->obtenerValor($consulta);
		if(($nMeses=="")||($nMeses==0))	
			$nMeses=1;
		$x=0;
		$query[$x]="begin";
		$x++;
		$query[$x]="delete from 9315_periodosRoles";
		$x++;
		$ctMeses=1;
		$ctPeriodo=1;
		$nPeriodos=(int)(12/$nMeses);
		if((12%$nMeses)!=0)
			$nPeriodos++;
		$ctMeses=1;
		$arrPeriodos=array();
		
		for($nP=1;$nP<=$nPeriodos;$nP++)
		{
			$cadMeses="";
			for($nMes=1;$nMes<=$nMeses;$nMes++)
			{
				if($cadMeses=="")
					$cadMeses=$ctMeses;
				else
					$cadMeses.=",".$ctMeses;
				$ctMeses++;
				if($ctMeses>12)
					break;
			}
			$arrMeses=explode(",",$cadMeses);
			$meses=$arrMesLetra[($arrMeses[0]-1)]." - ".$arrMesLetra[($arrMeses[(sizeof($arrMeses)-1)]-1)];
			$query[$x]="insert into 9315_periodosRoles(etiqueta,mesConsidera,noPeriodo) values('Periodo ".$nP." ".$meses." ','".$cadMeses."',".$nP.")";
			$x++;
			
		}
		
		$query[$x]="commit";
		$x++;
		$con->ejecutarBloque($query);
		echo "document.getElementById('frmEnvio').submit();";
	}
	
	
	function obtenerFechasAplicacion($tabla,$campoFechaInicio,$campoFechaFin,$condWhere)
	{
		global $con;
		$listFechas="";
		$consulta="select ".$campoFechaInicio.",".$campoFechaFin." from ".$tabla;
		if($condWhere!="")
			$consulta.=" where ".$condWhere;
		$resFila=$con->obtenerFilas($consulta);

		while($fila=mysql_fetch_row($resFila))
		{
			
			$fIni=strtotime($fila[0]);
			$fFin=strtotime($fila[1]);
			
			while($fIni<=$fFin)
			{
				if($listFechas=="")
					$listFechas=date("Y-m-d",$fIni);
				else	
					$listFechas.=",".date("Y-m-d",$fIni);
				$fIni=strtotime("+1 day",$fIni);
			}
		}
		if($listFechas=="")
			$listFechas="-1";
		return $listFechas	;
	}
	
	function requiereDocumentoComplementarios($idProceso,$idFormulario,$idRegistro)
	{
		global $con;
		$idFormulario=obtenerFormularioBase($idProceso);
		$consulta="SELECT sinoanexos FROM  _".$idFormulario."_tablaDinamica where id__278_tablaDinamica=".$idRegistro;

		$res=$con->obtenerValor($consulta);
		if($res==1)
			return false;
		else
			return true;
		
	}
	
	function publicarConvocatoriaProceso($idFormulario,$idRegistro)
	{
	  	global $con;
		$consulta="SELECT fechaIniPublicacion,fechaFinPublicacion,cicloAplica FROM 9042_formularioVSFechasConvocatoria 
					  WHERE idFormulario=".$idFormulario." AND idReferencia=".$idRegistro;
		$filasFechas=$con->obtenerPrimeraFila($consulta);
		$fechaIni="NULL";
		$fechaFin="NULL";
		$cicloAplica="NULL";
		if($filasFechas)
		{
			$fechaIni="'".$filasFechas[0]."'";
			$fechaFin="'".$filasFechas[1]."'";
			$cicloAplica=$filasFechas[2];
			if($cicloAplica=="")
				$cicloAplica="NULL";
			else
				$cicloAplica="'".$cicloAplica."'";
				
		}		
		$idProceso=obtenerIdProcesoFormulario($idFormulario);
		$consulta="SELECT vincularProcesoRegistroEnLinea FROM 9044_datosConvocatoria WHERE idFormulario=".$idFormulario." AND idReferencia=".$idRegistro;
		$idProcesoRegistro=$con->obtenerValor($consulta);
		if($idProcesoRegistro=="")
		{
			$consulta="select valoresDefault FROM 4001_procesos WHERE idProceso=".$idProceso;
			$cadObj=$con->obtenerValor($consulta);
			$idProcesoConv="-1";
			if($cadObj!="")
			{
				$obj=json_decode($cadObj);
				if(isset($obj->idProcesoReg))
					$idProcesoRegistro=$obj->idProcesoReg;
				else
					$idProcesoRegistro="-1";
			}
			else
				$idProcesoRegistro="-1";
		}
		$consulta="select complementario FROM 4001_procesos WHERE idProceso=".$idProceso;
		$idReporte=$con->obtenerValor($consulta);
		if($idReporte=="")
			$idReporte="-1";
		
		$consulta="INSERT INTO 9118_convocatoriasPublicadas(idFormulario,idRegistro,idReporte,fechaActivacion,fechaIniPublica,fechaFinPublica,
				  status,idProcesoRegistro,ciclo) VALUES(".$idFormulario.",".$idRegistro.",".$idReporte.",'".date('Y-m-d')."',".$fechaIni.",
				   ".$fechaFin.",1,".$idProcesoRegistro.",".$cicloAplica.")";
		return $con->ejecutarConsulta($consulta);		
	}
	
	
	function cambiarEstadoCliente($idFormulario,$idRegistro)
	{
		global $con;
		$consulta="SELECT contacto,idReferencia,fechaCita,horaCita FROM _830_tablaDinamica WHERE id__830_tablaDinamica=".$idRegistro;
		$fila=$con->obtenerPrimeraFila($consulta);
		$resultado=$fila[0];	
		$idCliente=$fila[1];
		$consulta="SELECT statusRelacion FROM _829_tablaDinamica WHERE id__829_tablaDinamica=".$resultado;
		$idEstado=$con->obtenerValor($consulta);
		if($idEstado==6)
		{
			if($fila[3]=="")
				$idEstado=8;
		}
		$consulta="UPDATE _813_tablaDinamica SET idEstado=".$idEstado." WHERE id__813_tablaDinamica=".$idCliente;
		if($con->ejecutarConsulta($consulta))
		{
			return registarSeguimientoCliente($idCliente,$idFormulario,$idRegistro);
		}
		return false;
	}
	
	function cambiarAEstadoEsperaResultadoCita($idFormulario,$idRegistro)
	{
		global $con;
		$consulta="SELECT idReferencia FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
		$fila=$con->obtenerPrimeraFila($consulta);
		$idCliente=$fila[0];
		if($idFormulario==833)
		{
			$consulta="SELECT fecha FROM 830_vistaCitas WHERE fecha>='".date("Y-m-d")."'";
			$fecha=$con->obtenerValor($consulta);
			if($fecha!="")
				$idEstado=9;
			else
				$idEstado=8;
		}
		if($idFormulario==834)
		{
			$consulta="select agentes from _833_tablaDinamica where idReferencia=".$idCliente;
			$agente=$con->obtenerValor($consulta);
			if($agente=="")
				$idEstado=6;
			else
				$idEstado=9;
			
		}
		$consulta="UPDATE _813_tablaDinamica SET idEstado=".$idEstado." WHERE id__813_tablaDinamica=".$idCliente;
		if($con->ejecutarConsulta($consulta))
		{
			echo "window.parent.ejecutarFuncionIframe('recargarGrids','');window.parent.cerrarVentanaFancy();return;";
			return true;
		}
		return false;
	}
	
	function resolverSituacionResultadoCita($idFormulario,$idRegistro)
	{
		global $con;
		$consulta="SELECT idReferencia,resultadoCita,fechaProxCita FROM _832_tablaDinamica WHERE id__832_tablaDinamica=".$idRegistro;
		$fila=$con->obtenerPrimeraFila($consulta);
		
		$idCliente=$fila[0];
		$resultado=$fila[1];
		$idEstado="";
		switch($resultado)
		{
			case 1:
				$idEstado=9;
				if($fila[2]=="")
					$idEstado=7;
			break;
			case 2:
				$idEstado=3;
			break;
			case 3:
				$idEstado=10;
				$consulta="INSERT INTO _835_tablaDinamica(idReferencia,fechaCreacion,responsable,idEstado,codigoUnidad,codigoInstitucion,codigo) VALUES(".$idRegistro.",'".date("Y-m-d")."',".$_SESSION["idUsr"].",1,'".$_SESSION["codigoUnidad"]."','".$_SESSION["codigoInstitucion"]."','".obtenerFolioFormulario("835")."')";
				$con->ejecutarConsulta($consulta);
			break;
		}

		$consulta="UPDATE _813_tablaDinamica SET idEstado=".$idEstado." WHERE id__813_tablaDinamica=".$idCliente;
		if($con->ejecutarConsulta($consulta))
		{
			return registarSeguimientoCliente($idCliente,$idFormulario,$idRegistro);
		}
		return false;
	}
	
	function registarSeguimientoCliente($idCliente,$idFormulario,$idRegistro)
	{
		global $con;
		$consulta="INSERT INTO 3000_registroSeguimientoClientes(idCliente,idFormularioSeguimiento,idRegistroSeguimiento,fecha,responsable) VALUES(".$idCliente.",".$idFormulario.",".$idRegistro.",'".date("T-m- H:i")."',".$_SESSION["idUsr"].")";
		if($con->ejecutarConsulta($consulta))
		{
			echo "window.parent.ejecutarFuncionIframe('recargarGrids','');window.parent.cerrarVentanaFancy();return;";
			return true;
		}
		return false;
	}
	
	function resolverConfiguracion($idFormulario,$idRegistro,$oParam,$conf)
	{
		global $con;
		$obj=json_decode($conf);
		$objParam=json_decode($oParam);
		if(isset($obj->campoDictamen))
		{
			$valorCampoDictamen=obtenerValorControlFormularioBase($obj->campoDictamen,$idRegistro);
			$valorCampoComentario=obtenerValorControlFormularioBase($obj->campoComentario,$idRegistro);
			$actorDictamen=$objParam->actor;
			$consulta="insert into 2002_comentariosRegistro(comentario,dictamen,fechaHoraDictamen,actorResponsableDictamen,idFormulario,idRegistro,idFormularioDictamen,idRegistroDictamen,VERSION,idUsuarioResponsable,idActor)
						 VALUES('".cv($valorCampoComentario)."','".cv($valorCampoDictamen)."','".date('Y-m-d H:i')."','".cv($actorDictamen)."',".$objParam->idFormulario.",".$objParam->idRegistro.",".$idFormulario.",".$idRegistro.",".$objParam->version.",".$_SESSION["idUsr"].",".$objParam->idActor.")";
			return $con->ejecutarConsulta($consulta);						 
		}
		
		return true;
	}
	
	function obtenerProfesoresCompatiblesHorarioGrupoV2($idGrupo,$fechaAplicacion,$fechaTermino,$idSolicitud=-1)
	{
		global $con;
		$listCandidatos="";
		$arrHorarioGpo=array();
		$arrHorarioGrupo=obtenerFechasHorarioGrupoV2($idGrupo,$idSolicitud,true,true,$fechaAplicacion,$fechaTermino);
		
		if(sizeof($arrHorarioGrupo)>0)
		{
			foreach($arrHorarioGrupo as $fila)
			{
				if(!isset($arrHorarioGpo[$fila[2]]))
					$arrHorarioGpo[$fila[2]]=array();
				$obj[0]=$fila[3];
				$obj[1]=$fila[4];
				$obj[2]=$fila[6];
				$obj[3]=$fila[7];
				array_push($arrHorarioGpo[$fila[2]],$obj);
			}
		}
		
		$consulta="select g.fechaInicio,g.fechaFin,g.idPeriodo,g.idCiclo,g.idInstanciaPlanEstudio,g.Plantel from 4520_grupos g where g.idGrupos=".$idGrupo;
		
		$filaFechas=$con->obtenerPrimeraFila($consulta);
		
		$idCiclo=$filaFechas[3];
		$idPeriodo=$filaFechas[2];
		$plantel=$filaFechas[5];
		


		$consulta="SELECT distinct idUsuario FROM 4065_disponibilidadHorario d	WHERE d.ciclo=".$idCiclo;



		$resFilas=$con->obtenerFilas($consulta);
		while($fProfesor=mysql_fetch_row($resFilas))
		{
			$idPeriodoRef=$idPeriodo;
			$idCicloRef=$idCiclo;
			$considerarProfesor=true;

			if(sizeof($arrHorarioGpo)>0)
			{
				
				$idUsuario=$fProfesor[0];
				$consulta="SELECT count(*) FROM 4065_disponibilidadHorario d,_1026_tablaDinamica r WHERE d.idUsuario=".$idUsuario." AND d.ciclo=".$idCiclo." and d.idPeriodo=".$idPeriodo." and d.idFormulario=1026 
				and r.id__1026_tablaDinamica=d.idReferencia and r.idEstado=2";


				$nReg=$con->obtenerValor($consulta);
				if(($nReg==0)&&($idCiclo<=10))
				{
					if(!esPeriodoBase($idPeriodo))
					{
						$consulta="select fechaInicial,fechaFinal from 4544_fechasPeriodo where idPeriodo=".$idPeriodo." and idCiclo=".$idCiclo." and idInstanciaPlanEstudio=".$filaFechas[4];
						$fechasPeriodo=$con->obtenerPrimeraFila($consulta);
						
						$consulta="SELECT distinct idPeriodo,idCiclo FROM 4544_fechasPeriodo WHERE '".$fechasPeriodo[0]."'>=fechaInicial AND '".$fechasPeriodo[0]."'<=fechaFinal AND 
								idPeriodo IN (".obtenerPeriodoBase().")  AND Plantel='".$plantel."' ORDER BY fechaFinal DESC";

						$fPeriodo=$con->obtenerPrimeraFila($consulta);
						if($fPeriodo)
						{
							$idPeriodoRef=$fPeriodo[0];
							$idCicloRef=$fPeriodo[1];
						}
					}
				}
				
				
				$consulta="SELECT idDiaSemana,horaInicio,horaFin FROM 4065_disponibilidadHorario d WHERE d.ciclo=".$idCicloRef." and idPeriodo=".$idPeriodoRef." and idUsuario=".$fProfesor[0]." and tipo=1";
				$resH=$con->obtenerFilas($consulta);
				
				if($con->filasAfectadas>0)
				{
					$arrHorarioProf=array();
					while($fila=mysql_fetch_row($resH))
					{
						if(!isset($arrHorarioProf[$fila[0]]))
							$arrHorarioProf[$fila[0]]=array();
						$obj[0]=$fila[1];
						$obj[1]=$fila[2];
						array_push($arrHorarioProf[$fila[0]],$obj);
					}
					
					foreach($arrHorarioProf as $h=>$resto)
					{
						$arrHorarioProf[$h]=organizarBloquesHorario($resto);
					}
					
					foreach($arrHorarioGpo as $d=>$horario)
					{
						if($d!=10)
						{
							if(isset($arrHorarioProf[$d]))
							{
								
								foreach($horario as $h)
								{
									
									$encontrado=false;
									foreach($arrHorarioProf[$d] as $intervalo)
									{
										if(cabeEnIntervaloTiempo($h,$intervalo))
										{
											$encontrado=true;
											break;
										}
									}
									
									if($encontrado)
									{
										$fechaInicio=$h[2];
										$fechaFin=$h[3];
										$comp=generarConsultaIntervalos($fechaInicio,$fechaFin,"h.fechaInicio","h.fechaFin");
										$comp2=generarConsultaIntervalos($fechaInicio,$fechaFin,"a.fechaAsignacion","a.fechaBaja");
										$arrAsignaciones=obtenerAsignacionesProfesor($fProfesor[0],$idSolicitud,$fechaInicio,$fechaFin,$idGrupo);
										foreach($arrAsignaciones as $a)
										{
											$fInicioProf=$fechaInicio;
											$fFinProf=$fechaFin;
											
											if(strtotime($a[6])>strtotime($fInicioProf))
											{
												$fInicioProf=$a[6];
											}
											
											if(strtotime($a[7])<strtotime($fFinProf))
											{
												$fFinProf=$a[7];
											}
											
											$arrHorario=obtenerFechasHorarioGrupoV2($a[1],$idSolicitud,true,true,$fInicioProf,$fFinProf,-1);
											if(sizeof($arrHorario)>0)
											{
												
												foreach($arrHorario as $horario)
												{
													if($horario[2]==$d)
													{
														$fOcupa[1]=$horario[3];
														$fOcupa[2]=$horario[4];
														if(colisionaTiempo($h[0],$h[1],$fOcupa[1],$fOcupa[2]))
														{
															$considerarProfesor=false;
															break;
														}
													}
												}
											}
										}
										$encontrado=false;
									}
									else
										$considerarProfesor=false;
									if((!$considerarProfesor)||($encontrado))
										break;
								}
								if(!$considerarProfesor)
									break;
		
							}
							else
								$considerarProfesor=false;
							if(!$considerarProfesor)
							break;
						}
						
					}
				
				}
				else
					$considerarProfesor=false;
			}
			
			if($considerarProfesor)
			{
				if($listCandidatos=="")
					$listCandidatos=$fProfesor[0];
				else
					$listCandidatos.=",".$fProfesor[0];
				
			}
		}

		if($listCandidatos=="")
			$listCandidatos=-1;

		return "'".$listCandidatos."'";
	}
	
	function obtenerProfesoresCompatiblesHorarioGrupo($idGrupo,$fechaAplicacion)
	{
		return "";
		global $con;
		$listCandidatos="";
		$consulta="SELECT dia,horaInicio,horaFin,fechaInicio,fechaFin FROM 4522_horarioGrupo WHERE (fechaFin>='".$fechaAplicacion."')  and idGrupo=".$idGrupo;

		$res=$con->obtenerFilas($consulta);
		$arrHorarioGpo=array();
		while($fila=mysql_fetch_row($res))
		{
			if(!isset($arrHorarioGpo[$fila[0]]))
				$arrHorarioGpo[$fila[0]]=array();
			$obj[0]=$fila[1];
			$obj[1]=$fila[2];
			$obj[2]=$fila[3];
			$obj[3]=$fila[4];
			array_push($arrHorarioGpo[$fila[0]],$obj);
		}
		
		$consulta="select g.fechaInicio,g.fechaFin,g.idPeriodo,g.idCiclo,g.idInstanciaPlanEstudio from 4520_grupos g where g.idGrupos=".$idGrupo;
		$filaFechas=$con->obtenerPrimeraFila($consulta);
		$idCiclo=$filaFechas[3];
		$idPeriodo=$filaFechas[2];
		
		if(!esPeriodoBase($idPeriodo))
		{
			$consulta="select fechaInicial,fechaFinal from 4544_fechasPeriodo where idPeriodo=".$idPeriodo." and idCiclo=".$idCiclo." and idInstanciaPlanEstudio=".$filaFechas[4];
			$fechasPeriodo=$con->obtenerPrimeraFila($consulta);
			$consulta="SELECT idPeriodo,idCiclo FROM 4544_fechasPeriodo WHERE '".$fechasPeriodo[0]."'>=fechaInicial AND '".$fechasPeriodo[0]."'<=fechaFinal AND idPeriodo IN (".obtenerPeriodoBase().") and idInstanciaPlanEstudio=".$filaFechas[4];
			$fPeriodo=$con->obtenerPrimeraFila($consulta);
			if($fPeriodo)
			{
				$idCiclo=$fPeriodo[1];
				$idPeriodo=$fPeriodo[0];
			}
		}
		
		$consulta="SELECT distinct idUsuario FROM 4065_disponibilidadHorario d
					WHERE d.ciclo=".$idCiclo." and idPeriodo=".$idPeriodo." and tipo=1 ";
		$resFilas=$con->obtenerFilas($consulta);
		while($fProfesor=mysql_fetch_row($resFilas))
		{
			
			$considerarProfesor=true;
			if(sizeof($arrHorarioGpo)>0)
			{
				$consulta="SELECT idDiaSemana,horaInicio,horaFin FROM 4065_disponibilidadHorario d WHERE d.ciclo=".$idCiclo." and idPeriodo=".$idPeriodo." and idUsuario=".$fProfesor[0]." and tipo=1";
				$resH=$con->obtenerFilas($consulta);
				$arrHorarioProf=array();
				while($fila=mysql_fetch_row($resH))
				{
					if(!isset($arrHorarioProf[$fila[0]]))
						$arrHorarioProf[$fila[0]]=array();
					$obj[0]=$fila[1];
					$obj[1]=$fila[2];
					array_push($arrHorarioProf[$fila[0]],$obj);
				}
				
				foreach($arrHorarioProf as $h=>$resto)
				{
					$arrHorarioProf[$h]=organizarBloquesHorario($resto);
				}
				
				foreach($arrHorarioGpo as $d=>$horario)
				{
					if(isset($arrHorarioProf[$d]))
					{
						
						foreach($horario as $h)
						{
							
							$encontrado=false;
							foreach($arrHorarioProf[$d] as $intervalo)
							{
								if(cabeEnIntervaloTiempo($h,$intervalo))
								{
									$encontrado=true;
									break;
								}
							}
							
							if($encontrado)
							{
								$fechaInicio=$h[2];
								$fechaFin=$h[3];
								$consulta="SELECT h.dia,h.horaInicio,h.horaFin,g.idGrupos FROM 4519_asignacionProfesorGrupo a,4520_grupos g,4522_horarioGrupo h 
										WHERE g.idGrupos=a.idGrupo AND h.idGrupo=a.idGrupo AND idUsuario=".$fProfesor[0]." AND participacionPrincipal=1 AND a.situacion=1 AND g.idGrupos<>".$idGrupo." and h.dia=".$d." and
										((g.fechaInicio<='".$fechaInicio."' AND g.fechaFin>='".$fechaInicio."')||(g.fechaInicio<='".$fechaFin."' AND g.fechaFin>='".$fechaFin."')||(g.fechaInicio>='".$fechaInicio.
										"' AND g.fechaFin<='".$fechaFin."'))
										 order by horaInicio";
								$resOcupa=$con->obtenerFilas($consulta);
								while($fOcupa=mysql_fetch_row($resOcupa))
								{
									
									if(colisionaTiempo($h[0],$h[1],$fOcupa[1],$fOcupa[2]))
									{
										$considerarProfesor=false;
										break;
									}
								}
								$encontrado=false;
							}
							else
								$considerarProfesor=false;
							if((!$considerarProfesor)||($encontrado))
								break;
							
							
						}
						if(!$considerarProfesor)
							break;

					}
					else
						$considerarProfesor=false;
					if(!$considerarProfesor)
						break;
				}
				
				
			}
			
			if($considerarProfesor)
			{
				if($listCandidatos=="")
					$listCandidatos=$fProfesor[0];
				else
					$listCandidatos.=",".$fProfesor[0];
			}
		}

		if($listCandidatos=="")
			$listCandidatos=-1;
		return "'".$listCandidatos."'";
	}
	
	function actualizarCiclo($idFormulario,$idRegistro)
	{
		global $con;
		$consulta="SELECT cmbConvocatoria FROM _298_tablaDinamica WHERE id__298_tablaDinamica=".$idRegistro;
		$idConvocatoria=$con->obtenerValor($consulta);
		$consulta="SELECT cmbCiclos FROM _277_tablaDinamica WHERE id__277_tablaDinamica=".$idConvocatoria;
		$ciclo=$con->obtenerValor($consulta);
		$consulta="update _298_tablaDinamica set ciclo=".$ciclo." WHERE id__298_tablaDinamica=".$idRegistro;
		return $con->ejecutarConsulta($consulta);
	}
	
	function actualizaCategoriaProducto($idProducto)
	{
		global $con;
		$consulta="select idCategoria from 9101_CatalogoProducto WHERE idProducto=".$idProducto;
		$idCategoria=$con->obtenerValor($consulta);
		$consulta="SELECT cveCategoria FROM 9030_categoriasObjetoGasto WHERE idCategoria=".$idCategoria;
		$cveCategoria=$con->obtenerValor($consulta);
		if($cveCategoria=="")
			$cveCategoria="-1";
		$consulta="SELECT a.cveAlmacen FROM 9030_almacenVSCategoria al,9030_almacenes a WHERE a.idAlmacen=al.idAlmacen AND al.idCategoria=".$idCategoria;
		$cveAlmacen=$con->obtenerValor($consulta);
		if($cveAlmacen=="")
			$cveAlmacen="-1";
		$consulta="UPDATE 9101_CatalogoProducto SET idAlmacen='".$cveAlmacen."',cve_grupo='".$cveCategoria."' WHERE idProducto=".$idProducto;
		return $con->ejecutarConsulta($consulta);
	}
	
	function generarFolioProtocoInvestigacion($idFormulario,$idRegistro)//1 Antes de registrar,2 Una vez registrado
	{
		global $con;
		$consulta="SELECT txtNoSiguiente FROM _913_tablaDinamica";
		$nConsecutivo=$con->obtenerValor($consulta);
		if($nConsecutivo=="")
			$nConsecutivo=1;

		$consulta="SELECT FechainicioPro,fechatermino,nVersion,cmbDeptoProtocolo FROM _278_tablaDinamica WHERE id__278_tablaDinamica=".$idRegistro;
		$filaProto=$con->obtenerPrimeraFila($consulta);
		
		$consulta="SELECT txtCveDepto FROM _912_tablaDinamica WHERE cmbDepto='".$filaProto[3]."'";
		$cveDepto=$con->obtenerValor($consulta);
		$codigo=$cveDepto."-".$nConsecutivo."-".date("y",strtotime($filaProto[0]))."/".date("y",strtotime($filaProto[1]))."-".number_format($filaProto[2]);
		$nConsecutivo++;
		$consulta="update _913_tablaDinamica set txtNoSiguiente=".$nConsecutivo;
		eC($consulta);
		return $codigo;
	}
	
	function esConvocatoriaLicitacionPublicada($idProceso,$idFormulario,$idRegistro)
	{
		global $con;
		$idFormularioBase=obtenerFormularioBase($idProceso);
		$consulta="select idEstado from _".$idFormularioBase."_tablaDinamica where id__".$idFormularioBase."_tablaDinamica=".$idRegistro;
		$idEstado=$con->obtenerValor($consulta);
		if($idEstado>=2)
			return false;
		return true;
	}
	
	function esConvocatoriaAsignacionPartida($idProceso,$idFormulario,$idRegistro)
	{
		global $con;
		$idFormularioBase=obtenerFormularioBase($idProceso);
		$consulta="select idEstado from _".$idFormularioBase."_tablaDinamica where id__".$idFormularioBase."_tablaDinamica=".$idRegistro;
		$idEstado=$con->obtenerValor($consulta);
		if($idEstado>=3)
			return false;
		return true;
	}
	function esConvocatoriaPublicable($idProceso,$idFormulario,$idRegistro)
	{
		global $con;
		$idFormularioBase=obtenerFormularioBase($idProceso);
		if($con->existeCampo("publicaConvocatoria","_".$idFormularioBase."_tablaDinamica" ))
		{
			$consulta="select publicaConvocatoria from _".$idFormularioBase."_tablaDinamica where id__".$idFormularioBase."_tablaDinamica=".$idRegistro;
			$publicaConvocatoria=$con->obtenerValor($consulta);
			if($publicaConvocatoria==1)
				return false;
			return true;
		}
	}
	
	function registrarAfectacionExistenciaAlmacen($idAlmacen,$idProducto,$cantidad,$tipoOperacion,$nOperacion,$comentario)
	{
		global $con;
		$consulta="INSERT INTO 9302_existenciaAlmacen(idAlmacen,idProducto,cantidad,fechaMovimiento,operacion,responsable,tipoMovimiento,complementario2)
				VALUES(".$idAlmacen.",".$idProducto.",".$cantidad.",'".date("Y-m-d")."',".$tipoOperacion.",".$_SESSION["idUsr"].",".$nOperacion.",'".$comentario."')";
		return $con->ejecutarConsulta($consulta);	
	}
	
	function requiereDictamenNOUtilidad($idProceso,$idFormulario,$idRegistro)
	{
		global $con;
		$idEstado="-1";
		$idFormularioBase=obtenerFormularioBase($idProceso);
		$existeRegistro=false;
		$consulta="SELECT id__921_tablaDinamica from _921_tablaDinamica WHERE idReferencia=".$idRegistro;
		$fila=$con->obtenerPrimeraFila($consulta);
		if(!$fila)
		{
			$consulta="select idEstado from _".$idFormularioBase."_tablaDinamica where id__".$idFormularioBase."_tablaDinamica =".$idRegistro;
			$idEstado=$con->obtenerValor($consulta);
		}
		else
			$existeRegistro=true;
		if(($idEstado=="2")||($idEstado=="2.1")||($idEstado=="2.2")||($existeRegistro))
		{
			return false;
		}
		return true;
	}
	
	function requiereRecuperacionPartes($idProceso,$idFormulario,$idRegistro)
	{
		global $con;
		$existeRegistro=false;
		$consulta="SELECT subOpcionesNU from _921_tablaDinamica WHERE idReferencia=".$idRegistro;
		$nOpcion=$con->obtenerValor($consulta);
		if($nOpcion=="")
		{
			$consulta="SELECT id__930_tablaDinamica from _930_tablaDinamica WHERE idReferencia=".$idRegistro;
			$fila=$con->obtenerPrimeraFila($consulta);
			if($fila)
				$existeRegistro=true;
		}
		if(($nOpcion==5)||($nOpcion==7)||($existeRegistro))
		{
			return false;
		}
		return true;
	}
	
	
	function elegirEtapaProceso($idProceso,$idFormulario,$idRegistro)
	{
		global $con;
		$consulta="select * from _".$idFormulario."_tablaDinamica where id__".$idFormulario."_tablaDinamica=".$idRegistro;
		$fila=$con->obtenerPrimeraFila($consulta);
		$idEtapa=3;
		if($fila[12]=='2013')
		{
			$grupoBien=$fila[16];
			$consulta="SELECT comite FROM _602_tablaDinamica WHERE id__602_tablaDinamica=".$grupoBien;
			$comite=$con->obtenerValor($consulta);
			switch($comite)
			{
				case 25:
					$idEtapa="2";
				break;
				case 26:
					$idEtapa="2.1";
				break;
				case 27:
					$idEtapa="2.2";
				break;
			}
		}
		return cambiarEtapaFormulario($idFormulario,$idRegistro,$idEtapa);
	}
	
	function registrarMovimientoBien($idProceso,$idFormulario,$idRegistro)
	{
		global $con;
		$consulta="select * from _".$idFormulario."_tablaDinamica where id__".$idFormulario."_tablaDinamica=".$idRegistro;
		$fila=$con->obtenerPrimeraFila($consulta);
		$x=0;
		$query[$x]="begin";
		$x++;
		if($idFormulario!=931)
		{
			
			$tipoTraslado=$fila[10];
			$fechaTemp=$fila[11];
			if($tipoTraslado==2)
			{
				$comentarios="Traslado temporal hasta el día ".date("d/m/Y",strtotime($fechaTemp));
			}
			$destinoTraslado=$fila[12];
			$comentarios="";
			$consulta="select codigoControl from 9309_ubicacionesFisicas WHERE idAreaFisica=".$destinoTraslado;
	
			$destinoTraslado=$con->obtenerValor($consulta);
			
			$consulta="SELECT * FROM _921_tablaDinamica WHERE  idReferencia=".$idRegistro;
	
			$filaDNU=$con->obtenerPrimeraFila($consulta);
			if($filaDNU)
			{
				$query[$x]="update 9307_inventario SET situacion=0,valorActualBien=0,idFormularioBaja=".$idFormulario.",idRegistroBaja=".$idRegistro.",
							idResponsableModif=".$_SESSION["idUsr"].",fechaModif='".date("Y-m-d")."' where idInventario=".$fila[15];
				$x++;
				$comentarios="Baja del activo";
			}
			
			$query[$x]="update 9308_historialTraslados set situacion=0 where idInventario=".$fila[15];
			$x++;
			$consulta="select idResponsable from 9308_historialTraslados where idInventario=".$fila[15]." and situacion=1";
			$idResponsable=$con->obtenerValor($consulta);
			if($idResponsable=="")
				$idResponsable=703585;
			$query[$x]="INSERT INTO 9308_historialTraslados(idInventario,idResponsable,fechaMovimiento,comentarios,ubicacion,situacion,idFormularioRef,idRegistroRef)
						VALUES(".$fila[15].",".$idResponsable.",'".date("Y-m-d")."','".$comentarios."','".$destinoTraslado."',1,".$idFormulario.",".$idRegistro.")";
			$x++;
		}
		else
		{
			$consulta="select * from 9308_historialTraslados where idInventario=".$fila[12]." and situacion=1";
			$fInventario=$con->obtenerPrimeraFila($consulta);

			$query[$x]="update 9308_historialTraslados set situacion=0 where idInventario=".$fila[12];
			$x++;
			if($fila[6]!=6)
			{
				$comentarios="Salida del instituto";
				$consulta="SELECT tipoBien FROM 9307_inventario WHERE idInventario=".$fila[12];
				$tBien=$con->obtenerValor($consulta);
				if($tBien==2)
				{
					$query[$x]="update 9307_inventario set situacion=0,idFormularioBaja=".$idFormulario.",idRegistroBaja=".$idRegistro." where idInventario=".$fila[12];
					$x++;
				}
				else
				{
					cambiarEtapaFormulario($idFormulario,$idRegistro,5);
				}
				if($fila[16]=="0")
				{
					$comentarios.=", Salida definitiva";
					
				}
				else
				{
					$comentarios.=", fecha estimada de regreso: ".date("d/m/Y",strtotime($fila[15]));
				}
			
			}
			else
			{
				$comentarios="Ingreso al instituto";
			}
			$query[$x]="INSERT INTO 9308_historialTraslados(idInventario,idResponsable,fechaMovimiento,comentarios,ubicacion,situacion,idFormularioRef,idRegistroRef)
						VALUES(".$fila[12].",".$fInventario[2].",'".date("Y-m-d")."','".$comentarios."','".$fInventario[5]."',1,".$idFormulario.",".$idRegistro.")";
			$x++;
		}
		
		$query[$x]="commit";
		$x++;

		return $con->ejecutarBloque($query);
	}
	
	function registrarInventarioNoInstituto($idFormulario,$idRegistro)
	{
		global $con;
		$query="select * from _".$idFormulario."_tablaDinamica where id__".$idFormulario."_tablaDinamica=".$idRegistro;
		$filaFolio=$con->obtenerPrimeraFila($query);
		$x=0;
		$consulta[$x]="begin";
		$x++;
		$consulta[$x]="INSERT INTO 9307_inventario(noInv,marca,modelo,numSerie,nombreProducto,idResponsable,fechaRegistro,tipoBien,situacion,idProveedor) 
						VALUES('".$filaFolio[9]."','".$filaFolio[12]."','".cv($filaFolio[13])."','".cv($filaFolio[14])."','".cv($filaFolio[11])."',".$filaFolio[3].",'".
						date("Y-m-d",strtotime($filaFolio[2]))."',2,1,-1)";
		$x++;
		$consulta[$x]="set @idInventario:=(select last_insert_id())";
		$x++;
		if($filaFolio[17]=="")
			$filaFolio[17]="NULL";
		$consulta[$x]="INSERT INTO 9308_historialTraslados(idInventario,idResponsable,fechaMovimiento,comentarios,ubicacion,situacion,idFormularioRef,
					idRegistroRef,tipoResponsable)	VALUES(@idInventario,".$filaFolio[17].",'".date("Y-m-d")."','".cv($filaFolio[15])."','".$filaFolio[21].
					"',1,'".$idFormulario."',".$idRegistro.",".($filaFolio[16]-1).")";
		$x++;
		$consulta[$x]="commit";
		$x++;
		return $con->ejecutarBloque($consulta);

	}
	
	function modificarInventarioNoInstituto($idFormulario,$idRegistro)
	{
		global $con;
		$consulta="select * from _".$idFormulario."_tablaDinamica where id__".$idFormulario."_tablaDinamica=".$idRegistro;
		$fila=$con->obtenerPrimeraFila($consulta);
		
		
	}

	function validarCicloActivo($idRegistro)
	{
		global $con;
		$consulta="SELECT situacion FROM 4526_ciclosEscolares WHERE idCiclo=".$idRegistro;
		$situacion=$con->obtenerValor($consulta);
		$x=0;
		$query[$x]="begin";
		$x++;
		if($situacion==1)
		{
			$query[$x]="update 4526_ciclosEscolares set situacion=2";
			$x++;
			$query[$x]="update 4526_ciclosEscolares set situacion=1 where idCiclo=".$idRegistro;
			$x++;
		}
		$query[$x]="commit";
		$x++;
		return $con->ejecutarBloque($query);

	}
	
	function bajaProfesor($idFormulario,$idRegistro)
	{
		global $con;
		$consulta="select * from _".$idFormulario."_tablaDinamica where id__".$idFormulario."_tablaDinamica=".$idRegistro;
		$fila=$con->obtenerPrimeraFila($consulta);
		$x=0;
		$query[$x]="begin";
		$x++;
		
		if($idFormulario==447)  //Baja profesor/Suplencia
		{
			$consulta="SELECT idOpcion FROM _447_otrasMateriasBaja WHERE idPadre=".$idRegistro;
			$listGrupos=$con->ObtenerListaValores($consulta);
			if($listGrupos=="")
				$listGrupos=$fila[14];
			else
				$listGrupos.=",".$fila[14];
			
			switch($fila[12])
			{
				case 5:
					$arrGrupos=explode(",",$listGrupos);
					foreach($arrGrupos as $g)
					{
						$consulta="SELECT idGrupo,idUsuario,g.idInstanciaPlanEstudio FROM 4519_asignacionProfesorGrupo a,4520_grupos g WHERE g.idGrupos=a.idGrupo and idAsignacionProfesorGrupo=".$g;
						$fGpo=$con->obtenerPrimeraFila($consulta);
						$idGrupo=$fGpo[0];
						$idProfesor=$fGpo[1];
						$idInstanciaPlan=$fGpo[2];
						$query[$x]="INSERT INTO 4519_asignacionProfesorGrupo(idGrupo,idUsuario,idParticipacion,esperaContrato,situacion,idFormularioAccion,idRegistroAccion,participacionPrincipal,fechaAsignacion,fechaBaja) 
									VALUES(".$idGrupo.",0,45,1,5,".$idFormulario.",".$idRegistro.",0,'".$fila[10]."','".date("Y-m-d",strtotime("-1 days",strtotime($fila[10])))."')";
						$x++;
						$consulta="select idMateria,idCiclo,Plantel,idPeriodo,idInstanciaPlanEstudio from 4520_grupos where idGrupos=".$fGpo[0];
						$fGrupo=$con->obtenerPrimeraFila($consulta);
						
						if($fGrupo[0]<0)
						{
							$consulta="select idGrupos FROM 4520_grupos WHERE idGrupoAgrupador=".$fGpo[0];
							$resGpo=$con->obtenerFilas($consulta);
							while($filaGpo=mysql_fetch_row($resGpo))
							{
								
								$query[$x]="INSERT INTO 4519_asignacionProfesorGrupo(idGrupo,idUsuario,idParticipacion,esperaContrato,situacion,idFormularioAccion,idRegistroAccion,participacionPrincipal,fechaAsignacion,fechaBaja) 
									VALUES(".$filaGpo[0].",0,37,1,5,".$idFormulario.",".$idRegistro.",0,'".$fila[10]."','".date("Y-m-d",strtotime("-1 days",strtotime($fila[10])))."')";
								$x++;
							}
						}
						
						
					}
				break;
				default: //Baja de suplencia
					$arrGrupos=explode(",",$listGrupos);
					foreach($arrGrupos as $g)
					{
						$consulta="SELECT idGrupo,idUsuario,g.idInstanciaPlanEstudio FROM 4519_asignacionProfesorGrupo a,4520_grupos g WHERE g.idGrupos=a.idGrupo and idAsignacionProfesorGrupo=".$g;

						$fGpo=$con->obtenerPrimeraFila($consulta);
						if($fGpo)
						{
							$idGrupo=$fGpo[0];
							$idProfesor=$fGpo[1];
							$idInstanciaPlan=$fGpo[2];
							
							$consulta="select idMateria,idCiclo,Plantel,idPeriodo,idInstanciaPlanEstudio from 4520_grupos where idGrupos=".$fGpo[0];
							$fGrupo=$con->obtenerPrimeraFila($consulta);
							
							
							$objDatos='{"idProfesorSuplencia":"'.$idProfesor.'","idFormulario":"'.$idFormulario.'","idRegistro":"'.$idRegistro.'"}';
							$folioAME=generarFolioAME($idGrupo);
							$query[$x]="INSERT INTO 4548_solicitudesMovimientoGrupo(fechaSolicitud,responsableSolicitud,idInstanciaPlan,tipoSolicitud,datosSolicitud,
										situacion,idGrupo,folio,idAsignacion) VALUES('".date("Y-m-d H:i")."',".$_SESSION["idUsr"].",".$idInstanciaPlan.",2,'".cv($objDatos)."',5,".$idGrupo.",'".$folioAME."',".$g.")";
							$x++;
							
							
							$consulta="select idMateria,idCiclo,Plantel,idPeriodo,idInstanciaPlanEstudio from 4520_grupos where idGrupos=".$fGpo[0];
							$fGrupo=$con->obtenerPrimeraFila($consulta);
							$listGrupos=$g;
							if($fGrupo[0]<0)
							{
								$consulta="select idGrupos FROM 4520_grupos WHERE idGrupoAgrupador=".$fGpo[0];
								$listaGrupos=$con->obtenerListaValores($query);
								if($listaGrupos=="")
									$listaGrupos=-1;
								$consulta="select idAsignacionProfesorGrupo from 4519_asignacionProfesorGrupo where idGrupo in (".$listaGrupos.")  and situacion=1 and participacionPrincipal=1";
								$listaProf=$con->obtenerListaValores($query);
								if($listaProf!="")
								{
									if($listGrupos=="")
										$listGrupos=$listaProf;
									else
										$listGrupos.=",".$listaProf;
								}
								
							}
						}
					}
					$query[$x]="UPDATE 4519_asignacionProfesorGrupo SET situacion=60,idFormularioAccion=".$idFormulario." , idRegistroAccion=".$idRegistro." WHERE idAsignacionProfesorGrupo in (".$listGrupos.")";
					$x++;
				break;
			}
		}
		else
		{
			$consulta="SELECT idGrupo,idUsuario,g.idInstanciaPlanEstudio FROM 4519_asignacionProfesorGrupo a,4520_grupos g 
						WHERE g.idGrupos=a.idGrupo and idAsignacionProfesorGrupo=".$fila[13];
			$fGpo=$con->obtenerPrimeraFila($consulta);
			$idGrupo=$fGpo[0];
			$idProfesor=$fGpo[1];
			$idInstanciaPlan=$fGpo[2];
			$listGrupos=$fila[13];
			$consulta="select idMateria,idCiclo,Plantel,idPeriodo,idInstanciaPlanEstudio from 4520_grupos where idGrupos=".$fGpo[0];
			$fGrupo=$con->obtenerPrimeraFila($consulta);

			if($fGrupo[0]<0)
			{
				$consulta="select idGrupos FROM 4520_grupos WHERE idGrupoAgrupador=".$fGpo[0];
				$listadoGrupos=$con->obtenerListaValores($query);
				if($listadoGrupos=="")
					$listadoGrupos=-1;
				$consulta="select idAsignacionProfesorGrupo from 4519_asignacionProfesorGrupo where idGrupo in (".$listadoGrupos.") and situacion=1 and idParticipacion=45";
				$listaProf=$con->obtenerListaValores($query);
				if($listaProf!="")
				{
					if($listGrupos=="")
						$listGrupos=$listaProf;
					else
						$listGrupos.=",".$listaProf;
				}
			}
			$query[$x]="UPDATE 4519_asignacionProfesorGrupo SET situacion=6 WHERE idAsignacionProfesorGrupo in (".$listGrupos.")";
			$x++;
			$objDatos='{"idProfesorSuplencia":"'.$idProfesor.'","idFormulario":"'.$idFormulario.'","idRegistro":"'.$idRegistro.'"}';
			$folioAME=generarFolioAME($idGrupo);
			$query[$x]="INSERT INTO 4548_solicitudesMovimientoGrupo(fechaSolicitud,responsableSolicitud,idInstanciaPlan,tipoSolicitud,datosSolicitud,
						situacion,idGrupo,folio,idAsignacion) VALUES('".date("Y-m-d H:i")."',".$_SESSION["idUsr"].",".$idInstanciaPlan.",5,'".cv($objDatos).
						"',1,".$idGrupo.",'".$folioAME."',".$fila[13].")";
			$x++;	
		}
		$query[$x]="commit";
		$x++;

		return $con->ejecutarBloque($query);
	}
	
	function crearCargaEvaluacionDocenteAdministrativa($idProceso,$idFormulario,$actor,$idReferencia)
	{
		global $con;
		$consulta="SELECT codigoInstitucion,ciclo FROM _440_tablaDinamica WHERE  id__440_tablaDinamica=".$idReferencia;
		$fila=$con->obtenerPrimeraFila($consulta);
		$x=0;
		$consulta="INSERT INTO _435_tablaDinamica(idReferencia,fechaCreacion,responsable,idEstado,codigoUnidad,codigoInstitucion,plantel)
					VALUES(".$idReferencia.",'".date("Y-m-d")."',".$_SESSION["idUsr"].",1,'".$_SESSION["codigoUnidad"]."','".$_SESSION["codigoInstitucion"]."','".$fila[0]."')";
		if($con->ejecutarConsulta($consulta))
		{
			$idRegistro=$con->obtenerUltimoID();
			$query[$x]="begin";
			$x++;
			if($fila)
			{
				$query[$x]="update _435_tablaDinamica set codigo='".$idRegistro."' where id__435_tablaDinamica=".$idRegistro;
				$x++;
				$query[$x]="INSERT INTO _437_tablaDinamica(idReferencia,fechaCreacion,responsable,idEstado,codigoUnidad,codigoInstitucion)
								VALUES(".$idRegistro.",'".date("Y-m-d")."',".$_SESSION["idUsr"].",1,'".$_SESSION["codigoUnidad"]."','".$_SESSION["codigoInstitucion"]."')";
				$x++;		
				$query[$x]="set @idRegistroAcademica:=(select last_insert_id())";
				$x++;
				$query[$x]="INSERT INTO _438_tablaDinamica(idReferencia,fechaCreacion,responsable,idEstado,codigoUnidad,codigoInstitucion)
								VALUES(".$idRegistro.",'".date("Y-m-d")."',".$_SESSION["idUsr"].",1,'".$_SESSION["codigoUnidad"]."','".$_SESSION["codigoInstitucion"]."')";
				$x++;		
				$query[$x]="set @idRegistroSE:=(select last_insert_id())";
				$x++;
				$query[$x]="INSERT INTO _439_tablaDinamica(idReferencia,fechaCreacion,responsable,idEstado,codigoUnidad,codigoInstitucion)
								VALUES(".$idRegistro.",'".date("Y-m-d")."',".$_SESSION["idUsr"].",1,'".$_SESSION["codigoUnidad"]."','".$_SESSION["codigoInstitucion"]."')";
				$x++;		
				$query[$x]="set @idRegistroFinanzas:=(select last_insert_id())";
				$x++;
				$consulta="SELECT DISTINCT a.idUsuario FROM 4520_grupos g,4519_asignacionProfesorGrupo a WHERE Plantel='".$fila[0]."' AND idCiclo='".$fila[1]."' 
							AND a.idGrupo=g.idGrupos AND a.situacion=1 AND participacionPrincipal=1";
				$res=$con->obtenerFilas($consulta);
				while($filaRes=mysql_fetch_row($res))
				{
					$query[$x]="INSERT INTO _437_dtgAcademica(idReferencia,nombreDocente) VALUES(@idRegistroAcademica,".$filaRes[0].")";
					$x++;
					$query[$x]="INSERT INTO _438_dtgServiciosEscolares(idReferencia,nombreProfesor) VALUES(@idRegistroSE,".$filaRes[0].")";
					$x++;
					$query[$x]="INSERT INTO _439_dtgEvaluacionFinanzas(idReferencia,nombreProfesor) VALUES(@idRegistroFinanzas,".$filaRes[0].")";
					$x++;
				}
							
			}
			$query[$x]="commit";
			$x++;
			if($con->ejecutarBloque($query))
				echo "1|".$idRegistro;
		}

		
	}
	
	function obtenerDiasTrabajados($fechaInicio,$fechaFin,$idUsuario)
	{
		global $con;
		$arrDias=$array();
		$fInicio=strtotime($fechaInicio);
		$fFin=strtotime($fFIn);
		for($x=0;$x<7;$x++)
		{
			$arrDias[$x]=0;
		}
		while($fInicio<=$fFin)
		{
			$dia=date("w");
			$arrDias[$dia]++;
			$fInicio=strtotime("+1 days",$fInicio);
		}
		
		$totalDias=0;
		$consulta="SELECT distinct h.dia FROM 4519_asignacionProfesorGrupo a,4520_grupos g,4522_horarioGrupo h 
						WHERE g.idGrupos=a.idGrupo AND h.idGrupo=a.idGrupo AND idUsuario=".$idUsuario." AND participacionPrincipal=1 and
						((g.fechaInicio<='".$fechaInicio."' AND g.fechaFin>='".$fechaInicio."')||(g.fechaInicio<='".$fechaFin."' AND g.fechaFin>='".$fechaFin."')||(g.fechaInicio>='".$fechaInicio."' AND g.fechaFin<='".$fechaFin."'))
						 order by horaInicio";
		$resDias=$con->obtenerFilas($consulta);
		$arrDiasProfesor=array();
		while($fila=mysql_fetch_row($resDias))
		{
			$arrDiasProfesor[$fila[0]]=1;
		}
		
		$consulta="SELECT distinct h.dia FROM 4519_asignacionProfesorGrupo a,4520_grupos g,4522_horarioGrupo h 
						WHERE g.idGrupos=a.idGrupo AND h.idGrupo=a.idGrupo AND idUsuario=".$idUsuario." AND participacionPrincipal=0 and
						((g.fechaInicio<='".$fechaInicio."' AND g.fechaFin>='".$fechaInicio."')||(g.fechaInicio<='".$fechaFin."' AND g.fechaFin>='".$fechaFin."')||(g.fechaInicio>='".$fechaInicio."' AND g.fechaFin<='".$fechaFin."'))
						 order by horaInicio";
		
		
	}
	
	function actualizarSituacionPedidoFactura($idFormulario,$idRegistro)
	{
		global $con;
		$consulta="select idReferencia from _".$idFormulario."_tablaDinamica where id__".$idFormulario."_tablaDinamica=".$idRegistro;
		$idReferencia=$con->obtenerValor($consulta);
		$consulta="UPDATE _960_tablaDinamica SET idEstado=2 WHERE noPedido=".$idReferencia;
		if($con->ejecutarConsulta($consulta))
		{
			echo "window.parent.recargarGrid();window.parent.cerrarVentanaFancy();return;";
		}
	}
	
	function tieneFuncionExclusion($idProceso,$idFormulario,$idRegistro)
	{
		global $con;
		return !existeRol("'93_0'");
	}
	
	
	function enviarCorreoCensida()
	{
		global $con;
		$arrMail[0]='cuarejhy@hotmail.com';
		$arrMail[1]='luis@jovenesconliderazgo.org';
		$arrMail[2]='angelliz_4@hotmail.com';
		$arrMail[3]='julyan61@hotmail.com';
		$arrMail[4]='elizabeth@intermedios.org.mx';
		$arrMail[5]='sihua_ac@hotmail.com';
		$arrMail[6]='rodrigo.rincon@codise.org.mx';
		$arrMail[7]='eugenia@redbalance.org';
		$arrMail[8]='jose28_su@hotmail.com';
		$arrMail[9]='eramos58_luisa@hotmail.com';
		$arrMail[10]='acasida@prodigy.net.mx';
		$arrMail[11]='ilse_koala@hotmail.com';
		$arrMail[12]='daniel.gyca@gmail.com';
		$arrMail[13]='molkomate@hotmail.com';
		$arrMail[14]='frosas22@prodigy.net.mx';
		$arrMail[15]='antonionevarez@gmail.com';
		$arrMail[16]='puertoseguroasociacioncivil@gmail.com';
		$arrMail[17]='mariagil1102@hotmail.com';
		$arrMail[18]='davidpicacho@hotmail.com';
		$arrMail[19]='proyectoscecash@yahoo.com.mx';
		$arrMail[20]='matetera_ac@hotmail.com';
		$arrMail[21]='acodemis@prodigy.net.mx';
		$arrMail[22]='aproase@yahoo.com';
		$arrMail[23]='neliabm@yahoo.com.mx';
		$arrMail[24]='cheetos72@hotmail.com';
		$arrMail[25]='vosabi@gmail.com';
		$arrMail[26]='gabriela@redbalance.org';
		$arrMail[27]='itzamnaac@yahoo.com.mx';
		$arrMail[28]='vicfroy_81@hotmail.com';
		$arrMail[29]='florfunlidem@yahoo.com.mx';
		$arrMail[30]='jessicarigel@intermedios.org.mx';
		$arrMail[31]='funsevida@gmail.com';
		$arrMail[32]='aquesex@hotmail.com';
		$arrMail[33]='administracion@seedssa.org.mx';
		$arrMail[34]='cesarcoria@hotmail.com';
		$arrMail[35]='sidamujeres@sipam.org.mx';
		$arrMail[36]='gestionfondos@seedssa.org.mx';
		$arrMail[37]='rafael.didesex@gmail.com';
		$arrMail[38]='veronica.olicon@yaaxil.org.mx';
		$arrMail[39]='andrezdelrio@live.com.mx';
		$arrMail[40]='mario3105@hotmail.com';
		$arrMail[41]='ciudadaniapositiva@hotmail.com';
		$arrMail[42]='yosoyelotro1@gmail.com';
		$arrMail[43]='juansil709@hotmail.com';
		$arrMail[44]='jgtabasco2010@hotmail.com';
		$arrMail[45]='meggan09@hotmail.com';
		$arrMail[46]='mujerestrans@hotmail.com';
		$arrMail[47]='lsantos@mexfam.org.mx';
		$arrMail[48]='emartine@cisc.org.mx';
		$arrMail[49]='nitram@cappsida.org.mx';
		$arrMail[50]='direccion@cumy.org.mx';
		$arrMail[51]='seducoy@gmail.com';
		$arrMail[52]='fatima@fatimaibp.org';
		$arrMail[53]='equilibrioenelaire@yahoo.com.mx';
		$arrMail[54]='corral.miguel@yahoo.com.mx';
		$arrMail[55]='rodezt@gmail.com';
		$arrMail[56]='ixtliyollotl@yahoo.com.mx';
		$arrMail[57]='pro-mo-gen@hotmail.com';
		$arrMail[58]='amigoscontraelsida@yahoo.com';
		$arrMail[59]='silveriobandala@hotmail.com';
		$arrMail[60]='tuyyoensinergia@yahoo.com.mx';
		$arrMail[61]='tzootz11@gmail.com';
		$arrMail[62]='gmisericordia@hotmail.com';
		$arrMail[63]='sagitariosigloxx1@hotmail.com';
		$arrMail[64]='hildae_99@yahoo.com';
		$arrMail[65]='sisex2010@hotmail.com';
		$arrMail[66]='putrid68@hotmail.com';
		$arrMail[67]='joalvigo77@hotmail.com';
		$arrMail[68]='isabel-urzua@hotmail.com';
		$arrMail[69]='democraciayuc@hotmail.com';
		$arrMail[70]='colectivoollin@hotmail.com';
		$arrMail[71]='sorel23@hotmail.com';
		$arrMail[72]='estefania.vela.barba@gmail.com';
		$arrMail[73]='coordinacion.oficina@amarcmexico.org';
		$arrMail[74]='DIVERSILESS@GMAIL.COM';
		$arrMail[75]='femess@hotmail.com';
		$arrMail[76]='actpays@gmail.com';
		$arrMail[77]='jorge68@live.com';
		
		$cuerpoMail="Estimadas y Estimados colegas de organizaciones de la sociedad civil con trabajo en VIH:<br><br>
					Les envío un saludo y el mejor deseo de felicidad, bienestar y salud en estas épocas de fin de año.<br>
					Espero que 2011 les haya sido fructífero y satisfactorio en el ámbito profesional y personal, así como para las comunidades para las que trabajamos.<br><br>
					
					Aprovecho la oportunidad para desearles el mayor de los éxitos en el año entrante y asegurándoles que en CENSIDA haremos lo posible para intensificar las acciones de colaboración con las organizaciones de la sociedad civil con trabajo en el tema, en beneficio de las personas y comunidades para las que trabajamos, objetivo común entre nosotros.<br><br>  
					
					Hemos tenido ya reuniones con el subsecretario, Dr. Pablo Kuri, tan pronto regresé de la participación de la junta de coordinación del ONUSIDA, y ahora les confirmo que haremos lo que esté a nuestro alcance para mejorar la respuesta nacional al VIH en nuestro país, respuesta de la que la sociedad civil es parte fundamental.<br><br>
					
					Les deseo un exitoso 2012 y que obtengan la felicidad y bienestar que tanto merecen ustedes y sus seres queridos.<br><br>
					
					Sinceramente,<bR><bR>
					
					Jose Antonio Izazola <br><br>
					
					
					Centro Nacional para la Prevención y el Control del VIH/SIDA<br>
					CENSIDA
					";
		$mail="";
		
		foreach($arrMail as $mail)			
		{
			
			var_dump(enviarMail($mail,"CENSIDA",$cuerpoMail,"proyectos@grupolatis.net","Censida",NULL));
			
		}
		
	}
	
	function esProcesoPublicableConvocatoria($idProceso,$idFormulario,$idRegistro)
	{
		global $con;
		$consulta="";
		$idFormularioBase=obtenerFormularioBase($idProceso);
		switch($idFormularioBase)
		{
			case 356:
				$consulta="SELECT publicaConvocatoria FROM _356_tablaDinamica WHERE id__356_tablaDinamica=".$idRegistro;
			break;
			case 699:
				$consulta="SELECT cmbSiNo FROM _699_tablaDinamica WHERE id__699_tablaDinamica=".$idRegistro;
			break;
		}
		
		$res=$con->obtenerValor($consulta);
		if($res=="1")
			return false;
		return true;
			
	}
	
	function recalcularFechaDiaNoHabilAutorizado($idFormulario,$idRegistro)
	{
		global $con;
		$consulta="SELECT fechaInicio,fechaFin,codigoInstitucion,recalcular FROM _465_tablaDinamica WHERE id__465_tablaDinamica=".$idRegistro;
		$fDatos=$con->obtenerPrimeraFila($consulta);
		if($fDatos[3]==0)
			return true;
		return recalcularFechasPlantel($fDatos[2],$fDatos[0],$fDatos[1]);
	}
	
	function validarChoqueProfesores()
	{
		global $con;
		$consulta="SELECT DISTINCT idUsuario FROM 4519_asignacionProfesorGrupo ORDER BY idUsuario";
		$res=$con->obtenerFilas($consulta);
		$arrGrupos=array();
		while($fila=mysql_fetch_row($res))
		{
			$arrGrupos=array();
			$arrChoques=array();
			$consulta="SELECT g.idGrupos,fechaInicio,fechaFin FROM 4519_asignacionProfesorGrupo a,4520_grupos g WHERE idUsuario=".$fila[0]." AND g.idGrupos=a.idGrupo AND g.situacion=1";

			$resGrupos=$con->obtenerFilas($consulta);
			while($fGrupo=mysql_fetch_row($resGrupos))
			{
				$obj["idGrupo"]=$fGrupo[0];
				$obj["fechaInicio"]=($fGrupo[1]);
				$obj["fechaFin"]=($fGrupo[2]);
				$obj["horario"]=array();
				$consulta="SELECT dia,horaInicio,horaFin FROM 4522_horarioGrupo WHERE idGrupo=".$fGrupo[0];
				$resH=$con->obtenerFilas($consulta);
				while($fHorario=mysql_fetch_row($resH))
				{
					$h["dia"]=$fHorario[0];
					$h["hInicio"]=($fHorario[1]);
					$h["hFin"]=($fHorario[2]);
					array_push($obj["horario"],$h);
				}
				array_push($arrGrupos,$obj);
				
			}
			for($x=0;$x<sizeof($arrGrupos);$x++)
			{
				$gBase=$arrGrupos[$x];
				$colisiona=false;
				for($ct=($x+1);$ct<sizeof($arrGrupos);$ct++)
				{
					$gCompara=$arrGrupos[$ct];
					if(colisionaTiempo($gBase["fechaInicio"],$gBase["fechaFin"],$gCompara["fechaInicio"],$gCompara["fechaFin"]))
					{
						foreach($gBase["horario"] as $hBase)
						{
							foreach($gCompara["horario"] as $hCompara)
							{
								if($hBase["dia"]==$hCompara["dia"])
								{
									if(colisionaTiempo($hBase["hInicio"],$hBase["hFin"],$hCompara["hInicio"],$hCompara["hFin"]))
									{
										$choque=$gBase["idGrupo"].",".$gCompara["idGrupo"];
										array_push($arrChoques,$choque);
										$colisiona=true;
									}
								}
								if($colisiona)
									break;
							}
							if($colisiona)
								break;
						}
					}
					
					
					
				}
				

			}
			if(sizeof($arrChoques>0))
			{
				$pos=0;
				$query[$pos]="begin";
				$pos;
				foreach($arrChoques as $c)
				{
					$query[$pos]="INSERT INTO 1000_choquesGrupos(idUsuario,idGrupos) VALUES(".$fila[0].",'".$c."')";
					$pos++;
				}
				$query[$pos]="commit";
				$pos++;
				if(!$con->ejecutarBloque($query))
					return;
				
			}
			
		}

	}
	
	function actualizarDatosRevisor($idFormulario,$idRegistro)
	{
		global $con;
		$consulta="select * from _".$idFormulario."_tablaDinamica where id__".$idFormulario."_tablaDinamica=".$idRegistro;
		$fila=$con->obtenerPrimeraFila($consulta);
		$x=0;
		$query[$x]="begin";
		$x++;
		$query[$x]="UPDATE 802_identifica SET Paterno='".cv($fila[11])."',Materno='".cv($fila[12])."',Nom='".cv($fila[10])."',resenaCurricular='".cv($fila[20])."' WHERE idUsuario=".$fila[3];
		$x++;
		$query[$x]="UPDATE 801_adscripcion SET institucionAbierto='".cv($fila[13])."',puestoAbierto='".cv($fila[15])."',Dependencia='".cv($fila[14])."' WHERE idUsuario=".$fila[3];
		$x++;
		$query[$x]="DELETE FROM 805_mails WHERE idUsuario=".$fila[3];
		$x++;
		$query[$x]="INSERT INTO 805_mails(Mail,Tipo,Notificacion,idUsuario) VALUES('".cv($fila[21])."',0,1,".$fila[3].")";
		$x++;
		$query[$x]="UPDATE 803_direcciones SET Estado='".cv($fila[19])."',Pais=146 WHERE idUsuario=".$fila[3]." AND Tipo=0";
		$x++;
		$query[$x]="DELETE FROM 804_telefonos WHERE idUsuario=".$fila[3];
		$x++;
		$query[$x]="INSERT INTO 804_telefonos(Numero,Tipo,Tipo2,idUsuario) VALUES('".cv($fila[16])."',1,0,".$fila[3].")";
		$x++;
		$query[$x]="INSERT INTO 804_telefonos(Numero,Tipo,Tipo2,idUsuario) VALUES('".cv($fila[17])."',0,0,".$fila[3].")";
		$x++;
		$query[$x]="INSERT INTO 804_telefonos(Numero,Tipo,Tipo2,idUsuario) VALUES('".cv($fila[18])."',0,1,".$fila[3].")";
		$x++;
		$query[$x]="UPDATE _365_tablaDinamica SET idEstado=2 WHERE id__365_tablaDinamica=".$idRegistro;
		$x++;
		$query[$x]="commit";
		$x++;
		return $con->ejecutarBloque($query);
	}
	
	function validarCategoriaProyectoNO7_Censida($idProceso,$idFormulario,$idRegistro)
	{
		global $con;	
		
		if(existeRol("'70_0'")&&($idFormulario==297))
			return true;
		
		$idFormularioBase=obtenerFormularioBase($idProceso);
		$consulta="select nombreTabla,idFormulario from 900_formularios where idFormulario=".$idFormularioBase;
		$filaFrm=$con->obtenerPrimeraFila($consulta);
		$nTablaAux=$filaFrm[0];
		$consulta="select categorias from ".$nTablaAux." where id_".$nTablaAux."=".$idRegistro;
		$cat=$con->obtenerValor($consulta);
		if($cat!=7)
			return false;
		else
			return true;
		
		
	}
	
	function validarCategoriaProyecto_Censida($idProceso,$idFormulario,$idRegistro)
	{
		global $con;	
		$idFormularioBase=obtenerFormularioBase($idProceso);
		$consulta="select nombreTabla,idFormulario from 900_formularios where idFormulario=".$idFormularioBase;
		$filaFrm=$con->obtenerPrimeraFila($consulta);
		$nTablaAux=$filaFrm[0];
		$consulta="select categorias from ".$nTablaAux." where id_".$nTablaAux."=".$idRegistro;
		$cat=$con->obtenerValor($consulta);
		if($cat==7)
			return false;
		else
			return true;
		
		
	}
	
	function validarCategoriaProyecto7_Censida($idProceso,$idFormulario,$idRegistro)
	{
		global $con;	
		$idFormularioBase=obtenerFormularioBase($idProceso);
		$consulta="select nombreTabla,idFormulario from 900_formularios where idFormulario=".$idFormularioBase;
		$filaFrm=$con->obtenerPrimeraFila($consulta);
		$nTablaAux=$filaFrm[0];
		$consulta="select categorias from ".$nTablaAux." where id_".$nTablaAux."=".$idRegistro;
		$cat=$con->obtenerValor($consulta);
		if($cat==7)
			return false;
		else
			return true;
		
		
	}

	function validarMontoCensida2012($idRegistro,$idReferencia,$montoSolicitado)
	{
		global $con;	
		$monto=normalizarNumero($montoSolicitado);
		$res="1|";
		$idSubCategoria=$idReferencia;
		if($idRegistro!=-1)
		{
			$consulta="SELECT idReferencia FROM _370_tablaDinamica WHERE id__370_tablaDinamica=".$idRegistro;
			$idSubCategoria=$con->obtenerValor($consulta);	
		}
		
		
		$consulta="SELECT montoMaximoProyecto,montoMinimo FROM _369_tablaDinamica t,_369_subcategorias s WHERE t.id__369_tablaDinamica=s.idReferencia AND s.id__369_subcategorias=".$idSubCategoria;
		$fila=$con->obtenerPrimeraFila($consulta);
		
		
		if($fila)
		{
			
			if($monto<$fila[1])
			{
				$res="El monto solicitado es <b>menor</b> que el monto m&iacute;nimo permitido de acuerdo a la categor&iacute;a ($ ".number_format($fila[1],2,".",",").")|presupuestoSolicitado";
			}
			if($monto>$fila[0])
			{
				$res="El monto solicitado es <b>mayor</b> que el monto m&aacute;ximo permitido de acuerdo a la categor&iacute;a ($ ".number_format($fila[0],2,".",",").")|presupuestoSolicitado";
			}
		}
		return $res;
	}
	
	function validarRangoEdadIndicadores2012($hEdadDe,$hEdadHasta,$mEdadDe,$mEdadHasta)
	{
		$res="1|";	
		if($hEdadDe>$hEdadHasta)
		{
			$res="La edad de inicio de las personas del g&eacute;nero masculino alcanzadas por el proyecto no puede ser mayor que la edad final";
		}
		
		if($mEdadDe>$mEdadHasta)
		{
			$res="La edad de inicio de las personas del g&eacute;nero femenino alcanzadas por el proyecto no puede ser mayor que la edad final";
		}
		return $res;
	}

	function validarRFC_OSC($rfc1,$rfc4,$rfc3,$idRegistro)
	{
		global $con;
		$res="1|";	
		$consulta="SELECT organizacion FROM _367_tablaDinamica WHERE ((rfc1='".$rfc1."' AND rfc4='".$rfc4."' AND rfc3='".$rfc3."') or (rfc1Respaldo='".$rfc1."' AND 
				rfc2Respaldo='".$rfc4."' AND rfc3Respaldo='".$rfc3."')) and id__367_tablaDinamica<>".$idRegistro;
		$nReg=$con->obtenerValor($consulta);
		if($nReg!="")
		{
			$res="El RFC ingresado ya ha sido registrado previamente, la organizaci&oacute;n asociada a dicho RFC es <b>".$nReg."</b>, si lo desea puede enviar un comentario a soporte para aclarar sus dudas";
		}
		
		echo $res;
	}
	
	function actualizarDatosOSC($idFormulario,$idRegistro)
	{
		global $con;
		$x=0;
		$nUnidad="";
		$query[$x]="begin";
		$x++;
		$consulta="select * from _".$idFormulario."_tablaDinamica where id__".$idFormulario."_tablaDinamica=".$idRegistro;
		$fRegistro=$con->obtenerPrimeraFila($consulta);
		$consulta="select codigoUnidad,idOrganigrama from 817_organigrama where codigoUnidad='".$fRegistro[8]."'";
		$fInstitucion=$con->obtenerPrimeraFila($consulta);
		$consulta="SELECT entidadFederativa,municipio,CP FROM _378_tablaDinamica WHERE idReferencia=".$idRegistro;
		$fDireccion=$con->obtenerPrimeraFila($consulta);
		$codigoUnidad=$fInstitucion[0];

		$homo=$fRegistro[12].$fRegistro[29];
		$folio=substr($fDireccion[0],0,4);
		$folio=$folio*1;
		$folio=str_pad($folio,2,"0",STR_PAD_LEFT);
		$folio.="-CEN-".$homo."-".$idRegistro;
		
		if($codigoUnidad=="")
		{
			$consulta="SELECT MAX(codigoUnidad) FROM 817_organigrama WHERE institucion=1 AND instColaboradora=1";
			$codigoUnidad=$con->obtenerValor($consulta);
			$codigoUnidad=($codigoUnidad*1)+1;
			
			$codigoUnidad=str_pad($codigoUnidad,4,"0",STR_PAD_LEFT);
			$query[$x]="INSERT INTO 817_organigrama(unidad,codigoFuncional,codigoUnidad,institucion,STATUS,instColaboradora,sigla,codigoInstitucion)
						VALUES('".cv($fRegistro[10])."','".$codigoUnidad."','".$codigoUnidad."',1,1,1,'".$fRegistro[30]."','".$folio."')";
			$x++;
			$query[$x]="set @idRegistro:=(select last_insert_id())";
			$x++;
			$query[$x]="INSERT INTO 247_instituciones(idOrganigrama,email,idPais,estado,municipio,cp) VALUES(@idRegistro,'".$fRegistro[26]."',146,'".$fDireccion[0]."','".$fDireccion[1]."','".$fDireccion[2]."')";
			$x++;
			$query[$x]="update  _".$idFormulario."_tablaDinamica set codigoInstitucion='".$codigoUnidad."' where id__".$idFormulario."_tablaDinamica=".$idRegistro;
			$x++;
			
			$query[$x]="update _".$idFormulario."_tablaDinamica set codigo='".$folio."' where id__".$idFormulario."_tablaDinamica=".$idRegistro;
			$x++;
		}
		else
		{
			$query[$x]="update 817_organigrama set unidad='".cv($fRegistro[10])."',sigla='".$fRegistro[30]."' where codigoUnidad='".$codigoUnidad."'";

			$x++;
			$query[$x]="update 247_instituciones set email='".$fRegistro[26]."',idPais=146,estado='".$fDireccion[0]."',municipio='".$fDireccion[1]."',cp='".$fDireccion[2]."' where idOrganigrama=".$fInstitucion[1];
			$x++;
			if(strpos($fRegistro[9],"-")===false)
			{
				$query[$x]="update 817_organigrama set codigoInstitucion='".cv($folio)."' where codigoUnidad='".$codigoUnidad."'";

				$x++;
				$query[$x]="update _".$idFormulario."_tablaDinamica set codigo='".$folio."' where id__".$idFormulario."_tablaDinamica=".$idRegistro;
				$x++;
			}
		}
		
		$query[$x]="UPDATE 801_adscripcion SET Institucion='".$codigoUnidad."' WHERE Institucion='".$fRegistro[8]."'";
		$x++;
		
		$query[$x]="UPDATE _498_tablaDinamica SET codigoInstitucion='".$codigoUnidad."',codigoUnidad='".$codigoUnidad."' WHERE codigoInstitucion='".$fRegistro[8]."'";
		$x++;
		
		$query[$x]="UPDATE _485_tablaDinamica SET codigoInstitucion='".$codigoUnidad."',codigoUnidad='".$codigoUnidad."' WHERE codigoInstitucion='".$fRegistro[8]."'";
		$x++;
		
		
		$query[$x]="UPDATE _466_tablaDinamica SET codigoInstitucion='".$codigoUnidad."',codigoUnidad='".$codigoUnidad."' WHERE codigoInstitucion='".$fRegistro[8]."'";
		$x++;
		$query[$x]="UPDATE _378_tablaDinamica SET codigoInstitucion='".$codigoUnidad."',codigoUnidad='".$codigoUnidad."' WHERE codigoInstitucion='".$fRegistro[8]."'";
		$x++;
		$query[$x]="UPDATE _407_tablaDinamica SET codigoInstitucion='".$codigoUnidad."',codigoUnidad='".$codigoUnidad."' WHERE codigoInstitucion='".$fRegistro[8]."'";
		$x++;
		$query[$x]="UPDATE _381_tablaDinamica SET codigoInstitucion='".$codigoUnidad."',codigoUnidad='".$codigoUnidad."' WHERE codigoInstitucion='".$fRegistro[8]."'";
		$x++;
		$query[$x]="UPDATE _379_tablaDinamica SET codigoInstitucion='".$codigoUnidad."',codigoUnidad='".$codigoUnidad."' WHERE codigoInstitucion='".$fRegistro[8]."'";
		$x++;
		$query[$x]="UPDATE _394_tablaDinamica SET codigoInstitucion='".$codigoUnidad."',codigoUnidad='".$codigoUnidad."' WHERE codigoInstitucion='".$fRegistro[8]."'";
		$x++;
		
		
		$query[$x]="commit";
		$x++;
		if($con->ejecutarBloque($query))
		{
			$_SESSION["codigoInstitucion"]=$codigoUnidad;
			$arrParam=array();
			$arrParam["idRegistro"]=$idRegistro;

			enviarMensajeEnvio(5,$arrParam);
		}
	}


	function asignarRevisoresProyectosEval()
	{
		global $con;
		$consulta="SELECT id__370_tablaDinamica FROM _370_tablaDinamica WHERE idEstado=2 
					AND id__370_tablaDinamica NOT IN (SELECT DISTINCT idProyecto FROM 1011_asignacionRevisoresProyectos) and id__370_tablaDinamica>12 ORDER BY id__370_tablaDinamica";
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			if(!asignarRevisorProyectos($fila[0]))
				return;
		}
	}
	function asignarRevisorProyectos($idProyecto)	
	{
		global $con;
		//SELECT idUsuario FROM 1011_asignacionRevisoresProyectos WHERE idFormulario=370 AND idProyecto=
		$consulta="SELECT idReferencia FROM _370_tablaDinamica WHERE id__370_tablaDinamica=".$idProyecto;
		$idSubcategoria=$con->obtenerValor($consulta);
		$consulta="SELECT id__369_tablaDinamica FROM _369_tablaDinamica t,_369_subcategorias s WHERE t.id__369_tablaDinamica=s.idReferencia AND s.id__369_subcategorias=".$idSubcategoria;	
		$idCategoria=$con->obtenerValor($consulta);
		$consulta="SELECT idUsuario FROM 1010_configuracionRevisores WHERE idCategoria LIKE '%".$idCategoria."%' AND numProyAsignados<num_Max AND tipo_Revisor=2
 					ORDER BY numProyAsignados asc LIMIT 0,1";
		$arrRevisores=array();
		$fUsuario=$con->obtenerPrimeraFila($consulta);
		if($fUsuario)
		{
			$o[0]=$fUsuario[0];
			$o[1]=2;
			array_push($arrRevisores,$o);
		}
		$nRevisores=3-sizeof($arrRevisores);
		$consulta="SELECT idUsuario FROM 1010_configuracionRevisores WHERE idCategoria LIKE '%".$idCategoria."%' AND numProyAsignados<num_Max AND tipo_Revisor=1
 					ORDER BY numProyAsignados asc LIMIT 0,".$nRevisores;
		$res=$con->obtenerFilas($consulta);
		while($fUsuario=mysql_fetch_row($res))
		{
			$o[0]=$fUsuario[0];
			$o[1]=1;
			array_push($arrRevisores,$o);
		}
		$x=0;
		if(sizeof($arrRevisores)!=3)
		{
			echo "No cumple 3 revisores: Proyecto ".$idProyecto;
			return;
		}
		$query[$x]="begin";
		$x++;
		foreach($arrRevisores as $r)
		{
			$query[$x]="INSERT INTO 1011_asignacionRevisoresProyectos(idUsuario,idCategoria,idProyecto,tipoRevisor,idFormulario,situacion,fechaAsignacion)
						VALUES(".$r[0].",".$idCategoria.",".$idProyecto.",".$r[1].",370,1,'".date("Y-m-d H:i")."')";
			$x++;
			$query[$x]="UPDATE 1010_configuracionRevisores SET numProyAsignados=numProyAsignados+1 WHERE idUsuario=".$r[0]." AND idFormulario=370";
			$x++;
		}
		
		$query[$x]="commit";
		$x++;
		return $con->ejecutarBloque($query);
	}
	
	
	function redistribuirCarga()
	{
		global $con;
		$x=0;
		$finalizar=false;
		while(!$finalizar)
		{
			$consulta="SELECT * FROM (SELECT idUsuario,(SELECT COUNT(*) FROM 1011_asignacionRevisoresProyectos WHERE idUsuario=c.idUsuario AND idCategoria<>7 AND situacion=1)  AS proy
						FROM 1010_configuracionRevisores c WHERE idUsuario IN(3,285,462,473,294,295,300,582,296,297,301)) AS tmp WHERE proy>7 ORDER BY proy desc";
			$resEval=$con->obtenerFilas($consulta);
			if($con->filasAfectadas==0)
			{
				$finalizar=true;
				continue;
			}
			while($fEval=mysql_fetch_row($resEval))
			{
				$consulta="SELECT  idUsuario FROM 1010_configuracionRevisores WHERE tipo_Revisor=2 AND num_Max>numProyAsignados AND idUsuario<>283 and idCategoria<>'6' limit 0,1";

				$res=$con->obtenerFilas($consulta);
				if($con->filasAfectadas==0)
				{
					$finalizar=true;
					break;
				}
				while($fila=mysql_fetch_row($res))
				{

					$consulta="SELECT idProyecto FROM 1011_asignacionRevisoresProyectos WHERE idUsuario=".$fila[0];
					$listProy=$con->obtenerListaValores($consulta);
					if($listProy=="")
						$listProy=-1;
					$consulta="SELECT DISTINCT idReferencia2 FROM 9053_resultadoCuestionario where responsableRegistro=".$fEval[0];
					$listProy2=$con->obtenerListaValores($consulta);
					if($listProy2!="")
					{
						if($listProy=="")
							$listProy=$listProy2;
						else
							$listProy.=",".$listProy2;
					}
					
					if($listProy=="")
						$listProy=-1;
					
					$consulta="SELECT idProyecto,idUsuarioVSProyecto FROM 1011_asignacionRevisoresProyectos WHERE idUsuario=".$fEval[0]." AND situacion=1 AND 
								idProyecto NOT IN (".$listProy.") and idCategoria<>7 LIMIT 0,1";

					$fProy=$con->obtenerPrimeraFila($consulta);	
					$x=0;
					$query=array();
					$query[$x]="begin";
					$x++;							
					$query[$x]="UPDATE 1011_asignacionRevisoresProyectos SET idUsuario=".$fila[0]." WHERE idUsuarioVSProyecto=".$fProy[1];
					$x++;
					$query[$x]="UPDATE 1010_configuracionRevisores SET numProyAsignados=numProyAsignados+1 WHERE idUsuario=".$fila[0];
					$x++;
					$query[$x]="UPDATE 1010_configuracionRevisores SET numProyAsignados=numProyAsignados-1 WHERE idUsuario=".$fEval[1];
					$x++;
					$query[$x]="commit";
					$x++;
					if(!$con->ejecutarBloque($query))
						return;
				}
			}
		}
		
		
	}
	
	function generarRetroActivoNomina($idNomina)
	{
		global $con;
		$controlLatis=true;
		$libro=new cExcel("retroactivo.xls",false);
		$consulta="SELECT codigoUnidad,unidad FROM 817_organigrama WHERE unidadPadre='0001' AND codigoUnidad NOT IN ('00010001','00010008','00010011') order by unidad";
		$res=$con->obtenerFilas($consulta);
		$nFila=1;
		while($fila=mysql_fetch_row($res))
		{
			$libro->setValor("A".$nFila,$fila[1]);
			$libro->setNegritas("A".$nFila);
			$nFila++;
			$consulta="SELECT idGrupos FROM 4520_grupos WHERE Plantel='".$fila[0]."'";
			$listGrupos=$con->obtenerListaValores($consulta);
			$consulta="SELECT DISTINCT c.idUsuario,CONCAT(Paterno,' ',Materno,' ',Nom) AS nombre FROM 4556_costoHoraDocentes c,802_identifica u WHERE idNomina=".$idNomina." and idGrupo IN (".$listGrupos.")  AND 
					u.idUsuario=c.idUsuario order by Paterno,Materno,Nom";
			$resUsr=$con->obtenerFilas($consulta);
			while($fUsr=mysql_fetch_row($resUsr))
			{
				$libro->setValor("B".$nFila,$fUsr[1]);
				$nFila++;
				$consulta="SELECT c.idGrupo,c.horas,c.costoHora, g.nombreGrupo FROM 4556_costoHoraDocentes c,4520_grupos g WHERE idNomina=".$idNomina." AND idGrupo IN (".$listGrupos.") AND idUsuario=".$fUsr[0]." 
						AND calculo='totalPercepcion' AND g.idGrupos=c.idGrupo order by g.nombreGrupo";
				$resMat=$con->obtenerFilas($consulta);
				$libro->setValor("D".$nFila,"Grupo");
				$libro->setValor("E".$nFila,"Horas a pagar");
				$libro->setValor("F".$nFila,"Horas pagadas");
				$libro->setValor("G".$nFila,"Diferencia");
				$libro->setValor("H".$nFila,"Costo");
				$libro->setValor("I".$nFila,"Total");
				$libro->setNegritas("D".$nFila.":I".$nFila);
				
				$libro->setAnchoColumna("D","auto");
				$libro->setAnchoColumna("E","auto");
				$libro->setAnchoColumna("F","auto");
				$libro->setAnchoColumna("G","auto");
				$libro->setAnchoColumna("H","auto");
				$libro->setAnchoColumna("I","auto");
				$libro->setHAlineacion("D".$nFila.":I".$nFila,"C");
				
				
				
				$nFila++;
				$nUsuario=0;
				$totalRetro=0;
				while($fMat=mysql_fetch_row($resMat))
				{
					$consulta="SELECT sum(c.horas) FROM 4556_costoHoraDocentes c WHERE idNomina=".$idNomina." AND idGrupo =".$fMat[0]." AND idUsuario=".$fUsr[0]." 
						AND calculo='ObtenerImporteFalta'";
					$nFaltas=$con->obtenerValor($consulta);
					if($nFaltas=="")
						$nFaltas=0;
					$nHoras	=$fMat[1]-$nFaltas;
					$consulta="SELECT c.horas FROM 4556_costoHoraDocentes_2 c WHERE idNomina=".$idNomina." AND idGrupo =".$fMat[0]." AND idUsuario=".$fUsr[0]." 
						AND calculo='totalPercepcion'";	
					$horaP=$con->obtenerValor($consulta);
					$consulta="SELECT c.horas FROM 4556_costoHoraDocentes_2 c WHERE idNomina=".$idNomina." AND idGrupo =".$fMat[0]." AND idUsuario=".$fUsr[0]." 
						AND calculo='ObtenerImporteFalta'";	
					$hFaltasP=$con->obtenerValor($consulta);
					$nHorasPagadas=	$horaP-$hFaltasP;
					$diferencia=$nHoras-$nHorasPagadas;
					if($diferencia>0)
					{
						//$libro->setValor("D".$nFila,$fMat[3]);
						//$libro->setValor("E".$nFila,$nHoras);
						//$libro->setValor("F".$nFila,$nHorasPagadas);
						//$libro->setValor("G".$nFila,$diferencia);
						//$libro->setValor("H".$nFila,$fMat[2]);
						$total=$diferencia*$fMat[2];
						$totalRetro+=$total;
						//$libro->setValor("I".$nFila,$total);
						$nUsuario++;
					}
						
				}
				if($nUsuario==0)
				{
					$nFila-=2;
					$libro->removerFila($nFila,2);
				}
				else
				{
					$libro->setValor("I".$nFila,"Total retroactivo: ");
					$libro->setNegritas("I".$nFila,$nFila);
					$libro->setValor("J".$nFila,$totalRetro);
					

					if($controlLatis)
					{
						$libro->setValor("K".$nFila,$totalRetro);
						$libro->setValor("L".$nFila,"'".$fila[0]."'");
						$libro->setValor("M".$nFila,$fUsr[0]);
					}
					$nFila++;
				}

			}
		}
		$libro->generarArchivo("Excel5");
		
	}
	
	function asignarProyectosMiembrosSubMesa($idFormulario,$idRegistro)
	{
		global $con;
		
		$consulta="select idReferencia from _391_tablaDinamica where id__391_tablaDinamica=".$idRegistro;
		$idRegistro=$con->obtenerValor($consulta);
		$x=0;
		
		
		$query[$x]="begin";
		$x++;
		$consulta="SELECT miembro FROM _391_gridMiembroSubMesa g,_391_tablaDinamica c WHERE g.idReferencia=c.id__391_tablaDinamica AND c.idReferencia=".$idRegistro;
		$resM=$con->obtenerFilas($consulta);
		while($filaM=mysql_fetch_row($resM))
		{
			$consulta="select count(*) from 807_usuariosVSRoles WHERE idUsuario=".$filaM[0]." AND codigoRol='10_0'";
			$nRol=$con->obtenerValor($consulta);
			if($nRol==0)
			{
				$query[$x]="INSERT INTO 807_usuariosVSRoles(idUsuario,idRol,idExtensionRol,codigoRol) VALUES(".$filaM[0].",10,0,'10_0')";
				$x++;
			}
			$consulta="SELECT proyectos FROM _391_gridProyectos g,_391_tablaDinamica c WHERE g.idReferencia=c.id__391_tablaDinamica AND c.idReferencia=".$idRegistro;
			$res=$con->obtenerFilas($consulta);
			while($fila=mysql_fetch_row($res))
			{
				$consulta="select count(*) from 1011_asignacionRevisoresProyectos where idFormulario=370 and idUsuario=".$filaM[0]." and idProyecto=".$fila[0];
				$nAsig=$con->obtenerValor($consulta);
				if($nAsig==0)
				{
					$consulta="SELECT c.id__369_tablaDinamica FROM   _370_tablaDinamica t,_369_tablaDinamica c,_369_subcategorias s
								WHERE c.id__369_tablaDinamica=s.idReferencia AND s.id__369_subcategorias=t.idReferencia AND t.id__370_tablaDinamica=".$fila[0];
					$idCategoria=$con->obtenerValor($consulta);
					
					$query[$x]="INSERT INTO 1011_asignacionRevisoresProyectos(idUsuario,idCategoria,idProyecto,tipoRevisor,idFormulario,situacion,fechaAsignacion)
								VALUES(".$filaM[0].",".$idCategoria.",".$fila[0].",5,370,1,'".date("Y-m-d H:i")."')";
					$x++;
				}
			}
		}
		$query[$x]="commit";
		$x++;
		eB($query);
		

	}
	
	function clonarCuestionario($idRegistroCuestionario,$idProyecto,$idUsuarioAsignar,$clonar)
	{
		global $con;
		$x=0;
		$query="SELECT c.id__369_tablaDinamica FROM   _370_tablaDinamica t,_369_tablaDinamica c,_369_subcategorias s
								WHERE c.id__369_tablaDinamica=s.idReferencia AND s.id__369_subcategorias=t.idReferencia AND t.id__370_tablaDinamica=".$idProyecto;
		$idCategoria=$con->obtenerValor($query);
		$consulta[$x]="begin";
		$x++;
		if($clonar)
		{
			$consulta[$x]="INSERT INTO 9053_resultadoCuestionario(idCuestionario,idReferencia1,idReferencia2,fechaRegistro,responsableRegistro,
						calificacionFinal,dictamen) SELECT idCuestionario,idReferencia1,idReferencia2,'2012-04-03 07:30:00',".$idUsuarioAsignar.",calificacionFinal,dictamen 
						FROM 9053_resultadoCuestionario WHERE idRegistroCuestionario=".$idRegistroCuestionario;
			$x++;
			$consulta[$x]="set @idRegistro:=(select last_insert_id())";
			$x++;
			$consulta[$x]="INSERT INTO 9054_respuestasCuestionario(idReferencia,idElemento,valorRespuesta,idRespuesta)
							SELECT @idRegistro,idElemento,valorRespuesta,idRespuesta FROM 9054_respuestasCuestionario WHERE idReferencia=".$idRegistroCuestionario;
			$x++;
			$consulta[$x]="INSERT INTO 1011_asignacionRevisoresProyectos(idUsuario,idCategoria,idProyecto,tipoRevisor,idFormulario,situacion,fechaAsignacion,fechaEvaluacion,idReferencia,idCuestionarioEval,tipoCuestionario)
								VALUES(".$idUsuarioAsignar.",".$idCategoria.",".$idProyecto.",7,370,2,'".date("Y-m-d H:i")."','".date("Y-m-d H:i")."',@idRegistro,1,1)";
			$x++;
		}
		else
		{
			$consulta[$x]="INSERT INTO 1011_asignacionRevisoresProyectos(idUsuario,idCategoria,idProyecto,tipoRevisor,idFormulario,situacion,fechaAsignacion)
						VALUES(".$idUsuarioAsignar.",".$idCategoria.",".$idProyecto.",6,370,1,'".date("Y-m-d H:i")."')";
			$x++;
		}
		
		
		$consulta[$x]="commit";
		$x++;
		return $con->ejecutarBloque($consulta);
	}
	
	function generarClonacionCuestionario()
	{
		global $con;
		
		$query="SELECT idProyect,comite FROM 1010_proyectosDiferenciaComite where idProyect in (181,235,89,100)";
		$res=$con->obtenerFilas($query);
		$ct=1;
		while($fila=mysql_fetch_row($res))
		{
			
			$idComite=$fila[1];
			$consulta="SELECT idReferencia,idUsuario,situacion FROM 1011_asignacionRevisoresProyectos WHERE idFormulario=370 AND idProyecto=".$fila[0]." ORDER BY idUsuarioVSProyecto LIMIT 3,1 ";
			$fRevision=$con->obtenerPrimeraFila($consulta);
			if($fRevision[2]<>2)
			{
				echo $ct."- Proyecto: ".$fila[0]." no evaluado<br>";
				$ct++;
				continue;
				
				
			}
			$query="SELECT idUsuario FROM 1010_miembrosComite WHERE idComite=".$idComite." AND idUsuario<>".$fRevision[1];
			$resUsr=$con->obtenerFilas($query);
			while($fUsr=mysql_fetch_row($resUsr))
			{
				$consulta="select count(*) from 1011_asignacionRevisoresProyectos where idFormulario=370 and idUsuario=".$fUsr[0]." and idProyecto=".$fRevision[0];
				$nAsig=$con->obtenerValor($consulta);
				if($nAsig==0)
				{
					
					if(!clonarCuestionario($fRevision[0],$fila[0],$fUsr[0],true))
						return;
						
				}
				
			}
		}
	}
	
	function asociarCategoriasConcepto($idRegistro,$objCategorias)
	{
		global $con;
		$x=0;
		$consulta[$x]="begin";
		$x++;
		$consulta[$x]="DELETE FROM 564_conceptosVSCategorias WHERE idConcepto=".$idRegistro;
		$x++;

		$obj=json_decode(bD($objCategorias));
		
		if(sizeof($obj->arrCategorias)>0)
		{
			foreach($obj->arrCategorias as $cat)
			{
				$consulta[$x]="INSERT INTO 564_conceptosVSCategorias(idConcepto,idCategoria) VALUES(".$idRegistro.",".$cat->idCategoria.")";
				$x++;
			}
		}
		$consulta[$x]="DELETE FROM 564_conceptosVSPlanPago WHERE idConcepto=".$idRegistro;
		$x++;
		if(sizeof($obj->arrPlanPagos)>0)
		{
			foreach($obj->arrPlanPagos as $plan)
			{
				$consulta[$x]="INSERT INTO 564_conceptosVSPlanPago(idConcepto,idPlanPago,noPago,idConceptoAsociado) VALUES(".$idRegistro.",".$plan->idPlanPagos.",".$plan->noPago.",".$plan->conceptoAsociado.")";
				$x++;
			}
		}
		$consulta[$x]="DELETE FROM 565_configuracionColumnaOperacion WHERE idOperacion IN (SELECT idOperacion FROM 564_conceptosVSOperacionesCargosDescuentos WHERE idConcepto=".$idRegistro.")";
		$x++;
		$consulta[$x]="DELETE FROM 564_conceptosVSOperacionesCargosDescuentos WHERE idConcepto=".$idRegistro;
		$x++;

		if(sizeof($obj->arrOperaciones)>0)
		{
			foreach($obj->arrOperaciones as $op)
			{
				$oConf=json_decode(bD($op->configuracionComp));
				if($op->idOperacion!=-1)
				{
					$consulta[$x]="INSERT INTO 564_conceptosVSOperacionesCargosDescuentos(idOperacion,ordenAplicacion,idTipoOperacion,etiquetaOperacion,origenValor,valor,funcionAplicacion,calcularInterfaceCosto,valorReferencia,idConcepto)
									VALUES(".$op->idOperacion.",".$op->ordenAplicacion.",".$op->idTipoOperacion.",'".cv($op->etiquetaOperacion)."',".$op->origenValor.",".$op->valor.",".$op->funcionAplicacion.",".
									$oConf->calcularInterfaceCosto.",".$oConf->valorReferencia.",".$idRegistro.")";
					$x++;
					$consulta[$x]="set @idOperacion:=".$op->idOperacion;
					$x++;
				}
				else
				{
					$consulta[$x]="INSERT INTO 564_conceptosVSOperacionesCargosDescuentos(ordenAplicacion,idTipoOperacion,etiquetaOperacion,origenValor,valor,funcionAplicacion,calcularInterfaceCosto,valorReferencia,idConcepto)
									VALUES(".$op->ordenAplicacion.",".$op->idTipoOperacion.",'".cv($op->etiquetaOperacion)."',".$op->origenValor.",".$op->valor.",".$op->funcionAplicacion.",".
									$oConf->calcularInterfaceCosto.",".$oConf->valorReferencia.",".$idRegistro.")";
					$x++;
					$consulta[$x]="set @idOperacion:=(select last_insert_id())";
					$x++;
				}
				if(sizeof($oConf->arrColumnas)>0)
				{
					foreach($oConf->arrColumnas as $c)
					{
						if($c->idColumna!=-1)
						{
							$consulta[$x]="INSERT INTO 565_configuracionColumnaOperacion(idColumna,idOperacion,etiqueta,anchoColumna,tipoValor)
											VALUES(".$c->idColumna.",@idOperacion,'".cv($c->etiqueta)."',".$c->anchoColumna.",".$c->tipoValor.")";
							$x++;
						}
						else
						{
							$consulta[$x]="INSERT INTO 565_configuracionColumnaOperacion(idOperacion,etiqueta,anchoColumna,tipoValor)
											VALUES(@idOperacion,'".cv($c->etiqueta)."',".$c->anchoColumna.",".$c->tipoValor.")";
							$x++;
						}
					}
				}
			}
		}
		
		if(sizeof($obj->arrConfiguracionPlan)>0)
		{
			$consulta[$x]="DELETE FROM 6023_planesPagosConceptoPlanteles WHERE idConcepto=".$idRegistro;
			$x++;
			foreach($obj->arrConfiguracionPlan as $p)
			{
				foreach($p->planteles as $objPlantel)
				{
					if($objPlantel->aplicaPlantel==1)
					{
						if($objPlantel->valorReferencia=="")
							$objPlantel->valorReferencia="NULL";
						if($objPlantel->tipoValorReferencia=="")
							$objPlantel->tipoValorReferencia="NULL";
								
						$consulta[$x]="INSERT INTO 6023_planesPagosConceptoPlanteles(idPlanPago,idConcepto,plantel,aplicaPlantel,valorReferencia,tipoValorReferencia)
										VALUES(".$p->idPlanPagos.",".$idRegistro.",'".$objPlantel->plantel."',".$objPlantel->aplicaPlantel.",".$objPlantel->valorReferencia.",".$objPlantel->tipoValorReferencia.")";
						$x++;	
					}
				}
			}
		}
		$consulta[$x]="commit";
		$x++;
		
		return $con->ejecutarBloque($consulta);
	}
	
	function comprometerPresupuestoProtocolo($idProtocolo)
	{
		global $con;
		$arrRubros=array();
		$consulta="SELECT * FROM 100_gridPresupuesto WHERE idFormulario=278 AND idReferencia=".$idProtocolo;
		$res=$con->obtenerFilas($consulta);
		
		$arrAsientos=array();
		$c=new cContabilidad();
		$consulta="SELECT cmbDeptoProtocolo FROM _278_tablaDinamica WHERE id__278_tablaDinamica=".$idProtocolo;
		$codDepto=$con->obtenerValor($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$cadObj='{"idFormulario":"278","idReferencia":"'.$idProtocolo.'","tipoMovimiento":"23","montoOperacion":"'.$fila[6].'","dimensiones":{"IDDepartamento":"'.$codDepto.'","IDProyecto":"'.$idProtocolo.'","IDRubro":"'.$fila[7].'","IDConcepto":"'.$fila[0].'"}}';
			$obj=json_decode($cadObj);
			array_push($arrAsientos,$obj);
		}

		return $c->asentarArregloMovimientos($arrAsientos);
			
		
	}
	
	function generarFolioContratoProtocolo($idFormulario,$idRegistro)
	{
		global $con;
		$consulta="SELECT ciclo FROM 550_cicloFiscal WHERE STATUS=1";
		$ciclo=$con->obtenerValor($consulta);
		$ciclo=substr($ciclo,2,2);
		$consulta="SELECT idProyecto FROM _988_tablaDinamica WHERE id__988_tablaDinamica=".$idRegistro;
		$idProtocolo=$con->obtenerValor($consulta);
		$consulta="select codigo FROM _278_tablaDinamica WHERE id__278_tablaDinamica=".$idProtocolo;
		$codigoProyecto=$con->obtenerValor($consulta);
		$query="begin";
		if($con->ejecutarConsulta($query))
		{
			$query="SELECT folioActual FROM _993_tablaDinamica FOR update";
			$folioActual=$con->obtenerValor($query);
			$query="update  _993_tablaDinamica set folioActual=folioActual+1";
			if($con->ejecutarConsulta($query))
			{
				$query="commit";
				if($con->ejecutarConsulta($query))
				{
					$folio="INCMNSZ/".$folioActual."/3/".$codigoProyecto."/".$ciclo;
					return $folio;
				}
			}
		}
		return $idRegistro;
	}
	
	function importarUsuarioSistema()
	{
		global $con;
		$fecha=date("Y-m-d");
		$consulImportacion="SELECT * FROM 800_importacionUsuarios ORDER BY registro";
		$res=$con->obtenerFilas($consulImportacion);
		$x=0;
		$consulta[$x]="begin";
		$x++;
		
		while($fila=mysql_fetch_row($res))
		{
			$nombre=$fila[2]." ".$fila[3]." ".$fila[4];
			$rol=$fila[26]."_0";
			$consulta[$x]="INSERT INTO 	800_usuarios(STATUS,Nombre,FechaActualiza,cambiarDatosUsr)VALUES('1','".$nombre."','".$fecha."','2')";
			$x++;
			$consulta[$x]="set @idUsuario=(select last_insert_id())";
			$x++;
			$consulta[$x]="UPDATE 800_usuarios SET 	Login=@idUsuario,PASSWORD=@idUsuario WHERE idUsuario=@idUsuario";
			$x++;
			$consulta[$x]="INSERT INTO 801_adscripcion(Institucion,cod_Puesto,STATUS,idUsuario,situacion,tipoContratacion) VALUES('".$fila[25]."',	'".$fila[26]."','1',@idUsuario,'1','".$fila[27]."')";
			$x++;
			$consulta[$x]="INSERT INTO 	801_fumpEmpleado(idUsuario,tipoOperacion,fechaAplicacion,activo,tipoContratacion)VALUES(@idUsuario,'1','".$fecha."','1','".$fila[27]."')";
			$x++;
			$consulta[$x]="INSERT INTO 	802_identifica(Nom,Paterno,Materno,Nombre,ciudadNacimiento,estadoNacimiento,paisNacimiento,Nacionalidad,RFC,fechaNacimiento,STATUS,Genero,CURP,IMSS,idUsuario)VALUES('".$fila[2]."','".$fila[3]."','".$fila[4]."','".$nombre."',
						'".$fila[5]."','".$fila[6]."','".$fila[7]."','".$fila[8]."','".$fila[9]."','".$fila[10]."','1','".$fila[11]."','".$fila[12]."','".$fila[13]."',@idUsuario)";
			$x++;
			$consulta[$x]="INSERT INTO 803_direcciones(Tipo,Calle,Numero,Colonia,Ciudad,CP,Estado,Pais,idUsuario)VALUES('0','".$fila[15]."','".$fila[16]."','".$fila[17]."','".$fila[18]."','".$fila[19]."','".$fila[20]."','".$fila[21]."',@idUsuario)";
			$x++;
			$consulta[$x]="INSERT INTO 	803_direcciones(Tipo,idUsuario)VALUES('1',@idUsuario)";
			$x++;
			$consulta[$x]="INSERT INTO 	804_telefonos(idUsuario)VALUES(@idUsuario)";
			$x++;
			$consulta[$x]="INSERT INTO 	805_mails(Mail,Tipo,Notificacion,idUsuario)VALUES('".$fila[24]."','0','1',@idUsuario)";
			$x++;
			$consulta[$x]="INSERT INTO 	806_fotos(idUsuario)VALUES(@idUsuario)";
			$x++;
			$consulta[$x]="INSERT INTO 	807_usuariosVSRoles(idUsuario,idRol,idExtensionRol,codigoRol)VALUES(@idUsuario,'".$fila[26]."','0','".$rol."')";
			$x++;
			$consulta[$x]="commit";
			$x++;
			$con->ejecutarBloque($consulta);
		}
		
		echo "Proceso terminado";
	}
	
	function permitirObservarPresupuesto($idProceso,$idFormulario,$idRegistro)
	{
		
		if(existeRol("'70_0'"))
			return true;
	}
	
	function datosGeneralesGuardados($idFormulario,$idRegistro)
	{
		global $con;
		$x=0;
		$consulta[$x]="begin";
		$x++;
		$query="select * from _".$idFormulario."_tablaDinamica where id__".$idFormulario."_tablaDinamica=".$idRegistro;
		$fRegistro=$con->obtenerPrimeraFila($query);
		$idReferencia=$fRegistro[1];
		$idEstado=$fRegistro[6];
		$idProceso="115";
		$nuevoReg="false";
		if($fRegistro[23]=="")
		{
			$nuevoReg="true";
			$nombreC=trim($fRegistro[10]).' '.trim($fRegistro[11]).' '.trim($fRegistro[12]);
			$mail=$fRegistro[20];
			$status="5";
			$password=generarPassword();
			$idIdioma="1";
			
			$query="insert into 800_usuarios(Login,Status,FechaCambio,Password,Nombre,idIdioma,cuentaActiva,cambiarDatosUsr) 
						values('".cv(trim($mail))."',".$status.",'".date('Y-m-d')."','".cv($password)."','".cv($nombreC)."',".$idIdioma.",1,2)";
			if(!$con->ejecutarConsulta($query))
			{
				return false;
			}
			$idUsuario=$con->obtenerUltimoID();
			$codInstitucion=$_SESSION["codigoInstitucion"];
			
			$idInstancia=$fRegistro[26];		
			$idPeriodo=$fRegistro[28];		
			$idCiclo=$fRegistro[27];		

			$consulta[$x]="insert into 805_mails(Mail,Tipo,Notificacion,idUsuario) values('".cv(trim($mail))."',0,1,".$idUsuario.")";
			$x++;
			$consulta[$x]="insert into 807_usuariosVSRoles(idUsuario,idRol,idExtensionRol,codigoRol) values(".$idUsuario.",-1000,0,'-1000_0')";
			$x++;
			$query="SELECT rol FROM 9019_rolesRegistro WHERE idProceso=".$idProceso;
			$resRoles=$con->obtenerFilas($query);
			while($filaRol=mysql_fetch_row($resRoles))
			{
				$arrDatos=explode("_",$filaRol[0]);
				$consulta[$x]="insert into 807_usuariosVSRoles(idUsuario,idRol,idExtensionRol,codigoRol) values(".$idUsuario.",".$arrDatos[0].",".$arrDatos[1].",'".$filaRol[0]."')";
				$x++;
			}
			$nombre=$fRegistro[10];
			$apPaterno=$fRegistro[11];
			$apMaterno=$fRegistro[12];
			$prefijo="";
			$sexo=$fRegistro[14];
			$consulta[$x]="insert into 802_identifica(Nom,Paterno,Materno,Nombre,Status,idUsuario,Prefijo,Genero) 
						  values('".cv($nombre)."','".cv($apPaterno)."','".cv($apMaterno)."','".cv($nombreC)."',".$status.",".$idUsuario.",'".$prefijo."',".$sexo.")";
			$x++;
			$consulta[$x]="insert into 801_adscripcion(Institucion,Status,idUsuario,codigoUnidad) values('".cv($codInstitucion)."',".$status.",".$idUsuario.",'')";
			$x++;
			$consulta[$x]="insert into 803_direcciones(idUsuario,Tipo) values(".$idUsuario.",0)";
			$x++;
			$consulta[$x]="insert into 803_direcciones(idUsuario,Tipo) values(".$idUsuario.",1)";
			$x++;
			$consulta[$x]="insert into 806_fotos(idUsuario) values(".$idUsuario.")";
			$x++;
			
			$cadInscripcion='{"idInstanciaPlan":"'.$idInstancia.'","idCiclo":"'.$idCiclo.'","idPeriodo":"'.$idPeriodo.'"}';
			$consulta[$x]="INSERT INTO 4573_solicitudesInscripcion(fechaCreacion,idUsuario,datosInscripcion,idCiclo,idPeriodo,idInstancia)
							VALUE('".date("Y-m-d H:i:s")."',".$idUsuario.",'".$cadInscripcion."',".$idCiclo.",".$idPeriodo.",".$idInstancia.")";
			$x++;
			$consulta[$x]="set @idRegistroSolicitud:=(select last_insert_id())";
			$x++;
			$consulta[$x]="update _".$idFormulario."_tablaDinamica set idReferencia=@idRegistroSolicitud where id__".$idFormulario."_tablaDinamica=".$idRegistro;;
			$x++;
			$consulta[$x]="UPDATE _678_tablaDinamica SET idUsuario='".$idUsuario."' WHERE id__678_tablaDinamica='".$idRegistro."' ";
			$x++;
		}
		else
		{
			
			$idUsuario=$fRegistro[23];
			if($idUsuario=="")
			{
				$idUsuario=-1;
			}
			else
			{
				$consulta[$x]="UPDATE 802_identifica SET Nom='".$fRegistro[10]."',Paterno='".$fRegistro[11]."',Materno='".$fRegistro[12]."',Genero='".$fRegistro[14]."',fechaNacimiento='".$fRegistro[13].
							"',idSituacionFam='".$fRegistro[15]."',Nacionalidad='".$fRegistro[16]."' WHERE idUsuario=".$idUsuario;
				$x++;
				
			}
		}
		
		$consulta[$x]="DELETE FROM 804_telefonos WHERE idUsuario=".$idUsuario;
		$x++;
		
		if($fRegistro[17]!="")
		{
			$consulta[$x]="	insert into 804_telefonos(codArea,Lada,Numero,Extension,Tipo,Tipo2,idUsuario) 
						values('','','".cv($fRegistro[17])."','',0,0,".$idUsuario.")";
			$x++;
		}
		if($fRegistro[18]!="")
		{
			$consulta[$x]="	insert into 804_telefonos(codArea,Lada,Numero,Extension,Tipo,Tipo2,idUsuario) 
						values('','','".cv($fRegistro[18])."','',0,1,".$idUsuario.")";
			$x++;
		}
		$consulta[$x]="UPDATE 805_mails SET Mail='".$mail."' WHERE idUsuario=".$idUsuario;
		$x++;
		$consulta[$x]="commit";
		$x++;
		
		$funcionEjecucion="window.parent.opener.recargarSolicitudesInscripcion();window.parent.recargarProcesoActorNuevaEtapa(false,".$idRegistro.",".$nuevoReg.");return;";
		$con->ejecutarBloque($consulta);
	}
	
	function guardarDatosDomiciliarios($idFormulario,$idRegistro)
	{
		global $con;
		$query="select *  from _".$idFormulario."_tablaDinamica where id__".$idFormulario."_tablaDinamica=".$idRegistro;
		$fRegistro=$con->obtenerPrimeraFila($query);
		$idReferencia=$fRegistro[1];	
		$query="SELECT idUsuario FROM 4573_solicitudesInscripcion WHERE idSolicitudInscripcion=".$idReferencia;
		$idUsuario=$con->obtenerValor($query);
		$x=0;
		$consulta[$x]="begin";
		$x++;
		$calle=$fRegistro[12];
		$numero=$fRegistro[12];
		$colonia=$fRegistro[14];
		$ciudad=$fRegistro[15];
		$cp=$fRegistro[13];
		if($cp=="")
			$cp='NULL';
		$idPais=$fRegistro[19];
		$estado=$fRegistro[10];
		$municipio=$fRegistro[11];
		if($idPais=="")
		{
			$idPais=146;
		}
		else
		{
			$estado=$fRegistro[20];
		}
		
		$consulta[$x]="UPDATE 803_direcciones SET Calle='".$calle."',Numero='".$numero."',Colonia='".$colonia."',Ciudad='".$ciudad."',
					CP=".$cp.",Pais=".$idPais.",Estado='".$estado."',Municipio='".$municipio."'  WHERE idUsuario=".$idUsuario." AND Tipo=0";
		$consulta[$x]="commit";
		$x++;
		return $con->ejecutarBloque($consulta);
		
	}
	
	
	function guardarDocumentoUsr($idUsuario,$tipoDocumento,$idDocumento,$vDocumento)
	{
		global $con;
		$query="select idDocumentoUsr from 825_documentosUsr where idUsuario=".$idUsuario." and  tipoDocumento=".$tipoDocumento." and idDocumento=".$idDocumento;
		$idDocumentoUsr=$con->obtenerValor($query);
		if($idDocumentoUsr=="")
		{
			$query="INSERT INTO 825_documentosUsr(idUsuario,tipoDocumento,idDocumento,valorDocumento) VALUES(".$idUsuario.",".$tipoDocumento.",".$idDocumento.",".$vDocumento.")";
		}
		else
		{
			$query="update 825_documentosUsr set valorDocumento=".$vDocumento." where idDocumentoUsr=".$idDocumentoUsr;
		}
		return $con->ejecutarConsulta($query);
	}
	
	function datosGeneralesNuevo($idFormulario,$idRegistro)
	{
		global $con;
		$consulta="SELECT * FROM _678_tablaDinamica WHERE id__678_tablaDinamica=".$idRegistro;
		$fDatosBase=$con->obtenerPrimeraFila($consulta);
		$idReferencia=$fDatosBase[1];
		$consulta="update _678_tablaDinamica set idReferencia=-1,idInstanciaPlanInscribe=".$idReferencia." where id__678_tablaDinamica=".$idRegistro;
		if($con->ejecutarConsulta($consulta))
			return datosGeneralesGuardados($idFormulario,$idRegistro);
		return false;
	}
	
	function verificarCoordinadorProyecto($idFormulario,$idRegistro)
	{
		global $con;
		$consulta="SELECT codigo FROM _370_tablaDinamica WHERE coordinador=".$idRegistro;
		$listFolios=$con->obtenerListaValores($consulta,"'");
		if($listFolios!="")
			return "El coordinador se encuentra asociado a los proyectos con Folio :".$listFolios;
		return "";
	}
	
	
	function verificarRepresentanteLegal($idFormulario,$idRegistro)
	{
		global $con;
		$resultado="";
		$comp="<br><span style='color:#F00'><b>*</b></span> ";
		
		
		
		
		$consulta="SELECT * FROM _379_tablaDinamica WHERE idReferencia=".$idRegistro." AND puesto=5";
		$con->obtenerFilas($consulta);
		if($con->filasAfectadas==0)
		{
			$resultado.=$comp."Sección: Miembros de la organización.- Debe registrar al menos a un Representante Legal";
		}
		$consulta="SELECT tipoOrganizacion,cuentaCluni,CLUNI,fechaConstitucion FROM _367_tablaDinamica WHERE id__367_tablaDinamica=".$idRegistro;
		$fOrganizacion=$con->obtenerPrimeraFila($consulta);
		
		if($fOrganizacion[3]=="")
		{
			$resultado.=$comp."Sección: Datos de la organización.- Debe ingresar la fecha de constituci&oacute;n de su organizaci&oacute;n ";
		}
		
		if(($fOrganizacion[0]==1)||($fOrganizacion[0]==4))
		{
			if(($fOrganizacion[1]==1)&&($fOrganizacion[2]==""))
			{
				$resultado.=$comp."Sección: Datos de la Organización.- Debe ingresar el CLUNI de la Organización";
			}
			
			
			$consulta="SELECT COUNT(*) FROM _407_documentosRequeridosOSC gd,_407_tablaDinamica d WHERE gd.idReferencia=d.id__407_tablaDinamica AND d.idReferencia=".$idRegistro." AND gd.tituloDocumento=6";
			$nReg=$con->obtenerValor($consulta);
			if($nReg==0)
			{
				$resultado.=$comp."Sección: Documentos anexos.- Debe ingresar el CLUNI (Escaneado) de su organización";
			}
			
			
			$consulta="SELECT COUNT(*) FROM _407_documentosRequeridosOSC gd,_407_tablaDinamica d WHERE gd.idReferencia=d.id__407_tablaDinamica AND d.idReferencia=".$idRegistro." AND gd.tituloDocumento=7
						and gd.activo=1";
			$nReg=$con->obtenerValor($consulta);
			if($nReg==0)
			{
				$resultado.=$comp."Sección: Documentos anexos.- Debe ingresar el Comprobante de entrega del Informe anual (INDESOL) de su organización";
			}
			
			
		}
		$consulta="SELECT aPaterno,aMaterno,nombre FROM _379_tablaDinamica WHERE idReferencia=".$idRegistro." AND (credencialElector IS NULL OR folioCredencialElector='' OR folioCredencialElector IS NULL)";
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$resultado.=$comp."Sección: Miembros de la organización.- Debe ingresar el folio de la credencial de elector y/o documento escaneado del mismo del miembro ".$fila[0]." ".$fila[1]." ".$fila[2];
		}
		
		$consulta="SELECT COUNT(*) FROM _407_documentosRequeridosOSC gd,_407_tablaDinamica d WHERE gd.idReferencia=d.id__407_tablaDinamica AND d.idReferencia=".$idRegistro." AND gd.tituloDocumento=1";
		$nReg=$con->obtenerValor($consulta);
		if($nReg==0)
		{
			$resultado.=$comp."Sección: Documentos anexos.- Debe ingresar el Acta Constitutiva de su organización";
		}
		
		$consulta="SELECT COUNT(*) FROM _407_documentosRequeridosOSC gd,_407_tablaDinamica d WHERE gd.idReferencia=d.id__407_tablaDinamica AND d.idReferencia=".$idRegistro.
				" AND gd.tituloDocumento=2 and gd.activo=1";
		$nReg=$con->obtenerValor($consulta);
		if($nReg==0)
		{
			$resultado.=$comp."Sección: Documentos anexos.- Debe ingresar el Comprobante de Domicilio de su organización";
		}
		
		$consulta="SELECT COUNT(*) FROM _407_documentosRequeridosOSC gd,_407_tablaDinamica d WHERE gd.idReferencia=d.id__407_tablaDinamica AND d.idReferencia=".$idRegistro." AND gd.tituloDocumento=5";
		$nReg=$con->obtenerValor($consulta);
		if($nReg==0)
		{
			$resultado.=$comp."Sección: Documentos anexos.- Debe ingresar la Cédula Fiscal (RFC) de su organización";
		}
		
		/*$consulta="SELECT COUNT(*) FROM _407_documentosRequeridosOSC gd,_407_tablaDinamica d WHERE gd.idReferencia=d.id__407_tablaDinamica AND d.idReferencia=".$idRegistro." AND gd.tituloDocumento=8
				and gd.activo=1"; 
		$nReg=$con->obtenerValor($consulta);
		if($nReg==0)
		{
			$resultado.=$comp."Sección: Documentos anexos.- Debe ingresar el Documento vigente expedido por el SAT en el que se emite la opinión de cumplimiento de obligaciones fiscales";
		}*/
		
		$consulta="SELECT COUNT(*) FROM _407_documentosRequeridosOSC gd,_407_tablaDinamica d WHERE gd.idReferencia=d.id__407_tablaDinamica AND d.idReferencia=".$idRegistro." AND gd.tituloDocumento=9
					and gd.activo=1"; 
		$nReg=$con->obtenerValor($consulta);
		if($nReg==0)
		{
			$resultado.=$comp."Sección: Documentos anexos.- Debe ingresar la carta bajo protesta de decir verdad";
		}
		
		
		return $resultado;
	}
	
	function esWebCast($idProceso,$idFormulario,$idRegistro)
	{
		global $con;
		$consulta="SELECT modalidadCurso FROM _246_tablaDinamica WHERE id__246_tablaDinamica=".$idRegistro;
		$modalidad=$con->obtenerValor($consulta);
		if($modalidad==1)
			return true;
		return false;
	}
	
	function ocultarDetalleSubcategoria($idProceso,$idFormulario,$idRegistro)
	{
		global $con;
		$consulta="SELECT permiteTema FROM _415_tablaDinamica WHERE id__415_tablaDinamica=".$idRegistro;
		$permiteTema=$con->obtenerValor($consulta);
		if($permiteTema==1)
			return true;
		return false;
	}
	
	function ocultarDefinicionPoblacion($idProceso,$idFormulario,$idRegistro)
	{
		global $con;
		$consulta="SELECT permiteTema FROM _415_tablaDinamica WHERE id__415_tablaDinamica=".$idRegistro;
		$permiteTema=$con->obtenerValor($consulta);
		if($permiteTema==1)
			return false;
		return true;
	}
	
	function ocultarSeccionesAcademica($idProceso,$idFormulario,$idRegistro)
	{
		global $con;
		$consulta="SELECT cmbTipoInstitucion FROM _716_tablaDinamica WHERE id__716_tablaDinamica=".$idRegistro;
		$tipoInstitucion=$con->obtenerValor($consulta);
		if($tipoInstitucion==1)
			return false;
		return true;
	}
	
	function ocultarSeccionesIndustrial($idProceso,$idFormulario,$idRegistro)
	{
		global $con;
		$consulta="SELECT cmbTipoInstitucion FROM _716_tablaDinamica WHERE id__716_tablaDinamica=".$idRegistro;
		$tipoInstitucion=$con->obtenerValor($consulta);
		if($tipoInstitucion==2)
			return false;
		return true;
	}
	
	function ocultarPoblacionBlanco($idProceso,$idFormulario,$idRegistro)
	{
		global $con;
		$idFormulario=410;

		$consulta="SELECT idReferencia FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
		$idConfiguracion=$con->obtenerValor($consulta);
		$consulta="SELECT campoCategoria,campoSubcategoria,campoTema,campoTituloProyecto,calculoPresupuestoSolicitado,
					calculoPresupuestoAutorizado FROM _428_tablaDinamica WHERE idReferencia=".$idConfiguracion;
					
		$fConfiguracion=$con->obtenerPrimeraFila($consulta);
		$consulta="select ".$fConfiguracion[0].",".$fConfiguracion[1].",".$fConfiguracion[2]."  FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
		$fReg=$con->obtenerPrimeraFila($consulta);
		$consulta="SELECT COUNT(*) FROM 0_poblacionesBlancoProyectos WHERE idSubcategoria=".$fReg[1]." AND idTema=".$fReg[2];
		$nReg=$con->obtenerValor($consulta);

		if($nReg==0)
			return true;
		return false;
	}
	
	function ocultarNoSocio($idProceso,$idFormulario,$idRegistro)
	{
		global $con;
		$consulta="SELECT radSolicitudSocio,radEsSocio FROM _716_tablaDinamica WHERE id__716_tablaDinamica=".$idRegistro;
		$fBase=$con->obtenerPrimeraFila($consulta);
		if(($fBase[1]==1)||($fBase[0]==1))
		{
			return false;
		}
		return true;
		
	}
	
	function enviarCorreosRevisoresV2()
	{
		global $con;
		$prueba=false;
		$arrUsrProblemas=array();
		$arrUsrOk=array();
		$arrAchivos=array();
		/*$arrAchivos[0][0]="../recursos/GuiaEvaluacionProyectos2013.pdf";
		$arrAchivos[0][1]="Guia de evaluacion 2013.pdf";*/
		
		
		$cuerpoMail="
					<table width=\"800\"><tr><td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/logoSalud.gif\"></td>
					<td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/censidaLogo.gif\"></td></tr></table>
					<br><br><h1>Centro Nacional para La Prevención y el<br>
					Control Del VIH/SIDA</h1><br>
					<br><br>
					
					<br><b>Estimado(a) _nombreRevisor:</b><br><br>
					
					El Centro Nacional para la Prevención y el Control del VIH y el sida (CENSIDA) lo invita a colaborar como miembro revisor de los proyectos registrados por las organizaciones de 
					la sociedad civil participantes en la <b>\"CONVOCATORIA PÚBLICA DIRIGIDA A LAS ORGANIZACIONES DE LA SOCIEDAD CIVIL CON EXPERIENCIA Y TRABAJO COMPROBABLE EN PREVENCIÓN DEL VIH E INFECCIONES DE TRANSMISIÓN SEXUAL (ITS) 
					PARA LA IMPLEMENTACIÓN DE ESTRATEGIAS DE PREVENCIÓN FOCALIZADA DEL VIH Y OTRAS ITS QUE FORTALEZCAN LA RESPUESTA NACIONAL 2015.\"</b>.
					
					En caso de aceptar dicha invitación, es requerido confirme su participación <a href=\"http://censida.grupolatis.net/modulosEspeciales_Censida/aceptacionRevisor.php?p=_parametro\"><font color='#F00'>AQUÍ</font></a><br><br>
					Si <b>NO</b> desea participar como revisor del proceso de click <a href=\"http://censida.grupolatis.net/modulosEspeciales_Censida/rechazoRevisor.php?p=_parametro\"><font color='#F00'>AQUÍ</font></a><br><br>
					
					
					Cualquier duda/sugerencia, le agradeceremos nos la haga llegar por este medio al siguiente correo electrónico:<br><br>
					<b>soporteSMAP@grupolatis.net</b><br><br>

					";	
	
	/*$cuerpoMail="
					<table width=\"800\"><tr><td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/logoSalud.gif\"></td>
					<td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/censidaLogo.gif\"></td></tr></table>
					<br><br><h1>Centro Nacional para La Prevención y el<br>
					Control Del VIH/SIDA</h1><br>
					<br><br>
					
					<span style='font-size:12px'>
					<br><b>Estimada/o _nombreRevisor:</b><br><br>
					
					En días anteriores se le envió por este medio dos invitaciones para formar parte del equipo de dictaminadores de los proyectos participantes en la <b>\"CONVOCATORIA PÚBLICA PARA LA IMPLEMENTACION DE ESTRATEGIAS DE PREVENCIÓN 
					COMBINADA para el fortalecimiento de la respuesta ante el VIH y el SIDA 2013\"</b>, hasta el momento no hemos recibido su confirmación o rechazado, por lo cual se le envía nuevamente la invitación esperando pueda indicarnos su respuesta.<br><br>.<br><br>


					Una vez leída la invitación adjunta, le solicito confirme o rechace su participación como dictaminador, dando click en alguno de los siguientes enlaces:<br><br>
					<a href=\"http://censida.grupolatis.net/modulosEspeciales_Censida/aceptacionRevisor.php?p=_parametro\"><img src=\"http://censida.grupolatis.net/images/Reinscribir.png\"> Sí, deseo formar parte del equipo de revisores</a><br><br>
					<a href=\"http://censida.grupolatis.net/modulosEspeciales_Censida/rechazoRevisor.php?p=_parametro\"><img src=\"http://censida.grupolatis.net/images/cross.png\"> Gracias, pero en estos momentos <b>NO</b> no deseo formar parte del equipo de revisores</a><br><br>
					
					Sin nada más que agregar le agradezco su atención y respuesta a está invitación.<br><br>
					Atentamente,<br><br>
					
					Dra. Patricia Uribe Zúñiga<br>
					Directora General
					</span>
					<br><br>
					";		
	
		$cuerpoMail="
					<table width=\"800\"><tr><td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/logoSalud.gif\"></td>
					<td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/censidaLogo.gif\"></td></tr></table>
					<br><br><h1>Centro Nacional para La Prevención y el<br>
					Control Del VIH/SIDA</h1><br>
					<br><br>
					
					<span style='font-size:12px'>
					<br><b>Estimada/o _nombreRevisor:</b><br><br>
					
					Por este medio, se envía en adjunto la invitación para formar parte del equipo de dictaminadores de los proyectos participantes en la <b>\"CONVOCATORIA PÚBLICA PARA LA IMPLEMENTACION DE ESTRATEGIAS DE PREVENCIÓN 
					COMBINADA para el fortalecimiento de la respuesta ante el VIH y el SIDA 2013\"</b> emitido por La Secretaría de Salud, a través de la Subsecretaría de Prevención y Promoción de la Salud y el Centro Nacional para la
					Prevención y el Control del VIH/SIDA, CENSIDA.<br><br>


					Una vez leída la invitación adjunta, le solicito confirme o rechace su participación como dictaminador, dando click en alguno de los siguientes enlaces:<br><br>
					<a href=\"http://censida.grupolatis.net/modulosEspeciales_Censida/aceptacionRevisor.php?p=_parametro\"><img src=\"http://censida.grupolatis.net/images/Reinscribir.png\"> Sí, deseo formar parte del equipo de revisores</a><br><br>
					<a href=\"http://censida.grupolatis.net/modulosEspeciales_Censida/rechazoRevisor.php?p=_parametro\"><img src=\"http://censida.grupolatis.net/images/cross.png\"> Gracias, pero en estos momentos <b>NO</b> no deseo formar parte del equipo de revisores</a><br><br>
					
					Sin nada más que agregar le agradezco su atención y respuesta a está invitación.<br><br>
					Atentamente,<br><br>
					
					Dra. Patricia Uribe Zúñiga<br>
					Directora General
					</span>
					<br><br>
					";	
		$cuerpoMail="
					<table width=\"800\"><tr><td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/logoSalud.gif\"></td>
					<td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/censidaLogo.gif\"></td></tr></table>
					<br><br><h1>Centro Nacional para La Prevención y el<br>
					Control Del VIH/SIDA</h1><br>
					<br><br>
					
					<span style='font-size:12px'>
					<br><b>Estimada/o _nombreRevisor:</b><br><br>
					
					Hemos detectado que áun tiene proyectos pendientes por evaluar, entendemos que debido a su importancia como profesional en su materia que su tiempo es limitado, por lo anterior apreciamos
					su esfuerzo en participar con nosotros y le invitamos a continuar con esta labor de apoyo a la institución conforme su disponibilidad de tiempo le permita. <br>
					Le reitero que su participación es de vital importancia para esta etapa del proceso de selección de proyectos financiables por CENSIDA.<br><br>
					Le recuerdamos que para evaluar sus proyectos asignados, deberá ingresar al SMAP (Sistema de Monitoreo de Actividades de Prevención) a través de la siguiente dirección
					de Internet <a href=\"http://censida.grupolatis.net\">http://censida.grupolatis.net</a>, sus datos de acceso son:<br><br>
					<b>Usuario:</b> _usuario<br>
					<b>Contraseña:</b> _contrasena<br><br>
					
					En adjunto se envía la \"Guía de evaluación de Proyectos 2013\" donde se detalla el procedimiento para llevar a cabo dicha actividad.
					
					Sin nada más que agregar le agradezco su atención y apoyo en esta etapa de la convocatoria.<br><br>
					Atentamente,<br><br>
					
					Dra. Patricia Uribe Zúñiga<br>
					Directora General
					</span>
					<br><br>
					";		
					
		$cuerpoMail="
					<table width=\"800\"><tr><td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/logoSalud.gif\"></td>
					<td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/censidaLogo.gif\"></td></tr></table>
					<br><br><h1>Centro Nacional para La Prevención y el<br>
					Control Del VIH/SIDA</h1><br>
					<br><br>
					
					<span style='font-size:12px'>
					<br><b>Estimada/o _nombreRevisor:</b><br><br>
					
					Hemos detectado que áun tiene proyectos pendientes por evaluar, entendemos que debido a su importancia como profesional en su materia cuenta con tiempo disponible limitado, por lo anterior se extenderá una prórroga para evaluar sus proyectos asignados siendo el domingo 17 de marzo a las 22:00 hrs el tiempo limite para dicha actividad, realmente apreciamos
					su esfuerzo en participar con nosotros y le invitamos a continuar con esta labor de apoyo a la institución. <br>
					
					Le recuerdamos que para evaluar sus proyectos asignados, deberá ingresar al SMAP (Sistema de Monitoreo de Actividades de Prevención) a través de la siguiente dirección
					de Internet <a href=\"http://censida.grupolatis.net\">http://censida.grupolatis.net</a>, sus datos de acceso son:<br><br>
					<b>Usuario:</b> _usuario<br>
					<b>Contraseña:</b> _contrasena<br><br>
					
					En adjunto se envía la \"Guía de evaluación de Proyectos 2013\" donde se detalla el procedimiento para llevar a cabo dicha actividad.
					
					Sin nada más que agregar le agradezco su atención y apoyo en esta etapa de la convocatoria.<br><br>
					Atentamente,<br><br>
					
					Dra. Patricia Uribe Zúñiga<br>
					Directora General
					</span>
					<br><br>
					";*/								
		//SELECT DISTINCT idUsuario FROM 1010_distribucionRevisoresProyectos WHERE idFormulario=410;
		//SELECT idUsuario FROM 1010_distribucionRevisoresProyectos WHERE situacion=1 AND numProyAsignados>0
		$comp="";
		if($prueba)		
			$comp=" limit 0,1";
		$consulta="SELECT distinct u.idUsuario,u.Login,u.Nombre,u.Password FROM  800_usuarios u WHERE u.idUsuario in 
					(SELECT DISTINCT idUsuario FROM 1011_asignacionRevisoresProyectos WHERE idFormulario=410 AND situacion=1 AND idUsuario>0)".$comp;
		
		
		$res=$con->obtenerFilas($consulta);	
		while($fila=mysql_fetch_row($res))
		{
			$idUsuario=$fila[0];
			$nombre=$fila[2];
			$login=$fila[1];
			$password=$fila[3];
			$parametro=bE("0_".$fila[0]."_410");
			$consulta="SELECT distinct Mail FROM 805_mails WHERE idUsuario=".$fila[0]." and Mail<>'' and Mail is not null limit 0,1";	
			$mail=$con->obtenerValor($consulta);

			$nCuerpo=str_replace("_nombreRevisor",$nombre,$cuerpoMail);
			$nCuerpo=str_replace("_usuario",$login,$nCuerpo);
			$nCuerpo=str_replace("_contrasena",$password,$nCuerpo);
			$nCuerpo=str_replace("_parametro",$parametro,$nCuerpo);
			
			$obj=array();
			$obj[0]=$idUsuario;
			$obj[1]=$nombre;
			if($prueba)
				$mail="novant1730@hotmail.com";
			$obj[2]=$mail;
			
			if($mail!="")
			{
				if(!enviarMail($mail,"Prorroga para evaluacion de proyectos",$nCuerpo,"soporteSMAP@grupolatis.net","CENSIDA",$arrAchivos,NULL))			
				{
					
					array_push($arrUsrProblemas,$obj);
				}
				else
				{
					array_push($arrUsrOk,$obj);	
				}
			}
		}
		echo "Usuarios Mal: ".sizeof($arrUsrProblemas)."<br>";
		varDump($arrUsrProblemas);
		echo "Usuarios OK: ".sizeof($arrUsrOk)." <br>";
		varDump($arrUsrOk);

	}
	
	
	function asignarRevisoresProyecto()
	{
		global $con;
		$continuar=true;
		$nCiclos=0;
		while($continuar)
		{
			$consulta="SELECT * FROM (
						SELECT id__410_tablaDinamica,(SELECT COUNT(*) FROM 1011_asignacionRevisoresProyectos WHERE idProyecto=t.id__410_tablaDinamica AND idFormulario=410) AS nAsignaciones 
						FROM _410_tablaDinamica t WHERE idEstado=2 AND id__410_tablaDinamica ) AS tmp WHERE nAsignaciones<3";
			if($con->filasAfectadas==0)
				$continuar=false;
			$resProy=$con->obtenerFilas($consulta);
			while($fProy=mysql_fetch_row($resProy))
			{
				$consulta="SELECT idUsuario FROM 1011_asignacionRevisoresProyectos WHERE idFormulario=410 AND idProyecto=".$fProy[0];
				$listUsuarios=$con->obtenerListaValores($consulta);
				if($listUsuarios=="")
					$listUsuarios=-1;
				$consulta="SELECT idUsuario FROM 1010_distribucionRevisoresProyectos WHERE numProyAsignados<numMax AND situacion=1 and idUsuario not in (".$listUsuarios.") order by numProyAsignados";
				$fila=$con->obtenerPrimeraFila($consulta);
				if($fila)
				{
					$consulta="INSERT INTO 1011_asignacionRevisoresProyectos(idUsuario,idProyecto,tipoRevisor,idFormulario,situacion,fechaAsignacion)
										VALUES(".$fila[0].",".$fProy[0].",1,410,1,'2013-03-11 16:00:00')";
					if($con->ejecutarConsulta($consulta))				
					{
						$consulta="update 1010_distribucionRevisoresProyectos set numProyAsignados=numProyAsignados+1 where idUsuario=".$fila[0]." and idFormulario=410";
						$con->ejecutarConsulta($consulta);
					}
				}
				else
				{
					$consulta="SELECT count(*) FROM 1010_distribucionRevisoresProyectos WHERE numProyAsignados<numMax AND situacion=1";
					$nReg=$con->obtenerValor($consulta);
					if($nReg==0)
						$continuar=false;
					else
						$nCiclos++;
				}
			}
			if($nCiclos>5)
				$continuar=false;
		}
	}
	
	function reasignarProyectos()
	{
		global $con;
		$arrRevisores=explode(",","301,461,298,460,1261");
		$consulta="SELECT * FROM (
				SELECT t.id__410_tablaDinamica,idCategoria,idSubcategoria,idTema,tituloProyecto, 
				(SELECT COUNT(*) FROM 1011_asignacionRevisoresProyectos WHERE idFormulario=410 AND 
				idProyecto= t.id__410_tablaDinamica AND situacion=1) AS nRegistros
				FROM _410_tablaDinamica t WHERE idEstado=2) AS tmp WHERE nRegistros=1 ORDER BY nRegistros 
				";
		$res=$con->obtenerFilas($consulta);
		$pos=0;
		while($fila=mysql_fetch_row($res))
		{
			$consulta="SELECT COUNT(*) FROM 9053_resultadoCuestionario WHERE idReferencia1=410 AND idReferencia2=".$fila[0]." AND calificacionFinal>=60";
			$nEval=$con->obtenerValor($consulta);
			if($nEval==0)
			{
				continue;
			}
			$consulta="SELECT * FROM 1011_asignacionRevisoresProyectos WHERE idFormulario=410 AND situacion=1 AND idProyecto=".$fila[0]."";
			$resEval=$con->obtenerFilas($consulta);
			while($fEval=mysql_fetch_row($resEval))
			{
				$idUsuario=$arrRevisores[$pos];
				$consulta="select count(*) from 1011_asignacionRevisoresProyectos where idFormulario=410 and idProyecto=".$fila[0]." and idUsuario=".$idUsuario;
				$nReg=$con->obtenerValor($consulta);
				if($nReg==0)
				{
					$consulta="update 1011_asignacionRevisoresProyectos set idUsuario=".$idUsuario." where idUsuarioVSProyecto=".$fEval[0];
					$con->ejecutarConsulta($consulta);
					$consulta="INSERT INTO 1010_reasignacionProyectos(idUsuario,idProyecto) VALUES(".$idUsuario.",".$fila[0].")";
					$con->ejecutarConsulta($consulta);
					$pos++;
					if($pos>4)
						$pos=0;
				}
			}
		}
		
	}
	
	function obtenerSituacionCategorias(&$arrCategorias,$idFormulario)
	{
		global $con;
		$convCerrada=esConvocatoriaCerrada();
		$consulta="";
		$minCalificacionAprobatoria=60;
		$cadCategorias="";
		$consulta="SELECT criterioCuartaEvaluacion,difernciaEntreResultados,numEvalMayorMinimo FROM _388_tablaDinamica";
		$fConfiguracion=$con->obtenerPrimeraFila($consulta);
		$criterioPromedio="";
		$valorDiferencia=$fConfiguracion[1];
		if($fConfiguracion[0]==1)
			$criterioPromedio="0,2";
		else
			$criterioPromedio="2";
		if(sizeof($arrCategorias)>0)
		{
			foreach($arrCategorias as $idCategoria=>$resto)	
			{
				
				$posicion=0;
				$arrDatosLlave=explode("_",$idCategoria);
				$query="SELECT montoMaximo FROM 0_distribucionTemas WHERE idCategoria=".$arrDatosLlave[0]." AND idSubcategoria=".$arrDatosLlave[1]." AND idTema=".$arrDatosLlave[2];
				$montoProyectoCategoria=$con->obtenerValor($query);
				$limiteMonto=$montoProyectoCategoria-($montoProyectoCategoria*0.40);
				$consulta="SELECT COUNT(*) FROM 1019_categoriasCerradas WHERE idCategoria=".$arrDatosLlave[0]." AND 
							idSubcategoria=".$arrDatosLlave[1]." AND idTema=".$arrDatosLlave[2];
				$nRegCerrada=$con->obtenerValor($consulta);
				$arrCategorias[$idCategoria][4]=$nRegCerrada;
				
				$consulta="SELECT id__".$idFormulario."_tablaDinamica,marcaAutorizado FROM _".$idFormulario."_tablaDinamica 
				WHERE idEstado>=2 AND idCategoria=".$arrDatosLlave[0]." AND idSubcategoria=".$arrDatosLlave[1]." AND idTema=".$arrDatosLlave[2];
				$resProy=$con->obtenerFilas($consulta);
				while($fila=mysql_fetch_row($resProy))
				{
					$listLlaves="-1";
					if($convCerrada)
					{
						$query="select complementario5 from 1011_asignacionRevisoresProyectos a where ".str_replace("a.complementario2",$fila[0],bD(glp()))."=complementario5";
						
						$listLlaves=$con->obtenerListaValores($query,"'");
					}
					/*if($fila[1]==-1)
					{
						$posicion=4;
					}
					else*/
					{
						if($fila[1]==-1)
						{
							$arrCategorias[$idCategoria][3][4]["valor"]++;
							if($arrCategorias[$idCategoria][3][4]["proyectos"]=="")
								$arrCategorias[$idCategoria][3][4]["proyectos"]=$fila[0];
							else
								$arrCategorias[$idCategoria][3][4]["proyectos"].=",".$fila[0];
						}
						else
						{
							$tipoIncidencia=0;
							$query="SELECT COUNT(*) FROM 1021_proyectosIgnoraSituacion WHERE idFormulario=".$idFormulario." AND idProyecto=".$fila[0];
							$nRegIgn=$con->obtenerValor($query);
							if($nRegIgn==0)
							{
								if(!$convCerrada)
									$query="SELECT complementario4 FROM 1011_asignacionRevisoresProyectos WHERE complementario3=".$idFormulario." AND complementario2=".$fila[0]." AND complementario4 IS NOT null limit 0,3";
								else
									$query="SELECT ".bD(gdhr())." FROM 1011_asignacionRevisoresProyectos WHERE  complementario5 IN (".$listLlaves.") and complementario3=".$idFormulario." AND complementario4 IS NOT null limit 0,3";
								$listEvaluaciones=$con->obtenerListaValores($query);
								if($listEvaluaciones=="")
									$listEvaluaciones=-1;
								$query="SELECT count(*) FROM 9054_respuestasCuestionario WHERE idReferencia in(".$listEvaluaciones.") AND idElemento=116 AND idRespuesta=19";
								$nReg=$con->obtenerValor($query);
								if($nReg>0)
									$tipoIncidencia++;
								$query="select sum(total) from 100_calculosGrid WHERE idFormulario=410 AND idReferencia=".$fila[0];
								$montoTotal=$con->obtenerValor($query);	
								if($limiteMonto<$limiteMonto)
								{
									$tipoIncidencia++;
								}
							}
							if($tipoIncidencia!=0)
							{
								$arrCategorias[$idCategoria][3][6]["valor"]++;
								if($arrCategorias[$idCategoria][3][6]["proyectos"]=="")
									$arrCategorias[$idCategoria][3][6]["proyectos"]=$fila[0];
								else
									$arrCategorias[$idCategoria][3][6]["proyectos"].=",".$fila[0];
							}
						}
						$numAprobatorias=0;
						$posicion=0;
						$calTotal=0;
						$numEvaluaciones=0;
						$consistente=true;
						$arrCalificaciones=array();
						$numRevisor=0;
						if(!$convCerrada)
						{
							$consulta="select complementario from 1011_asignacionRevisoresProyectos where complementario2=".$fila[0]." and complementario3=".$idFormulario." and esEvaluacionComite=0";
							$resRevisores=$con->obtenerFilas($consulta);
							while($fRevisores=mysql_fetch_row($resRevisores))
							{
								$consulta="SELECT responsableRegistro FROM 9053_resultadoCuestionario WHERE idReferencia1=".$idFormulario." AND idReferencia2=".$fila[0]." and responsableRegistro in (".$fRevisores[0].")";
								$resCuestionario=$con->obtenerFilas($consulta);
								if($con->filasAfectadas>0)
								{
									while($fCuestionario=mysql_fetch_row($resCuestionario))
									{
										$consulta="select * from 9053_resultadoCuestionario where idReferencia1=".$idFormulario." AND idReferencia2=".$fila[0]." and responsableRegistro=".$fCuestionario[0]." order by calificacionFinal desc";
										$fDictamen=$con->obtenerPrimeraFila($consulta);
										if($fDictamen)
										{
											if($con->filasAfectadas>1)
											{
												$consulta="update 9053_resultadoCuestionario set responsableRegistro=responsableRegistro*-1 where 
														idReferencia1=".$idFormulario." AND idReferencia2=".$fila[0]." and responsableRegistro=".$fCuestionario[0]." and idRegistroCuestionario<>".$fDictamen[0];
												$con->ejecutarConsulta($consulta);
											}
											$numEvaluaciones++;
											$calTotal+=$fDictamen[6];
											$arrCalificaciones[$fDictamen[6]."".$numEvaluaciones]=$fDictamen[6];
										}
									}
								}
								else
								{
									$fDictamen[6]=0;
									$numEvaluaciones++;
									$calTotal+=$fDictamen[6];
									$arrCalificaciones[$fDictamen[6]."".$numEvaluaciones]=$fDictamen[6];
									
								}
							}
						}
						else
						{

							$query="SELECT ".bD(gdhr())." FROM 1011_asignacionRevisoresProyectos WHERE  complementario5 IN (".$listLlaves.") and complementario3=".$idFormulario."  AND complementario4 IS NOT null and esEvaluacionComite=0";
							$resRevisores=$con->obtenerFilas($query);
							while($fRevisores=mysql_fetch_row($resRevisores))
							{
								
								$consulta="select * from 9053_resultadoCuestionario where idRegistroCuestionario=".$fRevisores[0];
								$fDictamen=$con->obtenerPrimeraFila($consulta);
								if($fDictamen)
								{
									$numEvaluaciones++;
									$calTotal+=$fDictamen[6];
									$arrCalificaciones[$fDictamen[6]."".$numEvaluaciones]=$fDictamen[6];
								}
							}
						}
						
						ksort($arrCalificaciones);
						
						
						if($numEvaluaciones==0)
						{
							$calFinal=0;
							$consistente=true;
						}
						else
						{
							$arrTemp=array();
							foreach($arrCalificaciones as $cal=>$resto)
							{
								array_push($arrTemp,$resto);
								if($resto>=$minCalificacionAprobatoria)
									$numAprobatorias++;
							}
							$calFinal=$calTotal/$numEvaluaciones;
							if($numAprobatorias==1)
							{
								$calFinal=0;
								$consistente=true;
							}
							else
							{
								$diferencia=$arrTemp[2]-$arrTemp[0];
								if($diferencia>=$valorDiferencia)
								{
									$consistente=false;
								}
							}
							
							
						}
						
						if($calFinal>=$minCalificacionAprobatoria)
						{
							if($consistente)
							{
								$posicion=0;
								if($fila[1]!='-1')
								{
									$arrCategorias[$idCategoria][3][5]["valor"]++;
									if($arrCategorias[$idCategoria][3][5]["proyectos"]=="")
										$arrCategorias[$idCategoria][3][5]["proyectos"]=$fila[0];
									else
										$arrCategorias[$idCategoria][3][5]["proyectos"].=",".$fila[0];
								}
								
							}
							else
								$posicion=1;
						}
						else
						{
							if($consistente)
								$posicion=3;
							else
								$posicion=2;
						}
						
					}
					
					$arrCategorias[$idCategoria][3][$posicion]["valor"]++;
					if($arrCategorias[$idCategoria][3][$posicion]["proyectos"]=="")
						$arrCategorias[$idCategoria][3][$posicion]["proyectos"]=$fila[0];
					else
						$arrCategorias[$idCategoria][3][$posicion]["proyectos"].=",".$fila[0];
				}
				$listFinanciables=$arrCategorias[$idCategoria][3][5]["proyectos"];
				if($listFinanciables=="")
					$listFinanciables=-1;

				if(!$convCerrada)
				{
					$consulta="SELECT idRegistro FROM (
								SELECT id__".$idFormulario."_tablaDinamica as idRegistro,
								(SELECT COUNT(*) FROM 1011_asignacionRevisoresProyectos WHERE complementario2=t.id__".$idFormulario."_tablaDinamica 
								AND complementario3=".$idFormulario." and situacion in (0,2)) AS nReg
								FROM  _".$idFormulario."_tablaDinamica t WHERE idEstado=2 AND idCategoria=".$arrDatosLlave[0]." 
								AND idSubcategoria=".$arrDatosLlave[1]." AND idTema=".$arrDatosLlave[2]." and marcaAutorizado<>'-1'
								AND id__".$idFormulario."_tablaDinamica NOT IN (".$listFinanciables.")) AS tmp WHERE nReg=4";

				}
				else
				{
					$consulta="SELECT idRegistro FROM (
								SELECT id__".$idFormulario."_tablaDinamica as idRegistro,
								(
									SELECT count(*) FROM 1011_asignacionRevisoresProyectos a,_410_tablaDinamica  WHERE complementario5=".bD(glp(true))." AND id__410_tablaDinamica=t.id__".$idFormulario."_tablaDinamica 
									AND complementario3=".$idFormulario." and situacion in (0,2)
								
								) AS nReg
								FROM  _".$idFormulario."_tablaDinamica t WHERE idEstado=2 AND idCategoria=".$arrDatosLlave[0]." 
								AND idSubcategoria=".$arrDatosLlave[1]." AND idTema=".$arrDatosLlave[2]." and marcaAutorizado<>'-1'
								AND id__".$idFormulario."_tablaDinamica NOT IN (".$listFinanciables.")) AS tmp WHERE nReg=4";
				}
				$lValor=$con->obtenerListaValores($consulta);
				if($lValor=="")
				{	
					$lValor=-1;
					//$listFinanciables.=",".$lValor;
				}
				$consulta="SELECT id__".$idFormulario."_tablaDinamica,marcaAutorizado FROM _".$idFormulario."_tablaDinamica WHERE 
						idEstado=2 AND idCategoria=".$arrDatosLlave[0]." AND idSubcategoria=".$arrDatosLlave[1]." 
						AND idTema=".$arrDatosLlave[2]." and marcaAutorizado<>'-1' and 
						id__".$idFormulario."_tablaDinamica  in(".$lValor.")";
				$resProy=$con->obtenerFilas($consulta);
				while($fila=mysql_fetch_row($resProy))
				{
					$listLlaves="-1";
					if($convCerrada)
					{
						
						$query="select complementario5 from 1011_asignacionRevisoresProyectos a where ".str_replace("a.complementario2",$fila[0],bD(glp()))."=complementario5";
						$listLlaves=$con->obtenerListaValores($query,"'");
					}
					$numAprobatorias=0;
					$posicion=0;
					$calTotal=0;
					$numEvaluaciones=0;
					$consistente=true;
					$arrCalificaciones=array();
					if(!$convCerrada)
					{
						$consulta="select complementario from 1011_asignacionRevisoresProyectos where complementario2=".$fila[0]." and complementario3=".$idFormulario." and situacion=2";
						$listUsuarios=$con->obtenerListaValores($consulta);
						if($listUsuarios=="")
							$listUsuarios=-1;
						$consulta="SELECT responsableRegistro FROM 9053_resultadoCuestionario WHERE idReferencia1=".$idFormulario." AND idReferencia2=".$fila[0]." and responsableRegistro in (".$listUsuarios.")";
						$resCuestionario=$con->obtenerFilas($consulta);
						while($fCuestionario=mysql_fetch_row($resCuestionario))
						{
							$consulta="select * from 9053_resultadoCuestionario where idReferencia1=".$idFormulario." AND idReferencia2=".$fila[0]." and responsableRegistro=".$fCuestionario[0]." order by calificacionFinal desc";
							$fDictamen=$con->obtenerPrimeraFila($consulta);
							if($fDictamen)
							{
								if($con->filasAfectadas>1)
								{
									$consulta="update 9053_resultadoCuestionario set responsableRegistro=responsableRegistro*-1 where 
											idReferencia1=".$idFormulario." AND idReferencia2=".$fila[0]." and responsableRegistro=".$fCuestionario[0]." and idRegistroCuestionario<>".$fDictamen[0];
									$con->ejecutarConsulta($consulta);
								}
								$numEvaluaciones++;
								$calTotal+=$fDictamen[6];
								$arrCalificaciones[$fDictamen[6]]=0;
							}
						}
					}
					else
					{
						$query="SELECT ".bD(gdhr())." FROM 1011_asignacionRevisoresProyectos WHERE  complementario5 IN (".$listLlaves.") and complementario3=".$idFormulario." AND complementario4 IS NOT null and situacion=2";
						$resRevisores=$con->obtenerFilas($query);
						while($fRevisores=mysql_fetch_row($resRevisores))
						{
							$consulta="select * from 9053_resultadoCuestionario where idRegistroCuestionario=".$fRevisores[0];
							$fDictamen=$con->obtenerPrimeraFila($consulta);
							if($fDictamen)
							{
								$numEvaluaciones++;
								$calTotal+=$fDictamen[6];
								$arrCalificaciones[$fDictamen[6]]=0;
							}
						}
					}
					ksort($arrCalificaciones);
					if($numEvaluaciones>0)
					{
						$arrTemp=array();
						foreach($arrCalificaciones as $cal=>$resto)
						{
							array_push($arrTemp,$cal);
							if($cal>=$minCalificacionAprobatoria)
								$numAprobatorias++;
						}
						$calFinal=$calTotal/$numEvaluaciones;
						if($calFinal>=$minCalificacionAprobatoria)
						{
							$arrCategorias[$idCategoria][3][5]["valor"]++;
							if($arrCategorias[$idCategoria][3][5]["proyectos"]=="")
								$arrCategorias[$idCategoria][3][5]["proyectos"]=$fila[0];
							else
								$arrCategorias[$idCategoria][3][5]["proyectos"].=",".$fila[0];
						}
					}
				}
			}
		}
		
		return $arrCategorias;
	}
	
	function enviarCorreosOSCModificaciones()
	{
		global $con;
		$prueba=false;
		$arrUsrProblemas=array();
		$arrUsrOk=array();
		$arrAchivos=array();
		$arrAchivos[0][0]="../recursos/comunicado2aMini.pdf";
		$arrAchivos[0][1]="Comunicado CENSIDA.pdf";
		
				
		$cuerpoMail="
					<table width=\"800\"><tr><td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/logoSalud.gif\"></td>
					<td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/censidaLogo.gif\"></td></tr></table>
					<br><br><h1>Centro Nacional para La Prevención y el<br>
					Control Del VIH/SIDA</h1><br>
					<br><br>
					
					<span style='font-size:12px'>
					<br><b>Estimadas OSC E IA:</b><br><br>
					Por  este medio se <br><br>
					
					
					
					</span>
					<br><br>
					
					";								
		

		$cuerpoMail="<table width=\"800\"><tr><td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/logoSalud.gif\"></td>

					<td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/censidaLogo.gif\"></td></tr></table>

					<br><br><h1>Centro Nacional para La Prevención y el<br>

					Control Del VIH/SIDA</h1><br>

					<br><br>

					

					<span style='font-size:12px'>
					<b>Estimadas OSC e IA:</b><br><br>
					
					Por este medio se envía el comunicado adjunto para su difusión.
					
					</span>

					<br><br>
				";
		$comp="";
		if($prueba)
			$comp=" limit 0,1";
		$consulta="SELECT distinct u.idUsuario,u.Login,u.Nombre,u.Password FROM  800_usuarios u WHERE u.idUsuario in 
					(SELECT DISTINCT responsable FROM  _410_tablaDinamica WHERE marcaAutorizado=1)  ".$comp;
		
		
		$res=$con->obtenerFilas($consulta);	
		while($fila=mysql_fetch_row($res))
		{
			$tblComentarios="";
		

			$idUsuario=$fila[0];
			$nombre=$fila[2];
			$login=$fila[1];
			$password=$fila[3];
			$parametro=bE("0_".$fila[0]."_410");
			$nCuerpo=str_replace("_listadoProyectos",$tblComentarios,$cuerpoMail);
			$consulta="SELECT distinct Mail FROM 805_mails WHERE idUsuario=".$fila[0]." and Mail<>'' and Mail is not null";	
			$resMail=$con->obtenerFilas($consulta);
			while($fMail=mysql_fetch_row($resMail))
			{
				$mail=$fMail[0];
	
				$nCuerpo=str_replace("_nombreRevisor",$nombre,$nCuerpo);
				$nCuerpo=str_replace("_usuario",$login,$nCuerpo);
				$nCuerpo=str_replace("_contrasena",$password,$nCuerpo);
				$nCuerpo=str_replace("_parametro",$parametro,$nCuerpo);
				
				$obj=array();
				$obj[0]=$idUsuario;
				$obj[1]=$nombre;
				if($prueba)
					$mail="novant1730@hotmail.com";
				$obj[2]=$mail;
				
				if($mail!="")
				{
					if(!enviarMail($mail,"Comunicado CENSIDA",$nCuerpo,"soporteSMAP@grupolatis.net","CENSIDA",$arrAchivos,NULL))			
					{
						
						array_push($arrUsrProblemas,$obj);
					}
					else
					{
						array_push($arrUsrOk,$obj);	
					}
				}
			}
		}
		echo "Usuarios Mal: ".sizeof($arrUsrProblemas)."<br>";
		varDump($arrUsrProblemas);
		echo "Usuarios OK: ".sizeof($arrUsrOk)." <br>";
		varDump($arrUsrOk);

	}
	
	function enviarCorreosRvisoresProyectos()
	{
		global $con;
		$prueba=false;
		$arrUsrProblemas=array();
		$arrUsrOk=array();
		$arrAchivos=array();
		/*$arrAchivos[0][0]="../recursos/GuiaEvaluacionProyectos2013.pdf";
		$arrAchivos[0][1]="Guia de evaluacion 2013.pdf";*/
				
		$cuerpoMail="
					<table width=\"800\"><tr><td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/logoSalud.gif\"></td>
					<td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/censidaLogo.gif\"></td></tr></table>
					<br><br><h1>Centro Nacional para La Prevención y el<br>
					Control Del VIH/SIDA</h1><br>
					<br><br>			
					
					<span style='font-size:12px'>
					<br><b>Estimada/o _nombreRevisor:</b><br><br>
					Por este medio se le notifica el resultado de las evaluaciones llevadas a cabo por el Comité de Revisión y Recomendación de Proyectos (CoRePro) sobre sus proyectos participantes en la \"CONVOCATORIA PÚBLICA PARA LA IMPLEMENTACION DE ESTRATEGIAS DE PREVENCIÓN
COMBINADA para el fortalecimiento de la respuesta ante el VIH y el SIDA 2013\"<br><br>

					_listadoProyectos
					<br><br>
					Es importante aclarar que de acuerdo a la convocatoria el promedio es calculado con base en las tres evaluaciones virtuales siempre y cuando no exista una Evaluación del Revisor 4,
					en caso contrario, se descarta la evaluación virtual más alta y es reemplazada por la Evaluación del Revisor 4 para su promedio.
					</span>
					<br><br>
					
					";								
		
		$comp="";
		if($prueba)		
			$comp=" limit 0,1";
		/*$consulta="SELECT distinct u.idUsuario,u.Login,u.Nombre,u.Password FROM  800_usuarios u WHERE u.idUsuario in 
					(SELECT DISTINCT usuarios FROM _435_tablaDinamica) ".$comp;*/
		
		$consulta="SELECT DISTINCT codigoInstitucion  FROM comentariosCoRePro where idProyecto in(164,188)  ".$comp." ";
		$res=$con->obtenerFilas($consulta);	
		while($filaOrg=mysql_fetch_row($res))
		{
			$tblComentarios="";
			$listProyectos="";
			$consulta="SELECT Folio,Titulo_de_proyecto,Descalificado,Motivo_descalificacion,Comentarios_generales_CoRePro,Evaluacion_1,Comentarios_Evaluacion_1,Evaluacion_2,Comentarios_Evaluacion_2,
						Evaluacion_3,Comentarios_Evaluacion_3,Evaluacion_4,Comentarios_Evaluacion_4,Promedio,idProyecto FROM comentariosCoRePro WHERE idProyecto in(164,188) and codigoInstitucion='".$filaOrg[0]."'";
			$resProy=$con->obtenerFilas($consulta);
			while($fComentarios=mysql_fetch_row($resProy))
			{
				if($listProyectos=="")
					$listProyectos=$fComentarios[14];
				else
					$listProyectos.=",".$fComentarios[14];
				if($fComentarios[4]=="")
					$fComentarios[4]="Sin comentarios";
				if($fComentarios[6]=="")
					$fComentarios[6]="Sin comentarios";
				if($fComentarios[8]=="")
					$fComentarios[8]="Sin comentarios";
				if($fComentarios[10]=="")
					$fComentarios[10]="Sin comentarios";
				if($fComentarios[11]!="")
				{
					if($fComentarios[12]=="")
						$fComentarios[12]="Sin comentarios";
				}
				$tblComentarios.="<table cellspacing='0' ><tr height='21'><td width='180' align='left' style='border-style: solid; border-width:1px;'><b>Folio:</b></td><td width='400' align='left' style='border-style: solid; border-width:1px;'>".$fComentarios[0]."</td></tr>";
				$tblComentarios.="<tr height='21'><td  align='left' style='border-style: solid; border-width:1px;'><b>Título del proyecto:</b></td><td align='justify' style='border-style: solid; border-width:1px;'>".$fComentarios[1]."</td></tr>";
				//$tblComentarios.="<tr height='21'><td  align='left' style='border-style: solid; border-width:1px;'><b>Proyecto descalificado:</b></td><td align='justify' style='border-style: solid; border-width:1px;'>".$fComentarios[2]."</td></tr>";
			//	$tblComentarios.="<tr height='21'><td  align='left' style='border-style: solid; border-width:1px;'><b>Motivo descalificación:</b></td><td align='justify' style='border-style: solid; border-width:1px;'>".$fComentarios[3]."</td></tr>";
				$tblComentarios.="<tr height='21'><td  align='left' style='border-style: solid; border-width:1px;'><b>Evaluación del Revisor 1:</b></td><td align='justify' style='border-style: solid; border-width:1px;'>".$fComentarios[5]."</td></tr>";
				$tblComentarios.="<tr height='21'><td  align='left' style='border-style: solid; border-width:1px;'><b>Evaluación del Revisor 2:</b></td><td align='justify' style='border-style: solid; border-width:1px;'>".$fComentarios[7]."</td></tr>";
				$tblComentarios.="<tr height='21'><td  align='left' style='border-style: solid; border-width:1px;'><b>Evaluación del Revisor 3:</b></td><td align='justify' style='border-style: solid; border-width:1px;'>".$fComentarios[9]."</td></tr>";
				$tblComentarios.="<tr height='21'><td  align='left' style='border-style: solid; border-width:1px;'><b>Evaluación del Revisor 4<br>(Realizado por CoRePro):</b></td><td align='justify' style='border-style: solid; border-width:1px;'>".$fComentarios[11]."</td></tr>";
				$tblComentarios.="<tr height='21'><td  align='left' style='border-style: solid; border-width:1px;'><b>Promedio de evaluaciones:</b></td><td align='justify' style='border-style: solid; border-width:1px;'>".$fComentarios[13]."</td></tr>";
				$tblComentarios.="<tr height='21'><td  align='left' style='border-style: solid; border-width:1px;'><b>Comentarios Generales:</b></td><td align='justify' style='border-style: solid; border-width:1px;'>".$fComentarios[4]."</td></tr>";
				$tblComentarios.="<tr height='21'><td  align='left' style='border-style: solid; border-width:1px;'><b>Comentarios del Revisor 1:</b></td><td align='justify' style='border-style: solid; border-width:1px;'>".$fComentarios[6]."</td></tr>";
				$tblComentarios.="<tr height='21'><td  align='left' style='border-style: solid; border-width:1px;'><b>Comentarios del Revisor 2:</b></td><td align='justify' style='border-style: solid; border-width:1px;'>".$fComentarios[8]."</td></tr>";
				$tblComentarios.="<tr height='21'><td  align='left' style='border-style: solid; border-width:1px;'><b>Comentarios del Revisor 3:</b></td><td align='justify' style='border-style: solid; border-width:1px;'>".$fComentarios[10]."</td></tr>";
				$tblComentarios.="<tr height='21'><td  align='left' style='border-style: solid; border-width:1px;'><b>Comentarios del Revisor 4<br>(Realizado por CoRePro):</b></td><td align='justify' style='border-style: solid; border-width:1px;'>".$fComentarios[12]."</td></tr>";
				$tblComentarios.="</tr></table><br><br>";
			}
			$consulta="SELECT distinct u.idUsuario,u.Login,u.Nombre,u.Password FROM  800_usuarios u WHERE u.idUsuario in 
					(SELECT distinct responsable FROM _410_tablaDinamica where id__410_tablaDinamica in(".$listProyectos."))";
			$fila=$con->obtenerPrimeraFila($consulta);		
			$idUsuario=$fila[0];
			$nombre=$fila[2];
			$login=$fila[1];
			$password=$fila[3];
			$parametro=bE("0_".$fila[0]."_410");
			$nCuerpo=str_replace("_listadoProyectos",$tblComentarios,$cuerpoMail);
			$consulta="SELECT distinct Mail FROM 805_mails WHERE idUsuario=".$fila[0]." and Mail<>'' and Mail is not null";	
			$resMail=$con->obtenerFilas($consulta);
			while($fMail=mysql_fetch_row($resMail))
			{
				$mail=$fMail[0];
	
				$nCuerpo=str_replace("_nombreRevisor",$nombre,$nCuerpo);
				$nCuerpo=str_replace("_usuario",$login,$nCuerpo);
				$nCuerpo=str_replace("_contrasena",$password,$nCuerpo);
				$nCuerpo=str_replace("tblComentarios",$tblComentarios,$nCuerpo);
				
				$obj=array();
				$obj[0]=$idUsuario;
				$obj[1]=$nombre;
				if($prueba)
					$mail="novant1730@hotmail.com";
				$obj[2]=$mail;
				
				if($mail!="")
				{
					if(!enviarMail($mail,"Resultado de evaluaciones CoRePro",$nCuerpo,"soporteSMAP@grupolatis.net","CENSIDA",$arrAchivos,NULL))			
					{
						
						array_push($arrUsrProblemas,$obj);
					}
					else
					{
						array_push($arrUsrOk,$obj);	
					}
				}
			}
		}
		echo "Usuarios Mal: ".sizeof($arrUsrProblemas)."<br>";
		varDump($arrUsrProblemas);
		echo "Usuarios OK: ".sizeof($arrUsrOk)." <br>";
		varDump($arrUsrOk);
	}
	
	function cerrarCategoriasRevisoresAnonimos()
	{
		global $con;
		$x=0;
		$consulta[$x]="begin";
		$x++;
		$consulta[$x]="UPDATE 1011_asignacionRevisoresProyectos a SET complementario5=".bD(glp()).",complementario6=1,complementario4=".bD(ghc());
		$x++;
		$consulta[$x]="delete from _225_tablaDinamica";
		$x++;
		$consulta[$x]="INSERT INTO _225_tablaDinamica(complementario1,complementario2,complementario3,complementario4,complementario8)
						SELECT  ".bD(ghst())."  AS part2,".bD(ghc2()).",".bD(ghst2())." AS part1,".bD(ghc2()).",complementario FROM 1011_asignacionRevisoresProyectos";
		$x++;
		$consulta[$x]="commit";
		$x++;
		
		if($con->ejecutarBloque($consulta))
		{
			$query="select distinct complementario8 from _225_tablaDinamica";
			$res=$con->obtenerFilas($query);
			while($fila=mysql_fetch_row($res))
			{
				$enc=false;
				while(!$enc)
				{
					$idEval=rand(10000,10000000);
					$query="select count(*) from _225_tablaDinamica where complementario6='".$idEval."'";
					$nReg=$con->obtenerValor($query);
					if($nReg==0)
					{
						$query="update _225_tablaDinamica set complementario6='".$idEval."' where complementario8='".$fila[0]."'";
						$con->ejecutarConsulta($query);
						$enc=true;
					}
				}
			}
		}
		
		cUsr();
		$consulta="INSERT INTO 1019_convocatoriasCerradas(ciclo) VALUES(2013)";
		return $con->ejecutarConsulta($consulta);
	}		
	
	function esConvocatoriaCerrada()
	{
		global $con;
		$consulta="SELECT COUNT(*) FROM 1019_convocatoriasCerradas WHERE ciclo=2013";
		$nReg=$con->obtenerValor($consulta);
		if($nReg>0)
			return true;
		return false;
	}
	
	function verificarCambioPerfilEvaluacion($idPerfil)
	{
		global $con;
		$consulta="SELECT tipoPonderacion FROM 4591_perfilesEvaluacionMateria WHERE idPerfil=".$idPerfil;
		$tipoPonderacion=$con->obtenerValor($consulta);	
		if($tipoPonderacion==2)
		{
			$codigoPadre=str_pad($idPerfil,7,"0",STR_PAD_LEFT);
			removerPorcentajesCriterioEvaluacionPerfil($codigoPadre,$idPerfil);
		}
		return true;
	}
	
	function enviarCorreosRespPrograma()
	{
		global $con;
		$prueba=false;
		$arrUsrProblemas=array();
		$arrUsrOk=array();
		$arrAchivos=array();
		/*$arrAchivos[0][0]="../recursos/GuiaEvaluacionProyectos2013.pdf";
		$arrAchivos[0][1]="Guia de evaluacion 2013.pdf";*/
				
		$cuerpoMail="
					<table width=\"800\"><tr><td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/logoSalud.gif\"></td>
					<td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/censidaLogo.gif\"></td></tr></table>
					<br><br><h1>Centro Nacional para La Prevención y el<br>
					Control Del VIH/SIDA</h1><br>
					<br><br>			
					
					<span style='font-size:12px'>
					<br><b>Estimada/o _nombreRevisor:</b><br><br>
					Por este medio se le envía sus datos de acceso al Sistema de Monitoreo de Actividades de Prevención(SMAP), en él podrá conocer aquellos proyectos que fueron financiados por el CENSIDA en el ciclo 2013 y anteriores, para esto usted deberá:<br><br>
					1.- Ingresar al SMAP (<a href=\"http://censida.grupolatis.net\">http://censida.grupolatis.net</a>)<br>
					2.- En la pantalla principal observará un mapa de la república mexicana, después del nombre de cada estado visualizará dos valores, el primero indica el número de proyectos (pertenecientes al estado) financiados por el CENSIDA, el segundo indica el número de proyectos(pertenecientes al estado) que participaron en la convocatoria.<br>
					3. Si da click sobre un estado, se abrirá una ventana mostrando el listado de proyectos asociados al estado, para ver el detalle de cada proyecto deberá dar click sobre el folio del proyecto que desee observar.<br><br>
					Si desea observar los proyectos financiados en ciclos anteriores, podrá hacerlo cambiando el valor del campo ciclo.<br>
					
					<br><br>

					Sus datos de acceso al SMAP son:<br><br>
					
					<b>Usuario:</b> _usuario<br>
					<b>Contraseña:</b> _contrasena<br><br>
					</span>
					<br><br>
					
					";								
		
		$comp="";
		if($prueba)		
			$comp=" limit 0,1";
		/*$consulta="SELECT distinct u.idUsuario,u.Login,u.Nombre,u.Password FROM  800_usuarios u WHERE u.idUsuario in 
					(SELECT DISTINCT usuarios FROM _435_tablaDinamica) ".$comp;*/
		$consulta="SELECT u.Login,u.Password,u.Nombre,e.cveEstado FROM 800_usuarios u,820_estados e WHERE u.Nombre=e.estado ".$comp;

		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$login=$fila[0];
			$password=$fila[1];
			$consulta="SELECT id__438_tablaDinamica,nombreResponsable FROM _438_tablaDinamica WHERE estado='".$fila[3]."' AND  puesto=3 ".$comp;
			$resResp=$con->obtenerFilas($consulta);
			while($fResp=mysql_fetch_row($resResp))
			{
				$nombre=$fResp[1];
			
				$consulta="SELECT email FROM _438_Email WHERE idReferencia=".$fResp[0]." ".$comp;
				$resMail=$con->obtenerFilas($consulta);
				while($fMail=mysql_fetch_row($resMail))
				{
					$mail=$fMail[0];

					$nCuerpo=str_replace("_nombreRevisor",$nombre,$cuerpoMail);
					$nCuerpo=str_replace("_usuario",$login,$nCuerpo);
					$nCuerpo=str_replace("_contrasena",$password,$nCuerpo);
					
					
					$obj=array();
					$obj[0]=$fResp[0];
					$obj[1]=$nombre;
					if($prueba)
						$mail="novant1730@hotmail.com";
					$obj[2]=$mail;
					
					if($mail!="")
					{
						if(!enviarMail($mail,"CENSIDA SMAP CUENTA DE ACCESO",$nCuerpo,"soporteSMAP@grupolatis.net","CENSIDA",$arrAchivos,NULL))			
						{
							
							array_push($arrUsrProblemas,$obj);
						}
						else
						{
							array_push($arrUsrOk,$obj);	
						}
					}
				}
				
			}
			
			
			
		}
		
		
		
		echo "Usuarios Mal: ".sizeof($arrUsrProblemas)."<br>";
		varDump($arrUsrProblemas);
		echo "Usuarios OK: ".sizeof($arrUsrOk)." <br>";
		varDump($arrUsrOk);
	}
	
	
	function pruebaRendererMenu()
	{
		return '<iframe style=\'width:400px; height:350px;\' scrolling=\'no\' src=\'http://censida.grupolatis.net\'></iframe>';
	}
	
	function enviarCorreosOSCFinanciados()
	{
		global $con;
		$arrAchivos=array();
		$arrAchivos[0][0]="../recursos/comunicado14022014.pdf";
		$arrAchivos[0][1]="Comunicado14022014.pdf";
		$prueba=false;
		$arrUsrProblemas=array();
		$arrUsrOk=array();
		
		$cuerpoMail="
					<table width=\"800\"><tr><td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/logoSalud.gif\"></td>
								<td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/censidaLogo.gif\"></td></tr></table>
								<br><br><h1>Centro Nacional para La Prevención y el<br>
								Control Del VIH/SIDA</h1><br>
								<br><br>	
								<span style='font-size:12px'>
								<br><b>Estimada OSC/IA:</b><br><br>	
									Por este medio se envía el comunicado adjunto para su difusión.
								</span>
								<br><br>
					";

	
	
		/*$cuerpoMail="
					<table width=\"800\"><tr><td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/logoSalud.gif\"></td>
								<td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/censidaLogo.gif\"></td></tr></table>
								<br><br><h1>Centro Nacional para La Prevención y el<br>
								Control Del VIH/SIDA</h1><br>
								<br><br>	
								<span style='font-size:12px'>
								<br><b>Estimadas OSC e IA:</b><br><br>	
									Por este medio se le notifica que algunos comprobantes enviados a través de la plataforma SMAP, no han podido ser validados debido a que el archivo recibido no es visible(posiblemente esté corrupto), por lo cual le se le solicita sean enviados nuevamente a través de la plataforma,
									a continuación se listan aquellos comprobantes con problemas:
									
									{tabla}
									
									Sin nada más que agregar y agradadeciendo su apoyo el CENSIDA le desea un buen día
								</span>
								<br><br>
					";*/

		
		$consulta="SELECT idUsuario FROM 801_adscripcion WHERE Institucion IN
				(SELECT codigoInstitucion FROM _410_tablaDinamica WHERE marcaAutorizado=1)";
				
		
		//$consulta="SELECT idUsuario,Institucion FROM 801_adscripcion WHERE Institucion IN
			//	(SELECT DISTINCT t.codigoInstitucion FROM 101_comprobantesPresupuestales c,_410_tablaDinamica t WHERE c.idFormulario=410 AND c.situacion=10 and t.id__410_tablaDinamica=c.idReferencia)";

				
		if($prueba)
			$consulta.=" limit 0,1";
		$res=$con->obtenerFilas($consulta);	
		while($filaUsr=mysql_fetch_row($res))
		{
			
		/*	$tablaFacturas="<table>";
			$tablaFacturas.="<tr><td><b>Proyecto</b></td><td><b>Concepto</b></td><td><b>Descripción comprobación</b></td><td><b>Monto comprobación</b></td><td><b>Tipo de comprobante</b></td><td><b>Folio del comprobante</b></td><td><b>RFC Proveedor</b></td><td><b>Proveedor</b></td></tr>";
			$consulta="SELECT distinct t.id__410_tablaDinamica,codigo FROM 101_comprobantesPresupuestales c,_410_tablaDinamica t WHERE c.idFormulario=410 AND c.situacion=10 and t.id__410_tablaDinamica=c.idReferencia and t.codigoInstitucion='".$filaUsr[1]."'";
			$resProy=$con->obtenerFilas($consulta);
			while($filaProyecto=mysql_fetch_row($resProy))
			{
				$consulta="SELECT g.calculo,c.descripcion,c.montoComprobacion,t.comprobante,f.folioComprobante,CONCAT(rfc1,'-',rfc2,'-',rfc3) AS rfc,pr.razonSocial 
							FROM 102_conceptosComprobacion c,100_calculosGrid g, 101_comprobantesPresupuestales f,595_proveedores pr,106_tipoComprobante t
							WHERE c.situacion=10 AND g.idGridVSCalculo=c.idConcepto AND f.idFactura=c.idFactura AND pr.idProveedor=f.idProveedor AND t.idTipoComprobante=f.tipoComprobante and f.idReferencia=".$filaProyecto[0];
				$resConcepto=$con->obtenerFilas($consulta);
				while($fConcepto=mysql_fetch_row($resConcepto))
				{
					$tablaFacturas.="<tr><td>".$filaProyecto[1]."</td><td>".$fConcepto[0]."</td><td>".$fConcepto[1]."</td><td>".$fConcepto[2]."</td><td>".$fConcepto[3]."</td><td>".$fConcepto[4]."</td><td>".$fConcepto[5]."</td><td>".$fConcepto[6]."</td><td>".$fConcepto[7]."</td></tr>";
				}
	
			}
			
			$tablaFacturas.="</tr></table>";*/
			
			
			
			$consulta="SELECT u.idUsuario,Nombre,m.Mail FROM 800_usuarios u,805_mails m WHERE m.idUsuario=u.idUsuario AND u.idUsuario=".$filaUsr[0];
			$rMail=$con->obtenerFilas($consulta);
			while($fila=mysql_fetch_row($rMail))
			{
				
				$idUsuario=$fila[0];
				$nombre=$fila[1];
				$mail=$fila[2];
				if($mail!="")
				{
					
					
					if($prueba)
						$mail="novant1730@hotmail.com";
					
					$obj=array();
					$obj[0]=$idUsuario;
					$obj[1]=$nombre;
					
					$obj[2]=$mail;
					
					$cuerpoMail2=str_replace("{tabla}",$tablaFacturas,$cuerpoMail);
					
					if(!enviarMail($mail,"Comunicado Censida",$cuerpoMail2,"soporteSMAP@grupolatis.net","CENSIDA",$arrAchivos))			
					{
						
						array_push($arrUsrProblemas,$obj);
					}
					else
					{
						array_push($arrUsrOk,$obj);	
					}
				}
			}
		}
		echo "Usuarios Mal: ".sizeof($arrUsrProblemas)."<br>";
		varDump($arrUsrProblemas);
		echo "Usuarios OK: ".sizeof($arrUsrOk)." <br>";
		varDump($arrUsrOk);

	}//342,61	
	
	
	function enviarCorreosDocumentosOSC($osc)
	{
		global $con;
		$arrAchivos=array();
		$prueba=false;
		$arrUsrProblemas=array();
		$arrUsrOk=array();
		
		
		
		$consulta="SELECT marcaAutorizacion,comentariosAdicionales FROM 1029_situacionEvaluacionOSC2014 WHERE codigoInstitucion='".$osc."'";
		$fResultado=$con->obtenerPrimeraFila($consulta);
		
		$lblResultado="";
		switch($fResultado[0])
		{
			case 1:
				$lblResultado="Aceptada";
			break;
			case 2:
				$lblResultado="Rechazada";
			break;
			case 3:
				$lblResultado="En espera de documentos";
			break;
			case 4:
				$lblResultado="Aceptada condicionada a cambio de documentos";
			break;
			case 5:
				$lblResultado="Rechazada";
			break;
		}
		
		$consulta="select upper(unidad) from 817_organigrama where codigoUnidad='".$osc."'";
		$nombreOSC=$con->obtenerValor($consulta);
		
		
		
		$consulta="SELECT id__367_tablaDinamica,tipoOrganizacion FROM _367_tablaDinamica WHERE codigoInstitucion='".$osc."'";
		
		$fDatosOrganizacion=$con->obtenerPrimeraFila($consulta);
		$idRegistro=$fDatosOrganizacion[0];
		$tOrganizacion=$fDatosOrganizacion[1];
		$registros="";
		$numReg=0;
		$tblDocumentos="";
		if($idRegistro!="")
		{
			
			$consulta="SELECT id__408_tablaDinamica,tituloDocumento FROM _408_tablaDinamica where tiporequerido=1 ";
			if($tOrganizacion==1)
				$consulta.=" and aplicaOSC=1";
			else
				$consulta.=" and aplicaIA=1";
			$consulta.=" ORDER BY tituloDocumento";
			$res=$con->obtenerFilas($consulta);
			
			while($fila=mysql_fetch_row($res))
			{
				$consulta="SELECT documentoAnexo,fechaRegistro FROM _407_documentosRequeridosOSC d,_407_tablaDinamica t WHERE
						d.idReferencia=t.id__407_tablaDinamica AND t.idReferencia=".$idRegistro." AND d.tituloDocumento=".$fila[0]." 
						order by fechaRegistro desc";
	
				$fDocumento=$con->obtenerPrimeraFila($consulta);
				
				
				$consulta="SELECT evaluacion,comentariosAdicionales FROM 1028_situacionDocumentosOSC2014 WHERE organizacion='".$osc."' AND idDocumento=".$fila[0];
				$fEval=$con->obtenerPrimeraFila($consulta);
				$lblDictamen="";
				switch($fEval[0])
				{
					case 1:
						$lblDictamen="Aceptado";
					break;
					case 2:
						$lblDictamen="Rechazado";
					break;
				}
				
				$tblDocumentos.="
									<tr>
										<td>".$fila[1]."</td>
										<td>".$lblDictamen."</td>
										<td>".$fEval[1]."</td>
									</tr>
									<tr height='10'>
										<td colspan='3'></td>
									
									</tr>
								";
			}
		}
		
		
		$cuerpoMail="
					<table width=\"800\"><tr><td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/logoSalud.gif\"></td>
								<td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/censidaLogo.gif\"></td></tr></table>
								<br><br><h1>Centro Nacional para La Prevención y el<br>
								Control Del VIH/SIDA</h1><br>
								<br><br>	
								<span style='font-size:12px'>
								<br><b>A quien corresponda:</b><br><br>	
									Por este medio se envía el resultado de la evaluaci&oacute;n de los documentos registrados en la plataforma SMAP bajo el nombre de la organizaci&oacute;n: <b>".$nombreOSC."</b><br><br>
									<table width='750'>
										<tr>
											<td width='220'>
											<b>Resultado de la evaluaci&oacute;n:</b>
											</td>
											<td width='530'>
											".$lblResultado."
											</td>
										</tr>
										<tr>
											<td>
											<b>Comentarios adicionales:</b>
											</td>
											<td>
											".$fResultado[1]."
											</td>
										</tr>
									</table><br><br>
									
									
									Resultado de evaluación de documentos:<br><br>
									<table width='750'>
									<tr>
										<td width='250'><b>Documento</b></td>
										<td width='200'><b>Resultado</b></td>
										<td width='400'><b>Comentarios</b></td>
									</tr>
									".$tblDocumentos."
									</table>
									
									<br><br>";
									
									if($fResultado[0]==5)
										$cuerpoMail.="Cabe mencionar que con este resultado de evaluación, ya NO es posible actualizar la documentación.<br>";
									$cuerpoMail.="Cualquier duda puede dirigirla a conv2014censida@gmail.com indicando el nombre de su organización y comentario
									
										
								</span>
								<br><br>
					";

	
	
		
//<b>Esta operación está disponible sólo si su evaluación como organización es diferentes a Rechazada</b>
		
		$consulta="SELECT idUsuario FROM 801_adscripcion WHERE Institucion IN ('".$osc."')";
				
		
		//$consulta="SELECT idUsuario,Institucion FROM 801_adscripcion WHERE Institucion IN
			//	(SELECT DISTINCT t.codigoInstitucion FROM 101_comprobantesPresupuestales c,_410_tablaDinamica t WHERE c.idFormulario=410 AND c.situacion=10 and t.id__410_tablaDinamica=c.idReferencia)";

				
		if($prueba)
			$consulta.=" limit 0,1";
		$res=$con->obtenerFilas($consulta);	
		while($filaUsr=mysql_fetch_row($res))
		{
			$consulta="SELECT u.idUsuario,Nombre,m.Mail FROM 800_usuarios u,805_mails m WHERE m.idUsuario=u.idUsuario AND u.idUsuario=".$filaUsr[0];
			$rMail=$con->obtenerFilas($consulta);
			while($fila=mysql_fetch_row($rMail))
			{
				
				$idUsuario=$fila[0];
				$nombre=$fila[1];
				$mail=$fila[2];
				if($mail!="")
				{
					
					
					if($prueba)
						$mail="novant1730@hotmail.com";
					
					$obj=array();
					$obj[0]=$idUsuario;
					$obj[1]=$nombre;
					
					$obj[2]=$mail;
					
					$cuerpoMail2=str_replace("{tabla}",$tablaFacturas,$cuerpoMail);
					
					if(!enviarMail($mail,"CENSIDA Resultado de Revision de Documentos",$cuerpoMail2,"soporteSMAP@grupolatis.net","CENSIDA",$arrAchivos))			
					{
						
						array_push($arrUsrProblemas,$obj);
					}
					else
					{
						array_push($arrUsrOk,$obj);	
					}
				}
			}
		}
		return true;
		/*echo "Usuarios Mal: ".sizeof($arrUsrProblemas)."<br>";
		varDump($arrUsrProblemas);
		echo "Usuarios OK: ".sizeof($arrUsrOk)." <br>";
		varDump($arrUsrOk);*/

	}
	
	
	function generarRolHorarioRutaDia($idRuta,$fecha,$sobreEscribir=false)
	{
		global $con;
		$x=0;
		$query[$x]="begin";
		$x++;
		if($sobreEscribir)
		{
			$query[$x]="delete from 3105_horarioEjecucionRuta where idRuta=".$idRuta." and fecha='".$fecha."'";
			$x++;
		}
		else
		{
			$consulta="	select count(*) from 3105_horarioEjecucionRuta where idRuta=".$idRuta." and fecha='".$fecha."'";
			$nReg=$con->obtenerValor($consulta);
			if($nReg>0)
				return true;
		}
		

		$dia=date("w",strtotime($fecha));
		
		$consulta="SELECT idPerfilHorario FROM 3102_perfilHorarioRuta WHERE idRuta=".$idRuta." AND fechaInicio<='".$fecha."' and (fechaInicio<=fechaFin OR fechaFin IS NULL)
					 ORDER BY fechaInicio DESC";	
		$idPerfil=$con->obtenerValor($consulta);
		if($idPerfil=="")
			$idPerfil=-1;
		if($idPerfil!=-1)
		{
			
			$consulta="SELECT * FROM 3103_horariosPerfilRuta WHERE idPerfilHorarioRuta=".$idPerfil." AND dia=".$dia;
			$resFechas=$con->obtenerFilas($consulta);
			
			while($fila=mysql_fetch_row($resFechas))
			{
				$query[$x]="INSERT INTO 3105_horarioEjecucionRuta(idRuta,fecha,idHorario,dia) VALUES(".$idRuta.",'".$fecha."',".$fila[0].",".$dia.")";
				$x++;
			}
		}	
		$query[$x]="commit";
		$x++;
		
		return $con->ejecutarBloque($query);
	}
	
	
	
?>