<?php
	global $et;
	include("latis/conexionBD.php"); 
	function cargarIdioma()
	{
		global $con;
		$urlPagina= $_SERVER['PHP_SELF'];
		$aPagina= explode("/",$urlPagina);
		$pNombreAux=   $aPagina[count($aPagina)-1];
		$aNombre=explode(".",$pNombreAux);
		$pNombre=$aNombre[0];
		$lenguaje="1";
		if(isset($_SESSION["leng"]))
			$lenguaje=$_SESSION["leng"];
		$consulta="select nombre,texto from 814_etiquetasIdiomaPaginas where idIdioma=".$lenguaje." and (pagina like '%".$pNombre."%' or pagina='*')";
		//echo $consulta;
		global $et;
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
			$et[$fila[0]]=uE($fila[1]);
	}
	cargarIdioma();
?>