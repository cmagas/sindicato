<?php include_once("latis/funcionesEnvioMensajes.php");
include_once("latis/conectoresAccesoDatos/administradorConexiones.php");
include_once("latis/cLectorFormatoImportacion.php"); 
include_once("latis/funcionesNominaModeloLita.php"); 

ini_set('memory_limit', '2048M');

$estadisticasCalculo=array();

function resolverExpresionCalculoPHP($idConsulta,$objParametros,&$cacheCalculos)
{
	global $con;
	global $estadisticasCalculo;

	$reflectionClase = new ReflectionObject($objParametros);
	
	$objValores=array();
	$reconstruirObjParam=false;
	foreach ($reflectionClase->getProperties() as $property => $value) 
	{

		$vParam="";
		$valorParam=$value->getValue($objParametros);
		$vParam=$valorParam;
		if(gettype($valorParam)=="object")
		{
			$reconstruirObjParam=true;
			$arrParam=array();
			$reflectionAux = new ReflectionObject($valorParam);
			foreach ($reflectionAux->getProperties() as $propertyAux => $valueAux) 
			{
				$arrParam[$valueAux->getName()]=$valueAux->getValue($valorParam);
			}
			
			$vParam=$arrParam;
			
			
		}
		$objValores[$value->getName()]=$vParam;
	}
	if($reconstruirObjParam)
	{
		$cadTmp="";
		foreach($objValores as $campo=>$valor)
		{
			if($cadTmp=="")
				$cadTmp='"'.$campo.'":""';
			else
				$cadTmp.=',"'.$campo.'":""';
		}
		$cadTmp='{'.$cadTmp.'}';
		$objTmp=json_decode($cadTmp);
		foreach($objValores as $campo=>$valor)
		{
			eval('$objTmp->'.$campo.'=$objValores[$campo];');
		}
		$objParametros=$objTmp;
	}
	
	$arrQueries=array();
	$modoDebug=false;

	if(isset($_SESSION["debuggerCalculos"])&&($_SESSION["debuggerCalculos"]==1))
		$modoDebug=true;

	

	$mInicio=microtime();
	$arrTime=explode(" ",$mInicio);
	$arrMili=explode(".",$arrTime[0]);
	$decIni=$arrTime[1].".".$arrMili[1];
	$tInicial=date("H:i:s",$arrTime[1]).".".$arrMili[1];
	$tFinal=NULL;

	if($cacheCalculos!==NULL)
	{

		if(isset($cacheCalculos[$idConsulta."_".$objParametros->marcaParametro]))
		{
			$mInicio=microtime();
			$arrTime=explode(" ",$mInicio);
			$arrMili=explode(".",$arrTime[0]);
			$decFinal=$arrTime[1].".".$arrMili[1];
			$tFinal=date("H:i:s",$arrTime[1]).".".$arrMili[1];
			$diferencia=$decFinal-$decIni;
			if($modoDebug)
			{
				if(isset($estadisticasCalculo[$idConsulta]))
					$estadisticasCalculo[$idConsulta]+=$diferencia;
				else
					$estadisticasCalculo[$idConsulta]=$diferencia;
				$consulta="SELECT nombreConsulta FROM 991_consultasSql WHERE idConsulta=".$idConsulta;
				$nCalculo=$con->obtenerValor($consulta);
				echo "<span  class='corpo8_bold'>idConsulta: ".$idConsulta.", calculo: ".$nCalculo." (De Caché)<br> Tiempo de ejecucion: <font color='red'>".exp_to_dec($diferencia)."</font>";
				echo '<br><font color="Red"><b>Resultado:</b>&nbsp;&nbsp;</font><span id="letraRojaSubrayada8">'.$cacheCalculos[$idConsulta."_".$objParametros->marcaParametro].'</span><br><br>';	
			}
			
			$arrResultado=array();
			$arrResultado["cache"]=1;
			if(gettype($cacheCalculos[$idConsulta."_".$objParametros->marcaParametro])=='array')
			{
				
				foreach($cacheCalculos[$idConsulta."_".$objParametros->marcaParametro] as $llave=>$valor)
				{
					$arrResultado[$llave]=$valor;
				}
				
				
			}
			else
			{
				$arrResultado["valorCalculado"]=$cacheCalculos[$idConsulta."_".$objParametros->marcaParametro];

			}
			return $arrResultado;
		}
		
	}
	
	$resultadoFinal="";
	$cuerpo='';
	
	$cuerpo=generarExpresionCalculoPHP($idConsulta,$objParametros,$cacheCalculo,$arrQueries);

	$cuerpo=normalizarEspacios($cuerpo);
	$cuerpo=str_replace("$$","$",$cuerpo);
	$cuerpo=str_replace("; /","/",$cuerpo);
	$cuerpo=str_replace("; +","+",$cuerpo);
	$cuerpo=str_replace("; -","-",$cuerpo);
	$cuerpo=str_replace("; *","*",$cuerpo);
	$cuerpo=str_replace("; )",")",$cuerpo);
	$cuerpo=str_replace("{ ;","{",$cuerpo);
	$cuerpo=str_replace("; ;",";",$cuerpo);
	$cuerpo=str_replace("' }","'; }",$cuerpo);
	$cuerpo=str_replace("'; <'","'<'",$cuerpo);
	$cuerpo=str_replace("'; >'","'>'",$cuerpo);
	$cuerpo=str_replace("'; ='","'='",$cuerpo);
	$cuerpo=str_replace("> =",">=",$cuerpo);
	$cuerpo=str_replace("< =","<=",$cuerpo);
	$cuerpo=str_replace("< =","<=",$cuerpo);
	$cuerpo=str_replace("! =","!=",$cuerpo);	
	$cuerpo=str_replace("'.' +",".",$cuerpo);
	$cuerpo=str_replace("== '","== ''",$cuerpo);
	$cuerpo=str_replace("== '","=='",$cuerpo);
	$cuerpo=str_replace("''","'",$cuerpo);
	$cuerpo=str_replace("= ' ;","= '' ;",$cuerpo);
	$cuerpo=str_replace("= ' )","= '' )",$cuerpo);
	$cuerpo=str_replace(" + +","++",$cuerpo);
	$cuerpo=str_replace("+ =","+=",$cuerpo);
	$cuerpo=str_replace("- =","-=",$cuerpo);
	$cuerpo=str_replace("* =","*=",$cuerpo);
	$cuerpo=str_replace("/ =","/=",$cuerpo);
	$cuerpo=str_replace(") ; {","){",$cuerpo);
	$cuerpo=str_replace("; ;",";",$cuerpo);
	$cuerpo=str_replace("( (","((",$cuerpo);
	$cuerpo=str_replace("' =","'=",$cuerpo);
	$cuerpo=str_replace(") |",")|",$cuerpo);
	$cuerpo=str_replace("| (","|(",$cuerpo);
	$cuerpo=str_replace("( '","('",$cuerpo);
	$cuerpo=str_replace("' )","')",$cuerpo);
	
	
	/*if($idConsulta==66)
		$modoDebug=true;*/
	
	if($modoDebug)
	{
		
		$consulta="SELECT nombreConsulta FROM 991_consultasSql WHERE idConsulta=".$idConsulta;
		$nCalculo=$con->obtenerValor($consulta);
		echo "<br><br><span  class='corpo8_bold'>idConsulta: ".$idConsulta.", calculo: ".$nCalculo."<br>";
		echo "".$cuerpo."</span><br><br>";
	}

	eval($cuerpo);
	$mInicio=microtime();
	$arrTime=explode(" ",$mInicio);
	$arrMili=explode(".",$arrTime[0]);
	$decFinal=$arrTime[1].".".$arrMili[1];
	$tFinal=date("H:i:s",$arrTime[1]).".".$arrMili[1];
	$diferencia=$decFinal-$decIni;
	if($modoDebug)
	{
		if(isset($estadisticasCalculo[$idConsulta]))
			$estadisticasCalculo[$idConsulta]+=$diferencia;
		else
			$estadisticasCalculo[$idConsulta]=$diferencia;
		
		echo "<span  class='corpo8_bold'>idConsulta: ".$idConsulta.", calculo: ".$nCalculo."<br> Tiempo de ejecucion: <font color='red'>".exp_to_dec($diferencia)."</font>";
		echo '<br><font color="Red"><b>Resultado:</b>&nbsp;&nbsp;</font><span id="letraRojaSubrayada8">'.$resultadoFinal.'</span><br><br>';	
	}
	
	if($cacheCalculos!==NULL)
	{
		$cacheCalculos[$idConsulta."_".$objParametros->marcaParametro]=$resultadoFinal;
	}
	
	if(is_numeric($resultadoFinal)||is_array($resultadoFinal)||is_object($resultadoFinal)|| is_resource($resultadoFinal))
		return $resultadoFinal;
	$resultadoFinal= "'".$resultadoFinal."'";
	if($resultadoFinal!="''")
		$resultadoFinal=str_replace("''","'",$resultadoFinal);
	return $resultadoFinal;
}

