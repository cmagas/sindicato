<?php session_start();
include("configurarIdiomaJS.php");
?>
var fp;
var ventana;
var archivo,archivoName;
var variableImagen=true;

var ImageChooser = function(config)
{
	this.config = config;
}

ImageChooser.prototype = 
{
    lookup : {},
	show : 	function(el, callback)
			{
            	
				if(!this.win)
				{
					this.initTemplates();
					this.store = new Ext.data.JsonStore	(
															{
																url: this.config.url,
																root: 'images',
																fields: [
																			'name', 'url',
																			{name:'size', type: 'float'},
																			{name:'lastmod'},
																			{name:'descripcion'},
																			{name:'ancho'},
																			{name:'alto'},
																			{name:'id'}
																		],
																listeners:	{
																	
																				'load':	{
																							fn:function()
																								{ 
																									this.view.select(0); 
																								}, 
																							scope:this, 
																							single:true
																						},
																				'beforeload':function(dSet)
																							{
																								if((typeof (idDoc) !='undefined')&&(idDoc!=null))
																									dSet.baseParams.idDoc=idDoc;
																							}
																			}
															}
                                                        );
					if(this.config.verTiposImg!=undefined)
                    {
                    	var configPadre=this.config;
                    	function enviarParametros(dSet)
                        {
                        	dSet.baseParams.tiposImg=configPadre.verTiposImg;
                        }
                    	this.store.on('beforeload',enviarParametros);
                    }
                    	
                                                                            
					this.store.load();
		
					var formatSize = function(data)
					{
						if(data.size < 1024) 
						{
							return data.size + " bytes";
						} else 
						{
							return (Math.round(((data.size*10) / 1024))/10) + " KB";
						}
					}
		
					var formatData = function(data)
					{
						data.shortName = data.name.ellipse(15);
						data.sizeString = formatSize(data);
						data.dateString = data.lastmod;//new Date(data.lastmod).format("d/d/Y g:i a");
						this.lookup[data.name] = data;
						return data;
					}
		
					this.view = new Ext.DataView	(
														{
															id:'imgAlmacen',
															tpl: this.thumbTemplate,
															singleSelect: true,
															overClass:'x-view-over',
															itemSelector: 'div.thumb-wrap',
															emptyText : '<div style="padding:10px;">No existen im&aacute;genes que concuerden con el filtro</div>',
															store: this.store,
															listeners: 	{
																			'selectionchange': {fn:this.showDetails, scope:this, buffer:100},
																			'dblclick'       : {fn:this.doCallback, scope:this},
																			'loadexception'  : {fn:this.onLoadException, scope:this},
																			'beforeselect'   : {fn:	function(view)
																									{
																										return view.store.getRange().length > 0;
																									}
																		}
															},
															prepareData: formatData.createDelegate(this)
														}
													);
		
					var cfg =	{
									title: 'Elegir una Imagen',
									id: 'img-chooser-dlg',
									layout: 'border',
									minWidth: 500,
									minHeight: 300,
									modal: true,
									closeAction: 'hide',
									border: false,
									items:	[
												{
													id: 'img-chooser-view',
													region: 'center',
													autoScroll: true,
													items: this.view,
													tbar:	[
																{
																	text: 'Filtro:'
																},
																{
																	xtype: 'textfield',
																	id: 'filter',
																	selectOnFocus: true,
																	width: 200,
																	listeners:	{
																					'render': {
																									fn:	function()
																										{
																											Ext.getCmp('filter').getEl().on('keyup', function()
																																					{
																																						this.filter();
																																					}, 
																																					this, {buffer:500}
																																			);
																										}, 
																									scope:this
																								}
																				}
																},
																' ',
																'-', 
																{
																	text: 'Ordenar por:'
																}, 
																{
																	id: 'sortSelect',
																	xtype: 'combo',
																	typeAhead: true,
																	triggerAction: 'all',
																	width: 120,
																	editable: false,
																	mode: 'local',
																	displayField: 'desc',
																	valueField: 'name',
																	lazyInit: false,
																	value: 'name',
																	store: new Ext.data.ArrayStore	(
																										{
																											fields: ['name', 'desc'],
																											data : [['name', 'Nombre'],['size', 'Tama\u00f1o'],['lastmod', 'Ultima Modificacion']]
																										}
																									),
																	listeners: 	{
																					'select': {fn:this.sortImages, scope:this}
																				}
																}
															]
												},
												{
													id: 'img-detail-panel',
													region: 'east',
													split: true,
													width: 250,
													minWidth: 150,
													maxWidth: 280
												}
											],
									bbar: 	[
												{
													icon: '../images/folder_up.png',
													cls:'x-btn-text-icon',
													id: 'sube',
													text: 'Subir Imagen...   ',
													handler: this.subirImagen,
													scope: this
												},
                                                
                                                {
													icon: '../images/folder_remove.png',
													cls:'x-btn-text-icon',
													id: 'btnEliminar',
													text: 'Eliminar Imagen',
													handler: this.eliminarImg,
													scope: this
												},
												'->',
												{
													icon: '../images/accept_green.png',
													cls:'x-btn-text-icon',
													id: 'ok-btn',
													text: 'Aceptar   ',
													handler: this.doCallback,
													scope: this
												},
												{
													icon: '../images/cancel_round.png',
													cls:'x-btn-text-icon',					
													text: 'Cancelar   ',
													handler: function()
															{ 
																this.win.hide(); 
															},
													scope: this
												}
											],
									keys: 	{
												key: 27, // Esc key
												handler: function()
														{ 
															this.win.hide(); 
														},
												scope: this
											}
								};
								Ext.apply(cfg, this.config);
								this.win = new Ext.Window(cfg);
							}
		
				this.reset();
				this.win.show(el);
				this.callback = callback;
				this.animateTarget = el;
			},

	initTemplates : function()
					{
						this.thumbTemplate = new Ext.XTemplate	(
																	'<tpl for=".">',
																		'<div class="thumb-wrap" id="{name}">',
																		'<div class="thumb"><img src="{url}" sizetitle="{name}"></div>',
																		'<span>{shortName}</span></div>',
																	'</tpl>'
																);
						this.thumbTemplate.compile();

						this.detailsTemplate = new Ext.XTemplate	(
																		'<div class="details">',
																			'<tpl for=".">',
																				'<img src="{url}" height="{alto}" width="{ancho}"><div class="details-info">',
																				'<b>Nombre de la imagen:</b>',
																				'<span>{name}</span>',
																				'<b>Tama\u00f1o:</b>',
																				'<span>{sizeString}</span>',
																				'<b>Ultima Modificacion:</b>',
																				'<span>{dateString}</span>',
																				'<b>Descripcion:</b>',
																				'<span>{descripcion}</span></div>',					
																			'</tpl>',
																		'</div>'
																	);
						this.detailsTemplate.compile();
					},

	showDetails : 	function()
					{
						var selNode = this.view.getSelectedNodes();
						var detailEl = Ext.getCmp('img-detail-panel').body;
						if(selNode && selNode.length > 0)
						{
							selNode = selNode[0];
							Ext.getCmp('ok-btn').enable();
							var data = this.lookup[selNode.id];
							detailEl.hide();
							this.detailsTemplate.overwrite(detailEl, data);
							detailEl.slideIn('l', {stopFx:true,duration:.2});
						}
						else
						{
							Ext.getCmp('ok-btn').disable();
							detailEl.update('');
						}
					},

	filter : function()
	{
		var filter = Ext.getCmp('filter');
		this.view.store.filter('name', filter.getValue());
		this.view.select(0);
	},

	sortImages : function()
	{
		var v = Ext.getCmp('sortSelect').getValue();
    	this.view.store.sort(v, v == 'name' ? 'asc' : 'desc');
    	this.view.select(0);
    },

	reset : function()
	{
		if(this.win.rendered)
		{
			Ext.getCmp('filter').reset();
			this.view.getEl().dom.scrollTop = 0;
		}
	    this.view.store.clearFilter();
		this.view.select(0);
	},
//------------------------------------------------------------------------- Comienza ventana

	eliminarImg:function()
    {
    	var selNode = this.view.getSelectedNodes()[0];
        
        if(selNode==undefined)
        {
        	Ext.MessageBox.alert('<?php echo $etj["lblAplicacion"]?>','Primero debe seleccionar la imagen a eliminar');
        	return;
        }
        
        var lookup = this.lookup;
        var data = lookup[selNode.id];
	    eliminarImagen(data.id);
    },
 	subirImagen: function()
	{
    
    	var guardarTipo=0;
		if(this.config.guardarTipoImg!=undefined)
        	guardarTipo=parseInt(this.config.guardarTipoImg);
		if(typeof(idDoc)=="undefined")
			idDoc='-1';
		else
		{
			if(idDoc==null)
				idDoc='-1';
		}
		
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
														msgTarget: 'side'
													},
								
										items:	[
												 	{
														xtype: 'fileuploadfield',
														id: 'form-file',
														emptyText: 'Elija una Imagen',
														fieldLabel: 'Imagen',
														name: 'image',
														buttonText: '',
														
														buttonCfg: 	{
																		iconCls: 'upload-icon'
																	}
													},
													{
														name:'titular',
														xtype: 'textfield',
														id: 'titulo',
														fieldLabel: 'T\u00CDtulo'
													
													},
													{
														name:'descript', 
														xtype: 'textarea',
														id: 'describe',
														fieldLabel: 'Descripci\u00F3n'
													 },
													 {
														 xtype:'hidden',
														 name:'idDoc',
														 value:idDoc
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
										title:'Agregar Imagen',
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
																			if((fp.getForm().isValid())&&((extension==".jpg")||(extension==".gif")||(extension==".jpeg")||(extension==".bmp")||(extension==".png")))
																			{
																					fp.getForm().submit	(	
																											{
																												url: '../media/guardarImagen.php',
																												waitMsg: 'Cargando imagen...',
																												success: function()
																																	{
																																		var almacenImg=Ext.getCmp('imgAlmacen');
																																		
																																		almacenImg.getStore().load();
																																		
																																		ventana.close();
																																	},
																												failure: this.falloAccion
																											}
																										);
																				
																			}
																			else
																			{
																				Ext.MessageBox.alert('Error de Archivo', 'El archivo ingresado no es v\u00e1lido, elija un archivo de imagen');
																			
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
	},

//-------------------------------------------------------------------------	termina subir imagen

	
	doCallback : function()
				{
					
					var selNode = this.view.getSelectedNodes()[0];
					var callback = this.callback;
					var lookup = this.lookup;
					this.win.hide(this.animateTarget, 	function()
														{
															if(selNode && callback)
															{
																var data = lookup[selNode.id];
																callback(data);
															}
														}
								);
				},

	onLoadException : 	function(v,o)
						{
							this.view.getEl().update('<div style="padding:10px;">Error al leer las imagenes.</div>');
						}
};

String.prototype.ellipse = function(maxLength)
{
    if(this.length > maxLength)
	{
        return this.substr(0, maxLength-3) + '...';
    }
    return this;
};


function eliminarImagen(idImagen)
{
	function  resp(btn)
	{
		if(btn=='yes')
		{
			function funcAjax()
			{
				var resp=peticion_http.responseText;
				arrResp=resp.split('|');
				if(arrResp[0]=='1')
				{
                	var almacenImg=Ext.getCmp('imgAlmacen');
					almacenImg.getStore().load();
				}
				else
				{
					msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
				}
			}
			obtenerDatosWeb('../media/eliminarImagen.php',funcAjax, 'POST','idImagen='+idImagen,true);

		}
	}
	Ext.MessageBox.confirm('<?php echo $etj["lblAplicacion"] ?>','Est&aacute; seguro de querer eliminar esta imagen?',resp);
}