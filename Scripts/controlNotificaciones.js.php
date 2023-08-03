<?php
	session_start();
	include("configurarIdiomaJS.php");
	include("conexionBD.php");
	
	$consulta="SELECT DISTINCT idTableroControl FROM 9064_rolesTableroControl WHERE rol IN(".$_SESSION["idRol"].")";
	$listaTableros=$con->obtenerListaValores($consulta);
	if($listaTableros=="")
		$listaTableros=-1;
		
	$arrTableros="";	
		
	$consulta="SELECT idTableroControl,nombreTableroControl,nombreCorto,tiempoActualizacion,descripcion FROM 9060_tablerosControl WHERE idTableroControl IN (".$listaTableros.") AND visibleBarraNotificaciones=1";
	$resTableros=$con->obtenerFilas($consulta);
	while($filaTablero=mysql_fetch_row($resTableros))
	{
		$o='{"idTableroControl":"'.$filaTablero[0].'","nombreTableroControl":"'.cv($filaTablero[1]).'","nombreCorto":"'.cv($filaTablero[2]).'","tiempoActualizacion":"'.cv($filaTablero[3]).
			'","descripcion":"'.cv($filaTablero[4]).'"}';
		if($arrTableros=="")
			$arrTableros=$o;
		else
			$arrTableros.=",".$o;
	}
	
	$arrTableros="[".$arrTableros."]";
	
	
	$consulta="select HEX(AES_ENCRYPT('".$_SESSION["idUsr"]."', '".bD($versionLatis)."'))";
	$idUsuario=$con->obtenerValor($consulta);
	
?>
var instanciaAlertas=null;
var arrNotificacionesActivas=[];
var arrTableros=<?php echo $arrTableros?>;
var arrNotificaciones=[];

function inicializarTablero(renderTo)
{
	var x;
    var o;
    var eNotificacion={};
    var tablaNotificacion='<table class="ttw-notification-menu" ><tr>'+

    						'<td class="notification-menu-item first-item" height="24" style="vertical-align:middle;">'+
                            '<table width="">'+
                            '<tr><td width="35" align="center"><img src="../images/warning_0.png"  /></td>'+
			                '<td width=""><span><a class="letraNombreTablero">Alertas/Notificaciones del d&iacute;a</a></span></td>'+
                            '<td ><a style="text-decoration:none" href="javascript:mostrarTableroAlertasNotificaciones()"><div class="burbujaNotificacion" id="total_alertas">0</div></a></td><td width="50"></td>';
/*
    						'<td class="notification-menu-item first-item" height="24" style="vertical-align:middle;width:290px;">'+
                            '<table width="800">'+
                            '<tr><td width="35" align="center"><img src="../images/warning_0.png"  /></td>'+
			                '<td width="300"><span><a class="letraNombreTablero">Alertas/Notificaciones del d&iacute;a:</a></span></td>'+
                            '<td style="padding-right:5px;padding-left:5px;"><a href="javascript:mostrarTableroAlertasNotificaciones()"><div class="burbujaNotificacion" id="total_alertas">0</div></a></td><td width="50"></td>';
*/
    
    for(x=0;x<arrTableros.length;x++)	
    {
    	o=arrTableros[x];
        tablaNotificacion+='<td class="notification-menu-item first-item" height="24"><table id="tblNotificacion_'+o.idTableroControl+
        					'"><tr><td width="35" align="center"><img src="../images/warning_0.png" id="icon_'+o.idTableroControl+
        					'_0"><img style="display:none" src="../images/warning_1.png" id="icon_'+o.idTableroControl+'_1"><img style="display:none" src="../images/warning_2.png" id="icon_'+o.idTableroControl+
                            '_2"></td><td><span><a class="letraNombreTablero">'+o.nombreCorto+'</a></span></td><td style=""><a style="text-decoration:none" href="javascript:mostrarTableroNotificaciones(\''+bE(o.idTableroControl)+'\')"><div id="total_'+o.idTableroControl+
                            '" class="burbujaNotificacion"><div>0</div></div></a></td></tr></table></td><td width="50"></td>';
        o.tiempoActualizacion=parseFloat(o.tiempoActualizacion);
        if(o.tiempoActualizacion>0)
        {
        	verificarNotificaciones(o.idTableroControl);
            o.idTemporizador=setInterval('verificarNotificaciones('+o.idTableroControl+')', o.tiempoActualizacion*60*1000);
            
        }        
    	arrNotificaciones.push(o)
    
    }
    
    tablaNotificacion+='</tr></table>';
    gE(renderTo).innerHTML=tablaNotificacion;
    verificarAlertas();
    //setInterval(verificarAlertas, 300000);
}

