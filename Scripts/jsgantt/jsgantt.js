var JSGantt; if (!JSGantt) JSGantt = {};

var vTimeout = 0;
var vBenchTime = new Date().getTime();
var listaTareas;

JSGantt.TaskItem = function(pID, pName, pStart, pEnd, pColor, pLink, pMile, pRes, pComp, pGroup, pParent, pOpen, pDepend, pCaption,pAgregar,pEliminar,pHoras)
{
	
	var vID    = pID;
	var vName  = pName;
	var vStart = new Date();	
	var vEnd   = new Date();
	var vColor = pColor;
	var vLink  = pLink;
	var vMile  = pMile;
	var vRes   = pRes;
	var vComp  = pComp;
	var vGroup = pGroup;
	var vParent = pParent;
	var vOpen   = pOpen;
	var vDepend = pDepend;
	var vCaption = pCaption;
	var vAgregar= false;
	var vEliminar=false;
	var vHoras=pHoras;
	var vDuration = '';
	var vLevel = 0;
	var vNumKid = 0;
	var vVisible  = 1;
	if(pAgregar!=undefined)
		vAgregar=pAgregar;
	if(pEliminar!=undefined)
		vEliminar=pEliminar;
	var x1, y1, x2, y2;
	//if (vGroup != 1)
	{  
	   vStart = JSGantt.parseDateStr(pStart,g.getDateInputFormat());
	   vEnd   = JSGantt.parseDateStr(pEnd,g.getDateInputFormat());
	}
	this.getID       = function(){ return vID };
    this.getName     = function(){ return vName };
    this.getStart    = function(){ return vStart};
	this.getEnd      = function(){ return vEnd  };
    this.getColor    = function(){ return vColor};
    this.getLink     = function(){ return vLink };
    this.getMile     = function(){ return vMile };
    this.getDepend   = function(){ if(vDepend) return vDepend; else return null };
    this.getCaption  = function(){ if(vCaption) return vCaption; else return ''; };
	this.getAgregar  = function(){ return vAgregar;}
	this.getEliminar  = function(){ return vEliminar;}
	this.getResource = function(){ if(vRes) return vRes; else return '&nbsp';  };
	this.getCompVal  = function(){ if(vComp) return vComp; else return 0; };
	this.getCompStr  = function(){ if(vComp) return vComp+'%'; else return ''; };
	this.getDuration = function(vFormat)
						{ 
							 if (vMile) 
								vDuration = '-';
								else 
								if (vFormat=='hour')
								{
									tmpPer =  Math.ceil((this.getEnd() - this.getStart()) /  ( 60 * 60 * 1000) );
									if(tmpPer == 1)  
										vDuration = '1 Hora';
									else
										vDuration = tmpPer + ' Horas';
								}
								
								else 
									if (vFormat=='minute')
									{
										tmpPer =  Math.ceil((this.getEnd() - this.getStart()) /  ( 60 * 1000) );
										if(tmpPer == 1)  
											vDuration = '1 Minuto';
										else
											vDuration = tmpPer + ' Minutos';
									}
								
									else 
									{ 
										tmpPer =  Math.ceil((this.getEnd() - this.getStart()) /  (24 * 60 * 60 * 1000) + 1);
										if(tmpPer == 1)  vDuration = '1 D&iacute;a';
										else             vDuration = tmpPer + ' Dias';
									 }
					
							 
									return( vDuration )
						  };
	this.getHoras	= function(){ return vHoras}
	this.getParent   = function(){ return vParent };
	this.getGroup    = function(){ return vGroup };
	this.getOpen     = function(){ return vOpen };
	this.getLevel    = function(){ return vLevel };
	this.getNumKids  = function(){ return vNumKid };
	this.getStartX   = function(){ return x1 };
	this.getStartY   = function(){ return y1 };
	this.getEndX     = function(){ return x2 };
	this.getEndY     = function(){ return y2 };
	this.getVisible  = function(){ return vVisible };
	this.setDepend   = function(pDepend){ vDepend = pDepend;};
	this.setStart    = function(pStart){ vStart = pStart;};
	this.setEnd      = function(pEnd)  { vEnd   = pEnd;  };
	this.setLevel    = function(pLevel){ vLevel = pLevel;};
	this.setNumKid   = function(pNumKid){ vNumKid = pNumKid;};
	this.setCompVal  = function(pCompVal){ vComp = pCompVal;};
	this.setStartX   = function(pX) {x1 = pX; };
	this.setStartY   = function(pY) {y1 = pY; };
	this.setEndX     = function(pX) {x2 = pX; };
	this.setEndY     = function(pY) {y2 = pY; };
	this.setOpen     = function(pOpen) {vOpen = pOpen; };
	this.setVisible  = function(pVisible) {vVisible = pVisible; };
	this.setAgregar  = function(valor){ vAgregar=valor;}
	this.setEliminar  = function(valor){ vEliminar=valor;}
	this.setHoras	 = function(valor){vHoras=valor}
};
	
