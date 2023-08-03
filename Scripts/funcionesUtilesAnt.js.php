<?php
	session_start();
	include("latis/configurarIdiomaJS.php");
	include("latis/conexionBD.php");
	$consulta="select tituloSistema from 903_variablesSistema";
	$lblAplicacion=$con->obtenerValor($consulta);
	if(!isset($_SESSION["AES"])||($_SESSION["AES"]==""))
	{
		$_SESSION["AES"]["inicio"]=rand(1000,9999).rand(1000,9999);
		$_SESSION["AES"]["fin"]=rand(1000,9999).rand(1000,9999);
	}
?>
var arrExtensionesComunes=	[		
								['Documentos de MS Word','doc'],
                                ['Documentos de MS Word','docx'],
                                ['Documentos PDF','pdf'],
                                ['Documentos de MS Excel','xls'],
                                ['Documentos de MS Excel','xlsx'],
                                ['Archivos de imagen','jpg'],
                                ['Archivos de imagen','png'],
                                ['Archivos de imagen','gif'],
                                ['Archivos de imagen','bmp'],
                                ['Archivos de imagen','tif'],
                                ['Archivos de Video','mpg'],
                                ['Archivos de Video','avi'],
                                ['Archivos de Video','mov'],
                                ['Archivos de Video','flv'],
                                ['Archivos de Video','3gp'],
                                ['Archivos de Video','mp4'],
                                ['Archivos de Video','div'],
                                ['Archivos de Video','divx'],
                                ['Archivos de Audio','mp3'],
                                ['Archivos de Audio','wav'],
                                ['Archivos de Audio','midi'],
                                ['Archivos de Audio','wma']
                                

				
							];
var arrVisores=	[
					['pdf','pdf_viewerBusqueda.php'],
                    ['doc','pdf_viewerBusqueda.php'],
                    ['docx','pdf_viewer.php'],
                    ['png','images_viewer.php'],
                    ['gif','images_viewer.php'],
                    ['jpg','images_viewer.php'],
                    ['jpeg','images_viewer.php']
                ];

var msgEspereAux=null;
var limpiarValorControl=true;
var lblAplicacion='<?php echo $lblAplicacion?>';
var arrDias=new Array();
arrDias[0]='Domingo';
arrDias[1]='Lunes';
arrDias[2]='Martes';
arrDias[3]='Mi\xE9rcoles';
arrDias[4]='Jueves';
arrDias[5]='Viernes';
arrDias[6]='Sábado';

var matrizDias=new Array();
matrizDias[0]=['0','Domingo'];
matrizDias[1]=['1','Lunes'];
matrizDias[2]=['2','Martes'];
matrizDias[3]=['3','Mi\xE9rcoles'];
matrizDias[4]=['4','Jueves'];
matrizDias[5]=['5','Viernes'];
matrizDias[6]=['6','Sábado'];
var arrMeses=[['1','Enero'],['2','Febrero'],['3','Marzo'],['4','Abril'],['5','Mayo'],['6','Junio'],['7','Julio'],['8','Agosto'],['9','Septiembre'],['10','Octubre'],['11','Noviembre'],['12','Diciembre']];

Ext.override(Ext.grid.GroupingView,	{
                                        doRender : function(cs, rs, ds, startRow, colCount, stripe)
                                        {
                                                if(rs.length < 1)
                                                {
                                                    return '';
                                                }
                                        
                                                if(!this.canGroup() || this.isUpdating)
                                                {
                                                    return Ext.grid.GroupingView.superclass.doRender.apply(this, arguments);
                                                }
                                        
                                                var groupField = this.getGroupField(),
                                                    colIndex = this.cm.findColumnIndex(groupField),
                                                    g,
                                                    gstyle = 'width:' + this.getTotalWidth() + ';',
                                                    cfg = this.cm.config[colIndex],
                                                    groupRenderer = cfg.groupRenderer || cfg.renderer,
                                                    prefix = this.showGroupName ? (cfg.groupName || cfg.header)+': ' : '',
                                                    groups = [],
                                                    curGroup, i, len, gid;
                                        
                                                for(i = 0, len = rs.length; i < len; i++){
                                                    var rowIndex = startRow + i,
                                                        r = rs[i],
                                                        gvalue = r.data[groupField];
                                        				
                                                        g = this.getGroup(gvalue, r, groupRenderer, rowIndex, colIndex, ds);
                                                       
                                                    if(!curGroup || curGroup.group != g)
                                                    {
                                                    	var pos=-1;
                                                        var x;
                                                        for(x=0;x<groups.length;x++)
                                                        {
                                                        	if(groups[x].group==g)
                                                            {
                                                            	pos=x;
                                                                break;
                                                            }
                                                        }
                                                        if(pos==-1)
                                                        {
                                                            gid = this.constructId(gvalue, groupField, colIndex);
                                                            this.state[gid] = !(Ext.isDefined(this.state[gid]) ? !this.state[gid] : this.startCollapsed);
                                                            curGroup = {
                                                                            group: g,
                                                                            gvalue: gvalue,
                                                                            text: prefix + g,
                                                                            groupId: gid,
                                                                            startRow: rowIndex,
                                                                            rs: [r],
                                                                            cls: this.state[gid] ? '' : 'x-grid-group-collapsed',
                                                                            style: gstyle
                                                                        };
                                                                    groups.push(curGroup);
                                                    	}
                                                        else
                                                        	groups[x].rs.push(r);
                                                    }
                                                    else
                                                    {
                                                        curGroup.rs.push(r);
                                                    }
                                                    r._groupId = gid;
                                                }
                                        
                                                var buf = [];
                                                for(i = 0, len = groups.length; i < len; i++)
                                                {
                                                    g = groups[i];
                                                    this.doGroupStart(buf, g, cs, ds, colCount);
                                                    buf[buf.length] = Ext.grid.GroupingView.superclass.doRender.call(
                                                            this, cs, g.rs, ds, g.startRow, colCount, stripe);
                                        
                                                    this.doGroupEnd(buf, g, cs, ds, colCount);
                                                }
                                                return buf.join('');
                                            }
										}
		)                                        

Ext.ux.IFrameComponent = Ext.extend(Ext.BoxComponent,{ 

  creado:false,
  objConf:null,
  
  myMask : null,
  cerrarMask:function()
              {
              	this.myMask.hide();
              },
              
	

	mostrarMascara:function()
    				{

                    	var padre=this.getEl().parent();
                    	padre.createChild(	
                                                    {
                                                        tag:'div',
                                                        id:'dMask_'+this.id,
                                                        class:'ext-el-mask'
                                                        
                                                    }
                                                )
                                                
                                                
						
                    	var div=padre.createChild(	
                                                    {
                                                        tag:'div',
                                                        id:'dMask2_'+this.id,
                                                        class:'ext-el-mask-msg x-mask-loading',
                                                        style:"left: 430px; top: 235px;"
                                                        
                                                    }
                                                )                                                
						
                        var hijo=div.createChild(	
                                                    {
                                                        tag:'div',
                                                        id:'dMask3_'+this.id
                                                        
                                                    }
                                                )   
                              
						hijo.update('Cargando...');                              
                                                
						div.center();
                        
                    },
                    
	ocultarMascara:function()
    				{
                    	var mascara=document.getElementById('dMask_'+this.id);
                        if(mascara)
                        {
                            mascara.parentNode.removeChild(mascara);
                            mascara=document.getElementById('dMask2_'+this.id);
                            mascara.parentNode.removeChild(mascara);
                        }
                    },                    

	ocultarMascaraManual:function()
                        {
                            var mascara=document.getElementById('dMask');
                            if(mascara)
                            {
                                mascara.parentNode.removeChild(mascara);
                                mascara=document.getElementById('dMask2');
                                mascara.parentNode.removeChild(mascara);
                            }
                        },
                  
              
  onRender: function (ct, position)
  {
  
  	var ctrlThis=this;
  	if(this.params==undefined)
    	this.params={}; 
     if(!this.creado)
     {
     	var objConf={tag:'iframe', id: 'iframe-'+ this.id, name:'iframe-'+ this.id, frameBorder:0, src:'',style:'overflow:scroll !important; -webkit-overflow-scrolling:touch !important;'};
	    this.el = ct.createChild(objConf); 
                           
                     
		var iFrame=document.getElementById( 'iframe-'+ this.id);
		asignarEvento(iFrame,'load',function()
        							{
                                    	ctrlThis.myMask.hide();
                                    }
        			);                     
                        
        if(this.loadFuncion)
        {
        	var iFrame=document.getElementById( 'iframe-'+ this.id);
            asignarEvento(iFrame,'load',this.loadFuncion);
        
        }
        var arrParam=[];
        var obj;
        if(this.params!=undefined)
        {
            
            for(campo in this.params)
            {
                obj=[campo,this.params[campo]];
                arrParam.push(obj);
            }
        }
        
        
        enviarFormularioDatos(this.url,arrParam,'POST','iframe-'+this.id);
        this.myMask={
                    	show:function()
                        		{
                                	ctrlThis.mostrarMascara('iframe-'+ctrlThis.id);
                                },
                        hide:function()
                        		{
                                	ctrlThis.ocultarMascara('iframe-'+ctrlThis.id);
                                }
                                
                    
                    }
                    
                    
                    
     }
    
     this.creado=true;


  },
  onShow:function()
  		{
        	
        },
  
  getFrameWindow:function()
  		{
        	return document.getElementById( 'iframe-'+ this.id).contentWindow;
        },
  
  
  getFrameDocument:function()
  					{
                    	var iFrame=document.getElementById( 'iframe-'+ this.id);
                    	if(iFrame.contentDocument)
                        	return iFrame.contentDocument;
                            
                            
                    	if(this.getFrameWindow().document)
	                    	return this.getFrameWindow().document;
                    },
  
  load:function(objParams)
  		{
        	var metodo='POST';
        	if(objParams.metodo)
            	metodo=objParams.metodo;
        	autoScroll=0;
        	var ctrlThis=this;	
        	this.objConf=objParams;
			var arrParam=[];
            var obj;
            
            if(objParams.params!=undefined)
            {
            	
                for(campo in objParams.params)
                {
                    obj=[campo,objParams.params[campo]];
                    arrParam.push(obj);
                }
            }
            
            //ctrlThis.myMask.show();
            enviarFormularioDatos(objParams.url,arrParam,metodo,'iframe-'+this.id);
        },
        

        
	reload:function()
    		{
            
                this.load(this.objConf);
            }        
        
}); 

Ext.override(Ext.form.TextField, 
{ // vtype=email validation problem for opera
    filterKeys : function(e){
        var k = e.getKey();
        if(e.isNavKeyPress() || k == e.BACKSPACE || (k == e.DELETE && e.button == -1)) return;
        var c = e.getCharCode(), cc = String.fromCharCode(c);
        if(!Ext.isGecko && e.isSpecialKey() && !cc) return;
        if(!this.maskRe.test(cc)) e.stopEvent();
    }
});

if ((typeof Range !== "undefined") && !Range.prototype.createContextualFragment)
{
	Range.prototype.createContextualFragment = function(html)
                                                {
                                                    var frag = document.createDocumentFragment(), 
                                                    div = document.createElement("div");
                                                    frag.appendChild(div);
                                                    div.outerHTML = html;
                                                    return frag;
                                                }
}

Ext.override(Ext.grid.GridView, {
                                   afterRenderUI: function() {
                                                                    var grid = this.grid;
                                                                    
                                                                    this.initElements();
                                                            
                                                                    
                                                                    Ext.fly(this.innerHd).on('click', this.handleHdDown, this);
                                                            
                                                                    this.mainHd.on({
                                                                        scope    : this,
                                                                        mouseover: this.handleHdOver,
                                                                        mouseout : this.handleHdOut,
                                                                        mousemove: this.handleHdMove
                                                                    });
                                                            
                                                                    this.scroller.on('scroll', this.syncScroll,  this);
                                                                    
                                                                    if (grid.enableColumnResize !== false) {
                                                                        this.splitZone = new Ext.grid.GridView.SplitDragZone(grid, this.mainHd.dom);
                                                                    }
                                                            
                                                                    if (grid.enableColumnMove) {
                                                                        this.columnDrag = new Ext.grid.GridView.ColumnDragZone(grid, this.innerHd);
                                                                        this.columnDrop = new Ext.grid.HeaderDropZone(grid, this.mainHd.dom);
                                                                    }
                                                            
                                                                    if (grid.enableHdMenu !== false) 
                                                                    {
                                                                        this.hmenu = new Ext.menu.Menu({id: grid.id + '-hctx'});
                                                                        this.hmenu.add(
                                                                                            {itemId:'asc',  text: this.sortAscText,  cls: 'xg-hmenu-sort-asc'},
                                                                                            {itemId:'desc', text: this.sortDescText, cls: 'xg-hmenu-sort-desc'}
                                                                                            
                                                                                        );
                                                            
                                                                        if (grid.enableColumnHide !== false) 
                                                                        {
                                                                            this.colMenu = new Ext.menu.Menu({id:grid.id + '-hcols-menu'});
                                                                            this.colMenu.on({
                                                                                scope     : this,
                                                                                beforeshow: this.beforeColMenuShow,
                                                                                itemclick : this.handleHdMenuClick
                                                                            });
                                                                            this.hmenu.add('-', {
                                                                                itemId:'columns',
                                                                                hideOnClick: false,
                                                                                text: this.columnsText,
                                                                                menu: this.colMenu,
                                                                                iconCls: 'x-cols-icon'
                                                                            });
                                                                        }
                                                                        
                                                                       	this.hmenu.add(
                                                                                            {itemId:'excel',  text: 'Exportar a Excel',  icon: '../images/page_excel.png',handler:function(){grid.funcionExportarExcel()}}
                                                                                       )
                                                                       
                                                            
                                                                        this.hmenu.on('itemclick', this.handleHdMenuClick, this);
                                                                    }
                                                            
                                                                    if (grid.trackMouseOver) {
                                                                        this.mainBody.on({
                                                                            scope    : this,
                                                                            mouseover: this.onRowOver,
                                                                            mouseout : this.onRowOut
                                                                        });
                                                                    }
                                                            
                                                                    if (grid.enableDragDrop || grid.enableDrag) {
                                                                        this.dragZone = new Ext.grid.GridDragZone(grid, {
                                                                            ddGroup : grid.ddGroup || 'GridDD'
                                                                        });
                                                                    }
                                                            
                                                                    this.updateHeaderSortState();
                                                                }
                                 }
			)                                 


Ext.override(Ext.grid.GridPanel, {

	funcionExportarExcel:function()
    					{
                        	if(typeof(funcExportacionExcel)=='undefined')
                            {
                            	this.exportarExcel();
                            }
                            else
                            	funcExportacionExcel();	
                        },
                        
	exportarExcel:function(nPlantilla,lineaI,letraI,cargarPlantilla)
    				{	
                    	
                        var x;
                        var fila;
                        var cadDatos='';
                        var cadEncabezados='';
                        var cm=this.getColumnModel();
                        var col;
                        var arrEncabezado=new Array();
                        var obj={};
                        var datosTotal=new Array();
                        var filaDatos=new Array();
                        for(x=0;x<this.getColumnModel().config.length;x++)
                        {
                        	col=cm.config[x];
                        	if((!cm.isHidden(x))&&(col.header!='')&&(col.header!=undefined)&&(col.dataIndex!=''))
                            {
                            	obj={}
                            	obj.titulo=entitiesDecode(col.header);
                                obj.noColumna=x;
                                obj.campo=col.dataIndex;
                                obj.renderer=col.renderer;
                            	arrEncabezado.push(obj);
                                filaDatos.push(obj.titulo);
                            }
                        }
                        
                        datosTotal.push(filaDatos);
                        var y;
                        var valor;
                        for(x=0;x<this.getStore().getCount();x++)
                        {
                        	fila=this.getStore().getAt(x);
                            filaDatos=new Array();
                            for(y=0;y<arrEncabezado.length;y++)
                            {
                            

                                valor='';
                                
                               
                                
                            	if((fila.get(arrEncabezado[y].campo))&&(fila.get(arrEncabezado[y].campo)!=undefined))
                                {
                            		valor=''+Ext.util.Format.stripTags(arrEncabezado[y].renderer(fila.get(arrEncabezado[y].campo),'',fila,x,arrEncabezado[y].noColumna));
                                }
                                if(valor.indexOf('<font')!='-1')
                                {
                                	var arrDatos=valor.split('>');
                                    
                                    valor=arrDatos[1];
                                    arrDatos=valor.split('</font');
                                    valor=arrDatos[0];
                                   
                                }
                            	valor=cv(entitiesDecode(valor));
                            	filaDatos.push(valor);
                            }
                            datosTotal.push(filaDatos);
                        }
                        var lineaInicial=1;
                        if(lineaI!=undefined)
                        	lineaInicial=lineaI;
                        
                        var letraInicial='A';
                        if(letraI!=undefined)
                        	letraInicial=letraI;
                        var nArchivo='listado.xls';
                        if(nPlantilla!=undefined)
                        	nArchivo=nPlantilla;	
                        var arrDatos=[['nArchivo',nArchivo]['lineaInicial',lineaInicial],['letraInicial',letraInicial],['data',serialize(datosTotal)]];
                        if(cargarPlantilla)
                        	arrDatos.push(['plantilla','true']);
                        enviarFormularioDatos('../reportes/gridToExcel.php',arrDatos);
                        
                        
                    }
    
});



Ext.data.ArrayStore=Ext.extend(Ext.data.Store,{constructor:function(a){Ext.data.ArrayStore.superclass.constructor.call(this,Ext.apply(a,{reader:new Ext.data.ArrayReader(a)}))},loadData:function(e,b){if(this.expandData===true){var d=[];for(var c=0,a=e.length;c<a;c++){d[d.length]=[e[c]]}e=d}Ext.data.ArrayStore.superclass.loadData.call(this,e,b)}});Ext.reg("arraystore",Ext.data.ArrayStore);Ext.data.SimpleStore=Ext.data.ArrayStore;Ext.reg("simplestore",Ext.data.SimpleStore);

Ext.DataView.prototype.getStore=function()
								{
									return this.store;	
								}



Ext.form.ComboBox.prototype.getStore = function() 
									{
										return this.store;
									}


registroSimple=Ext.data.Record.create	(
											[
												{name: 'id'},
												{name: 'nombre'}
											]
										)

function updateTimes(field) 
{
    var min = field.parseDate(field.minValue);
    if(!min)
	{
        min = new Date().clearTime();
    }
    var max = field.parseDate(field.maxValue);
    if(!max)
	{
        max = new Date().clearTime().add('mi', (24 * 60) - 1);
    }
    var times = [];
    while(min <= max)
	{
        times.push([min.dateFormat(field.format)]);
        min = min.add('mi', field.increment);
    }
	
	field.getStore().removeAll();
    field.getStore().loadData(times);
};

function generarIntervaloHoras(minimo,maximo,incremento,formatoValor,formatoEtiqueta) 
{
    var min = minimo;
    var max = maximo;
    var times = new Array();
	var ct=0;
    
    if(!formatoValor)
    	formatoValor='H:i';
    
    if(!formatoEtiqueta)
    	formatoEtiqueta='h:i A';
    
    
    while(min <= max)
	{
        var filaArreglo=new Array();
		filaArreglo[0]=min.dateFormat(formatoValor);
		filaArreglo[1]=min.dateFormat(formatoEtiqueta);
		times[ct]=filaArreglo;
        min = min.add('mi', incremento);
		ct++;
    }
	return times;
};

function convertirCadenaHora(cadena)
{
	var arrHoraI=cadena.split(':');
	
	if(arrHoraI[0].indexOf('0')==0)
		arrHoraI[0]=arrHoraI[0].substring(1);
	var horaI=parseInt(arrHoraI[0]);
	var minutosI=parseInt(arrHoraI[1]);
	
	var nHora=new Date().clearTime();
	
	nHora.setHours(horaI);
	nHora.setMinutes(minutosI);
	return nHora;
}

function convertirCadenaFecha(cadena)
{
	var arrFecha;
    
    if(cadena.indexOf('/')!=-1)
    {
		arrFecha=cadena.split('/');
    
        if(arrFecha[0].indexOf('0')===0)
            arrFecha[0]=arrFecha[0].substr(1);
        if(arrFecha[1].indexOf('0')===0)
            arrFecha[1]=arrFecha[1].substr(1);
        var dia=parseInt(arrFecha[0]);
        var mes=parseInt(arrFecha[1])-1;
        var anio=parseInt(arrFecha[2]);
        var nFecha=new Date(anio,mes,dia);
        return nFecha;
    }
    else
    {
    	
    	arrFecha=cadena.split('-');
    
        if(arrFecha[2].indexOf('0')===0)
            arrFecha[2]=arrFecha[2].substr(1);
        if(arrFecha[1].indexOf('0')===0)
            arrFecha[1]=arrFecha[1].substr(1);
            
       
            
        var dia=parseInt(arrFecha[2]);
        var mes=parseInt(arrFecha[1])-1;
        var anio=parseInt(arrFecha[0]);
        
        var nFecha=new Date(anio,mes,dia);
      
        return nFecha;
    }
	
}



function obtenerDiasDiferencia(fechaInicio,fechaFin)
{
	var fecha1=convertirCadenaFecha(fechaInicio);
	var fecha2=convertirCadenaFecha(fechaFin);
	var ct=0;
	while(fecha1<=fecha2)
	{
		fecha1.setDate(fecha1.getDate() + 1);
		ct++;
	}
	return ct;
	
}