function generarExpresionCalculoPHP($idConsulta,$objParametros,&$cacheCalculo,&$arrQueries)
{
	global $con;
	$cad='{"p17":[]}';
	if($idConsulta=="")
		$idConsulta=-1;
	$objParam=json_decode($cad);
	$objParam->p17=$objParametros;
	$arrQueries=resolverQueries($idConsulta,3,$objParam,true);
	$consulta="select * from 992_tokensConsulta where idConsulta=".$idConsulta." order by idToken";
	$resQuery=$con->obtenerFilas($consulta);
	$cad='';	
	$arrVariables=array();
	$consulta="SELECT idToken FROM 992_tokensConsulta WHERE idConsulta=".$idConsulta." AND tipoToken=21 AND comp1!=0 AND comp1!='' order by idToken";
	
	$resTokens=$con->obtenerFilas($consulta);
	
	while($fToken=mysql_fetch_row($resTokens))
	{
		$cad.=inicializarVariableEstructura($fToken[0],$arrVariables);
	}
	while($fila=mysql_fetch_row($resQuery))
	{
		
		$valor="";
		if($fila[3]<0)
		{
			$idQuery=($fila[3]*-1);
		
			switch($idQuery)
			{
				case "1":
					$valor="'".date("Y-m-d")."'";
				break;
				case "2":
					$valor=$_SESSION["idUsr"];
				break;
				case "3":
					if(gettype($objParametros->objDatosUsr)=="object")
						$valor=$objParametros->objDatosUsr->idUsuario;
					else
						$valor=$objParametros->objDatosUsr["idUsuario"];
				break;
				case "4":
					if(gettype($objParametros->objDatosUsr)=="object")
						$valor="'".$objParametros->objDatosUsr->fechaContratacion."'";
					else
						$valor="'".$objParametros->objDatosUsr["fechaContratacion"]."'";
				break;
				case "5":
					if(gettype($objParametros->objDatosUsr)=="object")
						$valor=$objParametros->objDatosUsr->sueldoBase;
					else
						$valor=$objParametros->objDatosUsr["sueldoBase"];
				break;
				case "6":
					if(gettype($objParametros->objDatosUsr)=="object")
						$valor=$objParametros->objDatosUsr->nFaltas;
					else
						$valor=$objParametros->objDatosUsr["nFaltas"];
				break;
				case "7":
					if(gettype($objParametros->objDatosUsr)=="object")
						$valor=$objParametros->objDatosUsr->nRetardos;
					else
						$valor=$objParametros->objDatosUsr["nRetardos"];
				break;
				case "8":
					if(gettype($objParametros->objDatosUsr)=="object")
						$valor=$objParametros->objDatosUsr->ciclo;
					else
						$valor=$objParametros->objDatosUsr["ciclo"];
				break;
				case "9":
					if(gettype($objParametros->objDatosUsr)=="object")
						$valor="'".$objParametros->objDatosUsr->quincena."'";
					else
						$valor="'".$objParametros->objDatosUsr["quincena"]."'";
				break;
				case "10":
					if(gettype($objParametros->objDatosUsr)=="object")
						$valor="'".$objParametros->objDatosUsr->fechaBaja."'";
					else
						$valor="'".$objParametros->objDatosUsr["fechaBaja"]."'";
				break;
				case "11":
					if(gettype($objParametros->objDatosUsr)=="object")
						$valor="'".$objParametros->objDatosUsr->puesto."'";
					else
						$valor="'".$objParametros->objDatosUsr["puesto"]."'";
				break;
				case "12":
					if(gettype($objParametros->objDatosUsr)=="object")
						$valor="'".$objParametros->objDatosUsr->fechaBasificacion."'";
					else
						$valor="'".$objParametros->objDatosUsr["fechaBasificacion"]."'";
				break;
				case "13":
					if(gettype($objParametros->objDatosUsr)=="object")
						$valor="'".$objParametros->objDatosUsr->horasTrabajador."'";
					else
						$valor="'".$objParametros->objDatosUsr["horasTrabajador"]."'";
				break;
				case "14":
					if(gettype($objParametros->objDatosUsr)=="object")
						$valor="'".$objParametros->objDatosUsr->departamento."'";
					else
						$valor="'".$objParametros->objDatosUsr["departamento"]."'";
				break;
				case "15":
					if(gettype($objParametros->objDatosUsr)=="object")
						$valor="'".$objParametros->objDatosUsr->institucion."'";
					else
						$valor="'".$objParametros->objDatosUsr["institucion"]."'";
				break;
				case "16":
					if(gettype($objParametros->objDatosUsr)=="object")
						$valor="'".$objParametros->objDatosUsr->tipoContratacion."'";
					else
						$valor="'".$objParametros->objDatosUsr["tipoContratacion"]."'";
				break;
				case "17":
					if(gettype($objParametros->objDatosUsr)=="object")
						$valor="'".$objParametros->objDatosUsr->idNomina."'";
					else
						$valor="'".$objParametros->objDatosUsr["idNomina"]."'";
				break;
				case "18"://Id Perfil Entidad Agrupadora
					if(gettype($objParametros->objDatosUsr)=="object")
						$valor="'".$objParametros->objDatosUsr->idEntidadAgrupacion."'";
					else
						$valor="'".$objParametros->objDatosUsr["idEntidadAgrupacion"]."'";
				break;
				case "19"://ID Entidad Agrupadora
					if(gettype($objParametros->objDatosUsr)=="object")
						$valor="'".$objParametros->objDatosUsr->idUnidadAgrupadora."'";
					else
						$valor="'".$objParametros->objDatosUsr["idUnidadAgrupadora"]."'";
				break;
				case "20"://ID Descripcion Complementario usuario proceso
					if(gettype($objParametros->objDatosUsr)=="object")
						$valor="'".$objParametros->objDatosUsr->idIdentificadorComplementario."'";
					else
						$valor="'".$objParametros->objDatosUsr["idIdentificadorComplementario"]."'";
				break;
				case "21"://Fecha de nomina
					if(gettype($objParametros->objDatosUsr)=="object")
						$valor="'".$objParametros->objDatosUsr->fechaBaseNomina."'";
					else
						$valor="'".$objParametros->objDatosUsr["fechaBaseNomina"]."'";
				break;
				case "22"://Id Zona
					if(gettype($objParametros->objDatosUsr)=="object")
						$valor="'".$objParametros->objDatosUsr->idZona."'";
					else
						$valor="'".$objParametros->objDatosUsr["idZona"]."'";
				break;
				case "23"://Obj Datos Usuario
					$valor="unserialize('".serialize($objParametros->objDatosUsr)."');";
					
				break;
				case "24"://Obj Calculo Asociado
					
					$valor="unserialize('".serialize($objParametros->objCalculoAsociado)."');";
					
				break;
				case "25"://Clasifiacion Puesto
					
					if(gettype($objParametros->objDatosUsr)=="object")
						$valor="'".$objParametros->objDatosUsr->clasificacionPuesto."'";
					else
						$valor="'".$objParametros->objDatosUsr["clasificacionPuesto"]."'";
					
				break;
				case "26"://SMG
					
					if(gettype($objParametros->objDatosUsr)=="object")
						$valor="'".$objParametros->objDatosUsr->SMG."'";
					else
						$valor="'".$objParametros->objDatosUsr["SMG"]."'";
					
				break;
				
				case "27"://Genero
					
					if(gettype($objParametros->objDatosUsr)=="object")
						$valor="'".$objParametros->objDatosUsr->genero."'";
					else
						$valor="'".$objParametros->objDatosUsr["genero"]."'";
					
				break;
				
				default:
					
					$consulta="SELECT idParametro,parametro FROM 993_parametrosConsulta WHERE idConsulta=".$idQuery;
					
					$resParam=$con->obtenerFilas($consulta);
					$arrParam="";
					$marcaParam="";
					while($fParam=mysql_fetch_row($resParam))
					{
						
						$consulta="SELECT tipoValor,valor FROM 994_valoresTokens WHERE idToken=".$fila[0]." AND idParametro=".$fParam[0];
						
						$fToken=$con->obtenerPrimeraFila($consulta);
						if($fToken[0]!=21)
						{
							$vParam=resolverValoresTokens($fToken[0],$fToken[1],$arrQueries,$objParametros);
							$oParam='"p'.$fParam[1].'":"'.str_replace("'","",$vParam).'"';
						}
						else
						{
							$vParam='$'.$fToken[1];
							$oParam='"p'.$fParam[1].'":"\'.'.$vParam.'.\'"';
						}
						if($marcaParam=="")
						{
							$marcaParam=str_replace("'","",str_replace('$',"_",str_replace(".","_",$vParam)));

						}
						else
						{
							$marcaParam.="_".str_replace("'","",str_replace('$',"_",str_replace(".","_",$vParam)));
						}
						if($arrParam=="")
							$arrParam=$oParam;
						else
							$arrParam.=",".$oParam;
					}
					
					if(isset($objParametros->objDatosUsr))
					{
						$idUsuario="";
						$puesto="";
						$fechaBaja="";
						$fechaBasificacion="";	
						$horasTrabajador="";
						$institucion="";
						$fechaContratacion="";	
						$sueldoBase="";	
						$nFaltas="";
						$nRetardos="";	
						$totalDeducciones="";	
						$totalPercepciones="";	
						$sueldoNeto="";
						$departamento="";
						$ciclo="";	
						$quincena="";	
						$tipoContratacion="";
						$fechaIniIncidencia="";
						$fechaFinIncidencia="";	
						$idZona="";
						$situacion="";
						$tipoPago="";	
						$idNomina="";
						$idEntidadAgrupacion="";
						$idUnidadAgrupadora="";
						$idIdentificadorComplementario="";
						if(gettype($objParametros->objDatosUsr)=="object")
						{
							$idUsuario=$objParametros->objDatosUsr->idUsuario;
							$puesto=$objParametros->objDatosUsr->puesto;
							$fechaBaja=$objParametros->objDatosUsr->fechaBaja;
							$fechaBasificacion=$objParametros->objDatosUsr->fechaBasificacion;
							$horasTrabajador=$objParametros->objDatosUsr->horasTrabajador;
							$institucion=$objParametros->objDatosUsr->institucion;
							$fechaContratacion=$objParametros->objDatosUsr->fechaContratacion;
							$sueldoBase=$objParametros->objDatosUsr->sueldoBase;
							$nFaltas=$objParametros->objDatosUsr->nFaltas;
							$nRetardos=$objParametros->objDatosUsr->nRetardos;
							$totalDeducciones=$objParametros->objDatosUsr->totalDeducciones;
							$totalPercepciones=$objParametros->objDatosUsr->totalPercepciones;
							$sueldoNeto=$objParametros->objDatosUsr->sueldoNeto;
							$departamento=$objParametros->objDatosUsr->departamento;
							$ciclo=$objParametros->objDatosUsr->ciclo;
							$quincena=$objParametros->objDatosUsr->quincena;
							$tipoContratacion=$objParametros->objDatosUsr->tipoContratacion;
							$fechaIniIncidencia=$objParametros->objDatosUsr->fechaIniIncidencia;
							$fechaFinIncidencia=$objParametros->objDatosUsr->fechaFinIncidencia;
							$idZona=$objParametros->objDatosUsr->idZona;
							$situacion=$objParametros->objDatosUsr->situacion;
							$tipoPago=$objParametros->objDatosUsr->tipoPago;
							$idNomina=$objParametros->objDatosUsr->idNomina;
							$idEntidadAgrupacion=$objParametros->objDatosUsr->idEntidadAgrupacion;
							$idUnidadAgrupadora=$objParametros->objDatosUsr->idUnidadAgrupadora;
							$idIdentificadorComplementario=$objParametros->objDatosUsr->idIdentificadorComplementario;
						}
						else
						{
							$idUsuario=$objParametros->objDatosUsr["idUsuario"];
							$puesto=$objParametros->objDatosUsr["puesto"];
							$fechaBaja=$objParametros->objDatosUsr["fechaBaja"];
							$fechaBasificacion=$objParametros->objDatosUsr["fechaBasificacion"];
							$horasTrabajador=$objParametros->objDatosUsr["horasTrabajador"];
							$institucion=$objParametros->objDatosUsr["institucion"];
							$fechaContratacion=$objParametros->objDatosUsr["fechaContratacion"];
							$sueldoBase=$objParametros->objDatosUsr["sueldoBase"];
							$nFaltas=$objParametros->objDatosUsr["nFaltas"];
							$nRetardos=$objParametros->objDatosUsr["nRetardos"];
							$totalDeducciones=$objParametros->objDatosUsr["totalDeducciones"];
							$totalPercepciones=$objParametros->objDatosUsr["totalPercepciones"];
							$sueldoNeto=$objParametros->objDatosUsr["sueldoNeto"];
							$departamento=$objParametros->objDatosUsr["departamento"];
							$ciclo=$objParametros->objDatosUsr["ciclo"];
							$quincena=$objParametros->objDatosUsr["quincena"];
							$tipoContratacion=$objParametros->objDatosUsr["tipoContratacion"];
							$fechaIniIncidencia=$objParametros->objDatosUsr["fechaIniIncidencia"];
							$fechaFinIncidencia=$objParametros->objDatosUsr["fechaFinIncidencia"];
							$idZona=$objParametros->objDatosUsr["idZona"];
							$situacion=$objParametros->objDatosUsr["situacion"];
							$tipoPago=$objParametros->objDatosUsr["tipoPago"];
							$idNomina=$objParametros->objDatosUsr["idNomina"];
							$idEntidadAgrupacion=$objParametros->objDatosUsr["idEntidadAgrupacion"];
							$idUnidadAgrupadora=$objParametros->objDatosUsr["idUnidadAgrupadora"];
							$idIdentificadorComplementario=$objParametros->objDatosUsr["idIdentificadorComplementario"];
						}
							
						$objDatos='	{
										"idUsuario":"'.str_replace("'","",$idUsuario).'",
										"puesto":"'.str_replace("'","",$puesto).'",
										"fechaBaja":"'.str_replace("'","",$fechaBaja).'",
										"fechaBasificacion":"'.str_replace("'","",$fechaBasificacion).'",
										"horasTrabajador":"'.str_replace("'","",$horasTrabajador).'",
										"institucion":"'.str_replace("'","",$institucion).'",
										"fechaContratacion":"'.str_replace("'","",$fechaContratacion).'",
										"sueldoBase":"'.str_replace("'","",$sueldoBase).'",
										"nFaltas":"'.str_replace("'","",$nFaltas).'",
										"nRetardos":"'.str_replace("'","",$nRetardos).'",
										"totalDeducciones":"'.str_replace("'","",$totalDeducciones).'",
										"totalPercepciones":"'.str_replace("'","",$totalPercepciones).'",
										"sueldoNeto":"'.str_replace("'","",$sueldoNeto).'",
										"departamento":"'.str_replace("'","",$departamento).'",
										"ciclo":"'.$ciclo.'",
										"quincena":"'.$quincena.'",
										"tipoContratacion":"'.$tipoContratacion.'",
										"fechaIniIncidencia":"'.$fechaIniIncidencia.'",
										"fechaFinIncidencia":"'.$fechaFinIncidencia.'",
										"idZona":"'.$idZona.'",
										"situacion":"'.$situacion.'",
										"tipoPago":"'.$tipoPago.'",
										"idNomina":"'.$idNomina.'",
										"idEntidadAgrupacion":"'.$idEntidadAgrupacion.'",
										"idUnidadAgrupadora":"'.$idUnidadAgrupadora.'",
										"idIdentificadorComplementario":"'.$idIdentificadorComplementario.'"
									}';
					
						if($arrParam=="")
							$arrParam='{"objDatosUsr":'.$objDatos.',"marcaParametro":"'.$marcaParam.'"}';
						else
							$arrParam='{"objDatosUsr":'.$objDatos.','.$arrParam.',"marcaParametro":"'.$marcaParam.'"}';
						
					}
					else
					{
						$reflectionClase = new ReflectionObject($objParametros);
						$objParam="";
						foreach ($reflectionClase->getProperties() as $property => $value) 
						{
							$nombre=$value->getName();
							$valor=$value->getValue($objParametros);
							$obj='"'.$nombre.'":"'.$valor.'"';
							if($objParam=="")
								$objParam=$obj;
							else
								$objParam.=",".$obj;
						}
						if($objParam!="")
						{
							if($arrParam!="")
								$arrParam.=",".$objParam;
							else
								$arrParam=$objParam;
						}
						$arrParam="{".$arrParam."}";
					}
					
					$valor='resolverExpresionCalculoPHP('.$idQuery.',json_decode(\''.$arrParam.'\'),$cacheCalculos);';
				break;
			}
		}
		else
		{
			if($fila[2]!="<br />")
			{
				$valor=resolverValoresTokens($fila[3],$fila[1],$arrQueries,$objParametros);

				
				
			}
			else
				$valor=';';
		}
		$cad.=$valor." ";
	}
	$cad=str_replace("; ;",";",$cad);
	$cad=str_replace(";;",";",$cad);
	$cad=str_replace(";  ;",";",$cad);
	$cad=str_replace("= =","==",$cad);
	$cad=str_replace("''","'",$cad);
	$cad=str_replace("' '","''",$cad);
	
	$cad=str_replace("'_'","''",$cad);
	$cad=str_replace("'__'","'_'",$cad);
	$cad=str_replace("'; <'","'<'",$cad);
	$cad=str_replace("'; >'","'>'",$cad);
	$cad=str_replace("'; ='","'='",$cad);
	$ultimoCaracter = substr ($cad, strlen($cad)-2, strlen($cad) - 1);
	if($ultimoCaracter!=";")
		$cad.=";";
	return $cad;
}

