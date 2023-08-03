<?php
	session_start();
	include("configurarIdiomaJS.php");
	include("conexionBD.php");
?>


var arrTimbrar=[];
var arrTemporal=[];
var totalProceso=0;
var totalProcesados=0;

function generarTimbradoIndividual(iN,e,c)
{
	arrTimbrar=[];
	gE('lblOperacion').innerHTML='Timbrado';
    var fila;
	
    for(x=0;x<gNomina.getStore().getCount();x++)
    {
    	fila=gNomina.getStore().getAt(x);
        if(((fila.data.situacionComprobante=='1')||(fila.data.situacionComprobante=='3'))&&(!fila.data.ignorarTimbrado))
        {
        	arrTimbrar.push(fila);
        
        }
    }
    totalProceso=arrTimbrar.length;
    totalProcesados=0;
    mostrarBarraAvance();
    gEx('pbar').updateProgress(0,'Procesando 0/'+totalProceso);
    gEx('gNomina').disable();
    detenerOperacion=false;
    timbrarEmpleado(arrTimbrar,iN,e,c);
    
}

function timbrarEmpleado(arrTimbrar,iN,e,c)
{
	
	var gNomina=gEx('gNomina').getStore();
    
	if((arrTimbrar.length==0)&&(esTimbradoTotal()))
    {

    	function funcAjax()
        {
            var resp=peticion_http.responseText;
            arrResp=resp.split('|');
            if(arrResp[0]=='1')
            {
            	ocultarBarraAvance()
                gEx('gNomina').enable();
                gE('frmRecargarPagina').submit();
            }
            else
            {
                msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
            }
        }
        obtenerDatosWeb('../paginasFunciones/funcionesEspecialesNomina.php',funcAjax, 'POST','funcion=21&idNomina='+iN+'&nEtapa='+e+'&comentarios='+cv(c),true);
    }
    else
    {
    	if(arrTimbrar.length==0)
    	{
        	ocultarBarraAvance()
	        gEx('gNomina').enable();
        }
        else
        {
            function funcAjax2()
            {
                var resp=peticion_http.responseText;
                arrResp=resp.split('|');
                if(arrResp[0]=='1')
                {
                    totalProcesados++;
                    var pos=obtenerPosFila(gNomina,'idAsientoNomina',arrTimbrar[0].data.idAsientoNomina);
                    var f=gNomina.getAt(pos);
                    f.set('idComprobante',arrResp[1]);
                    f.set('situacionComprobante',arrResp[2]);
                    f.set('comentarios',arrResp[3]);
                    gEx('gNomina').getView().refresh();
                    arrTimbrar.splice(0,1);
                    gEx('pbar').updateProgress((totalProcesados/totalProceso),'Procesando '+totalProcesados+'/'+totalProceso);
                    if(!detenerOperacion)
                        timbrarEmpleado(arrTimbrar,iN,e,c);
                    else
                    {
                        ocultarBarraAvance()
                        gEx('gNomina').enable();
                    }
                }
                else
                {
                    msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
                }
            }
            obtenerDatosWeb('../paginasFunciones/funcionesEspecialesNomina.php',funcAjax2, 'POST','funcion=34&idAsiento='+arrTimbrar[0].data.idAsientoNomina,true);
    	}
    }
}

function esTimbradoTotal()
{
	var gNomina=gEx('gNomina');
    var x;
    var fila;
	
    for(x=0;x<gNomina.getStore().getCount();x++)
    {
    	fila=gNomina.getStore().getAt(x);
        if((fila.data.situacionComprobante!='2')&&(fila.data.situacionComprobante!='6')&&(!fila.data.ignorarTimbrado))
        	return false;
    }
    return true;
}

function generarXMLIndividual(iN,e,c)
{
	arrTemporal=[];
	gE('lblOperacion').innerHTML='Generaci&oacute;n XML';
	var gNomina=gEx('gNomina');
    var x;
    var fila;
	
    for(x=0;x<gNomina.getStore().getCount();x++)
    {
    	fila=gNomina.getStore().getAt(x);
        if(((fila.data.situacionComprobante=='1')&&(fila.data.idComprobante==''))||(fila.data.situacionComprobante=='3'))
        {
        	arrTemporal.push(fila);
        
        }
    }
    totalProceso=arrTemporal.length;
    totalProcesados=0;
    mostrarBarraAvance();
    gEx('pbar').updateProgress(0,'Procesando 0/'+totalProceso);
    gEx('gNomina').disable();
    detenerOperacion=false;
    generarXMLEmpleado(arrTemporal,iN,e,c);
    
}

