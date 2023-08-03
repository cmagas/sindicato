<?php 

function generarTableroProceso9($idProceso,$tProceso,$idUsuario,$actor=-1)
{
	global $con;
	$roles="";
	if($actor==-1)
	{
		$consulta="select codigoRol from 807_usuariosVSRoles where idUsuario=".$idUsuario. " and codigoRol not like '%_-1'";
		$roles=$con->obtenerListaValores($consulta,"'");
	}
	else
		$roles=$actor;
	$consulta="SELECT DISTINCT rolActor FROM 9116_responsablesPAT WHERE idResponsable=".$idUsuario." AND idProceso=".$idProceso;
	$roles2=$con->obtenerListaValores($consulta,"'");
	if($roles2!="")
	{
		if($roles=="")
			$roles=str_replace("|","_",$roles2);
		else	
			$roles.=",".str_replace("|","_",$roles2);
	}
	$arrRolesUsr=explode(",",str_replace("'","",$roles));
	$consulta="select rp.rol,ap.numEtapa,idActorProcesoEtapa from 943_rolesActoresProceso rp,944_actoresProcesoEtapa ap where ap.actor=rp.rol and ap.idProceso=rp.idProceso and ap.tipoActor=1  and ap.numEtapa=1 and  rp.idProceso=".$idProceso." order by numEtapa";
	$arrRolesDispIni=array();
	$rolesProcIni=$con->obtenerFilasArregloPHP($consulta);
	$arrRolesIni="";
	$arrRolesAsigna=array();
	$arrRolesVarios="";
	foreach($rolesProcIni as $rolP)
	{
		$descRol=explode("_",$rolP[0]);
		if($descRol[1]!="-1")
		{
			if(existeValor($arrRolesUsr,$rolP[0]))
			{
				$consulta="SELECT idActorVSAccionesProceso FROM 949_actoresVSAccionesProceso WHERE actor='".$rolP[0]."' AND idAccion=15 AND idProceso=".$idProceso;
				$idAccion=$con->obtenerValor($consulta);
				if($idAccion!="")
				{
					$obj[0]=$rolP[0];
					$obj[1]=obtenerTituloRol($rolP[0]);
					$obj[2]=15;
					$obj[3]=$idAccion;
					array_push($arrRolesAsigna,$obj);
				}
				
				$consulta="SELECT idAccion FROM 949_actoresVSAccionesProceso WHERE actor='".$rolP[0]."' AND idAccion<>15 AND idProceso=".$idProceso;
				$con->obtenerFilas($consulta);
				if($con->filasAfectadas>0)
				{
					$obj[0]=$rolP[0];
					$obj[1]=obtenerTituloRol($rolP[0]);
					array_push($arrRolesDispIni,$obj);
					if($arrRolesIni=="")
						$arrRolesIni="'".$rolP[0]."'";
					else
						$arrRolesIni.=",'".$rolP[0]."'";
				}
			}
		}
		else
		{
			foreach($arrRolesUsr as $rolU)
			{
				if(strpos($rolU,$descRol[0]."_")!==false)
				{
					$consulta="SELECT idAccion FROM 949_actoresVSAccionesProceso WHERE actor='".$rolP[0]."' AND idAccion=15 AND idProceso=".$idProceso;
					
					$con->obtenerFilas($consulta);
					if($con->filasAfectadas>0)
					{
						$obj[0]=$rolP[0];
						$obj[1]=obtenerTituloRol($rolU);
						$obj[2]=15;
						array_push($arrRolesAsigna,$obj);
						
					}
					$consulta="SELECT idAccion FROM 949_actoresVSAccionesProceso WHERE actor='".$rolP[0]."' AND idAccion<>15 AND idProceso=".$idProceso;
					$con->obtenerFilas($consulta);
					if($con->filasAfectadas>0)
					{
						$obj[0]=$rolP[0];
						$obj[1]=obtenerTituloRol($rolU);
						array_push($arrRolesDispIni,$obj);
						if($arrRolesIni=="")
							$arrRolesIni="'".$rolU."'";
						else
							$arrRolesIni.=",'".$rolU."'";
					}
				}
			}
			
		}
		
	}
	if($arrRolesIni=="")
		$arrRolesIni="''";
	$consulta="select rp.rol,ap.numEtapa from 943_rolesActoresProceso rp,944_actoresProcesoEtapa ap where rp.rol not in (".$arrRolesIni.") and ap.actor=rp.rol and ap.idProceso=rp.idProceso and ap.tipoActor=1 and ap.numEtapa>1 and rp.idProceso=".$idProceso. " order by ap.numEtapa";
	$arrRolesDisp=array();
	$rolesProc=$con->obtenerFilasArregloPHP($consulta);
	if(sizeof($rolesProc)>0)
	{
		foreach($rolesProc as $rolP)
		{
			$descRol=explode("_",$rolP[0]);
			if($descRol[1]!="-1")
			{
				if(existeValor($arrRolesUsr,$rolP[0]))
				{
					if(!isset($arrRolesDisp[$rolP[0]]))
					{
						$arrRolesDisp[$rolP[0]]["tituloRol"]=obtenerTituloRol($rolP[0]);
						$arrRolesDisp[$rolP[0]]["vistasEtapa"]=array();
					}
					array_push($arrRolesDisp[$rolP[0]]["vistasEtapa"],$rolP[1]);
				}
			}
			else
			{
				
				foreach($arrRolesUsr as $rolU)
				{
					$descRol=explode("_",$rolU);
					if(strpos($rolP[0],$descRol[0]."_")!==false)
					{
						if(!isset($arrRolesDisp[$rolU]))
						{
							$arrRolesDisp[$rolU]["tituloRol"]=obtenerTituloRol($rolU);
							$arrRolesDisp[$rolU]["vistasEtapa"]=array();
						}
						array_push($arrRolesDisp[$rolU]["vistasEtapa"],$rolP[1]);
						
					}
				}
				
			}
		}
	}
	$consulta="select idComite,rol from 2007_rolesVSComites where rol in (".$roles.")";
	$resComites=$con->obtenerFilas($consulta);
	$arrComitesDisp=array();
	$obj=array();
	while($filaCom=mysql_fetch_row($resComites))
	{
		$idComite=$filaCom[0];
		$consulta="	select pc.idProyectoVSComite,c.nombreComite from 235_proyectosVSComites pc,2006_comites c 
					where c.idComite=pc.idComite and idProyecto=".$idProceso." and pc.idComite=".$idComite;
		$filaC=$con->obtenerPrimeraFila($consulta);
		if($filaC)
		{
			$obj[0]=$filaC[0];
			$obj[1]=$filaC[1];
			
			$consulta="select ap.numEtapa from 944_actoresProcesoEtapa ap where actor=".$filaC[0]." and ap.tipoActor=2 and ap.numEtapa>1 and ap.idProceso=".$idProceso." order by numEtapa";
			$resEtapas=$con->obtenerFilas($consulta);
			$arrEtapas=array();
			while($fila=mysql_fetch_row($resEtapas))
			{
				array_push($arrEtapas,$fila[0]);
			}
			$obj[2]=$arrEtapas;
			
			array_push($arrComitesDisp,$obj);
		}
	}
	if($arrRolesIni!="''")
	{
?>
	<table id="hor-minimalist-b" width="100%" >
	<thead>
		<tr>
			<th colspan="2">
				Vistas disponibles (Etapa inicial)
			</th>
		</tr>
	</thead>
	<tbody>
		<?php
			krsort($arrRolesDispIni);
			foreach($arrRolesDispIni as $rol)
			{
		?>
		<tr>
			<td width="20"><img src="../images/bullet_green.png" /></td>
			<td align="left"><a href="javascript:enviarActor('<?php echo base64_encode($rol[0])?>','<?php echo  base64_encode("-1")?>')"><span class="letraFichaRespuesta"><?php echo $rol[1]?></span></a></td>
		</tr>
		<?php
			}
		?>
	
<?php
	}
	if((sizeof($arrRolesDisp)>0)||(sizeof($arrComitesDisp)>0))
	{
?>
	<table id="hor-minimalist-b" width="100%" >
	<thead>
		<tr>
			<th colspan="2">
				Vistas disponibles (Por rol / etapa)
			</th>
		</tr>
	</thead>
	<tbody>
		<?php

			if(sizeof($arrRolesDisp)>0)
			{
				krsort($arrRolesDisp);
				foreach($arrRolesDisp as $llave=>$rol)
				{
					
			?>
			<tr>
				<td valign="top" width="20"><br /><img src="../images/bullet_green.png" /></td>
				<td align="left"><br /><span class="letraFichaRespuesta"><?php echo $rol["tituloRol"]?></span><br />
                	<table>
                    
					<?php
						foreach($rol["vistasEtapa"] as $nEtapa)
						{
							$consulta="select nombreEtapa from 4037_etapas where numEtapa=".$nEtapa." and idProceso=".$idProceso;
							$nomEtapa=$con->obtenerValor($consulta);
					?>
						<tr height="21"><td><a href="javascript:enviarActor('<?php echo base64_encode($llave)?>','<?php echo  base64_encode($nEtapa)?>')"><img src="../images/flecha_azul_corta.gif" /></a></td>
                        	<td width="5"></td>
                            <td><a href="javascript:enviarActor('<?php echo base64_encode($llave)?>','<?php echo  base64_encode($nEtapa)?>')"><span class="copyrigthSinPadding"><?php echo removerCerosDerecha($nEtapa).".- ".$nomEtapa?></span> </a></td>
                        </tr>
					<?php
						}
					?>
					</table>
				</td>
			</tr>
			<?php
				}
			}
			if(sizeof($arrRolesDisp)>0)
			{
				foreach($arrComitesDisp as $comite)
				{
			?>
					<tr>
						<td valign="top" width="20"><br /><img src="../images/bullet_green.png" /></td>
						<td align="left"><br /><span class="letraFichaRespuesta"><?php echo $comite[1]?></span><br />
							<table>
							
							<?php
								foreach($comite[2] as $nEtapa)
								{
									$consulta="select nombreEtapa from 4037_etapas where numEtapa=".$nEtapa." and idProceso=".$idProceso;
									$nomEtapa=$con->obtenerValor($consulta);
							?>
								<tr height="21"><td><a href="javascript:enviarActor('<?php echo base64_encode($comite[0])?>','<?php echo  base64_encode($nEtapa)?>')"><img src="../images/flecha_azul_corta.gif" /></a></td>
									<td width="5"></td>
									<td><a href="javascript:enviarActor('<?php echo base64_encode($comite[0])?>','<?php echo  base64_encode($nEtapa)?>')"><span class="copyrigthSinPadding"><?php echo removerCerosDerecha($nEtapa).".- ".$nomEtapa?></span> </a></td>
								</tr>
							<?php
								}
							?>
							</table>
						</td>
					</tr>
		<?php                    
				}
			}
			
			
		?>
		
	</tbody>
	</table>
	<?php
	}
	?>
    </tbody>
	</table>
	<?php
    if(sizeof($arrRolesAsigna)>0)
	{
	?>
    <table id="hor-minimalist-b" width="100%" >
	<thead>
		<tr>
			<th colspan="2">
				Otras acciones disponibles (Por rol)
			</th>
		</tr>
	</thead>
	<tbody>
    <?php
		foreach($arrRolesAsigna as 	$obj)
		{
			$lblEtiqueta="";
			$link="";
			$bullet="../images/flecha_azul_corta.gif";
			switch($obj[2])
			{
				case 15:
					$lblEtiqueta="Designar responsables de registro de requisición";
					$link="javascript:enviarAsignacionResponsable('".bE($idProceso)."','".bE($obj[3])."')";
					$bullet="../images/user_add.png";
				break;		
			}
	?>
			<tr>
				<td valign="top" width="20"><br /><img src="../images/bullet_green.png" /></td>
				<td align="left"><br /><span class="letraFichaRespuesta"><?php echo $rol["tituloRol"]?></span><br /><br />
					<table>
                    <tr height="23">
                    	<td valign="top">
                        	<a href="<?php echo $link?>"><img src="<?php echo $bullet?>" /></a>
                        </td>
                        <td width="5">
                        </td>
                        <td>
                        	<a href="<?php echo $link?>"><span class="copyrigthSinPadding"><?php echo $lblEtiqueta?></span> </a>
                        </td>
                    </tr>
                   	</table>
					
					
				</td>
			</tr>
		
    <?php
		}
	?>
    </tbody>
    </table>
    <?php
	}
	?>
	<input type="hidden" id="idProceso" value="<?php echo base64_encode($idProceso)?>" />
<?php	
	
}

