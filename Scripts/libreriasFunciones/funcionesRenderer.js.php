<?php
	session_start();
	include("configurarIdiomaJS.php");
	include("conexionBD.php");
	
	$consulta="select codigoUnidad,unidad from 817_organigrama order by unidad";
	$arrOrganigrama=$con->obtenerFilasArreglo($consulta);
	
	$consulta="SELECT idTipoCarpeta,nombreTipoCarpeta FROM 7020_tipoCarpetaAdministrativa";
	$arrTipoCarpeta=$con->obtenerFilasArreglo($consulta);
	
	
	
	
?>

function mod(dividendo , divisor) 
{ 
  resDiv = dividendo / divisor ;  
  parteEnt = Math.floor(resDiv);            // Obtiene la parte Entera de resDiv 
  parteFrac = resDiv - parteEnt ;      // Obtiene la parte Fraccionaria de la división
  modulo = Math.round(parteFrac * divisor);  // Regresa la parte fraccionaria * la división (modulo) 
  return modulo; 
} // Fin de función mod

// Función ObtenerParteEntDiv, regresa la parte entera de una división
function ObtenerParteEntDiv(dividendo , divisor) 
{ 
  resDiv = dividendo / divisor ;  
  parteEntDiv = Math.floor(resDiv); 
  return parteEntDiv; 
} // Fin de función ObtenerParteEntDiv

// function fraction_part, regresa la parte Fraccionaria de una cantidad
function fraction_part(dividendo , divisor) 
{ 
  resDiv = dividendo / divisor ;  
  f_part = Math.floor(resDiv); 
  return f_part; 
} // Fin de función fraction_part
 
function string_literal_conversion(number) 
{   
   // first, divide your number in hundreds, tens and units, cascadig 
   // trough subsequent divisions, using the modulus of each division 
   // for the next. 

   centenas = ObtenerParteEntDiv(number, 100); 
   
   number = mod(number, 100); 

   decenas = ObtenerParteEntDiv(number, 10); 
   number = mod(number, 10); 

   unidades = ObtenerParteEntDiv(number, 1); 
   number = mod(number, 1);  
   string_hundreds="";
   string_tens="";
   string_units="";
   // cascade trough hundreds. This will convert the hundreds part to 
   // their corresponding string in spanish.
   if(centenas == 1){
      string_hundreds = "ciento ";
   } 
   
   
   if(centenas == 2){
      string_hundreds = "doscientos ";
   }
    
   if(centenas == 3){
      string_hundreds = "trescientos ";
   } 
   
   if(centenas == 4){
      string_hundreds = "cuatrocientos ";
   } 
   
   if(centenas == 5){
      string_hundreds = "quinientos ";
   } 
   
   if(centenas == 6){
      string_hundreds = "seiscientos ";
   } 
   
   if(centenas == 7){
      string_hundreds = "setecientos ";
   } 
   
   if(centenas == 8){
      string_hundreds = "ochocientos ";
   } 
   
   if(centenas == 9){
      string_hundreds = "novecientos ";
   } 
   
 // end switch hundreds 

   // casgade trough tens. This will convert the tens part to corresponding 
   // strings in spanish. Note, however that the strings between 11 and 19 
   // are all special cases. Also 21-29 is a special case in spanish. 
   if(decenas == 1){
      //Special case, depends on units for each conversion
      if(unidades == 1){
         string_tens = "once";
      }
      
      if(unidades == 2){
         string_tens = "doce";
      }
      
      if(unidades == 3){
         string_tens = "trece";
      }
      
      if(unidades == 4){
         string_tens = "catorce";
      }
      
      if(unidades == 5){
         string_tens = "quince";
      }
      
      if(unidades == 6){
         string_tens = "dieciseis";
      }
      
      if(unidades == 7){
         string_tens = "diecisiete";
      }
      
      if(unidades == 8){
         string_tens = "dieciocho";
      }
      
      if(unidades == 9){
         string_tens = "diecinueve";
      }
   } 
   //alert("STRING_TENS ="+string_tens);
   
   if(decenas == 2){
      string_tens = "veinti";

   }
   if(decenas == 3){
      string_tens = "treinta";
   }
   if(decenas == 4){
      string_tens = "cuarenta";
   }
   if(decenas == 5){
      string_tens = "cincuenta";
   }
   if(decenas == 6){
      string_tens = "sesenta";
   }
   if(decenas == 7){
      string_tens = "setenta";
   }
   if(decenas == 8){
      string_tens = "ochenta";
   }
   if(decenas == 9){
      string_tens = "noventa";
   }
   
    // Fin de swicth decenas


   // cascades trough units, This will convert the units part to corresponding 
   // strings in spanish. Note however that a check is being made to see wether 
   // the special cases 11-19 were used. In that case, the whole conversion of 
   // individual units is ignored since it was already made in the tens cascade. 

   if (decenas == 1) 
   { 
      string_units="";  // empties the units check, since it has alredy been handled on the tens switch 
   } 
   else 
   { 
      if(unidades == 1){
         string_units = "un";
      }
      if(unidades == 2){
         string_units = "dos";
      }
      if(unidades == 3){
         string_units = "tres";
      }
      if(unidades == 4){
         string_units = "cuatro";
      }
      if(unidades == 5){
         string_units = "cinco";
      }
      if(unidades == 6){
         string_units = "seis";
      }
      if(unidades == 7){
         string_units = "siete";
      }
      if(unidades == 8){
         string_units = "ocho";
      }
      if(unidades == 9){
         string_units = "nueve";
      }
       // end switch units 
   } // end if-then-else 
   

//final special cases. This conditions will handle the special cases which 
//are not as general as the ones in the cascades. Basically four: 

// when you've got 100, you dont' say 'ciento' you say 'cien' 
// 'ciento' is used only for [101 >= number > 199] 
if (centenas == 1 && decenas == 0 && unidades == 0) 
{ 
   string_hundreds = "cien " ; 
}  

// when you've got 10, you don't say any of the 11-19 special 
// cases.. just say 'diez' 
if (decenas == 1 && unidades ==0) 
{ 
   string_tens = "diez " ; 
} 

// when you've got 20, you don't say 'veinti', which is used 
// only for [21 >= number > 29] 
if (decenas == 2 && unidades ==0) 
{ 
  string_tens = "veinte " ; 
} 

// for numbers >= 30, you don't use a single word such as veintiuno 
// (twenty one), you must add 'y' (and), and use two words. v.gr 31 
// 'treinta y uno' (thirty and one) 
if (decenas >=3 && unidades >=1) 
{ 
   string_tens = string_tens+" y "; 
} 

// this line gathers all the hundreds, tens and units into the final string 
// and returns it as the function value.
final_string = string_hundreds+string_tens+string_units;


return final_string ; 

} 

