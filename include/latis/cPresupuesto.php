<?php
	include_once("latis/conexionBD.php");

class cPresupuesto
{
	var $arrDescripcionConceptos;
	function cPresupuesto()
	{
		global $con;
		$this->arrDescripcionConceptos=array();
		$query="SELECT idConcepto,descripcionRegistroMovimiento FROM 598_conceptos";
		$res=$con->obtenerFilas($query);
		while($f=mysql_fetch_row($res))
		{
			$this->arrDescripcionConceptos[$f[0]]=$f[1];
		}
	}
	
	function registrarAfectacionPresupuestal($obj)
	{
		global $con;
		$x=0;
		$consulta[$x]="begin";
		$x++;
		$arrAsientos=array();
		if(!isset($obj->arrAsientos))
		{
		
			$query="SELECT cmbTiempoPresupuestal,txtProcentajeAfectacion,cmbTipoAfectacion FROM  _935_tablaDinamica WHERE cmbSituacion=1 AND idReferencia=".$obj->tipoMovimiento;
			$resAfectacion=$con->obtenerFilas($query);
			while($filaAfectacion=mysql_fetch_row($resAfectacion))
			{
				$objDatos=array();
				$objDatos[0]=$filaAfectacion[0];
				$objDatos[1]=$filaAfectacion[1];
				$objDatos[2]=$filaAfectacion[2];
				
				$objDatos[3]=($filaAfectacion[1]/100)*$obj->montoMovimiento;
				array_push($arrAsientos,$objDatos);
			}
		}
		else
			$arrAsientos=$obj->arrAsientos;
		
		$folio=$this->obtenerFolio($obj);
		$descripcionMov=$this->arrDescripcionConceptos[$obj->tipoMovimiento];
		foreach($arrAsientos as $filaAfectacion)
		{
			$noMovimiento=$this->obtenerNoMovimiento($obj);
			$idFormulario="NULL";
			if(isset($obj->idFormulario)&&($obj->idFormulario!=""))
				$idFormulario=$obj->idFormulario;
			$idRegistro="NULL";
			if(isset($obj->idRegistro)&&($obj->idRegistro!=""))
				$idRegistro=$obj->idRegistro;
			$idProceso="NULL";
			if(isset($obj->idProceso)&&($obj->idProceso!=""))
				$idProceso=$obj->idProceso;
			$complementario="";
			if(isset($obj->complementario))
				$complementario=$obj->complementario;
			$mes=date("m",strtotime($obj->fechaMovimiento))-1;
			if(isset($obj->mes))
				$mes=$obj->mes;
			$consulta[$x]="INSERT INTO 528_asientosCuentasPresupuestales(ciclo,programa,departamento,capitulo,partida,mes,monto,
						fechaOperacion,responsableOperacion,tiempoPresupuestal,operacion,idFormulario,idReferencia,idProceso,complementario,
						origenMovimiento,fuenteFinanciamiento,descripcionMovimiento,folioMovimiento,noMovimiento,ruta,horaMovimiento)
						VALUES(".$obj->idCiclo.",".$obj->idPrograma.",'".$obj->departamento."','".$obj->capitulo."','".$obj->partida."',".
						$mes.",".$filaAfectacion[3].",'".$obj->fechaMovimiento."',".$obj->idResponsableMovimiento.",
						".$filaAfectacion[0].",".$filaAfectacion[2].",".$idFormulario.",".$idRegistro.",".$idProceso.",'".$complementario."',
						".$obj->tipoMovimiento.",".$obj->tipoPresupuesto.",'".$descripcionMov."','".$folio."','".$noMovimiento."','".
						$obj->ruta."','".$obj->horaMovimiento."')";
			$x++;			
			
		}
		$consulta[$x]="commit";
		$x++;
		return $con->ejecutarBloque($consulta);
	}
	