function resolverValoresTokens($tipoToken,$valorToken,$arrQueries,$objParametros)
{
	global $con;
	$valor="NULL";
	
	switch($tipoToken)
	{
		case "0":  //Operadores y variables acumuladoras
			$valor=$valorToken;
		break;
		case "21":
			//$valor="$".$valorToken;
			$valor=$valorToken;
		break;	
		case "1":  //Valor constante
			$valor=codificarValor($valorToken);
			
		break;
		case 3:	//Valor de sesion
				$consulta="select valorSesion,valorReemplazo from 8003_valoresSesion where idValorSesion=".$valorToken;
				$filaSesion=$con->obtenerPrimeraFila($consulta);
				
				$valor="'".$_SESSION[$filaSesion[0]]."'";
				
			break;
		case 4:	//Valor de sistema
				$datosConsulta=explode("|",$valorToken);
				$valorSistema="";
				switch($valorToken)
				{
					case "8":
						$valor="'".date("Y-m-d")."'";
					break;
					case "9":
						$valor="'".date("H:i")."'";
					break;
				}
		break;
		case 5: //Parametros registrados
		case 6:
			
			eval('
					if(isset($objParametros->p'.$valorToken.'))
						$valParam=$objParametros->p'.$valorToken.';
					else
					{
						$valParam=$objParametros->'.$valorToken.';
					}
				');
			
			

			
			if((gettype($valParam)!="object")&&(gettype($valParam)!="array"))
				$valor=codificarValor($valParam);
			else
				$valor="unserialize(bD('".bE(serialize($valParam))."'))";
			
			
			//var_dump(eval($valor));
		break;
		case 7: //valor consulta auxilizar
			$datosConsulta=explode("|",$valorToken);
			if($arrQueries[$datosConsulta[0]]["ejecutado"]==1)
			{
				if($arrQueries[$datosConsulta[0]]["resultado"]!="")
				{
					
					$valor=$arrQueries[$datosConsulta[0]]["resultado"];
					$arrReg=explode("','",$valor);
					if(sizeof($arrReg)>1)
						$valor='"'.$valor.'"';
				}
				else
					$valor="'_'";
			}
			else
				$valor="'_'";
		break;
		case 11: //valor almacen 
			$datosConsulta=explode("|",$valorToken);
			
			if($arrQueries[$datosConsulta[0]]["ejecutado"]==1)
			{
				$resultado=$arrQueries[$datosConsulta[0]]["resultado"];
				
				if(gettype ($arrQueries[$datosConsulta[0]]["resultado"])=="resource")
				{
					$conAux=$arrQueries[$datosConsulta[0]]["conector"];
					$conAux->inicializarRecurso($resultado);
					$filaRes=$conAux->obtenerSiguienteFilaAsoc($resultado);
					if(!$filaRes)
						$valor="'_'";
					else
					{
						$cNormalizado=str_replace(".","_",$datosConsulta[1]);
						if($filaRes[$cNormalizado]!='')		
							$valor=codificarValor($filaRes[$cNormalizado]);
						else
							$valor="'_'";
					}
				}
				else
				{
					if(($resultado!="")&&($resultado!="''"))
					{
						$arrReg=explode("','",$resultado);
						if(sizeof($arrReg)==1)
							$valor=$resultado;
						else
							$valor='"'.$resultado.'"';
					}
					else
						$valor="'_'";	
				}
			}
			else
				$valor="'_'";
			
		break;
		case 17:
			$valParam="";

			if(gettype($objParametros)=='object')
			{
				
				
				eval('	if(isset($objParametros->'.$valorToken.')) 
							$valParam=$objParametros->'.$valorToken.';
						else
							if(isset($objParametros->p'.$valorToken.'))
								$valParam=$objParametros->p'.$valorToken.';');
			
				
			}
			else
			{
				
				if(isset($objParametros[$valorToken])) 
					$valParam=$objParametros[$valorToken];
			}
						
					
			
			$valor=codificarValor($valParam);
			if($valor=="")
			{
				$valor="''";
			}
			

		break;
		case 22: //Invocacion a funcion
			
			$valorToken=str_replace('["','[\"',$valorToken);
			$valorToken=str_replace('"]','\"]',$valorToken);
			$objFuncion=json_decode($valorToken);
			$arrParametros="";
			$evaluar=true;
			$nFuncion="";
			$procesamientoUnico=false;
			if(isset($objFuncion->parametros))
			{
				foreach($objFuncion->parametros as $param)
				{
					
					if($param->tipoValor==21)
					{
						if($arrParametros=="")
							$arrParametros="$".$param->valorSistema;	
						else
							$arrParametros.=",$".$param->valorSistema;	
						$evaluar=false;
						//echo $arrParametros;
					}
					else
					{
						
						$oParametro=resolverValoresTokens($param->tipoValor,$param->valorSistema,$arrQueries,$objParametros);
						
						if($arrParametros=="")
							$arrParametros=$oParametro;	
						else
							$arrParametros.=",".$oParametro;	
					}
				}
			}
			else
			{
				
				$evaluar=false;
			}
			

			$consulta="SELECT nombreFuncionPHP,procesamientoUnico FROM 9033_funcionesSistema WHERE idFuncion=".$objFuncion->idFuncion;
			$fDatosFuncion=$con->obtenerPrimeraFila($consulta);
			$nFuncion=$fDatosFuncion[0];
			$procesamientoUnico=$fDatosFuncion[1]==1;
			if($evaluar)
			{

				eval ('$valor='.$nFuncion.'('.$arrParametros.');');

				if((gettype($valor)=='object')||(gettype($valor)=='array'))
				{
					if(!$procesamientoUnico)
						$valor=$nFuncion.'('.$arrParametros.');';
					else
						$valor='unserialize(\''.serialize($valor).'\');';

				}
				
			}
			else
			{
				
				$valor=$nFuncion.'('.$arrParametros.');';
				
			}
		break;
		case 25:
			$oToken=json_decode($valorToken);
			/*if($arrQueries[$oToken->idOrigen]["ejecutado"]==1)
				return '$arrQueries['.$oToken->idOrigen.']["resultado"];';*/
			$consulta=$arrQueries[$oToken->idOrigen]["query"];
			$cadenaTmp="";
			if(sizeof($oToken->parametros)>0)
			{
				
				foreach($oToken->parametros as $p)
				{
					
					if($p->tipoValor=="")
						return "NULL";

					$vParam=resolverValoresTokens($p->tipoValor,$p->valorSistema,$arrQueries,$objParametros);
					if($p->tipoValor==21)
					{
						$vParam='".normalizarValorConsulta($'.$vParam.')."';
					}
					else
						$vParam=str_replace("'","",$vParam);
					if(strpos($consulta,"in ('@".$p->parametro."')")!==false)
						$consulta=str_replace("in ('@".$p->parametro."')","in (@".$p->parametro.")",$consulta);	
					$consulta=str_replace('@'.$p->parametro,$vParam,$consulta);

				}

				if($oToken->tipoOrigen==1)
				{
					$cadenaTmp.='$arrQueries['.$oToken->idOrigen.']["conector"]->obtenerFilasObjConector("'.$consulta.'");';
					return $cadenaTmp;
				}
				$cadenaTmp.='$arrQueries['.$oToken->idOrigen.']["conector"]->obtenerListaValores("'.$consulta.'","\'");';
				return $cadenaTmp;
			}
			
		break;
	}	
	
	
	
	return $valor;	
}

function codificarValor($valor)
{
	$cadenaFinal="";

	if(($valor==-1)||(isset($valor[0]))||(gettype($valor)=="integer")||(gettype($valor)=="double"))
	{
			
		if($valor[0]=="'")
			$cadenaFinal='"'.$valor.'"';
		else
			$cadenaFinal="'".$valor."'";

	}
	
	return $cadenaFinal;
}

function calcularNomina(&$objUsuario,&$nPuestos)
{
	global $con;
	global $totalSueldoBaseGlobal;
	global $totalDeduccionesGlobal;
	global $totalPercepcionesGlobal;
	global $totalSueldoNetoGlobal;
	global $arrCalculosDef;
	global $arrPuestos;
	global $habilitarCache;
	global $idPerfil;
	global $filaNomina;
	global $objGlobal;
	global $nominaProfesores;
	global $idUnidadAgrupadora;
	global $idNomina;
	global $arrCeldasExcel;
	
	$idPerfilImportacion=-1;
	$columnaEmpleado=-1;
	$considerarSoloEmpleadosImportados=0;
	
	if(($filaNomina[29]!="")&&($filaNomina[29]!="-1"))
	{
		$consulta="SELECT idPerfilImportacion,columnaEmpleado,considerarSoloEmpleadosImportados FROM 662_perfilesImportacionNomina WHERE idPerfilImportacionNomina=".$filaNomina[28];			
		$fPerfilImportacion=$con->obtenerPrimeraFila($consulta);
		$idPerfilImportacion=$fPerfilImportacion[0];
		$columnaEmpleado=$arrCeldasExcel[$fPerfilImportacion[1]];
		$considerarSoloEmpleadosImportados=$fPerfilImportacion[2];
	}
	
	
	$arrPlazas=array();
	if($idUnidadAgrupadora==0)
	{
		
		if($filaNomina[10]=="1")
		{
			$consulta="";
			if(!$nominaProfesores)
				$consulta="select idUsuario from 801_fumpEmpleado where activo=1";	
			else
			{
				$consulta="select idGrupos FROM 4520_grupos WHERE ((fechaInicio<='".$filaNomina[2]."' AND fechaFin>='".$filaNomina[2]."')OR(fechaInicio>='".$filaNomina[2]."' AND fechaInicio<='".$filaNomina[3]."') 
							OR (fechaInicio<'".$filaNomina[2]."' AND fechaFin>='".$filaNomina[3]."'))";
				$listGrupos=$con->obtenerListaValores($consulta);	
				if($listGrupos=="")
					$listGrupos=-1;
				$consulta="SELECT DISTINCT idUsuario FROM 4519_asignacionProfesorGrupo WHERE idGrupo IN (".$listGrupos.")";
			}
			$resTabulacion=$con->obtenerFilas($consulta);
			while($fPlaza=mysql_fetch_row($resTabulacion))
			{
				array_push($arrPlazas,$fPlaza[0]);	
			}
		}
		else
		{
			$cadObj='{"arreglo":'.str_replace("'","\"",$filaNomina[7])."}";	
			$obj=json_decode($cadObj);
			
			/*
			$objUsr[0]=$oUsr[0]; //IdUsr
			$objUsr[1]=$e[0];	//tipoUnidad
			$objUsr[2]=$e[1];	//idUnidad
			$objUsr[3]=$oUsr[1];	//Distintor
			$objUsr[4]=$oUsr[2];	//Etiqueta del distintor
			$llave=$objUsr[0]."_".$objUsr[1]."_".$objUsr[1]."_".$objUsr[3];
			
			if(!isset($arrPlazas[$llave]))	
			{
				$arrPlazas[$llave]=$objUsr;
			}
*/

			foreach($obj->arreglo as $e)
			{
				switch($e[0])
				{
					case 2: //depto
						if(isset($e[2])&&($e[2]!=0))
						{
							$obj=json_decode('{"param1":"'.$e[1].'","param2":"'.$filaNomina[0].'"}');
							$cache=NULL;
							$arrUsr=array();
							$listUsr=resolverExpresionCalculoPHP($e[2],$obj,$cache);
							if(gettype($listUsr)!="array")
							{
								if($listUsr!=-1)
								{
									$listUsr=str_replace("'","",$listUsr);
									$arrUsrTmp=explode(",",$listUsr);
									foreach($arrUsrTmp as $u)
									{
										$objU[0]=$u["idUsuario"];
										$objU[1]=0;
										$objU[2]="";
										array_push($arrUsr,$objU);	
									}
									
								}
							}
							else
							{
								if(sizeof($listUsr)>0)
								{
									foreach($listUsr as $u)
									{
										$objU[0]=$u["idUsuario"];
										$objU[1]=$u["distintor"];
										$objU[2]=$u["etDistintor"];
										array_push($arrUsr,$objU);	
									}
								}
							}
							if(sizeof($arrUsr)>0)
							{
								foreach($arrUsr as $oUsr)
								{
									$objUsr[0]=$oUsr[0];
									$objUsr[1]=$e[0];
									$objUsr[2]=$e[1];
									$objUsr[3]=$oUsr[1];
									$objUsr[4]=$oUsr[2];
									$llave=$oUsr[0]."_".$e[0]."_".$e[1]."_".$objUsr[3];
									if(!isset($arrPlazas[$llave]))	
									{
										$arrPlazas[$llave]=$objUsr;
									}
								}
							}
						}
						else
						{
							
							$consulta="select idUsuario from 801_fumpEmpleado where departamento='".$e[1]."'";	
							$resE=$con->obtenerFilas($consulta);
							while($fE=mysql_fetch_row($resE))
							{
								$objUsr[0]=$fE[0];
								$objUsr[1]=$e[0];
								$objUsr[2]=$e[1];
								$objUsr[3]="0";
								$objUsr[4]="";
								$llave=$fE[0]."_".$e[0]."_".$e[1]."_".$objUsr[3];
								if(!isset($arrPlazas[$llave]))	
								{
									$arrPlazas[$llave]=$objUsr;
								}
							}
						
							
						}
					break;
					case 3: //Empleado
						$llave=$e[1]."_".$e[0]."_0_0";
						if(!isset($arrPlazas[$llave]))	
						{
							$objUsr[0]=$e[1];
							$objUsr[1]=$e[0];
							$objUsr[2]="0";
							$objUsr[3]="0";
							$objUsr[4]="";
							$arrPlazas[$llave]=$objUsr;
						}
					break;
					case 4:  //Puesto
						if(isset($e[2])&&($e[2]!=0))
						{
							$obj=json_decode('{"param1":"'.$e[1].'","param2":"'.$filaNomina[0].'"}');
							$cache=NULL;
							$arrUsr=array();
							$listUsr=resolverExpresionCalculoPHP($e[2],$obj,$cache);
							if(gettype($listUsr)!="array")
							{
								if($listUsr!=-1)
								{
									$listUsr=str_replace("'","",$listUsr);
									$arrUsrTmp=explode(",",$listUsr);
									foreach($arrUsrTmp as $u)
									{
										$objU[0]=$u["idUsuario"];
										$objU[1]=0;
										$objU[2]="";
										array_push($arrUsr,$objU);	
									}
									
								}
							}
							else
							{
								if(sizeof($listUsr)>0)
								{
									foreach($listUsr as $u)
									{
										$objU[0]=$u["idUsuario"];
										$objU[1]=$u["distintor"];
										$objU[2]=$u["etDistintor"];
										array_push($arrUsr,$objU);	
									}
								}
							}
							if(sizeof($arrUsr)>0)
							{
								foreach($arrUsr as $oUsr)
								{
									$objUsr[0]=$oUsr[0];
									$objUsr[1]=$e[0];
									$objUsr[2]=$e[1];
									$objUsr[3]=$oUsr[1];
									$objUsr[4]=$oUsr[2];
									$llave=$oUsr[0]."_".$e[0]."_".$e[1]."_".$objUsr[3];
									if(!isset($arrPlazas[$llave]))	
									{
										$arrPlazas[$llave]=$objUsr;
									}
								}
							}
						}
						else
						{
							$consulta="select idUsuario from 801_fumpEmpleado where puesto='".$e[1]."'";	
							$resE=$con->obtenerFilas($consulta);
							while($fE=mysql_fetch_row($resE))
							{
								$objUsr[0]=$fE[0];
								$objUsr[1]=$e[0];
								$objUsr[2]=$e[1];
								$objUsr[3]="0";
								$objUsr[4]="";
								$llave=$fE[0]."_".$e[0]."_".$e[1]."_".$objUsr[3];
								if(!isset($arrPlazas[$llave]))	
								{
									$arrPlazas[$llave]=$objUsr;
								}
							}
						}
					break;
					case 5:  //Tipo de contratacion
						if(isset($e[2])&&($e[2]!=0))
						{
							$obj=json_decode('{"param1":"'.$e[1].'","param2":"'.$filaNomina[0].'"}');
							$cache=NULL;
							$arrUsr=array();
							$listUsr=resolverExpresionCalculoPHP($e[2],$obj,$cache);
							if(gettype($listUsr)!="array")
							{
								if($listUsr!=-1)
								{
									$listUsr=str_replace("'","",$listUsr);
									$arrUsrTmp=explode(",",$listUsr);
									foreach($arrUsrTmp as $u)
									{
										$objU[0]=$u["idUsuario"];
										$objU[1]=0;
										$objU[2]="";
										array_push($arrUsr,$objU);	
									}
									
								}
							}
							else
							{
								if(sizeof($listUsr)>0)
								{
									foreach($listUsr as $u)
									{
										$objU[0]=$u["idUsuario"];
										$objU[1]=$u["distintor"];
										$objU[2]=$u["etDistintor"];
										array_push($arrUsr,$objU);	
									}
								}
							}
							if(sizeof($arrUsr)>0)
							{
								foreach($arrUsr as $oUsr)
								{
									$objUsr[0]=$oUsr[0];
									$objUsr[1]=$e[0];
									$objUsr[2]=$e[1];
									$objUsr[3]=$oUsr[1];
									$objUsr[4]=$oUsr[2];
									$llave=$oUsr[0]."_".$e[0]."_".$e[1]."_".$objUsr[3];
									if(!isset($arrPlazas[$llave]))	
									{
										$arrPlazas[$llave]=$objUsr;
									}
								}
							}
						}
						else
						{
							$consulta="select idUsuario from 801_fumpEmpleado where tipoContratacion='".$e[1]."'";	
							$resE=$con->obtenerFilas($consulta);
							while($fE=mysql_fetch_row($resE))
							{
								$objUsr[0]=$fE[0];
								$objUsr[1]=$e[0];
								$objUsr[2]=$e[1];
								$objUsr[3]="0";
								$objUsr[4]="";
								$llave=$fE[0]."_".$e[0]."_".$e[1]."_".$objUsr[3];
								if(!isset($arrPlazas[$llave]))	
								{
									$arrPlazas[$llave]=$objUsr;
								}
							}
						}
					break;	
					
					case 7:  //Instituciones
						if(isset($e[2])&&($e[2]!=0))
						{
							$obj=json_decode('{"param1":"'.$e[1].'","param2":"'.$filaNomina[0].'"}');
							$cache=NULL;
							$arrUsr=array();
							$listUsr=resolverExpresionCalculoPHP($e[2],$obj,$cache);
							
							if(gettype($listUsr)!="array")
							{
								if($listUsr!=-1)
								{
									$listUsr=str_replace("'","",$listUsr);
									$arrUsrTmp=explode(",",$listUsr);
									foreach($arrUsrTmp as $u)
									{
										$objU[0]=$u;
										$objU[1]=0;
										$objU[2]="";
										array_push($arrUsr,$objU);	
									}
									
								}
							}
							else
							{
								if(sizeof($listUsr)>0)
								{
									foreach($listUsr as $u)
									{
										$objU[0]=$u["idUsuario"];
										$objU[1]=$u["distintor"];
										$objU[2]=$u["etDistintor"];
										array_push($arrUsr,$objU);	
									}
								}
							}
							
							if(sizeof($arrUsr)>0)
							{
								foreach($arrUsr as $oUsr)
								{
									$objUsr[0]=$oUsr[0];
									$objUsr[1]=$e[0];
									$objUsr[2]=$e[1];
									$objUsr[3]=$oUsr[1];
									$objUsr[4]=$oUsr[2];
									$llave=$oUsr[0]."_".$e[0]."_".$e[1]."_".$objUsr[3];
									if(!isset($arrPlazas[$llave]))	
									{
										$arrPlazas[$llave]=$objUsr;
									}
								}
							}
						}
						else
						{
							
							if($considerarSoloEmpleadosImportados==0)
							{
							
								$consulta="select idUsuario from 801_adscripcion where Institucion='".$e[1]."'";	
								$resE=$con->obtenerFilas($consulta);
								while($fE=mysql_fetch_row($resE))
								{
									$objUsr[0]=$fE[0];
									$objUsr[1]=$e[0];
									$objUsr[2]=$e[1];
									$objUsr[3]="0";
									$objUsr[4]="";
									$llave=$fE[0]."_".$e[0]."_".$e[1]."_".$objUsr[3];
									if(!isset($arrPlazas[$llave]))	
									{
										$arrPlazas[$llave]=$objUsr;
									}
								}
							}
							else
							{
								$consulta="select idEmpleado from 672_registrosArchivosImportadosNomina where idNomina=".$idNomina." and idEmpleado is not null";	
								$resE=$con->obtenerFilas($consulta);
								while($fE=mysql_fetch_row($resE))
								{
									$objUsr[0]=$fE[0];
									$objUsr[1]=$e[0];
									$objUsr[2]=$e[1];
									$objUsr[3]="0";
									$objUsr[4]="";
									$llave=$fE[0]."_".$e[0]."_".$e[1]."_".$objUsr[3];
									if(!isset($arrPlazas[$llave]))	
									{
										$arrPlazas[$llave]=$objUsr;
									}
								}
							}
						}
					break;	
					
					case 10:
						if(isset($e[2])&&($e[2]!=0))
						{
							$obj=json_decode('{"param1":"'.$e[1].'","param2":"'.$filaNomina[0].'"}');
							$cache=NULL;
							$arrUsr=array();
							$listUsr=resolverExpresionCalculoPHP($e[2],$obj,$cache);
							if(gettype($listUsr)!="array")
							{
								if($listUsr!=-1)
								{
									$listUsr=str_replace("'","",$listUsr);
									$arrUsrTmp=explode(",",$listUsr);
									foreach($arrUsrTmp as $u)
									{
										$objU[0]=$u["idUsuario"];
										$objU[1]=0;
										$objU[2]="";
										array_push($arrUsr,$objU);	
									}
									
								}
							}
							else
							{
								if(sizeof($listUsr)>0)
								{
									foreach($listUsr as $u)
									{
										$objU[0]=$u["idUsuario"];
										$objU[1]=$u["distintor"];
										$objU[2]=$u["etDistintor"];
										array_push($arrUsr,$objU);	
									}
								}
							}
							if(sizeof($arrUsr)>0)
							{
								foreach($arrUsr as $oUsr)
								{
									$objUsr[0]=$oUsr[0];
									$objUsr[1]=$e[0];
									$objUsr[2]=$e[1];
									$objUsr[3]=$oUsr[1];
									$objUsr[4]=$oUsr[2];
									$llave=$oUsr[0]."_".$e[0]."_".$e[1]."_".$objUsr[3];
									if(!isset($arrPlazas[$llave]))	
									{
										$arrPlazas[$llave]=$objUsr;
									}
								}
							}
						}
						else
						{
							
							$consulta="SELECT fechaInicioIncidencias,fechaFinIncidencias FROM 672_nominasEjecutadas WHERE idNomina=".$idNomina;
							$fDatosNomina=$con->obtenerPrimeraFila($consulta);
							$listExcepciones=-1;
							$consulta="SELECT cmbEmpleado FROM _1034_tablaDinamica WHERE cmbEmpresa=".$e[1]." AND fechaBaja>='".$fDatosNomina[0]."'";
							$listExcepciones=$con->obtenerListaValores($consulta);
							if($listExcepciones=="")
								$listExcepciones=-1;
							$consulta="SELECT idEmpleado FROM 693_empleadosNominaV2 WHERE idEmpresa='".$e[1]."' and (situacion=1 or idEmpleado in (".$listExcepciones."))";	

							$resE=$con->obtenerFilas($consulta);
							while($fE=mysql_fetch_row($resE))
							{
								$objUsr[0]=$fE[0]; //IdUsr
								$objUsr[1]=$e[0];	//tipoUnidad
								$objUsr[2]=$e[1];	//idUnidad
								$objUsr[3]=0;	//Distintor
								$objUsr[4]="";	//Etiqueta del distintor
								$llave=$objUsr[0]."_".$objUsr[1]."_".$objUsr[1]."_".$objUsr[3];
								if(!isset($arrPlazas[$llave]))	
								{
									$arrPlazas[$llave]=$objUsr;
								}
							}
						}
						
					case 11:
						if(isset($e[2])&&($e[2]!=0))
						{
							$obj=json_decode('{"param1":"'.$e[1].'","param2":"'.$filaNomina[0].'"}');
							$cache=NULL;
							$arrUsr=array();
							$listUsr=resolverExpresionCalculoPHP($e[2],$obj,$cache);
							if(gettype($listUsr)!="array")
							{
								if($listUsr!=-1)
								{
									$listUsr=str_replace("'","",$listUsr);
									$arrUsrTmp=explode(",",$listUsr);
									foreach($arrUsrTmp as $u)
									{
										$objU[0]=$u["idUsuario"];
										$objU[1]=0;
										$objU[2]="";
										array_push($arrUsr,$objU);	
									}
									
								}
							}
							else
							{
								if(sizeof($listUsr)>0)
								{
									foreach($listUsr as $u)
									{
										$objU[0]=$u["idUsuario"];
										$objU[1]=$u["distintor"];
										$objU[2]=$u["etDistintor"];
										array_push($arrUsr,$objU);	
									}
								}
							}
							if(sizeof($arrUsr)>0)
							{
								foreach($arrUsr as $oUsr)
								{
									$objUsr[0]=$oUsr[0];
									$objUsr[1]=$e[0];
									$objUsr[2]=$e[1];
									$objUsr[3]=$oUsr[1];
									$objUsr[4]=$oUsr[2];
									$llave=$oUsr[0]."_".$e[0]."_".$e[1]."_".$objUsr[3];
									if(!isset($arrPlazas[$llave]))	
									{
										$arrPlazas[$llave]=$objUsr;
									}
								}
							}
						}
						else
						{
							
							$consulta="SELECT fechaInicioIncidencias,fechaFinIncidencias FROM 672_nominasEjecutadas WHERE idNomina=".$idNomina;
							$fDatosNomina=$con->obtenerPrimeraFila($consulta);
							$listExcepciones=-1;
							/*$consulta="SELECT cmbEmpleado FROM _1034_tablaDinamica WHERE cmbEmpresa=".$e[1]." AND fechaBaja>='".$fDatosNomina[0]."'";
							$listExcepciones=$con->obtenerListaValores($consulta);
							if($listExcepciones=="")
								$listExcepciones=-1;*/
							$consulta="SELECT idEmpleado FROM 693_empleadosNominaV2 WHERE idCentroCosto='".$e[1]."' and (situacion=1 or idEmpleado in (".$listExcepciones."))";	
							
							$resE=$con->obtenerFilas($consulta);
							while($fE=mysql_fetch_row($resE))
							{
								$objUsr[0]=$fE[0]; //IdUsr
								$objUsr[1]=$e[0];	//tipoUnidad
								$objUsr[2]=$e[1];	//idUnidad
								$objUsr[3]=0;	//Distintor
								$objUsr[4]="";	//Etiqueta del distintor
								$llave=$objUsr[0]."_".$objUsr[1]."_".$objUsr[1]."_".$objUsr[3];
								if(!isset($arrPlazas[$llave]))	
								{
									$arrPlazas[$llave]=$objUsr;
								}
							}
						}	
						
					break;
				}	
			}
		}
	}
	else
	{
		$cadObj='{"arreglo":'.str_replace("'","\"",$filaNomina[7])."}";	
		$obj=json_decode($cadObj);
		foreach($obj->arreglo as $e)
		{

			$arrValoresUnidadAgrupadora=array();
			if($e[1]!=0)
			{
				array_push($arrValoresUnidadAgrupadora,$e[1]);
			}
			else
			{
				$idAgrupadora=($e[0]*-1);
				$consulta="SELECT idFuncionOrigenDatos,permiteSeleccionUnidades FROM 679_entidadesAgrupadorasNomina WHERE idEntidadNomina=".$idAgrupadora;
				$fEntidad=$con->obtenerPrimeraFila($consulta);
				$idFuncionSistema=$fEntidad[0];
				$arrElementos=array();
				$cadObj='{"idNomina":"'.$idNomina.'"}';
				$objParamTmp=json_decode($cadObj);
				$cache=NULL;
				$arrElementos=resolverExpresionCalculoPHP($idFuncionSistema,$objParamTmp,$cache);
				if(sizeof($arrElementos)>0)
				{
					foreach($arrElementos as $elem)
					{
						array_push($arrValoresUnidadAgrupadora,$elem["codigoUnidad"]);
					}
				}	
			}
			
			if(sizeof($arrValoresUnidadAgrupadora)>0)
			{
				foreach($arrValoresUnidadAgrupadora as $idValorAgrupador)
				{
					$obj=json_decode('{"idNomina":"'.$idNomina.'","idPerfilAgrupador":"'.$idAgrupadora.'","idValorAgrupador":"'.$idValorAgrupador.'"}');
					$cache=NULL;
					$arrUsr=array();

					$listUsr=resolverExpresionCalculoPHP($e[2],$obj,$cache);

					if(gettype($listUsr)!="array")
					{
						if($listUsr!=-1)
						{
							$listUsr=str_replace("'","",$listUsr);
							$arrUsrTmp=explode(",",$listUsr);
							foreach($arrUsrTmp as $u)
							{
								$objU[0]=$u["idUsuario"];
								$objU[1]=0;
								$objU[2]="";
								array_push($arrUsr,$objU);	
							}
							
						}
					}
					else
					{
						if(sizeof($listUsr)>0)
						{
							foreach($listUsr as $u)
							{
								$objU[0]=$u["idUsuario"];
								$objU[1]=$u["distintor"];
								$objU[2]=$u["etDistintor"];
								array_push($arrUsr,$objU);	
							}
						}
					}
					if(sizeof($arrUsr)>0)
					{
						foreach($arrUsr as $oUsr)
						{
							$objUsr[0]=$oUsr[0];
							$objUsr[1]=$idAgrupadora*-1;
							$objUsr[2]=$idValorAgrupador;
							$objUsr[3]=$oUsr[1];
							$objUsr[4]=$oUsr[2];
							$llave=$oUsr[0]."_".$idAgrupadora."_".$idValorAgrupador."_".$objUsr[3];
							if(!isset($arrPlazas[$llave]))	
							{
								$arrPlazas[$llave]=$objUsr;
							}
						}
					}
				}
			}
			
			
		}
		
	}
	
	
	if(sizeof($arrPlazas)>0)
	{
		$ctPlazas=1;
		foreach($arrPlazas as $llave=>$objUsr)
		{
			
			if($objUsr[0]==-1)
			{
				continue;
			}
			
			inicializarPuesto($objUsuario);
			$idUsuario=$objUsr[0];
			
			
			$filaTab=array();
			$consulta="";
			
			
			switch($objUsr[1])//TIpo unidad
			{
				case 11:
				case 10:
					$consulta="select '0' as salario,'".$idUsuario."' as  idUsuario, idPuesto,regimenContratacion,'0' as zona,'".$objUsr[1]."' as tipoUnidad from 693_empleadosNominaV2 where idEmpleado=".$idUsuario;
				break;
				default:
				
				
					if($considerarSoloEmpleadosImportados==1)
						$consulta="select '0' as salario,".$idUsuario." as idUsuario, '' as cod_Puesto,'' as tipoContratacion,'0' as zona,'".$objUsr[1]."' as tipoUnidad";
					else
						$consulta="select '0' as salario,idUsuario, cod_Puesto,tipoContratacion,'0' as zona,'".$objUsr[1]."' as tipoUnidad from 801_adscripcion where idUsuario=".$idUsuario;
				break;	
			}
			
			
			$filaTab=$con->obtenerPrimeraFila($consulta);
			if($filaTab)
			{
				$acumuladoBaseGravablePercepcion=0;
				$acumuladoBaseGravableDeduccion=0;
	
				calcularNominaUsuario($filaTab,$objUsuario,$objUsr);
				$objGlobal->totalDeducciones+=$objUsuario->totalDeducciones;
				$objGlobal->totalPercepciones+=$objUsuario->totalPercepciones;
				$objGlobal->nPlazas++;
				if(sizeof($objUsuario->arrCalculosGlobales)>0)
				{
					foreach($objUsuario->arrCalculosGlobales as $idCalculo=>$calculo)
					{
						if(isset($cadObjGlobal->arrCalculos[$idCalculo]))
							$objGlobal->arrCalculos[$idCalculo]["valorCalculado"]+=$calculo["valorCalculado"];
						else
						{
							$objGlobal->arrCalculos[$idCalculo]["orden"]=$calculo["orden"];
							$objGlobal->arrCalculos[$idCalculo]["tipoCalculo"]=$calculo["tipoCalculo"];
							$objGlobal->arrCalculos[$idCalculo]["nombreCalculo"]=$calculo["nombreCalculo"];
							$objGlobal->arrCalculos[$idCalculo]["valorCalculado"]=$calculo["valorCalculado"];
						}
					}
				}
	
			}
		}
		
	
		
	}
}

function inicializarPuesto(&$objUsuario)
{
	
	$objUsuario->idUsuario="";
	$objUsuario->sueldoBase=0;
	$objUsuario->nFaltas=0;
	$objUsuario->nRetardos=0;
	$objUsuario->totalDeducciones=0;
	$objUsuario->totalPercepciones=0;
	$objUsuario->sueldoNeto=0;
	$fechaContratacion="";
	$objUsuario->fechaContratacion="";
	$objUsuario->arrCalculosGlobales=array();
	$objUsuario->departamento="";
	$objUsuario->puesto="''";
	$objUsuario->fechaBaja="";
	$objUsuario->fechaBasificacion="";
	$objUsuario->horasTrabajador="";
	$objUsuario->institucion="";
	$objUsuario->fechaContratacion="";
	$objUsuario->tipoContratacion="";
	$objUsuario->idZona="";
	$objUsuario->situacion="";
	$objUsuario->tipoPago="";
	$objUsuario->idIdentificadorComplementario=0;
	$objUsuario->etiquetaIdentificadorComp="";
	$objUsuario->objConfiguracion=NULL;
	$objUsuario->objImportacion=NULL;
}

function calcularNominaUsuario($filaTab,&$objUsuario,$objU)
{
	global $f;
	global $con;
	global $habilitarCache;
	global $arrCalculosDef;
	global $idPerfil;
	global $nPuestosCalculados;
	global $arrAcumuldoresGlobales;
	global $arrResultados;
	global $idNomina;
	global $arrCarga;
	global $cargarDeArchivo;
	global $arrFactores;
	global $precision;
	global $accionPrecision;
	
	global $acumuladoBaseGravablePercepcion;
	global $acumuladoBaseGravableDeduccion;
	$objUsuario->objConfiguracion=$objU;
	$idUsuario=$filaTab[1];
	
	
	
	
	
	$consulta="SELECT arregloInformacion FROM 672_registrosArchivosImportadosNomina WHERE idNomina=".$idNomina." AND idEmpleado=".$idUsuario;
	$arregloInformacion=$con->obtenerValor($consulta);
	if($arregloInformacion!="")
	{
		$objUsuario->objImportacion=unserialize(bD($arregloInformacion));	
	}
	
	
	
	
	switch($filaTab[5])//TIpo unidad
	{
		case 10:
			$consulta="select fechaIniRelLab,'' as fechaBase,'' as fechaBaja,'0' as horasTrabajador,idEmpresa as Institucion,idDepartamento as codigoUnidad,situacion as Status,formaPago as tipoPago,idPuesto as cod_Puesto,
						regimenContratacion,'0' as zona from 693_empleadosNominaV2 where idEmpleado=".$idUsuario;
		break;
		default:
			//--Verificar
			$consulta="select fechaIngresoInstitucion,fechaBase,fechaBaja,horasTrabajador,Institucion,codigoUnidad,Status,tipoPago,cod_Puesto,tipoContratacion,'0' as zona from 801_adscripcion where idUsuario=".$idUsuario;

		break;	
	}
	

	$filaAds=$con->obtenerPrimeraFila($consulta);
	$sueldoBase=$filaTab[0];
	$titular=obtenerNombreUsuario($idUsuario);
	$objUsuario->idUsuario=$idUsuario;
	$objUsuario->puesto="'".$filaTab[2]."'";
	
	if(($objUsuario->puesto=='')||(($objUsuario->puesto=="''")))
	{
		
		$objUsuario->puesto=$filaAds[8];
		if($objUsuario->puesto=="")
			$objUsuario->puesto=-1;
	}
	$objUsuario->tipoContratacion=$filaTab[3];
	if($objUsuario->tipoContratacion=='')
	{
		$objUsuario->tipoContratacion=$filaAds[9];
		if($objUsuario->tipoContratacion=="")
			$objUsuario->tipoContratacion=-1;
	}
	$objUsuario->fechaBaja="'".$filaAds[2]."'";
	$objUsuario->fechaBasificacion="'".$filaAds[1]."'";
	if($filaAds[3]!="")
		$objUsuario->horasTrabajador=$filaAds[3];
	else
		$objUsuario->horasTrabajador="0";	
	$objUsuario->institucion=$filaAds[4];
	$objUsuario->fechaContratacion=$filaAds[0];
	$objUsuario->sueldoBase=$sueldoBase;
	$objUsuario->nFaltas=0;
	$objUsuario->nRetardos=0;
	$objUsuario->totalDeducciones=0;
	$objUsuario->totalPercepciones=0;
	$objUsuario->sueldoNeto=0;
	$objUsuario->departamento=$filaAds[5];
	$objUsuario->idZona=$filaTab[4];
	if($objUsuario->idZona=='')
	{
		$objUsuario->idZona=$filaAds[10];
		if($objUsuario->idZona=="")
			$objUsuario->idZona=-1;
	}
	
	if($filaAds[6]!="")
		$objUsuario->situacion=$filaAds[6];
	else
		$objUsuario->situacion="-1";	
	$objUsuario->tipoPago=$filaAds[7];
	if($objUsuario->tipoPago=="")
		$objUsuario->tipoPago=-1;
		
	$objUsuario->idEntidadAgrupacion=0;
	$objUsuario->idUnidadAgrupadora=0;
	if($objU[1]<0)
	{
		$objUsuario->idEntidadAgrupacion=$objU[1]*-1;
		$objUsuario->idUnidadAgrupadora=$objU[2];
		
	}
	$objUsuario->idIdentificadorComplementario=$objU[3];
	$objUsuario->etiquetaIdentificadorComp=$objU[4];	
		
	$cacheCalculos=NULL;
	if($habilitarCache)
		$cacheCalculos=array();
	if(isset($objU[2]))
	{
		switch($objU[1])
		{
			case 2:
				$objUsuario->departamento=$objU[2];
			break;
			case 4:
				$objUsuario->puesto="".$objU[2]."";
			break;
			case 5:
				$objUsuario->tipoContratacion="".$objU[2]."";
			break;
			case 7:
				$objUsuario->institucion="".$objU[2]."";
			break;
			case 10:
				$objUsuario->institucion="".$objU[2]."";
			break;
		}
	}
	
	
	
	foreach($arrAcumuldoresGlobales as $acumG=>$valor)
	{
		  $arrAcumuldoresGlobales[$acumG]=0;	
	}
	
	
	if($con->existeTabla("9135_vistafactorusuario"))
	{
		$consulta="SELECT cveFactor,cantidad,idTipoValor FROM 9135_vistafactorusuario WHERE idUsuario=".$objUsuario->idUsuario." AND ciclo=".$objUsuario->ciclo." AND estado=0 AND periodo=".$objUsuario->quincena;
		$arrFactores=$con->obtenerFilasArregloAsocPHP($consulta,true);
	}
	else
		$arrFactores=array();
	
	
	realizarCalculosGlobales($objUsuario,$arrCalculosDef,$arrAcumuldoresGlobales,$cacheCalculos,$idPerfil,$precision,$accionPrecision);
	$arrCalculosInd=array();
	$arrCalculosGlobal=array();
	if(sizeof($objUsuario->arrCalculosIndividuales)>0)
	{
		foreach($objUsuario->arrCalculosIndividuales as $calculoInd)
		{
			if($calculoInd["tipoCalculo"]=="1")
				$objUsuario->totalDeducciones+=$calculoInd["valorCalculado"];
			else
				$objUsuario->totalPercepciones+=$calculoInd["valorCalculado"];
			$obj["tipoCalculo"]=$calculoInd["tipoCalculo"];
			$obj["nombreCalculo"]=$calculoInd["nombreCalculo"];
			$obj["valorCalculado"]=$calculoInd["valorCalculado"];
			array_push($arrCalculosInd,$obj);
		}
	}
	
	if(sizeof($objUsuario->arrCalculosGlobales)>0)
	{
		foreach($objUsuario->arrCalculosGlobales as $calculoInd)
		{
			if($calculoInd["tipoCalculo"]=="1")
				$objUsuario->totalDeducciones+=$calculoInd["valorCalculado"];
			else
				if($calculoInd["tipoCalculo"]=="2")
					$objUsuario->totalPercepciones+=$calculoInd["valorCalculado"];
				
			$obj["tipoCalculo"]=$calculoInd["tipoCalculo"];
			$obj["nombreCalculo"]=$calculoInd["nombreCalculo"];
			$obj["valorCalculado"]=$calculoInd["valorCalculado"];
			$obj["importeGravado"]="";
			$obj["importeExento"]="";
			
		}
		
	}
	
	
	$compApDeducciones="";
	$compCDeducciones="";
	$compApPercepciones="";
	$compCPercepciones="";
	
	$nPuestosCalculados++;
	$objUsuario->sueldoNeto=$objUsuario->totalPercepciones-$objUsuario->totalDeducciones;
	
	$objSerial=serialize($objUsuario);
	$puesto=str_replace("'","",$objUsuario->puesto);
	$tPago=$objUsuario->tipoPago;
	if($tPago=="")
		$tPago="-1";
	$departamento=$objUsuario->departamento;
	
	if($departamento=="")
		$departamento=$objUsuario->institucion;
	if($objUsuario->tipoContratacion=="")
		$objUsuario->tipoContratacion=-1;
		
	if(isset($objU[2]))
	{
		switch($objU[1])
		{
			case 7:
				$departamento=$objUsuario->institucion;
			break;
		}
	}	
		
	if(isset($f))
	{
		$cadEscritura=$puesto."|".$departamento."|".$objUsuario->tipoContratacion."|".$objUsuario->idZona."|".$objUsuario->idUsuario."|".$objUsuario->totalDeducciones."|".$objUsuario->totalPercepciones."|".$objUsuario->sueldoNeto."|".
					$objSerial."|".$objUsuario->ciclo."|".$objUsuario->quincena."|".$idPerfil."|".$idNomina."|".$objUsuario->horasTrabajador."|".($objUsuario->sueldoBase/2)."|".$tPago."|".$objUsuario->situacion."|0|".$objUsuario->institucion.
					"|\N|\N|\N|".$objUsuario->idUnidadAgrupadora."|".$objUsuario->idIdentificadorComplementario."|".$objUsuario->etiquetaIdentificadorComp."\r";
		fwrite($f,$cadEscritura);
	}
	
	

	if(!$cargarDeArchivo)
	{
		
		$consulta="('".$puesto."','".$departamento."',".$objUsuario->tipoContratacion.",".$objUsuario->idZona.",".$objUsuario->idUsuario.",".$objUsuario->totalDeducciones.",".$objUsuario->totalPercepciones.",
					".$objUsuario->sueldoNeto.",'".cv($objSerial)."',".$objUsuario->ciclo.",".$objUsuario->quincena.",".$idPerfil.",".$idNomina.",".$objUsuario->horasTrabajador.",".($objUsuario->sueldoBase/2).
					",".$objUsuario->situacion.",'".$objUsuario->institucion."',0,NULL,NULL,".$tPago.",".$objUsuario->idUnidadAgrupadora.",".$objUsuario->idIdentificadorComplementario.",'".cv($objUsuario->etiquetaIdentificadorComp)."')";

		array_push($arrCarga,$consulta);
	}
}

function calcularIdNomina($iNomina,$nomProfesores=true)
{
	global $con;
	global $baseDir;
	global $nominaProfesores;
	$nominaProfesores=$nomProfesores;
	global $estadisticasCalculo;
	global $arrCalculosDef;
	global $arrPuestos;
	global $arrAcumuldoresGlobales;
	global $arrFactoresRiesgo;
	global $filaNomina;
	global $habilitarCache;
	global $idPerfil;
	global $idNomina;
	$idNomina=$iNomina;
	global $totalSueldoBaseGlobal;
	global $totalDeduccionesGlobal;
	global $totalPercepcionesGlobal;
	global $totalSueldoNetoGlobal;
	global $objGlobal;
	global $f;
	global $nPuestosCalculados;
	global $arrResultados;
	global $arrCarga;
	global $cargarDeArchivo;
	global $arrFactores;
	global $accionPrecision;
	global $precision;
	global $idUnidadAgrupadora;
	global $arrCeldasExcel;
	
	
	
	
	$calcular=1;

	
	$idPerfilImportacion=0;
	
	$consulta="select * from 672_nominasEjecutadas where idNomina=".$idNomina;
	$filaNomina=$con->obtenerPrimeraFila($consulta);
	
	if(($filaNomina[28]!="")&&($filaNomina[28]!="0"))
	{
		$consulta="SELECT idPerfilImportacion,columnaEmpleado,considerarSoloEmpleadosImportados FROM 662_perfilesImportacionNomina WHERE idPerfilImportacionNomina=".$filaNomina[28];			
		$fPerfilImportacion=$con->obtenerPrimeraFila($consulta);
		$idPerfilImportacion=$fPerfilImportacion[0];
		
	}
	
	$idPerfil=$filaNomina[1];
	$idUnidadAgrupadora=$filaNomina[25];
	
	$consulta="SELECT precisionDecimales,criterioPrecision,idFuncionRecalculo,idFuncionEliminacion FROM 662_perfilesNomina WHERE idPerfilesNomina=".$idPerfil;
	$fPerfil=$con->obtenerPrimeraFila($consulta);
	$accionPrecision=$fPerfil[1];
	$precision=$fPerfil[0];
	$consulta="select idCalculo,concat('[',idCalculo,'] ',co.nombreConsulta)  
				from 662_calculosNomina c,991_consultasSql co where idPerfil=".$idPerfil." and co.idConsulta=c.idConsulta";
	
	
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		$arrCalculosDef[$fila[0]]["etiquetaCalculo"]=$fila[1];
		$consulta="SELECT etiquetaConcepto FROM 662_calculosNomina WHERE idCalculo=".$fila[0];
		$etiquetaConcepto=$con->obtenerValor($consulta);
		if($etiquetaConcepto!="")
		{
			$arrCalculosDef[$fila[0]]["etiquetaCalculo"]=$etiquetaConcepto;
		}
		
		
		
		
		
		$arrCalculosDef[$fila[0]]["columnaImportacion"]="";
		if($idPerfilImportacion!=0)
		{
			$consulta="SELECT idColumnaAsociada FROM 662_configuracionPerfilImportacion WHERE idPerfilImportacion=".$idPerfilImportacion." AND idCalculoNomina=".$fila[0];	
			$iColumna=$con->obtenerValor($consulta);
			if($iColumna!="")
			{
				$arrCalculosDef[$fila[0]]["columnaImportacion"]=$arrCeldasExcel[$iColumna];
			}
		}
		
	}
	
	//$arrCalculosDef=$con->obtenerFilasArregloAsocPHP($consulta);
	
	
	
	$consulta="SELECT idTipoPuesto,nombreTipoPuesto FROM 664_tiposPuesto";
	$resConsulta=$con->obtenerFilas($consulta);
	$arrPuestos=array();
	while($filaPuesto=mysql_fetch_row($resConsulta))
		$arrPuestos[$filaPuesto[0]]=$filaPuesto[1];
	
	$consulta="SELECT idAcumuladorNomina FROM 665_acumuladoresNomina WHERE nivelAcumulador=0 AND idPerfil=".$idPerfil."";
	
	$resAcumuladores=$con->obtenerFilas($consulta);
	$arrAcumuldoresGlobales=array();
	while($filaAcum=mysql_fetch_row($resAcumuladores))
	{
		$arrAcumuldoresGlobales[$filaAcum[0]]=0;
	}
	
	$habilitarCache=true;
	
	$nPuestosCalculados=0;
	//////
	$arrFactores=array();
	$arrFactoresRiesgo=array();
	
	//-------------
	$fechaActual=date("Y-m-d");
	$anio=date("Y");
	$fechaInicial="".$anio."-01-01";
	$diasTranscurridos=obtenerDiferenciaDias($fechaInicial,$fechaActual);
	//--------------
	$consulta="SELECT concat(cveFactor,'_',departamento,'_',cvePuesto) as clave,valor,tipoValor FROM 669_definicionFactoresNomina ORDER BY cveFactor,departamento,cvePuesto";
	$arrFactoresRiesgo=$con->obtenerFilasArregloAsocPHP($consulta,true);
	
	$cargarDeArchivo=false;
	$arrCarga=array();
	$ciclo=$filaNomina[8];
	$codUnidad="0001";
	$quincena=$filaNomina[9];
	$totalSueldoBaseGlobal=0;
	$totalDeduccionesGlobal=0;
	$totalPercepcionesGlobal=0;
	$totalSueldoNetoGlobal=0;
	
	$cadObj='	{
						"idUsuario":"",
						"fechaContratacion":"",
						"sueldoBase":"",
						"arrCalculosIndividuales":[],
						"arrCalculosGlobales":[],
						"ciclo":"'.$ciclo.'",
						"quincena":"'.$quincena.'",
						"nFaltas":"",
						"nRetardos":"",
						"departamento":"",
						"totalDeducciones":"",
						"totalPercepciones":"",
						"sueldoNeto":"",
						"puesto":"",
						"fechaBaja":"",
						"fechaBasificacion":"",
						"horasTrabajador":"",
						"institucion":"",
						"tipoContratacion":"",
						"fechaIniIncidencia":"'.$filaNomina[2].'",
						"fechaFinIncidencia":"'.$filaNomina[3].'",
						"idZona":"",
						"situacion":"",
						"tipoPago":"",
						"idNomina":"'.$idNomina.'",
						"idEntidadAgrupacion":"0",
						"idUnidadAgrupadora":"0",
						"idIdentificadorComplementario":"0",
						"etiquetaIdentificadorComp":"",
						"calcularIdNomina":"",
						"objImportacion":""
						
						
					}';
	$cadObjGlobal='	{
						"arrCalculos":[],
						"totalDeducciones":"0",
						"totalPercepciones":"0",
						"nPlazas":"0"
					}';
	$objUsuario=json_decode($cadObj);
	$objGlobal=json_decode($cadObjGlobal);
	$modoDebug=false;
	$nPuestos=0;
	$mInicio=microtime();
	$arrTime=explode(" ",$mInicio);
	$arrMili=explode(".",$arrTime[0]);
	$decIni=$arrTime[1].".".$arrMili[1];
	$hInicio=date("H:i:s",$arrTime[1]).".".$arrMili[1];
	$generaNomina=false;
	if($calcular=="1")
		$generaNomina=true;
	
	
	$nEtapa=$filaNomina[6];
	$cadObjGlobal=$filaNomina[12];
	
	if($generaNomina)
	{
		$consulta="SELECT nombreTablas FROM 4557_tablasNominaDepuracion";
		$resTbl=$con->obtenerFilas($consulta);
		while($fNomina=mysql_fetch_row($resTbl))
		{
			$consulta="delete from ".$fNomina[0]." where idNomina=".$idNomina;
			if(!$con->ejecutarConsulta($consulta))
				return;
		}
		if($cargarDeArchivo)	
		{
			$nArchivo='tmp_'.$idPerfil."_".mt_rand(0,100).'.txt';
			$f=fopen('../archivosTemporales/'.$nArchivo,"w+");
		}
		
		if(($fPerfil[2]!="")&&($fPerfil[2]!="-1"))
		{
			$cache=NULL;
			$cadObj='{"idNomina":"'.$idNomina.'"}';
			$obj=json_decode($cadObj);
			resolverExpresionCalculoPHP($fPerfil[2],$obj,$cache);
		}
		
		if(($filaNomina[29]!="")&&($filaNomina[29]!="-1"))
		{
			$consulta="SELECT idPerfilImportacion,columnaEmpleado,considerarSoloEmpleadosImportados FROM 662_perfilesImportacionNomina WHERE idPerfilImportacionNomina=".$filaNomina[28];			
			$fPerfilImportacion=$con->obtenerPrimeraFila($consulta);
			$idPerfilImportacion=$fPerfilImportacion[0];
			$columnaEmpleado=$arrCeldasExcel[$fPerfilImportacion[1]];
			$considerarSoloEmpleadosImportados=$fPerfilImportacion[2];
			
			
			$rutaArchivo=$baseDir."/documentosUsr/archivo_".$filaNomina[29];
			$lectorImp=new cLectorPerfilImportacion($idPerfilImportacion,$rutaArchivo);

			$nQImp=0;
			$queryImpTmp[$nQImp]="begin";
			$nQImp++;
			$queryImpTmp[$nQImp]="ALTER TABLE 672_registrosArchivosImportadosNomina DISABLE KEYS";
			$nQImp++;
			$oLector=$lectorImp->obtenerLector();
			foreach($oLector->arrRegistros as $r)
			{
				$idEmpleado="NULL";
				if(isset($r[$columnaEmpleado]))		
				{
					$idEmpleado=$r[$columnaEmpleado];
				}
				
				$queryImpTmp[$nQImp]="INSERT INTO 672_registrosArchivosImportadosNomina(idNomina,idEmpleado,arregloInformacion) VALUES(".$idNomina.",".$idEmpleado.",'".bE(serialize($r))."')";
				$nQImp++;	
				
				
			}
			$queryImpTmp[$nQImp]="ALTER TABLE 672_registrosArchivosImportadosNomina ENABLE KEYS";
			$nQImp++;
			$queryImpTmp[$nQImp]="commit";
			$nQImp++;
			if(!$con->ejecutarBloque($queryImpTmp))
				return;
			
			
			
		}
		
		
		calcularNomina($objUsuario,$nPuestos);
		
		if($cargarDeArchivo)
			fclose($f);

		$x=0;
		$query[$x]="begin";
		$x++;
		
		$query[$x]="INSERT INTO 671_asientosCalculosNominaRespaldo
					SELECT * FROM 671_asientosCalculosNomina WHERE idNomina=".$idNomina." AND  idComprobante IS NOT NULL";
		$x++;
		
		$query[$x]="delete from 671_asientosCalculosNomina where idNomina=".$idNomina;
		$x++;
		$query[$x]="ALTER TABLE 671_asientosCalculosNomina DISABLE KEYS";
		$x++;
		if($cargarDeArchivo)
		{
			$query[$x]="load data local infile '".$baseDir."/archivosTemporales/".$nArchivo."' into table 671_asientosCalculosNomina FIELDS TERMINATED BY '|' LINES TERMINATED BY '\r'";
			$x++;
		}
		else
		{
			
			$listaValores="";
			foreach($arrCarga as $q)
			{
				if($listaValores=="")
					$listaValores=$q;
				else
					$listaValores.=",".$q;
			}
			if($listaValores!="")
			{
				$query[$x]="insert into 671_asientosCalculosNomina(cvePuesto,codDepartamento,tipoContratacion,idZona,idUsuario,totalDeducciones,
							totalPercepciones,sueldoNeto,objDetalle,idCiclo,quincenaAplicacion,idPerfil,idNomina,horasTrabajador,sueldoCompactado,situacion,institucion,pagado,responsablePago,fechaPago,
							tipoPago,idUnidadAgrupadora,identificador,descriptorIdentificador) values ".$listaValores;
				
				
				$x++;
			}
			 
			
		}
		$query[$x]="ALTER TABLE 671_asientosCalculosNomina ENABLE KEYS";
		$x++;
		/*if($nEtapa==0)
		{
			$query[$x]="update 672_nominasEjecutadas set etapa=1 where idNomina=".$idNomina;
			$x++;	
		}*/
		$query[$x]="update 672_nominasEjecutadas set fechaUltimaEjecucion='".date("Y-m-d H:i")."',respUltimaEjecucion=".$_SESSION["idUsr"].",objGlobal='".serialize($objGlobal)."',montoTotalDeducciones=".$objGlobal->totalDeducciones.",montoTotalPercepciones=".$objGlobal->totalPercepciones.",noPlazas=".$objGlobal->nPlazas.",montoTotalSueldoNeto=".($objGlobal->totalPercepciones-$objGlobal->totalDeducciones)." where idNomina=".$idNomina;
		$x++;
		
		$query[$x]="UPDATE 672_notificacionesNomina SET situacion=0 WHERE idNomina=".$idNomina;
		$x++;
		
		$query[$x]="delete from 672_registrosArchivosImportadosNomina where idNomina=".$idNomina;
		$x++;
		
		$query[$x]="commit";
		$x++;
		
		if($con->ejecutarBloque($query))
		{
			if($cargarDeArchivo)
				unlink('../archivosTemporales/'.$nArchivo);
		}
	
	}
	else
	{
		$objGlobal=unserialize($cadObjGlobal);	
		
	}
	$mFinal=microtime();
	$arrTime=explode(" ",$mFinal);
	$arrMili=explode(".",$arrTime[0]);

	$decFin=$arrTime[1].".".$arrMili[1];
	$diferencia=$decFin-$decIni;
	
	$hFin=date("H:i:s",$arrTime[1]).".".$arrMili[1];
	return true;
}

