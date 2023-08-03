<?php
    include("latis/conexionBD.php");

    //echo "aqui estamos";
    listarEventos();

    function listarEventos()
    {
        global $con;
        $fechaActual=date("Y-m-d");
        

        $x=0;
		$arrRegistro="";
		
        $consulta="SELECT * FROM 202_eventos WHERE '".$fechaActual."' BETWEEN fechaPublicacion 
                    AND fechaFin ORDER BY fechaPublicacion,idEvento";
         $res=$con->obtenerFilas($consulta);

        while($fila=mysql_fetch_row($res))
		{
            $x++;

            $fechaPlub=cambiarFormatoFecha($fila[6]);
            $fechaFin=cambiarFormatoFecha($fila[7]);
            $fechaEvento=cambiarFormatoFecha($fila[10]);
	
            $o='{"id":"'.$fila[0].'","titulo":"'.$fila[3].'","descripcion":"'.$fila[4].'","fechaPub":"'.$fechaPlub.'","fechaFin":"'.$fechaFin.'","lugar":"'.$fila[8].'","hora":"'.$fila[9].'","fechaEvento":"'.$fechaEvento.'","situacion":"'.$fila[11].'"}';

			if($arrRegistro=="")
				$arrRegistro=$o;
			else
				$arrRegistro.=",".$o;
			
        }

		echo '{"data":['.$arrRegistro.']}';
    }

?>