	function registrarAfectacionesPresupuestales($arrObj,&$consulta=null,&$ct=null)
	{
		global $con;
		$x=0;
		if($consulta==null)
		{
			$consulta[$x]="begin";
			$x++;
		}
		else
			$x=$ct;
		foreach($arrObj as $obj)
		{
			$arrAsientos=array();
			if(!isset($obj->arrAsientos))
			{
			
				$query="SELECT cmbTiempoPresupuestal,txtProcentajeAfectacion,cmbTipoAfectacion FROM  _935_tablaDinamica WHERE cmbSituacion=1 AND idReferencia=".$obj->tipoMovimiento;
				$resAfectacion=$con->obtenerFilas($query);
				while($filaAfectacion=mysql_fetch_row($resAfectacion))
				{
					$objDatos=array();
					$objDatos[0]=$filaAfectacion[0];
					$objDatos[1]=$filaAfectacion[1];
					$objDatos[2]=$filaAfectacion[2];
					//echo "(".$filaAfectacion[1]."/100)*".$obj->montoMovimiento."=".(($filaAfectacion[1]/100)*$obj->montoMovimiento)."<br>";
					$objDatos[3]=($filaAfectacion[1]/100)*$obj->montoMovimiento;
					if($objDatos[3]!=0)
						array_push($arrAsientos,$objDatos);
				}
			}
			else
				$arrAsientos=$obj->arrAsientos;
			$folio="";
			if(!isset($obj->folio))
				$folio=$this->obtenerFolio($obj);
			else
				$folio=$obj->folio;
			$descripcionMov=$this->arrDescripcionConceptos[$obj->tipoMovimiento];
			foreach($arrAsientos as $filaAfectacion)
			{
				$noMovimiento=$this->obtenerNoMovimiento($obj);
				$idFormulario="NULL";
				if(isset($obj->idFormulario)&&($obj->idFormulario!=""))
					$idFormulario=$obj->idFormulario;
				$idRegistro="NULL";
				if(isset($obj->idRegistro)&&($obj->idRegistro!=""))
					$idRegistro=$obj->idRegistro;
				$idProceso="NULL";
				if(isset($obj->idProceso)&&($obj->idProceso!=""))
					$idProceso=$obj->idProceso;
				$complementario="";
				if(isset($obj->complementario))
					$complementario=$obj->complementario;
				$mes=date("m",strtotime($obj->fechaMovimiento));
				if(isset($obj->mes))
					$mes=$obj->mes;
				$consulta[$x]="INSERT INTO 528_asientosCuentasPresupuestales(ciclo,programa,departamento,capitulo,partida,mes,monto,
							fechaOperacion,responsableOperacion,tiempoPresupuestal,operacion,idFormulario,idReferencia,idProceso,complementario,
							origenMovimiento,fuenteFinanciamiento,descripcionMovimiento,folioMovimiento,noMovimiento,ruta,horaMovimiento)
							VALUES(".$obj->idCiclo.",".$obj->idPrograma.",'".$obj->departamento."','".$obj->capitulo."','".$obj->partida."',".
							$mes.",".$filaAfectacion[3].",'".$obj->fechaMovimiento."',".$obj->idResponsableMovimiento.",
							".$filaAfectacion[0].",".$filaAfectacion[2].",".$idFormulario.",".$idRegistro.",".$idProceso.",'".$complementario."',
							".$obj->tipoMovimiento.",".$obj->tipoPresupuesto.",'".$descripcionMov."','".$folio."','".$noMovimiento."','".
							$obj->ruta."','".$obj->horaMovimiento."')";
				$x++;			
				
			}
		}
		
		if($ct!=null)
		{
			$ct=$x;
		}
		else
		{
			$consulta[$x]="commit";
			$x++;
			return $con->ejecutarBloque($consulta);
		}
	}
	
	function obtenerFolio($obj)
	{
		global $con;
		$x=0;
		$consulta="begin";
		if($con->ejecutarConsulta($consulta))
		{
			
			$consulta="SELECT txtPrefijo,txtSeparador,txtLongitud,txtIncremento,txtfolio,id__933_tablaDinamica FROM _933_tablaDinamica WHERE idReferencia=".$obj->tipoMovimiento." AND cmbSituacion=1 FOR update";

			$fila=$con->obtenerPrimeraFila($consulta);
			if($fila)
			{
				$consulta="update _933_tablaDinamica set txtfolio=txtfolio+1 where id__933_tablaDinamica=".$fila[5];
				if($con->ejecutarConsulta($consulta))
				{
					$folio=$fila[0].$fila[1].str_pad($fila[4],$fila[2],"0",STR_PAD_LEFT);
					$consulta="commit";
					if($con->ejecutarConsulta($consulta))
						return $folio;

				}
			}
		}
		return false;
	}
	
	function obtenerFolioSiguiente($tipoMovimiento)
	{
		global $con;
		$x=0;
		$consulta="begin";
		if($con->ejecutarConsulta($consulta))
		{
			
			$consulta="SELECT txtPrefijo,txtSeparador,txtLongitud,txtIncremento,txtfolio,id__933_tablaDinamica FROM _933_tablaDinamica WHERE idReferencia=".$tipoMovimiento." AND cmbSituacion=1 FOR update";

			$fila=$con->obtenerPrimeraFila($consulta);
			if($fila)
			{
				$consulta="update _933_tablaDinamica set txtfolio=txtfolio+1 where id__933_tablaDinamica=".$fila[5];
				if($con->ejecutarConsulta($consulta))
				{
					$folio=$fila[0].$fila[1].str_pad($fila[4],$fila[2],"0",STR_PAD_LEFT);
					$consulta="commit";
					if($con->ejecutarConsulta($consulta))
						return $folio;

				}
			}
		}
		return false;
	}
	