function generarFolioNomina($ciclo)
{
	global $con;
	$consulta="begin";
	if(!$con->ejecutarConsulta($consulta))
	{
		return "";
	}
	
	$consulta="SELECT folioActual FROM 673_foliosNomina WHERE cicloFiscal=".$ciclo." FOR UPDATE";
	$nFolio=$con->obtenerValor($consulta);
	if($nFolio=="")
	{
		$nFolio=1;
		$consulta="insert into  673_foliosNomina(cicloFiscal,folioActual) values(".$ciclo.",1)";
	}
	else
	{
		$consulta="update 673_foliosNomina set folioActual=folioActual+1 where  cicloFiscal=".$ciclo;
	}
	
	if(!$con->ejecutarConsulta($consulta))
		return "";
	
	$consulta="commit";
	if(!$con->ejecutarConsulta($consulta))
		return "";
	
	$folio="Nom-".$ciclo."-".str_pad($nFolio,"4","0",STR_PAD_LEFT);
	return $folio;
}

function obtenerValorCalculo($idNomina,$idCalculo,$idUsuario,$plantel,$identificador=0)
{
	global $con;	
	$buscarCalculo=false;
	$consulta="SELECT objDetalle FROM 671_asientosCalculosNomina WHERE idNomina=".$idNomina." AND idUsuario=".$idUsuario." and codDepartamento='".$plantel."' and identificador=".$identificador;
	$objDetalle=$con->obtenerValor($consulta);
	if($objDetalle=="")
		return 0;
	$obj=unserialize($objDetalle);

	foreach($obj->arrCalculosGlobales as $c)
	{
		if(isset($c["idConsulta"]))
		{
			if($c["idConsulta"]==$idCalculo)
			{
				return $c["valorCalculado"];
			}
		}
		else
		{
			$buscarCalculo=true;
			break;
		}
	}
	if($buscarCalculo)
	{
		$consulta="SELECT idPerfil FROM 672_nominasEjecutadas WHERE idNomina=".$idNomina;
	  	$idPerfil=$con->obtenerValor($consulta);
	  	$consulta="SELECT idCalculo FROM 662_calculosNomina WHERE idConsulta=".$idCalculo." AND idPerfil=".$idPerfil;
	  	$idCalculoNomina=$con->obtenerValor($consulta);
	  	if($idCalculoNomina=="")
			return 0;
	  	if(isset($obj->arrCalculosGlobales[$idCalculoNomina]))
	  		return $obj->arrCalculosGlobales[$idCalculoNomina]["valorCalculado"];
	}
	return 0;
	
}

