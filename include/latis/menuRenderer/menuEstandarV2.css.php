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
    text-align: left;
    font-family: Verdana, Arial, sans-serif;
    font-size: 12px;
    color: #<?php echo $colorTxTabla3?>;
    padding: 6px 3px 5px 5px;
    border-bottom: 1px dotted #FFF;
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
    border-bottom: 1px dotted #000;
    background-color:#FFF;
	color:#000;
    
}

.menu_link a
{
	color: #<?php echo $colorTxTabla4?>; !important;
}


.sf-mega
	{
		
		position:absolute;

		z-index:4;
	}
	.tdMenu
	{
		
	
	}
	  
	.tdMenu a:hover 
	{
	
	}
	
	.collapse-container>:nth-child(odd) {
	padding: 5px;
    background-color: gray;
    background-image: linear-gradient(bottom, gray 14%, #969696 70%);
    background-image: -o-linear-gradient(bottom, gray 14%, #969696 70%);
    background-image: -moz-linear-gradient(bottom, gray 14%, #969696 70%);
    background-image: -webkit-linear-gradient(bottom, gray 14%, #969696 70%);
    background-image: -ms-linear-gradient(bottom, gray 14%, #969696 70%);
    border: 1px solid black;
    margin: auto;
}
.collapse-container>:nth-child(even) {
	background-color: white;
	display: none;
	-moz-box-sizing: border-box; 
	-webkit-box-sizing: border-box; 
	box-sizing: border-box;
	border: 1px solid black;
}
.collapse-container>:nth-child(even) p {
    padding: 0px 5px;
}

.header-menu 	{
						float: left;
					}
                    
                    
.cImagenOpen
{

    content:url(../../../images/verMenos.png);
}

.cImagenClosed
{
   
    content:url(../../../images/verMas.gif);
}                    