	function obtenerNoMovimiento($obj)
	{
		global $con;
		$x=0;
		$consulta="begin";
		if($con->ejecutarConsulta($consulta))
		{
			$consulta="SELECT noMovimientoActual FROM 558_conteoNoMovimientoPresupuesto WHERE cicloFiscal=".$obj->idCiclo." AND codigoInstitucion='".$_SESSION["codigoInstitucion"]."' for update";
			$fila=$con->obtenerPrimeraFila($consulta);
			if($fila)
			{
				$consulta="update 558_conteoNoMovimientoPresupuesto set noMovimientoActual=noMovimientoActual+1 where cicloFiscal=".$obj->idCiclo." AND codigoInstitucion='".$_SESSION["codigoInstitucion"]."'";
				if($con->ejecutarConsulta($consulta))
					return $fila[0];
			}
			else
			{
				$consulta="INSERT INTO 558_conteoNoMovimientoPresupuesto(noMovimientoActual,cicloFiscal,codigoInstitucion) VALUES(2,".$obj->idCiclo.",'".$_SESSION["codigoInstitucion"]."')";
				if($con->ejecutarConsulta($consulta))
					return 1;
			}
		}
		return "0";
	}
	
	function obtenerSaldoPresupuesto($tPresupuestal,$dimensiones)
	{
		global $con;
		$saldo=0;
		$listAsientos="";
		$arrIdAsientos=array();
		$tDimensiones=sizeof($dimensiones);
		foreach($dimensiones as $d=>$valor)
		{
			$idDimension=-1;
			if(isset($this->arrDimensiones[$d]))
				$idDimension=$this->arrDimensiones[$d][0];
			if($idDimension!=-1)
			{
				$consulta="select idAsiento FROM 571_detallesAsientoPresupuestal WHERE idDimension=".$idDimension." AND valorCampo='".$valor."'";
				
				$res=$con->obtenerFilas($consulta);
				while($f=mysql_fetch_row($res))
				{
					if(!isset($arrIdAsientos[$f[0]]))
						$arrIdAsientos[$f[0]]=1;
					else
						$arrIdAsientos[$f[0]]++;
				}
			}
		}
		
		if(sizeof($arrIdAsientos)>0)
		{
			foreach($arrIdAsientos as $idAsiento=>$nElementos)
			{
				if($tDimensiones==$nElementos)
				{
					if($listAsientos=="")
						$listAsientos=$idAsiento;
					else
						$listAsientos.=",".$idAsiento;
				}
			}
		}
		
		if($listAsientos=="")
			$listAsientos=-1;
		$consulta="SELECT SUM(montoOperacion * tipoOperacion) FROM 570_asientosPresupuestales WHERE idAsientoPresupuestal IN (".$listAsientos.") AND idTiempoPresupuestal=".$tPresupuestal;
		$saldo=$con->obtenerValor($consulta);
		if($saldo=="")
			$saldo=0;
		return $saldo;
	}
			
	function obtenerTotalPresupuestoDimensiones($dimensiones)
	{
		global $con;
		$consulta="SELECT idTiempoPresupuestal FROM 524_tiemposPresupuestales WHERE situacion=1";
		$res=$con->obtenerFilas($consulta);
		$totalPresupuesto=0;
		while($f=mysql_fetch_row($res))
		{
			$totalPresupuesto+=obtenerSaldoPresupuesto($f[0],$dimensiones);
		}
		return $totalPresupuesto;
	}
	
	function obtenerSituacionPresupuestoDimensiones($dimensiones)
	{
		global $con;
		$arrTiemposPresupuestales=array();
		$consulta="SELECT idTiempoPresupuestal,nombreTiempo FROM 524_tiemposPresupuestales WHERE situacion=1";
		$res=$con->obtenerFilas($consulta);
		$totalPresupuesto=0;
		while($f=mysql_fetch_row($res))
		{
			$arrTiemposPresupuestales[$f[1]]["tiempoPresupuestal"]=$f[1];
			$arrTiemposPresupuestales[$f[1]]["saldo"]=obtenerSaldoPresupuesto($f[0],$dimensiones);
		}
		return $arrTiemposPresupuestales;
	}
	
	function existeSuficienciaPresupuestal($tPresupuestal,$dimensiones,$monto)
	{
		$saldo=obtenerSaldoPresupuesto($tPresupuestal,$dimensiones);
		if($saldo>$monto)
			return 0;
		else
			return $monto-$saldo;
	}
	
}

/*{
	"fechaMovimiento":"",
	"idResponsableMovimiento":"",
	"montoMovimiento":"",
	"tipoMovimiento":"",
	"idPrograma":"",
	"ruta":"",
	"horaMovimiento":"",
	"idCiclo":"",
	"departamento":"",
	"capitulo":"",
	"partida":"",
	"tipoPresupuesto":""
					
	
}*/



?>
