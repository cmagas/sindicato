// JavaScript Document
CKEDITOR.plugins.add('pSaveDocumentV2',	{
											init:	function(editor)
													{
														var pluginName='pSaveDocumentV2';
														editor.ui.addButton	('pSaveDocumentV2',
																				{
																					label:'Guardar documento',
																					command:'saveDocumentV2',
																					icon:CKEDITOR.plugins.getPath('pSaveDocumentV2')+'save.png'
																				}
																			)
																			
														var cmd=editor.addCommand('saveDocumentV2',{exec: guardarDocumentoExec});
													}	
										}
				);

function guardarDocumentoExec(e)
{
	
	var cadObj='{"idRegistroFormato":"'+gE('idRegistroFormato').value+'","tipoFormato":"'+gE('tipoFormato').value+'","cuerpoFormato":"'+
				bE(CKEDITOR.instances.txtDocumento.getData())+'","idFormulario":"'+gE('idFormulario').value+'","idRegistro":"'+
				gE('idRegistro').value+'","idReferencia":"'+gE('idReferencia').value+'","idFormularioProceso":"-1"}';
	
	function funcAjax()
	{
		var resp=peticion_http.responseText;
		
		arrResp=resp.split('|');
		if(arrResp[0]=='1')
		{
			gE('idRegistroFormato').value=arrResp[1];
			
			if(typeof(functionAfterSaveDocument)!='undefined')
				functionAfterSaveDocument();
			
		}
		else
		{
			
			msgBox('No se pudo guardar el documento debido al siguiente error: <br><br />'+arrResp[0]);
		}
	}
	obtenerDatosWeb('../paginasFunciones/funcionesModulosEspeciales_SGP.php',funcAjax, 'POST','funcion=1&cadObj='+bE(cadObj),true);
	
	
}