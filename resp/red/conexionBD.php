<?php 	error_reporting(E_ALL ^ E_DEPRECATED);

mb_internal_encoding('UTF-8');
include_once("latis/cConexion.php");

	
	//Investigacion 
	//Expendiente grupoLatisMigracion
	
	$con=new cConexion("localhost:3306","sommeil_plataforma","grup0latis17","sommeil_plataforma",true);//tus datso
	
	include_once("latis/funcionesComunes.php");
	include_once("latis/funcionesSistema.php");
	include_once("latis/funcionesSistemaAux.php");
	include_once("latis/funcionesSistemaNomina.php");
	include_once("latis/funcionesSistemaNominaFiniquito.php");

	$tipoServidor=2; //Linux, 2 Windows
	//---agenda
	
	$urlSitio="http://bpm.sommeil.mx//";
	$baseDir=$_SERVER["DOCUMENT_ROOT"]."";
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
	
	//---------------Programa academico
	$tituloPrograma="Plan de estudios";
	$tituloProgramaPlural="Planes de estudio";
	$tituloDefMateriaSingular="Materia";
	$tituloDefMateriaPlural="Materias";
	$tituloDefGrupoSingular="Grupo";
	$tituloDefGrupoPlural="Grupos";
	$tituloProfesorSingular="Profesor";
	$tituloProfesorPlural="Profesores";
	$tituloParticipanteInvitadoSingular="Profesor invitado";
	$tituloParticipanteInvitadoPlural="Profesores invitado";
	$preguntarNivel=false;
	$preguntarTipoPrograma=true;
	$mostrarEnVistaMateriasTipoCriterioEval=true;
	$calcularGradoMapa=false;
	$mostrarCalEvidencia="false";
	$mostrarTotalSoicitados="false";
	$habilitarAsitencias="true";
	$habilitarFaltas="false";
	$profesorJustificaFalta="true";
	$borrarFaltas="true";
	$calcularAsistencias="false";
	$manejarSedes="true";
	//imagenes
	$imgExtValidas="jpg|gif|png"; //extensiones válidas
	
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
	//Video
	$videoExtValidas="flv";
	//Archivos
	$maxTamanioArchivos=3; //MB;
	$arrMesLetra[0]="Enero";
	$arrMesLetra[1]="Febrero";
	$arrMesLetra[2]="Marzo";
	$arrMesLetra[3]="Abril";
	$arrMesLetra[4]="Mayo";
	$arrMesLetra[5]="Junio";
	$arrMesLetra[6]="Julio";
	$arrMesLetra[7]="Agosto";
	$arrMesLetra[8]="Septiembre";
	$arrMesLetra[9]="Octubre";
	$arrMesLetra[10]="Noviembre";
	$arrMesLetra[11]="Diciembre";
	$arrDiasSemana[0]="Domingo";
	$arrDiasSemana[1]="Lunes";
	$arrDiasSemana[2]="Martes";
	$arrDiasSemana[3]="Miércoles";
	$arrDiasSemana[4]="Jueves";
	$arrDiasSemana[5]="Viernes";
	$arrDiasSemana[6]="Sábado";
	
	$arrPosicionOrd[1]="Primera";
	$arrPosicionOrd[2]="Segunda";
	$arrPosicionOrd[3]="Tercera";
	$arrPosicionOrd[4]="Cuarta";
	$arrPosicionOrd[5]="Quinta";
	$arrPosicionOrd[6]="Sexta";
	$arrPosicionOrd[7]="Séptima";
	$arrPosicionOrd[8]="Octava";
	$arrPosicionOrd[9]="Novenda";
	$arrPosicionOrd[10]="Décima";
	
	$arrPosicionOrd[11]="Décimo primera";
	$arrPosicionOrd[12]="Décimo segunda";
	$arrPosicionOrd[13]="Décimo tercera";
	$arrPosicionOrd[14]="Décimo cuarta";
	$arrPosicionOrd[15]="Decimo quinta";
	
	$tipoOrganigrama=1; //0 General; 1 Estructura gobierno
	///Configuracion comites
	$lblComiteS="Comisi&oacute;n";
	$lblComiteP="Comisiones";
	
	$tipoCosto=1; //1 Ultimo costo; 2 Promedio
	$nPromedio=2;
	$tipoOG="2"; //estandar 2010;2 estandar 2011
	
	//Nomina
	$considerarAdscripcion=true;
	
	$arrFechasHorario[0]="2011-06-05";
	$arrFechasHorario[1]="2011-06-06";
	$arrFechasHorario[2]="2011-06-07";
	$arrFechasHorario[3]="2011-06-08";
	$arrFechasHorario[4]="2011-06-09";
	$arrFechasHorario[5]="2011-06-10";
	$arrFechasHorario[6]="2011-06-11";
	
	
	$permitirRegistro=true;
	$mostrarBusquedaInstitucion=false;
	$procesoRegistroAsociado=-1;
	
	$consultaInclude="select distinct archivoInclude from 9033_funcionesSistema WHERE archivoInclude<>''";
	$resInclude=$con->obtenerFilas($consultaInclude);
	while($fInclude=mysql_fetch_row($resInclude))
	{
		
		if(file_exists($baseDir."/include/".$fInclude[0]))
		{
			
			include_once($fInclude[0]);	

		}
	}
	
	$iEstiloMenu=5;
	
	$mostrarOpcionRegresar=false;
	$referenciaFiltros=".";
	if(isset($_SESSION["codigoInstitucion"]))
		$referenciaFiltros=$_SESSION["codigoInstitucion"];
	//$referenciaFiltros=".";
	$paginaInicioLogin="../principalPortal/inicio.php";
	$paginaCierreLogin="../principalPortal/index.php";
	
	
	//Estilo
	
	$consultaEstilo="SELECT estiloLetraFormulario,estiloLetraControles FROM 4081_colorEstilo";
	$fEstilo=$con->obtenerPrimeraFila($consultaEstilo);
	if(!$fEstilo)
	{
		$fEstilo[0]="corpo8_bold";
		$fEstilo[1]="";
	}
	$estiloLetraFormulario=$fEstilo[0];
	$estiloLetraControles=$fEstilo[1];
	
	$visorListadoProcesosProcesos="../modeloProyectos/visorRegistrosProcesosV2.php";
	$visorExpedienteProcesos="../modeloPerfiles/vistaDTDv3.php";
	$pathScriptsPaginasDinamicas="../modulosEspeciales_SGJP/Scripts";
	$comandoLibreOffice='export HOME=/tmp && libreoffice ';
	$urlWebServicesConversionPDF="http://172.19.223.28:9091/Service.asmx";
	$funcionWebServicesConversionPDF="convertirPDFToWORD";
	
	
	$servidorPruebas=true;
		
	
	$leyendaTribunal=utf8_encode('Año de Leona Vicario, Benemérita Madre de la Patria');
	$versionLatis="Z3J1cDBsYXRpczE3";
	$Enable_AES_Ecrypt=true;
	
	$arrRutasAlmacenamientoDocumentos[0]=$baseDir."\\repositorioDocumentos";
	//$arrRutasAlmacenamientoDocumentos[1]="Z:\\repositorioDocumentos";
	//$arrRutasAlmacenamientoDocumentos[2]="http://172.17.222.30";

	
	$arrRutasAlmacenamientoXMLSolicitudes[0]=$urlRepositorioDocumentos."\\repositorioDocumentosXMLSolicitudes";
	
	
	//$tipoFirmaPermitida[1]=false; 	//FIEL;
	$tipoFirmaPermitida[2]=true;	//FIREL
	$tipoFirmaPermitida[4]=true;	//DOcumento escaneado
	$URLServidorFirma="http://172.19.223.28/firmaDocumentos.asmx";
	$nombreFuncionFirma="firmaDocumento";
	$llaveFirmado="B04BD9E8EC63CA21AB16BA1D40BA306F16D8C0D5";
	$tipoMateria="SJCOL";
	$respaldarDocumentoPrevioFirma=false;
	
	$arrAudienciasIntermedias["15"]=1;
	$arrAudienciasIntermedias["142"]=1;
	$arrAudienciasIntermedias["223"]=1;
	
	//PGJ
	$pruebasPGJ=false;
	$cancelarNotificacionesPGJ=false;
	$urlPruebas="http://172.22.109.163/wsTribunalSJ.asmx?wsdl";
	$urlProduccion="http://172.22.109.146/wsTribunalSJ.asmx?wsdl";
	
	//Servicio QR
	$utilizarServidorQR=false;
	$urlServidorQR="http://172.19.223.26/service/index.php?wsdl";
	$llaveQR="C1423387-CFC4-460A-BA43-5F7663E19167";
	
	//Contenido Carpeta judicial
	$registrarIDCarpeta=false;
	
	//Sevicio USMECA
	$urlWSSobreseimientoUsmeca="http://10.17.5.29:8080/solicitud-evaluacion/SolicitudSobreseimientoService?WSDL";
	$nombreFuncionSobreseimientoUsmeca="SolicitudSobreseimientoService";

	$urlWSInformeMedidaSCPUsmeca="http://10.17.5.29:8080/solicitud-evaluacion/SolicitudService?WSDL";
	$nombreFuncionInformeMedidaSCPUsmeca="SolicitudImposicionService";
	
	$considerarSecretariasTareas=false;
	$rutaImgenesEscenario="../images";
	
	
	//Big Blue  Button
	$URLServidorBBB="https://api.mynaparrot.com/bigbluebutton/novant";
	$llaveServidorBBB="SFVojfNTgrMzPqrsyvgO";
	$prefijoUsuarioBBB="novant";
	$urlPantallaAccesoReunion=$urlSitio."/principalPortal/startMeeting.php";
	$pathScriptsPaginasDinamicas="../paginasScripts";
?>
