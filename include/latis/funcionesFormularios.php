<?php  
	include_once("latis/conexionBD.php"); 
	
	function crearFormulario($definicionFormulario,$crearControlesBase=true)
	{
		global $con;
		$objForm=json_decode(utf8_encode($definicionFormulario));
		$complementario="";
		if(isset($objForm->complementario))
			$complementario=$objForm->complementario;
		$consulta="insert into 900_formularios(nombreFormulario,descripcion,titulo,fechaCreacion,responsable,idProceso,idEtapa,
					idFrmEntidad,frmRepetible,formularioBase,estadoInicial,eliminable,tipoFormulario,mostrarTableroControl,complementario) values
					('".cv($objForm->nombreFormulario)."','".cv($objForm->descripcion)."','".cv($objForm->titulo)."','".
					date('Y-m-d')."',".$_SESSION["idUsr"].",".cv($objForm->idProceso).",".cv($objForm->idEtapa).",".cv($objForm->idFrmEntidad).",".
					cv($objForm->frmRepetible).",".cv($objForm->formularioBase).",".cv($objForm->estadoInicial).",".cv($objForm->eliminable).
					",".cv($objForm->tipoFormulario).",".cv($objForm->mostrarTableroControl).",'".$complementario."')";

		if($con->ejecutarConsulta($consulta))
		{
			$idFormulario=$con->obtenerUltimoID();
			$nombreTabla="_".$idFormulario."_tablaDinamica";
			$consulta="update 900_formularios set nombreTabla='".$nombreTabla."' where idFormulario=".$idFormulario;
			
			if($con->ejecutarConsulta($consulta))
			{
				if(crearTablaFormulario($nombreTabla))
				{
					if($crearControlesBase)
					{
						if(!crearControlesDefaultFormulario($idFormulario))
							return "-1";
					}
					if(isset($objForm->arrControles))
					{
						$arrControles=$objForm->arrControles;
						foreach($arrControles as $control)
						{
							$res=crearControl($control,false,$idFormulario);
							$arrRes=explode("|",$res);
							if($arrRes[0]!=1)
								return "-1";
						}
					}
					if(isset($objForm->confListadoFormulario))
					{
						$confListadoFormulario=$objForm->confListadoFormulario;
						if(!configurarListadoFormulario($confListadoFormulario,$idFormulario))
							return false;
						
					}
					
					return $idFormulario;
				}
			}
			return "-1";
		}
		return "-1";		

	}
	
	function crearControlesDefaultFormulario($idFormulario)
	{
		global $con;
		$x=0;
		$query="select idElementoFormularioSig from 903_variablesSistema for update";
		$idGrupoElemento=$con->obtenerValor($query);
		$consult[$x]="insert into 901_elementosFormulario(idFormulario,tipoElemento,posX,posY,idGrupoElemento,eliminable) values(".$idFormulario.",0,246,341,".$idGrupoElemento.",0) ";
		$x++;
		$consult[$x]="	insert into 904_configuracionElemFormulario(idElemFormulario,campoConf12) values(".$idGrupoElemento.",'btnGuardar')";
		$x++;
		$consult[$x]="insert into 901_elementosFormulario(idFormulario,tipoElemento,posX,posY,idGrupoElemento,eliminable) values(".$idFormulario.",-1,246,381,".($idGrupoElemento+1).",0) ";
		$x++;
		$consult[$x]="	insert into 904_configuracionElemFormulario(idElemFormulario,campoConf12) values(".($idGrupoElemento+1).",'btnCancelar')";
		$x++;
		$consult[$x]="insert into 901_elementosFormulario(idFormulario,tipoElemento,posX,posY,idGrupoElemento,eliminable) values(".$idFormulario.",-2,887,212,".($idGrupoElemento+2).",0) ";
		$x++;
		$consult[$x]="update 903_variablesSistema set idElementoFormularioSig=idElementoFormularioSig+3";
		$x++;
		$res=$con->ejecutarBloque($consult);
		
		return $res;
		
	}
	
	function crearTablaFormulario($nTabla)
	{
		global $con;
		if(!$con->existeTabla($nTabla))
		{
			$consulta="select estadoInicial from 900_formularios where nombreTabla='".$nTabla."'";
			$idEdoInicial=$con->obtenerValor($consulta);
			$arrCampos=",`idReferencia` bigint(20) default  '-1',`fechaCreacion` datetime default NULL, `responsable` bigint(20) default NULL,
						`fechaModif` datetime default NULL,`respModif` bigint(20) default NULL,`idEstado` decimal(10,2) default  '".$idEdoInicial."',
						`codigoUnidad` varchar(255) character set utf8 collate utf8_spanish2_ci default NULL,
						`codigoInstitucion` varchar(255) character set utf8 collate utf8_spanish2_ci default NULL,
						`codigo` varchar(255) character set utf8 collate utf8_spanish2_ci default NULL
						";
			$query="create table `".$nTabla."`(`id_".$nTabla."` bigint(20) NOT NULL auto_increment".$arrCampos.",PRIMARY KEY (`id_".$nTabla."`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci";
			return $con->ejecutarConsulta($query);
		}
		else
			return true;
	}
	
	function crearControl($defControl,$decodeJSON=true,$idForm="-1")
	{
		global $con;
		global $et;
		
		if($decodeJSON)
		{
			$objJson=json_decode($defControl);
			$idFormulario=$objJson->idFormulario;
		}
		else
		{
			$objJson=$defControl;
			$idFormulario=$idForm;
		}
		$idProceso=obtenerIdProcesoFormulario($idFormulario);
		
		$tipoProceso=obtenerTipoProceso($idProceso);
		
		$pregunta=$objJson->pregunta;
		$obligatorio=$objJson->obligatorio;
		$tipoElemento=	$objJson->tipoElemento;
		$posX=$objJson->posX;
		$posY=$objJson->posY;
		$eliminable="1";
		if(isset($objJson->eliminable))
			$eliminable=$objJson->eliminable;
		$arrElemento="";
		$query="begin";
		$maxValorOpcion=0;
		$consultaAux='select nombreTabla from 900_formularios where idFormulario='.$idFormulario;
		$nomTabla=$con->obtenerValor($consultaAux);		
		if($con->ejecutarConsulta($query))
		{
			$numPreguntas=sizeof($pregunta);
			$query="select idElementoFormularioSig from 903_variablesSistema for update";
			$idPregunta=$con->obtenerValor($query);
			$query="select idOpcionFormularioSig from 903_variablesSistema for update";
			$idGrupoOpcion=$con->obtenerValor($query);
			$numOpcionesAgregadas=0;
			$x=0;
			if(($tipoElemento==1)||($tipoElemento==13)||($tipoElemento==22)||($tipoElemento==23))
				$orden=0;
			else
			{
				$query="select count(idGrupoElemento) from 901_elementosFormulario where idFormulario=".$idFormulario." and tipoElemento not in(1,13,-2,22)";
				$orden=$con->obtenerValor($query);
				$orden++;
			}
			
			$claseDefault="letraFicha";
			switch($tipoElemento)
			{
				case 1: //label
					for($ct=0;$ct<$numPreguntas;$ct++)
					{
						$consulta[$x]="insert into 901_elementosFormulario(nombreCampo,idIdioma,idFormulario,
										tipoElemento,idGrupoElemento,maxValorRespuesta,posX,posY,obligatorio,eliminable,orden) values('".cv($pregunta[$ct]->etiqueta)."',".$pregunta[$ct]->idIdioma.
										",".$idFormulario.",".$tipoElemento.",".$idPregunta.",".$maxValorOpcion.",".$posX.",".$posY.",".$obligatorio.",".$eliminable.",".$orden.")";
						$x++;
						
					}
					$consulta[$x]="	insert into 904_configuracionElemFormulario(idElemFormulario,campoConf12) 
									values(".$idPregunta.",'".$claseDefault."')";
					$x++;
				break;
				case 17:
				case 14:
				case 2: //pregunta cerrada-Opciones Manuales
					$opciones=$objJson->opciones;
					$numOpciones=sizeof($opciones);
					for($ct=0;$ct<$numOpciones;$ct++)
					{
						$valorOpcion=$opciones[$ct]->vOpcion;
						if($valorOpcion>$maxValorOpcion)
							$maxValorOpcion=$valorOpcion;
						$arrOpciones=$opciones[$ct]->columnas;
						$numOpt=sizeof($arrOpciones);
						for($y=0;$y<$numOpt;$y++)
						{
							$consulta[$x]="insert into 902_opcionesFormulario(contenido,valor,idIdioma,idGrupoElemento,idGrupoOpcion) values
											('".$arrOpciones[$y]->texto."','".$valorOpcion."',".$arrOpciones[$y]->idLeng.",".$idPregunta.",".($idGrupoOpcion+$numOpcionesAgregadas).")";
							$x++;
						}
						$numOpcionesAgregadas++;
					}
					$consulta[$x]="insert into 901_elementosFormulario(nombreCampo,idIdioma,idFormulario,
										tipoElemento,idGrupoElemento,maxValorRespuesta,posX,posY,obligatorio,eliminable,orden) values('".cv($objJson->nomCampo)."',".$_SESSION["leng"].
										",".$idFormulario.",".$tipoElemento.",".$idPregunta.",".$maxValorOpcion.",".$posX.",".$posY.",".$obligatorio.",".$eliminable.",".$orden.")";
					$x++;
					switch($tipoElemento)
					{
						case 14:
							$consulta[$x]="	insert into 904_configuracionElemFormulario(idElemFormulario,campoConf8,campoConf9,campoConf10) 
									values(".$idPregunta.",'1','".$obligatorio."','0')";
							$x++;
							$arrElemento="[['-1',''],".$arrElemento."]";
						break;
						case 17:
							$consulta[$x]="	insert into 904_configuracionElemFormulario(idElemFormulario,campoConf8,campoConf9,campoConf10) 
									values(".$idPregunta.",'1','-1','0')";
							$x++;
							$arrNomTabla=explode('_',$nomTabla);
							$nuevaTabla=$arrNomTabla[1]."_".cv($objJson->nomCampo);
							if($tipoProceso!=1000)
							{
								$consulta[$x]=" create table _".$nuevaTabla."(id__".$nuevaTabla." bigint(20) NOT NULL auto_increment,idPadre bigint(20) default NULL,
												idOpcion varchar(30) character set utf8 collate utf8_spanish2_ci default NULL,
												PRIMARY KEY (id__".$nuevaTabla.")) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci";
								
								$x++;
							}
							$arrElemento="[['-1',''],".$arrElemento."]";
						break;	
						case 2:
							$consulta[$x]="	insert into 904_configuracionElemFormulario(idElemFormulario,campoConf9) 
									values(".$idPregunta.",'".$obligatorio."')";
							$x++;
							$arrElemento="[".$arrElemento."]";
						break;
					}
				break;
				case 18:
				case 15:
				case 3: //pregunta cerrada-Opciones intervalo
					$objIntervalo=$objJson->objInt;
					switch($tipoElemento)
					{
						case 3:
							$consulta[$x]="	insert into 904_configuracionElemFormulario(idElemFormulario,campoConf1,campoConf2,campoConf3) 
										values(".$idPregunta.",'".$objIntervalo->inicio."','".$objIntervalo->fin."','".$objIntervalo->intervalo."')";
							$x++;
						break;
						case 15:
							$consulta[$x]="	insert into 904_configuracionElemFormulario(idElemFormulario,campoConf1,campoConf2,campoConf3,campoConf8,campoConf9,campoConf10) 
										values(".$idPregunta.",'".$objIntervalo->inicio."','".$objIntervalo->fin."','".$objIntervalo->intervalo."','1','".$obligatorio."','0')";
							$x++;
						break;
						case 18:
							$consulta[$x]="	insert into 904_configuracionElemFormulario(idElemFormulario,campoConf1,campoConf2,campoConf3,campoConf8,campoConf9,campoConf10) 
										values(".$idPregunta.",'".$objIntervalo->inicio."','".$objIntervalo->fin."','".$objIntervalo->intervalo."','1','-1','0')";
							$x++;
							$arrNomTabla=explode('_',$nomTabla);
							$nuevaTabla=$arrNomTabla[1]."_".cv($objJson->nomCampo);
							if($tipoProceso!=1000)
							{
								$consulta[$x]=" create table _".$nuevaTabla."(id__".$nuevaTabla." bigint(20) NOT NULL auto_increment,idPadre bigint(20) default NULL,
												idOpcion varchar(30) character set utf8 collate utf8_spanish2_ci default NULL,
												PRIMARY KEY (id__".$nuevaTabla.")) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci";
								$x++;
							}
						break;
					}
					
					$consulta[$x]="insert into 901_elementosFormulario(nombreCampo,idIdioma,idFormulario,
										tipoElemento,idGrupoElemento,maxValorRespuesta,posX,posY,obligatorio,eliminable,orden) values('".cv($objJson->nomCampo)."',".$_SESSION["leng"].
										",".$idFormulario.",".$tipoElemento.",".$idPregunta.",".$maxValorOpcion.",".$posX.",".$posY.",".$obligatorio.",".$eliminable.",".$orden.")";
					$x++;
					$inicio=$objIntervalo->inicio;
					$fin=$objIntervalo->fin;
					$intervalo=$objIntervalo->intervalo;
					$arrElemento=generarIntervaloNumeros($inicio,$fin,$intervalo);
				break;
				case 19:
				case 16:
				case 4:	 //Pregunta cerrada-Opciones tabla
					$objTablaConf=$objJson->objTablaConf;
					$campoID=$objTablaConf->cLlave;
					$camp4="";
					$camp5="";
					$camp6="";
					$camp7="";
					$camp8="";
					$camp9="";
					$camp10="";
					$camp11="0";
					$condWhere="";
					switch($tipoElemento)
					{
						case 4:
							$consulta[$x]="	insert into 904_configuracionElemFormulario(idElemFormulario,campoConf1,campoConf2,campoConf3,campoConf4,campoConf5,campoConf6,campoConf7,campoConf8,campoConf9,campoConf10,campoConf11) 
											values(".$idPregunta.",'[".$objTablaConf->tabla."]','".cv($objTablaConf->columna)."','".$campoID."','".$camp4."','".$camp5."','".$camp6."','".$camp7."','".$objTablaConf->autocompletar."','".$objTablaConf->cBusqueda."','250','".$camp11."')";
							$x++;											
						break;
						case 16:
							$consulta[$x]="	insert into 904_configuracionElemFormulario(idElemFormulario,campoConf1,campoConf2,campoConf3,campoConf4,campoConf5,campoConf6,campoConf7,campoConf8,campoConf9,campoConf10,campoConf11) 
										values(".$idPregunta.",'[".$objTablaConf->tabla."]','".cv($objTablaConf->columna)."','".$campoID."','".$camp4."','".$camp5."','".$camp6."','".$camp7."','1','-1','0','".$camp11."')";
							$x++;
						break;
						case 19:
							$consulta[$x]="	insert into 904_configuracionElemFormulario(idElemFormulario,campoConf1,campoConf2,campoConf3,campoConf4,campoConf5,campoConf6,campoConf7,campoConf8,campoConf9,campoConf10,campoConf11) 
										values(".$idPregunta.",'[".$objTablaConf->tabla."]','".cv($objTablaConf->columna)."','".$campoID."','".$camp4."','".$camp5."','".$camp6."','".$camp7."','1','".$obligatorio."','0','".$camp11."')";
							$x++;
							$arrNomTabla=explode('_',$nomTabla);
							$nuevaTabla=$arrNomTabla[1]."_".cv($objJson->nomCampo);
							if($tipoProceso!=1000)
							{
								$consulta[$x]=" create table _".$nuevaTabla."(id__".$nuevaTabla." bigint(20) NOT NULL auto_increment,idPadre bigint(20) default NULL,
												idOpcion varchar(30) character set utf8 collate utf8_spanish2_ci default NULL,
												PRIMARY KEY (id__".$nuevaTabla.")) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci";
								$x++;
							}
						break;
					}
					
					$consulta[$x]="insert into 901_elementosFormulario(nombreCampo,idIdioma,idFormulario,
										tipoElemento,idGrupoElemento,maxValorRespuesta,posX,posY,obligatorio,eliminable,orden) values('".cv($objJson->nomCampo)."',".$_SESSION["leng"].
										",".$idFormulario.",".$tipoElemento.",".$idPregunta.",".$maxValorOpcion.",".$posX.",".$posY.",".$obligatorio.",".$eliminable.",".$orden.")";
					$x++;
					
					if($objTablaConf->autocompletar!=1)
					{
						$idProceso=obtenerIdProcesoFormulario($idFormulario);
						$cadObj='{"p16":{"p1":"'.$idFormulario.'","p2":"'.$idProceso.'","p3":"-1","p4":"0","p5":"0","p6":"-1"}}';
						$paramObj=json_decode($cadObj);
						$arrQueries=resolverQueries($idFormulario,5,$paramObj,true);
						
						$arrCamposProy=explode("@@",$objTablaConf->columna);
						if($arrQueries[$objTablaConf->tabla]["ejecutado"]==1)
						{
							$resQuery=$arrQueries[$objTablaConf->tabla]["resultado"];
							$cadObj='{"conector":null,"resQuery":null,"idAlmacen":"","arrCamposProy":[],"formato":"1","imprimir":"0","campoID":"'.$campoID.'"}';
							$obj=json_decode($cadObj);
							$obj->resQuery=$arrQueries[$objTablaConf->tabla]["resultado"];
							$obj->idAlmacen=$objTablaConf->tabla;
							$obj->arrCamposProy=$arrCamposProy;
							$obj->conector=$arrQueries[$objTablaConf->tabla]["conector"];
							$arrElemento="[".generarFormatoOpcionesQuery($obj)."]";
							
						}
						else
							$arrElemento="[]";
					}
					else	
						$arrElemento="[]";
				break; 	 
				case 5:
				case 11: //Grupo Texto
					$confCampo=$objJson->confCampo;
					$consulta[$x]="	insert into 904_configuracionElemFormulario(idElemFormulario,campoConf1,campoConf2) 
									values(".$idPregunta.",'".$confCampo->ancho."','".$confCampo->longitud."')";
					$x++;
					$consulta[$x]="insert into 901_elementosFormulario(nombreCampo,idIdioma,idFormulario,
										tipoElemento,idGrupoElemento,maxValorRespuesta,posX,posY,obligatorio,eliminable,orden) values('".cv($objJson->nomCampo)."',".$_SESSION["leng"].
										",".$idFormulario.",".$tipoElemento.",".$idPregunta.",".$maxValorOpcion.",".$posX.",".$posY.",".$obligatorio.",".$eliminable.",".$orden.")";
					$x++;
				
				break;
				case 12: //Grupo Archivo
					$confCampo=$objJson->confCampo;
					$consulta[$x]="	insert into 904_configuracionElemFormulario(idElemFormulario,campoConf1,campoConf2) 
									values(".$idPregunta.",'".$confCampo->tipoDoc."','".$confCampo->tamMax."')";
					$x++;
					$consulta[$x]=	"insert into 901_elementosFormulario(nombreCampo,idIdioma,idFormulario,
									tipoElemento,idGrupoElemento,maxValorRespuesta,posX,posY,obligatorio,eliminable,orden) values('".cv($objJson->nomCampo)."',".$_SESSION["leng"].
									",".$idFormulario.",".$tipoElemento.",".$idPregunta.",".$maxValorOpcion.",".$posX.",".$posY.",".$obligatorio.",".$eliminable.",".$orden.")";
					$x++;
				break;
				case 6:
					$consulta[$x]="	insert into 904_configuracionElemFormulario(idElemFormulario,campoConf1,campoConf2,campoConf5) 
									values(".$idPregunta.",'10',',','0')";
					$x++;
					$consulta[$x]=	"insert into 901_elementosFormulario(nombreCampo,idIdioma,idFormulario,
									tipoElemento,idGrupoElemento,maxValorRespuesta,posX,posY,obligatorio,eliminable,orden) values('".cv($objJson->nomCampo)."',".$_SESSION["leng"].
									",".$idFormulario.",".$tipoElemento.",".$idPregunta.",".$maxValorOpcion.",".$posX.",".$posY.",".$obligatorio.",".$eliminable.",".$orden.")";
					$x++;
				break;
				case 7:
					$consulta[$x]="	insert into 904_configuracionElemFormulario(idElemFormulario,campoConf1,campoConf2,campoConf3,campoConf5,campoConf6) 
									values(".$idPregunta.",'10',',','.','0','2')";
					$x++;
					$consulta[$x]=	"insert into 901_elementosFormulario(nombreCampo,idIdioma,idFormulario,
									tipoElemento,idGrupoElemento,maxValorRespuesta,posX,posY,obligatorio,eliminable,orden) values('".cv($objJson->nomCampo)."',".$_SESSION["leng"].
									",".$idFormulario.",".$tipoElemento.",".$idPregunta.",".$maxValorOpcion.",".$posX.",".$posY.",".$obligatorio.",".$eliminable.",".$orden.")";
					$x++;
				break;
				case 8:  //Grupo Fecha
					$confCampo=$objJson->confCampo;
					$consulta[$x]="	insert into 904_configuracionElemFormulario(idElemFormulario,campoConf1,campoConf2,campoConf3) 
									values(".$idPregunta.",'".cv(bD($confCampo->fechaMin))."','".cv(bD($confCampo->fechaMax))."','".$confCampo->diasSel."')";
					$x++;
					$consulta[$x]=	"insert into 901_elementosFormulario(nombreCampo,idIdioma,idFormulario,
									tipoElemento,idGrupoElemento,maxValorRespuesta,posX,posY,obligatorio,eliminable,orden) values('".cv($objJson->nomCampo)."',".$_SESSION["leng"].
									",".$idFormulario.",".$tipoElemento.",".$idPregunta.",".$maxValorOpcion.",".$posX.",".$posY.",".$obligatorio.",".$eliminable.",".$orden.")";
					$x++;
				break;
				case 9:
				case 10:
					$confCampo=$objJson->confCampo;
					$consulta[$x]="	insert into 904_configuracionElemFormulario(idElemFormulario,campoConf1,campoConf2) 
									values(".$idPregunta.",'".$confCampo->ancho."','".$confCampo->alto."')";
					$x++;
					$consulta[$x]=	"insert into 901_elementosFormulario(nombreCampo,idIdioma,idFormulario,
									tipoElemento,idGrupoElemento,maxValorRespuesta,posX,posY,obligatorio,eliminable,orden) values('".cv($objJson->nomCampo)."',".$_SESSION["leng"].
									",".$idFormulario.",".$tipoElemento.",".$idPregunta.",".$maxValorOpcion.",".$posX.",".$posY.",".$obligatorio.",".$eliminable.",".$orden.")";
					$x++;
				break;
				case 13: //frame
					$confCampo=$objJson->confCampo;
					$consulta[$x]="	insert into 904_configuracionElemFormulario(idElemFormulario,campoConf1,campoConf2) 
									values(".$idPregunta.",'".$confCampo->ancho."','".$confCampo->alto."')";
					$x++;
					for($ct=0;$ct<$numPreguntas;$ct++)
					{
						$consulta[$x]="insert into 901_elementosFormulario(nombreCampo,idIdioma,idFormulario,
										tipoElemento,idGrupoElemento,maxValorRespuesta,posX,posY,obligatorio,eliminable,orden) values('".cv($pregunta[$ct]->etiqueta)."',".$pregunta[$ct]->idIdioma.
										",".$idFormulario.",".$tipoElemento.",".$idPregunta.",".$maxValorOpcion.",".$posX.",".$posY.",".$obligatorio.",".$eliminable.",".$orden.")";
						$x++;
					}
					
				break;
				case 20: //Hidden
					$confCampo=$objJson->confCampo;
					$consulta[$x]="	insert into 904_configuracionElemFormulario(idElemFormulario,campoConf1,campoConf2) 
									values(".$idPregunta.",'".$confCampo->vSesion."','1')";
					$x++;
					$consulta[$x]="insert into 901_elementosFormulario(nombreCampo,idIdioma,idFormulario,
										tipoElemento,idGrupoElemento,maxValorRespuesta,posX,posY,obligatorio,eliminable,orden) values('".cv($objJson->nomCampo)."',".$_SESSION["leng"].
										",".$idFormulario.",".$tipoElemento.",".$idPregunta.",".$maxValorOpcion.",".$posX.",".$posY.",".$obligatorio.",".$eliminable.",".$orden.")";
					$x++;
				break;
				case 21:  //Grupo Fecha
					$confCampo=$objJson->confCampo;
					$consulta[$x]="	insert into 904_configuracionElemFormulario(idElemFormulario,campoConf1,campoConf2,campoConf3) 
									values(".$idPregunta.",'".$confCampo->horaMin."','".$confCampo->horaMax."','".$confCampo->intervalo."')";
					$x++;
					$consulta[$x]=	"insert into 901_elementosFormulario(nombreCampo,idIdioma,idFormulario,
									tipoElemento,idGrupoElemento,maxValorRespuesta,posX,posY,obligatorio,eliminable,orden) values('".cv($objJson->nomCampo)."',".$_SESSION["leng"].
									",".$idFormulario.",".$tipoElemento.",".$idPregunta.",".$maxValorOpcion.",".$posX.",".$posY.",".$obligatorio.",".$eliminable.",".$orden.")";
					$x++;
				break;
				case 22:  //Campo operación
					$accion=$objJson->accion;
					$arrTokens=$objJson->arrTokens;
					$ct=sizeof($arrTokens);
					if($objJson->accion=="-1")
					{
						$consulta[$x]="insert into 901_elementosFormulario(nombreCampo,idIdioma,idFormulario,
											tipoElemento,idGrupoElemento,maxValorRespuesta,posX,posY,obligatorio,eliminable,orden) values('".cv($objJson->nomCampo)."',1
											,".$idFormulario.",".$tipoElemento.",".$idPregunta.",".$maxValorOpcion.",".$posX.",".$posY.",".$obligatorio.",".$eliminable.",".$orden.")";
						$x++;
						$consulta[$x]="insert into 904_configuracionElemFormulario(idElemFormulario,campoConf1,campoConf2,campoConf3,campoConf4,campoConf12) values(".$idPregunta.",'2',',','.','1','".$claseDefault."')";
						$x++;
						$idElemFormulario=$idPregunta;
					}
					else
					{
						$idElemFormulario=$objJson->accion;
						$consulta[$x]="delete from 929_operacionesCampoExpresion where idElemFormulario=".$idElemFormulario;
						$x++;
					}
					
					for($n=0;$n<$ct;$n++)
					{
						$consulta[$x]="insert into 929_operacionesCampoExpresion(idElemFormulario,valorUsr,valorApp,tipoOperador) 
									values(".$idElemFormulario.",'".$arrTokens[$n]->tokenUsr."','".$arrTokens[$n]->tokenApp."',".$arrTokens[$n]->tipoToken.")";
						$x++;
					}
				break;
				case 23: //Control Imagen
					$confCampo=$objJson->confCampo;
					$consulta[$x]="	insert into 904_configuracionElemFormulario(idElemFormulario,campoConf1,campoConf2,campoConf3) 
									values(".$idPregunta.",'".$confCampo->ancho."','".$confCampo->alto."','".$objJson->idImagen."')";
					$x++;
					$consulta[$x]=	"insert into 901_elementosFormulario(nombreCampo,idIdioma,idFormulario,
									tipoElemento,idGrupoElemento,posX,posY,obligatorio,eliminable,orden) values('".cv($objJson->nomCampo)."',".$_SESSION["leng"].
									",".$idFormulario.",".$tipoElemento.",".$idPregunta.",".$posX.",".$posY.",".$obligatorio.",".$eliminable.",".$orden.")";
					$x++;
				
				break;
				case 25: //Control Imagen
					$confCampo=$objJson->confCampo;
					$consulta[$x]="	insert into 904_configuracionElemFormulario(idElemFormulario,campoConf1,campoConf2,campoConf3) 
									values(".$idPregunta.",'".$confCampo->ancho."','".cv($confCampo->formato)."','".$confCampo->origenFecha."')";
					$x++;
					$consulta[$x]=	"insert into 901_elementosFormulario(nombreCampo,idIdioma,idFormulario,
									tipoElemento,idGrupoElemento,posX,posY,obligatorio,eliminable,orden) values('".cv($objJson->nomCampo)."',".$_SESSION["leng"].
									",".$idFormulario.",".$tipoElemento.",".$idPregunta.",".$posX.",".$posY.",0,".$eliminable.",".$orden.")";
					$x++;
				break;
				case 32:
				case 24: //Moneda
					$confCampo=$objJson->confCampo;
					$consulta[$x]="	insert into 904_configuracionElemFormulario(idElemFormulario,campoConf1,campoConf2,campoConf3,campoConf5,campoConf6) 
									values(".$idPregunta.",'2',',','.','0','10')";
					$x++;
					$consulta[$x]="insert into 901_elementosFormulario(nombreCampo,idIdioma,idFormulario,
										tipoElemento,idGrupoElemento,maxValorRespuesta,posX,posY,obligatorio,eliminable,orden) values('".cv($objJson->nomCampo)."',".$_SESSION["leng"].
										",".$idFormulario.",".$tipoElemento.",".$idPregunta.",".$maxValorOpcion.",".$posX.",".$posY.",".$obligatorio.",".$eliminable.",".$orden.")";
					$x++;
				
				break;
				case 25: //Moneda
					$confCampo=$objJson->confCampo;
					$consulta[$x]="	insert into 904_configuracionElemFormulario(idElemFormulario,campoConf1,campoConf2,campoConf3,campoConf5,campoConf6) 
									values(".$idPregunta.",'2',',','.','0','10')";
					$x++;
					$consulta[$x]="insert into 901_elementosFormulario(nombreCampo,idIdioma,idFormulario,
										tipoElemento,idGrupoElemento,maxValorRespuesta,posX,posY,obligatorio,eliminable,orden) values('".cv($objJson->nomCampo)."',".$_SESSION["leng"].
										",".$idFormulario.",".$tipoElemento.",".$idPregunta.",".$maxValorOpcion.",".$posX.",".$posY.",".$obligatorio.",".$eliminable.",".$orden.")";
					$x++;
				
				break;
				case 30: //label con conexon a almacen
					$consulta[$x]="	insert into 904_configuracionElemFormulario(idElemFormulario,campoConf1,campoConf2,campoConf3,campoConf12) 
									values(".$idPregunta.",'".$objJson->almacen."','".cv($objJson->campo)."','".cv($objJson->campoEtUsuario)."','".$claseDefault."')";
					$x++;
					$consulta[$x]="insert into 901_elementosFormulario(nombreCampo,idIdioma,idFormulario,
										tipoElemento,idGrupoElemento,posX,posY,obligatorio,eliminable,orden) values('".cv($objJson->nomCampo)."',".$_SESSION["leng"].
										",".$idFormulario.",".$tipoElemento.",".$idPregunta.",".$posX.",".$posY.",".$obligatorio.",".$eliminable.",".$orden.")";
					$x++;
				break;
				case 31:
					$confCampo=$objJson->confCampo;
					$consulta[$x]="	insert into 904_configuracionElemFormulario(idElemFormulario,campoConf3,campoConf12) 
									values(".$idPregunta.",'".$confCampo->parametro."','".$claseDefault."')";
					//echo $consulta[$x];
					$x++;
					$consulta[$x]="insert into 901_elementosFormulario(nombreCampo,idIdioma,idFormulario,
										tipoElemento,idGrupoElemento,maxValorRespuesta,posX,posY,obligatorio,eliminable,orden) values('".cv($objJson->nomCampo)."',".$_SESSION["leng"].
										",".$idFormulario.",".$tipoElemento.",".$idPregunta.",".$maxValorOpcion.",".$posX.",".$posY.",".$obligatorio.",".$eliminable.",".$orden.")";
						//echo $consulta[$x];
					$x++;
				break;
				case 33: //Control Imagen
					$confCampo=$objJson->confCampo;
					$consulta[$x]="	insert into 904_configuracionElemFormulario(idElemFormulario,campoConf1,campoConf2) 
									values(".$idPregunta.",'".$confCampo->ancho."','".$confCampo->alto."')";
					$x++;
					$consulta[$x]=	"insert into 901_elementosFormulario(nombreCampo,idIdioma,idFormulario,
									tipoElemento,idGrupoElemento,posX,posY,obligatorio,eliminable,orden) values('".cv($objJson->nomCampo)."',".$_SESSION["leng"].
									",".$idFormulario.",".$tipoElemento.",".$idPregunta.",".$posX.",".$posY.",".$obligatorio.",".$eliminable.",".$orden.")";
					$x++;
				
				break;
			}
			if($tipoProceso!=1000)
			{
				$query="select nombreTabla from 900_formularios where idFormulario=".$idFormulario;
				$nomTabla=$con->obtenerValor($query);
				$queryAux="";
				$existeT=$con->existeTabla($nomTabla);
				if($existeT)
				{
					if(($tipoElemento>1)&&($tipoElemento!=13)&&(!(($tipoElemento>=17)&&($tipoElemento<=19)))&&($tipoElemento!=23)&&($tipoElemento!=33))
					{
						switch($tipoElemento)
						{
							case 14:
							case 2: //pregunta cerrada-Opciones Manuales
								$confCampo=" varchar(30) character set utf8 collate utf8_spanish2_ci default NULL";
							break;
							case 15:
							case 3: //pregunta cerrada-Opciones intervalo
								$confCampo=" decimal(16,4) default NULL";
							break;
							case 16:
							case 4: //pregunta cerrada-Opciones tabla
								$confCampo=" varchar(150) character set utf8 collate utf8_spanish2_ci default NULL";
								//$queryAux="alter table `".$con->bdActual."`.`".$nomTabla."` add constraint `FK_".cv($objJson->nomCampo)."` FOREIGN KEY (`".cv($objJson->nomCampo)."`) REFERENCES `".$objTablaConf->tabla."`(`".$campoID."`)";
							break;
							case 5: //Texto Corto
								$objCampo=$objJson->confCampo;
								$confCampo=" varchar(".$objCampo->longitud.") character set utf8 collate utf8_spanish2_ci default NULL";
							break;
							case 6: //Número entero
								$confCampo=" int(11) default NULL";
							break;
							case 7: //Número decimal
								$confCampo=" decimal(16,4) NOT NULL";		
							break;
							case 8: //Fecha
								$confCampo=" date default NULL";
							break;
							case 9://Texto Largo 
								$confCampo=" longtext collate utf8_spanish2_ci";
							break;
							case 10: //Texto Enriquecido
								$confCampo=" longtext collate utf8_spanish2_ci";
							break;
							case 11: //Correo Electrónico
								$objCampo=$objJson->confCampo;
								$confCampo=" varchar(".$objCampo->longitud.") character set utf8 collate utf8_spanish2_ci default NULL";
							break;
							case 12: //Archivo
								$confCampo=" int(11) default NULL";
							break;
							case 20:
								switch($confCampo->vSesion)
								{
									case 1:
										$confCampo=" varchar(40) character set utf8 collate utf8_spanish2_ci default NULL";
									break;
									case 2:
										$confCampo=" varchar(40) character set utf8 collate utf8_spanish2_ci default NULL";
									break;
									case 3:
										$confCampo=" int(11) default NULL";
									break;
									case 4:
										$confCampo=" varchar(40) character set utf8 collate utf8_spanish2_ci default NULL";
									break;
									case 5:
										$confCampo=" varchar(60) character set utf8 collate utf8_spanish2_ci default NULL";
									break;
									case 6:
										$confCampo=" date default NULL";
									break;
									case 7:
										$confCampo=" time default NULL";
									break;
								}
							break;
							case 21: //Fecha
								$confCampo=" time default NULL";
							break;
							case 22: //Campo Operación
									$confCampo=" decimal(16,4) default NULL";
							break;
							case 24: //Campo Operación
									$confCampo=" decimal(16,4) default NULL";
							break;
							case 25: //Hora/Fecha Solo lectura
									$confCampo=" datetime default NULL";
							break;
							case 31:
									$confCampo=" varchar(255) character set utf8 collate utf8_spanish2_ci default NULL";
							break;
							case 32: //Color
								$objCampo=$objJson->confCampo;
								$confCampo=" varchar(10) character set utf8 collate utf8_spanish2_ci default NULL";
							break;
	
	
						}
						
						
						
						if((!(($tipoElemento>=17)&&($tipoElemento<=19)))&&($tipoElemento!=30))
						{
							if(($tipoElemento!=22)||($objJson->accion=="-1"))
							{
								$consulta[$x]="alter table `".$con->bdActual."`.`".$nomTabla."` add column `".cv($objJson->nomCampo)."` ".$confCampo;
								//echo $consulta[$x];
								$x++;
							}
						}
						
						if($queryAux!="")
						{
							$consulta[$x]=$queryAux;
							$x++;
						}
						
					}
					
				}
			}
			$consulta[$x]="update 903_variablesSistema set idElementoFormularioSig=idElementoFormularioSig+1,idOpcionFormularioSig=idOpcionFormularioSig+".$numOpcionesAgregadas;
			$x++;
			$consulta[$x]="commit";
			$x++;
			
			if($con->ejecutarBloque($consulta))
			{
				if(($tipoElemento==14)||($tipoElemento==17))
				{
					$queryOpt="select valor,contenido from 902_opcionesFormulario where idGrupoElemento=".$idPregunta." and idIdioma=".$_SESSION["leng"]." order by contenido";
					$arrElemento=$con->obtenerFilasArreglo($queryOpt);
				}
				return "1|".$idPregunta."|".uEJ($arrElemento);	
			}
			else
				return "|";
		
		}
	}	
	
	function crearControlVistaFormulario($defControl,$decodeJSON=true,$idForm="-1")
	{
		global $con;
		global $et;
		if($decodeJSON)
		{
			$objJson=json_decode($defControl);
			$idFormulario=$objJson->idFormulario;
		}
		else
		{
			$objJson=$defControl;
			$idFormulario=$idForm;
		}
		
		$pregunta=$objJson->pregunta;
		$obligatorio=$objJson->obligatorio;
		$tipoElemento=	$objJson->tipoElemento;
		$posX=$objJson->posX;
		$posY=$objJson->posY;
		$eliminable="1";
		if(isset($objJson->eliminable))
			$eliminable=$objJson->eliminable;
		$arrElemento="";
		$query="begin";
		$maxValorOpcion=0;
		$consultaAux='select nombreTabla from 900_formularios where idFormulario='.$idFormulario;
		$nomTabla=$con->obtenerValor($consultaAux);		
		if($con->ejecutarConsulta($query))
		{
			$numPreguntas=sizeof($pregunta);
			$query="select idElementoFormularioSig from 903_variablesSistema for update";
			$idPregunta=$con->obtenerValor($query);
			$query="select idOpcionFormularioSig from 903_variablesSistema for update";
			$idGrupoOpcion=$con->obtenerValor($query);
			$numOpcionesAgregadas=0;
			$x=0;
			$orden=0;
			switch($tipoElemento)
			{
				case 1: //label
					for($ct=0;$ct<$numPreguntas;$ct++)
					{
						$consulta[$x]="insert into 937_elementosVistaFormulario(nombreCampo,idIdioma,idFormulario,
										tipoElemento,idGrupoElemento,maxValorRespuesta,posX,posY,obligatorio,eliminable,orden) values('".cv($pregunta[$ct]->etiqueta)."',".$pregunta[$ct]->idIdioma.
										",".$idFormulario.",".$tipoElemento.",".$idPregunta.",".$maxValorOpcion.",".$posX.",".$posY.",".$obligatorio.",".$eliminable.",".$orden.")";
						$x++;
						
					}
					$consulta[$x]="	insert into 938_configuracionElemVistaFormulario(idElemFormulario,campoConf12) 
									values(".$idPregunta.",'letraFicha')";
					$x++;
				break;
				
				case 13: //frame
					$confCampo=$objJson->confCampo;
					$consulta[$x]="	insert into 938_configuracionElemVistaFormulario(idElemFormulario,campoConf1,campoConf2) 
									values(".$idPregunta.",'".$confCampo->ancho."','".$confCampo->alto."')";
					$x++;
					for($ct=0;$ct<$numPreguntas;$ct++)
					{
						$consulta[$x]="insert into 937_elementosVistaFormulario(nombreCampo,idIdioma,idFormulario,
										tipoElemento,idGrupoElemento,maxValorRespuesta,posX,posY,obligatorio,eliminable,orden) values('".cv($pregunta[$ct]->etiqueta)."',".$pregunta[$ct]->idIdioma.
										",".$idFormulario.",".$tipoElemento.",".$idPregunta.",".$maxValorOpcion.",".$posX.",".$posY.",".$obligatorio.",".$eliminable.",".$orden.")";
						$x++;
					}
					
				break;
				case 23: //Control Imagen
					$confCampo=$objJson->confCampo;
					$consulta[$x]="	insert into 938_configuracionElemVistaFormulario(idElemFormulario,campoConf1,campoConf2,campoConf3) 
									values(".$idPregunta.",'".$confCampo->ancho."','".$confCampo->alto."','".$objJson->idImagen."')";
					$x++;
					$consulta[$x]=	"insert into 937_elementosVistaFormulario(nombreCampo,idIdioma,idFormulario,
									tipoElemento,idGrupoElemento,posX,posY,obligatorio,eliminable,orden) values('".cv($objJson->nomCampo)."',".$_SESSION["leng"].
									",".$idFormulario.",".$tipoElemento.",".$idPregunta.",".$posX.",".$posY.",".$obligatorio.",".$eliminable.",".$orden.")";
					$x++;
				
				break;
				
			}

			
			$consulta[$x]="update 903_variablesSistema set idElementoFormularioSig=idElementoFormularioSig+1,idOpcionFormularioSig=idOpcionFormularioSig+".$numOpcionesAgregadas;
			$x++;
			$consulta[$x]="commit";
			$x++;
			if($con->ejecutarBloque($consulta))
			{
				
				return "1|".$idPregunta."|".uEJ($arrElemento);	
			}
			else
				return "|";
		
		}
	}

	function configurarListadoFormulario($defListado,$idFormulario)
	{
		global $con;
		$consulta="select idConfGrid from 909_configuracionTablaFormularios where idFormulario=".$idFormulario;
		$idConf=$con->obtenerValor($consulta);
		if($idConf=="")
			$idConf=-1;
		$campoOrden=$defListado->campoOrden;
		$orden="ASC";
		$campoAgrupacion="0";
		if(isset($defListado->orden))
			$orden=$defListado->orden;
		$regPag="25";
		if(isset($defListado->regPag))
			$regPag=$defListado->regPag;
		if(isset($defListado->campoAgrupacion))
			$campoAgrupacion=$defListado->campoAgrupacion;
			
		
		$consulta="update 909_configuracionTablaFormularios set campoOrden='".$campoOrden."',direccionOrden='".$orden."',numRegPag=".$regPag.",campoAgrupacion='".$campoAgrupacion."'  where idConfGrid=".$idConf;
		if($con->ejecutarConsulta($consulta))
		{
			$campos=$defListado->campos;
			foreach($campos as $campo)
			{
				if(!insertarCampoListado($campo,$idConf,$idFormulario))
					return false;
			}
		}
		return true;
	}
	
	function insertarCampoListado($campo,$idConfiguracion,$idFormulario)
	{
		global $con;
		$query="select idGridFormularioSig from 903_variablesSistema for update";
		$idGrupoCampo=$con->obtenerValor($query);
		$nomCampo=$campo->campo;
		$query="select idGrupoElemento from 901_elementosFormulario where idFormulario=".$idFormulario." and nombreCampo='".$nomCampo."'";
		$idCampo=$con->obtenerValor($query);
		$anchoCol=$campo->anchoCol;
		$tituloCampo=$campo->titulo;
		$accion=$campo->accion;
		$idAlineacion=$campo->idAlineacion;
		
		$x=0;
		$numTitulos=sizeof($tituloCampo);
		for($ct=0;$ct<$numTitulos;$ct++)
		{
			$consulta[$x]="	insert into 907_camposGrid(idElementoFormulario,idIdioma,tamanoColumna,titulo,idGrupoCampo,alineacionValores,idConfGrid) values
							(".$idCampo.",".$tituloCampo[$ct]->idIdioma.",".$anchoCol.",'".cv($tituloCampo[$ct]->etiqueta)."',".$idGrupoCampo.",".$idAlineacion.",".$idConfiguracion.")";
			$x++;
		}
		$consulta[$x]="update 903_variablesSistema set idGridFormularioSig=idGridFormularioSig+1";
		if(!$con->ejecutarBloque($consulta))
			return false;
		return true;
			
	}
		
	function crearControlVistaElemento($defControl,$decodeJSON=true,$idReporte="-1")
	{
		global $con;
		global $et;
		if($decodeJSON)
		{

			$objJson=json_decode($defControl);
			$idReporte=$objJson->idReporte;
		}
		else
		{
			$objJson=$defControl;
			$idReporte=$idReporte;
		}
		
		
		$pregunta=$objJson->pregunta;
		$tipoElemento=	$objJson->tipoElemento;
		$tipoVinculo="0";
		if(isset($objJson->tipo))
			$tipoVinculo=	$objJson->tipo;
		
		$idAlmacen="-1";
		if(isset($objJson->idAlmacen))
			$idAlmacen=$objJson->idAlmacen;
		$idPadre="-1";
		if(isset($objJson->idPadre))
			$idPadre=$objJson->idPadre;
		$posX=$objJson->posX;
		$posY=$objJson->posY;
		$eliminable="1";
		if(isset($objJson->eliminable))
			$eliminable=$objJson->eliminable;
		$arrElemento="";
		$query="begin";
		$maxValorOpcion=0;
		$comp="|";
		if($con->ejecutarConsulta($query))
		{
			$numPreguntas=sizeof($pregunta);
			$query="select idElementoReporteSig from 903_variablesSistema for update";
			$idPregunta=$con->obtenerValor($query);
			$numOpcionesAgregadas=0;
			$x=0;
			$orden=0;
			switch($tipoElemento)
			{
				case 1: //label
					for($ct=0;$ct<$numPreguntas;$ct++)
					{
						$consulta[$x]="insert into 9011_elementosReportesThot(nombreCampo,idIdioma,idReporte,
										tipoElemento,idGrupoElemento,posX,posY,eliminable,idPadre) 
										values('".cv($pregunta[$ct]->etiqueta)."',".$pregunta[$ct]->idIdioma.
										",".$idReporte.",".$tipoElemento.",".$idPregunta.",".$posX.",".$posY.",".$eliminable.",".$idPadre.")";
						$x++;
						
					}
					$consulta[$x]="	insert into 9012_configuracionElemReporteThot(idElemReporte,campoConf12) 
									values(".$idPregunta.",'letraExt')";
					$x++;
				break;
				case 23: //Control Imagen
					$confCampo=$objJson->confCampo;
					$consulta[$x]="	insert into 9012_configuracionElemReporteThot(idElemReporte,campoConf1,campoConf2,campoConf3) 
									values(".$idPregunta.",'".$confCampo->ancho."','".$confCampo->alto."','".$objJson->idImagen."')";
					$x++;
					$consulta[$x]=	"insert into 9011_elementosReportesThot(nombreCampo,idIdioma,idReporte,
									tipoElemento,idGrupoElemento,posX,posY,eliminable,idPadre) values('".cv($objJson->nomCampo)."',".$_SESSION["leng"].
									",".$idReporte.",".$tipoElemento.",".$idPregunta.",".$posX.",".$posY.",".$eliminable.",".$idPadre.")";
					$x++;
				break;
				case 25: //Sección
					$confCampo=$objJson->confCampo;
					$consulta[$x]="	insert into 9012_configuracionElemReporteThot(idElemReporte,campoConf1,campoConf2,campoConf3) 
									values(".$idPregunta.",'".$confCampo->ancho."','".$confCampo->alto."','".$confCampo->tipoSeccion."')";

					$x++;
					$consulta[$x]=	"insert into 9011_elementosReportesThot(idIdioma,idReporte,
									tipoElemento,idGrupoElemento,posX,posY,eliminable,idPadre) values(".$_SESSION["leng"].
									",".$idReporte.",".$tipoElemento.",".$idPregunta.",".$posX.",".$posY.",".$eliminable.",".$idPadre.")";
					$x++;									
				break;
				case 27:
					$consulta[$x]="insert into 9011_elementosReportesThot(nombreCampo,idIdioma,idReporte,
										tipoElemento,idGrupoElemento,posX,posY,eliminable,idPadre) 
										values('',".$_SESSION["leng"].
										",".$idReporte.",".$tipoElemento.",".$idPregunta.",".$posX.",".$posY.",".$eliminable.",".$idPadre.")";
					$x++;
					$consulta[$x]="	insert into 9012_configuracionElemReporteThot(idElemReporte,campoConf12,campoConf3,campoConf4) 
									values(".$idPregunta.",'letraExt',1,1)";
					$x++;
				break;
				case 28:
					
					$consulta[$x]="insert into 9011_elementosReportesThot(nombreCampo,idIdioma,idReporte,
										tipoElemento,idGrupoElemento,posX,posY,eliminable,idPadre) 
										values('',".$_SESSION["leng"].
										",".$idReporte.",".$tipoElemento.",".$idPregunta.",".$posX.",".$posY.",".$eliminable.",".$idPadre.")";
					$x++;
					if(($objJson->tipo==1)||($objJson->tipo==2))
					{
						$consulta[$x]="	insert into 9012_configuracionElemReporteThot(idElemReporte,campoConf8,campoConf9,campoConf10,campoConf12) 
										values(".$idPregunta.",'".$tipoVinculo."','".$idAlmacen."','".$objJson->campoProy."','letraExt')";
						$x++;
						$campo=$objJson->campoProy;
						$arrDatoCampo=explode("_",$campo);
						if(strpos($campo,"tablaDinamica")!==false)
						{
							$campoAux=ucfirst($arrDatoCampo[2]);
							$arrCampo2=explode(".",$campoAux);
							$query="select nombreFormulario from 900_formularios where nombreTabla='_".$arrDatoCampo[1]."_".$arrCampo2[0]."'";
							$tituloProceso=$con->obtenerValor($query);
							$campoAux=str_replace("TablaDinamica",$tituloProceso,$campoAux);	
						}
						else
						{
							if(isset($arrDatoCampo[1]))
								$campoAux=ucfirst($arrDatoCampo[1]);	
							else
								$campoAux=ucfirst($arrDatoCampo[0]);	
						}
						$comp="|".$campoAux;
					}
					else
					{
						$consulta[$x]="	insert into 9012_configuracionElemReporteThot(idElemReporte,campoConf8,campoConf9,campoConf10,campoConf12) 
										values(".$idPregunta.",'".$tipoVinculo."','".$objJson->valor."','".$objJson->valorUsr."','letraExt')";
						$x++;
						$comp="|".$objJson->valorUsr;
					}
				break;
				case 31:
					$cadConf="";
					$confCampo=$objJson->confCampo;
					$query="select objPropiedadesGrafico from 9026_tiposGraficos WHERE idTiposGraficos=".$objJson->tipoGrafico;
					$cadPropiedades=str_replace("\n","",$con->obtenerValor($query));
					$cadPropiedades=str_replace("\r","",$cadPropiedades);
					$cadPropiedades=str_replace("\t","",$cadPropiedades);

					$objPropiedades=json_decode($cadPropiedades);
					
					$cadConf='"caption":"'.cv($objJson->tituloGrafico).'"';
					foreach($objPropiedades->arrPropiedades as $obj)
					{
						if(isset($obj->valorDefault))
						{
							$objConf='"'.$obj->id.'":"'.$obj->valorDefault.'"';
							$cadConf.=",".$objConf;
						}
					}
					$cadConf='{'.$cadConf.'}';
					$consulta[$x]="	insert into 9012_configuracionElemReporteThot(idElemReporte,campoConf1,campoConf2,campoConf3,campoConf5,campoConf7) 
									values(".$idPregunta.",'".$confCampo->ancho."','".$confCampo->alto."','".$objJson->tipoGrafico."',".$objJson->idAlmacen.",'".cv($cadConf)."')";
					$x++;
					$consulta[$x]=	"insert into 9011_elementosReportesThot(nombreCampo,idIdioma,idReporte,
									tipoElemento,idGrupoElemento,posX,posY,eliminable,idPadre) values('',".$_SESSION["leng"].
									",".$idReporte.",".$tipoElemento.",".$idPregunta.",".$posX.",".$posY.",".$eliminable.",".$idPadre.")";
					$x++;
					$comp="|".bE($cadConf."|".$cadPropiedades);

				break;
			}
			$consulta[$x]="update 903_variablesSistema set idElementoReporteSig=idElementoReporteSig+1";
			$x++;
			$consulta[$x]="commit";
			$x++;
			if($con->ejecutarBloque($consulta))
				return "1|".trim($idPregunta).$comp;	
			else
				return "|";
		}
		else
			return "|";
	}
	
	function obtenerValorControlFormularioBase($idElemento,$idRegistro,$objReg=null)
	{
		global $con;
		global $urlSitio;
		if($idElemento>0)
		{
			$consulta="SELECT tipoElemento,nombreCampo,idFormulario FROM 901_elementosFormulario WHERE idGrupoElemento=".$idElemento;
	
			$filaElemento=$con->obtenerPrimeraFila($consulta);
			
			$idFormulario=$filaElemento[2];
			if($filaElemento)
			{
				$valor="";
				switch($filaElemento[0])
				{
					case 6: //Número entero
						$consulta="select * from 904_configuracionElemFormulario where idElemFormulario=".$idElemento;
						$filaE=$con->obtenerPrimeraFila($consulta);
						$sepMiles=$filaE[3];
						$consulta="select `".$filaElemento[1]."` from _".$filaElemento[2]."_tablaDinamica where id__".$filaElemento[2]."_tablaDinamica=".$idRegistro;
						$valor=number_format($con->obtenerValor($consulta),0);
						$valor=str_replace(",",$sepMiles,$valor);
					break;
					case 7: //Número decimal
						$consulta="select * from 904_configuracionElemFormulario where idElemFormulario=".$idElemento;
						$filaE=$con->obtenerPrimeraFila($consulta);
						$sepDecimales=$filaE[4];
						$sepMiles=$filaE[3];
						$nDecimales=$filaE[7];
						$consulta="select `".$filaElemento[1]."` from _".$filaElemento[2]."_tablaDinamica where id__".$filaElemento[2]."_tablaDinamica=".$idRegistro;
						$valor=number_format($con->obtenerValor($consulta),$nDecimales);
						$valor=str_replace(".",$sepDecimales,str_replace(",",$sepMiles,$valor));
					break;
					case 8: //Fecha
						$consulta="select `".$filaElemento[1]."` from _".$filaElemento[2]."_tablaDinamica where id__".$filaElemento[2]."_tablaDinamica=".$idRegistro;
						$valor=$con->obtenerValor($consulta);
						if($valor!="")
							$valor=date("d/m/Y",strtotime($valor));
					break;
					case 9://Texto Largo 
						//$etiqueta= "<span class='".$claseRespuesta."'>".$valorCelda."</span>";
						$consulta="select `".$filaElemento[1]."` from _".$filaElemento[2]."_tablaDinamica where id__".$filaElemento[2]."_tablaDinamica=".$idRegistro;
						$valor=$con->obtenerValor($consulta);
					break;
					case 10: //Texto Enriquecido
						$consulta="select `".$filaElemento[1]."` from _".$filaElemento[2]."_tablaDinamica where id__".$filaElemento[2]."_tablaDinamica=".$idRegistro;
						$valor=$con->obtenerValor($consulta);
					break;
					case 11: //Correo Electrónico
						$consulta="select `".$filaElemento[1]."` from _".$filaElemento[2]."_tablaDinamica where id__".$filaElemento[2]."_tablaDinamica=".$idRegistro;
						$valor=$con->obtenerValor($consulta);
						
					break;
					case 12: //Archivo
						$consulta="select `".$filaElemento[1]."` from _".$filaElemento[2]."_tablaDinamica where id__".$filaElemento[2]."_tablaDinamica=".$idRegistro;
						$valor=$con->obtenerValor($consulta);
						$consulta="select nomArchivoOriginal from 908_archivos where idArchivo=".$valor;
						$valor="<a href=".$urlSitio."/paginasFunciones/obtenerArchivos.php?id=".bE($valor).">".$con->obtenerValor($consulta)."</a>";
					break;
					case 5:
					case 9:
					case 10:
						$consulta="select `".$filaElemento[1]."` from _".$filaElemento[2]."_tablaDinamica where id__".$filaElemento[2]."_tablaDinamica=".$idRegistro;
						$valor=$con->obtenerValor($consulta);
					break;
					case 2:
					case 14:
						$consulta="select `".$filaElemento[1]."` from _".$filaElemento[2]."_tablaDinamica where id__".$filaElemento[2]."_tablaDinamica=".$idRegistro;
						$valorCampo=$con->obtenerValor($consulta);
						$queryOpt="select contenido from 902_opcionesFormulario where valor='".$valorCampo."' and idGrupoElemento=".$idElemento." and idIdioma=".$_SESSION["leng"]." order by contenido";
						$valor=	$con->obtenerValor($queryOpt);	
						
					break;
					case 3:
					case 15:
						$consulta="select `".$filaElemento[1]."` from _".$filaElemento[2]."_tablaDinamica where id__".$filaElemento[2]."_tablaDinamica=".$idRegistro;
						$valorCampo=$con->obtenerValor($consulta);
						$valor=$valorCampo;
						
					break;
					case 4:
					case 16:
						$idProceso=obtenerIdProcesoFormulario($filaElemento[2]);
						$consulta="select idReferencia,responsable from _".$filaElemento[2]."_tablaDinamica where id__".$filaElemento[2]."_tablaDinamica=".$idRegistro;
						$filaRegistro=$con->obtenerPrimeraFila($consulta);
						$numEtapaPadre=obtenerEtapaProcesoActual($idRegistro,$filaRegistro[0],$filaElemento[2]);
						$paramObj=$objReg;
						if($objReg==null)
						{
							$cadObj='{"p16":{"p1":"'.$filaElemento[2].'","p2":"'.$idProceso.'","p3":"'.$idRegistro.'","p4":"'.$numEtapaPadre.'","p5":"'.$filaRegistro[0].'","p6":"'.$filaRegistro[1].'"}}';
							$paramObj=json_decode($cadObj);
						}
						$arrQueries=resolverQueries($idFormulario,5,$paramObj,true);
						
						$consulta="select ".$filaElemento[1]." from _".$filaElemento[2]."_tablaDinamica where id__".$filaElemento[2]."_tablaDinamica=".$idRegistro;
						$valorCampo=$con->obtenerValor($consulta);
						
						$consulta="select * from 904_configuracionElemFormulario where idElemFormulario=".$idElemento;
						$filaE=$con->obtenerPrimeraFila($consulta);
						if($valorCampo!="")
						{
							if(strpos($filaE["2"],"[")===false)
							{
								$filaE["4"]=str_replace("distinct","",$filaE[4]);
								$query="select concat(".$filaE["3"].") from ".$filaE["2"]." where ".$filaE["4"]."=".$valorCampo;	
								$valorCampo=$con->obtenerValor($query);
							}
							else
							{
								
								$tablaOD=$filaE["2"];
								$tablaOD=str_replace("[","",$tablaOD);
								$tablaOD=str_replace("]","",$tablaOD);
								$arrCamposProy=explode('@@',$filaE[3]);
								$campoId=$filaE[4];
								
								if($arrQueries[$tablaOD]["ejecutado"]==1)
								{
									
									$resQuery=$arrQueries[$tablaOD]["resultado"];
									$conAux=$arrQueries[$tablaOD]["conector"];
									$conAux->inicializarRecurso($resQuery);	
									
									$cadObj='{"conector":null,"resQuery":null,"idAlmacen":"","arrCamposProy":[],"formato":"4","imprimir":"0","query":"'.$arrQueries[$tablaOD]["query"].'","campoID":"'.$campoId.'"}';
									$obj=json_decode($cadObj);
									$obj->resQuery=$resQuery;
									$obj->idAlmacen=$tablaOD;
									$obj->arrCamposProy=$arrCamposProy;
									$obj->itemSelect=$valorCampo;
									$obj->conector=$conAux;
									$valorCampoAux=generarFormatoOpcionesQuery($obj);
									if($valorCampoAux!="")
										$valorCampo=$valorCampoAux;
								}
							}
							
						}

						$valor=$valorCampo;
					break;
					case 17:
						$queryTabla="select idOpcion from _".$filaElemento[2]."_".$filaElemento[1]." where idPadre=".$idRegistro;
						$listValores=$con->obtenerListaValores($query,"'");
						if($listValores=="")
							$listValores="-1";
						$queryOpt="select valor,contenido from 902_opcionesFormulario where idGrupoElemento=".$idElemento." and idIdioma=".$_SESSION["leng"]." and valor in (".$listValores.") order by contenido";
						$resOpt=$con->generarArreglo($queryOpt);
						$valor=$resOpt;
						
					break;
					case 18:
						$queryOpt="select * from 904_configuracionElemFormulario where idElemFormulario=".$idElemento;
						$filaE=$con->obtenerPrimeraFila($queryOpt);
						$arrNomTabla=explode('_',$nombreTabla);
						$queryTabla="select idOpcion,idOpcion from _".$filaElemento[2]."_".$filaElemento[1]." where idPadre=".$idRegistro;
						$resOpt=$con->generarArreglo($queryOpt);
						$valor=$resOpt;
					break;
					case 19:
						$idProceso=obtenerIdProcesoFormulario($filaElemento[2]);
						$consulta="select idReferencia,responsable from _".$filaElemento[2]."_tablaDinamica where id__".$filaElemento[2]."_tablaDinamica=".$idRegistro;
						$filaRegistro=$con->obtenerPrimeraFila($consulta);
						$numEtapaPadre=obtenerEtapaProcesoActual($idRegistro,$filaRegistro[0],$filaElemento[2]);
						$cadObj='{"p16":{"p1":"'.$filaElemento[2].'","p2":"'.$idProceso.'","p3":"'.$idRegistro.'","p4":"'.$numEtapaPadre.'","p5":"'.$filaRegistro[0].'","p6":"'.$filaRegistro[1].'"}}';
						$paramObj=json_decode($cadObj);
						$arrQueries=resolverQueries($idFormulario,5,$paramObj);
						$consulta="select * from 904_configuracionElemFormulario where idElemFormulario=".$idElemento;
						$filaE=$con->obtenerPrimeraFila($consulta);
						$queryOpt="select distinct ".$filaE[4].",".$filaE[3]." from ".$filaE[2]." order by ".$filaE[3];
						$tablaOD=$filaE[2];
						$campoId=$filaE[4];
						$consulta="select tokenMysql from 913_consultasFiltroElemento where idGrupoElemento=".$idElemento;
						$resFiltro=$con->obtenerFilas($consulta);
						$obtenerOpciones=true;
						$arrElemento=NULL;
						$condWhere="";
						while($filaFiltro=mysql_fetch_row($resFiltro))
							$condWhere.=str_replace('@codigoUnidad',$_SESSION["codigoInstitucion"],$filaFiltro[0])." ";
						if($condWhere!="")
							$condWhere=" where ".$condWhere;
							
						$queryTabla="select idOpcion from _".$filaElemento[2]."_".$filaElemento[1]." where idPadre=".$idRegistro;
	
						$listValores=$con->obtenerListaValores($queryTabla,"'");
						if($listValores=="")
							$listValores="-1";
						if(strpos($tablaOD,"[")===false)
						{
							if($condWhere!="")
								$condWhere.=" and ".str_replace('"',"",$filaE[4])." in (".$listValores.")"; 
							else
								$condWhere=" where ".str_replace('"',"",$filaE[4])." in (".$listValores.")"; 
							$queryOpt="select ".str_replace('"',"",$filaE[4]).",".str_replace('"',"",$filaE[3])." from ".$filaE[2]." ".$condWhere."  order by ".$filaE[3];
							
						}
						else
						{
							$tablaOD=str_replace("[","",$tablaOD);
							$tablaOD=str_replace("]","",$tablaOD);
							$arrCamposProy=explode('@@',$filaE[3]);
							if($arrQueries[$tablaOD]["ejecutado"]==1)
							{
								$resQuery=$arrQueries[$tablaOD]["resultado"];
								$conAux=$arrQueries[$tablaOD]["conector"];
								$conAux->inicializarRecurso($resQuery);	
								
								$resQuery=$arrQueries[$tablaOD]["resultado"];
								$cadObj='{"conector":null,"resQuery":null,"idAlmacen":"","arrCamposProy":[],"formato":"3","imprimir":"0","campoID":"'.$campoId.'"}';
								$obj=json_decode($cadObj);
								$obj->resQuery=$resQuery;
								$obj->idAlmacen=$tablaOD;
								$obj->arrCamposProy=$arrCamposProy;
								$obj->conector=$conAux;
								$arrElemento=generarFormatoOpcionesQuery($obj);
								
								$obtenerOpciones=false;
								
							}
							else
								$obtenerOpciones=false;
						}
						
						
							
						if($obtenerOpciones)	
						{
							$resOpt=$con->generarArreglo($queryOpt);
							$valor=$resOpt;
							
							
						}
						else
						{
							$arrValores=explode(",",str_replace("'","",$listValores));
							$arrFinalValores=array();
							foreach($arrElemento as $opt)
							{
								
								if(existeValor($arrValores,$opt[0]))
									array_push($arrFinalValores,$opt);
							}
							$valor=$arrFinalValores;
							
						}
						
					break;
					case 21: //Hora
						$consulta="select `".$filaElemento[1]."` from _".$filaElemento[2]."_tablaDinamica where id__".$filaElemento[2]."_tablaDinamica=".$idRegistro;
						$valor=$con->obtenerValor($consulta);
						if($valor!="")
							$valor=date("H:i",strtotime($valor));
					break;
					case 22:   //calculo
						$consulta="select * from 938_configuracionElemVistaFormulario where idElemFormulario=".$idElemento;
						$filaE=$con->obtenerPrimeraFila($consulta);
						$consulta="select `".$filaElemento[1]."` from _".$filaElemento[2]."_tablaDinamica where id__".$filaElemento[2]."_tablaDinamica=".$idRegistro;
						$valor=number_format($con->obtenerValor($consulta),$filaE[2]);
						$valor=str_replace(".",$filaE[4],str_replace(",",$filaE[3],$valor));
						
					break;
					case 24: //Moneda
						$consulta="select * from 904_configuracionElemFormulario where idElemFormulario=".$idElemento;
						$filaE=$con->obtenerPrimeraFila($consulta);
						/*if($valorCelda=="")
							$valorCelda="0";*/
						$filaE=$con->obtenerPrimeraFila($consulta);
						$sepDecimales=$filaE[4];
						$sepMiles=$filaE[3];
						$nDecimales=$filaE[2];
						$consulta="select `".$filaElemento[1]."` from _".$filaElemento[2]."_tablaDinamica where id__".$filaElemento[2]."_tablaDinamica=".$idRegistro;
						$valor=number_format($con->obtenerValor($consulta),$nDecimales);
						$valor="$ ".str_replace(".",$sepDecimales,str_replace(",",$sepMiles,$valor));
						
					break;
					case 31:
						$consulta="select `".$filaElemento[1]."` from _".$filaElemento[2]."_tablaDinamica where id__".$filaElemento[2]."_tablaDinamica=".$idRegistro;
						$valor=$con->obtenerValor($consulta);
					break;
					default:
						$valor="-100584";
					break;
				}
				return $valor;
			}
			else
			{
				return "";
			}
		}
		else
		{
			
			$idElemento*=-1;
			$consulta="select sentenciaDefinicionValor from 9017_camposControlFormulario where idCampo=".$idElemento;
			$sentencia=$con->obtenerValor($consulta);
			$sentencia=str_replace('"',"",$sentencia);
			
			$sentencia=str_replace("@tablaFormulario","_".$obj->p16->p1."_tablaDinamica",$sentencia);
			$sentencia=str_replace("@idProceso",$obj->p16->p2,$sentencia);
			$consulta="select ".$sentencia." from _".$obj->p16->p1."_tablaDinamica where id__".$obj->p16->p1."_tablaDinamica=".$idRegistro;
			
			return $con->obtenerValor($consulta);
			
			
		}
		return "";
	}
	
	function obtenerIdElementoFormulario($nombreCampo,$idFormulario)
	{
		global $con;
		$consulta="SELECT idCampo FROM 9017_camposControlFormulario WHERE campoUsr='".$nombreCampo."'";
		
		$idElemento=$con->obtenerValor($consulta);
		if($idElemento!="")
			$idElemento*=-1;
		else
		{
			$consulta="SELECT idGrupoElemento FROM 901_elementosFormulario WHERE nombreCampo='".$nombreCampo."' AND idFormulario=".$idFormulario;
			$idElemento=$con->obtenerValor($consulta);
		}
		if($idElemento=="")
			$idElemento=0;
		return $idElemento;
	}
	
	function cancelarTemporizadoresProceso($idFormulario,$idReferencia,$etapa,$ejecutar=true)
	{
		global $con;
		$consulta="update 9055_diparadoresTemporizador set situacion=0 where idFormulario=".$idFormulario." and idReferencia=".$idReferencia." and nEtapa=".$etapa;
		if($ejecutar)
			return $con->ejecutarConsulta($consulta);
		else
			return $consulta;
	}
	
	function registrarTemporizadorProceso($idFormulario,$idReferencia,$etapa,$ejecutar=true)
	{
		global $con;

		$fechaHoy=strtotime(date("Y-m-d H:i"));
		$x=0;
		$query[$x]="begin";
		$x++;
		$idProceso=obtenerIdProcesoFormulario($idFormulario);
		$consulta="SELECT idFuncionTemporizador,noUnidades,idFuncionEjecucionDiaria,noUnidadesDiaria 
				FROM 9056_temporizadoresProceso WHERE idProceso=".$idProceso." AND nEtapa=".$etapa;
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$idFuncion=$fila[0];
			$fechaFin=strtotime("+".$fila[1]." days",$fechaHoy);
			$fechaTemp=date("Y-m-d H:i",$fechaFin);
			$query[$x]="INSERT INTO 9055_diparadoresTemporizador(idProceso,idFormulario,idReferencia,fechaTemporizador,nEtapa,idFuncionTemporizador)
						values(".$idProceso.",".$idFormulario.",".$idReferencia.",'".$fechaTemp."',".$etapa.",".$idFuncion.")";
			$x++;
			if($fila[2]!=0)
			{
				$fechaActual=$fechaHoy;
				$fechaActual=strtotime("+".$fila[3]." days",$fechaActual);
				while($fechaActual<$fechaFin)
				{
					$query[$x]="INSERT INTO 9055_diparadoresTemporizador(idProceso,idFormulario,idReferencia,fechaTemporizador,nEtapa,idFuncionTemporizador)
						values(".$idProceso.",".$idFormulario.",".$idReferencia.",'".date("Y-m-d H:i",$fechaActual)."',".$etapa.",".$fila[2].")";
					$x++;
					$fechaActual=strtotime("+".$fila[3]." days",$fechaActual);
				}
			}
		}
		$query[$x]="commit";
		$x++;
		
		if($ejecutar)
		{
			
			return $con->ejecutarBloque($query);
		}
		else
		{
			return $query;
		}
		
	}
	
	function obtenerSeccionesElementosFormulario($modalidad,$idProceso,$idFormulario,$idRegistro,$idActor,$participante)
	{
		global $con;
		
		$requiereTodasSeccionesDictamenFinal=false;
		$consulta="select idEstado,responsable from _".$idFormulario."_tablaDinamica where id__".$idFormulario."_tablaDinamica=".$idRegistro;
		$filaReg=$con->obtenerPrimeraFila($consulta);
		$etapaRegistro=$filaReg[0];
		$respRegistro=$filaReg[1];
		if(!$filaReg)
		{
			$etapaRegistro=0;
			$respRegistro=$_SESSION["idUsr"];
		}
		
		$consulta="SELECT idPerfil FROM 944_actoresProcesoEtapa WHERE idActorProcesoEtapa=".$idActor;
		
		$idPerfil=$con->obtenerValor($consulta);
		if($idPerfil=="")
			$idPerfil="-1";
		else
		{
			$consulta="SELECT situacion FROM 206_perfilesEscenarios WHERE idPerfil=".$idPerfil;
			$situacionPerfil=$con->obtenerValor($consulta);	
			if($situacionPerfil!=1)
				$idPerfil="-1";
		}
			
			
			
		$arrSeccionesDTD=array();
		
		$constante=1;
		
		if($idActor<0)
			$constante=-1;
			
		$act=0;
		$tipoActor=0;
		
		
		if($idActor!=0)
		{
			
			if($participante==0)
			{

				$consulta="select tipoActor,actor from 944_actoresProcesoEtapa where idActorProcesoEtapa=".$idActor*$constante;

				$filaActor=$con->obtenerPrimeraFila($consulta);
				if(!$filaActor)
				{
					$tipoActor=1;
					$act=0;	
				}
				else
				{
					$tipoActor=$filaActor[0];
					$act=$filaActor[1];
				}
				
				$consulta="select aa.idGrupoAccion,aa.complementario,aa.complementario2,aa.complementario3,aa.idAccionesProcesoEtapaVSAcciones from 944_actoresProcesoEtapa ap,
						947_actoresProcesosEtapasVSAcciones aa where 
						aa.idActorProcesoEtapa=ap.idActorProcesoEtapa and ap.numEtapa=".$etapaRegistro." and ap.idProceso=".$idProceso." 
						and ap.idActorProcesoEtapa=".$idActor." and idPerfil=".$idPerfil;

			}
			/*else
			{

				$tipoActor=1;
				$act=$idActor;
				$consulta="SELECT idProyectoVSParticipanteVSEtapa FROM 995_proyectosVSParticipantesVSEtapas
							WHERE idProyecto=".$idProceso." AND idParticipante=".$idActor." AND numEtapa=".$etapaRegistro;
				
	
				$idParticipante=$con->obtenerValor($consulta);
				$consulta="SELECT idGrupoAccion,'' AS complementario,'' as complementario2,'' as complementario3 FROM 997_accionesParticipanteVSProcesoVSEtapa WHERE idProyectoParticipante=".$idParticipante;
	
			}*/
			

			$arrSometeRevision="";
			$resAcciones=$con->obtenerFilas($consulta);

			$arrAcciones=array();
			while($filaAcciones=mysql_fetch_row($resAcciones))
			{

				switch($filaAcciones[0])
				{
					case "1":  //someteRevision
						
						$objTemp=json_decode($filaAcciones[2]);
						if(!isset($objTemp->solicitarComentarios))
						{
							$cadAux=substr($filaAcciones[2],0,strlen($filaAcciones[2])-1);
							$cadAux.=',"solicitarComentarios":"0"}';
							$filaAcciones[2]=$cadAux;
						}
						
						if(!isset($objTemp->cerrarVentana))
						{
							$cadAux=substr($filaAcciones[2],0,strlen($filaAcciones[2])-1);
							$cadAux.=',"cerrarVentana":"0"}';
							$filaAcciones[2]=$cadAux;
						}
						
						if(!isset($objTemp->funcionVisualizacion))
						{
							$cadAux=substr($filaAcciones[2],0,strlen($filaAcciones[2])-1);
							$cadAux.=',"funcionVisualizacion":""}';
							$filaAcciones[2]=$cadAux;
						}
						
						$filaAcciones[2]=setAtributoCadJson($filaAcciones[2],"etapaCambio",$filaAcciones[1]);
						$filaAcciones[2]=setAtributoCadJson($filaAcciones[2],"idAccionesProcesoEtapaVSAcciones",$filaAcciones[4]);
						
						
					break;
					case "5":
						$requiereTodasSeccionesDictamenFinal=false;
						$mostrarFormularioAsociado=1;
						$mostrarComentariosAdicionales=0;
						$arrOpt="";
						$accionFinalizar=1;
						$funcionVisualizacion="-1";
						$funcionValidacion="-1";
						$icono="";
						$consulta="select ad.idFormulario,ad.estado,f.nombreTabla from 947_actoresProcesosEtapasVSAcciones pa,
									948_actoresVSFormulariosDictamen ad,900_formularios f 
									where f.idFormulario=ad.idFormulario and  ad.idActor=pa.idActorProcesoEtapa and f.tipoFormulario=14 
									and idAccionesProcesoEtapaVSAcciones=".$filaAcciones[4]." AND ad.idActorVSFormularioDictamen=pa.complementario";
						$fDictamenFinal=$con->obtenerPrimeraFila($consulta);

						$consulta="SELECT titulo FROM 900_formularios WHERE idFormulario=".$fDictamenFinal[0];
						$tituloDictamen=$con->obtenerValor($consulta);
						if($filaAcciones[3]!="")
						{
							$oAcciones=json_decode($filaAcciones[3]);
							$tituloDictamen=$oAcciones->etiqueta;
							if(isset($oAcciones->accionFinalizar))
								$accionFinalizar=$oAcciones->accionFinalizar;
							
							if(isset($oAcciones->mostrarFormularioAsociado))
							{
								
								$mostrarFormularioAsociado=$oAcciones->mostrarFormularioAsociado;

								if($mostrarFormularioAsociado==0)
								{
									
									$query="select idElementoDictamen,ad.idFormulario,complementario2 from 948_actoresVSFormulariosDictamen ad,947_actoresProcesosEtapasVSAcciones pa,
									900_formularios f where f.idFormulario=ad.idFormulario and  ad.idActor=pa.idActorProcesoEtapa and f.tipoFormulario=14 
									and idAccionesProcesoEtapaVSAcciones=".$filaAcciones[4]." AND ad.idActorVSFormularioDictamen=pa.complementario";
									$filaA=$con->obtenerPrimeraFila($query);
									
									$idElementoDictamen=$filaA[0];
									
									
									
									$consulta="select valor,Contenido,d.idEtapa,d.icono from 902_opcionesFormulario of,911_disparadores d
												where d.idValor=of.valor and d.idGrupoElemento=of.idGrupoElemento and of.idGrupoElemento=".
												$idElementoDictamen." 
												and idIdioma=1 order by valor";
									$resOpciones=$con->obtenerFilas($consulta);
									
									$opt="";
									
									while($filaOpcion=mysql_fetch_row($resOpciones))
									{
										$opt='{"idValor":"'.$filaOpcion[0].'","etiqueta":"'.$filaOpcion[1].'","etapaCambio":"'.$filaOpcion[2].
												'","icono":"'.$filaOpcion[3].'"}';
										if($arrOpt=="")
											$arrOpt=$opt;
										else
											$arrOpt.=",".$opt;
									}
									
									$consulta="SELECT configuracionFormulario FROM 900_formularios WHERE idFormulario=".$fDictamenFinal[0];
									$configuracionFormulario=$con->obtenerValor($consulta);
									if($configuracionFormulario!="")
									{
										$oConfiguracionFormulario=json_decode($configuracionFormulario);
										if(isset($oConfiguracionFormulario->campoComentario)&&($oConfiguracionFormulario->campoComentario!=-1)&&($oConfiguracionFormulario->campoComentario!=""))
										{
											$mostrarComentariosAdicionales=1;
										}
										
										
									}
									
									
									
								}
							}
							
							if(isset($oAcciones->requiereTodasSecciones)&&($oAcciones->requiereTodasSecciones==1))
								$requiereTodasSeccionesDictamenFinal=true;
								
							if(isset($oAcciones->funcionVisualizacion))	
								$funcionVisualizacion=$oAcciones->funcionVisualizacion;
							
							if(isset($oAcciones->funcionValidacion))	
								$funcionValidacion=$oAcciones->funcionValidacion;
							
							if(isset($oAcciones->icono))	
								$icono=$oAcciones->icono;
								
						}
						$filaAcciones[2]='{"idFormularioReferencia":"'.$fDictamenFinal[0].'","titulo":"'.$tituloDictamen.
										'","accionFinalizar":"'.$accionFinalizar.'","mostrarFormularioAsociado":"'.$mostrarFormularioAsociado.
										'","arrOpciones":['.$arrOpt.'],"mostrarComentariosAdicionales":"'.$mostrarComentariosAdicionales.
										'","idAccionesProcesoEtapaVSAcciones":"'.$filaAcciones[4].'","requiereTodasSecciones":"'.
										($requiereTodasSeccionesDictamenFinal?1:0).'","funcionVisualizacion":"'.$funcionVisualizacion.
										'","funcionValidacion":"'.$funcionValidacion.'","icono":"'.cv($icono).'"}';
						
						
					break;
				}
				

				switch($filaAcciones[0])
				{
					case 1:
					case 5:	
						if(!isset($arrAcciones[$filaAcciones[0]]))
						{
							$arrAcciones[$filaAcciones[0]][0]="";
							$arrAcciones[$filaAcciones[0]][1]="".$filaAcciones[2];
							$arrAcciones[$filaAcciones[0]][2]="";
						}
						else
						{
							$arrAcciones[$filaAcciones[0]][1].=",".$filaAcciones[2];
						}
					break;
					default:
						$arrAcciones[$filaAcciones[0]][0]="".$filaAcciones[1];
						$arrAcciones[$filaAcciones[0]][1]="".$filaAcciones[2];
						$arrAcciones[$filaAcciones[0]][2]="".$filaAcciones[3];
					break;
				}
				
			}
			
		}
		else
		{
			
			$tipoActor="1";
			$act=0;
		}
		
		
		

		//Nuevo
		$someteRevision=false;
		$modificaElementos=false;
		if($idActor==-255)
		  $modificaElementos=true;
		$asignaRevisores=false;
		$realizaDictamenF=false;
		$realizaDictamenP=false;
		$marcarElementos=false;
		$asignaComites=false;
		$adjuntaDocumentos=false;
		$remueveDocumentos=false;
		

		if(isset($arrAcciones["1"][0]))
			$someteRevision=true;
		if(isset($arrAcciones["2"][0]))
			$modificaElementos=true;
		if(isset($arrAcciones["3"][0]))
			$asignaRevisores=true;
		if(isset($arrAcciones["4"][0]))
			$realizaDictamenP=true;
		if(isset($arrAcciones["5"][0]))
			$realizaDictamenF=true;
		if(isset($arrAcciones["6"][0]))
			$marcarElementos=true;
		if(isset($arrAcciones["12"][0]))
			$asignaComites=true;
			
		if(isset($arrAcciones["31"][0]))
			$adjuntaDocumentos=true;	
		
		if(isset($arrAcciones["32"][0]))
			$remueveDocumentos=true;
			
		if((!$adjuntaDocumentos)&&(!$remueveDocumentos)	)
		{
			$adjuntaDocumentos=false;
		}

		//<Generar consulta de formulario que puede visualizar>
		$idComite="-1";
		
		
		$arrAccionesParticipante=array();
		if($tipoActor==1)
		{
			
			if($participante==0)
			{
			
				if($idPerfil!=-1)
				{
					$consulta="select e.idFormulario,nombreFormulario,titulo,e.tipoElemento,nombreTabla,e.obligatorio,e.idElementoDTD,e.funcionExclusion,e.idFuncionVisualizacion,e.idFuncionEdicion,e.tituloElemento,f.frmRepetible,
								f.anchoGrid,f.altoGrid,e.complementario,cadConfiguracion from 203_elementosDTD e,900_formularios f where e.idFormulario=f.idFormulario and 
								e.idProceso=".$idProceso." and e.noEtapa<=".$etapaRegistro."  and e.idPerfil=".$idPerfil."
								order by orden";	
				}
				else
				{
					$consulta="select e.idFormulario,nombreFormulario,titulo,e.tipoElemento,nombreTabla,e.obligatorio,e.idElementoDTD,e.funcionExclusion,e.idFuncionVisualizacion,e.idFuncionEdicion,e.tituloElemento,f.frmRepetible,
								f.anchoGrid,f.altoGrid,e.complementario,cadConfiguracion from 203_elementosDTD e,900_formularios f where e.idFormulario=f.idFormulario and 
								e.idProceso=".$idProceso." and f.idEtapa<=".$etapaRegistro."  and e.idPerfil=".$idPerfil."
								order by orden";	
				}
					
				
				
			}
			
		}
		
		
		//</Generar consulta de formulario que puede visualizar>
		
		$res=$con->obtenerFilas($consulta);
		$ct=1;
		$arrConfiguraciones="var arrParamConfiguraciones=new Array();";
		$ctParamFunciones=0;
		$arrValoresReemplazo[0][0]="@formulario";
		$arrValoresReemplazo[0][1]=$idFormulario;
		$arrValoresReemplazo[1][0]="@registro";
		$arrValoresReemplazo[1][1]=$idRegistro;
		$enlace="";
		$ctTD=2;
		$oblRestantes=0;
		$tabla="";
		$estado="";
	
		//<Creando nodo raíz>
	
		$consulta="SELECT titulo FROM 900_formularios WHERE idFormulario=".$idFormulario;
		$nombreFormulario=$con->obtenerValor($consulta);
		$oSeccion["modificable"]=0;


		if(($modificaElementos)||($idRegistro==-1))
			$oSeccion["modificable"]=1;
	

		$consulta="select e.idFormulario,nombreFormulario,titulo,e.tipoElemento,nombreTabla,e.obligatorio,e.idElementoDTD,e.funcionExclusion,e.idFuncionVisualizacion,e.idFuncionEdicion,e.tituloElemento,f.frmRepetible,
					f.anchoGrid,f.altoGrid,e.complementario from 203_elementosDTD e,900_formularios f where e.idFormulario=f.idFormulario and 
					e.idProceso=".$idProceso." and e.idFormulario=".$idFormulario."  and e.idPerfil=".$idPerfil."	order by orden";

		
		$f=$con->obtenerPrimeraFila($consulta);
		
		if($f)
		{
			
			if(($f[9]!="0")&&($f[9]!="")&&($f[9]!="-1"))
			{
	
				$cacheCalculo=null;
				$cadObj='{"numEtapa":"'.$etapaRegistro.'","idFormularioEvaluacion":"'.$idFormulario.'","idFormulario":"'.$idFormulario.'","idReferencia":"'.$idRegistro.'","actor":"'.$idActor.'"}';
				$obj=json_decode($cadObj);
				$resultado=resolverExpresionCalculoPHP($f[9],$obj,$cacheCalculo);
				
				if(gettype($resultado)=='string')
					$resultado=removerComillasLimite($resultado);
				if(($resultado=="1")||($resultado===true))
				{
					$oSeccion["modificable"]=1;
				}
			}	
			
			if($f[10]!="")
			{
				$nombreFormulario=$f[10];	
			}
			
		}
		
		$consulta="select estado from 963_estadosElementoDTD where idFormulario=".$idFormulario." and idReferencia=".$idRegistro;
		$estado=$con->obtenerValor($consulta);
		if($estado=="1")
			$oSeccion["bloqueado"]=1;
		else
			$oSeccion["bloqueado"]=0;
		$oSeccion["permisoBloqueoDesbloqueo"]=$marcarElementos?1:0;
		$oSeccion["titulo"]="<span style=\"color:#F00\">*</span>".$nombreFormulario;
		$oSeccion["tipoFormulario"]=0;
		$oSeccion["nombreTabla"]="_".$idFormulario."_tablaDinamica";
		$oSeccion["numReg"]=($idRegistro==-1?0:1);
		$oSeccion["obligatorio"]=1;
		
		if(!$f)
		{
			$consulta="SELECT anchoGrid,altoGrid FROM 900_formularios WHERE idFormulario=".$idFormulario;
			$fBase=$con->obtenerPrimeraFila($consulta);
			
			$f[12]=$fBase[0];
			$f[13]=$fBase[1];
			
		}
		
		
		$cadObj='{"formularioBase":"1","soloLectura":"vSL","modalidad":"2","ancho":"'.($f[12]+80).'","alto":"'.($f[13]+110).'","titulo":"'.cv($oSeccion["titulo"]).
				'","idFormularioReferencia":"'.$idFormulario.'","idRegistroReferencia":"'.$idRegistro.'"}';

		$oSeccion["objConf"]=$cadObj;
		$oSeccion["funcion"]="abrirSeccionPagina";
		$oSeccion["seccionRepetible"]="0";
		
		array_push($arrSeccionesDTD,$oSeccion);
	
		//</Creando nodo raíz>
		
		while($f=mysql_fetch_row($res))
		{
			
			$seccionObligatoria=false;
			$idFrm=$f[0];
			
			
			if($idFrm==$idFormulario)
				break;
			
			if($f[7]=="1") //Función de exclusión
			{
				
				if(excluirFormulario($idProceso,$f[0],$idRegistro,$idActor))
					continue;
			}
			
			if(($f[8]!="0")&&($f[8]!="")&&($f[8]!="-1")) //Función de visualización
			{
				$cacheCalculo=null;
				$cadObj='{"numEtapa":"'.$etapaRegistro.'","idFormularioEvaluacion":"'.$idFrm.'","idFormulario":"'.$idFormulario.'","idReferencia":"'.$idRegistro.'","actor":"'.$idActor.'"}';
				
				$obj=json_decode($cadObj);


				$resultado=resolverExpresionCalculoPHP($f[8],$obj,$cacheCalculo);
				
				
				if(($resultado=="0")||($resultado=="'0'")||($resultado===false))
				{
					continue;
				}
			}
			
			if($f[5]=="1")
			{
				
				$seccionObligatoria=true;
				$oblRestantes++;

			}
			

			$aplicarExcepcion=false;
			
			if($participante==1)
			{
				if($arrAccionesParticipante[$f[0]]==1)
					$aplicarExcepcion=true;
			}
			
			if((!$aplicarExcepcion)&&($f[9]!="0")&&($f[9]!="")&&($f[9]!="-1")) //Función de edición
			{
	
				$cacheCalculo=null;
				$cadObj='{"numEtapa":"'.$etapaRegistro.'","idFormularioEvaluacion":"'.$idFrm.'","idFormulario":"'.$idFormulario.'","idReferencia":"'.$idRegistro.'","actor":"'.$idActor.'"}';
				$obj=json_decode($cadObj);
				
				$resultado=resolverExpresionCalculoPHP($f[9],$obj,$cacheCalculo);

				if(gettype($resultado)=='string')
					$resultado=removerComillasLimite($resultado);
				if(($resultado=="1")||($resultado===true))
				{
					$aplicarExcepcion=true;
				}
			}	
			
			$elementoModificable=(($modificaElementos)||($aplicarExcepcion));
			$oSeccion=array();
			$oSeccion["modificable"]=$elementoModificable?"1":"0";
			
			$consulta="select estado from 963_estadosElementoDTD where idFormulario=".$idFrm." and idReferencia=".$idRegistro;
			$estado=$con->obtenerValor($consulta);
			if($estado=="1")
				$oSeccion["bloqueado"]=1;
			else
				$oSeccion["bloqueado"]=0;
			
			$oSeccion["permisoBloqueoDesbloqueo"]=0;	
			
			$btnBloqueoElem="";
			if($marcarElementos)
				$oSeccion["permisoBloqueoDesbloqueo"]=1;
	
	
			$titulo=$f[1];
			if($f[10]!="")
				$titulo=$f[10];
			$oSeccion["titulo"]=($f[5]==1)?"<span style=\"color:#F00\">*</span>".$titulo:$titulo;
			$oSeccion["tipoFormulario"]=$f[3];
			$oSeccion["obligatorio"]=$f[5];
			switch($f[3])
			{
				case 0://Formulario dinamico
				
					$oSeccion["nombreTabla"]=$f[4];
					$consulta="select id_".$f[4]." from ".$f[4]." where idReferencia=".$idRegistro;
					$fila=$con->obtenerPrimeraFila($consulta);
					$nReg=$con->filasAfectadas;
					$idFormularioBase=$f[0];
					$oSeccion["numReg"]=$nReg;
					
					if($f[11]==1)
					{
						$cadObj='{"idProcesoPadre":"'.$idProceso.'","titulo":"'.cv($titulo).'","idReferencia":"'.$idRegistro.'","idFormularioReferencia":"'.$idFormularioBase.'","soloLectura":"vSL","modalidad":"'.$modalidad.'"}';
						$oSeccion["objConf"]=$cadObj;
						$oSeccion["funcion"]="abrirSeccionListado";
						$oSeccion["seccionRepetible"]=1;
					}
					else
					{
						$cadObj='{"soloLectura":"vSL","modalidad":"'.$modalidad.'","ancho":"'.($f[12]+80).'","alto":"'.($f[13]+110).'","titulo":"'.cv($titulo).'","idReferencia":"'.$idRegistro.'","idFormularioReferencia":"'.$idFormularioBase.'","idRegistroReferencia":"'.($fila?$fila[0]:'-1').'"}';
						$oSeccion["objConf"]=$cadObj;
						$oSeccion["funcion"]="abrirSeccionPagina";
						$oSeccion["seccionRepetible"]=0;
					}
				break;
				case 1:  //modulop Predefinido

					$nTablaModulo=$f[4];
					$consulta="select modulo,paginaAsociada,paginaVistaAsociada from 200_modulosPredefinidosProcesos where idGrupoModulo=".$f[2]." and idIdioma=".$_SESSION["leng"];
					$filaR=$con->obtenerPrimeraFila($consulta);		
					$arrConfiguraciones.="arrParamConfiguraciones[".$ctParamFunciones."]=new Array();";
					$arrConfiguraciones.="arrParamConfiguraciones[".$ctParamFunciones."][0]='".$filaR[1]."';";
					$arrConfiguraciones.="arrParamConfiguraciones[".$ctParamFunciones."][2]='".$filaR[2]."';";
					
					$arrObj="idFormulario:".$idFormulario.",idRegistro:".$idRegistro;
					
					
					$arrConfiguraciones.="arrParamConfiguraciones[".$ctParamFunciones."][1]={".$arrObj."};";
					$oSeccion["nombreTabla"]=$nTablaModulo;
					if($nTablaModulo!="")
					{
						$consulta="select count(*) from ".$nTablaModulo." where idFormulario=".$idFormulario." and idReferencia=".$idRegistro;

						if($con->existeCampo("idFormularioProceso",$nTablaModulo))
						{
							$consulta.=" and idFormularioProceso=".$idFrm;
						}
							
						$nReg=$con->obtenerValor($consulta);
						$oSeccion["numReg"]=$nReg;
						
						/*if(($f[8]!="0")&&($f[8]!="")&&($f[8]!="-1")&&($nReg==0))
						{
							$oSeccion["numReg"]=1;
						}*/
						
					}
					else
						$oSeccion["numReg"]=-1;
					
					
					$ancho="";
					$alto="";
					if($f[15]!="")
					{
						$oConfAux=json_decode($f[15]);
						if(isset($oConfAux->ancho))	
							$ancho=$oConfAux->ancho;
						
						if(isset($oConfAux->alto))	
							$alto=$oConfAux->alto;	
					}
					$idFormularioBase=$f[0];
					$cadObj='{"idFormularioProceso":"'.$idFrm.'","idFormularioReferencia":"'.$idFormularioBase.'","paginaRegistro":"'.$filaR[1].'","paginaVista":"'.$filaR[2].'","soloLectura":"vSL","modalidad":"'.$modalidad.'","ancho":"'.$ancho.'","alto":"'.$alto.'","titulo":"'.cv($titulo).'"}';
					$oSeccion["objConf"]=$cadObj;
					$oSeccion["funcion"]="abrirSeccionPaginaModuloPredefinido";
					$oSeccion["seccionRepetible"]=0;
					
					
				break;
				case 2:
					
					$idProcesoAux=$f[2];
					$idFormularioBase=obtenerFormularioBase($idProcesoAux);
					$nTablaAux=obtenerNombreTabla($idFormularioBase);
					$tVista=1;
					$respRegistroUsr=$respRegistro;
						
					$complementario=$f[14];
					$msgComp="";
					if($complementario!="")
					{
						$arrComp=explode(",",$complementario);
						$actorProcHijo="'".$arrComp[0]."'";
						if((isset($arrComp[4]))&&($arrComp[4]==1))
						{
							$consulta="SELECT actor FROM 950_actorVSProcesoInicio WHERE idProceso=204 AND actor IN(".$_SESSION["idRol"].")";

							$actorTmp=$con->obtenerValor($consulta);
							if($actorTmp!="")
								$actorProcHijo="'".$actorTmp."'";
							
							
						}
							
						
						if($arrComp[1]!=0)
						{
							if($arrComp[1]>0)
							{
								$consulta="select descParticipacion from 953_elementosPerfilesParticipacionAutor where idElementoPerfilAutor=".$arrComp[1];
								$participacion=$con->obtenerValor($consulta);
								
								$consulta="SELECT idUsuario FROM 246_autoresVSProyecto WHERE idFormulario=".$idFormulario." AND idReferencia=".$idRegistro." AND claveParticipacion=".$arrComp[1];
								$respRegistroUsr=$con->obtenerValor($consulta);
								if($respRegistroUsr=="")
								{
									$sL=1;
									$respRegistroUsr=0;
								}
							}
							else
							{
								$consulta="SELECT nombreCampo FROM 901_elementosFormulario WHERE idGrupoElemento=".$arrComp[3];
								$nCampo=$con->obtenerValor($consulta);	
								$query="select * from _".$idFormulario."_tablaDinamica where id__".$idFormulario."_tablaDinamica=".$idRegistro;
								$resName=$con->obtenerFilas($query);
								if($con->filasAfectadas>0)
								{
									$filaRegName=mysql_fetch_assoc($resName);
									$respRegistroUsr=$filaRegName[$nCampo];
								}
								else
									$respRegistroUsr=0;
								
							}
						}
	
						$tVista=$arrComp[2];
						$consulta="select nombreTabla,idFormulario from 900_formularios where idFormulario=".$idFormularioBase;
						$filaFrm=$con->obtenerPrimeraFila($consulta);
						$nTablaAux=$filaFrm[0];
						$oSeccion["nombreTabla"]=$nTablaAux;
						$condWhere="";
						
						if($tVista==1)
							$condWhere=" where idProcesoPadre=".$idProceso." and idReferencia=".$idRegistro;
						else
						{
							$idFrmAutores=incluyeModulo($idProcesoAux,3);
							if($idFrmAutores=="-1")
								$condWhere=" where responsable=".$respRegistroUsr;
							else
								$condWhere=" where id_".$nTablaAux." in (select distinct idReferencia from 246_autoresVSProyecto where idUsuario=".$respRegistroUsr." and idFormulario=".$idFormularioBase.")";
						}
						$consulta="select count(id_".$nTablaAux.") from ".$nTablaAux." ".$condWhere;
						
						$nReg=$con->obtenerValor($consulta);
						$oSeccion["numReg"]=$nReg;
						

						$cadObj='{"titulo":"'.cv($titulo).'","idProcesoPadre":"'.$idProceso.'","idReferencia":"'.$idRegistro.'","idProcesoReferencia":"'.$idProcesoAux.
								  '","idFormularioReferencia":"'.$idFormularioBase.'","idUsuario":"'.$respRegistroUsr.'","actor":"'.$actorProcHijo.'","soloLectura":"vSL"}';
						
						$oSeccion["objConf"]=$cadObj;
						$oSeccion["funcion"]="abrirProcesoVinculado";
						$oSeccion["seccionRepetible"]=1;
					}
					else
						continue;
					
					
					
					
				break;
			}

			if((abs($oSeccion["numReg"])>0)&&($oSeccion["obligatorio"]==1))
				$oblRestantes--;
		
		
			if(($oSeccion["numReg"]==0)&&($oSeccion["modificable"]==0))
			{
				if($oSeccion["obligatorio"]==1)
					$oblRestantes--;
				continue;
			}
			
			
			array_push($arrSeccionesDTD,$oSeccion);
			
		}

		if($oblRestantes>0)
		{
			$someteRevision=false;
			if(($realizaDictamenF)&&($requiereTodasSeccionesDictamenFinal))
				$realizaDictamenF=false;
		}
			
		$arrSeccionesBtn="";
		$nReg=1;
		foreach($arrSeccionesDTD as $s)
		{
			
			$sL=0;
			if(($s["modificable"]==0)||($s["bloqueado"]==1))
				$sL=1;

			$oBtn='{"id":"'.$nReg.'","text":"'.cv($s["titulo"]).'","leaf":true,"icon":"../images/'.(abs($s["numReg"])>0?'bullet_green.png':'bullet_red.png').
					'","cls":"fondoOpcion","rO":"'.$sL.'","oConf":"'.bE($s["objConf"]).'","fc":"'.bE($s["funcion"]).'","nReg":'.$s["numReg"].
					',"repetible":"'.$s["seccionRepetible"].'","tipoSeccion":"'.$s["tipoFormulario"].'"}';
			if($arrSeccionesBtn=="")			 
				$arrSeccionesBtn=$oBtn;
			else
				$arrSeccionesBtn.=",".$oBtn;
			$nReg++;
		}
		
		$arrOpciones="";
		$arrOpcionesDictamenFinal="";
		$colorFondoSeccion="#E7E7E7";
		
		if(isset($arrAcciones[1]))
		{
			$objSometeRevision=json_decode("[".$arrAcciones[1][1]."]");
			foreach($objSometeRevision as $o)
			{

				
				//setAtributoObjJson($o,"idAccion",)
				
				$considerar=false;
				if(($o->funcionVisualizacion!="")&&($o->funcionVisualizacion!=-1))
				{
					$cacheCalculo=null;
					$cadObj='{"numEtapa":"'.$etapaRegistro.'","idFormularioEvaluacion":"'.$idFormulario.
							'","idFormulario":"'.$idFormulario.'","idReferencia":"'.$idRegistro.'","actor":"'.$idActor.'"}';
					$obj=json_decode($cadObj);
					$resultado=removerComillasLimite(resolverExpresionCalculoPHP($o->funcionVisualizacion,$obj,$cacheCalculo));
					
					
					if(($resultado=="1")||($resultado===true))
					{
						$considerar=true;
					}
				}
				else
				{
					if($someteRevision)
					{
						$considerar=true;
					}
				}
				
				if($considerar)
				{
					if($arrOpciones=="")
						$arrOpciones=json_encode($o);
					else
						$arrOpciones.=",".json_encode($o);
				}
				
			}
		}
		
		if(isset($arrAcciones[5]))
		{
			$objDictamen=json_decode("[".$arrAcciones[5][1]."]");
			
			foreach($objDictamen as $o)
			{


				//setAtributoObjJson($o,"idAccion",)
				
				$considerar=false;
				if(isset($o->funcionVisualizacion)&&($o->funcionVisualizacion!="")&&($o->funcionVisualizacion!=-1))
				{
					$cacheCalculo=null;
					$cadObj='{"numEtapa":"'.$etapaRegistro.'","idFormularioEvaluacion":"'.$idFormulario.
							'","idFormulario":"'.$idFormulario.'","idReferencia":"'.$idRegistro.'","actor":"'.$idActor.'"}';
					$obj=json_decode($cadObj);
					$resultado=removerComillasLimite(resolverExpresionCalculoPHP($o->funcionVisualizacion,$obj,$cacheCalculo));
					
					
					if(($resultado=="1")||($resultado===true))
					{
						$considerar=true;
					}
				}
				else
				{
					if($realizaDictamenF)
					{
						$considerar=true;
					}
				}
				
				if($considerar)
				{
					if($arrOpcionesDictamenFinal=="")
						$arrOpcionesDictamenFinal=json_encode($o);
					else
						$arrOpcionesDictamenFinal.=",".json_encode($o);
				}
				
			}
		}

		if($arrOpciones!="")
		{
			$someteRevision=true;
		}
		else
		{
			$someteRevision=false;
		}
		
		if($arrOpcionesDictamenFinal!="")
		{
			$realizaDictamenF=true;
		}
		else
		{
			$realizaDictamenF=false;
		}
		
		
		$cadOpt='{"idPerfil":"'.$idPerfil.'","someteRevision":"'.($someteRevision?"1":"0").'","objSometeRevision":"'.bE($someteRevision?$arrOpciones:'').
				'","modificaElementos":"'.($modificaElementos?"1":"0").'","asignaRevisores":"'.($asignaRevisores?"1":"0").'","realizaDictamenP":"'.
				($realizaDictamenP?"1":"0").'","realizaDictamenF":"'.($realizaDictamenF?"1":"0").'","objConfDictamenFinal":"'.
				bE($realizaDictamenF?$arrOpcionesDictamenFinal:'').'","marcarElementos":"'.($marcarElementos?"1":"0").'","asignaComites":"'.
				($asignaComites?"1":"0").'","certificaProceso":"'.(isset($arrAcciones[30])?1:0).'","confCertificaProceso":"'.
				bE(isset($arrAcciones["30"][1])?$arrAcciones["30"][1]:'').'","adjuntaDocumentos":"'.($adjuntaDocumentos?1:0).
				'","remueveDocumentos":"'.($remueveDocumentos?1:0).'","objConfAdjuntaDocumentos":"'.
				bE(isset($arrAcciones["31"][1])?$arrAcciones["31"][1]:'').'"}';
		
		$arrSeccionesBtn='{"idPerfil":"'.$idPerfil.'","id":"-1","text":"<b>Secciones del proyecto</b>","leaf":false,"children":['.$arrSeccionesBtn.'],"expanded":true,"icon":"../images/s.gif","cls":"fondoSeccion","objAccionesDisponibles":'.$cadOpt.'}';
		
		
		return "[".$arrSeccionesBtn."]";
	}
	
	function generarIDActividad($idFormulario,$idRegistro=-1)
	{
		global $con;
		
		$idActividad=-1;
		$consulta="INSERT INTO 9058_registroActividadesFormulario(idFormulario,idRegistro)VALUES(".$idFormulario.",".$idRegistro.")";
		if($con->ejecutarConsulta($consulta))
		{
			$idActividad=$con->obtenerUltimoID();
		}
		return $idActividad;
		
	}
	
	function crearInstanciaRegistroFormulario($idFormulario,$idReferencia,$etapaActual,$arrValores,$arrDocumentosReferencia,$idPerfil=-1,$actor=0,$comentariosAdicionales="")
	{
		global $con;
		
		$cadenaCampo="";
		$cadenaValores="";
		
		foreach($arrValores as $campo=>$valor)
		{

			$valor=trim($valor." ");
			if(($campo!='fechaCreacion')&&($campo!='responsable')&&($campo!='codigoUnidad')&&($campo!='codigoInstitucion'))
			{
				$cadenaCampo.=",".$campo;
			
				
				
				
				if(($valor=="")&&($valor!="0"))
					$valor="NULL";
				
				
				
				
				if($valor!="NULL")
					$cadenaValores.=",'".cv($valor)."'";
				else
					$cadenaValores.=",".$valor."";
			}
		}
		
		$consulta="INSERT INTO _".$idFormulario."_tablaDinamica(idReferencia,fechaCreacion,responsable,idEstado,codigoUnidad,codigoInstitucion".$cadenaCampo.")
				VALUES(".$idReferencia.",'".(isset($arrValores["fechaCreacion"])?$arrValores["fechaCreacion"]:date("Y-m-d H:i:s")).
				"',".(isset($arrValores["responsable"])?$arrValores["responsable"]:$_SESSION["idUsr"]).",".abs($etapaActual).",'".
				(isset($arrValores["codigoUnidad"])?$arrValores["codigoUnidad"]:$_SESSION["codigoUnidad"])."','".
				(isset($arrValores["codigoInstitucion"])?$arrValores["codigoInstitucion"]:$_SESSION["codigoInstitucion"])."'".$cadenaValores.")";
		
		if($con->ejecutarConsulta($consulta))
		{
			

			$idRegistro=$con->obtenerUltimoID();
			
			asignarFolioRegistro($idFormulario,$idRegistro);
			if($etapaActual>0)
				cambiarEtapaFormulario($idFormulario,$idRegistro,$etapaActual,$comentariosAdicionales,-1,"NULL","NULL",$actor);
			

			
			if($arrDocumentosReferencia!=NULL)
			{
				foreach($arrDocumentosReferencia as $idDocumento)
				{
					
					registrarDocumentoReferenciaProceso($idFormulario,$idRegistro,$idDocumento);
					
				}
			}
			
			return $idRegistro;
			
		}
		
		
		return -1;
		
		
	}
	
	function obtenerInstanciaRegistroBusqueda($idFormulario,$arrCondiciones)
	{
		global $con;
		
		$condiciones="";
		foreach($arrCondiciones as $campo=>$valor)
		{
			$c=$campo."=".(($valor!="NULL")?"'".$valor."'":"NULL");
			if($condiciones=="")
				$condiciones=$c;
			else
				$condiciones.=" and ".$c;
		}
		
		
		$consulta="SELECT id__".$idFormulario."_tablaDinamica FROM _".$idFormulario."_tablaDinamica WHERE ".$condiciones;
		
		$idRegistro=$con->obtenerValor($consulta);
		
		if($idRegistro=="")
			$idRegistro=-1;
		
		return $idRegistro;
		
		
	}
	
	function SYS_obtenerRemitenteEnvio($idFormulario,$idRegistro,$idActorProceso)
	{
		global $con;
		
		$consulta="SELECT idEstado FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
		$idEstadoActual=$con->obtenerValor($consulta);
		$consulta="SELECT * FROM 941_bitacoraEtapasFormularios WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro.
					" and etapaActual=".$idEstadoActual." and etapaAnterior<>etapaActual ORDER BY idRegistroEstado DESC";
		$fRegistro1=$con->obtenerPrimeraFilaAsoc($consulta);
		
		$encontrado=false;
		while(!$encontrado)
		{
			$consulta="SELECT * FROM 941_bitacoraEtapasFormularios WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro.
						" and etapaActual=".$fRegistro1["etapaAnterior"]." and etapaAnterior<>etapaActual ORDER BY idRegistroEstado DESC";
			if($fRegistro1["etapaAnterior"]==1)
			{
				$consulta="SELECT * FROM 941_bitacoraEtapasFormularios WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro.
						" and etapaActual=".$fRegistro1["etapaAnterior"]."  ORDER BY idRegistroEstado DESC";
			}
			$fRegistro2=$con->obtenerPrimeraFilaAsoc($consulta);
			
			if(($fRegistro1["idUsuarioCambio"]<>$fRegistro2["idUsuarioCambio"])||($fRegistro1["idRegistroEstado"]==$fRegistro2["idRegistroEstado"]))
			{
				$encontrado=true;
			}
			else
				$fRegistro1=$fRegistro2;
		}
		
		
		
		$consulta="SELECT actor FROM 944_actoresProcesoEtapa WHERE idActorProcesoEtapa=".$fRegistro2["actorCambio"];

		$rolActor=$con->obtenerValor($consulta);
		
		$arrDestinatarios=array();	
		
		$rolActor=obtenerTituloRol($rolActor);
		$nombreUsuario=obtenerNombreUsuario($fRegistro2["idUsuarioCambio"])." (".$rolActor.")";
		
		$o='{"idUsuarioDestinatario":"'.$fRegistro2["idUsuarioCambio"].'","nombreUsuarioDestinatario":"'.$nombreUsuario.'"}';
		$oDestinatario=json_decode($o);
		
		array_push($arrDestinatarios,$oDestinatario);
		
		$nombreUsuario=obtenerNombreUsuario(1)." (".$rolActor.")";
		$o='{"idUsuarioDestinatario":"1","nombreUsuarioDestinatario":"'.$nombreUsuario.'"}';
		$oDestinatario=json_decode($o);
		
		array_push($arrDestinatarios,$oDestinatario);
		
		return $arrDestinatarios;
	}
	
	
	function SYS_obtenerRemitenteEnvioActorAnterior($idFormulario,$idRegistro)
	{
		global $con;
		
		$consulta="SELECT idEstado FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
		$idEstadoActual=$con->obtenerValor($consulta);
		$consulta="SELECT * FROM 941_bitacoraEtapasFormularios WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro.
					" and etapaActual=".$idEstadoActual." and etapaAnterior<>etapaActual ORDER BY idRegistroEstado DESC";
		$fRegistro1=$con->obtenerPrimeraFilaAsoc($consulta);
		
		$encontrado=false;
		while(!$encontrado)
		{
			$consulta="SELECT * FROM 941_bitacoraEtapasFormularios WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro.
						" and etapaActual=".$fRegistro1["etapaAnterior"]." and etapaAnterior<>etapaActual ORDER BY idRegistroEstado DESC";
			if($fRegistro1["etapaAnterior"]==1)
			{
				$consulta="SELECT * FROM 941_bitacoraEtapasFormularios WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro.
						" and etapaActual=".$fRegistro1["etapaAnterior"]."  ORDER BY idRegistroEstado DESC";
			}
			$fRegistro2=$con->obtenerPrimeraFilaAsoc($consulta);
		
			if(($fRegistro1["idUsuarioCambio"]<>$fRegistro2["idUsuarioCambio"])||($fRegistro1["idRegistroEstado"]==$fRegistro2["idRegistroEstado"]))
			{
				$encontrado=true;
			}
			else
				$fRegistro1=$fRegistro2;
		}
		
		
		
		$consulta="SELECT actor FROM 944_actoresProcesoEtapa WHERE idActorProcesoEtapa=".$fRegistro2["actorCambio"];

		$rolActor=$con->obtenerValor($consulta);
		
		$arrDestinatarios=array();	
		
		$tituloRolActor=obtenerTituloRol($rolActor);
		$nombreUsuario=obtenerNombreUsuario($fRegistro2["idUsuarioCambio"])." (".$tituloRolActor.")";
		
		$o='{"idUsuarioDestinatario":"'.$fRegistro2["idUsuarioCambio"].'","nombreUsuarioDestinatario":"'.$nombreUsuario.
			'","actorDestinatario":"'.$rolActor.'"}';
		$oDestinatario=json_decode($o);
		
		array_push($arrDestinatarios,$oDestinatario);
		
		$nombreUsuario=obtenerNombreUsuario(1)." (".$tituloRolActor.")";
		$o='{"idUsuarioDestinatario":"1","nombreUsuarioDestinatario":"'.$nombreUsuario.'","actorDestinatario":"'.$rolActor.'"}';
		$oDestinatario=json_decode($o);
		
		array_push($arrDestinatarios,$oDestinatario);
		
		return $arrDestinatarios;
	}
	
	function SYS_enviarEtapaRemitente($idFormulario,$idRegistro)
	{
		global $con;
		$consulta="SELECT idEstado FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
		$idEstadoActual=$con->obtenerValor($consulta);
		$consulta="SELECT * FROM 941_bitacoraEtapasFormularios WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro.
					" and etapaActual=".$idEstadoActual." and etapaAnterior<>etapaActual ORDER BY idRegistroEstado DESC";
		$fRegistro1=$con->obtenerPrimeraFilaAsoc($consulta);
		
		$idActorProceso=$fRegistro1["actorCambio"];
		
		$consulta="SELECT idPerfil FROM 944_actoresProcesoEtapa WHERE idActorProcesoEtapa=".$idActorProceso;
		$idPerfil=$con->obtenerValor($consulta);
		
		
		$consulta="SELECT * FROM 941_bitacoraEtapasFormularios WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro.
					" and etapaActual=".$fRegistro1["etapaAnterior"]." and etapaAnterior<>etapaActual ORDER BY idRegistroEstado DESC";
					
		$fRegistro2=$con->obtenerPrimeraFilaAsoc($consulta);
		
		return cambiarEtapaFormulario($idFormulario,$idRegistro,$fRegistro2["etapaAnterior"],$fRegistro1["comentarios"],$idPerfil,"NULL","NULL",$idActorProceso);
		
	}

	
	function SYS_obtenerDestinatarioEnvio($idFormulario,$idRegistro,$idActorProceso)
	{
		global $con;
		
		$consulta="SELECT idUsuarioDestinatario,rolUsuarioDestinatario FROM _".$idFormulario.
				"_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
		$fDatosDestinatario=$con->obtenerPrimeraFila($consulta);
		$idUsuarioDestinatario=$fDatosDestinatario[0];
		$rolDestino=$fDatosDestinatario[1];
		
		
		$arrDestinatarios=array();	
		
		$rolActor=obtenerTituloRol($rolDestino);
		$nombreUsuario=obtenerNombreUsuario($idUsuarioDestinatario)." (".$rolActor.")";
		
		$o='{"idUsuarioDestinatario":"'.$idUsuarioDestinatario.'","nombreUsuarioDestinatario":"'.cv($nombreUsuario).'"}';

		$oDestinatario=json_decode($o);
		
		array_push($arrDestinatarios,$oDestinatario);
		
		$nombreUsuario=obtenerNombreUsuario(1)." (".$rolActor.")";
		$o='{"idUsuarioDestinatario":"1","nombreUsuarioDestinatario":"'.$nombreUsuario.'"}';
		$oDestinatario=json_decode($o);
		
		array_push($arrDestinatarios,$oDestinatario);


		return $arrDestinatarios;
	}
	
	function SYS_obtenerDatosRemitenteEnvio($idFormulario,$idRegistro)
	{
		global $con;
		
		$consulta="SELECT idEstado FROM _".$idFormulario."_tablaDinamica WHERE id__".$idFormulario."_tablaDinamica=".$idRegistro;
		$idEstadoActual=$con->obtenerValor($consulta);
		$consulta="SELECT * FROM 941_bitacoraEtapasFormularios WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro.
					" and etapaActual=".$idEstadoActual." and etapaAnterior<>etapaActual ORDER BY idRegistroEstado DESC";
		$fRegistro1=$con->obtenerPrimeraFilaAsoc($consulta);
		

		
		
		$consulta="SELECT * FROM 941_bitacoraEtapasFormularios WHERE idFormulario=".$idFormulario." AND idRegistro=".$idRegistro.
					" and etapaActual=".$fRegistro1["etapaAnterior"]." and etapaAnterior<>etapaActual ORDER BY idRegistroEstado DESC";
		$fRegistro2=$con->obtenerPrimeraFilaAsoc($consulta);
		

		
		$arrRemitente=array();
		$arrRemitente["idUsuario"]=$fRegistro2["idUsuarioCambio"];
		$arrRemitente["etapaOriginal"]=$fRegistro2["etapaAnterior"];
		$arrRemitente["actorRemitente"]=$fRegistro2["actorCambio"];
		
		return $arrRemitente;
	}
	
	
	function SYS_obtenerUsuariosResponsablesRegistro($idFormulario,$idRegistro,$actorDestinatario)
	{
		global $con;
		$rolActor=obtenerTituloRol($actorDestinatario);
		$consulta="SELECT DISTINCT idUsuarioCambio,actorCambio FROM 941_bitacoraEtapasFormularios WHERE idFormulario=".$idFormulario.
					" AND idRegistro=".$idRegistro." AND etapaAnterior=1 and etapaActual<>etapaAnterior";
		
		$resDestinatarios=$con->obtenerFilas($consulta);
		
		while($fDatosDestinatario=mysql_fetch_row($resDestinatarios))
		{
			$idUsuarioDestinatario=$fDatosDestinatario[0];
			
			$arrDestinatarios=array();	
			
			
			$nombreUsuario=obtenerNombreUsuario($idUsuarioDestinatario)." (".$rolActor.")";
			
			$o='{"idUsuarioDestinatario":"'.$idUsuarioDestinatario.'","nombreUsuarioDestinatario":"'.cv($nombreUsuario).'"}';
	
			$oDestinatario=json_decode($o);
			
			array_push($arrDestinatarios,$oDestinatario);
		}
		$nombreUsuario=obtenerNombreUsuario(1)." (".$rolActor.")";
		$o='{"idUsuarioDestinatario":"1","nombreUsuarioDestinatario":"'.$nombreUsuario.'"}';
		$oDestinatario=json_decode($o);
		
		array_push($arrDestinatarios,$oDestinatario);


		return $arrDestinatarios;
		
		
		
	}
	
	function SYS_obtenerUsuariosResponsablesRegistroActorAnterior($idFormulario,$idRegistro)
	{
		global $con;
		$rolActor="";
		$consulta="SELECT DISTINCT idUsuarioCambio,actorCambio FROM 941_bitacoraEtapasFormularios WHERE idFormulario=".$idFormulario.
					" AND idRegistro=".$idRegistro." AND etapaAnterior=1 and etapaActual<>etapaAnterior";
		
		$resDestinatarios=$con->obtenerFilas($consulta);
		
		while($fDatosDestinatario=mysql_fetch_row($resDestinatarios))
		{
			$idUsuarioDestinatario=$fDatosDestinatario[0];
			$consulta="SELECT actor FROM 944_actoresProcesoEtapa WHERE idActorProcesoEtapa=".$fDatosDestinatario[1];

			$rolActor=$con->obtenerValor($consulta);
			$tituloRolActor=obtenerTituloRol($rolActor);
			$arrDestinatarios=array();	
					
			$nombreUsuario=obtenerNombreUsuario($idUsuarioDestinatario)." (".$tituloRolActor.")";
			
			$o='{"idUsuarioDestinatario":"'.$idUsuarioDestinatario.'","nombreUsuarioDestinatario":"'.cv($nombreUsuario).
				'","actorDestinatario":"'.$rolActor.'"}';
	
			$oDestinatario=json_decode($o);
			
			array_push($arrDestinatarios,$oDestinatario);
		}
		$nombreUsuario=obtenerNombreUsuario(1)." (".$rolActor.")";
		$o='{"idUsuarioDestinatario":"1","nombreUsuarioDestinatario":"'.$nombreUsuario.'"}';
		$oDestinatario=json_decode($o);
		
		array_push($arrDestinatarios,$oDestinatario);


		return $arrDestinatarios;
		
		
		
	}
	
	
	function obtenerIDFormularioCategoria($idCategoria)
	{
		global $con;	
		
		$consulta="SELECT idFormulario FROM 900_formularios WHERE categoriaFormulario='".$idCategoria."'";
		$idFormulario=$con->obtenerValor($consulta);
		return $idFormulario;
	}
?>