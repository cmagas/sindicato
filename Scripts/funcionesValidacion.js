var nav4 = window.Event ? true : false;

String.prototype.trim = function() 
{
	return this.replace(/^\s+|\s+$/g,"");
}

String.prototype.ltrim = function() 
{
	return this.replace(/^\s+/g,"");
}

String.prototype.rtrim = function() 
{
	return this.replace(/\s+$/g,"");
} 

function ponerFocoControlValidacion(control)
{
	var classControl;
	var idExt=control.getAttribute('extId');
	if(!esHidden(control))
	{
		var idTab=control.getAttribute('jQUI');
		var noTab=parseInt(control.getAttribute('noTab'));
		if(idTab)
		{
			$( "#"+idTab ).tabs( "option", "active", noTab );   	
		}
		classControl=control.getAttribute('class');
		if(classControl.indexOf('ctrlSeleccionado')==-1)
		{
			control.setAttribute('class',classControl+' ctrlSeleccionado');
			asignarEvento(control,'change',function()
											{
												classControl=control.getAttribute('class').replace('ctrlSeleccionado','');
												control.setAttribute('class',classControl);
												
											}
							)
			
		}
		control.focus();
	}
	else
	{
		if(idExt!=null)
		{

			if(control.getAttribute('autocompletarext')!=null)
			{
					
				var tdControl=gE('tdContenedor_'+control.id);
				tdControl.setAttribute('class','ctrlSeleccionado');
				Ext.getCmp(idExt).on('change',function(cmb,registro)
												{
													var tdControl=gE('tdContenedor_'+control.id);
													tdControl.setAttribute('class','');
												}
									);
			}
			else
			{

				Ext.getCmp(idExt).addClass('ctrlSeleccionado');
				Ext.getCmp(idExt).on('change',function(cmb,registro)
												{
													cmb.removeClass( 'ctrlSeleccionado' ); 
												}
									);
			}
			Ext.getCmp(idExt).focus(true,10);
		}
		else
		{
			var controlSelVacio=control.getAttribute('controlSelVacio');
			if(controlSelVacio)
			{
				
				var tdControl=gE(controlSelVacio);
				tdControl.setAttribute('class','ctrlSeleccionado');
				
				asignarEvento(control,'change',function()
											{
												
												tdControl.setAttribute('class','');
												
											}
							)
				
			}
			else
			{
				var idControlF=control.getAttribute('controlF');
				if(idControlF!=null)
				{
					var arrDatosControl=idControlF.split('_');
					gE(idControlF).focus();
					
					if(arrDatosControl[0]=='opt')
					{
						var tdControl=gE('tbl_'+arrDatosControl[1]);
						tdControl.setAttribute('class','ctrlSeleccionado');
						
						asignarEvento(control,'change',function()
											{
												
												tdControl.setAttribute('class','');
												
											}
							)
						
					}
					
				}
				else
				{

					var idControlF=control.getAttribute('richText');
					
					if(idControlF!=null)
					{
						if(Ext.getCmp(idControlF))
						{
							var editor=Ext.getCmp(idControlF).getInnerEditor();
							editor.Focus();
						}
						else
						{
							var tdControl=gE('tdContenedor_'+control.id);
							tdControl.setAttribute('class','ctrlSeleccionado');
							CKEDITOR.instances[idControlF].focus();
							CKEDITOR.instances[idControlF].on('change', function() 
																		{ 
																			classControl=tdControl.getAttribute('class').replace('ctrlSeleccionado','');
																			tdControl.setAttribute('class',classControl);
																				
																		}
															);
							
						}
						
					}
				}
			}
		}
	}
}

function msg(mensaje,control)
{
	
	if(typeof keyMap!='undefined')
		keyMap.disable();
	function funcOKFoco()
	{	
		
		ponerFocoControlValidacion(control);
		
		if(typeof keyMap!='undefined')
			keyMap.enable();
	}
	msgBox(mensaje,funcOKFoco);
}

function getNomCampo(control)
{
	var nCampo=control.getAttribute('campo');
	if((nCampo==null)||(nCampo=='')||(nCampo=='undefined'))
		nCampo='';//nCampo=control.id.substring(3);

	return nCampo;
}

function validarFormularios(nContenedor)
{
	
	var contenedor=document.getElementById(nContenedor);
	return comenzarValidacion(contenedor);
}

function comenzarValidacion(contenedor)
{
	var x;
	var control;
	var validacion='';
	var valorValidar;
	for(x=0;x<contenedor.childNodes.length;x++)
	{
		control=contenedor.childNodes[x];
		
		if((control.name!=null)&&(control.name!='undefined')&&(control.name!=''))
		{
			validacion=control.getAttribute('val');
			
			if(validacion!=null)
			{
				
				if(!validarControl(control))
					return false;
			}
		}
		if(!comenzarValidacion(control))
			return false;
	}
	return true;
}

