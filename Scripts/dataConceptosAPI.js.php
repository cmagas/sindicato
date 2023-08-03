<?php
	session_start();
	include("configurarIdiomaJS.php");
	include("conexionBD.php");
	
	$lCategoriaExp="";
	$arrCategoriasExpresion="[['0,@totasCategorias','Todas']";
	$consulta="SELECT idCategoriaConcepto,nombreCategoria FROM 991_categoriasConcepto WHERE idCategoriaConcepto order by nombreCategoria";
	$resCategoria=$con->obtenerFilas($consulta);
	while($fCat=mysql_fetch_row($resCategoria))
	{
		$arrCategoriasExpresion.=",['".$fCat[0]."','".cv($fCat[1])."']";
		if($lCategoriaExp=="")
			$lCategoriaExp=$fCat[0];
		else
			$lCategoriaExp.=",".$fCat[0];
	}
	
	$arrCategoriasExpresion.="]";
	$arrCategoriasExpresion=str_replace("@totasCategorias",$lCategoriaExp,$arrCategoriasExpresion);
?>
var asignarFuncionNuevoConceptoInyeccion=null;
var arrCategoriasExpresion=<?php echo $arrCategoriasExpresion?>;

function mostrarVentanaExpresion(destino,pAgregar,objConf)
{
	var cmbCategoriaExpresion=crearComboExt('cmbCategoriaExpresion',arrCategoriasExpresion,160,10,350);
    cmbCategoriaExpresion.setValue(arrCategoriasExpresion[0][0]);
    if(objConf && objConf.idCategoriaDefault)
    {
    	cmbCategoriaExpresion.setValue(objConf.idCategoriaDefault);
    
    }
    cmbCategoriaExpresion.on('select',function(cmb,registro)
    									{
                                        	gEx('gExpresiones').getStore().load({params:{start:0, limit:15,funcion:146,idCategoria:registro.get('id')}});
                                        }
    						)
	var gridExpresiones=crearGridExpresiones(pAgregar,destino,objConf);
	var form = new Ext.form.FormPanel(	
										{
											baseCls: 'x-plain',
											layout:'absolute',
											defaultType: 'label',
											items: 	[
                                            			{
                                                        	x:10,
                                                            y:15,
                                                        	html:'Categor&iacute;a de la expresi&oacute;n: '
                                                        },
                                            			cmbCategoriaExpresion,
                                            			{
                                                        	x:10,
                                                            y:50,
                                                        	html:'Seleccione la expresi&oacute;n que desea agregar: '
                                                        },
														gridExpresiones

													]
										}
									);
	
	var ventanaAM = new Ext.Window(
									{
                                    	id:'vAgregarExp',
										title: 'Agregar expresi&oacute;n',
										width: 900,
										height:520,
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
                                                                    	var filas=gridExpresiones.getSelectionModel().getSelected();
                                                                    	if(filas==null)
                                                                        {
                                                                        	msgBox('Debe seleccionar la expresi&oacute;n a agregar');
                                                                        	return;
                                                                        }
                                                                        
                                                                        if(typeof(destino)=='function')
                                                                        {
                                                                        	destino(filas,ventanaAM);
                                                                            return;
                                                                        } 
                                                                        var idConsulta=filas.get('idConsulta');
                                                                        
                                                                        var iControl=bD(destino);
                                                                        gE(iControl).value=idConsulta;
                                                                        var lblControl=gE('lbl'+iControl);
                                                                        if(lblControl!=null)
                                                                        {
                                                                        	lblControl.innerHTML=filas.get('nombreConsulta');
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
	//llenarGridExpresiones(ventanaAM,gridExpresiones);
}

function crearGridExpresiones(pAgregar,destino,objConfGlobal)
{
	var dsDatos=[];
    
    var dsTablaRegistros = new Ext.data.JsonStore	(

																			{

																				root: 'registros',

																				totalProperty: 'numReg',

																				idProperty: 'idConsulta',

																				fields: 	[
                                                                                              {name: 'idConsulta'},
                                                                                              {name: 'nombreConsulta'},
                                                                                              {name: 'nombreCategoria'},
                                                                                              {name: 'descripcion'},
                                                                                              {name: 'valorRetorno'},
                                                                                              {name: 'parametros'}
                                                                                          ],

																				remoteSort:true,

																				proxy: new Ext.data.HttpProxy	(

																													{

																														url: '../paginasFunciones/funcionesProyectos.php'

																													}

																												)
                                                                              }
                                                    )

    function cargarDatos(proxy,parametros)
    {
        proxy.baseParams.funcion=146;
        proxy.baseParams.idCategoria=gEx('cmbCategoriaExpresion').getValue()
    }                                      

																	

	dsTablaRegistros.on('beforeload',cargarDatos); 
    
    
     var expander = new Ext.ux.grid.RowExpander({
                                                    column:2,
                                                    tpl : new Ext.Template(
                                                                                '<br><table >'+
                                                                                '<tr><td width:"230"><span class="letraRojaSubrayada8"><b>Descripci&oacute;n:</b></span></td><td></td></tr><tr><td></td><td><span class="copyrigthSinPadding">{descripcion}</span><br /><br /></td></tr>'+
                                                                                '</table>'
                                                                            )
    											}
                                               )
    var tamPagina =	15;     

																							

    var paginador=	new Ext.PagingToolbar	(

                                                {

                                                      pageSize: tamPagina,

                                                      store: dsTablaRegistros,

                                                      displayInfo: true,

                                                      disabled:false

                                                  }

                                               )   
	var filters = new Ext.ux.grid.GridFilters	(

    												{

                                                    	filters:	[ {type: 'string', dataIndex: 'nombreConsulta'}, {type: 'string', dataIndex: 'nombreCategoria'}]

                                                    }

                                                );                                                       
	var chkRow=new Ext.grid.CheckboxSelectionModel({singleSelect:true});
	var cModelo= new Ext.grid.ColumnModel   	(
												 	[
													 	new  Ext.grid.RowNumberer(),
                                                        expander,
														chkRow,
                                                        {
															header:'ID Expresi&oacute;n',
															width:100,
															sortable:true,
															dataIndex:'idConsulta'
														},
														{
															header:'Expresi&oacute;n',
															width:320,
															sortable:true,
															dataIndex:'nombreConsulta'
														},
														{
															header:'Tipo expresion',
															width:270,
															sortable:true,
															dataIndex:'nombreCategoria',
                                                            renderer:function(val)
                                                            			{
                                                                        	return val;
                                                                        }
														}
													]
												);

	var arrBotones=[];
    if(pAgregar)                                            
    {
    	arrBotones.push	(
        					{
                                icon:'../images/add.png',
                                cls:'x-btn-text-icon',
                                text:'Registrar nuevo concepto',
                                handler:function()
                                        {
                                            
                                            var objConf={};
                                            objConf.titulo='Registrar nuevo concepto';
                                            objConf.url='../nomina/conceptosNomina.php';
                                            objConf.ancho='100%';
                                            objConf.alto='100%';
                                            objConf.scrolling='yes';
                                            var destAux=destino;
                                            if(typeof(destino)=='function')
                                            	destAux='';
                                                
                                            var funcionGuardar='';    
                                            if(objConfGlobal && (objConfGlobal.funcionAgregarCalculo) && (objConfGlobal.funcionAgregarCalculo))
                                            {
                                            	funcionGuardar=bE('window.parent.'+objConfGlobal.funcionAgregarCalculo+'(\''+destAux+'\',@idRegistro,\'@nConsulta\')');
                                            }
                                            else
                                            	funcionGuardar=bE('window.parent.asignarFuncionNuevo(\''+destAux+'\',@idRegistro,\'@nConsulta\')');
                                            objConf.params=[['categoria',gEx('cmbCategoriaExpresion').getValue()],['idConsulta','-1'],['cPagina','sFrm=true'],['funcionGuardar',funcionGuardar],['mCerrar','1']];
                                            abrirVentanaFancy(objConf);
                                            
                                        }
                                
                            },'-',
                            {
                                icon:'../images/pencil.png',
                                cls:'x-btn-text-icon',
                                text:'Modificar concepto',
                                handler:function()
                                        {
                                            var fila=gEx('gExpresiones').getSelectionModel().getSelected();
                                            if(fila==null)
                                            {
                                            	msgBox('Debe seleccionar el concepto que desea modificar');
                                                return;
                                            }
                                            
                                            var objConf={};
                                            objConf.titulo='Registrar nuevo concepto';
                                            objConf.url='../nomina/conceptosNomina.php';
                                            objConf.ancho='100%';
                                            objConf.alto='100%';
                                            var destAux=destino;
                                            if(typeof(destino)=='function')
                                            	destAux='';
                                                
                                                
                                            var funcionGuardar=bE('window.parent.asignarFuncionNuevo(\''+destAux+'\',@idRegistro,\'@nConsulta\')');
                                            
                                            objConf.params=[['categoria',gEx('cmbCategoriaExpresion').getValue()],['idConsulta',fila.get('idConsulta')],['cPagina','sFrm=true'],['funcionGuardar',funcionGuardar],['mCerrar','1']];
                                            abrirVentanaFancy(objConf);
                                            
                                        }
                                
                            }
                         );
    }    
	var tblGrid=	new Ext.grid.EditorGridPanel	(
                                                        {
                                                            id:'gExpresiones',
                                                            store:dsTablaRegistros,
                                                            frame:false,
                                                            y:70,
                                                            cm: cModelo,
                                                            height:365,
                                                            width:850,
                                                            sm:chkRow,
                                                            loadMask:true,
                                                            stripeRows :true,
                                                            columnLines : true,
                                                            bbar: paginador,
                                                            tbar:arrBotones,
                                                            plugins:[filters,expander]

                                                        }
                                                    );
	dsTablaRegistros.load({params:{start:0, limit:tamPagina,funcion:146}});                                                    
	return 	tblGrid;
}

function llenarGridExpresiones(ventanaAM,grid)
{
	function funcAjax()
    {
        var resp=peticion_http.responseText;
        arrResp=resp.split('|');
        if(arrResp[0]=='1')
        {
        	grid.getStore().loadData(eval(arrResp[1]));
            ventanaAM.show();
        }
        else
        {
            msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
        }
    }
    obtenerDatosWeb('../paginasFunciones/funcionesProyectos.php',funcAjax, 'POST','funcion=146',true);
}

function removerConcepto(idControl)
{
	var iControl=bD(idControl);
    gE(iControl).value=-1;
    gE('lbl'+iControl).innerHTML='(No especificada)';
    
}

function asignarFuncionNuevo(destino,iR,lR)
{
	if(asignarFuncionNuevoConceptoInyeccion)
    {
    	asignarFuncionNuevoConceptoInyeccion(iR,lR);
    }
    else
    {
        var iControl=bD(destino);
        gE(iControl).value=iR;
        gE('lbl'+iControl).innerHTML=lR;
        var ventana= gEx('vAgregarExp');
        if(ventana)
	        ventana.close();
    }
}