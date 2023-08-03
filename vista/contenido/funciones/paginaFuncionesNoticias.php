<?php
    session_start();
    include_once("latis/conexionBD.php");
    include_once("latis/utiles.php");

    
    if(isset($_POST["parametros"]))
        $parametros=$_POST["parametros"];
    if(isset($_POST["funcion"]))
        $funcion=$_POST["funcion"];

       
    switch($funcion)
    {
        case 1:
                guardarDatosNoticias();
        break;
        case 2:
                modificarEstadoNoticia();
        break;
        case 3:
                guardarCambiosNoticias();
        break;

    }

    function guardarDatosNoticias()
    {
        global $con;
        $fechaActual=date("Y-m-d");
        $cadObj=$_POST["cadObj"];

        $obj="";
        $obj=json_decode($cadObj);

        $titulo=$obj->titulo;
        $descricion=$obj->descricion;
        $fechaPub=$obj->fechaPub;
        $fechaFin=$obj->fechaFin;
        $idArea=$obj->idArea;

        $tipoOperacion="Registra nueva Noticias";

        $x=0;
        $consulta[$x]="begin";
        $x++;
    
        $consulta[$x]="INSERT 203_noticias(fechaRegistro,idResponsable,titulo,descripcion,fechaPublicacion,fechaFin,idArea,
            situacion)VALUES('".$fechaActual."','".$_SESSION['idUsr']."','".$titulo."','".$descricion."','".$fechaPub."','".$fechaFin."','".$idArea."','1')";
        $x++;
    
        $consulta[$x]="commit";
        $x++;
        
        if($con->ejecutarBloque($consulta))
        {
            guardarBitacoraAdmon(2,"$tipoOperacion",$_SESSION['idUsr'],'');
            echo "1|";
        }  
    }

    function modificarEstadoNoticia()
    {
        global $con; 
        $fechaActual=date("Y-m-d");

        $cadObj=$_POST["cadObj"];

        $obj="";

        $obj=json_decode($cadObj);

    
        $idNoticia=$obj->idNoticia;
        $estado=$obj->estado;

        $tipoOperacion="Modificar estatus Noticia ".$idNoticia;

        $x=0;
        $consulta[$x]="begin";
        $x++;

        $consulta[$x]="UPDATE 203_noticias SET situacion='".$estado."' WHERE idNoticia='".$idNoticia."'";
        $x++;

        $consulta[$x]="commit";
        $x++;

        
        if($con->ejecutarBloque($consulta))
        {
            guardarBitacoraAdmon(3,"$tipoOperacion",$_SESSION['idUsr'],'');
            echo "1|";
        } 

    }

    function guardarCambiosNoticias()
    {
        global $con;
        $idUsuarioSesion=$_SESSION['idUsr'];
        $fechaActual=date("Y-m-d");

        $cadObj=$_POST["cadObj"];

        $obj="";

        $obj=json_decode($cadObj);

        $titulo=$obj->titulo;
        $descripcion=$obj->descripcion;
        $fechaAplicacion=$obj->fechaPub;
        $fechaFin=$obj->fechaFin;
        $idArea=$obj->idArea;
        $idNoticia=$obj->idNoticia;

        $tipoOperacion="Modificacion de noticia ".$idNoticia;

        $x=0;
        $consulta[$x]="begin";
        $x++;

        $consulta[$x]="UPDATE 203_noticias SET titulo='".$titulo."',descripcion='".$descripcion."',fechaPublicacion='".$fechaAplicacion."',
                    fechaFin='".$fechaFin."',idArea='".$idArea."' WHERE idNoticia='".$idNoticia."'";
        $x++;
        
        $consulta[$x]="commit";
        $x++;

        
        if($con->ejecutarBloque($consulta))
        {
            guardarBitacoraAdmon('3',"$tipoOperacion",$_SESSION['idUsr'],'');
            echo "1|";
        }     
    }




?>