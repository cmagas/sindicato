<?php
    include("latis/conexionBD.php");

    listarGaleria();

    function listarGaleria()
    {
        global $con;
        $fechaActual=date("Y-m-d");
       
        $x=0;
		$arrRegistro="";
		
        $consulta="SELECT * FROM 204_galeria where situacion IN(0,1) ORDER BY idGaleria";
        $res=$con->obtenerFilas($consulta);

        while($fila=mysql_fetch_row($res))
		{
            $x++;

            $fechaPlub=cambiarFormatoFecha($fila[2]);
			
            $o='{"id":"'.$fila[0].'","titulo":"'.$fila[3].'","fechaPub":"'.$fechaPlub.'","url":"'.$fila[6].'","situacion":"'.$fila[7].'"}';
			
			if($arrRegistro=="")
				$arrRegistro=$o;
			else
				$arrRegistro.=",".$o;
			
        }

		echo '{"data":['.$arrRegistro.']}';

    }

?>