function verificarNotificaciones(iT)
{
	
	function funcAjax(peticion_http)
    {
        var resp=peticion_http.responseText;
        arrResp=resp.split('|');
        if(arrResp[0]=='1')
        {
        	
        	var icon0=gE('icon_'+iT+'_0');
            var icon1=gE('icon_'+iT+'_1');
            var icon2=gE('icon_'+iT+'_2');
        	var totalMensajes=0;
        	oE(icon0.id);
            oE(icon1.id);
            oE(icon2.id);
        
            var cadObj=arrResp[1];
            var obj=eval('['+arrResp[1]+']')[0];
            
            gE('total_'+iT).innerHTML='<div>'+obj.registrosPendientes+'</div>';
            if(obj.registrosVencidos>0)
            {
            	totalMensajes++;
                mE(icon2.id);
                
                switch(obj.registrosVencidos)
                {
                	case 0:
                    	icon2.title='NO existen registros cuyo tiempo límite de atención haya vencido';
                    break;
                    case 1:
                    	icon2.title='Existe '+obj.registrosVencidos+' registro cuyo tiempo límite de atención ha vencido';
                    break;
                    default:
                    	icon2.title='Existen '+obj.registrosVencidos+' registros cuyo tiempo límite de atención ha vencido';
                    break;
                    
                }
                
            	
                
            }
           
            if(obj.registrosPorVencer>0)
            {
            	totalMensajes++;
            	mE(icon1.id);
                 switch(obj.registrosPorVencer)
                {
                	case 0:
                    	icon1.title='NO existen registros cuyo tiempo límite de atención;n esté por vencer';
                    break;
                    case 1:
                    	icon1.title='Existe '+obj.registrosPorVencer+' registro cuyo tiempo límite de atención está por vencer';
                    break;
                    default:
                    	icon1.title='Existen '+obj.registrosPorVencer+' registros cuyo tiempo límite de atención está por vencer';
                    break;
                    
                }
                
            }
            
            if(totalMensajes==0)
            	mE(icon0.id);
            
            if(obj.registrosNuevos>0)
            {
            	var pos=obtenerPosObjeto(arrTableros,'idTableroControl',iT);
                
                
            	var lblNuevosRegistros='';
                
                if(obj.registrosNuevos==1)
                	lblNuevosRegistros='Se ha detectado un nuevo registro';
                else
                	lblNuevosRegistros='Se han detectado '+obj.registrosNuevos+' nuevos registros';
                
               
               
               var posNotificacionActiva=existeValorMatriz(arrNotificacionesActivas,iT);
               
               if(posNotificacionActiva==-1)
               {
               		 var instancia=$.ClassyNotty({
                                        
                                        content : '<div style="text-align:center">'+lblNuevosRegistros+' consulte el tablero <a style="text-decoration:none" href="javascript:mostrarTableroNotificaciones(\''+bE(iT)+'\')"><span style="color:#900"><b>'+arrTableros[pos].nombreCorto+ '</b></span></a> para m&aacute;s detalles</div>',
                                        showTime:false
                                    });
               		arrNotificacionesActivas.push([iT,instancia]);             
               }
               else
               {
               		if(arrNotificacionesActivas[posNotificacionActiva][1])
                    	arrNotificacionesActivas[posNotificacionActiva][1].cerrar();
               		arrNotificacionesActivas[posNotificacionActiva][1]=$.ClassyNotty({
                                        
                                                                                        content : '<div style="text-align:center">'+lblNuevosRegistros+' consulte el tablero <a style="text-decoration:none" href="javascript:mostrarTableroNotificaciones(\''+bE(iT)+'\')"><span style="color:#900"><b>'+arrTableros[pos].nombreCorto+ '</b></span></a> para m&aacute;s detalles</div>',
                                                                                        showTime:false
                                                                                    });
               }
                            
            }   
                
            actualizarTableroControl(iT);
        }
    }
    obtenerDatosWebV2('../paginasFunciones/funcionesTblFormularios.php',funcAjax, 'POST','funcion=8&iU=<?php echo $idUsuario ?>&consultaAutomatica=1&iT='+bE(iT),false);
}

