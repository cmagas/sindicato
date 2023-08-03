<?php 	error_reporting(E_ALL ^ E_DEPRECATED);
mb_internal_encoding('UTF-8');
include_once("latis/cConexion.php");

	
$conPO=new cConexion("172.17.222.30","root","L@ti$2016","SGJP_TSJCDMX_PRODUCCION",true);

$URLServidorBBB="https://api.mynaparrot.com/bigbluebutton/novant";
$llaveServidorBBB="SFVojfNTgrMzPqrsyvgO";
$urlPantallaEsperaModeradorReunionPO="http://siguepj.poderjudicialcdmx.gob.mx:812/principalPortal/waitMeetingPO.php";
$urlPaginaRedireccionJoinReunionPO="https://grupolatis.net/principal/meeting.php";
$urlPaginaNotificacionVideoMeetingPO="https://siguepj.poderjudicialcdmx.gob.mx:812/principalPortal/videoMeetingPO.php";
$urlPaginaNotificacionEndMeetingPO="https://siguepj.poderjudicialcdmx.gob.mx:812/principalPortal/endMeetingPO.php";
?>