function generarTableroProcesoEstandar($idProceso,$tProceso,$idUsuario,$actor=-1)
{
	global $con;
	$roles="";
	if($actor==-1)
	{
		$consulta="select codigoRol from 807_usuariosVSRoles where idUsuario=".$idUsuario. " and codigoRol not like '%_-1'";
		$roles=$con->obtenerListaValores($consulta,"'");
		if($roles=="")
			$roles="-1";
	}
	else
		$roles=$actor;
	
	$arrRolesUsr=explode(",",str_replace("'","",$roles));
	$consulta="select distinct(actor) from 949_actoresVSAccionesProceso where actor in (".$roles.") and idProceso=".$idProceso;
	$resRolIni=$con->obtenerListaValores($consulta,"'");
	if($resRolIni=="")
		$resRolIni="-1";
	$mostrarVistaRegistro=false;
	$relacionReg="";
	if($con->filasAfectadas>0)
	{
		$mostrarVistaRegistro=true;
		$relacionReg=1;
	}
	else
	{
		$consulta="select nombreTabla,idFormulario from 900_formularios where idProceso=".$idProceso." and formularioBase=1";
		$filaFrm=$con->obtenerPrimeraFila($consulta);
		$nTabla=$filaFrm[0];
		$idFormularioBase=$filaFrm[1];
		$idFrmAutores=incluyeModulo($idProceso,3);
		if($idFrmAutores=="-1")
			$condWhere=" where responsable=".$idUsuario;
		else
			$condWhere=" where id_".$nTabla." in (select distinct idReferencia from 246_autoresVSProyecto where idUsuario=".$idUsuario." and idFormulario=".$idFormularioBase.")";
		$consulta="select id_".$nTabla." from ".$nTabla." ".$condWhere;
		$con->obtenerFilas($consulta);
		if($con->filasAfectadas>0)
		{
			$mostrarVistaRegistro=true;
			$relacionReg=2;
		}
	}
	// 
	
	$consulta="select distinct(actor) from 944_actoresProcesoEtapa where actor in (".$roles.") and actor not in (".$resRolIni.") and tipoActor=1 and idProceso=".$idProceso;
	$rolesProceso=$con->obtenerFilas($consulta);
	$mostrarVistaRoles=false;
	if($con->filasAfectadas>0)
		$mostrarVistaRoles=true;
	$arrRolesDispIni=array();
	$consulta="select idComite from 234_proyectosVSComitesVSEtapas pc where pc.idProyecto=".$idProceso;
	$lComite=$con->obtenerListaValores($consulta);
	$arrRolesComiteDisp=array();
	$mostrarVistaComite=false;
	if($lComite!='')
	{
		$consulta="select distinct(idComite) from 2007_rolesVSComites rc where rc.rol in(".$roles.") and idComite in (".$lComite.")";
		$lComite=$con->obtenerListaValores($consulta);
		if($con->filasAfectadas>0)
			$mostrarVistaComite=true;
	}
	
	
	
	 $consulta="select distinct idUsuarioRevisor,idActorProcesoEtapa from 955_revisoresProceso where idUsuarioRevisor=".$idUsuario." and idProceso=".$idProceso." and estado in (1,2)"; 
	 
	 $resRevisor=$con->obtenerFilas($consulta);
	 $mostrarVistaRevisor=false;
	 if($con->filasAfectadas>0)
		$mostrarVistaRevisor=true;
	
	?>
	<table id="hor-minimalist-b" >
	<thead>
		<tr>
			<th colspan="2">
				Vistas disponibles
			</th>
		</tr>
	</thead>
	<tbody>
		<?php
		if($mostrarVistaRegistro)
		{
			$lblVista="Listado registros, Actor:";
			if($relacionReg=="2")
				$lblVista="Listado registros, Actor:";
		?>
			<tr height="15">
				<td></td>
			</tr>
			<tr>
				<td><img src="../images/bullet_green.png" /></td>
				<td>
					<?php echo $lblVista ?>
				</td>
			</tr>
		<?php
			if($relacionReg=="1")
			{
				$arrRoles=explode(",",$resRolIni);
				foreach($arrRoles as $rol)
				{
		?>
					<tr>
						<td>
						</td>
						<td>
						&nbsp;<a href="javascript:enviarVistaProcesoActor('<?php echo base64_encode($idProceso)?>','<?php echo  base64_encode("1")?>','<?php echo bE($relacionReg)?>','<?php echo bE($rol)?>')"><img src="../images/magnifier.png" /><?php echo obtenerTituloRol(str_replace("'","",$rol))?></a>
						</td>
					</tr>
		<?php	
				}
			}
			else
			{
		?>
				  <tr>
						<td>
						</td>
						<td>
						&nbsp;<a href="javascript:enviarVistaProceso('<?php echo base64_encode($idProceso)?>','<?php echo  base64_encode("1")?>','<?php echo bE($relacionReg)?>')"><img src="../images/magnifier.png" />&nbsp;Participante</a>
						</td>
					</tr>
		<?php
			}						
			
		}
		if($mostrarVistaComite)
		{
		?>
		<tr height="15">
			<td></td>
		</tr>
		<tr>
			<td><img src="../images/bullet_green.png" /></td>
			<td>Evaluación comité:</td>
		</tr>
		<?php
			$consulta="select nombreComite,idComite from 2006_comites where idComite in (".$lComite.")";
			$resC=$con->obtenerFilas($consulta);
			while($filaC=mysql_fetch_row($resC))
			{
		?>
				<tr>
					<td>
					</td>
					<td>
					&nbsp;<a href="javascript:enviarVistaProceso('<?php echo base64_encode($idProceso)?>','<?php echo  base64_encode("2")?>','<?php echo bE($filaC[1])?>')"><img src="../images/magnifier.png" />&nbsp;<?php echo $filaC[0]?></a>
					</td>
				</tr>
		<?php
			}
		}
		if($mostrarVistaRoles)
		{
		?>
		<tr height="15">
			<td></td>
		</tr>
		<tr>
			<td><img src="../images/bullet_green.png" /></td>
			<td>Actividad:</td>
		</tr>	
		<?php
			while($fila=mysql_fetch_row($rolesProceso))
			{
		?>
				<tr>
					<td>
					</td>
					<td>
					&nbsp;<a href="javascript:enviarVistaProceso('<?php echo base64_encode($idProceso)?>','<?php echo  base64_encode("3")?>','<?php echo bE($fila[0])?>')"><img src="../images/magnifier.png" />&nbsp;<?php echo obtenerTituloRol($fila[0])?></a>
					</td>
				</tr>
		<?php
			}
		}
		if($mostrarVistaRevisor)
		{
		?>
		<tr height="15">
			<td></td>
		</tr>
		<tr>
			<td><img src="../images/bullet_green.png" /></td>
			<td>Vista como revisor:</td>
		</tr>
		<?php
			while($fila=mysql_fetch_row($resRevisor))
			{
				
				$consulta="SELECT actor,tipoActor FROM 944_actoresProcesoEtapa WHERE idActorProcesoEtapa=".$fila[1];
				$filaRevisor=$con->obtenerPrimeraFila($consulta);
				if($filaRevisor[1]==1)
					$actor=obtenerTituloRol($filaRevisor[0]);
				else
				{
					$consulta="SELECT c.nombreComite FROM 234_proyectosVSComitesVSEtapas pc,2006_comites c WHERE c.idComite=pc.idComite AND
							pc.idProyectoVSComiteVSEtapa=".$filaRevisor[0];
					$actor=$con->obtenerValor($consulta);
				}
		?>
				<tr>
					<td>
					</td>
					<td>
					&nbsp;<a href="javascript:enviarVistaProceso('<?php echo base64_encode($idProceso)?>','<?php echo  base64_encode("4")?>','<?php echo bE($fila[1])?>')"><img src="../images/magnifier.png" />&nbsp;<?php echo $actor?></a>
					</td>
				</tr>
		<?php
			}
		}
		?>
	<?php
		if(!$mostrarVistaRegistro&&!$mostrarVistaComite&&!$mostrarVistaRoles&&!$mostrarVistaRevisor)
		{
		?>
		<tr>
			<td colspan="2">Usted no cuenta con vistas disponibles</td>
		</tr>
		<?php
		}
	?>
	</tbody>
	</table>	
<?php	
}