function mostrarTableroNotificaciones(iT)
{

	/*if(parseFloat(gE('total_'+bD(iT)).innerHTML)==0)
    {
    	return;
    }*/

	var frameContenido=gEx('frameContenido');
    if(gEx('frameContenido').getFrameWindow().location.href.indexOf('tblAdministradorTablerosControl.php')==-1)
    {
		
        frameContenido.load	(
                                        {
                                            url:'../modeloPerfiles/tblAdministradorTablerosControl.php',
                                            params:	{
                                                        tableroActivo:bD(iT),
                                                        desactivarTemporizador:1,
                                                        cPagina:'sFrm=true'
													}	                                                            
                                        }
                                    )
	}
    else
    {
    	
    	gEx('frameContenido').getFrameWindow().establecerTableroControl(bD(iT));
    }

	var x;
	for(x=0;x<arrTableros.length;x++)	
    {
    	o=arrTableros[x];
        if(gE('tblNotificacion_'+o.idTableroControl))
	        gE('tblNotificacion_'+o.idTableroControl).setAttribute('class','');
    }
    if(gE('tblNotificacion_'+bD(iT)))
	    gE('tblNotificacion_'+bD(iT)).setAttribute('class','tablaNotificacionesSel');
}

function actualizarTableroControl(iT)
{
	var frameContenido=gEx('frameContenido');
    if(gEx('frameContenido').getFrameWindow().location.href.indexOf('tblAdministradorTablerosControl.php')!=-1)
    {
		if(gEx('frameContenido').getFrameWindow().recargarTableroControl)
       		gEx('frameContenido').getFrameWindow().recargarTableroControl(iT);
	}
    
}

function ocultarNotificacionesTableroControl(iT)
{
	
	var posNotificacionActiva=existeValorMatriz(arrNotificacionesActivas,iT);
    if(posNotificacionActiva!=-1)
    {
    	if(arrNotificacionesActivas[posNotificacionActiva][1])
       	{
       		//console.log(arrNotificacionesActivas);
        	arrNotificacionesActivas[posNotificacionActiva][1].cerrar();
       	}
    }
}

function verificarAlertas()
{
	
	function funcAjaxAlerta(peticion_http)
    {
        var resp=peticion_http.responseText;
        arrResp=resp.split('|');
        if(arrResp[0]=='1')
        {
            var cadObj=arrResp[1];
            var obj=eval('['+arrResp[1]+']')[0];
            
            if(obj.totalAlertas!='0')
            {
				gE('total_alertas').innerHTML='	<a style="text-decoration: none" href="javascript:mostrarTableroAlertasNotificaciones()">'+
													'<div>'+obj.totalAlertas+'</div>'+
												'</a>';
			}
           else
           {
           		gE('total_alertas').innerHTML='<div>'+obj.totalAlertas+'</div>';
           }
           
            if(obj.registrosNuevos>0)
            {
            	
            	var lblNuevosRegistros='';                
                if(obj.registrosNuevos==1)
                	lblNuevosRegistros='Se ha detectado una nueva notificaci&oacute;n/alerta';
                else
                	lblNuevosRegistros='Se han detectado '+obj.registrosNuevos+' nuevas notificaci&oacute;nes/alertas';
                
               
               
               if(!instanciaAlertas)
               {
               		  instanciaAlertas=$.ClassyNotty({
                                        
                                        content : '<div style="text-align:center">'+lblNuevosRegistros+' consulte el tablero <a style="text-decoration:none" href="javascript:mostrarTableroAlertasNotificaciones(\''+bE(0)+'\')"><span style="color:#900"><b>notificaciones/alertas del d&iacute;a</b></span></a> para m&aacute;s detalles</div>',
                                        showTime:false
                                    });
                   	
               		  
               }
               else
               {
               		instanciaAlertas.cerrar();
					instanciaAlertas=$.ClassyNotty({

														content : '<div style="text-align:center">'+lblNuevosRegistros+' consulte el tablero <a style="text-decoration:none" href="javascript:mostrarTableroAlertasNotificaciones(\''+bE(0)+'\')"><span style="color:#900"><b>notificaciones/alertas del d&iacute;a</b></span></a> para m&aacute;s detalles</div>',
														showTime:false
													});
               		
               }
                            
            }   
                
            
        }
    }
    obtenerDatosWebV2('../paginasFunciones/funcionesTblFormularios.php',funcAjaxAlerta, 'POST','funcion=11&iU=<?php echo $idUsuario ?>&consultaAutomatica=1',false);
}

function mostrarTableroAlertasNotificaciones()
{
	var obj={};
	obj.ancho='100%';
	obj.alto='100%';
	obj.params=[['fechaConsulta','<?php echo date("Y-m-d") ?>']];
	obj.url='../modulosEspeciales_SGJP/tblNotificacionesAlertasDelDia.php';
	obj.titulo='Alertas/notificaciones del d&iacute;a';
	if(window.parent)
		window.parent.abrirVentanaFancy(obj);
	else
		abrirVentanaFancy(obj);
}

function actualizarBurbujaTareas(iT,total)
{
	gE('total_'+iT).innerHTML='<div>'+total+'</div>';
}