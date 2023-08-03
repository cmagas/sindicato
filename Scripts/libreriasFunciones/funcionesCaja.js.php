<?php
	session_start();
	include("configurarIdiomaJS.php");
	include("conexionBD.php");
	$consulta="SELECT idMotivo,motivoDevolucion FROM 6948_motivosDevolucion where aplicableDevolucion in (0,2) ORDER BY motivoDevolucion";
	$arrMotivoDevolucion=$con->obtenerFilasArreglo($consulta);
	$consulta="SELECT idFormaPago,formaPago FROM 600_formasPago ";
	$arrFormaPago=$con->obtenerFilasArreglo($consulta);
	
	
	
?>

var tipoVentaActiva=0;//1 Venta; 2 pedido
var arrFormaPagoBD=<?php echo $arrFormaPago?>;
var arrMotivoDevolucion=<?php echo $arrMotivoDevolucion?>;
var arrCheck=[];


function buscarPorAtributo()
{}

function buscarPorNombre()
{
	buscarPorProductoNombre(gE('idZonaIVA').value);
	
}


function buscarPorProductoNombre(idZona)
{

	

	var regProducto=null;

	
    
    var gridProductoBuscar=crearGridBuscarProductoV2();
    
    
    
    
    var oConf=	{
    					idCombo:'cmbCodigoAlterno',
                        anchoCombo:200,
                        campoDesplegar:'codigoAlterno',
                        campoID:'llaveProducto',
                        funcionBusqueda:189,
                        raiz:'registros',
                        nRegistros:'numReg',
                        posX:170,
                        posY:5,
                        paginaProcesamiento:'../paginasFunciones/funcionesAlmacen.php',
                        confVista:	'<tpl for="."><div class="search-item"><table><tr><td width="380">[{codigoAlterno}] {descripcion}<br><b>Precio de venta:</b> {precioUnitario:usMoney}</td><td width="50"></td></tr></table></div></tpl>',
                        campos:	[
                                    {name:'idProducto'},
                                    {name:'llave'},
                                    {name:'codigoAlterno'},
                                    {name:'descripcion'},
                                    {name: 'tasaIVA'},
                                    {name: 'llaveProducto'},
                                    {name: 'precioUnitario'}
                                   
                                ],
                       	funcAntesCarga:function(dSet,combo)
                    				{
                                    
                                    	var arrChek=gEN('chkSel');
                                        var arrCategorias='';
                                        var x;
                                        for(x=0;x<arrChek.length;x++)
                                        {
                                            if(arrChek[x].checked)
                                            {
                                                if(arrCategorias=='')
                                                    arrCategorias="'"+arrChek[x].getAttribute('id')+"'";
                                                else
                                                    arrCategorias+=',\''+arrChek[x].getAttribute('id')+"'";
                                            }
                                        }
                                    
                                    	
                                        
                                        dSet.baseParams.valorBusqueda=gEx('cmbCodigoAlterno').getRawValue();
                                        dSet.baseParams.tipoBusqueda=1;
                                        
                                        dSet.baseParams.arrCategorias=arrCategorias;
                                        dSet.baseParams.idZona=idZona;
                                       
                                        dSet.baseParams.tipoCliente=tipoCliente;
                                        dSet.baseParams.idCliente=idCliente;
                                        dSet.baseParams.buscarPrecio=1;
                                        
                                        
                                        
                                        gEx('cmbDescripcion').setValue(''); 
                                    },
                      	funcElementoSel:function(combo,registro)
                    				{
                                    	var fila=registro;
                                        gEx('cmbCodigoAlterno').setRawValue(registro.data.codigoAlterno); 
                                        
                                       
                                          tipoBusqueda=5;
                                          gEx('txtClave').setValue(fila.data.idProducto+"_"+fila.data.llave);
                                          funcionEjecucionBusqueda=function()
                                                                  {
                                                                      gEx('txtClave').setValue('');
                                                                  }
                                          buscarCodigoProducto();
                                          
                                           
                                           gEx('vBuscarDescripcion').close();
                                        
                                    }  
    				};

    
	var cmbCodigoAlterno=crearComboExtAutocompletar(oConf);
    
    
    
     var oConf=	{
    					idCombo:'cmbDescripcion',
                        anchoCombo:330,
                        campoDesplegar:'descripcion',
                        campoID:'llaveProducto',
                        funcionBusqueda:189,
                        raiz:'registros',
                        nRegistros:'numReg',
                        posX:170,
                        posY:35,
                        paginaProcesamiento:'../paginasFunciones/funcionesAlmacen.php',
                        confVista:	'<tpl for="."><div class="search-item"><table><tr><td width="380">[{codigoAlterno}] {descripcion}<br><b>Precio de venta:</b> {precioUnitario:usMoney}</td><td width="50"></td></tr></table></div></tpl>',
                        campos:	[
                                    {name:'idProducto'},
                                    {name:'llave'},
                                    {name:'codigoAlterno'},
                                    {name:'descripcion'},
                                    {name: 'tasaIVA'},
                                    {name: 'llaveProducto'},
                                    {name: 'precioUnitario'}
                                    
                                   
                                ],
                       	funcAntesCarga:function(dSet,combo)
                    				{
                                    	var arrChek=gEN('chkSel');
                                        var arrCategorias='';
                                        var x;
                                        for(x=0;x<arrChek.length;x++)
                                        {
                                            if(arrChek[x].checked)
                                            {
                                                if(arrCategorias=='')
                                                    arrCategorias="'"+arrChek[x].getAttribute('id')+"'";
                                                else
                                                    arrCategorias+=',\''+arrChek[x].getAttribute('id')+"'";
                                            }
                                        }
                                    	
                                        
                                        dSet.baseParams.valorBusqueda=gEx('cmbDescripcion').getRawValue();
                                        dSet.baseParams.tipoBusqueda=2;
                                        
                                        dSet.baseParams.arrCategorias=arrCategorias;
                                        dSet.baseParams.idZona=idZona;
                                        
                                        
                                        dSet.baseParams.tipoCliente=tipoCliente;
                                        dSet.baseParams.idCliente=idCliente;
                                        dSet.baseParams.buscarPrecio=1;
                                        
                                        gEx('cmbCodigoAlterno').setValue(''); 
                                        
                                    },
                      	funcElementoSel:function(combo,registro)
                    				{
                                    

                                    	var fila=registro;
                                        gEx('cmbCodigoAlterno').setRawValue(registro.data.codigoAlterno); 
                                        
                                       
                                          tipoBusqueda=5;
                                          gEx('txtClave').setValue(fila.data.idProducto+"_"+fila.data.llave);
                                          funcionEjecucionBusqueda=function()
                                                                  {
                                                                      gEx('txtClave').setValue('');
                                                                  }
                                          buscarCodigoProducto();
                                          
                                           
                                           gEx('vBuscarDescripcion').close();
                                       
                                        
                                       	
                                    }  
    				};

    
	var cmbDescripcion=crearComboExtAutocompletar(oConf);
    
    
    
    
 
    
	var form = new Ext.form.FormPanel(	
										{
											baseCls: 'x-plain',
											layout:'absolute',
											defaultType: 'label',
											items: 	[
                                            			{
                                                        	xtype:'fieldset',
                                                            defaultType: 'label',
                                                            x:10,
                                                            y:10,
                                                            layout:'absolute',
                                                            width:550,
                                                            height:100,
                                                            title:'B&uacute;squeda de producto',
                                                            items:	[
                                                                        
                                                                        {
                                                                            x:10,
                                                                            y:10,
                                                                            html:'<b>C&oacute;digo alterno:</b>'
                                                                        },
                                                                        cmbCodigoAlterno,
                                                                        {
                                                                            x:10,
                                                                            y:40,
                                                                            id:'lblDescripcion',
                                                                            html:'<b>Descripci&oacute;n del producto:</b>'
                                                                        },
                                                                        cmbDescripcion
                                                                        
                                                                    ]
                                                        },
                                                        gridProductoBuscar,
                                                        crearGridCategorias()

													]
										}
									);
	
	var ventanaAM = new Ext.Window(
									{
										title: 'Buscar producto',
                                        id:'vBuscarDescripcion',
										width: 840,
										height:440,
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
                                                                	gEx('cmbCodigoAlterno').focus(false,500);
																}
															}
												},
										buttons:	[
														{
															
															text: '<?php echo $etj["lblBtnAceptar"]?>',
                                                            
															handler: function()
																	{
																		var gProductosBuscados=gEx('gProductosBuscados');
                                                                        var fila=gProductosBuscados.getSelectionModel().getSelected();
                                                                        if(!fila)
                                                                        {
                                                                        	msgBox('Debe seleccionar el producto que desea agregar');
                                                                            return;
                                                                        }
                                                                        tipoBusqueda=5;
                                                                        gEx('txtClave').setValue(fila.data.idProducto+"_"+fila.data.llave);
                                                                        funcionEjecucionBusqueda=function()
                                                                        						{
                                                                                                	gEx('txtClave').setValue('');
                                                                                                }
                                                                        buscarCodigoProducto();
                                                                        
                                                                         
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
    //cargarProductosBusqueda();
      
}

function crearGridCategorias()
{
	var store = new Ext.ux.maximgb.tg.AdjacencyListStore(	
      														{
                                                                autoLoad : true,
                                                                url: '../paginasFunciones/funcionesAlmacen.php',
                                                                reader: new Ext.data.JsonReader(
                                                                                                    {
                                                                                                        id: 'idCategoria',
                                                                                                        root: 'registros',
                                                                                                        totalProperty: 'numReg',
                                                                                                        fields:	[
                                                                                                                    {name: 'idCategoria'},
                                                                                                                    {name: 'nombreCategoria'},
                                                                                                                    {name: 'descripcion'},
                                                                                                                    {name: '_parent'},
                                                                                                                    {name: '_is_leaf', type: 'bool'},
                                                                                                                    {name: 'nivel'},
                                                                                                                    {name: 'llave'}
                                                                                                                ]
                                                                                                    }
                                                                                                    
                                                                                                )
                                                         	}
                                                          ); 
                
	
    store.on('beforeload',function(proxy)
    						{
                            	
                                proxy.baseParams.funcion=158;
                                proxy.baseParams.idAlmacen=gE('idAlmacenAsociado').value;
                            }
    		)
    				
                    
	store.on('load',function(almacen)
    				{
                    	//gEx('arbolCategorias').getStore().expandAll();
                    }    
            )
    			                    
                    
    grid=new  Ext.ux.maximgb.tg.GridPanel(	{
                                                    x:570,
                                                    y:15,
                                                    enableDD: false,
                                                    border:true,
                                                    autoScroll:true,
                                                    disableSelection:true,
                                                    
                                                    id:'arbolCategorias',
													store: store,
                                                    stripeRows: true,
                                                    
                                                    columnLines :true,
													loadMask :true,
                                                    width:220,
                                                    height:310,
                                                    columns:	[
                                                    				
                                                                    {
                                                                        header: 'Categor&iacute;a del producto',
                                                                        dataIndex: 'nombreCategoria',
                                                                        width: 180,
                                                                        renderer:function(v,meta,record)
                                                                        {
                                                                        	if(record.data._is_leaf)
                                                                            {
                                                                        		var checado='';
                                                                        		
                                                                                return [
                                                                                           '<img src="', Ext.BLANK_IMAGE_URL, '" class="ux-maximgb-tg-mastercol-icon" />',
                                                                                           '<input type="checkbox" onclick="checkSelBusqueda(this)" class="ux-maximgb-tg-mastercol-cb" ext:record-id="', record.id, '" id="'+record.data.llave+
                                                                                           '" '+checado+' name="chkSel"  />&nbsp;',
                                                                                           '<span class="ux-maximgb-tg-mastercol-editorplace">', mostrarValorDescripcion(v), '</span>'
                                                                                        ].join('');
                                                                        
                                                                  			}
                                                                            else
                                                                            {
                                                                            	var checado='';
                                                                        		
                                                                                return [
                                                                                           
                                                                                           '<span class="ux-maximgb-tg-mastercol-editorplace">', mostrarValorDescripcion(v), '</span>'
                                                                                        ].join('');
                                                                            }
                                                                            
                                                                        }
                                                                    }
                                                         		]
                                                     
                                            
                                                    
                                                }
                                          );
                                          
	
    
     
     
    
                                          
	return grid;  
   
}

function checkSelBusqueda()
{
	cargarProductosBusqueda(gE('idZonaIVA').value);
}

function cargarProductosBusqueda(idZona)
{
	var arrChek=gEN('chkSel');
    var arrCategorias='';
    var x;
    for(x=0;x<arrChek.length;x++)
    {
		if(arrChek[x].checked)
        {
            if(arrCategorias=='')
                arrCategorias="'"+arrChek[x].getAttribute('id')+"'";
            else
                arrCategorias+=',\''+arrChek[x].getAttribute('id')+"'";
        }
    }
	var gProductosBuscados=gEx('gProductosBuscados');
    gProductosBuscados.getStore().load	(
    										{
                                            	url: '../paginasFunciones/funcionesAlmacen.php',
                                                
                                                params:	{
                                                			funcion:175,
                                                            categorias:arrCategorias,
                                                			criterio:'2',
                                                            valor:'',
                                                            idZona:idZona,
                                                            
                                                		}
                                            }
    									)
}

function crearGridBuscarProductoV2()
{
	var lector= new Ext.data.JsonReader({
                                            
                                            totalProperty:'numReg',
                                            fields: [
                                               			{name:'idProducto'},
		                                                {name: 'nombreProducto'},
                                                        {name: 'llave'},
                                                        {name: 'tasaIVA'},
                                                        {name: 'precioUnitario'},
                                                        {name: 'codigoBarras'},
                                                        {name: 'codigoAlterno'}
                                            		],
                                            root:'registros'
                                            
                                        }
                                      );
	 
                                                                                      
	var alDatos=new Ext.data.GroupingStore({
                                                            reader: lector,
                                                            proxy : new Ext.data.HttpProxy	(

                                                                                              {

                                                                                                  url: '../paginasFunciones/funcionesAlmacen.php'

                                                                                              }

                                                                                          ),
                                                            sortInfo: {field: 'nombreProducto', direction: 'ASC'},
                                                            groupField: 'nombreProducto',
                                                            remoteGroup:false,
				                                            remoteSort: false,
                                                            autoLoad:false
                                                            
                                                        }) 
	alDatos.on('beforeload',function(proxy)
                            {
                                proxy.baseParams.tipoCliente=tipoCliente;
                                proxy.baseParams.idCliente=idCliente;
                                proxy.baseParams.buscarPrecio=1;
                                
                            }
                        )   
       
        var cModelo= new Ext.grid.ColumnModel   	(
                                                        [
                                                            new  Ext.grid.RowNumberer(),
                                                            
                                                            {
                                                                header:'Producto',
                                                                width:300,
                                                                sortable:true,
                                                                dataIndex:'nombreProducto',
                                                                renderer:mostrarValorDescripcion
                                                            },
                                                            {
                                                                header:'Precio unitario',
                                                                width:80,
                                                                sortable:true,
                                                                dataIndex:'precioUnitario',
                                                                renderer:'usMoney',
                                                                css:'text-align:right;'
                                                            },
                                                            {
                                                                header:'C&oacute;digo de barras',
                                                                width:110,
                                                                sortable:true,
                                                                dataIndex:'codigoBarras',
                                                                css:'text-align:right'
                                                            },
                                                            {
                                                                header:'C&oacute;digo alterno',
                                                                width:110,
                                                                sortable:true,
                                                                dataIndex:'codigoAlterno',
                                                                css:'text-align:right'
                                                            }
                                                        ]
                                                    );
                                                    
        var tblGrid=	new Ext.grid.GridPanel	(
                                                            {
                                                                id:'gProductosBuscados',
                                                                store:alDatos,
                                                                x:10,
                                                                y:120,
                                                                width:550,
                                                                height:200,
                                                                frame:false,
                                                                cm: cModelo,
                                                                stripeRows :true,
                                                                loadMask:true,
                                                                columnLines : true, 
                                                                listeners:	{
                                                                				rowdblclick:function(grid,nFila)
                                                                                            {
                                                                                                var fila=grid.getStore().getAt(nFila);
                                                                                                
                                                                                                gEx('cmbCodigoAlterno').setRawValue(fila.data.codigoAlterno); 
                                                                                                
                                                                                               
                                                                                                  tipoBusqueda=5;
                                                                                                  gEx('txtClave').setValue(fila.data.idProducto+"_"+fila.data.llave);
                                                                                                  funcionEjecucionBusqueda=function()
                                                                                                                          {
                                                                                                                              gEx('txtClave').setValue('');
                                                                                                                          }
                                                                                                  buscarCodigoProducto();
                                                                                                  
                                                                                                   
                                                                                                   gEx('vBuscarDescripcion').close();
                                                                                            }
                                                                			} ,                                                              
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
                                                        );
                                                        
	                                                      
                                                        
        return 	tblGrid;
}

////////////////

function buscarPorCodigoBarras(tFuncion)
{
	tipoBusqueda=1;
    var posDefault=existeValorMatriz(arrFuncionBusqueda,tFuncion);
    
    gE('lblBusqueda').innerHTML=arrFuncionBusqueda[posDefault][2]+':';
    funcionEjecucionBusqueda=null;
}

function buscarPorCodigoAlterno(tFuncion)
{
	tipoBusqueda=2;
     var posDefault=existeValorMatriz(arrFuncionBusqueda,tFuncion);
    
    gE('lblBusqueda').innerHTML=arrFuncionBusqueda[posDefault][2]+':';
    funcionEjecucionBusqueda=null;
}

function buscarPorNoPedido(tFuncion)
{
	
	if((idPedidoActivo!=-1)||((idVentaActiva!=-1)))
    	return;
	tipoBusqueda=3;
     var posDefault=existeValorMatriz(arrFuncionBusqueda,tFuncion);
    gE('lblBusqueda').innerHTML=arrFuncionBusqueda[posDefault][2]+':';
    sUltimaFila=false;
    funcionEjecucionBusqueda=inicializacionPedidoLocalizado
    
}

function buscarPorNoVenta(tFuncion)
{
	tipoBusqueda=4;
    var posDefault=existeValorMatriz(arrFuncionBusqueda,tFuncion);
    gEx('grid').getStore().removeAll();
    gE('lblBusqueda').innerHTML=arrFuncionBusqueda[posDefault][2]+':';
    funcionEjecucionBusqueda=inicializacionVentaLocalizado
    
}

function inicializacionPedidoLocalizado()
{
	
	tipoVentaActiva=2;
    tipoCliente=metaData.tipoCliente;
    
    idCliente=metaData.idCliente;
    idPedidoActivo=metaData.idPedido;
    gEx('btnReimprimirTicket').show();
    reajustarConceptos(tipoCliente,metaData.nombreCliente,true);
    
    var posDefault=existeValorMatriz(arrFuncionBusqueda,'1',3);
    if(posDefault==-1)
    	posDefault=0;
    var iFuncion=arrFuncionBusqueda[posDefault][0];
    gE('lblTotalAbono').innerHTML=Ext.util.Format.usMoney(metaData.abono);
    gEx('btnGuardarPedido').show();
    gEx('btnGuardarPedido').enable();
    //gEx('btnPedido').hide();
    gEx('btnVerAbono').show();
    
    
    arrFuncionBusqueda[posDefault][4](iFuncion);  
    gEx('txtClave').setValue('');
    gEx('txtCantidad').setValue('');
    
    
    
    gEx('txtClave').focus();
    
    sUltimaFila=true;
    
    
    
}

function inicializacionVentaLocalizado()
{
	tipoVentaActiva=1;
    
    gEx('btnGuardarPedido').hide();
    //gEx('btnPedido').hide();
    gEx('btnPagar').disable();
    gEx('btnDevolucion').show();
    gEx('btnReimprimirTicket').show();
    gEx('btnABonarNotaCredito').hide();
    
    
    
    
    tipoCliente=metaData.tipoCliente;
    
    idCliente=metaData.idCliente;
    idVentaActiva=metaData.idVenta;

    if(metaData.facturada=='0')
    {
    	 gEx('btnFactura').show();
        
   	}
    else
    {
    	 gEx('btnFactura2').show();
        
    }
    reajustarConceptos(tipoCliente,metaData.nombreCliente,true);
    
    var posDefault=existeValorMatriz(arrFuncionBusqueda,'1',3);
    if(posDefault==-1)
    	posDefault=0;
    var iFuncion=arrFuncionBusqueda[posDefault][0];
    arrFuncionBusqueda[posDefault][4](iFuncion);  
    gE('lblTotalAbono').innerHTML=Ext.util.Format.usMoney(metaData.abono);
    gE('lblTotalAdeudo').innerHTML=Ext.util.Format.usMoney(metaData.saldo);
    gEx('txtClave').setValue('');
    gEx('txtClave').disable();
    gEx('txtCantidad').setValue('');
	gEx('txtCantidad').disable();
    
    gEx('grid').getSelectionModel().clearSelections();
    gEx('txtPrecioUnitario').setValue('');
    gEx('txtPrecioUnitario').disable();
    gEx('cmbUnidadMedidaVenta').setValue('');
    gEx('cmbUnidadMedidaVenta').disable();
    
    gEx('txtClave').focus();
    sUltimaFila=false;
    
    
}

function mostrarVentanaRegistrarPedido()
{
	
    var form = new Ext.form.FormPanel(	
										{
											baseCls: 'x-plain',
											layout:'absolute',
											defaultType: 'label',
											items: 	[
														{
                                                        	xtype:'label',
                                                            x:10,
                                                            y:10,
                                                            html:'<span style="font-size:18px; color:#000; font-weight:bold">Monto total:</span>'
                                                        },
                                                        {
                                                        	xtype:'label',
                                                            x:200,
                                                            y:10,
                                                            html:'<span style="font-size:18px; color:#900; font-weight:bold" id="lblCobroTotal"></span>'
                                                        },
                                                        {
                                                        	xtype:'label',
                                                            x:10,
                                                            y:50,
                                                            id:'lblAbono',
                                                            
                                                            html:'<span style="font-size:18px; color:#000; font-weight:bold">Cantidad anticipo:</span>'
                                                        },
                                                        {
                                                        	xtype:'label',
                                                            x:200,
                                                            y:45,
                                                            
                                                            id:'txtCantidadAbono',
                                                            html:'<input type="text" class="btnEvento" style="width:150px; height:26px; font-size:18px; text-align:right" id="cantidadAbono" value="$ 0.00" onkeypress="return soloNumero(event,true,false,this)" onblur="cantidadRecibidaPedidoUnfocus(this)" />'
                                                        },
                                                        {
                                                        	xtype:'label',
                                                            x:10,
                                                            y:90,
                                                            id:'lblCantidadRecibida',
                                                            html:'<span style="font-size:18px; color:#000; font-weight:bold">Cantidad Recibida:</span>'
                                                        },
                                                         {
                                                        	xtype:'label',
                                                            x:200,
                                                            y:85,
                                                            id:'txtCantidadRecibida',
                                                            html:'<input type="text" class="btnEvento" style="width:150px; height:26px; font-size:18px; text-align:right" id="cantidadRecibida" value="$ 0.00" onkeypress="return soloNumero(event,true,false,this)" onblur="cantidadRecibidaPedidoUnfocus(this)" />'
                                                        },
                                                        {
                                                        	xtype:'label',
                                                            x:10,
                                                            y:130,
                                                            id:'lblEtCambio',
                                                            html:'<span style="font-size:18px; color:#000; font-weight:bold">Cambio:</span>'
                                                        },
                                                        {
                                                        	xtype:'label',
                                                            x:200,
                                                            y:130,
                                                            id:'lblValorEtCambio',
                                                            html:'<span style="font-size:18px; color:#900; font-weight:bold" id="lblCambioCobro">$ 0.00</span>'
                                                        }

													]
										}
									);
	
	var ventanaAM = new Ext.Window(
									{
										title: 'Registro de pedido',
										width: 500,
										height:270,
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
                                                                	gE('cantidadAbono').focus();
																}
															}
												},
										buttons:	[
														{
															
															text: '<?php echo $etj["lblBtnAceptar"]?>',
                                                            
															handler: function()
																	{
                                                                    	var cantidadAbono=normalizarValor(gE('cantidadAbono').value);
                                                                        if(cantidadAbono=='')
                                                                        	cantidadAbono='0';
                                                                        cantidadAbono=parseFloat(cantidadAbono);
                                                                        
                                                                        
                                                                        var cantidadRecibida=normalizarValor(gE('cantidadRecibida').value);
                                                                        if(cantidadRecibida=='')
                                                                        	cantidadRecibida='0';
                                                                        cantidadRecibida=parseFloat(cantidadRecibida);
                                                                        
                                                                        if(cantidadAbono==0)
                                                                        {
                                                                        	function resp1()
                                                                            {
                                                                            	gE('cantidadAbono').focus();
                                                                            }
                                                                        	msgBox('La cantidad de anticipo es obligatoria',resp1);
                                                                        	return;
                                                                        }
                                                                        
                                                                        var lblCobroTotal=parseFloat(gE('lblCobroTotal').innerHTML);
                                                                        
                                                                        
                                                                        if(lblCobroTotal<cantidadAbono)
                                                                        {
                                                                        	function resp2()
                                                                            {
                                                                            	gE('cantidadAbono').focus();
                                                                            }
                                                                        	msgBox('La cantidad de anticipo es mayor que el monto total del pedido',resp2);
                                                                        	return;
                                                                        }
                                                                        
                                                                        
                                                                        if(cantidadRecibida<cantidadAbono)
                                                                        {
                                                                        	function resp3()
                                                                            {
                                                                            	gE('cantidadRecibida').focus();
                                                                            }
                                                                        	msgBox('La cantidad recibida es menor que la cantidad de anticipo',resp3);
                                                                        	return;
                                                                        }
                                                                        
                                                                        
																		function resp(btn)
                                                                        {
                                                                            if(btn=='yes')
                                                                            {
                                                                                var arrProductos='';
                                                                                var obj='';
                                                                                var cadObj='';
                                                                                var grid=gEx('grid');
                                                                                var x;
                                                                                var fila;
                                                                                var totalDescuento=0;
                                                                                
                                                                                
                                                                                var descuento=0;
                                                                                for(x=0;x<grid.getStore().getCount();x++)
                                                                                {
                                                                                    fila=grid.getStore().getAt(x);
                                                                                    descuento=(parseFloat(fila.data.descuento)*parseFloat(fila.get('cantidad')));
                                                                                    obj='{"descuentoUnitario":"'+fila.data.descuento+'","descuento":"'+descuento+'","idRegistro":"'+fila.get('idRegistro')+'","cveProducto":"'+fila.get('cveProducto')+'","costoUnitario":"'+fila.get('costoUnitario')+'","cantidad":"'+fila.get('cantidad')+
                                                                                        '","subtotal":"'+fila.get('subtotal')+'","iva":"'+fila.get('iva')+'","total":"'+fila.get('total')+'","tipoConcepto":"'+fila.get('tipoConcepto')+'","idProducto":"'+fila.get('idProducto')+
                                                                                        '","dimensiones":"'+fila.get('dimensiones')+'","tipoMovimiento":"'+fila.get('tipoMovimiento')+'","porcentajeIVA":"'+fila.get('porcentajeIVA')+'","llave":"'+fila.get('llave')+'"}';
                                                                                    if(arrProductos=='')
                                                                                        arrProductos=obj;
                                                                                    else
                                                                                        arrProductos+=','+obj;
                                                                                    totalDescuento+=descuento;      
                                                                                }
                                                                                var dCompra='';
                                                                            	var lblCambioCobro=normalizarValor(gE('lblCambioCobro').innerHTML);
                                                                                var datosPedido='{"montoAnticipo":"'+cantidadAbono+'","cantidadRecibida":"'+cantidadRecibida+'","cambio":"'+lblCambioCobro+'"}';
                                                                                
                                                                                cadObj='{"datosPedido":"'+bE(datosPedido)+'","totalDescuento":"'+totalDescuento+'","tipoCliente":"'+tipoCliente+'","idCliente":"'+idCliente+'","formaPago":"0","datosCompra":"'+cv(dCompra)+'","total":"'+normalizarValor(gE('lblTotal').innerHTML)+'","subtotal":"'+normalizarValor(gE('lblSubtotal').innerHTML)+'","iva":"'+normalizarValor(gE('lblIVA').innerHTML)+'","idCaja":"'+gE('idCaja').value+'","arrProductos":['+arrProductos+']}';
                                                                                function funcAjax()
                                                                                {
                                                                                    var resp=peticion_http.responseText;
                                                                                    arrResp=resp.split('|');
                                                                                    if(arrResp[0]=='1')
                                                                                    {
                                                                                        imprimirTicket(2,arrResp[1]);
                                                                                        limpiarCaja();
                                                                    
                                                                                        gE('lblCambioVenta').innerHTML=Ext.util.Format.usMoney(lblCambioCobro);
                                                                                        ventanaAM.close();
                                                                                        
                                                                                    }
                                                                                    else
                                                                                    {
                                                                                        msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
                                                                                    }
                                                                                }
                                                                                obtenerDatosWeb('../paginasFunciones/funcionesTesoreria.php',funcAjax, 'POST','funcion=9&tipoOperacion=2&idCaja='+gE('idCaja').value+'&cadObj='+cadObj,true);		
                                                                            }
                                                                        }    
                                                                        msgConfirm('Est&aacute; seguro de querer guardar el pedido?',resp);    
                                                                        
                                                                        
                                                                        
                                                                        
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
    gE('lblCobroTotal').innerHTML=Ext.util.Format.usMoney(normalizarValor(gE('lblTotal').innerHTML));


	
}

function cantidadRecibidaPedidoUnfocus()
{
	var cantidadAbono=normalizarValor(gE('cantidadAbono').value);
    if(cantidadAbono=='')
    	cantidadAbono='0';
    cantidadAbono=parseFloat(cantidadAbono);
    
    var cantidadRecibida=normalizarValor(gE('cantidadRecibida').value);
    if(cantidadRecibida=='')
    	cantidadRecibida='0';
    cantidadRecibida=parseFloat(cantidadRecibida);
    
    var diferencia=cantidadRecibida-cantidadAbono;
    if(diferencia<0)
    	diferencia=0;
    gE('lblCambioCobro').innerHTML=Ext.util.Format.usMoney(diferencia);
}

function buscarCliente()
{
	
	iCliente=-1;
	var aTipoCliente=new Array();
    var x;
    var fila;
    
   	for(x=0;x<arrTipoCliente.length;x++)
    {
    	fila=arrTipoCliente[x];
        if(fila[0]!='1')
	    	aTipoCliente.push([fila[0],fila[1],fila[5]]);
    }
   
    var cmbTipoCliente=crearComboExt('cmbTipoCliente',aTipoCliente,160,5,200);
    
    
    if(aTipoCliente.length==1)
    {
    	cmbTipoCliente.setValue(aTipoCliente[0][0]);
    }
    else
        if(existeValorMatriz(aTipoCliente,'3')!=-1)
            cmbTipoCliente.setValue('3');
    
   
	cmbTipoCliente.on('select',function(cmb,registro)
    							{
                                	iCliente=-1;
                                	gEx('cmbAlumno').reset();
                                    /*switch(registro.data.id)
                                    {
                                    	case '1':
                                        
                                    		gEx('lNombreEmpleado').hide();
                                            gEx('cmbAlumno').hide();
                                        break;
                                    	case '2':
                                        
                                        	gEx('lNombreEmpleado').show();
                                            gEx('cmbAlumno').show();
                                    		gE('lblNombreEmpleado').innerHTML='Nombre del Alumno:';
                                        break;
                                        case '3':
                                        	gEx('lNombreEmpleado').show();
                                            gEx('cmbAlumno').show();
                                    		gE('lblNombreEmpleado').innerHTML='Nombre del Empleado:';
                                        break;
                                    }*/
                                    
                                    gEx('lNombreEmpleado').hide();
                                    gEx('cmbAlumno').hide();
                                    
                                    var pos=existeValorMatriz(arrTipoCliente,registro.data.id,0);
                                    
                                    var fTipo=arrTipoCliente[pos];
                                    if(fTipo[6]!='')
                                    {
                                    	gEx('lNombreEmpleado').show();
                                        gEx('cmbAlumno').show();
                                        gE('lblNombreEmpleado').innerHTML=fTipo[6]+':';
                                    }
                                    
                                    
                                    gEx('cmbAlumno').focus(false,500);
                                    if(registro.data.valorComp.trim()!='')
                                    	gEx('lblTipoCliente').show();
                                    else
                                    	gEx('lblTipoCliente').hide();
                                    
                                }
    					)
	var oConf=	{
    					idCombo:'cmbAlumno',
                        anchoCombo:380,
                        campoDesplegar:'cliente',
                        campoID:'idUsuario',
                        funcionBusqueda:4,
                        raiz:'personas',
                        nRegistros:'num',
                        posX:160,
                        posY:35,
                        paginaProcesamiento:'../paginasFunciones/funcionesModulosEspeciales_Sigloxxi.php',
                        confVista:	'<tpl for="."><div class="search-item"><table><tr><td width="380">{cliente}</td><td width="50"></td></tr></table></div></tpl>',
                        campos:	[
                                    {name:'idUsuario'},
                                    {name:'cliente'}
                                   
                                ],
                       	funcAntesCarga:function(dSet,combo)
                    				{
                                    	
                                    	iCliente=-1;
                                        dSet.baseParams.tipoCliente=gEx('cmbTipoCliente').getValue();
                                    },
                      	funcElementoSel:function(combo,registro)
                    				{
                                    	iCliente=registro.get('idUsuario');
                                    }  
    				};

    
	var cmbPersonal=crearComboExtAutocompletar(oConf);

	var form = new Ext.form.FormPanel(	
										{
											baseCls: 'x-plain',
											layout:'absolute',
											defaultType: 'label',
											items: 	[
                                            			
                                            			{
                                                        	xtype:'label',
                                                            x:10,
                                                            y:10,
                                                            
                                                            html:'<span style="color:#000; font-weight:bold">Tipo de cliente:</span>'
                                                        },
                                                        cmbTipoCliente,
                                                        {
                                                        	xtype:'label',
                                                            x:10,
                                                            y:40,
                                                            id:'lNombreEmpleado',
                                                            html:'<span style="color:#000; font-weight:bold" id="lblNombreEmpleado">Nombre del Empleado:</span>'
                                                        },
                                                        cmbPersonal,
                                                        {
                                                        	x:500,
                                                            y:35,
                                                            id:'lblTipoCliente',
                                                            html:'<a href="javascript:agregarTipoCliente()"><img src="../images/pencil.png"></a>'
                                                        }
													]
										}
									);
	
	var ventanaAM = new Ext.Window(
									{
                                    	id:'vPago',
										title: 'Buscar cliente',
										width: 630,
										height:150,
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
                                                                        
                                                                         gEx('cmbAlumno').focus(false,500);
                                                                    }
                                                                }
                                                    },
										buttons:	[
														{
															text: '<?php echo $etj["lblBtnAceptar"]?>',
															handler: function()
																	{
                                                                    	var lblMsg='';
                                                                        var lblNombreCliente='';
                                                                        
                                                                        if(cmbTipoCliente.getValue()!='1')
                                                                        {
                                                                        	
                                                                            if(iCliente==-1)
                                                                            {
                                                                                /*switch(cmbTipoCliente.getValue())
                                                                                {
                                                                                    case '2':
                                                                                        lblMsg='Debe indicar el alumno que desea asociar como cliente';
                                                                                    break;
                                                                                    case '3':
                                                                                        lblMsg='Debe indicar el empleado que desea asociar como cliente';
                                                                                    break;
                                                                                }*/
                                                                                lblMsg='Debe seleccionar un cliente';
                                                                                function resp()
                                                                                {
                                                                                    gEx('cmbAlumno').focus(false);
                                                                                }
                                                                                msgBox(lblMsg,resp);
                                                                                return;
                                                                                
                                                                            }
                                                                            lblNombreCliente=gEx('cmbAlumno').getRawValue();
                                                                        }
                                                                        else
                                                                        	iCliente=-1;
                                                                        
                                                                        tipoCliente=cmbTipoCliente.getValue();
                                                                        idCliente=iCliente;
																		reajustarConceptos(tipoCliente,lblNombreCliente);
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
    dispararEventoSelectCombo('cmbTipoCliente');
}

function agregarTipoCliente()
{
	var obj={};
    var cmbTipoCliente=gEx('cmbTipoCliente');
    var pos=existeValorMatriz(arrTipoCliente,cmbTipoCliente.getValue());
    var pagina=arrTipoCliente[pos][5];
	
    obj.url= pagina;
    obj.params=[['cPagina','sFrm=true'],['eJs',bE('window.parent.setTipoClienteAgregado(@idRegistro);window.parent.cerrarVentanaFancy();return;')]]; 
    obj.ancho=960;
    obj.alto=500;
    abrirVentanaFancy(obj);
    
    
    
    
}




function realizarCancelacionVenta(tipoOperacion)
{
	var idReferencia=-1;
	
	var lblEtiqueta='';
    var mConfirm='';
    var errMsg='';
    switch(tipoOperacion)
    {
    	case 1:
        	lblEtiqueta='Cancelaci&oacute;n de venta';
            mConfirm='la venta';
            errMsg=' de la venta';
            idReferencia=idPedidoActivo;
        break;
        case 2:
        	lblEtiqueta='Cancelaci&oacute;n de pedido';
            mConfirm='el pedido';
            errMsg=' del pedido';
            idReferencia=idVentaActiva;
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
                                                            html:'Especifique el motivo de la cancelaci&oacute;n:'
                                                        },
                                                        {
                                                        	x:10,
                                                            y:40,
                                                            xtype:'textarea',
                                                            width:450,
                                                            height:80,
                                                            id:'txtMotivo'
                                                        }

													]
										}
									);
	
	var ventanaAM = new Ext.Window(
									{
										title: lblEtiqueta,
										width: 500,
										height:220,
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
                                                                	gEx('txtMotivo').focus(false,500);
																}
															}
												},
										buttons:	[
														{
															
															text: '<?php echo $etj["lblBtnAceptar"]?>',
                                                            
															handler: function()
																	{
																		function resp(btn)
                                                                        {
                                                                        	if(btn=='yes')
                                                                            {
                                                                     			var txtMotivo=gEx('txtMotivo');
                                                                                if(txtMotivo.getValue()=='')
                                                                                {
                                                                                	function resp()
                                                                                    {
                                                                                    	gEx('txtMotivo').focus();
                                                                                    }
                                                                                	msgBox('Debe indicar el motivo de la cancelaci&oacute;n '+errMsg,resp);
                                                                                    return;
                                                                                }	  
                                                                                
                                                                                
                                                                                function funcAjax()
                                                                                {
                                                                                    var resp=peticion_http.responseText;
                                                                                    arrResp=resp.split('|');
                                                                                    if(arrResp[0]=='1')
                                                                                    {
                                                                                    	limpiarCaja();
                                                                                        ventanaAM.close();
                                                                                    }
                                                                                    else
                                                                                    {
                                                                                        msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
                                                                                    }
                                                                                }
                                                                                obtenerDatosWeb('../paginasFunciones/funcionesTesoreria.php',funcAjax, 'POST','funcion=34&idCaja='+gE('idCaja').value+'&tipoOperacion='+tipoOperacion+'&idReferencia='+idReferencia+'&motivo='+cv(txtMotivo.getValue()),true);
                                                                                
                                                                                
                                                                                     	
                                                                                
                                                                            }
                                                                        }
                                                                        msgConfirm('Est&aacute; seguro de querer cancelar '+mConfirm,resp);
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

function realizarDevolucionPedido()
{
	var grid=gEx('grid');
    var fila=grid.getSelectionModel().getSelected();
    if(!fila)
    {
    	msgBox('Debe seleccionar el producto cuya devluci&oacute;n desea realizar');
        return;
    }
    
}

function mostrarVentanaDevolucion()
{
	var arrFormaDevolucion=[['1','Efectivo'],['2','Nota de cr\xE9dito']];
    var cmbMotivoDevolucion=crearComboExt('cmbMotivoDevolucion',arrMotivoDevolucion,220,5,250);
    cmbMotivoDevolucion.on('select',function(cmb,registro)
    								{
                                    
                                    	
                                    	var x;
                                        var fila;
                                        if(registro.data.id=='4')
                                        {
                                            for(x=0;x<gEx('gridDevolcion').getStore().getCount();x++)
                                            {
                                                fila=gEx('gridDevolcion').getStore().getAt(x);
                                                if(parseFloat(fila.data.cantidad)>0)
                                                {
                                                    fila.set('numDevolucion',fila.get('cantidad'));
                                                    //fila.set('checado','1');
                                                    gE('chk_'+fila.data.idRegistro).checked=true;
                                                    nodoSelDevoClick(gE('chk_'+fila.data.idRegistro));
                                                    
                                                }
                                            }
                                            gEx('gridDevolcion').getView().refresh();
										}
                                        else
                                        {
                                        	gEx('gridDevolcion').getView().refresh();
                                        }                                        
                                        
                                        
                                        
                                    }
    					)
    var cmbFormaDevolucion=crearComboExt('cmbFormaDevolucion',arrFormaDevolucion,220,35,250);
    cmbFormaDevolucion.setValue('1');
    cmbFormaDevolucion.disable();
	var gDevolucion=crearGridCajaDevolucion();
    var posYTotal=70;
    var altoDesgloceTotal=240;
    /*if(!mostrarDesgloceCosto)
    {
    	posYTotal=10;
        altoDesgloceTotal=90;
    }*/
	var form = new Ext.form.FormPanel(	
										{
											baseCls: 'x-plain',
											layout:'absolute',
											defaultType: 'label',
											items: 	[
														gDevolucion,
                                                        {
                                                        	x:20,
                                                            y:300,
                                                            hidden:true,
                                                            id:'lblVentaFacturada',
                                                            html:'<img src="../images/exclamation.png"> <b><span style="color:#900; font-size:13px">La venta ya ha sido facturada, s&oacute;lo se permite la cancelaci&oacute;n total de la venta</span></b>'
                                                        },
                                                        {
                                                            xtype:'fieldset',
                                                            width:420,
                                                            height:altoDesgloceTotal,
                                                            x:560,
                                                            y:330,
                                                            
                                                            layout:'absolute',
                                                            items:	[
                                                                         {
                                                                            x:10,
                                                                            y:10,
                                                                            xtype:'label',
                                                                            
                                                                            html:'<span style="color:#003; font-size:16px"> <b>Subtotal: </b></span>'
                                                                        },
                                                                        {
                                                                            x:130,
                                                                            y:10,
                                                                            xtype:'label',
                                                                            hidden:!mostrarDesgloceCosto,
                                                                            html:'<span style="color:#900; font-size:16px"><b><span id="lblSubtotal2">'+Ext.util.Format.usMoney(0)+'</span></b></span>'
                                                                        },
                                                                        {
                                                                            x:250,
                                                                            y:10,
                                                                            xtype:'label',
                                                                            hidden:!mostrarDesgloceCosto,
                                                                            html:'<span style="color:#003; font-size:16px"> <b>Total Abonos: </b></span>'
                                                                        },
                                                                        {
                                                                            x:250,
                                                                            y:40,
                                                                            xtype:'label',
                                                                            hidden:!mostrarDesgloceCosto,
                                                                            html:'<span style="color:#900; font-size:16px"><b><span id="lblTotalAbono2">'+Ext.util.Format.usMoney(0)+'</span></b></span>'
                                                                        },
                                                                        {
                                                                            x:250,
                                                                            y:70,
                                                                            xtype:'label',
                                                                            hidden:!mostrarDesgloceCosto,
                                                                            html:'<span style="color:#003; font-size:16px"> <b>Total Adeudo: </b></span>'
                                                                        },
                                                                        {
                                                                            x:250,
                                                                            y:100,
                                                                            xtype:'label',
                                                                            hidden:!mostrarDesgloceCosto,
                                                                            html:'<span style="color:#900; font-size:16px"><b><span id="lblTotalAdeudo2">'+Ext.util.Format.usMoney(0)+'</span></b></span>'
                                                                        },
                                                                        {
                                                                            x:10,
                                                                            y:40,
                                                                            xtype:'label',
                                                                            hidden:!mostrarDesgloceCosto,
                                                                            html:'<span style="color:#003; font-size:16px"> <b>IVA: </b></span>'
                                                                        },
                                                                        {
                                                                            x:130,
                                                                            y:40,
                                                                            xtype:'label',
                                                                            hidden:!mostrarDesgloceCosto,
                                                                            html:'<span style="color:#900; font-size:16px"> <b><span id="lblIVA2">'+Ext.util.Format.usMoney(0)+'</span></b></span>'
                                                                        },
                                                                        {
                                                                            x:10,
                                                                            y:posYTotal,
                                                                            xtype:'label',
                                                                            html:'<span style="color:#003; font-size:16px"> <b>Total Venta: </b></span>'
                                                                        },
                                                                        {
                                                                            x:130,
                                                                            y:posYTotal,
                                                                            xtype:'label',
                                                                            html:'<span style="color:#900; font-size:16px"> <b><span id="lblTotal2">'+Ext.util.Format.usMoney(0)+'</span></b></span>'
                                                                        },
                                                                        
                                                                        {
                                                                            x:10,
                                                                            y:(posYTotal+60),
                                                                            xtype:'label',
                                                                            html:'<span style="color:#003; font-size:16px"> <b>Monto devoluci&oacute;n: </b></span>'
                                                                        },
                                                                        {
                                                                            x:160,
                                                                            y:(posYTotal+60),
                                                                            xtype:'label',
                                                                            html:'<span style="color:#900; font-size:16px"> <b><span id="lblMontoDevolucion">'+Ext.util.Format.usMoney(0)+'</span></b></span>'
                                                                        },
                                                                        {
                                                                            x:10,
                                                                            y:(posYTotal+90),
                                                                            xtype:'label',
                                                                            
                                                                            html:'<span style="color:#003; font-size:16px"> <b>Diferencia adeudo: </b></span>'
                                                                        },
                                                                        {
                                                                            x:160,
                                                                            y:(posYTotal+90),
                                                                            xtype:'label',
                                                                            html:'<span style="color:#900; font-size:16px"> <b><span id="lblDiferenciaAdeudo">'+Ext.util.Format.usMoney(0)+'</span></b></span>'
                                                                        },		
                                                                        {
                                                                            x:10,
                                                                            y:(posYTotal+120),
                                                                            xtype:'label',
                                                                            
                                                                            html:'<span style="color:#003; font-size:16px"> <b>Monto reintegro: </b></span>'
                                                                        },
                                                                        {
                                                                            x:160,
                                                                            y:(posYTotal+120),
                                                                            xtype:'label',
                                                                            html:'<span style="color:#900; font-size:16px"> <b><span id="lblMontoDiferencia">'+Ext.util.Format.usMoney(0)+'</span></b></span>'
                                                                        }			
                                                                    ]
                                                        
                                                        },
                                                        {
                                                        	xtype:'fieldset',
                                                            width:510,
                                                            height:240,
                                                            x:10,
                                                            y:330,
                                                            
                                                            layout:'absolute',
                                                            items:[
                                                            			{
                                                                        	xtype:'label',
                                                                            x:10,
                                                                            y:10,
                                                                            html:'<b>Motivo de la devoluci&oacute;n:</b>'
                                                                         },
                                                                         cmbMotivoDevolucion,
                                                                         {
                                                                        	xtype:'label',
                                                                            x:10,
                                                                            y:40,
                                                                            html:'<b>Forma de devoluci&oacute;n de reintegro:</b>'
                                                                         },
                                                                         cmbFormaDevolucion,
                                                                         {
                                                                        	xtype:'label',
                                                                            x:10,
                                                                            y:70,
                                                                            html:'<b>Comentarios adicionales:</b>'
                                                                         },
                                                                         {
                                                                         	x:10,
                                                                            y:100,
                                                                            width:440,
                                                                            height:100,
                                                                            xtype:'textarea',
                                                                            id:'txtComentarios'
                                                                         }
                                                                         
                                                            		]
                                                             
                                                        }

													]
										}
									);
	
	var ventanaAM = new Ext.Window(
									{
                                    	id:'vDevolucion',
										title: 'Devoluci&oacute;n de producto',
										width: 500,
										height:450,
										layout: 'fit',
										plain:true,
										modal:true,
                                        maximized:true,
										bodyStyle:'padding:5px;',
										buttonAlign:'center',
										items: form,
										listeners : {
													show : {
																buffer : 10,
																fn : function() 
																{
                                                                	if(metaData.facturada=='1')
                                                                    {
                                                                        gEx('lblVentaFacturada').show();
                                                                        cmbMotivoDevolucion.setValue('4');
                                                                        cmbMotivoDevolucion.disable();
                                                                        dispararEventoSelectCombo('cmbMotivoDevolucion');
                                                                        
                                                                    }
                                                                
																}
															}
												},
										buttons:	[
														{
															text: '<?php echo $etj["lblBtnAceptar"]?>',
															handler: function()
																	{
																		if(cmbMotivoDevolucion.getValue()=='')
                                                                        {
                                                                        	function resp()
                                                                            {
                                                                            	cmbMotivoDevolucion.focus();
                                                                            }
                                                                            msgBox('Debe especificar el motivo de la devoluci&oacute;n',resp);
                                                                            return;
                                                                        }
                                                                        
                                                                        
                                                                        
                                                                        
                                                                        
                                                                        var montoDevolucion=normalizarValor(gE('lblMontoDevolucion').innerHTML);
                                                                        var montoReintegro=normalizarValor(gE('lblMontoDiferencia').innerHTML);
                                                                        
                                                                        var arrProductos='';
                                                                        var x=0;
                                                                        var gridDevolcion=gEx('gridDevolcion');
                                                                        var fila;
                                                                        var chk;
                                                                        var o;
                                                                        for(x=0;x<gridDevolcion.getStore().getCount();x++)
                                                                        {
                                                                        	fila=gridDevolcion.getStore().getAt(x);
                                                                        	chk=gE('chk_'+fila.data.idRegistro);
                                                                            if((chk)&&(chk.checked)&&(fila.data.numDevolucion!='0'))
                                                                            {
                                                                            	o='{"idRegistroProducto":"'+fila.data.idRegistro+'","cantidad":"'+fila.data.numDevolucion+'"}';
                                                                            	if(arrProductos=='')
                                                                                	arrProductos=o;
                                                                                else
                                                                                	arrProductos+=','+o;
                                                                            }
                                                                        }
                                                                        
                                                                        
                                                                        var cadObj='{"idCaja":"'+gE('idCaja').value+'","idVenta":"'+idVentaActiva+'","montoReintegro":"'+montoReintegro+'","montoDevolucion":"'+montoDevolucion+
                                                                        			'","formaDevolucion":"'+cmbFormaDevolucion.getValue()+'","comentariosAdicionales":"'+cv(gEx('txtComentarios').getValue())+
                                                                                    '","idMotivoDevolucion":"'+cmbMotivoDevolucion.getValue()+'","arrProductos":['+arrProductos+']}';
                                                                        function resp(btn)
                                                                        {
                                                                        	if(btn=='yes')
                                                                            {
                                                                            	mostrarVentanaAutorizacionCancelacion(cadObj);
                                                                                	
                                                                            }
                                                                        }
                                                                        msgConfirm('Est&aacute; seguro de querer registrar la devoluci&oacute;n de los productos indicados?',resp);
                                                                        
                                                                        
                                                                        
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

	var regDevolucion=crearRegistro([
                                      {name: 'idRegistro'},
                                      {name: 'cveProducto'},
                                      {name: 'concepto'},
                                      {name: 'costoUnitario', type:'float'},
                                      {name: 'cantidad'},
                                      {name: 'subtotal', type:'float'},                                                                
                                      {name: 'iva', type:'float'},
                                      {name: 'total', type:'float'},
                                      {name: 'imagen'},
                                      {name: 'tipoConcepto'},
                                      {name: 'idProducto'},
                                      {name: 'detalle'},
                                      {name: 'dimensiones'},
                                      {name: 'tipoMovimiento'},
                                      {name: '_parent'},
                                      {name: 'descripcion'},
                                      {name: '_is_leaf', type: 'bool'},
                                      {name: 'llave'},
                                      {name: 'numDevueltos'},
                                      {name: 'numDevolucion'},
                                      {name: 'checado'}

                                  ]);
                                  

	var gridDevolcion=gEx('gridDevolcion');
    var grid=gEx('grid');
    var x;
    var filaO;
    var filaC;
    var o;

	var subTotal=0;
    var iva=0;
    var total=0;
    

    for(x=0;x<grid.getStore().getCount();x++)
    {
    	filaO=grid.getStore().getAt(x);
        o={"maxDisponibles":"0","checado":"0"};
        
        Ext.apply(o,filaO.data);
        
        filaC=new regDevolucion(o);
        
        filaC.set('numDevolucion',0);
        
        
        filaC.set('cantidad',parseFloat(filaC.data.cantidad)-parseFloat(filaC.data.numDevueltos));
        gridDevolcion.getStore().add(filaC);
        
        subTotal+=parseFloat(filaC.data.subtotal);
        iva+=parseFloat(filaC.data.iva);
        total+=parseFloat(filaC.data.total);
        
    }
	                             
	ventanaAM.show();	
    gE('lblSubtotal2').innerHTML=Ext.util.Format.usMoney(subTotal);                                
    gE('lblIVA2').innerHTML=Ext.util.Format.usMoney(iva);                                
    gE('lblTotal2').innerHTML=Ext.util.Format.usMoney(total); 
    gE('lblTotalAbono2').innerHTML=gE('lblTotalAbono').innerHTML;  
    gE('lblTotalAdeudo2').innerHTML=gE('lblTotalAdeudo').innerHTML;  
	
}

function mostrarVentanaAutorizacionCancelacion(cadObj)
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
                                                            html:'Por favor ingrese su clave de autorizaci&oacute;n:'
                                                        },
                                                        {
                                                        	x:10,
                                                            y:40,
                                                            id:'cveAutorizacion',
                                                            xtype:'textfield',
                                                            inputType:'password',
                                                            width:300
                                                        }

													]
										}
									);
	
	var ventanaAM = new Ext.Window(
									{
										title: 'En espera de autorizaci&oacute;n...',
										width: 340,
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
                                                                	gEx('cveAutorizacion').focus(false,500);
																}
															}
												},
										buttons:	[
														{
															
															text: '<?php echo $etj["lblBtnAceptar"]?>',
															handler: function()
																	{
                                                                    	if(gEx('cveAutorizacion').getValue()=='')
                                                                        {
                                                                        	function resp1()
                                                                            {
                                                                            	gEx('cveAutorizacion').focus();
                                                                                
                                                                            }
                                                                            msgBox('Debe ingresar su clave de autorizaci&oacute;n',resp1);
                                                                            return;
                                                                        }
                                                                    
																		function funcAjax()
                                                                        {
                                                                            var resp=peticion_http.responseText;
                                                                            arrResp=resp.split('|');
                                                                            if(arrResp[0]=='1')
                                                                            {
                                                                                switch(arrResp[1])
                                                                                {
                                                                                	case '1':
                                                                                    	function funcAjax()
                                                                                        {
                                                                                            var resp=peticion_http.responseText;
                                                                                            arrResp=resp.split('|');
                                                                                            if(arrResp[0]=='1')
                                                                                            {
                                                                                                function respA()
                                                                                                {
                                                                                                    imprimirTicket(4,arrResp[1]);
                                                                                                    limpiarCaja();
                                                                                                    gEx('vDevolucion').close();
                                                                                                    ventanaAM.close();
                                                                                                }
                                                                                                msgBox('La operaci&oacute;n ha sido llevada a cabo salisfactoriamente',respA)
                                                                                            }
                                                                                            else
                                                                                            {
                                                                                                msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
                                                                                            }
                                                                                        }
                                                                                        obtenerDatosWeb('../paginasFunciones/funcionesTesoreria.php',funcAjax, 'POST','funcion=36&cadObj='+cadObj,true);
                                                                                    break;
                                                                                    case '0':
                                                                                    	function resp2()
                                                                                        {
                                                                                        	gEx('cveAutorizacion').focus();
                                                                                        }
                                                                                        msgBox('La clave de autorizaci&oacuten ingresada NO es v&aacute;lida',resp2);
                                                                                    
                                                                                    break;
                                                                                }
                                                                            }
                                                                            else
                                                                            {
                                                                                msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
                                                                            }
                                                                        }
                                                                        obtenerDatosWeb('../paginasFunciones/funcionesTesoreria.php',funcAjax, 'POST','funcion=58&cve='+bE(gEx('cveAutorizacion').getValue()),true);
                                                                        
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

function crearGridCajaDevolucion()
{
    var alDatos = new Ext.ux.maximgb.tg.AdjacencyListStore(	
      														{
                                                                autoLoad : false,
                                                                url: '../paginasFunciones/funcionesAlmacen.php',
                                                                reader: new Ext.data.JsonReader(
                                                                                                    {
                                                                                                        id: 'idRegistro',
                                                                                                        root: 'registros',
                                                                                                        totalProperty: 'numReg',
                                                                                                        fields:	[
                                                                                                        			{name: 'idRegistro'},
                                                                                                                    {name: 'cveProducto'},
                                                                                                                    {name: 'concepto'},
                                                                                                                    {name: 'costoUnitario', type:'float'},
                                                                                                                    {name: 'cantidad'},
                                                                                                                    {name: 'subtotal', type:'float'},                                                                
                                                                                                                    {name: 'iva', type:'float'},
                                                                                                                    {name: 'total', type:'float'},
                                                                                                                    {name: 'imagen'},
                                                                                                                    {name: 'tipoConcepto'},
                                                                                                                    {name: 'idProducto'},
                                                                                                                    {name: 'detalle'},
                                                                                                                    {name: 'dimensiones'},
                                                                                                                    {name: 'tipoMovimiento'},
                                                                                                                    {name: '_parent'},
                                                                                                                    {name: 'descripcion'},
                                                                                                                    {name: '_is_leaf', type: 'bool'},
                                                                                                                    {name: 'llave'},
                                                                                                                    {name: 'numDevolucion'},
                                                                                                                    {name: 'checado'}
                                                                                                                ]
                                                                                                    }
                                                                                                    
                                                                                                )
                                                         	}
                                                          ); 
                
	
    alDatos.on('beforeload',function(proxy)
    						{
                            	/*proxy.baseParams.idAlmacen=gE('idAlmacen').value;
                                proxy.baseParams.funcion=158;*/
                            }
    		)
    				
                    
	alDatos.on('load',function(almacen)
    				{
                    	//gEx('arbolCategorias').getStore().expandAll();
                    }    
            )            
            
    var expander = new Ext.ux.grid.RowExpander({
                                                column:0,
                                                expandOnEnter:false,
                                                tpl : new Ext.Template(
                                                    '<table >',
                                                    '<tr><td style="padding:5px; color:#666; font-style:italic">{descripcion}</td></tr>',
                                                    '</table>'
                                                )
                                            });
                                            
	var cModelo= new Ext.grid.ColumnModel   	(
												 	[
                                                    	expander,
														{
															header:'Clave',
															width:120,
															sortable:true,
                                                            hidden:true,
															dataIndex:'cveProducto'
														},
                                                        {
															header:'Concepto',
															width:330,
															sortable:true,
															dataIndex:'concepto',
                                                            renderer:function(val,meta,record)
                                                            		{
                                                                    	
                                                                    	var id='chk_'+record.data.idRegistro;
                                                                        var checado='';
                                                                        var deshabilitado='';
                                                                        if(gEx('cmbMotivoDevolucion').getValue()=='4')
                                                                    		deshabilitado='disabled="disabled"';
                                                                        if(record.data.checado=='1')
                                                                        	checado='checked=checked';
                                                                        if(parseFloat(record.data.cantidad)>0)
                                                                        {
                                                                            return [
                                                                                       '<img src="', Ext.BLANK_IMAGE_URL, '" class="ux-maximgb-tg-mastercol-icon" />',
                                                                                       
                                                                                       
                                                                                       
                                                                                       '<input '+deshabilitado+' '+checado+' type="checkbox" class="ux-maximgb-tg-mastercol-cb" ext:record-id="', record.idRegistro, '" id="chk_'+record.data.idRegistro+
                                                                                       '"  name="chkSel" onclick="nodoSelDevoClick(this)" />&nbsp;',
                                                                                       '<span class="ux-maximgb-tg-mastercol-editorplace">', mostrarValorDescripcion(val), '</span>'
                                                                                    ].join('');
                                                                     	}
                                                                        else
                                                                        {
                                                                        	return [
                                                                                       '<img src="', Ext.BLANK_IMAGE_URL, '" class="ux-maximgb-tg-mastercol-icon" />',
                                                                                       '<span class="ux-maximgb-tg-mastercol-editorplace">', mostrarValorDescripcion(val), '</span>'
                                                                                    ].join('');
                                                                        }
                                                                     }
														},
                                                        
														{
															header:'Precio unitario',
															width:100,
                                                            css:'text-align:right;',
															sortable:true,
															dataIndex:'costoUnitario',
                                                            renderer:'usMoney'
														},
                                                        
                                                        {
															header:'Cantidad venta',
															width:90,
                                                            css:'text-align:right;',
															sortable:true,
															dataIndex:'cantidad'
														},
                                                        {
															header:'Cantidad a devolver',
															width:110,
                                                            css:'text-align:right;',
															sortable:true,
                                                            editor:	{xtype:'numberfield',allowDecimals:false,allowNegative:false},
															dataIndex:'numDevolucion'
														},
                                                        {
															header:'SubTotal',
															width:100,
                                                            css:'text-align:right;',
															sortable:true,
                                                            hidden:!mostrarDesgloceCosto,
															dataIndex:'subtotal',
                                                            renderer:'usMoney'
														},
                                                        {
															header:'IVA',
															width:100,
                                                            css:'text-align:right;',
															sortable:true,
															dataIndex:'iva',
                                                            hidden:!mostrarDesgloceCosto,
                                                            renderer:'usMoney'
														},
                                                        {
															header:'Total',
															width:100,
                                                             css:'text-align:right;',
															sortable:true,
															dataIndex:'total',
                                                            renderer:'usMoney'
														}
													]
												);
                                                
	
	var tblGrid=	new Ext.ux.maximgb.tg.EditorGridPanel	(
                                                                {
                                                                    id:'gridDevolcion',
                                                                    title:'Productos a devolver',
                                                                    x:5,
                                                                    y:10,
                                                                    store:alDatos,
                                                                    frame:false,
                                                                    border:true,
                                                                    height:280,
                                                                    cm: cModelo,
                                                                    clickToEdit:1,
                                                                    loadMask:true,
                                                                    stripeRows :true,
                                                                    plugins:[expander],
                                                                    columnLines : true
                                                                }
                                                            );
	tblGrid.nuevoRegistro=false;   
    tblGrid.on('beforeedit',function(e)
    						{
                            	
                            	if((!gE('chk_'+e.record.data.idRegistro).checked)||(gEx('cmbMotivoDevolucion').getValue()=='4'))
                                {
                                	e.cancel=true;
                                    return;
                                }
                            }
    	
    			)    
	tblGrid.on('afteredit',function(e)
    						{
                            	if(parseFloat(e.record.data.cantidad)<parseFloat(e.value))
                                {
                                	function respBtn()
                                    {
                                    	e.record.set('numDevolucion',e.originalValue);
                                    }
                                    msgBox('El n&uacute;mero de productos a devolver (<b>'+e.value+'</b>) es mayor que el n&uacute;mero de productos disponibles en la venta (<b>'+e.record.data.cantidad+'</b>)',respBtn);
                                    
                                }
                                calcularTotalDevolucion();
                            }
    	
    			)                                                                                                                                
	return 	tblGrid;		
}


function calcularTotalDevolucion()
{
	var gridDevolcion=gEx('gridDevolcion');
    var fila='';
    var totalDevolucion=0;
    var subtotal;
    var iva;
    var total;
    
	for(x=0;x<gridDevolcion.getStore().getCount();x++)
    {
    	fila=gridDevolcion.getStore().getAt(x);
        if((gE('chk_'+fila.data.idRegistro))&&(gE('chk_'+fila.data.idRegistro).checked))
        {
        	
            subtotal=parseFloat(fila.data.numDevolucion)*(parseFloat(fila.data.subtotal)/fila.data.cantidad);
            iva=parseFloat(fila.data.numDevolucion)*(parseFloat(fila.data.iva)/parseFloat(fila.data.cantidad))
            totalDevolucion+=(subtotal+iva);
        }
        
        
    }
    gE('lblMontoDevolucion').innerHTML=Ext.util.Format.usMoney(totalDevolucion); 
    
    var totalVenta=parseFloat(normalizarValor(gE('lblTotalAdeudo2').innerHTML));                               
    gE('lblMontoDevolucion').innerHTML=Ext.util.Format.usMoney(totalDevolucion);  
    var diferencia=totalVenta-totalDevolucion;
    gE('lblDiferenciaAdeudo').innerHTML=Ext.util.Format.usMoney(diferencia);                              
    if(diferencia<0)
    {
    	diferencia*=-1;
       // gEx('cmbFormaDevolucion').enable();
    }
    else
    {
    	diferencia=0;
        gEx('cmbFormaDevolucion').disable();
    }
    
    gE('lblMontoDiferencia').innerHTML=Ext.util.Format.usMoney(diferencia);                              
}

function nodoSelDevoClick(chk)
{
	if(chk.checked)
    {
    	arrCheck.push(chk.id);
        var arrId=chk.id.split('_');
        var gridDevolcion=gEx('gridDevolcion');
		var x;
        for(x=0;x<gridDevolcion.getStore().getCount();x++)
        {
        	fila=gridDevolcion.getStore().getAt(x);
        	if(fila.data.idRegistro==arrId[1])
            {
	          	 fila.set('numDevolucion',fila.get('cantidad'));
                 fila.set('checado','1');
           		 break;
            }
        }
       
        calcularTotalDevolucion();
    }
    else
    {
 		var pos=existeValorArreglo(arrCheck,chk.id);   	
        arrCheck.splice(pos,1);
        var arrId=chk.id.split('_');

        var gridDevolcion=gEx('gridDevolcion');
		var x;
        for(x=0;x<gridDevolcion.getStore().getCount();x++)
        {
        	fila=gridDevolcion.getStore().getAt(x);
        	if(fila.data.idRegistro==arrId[1])
            {
	          	 fila.set('numDevolucion',0);
                 fila.set('checado','0');
            	break;
            }
        }
        calcularTotalDevolucion();
    }
}

function mostrarVentanaAbonoNotaCredito()
{
	var aTipoCliente=new Array();
    var x;
    var fila;
    for(x=0;x<arrTipoCliente.length;x++)
    {
    	fila=arrTipoCliente[x];
        if(fila[0]!='1')
	    	aTipoCliente.push([fila[0],fila[1]]);
    }
	var cmbTipoCliente=crearComboExt('cmbTipoCliente',aTipoCliente,160,35,200);
	cmbTipoCliente.setValue('3');
    dispararEventoSelectCombo('cmbTipoCliente');
	cmbTipoCliente.on('select',function(cmb,registro)
    							{
                                	iCliente=-1;
                                	gEx('cmbAlumno').reset();
                                    switch(registro.data.id)
                                    {
                                    	case '1':
                                        
                                    		gEx('lNombreEmpleado').hide();
                                            gEx('cmbAlumno').hide();
                                        break;
                                    	case '2':
                                        
                                        	gEx('lNombreEmpleado').show();
                                            gEx('cmbAlumno').show();
                                    		gE('lblNombreEmpleado').innerHTML='Nombre del Alumno:';
                                        break;
                                        case '3':
                                        	gEx('lNombreEmpleado').show();
                                            gEx('cmbAlumno').show();
                                    		gE('lblNombreEmpleado').innerHTML='Nombre del Empleado:';
                                        break;
                                    }
                                    gEx('cmbAlumno').focus(false,500);
                                }
    					)
	var oConf=	{
    					idCombo:'cmbAlumno',
                        anchoCombo:330,
                        campoDesplegar:'cliente',
                        campoID:'idUsuario',
                        funcionBusqueda:2,
                        raiz:'personas',
                        nRegistros:'num',
                        posX:160,
                        posY:65,
                        paginaProcesamiento:'../paginasFunciones/funcionesModulosEspeciales_Sigloxxi.php',
                        confVista:	'<tpl for="."><div class="search-item"><table><tr><td width="380">{cliente}</td><td width="50"></td></tr></table></div></tpl>',
                        campos:	[
                                    {name:'idUsuario'},
                                    {name:'cliente'}
                                   
                                ],
                       	funcAntesCarga:function(dSet,combo)
                    				{
                                    	iCliente=-1;
                                        tipoBusqueda=2;
                                        gEx('gridNotas').getStore().removeAll();
                                        dSet.baseParams.tipoCliente=gEx('cmbTipoCliente').getValue();
                                    },
                      	funcElementoSel:function(combo,registro)
                    				{
                                    	combo.iCliente=registro.get('idUsuario');
                                        cargarNotasCredito();
                                        
                                    }  
    				};

    
	var cmbPersonal=crearComboExtAutocompletar(oConf);
    
    var gridNotasCredito=generarGridNotasCredito();
    
	var form = new Ext.form.FormPanel(	
										{
											baseCls: 'x-plain',
											layout:'absolute',
											defaultType: 'label',
											items: 	[
														{
                                                        	x:10,
                                                            y:10,
                                                            html:'<b>Folio Nota de Cr&eacute;dito:</b>'
                                                        },
                                                        {
                                                        	x:160,
                                                            y:5,
                                                            id:'txtFolio',
                                                            xtype:'textfield',
                                                            width:150,
                                                            enableKeyEvents:true,
                                                            listeners:	{
                                                            				
                                                                            keyup:function()
                                                                            		{
                                                                                    	tipoBusqueda=1;
                                                                                        gEx('gridNotas').getStore().removeAll();
                                                                                    	cargarNotasCredito()
                                                                                    }
                                                                            
                                                            			}
                                                            
                                                        },
                                                        {
                                                        	xtype:'label',
                                                            x:10,
                                                            y:40,
                                                            
                                                            html:'<span style="color:#000; font-weight:bold">Tipo de cliente:</span>'
                                                        },
                                                        cmbTipoCliente,
                                                        {
                                                        	xtype:'label',
                                                            x:10,
                                                            y:70,
                                                            id:'lNombreEmpleado',
                                                            html:'<span style="color:#000; font-weight:bold" id="lblNombreEmpleado">Nombre del Empleado:</span>'
                                                        },
                                                        cmbPersonal,
                                                        gridNotasCredito
                                                    ]
										}
									);
	
	var ventanaAM = new Ext.Window(
									{
										title: 'Buscar Nota de Cr&eacute;dito',
										width: 720,
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
                                                                	gEx('txtFolio').focus(false,500);
																}
															}
												},
										buttons:	[
														{
															
															text: '<?php echo $etj["lblBtnAceptar"]?>',
                                                            
															handler: function()
																	{
																		var fila=gridNotasCredito.getSelectionModel().getSelected();
                                                                        if(!fila)
                                                                        {
                                                                        	msgBox('Debe selecionar la Nota de Cr&eacute;dito que desea abonar');
                                                                        	return;
                                                                        }
                                                                        
                                                                        notaCredito.push(fila);
                                                                        calcularTotal();
                                                                        ventanaAM.close();
                                                                        gEx('btnGuardarPedido').hide();
                                                                        gEx('txtClave').focus();
																	}
														},
														{
															text: '<?php echo $etj["lblBtnCancelar"]?>',
															handler:function()
																	{
																		ventanaAM.close();
                                                                        gEx('txtClave').focus();
																	}
														}
													]
									}
								);
	ventanaAM.show();	
}


function generarGridNotasCredito()
{
	 var lector= new Ext.data.JsonReader({
                                            
                                            totalProperty:'numReg',
                                            fields: [
                                               			{name:'idNotaCredito'},
		                                                {name: 'fechaCreacion', type:'date', dateFormat:'Y-m-d H:i:s'},
		                                                {name:'folioNota'},
                                                        {name: 'montoNota'},
                                                        {name: 'cliente'}
                                            		],
                                            root:'registros'
                                            
                                        }
                                      );
	 
                                                                                      
	var alDatos=new Ext.data.GroupingStore({
                                                            reader: lector,
                                                            proxy : new Ext.data.HttpProxy	(

                                                                                              {

                                                                                                  url: '../paginasFunciones/funcionesTesoreria.php'

                                                                                              }

                                                                                          ),
                                                            sortInfo: {field: 'fechaCreacion', direction: 'ASC'},
                                                            groupField: 'fechaCreacion',
                                                            remoteGroup:false,
				                                            remoteSort: false,
                                                            autoLoad:false
                                                            
                                                        }) 
	alDatos.on('beforeload',function(proxy)
    								{
                                    	
                                        
                                    }
                        )   
       
        var cModelo= new Ext.grid.ColumnModel   	(
                                                        [
                                                            new  Ext.grid.RowNumberer(),
                                                            
                                                            {
                                                                header:'Fecha de realizaci&oacute;n',
                                                                width:120,
                                                                sortable:true,
                                                                dataIndex:'fechaCreacion',
                                                                renderer:function(val)
                                                                		{
                                                                        	if(val)
                                                                            	return val.format('d/m/Y')
                                                                        }
                                                            },
                                                            {
                                                                header:'Folio',
                                                                width:110,
                                                                sortable:true,
                                                                dataIndex:'folioNota'
                                                            },
                                                            {
                                                                header:'Monto Nota',
                                                                width:120,
                                                                sortable:true,
                                                                css:'text-align:right;',
                                                                dataIndex:'montoNota',
                                                                renderer:'usMoney'
                                                            },
                                                            {
                                                                header:'Cliente',
                                                                width:220,
                                                                sortable:true,
                                                                dataIndex:'cliente'
                                                            }
                                                        ]
                                                    );
                                                    
        var tblGrid=	new Ext.grid.GridPanel	(
                                                            {
                                                                id:'gridNotas',
                                                                store:alDatos,
                                                                x:10,
                                                                y:100,
                                                                height:230,
                                                                width:660,
                                                                frame:false,
                                                                border:true,
                                                                cm: cModelo,
                                                                stripeRows :true,
                                                                loadMask:true,
                                                                columnLines : true,
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
                                                        );
        return 	tblGrid;

}

function cargarNotasCredito()
{
	var cBusqueda='';
    switch(tipoBusqueda)
    {
    	case 1:
        	cBusqueda=gEx('txtFolio').getValue();
        break;
        case 2:
        	cBusqueda=gEx('cmbAlumno').idCliente;
        break;
        
    }
	gEx('gridNotas').getStore().load	(
    										{
                                            	url: '../paginasFunciones/funcionesTesoreria.php',
                                                params:	{
                                                			funcion:35,
                                                            tBusqueda:tipoBusqueda,
                                                            criterio:cBusqueda,
                                                            tipoCliente:gEx('cmbTipoCliente').getValue()
                                                		}
                                            }
    									)	
}

function mostrarAbonosAdeudo()
{
	var gridAbonosHistorial=crearGridAbonosHistorial();
	var form = new Ext.form.FormPanel(	
										{
											baseCls: 'x-plain',
											layout:'absolute',
											defaultType: 'label',
											items: 	[
														gridAbonosHistorial

													]
										}
									);
	
    
	var ventanaAM = new Ext.Window(
									{
										title: 'Historial de abonos',
										width: 700,
										height:350,
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
																		ventanaAM.close();
																	}
														}
													]
									}
								);
	ventanaAM.show();	
}

function crearGridAbonosHistorial()
{

	 var lector= new Ext.data.JsonReader({
                                            
                                            totalProperty:'numReg',
                                            fields: [
                                               			{name:'idAbono'},
		                                                {name: 'fechaAbono',type:'date',dateFormat:'Y-m-d'},
		                                                {name:'montoAbono'},
                                                        {name: 'formaPago'},
                                                        {name: 'idComprobante'}
                                                        
                                            		],
                                            root:'registros'
                                            
                                        }
                                      );
	 
                                                                                      
	var alDatos=new Ext.data.GroupingStore({
                                                            reader: lector,
                                                            proxy : new Ext.data.HttpProxy	(

                                                                                              {

                                                                                                  url: '../paginasFunciones/funcionesTesoreria.php'

                                                                                              }

                                                                                          ),
                                                            sortInfo: {field: 'fechaAbono', direction: 'ASC'},
                                                            groupField: 'fechaAbono',
                                                            remoteGroup:false,
				                                            remoteSort: false,
                                                            autoLoad:true
                                                            
                                                        }) 
	alDatos.on('beforeload',function(proxy)
    								{
                                    	var idFuncion=37;
                                        proxy.baseParams.tipoVentaActiva=tipoVentaActiva;
                                        var idRegistro=0;
                                        
                                        if(tipoVentaActiva==1)
                                        	idRegistro=idVentaActiva;
                                        else
                                        {
                                        	idRegistro=idPedidoActivo;
                                            idFuncion=62;
                                        }
                                        proxy.baseParams.idVenta=idRegistro;
                                        proxy.baseParams.funcion=idFuncion;
                                    }
                        )   
       
        var cModelo= new Ext.grid.ColumnModel   	(
                                                        [
                                                            new  Ext.grid.RowNumberer(),
                                                            
                                                            {
                                                                header:'Fecha abono',
                                                                width:90,
                                                                sortable:true,
                                                                dataIndex:'fechaAbono',
                                                                renderer:function(val)
                                                                		{
                                                                        	return val.format('d/m/Y');
                                                                        }
                                                            },
                                                            {
                                                                header:'Monto abono',
                                                                width:100,
                                                                css:'text-align:right;',
                                                                sortable:true,
                                                                dataIndex:'montoAbono',
                                                                renderer:'usMoney'
                                                            },
                                                            
                                                            {
                                                                header:'Forma de pago',
                                                                width:200,
                                                                
                                                                sortable:true,
                                                                dataIndex:'formaPago',
                                                                renderer:function(val)
                                                                		{
                                                                        	return formatearValorRenderer(arrFormaPagoBD,val);
                                                                        }
                                                            },
                                                            {
                                                                header:'Facturado',
                                                                width:60,
                                                                css:'text-align:right;',
                                                                sortable:true,
                                                                dataIndex:'idComprobante',
                                                                renderer:function(val,meta,registro)
                                                                		{
                                                                        	var comp='';
                                                                        	if(val=='')
                                                                            	return 'No';
                                                                            else
                                                                            	
                                                                            	return 'S&iacute;';
                                                                        }
                                                            },
                                                            {
                                                                header:'',
                                                                width:120,
                                                                css:'text-align:left;',
                                                                sortable:true,
                                                                dataIndex:'idComprobante',
                                                                renderer:function(val,meta,registro)
                                                                		{
                                                                        	var comp='';
                                                                        	if(val=='')
                                                                            {
                                                                            	comp=' <a href="javascript:mostrarVentanaFacturacionAbono(\''+bE(registro.data.idAbono)+'\')"><img  src="../images/Icono_txt.gif" title="Generar Factura" alt="Generar Factura"> Generar factura</a>';
                                                                            	return comp;
                                                                            }
                                                                            else
                                                                            	comp=' <a href="javascript:mostrarFacturaAbono(\''+bE(val)+'\')"><img src="../images/magnifier.png" title="Ver comprobante" alt="Ver comprobante"> Ver comprobante</a>';
                                                                            	return comp;
                                                                        }
                                                            }
                                                        ]
                                                    );
                                                    
        var tblGrid=	new Ext.grid.GridPanel	(
                                                            {
                                                                id:'gridAbonosHistorial',
                                                                store:alDatos,
                                                                x:10,
                                                                y:10,
                                                                height:680,
                                                                height:250,
                                                                frame:false,
                                                                border:true,
                                                                cm: cModelo,
                                                                stripeRows :true,
                                                                loadMask:true,
                                                                columnLines : true,
                                                                
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
                                                        );
        return 	tblGrid;
}

function mostrarFacturaAbono(iC)
{
	var obj={};
    obj.titulo='Factura';
    obj.ancho=900;
    obj.alto=450;
    obj.params=[['iC',iC],['cPagina','sFrm=true']];
    obj.url='../formatosFacturasElectronicas/vistaPreviaCFDI.php';
    abrirVentanaFancy(obj);
}



function buscarExistenciaProductoPorProductoNombre(idZona)
{
	var regProducto=null;
    
    var gridProductoBuscar=crearGridBuscarProductoV2();
    
    var oConf=	{
    					idCombo:'cmbCodigoAlterno',
                        anchoCombo:200,
                        campoDesplegar:'codigoAlterno',
                        campoID:'llaveProducto',
                        funcionBusqueda:189,
                        raiz:'registros',
                        nRegistros:'numReg',
                        posX:170,
                        posY:5,
                        paginaProcesamiento:'../paginasFunciones/funcionesAlmacen.php',
                        confVista:	'<tpl for="."><div class="search-item"><table><tr><td width="380">[{codigoAlterno}] {descripcion}<br><b>Precio de venta:</b> {precioUnitario:usMoney}</td><td width="50"></td></tr></table></div></tpl>',
                        campos:	[
                                    {name:'idProducto'},
                                    {name:'llave'},
                                    {name:'codigoAlterno'},
                                    {name:'descripcion'},
                                    {name: 'tasaIVA'},
                                    {name: 'llaveProducto'},
                                    {name: 'precioUnitario'}
                                   
                                ],
                       	funcAntesCarga:function(dSet,combo)
                    				{
                                    
                                    	var arrChek=gEN('chkSel');
                                        var arrCategorias='';
                                        var x;
                                        for(x=0;x<arrChek.length;x++)
                                        {
                                            if(arrChek[x].checked)
                                            {
                                                if(arrCategorias=='')
                                                    arrCategorias="'"+arrChek[x].getAttribute('id')+"'";
                                                else
                                                    arrCategorias+=',\''+arrChek[x].getAttribute('id')+"'";
                                            }
                                        }
                                    
                                    	
                                        
                                        dSet.baseParams.valorBusqueda=gEx('cmbCodigoAlterno').getRawValue();
                                        dSet.baseParams.tipoBusqueda=1;
                                        dSet.baseParams.arrCategorias=arrCategorias;
                                        dSet.baseParams.idZona=idZona;
                                        dSet.baseParams.tipoCliente=tipoCliente;
                                        dSet.baseParams.idCliente=idCliente;
                                        dSet.baseParams.buscarPrecio=1;
                                        dSet.baseParams.idAlmacen=gE('idAlmacenAsociado').value;
                                        
                                        gEx('cmbDescripcion').setValue(''); 
                                    },
                      	funcElementoSel:function(combo,registro)
                    				{
                                    	var fila=registro;
                                        gEx('cmbCodigoAlterno').setRawValue(registro.data.codigoAlterno); 
                                        
                                      	buscarExistenciaProductoCaja(fila.data.idProducto,fila.data.llave); 
                                        
                                        gE('lblPrecioVentaBusqueda').innerHTML=Ext.util.Format.usMoney(fila.data.precioUnitario);
                                        
                                    }  
    				};
    
	var cmbCodigoAlterno=crearComboExtAutocompletar(oConf);
    
    var oConf=	{
    					idCombo:'cmbDescripcion',
                        anchoCombo:330,
                        campoDesplegar:'descripcion',
                        campoID:'llaveProducto',
                        funcionBusqueda:189,
                        raiz:'registros',
                        nRegistros:'numReg',
                        posX:170,
                        posY:35,
                        paginaProcesamiento:'../paginasFunciones/funcionesAlmacen.php',
                        confVista:	'<tpl for="."><div class="search-item"><table><tr><td width="380">[{codigoAlterno}] {descripcion}<br><b>Precio de venta:</b> {precioUnitario:usMoney}</td><td width="50"></td></tr></table></div></tpl>',
                        campos:	[
                                    {name:'idProducto'},
                                    {name:'llave'},
                                    {name:'codigoAlterno'},
                                    {name:'descripcion'},
                                    {name: 'tasaIVA'},
                                    {name: 'llaveProducto'},
                                    {name: 'precioUnitario'}
                                    
                                   
                                ],
                       	funcAntesCarga:function(dSet,combo)
                    				{
                                    	var arrChek=gEN('chkSel');
                                        var arrCategorias='';
                                        var x;
                                        for(x=0;x<arrChek.length;x++)
                                        {
                                            if(arrChek[x].checked)
                                            {
                                                if(arrCategorias=='')
                                                    arrCategorias="'"+arrChek[x].getAttribute('id')+"'";
                                                else
                                                    arrCategorias+=',\''+arrChek[x].getAttribute('id')+"'";
                                            }
                                        }
                                    	
                                        
                                        dSet.baseParams.valorBusqueda=gEx('cmbDescripcion').getRawValue();
                                        dSet.baseParams.tipoBusqueda=2;
                                        
                                        dSet.baseParams.arrCategorias=arrCategorias;
                                        dSet.baseParams.idZona=idZona;
                                        dSet.baseParams.tipoCliente=tipoCliente;
                                        dSet.baseParams.idCliente=idCliente;
                                        dSet.baseParams.buscarPrecio=1;
                                        dSet.baseParams.idAlmacen=gE('idAlmacenAsociado').value;
                                        
                                        
                                        gEx('cmbCodigoAlterno').setValue(''); 
                                        
                                    },
                      	funcElementoSel:function(combo,registro)
                    				{
                                    	var fila=registro;
                                        buscarExistenciaProductoCaja(fila.data.idProducto,fila.data.llave);
                                        gE('lblPrecioVentaBusqueda').innerHTML=Ext.util.Format.usMoney(fila.data.precioUnitario); 
                                    }  
    				};
    
	var cmbDescripcion=crearComboExtAutocompletar(oConf);
    
	var form = new Ext.form.FormPanel(	
										{
											baseCls: 'x-plain',
											layout:'absolute',
											defaultType: 'label',
											items: 	[
                                            			{
                                                        	xtype:'fieldset',
                                                            defaultType: 'label',
                                                            x:10,
                                                            y:10,
                                                            layout:'absolute',
                                                            width:550,
                                                            height:165,
                                                            title:'B&uacute;squeda de producto',
                                                            items:	[
                                                                        
                                                                        {
                                                                            x:10,
                                                                            y:10,
                                                                            html:'<b>C&oacute;digo alterno:</b>'
                                                                        },
                                                                        cmbCodigoAlterno,
                                                                        {
                                                                            x:10,
                                                                            y:40,
                                                                            id:'lblDescripcion',
                                                                            html:'<b>Descripci&oacute;n del producto:</b>'
                                                                        },
                                                                        cmbDescripcion,
                                                                        {
                                                                            x:10,
                                                                            y:70,
                                                                            html:'<b>Producto en existencia:</b>'
                                                                        },
                                                                        {
                                                                        	x:170,
                                                                            y:65,
                                                                            xtype:'textfield',
                                                                            id:'totalExistencia',
                                                                            width:300,
                                                                            readOnly:true
                                                                        },
                                                                        {
                                                                            x:10,
                                                                            y:100,
                                                                            html:'<b>Precio de venta:</b> <b><span style="color:#900" id="lblPrecioVentaBusqueda"></span></b>'
                                                                        }
                                                                        
                                                                    ]
                                                        },
                                                        gridProductoBuscar,
                                                        crearGridCategorias()

													]
										}
									);
	
	var ventanaAM = new Ext.Window(
									{
										title: 'B&uacute;squeda de existencia de producto',
                                        id:'vBuscarDescripcion',
										width: 840,
										height:470,
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
                                                                	gEx('cmbCodigoAlterno').focus(false,500);
																}
															}
												},
										buttons:	[
														{
															
															text: '<?php echo $etj["lblBtnAceptar"]?>',
                                                            
															handler: function()
																	{
																		
                                                                         
                                                                         ventanaAM.close();
																	}
														}
													]
									}
								);
	ventanaAM.show();	
    
    gEx('gProductosBuscados').setHeight(170);
    gEx('gProductosBuscados').setPosition(10,185);
    gEx('gProductosBuscados').getSelectionModel().on('rowselect',function(sm,nFila,registro)
    															{
                                                                	gEx('cmbDescripcion').setValue('');
                                                                    gEx('cmbCodigoAlterno').setValue('');
                                                                    gEx('cmbCodigoAlterno').setValue(registro.data.codigoAlterno);
                                                                    gEx('cmbDescripcion').setValue(registro.data.nombreProducto);
                                                                	buscarExistenciaProductoCaja(registro.data.idProducto,registro.data.llave); 
                                                                    gE('lblPrecioVentaBusqueda').innerHTML=Ext.util.Format.usMoney(registro.data.precioUnitario);	
                                                                }
    												)
    
      
}


function buscarExistenciaProductoCaja(idProducto,llave)
{
	function funcAjax()
    {
        var resp=peticion_http.responseText;
        arrResp=resp.split('|');
        if(arrResp[0]=='1')
        {
            gEx('totalExistencia').setValue(arrResp[1]);
        }
        else
        {
            msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
        }
    }
    obtenerDatosWeb('../paginasFunciones/funcionesAlmacen.php',funcAjax, 'POST','funcion=180&idAlmacen='+gE('idAlmacenAsociado').value+'&idProducto='+idProducto+'&llave='+llave,true);
    
}

function setTipoClienteAgregado(idRegistro)
{
	var cmbTipoCliente=gEx('cmbTipoCliente');
    function funcAjax()
    {
        var resp=peticion_http.responseText;
        arrResp=resp.split('|');
        if(arrResp[0]=='1')
        {
         	
            
            gEx('cmbAlumno').setRawValue(arrResp[1]);   
            iCliente=idRegistro;
        }
        else
        {
            msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
        }
    }
    obtenerDatosWeb('../paginasFunciones/funcionesTesoreria.php',funcAjax, 'POST','funcion=60&tipoCliente='+gEx('cmbTipoCliente').getValue()+'&idRegistro='+idRegistro,true);
    
    
    
    
	
}

function mostrarVentanaBuscarClienteVenta()
{	
	iCliente=-1;
	var aTipoCliente=new Array();
    var x;
    var fila;
    for(x=0;x<arrTipoCliente.length;x++)
    {
    	fila=arrTipoCliente[x];
        if(fila[0]!='1')
        
	    	aTipoCliente.push([fila[0],fila[1]]);
    }
    
    var cmbTipoCliente=crearComboExt('cmbTipoCliente',aTipoCliente,110,5,200);
    
    var pos=obtenerPosFila(gEx('cmbTipoCliente').getStore(),'id','3');
    if(pos!=-1)
		cmbTipoCliente.setValue('3');
    else
    {
    	if(gEx('cmbTipoCliente').getStore().getCount()==1)
        {
        	cmbTipoCliente.setValue(gEx('cmbTipoCliente').getStore().getAt(0).data.id);
        }
    }
        
    var gridVentas=crearGridVentasV2();
	cmbTipoCliente.on('select',function(cmb,registro)
    							{
                                	iCliente=-1;
                                	gEx('cmbAlumno').reset();
                                    
                                    gEx('lNombreEmpleado').hide();
                                    gEx('cmbAlumno').hide();
                                    
                                    var pos=existeValorMatriz(arrTipoCliente,registro.data.id,0);
                                    
                                    var fTipo=arrTipoCliente[pos];
                                    if(fTipo[6]!='')
                                    {
                                    	gEx('lNombreEmpleado').show();
                                        gEx('cmbAlumno').show();
                                        gE('lblNombreEmpleado').innerHTML=fTipo[6]+':';
                                    }
                                    
                                    
                                    gEx('cmbAlumno').focus(false,500);
                                    
                                    
                                }
    					)
                        
	var oConf=	{
    					idCombo:'cmbAlumno',
                        anchoCombo:330,
                        campoDesplegar:'cliente',
                        campoID:'idUsuario',
                        funcionBusqueda:4,
                        raiz:'personas',
                        nRegistros:'num',
                        posX:485,
                        posY:5,
                        paginaProcesamiento:'../paginasFunciones/funcionesModulosEspeciales_Sigloxxi.php',
                        confVista:	'<tpl for="."><div class="search-item"><table><tr><td width="380">{cliente}</td><td width="50"></td></tr></table></div></tpl>',
                        campos:	[
                                    {name:'idUsuario'},
                                    {name:'cliente'}
                                   
                                ],
                       	funcAntesCarga:function(dSet,combo)
                    				{
                                    	iCliente=-1;
                                        dSet.baseParams.tipoCliente=gEx('cmbTipoCliente').getValue();
                                        
                                        gridVentas.getStore().removeAll();
                                    },
                      	funcElementoSel:function(combo,registro)
                    				{
                                    	iCliente=registro.get('idUsuario');
                                        recargarGridVentas();
                                        
                                       
                                    }  
    				};

    
	var cmbPersonal=crearComboExtAutocompletar(oConf);

	var form = new Ext.form.FormPanel(	
										{
											baseCls: 'x-plain',
											layout:'absolute',
											defaultType: 'label',
											items: 	[
                                            			
                                            			{
                                                        	xtype:'label',
                                                            x:10,
                                                            y:10,
                                                            
                                                            html:'<span style="color:#000; font-weight:bold">Tipo de cliente:</span>'
                                                        },
                                                        cmbTipoCliente,
                                                        {
                                                        	xtype:'label',
                                                            x:335,
                                                            y:10,
                                                            id:'lNombreEmpleado',
                                                            html:'<span style="color:#000; font-weight:bold" id="lblNombreEmpleado">Nombre del Empleado:</span>'
                                                        },
                                                        cmbPersonal,
                                                        gridVentas
													]
										}
									);
	
	var ventanaAM = new Ext.Window(
									{
                                    	id:'vVentasCliente',
										title: 'Busqueda de ventas por cliente',
										layout: 'fit',
										plain:true,
										modal:true,
                                        width:960,
                                        height:450,
										bodyStyle:'padding:5px;',
										buttonAlign:'center',
										items: form,
										listeners : {
                                                        show : {
                                                                    buffer : 100,
                                                                    fn : function() 
                                                                    {
                                                                        
                                                                         gEx('cmbAlumno').focus(false,500);
                                                                    }
                                                                }
                                                    },
										buttons:	[
														{
                                                        	id:'btnVBuscarVentasCliente',
															text: '<?php echo $etj["lblBtnAceptar"]?>',
                                                            listeners:	{
                                                                            click: function()
                                                                                    {
                                                                                        var fila=gridVentas.getSelectionModel().getSelected();
                                                                                        if(fila)
                                                                                        {
                                                                                            tipoBusqueda=4;
                                                                                            gEx('txtClave').setValue(fila.data.folioVenta);
                                                                                            funcionEjecucionBusqueda=inicializacionVentaLocalizado
                                                                                            buscarCodigoProducto();
                                                                                        }
                                                                                        
                                                                                        gEx('vVentasCliente').close();
                                                                                    }
                                                                         }
														}
														
													]
									}
								);
                                
	 
                                
	ventanaAM.show();
    
    
    if(gEx('cmbTipoCliente').getValue()!='')
	    dispararEventoSelectCombo('cmbTipoCliente');

}

function crearGridVentasV2()
{
	 var lector= new Ext.data.JsonReader({
                                            
                                            totalProperty:'numReg',
                                            fields: [
                                               			{name:'idVenta', type:'int'},
		                                                {name: 'fechaVenta', type:'date', dateFormat:'Y-m-d'},
		                                                {name: 'folioVenta'},
		                                                {name: 'formaPago'},
                                                        {name: 'total'},
                                                        {name: 'idFactura'}
                                            		],
                                            root:'registros'
                                            
                                        }
                                      );	 
                                                                                      
	var alDatos=new Ext.data.GroupingStore({
                                              reader: lector,
                                              proxy : new Ext.data.HttpProxy	(

                                                                                {

                                                                                    url: '../paginasFunciones/funcionesTesoreria.php'

                                                                                }

                                                                            ),
                                              sortInfo: {field: 'fechaVenta', direction: 'DESC'},
                                              groupField: 'fechaVenta',
                                              remoteGroup:false,
                                              remoteSort: true,
                                              autoLoad:false
                                              
                                          }) 
       
	alDatos.on('beforeload',function(proxy)
    								{
                                    	proxy.baseParams.funcion='61';
                                        proxy.baseParams.tipoCliente=gEx('cmbTipoCliente').getValue()
                                        proxy.baseParams.idCliente=gEx('cmbAlumno').getValue();
                                        
                                      
                                        
                                    }
                        )          
       
       
	var filters = new Ext.ux.grid.GridFilters	(
    												{
                                                    	filters:	[ 
                                                        				{type: 'date', dataIndex: 'fechaVenta'},
                                                                        {type: 'list', dataIndex: 'idFactura',phpMode:true, options:arrSiNo}
                                                                        
                                                                        
                                                                        
                                                        			]
                                                    }
                                                );             
       
  
	var chkRow=new Ext.grid.CheckboxSelectionModel();       
    
    var cModelo= new Ext.grid.ColumnModel   	(
                                                        [
                                                            new  Ext.grid.RowNumberer(),
                                                            chkRow,
                                                            {
                                                                header:'Fecha de la venta',
                                                                width:130,
                                                                sortable:true,
                                                                dataIndex:'fechaVenta',
                                                                renderer:function(val)
                                                                		{
                                                                        	if(val)
                                                                            	return val.format('d/m/Y');
                                                                        }
                                                            },
                                                            {
                                                                header:'Folio de la venta',
                                                                width:150,
                                                                sortable:true,
                                                                dataIndex:'folioVenta'
                                                            },
                                                            {
                                                                header:'Forma de pago',
                                                                width:180,
                                                                sortable:true,
                                                                dataIndex:'formaPago',
                                                                renderer:function(val)
                                                                		{
                                                                        	return formatearValorRenderer(arrFormaPagoBD,val);
                                                                        }
                                                            },
                                                           
                                                            {
                                                                header:'Total venta',
                                                                width:110,
                                                                sortable:true,
                                                                dataIndex:'total',
                                                                css:'text-align:right;',
                                                                renderer:'usMoney'
                                                            },
                                                            {
                                                                header:'Facturado',
                                                                width:110,
                                                                sortable:true,
                                                                css:'text-align:right;',
                                                                dataIndex:'idFactura',
                                                                renderer:function(val)
                                                                		{
                                                                        	return formatearValorRenderer(arrSiNo,val);
                                                                        }
                                                            }
                                                        ]
                                                    );
                               
                               
	var paginador=	new Ext.PagingToolbar	(
                                              {
                                                    pageSize: 50,
                                                    store: alDatos,
                                                    displayInfo: true,
                                                    disabled:false
                                                }
                                             )                                                    
                                                   
                               
                                                    
        var tblGrid=	new Ext.grid.GridPanel	(
                                                    {
                                                        id:'gVentasV2',
                                                        store:alDatos,
                                                        x:10,
                                                        y:50,
                                                        height:300,
                                                        width:900,
                                                        frame:false,
                                                        cm: cModelo,
                                                        stripeRows :true,
                                                        loadMask:true,
                                                        columnLines : true,
                                                        sm:chkRow,
                                                        bbar:[paginador],
                                                        plugins:[filters],
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
                                                );
                                                        
	tblGrid.on	('rowdblclick',function()
    							{
                                	
                                	gEx('btnVBuscarVentasCliente').fireEvent('click',gEx('btnVBuscarVentasCliente'));
                                }	
    	
    			)                                                        
                                                        
    return 	tblGrid;	
}

function recargarGridVentas()
{
	gEx('gVentasV2').getStore().load	(
    										{
                                            	url:'../paginasFunciones/funcionesTesoreria.php',
                                                params:	{
                                                			funcion:61,
                                                			tipoCliente:gEx('cmbTipoCliente').getValue(),
                                        					idCliente:gEx('cmbAlumno').getValue(),
                                                            start:0,
                                                            limit:50
                                                		}
                                            }
    									)
}

function mostrarVentanaFacturacionAbono(iAbono)
{
	mostrarVentanaFacturacion(bD(iAbono),1);
}