function generarOpcionesProceso($idProceso,$ciclo,$idFormulario=-1,$idReferencia=-1)
{
	global $con;
	$consulta="select idTipoProceso FROM 4001_procesos WHERE idProceso=".$idProceso;
	$tProceso=$con->obtenerValor($consulta);
	switch($tProceso)	
	{
		
		case 9:
			generarOpcionesProceso9($idProceso,$ciclo,$idFormulario,$idReferencia);
		break;
		case 28:
			generarOpcionesProceso28($idProceso,$idFormulario,$idReferencia);
		break;
		default:
			generarOpcionesProcesoEstandar($idProceso,$idFormulario,$idReferencia);
		break;
	}
}


function generarOpcionesProcesoEstandar($idProceso,$idFormulario=-1,$idReferencia=-1)
{
	global $con;
	$idUsuario=$_SESSION["idUsr"];
	$consulta="SELECT numEtapa, nombreEtapa,nombreMenuMacro FROM 4037_etapas WHERE idProceso=".$idProceso." ORDER BY nombreEtapa";
	$resEtapa=$con->obtenerFilas($consulta);
	$mostrarOpcion=false;
	$arrActores=array();
	$consulta="select codigoRol from 807_usuariosVSRoles where idUsuario=".$idUsuario. " and codigoRol not like '%_-1'";  //Obtención de roles asociados  a usuario
	$roles=$con->obtenerListaValores($consulta,"'");
	if($roles=="")
		$roles="-1";
	$arrRolesUsr=explode(",",str_replace("'","",$roles));
	$consulta="select distinct(actor) from 949_actoresVSAccionesProceso where actor in (".$roles.") and idProceso=".$idProceso;  //Verificar si el usuario puede ingresar al proceso como actor de etapa inicial
	$resRolIni=$con->obtenerListaValores($consulta,"'");
	if($resRolIni=="")
		$resRolIni="-1";
	$mostrarVistaRegistro=false;
	$relacionReg="";
	
	if($con->filasAfectadas>0)
	{
		$mostrarVistaRegistro=true;
		$relacionReg=1;
	}
	else
	{
		$consulta="select nombreTabla,idFormulario from 900_formularios where idProceso=".$idProceso." and formularioBase=1";
		$filaFrm=$con->obtenerPrimeraFila($consulta);
		$nTabla=$filaFrm[0];
		$idFormularioBase=$filaFrm[1];
		$idFrmAutores=incluyeModulo($idProceso,3);
		if($idFrmAutores=="-1")
			$condWhere=" where responsable=".$idUsuario;
		else
			$condWhere=" where id_".$nTabla." in (select distinct idReferencia from 246_autoresVSProyecto where idUsuario=".$idUsuario." and idFormulario=".$idFormularioBase.")";
		$consulta="select id_".$nTabla." from ".$nTabla." ".$condWhere;
		
		$con->obtenerFilas($consulta);
		if($con->filasAfectadas>0)
		{
			$mostrarVistaRegistro=true;
			$relacionReg=2;
		}
	}
  
	
	
	$consulta="select distinct(actor) from 944_actoresProcesoEtapa where actor in (".$roles.") and actor not in (".$resRolIni.") and tipoActor=1 and idProceso=".$idProceso;
	$rolesProceso=$con->obtenerFilas($consulta);
	$mostrarVistaRoles=false;
	if($con->filasAfectadas>0)
		$mostrarVistaRoles=true;
	$arrRolesDispIni=array();
	
	$consulta="select idComite from 234_proyectosVSComitesVSEtapas pc where pc.idProyecto=".$idProceso;
	$lComite=$con->obtenerListaValores($consulta);
	$arrRolesComiteDisp=array();
	$mostrarVistaComite=false;
	if($lComite!='')
	{
		$consulta="select distinct(idComite) from 2007_rolesVSComites rc where rc.rol in(".$roles.") and idComite in (".$lComite.")";
		$lComite=$con->obtenerListaValores($consulta);
		if($con->filasAfectadas>0)
			$mostrarVistaComite=true;
	}
	
	 $consulta="select distinct idUsuarioRevisor,idActorProcesoEtapa from 955_revisoresProceso where 
	 			idUsuarioRevisor=".$idUsuario." and idProceso=".$idProceso." and estado in (1,2)"; 
	 $resRevisor=$con->obtenerFilas($consulta);
	 $mostrarVistaRevisor=false;
	 if($con->filasAfectadas>0)
		$mostrarVistaRevisor=true;
	
	if($mostrarVistaRegistro)
	{
		if($relacionReg=="1")
		{
			$arrRoles=explode(",",$resRolIni);
			foreach($arrRoles as $rol)
			{
				?>
				<tr >
					<td class="box_body_l"></td>
					<td class="box_body box_body_td" width="100%"><ul><li class="bg_list_un"><a href="javascript:enviarVistaProcesoActor('<?php echo base64_encode($idProceso)?>','<?php echo  base64_encode("1")?>','<?php echo bE($relacionReg)?>','<?php echo bE($rol)?>','<?php echo bE($idFormulario)?>','<?php echo bE($idReferencia)?>')"><b>Perfil:</b> <?php echo obtenerTituloRol(str_replace("'","",$rol))?></a></li></ul></td>
					<td class="box_body_r"></td>
				</tr>
				
				<?php	
			}
		}
		else
		{
			?>
				<tr >
					<td class="box_body_l"></td>
					<td class="box_body box_body_td" width="100%"><ul><li class="bg_list_un"><a href="javascript:enviarVistaProceso('<?php echo base64_encode($idProceso)?>','<?php echo  base64_encode("1")?>','<?php echo bE($relacionReg)?>')"><b>Perfil:</b>&nbsp;Participante</a></li></ul></td>
					<td class="box_body_r"></td>
				</tr>
		<?php
		}
	}
	
	if($mostrarVistaComite)
	{
		$consulta="select nombreComite,idComite from 2006_comites where idComite in (".$lComite.")";
		$resC=$con->obtenerFilas($consulta);
		while($filaC=mysql_fetch_row($resC))
		{
	?>
			<tr >
				<td class="box_body_l"></td>
				<td class="box_body box_body_td" width="100%"><ul><li class="bg_list_un"><a href="javascript:enviarVistaProceso('<?php echo base64_encode($idProceso)?>','<?php echo  base64_encode("2")?>','<?php echo bE($filaC[1])?>')"><b>Perfil:</b> Comité&nbsp;<?php echo $filaC[0]?></a></li></ul></td>
				<td class="box_body_r"></td>
			</tr>
			
	<?php
		}
	}
	
	if($mostrarVistaRoles)
	{
	
		while($fila=mysql_fetch_row($rolesProceso))
		{
	?>
			<tr >
				<td class="box_body_l"></td>
				<td class="box_body box_body_td" width="100%"><ul><li class="bg_list_un"><a href="javascript:enviarVistaProceso('<?php echo base64_encode($idProceso)?>','<?php echo  base64_encode("3")?>')"><img src="../images/magnifier.png" /><b>Perfil:</b>&nbsp;<?php echo obtenerTituloRol($fila[0])?></a></li></ul></td>
				<td class="box_body_r"></td>
			</tr>
	<?php
		}
	}
	
	if($mostrarVistaRevisor)
	{
	
		while($fila=mysql_fetch_row($resRevisor))
		{
			
			$consulta="SELECT actor,tipoActor FROM 944_actoresProcesoEtapa WHERE idActorProcesoEtapa=".$fila[1];
			$filaRevisor=$con->obtenerPrimeraFila($consulta);
			if($filaRevisor[1]==1)
				$actor=obtenerTituloRol($filaRevisor[0]);
			else
			{
				$consulta="SELECT c.nombreComite FROM 234_proyectosVSComitesVSEtapas pc,2006_comites c WHERE c.idComite=pc.idComite AND
						pc.idProyectoVSComiteVSEtapa=".$filaRevisor[0];
				$actor=$con->obtenerValor($consulta);
			}
	?>
			<tr >
				<td class="box_body_l"></td>
				<td class="box_body box_body_td" width="100%"><ul><li class="bg_list_un"><a href="javascript:enviarVistaProceso('<?php echo base64_encode($idProceso)?>','<?php echo  base64_encode("4")?>','<?php echo bE($fila[1])?>')"><img src="../images/magnifier.png" /><b>Perfil:</b> Revisor(<?php echo $actor?>)</a></li></ul></td>
				<td class="box_body_r"></td>
			</tr>
	<?php
		}
	}
}


