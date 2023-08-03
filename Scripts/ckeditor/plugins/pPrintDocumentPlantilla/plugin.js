// JavaScript Document
var primeraCargaFrame=true;
CKEDITOR.plugins.add('pPrintDocumentPlantilla',	{
											init:	function(editor)
													{
														var pluginName='pPrintDocumentPlantilla';
														editor.ui.addButton	('pPrintDocumentPlantilla',
																				{
																					label:'Imprimir documento',
																					command:'printDocumentPlantilla',
																					icon:CKEDITOR.plugins.getPath('pPrintDocumentPlantilla')+'printer.png'
																				}
																			)
																			
														var cmd=editor.addCommand('printDocumentPlantilla',{exec: imprimirDocumentoPlantillaExec});
													}	
										}
				);

function imprimirDocumentoPlantillaExec(e)
{
	var _metodoConversionPDFvch=gE('_metodoConversionPDFvch');
	var conversorPDF=_metodoConversionPDFvch.options[_metodoConversionPDFvch.selectedIndex].value;
	if(conversorPDF=='-1')
		conversorPDF=1;
	var cadObj='{"idRegistroFormato":"'+gE('idRegistroFormato').value+'","tipoFormato":"'+gE('tipoFormato').value+'","cuerpoFormato":"'+
				bE(CKEDITOR.instances.txtDocumento.getData())+'","idFormulario":"'+gE('idFormulario').value+'","idRegistro":"'+gE('idRegistro').value+
				'","idReferencia":"'+gE('idReferencia').value+'","idFormularioProceso":"'+(gE('idFormularioProceso')?gE('idFormularioProceso').value:-1)+'"}';
	
	function funcAjax()
	{
		var resp=peticion_http.responseText;		
		arrResp=resp.split('|');
		if(arrResp[0]=='1')
		{
			gE('idRegistroFormato').value=arrResp[1];
			var cadObj='{"idRegistroFormato":"'+gE('idRegistroFormato').value+'","conversorPDF":"'+conversorPDF+'"}';
	
			function funcAjax2()
			{
				var resp=peticion_http.responseText;
				
				arrResp=resp.split('|');
				if(arrResp[0]=='1')
				{
					var iFrame=gE('frameEnvio');
					if(iFrame)
					{
						iFrame.parentNode.removeChild(iFrame);
					}

					primeraCargaFrame=false;
					iFrame=cE('iFrame');
					iFrame.name='frameEnvio';
					iFrame.id='frameEnvio';
					iFrame.style='width:1px; height:1px;';
					document.body.appendChild(iFrame);
					asignarEvento(iFrame,'load',frameLoad);
					var arrParametros=[['ref',generarNumeroAleatorio(10000,99999)+'_'+bE(gE('idRegistroFormato').value)]];
					enviarFormularioDatos('../modulosEspeciales_SGJP/obtenerDocumentoDigitalProceso.php',arrParametros,'POST','frameEnvio');
					
				}
				else
				{
					
					msgBox('No se pudo guardar el documento debido al siguiente error: <br><br />'+arrResp[0]);
				}
			}
			obtenerDatosWeb('../paginasFunciones/funcionesModulosEspeciales_SGP.php',funcAjax2, 'POST','funcion=3&cadObj='+cadObj,true);
		}
		else
		{
			
			msgBox('No se pudo guardar el documento debido al siguiente error: <br><br />'+arrResp[0]);
		}
	}
	obtenerDatosWeb('../paginasFunciones/funcionesModulosEspeciales_SGP.php',funcAjax, 'POST','funcion=1&cadObj='+bE(cadObj),true);
	
	
	
}



function frameLoad(iFrame)
{
    if(!primeraCargaFrame)
    {
        setTimeout(
                        function()
                        {
                            iFrame.contentWindow.print()
                        }, 10
                   );
        
        
    }
    else
        primeraCargaFrame=false;
    
}