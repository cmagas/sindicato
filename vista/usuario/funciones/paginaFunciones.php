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
            guardarDatosUsuario();
    break;
    case 2:
            cambiarSituacionUsuario();
    break;
    case 3:
            actualizarDatosUsuario();
    break;
}


function guardarDatosUsuario()
{
    global $con;
    $idUsuarioSesion=$_SESSION['idUsr'];
    $fechaActual=date("Y-m-d");
    $idEmpresa='2';

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

    $consulta[$x]="INSERT INTO usuarios(fechaRegistro,idResponsable,usu_nombre,usu_contrasena,usu_sexo,nombre,
            apPaterno,apMaterno,idEmpresa,email,situacion)VALUES('".$fechaActual."','".$_SESSION['idUsr']."','".$usuario."','".$password."',
            '".$genero."','".$nombre."','".$apPaterno."','".$apMaterno."','".$idEmpresa."','".$email."','1')";
    $x++;

    $consulta[$x]="commit";
	$x++;
	
    
	if($con->ejecutarBloque($consulta))
	{
        guardarBitacoraAdmon(2,"$tipoOperacion",$_SESSION['idUsr'],'');
		echo "1|";
	}  
     
}

function cambiarSituacionUsuario()
{
    global $con; 
    $fechaActual=date("Y-m-d");

    $cadObj=$_POST["cadObj"];

    $obj="";

    $obj=json_decode($cadObj);

   
    $idUsuario=$obj->idUsuario;
    $estado=$obj->estado;

    $x=0;
	$consulta[$x]="begin";
	$x++;

    $consulta[$x]="UPDATE usuarios SET situacion='".$estado."' WHERE idUsuario='".$idUsuario."'";
    $x++;

    $consulta[$x]="commit";
	$x++;

    
    if($con->ejecutarBloque($consulta))
	{
        guardarBitacoraAdmon(3,'modificacionUsuario',$_SESSION['idUsr'],'');
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

?>