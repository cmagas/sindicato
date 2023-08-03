// JavaScript Document
CKEDITOR.plugins.add('pComboMarcador',	{
											init:	function(editor)
													{
														var pluginName='pComboMarcador';
														editor.ui.addRichCombo	('pComboMarcador',
																					{
																						label:'Marcador',
																						title:'Marcar palabra clave',
																						icon:CKEDITOR.plugins.getPath('pSavePDF')+'save.png'
																					}
																				);
																			
														init : function()
																{
																	var opciones = [['1','Palabra clave'],['2','Imputado'],['3','Fecha']];
																	var opcion;
																   
																	this.startGroup( 'Zoom level' );
																	// Loop over the Array, adding all items to the
																	// combo.
																	for ( i = 0 ; i < zoomOptions.length ; i++ )
																	{
																		opcion = opciones[ i ];
																		// value, html, text
																		this.add( opcion[0], opcion[1], opcion[1]);
																	}
																	// Default value on first click
																	
																}
													}	
										}
				);

function funcionAccionComboMarcacion(e)
{
	
	
	
}