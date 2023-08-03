<?php
	session_start();
	include("configurarIdiomaJS.php");
	include("conexionBD.php");
?>

function mostrarBitacoraSituacionObjeto(tObjeto,iRegistro,arrSituacion,titulo)
{
	var form = new Ext.form.FormPanel(	
										{
											baseCls: 'x-plain',
											layout:'border',
                                            frame:false,
											defaultType: 'label',
											items: 	[
														crearGridHistorialObjeto(tObjeto,iRegistro,arrSituacion)

													]
										}
									);
	
	var ventanaAM = new Ext.Window(
									{
										title: !titulo?'Historial':titulo,
										width: 900,
										height:450,
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
															text: '<?php echo $etj["lblBtnAceptar"]?>',
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

function registrarCambioSituacionObjeto(objCobj)
{
	var cmbSituacionActual=crearComboExt('cmbSituacionActual',objCobj.arrSituacionObjeto,180,5,200);
	var form = new Ext.form.FormPanel(	
										{
											baseCls: 'x-plain',
											layout:'absolute',
											defaultType: 'label',
											items: 	[
                                            			{
                                                        	x:10,
                                                            y:10,
                                                            html:'Situaci&oacute;n actual:'
                                                        },
                                                        cmbSituacionActual,
                                                        {
                                                        	x:10,
                                                            y:40,
                                                            html:'Comentarios adicionales:'
                                                        },
                                                        {
                                                        	x:10,
                                                            y:70,
                                                            id:'txtComentariosAdicionales',
                                                            xtype:'textarea',
                                                            width:500,
                                                            height:60
                                                        }
													]
										}
									);
	
	var ventanaAM = new Ext.Window(
									{
										title: !objCobj.tituloVentana?'Cambiar Situaci&oacute;n':objCobj.tituloVentana,
										width: 550,
										height:230,
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
															
															text: '<?php echo $etj["lblBtnAceptar"]?>',
                                                            
															handler: function()
																	{
																		if(cmbSituacionActual.getValue()=='')
                                                                        {
                                                                        	function resp()
                                                                            {
                                                                            	cmbSituacionActual,focus();
                                                                            }
                                                                            mxgBox('Debe indicar la situaci&oacute;n a la cual desea cambiar el registro',resp);
                                                                            return;
                                                                        }
                                                                        
                                                                        var cadObj='{"tipoObjeto":"'+objCobj.tipoObjeto+'","idRegistro":"'+objCobj.idRegistro+
                                                                        		'","situacionActual":"'+cmbSituacionActual.getValue()+
                                                                                '","comentariosAdicionales":"'+cv(gEx('txtComentariosAdicionales').getValue())+'"}';
                                                                        
                                                                        
                                                                        function funcAjax()
                                                                        {
                                                                            var resp=peticion_http.responseText;
                                                                            arrResp=resp.split('|');
                                                                            if(arrResp[0]=='1')
                                                                            {
                                                                                if(objCobj.funcAfterChange)
                                                                                {
                                                                                	objCobj.funcAfterChange();
                                                                                }
                                                                                ventanaAM.close();
                                                                            }
                                                                            else
                                                                            {
                                                                                msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
                                                                            }
                                                                        }
                                                                        obtenerDatosWeb('../paginasFunciones/funcionesModulosEspeciales_BitacoraObjeto.php',funcAjax, 'POST','funcion=2&cadObj='+cadObj,true);
                                                                        
                                                                        
                                                                        
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


function crearGridHistorialObjeto(tObjeto,iRegistro,arrSituacion)
{
	var lector= new Ext.data.JsonReader({
                                            
                                            totalProperty:'numReg',
                                            fields: [
                                               			{name:'idRegistro'},
                                                        {name:'fechaOperacion', type:'date', dateFormat:'Y-m-d H:i:s'},
                                                        {name:'etapaOriginal'},
		                                                {name:'etapaCambio'},
		                                                {name:'responsable'},
                                                        {name: 'comentarios'}
                                            		],
                                            root:'registros'
                                            
                                        }
                                      );
	 
                                                                                      
	var alDatos=new Ext.data.GroupingStore({
                                                            reader: lector,
                                                            proxy : new Ext.data.HttpProxy	(

                                                                                              {

                                                                                                  url: '../paginasFunciones/funcionesModulosEspeciales_BitacoraObjeto.php'

                                                                                              }

                                                                                          ),
                                                            sortInfo: {field: 'fechaOperacion', direction: 'DESC'},
                                                            groupField: 'fechaOperacion',
                                                            remoteGroup:false,
				                                            remoteSort: true,
                                                            autoLoad:true
                                                            
                                                        }) 
	alDatos.on('beforeload',function(proxy)
    								{
                                    	proxy.baseParams.funcion='1';
                                        proxy.baseParams.tipoObjeto=tObjeto;
                                        proxy.baseParams.idRegistro=iRegistro;
                                        
                                    }
                        )   
       
        var cModelo= new Ext.grid.ColumnModel   	(
                                                        [
                                                            new  Ext.grid.RowNumberer(),
                                                            {
                                                                header:'Fecha',
                                                                width:150,
                                                                sortable:true,
                                                                align:'center',
                                                                dataIndex:'fechaOperacion',
                                                                renderer:function(val)
                                                                		{
                                                                        
                                                                        	return formatoTituloBitacoraObjeto(val.format('d')+' de '+arrMeses[parseInt(val.format('m'))-1][1]+' de '+val.format('Y')+'<br>('+val.format('H:i:s')+' hrs.)');
                                                                        }
                                                            },
                                                            {
                                                                header:'Etapa original',
                                                                width:200,
                                                                sortable:true,
                                                                dataIndex:'etapaOriginal',
                                                                renderer:formatoTitulo2BitacoraObjeto
                                                            },
                                                            {
                                                                header:'Etapa cambio',
                                                                width:200,
                                                                sortable:true,
                                                                dataIndex:'etapaCambio',
                                                                renderer:formatoTitulo2BitacoraObjeto
                                                            },                                                            
                                                            {
                                                                header:'Responsable',
                                                                width:250,
                                                                sortable:true,
                                                                dataIndex:'responsable',
                                                                renderer:formatoTitulo3BitacoraObjeto
                                                            }
                                                            
                                                        ]
                                                    );
                                                    
        var tblGrid=	new Ext.grid.GridPanel	(
                                                    {
                                                        id:'gridHistorialObjeto',
                                                        store:alDatos,
                                                        region:'center',
                                                        frame:false,
                                                        border:true,
                                                        cm: cModelo,
                                                        columnLines : false,
                                                        stripeRows :true,
                                                        loadMask:true,
                                                                                                                        
                                                        view:new Ext.grid.GroupingView({
                                                                                            forceFit:false,
                                                                                            showGroupName: false,
                                                                                            enableGrouping :false,
                                                                                            enableNoGroups:false,
                                                                                            enableGroupingMenu:false,
                                                                                            hideGroupedColumn: false,
                                                                                            startCollapsed:false,
                                                                                            enableRowBody:true,
                                                                                            getRowClass : formatearFilaHistorialBitacoraObjeto
                                                                                        })
                                                    }
                                                 );

		tblGrid.  arrEtapas=arrSituacion;                                               
        return 	tblGrid;	
}

function formatearFilaHistorialBitacoraObjeto(record, rowIndex, p, ds)
{
	var xf = Ext.util.Format;
    p.body = '<BR><p style="margin-left: 4em;margin-right: 4em;text-align:justify"><br><span class="menu"><span style="color: #001C02">Comentarios adicionales:</span><br><br><span style="color: #3B3C3B">' + ((record.data.comentarios.trim()=="")?"(Sin comentarios)":record.data.comentarios) + '</span></p><br><br><br>';
    return 'x-grid3-row-expanded';
}

function formatoTituloBitacoraObjeto(val)
{
	return '<span style="font-size:11px; color:#040033">'+val+'</span>';
}

function formatoTitulo2BitacoraObjeto(val)
{
	var pos=existeValorMatriz(gEx('gridHistorialObjeto').arrEtapas,val);
    var leyenda=gEx('gridHistorialObjeto').arrEtapas[pos][1];
    if(gEx('gridHistorialObjeto').arrEtapas[pos].length>2)
    {
    	leyenda='<span style="color:#'+gEx('gridHistorialObjeto').arrEtapas[pos][2]+'; font-weight:bold">'+leyenda+'</span>';
    }
	return '<div style="font-size:11px; color:#040033;; height:45px; word-wrap: break-word;white-space: normal; ">'+leyenda+'</div>';
}

function formatoTitulo3BitacoraObjeto(val)
{
	return '<div style="font-size:11px; height:45px; color:#040033; word-wrap: break-word;white-space: normal;"><img src="../images/user_gray.png">'+(val)+'</div>';
}


function mostrarVentanaCambioSituacionObjetoComentario(objCobj)
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
                                                            html:objCobj.leyendaComentario
                                                        },
                                                        {
                                                        	x:10,
                                                            y:40,
                                                            id:'txtComentariosAdicionales',
                                                            xtype:'textarea',
                                                            width:500,
                                                            height:60
                                                        }
													]
										}
									);
	
	var ventanaAM = new Ext.Window(
									{
										title: objCobj.tituloVentana,
										width: 550,
										height:190,
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
                                                                	gEx('txtComentariosAdicionales').focus(false, 500);
																}
															}
												},
										buttons:	[
														{
															
															text: '<?php echo $etj["lblBtnAceptar"]?>',
                                                            
															handler: function()
																	{
                                                                    	if(objCobj.comentarioObligatorio)
                                                                        {
                                                                            if(gEx('txtComentariosAdicionales').getValue()=='')
                                                                            {
                                                                                function resp()
                                                                                {
                                                                                    gEx('txtComentariosAdicionales').focus();
                                                                                }
                                                                                msgBox('El campo es obligatorio',resp);
                                                                                return;
                                                                            }
                                                                        }
                                                                        
                                                                        var cadObj='{"tipoObjeto":"'+objCobj.tipoObjeto+'","idRegistro":"'+objCobj.idRegistro+
                                                                        		'","situacionActual":"'+objCobj.situacionActual+
                                                                                '","comentariosAdicionales":"'+cv(gEx('txtComentariosAdicionales').getValue())+'"}';
                                                                        
                                                                        
                                                                        
                                                                        if(objCobj.solicitarConfirmacion)
                                                                        {
                                                                            function respAux(btn)
                                                                            {
                                                                            	if(btn=='yes')
                                                                                {
                                                                                	function funcAjax()
                                                                                    {
                                                                                        var resp=peticion_http.responseText;
                                                                                        arrResp=resp.split('|');
                                                                                        if(arrResp[0]=='1')
                                                                                        {
                                                                                            if(objCobj.funcAfterChange)
                                                                                            {
                                                                                                objCobj.funcAfterChange();
                                                                                            }
                                                                                            ventanaAM.close();
                                                                                        }
                                                                                        else
                                                                                        {
                                                                                            msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
                                                                                        }
                                                                                    }
                                                                                    obtenerDatosWeb('../paginasFunciones/funcionesModulosEspeciales_BitacoraObjeto.php',funcAjax, 'POST','funcion=2&cadObj='+cadObj,true);
                                                                                }
                                                                            
                                                                            }
                                                                            msgConfirm(objCobj.lenyedaConfirmacion,respAux);
																		}
                                                                        else
                                                                        {
                                                                        	function funcAjax()
                                                                            {
                                                                                var resp=peticion_http.responseText;
                                                                                arrResp=resp.split('|');
                                                                                if(arrResp[0]=='1')
                                                                                {
                                                                                    if(objCobj.funcAfterChange)
                                                                                    {
                                                                                        objCobj.funcAfterChange();
                                                                                    }
                                                                                    ventanaAM.close();
                                                                                }
                                                                                else
                                                                                {
                                                                                    msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
                                                                                }
                                                                            }
                                                                            obtenerDatosWeb('../paginasFunciones/funcionesModulosEspeciales_BitacoraObjeto.php',funcAjax, 'POST','funcion=2&cadObj='+cadObj,true);
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