function generarOpcionesProceso9($idProceso,$ciclo)
{
	global $con;
	$idUsuario=$_SESSION["idUsr"];
	
	$consulta="SELECT esquemaPlaneacion FROM 4001_configuracionProcesoPOA WHERE idProceso=".$idProceso;
	$esquema=$con->obtenerValor($consulta);
	
	if($esquema==2)
	{
	
		$consulta="select codigoRol from 807_usuariosVSRoles where idUsuario=".$idUsuario. " and codigoRol not like '%_-1'";
		$roles=$con->obtenerListaValores($consulta,"'");
		$consulta="SELECT DISTINCT rolActor FROM 9116_responsablesPAT p,9117_estructurasVSPrograma e WHERE e.ruta=p.ruta and e.idProgramaInstitucional=p.idPrograma and e.ciclo=".$ciclo." and idResponsable=".$idUsuario." AND idProceso=".$idProceso;
		$roles2=$con->obtenerListaValores($consulta,"'");
		if($roles2!="")
		{
			if($roles=="")
				$roles=str_replace("|","_",$roles2);
			else	
				$roles.=",".str_replace("|","_",$roles2);
		}
		$arrRolesUsr=explode(",",str_replace("'","",$roles));
		
		$consulta="select rp.rol,ap.numEtapa,idActorProcesoEtapa from 943_rolesActoresProceso rp,944_actoresProcesoEtapa ap where ap.actor=rp.rol and ap.idProceso=rp.idProceso and ap.tipoActor=1  and ap.numEtapa=1 and  
					rp.idProceso=".$idProceso." order by numEtapa";
		$arrRolesDispIni=array();
		$rolesProcIni=$con->obtenerFilasArregloPHP($consulta);
		$arrRolesIni="";
		$arrRolesAsigna=array();
		$arrRolesVarios="";
	
		foreach($rolesProcIni as $rolP)
		{
			$descRol=explode("_",$rolP[0]);
			if($descRol[1]!="-1")
			{
				
				if(existeValor($arrRolesUsr,$rolP[0]))
				{
					$consulta="SELECT idActorVSAccionesProceso FROM 949_actoresVSAccionesProceso WHERE actor='".$rolP[0]."' AND idAccion=15 AND idProceso=".$idProceso;
					
					$idAccion=$con->obtenerValor($consulta);
					if($idAccion!="")
					{
						$obj[0]=$rolP[0];
						$obj[1]=obtenerTituloRol($rolP[0]);
						$obj[2]=15;
						$obj[3]=$idAccion;
						array_push($arrRolesAsigna,$obj);
					}
					
					$consulta="SELECT idAccion FROM 949_actoresVSAccionesProceso WHERE actor='".$rolP[0]."' AND idAccion<>15 AND idProceso=".$idProceso;
					
					$con->obtenerFilas($consulta);
					if($con->filasAfectadas>0)
					{
						$obj[0]=$rolP[0];
						$obj[1]=obtenerTituloRol($rolP[0]);
						array_push($arrRolesDispIni,$obj);
						if($arrRolesIni=="")
							$arrRolesIni="'".$rolP[0]."'";
						else
							$arrRolesIni.=",'".$rolP[0]."'";
					}
				}
			}
			else
			{
				foreach($arrRolesUsr as $rolU)
				{
					if(strpos($rolU,$descRol[0]."_")!==false)
					{
						$consulta="SELECT idAccion FROM 949_actoresVSAccionesProceso WHERE actor='".$rolP[0]."' AND idAccion=15 AND idProceso=".$idProceso;
						
						$con->obtenerFilas($consulta);
						if($con->filasAfectadas>0)
						{
							$obj[0]=$rolP[0];
							$obj[1]=obtenerTituloRol($rolU);
							$obj[2]=15;
							array_push($arrRolesAsigna,$obj);
							
						}
						$consulta="SELECT idAccion FROM 949_actoresVSAccionesProceso WHERE actor='".$rolP[0]."' AND idAccion<>15 AND idProceso=".$idProceso;
						$con->obtenerFilas($consulta);
						if($con->filasAfectadas>0)
						{
							$obj[0]=$rolP[0];
							$obj[1]=obtenerTituloRol($rolU);
							array_push($arrRolesDispIni,$obj);
							if($arrRolesIni=="")
								$arrRolesIni="'".$rolU."'";
							else
								$arrRolesIni.=",'".$rolU."'";
						}
					}
				}
				
			}
			
		}

		if($arrRolesIni=="")
			$arrRolesIni="''";
		$consulta="select rp.rol,ap.numEtapa from 943_rolesActoresProceso rp,944_actoresProcesoEtapa ap where rp.rol not in (".$arrRolesIni.") and ap.actor=rp.rol and ap.idProceso=rp.idProceso and ap.tipoActor=1 and ap.numEtapa>1 and rp.idProceso=".$idProceso. " order by ap.numEtapa";
		$arrRolesDisp=array();
		$rolesProc=$con->obtenerFilasArregloPHP($consulta);
		if(sizeof($rolesProc)>0)
		{
			foreach($rolesProc as $rolP)
			{
				$descRol=explode("_",$rolP[0]);
				if($descRol[1]!="-1")
				{
					if(existeValor($arrRolesUsr,$rolP[0]))
					{
						if(!isset($arrRolesDisp[$rolP[0]]))
						{
							$arrRolesDisp[$rolP[0]]["tituloRol"]=obtenerTituloRol($rolP[0]);
							$arrRolesDisp[$rolP[0]]["vistasEtapa"]=array();
						}
						array_push($arrRolesDisp[$rolP[0]]["vistasEtapa"],$rolP[1]);
					}
				}
				else
				{
					
					foreach($arrRolesUsr as $rolU)
					{
						$descRol=explode("_",$rolU);
						if(strpos($rolP[0],$descRol[0]."_")!==false)
						{
							if(!isset($arrRolesDisp[$rolU]))
							{
								$arrRolesDisp[$rolU]["tituloRol"]=obtenerTituloRol($rolU);
								$arrRolesDisp[$rolU]["vistasEtapa"]=array();
							}
							array_push($arrRolesDisp[$rolU]["vistasEtapa"],$rolP[1]);
							
						}
					}
					
				}
			}
		}

		$consulta="select idComite,rol from 2007_rolesVSComites where rol in (".$roles.")";
		$resComites=$con->obtenerFilas($consulta);
		$arrComitesDisp=array();
		$obj=array();
		while($filaCom=mysql_fetch_row($resComites))
		{
			$idComite=$filaCom[0];
			$consulta="	select pc.idProyectoVSComite,c.nombreComite from 235_proyectosVSComites pc,2006_comites c 
						where c.idComite=pc.idComite and idProyecto=".$idProceso." and pc.idComite=".$idComite;
			$filaC=$con->obtenerPrimeraFila($consulta);
			if($filaC)
			{
				$obj[0]=$filaC[0];
				$obj[1]=$filaC[1];
				
				$consulta="select ap.numEtapa from 944_actoresProcesoEtapa ap where actor=".$filaC[0]." and ap.tipoActor=2 and ap.numEtapa>1 and ap.idProceso=".$idProceso." order by numEtapa";
				$resEtapas=$con->obtenerFilas($consulta);
				$arrEtapas=array();
				while($fila=mysql_fetch_row($resEtapas))
				{
					array_push($arrEtapas,$fila[0]);
				}
				$obj[2]=$arrEtapas;
				
				array_push($arrComitesDisp,$obj);
			}
		}	
		if($arrRolesIni!="''")
		{

	?>
		
			<?php
				krsort($arrRolesDispIni);
				foreach($arrRolesDispIni as $rol)
				{
			?>
			<tr >
			  <td class="box_body_l"></td>
			  <td class="box_body box_body_td" width="100%" ><ul><li class="bg_list_un"><a href="javascript:enviarActorProc('<?php echo base64_encode($rol[0])?>','<?php echo  base64_encode("-1")?>','<?php echo bE($idProceso)?>','<?php echo bE($ciclo)?>')"><b>Perfil:</b> <?php echo $rol[1]?></span></a></li></ul></td>
			  <td class="box_body_r"></td>
		  </tr>
			<?php
				}
			?>
		
	<?php
		}
		
		if((sizeof($arrRolesDisp)>0)||(sizeof($arrComitesDisp)>0))
		{
			if(sizeof($arrRolesDisp)>0)
			{
				krsort($arrRolesDisp);
				foreach($arrRolesDisp as $llave=>$rol)
				{
					$arrEtapas="";
					foreach($rol["vistasEtapa"] as $nEtapa)
					{
						$consulta="select nombreEtapa from 4037_etapas where numEtapa=".$nEtapa." and idProceso=".$idProceso;
						$nomEtapa=$con->obtenerValor($consulta);
						$obj="['".removerCerosDerecha($nEtapa)."','".$nomEtapa."']";
						if($arrEtapas=="")
							$arrEtapas=$obj;
						else
							$arrEtapas.=",".$obj;
					}
					$arrEtapas="[".$arrEtapas."]";
			?>
					<tr >
						<td class="box_body_l"></td>
						<td class="box_body box_body_td" width="100%" ><ul><li class="bg_list_un"><a href="javascript:enviarActorArrEtapas('<?php echo bE($llave)?>','<?php echo  bE($arrEtapas)?>','<?php echo bE($idProceso)?>','<?php echo bE($ciclo)?>')"><b>Perfil:</b> <?php  echo $rol["tituloRol"] ?> </a></li></ul></td>
						<td class="box_body_r"></td>
					</tr>
			
			
			
			<?php
				}
			}
			if(sizeof($arrComitesDisp)>0)
			{
				foreach($arrComitesDisp as $comite)
				{
					$arrEtapas="";
					foreach($comite[2] as $nEtapa)
					{
						$consulta="select nombreEtapa from 4037_etapas where numEtapa=".$nEtapa." and idProceso=".$idProceso;
						$nomEtapa=$con->obtenerValor($consulta);
						$obj="['".removerCerosDerecha($nEtapa)."','".$nomEtapa."']";
						if($arrEtapas=="")
							$arrEtapas=$obj;
						else
							$arrEtapas.=",".$obj;
					}
					$arrEtapas="[".$arrEtapas."]";
			?>
					<tr >
						<td class="box_body_l"></td>
						<td class="box_body box_body_td" width="100%" ><ul><li class="bg_list_un"><a href="javascript:enviarActorArrEtapas('<?php echo bE($comite[0])?>','<?php echo  bE($arrEtapas)?>','<?php echo bE($idProceso)?>','<?php echo bE($ciclo)?>')"><b>Comité:</b> <?php echo $comite[1]?></a></li></ul></td>
						<td class="box_body_r"></td>
					</tr>
		<?php                    
				}
			}
		}
		
		if(sizeof($arrRolesAsigna)>0)
		{
			foreach($arrRolesAsigna as 	$obj)
			{
				$lblEtiqueta="";
				$link="";
				$bullet="../images/flecha_azul_corta.gif";
				switch($obj[2])
				{
					case 15:
						$lblEtiqueta="Designar responsables de registro";
						$link="javascript:enviarAsignacionResponsable('".bE($idProceso)."','".bE($obj[3])."','".bE($ciclo)."')";
						$bullet="../images/user_add.png";
					break;		
				}
		?>
				<tr >
				  <td class="box_body_l"></td>
				  <td class="box_body box_body_td" width="100%" ><ul><li class="bg_list_un"><a href="<?php echo $link?>"><?php echo $lblEtiqueta?></a></li></ul></td>
				  <td class="box_body_r"></td>
			  </tr>
			
		<?php
			}
		
		}
	}
	else
	{
		$consulta="SELECT DISTINCT p.idPrograma FROM 9116_responsablesPAT p,9117_estructurasVSPrograma e 
		WHERE e.ruta=p.ruta and e.idProgramaInstitucional=p.idPrograma and e.ciclo=".$ciclo." and idResponsable=".$idUsuario." AND idProceso=".$idProceso;
		$fila=$con->obtenerPrimeraFila($consulta);
		if($fila)
		{
	?>
	    <tr >
			  <td class="box_body_l"></td>
			  <td class="box_body box_body_td" width="100%" ><ul><li class="bg_list_un"><a href="javascript:enviarResponsableRegistro('<?php echo bE($idProceso)?>','<?php echo bE($ciclo)?>')"><b>Perfil:</b> Responsable de registro</span></a></li></ul></td>
			  <td class="box_body_r"></td>
		  </tr>
    <?php	
		}
	}

}