function cambiarEtapaNomina($idNomina,$etapa,$comentarios="")
{
	global $con;
	if($etapa=="")
	{
		echo "No se ha configurado la etapa a la cual pasar&aacute; la nomina";
		return false;
	}
	
	$consulta="select etapa,idPerfil from 672_nominasEjecutadas WHERE idNomina=".$idNomina;
	$fNomina=$con->obtenerPrimeraFila($consulta);
	$estadoAnterior=$fNomina[0];
	$idPerfil=$fNomina[1];
	$x100584=0;
	$query100584[$x100584]="begin";
	$x100584++;
	$query100584[$x100584]="update 672_nominasEjecutadas set etapa=".$etapa." where idNomina=".$idNomina;

	$x100584++;
	$query100584[$x100584]="INSERT INTO 676_historialNomina(idNomina,fechaCambioEtapa,etapaAnterior,etapaActual,idResponsableCambio,comentarios)
							VALUES(".$idNomina.",'".date("Y-m-d H:i")."',".$estadoAnterior.",".$etapa.",".$_SESSION["idUsr"].",'".cv($comentarios)."')";

	$x100584++;
	$query100584[$x100584]="commit";
	$x100584++;

	if($con->ejecutarBloque($query100584))
	{
		$consulta="SELECT idConsulta FROM 991_consultasSql  WHERE tipoConsulta=11 AND comp1=".$idPerfil." AND comp2=".$etapa;
		$idConsulta=$con->obtenerValor($consulta);
		if($idConsulta!="")
		{
			$cadObj='{"idNomina":"'.$idNomina.'","numEtapa":"'.$etapa.'"}';
			
			$obj=json_decode($cadObj);
			$objAux=NULL;
			resolverExpresionCalculoPHP($idConsulta,$obj,$objAux);
		}
		return true;
	}
	return false;
}

