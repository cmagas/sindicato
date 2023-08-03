<?php
    session_start();
	include_once("latis/conexionBD.php"); 
	include_once("latis/funcionesEnvioMensajes.php"); 
	
	
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
            autenticarUsuario();
        break;
    }

    function autenticarUsuario()
	{
		global $con;
		
		$l=$_POST["l"];
		$p=$_POST["p"];
		
		$consulta="SELECT * FROM usuarios WHERE usu_nombre='".$l."' AND usu_contrasena='".$p."' AND situacion in(1,3)";
		$fila=$con->obtenerPrimeraFila($consulta);

		if($fila)
		{
			$conRol="SELECT idRol FROM 807_usuariosVSRoles WHERE idUsuario=".$fila[0]." AND idRol=1";
			$idRol=$con->obtenerValor($conRol);
			
			$_SESSION["idUsr"]=$fila[0];
			$_SESSION["login"]=$fila[1];
			$_SESSION["nombreUsr"]=$fila[2];
			$_SESSION["statusCuenta"]=$fila[5];
			$_SESSION["idEmpresa"]=$fila[6];

			$consultaRol="SELECT idRol from 807_usuariosVSRoles where idUsuario=".$fila[0];
			$resRoles=$con->obtenerFilas($consultaRol);
				
			$listaGrupo="";
			while($fRoles=mysql_fetch_row($resRoles))
			{
				if($listaGrupo=="")
					$listaGrupo=$fRoles[0];
				else
					$listaGrupo.=",".$fRoles[0];
			}

			if($listaGrupo=="")
				$listaGrupo='-1';
			$_SESSION["idRol"]=$listaGrupo;
			
			
			echo "1|1";
		}
		else
		{
			$_SESSION["idUsr"]="-1";
			$_SESSION["login"]="";
			$_SESSION["idRol"]="-1000";
			$_SESSION["status"]="-1";
			
			echo "1|0";
		}
	}
?>