Ext.override
(Ext.form.CheckboxGroup, 
{
  getNames: function() 
  			{
    			var n = [];

				this.items.each(	function(item) 
									{
									  if (item.getValue()) 
									  {
										n.push(item.getName());
									  }
									}
								);

    			return n;
  			},


	getLabels:function()
	{
		var v = [];
			
				this.items.each(
									function(item) 
									{
									  if (item.getValue()) 
									  {
										v.push(item.boxLabel);
									  }
									}
								);
			
				return v;
	},

  getValues: function() 
  			{
				var v = [];
			
				this.items.each(
									function(item) 
									{
										
									  if (item.getValue()) 
									  {
										v.push(item.value);
									  }
									}
								);
			
				return v;
			  },

	  setValues: function(v) 
	  {
		
		this.items.each
		(
			function(item) 
			{
				
				if(existeValorArreglo(v,item.value)!=-1)	
				  item.setValue(true);
			}
		);
	  }
}
);

function gE(idElemento)//getElement
{
	return document.getElementById(idElemento);
}

function gEN(name,sHidden)
{
	
	var arrElementos=[];
    
    if((!window.$)&&(!window.jQuery))
	    arrElementos=document.getElementsByName(name);
    else
    	arrElementos=$('[name='+name+']');
    
    if(!sHidden)
		return arrElementos;	
    else
    {
    	var x;
        var ctrl;
        var aFinal=[];
        for(x=0;x<arrElementos.length;x++)
        {
        	ctrl=arrElementos[x];
			
            if(ctrl.nodeName.toUpperCase()=='INPUT')
            	aFinal.push(ctrl);
            
        }
        return aFinal;
    }
}

function oE(idElemento)//ocultarControl
{ //alert(idElemento);
	var ctrlE=gE(idElemento);
    if(ctrlE)
		ctrlE.style.display='none';
}

function mE(idElemento)//mostrarControl
{
	var ctrlE=gE(idElemento);
    if(ctrlE)
		ctrlE.style.display='';
}

function hE(idElemento)//habiltarControl
{
	gE(idElemento).disabled=false;
}

function dE(idElemento)//deshabiltarControl
{
	var elem=gE(idElemento);
	elem.disabled=true;
}

function cE(tipoElemento)
{
	var elem=document.createElement(tipoElemento);
	return elem;
}

//cadenas
function trim(s)//quitarespacios
{
	
	return s.replace(/^\s*|\s*$/g,"");
}

//Validadores

function esListaNoVacia(obj,lista,msg,minItems)
{
	var res=obj.childNodes.length;
	if(res<minItems)
	{
		msgBox(msg);
		marcarCampo(lista);
		return false;
	}
	desmarcarCampo(lista);
	return true;
}

function esNoVacio(obj)
{
	var res=obj.value.trim();
	if(res.length==0)
	{
		msgBox("El campo es obligatorio");
		marcarCampo(obj);
		return false;
	}
	desmarcarCampo(obj);
	return true;
}

function esNumero(obj)
{
	entero=parseInt(obj.value);
	if(isNaN(entero))
	{
		msgBox('El valor ingresado no es num&eacute;rico');
		marcarCampo(obj);
		return false;
	}
	desmarcarCampo(obj);

	return true;
}

function esNumeroNoVacio(obj)
{
	if(esNoVacio(obj))
	{
		return esNumero(obj);
	}
}

//mensajes

//funciones para teclas presionadas

function esNumero(evt)
{
	var nav4 = window.Event ? true : false;
	var key = nav4 ? evt.which : evt.keyCode;
	return (key <= 13 || (key >= 48 && key <= 57));
}						   
//
function msgBox(mensaje,fun,icono)
{
	if(typeof(msgPersonalizado)!='undefined')
    {
    	msgPersonalizado(mensaje,fun);
        return;
	}
    funcion	=fun;
	if(fun==undefined)
		funcion=null
    var ICONO=Ext.MessageBox.WARNING;
    if(icono)
    	ICONO=icono;
	Ext.MessageBox.show(
							{
							 	title: lblAplicacion,
							   	msg: mensaje,
							   	buttons: Ext.MessageBox.OK,
							   	icon: ICONO,
								fn:funcion
							}
						);
}

function  msgBoxWin(mensaje,resp,ancho,alto)
{
	var alt=140;
	if(alto!=undefined)
		alt=alto;
	var anch=420;
	if(ancho!=undefined)
		anch=ancho;
	
	var ventanaAM = new Ext.Window(
									{
										title: lblAplicacion,
										width: anch,
										height:alt,
										layout: 'absolute',
										plain:true,
										modal:true,
										border:false,
										bodyStyle:'padding:3px;color:blue',
										buttonAlign:'center',
										items: {
															xtype:'label',
															html:'<table width="100%"><tr><td><img src="../images/icon-question.gif"></td><td width="10"></td><td valign="top"><span style="font:12px tahoma,arial,helvetica,sans-serif;color:#444444">'+mensaje+'</span></td></tr></table>',
															x:10,
															y:20
												},
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
															text: 'OK',
															handler: function()
																	{
																		resp();
																		ventanaAM.close();
																	}
														}
													]
									}
								);
	ventanaAM.show();
}

function msgConfirm(mensaje,resp)
{
	Ext.MessageBox.show(
							{
							 	title: lblAplicacion,
							   	msg: mensaje,
							   	buttons: Ext.MessageBox.YESNO,
							   	icon: Ext.MessageBox.QUESTION,
								fn:resp
							}
						);
}


function  msgConfirmWin(mensaje,resp,ancho,alto)
{
	var alt=140;
	if(alto!=undefined)
		alt=alto;
	var anch=420;
	if(ancho!=undefined)
		anch=ancho;
	
	var ventanaAM = new Ext.Window(
									{
										title: lblAplicacion,
										width: anch,
										height:alt,
										layout: 'absolute',
										plain:true,
										modal:true,
										border:false,
										bodyStyle:'padding:3px;color:blue',
										buttonAlign:'center',
										items: {
															xtype:'label',
															html:'<table width="100%"><tr><td><img src="../images/icon-question.gif"></td><td width="10"></td><td valign="top"><span style="font:12px tahoma,arial,helvetica,sans-serif;color:#444444">'+mensaje+'</span></td></tr></table>',
															x:10,
															y:20
												},
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
															
															text: 'Yes',
															handler: function()
																	{
																		resp('yes');
																		ventanaAM.close();
																	}
														},
														{
															text: 'No',
															handler:function()
																	{
																		resp('no');
																		ventanaAM.close();
																	}
														}
													]
									}
								);
	ventanaAM.show();
}

//formato a controles
function marcarCampo(obj)//fondo de un color
{
	obj.style.background='#FFd2dC';
	obj.style.backgroundColor='#FFd2dC';
}

function desmarcarCampo(obj)//quitarle el color
{
	obj.style.background='#FFFFFF';
	obj.style.backgroundColor='#FFFFFF';
}

//funciones de arbol

function limpiarNodo(nodo)
{
	
	while(nodo.hasChildNodes())
	{
		nodo.removeChild(nodo.item(0));
	}
}

function obtenerNodoSel(raiz)
{
	var z=0;
	var enc=false;
	var nodoSel=null;
	while((z<raiz.childNodes.length) &&(!enc))
	{
		enc=raiz.childNodes[z].isSelected();
		if(enc)
			nodoSel=raiz.childNodes[z];
		else
		{
			nodoSel=obtenerNodoSel(raiz.childNodes[z]);
			if(nodoSel!=null)
				enc=true;
		}
		z++;
	}
	return nodoSel;
}

//RevistaE
function rellenarValoresVacios(dSet,columna,valor)
{
	for(x=0;x<dSet.getCount();x++)
	{
		fila=dSet.getAt(x);
		if(fila.get(columna).trim()=='')
			fila.set(columna,valor);
	}
}

function rellenarValoresVaciosColumna(dSet,columna,columnaCopia)
{
	for(x=0;x<dSet.getCount();x++)
	{
		fila=dSet.getAt(x);
		if(fila.get(columna).trim()=='')
			fila.set(columna,'['+fila.get(columnaCopia)+']');
	}
}

function obtenerFilaIdioma(dSet,idIdioma)
{
	for(x=0;x<dSet.getCount();x++)
	{
		fila=dSet.getAt(x);
		if(fila.get('idIdioma')==idIdioma)
			return fila;
	}
}

function obtenerPosFila(dSet,columna,valor,posIgn)
{

	for(x=0;x<dSet.getCount();x++)
	{
		fila=dSet.getAt(x);
		var vFila=fila.get(columna)+'';
		if(vFila==valor)
        {
        	if((typeof(posIgn)=='undefined')||(x!=posIgn))
				return x;
        }
	}
	return -1;
}

function validarGridExt(dSet,columna,idIdioma)
{
	var fila;
	var nomDefault=false;
	var ct=0;
	for(x=0;x<dSet.getCount();x++)
	{
		fila=dSet.getAt(x);
		if(fila.get(columna).trim()!='')
		{
			if(fila.get('idIdioma')==idIdioma)
				nomDefault=true;
			ct++;
		}
	}
	if(dSet.getCount()==ct)
		return 0; //Sin problemas
	
	if(!nomDefault)
		return 1; //El nombre en idioma original no fue especificado
	
	return 2;
}

function validarCampoNoVacio(dSet,columna)
{
	var filaDSet;
	
	for(x=0;x<dSet.getCount();x++)
	{
		filaDSet=dSet.getAt(x);
		
		if((filaDSet.get(columna)+'').trim()=='')
			return x+1;
	}
	return -1;
}

function limpiarCombo(combo)//Limpiar combo
{
	while (combo.length > 0) 
        combo.remove(0);
}

function crearRichText(idCtrl,divDestino,ancho,alto,conf,valor)
{

	var div = document.getElementById(divDestino);
	var vTexto='';
	if (valor!=undefined)
		vTexto=valor;
	var configuracion="../fckconfig.js";
    if((conf!='')&&(conf!=undefined))
    	configuracion=conf;
		
	var conf=	{
					Name:idCtrl,
					Width:ancho,
					Height:alto,
					Value:vTexto,
					config:configuracion
					
				}
				
	var richText=new Ext.ux.FCKeditor(conf);
	
	var Panel=new Ext.Panel	(
							 	{
									id:'panel_'+idCtrl,
									renderTo:divDestino,
									items:[richText]
								}
							)
	
    return richText;
}

function crearCampoHora(idControlDestino,hiddenValor,horaMinima,horaMaxima,interval)
{
	hMinima='00:00';
	hMaxima='23:59';
	
	if(horaMaxima!=undefined)
		hMaxima=horaMaxima;
	if(horaMinima!=undefined)
		hMinima=horaMinima;
	intervalo=15;
	if(interval!=undefined)
		intervalo=interval;
	
	var arrHInicial=hMinima.split(':');	
	var arrHFinal=hMaxima.split(':');
	var horaInicial=new Date(2010,5,10,parseInt(arrHInicial[0]),parseInt(arrHInicial[1]));
	var horaFinal=new Date(2010,5,10,parseInt(arrHFinal[0]),parseInt(arrHFinal[1]));
	if(horaInicial>horaFinal)
    {
    	var temp=horaInicial;
        horaInicial=horaFinal;
        horaFinal=temp;
    }
    
	var arrHoras=generarIntervaloHoras(horaInicial,horaFinal,intervalo);
	
	
	function funcHoraCamb(campo,nuevoV,viejoV)
	{
		gE(hiddenValor).value=nuevoV;
	}
	
	/*var hora=new Ext.form.TimeField
										(
											{
												id:'f_'+idControlDestino,
												width:100,
												renderTo:idControlDestino,
												readOnly:true,
												minValue:hMinima,
												maxValue:hMaxima,
												height:150,
												format:'H:i',
												increment:intervalo
												
											}
										)*/
	var hora=crearComboExtFormulario(idControlDestino,hiddenValor,arrHoras);
	hora.setWidth(110);
	var hHora=gE(hiddenValor);
	hora.setValue(hHora.value);
	hora.on('change',funcHoraCamb);
	
    
	return hora;
}

function crearCampoHoraExt(idControl,horaMinima,horaMaxima,interval,esGrid)
{
	hMinima='00:00';
	hMaxima='23:59';
	
	if(horaMaxima!=undefined)
		hMaxima=horaMaxima;
	if(horaMinima!=undefined)
		hMinima=horaMinima;
	intervalo=15;
	if(interval!=undefined)
		intervalo=interval;
		
	var arrHInicial=hMinima.split(':');	
	var arrHFinal=hMaxima.split(':');
	var horaInicial=new Date(2010,5,10,parseInt(arrHInicial[0]),parseInt(arrHInicial[1]));
	var horaFinal=new Date(2010,5,10,parseInt(arrHFinal[0]),parseInt(arrHFinal[1]));
	var arrHoras=generarIntervaloHoras(horaInicial,horaFinal,intervalo);

    var hora;
    if(!esGrid)
	    hora=crearComboExt(idControl,arrHoras);
    else
    	hora=crearComboExt(idControl,arrHoras,0,0,null,{transform:false});
	return hora;
}

function crearCampoFecha(idControlDestino,hiddenValor,minimaFecha,maximaFecha,funcChange)
{
	minFecha=null;
	maxFecha=null;
	if(maximaFecha!=undefined)
		maxFecha=maximaFecha;
	if(minimaFecha!=undefined)
		minFecha=minimaFecha;
	function funcFechaCamb(campo,nuevoV,viejoV)
	{
		var f=new  Date(nuevoV);
		gE(hiddenValor).value=f.format('d/m/Y');
	}
	
	
	var FNac=new Ext.form.DateField
									(
										{
											id:'f_'+idControlDestino,
											width:100,
											format:'d/m/Y',
											renderTo:idControlDestino,
											readOnly:true,
											minValue:minFecha,
											maxValue:maxFecha,
											height:150
										}
									)
	var FNacimiento=gE(hiddenValor);
	FNac.setValue(FNacimiento.value);
	
	if(funcChange==undefined)
		FNac.on('change',funcFechaCamb);
	else
		FNac.on('select',funcChange);
	var contenedor=FNac.getEl();
	var idImg=contenedor.next().id;
	var img=gE(idImg);
	if(Ext.isIE)
		img.removeAttribute('style');
	else
		img.setAttribute('style','');
	
	
	return FNac;
}

function crearComboExtFormulario(idControlDestino,hiddenValor,arregloValores)
{
	var almacen=new Ext.data.SimpleStore	(
											 	{
													fields:	[
															 	{name:'id'},
																{name:'texto'}
															]
												}
											)
	var comboTmp=document.createElement('select');
	var combo =new Ext.form.ComboBox	(
												{
													
													id:'f_'+idControlDestino,
													mode:'local',
                                                    name:idControlDestino,
													emptyText:'Elija una opci\u00f3n',
													store:almacen,
													displayField:'texto',
													valueField:'id',
													transform:comboTmp,
													editable:false,
													typeAhead: true,
													triggerAction: 'all',
													lazyRender:true,
													renderTo:idControlDestino
												}
											)
	almacen.loadData(arregloValores);

	function funcValorCambiado(campo,nuevoV,viejoV)
	{
		gE(hiddenValor).value=nuevoV;
	}
	var hCombo=gE(hiddenValor);
	combo.setValue(hCombo.value);
	combo.on('change',funcValorCambiado);
	return combo;
}

Ext.form.TriggerField.override({
    afterRender: function() {
         Ext.form.TriggerField.superclass.afterRender.call(this);
    }
});

function cerrarSesion(noRedireccion)
{
	function procResp()
	{
    	if(noRedireccion==undefined)
        {
			document.location.href="<?php if($paginaCierreLogin=="") echo "../principal/inicio.php"; else echo $paginaCierreLogin; ?>";		
        }
	}
	obtenerDatosWeb('../paginasFunciones/funciones.php',procResp,'POST','funcion=2',true);
	
}

function hK()
{
	if(typeof keyMap!='undefined')
		keyMap.enable();	
}

function dK()
{
	if(typeof keyMap!='undefined')
		keyMap.disable();
}

