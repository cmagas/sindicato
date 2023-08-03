function verGantt(idRef)
{
	var idFrm=gE('idFormulario').value;
    var param=bE('idFormulario='+idFrm+'&idReferencia='+bD(idRef));
	window.parent.abrirVentana('../gantt/showGantt.php?param='+param);
}


function enviarProceso(t,iP)
{
	var arrParam=[['tipo',t],['iP',iP]];
	enviarFormularioDatos('../principal/proxyProcesos.php',arrParam);
}

////////////
function buscarCliente()
{
	mostrarVentanaCliente(1);
}

function buscarEmpresa()
{
	mostrarVentanaCliente(2);
}

function mostrarVentanaCliente(tipoC)
{
	var criterioB=tipoC;
	var ventanaAM ;
	var pPagina=new Ext.data.HttpProxy	(
										 	{
												url:'../Usuarios/procesarbUsuario.php',
												method:'POST'
											}
										 );
	var lector=new Ext.data.JsonReader 	(
										 	{
												root:'personas',
												totalProperty:'num',
												id:'idUsuario'
											},
											[
											 	{name:'idUsuario'},
												{name:'Nombre'},
                                                {name: 'complementario'},
												{name: 'respRegistro'},
												{name: 'idResponsable'},
												{name: 'acc'}
											]
										);
	var parametros=	{
						funcion:'3',
						criterio:''
					};
	var cmbNombre= inicializarCmbNombreCliente(pPagina,lector,parametros,tipoC);
	function funcSelectCliente(combo,registro)
	{
		var iC=cmbNombre.getValue();
		var nombre=cmbNombre.getRawValue();
		if(bD(registro.get('acc'))=='1')
		{
			var usrReg=false;
			
			if(registro.get('idResponsable')==bD(usr))
				usrReg=true;
			ventanaAM.close();
			
			mostrarVentanaCreditosEmpresa(iC,tipoC,nombre,usrReg);
		}
		else
		{
			var msg;
			if(criterioB=='1')
				msg='S&oacute;lo el promotor que registr&oacute; a la persona f&iacute;sica puede acceder al historial de la persona seleccionada';
			else
				msg='S&oacute;lo el promotor que registr&oacute; a la persona moral puede acceder al historial de la empresa seleccionada';
			function respMsg()
			{
				cmbNombre.reset();
				cmbNombre.focus();
			}
			msgBox(msg,respMsg);
			return;
		}
	}
	cmbNombre.on('select',funcSelectCliente);
    var lblEtiqueta;
    if(criterioB=='1')
    {
    	lblEtiqueta='Nombre del cliente:';
        lblDebe='Debe seleccionar un cliente';
        lblEtiquetaNoExiste='&iquest;No existe el cliente? agr&eacute;guelo ';
        lblTitulo='Buscar persona f&iacute;sica';
    }
    else
    {
    	lblEtiqueta='Nombre de la empresa:';
        lblDebe='Debe seleccionar una empresa';
        lblEtiquetaNoExiste='&iquest;No existe la empresa? agr&eacute;guela ';
        lblTitulo='Buscar persona moral';
    }
    var form = new Ext.form.FormPanel(	
										{
											baseCls: 'x-plain',
											layout:'absolute',
											defaultType: 'label',
											items: 	[
														{
                                                        	x:10,
                                                            y:20,
                                                            html:lblEtiqueta
                                                        },
                                                        cmbNombre,
                                                        {
                                                        	x:240,
                                                            y:45,
                                                        	html:lblEtiquetaNoExiste+'<a href="javascript:agregarCliente('+criterioB+')"><b><font color="red">AQU&Iacute;</font></a></b>'
                                                        }														
													]
										}
									);
	
	ventanaAM = new Ext.Window(
									{
                                    	id:'vAgregarC',
										title: lblTitulo,
										width: 500,
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
                                                                	cmbNombre.focus(false,500);
																}
															}
												},
										buttons:	[
														/*{
															
															text: 'Aceptar',
															handler: function()
																	{
																		if(cmbNombre.getValue()=='')
                                                                        {
                                                                        	msgBox(lblDebe);
                                                                        	return;
                                                                        }
                                                                        var iC=cmbNombre.getValue();
																		var nombre=cmbNombre.getRawValue();
																		ventanaAM.close();
																		mostrarVentanaCreditosEmpresa(iC,tipoC,nombre);
																	}
														},*/
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

function inicializarCmbNombreCliente(pagina,lector, parametros,tipoC)
{
	var ds=new Ext.data.Store	(
								 	{
										proxy:pagina,
										reader:lector,
										baseParams:parametros
									}
								 );
	
	function cargarDatos(dSet)
	{
    	var criterioB=tipoC;
		var aNombre=Ext.getCmp('cmbNombreEmpresa').getRawValue();
		dSet.baseParams.criterio=aNombre;
		dSet.baseParams.campoBusqueda=criterioB;
	}
	
	ds.on('beforeload',cargarDatos);
	
	var resultTpl=new Ext.XTemplate	(
									 	'<tpl for="."><div class="search-item">',
											'{Nombre}<br />-----<br><b>Promotor:</b>&nbsp;&nbsp;{respRegistro}<br>',
										'</div></tpl>'
									 )
	
	var comboNombre= new Ext.form.ComboBox	(
												 	{
														id:'cmbNombreEmpresa',
														store:ds,
														displayField:'Nombre',
                                                        valueField:'idUsuario',
														typeAhead:false,
														minChars:1,
														loadingText:'Procesando, por favor espere...',
														width:300,
														pageSize:10,
                                                        x:150,
                                                        y:15,
														hideTrigger:true,
														tpl:resultTpl,
														itemSelector:'div.search-item',
														listWidth :300
													}
												 );
    return comboNombre;
}

function agregarCliente(tc)
{
	var vAgregarC=Ext.getCmp('vAgregarC');
    vAgregarC.close();
	if(tc==1)
		TB_show(lblAplicacion,'../clientes/catalogoClientes.php?ejecutarExt='+Base64.encode('window.parent.TB_remove();return')+'&cPagina=sFrm=true&idCliente=-1&TB_iframe=true&height=500&width=900',"","scrolling=yes");
	else
    	TB_show(lblAplicacion,'../clientes/catalogoEmpresas.php?ejecutarExt='+Base64.encode('window.parent.TB_remove();return')+'&cPagina=sFrm=true&idEmpresa=-1&TB_iframe=true&height=500&width=900',"","scrolling=yes");
}

function nuevoCreditoProxy(iC,tC)
{
	nuevoCredito(Base64.encode(''+iC),Base64.encode(''+tC));
}

function nuevoCredito(iC,tC)
{
	
	function funcAjax()
	{
		var resp=peticion_http.responseText;
		arrResp=resp.split('|');
		if(arrResp[0]=='1')
		{
			var vCredito=Ext.getCmp('ventanaCredito');
			if(vCredito!=null)
				vCredito.close();
			verCaratulaCredito(arrResp[1]);
			actualizarPanelCentral();
			
		}
		else
		{
			msgBox('No se ha podido llevar a cabo la operaci&oacute;n debido al siguiente problema:'+' <br />'+arrResp[0]);
		}
	}
	obtenerDatosWeb('../paginasFunciones/funcionesRcCorporativo.php',funcAjax, 'POST','funcion=10&idCliente='+iC+'&tCliente='+tC,true);
}

function mostrarCreditoNuevo(iC)
{
	var vCredito=Ext.getCmp('ventanaCredito');
	if(vCredito!=null)
		vCredito.close();
	verCaratulaCredito(iC);
	actualizarPanelCentral();
}

function mostrarVentanaCreditosEmpresa(iC,tC,p,usrReg)
{
	var ocultarLbl=true;
	if(sr)
		ocultarLbl=false;
	else
		if(usrReg)
			ocultarLbl=false;
	if(tC=='1')
	{
		lblCreditos='La persona f&iacute;sica seleccionada cuenta con los siguientes cr&eacute;ditos:';
		tipoPersona='f&iacute;sica';
	}
	else
	{
		lblCreditos='La persona moral seleccionada cuenta con los siguientes cr&eacute;ditos:';
		tipoPersona='moral';
	}
	var gridCreditos=crearGridCreditos();
	var form = new Ext.form.FormPanel(	
										{
											baseCls: 'x-plain',
											layout:'absolute',
											defaultType: 'label',
											items: 	[
														{
															html:lblCreditos,
															x:10,
															y:10
														},
														gridCreditos,
														{
															x:260,
															y:375,
															hidden:ocultarLbl,
															html:'&iquest; Desea registrar un nuevo cr&eacute;dito a esta persona '+tipoPersona+'?, de click <a href="javascript:nuevoCredito(\''+Base64.encode(''+iC)+'\',\''+Base64.encode(''+tC)+'\')"><font color="red"><b>AQU&Iacute;</b></font></a>'
														}

													]
										}
									);
	
	var ventanaAM = new Ext.Window(
									{
										id:'ventanaCredito',
										title: 'Cr&eacute;ditos disponibles de la persona '+tipoPersona+': <b>'+p+'</b>',
										width: 730,
										height:480,
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
																		
																		mostrarCredito();
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
	obtenerCreditosCliente(iC,tC,ventanaAM);		
}

function obtenerCreditosCliente(iC,tC,ventana)
{
	function funcAjax()
	{
		var resp=peticion_http.responseText;
		arrResp=resp.split('|');
		if(arrResp[0]=='1')
		{
			var arrDatos=eval(arrResp[1]);
			var tblGridCreditos=Ext.getCmp('tblGridCreditos');
			tblGridCreditos.getStore().loadData(arrDatos);
			ventana.show();
		}
		else
		{
			msgBox('No se ha podido llevar a cabo la operaci&oacute;n debido al siguiente problema:'+' <br />'+arrResp[0]);
		}
	}
	obtenerDatosWeb('../paginasFunciones/funcionesRcCorporativo.php',funcAjax, 'POST','funcion=11&iC='+iC+'&tC='+tC,true);
	
}

function crearGridCreditos()
{
	var dsDatos=[];
    var alDatos=	new Ext.data.SimpleStore	(
                                                {
                                                    fields:	[
                                                                {name: 'idCredito'},
                                                                {name: 'folio'},
																{name: 'fechaReg'},
																{name: 'registrado'},
																{name: 'situacion'}
																
                                                            ]
                                                }
                                            );

    alDatos.loadData(dsDatos);
	var chkRow=new Ext.grid.CheckboxSelectionModel();
	
	var cModelo= new Ext.grid.ColumnModel   	(
												 	[
													 	new  Ext.grid.RowNumberer(),
														{
															header:'Folio',
															width:100,
															sortable:true,
															dataIndex:'folio'
														},
														{
															header:'Fecha registro',
															width:100,
															sortable:true,
															dataIndex:'fechaReg'
														},
														{
															header:'Registrado por:',
															width:200,
															sortable:true,
															dataIndex:'registrado'
														},
														{
															header:'Situaci&oacute;n',
															width:200,
															sortable:true,
															dataIndex:'situacion'
														}
													]
												);
                                                
	var tblGrid=	new Ext.grid.GridPanel	(
                                                        {
                                                            id:'tblGridCreditos',
                                                            store:alDatos,
                                                            frame:true,
                                                            y:40,
                                                            cm: cModelo,
                                                            height:320,
                                                            width:700,
															listeners:	{
																			dblclick:mostrarCredito
																		}	
                                                        }
                                                    );
	return 	tblGrid;	
}

function mostrarCredito()
{
	
	var tblGridCreditos=Ext.getCmp('tblGridCreditos');
	var fila=tblGridCreditos.getSelectionModel().getSelected();
	if(fila==null)
	{
		msgBox('Debe seleccionar el cr&eacute;dito al cual desea entrar');
		return;
	}
	
	var idCredito=fila.get('idCredito');
	verCaratulaCredito(idCredito);
}

function actualizarPanelCentral()
{
	
	gEx('frameContenido').getFrameWindow().recargarPagina();	
}

function regresarPaginaPanelCentral()
{
	gEx('frameContenido').getFrameWindow().regresarPagina();	
}

function irTableroCreditos()
{
		gEx('frameContenido').load(
									{
										url:'../clientes/tableroCredito.php',
										scripts:true,
										params:	{
													cPagina:'sFrm=true'
												}
									}
								)

}

function verCaratulaCredito(idCredito)
{
	var arrDatos=[['idCredito',idCredito]];
	var nVentana='ventana_'+(new Date().format('h_i'))+'_'+generarNumeroAleatorio(1,10000);
	window.open('',nVentana, "toolbar=no,directories=no,menubar=no,status=no,scrollbars=yes,fullscreen=yes");
	enviarFormularioDatos('../clientes/clientes.php',arrDatos,'POST',nVentana);	
}