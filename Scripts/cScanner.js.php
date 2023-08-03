<?php
	session_start();
	include("configurarIdiomaJS.php");
	include("conexionBD.php");
?>

var _connectedScan=false;
var _ipServerScan;
var _portScan;
var _wsImplScan;
var _wsScan; 
var _funcOnMessageScan;
var _funcOnOpen;
var _funcOnError;

var cLatisScan=function(dirIP,puerto,funcOnMessage,funcOnError,funcOnOpen)
				{
                	_connectedScan=false;
                    _ipServerScan=dirIP;
                    _portScan=puerto;
                    _wsImplScan = window.WebSocket || window.MozWebSocket;
                    _wsScan = new _wsImplScan('ws://'+_ipServerScan+':'+_portScan+'/');
                    _funcOnOpen=funcOnOpen;
                    
                    _funcOnMessageScan=funcOnMessage;
                    _funcOnError=funcOnError;
                    _wsScan.onerror = function(e)
                                        {
                                           ocultarMensajeProcesando();
                                            _connectedScan=false;
                                            if(_funcOnError)
	                                            _funcOnError(e);	
                                        }
                    _wsScan.onmessage = function(e)
                                        {
                                           
                                        	var cObj=eval('['+e.data+']')[0];
                                            _funcOnMessageScan(cObj);
            
                                           
                                            
                                        }
                    _wsScan.onopen = function(e)
                                        {
                                        	
                                            setConnected();
                                            if(funcOnOpen)
                                            	funcOnOpen(e);
                                        } 
                
                
                	      
                    		
                	
                }


function isConected()
{
	return _wsScan.readyState==1;
}

function reconect(msg)
{
	mostrarMensajeProcesando('Intentando reconectar con Latis Utilities');
	cScan=cLatisScan('127.0.0.1','8181',_funcOnMessageScan,_funcOnError,function()
    																	{
                                                                        	sendMessageScan(msg);
                                                                        }
    				);

}

function sendMessageScan(msg)
{
	if(isConected())
	    _wsScan.send(msg);
    else
    {
    
    	function resp(btn)
        {
        	if(btn=='yes')
            	reconect(msg);
        }
        msgConfirm('Se ha perdido la conexi&oacute;n con la aplicacion Latis Utilies, desea intentar reconectar?',resp);
    
    	
    }
}

function setConnected()
                        {
                            _connectedScan=true;
                            
                        }   

function getScanList()
{

    var tareaActiva=setInterval(function()
                                { 
                                    
                                    if( _connectedScan)
                                    {
                                        clearInterval(tareaActiva); 
                                        sendMessageScan('{"message":"listScanners","data1":"","data2":"","data3":""}');
                                        
                                        
                                        
                                    }
                                   
                                 }, 500);
}



function getCapabilities(dispositivo)
{

   if( _connectedScan)
    {
        sendMessageScan('{"message":"getCapabilities","data1":"'+dispositivo+'","data2":"","data3":""}');
    }
}


function setCapabilitie(capacidad,valor,dispositivo)
{
	if( _connectedScan)
    {
        sendMessageScan('{"message":"'+capacidad+'","data1":"'+valor+'","data2":"'+dispositivo+'"}');
    }

}


function startScanning(dispositivo)
{
	if( _connectedScan)
    {
        sendMessageScan('{"message":"startCapture","data1":"'+dispositivo+'"}');
    }
}