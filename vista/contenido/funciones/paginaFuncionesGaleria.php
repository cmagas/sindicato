<?php
session_start();
include_once("latis/conexionBD.php");
include_once("latis/utiles.php");


$idUsuario=$_SESSION['idUsr'];

if(isset($_POST["parametros"]))
    $parametros=$_POST["parametros"];
if(isset($_POST["funcion"]))
    $funcion=$_POST["funcion"];


switch($funcion)
{
    case 1:
            guardarDatosGaleria();
    break;
    case 2:
            cambiarSituacionGaleria();
    break;
    case 3:
            actualizarDatosUsuario();
    break;
}


function guardarDatosGaleria()
{
    
    global $con;
    $idUsuarioSesion=$_SESSION['idUsr'];
    $fechaActual=date("Y-m-d");
    

    $cadObj=$_POST["cadObj"];

    $obj="";

    $obj=json_decode($cadObj);

   
    $nombre=$obj->nombre;
    $apPaterno=$obj->apPaterno;
    $apMaterno=$obj->apMaterno;
    $genero=$obj->genero;
    $email=$obj->email;
    $usuario=$obj->usuario;
    $password=$obj->pass1;
    
        $tipoOperacion="Registrar nuevo Usuario: ".$nombre;

    $x=0;
	$consulta[$x]="begin";
	$x++;

    $consulta[$x]="";
    $x++;

    $consulta[$x]="commit";
	$x++;
	
    
	if($con->ejecutarBloque($consulta))
	{
        guardarBitacoraAdmon(2,"$tipoOperacion",$_SESSION['idUsr'],'');
		echo "1|";
	}  
     
}

function cambiarSituacionGaleria()
{
    global $con; 
    $fechaActual=date("Y-m-d");

    $cadObj=$_POST["cadObj"];

    $obj="";

    $obj=json_decode($cadObj);

   
    $idGaleria=$obj->idGaleria;
    $estado=$obj->estado;

    $tipoOperacion="Modifica situacion Galeria: ".$idGaleria." situacion: ".$estado;

    $x=0;
	$consulta[$x]="begin";
	$x++;

    $consulta[$x]="UPDATE 204_galeria SET situacion='".$estado."' WHERE idGaleria='".$idGaleria."'";
    $x++;

    $consulta[$x]="commit";
	$x++;

    
    if($con->ejecutarBloque($consulta))
	{
        guardarBitacoraAdmon(3,"$tipoOperacion",$_SESSION['idUsr'],'');
		echo "1|";
	} 
    
}

function actualizarDatosUsuario()
{
    global $con;

    $idUsuarioSesion=$_SESSION['idUsr'];
    $fechaActual=date("Y-m-d");

    $cadObj=$_POST["cadObj"];

    $obj="";

    $obj=json_decode($cadObj);

    $email=$obj->email;
    $usuario=$obj->usuario;
    $password=$obj->pass1; 
    $idUsuario=$obj->idUsuario;
    
    $x=0;
	$consulta[$x]="begin";
	$x++;

    $consulta[$x]="UPDATE usuarios SET usu_nombre='".$usuario."',usu_contrasena='".$password."',email='".$email."' WHERE idUsuario='".$idUsuario."'";
    $x++;
    
    $consulta[$x]="commit";
	$x++;

    
    if($con->ejecutarBloque($consulta))
	{
       // guardarBitacoraAdmon($tipo,$pagina,$idUsuario,$param)
        guardarBitacoraAdmon('3','modificacionUsuario',$_SESSION['idUsr'],'');
		echo "1|";
	}     

}
