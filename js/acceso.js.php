<?php
    //session_start();
    include_once("latis/conexionBD.php");

?>

function autentificar()
{
	var txtUsuario=gE('txtUsuario');
    var txtPasswd=gE('txtPasswd');

    if((txtUsuario.value.trim()=='')||(txtPasswd.value.trim()==''))
    {
	    return Swal.fire("Mensaje de Advertencia", "Llene los campos vacios", "Warning");
    }    
    
    function funcAjax2(peticion_http)
    {
        var resp=peticion_http.responseText;
        arrResp=resp.split('|');
        if(arrResp[0]=='1')
        {
         	if( arrResp[1] =='0')
            {
                Swal.fire("Mensaje de Error", 'Usuario y/o contraseña incorrecta', "error");
            }
            else
            {
            	location.href='../vista/index.php';
                
            }
        }
        else
        {
            msgBox('No se ha podido realizar la operaci&oacute;n debido al siguiente problema: '+' <br />'+arrResp[0]);
        }
    }
    obtenerDatosWebTecno('../paginaFunciones/funciones.php',funcAjax2, 'POST','funcion=1&l='+txtUsuario.value.trim()+'&p='+txtPasswd.value.trim(),false);
}