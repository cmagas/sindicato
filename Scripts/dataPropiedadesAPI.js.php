<?php
	session_start();
	include("configurarIdiomaJS.php");
	include("conexionBD.php");
?>

function crearGridPropiedades(objConf)
{
	var idGrid='gPropiedades';
    if(objConf.id!=undefined)
    	idGrid=objConf.id;
	var ancho=250;
    var alto=400;
    if(objConf.ancho!=undefined)
    	ancho=objConf.ancho;
    
    if(objConf.alto!=undefined)
    	alto=objConf.alto;
        
    var oConf={
                  id:idGrid,
                  width: ancho,
                  height:alto,
                  viewConfig : 
                              {
                                  forceFit: false,
                                  scrollOffset: 2 
                              },
                  source:	{
                          },
                  propertyNames:	{
                                  },
                  customRenderers:	{
                                      },
                  customEditors:	{
                                  }  
              }      
	if(objConf.renderTo)                
    	oConf.renderTo=objConf.renderTo;

	if(objConf.region)                
    	oConf.region=objConf.region;        
	var gridPropiedades=	new Ext.grid.PropertyGrid	(
    														oConf
    													)


	if(objConf.x)                
    	oConf.x=objConf.x;  

	if(objConf.y)                
    	oConf.y=objConf.y; 
    
    if(objConf.border)                
    	oConf.border=objConf.border; 
    
     if(objConf.frame)                
    	oConf.frame=objConf.frame;                     
                                                        
	var colM=gridPropiedades.getColumnModel();      
    
    if(objConf.tituloCampo2)
    	colM.setColumnHeader(1,objConf.tituloCampo2);
    else                                          
	    colM.setColumnHeader(1,'Valor');
    
    if(objConf.tituloCampo1)
    	colM.setColumnHeader(0,objConf.tituloCampo1);
    else                                          
	    colM.setColumnHeader(0,'Propiedad');
    
    if(objConf.anchoCampo1)
    	colM.setColumnWidth(0,objConf.anchoCampo1);
    else                                          
	    colM.setColumnWidth(0,180);
    
    
    if(objConf.anchoCampo2)
    	colM.setColumnWidth(1,objConf.anchoCampo2);
    else                                          
	    colM.setColumnWidth(1,130);

	if(objConf.afterEdit)
    {
    	gridPropiedades.on('afteredit',objConf.afterEdit);
    }
    
    if(objConf.beforeEdit)
    {
    	gridPropiedades.on('beforeedit',objConf.beforeEdit);
    }
    	
	return gridPropiedades;
}

function generarOrigenDatos(gPropiedades,obj,objDatos)
{
	var x;
    var p;
    var objPropiedades={};
    var valor="''";
	var gridPropiedades= gPropiedades;
    if(obj.arrPropiedades)
    {
        for(x=0;x<obj.arrPropiedades.length;x++)
        {
            valor="";
            p=obj.arrPropiedades[x];
            eval('if(objDatos.'+p.id+'!=undefined) valor=objDatos.'+p.id+';else{ if(p.valorDefault!=undefined) valor=p.valorDefault;}');
            eval('objPropiedades.'+p.id+'="'+valor+'";');
            eval('gridPropiedades.propertyNames.'+p.id+'="'+p.etiqueta+'";');
            switch(p.tipo+'')
            {
                case '2':
                    var ct=0;
                    var arrOpciones=[];
                    var cadValores='';
                    for(ct=0;ct<p.opciones.length;ct++)
                    {
                        arrOpciones.push([p.opciones[ct].valor,p.opciones[ct].etiqueta]);
                        if(cadValores=='')
                            cadValores="['"+p.opciones[ct].valor+"','"+p.opciones[ct].etiqueta+"']";
                        else
                            cadValores+=",['"+p.opciones[ct].valor+"','"+p.opciones[ct].etiqueta+"']";
                    }
                    if(p.tpl)
                    	eval("var cmb_"+p.id+"=crearComboExt('cmb_"+p.id+"',arrOpciones,0,0,0,{confVista:'"+p.tpl+"'});");
                    else
                    	eval("var cmb_"+p.id+"=crearComboExt('cmb_"+p.id+"',arrOpciones);");
                    eval('gridPropiedades.customEditors.'+p.id+'=new Ext.grid.GridEditor(cmb_'+p.id+')');
                    eval('gridPropiedades.customRenderers.'+p.id+'=function(val){var arrValores=['+cadValores+']; var pos=existeValorMatriz(arrValores,val); if(pos!=-1) return arrValores[pos][1];}');
                break;
                case '6':
                	 eval("var ctrlNum_"+p.id+"=new Ext.grid.GridEditor(new Ext.form.NumberField({'allowDecimals':false}));");
                     eval('gridPropiedades.customEditors.'+p.id+'=ctrlNum_'+p.id);

                break;
                case '7':
                 	 eval("var ctrlDec_"+p.id+"=new Ext.grid.GridEditor(new Ext.form.NumberField({'allowDecimals':true}));");
                     eval('gridPropiedades.customEditors.'+p.id+'=ctrlDec_'+p.id);

                break;
                case '8':
                 	 eval("var ctrlFecha_"+p.id+"=new Ext.grid.GridEditor(new Ext.form.DateField({}));");
                     eval('gridPropiedades.customEditors.'+p.id+'=ctrlFecha_'+p.id);

                break;
                case '32':
                	 eval("var ctrlColor_"+p.id+"=new Ext.grid.GridEditor(new Ext.form.ColorField());");
                     eval('gridPropiedades.customEditors.'+p.id+'=ctrlColor_'+p.id);
                     eval('gridPropiedades.customRenderers.'+p.id+'=formatearColor;');
                break;
                default:
                    if(p.renderer!=undefined)
                        eval('gridPropiedades.customRenderers.'+p.id+'='+p.renderer);
                
                break;
            }
            
        }
    }
    gridPropiedades.setSource(objPropiedades);
    gridPropiedades.objDataSet=obj;
    gridPropiedades.objDatos=objDatos;
}

function obtenerValoresGrid(gPropiedades,formato)//1 Formato arreglo objetos; 2 objeto
{
	var x;
    var fila;
    var cadValores='';
    var obj='';
    var campo='';
    var defCampo;
    for(x=0;x<gPropiedades.getStore().getCount();x++)
    {
    	fila=gPropiedades.getStore().getAt(x);
        campo=fila.get('name');
        defCampo=obtenerDefinicionAtributo(gPropiedades,campo);
        if(formato==1)
        {
        	obj='{"campo":"'+campo+'","valor":'+fila.get('value')+'}';
        }
        else
        	obj='"'+campo+'":"'+fila.get('value')+'"';
        if(cadValores=='')
        	cadValores=obj;
        else
        	cadValores+=','+obj;
    }
    if(formato==1)
	    return '{"valores":['+cadValores+']}';
    return '{'+cadValores+'}';
}

function obtenerDefinicionAtributo(gPropiedades,atributo)
{
	var objDataSet=gPropiedades.objDataSet;
    var x;
    for(x=0;x<objDataSet.arrPropiedades.length;x++)
    {
		
    	if(objDataSet.arrPropiedades[x].id==atributo)
        	return objDataSet.arrPropiedades[x];
    }
    return null;
}

function formatearColor(val)
{
	val=val.replace("#","");
	return '<span style="border-style:solid; border-width:1px; border-color:#000;height:10px;width:10px;background-color:#'+val+'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;&nbsp;'+val;
}

