<?php
	include("latis/conexionBD.php"); 
	function obtenerNombrePrograma($IdPrograma)
	{
		global $con;
		$consulta="select nombrePrograma from 4004_programa where idPrograma=".$IdPrograma;
		$programa=$con->obtenerValor($consulta);
		return $programa;
	}
	
	 function obtenerGenero($Genero)
	{
		if ($Genero==0)//Hombre
			return "Masculino";
		else
			return "Femenino";
	}

	 /*function cambiaraFechaMysql($fecha)
	{
		$dia=substr($fecha,0,2);
		$mes=substr($fecha,3,2);
		$anio=substr($fecha,6,4);
    	return $anio."-".$mes."-".$dia; 
	}*/

//Calcula el numero de meses a partir de la fecha
function calculaMeses($fecha)
{
	switch($fecha)
	{
		case '10000': $mes=12;break;
		case '9900': $mes=11;break;
		case '9800': $mes=10;break;
		case '900': $mes=9;break;
		case '800': $mes=8;break;
		case '700': $mes=7;break;
		case '600': $mes=6;break;
		case '500': $mes=5;break;
		case '400': $mes=4;break;
		case '300': $mes=3;break;
		case '200': $mes=2;break;
		case '100': $mes=1;break;
	}
	return $mes;
}

//Calcula la fecha a partir del numero de meses
function calculaFechas($mes,$idUsuario,$con)
{
	switch($mes)
	{
		case 12: $v="10000";break;
		case 11: $v="9900";break;
		case 10: $v="9800";break;;
		case 9: $v="900";break;
		case 8: $v="800";break;
		case 7: $v="700";break;
		case 6: $v="600";break;
		case 5: $v="500";break;
		case 4: $v="400";break;
		case 3: $v="300";break;
		case 2: $v="200";break;
		case 1: $v="100";break;
	}
	$sql="SELECT FechaActualiza+".$v." FROM 800_usuarios WHERE idUsuario=".$idUsuario;
	$f=$con->obtenerValor($sql);
	//echo "Valor ".$f;
	$ano=substr($f,0,4);
	$mes=substr($f,4,2);
	$dia=substr($f,6,2);
	$fec=$ano."-".$mes."-".$dia;
	return $fec;
}

function subirImagen($arch)
{

	// obtenemos los datos del archivo
		$tamano = $_FILES[$arch]['size'];
		$tipo = $_FILES[$arch]['type'];
		$archivo = $_FILES[$arch]['name'];
		
		$prefijo = substr(md5(uniqid(rand())),0,6);
		if ($archivo != "") 
		{
			$ext=substr($archivo,strlen($archivo)-3);
			if($ext=="jpg" || $ext=="JPG" || $ext=="png" || $ext=="PNG" || $ext=="PEG" || $ext=="peg")
			{
				// guardamos el archivo a la carpeta files
				$destino =  "images/".$prefijo."_".$archivo;
				copy($archivo['tmp_name'],$destino);
				$archivo=$prefijo."_".$archivo;
				return $archivo;
			}
			else
			{
				echo"<script 'javascript'>alert('Por favor seleccione un archivo de imgen válido');</script>";
				return "";
			}
		} 
		return "";
}

function guardarImagen($imagen)
{
	$img = imagecreatefromjpeg($imagen);
	ob_start();
	imagejpeg($img);
	$jpg = ob_get_contents();
	ob_end_clean();
	$jpg = str_replace('##','##',mysql_escape_string($jpg));
	return $jpg;
}
?>