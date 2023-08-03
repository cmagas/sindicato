<?php
include_once("latis/cExcel.php");
include_once("latis/zip.lib.php"); 
ini_set("memory_limit","512M");

$arrCeldasExcel=array();
$consultaExcel="SELECT idCelda,celda FROM 1019_catalogoCeldasExcel ORDER BY idCelda";
$resCelda=$con->obtenerFilas($consultaExcel);
while($fCelda=mysql_fetch_row($resCelda))
{
	$arrCeldasExcel[$fCelda[0]]=$fCelda[1];
}

class cLectorPerfilImportacion
{
	var $lector;
	var $objConfiguracion;
	var $claseObjeto;
	
	
	
	function cLectorPerfilImportacion($idPerfil,$archivo="")	
	{
		global $con;
		$arrPerfilesImportacion=array();
		$consulta="SELECT idPerfilConfiguracion,objConfiguracion,f.claseObjeto FROM 720_perfilesImportacion p,721_formatosImportacion f WHERE f.idFormato=p.formatoImportacion and idPerfilConfiguracion=".$idPerfil;
		$fila=$con->obtenerPrimeraFila($consulta);
		$this->objConfiguracion=json_decode(bD($fila[1]));
		$this->claseObjeto=$fila[2];
		eval('$this->lector=new '.$this->claseObjeto.'($this->objConfiguracion);');
		if($archivo!="")
			$this->lector->cargarArchivo($archivo);
		
	}
	
	function obtenerValor($nRegistro,$columna)
	{
		return $this->lector->obtenerValor($nRegistro,$columna);	
	}
	
	function obtenerLector()
	{
		return $this->lector;	
	}
	
}

class cLectorFormatoImportacion
{
	
	var $archivo="";
	var $numRegistros;
	var $objConfiguracion;
	var $arrRegistros;
	var $ultimaColumna;
	public function __construct($oConf)
	{
		$this->numRegistros=0;
		$this->objConfiguracion=$oConf;
		$this->arrRegistros=array();
	}	
	
	function cargarArchivo($rutaArchivo)
	{
		
	}
	
	function obtenerValor($nRegistro,$columna)
	{
		if(isset($this->arrRegistros[$nRegistro]))	
		{
			if(isset($this->arrRegistros[$nRegistro][$columna]))	
			{
				return	$this->arrRegistros[$nRegistro][$columna];
			}
		}
		return NULL;
	}
	
	
}

class cImportacionCSV extends cLectorFormatoImportacion
{
	var $libro;
	public function __construct($oConf)
	{
		
		parent::__construct($oConf);
	}	
	
	function cargarArchivo($rutaArchivo)
	{
		global $arrCeldasExcel;
		$this->archivo=$rutaArchivo;
		if(file_exists($rutaArchivo))
		{

			$objReader = PHPExcel_IOFactory::createReader("CSV");
			
			$separadorValores="";
			switch($this->objConfiguracion->separadorValores)
			{
				case "[n]":
					$separadorValores=PHP_EOL;
				break;	
				case "[t]":
					$separadorValores="\t";
				break;	
				case "[;]":
					$separadorValores=";";
				break;	
				case "[,]":
					$separadorValores=",";
				break;	
				case "[ ]":
					$separadorValores=" ";
				break;	
				default:
					$arrSeparador=explode("[@]",$this->objConfiguracion->separadorValores);
					$separadorValores=$arrSeparador[1];
				break;
			}
			
			$separadorLinea="";
			switch($this->objConfiguracion->separadorLinea)
			{
				case "[n]":
					$separadorLinea=PHP_EOL;
				break;	
				case "[t]":
					$separadorLinea="\t";
				break;	
				case "[;]":
					$separadorLinea=";";
				break;	
				case "[,]":
					$separadorLinea=",";
				break;	
				case "[ ]":
					$separadorLinea=" ";
				break;	
				default:
					$arrSeparador=explode("[@]",$this->objConfiguracion->separadorValores);
					$separadorValores=$arrSeparador[1];
				break;
			}
			
			
			$objReader->setDelimiter($separadorValores);
			$objReader->setEnclosure($this->objConfiguracion->caracterEncierro);
			$objReader->setLineEnding($separadorLinea);
			
			$objReader->setSheetIndex(0);
			$libro=$objReader->load($rutaArchivo);

			$this->libro=new cExcel("");
			$this->libro->libroExcel=$libro;
			$this->libro->hojaActiva=$libro->getActiveSheet();
			
			
			
				
			
		}
		else
			return false;
	
		if($this->libro)
		{
			$colInicial="A";
			$filaInicial="1";
			
			$colFinal=$colInicial;
			$encontrado=false;
			while(!$encontrado)
			{
				$valor=$this->libro->getValor($colFinal.$filaInicial);
				
				if($valor===NULL)
					$encontrado=true;
				else
					$colFinal=$this->libro->obtenerSiguienteColumna($colFinal);
					
			}
			$colFinal=$this->libro->obtenerAnteriorColumna($colFinal);
			
			
			
			
			$filaFinal=$filaInicial;
			$encontrado=false;
			while(!$encontrado)
			{
				$valor=$this->libro->getValor($colInicial.$filaFinal);
				if($valor===NULL)
					$encontrado=true;
				else
					$filaFinal++;
			}
			$filaFinal--;
			
			
			for($numReg=$filaInicial;$numReg<=$filaFinal;$numReg++)
			{
				$registro=array();
				$cInicial=$colInicial;
				$cFinal=$colFinal;
				$columnaArreglo="A";
				while($cInicial!=$cFinal)
				{
					$registro[$columnaArreglo]=$this->libro->getValor($cInicial.$numReg);
					$cInicial=$this->libro->obtenerSiguienteColumna($cInicial);
					$columnaArreglo=$this->libro->obtenerSiguienteColumna($cInicial);
				}
				$this->ultimaColumna=$this->libro->obtenerAnteriorColumna($columnaArreglo);
				$registro[$cInicial]=$this->libro->getValor($cInicial.$numReg);
				array_push($this->arrRegistros,$registro)	;
			}
			
		}
		
		$this->numRegistros=sizeof($this->arrRegistros);
		unset($this->libro);
		$this->libro=NULL;
		return true;
		
		
		
	}
	
	
	
}

