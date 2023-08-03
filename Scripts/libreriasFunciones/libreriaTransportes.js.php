

objConf.funcion=23;
objConf.params.idUnidad=idUnidad;
objConf.params.idAsignacion=idAsignacion;
objConf.params.idChofer=idChofer;
objConf.params.fecha='<?php echo date("Y-m-d")?>';
objConf.params.tipoEvaluacion=1;




function crearGridComentarios(objConf)
{
	 var lector= new Ext.data.JsonReader({
                                            
                                            totalProperty:'numReg',
                                            fields: [
                                               			{name:'comentario'},
		                                                {name: 'tipoComentario'},
		                                                {name:'icono'},
                                                        {name:'accion'}
                                            		],
                                            root:'registros'
                                            
                                        }
                                      );
	 
                                                                                      
	var alDatos=new Ext.data.GroupingStore({
                                                            reader: lector,
                                                            proxy : new Ext.data.HttpProxy	(

                                                                                              {

                                                                                                  url: '../paginasFunciones/funcionesModulosEspeciales_Transportes.php'

                                                                                              }

                                                                                          ),
                                                            sortInfo: {field: 'tipoComentario', direction: 'ASC'},
                                                            groupField: 'tipoComentario',
                                                            remoteGroup:false,
				                                            remoteSort: false,
                                                            autoLoad:false
                                                            
                                                        }) 
	alDatos.on('beforeload',function(proxy)
    								{
                                    	proxy.baseParams.funcion=objConf.funcion;
                                        for(var i in objConf.params)
                                        {
                                        	proxy.baseParams[i]=objConf.params[i];
                                        }
                                        
                                    }
                        )   

	 var expander = new Ext.ux.grid.RowExpander({
                                                column:0,
                                                expandOnEnter:false,
                                                tpl : new Ext.Template(
                                                    '<table >',
                                                    '<tr><td style="padding:5px; color:#000;">{comentario}</td></tr>',
                                                    '</table>'
                                                )
                                            });

       
        var cModelo= new Ext.grid.ColumnModel   	(
                                                        [
                                                            new  Ext.grid.RowNumberer(),
                                                            expander,
                                                             {
                                                                header:'',
                                                                width:40,
                                                                sortable:true,
                                                                dataIndex:'icono'
                                                            },
                                                            {

                                                                header:'Observaciones',
                                                                width:590,
                                                                sortable:true,
                                                                dataIndex:'comentario'
                                                            },
                                                            {
                                                                header:'Tipo de comentario',
                                                                width:350,
                                                                sortable:true,
                                                                dataIndex:'tipoComentario'
                                                            }
                                                        ]
                                                    );


	var posX=25;
    var posY=230;
    if(objConf.posX)
    	posX=objConf.posX;
    if(objConf.posY)
    	posY=objConf.posY;         
                                               
        var tblGrid=	new Ext.grid.GridPanel	(
                                                            {
                                                                id:'gridComentarios',
                                                                store:alDatos,
                                                                width:700,
                                                                height:220,
                                                                x:posX,
                                                                y:posY,
                                                                frame:false,
                                                                cm: cModelo,
                                                                stripeRows :true,
                                                                loadMask:true,
                                                                plugins:[expander],
                                                                columnLines : true,
                                                                bbar:	[
                                                                			{
                                                                                icon:'../images/icon_big_tick.gif',
                                                                                cls:'x-btn-text-icon',
                                                                                height:20,
                                                                                id:'btnRegistrar',
                                                                                text:'Registrar Asistencia',
                                                                                handler:function()
                                                                                        {
                                                                                        	if(idUnidad==-1)
                                                                                            {
                                                                                            	function resp2()
                                                                                                {
                                                                                                	gEx('txtNoUnidad').focus();
                                                                                                }
                                                                                                msgBox('Debe indicar la unidad cuya asistencia desea registrar',resp2);
                                                                                                return;
                                                                                            }
                                                                                            
                                                                                            if(idChofer==-1)
                                                                                            {
                                                                                            	function resp()
                                                                                                {
                                                                                                	gEx('cmbChofer').focus();
                                                                                                }
                                                                                                msgBox('Debe indicar el chofer asignado a la unidad',resp);
                                                                                                return;
                                                                                            }
                                                                                            mostrarVentanaConfirmacion();
                                                                                            return;
                                                                                            if(accion=='3')
                                                                                            {
                                                                                            	
                                                                                            	registrarAsistencia();
                                                                                            }
                                                                                            else
                                                                                            {
                                                                                            	
                                                                                            	mostrarVentanaConfirmacion();
                                                                                            }
                                                                                        }
                                                                                
                                                                            },'-',
                                                                            {
                                                                                icon:'../images/arrow_refresh.PNG',
                                                                                cls:'x-btn-text-icon',
                                                                                height:20,
                                                                                
                                                                                text:'Volver a Evaluar Observaciones',
                                                                                handler:function()
                                                                                        {
                                                                                        	buscarComentariosUnidadChofer();
                                                                                        }
                                                                                
                                                                            }	
                                                                            ,'-',
                                                                            {
                                                                                icon:'../images/cross.png',
                                                                                cls:'x-btn-text-icon',
                                                                                height:20,
                                                                                text:'Cancelar operaci&oacute;n',
                                                                                handler:function()
                                                                                        {
                                                                                        	limpiarCapturaUnidad();	
                                                                                            limpiarCapturaChofer();
                                                                                        }
                                                                                
                                                                            }		
                                                                		],
                                                                view:new Ext.grid.GroupingView({
                                                                                                    forceFit:false,
                                                                                                    showGroupName: false,
                                                                                                    enableGrouping :true,
                                                                                                    enableNoGroups:false,
                                                                                                    enableGroupingMenu:false,
                                                                                                    hideGroupedColumn: true,
                                                                                                    startCollapsed:false
                                                                                                })
                                                            }
                                                        );
	tblGrid.getStore().on('load',function()
    							{
                                	var x;
                                    var fila;
                                    accion='3';
                                    for(x=0;x<tblGrid.getStore().getCount();x++)
                                    {
                                    	fila=tblGrid.getStore().getAt(x);
                                        switch(fila.data.accion)
                                        {
                                        	case '1':
                                            	accion='1';
                                            break;
                                            case '2':
                                            	accion='2';
                                                var observaciones=obtenerObservacionesGrid('gridComentarios');
                                                var objNotificacion='{"datosEvento":"'+bE('{"idUnidad":"'+idUnidad+'","idChofer":"'+idChofer+'","fecha":"<?php echo date("Y-m-d")?>","idAsignacion":"'+idAsignacion+'"}')+'","tipoNotificacion":"1","respuesta":"0","accionEvento":"2","observaciones":"'+observaciones+'","comentarios":""}';
                                                registrarBitacoraNotificaciones(objNotificacion,idUnidad,idChofer);
                                                break;
                                            break;
                                            case '3':
                                            break;
                                        }
                                        
                                    }
                                    
                                    switch(accion)
                                    {
                                    	case '1':
                                            gEx('btnRegistrar').enable();
                                        break;
                                        case '2':
                                            gEx('btnRegistrar').disable();
                                        break;
                                        case '3':
                                        	gEx('btnRegistrar').enable();
                                        break;
                                    
                                    }
                                    
                                	
                                }
    			)                                                        
        return 	tblGrid;
}	

