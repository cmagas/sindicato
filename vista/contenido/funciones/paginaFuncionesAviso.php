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
                cambiarSituacionAvisos();
        break;
        
       
    }

    function cambiarSituacionAvisos()
    {
        global $con;
        $fechaActual=date("Y-m-d");

        $cadObj=$_POST["cadObj"];

        $obj="";

        $obj=json_decode($cadObj);

    
        $idAviso=$obj->idAviso;
        $estado=$obj->estado;

        $x=0;
        $consulta[$x]="begin";
        $x++;

        $consulta[$x]="UPDATE 201_avisos SET situacion='".$estado."' WHERE idAviso='".$idAviso."'";
        $x++;

        $consulta[$x]="commit";
        $x++;

        
        if($con->ejecutarBloque($consulta))
        {
            guardarBitacoraAdmon(3,'modificacionAvisos',$_SESSION['idUsr'],'');
            echo "1|";
        } 
    }

    

?>