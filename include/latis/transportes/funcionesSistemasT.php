<?php
	function validarCurpChofer($curp,$idRegistro)
	{
		global $con;
		$res="1|";
		$lnCurp=strlen($curp);
		if($lnCurp<>18)
		{
			$res="El tamaño de la CURP es invalido";
			echo $res;
		}
		else
		{
			if($idRegistro==-1)
			{
				//echo "2";
				$consulta="SELECT id__1013_tablaDinamica FROM _1013_tablaDinamica WHERE curp='".$curp."'";
				$resp=$con->obtenerValor($consulta);
				if($resp)
				{
					$res="CURP ya existente";
					echo $res;
				}
			}
			else
			{
				//echo "3";
				$consulta="SELECT id__1013_tablaDinamica FROM _1013_tablaDinamica WHERE curp='".$curp."' and id__1013_tablaDinamica<>'".$idRegistro."'";
				$resp=$con->obtenerValor($consulta);
				if($resp)
				{
					$res="CURP ya existente";
					echo $res;
				}
			}
		}
		return $res;
	}





?>