function mostrarVentanaConfirmacion()
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
                                                            xtype:'label',
                                                            html:'<span class="letraAzulSimple" style="color:#000">Se ha detectado las siguientes observaciones que requieren su confirmaci&oacute;n:</span> '
                                                            
                                                        },
                                                        crearGridConfirmacionObservaciones(),
                                                        {
                                                        	x:10,
                                                            y:260,
                                                            xtype:'label',
                                                            html:'<span class="letraAzulSimple" style="color:#000">Comentarios adicionales:</span>'
                                                            
                                                        },
                                                        {
                                                        	x:180,
                                                            y:255,
                                                            width:500,
                                                            height:80,
                                                            xtype:'textarea',
                                                            id:'txtComentarios'
                                                        },
                                                        {
                                                        	x:10,
                                                            y:350,
                                                            xtype:'label',
                                                            html:'<span class="letraAzulSimple" style="color:#900"><b>Est&aacute; seguro de querer continuar con el registro de la asistencia?</b></span>'
                                                            
                                                        }

													]
										}
									);
	
	var ventanaAM = new Ext.Window(
									{
										title: 'Confirmaci&oacute;n de observaciones',
										width: 770,
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
                                                                	gEx('txtComentarios').focus(false,500);
																}
															}
												},
										buttons:	[
														{
															
															text: 'S&iacute;, confirmo la acci&oacute;n',
                                                            height:25,
                                                            width:140,
															handler: function()
																	{
                                                                    
                                                                    	if(verificarAutorizacion=='1')
                                                                    	{
                                                                        	
                                                                        }
                                                                    	else
                                                                        	ejecutarRegistro();
																	}
														},
														{
															text: 'NO, detener la acci&oacute;n',
                                                            height:25,
                                                            width:140,
															handler:function()
																	{
                                                                    
                                                                     	var observaciones=obtenerObservacionesGrid('gridComentariosConf');
                                                                    	var objNotificacion='{"datosEvento":"'+bE('{"idUnidad":"'+idUnidad+'","idChofer":"'+idChofer+'","fecha":"<?php echo date("Y-m-d")?>","idAsignacion":"'+idAsignacion+'"}')+'","tipoNotificacion":"1","respuesta":"0","accionEvento":"1","observaciones":"'+observaciones+'","comentarios":"'+cv(gEx('txtComentarios').getValue())+'"}';
                                                                    	registrarBitacoraNotificaciones(objNotificacion,idUnidad,idChofer);
																		ventanaAM.close();
																	}
														}
													]
									}
								);
	ventanaAM.show();
    
    
    var gridComentariosConf=gEx('gridComentariosConf');
    var gridComentarios=gEx('gridComentarios');
    var x;
    var fila;
    for(x=0;x<gridComentarios.getStore().getCount();x++)
    {
    	fila=gridComentarios.getStore().getAt(x);
        if(fila.data.accion=='1')
        {
        	var filaAux=fila.copy();
            gridComentariosConf.getStore().add(filaAux);
        }
    }
}

