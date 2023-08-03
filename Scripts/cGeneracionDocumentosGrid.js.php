<?php
	session_start();
	include("configurarIdiomaJS.php");
	include("conexionBD.php");
	
	$consulta="SELECT idCategoria,nombreCategoria FROM 908_categoriasDocumentos";
	$arrCategorias=$con->obtenerFilasArreglo($consulta);
	
	$consulta="SELECT valor,texto FROM 1004_siNo";
	$arrSiNo=$con->obtenerFilasArreglo($consulta);


?>

var nodoPlantillaSel=null;
var uploadControl;
var objBaseConfiguracion=null;
var iFormularioBase='';
var iRegistroBase='';
var carpetaJudicialBase='';
var idCarpetaJudicialBase='';
var arbolFormatosExpandido=false;
var arrSiNo=<?php echo $arrSiNo?>;
var arrCategorias=<?php echo $arrCategorias?>;

function crear_CGridGeneracionDocumentos(objConf)
{
	objBaseConfiguracion=objConf;
	iFormularioBase=objConf.idFormulario;
    iRegistroBase=objConf.idRegistro;
	carpetaJudicialBase=objConf.carpetaAdministrativa;
    idCarpetaJudicialBase=objConf.idCarpetaAdministrativa?objConf.idCarpetaAdministrativa:-1;	
    arbolFormatosExpandido=objConf.expandidoFormatos?objConf.expandidoFormatos:false;
	var lector= new Ext.data.JsonReader({
                                            
                                            totalProperty:'numReg',
                                            fields: [
                                               			{name:'idDocumento'},
		                                                {name: 'tipoDocumento'},
		                                                {name:'descripcionDocumento'},
		                                                {name:'fechaCreacion', type:'date', dateFormat:'Y-m-d H:i:s'},
                                                        {name: 'responsableCreacion'},
                                                        {name: 'situacion'},
                                                        {name: 'plazoCumplimiento',type:'date', dateFormat:'Y-m-d'},
                                                        {name: 'modificaSituacionCarpeta'},
                                                        {name: 'situacionCarpeta'},
                                                        {name:'descripcionActuacion'},
                                                        {name:'categoriaDocumento'},
                                                        {name:'idDocumentoServidor'},
                                                        {name: 'documentoBloqueado'},
                                                        {name: 'lblAlertas'}
                                            		],
                                            root:'registros'
                                            
                                        }
                                      );
	 
    
	var expander = new Ext.ux.grid.RowExpander({
                                                column:1,
                                                expandOnDblClick:false,
                                                tpl : new Ext.Template(
                                                    '<table >'+
														'<tr><td ><span class="TSJDF_Control"><b>Descripci&oacute;n de la actuaci&oacute;n:</b><br>{descripcionActuacion}</span><br /><br /></td></tr>'+
                                                   		'<tr><td ><span class="TSJDF_Control"><b>Alertas programadas:</b><br><br>{lblAlertas}</span><br /><br /></td></tr>'+
                                                    '</table>'
                                                )
                                            });                                                                                       
                                                                                                                                                                                                                                                          
	var alDatos=new Ext.data.GroupingStore({
                                                            reader: lector,
                                                            proxy : new Ext.data.HttpProxy	(

                                                                                              {

                                                                                                  url: '../paginasFunciones/funcionesModulosEspeciales_SGP.php'

                                                                                              }

                                                                                          ),
                                                            sortInfo: {field: 'fechaCreacion', direction: 'ASC'},
                                                            groupField: 'fechaCreacion',
                                                            remoteGroup:false,
				                                            remoteSort: false,
                                                            autoLoad:true
                                                            
                                                        }) 
	alDatos.on('beforeload',function(proxy)
    								{
                                   	
                                   		gEx('btnModifyDocument').disable();
                                   		gEx('btnDeleteDocument').disable();
                                    	proxy.baseParams.funcion='145';
                                        proxy.baseParams.cA=carpetaJudicialBase;
                                        proxy.baseParams.idFormulario=iFormularioBase;
                                        proxy.baseParams.idReferencia=iRegistroBase;
                                    }
                        )   
       
        var cModelo= new Ext.grid.ColumnModel   	(
                                                        [
                                                            new  Ext.grid.RowNumberer(),
                                                            expander,
                                                            {
                                                                header:'',
                                                                width:30,
                                                                sortable:true,
                                                                dataIndex:'idDocumentoServidor',
                                                                renderer:function(val,meta,registro)
                                                                		{
                                                                        	if(registro.data.documentoBloqueado=='1')
                                                                            {
                                                                            	return '<a href="javascript:visualizarDocumentoFinalizado_CGridGeneracionDocumentos(\''+bE(registro.data.idDocumento)+'\',\''+bE(registro.data.tipoDocumento)+'\')"><img src="../images/page_white_magnify.png" title="Visualizar documento" alt="Visualizar documento"/></a>';
                                                                            }
                                                                        }
                                                            },
                                                            {
                                                                header:'Fecha de creaci&oacute;n',
                                                                width:130,
                                                                sortable:true,
                                                                dataIndex:'fechaCreacion',
                                                                renderer:function(val)
                                                                		{
                                                                			return val.format('d/m/Y H:i:s')
                                                                		}
                                                            },
                                                            {
                                                                header:'Tipo de documento',
                                                                width:180,
                                                                sortable:true,
                                                                renderer:mostrarValorDescripcion,
                                                                dataIndex:'categoriaDocumento',
                                                                renderer:function(val)
                                                                		{
                                                                			return formatearValorRenderer(arrCategorias,val);
                                                                		}
                                                            },
                                                            {
                                                                header:'T&iacute;tulo del documento',
                                                                width:320,
                                                                sortable:true,
                                                                renderer:mostrarValorDescripcion,
                                                                dataIndex:'descripcionDocumento'
                                                            },
                                                            {
                                                                header:'Registrado por',
                                                                width:250,
                                                                sortable:true,
                                                                renderer:mostrarValorDescripcion,
                                                                dataIndex:'responsableCreacion'
                                                            },
                                                            {
                                                                header:'Situaci&oacute;n actual',
                                                                width:300,
                                                                sortable:true,
                                                                renderer:mostrarValorDescripcion,
                                                                dataIndex:'situacion'
                                                            }
                                                        ]
                                                    );
                                                    


		var confGrid=	{
                          id:'gDocumentosCGrid',
                          store:alDatos,
                          frame:false,
                          cm: cModelo,
                          stripeRows :true,
                          loadMask:true,
                          columnLines : true,
                          plugins:[expander] ,  
                          tbar:	[
                                      {
                                          icon:'../images/add.png',
                                          cls:'x-btn-text-icon',
                                          text:'Crear documento de respuesta',
                                          handler:function()
                                                  {
                                                      
                                                      mostrarVentanaAddDocumento_CGridGeneracionDocumentos();
                                                      
    
                                                      
                                                  }
    
                                      },'-',
                                      {
                                          id:'btnModifyDocument',
                                          icon:'../images/pencil.png',
                                          cls:'x-btn-text-icon',
                                          text:'Modificar documento de respuesta',
                                          handler:function()
                                                  {
                                                      var fila=gEx('gDocumentosCGrid').getSelectionModel().getSelected();
                                                      if(!fila)
                                                      {
                                                          msgBox('Debe seleccionar el documento que desea modificar');
                                                          return;
                                                      }
                                                      var anchoVentana=(obtenerDimensionesNavegador()[1]*0.9);
                                                      var altoVentana=(obtenerDimensionesNavegador()[0]*0.9);
                                                      
                                                      objConf={
                                                                  tipoDocumento:fila.data.tipoDocumento,
                                                                  idFormulario:-2,
                                                                  ancho:800,
                                                                  alto:500,
                                                                  rol:'158_0',
                                                                  ancho:anchoVentana,
                                                                  alto:altoVentana,
                                                                  rolDefault:'158_0',
                                                                  idRegistro: fila.data.idDocumento,
                                                                  functionAfterSignDocument:function()
                                                                                      {
                                                                                      		
                                                                                          gEx('gDocumentosCGrid').getStore().reload();
                                                                                          if(objBaseConfiguracion.functionAfterSignDocument)
                                                                                          		objBaseConfiguracion.functionAfterSignDocument();
                                                                                      },
                                                                  functionAfterValidate:function()
                                                                                      {
                                                                                          gEx('gDocumentosCGrid').getStore().reload();
                                                                                          if(objBaseConfiguracion.functionAfterValidate)
                                                                                          		objBaseConfiguracion.functionAfterValidate();
                                                                                      },
                                                                  functionAfterTurn:function()
                                                                                      {
                                                                                          gEx('gDocumentosCGrid').getStore().reload();
                                                                                          if(objBaseConfiguracion.functionAfterTurn)
                                                                                          		objBaseConfiguracion.functionAfterTurn();
                                                                                      },
                                                                  functionAfterSaveDocument:function()
                                                                                  {
                                                                                      gEx('gDocumentosCGrid').getStore().reload();
                                                                                      if(objBaseConfiguracion.functionAfterSaveDocument)
                                                                                          		objBaseConfiguracion.functionAfterSaveDocument();
                                                                                  },
                                                                  functionAfterLoadDocument:function()
                                                                                          {
                                                                                              setTimeout(function()
                                                                                                          {
                                                                                                              var body = CKEDITOR.instances.txtDocumento.editable().$;
                                                                                                              
                                                                                                              var value = (anchoVentana*100)/960;
                                                                                                              
    
                                                                                                              body.style.MozTransformOrigin = "top left";
                                                                                                              body.style.MozTransform = "scale(" + (value/100)  + ")";
    
                                                                                                              body.style.WebkitTransformOrigin = "top left";
                                                                                                              body.style.WebkitTransform = "scale(" + (value/100)  + ")";
    
                                                                                                              body.style.OTransformOrigin = "top left";
                                                                                                              body.style.OTransform = "scale(" + (value/100)  + ")";
    
                                                                                                              body.style.TransformOrigin = "top left";
                                                                                                              body.style.Transform = "scale(" + (value/100)  + ")";
                                                                                                              // IE
                                                                                                              body.style.zoom = value/100;
                                                                                                          		
                                                                                                              if(objBaseConfiguracion.functionAfterLoadDocument)
                                                                                          						objBaseConfiguracion.functionAfterLoadDocument();
                                                                                                              
                                                                                                          },200
                                                                                                      )
                                                                                              
    
                                                                                              
                                                                                          }
    
                                                              };
                                                      
                                                      
                                                      mostrarVentanaGeneracionDocumentos(objConf);
                                                  }
    
                                      },'-',
                                      {
                                          icon:'../images/delete.png',
                                          id:'btnDeleteDocument',
                                          cls:'x-btn-text-icon',
                                          text:'Remover documento de respuesta',
                                          handler:function()
                                                  {
                                                      var fila=gEx('gDocumentosCGrid').getSelectionModel().getSelected();
                                                      if(!fila)
                                                      {
                                                          msgBox('Debe seleccionar el documento que desea remover');
                                                          return;
                                                      }
                                                      
                                                      function resp(btn)
                                                      {
                                                          if(btn=='yes')
                                                          {
                                                              function funcAjax()
                                                              {
                                                                  var resp=peticion_http.responseText;
                                                                  arrResp=resp.split('|');
                                                                  if(arrResp[0]=='1')
                                                                  {
                                                                      gEx('gDocumentosCGrid').getStore().reload();
                                                                  }
                                                                  else
                                                                  {
                                                                      msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
                                                                  }
                                                              }
                                                              obtenerDatosWeb('../paginasFunciones/funcionesModulosEspeciales_SGP.php',funcAjax, 'POST','funcion=146&iD='+fila.data.idDocumento,true);
                                                          }
                                                      }
                                                      msgConfirm('Est&aacute; seguro de querer remover el documento seleccionado?',resp);
                                                  }
    
                                      }
    
                                  ]   ,                                                          
                          view:new Ext.grid.GroupingView({
                                                              forceFit:false,
                                                              showGroupName: false,
                                                              enableGrouping :false,
                                                              enableNoGroups:false,
                                                              enableGroupingMenu:false,
                                                              hideGroupedColumn: false,
                                                              startCollapsed:false
                                                          })
                      }
                      
		if((objConf.confGrid)&&(objConf.confGrid.region))
        {
        	confGrid.region=objConf.confGrid.region;
        }   
        
        if((objConf.confGrid)&&(objConf.confGrid.posX))
        {
        	confGrid.x=objConf.confGrid.posX;
        }
        
        if((objConf.confGrid)&&(objConf.confGrid.posY))
        {
        	confGrid.y=objConf.confGrid.posY;
        }                   
                      
        var tblGrid=	new Ext.grid.GridPanel	(confGrid);
        
        
        
        tblGrid.getSelectionModel().on('rowselect',function(sm,nFila,registro)
       												{
       													gEx('btnModifyDocument').disable();
                                   						gEx('btnDeleteDocument').disable();
                                   						if(registro.data.documentoBloqueado=='0')
                                   						{
                                   							gEx('btnModifyDocument').enable();
                                   							gEx('btnDeleteDocument').enable();
                                   						}
       												}
        								)
        return 	tblGrid;
}


