<?php session_start();
	include("configurarIdiomaJS.php");
	include("conexionBD.php");
	$arrModulosProceso="[]";
	
	$consulta="select idValorSesion,descripcionValor,valorReemplazo from 8003_valoresSesion where tipo=1 order by descripcionValor ";
	$arrValorSesion=uEJ($con->obtenerFilasArreglo($consulta));
	$consulta="select idValorSesion,descripcionValor,valorReemplazo from 8003_valoresSesion where tipo=2 order by descripcionValor ";
	$arrValorSistema=uEJ($con->obtenerFilasArreglo($consulta));
	
	$arrConexiones="['0','Local']";
	$consulta="SELECT DISTINCT c.idConexion,nombreConexion FROM 251_conexionesSistema c,252_rolesVSConexiones r WHERE r.idConexion=c.idConexion AND idRol IN (".$_SESSION["idRol"].") ORDER BY nombreConexion";
	$res=$con->obtenerFilas($consulta);
	while($fila=mysql_fetch_row($res))
	{
		$arrConexiones.=",['".$fila[0]."','".$fila[1]."']";
	}
?>
var arrParametrosObjeto=[];
var idConexionAlmacen=0;
var arrConexiones=[<?php echo $arrConexiones?>];
var idArbolDataSet;
var arbolAlmacenEstructDatos;
var permitirRegistroParametro=true;
var arrValorSesion=<?php echo $arrValorSesion ?>;
var arrValorSistema=<?php echo $arrValorSistema ?>;  
var filtroUsuario=new Array();
var filtroMysql=new Array(); 
var filtroTipoValor;
var arrProceso=[['1','idFormulario'],['2','idProceso'],['3','idRegistro'],['4','numEtapa'],['5','idReferencia'],['6','Usuario registro']];
var arrOperaciones=[['6','Contabilizar elementos'],['1','Promedio'],['2','Proyecci\xF3n'],['7','Ra\xEDz cuadrada'],['3','Sumatoria'],['4','Valor m\xE1ximo'],['5','Valor m\xEDnimo']];
var arrParametrosAlmacen;
var idReferencia=-1;
var tDataSet=3;
var arrParametrosAlmacen=[];

function crearArbolAlmacen(conf)
{

	if(conf==undefined)
    	conf={};
	
    var lblConcepto='el nuevo c&aacute;lculo';
    
    if(gE('idReferencia')!=null)
	    idReferencia=bD(gE('idReferencia').value);
    if(conf.idReferencia)
    	idReferencia=conf.idReferencia;
    if(conf.tDataSet)
    	tDataSet=conf.tDataSet;
    
    if(conf.tituloConcepto)
    	lblConcepto=conf.tituloConcepto;
    if((conf.permitirRegistroParametro!=undefined)	&&(conf.permitirRegistroParametro==false))
    	permitirRegistroParametro=false;
    var cargadorArbol=new Ext.tree.TreeLoader(
												{
													baseParams:{
																	funcion:'7',
                                                                    idReporte:idReferencia,
                                                                    tipoDataSet:tDataSet
																},
													dataUrl:'../paginasFunciones/funcionesThot.php'
												}
											)	

	cargadorArbol.on('load',function(a,b,c)
    						{
                            	if(conf.funcLoad)
                                	conf.funcLoad(a,b,c);
                            }
    				)
   
   	cargadorArbol.on('beforeload',function(a,b,c)
                                {
                                    if(conf.funcBeforeLoad)
	                                	conf.funcBeforeLoad(a,b,c);
                                    
                                        
                                    a.baseParams.mAlmacenDatos=1;    
                                    a.baseParams.mConsultaAux=1;    
                                    
                                    if((conf.mAlmacenDatos)&&(conf.mAlmacenDatos=='0'))
                                    	a.baseParams.mAlmacenDatos=0;   
                                        
                                    if((conf.mConsultaAux)&&(conf.mConsultaAux=='0'))
                                    	a.baseParams.mConsultaAux=0;       
                                        
                                }
    				) 				
    var raiz=new  Ext.tree.AsyncTreeNode	(
                                                  {
                                                      id:'-1',
                                                      text:'DTD',
                                                      draggable:false,
                                                      expanded :true
                                                  }
                                            )

	var ancho=300;
    if(conf.ancho)
    	ancho=conf.ancho;
    var alto=600;
    if(conf.alto)
    	alto=conf.alto;


	idArbolDataSet='arbolDataSet';
    if(conf.idArbol)
    	idArbolDataSet=conf.idArbol;
        
	var objConf=	{
                        id:idArbolDataSet,
                        useArrows:true,
                        autoScroll:true,
                        animate:false,
                        enableDD:true,
                        containerScroll:true,
                        root:raiz,
                        rootVisible:false,
                        loader: cargadorArbol,
                        tbar:	[
                                  {
                                      id:'addDataSet',
                                      icon:'../images/database_add.png',
                                      cls:'x-btn-text-icon',
                                      tooltip:'Crear almac&eacute;n de datos',
                                      disabled:true,
                                      handler:function()
                                              {
                                                  if(idReferencia=='-1')
                                                  {
                                                      msgBox('Antes de crear un almac&eacute;n de datos, primero debe guardar '+lblConcepto);
                                                      return;
                                                  }
                                                  
                                                  switch(nodoSel.attributes.tipo)
                                                  {
                                                      case 'ad':
                                                          mostrarVentanaTablasInvolucradas(true);
                                                      break;
                                                      case 'ca':
                                                          mostrarVentanaTablasInvolucradas(false);
                                                      break;
                                                      case 'ag':
                                                          mostrarVentanaNuevoAlmacenGrafico();
                                                      break;
                                                  }
                                                  
                                                 
                                              }
                                  },
                                  {
                                      id:'delDataSet',
                                      icon:'../images/database_delete.png',
                                      cls:'x-btn-text-icon',
                                      disabled:true,
                                      tooltip:'Eliminar almac&eacute;n de datos',
                                      handler:function()
                                              {
                                                  if(nodoSel==null)
                                                  {
                                                      msgBox('Debe seleccionar el almac&eacute;n de datos a eliminar');
                                                      return;
                                                  }
                                                  function resp(btn)
                                                  {
                                                      if(btn=='yes')
                                                      {
                                                          function funcAjax()
                                                          {
                                                              var resp=peticion_http.responseText;
                                                              arrResp=resp.split('|');
                                                              if(arrResp[0]=='1')
                                                              {
                                                                  nodoSel.remove();
                                                                  nodoSel=null;
                                                              }
                                                              else
                                                              {
                                                                  msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
                                                              }
                                                          }
                                                          obtenerDatosWeb('../paginasFunciones/funcionesThot.php',funcAjax, 'POST','funcion=12&idAlmacen='+nodoSel.id,true);
    
                                                      }
                                                  }
                                                  msgConfirm('Est&aacute; seguro de querer eliminar el almac&eacute;n de datos seleccionado?',resp)
                                              }
                                  },
                                  {
                                      id:'linkDataSet',
                                      icon:'../images/database_edit.png',
                                      cls:'x-btn-text-icon',
                                      tooltip:'Modificar origen de datos',
                                      disabled:true,
                                      handler:function()
                                              {
                                                    switch(nodoSel.attributes.categoria)
                                                    {
                                                        case '2':
                                                            mostrarVentanaDatos(nodoSel.id);
                                                        break;
                                                        default:
                                                            mostrarVentanaTablasInvolucradasModif(false);
                                                        break;
                                                    }
                                                  
                                              }
                                  },'-',
                                  {
                                      id:'modificarNombreAlmacen',
                                      icon:'../images/tag_blue_edit.png',
                                      cls:'x-btn-text-icon',
                                      tooltip:'Modificar nombre del almac&eacute;n',
                                      hidden:true,
                                      handler:function()
                                              {
                                                  mostrarVentanaModificarNombre();
                                              }
                                  },   
                                  {
                                      id:'modificarFiltroAlmacen',
                                      icon:'../images/pencil.png',
                                      cls:'x-btn-text-icon',
                                      tooltip:'Modificar filtro de almac&eacute;n',
                                      hidden:true,
                                      handler:function()
                                              {
                                              		mostrarVentanaCondFiltro();
                                                  //mostrarVentanaModifFiltro();
                                              }
                                  },                                                            
                                  {
                                      id:'modficarValorParametro',
                                      icon:'../images/building_edit.png',
                                      cls:'x-btn-text-icon',
                                      tooltip:'Modificar valor de par&aacute;metro',
                                      hidden:true,
                                      handler:function()
                                              {
                                                  mostrarVentanaAsignarParam();
                                              }
                                  },
                                  {
                                      id:'addCamposProy',
                                      icon:'../images/add.png',
                                      cls:'x-btn-text-icon',
                                      tooltip:'Agregar campos a proyectar',
                                      hidden:true,
                                      handler:function()
                                              {
                                                  agregarCampoProy();
                                              }
                                  },
                                  {
                                      id:'delCamposProy',
                                      icon:'../images/delete.png',
                                      cls:'x-btn-text-icon',
                                      tooltip:'Remover campo a proyectar',
                                      hidden:true,
                                      handler:function()
                                              {
                                                  function resp(btn)   
                                                  {
                                                      if(btn=='yes')
                                                      {
                                                          function funcAjax()
                                                          {
                                                              var resp=peticion_http.responseText;
                                                              arrResp=resp.split('|');
                                                              if(arrResp[0]=='1')
                                                              {
                                                                  nodoSel.remove();
                                                                  nodoSel=null;
                                                              }
                                                              else
                                                              {
                                                                  msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
                                                              }
                                                          }
                                                          obtenerDatosWeb('../paginasFunciones/funcionesThot.php',funcAjax, 'POST','funcion=19&nCampo='+nodoSel.attributes.nCampo+'&idDataSet='+nodoSel.attributes.dSetPadre,true);
    
                                                      }
                                                  }
                                                  msgConfirm('Est&aacute; seguro de querer remover el campo seleccionado?',resp)
                                              }
                                  }
                              ]
                                                                      
                     }
	
    
    if(!conf.anchor)
    {
    	objConf.height=alto;
        objConf.width=ancho;
       
    }
    else
    {
    	objConf.anchor=conf.anchor;
    }
    
    if(conf.region)
    	objConf.region=conf.region;
    
    
    if((conf!=undefined)&&(conf.renderTo!=undefined))
    {
    	objConf.renderTo=conf.renderTo;
    }
    
    if(conf.collapsible)
    	objConf.collapsible=conf.collapsible;
        
    if(conf.title)
    	objConf.title=conf.title;
        
	panelArbol=new Ext.tree.TreePanel	(objConf);      
    panelArbol.on('click',funcClickArbol);
    arbolAlmacenEstructDatos=panelArbol;
    return panelArbol;
}

function funcClickArbol(nodo)
{
	nodoSel=nodo;
    gEx('addDataSet').disable();
    gEx('delDataSet').disable();
    gEx('linkDataSet').disable();
    gEx('modficarValorParametro').hide();
    gEx('addCamposProy').hide();
    gEx('delCamposProy').hide();
    gEx('modificarFiltroAlmacen').hide();
    gEx('modificarNombreAlmacen').hide();
    
    switch(nodoSel.attributes.tipo)
    {
    	
    	case 'p':
        	gEx('modficarValorParametro').show();
        break;
        case 'cc':
        	gEx('addCamposProy').show();
        break;
        case 'c':
            gEx('delCamposProy').show();
        break;
        case 't':
        	if(nodoSel.attributes.categoria!='2')
	        	gEx('modificarFiltroAlmacen').show();
            gEx('delDataSet').enable();
            gEx('linkDataSet').enable();
            gEx('modificarNombreAlmacen').show();
        break;
        case 'ad':
        	gEx('addDataSet').enable();
        break;
        case 'ca':
        	gEx('addDataSet').enable();
        break;
        case 'ct':
        	
        break;
        case 'ag':
        	gEx('addDataSet').enable();
        break;
    }
}

var regTabla=crearRegistro(
                                [
                                  {name:'nomTablaOriginal'},
                                  {name:'tabla'}, 
                                  {name:'tipoTabla'},
                                  {name:'proceso'}
                              ]
                          );

