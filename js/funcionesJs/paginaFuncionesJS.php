<?php    
    include_once("latis/conexionBD.php");
    include_once("latis/utiles.php");
    
    

    if(isset($_POST["parametros"]))
        $parametros=$_POST["parametros"];
    if(isset($_POST["funcion"]))
        $funcion=$_POST["funcion"];


    switch($funcion)
    {
        case 1:
                restablecerContrasenaUsuario();
        break;
        
    }

    function restablecerContrasenaUsuario()
    {
        global $con;

        $fechaActual=date("Y-m-d");
        $asunto="Solicitud de restablecer contraseña";

        $cadObj=$_POST["cadObj"];

        $obj="";

        $obj=json_decode($cadObj);

    
        $email=$obj->email;
        $pass=$obj->pass;

        $destinatario=$email;
        $nombreEmisor='administracion Sistema';
        $emisor="carlosmario07@hotmail.com";
        $mensaje="Su nueva contraseña es: ".$pass;

        $x=0;
        $consulta[$x]="begin";
        $x++;

        $consulta[$x]="UPDATE usuarios SET usu_contrasena='".$pass."' WHERE email='".$email."'";
        $x++;

        $consulta[$x]="commit";
        $x++;
        
        
        if($con->ejecutarBloque($consulta))
        {
            //guardarBitacoraAdmon(2,'cambioContrasena','','".$email."');
            enviarMail($destinatario,$asunto,$mensaje,$emisor,$nombreEmisor="",$arrArchivos=null,$arrCopia=null,$arrCopiaOculta=null);
            
        }  
    }

?>