function covertirNumLetras(number)
{
   var number=number+'';
  //number = number_format (number, 2);
   number1=number; 
   //settype (number, "integer");
   cent = number1.split(".");   
   centavos = cent[1];
   
   
   if (centavos == 0 || centavos == undefined){
   centavos = "00";}

   if (number == 0 || number == "") 
   { // if amount = 0, then forget all about conversions, 
      centenas_final_string=" cero "; // amount is zero (cero). handle it externally, to 
      // function breakdown 
  } 
   else 
   { 
   
     millions  = ObtenerParteEntDiv(number, 1000000); // first, send the millions to the string 
      number = mod(number, 1000000);           // conversion function 
      
     if (millions != 0)
      {                      
      // This condition handles the plural case 
         if (millions == 1) 
         {              // if only 1, use 'millon' (million). if 
            descriptor= " millon ";  // > than 1, use 'millones' (millions) as 
            } 
         else 
         {                           // a descriptor for this triad. 
              descriptor = " millones "; 
            } 
      } 
      else 
      {    
         descriptor = " ";                 // if 0 million then use no descriptor. 
      } 
      millions_final_string = string_literal_conversion(millions)+descriptor; 
          
      
      thousands = ObtenerParteEntDiv(number, 1000);  // now, send the thousands to the string 
        number = mod(number, 1000);            // conversion function. 
      //print "Th:".thousands;
     if (thousands != 1) 
      {                   // This condition eliminates the descriptor 
         thousands_final_string =string_literal_conversion(thousands) + " mil "; 
       //  descriptor = " mil ";          // if there are no thousands on the amount 
      } 
      if (thousands == 1)
      {
         thousands_final_string = " mil "; 
     }
      if (thousands < 1) 
      { 
         thousands_final_string = " "; 
      } 
  
      // this will handle numbers between 1 and 999 which 
      // need no descriptor whatsoever. 

     centenas  = number;                     
      centenas_final_string = string_literal_conversion(centenas) ; 
      
   } //end if (number ==0) 

   /*if (ereg("un",centenas_final_string))
   {
     centenas_final_string = ereg_replace("","o",centenas_final_string); 
   }*/
   //finally, print the output. 

   /* Concatena los millones, miles y cientos*/
   cad = millions_final_string+thousands_final_string+centenas_final_string; 
   
   /* Convierte la cadena a Mayúsculas*/
   cad = cad.toUpperCase();       

   if (centavos.length>2)
   {   
      if(centavos.substring(2,3)>= 5){
         centavos = centavos.substring(0,1)+(parseInt(centavos.substring(1,2))+1).toString();
      }   else{
        centavos = centavos.substring(0,2);
       }
   }

   /* Concatena a los centavos la cadena "/100" */
   if (centavos.length==1)
   {
      centavos = centavos+"0";
   }
   centavos = centavos+ "/100"; 
	centavos="";
    moneda="";

   /* Asigna el tipo de moneda, para 1 = PESO, para distinto de 1 = PESOS*/
   if (number == 1)
   {
     // moneda = " PESO ";  
   }
   else
   {
      //moneda = " PESOS ";  
   }
   /* Regresa el número en cadena entre paréntesis y con tipo de moneda y la fase M.N.*/
   return cad+moneda+centavos;
   
}

