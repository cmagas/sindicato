<?php include("latis/PHPWord.php");
	include_once("latis/zip.lib.php"); 
	function asignarProyectosRevisor2014()
	{
		global $con;
		$continuar=true;
		$nCiclos=0;
		
		/*$arrCategorias[1]=4;
		$arrCategorias[2]=6;
		$arrCategorias[3]=9;
		$arrCategorias[4]=5;
		$arrCategorias[5]=8;
		$arrCategorias[6]=7;*/
		
		/*
		$arrCategorias[5]=6;
		$arrCategorias[6]=4;
		$arrCategorias[4]=9;
		$arrCategorias[3]=5;
		$arrCategorias[2]=8;
		$arrCategorias[1]=7;
		;
		*/
		
		
		/*
		$arrCategorias[4]=9;
		$arrCategorias[6]=4;
		*/
		
		//
		//
		//
		//$arrCategorias[3]=5;
		//$arrCategorias[2]=8;
		//$arrCategorias[1]=7;
		
		
		/*
		
		


		
		*/
		
		
		
		//$arrCategorias[6]=7;
		//$arrCategorias[5]=8;
		//$arrCategorias[4]=5;
		//
		//
		//$arrCategorias[1]=4;
		//$arrCategorias[2]=6;
		$arrCategorias[3]=9;
		ksort($arrCategorias);
		
		foreach($arrCategorias as $orden=>$idCategoria)
		{
			$continuar=true;
			while($continuar)
			{
				$consulta="SELECT * FROM (
							SELECT id__448_tablaDinamica,(SELECT COUNT(*) FROM 1011_asignacionRevisoresProyectos WHERE idProyecto=t.id__448_tablaDinamica AND idFormulario=448) AS nAsignaciones,(idCategoria-3) as categoria 
							FROM _448_tablaDinamica t WHERE idEstado=2 and idCategoria=".$idCategoria." ) AS tmp WHERE nAsignaciones<5";

				if($con->filasAfectadas==0)
					$continuar=false;
				$resProy=$con->obtenerFilas($consulta);
				while($fProy=mysql_fetch_row($resProy))
				{
					$consulta="SELECT idUsuario FROM 1011_asignacionRevisoresProyectos WHERE idFormulario=448 AND idProyecto=".$fProy[0];
					$listUsuarios=$con->obtenerListaValores($consulta);
					if($listUsuarios=="")
						$listUsuarios=-1;
						
						
						
						
					$consultaAux='SELECT d.idUsuario FROM 1010_distribucionRevisoresProyectos d,relacionCategoria c WHERE idFormulario=448 and numProyAsignados < numMax AND situacion=1 and d.idUsuario not in ('.$listUsuarios.') 
								and c.idUsuario=d.idUsuario and c.categoria='.$fProy[2].' order by numProyAsignados';

					$fila=$con->obtenerPrimeraFila($consultaAux);
					if($fila)
					{
						$consulta="INSERT INTO 1011_asignacionRevisoresProyectos(idUsuario,idCategoria,idProyecto,tipoRevisor,idFormulario,situacion,fechaAsignacion)
											VALUES(".$fila[0].",".$fProy[2].",".$fProy[0].",1,448,1,'".date("Y-m-d H:i:s")."')";
						if($con->ejecutarConsulta($consulta))				
						{
							$consulta="update 1010_distribucionRevisoresProyectos set numProyAsignados=numProyAsignados+1 where idUsuario=".$fila[0]." and idFormulario=448";
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
				if($nCiclos>20)
					$continuar=false;
				
			}
			

			
		}
	}
	
	function enviarCorreosGeneralMasiva()
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
	
		$cuerpoMail="
		<table width=\"800\"><tr><td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/logoSalud.gif\"></td>
								<td width=\'250\' align=\"center\"><img width=\"75%\" height=\"75%\"  src=\"http://censida.grupolatis.net/images/censida/FIRMA_ELECTRONICAaaa.png\"></td></tr></table>
								<br><br><h1>Centro Nacional para La Prevención y el<br>
								Control Del VIH/SIDA</h1><br>
								<br><br>	
Estimado/a _nombre:<br><br>

Por este medio se le hace llegar sus datos de acceso a la plataforma SMAP (<a href=\"http://censida.grupolatis.net\">http://censida.grupolatis.net</a>) 
a fin de llevar acabo la evaluación presupuestal de los proyectos participAntes en la convocatoria 2014 del CENSIDA y que de acuerdo a los resultados de sus evaluaciones son suceptibles a financiamiento. Sus datos de acceso son:<br><br>
<b>Usuario:</b> _usuario<br>
<b>Password:</b> _contrasena<br><br>

Sólo cabe mencionar que si en el listado de proyectos en alguna columna de evaluaci&oacute;n se observa una imagen de alerta (ícono rojo) su significado es que el revisor ha indicado que el nombre de la OSC/IA autora es identificable dentro del proyecto, usted deberá confirmar dicho reporte y corroborarlo o no dentro de la evaluación final del mismo.
<br><br>



<br><br><br>";		
		$arrCopiaOculta=NULL;
		$comp="";
		$arrDestinatarios=array();
		if(!$prueba)
		{
			
		}
		else
		{
			
			$comp=" limit 0,1";
		}
		
		$consulta="SELECT Mail,m.idUsuario FROM 805_mails m,800_usuarios u WHERE u.idUsuario=m.idUsuario and m.idUsuario IN 
					(
						1425,
						1434,
						1431,
						1424,
						1426,
						1437,
						1428,
						1429,
						1423,
						1592,
						1593,
						1594,
						1595

					) ".$comp;
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
		
		$arrUsrProblemas=array();
		$arrUsrOk=array();
		foreach($arrDestinatarios as $d)
		{
			$consulta="SELECT Login,PASSWORD,Nombre FROM 800_usuarios WHERE idUsuario=".$d[0];
			$fUsr=$con->obtenerPrimeraFila($consulta);
			$cuerpoMailFinal=str_replace("_usuario",$fUsr[0],$cuerpoMail);
			$cuerpoMailFinal=str_replace("_contrasena",$fUsr[1],$cuerpoMailFinal);
			$cuerpoMailFinal=str_replace("_nombre",$fUsr[2],$cuerpoMailFinal);
			if(!enviarMail($d[2],"CENSIDA Evaluacion Presupuestal",$cuerpoMailFinal,"soporteSMAP@grupolatis.net","",$arrAchivos,NULL))			
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

	}
	
	function enviarCorreosAvisoOSCV3Masiva()
	{
		global $con;
		$prueba=true;
		$arrAchivos=array();
		$arrAchivos[0][0]="../recursos/Convo_Estrategica_2016.pdf";
		$arrAchivos[0][1]="Convocatoria estrategica 2016.pdf";
		
		$arrUsrProblemas=array();
		$arrUsrOk=array();
		
		$cuerpoMail="
		<table width=\"800\"><tr><td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/logoSalud.gif\"></td>
								<td width=\'250\' align=\"center\"><img width=\"75%\" height=\"75%\"  src=\"http://censida.grupolatis.net/images/censida/FIRMA_ELECTRONICAaaa.png\"></td></tr></table>
								<br><br><h1>Centro Nacional para La Prevención y el<br>
								Control Del VIH y el sida</h1><br>
								<br><br>	<span style='font-size:12px'>
A las organizaciones de la sociedad civil e instituciones académicas vinculadas al trabajo de prevención y control del VIH y el sida, se les informa que el \"Informe de la convocatoria pública 2014\" se encuentra disponible para consulta en el sitio de internet del Censida.
</span>
<br><br><br>";		
		
		$cuerpoMail="<table width=\"800\"><tr><td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/logoSalud.gif\"></td>
								<td width=\'250\' align=\"center\"><img width=\"75%\" height=\"75%\"  src=\"http://censida.grupolatis.net/images/censida/FIRMA_ELECTRONICAaaa.png\"></td></tr></table>
								<br><br><h1>Centro Nacional para La Prevención y el<br>
								Control Del VIH y el sida</h1><br>
								<br><br>	<span style='font-size:12px'>		
								Estimadas Organizaciones de la Sociedad Civil, Instituciones Académicas y Centros de Investigación:<br><br>

								Censida informa que el plazo para presentar proyectos en la  <b>\"Convocatoria pública dirigida a organizaciones de la sociedad civil (OSC), instituciones académicas (IA) y centros de
								investigación (CI) interesados en presentar proyectos que coadyuven a reforzar la rectoría del Centro nacional para la
								prevención y el control del VIH y el sida (Censida) mediante actividades de monitoreo de proyectos de prevención,
								desarrollo de herramientas de seguimiento, investigación, diagnóstico y otras intervenciones para fortalecer la
								respuesta nacional ante el VIH y el sida 2014\"</b> se extiende hasta el día viernes 16 de mayo a las 10:00 a.m.<br><br><br>
								</span>";


		$cuerpoMail="<table width=\"800\"><tr><td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/logoSalud.gif\"></td>
								<td width=\'250\' align=\"center\"><img width=\"75%\" height=\"75%\"  src=\"http://censida.grupolatis.net/images/censida/FIRMA_ELECTRONICAaaa.png\"></td></tr></table>
								<br><br><h1>Centro Nacional para La Prevención y el<br>
								Control Del VIH y el sida</h1><br>
								<br><br>	<span style='font-size:12px'>		
								<b>Coordinadores/as de proyecto.</b><br><br>
								Por este medio se les notifica que ya pueden enviar las facturas correspondientes a la tercera ministraci&oacute;n a sus respectivos supervisores, siempre y cuando se encuentren al d&iacute;a en sus comprobaciones t&eacute;cnicas y financieras.<br><br>

<br><br>



Atentamente<br><br>

<b>Dirección de Prevención y Participación Social</b>
<br><br><br>
								</span>";
								
								



		/*$cuerpoMail="<table width=\"800\"><tr><td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/logoSalud.gif\"></td>
								<td width=\'250\' align=\"center\"><img width=\"75%\" height=\"75%\"  src=\"http://censida.grupolatis.net/images/censida/FIRMA_ELECTRONICAaaa.png\"></td></tr></table>
								<br><br><h1>Centro Nacional para La Prevención y el<br>
								Control Del VIH y el sida</h1><br>
								<br><br>	<span style='font-size:12px'>		
								<b>_nombreOSC_:</b><br><br>

								Por este medio se le informa que se ha asignado a un miembro del CENSIDA cuya función será llevar a cabo la supervisión de sus proyectos financiados, también podrá referirse a esta persona para dudas que puedieran surgirle sobre sus proyectos.<br><br>
								A continuación se muestran los datos de su supervisor:<br><br>
								<b>Nombre:</b> _nombreSupervisor_<br>
								<b>E-mail de contacto:</b> _EmailSupervisor_<br><br>
								</span>";	*/
								
	/*$cuerpoMail="<table width=\"800\"><tr><td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/logoSalud.gif\"></td>
								<td width=\'250\' align=\"center\"><img width=\"75%\" height=\"75%\"  src=\"http://censida.grupolatis.net/images/censida/FIRMA_ELECTRONICAaaa.png\"></td></tr></table>
								<br><br><h1>Centro Nacional para La Prevención y el<br>
								Control Del VIH y el sida</h1><br>
								<br><br>	<span style='font-size:12px'>		
								<b>Estimadas y estimados coordinadores de proyecto,</b><br><br>

								Anexo al presente materiales de comunicación en formato editable, con la finalidad de que utilicen el diseño, colores e imágenes en aquellos materiales comunicativos cuyo contenido es distinto a los disponibles en el SMAP, aplica para guías, lonas, volantes, posters, y todos los materiales de comunicación e información desarrollados en el proyecto.<br><br>
								
								No omito mencionarles que el Censida requiere unificar la imagen institucional para todos los materiales que se utilizarán en los proyectos financiados 2014.<br><br>
								 
								
								Puede descargarlo dando click sobre la opción \"Descargar\" que se encuentra en el menú \"Archivo\".<br><br>
								 
								<a href='https://docs.google.com/file/d/0B6TBnQhWL2aWQUM4YzFJeGVDTk0/edit?usp=3Ddri=ve_web'>CONDON frente.ai</a><br><br> 
								
								<a href='https://docs.google.com/file/d/0B6TBnQhWL2aWdUtERVZXbHNlaGM/edit?usp=3Ddri=ve_web'>CONDON vuelta.ai</a><br><br> 
								
								<a href='https://docs.google.com/file/d/0B6TBnQhWL2aWUDgzRlJ6b2ZMbWc/edit?usp=3Ddri=ve_web'>EMBARAZO frente.ai</a><br><br> 
								
								<a href='https://docs.google.com/file/d/0B6TBnQhWL2aWckh1ak01bDBaNXM/edit?usp=3Ddri=ve_web'>EMBARAZO vuelta.ai</a><br><br> 
								
								<a href='https://docs.google.com/file/d/0B6TBnQhWL2aWWWdlWlVaSlRhRFk/edit?usp=3Ddri=ve_web'>ITS frente.ai</a><br><br> 
								
							 	<a href='https://docs.google.com/file/d/0B6TBnQhWL2aWQWhhc2hQV0hwUHM/edit?usp=3Ddri=ve_web'>ITS vuelta.ai</a><br><br> 
								
								<a href='https://docs.google.com/file/d/0B6TBnQhWL2aWX0U2eUVUQjJXRjg/edit?usp=3Ddri=ve_web'>PRUEBA frente.ai</a><br><br> 
								
								<a href='https://docs.google.com/file/d/0B6TBnQhWL2aWdEpuQXlWOVFhOWM/edit?usp=3Ddri=ve_web'>PRUEBA vuelta.ai</a><br><br> 
								
								<a href='https://docs.google.com/file/d/0B6TBnQhWL2aWdW9uenlNRHVUWUk/edit?usp=3Ddri=ve_web'>VIH frente.ai</a><br><br> 
								
								<a href='https://docs.google.com/file/d/0B6TBnQhWL2aWcDRrSEQ4MzVtdmc/edit?usp=3Ddri=ve_web'>VIH vuelta.ai</a><br><br> 
								
								<a href='https://docs.google.com/file/d/0B6TBnQhWL2aWeFJtZDlmWjgzUDQ/edit?usp=3Ddri=ve_web'>VIVIR CON VIH frente.ai</a><br><br> 
								
							 	<a href='https://docs.google.com/file/d/0B6TBnQhWL2aWZ25nLWhiUURWWWc/edit?usp=3Ddri=ve_web'>VIVIR CON VIH vuelta.ai</a><br><br> 
								
								Atentamente<br><br>
								
								<b>Dirección de Prevención y Participación Social</b><br><br><br><br>
								</span>";	*/								
								
								
	/*$cuerpoMail="<table width=\"800\"><tr><td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/logoSalud.gif\"></td>
								<td width=\'250\' align=\"center\"><img width=\"75%\" height=\"75%\"  src=\"http://censida.grupolatis.net/images/censida/FIRMA_ELECTRONICAaaa.png\"></td></tr></table>
								<br><br><h1>Centro Nacional para La Prevención y el<br>
								Control Del VIH y el sida</h1><br>
								<br><br>	<span style='font-size:12px'>		
								<b>_nombreOSC_</b><br><br>							
								
								Por este medio se le informa que para la firma de convenio usted deberá presentar la siguiente documentación:<br><br>
								
								<b>En original</b><br><br>
								- Acta constitutiva<br>
								- Ultima acta protocolaria (En caso de existir)<br>
								- Identificación oficial del representante legal<br><br>
								
								<b>2 copias</b><br><br>
								
								- Comprobante de domicilio<br>
								- Identificación oficial del representante legal<br>
								- Clave Única de Inscripción CLUNI<br>
								- Registro Federal de Contribuyentes (RFC)<br>
								- Estado de cuenta bancaria a nombre de la OSC donde se visualice la CLABE interbancaria<br>
								- Versión impresa del proyecto (Deberá se descargada del SMAP, ingresando al proyecto y presionando el botón \"Versión imprimible\" del menú \"Descargas\")
								<br><br>
								
								El convenio deberá ser firmado por el representante legal de la organización u otra persona falcultada para tal acción.<br><br>
								
								Se informa además el monto autorizado de cada proyecto por el CENSIDA:<br><br>
								
								_tablaProyectos_<br><br>
								
								Para observar más detalles del presupuesto autorizado usted deberá:<br>
								a) Ingresar al SMAP<br>
								b) Seleccionar el proyecto cuya información desea verificar<br>
								c) Presionar el botón \"Abrir expediente del proyecto\"<br>
								d) Ingresar a la sección \"VI. Presupuesto autorizado\" <br><br>
								
								
								<br><br>
								<br><br>
								</span>";*/
								
								
		

		$cuerpoMail="<table width=\"800\"><tr><td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/logoSalud.gif\"></td>
								<td width=\'250\' align=\"center\"><img width=\"75%\" height=\"75%\"  src=\"http://censida.grupolatis.net/images/censida/FIRMA_ELECTRONICAaaa.png\"></td></tr></table>
								<br><br><h1>Centro Nacional para La Prevención y el<br>
								Control Del VIH y el sida</h1><br>
								<br><br>	<span style='font-size:12px'>		
								Estimada/o _nombre:<br><br>

								Gracias por participar en esta etapa de la <b>\"Convocatoria pública dirigida a organizaciones de la sociedad civil (OSC), instituciones académicas (IA) y centros de
								investigación (CI) interesados en presentar proyectos que coadyuven a reforzar la rectoría del Centro nacional para la prevención y el control del VIH y el sida (Censida) 
								mediante actividades de asesoría de proyectos de prevención, desarrollo de herramientas de seguimiento, investigación, diagnóstico y otras intervenciones para fortalecer 
								la respuesta nacional ante el VIH y el sida 2015.\"</b><br><br>
								
								Le informamos que le han sido asignados proyectos para su evaluación, para realizar esta tarea usted deberá:<br><br>
								a) Ingresar a la plataforma SMAP (<a href=\"http://censida.grupolatis.net/principalCensida/acceso.php\">http://censida.grupolatis.net/principalCensida/acceso.php</a>) con su cuenta de acceso.<br><br>
								b) Seleccionar del \"Listado de proyectos en espera de evaluación\" el proyecto a evaluar.<br><br>
								c) Presionar el botón \"Evaluar proyecto\".<br><br>
								d) Deberá leer el proyecto (Usted puede descargar una versión del proyecto en formato Microsoft Word dando click sobre el botón \"Descargar versión imprimible\") deberá dar click sobre la pestaña \"Cuestionario de evaluación\".<br><br>
								c) Una vez contestadas todas las preguntas, deberá dar click sobre el botón \"Guardar\".<br><br>
								
								<br><br>
								
								A continuación se muestran sus datos de acceso:<br><br>
								<b>Usuario:</b> _usuario<br>
								<b>Password:</b> _contrasena<br><br>
								</span>";		
								
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
		
		/*
		and m.idUsuario in 
					(
						SELECT DISTINCT a.idUsuario FROM _448_tablaDinamica t,801_adscripcion a WHERE t.convenioFirmado=1
						AND a.institucion=t.codigoInstitucion

					)
		*/
		/*$nRegistros=0;
		
		$consulta="SELECT DISTINCT s.idUsuario,u.nombre,t.codigoInstitucion,(SELECT GROUP_CONCAT(Mail) FROM 805_mails WHERE idUsuario=s.idUsuario) AS mail FROM 1038_supervisionProyectos2014 s,_448_tablaDinamica t,
					800_usuarios u WHERE t.id__448_tablaDinamica=s.idProyecto AND u.idUsuario=s.idUsuario";
		$resResponsables=$con->obtenerFilas($consulta);
		while($fResponsable=mysql_fetch_row($resResponsables))
		{
			
			$consulta="SELECT organizacion FROM _367_tablaDinamica WHERE codigoInstitucion='".$fResponsable[2]."'";
			$nombreOSC=$con->obtenerValor($consulta);

			$arrDestinatarios=array();
			$consulta="SELECT distinct Mail,m.idUsuario FROM 805_mails m,800_usuarios u WHERE u.idUsuario=m.idUsuario and u.cuentaActiva=1 and m.idUsuario IN (
					SELECT u.idUsuario FROM 807_usuariosVSRoles u,801_adscripcion a WHERE a.idUsuario=u.idUsuario and a.Institucion='".$fResponsable[2]."' and idRol IN (37,62,94))";	
					
			$rOSC=$con->obtenerFilas($consulta);

			while($fOSC=mysql_fetch_row($rOSC))
			{
				$mail=trim($fOSC[0]);
				$obj=array();
				if($prueba)
					$mail="novant1730@hotmail.com";
				$obj[0]=$fOSC[1];
				$obj[1]="";
				$obj[2]=$mail;
				$obj[3]=obtenerNombreUsuario($fOSC[1]);
				array_push($arrDestinatarios,$obj);		
			}
			
			foreach($arrDestinatarios as $d)
			{
				$consulta="SELECT Login,PASSWORD,nombre FROM 800_usuarios WHERE idUsuario=".$d[0];
				$fUsr=$con->obtenerPrimeraFila($consulta);
				$cuerpoMailFinal=str_replace("_nombreOSC_",$nombreOSC,$cuerpoMail);
				$cuerpoMailFinal=str_replace("_nombreSupervisor_",$fResponsable[1],$cuerpoMailFinal);
				$cuerpoMailFinal=str_replace("_EmailSupervisor_",$fResponsable[3],$cuerpoMailFinal);
				
				
				
				if(!enviarMail($d[2],"CENSIDA - Notificacion Supervisor de proyectos asignado",$cuerpoMailFinal,"soporteSMAP@grupolatis.net","",$arrAchivos,NULL))			
				{
					
					array_push($arrUsrProblemas,$d);
				}
				else
				{
					array_push($arrUsrOk,$d);	
				}
				
				if($prueba)
					return;
			}
			
			$nRegistros++;
			
		}*/
		
		
		$cuerpoMail="<table width=\"800\"><tr><td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/logoSalud.gif\"></td>
								<td width=\'250\' align=\"center\"><img width=\"75%\" height=\"75%\"  src=\"http://censida.grupolatis.net/images/censida/FIRMA_ELECTRONICAaaa.png\"></td></tr></table>
								<br><span style='font-size:12px'>		
													
								
								Estimadas OSC/IA/CI:<br><br>
								Por este medio se les hace llegar la <b>\"CONVOCATORIA PÚBLICA PARA PROYECTOS QUE COADYUVEN A REFORZAR LA RECTORÍA DEL CENTRO NACIONAL PARA LA PREVENCIÓN Y EL CONTROL DEL VIH Y EL SIDA (CENSIDA) 2016\"</b> para su conocimiento y difusión.

								<br><br>
								<br><br>
								</span>";
		
		
		$consulta="SELECT distinct Mail,m.idUsuario FROM 805_mails m,800_usuarios u WHERE u.idUsuario=m.idUsuario and u.cuentaActiva=1 
					and m.idUsuario IN (
						SELECT idUsuario FROM 807_usuariosVSRoles WHERE idRol IN (37,62,94,102)
						) 

				
					
				
					".$comp; //
					
					/*
						and m.idUsuario in 
							(
							SELECT idUsuario FROM 801_adscripcion a,_522_tablaDinamica t 
							WHERE a.Institucion=t.codigoInstitucion AND t.marcaAutorizado=1
							and t.suspendido=0
							)
					*/
					
			
			/*
			
			and m.idUsuario in 
							(
							SELECT idUsuario FROM 801_adscripcion a,_498_tablaDinamica t 
							WHERE a.Institucion=t.codigoInstitucion AND t.marcaAutorizado=1
							and t.descalificado=0
							)
			
			$consulta="SELECT distinct Mail,m.idUsuario FROM 805_mails m,800_usuarios u WHERE u.idUsuario=m.idUsuario and u.cuentaActiva=1 
					and m.idUsuario IN (
						SELECT idUsuario FROM 807_usuariosVSRoles WHERE idRol IN (37,62,94,102)
						) 

					
					".$comp; //
			/*$consulta="SELECT distinct Mail,m.idUsuario FROM 805_mails m,800_usuarios u WHERE u.idUsuario=m.idUsuario and u.cuentaActiva=1 
					and m.idUsuario IN (
						SELECT idUsuario FROM 807_usuariosVSRoles WHERE idRol IN (37,62,94,102)
						) 

					and m.idUsuario in 
							(
							SELECT idUsuario FROM 801_adscripcion a,_522_tablaDinamica t 
							WHERE a.Institucion=t.codigoInstitucion AND t.marcaAutorizado=1
							and t.suspendido=0
							)
					
				
					".$comp; */
			
			
			/*$consulta="SELECT distinct Mail,m.idUsuario FROM 805_mails m,800_usuarios u WHERE u.idUsuario=m.idUsuario and u.cuentaActiva=1 
					and m.idUsuario IN (
						SELECT idUsuario FROM 801_adscripcion a,_522_tablaDinamica t 
										WHERE a.Institucion=t.codigoInstitucion AND idEstado IN (2,3)
						) 
					
					
				
					".$comp; *///
			
			/*

			and m.idUsuario IN (
						SELECT idUsuario FROM 807_usuariosVSRoles WHERE idRol IN (37,62,94,102)
						) 

			and m.idUsuario in 
					(
					SELECT idUsuario FROM 801_adscripcion a,_448_tablaDinamica t 
					WHERE a.Institucion=t.codigoInstitucion AND t.marcaAutorizado=1
					UNION
					SELECT idUsuario FROM 801_adscripcion a,_464_tablaDinamica t 
					WHERE a.Institucion=t.codigoInstitucion AND t.marcaAutorizado=1
					)
			*/
			
					//
	
		//and 
				//	m.idUsuario in (SELECT idUsuario FROM 801_adscripcion a,_448_tablaDinamica t WHERE a.Institucion=t.codigoInstitucion AND t.marcaAutorizado=1)
				
				
				
		/*$consulta="SELECT distinct Mail,m.idUsuario FROM 805_mails m,800_usuarios u WHERE u.idUsuario=m.idUsuario and u.cuentaActiva=1 and m.idUsuario IN (
					SELECT idUsuario FROM 1038_supervisionProyectos2014) 
					".$comp;
					*/
					
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
			$obj[4]=array();
			
			$consulta="SELECT Institucion FROM 801_adscripcion where idUsuario=".$fila[1];
			$osc=$con->obtenerValor($consulta);

			$obj[5]="";
			$obj[6]="";
			$obj[7]="";
			
			
			/*$consulta="SELECT DISTINCT m.Mail FROM 1038_supervisionProyectos2014 s,805_mails m WHERE codigoUnidad='".$osc."' 
					AND m.idUsuario=s.idUsuario AND TRIM(m.Mail)<>'' and ciclo=2015";
					
			
			$rMail=$con->obtenerFilas($consulta);
			while($fMail=mysql_fetch_row($rMail))
			{
				if($prueba)
					$fMail[0]="marco.magana@grupolatis.net";
				$objMail[0]=$fMail[0];
				$objMail[1]="";
				array_push($obj[4],$objMail);	
			}*/
			array_push($arrDestinatarios,$obj);
			
		}
		
		
		foreach($arrDestinatarios as $d)
		{
			$consulta="SELECT Login,PASSWORD,nombre FROM 800_usuarios WHERE idUsuario=".$d[0];
			$fUsr=$con->obtenerPrimeraFila($consulta);
			$cuerpoMailFinal=str_replace("_usuario",$fUsr[0],$cuerpoMail);
			$cuerpoMailFinal=str_replace("_contrasena",$fUsr[1],$cuerpoMailFinal);
			$cuerpoMailFinal=str_replace("_nombre",$fUsr[2],$cuerpoMailFinal);
			if(!enviarMail($d[2]," CENSIDA - INFORMACION",$cuerpoMailFinal,"soporteSMAP@grupolatis.net","",$arrAchivos,$d[4]))			
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
		echo $nRegistros;

	}	
	
	function enviarCorreoRevisor2014()
	{
		global $con;
		$prueba=false;
		$arrAchivos=array();

		$arrAchivos[0][0]="../recursos/GuiaEvaluacionProyectos2014.pdf";
		$arrAchivos[0][1]="GuiaEvaluacionProyectos2014.pdf";
		
		$arrUsrProblemas=array();
		$arrUsrOk=array();
		
		$cuerpoMail="
		<table width=\"800\"><tr><td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/logoSalud.gif\"></td>
								<td width=\'250\' align=\"center\"><img width=\"75%\" height=\"75%\"  src=\"http://censida.grupolatis.net/images/censida/FIRMA_ELECTRONICAaaa.png\"></td></tr></table>
								<br><br>	
								<span style='font-size:12px'>
								Estimado/a<br> 
								_nombre<br><br>
								
								Para el Centro Nacional para la Prevención y el Control del VIH y el sida, es un honor agradecer su proactiva colaboración al aceptar ser parte del equipo de evaluadores expertos del los proyectos registrados  por las organizaciones de la sociedad civil e instituciones académicas a través de  la convocatoria pública 2014.<br>
								
								Su experiencia, trayectoria y conocimientos en el tema de la prevención del VIH y el sida, influirá en otorgar los recursos a las mejores propuestas en beneficio de la prevención del VIH en nuestro país.<br>
								
								Con ánimos de facilitar su participación, ponemos a su servicio el Sistema de Monitoreo de Actividades de Prevención (SMAP), para entrar al sistema deberá dirigirse a la dirección web: <a href='http://censida.grupolatis.net'>http://censida.grupolatis.net</a> donde podrá accesar a través de los siguientes datos:<br><br>
								 
								<b>Cuenta de acceso:</b> _usuario<br>
								<b>Contraseña: </b> _contrasena<br><br>
								
								En el sistema usted, podrá revisar las bases de la convocatoria, la guía de evaluación de los proyectos,  los proyectos presentados, los formatos de evaluación y todas las herramientas que le permitirán realizar la evaluación, de manera amigable.<br>
								
								Con el objeto de cumplir en tiempo y forma lo establecido en los lineamientos de la convocatoria, le agradecemos contar con las evaluaciones a los proyectos que le han sido asignados a través del SMAP, a más tardar el viernes 28 de marzo del presente año. <br>
								
								Para cualquier duda  o aclaración ponemos a sus órdenes los siguientes correos electrónicos. conv2014@gmail.com, direcciondeprevencion@gmail.com, así como soporte smap al interior del sistema.<br><br>
								En adjunto se envía la Guía de evaluación de proyectos 2014.<br><br>
								Reciba de cuenta nueva nuestro más sincero agradecimiento y un afectuoso saludo.<br>
								<br><br><br>
								</span>
								";		
		
		/*$cuerpoMail="
		<table width=\"800\"><tr><td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/logoSalud.gif\"></td>
								<td width=\'250\' align=\"center\"><img width=\"75%\" height=\"75%\"  src=\"http://censida.grupolatis.net/images/censida/FIRMA_ELECTRONICAaaa.png\"></td></tr></table>
								<br><br>	
								<span style='font-size:12px'>
								Estimado/a<br> 
								_nombre<br><br>
								
								Agradecemos su participación y pronta respuesta en la evaluación de proyectos pertenecientes a la convocatoria pública 2014 del Centro Nacional para la Prevención y el Control del VIH y el sida. Le invitamos a continuar con su esfuerzo, ya que éste
								influirá en otorgar los recursos a las mejores propuestas en beneficio de la prevención del VIH en nuestro país. <BR><BR>								
								
								De igua formar le recordamos que cualquier duda o aclaración ponemos a sus órdenes los siguientes correos electrónicos. conv2014@gmail.com, direcciondeprevencion@gmail.com, así como soporte smap al interior del sistema.<br><br>
								Finalmente le confirmamos sus datos de acceso:<br><br>
								<b>Cuenta de acceso:</b> _usuario<br>
								<b>Contraseña: </b> _contrasena<br><br>
								<br>
								<br><br><br>
								</span>
								";		*/
								
								
		/*$cuerpoMail="
		<table width=\"800\"><tr><td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/logoSalud.gif\"></td>
								<td width=\'250\' align=\"center\"><img width=\"75%\" height=\"75%\"  src=\"http://censida.grupolatis.net/images/censida/FIRMA_ELECTRONICAaaa.png\"></td></tr></table>
								<br><br>	
								<span style='font-size:12px'>
								Estimado/a<br> 
								_nombre<br><br>
								
								El Centro Nacional para la Prevención y el Control del VIH y el sida, le agradece su colaboración al participar en la evaluaci&oacute;n del los proyectos registrados  por las organizaciones de la sociedad civil e instituciones académicas a través de  la convocatoria pública 2014.<br>
								
								Se ha detectado que a&uacute;n cuenta con evaluaciones pendientes, entendiendo que es una persona ocupada y dado que su experiencia, trayectoria y conocimientos en el tema de la prevención del VIH y el sida es muy importante para nosotros, se ha extendido el periodo de evaluación hasta el día martes 25 de marzo del presente año.<br>
								
								Le recordamos sus datos de acceso:<br><br>
								 
								 <b>URL:</b> <a href='http://censida.grupolatis.net'>http://censida.grupolatis.net</a><br>
								<b>Cuenta de acceso:</b> _usuario<br>
								<b>Contraseña: </b> _contrasena<br><br>
								
								Para cualquier duda  o aclaración ponemos a sus órdenes los siguientes correos electrónicos. conv2014@gmail.com, direcciondeprevencion@gmail.com, así como soporte SMAP al interior del sistema.<br><br>
								En adjunto se envía la Guía de evaluación de proyectos 2014.<br><br>
								En caso de o poder cumplir con la evaluación de los proyectos pendientes, le pedimos nos lo haga saber para de esta forma reasignar sus proyectos.<br><br>
								Reciba de cuenta nueva nuestro más sincero agradecimiento y un afectuoso saludo.<br>
								<br><br><br>
								</span>
								";	*/								

		/*$cuerpoMail="
		<table width=\"800\"><tr><td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/logoSalud.gif\"></td>
								<td width=\'250\' align=\"center\"><img width=\"75%\" height=\"75%\"  src=\"http://censida.grupolatis.net/images/censida/FIRMA_ELECTRONICAaaa.png\"></td></tr></table>
								<br><br>	
								<span style='font-size:12px'>
								Estimado/a<br> 
								_nombre<br><br>
								
								El Centro Nacional para la Prevención y el Control del VIH y el sida, le agradece su colaboración al participar en la evaluaci&oacute;n del los proyectos registrados  por las organizaciones de la sociedad civil e instituciones académicas a través de  la convocatoria pública 2014.<br>
								
								Se ha detectado que a&uacute;n cuenta con evaluaciones pendientes, entendiendo que es una persona ocupada y dado que su experiencia, trayectoria y conocimientos en el tema de la prevención del VIH y el sida es muy importante para nosotros, se le ha extendido el periodo de evaluación hasta el día viernes 28 de marzo  del presente año a las 23:59 hrs..<br>
								
								Le recordamos sus datos de acceso:<br><br>
								 
								 <b>URL:</b> <a href='http://censida.grupolatis.net'>http://censida.grupolatis.net</a><br>
								<b>Cuenta de acceso:</b> _usuario<br>
								<b>Contraseña: </b> _contrasena<br><br>
								
								Para cualquier duda  o aclaración ponemos a sus órdenes los siguientes correos electrónicos. conv2014@gmail.com, direcciondeprevencion@gmail.com, así como soporte SMAP al interior del sistema.<br><br>
								En adjunto se envía la Guía de evaluación de proyectos 2014.<br><br>
								<b>En caso de o poder cumplir con la evaluación de los proyectos pendientes, le pedimos nos lo haga saber para de esta forma reasignar sus proyectos.</b><br><br>
								Reciba de cuenta nueva nuestro más sincero agradecimiento y un afectuoso saludo.<br>
								<br><br><br>
								</span>
								";	*/


		$cuerpoMail="
		<table width=\"800\"><tr><td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/logoSalud.gif\"></td>
								<td width=\'250\' align=\"center\"><img width=\"75%\" height=\"75%\"  src=\"http://censida.grupolatis.net/images/censida/FIRMA_ELECTRONICAaaa.png\"></td></tr></table>
								<br><br>	
								<span style='font-size:12px'>
								Estimado/a<br> 
								_nombre<br><br>
								
								El Centro Nacional para la Prevención y el Control del VIH y el sida, le agradece su colaboración al continuar participando en la evaluaci&oacute;n del los proyectos registrados  por las organizaciones de la sociedad civil e instituciones académicas a través de  la convocatoria pública 2014.<br>
								
								Entendiendo que es una persona ocupada, se le han asignado un número de proyectos que podrán ser evaluados desde la plataforma SMAP de la misma forma en que revisó los anteriores teniendo como plazo  el día sábado 29 de marzo del presente año a las 23:59 hrs.
								
								Le recordamos sus datos de acceso:<br><br>
								 
								 <b>URL:</b> <a href='http://censida.grupolatis.net'>http://censida.grupolatis.net</a><br>
								<b>Cuenta de acceso:</b> _usuario<br>
								<b>Contraseña: </b> _contrasena<br><br>
								
								Para cualquier duda  o aclaración ponemos a sus órdenes los siguientes correos electrónicos. conv2014@gmail.com, direcciondeprevencion@gmail.com, así como soporte SMAP al interior del sistema.<br><br>
								En adjunto se envía la Guía de evaluación de proyectos 2014.<br><br>
								En caso de o poder cumplir con la evaluación de los proyectos pendientes, le pedimos nos lo haga saber para de esta forma reasignar sus proyectos.<br><br>
								Reciba de cuenta nueva nuestro más sincero agradecimiento y un afectuoso saludo.<br>
								<br><br><br>
								</span>
								";	
								
								
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
		
		
		$consulta="SELECT Mail,m.idUsuario,(SELECT Nombre FROM relacion WHERE idUsuario=m.idUsuario) AS nombre FROM 805_mails m WHERE  m.idUsuario IN (
					SELECT idUsuario FROM 1010_distribucionRevisoresProyectos WHERE situacion=1 and idUsuario in 
					(
						1566,1584

									
					)) ".$comp;
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
			$obj[3]=$fila[2];
			array_push($arrDestinatarios,$obj);
			
		}
		
		foreach($arrDestinatarios as $d)
		{
			$consulta="SELECT Login,PASSWORD FROM 800_usuarios WHERE idUsuario=".$d[0];
			$fUsr=$con->obtenerPrimeraFila($consulta);
			$cuerpoMailFinal=str_replace("_usuario",$fUsr[0],$cuerpoMail);
			$cuerpoMailFinal=str_replace("_contrasena",$fUsr[1],$cuerpoMailFinal);
			$cuerpoMailFinal=str_replace("_nombre",$d[3],$cuerpoMailFinal);
			if(!enviarMail($d[2],"CENSIDA Evaluacion de proyectos 2014",$cuerpoMailFinal,"soporteSMAP@grupolatis.net","",$arrAchivos,NULL))			
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
		
		
	}	
	
	function enviarComentariosEvaluacionProyectos($idProyecto,$prueba=false)
	{
		global $con;
		global $baseDir;

		
		$consulta="SELECT codigo,tituloProyecto,codigoInstitucion FROM _448_tablaDinamica WHERE id__448_tablaDinamica=".$idProyecto;
		$fProyecto=$con->obtenerPrimeraFila($consulta);
		
		
		$consulta="select unidad from 817_organigrama where codigoUnidad='".$fProyecto[2]."'";
		$nombreOSC=$con->obtenerValor($consulta);
		
		$PHPWord = new PHPWord();
		$document = $PHPWord->loadTemplate($baseDir.'/modulosEspeciales_Censida/reportes/comunicadoAjustePresupuestal.docx');	
		
		$document->setValue("_folioProy_",utf8_decode($fProyecto[0]));
		//$document->setValue("_tituloProy_",utf8_decode(str_replace('"','',$fProyecto[1])));
		$document->setValue("_nOSC_",utf8_decode(str_replace('"','',$nombreOSC)));
		
		
		/*$tblExplicacion="";
		$comentariosFinal="<w:tbl>";
		
		$consulta="SELECT calculo FROM 100_calculosGrid c,100_montosAutorizadosConceptos2014 a WHERE c.idFormulario=448 AND c.idReferencia=".$idProyecto." AND idGridVSCalculo=a.idConcepto
					AND a.situacion=2 ORDER BY calculo";
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			if(trim($fila[0]!=""))
			{
				
			
				$tblExplicacion.='<w:tr><w:tc><w:p><w:r><w:t>- '.str_replace(">","",str_replace("<","",$fila[0])).'</w:t></w:r></w:p></w:tc></w:tr>';
				
			}
		}
		if($tblExplicacion=="")
			$tblExplicacion="<w:tr><w:tc><w:p><w:r><w:t>Ninguno</w:t></w:r></w:p></w:tc></w:tr>";
			
			
			
		$comentariosFinal.=$tblExplicacion."</w:tbl>";
		
		$document->setValue("_lN_",utf8_decode($comentariosFinal));
		
		
		$tblExplicacion="";
		$comentariosFinal="<w:tbl>";
		
		$consulta="SELECT calculo FROM 100_calculosGrid c,100_montosAutorizadosConceptos2014 a WHERE c.idFormulario=448 AND c.idReferencia=".$idProyecto." AND idGridVSCalculo=a.idConcepto
					AND a.situacion=3 ORDER BY calculo";
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			if(trim($fila[0]!=""))
			{
				$tblExplicacion.='<w:tr><w:tc><w:p><w:r><w:t>- '.str_replace(">","",str_replace("<","",$fila[0])).'</w:t></w:r></w:p></w:tc></w:tr>';
			}
			
		}
		if($tblExplicacion=="")
			$tblExplicacion="<w:tr><w:tc><w:p><w:r><w:t>Ninguno</w:t></w:r></w:p></w:tc></w:tr>";
			
		$comentariosFinal.=$tblExplicacion."</w:tbl>";
		
		$document->setValue("_lF_",utf8_decode($comentariosFinal));
		
		$tblExplicacion="";
		$comentariosFinal="<w:tbl>";
		
		$consulta="SELECT calculo FROM 100_calculosGrid c,100_montosAutorizadosConceptos2014 a WHERE c.idFormulario=448 AND c.idReferencia=".$idProyecto." AND idGridVSCalculo=a.idConcepto
					AND a.situacion=4 ORDER BY calculo";
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			if(trim($fila[0]!=""))
			{
				$tblExplicacion.='<w:tr><w:tc><w:p><w:r><w:t>- '.str_replace(">","",str_replace("<","",$fila[0])).'</w:t></w:r></w:p></w:tc></w:tr>';
			}
		}
		if($tblExplicacion=="")
			$tblExplicacion="<w:tr><w:tc><w:p><w:r><w:t>Ninguno</w:t></w:r></w:p></w:tc></w:tr>";

		$comentariosFinal.=$tblExplicacion."</w:tbl>";



		$document->setValue("_1D_",utf8_decode($comentariosFinal));*/
		$nomArchivo=$fProyecto[0].'.docx';
		
		$document->save($nomArchivo);	
		
		
		$arrAchivos=array();
		$arrAchivos[0][0]=$nomArchivo;
		$arrAchivos[0][1]=$nomArchivo;
		
	
		$comp="";
		if($prueba)
		{
		
			$comp=" limit 0,1";
		}
		
	
		$cuerpoMail="
		<table width=\"800\"><tr><td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/logoSalud.gif\"></td>
								<td width=\'250\' align=\"center\"><img width=\"75%\" height=\"75%\"  src=\"http://censida.grupolatis.net/images/censida/FIRMA_ELECTRONICAaaa.png\"></td></tr></table>
								<br><br><h1>Centro Nacional para La Prevención y el<br>
								Control Del VIH y el sida</h1><br>
								<br><br>
								<span style=\"font-size:12px\">	<b>
".$nombreOSC."</b><br><br>

Por este medio se le hace llegar en el documento adjunto el resultado de la evaluaci&oacute;n presupuestal realizada sobre el proyecto <b>".$fProyecto[0]."</b>.<br><br> Para realizar los ajustes solicitados, usted deberá:<br><br>

1.- Ingresar a SMAP con su cuenta de acceso<br><br>
2.- En la pantalla central, dentro de la pestaña \"Proyectos 2014\", seleccionar del listado el proyecto cuyas modificaciones desea realizar y presionar el botón \"Abrir expediente del proyecto\"<br><br>
3.- Con lo anterior se abrirá la ventana del proyecto, deberá dar click en la sección \"Ajustes presupuestales\"<br><br>
4.- En la sección \"Ajustes presupuestales\", podrá observar los comentarios ingresados por el revisor. Para realizar algún ajuste o justificación (Sólo aquellos marcados como \"Se requiere justificación\" en la columna \"Situaci&oacute;n\") deberá seleccionar el concepto y presionar alguno de los siguientes botones:<br><br>
a) Registrar/modificar justificaci&oacute;n.- Este botón sólo está disponible para aquellos concpetos marcados como \"Se requiere justificación\" y permite el registro de la justificaciòn del concepto.<br><br>
b) Realizar ajuste presupuestal al concepto.- Este botón permite modificar la cantidad, costo unitario y rubro del concepto (Sólo debe cambiarse si la observación del revisor implica que el concepto pertenece a otro rubro presupuestal). En caso de ser necesario para el ajuste de su presupuesto también pude  eliminar el concepto presionando la casilla \"Este concepto no será utilizado\".<br><br>