JSGantt.GanttChart =  function(pGanttVar, pDiv, pFormat)
{
	
	var vGanttVar = pGanttVar;
	var vDiv      = pDiv;
	var vFormat   = pFormat;
	var vShowRes  = 1;
	var vShowDur  = 1;
	var vShowComp = 1;
	var vShowStartDate = 1;
	var vShowEndDate = 1;
	var vDateInputFormat = "mm/dd/yyyy";
	var vDateDisplayFormat = "mm/dd/yy";
	var vNumUnits  = 0;
	var vCaptionType;
	var vDepId = 1;
	var vTaskList     = new Array();	
	var vFormatArr	= new Array("day","week","month","quarter");
	var vQuarterArr   = new Array('Enero - Febrero - Marzo','Enero - Febrero - Marzo','Enero - Febrero - Marzo','Abril - Mayo - Junio','Abril - Mayo - Junio','Abril - Mayo - Junio','Julio - Agosto - Septiembre','Julio - Agosto - Septiembre','Julio - Agosto - Septiembre','Octubre - Noviembre - Diciembre','Octubre - Noviembre - Diciembre','Octubre - Noviembre - Diciembre');
	var vMonthDaysArr = new Array(31,28,31,30,31,30,31,31,30,31,30,31);
	var vMonthArr     = new Array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octobre","Noviembre","Diciembre");
	this.setFormatArr = function() 	 
						{
							vFormatArr = new Array();
							for(var i = 0; i < arguments.length; i++) 
							{
								vFormatArr[i] = arguments[i];
							}
							if(vFormatArr.length>4)
							{
								vFormatArr.length=4;
							}
						};
	this.setShowRes  = function(pShow) { vShowRes  = pShow; };
	this.setShowDur  = function(pShow) { vShowDur  = pShow; };
	this.setShowComp = function(pShow) { vShowComp = pShow; };
	this.setShowStartDate = function(pShow) { vShowStartDate = pShow; };
	this.setShowEndDate = function(pShow) { vShowEndDate = pShow; };
	this.setDateInputFormat = function(pShow) { vDateInputFormat = pShow; };
	this.setDateDisplayFormat = function(pShow) { vDateDisplayFormat = pShow; };
	this.setCaptionType = function(pType) { vCaptionType = pType };
	this.setFormat = 	function(pFormat)
						{ 
							 vFormat = pFormat; 
							 this.Draw(); 
						};
	this.getShowRes  = function(){ return vShowRes };
	this.getShowDur  = function(){ return vShowDur };
	this.getShowComp = function(){ return vShowComp };
	this.getShowStartDate = function(){ return vShowStartDate };
	this.getShowEndDate = function(){ return vShowEndDate };
	this.getDateInputFormat = function() { return vDateInputFormat };
	this.getDateDisplayFormat = function() { return vDateDisplayFormat };
	this.getCaptionType = function() { return vCaptionType };
	this.CalcTaskXY = function () 
					  {
						 var vList = this.getList();
						 var vTaskDiv;
						 var vParDiv;
						 var vLeft, vTop, vHeight, vWidth;
				
						 for(i = 0; i < vList.length; i++)
						 {
							vID = vList[i].getID();
							vTaskDiv = document.getElementById("taskbar_"+vID);
							vBarDiv  = document.getElementById("bardiv_"+vID);
							vParDiv  = document.getElementById("childgrid_"+vID);
				
							if(vBarDiv) {
							   vList[i].setStartX( vBarDiv.offsetLeft );
							   vList[i].setStartY( vParDiv.offsetTop+vBarDiv.offsetTop+6 );
							   vList[i].setEndX( vBarDiv.offsetLeft + vBarDiv.offsetWidth );
							   vList[i].setEndY( vParDiv.offsetTop+vBarDiv.offsetTop+6 );
							};
						 };
					  };
	this.AddTaskItem = function(value)
					  {
						 vTaskList.push(value);
					  };
	this.getList   = function() { return vTaskList };
	this.clearDependencies = function()
							  {
									var parent = document.getElementById('rightside');
									var depLine;
									var vMaxId = vDepId;
									for ( i=1; i<vMaxId; i++ ) 
									{
										depLine = document.getElementById("line"+i);
										if (depLine) { parent.removeChild(depLine); }
									};
									vDepId = 1;
							  };
	this.sLine = function(x1,y1,x2,y2) 
				{
						 vLeft = Math.min(x1,x2);
						 vTop  = Math.min(y1,y2);
						 vWid  = Math.abs(x2-x1) + 1;
						 vHgt  = Math.abs(y2-y1) + 1;
						 vDoc = document.getElementById('rightside');
						 var oDiv = document.createElement('div');
						 oDiv.id = "line"+vDepId++;
							 oDiv.style.position = "absolute";
						 oDiv.style.margin = "0px";
						 oDiv.style.padding = "0px";
						 oDiv.style.overflow = "hidden";
						 oDiv.style.border = "0px";
						 oDiv.style.zIndex = 0;
						 oDiv.style.backgroundColor = "red";
						 oDiv.style.left = vLeft + "px";
						 oDiv.style.top = vTop + "px";
						 oDiv.style.width = vWid + "px";
						 oDiv.style.height = vHgt + "px";
						 oDiv.style.visibility = "visible";
						 vDoc.appendChild(oDiv);
				};
	this.dLine = function(x1,y1,x2,y2) 
				{
					var dx = x2 - x1;
					var dy = y2 - y1;
					var x = x1;
					var y = y1;
					
					var n = Math.max(Math.abs(dx),Math.abs(dy));
					dx = dx / n;
					dy = dy / n;
					for ( i = 0; i <= n; i++ )
					{
					  vx = Math.round(x); 
					  vy = Math.round(y);
					  this.sLine(vx,vy,vx,vy);
					  x += dx;
					  y += dy;
					};
				
				};
	
	this.drawDependency =function(x1,y1,x2,y2)
						  {
							 if(x1 + 10 < x2)
							 { 
								this.sLine(x1,y1,x1+4,y1);
								this.sLine(x1+4,y1,x1+4,y2);
								this.sLine(x1+4,y2,x2,y2);
								this.dLine(x2,y2,x2-3,y2-3);
								this.dLine(x2,y2,x2-3,y2+3);
								this.dLine(x2-1,y2,x2-3,y2-2);
								this.dLine(x2-1,y2,x2-3,y2+2);
							 }
							 else
							 {
								this.sLine(x1,y1,x1+4,y1);
								this.sLine(x1+4,y1,x1+4,y2-10);
								this.sLine(x1+4,y2-10,x2-8,y2-10);
								this.sLine(x2-8,y2-10,x2-8,y2);
								this.sLine(x2-8,y2,x2,y2);
								this.dLine(x2,y2,x2-3,y2-3);
								this.dLine(x2,y2,x2-3,y2+3);
								this.dLine(x2-1,y2,x2-3,y2-2);
								this.dLine(x2-1,y2,x2-3,y2+2);
							 }
						  };
	this.DrawDependencies = function () 
	{
		this.CalcTaskXY();
		this.clearDependencies();
		var vList = this.getList();
		for(var i = 0; i < vList.length; i++)
		{
		  vDepend = vList[i].getDepend();
		  if(vDepend) 
		  {
		
			 var vDependStr = vDepend + '';
			 var vDepList = vDependStr.split(',');
			 var n = vDepList.length;
		
			 for(var k=0;k<n;k++) {
				var vTask = this.getArrayLocationByID(vDepList[k]);
		
				if(vList[vTask].getVisible()==1)
				   this.drawDependency(vList[vTask].getEndX(),vList[vTask].getEndY(),vList[i].getStartX()-1,vList[i].getStartY())
			 }
		  }
		}
	};
	this.getArrayLocationByID = function(pId)  
	{
		var vList = this.getList();
		for(var i = 0; i < vList.length; i++)
		{
		  if(vList[i].getID()==pId)
			 return i;
		}
	};
	
	
	this.Draw = function()
				{
					var vMaxDate = new Date();
					var vMinDate = new Date();	
					var vTmpDate = new Date();
					var vNxtDate = new Date();
					var vPriorDate=new Date();
					var vCurrDate = new Date();
					var vTaskLeft = 0;
					var vTaskRight = 0;
					var vNumCols = 0;
					var vID = 0;
					var vMainTable = "";
					var vLeftTable = "";
					var vRightTable = "";
					var vDateRowStr = "";
					var vItemRowStr = "";
					var vColWidth = 0;
					var vColUnit = 0;
					var vChartWidth = 0;
					var vNumDays = 0;
					var vDayWidth = 0;
					var vStr = "";
					var vNameWidth = 240;	
					var vStatusWidth = 75;
					var vLeftWidth = 15 + vNameWidth + (vStatusWidth*5);
					var colorGrupo='C00';
					var alturaFila=19;
					if(Ext.isChrome)
						alturaFila=21;
					if(Ext.isIE)
						alturaFila=20.5;
					if(vTaskList.length > 0)
					{
					   JSGantt.processRows(vTaskList, 0, -1, 1, 1);
					  
					   vMinDate = JSGantt.getMinDate(vTaskList, vFormat);
					   vMaxDate = JSGantt.getMaxDate(vTaskList, vFormat);
					  
					   if(vFormat == 'day') 
					   {
						  vColWidth = 25;
						  vColUnit = 1;
					   }
					   else 
						   if(vFormat == 'week') 
						   {
							  vColWidth = 140;
							  vColUnit = 7;
						   }
						   else 
							   if(vFormat == 'month') 
							   {
								  vColWidth = 130;
								  vColUnit = 30;
							   }
							   else 
								   if(vFormat == 'quarter') 
								   {
									  vColWidth = 230;
									  vColUnit = 90;
								   }
								   else 
									   if(vFormat=='hour')
									   {
										  vColWidth = 18;
										  vColUnit = 1;
									   }
									   else 
										   if(vFormat=='minute')
										   {
											  vColWidth = 18;
											  vColUnit = 1;
										   }
					   
					   vNumDays = (Date.parse(vMaxDate) - Date.parse(vMinDate)) / ( 24 * 60 * 60 * 1000);
					   vNumUnits = vNumDays / vColUnit;
					   vChartWidth = vNumUnits * vColWidth + 1;
					   vDayWidth = (vColWidth / vColUnit) + (1/vColUnit);
					   vMainTable =
						  '<TABLE id=theTable cellSpacing=0 cellPadding=0 border=0>'+
						  '<TBODY>'+
						  	'<TR >' +
						  		'<TD vAlign=top bgColor=#ffffff>';
					   if(vShowRes !=1) 
					   		vNameWidth+=vStatusWidth;
					   if(vShowDur !=1) 
					   		vNameWidth+=vStatusWidth;
					   if(vShowComp!=1) 
					   		vNameWidth+=vStatusWidth;
						 if(vShowStartDate!=1) 
						 	vNameWidth+=vStatusWidth;
						 if(vShowEndDate!=1) 
						 	vNameWidth+=vStatusWidth;
					  
					   vLeftTable =
						  '<DIV class=scroll id=leftside style="width:' + vLeftWidth + 'px">'+
						  '<TABLE cellSpacing=0 cellPadding=0 border=0>'+
						  '<TBODY>' +
						  '<TR style="HEIGHT: 17px; background-image: url(../images/);">' +
						 	'  <TD  colspan="7" style="HEIGHT: 17px"><span class="letraRojaSubrayada8">Ver programa de trabajo por:</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
						 if (vFormatArr.join().indexOf("minute")!=-1) 
					  { 
						  if (vFormat=='minute') 
							  vLeftTable += '<INPUT TYPE=RADIO NAME="radFormat" VALUE="minute" checked><span class="copyrigth">&nbsp;Minuto&nbsp;</span>&nbsp;&nbsp;&nbsp;';
						  else
							  vLeftTable += '<INPUT TYPE=RADIO NAME="radFormat" onclick=JSGantt.changeFormat("minute",'+vGanttVar+'); VALUE="minute"><span class="copyrigth">Minuto&nbsp;</span>&nbsp;&nbsp;&nbsp;';
					  }
					  
					  if (vFormatArr.join().indexOf("hour")!=-1) 
					  { 
						  if (vFormat=='hour') 
							  vLeftTable += '<INPUT TYPE=RADIO NAME="radFormat" VALUE="hour" checked><span class="copyrigth">&nbsp;Hora&nbsp;&nbsp;&nbsp;&nbsp;</span>';
						  else                
							  vLeftTable += '<INPUT TYPE=RADIO NAME="radFormat" onclick=JSGantt.changeFormat("hour",'+vGanttVar+'); VALUE="hour"><span class="copyrigth">&nbsp;Hora</span>&nbsp;&nbsp;&nbsp;&nbsp;';
					  }
					  
					  if (vFormatArr.join().indexOf("day")!=-1) 
					  { 
						  if (vFormat=='day') 
							  vLeftTable += '<INPUT TYPE=RADIO NAME="radFormat" VALUE="day" checked><span class="copyrigth">&nbsp;D&iacute;a</span>&nbsp;&nbsp;&nbsp;&nbsp;';
						  else                
							  vLeftTable += '<INPUT TYPE=RADIO NAME="radFormat" onclick=JSGantt.changeFormat("day",'+vGanttVar+'); VALUE="day"><span class="copyrigth">&nbsp;D&iacute;a</span>&nbsp;&nbsp;&nbsp;&nbsp;';
					  }
					  
					  if (vFormatArr.join().indexOf("week")!=-1) 
					  { 
						  if (vFormat=='week') 
							  vLeftTable += '<INPUT TYPE=RADIO NAME="radFormat" VALUE="week" checked><span class="copyrigth">&nbsp;Semana</span>&nbsp;&nbsp;&nbsp;&nbsp;';
						  else                
							  vLeftTable += '<INPUT TYPE=RADIO NAME="radFormat" onclick=JSGantt.changeFormat("week",'+vGanttVar+') VALUE="week"><span class="copyrigth">&nbsp;Semana</span>&nbsp;&nbsp;&nbsp;&nbsp;';
					  }
					  
					  if (vFormatArr.join().indexOf("month")!=-1) 
					  { 
						  if (vFormat=='month') 
							  vLeftTable += '<INPUT TYPE=RADIO NAME="radFormat" VALUE="month" checked><span class="copyrigth">&nbsp;Mes</span>&nbsp;&nbsp;&nbsp;&nbsp;';
						  else                
							  vLeftTable += '<INPUT TYPE=RADIO NAME="radFormat" onclick=JSGantt.changeFormat("month",'+vGanttVar+') VALUE="month"><span class="copyrigth">&nbsp;Mes</span>&nbsp;&nbsp;&nbsp;&nbsp;';
					  }
					  
					  if (vFormatArr.join().indexOf("quarter")!=-1) 
					  { 
						  if (vFormat=='quarter') 
							  vLeftTable += '<INPUT TYPE=RADIO NAME="radFormat" VALUE="quarter" checked><span class="copyrigth">&nbsp;Trimestre</span>&nbsp;&nbsp;&nbsp;&nbsp;';
						  else                
							  vLeftTable += '<INPUT TYPE=RADIO NAME="radFormat" onclick=JSGantt.changeFormat("quarter",'+vGanttVar+') VALUE="quarter"><span class="copyrigth">&nbsp;Trimestre</span>&nbsp;&nbsp;&nbsp;&nbsp;';
					  }
					  
						  						
					   vLeftTable +=
						  '</td></TR>'+
						  '<TR style="HEIGHT: 20px; " class="celdaImagenAzul1">' +
							  '  <TD style="BORDER-TOP: #efefef 1px solid; WIDTH: 15px; HEIGHT: 20px"></TD>' +
							  '  <TD style="BORDER-TOP: #efefef 1px solid; WIDTH: ' + vNameWidth + 'px; HEIGHT: 20px" align="center"><NOBR><span class="corpo8_bold">Actividad/Tarea</span></NOBR></TD>' ;
					
					   if(vShowRes ==1) 
					   		vLeftTable += '  <TD style="BORDER-TOP: #efefef 1px solid; FONT-SIZE: 12px; BORDER-LEFT: #efefef 1px solid; WIDTH: '+vStatusWidth+'px; HEIGHT: 20px" align=center nowrap><span class="corpo8_bold">Recursor</span></TD>' ;
					   if(vShowDur ==1) 
					   		vLeftTable += '  <TD style="BORDER-TOP: #efefef 1px solid; FONT-SIZE: 12px; BORDER-LEFT: #efefef 1px solid; WIDTH: '+vStatusWidth+'px; HEIGHT: 20px" align=center nowrap><span class="corpo8_bold">Horas</span></TD>' ;
					   if(vShowComp==1) 
					   		vLeftTable += '  <TD style="BORDER-TOP: #efefef 1px solid; FONT-SIZE: 12px; BORDER-LEFT: #efefef 1px solid; WIDTH: '+vStatusWidth+'px; HEIGHT: 20px" align=center nowrap><span class="corpo8_bold">% </span></TD>' ;
					   if(vShowStartDate==1) 
					   		vLeftTable += '  <TD style="BORDER-TOP: #efefef 1px solid; FONT-SIZE: 12px; BORDER-LEFT: #efefef 1px solid; WIDTH: '+vStatusWidth+'px; HEIGHT: 20px" align=center nowrap><span class="corpo8_bold">F. Inicio<span></TD>' ;
					   if(vShowEndDate==1) 
					   		vLeftTable += '  <TD style="BORDER-TOP: #efefef 1px solid; FONT-SIZE: 12px; BORDER-LEFT: #efefef 1px solid; WIDTH: '+vStatusWidth+'px; HEIGHT: 20px" align=center nowrap><span class="corpo8_bold">F. Fin</span></TD>' ;
					
					   vLeftTable += '</TR>';
					   
					  for(i = 0; i < vTaskList.length; i++)
					  {
						 if( vTaskList[i].getGroup()) 
						 {
							vBGColor = "f3f3f3";
							vRowType = "group";
						 } 
						 else 
						 {
							vBGColor  = "ffffff";
							vRowType  = "row";
						 }
						 
						 vID = vTaskList[i].getID();
					  
						 if(vTaskList[i].getVisible() == 0) 
							  vLeftTable += '<TR id="child_' + vID + '" style="display:none;background-color:#'+vBGColor+' !important"  onMouseover=g.mouseOver(this,"' + vID + '","left","' + vRowType + '") onMouseout=g.mouseOut(this,\'' + vID + '\',"left","' + vRowType + '")>' ;
						 else
							  vLeftTable += '<TR id="child_' + vID + '" style="background-color:#'+vBGColor+' !important"  onMouseover=g.mouseOver(this,"' + vID + '","left","' + vRowType + '") onMouseout=g.mouseOut(this,\'' + vID + '\',"left","' + vRowType + '")>' ;
						vLeftTable += 
									'  <TD class=gdatehead style="WIDTH: 15px; HEIGHT: 20px; BORDER-TOP: #efefef 1px solid; FONT-SIZE: 12px; BORDER-LEFT: #efefef 1px solid;">&nbsp;</TD>' +
									'  <TD align="left" class=gname style="WIDTH: ' + vNameWidth + 'px; HEIGHT: 20px; BORDER-TOP: #efefef 1px solid; FONT-SIZE: 12px;" nowrap><NOBR><span style="color: #aaaaaa">';
					  	
						 for(j=1; j<vTaskList[i].getLevel(); j++) 
						 {
							 
							vLeftTable += '&nbsp&nbsp&nbsp&nbsp';
						 }
						 vLeftTable += '</span>';
					  
						 if( vTaskList[i].getGroup()) 
						 {
							if( vTaskList[i].getOpen() == 1) 
							   vLeftTable += '<SPAN id="group_' + vID + '"  estado="1"  style="color:#000000; cursor:pointer; font-weight:bold; FONT-SIZE: 12px;vertical-align:middle !important" onclick="JSGantt.folder(\'' + vID + '\','+vGanttVar+');'+vGanttVar+'.DrawDependencies();" ><img src="../images/verMenos.png"></span><span style="color:#000000">&nbsp</SPAN>' ;
							else
							   vLeftTable += '<SPAN id="group_' + vID + '" estado="0" style="color:#000000; cursor:pointer; font-weight:bold; FONT-SIZE: 12px;vertical-align:middle !important" onclick="JSGantt.folder(\'' + vID + '\','+vGanttVar+');'+vGanttVar+'.DrawDependencies();"><img src="../images/verMas.gif"></span><span style="color:#000000">&nbsp</SPAN>' ;
						   
						 } 
						 else 
						 {
							vLeftTable += '<span style="color: #000000; font-weight:bold; FONT-SIZE: 12px;">&nbsp;&nbsp;&nbsp;&nbsp&nbsp&nbsp</span>';
						 }
					 	var btnAgregar="";
						var btnEliminar="";
						
						if(vTaskList[i].getAgregar())
					  		btnAgregar='&nbsp;&nbsp;<a href="javascript:agregarActividadHija(\''+vID+'\')"><img src="../images/add.png" alt="Agregar actividad hija" title="Agregar actividad hija"></a>';
						if(vTaskList[i].getEliminar())
					  		btnEliminar='<a href="javascript:removerActividad(\''+vID+'\')"><img src="../images/delete.png" title="Remover actividad" alt="Remover actividad"></a>';						
						btnAccion=btnAgregar+'&nbsp;'+btnEliminar;
						vLeftTable += 
									  '<span onclick=JSGantt.taskLink("' + vTaskList[i].getLink() + '",300,200); style="cursor:pointer"><span class="corpo7"> ' + vTaskList[i].getName() + '</span></span>'+btnAccion+'</NOBR></TD>' ;
					  	
						var hora=vTaskList[i].getHoras();
						if(hora<0)
						{
							hora*=-1;
							hora=hora+' <span alt="No se contabilizan"><font color="red">*</font></span>';
						}
						
						 if(vShowRes ==1) 
						 	vLeftTable += '  <TD class=gname style="WIDTH: 60px; HEIGHT: 20px; TEXT-ALIGN: center; BORDER-TOP: #efefef 1px solid; FONT-SIZE: 12px; BORDER-LEFT: #efefef 1px solid;" align=center><NOBR><span class="corpo7Simple">' + vTaskList[i].getResource() + '</span></NOBR></TD>' ;
						 if(vShowDur ==1) 
						 	vLeftTable += '  <TD class=gname style="WIDTH: 60px; HEIGHT: 20px; TEXT-ALIGN: right; BORDER-TOP: #efefef 1px solid; FONT-SIZE: 12px; BORDER-LEFT: #efefef 1px solid;" ><NOBR><span class="corpo7Simple">' + hora + '</span></NOBR>&nbsp;&nbsp;</TD>' ;
						 if(vShowComp==1) 
						 	vLeftTable += '  <TD class=gname style="WIDTH: 60px; HEIGHT: 20px; TEXT-ALIGN: center; BORDER-TOP: #efefef 1px solid; FONT-SIZE: 12px; BORDER-LEFT: #efefef 1px solid;" align=center><NOBR><span class="corpo7Simple">' + vTaskList[i].getCompStr()  + '</span></NOBR></TD>' ;
						 if(vShowStartDate==1) 
						 	vLeftTable += '  <TD class=gname style="WIDTH: 60px; HEIGHT: 20px; TEXT-ALIGN: center; BORDER-TOP: #efefef 1px solid; FONT-SIZE: 12px; BORDER-LEFT: #efefef 1px solid;" align=center><NOBR><span class="corpo7Simple">' + JSGantt.formatDateStr( vTaskList[i].getStart(), vDateDisplayFormat) + '</span></NOBR></TD>' ;
						 if(vShowEndDate==1) 
						 	vLeftTable += '  <TD class=gname style="WIDTH: 60px; HEIGHT: 20px; TEXT-ALIGN: center; BORDER-TOP: #efefef 1px solid; FONT-SIZE: 12px; BORDER-LEFT: #efefef 1px solid;" align=center><NOBR><span class="corpo7Simple">' + JSGantt.formatDateStr( vTaskList[i].getEnd(), vDateDisplayFormat) + '</span></NOBR></TD>' ;
						 vLeftTable += '</TR>';
					  
					  }
					  vLeftTable +=  '<TR style="HEIGHT: 20px; ">' +
							  '  <TD style="BORDER-TOP: #efefef 1px solid; WIDTH: 15px; HEIGHT: 20px"></TD>' +
							  '  <TD style="BORDER-TOP: #efefef 1px solid; WIDTH: ' + vNameWidth + 'px; HEIGHT: 20px" align="center"><NOBR><span class="corpo8_bold"></span></NOBR></TD>' ;
					   if(vShowRes ==1) 
					   		vLeftTable += '  <TD style="BORDER-TOP: #efefef 1px solid; FONT-SIZE: 12px; BORDER-LEFT: #efefef 1px solid; WIDTH: '+vStatusWidth+'px; HEIGHT: 20px" align=center nowrap><span class="corpo8_bold"></span></TD>' ;
					   if(vShowDur ==1) 
					   		vLeftTable += '  <TD style="BORDER-TOP: #efefef 1px solid; FONT-SIZE: 12px; BORDER-LEFT: #efefef 1px solid; WIDTH: '+vStatusWidth+'px; HEIGHT: 20px" align=center nowrap><span class="corpo8_bold"></span></TD>' ;
					   if(vShowComp==1) 
					   		vLeftTable += '  <TD style="BORDER-TOP: #efefef 1px solid; FONT-SIZE: 12px; BORDER-LEFT: #efefef 1px solid; WIDTH: '+vStatusWidth+'px; HEIGHT: 20px" align=center nowrap><span class="corpo8_bold"></span></TD>' ;
					   if(vShowStartDate==1) 
					   		vLeftTable += '  <TD style="BORDER-TOP: #efefef 1px solid; FONT-SIZE: 12px; BORDER-LEFT: #efefef 1px solid; WIDTH: '+vStatusWidth+'px; HEIGHT: 20px" align=center nowrap><span class="corpo8_bold"><span></TD>' ;
					   if(vShowEndDate==1) 
					   		vLeftTable += '  <TD style="BORDER-TOP: #efefef 1px solid; FONT-SIZE: 12px; BORDER-LEFT: #efefef 1px solid; WIDTH: '+vStatusWidth+'px; HEIGHT: 20px" align=center nowrap><span class="corpo8_bold"></span></TD>' ;
					
					   vLeftTable += '</TR>';
					   vLeftTable +=  '<TR style="HEIGHT: 20px; " class="celdaImagenAzul1">' +
									  ' <TD style="BORDER-TOP: #efefef 1px solid; WIDTH: 15px; HEIGHT: 20px" colspan="7"></TD>' ;
					  vLeftTable += '</TR>';
					  vLeftTable += '</TBODY></TABLE></TD>';
					  vMainTable += vLeftTable;
					  vRightTable = 
								  '<TD style="width: ' + vChartWidth + 'px;" vAlign=top bgColor=#ffffff>' +
								  	'<DIV  class="scroll2" id="rightside">' +
								  		'<TABLE style="width: ' + vChartWidth + 'px;" cellSpacing=0 cellPadding=0 border=0>' +
								  		'<TBODY>'+
								  		'<TR style="HEIGHT: 18px; ">';
					  
					  
					  //background-image: url(../images/fondo_beige.gif);
					  vTmpDate.setFullYear(vMinDate.getFullYear(), vMinDate.getMonth(), vMinDate.getDate());
					  vTmpDate.setHours(0);
					  vTmpDate.setMinutes(0);
					  var imagenFondo='example_tabs.gif';
					  
					  while(Date.parse(vTmpDate) <= Date.parse(vMaxDate))
					  {	
							vStr = vTmpDate.getFullYear() + '';
//							vStr = vStr.substring(2,4);
							
							if(vFormat == 'minute')
							{
								vRightTable += '<td class=gdatehead style="FONT-SIZE: 12px; HEIGHT: 19px;" align=center colspan=60><span class="copyrigthSinPadding">' ;
								vRightTable += JSGantt.formatDateStr(vTmpDate, vDateDisplayFormat) + ' ' + vTmpDate.getHours() + ':00 -' + vTmpDate.getHours() + ':59 </span></td>';
								vTmpDate.setHours(vTmpDate.getHours()+1);
							}
							
							if(vFormat == 'hour')
							{
								vRightTable += '<td class=gdatehead style="FONT-SIZE: 12px; HEIGHT: 19px;" align=center colspan=24><span class="copyrigthSinPadding">' ;
								vRightTable += JSGantt.formatDateStr(vTmpDate, vDateDisplayFormat) + '</span></td>';
								vTmpDate.setDate(vTmpDate.getDate()+1);
							}
							
							if(vFormat == 'day')
							{
								vRightTable += '<td class=gdatehead style="FONT-SIZE: 12px; HEIGHT: 19px;background-image: url(../images/'+imagenFondo+');" align=center colspan=7><span class="copyrigthSinPadding">Del ' +
								JSGantt.formatDateStr(vTmpDate,vDateDisplayFormat.substring(0,5)) + ' - ';
							   	vTmpDate.setDate(vTmpDate.getDate()+6);
								vRightTable += JSGantt.formatDateStr(vTmpDate, vDateDisplayFormat) + '</span></td>';
							   	vTmpDate.setDate(vTmpDate.getDate()+1);
							}
							else 
								if(vFormat == 'week')
								{
									vRightTable += '<td class=gdatehead align=center style="FONT-SIZE: 12px; HEIGHT: 19px;background-image: url(../images/'+imagenFondo+');" width='+vColWidth+'px><span class="copyrigthSinPadding">'+ vStr + '</span></td>';
								   	vTmpDate.setDate(vTmpDate.getDate()+7);
								}
								else 
									if(vFormat == 'month')
									{
										vRightTable += '<td class=gdatehead align=center style="FONT-SIZE: 12px; HEIGHT: 19px;background-image: url(../images/'+imagenFondo+');" width='+vColWidth+'px><span class="copyrigthSinPadding">'+ vStr + '</span></td>';
									   vTmpDate.setDate(vTmpDate.getDate() + 1);
									   
									   while(vTmpDate.getDate() > 1)
									   {
										 vTmpDate.setDate(vTmpDate.getDate() + 1);
									   }
									}
									else 
										if(vFormat == 'quarter')
										{
											vRightTable += '<td class=gdatehead align=center style="FONT-SIZE: 12px; HEIGHT: 19px;background-image: url(../images/'+imagenFondo+');" width='+vColWidth+'px><span class="copyrigthSinPadding">'+ vStr + '</span></td>';
										   vTmpDate.setDate(vTmpDate.getDate() + 81);
										   while(vTmpDate.getDate() > 1)
										   {
											 vTmpDate.setDate(vTmpDate.getDate() + 1);
										   }
										}
									if(imagenFondo=='example_tabs.gif')
										imagenFondo='grisOscuro.gif';
									else
										imagenFondo='example_tabs.gif';
										
						  
						  }
					
							vRightTable += '</TR>'+
											'<TR class="celdaImagenAzul1">';
							vTmpDate.setFullYear(vMinDate.getFullYear(), vMinDate.getMonth(), vMinDate.getDate());
							vNxtDate.setFullYear(vMinDate.getFullYear(), vMinDate.getMonth(), vMinDate.getDate());
							vPriorDate.setFullYear(vMinDate.getFullYear(), vMinDate.getMonth(), vMinDate.getDate()-1);
							vNumCols = 0;
						
							while(Date.parse(vTmpDate) <= Date.parse(vMaxDate))
							{	
								if (vFormat == 'minute')
								{
								
									  if( vTmpDate.getMinutes() ==0 ) 
										  vWeekdayColor = "ccccff";
									  else
										  vWeekdayColor = "ffffff";
									  vDateRowStr += '<td class="ghead" style="BORDER-TOP: #efefef 1px solid; FONT-SIZE: 12px; HEIGHT: 19px; BORDER-LEFT: #efefef 1px solid;"  bgcolor=#' + vWeekdayColor + ' align=center><div style="width: '+vColWidth+'px">' + vTmpDate.getMinutes() + '</div></td>';
									  vItemRowStr += '<td class="ghead" style="BORDER-TOP: #efefef 1px solid; FONT-SIZE: 12px; BORDER-LEFT: #efefef 1px solid; cursor: default;"  bgcolor=#' + vWeekdayColor + ' align=center><div style="width: '+vColWidth+'px">&nbsp&nbsp</div></td>';
									  vTmpDate.setMinutes(vTmpDate.getMinutes() + 1);
								}
							  	else 
								  if (vFormat == 'hour')
								  {
								  
										if(  vTmpDate.getHours() ==0  ) 
											vWeekdayColor = "ccccff";
										else
											vWeekdayColor = "ffffff";
										vDateRowStr += '<td class="ghead" style="BORDER-TOP: #efefef 1px solid; FONT-SIZE: 12px; HEIGHT: 19px; BORDER-LEFT: #efefef 1px solid;"  bgcolor=#' + vWeekdayColor + ' align=center><div style="width: '+vColWidth+'px">' + vTmpDate.getHours() + '</div></td>';
										vItemRowStr += '<td class="ghead" style="BORDER-TOP: #efefef 1px solid; FONT-SIZE: 12px; BORDER-LEFT: #efefef 1px solid; cursor: default;"  bgcolor=#' + vWeekdayColor + ' align=center><div style="width: '+vColWidth+'px">&nbsp&nbsp</div></td>';
										vTmpDate.setHours(vTmpDate.getHours() + 1);
								  }
								  else 
									  if(vFormat == 'day' )
									  {
											if( JSGantt.formatDateStr(vCurrDate,'mm/dd/yyyy') == JSGantt.formatDateStr(vTmpDate,'mm/dd/yyyy')) 
										  	{
												  vWeekdayColor  = "FFDFBF";
												  vWeekendColor  = "FFDFBF";
												  vWeekdayGColor  = "bbbbff";
												  vWeekendGColor = "8888ff";
										  	} 
										  	else 
										  	{
												  vWeekdayColor = "FFF5EC";
												  vWeekendColor = "DFD";
												  vWeekdayGColor = "f3f3f3";
												  vWeekendGColor = "c3c3c3";
										  	}
											if(vTmpDate.getDay() % 6 == 0) 
											{
											  	vDateRowStr  += '<td class="gheadwkend" style="BORDER-TOP: #efefef 1px solid; FONT-SIZE: 12px; HEIGHT: 19px; BORDER-LEFT: #efefef 1px solid;" align=center><div style="width: '+vColWidth+'px"><span class="corpo7Simple">' + vTmpDate.getDate() + '</span></div></td>';
											  	vItemRowStr  += '<td class="gheadwkend" style="BORDER-TOP: #efefef 1px solid; FONT-SIZE: 12px; BORDER-LEFT: #efefef 1px solid; cursor: default;background-color:#' + vWeekendColor + '" align=center><div style="width: '+vColWidth+'px">&nbsp</div></td>';
											}
											else 
											{
												vDateRowStr += '<td class="ghead" style="BORDER-TOP: #efefef 1px solid; FONT-SIZE: 12px; HEIGHT: 19px; BORDER-LEFT: #efefef 1px solid;" align=center><div style="width: '+vColWidth+'px"><span class="corpo7Simple">' + vTmpDate.getDate() + '</span></div></td>';
												if( JSGantt.formatDateStr(vCurrDate,'mm/dd/yyyy') == JSGantt.formatDateStr(vTmpDate,'mm/dd/yyyy')) 
													vItemRowStr += '<td class="ghead" style="BORDER-TOP: #efefef 1px solid; FONT-SIZE: 12px; BORDER-LEFT: #efefef 1px solid; cursor: default;background-color:#' + vWeekdayColor + '" align=center><div style="width: '+vColWidth+'px">&nbsp&nbsp</div></td>';
												else
													vItemRowStr += '<td class="ghead" style="BORDER-TOP: #efefef 1px solid; FONT-SIZE: 12px; BORDER-LEFT: #efefef 1px solid; cursor: default;"  align=center><div style="width: '+vColWidth+'px">&nbsp&nbsp</div></td>';
											}
										  	vTmpDate.setDate(vTmpDate.getDate() + 1);
									  }
									  else 
										  if(vFormat == 'week')
										  {
											  	
											 	vNxtDate.setDate(vNxtDate.getDate() + 7);
												vPriorDate.setDate(vPriorDate.getDate()+7);
												
												var dia=vTmpDate.getDate();
												if(dia<10)
													dia='0'+dia;
												var mes=vTmpDate.getMonth()+1;
												if(mes<10)
													mes='0'+mes;
													
												var diaS=vPriorDate.getDate();
												if(diaS<10)
													diaS='0'+diaS;
												var mesS=vPriorDate.getMonth()+1;
												if(mesS<10)
													mesS='0'+mesS;
													
												
											 	if( vCurrDate >= vTmpDate && vCurrDate < vNxtDate ) 
													vWeekdayColor = "ccccff";
												else
													vWeekdayColor = "ffffff";
												if(vNxtDate <= vMaxDate) 
												{
													vDateRowStr += '<td class="ghead" style="BORDER-TOP: #efefef 1px solid; FONT-SIZE: 12px; HEIGHT: 19px; BORDER-LEFT: #efefef 1px solid;" align=center width:'+vColWidth+'px><div style="width: '+vColWidth+'px" class="corpo7Simple"> Del ' + dia+'/'+mes  + ' al '+diaS+'/'+mesS+'</div></td>';
												  	if( vCurrDate >= vTmpDate && vCurrDate < vNxtDate ) 
														vItemRowStr += '<td class="ghead" style="BORDER-TOP: #efefef 1px solid; FONT-SIZE: 12px; BORDER-LEFT: #efefef 1px solid;" align=center><div style="width: '+vColWidth+'px">&nbsp&nbsp</div></td>';
												  	else
													 	vItemRowStr += '<td class="ghead" style="BORDER-TOP: #efefef 1px solid; FONT-SIZE: 12px; BORDER-LEFT: #efefef 1px solid;" align=center><div style="width: '+vColWidth+'px">&nbsp&nbsp</div></td>';
												}
												else 
												{
												  	vDateRowStr += '<td class="ghead" style="BORDER-TOP: #efefef 1px solid; FONT-SIZE: 12px; HEIGHT: 19px; BORDER-LEFT: #efefef 1px solid;  BORDER-RIGHT: #efefef 1px solid;" align=center width:'+vColWidth+'px><div style="width: '+vColWidth+'px" class="corpo7Simple">Del ' + dia+'/'+mes  + ' al '+diaS+'/'+mesS+'</div></td>';
												  	if( vCurrDate >= vTmpDate && vCurrDate < vNxtDate ) 
													 	vItemRowStr += '<td class="ghead" style="BORDER-TOP: #efefef 1px solid; FONT-SIZE: 12px; BORDER-LEFT: #efefef 1px solid; BORDER-RIGHT: #efefef 1px solid;" align=center><div style="width: '+vColWidth+'px">&nbsp&nbsp</div></td>';
												  	else
													 	vItemRowStr += '<td class="ghead" style="BORDER-TOP: #efefef 1px solid; FONT-SIZE: 12px; BORDER-LEFT: #efefef 1px solid; BORDER-RIGHT: #efefef 1px solid;" align=center><div style="width: '+vColWidth+'px">&nbsp&nbsp</div></td>';
												}
												vTmpDate.setDate(vTmpDate.getDate() + 7);
										  }
										  else 
											  	if(vFormat == 'month')
											  	{
													vNxtDate.setFullYear(vTmpDate.getFullYear(), vTmpDate.getMonth(), vMonthDaysArr[vTmpDate.getMonth()]);
												  	if( vCurrDate >= vTmpDate && vCurrDate < vNxtDate ) 
														vWeekdayColor = "ccccff";
													else
														vWeekdayColor = "ffffff";
												  
												  if(vNxtDate <= vMaxDate) 
												  {
														vDateRowStr += '<td class="ghead" style="BORDER-TOP: #efefef 1px solid; FONT-SIZE: 12px; HEIGHT: 19px; BORDER-LEFT: #efefef 1px solid;" bgcolor=#' + vWeekdayColor + ' align=center width:'+vColWidth+'px><div style="width: '+vColWidth+'px " class="corpo7Simple">' + vMonthArr[vTmpDate.getMonth()] + '</div></td>';
														if( vCurrDate >= vTmpDate && vCurrDate < vNxtDate ) 
													   		vItemRowStr += '<td class="ghead" style="BORDER-TOP: #efefef 1px solid; FONT-SIZE: 12px; BORDER-LEFT: #efefef 1px solid;" bgcolor=#' + vWeekdayColor + ' align=center><div style="width: '+vColWidth+'px">&nbsp&nbsp</div></td>';
														else
													   		vItemRowStr += '<td class="ghead" style="BORDER-TOP: #efefef 1px solid; FONT-SIZE: 12px; BORDER-LEFT: #efefef 1px solid;" align=center><div style="width: '+vColWidth+'px">&nbsp&nbsp</div></td>';
												  } 
												  else 
												  {
														vDateRowStr += '<td class="ghead" style="BORDER-TOP: #efefef 1px solid; FONT-SIZE: 12px; HEIGHT: 19px; BORDER-LEFT: #efefef 1px solid; BORDER-RIGHT: #efefef 1px solid;" bgcolor=#' + vWeekdayColor + ' align=center width:'+vColWidth+'px><div style="width: '+vColWidth+'px" class="corpo7Simple">' + vMonthArr[vTmpDate.getMonth()] + '</div></td>';
														if( vCurrDate >= vTmpDate && vCurrDate < vNxtDate ) 
													   		vItemRowStr += '<td class="ghead" style="BORDER-TOP: #efefef 1px solid; FONT-SIZE: 12px; BORDER-LEFT: #efefef 1px solid; BORDER-RIGHT: #efefef 1px solid;" bgcolor=#' + vWeekdayColor + ' align=center><div style="width: '+vColWidth+'px">&nbsp&nbsp</div></td>';
														else
													   		vItemRowStr += '<td class="ghead" style="BORDER-TOP: #efefef 1px solid; FONT-SIZE: 12px; BORDER-LEFT: #efefef 1px solid; BORDER-RIGHT: #efefef 1px solid;" align=center><div style="width: '+vColWidth+'px">&nbsp&nbsp</div></td>';
												  }
												  
												  vTmpDate.setDate(vTmpDate.getDate() + 1);
												  while(vTmpDate.getDate() > 1) 
												  {
														vTmpDate.setDate(vTmpDate.getDate() + 1);
												  }
											  }
											  else 
												  if(vFormat == 'quarter')
												  {
														vNxtDate.setDate(vNxtDate.getDate() + 122);
														if( vTmpDate.getMonth()==0 || vTmpDate.getMonth()==1 || vTmpDate.getMonth()==2 )
															vNxtDate.setFullYear(vTmpDate.getFullYear(), 2, 31);
													 	else 
															if( vTmpDate.getMonth()==3 || vTmpDate.getMonth()==4 || vTmpDate.getMonth()==5 )
																vNxtDate.setFullYear(vTmpDate.getFullYear(), 5, 30);
														  	else 
																if( vTmpDate.getMonth()==6 || vTmpDate.getMonth()==7 || vTmpDate.getMonth()==8 )
																	vNxtDate.setFullYear(vTmpDate.getFullYear(), 8, 30);
															  	else 
																  	if( vTmpDate.getMonth()==9 || vTmpDate.getMonth()==10 || vTmpDate.getMonth()==11 )
																		vNxtDate.setFullYear(vTmpDate.getFullYear(), 11, 31);
									  
													 	if( vCurrDate >= vTmpDate && vCurrDate < vNxtDate ) 
														  	vWeekdayColor = "ccccff";
													 	else
														  	vWeekdayColor = "ffffff";
									  
														if(vNxtDate <= vMaxDate) 
														{
														 	vDateRowStr += '<td class="ghead" style="BORDER-TOP: #efefef 1px solid; FONT-SIZE: 12px; HEIGHT: 19px; BORDER-LEFT: #efefef 1px solid;" bgcolor=#' + vWeekdayColor + ' align=center width:'+vColWidth+'px><div style="width: '+vColWidth+'px" class="corpo7Simple">' + vQuarterArr[vTmpDate.getMonth()] + '</div></td>';
														  	if( vCurrDate >= vTmpDate && vCurrDate < vNxtDate ) 
																vItemRowStr += '<td class="ghead" style="BORDER-TOP: #efefef 1px solid; FONT-SIZE: 12px; BORDER-LEFT: #efefef 1px solid;" bgcolor=#' + vWeekdayColor + ' align=center><div style="width: '+vColWidth+'px">&nbsp&nbsp</div></td>';
														  	else
																vItemRowStr += '<td class="ghead" style="BORDER-TOP: #efefef 1px solid; FONT-SIZE: 12px; BORDER-LEFT: #efefef 1px solid;" align=center><div style="width: '+vColWidth+'px">&nbsp&nbsp</div></td>';
														} 
														else 
														{
															vDateRowStr += '<td class="ghead" style="BORDER-TOP: #efefef 1px solid; FONT-SIZE: 12px; HEIGHT: 19px; BORDER-LEFT: #efefef 1px solid; BORDER-RIGHT: #efefef 1px solid;" bgcolor=#' + vWeekdayColor + ' align=center width:'+vColWidth+'px><div style="width: '+vColWidth+'px" class="corpo7Simple">' + vQuarterArr[vTmpDate.getMonth()] + '</div></td>';
														  	if( vCurrDate >= vTmpDate && vCurrDate < vNxtDate ) 
															 	vItemRowStr += '<td class="ghead" style="BORDER-TOP: #efefef 1px solid; FONT-SIZE: 12px; BORDER-LEFT: #efefef 1px solid; BORDER-RIGHT: #efefef 1px solid;" bgcolor=#' + vWeekdayColor + ' align=center><div style="width: '+vColWidth+'px">&nbsp&nbsp</div></td>';
														  	else 
																vItemRowStr += '<td class="ghead" style="BORDER-TOP: #efefef 1px solid; FONT-SIZE: 12px; BORDER-LEFT: #efefef 1px solid; BORDER-RIGHT: #efefef 1px solid;" align=center><div style="width: '+vColWidth+'px">&nbsp&nbsp</div></td>';
														}
									  
													 	vTmpDate.setDate(vTmpDate.getDate() + 81);
									  
														while(vTmpDate.getDate() > 1) 
														{
															vTmpDate.setDate(vTmpDate.getDate() + 1);
														}
									  
												  }
							}
					
					   		vRightTable += vDateRowStr + '</TR>';
					   		vRightTable += '</TBODY></TABLE>';
							
							vFilas='';
					   		for(i = 0; i < vTaskList.length; i++)
					   		{
								vTmpDate.setFullYear(vMinDate.getFullYear(), vMinDate.getMonth(), vMinDate.getDate());
								vTaskStart = vTaskList[i].getStart();
								vTaskEnd   = vTaskList[i].getEnd();
							
								vNumCols = 0;
								vID = vTaskList[i].getID();
								vNumUnits = (vTaskList[i].getEnd() - vTaskList[i].getStart()) / (24 * 60 * 60 * 1000) + 1;
						 		if (vFormat=='hour')
						 		{
							  		vNumUnits = (vTaskList[i].getEnd() - vTaskList[i].getStart()) / (  60 * 1000) + 1;
						 		}
						 		else 
								 	if (vFormat=='minute')
								 	{
										vNumUnits = (vTaskList[i].getEnd() - vTaskList[i].getStart()) / (  60 * 1000) + 1;
								 	}
						 
						 		if(vTaskList[i].getVisible() == 0) 
							 		vFilas += '<DIV id="childgrid_' + vID + '" style="position:relative; display:none;">';
						 		else
							   		vFilas += '<DIV id="childgrid_' + vID + '" style="position:relative">';
						  
						 		if( vTaskList[i].getMile()) 
						 		{
							 		vFilas += '<DIV>'+
													'<TABLE style="position:relative; top:0px; width: ' + vChartWidth + 'px;" cellSpacing=0 cellPadding=0 border=0>' +
													'<TR id="childrow_' + vID + '" class=yesdisplay style="HEIGHT: 20px" onMouseover=g.mouseOver(this,\'' + vID + '\',"right","mile") onMouseout=g.mouseOut(this,\'' + vID + '\',"right","mile")>' + vItemRowStr + '</TR></TABLE></DIV>';
							 		vDateRowStr = JSGantt.formatDateStr(vTaskStart,vDateDisplayFormat);
					
							 		vTaskLeft = (Date.parse(vTaskList[i].getStart()) - Date.parse(vMinDate)) / (24 * 60 * 60 * 1000);
							 		vTaskRight = 1;
					
							  		vFilas +=
												'<div id="bardiv_' + vID + '" style="position:absolute; top:0px; left:' + Math.ceil((vTaskLeft * (vDayWidth) + 1)) + 'px; height: 18px; width:160px; overflow:hidden;">' +
												'  <div id="taskbar_' + vID + '" title="' + vTaskList[i].getName() + ': ' + vDateRowStr + '" style="height: 16px; width:12px; overflow:hidden; cursor: pointer;" onclick=JSGantt.taskLink("' + vTaskList[i].getLink() + '",300,200);>';
					
							 		if(vTaskList[i].getCompVal() < 100)
								  	{
										vFilas += '&loz;</div>' ;
									}
							 		else
								 	{ 
										vFilas += '&diams;</div>' ;
									}
								 	if( g.getCaptionType() ) 
									{
										 vCaptionStr = '';
										 switch( g.getCaptionType() ) 
										 {           
											case 'Caption':    vCaptionStr = vTaskList[i].getCaption();  break;
											case 'Resource':   vCaptionStr = vTaskList[i].getResource();  break;
											case 'Duration':   vCaptionStr = vTaskList[i].getDuration(vFormat);  break;
											case 'Complete':   vCaptionStr = vTaskList[i].getCompStr();  break;
										 }
										 //vRightTable += '<div style="FONT-SIZE:12px; position:absolute; left: 6px; top:1px;">' + vCaptionStr + '</div>';
										 vFilas += '<div style="FONT-SIZE:12px; position:absolute; top:2px; width:220px; left:12px">' + vCaptionStr + '</div>';
									};
					
							  		vFilas += '</div>';
						  		} 
								else 
								{
								
								   // Build date string for Title
								   vDateRowStr = JSGantt.formatDateStr(vTaskStart,vDateDisplayFormat) + ' - ' + JSGantt.formatDateStr(vTaskEnd,vDateDisplayFormat);
								
									if (vFormat=='minute')
									{
										vTaskRight = (Date.parse(vTaskList[i].getEnd()) - Date.parse(vTaskList[i].getStart())) / ( 60 * 1000) + 1/vColUnit;
										vTaskLeft = Math.ceil((Date.parse(vTaskList[i].getStart()) - Date.parse(vMinDate)) / ( 60 * 1000));
									}
									else if (vFormat=='hour')
									{
										vTaskRight = (Date.parse(vTaskList[i].getEnd()) - Date.parse(vTaskList[i].getStart())) / ( 60 * 60 * 1000) + 1/vColUnit;
										vTaskLeft = (Date.parse(vTaskList[i].getStart()) - Date.parse(vMinDate)) / ( 60 * 60 * 1000);
									}
									else
									{
										vTaskRight = (Date.parse(vTaskList[i].getEnd()) - Date.parse(vTaskList[i].getStart())) / (24 * 60 * 60 * 1000) + 1/vColUnit;
										vTaskLeft = Math.ceil((Date.parse(vTaskList[i].getStart()) - Date.parse(vMinDate)) / (24 * 60 * 60 * 1000));
										if (vFormat='day')
										{
											var tTime=new Date();
											tTime.setTime(Date.parse(vTaskList[i].getStart()));
											if (tTime.getMinutes() > 29)
												vTaskLeft+=.5;
										}
									}
								
								   // Draw Group Bar  which has outer div with inner group div and several small divs to left and right to create angled-end indicators
								   if( vTaskList[i].getGroup()) 
								   {
									   
									  	vFilas += '<DIV>'+
															'<TABLE style="position:relative; top:0px; width: ' + vChartWidth + 'px;" cellSpacing=0 cellPadding=0 border=0>' +
																'<TR id="childrow_' + vID + '" class=yesdisplay style="HEIGHT: '+alturaFila+'px; 	background-color:#f3f3f3" onMouseover=g.mouseOver(this,\'' + vID + '\',"right","group") onMouseout=g.mouseOut(this,\'' + vID + '\',"right","group")>' + vItemRowStr + 
																'</TR>'+
															'</TABLE>'+
														'</DIV>';
										vFilas +=		
														'<div id="bardiv_' + vID + '" style="position:absolute; top:5px; left:' + Math.ceil(vTaskLeft * (vDayWidth) + 1) + 'px; height: 7px; width:' + Math.ceil((vTaskRight) * (vDayWidth) - 1) + 'px">' +
															'<div id="taskbar_' + vID + '" title="' + vTaskList[i].getName() + ': ' + vDateRowStr + '" class=gtask style="background-color:#'+colorGrupo+'; height: 7px; width:' + Math.ceil((vTaskRight) * (vDayWidth) -1) + 'px;  cursor: pointer;opacity:0.9;">' +
																'<div style="Z-INDEX: -4; float:left; background-color:#FF0; height:3px; overflow: hidden; margin-top:1px; ' +
																		   'margin-left:1px; margin-right:1px; filter: alpha(opacity=80); opacity:0.8; width:' + vTaskList[i].getCompStr() + '; ' + 
																		   'cursor: pointer;" onclick=\'JSGantt.taskLink("' + vTaskList[i].getLink() + '",300,200);\'>' +
																'</div>' +
															'</div>' +
															'<div style="Z-INDEX: -4; float:left; background-color:#'+colorGrupo+'; height:4px; overflow: hidden; width:1px;"></div>' +
															'<div style="Z-INDEX: -4; float:right; background-color:#'+colorGrupo+'; height:4px; overflow: hidden; width:1px;"></div>' +
															'<div style="Z-INDEX: -4; float:left; background-color:#'+colorGrupo+'; height:3px; overflow: hidden; width:1px;"></div>' +
															'<div style="Z-INDEX: -4; float:right; background-color:#'+colorGrupo+'; height:3px; overflow: hidden; width:1px;"></div>' +
															'<div style="Z-INDEX: -4; float:left; background-color:#'+colorGrupo+'; height:2px; overflow: hidden; width:1px;"></div>' +
															'<div style="Z-INDEX: -4; float:right; background-color:#'+colorGrupo+'; height:2px; overflow: hidden; width:1px;"></div>' +
															'<div style="Z-INDEX: -4; float:left; background-color:#'+colorGrupo+'; height:1px; overflow: hidden; width:1px;"></div>' +
															'<div style="Z-INDEX: -4; float:right; background-color:#'+colorGrupo+'; height:1px; overflow: hidden; width:1px;"></div>' ;
														
										if( g.getCaptionType() ) 
										{
											vCaptionStr = '';
											switch( g.getCaptionType() ) 
											{           
												case 'Caption':    vCaptionStr = vTaskList[i].getCaption();  break;
												case 'Resource':   vCaptionStr = vTaskList[i].getResource();  break;
												case 'Duration':   vCaptionStr = vTaskList[i].getDuration(vFormat);  break;
												case 'Complete':   vCaptionStr = vTaskList[i].getCompStr();  break;
											}
											//vRightTable += '<div style="FONT-SIZE:12px; position:absolute; left: 6px; top:1px;">' + vCaptionStr + '</div>';
											var claseComp='';
											var arrResp=new Array();
											var tituloResp='';
											if(vCaptionStr!='')
											{
												
												arrResp=vCaptionStr.split('|');
												vCaptionStr=arrResp[0];
												if(arrResp.length>1)
													tituloResp=arrResp[1];
												claseComp=''
											}
											
											if(vCaptionStr=='')
												claseComp='';
											
											vFilas += '<div style="FONT-SIZE:12px; position:absolute; top:-3px; width:220px; left:' + (Math.ceil((vTaskRight) * (vDayWidth) - 1) + 6) + 'px"><span class="corpo7Simple '+claseComp+' " alt="'+tituloResp+'" title="'+tituloResp+'">' + vCaptionStr + '</span></div>';
										};
										vFilas += '</div>' ;
									  	
								
								   } 
								   else 
								   {
										
										vDivStr = 	'<DIV>'+
													'<TABLE style="position:relative; top:0px; width: ' + vChartWidth + 'px;" cellSpacing=0 cellPadding=0 border=0>' +
								   						'<TR id="childrow_' + vID + '" class=yesdisplay style="HEIGHT: '+alturaFila+'px" bgColor=#ffffff onMouseover=g.mouseOver(this,\'' + vID + '\',"right","row") onMouseout=g.mouseOut(this,\'' + vID + '\',"right","row")>' + vItemRowStr + 
														'</TR>'+
													'</TABLE>'+
													'</DIV>';
										vFilas += vDivStr;
									  	vFilas +=
													   '<div id="bardiv_' + vID + '" style="position:absolute; top:4px; left:' + Math.ceil(vTaskLeft * (vDayWidth) + 1) + 'px; height:18px; width:' + Math.ceil((vTaskRight) * (vDayWidth) - 1) + 'px">' +
														  '<div id="taskbar_' + vID + '" title="' + vTaskList[i].getName() + ': ' + vDateRowStr + ' ('+vTaskList[i].getDuration()+') " class="gtask" style="background-color:#' + vTaskList[i].getColor() +'; height: 13px; width:' + Math.ceil((vTaskRight) * (vDayWidth) - 1) + 'px; cursor: pointer;opacity:0.9;" ' +
															 'onclick=\'JSGantt.taskLink("' + vTaskList[i].getLink() + '",300,200);\' >' +
															 '<div class="gcomplete" style="Z-INDEX: -4; float:left; background-color:#FF0; height:5px; overflow: auto; margin-top:4px; filter: alpha(opacity=80); opacity:0.8; width:' + vTaskList[i].getCompStr() + '; overflow:hidden">' +
															 '</div>' +
														  '</div>';
							
											  if( g.getCaptionType() ) 
											  {
												 vCaptionStr = '';
												 switch( g.getCaptionType() ) 
												 {           
													case 'Caption':    vCaptionStr = vTaskList[i].getCaption();  break;
													case 'Resource':   vCaptionStr = vTaskList[i].getResource();  break;
													case 'Duration':   vCaptionStr = vTaskList[i].getDuration(vFormat);  break;
													case 'Complete':   vCaptionStr = vTaskList[i].getCompStr();  break;
												   }
												 //vRightTable += '<div style="FONT-SIZE:12px; position:absolute; left: 6px; top:-3px;">' + vCaptionStr + '</div>';
												var claseComp='';
												var arrResp=new Array();
												var tituloResp='';
												if(vCaptionStr!='')
												{
													
													arrResp=vCaptionStr.split('|');
													vCaptionStr=arrResp[0];
													if(arrResp.length>1)
														tituloResp=arrResp[1];
													claseComp=''
												}
												if(vCaptionStr=='')
													claseComp='';
												 vFilas += '<div style="FONT-SIZE:12px; position:absolute; top:-3px; width:220px; left:' + (Math.ceil((vTaskRight) * (vDayWidth) - 1) + 6) + 'px"><span class="corpo7Simple '+claseComp+'" alt="'+tituloResp+'" title="'+tituloResp+'">' + vCaptionStr + '</span></div>';
											}
										vFilas += '</div>' ;
										
										
							 	}
								vFilas += '</div>' ;
						  }
					   }
					   vRightTable+=vFilas;
					   
					   
					   		vRightTable += '<DIV>'+
															'<TABLE style="position:relative; top:0px; width: ' + vChartWidth + 'px;" cellSpacing=0 cellPadding=0 border=0>' +
																'<TR style="HEIGHT: '+(alturaFila+1)+'px;"><td>&nbsp;</td>'+
																'</TR>'+
															'</TABLE>'+
														'</DIV>';
							vRightTable += '<DIV>'+
															'<TABLE style="position:relative; top:0px; width: ' + vChartWidth + 'px;" cellSpacing=0 cellPadding=0 border=0>' +
																'<TR style="HEIGHT: '+alturaFila+'px;" class="celdaImagenAzul1"><td>&nbsp;</td>'+
																'</TR>'+
															'</TABLE>'+
														'</DIV>';												
						
					   vMainTable += vRightTable + '</DIV></TD></TR></TBODY></TABLE>';
					   vDiv.innerHTML = vMainTable;
					}
		
		   		}; //this.draw
	
	this.mouseOver = function( pObj, pID, pPos, pType ) 
	{
		if( pPos == 'right' )  
		  vID = 'child_' + pID;
		else 
		  vID = 'childrow_' + pID;
		pObj.bgColor = "#ffffaa";
		vRowObj = JSGantt.findObj(vID);
		if (vRowObj) 
			vRowObj.bgColor = "#ffffaa";
	 };
	
	this.mouseOut = function( pObj, pID, pPos, pType ) 
	{
		  if( pPos == 'right' )  
			vID = 'child_' + pID;
		  else 
			vID = 'childrow_' + pID;
		  
		  pObj.bgColor = "#ffffff";
		  vRowObj = JSGantt.findObj(vID);
		  if (vRowObj) 
		  {
			 if( pType == "group") 
			 {
				pObj.bgColor = "#f3f3f3";
				vRowObj.bgColor = "#f3f3f3";
			 } 
			 else 
			 {
				pObj.bgColor = "#ffffff";
				vRowObj.bgColor = "#ffffff";
			 }
		  }
	};
}; 

