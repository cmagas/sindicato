function buscarPorNumeroReferencia(tFuncion)
{
	tipoBusqueda=1;
    var posDefault=existeValorMatriz(arrFuncionBusqueda,tFuncion);
    
    gE('lblBusqueda').innerHTML=arrFuncionBusqueda[posDefault][2]+':';
    window.funcionEjecucionBusquedaRealizada=function(arrResp)
                                            {
                                                metaData=null;
                                                
                                                
                                                var arrDatos=eval(arrResp[1]);
                                                if(arrDatos.length==0)
                                                {
                                                    function resp()
                                                    {
                                                        gEx('txtClave').focus(true,500);
                                                    }
                                                    msgBox('El n&uacute;mero de referencia ingresado no existe, favor de verificarlo',resp)
                                                    return;
                                                }
                                            
                                                gEx('btnCancelar').enable();
                                                gEx('btnPagar').enable();
                                                
                                                
                                                 var x;
                                                var r;
                                                for(x=0;x<arrDatos.length;x++)   
                                                {
                                                    r=new regProducto	(arrDatos[x]);
                                    
                                                    gEx('grid').getStore().add(r);
                                                }
                                                
                                                if(arrResp[2]!='')
                                                {
                                                    metaData=eval(''+arrResp[2]+'')[0];
                                                }
                                                
                                                
                                                gEx('btnPagar').show();
                                                gEx('btnCancelar').show();
                                                gEx('btnPagar').enable();
                                                gEx('btnCancelar').enable();
                                                gEx('btnAbono').hide();
                                                gEx('txtClave').hide();
                                                gEx('txtCantidad').hide();
                                                oE('lblBusqueda');
                                                oE('lblCantidad');
                                                
                                                

                                                if(metaData)    
                                                {
                                                	tipoCliente=metaData.tCliente;
                                                    idCliente=metaData.idCliente;
                                                    
                                                	reajustarConceptos(tipoCliente,metaData.nombreCliente,true,false);
                                                }
                                                calcularTotal();
                                                
                                            }
                                            ;  
    
}