function registrarAsistencia(comentarios)
{
	
	var c='';
    if(comentarios)
    	c=comentarios;
	function funcAjax()
    {
        var resp=peticion_http.responseText;
        arrResp=resp.split('|');
        if(arrResp[0]=='1')
        {
        	function respAux()
            {
            	idChofer=-1;
                gEx('cmbChofer').setValue('');
                gEx('txtFechaNacimiento').setValue(''); 
                gEx('txtEdad').setValue(''); 
                gEx('txtNoLicencia').setValue(''); 
                gEx('txtTipoLicencia').setValue(''); 
                gEx('txtVencimiento').setValue(''); 
                gE('imgUsuario').src='../images/imgNoDisponible.jpg';
                idUnidad=-1;
                
                gEx('txtNoUnidad').setValue();
                gEx('txtMarca').setValue('');
                gEx('txtModelo').setValue('');
                gEx('txtNoPlacas').setValue('');
                gEx('txtNoMotor').setValue('');
                gEx('txtPropietario').setValue('');
                gEx('txtRuta').setValue('');
                gEx('txtNoUnidad').focus(false,500);
            }
            msgBox('La asistencia ha sido registrada con &eacute;xito',respAux);
            
            
        }
        else
        {
            msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
        }
    }
    obtenerDatosWeb('../paginasFunciones/funcionesModulosEspeciales_Transportes.php',funcAjax, 'POST','funcion=19&comentarios='+cv(c)+'&idUnidad='+idUnidad+'&idChofer='+idChofer+'&idAsignacion='+idAsignacion,true);
    
}