JSGantt.isIE = function () 
{
	if(typeof document.all != 'undefined')
		{return true;}
	else
		{return false;}
};

JSGantt.processRows = 	function(pList, pID, pRow, pLevel, pOpen)
						{
						   var vMinDate = new Date();
						   var vMaxDate = new Date();
						   var vMinSet  = 0;
						   var vMaxSet  = 0;
						   var vList    = pList;
						   var vLevel   = pLevel;
						   var i        = 0;
						   var vNumKid  = 0;
						   var vCompSum = 0;
						   var vVisible = pOpen;
						   
						   for(i = 0; i < pList.length; i++)
						   {
							  
							  
							  if(pList[i].getParent() == pID) 
							  {
								  
								 vVisible = pOpen;
								 pList[i].setVisible(vVisible);
								 if(vVisible==1 && pList[i].getOpen() == 0) 
								   {vVisible = 0;}
									
								 pList[i].setLevel(vLevel);
								 vNumKid++;
						
								 if(pList[i].getGroup() == 1) 
								 {
									JSGantt.processRows(vList, pList[i].getID(), i, vLevel+1, vVisible);
								 };
						
								 if( vMinSet==0 || pList[i].getStart() < vMinDate) 
								 {
									vMinDate = pList[i].getStart();
									vMinSet = 1;
								 };
						
								 if( vMaxSet==0 || pList[i].getEnd() > vMaxDate) 
								 {
									vMaxDate = pList[i].getEnd();
									vMaxSet = 1;
								 };
						
								 vCompSum += pList[i].getCompVal();
						
							  }
						   }
						
						   if(pRow >= 0) 
						   {
							  //pList[pRow].setStart(vMinDate);
							  //pList[pRow].setEnd(vMaxDate);
							  //pList[pRow].setNumKid(vNumKid);
							  //pList[pRow].setCompVal(Math.ceil(vCompSum/vNumKid));
						   }
						
						};

