<?php
	include_once("latis/classCodigoBarras/phpqrcode/qrlib.php"); 
	class qr
	{
		function qr()//corrcion de rrores L,M,Q,H
		{
			
		}
		
		function generarCodigoBarrasImagenArchivo($valor,$archivo,$tArchivo=1,$ECC="M",$tamPixelesCuadro=4,$margen=2)
		{
			
			switch($tArchivo)
			{
				case 1:
					QRcode::png($valor,$archivo,$ECC, $tamPixelesCuadro, $margen);
				break;
				case  2:
					QRcode::jpg($valor,$archivo,$ECC, $tamPixelesCuadro, $margen);
				break;
			}
			
		}
		
		function generarCodigoBarraDatosImagen($valor,$tArchivo=1,$ECC="M",$tamPixelesCuadro=4,$margen=2)
		{
			switch($tArchivo)
			{
				case 1:
					header('Content: image/png');
					QRcode::png($valor,false,$ECC, $tamPixelesCuadro, $margen);
				break;
				case  2:
					header('Content: image/jpg');
					QRcode::jpg($valor,false,$ECC, $tamPixelesCuadro, $margen);
				break;
			}
			
		}
	}
?>