class cImportacionTextoPlano extends cLectorFormatoImportacion
{
	var $fArchivo;
	
	public function __construct($oConf)
	{
		parent::__construct($oConf);
	}	
	
	function cargarArchivo($rutaArchivo)
	{

		global $arrCeldasExcel;
		$this->archivo=$rutaArchivo;
		$arrLineas=array();
		
		
		///Separador de Linea
		$arrSeparadores=array();
		$aSeparadorAux=explode("[@]",$this->objConfiguracion->separadorLinea);
		$aAux=explode("@,@",$aSeparadorAux[0]);
		
		foreach($aAux as $s)
		{
			$valor="";
			switch($s)
			{
				case "[n]":
					$valor=PHP_EOL;
				break;	
				case "[t]":
					$valor="\t";
				break;	
				case "[;]":
					$valor=";";
				break;	
				case "[,]":
					$valor=",";
				break;	
				case "[ ]":
					$valor=" ";
				break;	
				
			}
			array_push($arrSeparadores,$valor);
			
		}
		
		if(sizeof($aSeparadorAux)>1)
		{
			$aAux=explode("@,@",$aSeparadorAux[1]);	
			
			foreach($aAux as $s)
			{
				if($s!="")
					array_push($arrSeparadores,$s);
			}
		}
		
		//--//

		///Separador Valores
		$patronCampos="";
		$aSeparadorAux=explode("[@]",$this->objConfiguracion->separadorValores);
		$aAux=explode("@,@",$aSeparadorAux[0]);
		
		foreach($aAux as $s)
		{
			$valor="";
			switch($s)
			{
				case "[n]":
					$valor="\n";
				break;	
				case "[t]":
					$valor="\t";
				break;	
				case "[;]":
					$valor=";";
				break;	
				case "[,]":
					$valor=",";
				break;	
				case "[ ]":
					$valor="\s";
				break;	
				
			}
			if($patronCampos=="")
				$patronCampos=$valor;
			else
				$patronCampos.="|".$valor;
			
		}
		
		$arrCaracteresReemplazo[0]='|';
		$arrCaracteresReemplazo[1]='.';
		$arrCaracteresReemplazo[2]='$';
		
		if(sizeof($aSeparadorAux)>1)
		{
			$aAux=explode("@,@",$aSeparadorAux[1]);	
			
			foreach($aAux as $s)
			{
				if($s!="")
				{
					foreach($arrCaracteresReemplazo as $c)
					{
						if(strpos($s,$c)!==false)
						{
							$s=str_replace($c,'\\'.$c,$s)	;
						}
					}
					if($patronCampos=="")
						$patronCampos=$s;
					else
						$patronCampos.="|".$s;	
				}
			}
		}

		//--//
		
		$fArchivo=fopen($this->archivo,"r");	
		if($fArchivo)
		{
			$linea="";
			$c=fgetc($fArchivo);
			while(!feof($fArchivo))
			{
				
				if(existeValor($arrSeparadores,$c))
				{
					if(trim($linea)!="")
					{
						array_push($arrLineas,$linea);
						$linea="";
					}
				}	
				else
				{
					$linea.=$c;
				}
				$c=fgetc($fArchivo);
			}
			
			if(trim($linea)!="")
			{
				array_push($arrLineas,$linea);
			}
			
			
			$this->numRegistros=sizeof($arrLineas);
			
			
			$primeraFila=true;
			
			
			if($this->objConfiguracion->tipoSeparacionValores==1)
			{
				foreach($arrLineas as $l)
				{
					$registro=array();
					$arrCampos=array();
					$arrCampos=preg_split('['.$patronCampos.']i',$l);
					$numColumna=1;
					foreach($arrCampos as $c)
					{
						
						if($this->objConfiguracion->caracterEncierro!="")
						{
							
							if($c!="")	
							{
								if($c[0]==$this->objConfiguracion->caracterEncierro)
									$c=substr($c,1);
									
								if($c[strlen($c)-1]==$this->objConfiguracion->caracterEncierro)
									$c=substr($c,0,strlen($c)-1);
							}
							
						}
						$registro[$arrCeldasExcel[$numColumna]]=$c;
						
						
						$numColumna++;
					}
					$numColumna--;
					if($primeraFila)
					{
						$this->ultimaColumna=$arrCeldasExcel[$numColumna];
						$primeraFila=false;	
					}
					array_push($this->arrRegistros,$registro)	;
					
				}
			}
			else
			{
				$noUltimaColumna=(sizeof($this->objConfiguracion->arrLongitudes)-1);
				
				$numColumna=$this->objConfiguracion->arrLongitudes[$noUltimaColumna]->idColumna;
				$this->ultimaColumna=$arrCeldasExcel[$numColumna];
				foreach($arrLineas as $l)
				{
					$registro=array();
					$posLinea=0;
					foreach($this->objConfiguracion->arrLongitudes as $col)
					{
						$columna=$arrCeldasExcel[$col->idColumna];
						$valor="";
						
						
						if($posLinea<=strlen($l))
						{
							$valor=substr($l,$posLinea,$col->longitud);	
							$posLinea+=$col->longitud;
						}
						
						$registro[$columna]=$valor;
					}
					array_push($this->arrRegistros,$registro)	;
					
					
				}
			}
			
			for($pos=0;$pos<sizeof($this->arrRegistros);$pos++)
			{
				foreach($this->arrRegistros[$pos] as $col=>$valor)
				{
					if(!is_numeric($valor))
					{
						
						$this->arrRegistros[$pos][$col]=utf8_encode($valor);
					}
				}
			}
			
			unset($arrLineas);
			return true;
		}

		
		
		return false;
	}
	
	
}

