<?php
	function generarListadoTareas($idFormulario,$idRegistro)
	{
		global $con;	
		?>
        
        <table>
        	<tr>
            	<td width="200" align="center">
                <span class="letraRojaSubrayada8">
            	Persona asistente
                </span>
                </td>
                <td width="120" align="center">
                <span class="letraRojaSubrayada8">
            	Evaluación tarea
                </span>
                </td>
                <td width="90" align="center">
                <span class="letraRojaSubrayada8">
            	Arch. tarea 1
                </span>
                </td>
                <td width="90" align="center">
                <span class="letraRojaSubrayada8">
            	Arch. tarea 2
                </span>
                </td>
                <td width="90" align="center">
                <span class="letraRojaSubrayada8">
            	Arch. tarea 3
                </span>
                </td>
            </tr>
            <?php
				$consulta="SELECT u.Nombre,(SELECT contenido FROM 902_opcionesFormulario WHERE idGrupoElemento=2672 AND valor=1=t.cmbEvaluacionTarea) AS evaluacion,
							txtAnexoTarea,txtAnexoTarea2,txtAnexoTarea3,u.idUsuario FROM _304_tablaDinamica t,800_usuarios u WHERE idReferencia=".$idRegistro." AND u.idUsuario=t.cmbParticipante ORDER BY u.Nombre";
				$resTareas=$con->obtenerFilas($consulta);
				while($fila=mysql_fetch_row($resTareas))
				{
			?>
            	<tr>
                	<td align="left">
                    <span class="letraExt">
                    <a href="javascript:verUsrNuevaPagina('<?php echo bE($fila[5])?>')">
                    <?php
						echo $fila[0];
					?>
                    </a>
                    </span>
                    </td>
                    <td align="center">
                    <span class="letraExt">
                    <?php
						echo $fila[1];
					?>
                    </span>
                    </td>
                    <td align="center">
                    <span class="letraExt">
                    <?php
						if($fila[2]!="")
							echo '<a href="../paginasFunciones/obtenerArchivos.php?id='.bE($fila[2]).'"><img src="../images/download.png" title="Descargar archivo" alt="Descargar archivo"></a>';
					?>
                    </span>
                    </td>
                    <td align="center">
                    <span class="letraExt">
                    <?php
						if($fila[3]!="")
							echo '<a href="../paginasFunciones/obtenerArchivos.php?id='.bE($fila[3]).'"><img src="../images/download.png" title="Descargar archivo" alt="Descargar archivo"></a>';
					?>
                    </span>
                    </td>
                    <td align="center">
                    <span class="letraExt">
                    <?php
						if($fila[4]!="")
							echo '<a href="../paginasFunciones/obtenerArchivos.php?id='.bE($fila[4]).'"><img src="../images/download.png" title="Descargar archivo" alt="Descargar archivo"></a>';
					?>
                    </span>
                    </td>
                </tr>
            <?php	
				}
			?>
        </table>
        
        <?php
	}
	
	function generarListadoAsistentes($idFormulario,$idRegistro)
	{
		global $con;	
		?>
        
        <table>
        	<tr>
            	<td width="200" align="center">
                <span class="letraRojaSubrayada8">
            	Persona asistente
                </span>
                </td>
                
            </tr>
            <?php
				$consulta="SELECT u.Nombre,u.idUsuario FROM _295_gridListaAsistencia g,800_usuarios u WHERE idReferencia=".$idRegistro." AND u.idUsuario=g.personaAsistente order by Nombre";
				$resTareas=$con->obtenerFilas($consulta);
				while($fila=mysql_fetch_row($resTareas))
				{
			?>
            	<tr>
                	<td align="left">
                    <span class="letraExt">
                    <a href="javascript:verUsrNuevaPagina('<?php echo bE($fila[1])?>')">
                    <?php
						echo $fila[0];
					?>
                    </a>
                    </span>
                    </td>
                </tr>
            <?php	
				}
			?>
        </table>
        
        <?php
	}
	
?>