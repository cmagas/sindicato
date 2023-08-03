var respXML;
function tratarContenido()
{		
	var inicio;
	var cita;
	var elementosCita;
	
	if(peticion_http.readyState==PETICION_COMPLETO)
	{
		if(peticion_http.status==RESPUESTA_OK)
		{
			mE("LblResult");
			mE("RespCitas");
			respXML=peticion_http.responseXML;
			inicio=respXML.getElementsByTagName("cita");
			gE("RespCitas").innerHTML="";
			for(x=0;x<inicio.length;x++)
			{
				cita=inicio[x];
				elementosCita=cita.childNodes;
				gE("RespCitas").innerHTML=gE("RespCitas").innerHTML+"<a class=\"enlace\" href=javascript:mostrarFicha('"+elementosCita[0].firstChild.nodeValue+"')>Cita #"+elementosCita[0].firstChild.nodeValue+"</a><BR><br>";
					
			}
			
		}
	}
}

function analizarCitas(citas)
{
	
	if(citas.value!="")
		obtenerDatosWeb("http://localhost:8080/citas/separa_cita5.php",tratarContenido,"POST","cita="+document.getElementById(citas).value+"&enviar=Enviar",false);	
	
}

function inicializar()
{
	oE("LblResult");
	oE("RespCitas");
}


function ocultarVentana(idElemento)
{
	oE("fondo1");
	oE(idElemento);
}

function mostrarFicha(idCita)
{
	var inicio=respXML.getElementsByTagName("cita");
	var elementos;
	var cita;
	var ct=0;
	var enc=0;
	var autores;
	var nAutor;
	while((ct<inicio.length)&&(enc!=1))
	{
		cita=inicio[ct];
		elementos=cita.childNodes;
		if(elementos[0].firstChild.nodeValue==idCita)
			enc=1;
		else
			ct++;
	}
	if(enc==1)
	{
		autores=elementos[1].childNodes;
		gE("txtAutores").value="";
		for(y=0;y<autores.length;y++)
		{
			nAutor=autores[y].childNodes[1].firstChild.nodeValue+"-"+autores[y].childNodes[2].firstChild.nodeValue+" "+autores[y].childNodes[3].firstChild.nodeValue;
			gE("txtAutores").value+=nAutor+"\n";
		}
		gE('txtTitulo').value=elementos[2].firstChild.nodeValue;
		gE('txtRevista').value=elementos[3].firstChild.nodeValue;
		gE('txtFecha').value=elementos[4].firstChild.nodeValue;
		gE('txtVolumen').value=elementos[5].firstChild.nodeValue;
		gE('txtNumero').value=elementos[6].firstChild.nodeValue;
		gE('txtPaginas').value=elementos[7].firstChild.nodeValue;
		//mostrarFondo();
		var vent=gE('ventana1');
		vent.style.left=((obtenerAncho()/2)-270)+"px";
		vent.style.top=((obtenerAlto()/2)-155)+"px";
		mostrarFondo();
		mE('ventana1');
	}
}

function mostrarFondo()
{
	gE('fondo1').style.height=obtenerAlto()+"px";
	gE('fondo1').style.width=obtenerAncho()+"px";
	mE('fondo1');
}

function obtenerAlto()
{

	var altoVent=document.all?document.body.offsetHeight+100:window.innerHeight; 
	return altoVent;
}

function obtenerAncho()
{
	var anchoVent=document.all?document.body.offsetWidth+100:window.innerWidth;
	return 	anchoVent;
}

function funcSoloNumeros()
{
	return true;
}

function funcNumerosFecha()
{
	return true;
}






