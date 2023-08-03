<?php 
include_once("latis/conexionBD.php");

function guardarProductosCarta($idRegistro,$objRegistro)
{
	global $con;
	
	$obj=json_decode(bD($objRegistro));
	
	$x=0;
	$consulta[$x]="begin";
	$x++;
	$consulta[$x]="DELETE FROM _720_productosAsociados WHERE idReferencia=".$idRegistro;
	$x++;
	foreach($obj->arrProductos as $l)
	{
		
		$consulta[$x]="INSERT INTO _720_productosAsociados(idReferencia,idProducto,llave) VALUES(".$idRegistro.",".$l->idProducto.",'".$l->llave."')";
		$x++;
		
	}
	$consulta[$x]="commit";
	$x++;
	
	return $con->ejecutarBloque($consulta);
}



?>