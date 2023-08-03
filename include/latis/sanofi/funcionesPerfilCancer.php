<?php
	
	
	function evaluarNivelRiesgo($idPerfil,$arrParams,$idPaciente=-1)
	{
		global $con;
		$arrResultado=NULL;
		$consulta="SELECT id__1045_gNivelesRiesgo,nivelRiesgo FROM _1045_gNivelesRiesgo WHERE idReferencia=".$idPerfil." ORDER BY prioridadIdentificacion";
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$resultado=cumpleNivelRiesgo($idPerfil,$fila[0],$arrParams,$idPaciente);
			//echo $fila[0]."_".$fila[1]."=".(($resultado==true)?'1':'0');
			if($resultado)
			{
				return $fila;	
			}
		}
		return $arrResultado;
	}
	
	
	function cumpleNivelRiesgo($idPerfil,$idNivel,$arrParams,$idPaciente=-1)
	{
		global $con;
		$consulta="SELECT idRegla FROM 3029_reglasNivelRiesgo WHERE idFormulario=1045 AND idReferencia=".$idPerfil." AND nivelRiesgo=".$idNivel;
		$reglas=$con->obtenerFilas($consulta);
		while($fRegla=mysql_fetch_row($reglas))
		{
			$cumpleRegla=true;
			$consulta="SELECT * FROM 3030_parametrosReglaNivelRiesgo WHERE idRegla=".$fRegla[0];
			$rParametros=$con->obtenerFilas($consulta);
			while($fParam=mysql_fetch_row($rParametros))
			{
				
				
				if((!isset($arrParams[$fParam[2]]))&&($fParam[3]!=3))
				{
					$cumpleRegla=false;	
					break;
				}
				$valor="";
				if($fParam[3]!=3)
					$valor=$arrParams[$fParam[2]];
				
				switch($fParam[3])
				{
					case 1:
						$condicion="'".$valor."'".$fParam[4]."'".$fParam[5]."'";
						$res=false;

						eval('$res=('.$condicion.");");
						
						if(!$res)
						{
							
							$cumpleRegla=false;
							break;
						}
						
						if($fParam[6]!="")
						{
							$condicion="'".$valor."'".$fParam[6]."'".$fParam[7]."'";
							eval('$res=('.$condicion.");");
							if(!$res)
							{
								$cumpleRegla=false;
								break;
							}
						}
						
					break;
					case 2:
						$arrValores=explode(",",$fParam[5]);
						if(!existeValor($arrValores,$valor))
						{
							
							$cumpleRegla=false;
							break;
						}
					break;
					case 3:
						$cache=NULL;
						$cadObj='{"idPaciente":"","idPerfil":"","idNivel":"","arrParams":""}';
						$obj=json_decode($cadObj);
						
						$obj->idPaciente=$idPaciente;
						$obj->idPerfil=$idPerfil;
						$obj->idNivel=$idNivel;
						$obj->arrParams=$arrParams;
						
						$resultado=trimComillas(resolverExpresionCalculoPHP($fParam[5],$obj,$cache));

						if(($resultado!=1)&&($resultado!=true))
						{
							
							$cumpleRegla=false;	
							break;
						}	
							
						
					break;	
				}
				
				
				
				
			}
			
			if($cumpleRegla)
				return true;

			
		}
		return false;
	}
	
	//Matriz de estudios
	function cumpleReglasEstudioTratamiento($idRegla,$idPerfil,$idNivel,$arrParams,$idPaciente=-1)
	{
		global $con;
		$cumpleRegla=true;
		$consulta="SELECT * FROM 3033_parametrosReglaNivelRiesgoTratamientosEstudios WHERE idRegla=".$idRegla;
		$rParametros=$con->obtenerFilas($consulta);
		while($fParam=mysql_fetch_row($rParametros))
		{
			
			
			if((!isset($arrParams[$fParam[2]]))&&($fParam[3]!=3))
			{
				$cumpleRegla=false;	
				break;
			}
			$valor="";
			if($fParam[3]!=3)
				$valor=$arrParams[$fParam[2]];
			
			switch($fParam[3])
			{
				case 1:
					$condicion="'".$valor."'".$fParam[4]."'".$fParam[5]."'";
					$res=false;
					
					eval('$res=('.$condicion.");");
					
					if(!$res)
					{
						
						$cumpleRegla=false;
						break;
					}
					
					if($fParam[6]!="")
					{
						$condicion="'".$valor."'".$fParam[6]."'".$fParam[7]."'";
						eval('$res=('.$condicion.");");
						if(!$res)
						{
							$cumpleRegla=false;
							break;
						}
					}
					
				break;
				case 2:
					$arrValores=explode(",",$fParam[5]);
					if(!existeValor($arrValores,$valor))
					{
						
						$cumpleRegla=false;
						break;
					}
				break;
				case 3:
					$cache=NULL;
					$cadObj='{"idRegla":"'.$idRegla.'","idPaciente":"","idPerfil":"","idNivel":"","arrParams":""}';
					$obj=json_decode($cadObj);
					
					$obj->idPaciente=$idPaciente;
					$obj->idPerfil=$idPerfil;
					$obj->idNivel=$idNivel;
					$obj->arrParams=$arrParams;
					
					$resultado=trimComillas(resolverExpresionCalculoPHP($fParam[5],$obj,$cache));

					if(($resultado!=1)&&($resultado!=true))
					{
						
						$cumpleRegla=false;	
						break;
					}	
						
					
				break;	
			}
			
			
			
			
		}

		return $cumpleRegla;
		
	}
	
	
	
	
	
?>