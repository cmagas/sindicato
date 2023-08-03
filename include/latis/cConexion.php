<?php 
	include("latis/utiles.php"); 
	
	class cConexion
	{
		var $filasAfectadas;
		var $conexion;	
		var $bdActual;
				
		function cConexion($host,$login,$passwd,$bd,$nuevaConexion=false)  //constructor de la clase cConexion
		{
			
			$this->conexion=mysql_connect($host,$login,$passwd,$nuevaConexion) or die(mysql_error());
			
			mysql_select_db($bd,$this->conexion);
			mysql_query("SET NAMES UTF8");
			$this->filasAfectadas=0;
			$this->bdActual=$bd;
		}
		
		function contarRegistros($consulta) 
		{
			$res = mysql_query ( $consulta, $this->conexion ) or die ( "Imposibe realizar esta acci�n (Obtener): " . mysql_error () );
			
			guardarBitacoraConsultaBD($consulta);
			
			return mysql_num_rows($res);
		}
		
		function obtenerRegistros($consulta) 
		{
			$result = mysql_query ( $consulta,$this->conexion ) or die ( "Imposibe realizar esta acci�n (Obtener): " . mysql_error () );
			$row = mysql_fetch_row( $result );
			mysql_free_result ( $result );
			guardarBitacoraConsultaBD($consulta);
			return $row;
		}
	
		function llenarComboChecado($consulta,$item)
		{
			$result = mysql_query ( $consulta, $this->conexion ) or die ( "Imposible realizar esta acci�n (Llenar): " . mysql_error () );
			while ( $row = mysql_fetch_row ( $result) )
			{
				if($item==$row[0])
					$s="selected";
				else
					$s="";
					
				echo"<option value='".$row[0]."' ".$s.">".($row[1])."</option>";
			}
		}
	
		function obtenerUltimoID()	//permite obtener el ultimo ID del elemento insertado en la BD
		{
			$res=mysql_query("select last_insert_id()",$this->conexion) or die(mysql_error());
			if($fila=mysql_fetch_row($res))
			{
				$id=$fila[0];
				return $id;
			}
			else
			{
				return "-1";
			}
		}	
	
		function existeRegistro($consulta) //indica si existe o no un registro que coincida con la consulta ingresada
		{
			$res=mysql_query($consulta,$this->conexion) or die(mysql_error());
			if($fila=mysql_fetch_row($res))
				return true;
			else
				return false;
		} 
	
		function obtenerValor($consulta) //Devuelve el primer valor del primer registro, de los campos proyectados en una consulta select
		{
			$modoDebug=false;
			if(isset($_SESSION["debuggerConsulta"])&&($_SESSION["debuggerConsulta"]==1))
				$modoDebug=true;
			if($modoDebug)
				echo $consulta."<br>";

			$res=mysql_query($consulta,$this->conexion) or die(mysql_error());
			$this->filasAfectadas=mysql_num_rows($res);
			if($fila=mysql_fetch_row($res))
				return $fila[0];
			else
				return "";
		} 
	
		function obtenerFilas($consulta,$log=true) //DEveulve un recordset con las filas devueltas por la consulta
		{
			//echo $consulta;
			$modoDebug=false;
			if(isset($_SESSION["debuggerConsulta"])&&($_SESSION["debuggerConsulta"]==1))
				$modoDebug=true;
			if($modoDebug)
				echo $consulta."<br>";
			$res=mysql_query($consulta,$this->conexion) or die(mysql_error());
			$this->filasAfectadas=mysql_num_rows($res);
			//echo mysql_info($this->conexion);
			if($log)
				guardarBitacoraConsultaBD($consulta);
			return $res;
		}		
			
		function obtenerPrimeraFila($consulta)
		{
			//echo $consulta;
			$modoDebug=false;
			if(isset($_SESSION["debuggerConsulta"])&&($_SESSION["debuggerConsulta"]==1))
				$modoDebug=true;
			if($modoDebug)
				echo $consulta."<br>";
			$res=mysql_query($consulta,$this->conexion) or die(mysql_error());
			$this->filasAfectadas=mysql_num_rows($res);
			$fila=mysql_fetch_row($res);
			guardarBitacoraConsultaBD($consulta);
			return $fila;
		}
				
		function obtenerFilasJson($consulta)
		{
			$modoDebug=false;
			if(isset($_SESSION["debuggerConsulta"])&&($_SESSION["debuggerConsulta"]==1))
				$modoDebug=true;
			if($modoDebug)
				echo $consulta."<br>";
			$res=mysql_query($consulta,$this->conexion) or die(mysql_error());
			
			$this->filasAfectadas=mysql_num_rows($res);
			guardarBitacoraConsultaBD($consulta);
			return $this->cjv($res);
		}
	
		function ejecutarConsulta($consulta) //Ejecuta cualquier consulta enla BD
		{
			$modoDebug=false;
			if(isset($_SESSION["debuggerConsulta"])&&($_SESSION["debuggerConsulta"]==1))
				$modoDebug=true;
			
			if(!mysql_query($consulta,$this->conexion))
			{
				echo mysql_error();
				$consulta="rollback";
				mysql_query($consulta,$this->conexion);
				return false;
			}
			guardarBitacoraModificacionBD($consulta);
			return true;
			
		}
		
		function ejecutarConsultaLog($consulta) //Ejecuta cualquier consulta enla BD
		{
			if(!mysql_query($consulta,$this->conexion))
			{
				echo mysql_error();
				$consulta="rollback";
				mysql_query($consulta,$this->conexion);
				return false;
			}
			return true;
		}
	
		function ejecutarBloque($consultaBloque,$modoDebug=false) //Ejecutar el conjulto de consultas que se le pases como parametro
		{
			$modoDebug=false;

			if(isset($_SESSION["debuggerBloque"])&&($_SESSION["debuggerBloque"]==1)&&((!isset($_SESSION["debuggerConsulta"]))||($_SESSION["debuggerConsulta"]==0)))
				$modoDebug=true;
			$ct=sizeof($consultaBloque);
			for($x=0;$x<$ct;$x++)
			{
				if($modoDebug)
					echo $consultaBloque[$x]."<br>";
				if(!$this->ejecutarConsulta($consultaBloque[$x]))
				{
					echo "|";
					return false;
				}
			}
			return true;
		}
		
		//Recomendada cuando no se conoce el ultimo Id ingresado
		function agregarRegistro($consulta) 
		{
			$result = mysql_query ( $consulta, $this->conexion ) or die ( "Imposibe realizar esta acci�n (agregar): " . mysql_error () );
			$r = mysql_query ( "SELECT LAST_INSERT_ID()", $this->conexion) or die ( "Imposibe realizar esta acci�n: " . mysql_error () );
			while ( $row = mysql_fetch_row ( $r ) )
				$res = $row [0];
			guardarBitacoraModificacionBD($consulta);
			return $res;
		}
	
		function obtenerListaValores($consulta,$caracter="") //Devuelve una cadena separada por comas (,) con la primera columna de cada fila devuelta por la consulta
		{
			$modoDebug=false;
			if(isset($_SESSION["debuggerConsulta"])&&($_SESSION["debuggerConsulta"]==1))
				$modoDebug=true;
			if($modoDebug)
				echo $consulta."<br>";
			$res=$this->obtenerFilas($consulta);
			$lista="";
			
				
			while($fila=mysql_fetch_row($res))
			{
				if($lista=="")
					$lista=$caracter.$fila[0].$caracter;
				else
					$lista.=",".$caracter.$fila[0].$caracter;
			}
			
			return $lista;
		}
		
		function generarOpcionesSelect($consulta,$item="-1",$mostrarNoExiste=false)
		{
			$result = $this->obtenerFilas($consulta);
			while ( $row = mysql_fetch_row ( $result ) )
			{
				if($item==$row[0])
					$s="selected='selected'";
				else
					$s="";
					
				echo"<option value='".$row[0]."' ".$s.">".$row[1]."</option>";
			}
			if(($this->filasAfectadas==0)&&($mostrarNoExiste))
				echo"<option value='-1'>No existen Opciones</option>";
		}
		
		function generarOpcionesSelectNoImp($consulta,$item="-1")
		{
			$result = $this->obtenerFilas($consulta);
			$arrOpciones="";
			while ( $row = mysql_fetch_row ( $result ) )
			{
				if($item==$row[0])
					$s="selected";
				else
					$s="";
					
				$arrOpciones.="<option value='".$row[0]."' ".$s.">".$row[1]."</option>";
			}
			return $arrOpciones;
		}
		
		function generarFilasTabla($consulta)
		{
			$res=$this->obtenerFilas($consulta);
			$filasT="";
			while($fila=mysql_fetch_row($res))
			{
				$ct=sizeof($fila);
				$filasT.="<tr>";
				for($x=0;$x<$ct;$x++)
				{
					$filasT.="<td>".$fila[$x]."</td>";
				}
				$filasT.="</tr>";
			}
			echo $filasT;
		}
		
		function cjv($dataSet)
		{
			$arrObj="";
			$obj="";
			$ct;
			$cmp="";
			while($fila=mysql_fetch_row($dataSet))
			{
				$ct=sizeof($fila);
				$obj="{";
				$arrCmp="";
				for($x=0;$x<$ct;$x++)
				{
					$nomCampo=mysql_field_name($dataSet,$x);
					$cmp='"'.$nomCampo.'":"'.dv(str_replace('"','"',$fila[$x])).'"';
					if($arrCmp=="")
						$arrCmp=$cmp;
					else
						$arrCmp.=",".$cmp;
						
					
				}
				$obj.=$arrCmp."}";
				if($arrObj=="")
					$arrObj=$obj;
				else
					$arrObj.=",".$obj;	
			}
			return "[".$arrObj."]";
		}
		
		function generarNumeracionSelectNoImp($inicio,$fin,$elemSel="-1",$intervalo=1)
		{
			$arrOpciones="";
			if($inicio<$fin)
			{
				for($x=$inicio;$x<=$fin;$x+=$intervalo)
				{
					
					if($x==$elemSel)
						$arrOpciones.='<option value="'.$x.'" selected>'.$x.'</option>';
					else
						$arrOpciones.='<option value="'.$x.'">'.$x.'</option>';
				}
			}
			else
			{
				for($x=$inicio;$x>=$fin;$x-=$intervalo)
				{
					
					if($x==$elemSel)
						$arrOpciones.='<option value="'.$x.'" selected>'.$x.'</option>';
					else
						$arrOpciones.='<option value="'.$x.'">'.$x.'</option>';
				}
			}
			return $arrOpciones;
		}
		
		function generarNumeracionSelect($inicio,$fin,$elemSel="-1",$intervalo=1)
		{
			$arrOpciones="";
			if($inicio<$fin)
			{
				for($x=$inicio;$x<=$fin;$x+=$intervalo)
				{
					
					if($x==$elemSel)
						$arrOpciones.='<option value="'.$x.'" selected>'.$x.'</option>';
					else
						$arrOpciones.='<option value="'.$x.'">'.$x.'</option>';
				}
			}
			else
			{
				for($x=$inicio;$x>=$fin;$x-=$intervalo)
				{
					
					if($x==$elemSel)
						$arrOpciones.='<option value="'.$x.'" selected>'.$x.'</option>';
					else
						$arrOpciones.='<option value="'.$x.'">'.$x.'</option>';
				}
			}
			echo $arrOpciones;
		}
		
		function generarOpcionesSelectArray($arreglo,$elemSel="-1")
		{
			$ct=sizeof($arreglo);
			for($x=0;$x<$ct;$x++)
			{
				if(($x+1)==$elemSel)
					echo '<option value="'.($x+1).'" selected>'.$arreglo[$x].'</option>';
				else
					echo '<option value="'.($x+1).'">'.$arreglo[$x].'</option>';
			}
		}
		
		function generarOpcionesSelectArreglo($arreglo,$elemSel="-1")
		{
			$ct=sizeof($arreglo);
			for($x=0;$x<$ct;$x++)
			{
				if($arreglo[$x][0]==$elemSel)
					echo '<option value="'.$arreglo[$x][0].'" selected>'.$arreglo[$x][1].'</option>';
				else
					echo '<option value="'.$arreglo[$x][0].'">'.$arreglo[$x][1].'</option>';
			}
		}
		
		function generarOpcionesSelectArregloNoImp($arreglo,$elemSel="-1")
		{
			$ct=sizeof($arreglo);
			$arrOpciones="";
			for($x=0;$x<$ct;$x++)
			{
				if($arreglo[$x][0]==$elemSel)
					$arrOpciones.='<option value="'.$arreglo[$x][0].'" selected>'.$arreglo[$x][1].'</option>';
				else
					$arrOpciones.='<option value="'.$arreglo[$x][0].'">'.$arreglo[$x][1].'</option>';
			}
			return $arrOpciones;
		}
		
		function generarOpcionesSelectArregloAsoc($arreglo,$elemSel="-1")
		{
			foreach($arreglo as $valor=>$texto)
			{
				if($valor==$elemSel)
					echo '<option value="'.$valor.'" selected>'.$texto.'</option>';
				else
					echo '<option value="'.$valor.'">'.$texto.'</option>';
			}
		}
		
		function generarOpcionesSelectArregloAsocNoImp($arreglo,$elemSel="-1")
		{
			$arrOpciones="";
			foreach($arreglo as $valor=>$texto)
			{
				if($valor==$elemSel)
					$arrOpciones.='<option value="'.$valor.'" selected>'.$texto.'</option>';
				else
					$arrOpciones.='<option value="'.$valor.'">'.$texto.'</option>';
			}
			return $arrOpciones;
		}
		
		function obtenerFilasArreglo($consulta)
		{
			$modoDebug=false;
			if(isset($_SESSION["debuggerConsulta"])&&($_SESSION["debuggerConsulta"]==1))
				$modoDebug=true;
			if($modoDebug)
				echo $consulta."<br>";
			$res=mysql_query($consulta,$this->conexion) or die(mysql_error());
			$this->filasAfectadas=mysql_num_rows($res);
			$obj;
			$arrObj="";
			while($fila=mysql_fetch_row($res))
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
		
		function obtenerFilasArregloPHP($consulta)
		{
			$modoDebug=false;
			if(isset($_SESSION["debuggerConsulta"])&&($_SESSION["debuggerConsulta"]==1))
				$modoDebug=true;
			if($modoDebug)
				echo $consulta."<br>";
			$res=$this->obtenerFilas($consulta);
			$this->filasAfectadas=mysql_num_rows($res);
			$arreglo=null;
			$pos=0;
			while($fila=mysql_fetch_row($res))
			{
				$numCol=sizeof($fila);
				for($ct=0;$ct<$numCol;$ct++)
				{
					$arreglo[$pos][$ct]=$fila[$ct];
				}
				$pos++;
			}
			
			return $arreglo;
		}		
		
		function obtenerFilasArregloAsocPHP($consulta,$todasDimensiones=false)
		{
			$modoDebug=false;
			if(isset($_SESSION["debuggerConsulta"])&&($_SESSION["debuggerConsulta"]==1))
				$modoDebug=true;
			if($modoDebug)
				echo $consulta."<br>";
			$res=$this->obtenerFilas($consulta);
			$this->filasAfectadas=mysql_num_rows($res);
			$arreglo=null;
			
			while($fila=mysql_fetch_row($res))
			{
				$numCol=sizeof($fila);
				if($todasDimensiones)
				{
					$arreglo[$fila[0]]=array();
					for($ct=1;$ct<$numCol;$ct++)
					{
						array_push($arreglo[$fila[0]],$fila[$ct]);
					}
				}
				else
					$arreglo[$fila[0]]=$fila[1];
			}
			
			return $arreglo;
		}
		
		function existeTabla($nomTabla)
		{
			$consulta="select count(VERSION) from information_schema.TABLES where TABLE_SCHEMA='".$this->bdActual."' and TABLE_NAME='".$nomTabla."'";

			$numFilas=$this->obtenerValor($consulta);
			if($numFilas==0)
				return false;
			else
				return true;
		}
		
		function generarArreglo($consulta)
		{
			$res=$this->obtenerFilas($consulta);
			$pos=0;
			$arreglo=null;
			while($fila=mysql_fetch_row($res))
			{
				$arreglo[$pos][0]=$fila[0];
				$arreglo[$pos][1]=$fila[1];
				$pos++;
			}
			
			return $arreglo;
		}
		
		function obtenerFilasArreglo1D($consulta)
		{
			
			$res=$this->obtenerFilas($consulta);
			$pos=0;
			$arreglo=null;
			while($fila=mysql_fetch_row($res))
			{
				$arreglo[$pos]=$fila[0];
				$pos++;
			}
			return $arreglo;
		}
	
		function generarOpcionesSelectMatriz($matriz,$elemSel="-1",$colProy="1")
		{
			$ct=sizeof($matriz);
			for($x=0;$x<$ct;$x++)
			{
				if($matriz[$x][0]==$elemSel)
					echo '<option value="'.$matriz[$x][0].'" selected>'.$matriz[$x][$colProy].'</option>';
				else
					echo '<option value="'.$matriz[$x][0].'">'.$matriz[$x][$colProy].'</option>';
			}
		}

		function existeCampo($campo,$nomTabla)
		{
			$consulta="select ORDINAL_POSITION from information_schema.COLUMNS where TABLE_SCHEMA='".$this->bdActual."' and TABLE_NAME='".$nomTabla."' and COLUMN_NAME='".$campo."'";
			$posicion=$this->obtenerValor($consulta);
			return $posicion;
		}
		
		function depurarBloque($arrQuery)	
		{
			foreach($arrQuery as $c)
			{
				echo $c."<br>\r\n";
			}
		}
		
		function cT()
		{
			return $this->comenzarTransaccion();
		}
		
		function fT()
		{
			return $this->finalizarTransaccion();
		}
		
		function comenzarTransaccion()
		{
			$consulta="begin";
			return $this->ejecutarConsulta($consulta);
		}
		
		function finalizarTransaccion()
		{
			$consulta="commit";
			return $this->ejecutarConsulta($consulta);
		}
		
		function obtenerTablasSistemaJSON($filtro)
		{
			ini_set('max_execution_time', 300);
			$con=$this;
			$mostrarFrmSistema=true;
			$mostrarFrmDinamicos=true;
			$filtrarTablas=false;
			$condWhereFD="";
			
			$esSistemaLatis=$this->esSistemaLatis();
			
			
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
			$consulta="select TABLE_NAME from information_schema.TABLES where TABLE_SCHEMA='".$con->bdActual.
					"' union select TABLE_NAME from information_schema.VIEWS where TABLE_SCHEMA='".$con->bdActual."'";
			
			$res=$con->obtenerFilas($consulta);
			
			$arrObj="";
			$arrTablas=array();
			$nReg=0;
			while($filas=mysql_fetch_row($res))
			{
				$nomTabla=$filas[0];
				$arrTable= explode("_",$nomTabla);
				if((sizeof($arrTable)>1)||(!$esSistemaLatis))
				{
					if((strpos($nomTabla,'tablaDinamica')!==false)&&($esSistemaLatis))
					{
						if($mostrarFrmDinamicos)	
						{
							$query="select concat(nombreFormulario,'@',f.idFormulario) as titulo,nombreFormulario,p.nombre from 900_formularios f,
										4001_procesos p where p.idProceso=f.idProceso and nombreTabla='".$nomTabla."'".$condWhereFD." and situacion=1";
							$filaFrm=$con->obtenerPrimeraFila($query);
							if($filaFrm)
							{
								$tipoTabla="Formulario Din&aacute;mico";
								$nFormulario=$filaFrm[1];
								$nProceso=$filaFrm[2];
								$nomTablaParaUsuario=$filaFrm[0];
								if($nFormulario=='')
									$nFormulario=$nomTabla;
								$arrTablas[$nomTablaParaUsuario]["tablaOriginal"]=$nomTabla;
								$arrTablas[$nomTablaParaUsuario]["tipoTabla"]=$tipoTabla;
								$arrTablas[$nomTablaParaUsuario]["nProceso"]=$nProceso;
							}
						}
					}
					else
					{
						if(($nomTabla[0]!="_")||(!$esSistemaLatis))
						{
							if($mostrarFrmSistema)
							{
								$tipoTabla="Sistema";
								
								$nomTablaParaUsuario="";
								if(!$esSistemaLatis)
									$nomTablaParaUsuario=$nomTabla;
								else
								{
									//$nomTablaParaUsuario=$arrTable[1]."@".$arrTable[0];
									$tablaSistema="";
									for($x=1;$x<sizeof($arrTable);$x++)
									{
										if($tablaSistema=="")
											$tablaSistema=$arrTable[$x];
										else
											$tablaSistema.="_".$arrTable[$x];
									}
									$nomTablaParaUsuario=$tablaSistema."@".$arrTable[0];
								}
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
						else
						{
							
							if(($mostrarFrmSistema)&&($esSistemaLatis))
							{
	
								$tipoTabla="Asociado a Proceso";
								
								$tablaSistema="";
								for($x=2;$x<sizeof($arrTable);$x++)
								{
									if($tablaSistema=="")
										$tablaSistema=$arrTable[$x];
									else
										$tablaSistema.="_".$arrTable[$x];
								}
								
								$nomTablaParaUsuario=$tablaSistema."@".$arrTable[1];

								$nFormulario="N/A";
								$consulta="select nombre,p.situacion from 900_formularios f,4001_procesos p where idFormulario=".$arrTable[1]."
											and p.idProceso=f.idProceso and p.situacion=1";
								
								$filaProceso=$con->obtenerPrimeraFila($consulta);
								if($filaProceso)
								{
									$nProceso=$filaProceso[0];
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
						}
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
		
		function obtenerCamposTabla($nTabla,$nLargo=false,$mostrarCamposCheck=false,$mostrarCamposControl=true,$formato=0)//0 Arreglo javascript, 1 Aregglo php, 2 cadenaJSON
		{
			$con=$this;
			$esSistemaLatis=$con->existeTabla("900_formularios");
			$camposCheck=",17,18,19";
			if($mostrarCamposCheck)
				$camposCheck="";
			if((strpos($nTabla,"tablaDinamica")===false)||(!$esSistemaLatis))
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
					
				$consulta="SELECT COLUMN_NAME,COLUMN_NAME,DATA_TYPE,'0' as 'tipoCtrl',IF(COLUMN_KEY='PRI','1','0') AS campoLlave FROM 
							information_schema.COLUMNS WHERE TABLE_SCHEMA='".$this->bdActual."' AND TABLE_NAME='".$nTabla."'
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
				while($filaObj=mysql_fetch_row($resObj))
				{
					$tipoDato=$filaObj[2];
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
					
					
					$objArreglo[1]=$tipoDato;
					$objArreglo[2]=$filaObj[3];
					$objArreglo[3]=$filaObj[4];
					$objArreglo[4]=$nTabla;
					$objArreglo[5]=$tipoTabla;
					$arrCamposFinal[$nTablaUsr.$filaObj[0]]=$objArreglo;
				}
				
			}
			else
			{
				$arrDatosTabla=explode("_",$nTabla);
				$consulta="select idFormulario,nombreFormulario from 900_formularios where nombreTabla='".$nTabla."'";
				$filaFrm=$con->obtenerPrimeraFila($consulta);
				$idFormulario=$filaFrm[0];
				//if($numTablasAsoc>1)
				$nTablaUsr=$filaFrm[1].".";
				if(!$nLargo)
					$nTablaUsr="";
				$compUnion="union
								(
									select campoUsr,campoMysql,tipoDato,tipoElemento,if(campoLlave=0,'0','1') from 9017_camposControlFormulario
								)";
				if(!$mostrarCamposControl)
					$compUnion="";
				$consulta="	select * from ((select nombreCampo as campoUsr,nombreCampo as campoMysql,
								( 
									case tipoElemento 
										when 2 then 'optM'
										when 3 then 'int'
										when 4 then 'optT'
										when 5 then 'varchar'
										when 6 then 'int'
										when 7 then 'decimal'
										when 8 then 'date'
										when 9 then 'varchar'
										when 10 then 'varchar'
										when 11 then 'varchar'
										when 12 then 'archivo'
										when 14 then 'optM'
										when 15 then 'int'
										when 16 then 'optT'
										when 21 then 'time'
										when 22 then 'decimal'
										when 24 then 'decimal'
										when 31 then 'varchar'
									end
								) as tipo,tipoElemento,'No' as campoLlave
								from 901_elementosFormulario where idFormulario=".$idFormulario." and tipoElemento not in (-2,-1,0,1,13,20".$camposCheck.")) ".$compUnion."
								) as tmp order by campoUsr
								
								";
				$filaObj[0]="id_".$nTabla;
				$filaObj[1]="id_".$nTabla;
				$filaObj[2]="int";
				$filaObj[3]='3';
				$filaObj[4]='1';
				
				
				if($nLargo)
					$objArreglo[0]=$nTabla.".".$filaObj[1];
				else
					$objArreglo[0]=$filaObj[1];
				$objArreglo[1]=$filaObj[2];
				$objArreglo[2]=$filaObj[3];
				$objArreglo[3]=$filaObj[4];
				$objArreglo[4]=$nTabla;
				$objArreglo[5]=$tipoTabla;
				
				$arrCamposFinal[$nTablaUsr.$filaObj[0]]=$objArreglo;
					
				$res=$con->obtenerFilas($consulta);
				while($filaObj=mysql_fetch_row($res))
				{
					if($nLargo)
						$objArreglo[0]=$nTabla.".".$filaObj[1];
					else
						$objArreglo[0]=$filaObj[1];
					$objArreglo[1]=$filaObj[2];
					$objArreglo[2]=$filaObj[3];
					$objArreglo[3]=$filaObj[4];
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
		}
		
		function obtenerListaValoresCampoTabla($nCampo,$nTabla)
		{
			$con=$this;
			$cadValores="-1";
			if((strpos($nTabla,"tablaDinamica")===false)||(!$this->esSistemaLatis()))
			{
				$tipoTabla="0";
				if($this->esSistemaLatis())
				{
					if((strpos($nTabla,"_")!==false)&&($nCampo=="idOpcion"))
					{
						$arrTabla=explode("_",$nTabla);

						if($this->existeTabla("_".$arrTabla[1]."_tablaDinamica"))
						{
							$tipoTabla=2;
							$nCampo=$arrTabla[2];
							
						}
					}
				}
			}
			else
				$tipoTabla="1";
				
			if(($tipoTabla==1)||($tipoTabla==2))
			{
				$arrTabla=explode("_",$nTabla);
				$idFormulario=$arrTabla[1];
				$idProceso=obtenerIdProcesoFormulario($idFormulario,$con);
				$cadObj='{"p16":{"p1":"'.$idFormulario.'","p2":"'.$idProceso.'","p3":"-1","p4":"-1","p5":"-1","p6":"-1"}}';
				$paramObj=json_decode($cadObj);	
				$arrQueries=resolverQueries($idFormulario,5,$paramObj,true,true,true,$this);
				
				
				$consulta="SELECT idGrupoElemento,tipoElemento FROM 901_elementosFormulario WHERE idFormulario=".$idFormulario." AND nombreCampo='".$nCampo."'";
				$fElemento=$con->obtenerPrimeraFila($consulta);
				$idGrupoElemento=$fElemento[0];
				$tElemento=$fElemento[1];
				switch($tElemento)
				{
					case "2":
					case "14":
					case "17":
						$consulta="select * from 904_configuracionElemFormulario where idElemFormulario=".$idGrupoElemento;
						$filaE=$con->obtenerPrimeraFila($consulta);
						
						$consulta="select tokenMysql from 913_consultasFiltroElemento where idGrupoElemento=".$idGrupoElemento;
						$resFiltro=$con->obtenerFilas($consulta);
						$condWhere="";
						while($filaFiltro=mysql_fetch_row($resFiltro))
							$condWhere.=$filaFiltro[0]." ";
						$orden="";	
						switch($filaE[2])
						{
							case "":
							case "0":
								$orden="order by contenido";
							break;
							case "1":
								$orden="order by valor";
							break;
							case "2":
							break;
						}	
							
						if($condWhere=='')
							$queryOpt="select valor,contenido from 902_opcionesFormulario where idGrupoElemento=".$idGrupoElemento." and idIdioma=".$_SESSION["leng"]." ".$orden;
						else
							$queryOpt="select valor,contenido from 902_opcionesFormulario where idGrupoElemento=".$idGrupoElemento." and ".$condWhere." and idIdioma=".$_SESSION["leng"]." ".$orden;
						
						$cadValores=$con->obtenerFilasArreglo($queryOpt);
					break;
					case "3":
					case "15":
					case "18":
						$queryOpt="select * from 904_configuracionElemFormulario where idElemFormulario=".$idGrupoElemento;		
						$filaE=$con->obtenerPrimeraFila($queryOpt);
						$obj="";
						$cadAux="";
						for($x=$filaE[2];$x<=$filaE[3];$x+=$filaE[4])
						{
							$obj="['".$x."','".$x."']";
							if($cadAux=="")
								$cadAux=$obj;
							else
								$cadAux.=",".$obj;
						}
						$cadValores="[".$cadAux."]";
						
						
					break;
					case "4":
					case "16":
					case "19":
						
						$consulta="select * from 904_configuracionElemFormulario where idElemFormulario=".$idGrupoElemento;
						$filaE=$con->obtenerPrimeraFila($consulta);
						$tablaOD=$filaE[2];
						$campoProy=$filaE[3];
						$campoId=$filaE[4];
						$campoFiltro=$filaE[8];
						$condicionFiltro=$filaE[7];
						$controlFiltro="_".$filaE[6]."vch";
						$controlDestino=$fElemento[1];
						$autocompletar=$filaE[9];
						$cBusqueda=$filaE[10];
						$anchoC=$filaE[11];
						$camposEnvio=$filaE[15];
						$condicionFiltroComp=$filaE[16];
						
						$consulta="select tokenMysql from 913_consultasFiltroElemento where idGrupoElemento=".$idGrupoElemento;
						$resFiltro=$con->obtenerFilas($consulta);
						$condWhere="";
						while($filaFiltro=mysql_fetch_row($resFiltro))
							$condWhere.=$filaFiltro[0]." ";
						$obtenerOpciones=true;
						$arrElemento="";
						
						if(strpos($tablaOD,"[")===false)
						{
							if($condWhere=="")
								$query="select ".$campoId.",".$campoProy." from ".$tablaOD." order by ".$campoProy;	
							else
								$query="select ".$campoId.",".$campoProy." from ".$tablaOD." where ".$condWhere." order by ".$campoProy;	
							$cadValores=$con->obtenerFilasArreglo($query);
							
						}
						else
						{
							$tablaOD=str_replace("[","",$tablaOD);
							$tablaOD=str_replace("]","",$tablaOD);
							$arrCamposProy=split('@@',$filaE[3]);
							if($arrQueries[$tablaOD]["ejecutado"]==1)
							{
								if($arrQueries[$tablaOD]["filasAfectadas"]>0)
									mysql_data_seek($arrQueries[$tablaOD]["resultado"],0);
								$resQuery=$arrQueries[$tablaOD]["resultado"];
								
								
								$cadObj='{"conector":null,"resQuery":null,"idAlmacen":"","arrCamposProy":[],"formato":"1","imprimir":"0","campoID":"'.$campoId.'"}';
								$obj=json_decode($cadObj);
								$obj->resQuery=$arrQueries[$tablaOD]["resultado"];
								$obj->idAlmacen=$tablaOD;
								$obj->arrCamposProy=$arrCamposProy;
								$obj->itemSelect="-1";
								$obj->conector=$arrQueries[$tablaOD]["conector"];
								$cadValores="[".generarFormatoOpcionesQuery($obj,$this)."]";
							}
						}	
					break;
				}
			}
			
			
			if($cadValores==-1)
			{
				if($this->esSistemaLatis())
				{
					$consulta="SELECT tablaVinculo,campoVinculo,campoProyeccion FROM 9013_relacionesTablaSistema WHERE tablaOrigen='".$nTabla."' AND campoOrigen='".$nCampo."'";
					$fConsulta=$con->obtenerPrimeraFila($consulta);
					if($fConsulta)
					{
						$consulta="select ".$fConsulta[1].",".$fConsulta[2]." from ".$fConsulta[0];
						$cadValores=$con->obtenerFilasArreglo($consulta);
					}
				}
			}
			
			return $cadValores;
		}
	
		function obtenerValoresTabla($nTabla,$campoId,$campoEtiqueta)
		{
			$con=$this;
			$consulta="select ".$campoId.",".$campoEtiqueta." from ".$nTabla;
			return $con->obtenerFilasArreglo($consulta);
		}
	
		function obtenerCampoLlave($nTabla)
		{
			$con=$this;
			$consulta="SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='".$this->bdActual."' AND TABLE_NAME='".$nTabla."' and COLUMN_KEY='PRI'";
			return $con->obtenerValor($consulta);
		}
		
		function esSistemaLatis()
		{
			$con=$this;
			return (($con->existeTabla("900_formularios"))&&($con->existeTabla("901_elementosFormulario")));
		}
		
		function obtenerSiguienteFila(&$res)
		{
			if($res==NULL)
				return NULL;
			return mysql_fetch_row($res);
			
		}
		
		function obtenerSiguienteFilaAsoc(&$res)
		{
			if($res==NULL)
				return NULL;
			return mysql_fetch_assoc($res);
			
		}
		
		function inicializarRecurso(&$res)
		{
			$con=$this;
			if($con->obtenerNumeroRegistros($res)>0)
			{
				mysql_data_seek($res,0);
			}
		}
		
		function obtenerNumeroRegistros($res)
		{
			return mysql_num_rows($res);
		}
		
		function obtenerPrimeraFilaAsoc($consulta)
		{
			
			$modoDebug=false;
			if(isset($_SESSION["debuggerConsulta"])&&($_SESSION["debuggerConsulta"]==1))
				$modoDebug=true;
			if($modoDebug)
				echo $consulta."<br>";
				
			$res=mysql_query($consulta,$this->conexion) or die(mysql_error());
			
			$this->filasAfectadas=mysql_num_rows($res);
			$fila=mysql_fetch_assoc($res);
			
			guardarBitacoraConsultaBD($consulta);
			return $fila;
		}
		
		function obtenerFilasObjConector($consulta)
		{
			//echo $consulta;
			$modoDebug=false;
			if(isset($_SESSION["debuggerConsulta"])&&($_SESSION["debuggerConsulta"]==1))
				$modoDebug=true;
			if($modoDebug)
				echo $consulta."<br>";
			$res=mysql_query($consulta,$this->conexion) or die(mysql_error());
			$this->filasAfectadas=mysql_num_rows($res);
			//echo mysql_info($this->conexion);
			/*if($log)
				guardarBitacoraConsultaBD($consulta);*/
			$obj[0]=$res;
			$obj[1]=$this;
			return $obj;
		}
			
	}
	
	
	function stringEncode($cadena,$llave)
	{
		
	}
	
	function stringDecode($cadena,$llave)
	{
	}
	
	function obtenerTablasConsulta($consulta)
	{
		$arrTablas=array();
		$arrAux=explode("from",$consulta);
		
		if(sizeof($arrAux)>1)
		{
			$arrAux=explode(" where ",$arrAux[1]);
			$arrAux2=explode(",", $arrAux[0]);
			foreach($arrAux2 as $a)
			{
				array_push($arrTablas,trim($a));
			}
		}
		return $arrTablas;
	}
	
	function normalizarCondicionesWhere($condicion)
	{
		global $con;
		
		$posIndex=strpos($condicion,"@Control_");
	
		while($posIndex!==false)
		{
			$etControl="";
			$fin=false;
			while(!$fin)
			{
				if($condicion[$posIndex]!="'")
					$etControl.=$condicion[$posIndex];
				else
					$fin=true;
				$posIndex++;
					
			}
			
			$arrCtrl=explode("_",$etControl);
			$consulta="SELECT nombreCampo,idFormulario FROM 901_elementosFormulario WHERE idGrupoElemento=".$arrCtrl[1];
			$fCampo=$con->obtenerPrimeraFila($consulta);
			$nCampo=$fCampo[0];
			
			
			
			$condicion=str_replace("'".$etControl."'","_".$fCampo[1]."_tablaDinamica.".$nCampo,$condicion);
			$posIndex=strpos($condicion,"@Control_");
		}
		
		return $condicion;

	}
	
	function generarConsultaIdElementoTablaExterna($idElemento,$arrQueries,$idFormulario)
	{
		global $con;	
		
		$consulta="select nombreTabla,idFrmEntidad,complementario from 900_formularios where idFormulario=".$idFormulario;
		
		$fila=$con->obtenerPrimeraFila($consulta);
		$tablaFormulario=$fila[0];
		
		$consulta="	select e.nombreCampo,e.idGrupoElemento,e.tipoElemento from 901_elementosFormulario e where idGrupoElemento=".$idElemento;

		$filas=$con->obtenerPrimeraFila($consulta);
		
		$queryConf="select * from 904_configuracionElemFormulario where idElemFormulario=".$filas[1];
		
		$filaConf=$con->obtenerPrimeraFila($queryConf);
		$tablaD=$filaConf[2];
		$campoP=$filaConf[3];
		$campoId=$filaConf[4];
		$consultaRefTablas="";	
		$arrComp=array();
		if(strpos($tablaD,"[")===false)
		{
			$consultaRefTablas="(select ".$campoP." from ".$tablaD." where ".$campoId."=".$tablaFormulario.".".$filas[0]." limit 0,1)";
			echo $consultaRefTablas;
		}
		else
		{
			$idAlmacen=str_replace("[","",$tablaD);
			$idAlmacen=str_replace("]","",$idAlmacen);
			
			$consulta="SELECT configuracion FROM 907_camposGrid WHERE idElementoFormulario=".$filas[1];
			$conf=$con->obtenerValor($consulta);
			
			
			if($conf!='')
			{
				$obj=json_decode($conf);
				if(isset($obj->consultaReemplazo)&&($obj->consultaReemplazo!=""))
					$idAlmacen=$obj->consultaReemplazo;
			}
			
			
			
			if($arrQueries[$idAlmacen]["ejecutado"]==1)
			{
				
				
				$arrCampos=str_replace("@@",",",$campoP);
				
				
				$normalizado=normalizarQueryProyeccionOptimizacion($arrQueries[$idAlmacen]["query"],$arrCampos);
				
				$compOr="";
				if(strpos($normalizado,"where")!==false)
				{
					
					$arrNormalizado=explode(" where ",$normalizado);
					
					
					$arrComp=explode(" order ",$normalizado);
					
					$normalizado=$arrNormalizado[0]." where (".$arrNormalizado[1].")";
					
					
					if(sizeof($arrComp)>1)
					{
						$compOr.=" order ".$arrComp[1];
					}

				}
				else
				{
					
					$arrComp=explode(" order ",$normalizado);
					$normalizado=$arrComp[0];
					$normalizado.=" where 1=1";
					if(sizeof($arrComp)>1)
					{
						$compOr.=" order ".$arrComp[1];
					}
				}
				
				
				if($arrQueries[$idAlmacen]["idConexion"]==0)
				{
					
					$arrTablas=obtenerTablasInvolucradasQuery($arrQueries[$idAlmacen]["query"]);
					$arrTablasProyeccion=obtenerTablasCamposProyeccionQuery($arrQueries[$idAlmacen]["query"],$arrTablas);
					
					$aToken=explode(",",$arrCampos);
					$arrTablasProyeccion=array();
					foreach($arrTablas as $tblAux)
					{
						foreach($aToken as $token)
						{
							if(strpos($token,$tblAux.".")!==false)
								$arrTablasProyeccion[$tblAux]=1;
						}
					}
					
					$aTablaCampoId=explode(".",$campoId);
					$tblTablaID=$aTablaCampoId[0];
					
					$queryKey="SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='".$arrQueries[$idAlmacen]["conector"]->bdActual."' AND TABLE_NAME='".$aTablaCampoId[0].
							"' AND COLUMN_NAME='".$aTablaCampoId[1]."' AND COLUMN_KEY='PRI'";
					$nKey=$con->obtenerValor($queryKey);
					$esCampoLlave=$nKey>0;
					
					
					if((sizeof($arrTablasProyeccion)>1)&&(isset($arrTablasProyeccion[$tblTablaID]))&&($esCampoLlave))
					{
						//$arrTablasProyeccion["ignorar"]=1;
						
						$aAux=explode( " where ",$normalizado);
							
						$normalizado=$aAux[0];
					
						$normalizado.=" where 1=1";
						if(sizeof($arrComp)>1)
						{
							$compOr.=" order ".$arrComp[1];
						}

						$consultaRefTablas="(".$normalizado." and ".$campoId."=".$tablaFormulario.".".$filas[0]." ".$compOr." limit 0,1)";
						
						
					}
					else
					{
						
						if((sizeof($arrTablasProyeccion)==1)&&(isset($arrTablasProyeccion[$tblTablaID]))&&($esCampoLlave))
						{
							$aAux=explode( " from ",$normalizado);
						
							$normalizado2=$aAux[0]." from ".$tblTablaID;
							
							$aAux=explode( " where ",$normalizado);
						
							$normalizado=$normalizado2;
	
							$normalizado.=" where 1=1";
							if(sizeof($arrComp)>1)
							{
								$compOr.=" order ".$arrComp[1];
							}
							
							$consultaRefTablas="(".$normalizado." and ".$campoId."=".$tablaFormulario.".".$filas[0]." ".$compOr." limit 0,1)";
						}
						else
						{
							$aQueryAux=explode(" where ",$normalizado);//modificdo
							$arrComp="";
							$normalizado=$aQueryAux[0];
	
	
							if(sizeof($aQueryAux)>1)
							{
								$arrComp=explode(" order ",$aQueryAux[1]);
	
								$condAux=normalizarCondicionesWhere($arrComp[0]);
								//echo $condAux."<br>";
								if($condAux!="")
								{
									if(strpos($normalizado," where ")!==false)
										$normalizado.=" and ".$condAux;
									else
										$normalizado.=" where ".$condAux;
								}
							}
							if(sizeof($arrComp)>1)
							{
								$compOr.=" order ".$arrComp[1];
							}
							$consultaRefTablas="(".$normalizado." and ".$campoId."=".$tablaFormulario.".".$filas[0]." ".$compOr." limit 0,1)";
						}
							//$consultaRefTablas="(".$normalizado." and ".$campoId."=".$tablaFormulario.".".$filas[0]." ".$compOr." limit 0,1)";
							
						
					}
				}
				else
				{
					$arrConsultaNormaliza=explode(" where ",$normalizado);
					$consultaRefTablas=str_replace("concat",$campoId.", concat",$arrConsultaNormaliza[0]);
					$conAux=$arrQueries[$idAlmacen]["conector"];
					$resAux=$conAux->obtenerFilas($consultaRefTablas);
					$consultaRefTablas="(SELECT (CASE ".$tablaFormulario.".".$filas[0]." ";
					while($filaAux=$conAux->obtenerSiguienteFila($resAux))
					{
						$consultaRefTablas.=" WHEN ".$filaAux[0]." THEN '".$filaAux[1]."'";
					}
					$consultaRefTablas.=" END))";
					
				}

				
			}
			else
			{
				
				if($arrQueries[$idAlmacen]["idConexion"]==0)
				{
					
					$aQueryAux=explode(" where ",$arrQueries[$idAlmacen]["query"]);
					
					$arrCampos=str_replace("@@",",",$campoP);
					$normalizado=normalizarQueryProyeccionOptimizacion($aQueryAux[0],$arrCampos);
					
					$compOr="";
					
						
					$arrComp=explode(" order ",$normalizado);
					$normalizado=$arrComp[0];
					
					
					$arrTablas=obtenerTablasConsulta($arrQueries[$idAlmacen]["query"]);
					if(sizeof($arrTablas)==1)
					{
						$aAux=explode( " where ",$normalizado);
						$normalizado=$aAux[0];
						$normalizado.=" where 1=1";
						if(sizeof($arrComp)>1)
						{
							$compOr.=" order ".$arrComp[1];
						}
						
						$consultaRefTablas="(".$normalizado." and ".$campoId."=".$tablaFormulario.".".$filas[0]." ".$compOr." limit 0,1)";
					}
					else
					{
						$aAux=explode(" order ",$aQueryAux[1]);
						$normalizado.=" where ".normalizarCondicionesWhere($aAux[0]);
						if(sizeof($arrComp)>1)
						{
							$compOr.=" order ".$arrComp[1];
						}
						
						$consultaRefTablas="(".$normalizado." and ".$campoId."=".$tablaFormulario.".".$filas[0]." ".$compOr." limit 0,1)";

					}
					
				}
				else
					$consultaRefTablas="'No se ha podido resolver'";
			}
		}

		return $consultaRefTablas;
	}
?>