class cImportacionExcel extends cLectorFormatoImportacion
{
	var $libro;
	
	public function __construct($oConf)
	{
		parent::__construct($oConf);
	}	
	
	function cargarArchivo($rutaArchivo)
	{
		global $arrCeldasExcel;
		$this->archivo=$rutaArchivo;
		if(file_exists($rutaArchivo))
		{
			$arrArchivos=explode(".",$rutaArchivo);
			switch(strtoupper($arrArchivos[1]))
			{
				case 'XLS':
					$this->libro=new cExcel($rutaArchivo,true,"Excel5");	
				break;	
				case 'XLSX':
					$this->libro=new cExcel($rutaArchivo,true,"Excel2007");	
				break;	
			}
		}
		else
			return false;
	
		if($this->libro)
		{
			$colInicial=$arrCeldasExcel[$this->objConfiguracion->columnaInicial];
			$filaInicial=$this->objConfiguracion->filaInicial;
			
			$colFinal="";
			
			if($this->objConfiguracion->columnaFinal!=0)
			{
				$colFinal=$arrCeldasExcel[$this->objConfiguracion->colFinal];
			}
			else
			{
				$colFinal=$colInicial;
				$encontrado=false;
				while(!$encontrado)
				{
					$valor=$this->libro->getValor($colFinal.$filaInicial);
					
					if($valor===NULL)
						$encontrado=true;
					else
						$colFinal=$this->libro->obtenerSiguienteColumna($colFinal);
						
				}
				$colFinal=$this->libro->obtenerAnteriorColumna($colFinal);
			}
			
			
			$filaFinal="";
			if($this->objConfiguracion->filaFinal!=0)
			{
				$filaFinal=$this->objConfiguracion->filaFinal;
			}
			else
			{
				$filaFinal=$filaInicial;
				$encontrado=false;
				while(!$encontrado)
				{
					$valor=$this->libro->getValor($colInicial.$filaFinal);
					if($valor===NULL)
						$encontrado=true;
					else
						$filaFinal++;
				}
				$filaFinal--;
			}
			
			for($numReg=$filaInicial;$numReg<=$filaFinal;$numReg++)
			{
				$registro=array();
				$cInicial=$colInicial;
				$cFinal=$colFinal;
				$columnaArreglo="A";
				while($cInicial!=$cFinal)
				{
					$registro[$columnaArreglo]=$this->libro->getValor($cInicial.$numReg);
					$cInicial=$this->libro->obtenerSiguienteColumna($cInicial);
					$columnaArreglo=$this->libro->obtenerSiguienteColumna($cInicial);
				}
				$this->ultimaColumna=$this->libro->obtenerAnteriorColumna($columnaArreglo);
				$registro[$cInicial]=$this->libro->getValor($cInicial.$numReg);
				array_push($this->arrRegistros,$registro)	;
			}
			
		}
		
		$this->numRegistros=sizeof($this->arrRegistros);
		unset($this->libro);
		$this->libro=NULL;
		return true;
	}
	
	
}

