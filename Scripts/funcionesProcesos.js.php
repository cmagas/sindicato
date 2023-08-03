<?php
	session_start();
	include("configurarIdiomaJS.php");
	include("conexionBD.php");
?>
function enviarActor(a,e)
{
	var content=Ext.getCmp('content');
    var iP=gE('idProceso').value;	
    content.load({url:'../modeloPerfiles/proxyProcesos.php',scripts:true,params:{actor:a,idProceso:iP,cPagina:'sFrm=true',numEtapa:e}})
}

function enviarVistaProceso(iP,v,comp)
{
	var content=Ext.getCmp('content');
    content.load({url:'../modeloPerfiles/proxyProcesos.php',scripts:true,params:{vista:v,idProceso:iP,cPagina:'sFrm=true',vComp:comp}})
    if(gEx('tblProcesos')!=null)
	    gEx('tblProcesos').expand();	
}

function enviarVistaProcesoActor(iP,v,comp,a,iF,iR)
{
	var idFrm=-1;
    var idRef=-1;
    if(iF!=undefined)
    	idFrm=bD(iF);
    if(iR!=undefined)
    	idRef=bD(iR);


	var content=Ext.getCmp('content');
   	content.load({url:'../modeloPerfiles/proxyProcesos.php',scripts:true,params:{idFormulario:idFrm,idReferencia:idRef,vista:v,idProceso:iP,cPagina:'sFrm=true',vComp:comp,actor:a}})
    if(gEx('tblProcesos')!=null)
	    gEx('tblProcesos').expand();
}



function mostrarOcultarEtapas(pr)
{
	var img=gE('imgEtapas_'+pr);
    if(img.title=='Ver registros por etapas')
    {
    	mE('tbl_'+pr);
        img.src='../images/verMenos.png';
        img.title='Ocultar registros por etapas';
        img.alt='Ocultar registros por etapas';
        
    }
    else
    {
    	oE('tbl_'+pr);
        img.src='../images/verMas.gif';
        img.title='Ver registros por etapas';
        img.alt='Ver registros por etapas';
    }
}

var arrActores;

function registrarNuevo(frm,actor)
{
	var pag=existeValorMatriz(arrPagNuevo,bD(frm),0);
    if(pag>-1)
    	pag=arrPagNuevo[pag][1];
    var idProcesoP=gE('idProcesoP').value;
    var idReferencia=gE('idReferencia').value;
    var idUsuario=gE('idUsuario').value;
    var arrDatos=[['idRegistro','-1'],['idFormulario',bD(frm)],['dComp','<?php echo base64_encode("agregar")?>'],['actor',actor],['idProcesoP',idProcesoP],['idReferencia',idReferencia],['idUsuario',idUsuario]];
    var nVentana='ventana_'+(new Date().format('h_i'))+'_'+generarNumeroAleatorio(1,10000);
    window.open('',nVentana, "toolbar=no,directories=no,menubar=no,status=no,scrollbars=yes,fullscreen=yes");
    enviarFormularioDatos(pag,arrDatos,'POST',nVentana);
}

function registrarNuevoFuncion(frm,actor,iR)
{
	function funcAjax()
    {
        var resp=peticion_http.responseText;
        arrResp=resp.split('|');
        if(arrResp[0]=='1')
        {

            var pag=existeValorMatriz(arrPagNuevo,bD(frm),0);
            if(pag>-1)
                pag=arrPagNuevo[pag][1];
            var idProcesoP=gE('idProcesoP').value;
            var idReferencia=gE('idReferencia').value;
            var idUsuario=gE('idUsuario').value;
            var arrDatos=[['idRegistro',arrResp[1]],['idFormulario',bD(frm)],['dComp','<?php echo base64_encode("auto")?>'],['actor',bE(arrResp[2])],['idProcesoP',idProcesoP],['idReferencia',idReferencia],['idUsuario',idUsuario]];
            var nVentana='ventana_'+(new Date().format('h_i'))+'_'+generarNumeroAleatorio(1,10000);
            window.open('',nVentana, "toolbar=no,directories=no,menubar=no,status=no,scrollbars=yes,fullscreen=yes");
            enviarFormularioDatos(pag,arrDatos,'POST',nVentana);
            recargarPagina();
        }
        else
        {
            msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
        }
    }
    obtenerDatosWeb('../paginasFunciones/funcionesProyectos.php',funcAjax, 'POST','funcion=239&idFormulario='+bD(frm)+'&actor='+(actor)+'&idReferencia='+bD(iR),true);
    
}


function verRegistro(idReg,aux,actor,idRef)
{
	var idFormulario=gE('idFormulario').value;
    if(idRef=='')
    	idRef=bE('-1');
	var arrDatos=[['idFormulario',idFormulario],['idRegistro',aux],['dComp','<?php echo base64_encode('auto')?>'],['actor',actor],['idReferencia',bD(idRef)],['idProcesoP',bD(gE('idProcesoP').value)],['idUsuario',gE('idUsuario').value]];
    var nVentana='ventana_'+(new Date().format('h_i'))+'_'+generarNumeroAleatorio(1,10000);
	window.open('',nVentana, "toolbar=no,directories=no,menubar=no,status=no,scrollbars=yes,fullscreen=yes");
    enviarFormularioDatos(arrPagNuevo[1],arrDatos,'POST',nVentana);
} 

function verRegistroParticipante(idReg,participante,idRef)
{
	var idFormulario=gE('idFormulario').value;
	var arrDatos=[['idFormulario',idFormulario],['idRegistro',bD(idReg)],['dComp','<?php echo base64_encode('auto')?>'],['actor',participante],['participante','1'],['idReferencia',bD(idRef)]];
    var nVentana='ventana_'+(new Date().format('h_i'))+'_'+generarNumeroAleatorio(1,10000);
	window.open('',nVentana, "toolbar=no,directories=no,menubar=no,status=no,scrollbars=yes,fullscreen=yes");
    enviarFormularioDatos(arrPagNuevo[1],arrDatos,'POST',nVentana);
}



function obtenerDocumentoPagoReferenciado(iC)
{
	function funcAjax()
    {
        var resp=peticion_http.responseText;
        arrResp=resp.split('|');
        if(arrResp[0]=='1')
        {
            var ref=arrResp[1];
            obtenerFormatoPagoReferenciado(ref);
        }
        else
        {
            msgBox('<?php echo $etj["errOperacion"]?>'+' <br />'+arrResp[0]);
        }
    }
    obtenerDatosWeb('../paginasFunciones/funcionesProyectos.php',funcAjax, 'POST','funcion=338&idConcepto='+iC+'&idFormulario='+gE('idFormularioAux').value+'&idRegistro='+gE('idRegistroAux').value,true);
}


