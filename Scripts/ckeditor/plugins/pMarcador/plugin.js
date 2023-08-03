
// JavaScript Document
CKEDITOR.plugins.add('pMarcador',	{
											init:	function(editor)
													{
														var pluginName='pMarcador';
														editor.ui.addButton	('pMarcador',
																				{
																					label:'Insertar marcador',
																					command:'insertMarcador',
																					icon:CKEDITOR.plugins.getPath('pMarcador')+'save.png'
																				}
																			)
																			
														var cmd=editor.addCommand('insertMarcador',{exec: insertarMarcador});
													}	
										}
				);

function insertarMarcador(e)
{
	
	var oEditor=CKEDITOR.instances[e.name];
	var txtSelecion = oEditor.getSelection();
	var rangoInicial=txtSelecion.getRanges()[0].startOffset;
	var rangoFinal=txtSelecion.getRanges()[0].endOffset;
	
	
	if(rangoInicial==rangoFinal)
	{
		msgBox('Debe seleccionar el texto al cual desea asociar el marcador');
		return;
	}
	mostrarVentanaTipoMarcadores(oEditor);
}

function registrarMarcador(tipoMarcador,oEditor)
{
	var IDMarcadores=new Date().format('YmdHis_')+Ext.util.Format.number((Math.random()*1000),'0000');
	
	var txtSelecion = oEditor.getSelection();
	
	
	var fragmentoExtraido = txtSelecion.getRanges()[0].extractContents();
	var textoParrafo=fragmentoExtraido.$.textContent;
	var container = CKEDITOR.dom.element.createFromHtml("<marcadorTexto tipoMarcador='"+tipoMarcador+"' idMarcador='marcador_"+IDMarcadores+"'><img src='../images/marcador.png' width='12'>", oEditor.document);
	oEditor.insertElement(container);
	
	
	fragmentoExtraido.appendTo(container)
	var icono2= CKEDITOR.dom.element.createFromHtml("<img src='../images/marcador.png' width='12' >", oEditor.document);
	icono2.appendTo(container);
	
	arrMarcadores.push([IDMarcadores,tipoMarcador,textoParrafo]);
	

}

function mostrarVentanaTipoMarcadores(oEditor)
{
	
	var obj={};
	obj.confVista='<tpl for="."><div class="search-item">{nombre}<br>---</div></tpl>';
	var cmbTipoMarcador=crearComboExt('cmbTipoMarcador',arrTiposMarcadores,240,5,300,obj);
	var form = new Ext.form.FormPanel(	
										{
											baseCls: 'x-plain',
											layout:'absolute',
											defaultType: 'label',
											items: 	[
														{
															x:10,
															y:10,
															html:'Indique el tipo de marcador a asignar:'
														},
														cmbTipoMarcador

													]
										}
									);
	
	var ventanaAM = new Ext.Window(
									{
										title: 'Selecci&oacute;n de tipo de marcador',
										width: 600,
										height:150,
										layout: 'fit',
										plain:true,
										modal:true,
										bodyStyle:'padding:5px;',
										buttonAlign:'center',
										items: form,
										listeners : {
													show : {
																buffer : 10,
																fn : function() 
																{
																}
															}
												},
										buttons:	[
														{
															
															text: 'Aceptar',
                                                            
															handler: function()
																	{
																		if(cmbTipoMarcador.getValue()=='')
																		{
																			function resp()
																			{
																				cmbTipoMarcador.focus();
																			}
																			msgBox('Debe induicar el tipo de marcador a asignar',resp);
																			return;	
																		}
																		
																		registrarMarcador(cmbTipoMarcador.getValue(),oEditor);
																		ventanaAM.close();
																		
																	}
														},
														{
															text: 'Cancelar',
															handler:function()
																	{
																		ventanaAM.close();
																	}
														}
													]
									}
								);
	ventanaAM.show();	
}