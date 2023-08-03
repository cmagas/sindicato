<?php
    session_start();
    include_once("latis/conexionBD.php");
    include_once("latis/utiles.php");
 
 	obtenerAreas();

    function obtenerAreas()
    {
        global $con;
        
        $arrRegistro="";

        $consulta="SELECT idArea,nombreArea FROM 11_cat_areas WHERE situacion='1'";
        $res=$con->obtenerFilas($consulta);

        while($fila=mysql_fetch_row($res))
		{
			
            $o='{"id":"'.$fila[0].'","nombre":"'.$fila[1].'"}';

            if($arrRegistro=="")
				$arrRegistro=$o;
			else
				$arrRegistro.=",".$o;
        }

        echo $arrRegistro;
    }

    ?>