function generarOpcionesProceso28($idProceso,$idFormulario=-1,$idReferencia=-1)
{
	global $con;
	$idUsuario=$_SESSION["idUsr"];
	$consulta="SELECT numEtapa, nombreEtapa,nombreMenuMacro FROM 4037_etapas WHERE idProceso=".$idProceso." ORDER BY nombreEtapa";
	$resEtapa=$con->obtenerFilas($consulta);
	$mostrarOpcion=false;
	$arrActores=array();
	$consulta="select codigoRol from 807_usuariosVSRoles where idUsuario=".$idUsuario. " and codigoRol not like '%_-1'";  //Obtención de roles asociados  a usuario
	$roles=$con->obtenerListaValores($consulta,"'");
	if($roles=="")
		$roles="-1";
	$arrRolesUsr=explode(",",str_replace("'","",$roles));
	$consulta="select distinct(actor) from 949_actoresVSAccionesProceso where actor in (".$roles.") and idProceso=".$idProceso;  //Verificar si el usuario puede ingresar al proceso como actor de etapa inicial
	$resRolIni=$con->obtenerListaValores($consulta,"'");
	if($resRolIni=="")
		$resRolIni="-1";
	$mostrarVistaRegistro=false;
	$relacionReg="";
	
	if($con->filasAfectadas>0)
	{
		$mostrarVistaRegistro=true;
		$relacionReg=1;
	}
	else
	{
		$consulta="select nombreTabla,idFormulario from 900_formularios where idProceso=".$idProceso." and formularioBase=1";
		$filaFrm=$con->obtenerPrimeraFila($consulta);
		$nTabla=$filaFrm[0];
		$idFormularioBase=$filaFrm[1];
		$idFrmAutores=incluyeModulo($idProceso,3);
		if($idFrmAutores=="-1")
			$condWhere=" where responsable=".$idUsuario;
		else
			$condWhere=" where id_".$nTabla." in (select distinct idReferencia from 246_autoresVSProyecto where idUsuario=".$idUsuario." and idFormulario=".$idFormularioBase.")";
		$consulta="select id_".$nTabla." from ".$nTabla." ".$condWhere;
		
		$con->obtenerFilas($consulta);
		if($con->filasAfectadas>0)
		{
			$mostrarVistaRegistro=true;
			$relacionReg=2;
		}
	}
  
	
	
	$consulta="select distinct(actor) from 944_actoresProcesoEtapa where actor in (".$roles.") and actor not in (".$resRolIni.") and tipoActor=1 and idProceso=".$idProceso;
	$rolesProceso=$con->obtenerFilas($consulta);
	$mostrarVistaRoles=false;
	if($con->filasAfectadas>0)
		$mostrarVistaRoles=true;
	$arrRolesDispIni=array();
	
	$consulta="select idComite from 234_proyectosVSComitesVSEtapas pc where pc.idProyecto=".$idProceso;
	$lComite=$con->obtenerListaValores($consulta);
	$arrRolesComiteDisp=array();
	$mostrarVistaComite=false;
	if($lComite!='')
	{
		$consulta="select distinct(idComite) from 2007_rolesVSComites rc where rc.rol in(".$roles.") and idComite in (".$lComite.")";
		$lComite=$con->obtenerListaValores($consulta);
		if($con->filasAfectadas>0)
			$mostrarVistaComite=true;
	}
	
	 $consulta="select distinct idUsuarioRevisor,idActorProcesoEtapa from 955_revisoresProceso where idUsuarioRevisor=".$idUsuario." and idProceso=".$idProceso." and estado in (1,2)"; 
	 $resRevisor=$con->obtenerFilas($consulta);
	 $mostrarVistaRevisor=false;
	 if($con->filasAfectadas>0)
		$mostrarVistaRevisor=true;
	
	if($mostrarVistaRegistro)
	{
		if($relacionReg=="1")
		{
			$arrRoles=explode(",",$resRolIni);
			foreach($arrRoles as $rol)
			{
				?>
				<tr >
					<td class="box_body_l"></td>
					<td class="box_body box_body_td" width="100%"><ul><li class="bg_list_un"><a href="javascript:enviarVistaProcesoActor('<?php echo base64_encode($idProceso)?>','<?php echo  base64_encode("1")?>','<?php echo bE($relacionReg)?>','<?php echo bE($rol)?>')"><b>Perfil:</b> <?php echo obtenerTituloRol(str_replace("'","",$rol))?></a></li></ul></td>
					<td class="box_body_r"></td>
				</tr>
				
				<?php	
			}
		}
		else
		{
			?>
				<tr >
					<td class="box_body_l"></td>
					<td class="box_body box_body_td" width="100%"><ul><li class="bg_list_un"><a href="javascript:enviarVistaProceso('<?php echo base64_encode($idProceso)?>','<?php echo  base64_encode("1")?>','<?php echo bE($relacionReg)?>')"><b>Perfil:</b>&nbsp;Participante</a></li></ul></td>
					<td class="box_body_r"></td>
				</tr>
		<?php
		}
	}
	
	if($mostrarVistaComite)
	{
		$consulta="select nombreComite,idComite from 2006_comites where idComite in (".$lComite.")";
		$resC=$con->obtenerFilas($consulta);
		while($filaC=mysql_fetch_row($resC))
		{
	?>
			<tr >
				<td class="box_body_l"></td>
				<td class="box_body box_body_td" width="100%"><ul><li class="bg_list_un"><a href="javascript:enviarVistaProceso('<?php echo base64_encode($idProceso)?>','<?php echo  base64_encode("2")?>','<?php echo bE($filaC[1])?>')"><b>Perfil:</b> Comité&nbsp;<?php echo $filaC[0]?></a></li></ul></td>
				<td class="box_body_r"></td>
			</tr>
			
	<?php
		}
	}
	
	if($mostrarVistaRoles)
	{
	
		while($fila=mysql_fetch_row($rolesProceso))
		{
	?>
			<tr >
				<td class="box_body_l"></td>
				<td class="box_body box_body_td" width="100%"><ul><li class="bg_list_un"><a href="javascript:enviarVistaProcesoActor('<?php echo base64_encode($idProceso)?>','<?php echo  base64_encode("1")?>','<?php echo bE("1")?>','<?php echo bE($fila[0])?>')"><img src="../images/magnifier.png" /><b>Perfil:</b>&nbsp;<?php echo obtenerTituloRol($fila[0])?></a></li></ul></td>
				<td class="box_body_r"></td>
			</tr>
	<?php
		}
	}
	
	if($mostrarVistaRevisor)
	{
	
		while($fila=mysql_fetch_row($resRevisor))
		{
			
			$consulta="SELECT actor,tipoActor FROM 944_actoresProcesoEtapa WHERE idActorProcesoEtapa=".$fila[1];
			$filaRevisor=$con->obtenerPrimeraFila($consulta);
			if($filaRevisor[1]==1)
				$actor=obtenerTituloRol($filaRevisor[0]);
			else
			{
				$consulta="SELECT c.nombreComite FROM 234_proyectosVSComitesVSEtapas pc,2006_comites c WHERE c.idComite=pc.idComite AND
						pc.idProyectoVSComiteVSEtapa=".$filaRevisor[0];
				$actor=$con->obtenerValor($consulta);
			}
	?>
			<tr >
				<td class="box_body_l"></td>
				<td class="box_body box_body_td" width="100%"><ul><li class="bg_list_un"><a href="javascript:enviarVistaProceso('<?php echo base64_encode($idProceso)?>','<?php echo  base64_encode("4")?>','<?php echo bE($fila[1])?>')"><img src="../images/magnifier.png" /><b>Perfil:</b> Revisor(<?php echo $actor?>)</a></li></ul></td>
				<td class="box_body_r"></td>
			</tr>
	<?php
		}
	}
}

