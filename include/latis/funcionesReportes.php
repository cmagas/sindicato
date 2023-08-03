<?php session_start();
	include("latis/conexionBD.php"); 
	
	$parametros="";
	if(isset($_POST["funcion"]))
	{
		$funcion=$_POST["funcion"];
		if(isset($_POST["param"]))
		{
			$p=$_POST["param"];
			$parametros=json_decode($p,true);
			
		}
	}	
	
	switch($funcion)
	{
		case 1: 
			obtenerProgramasSede();
		break;
		case 2:
			cambiarStatusGrado();
		break;
	}
	
	function obtenerProgramasSede()
	{
		global $con;
		$sede=$_POST["sede"];
		$ciclo=$_POST["ciclo"];
		$consulta="SELECT idMapaCurricular,concat(nombrePrograma,' Modalidad: ',m.nombre) as modalidad FROM 4241_nuevosMapas n,4004_programa p,4153_modalidadPrograma m WHERE m.idModalidad=n.idModalidadCurso and p.idPrograma=n.idPrograma
					and n.sede='".$sede."' AND n.ciclo=".$ciclo;
		$arrProgramas=$con->obtenerFilasArreglo($consulta);
		echo "1|".$arrProgramas;
	}
	
	function cambiarStatusGrado()
	{
		global $con;
		$valor=$_POST["valor"];
		$idMapa=$_POST["idMapa"];
		$idGrado=$_POST["idGrado"];
		$x=0;
		$consulta[$x]="begin";
		$x++;
		$consulta[$x]="delete from 4241_aperturaGrados where idNuevoMapa=".$idMapa." and idGrado=".$idGrado;
		$x++;
		$consulta[$x]="insert into 4241_aperturaGrados(idNuevoMapa,idGrado,situacion) values(".$idMapa.",".$idGrado.",".$valor.")";
		$x++;
		$consulta[$x]="commit";
		$x++;
		eB($consulta);
	}
?>