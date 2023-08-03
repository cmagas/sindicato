<?php	
	include("latis/conexionBD.php"); 
	include_once("latis/cPresupuesto.php");
	include_once("latis/cContabilidad.php");
	
	class cProcesadorAsientos
	{
		var $rPresupuesto;
		var $rContabilidad;
		function cProcesadorAsientos()
		{
			$this->rPresupuesto=new cPresupuesto();
			$this->rContabilidad=new cContabilidad();
		}
		
		function registrarAsientosPedido($idPedido,$folioMovimiento="",$tMovimiento,&$consulta=null,&$ct=null)
		{
			global $con;
			$x=0;
			$folio="";
			if($folioMovimiento!="")
				$folio=$folioMovimiento;
			else
				$folio=$this->rPresupuesto->obtenerFolioSiguiente($tMovimiento);
			
			
			if($consulta!=null)
			{
				$x=$ct;
				
			}
			else
			{
				$consulta=array();
				$consulta[$x]="begin";
				$x++;
			}
			$arrAsientosPresupuestales=array();
			$arrAsientosContables=array();
			$query="select * from 9102_PedidoCabecera where idPedido=".$idPedido;
			$fPedido=$con->obtenerPrimeraFila($query);
			$query="SELECT * FROM 9108_distribucionPedido WHERE idPedido=".$idPedido;
			$resSol=$con->obtenerFilas($query);
			while($filaDist=mysql_fetch_row($resSol))
			{
				$query="SELECT a.cveAlmacen,a.nombreAlmacen FROM 9101_CatalogoProducto c,9030_almacenes a WHERE idProducto=".$filaDist[3]." AND c.idAlmacen=a.idAlmacen";
				$fAlmacen=$con->obtenerPrimeraFila($query);
				$idAlmacen=$fAlmacen[0];
				$nAlmacen=$fAlmacen[1];
				
				$montoMovimiento=$filaDist[7];
				$departamento=$filaDist[2];
				
				$idPrograma=$filaDist[8];
				$partida=$filaDist[10];
				$tPresupuesto=$filaDist[12];
				$ruta=$filaDist[9];
				$ciclo=$filaDist[11];
				$cadObj='{
							  "fechaMovimiento":"'.date("Y-m-d").'",
							  "folio":"'.$folio.'",
							  "idResponsableMovimiento":"'.$_SESSION["idUsr"].'",
							  "montoMovimiento":"'.$montoMovimiento.'",
							  "tipoMovimiento":"'.$tMovimiento.'",
							  "mes":"'.date("m",strtotime($fPedido[5])).'",
							  "idRegistro":"'.$idPedido.'",
							  "idPrograma":"'.$idPrograma.'",
							  "ruta":"'.$ruta.'",
							  "idCiclo":"'.$ciclo.'",
							  "departamento":"'.$departamento.'",
							  "capitulo":"'.substr($partida,0,3).'",
							  "partida":"'.$partida.'",
							  "horaMovimiento":"'.date("H:i").'",
							  "tipoPresupuesto":"'.$tPresupuesto.'"
						  }';
					$objAsiento=json_decode($cadObj);
					array_push($arrAsientosPresupuestales,$objAsiento);
					$cadObj='{
								"tipoMovimiento":"'.$tMovimiento.'",
								"folio":"'.$folio.'",
								"montoOperacion":"'.$montoMovimiento.'",
								"idPrograma":"'.$idPrograma.'",
								"codDepto":"'.$departamento.'", 
								"codPartida":"'.$partida.'", 
								"idLibro":"'.$this->rContabilidad->generarLibroDelDia().'",
								"tipoPresupuesto":"'.$tPresupuesto.'"@resto
							}';
					if($idAlmacen!="")
						$cadObj=str_replace("@resto",',"datosComplementarios":{"noAlmacen":"'.$idAlmacen.'","nombreAlmacen":"'.$nAlmacen.'","idPedido":"'.$idPedido.'"}',$cadObj);
					else
						$cadObj=str_replace("@resto","",$cadObj);
					
					$objAsiento=json_decode($cadObj);
					array_push($arrAsientosContables,$objAsiento);
			}
			
			$this->rPresupuesto->registrarAfectacionesPresupuestales($arrAsientosPresupuestales,$consulta,$x);
			$this->rContabilidad->asentarArregloMovimientos($arrAsientosContables,$consulta,$x);
			if($ct==null)
			{
				$consulta[$x]="commit";
				$x++;
				return $con->ejecutarBloque($consulta);
			}
			else
				$ct=$x;
			return true;
		}
	}
	
	
?>