function mostrarVentanaTablasInvolucradas(ocultarProy)
{
	var cmbConexion=crearComboExt('cmbConexion',arrConexiones,110,5,250);
    cmbConexion.setValue('0');
    idConexionAlmacen=0;
    cmbConexion.on('select',function(cmb,registro)
    						{
                            	idConexionAlmacen=registro.get('id');
                            }
    				)
    
    
	var gridTablasInv= crearGridConsiderarTablas(ocultarProy,false);
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
                                                            html:'Conexi&oacute;n:'
                                                        },
                                                        cmbConexion,
														gridTablasInv
													]
										}
									);
	
    
     btnSiguiente=new Ext.Button	(
                                        {
                                            text: 'Siguiente >>',
                                            minWidth:80,
                                            id:'btnFinalizar',
                                            listeners:	{
                                                            click:
                                                                    {
                                                                        fn:function()
                                                                        {
                                                                            if(gridTablasInv.getStore().getCount()==0)
                                                                            {
                                                                                Ext.MessageBox.alert(lblAplicacion,'Debe almenos selecionar una tabla en la cual se basar&aacute; su almac&eacute;n');
                                                                                return;
                                                                            }
                                                                            var x;
                                                                            var nomTabla='';
                                                                            var nTablaUsr='';
                                                                            var tTabla='';
                                                                            var filaSel;
                                                                            for(x=0;x<gridTablasInv.getStore().getCount();x++)
                                                                            {
                                                                            	filaSel=gridTablasInv.getStore().getAt(x);
                                                                            	if(nomTabla=='')
                                                                                {
                                                                                	nomTabla=filaSel.get('nomTablaOriginal');
                                                                                    nTablaUsr=filaSel.get('tabla');
                                                                                    if(filaSel.get('tipoTabla')=='Sistema')
                                                                                        tTabla='0';
                                                                                    else
                                                                                        tTabla='1';
                                                                                }
                                                                               	else
                                                                                {
                                                                                	nomTabla+=','+filaSel.get('nomTablaOriginal');
                                                                                    nTablaUsr+=','+filaSel.get('tabla');
                                                                                    if(filaSel.get('tipoTabla')=='Sistema')
                                                                                        tTabla+=',0';
                                                                                    else
                                                                                        tTabla+=',1';
                                                                                }
                                                                            }
                                                                            if(typeof(ventanaSelTabla)!='undefined')
		                                                                        ventanaSelTabla.close();
                                                                            var obj={};
                                                                            obj.nTablaO=nomTabla;
                                                                            obj.nTablaUsr=nTablaUsr;
                                                                            obj.tipoTabla=tTabla;
                                                                            
                                                                            if(ocultarProy==undefined)
                                                                                mostrarVentanaFiltro(obj,false);
                                                                            else
                                                                                mostrarVentanaFiltro(obj,ocultarProy);
                                                                            ventanaAM.close();
                                                                        }
                                                                    }
                                                        }
                                        }
                                    )
    
	var ventanaAM = new Ext.Window(
									{
										title: 'Tabla involucadas en el almac&eacute;n',
										width: 700,
										height:370,
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
														btnSiguiente,
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

function crearGridConsiderarTablas(ocultarProy,validarDelete)
{
	var dsDatos=[];
    var alDatos=	new Ext.data.SimpleStore	(
                                                    {
                                                        fields:	[
                                                                    {name: 'nomTablaOriginal'},
                                                                    {name: 'tabla'},
                                                                    {name:'tipoTabla'},
	                                                                {name:'proceso'}
                                                                ]
                                                    }
                                                );

    alDatos.loadData(dsDatos);
	var chkRow=new Ext.grid.CheckboxSelectionModel({singleSelect:true});
	var cModelo= new Ext.grid.ColumnModel   	(
												 	[
														 new  Ext.grid.RowNumberer({width:35}),
                                                        {
                                                            header:'Tabla',
                                                            width:250,
                                                            dataIndex:'tabla',
                                                            sortable:true
                                                        },
                                                        {
                                                        	header:'Tipo',
                                                            width:130,
                                                            sortable:true,
                                                            dataIndex:'tipoTabla'
                                                        },
                                                        {
                                                        	header:'Proceso',
                                                            width:180,
                                                            sortable:true,
                                                            dataIndex:'proceso'
                                                        }													
                                                    ]
												);
                                                
	var tblGrid=	new Ext.grid.EditorGridPanel	(
                                                        {
                                                        	id:'gridConsidera',
                                                            x:10,
                                                            store:alDatos,
                                                            frame:true,
                                                            y:40,
                                                            cm: cModelo,
                                                            height:240,
                                                            width:650,
                                                            loadMask:true,
                                                            sm:chkRow,
                                                            tbar:	[
                                                            			{
                                                                        	icon:'../images/add.png',
                                                                            cls:'x-btn-text-icon',
                                                                        	text:'Agregar tabla',
                                                                            handler:function()
                                                                            		{
                                                                                    	mostrarVentanaSelTabla(ocultarProy);
                                                                                    }
                                                                        },'-',
                                                                        {
                                                                        	
                                                                        	icon:'../images/delete.png',
                                                                            cls:'x-btn-text-icon',
                                                                        	text:'Remover tabla',
                                                                            handler:function()
                                                                            		{
                                                                                      	var idReporte=gE('idReporte').value;
                                                                                    	var filaSel= tblGrid.getSelectionModel().getSelected();
                                                                                        if(filaSel==null)
                                                                                        {
                                                                                            Ext.MessageBox.alert(lblAplicacion,'Debe selecionar la tabla en la cual se basar&aacute; su consulta');
                                                                                            return;
                                                                                        }
                                                                                        
                                                                                        if(!validarDelete)
                                                                                        {
                                                                                        
                                                                                            function resp(btn)
                                                                                            {
                                                                                                if(btn=='yes')
                                                                                                {
                                                                                                    tblGrid.getStore().remove(filaSel);
                                                                                                }
                                                                                            }
                                                                                            msgConfirm('Est&aacute; seguro de querer remover la tabla seleccionada?',resp);
                                                                                    	}
                                                                                        else
                                                                                        {
                                                                                        	function resp2(btn)
                                                                                            {
                                                                                                if(btn=='yes')
                                                                                                {
                                                                                                	function funcAjax()
                                                                                                    {
                                                                                                        var resp=peticion_http.responseText;
                                                                                                        arrResp=resp.split('|');
                                                                                                        if(arrResp[0]=='1')
                                                                                                        {
                                                                                                        	recargarPagina();
                                                                                                        }
                                                                                                        else
                                                                                                        {
                                                                                                            msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
                                                                                                        }
                                                                                                    }
                                                                                                    obtenerDatosWeb('../paginasFunciones/funcionesThot.php',funcAjax, 'POST','funcion=24&tDataSet=2&idReporte='+idReporte+'&idAlmacen='+nodoSel.id+'&nTabla='+filaSel.get('nomTablaOriginal'),true);
                                                                                                }
                                                                                            }
                                                                                            msgConfirm('Al remover la tabla seleccionada se eliminar&aacute; toda informaci&oacute;n relacionada con dicha tabla dentro del almac&eacute;n (referencias en etiquetas, en almac&eacute;, etc..), desea continuar?',resp2);
                                                                                        
                                                                                        }
                                                                                    }
                                                                        }
                                                            		]
                                                        }
                                                    );
	tblGrid.getStore().on('add',function(almacen,registro)
                            {
                                if(almacen.getCount()>0)
                                {
                                    gEx('cmbConexion').disable();
                                }
                            }
    			) 
	
    tblGrid.getStore().on('remove',function(almacen,registro)
                            {
                                if(almacen.getCount()==0)
                                {
                                    gEx('cmbConexion').enable();
                                }
                            }
    			)                                                                    
	return 	tblGrid;		
}

function mostrarVentanaSelTabla(ocultarProy)
{
    arrParametrosAlmacen=new Array();
	var alDatos = new Ext.data.JsonStore	(
                                                {
                                                    root: 'registros',
                                                    totalProperty: 'numReg',
                                                    idProperty: 'nomTablaOriginal',
                                                    fields:	[
                                                                {name:'nomTablaOriginal'},
                                                                {name:'tabla'}, 
                                                                {name:'tipoTabla'},
                                                                {name:'proceso'}
                                                            ],
                                                    remoteSort:false,
                                                    proxy: new Ext.data.HttpProxy	(
                                                                                        {
                                                                                            url: '../paginasFunciones/funcionesFormulario.php',
                                                                                           	
                                                                                            
                                                                                        }
                                                                                    )
                                                }
                                            );  
	                                            
                                      
	var filters = new Ext.ux.grid.GridFilters	(
    												{
                                                    	filters:	[
                                                        				{
                                                                            type:'string',
                                                                           	dataIndex:'tabla' 
																		},
                                                                        {
                                                                            type:'list',
                                                                           	dataIndex:'tipoTabla',
                                                                            phpMode:true,
                                                                            options:	[
                                                                            				{
                                                                                            	id:'1',
                                                                                                text:'Formulario Din&aacute;mico'
                                                                                            },
                                                                            				{
                                                                                            	id:'2',
                                                                                                text:'Sistema'
                                                                                            }
                                                                            			] 
																		},
                                                                        {
                                                                            type:'string',
                                                                           	dataIndex:'proceso' 
																		}
                                                        			]
                                                    }
                                                );                                                                                                                           
   
   
    var cmFrmDTD= new Ext.grid.ColumnModel   	(
                                                    [
                                                        new  Ext.grid.RowNumberer({width:35}),
                                                        {
                                                            header:'Tabla',
                                                            width:250,
                                                            dataIndex:'tabla',
                                                            sortable:true
                                                        },
                                                        {
                                                        	header:'Tipo',
                                                            width:130,
                                                            sortable:true,
                                                            dataIndex:'tipoTabla'
                                                        },
                                                        {
                                                        	header:'Proceso',
                                                            width:180,
                                                            sortable:true,
                                                            dataIndex:'proceso'
                                                        }
                                                       
                                                    ]
                                                );
    
    
    var tblOpciones=	new Ext.grid.GridPanel	(
                                                        {
                                                            id:'gridTabla',
                                                            store:alDatos,
                                                            frame:true,
                                                            loadMask:true,
                                                            cm: cmFrmDTD,
                                                            height:300,
                                                            width:630,
                                                            plugins: filters
                                                            
                                                        }
                                                    );
    tblOpciones.on('dblclick',function()
    							{
                                	gEx('btnAgregar').fireEvent('click');
                                }
    				);
    panelGrid=new Ext.Panel	(
                                {
                                    y:10,
                                    width:630,
                                    items:	[
                                                tblOpciones
                                            ]
                                }
                            );
    
    var form = new Ext.form.FormPanel(	
                                        {
                                            baseCls: 'x-plain',
                                            layout:'absolute',
                                            defaultType: 'textfield',
                                            items: 	[
                                                        panelGrid
                                                    ]
                                        }
                                    );
   
    btnAgregar=new Ext.Button	(
                                        {
                                            text: 'Agregar tabla',
                                            minWidth:80,
                                            id:'btnAgregar',
                                            listeners:	{
                                                            click:
                                                                    {
                                                                        fn:function()
                                                                        {
                                                                            
                                                                            var filaSel= tblOpciones.getSelectionModel().getSelected();
                                                                            if(filaSel==null)
                                                                            {
                                                                                Ext.MessageBox.alert(lblAplicacion,'Debe selecionar la tabla en la cual se basar&aacute; su consulta');
                                                                                return;
                                                                            }
                                                                            var nomTablaOriginal=filaSel.get('nomTablaOriginal');
                                                                            var almacenDestino=gEx('gridConsidera').getStore();

                                                                            if(obtenerPosFila(almacenDestino,'nomTablaOriginal',nomTablaOriginal)==-1)
                                                                            {
                                                                                var nFila=new regTabla  (
                                                                                                            {
                                                                                                                nomTablaOriginal:filaSel.get('nomTablaOriginal'),
                                                                                                                tabla:filaSel.get('tabla'),
                                                                                                                tipoTabla:filaSel.get('tipoTabla'),
                                                                                                                proceso:filaSel.get('proceso')
                                                                                                            }
                                                                                                        )
                                                                                almacenDestino.add(nFila);
                                                                            }
                                                                            ventanaSelTabla.close();
                                                                        }
                                                                    }
                                                        }
                                        }
                                    )
   
    
    ventanaSelTabla = new Ext.Window(
                                            {
                                                title: 'Seleccione la tabla en la cual se basar&aacute; su consulta',
                                                width: 660 ,
                                                height:390,
                                                minWidth: 300,
                                                minHeight: 100,
                                                layout: 'fit',
                                                plain:true,
                                                modal:true,
                                                bodyStyle:'padding:5px;',
                                                buttonAlign:'center',
                                                items: 	[
                                                            form
                                                        ],
                                                listeners : {
                                                            show : {
                                                                        buffer : 10,
                                                                        fn : function() 
                                                                        {
                                                          			                  
                                                                        }
                                                                    }
                                                        },
                                                buttons:	[
                                                                btnAgregar,
                                                                {
                                                                    text: 'Cancelar',
                                                                    handler:function()
                                                                    {
                                                                        ventanaSelTabla.close();
                                                                    }
                                                                }
                                                            ]
                                            }
                                        );
                                        
	tblOpciones.getStore().load(
    								{	
                                    	params:	{
                                            		funcion:46,
                                                    idConexion:idConexionAlmacen     
                                        		}	
                                    }
                               );                                        	
	ventanaSelTabla.show();                                        
	
}

function mostrarVentanaFiltro(objTabla,ocultarProy)
{
	
	var gridCampos=crearGridCampos(ocultarProy);
    var cmbOperacion=crearComboExt('cmbOperacion',arrOperaciones,180,5);
    cmbOperacion.on('select',funcCmbSelect);
    ocultarEt=false;
    if(ocultarProy)
    {
    	cmbOperacion.setValue('2');
        cmbOperacion.hide();
		ocultarEt=true;
        gridCampos.enable();
    }
	var form = new Ext.form.FormPanel(	
										{
											baseCls: 'x-plain',
											layout:'absolute',
											defaultType: 'label',
											items: 	[
                                            			{
                                                        	id:'lblOperacion',
                                                        	x:10,
                                                            y:10,
                                                            html:'Indique la operaci&oacute;n a realizar:',
                                                            hidden:ocultarEt
                                                        },
                                                        cmbOperacion,
                                            			{
                                                        	x:10,
                                                            y:40,
                                                            html:'Seleccione los campos que desea proyectar en su consulta:'
                                                        },
														gridCampos
													]
										}
									);
	
	var ventanaAM = new Ext.Window(
									{
										title: 'Configuraci&oacute;n de filtro de consulta [<b>Tabla:</b> '+objTabla.nTablaUsr+']',
										width: 600,
										height:380,
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
															disabled:!ocultarEt,
                                                            id:'btnSiguiente',
															text: 'Siguiente',
															handler: function()
																	{
                                                                    	var op=cmbOperacion.getValue();
                                                                    	var filas=gridCampos.getSelectionModel().getSelections();
                                                                        if((filas.length==0)&&(op!='6'))
                                                                        {
                                                                        	msgBox('Debe seleccionar al menos un campo para relizar la operaci&oacute;n indicada');
                                                                        	return;
                                                                        }
                                                                        var cadCampos='';
                                                                        var x;
                                                                        for(x=0;x<filas.length;x++)
                                                                        {
                                                                        	if(cadCampos=='')
                                                                            	cadCampos=filas[x].get('nCamposO');
                                                                            else
                                                                            	cadCampos+=','+filas[x].get('nCamposO');
                                                                        }
                                                                        
                                                                    	var objCondiciones=	{
                                                                        						campos:cadCampos,
                                                                        						operacion:op,
                                                                                                arrCamposProy:filas,
                                                                        						dTabla:objTabla,
                                                                                                camposProy:arrCampos
                                                                        					};
                                                                       	ventanaAM.close();
                                                                        
																		mostrarVentanaCondFiltro(objCondiciones);
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
	llenarDatosCampos(ventanaAM,objTabla);          
    
    

}

function crearGridCampos(ocultarProy)
{
	var dsDatos=[];
    var alDatos=	new Ext.data.SimpleStore	(
                                                    {
                                                        fields:	[
                                                                    {name: 'nCamposO'},
                                                                    {name: 'nombreCampo'},
                                                                    {name: 'tipoDato'},
                                                                    {name: 'tipoCtrl'},
                                                                    {name: 'campoLlave'},
                                                                    {name: 'tablaO'},
                                                                    {name: 'tipoTabla'}
                                                                    
                                                                    
                                                                ]
                                                    }
                                                );

    alDatos.loadData(dsDatos);
	var chkRow=new Ext.grid.CheckboxSelectionModel({singleSelect:!ocultarProy});
	chkRow.on('beforerowselect',filaSeleccionada);
	var cModelo= new Ext.grid.ColumnModel   	(
												 	[
													 	new  Ext.grid.RowNumberer(),
														chkRow,
														{
															header:'Campo',
															width:250,
															sortable:true,
															dataIndex:'nombreCampo'
														},
                                                        {
															header:'Campo llave',
															width:100,
															sortable:true,
															dataIndex:'campoLlave',
                                                            renderer:function(val)
                                                            		{
                                                                    	if(val=='0')
                                                                        	return 'No';
                                                                         return 'S&iacute;';
                                                                    }
														},
                                                        {
                                                        	header:'Tipo',
                                                            width:100,
                                                            dataIndex:'tipoDato'
                                                        }
                                                        
													]
												);
                                                
	var tblGrid=	new Ext.grid.EditorGridPanel	(
                                                        {
                                                            id:'gridCampos',
                                                            store:alDatos,
                                                            frame:true,
                                                            x:10,
                                                            y:60,
                                                            cm: cModelo,
                                                            height:230,
                                                            width:550,
                                                            sm:chkRow,
                                                            disabled:true
                                                        }
                                                    );
	return 	tblGrid;
}

var arrEnteros=['int','float','decimal','double','bigint','numeric','real','smallint','tinyint','year'];
var arrTiempo=['date','time','timestamp'];

function filaSeleccionada(cm,fila,kExist,registro)
{
	var cmbOperacion=gEx('cmbOperacion');
    if(cmbOperacion==null)
    	return;
    var accion=cmbOperacion.getValue();
    var tipoDato=registro.get('tipoDato');
    switch(accion)	
    {
    	case '1':
        	if(validarCampoUnico())
            {
            	if(existeValorArreglo(arrEnteros,tipoDato)==-1)
                {
                	msgBox('El tipo de dato del campo seleccionado no es compatible con la operaci&oacute;n a realizar');
                	return false;
                }
            }
            else
            	return false;
        break;
        case '2':
        break;
        case '3':
        	if(validarCampoUnico())
            {
            	if(existeValorArreglo(arrEnteros,tipoDato)==-1)
                {
                	msgBox('El tipo de dato del campo seleccionado no es compatible con la operaci&oacute;n a realizar');
                	return false;
                }
            
            }
            else
            	return false;
        break;
        case '4':
        	if(validarCampoUnico())
            {
	          	if(existeValorArreglo(arrEnteros,tipoDato)==-1)
                {
                	if(existeValorArreglo(arrTiempo,tipoDato)==-1)
                    {
                        msgBox('El tipo de dato del campo seleccionado no es compatible con la operaci&oacute;n a realizar');
                        return false;
                    }
                }
            
            }
            else
            	return false;            
        break;
        case '5':
        	if(validarCampoUnico())
            {
            	if(existeValorArreglo(arrEnteros,tipoDato)==-1)
                {
                	if(existeValorArreglo(arrTiempo,tipoDato)==-1)
                    {
                        msgBox('El tipo de dato del campo seleccionado no es compatible con la operaci&oacute;n a realizar');
                        return false;
                    }
                }
            }
            else
            	return false;            
        break;
        case '6':
        break;
        case '7':
        	if(validarCampoUnico())
            {
            	if(existeValorArreglo(arrEnteros,tipoDato)==-1)
                {
                	msgBox('El tipo de dato del campo seleccionado no es compatible con la operaci&oacute;n a realizar');
                	return false;
                }
            
            }
            else
            	return false;            
        break;
    }
}

function validarCampoUnico()
{
	var grid=gEx('gridCampos');
	var fila=grid.getSelectionModel().getSelections();
    if(fila.length==0)
    	return true;
    else
    {
    	msgBox('La operaci&oacute;n seleccionada requiere que sea elegido solo un campo para su ejecuci&oacute;n');
    	return false;
    }
}

function funcCmbSelect(combo,registro)
{
	gEx('gridCampos').getSelectionModel().clearSelections();
	switch(registro.get('id'))
    {
    	case '1':
        	gEx('gridCampos').enable();
        break;
        case '2':
        	gEx('gridCampos').enable();
        break;
        case '3':
        	gEx('gridCampos').enable();
        break;
        case '4':
        	gEx('gridCampos').enable();
        break;
        case '5':
        	gEx('gridCampos').enable();
        break;
    	case '6':
        
        	gEx('gridCampos').disable();
        break;
        case '7':
        	gEx('gridCampos').enable();
        break;
    }
   	
    gEx('btnSiguiente').enable();
}

var arrCampos=null;

function llenarDatosCampos(ventana,objTabla)
{
	function funcAjax()
    {
        var resp=peticion_http.responseText;
        arrResp=resp.split('|');
        if(arrResp[0]=='1')
        {
        	arrCampos=eval(arrResp[1]);
            
            gEx('gridCampos').getStore().loadData(arrCampos);
            ventana.show();
        }
        else
        {
            msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
        }
    }
    obtenerDatosWeb('../paginasFunciones/funcionesThot.php',funcAjax, 'POST','funcion=8&idConexion='+idConexionAlmacen+
    				'&nTabla='+bE(objTabla.nTablaO)+'&tTabla='+objTabla.tipoTabla,true);
}

var tipoCampoF;

var arrVarchar=[['<>','Distinto a'],['=','Igual a'],['in','Est\xE9 en'],['not in','No est\xE9 en'],['like \'valor%\'','Inicie con'],['like \'%valor\' ','Termine con'],['like \'%valor%\'','Contenga']];
var arrInt=[['>','Mayor que'],['>=','Mayor o igual que'],['<','Menor a'],['<=','Menor o igual que'],['<>','Distinto a'],['=','Igual a'],['in','Est\xE9 en'],['not in','No est\xE9 en']];
var arrCombo=[['<>','Distinto a'],['=','Igual a'],['in','Est\xE9 en'],['not in','No est\xE9 en']];

var arrCamposTablas;

function mostrarVentanaCondFiltro(objFinal)
{
   	var nombreTablaQuery;
	filtroUsuario=new Array();
    filtroMysql=new Array();
    filtroTipoValor=new Array();
    var dsDatos= new Ext.data.SimpleStore	(
													{
														fields:	[
																	{name: 'nCamposO'},
                                                                    {name: 'nombreCampo'},
                                                                    {name: 'tipoDato'},
                                                                    {name: 'tipoCtrl'},
                                                                    {name: 'campoLlave'},
                                                                    {name: 'tablaO'},
                                                                    {name: 'tipoTabla'}
																]
													}
												);
	var titulo='';
    var tVentana;
    var arrCamposProy=[];                                                
	if(objFinal)
    {                      
    	console.log(objFinal);                          
        arrCamposTablas=objFinal.camposProy;                                           
        dsDatos.loadData(objFinal.camposProy);
        titulo=objFinal.dTabla.nTablaO;
        arrCamposProy=objFinal.camposProy;
        nombreTablaQuery=objFinal.dTabla.nTablaO;
        tVentana='Especificar filtro almac&eacute;n de datos [Tabla: '+titulo+']';
    }
    else
    {
    	nombreTablaQuery=nodoSel.attributes.objJava.tabla;
        tVentana='Modificar filtro almac&eacute;n de datos [Tabla: '+nodoSel.text+']';
    }
    
	var comboEtapas=document.createElement('select');
    var comboTmp=document.createElement('select');
	var cmbCampo=new Ext.form.ComboBox	(
													{
														id:'cmbCampo',
														mode:'local',
														emptyText:'Elija una opci\xF3n',
														store:dsDatos,
														displayField:'nombreCampo',
														valueField:'nCamposO',
														transform:comboTmp,
														editable:false,
														typeAhead: true,
														triggerAction: 'all',
														lazyRender:true,
                                                        width:350,
														listWidth:350
													}
												)
    
    cmbCampo.setPosition(10,40);
    function setCondicionValor(combo,registro,indice)
    {
    	
        var cmbCondicion=Ext.getCmp('cmbCondicion');
        var arr;
        cmbCondicion.reset();
        tipoCampoF=registro.get('tipoDato');
        switch(tipoCampoF)
        {
            case 'optM':
            case 'optT':
                arr=arrCombo;
                mostrarCampoF('cmbValor');
                Ext.getCmp('cmbValor').reset();
                llenarOpciones(registro);
            break;
            case 'varchar':
            case 'text':
                arr=arrVarchar;
                Ext.getCmp('txtValor').setValue('');
                mostrarCampoF('txtValor');
            break;
            case 'smallint':
            case 'year':
            case 'bigint':
            case 'tinyint':
            case 'int':
            case 'varbinary':
                arr=arrInt;
                Ext.getCmp('intValor').setValue('0');
                mostrarCampoF('intValor');
            break;
            case 'numeric':
            case 'real':
            case 'double':
            case 'float':
            case 'decimal':
                arr=arrInt;
                Ext.getCmp('decValor').setValue('0.0');
                mostrarCampoF('decValor');
            break;
            case 'datetime':
            case 'date':
                arr=arrInt;
                mostrarCampoF('dteValor');
            break;
        }
        cmbCondicion.getStore().loadData(arr);
        cmbCondicion.focus(false,10);
    }
    
    
    cmbCampo.on('select',setCondicionValor);
    var condicion=crearComboGeneral('cmbCondicion',null,'<?php echo $etj["lblElijaOpcion"]?>');
    condicion.setPosition(390,40);
    condicion.setWidth(125);
    
    function setFocoValor(combo,registro,indice)
    {
    	switch(tipoCampoF)
        {
            case'optM':
            case 'optT':
                Ext.getCmp('cmbValor').focus(false,10);
            break;
            case 'varchar':
            case 'text':
            	Ext.getCmp('txtValor').focus(false,10);
            break;
            case 'smallint':
            case 'year':
            case 'bigint':
            case 'tinyint':
            case 'int':
            case 'varbinary':
                Ext.getCmp('intValor').focus(false,10);
            break;
            case 'numeric':
            case 'real':
            case 'double':
            case 'float':
            case 'decimal':
                Ext.getCmp('decValor').focus(false,10);
            break;
            case 'datetime':
            case 'date':
                Ext.getCmp('dteValor').focus(false,10);
            break;
        }
    	
    }
    
    condicion.on('select',setFocoValor);
    var valor=crearComboGeneral('cmbValor',null,'<?php echo $etj["lblElijaOpcion"]?>');
    valor.setPosition(545,40);
    valor.setWidth(185);
    
    var valorTxt=new Ext.form.TextField	(
    										{
                                            	id:'txtValor',
                                                width:130,
                                                x:545,
                                                y:40,
                                                hidden:true
                                                
                                            }	
    									)
    
    var valorDte=new Ext.form.DateField	(
    										{
                                            	id:'dteValor',
                                                width:100,
                                                x:545,
                                                y:40,
                                                hidden:true
                                            }
    									)
                                        
    var valorInt= new Ext.form.NumberField	(
                                                {
                                                    id:'intValor',
                                                    width:100,
                                                    x:545,
	                                                y:40,
                                                    hidden:true,
                                                    allowDecimals:false
                                                    
                                                }	
                                            )
                                            
	var valorDec= new Ext.form.TextField	(
                                                {
                                                    id:'decValor',
                                                    width:100,
                                                    x:545,
	                                                y:40,
                                                    hidden:true,
                                                    allowDecimals:true
                                                    
                                                }	
	                                           )                                                                            
    
    var gridOrden=crearGridOrden(arrCamposProy);
	
    var form = new Ext.form.FormPanel(	
                                        {
                                            baseCls: 'x-plain',
                                            layout:'absolute',
                                            defaultType: 'textfield',
                                            items: 	[
                                            
                                                        {
                                                            x:10,
                                                            y:15,
                                                            xtype:'label',
                                                            html:'<span  style="color:#000; font-weight:bold">Campo filtro:</span>'
                                                        },
                                                        cmbCampo,
                                                        {
                                                            x:390,
                                                            y:15,
                                                            xtype:'label',
                                                            html:'<span  style="color:#000; font-weight:bold">Condici&oacute;n:</span>'
                                                        },
                                                        condicion,
                                                        {
                                                            x:545,
                                                            y:10,
                                                            xtype:'label',
                                                            html:'<span  style="color:#000; font-weight:bold">Valor: </span><a href="javascript:ingresarParametroAlmacen()"><img src="../images/database_connect.png" title="Ingresar parametro de referencia" alt="Ingresar parametro de referencia"></a>'
                                                        },
                                                        valor,
                                                        valorTxt,
                                                        valorDte,
                                                        valorInt,
                                                        valorDec,
                                                        {
                                                            xtype:'panel',
                                                            x:10,
                                                            y:70,
                                                            baseCls: 'x-plain',
                                                            items:	[
                                                                         {
                                                                            xtype:'button',
                                                                            text:'Agregar',
                                                                            icon:'../images/mas.gif',
                                                                            cls:'x-btn-text-icon',
                                                                            handler:function()
                                                                                    {
                                                                                        if(cmbCampo.getValue()=='')
                                                                                        {
                                                                                            function resp()
                                                                                            {
                                                                                                cmbCampo.focus(false,10);
                                                                                            }
                                                                                            Ext.MessageBox.alert(lblAplicacion,'Debe seleccionar el campo bajo el cual se filtrar&aacute;a la informaci&oacute;n',resp);
                                                                                            return;
                                                                                        }
                                                                                        var campoMysql=cmbCampo.getValue();
                                                                                        var campoUsr=cmbCampo.getRawValue();
                                                                                        var condicionU;
                                                                                        var condicionM;
                                                                                        if(condicion.getValue()=='')
                                                                                        {
                                                                                            function resp()
                                                                                            {
                                                                                                condicion.focus(false,10);
                                                                                            }
                                                                                            Ext.MessageBox.alert(lblAplicacion,'Debe seleccionar la condici&oacute;n de comparaci&oacute;n',resp);
                                                                                            return;
                                                                                        }
                                                                                        condicionU=condicion.getRawValue();
                                                                                        condicionM=condicion.getValue();
                                                                                        var valorU='';
                                                                                        var valorM='';
                                                                                        
                                                                                        switch(tipoCampoF)
                                                                                        {
                                                                                            case 'optM':
                                                                                            case 'optT':
                                                                                                if(valor.getValue()=='')
                                                                                                {
                                                                                                    function resp()
                                                                                                    {
                                                                                                        valor.focus(false,10);
                                                                                                    }
                                                                                                    Ext.MessageBox.alert(lblAplicacion,'Debe ingresar el valor bajo el cual de filtrar&aacute; la informaci&oacute;n',resp);
                                                                                                    return;
                                                                                                }
                                                                                                valorM=valor.getValue();
                                                                                                valorU=valor.getRawValue();
                                                                                            break;
                                                                                            case 'varchar':
                                                                                            case 'text':
                                                                                                valorU="'"+valorTxt.getValue()+"'";
                                                                                                valorM="'"+valorTxt.getValue()+"'";
                                                                                            break;
                                                                                            case 'smallint':
                                                                                            case 'year':
                                                                                            case 'bigint':
                                                                                            case 'tinyint':
                                                                                            case 'int':
                                                                                            case 'varbinary':
                                                                                                if(valorInt.getRawValue()=='')
                                                                                                {
                                                                                                    function resp()
                                                                                                    {
                                                                                                        valorInt.focus(false,10);
                                                                                                    }
                                                                                                    Ext.MessageBox.alert(lblAplicacion,'Debe ingresar el valor bajo el cual de filtrar&aacute; la informaci&oacute;n',resp);
                                                                                                    return;
                                                                                                }
                                                                                                valorU=valorInt.getValue();
                                                                                                valorM=valorInt.getValue();
                                                                                                
                                                                                            break;
                                                                                            case 'numeric':
                                                                                            case 'real':
                                                                                            case 'double':
                                                                                            case 'float':
                                                                                            case 'decimal':
                                                                                                if(valorDec.getRawValue()=='')
                                                                                                {
                                                                                                    function resp()
                                                                                                    {
                                                                                                        valorDec.focus(false,10);
                                                                                                    }
                                                                                                    Ext.MessageBox.alert(lblAplicacion,'Debe ingresar el valor bajo el cual de filtrar&aacute; la informaci&oacute;n',resp);
                                                                                                    return;
                                                                                                }
                                                                                                valorU=valorDec.getValue();
                                                                                                valorM=valorDec.getValue();
                                                                                            break;
                                                                                            case 'date':
                                                                                            case 'datetime':
                                                                                                if(valorDte.getValue()=='')
                                                                                                {
                                                                                                    function resp()
                                                                                                    {
                                                                                                        valorDte.focus(false,10);
                                                                                                    }
                                                                                                    Ext.MessageBox.alert(lblAplicacion,'Debe ingresar el valor bajo el cual de filtrar&aacute; la informaci&oacute;n',resp);
                                                                                                    return;
                                                                                                }
                                                                                                valorU=valorDte.getValue().format('d/m/Y');
                                                                                                valorM="'"+valorDte.getValue().format('Y-m-d')+"'";
                                                                                                
                                                                                            break;
                                                                                            
                                                                                        }
                                                                                        var compA='';
                                                                                        var compC='';
                                                                                        if((condicionM=='in')||(condicionM=='not in'))
                                                                                        {
                                                                                        	compA='(';
                                                                                            compC=')';
                                                                                        }
                                                                                        var cadM='';
                                                                                        var cadU='';
                                                                                        if((condicionM.indexOf('like')!=-1))
                                                                                        {
                                                                                        	cadM=campoMysql+' '+condicionM.replace('valor',valorM.replace(/\'/gi,''));
                                                                                       	}
                                                                                        else
                                                                                        {
                                                                                            cadM=campoMysql+' '+condicionM+' '+compA+valorM+compC;
                                                                                            
                                                                                        }
                                                                                        cadU=campoUsr+' '+condicionU+' '+compA+valorU+compC;
                                                                                        filtroUsuario[filtroUsuario.length]=cadU;
                                                                                        filtroMysql[filtroMysql.length]=cadM;
                                                                                        filtroTipoValor[filtroTipoValor.length]='1|0';
                                                                                        generarSentencia();
                                                                                    }
                                                                         }
                                                                    ]
                                                            
                                                        
                                                               
                                                        },
                                                        {
                                                            xtype:'panel',
                                                            x:100,
                                                            y:70,
                                                            baseCls: 'x-plain',
                                                            items:	[
                                                                         {
                                                                             xtype:'button',
                                                                             text:'Remover',
                                                                             icon:'../images/menos.gif',
                                                                             cls:'x-btn-text-icon',
                                                                             handler:function()
                                                                                    {
                                                                                        if(filtroUsuario.length>0)
                                                                                        {
                                                                                            filtroUsuario.splice(filtroUsuario.length-1,1);
                                                                                            filtroMysql.splice(filtroMysql.length-1,1);
                                                                                            filtroTipoValor.splice(filtroTipoValor.length-1,1);
                                                                                            generarSentencia();
                                                                                        }
                                                                                    }
                                                                         }
                                                                    ]
                                                        },
                                                        {
                                                            xtype:'panel',
                                                            x:195,
                                                            y:70,
                                                            baseCls: 'x-plain',
                                                            items:	[
                                                                         {
                                                                         	 width:25,
                                                                             xtype:'button',
                                                                             text:'(',
                                                                             handler:function()
                                                                                    {
                                                                                        filtroUsuario[filtroUsuario.length]='(';
                                                                                        filtroMysql[filtroMysql.length]='(';
                                                                                        filtroTipoValor[filtroTipoValor.length]='0|0';
                                                                                        generarSentencia();
                                                                                    }
                                                                         }
                                                                    ]
                                                        },
                                                        {
                                                            xtype:'panel',
                                                            x:230,
                                                            y:70,
                                                            baseCls: 'x-plain',
                                                            items:	[
                                                                         {
                                                                         	width:25,
                                                                            xtype:'button',
                                                                            text:')',
                                                                            handler:function()
                                                                                    {
                                                                                        filtroUsuario[filtroUsuario.length]=')';
                                                                                        filtroMysql[filtroMysql.length]=')';
                                                                                        filtroTipoValor[filtroTipoValor.length]='0|0';
                                                                                        generarSentencia();
                                                                                    }
                                                                         }
                                                                    ]
                                                        },
                                                        {
                                                            xtype:'panel',
                                                            x:265,
                                                            y:70,
                                                            baseCls: 'x-plain',
                                                            items:	[
                                                                         {
                                                                         	width:25,
                                                                            xtype:'button',
                                                                            text:'Y',
                                                                            handler:function()
                                                                                    {
                                                                                        filtroUsuario[filtroUsuario.length]='Y';
                                                                                        filtroMysql[filtroMysql.length]='AND';
                                                                                        filtroTipoValor[filtroTipoValor.length]='0|0';
                                                                                        generarSentencia();
                                                                                    }
                                                                         }
                                                                    ]
                                                        },
                                                        {
                                                            xtype:'panel',
                                                            x:300,
                                                            y:70,
                                                            baseCls: 'x-plain',
                                                            items:	[
                                                                         {
                                                                         	 width:25,
                                                                             xtype:'button',
                                                                             text:'O',
                                                                             handler:function()
                                                                                    {
                                                                                        filtroUsuario[filtroUsuario.length]='O';
                                                                                        filtroMysql[filtroMysql.length]='OR';
                                                                                        filtroTipoValor[filtroTipoValor.length]='0|0';
                                                                                        generarSentencia();
                                                                                    }
                                                                         }
                                                                    ]
                                                        },
                                                        {
                                                            id:'txtConsulta',
                                                            xtype:'textarea',
                                                            x:10,
                                                            y:105,
                                                            width:760,
                                                            height:90,
                                                            readOnly:true
                                                        },
                                                        {
                                                        	x:10,
                                                            y:210,
                                                            html:'<span  style="color:#000; font-weight:bold">Ordenar registros por:</span>',
                                                            xtype:'label'
                                                        },
                                                        gridOrden,
                                                       
                                                        {
                                                        	xtype:'label',
                                                        	x:10,
                                                            y:375,
                                                            html : '<span  style="color:#000; font-weight:bold">Considerar s&oacute;lo los primeros:</span>'
                                                        },
                                                        {
                                                        	xtype:'numberfield',
                                                            allowDecimals:false,
                                                            allowNegative:false,
                                                            width:80,
                                                            id:'txtNoRegistros',
                                                            x:170,
                                                            y:370
                                                        },
                                                        {
                                                        	xtype:'label',
                                                        	x:260,
                                                            y:375,
                                                            html : '<span  style="color:#000; font-weight:bold">registros</span>'
                                                        },
                                                         {
                                                        	xtype:'checkbox',
                                                            id:'chkDistinct',
                                                        	x:400,
                                                            y:370,
                                                            boxLabel : '<span  style="color:#000; font-weight:bold">Considerar s&oacute;lo valores distintos (Distinct)</span>'
                                                        }
                                                    ]
                                        }
                                    );
		
	var ventanaOrigenDatosSel = new Ext.Window(
											{
												title: tVentana,
												width: 820,
												height:480,
												minWidth: 300,
												minHeight: 100,
												layout: 'fit',
												plain:true,
												modal:true,
												bodyStyle:'padding:5px;',
												buttonAlign:'center',
												items: form,
												buttons:	[
																{
																	id:'btnAceptar',
																	text: 'Finalizar',
																	listeners:	{
																					click:function()
																						{
                                                                                        	var sentenciaMysql='';
                                                                                            function funcAjax()
                                                                                            {
                                                                                            	var idReporte=idReferencia;
                                                                                                var resp=peticion_http.responseText;
                                                                                                arrResp=resp.split('|');
                                                                                                var cadObj;
                                                                                                if(arrResp[0]=='1')
                                                                                                {
                                                                                                 	var x;
                                                                                                    var token;
                                                                                                    var arrTokens='';
                                                                                                    var datosTValor;
                                                                                                    var cadParametros='';
                                                                                                    var arrParamAux=new Array();
                                                                                                    for(x=0;x<filtroUsuario.length;x++)
                                                                                                    {
                                                                                                    	token='{"tokenUsuario":"'+(filtroUsuario[x])+'","tokenMysql":"'+(filtroMysql[x])+'","tokenTipo":"'+filtroTipoValor[x]+'"}';
                                                                                                        if(arrTokens=='')
                                                                                                        	arrTokens=token;
                                                                                                        else
                                                                                                        	arrTokens+=','+token;
                                                                                                        datosTValor=filtroTipoValor[x].split("|");
                                                                                                        if((datosTValor[0]=='5')||(datosTValor[0]=='6'))
                                                                                                        {
                                                                                                        	if(existeValorArreglo(arrParamAux,datosTValor[1])==-1)
                                                                                                            {
                                                                                                                if(cadParametros=='')
                                                                                                                    cadParametros=datosTValor[1];
                                                                                                                else
                                                                                                                    cadParametros+=','+datosTValor[1];  
                                                                                                                arrParamAux.push(datosTValor[1]); 
                                                                                                            }
                                                                                                    	}
                                                                                                    }
                                                                                                   
                                                                                                    var cadOrden='';
                                                                                                    var obj;
                                                                                                    var fila;
                                                                                                    for(x=0;x<gridOrden.getStore().getCount();x++)
                                                                                                    {
                                                                                                    	fila=gridOrden.getStore().getAt(x);
                                                                                                    	obj='{"campo":"'+fila.get('campo')+'","orden":"'+fila.get('orden')+'"}';
                                                                                                        if(cadOrden=='')
                                                                                                        	cadOrden=obj;
                                                                                                        else
                                                                                                        	cadOrden+=','+obj;
                                                                                                    
                                                                                                    }
                                                                                                    
                                                                                                    var numRegistros='';
                                                                                                    var txtNoRegistros=gEx('txtNoRegistros');
                                                                                                    numRegistros=txtNoRegistros.getValue();
                                                                                                    
                                                                                                    var chkDistinct=gEx('chkDistinct').getValue();
                                                                                                    
                                                                                                    var distinct=0;
                                                                                                    if(chkDistinct)
                                                                                                    	distinct=1;
                                                                                                    
                                                                                                    if(objFinal)	 
                                                                                                    {
                                                                                                        var x;
                                                                                                        var tAlmacen;
                                                                                                        if(nodoSel.attributes.tipo=='ad')
                                                                                                            tAlmacen=0;
                                                                                                        else
                                                                                                            tAlmacen=1;
                                                                                                        cadObj='"numRegistros":"'+numRegistros+'","distinto":"'+distinct+'","idConexion":"'+idConexionAlmacen+'","tipoDataSet":"'+tDataSet+'","tipoAlmacen":"'+tAlmacen+'","parametros":"'+cadParametros+'","nombreDataSet":"@nombreDS","descripcion":"@descripcion","idReporte":"'+idReporte+'","tabla":"'+objFinal.dTabla.nTablaO+'","camposProy":"'+objFinal.campos+'","operacion":"'+objFinal.operacion+'","arrOrden":"'+bE('['+cadOrden+']')+'","tokenSql":['+arrTokens+']';
                                                                                                        cadObj="{"+cadObj+"}";
                                                                                                        
                                                                                                        mostrarVentadaDatosDataSet(cadObj,ventanaOrigenDatosSel);
                                                                                                    }
                                                                                                    else
                                                                                                    {
                                                                                                    	cadObj='"numRegistros":"'+numRegistros+'","distinto":"'+distinct+'","parametros":"'+cadParametros+'","idAlmacen":"'+nodoSel.id+'","arrOrden":"'+bE('['+cadOrden+']')+'","tokenSql":['+arrTokens+']';
                                                                                                    	cadObj="{"+cadObj+"}"; 
                                                                                                   		modificarPregunta(cadObj,ventanaOrigenDatosSel);
                                                                                                    }
                                                                                                    
                                                                                                }
                                                                                                else
                                                                                                {
                                                                                                    Ext.MessageBox.alert(lblAplicacion,'La consulta ingresada presenta errores de sintaxis, por favor verifiquela');
                                                                                                    return;
                                                                                                }
                                                                                            }
                                                                                            obtenerDatosWeb('../paginasFunciones/funcionesFormulario.php',funcAjax, 'POST','funcion=27&idConexionAlmacen='+idConexionAlmacen+'&tb='+nombreTablaQuery+'&qry='+sentenciaMysql,true);
                                                                                            
																						}
																				}
																},
																{
																	text: '<?php echo $etj["lblBtnCancelar"]?>',
																	handler:function()
																			{
																				ventanaOrigenDatosSel.close();
																			}
																}
															]
											}
										);                                        
                                        
    if((typeof(frmProceso)!='undefined')&&(frmProceso))
    	generarSentencia();
    if(objFinal)	
    {                  
		ventanaOrigenDatosSel.show();
        if(nodoSel.id=='-10')
	       	gEx('chkDistinct').hide();
    }
    else
	    llenarCondicionesFiltrado(ventanaOrigenDatosSel);                      
}

var regOrden=crearRegistro([
                              {name: 'campo'},
                              {name: 'orden'}
                          ]
						);	

function crearGridOrden(aCampos)
{
	arrCampos=[];
	if(aCampos!=undefined)
    	arrCampos=aCampos;
        
	var arrOrden=[['0','Ascendente'],['1','Descendente']];
	var cmbOrden=crearComboExt('cmbOrden',arrOrden);
    var cmbCampos=crearComboExt('cmbCamposOrdenQuery',arrCampos);
    var dsDatos=[];
    var alDatos=	new Ext.data.SimpleStore	(
                                                {
                                                    fields:	[
                                                                {name: 'campo'},
                                                                {name: 'orden'}
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
															header:'Campo',
															width:350,
															sortable:true,
															dataIndex:'campo',
                                                            editor:cmbCampos,
                                                            renderer:function(val)
                                                            			{
                                                                        	return formatearValorRendererCombo(cmbCampos.getStore(),val,'id','nombre');
                                                                        }
														},
														{
															header:'Orden',
															width:90,
															sortable:true,
															dataIndex:'orden',
                                                            editor:cmbOrden,
                                                            renderer:function(val)
                                                            			{
                                                                        	return formatearValorRenderer(arrOrden,val);
                                                                        }
														}
													]
												);
                                                
	var tblGrid=	new Ext.grid.EditorGridPanel	(
                                                        {
                                                        	id:'gridOrden',
                                                            store:alDatos,
                                                            frame:true,
                                                            x:140,
                                                            y:205,
                                                            cm: cModelo,
                                                            height:150,
                                                            width:630,
                                                            sm:chkRow,
                                                            tbar:	[
                                                            			{
                                                                        	icon:'../images/add.png',
                                                                            cls:'x-btn-text-icon',
                                                                            text:'Agregar criterio de orden',
                                                                            handler:function()
                                                                            		{
                                                                                    	var r=new regOrden(
                                                                                        						{
                                                                                                                	campo:'',
                                                                                                                    orden:'0'
                                                                                                                }
                                                                                                           )
                                                                                   		tblGrid.getStore().add(r);
                                                                                    }
                                                                            
                                                                        },
                                                                        {
                                                                        	icon:'../images/delete.png',
                                                                            cls:'x-btn-text-icon',
                                                                            text:'Remover criterio de orden',
                                                                            handler:function()
                                                                            		{
                                                                                    	var filas=tblGrid.getSelectionModel().getSelections();
                                                                                        if(filas.length==0)
                                                                                        {
                                                                                        	msgBox('Debe seleccionar almenos un criterio de orden a remover');
                                                                                        	return;
                                                                                        }
                                                                                        function resp(btn)
                                                                                        {
                                                                                        	if(btn=='yes')
                                                                                            {
                                                                                            	tblGrid.getStore().remove(filas);
                                                                                            }
                                                                                        }
                                                                                        msgConfirm('Est&aacute; seguro de querer remover los criterios seleccionados?',resp);
                                                                                    }
                                                                            
                                                                        }
                                                                        
                                                            		]
                                                        }
                                                    );
	return 	tblGrid;
}

function mostrarCampoF(idCampo)
{
	Ext.getCmp('cmbValor').hide();
	Ext.getCmp('txtValor').hide();
    Ext.getCmp('dteValor').hide();
    Ext.getCmp('intValor').hide();
    Ext.getCmp('decValor').hide();
    Ext.getCmp(idCampo).show();
}

function llenarOpciones(registro)
{
	function funcAjax()
    {
        var resp=peticion_http.responseText;
        arrResp=resp.split('|');
        if(arrResp[0]=='1')
        {
        	var datos=eval(arrResp[1]);
     		Ext.getCmp('cmbValor').getStore().loadData(datos);		   	  
        }
        else
        {
            msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
        }
    }
    obtenerDatosWeb('../paginasFunciones/funcionesFormulario.php',funcAjax, 'POST','funcion=26&tb='+registro.get('tablaO')+'&tipoTabla='+registro.get('tipoTabla')+'&campo='+registro.get('nCamposO'),true);
}

var sentenciaMysql;

function generarSentencia()
{
	var x;
    var txtConsulta='';
    sentenciaMysql='';
	for(x=0;x<filtroUsuario.length;x++)
    {
    	txtConsulta+=' '+filtroUsuario[x];
        sentenciaMysql+=' '+filtroMysql[x];
    }
    Ext.getCmp('txtConsulta').setValue(txtConsulta);
}

function guardarPregunta(objFinal,ventana)
{
	function funcAjax()
    {
        var resp=peticion_http.responseText;
        arrResp=resp.split('|');
        if(arrResp[0]=='1')
        {
        	arbolAlmacenEstructDatos.getRootNode().reload();
        	ventana.close();
        }
        else
        {
            msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
        }
    }
    obtenerDatosWeb('../paginasFunciones/funcionesThot.php',funcAjax, 'POST','funcion=9&obj='+objFinal,true);
}

function modificarPregunta(objFinal,ventana)
{
	function funcAjax()
    {
        var resp=peticion_http.responseText;
        arrResp=resp.split('|');
        if(arrResp[0]=='1')
        {
        	arbolAlmacenEstructDatos.getRootNode().reload();
        	ventana.close();
        }
        else
        {
            msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
        }
    }
    obtenerDatosWeb('../paginasFunciones/funcionesThot.php',funcAjax, 'POST','funcion=20&obj='+objFinal,true);
}

function mostrarVentadaDatosDataSet(cadObj,ventanaOrigenDatosSel)
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
                                                            html:'Nombre:'
                                                        },
                                                        {
                                                        	x:100,
                                                            y:5,
                                                            xtype:'textfield',
                                                            id:'txtNombre',
                                                            width:300
                                                            
                                                        },
                                                        
                                                        {
                                                        	x:10,
                                                            y:40,
                                                            html:'Descripci&oacute;n:'
                                                        },
                                                        {
                                                        	x:100,
                                                            y:35,
                                                            xtype:'textarea',
                                                            id:'txtDescripcion',
                                                            width:300,
                                                            height:80
                                                            
                                                        }

													]
										}
									);
	
	var ventanaAM = new Ext.Window(
									{
										title: 'Guardar almac&eacute;n de datos',
										width: 500,
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
                                                                	gEx('txtNombre').focus(false,500);
																}
															}
												},
										buttons:	[
														{
															
															text: '<?php echo $etj["lblBtnAceptar"]?>',
															handler: function()
																	{
                                                                    	var txtNombre=gEx('txtNombre') ;
                                                                        var txtDescripcion=gEx('txtDescripcion');
                                                                        if(txtNombre.getValue()=='')
                                                                        {
                                                                        	function resp()
                                                                            {
                                                                            	txtNombre.focus();
                                                                            }
                                                                        	msgBox('Debe ingresar el nombre del almacen de datos',resp);
                                                                        }
                                                                        var nombre=txtNombre.getValue();
                                                                        var descripcion=txtDescripcion.getValue();
                                                                        var cadAux=cadObj.replace('@nombreDS',cv(nombre));
                                                                        cadAux=cadAux.replace('@descripcion',cv(descripcion));
                                                                        ventanaAM.close();
																		guardarPregunta(cadAux,ventanaOrigenDatosSel);
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

function ingresarParametroAlmacen()
{
	arrParametrosRep=[];
	var cmbCampo=gEx('cmbCampo');
    var condicion=gEx('cmbCondicion');
    if(cmbCampo.getValue()=='')
    {
        function resp()
        {
            cmbCampo.focus(false,10);
        }
        msgBox('Debe seleccionar el campo bajo el cual se filtrar&aacute; la informaci&oacute;n',resp);
        return;
    }
    
    if(condicion.getValue()=='')
    {
        function resp()
        {
            cmbCondicion.focus(false,10);
        }
        msgBox('Debe seleccionar la condici&oacute;n de comparaci&oacute;n',resp);
        return;
    }

    var arrTipoEntrada=[];

    if((tDataSet==1)||(tDataSet==3))
	    objParametro=['17','Valor de par\xE1metro de reporte'];
    if(tDataSet==5)
	    objParametro=['16','Valor de par\xE1metro de formulario'];
    if(tDataSet==10)
	    objParametro=['17','Valor de par\xE1metro de mensaje de env\xEDo'];
    
    
    
    
    
    
    switch(tDataSet)
    {
    	case 1:
        case 3:
        case 5:
        	if(permitirRegistroParametro)
                arrTipoEntrada=[['0','Campo de tabla'],['7','Consulta auxiliar'],['6','Par\xE1metro registrado'],['5','Registrar par\xE1metro'],['11','Valor de almac\xE9n'],['31','Valor de funci\xF3n/C\xE1lculo'],objParametro,['3','Valor de sesi\xF3n'],['4','Valor de sistema']];
            else
                arrTipoEntrada=[['0','Campo de tabla'],['7','Consulta auxiliar'],['11','Valor de almac\xE9n'],['31','Valor de funci\xF3n/C\xE1lculo'],objParametro,['3','Valor de sesi\xF3n'],['4','Valor de sistema']];
            
            if(tDataSet!=1)
            {
                var pos=existeValorMatriz(arrTipoEntrada,'31');
                arrTipoEntrada.splice(pos,1);
            }
            if(tDataSet==5)
            {
            	arrTipoEntrada.push(['20','Valor de campo tipo "Par\xE1metro"']);
            }
        break;
        case 10:
        	 arrTipoEntrada=[['0','Campo de tabla'],['7','Consulta auxiliar'],['11','Valor de almac\xE9n'],['31','Valor de funci\xF3n/C\xE1lculo'],objParametro,['3','Valor de sesi\xF3n'],['4','Valor de sistema']];
        break;
        default:
        	if(permitirRegistroParametro)
                arrTipoEntrada=[['0','Campo de tabla'],['7','Consulta auxiliar'],['6','Par\xE1metro registrado'],['5','Registrar par\xE1metro'],['11','Valor de almac\xE9n'],['31','Valor de funci\xF3n/C\xE1lculo'],['3','Valor de sesi\xF3n'],['4','Valor de sistema']];
            else
                arrTipoEntrada=[['0','Campo de tabla'],['7','Consulta auxiliar'],['11','Valor de almac\xE9n'],['31','Valor de funci\xF3n/C\xE1lculo'],['3','Valor de sesi\xF3n'],['4','Valor de sistema']];
        break;
    }
   
   	if(typeof(arrParametrosGenerales)!='undefined')
    {
    	arrTipoEntrada.push(['18','Valor de par\xE1metro general']);
    }
   
    var cmbTipoValor=crearComboExt('cmbTipoValor',arrTipoEntrada,180,5,350);
    cmbTipoValor.on('select',funcTipoEntradaChangeParam);
    
    var cmbValor=crearComboExt('cmbValorAlmacen',[],180,35);
    cmbValor.setWidth(300);
    cmbValor.hide();
    var cmbAlmacen=crearComboExt('cmbAlmacen',[],180,35);
    cmbAlmacen.setWidth(250);
    cmbAlmacen.hide();
    
    cmbAlmacen.on('select',function(cmb,registro)
    						{
                            	var arrCampos=obtenerCamposDisponibles(registro.get('id'));
                            	gEx('cmbCampoParametro').getStore().loadData(arrCampos);
                            }
    			 )
    						
    
    var cmbCampoParametro=crearComboExt('cmbCampoParametro',[],180,65);
    cmbCampoParametro.setWidth(250);
    cmbCampoParametro.hide();
	var form = new Ext.form.FormPanel(	
										{
											baseCls: 'x-plain',
											layout:'absolute',
											defaultType: 'label',
											items: 	[
														{
                                                        	x:10,
                                                            y:10,
                                                            html:'Tipo de valor a asignar:'
                                                        },
                                                        cmbTipoValor,
                                                        {
                                                        	id:'lblValorAsigna',
                                                        	x:10,
                                                            y:40,
                                                            html:'Valor a asignar:'
                                                        },
                                                        {
                                                        	xtype:'textfield',
                                                        	id:'txtValorConstante',
                                                            width:250,
                                                            x:180,
                                                            y:35
                                                        },
                                                        {
                                                        	xtype:'label',
                                                            y:35,
                                                            x:440,
                                                            hidden:true,
                                                            id:'lblMostrarReferenciaParametro',
                                                            html:'<a href="javascript:mostrarVentanaReferenciaParametro()"><img src="../images/pencil.png"></a>'
                                                        },
                                                        cmbValor,
                                                        {
                                                        	id:'lblAlmacen',
                                                        	x:10,
                                                            y:40,
                                                            html:'Seleccione almac&eacute;n:',
                                                            hidden:true
                                                        },
                                                        cmbAlmacen,
                                                        {
                                                        	id:'lblCampo',
                                                        	x:10,
                                                            y:70,
                                                            html:'Seleccione campo a utilizar:',
                                                            hidden:true
                                                        },
                                                        cmbCampoParametro
													]
										}
									);
	
    
    var comboTmp=document.createElement('select');
	    
	var ventanaAM = new Ext.Window(
									{
										title: 'Asignar valor a par&aacute;metro',
										width: 650,
										height:180,
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
                                                                    	var compTipoValor='';
																		if(cmbTipoValor.getValue()=='')
                                                                        {
                                                                        	msgBox('Debe seleccionar el tipo de entrada al que pertenece el valor a asignar');
                                                                        	return;
                                                                        }
                                                                        var valorUsr;
                                                                        var valor;
                                                                        switch(cmbTipoValor.getValue())
                                                                        {
                                                                        	case '0':
                                                                            	valor=gEx('cmbValorAlmacen').getValue();
                                                                                valorUsr=gEx('cmbValorAlmacen').getRawValue();
                                                                            	compTipoValor='0';
                                                                            break;
                                                                        	case '5':
                                                                            	valor='@'+gEx('txtValorConstante').getValue();
                                                                                valorUsr='[Parámetro: ('+valor+')]';
                                                                                var obj=new Array();
                                                                                obj[0]=valor;
                                                                                obj[1]=valor;
                                                                                compTipoValor=valor;
                                                                                arrParametrosAlmacen.push(obj);
                                                                            break;
                                                                            case '6':
                                                                            	valor=''+gEx('cmbValorAlmacen').getValue();
                                                                                valorUsr='[Parámetro: ('+valor+')]';
                                                                                var obj=new Array();
                                                                                obj[0]=valor;
                                                                                obj[1]=valor;
                                                                                compTipoValor=valor;
                                                                                arrParametrosAlmacen.push(obj);
                                                                            break;
                                                                            case '7':
                                                                            	valor='@';
                                                                                valorUsr='['+gEx('cmbValorAlmacen').getRawValue()+']';
                                                                                compTipoValor=gEx('cmbValorAlmacen').getValue();
                                                                            break;
                                                                            case '11':
                                                                            	
                                                                                valor='@';
                                                                                var arbol=gEx('arbolDataSet').getRootNode();
                                                                                valorUsr='[Almac\xE9n: '+gEx('cmbAlmacen').getRawValue()+', Campo:'+gEx('cmbCampoParametro').getRawValue()+']';
                                                                                var nodoCampo=buscarNodoID(arbol,gEx('cmbCampoParametro').getValue());
                                                                                var nCampo;
                                                                                if(nodoCampo.nCampo!=undefined)
                                                                                	nCampo=nodoCampo.nCampo;
                                                                                else
                                                                                	nCampo=nodoCampo.attributes.nCampo;
                                                                                compTipoValor=gEx('cmbAlmacen').getValue()+'|'+nCampo;
                                                                                
                                                                            break;
                                                                            
                                                                            case '31':
                                                                            	valor='@';
                                                                                valorUsr='[Función: '+gEx('txtValorConstante').getValue()+']';
                                                                                compTipoValor=gEx('txtValorConstante').idFuncion;
                                                                            break;
                                                                            default:
                                                                            	if(cmbValor.getValue()=='')
                                                                                {
                                                                                	msgBox('Debe seleccionar el valor que desea asignar');
                                                                                	return;
                                                                                }
                                                                                var pos=obtenerPosFila(cmbValor.getStore(),'id',cmbValor.getValue());
                                                                                var fila=cmbValor.getStore().getAt(pos);
                                                                                valor='@'+fila.get('valorComp');
                                                                                valorUsr='['+cmbValor.getRawValue()+']';
                                                                                compTipoValor=cmbValor.getValue();
                                                                            break;
                                                                        }            
                                                                        
                                                                        var tipo=cmbTipoValor.getValue();
                                                                        var cmbCampo=gEx('cmbCampo');
                                                                        var campoMyql=cmbCampo.getValue();
                                                                        var campoUsr=cmbCampo.getRawValue();
                                                                        var cmbCondicion=gEx('cmbCondicion');
                                                                        var condicionU;
                                                                        var condicionM;
                                                                        condicionU=condicion.getRawValue();
                                                                        condicionM=condicion.getValue();
                                                                        var valorU=valorUsr;
                                                                        var valorM=valor;
                                                                        var compA='';
                                                                        var compC='';
                                                                         if((condicionM=='in')||(condicionM=='not in'))
                                                                        {
                                                                            compA='(';
                                                                            compC=')';
                                                                        }
                                                                        var cadM=campoMyql+' '+condicionM+' '+compA+valorM+compC;
                                                                        var cadU=campoUsr+' '+condicionU+' '+compA+valorU+compC;
                                                                        if(condicionM.indexOf('like ')!=-1)
                                                                        {
                                                                        	cadM=campoMyql+' '+condicionM;
                                                                            cadM=cadM.replace(/valor/gi,valorM);
                                                                        }
                                                                        filtroUsuario[filtroUsuario.length]=cadU;
                                                                        filtroMysql[filtroMysql.length]=cadM;
                                                                        filtroTipoValor[filtroTipoValor.length]=tipo+'|'+compTipoValor;
                                                                        generarSentencia();
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
    
}

function funcTipoEntradaChangeParam(combo,registro)
{
	var txtValorConstante=gEx('txtValorConstante');
    txtValorConstante.setReadOnly(false);
    txtValorConstante.hide();
    var cmbValor=gEx('cmbValorAlmacen');
    cmbValor.reset();
    cmbValor.hide();
    var lblValorAsigna=gEx('lblValorAsigna');
    lblValorAsigna.hide();
    var cmbAlmacen=gEx('cmbAlmacen');
    cmbAlmacen.hide();
    var cmbCampo=gEx('cmbCampoParametro');
    cmbCampo.hide();
    var lblAlmacen=gEx('lblAlmacen');
    lblAlmacen.hide();
    var lblCampo=gEx('lblCampo');
    lblCampo.hide();
    var datosNodo=nodoSel.id;
    var lblMostrarReferenciaParametro=gEx('lblMostrarReferenciaParametro');
    lblMostrarReferenciaParametro.hide();
    
	switch(registro.get('id'))
    {
    	case '0':
        	cmbValor.getStore().loadData(arrCamposTablas);
            cmbValor.show();
            lblValorAsigna.show();
        break;
    	case '5':
        	txtValorConstante.show();
            lblValorAsigna.show();
        break;
        case '2':
        	cmbValor.getStore().loadData(arrParametrosRep);
        	cmbValor.show();
            lblValorAsigna.show();
        break;
        case '3':
        	cmbValor.getStore().loadData(arrValorSesion);
        	cmbValor.show();
            lblValorAsigna.show();
        break;
        case '4':
        	cmbValor.getStore().loadData(arrValorSistema);
        	cmbValor.show();
            lblValorAsigna.show();
        break;
        case '6':
        	cmbValor.getStore().loadData(arrParametrosAlmacen);
        	cmbValor.show();
            lblValorAsigna.show();
        break;
        case '7':
        	var arrConsultaAux=new Array();
			arrConsultaAux=obtenerAlmacenesDatosDisponibles('2');            
        	cmbValor.getStore().loadData(arrConsultaAux);
        	cmbValor.show();
            lblValorAsigna.show();
        break;
         case '11':
        	var arrAlmacenes=obtenerAlmacenesDatosDisponibles('1');
            cmbAlmacen.getStore().loadData(arrAlmacenes);
        	cmbAlmacen.show();
            lblAlmacen.show();
            lblCampo.show();
            cmbCampo.show();
            cmbCampo.reset();
        break;
        case '16':
        	cmbValor.getStore().loadData(arrProceso);
        	cmbValor.show();
            lblValorAsigna.show();
        break;
        case '17':
        	cmbValor.getStore().loadData(arrParametrosObjeto);
        	cmbValor.show();
            lblValorAsigna.show();
        break;
        case '18':
        	cmbValor.getStore().loadData(arrParametrosGenerales);
        	cmbValor.show();
            lblValorAsigna.show();
            break;
        break;
        case '20':
        	function funcAjax()
            {
                var resp=peticion_http.responseText;
                arrResp=resp.split('|');
                if(arrResp[0]=='1')
                {
                    cmbValor.getStore().loadData(eval(arrResp[1]));
                    cmbValor.show();
                    lblValorAsigna.show();
                }
                else
                {
                    msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
                }
            }
            obtenerDatosWeb('../paginasFunciones/funcionesProyectos.php',funcAjax, 'POST','funcion=340&idFormulario='+gE('idFormulario').value+'&tipoControl=31',true);

        	
        break;
        case '31':
        	lblValorAsigna.show();
        	txtValorConstante.show();
            txtValorConstante.setReadOnly(true);
            lblMostrarReferenciaParametro.show();
        break;
        
    }
}

function vincularAlmacenDatos(iE)
{
	var gridAlmacenesDatos=crearGridAlmacenDatos();
	var form = new Ext.form.FormPanel(	
										{
											baseCls: 'x-plain',
											layout:'absolute',
											defaultType: 'label',
											items: 	[
														{
                                                        	x:10,
                                                            y:10,
                                                        	html:'Elija el almac&eacute;n de datos que desea vincular a la secci&oacute;n seleccionada:'
                                                        },
                                                        gridAlmacenesDatos
													]
										}
									);
	
	var ventanaAM = new Ext.Window(
									{
										title: 'Vincular secci&oacute;n con almac&eacute;n de datos',
										width: 590,
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
																		var fila=gridAlmacenesDatos.getSelectionModel().getSelected();
                                                                        if(fila==null)
                                                                        {
                                                                        	msgBox('Debe seleccionar el almac&eacute;n de datos que desea asignar como almacen de datos maestro de la secci&oacute;n');
                                                                        	return;
                                                                        }
                                                                        function funcAjax()
                                                                        {
                                                                            var resp=peticion_http.responseText;
                                                                            arrResp=resp.split('|');
                                                                            if(arrResp[0]=='1')
                                                                            {
                                                                            	g.gE('div_'+bD(iE)).setAttribute('almacenVinculado','1');
                                                                            	ventanaAM.close();
                                                                            }
                                                                            else
                                                                            {
                                                                                msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
                                                                            }
                                                                        }
                                                                        obtenerDatosWeb('../paginasFunciones/funcionesThot.php',funcAjax, 'POST','funcion=13&idElemento='+iE+'&idAlmacen='+fila.get('idAlmacen'),true);
                                                                        
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

function crearGridAlmacenDatos()
{
	var arrAlmacenes=obtenerAlmacenesDisponibles();
	var dsDatos=arrAlmacenes;
    var alDatos=	new Ext.data.SimpleStore	(
                                                    {
                                                        fields:	[
                                                                    {name: 'idAlmacen'},
                                                                    {name: 'almacen'},
                                                                    {name: 'descripcion'}
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
															header:'Almac&eacute;n',
															width:150,
															sortable:true,
															dataIndex:'almacen'
														},
														{
															header:'Descripci&oacute;n',
															width:300,
															sortable:true,
															dataIndex:'descripcion'
														}
													]
												);
                                                
	var tblGrid=	new Ext.grid.EditorGridPanel	(
                                                        {
                                                         	id:'gridAlmacenesDatos' ,  
                                                            store:alDatos,
                                                            frame:true,
                                                            x:10,
                                                            y:40,
                                                            cm: cModelo,
                                                            height:300,
                                                            width:540,
                                                            sm:chkRow
                                                        }
                                                    );
	return 	tblGrid;	
}

function obtenerAlmacenesDisponibles()
{
	var arbolDataSet=arbolAlmacenEstructDatos;
    var raiz=arbolDataSet.getRootNode();
    var x;
    var arrDatos=new Array();
    var obj;
    for(x=0;x<raiz.childNodes[0].childNodes.length;x++)	
    {
    	obj=new Array();
        obj[0]=raiz.childNodes[0].childNodes[x].id;
        obj[1]=raiz.childNodes[0].childNodes[x].text;
        obj[2]=raiz.childNodes[0].childNodes[x].attributes.descripcion;
        arrDatos.push(obj);
    }
	return arrDatos;
}

function administrarAlmacenDatos(iE)
{
	var arbolAlmacenesVinculados=crearAlbolAlmacenesVinculados(iE);
	var form = new Ext.form.FormPanel(	
										{
											baseCls: 'x-plain',
											layout:'absolute',
											defaultType: 'label',
											items: 	[
														arbolAlmacenesVinculados

													]
										}
									);
	
	var ventanaAM = new Ext.Window(
									{
                                    	id:'vVincularDataset2',
										title: 'Almacenes de datos vinculados con la secci&oacute;n',
										width: 500,
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
	ventanaAM.show();
}					

function buscarAlmacen(id)
{
	var arbolDataSet=arbolAlmacenEstructDatos;
    var raiz=arbolDataSet.getRootNode();
    var x;
    var arrDatos=new Array();
    var obj;
    for(x=0;x<raiz.childNodes[0].childNodes.length;x++)	
    {
    	if(raiz.childNodes[0].childNodes[x].id==id)
        	return raiz.childNodes[0].childNodes[x];
    }
	return null;
}

function crearAlbolAlmacenesVinculados(iE)
{
	
	g.setClase('td_'+bD(iE),'');	
	var cargadorArbol=new Ext.tree.TreeLoader(
												{
													baseParams:{
																	funcion:'14',
                                                                    idElemento:bD(iE)
																},
													dataUrl:'../paginasFunciones/funcionesThot.php'
												}
											)	



    var raiz=new  Ext.tree.AsyncTreeNode	(
                                                  {
                                                      
                                                      text:'DTD',
                                                      draggable:false,
                                                      expanded :true
                                                  }
                                            )

	panelArbol=new Ext.tree.TreePanel	(
                                              {
                                              	  id:'arbolAlmacenesV',
                                                  useArrows:true,
                                                  autoScroll:true,
                                                  animate:false,
                                                  enableDD:true,
                                                  height:330,
                                                  containerScroll:true,
                                                  root:raiz,
                                                  rootVisible:false,
												  loader: cargadorArbol,
                                                  tbar:	[
                                                  			{
                                                                icon:'../images/add.png',
                                                                tooltip:'Agregar los campos seleccionados a la secci&oacute;n padre',
                                                                cls:'x-btn-text-icon',
                                                                handler:function()
                                                                        {
                                                                            var arrCampos=panelArbol.getChecked();
                                                                            if(arrCampos.length==0)
                                                                            {
                                                                            	msgBox('Al menos debe seleccionar un campo para agregar a la secci&oacute;n');
                                                                                return;
                                                                            }
                                                                            var x;
                                                                            var cadCampos='';
                                                                            for(x=0;x<arrCampos.length;x++)
                                                                            {
                                                                            	if(cadCampos=='')
	                                                                            	cadCampos=arrCampos[x].text;
                                                                                else
                                                                                	cadCampos+=','+arrCampos[x].text;
                                                                                
                                                                            }
                                                                            tipoElemento=bD(iE);
                                                                            var obj='{"campos":"'+cadCampos+'","idElemento":"'+bD(iE)+'"}';
			                                                                function funcAjax()
                                                                            {
                                                                                var resp=peticion_http.responseText;
                                                                                arrResp=resp.split('|');
                                                                                if(arrResp[0]=='1')
                                                                                {
                                                                                	var arrControles=g.crearControlSeccion(arrResp[1],bD(iE));
                                                                                    g.tipoElemento='26';
																                    var x;
                                                                                    for(x=0;x<arrControles.length;x++)
                                                                                    	g.insertarControl(arrControles[x]);
                                                                                	gEx('vVincularDataset2').close();	
                                                                                }
                                                                                else
                                                                                {
                                                                                    msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
                                                                                }
                                                                            }
                                                                            obtenerDatosWeb('../paginasFunciones/funcionesThot.php',funcAjax, 'POST','funcion=15&obj='+obj,true);
                                                                            
                                                                        }
                                                            }
                                                  		]
                                                                                                
                                               }
                                          );      
    //panelArbol.expandAll();
   // panelArbol.on('click',funcClickArbol);
    return panelArbol;	
}

function agregarCampoProy(idData)
{
	var idDataSet=idData;
    var selUnaOpcion;
    var cmbOperacion=crearComboExt('cmbOperacion',arrOperaciones,180,5);    
    var lblAceptar='Aceptar';
    if(idData!=undefined)
    {
   	    idDataSet=idData;
        selUnaOpcion=true;
        cmbOperacion.setValue('2');
        cmbOperacion.hide();
        lblAceptar='Siguiente >>';
    }
    else
    {
	    idDataSet=nodoSel.attributes.dSetPadre;
        if(nodoSel.attributes.categoria=='0')
        {
            selUnaOpcion=true;
            cmbOperacion.setValue('2');
            cmbOperacion.hide();
        }
        else
        {
            cmbOperacion.setValue(nodoSel.parentNode.attributes.operacion);
            selUnaOpcion=false;
        }
    }
	var gridCampos=crearGridCampos(selUnaOpcion);
	gridCampos.enable();
	var form = new Ext.form.FormPanel(	
										{
											baseCls: 'x-plain',
											layout:'absolute',
											defaultType: 'label',
											items: 	[
                                            			{
                                                        	id:'lblOperacion',
                                                        	x:10,
                                                            y:10,
                                                            html:'Indique la operaci&oacute;n a realizar:',
                                                            hidden:selUnaOpcion
                                                        },
                                                        cmbOperacion,
                                            			{
                                                        	x:10,
                                                            y:30,
                                                            html:'Seleccione los campos que desea proyectar en su consulta:'
                                                        },
														gridCampos
													]
										}
									);
	
	var ventanaAM = new Ext.Window(
									{
										title: 'Agregar campo a proyectar',
										width: 600,
										height:380,
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
															text: lblAceptar,
															handler: function()
																	{
                                                                          var filas=gridCampos.getSelectionModel().getSelections();
                                                                          if((filas.length==0)&&(lblAceptar=='Aceptar'))
                                                                          {
                                                                              msgBox('Debe seleccionar al menos un campo para relizar la operaci&oacute;n indicada');
                                                                              return;
                                                                          }
                                                                          var cadCampos='';
                                                                          var x;
                                                                          if(filas.length>0)
                                                                          {
                                                                              for(x=0;x<filas.length;x++)
                                                                              {
                                                                                  if(cadCampos=='')
                                                                                      cadCampos=filas[x].get('nCamposO');
                                                                                  else
                                                                                      cadCampos+=','+filas[x].get('nCamposO');
                                                                              }
                                                                              function funcAjax()
                                                                              {
                                                                                  var resp=peticion_http.responseText;
                                                                                  arrResp=resp.split('|');
                                                                                  if(arrResp[0]=='1')
                                                                                  {
                                                                                        arbolAlmacenEstructDatos.getRootNode().reload();
                                                                                        ventanaAM.close();
                                                                                        if(lblAceptar!='Aceptar')
                                                                                            mostrarVentanaModifFiltro();
                                                                                  }
                                                                                  else
                                                                                  {
                                                                                      msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
                                                                                  }
                                                                              }
                                                                              obtenerDatosWeb('../paginasFunciones/funcionesThot.php',funcAjax, 'POST','funcion=18&idDataSet='+idDataSet+'&cadCampos='+cadCampos+'&categoria='+nodoSel.attributes.categoria+'&operacion='+cmbOperacion.getValue(),true);
																			}																		
                                                                            else
                                                                            {
                                                                            	ventanaAM.close();
                                                                            	mostrarVentanaModifFiltro();
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
	llenarDatosCamposDset(ventanaAM,idData);	                            
}

function llenarDatosCamposDset(ventana,idData)
{
	var idDataSet;
    if(idData==undefined)
    	idDataSet=nodoSel.attributes.dSetPadre;
    else
    	idDataSet=idData;
	function funcAjax()
    {
        var resp=peticion_http.responseText;
        arrResp=resp.split('|');
        if(arrResp[0]=='1')
        {
        	arrCamposComp=eval(arrResp[1]);
            gEx('gridCampos').getStore().loadData(arrCamposComp);
            ventana.show();
        }
        else
        {
            msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
        }
    }
    obtenerDatosWeb('../paginasFunciones/funcionesThot.php',funcAjax, 'POST','funcion=17&idDataSet='+idDataSet,true);
}

function llenarCondicionesFiltrado(ventana)
{
	var datosTValor='';
    arrParametrosAlmacen=new Array();
    function funcAjax()
    {
        var resp=peticion_http.responseText;
        arrResp=resp.split('|');
        if(arrResp[0]=='1')
        {
        		var x;
                for(x=0;x<nodoSel.attributes.objJava.condicion.length;x++)
                {
                    filtroUsuario.push(nodoSel.attributes.objJava.condicion[x].tokenUsuario);
                    filtroMysql.push(nodoSel.attributes.objJava.condicion[x].tokenMysql);
                    filtroTipoValor.push(nodoSel.attributes.objJava.condicion[x].tokenTipo);
                    datosTValor=nodoSel.attributes.objJava.condicion[x].tokenTipo.split("|");
                    if((datosTValor[0]=='6')||(datosTValor[0]=='5'))
                    {
                        if(existeValorMatriz(arrParametrosAlmacen,datosTValor[1],0)==-1)
                        {
                            var obj=new Array();
                            obj[0]=datosTValor[1];
                            obj[1]=datosTValor[1];
                            
                            
                            
                            arrParametrosAlmacen.push(obj);
                        }
                	}
                }
                generarSentencia();
                arrCamposTablas=eval(arrResp[1]);
                gEx('cmbCampo').getStore().loadData(arrCamposTablas);
                gEx('cmbCamposOrdenQuery').getStore().loadData(arrCamposTablas);
                gEx('gridOrden').getStore().loadData(nodoSel.attributes.orden);
                idConexionAlmacen=arrResp[2];
                
                ventana.show();
                gEx('txtNoRegistros').setValue(nodoSel.attributes.numRegistros);
                var chkDistinct=gEx('chkDistinct');
                chkDistinct.setValue(nodoSel.attributes.diferenteValor);
                if(nodoSel.attributes.tipoAlmacen=='1')
                {
                	chkDistinct.hide();
                }

        }
        else
        {
            msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
        }
    }
    obtenerDatosWeb('../paginasFunciones/funcionesThot.php',funcAjax, 'POST','funcion=8&idDataSet='+nodoSel.attributes.id,true);

}

function mostrarVentanaModificarNombre()
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
                                                            html:'Nombre:'
                                                           
                                                        },
                                                        {
                                                        	x:100,
                                                            y:5,
                                                            xtype:'textfield',
                                                            id:'txtNombre',
                                                            width:300,
                                                            value:nodoSel.text
                                                            
                                                        },
                                                        
                                                        {
                                                        	x:10,
                                                            y:40,
                                                            html:'Descripci&oacute;n:'
                                                        },
                                                        {
                                                        	x:100,
                                                            y:35,
                                                            xtype:'textarea',
                                                            id:'txtDescripcion',
                                                            width:300,
                                                            height:80,
                                                            value:nodoSel.attributes.descripcion
                                                            
                                                        }

													]
										}
									);
	
	var ventanaAM = new Ext.Window(
									{
										title: 'Guardar almac&eacute;n de datos',
										width: 500,
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
                                                                	gEx('txtNombre').focus(false,500);
																}
															}
												},
										buttons:	[
														{
															
															text: '<?php echo $etj["lblBtnAceptar"]?>',
															handler: function()
																	{
                                                                    	var txtNombre=gEx('txtNombre') ;
                                                                        var txtDescripcion=gEx('txtDescripcion');
                                                                        if(txtNombre.getValue()=='')
                                                                        {
                                                                        	function resp()
                                                                            {
                                                                            	txtNombre.focus();
                                                                            }
                                                                        	msgBox('Debe ingresar el nombre del almacen de datos',resp);
                                                                        }
                                                                        var nombre=txtNombre.getValue();
                                                                        var descripcion=txtDescripcion.getValue();
                                                                        var idAlmacen=nodoSel.id;
                                                                        function funcAjax()
                                                                        {
                                                                            var resp=peticion_http.responseText;
                                                                            arrResp=resp.split('|');
                                                                            if(arrResp[0]=='1')
                                                                            {
                                                                            	ventanaAM.close();
                                                                                arbolAlmacenEstructDatos.getRootNode().reload();
                                                                            }
                                                                            else
                                                                            {
                                                                                msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
                                                                            }
                                                                        }
                                                                        obtenerDatosWeb('../paginasFunciones/funcionesThot.php',funcAjax, 'POST','funcion=21&idAlmacen='+idAlmacen+'&nombre='+cv(nombre)+'&descripcion='+cv(descripcion),true);
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

function mostrarVentanaTablasInvolucradasModif(ocultarProy)
{
	
	var cmbConexion=crearComboExt('cmbConexion',arrConexiones,110,5,250);
    
	var gridTablasInv=crearGridConsiderarTablas(ocultarProy,true);
    gridTablasInv.getStore().on('add',funcAgregaFila);
    
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
                                                            html:'Conexi&oacute;n:'
                                                        },
                                                        cmbConexion,
														gridTablasInv
													]
										}
									);
	
    
     btnSiguiente=new Ext.Button	(
                                        {
                                            text: 'Siguiente >>',
                                            minWidth:80,
                                            id:'btnFinalizar',
                                            listeners:	{
                                                            click:
                                                                    {
                                                                        fn:function()
                                                                        {	
                                                                        	ventanaAM.close();
                                                                            agregarCampoProy(nodoSel.id);
                                                                            
                                                                        }
                                                                    }
                                                        }
                                        }
                                    )
    
	var ventanaAM = new Ext.Window(
									{
										title: 'Tablas involucradas en el almac&eacute;n',
										width: 700,
										height:370,
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
														btnSiguiente,
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
	llenarTablasInvolucradas(ventanaAM);                                
	
}

function funcAgregaFila(almacen,registro)
{
	function funcAjax()
    {
        var resp=peticion_http.responseText;
        arrResp=resp.split('|');
        if(arrResp[0]=='1')
        {
        	
        }
        else
        {
            msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
        }
    }
    obtenerDatosWeb('../paginasFunciones/funcionesThot.php',funcAjax, 'POST','funcion=23&nTabla='+registro[0].get('nomTablaOriginal')+'&idAlmacen='+nodoSel.id,true);
}

function llenarTablasInvolucradas(ventanaAM)
{
	var idAlmacen=nodoSel.id;
    function funcAjax()
    {
        var resp=peticion_http.responseText;
        arrResp=resp.split('|');
        if(arrResp[0]=='1')
        {
        	gEx('gridConsidera').getStore().loadData(eval(arrResp[1]));
            ventanaAM.show();
            gEx('cmbConexion').setValue(arrResp[2]);
            idConexionAlmacen=arrResp[2];
            gEx('cmbConexion').disable();
        }
        else
        {
            msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
        }
    }
    obtenerDatosWeb('../paginasFunciones/funcionesThot.php',funcAjax, 'POST','funcion=22&idAlmacen='+idAlmacen,true);
}

function mostrarVentanaErrores()
{
	var gridErrores=crearGridErrores();
    var form = new Ext.form.FormPanel(	
										{
											baseCls: 'x-plain',
											layout:'absolute',
											defaultType: 'label',
											items: 	[
														gridErrores

													]
										}
									);
	
	var ventanaAM = new Ext.Window(
									{
										title: 'Listado de errores detectados en la consulta',
										width: 880,
										height:380,
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

function crearGridErrores()
{
	var dsDatos=nodoSel.attributes.arrErrores;
    var alDatos=	new Ext.data.SimpleStore	(
                                                {
                                                    fields:	[
                                                                
                                                                {name: 'errores'}
                                                            ]
                                                }
                                            );

    alDatos.loadData(dsDatos);
	var chkRow=new Ext.grid.CheckboxSelectionModel();
	
	var cModelo= new Ext.grid.ColumnModel   	(
												 	[
													 	new  Ext.grid.RowNumberer(),
														
														{
															header:'Errores detectados',
															width:780,
															sortable:true,
															dataIndex:'errores'
														}
													]
												);
                                                
	var tblGrid=	new Ext.grid.EditorGridPanel	(
                                                        {
                                                            
                                                            store:alDatos,
                                                            frame:true,
                                                            y:10,
                                                            cm: cModelo,
                                                            height:260,
                                                            width:850,
                                                            sm:chkRow
                                                        }
                                                    );
	return 	tblGrid;		
}

function mostrarVentanaAlmacenesProceso()
{
	var gridAlmacenProceso=crearGridAlmacenesProceso();
	var form = new Ext.form.FormPanel(	
										{
											baseCls: 'x-plain',
											layout:'absolute',
											defaultType: 'label',
											items: 	[
														{
                                                        	html:'Selecciones el almac&eacute;n que desea utilizar:',
                                                            x:10,
                                                            y:10
                                                        },
														gridAlmacenProceso
													]
										}
									);
	
	var ventanaAM = new Ext.Window(
									{
										title: 'Almacenes del proceso',
										width: 545,
										height:410,
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
																		 var filaSel= gridAlmacenProceso.getSelectionModel().getSelected();
                                                                        if(filaSel==null)
                                                                        {
                                                                            Ext.MessageBox.alert(lblAplicacion,'Debe selecionar la tabla en la cual se basar&aacute; su consulta');
                                                                            return;
                                                                        }
                                                                        var nomTablaOriginal=filaSel.get('nomTablaOriginal');
                                                                        var almacenDestino=gEx('gridConsidera').getStore();
                                                                        frmProceso=true;

                                                                        if(obtenerPosFila(almacenDestino,'nomTablaOriginal',nomTablaOriginal)==-1)
                                                                        {
                                                                            var nFila=new regTabla  (
                                                                                                        {
                                                                                                            nomTablaOriginal:filaSel.get('nomTablaOriginal'),
                                                                                                            tabla:filaSel.get('tabla'),
                                                                                                            tipoTabla:filaSel.get('tipoTabla'),
                                                                                                            proceso:filaSel.get('proceso')
                                                                                                        }
                                                                                                    )
                                                                            almacenDestino.add(nFila);
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
}

function crearGridAlmacenesProceso()
{
	var dsDatos=<?php echo $arrModulosProceso?>;
    var alDatos=	new Ext.data.SimpleStore	(
                                                    {
                                                        fields:	[
                                                                    {name: 'nomTablaOriginal'},
                                                                    {name: 'tabla'},
                                                                    {name:'tipoTabla'},
                                                                    {name:'proceso'}
                                                                    
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
															header:'Nombre almac&eacute;n',
															width:250,
															sortable:true,
															dataIndex:'tabla'
														},
                                                        {
															header:'Tipo almac&eacute;n',
															width:150,
															sortable:true,
															dataIndex:'tipoTabla'
														}
													]
												);
                                                
	var tblGrid=	new Ext.grid.EditorGridPanel	(
                                                        {
                                                            id:'gridAlmacenesProceso',
                                                            store:alDatos,
                                                            frame:true,
                                                            x:10,
                                                            y:40,
                                                            cm: cModelo,
                                                            height:260,
                                                            width:500,
                                                            sm:chkRow
                                                        }
                                                    );
	return 	tblGrid;		
}

function obtenerAlmacenesDisponibles()
{
	var arbolDataSet=gEx(idArbolDataSet);
    var raiz=arbolDataSet.getRootNode();
    var x;
    var arrDatos=new Array();
    var obj;
    for(x=0;x<raiz.childNodes[0].childNodes.length;x++)	
    {
    	obj=new Array();
        obj[0]=raiz.childNodes[0].childNodes[x].id;
        obj[1]=raiz.childNodes[0].childNodes[x].text;
        obj[2]=raiz.childNodes[0].childNodes[x].attributes.descripcion;
        arrDatos.push(obj);
    }
	return arrDatos;
}

function obtenerAlmacenesDatosDisponibles(tAlmacen)
{
	var nodoAlmacen=null;
	switch(tAlmacen)
    {
    	case 1:
    	case '1':
        	nodoAlmacen=gEx(idArbolDataSet).getNodeById('0');
        break;
        case 2:
        case '2':
        	nodoAlmacen=gEx(idArbolDataSet).getNodeById('-10');
        break;
        case 3:
        case '3':
        	nodoAlmacen=gEx(idArbolDataSet).getNodeById('-20');
        break;
    }
    if(nodoAlmacen==null)	
    	return [];
     	
    var arrHijos=obtenerHijosNodoArbol(nodoAlmacen);    
	var x;
    var arrDatos=new Array();
    var obj;
    for(x=0;x<arrHijos.length;x++)	
    {
    	obj=new Array();
        obj[0]=arrHijos[x].id;
        obj[1]=arrHijos[x].text;
        arrDatos.push(obj);
    }
	return arrDatos;
}

function obtenerCamposDisponibles(id,getCampoOriginal)
{
	var nodoAlmacen=buscarNodoID(gEx(idArbolDataSet).getRootNode(),'c_'+id);
    var arrHijos=obtenerHijosNodoArbol(nodoAlmacen);    
	var x;
    var arrDatos=new Array();
    var obj;
    for(x=0;x<arrHijos.length;x++)	
    {
    	obj=new Array();
        if(getCampoOriginal)
        {
        	if(arrHijos[x].attributes)
        		obj[0]=arrHijos[x].attributes.nCampo;
            else
            	obj[0]=arrHijos[x].nCampo;
        }
       	else
        	obj[0]=arrHijos[x].id;
        obj[1]=arrHijos[x].text;
        obj[2]=arrHijos[x].id;
        arrDatos.push(obj);
    }
	return arrDatos;
}


function mostrarInstanciaVentanaSeleccionTabla(objConf)
{
	var cmbConexion=crearComboExt('cmbConexion',arrConexiones,110,5,250);
    cmbConexion.setValue('0');
    cmbConexion.on('select',function(cmbRegistro,registro)
    						{
                            	gEx('gridTabla').getStore().removeAll();
                            	gEx('gridTabla').getStore().load({url: '../paginasFunciones/funcionesFormulario.php',params:{funcion:46,idConexion:cmbConexion.getValue()}});
                                idConexionAlmacen=registro.get('id');
                            }
    				)
    idConexionAlmacen=0;
    arrParametrosAlmacen=new Array();
	var alDatos = new Ext.data.JsonStore	(
                                                {
                                                    root: 'registros',
                                                    totalProperty: 'numReg',
                                                    idProperty: 'nomTablaOriginal',
                                                    fields:	[
                                                                {name:'nomTablaOriginal'},
                                                                {name:'tabla'}, 
                                                                {name:'tipoTabla'},
                                                                {name:'proceso'}
                                                            ],
                                                    remoteSort:false,
                                                    proxy: new Ext.data.HttpProxy	(
                                                                                        {
                                                                                            url: '../paginasFunciones/funcionesFormulario.php'
                                                                                            
                                                                                        }
                                                                                    )
                                                }
                                            );  
                                            
                                            
	var filters = new Ext.ux.grid.GridFilters	(
    												{
                                                    	filters:	[
                                                        				{
                                                                            type:'string',
                                                                           	dataIndex:'tabla' 
																		},
                                                                        {
                                                                            type:'list',
                                                                           	dataIndex:'tipoTabla',
                                                                            phpMode:true,
                                                                            options:	[
                                                                            				{
                                                                                            	id:'1',
                                                                                                text:'Formulario Din&aacute;mico'
                                                                                            },
                                                                            				{
                                                                                            	id:'2',
                                                                                                text:'Sistema'
                                                                                            }
                                                                            			] 
																		},
                                                                        {
                                                                            type:'string',
                                                                           	dataIndex:'proceso' 
																		}
                                                        			]
                                                    }
                                                );                                                                                                                           
   
   
    var cmFrmDTD= new Ext.grid.ColumnModel   	(
                                                    [
                                                        new  Ext.grid.RowNumberer({width:35}),
                                                        {
                                                            header:'Tabla',
                                                            width:250,
                                                            dataIndex:'tabla',
                                                            sortable:true
                                                        },
                                                        {
                                                        	header:'Tipo',
                                                            width:130,
                                                            sortable:true,
                                                            dataIndex:'tipoTabla'
                                                        },
                                                        {
                                                        	header:'Proceso',
                                                            width:180,
                                                            sortable:true,
                                                            dataIndex:'proceso'
                                                        }
                                                       
                                                    ]
                                                );
    
    
    var tblOpciones=	new Ext.grid.GridPanel	(
                                                        {
                                                        	x:10,
                                                            y:40,
                                                            id:'gridTabla',
                                                            store:alDatos,
                                                            frame:true,
                                                            loadMask:true,
                                                            cm: cmFrmDTD,
                                                            height:300,
                                                            width:630,
                                                            plugins: filters
                                                            
                                                        }
                                                    );
    
    
                            
    
    
    
    
    
    
    var form = new Ext.form.FormPanel(	
                                        {
                                            baseCls: 'x-plain',
                                            layout:'absolute',
                                            defaultType: 'textfield',
                                            items: 	[
                                            			{
                                                        	x:10,
                                                            y:10,
                                                            xtype:'label',
                                                            html:'Conexi&oacute;n:'
                                                        },
                                                        cmbConexion,
                                                        tblOpciones
                                                    ]
                                        }
                                    );
    
   
   
    btnAgregar=new Ext.Button	(
                                        {
                                            text: 'Agregar tabla',
                                            minWidth:80,
                                            id:'btnAgregar',
                                            listeners:	{
                                                            click:
                                                                    {
                                                                        fn:function()
                                                                        {
                                                                            
                                                                            var filaSel= tblOpciones.getSelectionModel().getSelected();
                                                                            if(filaSel==null)
                                                                            {
                                                                                Ext.MessageBox.alert(lblAplicacion,'Debe selecionar la tabla en la cual se basar&aacute; su consulta');
                                                                                return;
                                                                            }
                                                                            if(objConf.funcionSeleccion)
                                                                            {
                                                                            	objConf.funcionSeleccion(filaSel,ventanaSelTabla,idConexionAlmacen);
                                                                            }
                                                                            else
                                                                            	ventanaSelTabla.close();
                                                                        }
                                                                    }
                                                        }
                                        }
                                    )
   
    var tVentana='Seleccione la tabla de que desea utilizar';
    if(objConf.titulo)
    	tVentana=objConf.titulo;
    ventanaSelTabla = new Ext.Window(
                                            {
                                                title: tVentana,
                                                width: 680 ,
                                                height:430,
                                                minWidth: 300,
                                                minHeight: 100,
                                                layout: 'fit',
                                                plain:true,
                                                modal:true,
                                                bodyStyle:'padding:5px;',
                                                buttonAlign:'center',
                                                items: 	[
                                                            form
                                                        ],
                                                listeners : {
                                                            show : {
                                                                        buffer : 10,
                                                                        fn : function() 
                                                                        {
                                                          			                  
                                                                        }
                                                                    }
                                                        },
                                                buttons:	[
                                                                btnAgregar,
                                                                {
                                                                    text: 'Cancelar',
                                                                    handler:function()
                                                                    {
                                                                        ventanaSelTabla.close();
                                                                    }
                                                                }
                                                            ]
                                            }
                                        );
                                        
	tblOpciones.getStore().load(
    								{	
                                    	params:	{
                                            		funcion:46,
                                                    idConexion:cmbConexion.getValue()
                                        		}	
                                    }
                               ); 
	tblOpciones.on('dblclick',function()
    							{
                                	gEx('btnAgregar').fireEvent('click');
                                }
    				);                                                                      	
	ventanaSelTabla.show();                                        
	
}

function mostrarVentanaAsignarParam()
{
    switch(tDataSet)
    {
    	case 1:
        	objParametro=['17','Valor de par\xE1metro de reporte'];
        case 3:
        	if(tDataSet==3)
	        	objParametro=['17','Valor de par\xE1metro de c\xE1lculo'];
        	arrTipoEntrada=[['7','Consulta auxiliar'],['11','Valor de almac\xE9n'],['3','Valor de sesi\xF3n'],['4','Valor de sistema']];
        break;
        case 5:
        	objParametro=['16','Valor de par\xE1metro de formulario'];
            arrTipoEntrada=[['7','Consulta auxiliar'],['11','Valor de almac\xE9n'],['24','Valor de control de formulario'],['3','Valor de sesi\xF3n'],['4','Valor de sistema']];
        break;
        default:
        	 arrTipoEntrada=[['7','Consulta auxiliar'],['11','Valor de almac\xE9n'],['3','Valor de sesi\xF3n'],['4','Valor de sistema']];
        break;
    }
    
    var cmbTipoValor=crearComboExt('cmbTipoValor',arrTipoEntrada,160,5,250);
    cmbTipoValor.on('select',funcTipoEntradaChange);
    var cmbValor=crearComboExt('cmbValor',[],160,35);
    cmbValor.setWidth(250);
    cmbValor.hide();
    var cmbAlmacen=crearComboExt('cmbAlmacen',[],160,35);
    cmbAlmacen.setWidth(250);
    cmbAlmacen.hide();
    var cmbControlFormulario=crearComboExt('cmbControlFormulario',[],160,35,250);
    cmbControlFormulario.hide();
    cmbAlmacen.on('select',function(cmb,registro)
    						{
                            	var arrCampos=obtenerCamposDisponibles(registro.get('id'));
                            	gEx('cmbCampoParametro').getStore().loadData(arrCampos);
                            }
    			 )
    						
    
    var cmbCampoParametro=crearComboExt('cmbCampoParametro',[],160,65);
    cmbCampoParametro.setWidth(250);
    cmbCampoParametro.hide();
	var form = new Ext.form.FormPanel(	
										{
											baseCls: 'x-plain',
											layout:'absolute',
											defaultType: 'label',
											items: 	[
														{
                                                        	x:10,
                                                            y:10,
                                                            html:'Tipo de valor a asignar:'
                                                        },
                                                        cmbTipoValor,
                                                        {
                                                        	id:'lblValorAsigna',
                                                        	x:10,
                                                            y:40,
                                                            html:'Valor a asignar:'
                                                        },
                                                        {
                                                        	id:'lblControlFrm',
                                                        	x:10,
                                                            y:40,
                                                            hidden:true,
                                                            html:'Control de formulario:'
                                                        },
                                                        cmbControlFormulario,
                                                        {
                                                        	xtype:'textfield',
                                                        	id:'txtValorConstante',
                                                            x:160,
                                                            y:35
                                                        },
                                                        cmbValor,
                                                        {
                                                        	id:'lblAlmacen',
                                                        	x:10,
                                                            y:40,
                                                            html:'Seleccione almac&eacute;n:',
                                                            hidden:true
                                                        },
                                                        cmbAlmacen,
                                                        {
                                                        	id:'lblCampo',
                                                        	x:10,
                                                            y:70,
                                                            html:'Seleccione campo a utilizar:',
                                                            hidden:true
                                                        },
                                                        cmbCampoParametro
													]
										}
									);
	
	var ventanaAM = new Ext.Window(
									{
										title: 'Asignar valor a par&aacute;metro',
										width: 460,
										height:180,
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
																		if(cmbTipoValor.getValue()=='')
                                                                        {
                                                                        	msgBox('Debe seleccionar el tipo de entrada al que pertenece el valor a asignar');
                                                                        	return;
                                                                        }
                                                                        var valorUsr;
                                                                        var valor;
                                                                        switch(cmbTipoValor.getValue())
                                                                        {
                                                                        	case '1':
                                                                            	valor=gEx('txtValorConstante').getValue();
                                                                                valorUsr=valor;
                                                                            break;
                                                                            case '7':
                                                                            	valor='@';
                                                                                if(gEx('cmbValorAlmacen'))
                                                                                {
                                                                                	valorUsr='['+gEx('cmbValorAlmacen').getRawValue()+']';
                                                                                    compTipoValor=gEx('cmbValorAlmacen').getValue();
                                                                                }
                                                                                else
                                                                                {
                                                                                    valorUsr='['+gEx('cmbValor').getRawValue()+']';
                                                                                    compTipoValor=gEx('cmbValor').getValue();
                                                                                    
                                                                                }
                                                                            break;
                                                                            case '11':
                                                                                valor='@';
                                                                                var arbol=gEx('arbolDataSet').getRootNode();
                                                                                valorUsr='[Almac\xE9n: '+gEx('cmbAlmacen').getRawValue()+', Campo:'+gEx('cmbCampoParametro').getRawValue()+']';
                                                                                var nodoCampo=buscarNodoID(arbol,gEx('cmbCampoParametro').getValue());
                                                                                var nCampo;
                                                                                if(nodoCampo.nCampo!=undefined)
                                                                                	nCampo=nodoCampo.nCampo;
                                                                                else
                                                                                	nCampo=nodoCampo.attributes.nCampo;
                                                                                compTipoValor=gEx('cmbAlmacen').getValue()+'|'+nCampo;
                                                                            break;
                                                                            case '24':
                                                                            	valor=gEx('cmbControlFormulario').getValue();
                                                                                valorUsr='[Control: '+gEx('cmbControlFormulario').getRawValue()+']';
                                                                                compTipoValor=gEx('cmbControlFormulario').getValue();
                                                                            break;
                                                                            default:
                                                                            	if(cmbValor.getValue()=='')
                                                                                {
                                                                                	msgBox('Debe seleccionar el valor que desea asignar');
                                                                                	return;
                                                                                }
                                                                                valor=cmbValor.getValue();
                                                                                valorUsr=cmbValor.getRawValue();
                                                                            break;
                                                                        }                                                                        
                                                                        var tipo=cmbTipoValor.getValue();
                                                                        var almacen=nodoSel.attributes.dSetPadre;
                                                                        var param=nodoSel.attributes.nParametro;
                                                                        
                                                                        function funcAjax()
                                                                        {
                                                                            var resp=peticion_http.responseText;
                                                                            arrResp=resp.split('|');
                                                                            if(arrResp[0]=='1')
                                                                            {
                                                                            	if(tipo=='1')
	                                                                            	nodoSel.setText(nodoSel.attributes.nParametro+' (<b>Valor:</b> '+valorUsr+')');
                                                                                else
                                                                                	nodoSel.setText(nodoSel.attributes.nParametro+' (<b>Valor:</b> ['+valorUsr+'])');
                                                                                ventanaAM.close();
                                                                            }
                                                                            else
                                                                            {
                                                                                msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
                                                                            }
                                                                        }
                                                                        obtenerDatosWeb('../paginasFunciones/funcionesThot.php',funcAjax, 'POST','funcion=16&valor='+valor+'&valorUsr='+valorUsr+'&parametro='+bE(param)+'&tipo='+tipo+'&almacen='+almacen,true);

                                                                    	
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

function funcTipoEntradaChange(combo,registro)
{
	gEx('cmbControlFormulario').hide();
	gEx('lblControlFrm').hide();
	var txtValorConstante=gEx('txtValorConstante');
    txtValorConstante.hide();
    var cmbValor=gEx('cmbValor');
    cmbValor.hide();
    var lblValorAsigna=gEx('lblValorAsigna');
    lblValorAsigna.hide();
    var lblAlmacen=gEx('lblAlmacen');
    lblAlmacen.hide();
    var cmbAlmacen=gEx('cmbAlmacen');
    cmbAlmacen.hide();
    var cmbCampo=gEx('cmbCampoParametro');
    cmbCampo.hide();
    var lblCampo=gEx('lblCampo');
    lblCampo.hide();
    var datosNodo=nodoSel.id.split('_');
	switch(registro.get('id'))
    {
    	case '1':
        	txtValorConstante.show();
            lblValorAsigna.show();
        break;
        case '2':
        	cmbValor.getStore().loadData(arrParametrosRep);
        	cmbValor.show();
            lblValorAsigna.show();
        break;
        case '3':
        	cmbValor.getStore().loadData(arrValorSesion);
        	cmbValor.show();
            lblValorAsigna.show();
        break;
        case '4':
        	cmbValor.getStore().loadData(arrValorSistema);
        	cmbValor.show();
            lblValorAsigna.show();
        break;
        case '7':
        	var arrConsultaAux=new Array();
            var arbolDataSet=gEx('arbolDataSet');
            var raiz=arbolDataSet.getRootNode();
            var nodoConsulta=raiz.childNodes[1];
            var x;
            var obj;
            for(x=0;x<nodoConsulta.childNodes.length;x++)
            {
            	if(datosNodo[1]!=nodoConsulta.childNodes[x].id)
                {
                    obj=new Array();
                    obj[0]=nodoConsulta.childNodes[x].id;
                    obj[1]='Consulta: '+nodoConsulta.childNodes[x].text;
                    arrConsultaAux.push(obj);
                }
           	}
        	cmbValor.getStore().loadData(arrConsultaAux);
        	cmbValor.show();
            lblValorAsigna.show();
        break;
        case '11':
        	var arrAlmacenes=obtenerAlmacenesDatosDisponibles('1');
            cmbAlmacen.getStore().loadData(arrAlmacenes);
        	cmbAlmacen.show();
            lblAlmacen.show();
            lblCampo.show();
            cmbCampo.show();
            cmbCampo.reset();

        break;
        case '16':
        	cmbValor.getStore().loadData(arrParametrosObjeto);
        	cmbValor.show();
            lblValorAsigna.show();
        break;
        case '17':
        	cmbValor.getStore().loadData(arrParametrosObjeto);
        	cmbValor.show();
            lblValorAsigna.show();
        break;
        
        case '24':
        	gEx('lblControlFrm').show();
            gEx('cmbControlFormulario').show();
            function funcAjax()
            {
                var resp=peticion_http.responseText;
                arrResp=resp.split('|');
                if(arrResp[0]=='1')
                {
                    gEx('cmbControlFormulario').getStore().loadData(eval(arrResp[1]));
                }
                else
                {
                    msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
                }
            }
            obtenerDatosWeb('../paginasFunciones/funcionesFormulario.php',funcAjax, 'POST','funcion=204&t=2,3,4,5,6,7,8,11,14,15,16,21,22,24,31&f='+gE('idFormulario').value,true);

        break;
        
    }
}

function mostrarVentanaReferenciaParametro()
{
	var cmbTipoValor=gEx('cmbTipoValor');
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
                                                                            
                                                funcionSeleccionada(r, gEx('vAgregarExp'));   
                                            }
    mostrarVentanaExpresion(funcionSeleccionada,true);
    
}

function funcionSeleccionada(fila,ventana)
{
	gEx('txtValorConstante').setValue(fila.get('nombreConsulta'));
    gEx('txtValorConstante').idFuncion=fila.get('idConsulta');
    ventana.close();
}