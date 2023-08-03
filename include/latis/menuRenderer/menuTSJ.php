<?php
	function inicializarMenuRenderer($menu,$tipoMenu,$nElemento)
	{

		if($tipoMenu==1)
		{
			echo '<li><a href="#" >'.$menu["titulo"].'</a>';
			if(isset($menu["opciones"]))
				echo generarOpcionesMenuCensida($menu["opciones"]);
			echo "</li>";
		
		}
		else
		{
			
			
			echo '	<li><a href="#"><span class="iconic"></span> '.$menu["titulo"]."</a>
						<ul>
							";
			if(isset($menu["opciones"]))							
				echo generarOpcionesMenuCensidaVertical($menu["opciones"]);
			echo "		</ul>
					</li>";
			
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
				
			echo '<li style="z-index:1000; display:block"><a href="'.$opt["url"].'" >'.$opt["texto"].'</a>';
			
			if(sizeof($opt["opciones"])>0)
			{
				echo "<ul>";
				echo generarOpcionesMenuCensidaVertical($opt["opciones"]);
				echo "</ul>";
			}
			
			echo '</li>';

		}
	}
	
	
	
?>