function convertirNumeroLetraMoneda(valor)
{
	var valorNormalizado=parseFloat(normalizarValor(valor));
  
	var texto='';
	var centavos=Math.round(valorNormalizado * 100)-Math.floor(valorNormalizado)*100;
    if (centavos>0)
    	texto=covertirNumLetras(valorNormalizado).toUpperCase()+" PESOS "+ centavos +"/100 M.N.";
    else
    	texto=covertirNumLetras(valorNormalizado).toUpperCase()+" PESOS 00/100 M.N.";
    return texto;
}

function formatearAsMoneda(val)
{
	val=normalizarValor(val);
	if(val=='')
    	val=0;
	return Ext.util.Format.usMoney(val);
}


function obtenerFotoUsuario(valor)
{
	return '<img height="120" width="100" src="../Usuarios/verFoto.php?Id='+valor+'"/>	';
}

function formatearValorNoAplica(val)
{
	val=val.trim();
	if((val=='-1')||(val==''))
    {
    	return 'No aplica';
    }
    return val;
}

function formatearCodigoUnidadPlantel(val)
{
	var arrOrganigrama=<?php echo $arrOrganigrama?>;
    return formatearValorRenderer(arrOrganigrama,val);
}

function obtenerEmailUsuario(valor,ctrl)
{
	if(valor=='')
    {
    	return '';
    }

	function funcAjax()
    {
        var resp=peticion_http.responseText;
        arrResp=resp.split('|');
        if(arrResp[0]=='1')
        {
            if(arrResp[1]=='')
            	ctrl.innerHTML= '<img src="../images/exclamation.png" width="13" height="13" />&nbsp;<span class="letraRoja">Sin correo electr&oacute;nico asociado</span>';
            else
            	ctrl.innerHTML='<span class="letraRojaSubrayada8">'+arrResp[1]+'</span>';
        }
        else
        {
            msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
        }
    }
    obtenerDatosWeb('../paginasFunciones/funcionesUsuarios.php',funcAjax, 'POST','funcion=102&iU='+valor,true);
    return '';
}