function mostrarVentanaAddDocumento_CGridGeneracionDocumentos(iDocumento,importarWord)
{
	     
	if(iDocumento)
    {
    	nodoPlantillaSel={};
    	nodoPlantillaSel.id=iDocumento;
        nodoPlantillaSel.attributes={};
        
        var pos=existeValorMatriz(arrPlatillasDocumentos,iDocumento);
        
    	nodoPlantillaSel.attributes.perfilValidacion=arrPlatillasDocumentos[pos][2];
		nodoPlantillaSel.text=arrPlatillasDocumentos[pos][1];
    	mostrarVentanaDatosDocumento_CGridGeneracionDocumentos(importarWord?true:false,importarWord);
    	return;
    }
	var form = new Ext.form.FormPanel(	
										{
											baseCls: 'x-plain',
											layout:'border',
											defaultType: 'label',
                                            bbar:	[
                                            			{
                                                        	xtype:'label',
                                                            hidden:!objBaseConfiguracion.permitePDFDirecto,
                                                        	html: '<b>Subir documento PDF:&nbsp;&nbsp;&nbsp;</b>'
                                                        },
                                                        
                                                        {
                                                           xtype:'label',
                                                            hidden:!objBaseConfiguracion.permitePDFDirecto,
                                                            html:	'<table width="290"><tr><td><div id="uploader"><p>Your browser doesn\'t have Flash, Silverlight or HTML5 support.</p></div></td></tr></table>'
                                                        },
                                                       	{
                                                           xtype:'label',
                                                            hidden:!objBaseConfiguracion.permitePDFDirecto,
                                                            html:	'<table ><tr id="filaAvance" style="display:none"><td align="right">&nbsp;&nbsp;Porcentaje de avance: <span id="porcentajeAvance"> 0%</span>&nbsp;&nbsp;</td></tr></table>'
                                                        },
                                                        {
                                                           
                                                            hidden:!objBaseConfiguracion.permitePDFDirecto,
                                                            id:'btnUploadFile',
                                                            xtype:'button',
                                                            text:'Seleccionar...',
                                                            handler:function()
                                                                    {
                                                                        $('#containerUploader').click();
                                                                    }
                                                        },
                                                        
                                                        {
                                                           
                                                            xtype:'label',
                                                            hidden:true,
                                                            html:	'<div id="containerUploader"></div>'
                                                        }
                                                        
                                                        
                                                        
                                                        
                                            		],
											items: 	[
                                            			crearArbolPlantillas_CGridGeneracionDocumentos(),
                                            			{
                                            				xtype:'panel',
                                            				region:'center',
                                            				layout:'absolute',
                                            				items: 	[
                                           								{
                                           									x:0,
                                           									y:0,
                                           									xtype:'label',
																			html:'<textarea id="txtDocumentoDemo"></textarea>'
                                           								}
                                            						]
                                            			}                               			
                                            			
                                            			
													]
										}
									);
	
    
    var anchoVentana=(obtenerDimensionesNavegador()[1]*0.9);
    var altoVentana=(obtenerDimensionesNavegador()[0]*0.9);
    
	var ventanaAM = new Ext.Window(
									{
										title: 'Crear documento',
                                        id:'wCreateDocumentDocument',
										width: anchoVentana,
										height:altoVentana,
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
																	var editor1=	CKEDITOR.replace('txtDocumentoDemo',
																										 {

																											customConfig:'../../modulosEspeciales_SGJP/Scripts/configCKEditorVistaPrevia.js',
																											width:anchoVentana-310,
																											height:altoVentana-100,
																											resize_enabled:false,
																											on:	{
																													instanceReady:function(evt)
																																{
																																	


																																}

																												}
																										 }
																						);
                                                                                        
                                                                                        
                                                                      var cObj={
                                                                      // Backend settings
                                                                                              upload_url: "../modulosEspeciales_SGJP/procesarComprobante.php", //lquevedor
                                                                                              file_post_name: "archivoEnvio",
                                                                               
                                                                                              // Flash file settings
                                                                                              file_size_limit : "1000 MB",
                                                                                              file_types : "*.pdf;",			// or you could use something like: "*.doc;*.wpd;*.pdf",
                                                                                              file_types_description : "Todos los archivos",
                                                                                              file_upload_limit : 0,
                                                                                              file_queue_limit : 1,
                                                                                              timeAjustControl:150,
                                                                                              timeAfterDrawHandler:150,
                                                                               				  afterDrawHandler:function()
                                                                                              					{
                                                                                                                	gEx('wCreateDocumentDocument').doLayout();
                                                                                                                },
                                                                                              
                                                                                              upload_success_handler : subidaCorrecta_CGridGeneracionDocumentosPDF
                                                                                          };  
                        											crearControlUploadHTML5(cObj);                                          
                        
																}
															}
												},
										buttons:	[
														{
															id:'btnAddCrearDocumentoGrid',
															text: '<?php echo $etj["lblBtnAceptar"]?>',
                                                            
															handler: function()
																	{
                                                                    	if( objBaseConfiguracion.permitePDFDirecto && uploadControl && uploadControl.files.length>0)
                                                                        {
                                                                        	gEx('btnAddCrearDocumentoGrid').disable();
                                                                            uploadControl.start();
                                                                        }
                                                                       else
                                                                       {

                                                                            if(!nodoPlantillaSel)
                                                                            {
                                                                                msgBox('Debe seleccionar la plantilla a utilizar para generar el documento');
                                                                                return;
                                                                            }
                                                                            ventanaAM.close();
                                                                            mostrarVentanaDatosDocumento_CGridGeneracionDocumentos(null);
                                                                        }
																	}
														},
														{
															text: '<?php echo $etj["lblBtnCancelar"]?>',
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

function crearArbolPlantillas_CGridGeneracionDocumentos()
{
	var raiz=new  Ext.tree.AsyncTreeNode(
											{
												id:'-1',
												text:'Raiz',
												draggable:false,
												expanded :false,
												cls:'-1'
											}
										)
										
	var cargadorArbol=new Ext.tree.TreeLoader(
											{
												baseParams:{
																funcion:'1',
                                                                idFormulario:iFormularioBase,
                                                                arbolExpandido:arbolFormatosExpandido?1:0
															},
												dataUrl:'../paginasFunciones/funcionesModulosEspeciales_cGeneracionDocumentosGrid.php'
											}
										)		
										
											
										
	var arbolPlantillas=new Ext.tree.TreePanel	(
														{
															
															id:'arbolPlantillas',
															useArrows:true,
															autoScroll:true,
															animate:true,
															enableDD:true,
															width:280,
															region:'west',
															containerScroll: true,
															root:raiz,
															loader: cargadorArbol,
															rootVisible:false
														}
													)
			
							
	arbolPlantillas.on('click',funcPlantillaClick_CGridGeneracionDocumentos);	
	
	return arbolPlantillas;
}

function funcPlantillaClick_CGridGeneracionDocumentos(nodo)
{
	nodoPlantillaSel=nodo;
	
	if(nodo.attributes.tipoNodo=='2')
	{
	
		function funcAjax()
		{
			var resp=peticion_http.responseText;
			arrResp=resp.split('|');
			if(arrResp[0]=='1')
			{
				var objPlantilla=eval('['+arrResp[1]+']')[0];
				CKEDITOR.instances["txtDocumentoDemo"].setData(bD(objPlantilla.cuerpoDocumento));
                setTimeout(	function()
                			{
                            
			                	var anchoVentana=(obtenerDimensionesNavegador()[1]*0.85)-310;
							    var altoVentana=(obtenerDimensionesNavegador()[0]*0.85)-100;                                                                                       											
                                var body = CKEDITOR.instances.txtDocumentoDemo.editable().$;
                
                                var value = anchoVentana*100/800;
                                body.style.MozTransformOrigin = "top left";
                                body.style.MozTransform = "scale(" + (value/100)  + ")";
                                body.style.WebkitTransformOrigin = "top left";
                                body.style.WebkitTransform = "scale(" + (value/100)  + ")";
                                body.style.OTransformOrigin = "top left";
                                body.style.OTransform = "scale(" + (value/100)  + ")";
                                body.style.TransformOrigin = "top left";
                                body.style.Transform = "scale(" + (value/100)  + ")";
                                body.style.zoom = value/100;
                          	},200
                         )


			}
			else
			{
				msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
			}
		}
		obtenerDatosWeb('../paginasFunciones/funcionesModulosEspeciales_SGP.php',funcAjax, 'POST','funcion=143&iD='+nodo.id,true);
	}
	else
	{
		nodoPlantillaSel=null;
		CKEDITOR.instances["txtDocumentoDemo"].setData('');
	}
	
}

function mostrarVentanaDatosDocumento_CGridGeneracionDocumentos(datosDocumento,importarDocumento)
{
	
	
	var form = new Ext.form.FormPanel(	
										{
											baseCls: 'x-plain',
											layout:'absolute',
											defaultType: 'label',
											items: 	[
                                            			{
                                            			
                                            				x:10,
                                            				y:10,
                                                            id:'lblTituloDocumento',
                                                            hidden:importarDocumento,
                                            				html:'T&iacute;tulo del documento:'
                                            			},
                                                        {
                                            			
                                            				x:10,
                                            				y:10,
                                                            id:'lblIngreseDocumento',
                                                            hidden:!importarDocumento,
                                            				html:'Ingrese documento a adjuntar:'
                                            			},
                                            			
                                                        {
                                            				x:210,
                                            				y:5,
                                            				xtype:'textfield',
                                            				width:300,
                                                            
                                                            hidden:importarDocumento,
                                            				id:'txtTitulo'
                                            			},
                                                        {
                                                        	xtype:'button',
                                                            cls:'x-btn-text-icon',
                                                            icon:'../images/page_word.png',
                                                            text:'Adjuntar documento Word',
                                                            width:160,
                                                            x:530,
                                                            y:5,
                                                            id:'btnAdjuntarDocumento',
                                                            enableToggle : true,
                                                            pressed:importarDocumento,
                                                            toggleHandler:function(btn,presionado)
                                                                            {
                                                                                if(presionado)
                                                                                {
                                                                                   	gEx('lblIngreseDocumento').show();
                                                                                    gEx('lblTituloDocumento').hide();
                                                                                    gEx('txtTitulo').hide();                                                                                    
                                                                                    gEx('lblTablaAdjunta').show();
                                                                                    gEx('btnUploadFile').show();
                                                                                    importarDocumento=true;
                                                                                }
                                                                                else
                                                                                {
                                                                                    gEx('lblIngreseDocumento').hide();
                                                                                    gEx('lblTituloDocumento').show();                                                                                    
                                                                                    gEx('txtTitulo').show();
                                                                                    gEx('lblTablaAdjunta').hide();
                                                                                    gEx('btnUploadFile').hide();
                                                                                    gEx('txtTitulo').focus();
                                                                                    importarDocumento=false;
                                                                                }
                                                                            }
                                                            			
                                                        },
                                                        {
                                                            x:180,
                                                            y:5,
                                                            id:'lblTablaAdjunta',
                                                            hidden:!importarDocumento,
                                                            html:	'<table width="290"><tr><td><div id="uploader"><p>Your browser doesn\'t have Flash, Silverlight or HTML5 support.</p></div></td></tr><tr id="filaAvance" style="display:none"><td align="right">Porcentaje de avance: <span id="porcentajeAvance"> 0%</span></td></tr></table>'
                                                        },
                                                       
                                                        {
                                                            x:475,
                                                            y:6,
                                                            width:50,
                                                            hidden:!importarDocumento,
                                                            id:'btnUploadFile',
                                                            xtype:'button',
                                                            text:'...',
                                                            handler:function()
                                                                    {
                                                                        $('#containerUploader').click();
                                                                    }
                                                        },
                                                        {
                                                            x:185,
                                                            y:10,
                                                            hidden:true,
                                                            html:	'<div id="containerUploader"></div>'
                                                        },
                                                       
                                                        
                                                        {
                                                            x:290,
                                                            y:0,
                                                            xtype:'hidden',
                                                            id:'idArchivo'

                                                        },
                                                        {
                                                            x:290,
                                                            y:0,
                                                            xtype:'hidden',
                                                            id:'nombreArchivo'
                                                        }, 
                                            			{
                                            			
                                            				x:10,
                                            				y:40,
                                            				html:'Ingrese la descripci&oacute;n de la actuaci&oacute;n:'
                                            			},
                                            			{
                                            				x:10,
                                            				y:70,
                                            				xtype:'textarea',
                                            				width:680,
                                            				height:80,
                                            				id:'txtDescripcion'
                                            			},
                                            			crearGridProgramacionAlerta_CGridGeneracionDocumentos()
													]
										}
									);
	
	var ventanaAM = new Ext.Window(
									{
										title: 'Informaci&oacute;n del documento',
										width: 730,
                                        id:'vInfoDocumento',
										height:440-(!objBaseConfiguracion.mostrarGridProgramacionAlerta?190:0),
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
                                                                	if(!importarDocumento)
																		gEx('txtTitulo').focus(false,500);
                                                                    
                                                                    
                                                                    var cObj={
                                                                    // Backend settings
                                                                                            upload_url: "../modulosEspeciales_SGJP/procesarComprobante.php", //lquevedor
                                                                                            file_post_name: "archivoEnvio",
                                                                             
                                                                                            // Flash file settings
                                                                                            file_size_limit : "1000 MB",
                                                                                            file_types : "*.doc;*.docx",			// or you could use something like: "*.doc;*.wpd;*.pdf",
                                                                                            file_types_description : "Todos los archivos",
                                                                                            file_upload_limit : 0,
                                                                                            file_queue_limit : 1,
                                                                             
                                                                                            
                                                                                            upload_success_handler : subidaCorrecta_CGridGeneracionDocumentos
                                                                                        };  
																	crearControlUploadHTML5(cObj);
                                                                }
															}
												},
										buttons:	[
														{
															
															text: '<?php echo $etj["lblBtnAceptar"]?>',
                                                            
															handler: function()
																	{
                                                                    	
																		
																		var txtDescripcion=gEx('txtDescripcion');
																		if(!importarDocumento)
                                                                        {

																			var txtTitulo=gEx('txtTitulo');
                                                                            if(txtTitulo.getValue().trim()=='')
                                                                            {
                                                                                function resp()
                                                                                {
                                                                                    txtTitulo.focus();
                                                                                }
                                                                                msgBox('Debe ingresar el t&iacute;tulo del documento',resp);
                                                                                return;
                                                                            }
                                                                            
                                                                            
                                                                            var arrAlertas='';
                                                                            var gAlertas=gEx('gAlertas');
                                                                            var x;
                                                                            var f;
                                                                            for(x=0;x<gAlertas.getStore().getCount();x++)
                                                                            {
                                                                                f=gAlertas.getStore().getAt(x);
                                                                                
                                                                                if(f.data.fechaAlerta=='')
                                                                                {
                                                                                    function respDoc2()
                                                                                    {
                                                                                        gAlertas.startEditing(x,2);
                                                                                    }
                                                                                    
                                                                                    msgBox('Debe ingresar la fecha de la alerta',respDoc2);
                                                                                    return;
                                                                                }
                                                                                
                                                                                if(f.data.descripcionAlerta.trim()=='')
                                                                                {
                                                                                    function respDoc()
                                                                                    {
                                                                                        gAlertas.startEditing(x,3);
                                                                                    }
                                                                                    msgBox('Debe ingresar la descripci&oacute;n de la alerta',respDoc);
                                                                                    return;
                                                                                }
                                                                                
                                                                                oAlerta='{"fechaAlerta":"'+f.data.fechaAlerta.format('Y-m-d')+'","textoAlerta":"'+cv(f.data.descripcionAlerta)+'"}';
                                                                                if(arrAlertas=='')
                                                                                    arrAlertas=oAlerta;
                                                                                else
                                                                                    arrAlertas+=','+oAlerta;
                                                                            }
                                                                            
                                                                            var cadObj='{"idGeneracionDocumento":"-1","tipoDocumento":"'+nodoPlantillaSel.id+'","tituloDocumento":"'+cv(txtTitulo.getValue().trim())+
                                                                                        '","perfilValidacion":"'+nodoPlantillaSel.attributes.perfilValidacion+'","fechaLimite":"","modificaSituacionCarpeta":"0","situacion":"'+
                                                                                        '","descripcionActuacion":"'+cv(gEx('txtDescripcion').getValue())+'","carpetaAdministrativa":"'+
                                                                                        carpetaJudicialBase+'","idFormulario":"'+iFormularioBase+'","idRegistro":"'+iRegistroBase+
                                                                                        '","arrAlertas":['+arrAlertas+']}';
                                                                            
                                                                            
                                                                            
                                                                            if((importarDocumento)||(nodoPlantillaSel.attributes.funcionJSParametros==''))
                                                                            {
                                                                            	guardarDatosDocumento_CGridGeneracionDocumentos(cadObj,'',ventanaAM);
                                                                            }
                                                                            else
                                                                            {
                                                                            	var resultado='';
                                                                                eval('resultado=typeof('+nodoPlantillaSel.attributes.funcionJSParametros+');');
                                                                                if(resultado!='undefined')
                                                                                {
                                                                            		eval(nodoPlantillaSel.attributes.funcionJSParametros+'(cadObj,ventanaAM);');
                                                                                }
                                                                                else
                                                                                {
                                                                                	var paramComp='';
                                                                                    
                                                                                    if(objBaseConfiguracion.parametrosFuncionesLlenado)
                                                                                    {
                                                                                    	var x;
                                                                                        for(x=0;x<objBaseConfiguracion.parametrosFuncionesLlenado.length;x++)
                                                                                        {
                                                                                        	if(paramComp=='')
                                                                                            	paramComp=objBaseConfiguracion.parametrosFuncionesLlenado[x][0]+'='+objBaseConfiguracion.parametrosFuncionesLlenado[x][1];
                                                                                            else
                                                                                            	paramComp+='&'+objBaseConfiguracion.parametrosFuncionesLlenado[x][0]+'='+objBaseConfiguracion.parametrosFuncionesLlenado[x][1];
                                                                                        }
                                                                                        
                                                                                        paramComp='?'+paramComp;
                                                                                    }
                                                                                    
                                                                                	if(nodoPlantillaSel.attributes.libreriaFuncionJSParametros!='')
                                                                                    {
                                                                                    	loadScript(nodoPlantillaSel.attributes.libreriaFuncionJSParametros+paramComp, function()
                                                                                        				{
                                                                                                        	eval(nodoPlantillaSel.attributes.funcionJSParametros+'(cadObj,ventanaAM);');
                                                                                                        }
                                                                                        			)
                                                                                    }
                                                                                    else
                                                                                    {
                                                                                    	msgBox('No se encuentra el m&oacute;dulo de registro de par&aacute;metros');
                                                                                    }
                                                                                	
                                                                                }
                                                                            }
                                                                            
																		}
																		else
                                                                        {
                                                                        
                                                                        	if(uploadControl.files.length==0)
                                                                            {
                                                                                msgBox('Debe ingresar el documento que desea adjuntar');
                                                                                return;
                                                                            }
                                                                        
                                                                            uploadControl.start();
	                                                                    }    
																		
																	}
														},
														{
															text: '<?php echo $etj["lblBtnCancelar"]?>',
															handler:function()
																	{
																		ventanaAM.close();
																	}
														}
													]
									}
								);
	ventanaAM.show();
	
	
	
	if(!datosDocumento)
	{
		
		gEx('txtTitulo').setValue(nodoPlantillaSel.text);
	}
}

