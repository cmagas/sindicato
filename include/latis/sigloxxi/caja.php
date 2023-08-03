<?php
	include_once("latis/cAlmacen.php");
	
	function obtenerProductoCajaClave($clave,$tipoBusqueda,$idCaja,$tipoCliente,$idCliente)//1 Buscar por cB;2 Buscar clave aux
	{
		global $con;
		global $baseDir;	
		
		$consulta="SELECT idAlmacenAsociado FROM 6007_instanciasCaja WHERE idCaja=".$idCaja;
		$idAlmacen=$con->obtenerValor($consulta);
		$tPresupuestal=21; //Reemplazar
		$cA=new cAlmacen($idAlmacen);
		$arrProductos=array();
		switch($tipoBusqueda)
		{
			case 1:
			case 2:
				$consulta="SELECT c.idProducto,c.llaveProducto,c.clave FROM 6910_clavesProductos c,6901_catalogoProductos p WHERE c.tipoClave=".$tipoBusqueda." AND c.clave='".cv($clave)."'
						and p.idProducto=c.idProducto and p.situacion=1 limit 0,1";
		
				$res=$con->obtenerFilas($consulta);
				while($fDatos=mysql_fetch_row($res))
				{
					$regProducto=array();
					$regProducto["dimensiones"]=array();
					
					
					/*$regProducto["metaData"]="";
					$regProducto["idRegistro"]="";
					$regProducto["sL"]=0;
					$regProducto["numDevueltos"]=0;*/
					$archivoImagen="";
		
					$datosProducto=obtenerDatosProducto($fDatos[0],$fDatos[1],$idCaja,$tipoCliente,$idCliente);
					
					
					$regProducto["cveProducto"]=$fDatos[2];
					$regProducto["idProducto"]=$fDatos[0];
					$regProducto["cantidad"]=1;
					$regProducto["tipoConcepto"]=1;
					$regProducto["llaveProducto"]=$fDatos[1];
					$regProducto["tipoMovimiento"]="";
					
					
					$regProducto["concepto"]=$datosProducto["nombreProducto"];
					$regProducto["descripcion"]=$datosProducto["descripcion"];
					
					$regProducto["costoUnitario"]=$datosProducto["costoUnitario"];
					$regProducto["costoUnitarioNeto"]=$datosProducto["costoUnitario"];
					$regProducto["costoUnitarioConDescuento"]=$datosProducto["costoUnitario"];
					
					if($datosProducto["tipoPrecio"]==1)
					{
						$regProducto["costoUnitarioNeto"]=str_replace(",","",number_format($regProducto["costoUnitarioNeto"]/(1+($datosProducto["porcentajeIVA"]/100)),2));
					}
					
					$regProducto["descuento"]=0;
					
					
					
					$descuento=buscarDescuentoProducto($tipoCliente,$idCliente,$regProducto["idProducto"],$fDatos[1]);

					if(($descuento["pDescuento"]!="")&&($descuento["pDescuento"]!=0))
					{
						$costoNeto=$regProducto["costoUnitarioNeto"];
						$regProducto["descuento"]=str_replace(",","",number_format($costoNeto*($descuento["pDescuento"]/100),2));
						$regProducto["costoUnitarioConDescuento"]=str_replace(",","",number_format($regProducto["costoUnitario"],2))-str_replace(",","",number_format($regProducto["costoUnitario"]*($descuento["pDescuento"]/100),2));
						
						
						
					}
					
					if(isset($descuento["mDescuento"])&&($descuento["mDescuento"]!="")&&($descuento["mDescuento"]!=0))
					{
						$regProducto["descuento"]=$descuento["mDescuento"];
						$regProducto["costoUnitarioConDescuento"]=str_replace(",","",number_format($regProducto["costoUnitario"],2))-str_replace(",","",number_format($regProducto["descuento"],2));
						
					}
					
					

					$regProducto["porcentajeIVA"]=$datosProducto["porcentajeIVA"];
					
					$regProducto["tipoPrecio"]=$datosProducto["tipoPrecio"];
					
					$regProducto["imagen"]="[]";
					if(sizeof($datosProducto["imagenes"])>0)
					{
						$aImagen="";
						foreach($datosProducto["imagenes"] as $i)	
						{
							$dI='{"imagen":"'.$i.'"}';
							if($aImagen=="")
								$aImagen=$dI;
							else
								$aImagen.=",".$dI;
						}
						$regProducto["imagen"]="".$aImagen."";
					}
					$regProducto["detalle"]="";
					
					$regProducto["unidadMedida"]=$datosProducto["unidadMedida"];
					$regProducto["arrUnidadesMedida"]=$datosProducto["arrUnidadesMedida"];
					$existencia=$cA->existeSuficienciaTiempoMovimientoV2($regProducto["idProducto"],1,$datosProducto["unidadMedida"],$tPresupuestal,convertirLlaveDimensiones($fDatos[1]));
					$regProducto["productoConExistencia"]=($existencia)?"1":"0";
					array_push($arrProductos,$regProducto);
					
				}
			break;
			case 3:
				$consulta="SELECT * FROM 6934_pedidosTienda WHERE folioPedido='".$clave."' and situacion=1";
				$fPedido=$con->obtenerPrimeraFila($consulta);
				if($fPedido)
				{
					$regProducto=array();
					if($fPedido[13]=="")
						$fPedido[13]=0;
					$datosPedido='{"abono":"'.$fPedido[13].'","idPedido":"'.$fPedido[0].'","fechaPedido":"'.$fPedido[1].'","tipoCliente":"'.$fPedido[9].'","idCliente":"'.$fPedido[2].'","nombreCliente":"'.cv(obtenerNombreCliente($fPedido[9],$fPedido[2])).'"}';
					
					
					
					$regProducto["metaData"]=$datosPedido;
					array_push($arrProductos,$regProducto);
					
					$consulta="SELECT * FROM 6935_productosPedidoTienda WHERE idPedido=".$fPedido[0];
					$res=$con->obtenerFilas($consulta);
					while($fDatos=mysql_fetch_row($res))
					{
						$regProducto=array();
						$regProducto["dimensiones"]=array();
						$archivoImagen="";
						$tipoCliente=$fPedido[9];
						$idCliente=$fPedido[2];
						$datosProducto=obtenerDatosProducto($fDatos[2],$fDatos[8],$idCaja,$tipoCliente,$idCliente);
						$regProducto["idRegistro"]=$fDatos[0];
						$regProducto["cveProducto"]=$clave;
						$regProducto["idProducto"]=$fDatos[2];
						$regProducto["cantidad"]=$fDatos[3];
						$regProducto["tipoConcepto"]=$fDatos[10];
						$regProducto["llaveProducto"]=$fDatos[8];
						$regProducto["tipoMovimiento"]="";
						
						$regProducto["concepto"]=$datosProducto["nombreProducto"];
						$regProducto["descripcion"]=$datosProducto["descripcion"];
						$regProducto["costoUnitario"]=$fDatos[4];
						$regProducto["porcentajeIVA"]=$fDatos[9];
						$regProducto["descuento"]=$fDatos[11];
						
						
						
						$regProducto["imagen"]="[]";
						if(sizeof($datosProducto["imagenes"])>0)
						{
							$aImagen="";
							foreach($datosProducto["imagenes"] as $i)	
							{
								$dI='{"imagen":"'.$i.'"}';
								if($aImagen=="")
									$aImagen=$dI;
								else
									$aImagen.=",".$dI;
							}
							$regProducto["imagen"]="".$aImagen."";
						}
						$regProducto["detalle"]="";
						
						array_push($arrProductos,$regProducto);
						
					}
				}
			break;
			case 4:
				$consulta="SELECT * FROM 6008_ventasCaja WHERE folioVenta='".$clave."' and situacion=1";
				$fPedido=$con->obtenerPrimeraFila($consulta);
				if($fPedido)
				{
					$regProducto=array();
					$consulta="SELECT idAdeudo FROM 6942_adeudos WHERE tipoAdeudo=1 AND idReferencia=".$fPedido[0];
					$idAdeudo=$con->obtenerValor($consulta);
					if($idAdeudo=="")
						$idAdeudo=-1;
					$consulta="SELECT SUM(montoAbono) FROM 6936_controlPagos WHERE idAdeudo=".$idAdeudo;
			
					$montoAbono=$con->obtenerValor($consulta);
					if($montoAbono=="")
						$montoAbono=0;
					$saldo=$fPedido[7]-$montoAbono;
					if($idAdeudo==-1)
						$saldo=0;
					$facturada=0;
					if(($fPedido[15]!="")&&($fPedido[15]!="-1"))
					{
						$consulta="SELECT COUNT(*) FROM 703_relacionFoliosCFDI WHERE idFolio=".$fPedido[15]." AND situacion=2";
						$facturada=$con->obtenerValor($consulta);
					}

					$datosPedido='{"facturada":"'.$facturada.'","idAdeudo":"'.$idAdeudo.'","saldo":"'.$saldo.'","abono":"'.$montoAbono.'","formaPago":"'.$fPedido[8].'","idVenta":"'.$fPedido[0].'","fechaVenta":"'.$fPedido[1].'","tipoCliente":"'.
								$fPedido[12].'","idCliente":"'.$fPedido[13].'","nombreCliente":"'.cv(obtenerNombreCliente($fPedido[12],$fPedido[13])).'"}';
					
					
					
					$regProducto["metaData"]=$datosPedido;
					array_push($arrProductos,$regProducto);
					
					$consulta="SELECT * FROM 6009_productosVentaCaja WHERE idVenta=".$fPedido[0]." and situacion=1";

					$res=$con->obtenerFilas($consulta);
					while($fDatos=mysql_fetch_row($res))
					{
						$regProducto=array();
						$regProducto["dimensiones"]=array();
						
						
						
						$archivoImagen="";
						$tipoCliente=$fPedido[9];
						$idCliente=$fPedido[2];
						$datosProducto=obtenerDatosProducto($fDatos[2],$fDatos[12],$idCaja,$tipoCliente,$idCliente);
						$regProducto["idRegistro"]=$fDatos[0];
						$regProducto["cveProducto"]=$clave;
						$regProducto["idProducto"]=$fDatos[2];
						$regProducto["cantidad"]=$fDatos[4];
						$regProducto["tipoConcepto"]=$fDatos[3];
						$regProducto["llaveProducto"]=$fDatos[12];
						$regProducto["tipoMovimiento"]="";
						
						$regProducto["concepto"]=$datosProducto["nombreProducto"];
						$regProducto["descripcion"]=$datosProducto["descripcion"];
						$regProducto["costoUnitario"]=$fDatos[5];
						$regProducto["porcentajeIVA"]=$fDatos[13];
						$regProducto["descuento"]=$fDatos[15];
						
						
						
						$regProducto["sL"]=1;
						
						$regProducto["imagen"]="[]";
						if(sizeof($datosProducto["imagenes"])>0)
						{
							$aImagen="";
							foreach($datosProducto["imagenes"] as $i)	
							{
								$dI='{"imagen":"'.$i.'"}';
								if($aImagen=="")
									$aImagen=$dI;
								else
									$aImagen.=",".$dI;
							}
							$regProducto["imagen"]="".$aImagen."";
						}
						$regProducto["detalle"]="";
						$consulta="SELECT sum(cantidad) FROM 6950_productosDevolucion WHERE idProductoVenta=".$fDatos[0];
						$regProducto["numDevueltos"]=$con->obtenerValor($consulta);
						if($regProducto["numDevueltos"]=="")
							$regProducto["numDevueltos"]=0;
						array_push($arrProductos,$regProducto);
						
					}
				}
			break;
			case 5:
				$arrProducto=explode("_",$clave);
				$consulta="SELECT distinct idProducto,llave FROM 6911_costosProductos WHERE idProducto=".$arrProducto[0]." and llave='".$arrProducto[1]."' limit 0,1";

				$res=$con->obtenerFilas($consulta);
				while($fDatos=mysql_fetch_row($res))
				{
					$regProducto=array();
					$regProducto["dimensiones"]=array();
					
					/*$regProducto["metaData"]="";
					$regProducto["idRegistro"]="";
					$regProducto["sL"]=0;
					$regProducto["numDevueltos"]=0;*/
					
					$archivoImagen="";
					$datosProducto=obtenerDatosProducto($fDatos[0],$fDatos[1],$idCaja,$tipoCliente,$idCliente);
					$regProducto["cveProducto"]="";
					$regProducto["idProducto"]=$fDatos[0];
					$regProducto["cantidad"]=1;
					$regProducto["tipoConcepto"]=1;
					$regProducto["llaveProducto"]=$fDatos[1];
					$regProducto["tipoMovimiento"]="";
					
					$regProducto["concepto"]=$datosProducto["nombreProducto"];
					$regProducto["descripcion"]=$datosProducto["descripcion"];
					$regProducto["costoUnitario"]=$datosProducto["costoUnitario"];
					$regProducto["costoUnitarioNeto"]=$datosProducto["costoUnitario"];
					$regProducto["costoUnitarioConDescuento"]=$datosProducto["costoUnitario"];
					
					
					if($datosProducto["tipoPrecio"]==1)
					{
						$regProducto["costoUnitarioNeto"]=str_replace(",","",number_format($regProducto["costoUnitarioNeto"]/(1+($datosProducto["porcentajeIVA"]/100)),2));
					}
					
					
					
					
					
					$regProducto["descuento"]=0;
					$descuento=buscarDescuentoProducto($tipoCliente,$idCliente,$regProducto["idProducto"],$fDatos[1]);

					if(($descuento["pDescuento"]!="")&&($descuento["pDescuento"]!=0))
					{
						$costoNeto=$regProducto["costoUnitarioNeto"];
						$regProducto["descuento"]=str_replace(",","",number_format($costoNeto*($descuento["pDescuento"]/100),2));
						$regProducto["costoUnitarioConDescuento"]=str_replace(",","",number_format($regProducto["costoUnitario"],2))-str_replace(",","",number_format($regProducto["costoUnitario"]*($descuento["pDescuento"]/100),2));
						
						
						
					}
					
					if(isset($descuento["mDescuento"])&&($descuento["mDescuento"]!="")&&($descuento["mDescuento"]!=0))
					{
						$regProducto["descuento"]=$descuento["mDescuento"];
						$regProducto["costoUnitarioConDescuento"]=str_replace(",","",number_format($regProducto["costoUnitario"],2))-str_replace(",","",number_format($regProducto["descuento"],2));
						
					}
					
					
					

					$regProducto["porcentajeIVA"]=$datosProducto["porcentajeIVA"];
					$regProducto["tipoPrecio"]=$datosProducto["tipoPrecio"];
					

					
					
					$regProducto["imagen"]="[]";
					if(sizeof($datosProducto["imagenes"])>0)
					{
						$aImagen="";
						foreach($datosProducto["imagenes"] as $i)	
						{
							$dI='{"imagen":"'.$i.'"}';
							if($aImagen=="")
								$aImagen=$dI;
							else
								$aImagen.=",".$dI;
						}
						$regProducto["imagen"]="".$aImagen."";
					}
					$regProducto["detalle"]="";
					$regProducto["unidadMedida"]=$datosProducto["unidadMedida"];
					$regProducto["arrUnidadesMedida"]=$datosProducto["arrUnidadesMedida"];
					$existencia=$cA->existeSuficienciaTiempoMovimientoV2($regProducto["idProducto"],1,$datosProducto["unidadMedida"],$tPresupuestal,convertirLlaveDimensiones($fDatos[1]));
					$regProducto["productoConExistencia"]=($existencia)?"1":"0";
					
					array_push($arrProductos,$regProducto);
					
				}
			break;
		}
		
		return $arrProductos;
	}	
	
	function registrarVentaArticulo($idVenta,$tipoOperacion,$idCaja)
	{
		global $con;
		
		$numDias=60;
		$query="SELECT formaPago,datosCompra,total,subtotal,iva,tipoCliente,idCliente,idCaja,idPedido FROM 6008_ventasCaja WHERE idVenta=".$idVenta;
		$fVenta=$con->obtenerPrimeraFila($query);	
		$x=0;
		$consulta[$x]="begin";
		$x++;
		$tipoMovimiento=-1;
		if($tipoOperacion==1)
		{
			if(($fVenta[8]==-1)||($fVenta[8]==""))
			{
				switch($fVenta[0])
				{
					case 1:
					case 2:
					case 3:
						$tipoMovimiento=27;
					break;	
					case 4:
						$tipoMovimiento=31;
					break;
				}
			}
			else
				$tipoMovimiento=32;
		}
		else
			$tipoMovimiento=30;
			
		
		$objAsiento='{
						"tipoMovimiento":"'.$tipoMovimiento.'",
						"cantidadOperacion":"",
						"idProducto":"",
						"tipoReferencia":"3",
						"datoReferencia1":"'.$idVenta.'",
						"datoReferencia2":"",
						"complementario":"",
						"dimensiones":null
					}';
					
		$arrMovimientos=array();
		$c=NULL;
		if($tipoMovimiento!=30)
		{
		
			$query="SELECT idProducto,cantidad,llave FROM 6009_productosVentaCaja WHERE idVenta=".$idVenta;
		}
		else
		{
			$query="SELECT idProducto,cantidad,llaveProducto FROM 6935_productosPedidoTienda WHERE idPedido=".$idVenta;	
		}
		$res=$con->obtenerFilas($query);
		while($fila=mysql_fetch_row($res))
		{
			$query="SELECT idAlmacen from 6901_catalogoProductos WHERE idProducto=".$fila[0];
			$idAlmacen=$con->obtenerValor($query);
			$c=new cAlmacen($idAlmacen);
			$oProducto=json_decode($objAsiento);
			$oProducto->cantidadOperacion=$fila[1];
			$oProducto->idProducto=$fila[0];
			$oProducto->dimensiones=convertirLlaveDimensiones($fila[2]);
			array_push($arrMovimientos,$oProducto);
			
		}
		
		$c->asentarArregloMovimientos($arrMovimientos,$consulta,$x,true);
		
		
		
		$consulta[$x]="commit";
		$x++;
		if($con->ejecutarBloque($consulta))
		{
			
			return true;
			
		}
		
	}
	
	function obtenerDatosPedidoTiendaCaja($referencia)
	{
		global $con;
		$regProducto=array();
		$consulta="SELECT m.idConcepto FROM 6011_movimientosPago m WHERE m.idReferencia='".$referencia."' and pagado=0 ";
		$fDatos=$con->obtenerPrimeraFila($consulta);
		if($fDatos)
		{
			$regProducto["cveProducto"]=$referencia;
			$regProducto["concepto"]="Pago de Pedido con Folio: ".$fDatos[0]." [Venta por tienda virtual]";
			$regProducto["cantidad"]=1;
			$regProducto["tipoConcepto"]=1;
			$regProducto["idProducto"]=$fDatos[0];
			$regProducto["tipoMovimiento"]="";
			$regProducto["costoUnitario"]=obtenerMontoPagoReferencia($referencia);
			$regProducto["iva"]=0;
			$regProducto["total"]=$regProducto["costoUnitario"];
			$regProducto["subtotal"]=$regProducto["costoUnitario"];
			$regProducto["dimensiones"]=array();
			$regProducto["imagen"]="";
			$regProducto["detalle"]="";
			
		}
		return $regProducto;
	}
	
	function buscarDescuentoProductoEmpleado($idCliente,$categoria,$idReferencia=-1)
	{
		global $con;
		$porcentaje=0;
		if($idReferencia==-1)
		{
			$consulta="SELECT id__1003_tablaDinamica FROM _1003_tablaDinamica WHERE personal=".$idCliente;
			$idReferencia=$con->obtenerValor($consulta);
		}
		if($idReferencia!="")
		{
			$consulta="SELECT porcentajeDesc FROM _1003_gridDescuentoPersonal WHERE idReferencia=".$idReferencia." AND categoria='".$categoria."'";
			$porcentaje=$con->obtenerValor($consulta);
			if($porcentaje=="")
			{
				$arrCategoria=explode(".",$categoria);
				if(sizeof($arrCategoria)>1)
				{
					$cadCategoria="";
					for($ct=0;$ct<sizeof($arrCategoria)-1;$ct++)	
					{
						if($cadCategoria=="")
							$cadCategoria=$arrCategoria[$ct];
						else
							$cadCategoria.=".".$arrCategoria[$ct];
					}
					return buscarDescuentoProductoEmpleado($idCliente,$cadCategoria,$idReferencia);
				}		
			}
		}	
		return $porcentaje;
	}
	
	function buscarDescuentoProducto($tipoCliente,$idCliente,$idProducto,$llave)
	{
		global $con;
		$resultado["pDescuento"]=0;
		$resultado["motivoDescuento"]="";
		$fechaActual=date("Y-m-d");
		$arrConsultas=array();
		$arrConsultas[0]="buscarDescuentoProductoRol";
		$arrConsultas[1]="buscarDescuentoProductoIndividual";
		$arrConsultas[2]="buscarDescuentoProductoGeneral";
		$arrConsultas[3]="buscarDescuentoPorCategoria";
		$arrConsultas[4]="buscarDescuentoPorAlmacen";		
		
		$consulta="SELECT criterioDefinicionDescuento FROM _1025_tablaDinamica";
		$criterioDefinicionDescuento=$con->obtenerValor($consulta);//1 buscar mayor descuento; 2 Detener ante primer descuento detectado
		
		$consulta="SELECT tipoDescuento FROM _1025_resolucionDescuentos ORDER BY id__1025_resolucionDescuentos";
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$resDescuento=NULL;
			eval('$resDescuento='.$arrConsultas[$fila[0]].'($idProducto,$llave,$tipoCliente,$idCliente);');
			if($resDescuento["pDescuento"]>0)			
			{
				if($criterioDefinicionDescuento==1)
				{
					if($resultado["pDescuento"]<$resDescuento["pDescuento"])	
					{
						$resultado=$resDescuento;	
					}
				}
				else
					return $resDescuento;
			}
			
			
		}
		
		
		
		return $resultado;
	}	
	
	function buscarDescuentoProductoRol($idProducto,$llave,$tipoCliente,$idCliente)
	{
		global $con;
		$resultado["pDescuento"]=0;
		$resultado["motivoDescuento"]="Descuento por tipo de usuario (Rol de usuario)";
		
		
		$pDescuento=0;
		if($tipoCliente==3)
		{
			$consulta="SELECT DISTINCT idRol FROM 807_usuariosVSRoles where idUsuario=".$idCliente;
			$listDescuento=$con->obtenerListaValores($consulta);
			if($listDescuento=="")
				$listDescuento=-1;
			$consulta="SELECT porcentaje FROM _1003_tablaDinamica WHERE rolDescuento IN (".$listDescuento.") ORDER BY porcentaje DESC";	
			$pDescuento=$con->obtenerValor($consulta);
			if($pDescuento=="")
				$pDescuento=0;
			
		}
		
		$resultado["pDescuento"]=$pDescuento;
		
		return $resultado;
	}
	
	
	function buscarDescuentoProductoIndividual($idProducto,$llave,$tipoCliente,$idCliente)
	{
		global $con;
		$resultado["pDescuento"]=0;
		$resultado["motivoDescuento"]="";
		$fechaActual=date("Y-m-d");
		$consulta="SELECT porcentajeDescuento,descripcionDescuento FROM 6954_descuentosProducto WHERE idProducto=".$idProducto." AND llave='".$llave."' AND fechaInicio<='".$fechaActual."' AND fechaTermino>='".$fechaActual."' and tipoDescuento=0 and situacion=1";
		
		$fDescuento=$con->obtenerPrimeraFila($consulta);
		if($fDescuento)
		{
			$porcentajeDescuento=$fDescuento[0];
			if($porcentajeDescuento=="")
				$porcentajeDescuento=0;
			$resultado["pDescuento"]=$porcentajeDescuento;
			$resultado["motivoDescuento"]=$fDescuento[1];
		}
		return $resultado;
	}
	
	function buscarDescuentoProductoGeneral($idProducto,$llave,$tipoCliente,$idCliente)
	{
		global $con;
		$resultado["pDescuento"]=0;
		$resultado["motivoDescuento"]="";
		$fechaActual=date("Y-m-d");
		$consulta="SELECT porcentajeDescuento,descripcionDescuento FROM 6954_descuentosProducto WHERE idProducto=".$idProducto." AND llave='' AND fechaInicio<='".$fechaActual."' AND fechaTermino>='".$fechaActual."' and tipoDescuento=0 and situacion=1";
		$fDescuento=$con->obtenerPrimeraFila($consulta);
		if($fDescuento)
		{
			$porcentajeDescuento=$fDescuento[0];
			if($porcentajeDescuento=="")
				$porcentajeDescuento=0;
			$resultado["pDescuento"]=$porcentajeDescuento;
			$resultado["motivoDescuento"]=$fDescuento[1];
		}
		return $resultado;
		
	}
	
	
	function buscarDescuentoPorCategoria($idProducto,$llave,$tipoCliente,$idCliente)
	{
		global $con;
		$fechaActual=date("Y-m-d");
		$resultado["pDescuento"]=0;
		$resultado["motivoDescuento"]="";
		$consulta="SELECT categoria FROM 6901_catalogoProductos WHERE idProducto=".$idProducto;
		$categoria=$con->obtenerValor($consulta);
		$arrCategorias=explode(".",$categoria);
		$numElem=sizeof($arrCategorias)-1;
		
		for($x=$numElem;$x>=0;$x--)
		{
			$consulta="SELECT porcentajeDescuento,descripcionDescuento FROM 6954_descuentosProducto WHERE idProducto=".$arrCategorias[$x]." AND fechaInicio<='".$fechaActual."' AND fechaTermino>='".$fechaActual."' and tipoDescuento=1 and situacion=1";
			$fDescuento=$con->obtenerPrimeraFila($consulta);
			if($fDescuento)
			{
				$porcentajeDescuento=$fDescuento[0];
				if($porcentajeDescuento=="")
					$porcentajeDescuento=0;
				$resultado["pDescuento"]=$porcentajeDescuento;
				$resultado["motivoDescuento"]=$fDescuento[1];
				
				if($porcentajeDescuento!="0")
					return $resultado;
			}
		}
		
		
		return $resultado;
	}
	
	
	function buscarDescuentoPorAlmacen($idProducto,$llave,$tipoCliente,$idCliente)
	{
		global $con;
		$fechaActual=date("Y-m-d");
		$resultado["pDescuento"]=0;
		$resultado["motivoDescuento"]="";
		$consulta="SELECT idAlmacen FROM 6901_catalogoProductos WHERE idProducto=".$idProducto;
		$idAlmacen=$con->obtenerValor($consulta);
		
		$consulta="SELECT porcentajeDescuento,descripcionDescuento FROM 6954_descuentosProducto WHERE idProducto=".$idAlmacen." AND fechaInicio<='".$fechaActual."' AND fechaTermino>='".$fechaActual."' and tipoDescuento=2 and situacion=1";
		$fDescuento=$con->obtenerPrimeraFila($consulta);
		if($fDescuento)
		{
			$porcentajeDescuento=$fDescuento[0];
			if($porcentajeDescuento=="")
				$porcentajeDescuento=0;
			$resultado["pDescuento"]=$porcentajeDescuento;
			$resultado["motivoDescuento"]=$fDescuento[1];
		}
		return $resultado;
	}
	
	
	
	
	function buscarDatosAlumno($nombre)
	{
		global $con;
		$consulta="SELECT * FROM (SELECT id as idUsuario,CONCAT(IF(paterno IS NULL,'',paterno),' ',IF(materno IS NULL,'',materno),' ',IF(nombre IS NULL,'',nombre)) AS cliente 
					FROM alumno) AS tmp WHERE cliente LIKE '%".$nombre."%' ORDER BY cliente";	
		$registros=utf8_encode($con->obtenerFilasJSON($consulta));
		$res["numReg"]=$con->filasAfectadas;
		$res["registros"]=$registros;
		return $res;
	}
	
	function buscarDatosEmpleado($nombre)
	{
		global $con;
		$consulta="SELECT idUsuario FROM 807_usuariosVSRoles WHERE idRol=47";
		$listUsuario=$con->obtenerListaValores($consulta);
		if($listUsuario=="")
			$listUsuario=-1;
		$consulta="select * from(SELECT idUsuario,CONCAT(IF(Paterno IS NULL,'',Paterno),' ',IF(Materno IS NULL,'',Materno),' ',IF(Nom IS NULL,'',Nom)) AS cliente 
				FROM 802_identifica where idUsuario in (".$listUsuario.")) as tmp where cliente LIKE '%".$nombre."%' ORDER BY cliente";
		$registros=utf8_encode($con->obtenerFilasJSON($consulta));
		$res["numReg"]=$con->filasAfectadas;
		$res["registros"]=$registros;
		return $res;
	}
	
	function buscarDatosCliente($nombre)
	{
		global $con;
		$consulta="SELECT idEmpresa FROM 6927_categoriaEmpresa WHERE idCategoria=1";
		$listEmpresas=$con->obtenerListaValores($consulta);
		if($listEmpresas=="")
			$listEmpresas=-1;
		$consulta="select * from(SELECT idEmpresa AS idUsuario,CONCAT((IF(rfc1 IS NOT NULL,CONCAT('[',rfc1,'-',rfc2,'-',rfc3,'] '),'[] ')),CONCAT(razonSocial,IF(apPaterno IS NULL,'',apPaterno),IF(apMaterno IS NULL,'',apMaterno))) AS cliente  FROM 6927_empresas
 					WHERE idEmpresa IN(".$listEmpresas.")) as tmp where cliente LIKE '%".$nombre."%' ORDER BY cliente";
		$registros=utf8_encode($con->obtenerFilasJSON($consulta));
		$res["numReg"]=$con->filasAfectadas;
		$res["registros"]=$registros;
		return $res;
	}
	
	
	function registrarVentaArticuloV2($idVenta,$tipoOperacion,$idCaja)
	{
		global $con;
		
		$numDias=60;
		$query="SELECT formaPago,datosCompra,total,subtotal,iva,tipoCliente,idCliente,idCaja,idPedido,idAlmacen FROM 6008_ventasCaja WHERE idVenta=".$idVenta;
		$fVenta=$con->obtenerPrimeraFila($query);	
		$x=0;
		$consulta[$x]="begin";
		$x++;
		$tipoMovimiento=11;
		
		$c=new cAlmacen($fVenta[9]);
			
		
		$arrMovimientos=array();
		
		$cM=	'{
					"tipoMovimiento":"",
					"cantidadOperacion":"",
					"unidadMedida":"",
					"idProducto":"",
					"llaveProducto":"",
					"tipoReferencia":"", 	
					"datoReferencia1":"",	
					"datoReferencia2":"", 	
					"arrMovimientos":[] , 	
					"complementario":"", 	
					"codigoUnidad":""
					
				}';	

					
		$query="SELECT * FROM 6009_productosVentaCaja WHERE idVenta=".$idVenta;
		$res=$con->obtenerFilas($query);
		while($fila=mysql_fetch_row($res))
		{
			$oM=json_decode($cM);
			$oM->tipoMovimiento=$tipoMovimiento;
			$oM->cantidadOperacion=$fila[4];
			$oM->unidadMedida=$fila[20];
			$oM->idProducto=$fila[2];
			$oM->llaveProducto=$fila[12];
			$oM->tipoReferencia=8;
			$oM->datoReferencia1=$idVenta;
			
			array_push($arrMovimientos,$oM);
			
		}
		
		$c->asentarArregloMovimientos($arrMovimientos,$consulta,$x,true);
		
		
		
		$consulta[$x]="commit";
		$x++;
		return $con->ejecutarBloque($consulta);
		
		
	}
?>