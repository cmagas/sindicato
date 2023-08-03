<?php
	function generarSesionesProyecto($idFormulario,$idRegistro)
	{
		global $con;
		$arrSesiones=array();
		$arrDias=array();
		$consulta="SELECT fechaInicio,fechaFinalizacion FROM _401_tablaDinamica WHERE id__401_tablaDinamica=".$idRegistro;
		$fSesion=$con->obtenerPrimeraFila($consulta);
		
		$consulta="SELECT  dia FROM _401_GridSesionesClase WHERE idReferencia=".$idRegistro;
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$arrDias[$fila[0]]=$fila[0];
		}
		
		$fIni=strtotime($fSesion[0]);
		$fFin=strtotime($fSesion[1]);
		while($fIni<=$fFin)
		{
			$fecha=date("Y-m-d",$fIni);
			if(esDiaHabilProyecto($fecha,$idRegistro))
			{
				$dia=date("N",$fIni);				
				if(isset($arrDias[$dia]))
				{
					array_push($arrSesiones,$fecha);
				}
			}
			$fIni=strtotime("+1 days",$fIni);
		}
		
		$x=0;
		$query[$x]="begin";
		$x++;
		
		
		
		$arrSesionesActivas=array();
		$consulta="select idSesion,noSesion,fechaSesion from 3003_sesionesProyecto WHERE idFormulario=".$idFormulario." AND idReferencia=".$idRegistro;
		$resS=$con->obtenerFilas($consulta);
		while($filaS=mysql_fetch_row($resS))
		{
			$o["idSesion"]=$filaS[0];
			$o["noSesion"]=$filaS[1];
			$o["fechaSesion"]=$filaS[2];
			$o["mantener"]=0;
			array_push($arrSesionesActivas,$o);
		}
		$numPos=0;
		
		if(sizeof($arrSesiones)>0)
		{
			foreach($arrSesiones as $fecha)
			{
				if(isset($arrSesionesActivas[$numPos]))
				{
					$arrSesionesActivas[$numPos]["fechaSesion"]=$fecha;
					$arrSesionesActivas[$numPos]["mantener"]=1;
				}
				else
				{
					$arrSesionesActivas[$numPos]["idSesion"]=-1;
					$arrSesionesActivas[$numPos]["noSesion"]=($numPos+1);
					$arrSesionesActivas[$numPos]["fechaSesion"]=$fecha;
					$arrSesionesActivas[$numPos]["mantener"]=1;
				}
				$numPos++;
			}
		}
		if(sizeof($arrSesionesActivas)>0)
		{
			foreach($arrSesionesActivas as $o)
			{
				if($o["idSesion"]==-1)
				{
					$query[$x]="INSERT INTO 3003_sesionesProyecto(idFormulario,idReferencia,noSesion,fechaSesion)
								VALUES(".$idFormulario.",".$idRegistro.",".$o["noSesion"].",'".$o["fechaSesion"]."')";
					$x++;
				}
				else
				{
					if($o["mantener"]==1)
					{
						$query[$x]="update  3003_sesionesProyecto set noSesion=".$o["noSesion"].",fechaSesion='".$o["fechaSesion"]."' WHERE idSesion=".$o["idSesion"];
						$x++;
					}
					else
					{
						$query[$x]="DELETE FROM 3003_sesionesProyecto WHERE idSesion=".$o["idSesion"];
						$x++;
						$query[$x]="DELETE FROM 3004_sesionesVSTemas WHERE idSesion=".$o["idSesion"];
						$x++;

					}
				}
			}
		}
		$query[$x]="commit";
		$x++;
		return $con->ejecutarBloque($query);
	}
	
	function esDiaHabilProyecto($fecha,$idProyecto)
	{
		global $con;
		$arrFechasNoHabiles=array();
		$consulta="SELECT count(*) FROM _401_gridDiasNOHabiles WHERE idReferencia=".$idProyecto." and '".$fecha."'>=fechaInicio and '".$fecha."'<=fechaFin ORDER BY fechaInicio,fechaFin";
		$nReg=$con->obtenerValor($consulta);
		if($nReg==0)
			return true;
		return false;
	}

?>