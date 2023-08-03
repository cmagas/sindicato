<?php
function generarTableroControlCredito($tipoProceso,$titulo,$titulo2,$sl=false,$idProceso="",$idUsr="",$idEntidad,$tipoEntidad)  //OK
{
	global $con;
	global $nomPagina;
	$idUsuario="";
	$roles="";
	if($idUsr=="")
	{
		$idUsuario=$_SESSION["idUsr"];
		$roles=$_SESSION["idRol"];
	}
	else
	{
		$idUsuario=$idUsr;
		$consulta="select codigoRol from 807_usuariosVSRoles where idUsuario=".$idUsuario;
		$roles=$con->obtenerListaValores($consulta,"'");
		
	}
	?>
    <table width="850" cellpadding="4">
    <tr>
        <td width="60">
            <table width="100%">
            
                    <tr height="23">
                    <?php
                    
                    /*if(!$sl)
                    {
                        $consulta="select idComite from 234_proyectosVSComitesVSEtapas pc,4001_procesos p where pc.idProyecto=p.idProceso
                        and p.idTipoProceso=".$tipoProceso;
                        $lComite=$con->obtenerListaValores($consulta);
                        if($lComite!='')
                        {
                            $consulta="select distinct(idComite) from 2007_rolesVSComites rc where rc.rol in(".$roles.") and idComite in (".$lComite.")";
                            $resC=$con->obtenerFilas($consulta);
                            
                            if($con->filasAfectadas>0)
                            {
                            ?>
                                    <td align="right">
                                    <img src="../images/fechaGridBullet.gif" />
                                    <a href="../procesoCreditos/<?php echo $nomPagina?>Comite.php">
                                    <span class="letraRojaSubrayada8">
                                        <?php echo $titulo2 ?> en evaluación por comités
                                    </span>&nbsp;
                                    </a>
                                     </td>
                                
                            <?php
                            }
                        }
                        ?>
                         
                            <td width="280" class="" align="right">
                                <img src="../images/fechaGridBullet.gif" />
                                <a href="../procesoCreditos/<?php echo $nomPagina?>Usuario.php">
                                <span class="letraRojaSubrayada8">
                                    <?php echo $titulo2 ?> en evaluación por roles
                                 </span>&nbsp;
                                 </a>
                           </td>
                         <?php
                         
                        $consulta="select idUsuarioRevisor from 955_revisoresProceso where idUsuarioRevisor=".$idUsuario." and estado in (1,2)"; 
                        $con->obtenerFilas($consulta);
                        if(existeRol("'10_0'")&&($con->filasAfectadas>0))
                        {
                        ?>
                            <td width="280" class="" align="right">
                                <img src="../images/fechaGridBullet.gif" />
                                <a href="../procesoCreditos/<?php echo $nomPagina?>Revisor.php">
                                <span class="letraRojaSubrayada8">
                                    <?php echo $titulo2 ?> en evaluación por revisor
                                 </span>&nbsp;
                                 </a>
                           </td>
                        <?php
                        }
                    }*/
                    ?>
                    </tr>	                                                      
           </table>
        </td>
    </tr>
</table>
    <br /><br />
	<table width="850" class="" cellpadding="4">

	<tr height="23">
		<td align="left" class='celdaImagenAzul1'>
		<span class="speaker"><?php echo $titulo ?></span>
		</td>
	</tr>
	<tr>
		<td>
		</td>
	</tr>
	<?php
		if($idProceso=="")
			$consulta="select distinct(ap.idProceso),nombre,p.idTipoProceso from 950_actorVSProcesoInicio ap,4001_procesos p where p.idProceso=ap.idProceso and p.idTipoProceso=".$tipoProceso." and ap.actor in(".$roles.")";
		else
			$consulta="select distinct(ap.idProceso),nombre,p.idTipoProceso from 950_actorVSProcesoInicio ap,4001_procesos p where p.idProceso=ap.idProceso and p.idTipoProceso=".$tipoProceso." and ap.idProceso=".$idProceso." and ap.actor in(".$roles.")";
		$arrPagNuevo="var arrPagNuevo=new Array();var arrAux;";
		$resP=$con->obtenerFilas($consulta);	
		if($con->filasAfectadas>0)
		{
			while($filaP=mysql_fetch_row($resP))
			{
				$idProceso=$filaP[0];
				$tipoProceso=$filaP[2];
				$consulta="select nombreTabla,idFormulario from 900_formularios where idProceso=".$idProceso." and formularioBase=1";
				$filaFrm=$con->obtenerPrimeraFila($consulta);
				$nTabla=$filaFrm[0];
				$idFormulario=$filaFrm[1];
				$pagNuevo="../modeloPerfiles/registroFormulario.php";
				$query="select pagVista,accion from 936_vistasProcesos where tipoProceso=".$tipoProceso." and accion=0";
				$resPagAccion=$con->obtenerFilas($query);
				while($filaAccion=mysql_fetch_row($resPagAccion))
				{
					switch($filaAccion[1])
					{
						case "0"://Nuevo
							$pagNuevo=$filaAccion[0];
						break;
					}
				}
				$arrPagNuevo.="arrAux=new Array();arrAux[0]='".$idFormulario."';arrAux[1]='".$pagNuevo."';arrPagNuevo.push(arrAux);";
				$consulta="select complementario from 949_actoresVSAccionesProceso where actor in(".$roles.") and idAccion=9 and idProceso=".$idProceso." order by complementario";
				$complementario=$con->obtenerValor($consulta);
				$consulta="select actor from 949_actoresVSAccionesProceso where actor in(".$roles.") and idAccion=8 and idProceso=".$idProceso." order by complementario";
				$pAgregar=$con->obtenerValor($consulta);
				$condWhere=" where responsable=".$idUsuario;
				switch($complementario)
				{
					case "1":
						$condWhere=" where 1=1";
					break;
					case "2":
						$condWhere=" where codigoInstitucion='".$_SESSION["codigoInstitucion"]."'";
					break;
					case "3":
						$condWhere=" where codigoUnidad like '".$_SESSION["codigoUnidad"]."%'";
					break;
					case "4":
						$condWhere=" where codigoUnidad='".$_SESSION["codigoUnidad"]."'";
					break;
					case "5":
						$condWhere=" where responsable=".$idUsuario;
					break;
				}
				$condWhere.=" and id_".$nTabla." in (select idReferencia from 710_entidadesCreditos where idEntidad=".$idEntidad." and tipoEntidad=".$tipoEntidad.")";				

				$consulta="select id_".$nTabla.",idEstado from ".$nTabla." ".$condWhere;
//				echo $consulta;
				$resReg=$con->obtenerFilas($consulta);
				$arrEtapas=array();
				while($filaReg=mysql_fetch_row($resReg))
				{
					if(isset($arrEtapas["".$filaReg[1].""]))
						$arrEtapas["".$filaReg[1].""]++;
					else
						$arrEtapas["".$filaReg[1].""]=1;
				}
				
				$totalRegistros=$con->filasAfectadas;
				$consulta="select idAccion from 949_actoresVSAccionesProceso where idProceso=".$idProceso." and idAccion in(8,9) and actor in(".$roles.")";
				$resPermisos=$con->obtenerFilas($consulta);
				$agregarRegistros=false;
				$verRegistros=false;
				while($fPermisos=mysql_fetch_row($resPermisos))
				{
					if($fPermisos[0]=="8")//Agregar
						$agregarRegistros=true;
	
					if($fPermisos[0]=="9")//Agregar
						$verRegistros=true;
				}
				if($sl)
				{
					$agregarRegistros=false;
					$verRegistros=true;
				}
		?>
                <tr>
                <td>
					<table >
						<tr height="23">
						<td width="20" class="celdaImagenAzul2" align="right">
						<a href="javascript:mostrarOcultarEtapas(<?php echo $filaP[0]?>)">
						<img src="../images/verMenos.png" alt="Ocultar registros por etapas" title="Ocultar registros por etapas" id='imgEtapas_<?php echo $filaP[0] ?>'/>
						</a>
						</td>
						<td class="celdaImagenAzul2" width="760" align="left" colspan="2">
							&nbsp;&nbsp;<span class="corpo8_bold"><?php echo $filaP[1]?></span>&nbsp;<span class="letraAzulSubrayada7">(<?php echo $totalRegistros?> Registros)</span>
							<?php
								if($agregarRegistros)
								{
							?>
									<span class="copyrigth">Si desea registrar un nuevo crédito OJO de  click </span><a href="javascript:registrarNuevo(<?php echo $idFormulario?>)"><span class="letraRoja">AQUÍ</span></a>
							<?php
								}
							?>
						</td>
						
						</tr>
						<tr>
						<td style="padding:7px" colspan="3">
							<table id="tbl_<?php echo $idProceso?>" style="display:">
							<?php
							$consulta="select numEtapa,nombreEtapa from 4037_etapas where idProceso=".$idProceso." order by numEtapa";	
							$resEt=$con->obtenerFilas($consulta);
							$clase="celdaImagenAzul3";
							while($filasEt=mysql_fetch_row($resEt))
							{
								$tagA="";
								$tagC="";
								if(isset($arrEtapas[$filasEt[0]])&&$verRegistros)
								{
									$tagA="<a href=\"javascript:enviarVista('".base64_encode("idProceso=".$idProceso."&numEtapa=".$filasEt[0]."&sl=".$sl."&idUsuario=".$idUsuario."&idEntidad=".$idEntidad."&tEntidad=".$tipoEntidad)."')\">";
									$tagC="</a>";
								}
									
							?>
								<tr height="21">
									<td class="<?php echo $clase?>" width="50" align="right"><img src="../images/bullet_red.png" /></td>
									<td class="<?php echo $clase?>" width="570" align="left">&nbsp;&nbsp;<?php echo $tagA?><span class="tituloEtiqueta"><?php echo $filasEt[0]?>.- <?php echo $filasEt[1]?></span><?php echo $tagC?></td>
									<td class="<?php echo $clase?>" width="80" align="center"><?php echo $tagA?><span class="corpo8_bold">
									<?php 
										if(isset($arrEtapas[$filasEt[0]]))
											echo $arrEtapas[$filasEt[0]];
										else
											echo "0";
										
									?>
								   </span><?php echo $tagC?>
                                   
                                   </td>
								</tr>
							<?php
							
								if($clase=="celdaImagenAzul3")
									$clase="celdaBlancaSinImg";
								else
									$clase="celdaImagenAzul3";
							}
							?>
							</table>
				
				</td>
				</tr>
			</table>
		</td>
		</tr>
		<tr>
			<td><br /></td>
		</tr>
		<?php
			}
		}
		else
		{
	?>
    		<tr>
            <td>
                <table width="100%">
                <tr>
                    <td align="center">
                        <span class="letraFicha">Usted no cuenta con privilegios para ver algún registro</span><br /><br /><br /><br />
                    </td>
                </tr>
                </table>
            </td>
            </tr>
    <?php
		}
	?>
	
</table>
<input type="hidden" name="idFormulario" id="idFormulario" value="<?php echo $idFormulario?>" />
<script>
	<?php 
		echo $arrPagNuevo;
	?>
</script>
<?php
}

