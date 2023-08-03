<?php 	
	mb_internal_encoding('UTF-8');
	include_once("latis/cConexion.php");
	//error_reporting(E_ALL ^ E_DEPRECATED);

	date_default_timezone_set('America/Mexico_City');

	//Local
	$con=new cConexion("localhost","root","sistemas07","bd_sindicato",true);//tus datso

	//Red
	//$con=new cConexion("localhost","sgtecno2_sindicato","grup0latis17","sgtecno2_bdsidepev",true);//tus datso

	$tipoServidor=2; //Linux, 2 Windows
	//---agenda
	
	//$urlSitio="http://bpm.sommeil.mx//";
	$baseDir=$_SERVER["DOCUMENT_ROOT"]."";
	$baseDir="localhost/latis";
	$urlRepositorioDocumentos=$baseDir;
	$directorioInstalacion=$baseDir;
	$guardarArchivosBD=false;
	$considerarDeprecated=false;
	$SO=$tipoServidor; //1 Linux; 2 Windows
	$desHabilitarImageSize=true;
	$habilitarEnvioCorreo=true;
	$mailAdministrador="proyectos@grupolatis.net";
	$nombreEmisorAdministrador="Latis Admon";
	$considerarFichaMedica=false;
	$incluirCabeceraISO=false;
	$codificarUTF8uEJ=false;
	$codificarUTF8uE=false;
	$decodificarUTF8uEJ=false;
	$considerarJustificacionFaltas=false;
	$considerarReporteConducta=false; 
	$considerarAlumnosAsociados=false;
	$externosOrganigrama=false;
		
	//log de sistema
	$logInicioSesion=true;
	$logFinSesion=true;
	$logSistemaAccesoPaginas=true;
	$logSistemaModificacionBD=false; //insert,delete,update
	$logSistemaConsultaBD=false; //select
	//Configuracion materia
	$considerarActitudes=true;
	$considerarCompetencias=true;
	$considerarCriteriosE=true;
	$considerarHabilidades=true;
	$considerarTecnicasColaborativas=true;
	$considerarAreaPractica=true;
	$considerarProductos=true;
	
	$paginaInicioLogin="../Login/index.php";
	$paginaCierreLogin="../Login/index.php";
	
?>