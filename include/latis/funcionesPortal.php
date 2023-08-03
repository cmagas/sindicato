<?php

	$lenguaje="1";//1.-ESPAÑOL;2.- INGLES
	if(isset($_POST["leng"]))
	{
		$lenguaje=$_POST["leng"];
		$_SESSION["leng"]=$lenguaje;
	}
	else
		if(isset($_SESSION["leng"]))
		{
			$lenguaje=$_SESSION["leng"];
		}
		else
			$_SESSION["leng"]="1";
	$login="";
	if(isset($_SESSION["idUsr"]))
	{
		if($_SESSION["idUsr"]!="-1")
		{
			$login=$_SESSION["login"];
		}
		else
			$_SESSION["idRol"]="'-1000_0','-1001_0'";
	}
	else
		$_SESSION["idRol"]="'-1000_0','-1001_0'";
	
	function mostrarSiLog()
	{
		global $login;
		if($login=="")
			echo	"style=\"display:none\"";
		else
			echo "style=\"display:''\"";
	}
	
	function ocultarSiLog()
	{
		global $login;
		if($login=="")
			echo	"style=\"display:''\"";
		else
			echo "style=\"display:none\"";
	} 

	function esUsuarioLog()
	{
		global $login;
		
		if((!isset($_SESSION["idUsr"]))||($_SESSION["idUsr"]==-1))
			return false;
		return true;
	}

	function generarCabeceraMenu($titulo)
	{
		global $colorLetra;
		$cabecera='	
                  	<td  height=23 align="center"><span class="letraTituloMenuIzq">'.uE($titulo).'</span></td>
                   ';
	
		return $cabecera;
	}
	
	function generarOpciones($padre)
	{
		global $con;

		$consulta="select distinct(pO.textoOpcion),pO.paginaUrlDestino,pO.idOpcion from 811_menusVSOpciones mO, 809_opciones 
					pO  where pO.idOpcion=mO.idOpcion and pO.idIdioma=".$_SESSION["leng"]." and  mO.idMenu=".$padre." order by mO.orden";
		$filas=$con->obtenerFilas($consulta);
		$opciones="";
		while($fila=mysql_fetch_row($filas))
		{
			if(strpos($fila[1],"?idFormulario"))
			{
				
				$arrOpciones=explode('=',$fila[1]);
				$idFormulario=$arrOpciones[1];
				if(strpos($fila[1],"administrarHorarioUnidadApartado.php"))
					$opciones.='<li class="bg_list_un"><a href="javaScript:enviarFormularioAdmon('.$idFormulario.')">'.uE($fila[0]).'</a></li>';
				else
					$opciones.='<li class="bg_list_un"><a href="javaScript:enviarFormulario('.$idFormulario.')">'.uE($fila[0]).'</a></li>';
			}
			else
			{
				if(strpos($fila[1],"?idTipoProyecto"))
				{
					$arrOpciones=explode('=',$fila[1]);
					$idTipoProyecto=$arrOpciones[1];
					
				}
				else
					$opciones.='<li class="bg_list_un"><a href="'.$fila[1].'">'.uE($fila[0]).'</a></li>';
			}
		}
		return $opciones;
		
	}
	
	
	function generarArregloMenusPagina($nomPagina,$posicion)
	{
		global $con;
		$arrMenus=array();
		$consulta="select distinct(pO.idOpcion),tM.textoMenu,tM.colorFondo,idFuncionVisualiza,idFuncionRenderer,clase from 813_paginasVSOpciones pO,
							810_paginas p,812_permisosOpcionesMenus pM,808_titulosMenu tM where pO.idPagina=p.idPagina 
							and pM.idOpcion=pO.idOpcion and pM.idRol in(".$_SESSION["idRol"].") and p.pagina='".$nomPagina.
							"' and pO.posicion=".$posicion." and tM.idMenu=pO.idOpcion and tM.situacion=1 order by tM.prioridad,tM.textoMenu";
		
		
		$res=$con->obtenerFilas($consulta);

		while($filaM=mysql_fetch_row($res))
		{
			
			
			if(($filaM[3]!=-1)&&($filaM[3]!=""))
			{
				
				$cadObj='{"idMenu":"'.$filaM[0].'"}';
				$obj=json_decode($cadObj);
				$cache=NULL;
				$resEval= resolverExpresionCalculoPHP($filaM[3],$obj,$cache);
				if(($resEval===false)||($resEval==0))
				{
					continue;
				}
			}
			
			$arrMenus[$filaM[1]."_".$filaM[0]]["titulo"]=$filaM[1];
			$arrMenus[$filaM[1]."_".$filaM[0]]["colorFondo"]=$filaM[2];
			$arrMenus[$filaM[1]."_".$filaM[0]]["idFuncionVisualiza"]=$filaM[3];
			$arrMenus[$filaM[1]."_".$filaM[0]]["idFuncionRenderer"]=$filaM[4];
			$arrMenus[$filaM[1]."_".$filaM[0]]["clase"]=$filaM[5];
			$arrMenus[$filaM[1]."_".$filaM[0]]["opciones"]=array();
			
			
			
			
			$consulta2="select distinct(oP.textoOpcion),oP.paginaUrlDestino,oP.nombreBullet,oP.idOpcion,idFuncionVisualizacion,idFuncionRenderer,clase from 809_opciones oP,811_menusVSOpciones mO where
						 mO.idOpcion=oP.idOpcion and idIdioma=".$_SESSION["leng"]." and mO.idMenu=".$filaM[0]."  order by mO.orden";
			$opcionesNivel1= $con->obtenerFilas($consulta2);
			while($fila=mysql_fetch_row($opcionesNivel1))
			{
				
				if(($fila[4]!=-1)&&($fila[4]!=""))
				{
					$cadObj='{"idMenu":"'.$filaM[0].'","idOpcion":"'.$fila[3].'"}';
					$obj=json_decode($cadObj);
					$cache=NULL;
					$resEval= removerComillasLimite(resolverExpresionCalculoPHP($fila[4],$obj,$cache));
					
					if(($resEval===false)||($resEval==0))
					{
						
						continue;
					}
				}
				
				$oMenu=array();
				$oMenu["texto"]=$fila[0];
				$oMenu["url"]=$fila[1];
				$oMenu["bullet"]=$fila[2];
				$oMenu["idOpcion"]=$fila[3];
				$oMenu["idFuncionVisualiza"]=$fila[4];
				$oMenu["idFuncionRenderer"]=$fila[5];
				$oMenu["clase"]=$fila[6];
				$oMenu["opciones"]=obtenerOpcionesHijos($fila[3]);
				array_push($arrMenus[$filaM[1]."_".$filaM[0]]["opciones"],$oMenu);
				
			}
			
			
			
			if(sizeof($arrMenus[$filaM[1]."_".$filaM[0]]["opciones"])==0)
			{
				unset($arrMenus[$filaM[1]."_".$filaM[0]]["opciones"]);
			}
			
		}	
		
		
		
		return $arrMenus;
	}
	
	function obtenerOpcionesHijos($idOpcion)
	{
		global $con;	
		$arrOpciones=array();
		$consulta2="select distinct(oP.textoOpcion),oP.paginaUrlDestino,oP.nombreBullet,oP.idOpcion,idFuncionVisualizacion,idFuncionRenderer,clase from 809_opciones oP where idPadre=".$idOpcion."
					and idIdioma=".$_SESSION["leng"]." order by orden";
		$opcionesNivel1= $con->obtenerFilas($consulta2);
		while($fila=mysql_fetch_row($opcionesNivel1))
		{
			
			if(($fila[4]!=-1)&&($fila[4]!=""))
			{
				$cadObj='{}';
				$obj=json_decode($cadObj);
				$cache=NULL;
				$resEval= resolverExpresionCalculoPHP($fila[4],$obj,$cache);
				if(($resEval===false)||($resEval==0))
				{
					continue;
				}
			}
			
			$oMenu=array();
			$oMenu["texto"]=$fila[0];
			
			$url=$fila[1];
			if(strpos($url,"?idFormulario"))
			{
				
				$arrOpciones=explode('=',$url);
				$idFormulario=$arrOpciones[1];
				if(strpos($url,"administrarHorarioUnidadApartado.php"))
					$url='javaScript:enviarFormularioAdmon('.$idFormulario.')';
				else
					$url='javaScript:enviarFormulario('.$idFormulario.')';
					
			}
			else
			{
				if(strpos($url,"?idTipoProyecto"))
				{
					$arrOpciones=explode('=',$fila[1]);
					$idTipoProyecto=$arrOpciones[1];
					
				}
				else
					$url=$fila[1];
			}
			
			$oMenu["url"]=$url;
			$oMenu["bullet"]=$fila[2];
			$oMenu["idOpcion"]=$fila[3];
			$oMenu["idFuncionVisualiza"]=$fila[4];
			$oMenu["idFuncionRenderer"]=$fila[5];
			$oMenu["clase"]=$fila[6];
			$oMenu["opciones"]=obtenerOpcionesHijos($fila[3]);
			array_push($arrOpciones,$oMenu);
		}
		return $arrOpciones;
	}
	
	function generarMenuPagina($arrMenus,$tipoMenu) //1 horizontal, 2 vertical
	{
		global $con;	
		$llave=rand(1,10000);
		$ct=1;
		foreach($arrMenus as $m)
		{
			$llaveMenu=$llave."_".$ct;
			inicializarMenuRenderer($m,$tipoMenu,$llaveMenu);
			$ct++;
		}
	}
	
	function genearOpcionesMenusPrincipal($nomPagina,$posicion,$iTipoMenu=0)
	{
		$tipoMenu=0;
		switch($posicion)
		{
			case 1:
			case 2:
				$tipoMenu=1;
			break;
			case 3:
				$tipoMenu=2;
			break;
		}
		$arrMenus=generarArregloMenusPagina($nomPagina,$posicion);
		
		generarMenuPagina($arrMenus,$tipoMenu);
	}
	
	function generarConfiguracionesMenus($idConfiguracion)
	{
		global $con;
		$consulta="SELECT jsRequeridos,cssRequeridos,funcionInicializacion FROM 808_configuracionEstilosMenu WHERE idConfiguracion=".$idConfiguracion;
		$fConf=$con->obtenerPrimeraFila($consulta);
		
		$arrCSS=array();
		if($fConf[1]!="")
			$arrCSS=explode(",",$fConf[1]);
		foreach($arrCSS as $c)
		{
			echo '<link rel="stylesheet" href="'.$c.'"  type="text/css" media="screen" />';	
		}
		$arrJS=array();
		if($fConf[0]!="")
			$arrJS=explode(",",$fConf[0]);
		foreach($arrJS as $js)
		{
			echo '<script type="text/javascript" src="'.$js.'"></script>';	
		}
		
	}
	
	function redireccionarSiLog()
	{
		global $paginaInicioLogin;
		if(esUsuarioLog())
		{
			$redireccion="../principal/inicio.php";
			if($paginaInicioLogin!="")
				header('Location:'.$paginaInicioLogin);		
			else
				header('Location:'.$redireccion);		
		}	
	}
	
	function registrarVisita()
	{
		global $con;
		$ipVisitante=obtenerIP();
		$consulta="select ip, TIMEDIFF(NOW(), fechaIngreso),  numVisitas from 8004_contadorVisitas where ip='".$ipVisitante."'";
		$fDatos=$con->obtenerPrimeraFila($consulta);
		if($fDatos)
		{
			$tiempo=$fDatos[1];
			$numVisitas=$fDatos[2];
			$horas=substr($tiempo,0,2);
			$tiemRes=5;
			if($horas>$tiemRes)
			{
				$consulta="update 8004_contadorVisitas set ip='".$ipVisitante."',fechaIngreso='".date("Y-m-d H:i:s")."',numVisitas=numVisitas+1 where ip='".$ipVisitante."'";
				$con->ejecutarConsulta($consulta);		
			}
		}
		else
		{
			$consulta="INSERT INTO 8004_contadorVisitas(ip,fechaIngreso,numVisitas) VALUES('".$ipVisitante."','".date("Y-m-d H:i:s")."',1)";
			$con->ejecutarConsulta($consulta);	
		}
			
	}
	
?>