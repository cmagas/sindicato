<?php 
	function prepararPublicacionBoletin($idFormulario,$idRegistro)
	{
		global $con;
		$consulta="UPDATE _752_tablaDinamica SET idEstado=6,publicadoEn=".$idRegistro." WHERE id__752_tablaDinamica IN (
					SELECT idArticulo FROM 3005_articulosBoletin WHERE idFormulario=".$idFormulario." AND idReferencia=".$idRegistro.")";
		verificarBoletinesPublicados();
		return $con->ejecutarConsulta($consulta);
	}
	
	function verificarBoletinesPublicados()
	{
		global $con;
		$consulta="SELECT id__763_tablaDinamica FROM _763_tablaDinamica WHERE idEstado=2 AND fechaInicio='".date("Y-m-d")."'";
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$consulta="SELECT DISTINCT codigoInstitucion FROM _716_tablaDinamica";
			$resEmpresas=$con->obtenerFilas($consulta);
			while($fEmpresa=mysql_fetch_row($resEmpresas))
			{
				$consulta="SELECT idUsuario FROM 801_adscripcion where Institucion='".$fEmpresa[0]."'";
				$resUsr=$con->obtenerFilas($consulta);
				while($fUsr=mysql_fetch_row($resUsr))
				{
					$idUsuario=$fUsr[0];
					$consulta="SELECT Mail FROM 805_mails WHERE idUsuario=".$idUsuario." and trim(Mail)<>''";
					$resMail=$con->obtenerFilas($consulta);
					while($fMail=mysql_fetch_row($resMail))
					{
						$arrParam["idBoletin"]=$fila[0];
						$arrParam["idUsuario"]=$idUsuario;
						$arrParam["email"]=$fMail[0];
						enviarMensajeEnvio(4,$arrParam);
					}
				}
			}
		}
	}
	
	function generarFichaResumenArticulo($idRegistro,$tiempoEjecucion=false)
	{
		global $con;
		global $urlSitio;
		$claseResumen="Letragris";
		$claseArticulo="Letranegra";
		$claseTitulo="letraVino14N";
		$claseLeerMas="vino11SubrayadadoNegritas";
		
		$aArticulo="";
		$cArticulo="";
		if($tiempoEjecucion)
		{
			$aArticulo='<a href="javascript:abrirArticulo(\''.bE($idRegistro).'\')">';
			$cArticulo='</a>';
		}
		$consulta="SELECT idUsuario FROM 246_autoresVSProyecto WHERE idFormulario=752 AND idReferencia=".$idRegistro." AND responsable=1";
		$idAutor=$con->obtenerValor($consulta);
		if($idAutor=="")
			$idAutor=-1;
		$nombreAutor=obtenerNombreUsuario($idAutor);
		$consulta="SELECT * FROM _752_tablaDinamica WHERE id__752_tablaDinamica =".$idRegistro;
		$fRegistro=$con->obtenerPrimeraFila($consulta);
		$tblResumen='<table width="100%">
					<tr>
						<td>'.$aArticulo.'<span class="'.$claseTitulo.'">'.$fRegistro[10].'</span>'.$cArticulo.'</td>
					</tr>
					<tr>
						<td align="right"><br><span style="font-size:11px; color:#777; font-weight: normal" class="letraExt"><b>Por:</b> '.$nombreAutor.'</span></td>
					</tr>
					<tr height="20">
							<td></td>
						</tr>
					<tr>
						<td valign="top">
							<p><img style="MARGIN-RIGHT: 10px" src="'.$urlSitio.'/paginasFunciones/obtenerArchivos.php?id='.bE($fRegistro[16]).'" height="100" alt="" width="100" align="left" /><div class="'.$claseResumen.'" style="text-align:justify">'.$fRegistro[11].'</div></p>
						</td>
					</tr>
					<tr height="10">
						<td></td>
					</tr>
					<tr>
						<td align="right">'.$aArticulo.'<span class="'.$claseLeerMas.'">Leer mas...</span>'.$cArticulo.'</td>
					</tr>
					</table>';
		
		return $tblResumen;
	}
	
	function generarFichaArticulo($idRegistro)
	{
		global $con;
		global $urlSitio;
		$claseResumen="Letragris";
		$claseArticulo="Letranegra";
		$claseTitulo="letraVino14N";
		$claseLeerMas="vino11SubrayadadoNegritas";
		
		$consulta="SELECT idUsuario FROM 246_autoresVSProyecto WHERE idFormulario=752 AND idReferencia=".$idRegistro." AND responsable=1";
		$idAutor=$con->obtenerValor($consulta);
		if($idAutor=="")
			$idAutor=-1;
		$nombreAutor=obtenerNombreUsuario($idAutor);
		$consulta="SELECT * FROM _752_tablaDinamica WHERE id__752_tablaDinamica =".$idRegistro;
		$fRegistro=$con->obtenerPrimeraFila($consulta);
		$cadCita="";
		$consulta="SELECT cita FROM citaxnumero WHERE idFormulario=752 AND idReferencia=".$idRegistro." AND cita <>''";
		$resCita=$con->obtenerFilas($consulta);
		while($fCita=mysql_fetch_row($resCita))
		{
			if($cadCita=="")
				$cadCita='<span style="font-size:11px; color:#777; font-weight: normal" class="letraExt">'.$fCita[0].'</span>';
			else
				$cadCita.='<span style="font-size:11px; color:#777; font-weight: normal" class="letraExt">'.$fCita[0].'</span><br><br>';
		}
		$urlDocumento="";
		if($fRegistro[14]!="")
			$urlDocumento='<table><tr height="5"><td></td></tr><tr><td><a href="../paginasFunciones/obtenerArchivos.php?id='.bE($fRegistro[14]).'"><img src="../imagenesDocumentos/16/file_extension_pdf.png"></a></td><td><span style="color:#006">&nbsp;<a href="../paginasFunciones/obtenerArchivos.php?id='.bE($fRegistro[14]).'">Descargar artículo</a></span></td></tr></table>';
		
		
		$tblArticulo='<table width="100%">
					<tr>
						<td><span class="'.$claseTitulo.'">'.$fRegistro[10].'</span><br></td>
					</tr>
					<tr>
						<td align="right"><br><span style="font-size:11px; color:#777; font-weight: normal" class="letraExt"><b>Por:</b> '.$nombreAutor.'<br>'.$urlDocumento.'</span></td>
					</tr>
					<tr height="20">
							<td></td>
						</tr>
					<tr>
						<td valign="top">
							<p><img style="MARGIN-RIGHT: 10px" src="'.$urlSitio.'/paginasFunciones/obtenerArchivos.php?id='.bE($fRegistro[16]).'" height="100" alt="" width="100" align="left" /><div class="'.$claseArticulo.'" style="text-align:justify">'.$fRegistro[13].'</div></p>
						</td>
					</tr>
					<tr height="10">
						<td></td>
					</tr>
					<tr height="1">
						<td style="background-color: #CCC"></td>
					</tr>
					<tr height="10">
						<td></td>
					</tr>
					<tr>
						<td valign="top">
							'.$cadCita.'
						</td>
					</tr>
					</table>';
		return $tblArticulo;
	}
	
	function generarLinkBoletin($idBoletin)
	{
		$cadLink=bE("100584.".$idBoletin.".latis");
		return $cadLink;
	}
	
	function rendererGeneradorLink($valor)
	{
		global $urlSitio;
		$cad=$urlSitio."/modulosEspeciales_Biotq/portalBoletin.php?ref=".generarLinkBoletin($valor);
		
		$url='<a href="'.$cad.'">'.$cad.'</a>';
		
		return "'".$url."'";
		
	}
	
	function rendererGeneradorVacante($valor)
	{
		return "'".generarFichaResumenVacanteEmail($valor,"",false)."'";
	}
	
	function generarFichaResumenVacante($idVacante,$noRegistro,$vistaPortal=true)
	{
		global $con;
		global $urlSitio;
		$claseResumen="letraExt";
		$claseArticulo="Letranegra";
		$claseTitulo="letraVino14N";
		$claseLeerMas="vino11SubrayadadoNegritas";
		
		$aArticulo="";
		$cArticulo="";
		if($vistaPortal)
		{
			$aArticulo='<a href="javascript:window.parent.abrirDetalleVacante(\''.bE($idVacante).'\')">';
			$cArticulo='</a>';
		}
		else
		{
			$aArticulo='<a href="'.$urlSitio.'/modulosEspeciales_Biotq/portalBolsaTrabajo.php?v='.generarLinkBoletin($idVacante).'">';
			$cArticulo='</a>';
		}
		$consulta="SELECT fechaCambio FROM 941_bitacoraEtapasFormularios WHERE idFormulario=771 AND idRegistro=".$idVacante." ORDER BY idRegistroEstado";
		$fPublicacion=strtotime($con->obtenerValor($consulta));
		$consulta="SELECT * FROM _771_tablaDinamica WHERE id__771_tablaDinamica =".$idVacante;
		$fRegistro=$con->obtenerPrimeraFila($consulta);
		$consulta="select unidad from 817_organigrama where codigoUnidad='".$fRegistro[8]."'";
		$nombreEmpresa=$con->obtenerValor($consulta);
		$consulta="SELECT nombreCategoria FROM _772_tablaDinamica WHERE id__772_tablaDinamica=".$fRegistro[22];
		$nombreCategoria=$con->obtenerValor($consulta);
		if($noRegistro!="")
			$noRegistro.=".- ";
		$tblResumen='<table width="100%">
					<tr>
						<td width="10" valign="top"><span class="'.$claseTitulo.'">'.$noRegistro.'</span></td><td width="10"></td><td valign="top">'.$aArticulo.'<span class="'.$claseTitulo.'">'.$fRegistro[10].'</span>'.$cArticulo.'</td>
					</tr>
					<tr height="21">
						<td colspan="2"></td><td align="right"><span style="font-size:11px; color:#000; font-weight: normal" class="letraExt"><b>Publicado el día:</b> '.date("d/m/Y",$fPublicacion).'</span><br></td>
					</tr>
					<tr height="21">
						<td colspan="2"></td><td align="left"><span style="font-size:11px;" class="letraRojaSubrayada8"><b>Categoría:</b></span><span style="font-size:11px;" class="corpo8Bold"><b> '.$nombreCategoria.'</b></span></td>
					</tr>
					<tr height="21">
						<td colspan="2"></td><td align="left"><span style="font-size:11px;" class="letraRojaSubrayada8"><b>Empresa solicitante:</b></span><span style="font-size:11px;" class="corpo8Bold"><b> '.$nombreEmpresa.'</b></span></td>
					</tr>
					
					<tr height="20">
							<td colspan="2"></td><td></td>
						</tr>
					<tr>
						<td colspan="2"></td>
						<td valign="top">
							<div class="'.$claseResumen.'" style="text-align:justify">'.$fRegistro[11].'</div></p>
						</td>
					</tr>
					<tr height="10">
						<td colspan="3"></td>
					</tr>
					<tr>
						<td colspan="2"></td>
						<td align="right">'.$aArticulo.'<span class="'.$claseLeerMas.'">Detalles...</span>'.$cArticulo.'</td>
					</tr>
					</table>';
		
		return $tblResumen;
	}
	
	function generarFichaResumenVacanteEmail($idVacante,$noRegistro,$vistaPortal=true)
	{
		global $con;
		global $urlSitio;
		
		$aArticulo="";
		$cArticulo="";
		if($vistaPortal)
		{
			$aArticulo='<a href="javascript:abrirDetalleVacante(\''.bE($idVacante).'\')">';
			$cArticulo='</a>';
		}
		else
		{
			$aArticulo='<a href="'.$urlSitio.'/modulosEspeciales_Biotq/portalBolsaTrabajo.php?v='.generarLinkBoletin($idVacante).'">';
			$cArticulo='</a>';
		}
		$consulta="SELECT fechaCambio FROM 941_bitacoraEtapasFormularios WHERE idFormulario=771 AND idRegistro=".$idVacante." ORDER BY idRegistroEstado";
		$fPublicacion=strtotime($con->obtenerValor($consulta));
		$consulta="SELECT * FROM _771_tablaDinamica WHERE id__771_tablaDinamica =".$idVacante;
		$fRegistro=$con->obtenerPrimeraFila($consulta);
		$consulta="select unidad from 817_organigrama where codigoUnidad='".$fRegistro[8]."'";
		$nombreEmpresa=$con->obtenerValor($consulta);
		$consulta="SELECT nombreCategoria FROM _772_tablaDinamica WHERE id__772_tablaDinamica=".$fRegistro[22];
		$nombreCategoria=$con->obtenerValor($consulta);
		
		$tblResumen='<table width="100%">
					<tr height="1"><td colspan="2"></td><td align="left"><table width="400"><tbody><tr style="height:1px !important;"><td style="width:100%;background-color:#CCC"></td></tr></tbody></table></td></tr>
					<tr>
						<td width="10" valign="top"><span ></span></td><td width="10"></td><td valign="top">'.$aArticulo.'<span  style="color:#B0281A; font-size:14px;font-weight: bold !important;text-decoration: underline !important;">'.$fRegistro[10].'</span>'.$cArticulo.'</td>
					</tr>
					<tr height="21">
						<td colspan="2"></td><td align="right"><span style="font-size:11px; color:#000; font-weight: normal"><b>Publicado el día:</b> '.date("d/m/Y",$fPublicacion).'</span><br></td>
					</tr>
					<tr height="21">
						<td colspan="2"></td><td align="left"><span style="color:#B0281A; font-size:11px;font-weight: bold !important;text-decoration: underline !important;" ><b>Categoría:</b></span><span style="font-size:11px;" ><b> '.$nombreCategoria.'</b></span></td>
					</tr>
					<tr height="21">
						<td colspan="2"></td><td align="left"><span style="color:#B0281A; font-size:11px;font-weight: bold !important;text-decoration: underline !important;" ><b>Empresa solicitante:</b></span><span style="font-size:11px;" ><b> '.$nombreEmpresa.'</b></span></td>
					</tr>
					
					<tr height="20">
							<td colspan="2"></td><td></td>
						</tr>
					<tr>
						<td colspan="2"></td>
						<td valign="top">
							<div style="text-align:justify;font-size:11px">'.$fRegistro[11].'</div></p>
						</td>
					</tr>
					<tr height="10">
						<td colspan="3"></td>
					</tr>
					<tr>
						<td colspan="2"></td>
						<td align="right">'.$aArticulo.'<span style="color:#B0281A; font-size:10px;font-weight: bold !important;text-decoration: underline !important;">Detalles...</span>'.$cArticulo.'</td>
					</tr>
					<tr height="1"><td colspan="2"></td><td align="left"><table width="400"><tbody><tr style="height:1px !important;"><td style="width:100%;background-color:#CCC"></td></tr></tbody></table></td></tr>
					</table>';
		
		return $tblResumen;
	}
	
	function notificarVacanteBolsaTrabajo($idRegistro)
	{
		global $con;
		global $con;
		$consulta="SELECT txtCorreoE,txtNombre,txtPaterno,txtMaterno FROM _734_tablaDinamica WHERE recibeAnuncionBosaTrabajo=1";
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$arrParam["idVacante"]=$idRegistro;
			$arrParam["apPaterno"]=$fila[2];
			$arrParam["apMaterno"]=$fila[3];
			$arrParam["nombre"]=$fila[1];
			$arrParam["email"]=$fila[0];
			enviarMensajeEnvio(5,$arrParam);
			
		}
	}
	
	function registrarEmpresa($idRegistro)
	{
		global $con;
		$consulta="SELECT txtRazonSocial,txtDireccion,txtColonia,txtCodPostal,cmbEstado,cmbMunicipio,txtNumeroExterior,txtNumeroInterior,txtDescripcionEmpresaEsp,txtRFC FROM _716_tablaDinamica WHERE id__716_tablaDinamica=".$idRegistro;
		$fRegistro=$con->obtenerPrimeraFila($consulta);
		$numero=$fRegistro[6];
		if($fRegistro[7]!="")
			$numero.=" Int. ".$fRegistro[7];
		$consulta="SELECT codigoInstitucion FROM _716_tablaDinamica WHERE id__716_tablaDinamica=".$idRegistro;
		$codigoInstitucion=$con->obtenerValor($consulta);
		$consulta="select idOrganigrama from 817_organigrama where codigoUnidad='".$codigoInstitucion."'";
		$iOrganigrama=$con->obtenerValor($consulta);
		
		$consulta="SELECT MAX(codigoUnidad) FROM 817_organigrama WHERE unidadPadre='0002'";
		$maxUnidad=$con->obtenerValor($consulta);
		if($maxUnidad=="")
			$maxUnidad=1;
		else
			$maxUnidad=($maxUnidad*1)+1;
		
		$codigoIndividual=str_pad($maxUnidad,4,"0",STR_PAD_LEFT);
		$maxUnidad="0002".$codigoIndividual;
		$x=0;
		$query[$x]="begin";
		$x++;
		if($iOrganigrama=="")
		{
			$query[$x]="INSERT INTO 817_organigrama(unidad,codigoFuncional,codigoUnidad,descripcion,institucion,unidadPadre,codigoIndividual,codigoInstitucion,fechaCreacion,responsableCreacion,STATUS,instColaboradora)
						VALUES('".cv($fRegistro[0])."','".$maxUnidad."','".$maxUnidad."','".cv($fRegistro[8])."',1,'0002','".$codigoIndividual."','0001','".date("Y-m-d")."',".$_SESSION["idUsr"].",1,0)";
			$x++;
			$query[$x]="set @idRegistro:=(select last_insert_id())";
			$x++;
			$query[$x]="INSERT INTO 247_instituciones(idOrganigrama,cp,ciudad,estado,idPais,fechaCreacion,responsable,municipio,colonia,calle,numero)
						VALUES(@idRegistro,'".$fRegistro[3]."','".$fRegistro[5]."','".$fRegistro[4]."',146,'".date("Y-m-d")."',".$_SESSION["idUsr"].",'".$fRegistro[5]."','".$fRegistro[2]."','".cv($fRegistro[1])."','".$numero."')";

			$x++;
		}
		else
		{
			$query[$x]="update 817_organigrama set unidad='".cv($fRegistro[0])."',descripcion='".cv($fRegistro[8])."' where idOrganigrama=".$iOrganigrama;
			$x++;
			$query[$x]="set @idRegistro:=".$iOrganigrama;
			$x++;
			$query[$x]="update 247_instituciones set cp='".$fRegistro[3]."',ciudad='".$fRegistro[5]."',estado='".$fRegistro[4]."',municipio='".$fRegistro[5]."',colonia='".$fRegistro[2].
					"',calle='".cv($fRegistro[1])."',numero='".$numero."' where idOrganigrama=".$iOrganigrama;
			$x++;
		}
		$query[$x]="delete from 818_telefonosOrganigrama where idOrganigrama=@idRegistro";
		$x++;
		$consulta="SELECT lada,numTelefono FROM _716_gridTelefono WHERE idReferencia=".$idRegistro;
		$resTelefono=$con->obtenerFilas($consulta);
		while($fTelefono=mysql_fetch_row($resTelefono))
		{
			$query[$x]="INSERT INTO 818_telefonosOrganigrama(idOrganigrama,lada,telefono,tipoTel)
						VALUES(@idRegistro,'".$fTelefono[0]."','".$fTelefono[1]."',0)";
			$x++;
		}
		$query[$x]="UPDATE _716_tablaDinamica SET codigoInstitucion='".$maxUnidad."' WHERE id__716_tablaDinamica=".$idRegistro;
		$x++;
		$query[$x]="commit";
		$x++;		
		if($con->ejecutarBloque($query))
		{
			if($iOrganigrama=="")
			{
				$idUsuario=crearBaseUsuario("Administrador","Empresa",$fRegistro[9],"");
				$query=array();
				$x=0;
				$query[$x]="begin";
				$x++;
				$query[$x]="update 800_usuarios set Login='".$fRegistro[9]."' where idUsuario=".$idUsuario;
				$x++;
				$query[$x]="update 801_adscripcion set Institucion='".$maxUnidad."' where idUsuario=".$idUsuario;
				$x++;
				$query[$x]="INSERT INTO 807_usuariosVSRoles(idUsuario,idRol,idExtensionRol,codigoRol) VALUES(".$idUsuario.",2,0,'2_0')";
				$x++;
				$query[$x]="INSERT INTO 807_usuariosVSRoles(idUsuario,idRol,idExtensionRol,codigoRol) VALUES(".$idUsuario.",82,0,'82_0')";
				$x++;
				$query[$x]="INSERT INTO 807_usuariosVSRoles(idUsuario,idRol,idExtensionRol,codigoRol) VALUES(".$idUsuario.",84,0,'84_0')";
				$x++;
				$query[$x]="INSERT INTO 807_usuariosVSRoles(idUsuario,idRol,idExtensionRol,codigoRol) VALUES(".$idUsuario.",85,0,'85_0')";
				$x++;
				$query[$x]="INSERT INTO 807_usuariosVSRoles(idUsuario,idRol,idExtensionRol,codigoRol) VALUES(".$idUsuario.",61,0,'61_0')";
				$x++;
				$query[$x]="INSERT INTO 807_usuariosVSRoles(idUsuario,idRol,idExtensionRol,codigoRol) VALUES(".$idUsuario.",86,0,'86_0')";
				$x++;
				$query[$x]="INSERT INTO 807_usuariosVSRoles(idUsuario,idRol,idExtensionRol,codigoRol) VALUES(".$idUsuario.",-3000,0,'-3000_0')";
				$x++;
				$query[$x]="commit";
				$x++;	
				return $con->ejecutarBloque($query);
			}
			return true;
		}
			
	}
	
	function registrarCompra($idRegistroCompra)
	{
		global $con;
		$consulta="SELECT idReferencia,concepto FROM _779_tablaDinamica WHERE id__779_tablaDinamica=".$idRegistroCompra;
		$fSolicitud=$con->obtenerPrimeraFila($consulta);
		$consulta="SELECT fechaCompra,montoTotal,id__782_tablaDinamica FROM _782_tablaDinamica WHERE idReferencia=".$idRegistroCompra;
		$fCompra=$con->obtenerPrimeraFila($consulta);
		$consulta="INSERT INTO 3001_movimientosRegistro(idFormulario,idReferencia,fechaOperacion,montoOperacion,tipoOperacion,concepto,fechaCreacion,respCreacion,idFormularioRef,idRegistroRef)
					VALUES(758,".$fSolicitud[0].",'".$fCompra[0]."',".$fCompra[1].",-1,'Adquisición del producto: ".cv($fSolicitud[1])."','".date("Y-m-d H:i:s")."',".$_SESSION["idUsr"].",779,".$idRegistroCompra.")";
		return $con->ejecutarConsulta($consulta);
	}
	
	function esProyectoEjecucion($idRegistro)
	{
		global $con;
		$consulta="SELECT idEstado FROM _758_tablaDinamica WHERE id__758_tablaDinamica=".$idRegistro;
		$idEstado=$con->obtenerValor($consulta);
		if($idEstado==2)
			return 1;
		else 
			return 0;
	}
?>