var uploadControl;

Ext.form.TextoBotonField = function(config)
{
	tamanoMaxArchivo=config.tamanoMaxArchivo;
	extensionesPermitidas=config.extensionesPermitidas;
	filaRegistro=(config.filaRegistro==true);
    Ext.form.TextoBotonField.superclass.constructor.call(this, config);
};

Ext.extend(Ext.form.TextoBotonField, Ext.form.TriggerField,  
{
   
   
    invalidText : "El archivo no es v&aacute;lido",
    
    triggerClass : 'x-form-TextoBoton-trigger',
    
    defaultAutoCreate : {tag: "input", readOnly:"true", type: "text", size: "10", maxlength: "7", autocomplete: "off"},

    validateValue : function(value)
	{
        if(!Ext.form.TextoBotonField.superclass.validateValue.call(this, value)){
            return false;
        }
        if(value.length < 1){ // if it's blank and textfield didn't flag it then it's valid
             return true;
        }
        return true;
    },

   
    validateBlur : function()
	{
        return true;
    },

    
    getValue : function()
	{
        return Ext.form.TextoBotonField.superclass.getValue.call(this) || "";
    },

    setValue : function(valor)
	{
		var arrDatos=valor.split('|');

		if(arrDatos.length>1)
		{
			valor=arrDatos[0];
			this.attValor=arrDatos[1];
		}
        Ext.form.TextoBotonField.superclass.setValue.call(this, valor);
    },

   
    onTriggerClick : function(e)
	{

        if(this.disabled){
            return;
        }
		
		mostrarVentanaArchivo(this);
    }
});
Ext.reg('textoBotonField', Ext.form.TextoBotonField);



