<?php
	function exportarModuloAutores($idFormulario,$idRegistro)
	{
		global $con;
		$idProceso=obtenerIdProcesoFormularioBase($idFormulario);
		$consulta="select ed.complementario from 203_elementosDTD ed,900_formularios f where f.idFormulario=ed.idFormulario and f.titulo=3 and ed.idProceso=".$idProceso;
		$conf=$con->obtenerValor($consulta);
		$arrConsulta=explode(",",$conf);
		
		$idPerfil=$arrConsulta[0];
		$principal=$arrConsulta[1];
		$externoP=$arrConsulta[2];
		$singular="Investigador";
		  if(isset($arrConsulta[3]))
			  $singular=$arrConsulta[3];
		  $plural="Investigadores";
		  if(isset($arrConsulta[4]))
			  $plural=$arrConsulta[4];
		  $responsable="Responsable";
		  if(isset($arrConsulta[5]))
			  $responsable=$arrConsulta[5];
		$obj="[['".$singular."','Participación','Orden de participación','".$responsable."'],";
		$consulta="select concat(i.Paterno,' ',i.Materno,', ',i.Nom) as nombre,(select descParticipacion from 953_elementosPerfilesParticipacionAutor where idElementoPerfilAutor=a.claveParticipacion),
				a.orden,if(a.responsable='1','Sí','No') as responsable 	from 246_autoresVSProyecto a,802_identifica i 
				  where   a.idUsuario<>703585 and i.idUsuario=a.idUsuario and a.idFormulario=".$idFormulario." and a.idReferencia=".$idRegistro." order by orden";
			  
		$arrAutores=$con->obtenerFilasArreglo($consulta);	
		$arrAutores=$obj.substr($arrAutores,1);	 
		$objAutores='{"etiquetaExportacion":"","valor":"","nombreCampo":"","tipoCampo":"0","camposHijos":"['.$arrAutores.']","cabeceraPrimeraFila":"1"}';
		$cadObj='{"seccion":"@nFormulario","idFormulario":"@idFormulario","instancias":[{"campos":['.$objAutores.']}]}';
		return $cadObj;
	}
?>