function obtenerActoresDisponiblesProceso($idProceso,$idUsr=-1,$actor=-1)
{
	global $con;
	global $lblComiteS;

	$arrActores="[]";
	$idUsuario=$_SESSION["idUsr"];
	if($idUsr!=-1)
		$idUsuario=$idUsr;
	$idFormularioBase=obtenerFormularioBase($idProceso);
	$consulta="SELECT configuracionExtra FROM 909_configuracionTablaFormularios WHERE idFormulario=".$idFormularioBase;
	$configuracionExtra=$con->obtenerValor($consulta);
	$rolesAdd="";
	$rolesDel="";
	
	if($configuracionExtra!="")
	{
		
		$configuracionExtra=json_decode($configuracionExtra);
		
		if(isset($configuracionExtra->rolesExclusion))
		{
			foreach($configuracionExtra->rolesExclusion as $oRol)
			{
				if(existeRol("'".$oRol->rolBase."'"))
				{
					if($oRol->accion==0)
					{
						if($rolesDel=="")
						{
							$rolesDel="'".$oRol->rolAfectado."'";
						}
						else
							$rolesDel.=",'".$oRol->rolAfectado."'";
					}
					else
					{
						if($rolesAdd=="")
						{
							$rolesAdd="'".$oRol->rolAfectado."'";
						}
						else
							$rolesAdd.=",'".$oRol->rolAfectado."'";
					}
				}
			}
		}
	}
	

	$consulta="SELECT numEtapa, nombreEtapa,nombreMenuMacro FROM 4037_etapas WHERE idProceso=".$idProceso." ORDER BY nombreEtapa";
	$resEtapa=$con->obtenerFilas($consulta);
	$mostrarOpcion=false;
	$arrActores=array();
	$roles="";
	if($actor==-1)
	{
		$consulta="select codigoRol from 807_usuariosVSRoles where idUsuario=".$idUsuario. " and codigoRol not like '%_-1'";  //Obtención de roles asociados  a usuario
		$roles=$con->obtenerListaValores($consulta,"'");
		if($roles=="")
			$roles="'-100_0'";
		else
			$roles.=",'-100_0'";
		
		
		
		
	}
	else
	{
		$roles=$actor;

	}
	
	
	if($rolesDel!="")
	{
		$arrRolesAplicar=array();
		$arrRolesAux=explode(",",$roles);
		$aRolesDelete=explode(",",$rolesDel);
		
		foreach($arrRolesAux as $r)
		{
			if(!existeValor($aRolesDelete,$r))
			{
				array_push($arrRolesAplicar,$r);
		
			}
		}
		$roles=implode(",",$arrRolesAplicar);
	}
	
	if($rolesAdd!="")
	{
		$aRolesAdd=explode(",",$rolesAdd);
		foreach($aRolesAdd as $r)
		{
			$roles.=",".$r;
		}
	}
	
	
	
	$arrRolesUsr=explode(",",str_replace("'","",$roles));
	
	$consulta="select distinct(actor) from 949_actoresVSAccionesProceso where actor in (".$roles.") 
			and idProceso=".$idProceso." order by prioridad desc,idActorVSAccionesProceso";  //Verificar si el usuario puede ingresar al proceso como actor de etapa inicial

	$resRolIni=$con->obtenerListaValores($consulta,"'");
	if($resRolIni=="")
		$resRolIni="-1";
			
	$mostrarVistaRegistro=false;
	if($con->filasAfectadas>0)
	{
		$relacionReg=1;
		$mostrarVistaRegistro=true;
	}
	else
	{

		$consulta="select nombreTabla,idFormulario from 900_formularios where idProceso=".$idProceso." and formularioBase=1";

		$filaFrm=$con->obtenerPrimeraFila($consulta);
		$nTabla=$filaFrm[0];
		$idFormularioBase=$filaFrm[1];

		$idFrmAutores=incluyeModulo($idProceso,3);
		if($idFrmAutores=="-1")
			$condWhere=" where responsable=".$idUsuario;
		else
			$condWhere=" where id_".$nTabla." in (select distinct idReferencia from 246_autoresVSProyecto where 
						idUsuario=".$idUsuario." and idFormulario=".$idFormularioBase.")";
						
						
		$consulta="select id_".$nTabla." from ".$nTabla." ".$condWhere;
		
		$con->obtenerFilas($consulta);
		if($con->filasAfectadas>0)
		{
			$mostrarVistaRegistro=true;
			$relacionReg=2;
		}
	}

	$consulta="select distinct(actor) from 944_actoresProcesoEtapa where actor in (".$roles.") and actor not in (".$resRolIni.") and 
				tipoActor=1 and idProceso=".$idProceso;

	$rolesProceso=$con->obtenerFilas($consulta);
	$mostrarVistaRoles=false;
	if($con->filasAfectadas>0)
		$mostrarVistaRoles=true;
	$arrRolesDispIni=array();
	
	$consulta="select distinct idComite from 234_proyectosVSComitesVSEtapas pc where pc.idProyecto=".$idProceso;
	$lComite=$con->obtenerListaValores($consulta);
	$arrRolesComiteDisp=array();
	$mostrarVistaComite=false;
	if($lComite!='')
	{
		$consulta="select distinct(idComite) from 2007_rolesVSComites rc where rc.rol in(".$roles.") and idComite in (".$lComite.")";
		$lComite=$con->obtenerListaValores($consulta);
		if($con->filasAfectadas>0)
			$mostrarVistaComite=true;
	}
	
	 $consulta="select distinct idUsuarioRevisor,idActorProcesoEtapa from 955_revisoresProceso where idUsuarioRevisor=".$idUsuario
	 		." and idProceso=".$idProceso." and estado in (1,2)"; 
	 $resRevisor=$con->obtenerFilas($consulta);
	 $mostrarVistaRevisor=false;
	 if($con->filasAfectadas>0)
		$mostrarVistaRevisor=true;
	$arrActoresDisponibles=array();
	if($mostrarVistaRegistro)
	{
		
		if($relacionReg=="1")
		{
			
			$arrRoles=explode(",",$resRolIni);
			foreach($arrRoles as $rol)
			{
				$idPerfil=obtenerIdPerfilEscenario($idProceso,1,str_replace("'","",$rol),true);	
				$consulta="select idActorVSAccionesProceso from 949_actoresVSAccionesProceso where idProceso=".$idProceso.
							" and idAccion=8 and actor =".$rol." and idPerfil=".$idPerfil;
				
				$idActorAgregar=$con->obtenerValor($consulta);
				
				$consulta="SELECT nombreFuncionEjecucion FROM 956_funcionesEvaluacionAccionActor WHERE actor=".$rol.
						" AND tipoActor='1' AND accion LIKE '%A%' and idProceso=".$idProceso;
				$nFuncion=$con->obtenerValor($consulta);

				$resEvaluacion=true;
				if($nFuncion!="")
					eval('$resEvaluacion='.$nFuncion."(".$rol.",".$idUsuario.",".$idProceso.",1);");

				if((!$resEvaluacion)||($idActorAgregar==""))
					$idActorAgregar=-1;

				$rol=str_replace("'","",$rol);
				array_push($arrActoresDisponibles,"['".$rol."','".obtenerTituloRol($rol)."','{\"idActorAgregar\":\"".$idActorAgregar."\",\"tipoActor\":\"1\"}']");

			}
		}
		else
		{
			array_push($arrActoresDisponibles,"['','Participante','5']");
			?>
				<tr >
					<td class="box_body_l"></td>
					<td class="box_body box_body_td" width="100%"><ul><li class="bg_list_un"><a href="javascript:enviarVistaProceso('<?php echo base64_encode($idProceso)?>','<?php echo  base64_encode("1")?>','<?php echo bE($relacionReg)?>')"><b>Perfil:</b>&nbsp;Participante</a></li></ul></td>
					<td class="box_body_r"></td>
				</tr>
		<?php
		}
	}
	
	if($mostrarVistaComite)
	{
		$consulta="select nombreComite,idComite from 2006_comites where idComite in (".$lComite.")";
		$resC=$con->obtenerFilas($consulta);
		while($filaC=mysql_fetch_row($resC))
		{
			array_push($arrActoresDisponibles,"['".$filaC[1]."','".$filaC[0]."','{\"tipoActor\":\"2\"}']");
		}
	}
	
	if($mostrarVistaRoles)
	{
	
		while($fila=mysql_fetch_row($rolesProceso))
		{
			array_push($arrActoresDisponibles,"['".$fila[0]."','".obtenerTituloRol($fila[0])."','{\"tipoActor\":\"3\"}']");
		}
	}
	
	if($mostrarVistaRevisor)
	{
	
		while($fila=mysql_fetch_row($resRevisor))
		{
			
			$consulta="SELECT actor,tipoActor FROM 944_actoresProcesoEtapa WHERE idActorProcesoEtapa=".$fila[1];
			$filaRevisor=$con->obtenerPrimeraFila($consulta);
			if($filaRevisor[1]==1)
				$actor=obtenerTituloRol($filaRevisor[0]);
			else
			{
				$consulta="SELECT c.nombreComite FROM 234_proyectosVSComitesVSEtapas pc,2006_comites c WHERE c.idComite=pc.idComite AND
						pc.idProyectoVSComiteVSEtapa=".$filaRevisor[0];
				$actor=$con->obtenerValor($consulta);
			}
			array_push($arrActoresDisponibles,"['".$fila[1]."','Revisor (".$actor.")','{\"tipoActor\":\"4\"}']");
	
		}
	}
	
	
	
	if(sizeof($arrActoresDisponibles)>0)
	{
		$arrActores="";
		foreach($arrActoresDisponibles as $actorD)
		{
			if($arrActores=="")
				$arrActores=$actorD;
			else
				$arrActores.=",".$actorD;
		}
		$arrActores="[".$arrActores."]";
	}
	else
		$arrActores='[]';
	

	return $arrActores;
}

