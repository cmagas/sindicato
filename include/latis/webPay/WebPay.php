<?php


//phpinfo();

//error_reporting(E_ALL);
//ini_set('display_errors', '1');

include_once("latis/webPay/AESCrypto.php"); 

//$url = WebPay::getWebPayURL("C", 65, "$webpay_proxy_url/webpay/RespuestaPagoPaqueteUT.php", 12121212121);
//print($url);

class WebPay {


    
	/*private static $cadena_key="5DCC67393750523CD165F17E1EFADD21";
	private static $baseURL = "https://wppsandbox.mit.com.mx/gen";
	private static $id_company='SNBX';
	private static $id_branch='01SNBXBRNCH';
	private static $user='SNBXUSR01';
	private static $pwd='SECRETO';
	private static $data0='SNDBX123';*/
    
	private static $cadena_key="4401F576F47C7B3C1533FAE7F81675A7";
	private static $baseURL = "https://bc.mitec.com.mx/p/gen";
	private static $id_company='3945';
	private static $id_branch='002';
	private static $user='3945SIUS0';
	private static $pwd='575KU9X4UY';
	private static $data0='9265654955';
	
	
    public static function getWebPayURL( $amount, $urlResponse, $referencia )
	{
        

        $xmlc = '<?xml version="1.0" encoding="UTF-8"?>
				<P>
					<business><id_company>'.WebPay::$id_company.'</id_company>
						<id_branch>'.WebPay::$id_branch.'</id_branch>
						<user>'.WebPay::$user.'</user>
						<pwd>'.WebPay::$pwd.'</pwd>
					</business>
					<url>
						<reference>'.$referencia.'</reference>
						<amount>'.$amount.'</amount>
						<moneda>MXN</moneda>
						<canal>W</canal>
						<omitir_notif_default>1</omitir_notif_default>
						<st_correo>0</st_correo>
					</url>
				</P>';

		$xmlc=$encryptedString = AESCrypto::encriptar($xmlc, WebPay::$cadena_key);
	
		$encodedString =("<pgs><data0>".WebPay::$data0."</data0><data>".$xmlc."</data></pgs>");
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,WebPay::$baseURL);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array( 'xml' => $encodedString)));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$originalString = curl_exec ($ch);
		curl_close ($ch);
		
		$decryptedString = AESCrypto::desencriptar($originalString, WebPay::$cadena_key);
		$xml = simplexml_load_string($decryptedString);
		
		//print($xml->nb_url);
        return $xml->nb_url;
    }


    public static function getRespuesta($respuesta)
	{
		
		//print($respuesta);
		
        global $files_dir;
        //Logger::debug($respuesta);
        //$rc4 = new RC4();
        //$xmls= $rc4->Salaa($rc4->HexStringToString($respuesta), WebPay::$semilla);
        //$xmls .= "</xml>";
		
		$decodedString =    $respuesta ;
		
		//$decodedString =  $respuesta ;
		$xmls = AESCrypto::desencriptar($decodedString, WebPay::$cadena_key);
		
		//Logger::debug( htmlentities($xmls));
        try{
			
            $xmle = new SimpleXMLElement(utf8_encode($xmls));
        }  catch (Exception $e){
            $xmle = null;
        }
		
		
        $linea = date("Y-m-d H:i:s") . "\t" . (is_null($xmle)?"":$xmle->reference.'--'.$xmle->response.'--'.$xmle->foliocpagos) . "\t" . $respuesta . "\n";
        $file = $files_dir . "/" . "log/webpay_resp_" . date("Y_m") . ".log";
        $fp = fopen($file,"a");
        fwrite($fp, $linea);
        fclose($fp);

        return $xmle;
    }
	
    public static function decodeHex($respuesta)
	{
        global $files_dir;
        //Logger::debug($respuesta);
        $rc4 = new RC4();
        $xmls= $rc4->Salaa($rc4->HexStringToString($respuesta), WebPay::$semilla);
        $xmls .= "</xml>";
        //Logger::debug( htmlentities($xmls));
        return $xmls;
    }
    
}
?>