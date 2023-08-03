<?php
    include("latis/conexionBD.php");

    listarArea();

    function listarArea()
    {
        global $con;

        $x=0;
		$arrRegistro="";
		
        $consulta="SELECT * FROM 11_cat_areas ORDER BY idArea";
        $res=$con->obtenerFilas($consulta);

        while($fila=mysql_fetch_row($res))
		{
            $x++;
			
            $o='{"id":"'.$fila[0].'","nombre":"'.$fila[3].'","email":"'.$fila[4].'","estatus":"'.$fila[5].'"}';
			
			if($arrRegistro=="")
				$arrRegistro=$o;
			else
				$arrRegistro.=",".$o;
			
        }

		echo '{"data":['.$arrRegistro.']}';
    }

?>