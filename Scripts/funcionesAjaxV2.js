var PETICION_NO_INICIALIZADO = 0; 
var PETICION_CARGANDO = 1; 
var PETICION_CARGADO = 2;
var PETICION_INTERACTIVO = 3; 
var PETICION_COMPLETO = 4;
var RESPUESTA_OK=200;
var peticion_http;

var msgEspereAjaxv2;

function crearMotorAjax()
{
	var motorAjax;
	if(window.XMLHttpRequest)
	{
		motorAjax=new XMLHttpRequest();
	}
	else
	{
		if(window.ActiveXObject)
		{
			motorAjax=new ActiveXObject("Microsoft.XMLHTTP");
		}
	}
	return motorAjax;
}

function obtenerDatosWebV2(urlADescargar,funcionAEjecutar, metodoInvocacion,parametros,mostrarMensajeEspere)
{
	var peticion_http=crearMotorAjax();
	var datos=null;
	if(peticion_http)
	{
		peticion_http.onreadystatechange=function()
										{
											if(peticion_http.readyState==PETICION_COMPLETO)
											{
												if(peticion_http.status==RESPUESTA_OK)
												{
													
													ocultarMensajeEspereAjaxV2();
													
													if(peticion_http.responseText.indexOf('c2VzaW9uQ2FkdWNhZGExMDA1ODQ=')>-1)
													{
														function resp()
														{
															irPaginaLogin();
														}
														msgBox('La sesi&oacute;n ha caducado',resp);
														return;
													}
													
													
													funcionAEjecutar(peticion_http);
												}
												else
												{
													ocultarMensajeEspereAjaxV2();
													msgBox('No se ha podido establecer conexi&oacute;n con la p&aacute;gina: '+urlADescargar);
													return;
												}
											}
		
										}
		if(metodoInvocacion.toUpperCase()=="GET")
			urlADescargar=urlADescargar+"?"+parametros;
		else
			datos=parametros;
		
		if((typeof(mostrarMensajeEspere)=='undefined')||(mostrarMensajeEspere))
		{

			mostrarVentanaEspereAjaxV2();	
		}
		peticion_http.open(metodoInvocacion,urlADescargar,true);
		if(metodoInvocacion.toUpperCase()=="POST")
		{
			peticion_http.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
		}
		peticion_http.send(datos);
		
	}
	
}

function mostrarVentanaEspereAjaxV2()
{
	
	msgEspereAjaxv2=Ext.MessageBox.wait('Espere por favor...',lblAplicacion)
	
}

function ocultarMensajeEspereAjaxV2()
{
	if(msgEspereAjaxv2)
		msgEspereAjaxv2.hide()
	
}


function obtenerDatosWebSincrono(urlADescargar,funcionAEjecutar, metodoInvocacion,parametros,mostrarMensajeEspere)
{
	var peticion_http=crearMotorAjax();
	var datos=null;
	if(peticion_http)
	{
		peticion_http.onreadystatechange=function()
										{
											if(peticion_http.readyState==PETICION_COMPLETO)
											{
												if(peticion_http.status==RESPUESTA_OK)
												{
													
													ocultarMensajeEspereAjaxV2();
													funcionAEjecutar(peticion_http);
												}
												else
												{
													ocultarMensajeEspereAjaxV2();
													msgBox('No se ha podido establecer conexi&oacute;n con la p&aacute;gina: '+urlADescargar);
													return;
												}
											}
		
										}
		if(metodoInvocacion.toUpperCase()=="GET")
			urlADescargar=urlADescargar+"?"+parametros;
		else
			datos=parametros;
		
		if((typeof(mostrarMensajeEspere)=='undefined')||(mostrarMensajeEspere))
		{

			mostrarVentanaEspereAjaxV2();	
		}
		peticion_http.open(metodoInvocacion,urlADescargar,true);
		if(metodoInvocacion.toUpperCase()=="POST")
		{
			peticion_http.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
		}
		peticion_http.send(datos);
		
	}
	
}

function obtenerDatosWebArchivos(urlADescargar,funcionAEjecutar, metodoInvocacion,parametros,mostrarMensajeEspere)
{
	var peticion_http_local=crearMotorAjax();
	var datos=null;
	if(peticion_http_local)
	{
		peticion_http_local.onreadystatechange=function()
										{
											if(peticion_http_local.readyState==PETICION_COMPLETO)
											{
												if(peticion_http_local.status==RESPUESTA_OK)
												{
													ocultarMensajeEspereAjaxV2();
													funcionAEjecutar(peticion_http_local);
												}
												else
												{
													ocultarMensajeEspereAjaxV2();
													msgBox('No se ha podido establecer conexi&oacute;n con la p&aacute;gina: '+urlADescargar);
													return;
												}
											}
		
										}
		
		
		if((typeof(mostrarMensajeEspere)=='undefined')||(mostrarMensajeEspere))
		{
			mostrarVentanaEspereAjaxV2();	
		}
		peticion_http_local.open(metodoInvocacion,urlADescargar,true);
		//peticion_http.setRequestHeader("Content-Type","multipart/form-data");
		peticion_http_local.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
		peticion_http_local.send(parametros);
		
	}
	
}
