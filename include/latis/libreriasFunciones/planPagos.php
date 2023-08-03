<?php

function calcularPlanPagosMonto($monto,$idPlan,$objParametro,$esVistaRealEjecucion=true,$idServicio=-1,$idCostoServicio=-1)
{
	global $con;

	$decimalesIndividual=0;
	$decimalesPago=0;
	$acumularDecimales=true;
	
	$montoDecimalAcumulado=0;
	$montoDecimalAcumulado1=0;
	$montoDecimalAcumulado2=0;
	$montoDecimalAcumuladoCargo1=0;
	$montoDecimalAcumuladoCargo2=0;
	$ultimoPago=false;
	$ultimoMonto=0;
	$consulta="SELECT p.* FROM 6020_planesPago p WHERE p.idPlanPagos = ".$idPlan;

	$fPlan=$con->obtenerPrimeraFila($consulta);
	$decimalesIndividual=$fPlan[4];
	$decimalesPago=$fPlan[5];
	if($fPlan[6]==0)
		$acumularDecimales=false;
	
	$totalPagos=$fPlan[3];
	$arrPagos=array();
	$pagoIndividual=normalizarNumero(number_format($monto/$totalPagos,$decimalesIndividual));
	$acumPago=0;
	$ct=1;
	$consulta="SELECT * FROM 6021_desglocePlanPagos WHERE idPlanPagos=".$fPlan[0]." order by noPago";
	$res=$con->obtenerFilas($consulta);
	$nPagos=$con->filasAfectadas;
	
	while($fila=mysql_fetch_row($res))
	{
		
		$obj=array();

		$montoPago=normalizarNumero(number_format($pagoIndividual*$fila[2],$decimalesPago));

		if($acumularDecimales)
		{
			$montoPago=parteEntera($montoPago,false);
			$montoDecimalAcumulado+="0.".parteDecimal($montoPago);
			$obj["decimalMontoPago"]="0.".parteDecimal($montoPago);
		}
		if($ct==$nPagos)
		{
			$montoPago=($monto-$acumPago)+$montoDecimalAcumulado;

			$ultimoPago=true;
		}
		if(($acumPago+$montoPago)>$monto)
		{
			$montoPago=$monto-$acumPago;
			
		}
		
		$acumPago+=$montoPago;
		$obj["noPago"]=$fila[3];
		$obj["etiquetaPago"]=$fila[1];
		$obj["pagoIndividual"]=$montoPago;
		$obj["arrDescuestosCargos"]=array();
		$ultimoMonto=$montoPago;
		if($idServicio!=-1)
		{
			$consulta="SELECT idConceptoAsociado FROM 564_conceptosVSPlanPago WHERE idConcepto=".$idServicio." AND idPlanPago=".$idPlan." AND noPago=".$fila[3];
			$idConceptoNoPago=$con->obtenerValor($consulta);
			if($idConceptoNoPago=="")
				$idConceptoNoPago=-1;
			$obj["idConceptoNoPago"]=$idConceptoNoPago;	
			$consulta="SELECT * FROM 564_conceptosVSOperacionesCargosDescuentos WHERE idConcepto =".$idServicio." ORDER BY ordenAplicacion";
			$resConcepto=$con->obtenerFilas($consulta);
			$arrColumnas=array();
			while($filaConcepto=mysql_fetch_row($resConcepto))
			{
				$objOperacion["fechaInicio"]="";
				$objOperacion["fechaFin"]="";
				$oBaseParametro=array();
				$oBaseParametro["idPlanPagos"]=$idPlan;
				if(sizeof($objParametro)>0)
				{
					foreach($objParametro as $nombre=>$valor)
					{
						$oBaseParametro[$nombre]=$valor;
					}
				}
				$arrReferencias=array();
				
				$consulta="SELECT idColumna,tipoValor FROM 565_configuracionColumnaOperacion WHERE idOperacion=".$filaConcepto[0]." ORDER BY idColumna";

				$resCol=$con->obtenerFilas($consulta);
				while($fCol=mysql_fetch_row($resCol))
				{
					$consulta="SELECT valor FROM 6016_valoresReferenciaCosteoServicios WHERE idCostoConcepto =".$idCostoServicio." AND 
								idPlanPago=".$idPlan." AND noPago=".$fila[3]." AND noColumna=".$fCol[0];

					$valor=$con->obtenerValor($consulta);
					$oBaseParametro["valorReferencia_".$fCol[0]]=$valor;
					$arrReferencias["".$fCol[0]]=$valor;
					
					switch($fCol[1])
					{
						case 4://Fecha inical de aplicación
							$objOperacion["fechaInicio"]=$valor;
						break;
						case 5://Fecha final de aplicación
							$objOperacion["fechaFin"]=$valor;
						break;
					}
				}
				$consulta="SELECT valor FROM 6016_valoresReferenciaCosteoServicios WHERE idCostoConcepto =".$idCostoServicio." AND idPlanPago=".$idPlan." AND noPago=".$fila[3]." AND noColumna=0";
				$valor=$con->obtenerValor($consulta);
				$obj["fechaVencimiento"]=$valor;
				
				$idTipoOperacion=$filaConcepto[2];
				$etiquetaOperacion=$filaConcepto[3];
				$origenValor=$filaConcepto[4];
				$valorCalculo=$filaConcepto[5];
				$funcionAplicacion=$filaConcepto[6];
				$calcularOperacion=0;
				
				if($esVistaRealEjecucion)
					$oBaseParametro["vistaEjecucion"]=1;
				else
					$oBaseParametro["vistaEjecucion"]=0;
				$oBaseParametro["montoBase"]=$montoPago;
				$oBaseParametro["montoAcumulado"]=$ultimoMonto;
				$oBaseParametro["idConceptoServicio"]=$idServicio;
				$oBaseParametro["noPago"]=$fila[3];
				$oBaseParametro["idCostoConcepto"]=$idCostoServicio;
				$oNull=NULL;
				$cadObj='{"param1":null}';
				$objParam1=json_decode($cadObj);
				$objParam1->param1=$oBaseParametro;
					
				if(($funcionAplicacion=="")||($funcionAplicacion==-1)||($oBaseParametro["vistaEjecucion"]==0))
					$calcularOperacion=1;
				else
				{
					
					$calcularOperacion=resolverExpresionCalculoPHP($funcionAplicacion,$objParam1,$oNull);
				}
				
				
				
				$montoColumna=0;
				if($calcularOperacion==1)
				{

					switch($origenValor)
					{
						case 1:  //funcion
							
							$montoColumna=resolverExpresionCalculoPHP($valorCalculo,$objParam1,$oNull);

						break;
						case 2:  //Porcentaje
							$montoColumna=$ultimoMonto*($valorCalculo/100);
						break;
						case 3:  //valor absoluto
							$montoColumna=$valorCalculo;
						break;	
					}
					if(gettype($montoColumna)=='array')
					{
						
						$ultimoMonto+=($montoColumna["montoDescuento"]*$idTipoOperacion);	
						if(isset($montoColumna["fechaInicio"]))
							$objOperacion["fechaInicio"]=$montoColumna["fechaInicio"];
						if(isset($montoColumna["fechaFin"]))
							$objOperacion["fechaFin"]=$montoColumna["fechaFin"];
						$objOperacion["cantidadMonto"]=$montoColumna["montoDescuento"];
					}
					else
					{
						
						$ultimoMonto+=($montoColumna*$idTipoOperacion);
						$objOperacion["cantidadMonto"]=$montoColumna;
					}
					$objOperacion["idConcepto"]=$filaConcepto[0];
					$objOperacion["etiqueta"]=$etiquetaOperacion;
					$objOperacion["opMonto"]=$ultimoMonto;
					
					$objOperacion["tipoOperacion"]=$idTipoOperacion;
					$objOperacion["valoresReferencia"]=array();
					if(sizeof($arrReferencias)>0)
					{
						foreach($arrReferencias as $id=>$v)
						{
							$objOperacion["valoresReferencia"][$id]=$v;
						}
					}
					
					array_push($obj["arrDescuestosCargos"],$objOperacion);
					
				}
				
			}
		}
		$obj["montoFinal"]=$ultimoMonto;
		
		array_push($arrPagos,$obj);
		
		
		$ct++;
	}
	return $arrPagos;

}
?>