function cv(valor,ignorarRetorno,evitaCodificarURI)
{

	valor=valor+'';
	valor=valor.replace(/"/gi,'\\"');
	
	if((ignorarRetorno==undefined)||(ignorarRetorno==false))		
		valor=valor.replace(/\n/gi, '<br />');
	else
		valor=valor.replace(/\n/gi, '');
	valor=valor.replace(/\r/gi, '');
    valor=valor.replace(/\t/gi, '  ');
    if(!evitaCodificarURI)
		return encodeURIComponent(valor);
  	return (valor);
}

function dv(valor,urlEncode)
{
	var cadena=valor;
    if((urlEncode==undefined)||(urlEncode))
	    cadena=decodeURIComponent(valor);
    cadena=cadena.replace(/#R<br \/>/gi,'\n\r');
	return cadena;
}

function selElemCombo(combo,valor)
{
	var x;
    var valorAux=valor+'';
    var valRef;
	for(x=0;x<combo.length;x++)
	{
		valRef=combo.options[x].value+'';
		if(valRef==valorAux)
		{
			combo.options[x].selected=true;
			break;
		}
	}
}

function esEntero(numero)
{
		var valor=numero;
		var re = /^\-?[0-9]+$/;
			return re.test(valor);
}

function lanzarEvento(ctrl,evento,params)
{

	var control=ctrl;
	
    if(typeof(ctrl)=='string')
    	control=gE(ctrl);
	try
	{
    	var versionIE=parseFloat(vIE());
		if ((document.createEventObject)&&(versionIE<9))
		{
			// dispatch for IE
            
			var evt = document.createEventObject();
            if(params)
            	evt.params=params;
			return control.fireEvent('on'+evento,evt)
		}
		else
		{
			// dispatch for firefox + others
			var evt = document.createEvent("HTMLEvents");
            if(params)
            	evt.params=params;
			evt.initEvent(evento, true, true ); // event type,bubbling,cancelable
			return !control.dispatchEvent(evt);
		}
	}
	catch(e)
	{
    	
	}
}

function asignarEventoChange(control)
{
	if (control.addEventListener)
    {
    	control.addEventListener	('change',	function(event)
        										{   
	                                                var control=event.target;
                                                	var cFiltro=event.target.getAttribute('cFiltro');
                                                    var condicion=event.target.getAttribute('condicion');
                                                    var cDestino=event.target.getAttribute('cDestino'); 
                                                    var camposDependencias=event.target.getAttribute('camposDependencias'); 
                                                    if(camposDependencias==null)
                                                    	camposDependencias='';
                                                    var condComp=event.target.getAttribute('condComp'); 
                                                     if(condComp==null)
                                                    	condComp='';
                                                	actualizarCombo(control,cFiltro,condicion,cDestino,camposDependencias,condComp);
                                                 }
                                     , false);
    } 
    else 
    	if (control.attachEvent)
        {
   	    	

	    	control.attachEvent('onchange', function(event)
            								{ 
                                            	var control=event.srcElement;
                                                var cFiltro=event.srcElement.getAttribute('cFiltro');
                                                var condicion=event.srcElement.getAttribute('condicion');
                                                var cDestino=event.srcElement.getAttribute('cDestino');
                                                var camposDependencias=event.srcElement.getAttribute('camposDependencias'); 
                                                if(camposDependencias==null)
                                                	camposDependencias='';
                                                var condComp=event.srcElement.getAttribute('condComp'); 
                                                if(condComp==null)
                                                	condComp='';
                                              	
                                            	actualizarCombo(control,cFiltro,condicion,cDestino,camposDependencias,condComp);
                                             }
                               );
    	}
}

function asignarEvento(controlID,evento,funcion)
{
	var control;
	if(typeof(controlID)=='object')
		control=controlID;
	else
		control=gE(controlID);
	if(!control)
    	return;
	if (control.addEventListener)
    {
    	control.addEventListener(evento,function(event)
										{
											funcion(event.target);
										}
								, false);
    } 
    else 
    	if (control.attachEvent)
        {
   	    	

	    	control.attachEvent('on'+evento, function(event)
											{
												funcion(event.srcElement);
											}
								);
    	}
}

function asignarEventoChangeListado(control,tipoControl)
{
	if (control.addEventListener)
    {
    	control.addEventListener	('change',	function(event)
        										{   
	                                                var control=event.target;
                                                	var cFiltro=event.target.getAttribute('cFiltro');
                                                    var condicion=event.target.getAttribute('condicion');
                                                    var cDestino=event.target.getAttribute('cDestino'); 
                                                	actualizarListado(control,cFiltro,condicion,cDestino,tipoControl);
                                                 }
                                     , false);
    } 
    else 
    	if (control.attachEvent)
        {
   	    	

	    	control.attachEvent('onchange', function(event)
            								{ 
                                            	var control=event.srcElement;
                                                var cFiltro=event.srcElement.getAttribute('cFiltro');
                                                var condicion=event.srcElement.getAttribute('condicion');
                                                var cDestino=event.srcElement.getAttribute('cDestino');
                                            	actualizarListado(control,cFiltro,condicion,cDestino,tipoControl);
                                             }
                               );
    	}
}

function actualizarCombo(combo,cFiltro,condicion,cDestino,camposDependencias,condComp)
{
	var valorCondicion=combo.options[combo.selectedIndex].value;
	var idFormulario=gE('idFormulario').value;
    var cadObj="";
    if(camposDependencias!='')
    {
        var arrJava=camposDependencias.split(',');
        for(x=0;x<arrJava.length;x++)
        {
            control=arrJava[x];
            pos=existeValorMatriz(diccionarioCtrl,control);
            if(pos!=-1)
            {
                campo=diccionarioCtrl[pos][1];
                fila='"'+control+'":"'+cv(obtenerValorCampo(campo))+'"';
                
            }
            else
            	fila='"'+control+'":"-100584"';
            if(cadObj=='')
            	cadObj=fila;
            else
	            cadObj+=','+fila;
        }
 	}    
   
    if(cadObj!='')   
    	cadObj='{'+cadObj+'}';
    
	function funcResp()
    {
        arrResp=peticion_http.responseText.split('|');
        if(arrResp[0]=='1')
        {
        	
        	var arrOpciones=eval(arrResp[1]);
            var comboD=gE('_'+cDestino+'vch');
            limpiarCombo(comboD);
            var numOpt=arrOpciones.length;
            var x;
            var opt;
            var ct=0;

            for(x=0;x<numOpt;x++)
            {
            	opt=document.createElement('option');
                opt.value=arrOpciones[x][0];
                opt.text=arrOpciones[x][1];
                comboD.options[x]=opt;
            }
            if(comboD.getAttribute('cFiltro')!=null)
            {
             	lanzarEvento(comboD,'change');   
            }

        }
        else
        {
            msgBox('No se ha podido llevar cabo la operación debido al siguiente error: <br />'+arrResp[0]);
        }
    }
    obtenerDatosWeb('../paginasFunciones/funcionesFormulario.php',funcResp, 'POST','funcion=16&condComp='+condComp+'&valDep='+cadObj+'&idReferencia='+gE('idReferencia').value+'&vCondicion='+valorCondicion+'&condicion='+condicion+'&cDestino='+cDestino+'&idFormulario='+idFormulario,true);

}

function actualizarListado(combo,cFiltro,condicion,cDestino,tControl)
{
	
	var tipoControl=tControl;
	var valorCondicion;
    
    if(typeof(combo)!='string')
	    valorCondicion=combo.options[combo.selectedIndex].value;
    else
    	valorCondicion=combo;
	var idFormulario=gE('idFormulario').value;
	var nomNCol;
	
	var nColumnas;
	var anchoCol;
	var tblDestino;
	var nomDestino;
	if((tipoControl>=14)&&(tipoControl<=16))
	{
		nomDestino='_'+cDestino+'vch';
		nomNCol='nColumnas_'+cDestino+'vch';
		nColumnas=gE(nomNCol).value;
		anchoCol=gE('ancho_'+cDestino+'vch').value;
		tblDestino=gE('tbl_'+cDestino+'vch');
		gE(nomDestino).value='-1';
	}
	else
	{
		if((tipoControl>=17)&&(tipoControl<=19))
		{
			nomDestino='_'+cDestino+'arr';
			nomNCol='nColumnas_'+cDestino+'arr';

			nColumnas=gE(nomNCol).value;
			anchoCol=gE('ancho_'+cDestino+'arr').value;
			tblDestino=gE('tbl_'+cDestino+'arr');
		}
	}
	function funcResp()
    {
        arrResp=peticion_http.responseText.split('|');
        if(arrResp[0]=='1')
        {
        	var arrOpciones=eval(arrResp[1]);
            var numOpt=arrOpciones.length;
            var x;
            var opt;
            var ct=0;
			
			var padre=tblDestino.parentNode;
			padre.removeChild(tblDestino);
			
			var tablaCtrl=crearTabla(nColumnas,arrOpciones,tipoControl,nomDestino,anchoCol);
			padre.appendChild(tablaCtrl);
        }
        else
        {
            msgBox('No se ha podido llevar cabo la operación debido al siguiente error: <br />'+arrResp[0]);
        }
    }
    obtenerDatosWeb('../paginasFunciones/funcionesFormulario.php',funcResp, 'POST','funcion=16&vCondicion='+valorCondicion+'&condicion='+condicion+'&cDestino='+cDestino+'&idFormulario='+idFormulario,true);
}

function crearComboGeneral(id,arrData,msgInicial)
{

	var arrDatos=[];
    if((arrData !=undefined)&&(arrData!=null))
    	arrDatos=arrData;
	var idCombo='idComboGral';
    if(id!=undefined)
    	idCombo=id;
	
	var dsDatos= new Ext.data.SimpleStore	(
													{
														fields:	[
																	{name:'id'},
																	{name:'tipo'},
                                                                    {name:'comp1'},
                                                                    {name:'comp2'},
                                                                    {name:'comp3'}
																]
													}
												);
	dsDatos.loadData(arrDatos);
	var comboEtapas=document.createElement('select');
	var cmbGeneral=new Ext.form.ComboBox	(
													{
														id:idCombo,
														mode:'local',
														emptyText:msgInicial,
														store:dsDatos,
														displayField:'tipo',
														valueField:'id',
														transform:comboEtapas,
														editable:false,
														typeAhead: true,
														triggerAction: 'all',
														lazyRender:true,
                                                        width:120,
														listWidth:270
													}
												)
	return cmbGeneral;	
}

function llenarCombo(combo,arrValores,seleccione)
{
	var x;
	var opcion;
	limpiarCombo(combo);
    
	if((seleccione!=undefined)&&(combo.getAttribute('multiple')==null))
	{
		option=document.createElement('option');
		combo.options[0]=option;
		option.text="Seleccione";
		option.value="-1";
	}
	for(x=0;x<arrValores.length;x++)
	{
		opcion=document.createElement('option');
		opcion.value=arrValores[x][0];
		opcion.text=arrValores[x][1];
		combo.options[combo.length]=opcion;
	}
}

function reemplazarEtiqueta(idLabel,etiqueta,clase)
{
	var lblControl=gE(idLabel);
	var pControl=lblControl.parentNode;
	pControl.removeChild(lblControl);
	lblControl=document.createElement('label');
	lblControl.id=idLabel;
    if(clase!=undefined)
    	setClase(lblControl,clase);
	var txtEtiqueta=document.createTextNode(etiqueta);
	lblControl.appendChild(txtEtiqueta);
	pControl.appendChild(lblControl);
}



function enviarFormularioDatos(paginaDestino,arrParametros,metodo,objetivo)
{
	enviarFormularioDatosV(paginaDestino,arrParametros,metodo,objetivo);
    
	
}

function enviarFormularioDatosV(paginaDestino,arrParametros,metodo,objetivo) //Para validar que los parametros de envio no se repitan
{
	var frm=gE('frmEnvioDatos');

    var arrElementos=frm.childNodes;
    var arrElementosMantener=['confReferencia','cPagina'];
	var valorMetodo='POST';
	if(metodo!=undefined)
		valorMetodo=metodo;
	frm.action=paginaDestino;
	frm.method=valorMetodo;
   
	if(objetivo!=undefined)
		frm.target=objetivo;
	else
		frm.target='';
        
       

        
	var x;
    var ct;
	var nomParametro;
	var valorParametro;
	var hElemento;

	for(x=0;x<arrParametros.length;x++)
	{
	    var nCtrl=null;
        nomParametro=arrParametros[x][0];
		valorParametro=arrParametros[x][1];
		for(ct=0;ct<arrElementos.length;ct++)
        {
        	if(arrElementos[ct].id==nomParametro)
            {
            	nCtrl=arrElementos[ct];
 				arrElementosMantener.push(arrElementos[ct].name);
                break;
            }
        }
		
		if(nCtrl==null)
		{
			hElemento=document.createElement('input');
			hElemento.type='hidden';
			hElemento.name=nomParametro;
			hElemento.id=nomParametro;
			arrElementosMantener.push(nomParametro);
			hElemento.value=valorParametro;
			frm.appendChild(hElemento);
            
		}
		else
		{
        	
			nCtrl.value=valorParametro;
            
		}
		
	}
    
    for(ct=0;ct<arrElementos.length;ct++)
	{
    	if(arrElementos[ct].name)
        {
        	if(existeValorArreglo(arrElementosMantener,arrElementos[ct].name)==-1)
            {
	            
            	arrElementos[ct].parentNode.removeChild(arrElementos[ct]);
                ct--;
            }
        }
    }
   	
    frm.submit();
	
}

function rellenarCombo(combo,arreglo,seleccione)
{
	var x;
	var opcion;
	limpiarCombo(combo);
	var ct=0;
	
	if(seleccione!=undefined)
	{
		opcion=document.createElement('option');
		opcion.value='-1';
		opcion.text='Seleccione';
		combo.options[ct]=opcion;
		ct=1;
	}
	for(x=0;x<arreglo.length;x++)
	{
		opcion=document.createElement('option');
		opcion.value=arreglo[x][0];
		opcion.text=arreglo[x][1];
		combo.options[ct]=opcion;
		ct++;
	}
}

var regCombo = Ext.data.Record.create	
(
	[
		{name: 'id'},
		{name: 'nombre'},
		{name: 'valorComp'}
	]
);

function crearComboExt(id,arregloValores,posX,posY,tamano,objConf)
{
	
	posx=0;
	if(posX!=undefined)
		posx=posX;
	posy=0;
	if(posY!=undefined)
		posy=posY;
	
    var confVista='<tpl for="."><div class="search-item">{nombre}</div></tpl>';
    if(objConf!=undefined)
    {
        if(objConf.confVista!=undefined)
            confVista=objConf.confVista;
    }
    var resultTpl=new Ext.XTemplate	(confVista);
    
    var arrCampos=	[
                          {name:'id'},
                          {name:'nombre'},
                          {name:'valorComp'},
                          {name:'valorComp2'},
                          {name:'valorComp3'},
                          {name:'valorComp4'}
                          
                      ]
	
    if(objConf!=undefined)
    {
    	
    	if(objConf.arrCampos!=undefined)
        {
        	arrCampos=objConf.arrCampos;
        }
    }                      
	var almacen=new Ext.data.SimpleStore	(
											 	{
													fields:	arrCampos
												}
											)
	var comboTmp=document.createElement('select');
	var campoValor='id';
    if((objConf)&&(objConf.campoValor))
    	campoValor=objConf.campoValor;
        
        
	var campoEtiqueta='nombre';  
    if((objConf)&&(objConf.campoEtiqueta))
    	campoEtiqueta=objConf.campoEtiqueta;      
	var objConfCombo=	{
    						id:id,
                            x:posx,
                            y:posy,
                            mode:'local',
                            emptyText:'Elija una opci\u00f3n',
                            store:almacen,
                            displayField:campoEtiqueta,
                            valueField:campoValor,
                            forceSelection: true,
                            lazyRender:true,
                            itemSelector:'div.search-item',
                            listener:	{
                            				focus:function (e,html)
                                            {
                                            	try {
                                                        htmlElement.blur();
                                                    } 
                                                    catch (ex) 
                                                    {
                                                    }

                                            }
                            			}
    					};
    
    
    
    if((objConf!=undefined)&&(objConf.renderTo))
    	objConfCombo.renderTo=objConf.renderTo;

    if((objConf==undefined)||(objConf.transform==undefined)||(Ext.isIE))
    {
    	objConfCombo.transform=comboTmp;

    }
	
    if((tamano!=undefined)&&(tamano!=0))
	{
    	var tamanoList=tamano;
        if(objConf!=undefined)
        {
            if(objConf.anchoList!=undefined)
                tamanoList=objConf.anchoList;
        }
    	objConfCombo.width=tamano;
        objConfCombo.listWidth=tamanoList
	}
    
    var combo;
    
	
    if((objConf)&&(objConf.multiSelect))
    {
    	
        
    	if(objConf.funcionCheckBoxCheck)
        	objConfCombo.funcionCheckBoxCheck=objConf.funcionCheckBoxCheck;

    	combo =new Ext.ux.form.CheckboxCombo	(
                                                        
                                                 	objConfCombo
                                                )
    }
    else
    {
    	objConfCombo.tpl=resultTpl;
        objConfCombo.editable=false;
        objConfCombo.typeAhead= false;
        
        if((objConf!=undefined)&&(objConf.typeAhead))
        {
	    	objConfCombo.typeAhead=objConf.typeAhead;
            if(objConfCombo.typeAhead)
            	objConfCombo.editable=true;
        }
        
        objConfCombo.triggerAction= 'all';
        
        if((objConf!=undefined)&&(objConf.otrasConfiguraciones))
        {
	        for (key in objConf.otrasConfiguraciones) 
            {
            	objConfCombo[key]=objConf.otrasConfiguraciones[key];
            }
        }
        
        combo =new Ext.form.ComboBox	(
                                                objConfCombo
                                            )
	}    
	almacen.loadData(arregloValores);
	if((objConf)&&(objConf.valor))
    {
    	combo.setValue(objConf.valor);
    }
	return combo;
}

function crearComboExtAutocompletar(pConf)
{
	var idCombo='idCombo';
	if(pConf.idCombo!=undefined)
		idCombo=pConf.idCombo;
	
	var txtDestino='';
	if(pConf.txtDestino!=undefined)
		txtDestino=pConf.txtDestino;
	
	var posX=0;
	if(pConf.posX!=undefined)
		posX=pConf.posX;
	
	var posY=0;
	if(pConf.posY!=undefined)
		posY=pConf.posY;
	
	var anchoCombo=300;
	if(pConf.anchoCombo!=undefined)
		anchoCombo=pConf.anchoCombo;
	
	var campoDesplegar='valor';
	if(pConf.campoDesplegar!=undefined)
		campoDesplegar=pConf.campoDesplegar;
	var campoID='id';
    if(pConf.campoID!=undefined)
		campoID=pConf.campoID;
	var funcAntesCarga=null;
	if(pConf.funcAntesCarga!=undefined)
		funcAntesCarga=pConf.funcAntesCarga;
	
	var funcElementoSel=null;
	if(pConf.funcElementoSel!=undefined)
		funcElementoSel=pConf.funcElementoSel;
	
	var campoHDestino='';
	if(pConf.campoHDestino!=undefined)
		campoHDestino=pConf.campoHDestino;
	
	var funcionBusqueda=1;
	if(pConf.funcionBusqueda!=undefined)
		funcionBusqueda=pConf.funcionBusqueda;
	
	var paginaProcesamiento='../paginasFunciones/procesarBusqueda.php';
	if(pConf.paginaProcesamiento!=undefined)
		paginaProcesamiento=pConf.paginaProcesamiento;
	
	var confVista='<tpl for="."><div class="search-item">{valorComp}</div></tpl>';
	if(pConf.confVista!=undefined)
		confVista=pConf.confVista;
		
	var campos=	[
					{name:'id'},
					{name:'valor'},
                    {name:'valorComp'}
				]	;	
	if(pConf.campos!=undefined)
		campos=pConf.campos;
	
	var valorCarga='';
	if(pConf.valorCarga!=undefined)
		valorCarga=pConf.valorCarga;
	var desHabilitado=false;
	if(pConf.desHabilitado!=undefined)
		desHabilitado=pConf.desHabilitado;
	
    var anchoLista=anchoCombo;
    if(pConf.anchoLista!=undefined)
    	anchoLista=pConf.anchoLista;
    
	var pPagina=new Ext.data.HttpProxy	(
										 	{
												url:paginaProcesamiento,
												method:'POST'
											}
										 );
	
    var nNodo='objetos';
    if(pConf.raiz!=undefined)
    	nNodo=pConf.raiz;
    var nPropiedades='num';
    if(pConf.nRegistros!=undefined)
    	nPropiedades=pConf.nRegistros;
	var lector=new Ext.data.JsonReader 	(
										 	{
												root:nNodo,
												totalProperty:nPropiedades,
												idProperty:campoID
											},
											campos
											
										);
	var parametros=	{
						funcion:funcionBusqueda,
						criterio:''
					};
	
	var ds=new Ext.data.Store	(
								 	{
										proxy:pPagina,
										reader:lector,
										baseParams:parametros
									}
								 );
	
	function cargarDatos(dSet)
	{
    	if(typeof(funcionAntesCargaInyeccion)!='undefined')
        {
        	funcionAntesCargaInyeccion(idCombo);
        }
		if(funcAntesCarga==null)
		{
        	var hDestino=gE(campoHDestino);
        	if(hDestino!=null)
            	hDestino.value=-1;
			var aValor=Ext.getCmp(pConf.idCombo).getRawValue();
            
			dSet.baseParams.criterio=aValor;
		}
		else
			funcAntesCarga(dSet,Ext.getCmp(pConf.idCombo));
	}
	
	ds.on('beforeload',cargarDatos);
	
	var resultTpl=new Ext.XTemplate	(confVista);
	var comboBusqueda;
    var objConf;
	if(txtDestino=='')
	{
        objConf=	{
                        id:idCombo,
                        store:ds,
                        x:posX,
                        y:posY,
                        displayField:campoDesplegar,
                        valueField:campoID,
                        typeAhead:false,
                        minChars:1,
                        loadingText:'Procesando, por favor espere...',
                        width:anchoCombo,
                        hideTrigger:true,
                        tpl:resultTpl,
                        itemSelector:'div.search-item',
                        listWidth :anchoLista,
                        disabled:desHabilitado
                    }									

	}
	else
	{
		 objConf=	{
                        id:idCombo,
                        store:ds,
                        x:posX,
                        y:posY,
                        displayField:campoDesplegar,
                        valueField:campoID,
                        typeAhead:false,
                        minChars:1,
                        loadingText:'Procesando, por favor espere...',
                        width:anchoCombo,
                        hideTrigger:true,
                        tpl:resultTpl,
                        applyTo:txtDestino,
                        itemSelector:'div.search-item',
                        listWidth :anchoLista,
                        disabled:false
                    }
     
	}
    if(pConf.renderTo!=undefined)
    	objConf.renderTo=pConf.renderTo;
	comboBusqueda= new Ext.form.ComboBox(objConf);
	
	function funcElemSeleccionado(combo,registro)
	{	
    	
    	if(typeof(funcionSeleccionInyeccion)!='undefined')
        {
        	funcionSeleccionInyeccion(combo,registro);
        }
		if(funcElementoSel!=null)
		{
        	
			var cHDestino=gE(campoHDestino);
			if(cHDestino!=null)
				cHDestino.value=registro.get(campoID);
			funcElementoSel(combo,registro);
		}
		else
		{
			var cHDestino=gE(campoHDestino);

			if(cHDestino!=null)
				cHDestino.value=registro.get(campoID);
		}
        var cadena=Ext.util.Format.stripTags(registro.get(campoDesplegar));
        
         combo.setRawValue(cadena);
         combo.setValue(cadena);
	}
	comboBusqueda.on('select',funcElemSeleccionado);
	comboBusqueda.setValue(valorCarga);
	return comboBusqueda;
}

function generarNumeracion(inicio,final)
{
	var x;
    var numeracion=new Array();
    var ct=0;
    for(x=inicio;x<=final;x++)
    {
    	numeracion[ct]=[x,x];
        ct++;
    }
    return numeracion;
}

function obtenerOpcionSelect(combo,valor)
{
	var x;
	for(x=0;x<combo.options.length;x++)
	{
		if(combo.options[x].value==valor)
			return combo.options[x];
	}
	return null;
}

function cambiaraFechaMysql(fecha)
{
	var arrFecha=fecha.split('/');
	return arrFecha[2]+'-'+arrFecha[1]+"-"+arrFecha[0];
}

function formatearNumero(numero, decimales, separador_decimal, separador_miles,truncar)
{ 
    numero=parseFloat(numero);
    if(isNaN(numero))
	{
        return "NaN";
    }

	if((numero+'').indexOf('.')==-1)
    	numero=numero+'.00';

    if(decimales!==undefined)
	{
        // Redondeamos
		if((truncar==undefined)||(truncar==false))
        {
	        numero=parseFloat(numero).toFixed(decimales);
        }
		else
			numero=trucarValor(numero,decimales);
    }

    
	
	var valorNum=(numero+'').split('.');
	
	var valorEntero=valorNum[0];
    if(separador_miles)
	{
        // Añadimos los separadores de miles
        var miles=new RegExp("(-?[0-9]+)([0-9]{3})");
        while(miles.test(valorEntero)) 
		{
            valorEntero=valorEntero.replace(miles, "$1" + separador_miles + "$2");
        }
    }
	else
		valorEntero=valorNum[0];
	if(valorNum[1]==undefined)
		    return valorEntero;
    return valorEntero+(separador_decimal)+valorNum[1];
}

function trucarValor(numero,decimales)
{
	var valorBase=Math.pow(10,decimales);
	var nuevoNum=(numero*valorBase)+'';
	var arrNum=nuevoNum.split('.');
	var valor=parseFloat(arrNum[0]);
	return valor/valorBase;
	
	
}

function existeValor(combo,valor)
{
	var x;
	for(x=0;x<combo.options.length;x++)
	{
		if(combo[x].value==valor)
			return x;
		
	}
	return -1;
}

function existeValorMatriz(matriz,valor,posicion,comparaNumerico)
{
	var x;
	if(posicion==undefined)
		pos=0;
	else
		pos=posicion;
		
	
	for(x=0;x<matriz.length;x++)
	{
		if(!comparaNumerico)
        {
            if(matriz[x][pos]==valor)
                return x;
		}
        else
        {
        	 if(parseFloat(matriz[x][pos])==parseFloat(valor))
                return x;
        }		
	}
	return -1;
}

function existeValorArreglo(arreglo,valor)

{
	var x;
	
	for(x=0;x<arreglo.length;x++)
	{
		if((arreglo[x]+'')==valor)
			return x;
	}
	return -1;
}

function recoletarValoresCombo(idElemento)
{
	var x;
	var cadValores='';
	var combo;
	if(typeof(idElemento)=='object')
		combo=idElemento;
	else
		combo=gE(idElemento);
	
	for(x=0;x<combo.options.length;x++)
	{
		if(cadValores=='')
			cadValores=combo[x].value;
		else
			cadValores+=','+combo[x].value;
	}
	return cadValores;
}

function recoletarValoresGrid(grid,campo,separador)
{
	var x;
	var valores='';
	var sep='';
	if(separador != undefined)
		sep=separador;
	for(x=0;x<grid.getStore().getCount();x++)
	{
		if(valores=='')
			valores=sep+grid.getStore().getAt(x).get(campo)+sep;
		else
			valores+=','+sep+grid.getStore().getAt(x).get(campo)+sep;
	}
	return valores;
}

function enviarFormulario(idFormulario)
{
	var arrParam=[['idFormulario',idFormulario],['cPagina','']];
	enviarFormularioDatos('../modeloPerfiles/tblFormularios.php',arrParam);
}

function enviarFormularioAdmon(idFormulario)
{
	var arrParam=[['idFormulario',idFormulario]];
	enviarFormularioDatos('../modeloCitas/administrarHorarioUnidadApartado.php',arrParam);
}

function _GET( name )
{
	var regexS = "[\\?&]"+name+"=([^&#]*)";
	var regex = new RegExp ( regexS );
	var tmpURL = window.location.href;
	var results = regex.exec( tmpURL );
	if( results == null )
		return"";
	else
		return results[1];
}

function var_dump(obj) 
{
   if(typeof obj == "object") 
   {
      return "Type: "+typeof(obj)+((obj.constructor) ? "\nConstructor: "+obj.constructor : "")+"\nValue: " + obj;
   } 
   else 
   {
      return "Type: "+typeof(obj)+"\nValue: "+obj;
   }
}

function setClase(idElemento,clase)
{
	var control;
	if(typeof(idElemento)=='object')
		control=idElemento;
	else
		control=gE(idElemento);

	if(Ext.isIE)
		control.className=clase;
	else
		control.setAttribute('class',clase);
}

function obtenerClase(idElemento)
{
	var control;
	if(typeof(idElemento)=='object')
		control=idElemento;
	else
		control=gE(idElemento);

	if(Ext.isIE)
		return control.className;
	else
		return control.getAttribute('class');
}

function generarNumeroAleatorio(inferior,superior)
{ 
   	numPosibilidades = superior - inferior 
   	aleat = Math.random() * numPosibilidades 
   	aleat = Math.round(aleat) 
   	return parseInt(inferior) + aleat 
} 

function abrirVentana(url) 
{
	//var windowprops ="top=0,left=0,toolbar=no,location=no,status=no, menubar=no,scrollbars=no, resizable=no,width=" + w + ",height=" + h;
	window.open(url,"", "toolbar=no,directories=no,menubar=no,status=no,scrollbars=yes,fullscreen=yes");
} 

function verUsr(iU)
{
	var arrDatos=[['idUsuario',iU]];
	enviarFormularioDatos('../Usuarios/vista.php',arrDatos);
}

function verUsrNuevaPagina(iU)
{
	var arrDatos=[['idUsuario',iU],['cPagina','mR1=false']];
	window.open('',"vAuxiliar2", "toolbar=no,directories=no,menubar=no,status=no,scrollbars=yes,fullscreen=yes");
    enviarFormularioDatos('../Usuarios/vista.php',arrDatos,'POST','vAuxiliar2');
}

function obtenerTamanioArchivo(file)
{
	
	if(Ext.isIE)
	{
		var oas = new ActiveXObject("Scripting.FileSystemObject");
		var e = file.getFile(d);
		var f = e.size;
		return f;
	}
	else //Firefox-Chrome
		return file.files[0].fileSize;
}

function rellenarCadena(cad,tamano,caracter,direccion) //direccion =-1 izquierda; 1 derecha
{
	var cadAux;
	var x;
	cadAux=cad+'';
	for(x=cadAux.length;x<tamano;x++)
	{
		if(direccion==-1)
			cadAux=caracter+cadAux;
		else
			cadAux=cadAux+caracter;
	}
	return cadAux;
}

function normalizarValor(valor)
{
	var x;
	var cadAux='';
    valor=valor+'';
	for(x=0;x<valor.length;x++)
	{
		if(((valor.substr(x,1)>='0')&&(valor.substr(x,1)<='9'))||(valor.substr(x,1)=='.')||(valor.substr(x,1)=='-'))
			cadAux+=valor.substr(x,1);
	}
	return cadAux;
}

function cambiaraFormatoMysqlToEstandar(fecha)
{
	if(typeof(fecha)=='string')
	{
		var arrFecha=fecha.split('-');
		return arrFecha[2]+'/'+arrFecha[1]+"/"+arrFecha[0];
	}
	else
		return fecha.format('d/m/Y');
}

function bE(valor)
{
	return Base64.encode(valor)
}

function bD(valor)
{
	return Base64.decode(valor)
}

function crearRegistro(campos)
{
	return Ext.data.Record.create	( campos);
}

function obtenerValorSelect(combo)
{
	var cmb;
	if(typeof(combo)=='object')
		cmb=combo;
	else
		cmb=gE(combo);
	return cmb.options[cmb.selectedIndex].value;
}


function obtenerListadoArregloFilas(arrFilas,columna,separador)
{
	var x;
	var listado='';
    var sep='';
    if(separador!=undefined)
    	sep=separador;
	for(x=0;x<arrFilas.length;x++)
	{
		if(listado=='')
			listado=sep+arrFilas[x].get(columna)+sep;
		else
			listado+=','+sep+arrFilas[x].get(columna)+sep;
	}
	return listado;
}

function oEx(idControl)
{
	Ext.getCmp(idControl).hide();
}

function mEx(idControl)
{
	Ext.getCmp(idControl).show();
}

function hEx(idControl)
{
	Ext.getCmp(idControl).enable();
}

function dEx(idControl)
{
	Ext.getCmp(idControl).disable();
}

function gEx(idControl)
{
	return Ext.getCmp(idControl);
}

function letraCapitalFrase(string)
{
	var arrayWords;
 	var returnString = "";
 	var len;
 	arrayWords = string.split(" ");
 	len = arrayWords.length;
 	for(i=0;i < len ;i++)
	{
  		if(i != (len-1))
		{
   			returnString = returnString+ucFirst(arrayWords[i])+" ";
  		}
  		else
		{
   			returnString = returnString+ucFirst(arrayWords[i]);
  		}
 	}
 	return returnString;
}

function letraCapitalPalabra(string)
{
	return string.substr(0,1).toUpperCase()+string.substr(1,string.length).toLowerCase();
}

function obtenerPosX(idObj)
{

  var curleft = 0;
  var obj=gE(idObj);
  if(obj.offsetParent)
      while(1)
      {
        curleft += obj.offsetLeft;
        if(!obj.offsetParent)
          break;
        obj = obj.offsetParent;
      }
  else if(obj.x)
      curleft += obj.x;
  return curleft;
}

function obtenerPosY(idObj)
{
  var curtop = 0;
  var obj=gE(idObj);
  if(obj.offsetParent)
      while(1)
      {
        curtop += obj.offsetTop;
        if(!obj.offsetParent)
          break;
        obj = obj.offsetParent;
      }
  else if(obj.y)
      curtop += obj.y;
  return curtop;
}

function calibrarPosicion(id)
{
	var elMovimiento;
	if(typeof(id)=='object')
		elMovimiento=id;	
	else
		elMovimiento=gE(id);
	try
	{
	var idPadre=elMovimiento.getAttribute('idPadre');
	}
	catch(ex)
	{
		alert(id);
	}
	if((idPadre==null)||(idPadre=='-1'))
	{
		elMovimiento.style.left=(parseInt(elMovimiento.style.left)+posDivX)+'px';
		elMovimiento.style.top=(parseInt(elMovimiento.style.top)+posDivY)+'px';
    }
	else
	{
    
    
		var divPadre=gE('div_'+idPadre);
		
		var posDivPadreX=parseInt(divPadre.style.left);
		var posDivPadreY=parseInt(divPadre.style.top);
		elMovimiento.style.left=(parseInt(elMovimiento.style.left)+posDivPadreX)+'px';
		elMovimiento.style.top=(parseInt(elMovimiento.style.top)+posDivPadreY)+'px';
	}
	
}

function DEBUG()
{ 
    this.a=''; 
    this.vardump=function(o)
	{ 
        if(o.constructor==Array) 
               this.a+='['; 
        if(o.constructor==Object) 
            this.a+='{'; 
        for(var i in o)
		{ 
            if(o.constructor!=Array) 
                this.a+=i+':'; 
            if(o[i].constructor==Object)
			{ 
            	this.vardump(o[i]); 
            }
			else 
				if(o[i].constructor==Array)
				{ 
                	this.vardump(o[i]); 
            	}
				else 
					if(o[i].constructor==String)
					{ 
						this.a+='"'+o[i]+'",'; 
					}
					else
					{ 
						this.a+=o[i]+','; 
					} 
        } 
        if(o.constructor==Object) 
            this.a+='},'; 
        if(o.constructor==Array) 
            this.a+='],'; 
        return this.a.substr(0,this.a.length-1).split(',}').join('}').split(',]').join(']');
    } 
} 


var debug=new DEBUG();


function obtenerPosXMouse(event)
{
	var xActual;
	if((Ext.isOpera)||(Ext.isIE))
		xActual=window.event.clientX+document.documentElement.scrollLeft+document.body.scrollLeft;
	else
		xActual=event.clientX+window.scrollX;
	return xActual;
}

function obtenerPosYMouse(event)
{
	var yActual;
	if((Ext.isOpera)||(Ext.isIE))
		 yActual=window.event.clientY+document.documentElement.scrollTop+document.body.scrollTop;
	else
		yActual=event.clientY+window.scrollY;
	return yActual;
}

function generarObjetoUsuarioMail(listUsuario)
{
	cadUsuario="";
	arrUsuarios=listUsuario.split(',');
	var x;
	var obj;
	for(x=0;x<arrUsuarios.length;x++)
	{
		obj='{"tipo":"0","idUsuario":"'+arrUsuarios[x]+'","ciclo":"-1","idPrograma":"-1","idGrado":"-1","idMateria":"-1","idGrupo":"-1"}';
		if(cadUsuario=="")
			cadUsuario=obj;
		else
			cadUsuario+=","+obj;
	}	
	return bE('{"destinatario":['+cadUsuario+']}');
}

function obtenerHijosNodoArbol(nodo)
{
	if((typeof(nodo.childNodes)!='undefined')&&(nodo.childNodes.length>0))
		return nodo.childNodes;
	if((typeof(nodo.children)!='undefined')&&(nodo.children.length>0))	
		return nodo.children;
	if(nodo.attributes!=undefined)
	{
		if((typeof(nodo.attributes.children)!='undefined')&&(nodo.attributes.children.length>0))		
			return nodo.attributes.children;
	}
	return [];
}

function ucWords(string)
{
	var arrayWords;
 	var returnString = "";
 	var len;
 	arrayWords = string.split(" ");
 	len = arrayWords.length;
 	for(i=0;i < len ;i++)
	{
  		if(i != (len-1))
		{
		   returnString = returnString+ucFirst(arrayWords[i])+" ";
		}
  		else
		{
   			returnString = returnString+ucFirst(arrayWords[i]);
  		}
 	}
 	return returnString;
}

function ucFirst(string)
{
	return string.substr(0,1).toUpperCase()+string.substr(1,string.length);
}

function abrirVentanaPOST(URL,arrDatos,nVentana)
{
	var nombreVentana='vAuxiliar';
    if(nVentana)
    	nombreVentana=nVentana;
	window.open('',nombreVentana, "toolbar=no,directories=no,menubar=no,status=no,scrollbars=yes,fullscreen=yes");
    enviarFormularioDatos(URL,arrDatos,'POST',nombreVentana);
}

function ordenarCombo(cmb,orden) //1= por valor asc,-1 por valor desc;2 por titulo asc;-2 por titulo desc
{
	if(typeof(cmb)=='object')
		cmbDestino=cmb;
	else
		cmbDestino=gE(cmb);
	var x;
	var arrDestino=new Array();
	var objArray;
	for(x=0;x<cmbDestino.length;x++)
	{
		objArray=new Array();
		objArray[0]=cmbDestino.options[x].value;
		objArray[1]=cmbDestino.options[x].text;
		arrDestino.push(objArray);
	}
	
	switch(orden)
	{
		case -2:
			arrDestino.sort(ordenaTituloDesc);
		break;
		case -1:
			arrDestino.sort(ordenaValorDesc);
		break;
		case 1:
			arrDestino.sort(ordenaValorAsc);
		break;
		case 2:
			arrDestino.sort(ordenaTituloAsc);
		break;
	}
	limpiarCombo(cmbDestino);
	llenarCombo(cmbDestino,arrDestino);
}

function ordenaValorAsc(v1,v2)
{
	var filter=/^\d+(\.\d+)?$/;
	var filter2=/^(?:\+|-)?\d+$/;
	
	if((filter.test(v1[0]))||(filter2.test(v1[0]))) //Es flotante o entero
	{
		var val1=parseFloat(v1[0]);
		var val2=parseFloat(v2[0]);
		return val1-val2;
	}
	else
	{
		if(v1[0].toLowerCase()<v2[0].toLowerCase())
			return -1;
		else
			return 1;
	}
}

function ordenaValorDesc(v1,v2)
{
	var filter=/^\d+(\.\d+)?$/;
	var filter2=/^(?:\+|-)?\d+$/;
	if((filter.test(v1[0]))||(filter2.test(v1[0]))) //Es flotante o entero
	{
		var val1=parseFloat(v1[0]);
		var val2=parseFloat(v2[0]);
		
		return val2-val1;
		
	}
	else
	{
		if(v1[0].toLowerCase()<v2[0].toLowerCase())
			return 1;
		else
			return -1;
	}
}

function ordenaTituloAsc(v1,v2)
{
	var filter=/^\d+(\.\d+)?$/;
	var filter2=/^(?:\+|-)?\d+$/;
	if((filter.test(v1[1]))||(filter2.test(v1[1]))) //Es flotante o entero
	{
		var val1=parseFloat(v1[1]);
		var val2=parseFloat(v2[1]);
		
		return val1-val2;
		
	}
	else
	{
		if(v1[1].toLowerCase()<v2[1].toLowerCase())
			return -1;
		else
			return 1;
	}
}

function ordenaTituloDesc(v1,v2)
{
	var filter=/^\d+(\.\d+)?$/;
	var filter2=/^(?:\+|-)?\d+$/;
	if((filter.test(v1[1]))||(filter2.test(v1[1]))) //Es flotante o entero
	{
		var val1=parseFloat(v1[1]);
		var val2=parseFloat(v2[1]);
		
		return val2-val1;
		
	}
	else
	{
		if(v1[1].toLowerCase()<v2[1].toLowerCase())
			return 1;
		else
			return -1;
	}
}

function eliminarValorCombo(combo,valor)
{
	var posElem=existeValor(combo,valor);		
	if(posElem!=-1)
	{
		combo.remove(posElem);
	}
}

function crearCampoGridFormulario(idCampoGrid,spDestino,ancho,alto,arrCampos,arrColumnas,permisos,habilitado,mGrid,objConf)
{

	var Visible=true;
	if(mGrid!=undefined)
		Visible=mGrid;
	var arrBotones=new Array();
	if(!objConf)
    	objConf={"etRemover":"Remover","etAgregar":"Agregar"};
	eOculto=true;
	arrBotones.push(		{
								id:'btnAdd_'+idCampoGrid,
								text:objConf.etAgregar,
								icon:'../images/add.png',
								cls:'x-btn-text-icon',
								handler:function()
										{}
							}
						);	
	
	
	
						
	if(permisos.indexOf('E')!=-1)
	{
		eOculto=false;
	}
	if(arrBotones.length>0)
    {
    	arrBotones.push('-');
    }
	arrBotones.push(	{
								id:'btnDel_'+idCampoGrid,
								text:objConf.etRemover,
								icon:'../images/delete.png',
								cls:'x-btn-text-icon',
								hidden:eOculto,
								handler:function()
										{}
							}
						);	
	
	
	var dsDatos=[];
    var alDatos=	new Ext.data.SimpleStore	(
													{
														fields:	arrCampos
													}
												);

    alDatos.loadData(dsDatos);
	var chkRow=new Ext.grid.CheckboxSelectionModel();
	var arrColumnasCompleto=new Array();
	arrColumnasCompleto.push(new  Ext.grid.RowNumberer());
	arrColumnasCompleto.push(chkRow);
	var x;
	for(x=0;x<arrColumnas.length;x++)
	{
		arrColumnasCompleto.push(arrColumnas[x]);
	}
	
	var cModelo= new Ext.grid.ColumnModel   	(
												 	arrColumnasCompleto
												);
                                                
	var tblGrid=	new Ext.grid.EditorGridPanel	(
                                                        {
                                                            id:idCampoGrid,
                                                            store:alDatos,
                                                            frame:false,
                                                            border:true,
                                                            renderTo:spDestino,
                                                            cm: cModelo,
                                                            height:alto,
                                                            width:ancho,
                                                            sm:chkRow,
															disabled:!habilitado,
															tbar:arrBotones,
															visible:Visible
                                                        }
                                                    );
	return tblGrid;													
}

function crearCampoGridFormularioEjecucion(idCtrl,idCampoGrid,spDestino,ancho,alto,arrCampos,arrColumnas,permisos,habilitado,mGrid,arrDatosGrid,objConf)
{


	var Visible=true;
	if(mGrid!=undefined)
		Visible=mGrid;
        
	var arrBotones=new Array();
	
	eOculto=true;
	if(permisos.indexOf('A')!=-1)
	{
    	var btnAgregar={
                            id:'btnAdd_'+idCampoGrid,
                            text:objConf.etAgregar,
                            icon:'../images/add.png',
                            cls:'x-btn-text-icon'
                        }
        
        if((objConf!=undefined)&&(objConf.funcionAgregar!=undefined))
        {
        	btnAgregar.handler=objConf.funcionAgregar;
        }
        else
        {
        	btnAgregar.handler=function()
                              {
                                  
                                  var tblGrid=gEx(idCampoGrid);
                                  
                                  var editorFila=gEx('editor_'+idCtrl);
                                  var registroGrid=crearRegistro(arrCampos);
                                  var cadObjGrid='';
                                  var x;
                                  for(x=0;x<arrCampos.length;x++)
                                  {
                                      if(cadObjGrid=='')
                                          cadObjGrid='"'+arrCampos[x].name+'":"-1"';
                                      else
                                          cadObjGrid+=',"'+arrCampos[x].name+'":""';
                                      
                                      
                                  }

                                  cadObjGrid='[{'+cadObjGrid+'}]';
                                  var objGrid=eval(cadObjGrid)[0];
                                  var nReg=new registroGrid	(
                                                                  objGrid
                                                              )
                                  
                                  editorFila.stopEditing();
                                  tblGrid.getStore().add(nReg);
                                  tblGrid.nuevoRegistro=true;
                                  editorFila.startEditing(tblGrid.getStore().getCount()-1);	
                                  Ext.getCmp('btnAdd_'+idCampoGrid).disable();
                                  Ext.getCmp('btnDel_'+idCampoGrid).disable();																	
                              }
        }
        
		arrBotones.push(btnAgregar);	
	}
	
	
						
	if(permisos.indexOf('E')!=-1)
	{
		eOculto=false;
	}
	if(arrBotones.length>0)
    {
    	arrBotones.push('-');
    }
	arrBotones.push(	{
								id:'btnDel_'+idCampoGrid,
								text:objConf.etRemover,
								icon:'../images/delete.png',
								cls:'x-btn-text-icon',
								hidden:eOculto,
								handler:function()
										{
											var datosGrid=idCampoGrid.split('_');
											var tblGrid=gEx(idCampoGrid);
											var fila=tblGrid.getSelectionModel().getSelected();
											if(fila==null)
											{
												msgBox('Primero debe seleccionar el elemento a eliminar');
												return;	
											}
											
											function resp(btn)
											{
												if(btn=='yes')
												{
													tblGrid.getStore().remove(fila);	
												}
											}
											msgConfirm('Est&aacute; seguro de querer eliminar el elemento seleccionado?',resp);
										}
							}
						);	
	
	
    
    var alDatos;
    
    if((objConf.agrupado)&&(objConf.agrupado=='1'))
    {
    	var lector= new Ext.data.ArrayReader({
                                            
                                            
                                            fields: [
                                               			{name:'idPedido'},
		                                                {name: 'txtRazonSocial2'},
		                                                {name:'folioPedido'},
		                                                {name:'fechaRecepcion', type:'date'},
                                                        {name: 'diferencia', type:'int'},
                                                        {name: 'num_Factura'},
                                                        {name: 'fecha_entrada',type:'date'},
                                                        {name: 'Nombre'},
                                                        {name: 'observaciones'},
                                                        {name:'num_entrega'},
                                                        {name:'cond_pago'},
                                                        {name: 'txtRFC'}
                                            		]
                                            
                                        }
                                      );
    
    	alDatos=	new Ext.data.GroupingStore({
                                                    reader: lector,
                                                    sortInfo: {field: objConf.campoAgrupacion, direction: 'ASC'},
                                                    groupField: objConf.campoAgrupacion,
                                                    remoteGroup:false,
                                                    remoteSort: false,
                                                    autoLoad:false
                                                    
                                                }) 
    }
    else
    {
        alDatos=	new Ext.data.SimpleStore	(
                                                        {
                                                             fields:	arrCampos
                                                        }
                                                    );
	}
    alDatos.loadData(arrDatosGrid);
	var chkRow=new Ext.grid.CheckboxSelectionModel();
	var arrColumnasCompleto=new Array();
	arrColumnasCompleto.push(new  Ext.grid.RowNumberer());
	arrColumnasCompleto.push(chkRow);
	var x;
	for(x=0;x<arrColumnas.length;x++)
	{
		arrColumnasCompleto.push(arrColumnas[x]);
	}
	
	var cModelo= new Ext.grid.ColumnModel   	(
												 	arrColumnas
												);
	
	
	var editorFila=new Ext.ux.grid.RowEditor	(
    												{
														id:'editor_'+idCtrl,
                                                        saveText: 'Guardar',
                                                        cancelText:'Cancelar',
                                                        clicksToEdit:2
                                                    }
                                                );
	editorFila.on('afteredit',funcEditorFilaAfterEditCampoGrid)                                                
    editorFila.on('beforeedit',funcEditorFilaBeforeEditCampoGrid)
    editorFila.on('validateedit',funcEditorValidaCampoGrid);
    editorFila.on('canceledit',funcEditorCancelEditCampoGrid);
	var summary = new Ext.ux.grid.GridSummary();
	var tblGrid=	new Ext.grid.EditorGridPanel	(
                                                        {
                                                            id:idCampoGrid,
                                                            store:alDatos,
                                                            frame:false,
                                                            border:true,
                                                            stripeRows :true,
                                                            columnLines : true,
                                                            renderTo:spDestino,
                                                            cm: cModelo,
                                                            height:alto,
                                                            width:ancho,
                                                            sm:chkRow,
															disabled:!habilitado,
															tbar:arrBotones,
															visible:false,
															plugins:[editorFila,summary]
                                                        }
                                                    );
	tblGrid.nuevoRegistro=false;
	tblGrid.soloLectura=true;
	if(permisos.indexOf('M')!=-1)
		tblGrid.soloLectura=false;
	tblGrid.on('beforeedit',funcAntesEditCampoGrid)		
    if(!Visible)
		tblGrid.hide();
    else    	
    	tblGrid.show();

}

function funcEditorFilaAfterEditCampoGrid(rowEdit,obj,registro,nFila)
{
	var datosEditor=rowEdit.getId().split('_')	
	var idGrid=datosEditor[1];
    
    var arrFunciones=eval("arrFuncionesAfterEdit.func_"+idGrid);
   	if(arrFunciones.length>0)
    {
    	arrFunciones[0](obj,registro,0,idGrid);
    }

}

function funcAntesEditCampoGrid(e)
{
	if((e.grid.soloLectura)&&(!e.grid.nuevoRegistro))
		e.cancel=true;
}

function funcEditorFilaBeforeEditCampoGrid(rowEdit,fila)
{

	var datosEditor=rowEdit.getId().split('_')	
    
    	eval('if(typeof(beforeEdit_'+datosEditor[1]+')!=\'undefined\')beforeEdit_'+datosEditor[1]+'(rowEdit,fila);');
	var idGrid='grid_'+datosEditor[1];
	var grid=Ext.getCmp(idGrid);
    grid.copiaRegistro=grid.getStore().getAt(fila).copy();
    grid.registroEdit=grid.getStore().getAt(fila);
	if((grid.soloLectura)&&(!grid.nuevoRegistro))
		return false;
}

function funcEditorValidaCampoGrid(rowEdit,obj,registro,nFila)
{
	var datosEditor=rowEdit.getId().split('_')	
	var idGrid='grid_'+datosEditor[1];
	var grid=Ext.getCmp(idGrid);
	var cm=grid.getColumnModel();
	var nColumnas=cm.getColumnCount(false);
	var x;
	var editor;
	var dataIndex;
	var valor;
	for(x=0;x<nColumnas;x++)
	{
		if(cm.getColumnHeader(x).indexOf('*')!=-1)
		{
			dataIndex=cm.getDataIndex(x);
			valor=(eval('obj.'+dataIndex));
			if(valor=='')
			{
				function funcResp()
				{
					var ctrl=gEx('editor_'+dataIndex);
					ctrl.focus();
				}
				msgBox('La columna "'+cm.getColumnHeader(x).replace('*','')+'" no puede ser vac&iacute;a',funcResp);
				return false;
			}
		}	
	}
   
   	var c;
    var resValidacion=eval('arrFuncionesValidacionEdit.funcionValidacionGrid_'+datosEditor[1]+'(grid,obj)');
    if(resValidacion)
    {
    	for(x=0;x<nColumnas;x++)
		{
        	c=cm.config[x];
            if((c.editor)&&(c.editor.attValor)&&(c.editor.attValor!=''))
            {
            	dataIndex=cm.getDataIndex(x);
            	eval('obj.'+dataIndex+'=obj.'+dataIndex+'+\'|'+c.editor.attValor+'\';')
            }
        }
    
    
    	if(Ext.getCmp('btnDel_'+idGrid)!=null)
	        Ext.getCmp('btnDel_'+idGrid).enable();	
		if(Ext.getCmp('btnAdd_'+idGrid)!=null)            
	        Ext.getCmp('btnAdd_'+idGrid).enable();
        grid.nuevoRegistro=false;
		return true;
   }
   else
   {
   		return false;
  	}
	
}

function funcEditorCancelEditCampoGrid(rowEdit,cancelado)
{
	var datosEditor=rowEdit.getId().split('_')
	var idGrid='grid_'+datosEditor[1];
	var grid=Ext.getCmp(idGrid);
	if(grid.nuevoRegistro)
		grid.getStore().removeAt(grid.getStore().getCount()-1);
    if(Ext.getCmp('btnDel_'+idGrid)!=null)
		Ext.getCmp('btnDel_'+idGrid).enable();
    if(Ext.getCmp('btnAdd_'+idGrid)!=null)
	    Ext.getCmp('btnAdd_'+idGrid).enable();
    var copiaRegistro=grid.copiaRegistro;
    
    var x=0;
    var arrCampos=grid.getStore().fields;
    var filaDestino=grid.registroEdit;

    for(x=0;x<arrCampos.items.length;x++)
    {
    	filaDestino.set(arrCampos.items[x].name,copiaRegistro.get(arrCampos.items[x].name));

    }

    
	grid.nuevoRegistro=false;
	
}

function formatearValorRendererNumerico(matriz,valor)
{

	return formatearValorRenderer(matriz,''+valor,1,true);
}

function formatearValorRenderer(matriz,valor,columna,comparaNumerico,habilitarColor,habilitarIcono)
{
	var column=1;
	if(columna!=undefined)
		column=columna;
    var cNumerico=false;
    if(comparaNumerico)
    	cNumerico=comparaNumerico;
    var pos;
   	var color='';
    var icono='';
    if(valor.indexOf(',')!=-1)   
    {
    	var leyenda='';
    	var arrValores=valor.split(',');
        
        var x;
        for(x=0;x<arrValores.length;x++)
        {
        	
        	pos=existeValorMatriz(matriz,arrValores[x],0,cNumerico);
            if(pos!=-1)
            {
                if(habilitarColor)
                {
                    color=matriz[pos][2];
                }
                
                if(habilitarIcono)
                {
                	icono='<img src="'+matriz[pos][3]+'" width="16" height="16">&nbsp;';
                }
                
                if(!habilitarColor)
                {
                    if(leyenda=='')
                        leyenda=icono+'<span style="color:#'+color+'">'+matriz[pos][column]+'</span>';
                    else
                        leyenda+=", "+icono+"<span style=\"color:#'+color+'\">"+matriz[pos][column]+'</span>';
                }
                else
                {
                    if(leyenda=='')
                        leyenda=icono+matriz[pos][column];
                    else
                        leyenda+=", "+icono+matriz[pos][column];
                }
         	}
        }
        
        return leyenda;
    }
    else
    {   
        var pos=existeValorMatriz(matriz,valor,0,cNumerico);
    
        if(pos==-1)
            return '';
        else
        {
        	 if(habilitarIcono)
            {
                icono='<img src="'+matriz[pos][3]+'" width="16" height="16">&nbsp;';
            }
        	if(habilitarColor)
            {
                color=matriz[pos][2];
                
                return icono+'<span style="color:#'+color+'">'+ matriz[pos][column]+'</span>';
            }
            return icono+matriz[pos][column];
        }
    }
}

function formatearValorRendererCombo(almacen,valor,columna,columnaProy)
{
	
	var pos=obtenerPosFila(almacen,columna,valor);
	if(pos==-1)
		return '';
	else
		return almacen.getAt(pos).get(columnaProy);
}


function buscarNodoID(nodoBase,id)
{
	if(nodoBase.id==id)
    {
    	return nodoBase;
    }
	var arregloHijos=obtenerHijosNodoArbol(nodoBase);
	var x;
	var nodo=null;
	
	for(x=0;x<arregloHijos.length;x++)
	{
		
		if(arregloHijos[x].id==id)	
			nodo=arregloHijos[x];
		else
			nodo=buscarNodoID(arregloHijos[x],id);
		if(nodo!=null)
			break;
			
	}
	
	return nodo;
}

function generarCadenaExpresionQuery(arrExpresion,destino)
{
	var x;
    var cadena='';
    for(x=0;x<arrExpresion.length;x++)
    {
    	cadena+=' '+arrExpresion[x].tokenUsr;
    }
	if(destino!=null)
	    gEx(destino).setValue(cadena);
	return cadena;
}

function generarCadenaExpresionTexto(arrExpresion)
{
	var x;
    var cadena='';
	var obj;
	var tipoValor='';
    for(x=0;x<arrExpresion.length;x++)
    {
		if(arrExpresion[x].tipoValor!=undefined)
			tipoValor=arrExpresion[x].tipoValor;
		obj='{"tokenUsr":"'+arrExpresion[x].tokenUsr+'","tokenMysql":"'+arrExpresion[x].tokenMysql+'","tipoToken":"'+arrExpresion[x].tipoToken+'","tipoValor":"'+tipoValor+'"}';
		if(cadena=='')
    		cadena=obj;
		else
			cadena+=','+obj;
    }
	return '['+cadena+']';
}

function str_replace(busca, repla, orig)
{
	str 	= new String(orig);
	rExp	= "/"+busca+"/g";
	rExp	= eval(rExp);
	newS	= String(repla);
	str = new String(str.replace(rExp, newS));
	return str;
}

function generarNumAleatorio(minValue,maxValue) 
{
	var numPosibilidades = maxValue - minValue; 
   	var aleat = Math.random() * numPosibilidades ;
    
   	aleat = Math.floor(aleat) ;
   	return parseInt(minValue) + aleat ;
}

function generarColorAleatorio()
{
	var hexadecimal = new Array("0","1","2","3","4","5","6","7","8","9","A","B","C","D","E","F"); 
   	var color_aleatorio = "#"; 
    var posArray;
    var i;
    for (i=0;i<6;i++)
    { 
    	posArray = generarNumAleatorio(0,hexadecimal.length) 
        color_aleatorio += hexadecimal[posArray] 
    } 
    return color_aleatorio ;
}

function marcarRegistros(arrRegistros,grid,columna)
{
	var x;
    grid.getSelectionModel().clearSelections();
    var almacen=grid.getStore();
    for(x=0;x<arrRegistros.length;x++)
    {
    	var pos=obtenerPosFila(almacen,columna,arrRegistros[x]);
        if(pos!=-1)
        {
        	grid.getSelectionModel().selectRow(pos);
        }
    }
}

function removerCerosDerecha(cadena)
{
	var x;
    
    cadena=cadena+'';
    if(cadena.indexOf('.')==-1)
    	return cadena;
    var posIni=cadena.length-1;

    for(x=posIni;x>=0;x--)
    {
		if((cadena[x]=='.')||(cadena[x]!='0'))
        {
        	if(cadena[x]!='.')
            	x++;
            break;
        }    
    }
    return cadena.substr(0,x);
}



function ingresarProceso(iP,c,iR,iF)
{

	var idReferencia=-1;
    if(iR!=undefined)
    	idReferencia=bD(iR);
    var idFormulario=-1;
    if(iF!=undefined)
    	idFormulario=bD(iF);
    var ciclo=-1;
   
    if(c!=undefined)
    	ciclo=bD(c);
     
	var arrParam=[['idProceso',bD(iP)],['ciclo',ciclo],['idReferencia',idReferencia],['idFormulario',idFormulario],['sL','0']];
    if(document.URL.indexOf('vistaMacroProceso.php')==-1)
		enviarFormularioDatos('../modeloPerfiles/vistaProcesos.php',arrParam);
    else
    {
    	vListadoRegistros=true;
    	var content=Ext.getCmp('content');
        content.load({ scripts:true,url:'../modeloProyectos/visorRegistrosProcesos.php',params:{sL:0,ciclo:(ciclo),idProceso:bD(iP),cPagina:'sFrm=true',idReferencia:(idReferencia),idFormulario:(idFormulario)}});
    }
}

function ingresarProcesoPadre(iP)
{
	var arrParam=[['idProceso',bD(iP)]];
	window.parent.enviarFormularioDatos('../modeloPerfiles/vistaProcesos.php',arrParam);
}

function irInscripcion(c)
{
	var arrParam=[['ciclo',bD(c)]];
    enviarFormularioDatos('../Usuarios/reinscripcion.php',arrParam);
}

function vFilaSel(nGrid,msgError)
{
	var grid=nGrid;
    if(typeof(grid)=='string')
    	grid=gEx(nGrid);
        
	var fila=grid.getSelectionModel().getSelected();
    if(fila==null)
    {
    	msgBox(msgError);
        return null;
    }
	return fila;
}

function vNFilasSel(nGrid,msgError)
{
	var grid=nGrid;
    if(typeof(grid)=='string')
    	grid=gEx(nGrid);
	var fila=grid.getSelectionModel().getSelections();
    if(fila.length==null)
    {
    	msgBox(msgError);
        return null;
    }
	return fila;
}

function detenerEvento(e) 
{
    if (!e) 
    	e = window.event;
    if (e.stopPropagation) 
        e.stopPropagation();
    else 
        e.cancelBubble = true;
}

function cancelarEvento(e) 
{
    if (!e) 
    	e = window.event;
    if (e.preventDefault) 
        e.preventDefault();
    else
        e.returnValue = false;
}

function convertirDatSetArray(dataSet)
{
	var x;
    var fila;
    var arreglo=new Array();
    var row;
    var y;
    for(x=0;x<dataSet.getCount();x++)
    {
    	fila=dataSet.getAt(x);
        row=new Array();
        for(y=0;y<fila.fields.getCount();y++)
        {
        	row.push(fila.get(fila.fields.item(y).name));
        }
        arreglo.push(row);
    }
    return arreglo;
}

function obtenerDimensionesNavegador()
{
	var dimensiones=new Array();
	if(Ext.isIE)
    {
    	dimensiones[0]=window.document.body.clientHeight ;
        dimensiones[1]=window.document.body.clientWidth ;
    }
    else
    {
   		dimensiones[0]=window.innerHeight;
        dimensiones[1]=window.innerWidth ;	
   	}
	return dimensiones;
}

function cerrarVentanaFancy()
{
	if($.fancybox)
		$.fancybox.close();
    else
		jQuery.fancybox.close();
}

function abrirVentanaFancy(config)
{
	
    var width=600;
    if(config.ancho!=undefined)
    	width=config.ancho;
    var height=400;
    if(config.alto!=undefined)
    	height=config.alto;
    var titulo='';
    if(config.titulo!=undefined)
    	titulo=config.titulo;
    var url=config.url;
    
    var margen=5;
    var modal=false;
    if(config.modal!=undefined)
    {
    	modal=config.modal;
        if(!modal)
        	margen+=15;
    }
    else
    	margen+=15;
    var cadParam='';
    if(config.params!=undefined)
    {
    	
    	var x;
        var obj;
        for(x=0;x<config.params.length;x++)
        {
        	
        	if(cadParam=='')
            	cadParam+='?'+config.params[x][0]+'='+config.params[x][1];
            else
            	cadParam+='&'+config.params[x][0]+'='+config.params[x][1];
        }
    }
	url+=cadParam;
    
    var scrolling='auto';
    if(config.scrolling)
    	scrolling=config.scrolling;
    var objFancy=	{
                        'href'				: url,
                        'title'    			: titulo,			
                        'width'				: width,
                        'height'			: height,
                        'openEffect'		: 'none',
                        'closeEffect'		: 'none',
                        'type'				: 'iframe',
                        'modal'				:	modal,
                        'padding'			:	3,
                        'margin'			:	margen,
                        'scrolling':		scrolling,
                        'autoSize'			:	false
                    }
                    
    
    
    if(config.openEffect)
    	objFancy.openEffect=config.openEffect;
        
    if(config.closeEffect)
    	objFancy.closeEffect=config.closeEffect;
                    
	if(config.autoSize)
    	objFancy.autoSize=config.autoSize;
   
   
   if(config.complete)
   {
   		
    	objFancy.onComplete= config.complete;
        objFancy.afterLoad= config.complete;

   }
                    
    if(config.funcionCerrar!=undefined)
    {
		objFancy.onClosed=  config.funcionCerrar;                  
        objFancy.afterClose=  config.funcionCerrar;                  
    }
    
    if(config.funcionAntesCerrar!=undefined)
    {
        objFancy.beforeClose=  config.funcionAntesCerrar;                  
    }
    
    
    if(config.afterShow!=undefined)
    {
    	objFancy.afterShow=  config.afterShow;        
    }
    
    if(config.afterUpdate)
    {
   		
    	objFancy.afterUpdate= config.afterUpdate;
        

    }
    
    
    
    if((typeof($)!='undefined')&&($.fancybox))
	    $.fancybox(objFancy);	
    else
    	jQuery.fancybox(objFancy);	
}

function formatearfecha(date) 
{
    if (!date) 
    {
        return '';
    }
    var now = new Date();
    var d = now.clearTime(true);
    var notime = date.clearTime(true).getTime();
    if (notime == d.getTime()) 
    {
        return 'Hoy ' + date.dateFormat('g:i a');
    }
   
   	
    return date.dateFormat('d/m/Y g:i a');
}

function formatearSoloFecha(date) 
{
    if ((!date)||(date=='')) 
    {
        return '';
    }
    var now = new Date();
    var arrFecha=new Array();
    if(typeof(date)=='string')
    	arrFecha=date.split(' ');
    else
    	arrFecha.push(date.format('Y-m-d'));
    var objFecha= Date.parseDate(arrFecha[0],'Y-m-d');
    if (now.format('d/m/Y') == objFecha.format('d/m/Y')) 
    {
        return 'Hoy';
    }
    return objFecha.dateFormat('d/m/Y');
}

function verRegistroProyecto(iR,a,iF,param)
{
	var accion='auto';

	if(bD(iR)=='-1')

    	accion='agregar';

	var arrDatos=[["idFormulario",bD(iF)],["idRegistro",bD(iR)],["actor",a],['dComp',bE(accion)]];

    if(param)

    {

    	var arrParam=eval(bD(param));

        var x;

        for(x=0;x<arrParam.length;x++)

        {

        	arrDatos.push([arrParam[x][0],arrParam[x][1]]);

        }

    }

    var ventanaAbierta=window.open('',"vAuxiliar2", "toolbar=no,directories=no,menubar=no,status=no,scrollbars=yes,fullscreen=yes");

    enviarFormularioDatosV("../modeloPerfiles/vistaDTD.php",arrDatos,'POST','vAuxiliar2');

    return ventanaAbierta;
    
    
    
}

function verRegistroProyectoV2(iR,a,iF,param)
{
	var accion='auto';
	if(bD(iR)=='-1')
    	accion='agregar';
	var arrDatos=[["idFormulario",bD(iF)],["idRegistro",bD(iR)],["actor",a],['dComp',bE(accion)]];
    if(param)
    {
    	var arrParam=eval(bD(param));
        var x;
        for(x=0;x<arrParam.length;x++)
        {
        	arrDatos.push([arrParam[x][0],arrParam[x][1]]);
        }
    }

    var obj={};
    obj.ancho='100%';
    obj.alto='100%';
    obj.params=arrDatos;
    obj.url="../modeloPerfiles/vistaDTDv2.php";
	abrirVentanaFancy(obj);    
    
    
    
}

function verRegistroAsociado(iR,a,iF,ref)
{
	var arrDatos=[["idFormulario",bD(iF)],["idRegistro",bD(iR)],["actor",a],['dComp',bE('auto')],['idReferencia',bD(ref)]];
    var ventanaAbierta=window.open('',"vAuxiliar2", "toolbar=no,directories=no,menubar=no,status=no,scrollbars=yes,fullscreen=yes");
    enviarFormularioDatosV("../modeloPerfiles/vistaDTD.php",arrDatos,'POST','vAuxiliar2');
    return ventanaAbierta;
}

function convertirCadenaJson(obj)
{
	var campo;
    var cadObj='';
	for(campo in obj)
    {
    	if(cadObj=='')
        	cadObj='"'+campo+'":"'+cvTextArea(obj[campo],true)+'"';
        else
        	cadObj+=',"'+campo+'":"'+cvTextArea(obj[campo],true)+'"';
    }
    return '{'+cadObj+'}';
}

function setAtributoObjJson(obj,atributo,valor)
{
   	eval('obj.'+atributo+'="'+valor+'";')
    return obj;
}

function setAtributoCadJson(cadObj,atributo,valor)
{
	var obj=eval('['+cadObj+']')[0];
   	eval('obj.'+atributo+'="'+valor+'";')
    return convertirCadenaJson(obj);
}


function serialize (mixed_value) {
   
    var _utf8Size = function (str) {
        var size = 0,
            i = 0,
            l = str.length,
            code = '';
        for (i = 0; i < l; i++) {
            code = str.charCodeAt(i);
            if (code < 0x0080) {
                size += 1;
            } else if (code < 0x0800) {
                size += 2;
            } else {
                size += 3;
            }
        }
        return size;
    };
    var _getType = function (inp) {
        var type = typeof inp,
            match;
        var key;
 
        if (type === 'object' && !inp) {
            return 'null';
        }
        if (type === "object") {
            if (!inp.constructor) {
                return 'object';
            }
            var cons = inp.constructor.toString();
            match = cons.match(/(\w+)\(/);
            if (match) {
                cons = match[1].toLowerCase();
            }
            var types = ["boolean", "number", "string", "array"];
            for (key in types) {
                if (cons == types[key]) {
                    type = types[key];
                    break;
                }
            }
        }
        return type;
    };
    var type = _getType(mixed_value);
    var val, ktype = '';
 
    switch (type) {
    case "function":
        val = "";
        break;
    case "boolean":
        val = "b:" + (mixed_value ? "1" : "0");
        break;
    case "number":
        val = (Math.round(mixed_value) == mixed_value ? "i" : "d") + ":" + mixed_value;
        break;
    case "string":
        val = "s:" + _utf8Size(mixed_value) + ":\"" + mixed_value + "\"";
        break;
    case "array":
    case "object":
        val = "a";
/*
            if (type == "object") {
                var objname = mixed_value.constructor.toString().match(/(\w+)\(\)/);
                if (objname == undefined) {
                    return;
                }
                objname[1] = this.serialize(objname[1]);
                val = "O" + objname[1].substring(1, objname[1].length - 1);
            }
            */
        var count = 0;
        var vals = "";
        var okey;
        var key;
        for (key in mixed_value) {
            if (mixed_value.hasOwnProperty(key)) {
                ktype = _getType(mixed_value[key]);
                if (ktype === "function") {
                    continue;
                }
 
                okey = (key.match(/^[0-9]+$/) ? parseInt(key, 10) : key);
                vals += this.serialize(okey) + this.serialize(mixed_value[key]);
                count++;
            }
        }
        val += ":" + count + ":{" + vals + "}";
        break;
    case "undefined":
        // Fall-through
    default:
        // if the JS object has a property which contains a null value, the string cannot be unserialized by PHP
        val = "N";
        break;
    }
    if (type !== "object" && type !== "array") {
        val += ";";
    }
    return val;
}

function entitiesDecode(valor)
{
	return valor.replace('<br />','').replace('&aacute;','á').replace('&eacute;','é').replace('&iacute;','í').replace('&oacute;','ó').replace('&uacute;','ú').replace('&ntilde;','ñ').replace('&Aacute;','Á').replace('&Eacute;','É').replace('&Iacute;','Í').replace('&Oacute;','Ó').replace('&Uacute;','Ú').replace('&Ntilde;','Ñ');
}

function str_pad (input, pad_length, pad_string, pad_type) 
{
    var half = '',
        pad_to_go;
 
    var str_pad_repeater = function (s, len) 
    {
        var collect = '',
            i;
     	while (collect.length < len) 
     	{
            collect += s;
        }
        collect = collect.substr(0, len);
 
        return collect;
    };
 
    input += '';
    pad_string = pad_string !== undefined ? pad_string : ' ';
 
    if (pad_type != 'STR_PAD_LEFT' && pad_type != 'STR_PAD_RIGHT' && pad_type != 'STR_PAD_BOTH') {
        pad_type = 'STR_PAD_RIGHT';
    }
    if ((pad_to_go = pad_length - input.length) > 0) {
        if (pad_type == 'STR_PAD_LEFT') {
            input = str_pad_repeater(pad_string, pad_to_go) + input;
        } else if (pad_type == 'STR_PAD_RIGHT') {
            input = input + str_pad_repeater(pad_string, pad_to_go);
        } else if (pad_type == 'STR_PAD_BOTH') {
            half = str_pad_repeater(pad_string, Math.ceil(pad_to_go / 2));
            input = half + input + half;
            input = input.substr(0, pad_length);
        }
    }
 
    return input;
}

function colisionaTiempo(tI1,tF1,tI2,tF2,cosiderarLimites,formato)
{
	var f='Y-m-d H:i A';
    if(formato!=undefined)
    	f=formato;
	var tInicio1=Date.parseDate(tI1,f);
    var tFin1=Date.parseDate(tF1,f);
    var tInicio2=Date.parseDate(tI2,f);
    var tFin2=Date.parseDate(tF2,f);
    
    if(cosiderarLimites)
	{
    	if((tInicio1>=tInicio2)&&(tInicio1<=tFin2))
			return true;
		else
			if((tFin1>=tInicio2)&&(tFin1<=tFin2))
				return true;
		
		if((tInicio2>=tInicio1)&&(tInicio2<=tFin1))
			return true;
		else
			if((tFin2>=tInicio1)&&(tFin2<=tFin1))
				return true;
	}
	else
	{
		if((tInicio1>=tInicio2)&&(tInicio1<tFin2))
			return true;
		else
			if((tFin1>tInicio2)&&(tFin1<=tFin2))
				return true;
		
		if((tInicio2>=tInicio1)&&(tInicio2<tFin1))
			return true;
		else
			if((tFin2>tInicio1)&&(tFin2<=tFin1))
				return true;
	}
	return false;
}

function hDebugC()
{
	if(typeof(obtenerDatosWeb)!='undefined')
    {
        function funcAjax()
        {
            var resp=peticion_http.responseText;
            arrResp=resp.split('|');
            if(arrResp[0]=='1')
            {
                msgBox("Modo debug de c&aacute;lculos habilitado");
            }
            else
            {
                msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
            }
        }
        obtenerDatosWeb('../paginasFunciones/funciones.php',funcAjax, 'POST','funcion=5&tDebug=1&accion=1',true);
	}
    else
    {
    	 function funcAjax(peticion_http)
        {
            var resp=peticion_http.responseText;
            arrResp=resp.split('|');
            if(arrResp[0]=='1')
            {
                msgBox("Modo debug de c&aacute;lculos habilitado");
            }
            else
            {
                msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
            }
        }
        obtenerDatosWebV2('../paginasFunciones/funciones.php',funcAjax, 'POST','funcion=5&tDebug=1&accion=1',true);
    }    
}

function dDebugC()
{
	if(typeof(obtenerDatosWeb)!='undefined')
    {
    
        function funcAjax()
        {
            var resp=peticion_http.responseText;
            arrResp=resp.split('|');
            if(arrResp[0]=='1')
            {
                msgBox("Modo debug de c&aacute;lculos deshabilitado");
                
            }
            else
            {
                msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
            }
        }
        obtenerDatosWeb('../paginasFunciones/funciones.php',funcAjax, 'POST','funcion=5&tDebug=1&accion=0',true);	
	}
    else
    {
    	function funcAjax(peticion_http)
        {
            var resp=peticion_http.responseText;
            arrResp=resp.split('|');
            if(arrResp[0]=='1')
            {
                msgBox("Modo debug de c&aacute;lculos deshabilitado");
                
            }
            else
            {
                msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
            }
        }
        obtenerDatosWebV2('../paginasFunciones/funciones.php',funcAjax, 'POST','funcion=5&tDebug=1&accion=0',true);	
    }
}

function hDebugQ()
{
	if(typeof(obtenerDatosWeb)!='undefined')
    {
        function funcAjax()
        {
            var resp=peticion_http.responseText;
            arrResp=resp.split('|');
            if(arrResp[0]=='1')
            {
                msgBox("Modo debug de consultas habilitado");
            }
            else
            {
                msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
            }
        }
        obtenerDatosWeb('../paginasFunciones/funciones.php',funcAjax, 'POST','funcion=5&tDebug=2&accion=1',true);
    }
	else
    {
    	function funcAjax(peticion_http)
        {
            var resp=peticion_http.responseText;
            arrResp=resp.split('|');
            if(arrResp[0]=='1')
            {
                msgBox("Modo debug de consultas habilitado");
            }
            else
            {
                msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
            }
        }
        obtenerDatosWebV2('../paginasFunciones/funciones.php',funcAjax, 'POST','funcion=5&tDebug=2&accion=1',true);
    }
}


function dDebugQ()
{
	if(typeof(obtenerDatosWeb)!='undefined')
    {
        function funcAjax()
        {
            var resp=peticion_http.responseText;
            arrResp=resp.split('|');
            if(arrResp[0]=='1')
            {
                msgBox("Modo debug de consultas deshabilitado");
            }
            else
            {
                msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
            }
        }
        obtenerDatosWeb('../paginasFunciones/funciones.php',funcAjax, 'POST','funcion=5&tDebug=2&accion=0',true);
	}
    else
    {
    	function funcAjax(peticion_http)
        {
            var resp=peticion_http.responseText;
            arrResp=resp.split('|');
            if(arrResp[0]=='1')
            {
                msgBox("Modo debug de consultas deshabilitado");
            }
            else
            {
                msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
            }
        }
        obtenerDatosWebV2('../paginasFunciones/funciones.php',funcAjax, 'POST','funcion=5&tDebug=2&accion=0',true);

    }
}

function hDebugBloque()
{
	function funcAjax()
    {
        var resp=peticion_http.responseText;
        arrResp=resp.split('|');
        if(arrResp[0]=='1')
        {
            msgBox("Modo debug de consultas habilitado");
        }
        else
        {
            msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
        }
    }
    obtenerDatosWeb('../paginasFunciones/funciones.php',funcAjax, 'POST','funcion=5&tDebug=3&accion=1',true);
}

function dDebugBloque()
{
	function funcAjax()
    {
        var resp=peticion_http.responseText;
        arrResp=resp.split('|');
        if(arrResp[0]=='1')
        {
            msgBox("Modo debug de consultas deshabilitado");
        }
        else
        {
            msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
        }
    }
    obtenerDatosWeb('../paginasFunciones/funciones.php',funcAjax, 'POST','funcion=5&tDebug=3&accion=0',true);
}

function hDebugConsulta()
{
	function funcAjax()
    {
        var resp=peticion_http.responseText;
        arrResp=resp.split('|');
        if(arrResp[0]=='1')
        {
            msgBox("Modo debug de consultas habilitado");
        }
        else
        {
            msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
        }
    }
    obtenerDatosWeb('../paginasFunciones/funciones.php',funcAjax, 'POST','funcion=5&tDebug=4&accion=1',true);
}

function dDebugConsulta()
{
	function funcAjax()
    {
        var resp=peticion_http.responseText;
        arrResp=resp.split('|');
        if(arrResp[0]=='1')
        {
            msgBox("Modo debug de consultas deshabilitado");
        }
        else
        {
            msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
        }
    }
    obtenerDatosWeb('../paginasFunciones/funciones.php',funcAjax, 'POST','funcion=5&tDebug=4&accion=0',true);
}

function checarNodosHijos(nodo,valor)
{
	var x;
    for(x=0;x<nodo.childNodes.length;x++)
    {
    	nodo.childNodes[x].getUI().toggleCheck(valor);
        checarNodosHijos(nodo.childNodes[x],valor);
    }
}

function mostrarVentanaDuda()
{
	var filter=/^[A-Za-z0-9\._\-]+@[A-Za-z0-9_\-]+(\.[A-Za-z]+){1,2}$/;

	var form = new Ext.form.FormPanel(	
										{
											baseCls: 'x-plain',
											layout:'absolute',
											defaultType: 'label',
											items: 	[
														{
                                                        	x:10,
                                                            y:10,
                                                            html:'<span style="color:#000000">Ingrese su nombre:</span> <span style="color:#FF0000">*</span>'
                                                        },
                                                        {
                                                        	x:140,
                                                            y:5,
                                                            xtype:'textfield',
                                                            width:325,
                                                            id:'txtNombre'
                                                        },
                                                        {
                                                        	x:10,
                                                            y:40,
                                                            html:'<span style="color:#000000">E-mail de contacto:</span> <span style="color:#FF0000">*</span>'
                                                        },
                                                        {
                                                        	x:140,
                                                            y:35,
                                                            xtype:'textfield',
                                                            width:240,
                                                            id:'txtEmail'
                                                        },
							{
								x:10,
								y:70,
								html:'<span style="color:#000000">Tel&eacute;fono de contacto:</span>'
							},
							{
								x:140,
								y:65,
								xtype:'textfield',
								allowDecimals:false,
								allowNegative:false,
								width:300,
								id:'txtTelefono'
							},
                                                        {
                                                        	x:10,
                                                            y:100,
                                                            html:'<span class="letraRojaSubrayada8"><b>Por favor ingrese su Duda / Comentario:</b></span> <span style="color:#FF0000">*</span>'
                                                        },
                                                        {
                                                        	x:10,
                                                            y:130,
                                                            width:505,
                                                            height:140,
                                                            id:'txtComentario',
                                                            xtype:'textarea'
                                                        }
                                                        

													]
										}
									);
	
	var ventanaAM = new Ext.Window(
									{
										title: 'Buz&oacute;n de Dudas  / Comentarios',
										width: 550,
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
                                                                	gEx('txtNombre').focus(false,500);
																}
															}
												},
										buttons:	[
														{
															
															text: '<?php echo $etj["lblBtnAceptar"]?>',
                                                            
															handler: function()
																	{
																		var txtNombre=gEx('txtNombre');
                                                                        var txtEmail=gEx('txtEmail');
                                                                        var txtComentario=gEx('txtComentario');
                                                                        if(txtNombre.getValue().trim()=='')
                                                                        {
                                                                        	function resp()
                                                                            {
                                                                            	txtNombre.focus();
                                                                            }
                                                                            msgBox('Debe ingresar su nombre',resp)
                                                                            return;
                                                                        }
                                                                        
                                                                        if(txtEmail.getValue().trim()=='')
                                                                        {
                                                                        	function resp2()
                                                                            {
                                                                            	txtEmail.focus();
                                                                            }
                                                                            msgBox('El campo de E-mail de contacto es obligatorio',resp2)
                                                                            return;
                                                                        }
                                                                       	if (!filter.test(txtEmail.getValue().trim()))
                                                                        {
                                                                        	function resp4()
                                                                            {
                                                                            	txtEmail.focus();
                                                                            }
                                                                            msgBox('El E-mail ingresado no es v&aacute;lido',resp4)
                                                                            return;
                                                                        }
                                                                        
                                                                        
                                                                        if(txtComentario.getValue().trim()=='')
                                                                        {
                                                                        	function resp3()
                                                                            {
                                                                            	txtComentario.focus();
                                                                            }
                                                                            msgBox('Debe ingresar la duda o comentario que desea enviar',resp3)
                                                                            return;
                                                                        }
                                                                        
                                                                        
                                                                        var obj='{"telefono":"'+cv(gEx('txtTelefono').getValue())+'","nombre":"'+cv(txtNombre.getValue().trim())+'","email":"'+cv(txtEmail.getValue().trim())+'","comentario":"'+cv(txtComentario.getValue().trim())+'"}';
                                                                        function funcAjax(peticion_http)
                                                                        {
                                                                            var resp=peticion_http.responseText;
                                                                            arrResp=resp.split('|');
                                                                            if(arrResp[0]=='1')
                                                                            {
                                                                            	function resp6()
                                                                                {
                                                                                	ventanaAM.close();
                                                                                }
                                                                                msgBox('Su duda / comentario ha sido enviado exitosamente, en breve recibir&aacute; respuesta',resp6);
                                                                                
                                                                            }
                                                                            else
                                                                            {
                                                                                msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
                                                                            }
                                                                        }
                                                                        obtenerDatosWebV2('../paginasFunciones/funciones.php',funcAjax, 'POST','funcion=6&cadObj='+obj,true);
                                                                        
                                                                        
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

function mostrarVentanaDudaUsuario()
{
	var filter=/^[A-Za-z0-9\._\-]+@[A-Za-z0-9_\-]+(\.[A-Za-z]+){1,2}$/;

	var form = new Ext.form.FormPanel(	
										{
											baseCls: 'x-plain',
											layout:'absolute',
											defaultType: 'label',
											items: 	[
														
                                                        {
                                                        	x:10,
                                                            y:10,
                                                            html:'<span class="letraRojaSubrayada8"><b>Por favor ingrese su Duda / Comentario:</b></span> <span style="color:#FF0000">*</span>'
                                                        },
                                                        {
                                                        	x:10,
                                                            y:35,
                                                            width:445,
                                                            height:170,
                                                            id:'txtComentario',
                                                            xtype:'textarea'
                                                        }
                                                        

													]
										}
									);
	
	var ventanaAM = new Ext.Window(
									{
										title: 'Buz&oacute;n de Dudas  / Comentarios',
										width: 490,
										height:305,
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
                                                                	gEx('txtNombre').focus();
																}
															}
												},
										buttons:	[
														{
															
															text: '<?php echo $etj["lblBtnAceptar"]?>',
                                                            
															handler: function()
																	{
																		var txtComentario=gEx('txtComentario');
                                                                        if(txtComentario.getValue().trim()=='')
                                                                        {
                                                                        	function resp3()
                                                                            {
                                                                            	txtComentario.focus();
                                                                            }
                                                                            msgBox('Debe ingresar la duda o comentario que desea enviar',resp3)
                                                                            return;
                                                                        }
                                                                        
                                                                        
                                                                        var obj='{"comentario":"'+cv(txtComentario.getValue().trim())+'"}';
                                                                        function funcAjax(peticion_http)
                                                                        {
                                                                            var resp=peticion_http.responseText;
                                                                            arrResp=resp.split('|');
                                                                            if(arrResp[0]=='1')
                                                                            {
                                                                            	function resp6()
                                                                                {
                                                                                	ventanaAM.close();
                                                                                }
                                                                                msgBox('Su duda / comentario ha sido enviado exitosamente, en breve recibir&aacute; respuesta',resp6);
                                                                                
                                                                            }
                                                                            else
                                                                            {
                                                                                msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
                                                                            }
                                                                        }
                                                                        obtenerDatosWebV2('../paginasFunciones/funciones.php',funcAjax, 'POST','funcion=6&cadObj='+obj,true);
                                                                        
                                                                        
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

function getDocumento(u,i)
{
	var arrParam=[['iD',(i)]];
    enviarFormularioDatos(bD(u),arrParam);
}

function showDocumento(t,e,i)
{
	var obj={};
    var extension=bD(e);

    var pos=existeValorMatriz(arrVisores,extension,4);
    if(pos==-1)
	{
    	msgBox('No existe visor de documento disponible');
    	return;
    }
    var fila=arrVisores[pos];
    obj.titulo=bD(t);
    obj.url=fila[1];
    obj.ancho=parseFloat(fila[2]);
    obj.alto=parseFloat(fila[3]);
    obj.params=[['iD',(i)],['cPagina','sFrm=true']];
    abrirVentanaFancy(obj);
}

function mostrarOpcionFancy(url,o)
{
	var obj={};
    obj.titulo='';
    obj.ancho='95%';
    obj.alto='95%';
    obj.url=url;
    if(o!=undefined)
    {
    	var ob=eval('['+bD(o)+']')[0];
        if(ob.titulo!=undefined)
        	obj.titulo=ob.titulo;
        if(ob.ancho!=undefined)
        	obj.ancho=ob.ancho;
        if(ob.alto!=undefined)
        	obj.alto=ob.alto;
        if(ob.params!=undefined)
        	obj.params=ob.params;
            
    }
    abrirVentanaFancy(obj);
    
}

function habilitarMantenimientoSesion(minutosReactivacion)
{

	setInterval('llamadaActualizacionSesionAjax()',(minutosReactivacion*60000));	
}

function llamadaActualizacionSesionAjax()
{

	var motorAjax=crearMotorAjax();
    var datos='funcion=0';
    if(motorAjax)
    {
        motorAjax.onreadystatechange=function()
        								{
                                        	if(motorAjax.readyState==PETICION_COMPLETO)
                                            {
                                                if(motorAjax.status==RESPUESTA_OK)
                                                {
                                                    
                                                }
                                                else
                                                {
                                                    
                                                }
                                            }
                                        }
                                        
        motorAjax.open("POST","../paginasFunciones/funcionesPortal.php",true);
        motorAjax.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
        motorAjax.send(datos);

    }
}

function mostrarValorDescripcion(val)
{
	return '<span alt="'+escaparBR(Ext.util.Format.stripTags(val.replace(/"/gi,'&quot;')))+'" title="'+escaparBR(Ext.util.Format.stripTags(val.replace(/"/gi,'&quot;')))+'">'+val+'</span>';
}

function obtenerPosObjeto(arrObjetos,campo,valor)
{
	var x;
    for(x=0;x<arrObjetos.length;x++)
    {
    	if(arrObjetos[x][campo]==valor)
        	return x;
    }
    return -1;
}

function dispararEventoSelectCombo(idCombo,valorNull)
{
	var combo=gEx(idCombo);

    var pos=obtenerPosFila(combo.getStore(),combo.initialConfig .valueField,combo.getValue());
    if(pos!=-1)
    {
    
        var registro=combo.getStore().getAt(pos);
        
        combo.fireEvent('select',combo,registro);
    }
    else
    {
    	if(valorNull)
        {
        	combo.fireEvent('select',combo,null);
        }
    }
}

function normalizarValorRGB(valor)
{
	var valorTmp="";
    if(valor.length==3)
    {
    	var x;
        for(x=0;x<3;x++)	
		{
			valorTmp+=valor.substr(x,1)+valor.substr(x,1);
		}
    }
    else
    	valorTmp=valor;
    return valorTmp;
}

function rendererNulo(val)
{
	return val;
}

function formatearArchivoFormulario(val)
{
    var descArch='';
    if(val!='')
        descArch='<a href="../paginasFunciones/obtenerArchivos.php?id='+bE(val)+'"><img src="../images/download.png" alt="Descargar" title="Descargar" />&nbsp;&nbsp;Descargar</a>';
    else
        descArch='Sin documento';
    return descArch;
}


function obtenerMunicipio(estado,ctrlDestino,tipo,valSel) //Sin tipo o 0 Ext,1 Control frm
{
	function funcAjax()
    {
        var resp=peticion_http.responseText;
        arrResp=resp.split('|');
        if(arrResp[0]=='1')
        {
        	var arrDatos=eval(arrResp[1]);
            if(tipo==1)
            {
            	llenarCombo(gE(ctrlDestino),arrDatos,true);
                if(valSel)
                	selElemCombo(gE(ctrlDestino),valSel);
                	
            }
            else
            {
            	gEx(ctrlDestino).getStore().loadData(arrDatos);
                if(valSel)
	                gEx(ctrlDestino).setValue(valSel);
            }
        }
        else
        {
            msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
        }
    }
    obtenerDatosWeb('../paginasFunciones/funcionesOrganigrama.php',funcAjax, 'POST','funcion=60&accion=1&codigo='+estado,true);
}

function obtenerLocalidad(municipio,ctrlDestino,tipo,valSel) //Sin tipo o 0 Ext,1 Control frm
{
	function funcAjax()
    {
        var resp=peticion_http.responseText;
        arrResp=resp.split('|');
        if(arrResp[0]=='1')
        {
        	var arrDatos=eval(arrResp[1]);
          	if(tipo==1)
            {
            	llenarCombo(gE(ctrlDestino),arrDatos,true);
                if(valSel)
                	selElemCombo(gE(ctrlDestino),valSel);
                	
            }
            else
            {
            	gEx(ctrlDestino).getStore().loadData(arrDatos);
                if(valSel)
	                gEx(ctrlDestino).setValue(valSel);
            }
        }
        else
        {
            msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
        }
    }
    obtenerDatosWeb('../paginasFunciones/funcionesOrganigrama.php',funcAjax, 'POST','funcion=60&accion=2&codigo='+municipio,true);
    

}

function obtenerColonia(localidad,ctrlDestino,tipo,valSel) //Sin tipo o 0 Ext,1 Control frm
{
	function funcAjax()
    {
        var resp=peticion_http.responseText;
        arrResp=resp.split('|');
        if(arrResp[0]=='1')
        {
        	var arrDatos=eval(arrResp[1]);
            if(tipo==1)
            {
            	llenarCombo(gE(ctrlDestino),arrDatos,true);
                if(valSel)
                	selElemCombo(gE(ctrlDestino),valSel);
                	
            }
            else
            {
            	gEx(ctrlDestino).getStore().loadData(arrDatos);
                if(valSel)
	                gEx(ctrlDestino).setValue(valSel);
            }
        }
        else
        {
            msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
        }
    }
    obtenerDatosWeb('../paginasFunciones/funcionesOrganigrama.php',funcAjax, 'POST','funcion=60&accion=3&codigo='+localidad,true);
}

function ejecutarPV(iP)
{
    
    var objParam={};
    objParam.idCaja=bD(iP);
   
	var content=gEx('content');
    if(content)
    {
        content.load	(
                            {
                                url:'../caja/puntoVenta.php',
                                params:objParam,
                                scripts:true
                            }
                        )
	}
    else
    {
    	var arrParam=[['idCaja',bD(iP)],['mRegresar','1']];
    	enviarFormularioDatos('../caja/puntoVenta.php',arrParam);
    }
}

function existeValorArregloObjetos(arreglo,campo,valor)
{
	var x;
    var res;
    for(x=0;x<arreglo.length;x++)
    {
    	res=false;
        eval('if(arreglo[x].'+campo+'==valor) res=true;');
        if(res)
        	return x;
    }
    return -1;
}

function getValorCombo(combo)
{
	if(typeof(combo)=='string')
    	combo=gE(combo);
	if(combo.selectedIndex==-1)
    	return -1;
	var valor=combo.options[combo.selectedIndex].value;
    return valor;
}

function abrirAdmonAlmacen(iA)
{
	var arrParam=[['idAlmacen',bD(iA)]];
    enviarFormularioDatos('../almacen/admonAlmacen.php',arrParam);
}

function abrirAdmonAlmacenFancy(iA)
{
	
    var objConf={};
    objConf.url='../almacen/admonAlmacen.php';
    objConf.params=[['idAlmacen',bD(iA)]];
    objConf.ancho='95%';
    objConf.alto='95%';
	abrirVentanaFancy(objConf);    
}

function autofitIframe(id,funcAfterHeight)
{
	
	id.style.height=1+"px";
	if (!window.opera && document.all && document.getElementById)
    {

		setTimeout(function()
        			{
                    		
                    		id.style.height=id.contentWindow.document.body.scrollHeight+'px';
                            if(funcAfterHeight)
                            	funcAfterHeight();
                     },1000);
		
	} 
    else 
    {
    	if(document.getElementById) 
        {
			if(!Ext.isGecko)
	            setTimeout(function()
                			{
                            	
                            	id.style.height=id.contentDocument.body.scrollHeight+"px";
                                if(funcAfterHeight)
                            		funcAfterHeight();
                            },1000);
            else
            {
            	//console.log(id.contentDocument);
            	setTimeout(function()
                			{
                            	
                            	id.style.height="1800px";
                                if(funcAfterHeight)
                            		funcAfterHeight();
                            },1000);
            }
			
		}
    }
}

function buscarIdRegistro(almacen,id)
{
	var x;
    var fila;
    for(x=0;x<almacen.getCount();x++)
    {
    	fila=almacen.getAt(x);
        if(fila.id==id)
        	return x;
    }
    return -1;
}

function renderizarValorCombo(combo,valor)
{
	var pos=obtenerPosFila(combo.getStore(),combo.initialConfig.valueField,valor);
    if(pos!=-1)
    {
    	return combo.getStore().getAt(pos).get(combo.initialConfig.displayField);
    }
    return '';
}

function vIE()
{
	return (navigator.appName=='Microsoft Internet Explorer')?parseFloat((new RegExp("MSIE ([0-9]{1,}[.0-9]{0,})")).exec(navigator.userAgent)[1]):-1;
}



function obtenerDocumentoUsr(idDocumento)
{
    var arrParam=[['id',(idDocumento)]];
    enviarFormularioDatos('../paginasFunciones/obtenerArchivos.php',arrParam,'GET');
}

function verVideoTutorial(f,w,h)
{
	var objConf={};
    objConf.url='../media/verVideosFlash.php';
    objConf.params=[['f',f]];
    if(w)
    	objConf.ancho=w;
    else
    	objConf.ancho='95%';
    
    if(h)
    	objConf.alto=h;
    else
    	objConf.alto='95%';
	abrirVentanaFancy(objConf);
}

function abrirUrl(URL)
{
	window.location.href=URL;
}

function obtenerFormatoParaPagoReferenciado(ref)
{
	var arrParam=[['referencia',bD(ref)]];
    enviarFormularioDatos('../reportes/generarPagoReferenciado.php',arrParam);
}

function abrirFormularioProceso(iF,iR,a,param,param2)
{
	var accion='auto';
    if(bD(iR)=='-1')
    	accion="agregar";
	var arrDatos=[["idFormulario",bD(iF)],["idRegistro",bD(iR)],["actor",a],['dComp',bE(accion)],['actorInicio',1]];
    
    if(param)
    {
	    var aParam=eval(bD(param));
        var x;
        var pos;
        for(x=0;x<aParam.length;x++)
        {
        	pos=existeValorMatriz(arrDatos,aParam[x][0]);
        	if(pos==-1)
	        	arrDatos.push([aParam[x][0],aParam[x][1]]);
            else
            	arrDatos[pos][1]=aParam[x][1];
        }
    }
    if(param2)
    {
	    var aParam=eval(bD(param2));
        var x;
        var pos;
        for(x=0;x<aParam.length;x++)
        {
        	pos=existeValorMatriz(arrDatos,aParam[x][0]);
        	if(pos==-1)
	        	arrDatos.push([aParam[x][0],aParam[x][1]]);
            else
            	arrDatos[pos][1]=aParam[x][1];
        }
    }
    var ventanaAbierta=window.open('',"vAuxiliar2", "toolbar=no,directories=no,menubar=no,status=no,scrollbars=yes,fullscreen=yes");
    enviarFormularioDatosV("../modeloPerfiles/vistaDTD.php",arrDatos,'POST','vAuxiliar2');
    return ventanaAbierta;
}

function obtenerNodoChecados(raiz)
{
	var z=0;
	var arrNodos=new Array();
	var enc=false;
	var nodoSel=null;
	while(z<raiz.childNodes.length) 
	{
		if(raiz.childNodes[z].getUI().isChecked( ) )
        {
        	arrNodos.push(raiz.childNodes[z]);
        }
        obtenerNodoChecadosV2(raiz.childNodes[z],arrNodos);
		z++;
	}
	return arrNodos;
}

function obtenerNodoChecadosV2(raiz,arrNodos)
{
	var z=0;
	
	var enc=false;
	
	while(z<raiz.childNodes.length) 
	{
		if(raiz.childNodes[z].getUI().isChecked( ) )
        {
        	arrNodos.push(raiz.childNodes[z]);
        }
        obtenerNodoChecadosV2(raiz.childNodes[z],arrNodos);
		z++;
	}
	
}

function bytesToSize(bytes, p)
{  
	var precision=0;
	if(p)
    	precision=p;
    var kilobyte = 1024;
    var megabyte = kilobyte * 1024;
    var gigabyte = megabyte * 1024;
    var terabyte = gigabyte * 1024;
   
    if ((bytes >= 0) && (bytes < kilobyte)) {
        return bytes + ' B';
 
    } else if ((bytes >= kilobyte) && (bytes < megabyte)) {
        return (bytes / kilobyte).toFixed(precision) + ' KB';
 
    } else if ((bytes >= megabyte) && (bytes < gigabyte)) {
        return (bytes / megabyte).toFixed(precision) + ' MB';
 
    } else if ((bytes >= gigabyte) && (bytes < terabyte)) {
        return (bytes / gigabyte).toFixed(precision) + ' GB';
 
    } else if (bytes >= terabyte) {
        return (bytes / terabyte).toFixed(precision) + ' TB';
 
    } else {
        return bytes + ' B';
    }
}

function obtenerMunicipioV2(estado,ctrlDestino,tipo,valSel,funcionEjecucion) //Sin tipo o 0 Ext,1 Control frm
{
	function funcAjax()
    {
        var resp=peticion_http.responseText;
        arrResp=resp.split('|');
        if(arrResp[0]=='1')
        {
        	var arrDatos=eval(arrResp[1]);
            if(tipo==1)
            {
            	llenarCombo(gE(ctrlDestino),arrDatos,true);
                if(valSel)
                	selElemCombo(gE(ctrlDestino),valSel);
                	
            }
            else
            {
            	gEx(ctrlDestino).getStore().loadData(arrDatos);
                if(valSel)
	                gEx(ctrlDestino).setValue(valSel);
            }
            if(funcionEjecucion)
                funcionEjecucion();

        }
        else
        {
            msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
        }
    }
    obtenerDatosWeb('../paginasFunciones/funcionesOrganigrama.php',funcAjax, 'POST','funcion=69&accion=1&codigo='+estado,true);
}

function obtenerLocalidadV2(municipio,ctrlDestino,tipo,valSel,funcionEjecucion) //Sin tipo o 0 Ext,1 Control frm
{
	function funcAjax()
    {
        var resp=peticion_http.responseText;
        arrResp=resp.split('|');
        if(arrResp[0]=='1')
        {
        	var arrDatos=eval(arrResp[1]);
          	if(tipo==1)
            {
            	llenarCombo(gE(ctrlDestino),arrDatos,true);
                if(valSel)
                	selElemCombo(gE(ctrlDestino),valSel);
                	
            }
            else
            {
            	gEx(ctrlDestino).getStore().loadData(arrDatos);
                if(valSel)
	                gEx(ctrlDestino).setValue(valSel);
            }
            if(funcionEjecucion)
                funcionEjecucion();
        }
        else
        {
            msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
        }
    }
    obtenerDatosWeb('../paginasFunciones/funcionesOrganigrama.php',funcAjax, 'POST','funcion=69&accion=2&codigo='+municipio,true);
    

}

function abrirFormularioProcesoFancy(iF,iR,a,param,param2)
{
	var accion='auto';
    if(bD(iR)=='-1')
    	accion="agregar";
	var arrDatos=[["idFormulario",bD(iF)],["idRegistro",bD(iR)],["actor",a],['dComp',bE(accion)],['actorInicio',1]];
    
    if(param)
    {
	    var aParam=eval(bD(param));
        var x;
        var pos;
        for(x=0;x<aParam.length;x++)
        {
        	pos=existeValorMatriz(arrDatos,aParam[x][0]);
        	if(pos==-1)
	        	arrDatos.push([aParam[x][0],aParam[x][1]]);
            else
            	arrDatos[pos][1]=aParam[x][1];
        }
    }
    if(param2)
    {
	    var aParam=eval(bD(param2));
        var x;
        var pos;
        for(x=0;x<aParam.length;x++)
        {
        	pos=existeValorMatriz(arrDatos,aParam[x][0]);
        	if(pos==-1)
	        	arrDatos.push([aParam[x][0],aParam[x][1]]);
            else
            	arrDatos[pos][1]=aParam[x][1];
        }
    }
    
    var obj={};
    obj.url="../modeloPerfiles/vistaDTDv3.php";
    obj.params=arrDatos;
    obj.ancho='100%';
    obj.alto='100%';
    
    abrirVentanaFancy(obj);
    
    
    
}

function abrirFormularioFancy(iF,iR,a,param,param2)
{
	var accion='auto';
    if(bD(iR)=='-1')
    	accion="agregar";
	var arrDatos=[["idFormulario",bD(iF)],["idRegistro",bD(iR)],["actor",a],['dComp',bE(accion)],['actorInicio',1]];
    
    if(param)
    {
	    var aParam=eval(bD(param));
        var x;
        var pos;
        for(x=0;x<aParam.length;x++)
        {
        	pos=existeValorMatriz(arrDatos,aParam[x][0]);
        	if(pos==-1)
	        	arrDatos.push([aParam[x][0],aParam[x][1]]);
            else
            	arrDatos[pos][1]=aParam[x][1];
        }
    }
    if(param2)
    {
	    var aParam=eval(bD(param2));
        var x;
        var pos;
        for(x=0;x<aParam.length;x++)
        {
        	pos=existeValorMatriz(arrDatos,aParam[x][0]);
        	if(pos==-1)
	        	arrDatos.push([aParam[x][0],aParam[x][1]]);
            else
            	arrDatos[pos][1]=aParam[x][1];
        }
    }
    
    var obj={};
    
    if(bD(iR)=='-1')
        obj.url="../modeloPerfiles/registroFormulario.php";
    else
    	obj.url="../modeloPerfiles/verFichaFormulario.php";
    obj.params=arrDatos;
    obj.ancho='100%';
    obj.alto='100%';
    
    abrirVentanaFancy(obj);
    
    
    
}

function obtenerOrdenPagoReferenciado(iC,iF,iR)
{
	function funcAjax()
    {
        var resp=peticion_http.responseText;
        arrResp=resp.split('|');
        if(arrResp[0]=='1')
        {
            var ref=arrResp[1];
            generarDocumentoPagoReferenciado(ref);
        }
        else
        {
            msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
        }
    }
    obtenerDatosWeb('../paginasFunciones/funcionesProyectos.php',funcAjax, 'POST','funcion=338&idConcepto='+iC+'&idFormulario='+iF+'&idRegistro='+iR,true);
}

function generarDocumentoPagoReferenciado(ref,objetivo)
{
	var arrParam=[['referencia',ref]];
    if(!objetivo)
	    enviarFormularioDatos('../reportes/generarPagoReferenciado.php',arrParam);
    else
    	enviarFormularioDatos('../reportes/generarPagoReferenciado.php',arrParam,'POST',objetivo);
}


function getAcrobatInfo() 
{
	var getBrowserName = function() 
    					{
    						return this.name = this.name || function() 
                            								{
														    	var userAgent = navigator ? navigator.userAgent.toLowerCase() : "other";
 
      															if(userAgent.indexOf("chrome") > -1)        
                                                                	return "chrome";
                                                                else 
                                                                	if(userAgent.indexOf("safari") > -1)   
                                                                    	return "safari";
                                                                	else 
                                                                    	if(userAgent.indexOf("msie") > -1)     
                                                                        	return "ie";
                                                                		else 
                                                                        	if(userAgent.indexOf("firefox") > -1)  
                                                                            	return "firefox";
                                                                			return userAgent;
                                                              }();
  						};
 	var getActiveXObject = function(name) 
    						{
    							try 
                                { 
                                	return new ActiveXObject(name); 
                                } 
                                catch(e) {}
  							};
 
  var getNavigatorPlugin = function(name) 
  							{
                                for(key in navigator.plugins) 
                                {
                                  	var plugin = navigator.plugins[key];
                                  	if(plugin.name == name) 
                                    	return plugin;
                                }
                             };
 
  var getPDFPlugin = function() 
  					{
    					return this.plugin = this.plugin || function() 
                        									{
											                	if(getBrowserName() == 'ie') 
                                                                {
                                                                    //
                                                                    // load the activeX control
                                                                    // AcroPDF.PDF is used by version 7 and later
                                                                    // PDF.PdfCtrl is used by version 6 and earlier
                                                                    return getActiveXObject('AcroPDF.PDF') || getActiveXObject('PDF.PdfCtrl');
                                                                }
                                                                else 
                                                                {
                                                                  	return getNavigatorPlugin('Adobe Acrobat') || getNavigatorPlugin('Chrome PDF Viewer') || getNavigatorPlugin('WebKit built-in PDF');
                                                                }
    														}();
  					};
 
  var isAcrobatInstalled = 	function() 
  							{
                                return !!getPDFPlugin();
                              };
 
  var getAcrobatVersion = 	function() 
  							{
                                try 
                                {
                                  	var plugin = getPDFPlugin();
                             
                                  	if(getBrowserName() == 'ie') 
                                    {
                                    	var versions = plugin.GetVersions().split(',');
                                    	var latest   = versions[0].split('=');
                                    	return parseFloat(latest[1]);
                                  	}
                             
                                  	if(plugin.version) return parseInt(plugin.version);
                                  		return plugin.name
      
    							}
    							catch(e) 
                                {
                                  return null;
                                }
  							}
 
  
  return 	{
                browser:        getBrowserName(),
                acrobat:        isAcrobatInstalled() ? 'installed' : false,
                acrobatVersion: getAcrobatVersion()
              };
};

function cvTextArea(valor,ignorarRetorno)
{
	//alert(valor);
	valor=valor+'';
	valor=valor.replace(/"/gi,'\\"');
	
	if(ignorarRetorno==undefined)
		valor=valor.replace(/\n/gi, '<br />');
	else
		valor=valor.replace(/\n/gi, '');
	valor=valor.replace(/\r/gi, '');
    valor=valor.replace(/\t/gi, '  ');
	return valor;
}

function escaparEnter(valor)
{
	if(valor)
    {
        if(valor.replace)
        {
            valor=valor.replace(/\r/gi, '');
            valor=valor.replace(/\n/gi, '<br />');
        }
        
  	}
    return valor;
}


function escaparBR(valor,sEnter)
{
	if(valor.replace)
    {

        if(sEnter)
        {
            valor=valor.replace(/<br \/>/gi, '\n');
            valor=valor.replace(/<br>/gi, '\n');
            valor=valor.replace(/<br %2F>/gi, '\n');
            
        }
        else
        {
            valor=valor.replace(/<br \/>/gi, '\n\r');
            valor=valor.replace(/<br>/gi, '\n\r');
             valor=valor.replace(/<br %2F>/gi, '\n\r');
        }
    }
    return valor;
}

function cancelarOperacion(url)
{
	function respQuestion(btn)
    {
    	if(btn=='yes')
        {
        	if(gE('accionCancelar') && (gE('accionCancelar').value!=''))
            {
            	eval(bD(gE('accionCancelar').value));
            }
            else
            {
                if(url)
                {
                    location.href=url;
                }
                else
                {
                    
                    regresarPagina();
                }
            }
       	}
    }
    msgConfirm('Est&aacute; seguro de querer cancelar la operaci&oacute;n?',respQuestion);
}

function irRuta(iConf,url)
{
	var params=[['configuracion',bD(iConf)]];
	enviarFormularioDatos(bD(url),params);
}

function ingresarProcesoIframe(iP)
{
    var content=Ext.getCmp('content');
    if(!content)
    	content=Ext.getCmp('frameContenido');
    content.load({ scripts:true,url:'../modeloProyectos/visorRegistrosProcesos.php',params:{sL:0,idProceso:bD(iP),cPagina:'sFrm=true',idReferencia:-1,idFormulario:-1}});
    
}

function mostrarContacto(iZ)
{
	var obj={};
    obj.url='../principal/contacto.php';
    obj.ancho=650;
    obj.alto=460;
    abrirVentanaFancy(obj);
}


function registrarBitacoraNotificaciones(cadObj,ref1,ref2,ref3)//{"tipoNotificacion":"","respuesta":"","accionEvento":"","datosEvento":"","observaciones":"","comentarios":""}
{
	var motorAjax=crearMotorAjax();
    var datos='funcion=85&cadObj='+cadObj;
    if(ref1)
    	datos+='&referencia1='+ref1;
    if(ref2)
    	datos+='&referencia2='+ref2;
        
    if(ref3)
    	datos+='&referencia3='+ref3;
    if(motorAjax)
    {
        motorAjax.onreadystatechange=function()
        								{
                                        	if(motorAjax.readyState==PETICION_COMPLETO)
                                            {
                                                if(motorAjax.status==RESPUESTA_OK)
                                                {
                                                    
                                                }
                                                else
                                                {
                                                    
                                                }
                                            }
                                        }
                                        
        motorAjax.open("POST","../paginasFunciones/funcionesPortal.php",true);
        motorAjax.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
        motorAjax.send(datos);

    }
    
}


function mostrarMensajeProcesando(msg)
{
	try
	{
    	var lblMesg='Espere por favor...';
        if(msg)
        	lblMesg=msg;
		msgEspereAux=Ext.MessageBox.wait(lblMesg,lblAplicacion);
	}
	catch(err)
	{
		
	}
}

function ocultarMensajeProcesando()
{
	try
	{
    	if(msgEspereAux)
			msgEspereAux.hide();
	}
	catch(err)
	{
		
	}
}

function abrirPaginaFrameCentral(pUrl,iF,nFrame)
{
	var arrParams={cPagina:'sFrm=true'};
    if(iF)
		arrParams={idFormulario:iF,cPagina:'sFrm=true'};
    if(!nFrame)
    {

        gEx('frameContenido').load	(
                                        {
                                            url:pUrl,
                                            params:arrParams,
                                            scripts:true
                                            
                                        }
                                    )   
	}
    else
    {
    	gEx(nFrame).load	(
                                        {
                                            url:pUrl,
                                            params:arrParams,
                                            scripts:true
                                            
                                        }
                                    ) 
    }
}


function abrirFormatoEvaluacion()
{
	var arrDatos=[["idFormulario",318],['idRegistro',-1],['idReferencia',1],['cPagina','sFrm=true']];
    var ventanaAbierta=window.open('',"vAuxiliar2", "toolbar=no,directories=no,menubar=no,status=no,scrollbars=yes,fullscreen=yes");
    enviarFormularioDatosV("../modeloPerfiles/registroFormulario.php",arrDatos,'POST','vAuxiliar2');	

}


function abrirAvisoPrivacidad()
{
	var obj={};
    obj.titulo='Aviso de privacidad';
    obj.url='../principal/privacidad.php';
    obj.ancho=950;
    obj.alto='90%';
    abrirVentanaFancy(obj);
}

function colisionaIntervalo(tI1,tF1,tI2,tF2,cosiderarLimites)
{
	
	var tInicio1=parseFloat(tI1);
    var tFin1=parseFloat(tF1);
    var tInicio2=parseFloat(tI2);
    var tFin2=parseFloat(tF2);
    
    if(cosiderarLimites)
	{
    	if((tInicio1>=tInicio2)&&(tInicio1<=tFin2))
			return true;
		else
			if((tFin1>=tInicio2)&&(tFin1<=tFin2))
				return true;
		
		if((tInicio2>=tInicio1)&&(tInicio2<=tFin1))
			return true;
		else
			if((tFin2>=tInicio1)&&(tFin2<=tFin1))
				return true;
	}
	else
	{
		if((tInicio1>=tInicio2)&&(tInicio1<tFin2))
			return true;
		else
			if((tFin1>tInicio2)&&(tFin1<=tFin2))
				return true;
		
		if((tInicio2>=tInicio1)&&(tInicio2<tFin1))
			return true;
		else
			if((tFin2>tInicio1)&&(tFin2<=tFin1))
				return true;
	}
	return false;
}

function obtenerTipoControl(control)
{
	var tipo=control.nodeName;
	switch(tipo.toLowerCase())
	{
		case 'textarea':
        	return 'textarea';
		case 'input':
			return control.type.toLowerCase();
		break;
		case 'select':
			return 'select';
		break;
	}
}

function invocarEjecucionFuncionIframe(iFrame,funcion,params)
{
	var p='';
    
    if(params)
    {
    	p=params;
    }
    var resp;
	eval(" resp=gEx('"+iFrame+"').getFrameWindow()."+funcion+"("+p+");");
    return resp;
}

function obtenerNavegador() 
{
    
  var userAgent = navigator ? navigator.userAgent.toLowerCase() : "other";
  
  if(userAgent.indexOf("chrome") > -1)        
      return "chrome";
  else 
      if(userAgent.indexOf("safari") > -1)   
          return "safari";
      else 
          if(userAgent.indexOf("msie") > -1)     
              return "ie";
          else 
              if(userAgent.indexOf("firefox") > -1)  
                  return "firefox";
              return userAgent;
                                     
};

function esChrome()
{
	if(obtenerNavegador() =='chrome')
    	return true;
    return false;
}

function esSafari()
{
	if(obtenerNavegador() =='safari')
    	return true;
    return false;
}

function esIE()
{
	if(obtenerNavegador() =='msie')
    	return true;
    return false;
}

function esFirefox()
{
	if(obtenerNavegador() =='firefox')
    	return true;
    return false;
}

function includeScript(archivo) 
{
	var nuevo = document.createElement('script');
	nuevo.setAttribute('type', 'text/javascript');
	nuevo.setAttribute('src', archivo);
	document.getElementsByTagName('head')[0].appendChild(nuevo);
}


function abrirPaginaFancyBox(pUrl,iF)
{
	var obj={};
    obj.ancho='100%';
    obj.alto='100%';
    obj.url=pUrl;
	var arrParams=[['cPagina','sFrm=true']];
    
    
	abrirVentanaFancy(obj); 
}


function abrirPaginaIframe(pUrl,iF)
{
	var arrParams={};
    arrParams.cPagina='sFrm=true';
    if(iF)
		arrParams.idFormulario=iF;
	gEx('frameContenido').load	(
                                    {
                                        url:pUrl,
                                        params:arrParams,
                                        scripts:true
                                        
                                    }
                                )   
}


function ingresarProcesoIframeV2(iP)
{
    var content=Ext.getCmp('content');
    if(!content)
    	content=Ext.getCmp('frameContenido');
        
    content.load({ scripts:true,url:'<?php echo $visorListadoProcesosProcesos?>',params:{sL:0,idProceso:bD(iP),cPagina:'sFrm=true',idReferencia:-1,idFormulario:-1}});
    
}

function actualizarDatosSesion(nSesion,cadDatos)
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
    obtenerDatosWeb('../paginasFunciones/funcionesSistema.php',funcAjax, 'POST','funcion=3&nS='+nSesion+'&cadObj='+cadDatos,true);
}


function obtenerAlturaElemento(id)
{
	var ctrl=gE(id);
    var divHeight=0;
    if(ctrl.offsetHeight)          
   	{
    	divHeight=ctrl.offsetHeight;
    }
	else 
    	if(ctrl.style.pixelHeight)
        {
        	divHeight=ctrl.style.pixelHeight;
        }
        
	return divHeight;        
}


function mostrarGraficoAnalisisOSC()
{
	var obj={};
    obj.ancho='100%';
    obj.alto='100%';
    obj.url='../modulosEspeciales_Censida/gRadarOSC.php';
    abrirVentanaFancy(obj);
    
}

function crearRichTextV2(idCtrl,divDestino,ancho,alto,conf,valor,habilitado)
{

	
	var arrDatosDestino=divDestino.split('_');
	var spEditor=gE('sp_'+idCtrl);
    if(spEditor)
    {
    	spEditor.parentNode.removeChild(spEditor);
    }
    
    
	var div = document.getElementById(divDestino);
    gE('div_'+arrDatosDestino[1]).setAttribute('version2','1');
	var vTexto='';
	if (valor!=undefined)
		vTexto=valor;
	var configuracion="";
    if((conf!='')&&(conf!=undefined))
    	configuracion=conf;
		
	var conf=	{
					width:ancho,
					height:alto,
                    enterMode : CKEDITOR.ENTER_BR,
                    resize_enabled:false,
                    readOnly:(habilitado==0)?true:false,
					customConfig:configuracion
					
				}
	
    
    var spContenedor=cE('span');
    spContenedor.id='sp_'+idCtrl;
    var textArea=cE('textarea');
    textArea.id='txt'+idCtrl;
    spContenedor.appendChild(textArea);
    div.appendChild(spContenedor)		
    
   
            
    var richText=CKEDITOR.replace(textArea.id,conf);
    richText.setData((valor));
    return richText;
}


function deshabilitarTextoEnriquecido(idCtrl)
{
	
	CKEDITOR.instances[idCtrl].setReadOnly(true);
}

function habilitarTextoEnriquecido(idCtrl)
{
	
	CKEDITOR.instances[idCtrl].setReadOnly(false);
}

function obtenerValorTextEnriquecido(idCtrl)
{
	return CKEDITOR.instances[idCtrl].getData();
}

function establecerValorTextEnriquecido(idCtrl,valor)
{
	if(CKEDITOR.instances[idCtrl])
		CKEDITOR.instances[idCtrl].setData(valor);
}

function loadScript(url, callback)
{

	var script = document.createElement("script")
    script.type = "text/javascript";
    if (script.readyState)
    {  //IE
      	script.onreadystatechange = function()
        							{
                                       	if(script.readyState == "loaded" || script.readyState == "complete")
                                        {
                                             script.onreadystatechange = null;
                                             callback();
                                        }
									};
    } 
    else 
    { 
        script.onload = function()
                        {
                            callback();
                        };
    }

 	script.src = url;
	document.getElementsByTagName("head")[0].appendChild(script);
}

function loadCSS(url, callback)
{
	var estiloCSS = document.createElement("link")
    estiloCSS.setAttribute('rel','stylesheet');
    estiloCSS.type = "text/css";
    
    if (estiloCSS.readyState)
    {  
    	estiloCSS.onreadystatechange = function()
        							{
                                       	if(estiloCSS.readyState == "loaded" || estiloCSS.readyState == "complete")
                                        {
                                             estiloCSS.onreadystatechange = null;
                                             callback();
                                        }
									};
    } 
    else 
    { 
        estiloCSS.onload = function()
                        {
                            callback();
                        };
    }

 	estiloCSS.href=url;
	document.getElementsByTagName("head")[0].appendChild(estiloCSS);
    
}

function existeFuncionJS(funcion)
{
	var tipo='';
    eval('tipo=typeof('+funcion+');');
    
    if(tipo=='undefined')
    	return false;
    return true;
}


function visualizarDocumentoAdjuntoB64(iD,e)
{
	mostrarVisorDocumentoProceso(bD(e),bD(iD));
}


function mostrarVisorDocumentoProceso(extension,idDocumento,registro,nombreArchivo)
{
	var obj={};
    obj.url='../visoresGaleriaDocumentos/visorDocumentosGeneral.php';
    obj.ancho='100%';
    obj.alto='100%';
    obj.params=	[['iD',bE('iD_'+idDocumento)],['cPagina','sFrm=true']];
    if(extension!='')
    	obj.params.push(['extension',extension]);
    if(nombreArchivo)
    	obj.params.push(['nombreArchivo',nombreArchivo]);
    abrirVentanaFancy(obj);
	
}


function obtenerDiferenciaDias(fInicio,fTermino)
{
	var totalDias=0;
	var fechaInicio=Date.parseDate(fInicio,'Y-m-d');
	var fTermino=Date.parseDate(fTermino,'Y-m-d');
	while(fechaInicio<=fTermino)
	{
		fechaInicio=fechaInicio.add(Date.DAY,1);
		totalDias++;
	}
	
	return totalDias;
	
}


function mostrarVisorDocumentoProcesoV2(extension,idDocumento,oComp)
{
	var obj={};
    obj.url='../visoresGaleriaDocumentos/visorDocumentosGeneral.php';
    obj.ancho='100%';
    obj.alto='100%';
    obj.params=	[['iD',bE('iD_'+idDocumento)],['cPagina','sFrm=true']];
    
    
    if(oComp)
    {
    	var cadObj='';
    	var o;
    	for(var propiedad in oComp)
    	{
    		o='"'+propiedad+'":"'+oComp[propiedad]+'"';
    		if(cadObj=='')
    			cadObj=o;
    		else
    			cadObj+=','+o;
    	}
    	
    	cadObj='{'+cadObj+'}';
    	
    	obj.params.push(['oComp',bE(cadObj)]);
    }
    abrirVentanaFancy(obj);
	
}

function AES_Encrypt(val)
{

	var i='<?php echo $_SESSION["AES"]["inicio"]?>';
    var f='<?php echo $_SESSION["AES"]["fin"]?>';
    var res='';
    
    retrn(res);
    
    var vAux='';
    var x;
    for(x=0;x<val.length;x++)
    {
    	vAux+=val[x]+String.fromCharCode(generarNumeroAleatorio(65,90));
    }
   
    
    res=bE(i+bE(vAux)+f);
    return (res);
}

function retrn(val)
{


}


function abrirVentanaFancySuperior(obj)
{
	if(window.parent.parent.parent.abrirVentanaFancy)
    	window.parent.parent.parent.abrirVentanaFancy(obj);
   	else
    	if(window.parent.parent.abrirVentanaFancy)
    		window.parent.parent.abrirVentanaFancy(obj); 
        else
            if(window.parent.abrirVentanaFancy)
                window.parent.abrirVentanaFancy(obj);
            else
                abrirVentanaFancy(obj);


}


function delElemCombo(combo,valor)
{
	var x;
    var valorAux=valor+'';
    var valRef;
	for(x=0;x<combo.length;x++)
	{
		valRef=combo.options[x].value+'';
		if(valRef==valorAux)
		{
			combo.options[x]=null;
			break;
		}
	}
}

function insertCss( code ) 
{

    var style = document.createElement('style');
    style.type = 'text/css';

    if (style.styleSheet) {
        // IE
        style.styleSheet.cssText = code;
    } else {
        // Other browsers
        style.innerHTML = code;
    }

    document.getElementsByTagName("head")[0].appendChild( style );
}

function marcarTareaAtendida(iT,iA)
{
	function funcAjax()
    {
        var resp=peticion_http.responseText;
        arrResp=resp.split('|');
        if(arrResp[0]=='1')
        {
        
            window.parent.parent.mostrarTableroNotificaciones(bE(iT));
            
        }
        else
        {
            msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
        }
    }
    obtenerDatosWeb('../paginasFunciones/funcionesModulosEspeciales_SGP.php',funcAjax, 'POST','funcion=152&iT='+(iT)+'&iA='+(iA),true);
}


function colisionaTiempoV2(tI1,tF1,tI2,tF2,cosiderarLimites)
{
	var arrTiempo=tI1.split(' ');
   	var arrFecha=arrTiempo[0].split('-');
    var arrHora=arrTiempo[1].split(':');
    var tInicio1=Date.UTC(parseInt(arrFecha[0]),parseInt(arrFecha[1])-1,parseInt(arrFecha[2]),parseInt(arrHora[0]),parseInt(arrHora[1]),parseInt(arrHora[2]));
	
    arrTiempo=tF1.split(' ');
   	arrFecha=arrTiempo[0].split('-');
    arrHora=arrTiempo[1].split(':');
    var tFin1=Date.UTC(parseInt(arrFecha[0]),parseInt(arrFecha[1])-1,parseInt(arrFecha[2]),parseInt(arrHora[0]),parseInt(arrHora[1]),parseInt(arrHora[2]));
	
    arrTiempo=tI2.split(' ');
   	arrFecha=arrTiempo[0].split('-');
    arrHora=arrTiempo[1].split(':');
    var tInicio2=Date.UTC(parseInt(arrFecha[0]),parseInt(arrFecha[1])-1,parseInt(arrFecha[2]),parseInt(arrHora[0]),parseInt(arrHora[1]),parseInt(arrHora[2]));
	
    arrTiempo=tF2.split(' ');
   	arrFecha=arrTiempo[0].split('-');
    arrHora=arrTiempo[1].split(':');
    var tFin2=Date.UTC(parseInt(arrFecha[0]),parseInt(arrFecha[1])-1,parseInt(arrFecha[2]),parseInt(arrHora[0]),parseInt(arrHora[1]),parseInt(arrHora[2]));
	
    if(cosiderarLimites)
	{
    	if((tInicio1>=tInicio2)&&(tInicio1<=tFin2))
        {
        	
        	return true;
		}
        else
		{
        	if((tFin1>=tInicio2)&&(tFin1<=tFin2))
            {
            	
				return true;
			}
        }
        
		if((tInicio2>=tInicio1)&&(tInicio2<=tFin1))
		{
        	return true;
		}
        else
		{
        	if((tFin2>=tInicio1)&&(tFin2<=tFin1))
				return true;
		}
    }
	else
	{
    	
		if((tInicio1>=tInicio2)&&(tInicio1<tFin2))
		{
			
			
            return true;
		}
        else
        {
			if((tFin1>tInicio2)&&(tFin1<=tFin2))
            {

				return true;
			}
        }
        
		if((tInicio2>=tInicio1)&&(tInicio2<tFin1))
		{

        	return true;
		}
        else
		{
        	if((tFin2>tInicio1)&&(tFin2<=tFin1))
            {
				return true;
			}
        }
    }
	return false;
}


function obtenerPosicionScroll()
{
	var scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    return scrollTop;
}


function crearControlUploadHTML5(objConf)
{
	
	var llavePropiedad;
	var arrExtencionesAgrupadas={};
	var extension;
    var x=0;
	var arrExtensionesPermitidas=[];

	if(objConf.file_types!='*.*')
    {
    	var objExtension='';
    	var aExtensiones=objConf.file_types.split(';');
        
        for(x=0;x<aExtensiones.length;x++)
        {
        	extension=aExtensiones[x].toLowerCase().replace("*.","");
            
        	var pos=existeValorMatriz(arrExtensionesComunes,extension,1);
        	if(pos!=-1)
            {
            	llavePropiedad=arrExtensionesComunes[pos][0].replace(/\s/gi,'__');
                
            	if(typeof(arrExtencionesAgrupadas[llavePropiedad])=='undefined')
                {
                	
                	arrExtencionesAgrupadas[llavePropiedad]=[];
                }
                
                arrExtencionesAgrupadas[llavePropiedad].push(arrExtensionesComunes[pos][1].trim());
            	
            
            }
            else
            {
            	objExtension={};
                objExtension.title='Documento '+extension.toUpperCase();
                objExtension.extensions=extension.trim();
                arrExtensionesPermitidas.push(objExtension);
            }
        
        	
        }
    }
    
    for(llavePropiedad in arrExtencionesAgrupadas)
    {
    	objExtension={};
        objExtension.title=llavePropiedad.replace(/__/gi,' ');
        objExtension.extensions='';
       
    	for(x=0;x<arrExtencionesAgrupadas[llavePropiedad].length;x++)
        {
        	if(objExtension.extensions=='')
            	objExtension.extensions=arrExtencionesAgrupadas[llavePropiedad][x].trim();
            else
            	objExtension.extensions+=','+arrExtencionesAgrupadas[llavePropiedad][x].trim();
        }
         arrExtensionesPermitidas.push(objExtension);
    }
    
    
	$("#uploader").pluploadQueue({
                                    
                                      runtimes : 'html5,html4',
                                      container:gE('containerUploader'),
                                      useUpdateV2:true,
                                      leyendaSeleccione:objConf.leyendaSeleccione?objConf.leyendaSeleccione:'Seleccione documento',
                                      url : objConf.upload_url,
                                      prevent_duplicates:true,
                                      file_data_name:objConf.file_post_name,
                                      multiple_queues:true,
                                      multi_selection:false,
                                      max_retries:10,                                     
                                      rename : true,
                                      dragdrop: true,
                                      init:	{	
                                      
                                                  Init:function(up) 
                                                          {
                                                              	uploadControl=up;
                                                            
                                                          },
                                                  
                                                  FilesAdded: function(up, files) {
                                                  										
                                                                                      $(".plupload_add").hide();
                                                                                      up.files.splice(1,up.files.length-1);   
                                                                                  },
                                                  FilesRemoved: function(up, files) {
                                                                                      if(up.files.length==0)
                                                                                          $(".plupload_add").show();
                                                                                      
                                                                                  },
                                                  
                                                  UploadComplete:function(up,archivos)
                                                                  {
                                                                      gEx('btnUploadFile').enable();
                                                                  },
                                                  FileUploaded:function(up,archivos,response)
                                                                  {
                                                                  		oE('filaAvance');
                                                                      	objConf.upload_success_handler(up,response.response)
                                                                      
                                                                  }
                                              },
                                      filters : 	{
                                                      
                                                      max_file_size : objConf.file_size_limit.replace(/\s/gi,'').toLowerCase(),
                                                      mime_types: arrExtensionesPermitidas
                                                  },
                               
                                     
                                      flash_swf_url : '../Scripts/plupload/js/Moxie.swf',
                                      silverlight_xap_url : '../Scripts/plupload/js/Moxie.xap'
                                  });
    
                                            
      
      setTimeout(function()
      			{
                	
      			 	gE('ID_plupload_droptext').style='line-height: 23px;padding:0px'; 
                 	$(".plupload_start").hide(); 
                  	$("#uploader_filelist").height('20px');  
                  	$("#uploader_filelist").width((objConf.ancho?objConf.ancho:'280')+'px');  
                  	$("#uploader_filelist").css("line-height", '15px !important');
                    $(".plupload_filelist_header").hide();
                    $(".plupload_filelist_footer").hide();
                   	gE('uploader_filelist').style.overflowY='hidden';
                    if(objConf.afterDrawHandler)
                    {	
                    	setTimeout(function()
      					{
                    		objConf.afterDrawHandler();
                    	},objConf.timeAfterDrawHandler?objConf.timeAfterDrawHandler:100);
                    }
                    
                 }, objConf.timeAjustControl? objConf.timeAjustControl:100);
      
      
}



<?php
	if(isset($arrHojasEstilo) && sizeof($arrHojasEstilo)>0)
	{
		foreach($arrHojasEstilo as $e)
		{
			echo "loadCSS('".$e."',function(){})\r\n";
		}
	}
?>