JSGantt.getMinDate = function getMinDate(pList, pFormat)  
					  {
				
						 var vDate = new Date();
				
						 vDate.setFullYear(pList[0].getStart().getFullYear(), pList[0].getStart().getMonth(), pList[0].getStart().getDate());
				
						 // Parse all Task End dates to find min
						 for(i = 0; i < pList.length; i++)
						 {
							if(Date.parse(pList[i].getStart()) < Date.parse(vDate))
							   vDate.setFullYear(pList[i].getStart().getFullYear(), pList[i].getStart().getMonth(), pList[i].getStart().getDate());
						 }
				
						 if ( pFormat== 'minute')
						 {
							vDate.setHours(0);
							vDate.setMinutes(0);
						 }
						 else 
							if (pFormat == 'hour' )
							 {
								vDate.setHours(0);
								vDate.setMinutes(0);
							 }
						 
							else 
								if (pFormat=='day')
								{
									vDate.setDate(vDate.getDate() - 1);
									while(vDate.getDay() % 7 > 0)
									{
										vDate.setDate(vDate.getDate() - 1);
									}
						
								}
								else 
									if (pFormat=='week')
									{
										vDate.setDate(vDate.getDate() - 7);
										while(vDate.getDay() % 7 > 0)
										{
											vDate.setDate(vDate.getDate() - 1);
										}
										vDate.setDate(vDate.getDate() + 1);
									}
									else 
										if (pFormat=='month')
										{
											while(vDate.getDate() > 1)
											{
												vDate.setDate(vDate.getDate() - 1);
											}
										}
										else 
											if (pFormat=='quarter')
											{
												if( vDate.getMonth()==0 || vDate.getMonth()==1 || vDate.getMonth()==2 )
												   {vDate.setFullYear(vDate.getFullYear(), 0, 1);}
												else if( vDate.getMonth()==3 || vDate.getMonth()==4 || vDate.getMonth()==5 )
												   {vDate.setFullYear(vDate.getFullYear(), 3, 1);}
												else if( vDate.getMonth()==6 || vDate.getMonth()==7 || vDate.getMonth()==8 )
												   {vDate.setFullYear(vDate.getFullYear(), 6, 1);}
												else if( vDate.getMonth()==9 || vDate.getMonth()==10 || vDate.getMonth()==11 )
												   {vDate.setFullYear(vDate.getFullYear(), 9, 1);}
									
											};
						 return(vDate);
				
					  };


