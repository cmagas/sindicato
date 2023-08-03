<?php
	session_start();
	include("configurarIdiomaJS.php");
	include("conexionBD.php");
	$consulta="SELECT idTiposGraficos,nombreGraficoUsr,idCategoria FROM 9026_tiposGraficos ORDER BY nombreGraficoUsr";
	$arrGraficos=$con->obtenerFilasArreglo($consulta);
	
?>



var arrGraficos=<?php echo $arrGraficos?>;
var arrOrigenCategorias=[['0','Definici\xF3n manual'],['1','Campo de tabla de BD'],['2','Funci\xF3n del sistema']];
var arrOrigenSeries=[['0','Definici\xF3n manual'],['2','Funci\xF3n del sistema']];

var registroConcepto=crearRegistro	(
                                        [
                                            {name: 'idConsulta'},
                                            {name: 'nombreConsulta'},
                                            {name: 'nombreCategoria'},
                                            {name: 'descripcion'},
                                            {name: 'valorRetorno'},
                                            {name: 'parametros'}
                                        ]
                                    )

function mostrarVentanaDatos(idGrafico)
{
	var gridDatos;
    gridDatos=crearGridDatosMulti(idGrafico);
	var form = new Ext.form.FormPanel(	
										{
											baseCls: 'x-plain',
											layout:'absolute',
											defaultType: 'label',
											items: 	[
														gridDatos

													]
										}
									);
	
	var ventanaAM = new Ext.Window(
									{
										title: 'Configuraci&oacute;n de origen de datos',
										width: 700,
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
															text: 'Cerrar',
															handler:function()
																	{
																		ventanaAM.close();
																	}
														}
													]
									}
								);
	llenarConfiguracionDatosAlmacen(idGrafico,ventanaAM);                                	
}

function llenarConfiguracionDatosAlmacen(idGrafico,ventana)
{

	function funcAjax()
    {
        var resp=peticion_http.responseText;
        arrResp=resp.split('|');
        if(arrResp[0]=='1')
        {
        	var arrSeries=eval(arrResp[1]);
            var arrDatos=eval(arrResp[2]);
        	var gridOrigenesDatosGrafico=gEx('gridOrigenesDatosGrafico');
            var arrCampos=[{name: 'idCategoria'},{name: 'categoria'}];
            var x;
            for(x=0;x<arrSeries.length;x++)
	            arrCampos.push({name:'serie_'+arrSeries[x][1]});
            
            var almacen=	new Ext.data.SimpleStore	(
                                                            {
                                                                fields:	arrCampos
                                                            }
                                                        );
            var arrColModel=[
                                new  Ext.grid.RowNumberer(),
                                {
                                    header:'Categor&iacute;a',
                                    width:200,
                                    sortable:true,
                                    dataIndex:'categoria'
                                }
                            ]
                            
            for(x=0;x<arrSeries.length;x++)
            {
	            arrColModel.push( {
                					id:arrSeries[x][1],
                                    header:arrSeries[x][0],
                                    width:180,
                                    sortable:true,
                                    dataIndex:'serie_'+arrSeries[x][1],
                                    renderer:function(val,meta,registro,fila,columna,almacen)
                                    		{
                                            	var idSerie;
                                                idSerie=gEx('gridOrigenesDatosGrafico').getColumnModel().getColumnId(columna);
                                            	return '<a href="javascript:modificarOrigenDatos(\''+bE(registro.get('idCategoria'))+'\',\''+bE(idSerie)+'\',\''+bE(idGrafico)+'\')">'+val+' <img height="13" width="13" src="../images/pencil.png" title="Modificar origen de datos"></a>';
                                            }
                                });
            }                    
            var colModel= new Ext.grid.ColumnModel   	(
                                                            arrColModel
                                                        );
            gridOrigenesDatosGrafico.reconfigure(almacen,colModel);
            almacen.loadData(arrDatos);
            if(ventana!=undefined)
	            ventana.show();
        }
        else
        {
            msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
        }
    }
    obtenerDatosWeb('../paginasFunciones/funcionesThot.php',funcAjax, 'POST','funcion=35&idAlmacen='+idGrafico,true);
}

function crearGridDatosMulti(idGrafico,arrSeries)
{
	var dsDatos=[];
    
    var alDatos=	new Ext.data.SimpleStore	(
                                                    {
                                                        fields:	[
                                                                    {name: 'idCategoria'},
                                                                    {name: 'categoria'}
                                                                ]
                                                    }
                                                );

    alDatos.loadData(dsDatos);
   
    
    var cModelo= new Ext.grid.ColumnModel   	(
                                                    [
                                                        new  Ext.grid.RowNumberer(),
                                                        
                                                        {
                                                            header:'Categor&iacute;a',
                                                            width:120,
                                                            sortable:true,
                                                            dataIndex:'categoria'
                                                        }
                                                    ]
                                                );
                                                
    var tblGrid=	new Ext.grid.EditorGridPanel	(
                                                        {
                                                            id:'gridOrigenesDatosGrafico',
                                                            store:alDatos,
                                                            frame:true,
                                                            y:5,
                                                            cm: cModelo,
                                                            height:360,
                                                            width:670,
                                                            tbar:	[
                                                                        {
                                                                            icon:'../images/add.png',
                                                                            cls:'x-btn-text-icon',
                                                                            text:'Categor&iacute;as...',
                                                                            handler:function()
                                                                                    {
                                                                                        mostrarVentanaAdmonCat(idGrafico);
                                                                                    }
                                                                            
                                                                        },'-',
                                                                        {
                                                                            icon:'../images/add.png',
                                                                            cls:'x-btn-text-icon',
                                                                            text:'Series...',
                                                                            handler:function()
                                                                                    {
                                                                                        mostrarVentanaAdmonSeries(idGrafico);
                                                                                    }
                                                                            
                                                                        }
                                                                        
                                                                    ]
                                                        }
                                                    );
                                                        
	                                                    
	return 	tblGrid;		
}

