// JavaScript Document
CKEDITOR.plugins.add('pSavePDF',	{
											init:	function(editor)
													{
														var pluginName='pSavePDF';
														editor.ui.addButton	('pSavePDF',
																				{
																					label:'Descargar documento',
																					command:'saveDocumentPDF',
																					icon:CKEDITOR.plugins.getPath('pSavePDF')+'save.png'
																				}
																			)
																			
														var cmd=editor.addCommand('saveDocumentPDF',{exec: guardarDocumentoPDFExec,readOnly:true});
													}	
										}
				);

function guardarDocumentoPDFExec(e)
{
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
			var cadObj='{"idRegistroFormato":"'+gE('idRegistroFormato').value+'"}';
	
			function funcAjax2()
			{
				var resp=peticion_http.responseText;
				
				arrResp=resp.split('|');
				if(arrResp[0]=='1')
				{
					var arrParametros=[['ref',generarNumeroAleatorio(10000,99999)+'_'+bE(gE('idRegistroFormato').value)]];
					enviarFormularioDatos('../modulosEspeciales_SGJP/obtenerDocumentoDigitalProceso.php',arrParametros,'POST','_blank');
					
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