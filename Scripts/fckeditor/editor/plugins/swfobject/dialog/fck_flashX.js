function subirflash()
	{
		
		
   
    	var guardarTipo=5;
		
		fp = new Ext.FormPanel	(
									{
										fileUpload: true,
										width: 500,
										frame: true,
										autoHeight: true,
										bodyStyle: 'padding: 10px 10px 0 10px;',
										labelWidth: 100,
										defaults: 	{
														anchor: '100%',
														allowBlank: false,
														msgTarget: 'side'
													},
								
										items:	[
												 	{
														xtype: 'fileuploadfield',
														id: 'form-file',
														emptyText: 'Elija un Archivo Flash',
														fieldLabel: 'Flash',
														name: 'flash',
														buttonText: '',
														
														buttonCfg: 	{
																		iconCls: 'upload-icon'
																	}
													},
													{
														name:'titular',
														xtype: 'textfield',
														id: 'titulo',
														fieldLabel: 'Titulo'
													
													},
													{
														name:'descript', 
														xtype: 'textarea',
														id: 'describe',
														allowBlank: true,
														fieldLabel: 'Descripcion'
													},
													
                                                     {
                                                     	 xtype:'hidden',
														 name:'tipoArchivo',
														 value:guardarTipo
                                                     }
													 
												]
									}
								);
	
		ventana=new Ext.Window(
							   		{
										title:'Agregar Flash',
										width:450,
										height:235,
										layout:'fit',
										buttonAlign:'center',
										items:[fp],
										modal:true,
										plain:true,
										listeners:
                                                    {
                                                        show:
                                                                {
                                                                    buffer:10,
                                                                    fn:function()
                                                                            {
                                                                                
                                                                            }
                                                                }
                                                    },
											buttons: 	[
															{
																text: 'Agregar',
																handler: function()
																		{
																			archivo=gE('form-file-file');
																			archivoName=archivo.value;
																			var extension = (archivoName.substring(archivoName.lastIndexOf("."))).toLowerCase();
																			if((fp.getForm().isValid())&&((extension==".flv")||(extension==".swf")))
																			{
																					fp.getForm().submit	(	
																											{
																												url: '../../../../../../media/guardarFlash.php',
																												waitMsg: 'Cargando Flash...',
																												success: function(fu,o)
																																	{
																																		
																																		var almacenImg=Ext.getCmp('imgAlmacen');
																																		gE('framePre').src='../media/verImagen.php?id='+o.result.idFlash;
																																		gE('txtUrl').value='../media/verImagen.php?id='+o.result.idFlash;
																																		
																																		ventana.close();
																																		
																																		
																																		
																																	},
																												failure: this.falloAccion
																											}
																										);
																				
																			}
																			else
																			{
																				Ext.MessageBox.alert('Error de Archivo', 'El archivo ingresado no es v\u00e1lido, elija un archivo de Flash');
																			
																			}
																
																		}
															},
															{
																text: 'Cancelar',
																handler: function()
																		{
																			ventana.close();
																		}
															}
														]
									}
							   )
		ventana.show();
	};
	
function recarga()
{
gE('framePre').src=gE('txtUrl').value;
}
