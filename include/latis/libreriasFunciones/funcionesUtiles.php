<?php	
	include_once("latis/libreriasFunciones/cPagoReferenciado.php"); 
	
	function obtenerFechaFormato($fecha,$formato)
	{
		return strtotime($formato,$fecha);
	}
	
	function obtenerFechaLunesSemanaActual()
	{
		$fechaActual=date("Y-m-d");
		
		$dia=date("N",strtotime($fechaActual));
		
		$diferencia=$dia-1;
		
		$fechaLunes=date("Y-m-d",strtotime("-".$diferencia." days",strtotime($fechaActual)));
		
		return "'".$fechaLunes."'";
	}
	
	function obtenerFechaDomingoSemanaActual()
	{
		$fechaActual=date("Y-m-d");
		$dia=date("N",strtotime($fechaActual));
		$diferencia=7-$dia;
		$fechaDomingo=date("Y-m-d",strtotime("+".$diferencia." days",strtotime($fechaActual)));
		return "'".$fechaDomingo."'";
	}
	
	function obtenerFiltroPeriodosFechaActual($noFiltro)
	{
		
		$resultado=array();
		if($noFiltro==1)	
		{
			$resultado[0]=">=";
			$resultado[1]=obtenerFechaLunesSemanaActual();
		}
		else
		{
			$resultado[0]="<=";
			$resultado[1]=obtenerFechaDomingoSemanaActual();	
		}
		
		return $resultado;
	}

?>