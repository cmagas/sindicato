// JavaScript Document
CKEDITOR.plugins.add('pSaveDocument',	{
											init:	function(editor)
													{
														var pluginName='pSaveDocument';
														editor.ui.addButton	('pSaveDocument',
																				{
																					label:'Guardar documento',
																					command:'saveDocument',
																					icon:CKEDITOR.plugins.getPath('pSaveDocument')+'icon_big_tick.gif'
																				}
																			)
																			
														var cmd=editor.addCommand('saveDocument',{exec: guardarDocumentoExec});
													}	
										}
				);

function guardarDocumentoExec(e)
{
	
	var cadObj='{"idRegistroFormato":"'+gE('idRegistroFormato').value+'","tipoFormato":"'+gE('tipoFormato').value+'","cuerpoFormato":"'+
				bE(CKEDITOR.instances.txtDocumento.getData())+'","idFormulario":"'+gE('idFormulario').value+'","idRegistro":"'+
				gE('idRegistro').value+'","idReferencia":"'+gE('idReferencia').value+'","idFormularioProceso":"'+gE('idFormularioProceso').value+'"}';
	
	function funcAjax()
	{
		var resp=peticion_http.responseText;
		
		arrResp=resp.split('|');
		if(arrResp[0]=='1')
		{
			gE('idRegistroFormato').value=arrResp[1];
			
			if(typeof(refrescarMenuDTD)!='undefined')
				refrescarMenuDTD();
			
		}
		else
		{
			
			msgBox('No se pudo guardar el documento debido al siguiente error: <br><br />'+arrResp[0]);
		}
	}
	obtenerDatosWeb('../paginasFunciones/funcionesModulosEspeciales_SGP.php',funcAjax, 'POST','funcion=1&cadObj='+bE(cadObj),true);
	
	
}