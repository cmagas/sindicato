<?php
	session_start();
	include("configurarIdiomaJS.php");
	include("conexionBD.php");
	
	$idUsuario=-1;
	if(isset($_SESSION["idUsr"]))
		$idUsuario=$_SESSION["idUsr"];
	
?>
var ejecutarAccionBiometrico=null;
var verificarAutorizacion=0;
var idUsuarioAutorizacion=<?php echo $idUsuario?>;
	
/*
objConf.url='../paginasFunciones/funcionesModulosEspeciales_Transportes.php';
objConf.funcion=45;
objConf.cadObj=cadObj;
obj.tipoNotificacion=7;
obj.dEventos='{}';
obj.continuarOperacion=function (objConf)
						{
                        	objConf.ejecucionFinal();
                        }
                        
obj.cancelarOperacion=function ()
						{
                        }
                                                

objConf.funcionEjecucionCorrecta=function()
								{
                                	actualizarGrids();
                                    ventanaAM.close();
                                    msgBox('La solicitud de reemplazo de unidad ha sido enviada a autorizaci&oacute;n');
                                }


objConf.funcionEjecucionCierreVObservaciones=NULL;

*/


function ejecutarAccionVerificacionSituacion(objConf)
{
   if(objConf.verificarTipoNotificacion)                         
   {
        function funcAjax()
        {
            var resp=peticion_http.responseText;
            arrResp=resp.split('|');
            if(arrResp[0]=='1')
            {
            
                if(arrResp[1]=='1')
                {
                    verificarAutorizacion=1;
                    
                    mostrarVentanaAutorizacionBiometricoMensaje(objConf);
                }
                else
                {
                    verificarSituacionAccion(objConf);
                }
            }
            else
            {
                msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
            }
        }
        obtenerDatosWeb('../paginasFunciones/funcionesModulosEspeciales_Transportes.php',funcAjax, 'POST','funcion=46&idAccion='+objConf.tipoNotificacion,true);
   }                         
   else
   {                         
       if((typeof('verificarAutorizacion')!='undefined')&&(verificarAutorizacion=='1'))
        {
            mostrarVentanaAutorizacionBiometricoMensaje(objConf);
        }
        else
        {
           verificarSituacionAccion(objConf);

        }
    }
    
    
}


function verificarSituacionAccion(objConf)
{
	function funcAjax()
    {
        var resp=peticion_http.responseText;
        arrResp=resp.split('|');
        if(arrResp[0]=='1')
        {
            switch(arrResp[1])
            {
                case '0':
                    objConf.aComentarios=arrResp[2];
                    objConf.continuarOperacion=function(obj)
                    							{
                                                	function funcAjax2()
                                                    {
                                                        var resp=peticion_http.responseText;
                                                        arrResp=resp.split('|');
                                                        if(arrResp[0]=='1')
                                                        {
                                                        	if(obj.ejecucionFinal)
	                                                        	obj.ejecucionFinal();
                                                            if(gEx('vObservacionesError'))
	                                                        	gEx('vObservacionesError').close();
                                                            objConf.funcionEjecucionCorrecta();
                                                        }
                                                        else
                                                        {
                                                            msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
                                                        }
                                                    }
                                                	 obtenerDatosWeb(objConf.url,funcAjax2, 'POST','funcion='+objConf.funcion+'&cadObj='+objConf.cadObj.replace('"validar":"1"','"validar":"0"'),true);
                                                	
                                                }
                    
                    mostrarVentanaMensajeError(objConf);
                break;
                case '1':
                    objConf.funcionEjecucionCorrecta();
                break;
            }
        }
        else
        {
            msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
        }
    }
    obtenerDatosWeb(objConf.url,funcAjax, 'POST','funcion='+objConf.funcion+'&cadObj='+objConf.cadObj,true);

}


