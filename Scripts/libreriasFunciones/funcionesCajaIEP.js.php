<?php
	session_start();
	include("configurarIdiomaJS.php");
	include("conexionBD.php");
	$consulta="SELECT idMotivo,motivoDevolucion FROM 6948_motivosDevolucion where aplicableDevolucion in (0,2) ORDER BY motivoDevolucion";
	$arrMotivoDevolucion=$con->obtenerFilasArreglo($consulta);
	$consulta="SELECT idFormaPago,formaPago FROM 600_formasPago";
	$arrFormaPago=$con->obtenerFilasArreglo($consulta);
	
	
	$consulta="SELECT id__464_gridPeriodos,nombrePeriodo,idReferencia FROM _464_gridPeriodos";
	$arrPeriodos=$con->obtenerFilasArreglo($consulta);
	
	
	$consulta="SELECT idCiclo,nombreCiclo FROM 4526_ciclosEscolares";
	$arrCiclos=$con->obtenerFilasArreglo($consulta);	
	
	$consulta="SELECT claveElemento,nombreElemento FROM 1018_catalogoVarios WHERE tipoElemento=102 ORDER BY claveElemento";
	$arrMes=$con->obtenerFilasArreglo($consulta);
	$fechaActual=date("Y-m-d");
		
?>

var idClienteBusqueda=-1;
var aPeriodos=[];
var fechaActual=null;
var arrCiclos=<?php echo $arrCiclos?>;
var arrPeriodos=<?php echo $arrPeriodos?>;
var arrMes=<?php echo $arrMes?>;

function buscarNombreAlumno(tFuncion)
{

	fechaActual=Date.parseDate('<?php echo $fechaActual?>','Y-m-d');
	tipoBusqueda=1;
    var posDefault=existeValorMatriz(arrFuncionBusqueda,tFuncion);    
    gE('lblBusqueda').innerHTML=arrFuncionBusqueda[posDefault][2]+':';
   
                            
	window.funcionEjecucionBusquedaRealizada=function(arrResp)
                                            {
                                                metaData=null;
                                                if(arrResp[2]!='')
                                                {
                                                    metaData=eval(''+arrResp[2]+'');
                                                }
                                                if(metaData.length==0)
                                                {
                                                    mostrarVentanaSeleccionAlumno(metaData);
                                                }  
                                                else
                                                {
                                                	mostrarVentanaSeleccionAlumno(metaData);
                                                }
                                            }
                                            ;                            
                                            
}

function mostrarVentanaSeleccionAlumno(metaData)
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
                                                            html:'Seleccione el alumno cuyo cobro desea registrar:'
                                                        },
                                                        crearGridAlumnos(metaData)

													]
										}
									);
	
	var ventanaAM = new Ext.Window(
									{
                                    	id:'vSelAlumno',
										title: 'Selecci&oacute;n de alumno',
										width: 850,
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
                                                            
															handler: function()
																	{
																		var gAlumnosBusqueda=gEx('gAlumnosBusqueda');
                                                                        var fila=gAlumnosBusqueda.getSelectionModel().getSelected();
                                                                        if(!fila)
                                                                        {
                                                                        	msgBox('Debe seleccionar el alumno al cual desea registrar el cobro');
                                                                            return;
                                                                        }
																		
                                                                        
                                                                        mostrarDatosAlumno(fila);
                                                                        
                                                                        
                                                                        
                                                                        
                                                                        
                                                                     	
																	}
														},
														{
															text: '<?php echo $etj["lblBtnCancelar"]?>',
															handler:function()
																	{	
                                                                    	
																		ventanaAM.close();
                                                                        gEx('txtClave').setValue('');
                                                                        gEx('txtClave').focus();
                                                                        
																	}
														}
													]
									}
								);
	ventanaAM.show();
}

