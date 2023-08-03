<?php
   if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES["id_file"]["type"]))
   {
        session_start();
        include_once("latis/conexionBD.php");
        include_once("latis/utiles.php");

       // varDump($_POST);

        $fechaAct=date("Y-m-d");
         $subNombre=date("Ymdhis");
         $rutaImagen="contenido/galeria/img/";

        global $con;
        $idUsuarioSesion=$_SESSION['idUsr'];

 
        $titulo=$_POST['txt_titulo'];
        $fecha_aplica=date("Y-m-d");

        if($titulo!="")
        {
            
            $nombreArchivo=$_FILES['id_file']['name'];
    
            if(($_FILES["id_file"]["type"] == "image/jpg") || ($_FILES["id_file"]["type"] == "image/png") || ($_FILES["id_file"]["type"] == "image/gif") || ($_FILES["id_file"]["type"] == "image/jpeg"))
            {
                $nombreArchivo=$_FILES['id_file']['name'];
                $file_tmp_name=$_FILES['id_file']['tmp_name'];
                $dir='../image/';
                
                if (!file_exists($dir)) {
                    mkdir($dir, 0777, true);
                }
                
                $nombreArchivoFin=$subNombre."_".$nombreArchivo;
                $new_name_file=$dir.$subNombre."_".$nombreArchivo;
                //echo "nombre archivo ".$new_name_file;
    
                if (copy($file_tmp_name, $new_name_file)) 
                {
                    $consulta="INSERT INTO 204_galeria(idResponsable,fechaRegistro,titulo,url_imagen,situacion)VALUES('".$idUsuarioSesion."','".$fechaAct."','".$titulo."','".$nombreArchivoFin."','1')";
                    $resp=$con->ejecutarConsulta($consulta);

                    if($resp)
                    {
                        echo "1";
                    }
                    else{
                        echo "2";
                    }
                }
                
            }
            else{
                echo "0";
            }
        }
        else
        {
            echo "3";
        }
    }

    
?>