JSGantt.getMaxDate = function (pList, pFormat)
					{
					   var vDate = new Date();
						vDate.setFullYear(pList[0].getEnd().getFullYear(), pList[0].getEnd().getMonth(), pList[0].getEnd().getDate());
						for(i = 0; i < pList.length; i++)
						{
							if(Date.parse(pList[i].getEnd()) > Date.parse(vDate))
							{
								 //vDate.setFullYear(pList[0].getEnd().getFullYear(), pList[0].getEnd().getMonth(), pList[0].getEnd().getDate());
								 vDate.setTime(Date.parse(pList[i].getEnd()));
							}	
						}
						
						if (pFormat == 'minute')
						{
							vDate.setHours(vDate.getHours() + 1);
							vDate.setMinutes(59);
						}	
						
						if (pFormat == 'hour')
						{
							vDate.setHours(vDate.getHours() + 2);
						}				
						
						if (pFormat=='day')
						{
							vDate.setDate(vDate.getDate() + 1);
							while(vDate.getDay() % 6 > 0)
							{
								vDate.setDate(vDate.getDate() + 1);
							}
						}
						
						if (pFormat=='week')
						{
							vDate.setDate(vDate.getDate() + 7);
							while(vDate.getDay() % 6 > 0)
							{
								vDate.setDate(vDate.getDate() + 1);
							}
						}
						
						if (pFormat=='month')
						{
							while(vDate.getDay() > 1)
							{
								vDate.setDate(vDate.getDate() + 1);
							}
							vDate.setDate(vDate.getDate() - 1);
						}
						
						
						if (pFormat=='quarter')
						{
							if( vDate.getMonth()==0 || vDate.getMonth()==1 || vDate.getMonth()==2 )
							   vDate.setFullYear(vDate.getFullYear(), 2, 31);
							else if( vDate.getMonth()==3 || vDate.getMonth()==4 || vDate.getMonth()==5 )
							   vDate.setFullYear(vDate.getFullYear(), 5, 30);
							else if( vDate.getMonth()==6 || vDate.getMonth()==7 || vDate.getMonth()==8 )
							   vDate.setFullYear(vDate.getFullYear(), 8, 30);
							else if( vDate.getMonth()==9 || vDate.getMonth()==10 || vDate.getMonth()==11 )
							   vDate.setFullYear(vDate.getFullYear(), 11, 31);
						}
						
						return(vDate);
					
					};