function agregoRegistros($rol,$idUsuario,$idProceso,$nRegistrosLimite)
{
	global $con;
	$idFormularioBase=obtenerFormularioBase($idProceso);
	$consulta="select count(*) from _".$idFormularioBase."_tablaDinamica where responsable=".$idUsuario." and fechaCreacion>'2012-11-075'";
	$nRegistros=$con->obtenerValor($consulta);

	if($nRegistros>=$nRegistrosLimite)
		return false;
	return true;
}

function obtenerActorProcesoIdRol($idProceso,$rol,$numEtapa)
{
	global $con;
	$consulta="SELECT idPerfil FROM 206_perfilesEscenarios WHERE idProceso=".$idProceso." AND tipoActor=1 AND actor='".$rol."' AND situacion=1";
	
	$idPerfil=$con->obtenerValor($consulta);
	if($idPerfil=="")
		$idPerfil=-1;
	
	if($numEtapa>0)
	{
		
		$consulta="SELECT idActorProcesoEtapa FROM 944_actoresProcesoEtapa WHERE idProceso=".$idProceso." AND numEtapa=".$numEtapa.
				" AND actor='".$rol."' AND tipoActor=1 and idPerfil=".$idPerfil;
	}
	else
	{
		$consulta="SELECT idActorVSAccionesProceso FROM 949_actoresVSAccionesProceso WHERE idProceso=".$idProceso." AND actor='".$rol."' AND idAccion=8";
	}
	return $con->obtenerValor($consulta);
}

?>