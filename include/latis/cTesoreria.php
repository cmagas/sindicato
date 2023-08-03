<?php
	class cTesoreria
	{
		function cTesoreria()
		{
		}
		
		/*Objeto que recibe
		{
			"tipoAdeudo":"",
			"idReferencia":"",
			"subtotal":"",
			"iva":"",
			"total":"",
			"tipoCliente":"",
			"idCliente":"",
			"fechaVencimiento":"",
			"naturalezaAdeudo":""  //0 egreso; 1 Ingreso
			
		}
		*/
		
		function registrarAdeudo($oAdeudo)//
		{
			global $con;
			$x=0;
			$consulta[$x]="begin";
			$x++;
			
			$consulta[$x]="INSERT INTO 6942_adeudos(tipoAdeudo,idReferencia,fechaCreacion,subtotal,iva,total,tipoCliente,idCliente,situacion,fechaVencimiento,naturalezaAdeudo)
						VALUES(".$oAdeudo->tipoAdeudo.",".$oAdeudo->idReferencia.",'".date("Y-m-d H:i:s")."',".$oAdeudo->subtotal.",".$oAdeudo->iva.",".$oAdeudo->total.
						",".$oAdeudo->tipoCliente.",".$oAdeudo->idCliente.",1,'".$oAdeudo->fechaVencimiento."',".(!isset($oAdeudo->naturalezaAdeudo)?"0":$oAdeudo->naturalezaAdeudo).")";
			$x++;
			$consulta[$x]="set @idAdeudo:=(select last_insert_id())";
			$x++;
			$consulta[$x]="commit";
			$x++;
			
			if($con->ejecutarBloque($consulta))
			{
				$query="select @idAdeudo";
				$idAdeudo=$con->obtenerValor($query);	
				return $idAdeudo;
			}
			
			return -1;
		}
		
		/*
		{
			"montoAbono":"",
			"idAdeudo":"",
			"formaPago":"",
			"datosComp":"",
			"idCaja":"",
			"tipoOperacion":"",//1 ingreso, -1 Egreso
			"comentarios":"",
			"cobrado":"",//opcional,
			"fechaCobro":"",//opcional
			"idComprobante":""
			
		}
		*/
		
		function registrarAbonoAdeudo($objAbono)
		{
			global $con;
			
			
			$x=0;
			$consulta[$x]="begin";
			$x++;
			
			$query="SELECT subtotal,iva,total FROM 6942_adeudos WHERE idAdeudo=".$objAbono->idAdeudo;
			$fAdeudo=$con->obtenerPrimeraFila($query);
			
			$ivaAdeudo=$fAdeudo[1];
			$totalAdeudo=$fAdeudo[2];
			
			
			$query="select sum(montoAbono) from 6936_controlPagos where idAdeudo=".$objAbono->idAdeudo." and cobrado=1";
			$montoAbonado=$con->obtenerValor($query);	
			if($montoAbonado=="")
				$montoAbonado=0;
			$saldo=$totalAdeudo-$montoAbonado;
	
			
			$cobrado=1;
			if(isset($objAbono->cobrado))
			{
				$cobrado=$objAbono->cobrado;
			}
			
			
			if($cobrado==1)
				$saldoVirtual=$saldo-$objAbono->montoAbono;
			else
				$saldoVirtual=$saldo;
				
			
			
			
			$porcIva=$ivaAdeudo/$totalAdeudo;
			
			$subtotal=0;
			$iva=0;
			
			if($saldoVirtual<=0)
			{
				$query="select sum(iva) from 6936_controlPagos where idAdeudo=".$objAbono->idAdeudo." and cobrado=1";
				$totalIVA=$con->obtenerValor($query);
				$diferenciaIVA=$ivaAdeudo-$totalIVA;
				$iva=$diferenciaIVA;
				$subtotal=$montoAbonado-$iva;
				$consulta[$x]="UPDATE 6942_adeudos SET situacion=2 WHERE idAdeudo=".$objAbono->idAdeudo;
				$x++;
			}
			else
			{
	
				$iva=str_replace(",","",number_format($objAbono->montoAbono*$porcIva,2));	
	
				$subtotal=$objAbono->montoAbono-$iva;
			}
			$folioAbono=generarNombreArchivoTemporal(1,"");
			
			
			$consulta[$x]=convertirObjInsert("6936_controlPagos",$objAbono,"fechaAbono,horaAbono,idResponsableCobro,subtotal,iva,folioAbono","'".date("Y-m-d").
							"','".date("H:i:s")."',".$_SESSION["idUsr"].",".$subtotal.",".$iva.",'".$folioAbono."'");
			$x++;
			
			/*$consulta[$x]="INSERT INTO (montoAbono,fechaAbono,idAdeudo,formaPago,horaAbono,idResponsableCobro,idCaja,folioAbono)
							VALUES(".$obj->montoAbonado.",'".date("Y-m-d")."',@idAdeudo,1,'".date("H:i:s")."',".$_SESSION["idUsr"].",0,'".$folioAbono."')";
			$x++;*/
			
			
			$consulta[$x]="commit";
			$x++;
			
			if($con->ejecutarBloque($consulta))
			{
				$query="select last_insert_id()";
				$idAbono=$con->obtenerValor($query);	
				return $idAbono;
				
			}
			return -1;
			
			
		}		
		
		/*
		{
			"tipoAdeudo":"",
			"idReferencia":""
		}
		*/
		
		function cancelarAdeudo($oAdeudo)
		{
			global $con;
			$consulta="UPDATE 6942_adeudos SET situacion=3 WHERE tipoAdeudo=".$oAdeudo->tipoAdeudo." AND idReferencia=".$oAdeudo->idReferencia;
			return eC($consulta);
			
		}
		
		function verificarSituacionAdeudo($idAdeudo)
		{
			global $con;
			
			
			$query="SELECT subtotal,iva,total FROM 6942_adeudos WHERE idAdeudo=".$idAdeudo;
			$fAdeudo=$con->obtenerPrimeraFila($query);
			
			
			$totalAdeudo=$fAdeudo[2];
			
			$query="select sum(montoAbono) from 6936_controlPagos where idAdeudo=".$idAdeudo." and cobrado=1";
			$montoAbonado=$con->obtenerValor($query);	
			
			
			$diferencia=$totalAdeudo->$montoAbonado;
			if($diferencia<=0)
			{
				$query="UPDATE 6942_adeudos SET situacion=2 WHERE idAdeudo=".$idAdeudo;
				return $con->ejecutarConsulta($query);
			}
			
			
			return true;
		}
	
		
		/*
			{
				"idMotivoCancelacion":"",  		//opcional
				"comentariosAdicionales":"",	//opcional			
				"complementario1":"",			//opcional
				"complementario2":"",			//opcional
				"complementario3":""			//opcional
			}
		*/
		
		function registrarCancelacionOperacion($oCancelacion)
		{
			global $con;
			$consulta=convertirObjInsert("6933_cancelacionOperaciones",$oCancelacion,"fechaCancelacion,idResponsableCancelacion","'".date("Y-m-d H:i:s")."',".$_SESSION["idUsr"]);
			if($con->ejecutarConsulta($consulta))
			{
				$consulta="select last_insert_id()";
				$idCancelacion=$con->obtenerValor($consulta);
				
				return $idCancelacion;
				
			}
			
			return -1;
			
			
			
		}
	}
?>