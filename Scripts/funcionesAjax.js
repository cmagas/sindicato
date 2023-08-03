var PETICION_NO_INICIALIZADO = 0; 
var PETICION_CARGANDO = 1; 
var PETICION_CARGADO = 2;
var PETICION_INTERACTIVO = 3; 
var PETICION_COMPLETO = 4;
var RESPUESTA_OK=200;
var peticion_http;

var msgEspere;

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

function obtenerDatosWeb(urlADescargar,funcionAEjecutar, metodoInvocacion,parametros)
{
	peticion_http=crearMotorAjax();
	var datos=null;
	if(peticion_http)
	{
		peticion_http.onreadystatechange=funcionAjax;
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
		mostrarVentanaEspere();
	}
	
	function funcionAjax()
	{	
		if(peticion_http.readyState==PETICION_COMPLETO)
		{
			if(peticion_http.status==RESPUESTA_OK)
			{
				ocultarMensajeEspere();
				funcionAEjecutar();
			}
			else
			{
				ocultarMensajeEspere();
				msgBox('No se ha podido establecer conexi&oacute;n con la p&aacute;gina: '+urlADescargar);
				return;
			}
		}
	}
}

function mostrarVentanaEspere()
{
	try
	{
		msgEspere=Ext.MessageBox.wait(lblAplicacion,'Espere por favor...')
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