function mostrarVentanaArchivo(ctrl)
{
	var ventanaAM; 
	var form = new Ext.form.FormPanel(	
										{
											baseCls: 'x-plain',
											layout:'absolute',
											defaultType: 'label',
											items: 	[
														 {
															  xtype:'label',
															  x:10,
															  y:15,
															  html:'Documento a subir: <span id="oblComprobante" style="color:#F00">*</span>'
														  },
														  {
															  x:140,
															  y:10,
															  html:	'<table width="290"><tr><td><div id="uploader"><p>Your browser doesn\'t have Flash, Silverlight or HTML5 support.</p></div></td></tr><tr id="filaAvance" style="display:none"><td align="right"><span style="font-size:11px">Porcentaje de avance: </span><span style="font-size:11px" id="porcentajeAvance"> 0%</span></td></tr></table>'
														  },
														 
														  {
															  x:440,
															  y:11,
															  id:'btnUploadFile',
															  xtype:'button',
															  icon:'../images/add.png',
                                                           	  cls:'x-btn-text-icon',
															  text:'Seleccionar',
															  handler:function()
																	  {
																		  $('#containerUploader').click();
																	  }
														  },
														  {
                                                            x:530,
                                                            y:11,
                                                            width:90,
                                                            id:'btnScanFile',
                                                            icon:'../../images/scanner.png',
                                                            cls:'x-btn-text-icon',
															hidden:ventanaAdjuntaDocumento,
                                                            xtype:'button',
                                                            text:'Escanear',
                                                            handler:function()
                                                                    {
																		gEx('vAddDocumento').hide();
                                                                        var cadObj='{"afterScanFunction":"scanCorrectoDocumentRegistroFormulario"}';
                                                                        var obj={};
                                                                        obj.ancho='100%';
                                                                        obj.alto='100%';
																		obj.url='../scan/tLatisScanner.php';
                                                                        obj.params=[['cadObj',bE(cadObj)]];
																		obj.funcionCerrar=function()
																							{
																								if(gEx('vAddDocumento'))
																									gEx('vAddDocumento').show();
																							};
                                                                        abrirVentanaFancy(obj);
                                                                       
                                                                        
                                                                        
                                                                    }
                                                        },
														  {
															  x:185,
															  y:10,
															  hidden:true,
															  html:	'<div id="containerUploader"></div>'
														  },
														  
														  {
															  x:190,
															  y:0,
															  ctrlAsociado:ctrl,
															  xtype:'hidden',
															  id:'idArchivo'
  
														  },
														  {
															  x:190,
															  y:0,
															  xtype:'hidden',
															  id:'nombreArchivo'
														  } ,
														  {
															  x:160,
															  y:67,
															  xtype:'label',
															  html:'<b><span style="color: #000000 !important;">Si desea remover el documento asociado al registro de click</span></b>&nbsp;<a href="javascript:removerDocumentoAsociado()"><span style="color: #FF0000 !important;"><b>AQU&Iacute;</b></span></a>'
														  }

													]
										}
									);

	var swfu;									
	
	ventanaAM = new Ext.Window(
									{
										id:'vAddDocumento',
										title: 'Subir documento al servidor',
										width: 580+ventanaAdjuntaDocumento,
										height:160,
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
																	var cObj={
																								// Backend settings
																								upload_url: "../paginasFunciones/procesarDocumentoV2.php", //lquevedor
																								file_post_name: "archivoEnvio",
																				 
																								// Flash file settings
																								file_size_limit : ctrl.tamanoMaxArchivo,
																								file_types : ctrl.extensionesPermitidas,			// or you could use something like: "*.doc;*.wpd;*.pdf",
																								file_types_description : "Todos los archivos",
																								file_upload_limit : 0,
																								file_queue_limit : 1,
																				 
																								
																								upload_success_handler : subidaCorrecta2
																							}; 
																	
																	
																	
																	crearControlUploadHTML5(cObj);
																}
															}
												},
										buttons:	[
														{
															
															text: 'Aceptar',
															handler: function()
																	{
																		
																		if(uploadControl.files.length==0)
																		{
																			msgBox('Debe indicar el documento que desea subir al servidor');
																			return;
																		}
																		uploadControl.start();
																		
																		
																		
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

function scanCorrectoDocumentRegistroFormulario(idDocumento,nombreDocumento)
{
	cerrarVentanaFancy(); 
	subidaCorrecta2({}, '1|'+idDocumento+'|'+nombreDocumento);
    
}



function subidaCorrecta2(file, serverData) 
{

	file.id = "singlefile";	// This makes it so FileProgress only makes a single UI element, instead of one for each file
	var arrDatos=serverData.split('|');
	if ( arrDatos[0]!='1') 
	{
	} 
	else 
	{
		var idArchivo=gEx("idArchivo");
		idArchivo.ctrlAsociado.setValue(arrDatos[2]);
		idArchivo.ctrlAsociado.attValor=arrDatos[1];
		if(!idArchivo.ctrlAsociado.filaRegistro)
		{
			gEx("nombreArchivo").setValue(arrDatos[2]);
			
		}
		else
		{
			
			idArchivo.ctrlAsociado.gridEditor.record.set('documentoDigital',arrDatos[2]+'|'+arrDatos[1]);
		}
		
		
		gEx('vAddDocumento').close();
		
		if(idArchivo.ctrlAsociado.funcionAfterUpload)
		{
			idArchivo.ctrlAsociado.funcionAfterUpload(idArchivo.ctrlAsociado);
		}
		
		
		
	}
		
}

function removerDocumentoAsociado()
{
	var ctrl;
	var idArchivo=gEx('idArchivo');
	function resp(btn)
	{
		if(btn=='yes')
		{
			if(!idArchivo.ctrlAsociado.filaRegistro)
			{
				idArchivo.ctrlAsociado.setValue('');
				idArchivo.ctrlAsociado.attValor='';
			}
			else
			{
				idArchivo.ctrlAsociado.gridEditor.record.set('documentoDigital','');
			}
			gEx('vAddDocumento').close();
		}
	}
	msgConfirm('Est&aacute; seguro de querer remover el documento asociado al registro?',resp);
}

function progresoSubida(file, bytesLoaded, bytesTotal) 
{
	
	var percent = Math.ceil((bytesLoaded / bytesTotal) * 100);
	gE('etPorcentaje').innerHTML=percent;
}