function obtenerValor(control)
{
	var tipo=control.nodeName;
	switch(tipo)
	{
		case 'TEXTAREA':
		case 'INPUT':
			return control.value;
		break;
		case 'SELECT':
			return control.selectedIndex;
		break;
	}
}

function validarControl(control)
{
	var cadValidacion=control.getAttribute('val');
	var tValidacion='';
	var res;

	if(cadValidacion!='')
	{
		tValidacion=cadValidacion.split('|');
		var ct=0;
		for(ct=0;ct<tValidacion.length;ct++)
		{
			switch(tValidacion[ct])
			{
				case 'obl':
					res=esVacio(control);
				break;
				case 'txt':
					res=true;
				break;
				case 'num':
					res=esValorNumero(control);
				break;
				case 'flo':
					res=esFlotante(control);
				break;
				case 'dte':
					res=esFechaValida(control);
				break;
				case 'mail':
					res=esCorreoValido(control);
				break;
				case 'min': //seleccion minima
					res=cumpleMinSeleccion(control);
				break
				default:
					res=true;
				break;
			}
			if(res==false)
				return false;
		}
	}
	return true;
}

////////////
function esVacio(control)
{
	
	var tipo=control.nodeName;
	var valor;
	var res;
	
	switch(tipo)
	{
		case 'TEXTAREA':
		case 'INPUT':
			valor=control.value;
			
			if(control.getAttribute('ceroVacio')!=null)
			{
				if((valor.trim()=='')||(parseFloat(valor.trim())==0))
				{
					if(control.getAttribute('valOriginal')==null)
						res=false;
					
					if(control.getAttribute('valOriginal')=='')	
					{
						res=false;
					}
					
					if(control.getAttribute('valOriginal')!='')	
					{
						res=true;
					}
				}
				else
					res=true;
			}
			else
			{
				
				if(valor.trim()=='')
				{
					if(control.getAttribute('valOriginal')==null)
						res=false;
					else
						if(control.getAttribute('valOriginal')=='')	
						{
							res=false;
						}
						else
							if(control.getAttribute('valOriginal')!='')	
							{
								res=true;
							}
				}
				else
					res=true;
			}
			
			
			if(res)
			{
				if(control.getAttribute('autocompletarExt'))
				{
					if(gEx(control.getAttribute('extId')).getRawValue().trim()=='')
						res=false;
				}
			}
		break;
		case 'SELECT':
			valor="";
			if(control.selectedIndex>=0)
				valor=control.options[control.selectedIndex].value;
			if((control.selectedIndex==-1)||(valor==-1)||(valor=="-1"))
				res=false;
			else
				res=true;
		break;
	}
	
	if(res==false)
	{
		var leyenda='';
		
		if(control.getAttribute('msgValorNoValido')==null)
		{
			
			var nomCamp=getNomCampo(control);
			if(nomCamp!='')
				nomCamp='"'+nomCamp+'" ';
			else
				nomCamp=control.id;
			nomCamp='';
			leyenda='El campo '+((nomCamp=='')?'':':'+nomCamp)+'es obligatorio';
			console.log(control);
		}
		else
		{
			leyenda='El valor ingresado no es v&aacute;lido';
		}
		
		
		msg(leyenda,control);
	}
	return res;
}


function esValorNumero(control)
{
	var valor=obtenerValor(control);
	if(valor=='')
		valor='0';
	valorEnt=parseInt(normalizarValor(valor));
	if((isNaN(valorEnt))||(valor.indexOf('.')>=0))
	{
		var nomCamp=getNomCampo(control);
		if(nomCamp!='')
			nomCamp='"'+nomCamp+'" ';

		var leyenda='El valor ingresado en el campo'+((nomCamp!='')?':'+nomCamp:'')+' no es válido';

		msg(leyenda,control);
		return false;
	}
	else
		return true;
}

function esFlotante(control)
{
	var valor=obtenerValor(control);
	if(valor=='')
		valor='0';
	valorEnt=parseFloat(normalizarValor(valor));
	if(isNaN(valorEnt))
	{
		var nomCamp=getNomCampo(control);
		if(nomCamp!='')
			nomCamp='"'+nomCamp+'" ';

		var leyenda='El valor ingresado en el campo'+((nomCamp!='')?':'+nomCamp:'')+' no es válido';

		msg(leyenda,control);

		
		return false;
	}
	else
		return true;
}