function inicializarVariableEstructura($idToken,&$arrVariablesRegistradas)
{
	global $con;
	$cad="";
	$consulta="select tokenMysql,comp1,idConsulta FROM 992_tokensConsulta WHERE idToken=".$idToken;
	$fToken=$con->obtenerPrimeraFila($consulta);
	$arrTokens=explode("[",$fToken[0]);
	$arrTokens[0]=str_replace("=","",$arrTokens[0]);
	if(!existeValor($arrVariablesRegistradas,$arrTokens[0]))
	{
		array_push($arrVariablesRegistradas,$arrTokens[0]);
		$cad.=inicializarEstructura($arrTokens[0],$fToken[1],$fToken[2]);
		
	}
	return $cad;
}

function inicializarEstructura($nVariable,$idEstructura,$idCalculo)
{
	global $con;
	$nVariable=str_replace("=","",$nVariable);
	$cad=$nVariable."=array();";
	$consulta="select arrEstructuras from 991_consultasSql where idConsulta=".$idCalculo;
	$fila=$con->obtenerPrimeraFila($consulta);
	if($fila[0]!="")
	{
		$cadObj='{"registros":'.$fila[0].'}';
		$obj=json_decode($cadObj);
		if(sizeof($obj->registros)>0)
		{
			
			foreach($obj->registros as $r)
			{
				if($r->idEstructura==$idEstructura)
				{
					foreach($r->regEstructura as $att)
					{
						$cad.=$nVariable.'["'.$att->nAtributo.'"]="";';
					}
				}
				
			}
		}
  
	}
	return $cad;
}