function guardarDatosDocumento_CGridGeneracionDocumentos(cadObj,parametros,ventana)
{
	cadObj=cadObj.substring(0,cadObj.length-1);
    cadObj+=',"datosParametros":"'+parametros.replace(/"/gi,'\\"',parametros)+'"}';
    

	function funcAjax()
    {
        var resp=peticion_http.responseText;
        arrResp=resp.split('|');
        if(arrResp[0]=='1')
        {
        	var anchoVentana=(obtenerDimensionesNavegador()[1]*0.9);
			var altoVentana=(obtenerDimensionesNavegador()[0]*0.9);
            
        	gEx('gDocumentosCGrid').getStore().reload();
            objConf={
                        tipoDocumento:nodoPlantillaSel.id,
                        idFormulario:-2,
                        ancho:anchoVentana,
                        alto:altoVentana,
                        rol:'158_0',
                        rolDefault:'158_0',
                        idRegistro: arrResp[1],
                        functionAfterSignDocument:function()
                        				{
                                       		gEx('gDocumentosCGrid').getStore().reload();
                                            if(objBaseConfiguracion.functionAfterSignDocument)
                                            	objBaseConfiguracion.functionAfterSignDocument();
                                        },
                        functionAfterValidate:function()
                                        {
                                            gEx('gDocumentosCGrid').getStore().reload();
                                            if(objBaseConfiguracion.functionAfterValidate)
                                            	objBaseConfiguracion.functionAfterValidate();
                                        },
                        functionAfterTurn:function()
                                        {
                                            gEx('gDocumentosCGrid').getStore().reload();
                                            if(objBaseConfiguracion.functionAfterTurn)
                                            	objBaseConfiguracion.functionAfterTurn();
                                        },
                        functionAfterSaveDocument:function()
                                                    {
                                                        gEx('gDocumentosCGrid').getStore().reload();
                                                        if(objBaseConfiguracion.functionAfterSaveDocument)
			                                            	objBaseConfiguracion.functionAfterSaveDocument();
                                                    },
                       	
                        functionAfterLoadDocument:function()
                                                {
                                                    setTimeout(function()
                                                                {
                                                                    var body = CKEDITOR.instances.txtDocumento.editable().$;
                                                                    
                                                                    var value = (anchoVentana*100)/960;
                                                                    

                                                                    body.style.MozTransformOrigin = "top left";
                                                                    body.style.MozTransform = "scale(" + (value/100)  + ")";

                                                                    body.style.WebkitTransformOrigin = "top left";
                                                                    body.style.WebkitTransform = "scale(" + (value/100)  + ")";

                                                                    body.style.OTransformOrigin = "top left";
                                                                    body.style.OTransform = "scale(" + (value/100)  + ")";

                                                                    body.style.TransformOrigin = "top left";
                                                                    body.style.Transform = "scale(" + (value/100)  + ")";
                                                                    // IE
                                                                    body.style.zoom = value/100;
                                                                
                                                                    if(objBaseConfiguracion.functionAfterLoadDocument)
						                                            	objBaseConfiguracion.functionAfterLoadDocument();
                                                                },200
                                                            )
                                                    

                                                    
                                                }                             

                     };
            
            
            
            
            
            
            
            
            
            ventana.close();
            mostrarVentanaGeneracionDocumentos(objConf);
            
            
        }
        else
        {
            msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
        }
    }
    obtenerDatosWeb('../paginasFunciones/funcionesModulosEspeciales_SGP.php',funcAjax, 'POST','funcion=144&cadObj='+cadObj,true);
}

function subidaCorrecta_CGridGeneracionDocumentos(file, serverData) 
{
	
	try 
    {
    	file.id = "singlefile";	// This makes it so FileProgress only makes a single UI element, instead of one for each file
        var arrDatos=serverData.split('|');
		if ( arrDatos[0]!='1') 
		{
			
		} 
		else 
		{
        	
			gEx("idArchivo").setValue(arrDatos[1]);
            gEx("nombreArchivo").setValue(arrDatos[2]);
            if(gE('txtFileName'))
	            gE('txtFileName').value=arrDatos[2];
            
			var arrAlertas='';
            var gAlertas=gEx('gAlertas');
            var x;
            var f;
            for(x=0;x<gAlertas.getStore().getCount();x++)
            {
                f=gAlertas.getStore().getAt(x);
                
                if(f.data.fechaAlerta=='')
                {
                    function respDoc2()
                    {
                        gAlertas.startEditing(x,2);
                    }
                    
                    msgBox('Debe ingresar la fecha de la alerta',respDoc2);
                    return;
                }
                
                if(f.data.descripcionAlerta.trim()=='')
                {
                    function respDoc()
                    {
                        gAlertas.startEditing(x,3);
                    }
                    msgBox('Debe ingresar la descripci&oacute;n de la alerta',respDoc);
                    return;
                }
                
                oAlerta='{"fechaAlerta":"'+f.data.fechaAlerta.format('Y-m-d')+'","textoAlerta":"'+cv(f.data.descripcionAlerta)+'"}';
                if(arrAlertas=='')
                    arrAlertas=oAlerta;
                else
                    arrAlertas+=','+oAlerta;
            }
            
            var cadObj='{"idGeneracionDocumento":"-1","tipoDocumento":"'+nodoPlantillaSel.id+'","tituloDocumento":"'+cv(arrDatos[2])+'","perfilValidacion":"'+
            		nodoPlantillaSel.attributes.perfilValidacion+'","fechaLimite":"","modificaSituacionCarpeta":"0","situacion":"'+
                        '","descripcionActuacion":"'+cv(gEx('txtDescripcion').getValue())+'","carpetaAdministrativa":"'+
                        carpetaJudicialBase+'","nombreArchivoTemp":"'+arrDatos[1]+'","nombreArchivo":"'+arrDatos[2]+
                        '","idFormulario":"'+iFormularioBase+'","idRegistro":"'+iRegistroBase+'","arrAlertas":['+arrAlertas+']}';
            
            
            var anchoVentana=(obtenerDimensionesNavegador()[1]*0.9);
            var altoVentana=(obtenerDimensionesNavegador()[0]*0.9);
            
            function funcAjax()
            {
                var resp=peticion_http.responseText;
                arrResp=resp.split('|');
                if(arrResp[0]=='1')
                {
                    objConf={
                                tipoDocumento:nodoPlantillaSel.id,
                                idFormulario:-2,
                                rol:'158_0',
                                ancho:anchoVentana,
                                alto:altoVentana,
                                rolDefault:'158_0',
                                idRegistro: arrResp[1],
                                idRegistroFormato:arrResp[2],
                                functionAfterSignDocument:function()
                        				{
                                       		gEx('gDocumentosCGrid').getStore().reload();
                                            if(objBaseConfiguracion.functionAfterSignDocument)
                                            	objBaseConfiguracion.functionAfterSignDocument();
                                        },
                                functionAfterValidate:function()
                                                {
                                                    gEx('gDocumentosCGrid').getStore().reload();
                                                    if(objBaseConfiguracion.functionAfterValidate)
		                                            	objBaseConfiguracion.functionAfterValidate();
                                                },
                                functionAfterTurn:function()
                                                {
                                                    gEx('gDocumentosCGrid').getStore().reload();
                                                    if(objBaseConfiguracion.functionAfterTurn)
		                                            	objBaseConfiguracion.functionAfterTurn();
                                                },
                                functionAfterSaveDocument:function()
                                                            {
                                                                gEx('gDocumentosCGrid').getStore().reload();
                                                                if(objBaseConfiguracion.functionAfterSaveDocument)
					                                            	objBaseConfiguracion.functionAfterSaveDocument();
                                                            }
  
                             };
                    gEx('gDocumentosCGrid').getStore().reload();
                    gEx('vInfoDocumento').close();
                    mostrarVentanaGeneracionDocumentos(objConf);
                    
                    
                }
                else
                {
                    msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
                }
            }
            obtenerDatosWeb('../paginasFunciones/funcionesModulosEspeciales_SGP.php',funcAjax, 'POST','funcion=144&cadObj='+cadObj,true);
            
			
            
		}
		
	} 
    catch (e) 
	{
		alert(e);
	}
}


function crearGridProgramacionAlerta_CGridGeneracionDocumentos()
{
	var dsDatos=[];
    var alDatos=	new Ext.data.SimpleStore	(
                                                    {
                                                        fields:	[
                                                                   
                                                                    {name: 'fechaAlerta', type:'date', dateFormat:'Y-m-d'},
                                                                    {name: 'descripcionAlerta'}
                                                                ]
                                                    }
                                                );

    alDatos.loadData(dsDatos);
	var chkRow=new Ext.grid.CheckboxSelectionModel();
	
	var cModelo= new Ext.grid.ColumnModel   	(
												 	[
													 	new  Ext.grid.RowNumberer(),
														chkRow,
														{
															header:'Fecha de alerta',
															width:120,
															sortable:true,
															dataIndex:'fechaAlerta',
															editor:{xtype:'datefield'},
															renderer:function(val)
																	{
																		if(!val)
																			return '';
																		return val.format('d/m/Y');
																	}
														},
														{
															header:'Descripci&oacute;n de la alerta',
															width:480,
															sortable:true,
															editor:{ xtype:'textarea',height:80},
															dataIndex:'descripcionAlerta',
															renderer:mostrarValorDescripcion
														}
													]
												);
                                                
	var tblGrid=	new Ext.grid.EditorGridPanel	(
                                                        {
                                                            id:'gAlertas',
                                                            store:alDatos,
                                                            frame:false,
                                                            y:160,
                                                            x:10,
                                                            hidden:!objBaseConfiguracion.mostrarGridProgramacionAlerta,
                                                            clicksToEdit:1,
                                                            cm: cModelo,
                                                            stripeRows :true,
                                                            loadMask:true,
                                                            stripeRows :true,                                                            
                                                            columnLines : true,
                                                            height:190,
                                                            width:680,
                                                            sm:chkRow,
                                                            tbar:	[
                                                            			{
                                                                        	icon:'../images/clock_add.png',
                                                                            cls:'x-btn-text-icon',
                                                                            text:'Programar alerta',
                                                                            handler:function()
                                                                            		{
                                                                                    	var regAlerta= crearRegistro (
                                                                                   										[
                                                                                   											{name: 'fechaAlerta'},
                                                                    														{name: 'descripcionAlerta'}
                                                                                   										
                                                                                   										]
                                                                                    								)
                                                                                    
                                                                                    	var r=new  regAlerta 	(
                                                                                   									{
                                                                                   										fechaAlerta:'',
                                                                                   										descripcionAlerta:''
                                                                                   									}
                                                                                    							)
                                                                                    
                                                                                    
                                                                                    
                                                                                    	tblGrid.getStore().add(r);
                                                                                    	tblGrid.startEditing(tblGrid.getStore().getCount()-1,2);
                                                                                    	
                                                                                    	
                                                                                    	
                                                                                    	
                                                                                    }
                                                                            
                                                                        },'-',
                                                                        {
                                                                        	icon:'../images/delete.png',
                                                                            cls:'x-btn-text-icon',
                                                                            text:'Remover alerta',
                                                                            handler:function()
                                                                            		{
                                                                                    	var fila=tblGrid.getSelectionModel().getSelected();
                                                                                    	if(!fila)
                                                                                    	{
                                                                                    		msgBox('Debe seleccionar la alerta que desea remover');
                                                                                    		return;
                                                                                    	}
                                                                                    	
                                                                                    	function resp(btn)
                                                                                    	{
                                                                                    		if(btn=='yes')
                                                                                    		{
                                                                                    			tblGrid.getStore().remove(fila);
                                                                                    		}
                                                                                    	}
                                                                                    	msgConfirm('Est&aacute; seguro de querer remover la alerta seleccionada?',resp);
                                                                                    	return;
                                                                                    	
                                                                                    	
                                                                                    }
                                                                            
                                                                        }
                                                                        
                                                            		]
                                                        }
                                                    );
	return 	tblGrid;
}

function visualizarDocumentoFinalizado_CGridGeneracionDocumentos(iD,tD)
{
	function funcAjax()
    {
        var resp=peticion_http.responseText;
        arrResp=resp.split('|');
        if(arrResp[0]=='1')
        {
        	var arrNombre=arrResp[2].split('.');
            extension=arrNombre[arrNombre.length-1];
            mostrarVisorDocumentoProceso(extension,arrResp[1]);
        }
        else
        {
            msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
        }
    }
    obtenerDatosWeb('../paginasFunciones/funcionesModulosEspeciales_SGP.php',funcAjax, 'POST','funcion=131&tD='+bD(tD)+'&iF=-2&iR='+bD(iD),true);
}


function subidaCorrecta_CGridGeneracionDocumentosPDF(file, serverData) 
{
	
	try 
    {
    	file.id = "singlefile";	// This makes it so FileProgress only makes a single UI element, instead of one for each file
        var arrDatos=serverData.split('|');
		if ( arrDatos[0]!='1') 
		{
			
		} 
		else 
		{
        	
			
           	if(gE('txtFileName'))
	            gE('txtFileName').value=arrDatos[2];
            
            gEx('wCreateDocumentDocument').close();
			if(objBaseConfiguracion.funcionAfterLoadPDF)
            {
            	objBaseConfiguracion.funcionAfterLoadPDF(arrDatos[1],arrDatos[2]);
            }
			
            
            
		}
		
	} 
    catch (e) 
	{
		alert(e);
	}
}


function fileDialogCompletePDF(numFilesSelected, numFilesQueued) 
{
	
}


function guardarDatosDocumento(cadObj,parametros,ventana)
{
	guardarDatosDocumento_CGridGeneracionDocumentos(cadObj,parametros,ventana);
}