class cImportacionODS extends cLectorFormatoImportacion
{
	var $libro;
	
	public function __construct($oConf)
	{
		parent::__construct($oConf);
	}	
	
	function cargarArchivo($rutaArchivo)
	{
		global $arrCeldasExcel;
		$this->archivo=$rutaArchivo;
		if(file_exists($rutaArchivo))
		{
			$arrArchivos=explode(".",$rutaArchivo);
			$this->libro=new cExcel($rutaArchivo,true,"OOCalc");	
			
		}
		else
			return false;
	
		if($this->libro)
		{
			$colInicial=$arrCeldasExcel[$this->objConfiguracion->columnaInicial];
			$filaInicial=$this->objConfiguracion->filaInicial;
			
			$colFinal="";
			
			if($this->objConfiguracion->columnaFinal!=0)
			{
				$colFinal=$arrCeldasExcel[$this->objConfiguracion->colFinal];
			}
			else
			{
				$colFinal=$colInicial;
				$encontrado=false;
				while(!$encontrado)
				{
					$valor=$this->libro->getValor($colFinal.$filaInicial);
					
					if($valor===NULL)
						$encontrado=true;
					else
						$colFinal=$this->libro->obtenerSiguienteColumna($colFinal);
						
				}
				$colFinal=$this->libro->obtenerAnteriorColumna($colFinal);
			}
			
			
			$filaFinal="";
			if($this->objConfiguracion->filaFinal!=0)
			{
				$filaFinal=$this->objConfiguracion->filaFinal;
			}
			else
			{
				$filaFinal=$filaInicial;
				$encontrado=false;
				while(!$encontrado)
				{
					$valor=$this->libro->getValor($colInicial.$filaFinal);
					if($valor===NULL)
						$encontrado=true;
					else
						$filaFinal++;
				}
				$filaFinal--;
			}
			
			for($numReg=$filaInicial;$numReg<=$filaFinal;$numReg++)
			{
				$registro=array();
				$cInicial=$colInicial;
				$cFinal=$colFinal;
				$columnaArreglo="A";
				while($cInicial!=$cFinal)
				{
					$registro[$columnaArreglo]=$this->libro->getValor($cInicial.$numReg);
					$cInicial=$this->libro->obtenerSiguienteColumna($cInicial);
					$columnaArreglo=$this->libro->obtenerSiguienteColumna($cInicial);
				}
				$this->ultimaColumna=$this->libro->obtenerAnteriorColumna($columnaArreglo);
				$registro[$cInicial]=$this->libro->getValor($cInicial.$numReg);
				array_push($this->arrRegistros,$registro)	;
			}
			
		}
		
		$this->numRegistros=sizeof($this->arrRegistros);
		unset($this->libro);
		$this->libro=NULL;
		return true;
	}
	
	
}


?>