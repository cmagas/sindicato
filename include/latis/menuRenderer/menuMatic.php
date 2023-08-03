<?php
	function inicializarMenuRenderer($menu,$tipoMenu,$nElemento)
	{
		
		$estilo="";
		if($tipoMenu==1)
			$estilo='border-top-style:none';
		
		echo '<ul id="nav'.$nElemento.'" class="nav"  name="menuSistema" tipo="'.$tipoMenu.'">';
		echo '<li style="'.$estilo.'"><a href="#" >'.$menu["titulo"].'</a>';
		echo generarOpcionesMenuMatic($menu["opciones"]);
		echo "</li></ul>";
	}
	
	function generarOpcionesMenuMatic($arrOpciones)
	{
		if(sizeof($arrOpciones)>0)
		{
			echo '<ul>';
			foreach($arrOpciones as $opt)	
			{
				echo '<li><a href="'.$opt["url"].'" >'.$opt["texto"].'</a>';
				echo generarOpcionesMenuMatic($opt["opciones"]);
				echo "</li>";
			}
			echo '</ul>';
		}
	}
?>