function esFechaValida(control)
{  
	var Cadena=obtenerValor(control);
	var Fecha= new String(Cadena);   
	var RealFecha= new Date();   
    var Ano= new String(Fecha.substring(Fecha.lastIndexOf("/")+1,Fecha.length));  
    var Mes= new String(Fecha.substring(Fecha.indexOf("/")+1,Fecha.lastIndexOf("/")));
    var Dia= new String(Fecha.substring(0,Fecha.indexOf("/")));
   
    if (isNaN(Ano) || Ano.length<4 || parseFloat(Ano)<1900)
	{
		msg('La fecha ingresada no es válida',control);
		return false  
	}
    if (isNaN(Mes) || parseFloat(Mes)<1 || parseFloat(Mes)>12)
	{
		msg('La fecha ingresada no es válida',control);
		return false  
	}
     
    if (isNaN(Dia) || parseInt(Dia, 10)<1 || parseInt(Dia, 10)>31)
	{
		msg('La fecha ingresada no es válida',control);
	    return false  
	}
    if (Mes==4 || Mes==6 || Mes==9 || Mes==11 || Mes==2) 
	{  
    	if (Mes==2 && Dia > 28 || Dia>30) 
		{
			msg('La fecha ingresada no es válida',control);
			return false  
		}
    }  
   return true;    
} 

function esCorreoValido(control)
{
	var valor=obtenerValor(control);
	var filter=/^[A-Za-z0-9\.\_\-]+@[A-Za-z0-9\.\_\-]+(\.[A-Za-z]+){1,2}$/;
	if(valor.length == 0 ) 
		return true;
	if (filter.test(valor))
		return true;
	else
		msg('La dirección de correo electrónico ingresada no es válida',control);
	return false;
}

function cumpleMinSeleccion(control)
{
	var minSelPermitido=parseInt(control.getAttribute('minSel'));
	
	var numElemSel=parseInt(gE('numSel'+control.id).value);
	
	
	
	if(numElemSel<0)
		numElemSel=0;
	if(numElemSel>=minSelPermitido)
		return	true;
	else
	{
		msg('Al menos debe seleccionar '+minSelPermitido+' elementos del listado',control);
		return false;
	}
}

function esHidden(control)
{
	var tipo=control.nodeName;
	if(tipo=='INPUT')
	{
		if(control.type=='hidden')
			return true;
		else
			return false;
	}
}

function soloNumero(evt,decimal,guion,control,maxDecimales) //onkeypress="return soloNumero(event,false,false,this)"
{
	//var key = nav4 ? evt.which : evt.keyCode;
	var key= evt.which;
	if(Ext.isIE)
		key=evt.keyCode;
	var res;
	
	if(decimal)
	{
		if((control.value.indexOf('.')!=-1)&&(maxDecimales!=undefined))
		{
			var arrDatos=control.value.split('.');
			if(arrDatos[1].length>=parseFloat(maxDecimales))
			{
				return false;
			}
		}
		
		res= ((key <= 13) || (key >= 48 && key <= 57) || ((key == 46)&&(control.value.indexOf('.')==-1)))
	}
	else
		res= (key <= 13 || (key >= 48 && key <= 57));
	if((res==false)&&(key==45)&&(guion==true))
		return true;
	return res;	
	
}

function soloLetrasNumeros(evt)
{
	var key = nav4 ? evt.which : evt.keyCode;
	var res;
	res= ((key <= 13) || (key >= 48 && key <= 57)||(key==32)||((key>=65) &&(key<=90))||((key>=97) &&(key<=122)));
	return res;	
}

function validarCorreo(mail)
{
	var valor=mail;
	var filter=/^[A-Za-z0-9\._\-]+@[A-Za-z0-9_\-]+(\.[A-Za-z]+){1,2}$/;
	if (valor.length == 0 ) 
		return false;
	if (filter.test(valor))
		return true;
	return false;
}

function validarIP(ip) 
{
    partes=ip.split('.');
    if (partes.length!=4) 
	    return false;
    for (i=0;i<4;i++) 
	{
        num=partes[i];
        if (num>255 || num<0 || num.length==0 || isNaN(num))
	        return false;
    }
    return true
} 

function vFormulario(idFrm)
{
	var formulario;
	if(idFrm!=undefined)
		formulario=idFrm;
	else
		formulario='frmEnvio';
	if(validarFormularios(formulario))
		gE(formulario).submit();
}

function soloLetras(evt)
{
	var key = nav4 ? evt.which : evt.keyCode;
	var res;
	res= ((key <= 13) || (key >= 48 && key <= 57)||(key==32)||((key>=65) &&(key<=90))||((key>=97) &&(key<=122)));
	return res;	
}