function mostrarVentanaMensajeError(objConf)
{
	funcionEjecucion=null;
	var arrComentarios=eval(objConf.aComentarios);
    var gGrid=crearGridComentariosErr(objConf);
    
    var fila;
	accion='1';
    var x;
    var r;
    var reg=crearRegistro([
                              {name:'comentario'},
                              {name: 'tipoComentario'},
                              {name:'icono'},
                              {name:'accion'}
                          ]);
    for(x=0;x<arrComentarios.length;x++)
    {
    	r=new reg(arrComentarios[x]);
    
    	gGrid.getStore().add(r);
        
        switch(arrComentarios[x].accion)
        {
            case '1':
               
            break;
            case '2':
                 accion='2';
            break;
            case '3':
                accion='3';
               	x=arrComentarios.length;
                gEx('btnRegistrar').disable();
				break;
            break;
        }
    }
    var lblMensaje='';
    switch(accion)
    {
    	case '2':
        	lblMensaje='<span class="letraAzulSimple" style="color:#000">Se han detectado las siguientes observaciones que requieren se confirme la ejecuci&oacute;n de la operaci&oacute;n:</span>';
        break;
        case '3':
        	lblMensaje='<span class="letraAzulSimple" style="color:#000">Se han detectado las siguientes observaciones que impiden se realice la ejecuci&oacute;n de la operaci&oacute;n:</span>';
            
            var observaciones=aComentarios;
            var objNotificacion='{"datosEvento":'+objConf.dEventos+',"tipoNotificacion":"'+objConf.tipoNotificacion+'","respuesta":"0","accionEvento":"3","observaciones":"'+bE(observaciones)+'","comentarios":"","verificarAutorizacion":"'+verificarAutorizacion+'","idUsuarioAutorizacion":"0"}';
            registrarBitacoraNotificaciones(objNotificacion);
            
            
        break;
    }    
    
    var form = new Ext.form.FormPanel(	
										{
											baseCls: 'x-plain',
											layout:'absolute',
											defaultType: 'label',
											items: 	[
                                            			{
                                                        	x:10,
                                                            y:10,
                                                            html:'<span style="color:#000">'+lblMensaje+'</span>'
                                                        },
                                            
														gGrid														
													]
										}
									);
	
	var ventanaAM = new Ext.Window(
									{
										title: 'Observaciones',
                                        id:'vObservacionesError',
										width: 760,
										height:430,
										layout: 'fit',
										plain:true,
										modal:true,
										bodyStyle:'padding:5px;',
										buttonAlign:'center',
										items: form,
										listeners : {
                                        			close:	function()
                                                    		{
                                                    			if(objConf.funcionEjecucionCierreVObservaciones)
                                                                {
                                                                	funcionEjecucion=objConf.funcionEjecucionCierreVObservaciones;
                                                                }
                                                    		},
													show : {
																buffer : 10,
																fn : function() 
																{
																}
															}
												}
										
									}
								);
	ventanaAM.show();	
    
}

