<?php
include_once("conexionBD.php"); 

function indenizacion($fi,$ff,$salDia)
{
	global $con;
	
	$sumaTotal=0;
	
	//echo "fecha 1 ". $fi;
	
//	$desde1=cambiaraFechaMysql($fi);
//	$hasta2=cambiaraFechaMysql($ff);
$desde1=$fi;
$hasta2=$ff;
	$salarioDiario=$salDia;
	
	$fechaSumaInicial=sumarDiasFecha($desde1,365);
	
	
	if($hasta2<$fechaSumaInicial)
	{
		$valorFecha=restarDiasFecha($hasta2,1);
		$hasta1=$valorFecha;
	}
	else
	{
		$valorFecha=restarDiasFecha($desde1,1);
		$hasta1=$valorFecha;
	}
	
	if($desde1==$hasta2)
	{
		$desde2=$hasta2;
	}
	else
	{
		$desde2=sumarDiasFecha($hasta1,1);
	}
	
	$diferenciaDias1=obtenerDiferenciaDias($desde1,$hasta1);
	$diferenciaDiasA=$diferenciaDias1-3;
	
	$diferenciaDias2=obtenerDiferenciaDias($desde2,$hasta2);
	
	$anioLaborado1=$diferenciaDiasA/360;
	$anioLaborado2=$diferenciaDias2/360;
	
	
	$numeroDiaSancion1=30;
	$numeroDiaSancion2=20;
	
	if($anioLaborado1>0)
	{
		$total1=$salarioDiario*$numeroDiaSancion1;
	}
	else
	{
		$total1=0;
	}
	
	if($anioLaborado2>0)
	{
		$total2=$salarioDiario*$numeroDiaSancion2;
	}
	else
	{
		$total2=0;
	}
	
	$sumaTotal=$total1+$total2;
	
	return $sumaTotal;
}

function sancionmoratoria($fr,$fs,$salarioDia)
{
	global $con;
	$resultado=0;
	
	$fechaInicial=$fr;
	$fechaFinal=$fs;
	$salarioDiario=$salarioDia;
	
	
	$dias1=obtenerDiferenciaDias($fechaInicial,$fechaFinal);
	$dias2=obtenerDiferenciaDias($fechaFinal,$fechaInicial);
	
	$fechaSumaDia1=$dias1+1;
	$fechaSumaDia2=$dias2+1;
	
	if($fechaSumaDia1>720)
	{
		$numDias=720;
	}
	else
	{
		$numDias=$fechaSumaDia2;
	}
	
	$resultado=$salarioDiario*$numDias;
	
	return $resultado;
}

function interesesMoratorios($b16,$b17,$b19,$b4) //b16=primaServicioDicimbre, B17=primaServicioJunio, B19=Cesantia, B4=salarioInsoluto
{
	global $con;
	
	$valorFinal=0;
	
	$sumaT=$b16+$b17+$b19+$b4;
	
	$diasP=25;
	$interesesCon='0.2634';
	$interesDiario='.06329';
	
	$valorFinal=$diasP*($interesDiario/100)*$sumaT;
	
	return $valorFinal;
}

//obtenerDiferenciaDias($fechaI,$fechaF)

function sumarDiasFecha($fecha,$dias)
{
	global $con;
	
	return date("Y-m-d",strtotime($fecha."+ ".$dias." days"));
}

function restarDiasFecha($fecha,$dias)
{
	global $con;
	
	return date("Y-m-d",strtotime($fecha."- ".$dias." days"));
}
?>