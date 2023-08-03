<?php	include_once("latis/conexionBD.php"); 
		define('IN_CB',true);
		define('IMG_FORMAT_PNG',	1);
		define('IMG_FORMAT_JPEG',	2);
		define('IMG_FORMAT_WBMP',	4);
		define('IMG_FORMAT_GIF',	8);

		include('latis/classCodigoBarras/FColor.php');
		include('latis/classCodigoBarras/BarCode.php');
		include('latis/classCodigoBarras/FDrawing.php');
		
		
	
	$consulta="SELECT * FROM  824_formatosCodigoBarra ORDER BY etiqueta";
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		include("latis/classCodigoBarras/".$fila[2]); 

	}

	class cCodigoBarras
	{
		var $formato;
		var $valor;
		
		var $arrFormatos;
		var $formatoImg; //1 png, 2 jpg
		var $anchoImagen; //1 Delgado; 2 Medio; 3 ancho //QR anchoImagen=tam. cuadro
		var $altoImagen;
		var $tamLetra; //Del 1 al 5  //QR tamLetra=tam. margen
		
		var $color_black;
		var $extension;
		var $color_white;
		var $comp1; //comp1: code 128=A,B,C (Subconjunto caracteres);  EAN-13='' NO slash(-),'1' con slash; I25, S25, MSI = '' No check sum, '1' con Checksum
					//QR=L,M,Q,H ECC redundancia de errores (Low 7%,Medium 15%,Quartile 25%,High 30%)

		
		function cCodigoBarras($valor="",$formato="",$comp1="",$formatoImg=1,$anchoImagen=2,$altoImagen=30,$tamLetra=2)
		{
			global $con;
			
			$this->valor=$valor;
			$this->formato=strtoupper($formato);
			$this->formatoImg=$formatoImg;
			$this->anchoImagen=$anchoImagen;
			$this->tamLetra=$tamLetra;
			$this->color_black = new FColor(0,0,0);
			$this->color_white= new FColor(255,255,255);
			$this->comp1=strtoupper($comp1);
			$this->altoImagen=$altoImagen;
			$this->arrFormatos=array();
			$consulta="SELECT * FROM  824_formatosCodigoBarra ORDER BY etiqueta";
			$res=$con->obtenerFilas($consulta);
			while($fila=mysql_fetch_row($res))
			{
				$this->arrFormatos[$fila[7]]["nombreClase"]=$fila[3];
			}
			
			if($formato=="QR")
			{
				if($comp1=="")
					$this->comp1="M";
			}
			
			switch($formatoImg)
			{
				case 1:
					$this->extension='png';
				break;
				case 2:
					$this->extension='jpg';
				break;
			}
			
				
			
			
		}
		
		
		function setFormato($formato)
		{
			$this->formato=$formato;
		}
		
		function setValor($valor)
		{
			$this->valor=$valor;
		}
		
		function setFormatoImagen($formatoImg)
		{
			$this->formatoImg=$formatoImg;
		}
		
		function setAnchoImagen($anchoImagen)
		{
			$this->anchoImagen=$anchoImagen;
		}
		
		function setTamLetra($tamLetra)
		{
			$this->tamLetra=$tamLetra;
		}
		
		function generarCodigoBarrasImagenArchivo()
		{
			global $baseDir;
			$nArchivo='temp_'.rand(1,100).'_'.date("Y_m_d").'_'.date("H_i_s").".".$this->extension;
			$rutaArchivo=str_replace("//","/",$baseDir.'/archivosTemporalesCodigoBarras/'.$nArchivo);
			
			

			switch($this->formato)
			{
				case "QR":
				
					$imagen=new qr();

					$imagen->generarCodigoBarrasImagenArchivo($this->valor,$rutaArchivo,$this->formatoImg,$this->comp1,$this->anchoImagen,$this->tamLetra);
					
				break;
				default:
					$drawing = new FDrawing(1024,1024,$rutaArchivo,$this->color_white);

					$drawing->init();
					if($this->comp1=="")
						$code_generated = new $this->formato($this->altoImagen,$this->color_black,$this->color_white,$this->anchoImagen,$this->valor,$this->tamLetra);
					else
						$code_generated = new $this->formato($this->altoImagen,$this->color_black,$this->color_white,$this->anchoImagen,$this->valor,$this->tamLetra,$this->comp1);

					$drawing->add_barcode($code_generated);
					$drawing->draw_all();
					$im = $drawing->get_im();
					$im2 = imagecreate($code_generated->lastX,$code_generated->lastY);

					imagecopyresized($im2, $im, 0, 0, 0, 0, $code_generated->lastX, $code_generated->lastY, $code_generated->lastX, $code_generated->lastY);
					$drawing->set_im($im2);
					$drawing->finish($this->formatoImg);
				break;
			}
			
			return $nArchivo;
		}
		
		function generarCodigoBarraDatosImagen()
		{
			switch($this->formato)
			{
				case "QR":
					$imagen=new qr();
					$imagen->generarCodigoBarraDatosImagen($this->valor,$this->formatoImg,$this->comp1,$this->anchoImagen,$this->tamLetra);
				break;
				default:
					$code_generated=null;
					$drawing = new FDrawing(1024,1024,'',$this->color_white);
					$drawing->init();
					if($this->comp1=="")
						$code_generated = new $this->arrFormatos[$this->formato]["nombreClase"]($this->altoImagen,$this->color_black,$this->color_white,$this->anchoImagen,$this->valor,$this->tamLetra);
					else
						$code_generated = new $this->arrFormatos[$this->formato]["nombreClase"]($this->altoImagen,$this->color_black,$this->color_white,$this->anchoImagen,$this->valor,$this->tamLetra,$this->comp1);
		
					$drawing->add_barcode($code_generated);
					$drawing->draw_all();
					$im = $drawing->get_im();
					$im2 = imagecreate($code_generated->lastX,$code_generated->lastY);
					imagecopyresized($im2, $im, 0, 0, 0, 0, $code_generated->lastX, $code_generated->lastY, $code_generated->lastX, $code_generated->lastY);
					$drawing->set_im($im2);
					
					switch($this->formatoImg)
					{
						case 1:
							header('Content: image/png');
						break;
						case 2:
							header('Content: image/jpg');
						break;
						
					}
					$drawing->finish($this->formatoImg);
				break;
			}
			
		}
	}
?>