function mostrarDatosAlumno(fila)
{
	mostrarVentanaSeleccionServicio(fila.data.idRegistro);
    return;
	<?php
		//if($_SESSION["idUsr"]==2)
		//{
	?>
		//	
	<?php
		//}
		//else
		{
	?>
			var objConf={};
			objConf.ancho='100%';
			objConf.alto='100%';
			objConf.url='../modeloPerfiles/registroFormularioV2.php';
			objConf.params=[['eJs',bE('window.parent.mostrarVentanaSeleccionServicio(\'@idRegistro\');return;')],['accionCancelar',('window.parent.cerrarVentanaFancy()')],['cPagina','sFrm=true'],['idFormulario','1069'],['idRegistro',fila.data.idRegistro]];
			abrirVentanaFancy(objConf);
	
	
	<?php
	
		
		}
	?>
}

function crearGridAlumnos(metaData)
{
	var dsDatos=metaData;
    var alDatos=	new Ext.data.JsonStore	(
                                                    {
                                                        fields:	[
                                                        			{name: 'idRegistro'},
                                                                    {name: 'apPaterno'},
                                                                    {name: 'apMaterno'},
                                                                    {name: 'nombre'},
                                                                    {name: 'curp'},
                                                                    {name: 'planEstudio'},
                                                                    {name: 'grado'},
                                                                    {name: 'grupo'}
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
															header:'Ap. Paterno',
															width:100,
															sortable:true,
															dataIndex:'apPaterno'
														},
														{
															header:'Ap. Materno',
															width:100,
															sortable:true,
															dataIndex:'apMaterno'
														},
														{
															header:'Nombre',
															width:120,
															sortable:true,
															dataIndex:'nombre'
														},
                                                        {
															header:'CURP',
															width:100,
															sortable:true,
															dataIndex:'curp'
														},
                                                        {
															header:'Plan de estudios',
															width:200,
															sortable:true,
															dataIndex:'planEstudio',
                                                            renderer:mostrarValorDescripcion
														},
                                                        {
															header:'Grado',
															width:140,
															sortable:true,
															dataIndex:'grado',
                                                            renderer:mostrarValorDescripcion
														},
                                                        {
															header:'Grupo',
															width:70,
															sortable:true,
															dataIndex:'grupo',
                                                            renderer:mostrarValorDescripcion
														}
													]
												);
                                                
	var tblGrid=	new Ext.grid.EditorGridPanel	(
                                                        {
                                                            id:'gAlumnosBusqueda',
                                                            store:alDatos,
                                                            frame:false,
                                                            y:40,
                                                            cm: cModelo,
                                                            stripeRows :true,
                                                            loadMask:true,
                                                            stripeRows :true,
                                                            columnLines : true,
                                                            height:320,
                                                            width:820,
                                                            sm:chkRow,
                                                            tbar:	[
                                                            			{
                                                                        	icon:'../images/add.png',
                                                                            cls:'x-btn-text-icon',
                                                                            text:'Registrar nuevo alumno',
                                                                            handler:function()
                                                                            		{
                                                                                    	var objConf={};
                                                                                        objConf.ancho='100%';
                                                                                        objConf.alto='100%';
                                                                                        objConf.url='../modeloPerfiles/registroFormularioV2.php';
                                                                                        objConf.params=[['eJs',bE('window.parent.mostrarVentanaSeleccionServicio(\'@idRegistro\');return;')],['accionCancelar',('window.parent.cerrarVentanaFancy()')],['cPagina','sFrm=true'],['idFormulario','1069'],['idRegistro','-1']];
                                                                                        abrirVentanaFancy(objConf);
                                                                                        
                                                                                    }
                                                                            
                                                                        }
                                                                        
                                                            		]
                                                        }
                                                    );

	tblGrid.on('rowdblclick',function(g,nFila)
    						{
                            	var fila=g.getStore().getAt(nFila);
                               
                                
                                
                                mostrarDatosAlumno(fila);
                            }
                            
    			)
	return 	tblGrid;		
}

