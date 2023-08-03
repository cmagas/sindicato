<?php
    include("latis/conexionBD.php");

    listarNoticias();

    function listarNoticias()
    {
        global $con;
        $fechaActual=date("Y-m-d");
        $nombreArea="";
        

        $x=0;
        $arrRegistro="";
        
        $consulta="SELECT * FROM 203_noticias ORDER BY idNoticia";
        $res=$con->obtenerFilas($consulta);

        while($fila=mysql_fetch_row($res))
        {
            $x++;

            $fechaPlub=cambiarFormatoFecha($fila[6]);
            $fechaFin=cambiarFormatoFecha($fila[7]);
            $nombreArea=obtenerNombreAreas($fila[8]);

            $o='{"id":"'.$fila[0].'","titulo":"'.$fila[3].'","descripcion":"'.$fila[4].'","fechaPub":"'.$fechaPlub.'","fechaFin":"'.$fechaFin.'","idArea":"'.$fila[8].'","nombreArea":"'.$nombreArea.'","situacion":"'.$fila[9].'"}';
            
            if($arrRegistro=="")
                $arrRegistro=$o;
            else
                $arrRegistro.=",".$o;
            
        }

        echo '{"data":['.$arrRegistro.']}';
        
    }


?>