function formatearFolio(val)
{

	if(val.indexOf('-')!=-1)
		return val.replace(/'/gi,'');    
    else
    	return '[A&uacute;n NO asignado]';
}

function formatearTextoRetornoCarro(val)
{
	return val.replace(/\r/gi,'<br />');
}

function removerComillasContenedorasCadena(val)
{
	var cad='';
    if(val.substring(0,1)=="'")
    {
    	cad=val.substring(1);
    }
    else
    	cad=val;
  
    if(cad.substring(cad.length-1)=="'")
    {
    	cad=cad.substring(0,cad.length-1);
    }
    return cad;
}

function formatearPorcentajeDecimal(val)
{
	return Ext.util.Format.number((parseFloat(val)/10),"0.00");
}


function formatearPorcentajeDecimalCalificaciones(val)
{
	var valor=parseFloat(val);
    if(valor>=0)
		return Ext.util.Format.number((valor/10),"0.00");
    else
    	return formatearValorRenderer(arrAbreviatura,(valor*-1),1,true)
}

function descomponerValoresVarios(val)
{
	var arrVarios=val.split('_');
    return arrVarios[0];
}

function removerParentesisVacios(val)
{
	if(val.indexOf('Otra población en desigualdad')!=-1)
    {
    	return 'Otra';
    }
	if(val.indexOf('() ')!=-1)
    {
    	var arrDatos=val.split('() ');
        return arrDatos[1];
    }
    return val;
}


function estiloProyectos(val)
{
	return '<span class="tituloParrafoV2">'+val+'</span>';
}

function fechaEstiloProyectos(val)
{
	return '<span class="tituloParrafoV2">'+Date.parseDate(val,'Y-m-d H:i:s').format('d/m/Y H:i')+'</span>';
}

function formatearCampoFormulario(val)
{

	var valor=removerComillasContenedorasCadena(val);
	if((valor=='')||(valor=='N/E'))
    	valor='POR ASIGNAR';
    
    return valor;
}

function formatearCampoFormularioFecha(val)
{
	var valor=removerComillasContenedorasCadena(val);
	if((valor=='')||(valor=='N/E'))
    	return 'POR ASIGNAR';
    
    if(Date.parseDate(valor,'Y-m-d H:i:s'))
    	return Date.parseDate(valor,'Y-m-d H:i:s').format('d/m/Y H:i:s');
    else
    	if(Date.parseDate(valor,'Y-m-d'))
        	return Date.parseDate(valor,'Y-m-d').format('d/m/Y');
        else
    		if(Date.parseDate(valor,'H:i:s'))
        		return Date.parseDate(valor,'H:i:s').format('H:i')+' hrs.';
        
    
   
}

function escaparComaBR(val)
{
	return val.replace(/,/gi,'<br />');
}


function formatearStatusNotificacion(val)
{
	switch(val)
    {
    	case '1':
        	return 'En espera de atenci&oacute;n';
        break;
        case '2':
        	return 'Tareas atendidas';
        break;
    }
}

function formatearCampoFecha(val)
{
	var valor=removerComillasContenedorasCadena(val);
	if(valor=='')
    	return '';
    
    return Date.parseDate(valor,'Y-m-d H:i:s').format('d/m/Y H:i:s');
}

function formatearCampoNoEspecificado(val)
{
	if(val.trim()=='')
    	return '(No especificado)';
    return val;
}

function removerComillasSimples(val)
{
	return removerComillasContenedorasCadena(val);
}

function formatearFechaEvento(val)
{
	var tipoAudiencia='';
    var x;
    
	var arrDatos=removerComillasContenedorasCadena(val).split(' ');
    if(arrDatos.length>1)
    {
    	for(x=2;x<arrDatos.length;x++)
        {
        	if(tipoAudiencia=='')
            	tipoAudiencia=arrDatos[x];
            else
            	tipoAudiencia+=' '+arrDatos[x];
        }
    	return Date.parseDate(arrDatos[0]+' '+arrDatos[1],'Y-m-d H:i:s').format('d/m/Y H:i:s')+' '+tipoAudiencia;
    }
    return val;
}

function formatearListadoFechaEvento(val)
{
	var listado='';
    var x;
    
	var arrDatos=removerComillasContenedorasCadena(val).split(',');
   
    for(x=0;x<arrDatos.length;x++)
    {
        if(listado=='')
            listado=formatearFechaEvento(arrDatos[x].trim());
        else
            listado+='<br>'+formatearFechaEvento(arrDatos[x].trim());
    }
    	
    
    return listado;
}


function formatearAccionRealizada(val)
{

	if(!val ||(val=='')||(val=='N/E'))
    	return 'En tr&aacute;mite';
    switch(parseInt(val))
    {
    	case 1:
        	return 'Se Admite';
        break;
        case 2:
        	return 'Se Rechaza';
        break;
        case 3:
        	return 'Se Previene';
        break;
    }


}

function formatearRegistroProcesosPromocion(val)
{
	
	if(val=='')
    	return '';
	var arrValores=val.split('---');

	return 'Folio: '+arrValores[0]+', Fecha registro: '+Date.parseDate(arrValores[1],'Y-m-d H:i:s').format('d/m/Y')+
            		((arrValores[3]!='')?(', No. Billete: '+arrValores[3]):'')+', Asunto: '+(arrValores[4]==''?'(Sin asunto)':arrValores[4]);
    
    
}

function formatearRegistroProcesosAmparo(val)
{
	if(val=='')
    	return '';
	var arrValores=val.split('---');
    
	return 'Folio: '+arrValores[0]+', Fecha registro: '+Date.parseDate(arrValores[1],'Y-m-d H:i:s').format('d/m/Y')+', Acto reclamado: '+arrValores[2];
    
}


function formatearRegistroProcesosApelacion(val)
{
	if(val=='')
    	return '';
	var arrValores=val.split('---');
    
	return 'Folio: '+arrValores[0]+', Fecha admisión: '+Date.parseDate(arrValores[1],'Y-m-d').format('d/m/Y')+', Resolución impugnada: '+arrValores[2];
        
    
}


function formatearRegistroProcesosValor(val)
{
	if(val=='')
    	return '';
	var arrValores=val.split('---');

	
    return 'Folio: '+arrValores[0]+', Fecha registro: '+Date.parseDate(arrValores[1],'Y-m-d H:i:s').format('d/m/Y')+', No. Billete: '+arrValores[2];
     
    
}

function formatearDatosFianza(val)
{
	var arrDatos=val.split('Monto: ');
    return arrDatos[0]+' Monto: '+Ext.util.Format.usMoney(arrDatos[1]);
}


function escaparBRCombo(val)
{
	return escaparBR(val,true);
}

function formatoMoneda(val)
{
	val=val.replace(/'/gi,'');
	if((val=='')||(val=="''"))
    	val=0;
    return Ext.util.Format.usMoney(val);
}

function formatearValorTelefono(val)
{
	if(val=='')
    	return '';
	var arrTelefono=val.split(',');
    var x;
    var aTelefono;
    var t;
    var listaTelefono='';
    var tel;
    for(x=0;x<arrTelefono.length;x++)
    {
    	aTelefono=arrTelefono[x].split('_');
        
        tel=aTelefono[0]!=''?'('+aTelefono[0]+') ':'';
        
        tel+=aTelefono[1];
        
        if(aTelefono[2]!='')
        {
        	tel+=" Ext. "+aTelefono[2];
        }
        
        if(listaTelefono=='')
        	listaTelefono=tel;
        else
        	listaTelefono+=", "+tel;
    }
    
    return listaTelefono;
}


function mostrarTipoExpediente(val)
{
	var arrTipoCarpeta=<?php echo $arrTipoCarpeta?>;
    return formatearValorRenderer(arrTipoCarpeta,val);
}


function formatearAreaDestinataria(val,meta,registro)
{
	if(registro.data.institucionDestino=='Otro')
    	return mostrarValorDescripcion(registro.data.txtUnidadDestinataria);
	return mostrarValorDescripcion(val);
}


function formatearCausaPenal(val,meta,registro)
{

	return registro.data.noExpediente+'/'+parseInt(registro.data.anioExpediente);
}


function formatearDocumentoProceso(val)
{
	if((val!='N/E')&&(val!='-1')&&(val!=''))
    {
    	return '<a href="javascript:visualizarDocumentoProceso(\''+bE(val)+'\')"><img src="../imagenesDocumentos/16/file_extension_pdf.png"></a>';
    }
    
    return '---';
}


function visualizarDocumentoProceso(iD)
{
	if(window.parent && window.parent.mostrarVisorDocumentoProceso)
    {
        window.parent.mostrarVisorDocumentoProceso('pdf',bD(iD));
    }
    else
        mostrarVisorDocumentoProceso('pdf',bD(iD));
    
	
}

///////
function rendererInfoProcesoJudicial(cupj,ctrRenderer)
{
	if(cupj.trim()=='')
    	return '';
	function funcAjax(peticion_http)
    {
        var resp=peticion_http.responseText;
        arrResp=resp.split('|');
        if(arrResp[0]=='1')
        {
        	 var obj=eval('['+arrResp[1]+']')[0];  
        	var arrSpan=ctrRenderer.innerHTML.split('sp_');
            if(arrSpan.length>1)
            {
                var arrSpan=arrSpan[1].split('">');
               
                ctrRenderer.innerHTML='<span id="sp_'+arrSpan[0]+'">'+obj.leyenda+'</span>' ;
			}
            else
            {
            	ctrRenderer.innerHTML=obj.leyenda ;
            }
        }
        else
        {
            msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
        }
    }
    obtenerDatosWebV2('../modulosEspeciales_SIUGJ/paginasFunciones/funcionesSIUGJ.php',funcAjax, 'POST','funcion=1&cupj='+cupj,true);
    return '';
}


function rendererInfoProcesoJudicialVistaReasignacion(cupj,ctrRenderer)
{
	if(cupj.trim()=='')
    	return '';
	function funcAjax(peticion_http)
    {
        var resp=peticion_http.responseText;
        arrResp=resp.split('|');
        if(arrResp[0]=='1')
        {
        	 var obj=eval('['+arrResp[1]+']')[0];  
        	var arrSpan=ctrRenderer.innerHTML.split('sp_');
            if(arrSpan.length>1)
            {
                var arrSpan=arrSpan[1].split('">');
               
                ctrRenderer.innerHTML='<span id="sp_'+arrSpan[0]+'">'+obj.leyenda+'</span>' ;
			}
            else
            {
            	ctrRenderer.innerHTML=obj.leyenda ;
            }
        }
        else
        {
            msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
        }
    }
    obtenerDatosWebV2('../modulosEspeciales_SIUGJ/paginasFunciones/funcionesSIUGJ.php',funcAjax, 'POST','funcion=1&vReasignacion=1&cupj='+cupj,true);
    return '';
}


function rendererProcesoJudicialTablero(val)
{
	if((val=='')||(val=='N/E'))
    	return '------';
    return val;
}

function rendererInformacionReservada(val)
{
	return '(Reservado)';
}

function rendererSiNOCampo(val)
{
	if((val=='')||(val=='N/E'))
    	return '----';
	
    
    if(parseInt(val)=='1')
    	return 'Sí';
    return 'No';
           
}

function formatearCodigoDespacho(val)
{
	var arrOrganigrama=<?php echo $arrOrganigrama?>;
    return formatearValorRenderer(arrOrganigrama,val);
}


function formatearCampoTableroFecha(val)
{
	var valor=removerComillasContenedorasCadena(val);
	if((valor=='')||(valor=='N/E'))
    	return '------';
    
    if(Date.parseDate(valor,'Y-m-d H:i:s'))
    	return Date.parseDate(valor,'Y-m-d H:i:s').format('d/m/Y H:i:s');
    else
    	if(Date.parseDate(valor,'Y-m-d'))
        	return Date.parseDate(valor,'Y-m-d').format('d/m/Y');
        else
    		if(Date.parseDate(valor,'H:i:s'))
        		return Date.parseDate(valor,'H:i:s').format('H:i')+' hrs.';
        
    
   
}

function rendererPrioridadTarea(val)
{
	switch(val)
    {
    	case '1':
        	val='<span style="color:#F00; font-weight:bold">Alta</span>';
        break;
        case '2':
        	val='<span style="color:#FF9A00; font-weight:bold">Media</span>';
        break;
        case '3':
        	val='<span style="color:#DFDF81; font-weight:bold">Baja</span>';
        break;
        default:
        	val='<span style="color:#008044; font-weight:bold">-</span>';
        break;
    }
	return val;
}

function rendererInfoBienRemate(idBien,ctrRenderer)
{
	if(idBien.trim()=='')
    	return '';
	function funcAjax(peticion_http)
    {
        var resp=peticion_http.responseText;
        arrResp=resp.split('|');
        if(arrResp[0]=='1')
        {
        	var obj=eval('['+arrResp[1]+']')[0];  
        	var arrSpan=ctrRenderer.innerHTML.split('sp_');
            if(arrSpan.length>1)
            {
                var arrSpan=arrSpan[1].split('">');
               
                ctrRenderer.innerHTML='<span id="sp_'+arrSpan[0]+'">'+obj.leyenda+'</span>' ;
			}
            else
            {
            	ctrRenderer.innerHTML=obj.leyenda ;
            }
        }
        else
        {
            msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
        }
    }
    obtenerDatosWebV2('../modulosEspeciales_SIUGJ/paginasFunciones/funcionesBienes.php',funcAjax, 'POST','funcion=6&idBien='+idBien,true);
    return '';
}
