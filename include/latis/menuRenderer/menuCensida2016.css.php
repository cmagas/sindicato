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
    background-color: #ED1B24;
    text-align: left;
    font-family: Ubuntu, sans-serif;
    font-size: 12px;
    color: #fff;
    padding: 6px 3px 5px 5px;
    
    /*border-radius: 13px 13px 13px 13px;
    -moz-border-radius: 13px 13px 13px 13px;
    -webkit-border-radius: 13px 13px 13px 13px;
    border: 0px solid #000000;*/
    border-radius: 8px 0px 8px 0px;
-moz-border-radius: 8px 0px 8px 0px;
-webkit-border-radius: 8px 0px 8px 0px;
border: 0px solid #000000;
    
}

.menu_title a
{
	color: #FFF !important;
}

.menu_link 
{
    font-family: Ubuntu, sans-serif;
    font-size:12px;
    list-style-type: none;
    margin: 0px 2px 0px 2px;
	padding: 7px 0px 7px 0px;
    display: block;
    border-top: 1px solid #d1d1d1;
    background-color: #f2f2f2;
	color:#000;
    
}


.menu_link a
{
	font-size:12px;
	color:#666 !important;
    text-decoration:none;
    
}


.sf-mega
	{
		
		position:absolute;

		z-index:4;
	}
	.tdMenu
	{
		
		background: #303334;
		border-left-style:dotted;
		border-left-width:thin;
		border-left-color:#FFF;
		
		border-bottom-style:dotted;
		border-bottom-width:thin;
		border-bottom-color:#FFF;
		
		min-width:200px;
		text-align:center;
		vertical-align:middle;
		
		color:#FFF;
		font-family: 'Ubuntu Condensed', sans-serif;
		font-size: 14px;
	}
	  
	.tdMenu a:hover 
	{
		text-decoration: none;
		color: #5fc7e6;
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

     background:url(../../../images/verMenos3.png) no-repeat scroll right center ;
     width:16px;
     height:16px;
     border:10px;
     
}

.cImagenClosed
{
   
     background:url(../../../images/verMas3.png) no-repeat scroll right center ;
     width:16px;
     height:16px;
     border:10px;
     
}                    


.menu_title_1 
{
    background-color: #ED1B24;
    text-align: left;
    font-family: Ubuntu, sans-serif;
    font-size: 12px;
    color: #fff;
    font-weight: bold;
    padding: 6px 3px 5px 5px;
    
    
    border-radius: 18px 0px 0px 0px;
    -moz-border-radius: 18px 0px 0px 0px;
    -webkit-border-radius: 18px 0px 0px 0px;
    border: 0px solid #000000;
    border-bottom: 1px solid #FFF;
        
}

.menu_title_2
{
    background-color: #ED1B24;
    text-align: left;
    font-family: Ubuntu, sans-serif;
    font-size: 12px;
    color: #fff;
    padding: 6px 3px 5px 5px;
    font-weight: bold;
    
    border-radius: 0px 0px 18px 0px;
    -moz-border-radius: 0px 0px 18px 0px;
    -webkit-border-radius: 0px 0px 18px 0px;
    border: 0px solid #000000;
    border-bottom: 1px solid #FFF;
        
}

