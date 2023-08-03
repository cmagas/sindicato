// JavaScript Document
CKEDITOR.plugins.add('pReprocesar',	{
											init:	function(editor)
													{
														var pluginName='pReprocesar';
														editor.ui.addButton	('pReprocesar',
																				{
																					label:'Reprocesar',
																					command:'reprocesarDiligenciaEditor',
																					icon:CKEDITOR.plugins.getPath('pReprocesar')+'arrow_refresh.png'
																				}
																			)
																			
														var cmd=editor.addCommand('reprocesarDiligenciaEditor',{exec: reprocesarDiligenciaEditor});
													}	
										}
				);

function reprocesarDiligenciaEditor(e)
{
	
	generarExposicionDiligencia();
	
	
}