Sólo podrá modificar aquellos conceptos marcados como \"Ajustable\" ó \"Se requiere justificación\" en la columna \"Situaci&oacute;n\". Es recomendable no sobrepasar los porcentajes de gastoa asociados a cada rubro presupuestal:<br><br>
a) 30% para Recursos Humanos<br><br>
b) 35% para Insumos de la intervenci&oacute;n<br><br>
c) 15% para Gastos de operación<br><br>
d) 20% para Viáticos<br><br>

	
	Cada pestaña presupuestal cuenta además con una columna llamada \"Comentarios del revisor\" que opcionalmente pudiese trae comentarios del revisor específico al concepto.<br><br>
	
	Para enviar los ajustes de su proyecto a evaluaci&oacute;n, usted deberá dar click sobre el botón \"Notificar cambios al CENSIDA\" donde opcionalmente puede enviar un comentario. <br><br>
	Finalmente, es importante mencionar que una vez notificados los cambios NO podrá llevarse a cabo modificacián alguna sobre el presupuesto.<br><br>
	
	Para cualquier duda  o aclaración ponemos a sus órdenes los siguientes correos electrónicos. conv2014@gmail.com, direcciondeprevencion@gmail.com, así como soporte SMAP al interior del sistema.<br><br>
	
<br><br>