function mostrarVentanaSeleccionServicio(idRegistro)
{
	if(gEx('vSelAlumno'))
    	gEx('vSelAlumno').close();
	cerrarVentanaFancy();
	var form = new Ext.form.FormPanel(	
										{
											baseCls: 'x-plain',
											layout:'absolute',
											defaultType: 'label',
											items: 	[
                                            			{
                                                        	xtype:'textfield',
                                                            readOnly:true,
                                                            width:160,
                                                            x:10,
                                                            y:10,
                                                            id:'txtApPaterno'
                                                        },
														{
                                                        	x:60,
                                                            y:40,
                                                            html:'Ap. paterno'
                                                        },
                                                        {
                                                        	xtype:'textfield',
                                                            readOnly:true,
                                                            width:160,
                                                            x:200,
                                                            y:10,
                                                            id:'txtApMaterno'
                                                        },
                                                        {
                                                        	x:250,
                                                            y:40,
                                                            html:'Ap. materno'
                                                        },
                                                        {
                                                        	xtype:'textfield',
                                                            readOnly:true,
                                                            width:200,
                                                            x:390,
                                                            y:10,
                                                            id:'txtNombre'
                                                        },
                                                        {
                                                        	x:460,
                                                            y:40,
                                                            html:'Nombre'
                                                        },
                                                        {
                                                        	xtype:'textfield',
                                                            readOnly:true,
                                                            width:320,
                                                            x:10,
                                                            y:70,
                                                            id:'txtPlanEstudio'
                                                        },
														{
                                                        	x:140,
                                                            y:100,
                                                            html:'Plan de estudios'
                                                        },
                                                        {
                                                        	xtype:'textfield',
                                                            readOnly:true,
                                                            width:160,
                                                            x:340,
                                                            y:70,
                                                            id:'txtGrado'
                                                        },
                                                        {
                                                        	x:400,
                                                            y:100,
                                                            html:'Grado'
                                                        },
                                                        {
                                                        	xtype:'textfield',
                                                            readOnly:true,
                                                            width:70,
                                                            x:510,
                                                            y:70,
                                                            id:'txtGrupo'
                                                        },
                                                        {
                                                        	x:530,
                                                            y:100,
                                                            html:'Grupo'
                                                        },
                                                        {
                                                        	x:10,
                                                            y:130,
                                                            html:'Indique los conceptos que desea cobrar:'
                                                        },
                                                        crearGridConceptosCobro()

													]
										}
									);
	
	var ventanaAM = new Ext.Window(
									{
										title: 'Cobro de servicio',
										width: 950,
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
                                                            
															handler: function()
																	{
																		var arrFilas=[];
                                                                        var gCobroServicios=gEx('gCobroServicios')
                                                                        var x;
                                                                        var f;
                                                                        for(x=0;x<gCobroServicios.getStore().getCount();x++)
                                                                        {
                                                                        	f=gCobroServicios.getStore().getAt(x);
                                                                            if(f.data.considerarCobro)
                                                                            	arrFilas.push(f);
                                                                        }
                                                                        
                                                                        
                                                                        if(arrFilas.length==0)
                                                                        {
                                                                        	msgBox('Debe seleccionar almenos un concepto a cobrar');
                                                                            return;
                                                                        }
                                                                        
                                                                        var iva=0;
                                                                        for(x=0;x<arrFilas.length;x++)
                                                                        {
                                                                        	f=arrFilas[x];

                                                                            var lblCiclo='Ciclo: '+formatearValorRenderer(arrCiclos,f.data.idCiclo)+', Periodo: '+formatearValorRenderer(arrPeriodos,f.data.idPeriodo)+
                                                                            			', Mes: '+formatearValorRenderer(arrMes,f.data.idMes);
                                                                            valIva=f.data.costo*(parseFloat(f.data.iva)/100);
                                                                            var objComp='{"ciclo":"'+f.data.idCiclo+'","mes":"'+f.data.idMes+'","periodo":"'+f.data.idPeriodo+'","comentarios":"'+
                                                                            			cv(f.data.comentarios)+'","motivoDescuento":"'+cv(f.data.descDescuento)+'"}';
                                                                            r=new regProducto	(
                                                                            						{
                                                                                                         idRegistro:f.data.idConceptoCobro,
                                                                                                          cveProducto:f.data.idConceptoCobro,
                                                                                                          concepto:f.data.leyendaConcepto,
                                                                                                          costoUnitario:f.data.costo-valIva,
                                                                                                          precioUnitarioOriginal:f.data.costo,
                                                                                                          cantidad:1,
                                                                                                          subtotal:'',                                                                
                                                                                                          iva:valIva,
                                                                                                          total:parseFloat(f.data.costo),
                                                                                                          imagen:'',
                                                                                                          tipoConcepto:f.data.tipoProducto,
                                                                                                          idProducto:f.data.idConceptoCobro,
                                                                                                          detalle:f.data.comentarios,
                                                                                                          dimensiones:'',
                                                                                                          tipoMovimiento:'',
                                                                                                          _parent:'',
                                                                                                          _is_leaf:true,
                                                                                                          descripcion:(parseFloat(f.data.descuento)>0)?(lblCiclo+'<br><br><b>Comentarios adicionales:</b> '+f.data.comentarios+'<br><br><b>Motivo de descuento:</b> '+f.data.descDescuento):(lblCiclo+'<br><br><b>Comentarios adicionales:</b> '+f.data.comentarios),
                                                                                                          porcentajeIVA:f.data.iva,
                                                                                                          llave:'',
                                                                                                          numDevueltos:0,
                                                                                                          descuento:f.data.descuento,
                                                                                                          sL:0,
                                                                                                          metaData:objComp,
                                                                                                          tipoPrecio:'0',
                                                                                                          costoUnitarioConDescuento:(f.data.costo/(1+(parseFloat(f.data.iva)/100)))- parseFloat(f.data.descuento)
                                                                            						}
                                                                                                );
    																		
                                                                        	 gEx('grid').getStore().add(r);    
                                                                        }
                                                                        iCliente=idRegistro;
                                                                        idCliente=idRegistro;
                                                                            
                                                                        gE('lblCliente').innerHTML='P&uacute;blico general';
                                                                        gE('lblNombreCliente').innerHTML=gEx('txtApPaterno').value+' '+gEx('txtApMaterno').value+' '+gEx('txtNombre').value;
                                                                       
                                                                        calcularTotal();
                                                                        seleccionarUltimaFila();
                                                                        ventanaAM.close();
                                                                        gEx('btnPagar').enable();
                                                                        gEx('btnCancelar').enable();
                                                                        gEx('btnAbono').hide();
                                                                        gEx('txtClave').hide();
                                                                        gEx('txtCantidad').hide();
                                                                        oE('lblBusqueda');
                                                                        oE('lblCantidad');
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
	buscarDatosAlumno(idRegistro,ventanaAM);

	
}

function crearGridConceptosCobro()
{
	var cmbCiclo=crearComboExt('cmbCiclo',arrCiclos,0,0);
    var cmbPeriodo=crearComboExt('cmbPeriodo',arrPeriodos,0,0);
    var cmbMes=crearComboExt('cmbMes',arrMes,0,0);
	var dsDatos=[];
    var alDatos=	new Ext.data.SimpleStore	(
                                                    {
                                                        fields:	[
                                                                    {name: 'idConceptoCobro'},
                                                                    {name: 'leyendaConcepto'},
                                                                    {name: 'iva'},
                                                                    {name: 'costo'},
                                                                    {name: 'considerarCobro'},
                                                                    {name: 'comentarios'},
                                                                    {name: 'idCiclo'},
                                                                    {name: 'idPeriodo'},
                                                                    {name: 'idMes'},
                                                                    {name: 'pocentajeDescuento'},
                                                                    {name: 'descDescuento'},
                                                                    {name: 'descuento'},
                                                                    {name: 'costoFinal'},
                                                                    {name: 'tipoProducto'},
                                                                    {name: 'fechaLimiteDescuento'},
                                                                    {name: 'cobrable'},
                                                                    {name: 'motivoNoCobro'}
                                                                ]
                                                    }
                                                );

    alDatos.loadData(dsDatos);
	var chkRow=new Ext.grid.CheckboxSelectionModel({singleSelect:true});
	
    
    var checkColumn = new Ext.grid.CheckColumn	(
	 												{
													   header: '',
													   dataIndex: 'considerarCobro',
													   width: 50
													}
												);
    
    
    
    
	var cModelo= new Ext.grid.ColumnModel   	(
												 	[
													 	checkColumn,
														{
															header:'Concepto',
															width:210,
															sortable:true,
                                                            css:'height:21px;vertical-align:top;',
															dataIndex:'leyendaConcepto',
                                                            renderer:function(val,meta,registro)
                                                                    {
                                                                    	var color='000';
                                                                    	meta.attr='style="height:18px;vertical-align:bottom;"';
                                                                    	var comp='';
                                                                    	if(registro.data.cobrable=='0')
                                                                        {
                                                                        	color='900';
                                                                        	comp='<img src="../images/exclamation.png" title="'+(registro.data.motivoNoCobro)+'" alt="'+(registro.data.motivoNoCobro)+'"> ';
                                                                        }
                                                                        comp+='<span style="color:#'+color+'">'+mostrarValorDescripcion(val)+'</span>';
                                                                        return comp;
                                                                    
                                                                    }
                                                            
														},
														{
															header:'Precio neto',
															width:90,
                                                            css:'text-align:right;vertical-align:top;',
                                                            /*editor:	{
                                                            			xtype:'numberfield',
                                                                        allowDecimals:true,
                                                                        allowNegative:false
                                                            		},*/
															sortable:true,
															dataIndex:'costo',
                                                            renderer:function(val,meta,registro)
                                                            		{
                                                                    	var color='000';
                                                                        if(registro.data.cobrable=='0')
                                                                        {
                                                                        	color='900';
                                                                        }
                                                                    	meta.attr='style="height:18px;vertical-align:bottom;"';
                                                                        return '<span style="color:#'+color+'">'+Ext.util.Format.usMoney(val)+'</span>';
                                                                    }
														},
                                                        {
															header:'Descuento',
															width:90,
                                                            css:'text-align:right;vertical-align:top;',
															sortable:true,
															dataIndex:'descuento',
                                                            renderer:function(val,meta,registro)
                                                            		{
                                                                    	meta.attr='style="height:18px;vertical-align:bottom;"';
                                                                        var color='000';
                                                                        var comp='';
                                                                        if(registro.data.fechaLimiteDescuento!='')
                                                                        {
                                                                        	var fLimite=Date.parseDate(registro.data.fechaLimiteDescuento,'Y-m-d');
                                                                        	comp=', Aplicable hasta el '+fLimite.format('d/m/Y');
                                                                            if(fechaActual>fLimite)
                                                                            {
                                                                                color='AAA';
                                                                            }
																		}
                                                                       
                                                                         if(registro.data.cobrable=='0')
                                                                         {
                                                                         	color='900';
                                                                         }
                                                                         
                                                                         
                                                                    	if((parseFloat(val)>0)&&(registro.data.descDescuento!=''))
                                                                        {
                                                                        	return '<img src="../images/icon_comment.gif" width="16" height="16" title="'+cv((registro.data.descDescuento+comp),false,true)+
                                                                            '" alt="'+cv((registro.data.descDescuento+comp),false,true)+'"> <span style="color:#'+color+'">'+Ext.util.Format.usMoney(val)+'</span>';
                                                                        }
                                                                        else
                                                                        	return ''+Ext.util.Format.usMoney(val)+'';
                                                                    
                                                                    }
														},
                                                        {
															header:'Total',
															width:90,
                                                            css:'text-align:right;vertical-align:top;',
															sortable:true,
															dataIndex:'costoFinal',
                                                            renderer:function(val,meta,registro)
                                                            		{
                                                                    	var color='000';
                                                                        if(registro.data.cobrable=='0')
                                                                        {
                                                                        	color='900';
                                                                        }
                                                                    	meta.attr='style="height:18px;vertical-align:bottom;"';
                                                                        return '<span style="color:#'+color+'">'+Ext.util.Format.usMoney(val)+'</span>';
                                                                    	
                                                                    }
														},
                                                        {
                                                        	header:'Ciclo',
															width:65,
															sortable:true,
															dataIndex:'idCiclo',
                                                            editor:cmbCiclo,
                                                            renderer:function(val,meta,registro)
                                                            		{
                                                                    	var color='000';
                                                                        if(registro.data.cobrable=='0')
                                                                        {
                                                                        	color='900';
                                                                        }
                                                                    	meta.attr='style="height:18px;vertical-align:bottom;"';
                                                                    	return '<span style="color:#'+color+'">'+formatearValorRenderer(arrCiclos,val)+'</span>';
                                                                    }
                                                        },
                                                        {
                                                        	header:'Periodo',
															width:100,
															sortable:true,
															dataIndex:'idPeriodo',
                                                            editor:cmbPeriodo,
                                                            renderer:function(val,meta,registro)
                                                            		{
                                                                    	var color='000';
                                                                        if(registro.data.cobrable=='0')
                                                                        {
                                                                        	color='900';
                                                                        }
                                                                    	meta.attr='style="height:18px;vertical-align:bottom;"';
                                                                    	return '<span style="color:#'+color+'">'+mostrarValorDescripcion(formatearValorRenderer(arrPeriodos,val))+'</span>';
                                                                    }
                                                        },
                                                        {
                                                        	header:'Mes',
															width:90,
															sortable:true,
															dataIndex:'idMes',
                                                            editor:cmbMes,
                                                            renderer:function(val,meta,registro)
                                                            		{
                                                                    	var color='000';
                                                                        if(registro.data.cobrable=='0')
                                                                        {
                                                                        	color='900';
                                                                        }
                                                                    	meta.attr='style="height:18px;vertical-align:bottom;"';
                                                                    	return '<span style="color:#'+color+'">'+formatearValorRenderer(arrMes,val)+'</span>';
                                                                    }
                                                        },
                                                        {
															header:'Comentarios',
															width:350,
															sortable:true,
															dataIndex:'comentarios',
                                                            editor:{xtype:'textfield'},
                                                            renderer:function(val,meta,registro)
                                                            		{
                                                                    	var color='000';
                                                                        if(registro.data.cobrable=='0')
                                                                        {
                                                                        	color='900';
                                                                        }
                                                                    	meta.attr='style="height:18px;vertical-align:bottom;"';
                                                                        return '<span style="color:#'+color+'">'+mostrarValorDescripcion(val)+'</span>';
                                                                    }
														}
													]
												);
                                                
	var tblGrid=	new Ext.grid.EditorGridPanel	(
                                                        {
                                                            id:'gCobroServicios',
                                                            store:alDatos,
                                                            frame:false,
                                                            y:160,
                                                            cm: cModelo,
                                                            stripeRows :true,
                                                            loadMask:true,
                                                            stripeRows :true,
                                                            plugins:[checkColumn],
                                                            columnLines : true,
                                                            height:200,
                                                            width:900,
                                                            clicksToEdit:1
                                                           
                                                        }
                                                    );
	
    
    tblGrid.on('beforeedit',function(e)
    						{
                            	
                            	if(e.field=='idPeriodo')
                                {
                                	gEx('cmbPeriodo').getStore().loadData(aPeriodos);
                                }
                            }
    		)
    
    tblGrid.on('afteredit',function(e)
    						{
                            	if(e.field=='considerarCobro')
                                {
                                	if(e.record.data.cobrable=='0')
                                    {
                                    	e.record.data.considerarCobro=0;
                                        gEx('gCobroServicios').getView().refresh();
                                        
                                    }
                                }
                                
                                
                                if((e.field=='idCiclo')||(e.field=='idMes'))
                                {
                                    function funcAjax()
                                    {
                                        var resp=peticion_http.responseText;
                                        arrResp=resp.split('|');
                                        if(arrResp[0]=='1')
                                        {
                                        	var aRegistros=eval(arrResp[1]);
                                            
                                            e.record.data.iva=aRegistros[0][2];
                                            e.record.data.costo=aRegistros[0][3];
                                            e.record.data.pocentajeDescuento=aRegistros[0][9];
                                            e.record.data.descDescuento=aRegistros[0][10];
                                            e.record.data.fechaLimiteDescuento=aRegistros[0][14];
                                            e.record.data.cobrable=aRegistros[0][15];
                                            e.record.data.motivoNoCobro=aRegistros[0][16];
                                            
											                                        
                                            calcularDescuentoConceptos(e.record,true);
                                        }
                                        else
                                        {
                                            msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
                                        }
                                    }
                                    obtenerDatosWeb('../paginasFunciones/funcionesModulosEspeciales_iep.php',funcAjax, 'POST','funcion=4&iP='+e.record.data.idPeriodo+'&iA='+idClienteBusqueda+'&iC='+
                                    e.record.data.idCiclo+'&iM='+e.record.data.idMes+'&c='+e.record.data.idConceptoCobro,true);
                                }
                                
                                
                            	
                            	/*if(((e.field=='idCiclo')||(e.field=='idMes'))&&(e.record.data.idConceptoCobro=='14'))
                                {
                                	calcularDescuentoConceptos(e.record,true);
                                }*/
                            }
    		)
    
 
                                                        
	return 	tblGrid;		
}


function calcularDescuentoConceptos(fila,refrescar)
{
	var pocentajeDescuento=parseFloat(fila.data.pocentajeDescuento);
    var precioNeto=parseFloat(fila.data.costo);
    var fechaLimite=null;
    
    if(fila.data.fechaLimiteDescuento!='')
    	fechaLimite=Date.parseDate(fila.data.fechaLimiteDescuento,'Y-m-d');
    var aplicaDescuento=true;
    
    if((fechaLimite!=null)&&(fechaActual>fechaLimite))
    {
    	aplicaDescuento=false;
    }
    
    
    var descuento=0;
    var total=precioNeto;
	switch(fila.data.idConceptoCobro)
    {
        case '14':
        	if(pocentajeDescuento>0)
            {
				descuento=precioNeto*(pocentajeDescuento/100);
            	if(aplicaDescuento)
                {
					total=precioNeto-descuento;
                }
            }
            
            fila.set('descuento',descuento);
            fila.set('costoFinal',total);
            
           
            
        break;
    }
    
     if(refrescar)
    {
        gEx('gCobroServicios').getView().refresh();
    
    }
}

function buscarDatosAlumno(iR,v)
{
	idClienteBusqueda=iR;
	function funcAjax()
    {
        var resp=peticion_http.responseText;
        arrResp=resp.split('|');
        if(arrResp[0]=='1')
        {
        	var obj=eval(arrResp[1])[0];
            gEx('txtApPaterno').setValue(obj.apPaterno);
            gEx('txtApMaterno').setValue(obj.apMaterno);
            gEx('txtNombre').setValue(obj.nombre);
            gEx('txtPlanEstudio').setValue(obj.planEstudio);
            gEx('txtGrado').setValue(obj.grado);
            gEx('txtGrupo').setValue(obj.grupo);
            
            aPeriodos=[];
            var x;

			for(x=0;x<arrPeriodos.length;x++)
            {
            	if(arrPeriodos[x][2]==obj.periodicidad)
            		aPeriodos.push(arrPeriodos[x]);
            }
            
            

            
            gEx('gCobroServicios').getStore().loadData(obj.arrConceptosCobro);
            
            var fila;
            for(x=0;x<gEx('gCobroServicios').getStore().getCount();x++)
            {
            	fila=gEx('gCobroServicios').getStore().getAt(x);
                calcularDescuentoConceptos(fila);
                
            }
         	v.show();  
            
           
             
        }
        else
        {
            msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
        }
    }
    obtenerDatosWeb('../paginasFunciones/funcionesModulosProcesos.php',funcAjax, 'POST','funcion=32&idRegistro='+iR,true);
    
}

function reajustarConceptos(tCliente,nombre,noAjustarPrecios)
{

}