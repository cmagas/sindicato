// JavaScript Document
CKEDITOR.plugins.add('pInsertParametroPlantilla',	{
														init:	function(editor)
																{
																	var pluginName='pInsertParametroPlantilla';
																	editor.ui.addButton	('pInsertParametroPlantilla',
																							{
																								label:'Insertar p&aacute;metro',
																								command:'insertPlantilla',
																								icon:CKEDITOR.plugins.getPath('pInsertParametroPlantilla')+'PAutomatico.gif'
																							}
																						)
																						
																	var cmd=editor.addCommand('insertPlantilla',{exec: insertPlantillaEditor});
																}	
													}
							);

function insertPlantillaEditor(e)
{
	
	mostrarVentanaInsertParametroPlantilla(e);
	
}