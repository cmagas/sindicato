<?php
	function clonarElementosReporte($idReporteO,$idReporteD)
	{
		global $con;
		$consulta="SELECT idElementoReporteSig FROM 903_variablesSistema for update";
		$elementoSig=$con->obtenerValor($consulta);
		$consulta="SELECT * FROM 9011_elementosReportesThot WHERE idReporte=".$idReporteO;	
		$res=$con->obtenerFilas($consulta);
		$nFilasAfectadas=$con->filasAfectadas;
		$diccionario=array();
		while($fila=mysql_fetch_assoc($res))
		{
			$idPadre=$fila["idPadre"];
			if($idPadre!="-1")
				$idPadre=$diccionario["p".$fila["idPadre"]];
			$consulta="INSERT INTO 9011_elementosReportesThot(idIdioma,idReporte,tipoElemento,idGrupoElemento,nombreCampo,posX,posY,eliminable,idPadre)
						values(".$fila["idIdioma"].",".$idReporteD.",".$fila["tipoElemento"].",".$elementoSig.",'".$fila["nombreCampo"]."',".$fila["posX"].",".
						$fila["posY"].",".$fila["eliminable"].",".$idPadre.")";
			if(!$con->ejecutarConsulta($consulta))			
			{
				return false;
			}
			$idElemento=$elementoSig;			
			$consulta="INSERT INTO 9012_configuracionElemReporteThot(idElemReporte,campoConf1,campoConf2,campoConf3,campoConf4,campoConf5,campoConf6,campoConf7,campoConf8,campoConf9,campoConf10,campoConf11,campoConf12,campoConf13)
						SELECT '".$idElemento."' AS idEReporte,campoConf1,campoConf2,campoConf3,campoConf4,campoConf5,campoConf6,campoConf7,campoConf8,campoConf9,campoConf10,campoConf11,campoConf12,campoConf13 FROM 9012_configuracionElemReporteThot
						WHERE idElemReporte=".$fila["idGrupoElemento"];
			
			if(!$con->ejecutarConsulta($consulta))			
			{
				return false;
			}
			$diccionario["p".$fila["idElemento"]]=$idElemento;	
			$elementoSig++;
		}
		$consulta="update 903_variablesSistema	set idElementoReporteSig=".$elementoSig;
		if($con->ejecutarConsulta($consulta))
			return true;
		return false;
	}
	
	function clonarAlmacenesReporte($idReporte,$idDestino)
	{
		global $con;
		$consulta="select * from 9014_almacenesDatos where idReporte=".$idReporte." and tipoDataSet=1 order by idDataSet";
		$resAlmacen=$con->obtenerFilas($consulta);	
		$nAlmacenes=$con->filasAfectadas;
		$arrDiccionario=array();
		
		while($fila=mysql_fetch_assoc($resAlmacen))
		{
			$operacion=$fila["operacion"];
			if($operacion=="")
				$operacion="NULL";
			$consulta="INSERT INTO 9014_almacenesDatos(nombreDataSet,descripcion,camposProy,consulta,idReporte,idUsuario,nodosFiltro,operacion,nTabla,parametros,tipoAlmacen,tipoDataSet,orden,complementario)
						VALUES('".$fila["nombreDataSet"]."','".cv($fila["descripcion"])."','".cv($fila["camposProy"])."','".cv($fila["consulta"])."',".$idDestino.",".$_SESSION["idUsr"].",'".cv($fila["nodosFiltro"])."',
						".$operacion.",'".$fila["nTabla"]."','".$fila["parametros"]."',".$fila["tipoAlmacen"].",".$fila["tipoDataSet"].",'".$fila["orden"]."','".$fila["complementario"]."')";	

			if(!$con->ejecutarConsulta($consulta))			
			{
				return false;
			}
			
			$idDataSet=$con->obtenerUltimoID();	
			$consulta="INSERT INTO 9014_valoresParametroAlmacenesDatos(parametro,valor,valorUsr,tipovalor,idDataSet)
						SELECT parametro,valor,valorUsr,tipoValor,'".$idDataSet."' AS iDataSet from 9014_valoresParametroAlmacenesDatos WHERE idDataSet=".$fila["idDataSet"];

			if(!$con->ejecutarConsulta($consulta))			
			{
				return false;
			}
			$arrDiccionario["p".$fila["idDataSet"]]=$idDataSet;
		}

		$diccionarioCat=array();
		$diccionarioSerie=array();
		if($nAlmacenes!=0)
		{
			mysql_data_seek($resAlmacen,0);
			while($fila=mysql_fetch_assoc($resAlmacen))
			{
				if($fila["tipoAlmacen"]==2)
				{
					$consulta="SELECT idCategoriaAlmacenGrafico,nombreCategoria,color FROM 9014_categoriasAlmacenesGraficos WHERE idAlmacen=".$fila["idDataSet"];
					$resCat=$con->obtenerFilas($consulta);
					
					while($fCat=mysql_fetch_assoc($resCat))
					{
						$consulta="INSERT INTO 9014_categoriasAlmacenesGraficos (idAlmacen,nombreCategoria,color) values(".$arrDiccionario["p".$fila["idDataSet"]].",'".$fCat["nombreCategoria"]."','".$fCat["color"]."')";
						if(!$con->ejecutarConsulta($consulta))			
						{
							return false;
						}
						$idCat=$con->obtenerUltimoID();
						$diccionarioCat["p".$fCat["idCategoriaAlmacenGrafico"]]=$idCat;
					}
						
					
					$consulta="SELECT idSerieAlmacenGrafico,nombreSerie,titulo,color FROM 9014_seriesAlmacenesGraficos WHERE idAlmacen=".$fila["idDataSet"];
					$resSerie=$con->obtenerFilas($consulta);
					
					while($fSerie=mysql_fetch_assoc($resSerie))
					{
						$consulta="INSERT INTO 9014_seriesAlmacenesGraficos (idAlmacen,nombreSerie,titulo,color) values(".$arrDiccionario["p".$fila["idDataSet"]].",'".$fSerie["nombreSerie"]."','".$fSerie["titulo"]."','".$fSerie["color"]."')";
						if(!$con->ejecutarConsulta($consulta))			
						{
							return false;
						}
						$idSerie=$con->obtenerUltimoID();
						$diccionarioSerie["p".$fSerie["idSerieAlmacenGrafico"]]=$idSerie;
					}
					
					$consulta="SELECT * FROM 9014_valoresAlmacenGrafico WHERE idAlmacen=".$fila["idDataSet"];
					//$consultaAux="SELECT * FROM 9014_valoresAlmacenGrafico WHERE idAlmacen=".$fila["idDataSet"];
					$resValoresGraf=$con->obtenerFilas($consulta);
					while($filaValGraf=mysql_fetch_assoc($resValoresGraf))
					{
						$valor="";	
						if(($filaValGraf["tipoValor"]==7)||($filaValGraf["tipoValor"]==11))
						{
							$arrDatos=explode("|",$filaValGraf["valor"]);
							$valor=$arrDiccionario["p".$arrDatos[0]]."|".$arrDatos[1];	
						}
						$consulta="INSERT INTO 9014_valoresAlmacenGrafico(idCategoria,idSerie,idAlmacen,tipoValor,valor)
									VALUES(".$diccionarioCat["p".$filaValGraf["idCategoria"]].",".$diccionarioSerie["p".$filaValGraf["idSerie"]].",
									".$arrDiccionario["p".$fila["idDataSet"]].",".$filaValGraf["tipoValor"].",'".$valor."')";

						if(!$con->ejecutarConsulta($consulta))			
						{
							return false;
						}
						
					}
					
				}
			}
		}
		
		$consulta="	SELECT * FROM 9012_configuracionElemReporteThot  WHERE idElemReporte IN(
					SELECT idGrupoElemento FROM 9011_elementosReportesThot WHERE idReporte=".$idDestino." AND tipoElemento in (31,25,30,28))";
		
		$resGrafico=$con->obtenerFilas($consulta);
		while($filaGrafico=mysql_fetch_row($resGrafico))
		{
			$consulta="select tipoElemento from 9011_elementosReportesThot where idGrupoElemento=".$filaGrafico[1];
			$tElemento=$con->obtenerValor($consulta);
			switch($tElemento)
			{
				case 31:
					$consulta="update 9012_configuracionElemReporteThot set campoConf5=".$arrDiccionario["p".$filaGrafico[6]]." where idConfElemento=".$filaGrafico[0];	
				break;
				case 25:
					$consulta="update 9012_configuracionElemReporteThot set campoConf4=".$arrDiccionario["p".$filaGrafico[5]]." where idConfElemento=".$filaGrafico[0];	
				break;
				case 28:
					$consulta="update 9012_configuracionElemReporteThot set campoConf9=".$arrDiccionario["p".$filaGrafico[10]]." where idConfElemento=".$filaGrafico[0];	
				break;
				case 30:
					$consulta="update 9012_configuracionElemReporteThot set campoConf1=".$arrDiccionario["p".$filaGrafico[2]]." where idConfElemento=".$filaGrafico[0];	
				break;
			}
			if(!$con->ejecutarConsulta($consulta))			
			{
				return false;
			}
		}
		
		
		$consulta="select idDataSet,nodosFiltro from 9014_almacenesDatos where idReporte=".$idDestino." and tipoDataSet=1";
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$cadNodo='';
			$obj='';
			$nodos=$fila[1];
			$arrObj=json_decode($nodos);	
			if(sizeof($arrObj)>0)
			{
				foreach($arrObj as $obj)	
				{
					$tValor=$obj->tokenTipo;
					$arrValor=explode("|",$tValor);
					if(($arrValor[0]=='7')||($arrValor[0]=='11'))
					{
						$tValor=$arrValor[0]."|".$arrDiccionario["p".$arrValor[1]];	
					}
					$oNodo='{"tokenUsuario":"'.cv($obj->tokenUsuario).'","tokenMysql":"'.cv($obj->tokenMysql).'","tokenTipo":"'.$tValor.'"}';
					if($cadNodo=='')
						$cadNodo=$oNodo;
					else
						$cadNodo.=",".$oNodo;
								
				}
			}
			$cadNodo="[".$cadNodo."]";
			$consulta="update 9014_almacenesDatos set nodosFiltro='".$cadNodo."' where idDataSet=".$fila[0];	
			if(!$con->ejecutarConsulta($consulta))			
			{
				return false;
			}
		}
		
		return true;
	}
	
?>