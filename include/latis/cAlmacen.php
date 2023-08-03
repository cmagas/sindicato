<?php
	class cAlmacen
	{
		var $idAlmacen;
		var $arrDimensiones;
		var $arrDescripcionConceptos;

		function cAlmacen($iA)
		{
			global $con;
			$this->idAlmacen=$iA;
			$consulta="SELECT nombreEstructura,idDimension,idFuncionInterpretacion FROM 563_dimensiones";
			$this->arrDimensiones=$con->obtenerFilasArregloAsocPHP($consulta,true);	
			$this->arrDescripcionConceptos=array();
			$query="SELECT idPerfilMovimiento,descripcion FROM 6904_permilesMovimientoAlmacen";
			$res=$con->obtenerFilas($query);
			while($f=mysql_fetch_row($res))
			{
				$this->arrDescripcionConceptos[$f[0]]=$f[1];
			}
		}		
		
		/*
		{
			"tipoMovimiento":"",
			"cantidadOperacion":"",
			"unidadMedida":"",
			"idProducto":"",
			"llaveProducto":"",
			"tipoReferencia":"", 	//opcional
			"datoReferencia1":"",	//opcional
			"datoReferencia2":"", 	//opcional
			"arrMovimientos":[]  	//opcional
			"complementario":"" 	//opcional
			"codigoUnidad":""
			
		}
		*/
		function asentarMovimiento($obj,&$consulta=null,&$ct=null)
		{
			global $con;
			
			$arrMovimientos=array();
			
			if(!isset($obj->arrMovimientos)||(sizeof($obj->arrMovimientos)==0))
			{
			
				$query="SELECT tiempoMovimiento,(SELECT descripcionRegistroMovimiento FROM 6904_permilesMovimientoAlmacen WHERE idPerfilMovimiento=a.idPerfilMovimiento) as etiqueta,
						100 as porcentajeAfectacion,tipoAfectacion,idFuncionAplicacion,idAfectacion 
						from 6905_afectacionMovimientoAlmacen a WHERE idPerfilMovimiento=".$obj->tipoMovimiento;
				$resAfectacion=$con->obtenerFilas($query);
				while($filaAfectacion=mysql_fetch_row($resAfectacion))
				{
					$considerarMovimiento=true;
					if(($filaAfectacion[4]!="")&&($filaAfectacion[4]!="-1"))
					{
						$cadObj='{"vAmbiente":"","idAfectacion":"'.$filaAfectacion[5].'"}';
						$objParam=json_decode($cadObj);
						$objParam->vAmbiente=$obj;
						$cacheConsulta=NULL;
						$resAplicacion=resolverExpresionCalculoPHP($fFolio[1],$objParam,$cacheConsulta);
						if($resAplicacion!='1')
							$considerarMovimiento=false;
					}
					
					if($considerarMovimiento)
					{
						$objDatos=array();
						$objDatos[0]=$filaAfectacion[0];  								//Tiempo movimiento
						$objDatos[1]=$filaAfectacion[2];								//POrcentaje afectacion
						$objDatos[2]=1;													//
						$objDatos[3]=$filaAfectacion[3];								//Tipo afectación
						$objDatos[4]=$obj->cantidadOperacion*($filaAfectacion[2]/100);	//Cantidad afectacion
						$objDatos[5]=$filaAfectacion[1];								//etiqueta movimiento
						array_push($arrMovimientos,$objDatos);
					}
				}
			}
			else
				$arrMovimientos=$obj->arrMovimientos;
				
				
			$x=0;
			
			if($consulta==null)
			{
				
				$consulta=array();
				$consulta[$x]="begin";
				$x++;
			}
			else
			{
				
				$x=$ct;
			}
			if(sizeof($arrMovimientos)>0)
			{
				foreach($arrMovimientos as $filaAfectacion)
				{
					$cantidadOperacion=$filaAfectacion[4];
					$tipoReferencia=0;
					if(isset($obj->tipoReferencia))
						$tipoReferencia=$obj->tipoReferencia;
					$datoReferencia1="";
					if(isset($obj->datoReferencia1))
						$datoReferencia1=$obj->datoReferencia1;
					$datoReferencia2="";
					if(isset($obj->datoReferencia2))
						$datoReferencia2=$obj->datoReferencia2;
					$complementario="";
					if(isset($obj->complementario))
						$complementario=$obj->complementario;
					$codigoUnidad=$_SESSION["codigoUnidad"];
					if(isset($obj->codigoUnidad))
						$codigoUnidad=$obj->codigoUnidad;
					
					$consulta[$x]="INSERT INTO 6920_movimientosAlmacen(idAlmacen,idProducto,tipoReferencia,datoReferencia1,datoReferencia2,complementario,
								codigoUnidad,fechaMovimiento,tiempoMovimiento,cantidad,tipoOperacion,idResponsableMovimiento,descripcionMovimiento,idPerfilMovimiento,
								llaveProducto,unidadMedida) 
								VALUES(".$this->idAlmacen.",".$obj->idProducto.",".$tipoReferencia.",'".cv($datoReferencia1)."','".cv($datoReferencia2)."','".cv($complementario).
								"','".$codigoUnidad."','".date("Y-m-d H:i:s")."',".$filaAfectacion[0].",".$cantidadOperacion.",".$filaAfectacion[3].",".
								$_SESSION["idUsr"].",'".cv($filaAfectacion[5])."',".$obj->tipoMovimiento.",'".cv($obj->llaveProducto)."',".$obj->unidadMedida.")";
					

					$x++;
					
					
					$arrDimensionesLlave=convertirLlaveDimensiones($obj->llaveProducto);
					if(sizeof($arrDimensionesLlave)>0)
					{
						
						$consulta[$x]="set @idAsiento=(select last_insert_id())";
						$x++;
						
						foreach ($arrDimensionesLlave as $nombre=>$valor) 
						{
							if(isset($this->arrDimensiones[$nombre]))
							{
								
								$idDimension=$this->arrDimensiones[$nombre][0];
								$funcionInterpretacion=$this->arrDimensiones[$nombre][1];
								$valorInterpretacion="";
								if($funcionInterpretacion!="")
								{
									$cadObj='{"valor":""}';
									$objParam=json_decode($cadObj);
									$objParam->valor=$valor;
									$cacheConsulta=NULL;
									$valorInterpretacion=resolverExpresionCalculoPHP($funcionInterpretacion,$objParam,$cacheConsulta);	
								}
								
								$consulta[$x]="INSERT INTO 6921_detallesMovimientosAlmacen (idMovimiento,idDimension,valorCampo,valorInterpretacion) VALUES(@idAsiento,".$idDimension.",'".$valor."','".$valorInterpretacion."')";
								$x++;
								
							}
							
						}
					}
				}
			}

			if($ct!=null)
			{

				$ct=$x;
			}
			else
			{
				$consulta[$x]="commit";
				$x++;

				return $con->ejecutarBloque($consulta);
			}
			return true;

			
		}
		
		function asentarArregloMovimientos($arrObj,&$consulta=null,&$ct=null)
		{
			global $con;

			$arrMovimientos=array();
			$x=0;
			
			if($consulta==null)
			{
				
				$consulta=array();
				$consulta[$x]="begin";
				$x++;
			}
			else
			{
				
				$x=$ct;
			}

			foreach($arrObj as $obj)
			{	
				$this->asentarMovimiento($obj,$consulta,$x);
			}
			
			
			if($ct!=null)
			{

				$ct=$x;
			}
			else
			{
				$consulta[$x]="commit";
				$x++;

				return $con->ejecutarBloque($consulta);
			}
			return true;
			
		}
						
		function obtenerCantidadTiempoMovimiento($idProducto,$tMovimiento,$dimensiones,$codigoUnidad="")
		{
			global $con;
			$saldo=0;
			$listAsientos="";
			$arrIdAsientos=array();
			$tDimensiones=sizeof($dimensiones);
			$listAsientos=-1;
			$comp="";
			$comp2="";

			if($codigoUnidad!="")
				$comp2=" and a.codigoUnidad='".$codigoUnidad."'";
			if(sizeof($dimensiones)>0)
			{
				foreach($dimensiones as $d=>$valor)
				{
					$idDimension=-1;
					if(isset($this->arrDimensiones[$d]))
						$idDimension=$this->arrDimensiones[$d][0];
					if($idDimension!=-1)
					{
						if($listAsientos!=-1)
							$comp=" and idMovimientoAlmacen in (".$listAsientos.")";
						$consulta="select idMovimientoAlmacen FROM 6921_detallesMovimientosAlmacen d,6920_movimientosAlmacen a  WHERE a.idAlmacen=".$this->idAlmacen." and a.idProducto=".$idProducto." ".$comp2." ".$comp." 
										and idDimension=".$idDimension." AND valorCampo='".$valor."'";
	
						$listAsientos=$con->obtenerListaValores($consulta);
						if($listAsientos=="")
							break;
					}
				}
			}
			else
			{
				$consulta="SELECT idMovimientoAlmacen FROM 6920_movimientosAlmacen WHERE idProducto=".$idProducto.$comp2;
				$listAsientos=$con->obtenerListaValores($consulta);
			}
			
			if($listAsientos=="")
				$listAsientos=-1;
			$consulta="SELECT SUM(cantidad * tipoOperacion) FROM 6920_movimientosAlmacen WHERE idMovimientoAlmacen IN (".$listAsientos.") AND tiempoMovimiento=".$tMovimiento;

			$saldo=$con->obtenerValor($consulta);
			if($saldo=="")
				$saldo=0;
			return $saldo;
		}
				
		function obtenerTotalTiempoMovimientoDimensiones($idProducto,$dimensiones)
		{
			global $con;
			$consulta="SELECT idTiempoMovimiento FROM 6902_tiempoMovimientosAlmacen WHERE idAlmacen=".$this->idAlmacen;
			$res=$con->obtenerFilas($consulta);
			$totalPresupuesto=0;
			while($f=mysql_fetch_row($res))
			{
				$totalPresupuesto+=$this->obtenerSaldoPresupuesto($idProducto,$f[0],$dimensiones);
			}
			return $totalPresupuesto;
		}
		
		function obtenerSituacionTiempoMovimientoDimensiones($idProducto,$dimensiones,$codigoUnidad="")
		{
			global $con;
			$arrTiemposPresupuestales=array();
			$consulta="SELECT idTiempoMovimiento,nombreTiempo FROM 6902_tiempoMovimientosAlmacen WHERE idAlmacen=".$this->idAlmacen;
			$res=$con->obtenerFilas($consulta);
			$totalPresupuesto=0;
			while($f=mysql_fetch_row($res))
			{
				$arrTiemposPresupuestales[$f[1]]["tiempoMovimiento"]=$f[1];
				$arrTiemposPresupuestales[$f[1]]["cantidad"]=$this->obtenerCantidadTiempoMovimiento($idProducto,$f[0],$dimensiones,$codigoUnidad);
			}
			return $arrTiemposPresupuestales;
		}
		
		function existeSuficienciaTiempoMovimiento($idProducto,$cantidad,$tPresupuestal,$dimensiones)
		{
			$totalExistencia=$this->obtenerSaldoPresupuesto($idProducto,$tPresupuestal,$dimensiones);
			if($totalExistencia>$cantidad)
				return 0;
			else
				return $cantidad-$totalExistencia;
		}
				
		function obtenerSituacionTiempoMovimientoDimensionesV2($idProducto,$dimensiones,$codigoUnidad="")
		{
			global $con;
			$arrTiemposPresupuestales=array();
			$consulta="SELECT idTiempoMovimiento,nombreTiempo FROM 6902_tiempoMovimientosAlmacen WHERE idAlmacen=".$this->idAlmacen;
			$res=$con->obtenerFilas($consulta);
			$totalPresupuesto=0;
			while($f=mysql_fetch_row($res))
			{
				$arrTiemposPresupuestales[$f[1]]["IDTiempoMovimiento"]=$f[0];
				$arrTiemposPresupuestales[$f[1]]["tiempoMovimiento"]=$f[1];
				$arrTiemposPresupuestales[$f[1]]["cantidad"]=$this->obtenerCantidadTiempoMovimientoV2($idProducto,$f[0],$dimensiones,$codigoUnidad);
			}
			return $arrTiemposPresupuestales;
		}
		
		function obtenerCantidadTiempoMovimientoV2($idProducto,$tMovimiento,$dimensiones,$codigoUnidad="")
		{
			global $con;			
			$arrCantidad=array();
			
			$consulta="SELECT idUnidadMedida FROM 6901_catalogoProductos WHERE idProducto=".$idProducto;
			$unidadMedidaProducto=$con->obtenerValor($consulta);
			
			$unidadMedidaBase=0;
			
			$saldo=0;
			$listAsientos="";
			$arrIdAsientos=array();
			$tDimensiones=sizeof($dimensiones);
			$listAsientos=-1;
			$comp="";
			$comp2="";

			if($codigoUnidad!="")
				$comp2=" and a.codigoUnidad='".$codigoUnidad."'";
			if(sizeof($dimensiones)>0)
			{
				foreach($dimensiones as $d=>$valor)
				{
					$idDimension=-1;
					if(isset($this->arrDimensiones[$d]))
						$idDimension=$this->arrDimensiones[$d][0];
					if($idDimension!=-1)
					{
						if($listAsientos!=-1)
							$comp=" and idMovimientoAlmacen in (".$listAsientos.")";
						$consulta="select idMovimientoAlmacen FROM 6921_detallesMovimientosAlmacen d,6920_movimientosAlmacen a  WHERE a.idAlmacen=".$this->idAlmacen." and a.idProducto=".$idProducto." ".$comp2." ".$comp." 
										and idDimension=".$idDimension." AND valorCampo='".$valor."'";
	
						$listAsientos=$con->obtenerListaValores($consulta);
						if($listAsientos=="")
							break;
					}
				}
			}
			else
			{
				$consulta="SELECT idMovimientoAlmacen FROM 6920_movimientosAlmacen WHERE idProducto=".$idProducto.$comp2;
				$listAsientos=$con->obtenerListaValores($consulta);
			}
			
			if($listAsientos=="")
				$listAsientos=-1;			
				
			$arrExistenciaUnidadMedida=array();
			$query="SELECT (cantidad*tipoOperacion) as cantidad,unidadMedida FROM 6920_movimientosAlmacen WHERE idMovimientoAlmacen IN (".$listAsientos.") AND tiempoMovimiento=".$tMovimiento;	
			
			$rExistencia=$con->obtenerFilas($query);
			while($fExistencia=mysql_fetch_row($rExistencia))
			{
				if(!isset($arrExistenciaUnidadMedida[$fExistencia[1]]))
					$arrExistenciaUnidadMedida[$fExistencia[1]]=0;
				$arrExistenciaUnidadMedida[$fExistencia[1]]+=$fExistencia[0];						
			}				
			
			$aEquivalencia=array();
			
			foreach($arrExistenciaUnidadMedida as $iUnidad=>$resto)
			{
				if(abs($resto)>0)
				{
					
					$aEquivalencia[$iUnidad]=convertirUnidadesMedida(1,$unidadMedidaProducto,$iUnidad);
				}
			}
			
			$cBase=0;
			foreach($aEquivalencia as $iUnidad=>$equivalencia)
			{
				if($cBase<$equivalencia)
					$unidadMedidaBase=$iUnidad;
			}				
			
			$resultadoFinal=0;
			
			foreach($arrExistenciaUnidadMedida as $iUnidad=>$cantidad)
			{
				$resultadoFinal+=convertirUnidadesMedida($cantidad,$iUnidad,$unidadMedidaBase);
			}			
			
			if(abs($resultadoFinal)>0)
			{
				
				$arrCantidad=convertirCantidadesToDesgloce($resultadoFinal,$unidadMedidaProducto,$unidadMedidaBase);
			}
			return $arrCantidad;
		}
		
		function existeSuficienciaTiempoMovimientoV2($idProducto,$cantidad,$unidadMedida,$tPresupuestal,$dimensiones,$codigoUnidad="")
		{
			$totalCantidad=0;
			$totalExistencia=$this->obtenerCantidadTiempoMovimientoV2($idProducto,$tPresupuestal,$dimensiones,$codigoUnidad);
			
			foreach($totalExistencia as $t)
			{
				$tEquivalencia=convertirUnidadesMedida(1,$t["idUnidadMedida"],$unidadMedida);
				$totalCantidad+=$tEquivalencia*$t["cantidadEquivalencia"];
				if($t["idUnidadMedida"]==$unidadMedida)
				{
					
					break;
				}
				
			}
			if($totalCantidad>=$cantidad)
				return true;
			return false;
			
			
		}
		
		
		
		
	}
		
	function obtenerDatosProducto($idProducto,$llave,$idCaja,$tipoCliente,$idCliente)
	{
		global $con;
		$detalle="";
		$fechaActual=date("Y-m-d");
		$consulta="SELECT idZonaIVA FROM 6007_instanciasCaja WHERE idCaja=".$idCaja;
		$idZonaIVA=$con->obtenerValor($consulta);
		if($idZonaIVA=="")
		{
			$consulta="SELECT idZona FROM 6937_zonas";
			$idZonaIVA=$con->obtenerValor($consulta);
			if($idZonaIVA=="")
				$idZonaIVA=-1;
		}
		
		$consulta="SELECT idAlmacen,categoria,nombreProducto,descripcion,if(categoriaIVA is null,-1,categoriaIVA),idUnidadMedida FROM 
				6901_catalogoProductos WHERE idProducto=".$idProducto;
		$fProducto=$con->obtenerPrimeraFila($consulta);
		
		$arrDatos=array();
		
		$dimensionesProducto=array();
		$arrDimen=explode(".",$llave);
		
		if(sizeof($arrDimen)>0)
		{
			foreach($arrDimen as $d)	
			{
				$aDimension=explode(":",$d);
				$dimensionesProducto[$aDimension[0]]=$aDimension[1];
			}
			
			
			$consulta="SELECT idDimension FROM 6909_atributosProductos WHERE idProducto=".$idProducto." AND idPadre IS NULL";

			$idDimension=$con->obtenerValor($consulta);
			if($idDimension=="")
				$idDimension=-1;
			$arrCategoria=explode(".",$fProducto[1]);
			$tamCategoria=sizeof($arrCategoria)-1;
			$idCategoria="";
			$arrDimensiones=array();
			for($ct=$tamCategoria;$ct>=0;$ct--)
			{
				$idCategoria=$arrCategoria[$ct];
				$consulta="SELECT idDimension FROM 6908_dimensionesCategoriasProducto WHERE idCategoria=".$idCategoria." and idDimension=".$idDimension." ORDER BY orden";

				$res=$con->obtenerFilas($consulta);
				if($con->filasAfectadas>0)
				{
					break;	
				}
			}
			
			
			$generarDetalle=true;
			if(sizeof($dimensionesProducto)==1)
			{
				
				foreach($dimensionesProducto as $idDimension=>$valor)
				{

					if($idDimension==17)
						$generarDetalle=false;
					break;	
				}
			}
			
			if($generarDetalle)
			{
				foreach($dimensionesProducto as $idDimension=>$valor)
				{
					
					$consulta="SELECT etiqueta,orden,galeriaImagen FROM 6908_dimensionesCategoriasProducto WHERE idCategoria=".$idCategoria." AND idDimension=".$idDimension;
					$fCategoria=$con->obtenerPrimeraFila($consulta);
					$etiqueta=$fCategoria[0];
					$etValor="";
					$consulta="SELECT idFuncionOrigenDatos FROM 563_dimensiones WHERE idDimension=".$idDimension;
					$fDimension=$con->obtenerPrimeraFila($consulta);
					if($fDimension)
					{
						if(($fDimension[0]!="")&&($fDimension[0]!="-1"))
						{
							$oNull=NULL;
							$cadObj='{"param1":"-1"}';
							$objParam1=json_decode($cadObj);
							$resRegistros=resolverExpresionCalculoPHP($fDimension[0],$objParam1,$oNull);	
							if(sizeof($resRegistros)>0)
							{
								foreach($resRegistros as $r)
								{
									if($r["id"]==$valor)
									{
										$etValor=$r["etiqueta"];
										break;
									}
	
								}
							}
						}
					}	
					
					$descComp="<b>".$etiqueta." :</b> ".$etValor;
					
					if($detalle=="")
						$detalle=$descComp;
					else
						$detalle.=", ".$descComp;
					
				}
				
			}
		
		}
		$nombreProducto=$fProducto[2];
		$descripcion=$fProducto[3];
		if($descripcion=="")
			$descripcion=$nombreProducto;
		
		
		$arrDatos["nombreProducto"]=$nombreProducto.". (".$detalle.")";
		
		$arrDatos["descripcion"]=$descripcion.". ".$detalle."";
		if($detalle=="")
		{
			$arrDatos["nombreProducto"]=$nombreProducto;
			$arrDatos["descripcion"]=$descripcion.".";
			
		}
		
		$arrDatos["unidadMedida"]=$fProducto[5];
		$consulta="SELECT precio,tipoPrecio FROM 6911_costosProductos WHERE idProducto=".$idProducto." AND llave='".$llave."' and idZona=".$idZonaIVA." AND fechaInicio<='".$fechaActual."' ORDER BY fechaInicio DESC";
		$fDatosPrecio=$con->obtenerPrimeraFila($consulta);
		$precio=$fDatosPrecio[0];
		
		$arrDatos["costoUnitario"]=$precio;
		$arrDatos["tipoPrecio"]=$fDatosPrecio[1];
		$consulta="SELECT porcentajeIVA FROM 6940_porcentajeIVACategoria WHERE idCategoria=".$fProducto[4]." AND idZona=".$idZonaIVA." AND fechaInicio<='".$fechaActual."' ORDER BY fechaInicio DESC";
		$porcentajeIVA=$con->obtenerValor($consulta);
		if($porcentajeIVA=="")
			$porcentajeIVA=0;
		$arrDatos["porcentajeIVA"]=$porcentajeIVA;
		
		$arrDatos["categoria"]=$fProducto[1];
		
		$arrDatos["imagenes"]=array();
		
		$consulta="SELECT idImagen FROM 6912_imagenesProductos WHERE idProducto=".$idProducto." AND llave='".$llave."'";
		$res=$con->obtenerFilas($consulta);
		if($con->filasAfectadas==0)
		{
			$consulta="SELECT idImagen FROM 6912_imagenesProductos WHERE idProducto=".$idProducto." AND llave=''";
			$res=$con->obtenerFilas($consulta);
		}
		while($fila=mysql_fetch_row($res))
		{
			array_push($arrDatos["imagenes"],"../paginasFunciones/obtenerArchivos.php?id=".bE($fila[0]));
		}
		
		$arrUnidadesMedida=array();
		
		$consulta="	SELECT u.idUnidadMedida,IF(abreviatura='' OR abreviatura IS NULL,u.unidadMedida,CONCAT('(',u.abreviatura,') ',u.unidadMedida)) AS unidadMedida 
					FROM 6923_unidadesMedida u WHERE u.idUnidadMedida=".$arrDatos["unidadMedida"];
				
		
		$fUnidad=$con->obtenerPrimeraFila($consulta);
		$arrUnidadesMedida[$fUnidad[1]]="['".$fUnidad[0]."','".cv($fUnidad[1])."']";
		
		obtenerUnidadesMedidaEquivalencia($arrDatos["unidadMedida"],$arrUnidadesMedida);
		
		$aUnidadesMedida="";
		ksort($arrUnidadesMedida);
		foreach($arrUnidadesMedida as $et=>$resto)
		{
			if($aUnidadesMedida=="")
				$aUnidadesMedida=$resto;
			else
				$aUnidadesMedida.=",".$resto;
		}
		
		$arrDatos["arrUnidadesMedida"]="[".$aUnidadesMedida."]";


		return $arrDatos;
		
		
	}
	
	function obtenerNombreCliente($tipoCliente,$idCliente)
	{
		global $con;
		
		$consulta="";
		if($tipoCliente==2)
		
			$consulta="SELECT CONCAT(IF(paterno IS NULL,'',paterno),' ',IF(materno IS NULL,'',materno),' ',IF(nombre IS NULL,'',nombre)) AS cliente FROM alumno where id=".$idCliente;
		else
			$consulta="SELECT CONCAT(IF(Paterno IS NULL,'',Paterno),' ',IF(Materno IS NULL,'',Materno),' ',IF(Nom IS NULL,'',Nom)) AS cliente FROM 802_identifica where idUsuario=".$idCliente;
	
		return $con->obtenerValor($consulta);
	}
	
	function renombrarProductosAsociados($idProducto)
	{
		global $con;
		$x=0;
		$query[$x]="begin";
		$x++;
		$consulta="SELECT DISTINCT llave FROM 6932_descripcionProducto where idProducto=".$idProducto;

		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$detalleProducto=obtenerDatosProducto($idProducto,$fila[0],-1,0,0);
			$query[$x]="update 6932_descripcionProducto set descripcionProducto='".cv($detalleProducto["nombreProducto"])."' where idProducto=".$idProducto." and llave='".$fila[0]."'";
			$x++;	
		}
		$query[$x]="commit";
		$x++;
		return $con->ejecutarBloque($query);
	}
	
	function convertirLlaveDimensiones($llave)
	{
		global $con;		
		$arrDimensiones=array();
		$consulta="SELECT idDimension,nombreEstructura FROM 563_dimensiones";
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$arrDimensiones[$fila[0]]=$fila[1];			
		}

		
		$dimensiones=array();
		$arrDimen=explode(".",$llave);
		
		if(sizeof($arrDimen)>0)
		{
			foreach($arrDimen as $d)	
			{
				$aDimension=explode(":",$d);
				$dimensiones[$arrDimensiones[$aDimension[0]]]=$aDimension[1];
			}
		}
		
		return $dimensiones;
		
	}
	
	function obtenerTiempoExistencia($idAlmacen)
	{
		global $con;
		$consulta="SELECT idTiempoMovimiento FROM 6902_tiempoMovimientosAlmacen WHERE idAlmacen=".$idAlmacen." and referencienciaExistencia=1";
		$idTiempoMovimiento=$con->obtenerValor($consulta);
		if($idTiempoMovimiento=="")
		{
			$consulta="SELECT idTiempoMovimiento FROM 6902_tiempoMovimientosAlmacen WHERE referencienciaExistencia=1";
			$idTiempoMovimiento=$con->obtenerValor($consulta);
		}
		if($idTiempoMovimiento=="")
			$idTiempoMovimiento=-1;
		return $idTiempoMovimiento;
	}
	
	function buscarDescuentoProductoAlmacen($p1,$p2,$idProducto,$llave)
	{
		$descuento["pDescuento"]=0;
		return $descuento["pDescuento"];
	}
	
	function obtenerUnidadesMedidaEquivalencia($idUnidadMedida,&$arrUnidadMedida)
	{
		global $con;
		$consulta="	SELECT u.idUnidadMedida,IF(abreviatura='' OR abreviatura IS NULL,u.unidadMedida,CONCAT('(',u.abreviatura,') ',u.unidadMedida)) AS unidadMedida,cantidad 
					FROM 6923_equivalenciasUnidadesMedida e,6923_unidadesMedida u WHERE e.idUnidadMedida=".$idUnidadMedida." AND e.idUnidadEquivalencia=u.idUnidadMedida 
					ORDER BY u.idUnidadMedida";
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$arrUnidadMedida[$fila[1]]="['".$fila[0]."','".cv($fila[1])."']";
			obtenerUnidadesMedidaEquivalencia($fila[0],$arrUnidadesMedida);
		}
		
	}
	
	function convertirUnidadesMedida($cantidad,$unidadOrigen,$unidadDestido)
	{
		$rutaEquivalencia=obtenerRutaUnidadesEquivalencia($unidadOrigen,$unidadDestido);
		
		$equivalenciaUnidad=0;
		if($rutaEquivalencia)
		{
			$equivalenciaUnidad=1;
			foreach($rutaEquivalencia as $e)
			{
				$equivalenciaUnidad*=$e["cantidad"];
			}
		}
		
		return $cantidad*$equivalenciaUnidad;
	}
	
	function obtenerRutaUnidadesEquivalencia($unidadOrigen,$unidadDestino)
	{
		global $con;
		$encuentraRuta=false;
		if($unidadOrigen==$unidadDestino)
		{
			$arrRuta=array();
			$oEquivalencia["idUnidadMedida"]=$unidadOrigen;
			$oEquivalencia["cantidad"]=1;
			array_push($arrRuta,$oEquivalencia);
		}
		else
		{
			$consulta="SELECT idUnidadMedida,cantidad FROM 6923_equivalenciasUnidadesMedida WHERE idUnidadEquivalencia=".$unidadDestino;
			$res=$con->obtenerFilas($consulta);
			while($fila=mysql_fetch_row($res))
			{
				$arrRuta=array();
				$oEquivalencia["idUnidadMedida"]=$unidadDestino;
				$oEquivalencia["cantidad"]=$fila[1];
				array_push($arrRuta,$oEquivalencia);
				$encuentraRuta=existeRutaConversion($fila[0],$unidadOrigen,$arrRuta);
				
				if($encuentraRuta)
					break;
				
			}
			if(!$encuentraRuta)
				return NULL;
		}
		return $arrRuta;
				
	}
	
	function existeRutaConversion($unidadOrigen,$unidadDestino,&$arrRuta)
	{
		global $con;
		if($unidadOrigen==$unidadDestino)
		{
			return true;
		}
		
		$aRuta=$arrRuta;
		
		$consulta="SELECT idUnidadMedida,cantidad FROM 6923_equivalenciasUnidadesMedida WHERE idUnidadEquivalencia=".$unidadOrigen;
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			
			$oEquivalencia["idUnidadMedida"]=$unidadOrigen;
			$oEquivalencia["cantidad"]=$fila[1];
			array_push($aRuta,$oEquivalencia);
			
			$encuentraRuta=existeRutaConversion($fila[0],$unidadDestino,$aRuta);
			
			if($encuentraRuta)
			{
				$arrRuta=$aRuta;
				return true;
			}
			else
				$aRuta=$arrRuta;
			
		}
		
		return false;
		
	}	
	
	function obtenerUnidadesEquivalencia($unidadOrigen,$unidadDestino)
	{
		global $con;
		$consulta="SELECT idUnidadMedida FROM 6923_equivalenciasUnidadesMedida WHERE idUnidadEquivalencia=".$unidadDestino;
		
		
		
		
		
		
	}
	
	function generarTablaConversion($unidadOrigen,$unidadDestino)
	{
		global $con;	
		$arrUnidadesMedida=array();
		$consulta="SELECT * FROM 6923_unidadesMedida ORDER BY idUnidadMedida";
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$arrUnidadesMedida[$fila[0]]=($fila[4]=="")?$fila[1]:"(".$fila[4].") ".$fila[1];
		}
				
				
		
		$arrTablaConversion=array();
		$arrRutaConversion=obtenerRutaUnidadesEquivalencia($unidadOrigen,$unidadDestino);
		
		$acumulado=1;
		$arrTablaConversion=array();
		for($x=sizeof($arrRutaConversion)-1;$x>=0;$x--)
		{
			$c=$arrRutaConversion[$x];
			$o=array();
			$o["unidadBase"]=$unidadOrigen;
			$o["lblUnidadBase"]=$arrUnidadesMedida[$unidadOrigen];
			$o["cantidad"]=$acumulado*$c["cantidad"];
			$o["unidadConversion"]=$c["idUnidadMedida"];
			$o["lblUnidadConversion"]=$arrUnidadesMedida[$c["idUnidadMedida"]];
			$acumulado=$o["cantidad"];
			array_push($arrTablaConversion,$o);
			
		}
		return $arrTablaConversion;
	}
	
	function convertirCantidadesToDesgloce($cantidad,$unidadBase,$unidadMinima)
	{
		global $con;
		$consulta="SELECT IF(abreviatura='' OR abreviatura IS NULL,u.unidadMedida,CONCAT('(',u.abreviatura,') ',u.unidadMedida)) AS unidadMedida 
					FROM 6923_unidadesMedida u WHERE u.idUnidadMedida=".$unidadBase;
					
		$arrConversion=array();
		
		if($unidadBase==$unidadMinima)
		{
			
			$oC=array();			
			$oC["idUnidadMedida"]=$unidadBase;
			$oC["lblUnidadMedida"]=$con->obtenerValor($consulta);
			$oC["totalEquivalencia"]=1;
			$oC["cantidadEquivalencia"]=$cantidad;
			
			if(abs($cantidad)>0)
				array_push($arrConversion,$oC)	;
		}
		else
		{
			
			$arrTablaConversion=generarTablaConversion($unidadBase,$unidadMinima);
			
			$totalEquivalencia=convertirUnidadesMedida(1,$unidadBase,$unidadMinima);
			
			$totalUnidad=parteEntera($cantidad/$totalEquivalencia,false);
			$resto=$totalUnidad*$totalEquivalencia;
			$cantidad=$cantidad-$resto;	
			
			$oC=array();			
			$oC["idUnidadMedida"]=$unidadBase;
			$oC["lblUnidadMedida"]=$con->obtenerValor($consulta);
			$oC["totalEquivalencia"]=$totalEquivalencia;
			$oC["cantidadEquivalencia"]=$totalUnidad;
			
			if(abs($totalUnidad)>0)
				array_push($arrConversion,$oC)	;
			
			for($x=0;$x<sizeof($arrTablaConversion)-1;$x++)
			{
				$c=$arrTablaConversion[$x];
				$totalEquivalencia=convertirUnidadesMedida(1,$c["unidadConversion"],$unidadMinima);
				
				$totalUnidad=parteEntera($cantidad/$totalEquivalencia,false);
				$resto=$totalUnidad*$totalEquivalencia;
				$cantidad=$cantidad-$resto;			
				
				$oC=array();			
				$oC["idUnidadMedida"]=$c["unidadConversion"];
				$oC["lblUnidadMedida"]=$c["lblUnidadConversion"];
				$oC["totalEquivalencia"]=$totalEquivalencia;
				$oC["cantidadEquivalencia"]=$totalUnidad;
				
				if(abs($totalUnidad)>0)
					array_push($arrConversion,$oC)	;						
			}
			
	
			$x=sizeof($arrTablaConversion)-1;
			$c=$arrTablaConversion[$x];
			$oC=array();			
			$oC["idUnidadMedida"]=$c["unidadConversion"];
			$oC["lblUnidadMedida"]=$c["lblUnidadConversion"];
			$oC["totalEquivalencia"]=1;
			$oC["cantidadEquivalencia"]=$cantidad;
			
			if(abs($cantidad)>0)
				array_push($arrConversion,$oC)	;
				
			
		}
		return $arrConversion;
	}
	
	function obtenerAlmacenesDisponiblesJS()
	{
		global $con;
		
		$consulta="SELECT idAlmacen,nombreAlmacen from 6900_almacenes ORDER BY nombreAlmacen";
		$res=$con->obtenerFilas($consulta);
		$listAlmacen="";
		$arrAlmacenes="";
		$arrAlmacenesDisp="";
		while($fila=mysql_fetch_row($res))
		{
			$o="['".$fila[0]."','".cv($fila[1])."']";
			if($arrAlmacenes=="")
				$arrAlmacenes=$o;
			else
				$arrAlmacenes.=",".$o;
			if($listAlmacen=="")
				$listAlmacen=$fila [0];
			else
				$listAlmacen.=",".$fila [0];
		}
		
		if($listAlmacen=="")
			$listAlmacen=-1;
		$o="['".$listAlmacen."','Cualquier almacén']";
		if($arrAlmacenes=="")
			$arrAlmacenes=$o;
		else
			$arrAlmacenes.=",".$o;
		
		return $arrAlmacenes;
	}
	
	function obtenerTodosAlmacenesDisponiblesJS()
	{
		global $con;
		
		$consulta="SELECT idAlmacen,nombreAlmacen from 6900_almacenes ORDER BY nombreAlmacen";
		$res=$con->obtenerFilas($consulta);
		$listAlmacen="";
		$arrAlmacenes="";
		$arrAlmacenesDisp="";
		while($fila=mysql_fetch_row($res))
		{
			$o="['".$fila[0]."','".cv($fila[1])."']";
			if($arrAlmacenes=="")
				$arrAlmacenes=$o;
			else
				$arrAlmacenes.=",".$o;
			if($listAlmacen=="")
				$listAlmacen=$fila [0];
			else
				$listAlmacen.=",".$fila [0];
		}
		
		
		
		return "[".$arrAlmacenes."]";
	}
	
	function obtenerTodosAlmacenesDisponiblesPHP()
	{
		global $con;
		
		$consulta="SELECT idAlmacen,nombreAlmacen from 6900_almacenes ORDER BY nombreAlmacen";
		$res=$con->obtenerFilas($consulta);
		$arrAlmacenes=array();
		while($fila=mysql_fetch_row($res))
		{
			$arrAlmacenes[$fila[0]]=$fila[1];
		}
		return $arrAlmacenes;
	}
	
?>