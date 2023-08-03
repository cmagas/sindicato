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
                guardarDatosAreas();
        break;
        case 2:
                cambiarSituacionAreas();
        break;
        case 3:
                actualizarDatosAreas();
        break;
        
    }

    function guardarDatosAreas()
    {
        global $con;
        
        $idUsuarioSesion=$_SESSION['idUsr'];
        $fechaActual=date("Y-m-d");

        $cadObj=$_POST["cadObj"];

        $obj="";

        $obj=json_decode($cadObj);

        $nombreArea=$obj->nombre;
        $email=$obj->email;

        $x=0;
        $consulta[$x]="begin";
        $x++;

        $consulta[$x]="INSERT INTO 11_cat_areas(idResponsable,fechaRegistro,nombreArea,email,situacion)VALUES('".$_SESSION['idUsr']."',
                '".$fechaActual."','".$nombreArea."','".$email."','1')";
        $x++;

        $consulta[$x]="commit";
        $x++;
        
        
        if($con->ejecutarBloque($consulta))
        {
            guardarBitacoraAdmon(2,'registroAreas',$_SESSION['idUsr'],'');
            echo "1|";
        }  
    }

    function cambiarSituacionAreas()
    {
        global $con; 
        
        $fechaActual=date("Y-m-d");

        $cadObj=$_POST["cadObj"];

        $obj="";

        $obj=json_decode($cadObj);

    
        $idArea=$obj->idArea;
        $estado=$obj->estado;

        if($estado==0)
        {
            $tipoOperacion="Cambio de estatus Inactiva area: ".$idArea;
        }
        else{
            $tipoOperacion="Cambio de estatus Activar area: ".$idArea;
        }
        

        $x=0;
        $consulta[$x]="begin";
        $x++;

        $consulta[$x]="UPDATE 11_cat_areas SET situacion='".$estado."' WHERE idArea='".$idArea."'";
        $x++;

        $consulta[$x]="commit";
        $x++;

        
        if($con->ejecutarBloque($consulta))
        {
            guardarBitacoraAdmon(3,"$tipoOperacion",$_SESSION['idUsr'],'');
            echo "1|";
        } 
    }

    function actualizarDatosAreas()
    {
        global $con;

        $idUsuarioSesion=$_SESSION['idUsr'];
        $fechaActual=date("Y-m-d");

        $cadObj=$_POST["cadObj"];

        $obj="";

        $obj=json_decode($cadObj);

        $nombreArea=$obj->nombre;
        $email=$obj->email;
        $idArea=$obj->idArea;

        $tipoOperacion="Cambio de Datos del Area: ".$idArea;
        
        $x=0;
        $consulta[$x]="begin";
        $x++;

        $consulta[$x]="UPDATE 11_cat_areas SET nombreArea='".$nombreArea."',email='".$email."' WHERE idArea='".$idArea."'";
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