function mostrarVentanaAdmonCat(idGrafico)
{
	var cmbOrigenCategoria=crearComboExt('cmbOrigenCategoria',arrOrigenCategorias,205,5);
    cmbOrigenCategoria.setValue('0');
    cmbOrigenCategoria.on('select',function(cmb,registro)
    								{
                                    	
                                   	 	gEx('gridCategoriasGrafico').getStore().removeAll();
                                        gEx('cmbCampoTabla').reset();
                                        gEx('txtTabla').setValue('');
                                    	switch(registro.get("id"))
                                        {
                                        	case '0':
                                            	gEx('gridCategoriasGrafico').enable();
                                                gEx('gridCategoriasGrafico').getColumnModel().setHidden(4,false);
                                                gEx('lblCampoTabla').hide();
                                                gEx('cmbCampoTabla').hide();
                                                gEx('lblEtParam1').hide();
                                                gEx('txtTabla').hide();
                                                gEx('lblMostrarReferencia').hide();
                                                gEx('lblFiltro').hide();
                                                gEx('lblTtxFiltro').hide();
                                                gEx('hCondiciones').hide();
                                            break;
                                            case '1':
                                            	gEx('gridCategoriasGrafico').enable();
                                                gEx('gridCategoriasGrafico').getColumnModel().setHidden(4,false);
                                                gEx('lblCampoTabla').show();
                                                gEx('cmbCampoTabla').show();
                                                gEx('lblEtParam1').show();
                                                gEx('lblEtParam1').setText('Tabla de sistema',false);
                                                gEx('txtTabla').show();
                                                gEx('lblMostrarReferencia').show();
                                                gEx('lblFiltro').show();
                                                gEx('lblTtxFiltro').show();
                                                gEx('hCondiciones').show();
                                                
                                            break;
                                            case '2':
                                            	gEx('gridCategoriasGrafico').disable();
                                                gEx('gridCategoriasGrafico').getColumnModel().setHidden(4,true);
                                                gEx('lblCampoTabla').hide();
                                                gEx('cmbCampoTabla').reset();
                                                gEx('cmbCampoTabla').hide();
                                                gEx('lblEtParam1').show();
                                                gEx('lblEtParam1').setText('Funci&oacute;n de sistema',false);
                                                gEx('txtTabla').show();
                                                gEx('lblMostrarReferencia').show();
                                                gEx('lblFiltro').hide();
                                                gEx('lblTtxFiltro').hide();
                                                gEx('hCondiciones').hide();
                                            break;
                                        }
                                    }
    					)
	var cmbCampoTabla=crearComboExt('cmbCampoTabla',[],150,65,250); 
    cmbCampoTabla.hide();    
    
    cmbCampoTabla.on('select',function(cmb,registro)
    							{
                                	function funcAjax()
                                    {
                                        var resp=peticion_http.responseText;
                                        arrResp=resp.split('|');
                                        if(arrResp[0]=='1')
                                        {
                                        	var gridCategoriasGrafico=gEx('gridCategoriasGrafico');
                                             gridCategoriasGrafico.getStore().removeAll();
                                            if(arrResp[1]!='-1')
                                            {
                                            	var arrDatos=eval(arrResp[1]);
                                                var x;
                                                
                                                var registro=crearRegistro	(
                                                								[
                                                                                	{name: 'idCategoria'},
                                                                                    {name: 'categoria'},
                                                                                    {name:'color'},
                                                                                    {name:'tipo'},
                                                                                    {name:'comp1'},
                                                                                    {name: 'valor'}
                                                                                ]
                                                							)
                                                var r;
                                                for(x=0;x<arrDatos.length;x++)
                                                {
                                                	r=new registro	(
                                                    					{
                                                                        	idCategoria:-1,
                                                                            categoria:arrDatos[x][1],
                                                                            color:'',
                                                                            tipo:cmbOrigenCategoria.getValue(),
                                                                            comp1:'',
                                                                            valor:"'"+arrDatos[x][0]+"'"
                                                                        }
                                                    				)
                                                	gridCategoriasGrafico.getStore().add(r);
                                                }
                                            }
                                            else
                                            {
                                            	msgBox('No se ha podido determinar la diferentes categor&iacute;as asociadas con el campo seleccionado, deber&aacute; ser definido de manera manual');
                                            }
                                        }
                                        else
                                        {
                                            msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
                                        }
                                    }
                                    obtenerDatosWeb('../paginasFunciones/funcionesThot.php',funcAjax, 'POST','funcion=51&idConexionAlmacen='+gEx('txtTabla').idConexionAlmacen+'&c='+bE(registro.get('id'))+'&t='+bE(gEx('txtTabla').tablaOriginal),true);
                                }
    				)
                       
	var gridCategorias=crearGridCategorias(idGrafico);
	var form = new Ext.form.FormPanel(	
										{
											baseCls: 'x-plain',
											layout:'absolute',
											defaultType: 'label',
											items: 	[
                                            			{
                                                        	x:10,
                                                            y:10,
                                                            html:'Determinar categor&iacute;as mediante:'
                                                        },
                                                        cmbOrigenCategoria,
                                                        {
                                                        	x:10,
                                                            y:40,
                                                            id:'lblEtParam1',
                                                            hidden:true,
                                                            html:'Tabla de sistema:'
                                                        },
                                                        {
                                                        	xtype:'textfield',
                                                            x:150,
                                                            y:35,
                                                            id:'txtTabla',
                                                             hidden:true,
                                                            width:250,
                                                            readOnly:true

                                                        },
                                                        {
                                                        	xtype:'label',
                                                            y:35,
                                                            x:405,
                                                            hidden:true,
                                                            id:'lblMostrarReferencia',
                                                            html:'<a href="javascript:mostrarVentanaReferencia()"><img src="../images/pencil.png"></a>'
                                                        },
                                                        {
                                                        	x:10,
                                                            y:70,
                                                            hidden:true,
                                                            id:'lblCampoTabla',
                                                            html:'Campo de tabla:'
                                                        },
                                                        cmbCampoTabla,
                                                        {
                                                        	x:430,
                                                            y:70,
                                                            hidden:true,
                                                            id:'lblFiltro',
                                                            html:'Considerar registros de:'
                                                        },
                                                        {
                                                        	x:560,
                                                            y:63,
                                                            hidden:true,
                                                            id:'lblTtxFiltro',
                                                            html:'<span id="txtRegistrosConsidera" style="color: #000"><b>Todos los registros de la tabla seleccionada</b></span>&nbsp;<a href="javascript:modificarFiltroRegistros()"><img src="../images/pencil.png" title="Modificar filtro" alt="Modificar filtro"></a>'
                                                        },
                                                        {
                                                        	x:10,
                                                            y:10,
                                                            xtype:'hidden',
                                                            id:'hCondiciones'
                                                        },
                                                        gridCategorias

													]
										}
									);
	
	var ventanaAM = new Ext.Window(
									{
										title: 'Administraci&oacute;n de categor&iacute;as',
										width: 900,
										height:430,
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
																		
                                                                        var x;
                                                                        var fila;
                                                                        var cadObj='';
                                                                        var obj='';
                                                                        for(x=0;x<gridCategorias.getStore().getCount();x++)
                                                                        {
                                                                        	fila=gridCategorias.getStore().getAt(x);
                                                                            obj='{"idCategoria":"'+fila.get('idCategoria')+'","categoria":"'+cv(fila.get('categoria'))+'","color":"'+fila.get('color')+
                                                                            	'","tipo":"'+fila.get('tipo')+'","comp":"'+fila.get('comp1')+'","valor":"'+fila.get('valor')+'"}';
                                                                            if(cadObj=='')
                                                                            	cadObj=obj;
                                                                            else
                                                                            	cadObj+=","+obj;
                                                                        }
                                                                        
                                                                        var val1='';
                                                                        var val2='';
                                                                        var val3='';
                                                                        var txtTabla=gEx('txtTabla');
                                                                        var cadComplementario='';
                                                                        if(cmbOrigenCategoria.getValue()=='1')
                                                                        {
                                                                        	if(txtTabla.getValue()=='')
                                                                            {
                                                                            	function resp()
                                                                                {
                                                                                	txtTabla.focus();
                                                                                }
                                                                                msgBox('Debe indicar la tabla de la cual se tomar&aacute; el campo que ser&aacute; utilizado como categor&iacute;a del gr&aacute;fico',resp);
                                                                                return;
                                                                            }
                                                                            if(cmbCampoTabla.getValue()=='')
                                                                            {
                                                                            	function resp3()
                                                                                {
                                                                                	cmbCampoTabla.focus();
                                                                                }
                                                                                msgBox('Debe indicar el campo que ser&aacute; utilizado como categor&iacute;a del gr&aacute;fico',resp3);
                                                                                return;
                                                                            }
                                                                            val1=txtTabla.tablaOriginal;
                                                                            val2=txtTabla.getValue();
                                                                            val3=txtTabla.tipoTabla;
                                                                            val4=cmbCampoTabla.getValue();
                                                                            cadComplementario='{"idConexion":"'+txtTabla.idConexionAlmacen+'","tipo":"'+cmbOrigenCategoria.getValue()+'","tablaOrigen":"'+val1+
                                                                            				'","tablaUsr":"'+val2+'","tipoTabla":"'+val3+'","campo":"'+val4+'"}';
                                                                            
                                                                        }
                                                                        
                                                                        if(cmbOrigenCategoria.getValue()=='2')
                                                                        {
                                                                        	if(txtTabla.getValue()=='')
                                                                            {
                                                                            	function resp2()
                                                                                {
                                                                                	txtTabla.focus();
                                                                                }
                                                                                msgBox('Debe indicar la funci&oacute;n de sistema que ser&aacute; utilizado como categor&iacute;a del gr&aacute;fico',resp2);
                                                                                return;
                                                                            }
                                                                            cadComplementario='{"tipo":"'+cmbOrigenCategoria.getValue()+'","idFuncion":"'+txtTabla.idFuncion+'","funcionUsr":"'+txtTabla.getValue()+'"}';
                                                                        }
                                                                        
                                                                        cadObj='{"condicionesFiltro":"'+bE(gEx('hCondiciones').getValue())+'","complementario":"'+bE(cadComplementario)+'","idAlmacen":"'+idGrafico+'","categorias":['+cadObj+']}';
                                                                        function funcAjax()
                                                                        {
                                                                            var resp=peticion_http.responseText;
                                                                            arrResp=resp.split('|');
                                                                            if(arrResp[0]=='1')
                                                                            {
                                                                            	llenarConfiguracionDatosAlmacen(idGrafico);
                                                                            	ventanaAM.close();
                                                                            }
                                                                            else
                                                                            {
                                                                                msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
                                                                            }
                                                                        }
                                                                        obtenerDatosWeb('../paginasFunciones/funcionesThot.php',funcAjax, 'POST','funcion=36&cadObj='+cadObj,true);
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
	obtenerCategoriasAlmacenGrafico(idGrafico,ventanaAM);                                
}

function crearGridCategorias(idGrafico)
{
	var dsDatos=[];
    var alDatos=	new Ext.data.SimpleStore	(
                                                    {
                                                        fields:	[
                                                                    {name: 'idCategoria'},
                                                                    {name: 'categoria'},
                                                                    {name:'color'},
                                                                    {name:'tipo'},
                                                                    {name:'comp1'},
                                                                    {name: 'valor'}
                                                                ]
                                                    }
                                                );

    alDatos.loadData(dsDatos);
	var chkRow=new Ext.grid.CheckboxSelectionModel({singleSelect:true});
	
	var cModelo= new Ext.grid.ColumnModel   	(
												 	[
													 	new  Ext.grid.RowNumberer(),
														chkRow,
														{
															header:'Categor&iacute;a',
															width:300,
															sortable:true,
															dataIndex:'categoria'
														},
                                                        {
															header:'Color',
															width:150,
															sortable:true,
															dataIndex:'color',
                                                            renderer:function(val)
                                                            		{
                                                                    	return '<span style="border-style:solid; border-width:1px; border-color:#000;height:10px;width:10px;background-color:#'+val+'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;&nbsp;'+val;
                                                                    }
														},
                                                        {
                                                        	header:'Valores de la categor&iacute;a',
                                                            width:300,
															sortable:true,
															dataIndex:'valor'
                                                        }
													]
												);
                                                
	var tblGrid=	new Ext.grid.EditorGridPanel	(
                                                        {
                                                            id:'gridCategoriasGrafico',
                                                            store:alDatos,
                                                            frame:true,
                                                            x:10,
                                                            y:110,
                                                            cm: cModelo,
                                                            height:230,
                                                            width:850,
                                                            sm:chkRow,
                                                            tbar:	[
                                                            			{
                                                                        	icon:'../images/add.png',
                                                                            cls:'x-btn-text-icon',
                                                                            text:'Agregar categor&iacute;a',
                                                                            handler:function()
                                                                            		{
                                                                                    	mostrarVentanaAgregarCategoria(idGrafico);
                                                                                    }
                                                                            
                                                                        },'-',
                                                                        {
                                                                        	icon:'../images/pencil.png',
                                                                            cls:'x-btn-text-icon',
                                                                            text:'Modificar categor&iacute;a',
                                                                            handler:function()
                                                                            		{
                                                                                    	var fila=tblGrid.getSelectionModel().getSelected();
                                                                                        if(fila==null)
                                                                                        {
                                                                                        	msgBox('Debe seleccionar la categor&iacute;a que desea modificar');
                                                                                            return;
                                                                                        }
                                                                                    	mostrarVentanaAgregarCategoria(idGrafico,fila);
                                                                                    }
                                                                            
                                                                        },'-',
                                                                        {
                                                                        	icon:'../images/delete.png',
                                                                            cls:'x-btn-text-icon',
                                                                            text:'Remover categor&iacute;a',
                                                                            handler:function()
                                                                            		{
                                                                                    	var fila=tblGrid.getSelectionModel().getSelected();
                                                                                        if(fila==null)
                                                                                        {
                                                                                        	msgBox('Debe seleccionar la categor&iacute;a a remover');
                                                                                            return;
                                                                                        }
                                                                                        function resp(btn)
                                                                                        {
                                                                                        	if(btn=='yes')
                                                                                            {
                                                                                            	tblGrid.getStore().remove(fila);

                                                                                            }
                                                                                        }
                                                                                        msgConfirm('Est&aacute; seguro de querer remover la categor&iacute;a seleccionada',resp);
                                                                                    }
                                                                            
                                                                        },
                                                                        '-',
                                                                        {
                                                                        	icon:'../images/database_table.png',
                                                                            cls:'x-btn-text-icon',
                                                                            text:'Importar categor&iacute;as de tabla de sistema',
                                                                            handler:function()
                                                                            		{
                                                                                    	var objConf={};
                                                                                        objConf.titulo='Seleccione la tabla cuyo campo desea utilizar como origen de categor&iacute;a',
											    										objConf.funcionSeleccion=function(fila,ventana,idConexion)
                                                                                        						{
                                                                                                                	
                                                                                                                	ventana.close();
                                                                                                                	mostrarVentanaSeleccionCampos(fila.get('nomTablaOriginal'),fila.get('tabla'),0,idConexion);
                                                                                                                }
                                                                                    	mostrarInstanciaVentanaSeleccionTabla(objConf)
                                                                                    }
                                                                        }
                                                                        
                                                            		]
                                                        }
                                                    );
	
	return 	tblGrid;	
}

function modificarFiltroRegistros()
{
	var arrAlmacenes=[];
	var arrTipoValor=[['1','Valor constante'],['7','Consulta auxiliar'],['11','Almac\xE9n de datos'],['22','Invocaci\xF3n de funci\xF3n']];
	var cmbTipoValor=crearComboExt('cmbTipoValor',arrTipoValor,125,5);
    cmbTipoValor.setValue('1');
    cmbTipoValor.on('select',function(combo,registro)
    						{
                            	gEx('vSistema').hide();
                                gEx('btnFuncionSistema').hide();
                                gEx('txtTablaFiltro').hide();
                                gEx('txtTablaFiltro').setValue('');
                            	var vConstante=gEx('vConstante');
                                vConstante.hide();
                                var txtConstante=gEx('txtConstante');
                                txtConstante.hide();
                                txtConstante.setValue('');
                                var vAlmacen=gEx('vAlmacen');
                                vAlmacen.hide();
                                var cmbAlmacenesDatos=gEx('cmbAlmacenesDatos');
                                cmbAlmacenesDatos.hide();
                                cmbAlmacenesDatos.reset();
                                cmbAlmacenesDatos.getStore().removeAll();
                                var vCampo=gEx('vCampo');
                                vCampo.hide();
                                var cmbCampo=gEx('cmbCampo');
                                cmbCampo.hide();
                                cmbCampo.reset();
                                cmbCampo.getStore().removeAll();
                            	switch(registro.get('id'))
                                {
                                	case '1':
                                    	vConstante.show();
                                        txtConstante.show();
                                    break;
                                    case '7':
                                    case '11':
                                    	var arrAlmacenes;
                                    	if(registro.get('id')=='7')
                                        	arrAlmacenes=obtenerAlmacenesDatosDisponibles(2);
                                        else
                                        	arrAlmacenes=obtenerAlmacenesDatosDisponibles(1);
                                    	cmbAlmacenesDatos.show();
                                        cmbAlmacenesDatos.getStore().loadData(arrAlmacenes);
                                        vAlmacen.show();
                                        vCampo.show();
                                    	cmbCampo.show();
                                    break;
                                    case '22':
                                    	gEx('vSistema').show();
                                        gEx('txtTablaFiltro').show();
                                        gEx('btnFuncionSistema').show();
                                    break;
                                }
                            }
    				)
    var cmbAlmacenesDatos=crearComboExt('cmbAlmacenesDatos',arrAlmacenes,125,35,250);
    cmbAlmacenesDatos.hide();
    cmbAlmacenesDatos.on('select',function (combo,registro)
    								{
                                    	var arrCampos=obtenerCamposDisponibles(registro.get('id'));
                                        gEx('cmbCampo').reset();
                                        gEx('cmbCampo').getStore().loadData(arrCampos);
                                    }
    					)
    
    var cmbCampo=crearComboExt('cmbCampo',[],125,65,250);
    cmbCampo.hide();
	var form = new Ext.form.FormPanel(	
										{
											baseCls: 'x-plain',
											layout:'absolute',
											defaultType: 'label',
											items: 	[
														{
                                                        	x:10,
                                                            y:10,
                                                            html:'Registros a considerar:'
                                                        },
                                                        cmbTipoValor,
                                                        {
                                                        	id:'vConstante',
                                                        	x:10,
                                                            y:40,
                                                            html:'Valor constante:'
                                                        },
                                                        {
                                                        	id:'vSistema',
                                                        	x:10,
                                                            y:40,
                                                            hidden:true,
                                                            html:'Funci&oacute;n de sistema:'
                                                        },
                                                        {
                                                        	x:125,
                                                            y:35,
                                                            width:300,
                                                            maskRe :/[0-9]/,
                                                            xtype:'textfield',
                                                            id:'txtConstante',
                                                            allowDecimals:true,
                                                            allowNegative:true
                                                        },
                                                        {
                                                        	x:125,
                                                            y:35,
                                                            xtype:'textfield',
                                                            readOnly:true,
                                                            hidden:true,
                                                            id:'txtTablaFiltro',
                                                            width:250
                                                        },
                                                         {
                                                        	x:390,
                                                            y:35,
                                                            xtype:'label',
                                                            hidden:true,
                                                            id:'btnFuncionSistema',
                                                            html:'<a href="javascript:asignarValorFuncionSistema(0)"><img src="../images/pencil.png"></a>'
                                                        },
                                                        {
                                                        	id:'vAlmacen',
                                                        	x:10,
                                                            y:40,
                                                            hidden:true,
                                                            html:'Seleccione almac\xE9n:'
                                                        },
                                                        cmbAlmacenesDatos,
                                                        {
                                                        	id:'vCampo',
                                                        	x:10,
                                                            y:70,
                                                            hidden:true,
                                                            html:'Seleccione campo:'
                                                        },
                                                        cmbCampo
                                                      
                                                        
	
													]
										}
									);
	
	var ventanaAM = new Ext.Window(
									{
										title: 'Definici&oacute;n de origen de registros a considerar',
										width: 500,
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
                                                                    }
                                                                }
                                                    },
										buttons:	[
														{
															text: '<?php echo $etj["lblBtnAceptar"]?>',
															handler: function()
																	{
                                                                    	
                                                                    	var tValor;
                                                                        var valor;
                                                                        tValor=cmbTipoValor.getValue();
                                                                        var cadObjConf='{"tipoValor":"'+tValor+'"';
																		switch(cmbTipoValor.getValue())
                                                                        {
                                                                        	case '1':
                                                                            	var txtConstante=gEx('txtConstante');
                                                                            	if(txtConstante.getValue()=='')
                                                                                {
                                                                                	function resp()
                                                                                    {
                                                                                    	txtConstante.focus();
                                                                                    }
                                                                                    msgBox('Debe ingresar el listado de registros que desea considerar',resp);
                                                                                    return;
                                                                                }
                                                                                valor=txtConstante.getValue();
                                                                                var arrValores=valor.split(",");
                                                                                var x;
                                                                                var valAux='';
                                                                                var cadAux='';
                                                                                for(x=0;x<arrValores.length;x++)
                                                                                {
                                                                                	valAux=normalizarValor(arrValores[x].trim());
                                                                                    if(valAux!='')
                                                                                    {
                                                                                    	if(cadAux=='')
                                                                                        	cadAux=valAux;
                                                                                        else
                                                                                        	cadAux+=','+valAux;
                                                                                    }
                                                                                }
                                                                                cadObjConf+=',"valor":"'+cadAux+'"}';
                                                                            break;
                                                                            case '7':
                                                                            case '11':
                                                                            	var cmbCampo=gEx('cmbCampo');
                                                                                if(cmbCampo.getValue()=='')
                                                                                {
                                                                                	function resp2()
                                                                                    {
                                                                                    	cmbCampo.focus();
                                                                                    }
                                                                                    msgBox('Debe seleccionar el campo a utilizar como valor de origen de los registros a considerar',resp2);
                                                                                    return;
                                                                                }
                                                                                var nodoMysql=buscarNodoID(gEx(idArbolDataSet).getRootNode(),cmbCampo.getValue());
                                                                        		var campo;
                                                                                if(nodoMysql.nCampo!=undefined)
                                                                                    campo=nodoMysql.nCampo;
                                                                                else
                                                                                    campo=nodoMysql.attributes.nCampo;
                                                                                valor=cmbAlmacenesDatos.getValue()+'|'+campo;
                                                                                
                                                                                
                                                                                cadObjConf+=',"idAlmacen":"'+cmbAlmacenesDatos.getValue()+'","campoMysql":"'+campo+'","campoUsr":"'+cmbCampo.getRawValue()+'","almacenUsr":"'+cmbAlmacenesDatos.getRawValue()+'"}';
                                                                                
                                                                            break;
                                                                            case '22':
                                                                            	var txtTabla=gEx('txtTablaFiltro');
                                                                                if(txtTabla.getValue()=='')
                                                                                {
                                                                                	function resp20()
                                                                                    {
                                                                                    	txtTabla.focus();
                                                                                    }
                                                                                    msgBox('Debe indicar la funci&oacute;n de sistema que ser&aacute; utilizado como origen de los registros a considerar',resp20);
                                                                                    return;
                                                                                }
                                                                                valor=txtTabla.idFuncion;
                                                                                cadObjConf+=',"funcion":"'+valor+'","funcionUsr":"'+txtTabla.getValue()+'"}';
                                                                            break;
                                                                        }
                                                                     	
                                                                        gEx('hCondiciones').setValue(cadObjConf);
                                                                        ventanaAM.close();
                                                                        formatearValorFiltro();
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

function formatearValorFiltro()
{
	var hCondiciones=gEx('hCondiciones');
    var etiqueta='<b>Todos los registros de la tabla seleccionada</b>';
    if(hCondiciones.getValue()!='')
    {
    	var obj=eval('['+hCondiciones.getValue()+']')[0];
        switch(obj.tipoValor)
        {
        	case '1':
            	etiqueta='<span alt="Valores registrados: '+obj.valor+'" tile="Valores registrados: '+obj.valor+'"><b>Listado de valores constantes</b></span>';
            break;
            case '7':
            	etiqueta='<b>Consulta auxiliar:</b> ['+obj.almacenUsr+']';
            break;
            case '11':
            	etiqueta='<b>Almac&eacute;n de datos:</b> ['+obj.almacenUsr+', '+obj.campoUsr+']';
            break;
            case '22':
            	etiqueta='<b>Funci&oacute;n:</b> '+obj.funcionUsr;
            break;
            
        }
        
    }
    gE('txtRegistrosConsidera').innerHTML=etiqueta;
}

function mostrarVentanaSeleccionCampos(nTabla,nTablaUsr,tipoImportacion,iConexion)
{
	var cmbCampoId=crearComboExt('cmbCampoId',[],120,5,300);
    var cmbCampoEtiqueta=crearComboExt('cmbCampoEtiqueta',[],120,35,300);
	var form = new Ext.form.FormPanel(	
										{
											baseCls: 'x-plain',
											layout:'absolute',
											defaultType: 'label',
											items: 	[
														{
                                                        	x:10,
                                                            y:10,
                                                            html:'Campo ID:'
                                                        },
                                                        cmbCampoId,
                                                        {
                                                        	x:10,
                                                            y:40,
                                                            html:'Campo etiqueta:'
                                                        },
                                                        cmbCampoEtiqueta

													]
										}
									);
	
	var ventanaAM = new Ext.Window(
									{
										title: 'Importaci&oacute;n de categor&iacute;as de tabla de sistema ['+nTablaUsr+']',
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
																}
															}
												},
										buttons:	[
														{
															
															text: '<?php echo $etj["lblBtnAceptar"]?>',
															handler: function()
																	{
																		if(cmbCampoId.getValue()=='')
                                                                        {
                                                                        	function resp()
                                                                            {
                                                                            	cmbCampoId.focus();
                                                                            }
                                                                            msgBox('Debe indicar el campo que ser&aacute; utilizado como valor ID de la tabla',resp);
                                                                        }
                                                                        
                                                                        if(cmbCampoEtiqueta.getValue()=='')
                                                                        {
                                                                        	function resp2()
                                                                            {
                                                                            	cmbCampoEtiqueta.focus();
                                                                            }
                                                                            msgBox('Debe indicar el campo que ser&aacute; utilizado como etiqueta del valor ID',resp2);
                                                                        }
                                                                        
                                                                        function funcAjax()
                                                                        {
                                                                            var resp=peticion_http.responseText;
                                                                            arrResp=resp.split('|');
                                                                            if(arrResp[0]=='1')
                                                                            {
                                                                            	var cmbOrigenCategoria=gEx('cmbOrigenCategoria');
                                                                            	
                                                                             	var arrDatos=eval(arrResp[1]);
                                                                                var x;
                                                                                if(tipoImportacion==0)
                                                                                {
                                                                                	var gridCategoriasGrafico=gEx('gridCategoriasGrafico');
	                                                                                gridCategoriasGrafico.getStore().removeAll();
                                                                                    var registro=crearRegistro	(
                                                                                                                    [
                                                                                                                        {name: 'idCategoria'},
                                                                                                                        {name: 'categoria'},
                                                                                                                        {name:'color'},
                                                                                                                        {name:'tipo'},
                                                                                                                        {name:'comp1'},
                                                                                                                        {name: 'valor'}
                                                                                                                    ]
                                                                                                                )
                                                                                    var r;
                                                                                    for(x=0;x<arrDatos.length;x++)
                                                                                    {
                                                                                        r=new registro	(
                                                                                                            {
                                                                                                                idCategoria:-1,
                                                                                                                categoria:arrDatos[x][1],
                                                                                                                color:'',
                                                                                                                tipo:cmbOrigenCategoria.getValue(),
                                                                                                                comp1:'',
                                                                                                                valor:"'"+arrDatos[x][0]+"'"
                                                                                                            }
                                                                                                        )
                                                                                        gridCategoriasGrafico.getStore().add(r);
                                                                                    }   
                                                                                }
                                                                                else
                                                                                {
                                                                                	var gridSeriesGrafico=gEx('gridSeriesGrafico');
	                                                                                gridSeriesGrafico.getStore().removeAll();
                                                                                    var registro=crearRegistro	(
                                                                                                                    [
                                                                                                                        {name: 'idSerie'},
                                                                                                                        {name: 'serie'},
                                                                                                                        {name: 'leyenda'},
                                                                                                                        {name:'color'},
                                                                                                                        {name:'tipo'},
                                                                                                                        {name:'comp1'},
                                                                                                                        {name: 'valor'}
                                                                                                                    ]
                                                                                                                )
                                                                                    var r;
                                                                                    for(x=0;x<arrDatos.length;x++)
                                                                                    {
                                                                                        r=new registro	(
                                                                                                            {
                                                                                                                idSerie:-1,
                                                                                                                serie:arrDatos[x][1],
                                                                                                                leyenda:arrDatos[x][1],
                                                                                                                color:'',
                                                                                                                tipo:cmbOrigenCategoria.getValue(),
                                                                                                                comp1:'',
                                                                                                                valor:"'"+arrDatos[x][0]+"'"
                                                                                                            }
                                                                                                        )
                                                                                        gridSeriesGrafico.getStore().add(r);
                                                                                    }   
                                                                                }
                                                                                ventanaAM.close();
                                                                            }
                                                                            else
                                                                            {
                                                                                msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
                                                                            }
                                                                        }
                                                                        obtenerDatosWeb('../paginasFunciones/funcionesThot.php',funcAjax, 'POST','funcion=52&idConexionAlmacen='+iConexion+'&t='+bE(nTabla)+'&i='+bE(cmbCampoId.getValue())+'&e='+bE(cmbCampoEtiqueta.getValue()),true);

                                                                        
                                                                        
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
	function funcAjax()
    {
        var resp=peticion_http.responseText;
        arrResp=resp.split('|');
        if(arrResp[0]=='1')
        {
          	var arrDatos=eval(arrResp[1]);
			cmbCampoId.getStore().loadData(arrDatos);
            cmbCampoEtiqueta.getStore().loadData(arrDatos);
            ventanaAM.show()
        }
        else
        {
            msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
        }
    }
    obtenerDatosWeb('../paginasFunciones/funcionesThot.php',funcAjax, 'POST','funcion=50&idConexionAlmacen='+iConexion+'&t='+bE(nTabla),true);                                
	
}

function obtenerCategoriasAlmacenGrafico(idGrafico,ventana)
{
	function funcAjax()
    {
        var resp=peticion_http.responseText;
        arrResp=resp.split('|');
        if(arrResp[0]=='1')
        {
            
            if(ventana!=undefined)
            {
            	ventana.show();
            	if(arrResp[2]!='')
                {
                	var obj=eval('['+arrResp[2]+']')[0];
                   
                    gEx('cmbOrigenCategoria').setValue(obj.tipo);
                    dispararEventoSelectCombo('cmbOrigenCategoria');
                    gEx('hCondiciones').setValue(obj.condicionFiltro);
                    switch(obj.tipo)
                    {
                    	case '1':
                        	gEx('txtTabla').setValue(obj.tablaUsr);
                            gEx('txtTabla').tablaOriginal=obj.tablaOrigen;
                            gEx('txtTabla').idConexionAlmacen=obj.idConexion;
                            function funcAjax()
                            {
                                var resp=peticion_http.responseText;
                                arrResp=resp.split('|');
                                if(arrResp[0]=='1')
                                {
                                    gEx('cmbCampoTabla').reset();
                                    gEx('cmbCampoTabla').getStore().loadData(eval(arrResp[1]));
                                    gEx('cmbCampoTabla').setValue(obj.campo);
                                }
                                else
                                {
                                    msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
                                }
                            }
                            obtenerDatosWeb('../paginasFunciones/funcionesThot.php',funcAjax, 'POST','funcion=50&idConexionAlmacen='+obj.idConexion+'&t='+bE(obj.tablaOrigen),true);
                            
                        break;
                        case '2':
                        	gEx('txtTabla').setValue(obj.funcionUsr);
                            gEx('txtTabla').idFuncion=obj.tablaOrigen;
                            
                            
                            
                        break;
                    }
                    formatearValorFiltro();
                }
            }
            gEx('gridCategoriasGrafico').getStore().loadData(eval(arrResp[1]));
        }
        else
        {
            msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
        }
    }
    obtenerDatosWeb('../paginasFunciones/funcionesThot.php',funcAjax, 'POST','funcion=32&idAlmacen='+idGrafico,true);
    

}

function mostrarVentanaNuevoAlmacenGrafico()
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
                                                            html:'Nombre del almac&eacute;n:'
                                                        },
                                                        {
                                                        	xtype:'textfield',
                                                            x:130,
                                                            y:5,
                                                            id:'txtNombreAlmacen',
                                                            width:300
                                                        },
                                                        {
                                                        	x:10,
                                                            y:35,
                                                            html:'Descripci&oacute;n:'
                                                        },
                                                        {
                                                        	x:130,
                                                            y:30,
                                                            xtype:'textarea',
                                                            height:80,
                                                            width:300,
                                                            id:'txtDescripcion'
                                                        }

													]
										}
									);
	
	var ventanaAM = new Ext.Window(
									{
										title: 'Nuevo almac&eacute;n',
										width: 470,
										height:210,
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
                                                                	gEx('txtNombreAlmacen').focus();
																}
															}
												},
										buttons:	[
														{
															
															text: '<?php echo $etj["lblBtnAceptar"]?>',
                                                            
															handler: function()
																	{
																		var txtNombreAlmacen=gEx('txtNombreAlmacen');
                                                                        if(txtNombreAlmacen.getValue()=='')
                                                                        {
                                                                        	function resp()
                                                                            {
                                                                            	txtNombreAlmacen.focus();
                                                                            }
                                                                        	msgBox('Debe ingresar el nombre del almac&eacute;n',resp);
                                                                            return;
                                                                        }
                                                                        var txtDescripcion=gEx('txtDescripcion');
                                                                        var idReporte=gE('idReporte').value;
                                                                        function funcAjax()
                                                                        {
                                                                            var resp=peticion_http.responseText;
                                                                            arrResp=resp.split('|');
                                                                            if(arrResp[0]=='1')
                                                                            {
                                                                            	gEx(idArbolDataSet).getRootNode().reload();
                                                                            	mostrarVentanaDatos(arrResp[1]);
                                                                                gEx('cmbAlmacenGraf').getStore().loadData(eval(arrResp[2]));
                                                                                ventanaAM.close();
                                                                            }
                                                                            else
                                                                            {
                                                                                msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
                                                                            }
                                                                        }
                                                                        obtenerDatosWeb('../paginasFunciones/funcionesThot.php',funcAjax, 'POST','funcion=34&idReporte='+idReporte+'&nombreAlmacen='+cv(txtNombreAlmacen.getValue())+'&descripcion='+cv(txtDescripcion.getValue()),true);
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

function mostrarVentanaAgregarCategoria(idGrafico,fila)
{
	var gridCategoriasValores=crearGridCategoriasValores();
    var ocultarLblValores=false;
    var alto=350;
    if(gEx('cmbOrigenCategoria').getValue()=='2')
    {
    	gridCategoriasValores.hide();
        ocultarLblValores=true;
        alto=150;
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
                                                            html:'Nombre de la categor&iacute;a:'
                                                        },
                                                        {
                                                        	xtype:'textfield',
                                                            x:140,
                                                            y:5,
                                                            id:'txtNombreCategoria',
                                                            width:250
                                                            
                                                        },
                                                        {
                                                        	x:10,
                                                            y:45,
                                                            html:'Color de la categor&iacute;a:'
                                                        },
                                                        {
                                                        	xtype:'textoBotonColor',
                                                            x:140,
                                                            y:35,
                                                            id:'txtColor',
                                                            width:150,
                                                            listeners:{
                                                            				'focus':function(control)
                                                                            		{
                                                                                    	
                                                                                    }
                                                            			}
                                                        },
                                                         {
                                                        	x:10,
                                                            y:75,
                                                            hidden:ocultarLblValores,
                                                            html:'Valores a considerar en la categor&iacute;a:'
                                                        },
                                                        gridCategoriasValores
                                                        

													]
										}
									);
	
	var ventanaAM = new Ext.Window(
									{
										title: 'Agregar categor&iacute;a',
										width: 550,
										height:alto,
										layout: 'fit',
										plain:true,
										modal:true,
										bodyStyle:'padding:5px;',
										buttonAlign:'center',
										items: form,
										listeners : {
													show : {
																buffer : 100,
																fn : function() 
																{
                                                                	gEx('txtNombreCategoria').focus();
                                                                    

																}
															}
												},
										buttons:	[
														{
															
															text: '<?php echo $etj["lblBtnAceptar"]?>',
															handler: function()
																	{
																		var txtNombreCategoria=gEx('txtNombreCategoria');
                                                                        if(txtNombreCategoria.getValue()=='')
                                                                        {
                                                                        	function resp()
                                                                            {
                                                                            	txtNombreCategoria.focus();
                                                                            }
                                                                        	msgBox('Debe ingresar el nombre de la categor&iacute;a',resp);
                                                                        	return;
                                                                        }
                                                                        
                                                                        var registro=crearRegistro	(
                                                                                                        [
                                                                                                            {name: 'idCategoria'},
                                                                                                            {name: 'categoria'},
                                                                                                            {name:'color'},
                                                                                                            {name:'tipo'},
                                                                                                            {name:'comp1'},
                                                                                                            {name: 'valor'}
                                                                                                        ]
                                                                                                    );
                                                                        var valCategorias='';
                                                                        var gridValoresCategoria=gEx('gridValoresCategoria');
                                                                        var x;
                                                                        var filaReg;
                                                                        for(x=0;x<gridValoresCategoria.getStore().getCount();x++)                            
                                                                        {
                                                                        	filaReg=gridValoresCategoria.getStore().getAt(x);
                                                                            if(valCategorias=='')
                                                                            	valCategorias="'"+filaReg.get('valor')+"'";
                                                                            else
                                                                            	valCategorias+=",'"+filaReg.get('valor')+"'";
                                                                            
                                                                        }
                                                                        if(!fila)
                                                                        {
                                                                            var r=new registro	(
                                                                                                    {
                                                                                                        idCategoria:-1,
                                                                                                        categoria:txtNombreCategoria.getValue(),
                                                                                                        color:gEx('txtColor').getValue().replace(/#/,''),
                                                                                                        tipo:gEx('cmbOrigenCategoria').getValue(),
                                                                                                        comp1:'',
                                                                                                        valor:valCategorias
                                                                                                    }
                                                                                                  )
                                                                           gEx('gridCategoriasGrafico').getStore().add(r);
                                                                       }
                                                                       else
                                                                       {
                                                                       		fila.set('categoria',txtNombreCategoria.getValue());
                                                                            fila.set('color',gEx('txtColor').getValue().replace(/#/,''));
                                                                            fila.set('valor',valCategorias);
                                                                           
                                                                       }
                                                                       ventanaAM.close();
                                                                       /* function funcAjax()
                                                                        {
                                                                            var resp=peticion_http.responseText;
                                                                            arrResp=resp.split('|');
                                                                            if(arrResp[0]=='1')
                                                                            {
                                                                            	gEx('gridCategoriasGrafico').getStore().loadData(eval(arrResp[1]));
                                                                                llenarConfiguracionDatosAlmacen(idGrafico);
                                                                                ventanaAM.close();
                                                                            }
                                                                            else
                                                                            {
                                                                                msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
                                                                            }
                                                                        }
                                                                        obtenerDatosWeb('../paginasFunciones/funcionesThot.php',funcAjax, 'POST','funcion=36&color='+gEx('txtColor').getValue()+'&idAlmacen='+idGrafico+'&nCategoria='+cv(txtNombreCategoria.getValue()),true);*/
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
    if(fila)                                                    
    {
    	gEx('txtNombreCategoria').setValue(fila.get('categoria'));
        gEx('txtColor').setValue(fila.get('color'));
        if(fila.get('valor')!='')
        	gridCategoriasValores.getStore().loadData(eval('[['+fila.get('valor')+']]'));
    }
}

function crearGridCategoriasValores()
{
	var dsDatos=[];
    var alDatos=	new Ext.data.SimpleStore	(
                                                {
                                                    fields:	[
                                                                {name: 'valor'}
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
															header:'Valor',
															width:250,
															sortable:true,
															dataIndex:'valor',
                                                            editor:{xtype:'textfield'}
														}
													]
												);
                                                
	var tblGrid=	new Ext.grid.EditorGridPanel	(
                                                        {
                                                            id:'gridValoresCategoria',
                                                            store:alDatos,
                                                            frame:true,
                                                            x:100,
                                                            y:105,
                                                            cm: cModelo,
                                                            stripeRows :true,
                                                            loadMask:true,
                                                            stripeRows :true,
                                                            columnLines : true,
                                                            height:160,
                                                            width:350,
                                                            sm:chkRow,
                                                            tbar:	[
                                                            			{
                                                                        	icon:'../images/add.png',
                                                                            cls:'x-btn-text-icon',
                                                                            text:'Agregar valor',
                                                                            handler:function()
                                                                            		{
                                                                                    	var reg=crearRegistro([{"name":"valor"}]);
                                                                                        var r=new reg({valor:''});
                                                                                        tblGrid.getStore().add(r);
                                                                                        tblGrid.startEditing(tblGrid.getStore().getCount()-1,2);
                                                                                    }
                                                                            
                                                                        },'-',
                                                                        
                                                                        {
                                                                        	icon:'../images/delete.png',
                                                                            cls:'x-btn-text-icon',
                                                                            text:'Remover valor',
                                                                            handler:function()
                                                                            		{
                                                                                    	var filas=tblGrid.getSelectionModel().getSelections();
                                                                                        if(filas.length==0)
                                                                                        {
                                                                                        	msgBox('Debe selecceionar al menos un elemento a remover');
                                                                                        	return;
                                                                                        }
                                                                                        function resp(btn)
                                                                                        {
                                                                                        	if(btn=='yes')
                                                                                            {
                                                                                            	tblGrid.getStore().remove(filas);
                                                                                            }
                                                                                        }
                                                                                        msgConfirm('Est&aacute; seguro de querer remover los elementos seleccionados?',resp)
                                                                                    }
                                                                            
                                                                        }
                                                                        
                                                            		]
                                                        }
                                                    );
	return 	tblGrid;
}

function mostrarVentanaAdmonSeries(idGrafico)
{
	var cmbOrigenCategoria=crearComboExt('cmbOrigenCategoria',arrOrigenSeries,205,5);
    cmbOrigenCategoria.setValue('0');
    cmbOrigenCategoria.on('select',function(cmb,registro)
    								{
                                    	
                                   	 	gEx('gridSeriesGrafico').getStore().removeAll();
                                        gEx('cmbCampoTabla').reset();
                                        gEx('txtTabla').setValue('');
                                    	switch(registro.get("id"))
                                        {
                                        	case '0':
                                            	gEx('gridSeriesGrafico').enable();
                                                gEx('gridSeriesGrafico').getColumnModel().setHidden(5,false);
                                                gEx('lblCampoTabla').hide();
                                                gEx('cmbCampoTabla').hide();
                                                gEx('lblEtParam1').hide();
                                                gEx('txtTabla').hide();
                                                gEx('lblMostrarReferencia').hide();
                                                gEx('lblFiltro').hide();
                                                gEx('lblTtxFiltro').hide();

                                            break;
                                            case '1':
                                            	gEx('gridSeriesGrafico').enable();
                                                gEx('gridSeriesGrafico').getColumnModel().setHidden(5,false);
                                                gEx('lblCampoTabla').show();
                                                gEx('cmbCampoTabla').show();
                                                gEx('lblEtParam1').show();
                                                gEx('lblEtParam1').setText('Tabla de sistema',false);
                                                gEx('txtTabla').show();
                                                gEx('lblMostrarReferencia').show();
                                                gEx('lblFiltro').show();
                                                gEx('lblTtxFiltro').show();
                                            break;
                                            case '2':
                                            	gEx('gridSeriesGrafico').disable();
                                                gEx('gridSeriesGrafico').getColumnModel().setHidden(5,true);
                                                gEx('lblCampoTabla').hide();
                                                gEx('cmbCampoTabla').reset();
                                                gEx('cmbCampoTabla').hide();
                                                gEx('lblEtParam1').show();
                                                gEx('lblEtParam1').setText('Funci&oacute;n de sistema',false);
                                                gEx('txtTabla').show();
                                                gEx('lblMostrarReferencia').show();
                                                gEx('lblFiltro').hide();
                                                gEx('lblTtxFiltro').hide();
                                            break;
                                        }
                                    }
    					)
	var cmbCampoTabla=crearComboExt('cmbCampoTabla',[],150,65,250); 
    cmbCampoTabla.hide();    
    
    cmbCampoTabla.on('select',function(cmb,registro)
    							{
                                	function funcAjax()
                                    {
                                        var resp=peticion_http.responseText;
                                        arrResp=resp.split('|');
                                        if(arrResp[0]=='1')
                                        {
                                        	var gridSeriesGrafico=gEx('gridSeriesGrafico');
                                             gridSeriesGrafico.getStore().removeAll();
                                            if(arrResp[1]!='-1')
                                            {
                                            	var arrDatos=eval(arrResp[1]);
                                                var x;
                                                
                                                var registro=crearRegistro	(
                                                								[
                                                                                	{name: 'idSerie'},
                                                                                    {name: 'serie'},
                                                                                    {name: 'leyenda'},
                                                                                    {name:'color'},
                                                                                    {name:'tipo'},
                                                                                    {name:'comp1'},
                                                                                    {name: 'valor'}
                                                                                ]
                                                							)
                                                var r;
                                                for(x=0;x<arrDatos.length;x++)
                                                {
                                                	r=new registro	(
                                                    					{
                                                                        	idSerie:-1,
                                                                            serie:arrDatos[x][1],
                                                                            leyenda:arrDatos[x][1],
                                                                            color:'',
                                                                            tipo:cmbOrigenCategoria.getValue(),
                                                                            comp1:'',
                                                                            valor:"'"+arrDatos[x][0]+"'"
                                                                        }
                                                    				)
                                                	gridSeriesGrafico.getStore().add(r);
                                                }
                                            }
                                            else
                                            {
                                            	msgBox('No se ha podido determinar la diferentes categor&iacute;as asociadas con el campo seleccionado, deber&aacute; ser definido de manera manual');
                                            }
                                        }
                                        else
                                        {
                                            msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
                                        }
                                    }
                                    obtenerDatosWeb('../paginasFunciones/funcionesThot.php',funcAjax, 'POST','funcion=51&idConexionAlmacen='+gEx('txtTabla').idConexionAlmacen+'&c='+bE(registro.get('id'))+'&t='+bE(gEx('txtTabla').tablaOriginal),true);
                                }
    				)
	var gridSeries=crearGridSeries(idGrafico);
	var form = new Ext.form.FormPanel(	
										{
											baseCls: 'x-plain',
											layout:'absolute',
											defaultType: 'label',
											items: 	[
                                            			{
                                                        	x:10,
                                                            y:10,
                                                            html:'Determinar series mediante:'
                                                        },
                                                        cmbOrigenCategoria,
                                                        {
                                                        	x:10,
                                                            y:40,
                                                            id:'lblEtParam1',
                                                            hidden:true,
                                                            html:'Tabla de sistema:'
                                                        },
                                                        {
                                                        	xtype:'textfield',
                                                            x:150,
                                                            y:35,
                                                            id:'txtTabla',
                                                             hidden:true,
                                                            width:250,
                                                            readOnly:true

                                                        },
                                                        {
                                                        	xtype:'label',
                                                            y:35,
                                                            x:405,
                                                            hidden:true,
                                                            id:'lblMostrarReferencia',
                                                            html:'<a href="javascript:mostrarVentanaReferencia()"><img src="../images/pencil.png"></a>'
                                                        },
                                                        {
                                                        	x:10,
                                                            y:70,
                                                            hidden:true,
                                                            id:'lblCampoTabla',
                                                            html:'Campo de tabla:'
                                                        },
                                                        cmbCampoTabla,
                                                        {
                                                        	x:430,
                                                            y:70,
                                                            hidden:true,
                                                            id:'lblFiltro',
                                                            html:'Considerar registros de:'
                                                        },
                                                        {
                                                        	x:560,
                                                            y:63,
                                                            hidden:true,
                                                            id:'lblTtxFiltro',
                                                            html:'<span id="txtRegistrosConsidera" style="color: #000"><b>Todos los registros de la tabla seleccionada</b></span>&nbsp;<a href="javascript:modificarFiltroRegistros()"><img src="../images/pencil.png" title="Modificar filtro" alt="Modificar filtro"></a>'
                                                        },
                                                        {
                                                        	x:10,
                                                            y:10,
                                                            xtype:'hidden',
                                                            id:'hCondiciones'
                                                        },
                                                        
														gridSeries
													]
										}
									);
	
	var ventanaAM = new Ext.Window(
									{
										title: 'Administraci&oacute;n de series de datos',
										width: 850,
										height:430,
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
																		
                                                                        var x;
                                                                        var fila;
                                                                        var cadObj='';
                                                                        var obj='';
                                                                        var gridCategorias=gEx('gridSeriesGrafico');
                                                                        for(x=0;x<gridCategorias.getStore().getCount();x++)
                                                                        {
                                                                        	fila=gridCategorias.getStore().getAt(x);
                                                                            obj='{"idSerie":"'+fila.get('idSerie')+'","serie":"'+cv(fila.get('serie'))+'","leyenda":"'+cv(fila.get('leyenda'))+'","color":"'+fila.get('color')+
                                                                            	'","tipo":"'+fila.get('tipo')+'","comp":"'+fila.get('comp1')+'","valor":"'+fila.get('valor')+'"}';
                                                                            if(cadObj=='')
                                                                            	cadObj=obj;
                                                                            else
                                                                            	cadObj+=","+obj;
                                                                        }
                                                                        
                                                                        var val1='';
                                                                        var val2='';
                                                                        var val3='';
                                                                        var txtTabla=gEx('txtTabla');
                                                                        var cadComplementario='';
                                                                        if(cmbOrigenCategoria.getValue()=='1')
                                                                        {
                                                                        	if(txtTabla.getValue()=='')
                                                                            {
                                                                            	function resp()
                                                                                {
                                                                                	txtTabla.focus();
                                                                                }
                                                                                msgBox('Debe indicar la tabla de la cual se tomar&aacute; el campo que ser&aacute; utilizado como serie del gr&aacute;fico',resp);
                                                                                return;
                                                                            }
                                                                            if(cmbCampoTabla.getValue()=='')
                                                                            {
                                                                            	function resp3()
                                                                                {
                                                                                	cmbCampoTabla.focus();
                                                                                }
                                                                                msgBox('Debe indicar el campo que ser&aacute; utilizado como serie del gr&aacute;fico',resp3);
                                                                                return;
                                                                            }
                                                                            val1=txtTabla.tablaOriginal;
                                                                            val2=txtTabla.getValue();
                                                                            val3=txtTabla.tipoTabla;
                                                                            val4=cmbCampoTabla.getValue();
                                                                            cadComplementario='{"idConexion":"'+txtTabla.idConexionAlmacen+'","tipo":"'+cmbOrigenCategoria.getValue()+'","tablaOrigen":"'+val1+'","tablaUsr":"'+val2+'","tipoTabla":"'+val3+'","campo":"'+val4+'"}';
                                                                            
                                                                        }
                                                                        
                                                                        if(cmbOrigenCategoria.getValue()=='2')
                                                                        {
                                                                        	if(txtTabla.getValue()=='')
                                                                            {
                                                                            	function resp2()
                                                                                {
                                                                                	txtTabla.focus();
                                                                                }
                                                                                msgBox('Debe indicar la funci&oacute;n de sistema que ser&aacute; utilizado como serie del gr&aacute;fico',resp2);
                                                                                return;
                                                                            }
                                                                            cadComplementario='{"tipo":"'+cmbOrigenCategoria.getValue()+'","idFuncion":"'+txtTabla.idFuncion+'","funcionUsr":"'+txtTabla.getValue()+'"}';
                                                                        }
                                                                        
                                                                        cadObj='{"condicionesFiltro":"'+bE(gEx('hCondiciones').getValue())+'","complementario":"'+bE(cadComplementario)+'","idAlmacen":"'+idGrafico+'","series":['+cadObj+']}';
                                                                        
                                                                        function funcAjax()
                                                                        {
                                                                            var resp=peticion_http.responseText;
                                                                            arrResp=resp.split('|');
                                                                            if(arrResp[0]=='1')
                                                                            {
                                                                            	llenarConfiguracionDatosAlmacen(idGrafico);
                                                                            	ventanaAM.close();
                                                                            }
                                                                            else
                                                                            {
                                                                                msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
                                                                            }
                                                                        }
                                                                        obtenerDatosWeb('../paginasFunciones/funcionesThot.php',funcAjax, 'POST','funcion=39&cadObj='+cadObj,true);
																	}
														},
														{
															text: 'Cerrar',
															handler:function()
																	{
																		ventanaAM.close();
																	}
														}
													]
									}
								);
	obtenerSeriesAlmacenGrafico(idGrafico,ventanaAM);                                
}

function crearGridSeries(idGrafico)
{
	var dsDatos=[];
    var alDatos=	new Ext.data.SimpleStore	(
                                                    {
                                                        fields:	[
                                                                    {name: 'idSerie'},
                                                                    {name: 'serie'},
                                                                    {name: 'leyenda'},
                                                                    {name: 'color'},
                                                                    {name:'tipo'},
                                                                    {name:'comp1'},
                                                                    {name: 'valor'}
                                                                ]
                                                    }
                                                );

    alDatos.loadData(dsDatos);
	var chkRow=new Ext.grid.CheckboxSelectionModel({singleSelect:true});
	
	var cModelo= new Ext.grid.ColumnModel   	(
												 	[
													 	new  Ext.grid.RowNumberer(),
														chkRow,
														{
															header:'Serie',
															width:150,
															sortable:true,
															dataIndex:'serie'
														},
                                                        {
															header:'Leyenda',
															width:250,
															sortable:true,
															dataIndex:'leyenda'
														},
                                                        {
															header:'Color',
															width:100,
															sortable:true,
															dataIndex:'color',
                                                            renderer:function(val)
                                                            		{
                                                                    	return '<span style="border-style:solid; border-width:1px; border-color:#000;height:10px;width:10px;background-color:#'+val+'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;&nbsp;'+val;
                                                                    }
														},
                                                         {
                                                        	header:'Valores de la serie',
                                                            width:200,
                                                            sortable:true,
															dataIndex:'valor'
                                                        }
													]
												);
                                                
	var tblGrid=	new Ext.grid.EditorGridPanel	(
                                                        {
                                                            id:'gridSeriesGrafico',
                                                            store:alDatos,
                                                            frame:true,
                                                            x:10,
                                                            y:110,
                                                            cm: cModelo,
                                                            height:230,
                                                            width:800,
                                                            sm:chkRow,
                                                            tbar:	[
                                                            			{
                                                                        	icon:'../images/add.png',
                                                                            cls:'x-btn-text-icon',
                                                                            text:'Agregar Serie',
                                                                            handler:function()
                                                                            		{
                                                                                    	mostrarVentanaAgregarSerie(idGrafico);
                                                                                    }
                                                                            
                                                                        },'-',
                                                                         {
                                                                        	icon:'../images/pencil.png',
                                                                            cls:'x-btn-text-icon',
                                                                            text:'Modificar serie',
                                                                            handler:function()
                                                                            		{
                                                                                    	var fila=tblGrid.getSelectionModel().getSelected();
                                                                                        if(fila==null)
                                                                                        {
                                                                                        	msgBox('Debe seleccionar la serie que desea modificar');
                                                                                            return;
                                                                                        }
                                                                                    	mostrarVentanaAgregarSerie(idGrafico,fila);
                                                                                    }
                                                                            
                                                                        },'-',
                                                                        {
                                                                        	icon:'../images/delete.png',
                                                                            cls:'x-btn-text-icon',
                                                                            text:'Remover serie',
                                                                            handler:function()
                                                                            		{
                                                                                    	var fila=tblGrid.getSelectionModel().getSelected();
                                                                                        if(fila==null)
                                                                                        {
                                                                                        	msgBox('Debe seleccionar la serie a remover');
                                                                                            return;
                                                                                        }
                                                                                        function resp(btn)
                                                                                        {
                                                                                        	if(btn=='yes')
                                                                                            {
                                                                                            	tblGrid.getStore().remove(fila);
                                                                                            }
                                                                                        }
                                                                                        msgConfirm('Est&aacute; seguro de querer remover la serie de datos seleccionada',resp);
                                                                                    }
                                                                            
                                                                        },'-',
                                                                        {
                                                                        	icon:'../images/database_table.png',
                                                                            cls:'x-btn-text-icon',
                                                                            text:'Importar serie de tabla de sistema',
                                                                            handler:function()
                                                                            		{
                                                                                    	var objConf={};
                                                                                        objConf.titulo='Seleccione la tabla cuyo campo desea utilizar como origen de serie',
											    										objConf.funcionSeleccion=function(fila,ventana,idConexion)
                                                                                        						{
                                                                                                                	ventana.close();
                                                                                                                	mostrarVentanaSeleccionCampos(fila.get('nomTablaOriginal'),fila.get('tabla'),1,idConexion);
                                                                                                                }
                                                                                    	mostrarInstanciaVentanaSeleccionTabla(objConf)
                                                                                    }
                                                                        }
                                                                        
                                                            		]
                                                        }
                                                    );
	return 	tblGrid;	
}

function obtenerSeriesAlmacenGrafico(idGrafico,ventana)
{
	function funcAjax()
    {
        var resp=peticion_http.responseText;
        arrResp=resp.split('|');
        if(arrResp[0]=='1')
        {
            
            if(ventana!=undefined)
            {
	            ventana.show();
                if(arrResp[2]!='')
                {
                	var obj=eval('['+arrResp[2]+']')[0];
                    gEx('cmbOrigenCategoria').setValue(obj.tipo);
                    dispararEventoSelectCombo('cmbOrigenCategoria');
                    switch(obj.tipo)
                    {
                    	case '1':
                        	gEx('txtTabla').setValue(obj.tablaUsr);
                            gEx('txtTabla').tablaOriginal=obj.tablaOrigen;
                            gEx('txtTabla').idConexionAlmacen=obj.idConexion;
                            gEx('hCondiciones').setValue(obj.condicionFiltro);
                            function funcAjax()
                            {
                                var resp=peticion_http.responseText;
                                arrResp=resp.split('|');
                                if(arrResp[0]=='1')
                                {
                                    gEx('cmbCampoTabla').reset();
                                    gEx('cmbCampoTabla').getStore().loadData(eval(arrResp[1]));
                                    gEx('cmbCampoTabla').setValue(obj.campo);
                                }
                                else
                                {
                                    msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
                                }
                            }
                            obtenerDatosWeb('../paginasFunciones/funcionesThot.php',funcAjax, 'POST','funcion=50&idConexionAlmacen='+obj.idConexion+'&t='+bE(obj.tablaOrigen),true);
                            
                        break;
                        case '2':
                        	gEx('txtTabla').setValue(obj.funcionUsr);
                            gEx('txtTabla').idFuncion=obj.tablaOrigen;
                            
                            
                            
                        break;
                    }
                    formatearValorFiltro();
                }
            }
            gEx('gridSeriesGrafico').getStore().loadData(eval(arrResp[1]));
        }
        else
        {
            msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
        }
    }
    obtenerDatosWeb('../paginasFunciones/funcionesThot.php',funcAjax, 'POST','funcion=37&idAlmacen='+idGrafico,true);
    

}

function mostrarVentanaAgregarSerie(idGrafico,fila)
{
	var gridCategoriasValores=crearGridCategoriasValores();
    gridCategoriasValores.setPosition(100,135);
    var ocultarLblValores=false;
    var alto=380;
    if(gEx('cmbOrigenCategoria').getValue()=='2')
    {
    	gridCategoriasValores.hide();
        ocultarLblValores=true;
        alto=180;
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
                                                            html:'Nombre de la serie:'
                                                        },
                                                        {
                                                        	xtype:'textfield',
                                                            x:140,
                                                            y:5,
                                                            id:'txtNombreSerie',
                                                            width:250
                                                            
                                                        },
                                                        {
                                                        	x:10,
                                                            y:45,
                                                            html:'Leyenda de la serie:'
                                                        },
                                                        {
                                                        	xtype:'textfield',
                                                            x:140,
                                                            y:40,
                                                            id:'txtLeyenda',
                                                            width:250
                                                            
                                                        },
                                                        {
                                                        	x:10,
                                                            y:80,
                                                            html:'Color de la serie:'
                                                        },
                                                        {
                                                        	xtype:'textoBotonColor',
                                                            x:140,
                                                            y:75,
                                                            id:'txtColor',
                                                            width:110
                                                            
                                                        },
                                                        {
                                                        	x:10,
                                                            y:110,
                                                            hidden:ocultarLblValores,
                                                            html:'Valores a considerar en la categor&iacute;a:'
                                                        },
                                                        gridCategoriasValores

													]
										}
									);
	
	var ventanaAM = new Ext.Window(
									{
										title: 'Agregar serie de datos',
										width: 550,
										height:alto,
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
                                                                	gEx('txtNombreSerie').focus();
																}
															}
												},
										buttons:	[
														{
															
															text: '<?php echo $etj["lblBtnAceptar"]?>',
															handler: function()
																	{
																		var txtNombreSerie=gEx('txtNombreSerie');
                                                                        if(txtNombreSerie.getValue()=='')
                                                                        {
                                                                        	function resp()
                                                                            {
                                                                            	txtNombreSerie.focus();
                                                                            }
                                                                        	msgBox('Debe ingresar el nombre de la serie a agregar',resp);
                                                                        	return;
                                                                        }
                                                                        var txtLeyenda=gEx('txtLeyenda');
                                                                        if(txtLeyenda.getValue()=='')
                                                                        {
                                                                        	function resp()
                                                                            {
                                                                            	txtLeyenda.focus();
                                                                            }
                                                                        	msgBox('Debe ingresar la leyenda de la serie a agregar',resp);
                                                                        	return;
                                                                        }
                                                                        
                                                                       /* var txtColor=gEx('txtColor');
                                                                        if(txtColor.getValue()=='')
                                                                        {
                                                                        	function resp()
                                                                            {
                                                                            	txtColor.focus();
                                                                            }
                                                                        	msgBox('Debe ingresar el color de la serie a agregar',resp);
                                                                        	return;
                                                                        }*/
                                                                        
                                                                        var registro=crearRegistro	(
                                                                                                        [
                                                                                                            {name: 'idSerie'},
                                                                                                            {name: 'serie'},
                                                                                                            {name: 'leyenda'},
                                                                                                            {name:'color'},
                                                                                                            {name:'tipo'},
                                                                                                            {name:'comp1'},
                                                                                                            {name: 'valor'}
                                                                                                        ]
                                                                                                    );
                                                                        var valCategorias='';
                                                                        var gridValoresCategoria=gEx('gridValoresCategoria');
                                                                        var x;
                                                                        var filaReg;
                                                                        for(x=0;x<gridValoresCategoria.getStore().getCount();x++)                            
                                                                        {
                                                                        	filaReg=gridValoresCategoria.getStore().getAt(x);
                                                                            if(valCategorias=='')
                                                                            	valCategorias="'"+filaReg.get('valor')+"'";
                                                                            else
                                                                            	valCategorias+=",'"+filaReg.get('valor')+"'";
                                                                            
                                                                        }
                                                                        if(!fila)
                                                                        {
                                                                            var r=new registro	(
                                                                                                    {
                                                                                                        idSerie:-1,
                                                                                                        serie:txtNombreSerie.getValue(),
                                                                                                        leyenda:txtLeyenda.getValue(),
                                                                                                        color:gEx('txtColor').getValue().replace(/#/,''),
                                                                                                        tipo:gEx('cmbOrigenCategoria').getValue(),
                                                                                                        comp1:'',
                                                                                                        valor:valCategorias
                                                                                                    }
                                                                                                  )
                                                                           gEx('gridSeriesGrafico').getStore().add(r);
                                                                       }
                                                                       else
                                                                       {
                                                                       		fila.set('serie',txtNombreSerie.getValue());
                                                                            fila.set('leyenda',txtLeyenda.getValue());
                                                                            fila.set('color',gEx('txtColor').getValue().replace(/#/,''));
                                                                            fila.set('valor',valCategorias);
                                                                           
                                                                       }
                                                                       ventanaAM.close();
                                                                        
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
    if(fila)                                                    
    {
    	gEx('txtNombreSerie').setValue(fila.get('serie'));
        gEx('txtLeyenda').setValue(fila.get('leyenda'));
        gEx('txtColor').setValue(fila.get('color'));
        if(fila.get('valor')!='')
	        gridCategoriasValores.getStore().loadData(eval('[['+fila.get('valor')+']]'));
    }
}

function modificarOrigenDatos(iC,iS,iG)
{
	var arrAlmacenes=[];
	var arrTipoValor=[['1','Valor constante'],['7','Consulta auxiliar'],['11','Almac\xE9n de datos'],['22','Invocaci\xF3n de funci\xF3n']];
	var cmbTipoValor=crearComboExt('cmbTipoValor',arrTipoValor,125,5);
    cmbTipoValor.setValue('1');
    cmbTipoValor.on('select',function(combo,registro)
    						{
                            	gEx('vSistema').hide();
                                gEx('btnFuncionSistema').hide();
                                gEx('txtTabla').hide();
                                gEx('txtTabla').setValue('');
                            	var vConstante=gEx('vConstante');
                                vConstante.hide();
                                var txtConstante=gEx('txtConstante');
                                txtConstante.hide();
                                txtConstante.setValue('');
                                var vAlmacen=gEx('vAlmacen');
                                vAlmacen.hide();
                                var cmbAlmacenesDatos=gEx('cmbAlmacenesDatos');
                                cmbAlmacenesDatos.hide();
                                cmbAlmacenesDatos.reset();
                                cmbAlmacenesDatos.getStore().removeAll();
                                var vCampo=gEx('vCampo');
                                vCampo.hide();
                                var cmbCampo=gEx('cmbCampo');
                                cmbCampo.hide();
                                cmbCampo.reset();
                                cmbCampo.getStore().removeAll();
                            	switch(registro.get('id'))
                                {
                                	case '1':
                                    	vConstante.show();
                                        txtConstante.show();
                                    break;
                                    case '7':
                                    case '11':
                                    	var arrAlmacenes;
                                    	if(registro.get('id')=='7')
                                        	arrAlmacenes=obtenerAlmacenesDatosDisponibles(2);
                                        else
                                        	arrAlmacenes=obtenerAlmacenesDatosDisponibles(1);
                                    	cmbAlmacenesDatos.show();
                                        cmbAlmacenesDatos.getStore().loadData(arrAlmacenes);
                                        vAlmacen.show();
                                        vCampo.show();
                                    	cmbCampo.show();
                                    break;
                                    case '22':
                                    	gEx('vSistema').show();
                                        gEx('txtTabla').show();
                                        gEx('btnFuncionSistema').show();
                                    break;
                                }
                            }
    				)
    var cmbAlmacenesDatos=crearComboExt('cmbAlmacenesDatos',arrAlmacenes,125,35,250);
    cmbAlmacenesDatos.hide();
    cmbAlmacenesDatos.on('select',function (combo,registro)
    								{
                                    	var arrCampos=obtenerCamposDisponibles(registro.get('id'));
                                        gEx('cmbCampo').reset();
                                        gEx('cmbCampo').getStore().loadData(arrCampos);
                                    }
    					)
    
    var cmbCampo=crearComboExt('cmbCampo',[],125,65,250);
    cmbCampo.hide();
	var form = new Ext.form.FormPanel(	
										{
											baseCls: 'x-plain',
											layout:'absolute',
											defaultType: 'label',
											items: 	[
														{
                                                        	x:10,
                                                            y:10,
                                                            html:'Valor a utilizar'
                                                        },
                                                        cmbTipoValor,
                                                        {
                                                        	id:'vConstante',
                                                        	x:10,
                                                            y:40,
                                                            html:'Valor constante:'
                                                        },
                                                        {
                                                        	id:'vSistema',
                                                        	x:10,
                                                            y:40,
                                                            hidden:true,
                                                            html:'Funci&oacute;n de sistema:'
                                                        },
                                                        {
                                                        	x:125,
                                                            y:35,
                                                            xtype:'numberfield',
                                                            id:'txtConstante',
                                                            allowDecimals:true,
                                                            allowNegative:true
                                                        },
                                                        {
                                                        	x:125,
                                                            y:35,
                                                            xtype:'textfield',
                                                            readOnly:true,
                                                            hidden:true,
                                                            id:'txtTabla',
                                                            width:250
                                                        },
                                                         {
                                                        	x:390,
                                                            y:35,
                                                            xtype:'label',
                                                            hidden:true,
                                                            id:'btnFuncionSistema',
                                                            html:'<a href="javascript:asignarValorFuncionSistema(1)"><img src="../images/pencil.png"></a>'
                                                        },
                                                        {
                                                        	id:'vAlmacen',
                                                        	x:10,
                                                            y:40,
                                                            hidden:true,
                                                            html:'Seleccione almac\xE9n:'
                                                        },
                                                        cmbAlmacenesDatos,
                                                        {
                                                        	id:'vCampo',
                                                        	x:10,
                                                            y:70,
                                                            hidden:true,
                                                            html:'Seleccione campo:'
                                                        },
                                                        cmbCampo,
                                                        {
                                                        	
                                                            x:10,
                                                            y:100,
                                                            html:'Color:'
                                                        },
                                                        {
                                                        	x:125,
                                                            y:95,
                                                            xtype:'textoBotonColor',
                                                            id:'txtColor'
                                                        },
                                                        {
                                                        	x:10,
                                                            y:130,
                                                            html:'Etiqueta:'
                                                        },
                                                        {
                                                        	x:125,
                                                            y:125,
                                                            width:250,
                                                            id:'txtEtiqueta',
                                                            xtype:'textfield'
                                                            
                                                        }
                                                        
	
													]
										}
									);
	
	var ventanaAM = new Ext.Window(
									{
										title: 'Insertar origen de datos',
										width: 500,
										height:250,
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
                                                                    	var tValor;
                                                                        var valor;
                                                                        tValor=cmbTipoValor.getValue();
																		switch(cmbTipoValor.getValue())
                                                                        {
                                                                        	case '1':
                                                                            	var txtConstante=gEx('txtConstante');
                                                                            	if(txtConstante.getValue()=='')
                                                                                {
                                                                                	function resp()
                                                                                    {
                                                                                    	txtConstante.focus();
                                                                                    }
                                                                                    msgBox('Debe ingresar el valor constante',resp);
                                                                                    return;
                                                                                }
                                                                                valor=txtConstante.getValue();
                                                                            break;
                                                                            case '7':
                                                                            case '11':
                                                                            	var cmbCampo=gEx('cmbCampo');
                                                                                if(cmbCampo.getValue()=='')
                                                                                {
                                                                                	function resp2()
                                                                                    {
                                                                                    	cmbCampo.focus();
                                                                                    }
                                                                                    msgBox('Debe seleccionar el campo a utilizar como valor',resp2);
                                                                                    return;
                                                                                }
                                                                                var nodoMysql=buscarNodoID(gEx(idArbolDataSet).getRootNode(),cmbCampo.getValue());
                                                                        		var campo;
                                                                                if(nodoMysql.nCampo!=undefined)
                                                                                    campo=nodoMysql.nCampo;
                                                                                else
                                                                                    campo=nodoMysql.attributes.nCampo;
                                                                                valor=cmbAlmacenesDatos.getValue()+'|'+campo;
                                                                            break;
                                                                            case '22':
                                                                            	var txtTabla=gEx('txtTabla');
                                                                                if(txtTabla.getValue()=='')
                                                                                {
                                                                                	function resp20()
                                                                                    {
                                                                                    	txtTabla.focus();
                                                                                    }
                                                                                    msgBox('Debe indicar la funci&oacute;n de sistema que ser&aacute; utilizado como origen de datos',resp20);
                                                                                    return;
                                                                                }
                                                                                valor=txtTabla.idFuncion;
                                                                            break;
                                                                        }
                                                                     	
                                                                        function funcAjax()
                                                                        {
                                                                            var resp=peticion_http.responseText;
                                                                            arrResp=resp.split('|');
                                                                            if(arrResp[0]=='1')
                                                                            {
                                                                                llenarConfiguracionDatosAlmacen(bD(iG));
                                                                                ventanaAM.close();
                                                                            }
                                                                            else
                                                                            {
                                                                                msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
                                                                            }
                                                                        }
                                                                        obtenerDatosWeb('../paginasFunciones/funcionesThot.php',funcAjax, 'POST','funcion=40&etiqueta='+gEx('txtEtiqueta').getValue()+'&idCategoria='+bD(iC)+'&idSerie='+bD(iS)+'&idAlmacen='+bD(iG)+'&tValor='+tValor+'&valor='+valor+'&color='+gEx('txtColor').getValue(),true);
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

function asignarValorFuncionSistema(r)
{
	 
	if(r==1)
    {
    	asignarFuncionNuevoConceptoInyeccion=function(idConsulta,nombre)
                                            {
                                                var iConsulta=idConsulta;
                                                var r=new registroConcepto	(
                                                                                {
                                                                                    idConsulta:iConsulta,
                                                                                    nombreConsulta:nombre,
                                                                                    nombreCategoria:'',
                                                                                    descripcion:'',
                                                                                    valorRetorno:'',
                                                                                    parametros:''
                                                                                }
                                                                            )
                                                                            
                                                conceptoSeleccionado(r, gEx('vAgregarExp'));	
                                            }
		mostrarVentanaExpresion(conceptoSeleccionado,true);
    }
    else
    {
    	asignarFuncionNuevoConceptoInyeccion=function(idConsulta,nombre)
                                            {
                                                var iConsulta=idConsulta;
                                                var r=new registroConcepto	(
                                                                                {
                                                                                    idConsulta:iConsulta,
                                                                                    nombreConsulta:nombre,
                                                                                    nombreCategoria:'',
                                                                                    descripcion:'',
                                                                                    valorRetorno:'',
                                                                                    parametros:''
                                                                                }
                                                                            )
                                                                            
                                                conceptoSeleccionadoFiltro(r, gEx('vAgregarExp'));	
                                            }
    	mostrarVentanaExpresion(conceptoSeleccionadoFiltro,true);
    }
}

function mostrarVentanaReferencia()
{
	var cmbOrigenCategoria=gEx('cmbOrigenCategoria');
    
	switch(cmbOrigenCategoria.getValue())
    {
    	case '1':
        	var objConf={
                            titulo:'Seleccione la tabla cuyo campo desea utilizar como origen de categor&iacute;a',
                            funcionSeleccion:function(fila,ventana,idConexion)
                                            {
                                                gEx('txtTabla').setValue(fila.get('tabla'));
                                                gEx('txtTabla').tablaOriginal=fila.get('nomTablaOriginal');
                                                gEx('txtTabla').idConexionAlmacen=idConexionAlmacen;
                                                if(fila.get('tipoTabla')=='Sistema')
                                                    gEx('txtTabla').tipoTabla=0;
                                                else
                                                    gEx('txtTabla').tipoTabla=1;
        
                                                ventana.close();
                                                function funcAjax()
                                                {
                                                    var resp=peticion_http.responseText;
                                                    arrResp=resp.split('|');
                                                    if(arrResp[0]=='1')
                                                    {
                                                        gEx('cmbCampoTabla').reset();
                                                        gEx('cmbCampoTabla').getStore().loadData(eval(arrResp[1]));
                                                    }
                                                    else
                                                    {
                                                        msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
                                                    }
                                                }
                                                obtenerDatosWeb('../paginasFunciones/funcionesThot.php',funcAjax, 'POST','funcion=50&idConexionAlmacen='+idConexion+'&t='+bE(fila.get('nomTablaOriginal')),true);
                                                
                                                
                                            }
                        };
        	mostrarInstanciaVentanaSeleccionTabla(objConf);
        break;
        case '2':
        	mostrarVentanaExpresion(conceptoSeleccionado,true);
        break;
    }
}

function conceptoSeleccionado(fila,ventana)
{
	gEx('txtTabla').setValue(fila.get('nombreConsulta'));
    gEx('txtTabla').idFuncion=fila.get('idConsulta');
    
    var gridCategoriasGrafico=gEx('gridCategoriasGrafico');
    if(gridCategoriasGrafico!=null)
    {
    	gridCategoriasGrafico.getStore().removeAll();
    	var registro=crearRegistro	(
                                        [
                                            {name: 'idCategoria'},
                                            {name: 'categoria'},
                                            {name:'color'},
                                            {name:'tipo'},
                                            {name:'comp1'},
                                            {name: 'valor'}
                                        ]
                                    );
		 var r=new registro	(
                                {
                                    idCategoria:-1,
                                    categoria:'Función: '+fila.get('nombreConsulta'),
                                    color:'',
                                    tipo:gEx('cmbOrigenCategoria').getValue(),
                                    comp1:fila.get('idConsulta'),
                                    valor:''
                                }
                              )
       gridCategoriasGrafico.getStore().add(r);                                    	
    }
    else
    {
    	var gridSeriesGrafico=gEx('gridSeriesGrafico');
        if(gridSeriesGrafico!=null)
        {
            gridSeriesGrafico.getStore().removeAll();
            var registro=crearRegistro	(
                                            [
                                                {name: 'idSerie'},
                                                {name: 'serie'},
                                                {name: 'leyenda'},
                                                {name:'color'},
                                                {name:'tipo'},
                                                {name:'comp1'},
                                                {name: 'valor'}
                                            ]
                                        );
                                        
             var r=new registro	(
                                  {
                                      idSerie:-1,
                                      serie:'Función: '+fila.get('nombreConsulta'),
                                      leyenda:'',
                                      color:'',
                                      tipo:gEx('cmbOrigenCategoria').getValue(),
                                      comp1:fila.get('idConsulta'),
                                      valor:''
                                  }
                                )
            gridSeriesGrafico.getStore().add(r);                                    
        }
    }
    
    
    ventana.close();
}

function conceptoSeleccionadoFiltro(fila,ventana)
{
	gEx('txtTablaFiltro').setValue(fila.get('nombreConsulta'));
    gEx('txtTablaFiltro').idFuncion=fila.get('idConsulta');
    ventana.close();
}

