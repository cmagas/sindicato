<?php 	
	include_once("latis/conexionBD.php");
	include_once("latis/funcionesFormularios.php");
	include_once("latis/funcionesExportacion.php");

class cProcesosLatis
{
	var $idProceso;
	var $idRegistro;
	function cProcesosLatis($idProceso=-1,$idRegistro=-1)
	{
		$this->idProceso=$idProceso;
		$this->idRegistro=$idRegistro;
	}
	
	function obtenerDatosExportacionRegistro($idFormulario,$idRegistro)
	{
		global $con;
		$consulta="SELECT nombreFormulario,tipoFormulario,titulo FROM 900_formularios WHERE idFormulario=".$idFormulario;
		$filaFormulario=$con->obtenerPrimeraFila($consulta);
		$nFormulario=$filaFormulario[0];
		$tipoFormulario=$filaFormulario[1];
		$cadObj="";
		$consulta="SELECT etiquetaExportacion,idGrupoElemento,tipoElemento,nombreCampo FROM 901_elementosFormulario WHERE idFormulario=".$idFormulario." AND etiquetaExportacion IS NOT NULL AND etiquetaExportacion<>'' ";
		$res=$con->obtenerFilas($consulta);
		$arrCampos="";
		$cabeceraPrimeraFila=0;
		while($fila=mysql_fetch_row($res))
		{

			$valorCampo=obtenerValorControlFormularioBase($fila[1],$idRegistro);
			$camposHijos="";
			switch($fila[2])
			{
				case 17:
				case 18:
				case 19:
					foreach($valorCampo as $f)
					{
						$obj="";
						$nCol=0;
						foreach($f as $v)
						{
							if($nCol>0)
							{
								if($obj=="")
									$obj="'".$v."'";
								else
									$obj.=",'".$v."'";
							}
							$nCol++;
						}
						$obj="[".$obj."]";
						if($camposHijos=="")
							$camposHijos=$obj;
						else
							$camposHijos.=",".$obj;
					}
					$valorCampo="";
					$camposHijos="[".$camposHijos."]";
				break;
			}
			$valorCampo=str_replace("\r","<br>",str_replace("\t","",trim($valorCampo)));
			$campo='{"etiquetaExportacion":"'.$fila[0].'","valor":"'.cv(str_replace('"','\"',($valorCampo))).'","nombreCampo":"'.$fila[3].'","tipoCampo":"'.$fila[2].'","camposHijos":"'.$camposHijos.'","cabeceraPrimeraFila":"'.$cabeceraPrimeraFila.'"}';
			
			if($arrCampos=="")
				$arrCampos=$campo;
			else
				$arrCampos.=",".$campo;
		}
		$cadObj='{"campos":['.$arrCampos.']}';
		return $cadObj;
		
	}
	
	function obtenerDatosExportacionModuloPredefinido($idFormulario,$idFormularioBase,$idRegistro)
	{
		global $con;
		$cadObj="";
		$consulta="SELECT titulo FROM 900_formularios WHERE idFormulario=".$idFormulario;
		$filaFormulario=$con->obtenerPrimeraFila($consulta);
		$consulta="SELECT funcionDTDXML FROM 200_modulosPredefinidosProcesos WHERE idGrupoModulo=".$filaFormulario[0];
		$funcionExportacion=$con->obtenerValor($consulta);
		if($funcionExportacion!="")
		{
			eval('$cadObj='.$funcionExportacion.'('.$idFormularioBase.','.$idRegistro.');');
		}
		return $cadObj;
		
	}
	
	function obtenerDatosExportacionProcesoRegistro()
	{
		global $con;
		if(($this->idProceso==-1)||($this->idRegistro==-1))
			return "";
		$consulta="SELECT idFormulario FROM 203_elementosDTD WHERE idProceso=".$this->idProceso." ORDER BY orden ";
		$cadObj="";
		$arrSecciones="";
		$res=$con->obtenerFilas($consulta);
		$idFormularioBase=obtenerFormularioBase($this->idProceso);
		
		$consulta="SELECT nombreFormulario,titulo,tipoFormulario,idFormulario FROM 900_formularios WHERE idFormulario=".$idFormularioBase;
		$filaForm=$con->obtenerPrimeraFila($consulta);
		$obj=$this->obtenerDatosExportacionRegistro($idFormularioBase,$this->idRegistro);
		$arrSecciones='{"seccion":"'.$filaForm[0].'","idFormulario":"'.$idFormularioBase.'","instancias":['.$obj.']}';

		while($fila=mysql_fetch_row($res))
		{
			$consulta="SELECT nombreFormulario,titulo,tipoFormulario FROM 900_formularios WHERE idFormulario=".$fila[0];
			$filaForm=$con->obtenerPrimeraFila($consulta);
			if($filaForm[2]==0)
			{

				$consulta="select id__".$fila[0]."_tablaDinamica from _".$fila[0]."_tablaDinamica where idReferencia=".$this->idRegistro;
				$resRegistros=$con->obtenerFilas($consulta);
				$arrInstancias="";
				while($filaReg=mysql_fetch_row($resRegistros))
				{
					$instancia=$this->obtenerDatosExportacionRegistro($fila[0],$filaReg[0]);
					if($arrInstancias=="")
						$arrInstancias=$instancia;
					else
						$arrInstancias.=",".$instancia;
						
				}
				$arrSecciones.=',{"seccion":"'.$filaForm[0].'","idFormulario":"'.$fila[0].'","instancias":['.$arrInstancias.']}';
			}
			else
			{
				
				$cadComp=$this->obtenerDatosExportacionModuloPredefinido($fila[0],$idFormularioBase,$this->idRegistro);
				if($cadComp!="")
					$arrSecciones.=",".str_replace("@idFormulario",$fila[0],str_replace("@nFormulario",$filaForm[0],$cadComp));
			}
		}
		return '{"arrSecciones":['.$arrSecciones.']}';
	}
}

?>