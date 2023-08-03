<?php 
include_once("latis/conexionBD.php");
include_once("latis/utiles.php");


function registrarFigurasJuridicasOrden($idFormulario,$idRegistro,$idProceso)
{
	global $con;
	$fechaCreacion=date("Y-m-d H:i:s");
	
	$consultar="SELECT carpetaAdministrativa,idEvento,responsable,codigoInstitucion FROM _67_tablaDinamica WHERE id__67_tablaDinamica='".$idRegistro."'";
	$res=$con->obtenerPrimeraFila($consultar);
	$carpetaAdministrativa=$res[0];
	$idEvento=$res[1];
	$responsable=$res[2];
	$institucion=$res[3];
	
	$consultarFiguras="SELECT u.idOpcion,p.idPadre,p.idOpcion AS tipoFigura,apellidoPaterno,apellidoMaterno,nombre,p.id__47_chParticipacionJuridica 
					FROM _67_usuariosNotificar u,_47_chParticipacionJuridica p,_47_tablaDinamica n 
					WHERE u.idOpcion=p.id__47_chParticipacionJuridica AND p.idPadre=n.id__47_tablaDinamica AND u.idPadre='".$idRegistro."'";
	$resFiguras=$con->obtenerFilas($consultarFiguras);
	$x=0;
	$query[$x]="begin";
	$x++;
	
	while($fila=mysql_fetch_row($resFiguras))
	{
		$nombre=$fila[5]." ".$fila[3]." ".$fila[4];
		$tipoFigura="SELECT nombreTipo FROM _5_tablaDinamica WHERE id__5_tablaDinamica='".$fila[2]."'";
		$nombreFigura=$con->obtenerValor($tipoFigura);
		
		$query[$x]="INSERT INTO _72_tablaDinamica(idReferencia,fechaCreacion,responsable,codigoInstitucion,nombreNotificar,
					figuraJuridica,carpetaAdministrativa,idProcesoPadre,idFiguraJuridica,idReferenciaFormulario)VALUES('".$idRegistro."',
					'".$fechaCreacion."','".$responsable."','".$institucion."','".$nombre."','".$nombreFigura."','".$carpetaAdministrativa."',
					'".$idProceso."','".$fila[2]."','".$fila[6]."')";
		$x++;
		$query[$x]="set @idRegistroG=(select last_insert_id())";
		$x++;
		$query[$x]="UPDATE _72_tablaDinamica SET codigo=@idRegistroG WHERE id__72_tablaDinamica=@idRegistroG";
		$x++;			
					
	}
		$query[$x]="commit";
		$x++;
		$con->ejecutarBloque($query);
}

function insertarDatosSolicitudDefensor($idFormulario,$idRegistro)
{
	global $con;
	$consulEliminar="DELETE FROM _82_tablaDinamica WHERE idReferencia='".$idRegistro."'";
	$con->ejecutarConsulta($consulEliminar);
	
	$consultaDatos="SELECT fechaCreacion,responsable,codigoInstitucion,idEvento,carpetaAdministrativa1,cmbImputado,codigo FROM _80_tablaDinamica
				WHERE id__80_tablaDinamica='".$idRegistro."'";
	$res=$con->obtenerPrimeraFila($consultaDatos);
	$fechaCreacion=$res[0];
	$responsable=$res[1];
	$institucion=$res[2];
	$idEvento=$res[3];
	$carpetaAdministrativa=$res[4];
	$imputado=$res[5];
	$folioSolicitud=$res[6];
	
	$consultarNombre="SELECT apellidoPaterno,apellidoMaterno,nombre FROM _47_tablaDinamica WHERE id__47_tablaDinamica='".$imputado."'";
	$resNombre=$con->obtenerPrimeraFila($consultarNombre);
	$nombreImputado=$resNombre[2]." ".$resNombre[0]." ".$resNombre[1];
	
	$x=0;
	$query[$x]="begin";
	$x++;
	
	$query[$x]="INSERT INTO _82_tablaDinamica(idReferencia,fechaCreacion,responsable,idEstado,codigoInstitucion,folioSolicitud,
				carpetaAdministrativa,nombreImputado,idEvento) VALUES('".$idRegistro."','".$fechaCreacion."','".$responsable."','2',
				'".$institucion."','".$folioSolicitud."','".$carpetaAdministrativa."','".$nombreImputado."','".$idEvento."')";
	$x++;
	
	$query[$x]="set @idRegistroG=(select last_insert_id())";
	$x++;
	$query[$x]="UPDATE _82_tablaDinamica SET codigo=@idRegistroG WHERE id__82_tablaDinamica=@idRegistroG";
	$x++;			
	
	$query[$x]="commit";
	$x++;
	$con->ejecutarBloque($query);
}

function cambiarEtapaFormulario96($idRegistro,$idFormulario)
{
	global $con;
	
	$consulta="SELECT tipoPromociones FROM _96_tablaDinamica WHERE id__96_tablaDinamica='".$idRegistro."'";
	$res=$con->obtenerValor($consulta);
	
	switch($res)
	{
		case 1: //Tramite
				$etapa="3";
		break;
		case 2: //Solicitud de programación de audiencia
				$etapa="4";
		break;
		case 3: //interposición de apelación
				$etapa="5";
		break;
		case 4: //Juicio de amparo
				$etapa="5";
		break;
		case 5: //Autoridad federal
				$etapa="5";
		break;
	}
	
	$actualizar="UPDATE _96_tablaDinamica SET idEstado='".$etapa."' WHERE id__96_tablaDinamica='".$idRegistro."'";
	$con->ejecutarConsulta($actualizar);
}


?>