function generarTablaElementosCredito($idProceso,$numEtapa,$titulo="Registros",$idUsuario,$sl,$idEntidad,$tipoEntidad)				//OK+
{
	global $con;
	$consulta="select p.idTipoProceso from 4001_procesos p where p.idProceso=".$idProceso;
	$tipoProceso=$con->obtenerValor($consulta);
	$consulta="select nombreEtapa from 4037_etapas where idProceso=".$idProceso." and numEtapa= ".$numEtapa;
	$nEtapa=$con->obtenerValor($consulta);
	$consulta="select codigoRol from 807_usuariosVSRoles where idUsuario=".$idUsuario;
	$roles=$con->obtenerListaValores($consulta,"'");
	?>
	
		<table width="750" class="" style="padding:7px">
			<tr height="23">
				<td align="center" style="background-image:url(../images/fondo_barra_mc_azul.gif)">
				<span class="speaker"><?php echo $titulo ?> EN ETAPA:</span> <span class="letraRojaSubrayada8"><?php echo $numEtapa.".- ".$nEtapa ?></span>
				</td>
			</tr>
			<tr>
				<td><br /><br />
				</td>
			</tr>
					<?php
					$consulta="select nombreTabla,idFormulario from 900_formularios where idProceso=".$idProceso." and formularioBase=1";
					$filaFrm=$con->obtenerPrimeraFila($consulta);
					$nTabla=$filaFrm[0];
					$idFormulario=$filaFrm[1];
					$pagNuevo="../modeloPerfiles/registroFormulario.php";
					$pagModificar="../modeloPerfiles/registroFormulario.php";
					$pagVer="../modeloPerfiles/verFichaFormulario.php";
					$query="select pagVista,accion from 936_vistasProcesos where tipoProceso=".$tipoProceso." and accion=0";
					$resPagAccion=$con->obtenerFilas($query);
					while($filaAccion=mysql_fetch_row($resPagAccion))
					{
						switch($filaAccion[1])
						{
							case "0"://Nuevo
								$pagNuevo=$filaAccion[0];
							break;
							case "1"://Modificar
								$pagModificar=$filaAccion[0];
							break;
							case "2"://Consultar/Ver
								$pagVer=$filaAccion[0];
							break;
						}
					}
					
					
					$elimina=false;
					
					$consulta="select idGrupoAccion from 944_actoresProcesoEtapa a,947_actoresProcesosEtapasVSAcciones ae where ae.idActorProcesoEtapa=a.idActorProcesoEtapa and a.tipoActor=1 and  a.actor in (".$roles.") and a.numEtapa=".$numEtapa." and a.idProceso=".$idProceso;
					
					$resAcciones=$con->obtenerFilas($consulta);
					while($filaAcciones=mysql_fetch_row($resAcciones))
					{
						switch($filaAcciones[0])
						{
							
							case "7":
								$elimina=true;
							break;
						}
					}
					
					$btnEliminar="";										
					
						
					$consulta="select complementario from 949_actoresVSAccionesProceso where actor in(".$roles.") and idAccion=9 and idProceso=".$idProceso." order by complementario";
					$complementario=$con->obtenerValor($consulta);
					$condWhere=" idEstado=".$numEtapa;
					$idFrmAutores="-1";
					switch($complementario)
					{
						case "1":
							$condWhere="  1=1";
						break;
						case "2":
							$condWhere="  codigoInstitucion='".$_SESSION["codigoInstitucion"]."'";
						break;
						case "3":
							$condWhere="  codigoUnidad like '".$_SESSION["codigoUnidad"]."%'";
						break;
						case "4":
							$condWhere="  codigoUnidad='".$_SESSION["codigoUnidad"]."'";
						break;
						case "5":
							$condWhere="  responsable=".$idUsuario;
						break;
					}
				   $condWhere.=" and id_".$nTabla." in (select idReferencia from 710_entidadesCreditos where idEntidad=".$idEntidad." and tipoEntidad=".$tipoEntidad.")";				
				   $consulta=obtenerRegistros($idFormulario,$condWhere);
				   $resReg=$con->obtenerFilas($consulta);
				  $consulta="select f.idConfGrid,campoAgrupacion,complementario from 909_configuracionTablaFormularios f where f.idFormulario=".$idFormulario;
				  $filaConfFrm=$con->obtenerPrimeraFila($consulta);
				  $idConfiguracion=$filaConfFrm[0];
				  $campoAgrupacion=$filaConfFrm[1];
				  $datosComp=$filaConfFrm[2];
				  if($idConfiguracion=="")
					  $idConfiguracion="-1";
				  
				  $consulta="		select cg.titulo,if((select distinct(tipoElemento) from 901_elementosFormulario where 
								  idGrupoElemento=cg.idElementoFormulario) is null,cg.idElementoFormulario,(select distinct(tipoElemento) 
								  from 901_elementosFormulario where idGrupoElemento=cg.idElementoFormulario)) as tipoElemento,
								  if((select distinct(nombreCampo) from 901_elementosFormulario where 
								  idGrupoElemento=cg.idElementoFormulario) is null,
								   case cg.idElementoFormulario
									   when '-10' then 'fechaCreacion'
									   when '-11' then 'responsableCreacion'
									   when '-12' then 'fechaModificacion'
									   when '-13' then 'responsableModificacion'
									   when '-14' then 'unidadUsuarioRegistro'
									   when '-15' then 'institucionUsuarioRegistro'
									   when '-16' then 'dtefechaSolicitud'
									   when '-17' then 'tmeHoraInicio'
									   when '-18' then 'tmeHoraFin'
									   when '-19' then 'dteFechaAsignada'
									   when '-20' then 'tmeHoraInicialAsignada'
									   when '-21' then 'tmeHoraFinalAsignada'
									   when '-22' then 'unidadReservada'
									   when '-23' then 'tmeHoraSalida'
									   when '-24' then 'idEstado'
									   end
									 ,(select distinct(nombreCampo) 
								  from 901_elementosFormulario where idGrupoElemento=cg.idElementoFormulario)) as nombreCampo,alineacionValores
								  from 907_camposGrid cg where 
								  cg.idIdioma=".$_SESSION["leng"]." and cg.idConfGrid=".$idConfiguracion." order by cg.idGrupoCampo";
			
				  $resCampos=$con->obtenerFilas($consulta);
				  $arrConfCampo=array();
				  $pos=0;
				  while($fcampos=mysql_fetch_row($resCampos))
				  {
					  switch($fcampos[3])
					  {
						  case 1:
						  	$alineacion='left';
						  break;
						  case 2:
						  	$alineacion='right';
						  break;
						  case 3:
						  	$alineacion='center';
						  break;
						  case 4:
						  	$alineacion='justify';
						  break;
					  }
					 
					  $arrConfCampo[$pos]["titulo"]=$fcampos[0];
					  $arrConfCampo[$pos]["tipoElemento"]=$fcampos[1];
					  $arrConfCampo[$pos]["nombreCampo"]=$fcampos[2];
					  $arrConfCampo[$pos]["alineacion"]=$alineacion;
					  $pos++;
				  }
				  
				 
				  $nFilasConf=sizeof($arrConfCampo);
					
					$consulta="select f.titulo,e.complementario from 203_elementosDTD e,900_formularios f where f.idFormulario=e.idFormulario and e.idProceso=".$idProceso." and e.tipoElemento=1 and titulo in(3,4,6,7)";
					$resEl=$con->obtenerFilas($consulta);
					
					$anexos=false;
					$investigadores=false;
					$investigadoresComp="";
					$programaTrabajo=false;
					$ademdum=false;
					while($filaEl=mysql_fetch_row($resEl))
					{
						switch($filaEl[0])
						{
							case "3":
								
								$investigadoresComp=explode(",",$filaEl[1]);
								if(strpos($datosComp,"A")!==false)
									$investigadores=true;
							break;
							case "4":
								if(strpos($datosComp,"D")!==false)
									$anexos=true;
							break;
							case "6":
								if(strpos($datosComp,"P")!==false)
									$programaTrabajo=true;
							break;
							case "7":
								if(strpos($datosComp,"M")!==false)
									$ademdum=true;
								
							break;
							
						}
					}
					$idInvPrincipal="0";
					while($filaReg=mysql_fetch_assoc($resReg))
					{
						if(($elimina))
						{
							$btnEliminar="&nbsp;&nbsp;<a href='javascript:eliminarRegistro(\"".base64_encode($filaReg["idRegistro"])."\",".$filaReg["idRegistro"].")'><img src=\"../images/delete.png\" alt='Eliminar registro' title=\"Eliminar registro\" /></a>";
						}
						
						$invPrincipal="";
						$tituloInvestigador="Participante";
						$idInvs="";
						$respSeguimiento="Responsable seguimiento";
						if($investigadores)
						{
							if(isset($investigadoresComp[4]))
								$tituloInvestigador=$investigadoresComp[4];
  							if(isset($investigadoresComp[5]))
								$respSeguimiento=$investigadoresComp[5];
							$idPerfilPart=$investigadoresComp[0];
							
							$consulta="select concat(if(Prefijo is null,'',Prefijo),' ',if(Nom is null,'',Nom),' ',if(Paterno is null,'',Paterno),' ',
										if(Materno is null,'',Materno)) as registrado,i.idUsuario,ap.responsable,claveParticipacion from 802_identifica i, 246_autoresVSProyecto ap 
										where i.idUsuario=ap.idUsuario and ap.idReferencia=".$filaReg["idRegistro"]." and ap.idFormulario=".$idFormulario." order by registrado";
							$resInv=$con->obtenerFilas($consulta);
							$invPrincipal="<table>";
							while($filaInv=mysql_fetch_row($resInv))
							{
								$cveParticipacion=$filaInv[3];
								$consulta="select descParticipacion from 953_elementosPerfilesParticipacionAutor where idPerfilAutor=".$idPerfilPart." and clave='".$cveParticipacion."'";
								$participacion=$con->obtenerValor($consulta);
								
								if($filaInv[2]=="1")
								{
									$invPrincipal.="<tr><td><a href='javascript:verUsr(\"".base64_encode($filaInv[1])."\")'>".$filaInv[0]."</a> <font color='red'><span title='".$respSeguimiento."' alt='".$respSeguimiento."' style='cursor:help'>*</font></td><td>&nbsp;&nbsp;[".$participacion."]</td></tr>";
									$idInvPrincipal=$filaInv[1];
								}
								else
									$invPrincipal.="<tr><td><a href='javascript:verUsr(\"".base64_encode($filaInv[1])."\")'>".$filaInv[0]."</a></td><td>&nbsp;&nbsp;[".$participacion."]</td></tr>";
								
								
							}
							$invPrincipal.="</table>";
						}
						if($invPrincipal=="<table></table>")
						{
							$invPrincipal="No especificado";
						}
						
						
						
					?>
					 <tr id="fila_<?php echo $filaReg["idRegistro"]?>">
						<td align="center">
					
							<table>
									<tr>
										<td width="210" class="esquinaFicha" align="left" ><span class="corpo8_bold"><?php echo $arrConfCampo[0]["titulo"]?></span></td><td width="440" class="valorFicha" align="<?php echo $arrConfCampo[0]["alineacion"]?>"><span class="speaker"><?php echo formatearValorFicha($filaReg[$arrConfCampo[0]["nombreCampo"]],$arrConfCampo[0]["tipoElemento"])?></span></td>
										<td class="valorFicha" >
										<?php
											if(!$sl)
											{
												$consulta="select distinct(actor),idActorProcesoEtapa from 944_actoresProcesoEtapa where idProceso=".$idProceso." and tipoActor=1 and actor in (".$roles.") and numEtapa=".$numEtapa;
												$resVAct=$con->obtenerFilas($consulta);
												while($filaVAct=mysql_fetch_row($resVAct))
												{
													$nRol=obtenerTituloRol($filaVAct[0]);
													if(!$investigadores)
														$idActorE=$filaVAct[1];
													else
													{
														if($idUsuario==$idInvPrincipal)
															$idActorE=$filaVAct[1];
														else
															$idActorE=0;
													}
													
											?>
												<a href="javascript:verRegistro('<?php echo base64_encode($filaReg["idRegistro"])?>',<?php echo $filaReg["idRegistro"] ?>,'<?php echo base64_encode($idActorE)?>')">
												<img src="../images/magnifier.png" alt='Ver registro' title="Ver registro, Actor: <?php echo $nRol?>" />
												</a>
												<?php
												}
												echo $btnEliminar;
											}
											?>
										</td>
									</tr>
									<?php
										for($x=1;$x<$nFilasConf;$x++)
										{
											if($arrConfCampo[$x]["nombreCampo"]=='responsableCreacion')
											{
												$consulta="select responsable from ".$nTabla." where id_".$nTabla."=".$filaReg["idRegistro"];
												$idResponsable=$con->obtenerValor($consulta);
												
									?>
                                                <tr>
                                                    <td align="left" class="etiquetaFicha"><span class="corpo8_bold"><?php  echo $arrConfCampo[$x]["titulo"] ?></span></td>
                                                    <td class="valorFicha" colspan="2" align="<?php echo $arrConfCampo[$x]["alineacion"]?>" ><span class="corpo8"><a href="javascript:verUsr('<?php echo base64_encode($idResponsable) ?>')"><?php echo formatearValorFicha($filaReg[$arrConfCampo[$x]["nombreCampo"]],$arrConfCampo[$x]["tipoElemento"])?></a></span></td>
                                                </tr>
									<?php
											}
											else
											{
									?>
                                                <tr>
                                                    <td align="left" class="etiquetaFicha"><span class="corpo8_bold"><?php  echo $arrConfCampo[$x]["titulo"] ?></span></td>
                                                    <td class="valorFicha" colspan="2" align="<?php echo $arrConfCampo[$x]["alineacion"]?>" ><span class="corpo8"><?php echo formatearValorFicha($filaReg[$arrConfCampo[$x]["nombreCampo"]],$arrConfCampo[$x]["tipoElemento"])?></span></td>
                                                </tr>
									<?php
											}
										}
									
									?>
									<?php
										if($investigadores)
										{
									?>
									 <tr>
										<td class="etiquetaFicha" align="left" valign="top"><span class="corpo8_bold"><?php echo $tituloInvestigador?> participantes:</span></td><td class="valorFicha" colspan="4" align="left"><span class="corpo8"><?php echo $invPrincipal?></span></td>
									</tr>
									<?php
										}
									?>
									<?php
										if($programaTrabajo)
										{
									?>
									 <tr>
										<td class="etiquetaFicha" align="left"><span class="corpo8_bold">Programa de trabajo:</span></td><td class="valorFicha" colspan="4" align="left"><span class="corpo8"><a href="javascript:verGantt('<?php echo base64_encode($filaReg["idRegistro"])?>')"><img src="../images/gantt.png" title="Ver programa de trabajo" alt="Ver programa de trabajo" /></a></span></td>
									</tr>
									<?php
										}
									?>
									<?php
										if($anexos)
										{
											?>
											<tr>
												<td class="etiquetaFicha" colspan="" align="left"><span class="corpo8_bold">Anexos:</span></td>
												<td class="valorFicha" colspan="4" align="left">
												<table>
												<?php
												
												$consulta="select id_210_archivosProyectos,titulo from 210_archivosProyectos where idFormulario=".$idFormulario." and idReferencia=".$filaReg["idRegistro"]." and tipoDocumento=1 order by titulo desc";
												$resArch=$con->obtenerFilas($consulta);
												while($filaArch=mysql_fetch_row($resArch))
												{
												?>
													<tr>
													<td>
													<a href="../media/obtenerDocumento.php?doc=<?php echo base64_encode ($filaArch[0]) ?>">
													<img src='../images/icon_document.gif' />&nbsp;<span class="copyrigth"><?php echo $filaArch[1]?></</span>
													</a><br />
													</td>
													</tr>
												<?php
												}
											?>
												</table>
												</td>
											</tr>
											<?php
										
										}
										?>
                                        <?php
										if($ademdum)
										{
											?>
											<tr>
												<td class="etiquetaFicha" colspan="" align="left" valign="top"><span class="corpo8_bold">Ademdum de:</span></td>
												<td class="valorFicha" colspan="4" align="left">
												<table>
												<?php
												$consulta="select id_981_ademdums,idFormularioAd,idReferenciaAd from 981_ademdums a where a.idFormulario=".$idFormulario." and a.idReferencia=".$filaReg["idRegistro"];
												$resArch=$con->obtenerFilas($consulta);
												while($fila=mysql_fetch_row($resArch))
												{
													$consulta="select f.nombreTabla,p.nombre from 900_formularios f,4001_procesos p where p.idProceso=f.idProceso and f.idFormulario=".$fila[1];
													$filaProc=$con->obtenerPrimeraFila($consulta);
													$nTabla=$filaProc[0];
													$tipo=$filaProc[1];
													$consulta="select * from ".$nTabla." where id_".$nTabla."=".$fila[2];
													$filaProy=$con->obtenerPrimeraFila($consulta);
													$nProy=$filaProy[9];
												?>
                                                            	<tr>
                                                                	
                                                                	<td align="left" ><span class="corpo8"><?php echo $nProy?></span></td>
                                                                    <td align="left" ><span class="corpo8">&nbsp;&nbsp;(<?php echo $tipo?>)</span></td>
                                                                </tr>
                                            <?php
												}
											?>
												</table>
												</td>
											</tr>
											<?php
										
										}
										?>
							</table>
						</td>
					 </tr>
					 <tr id="filaComp_<?php echo $filaReg["idRegistro"]?>">
						<td>
						<br /><br />
						<input type="hidden" value="<?php echo $idFormulario?>" id="idFormulario" />
						</td>
					 </tr>
					
					<?php	
					}
					
					?>
		</table>
		<?php        
}

?>