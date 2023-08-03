<?php
	session_start();
	include("configurarIdiomaJS.php");
	include("conexionBD.php");
	$consulta="SELECT * FROM 9049_visoresDocumentos ORDER BY extension";
	$arrVisores=$con->obtenerFilasArreglo($consulta);
?>
var arrVisores=<?php echo $arrVisores?>;

