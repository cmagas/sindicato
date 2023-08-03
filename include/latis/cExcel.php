<?php	include_once("latis/PHPExcel.php");		

	class cExcel
	{
		var $nArchivo;
		var $hojaActiva;
		var $libroExcel;
		var $celdaActiva;
		
		
		function cExcel($nombreArchivo,$cargaPlantilla=false,$tipoArchivo="Excel5")
		{
			$this->nArchivo=$nombreArchivo;
			if(!$cargaPlantilla)
			{
				$this->libroExcel=new PHPExcel();
			}
			else
			{
				$objReader = PHPExcel_IOFactory::createReader($tipoArchivo);
				$this->libroExcel=$objReader->load($nombreArchivo);
			}
			$this->libroExcel->setActiveSheetIndex(0);
			$this->hojaActiva=$this->libroExcel->getActiveSheet();
		}	
		
		
		
		function setNombreArchivo($nombreArchivo)
		{
			$this->nArchivo=$nombreArchivo;	
		}
		//Control
	
		function generarArchivo($formato="Excel5",$nArchivo="") //PDF; Excel2007
		{
			global $baseDir;
			global $tipoServidor;
			$nomArchivo="";
			if($nArchivo=="")
				$nomArchivo=$this->nArchivo;
			else
				$nomArchivo=$nArchivo;
				
				
			switch($formato)
			{
				case "HTML":
				break;
				case "PDF":
				break;
				default:
					header('Content-Type: application/vnd.ms-excel');
					header('Content-Disposition: attachment; filename='.$nomArchivo);
					header('Cache-Control: max-age=0');
				break;
			}	
				
			
			if($formato!="PDF")
			{
				$objExcelWrite=PHPExcel_IOFactory::createWriter($this->libroExcel,$formato); //Excel5
				$objExcelWrite->save("php://output");
			}
			else
			{
				$separador="/";
				if($tipoServidor!="1")
					$separador="\\";
				$baseArchivoDestino=$baseDir."/archivosTemporales/".date("YmdHis_".rand(10,1000)).".xlsx";
				
				$arrArchivo=explode($separador,str_replace("..","",$nomArchivo));
				
				$nombreFinalArchivo=$arrArchivo[sizeof($arrArchivo)-1];
				$arrArchivo=explode(".",$nombreFinalArchivo);
				$objExcelWrite=PHPExcel_IOFactory::createWriter($this->libroExcel,"Excel2007"); 
				$objExcelWrite->save($baseArchivoDestino);

				generarDocumentoPDF($baseArchivoDestino,true,true,true,$arrArchivo[0].".pdf");
			}
		}
		
		function generarArchivoServidor($formato="Excel5",$nArchivo="") //PDF; Excel2007
		{
			global $baseDir;
			global $tipoServidor;

			$nomArchivo="";
			if($nArchivo=="")
				$nomArchivo=$this->nArchivo;
			else
				$nomArchivo=$nArchivo;
			if($formato!="PDF")
			{
				$objExcelWrite=PHPExcel_IOFactory::createWriter($this->libroExcel,$formato); //Excel5
				$objExcelWrite->save($nomArchivo);
			}
			else
			{

				$separador="/";
				if($tipoServidor!="1")
					$separador="\\";
					
				$nombreAleatorio=date("YmdHis_".rand(10,1000));	
				$baseArchivoDestino=$baseDir."/archivosTemporales/".$nombreAleatorio.".xlsx";
				
				$arrArchivo=explode($separador,$nomArchivo);
				
				$nombreFinalArchivo=$arrArchivo[sizeof($arrArchivo)-1];

				$arrArchivo=explode(".",$nombreFinalArchivo);

				$rutaFinal=str_replace($nombreFinalArchivo,$arrArchivo[0].".pdf",$nomArchivo);


				$objExcelWrite=PHPExcel_IOFactory::createWriter($this->libroExcel,"Excel2007"); 
				$objExcelWrite->save($baseArchivoDestino);

				generarDocumentoPDF($baseArchivoDestino,false,false,true,$arrArchivo[0].".pdf");

				$archivoPDF=$nombreAleatorio.".pdf";

				
				if(file_exists($baseDir."/archivosTmpPDF/".$archivoPDF))
					copy($baseDir."/archivosTmpPDF/".$archivoPDF,$rutaFinal);
				unlink($baseDir."/archivosTmpPDF/".$archivoPDF);
			}
		}
		
		
		function cambiarHojaActiva($nHoja)
		{
			$this->libroExcel->setActiveSheetIndex($nHoja);
			$this->hojaActiva=$this->libroExcel->getActiveSheet();
		}
		
		function cambiarTituloHoja($nHoja,$titulo)
		{
			$hojaActual=$this->libroExcel->getActiveSheet();
			$this->cambiarHojaActiva($nHoja);
			$this->hojaActiva->setTitle($titulo);
			$this->hojaActiva=$hojaActual;
		}
		
		function obtenerHojaActiva()
		{
			return $this->libroExcel->getActiveSheet();
		}
		
		function setValor($celda,$valor)
		{
			$this->hojaActiva->setCellValue($celda,$valor);
		}
		
		function getValor($celda)
		{
			return $this->hojaActiva->getCell($celda)->getValue();
		}
		
		function insertarFila($nFila,$numFilas=1)
		{

			$this->hojaActiva->insertNewRowBefore($nFila,$numFilas);
		}
		
		function removerFila($nFila,$nElemento)
		{

			$this->hojaActiva->removeRow($nFila,$nElemento);
		}
		
		function crearNuevaHoja()
		{
			$this->libroExcel->createSheet();	
		}
		
		function removerHoja($nHoja)
		{
			$this->libroExcel->removeSheetByIndex($nHoja);
		}
		
		//Posicionamiento
		
		function obtenerSiguienteColumna($col)
		{
			$letra=substr(strtoupper($col),strlen($col)-1,1);
			$ascii=ord($letra);
			$ascii++;
			$letraRes="";
			if($ascii>90)
			{
				if(strlen($col)==1)
				{
					return "AA";
				}
				else
				{
					$cad1=substr($col,0,1);
					$asciiCad=ord($cad1);
					$asciiCad++;
					return chr($asciiCad)."A";
				}
			}
			else
			{
				if(strlen($col)==1)
				{
					return chr($ascii);
				}
				else
				{
					$cad1=substr($col,0,1);
					return $cad1.chr($ascii);
				}
			}
	
		}
			
		function obtenerAnteriorColumna($col)
		{
			$letra=substr(strtoupper($col),strlen($col)-1,1);
			$ascii=ord($letra);
			$ascii--;
			$letraRes="";
			if($ascii<65)
			{
				if(strlen($col)==1)
				{
					return "A";
				}
				else
				{
					$cad1=substr($col,0,1);
					$asciiCad=ord($cad1);
					$asciiCad--;
					return chr($asciiCad)."Z";
				}
			}
			else
			{
				if(strlen($col)==1)
				{
					return chr($ascii);
				}
				else
				{
					$cad1=substr($col,0,1);
					return $cad1.chr($ascii);
				}
			}
			
		}
		
		function obtenerDesplazamientoColumna($col,$nColumnas)
		{
			$colFinal=$col;
			if($nColumnas>0)
			{
				for($x=0;$x<$nColumnas;$x++)
				{
					$colFinal=$this->obtenerSiguienteColumna($colFinal);
				}
			}
			else
			{
				$nColumnas=abs($nColumnas);
				for($x=0;$x<$nColumnas;$x++)
				{
					$colFinal=$this->obtenerAnteriorColumna($colFinal);
				}
			}
			return $colFinal;
		}
		
		//Formato
		
		function setColorLetra($rango,$color)
		{
			$this->hojaActiva->getStyle($rango)->getFont()->getColor()->setARGB("00".$color);
		}
		
		function combinarCelda($celdaIni,$celdaFin)
		{
			$this->hojaActiva->mergeCells($celdaIni.":".$celdaFin);
		}
		
		function desCombinarCelda($celdaIni,$celdaFin)
		{
			$this->hojaActiva->unmergeCells($celdaIni.":".$celdaFin);
		}
		
		function setHAlineacion($rango,$tAlineacion) //D,I,C,J
		{
			$alineacion;
			switch($tAlineacion)
			{
				case "D":
					$alineacion=PHPExcel_Style_Alignment::HORIZONTAL_RIGHT;
				break;
				case "I":
					$alineacion=PHPExcel_Style_Alignment::HORIZONTAL_LEFT;
				break;
				case "C":
					$alineacion=PHPExcel_Style_Alignment::HORIZONTAL_CENTER;
				break;
				case "J":
					$alineacion=PHPExcel_Style_Alignment::HORIZONTAL_JUSTIFY;
				break;
			}
			$this->hojaActiva->getStyle($rango)->getAlignment()->setHorizontal($alineacion);
		}
		
		function setVAlineacion($rango,$tAlineacion) //I,S,C,J
		{
			$alineacion;
			switch($tAlineacion)
			{
				case "I":
					$alineacion=PHPExcel_Style_Alignment::VERTICAL_BOTTOM;
				break;
				case "S":
					$alineacion=PHPExcel_Style_Alignment::VERTICAL_TOP;
				break;
				case "C":
					$alineacion=PHPExcel_Style_Alignment::VERTICAL_CENTER;
				break;
				case "J":
					$alineacion=PHPExcel_Style_Alignment::VERTICAL_JUSTIFY;
				break;
			}
			$this->hojaActiva->getStyle($rango)->getAlignment()->setVertical($alineacion);
		}
				
		function activarCortarPalabras($rango)
		{
			$this->hojaActiva->getStyle($rango)->getAlignment()->setWrapText(true);	
		}	
		
		function desActivarCortarPalabras($rango)
		{
			$this->hojaActiva->getStyle($rango)->getAlignment()->setWrapText(false);	
		}
				
		function setNegritas($rango)
		{
			$this->hojaActiva->getStyle($rango)->getFont()->setBold(true);

		}
		
		function unsetNegritas($rango)
		{
			$this->hojaActiva->getStyle($rango)->getFont()->setBold(false);

		}
		
		function setCursivas($rango)
		{
			$this->hojaActiva->getStyle($rango)->getFont()->setItalic(true);

		}
				
		function setSubrayado($rango,$estiloSub) //N ninguno, D doble, S sencillo, DD doble discontinuo, SD sencillo discontinuo
		{
			$estilo;
			switch($estiloSub)
			{
				case "N":
					$estilo=PHPExcel_Style_Font::UNDERLINE_NONE;
				break;
				case "D":
					$estilo=PHPExcel_Style_Font::UNDERLINE_DOUBLE;
				break;
				case "S":
					$estilo=PHPExcel_Style_Font::UNDERLINE_SINGLE;
				break;
				case "DD":
					$estilo=PHPExcel_Style_Font::UNDERLINE_DOUBLEACCOUNTING;
				break;
				case "SD":
					$estilo=PHPExcel_Style_Font::UNDERLINE_SINGLEACCOUNTING;
				break;	
			}
			
			$this->hojaActiva->getStyle($rango)->getFont()->setUnderline($estilo);
		}
		
		function setColorFondo($rango,$color,$estilo="S") //Solido, N Ninguno
		{
			$estiloLlenado;
			switch($estilo)
			{
				case "S":
					$estiloLlenado=PHPExcel_Style_Fill::FILL_SOLID;
				break;	
				case "N":
					$estiloLlenado=PHPExcel_Style_Fill::FILL_NONE;
				break;
			}
			$this->hojaActiva->getStyle($rango)->getFill()->setFillType($estiloLlenado);
			$this->hojaActiva->getStyle($rango)->getFill()->getStartColor()->setARGB('00'.$color);
		}
		
		function setBorde($rango,$estilo,$color="000000") //P punteado,N ninguno,PR punto y guion, RPP  raya punto punto, D doble,G grueso,DE delgado
		{
			$estiloLinea;
			switch($estilo)
			{
				case "P":
					$estiloLinea=PHPExcel_Style_Border::BORDER_DOTTED;
				break;
				case "N":
					$estiloLinea=PHPExcel_Style_Border::BORDER_NONE;
				break;
				case "PR":
					$estiloLinea=PHPExcel_Style_Border::BORDER_DASHDOT;
				break;
				case "RPP":
					$estiloLinea=PHPExcel_Style_Border::BORDER_DASHDOTDOT;
				break;
				case "D":
					$estiloLinea=PHPExcel_Style_Border::BORDER_DOUBLE;
				break;
				case "G":
					$estiloLinea=PHPExcel_Style_Border::BORDER_THICK;
				break;
				case "DE":
					$estiloLinea=PHPExcel_Style_Border::BORDER_THIN;
				break;
				
			}
			$arrEstilo["borders"]["allborders"]["color"]["argb"]="00".$color;
			$arrEstilo["borders"]["allborders"]["style"]=$estiloLinea;
			$this->hojaActiva->getStyle($rango)->applyFromArray($arrEstilo);	
		}
		
		function setAnchoColumna($columna,$ancho) //ancho =auto o numero
		{
			switch($ancho)
			{
				case "auto"	:
					$this->hojaActiva->getColumnDimension($columna)->setAutoSize(true);
				break;
				default:
					$this->hojaActiva->getColumnDimension($columna)->setWidth($ancho);
				break;
			}
		}
		
		function setAnchoRango($columnaIni,$columnaFin,$ancho) //ancho =auto o numer
		{
			$cIni=$columnaIni;
			$cFinal=$this->obtenerSiguienteColumna($columnaFin);
			$ct=0;
			while($cIni<>$cFinal)
			{
				$this->setAnchoColumna($cIni,$ancho);
				$cIni=$this->obtenerSiguienteColumna($cIni);
				
			}
		}
		
		function setAltoFila($fila,$alto) //ancho =auto o numero
		{
			if($alto=="auto")
				$alto=-1;
			$this->hojaActiva->getRowDimension($fila)->setRowHeight($alto);
			
			/*switch($fila)
			{
				
				default:
					$this->hojaActiva->getColumnDimension($fila)->setRowHeight($alto);
				break;
			}*/
		}
		
		function setColorFuente($rango,$color)
		{
			$this->hojaActiva->getStyle($rango)->getFont()->getColor()->setARGB("00".$color);
		}
		
		function setFuente($rango,$fuente)
		{
			$this->hojaActiva->getStyle($rango)->getFont()->setName($fuente);
		}
		
		function setTamano($rango,$tamano)
		{
			$this->hojaActiva->getStyle($rango)->getFont()->setSize($tamano);
		}
		
		function setFormatoCelda($rango,$formato)
		{
			$this->hojaActiva->getStyle($rango)->getNumberFormat()->setFormatCode($formato);	
		}
		
		function setHipervinculo($celda,$Url,$toolTip)
		{
			$this->hojaActiva->getCell($celda)->getHyperlink()->setUrl($Url);
			$this->hojaActiva->getCell($celda)->getHyperlink()->setTooltip($toolTip);
		}
		
		function crearObjetoImagen($imagen,$nombre="",$descripcion="")
		{
			$objDrawing= new PHPExcel_Worksheet_Drawing();
			$objDrawing->setName($nombre);
			$objDrawing->setDescription($descripcion);
			$objDrawing->setPath($imagen);
			return $objDrawing;
		}
		
		function insertarImagenHoja($nHoja,$objImagen,$celda)
		{
			$objImagen->setCoordinates($celda);
			$hojaActual=$this->libroExcel->getActiveSheet();
			$this->cambiarHojaActiva($nHoja);
			$objImagen->setWorksheet($this->hojaActiva);
			$this->hojaActiva=$hojaActual;
		}

		//funciones auxiliares

		function generarTablaQuery($query,$mostrarEncabezado=true,$letraIni="A",$filaIni="1")
		{
			global $con;
			$resFilas=$con->obtenerFilas($query);
			return $this->generarTablaResultado($resFilas,$mostrarEncabezado,$letraIni,$filaIni);
			
		}
		
		function generarTablaResultado($res,$mostrarEncabezado=true,$letraIni="A",$filaIni="1")
		{
			$lIni=$letraIni;
			$filaActual=$filaIni;
			if(mysql_num_rows($res)>0)
			{
				if($mostrarEncabezado)
				{
					$this->generarEncabezadoResultado($res,$letraIni,$filaActual);
					$filaActual++;
					
				}
				while($fila=mysql_fetch_row($res))
				{
					$lIni=$letraIni;
					foreach($fila as $valor)
					{
						$this->setValor($lIni.$filaActual,$valor);
						$lIni=$this->obtenerSiguienteColumna($lIni);
						
					}
					
					$filaActual++;
				}
			}
			$lIni=$this->obtenerAnteriorColumna($lIni);
			$filaActual--;
			
			//$this->setAnchoRango($letraIni,$lIni,"auto");
			
			$resultado[0]=$lIni;
			$resultado[1]=$filaActual;
			return 	$resultado;
		}
	
		function generarTablaMatriz($matriz,$letraIni="A",$filaIni="1",$considerarEncabezado=false)
		{
			$ct=0;
			$lIni=$letraIni;
			$filaActual=$filaIni;
			for($ct=0;$ct<sizeof($matriz);$ct++)
			{
				$lIni=$letraIni;
				$fila=$matriz[$ct];
				if($considerarEncabezado)
				{
					$this->generarFilaArreglo($fila,$lIni,$filaActual);
					$filaActual++;
					$considerarEncabezado=false;
					continue;
				}
				foreach($fila as $valor)
				{
					$this->setValor($lIni.$filaActual,urldecode($valor));
					$lIni=$this->obtenerSiguienteColumna($lIni);
					  
				}
				$filaActual++;
				
			}	
			$lIni=$this->obtenerAnteriorColumna($lIni);
			$filaActual--;
			
			//$this->setAnchoRango($letraIni,$lIni,"auto");
			$resultado[0]=$lIni;
			$resultado[1]=$filaActual;
			return 	$resultado;
		}
		
		function generarEncabezadoResultado($res,$letraIni="A",$filaIni="1")
		{
			$lIni=$letraIni;
			$filaActual=$filaIni;
			if(mysql_num_rows($res)>0)
			{
				
				  $filaRes=mysql_fetch_assoc($res);
				  foreach($filaRes as $campo=>$valor)
				  {
					  $this->setValor($lIni.$filaActual,$campo);
					  $lIni=$this->obtenerSiguienteColumna($lIni);
					  
				  }
				  $lIni=$this->obtenerAnteriorColumna($lIni);
			}
			return $lIni;
		}
		
		function generarFilaArreglo($fila,$letraIni="A",$filaActual="1")
		{
			$lIni=$letraIni;
			foreach($fila as $valor)
			{
				$this->setValor($lIni.$filaActual,$valor);
				$lIni=$this->obtenerSiguienteColumna($lIni);
				
			}
			$lIni=$this->obtenerAnteriorColumna($lIni);
			return $lIni;
		}
				
		function clonarRangoCeldas($colIniOrigen,$filaIniOrigen,$colFinOrigen,$filaFinOrigen,$colDestino,$filaDestino,$numHojaOrigen=0,$numHojaDestino=0,$clonarAltoFilas=false,$clonarAnchoCelda=false)
		{
			$numColD=$this->columnaToNumero($colDestino);
			$numFilaD=$filaDestino;
			
			
			$numColO=$this->columnaToNumero($colIniOrigen);
			$numFilaO=$filaIniOrigen;
			
			
			$numColDesplazamiento=$numColD-$numColO;
			$numFilaDesplazamiento=$numFilaD-$numFilaO;
			
			
			$hojaOrigen=$this->libroExcel->getSheet($numHojaOrigen);
			$hojaDestino=$this->libroExcel->getSheet($numHojaDestino);
			$hojaDestino->removeRow($filaDestino,1);
			$nfilaFinal=$filaDestino;
			
			$totalFilas=($filaFinOrigen-$filaIniOrigen+1);
			$hojaDestino->insertNewRowBefore($filaDestino,$totalFilas);	
			$nfilaFinal+=$totalFilas;
			/*for($ct=$filaIniOrigen;$ct<=$filaFinOrigen;$ct++)
			{
				$hojaDestino->insertNewRowBefore($filaDestino);	
				$nfilaFinal++;
				
						
			}*/
			
			$rango=$hojaOrigen->rangeToArray($colIniOrigen.$filaIniOrigen.":".$colFinOrigen.$filaFinOrigen);
			$hojaDestino->fromArray($rango,null,$colDestino.$filaDestino);
			$filaCopia=$filaDestino;
			for($ct=$filaIniOrigen;$ct<=$filaFinOrigen;$ct++)
			{
				
				$copiar=true;
				$colIni=$colIniOrigen;
				$colCopia=$colDestino;
				
				$limiteAlcanzado=false;
				if($colIni==$colFinOrigen)
					$limiteAlcanzado=true;
				
				while($copiar)
				{
					$estilo=$hojaOrigen->getStyle($colIni.$ct);
					
					$hojaDestino->duplicateStyle($estilo,$colCopia.$filaCopia);
					
					$colIni=$this->obtenerSiguienteColumna($colIni);
					
					
					
					$colCopia=$this->obtenerSiguienteColumna($colCopia);
					if($limiteAlcanzado)
						$copiar=false;
					if($colIni==$colFinOrigen)
					{
						$limiteAlcanzado=true;
					}	

					
				}
				$filaCopia++;
			}
			
			
			$arrCeldasCombinadas=$hojaOrigen->getMergeCells();
			foreach($arrCeldasCombinadas as $rango)
			{
				$arrRango=explode(":",$rango);
				if($this->celdaEstaEnRango($arrRango[0],$colIniOrigen.$filaIniOrigen.":".$colFinOrigen.$filaFinOrigen))
				{
					$arrRangoAUx[0]=$this->obtenerDesplazamientoCelda($arrRango[0],$numFilaDesplazamiento,$numColDesplazamiento);	
					$arrRangoAUx[1]=$this->obtenerDesplazamientoCelda($arrRango[1],$numFilaDesplazamiento,$numColDesplazamiento);	
					$hojaDestino->mergeCells($arrRangoAUx[0].":".$arrRangoAUx[1]);

				}
			}
			if($clonarAltoFilas)
			{
				$nFila=0;
				for($ct=$filaIniOrigen;$ct<=$filaFinOrigen;$ct++)
				{
					$alto=$hojaOrigen->getRowDimension($ct)->getRowHeight();	

					$hojaDestino->getRowDimension($filaDestino+$nFila)->setRowHeight($alto);
					$nFila++;
					
					
				}
			}
			
			if($clonarAnchoCelda)
			{
				$continuar=true;
				$colO=$colIniOrigen;
				$colD=$colDestino;
				while($continuar)
				{
					$ancho=$hojaOrigen->getColumnDimension($colO)->getWidth();	
					$hojaDestino->getColumnDimension($colD)->setWidth($ancho);	
					if($colO==$colFinOrigen)
						$continuar=false;
					else
					{
						$colO=$this->obtenerSiguienteColumna($colO);
						$colD=$this->obtenerSiguienteColumna($colD);
					}
				}
			}
			
			return $nfilaFinal;
		}
				
		function celdaEstaEnRango($celda,$rango)
		{
			$arrCeldaBase=$this->descomponerCelda($celda);
			$arrCeldaBase[0]=$this->columnaToNumero($arrCeldaBase[0]);
			$arrRango=explode(":",$rango);
			$arrCeldaInicioRango=$this->descomponerCelda($arrRango[0]);
			$arrCeldaInicioRango[0]=$this->columnaToNumero($arrCeldaInicioRango[0]);
			$arrCeldaFinRango=$this->descomponerCelda($arrRango[1]);
			$arrCeldaFinRango[0]=$this->columnaToNumero($arrCeldaFinRango[0]);
			
			if(($arrCeldaBase[0]>=$arrCeldaInicioRango[0])&&($arrCeldaBase[0]<=$arrCeldaFinRango[0]))
			{
				if(($arrCeldaBase[1]>=$arrCeldaInicioRango[1])&&($arrCeldaBase[1]<=$arrCeldaFinRango[1]))
					return true;
			}
			return false;
		}		
		
		function descomponerCelda($celda)
		{
			$arrCelda=array();
			$colRef="";
			
			$filaRef="";
			$tamCelda=strlen($celda);
			for($ct=0;$ct<$tamCelda;$ct++)
			{
				if(ctype_digit($celda[$ct]))	
					$filaRef.=$celda[$ct];
				else
					$colRef.=$celda[$ct];
			}
			$arrCelda[0]=$colRef;
			$arrCelda[1]=$filaRef;
			return $arrCelda;
		}
		
		function columnaToNumero($columna)
		{
			$colIni="A";
			$pos=0;
			while(true)
			{
				if($colIni==$columna)
					return $pos;
				$colIni=$this->obtenerSiguienteColumna($colIni);
				$pos++;
			}
				
		}
		
		function obtenerDesplazamientoCelda($celda,$dFila,$dColumna)
		{
			$arrCelda=$this->descomponerCelda($celda);
			if($dColumna!=0)
				$arrCelda[0]=$this->obtenerDesplazamientoColumna($arrCelda[0],$dColumna);
			$arrCelda[1]+=$dFila;
			return $arrCelda[0].$arrCelda[1];
		}
		
	}
?>