JSGantt.findObj = function (theObj, theDoc)
				  {
			
					 var p, i, foundObj;
			
					 if(!theDoc) {theDoc = document;}
			
					 if( (p = theObj.indexOf("?")) > 0 && parent.frames.length){
			
						theDoc = parent.frames[theObj.substring(p+1)].document;
			
						theObj = theObj.substring(0,p);
			
					 }
			
					 if(!(foundObj = theDoc[theObj]) && theDoc.all) 
			
						{foundObj = theDoc.all[theObj];}
			
			
			
					 for (i=0; !foundObj && i < theDoc.forms.length; i++) 
			
						{foundObj = theDoc.forms[i][theObj];}
			
			
			
					 for(i=0; !foundObj && theDoc.layers && i < theDoc.layers.length; i++)
			
						{foundObj = JSGantt.findObj(theObj,theDoc.layers[i].document);}
			
			
			
					 if(!foundObj && document.getElementById)
			
						{foundObj = document.getElementById(theObj);}
			
			
			
					 return foundObj;
			
				  };


JSGantt.changeFormat =  function(pFormat,ganttObj) 
						{
					
							if(ganttObj) 
							{
							ganttObj.setFormat(pFormat);
							ganttObj.DrawDependencies();
							}
							else
							{alert('Chart undefined');};
						};