<br><br><br></span>";		
		
		/*header("Content-type: application/vnd.ms-word");
		header("Content-length: ".filesize($nomArchivo)); 

		header("Content-Disposition: attachment; filename=".$nomArchivo);
		
		
		readfile($nomArchivo);*/

		$consulta="SELECT Mail,m.idUsuario FROM 805_mails m,800_usuarios u WHERE u.idUsuario=m.idUsuario and u.cuentaActiva=1 and m.idUsuario IN (
					SELECT idUsuario FROM 801_adscripcion WHERE Institucion='".$fProyecto[2]."') ".$comp;
					
		$arrDestinatarios=array();
					
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
		$arrUsrProblemas=array();
		$arrUsrOk=array();
		foreach($arrDestinatarios as $d)
		{
			
			if(!enviarMail($d[2],"Resultado Evaluacion presupuestal ".$fProyecto[0],$cuerpoMail,"soporteSMAP@grupolatis.net","",$arrAchivos,NULL))			
			{
				
				array_push($arrUsrProblemas,$d);
			}
			else
			{
				
				$consulta="UPDATE _448_tablaDinamica SET notificado=1 WHERE id__448_tablaDinamica=".$idProyecto;
				$con->ejecutarConsulta($consulta);
				
				array_push($arrUsrOk,$d);	
			}
		}
		
		unlink($nomArchivo);
		echo "Usuarios Mal: ".sizeof($arrUsrProblemas)."<br>";
		varDump($arrUsrProblemas);
		echo "Usuarios OK: ".sizeof($arrUsrOk)." <br>";
		varDump($arrUsrOk);
			
	}	
	
	function enviarCorreosInvitacionFirmaConvenio()
	{
		global $con;
		global $baseDir;
		$prueba=false;
		$arrAchivos=array();



		$comp="";
		if($prueba)
			$comp=" limit 0,1";
		
		$consulta="SELECT institucion,nombreOSC,fecha,hora,idCita FROM 1036_agendaCitas".$comp;
		$rAgenda=$con->obtenerFilas($consulta);
		while($fAgenda=mysql_fetch_row($rAgenda))
		{
			
			$nombreOSC=$fAgenda[1];
			/*
			$PHPWord = new PHPWord();
			$document = $PHPWord->loadTemplate($baseDir.'/modulosEspeciales_Censida/reportes/invitacionFirmaConvenio.docx');	
			
			
			
			$document->setValue("_nOSC_",utf8_decode(str_replace('"','',$nombreOSC)));
			$document->setValue("_horaCita_",date("H:i ",strtotime($fAgenda[3]))." hrs.");
			$document->setValue("_fechaCita_",date("d/m/Y",strtotime($fAgenda[2]))."");
			$nomArchivo="invitacionInstitucion.docx";
			$document->save($nomArchivo);	*/
			
			$arrUsrProblemas=array();
			$arrUsrOk=array();
			
			$arrAchivos=array();
			$arrAchivos[0][0]="../recursos/Cartas_conflicto_de_interes_y_experiencia.doc";
			$arrAchivos[0][1]="Cartas_conflicto_de_interes_y_experiencia.doc";
			
			
			$cuerpoMail="
			<table width=\"800\"><tr><td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/logoSalud.gif\"></td>
									<td width=\'250\' align=\"center\"><img width=\"75%\" height=\"75%\"  src=\"http://censida.grupolatis.net/images/censida/FIRMA_ELECTRONICAaaa.png\"></td></tr></table>
									<br><br><h1>Centro Nacional para La Prevención y el<br>
									Control Del VIH y el sida</h1><br>
									<br><br>	
	<span style=\"font-size:12px\">
	<b>".$nombreOSC."</b>:<br><br>
	
	Por este medio se envia en adjunto la versión actualizada de:<br><br>
	FORMATO CARTA NO EXISTENCIA DE CONFLICTO DE INTERÉS<br><br>
	FORMATO CARTA DECLARACIÓN DE EXPERIENCIA Y ACEPTACIÓN DE BASES (Carta de apego a los lineamientos de transparencia).<br><br>


	 Éstas deberán se llenadas y presentadas al CENSIDA en original y copia tamaño carta legible en dos tantos en su fecha programada para firma del convenio.
	 Deberá reemplazar la letra pintada en color vino por el dato correspondiente y todo deberá estar en letra color negro.
	<br><br>
	Para cualquier duda  o aclaración ponemos a sus órdenes los siguientes correos electrónicos: conv2014@gmail.com, direcciondeprevencion@gmail.com, así como soporte SMAP al interior del sistema.<br><br>
	<br></span>";		
			
			
			$arrCopiaOculta=NULL;

			$arrDestinatarios=array();
			
			
			
			
			$consulta="SELECT Mail,m.idUsuario FROM 805_mails m,800_usuarios u WHERE u.idUsuario=m.idUsuario and u.cuentaActiva=1 and m.idUsuario IN (
						SELECT idUsuario FROM 801_adscripcion WHERE Institucion='".$fAgenda[0]."') ".$comp;
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
				if(!enviarMail($d[2],"CARTAS ACTUALIZADAS CENSIDA FIRMA CONVENIO 2014",$cuerpoMailFinal,"soporteSMAP@grupolatis.net","",$arrAchivos,NULL))			
				{
					
					array_push($arrUsrProblemas,$d);
				}
				else
				{
					array_push($arrUsrOk,$d);	
				}
				
				
				/*$consulta="UPDATE 1036_agendaCitas SET notificado=1 WHERE idCita=".$fAgenda[4];
				$con->ejecutarConsulta($consulta);*/
				
			}
			unlink($nomArchivo);
			echo "Usuarios Mal: ".sizeof($arrUsrProblemas)."<br>";
			varDump($arrUsrProblemas);
			echo "Usuarios OK: ".sizeof($arrUsrOk)." <br>";
			varDump($arrUsrOk);
		}
	}		
	
	function actualizarDatosAccesoMiembroOSC($idFormulario,$idRegistro)
	{
		global $con;
		
		
		$nuevoUsuario=false;
		
		$x=0;
		$query[$x]="begin";
		$x++;
		
		$consulta="SELECT miembroOrganizacion,privilegiosCuenta,usuario,PASSWORD,cuentaActiva FROM _466_tablaDinamica WHERE id__466_tablaDinamica=".$idRegistro;
		$fDatos=$con->obtenerPrimeraFila($consulta);		
		
		$consulta="SELECT idUsuarioSistema,aPaterno,aMaterno,nombre,emailCoordinador FROM _379_tablaDinamica WHERE id__379_tablaDinamica=".$fDatos[0];
		$fMiembro=$con->obtenerPrimeraFila($consulta);
		$idUsuario=$fMiembro[0];
		if($idUsuario!="")
		{
			
			$query[$x]="set @idUsuario:=".$idUsuario;
			$x++;
			$query[$x]="UPDATE 800_usuarios SET PASSWORD='".$fDatos[3]."',cuentaActiva='".$fDatos[4]."' WHERE idUsuario=@idUsuario";
			$x++;
		}
		else
		{
			$nuevoUsuario=true;
			$idUsuario=crearBaseUsuario($fMiembro[1],$fMiembro[2],$fMiembro[3],$fMiembro[4],$_SESSION["codigoInstitucion"],"","37");

			$query[$x]="set @idUsuario:=".$idUsuario;
			$x++;
			$query[$x]="UPDATE 800_usuarios SET Login='".$fDatos[2]."',PASSWORD='".$fDatos[3]."',cuentaActiva='".$fDatos[4]."' WHERE idUsuario=@idUsuario";
			$x++;
			$query[$x]="UPDATE _379_tablaDinamica SET idUsuarioSistema=@idUsuario WHERE id__379_tablaDinamica=".$fDatos[0];
			$x++;
		}
		
		$query[$x]="DELETE FROM 807_usuariosVSRoles WHERE idUsuario=".$idUsuario." AND idRol=94";
		$x++;
		if($fDatos[1]==1)
		{
			$query[$x]="INSERT INTO 807_usuariosVSRoles(idUsuario,idRol,idExtensionRol,codigoRol) VALUES(@idUsuario,94,0,'94_0')";
			$x++;
		}
		$query[$x]="commit";
		$x++;
		if($con->ejecutarBloque($query))
		{
			if(!$nuevoUsuario)
			{
				$arrParam=array();
				$arrParam["idUsuario"]=$idUsuario;
				enviarMensajeEnvio(7,$arrParam);
			}
			else
			{
				$arrParam=array();
				$arrParam["idUsuario"]=$idUsuario;
				enviarMensajeEnvio(6,$arrParam);
			}
			return true;	
		}
	}
	
	function actualizarDatosCuentaAcceso($idFormulario,$idRegistro)
	{
		global $con;
		$x=0;
		$query[$x]="begin";
		$x++;
		$consulta="SELECT idUsuarioSistema,aPaterno,aMaterno,nombre,emailCoordinador,miembroActivo FROM _379_tablaDinamica WHERE id__379_tablaDinamica=".$idRegistro;
		$fMiembro=$con->obtenerPrimeraFila($consulta);	
		if(($fMiembro[0]!="")&&($fMiembro[0]!="-1"))
		{
			$query[$x]="UPDATE 800_usuarios SET cuentaActiva='".$fMiembro[5]."' WHERE idUsuario=".$fMiembro[0];
			$x++;
			$query[$x]="UPDATE 802_identifica SET Paterno='".cv($fMiembro[1])."',Materno='".cv($fMiembro[2])."',Nom='".cv($fMiembro[3])."' WHERE idUsuario=".$fMiembro[0];
			$x++;
			$query[$x]="UPDATE _466_tablaDinamica SET cuentaActiva='".cv($fMiembro[5])."'  WHERE miembroOrganizacion=".$idRegistro;
			$x++;
			$query[$x]="DELETE FROM 805_mails WHERE idUsuario=".$fMiembro[0];
			$x++;
			$query[$x]="insert into 805_mails(Mail,Tipo,Notificacion,idUsuario) values('".cv(trim($fMiembro[4]))."',0,1,".$fMiembro[0].")";
			$x++;
		}
		$query[$x]="commit";
		$x++;
		return $con->ejecutarBloque($query);
	}	
	
	function registrarEvaluacionProyectoThotCuestionario($idFormulario,$idRegistro)
	{
		global $con;
		$consulta="select idReferencia,responsable,idFormularioBase from  _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
		$fEvaluacion=$con->obtenerPrimeraFila($consulta);
		$consulta="UPDATE 1011_asignacionRevisoresProyectos SET situacion=2,fechaEvaluacion='".date("Y-m-d H:i:s")."',idReferencia=".$idRegistro.
				",idCuestionarioEval=".$idFormulario.",tipoCuestionario=2 WHERE idUsuario=".$fEvaluacion[1]." AND idProyecto=".$fEvaluacion[0]." AND idFormulario=".$fEvaluacion[2];
		if($con->ejecutarConsulta($consulta))
		{
			echo "window.parent.evaluacionFinal();return";
			return true;
		}
		
	}
	
	function comunicarEvaluacionContenido($idFormulario,$idRegistro)
	{
		global $con;
		$consulta="SELECT dictamenFinal,comentarios,idReferencia FROM _482_tablaDinamica WHERE id__482_tablaDinamica=".$idRegistro;
		$fRegistro=$con->obtenerPrimeraFila($consulta);
		$consulta="SELECT codigo,idReferencia,descripcion FROM _473_tablaDinamica WHERE id__473_tablaDinamica=".$fRegistro[2];
		$fProducto=$con->obtenerPrimeraFila($consulta);
		
		$consulta="SELECT o.organizacion,t.codigo,o.codigoInstitucion FROM _448_tablaDinamica t,_367_tablaDinamica o WHERE id__448_tablaDinamica=".$fProducto[1]." AND o.codigoInstitucion=t.codigoInstitucion";
		$fProyecto=$con->obtenerPrimeraFila($consulta);
		
		$arrParam=array();
		$arrParam["idRegistro"]=$fRegistro[2];
		
		
		$arrParam["nombreOSC"]=$fProyecto[0];
		$arrParam["descripcionProducto"]=$fProducto[2];
		$arrParam["folioProducto"]=$fProducto[0];
		$arrParam["folioProyecto"]=$fProyecto[1];
		$arrParam["comentarios"]=$fRegistro[1];
		
		$resultado="";
		switch($fRegistro[0])
		{
			case 0:
				$resultado="Rechazado";
			break;
			case 1:
				$resultado="Aceptado";
			break;
			case 2:
				$resultado="Requiere cambios";
			break;				
		}
		
		$arrParam["resultado"]=$resultado;
		
		
		$consulta="SELECT distinct Mail,m.idUsuario FROM 805_mails m,800_usuarios u WHERE u.idUsuario=m.idUsuario and u.cuentaActiva=1 and m.idUsuario IN (
					SELECT idUsuario FROM 807_usuariosVSRoles WHERE idRol IN (37,62,94)) 
					and m.idUsuario in (SELECT idUsuario FROM 801_adscripcion a where a.Institucion='".$fProyecto[2]."')";
		$rUsuarios=$con->obtenerFilas($consulta);			
		while($fUsuario=mysql_fetch_row($rUsuarios))
		{
			
			if($fUsuario[0]!="")
			{
				$arrParam["emailReceptor"]=$fUsuario[0];
				enviarMensajeEnvio(8,$arrParam);	
			}
		}
		
		if(($_SESSION["idUsr"]==301)&&($fRegistro[0]==1))
		{
			cambiarEtapaFormulario(473,$fRegistro[2],3)	;
		}
		
		return true;
		
	}
	
	function comunicarEvaluacionImagen($idFormulario,$idRegistro)
	{
		global $con;
		$consulta="SELECT dictamenFinal,comentarios,idReferencia FROM _474_tablaDinamica WHERE id__474_tablaDinamica=".$idRegistro;
		
		$fRegistro=$con->obtenerPrimeraFila($consulta);
		
		$consulta="SELECT codigo,idReferencia,descripcion FROM _473_tablaDinamica WHERE id__473_tablaDinamica=".$fRegistro[2];
		$fProducto=$con->obtenerPrimeraFila($consulta);
		
		$consulta="SELECT o.organizacion,t.codigo,o.codigoInstitucion FROM _448_tablaDinamica t,_367_tablaDinamica o WHERE id__448_tablaDinamica=".$fProducto[1]." AND o.codigoInstitucion=t.codigoInstitucion";

		$fProyecto=$con->obtenerPrimeraFila($consulta);
		
		$arrParam=array();
		$arrParam["idRegistro"]=$fRegistro[2];
		
		
		$arrParam["nombreOSC"]=$fProyecto[0];
		$arrParam["descripcionProducto"]=$fProducto[2];
		$arrParam["folioProducto"]=$fProducto[0];
		$arrParam["folioProyecto"]=$fProyecto[1];
		$arrParam["comentarios"]=$fRegistro[1];
		
		$resultado="";
		switch($fRegistro[0])
		{
		
			case 1:
				$resultado="Aceptado";
			break;
			case 2:
				$resultado="Requiere cambios";
			break;				
		}
		
		$arrParam["resultado"]=$resultado;
		
		
		$consulta="SELECT distinct Mail,m.idUsuario FROM 805_mails m,800_usuarios u WHERE u.idUsuario=m.idUsuario and u.cuentaActiva=1 and m.idUsuario IN (
					SELECT idUsuario FROM 807_usuariosVSRoles WHERE idRol IN (37,62,94)) 
					and m.idUsuario in (SELECT idUsuario FROM 801_adscripcion a where a.Institucion='".$fProyecto[2]."')";
		$rUsuarios=$con->obtenerFilas($consulta);			
		while($fUsuario=mysql_fetch_row($rUsuarios))
		{
			if($fUsuario[0]!="")
			{
				$arrParam["eMailReceptor"]=$fUsuario[0];
				enviarMensajeEnvio(9,$arrParam);	
			}
		}
		return true;
	}
	
	function enviarCorreosResponsableVIHEstatal()
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
	
		$cuerpoMail="
		<table width=\"800\"><tr><td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/logoSalud.gif\"></td>
								<td width=\'250\' align=\"center\"><img width=\"75%\" height=\"75%\"  src=\"http://censida.grupolatis.net/images/censida/FIRMA_ELECTRONICAaaa.png\"></td></tr></table>
								<br><br><h1>Centro Nacional para La Prevención y el<br>
								Control Del VIH/SIDA</h1><br>
								<br><br>	
