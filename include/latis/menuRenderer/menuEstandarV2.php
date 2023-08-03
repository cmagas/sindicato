<?php
	function inicializarMenuRenderer($menu,$tipoMenu,$nElemento)
	{
		
		if($tipoMenu==1)
		{
			echo '<li><a href="#" ></a>';
			echo generarOpcionesMenuCensida($menu["opciones"]);
			echo "</li>";
		
		}
		else
		{
			/*echo '<div class="menuCensida">'.
				'<ul id="nav'.$nElemento.'"name="menuSistema" tipo="'.$tipoMenu.'">';
			echo '<li class="menu_title">'.$menu["titulo"].'</li>';
			echo generarOpcionesMenuCensidaVertical($menu["opciones"]);
			echo "</ul></div>";*/
			
			echo '<div class="cabeceraMenu" id="'.$nElemento.'"><table width="100%"><tr><td class="menu_title" style="width:20px;text-align: left;vertical-align:middle important; "><img id="img_'.$nElemento.'" class="cImagenClosed"></td><td class="menu_title">'.$menu["titulo"]."</td></tr></table><div style='display:none'>";
			echo generarOpcionesMenuCensidaVertical($menu["opciones"]);
			echo "</div></div>";
			
		}
	}
	
	function generarOpcionesMenuCensida($arrOpciones)
	{

		if(sizeof($arrOpciones)>0)
		{
			//if(sizeof($arrOpciones)>5)
			{
				echo '<div class="sf-mega"><div><table>';
				$nOpcion=1;
				foreach($arrOpciones as $opt)	
				{
					if($nOpcion==1)
					{
						echo '<tr>';
					}
					echo '<td class="tdMenu"><a href="'.$opt["url"].'" >'.$opt["texto"].'</a>';
//					echo generarOpcionesMenuMatic($opt["opciones"]);
					echo "</td>";
					$nOpcion++;
					if($nOpcion>6)
					{
						echo "</tr>";
						$nOpcion=1;
					}
				}
				
				
				echo '</table></div></div>';
			}
			/*else
			{
				echo '<ul>';
				foreach($arrOpciones as $opt)	
				{
					echo '<li><a href="'.$opt["url"].'" >'.$opt["texto"].'</a>';
					echo generarOpcionesMenuCensida($opt["opciones"]);
					echo "</li>";
				}
				echo '</ul>';
			}*/
		}
	}
	
	
	function generarOpcionesMenuCensidaVertical($arrOpciones)
	{
	
		foreach($arrOpciones as $opt)	
		{
			$img="";
			if(($opt["bullet"]!="")&&($opt["bullet"]!="NULL"))
				$img='<img src="../media/verBullet.php?id='.$opt["idOpcion"].'">&nbsp;';
				
			if(strpos($opt["url"],"?idFormulario")!==false)	
			{
				$arrDatos=explode("?idFormulario=",$opt["url"]);
				$opt["url"]="javascript:abrirPaginaIframe('../modeloProyectos/visorRegistrosProcesosV2.php',".$arrDatos[1].")";
			}
			
			if(strpos($opt["url"],"ingresarProceso(")!==false)	
			{
				$opt["url"]=str_replace("ingresarProceso(","ingresarProcesoIframeV2(",$opt["url"]);
			}
				
			echo '<div class="menu_link"><table><tr><td width="20">'.$img.'</td><td><a href="'.$opt["url"].'" >'.$opt["texto"].'</a></td></tr></table>';
			echo "</div>";
		}
	}
	
?>