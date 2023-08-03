<?php
    include("latis/conexionBD.php");

    listarUsuario();

    function listarUsuario()
    {
        global $con;
        $x=0;
		$arrRegistro="";
		
        $consulta="SELECT * FROM usuarios where idUsuario!=3 ORDER BY  idUsuario";
        $res=$con->obtenerFilas($consulta);

        while($fila=mysql_fetch_row($res))
		{
            $x++;
			$nomEmpresa=obtenerNombreEmpresaCliente($fila[9]);
            $o='{"id":"'.$fila[0].'","nombre":"'.$fila[6].'","apPaterno":"'.$fila[7].'","apMaterno":"'.$fila[8].'","sexo":"'.$fila[5].'","email":"'.$fila[10].'","estatus":"'.$fila[11].'","usuario":"'.$fila[3].'","pass":"'.$fila[4].'"}';
			
			if($arrRegistro=="")
				$arrRegistro=$o;
			else
				$arrRegistro.=",".$o;
			
        }

		echo '{"data":['.$arrRegistro.']}';

    }

?>