JSGantt.folder= function (pID,ganttObj) 
{
   var vList = ganttObj.getList();
   for(i = 0; i < vList.length; i++)
   {
      if(vList[i].getID() == pID) 
	  {
         if( vList[i].getOpen() == 1 ) 
		 {
				vList[i].setOpen(0);
				JSGantt.hide(pID,ganttObj);
				if (JSGantt.isIE()) 
				{
					JSGantt.findObj('group_'+pID).innerHTML = '<img src="../images/verMas.gif">';
					JSGantt.findObj('group_'+pID).setAttribute('estado','0');
				}
				else
				{
					JSGantt.findObj('group_'+pID).innerHTML = '<img src="../images/verMas.gif">';
					JSGantt.findObj('group_'+pID).setAttribute('estado','0');
				}
         } 
		 else 
		 {

				vList[i].setOpen(1);
            	JSGantt.show(pID, 1, ganttObj);
               	if (JSGantt.isIE()) 
                {
					JSGantt.findObj('group_'+pID).innerHTML = '<img src="../images/verMenos.png">';
					JSGantt.findObj('group_'+pID).setAttribute('estado','1');
				}
               	else
                {
					JSGantt.findObj('group_'+pID).innerHTML = '<img src="../images/verMenos.png">';
					JSGantt.findObj('group_'+pID).setAttribute('estado','1');
				}

         }

      }
   }
};

JSGantt.hide=     function (pID,ganttObj) 
				{
				   var vList = ganttObj.getList();
				   var vID   = 0;
				
				   for(var i = 0; i < vList.length; i++)
				   {
					  if(vList[i].getParent() == pID) 
					  {
							vID = vList[i].getID();
							JSGantt.findObj('child_' + vID).style.display = "none";
							JSGantt.findObj('childgrid_' + vID).style.display = "none";
							vList[i].setVisible(0);
							if(vList[i].getGroup() == 1) 
							{
								JSGantt.hide(vID,ganttObj);
							}
					  }
				
				   }
				};

JSGantt.show =  function (pID, pTop, ganttObj) 
				{
				   var vList = ganttObj.getList();
				   var vID   = 0;
				   for(var i = 0; i < vList.length; i++)
				   {
						if(vList[i].getParent() == pID) 
						{
							
						   vID = vList[i].getID();
						   if(pTop == 1) 
						   {
							  if (JSGantt.isIE()) 
							  { // IE;
								 if( JSGantt.findObj('group_'+pID).getAttribute('estado')=='0') 
								 {
									JSGantt.findObj('child_'+vID).style.display = "";
									JSGantt.findObj('childgrid_'+vID).style.display = "";
									vList[i].setVisible(1);
								 }
							  } 
							  else 
							  {
								 if( JSGantt.findObj('group_'+pID).getAttribute('estado')=='0') 
								 {
									JSGantt.findObj('child_'+vID).style.display = "";
									JSGantt.findObj('childgrid_'+vID).style.display = "";
									vList[i].setVisible(1);
								 }
							  }
						
						   } 
						   else 
						   {
						
							  if (JSGantt.isIE()) 
							  { // IE;
								 if( JSGantt.findObj('group_'+pID).getAttribute('estado')=='1') 
								 {
									JSGantt.findObj('child_'+vID).style.display = "";
									JSGantt.findObj('childgrid_'+vID).style.display = "";
									vList[i].setVisible(1);
								 }
						
							  } 
							  else 
							  {
						
								 if( JSGantt.findObj('group_'+pID).getAttribute('estado')=='1') 
								 {
									JSGantt.findObj('child_'+vID).style.display = "";
									JSGantt.findObj('childgrid_'+vID).style.display = "";
									vList[i].setVisible(1);
								 }
							  }
						   }
						
						  if(vList[i].getGroup() == 1) 
						  {
							   JSGantt.show(vID, 0,ganttObj);
						  }
				
					  }
				   }
				};

JSGantt.taskLink = function(pRef,pWidth,pHeight) 
				  {
				
					if(pWidth)  {vWidth =pWidth;}  else {vWidth =400;}
					if(pHeight) {vHeight=pHeight;} else {vHeight=400;}
				
					var OpenWindow=window.open(pRef, "newwin", "height="+vHeight+",width="+vWidth); 
				
				  };