function generarXMLEmpleado(arrTemporal,iN,e,c)
{
	
	var gNomina=gEx('gNomina').getStore();
    
	if((arrTemporal.length==0)&&(esGeneracionXMLTotal()))
    {

    	function funcAjax()
        {
            var resp=peticion_http.responseText;
            arrResp=resp.split('|');
            if(arrResp[0]=='1')
            {
            	ocultarBarraAvance()
                gEx('gNomina').enable();
                gE('frmRecargarPagina').submit();
            }
            else
            {
                msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
            }
        }
        obtenerDatosWeb('../paginasFunciones/funcionesEspecialesNomina.php',funcAjax, 'POST','funcion=21&idNomina='+iN+'&nEtapa='+e+'&comentarios='+cv(c),true);
    }
    else
    {
    
    	if(arrTemporal.length==0)
    	{
        	ocultarBarraAvance()
	        gEx('gNomina').enable();
        }
        else
        {
            function funcAjax2()
            {
                var resp=peticion_http.responseText;
                arrResp=resp.split('|');
                if(arrResp[0]=='1')
                {
                    totalProcesados++;
                    var pos=obtenerPosFila(gNomina,'idAsientoNomina',arrTemporal[0].data.idAsientoNomina);
                    var f=gNomina.getAt(pos);
                    f.set('idComprobante',arrResp[1]);
                    f.set('situacionComprobante',arrResp[2]);
                    f.set('comentarios',arrResp[3]);
                    gEx('gNomina').getView().refresh();
                    arrTemporal.splice(0,1);
                    gEx('pbar').updateProgress((totalProcesados/totalProceso),'Procesando '+totalProcesados+'/'+totalProceso);
                    
                    if(!detenerOperacion)
                        generarXMLEmpleado(arrTemporal,iN,e,c);
                    else
                    {
                        ocultarBarraAvance()
                        gEx('gNomina').enable();
                    }
                    
                }
                else
                {
                    msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
                }
            }
            obtenerDatosWeb('../paginasFunciones/funcionesEspecialesNomina.php',funcAjax2, 'POST','funcion=41&idAsiento='+arrTemporal[0].data.idAsientoNomina,true);
    	}
    }
}

function esGeneracionXMLTotal()
{
	var gNomina=gEx('gNomina');
    var x;
    var fila;
	
    for(x=0;x<gNomina.getStore().getCount();x++)
    {
    	fila=gNomina.getStore().getAt(x);
        if((fila.data.situacionComprobante=='1')&&(fila.data.idComprobante==''))
        	return false;
    }
    return true;
}

function mostrarVentanaEspere()
{
	
}

function ocultarMensajeEspere()
{
	
}

function generarTimbradoXMLIndividual(iN,e,c)
{
	var gNomina=gEx('gNomina');
	arrTimbrar=[];
	gE('lblOperacion').innerHTML='Timbrado de XML';
    var fila;
    for(x=0;x<gNomina.getStore().getCount();x++)
    {
    	fila=gNomina.getStore().getAt(x);
        
        if(((fila.data.situacionComprobante=='1')&&(fila.data.idComprobante!=''))&&(!fila.data.ignorarTimbrado))
        {
        
        	arrTimbrar.push(fila);
        }
    }
	
    totalProceso=arrTimbrar.length;
    totalProcesados=0;
    mostrarBarraAvance();
    gEx('pbar').updateProgress(0,'Procesando 0/'+totalProceso);
    gEx('gNomina').disable();
    detenerOperacion=false;
    timbrarXMLEmpleado(arrTimbrar,iN,e,c);
    
}

