<?php
class coloresPlantillas
{
	function cambiarColorMain($colorSuperior,$colorInf)
	{
		$cSuperior=$this->descompenerHexToDec($colorSuperior);
		$cInferior=$this->descompenerHexToDec($colorInf);
		$imagen=imagecreatefromgif('../images/main2.gif');
		$this->cambiarColorSuperiorMainHayasGif($imagen,$cSuperior[0],$cSuperior[1],$cSuperior[2]);
		$this->cambiarColorInferiorMainHayasGif($imagen,$cInferior[0],$cInferior[1],$cInferior[2]);
		imagegif($imagen,'../images/main.gif');
	}
	
	function cambiarColorSuperiorMainHayasGif($imagen,$r,$g,$b)
	{
		$minX=0;
		$minY=0;
		$indice=imagecolorat($imagen, $minX, $minY);
		imagecolorset($imagen,$indice,$r,$g,$b);
	}
	
	function cambiarColorInferiorMainHayasGif($imagen,$r,$g,$b)
	{
		$maxX=49;
		$maxY=224;
		$indice=imagecolorat($imagen, $maxX, $maxY);
		imagecolorset($imagen,$indice,$r,$g,$b);
	}
	  
	function descomponerHexadecimal($valorHex)
	{
	  $arrColor[0]=substr($valorHex,0,2);
	  $arrColor[1]=substr($valorHex,2,2);
	  $arrColor[2]=substr($valorHex,4,2);
	  return $arrColor;
	}
	
	function descompenerHexToDec($valorHex)
	{
	  $arrHex=$this->descomponerHexadecimal($valorHex);
	  $arrDec[0]=hexdec($arrHex[0]);
	  $arrDec[1]=hexdec($arrHex[1]);
	  $arrDec[2]=hexdec($arrHex[2]);
	  return $arrDec;
	}
}
?>