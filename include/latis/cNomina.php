<?php include_once("latis/cContabilidad.php");

class cNomina
{
	var $cConta;
	function cNomina()
	{
		
	}
	
	function realizarAsientosNomina($idNomina,$idConcepto)
	{
		global $con;
		$consulta="SELECT ciclo,quincenaAplicacion,idPerfil FROM 672_nominasEjecutadas WHERE idNomina=".$idNomina;
		$filaNom=$con->obtenerPrimeraFila($consulta);
		$ciclo=$filaNom[0];
		
		$this->cConta=new cContabilidad($ciclo);
		$consulta="SELECT nombrePerfil FROM 662_perfilesNomina WHERE idPerfilesNomina=".$filaNom[2];
		$nPerfil=$con->obtenerValor($consulta);
		$nLibro="Nómina Ejecutada del perfil ".$nPerfil." Quincena: ".$filaNom[0]."-".$ciclo;
		$consulta="INSERT INTO 590_librosDiarios(tituloLibro, descripcion) VALUES('Nom-".$filaNom[0]." ".$filaNom[1]."','".$nLibro."')";
		if(!$con->ejecutarConsulta($consulta))
			return;
		$idLibro=$con->obtenerUltimoID();
		
		$consulta="INSERT INTO 594_rolesVSLibros(idLibro,rol,permisos) VALUES(".$idLibro.",'-100_0','C')";
		if(!$con->ejecutarConsulta($consulta))
			return;
		
		$consulta="SELECT objDetalle,codDepartamento FROM 671_asientosCalculosNomina WHERE idNomina=".$idNomina;
		$res=$con->obtenerFilas($consulta);
		$x=0;

		while($fila=mysql_fetch_row($res))
		{
			$objAsiento=unserialize($fila[0]);
			$arrDistribucion=array();
			foreach($objAsiento->arrCalculosGlobales as $calculo)
			{
				$arrNombre=explode(" [",$calculo["nombreCalculo"]);
				foreach($calculo["distriCuentas"] as $c)
				{
					
					$objCuenta[0]=$c["codCuentaAfectacion"];
					$objCuenta[1]=$arrNombre[0];
					$objCuenta[2]=$c["porcentaje"];
					$objCuenta[3]=$c["tipoAfectacion"];
					$objCuenta[4]=$c["valorAsignado"];
					$objCuenta[5]=$c["tipoPresupuesto"];
					$objCuenta[6]=$c["idTipoPresupuesto"];
					if($objCuenta[4]!=0)
						array_push($arrDistribucion,$objCuenta)	;	
				}
			}
			$arrAsientos=json_encode($arrDistribucion);
			$cadObj='	{
							"tipoBeneficiarioO":"",
							"idBeneficiarioO":"",
							"tipoBeneficiarioD":"1",
							"idBeneficiarioD":"'.$objAsiento->idUsuario.'",
							"folio":"",
							"arrAsientos":'.$arrAsientos.', 
							"tipoMovimiento":"'.$idConcepto.'",
							"montoOperacion":"",
							"idFormulario":"",
							"idRegistro":"",
							"idProceso":"",
							"idPrograma":"", 
							"codDepto":"'.$fila[1].'", 
							"codPartida":"", 
							"idFormularioProy":"",
							"idRegistroProy":"",	
							"folioProy":"",
							"idLibro":"'.$idLibro.'",
							"tipoPago":"1",
							"tipoPresupuesto":"1"
							
						}';
						
			$obj=json_decode($cadObj);
			
			$this->cConta->asentarMovimiento($obj);
		}
		
		return $idLibro;
	}
	
}
		
?>