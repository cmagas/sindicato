<?php
	class cTTS
	{
	
		function convertirTextoToAudio($texto)
		{
			global $baseDir;	
			
			
			
			$textoFinal=strip_tags($texto);
			
			$textoFinal=str_replace("\r"," ",$textoFinal);
			$textoFinal=str_replace("\n"," ",$textoFinal);
			$archivoTemporal=generarNombreArchivoTemporal();
			$f=fopen($baseDir."/archivosTemporales/".$archivoTemporal,"w");
			if($f)
			{
				fwrite($f,$textoFinal);
				fclose($f);
				
				$comando=$baseDir.'/tts/textoToVoz.sh < '.$baseDir."/archivosTemporales/".$archivoTemporal.' > '.$baseDir."/archivosTemporales/".$archivoTemporal.'.wav';
				shell_exec($comando);
				if(file_exists($baseDir."/archivosTemporales/".$archivoTemporal.'.wav'))
				{
					return $archivoTemporal;
				}
				
				return false;
			}
			return false;
			
		}
			
	}
?>