<?php
	session_start();
	include("configurarIdiomaJS.php");
	include("conexionBD.php");
	
	$consulta="SELECT id__742_tablaDinamica,agendarDiaInhabil,accionDiaNoHabil FROM _742_tablaDinamica";
	$fConfiguracion=$con->obtenerPrimeraFila($consulta);
	
	$agendarDiaInhabil=1;
	
	$accionDiaNoHabil=1;
	$listDias="";
	if($fConfiguracion)
	{
		$agendarDiaInhabil=$fConfiguracion[1];
		$accionDiaNoHabil=$fConfiguracion[2];
		$consulta="SELECT dia FROM _742_diasValidosFechaPago WHERE idReferencia=".$fConfiguracion[0];
		$listDias=$con->obtenerListaValores($consulta);
	}
	if($listDias=="")
		$listDias=-1;
	$fechasNoHabiles="";
	if($fConfiguracion&&($fConfiguracion[1]==0))
	{
		$consulta="SELECT fechaInicio,fechaFin FROM 4525_fechaCalendarioDiaHabil WHERE afectaPago=1 ORDER BY fechaInicio";
		$res=$con->obtenerFilas($consulta);
		while($fila=mysql_fetch_row($res))
		{
			$obj="[Date.parseDate('".$fila[0]."','Y-m-d'),Date.parseDate('".$fila[1]."','Y-m-d')]";
			if($fechasNoHabiles=="")
				$fechasNoHabiles=$obj;
			else
				$fechasNoHabiles.=",".$obj;
		}
	}

?>
var arrTiposDescuento=[['1','Costo total'],['2','Monto descuento'],['3','Porcentaje']];
var arrDiasPermitidos=[<?php echo $listDias?>];
var agendarDiaInhabil=<?php echo $agendarDiaInhabil?>;
var accionDiaNoHabil=<?php echo $accionDiaNoHabil?>;
var fechasNoHabiles=[<?php echo $fechasNoHabiles?>];
var regDescuento=null;

