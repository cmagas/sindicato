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

                guardarDatosEventos();

        break;

        case 2:

                modificarEstatusEvento();

        break;

        case 3:

                guardarModificacionEventos();

        break;

        

    }



    function guardarDatosEventos()

    {

        global $con;

        

        $fechaActual=date("Y-m-d");

        $cadObj=$_POST["cadObj"];



        $obj="";

        $obj=json_decode($cadObj);



        $titulo=$obj->titulo;

        $descripcion=$obj->descripcion;

        $fechaEvento=$obj->fechaEvento;

        $horaEvento=$obj->horaEVento;

        $lugarEvento=$obj->lugarEvento;

        $fechaAplicacion=$obj->fechaAplicacion;

        $fechaFin=$obj->fechaFin;



        $tipoOperacion="Registrar nuevo evento";



        

        $x=0;

        $consulta[$x]="begin";

        $x++;

    

        $consulta[$x]="INSERT INTO 202_eventos(fechaRegistro,idResponsable,titulo,descripcion,fechaPublicacion,

                fechaFin,lugar,hora,fechaEvento,situacion)VALUES('".$fechaActual."','".$_SESSION['idUsr']."','".$titulo."','".$descripcion."',

                '".$fechaAplicacion."','".$fechaFin."','".$lugarEvento."','".$horaEvento."','".$fechaEvento."','1')";

        $x++;

    

        $consulta[$x]="commit";

        $x++;

        

        if($con->ejecutarBloque($consulta))

        {

            guardarBitacoraAdmon(2,"$tipoOperacion",$_SESSION['idUsr'],'');

            echo "1|";

        }  



    }



    function modificarEstatusEvento()

    {

        global $con; 

        $fechaActual=date("Y-m-d");



        $cadObj=$_POST["cadObj"];



        $obj="";



        $obj=json_decode($cadObj);



    

        $idEvento=$obj->idEvento;

        $estado=$obj->estado;



        $tipoOperacion="Modificar estatus evento ".$idEvento;



        $x=0;

        $consulta[$x]="begin";

        $x++;



        $consulta[$x]="UPDATE 202_eventos SET situacion='".$estado."' WHERE idEvento='".$idEvento."'";

        $x++;



        $consulta[$x]="commit";

        $x++;



        

        if($con->ejecutarBloque($consulta))

        {

            guardarBitacoraAdmon(3,"$tipoOperacion",$_SESSION['idUsr'],'');

            echo "1|";

        } 

    }



    function guardarModificacionEventos()

    {

        global $con;

        $idUsuarioSesion=$_SESSION['idUsr'];

        $fechaActual=date("Y-m-d");



        $cadObj=$_POST["cadObj"];



        $obj="";



        $obj=json_decode($cadObj);



        $titulo=$obj->titulo;

        $descripcion=$obj->descripcion;

        $fechaEvento=$obj->fechaEvento;

        $horaEvento=$obj->horaEvento;

        $lugarEvento=$obj->lugarEvento;

        $fechaAplicacion=$obj->fechaAplicacion;

        $fechaFin=$obj->fechaFin;

        $idEvento=$obj->idEvento;



        $tipoOperacion="Modificacion de evento ".$idEvento;

    

        $x=0;

        $consulta[$x]="begin";

        $x++;



        $consulta[$x]="UPDATE 202_eventos SET titulo='".$titulo."',descripcion='".$descripcion."',fechaPublicacion='".$fechaAplicacion."',

        fechaFin='".$fechaFin."',lugar='".$lugarEvento."',hora='".$horaEvento."',fechaEvento='".$fechaEvento."' WHERE idEvento='".$idEvento."'";

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