function crearGridComentariosErr(objConf)
{
	 var lector= new Ext.data.JsonReader({
                                            
                                            totalProperty:'numReg',
                                            fields: [
                                            			{name: 'entidadValidacion'},
                                                        {name: 'idEntidadValidacion'},
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
                                                column:3,
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
                                                    
        var tblGrid=	new Ext.grid.GridPanel	(
                                                            {
                                                                id:'gridComentarios',
                                                                store:alDatos,
                                                                width:710,
                                                                height:340,
                                                                x:10,
                                                                y:40,
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
                                                                                text:'Continuar Operaci&oacute;n',
                                                                                handler:function()
                                                                                        {
                                                                                        	objConf.ejecucionFinal=function()
                                                                                            						{
                                                                                                                    	var observaciones=obtenerObservacionesGrid('gridComentarios');     
                                                                                                                    	var objNotificacion='{"datosEvento":'+objConf.dEventos+',"tipoNotificacion":"'+objConf.tipoNotificacion+'","respuesta":"1","accionEvento":"1","observaciones":"'+
                                                                                                                                            bE(observaciones)+'","comentarios":"","verificarAutorizacion":"'+verificarAutorizacion+'","idUsuarioAutorizacion":"'+idUsuarioAutorizacion+'"}';
                                                                                                                        registrarBitacoraNotificaciones(objNotificacion); 
                                                                                                                    }
                                                                                                                    
                                                                                                                    
                                                                                                                 
                                                                                              
                                                                                        	if(typeof(objConf.continuarOperacion)!='undefined')
                                                                                            {
                                                                                                objConf.continuarOperacion(objConf);
                                                                                                
                                                                                            }
                                                                                        }
                                                                                
                                                                            },'-',
                                                                            {
                                                                                icon:'../images/cross.png',
                                                                                cls:'x-btn-text-icon',
                                                                                height:20,
                                                                                text:'Cancelar operaci&oacute;n',
                                                                                handler:function()
                                                                                        {
                                                                                            if(typeof(objConf.cancelarOperacion)!='undefined')
                                                                                            	objConf.cancelarOperacion();
                                                                                            var observaciones=obtenerObservacionesGrid('gridComentarios');     
                                                                                            
                                                                                            var objNotificacion='{"datosEvento":'+objConf.dEventos+',"tipoNotificacion":"'+objConf.tipoNotificacion+'","respuesta":"0","accionEvento":"2","observaciones":"'+
                                                                                                                bE(observaciones)+'","comentarios":"","verificarAutorizacion":"'+verificarAutorizacion+'","idUsuarioAutorizacion":"0"}';
            																				registrarBitacoraNotificaciones(objNotificacion);    
                                                                                                
                                                                                            gEx('vObservacionesError').close();
                                                                                        }
                                                                            }		
                                                                		],
                                                                view:new Ext.grid.GroupingView({
                                                                                                    forceFit:false,
                                                                                                    showGroupName: false,
                                                                                                    enableGrouping :false,
                                                                                                    enableNoGroups:false,
                                                                                                    enableGroupingMenu:false,
                                                                                                    hideGroupedColumn: true,
                                                                                                    startCollapsed:false
                                                                                                })
                                                            }
                                                        );
	                                                
        return 	tblGrid;
}	

function mostrarVentanaAutorizacionBiometricoMensaje(objConf)
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
                                                            html:'<span style="color:#900"><b>La operaci&oacute;n requiere la comprobaci&oacute;n de su identidad mediante huella dactilar. <br><br>Esperando autenticaci&oacute;n...</b><br></span>'
                                                            
                                                        }

													]
										}
									);
	
	var ventanaAM = new Ext.Window(
									{
										title: 'Verificando permisos',
                                        id:'vAutorizacionDactilar',
										width: 500,
										height:140,
										layout: 'fit',
										plain:true,
										modal:true,
										bodyStyle:'padding:5px;',
										buttonAlign:'center',
										items: form,
										listeners : {
                                        				close:	function()
                                                        		{
                                                                	funcionEjecucion=null;
                                                                    ejecutarAccionBiometrico=null;
                                                                },
                                                        show : {
                                                                    buffer : 10,
                                                                    fn : function() 
                                                                    {
                                                                    }
                                                                }
                                                    },
										buttons:	[
														
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
    ejecutarAccionBiometrico=function(u)
                            {
                                if(parseInt(u)>=1000000)
                                {
                                    
                                    msgBox('El usuario NO cuenta con los permisos para autorizar esta acci&oacute;n');
                                    return;
                                }
                                var iU=u;
                                function funcAjax()
                                {
                                    var resp=peticion_http.responseText;
                                    arrResp=resp.split('|');
                                    if(arrResp[0]=='1')
                                    {
                                        if(arrResp[1]=='0')
                                        {
                                            msgBox('El usuario NO cuenta con los permisos para autorizar esta acci&oacute;n');
                                            return;
                                        }
                                        else
                                        {
                                            gEx('vAutorizacionDactilar').close();
                                            idUsuarioAutorizacion=iU;
                                            verificarSituacionAccion(objConf);
                                        }
                                    }
                                    else
                                    {
                                        msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
                                    }
                                }
                                obtenerDatosWeb('../paginasFunciones/funcionesModulosEspeciales_Transportes.php',funcAjax, 'POST','funcion=30&iU='+iU+'&a='+objConf.tipoNotificacion,true);
                            
                            }
                    
            
	funcionEjecucion=ejecutarAccionBiometrico
            		
}

function obtenerObservacionesGrid(idGrid)
{
	var grid=gEx(idGrid);
    var x;
    var fila;
    var arrObservaciones='';
    
    
	var o;
        
    for(x=0;x<grid.getStore().getCount();x++)
    {
    	fila=grid.getStore().getAt(x);
        o='{"entidadValidacion":"'+fila.data.entidadValidacion+'","idEntidadValidacion":"'+fila.data.idEntidadValidacion+'","accion":"'+fila.data.accion+'","tipoComentario":"'+fila.data.tipoComentario+'","observacion":"'+escaparEnter(fila.data.comentario.replace(/"/gi,'\\"'))+'"}';
        if(arrObservaciones=='')
        	arrObservaciones=o;
        else
        	arrObservaciones+=','+o;
        
        
    }
    return ('['+arrObservaciones+']');
}