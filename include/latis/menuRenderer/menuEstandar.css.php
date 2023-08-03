<?php
	header("Content-type: text/css");
	include("latis/conexionBD.php");
	$sql = "SELECT *FROM 4081_colorEstilo";
    $fila= $con->obtenerPrimeraFila($sql);
	$colorFondoEx=$fila[0];
	$colorFondoIn=$fila[1];
	$colorMenu=$fila[2];
	$colorBarraIn=$fila[3];
	$colorMenuIn=$fila[4];
	$colorBanner=$fila[5];
	$colorLink=$fila[6];
	$colorTiTabla=$fila[7];
	$colorCelda1=$fila[8];
	$colorCelda2=$fila[9];
	$colorLeTabla=$fila[10];
	$colorTxTabla=$fila[11];
	$colorFuMenu=$fila[12];
	$tamFuMenu=$fila[13];
	$colorBordeIn=$fila[14];
	$botonazo=$fila[15];
	$disenoBanner=$fila[16];
	$colorLeNivel1=$fila[20];
	$colorLeNivel2=$fila[21];
	$tituloMenuIzq=$fila[22];
	$colorLePieIzq=$fila[23];
	$colorLetra=$fila[26];
	$colorTxTabla1=$fila[24];
	$colorTxTabla2=$fila[25];
	$colorTxTabla3=$fila[26];
	$colorTxTabla4=$fila[27];	
	$colorTxTabla5=$fila[29];
	$colorCelda3=$fila[28];
	$colorBorde1=$fila[30];
	
	$colortxtImpre1=$fila[31];
	$colortxtImpre2=$fila[32];
	$colorCeldaImp1=$fila[33];
	$colorCeldaImp2=$fila[34];
?>
.content_right 
{
    padding: 6px;
    margin: 5px 0px;
    background-color: #EFEFEF;
}

.menuStandart 
{
    padding-top: 5px;
    padding-bottom: 10px;
    font-family: Arial;
    font-size: 12px;
}


.menu_title 
{
    background-color: #<?php echo $colorMenu?>;
    font-size: 12px;
    color: #fff;
    padding: 6px 3px 5px 5px;
}

.menu_title a
{
	color: #FFF !important;
}

.menu_link 
{
    list-style-type: none;
    margin: 0px 2px 0px 2px;
	padding: 7px 0px 7px 0px;
    display: block;
    border-bottom: 1px solid #fff;
    
}

.menu_link a
{
	color: rgb(73, 73, 101) !important;
}