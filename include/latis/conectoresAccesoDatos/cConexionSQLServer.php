<?php include_once("latis/conectoresAccesoDatos/cBaseConectorDatos.php");

	class cConexionSQLServer extends cBaseConectorDatos
	{
		function cConexionSQLServer($host,$puerto,$login,$passwd,$bd)
		{
			global $tipoServidor;
			if($puerto=="")
				$puerto=1433;
			$separadorPuerto=":";
			if($tipoServidor==2)
				$separadorPuerto=",";
			$this->conexion=@mssql_connect($host.$separadorPuerto.$puerto,$login,$passwd,true);
			if($this->conexion)
			{
				mssql_select_db($bd,$this->conexion);
				$this->filasAfectadas=0;
				$this->bdActual=$bd;
			}
		}
		
		function obtenerListaValoresCampoTabla($nCampo,$nTabla)
		{
			return "[]";
		}
		
		function obtenerValoresTabla($nTabla,$campoId,$campoEtiqueta)
		{
			$con=$this;
			$consulta="select ".$campoId.",".$campoEtiqueta." from ".$nTabla;
			return $con->obtenerFilasArreglo($consulta);
		}
		
		function esSistemaLatis()
		{
			return false;
		}		
		
		function obtenerValor($consulta)
		{
			$consulta=normalizarConsulta($consulta);
			$modoDebug=false;
			if(isset($_SESSION["debuggerConsulta"])&&($_SESSION["debuggerConsulta"]==1))
				$modoDebug=true;
			if($modoDebug)
				echo $consulta."<br>";
			$res=mssql_query($consulta,$this->conexion) or die("");
			$this->filasAfectadas=mssql_num_rows($res);
			if($fila=mssql_fetch_row($res))
				return $fila[0];
			else
				return "";
			return "";
		}
		
		function obtenerFilas($consulta)
		{
			$consulta=normalizarConsulta($consulta);
			$modoDebug=false;
			if(isset($_SESSION["debuggerConsulta"])&&($_SESSION["debuggerConsulta"]==1))
				$modoDebug=true;
			if($modoDebug)
				echo $consulta."<br>";
			$res=mssql_query($consulta,$this->conexion) or die("");
			$this->filasAfectadas=mssql_num_rows($res);
			return $res;
		}
		
		function obtenerFilasArreglo($consulta)
		{
			$consulta=normalizarConsulta($consulta);
			$modoDebug=false;
			if(isset($_SESSION["debuggerConsulta"])&&($_SESSION["debuggerConsulta"]==1))
				$modoDebug=true;
			if($modoDebug)
				echo $consulta."<br>";
			$res=mssql_query($consulta,$this->conexion) or die("");
			$this->filasAfectadas=mssql_num_rows($res);
			$obj;
			$arrObj="";
			while($fila=mssql_fetch_row($res))
			{
				$cols="";
				$ctCol=sizeof($fila);
				for($x=0;$x<$ctCol;$x++)
				{
					if($cols=="")
						$cols="'".str_replace("'","\\'",$fila[$x])."'";
					else
						$cols.=",'".str_replace("'","\\'",$fila[$x])."'";
					
				}
				if($arrObj=="")
					$arrObj='['.$cols.']';
				else
					$arrObj.=',['.$cols.']';
			}
			
			return '['.str_replace(chr(10),"",str_replace(chr(13),"", $arrObj)).']';
		}
		
		function obtenerSiguienteFila(&$res)
		{
			if($res==NULL)
				return NULL;
			return mssql_fetch_row($res);
		}
		
		function obtenerSiguienteFilaAsoc(&$res)
		{
			if($res==NULL)
				return NULL;
			return mssql_fetch_assoc($res);
		}
		
		function inicializarRecurso(&$res)
		{
			$con=$this;
			if($con->obtenerNumeroRegistros($res)>0)
			{
				mssql_data_seek($res,0);
			}
		}
		
		function obtenerNumeroRegistros($res)
		{
			return mssql_num_rows($res);
		}
		
		function obtenerListaValores($consulta,$caracter="")
		{
			$consulta=normalizarConsulta($consulta);
			$modoDebug=false;
			if(isset($_SESSION["debuggerConsulta"])&&($_SESSION["debuggerConsulta"]==1))
				$modoDebug=true;
			if($modoDebug)
				echo $consulta."<br>";
			$res=$this->obtenerFilas($consulta);
			$lista="";
			
				
			while($fila=mssql_fetch_row($res))
			{
				if($lista=="")
					$lista=$caracter.$fila[0].$caracter;
				else
					$lista.=",".$caracter.$fila[0].$caracter;
			}
			
			return $lista;
		}
		
		function obtenerPrimeraFila($consulta)
		{
			$consulta=normalizarConsulta($consulta);
			$modoDebug=false;
			if(isset($_SESSION["debuggerConsulta"])&&($_SESSION["debuggerConsulta"]==1))
				$modoDebug=true;
			if($modoDebug)
				echo $consulta."<br>";
			$res=mssql_query($consulta,$this->conexion) or die("");
			$this->filasAfectadas=mssql_num_rows($res);
			$fila=mssql_fetch_row($res);
			
			return $fila;
		}
		
		function obtenerPrimeraFilaAsoc($consulta)
		{
			$consulta=normalizarConsulta($consulta);
			$modoDebug=false;
			if(isset($_SESSION["debuggerConsulta"])&&($_SESSION["debuggerConsulta"]==1))
				$modoDebug=true;
			if($modoDebug)
				echo $consulta."<br>";
			$res=mssql_query($consulta,$this->conexion) or die("");
			$this->filasAfectadas=mssql_num_rows($res);
			$fila=mssql_fetch_assoc($res);
			return $fila;
		}
		
		function ejecutarConsulta($consulta)
		{
			$consulta=normalizarConsulta($consulta);
			$modoDebug=false;
			if(isset($_SESSION["debuggerConsulta"])&&($_SESSION["debuggerConsulta"]==1))
				$modoDebug=true;
			if($modoDebug)
				echo $consulta."<br>";
			if(!mssql_query($consulta,$this->conexion))
			{
				$consulta="rollback";
				$consulta=normalizarConsulta($consulta);
				mssql_query($consulta,$this->conexion);
				return false;
			}
			return true;
		}
		
		function obtenerFilasObjConector($consulta)
		{
			$consulta=normalizarConsulta($consulta);
			$modoDebug=false;
			if(isset($_SESSION["debuggerConsulta"])&&($_SESSION["debuggerConsulta"]==1))
				$modoDebug=true;
			if($modoDebug)
				echo $consulta."<br>";
			$res=mssql_query($consulta,$this->conexion) or die("");
			$this->filasAfectadas=mssql_num_rows($res);
			$obj[0]=$res;
			$obj[1]=$this;
			return $obj;
		}	
		
		function obtenerTablasSistemaJSON($filtro)//Revisar
		{
			ini_set('max_execution_time', 300);
			$filtrarTablas=false;
			$mostrarFrmSistema=true;
			$mostrarFrmDinamicos=true;
			$con=$this;
			$condWhereFD="";
			
			if($filtro!=NULL)
			{
				$nFiltros=sizeof($filtro);
				for($x=0;$x<$nFiltros;$x++)
				{
					$f=$filtro[$x];
					$campo=$f["field"];
					switch($campo)
					{
						case "tipoTabla":
							$mostrarFrmSistema=false;
							$mostrarFrmDinamicos=false;
							$arrCampos=explode(',',$f["data"]["value"]);
							$nCampos=sizeof($arrCampos);
							for($y=0;$y<$nCampos;$y++)
							{
								switch($arrCampos[$y])
								{
									case 1:
										$mostrarFrmDinamicos=true;
									break;
									case 2:
										$mostrarFrmSistema=true;
									break;
									
								}
							}
						break;
						case "tabla":
							$filtrarTablas=true;
							$condWhereFD.=" and titulo like '".$f["data"]["value"]."%'";
						break;
						case "proceso":
							$mostrarFrmSistema=false;
							$condWhereFD.=" and p.nombre like '".$f["data"]["value"]."%'";
						break;
					}
				}
		
			}
			$consulta="select table_name from ".$con->bdActual.".Information_Schema.Tables";
			$res=$con->obtenerFilas($consulta);
			$arrObj="";
			$arrTablas=array();
			$nReg=0;
			while($filas=mssql_fetch_row($res))
			{
				if($mostrarFrmSistema)
				{
					$nomTabla=$filas[0];
					$tipoTabla="Sistema";
					$nomTablaParaUsuario=$nomTabla;
					$nFormulario="N/A";
					$nProceso="N/A";
					if($filtrarTablas)
					{
						$nomTablaUsrMinus=strtolower($nomTablaParaUsuario);
						$posValor=strpos($nomTablaUsrMinus,strtolower($f["data"]["value"]));
						if($posValor===0)
						{
							$arrTablas[$nomTablaParaUsuario]["tablaOriginal"]=$nomTabla;
							$arrTablas[$nomTablaParaUsuario]["tipoTabla"]=$tipoTabla;
							$arrTablas[$nomTablaParaUsuario]["nProceso"]=$nProceso;
						}
					}
					else
					{
						$arrTablas[$nomTablaParaUsuario]["tablaOriginal"]=$nomTabla;
						$arrTablas[$nomTablaParaUsuario]["tipoTabla"]=$tipoTabla;
						$arrTablas[$nomTablaParaUsuario]["nProceso"]=$nProceso;
						
					}
				}
			}
			uksort($arrTablas,'compararCadenas');
			$arrObj="";
			foreach($arrTablas as $tabla=>$objTabla)
			{
				$nReg++;
				$arrTabla=explode("@",$tabla);
				$obj='{nomTablaOriginal:"'.$objTabla["tablaOriginal"].'",tabla:"'.ucfirst ($arrTabla[0]).'",tipoTabla:"'.$objTabla["tipoTabla"].'",
						proceso:"'.$objTabla["nProceso"].'"}';
				if($arrObj=="")
					$arrObj=$obj;
				else			
					$arrObj.=",".$obj;		
			}
			return '{"numReg":"'.$nReg.'","registros":['.$arrObj.']}';
		}
		
		function existeTabla($nomTabla)//Revisar
		{
			$con=$this;
			$consulta="select table_name from ".$con->bdActual.".Information_Schema.Tables where table_name='".$nomTabla."'";
			$fRegistro=$con->obtenerPrimeraFila($consulta);
			if($fRegistro)
				return true;
			else
				return false;
		}
		
		function obtenerCampoLlave($nTabla)//Revisar
		{
			$con=$this;
			$consulta="SELECT COLUMN_NAME FROM ".$con->bdActual.".information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME='".$nTabla."'";
			return $con->obtenerValor($consulta);

		}
		
		function existeCampo($campo,$nomTabla)//Revisar
		{
			$con=$this;
			$consulta="select COLUMN_NAME from ".$this->bdActual.".information_schema.COLUMNS where  TABLE_NAME='".$nomTabla."' and COLUMN_NAME='".$campo."'";
			$resultado=$this->obtenerValor($consulta);
			if($resultado!="")
				return true;
			else
				return false;
			
		}
		
		function obtenerCamposTabla($nTabla,$nLargo=false,$mostrarCamposCheck=false,$mostrarCamposControl=true,$formato=0)//Revisar
		{
			$con=$this;
			$esSistemaLatis=$con->esSistemaLatis();
			$camposCheck=",17,18,19";
			if($mostrarCamposCheck)
				$camposCheck="";
			if(!$esSistemaLatis)
				$tipoTabla="0";
			else
				$tipoTabla="1";
			$arrObj="";
			$arrCamposFinal=array();
			if($tipoTabla=="0")
			{
				$nTablaUsr=$nTabla.".";
				if($esSistemaLatis)
				{
					$arrDatosTabla=explode("_",$nTabla);
				
					$nTablaUsr=ucfirst($arrDatosTabla[1]).".";
				}
				if(!$nLargo)
					$nTablaUsr="";
					
				$consulta="SELECT COLUMN_NAME,COLUMN_NAME,DATA_TYPE,'0' as 'tipoCtrl' FROM 
							".$this->bdActual.".information_schema.COLUMNS WHERE TABLE_NAME='".$nTabla."'
							 ORDER BY COLUMN_NAME";
				$resObj=$con->obtenerFilas($consulta);
				$validarRelacion=false;
				if($con->existeTabla("9013_relacionesTablaSistema"))
				{
					$consulta="select idRelacionTabla from 9013_relacionesTablaSistema where tablaOrigen='".$nTabla."'";
					$resRelaciones=$con->obtenerFilas($consulta);
					
					if($con->filasAfectadas>0)
						$validarRelacion=true;
				}
				$cLlaveTabla=obtenerCampoLlave($nTabla);
				while($filaObj=mssql_fetch_row($resObj))
				{
					$tipoDato=normalizarTipoDato($filaObj[2]);
					if($validarRelacion)
					{
						$consulta="select * from 9013_relacionesTablaSistema where tablaOrigen='".$nTabla."' and campoOrigen='".$filaObj[0]."'";
						$filaRelacion=$con->obtenerPrimeraFila($consulta);
						if($filaRelacion)
							$tipoDato="optT";
					}
					if($nLargo)
						$objArreglo[0]=$nTabla.".".$filaObj[1];
					else
						$objArreglo[0]=$filaObj[1];
					
					$esCampoLlave=0;
					
					if($cLlaveTabla==$filaObj[0])
						$esCampoLlave=1;
					
					$objArreglo[1]=$tipoDato;
					$objArreglo[2]=$filaObj[3];
					$objArreglo[3]=$esCampoLlave;
					$objArreglo[4]=$nTabla;
					$objArreglo[5]=$tipoTabla;
					$arrCamposFinal[$nTablaUsr.$filaObj[0]]=$objArreglo;
				}
				
			}
			
			switch($formato)
			{
				case "0":
				
					$cadCampos="";
					foreach($arrCamposFinal as $r)
					{
						$obj="['".$r[0]."','".$r[0]."','".$r[1]."','".$r[2]."','".$r[3]."','".$r[4]."','".$r[5]."']";
						if($cadCampos=="")
							$cadCampos=$obj;
						else
							$cadCampos.=",".$obj;
					}
					return "[".$cadCampos."]";
				break;
				case "1":
					return $arrCamposFinal;
				break;
				case "2":
					$cadCampos="";
					foreach($arrCamposFinal as $r)
					{
						$obj='{"nombreMysql":"'.$r[0].'","nombreUsr":"'.$r[0].'","tipoCampo":"'.$r[1].'","tipoElemento":"'.$r[2].'","campoLlave":"'.$r[3].'","nTabla":"'.$r[4].'","tipoTabla":"'.$r[5].'"}';
						if($cadCampos=="")
							$cadCampos=$obj;
						else
							$cadCampos.=",".$obj;
					}
					return '{"registros":['.$cadCampos.']}';
				break;
			}
			return "[]";
		}
	
		function obtenerUltimoID()
		{
			$res=mssql_query("SELECT @@IDENTITY",$this->conexion) or die("");
			if($fila=mssql_fetch_row($res))
			{
				$id=$fila[0];
				return $id;
			}
			else
			{
				return "-1";
			}
		}		
		
		//Funciones aux;
		function normalizarConcatenacion($consultaTmp)
		{
			$arrConsulta=explode(" concat(",$consultaTmp);
			$resto=$arrConsulta[1];
			$cadAux="";
			$nComillasS=0;
			$nComillasD=0;
			$posFin=0;
			for($x=0;$x<strlen($resto);$x++)
			{
				if(($resto[$x]=="'")&&($nComillasD==0))
				{
					if(($x-2)>0)
					{
						$cadTmp=substr($resto,($x-2),3);
						if($cadTmp=="\\'")
						{
							if($nComillasS==0)
								$nComillasS++;
							else
								$nComillasS--;
						}
						else
						{
							$cadTmp=substr($resto,($x-1),2);
							if($cadTmp!="\'")
							{
								if($nComillasS==0)
									$nComillasS++;
								else
									$nComillasS--;
							}
						}
					}
					else
					{
						if(($x-1)>0)
						{
							$cadTmp=substr($resto,($x-1),2);
							if($cadTmp!="\'")
							{
								if($nComillasS==0)
									$nComillasS++;
								else
									$nComillasS--;
							}
						}
						else
						{
							if($nComillasS==0)
								$nComillasS++;
							else
								$nComillasS--;
						}
					}
				}
				
				if(($resto[$x]=='"')&&($nComillasS==0))
				{
					if(($x-2)>0)
					{
						$cadTmp=substr($resto,($x-2),3);
						if($cadTmp=='\\"')
						{
							if($nComillasD==0)
								$nComillasD++;
							else
								$nComillasD--;
						}
						else
						{
							$cadTmp=substr($resto,($x-1),2);
							if($cadTmp!='\"')
							{
								if($nComillasD==0)
									$nComillasD++;
								else
									$nComillasD--;
							}
						}
					}
					else
					{
						if(($x-1)>0)
						{
							$cadTmp=substr($resto,($x-1),2);
							if($cadTmp!='\"')
							{
								if($nComillasD==0)
									$nComillasD++;
								else
									$nComillasD--;
							}
						}
						else
						{
							if($nComillasD==0)
								$nComillasD++;
							else
								$nComillasD--;
						}
					}
				}
				
				
				if(($resto[$x]==')')&&($nComillasD==0)&&($nComillasS==0))
				{
					$posFin=$x;
					break;
				}
			}
			
			$particulaConcat=substr($resto,0,$x);
			$restoSentencia=substr($resto,($x+1));
			$arrElementos=array();
			$nComillasS=0;
			$nComillasD=0;
			$posIni=0;
			$resto=$particulaConcat;
			for($x=0;$x<strlen($resto);$x++)
			{
				if(($resto[$x]=="'")&&($nComillasD==0))
				{
					if(($x-2)>0)
					{
						$cadTmp=substr($resto,($x-2),3);
						if($cadTmp=="\\'")
						{
							if($nComillasS==0)
								$nComillasS++;
							else
								$nComillasS--;
						}
						else
						{
							$cadTmp=substr($resto,($x-1),2);
							if($cadTmp!="\'")
							{
								if($nComillasS==0)
									$nComillasS++;
								else
									$nComillasS--;
							}
						}
					}
					else
					{
						if(($x-1)>0)
						{
							$cadTmp=substr($resto,($x-1),2);
							if($cadTmp!="\'")
							{
								if($nComillasS==0)
									$nComillasS++;
								else
									$nComillasS--;
							}
						}
						else
						{
							if($nComillasS==0)
								$nComillasS++;
							else
								$nComillasS--;
						}
					}
				}
				
				if(($resto[$x]=='"')&&($nComillasS==0))
				{
					if(($x-2)>0)
					{
						$cadTmp=substr($resto,($x-2),3);
						if($cadTmp=='\\"')
						{
							if($nComillasD==0)
								$nComillasD++;
							else
								$nComillasD--;
						}
						else
						{
							$cadTmp=substr($resto,($x-1),2);
							if($cadTmp!='\"')
							{
								if($nComillasD==0)
									$nComillasD++;
								else
									$nComillasD--;
							}
						}
					}
					else
					{
						if(($x-1)>0)
						{
							$cadTmp=substr($resto,($x-1),2);
							if($cadTmp!='\"')
							{
								if($nComillasD==0)
									$nComillasD++;
								else
									$nComillasD--;
							}
						}
						else
						{
							if($nComillasD==0)
								$nComillasD++;
							else
								$nComillasD--;
						}
					}
				}
				
				if(($resto[$x]==',')&&($nComillasD==0)&&($nComillasS==0))
				{
					$posFin=$x;
					array_push($arrElementos,substr($resto,$posIni,($posFin-$posIni)));
					$posIni=($x+1);
				}
			}
			array_push($arrElementos,substr($resto,$posIni));
			
			$concatFinal="";
			foreach($arrElementos as $e)
			{
				if($concatFinal=="")
					$concatFinal="cast(".$e." as varchar)";
				else
					$concatFinal.="+cast(".$e." as varchar)";
			}
			$concatFinal='('.$concatFinal.')';
			
			return $arrConsulta[0]." ".$concatFinal." ".$restoSentencia;
			
			
		}
		
		function normalizarTipoDato($tDato)
		{
			return $tDato;
		}
		
		function normalizarConsulta($consulta)
		{
			$consultaTmp=normalizarEspacios(trim($consulta));

			if($consultaTmp=="begin")
				$consultaTmp="begin transaction";
			else
			{
				if($consultaTmp=="commit")
					$consultaTmp="commit transaction";
				else
				{
					if($consultaTmp=="rollback")
						$consultaTmp="rollback transaction";
				}
			}
			if(strpos($consultaTmp," concat(")!==false)
			{
				$consultaTmp=normalizarConcatenacion($consultaTmp);
			}
			if(strpos($consultaTmp," limit ")!==false)
			{
				$arrConsulta=explode(" limit ",$consultaTmp);
				$arrLimites=explode(",",trim($arrConsulta[1]));
				$consultaTmp=$arrConsulta[0];
				$consultaTmp=str_replace("select ","select TOP ".$arrLimites[1]." ",$consultaTmp);
			}
			
			$consultaTmp=str_replace(":=","=",$consultaTmp);
			
			return $consultaTmp;
		}
	}
	
?>