function timbrarXMLEmpleado(arrTimbrar,iN,e,c)
{
	var gNomina=gEx('gNomina').getStore();
	if((arrTimbrar.length==0)&&(esTimbradoXMLTotal()))
    {

    	function funcAjax()
        {
            var resp=peticion_http.responseText;
            arrResp=resp.split('|');
            if(arrResp[0]=='1')
            {
            	ocultarBarraAvance()
                gEx('gNomina').enable();
                gE('frmRecargarPagina').submit();
            }
            else
            {
                msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
            }
        }
        obtenerDatosWeb('../paginasFunciones/funcionesEspecialesNomina.php',funcAjax, 'POST','funcion=21&idNomina='+iN+'&nEtapa='+e+'&comentarios='+cv(c),true);
    }
    else
    {
    	if(arrTimbrar.length==0)
    	{
        	ocultarBarraAvance()
	        gEx('gNomina').enable();
        }
        else
        {
            function funcAjax2()
            {
                var resp=peticion_http.responseText;
                arrResp=resp.split('|');
                if(arrResp[0]=='1')
                {
                    totalProcesados++;
                    var pos=obtenerPosFila(gNomina,'idAsientoNomina',arrTimbrar[0].data.idAsientoNomina);
                    var f=gNomina.getAt(pos);
                    f.set('idComprobante',arrResp[1]);
                    f.set('situacionComprobante',arrResp[2]);
                    f.set('comentarios',arrResp[3]);
                    gEx('gNomina').getView().refresh();
                    arrTimbrar.splice(0,1);
                    gEx('pbar').updateProgress((totalProcesados/totalProceso),'Procesando '+totalProcesados+'/'+totalProceso);
                    if(!detenerOperacion)
                        timbrarXMLEmpleado(arrTimbrar,iN,e,c);
                    else
                    {
                        ocultarBarraAvance()
                        gEx('gNomina').enable();
                    }
                }
                else
                {
                    msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
                }
            }
            obtenerDatosWeb('../paginasFunciones/funcionesEspecialesNomina.php',funcAjax2, 'POST','funcion=42&idAsiento='+arrTimbrar[0].data.idAsientoNomina,true);
    	}
    }
}

function esTimbradoXMLTotal()
{
	var gNomina=gEx('gNomina');
    var x;
    var fila;
	
    for(x=0;x<gNomina.getStore().getCount();x++)
    {
    	fila=gNomina.getStore().getAt(x);
        if((fila.data.situacionComprobante!='2')&&(fila.data.situacionComprobante!='6')&&(!fila.data.ignorarTimbrado))
        	return false;
    }
    return true;
}


function cancelarTimbradoIndividual(iN,e,c)
{
	arrTemporal=[];
	gE('lblOperacion').innerHTML='Cancelaci&oacute;n de timbrado';
	var gNomina=gEx('gNomina');
    var x;
    var fila;
	
    for(x=0;x<gNomina.getStore().getCount();x++)
    {
    	fila=gNomina.getStore().getAt(x);
        if(fila.data.situacionComprobante=='2')
        {
        	arrTemporal.push(fila);
        
        }
    }
    totalProceso=arrTemporal.length;
    totalProcesados=0;
    mostrarBarraAvance();
    gEx('pbar').updateProgress(0,'Procesando 0/'+totalProceso);
    gEx('gNomina').disable();
    detenerOperacion=false;
    cancelarTimbradoEmpleado(arrTemporal,iN,e,c);
    
}

function cancelarTimbradoEmpleado(arrTemporal,iN,e,c)
{
	
	var gNomina=gEx('gNomina').getStore();
    
	if((arrTemporal.length==0)&&(esCancelacionTotal()))
    {

    	function funcAjax()
        {
            var resp=peticion_http.responseText;
            arrResp=resp.split('|');
            if(arrResp[0]=='1')
            {
            	ocultarBarraAvance()
                gEx('gNomina').enable();
                gE('frmRecargarPagina').submit();
            }
            else
            {
                msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
            }
        }
        obtenerDatosWeb('../paginasFunciones/funcionesEspecialesNomina.php',funcAjax, 'POST','funcion=21&idNomina='+iN+'&nEtapa='+e+'&comentarios='+cv(c),true);
    }
    else
    {
    	if(arrTemporal.length==0)
    	{
        	ocultarBarraAvance()
	        gEx('gNomina').enable();
        }
        else
        {
            function funcAjax2()
            {
                var resp=peticion_http.responseText;
                arrResp=resp.split('|');
                if(arrResp[0]=='1')
                {
                    totalProcesados++;
                    var pos=obtenerPosFila(gNomina,'idAsientoNomina',arrTemporal[0].data.idAsientoNomina);
                    var f=gNomina.getAt(pos);
                    f.set('idComprobante',arrResp[1]);
                    f.set('situacionComprobante',arrResp[2]);
                    f.set('comentarios',arrResp[3]);
                    gEx('gNomina').getView().refresh();
                    arrTemporal.splice(0,1);
                    gEx('pbar').updateProgress((totalProcesados/totalProceso),'Procesando '+totalProcesados+'/'+totalProceso);
                    
                    if(!detenerOperacion)
                        cancelarTimbradoEmpleado(arrTemporal,iN,e,c);
                    else
                    {
                        ocultarBarraAvance()
                        gEx('gNomina').enable();
                    }
                    
                }
                else
                {
                    msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
                }
            }
            obtenerDatosWeb('../paginasFunciones/funcionesEspecialesNomina.php',funcAjax2, 'POST','funcion=44&idAsiento='+arrTemporal[0].data.idAsientoNomina,true);
    	}
    }
}