function obtenerReferenciaAlmacenDatos($arrQueries,$idAlmacen)
{
	$almacen=$arrQueries[$idAlmacen];
	if($almacen["ejecutado"]==1)
	{
		$resultado=$almacen["resultado"];
		$conector=$almacen["conector"];
		$conector->inicializarRecurso($resultado);
		$obj[0]=$resultado;
		$obj[1]=$conector;
		return $obj;
	}
	return NULL;	
}

function importeGravadoTotal($importe)
{
	$arr["importeGravado"]=$importe;
	$arr["importeExento"]=0;
	return $arr;
}

function importeExentoTotal($importe)
{
	$arr["importeGravado"]=0;
	$arr["importeExento"]=$importe;
	return $arr;
}


function registrarNotificacionNomina($idNomina,$notificacion)
{
	global $con;	
	$consulta="INSERT INTO 672_notificacionesNomina(idNomina,fechaOperacion,notificacion,situacion) VALUES(".$idNomina.",'".date("Y-m-d H:i:s")."','".cv($notificacion)."',1)";
	return $con->ejecutarConsulta($consulta);
}


function calcularNominaUsuarioV2(&$objUsuario)
{
	global $f;
	global $con;
	global $habilitarCache;
	global $arrCalculosDef;
	global $idPerfil;
	global $nPuestosCalculados;
	global $arrAcumuldoresGlobales;
	global $arrResultados;
	global $idNomina;
	global $arrCarga;
	global $cargarDeArchivo;
	global $arrFactores;
	global $precision;
	global $accionPrecision;
	
	global $acumuladoBaseGravablePercepcion;
	global $acumuladoBaseGravableDeduccion;
	$idUsuario=$objUsuario->idUsuario;
	
	
	
	
	
	if($con->existeTabla("9135_vistafactorusuario"))
	{
		$consulta="SELECT cveFactor,cantidad,idTipoValor FROM 9135_vistafactorusuario WHERE idUsuario=".$objUsuario->idUsuario." AND ciclo=".$objUsuario->ciclo." AND estado=0 AND periodo=".$objUsuario->quincena;
		$arrFactores=$con->obtenerFilasArregloAsocPHP($consulta,true);
	}
	else
		$arrFactores=array();
	
	
	
	
	
}



?>