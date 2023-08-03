<?php
	function inicializarMenuRenderer($menu,$tipoMenu,$nElemento)
	{
		
		$estilo="";
		if($tipoMenu==1)
			$estilo='border-top-style:none';
		
		echo '<div class="menuStandart content_right">'.
			'<ul id="nav'.$nElemento.'"name="menuSistema" tipo="'.$tipoMenu.'">';
		echo '<li class="menu_title">'.$menu["titulo"].'</li>';
		echo generarOpcionesMenuMatic($menu["opciones"]);
		echo "</ul></div>";
	}
	
	function generarOpcionesMenuMatic($arrOpciones)
	{

		if(sizeof($arrOpciones)>0)
		{
			
			foreach($arrOpciones as $opt)	
			{
				$img="";
				if(($opt["bullet"]!="")&&($opt["bullet"]!="NULL"))
					$img='<img src="../media/verBullet.php?id='.$opt["idOpcion"].'">&nbsp;';
					
				if(strpos($opt["url"],"?idFormulario")!==false)	
				{
					$arrDatos=explode("?idFormulario=",$opt["url"]);
					$opt["url"]="javascript:abrirPaginaIframe('".$arrDatos[0]."',".$arrDatos[1].")";
				}
				
				if(strpos($opt["url"],"ingresarProceso(")!==false)	
				{
					$opt["url"]=str_replace("ingresarProceso(","ingresarProcesoIframe(",$opt["url"]);
				}
					
				echo '<li class="menu_link"><table><tr><td width="20">'.$img.'</td><td><a href="'.$opt["url"].'" >'.$opt["texto"].'</a></td></tr></table>';
				echo "</li>";
			}

		}
	}
?>