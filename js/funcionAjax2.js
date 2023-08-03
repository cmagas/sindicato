var PETICION_NO_INICIALIZADO = 0; 
var PETICION_CARGANDO = 1; 
var PETICION_CARGADO = 2;
var PETICION_INTERACTIVO = 3; 
var PETICION_COMPLETO = 4;
var RESPUESTA_OK=200;
var peticion_http;

var msgEspere;

function crearMotorAjax2()
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

function obtenerDatosWebTecno(urlADescargar,funcionAEjecutar, metodoInvocacion,parametros,mostrarMensajeEspere)
{
	var peticion_http=crearMotorAjax2();
	var datos=null;
	if(peticion_http)
	{
		peticion_http.onreadystatechange=function()
										{
											if(peticion_http.readyState==PETICION_COMPLETO)
											{
												if(peticion_http.status==RESPUESTA_OK)
												{
													ocultarMensajeEspere();
													funcionAEjecutar(peticion_http);
												}
												else
												{
													ocultarMensajeEspere();
													msgBox('No se ha podido establecer conexi&oacute;n con la p&aacute;gina: '+urlADescargar);
													return;
												}
											}
										}

		if(metodoInvocacion.toUpperCase()=="GET")
			urlADescargar=urlADescargar+"?"+parametros;
		else
			datos=parametros;
		peticion_http.open(metodoInvocacion,urlADescargar,true);
		if(metodoInvocacion.toUpperCase()=="POST")
		{
			peticion_http.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
		}
		
		peticion_http.send(datos);
		if((typeof(mostrarMensajeEspere)=='undefined')||(mostrarMensajeEspere))
			mostrarVentanaEspere();
	}
}

function mostrarVentanaEspere()
{
	try
	{
		//msgEspere=Ext.MessageBox.wait('Espere por favor...',lblAplicacion)
	}
	catch(err)
	{
		
	}
}

function ocultarMensajeEspere()
{
	try
	{
		msgEspere.hide()
	}
	catch(err)
	{
		
	}
}