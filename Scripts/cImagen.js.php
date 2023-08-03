<?php
	session_start();
	include("configurarIdiomaJS.php");
	include("conexionBD.php");
?>

var cxt;
var video;
var video2;

var cImagen=function(conf)
            {
            	
            	this.funcionAceptar=null;
                if(conf.funcionAceptar)
            		this.funcionAceptar=conf.funcionAceptar;
                
            	var canvas =null;
                var ctx=null;
            	this.mostarVenanaCtrlImagen=function()
                                        {
                                        	var prepararCamara=this.prepararCamara;
                                            var tomarFoto=this.tomarFoto;
                                            var mostrarVentanaFoto=this.mostrarVentanaFoto;
                                            var form = new Ext.form.FormPanel(	
                                                                                {
                                                                                    baseCls: 'x-plain',
                                                                                    layout:'absolute',
                                                                                    defaultType: 'label',
                                                                                    items: 	[
                                                                                                {
                                                                                                    x:20,
                                                                                                    y:0,
                                                                                                    xtype:'label',
                                                                                                    html:'<video style="background-color:#FFF" id="video" width="225" height="184" autoplay="autoplay"></video>'
                                                                                                },
                                                                                                {
                                                                                                    x:270,
                                                                                                    y:0,
                                                                                                    hidden:true,
                                                                                                    xtype:'label',
                                                                                                    html:'<video  id="video2" width="675" height="552" autoplay="autoplay"></video>'
                                                                                                },
                                                                                                {
                                                                                                    x:270,
                                                                                                    y:0,
                                                                                                    hidden:true,
                                                                                                    xtype:'label',
                                                                                                    html:'<canvas id="canvas" width="675" height="552"></canvas>'
                                                                                                },
                                                                                                {
                                                                                                    x:280,
                                                                                                    y:0,
                                                                                                    xtype:'toolbar',
                                                                                                    items:	[
                                                                                                                {
                                                                                                                    xtype: 'buttongroup',
                                                                                                                    columns: 1,
                                                                                                                    items:	[
                                                                                                                               
                                                                                                                                {
                                                                                                                                    icon:'../images/pictures.png',
                                                                                                                                    cls:'x-btn-text-icon',
                                                                                                                                    width:130,
                                                                                                                                    height:40,
                                                                                                                                    id:'btnCapturar',
                                                                                                                                    disabled:false,
                                                                                                                                    text:'Capturar imagen',
                                                                                                                                    handler:function()
                                                                                                                                            {
                                                                                                                                                tomarFoto();
                                                                                                                                               	mostrarVentanaFoto();
                                                                                                                                                
                                                                                                                                            }
                                                                                                                                    
                                                                                                                                },
                                                                                                                                {
                                                                                                                                    icon:'../images/cross.png',
                                                                                                                                    cls:'x-btn-text-icon',
                                                                                                                                    width:130,
                                                                                                                                    height:40,
                                                                                                                                    id:'btnCancelar',
                                                                                                                                    disabled:false,
                                                                                                                                    text:'Cancelar captura',
                                                                                                                                    handler:function()
                                                                                                                                            {
                                                                                                                                                ventanaAM.close();
                                                                                                                                                
                                                                                                                                            }
                                                                                                                                    
                                                                                                                                },
                                                                                                                                {
                                                                                                                                    icon:'../images/control_pause.png',
                                                                                                                                    cls:'x-btn-text-icon',
                                                                                                                                    width:130,
                                                                                                                                    height:40,
                                                                                                                                    hidden:true,
                                                                                                                                    disabled:false,
                                                                                                                                    id:'btnPausar',
                                                                                                                                    text:'Detener C&aacute;mara',
                                                                                                                                    handler:function()
                                                                                                                                            {
                                                                                                                                                gEx('btnPausar').disable();
                                                                                                                                                gEx('btnPreparar').enable();
                                                                                                                                                gEx('btnCapturar').disable();
                                                                                                                                                video.pause();
                                                                                                                                                video2.pause();
                                                                                                                                                
                                                                                                                                            }
                                                                                                                                    
                                                                                                                                }
                                                                                                                            ]
                                                                                                                }
                                        
                                                                                                            ]
                                                                                                }
                                                                                          	]
                                                                               	}
                                                                              );
                                            
                                            var ventanaAM = new Ext.Window(
                                                                            {
                                                                                title: 'Tomar Imagen',
                                                                                id:'vTomarImagen',
                                                                                width: 480,
                                                                                height:280,
                                                                                layout: 'fit',
                                                                                plain:true,
                                                                                modal:true,
                                                                                bodyStyle:'padding:5px;',
                                                                                buttonAlign:'center',
                                                                                items: form,
                                                                                listeners : {
                                                                                            	show : {
                                                                                                            buffer : 10,
                                                                                                            fn : function() 
                                                                                                            {
                                                                                                                prepararCamara();
                                                                                                                                                    
                                                                                                            }
                                                                                                        },
                                                                                                 close:function()
                                                                                                 		{
                                                                                                        	video.pause();
                                                                                                            video2.pause();
                                                                                                        }       
                                                                                                       
                                                                                              
                                                                                        }
                                                                            }
                                                                        );
                                            ventanaAM.show();
                                            
										    
                                        }   
							                                        
                this.prepararCamara=	function ()
                                    {
                                        this.video = gE('video');
                                        this.video2 = gE('video2');
                                        if (navigator.getUserMedia) 
                                        {
                                              navigator.getUserMedia
                                                                      (
                                                                          { 'video': true },function(stream)
                                                                                          {
                                                                                              this.video.src = stream;
                                                                                              this.video.play();
                                                                                              this.video2.src = stream;
                                                                                              this.video2.play();
                                                                                          },
                                                                                          
                                                                                          
                                                                          function(err)
                                                                                          {
                                                                                              msgBox('No se pudo llevar a cabo la operaci&oacute;n debido al siguiente error: '+err);
                                                                                          }                
                                                                      );
                                        } 
                                        else 
                                          if (navigator.webkitGetUserMedia) 
                                          {
                                              navigator.webkitGetUserMedia
                                                                          (
                                                                              { 'video': true },
                                                                              function(stream)
                                                                              {
                                                                                  this.video.src = window.webkitURL.createObjectURL(stream);
                                                                                  this.video.play();
                                                                                  this.video2.src = window.webkitURL.createObjectURL(stream);
                                                                                  this.video2.play();
                                                                              },
                                                                              function(err)
                                                                                          {
                                                                                              msgBox('No se pudo llevar a cabo la operaci&oacute;n debido al siguiente error: '+err);
                                                                                          }    
                                                                          );
                                          } 
                                          else 
                                              if (navigator.mozGetUserMedia) 
                                              {
                                                  navigator.mozGetUserMedia
                                                                          (
                                                                              { 'video': true },
                                                                              function(stream)
                                                                              {
                                                                                  this.video.mozSrcObject = stream;
                                                                                  this.video.play();
                                                                                  this.video2.mozSrcObject = stream;
                                                                                  this.video2.play();
                                                                              },
                                                                              function(err)
                                                                              {
                                                                                  msgBox('No se pudo llevar a cabo la operaci&oacute;n debido al siguiente error: '+err);
                                                                              }
                                                                          );
                                              }
                                    }

                this.tomarFoto=function ()
			                {
                            	
                                gE('canvas').getContext('2d').drawImage(video2, 0, 0, 675, 552);
                            }

                this.mostrarVentanaFoto=function ()
                                        {
                                        	var funcionAceptar=this.funcionAceptar;
                                            
                                            
                                            var form = new Ext.form.FormPanel(	
                                                                                {
                                                                                    baseCls: 'x-plain',
                                                                                    layout:'absolute',
                                                                                    defaultType: 'label',
                                                                                    items: 	[
                                                                                                {
                                                                                                    x:5,
                                                                                                    y:5,
                                                                                                    html:'<img id="canvasTmp" style="border-style:solid; border-width:1px; border-color:#000; width:450px;height:368px">'
                                                                                                }
                                        
                                                                                            ]
                                                                                }
                                                                            );
                                            
                                            var ventanaAM = new Ext.Window(
                                                                            {
                                                                                title: 'Imagen capturada',
                                                                                
                                                                                width: 490,
                                                                                height:475,
                                                                                layout: 'fit',
                                                                                plain:true,
                                                                                modal:true,
                                                                                bodyStyle:'padding:5px;',
                                                                                buttonAlign:'right',
                                                                                items: form,
                                                                                listeners : {
                                                                                            show : {
                                                                                                        buffer : 10,
                                                                                                        fn : function() 
                                                                                                        {
                                                                                                        }
                                                                                                    }
                                                                                        },
                                                                                buttons:	[
                                                                                                {
                                                                                                    icon:'../images/icon_big_tick.gif',
                                                                                                    cls:'x-btn-text-icon',
                                                                                                    width:130,
                                                                                                    height:40,
                                                                                                    id:'btnMantener',
                                                                                                    text:'Guadar imagen',
                                                                                                    handler:function()
                                                                                                            {
                                                                                                                
                                                                                                                
                                                                                                                
                                                                                                                var imagen=gE('canvas').toDataURL("image/png");
                                                                                                                var peticion_http=crearMotorAjax();
                                                                                                                if(peticion_http)
                                                                                                                {
                                                                                                                    peticion_http.onreadystatechange=function()
                                                                                                                                                    {
                                                                                                                                                        if(peticion_http.readyState==PETICION_COMPLETO)
                                                                                                                                                        {
                                                                                                                                                            if(peticion_http.status==RESPUESTA_OK)
                                                                                                                                                            {
                                                                                                                                                                ocultarMensajeEspere();
                                                                                                                                                                ventanaAM.close();
                                                                                                                                                                if(conf.funcionAceptar)
                                                                                                                                                                    conf.funcionAceptar(peticion_http.responseText);
                                                                                                                                                            }
                                                                                                                                                            else
                                                                                                                                                            {
                                                                                                                                                                ocultarMensajeEspere();
                                                                                                                                                                msgBox('No se ha podido establecer conexi&oacute;n con la p&aacute;gina: '+urlADescargar);
                                                                                                                                                                return;
                                                                                                                                                            }
                                                                                                                                                        }
                                                                                                                                                    }
                                                                                                                    peticion_http.open("POST",'../paginasFunciones/procesarImagenCamara.php',true); 
                                                                                                                    peticion_http.setRequestHeader("Content-Type","application/upload'");
                                                                                                                    peticion_http.send(imagen);        
                                                                                                                    mostrarVentanaEspere();                       
                                                                                                             	}                       
                                                                                                                                                                                                                                    
                                                                                                                
                                                                                                            }
                                                                                                    
                                                                                                },
                                                                                                {
                                                                                                    icon:'../images/cross.png',
                                                                                                    cls:'x-btn-text-icon',
                                                                                                    width:130,
                                                                                                    height:40,
                                                                                                    id:'btnDesechar',
                                                                                                    text:'Desechar imagen',
                                                                                                    handler:function()
                                                                                                            {
                                                                                                               function resp(btn)
                                                                                                               {
                                                                                                                    if(btn=='yes')
                                                                                                                    {
                                                                                                                        ventanaAM.close();
                                                                                                                    }
                                                                                                               }
                                                                                                               msgConfirm('Est&aacute; seguro de querer desechar la imagen capturada?',resp);
                                                                                                                
                                                                                                            }
                                                                                                    
                                                                                                }
                                                                                            ]
                                                                            }
                                                                        );
                                            ventanaAM.show();	
                                           	var dataUrl=gE('canvas').toDataURL('image/png',1);
                                            gE('canvasTmp').src=dataUrl;
                                            
											
                                            
                                            
                                        }                        
                                        
            }

