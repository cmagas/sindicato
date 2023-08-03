<?php
include("conexionBD.php");
	
	if(isset($_GET['id'])) 
	{	
		$sql = "SELECT nombreArchivo,datosArchivo,tipoMIME,tamArchivo FROM 4080_archivosEditor WHERE idArchivo=".$_GET['id'];
		$res=$con->obtenerRegistros($sql);
		header("Content-type: ".$res[2]);
		header("Content-length: ".$res[3]); 
		header("Content-Disposition: inline; filename=".$res[0]);
		echo $res[1];
	}
	else
	{	
		if(isset($_GET['itipo'])) 
		{	
		$sql = "SELECT nombreArchivo,datosArchivo,tipoMIME,tamArchivo FROM 4080_archivosEditor WHERE tipoArchivo=".$_GET['itipo'];
		$res=$con->obtenerRegistros($sql);
		
		header("Content-type: ".$res[2]);
		header("Content-length: ".$res[3]); 
		header("Content-Disposition: inline; filename=".$res[0]);
		
		echo $res[1];
	
		}
	}
?> 