function esCancelacionTotal()
{
	var gNomina=gEx('gNomina');
    var x;
    var fila;
	
    for(x=0;x<gNomina.getStore().getCount();x++)
    {
    	fila=gNomina.getStore().getAt(x);
        if((fila.data.situacionComprobante!='3')&&(fila.data.situacionComprobante!='6')&&(!fila.data.ignorarTimbrado))
        	return false;
    }
    return true;
}


function verificaNotificacionesNomina(iN,etapa,comentarios)
{
	function funcAjax()
    {
        var resp=peticion_http.responseText;
        arrResp=resp.split('|');
        if(arrResp[0]=='1')
        {
            var arrDatos=eval(arrResp[1]);
            if(arrDatos.length==0)
            {
                cambiarEtapaNomina(iN,etapa,comentarios);
            }
            else
                mostrarVentanaNotificaciones(arrDatos);
        }
        else
        {
            msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
        }
    }
    obtenerDatosWeb('../paginasFunciones/funcionesEspecialesNomina.php',funcAjax, 'POST','funcion=57&idNomina='+iN,true);

}



function cambiarEtapaNomina(iN,etapa,comentarios)
{
	function funcAjax()
    {
        var resp=peticion_http.responseText;
        arrResp=resp.split('|');
        if(arrResp[0]=='1')
        {
            gE('frmRecargarPagina').submit();
        }
        else
        {
            msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
        }
    }
    obtenerDatosWeb('../paginasFunciones/funcionesEspecialesNomina.php',funcAjax, 'POST','funcion=21&idNomina='+iN+'&nEtapa='+etapa+'&comentarios='+cv(comentarios),true);	
}

function mostrarVentanaNotificaciones(arrDatos)
{
	var gridNotificaciones=crearGridNotificaciones();
    gridNotificaciones.getStore().loadData(arrDatos);
	var form = new Ext.form.FormPanel(	
										{
											baseCls: 'x-plain',
											layout:'absolute',
											defaultType: 'label',
											items: 	[
														{
                                                        	x:10,
                                                            y:10,
                                                            html:'La n&oacute;mina debe ser reprocesada debido a que se han encontrado las siguientes notificaciones:'
                                                        },
                                                        gridNotificaciones

													]
										}
									);
	
	var ventanaAM = new Ext.Window(
									{
										title: 'Notificaciones',
										width: 800,
										height:400,
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

function crearGridNotificaciones()
{
	var dsDatos=[];
    var alDatos=	new Ext.data.SimpleStore	(
                                                    {
                                                        fields:	[
                                                                    {name: 'fechaNotificacion',type:'date',dateFormat:'Y-m-d H:i:s'},
                                                                    {name: 'notificacion'}
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
															header:'Fecha notificaci&oacute;n',
															width:120,
															sortable:true,
															dataIndex:'fechaNotificacion',
                                                            renderer:function(val)
                                                            		{
                                                                    	return val.format('d/m/Y H:i');
                                                                    }
														},
														{
															header:'Notificaci&oacute;n',
															width:530,
															sortable:true,
															dataIndex:'notificacion',
                                                            renderer:function(val)
                                                            		{
                                                                    	return '<img src="../images/exclamation.png"> '+mostrarValorDescripcion(val);
                                                                    }
														}
													]
												);
                                                
	var tblGrid=	new Ext.grid.EditorGridPanel	(
                                                        {
                                                            
                                                            store:alDatos,
                                                            frame:true,
                                                            y:40,
                                                            x:10,
                                                            cm: cModelo,
                                                            stripeRows :true,
                                                            loadMask:true,
                                                            stripeRows :true,
                                                            columnLines : true,
                                                            height:260,
                                                            width:750,
                                                            sm:chkRow
                                                        }
                                                    );
	return 	tblGrid;		
}