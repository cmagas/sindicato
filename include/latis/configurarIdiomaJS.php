<?php
	global $etj;
	include("latis/conexionBD.php"); 
	function cargarIdiomaJS()
	{
		global $con;
		$urlPagina= $_SERVER['PHP_SELF'];
		$aPagina= explode("/",$urlPagina);
		$pNombreAux=   $aPagina[count($aPagina)-1];
		$aNombre=explode(".",$pNombreAux);
		$pNombre=$aNombre[0];
		$leng=1;
		if(isset($_SESSION["leng"]))
			$leng=$_SESSION["leng"];
		else
			$_SESSION["leng"]=1;
		$consulta="select nombre,texto from 815_etiquetasIdiomaScripts where idIdioma=".$leng." and (pagina like '%".$pNombre."%' or pagina='*')";
		//echo $consulta;
		global $etj;
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
			$etj[$fila[0]]=uEJ($fila[1]);
	}
	cargarIdiomaJS();
?>