/**
* Parse dates based on gantt date format setting as defined in JSGantt.GanttChart.setDateInputFormat()
*
* @method parseDateStr
* @param pDateStr {String} - A string that contains the date (i.e. "01/01/09")
* @param pFormatStr {String} - The date format (mm/dd/yyyy,dd/mm/yyyy,yyyy-mm-dd)
* @return {Datetime}
*/
JSGantt.parseDateStr = 	function(pDateStr,pFormatStr) 
						{
   var vDate =new Date();	
   vDate.setTime( Date.parse(pDateStr));

   switch(pFormatStr) 
   {
	  case 'mm/dd/yyyy':
	     var vDateParts = pDateStr.split('/');
         vDate.setFullYear(parseInt(vDateParts[2], 10), parseInt(vDateParts[0], 10) - 1, parseInt(vDateParts[1], 10));
         break;
	  case 'dd/mm/yyyy':
	     var vDateParts = pDateStr.split('/');
         vDate.setFullYear(parseInt(vDateParts[2], 10), parseInt(vDateParts[1], 10) - 1, parseInt(vDateParts[0], 10));
         break;
	  case 'yyyy-mm-dd':
	     var vDateParts = pDateStr.split('-');
         vDate.setFullYear(parseInt(vDateParts[0], 10), parseInt(vDateParts[1], 10) - 1, parseInt(vDateParts[1], 10));
         break;
    }

    return(vDate);
    
};

JSGantt.formatDateStr = function(pDate,pFormatStr) 
{
       vYear4Str = pDate.getFullYear() + '';
 	   vYear2Str = vYear4Str.substring(2,4);
       vMonthStr = (pDate.getMonth()+1) + '';
       vDayStr   = pDate.getDate() + '';
		if(vDayStr.length==1)
			vDayStr='0'+vDayStr;
		if(vMonthStr.length==1)
			vMonthStr='0'+vMonthStr;		
      var vDateStr = "";	

      switch(pFormatStr) 
	  {
	        case 'mm/dd/yyyy':
               return( vMonthStr + '/' + vDayStr + '/' + vYear4Str );
	        case 'dd/mm/yyyy':
               return( vDayStr + '/' + vMonthStr + '/' + vYear4Str );
	        case 'yyyy-mm-dd':
               return( vYear4Str + '-' + vMonthStr + '-' + vDayStr );
	        case 'mm/dd/yy':
               return( vMonthStr + '/' + vDayStr + '/' + vYear2Str );
	        case 'dd/mm/yy':
               return( vDayStr + '/' + vMonthStr + '/' + vYear2Str );
	        case 'yy-mm-dd':
               return( vYear2Str + '-' + vMonthStr + '-' + vDayStr );
	        case 'mm/dd':
               return( vMonthStr + '/' + vDayStr );
	        case 'dd/mm':
               return( vDayStr + '/' + vMonthStr );
      }		 
	  
};


JSGantt.parseXML = function(ThisFile,pGanttVar)
{
	var is_chrome = navigator.userAgent.toLowerCase().indexOf('chrome') > -1;   // Is this Chrome 
	
	try 
	{ //Internet Explorer  
		xmlDoc=new ActiveXObject("Microsoft.XMLDOM");
	}
	catch(e) 
	{
		try 
		{ 
			if (is_chrome==false) 
			{  
				xmlDoc=document.implementation.createDocument("","",null); 
			}
		}
		catch(e) 
		{
			alert(e.message);
			return;
		}
	}

	if (is_chrome==false) 
	{ 	// can't use xmlDoc.load in chrome at the moment
		xmlDoc.async=false;
		xmlDoc.load(ThisFile);		// we can use  loadxml
		JSGantt.AddXMLTask(pGanttVar);
		xmlDoc=null;			// a little tidying
		Task = null;
	}
	else 
	{
		JSGantt.ChromeLoadXML(ThisFile,pGanttVar);	
		ta=null;	// a little tidying	
	}
};

JSGantt.AddXMLTask = function(pGanttVar)
{

	Task=xmlDoc.getElementsByTagName("task");
	
	var n = xmlDoc.documentElement.childNodes.length;	
	
	for(var i=0;i<n;i++) 
	{
		try 
		{ 
			pID = Task[i].getElementsByTagName("pID")[0].childNodes[0].nodeValue;
			
		} 
		catch (error) 
		{
			pID =0;
		}
		//pID *= 1;	// make sure that these are numbers rather than strings in order to make jsgantt.js behave as expected.

		if(pID!=0)
		{
	 		try { pName = Task[i].getElementsByTagName("pName")[0].childNodes[0].nodeValue;
			} catch (error) {pName ="No Task Name";}			// If there is no corresponding entry in the XML file the set a default.
		
			try { pColor = Task[i].getElementsByTagName("pColor")[0].childNodes[0].nodeValue;
			} catch (error) {pColor ="0000ff";}
			
			try { pParent = Task[i].getElementsByTagName("pParent")[0].childNodes[0].nodeValue;
			} catch (error) {pParent =0;}
			
	
			try { pStart = Task[i].getElementsByTagName("pStart")[0].childNodes[0].nodeValue;
			} catch (error) {pStart ="";}

			try { pEnd = Task[i].getElementsByTagName("pEnd")[0].childNodes[0].nodeValue;
			} catch (error) { pEnd ="";}

			try { pLink = Task[i].getElementsByTagName("pLink")[0].childNodes[0].nodeValue;
			} catch (error) { pLink ="";}
	
			try { pMile = Task[i].getElementsByTagName("pMile")[0].childNodes[0].nodeValue;
			} catch (error) { pMile=0;}
			pMile *= 1;

			try { pRes = Task[i].getElementsByTagName("pRes")[0].childNodes[0].nodeValue;
			} catch (error) { pRes ="";}

			try { pComp = Task[i].getElementsByTagName("pComp")[0].childNodes[0].nodeValue;
			} catch (error) {pComp =0;}
			pComp *= 1;

			try { pGroup = Task[i].getElementsByTagName("pGroup")[0].childNodes[0].nodeValue;
			} catch (error) {pGroup =0;}
			pGroup *= 1;

			try { pOpen = Task[i].getElementsByTagName("pOpen")[0].childNodes[0].nodeValue;
			} catch (error) { pOpen =1;}
			pOpen *= 1;

			try { pDepend = Task[i].getElementsByTagName("pDepend")[0].childNodes[0].nodeValue;
			} catch (error) { pDepend =0;}
			//pDepend *= 1;
			if (pDepend.length==0){pDepend=''} // need this to draw the dependency lines
			
			try { pCaption = Task[i].getElementsByTagName("pCaption")[0].childNodes[0].nodeValue;
			} catch (error) { pCaption ="";}
			try 
			{ 
				pAgregar = Task[i].getElementsByTagName("pAgregar")[0].childNodes[0].nodeValue;
				if(pAgregar=='1')
					pAgregar=true
				else
					pAgregar=false;
			} 
			catch (error) 
			{ 
				pAgregar =false;
			}
			try 
			{ 
				pEliminar = Task[i].getElementsByTagName("pEliminar")[0].childNodes[0].nodeValue;
				if(pEliminar=='1')
					pEliminar=true
				else
					pEliminar=false;
			} 
			catch (error) 
			{ 
				pEliminar =false;
			}
			try 
			{ 
				pHoras = Task[i].getElementsByTagName("pHoras")[0].childNodes[0].nodeValue;
				
			} 
			catch (error) 
			{ 
				pHoras =0;
			}
			pGanttVar.AddTaskItem(new JSGantt.TaskItem(pID , pName, pStart, pEnd, pColor,  pLink, pMile, pRes,  pComp, pGroup, pParent, pOpen, pDepend,pCaption,pAgregar,pEliminar,pHoras));
		}
	}
};


JSGantt.ChromeLoadXML = function(ThisFile,pGanttVar)
{

	XMLLoader = new XMLHttpRequest();
	XMLLoader.onreadystatechange= function(){
    JSGantt.ChromeXMLParse(pGanttVar);
	};
	XMLLoader.open("GET", ThisFile, false);
	XMLLoader.send(null);
};


JSGantt.ChromeXMLParse = function (pGanttVar)
{
	if (XMLLoader.readyState == 4) 
	{
		var ta=XMLLoader.responseText.split(/<task>/gi);

		var n = ta.length;	// the number of tasks. 
		for(var i=1;i<n;i++) 
		{
			Task = ta[i].replace(/<[/]p/g, '<p');	
			var te = Task.split(/<pid>/i);
	
			if(te.length> 2)
			{
				var pID=te[1];
			} 
			else 
			{
				var pID = 0;
			}
			//pID *= 1;
			
			var te = Task.split(/<pName>/i);
			if(te.length> 2){var pName=te[1];} else {var pName = "No Task Name";}
	
			var te = Task.split(/<pstart>/i);
			if(te.length> 2)
			{
				var pStart=te[1];
			} 
			else 
			{var pStart = "";}
	
			var te = Task.split(/<pEnd>/i);
			if(te.length> 2){var pEnd=te[1];} else {var pEnd = "";}
	
			var te = Task.split(/<pColor>/i);
			if(te.length> 2){var pColor=te[1];} else {var pColor = '0000ff';}

			var te = Task.split(/<pLink>/i);
			if(te.length> 2){var pLink=te[1];} else {var pLink = "";}
	
			var te = Task.split(/<pMile>/i);
			if(te.length> 2){var pMile=te[1];} else {var pMile = 0;}
			pMile  *= 1;
	
			var te = Task.split(/<pRes>/i);
			if(te.length> 2){var pRes=te[1];} else {var pRes = "";}	
	
			var te = Task.split(/<pComp>/i);
			if(te.length> 2){var pComp=te[1];} else {var pComp = 0;}	
			pComp  *= 1;
	
			var te = Task.split(/<pGroup>/i);
			if(te.length> 2){var pGroup=te[1];} else {var pGroup = 0;}	
			pGroup *= 1;

			var te = Task.split(/<pParent>/i);
			if(te.length> 2){var pParent=te[1];} else {var pParent = 0;}	
			
	
			var te = Task.split(/<pOpen>/i);
			if(te.length> 2){var pOpen=te[1];} else {var pOpen = 1;}
			pOpen *= 1;
	
			var te = Task.split(/<pDepend>/i);
			if(te.length> 2){var pDepend=te[1];} else {var pDepend = "";}	
			//pDepend *= 1;
			if (pDepend.length==0){pDepend=''} // need this to draw the dependency lines
			
			var te = Task.split(/<pCaption>/i);
			if(te.length> 2){var pCaption=te[1];} else {var pCaption = "";}
			
			var te = Task.split(/<pAgregar>/i);
			if(te.length> 2)
			{
				var pAgregar=te[1];
				if(pAgregar=='1')
					pAgregar=true
				else
					pAgregar=false;
			} 
			else 
			{
				var pAgregar = false;
			}
			
			var te = Task.split(/<pEliminar>/i);
			if(te.length> 2)
			{
				var pEliminar=te[1];
				if(pEliminar=='1')
					pEliminar=true
				else
					pEliminar=false;
			} 
			else 
			{
				var pEliminar = false;
			}
			var te = Task.split(/<pHoras>/i);
			if(te.length> 2)
			{
				var pHoras=te[1];
			} 
			else 
			{
				var pHoras=0;
			}
			// Finally add the task
			pGanttVar.AddTaskItem(new JSGantt.TaskItem(pID , pName, pStart, pEnd, pColor,  pLink, pMile, pRes,  pComp, pGroup, pParent, pOpen, pDepend,pCaption,pAgregar,pEliminar,pHoras));
		};
	};
};

JSGantt.benchMark = function(pItem)
{
   var vEndTime=new Date().getTime();
   alert(pItem + ': Elapsed time: '+((vEndTime-vBenchTime)/1000)+' seconds.');
   vBenchTime=new Date().getTime();
};