Estimado/a _nombre:<br><br>

Por este medio se le hace llegar sus datos de acceso a la plataforma SMAP (<a href=\"http://censida.grupolatis.net\">http://censida.grupolatis.net</a>) 
el cual le permitirá monitorear las actividades que están realizándose en su entidad federativa por parte de las OSC. Sus datos de acceso son:<br><br>
<b>Usuario:</b> _usuario<br>
<b>Password:</b> _contrasena<br><br>

Sin nada más que agregar el equipo del CENSIDA le desea un buen día.
<br><br>



<br><br><br>";		
		$arrCopiaOculta=NULL;
		$comp="";
		$arrDestinatarios=array();
		if(!$prueba)
		{
			
		}
		else
		{
			
			$comp=" limit 0,1";
		}
		
		
		$consulta="SELECT codigoUnidad,idUsuario FROM 801_adscripcion WHERE idUsuario IN(
					SELECT idUsuario FROM 800_usuarios WHERE idUsuario>=920 AND idUsuario<=951) ".$comp;
		$rEstados=$con->obtenerFilas($consulta);
		while($fEstados=mysql_fetch_row($rEstados))
		{
			$consulta="SELECT id__438_tablaDinamica,nombreResponsable FROM _438_tablaDinamica WHERE estado='".$fEstados[0]."' AND puesto='3'";
			
			$resResponsable=$con->obtenerFilas($consulta);	
			while($fila=mysql_fetch_row($resResponsable))
			{
				$consulta="SELECT email FROM _438_Email WHERE idReferencia=".$fila[0];
				$rMail=$con->obtenerFilas($consulta);
				while($fMail=mysql_fetch_row($rMail))
				{
					$mail=trim($fMail[0]);
					$obj=array();
					if($prueba)
						$mail="novant1730@hotmail.com";
					$obj[0]=$fEstados[1];
					$obj[1]="";
					$obj[2]=$mail;
					$obj[3]=$fila[1];
					array_push($arrDestinatarios,$obj);
				}
			}
		}
		$arrUsrProblemas=array();
		$arrUsrOk=array();
		foreach($arrDestinatarios as $d)
		{
			$consulta="SELECT Login,PASSWORD,Nombre FROM 800_usuarios WHERE idUsuario=".$d[0];
			$fUsr=$con->obtenerPrimeraFila($consulta);
			$cuerpoMailFinal=str_replace("_usuario",$fUsr[0],$cuerpoMail);
			$cuerpoMailFinal=str_replace("_contrasena",$fUsr[1],$cuerpoMailFinal);
			$cuerpoMailFinal=str_replace("_nombre",$d[3],$cuerpoMailFinal);
			if(!enviarMail($d[2],"CENSIDA - Cuenta de acceso SMAP",$cuerpoMailFinal,"soporteSMAP@grupolatis.net","",$arrAchivos,NULL))			
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

	}

	function enviarCorreosRevisores_2015()
	{
		global $con;
		$prueba=false;
		$arrUsrProblemas=array();
		$arrUsrOk=array();
		$arrAchivos=array();
		
		$arrAchivos[0][0]="../recursos/resumenParaEvaluacion.pptx";
		$arrAchivos[0][1]="resumenParaEvaluacion.pptx";
		$arrAchivos[1][0]="../recursos/convocatoria2015.pdf";
		$arrAchivos[1][1]="Convocatoria2015.pdf";
		
		
	/*	$cuerpoMail="
					<table width=\"800\"><tr><td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/logoSalud.gif\"></td>
					<td width=\'250\' align=\"center\"><img width=\"75%\" height=\"75%\"  src=\"http://censida.grupolatis.net/images/censida/FIRMA_ELECTRONICAaaa.png\"></td></tr></table>
					<br><br><h1>Centro Nacional para La Prevención y el<br>
					Control Del VIH y el sida</h1>
					<span style='font-size:12px'>
					<br><b>Estimada/o _nombreRevisor:</b><br><br>
					
					El Centro Nacional para la Prevención y el Control del VIH y el sida (CENSIDA) le invita a colaborar como miembro revisor de los proyectos registrados por las organizaciones de 
					la sociedad civil participantes en la <b>\"CONVOCATORIA PÚBLICA DIRIGIDA A LAS ORGANIZACIONES DE LA SOCIEDAD CIVIL CON EXPERIENCIA Y TRABAJO COMPROBABLE EN PREVENCIÓN DEL VIH E INFECCIONES DE TRANSMISIÓN SEXUAL (ITS) 
					PARA LA IMPLEMENTACIÓN DE ESTRATEGIAS DE PREVENCIÓN FOCALIZADA DEL VIH Y OTRAS ITS QUE FORTALEZCAN LA RESPUESTA NACIONAL 2015.\"</b>.<br><br>
					
					El proceso es muy simple, en caso de aceptar, en días próximos se le notificará sus datos de acceso a la plataforma SMAP, en donde deberá ingresar y leer los proyectos asignados y, finalmente contestar un cuestionario con referencia al mismo, su opini&oacute;n nos permitirá tomar decisiones respecto al financiamiento del proyecto.<br><br>
					
					Le pedimos nos confirme si desea o no participar en esta labor dando click en cualquiera de los siguientes enlaces:<br><br>
					
					<table width='100%'>
					<tr>
						<td align='center'>
							<table>
								<tr>
									<td><a href=\"http://censida.grupolatis.net/modulosEspeciales_Censida/aceptacionRevisor.php?p=_parametro\"><img src='http://censida.grupolatis.net/images/001_06.gif'></a></td><td> <a href=\"http://censida.grupolatis.net/modulosEspeciales_Censida/aceptacionRevisor.php?p=_parametro\"><font color='#F00' style='color:#900'>Sí deseo participar como revisor</font></a></td>
									<td width='25'>
									</td>
									<td><a href=\"http://censida.grupolatis.net/modulosEspeciales_Censida/rechazoRevisor.php?p=_parametro\"><img src='http://censida.grupolatis.net/images/001_05.gif'></a></td><td><a href=\"http://censida.grupolatis.net/modulosEspeciales_Censida/rechazoRevisor.php?p=_parametro\"><font color='#F00' style='color:#900'>NO gracias, quizás en otra ocasi&oacute;n</font></a></td>
								</tr>
							</table>
						</td>
					</tr>
					</table>					
						
					
					
					Cualquier duda/sugerencia, le agradeceremos nos la haga llegar por este medio al siguiente correo electrónico: soporteSMAP@grupolatis.net<br><br>
					</span>
					";	
	*/
	
		$cuerpoMail="	
						<table width=\"800\"><tr><td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/logoSalud.gif\"></td>
						<td width=\'250\' align=\"center\"><img width=\"75%\" height=\"75%\"  src=\"http://censida.grupolatis.net/images/censida/FIRMA_ELECTRONICAaaa.png\"></td></tr></table>
						<br><br><h1>Centro Nacional para La Prevención y el<br>
						Control Del VIH y el sida</h1>
						<span style='font-size:12px'>
						<br><b>Estimada/o _nombre:</b><br><br>

						Gracias por participar como revisor en esta etapa de la <b>\"CONVOCATORIA PÚBLICA DIRIGIDA A LAS ORGANIZACIONES DE LA SOCIEDAD CIVIL CON EXPERIENCIA Y TRABAJO COMPROBABLE EN PREVENCIÓN DEL VIH E INFECCIONES DE TRANSMISIÓN SEXUAL (ITS) PARA LA IMPLEMENTACIÓN DE ESTRATEGIAS DE PREVENCIÓN FOCALIZADA DEL VIH Y OTRAS ITS QUE FORTALEZCAN LA RESPUESTA NACIONAL 2015.\"</b>, le informo que se han asignado proyectos para su evaluación, el procedimiento es muy simple, usted deberá:<br><br>
						a) Ingresar al SMAP (<a href=\"http://censida.grupolatis.net/principalCensida/acceso.php\">http://censida.grupolatis.net/principalCensida/acceso.php</a>) con su cuenta de acceso.<br><br>
						b) Seleccionar de su lista de proyectos asignados el proyecto a evaluar y presionar el botón \"Evaluar proyecto\"<br><br>
						c) Una vez leído el proyecto (usted puede leerlo online o descargar una versión en Microsoft Word dando click en el botón \"Descargar versión imprimible\"), deberá contestar el cuestionario de evaluación y presionar el botón \"Finalizar evaluación\", con esta acción su evaluación será guardada y el proyecto desaparacerá de su lista de pendientes por evaluar.<br><br>
						
						Sus datos de acceso son:<br><br>
						<b>Usuario:</b> _usuario<br>
						<b>Contraseña:</b> _contrasena<br><br>
						Se envía en adjunto la convocatoria 2015 así como un resúmen del mismo, se recomienda su lectura antes de comenzar la evaluación. Si presenta alguna
						duda o problema puede dirigirla a soportesmap@grupolatis.net. Para una mejor experiencia de evaluación se recomienda el uso del navegador web Google Chrome.<br><br>
						Finalmente, sólo nos queda agradecer nuevamente su participación en esta etapa del proceso.<br><br>
						<b>Atte.</b><br>
						Centro Nacional para La Prevención y el Control Del VIH y el sida
						<br><br>
						</span>
					";
	
	
/*		$cuerpoMail="	
						<table width=\"800\"><tr><td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/logoSalud.gif\"></td>
						<td width=\'250\' align=\"center\"><img width=\"75%\" height=\"75%\"  src=\"http://censida.grupolatis.net/images/censida/FIRMA_ELECTRONICAaaa.png\"></td></tr></table>
						<br><br><h1>Centro Nacional para La Prevención y el<br>
						Control Del VIH y el sida</h1>
						<span style='font-size:12px'>
						<br><b>Estimada/o _nombre:</b><br><br>

						Hemos detectado que aún no ha calificado alguno de los proyectos asignados para su evaluación, entendemos que su tiempo es limitado, sin embargo le pedimos nos apoye en esta labor lo más pronto posible, el procedimiento es muy simple, usted deberá:<br><br>
						a) Ingresar al SMAP (<a href=\"http://censida.grupolatis.net/principalCensida/acceso.php\">http://censida.grupolatis.net/principalCensida/acceso.php</a>) con su cuenta de acceso.<br><br>
						b) Seleccionar de su lista de proyectos asignados el proyecto a evaluar y presionar el botón \"Evaluar proyecto\"<br><br>
						c) Una vez leído el proyecto (usted puede leerlo online o descargar una versión en Microsoft Word dando click en el botón \"Descargar versión imprimible\"), deberá contestar el cuestionario de evaluación y presionar el botón \"Finalizar evaluación\", con esta acción su evaluación será guardada y el proyecto desaparacerá de su lista de pendientes por evaluar.<br><br>
						
						Sus datos de acceso son:<br><br>
						<b>Usuario:</b> _usuario<br>
						<b>Contraseña:</b> _contrasena<br><br>
						Nuevamente se envía en adjunto la convocatoria 2015 así como un resúmen del mismo, se recomienda su lectura antes de comenzar la evaluación. Si presenta alguna
						duda o problema puede dirigirla a soportesmap@grupolatis.net. Para una mejor experiencia de evaluación se recomienda el uso del navegador web Google Chrome.<br><br>
						Finalmente, sólo nos queda agradecer nuevamente su participación en esta etapa del proceso.<br><br>
						<b>Atte.</b><br>
						Centro Nacional para La Prevención y el Control Del VIH y el sida
						<br><br>
						</span>
					";*/
	
	
		/*$cuerpoMail="	
						<table width=\"800\"><tr><td width=\'250\' align=\"center\"><img  width=\"180\" height=\"60\" src=\"http://censida.grupolatis.net/images/censida/logoSalud.gif\"></td>
						<td width=\'250\' align=\"center\"><img width=\"75%\" height=\"75%\"  src=\"http://censida.grupolatis.net/images/censida/FIRMA_ELECTRONICAaaa.png\"></td></tr></table>
						<br><br><h1>Centro Nacional para La Prevención y el<br>
						Control Del VIH y el sida</h1>
						<span style='font-size:12px'>
						<br><b>Estimada/o _nombre:</b><br><br>
						Por este medio le informo que la fecha límite para llevar a cabo la evaluación de todos los proyectos es el día viernes 10 de abril de 2015.<br><br>
						Le recordamos sus datos de acceso y agradecemos la atención prestada:<br><br>
						<b>Usuario:</b> _usuario<br>
						<b>Contraseña:</b> _contrasena<br><br>
						<b>Atte.</b><br>
						Centro Nacional para La Prevención y el Control Del VIH y el sida
						<br><br>
						</span>
					";*/
	//La fecha límite para llevar a cabo la evaluación de todos los proyectos es el día Viernes 10 de abril de 2015.<br><br>
		$comp="";
		if($prueba)		
			$comp=" limit 0,1";
			
			
		/*$consulta="SELECT idRegistro,correos,nombreRevisor,titulo,tipo FROM 1010_distribucionRevisoresProyectos where reenviar=1 ".$comp;
		
		$res=$con->obtenerFilas($consulta);	
		while($fila=mysql_fetch_row($res))
		{
			$idUsuario=$fila[0];
			$nombre=(trim($fila[3])!="")?trim($fila[3])." ".$fila[2]:$fila[2];
			$parametro=bE("0_".$fila[0]."_498");
			$arrMails=explode(",",$fila[1]);
			
			
			
			foreach($arrMails as $m)
			{
				$mail=trim($m);
	
				$nCuerpo=str_replace("_nombreRevisor",$nombre,$cuerpoMail);
				$nCuerpo=str_replace("_parametro",$parametro,$nCuerpo);
				
				$obj=array();
				$obj[0]=$idUsuario;
				$obj[1]=$nombre;
				if($prueba)
					$mail="novant1730@hotmail.com";
				$obj[2]=$mail;
				
				if($mail!="")
				{
					if(!enviarMail($mail,"CENSIDA - Invitacion a revisor proceso 2015",$nCuerpo,"soporteSMAP@grupolatis.net","CENSIDA",$arrAchivos,NULL))			
					{
						
						array_push($arrUsrProblemas,$obj);
					}
					else
					{
						array_push($arrUsrOk,$obj);	
					}
				}
			}
		}*/
		
		$arrDestinatarios=array();
		
		$consulta="SELECT d.idUsuario,titulo,nombreRevisor,u.login,u.password FROM 
				1010_distribucionRevisoresProyectos d,800_usuarios u WHERE d.reenviar=1 and 
				u.idUsuario=d.idUsuario".$comp;

		
		$res=$con->obtenerFilas($consulta);	
		while($fila=mysql_fetch_row($res))
		{
			$consulta="SELECT Mail FROM 805_mails WHERE idUsuario=".$fila[0];
			$rMail=$con->obtenerFilas($consulta);
			while($fMail=mysql_fetch_row($rMail))
			{
				$mail=trim($fMail[0]);
				$obj=array();
				if($prueba)
					$mail="novant1730@hotmail.com";
				$obj[0]=$fila[0];
				$obj[1]="";
				$obj[2]=$mail;
				$obj[3]=(trim($fila[1])=="")?$fila[2]:$fila[1]." ".$fila[2];
				$obj[4]=$fila[3];
				$obj[5]=$fila[4];
				
				array_push($arrDestinatarios,$obj);
			}	
		}
		
		
		
		$arrUsrProblemas=array();
		$arrUsrOk=array();
		foreach($arrDestinatarios as $d)
		{
			$consulta="SELECT Login,PASSWORD,Nombre FROM 800_usuarios WHERE idUsuario=".$d[0];
			$fUsr=$con->obtenerPrimeraFila($consulta);
			$cuerpoMailFinal=str_replace("_usuario",$fUsr[0],$cuerpoMail);
			$cuerpoMailFinal=str_replace("_contrasena",$fUsr[1],$cuerpoMailFinal);
			$cuerpoMailFinal=str_replace("_nombre",$d[3],$cuerpoMailFinal);
			if(!enviarMail($d[2],"CENSIDA - Notificacion de proyectos asignados",$cuerpoMailFinal,"soporteSMAP@grupolatis.net","",$arrAchivos,NULL))			
			{
				array_push($arrUsrProblemas,$d);
			}
			else
			{
				array_push($arrUsrOk,$d);	
			}
		}
		
		
		echo "Usuarios Mal: ".sizeof($arrUsrProblemas)."<br>";
		varDump($arrUsrProblemas);
		echo "Usuarios OK: ".sizeof($arrUsrOk)." <br>";
		varDump($arrUsrOk);

	}

	function registrarEvaluacionProyectoCuestionarioFormulario2015($idFormulario,$idRegistro)
	{
		global $con;
		$idFormularioBase=498;
		$consulta="select idReferencia,responsable from  _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
		$fEvaluacion=$con->obtenerPrimeraFila($consulta);
		$consulta="UPDATE 1011_asignacionRevisoresProyectos SET situacion=2,fechaEvaluacion='".date("Y-m-d H:i:s")."',idReferencia=".$idRegistro.
				",idCuestionarioEval=".$idFormulario.",tipoCuestionario=2 WHERE idUsuario=".$fEvaluacion[1]." AND idProyecto=".$fEvaluacion[0].
				" AND idFormulario=".$idFormularioBase;
				
		if($con->ejecutarConsulta($consulta))
		{
			echo "window.parent.evaluacionFinal();return";
			return true;
		}
		
	}
	
	function recalcularEvaluaciones()
	{
		global $con;
		$arrEvaluaciones=array();
		
		$consulta="DELETE FROM _518_tablaDinamica WHERE id__518_tablaDinamica NOT IN
					(
					SELECT idReferencia FROM 1011_asignacionRevisoresProyectos WHERE idReferencia IS NOT NULL AND idReferencia<>''
					)
					";
		$con->ejecutarConsulta($consulta);
				
		$consulta="SELECT id__498_tablaDinamica FROM _498_tablaDinamica WHERE idEstado IN(2,3) AND marcaDescalificacion=0";
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$consulta="SELECT total,id__518_tablaDinamica FROM _518_tablaDinamica WHERE idReferencia=".$fila[0]." ORDER BY total";
			$rCal=$con->obtenerFilas($consulta);
			$nPos=0;
			switch($con->filasAfectadas)
			{
				case 5:
					$nPos=1;
				break;
				case 4:
					$nPos=2;
				break;
				case 3:
					$nPos=2;
				break;
				case 2:
					$nPos=2;
				break;
				case 1:
					$nPos=2;
				break;
				
			}
			$x=0;
			$query=array();
			$query[$x]="begin";
			$x++;
			
			while($fCal=mysql_fetch_row($rCal))
			{
				$query[$x]="UPDATE 1011_asignacionRevisoresProyectos SET noEvaluacionOrden=".$nPos." WHERE idReferencia=".$fCal[1];
				$x++;
				$nPos++;
			}
			$query[$x]="commit";
			$x++;
			$con->ejecutarBloque($query);
		}
	}
	
	function recalcularEvaluaciones2()
	{
		global $con;
		$arrEvaluaciones=array();
		
		$consulta="DELETE FROM _518_tablaDinamica WHERE id__518_tablaDinamica NOT IN
					(
					SELECT idReferencia FROM 1011_asignacionRevisoresProyectos WHERE idReferencia IS NOT NULL AND idReferencia<>''
					)
					";
		$con->ejecutarConsulta($consulta);
				
		$consulta="SELECT id__498_tablaDinamica FROM _498_tablaDinamica WHERE idEstado IN(2,3) AND marcaDescalificacion=0";
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$consulta="SELECT total,id__518_tablaDinamica FROM _518_tablaDinamica WHERE idReferencia=".$fila[0]." ORDER BY total";
			$rCal=$con->obtenerFilas($consulta);
			$nPos=0;
			switch($con->filasAfectadas)
			{
				case 5:
					$nPos=1;
				break;
				case 4:
					$nPos=1;
				break;
				case 3:
					$nPos=2;
				break;
				case 2:
					$nPos=2;
				break;
				case 1:
					$nPos=2;
				break;
				
			}
			$x=0;
			$query=array();
			$query[$x]="begin";
			$x++;
			
			while($fCal=mysql_fetch_row($rCal))
			{
				$query[$x]="UPDATE 1011_asignacionRevisoresProyectos SET noEvaluacionOrden2=".$nPos." WHERE idReferencia=".$fCal[1];
				$x++;
				$nPos++;
			}
			$query[$x]="commit";
			$x++;
			$con->ejecutarBloque($query);
		}
	}
	
	
	function mostrarAjustesPresupuestales($idFormulario,$idReferencia)
	{
		global $con;
		
		$consulta="SELECT COUNT(*) FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idReferencia.
				" AND marcaAutorizado=1 AND descalificado=0 AND presupuestoAutorizadoCENSIDA=0
				
				";
				
		$nRegistro=$con->obtenerValor($consulta);	
		if($nRegistro==1)
		{
			return 1;
		}
		
		return 0;
		
	}
	
	function registrarEvaluacionProyectoCuestionarioFormulario20152da($idFormulario,$idRegistro)
	{
		global $con;
		$idFormularioBase=522;
		$consulta="select idReferencia,responsable from  _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
		$fEvaluacion=$con->obtenerPrimeraFila($consulta);
		$consulta="UPDATE 1011_asignacionRevisoresProyectos SET situacion=2,fechaEvaluacion='".date("Y-m-d H:i:s")."',idReferencia=".$idRegistro.
				",idCuestionarioEval=".$idFormulario.",tipoCuestionario=2 WHERE idUsuario=".$fEvaluacion[1]." AND idProyecto=".$fEvaluacion[0].
				" AND idFormulario=".$idFormularioBase;
				
		if($con->ejecutarConsulta($consulta))
		{
			echo "window.parent.evaluacionFinal();return";
			return true;
		}
		
	}
	
	function registrarEvaluacionProyectoCuestionarioFormulario20152daCat10($idFormulario,$idRegistro)
	{
		global $con;
		$idFormularioBase=522;
		$consulta="select idReferencia,responsable from  _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
		$fEvaluacion=$con->obtenerPrimeraFila($consulta);
		$consulta="UPDATE 1011_asignacionRevisoresProyectos SET situacion=1,fechaEvaluacion='".date("Y-m-d H:i:s")."',idReferencia=".$idRegistro.
				",idCuestionarioEval=".$idFormulario.",tipoCuestionario=2 WHERE idUsuario=".$fEvaluacion[1]." AND idProyecto=".$fEvaluacion[0].
				" AND idFormulario=".$idFormularioBase;
				
		if($con->ejecutarConsulta($consulta))
		{
			echo "window.parent.parent.invocarEjecucionFuncionContenedor('imprimirFormatoComentarios','".$idRegistro."');";
			echo "window.parent.evaluacionFinal();return";
			return true;
		}
		
	}
	

	function rendererPerfilOSC($idFormulario,$idRegistro)
	{
		global $con;
		$consulta="SELECT codigoInstitucion FROM _367_tablaDinamica WHERE id__367_tablaDinamica=".$idRegistro;
		$codigoInstitucion=$con->obtenerValor($consulta);
		$consulta="SELECT id__485_tablaDinamica FROM _485_tablaDinamica WHERE codigoInstitucion='".$codigoInstitucion."'";
		$idOSC=$con->obtenerValor($consulta);
		
		if($idOSC!="")
		{
			return "Ver perfil de la organización";
		}
		return "";
		
	}
	
	function esCuentaLimitada()
	{
		if(existeRol("'117_0'"))
			return 0;
		else
			return 1;
	}
	
?>