function crearGridConfirmacionObservaciones()
{
	var lector= new Ext.data.JsonReader({
                                            
                                            totalProperty:'numReg',
                                            fields: [
                                               			{name:'comentario'},
		                                                {name: 'tipoComentario'},
		                                                {name:'icono'},
                                                        {name:'accion'}
                                            		],
                                            root:'registros'
                                            
                                        }
                                      );
	 
                                                                                      
	var alDatos=new Ext.data.GroupingStore({
                                                            reader: lector,
                                                            proxy : new Ext.data.HttpProxy	(

                                                                                              {

                                                                                                  url: '../paginasFunciones/funcionesModulosEspeciales_Transportes.php'

                                                                                              }

                                                                                          ),
                                                            sortInfo: {field: 'tipoComentario', direction: 'ASC'},
                                                            groupField: 'tipoComentario',
                                                            remoteGroup:false,
				                                            remoteSort: false,
                                                            autoLoad:false
                                                            
                                                        }) 
	  

	 var expander = new Ext.ux.grid.RowExpander({
                                                column:0,
                                                expandOnEnter:false,
                                                tpl : new Ext.Template(
                                                    '<table >',
                                                    '<tr><td style="padding:5px; color:#000;">{comentario}</td></tr>',
                                                    '</table>'
                                                )
                                            });

       
        var cModelo= new Ext.grid.ColumnModel   	(
                                                        [
                                                            new  Ext.grid.RowNumberer(),
                                                            expander,
                                                             {
                                                                header:'',
                                                                width:40,
                                                                sortable:true,
                                                                dataIndex:'icono'
                                                            },
                                                            {
                                                                header:'Observaciones',
                                                                width:590,
                                                                sortable:true,
                                                                dataIndex:'comentario'
                                                            }
                                                        ]
                                                    );
                                                    
        var tblGrid=	new Ext.grid.GridPanel	(
                                                            {
                                                                id:'gridComentariosConf',
                                                                store:alDatos,
                                                                width:700,
                                                                height:200,
                                                                x:10,
                                                                y:40,
                                                                frame:false,
                                                                cm: cModelo,
                                                                stripeRows :true,
                                                                loadMask:true,
                                                                plugins:[expander],
                                                                columnLines : true,
                                                                view:new Ext.grid.GroupingView({
                                                                                                    forceFit:false,
                                                                                                    showGroupName: false,
                                                                                                    enableGrouping :true,
                                                                                                    enableNoGroups:false,
                                                                                                    enableGroupingMenu:false,
                                                                                                    hideGroupedColumn: true,
                                                                                                    startCollapsed:false
                                                                                                })
                                                            }
                                                        );	
	return tblGrid;                                                        
}

function obtenerObservacionesGrid(idGrid)
{
	var grid=gEx(idGrid);
    var x;
    var fila;
    var arrObservaciones='';
    for(x=0;x<grid.getStore().getCount();x++)
    {
    	fila=grid.getStore().getAt(x);
        o='{"accion":"'+fila.data.accion+'","tipoComentario":"'+fila.data.tipoComentario+'","observacion":"'+escaparEnter(fila.data.comentario.replace(/"/gi,'\\"'))+'"}';
        if(arrObservaciones=='')
        	arrObservaciones=o;
        else
        	arrObservaciones+=','+o;
        
        
    }
    return bE('['+arrObservaciones+']');
}

function buscarComentariosUnidadChofer()
{
	var iChofer=idChofer;
    var iAsignacion=idAsignacion;
    var iUnidad=idUnidad;
	gEx('gridComentarios').getStore().load	(
    
    											{
                                                	url: '../paginasFunciones/funcionesModulosEspeciales_Transportes.php',
                                                    params:	{
                                                    			funcion:'23',
                                                                idUnidad:iUnidad,
                                                                idAsignacion:iAsignacion,
                                                                idChofer:iChofer,
                                                                fecha:'<?php echo date("Y-m-d")?>',
                                                                tipoEvaluacion:1
                                                    		}
                                                }
    										)	
}