function ingresarCostoEstandar(iP)
{

	var lblServicio='';
    var x;
    var arrServicios=new Array();
    var arrFilas=null;
    var tipoArreglo=typeof(arrNodos);
    
    var gridCosteo=false;
    if((typeof('esGridCosteo')!='undefined')&& esGridCosteo)
    {
    	gridCosteo=true;
    }
    
    if(gridCosteo)
    {
    
    	lblServicio=gEx('cmbConceptos').getRawValue();
        arrNodos=gEx('gCostos').getSelectionModel().getSelections();
        if(gEx('gCostos').disabled)
        {
        	arrNodos=[];
            
            
            var reg=crearRegistro	(
            							[
                                            {name:'id'},
                                            {name:'idProgramaEducativo'},
                                            {name:'programaEducativo'},
                                            {name: 'idPlanEstudios'},
                                            {name: 'planEstudios'},
                                            {name:'idGrado'},
                                            {name:'grado'},
                                            {name:'costo'}
                                        ]
            						)
            
            var r=new reg	(
            					{
                                	id:gEx('cmbPlantel').getValue()+'_-1_-1_-1_'+gEx('cmbConceptos').getValue(),
                                    idProgramaEducativo:'-1',
                                    programaEducativo:'',
                                    idPlanEstudios:'-1',
                                    planEstudios:'',
                                    idGrado:'-1',
                                    grado:'',
                                    costo:normalizarValor(gEx('tCosto').getValue())
                                    
                                }
                                
            				)
        	arrNodos.push(r);
        }
        
    }
    else
    {
        for(x=0;x<arrNodos.length;x++)
        {
            if(existeValorArreglo(arrServicios,arrNodos[x].text)==-1)
            {
                arrServicios.push(arrServicios,arrNodos[x].text);
                if(lblServicio=='')
                    lblServicio=arrNodos[x].text;
                else
                    lblServicio+=', '+arrNodos[x].text;
            }	
        }
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
                                                            html:'<span style="color:#000;"><b>Servicio:</b></span>'
                                                        },
                                                        {
                                                        	x:80,
                                                            y:10,
                                                            html:'<span style="color:#900" title="'+lblServicio+'" alt="'+lblServicio+'"><b>'+Ext.util.Format.ellipsis( lblServicio, 75 )+'</b></span>'
                                                        },
                                                        {
                                                        	x:10,
                                                            y:40,
                                                            html:'<span style="color:#000;"><b>Costo del servicio:</b></span>'
                                                        },
                                                        {
                                                        	id:'txtCosto',
                                                        	x:120,
                                                            y:35,
                                                            width:110,
                                                            xtype:'numberfield',
                                                            allowDecimals:true,
                                                            allowNegative:false
                                                        },
                                                        {
                                                        	x:260,
                                                            y:40,
                                                            html:'<span style="color:#000;"><b><span style="color:#F00">*</span> Fecha de vencimiento:</b></span>'
                                                        },
                                                        {
                                                        	x:390,
                                                            y:35,
                                                            disabled:(consideraFechaVencimiento==0),
                                                            id:'dteFechaVencimiento',
                                                            xtype:'datefield',
                                                            minValue:'<?php echo date("Y-m-d")?>'
                                                        },
                                                        {
                                                        	x:10,
                                                            y:70,
                                                            html:'<span style="color:#F00">*</span> S&oacute;lo se aplicar&aacute; a aquellos servicios marcados como "Considera fecha de vencimiento"'
                                                        }

													]
										}
									);
	
	var ventanaAM = new Ext.Window(
									{
										title: 'Ingresar costo',
										width: 550,
										height:170,
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
                                                                        gEx('txtCosto').focus(true,500);
                                                                    }
                                                                }
                                                    },
										buttons:	[
														{
															
															text: '<?php echo $etj["lblBtnAceptar"]?>',
															handler: function()
																	{
																		var txtCosto=gEx('txtCosto');
                                                                        var dteFechaVencimiento=gEx('dteFechaVencimiento');
                                                                        if(txtCosto.getValue()=='')
                                                                        {
                                                                        	function resp()
                                                                            {
                                                                            	txtCosto.focus();
                                                                            }
                                                                            msgBox('El costo del concepto ingresado no es v&aacute;lido',resp);
                                                                            return;
                                                                        }
                                                                        
                                                                        if(consideraFechaVencimiento==1)
                                                                        {
                                                                        	if(dteFechaVencimiento.getValue()=='')
                                                                            {
                                                                                function resp2()
                                                                                {
                                                                                    dteFechaVencimiento.focus();
                                                                                }
                                                                                msgBox('Debe ingresar la fecha de vencimiento de los servicios seleccionados',resp2);
                                                                                return;
                                                                            }
                                                                        }
                                                                        
                                                                        var fVencimiento='';
                                                                        if(dteFechaVencimiento.getValue()!='')
                                                                        {
                                                                        	fVencimiento=dteFechaVencimiento.getValue().format('Y-m-d');
                                                                        }
                                                                        var listaServicios='';
                                                                        for(x=0;x<arrNodos.length;x++)
                                                                        {
                                                                        	if(listaServicios=='')
                                                                            	listaServicios=(arrNodos[x].data)?arrNodos[x].data.id:arrNodos[x].id;
                                                                            else
                                                                            	listaServicios+=','+((arrNodos[x].data)?arrNodos[x].data.id:arrNodos[x].id);
                                                                        }
                                                                        var cadObj='{"idPerfilCosteo":"'+bD(iP)+'","idCiclo":"'+gEx('cmbCiclo').getValue()+'","idPeriodo":"'+gEx('cmbPeriodo').getValue()+'","costoServicio":"'+txtCosto.getValue()+'","fechaVencimiento":"'+fVencimiento+'","arrServicios":"'+listaServicios+'"}';
                                                                        function funcAjax()
                                                                        {
                                                                            var resp=peticion_http.responseText;
                                                                            arrResp=resp.split('|');
                                                                            if(arrResp[0]=='1')
                                                                            {
                                                                            	if(!esGridCosteo)
                                                                                {
                                                                                    for(x=0;x<arrNodos.length;x++)
                                                                                    {
                                                                                        gE('lblCosto_'+arrNodos[x].id).innerHTML=Ext.util.Format.usMoney(txtCosto.getValue());
                                                                                        arrNodos[x].attributes.costo=arrNodos[x].attributes.costoBase.replace('@costo',Ext.util.Format.usMoney(txtCosto.getValue()));
                                                                                    }
                                                                                }
                                                                                else
                                                                                {
                                                                                	if(gEx('gCostos').disabled)
                                                                                    	gEx('tCosto').setValue(Ext.util.Format.usMoney(txtCosto.getValue()));
                                                                                    else
	                                                                                	cargarCostoServicios();
                                                                                }
                                                                                ventanaAM.close();
                                                                            }
                                                                            else
                                                                            {
                                                                                msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
                                                                            }
                                                                        }
                                                                        obtenerDatosWeb('../paginasFunciones/funcionesPlanteles.php',funcAjax, 'POST','funcion=143&cadObj='+cadObj,true);
                                                                        
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
	
                                   
    if(arrNodos.length>1)                                
        ventanaAM.show();
    else
    {
        function funcAjax()
        {
            var resp=peticion_http.responseText;
            arrResp=resp.split('|');
            if(arrResp[0]=='1')
            {
                gEx('txtCosto').setValue(arrResp[1]);
                gEx('dteFechaVencimiento').setValue(arrResp[2]);
                ventanaAM.show();
            }
            else
            {
                msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
            }
        }
        obtenerDatosWeb('../paginasFunciones/funcionesTesoreria.php',funcAjax, 'POST','funcion=21&idCiclo='+gEx('cmbCiclo').getValue()+'&idPeriodo='+gEx('cmbPeriodo').getValue()+'&idNodo='+((gridCosteo)?arrNodos[0].data.id:arrNodos[0].id),true);
    }
	
    
}

function ingresarCostoPlanPagoDescuentoPP(iP)
{
	var gridCosteo=false;
    var lblServicio='';
    if((typeof('esGridCosteo')!='undefined')&& esGridCosteo)
    {
    	gridCosteo=true;
    }
    
    if(gridCosteo)
    {
    
    	lblServicio=gEx('cmbConceptos').getRawValue();
        arrNodos=gEx('gCostos').getSelectionModel().getSelections();
        if(gEx('gCostos').disabled)
        {
        	arrNodos=[];
            
            
            var reg=crearRegistro	(
            							[
                                            {name:'id'},
                                            {name:'idProgramaEducativo'},
                                            {name:'programaEducativo'},
                                            {name: 'idPlanEstudios'},
                                            {name: 'planEstudios'},
                                            {name:'idGrado'},
                                            {name:'grado'},
                                            {name:'costo'}
                                        ]
            						)
            
            var r=new reg	(
            					{
                                	id:gEx('cmbPlantel').getValue()+'_-1_-1_-1_'+gEx('cmbConceptos').getValue(),
                                    idProgramaEducativo:'-1',
                                    programaEducativo:'',
                                    idPlanEstudios:'-1',
                                    planEstudios:'',
                                    idGrado:'-1',
                                    grado:'',
                                    costo:normalizarValor(gEx('tCosto').getValue())
                                    
                                }
                                
            				)
        	arrNodos.push(r);
        }
    }
    
	var idConcepto=(gridCosteo)?arrNodos[0].data.id.split('_')[4]:arrNodos[0].id.split('_')[4];
    var costoServicio=parseFloat(normalizarValor(Ext.util.Format.stripTags(gridCosteo?arrNodos[0].data.costo:arrNodos[0].attributes.costo)));
    
    
    function funcAjax()
    {
        var resp=peticion_http.responseText;
        arrResp=resp.split('|');
        if(arrResp[0]=='1')
        {
        	var objConf=eval(arrResp[1])[0];
            
            var x;
            var arrServicios=new Array();
            if(!gridCosteo)
            {
                for(x=0;x<arrNodos.length;x++)
                {
                    if(existeValorArreglo(arrServicios,arrNodos[x].text)==-1)
                    {
                        arrServicios.push(arrServicios,arrNodos[x].text);
                        if(lblServicio=='')
                            lblServicio=arrNodos[x].text;
                        else
                            lblServicio+=', '+arrNodos[x].text;
                    }	
                }
            }
            var gridFechas=crearGridFechasGradosReplica();
            var gridTabuladorPlanesPago=crearGridTabuladorPlanPago(objConf);
            var form = new Ext.form.FormPanel(	
                                                {
                                                    baseCls: 'x-plain',
                                                    layout:'absolute',
                                                    defaultType: 'label',
                                                    items: 	[
                                                                {
                                                                    x:10,
                                                                    y:10,
                                                                    html:'<span style="color:#000;"><b>Servicio:</b></span>'
                                                                },
                                                                {
                                                                    x:80,
                                                                    y:10,
                                                                    html:'<span style="color:#900" title="'+lblServicio+'" alt="'+lblServicio+'"><b>'+Ext.util.Format.ellipsis( lblServicio, 75 )+'</b></span>'
                                                                },
                                                                {
                                                                    x:10,
                                                                    y:40,
                                                                    html:'<span style="color:#000;"><b>Costo del servicio:</b></span>'
                                                                },
                                                                {
                                                                    id:'txtCosto',
                                                                    x:120,
                                                                    y:35,
                                                                    width:110,
                                                                    xtype:'numberfield',
                                                                    allowDecimals:true,
                                                                    allowNegative:false,
                                                                    value:costoServicio,
                                                                    listeners:	{
                                                                                    'change':function()
                                                                                                {
                                                                                                    var txtCosto=gEx('txtCosto');
                                                                                                    if(txtCosto.getValue()=='')
                                                                                                    {
                                                                                                        return;
                                                                                                        function resp2()
                                                                                                        {
                                                                                                            txtCosto.focus();
                                                                                                        }
                                                                                                        msgBox('El costo del servicio ingresado no es v&aacute;lido',resp2);
                                                                                                        return;
                                                                                                    }
                                                                                                    var x;
                                                                                                    var arrId;
                                                                                                    var arrServicios=new Array();
                                                                                                    for(x=0;x<arrNodos.length;x++)
                                                                                                    {
                                                                                                        arrId=(gridCosteo)?arrNodos[x].data.id.split('_'):arrNodos[x].id.split('_');
                                                                                                        
                                                                                                        if(existeValorArreglo(arrServicios,arrId[4])==-1)
                                                                                                        {
                                                                                                            arrServicios.push(arrId[4]);
                                                                                                        }
                                                                                                    }
                                                                                                    
                                                                                                    var listaServicios='';
                                                                                                    for(x=0;x<arrServicios.length;x++)
                                                                                                    {
                                                                                                        if(listaServicios=='')
                                                                                                            listaServicios=arrServicios[x];
                                                                                                        else
                                                                                                            listaServicios+=','+arrServicios[x];
                                                                                                    }
                                                                                                    
                                                                                                    function funcAjax()
                                                                                                    {
                                                                                                        var resp=peticion_http.responseText;
                                                                                                        arrResp=resp.split('|');
                                                                                                        if(arrResp[0]=='1')
                                                                                                        {
                                                                                                            gEx('gridTabuladorPlanPago').getStore().loadData(eval(arrResp[1]));
                                                                                                        }
                                                                                                        else
                                                                                                        {
                                                                                                            msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
                                                                                                        }
                                                                                                    }
                                                                                                    obtenerDatosWeb('../paginasFunciones/funcionesTesoreria.php',funcAjax, 'POST','funcion=18&idCiclo='+gEx('cmbCiclo').getValue()+'&idPeriodo='+gEx('cmbPeriodo').getValue()+'&idConceptoBase='+(gridCosteo?arrNodos[0].data.id:arrNodos[0].id)+'&listaServicios='+listaServicios+'&costoServicio='+gEx('txtCosto').getValue(),true);
                                                                                                }
                                                                                }
                                                                },
                                                               
                                                                {
                                                                    x:10,
                                                                    y:70,
                                                                    xtype:'tabpanel',
                                                                    activeTab:0,
                                                                    baseCls: 'x-plain',
                                                                    items:	[
                                                                                gridTabuladorPlanesPago
                                                                                
                                                                            ]
                                                                }
                                                                
        
                                                            ]
                                                }
                                            );
            
            var ventanaAM = new Ext.Window(
                                            {
                                                title: 'Fechas de Planes de pago',
                                                width: 900,
                                                height:520,
                                                layout: 'fit',
                                                plain:true,
                                                modal:true,
                                                buttonAlign:'center',
                                                bodyStyle:'padding:5px;',
                                                items: form,
                                                listeners : {
                                                            show : {
                                                                        buffer : 10,
                                                                        fn : function() 
                                                                        {
                                                                            gEx('txtCosto').focus(true,500);
                                                                        }
                                                                    }
                                                        },
                                                buttons:	[
                                                                {
                                                                    
                                                                    text: 'Aceptar',
                                                                    handler: function()
                                                                            {
                                                                            	var txtCosto=gEx('txtCosto');
                                                                                if(gEx('txtCosto').getValue()=='')
                                                                                {
                                                                                	function respAux()
                                                                                    {
                                                                                    	txtCosto.focus(false,500);
                                                                                    }
                                                                                    msgBox('El costo del servicio ingresado no es v&aacute;lido',respAux);
                                                                                    return;
                                                                                }
                                                                                function resp(btn)
                                                                                {
                                                                                    if(btn=='yes')
                                                                                    {
                                                                                        var cadPlanPagos='';	
                                                                                        var x;
                                                                                        var fila;
                                                                                        var objFechas;
                                                                                        var cadArrFechas='';
                                                                                        var fechaMaxima='';
                                                                                        var arrReferencias='';
                                                                                        var arrColReferencia=new Array();
                                                                                        var idCampo;
                                                                                        var aCampo='';
                                                                                        var oCampo=new Array();
                                                                                        var aux=0;

                                                                                        for(x=0;x<gridTabuladorPlanesPago.getStore().fields.items.length;x++)
                                                                                        {
                                                                                        	idCampo=gridTabuladorPlanesPago.getStore().fields.items[x].name;
                                                                                            aCampo=idCampo.split('_');
                                                                                            if(aCampo[0]=='ref')
                                                                                            {
                                                                                            	oCampo[0]=idCampo;
                                                                                                oCampo[1]=aCampo[1];
                                                                                                oCampo[2]=aCampo[2];
                                                                                                for(aux=0;aux<gridTabuladorPlanesPago.getColumnModel().config.length;aux++)
                                                                                                {
                                                                                                	if(gridTabuladorPlanesPago.getColumnModel().config[aux].dataIndex==idCampo)
                                                                                                    {
                                                                                                    	oCampo[3]=Ext.util.Format.stripTags(gridTabuladorPlanesPago.getColumnModel().config[aux].header);
                                                                                                        oCampo[4]=aux;
                                                                                                        break;
                                                                                                    }
                                                                                                }
                                                                                                arrColReferencia.push(oCampo);
                                                                                            }
                                                                                        }
                                                                                        
                                                                                        var ct=0;
                                                                                        var oReferencia='';
                                                                                        var valor='';
                                                                                        for(x=0;x<gridTabuladorPlanesPago.getStore().getCount();x++)
                                                                                        {
                                                                                            fila=gridTabuladorPlanesPago.getStore().getAt(x);
                                                                                            if((consideraFechaVencimiento=='1')&&(fila.get('fechaVencimiento')==''))
                                                                                            {
                                                                                            	function resp2()
                                                                                                {
                                                                                                	gridTabuladorPlanesPago.startEditing(x,5);
                                                                                                }
                                                                                            	msgBox('Debe ingresar la fecha de vencimiento',resp2);
                                                                                            	return;
                                                                                            }
                                                                                            
                                                                                            fechaMaxima='';
                                                                                            if(typeof(fila.get('fechaVencimiento'))!='string')
                                                                                                fechaMaxima=fila.get('fechaVencimiento').format('Y-m-d');
                                                                                            else
                                                                                                fechaMaxima=fila.get('fechaVencimiento');
                                                                                            arrReferencias='';
                                                                                            for(ct=0;ct<arrColReferencia.length;ct++)   
                                                                                            {
                                                                                            	valor=fila.get(arrColReferencia[ct][0]);
                                                                                                if(valor=='')
                                                                                                {
                                                                                                	function resp3()
                                                                                                    {
                                                                                                    	gridTabuladorPlanesPago.startEditing(x,arrColReferencia[ct][4]);
                                                                                                    }
                                                                                                    msgBox('Debe ingresar el valor de la columna: "'+arrColReferencia[ct][3],resp3)+'"';
                                                                                                	return;
                                                                                                }
                                                                                                switch(arrColReferencia[ct][2])
                                                                                                {	
                                                                                                	case '3':
                                                                                                    case '4':
                                                                                                	case '5':
                                                                                                    	if(typeof(valor)!='string')
                                                                                                            valor=valor.format('Y-m-d');
                                                                                                        else
                                                                                                            valor=valor;
                                                                                                    break;
                                                                                                }
                                                                                            	oReferencia='{"idColumna":"'+arrColReferencia[ct][1]+'","valor":"'+valor+'"}';
                                                                                            	if(arrReferencias=='')
                                                                                                	arrReferencias=oReferencia;
                                                                                                else
                                                                                                	arrReferencias+=','+oReferencia;
                                                                                            }
                                                                                            objFechas='{"noPago":"'+fila.get('noPago')+'","idPlanPago":"'+fila.get('idPlanPago')+'","fechaVencimiento":"'+fechaMaxima+'","arrReferencias":['+arrReferencias+']}';
                                                                                            if(cadArrFechas=='')
                                                                                                cadArrFechas=objFechas;
                                                                                            else
                                                                                                cadArrFechas+=','+objFechas;
                                                                                            
                                                                                        }
                                                                                       	var listaServicios='';
                                                                                        for(x=0;x<arrNodos.length;x++)
                                                                                        {
                                                                                            if(listaServicios=='')
                                                                                                listaServicios=gridCosteo?arrNodos[0].data.id:arrNodos[0].id;
                                                                                            else
                                                                                                listaServicios+=','+((gridCosteo)?arrNodos[x].data.id:arrNodos[x].id);
                                                                                        }
                                                                                        var cadObj='{"idPerfilCosteo":"'+bD(iP)+'","idCiclo":"'+gEx('cmbCiclo').getValue()+'","idPeriodo":"'+gEx('cmbPeriodo').getValue()+'","costoServicio":"'+txtCosto.getValue()+'","arrServicios":"'+listaServicios+'","arrPagos":['+cadArrFechas+']}';
                                                                                       
                                                                                        function funcAjax()
                                                                                        {
                                                                                            var resp=peticion_http.responseText;
                                                                                            arrResp=resp.split('|');
                                                                                            if(arrResp[0]=='1')
                                                                                            {
                                                                                            	if(!gridCosteo)
                                                                                                {
                                                                                                    for(x=0;x<arrNodos.length;x++)
                                                                                                    {
                                                                                                        gE('lblCosto_'+arrNodos[x].id).innerHTML=Ext.util.Format.usMoney(txtCosto.getValue());
                                                                                                        arrNodos[x].attributes.costo=arrNodos[x].attributes.costoBase.replace('@costo',Ext.util.Format.usMoney(txtCosto.getValue()));
                                                                                                    }
                                                                                                }
                                                                                                else
                                                                                                {
                                                                                                	if(gEx('gCostos').disabled)
				                                                                                    	gEx('tCosto').setValue(Ext.util.Format.usMoney(txtCosto.getValue()));
                                                                                                    else
                	                                                                                	cargarCostoServicios();
                                                                                                }
                                                                                                ventanaAM.close();
                                                                                            }
                                                                                            else
                                                                                            {
                                                                                                msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
                                                                                            }
                                                                                        }
                                                                                        obtenerDatosWeb('../paginasFunciones/funcionesTesoreria.php',funcAjax, 'POST','funcion=20&cadObj='+cadObj,true);
                                                                                        
                                                                                        
                                                                                    }
                                                                                }
                                                                                msgConfirm('Est&aacute; seguro de querer guardar las fechas indicadas',resp);
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
            ventanaAM.show();
            if(costoServicio>0)
            {
            	gEx('txtCosto').fireEvent('change',gEx('txtCosto'),costoServicio,0);
            }
        }
        else
        {
            msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
        }
    }
    obtenerDatosWeb('../paginasFunciones/funcionesTesoreria.php',funcAjax, 'POST','funcion=19&idConcepto='+idConcepto,true);
}

function crearGridTabuladorPlanPago(objConf)
{

	
	var lector= new Ext.data.ArrayReader({
                                            
                                            
                                            fields: objConf.arrCampos
                                            
                                        }
                                      );
	var chkRow=objConf.arrColumnas[0]; 
    var summary = new Ext.ux.grid.GroupSummary();                                                                                  
	var alDatos=new Ext.data.GroupingStore({
                                                            reader: lector,
                                                            
                                                            sortInfo: {field: 'nombrePlanPago', direction: 'ASC'},
                                                            groupField: 'nombrePlanPago',
                                                            remoteGroup:false,
				                                            remoteSort: false,
                                                            autoLoad:false
                                                            
                                                        }) 
	alDatos.on('beforeload',function(proxy)
    								{
                                    	
                                        
                                    	/*var cadObj='{"plantel":"'+plantel+'","idCiclo":"'+gEx('cmbCiclo').getValue()+'","idPeriodo":"'+gEx('cmbPeriodo').getValue()+
                                        			'","idConcepto":"'+arrConcepto[2]+'","tipoConcepto":"'+arrConcepto[1]+'","idElemento":"'+f.get('idElemento')+'","tipoElemento":"'+f.get('tipoElemento')+
                                                    '","idInstanciaPlanEstudio":"'+f.get('idInstanciaPlan')+'"}';
                                    	proxy.baseParams.funcion='14';
                                        proxy.baseParams.cadObj=cadObj;*/
                                    }
                        )   
       
        var cModelo= new Ext.grid.ColumnModel   	(
                                                       	objConf.arrColumnas 
                                                    );
                                                    
        var tblGrid=	new Ext.grid.EditorGridPanel	(
                                                            {
                                                                id:'gridTabuladorPlanPago',
                                                                store:alDatos,
                                                                title:'Planes de pago',
                                                                height:320,
                                                                frame:false,
                                                                border:true,
                                                                cm: cModelo,
                                                                stripeRows :true,
                                                                loadMask:true,
                                                                columnLines : true,
                                                                plugins:[summary],
                                                                sm:chkRow,
                                                                tbar :	[
                                                                			{
                                                                                icon:'../images/table_row_insert.png',
                                                                                cls:'x-btn-text-icon',
                                                                                hidden:true,
                                                                                text:'Importar Esquema de Fechas',
                                                                                handler:function()
                                                                                        {
                                                                                            mostrarVentanaImportarEsquemaFechas(arrConcepto[2],arrConcepto[1]);
                                                                                        }
                                                                                
                                                                            }
                                                                		],
                                                                view:new Ext.grid.GroupingView({
                                                                                                    forceFit:false,
                                                                                                    showGroupName: true,
                                                                                                    enableNoGroups:false,
                                                                                                    enableGroupingMenu:false,
                                                                                                    hideGroupedColumn: true,
                                                                                                    startCollapsed:false
                                                                                                })
                                                            }
                                                        );
        return 	tblGrid;	
}

function mostrarVentanaAsignarFechaPlanV2(c)
{

	var arrCriterio=[['1','Primer d\xEDa de cada mes'],['2','\xDAltimo d\xEDa de cada mes'],['3','Intervalo de tiempo'],['4','Mismo d\xEDa de cada mes']];
	var cmbCriterio=crearComboExt('cmbCriterio',arrCriterio,250,5,250);
    cmbCriterio.on('select',function(cmb,registro)
    								{
                                    	var lblIndique=gEx('lblIndique');
                                        var txtIntervalo=gEx('txtIntervalo');
                                        var cmbPeriodoTiempo=gEx('cmbPeriodoTiempo');
                                    	lblIndique.hide();
                                        txtIntervalo.hide();
                                        cmbPeriodoTiempo.hide();
                                        txtIntervalo.setValue('');
                                        if(registro.get('id')=='3')
                                        {
                                        	lblIndique.show();
                                            txtIntervalo.show();
                                            cmbPeriodoTiempo.show();
                                            txtIntervalo.focus(false,500);
                                        }
                                    }
    					)
    
    
    var arrPeriodo=[['1','Dias'],['2','Meses'],['3','A\xF1os']];
    var cmbPeriodoTiempo=crearComboExt('cmbPeriodoTiempo',arrPeriodo,310,35,150);
    cmbPeriodoTiempo.setValue('1');
    cmbPeriodoTiempo.hide();
   
	var gridTabuladorPlanPago=gEx('gridTabuladorPlanPago');
	var filas=gridTabuladorPlanPago.getSelectionModel().getSelections();
    if(filas.length==0)
    {
    	msgBox('Debe seleccionar al menos un elemento como objetivo de asignaci&oacute;n de fecha');
    	return;
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
                                                            html:'Fecha m&aacute;xima para descuento:'
                                                        },
                                                        {
                                                        	x:190,
                                                            y:5,
                                                            xtype:'datefield',
                                                            id:'dteFechaDescuento'
                                                        },
                                                        {
                                                        	x:10,
                                                            y:40,
                                                            id:'chkCalcular',
                                                            xtype:'checkbox',
                                                            boxLabel:'<span class="letraExt"><b>Deseo asignar la fecha seleccionada y adem&aacute;s considerarla como fecha base para el resto de los pagos</b></span>',
                                                            listeners:	{
                                                            				check:function(chk,valor)
                                                                            		{
                                                                                    	if(valor)
	                                                                                    	gEx('fCriterio').enable();
                                                                                        else
                                                                                        	gEx('fCriterio').disable();
                                                                                    }
                                                            			}
                                                        },
                                                        {
                                                        	x:10,
                                                            y:80,
                                                            xtype:'fieldset',
                                                            layout:'absolute',
                                                            title:'Criterio de c&aacute;lculo de siguientes pagos',
                                                            width:650,
                                                            height:150,
                                                            id:'fCriterio',
                                                            disabled:true,
                                                            items:	[	
                                                            			{
                                                                        	x:10,
                                                                            y:10,
                                                                            xtype:'label',
                                                                            html:'Elija un criterio de generaci&oacute;n de fechas:'
                                                                        },
                                                                        cmbCriterio,
                                                                        {
                                                                        	x:10,
                                                                            y:40,
                                                                            id:'lblIndique',
                                                                            xtype:'label',
                                                                            hidden:true,
                                                                            html:'Indique el intervalo de tiempo de entre pagos:'
                                                                        },
                                                                        {
                                                                        	xtype:'numberfield',
                                                                            id:'txtIntervalo',
                                                                            width:50,
                                                                            hidden:true,
                                                                            x:250,
                                                                            y:35
                                                                        },
                                                                        cmbPeriodoTiempo,
                                                                        {
                                                                        	xtype:'label',
                                                                            x:35,
                                                                            y:70,
                                                                            html:'<span class="letraRoja">*</span> <span style="color: #000">Las fechas de los pagos se calcular&aacute;n a partir del primer elemento seleccionado de cada plan de pago</span>'
                                                                        },
                                                                        {
                                                                        	xtype:'label',
                                                                            x:35,
                                                                            y:90,
                                                                            html:'<span class="letraRoja">**</span> <span style="color: #000">Si la fecha del pago excede al &uacute;ltimoltimo d&iacute;a del mes, se asignar&aacute; como fecha de pago el ultimo d&iacute;a del mismo</span>'
                                                                        }
                                                            		]
                                                            
                                                        }
													]
										}
									);
	
	var ventanaAM = new Ext.Window(
									{
										title: 'Asignaci&oacute;n de fechas para descuesto por Pronto Pago',
										width: 700,
										height:320,
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
                                                                        var dteFechaDescuento=gEx('dteFechaDescuento');
                                                                        if(dteFechaDescuento.getValue()=='')	
                                                                        {
                                                                        	function resp()
                                                                            {
                                                                            	dteFechaDescuento.focus();
                                                                            }
                                                                            msgBox('Debe indicar la fecha a asignar',resp);
                                                                            return;
                                                                        }
                                                                        
                                                                        var fCriterio=gEx('fCriterio');
                                                                        var chkCalcular=gEx('chkCalcular');
                                                                        var cmbCriterio=gEx('cmbCriterio');
                                                                        var cmbPeriodoTiempo=gEx('cmbPeriodoTiempo');
                                                                        var txtIntervalo=gEx('txtIntervalo');
                                                                        if(chkCalcular.getValue())
                                                                        {
                                                                        	if(cmbCriterio.getValue()=='')
                                                                            {
                                                                            	function resp2()
                                                                                {
                                                                                	cmbCriterio.focus();
                                                                                }
                                                                            	msgBox('Debe indicar el criterio de generaci&oacute;n de fechas',resp2);
                                                                                return;
                                                                            }
                                                                            if(cmbCriterio.getValue()=='3')
                                                                            {
                                                                            	if(txtIntervalo.getValue()=='')
                                                                                {
                                                                                	function resp3()
                                                                                    {
                                                                                    	txtIntervalo.focus();
                                                                                    }
                                                                                    msgBox('Debe indicar el intervalo de tiempo a aplicar entre pagos',resp3);
                                                                                    return;
                                                                                }
                                                                            }
                                                                        }
                                                                        
                                                                        var ultimoIDPlan=-1;
                                                                        var ultimaFecha;
                                                                        var fecha;
                                                                        var txtFecha;
                                                                        var dia;
                                                                        var mes;
                                                                        var anio;
                                                                        var nFecha;
                                                                        var cadFechaAux;
                                                                        var nFechaAux;
                                                                        var intervalo;
                                                                        for(x=0;x<filas.length;x++)
                                                                        {
                                                                        	if(chkCalcular.getValue())
                                                                            {
                                                                                if(ultimoIDPlan!=filas[x].get('idPlanPago'))
                                                                                {
                                                                                	ultimoIDPlan=filas[x].get('idPlanPago');
                                                                                	ultimaFecha=dteFechaDescuento.getValue();
                                                                                   
                                                                                	filas[x].set(bD(c),dteFechaDescuento.getValue());
                                                                                    
                                                                                }
                                                                                else
                                                                                {
                                                                                	fecha=ultimaFecha;
                                                                                    dia=parseInt(fecha.format('d'));
                                                                                    mes=parseInt(fecha.format('m'));
                                                                                    anio=parseInt(fecha.format('Y'));
                                                                                    switch(cmbCriterio.getValue())
                                                                                    {
                                                                                    	case '1'://Primer dia de cada mes
                                                                                        	mes++;
                                                                                            if(mes>12)
                                                                                            {
                                                                                            	mes=1;
                                                                                                anio++;
                                                                                            }
                                                                                            
                                                                                            nFecha=anio+'-'+rellenarCadena(mes,2,'0',-1)+'-01';
                                                                                            fecha=Date.parseDate(nFecha,'Y-m-d');
                                                                                        break;
                                                                                        case '2'://Ultimo dia de cada mes
                                                                                        	mes++;
                                                                                            if(mes>12)
                                                                                            {
                                                                                            	mes=1;
                                                                                                anio++;
                                                                                            }
                                                                                            
                                                                                            nFecha=anio+'-'+rellenarCadena(mes,2,'0',-1)+'-01';
                                                                                            fecha=Date.parseDate(nFecha,'Y-m-d');
                                                                                            fecha=fecha.getLastDateOfMonth();
                                                                                        break;
                                                                                        case '3'://Intervalo de tiempo
                                                                                        	
                                                                                            switch(cmbPeriodoTiempo.getValue())
                                                                                            {
                                                                                            	case '1':
                                                                                                	intervalo=	Date.DAY;
                                                                                                break;
                                                                                                case '2':
                                                                                                	intervalo=	Date.MONTH;
                                                                                                break;
                                                                                                case '3':
                                                                                                	intervalo=	Date.YEAR;
                                                                                                break;
                                                                                            }
                                                                                            
                                                                                            fecha=fecha.add(intervalo,txtIntervalo.getValue());
                                                                                            
                                                                                            
                                                                                        break;
                                                                                        case '4':  //Mismo dia de cada mes
                                                                                        	mes++;
                                                                                            if(mes>12)
                                                                                            {
                                                                                            	mes=1;
                                                                                                anio++;
                                                                                            }
                                                                                            cadFechaAux=anio+'-'+rellenarCadena(mes,2,'0',-1)+'-01';
                                                                                            fecha=Date.parseDate(cadFechaAux,'Y-m-d');
                                                                                            fecha=fecha.getLastDateOfMonth();
                                                                                            cadFechaAux=anio+'-'+rellenarCadena(mes,2,'0',-1)+'-'+rellenarCadena(dia,2,'0',-1);
                                                                                            nFechaAux=Date.parseDate(cadFechaAux,'Y-m-d');
                                                                                            
                                                                                            if(fecha.format('m')==nFechaAux.format('m'))
                                                                                            {
                                                                                            	fecha=nFechaAux;
                                                                                            }
                                                                                        break;
                                                                                    }
                                                                                    fecha=obtenerDiaHabil(fecha,accionDiaNoHabil);
                                                                                	filas[x].set(bD(c),fecha);
                                                                                    ultimaFecha=fecha;
                                                                                   
                                                                                }
                                                                            }
                                                                            else
                                                                            {
                                                                            	filas[x].set(bD(c),dteFechaDescuento.getValue());
                                                                            }
                                                                            
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

function crearGridFechasGradosReplica()
{
	var lector= new Ext.data.ArrayReader({
                                            
                                           
                                            fields: [
                                               			{name:'idElemento'},
                                                        {name:'tipoElemento'},
		                                                {name: 'idInstanciaPlan'},
                                                        {name: 'planEstudios'},
		                                                {name:'descripcion'}
                                            		]
                                            
                                        }
                                      );
	 
                                                                                      
	var alDatos=new Ext.data.GroupingStore({
                                                            reader: lector,
                                                            proxy : new Ext.data.HttpProxy	(

                                                                                              {

                                                                                                  url: '../paginasFunciones/funcionesPlanteles.php'

                                                                                              }

                                                                                          ),
                                                            sortInfo: {field: 'planEstudios', direction: 'ASC'},
                                                            groupField: 'planEstudios',
                                                            remoteGroup:false,
				                                            remoteSort: false,
                                                            autoLoad:false
                                                            
                                                        }) 
	
		                     
    var chkRow=new Ext.grid.CheckboxSelectionModel();   
    var cModelo= new Ext.grid.ColumnModel   	(
                                                    [
                                                        chkRow,
                                                        {
                                                            header:'Plan Estudios',
                                                            width:300,
                                                            sortable:true,
                                                            dataIndex:'planEstudios'
                                                        },
                                                        {
                                                            header:'',
                                                            width:350,
                                                            sortable:true,
                                                            dataIndex:'descripcion',
                                                            renderer:function(val,meta,registro)
                                                            		{
                                                                    	switch(registro.data.tipoElemento)
                                                                        {
                                                                        	case '1':
                                                                            	return '<img src="../images/table_row_insert.png"  width="13" height="13">&nbsp;<span style="color:#030"><b>'+val+'</b></span></span>';
                                                                            break;
                                                                            case '2':
                                                                            	return '&nbsp;&nbsp;&nbsp;&nbsp;<img src="../images/text_lowercase.png" width="13" height="13">&nbsp;<span style="color:#003"><b>'+val+'</b></span></span>';
                                                                            break;
                                                                        }
                                                                    }
                                                        }
                                                        
                                                    ]
                                                );
                                                
    var tblGrid=	new Ext.grid.EditorGridPanel	(
                                                        {
                                                            id:'griFechasdGradoReplica',
                                                            store:alDatos,
                                                            x:10,
                                                            y:40,
                                                            frame:true,
                                                            border:true,
                                                            cm: cModelo,
                                                            height:360,
                                                            stripeRows :true,
                                                            loadMask:true,
                                                            columnLines : true,
                                                            sm:chkRow,
                                                            view:new Ext.grid.GroupingView({
                                                                                                forceFit:false,
                                                                                                showGroupName: false,
                                                                                                enableNoGroups:false,
                                                                                                enableGroupingMenu:false,
                                                                                                hideGroupedColumn: true,
                                                                                                startCollapsed:false
                                                                                            })
                                                        }
                                                    );
	return tblGrid;                                                    
}

function mostrarVentanaAsignarFechaPlan()
{
	var arrCriterio=[['1','Primer d\xEDa de cada mes'],['2','\xDAltimo d\xEDa de cada mes'],['3','Intervalo de tiempo'],['4','Mismo d\xEDa de cada mes']];
	var cmbCriterio=crearComboExt('cmbCriterio',arrCriterio,250,5,250);
    cmbCriterio.on('select',function(cmb,registro)
    								{
                                    	var lblIndique=gEx('lblIndique');
                                        var txtIntervalo=gEx('txtIntervalo');
                                        var cmbPeriodoTiempo=gEx('cmbPeriodoTiempo');
                                    	lblIndique.hide();
                                        txtIntervalo.hide();
                                        cmbPeriodoTiempo.hide();
                                        txtIntervalo.setValue('');
                                        if(registro.get('id')=='3')
                                        {
                                        	lblIndique.show();
                                            txtIntervalo.show();
                                            cmbPeriodoTiempo.show();
                                            txtIntervalo.focus(false,500);
                                        }
                                    }
    					)
    
    
    var arrPeriodo=[['1','Dias'],['2','Meses'],['3','A\xF1os']];
    var cmbPeriodoTiempo=crearComboExt('cmbPeriodoTiempo',arrPeriodo,310,35,150);
    cmbPeriodoTiempo.setValue('1');
    cmbPeriodoTiempo.hide();
   
	var gridTabuladorPlanPago=gEx('gridTabuladorPlanPago');
	var filas=gridTabuladorPlanPago.getSelectionModel().getSelections();
    if(filas.length==0)
    {
    	msgBox('Debe seleccionar al menos un elemento como objetivo de asignaci&oacute;n de fecha');
    	return;
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
                                                            html:'Fecha a asignar:'
                                                        },
                                                        {
                                                        	x:190,
                                                            y:5,
                                                            xtype:'datefield',
                                                            id:'dteFechaDescuento'
                                                        },
                                                        {
                                                        	x:10,
                                                            y:40,
                                                            id:'chkCalcular',
                                                            xtype:'checkbox',
                                                            boxLabel:'<span class="letraExt"><b>Deseo asignar la fecha seleccionada y adem&aacute;s considerarla como fecha base para el resto de los pagos</b></span>',
                                                            listeners:	{
                                                            				check:function(chk,valor)
                                                                            		{
                                                                                    	if(valor)
	                                                                                    	gEx('fCriterio').enable();
                                                                                        else
                                                                                        	gEx('fCriterio').disable();
                                                                                    }
                                                            			}
                                                        },
                                                        {
                                                        	x:10,
                                                            y:80,
                                                            xtype:'fieldset',
                                                            layout:'absolute',
                                                            title:'Criterio de c&aacute;lculo de siguientes pagos',
                                                            width:650,
                                                            height:150,
                                                            id:'fCriterio',
                                                            disabled:true,
                                                            items:	[	
                                                            			{
                                                                        	x:10,
                                                                            y:10,
                                                                            xtype:'label',
                                                                            html:'Elija un criterio de generaci&oacute;n de fechas:'
                                                                        },
                                                                        cmbCriterio,
                                                                        {
                                                                        	x:10,
                                                                            y:40,
                                                                            id:'lblIndique',
                                                                            xtype:'label',
                                                                            hidden:true,
                                                                            html:'Indique el intervalo de tiempo de entre pagos:'
                                                                        },
                                                                        {
                                                                        	xtype:'numberfield',
                                                                            id:'txtIntervalo',
                                                                            width:50,
                                                                            hidden:true,
                                                                            x:250,
                                                                            y:35
                                                                        },
                                                                        cmbPeriodoTiempo,
                                                                        {
                                                                        	xtype:'label',
                                                                            x:35,
                                                                            y:70,
                                                                            html:'<span class="letraRoja">*</span> <span style="color: #000">Las fechas de los pagos se calcular&aacute;n a partir del primer elemento seleccionado de cada plan de pago</span>'
                                                                        },
                                                                        {
                                                                        	xtype:'label',
                                                                            x:35,
                                                                            y:90,
                                                                            html:'<span class="letraRoja">**</span> <span style="color: #000">Si la fecha del pago excede al &uacute;ltimoltimo d&iacute;a del mes, se asignar&aacute; como fecha de pago el ultimo d&iacute;a del mismo</span>'
                                                                        }
                                                            		]
                                                            
                                                        }
													]
										}
									);
	
	var ventanaAM = new Ext.Window(
									{
										title: 'Asignaci&oacute;n de fechas para descuesto por Pronto Pago',
										width: 700,
										height:320,
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
                                                                        var dteFechaDescuento=gEx('dteFechaDescuento');
                                                                        if(dteFechaDescuento.getValue()=='')	
                                                                        {
                                                                        	function resp()
                                                                            {
                                                                            	dteFechaDescuento.focus();
                                                                            }
                                                                            msgBox('Debe indicar la fecha m&aacute;xima para descuento',resp);
                                                                            return;
                                                                        }
                                                                        
                                                                        var fCriterio=gEx('fCriterio');
                                                                        var chkCalcular=gEx('chkCalcular');
                                                                        var cmbCriterio=gEx('cmbCriterio');
                                                                        var cmbPeriodoTiempo=gEx('cmbPeriodoTiempo');
                                                                        var txtIntervalo=gEx('txtIntervalo');
                                                                        if(chkCalcular.getValue())
                                                                        {
                                                                        	if(cmbCriterio.getValue()=='')
                                                                            {
                                                                            	function resp2()
                                                                                {
                                                                                	cmbCriterio.focus();
                                                                                }
                                                                            	msgBox('Debe indicar el criterio de generaci&oacute;n de fechas',resp2);
                                                                                return;
                                                                            }
                                                                            if(cmbCriterio.getValue()=='3')
                                                                            {
                                                                            	if(txtIntervalo.getValue()=='')
                                                                                {
                                                                                	function resp3()
                                                                                    {
                                                                                    	txtIntervalo.focus();
                                                                                    }
                                                                                    msgBox('Debe indicar el intervalo de tiempo a aplicar entre pagos',resp3);
                                                                                    return;
                                                                                }
                                                                            }
                                                                        }
                                                                        
                                                                        var ultimoIDPlan=-1;
                                                                        var ultimaFecha;
                                                                        var fecha;
                                                                        var txtFecha;
                                                                        var dia;
                                                                        var mes;
                                                                        var anio;
                                                                        var nFecha;
                                                                        var cadFechaAux;
                                                                        var nFechaAux;
                                                                        var intervalo;
                                                                        for(x=0;x<filas.length;x++)
                                                                        {
                                                                        	if(chkCalcular.getValue())
                                                                            {
                                                                                if(ultimoIDPlan!=filas[x].get('idPlanPago'))
                                                                                {
                                                                                	ultimoIDPlan=filas[x].get('idPlanPago');
                                                                                	ultimaFecha=dteFechaDescuento.getValue();
                                                                                	filas[x].set('fechaMaximaDescuento',dteFechaDescuento.getValue());
                                                                                    
                                                                                }
                                                                                else
                                                                                {
                                                                                	fecha=ultimaFecha;
                                                                                    dia=parseInt(fecha.format('d'));
                                                                                    mes=parseInt(fecha.format('m'));
                                                                                    anio=parseInt(fecha.format('Y'));
                                                                                    switch(cmbCriterio.getValue())
                                                                                    {
                                                                                    	case '1'://Primer dia de cada mes
                                                                                        	mes++;
                                                                                            if(mes>12)
                                                                                            {
                                                                                            	mes=1;
                                                                                                anio++;
                                                                                            }
                                                                                            
                                                                                            nFecha=anio+'-'+rellenarCadena(mes,2,'0',-1)+'-01';
                                                                                            fecha=Date.parseDate(nFecha,'Y-m-d');
                                                                                        break;
                                                                                        case '2'://Ultimo dia de cada mes
                                                                                        	mes++;
                                                                                            if(mes>12)
                                                                                            {
                                                                                            	mes=1;
                                                                                                anio++;
                                                                                            }
                                                                                            
                                                                                            nFecha=anio+'-'+rellenarCadena(mes,2,'0',-1)+'-01';
                                                                                            fecha=Date.parseDate(nFecha,'Y-m-d');
                                                                                            fecha=fecha.getLastDateOfMonth();
                                                                                        break;
                                                                                        case '3'://Intervalo de tiempo
                                                                                        	
                                                                                            switch(cmbPeriodoTiempo.getValue())
                                                                                            {
                                                                                            	case '1':
                                                                                                	intervalo=	Date.DAY;
                                                                                                break;
                                                                                                case '2':
                                                                                                	intervalo=	Date.MONTH;
                                                                                                break;
                                                                                                case '3':
                                                                                                	intervalo=	Date.YEAR;
                                                                                                break;
                                                                                            }
                                                                                            
                                                                                            fecha=fecha.add(intervalo,txtIntervalo.getValue());
                                                                                            
                                                                                            
                                                                                        break;
                                                                                        case '4':  //Mismo dia de cada mes
                                                                                        	mes++;
                                                                                            if(mes>12)
                                                                                            {
                                                                                            	mes=1;
                                                                                                anio++;
                                                                                            }
                                                                                            cadFechaAux=anio+'-'+rellenarCadena(mes,2,'0',-1)+'-01';
                                                                                            fecha=Date.parseDate(cadFechaAux,'Y-m-d');
                                                                                            fecha=fecha.getLastDateOfMonth();
                                                                                            cadFechaAux=anio+'-'+rellenarCadena(mes,2,'0',-1)+'-'+rellenarCadena(dia,2,'0',-1);
                                                                                            nFechaAux=Date.parseDate(cadFechaAux,'Y-m-d');
                                                                                            
                                                                                            if(fecha.format('m')==nFechaAux.format('m'))
                                                                                            {
                                                                                            	fecha=nFechaAux;
                                                                                            }
                                                                                        break;
                                                                                    }
                                                                                    fecha=obtenerDiaHabil(fecha,accionDiaNoHabil);
                                                                                	filas[x].set('fechaMaximaDescuento',fecha);
                                                                                    ultimaFecha=fecha;
                                                                                }
                                                                            }
                                                                            else
                                                                            {
                                                                            	filas[x].set('fechaMaximaDescuento',dteFechaDescuento.getValue());
                                                                            }
                                                                            
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

function obtenerDiaHabil(fecha,accion)
{
	var dia=fecha.getDay();
    console.log(arrDiasPermitidos);
    return fecha;

    if(dia==0)
	   	dia=7;
    if(existeValorArreglo(arrDiasPermitidos,dia)!=-1)
    {
    	if(!esDiaInhabil(fecha))
        	return fecha;
        
    }
    
    if(accion=='0')
        fecha=fecha.add(Date.DAY,1);
   	else
        fecha=fecha.add(Date.DAY,-1);
   	
    return obtenerDiaHabil(fecha,accion);
    
    
    
}

function esDiaInhabil(fecha)
{
	var x;
    for(x=0;x<fechasNoHabiles.length;x++)
    {
    	if((fecha>=fechasNoHabiles[x][0])&&(fecha<=fechasNoHabiles[x][1]))
        	return true;
        if(fechasNoHabiles[x][0]>fecha)
        	return false
    }
    return false;
}

function formatearResumen()
{
	return '<span class="letraRojaSubrayada8"><b>Total a pagar:</b></span>'
}

function ingresarCostoEstandarTabuladorDescuento(iP)
{
	var gridCosteo=false;
    if((typeof('esGridCosteo')!='undefined')&& esGridCosteo)
    {
    	gridCosteo=true;
    }

	regDescuento=crearRegistro	(
    								[
                                        {name: 'valorDescuento'},
                                        {name:'tipoDescuento'},
                                        {name:'fechaLimiteAplicacion'},
                                        {name:'costoServicio'}
                                    ]	
    							)

	var gridDescuentos=crearGridDescuentos();
	var lblServicio='';
    var x;
    var arrServicios=new Array();
    
    if(gridCosteo)
    {
    
    	lblServicio=gEx('cmbConceptos').getRawValue();
        arrNodos=gEx('gCostos').getSelectionModel().getSelections();
        if(gEx('gCostos').disabled)
        {
        	arrNodos=[];
            
            
            var reg=crearRegistro	(
            							[
                                            {name:'id'},
                                            {name:'idProgramaEducativo'},
                                            {name:'programaEducativo'},
                                            {name: 'idPlanEstudios'},
                                            {name: 'planEstudios'},
                                            {name:'idGrado'},
                                            {name:'grado'},
                                            {name:'costo'}
                                        ]
            						)
            
            var r=new reg	(
            					{
                                	id:gEx('cmbPlantel').getValue()+'_-1_-1_-1_'+gEx('cmbConceptos').getValue(),
                                    idProgramaEducativo:'-1',
                                    programaEducativo:'',
                                    idPlanEstudios:'-1',
                                    planEstudios:'',
                                    idGrado:'-1',
                                    grado:'',
                                    costo:normalizarValor(gEx('tCosto').getValue())
                                    
                                }
                                
            				)
        	arrNodos.push(r);
        }
    }
    else
    {
    
        for(x=0;x<arrNodos.length;x++)
        {
            if(existeValorArreglo(arrServicios,arrNodos[x].text)==-1)
            {
                arrServicios.push(arrServicios,arrNodos[x].text);
                if(lblServicio=='')
                    lblServicio=arrNodos[x].text;
                else
                    lblServicio+=', '+arrNodos[x].text;
            }	
        }
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
                                                            html:'<span style="color:#000;"><b>Servicio:</b></span>'
                                                        },
                                                        {
                                                        	x:80,
                                                            y:10,
                                                            html:'<span style="color:#900" title="'+lblServicio+'" alt="'+lblServicio+'"><b>'+Ext.util.Format.ellipsis( lblServicio, 75 )+'</b></span>'
                                                        },
                                                        {
                                                        	x:10,
                                                            y:40,
                                                            html:'<span style="color:#000;"><b>Costo del servicio:</b></span>'
                                                        },
                                                        {
                                                        	id:'txtCosto',
                                                        	x:120,
                                                            y:35,
                                                            width:110,
                                                            xtype:'numberfield',
                                                            allowDecimals:true,
                                                            allowNegative:false,
                                                            listeners:	{
                                                            				change:function()
                                                                            		{
                                                                                    	recalcularCostos();
                                                                                    }
                                                            			}
                                                        },
                                                        gridDescuentos									
                                                  ]
										}
									);
	
	var ventanaAM = new Ext.Window(
									{
										title: 'Ingresar costo',
										width: 670,
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
                                                                        gEx('txtCosto').focus(true,500);
                                                                    }
                                                                }
                                                    },
										buttons:	[
														{
															
															text: '<?php echo $etj["lblBtnAceptar"]?>',
															handler: function()
																	{
																		var txtCosto=gEx('txtCosto');
                                                                        var dteFechaVencimiento=gEx('dteFechaVencimiento');
                                                                        if(txtCosto.getValue()=='')
                                                                        {
                                                                        	function resp()
                                                                            {
                                                                            	txtCosto.focus();
                                                                            }
                                                                            msgBox('El costo del servicio ingresado no es v&aacute;lido',resp);
                                                                            return;
                                                                        }
                                                                        
                                                                        
                                                                        var fVencimiento='';
                                                                        var listaServicios='';
                                                                        
                                                                        
                                                                        var gridDescuentos=gEx('gridDescuentos');
                                                                        var x;
                                                                        var fila;
                                                                        var arrDescuentos='';
                                                                        var oD='';
                                                                        for(x=0;x<gridDescuentos.getStore().getCount();x++)
                                                                        {
                                                                            fila=gridDescuentos.getStore().getAt(x);
                                                                            if(fila.get('valorDescuento')=='')
                                                                            {
                                                                            	function resp1()
                                                                                {
                                                                                	gridDescuentos.startEditing(x,0);
                                                                                }
                                                                                msgConfirm('Debe ingresar el valor de descuento',resp1);
                                                                            	return;
                                                                            }
                                                                            if(fila.get('tipoDescuento')=='')
                                                                            {
                                                                            	function resp2()
                                                                                {
                                                                                	gridDescuentos.startEditing(x,1);
                                                                                }
                                                                                msgConfirm('Debe ingresar el tipo de descuento a aplicar',resp2);
                                                                            	return;
                                                                            }
                                                                            if(fila.get('fechaLimiteAplicacion')=='')
                                                                            {
                                                                            	function resp3()
                                                                                {
                                                                                	gridDescuentos.startEditing(x,2);
                                                                                }
                                                                                msgConfirm('Debe ingresar la fecha l&iacute;mite de aplicaci&oacute;n del descuento',resp3);
                                                                            	return;
                                                                            }
                                                                            oD='{"valorDescuento":"'+fila.get('valorDescuento')+'","tipoDescuento":"'+fila.get('tipoDescuento')+'","fechaLimite":"'+
                                                                            	fila.get('fechaLimiteAplicacion').format('Y-m-d')+'","costoServicio":"'+fila.get('costoServicio')+'"}';
                                                                            if(arrDescuentos=='')
                                                                            	arrDescuentos=oD;
                                                                            else
                                                                            	arrDescuentos+=','+oD;
                                                                        }
                                                                        
                                                                        for(x=0;x<arrNodos.length;x++)
                                                                        {
                                                                        	if(listaServicios=='')
                                                                            	listaServicios=(gridCosteo)?arrNodos[x].data.id:arrNodos[x].id;
                                                                            else
                                                                            	listaServicios+=','+((gridCosteo)?arrNodos[x].data.id:arrNodos[x].id);
                                                                        }
                                                                        var cadObj='{"idPerfilCosteo":"'+bD(iP)+'","arrDescuentos":['+arrDescuentos+'],"idCiclo":"'+gEx('cmbCiclo').getValue()+'","idPeriodo":"'+gEx('cmbPeriodo').getValue()+'","costoServicio":"'+txtCosto.getValue()+'","fechaVencimiento":"'+fVencimiento+'","arrServicios":"'+listaServicios+'"}';
                                                                        function funcAjax()
                                                                        {
                                                                            var resp=peticion_http.responseText;
                                                                            arrResp=resp.split('|');
                                                                            if(arrResp[0]=='1')
                                                                            {
                                                                            	if(!esGridCosteo)
                                                                                {
                                                                                    for(x=0;x<arrNodos.length;x++)
                                                                                    {
                                                                                        gE('lblCosto_'+arrNodos[x].id).innerHTML=Ext.util.Format.usMoney(txtCosto.getValue());
                                                                                        arrNodos[x].attributes.costo=arrNodos[x].attributes.costoBase.replace('@costo',Ext.util.Format.usMoney(txtCosto.getValue()));
                                                                                    }
                                                                                }
                                                                                else
                                                                                {
                                                                                	if(gEx('gCostos').disabled)
                                                                                    	gEx('tCosto').setValue(Ext.util.Format.usMoney(txtCosto.getValue()));
                                                                                    else
	                                                                                	cargarCostoServicios();
                                                                                }
                                                                                
                                                                                
                                                                                ventanaAM.close();
                                                                            }
                                                                            else
                                                                            {
                                                                                msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
                                                                            }
                                                                        }
                                                                        obtenerDatosWeb('../paginasFunciones/funcionesTesoreria.php',funcAjax, 'POST','funcion=22&cadObj='+cadObj,true);
                                                                        
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
	if(arrNodos.length>1)                                
		ventanaAM.show();
    else
    {
    	function funcAjax()
        {
            var resp=peticion_http.responseText;
            arrResp=resp.split('|');
            if(arrResp[0]=='1')
            {
            	gEx('txtCosto').setValue(arrResp[1]);
               	gridDescuentos.getStore().load	(
                									{
                                                    	url:'../paginasFunciones/funcionesTesoreria.php',
                                                        params:	{
                                                        			funcion:23,
                                                                    idCiclo:gEx('cmbCiclo').getValue(),
                                                                    idPeriodo: gEx('cmbPeriodo').getValue(),
                                                                    idNodo:esGridCosteo?arrNodos[0].data.id:arrNodos[0].id
                                                        		}
                                                    }
                								)
                ventanaAM.show();
            }
            else
            {
                msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
            }
        }
        obtenerDatosWeb('../paginasFunciones/funcionesTesoreria.php',funcAjax, 'POST','funcion=21&idCiclo='+gEx('cmbCiclo').getValue()+'&idPeriodo='+gEx('cmbPeriodo').getValue()+'&idNodo='+(gridCosteo?arrNodos[0].data.id:arrNodos[0].id),true);
    }
}

function crearGridDescuentos()
{
	var cmbTipoDescuento=crearComboExt('cmbTipoDescuento',arrTiposDescuento);
	
    var dteFechaLimite= new Ext.form.DateField	(
    												{
                                                    	id:'dteFechaLimite'
                                                    }
    											);
	
	var lector= new Ext.data.JsonReader({
                                            
                                            totalProperty:'numReg',
                                            fields: [
                                               			
		                                                {name: 'valorDescuento'},
		                                                {name:'tipoDescuento'},
		                                                {name:'fechaLimiteAplicacion', type:'date', dateFormat:'Y-m-d'},
                                                        {name:'costoServicio'}
                                                        
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
                                                            sortInfo: {field: 'fechaLimiteAplicacion', direction: 'ASC'},
                                                            groupField: 'fechaLimiteAplicacion',
                                                            remoteGroup:false,
				                                            remoteSort: false,
                                                            autoLoad:false
                                                            
                                                        }) 
	alDatos.on('beforeload',function(proxy)
    								{
                                    	proxy.baseParams.funcion='87';
                                        
                                    }
                        )   
       
    var cModelo= new Ext.grid.ColumnModel   	(
                                                        [
                                                            
                                                            
                                                            {
                                                                header:'Cantidad descuento',
                                                                width:120,
                                                                sortable:true,
                                                                css:'text-align:right;',
                                                                dataIndex:'valorDescuento',
                                                                editor:{
                                                                			xtype:'numberfield',
                                                                            allowDecimals:true,
                                                                            allowNegative:false
                                                                		},
                                                                renderer:function(val,meta,registro)
                                                                		{
                                                                        	if(registro.data.tipoDescuento!='3')
                                                                            {
                                                                            	return Ext.util.Format.usMoney(val);
                                                                            }
                                                                            else
                                                                            	return Ext.util.Format.number(val,'0,000.00')+ ' %' ;
                                                                        }
                                                            },
                                                            {
                                                                header:'Tipo de descuento',
                                                                width:150,
                                                                sortable:true,
                                                                dataIndex:'tipoDescuento',
                                                                editor:cmbTipoDescuento,
                                                                renderer:function(val)
                                                                		{
                                                                        
                                                                        	return formatearValorRenderer(arrTiposDescuento,val);
                                                                        }
                                                            },
                                                            {
                                                            
                                                                header:'Fecha m&aacute;xima <br />de aplicaci&oacute;n',
                                                                width:120,
                                                                css:'text-align:right;',
                                                                sortable:true,
                                                                editor:	dteFechaLimite,
                                                                dataIndex:'fechaLimiteAplicacion',
                                                                renderer:function(val)
                                                                		{
                                                                        	if(val)
                                                                            	return val.format('d/m/Y');
                                                                        }
                                                            },
                                                            {
                                                                header:'Costo del servicio',
                                                                width:120,
                                                                sortable:true,
                                                                css:'text-align:right;',
                                                                dataIndex:'costoServicio',
                                                                renderer:'usMoney'
                                                            }
                                                        ]
                                                    );
                                                    
    var tblGrid=	new Ext.grid.EditorGridPanel	(
                                                        {
                                                            id:'gridDescuentos',
                                                            store:alDatos,
                                                            x:10,
                                                            y:70,
                                                            clicksToEdit:2,
                                                            frame:false,
                                                            cm: cModelo,
                                                            stripeRows :true,
                                                            loadMask:true,
                                                            columnLines : true,
                                                            width:620,
                                                            sm:new Ext.grid.RowSelectionModel(),
                                                            height:230,
                                                            tbar:	[
                                                                        {
                                                                            icon:'../images/add.png',
                                                                            cls:'x-btn-text-icon',
                                                                            text:'Agregar descuento',
                                                                            handler:function()
                                                                                    {
                                                                                        var r=new regDescuento	(
                                                                                                                    {
                                                                                                                        valorDescuento:'',
                                                                                                                        tipoDescuento:'',
                                                                                                                        fechaLimiteAplicacion:'',
                                                                                                                        costoServicio:''
                                                                                                                    }
                                                                                                                )
                                                                                        tblGrid.getStore().add(r);
                                                                                        tblGrid.startEditing(tblGrid.getStore().getCount()-1,0);
                                                                                    }
                                                                            
                                                                        },'-',
                                                                        {
                                                                            icon:'../images/delete.png',
                                                                            cls:'x-btn-text-icon',
                                                                            text:'Remover descuento',
                                                                            handler:function()
                                                                                    {
                                                                                        var fila=tblGrid.getSelectionModel().getSelected();
                                                                                        if(!fila)
                                                                                        {
                                                                                            msgBox('Debe selecionar el descuento que desea remover');
                                                                                            return;
                                                                                        }
                                                                                        function resp(btn)
                                                                                        {
                                                                                            if(btn=='yes')
                                                                                            {
                                                                                                tblGrid.getStore().remove(fila);
                                                                                            }
                                                                                        }
                                                                                        msgBox('Est&aacute; seguro de querer remover el descuento seleccionado?',resp);
                                                                                    }
                                                                            
                                                                        }
                                                                    ],
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

	tblGrid.on('beforeedit',function(e)
    						{
                            	var dteFechaLimite=gEx('dteFechaLimite');
                                var fechaMin=obtenerMinFechaFila(e.row);
                                if(fechaMin!=null)
                                {
                                	fechaMin=fechaMin.add(Date.DAY,1);
                                }
                                var fechaMax=obtenerMaxFechaFila(e.row+1);
                                if(fechaMax!=null)
                                {
                                	fechaMax=fechaMax.add(Date.DAY,-1);
                                }
                                dteFechaLimite.setMinValue(fechaMin);
                                dteFechaLimite.setMaxValue(fechaMax);
                            }
              )
              
    tblGrid.on('afteredit',function(e)
    						{
                            	if((e.field=='valorDescuento')||(e.field=='tipoDescuento'))
                                {
                                    var costoServicio=gEx('txtCosto');
                                    var valor=0;
                                    if(e.record.data.valorDescuento=='')
                                    	e.record.data.valorDescuento=0;
                                    switch(e.record.data.tipoDescuento)
                                    {
                                        case '1':
                                            valor=e.record.data.valorDescuento;
                                        break;
                                        case '2':
                                            valor=parseFloat(costoServicio.getValue())-parseFloat(e.record.data.valorDescuento);
                                        break;
                                        case '3':
                                            valor=parseFloat(costoServicio.getValue())-(parseFloat(costoServicio.getValue())*(parseFloat(e.record.data.valorDescuento)/100));
                                        break;
                                    }
                                    if(valor<0)
                                        valor=0;
                                    e.record.set('costoServicio',valor);
                               	}
                                
                            }
              )
              
    return tblGrid;
}

function recalcularCostos()
{
	var gridDescuentos=gEx('gridDescuentos');
    var x;
    var fila;
    var costoServicio=gEx('txtCosto');
    if(costoServicio.getValue()=='')
    	costoServicio.setValue(0);
    var valor=0;
    for(x=0;x<gridDescuentos.getStore().getCount();x++)
    {
    	fila=gridDescuentos.getStore().getAt(x);
        
        
        valor=0;
        switch(fila.data.tipoDescuento)
        {
            case '1':
                valor=fila.data.valorDescuento;
            break;
            case '2':
                valor=parseFloat(costoServicio.getValue())-parseFloat(fila.data.valorDescuento);
            break;
            case '3':
                valor=parseFloat(costoServicio.getValue())-(parseFloat(costoServicio.getValue())*(parseFloat(fila.data.valorDescuento)/100));
            break;
        }
        if(valor<0)
            valor=0;
        fila.set('costoServicio',valor);
        
        
    }

}


function obtenerMinFechaFila(pos)
{
	if(pos==0)
    	return null;
    var fecha=null;   
	var gridDescuentos=gEx('gridDescuentos');
    var x;
    var fila;
    var costoServicio=gEx('txtCosto');
    var valor=0;
    for(x=0;x<pos;x++)
    {
    	fila=gridDescuentos.getStore().getAt(x);
        if(fila.data.fechaLimiteAplicacion!='')
        {
        	fecha=fila.data.fechaLimiteAplicacion;
        }
    }
    return fecha;
}

function obtenerMaxFechaFila(pos)
{
    var fecha=null;   
	var gridDescuentos=gEx('gridDescuentos');
    if(pos>gridDescuentos.getStore().getCount()-1)
    	return null;
    var x;
    var fila;
    var costoServicio=gEx('txtCosto');
    var valor=0;
    for(x=pos;x<gridDescuentos.getStore().getCount();x++)
    {
    	fila=gridDescuentos.getStore().getAt(x);
        if(fila.data.fechaLimiteAplicacion!='')
        {
        	fecha=fila.data.fechaLimiteAplicacion;
            break;
        }
    }
    return fecha;
}