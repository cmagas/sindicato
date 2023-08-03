<?php
    include("latis/conexionBD.php");

    

    listarAviso();

    function listarAviso()
    {
        global $con;
        $fechaActual=date("Y-m-d");
        $docExistente=1;

        $x=0;
		$arrRegistro="";
		
        $consulta="SELECT * FROM 201_avisos WHERE '".$fechaActual."' BETWEEN fechaPublicacion AND fechaFin ORDER BY fechaPublicacion,idAviso";
        $res=$con->obtenerFilas($consulta);

        while($fila=mysql_fetch_row($res))
		{
            $x++;

            $fechaPlub=cambiarFormatoFecha($fila[7]);
            $fechaFin=cambiarFormatoFecha($fila[8]);
            $nombreArea=obtenerNombreAreas($fila[9]);

            if($fila[6]=="" || $fila[6]==null)
            {
                $docExistente=0;
            }
			
            $o='{"id":"'.$fila[0].'","titulo":"'.$fila[3].'","descripcion":"'.$fila[4].'","url":"'.$fila[6].'","fechaPub":"'.$fechaPlub.'","fechaFin":"'.$fechaFin.'","idArea":"'.$fila[9].'","nombreArea":"'.$nombreArea.'","docExiste":"'.$docExistente.'","situacion":"'.$fila[10].'"}';
			
			if($arrRegistro=="")
				$arrRegistro=$o;
			else
				$arrRegistro.=",".$o;
			
        }

		echo '{"data":['.$arrRegistro.']}';

    }

?>