<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ReferenciaSantander
 *
 * @author Javier
 */

class ReferenciaSantander 
{

    private static $constante = 2;
    private static $factorFijo = 1;
    private static $factoresImporte = array(7,3,1);
    private static $factoresReferencia=array(11,13,17,19,23);
    private static $anioInicio = 2000;
    private static $factorAnio = 372;
    private static $factorMes = 31;


    public static function generaReferencia( $total, $vigencia , $area , $producto , $unidades , $idSaldo)
	{

            //date_default_timezone_set("America/Mexico_City");
            $vFecha = explode("-", date("Y-n-j", ($idSaldo/1000)+($vigencia*24*60*60)) );

            $fa = ($vFecha[0]-self::$anioInicio) * self::$factorAnio;
            $fm = ($vFecha[1]-1)*self::$factorMes;
            $fd = ($vFecha[2]-1);
            $fecha = $fa + $fm + $fd;

            $total = str_replace(",", "", $total);
            $sTotal = number_format(floatval($total),2);
            $sTotal = str_replace(".", "", $sTotal);
            $sTotal = str_replace(",", "", $sTotal);
            $size = strlen($sTotal);
            $factorActual=0;
            $importe = 0;
            for($i=$size-1; $i>-1; --$i){
                $importe += ((int)substr($sTotal, $i, 1)) * self::$factoresImporte[$factorActual];
                ++$factorActual;
                if($factorActual==3)
                    $factorActual=0;
            }
            $importe = ($importe % 10);

            $referencia = $area . $producto . str_pad($unidades,3,"0",STR_PAD_LEFT) . str_pad($vigencia,4,"0",STR_PAD_LEFT) . $idSaldo . $fecha . $importe . ReferenciaSantander::$constante;
            $size = strlen($referencia);
            $factorActual=0;
            $verificador = 0;
            for($i=$size-1; $i>-1; --$i){
                $verificador += ((int)substr($referencia, $i, 1)) * ReferenciaSantander::$factoresReferencia[$factorActual];
                ++$factorActual;
                if($factorActual==5)
                    $factorActual=0;
            }

            $verificador = ($verificador % 97) + ReferenciaSantander::$factorFijo;
            $verificador = str_pad($verificador, 2, "0",STR_PAD_LEFT);

            $referencia .= $verificador;

            return $referencia;

    }
}
?>
