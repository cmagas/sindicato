<?php 
	include_once("latis/conexionBD.php"); 
	
	
	function buscarAlumnoCobroServicios($clave,$tipoBusqueda)
	{
		global $con;
		$arrRegistros=array();
		$aRegistros='';
		$arrGrupos[1]="A";
		$arrGrupos[2]="B";
		$arrGrupos[3]="C";
		$arrGrupos[4]="D";
		$arrGrupos[5]="E";
		
		$consulta="select * from (SELECT id__1069_tablaDinamica,apPaterno,apMaterno,nombre,curp,planEstudios,grados,grupo,concat(apPaterno,' ',apMaterno,' ',nombre) as nombreFull FROM _1069_tablaDinamica) as tmp 
				WHERE nombreFull LIKE '%".$clave."%'";

		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$planEstudio=obtenerNombreInstanciaPlan($fila[5]);
			$consulta="SELECT leyendaGrado FROM 4501_Grado WHERE idGrado=".$fila[6];
			$grado=$con->obtenerValor($consulta);
			$o='{"idRegistro":"'.$fila[0].'","apPaterno":"'.cv($fila[1]).'","apMaterno":"'.cv($fila[2]).'","nombre":"'.cv($fila[3]).'","curp":"'.cv($fila[4]).'","planEstudio":"'.cv($planEstudio).'","grado":"'.$grado.'","grupo":"'.$arrGrupos[$fila[7]].'"}';

			if($aRegistros=="")
				$aRegistros=$o;
			else
				$aRegistros.=",".$o;
		}

		$obj["metaData"]="[".$aRegistros."]";
		array_push($arrRegistros,$obj);
		return $arrRegistros;
	}
	
	function formatearConceptoIngreso($idVenta,$idProductoVenta,$idProducto,$nombreProducto)
	{
		global $con;
		
		$datosAlumno="";
		$nProducto=$nombreProducto;
		$consulta="SELECT descripcion FROM 6009_productosVentaCaja WHERE idProductoVenta=".$idProductoVenta;
		$descripcion=$con->obtenerValor($consulta);
		$arrDescripcion=explode('<br><br><b>Comentarios adicionales:</b>',$descripcion);
		
		$consulta="SELECT idCliente FROM 6008_ventasCaja WHERE idVenta=".$idVenta;
		$idAlumno=$con->obtenerValor($consulta);
		$consulta="SELECT CONCAT(apPaterno,' ',apMaterno,' ',nombre) AS nombre,curp,(SELECT nombrePlanEstudios FROM 4513_instanciaPlanEstudio WHERE idInstanciaPlanEstudio=planEstudios) as planEstudio FROM _1069_tablaDinamica WHERE id__1069_tablaDinamica=".$idAlumno;
		$fDatosAlumno=$con->obtenerPrimeraFila($consulta);
		

		
		$datosAlumno=$fDatosAlumno[2].", ".$fDatosAlumno[0].", CURP: ".$fDatosAlumno[1];
		$nProducto.="<br>(".$arrDescripcion[0]."<br>".$datosAlumno.")";
		$nProducto=str_replace("<br>","\n\r",$nProducto);
		return $nProducto;
		
	}
	
?>